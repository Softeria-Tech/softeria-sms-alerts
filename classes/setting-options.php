<?php
/**
 * WordPress settings API class
 *
 * PHP version 5
 *
 * @category Handler
 * @package  SMSPro
 * @author   SMS Pro <support@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 */
if (! defined('ABSPATH') ) {
    exit;
} 
require_once ABSPATH . 'wp-admin/includes/plugin.php';
/**
 * PHP version 5
 *
 * @category Handler
 * @package  SMSPro
 * @author   SMS Pro <support@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 * Smsalert Setting Options class
 */
class smspro_Setting_Options
{
    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     * @return stirng
     */
    public static function init()
    {
        include_once plugin_dir_path(__DIR__) . '/helper/class-uncannyautomator.php';
        include_once plugin_dir_path(__DIR__) . '/helper/class-shortcode.php';
        include_once plugin_dir_path(__DIR__) . '/helper/class-divi.php';
        include_once plugin_dir_path(__DIR__) . '/helper/class-wordpresswidget.php';
        include_once plugin_dir_path(__DIR__) . '/helper/upgrade.php';
        include_once plugin_dir_path(__DIR__) . '/helper/class-backend.php';
        include_once plugin_dir_path(__DIR__) . '/helper/edd.php';
        include_once plugin_dir_path(__DIR__) . '/helper/learnpress.php';
        include_once plugin_dir_path(__DIR__) . '/helper/woocommerce-booking.php';
        include_once plugin_dir_path(__DIR__) . '/helper/events-manager.php';
        include_once plugin_dir_path(__DIR__) . '/helper/class-cartbounty.php';
        include_once plugin_dir_path(__DIR__) . '/helper/delivery-drivers-woocommerce.php';
        include_once plugin_dir_path(__DIR__) . '/helper/class-sapopup.php';
        include_once plugin_dir_path(__DIR__) . '/helper/class-elementorwidget.php';
        include_once plugin_dir_path(__DIR__) . '/helper/class-backinstock.php';
        include_once plugin_dir_path(__DIR__) . '/helper/wc-low-stock.php';
        include_once plugin_dir_path(__DIR__) . '/helper/review.php';
        include_once plugin_dir_path(__DIR__) . '/helper/share-cart.php';
        include_once plugin_dir_path(__DIR__) . '/helper/class-terawallet.php';
        include_once plugin_dir_path(__DIR__) . '/helper/wc-subscriptions.php';
        include_once plugin_dir_path(__DIR__) . '/helper/class-abandonedcart.php';
        include_once plugin_dir_path(__DIR__) . '/helper/wc-integration.php';
        include_once plugin_dir_path(__DIR__) . '/helper/new-user-approve.php';
        include_once plugin_dir_path(__DIR__) . '/helper/return-warranty.php';
        include_once plugin_dir_path(__DIR__)    .'/helper/signup-with-otp.php';
        include_once plugin_dir_path(__DIR__)    . '/helper/feedback.php';
        include_once plugin_dir_path(__DIR__)    . '/helper/class-blocks.php';
        include_once plugin_dir_path(__DIR__)    . '/helper/class-smscampaign.php';
        include_once plugin_dir_path(__DIR__)    . '/helper/class-wpfusion.php';
        
        add_action('admin_menu', __CLASS__ . '::smsAlertWcSubmenu', 50);

        add_filter('um_predefined_fields_hook', __CLASS__ . '::myPredefinedFields', 10, 2);

        add_action('verify_senderid_button', __CLASS__ . '::actionWoocommerceAdminFieldVerifySmsAlertUser');
        add_action('admin_post_save_sms_pro_settings', __CLASS__ . '::save');
        if (! self::is_user_authorised() ) {
            add_action('admin_notices', __CLASS__ . '::showAdminNoticeSuccess');
        }
		else if(!is_plugin_active( 'chat-on-desk/ChatOnDesk-wc-order-sms.php' ))
		{
			$chatondesk_notice = get_option('dismiss_chatondesk_notice', 0);
			if($chatondesk_notice != 1)
			{
				add_action( 'load-index.php', 
					function(){
						add_action('admin_notices', __CLASS__ . '::showWhatsappNotification');
					}
				);
			}
		}
        add_action('admin_notices', __CLASS__ . '::showPlayGroundNotices');
		
        self::smsproDashboardSetup();
        self::resetOTPModalStyle();
        $current_user_is_admin = current_user_can('manage_options');
        if (array_key_exists('option', $_GET) && $current_user_is_admin && !empty($_GET['nonce']) && wp_verify_nonce( $_GET['nonce'], 'smspro-nonce' ) ) {
            switch ( trim(sanitize_text_field(wp_unslash($_GET['option']))) ) {
            case 'smspro-woocommerce-senderlist':
                $user = isset($_GET['user']) ? sanitize_text_field(wp_unslash($_GET['user'])) : '';
                $pwd  = isset($_GET['pwd']) ? sanitize_text_field(wp_unslash($_GET['pwd'])) : '';
                wp_send_json(SmsAlertcURLOTP::getSenderids($user, $pwd));
                exit;
            case 'smspro-woocommerce-creategroup':
                SmsAlertcURLOTP::creategrp();
                wp_send_json(SmsAlertcURLOTP::groupList());
                break;
            case 'smspro-woocommerce-logout':
                wp_send_json(self::logout());
                break;
            case 'smspro-woocommerce-countrylist':
                wp_send_json(SmsAlertcURLOTP::country_list());
                break;
            case 'dismiss_chatondesk_notice':
                update_option('dismiss_chatondesk_notice', 1);
                break;
            case 'smspro_sandbox_mode':
                update_option('smspro_sandbox_mode', 1);
                break;				
            }
        }
    }

    /**
     * Triggers when woocommerce is loaded.
     *
     * @return stirng
     */
    public static function action_woocommerce_loaded()
    {
        $sa_abcart = new SA_Abandoned_Cart();
        $sa_abcart->run();
    }

    /**
     * Add smspro phone button in ultimate form.
     *
     * @param array $predefined_fields Default fields of the form.
     *
     * @return stirng
     */
    public static function myPredefinedFields( $predefined_fields )
    {
        $fields            = array(
        'billing_phone' => array(
        'title'    => 'Smsalert Phone',
        'metakey'  => 'billing_phone',
        'type'     => 'text',
        'label'    => 'Mobile Number',
        'required' => 0,
        'public'   => 1,
        'editable' => 1,
        'validate' => 'billing_phone',
        'icon'     => 'um-faicon-mobile',
        ),
        );
        $predefined_fields = array_merge($predefined_fields, $fields);
        return $predefined_fields;
    }

    /**
     * Adds widgets to dashboard.
     *
     * @return stirng
     */
    public static function smsproDashboardSetup()
    {
        add_action('dashboard_glance_items', __CLASS__ . '::smsproAddDashboardWidgets', 10, 1);
    }
    
            
    /**
     * RouteData function
     *
     * @return array
     */
    private static function resetOTPModalStyle()
    {
        if (!empty($_GET['action']) && $_GET['action']=='reset_style') {            
            $post_name = trim(sanitize_text_field(wp_unslash($_GET['postname'])));            
            $page = get_page_by_title($post_name, OBJECT, 'sms-pro');
            
            if (!empty($page)) {
                $post_ids       = $page->ID;
                if (!empty($post_ids) ) {                            
                    $delete_metadata = wp_delete_post($post_ids);                                
                }
                echo wp_json_encode(array("status"=>"success","description"=>"post deleted"));
                exit();
                    
            }
            
        }
    }
	
    /**
     * Prompts chatondesk notification.
     *
     * @return stirng
     */
    public static function showWhatsappNotification()
    {
		$credits = SmsAlertcURLOTP::getCredits();  
        if (! empty($credits) ) {
            if (!empty($credits['credit_balance'])) {
                if ($credits['credit_balance']< 100) {
                    global $current_user;
                    wp_get_current_user();						
                    ?>
                    <div class="notice notice-warning is-dismissible">
                    <div class="e-notice__content">
                    <h3>Expand Your Messaging Capabilities with WhatsApp!</h3>
                    <p>As a valued SMS Pro customer, you already know the power of seamless communication. Now, take it a step further with our new WhatsApp messaging service—FREE for 30 days! Connect with your customers on the platform they prefer and enhance your outreach effortlessly.</p>
                    <p>Get started today and enjoy the same trusted service you rely on, now with WhatsApp. <a href="https://sms.softeriatech.com" class="button button-primary" target="_blank"><span>Get Started with Free Trial</span></a><a style="margin-left:20px;text-decoration: none" href="javascript:void(0)" id="smspro-remind-later">Don't show it again</a></p>
                        </div>
                    </div>		
                <?php
                }
		    }
	    }
    }
	
    /**
     * Prompts admin to login to SMS Pro if not already logged in.
     *
     * @return stirng
     */
    public static function showAdminNoticeSuccess()
    {
        ?>
    <div class="notice notice-warning is-dismissible">
        <p>
        <?php
        /* translators: %s: plugin settings url */
        echo wp_kses_post(sprintf(__('<a href="%s" target="_blank">Login to SMS Pro</a> to configure SMS Notifications', 'sms-pro'), 'admin.php?page=sms-pro'));
        ?>
        </p>
    </div>		
        <?php
	 
    }
	/**
     * Prompts admin to show Playground Notices.
     *
     * @return stirng
     */
    public static function showPlayGroundNotices()
    {
		global $pagenow;
		if ('admin.php' === $pagenow && 'sms-pro' === sanitize_text_field($_GET['page']) && $_SERVER['HTTP_HOST'] == 'playground.wordpress.net' ) {
		$sandbox_mode = get_option('smspro_sandbox_mode', 0);	
        ?>
		<div class="notice notice-warning">
		<div class="e-notice__content">
		<p><?php
			echo wp_kses_post(sprintf(__('Our SMS Pro service does not send messages through WordPress playground site.', 'sms-pro')));
        ?></p>
		 <?PHP
		 if($sandbox_mode != 1)
		 {
		 ?>
           <p>To check SMS Pro functionality, please enable sandbox mode. <a style="margin-left:20px;text-decoration: none" href="javascript:void(0)" id="smspro-sandbox-mode">Enable Sandbox Mode</a></p>
		 <?PHP
		 }
		 ?>
			</div>
		</div>		
        <?php
		}
    }
    
    /**
     * Gets all payment gateways.
     *
     * @return stirng
     */
    public static function getAllGateways()
    {
        if (! is_plugin_active('woocommerce/woocommerce.php') ) {
            return array(); 
        }
        $gateways      = array();
        $payment_plans = WC()->payment_gateways->payment_gateways();
        foreach ( $payment_plans as $payment_plan ) {
            $gateways[] = $payment_plan->id;
        }
        return $gateways;
    }

    /**
     * Adds SMS Pro in menu.
     *
     * @return stirng
     */
    public static function smsAlertWcSubmenu()
    {

        add_submenu_page('woocommerce', 'SMS Pro', 'SMS Pro', 'manage_options', 'sms-pro', __CLASS__ . '::settingsTab');
        
        add_submenu_page('elementor', 'SMS Pro', 'SMS Pro', 'manage_options', 'sms-pro', __CLASS__ . '::settingsTab');
        
        add_submenu_page('options-general.php', 'SMS ALERT', 'SMS Pro', 'manage_options', 'sms-pro', __CLASS__ . '::settingsTab');

        add_submenu_page('gf_edit_forms', __('SMS ALERT', 'gravityforms'), __('SMS Pro', 'gravityforms'), 'manage_options', 'sms-pro', __CLASS__ . '::settingsTab');

        add_submenu_page('ultimatemember', __('SMS ALERT', 'ultimatemember'), __('SMS Pro', 'ultimatemember'), 'manage_options', 'sms-pro', __CLASS__ . '::settingsTab');

        add_submenu_page('wpcf7', __('SMS ALERT', 'wpcf7'), __('SMS Pro', 'wpcf7'), 'manage_options', 'sms-pro', __CLASS__ . '::settingsTab');

        add_submenu_page('pie-register', __('SMS ALERT', 'pie-register'), __('SMS Pro', 'pie-register'), 'manage_options', 'sms-pro', __CLASS__ . '::settingsTab');

        add_submenu_page('wpam-affiliates', __('SMS ALERT', 'affiliates-manager'), __('SMS Pro', 'affiliates-manager'), 'manage_options', 'sms-pro', __CLASS__ . '::settingsTab');

        add_submenu_page('learn_press', __('SMS ALERT', 'learnpress'), __('SMS Pro', 'learnpress'), 'manage_options', 'sms-pro', __CLASS__ . '::settingsTab');

        add_submenu_page('ninja-forms', __('SMS ALERT', 'ninja-forms'), __('SMS Pro', 'ninja-forms'), 'manage_options', 'sms-pro', __CLASS__ . '::settingsTab');
        
        add_submenu_page('fluent_forms', __('SMS ALERT', 'fluent_forms'), __('SMS Pro', 'fluent_forms'), 'manage_options', 'sms-pro', __CLASS__ . '::settingsTab');
        
        add_submenu_page('forminator', __('SMS ALERT', 'forminator'), __('SMS Pro', 'forminator'), 'manage_options', 'sms-pro', __CLASS__ . '::settingsTab');

        add_submenu_page('user-registration', __('SMS ALERT', 'user-registration'), __('SMS Pro', 'user-registration'), 'manage_options', 'sms-pro', __CLASS__ . '::settingsTab');

        add_submenu_page('erforms-overview', __('SMS ALERT', 'erforms-overview'), __('SMS Pro', 'erforms-overview'), 'manage_options', 'sms-pro', __CLASS__ . '::settingsTab');
         add_submenu_page('options.php', 'Booking Calendar', __('Booking Calendar', 'sms-pro'), 'manage_options', 'booking-reminder', array( 'SAReminderlist', 'display_page' ));
        add_submenu_page('wpforms-overview', __('SMS ALERT', 'wpforms-overview'), __('SMS Pro', 'wpforms-overview'), 'manage_options', 'sms-pro', __CLASS__ . '::settingsTab');

        add_submenu_page('options.php', 'Abandoned Carts', __('Abandoned Carts', 'sms-pro'), 'manage_options', 'ab-cart', array( 'SA_Cart_Admin', 'display_page' ));
        add_submenu_page('options.php', 'Abandoned Carts', __('Abandoned Carts', 'sms-pro'), 'manage_options', 'ab-cart-reports', array( 'SA_Cart_Admin', 'display_reports_page' ));
        
        add_submenu_page('wpbc', __('SMS ALERT', 'wpbc'), __('SMS Pro', 'wpbc'), 'manage_options', 'sms-pro', __CLASS__ . '::settingsTab');
    }

    /**
     * Checks if the user is logged in SMS Pro plugin.
     *
     * @return stirng
     */
   public static function is_user_authorised()
    {
        $smspro_name     = smspro_get_option('smspro_name', 'smspro_gateway', '');
        $smspro_password = smspro_get_option('smspro_password', 'smspro_gateway', '');
        $islogged          = false;
        if (! empty($smspro_name) && ! empty($smspro_password) ) {
            $islogged = true;
        }
		if (SmsAlertUtility::isPlayground()) {
			$islogged = true;
		}
        return $islogged;
    }

    /**
     * Adds Dashboard widgets.
     *
     * @param array $items Default widgets.
     *
     * @return stirng
     */
    public static function smsproAddDashboardWidgets( $items = array() )
    {
        if (self::is_user_authorised() ) {
            $credits = SmsAlertcURLOTP::getCredits();
            $items[] = sprintf('<a href="%1$s" class="smspro-credit"><strong>%2$s SMS</strong> : %3$s</a>', admin_url('admin.php?page=sms-pro'), 'Rate @'.$credits['rate'].'/SMS', $credits['credit_balance']) . '<br />';
        }
        return $items;
    }

    /**
     * Logs out user from SMS Pro plugin.
     *
     * @return void
     */
    public static function logout()
    {
        if (delete_option('smspro_gateway') ) {
            return true;
        }
    }

    /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     *
     * @uses woocommerce_admin_fields()
     * @uses self::getSettings()
     *
     * @return void
     */
    public static function settingsTab()
    {
        self::getSettings();
    }

    /**
     * Save data.
     *
     * @return void
     */
    public static function save()
    {
        error_log( 'admin-post.php: action: save_sms_pro_settings');
        // $verify = check_ajax_referer('wp_save_sms_pro_settings_nonce', 'save_sms_pro_settings_nonce', true);
        // if (!$verify) {
        //     error_log('admin-post.php: action: save_sms_pro_settings - nonce verification failed');
        //     wp_safe_redirect(admin_url('admin.php?page=sms-pro&m=1'));
        //     exit;
        // }
        $_POST = smspro_sanitize_array($_POST);
        self::saveSettings($_POST);
    }

    /**
     * Save settings.
     *
     * @param array $option Default option.
     *
     * @return void
     */
    public static function saveSettings( $option )
    {
        error_log( 'admin-post.php: action: save_sms_pro_settings - saving settings' );

        if (empty($_POST) ) {
            error_log('admin-post.php: action: save_sms_pro_settings - no POST data found');
            return false;
        }

        $reset_settings = ( ! empty($_POST['smspro_reset_settings']) && ( 'on' === $_POST['smspro_reset_settings'] ) ) ? true : false;

        $defaults = array(
        'smspro_gateway'=> array(
        'smspro_name'     => 'SMSPRO',
        'smspro_password' => '',
        'smspro_api'      => '',
        ),
        'smspro_message'              => array(
        'sms_admin_phone'                 => '',
        'group_auto_sync'                 => '',
        'sms_body_new_note'               => '',
        'sms_body_registration_msg'       => '',
        'sms_body_registration_admin_msg' => '',
        'sms_body_admin_low_stock_msg'    => '',
        'sms_body_admin_out_of_stock_msg' => '',
        'sms_otp_send'                    => '',
        ),
        'smspro_general'              => array(
        'buyer_checkout_otp'           => 'off',
        'buyer_signup_otp'             => 'off',
        'buyer_login_otp'              => 'off',
        'buyer_notification_notes'     => 'off',
        'allow_multiple_user'          => 'off',
        'admin_bypass_otp_login'       => array( 'administrator' ),
        'checkout_show_otp_button'     => 'off',
        'checkout_show_otp_guest_only' => 'off',
        'checkout_show_country_code'   => 'off',
        'enable_selected_country'      => 'off',
        'allow_otp_verification'      => 'off',
        'whitelist_country'            => '',
        'allow_otp_country'            => '',
        'daily_bal_alert'              => 'off',
        'enable_short_url'             => 'off',
        'clear_all_data'             => 'off',
        'subscription_reminder_cron_time' => '10:00',
        'auto_sync'                     => 'off',
        'low_bal_alert'                 => 'off',
        'show_flag'                     => 'off',
        'alert_email'                   => '',
        'otp_template_style'            => 'popup-4',
        'checkout_payment_plans'        => '',
        'otp_for_selected_gateways'     => 'off',
        'otp_for_roles'                 => 'off',
        'otp_verify_btn_text'           => 'Click here to verify your Phone',
        'default_country_code'          => '254',
        'sa_mobile_pattern'             => '',
        'login_with_otp'                => 'off',
        'login_with_admin_otp'          => 'off',
        'hide_default_login_form'       => 'off',
        'hide_default_admin_login_form' => 'off',
        'registration_msg'              => 'off',
        'admin_registration_msg'        => 'off',
        'admin_low_stock_msg'           => 'off',
        'admin_out_of_stock_msg'        => 'off',
        'reset_password'                => 'off',
        'otp_in_popup'                  => 'off',
        'post_order_verification'       => 'off',
        'pre_order_verification'        => 'off',
        ),
        'smspro_sync'                 => array(
        'last_sync_userId' => '0',
        ),
        'smspro_background_task'      => array(
        'last_updated_lBal_alert' => '',
        ),
        'smspro_background_dBal_task' => array(
        'last_updated_dBal_alert' => '',
        ),
        'smspro_edd_general'          => array(),
        );

        $defaults = apply_filters('sAlertDefaultSettings', $defaults);
        $_POST['smspro_general']['checkout_payment_plans'] = isset($_POST['smspro_general']['checkout_payment_plans']) ? maybe_serialize($_POST['smspro_general']['checkout_payment_plans']) : array();
        $options = array_replace_recursive($defaults, array_intersect_key($_POST, $defaults));

        foreach ( $options as $name => $value ) {
            if ($reset_settings ) {
                delete_option($name, $value);
            } else {
                update_option($name, $value);
            }
        }
        do_action('litespeed_purge_all');
		if ( function_exists('wp_cache_clear_cache') ) {
          wp_cache_clear_cache();
        }
        error_log('admin-post.php: action: save_sms_pro_settings - settings saved successfully');
        wp_safe_redirect(admin_url('admin.php?page=sms-pro&m=1'));
        exit;
    }

    /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
     * @return void
     */
    public static function getSettings()
    {
        global $current_user;
        wp_get_current_user();

        $smspro_name                                = smspro_get_option('smspro_name', 'smspro_gateway', '');
        $smspro_password                            = smspro_get_option('smspro_password', 'smspro_gateway', '');
        $smspro_api                                 = smspro_get_option('smspro_api', 'smspro_gateway', '');
        $has_woocommerce                              = is_plugin_active('woocommerce/woocommerce.php');
        $has_w_p_members                              = is_plugin_active('wp-members/wp-members.php');
        $has_ultimate                                 = ( is_plugin_active('ultimate-member/ultimate-member.php') || is_plugin_active('ultimate-member/index.php') ) ? true : false;
        $has_woocommerce_bookings                     = ( is_plugin_active('woocommerce-bookings/woocommerce-bookings.php') ) ? true : false;
        $has_e_m_bookings                             = ( is_plugin_active('events-manager/events-manager.php') ) ? true : false;
        $has_w_p_a_m                                  = ( is_plugin_active('affiliates-manager/boot-strap.php') ) ? true : false;
        $has_learn_press                              = ( is_plugin_active('learnpress/learnpress.php') ) ? true : false;
        $has_cart_bounty                              = ( is_plugin_active('woo-save-abandoned-carts/cartbounty-abandoned-carts.php') ) ? true : false;
        $has_booking_calendar                         = ( is_plugin_active('booking/wpdev-booking.php') ) ? true : false;
        $sms_admin_phone                              = smspro_get_option('sms_admin_phone', 'smspro_message', '');
        $group_auto_sync                              = smspro_get_option('group_auto_sync', 'smspro_general', '');
        $sms_body_on_hold                             = smspro_get_option('sms_body_on-hold', 'smspro_message', SmsAlertMessages::showMessage('DEFAULT_BUYER_SMS_ON_HOLD'));
        $sms_body_processing                          = smspro_get_option('sms_body_processing', 'smspro_message', SmsAlertMessages::showMessage('DEFAULT_BUYER_SMS_PROCESSING'));
        $sms_body_completed                           = smspro_get_option('sms_body_completed', 'smspro_message', SmsAlertMessages::showMessage('DEFAULT_BUYER_SMS_COMPLETED'));
        $sms_body_cancelled                           = smspro_get_option('sms_body_cancelled', 'smspro_message', SmsAlertMessages::showMessage('DEFAULT_BUYER_SMS_CANCELLED'));
        $sms_body_registration_msg                    = smspro_get_option('sms_body_registration_msg', 'smspro_message', SmsAlertMessages::showMessage('DEFAULT_NEW_USER_REGISTER'));
        $sms_otp_send                                 = smspro_get_option('sms_otp_send', 'smspro_message', SmsAlertMessages::showMessage('DEFAULT_BUYER_OTP'));
        $smspro_notification_checkout_otp           = smspro_get_option('buyer_checkout_otp', 'smspro_general', 'on');
        $smspro_notification_signup_otp             = smspro_get_option('buyer_signup_otp', 'smspro_general', 'on');
        $smspro_notification_login_otp              = smspro_get_option('buyer_login_otp', 'smspro_general', 'on');
        $smspro_notification_reg_msg                = smspro_get_option('registration_msg', 'smspro_general', 'on');
        $smspro_notification_out_of_stock_admin_msg = smspro_get_option('admin_out_of_stock_msg', 'smspro_general', 'on');
        $smspro_allow_multiple_user                 = smspro_get_option('allow_multiple_user', 'smspro_general', 'on');
        $admin_bypass_otp_login                       = maybe_unserialize(smspro_get_option('admin_bypass_otp_login', 'smspro_general', array( 'administrator' )));
        $checkout_show_otp_button                     = smspro_get_option('checkout_show_otp_button', 'smspro_general', 'off');
        $checkout_show_otp_guest_only                 = smspro_get_option('checkout_show_otp_guest_only', 'smspro_general', 'on');

        $checkout_show_country_code = smspro_get_option('checkout_show_country_code', 'smspro_general', 'off');
		$disablePlayground     = SmsAlertUtility::isPlayground()?"disablePlayground":"";
        $enable_selected_country    = smspro_get_option('enable_selected_country', 'smspro_general', 'off');
        $enable_reset_password      = smspro_get_option('reset_password', 'smspro_general', 'off');
        $allow_otp_verification    = smspro_get_option('allow_otp_verification', 'smspro_general', 'off');
        $otp_in_popup      = smspro_get_option('otp_in_popup', 'smspro_general', 'on');
        $otp_verify_btn_text        = smspro_get_option('otp_verify_btn_text', 'smspro_general', 'Click here to verify your Phone');
        $default_country_code       = smspro_get_option('default_country_code', 'smspro_general', '');
        $sa_mobile_pattern          = smspro_get_option('sa_mobile_pattern', 'smspro_general', '');
        $login_with_otp             = smspro_get_option('login_with_otp', 'smspro_general', 'off');
        $login_with_admin_otp      = smspro_get_option('login_with_admin_otp', 'smspro_general', 'off');
        $hide_default_login_form    = smspro_get_option('hide_default_login_form', 'smspro_general', 'off');
        $hide_default_admin_login_form    = smspro_get_option('hide_default_admin_login_form', 'smspro_general', 'off');
        $daily_bal_alert            = smspro_get_option('daily_bal_alert', 'smspro_general', 'on');
        $subscription_reminder_cron_time           = smspro_get_option('subscription_reminder_cron_time', 'smspro_general', '10:00');
        $enable_short_url           = smspro_get_option('enable_short_url', 'smspro_general', 'off');
        $clear_all_data           = smspro_get_option('clear_all_data', 'smspro_general', 'off');
        $auto_sync                  = smspro_get_option('auto_sync', 'smspro_general', 'off');
        $low_bal_alert              = smspro_get_option('low_bal_alert', 'smspro_general', 'on');
        $show_flag              = smspro_get_option('show_flag', 'smspro_general', 'on');
        $low_bal_val                = smspro_get_option('low_bal_val', 'smspro_general', '1000');
        $alert_email                = smspro_get_option('alert_email', 'smspro_general', $current_user->user_email);
        $modal_style                = smspro_get_option('modal_style', 'smspro_general', '');
        $checkout_payment_plans     = maybe_unserialize(smspro_get_option('checkout_payment_plans', 'smspro_general', null));
        $otp_for_selected_gateways  = smspro_get_option('otp_for_selected_gateways', 'smspro_general', 'on');
        $otp_for_roles              = smspro_get_option('otp_for_roles', 'smspro_general', 'on');
        $islogged                   = false;
        $hidden                     = '';
        $credit_show                = 'hidden';
        $smspro_helper            = '';
        if (! empty($smspro_name) && ! empty($smspro_password) ) {
            $credits = SmsAlertcURLOTP::getCredits();
            $isError = ( is_array($credits) && array_key_exists('status', $credits) && 'error' === $credits['status'] ) ? true : false;

            if ($isError || $credits['credit_balance'] < 1) {
                $smspro_helper = sprintf(__('Please contact <a href="mailto:%1$s">%2$s</a> to create or activate your Account.', 'sms-pro'), 'support@softeriatech.com', 'support@softeriatech.com');
            }else{
                $islogged    = true;
                $hidden      = 'hidden';
                $credit_show = '';
            }

            
        } else {
            /* translators: %1$s: SMS Pro website URL, %2$s: Current website URL */
            $smspro_helper = ( ! $islogged ) ? sprintf(__('Please enter below your <a href="%1$s" target="_blank">sms.softeriatech.com</a> login details to link it with %2$s', 'sms-pro'), 'https://sms.softeriatech.com', get_bloginfo()) : '';
        }
        ?>
        <form method="post" id="smspro_form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <div class="SMSPro_box SMSPro_settings_box">
                <div class="SMSPro_nav_tabs">
        <?php
        $params = array(
         'hasWoocommerce'     => $has_woocommerce,
         'hasWPmembers'       => $has_w_p_members,
         'hasUltimate'        => $has_ultimate,
         'hasWPAM'            => $has_w_p_a_m,
         'credit_show'        => $credit_show,
         'hasCartBounty'      => $has_cart_bounty,
         'hasBookingCalendar' => $has_booking_calendar,		
        );
        get_smspro_template('views/smspro_nav_tabs.php', $params);
        ?>
                </div>
                <div>
                    <div class="SMSPro_nav_box SMSPro_nav_global_box SMSPro_active general">
                    <!--general tab-->
        <?php
        $params = array(
         'smspro_helper'   => $smspro_helper,
         'smspro_name'     => $smspro_name,
         'smspro_password' => $smspro_password,
         'hidden'            => $hidden,
         'smspro_api'      => $smspro_api,
         'islogged'          => $islogged,
         'sms_admin_phone'   => $sms_admin_phone,
         'hasWoocommerce'    => $has_woocommerce,
         'hasWPAM'           => $has_w_p_a_m,
         'hasEMBookings'     => $has_e_m_bookings,
         'disablePlayground' => $disablePlayground
        );
        get_smspro_template('views/smspro_general_tab.php', $params);
        ?>
                    </div>
                    <!--/-general tab-->
        <?php
        $tabs = apply_filters('sa_addTabs', array());
        $sno  = 1;
		$not_disable = array('Shortcodes', 'Fluent Form','Form Maker', 'Forminator Form', 'WS Form');
        foreach ( $tabs as $tab ) {
            if (array_key_exists('nav', $tab) ) {
				
                ?>
                    <div class="SMSPro_nav_box SMSPro_nav_<?php echo esc_attr(strtolower(str_replace(' ', '_', $tab['nav']))); ?>_box <?php echo esc_attr(strtolower(str_replace(' ', '_', $tab['nav'])));  ?>">
					
                         <div class="tabset <?php echo !in_array($tab['nav'],$not_disable)?$disablePlayground:''; ?>">
                            <ul>
                <?php foreach ( $tab['inner_nav'] as $in_tab ) { ?>
                            <li>
                                <input type="radio" name="tabset<?php echo esc_attr($sno); ?>" id="tab<?php echo esc_attr(strtolower(str_replace(' ', '_', $in_tab['title'])) . str_replace(' ', '_', $tab['nav'])); ?>" aria-controls="<?php echo esc_attr(strtolower(str_replace(' ', '_', $in_tab['title'])) . str_replace(' ', '_', $tab['nav'])); ?>" <?php echo ( ! empty($in_tab['first_active']) ) ? 'checked' : ''; ?>>
                                <label for="tab<?php echo esc_attr(strtolower(str_replace(' ', '_', $in_tab['title'])) . str_replace(' ', '_', $tab['nav'])); ?>"><?php echo esc_attr($in_tab['title']); ?></label>
                            </li>    
                            
                            
                <?php } ?>
                            <li class="more_tab hide">
                                <a href="#" onclick="return false;"><span class="dashicons dashicons-menu-alt"></span></a>
                                <ul style="display:none"></ul>
                            </li>
                            </ul>
                            <div class="tab-panels">
                <?php
                foreach ( $tab['inner_nav'] as $in_tab ) {
                    ?>
                                <section id="<?php echo esc_attr(strtolower(str_replace(' ', '_', $in_tab['title'])) . str_replace(' ', '_', $tab['nav'])); ?>" class="tab-panel">
                    <?php
                    if (is_array($in_tab['tabContent']) ) {
                        get_smspro_template($in_tab['filePath'], $in_tab['tabContent']);
                    } else {
                        echo ( ! empty($in_tab['tabContent']) ) ? $in_tab['tabContent'] : '';
                    }
                    ?>
                                    <!--help links-->
                    <?php
                                
                    if (isset($in_tab['help_links']) ) {
                                
                        foreach ($in_tab['help_links'] as $link) {
                               echo wp_kses_post('<a href="'.$link['href'].'" alt="'.$link['alt'].'" target="'.$link['target'].'" class="'.$link['class'].'">'.$link['icon']." ".$link['label'].'</a>');
                        }
                    } 
                    ?>
                            <!--/-help links-->
                                </section>
                                                            
                <?php } ?>
                            </div>
                            <!--help links-->
                <?php
                                
                if (!empty($tab['help_links']) ) {
                                
                    foreach ($tab['help_links'] as $link) {
                        echo wp_kses_post('<a href="'.$link['href'].'" alt="'.$link['alt'].'" target="'.$link['target'].'" class="'.$link['class'].'">'.$link['icon']." ".$link['label'].'</a>');
                    }
                } 
                ?>
                            <!--/-help links-->
                            
                        </div>
                    </div>
            <?php } else { ?>
                    <div class="SMSPro_nav_box SMSPro_nav_<?php echo esc_attr($tab['tab_section']); ?>_box <?php echo esc_attr($tab['tab_section']); ?>">
                <?php
                if (is_array($tab['tabContent']) ) {
                    get_smspro_template($tab['filePath'], $tab['tabContent']);
                } else {
                    echo ( ! empty($tab['tabContent']) ) ? $tab['tabContent'] : '';
                }
                ?>
                            
                <?php
                if (!empty($tab['help_links']) ) {
                                
                    foreach ($tab['help_links'] as $links) {
                        foreach ($links as $link) {
                               echo '<a href="'.esc_attr($link['href']).'" alt="'.esc_attr($link['alt']).' target="'.esc_attr($link['target']).'">'.esc_attr($link['text']).'</a>';
                        }
                    }
                } 
                ?>
             </div>
			 
            <?php } $sno++;
        } ?>
                    <div class="SMSPro_nav_box SMSPro_nav_otp_section_box otpsection"><!--otp_section tab-->
        <?php
        $user          = wp_get_current_user();
        $off_excl_role = false;
        if (in_array('administrator', (array) $user->roles, true) ) {
            $user_id       = $user->ID;
            $user_phone    = get_user_meta($user_id, 'billing_phone', true);
            $off_excl_role = empty($user_phone) ? true : false;
        }
        if (! is_array($checkout_payment_plans) ) {
            $checkout_payment_plans = self::getAllGateways();
        }

        $params = array(
         'smspro_notification_checkout_otp' => $smspro_notification_checkout_otp,
         'smspro_notification_signup_otp' => $smspro_notification_signup_otp,
         'smspro_notification_login_otp'  => $smspro_notification_login_otp,
         'has_w_p_members'                  => $has_w_p_members,
         'has_woocommerce'                  => $has_woocommerce,
         'has_ultimate'                     => $has_ultimate,
         'has_w_p_a_m'                      => $has_w_p_a_m,
         'sms_otp_send'                     => $sms_otp_send,
         'login_with_otp'                   => $login_with_otp,
         'login_with_admin_otp'                => $login_with_admin_otp,
         'hide_default_login_form'          => $hide_default_login_form,
         'hide_default_admin_login_form'    => $hide_default_admin_login_form,
         'enable_reset_password'            => $enable_reset_password,
         'otp_in_popup'                     => $otp_in_popup,
         'modal_style'                     => $modal_style  ,
         'has_learn_press'                  => $has_learn_press,
         'otp_for_selected_gateways'        => $otp_for_selected_gateways,
         'checkout_show_otp_button'         => $checkout_show_otp_button,
         'checkout_show_otp_guest_only'     => $checkout_show_otp_guest_only,
         'checkout_show_country_code'       => $checkout_show_country_code,
         'otp_verify_btn_text'              => $otp_verify_btn_text,
         'checkout_payment_plans'           => $checkout_payment_plans,
         'smspro_allow_multiple_user'     => $smspro_allow_multiple_user,
         'otp_for_roles'                    => $otp_for_roles,
         'off_excl_role'                    => $off_excl_role,
         'admin_bypass_otp_login'           => $admin_bypass_otp_login,
		  'disablePlayground'               => $disablePlayground
        );

        get_smspro_template('views/otp-section-template.php', $params);
        ?>
                    </div>
                    <!--/-otp_section tab-->
                    <div class="SMSPro_nav_box SMSPro_nav_callbacks_box callbacks "><!--otp tab-->
                        <!--enable country code -->
                        <div class="cvt-accordion">
                            <div class="accordion-section">
                                <div class="cvt-accordion-body-title" data-href="#accordion_10"> 
                                <input type="checkbox" name="smspro_general[checkout_show_country_code]" id="smspro_general[checkout_show_country_code]" class="notify_box" <?php echo ( ( 'on' === $checkout_show_country_code ) ? "checked='checked'" : '' ); ?>/><label for="smspro_general[checkout_show_country_code]"><?php esc_attr_e('Enable Country Code Selection', 'sms-pro'); ?></label><span class="expand_btn"></span>
                                </div>
								
                                <div id="accordion_10" class="cvt-accordion-body-content" style="height:150px">
                                    <table class="form-table <?php echo $disablePlayground; ?>">
                                        <tr valign="top">
                                            <td class="td-heading" style="width:30%">
                                                <input data-parent_id="smspro_general[checkout_show_country_code]" type="checkbox" name="smspro_general[enable_selected_country]" id="smspro_general[enable_selected_country]" class="notify_box" <?php echo ( ( 'on' === $enable_selected_country ) ? "checked='checked'" : '' ); ?> parent_accordian="callbacks"/><label for="smspro_general[enable_selected_country]"><?php esc_attr_e('Show only selected countries', 'sms-pro'); ?></label>
                                                <span class="tooltip" data-title="Enable Selected Countries before phone field"><span class="dashicons dashicons-info"></span></span>
                                            </td>                                        
                                            <td>
        <?php
        $whitelist_country = (array) smspro_get_option('whitelist_country', 'smspro_general', null);
        $content = '<select name="smspro_general[whitelist_country][]" id="whitelist_country" multiple class="multiselect chosen-select" data-parent_id="smspro_general[enable_selected_country]" parent_accordian="callbacks">';
        foreach ( $whitelist_country as $key => $country_code ) {
            $content .= '<option value="' . esc_attr($country_code) . '" selected="selected"></option>';
        }
        $content .= '</select>';

        $content .= '<script>jQuery(function() {jQuery(".chosen-select").chosen({width: "100%"});});</script>';
        echo $content;
        ?>
                                            </td>
                                        </tr>
          <tr valign="top">
            <td class="td-heading" style="width:30%">
                <input data-parent_id="smspro_general[checkout_show_country_code]" type="checkbox" name="smspro_general[allow_otp_verification]" id="smspro_general[allow_otp_verification]" class="notify_box" <?php echo ( ( 'on' === $allow_otp_verification ) ? "checked='checked'" : '' ); ?> parent_accordian="callbacks"/><label for="smspro_general[allow_otp_verification]"><?php esc_attr_e('Allow OTP Verification', 'sms-pro'); ?></label>
                <span class="tooltip" data-title="Enable Selected Countries before phone field"><span class="dashicons dashicons-info"></span></span>
            </td>                                        
            <td>
            <?php
            $allow_otp_country = (array) smspro_get_option('allow_otp_country', 'smspro_general', null);
            $content = '<select name="smspro_general[allow_otp_country][]" id="allow_otp_country" multiple class="multiselect chosen-select" data-parent_id="smspro_general[allow_otp_verification]" parent_accordian="callbacks">';
            foreach ( $allow_otp_country as $key => $country_code ) {
                $content .= '<option value="' . esc_attr($country_code) . '" selected="selected"></option>';
            }
            $content .= '</select>';
            $content .= '<script>jQuery(function() {jQuery(".chosen-select").chosen({width: "100%"});});</script>';
            echo $content;
            ?>
            </td>
        </tr>                                
         <tr valign="top" >
            <td class="td-heading">
                <input type="checkbox" data-parent_id="smspro_general[checkout_show_country_code]" name="smspro_general[show_flag]" id="smspro_general[show_flag]" class="notify_box" <?php echo ( ( 'on' === $show_flag ) ? "checked='checked'" : '' ); ?> />
                <label for="smspro_general[show_flag]"><?php esc_attr_e('Show Country Flag', 'sms-pro'); ?></label>
                <span class="tooltip" data-title="Show Country Flag"><span class="dashicons dashicons-info"></span></span>
            </td>
        </tr>                              
                                    </table> 
                                </div>
                            </div>
                        </div>    
                        <!--/--enable country code -->                        
                        <div class="cvt-accordion <?php echo $disablePlayground; ?>" style="padding: 0px 10px 10px 10px;">
                        <style>.top-border{border-top:1px dashed #b4b9be;}</style>
						
                        <table class="form-table">
                            <tr valign="top">
                                <td scope="row" class="td-heading"><?php esc_attr_e('Default Country', 'sms-pro'); ?>
                                </td>
                                <td>
        <?php
        $default_country_code = smspro_get_option('default_country_code', 'smspro_general');
        $content              = '<select name="smspro_general[default_country_code]" id="default_country_code" onchange="choseMobPattern(this)">';
        $content .= '<option value="' . esc_attr($default_country_code) . '" selected="selected">Loading...</option>';
        $content .= '</select>';
        echo $content;
        ?>
                                    <span class="tooltip" data-title="Default Country for mobile number format validation"><span class="dashicons dashicons-info"></span></span>
                                    <input type="hidden" name="smspro_general[sa_mobile_pattern]" id="sa_mobile_pattern" value="<?php echo esc_attr($sa_mobile_pattern); ?>"/>
                                </td>
                            </tr>                            
                            <style>
                            .otp .tags-input-wrapper {float:left;}
                            </style>
                            <tr valign="top" class="top-border">
                                <td scope="row" class="td-heading"><?php esc_attr_e('Alerts', 'sms-pro'); ?>
                                </td>
                                <td>
                                    <input type="text" name="smspro_general[alert_email]" class="admin_email " id="smspro_general[alert_email]" value="<?php echo esc_attr($alert_email); ?>" style="width: 40%;" parent_accordian="callbacks">

                                    <span class="tooltip" data-title="Send Alerts for low balance & daily balance etc."><span class="dashicons dashicons-info"></span></span>
                                </td>
                            </tr>
                            <tr valign="top">
                                <td scope="row"> </td>
                                <td class="td-heading">
                                    <input type="checkbox" name="smspro_general[low_bal_alert]" id="smspro_general[low_bal_alert]" class="SMSPro_box notify_box" <?php echo ( ( 'on' === $low_bal_alert ) ? "checked='checked'" : '' ); ?> />
                                    <label for="smspro_general[low_bal_alert]"><?php esc_attr_e('Low Balance Alert', 'sms-pro'); ?></label> <input type="number" min="100" name="smspro_general[low_bal_val]" id="smspro_general[low_bal_val]" data-parent_id="smspro_general[low_bal_alert]" value="<?php echo esc_attr($low_bal_val); ?>" parent_accordian="otp">
                                    <span class="tooltip" data-title="Set Low Balance Alert"><span class="dashicons dashicons-info"></span></span>
                                </td>
                            </tr>
                            <tr valign="top">
                                <td scope="row"> </td>
                                <td class="td-heading">
                                    <input type="checkbox" name="smspro_general[daily_bal_alert]" id="smspro_general[daily_bal_alert]" class="notify_box" <?php echo ( ( 'on' === $daily_bal_alert ) ? "checked='checked'" : '' ); ?> />
                                    <label for="smspro_general[daily_bal_alert]"><?php esc_attr_e('Daily Balance Alert', 'sms-pro'); ?></label>
                                    <span class="tooltip" data-title="Set Daily Balance Alert"><span class="dashicons dashicons-info"></span></span>
                                </td>
                            </tr>
                        
                            <!--Time for sending SMS Notification-->
        <?php
        if (is_plugin_active('membermouse/index.php') || is_plugin_active('woocommerce-subscriptions/woocommerce-subscriptions.php') || is_plugin_active('wpadverts/wpadverts.php') || is_plugin_active('paid-memberships-pro/paid-memberships-pro.php')) {
            ?>
                                    <tr valign="top" class="top-border">
                                <th scope="row">
                                        <label for="smspro_general[subscription_reminder_cron_time]"><?php esc_html_e('Cron run time for reminder notification:', 'sms-pro'); ?></label>
                                    </th>
                                    <td>
                                    <input type="time" name="smspro_general[subscription_reminder_cron_time]" id="smspro_general[subscription_reminder_cron_time]" value="<?php echo esc_attr($subscription_reminder_cron_time); ?>" ><span class="tooltip" data-title="Time to send out the reminder notification"><span class="dashicons dashicons-info"></span></span>
                                        </td>
                                </tr>
            <?php
        }     
        ?>
    
                            <!--enable shorturl-->
                            <tr valign="top" >
                                <td scope="row"> </td>
                                <td class="td-heading">
                                    <input type="checkbox" name="smspro_general[enable_short_url]" id="smspro_general[enable_short_url]" class="notify_box" <?php echo ( ( 'on' === $enable_short_url ) ? "checked='checked'" : '' ); ?> />
                                        <label for="smspro_general[enable_short_url]"><?php esc_attr_e('Enable Short Url', 'sms-pro'); ?></label>
                                    <span class="tooltip" data-title="Enable Short Url"><span class="dashicons dashicons-info"></span></span>
                                </td>
                            </tr>
            
                            <!--/-enable shorturl-->
          <?php //if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) { ?>
                            <tr valign="top">
                                <td scope="row"> </td>
                                <td class="td-heading">
                                    <input type="checkbox" name="smspro_general[auto_sync]" id="smspro_general[auto_sync]" class="SMSPro_box sync_group" <?php echo ( ( 'on' === $auto_sync ) ? "checked='checked'" : '' ); ?> /> <label for="smspro_general[auto_sync]"><?php esc_attr_e('Sync Customers To Group', 'sms-pro'); ?></label>
                                    <?php $groups = SmsAlertcURLOTP::groupList();?>
    
                                    <select name="smspro_general[group_auto_sync]" data-parent_id="smspro_general[auto_sync]" id="group_auto_sync">
                                    <?php
                                    if (!empty($groups)) {
                                        if (! is_array($groups['data'])) {
                                            ?> <option value=""><?php esc_attr_e('SELECT', 'sms-pro'); ?></option>  <?php
                                        } else {
                                            foreach ( $groups['data'] as $group ) {
                                                ?>
                                            <option value="<?php echo esc_attr($group['name']); ?>" <?php echo ( trim($group_auto_sync) === $group['name'] ) ? 'selected="selected"' : ''; ?>><?php echo esc_attr($group['name']); ?></option>
                                                <?php
                                            }
                                        }
                                    }
                                    ?>
                                    </select>
            <?php
            if (! empty($groups) && ( ! is_array($groups['data'])) && $islogged ) {
                ?>
                                        <a href="#" onclick="create_group(this);" id="create_group" data-parent_id="smspro_general[auto_sync]" style="text-decoration: none;"><?php esc_attr_e('Create Group', 'sms-pro'); ?></a>
                <?php
            } elseif ('on' === $auto_sync && '' !== $group_auto_sync && '0' !== $group_auto_sync ) {
                ?>
                                        <input type="button" id="smspro_sync_btn" data-parent_id="smspro_general[auto_sync]" onclick="doSASyncNow(this)" class="button button-primary" value="Sync Now" disabled>
                <?php
            }
            ?>
                                    <span class="tooltip" data-title="<?php _e('Sync users to a Group in sms.softeriatech.com', 'sms-pro'); ?>"><span class="dashicons dashicons-info"></span></span>
                                    <span id="sync_status" style="opacity:0;margin-left: 20px;">
            <?php
            /* translators: %s: Number of contacts synced in group */
            echo esc_html(sprintf(__('%s contacts synced', 'sms-pro'), '0'));
            ?>
                                    </span>
                                    <div id="sa_progressbar"></div>
                                </td>
                            </tr>
                            <!--reset all settings-->
                            
                            <tr valign="top" class="top-border">
                                <td scope="row" class="td-heading" style="vertical-align: top;padding-top: 15px;"><?php esc_attr_e('Danger Zone', 'sms-pro'); ?></td>
                                <td class="td-heading">
                                <input type="checkbox" name="smspro_general[clear_all_data]" id="smspro_general[clear_all_data]" class="notify_box" <?php echo ( ( 'on' === $clear_all_data ) ? "checked='checked'" : '' ); ?> />
                                        <label for="smspro_general[clear_all_data]"><?php esc_attr_e('After uninstalled SMS Pro, delete its related data from database.', 'sms-pro'); ?></label>
                                <input type="checkbox" name="smspro_reset_settings" id="smspro_reset_btn" class="SMSPro_box notify_box hide smspro_reset" />
                                    <p><?php esc_attr_e('Once you reset templates, there is no going back. Please be certain.', 'sms-pro'); ?></p><br/>
                                    <input type="button" name="smspro_reset_setting_btn" id="smspro_reset_settings" class="SMSPro_box notify_box button button-danger" value="<?php esc_attr_e('Reset all Templates & Settings', 'sms-pro'); ?>"/>
                                    <span class="tooltip" data-title="Reset All Settings"><span class="dashicons dashicons-info"></span></span>
                                </td>
                            </tr>
                            <!--/-reset all settings-->
          <?php //} ?>
                        </table>
                        </div>
                    </div><!--/-otp tab-->
                    <div class="SMSPro_nav_box SMSPro_nav_credits_box credits <?php echo esc_attr($credit_show); ?>">        <!--credit tab-->
                        <div class="cvt-accordion" style="padding: 0px 10px 10px 10px;">
                            <table class="form-table">
                                <tr valign="top">
                                    <td>
            <?php
            if ($islogged ) {
                echo '<h2><strong>'.__('SMS Credits', 'sms-pro').'</strong></h2>';
                    ?>
                        <div class="col-lg-12 creditlist" >
                            <div class="col-lg-8 route">
                                <h3><span class="dashicons dashicons-bank"></span> <?php echo esc_attr($credits['credit_balance']); ?> <?php esc_attr_e('Credits', 'sms-pro'); ?></h3>                                
                            </div>
                            <div class="col-lg-4 credit">
                                <h3><span class="dashicons dashicons-email"></span> <?php echo esc_attr(ucwords($credits['rate'])); ?>/SMS</h3>
                            </div>
                        </div>
                    <?php
            }
            ?>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <td>
                                        <p><b><?php esc_attr_e('Need More credits?', 'sms-pro'); ?></b>
             <?php
                /* translators: %s: SMS Pro Pricing URL */
                echo wp_kses_post(sprintf(__('<a href="%s" target="_blank">Click Here</a> to purchase. ', 'sms-pro'), 'https://sms.softeriatech.com/partner/auth/'.smspro_get_option('smspro_password', 'smspro_gateway')));
                ?>
                                        </p>    
                                    </td>
                                </tr>
                            </table>
                        </div>
                        </div><!--/-credit tab-->
                    <div class="SMSPro_nav_box SMSPro_nav_support_box support"><!--support tab-->
         <?php get_smspro_template('views/support.php', array()); ?>
                    </div><!--/-support tab-->
                    <script>
                    jQuery('.more_tab a').click(function(){
                        jQuery(this).next().toggle();                    
                    });    
                    /*tagged input start*/
                    // Email Alerts
                    var adminemail     = "<?php echo esc_attr($alert_email); ?>";
                    var tagInput2     = new TagsInput({
                        selector: 'smspro_general[alert_email]',
                        duplicate : false,
                        max : 10,
                    });
                    var email = (adminemail!='') ? adminemail.split(",") : [];
                    if (email.length >= 1){
                        tagInput2.addData(email);
                    }
                    //Send Admin SMS To
        <?php if ($islogged ) { ?>
                    var adminnumber = "<?php echo esc_attr($sms_admin_phone); ?>";
                    var tagInput1     = new TagsInput({
                        selector: 'smspro_message[sms_admin_phone]',
                        duplicate : false,
                        max : 10,
                    });
                    var number = (adminnumber!='') ? adminnumber.split(",") : [];
                    if (number.length > 0) {
                        tagInput1.addData(number);
                    }
        <?php } ?>
                    /*tagged input end*/
                    // on checkbox enable-disable select
                    function choseMobPattern(obj){
                        var pattern = jQuery('option:selected', obj).attr('data-pattern');
                        jQuery('#sa_mobile_pattern').val(pattern);
                    }
                    </script>
                </div>
            </div>
            <input type="hidden" name="action" value="save_sms_pro_settings" />
            <p class="submit"><input type="submit" id="smspro_bckendform_btn" class="button button-primary" value="Save Changes" /></p>
        </form>
        <!--reset modal-->
        <?php
        $params = array(
        'modal_id'     => 'smspro_reset_style_modal',
        'modal_title'  => __('Are you sure?', 'sms-pro'),
        'modal_body'   => __('This action can not be reversed. Default style will be set.', 'sms-pro'),
        'modal_footer' => '<button type="button" data-dismiss="sa-modal" class="button button-danger" id="sconfirmed">Yes</button>
				<button type="button" data-dismiss="sa-modal" class="button button-primary btn_cancel">No</button>',
        );
        get_smspro_template('views/alert-modal.php', $params);
        $params = array(
        'modal_id'     => 'smspro_reset_modal',
        'modal_title'  => __('Are you sure?', 'sms-pro'),
        'modal_body'   => __('This action can not be reversed. You will be logged out of SMS Pro plugin.', 'sms-pro'),
        'modal_footer' => '<button type="button" data-dismiss="sa-modal" class="button button-danger" id="confirmed">Yes</button>
				<button type="button" data-dismiss="sa-modal" class="button button-primary btn_cancel">No</button>',
        );
        get_smspro_template('views/alert-modal.php', $params);
        add_action('admin_footer', array( 'SAVerify', 'add_shortcode_popup_html' )); 
        wp_localize_script(
            'admin-smspro-scripts',
            'alert_msg',
            array(
            'is_playground'             => SmsAlertUtility::isPlayground(),
            'otp_error'             => __('Please add OTP tag in OTP Template.', 'sms-pro'),
            'payment_gateway_error' => __('Please choose any payment gateway.', 'sms-pro'),
            'invalid_email'         => __('You have entered an invalid email address in Advanced Settings option!', 'sms-pro'),
            'invalid_sender'        => __('Please choose your senderid.', 'sms-pro'),
            'low_alert'             => __('Value must be greater than or equal to 100.', 'sms-pro'),
            'wcountry_err'          => __('Please choose any country.', 'sms-pro'),
            'dcountry_err'          => __('Please choose default country from selected countries', 'sms-pro'),
            'last_item'             => __('last Item Cannot be deleted.', 'sms-pro'),
            'global_country_err'             => __('You will have to enable Country Code Selection because you have selected global country.', 'sms-pro')
            )
        );
        ?>
        <!--Choose otp token  modal-->
        <?php
        $params = array(
        'modal_id'     => 'sa_backend_modal',
        'modal_title'  => __('Alert', 'sms-pro'),
        'modal_body'   => '',
        'modal_footer' => '<button type="button" data-dismiss="sa-modal" class="button button-primary btn_cancel">OK</button>',
        );
        get_smspro_template('views/alert-modal.php', $params);
        ?>
        <!--/-Choose otp token  modal-->
        <?php
        $show_dlt_modal = false;
        // if (! empty($credits) ) {
        //     if (is_array($credits['description']) && array_key_exists('routes', $credits['description']) ) {
        //         foreach ( $credits['description']['routes'] as $credit ) {
        //             if (strtolower($credit['route']) === 'demo' ) {
        //                 $default_country_code = smspro_get_option('default_country_code', 'smspro_general');
        //                 if ('91' === $default_country_code ) {
        //                     $show_dlt_modal = true;
        //                     break;
        //                 }
        //             }
        //         }
        //     }
        // }
        wp_localize_script(
            'admin-smspro-scripts',
            'sa_admin_settings',
            array(
            'show_dlt_modal' => $show_dlt_modal,
            'variable_err'   => __('*Please replace {#var#} with plugin variables.', 'sms-pro'),                /* translators: %1%s: Reset template text, %2%s: line break, %3%s: DLT Help URL */
            'show_dlt_text'  => sprintf(__('*Changing of SMS text is not allowed in Demo. This message may not get Delivered <a href="#" onclick="return false;" class="reset_text">%1$s</a>.%2$sIndian users need to register on DLT to use SMS Services. <a href="%3$s" target="_blank">Know more</a>', 'sms-pro'), 'Reset this Template', '<br/>', 'https://sms.softeriatech.com/dlt'),
            )
        );
        ?>
        <script>
        var isSubmitting = false;        
        function showAlertModal(msg)
        {
            jQuery("#sa_backend_modal").addClass("sa-show");
            jQuery("#sa_backend_modal").find(".sa-modal-body").text(msg);
            jQuery("#sa_backend_modal").after('<div class="sa-modal-backdrop sa-fade"></div>');
            jQuery(".sa-modal-backdrop").addClass("sa-show");            
        }

        jQuery('#smspro_bckendform_btn').click(function(){
            jQuery(".SMSPro_nav_box").find(".hasError").removeClass("hasError");
            jQuery(".SMSPro_nav_box").find(".hasErrorField").removeClass("hasErrorField");
            jQuery("#sa_backend_modal").find(".modal_body").text("");            
            var payment_plans = jQuery('#checkout_payment_plans :selected').map((_,e) => e.value).get();            
            var whitelist_countries = jQuery('#whitelist_country :selected').map((_,e) => e.value).get();    
            jQuery('select').removeAttr('disabled',false);            
            isSubmitting = true; 
			if (alert_msg.is_playground){
				var url     = jQuery("#smspro_form").attr('action');
				var hash     = window.location.hash;
				jQuery('#smspro_form').attr('action', url+hash);
				jQuery('#smspro_form').submit();
			}
			else {				
            if (jQuery('[name="smspro_gateway[smspro_api]"]').val()=='SELECT' || jQuery('[name="smspro_gateway[smspro_api]"]').val()=='')
            {
                showAlertModal(alert_msg.invalid_sender);
                var menu_accord = jQuery('[name="smspro_gateway[smspro_api]"]').attr("parent_accordian");
                jQuery('[name="smspro_gateway[smspro_api]"]').addClass("hasErrorField");
                jQuery('[name="smspro_gateway[smspro_api]"]').parents(".SMSPro_nav_box").addClass("hasError").attr("menu_accord",menu_accord);                
                jQuery('[tab_type=global]').trigger('click');
                window.location.hash = '#general';
                return false;
            } else if ((jQuery('[name="smspro_general[default_country_code]"]').val() == '' && !jQuery('[name="smspro_general[checkout_show_country_code]"]').prop("checked")))
            {
                showAlertModal(alert_msg.global_country_err);                
                var menu_accord = jQuery('[name="smspro_general[checkout_show_country_code]"]').attr("parent_accordian");
                jQuery('[name="smspro_general[checkout_show_country_code]"]').addClass("hasErrorField");
                jQuery('[name="smspro_general[checkout_show_country_code]"]').parents(".SMSPro_nav_box").addClass("hasError").attr("menu_accord",menu_accord);
                return false;    
            } else if (!(jQuery('[name="smspro_general[low_bal_val]"]').val() >= 100))
            {
                showAlertModal(alert_msg.low_alert);                
                var menu_accord = jQuery('[name="smspro_general[low_bal_val]"]').attr("parent_accordian");
                jQuery('[name="smspro_general[low_bal_val]"]').addClass("hasErrorField");
                jQuery('[name="smspro_general[low_bal_val]"]').parents(".SMSPro_nav_box").addClass("hasError").attr("menu_accord",menu_accord);
                jQuery('[tab_type=callbacks]').trigger('click');
                window.location.hash = '#otp';                
                return false;    
            } else if (jQuery('[name="smspro_message[sms_otp_send]"]').val() =='' || jQuery('[name="smspro_message[sms_otp_send]"]').val().match(/\[otp.*?\]/i)==null)
            {
                showAlertModal(alert_msg.otp_error);
                var menu_accord = jQuery('[name="smspro_message[sms_otp_send]"]').attr("parent_accordian");
                jQuery('[name="smspro_message[sms_otp_send]"]').addClass("hasErrorField");
                jQuery('[name="smspro_message[sms_otp_send]"]').parents(".SMSPro_nav_box").addClass("hasError").attr("menu_accord",menu_accord);
                return false;
            } else if (jQuery('[name="smspro_general[buyer_checkout_otp]"]').prop("checked") && jQuery('[name="smspro_general[otp_for_selected_gateways]"]').prop("checked") && payment_plans.length==0)
            {
                showAlertModal(alert_msg.payment_gateway_error);                
                var menu_accord = jQuery('[name="smspro_general[otp_for_selected_gateways]"]').attr("parent_accordian");
                var payment_plans = jQuery('[name="smspro_general[otp_for_selected_gateways]"]').parents(".SMSPro_nav_box").find("#checkout_payment_plans_chosen");                
                payment_plans.find(".chosen-choices").addClass("hasErrorField");
                payment_plans.parents(".SMSPro_nav_box").addClass("hasError").attr("menu_accord",menu_accord);
                return false;
            } else if (jQuery('[name="smspro_general[checkout_show_country_code]"]').prop("checked") && jQuery('[name="smspro_general[enable_selected_country]"]').prop("checked") && whitelist_countries.length==0)
            {
                showAlertModal(alert_msg.wcountry_err);                
                var menu_accord = jQuery('#whitelist_country').attr("parent_accordian");
                var whitelist_country = jQuery('#whitelist_country').parents(".SMSPro_nav_box").find("#whitelist_country_chosen");                
                whitelist_country.find(".chosen-choices").addClass("hasErrorField");
                whitelist_country.parents(".SMSPro_nav_box").addClass("hasError").attr("menu_accord",menu_accord);
                return false;
            } else if (jQuery('[name="smspro_general[checkout_show_country_code]"]').prop("checked") && jQuery('[name="smspro_general[enable_selected_country]"]').prop("checked") && jQuery("#default_country_code").val() !== '' && jQuery.inArray( jQuery("#default_country_code").val(), whitelist_countries )==-1)
            {
                showAlertModal(alert_msg.dcountry_err);                
                var menu_accord = jQuery('[name="smspro_general[whitelist_country]"]').attr("parent_accordian");
                var default_country_code = jQuery("#default_country_code");
                default_country_code.addClass("hasErrorField");
                default_country_code.focus();
                return false;
            } else if (jQuery('[name="smspro_general[alert_email]"]').val() != '')
            {
                var alert_email = jQuery('[name="smspro_general[alert_email]"]');
                var inputText = alert_email.val();
                var email = inputText.split(',');

                for (i = 0; i < email.length; i++) {
                    var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w+)+$/;
                    if (!email[i].match(mailformat)) {
                        showAlertModal(alert_msg.invalid_email);                        
                        alert_email.parent().find(".tags-input-wrapper").addClass("hasErrorField");
                        //jQuery('[tab_type=callbacks]').trigger('click');
                        var menu_accord = jQuery('[name="smspro_general[alert_email]"]').attr("parent_accordian");
                        jQuery('[name="smspro_general[alert_email]"]').parents(".SMSPro_nav_box").addClass("hasError").attr("menu_accord",menu_accord);
                        return false;
                    }
                }
				
				
            } else if (jQuery('#smspro_form')[0].checkValidity()) {
                var url     = jQuery("#smspro_form").attr('action');
                var hash     = window.location.hash;
                jQuery('#smspro_form').attr('action', url+hash);
                jQuery('#smspro_form').submit();
            }
			}
        });

        //check before leave page
        jQuery('form').data('initial-state', jQuery('form').serialize());

        jQuery(window).on('beforeunload', function() {
            if (!isSubmitting && jQuery('form').serialize() != jQuery('form').data('initial-state')){
                return 'You have unsaved changes which will not be saved.';
            }
        });
        </script>
        <script>
        //add token variable on admin and customer template 21/07/2020
        window.addEventListener('message', receiveMessage, false);
        function receiveMessage(evt) {
            if (evt.data.type=='smspro_token')
            {
                var txtbox_id =  jQuery('.cvt-accordion-body-content.open').find('textarea').attr('id');
                insertAtCaret(evt.data.token, txtbox_id);
                tb_remove();
            }
        }
        </script>
        <?php
        return apply_filters('wc_sms_pro_setting', array());
    }

    /**
     * Verifies if SMS Pro credentials are correct.
     *
     * @param string $value Value.
     *
     * @return void
     */
    public static function actionWoocommerceAdminFieldVerifySmsAlertUser( $value )
    {
        global $current_user;
        wp_get_current_user();
        $smspro_name     = smspro_get_option('smspro_name', 'smspro_gateway', '');
        $smspro_password = smspro_get_option('smspro_password', 'smspro_gateway', '');
        $hidden= '';
        if (!empty($smspro_password) ) {
            $credits = SmsAlertcURLOTP::getCredits();
            if (!(array_key_exists('status', $credits) && $credits['status']=='error')) {
                $hidden = 'hidden';
            }
        }		
        ?>
            <tr valign="top" class="<?php echo esc_attr($hidden); ?>">
                <th>&nbsp;</th>
                <td>
				<?php  if (SmsAlertUtility::isPlayground()) { ?>
                    <a href="#" class="button-primary woocommerce-save-button" onclick="verifyUser(this); return false;"><?php esc_attr_e('verify and continue', 'sms-pro'); ?></a>
				<?php } else { ?>
				<a href="#" class="button-primary woocommerce-save-button" onclick="verifyUser(this); return false; "><?php esc_attr_e('verify and continue', 'sms-pro'); ?></a>
		<?php }
        $link = 'https://sms.softeriatech.com/register?name=' . rawurlencode($current_user->user_firstname . ' ' . $current_user->user_lastname) . '&email=' . rawurlencode($current_user->user_email) . '&phone=&username=' . preg_replace('/\s+/', '_', strtolower(get_bloginfo())) . '#register';
        /* translators: %s: SMS Pro Signup URL */
        echo wp_kses_post(sprintf(__('Don\'t have an account on SMS Pro? <a href="%s" target="_blank">Signup Here for FREE</a> ', 'sms-pro'), $link));
        ?>
                <div id="verify_status"></div>
                </td>
            </tr>
        <?php
    }
}
smspro_Setting_Options::init();