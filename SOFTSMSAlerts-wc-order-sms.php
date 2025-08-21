<?php
/**
 * SMS Notifications, alerts and OTP for Activities, By https://softeriatech.com
 * PHP version 5
 * @category Helper
 * @package  SOFTSMSAlerts
 * @author   Softeria Tech <billing@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 * Plugin Name: SOFT SMS Alerts
 * Plugin URI: https://wordpress.org/plugins/soft-sms-alerts/
 * Description: SMS Notifications, alerts and OTP for Activities, By https://sms.softeriatech.com
 * Version: 1.0.1
 * Tested up to: 6.1.1
 * Stable tag: 1.0.1
 * Author: Softeria Tech Ltd.
 * Author URI: https://sms.softeriatech.com
 * Text Domain: softeria-sms-alerts
 * License: GNU GPL
 */


if (! defined('ABSPATH') ) {
    exit;
}
if (! defined('SOFTERIA_ALERTS_TEXT_DOMAIN') ) {
    define('SOFTERIA_ALERTS_TEXT_DOMAIN', 'softeria-sms-alerts');
}
if (! defined('SOFTERIA_ALERTS_PLUGIN_NAME') ) {
    define('SOFTERIA_ALERTS_PLUGIN_NAME', 'Softeria Tech Order Notifications – WooCommerce');
}
if (! defined('SOFTERIA_ALERTS_ABANDONED') ) {
    define('SOFTERIA_ALERTS_ABANDONED', 'softeria_alerts_abandoned');
}
if (! defined('SOFTERIA_ALERTS_PLUGIN_NAME_SLUG') ) {
    define('SOFTERIA_ALERTS_PLUGIN_NAME_SLUG', 'softeria-sms-alerts');
}
if (! defined('CHECKOUT_VIEW_NAME') ) {
    define('CHECKOUT_VIEW_NAME', 'captured_wc_input');
}
if (! defined('CHECKOUT_JOB_SCHECDULE') ) {
    define('CHECKOUT_JOB_SCHECDULE', 10);
}
if (! defined('BOOKING_SCHECDULE_REMINDER') ) {
    define('BOOKING_SCHECDULE_REMINDER', 10);
}

if (! defined('SHOPPING_INPROGRESS') ) {
    define('SHOPPING_INPROGRESS', 10);
}
if (! defined('CART_STATUS_CHANGED') ) {
    define('CART_STATUS_CHANGED', 240);
}

if (! defined('SHOPPING_KEY') ) {
    define('SHOPPING_KEY', 'smsProSoft3@r!atEchLim1t3d');
}

add_action(
    'before_woocommerce_init', function () {
        if (wp_doing_ajax() ) {
            return;
        }
        if (class_exists('Automattic\\WooCommerce\\Utilities\\FeaturesUtil') && method_exists('Automattic\\WooCommerce\\Utilities\\FeaturesUtil', 'declare_compatibility') ) {
        
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', plugin_basename(__FILE__), true);
        }
    }
);

function softeria_alerts_sanitize_array( $arr )
{
    global $wp_version;
    $older_version = ( $wp_version < '4.7' ) ? true : false;
    if (! is_array($arr) ) {
        return ( ( $older_version ) ? stripcslashes(sanitize_text_field($arr)) : stripcslashes(sanitize_textarea_field($arr)) );
    }

    $result = array();
    foreach ( $arr as $key => $val ) {
        $result[ $key ] = is_array($val) ? softeria_alerts_sanitize_array($val) : ( ( $older_version ) ? stripcslashes(sanitize_text_field($val)) : stripcslashes(sanitize_textarea_field($val)) );
    }

    return $result;
}

function create_softeria_alerts_cookie( $cookie_key, $cookie_value )
{
    ob_start();
    setcookie($cookie_key, $cookie_value, time() + ( 15 * 60 ));
    ob_get_clean();
}

function clear_softeria_alerts_cookie( $cookie_key )
{
    if (isset($_COOKIE[ $cookie_key ]) ) {
        unset($_COOKIE[ $cookie_key ]);
        setcookie($cookie_key, '', time() - ( 15 * 60 ));
    }
}


function get_softeria_alerts_cookie( $cookie_key )
{
    if (! isset($_COOKIE[ $cookie_key ]) ) {
        return false;
    } else {
        return sanitize_text_field(wp_unslash($_COOKIE[ $cookie_key ]));
    }
}

function softeria_alerts_get_option( $option, $section, $default = '' )
{
    $options = get_option($section);

    if (isset($options[ $option ]) ) {
        return $options[ $option ];
    }
    return $default;
}

function get_softeria_alerts_template( $filepath, $datas, $ret = false )
{
    if ($ret ) {
        ob_start();
    }
    extract($datas);
    include $filepath;
    if ($ret ) {
        return ob_get_clean();
    }
}


class softeriaAlerts_WC_Order_SMS
{

    public function __construct()
    {
        // Instantiate necessary class.
        
        $this->instantiate();
        
        add_action('init', array( $this, 'registerHookSendSms' ));

        add_action('woocommerce_checkout_update_order_meta', array( $this, 'buyerNotificationUpdateOrderMeta' ));
        add_action('woocommerce_order_status_changed', array( 'WooCommerceCheckOutForm', 'trigger_after_order_place' ), 10, 3);
        add_action('woocommerce_checkout_order_processed', array( $this, 'saWcOrderPlace' ), 10, 1);
        if (!did_action('woocommerce_checkout_order_processed') && is_admin()) {
            add_action('woocommerce_new_order', array( $this, 'saWcOrderPlace' ), 10, 1);
        }
        add_filter('sa_wc_order_sms_customer_before_send', array( 'WooCommerceCheckOutForm', 'pharseSmsBody' ), 10, 2);
        add_filter('sa_wc_order_sms_admin_before_send', array( 'WooCommerceCheckOutForm', 'pharseSmsBody' ), 10, 2);
        add_action('woocommerce_new_customer_note', array( 'WooCommerceCheckOutForm', 'trigger_new_customer_note' ), 10);
        add_filter('default_checkout_billing_phone', array( $this, 'modifyBillingPhoneField' ), 1, 2); 
        add_action('user_register', array( $this, 'wcUserCreated' ), 1, 1);
        add_action('softeria_alerts_after_update_new_user_phone', array( $this, 'smsproAfterUserRegister' ), 10, 2);

        include_once 'helper/formlist.php';
        include_once 'views/common-elements.php';
        include_once 'handler/forms/FormInterface.php';
        include_once 'handler/softeria_alerts_form_handler.php';
        include_once 'helper/shortcode.php';

        if (is_admin() ) {
            add_action('admin_enqueue_scripts', array( $this, 'adminEnqueueScripts' ));
            add_filter('plugin_row_meta', array( $this, 'pluginRowMetaLink' ), 10, 4);
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'addActionLinks' ));
        }

        add_action('softeria_alerts_balance_notify', array( $this, 'backgroundTask' ));
        self::saSyncGrpAction();
        add_filter('sa_before_send_sms', array( $this, 'replaceCommonTokenName' ), 100, 1);
        add_action('admin_init', array($this, 'smsproPluginRedirect'));
        add_action('sa_addTabs', array( $this, 'addTabs' ), 10);
        add_filter('sAlertDefaultSettings', array( $this, 'addDefaultSetting' ), 1);
    }
    
    
    public function modifyBillingPhoneField( $value, $input )
    {
        if ('billing_phone' === $input && ! empty($value) ) {
            return SmsAlertUtility::formatNumberForCountryCode($value);
        }
    }
  
    public function wcUserCreated( $user_id )
    {
        $billing_phone = ( ! empty($_POST['billing_phone']) ) ? sanitize_text_field(wp_unslash($_POST['billing_phone'])) : null;
        $billing_phone = apply_filters('sa_get_user_phone_no', $billing_phone, $user_id);
        $billing_phone = SmsAlertcURLOTP::checkPhoneNos($billing_phone);
        update_user_meta($user_id, 'billing_phone', $billing_phone);
        do_action('softeria_alerts_after_update_new_user_phone', $user_id, $billing_phone);
    }
   
    public function smsproAfterUserRegister( $user_id, $billing_phone )
    {
        $user                = get_userdata($user_id);
        $role                = ( ! empty($user->roles[0]) ) ? $user->roles[0] : '';
        $role_display_name   = ( ! empty($role) ) ? self::get_user_roles($role) : '';
        $softeria_alerts_reg_notify = softeria_alerts_get_option('wc_user_roles_' . $role, 'softeria_alerts_signup_general', 'off');
        $sms_body_new_user   = softeria_alerts_get_option('signup_sms_body_' . $role, 'softeria_alerts_signup_message', SmsAlertMessages::showMessage('DEFAULT_NEW_USER_REGISTER'));

        $softeria_alerts_reg_admin_notify = softeria_alerts_get_option('admin_registration_msg', 'softeria_alerts_general', 'off');
        $sms_admin_body_new_user   = softeria_alerts_get_option('sms_body_registration_admin_msg', 'softeria_alerts_message', SmsAlertMessages::showMessage('DEFAULT_ADMIN_NEW_USER_REGISTER'));
        $admin_phone_number        = softeria_alerts_get_option('sms_admin_phone', 'softeria_alerts_message', '');

        $store_name = trim(get_bloginfo());

        if ('on' === $softeria_alerts_reg_notify && ! empty($billing_phone) ) {
            $search = array(
            '[username]',
            '[store_name]',
            '[email]',
            '[billing_phone]',
            '[role]',
            );

            $replace           = array(
            $user->user_login,
            $store_name,
            $user->user_email,
            $billing_phone,
            $role_display_name,
            );
            $sms_body_new_user = str_replace($search, $replace, $sms_body_new_user);
            // do_action( 'sa_send_sms', $billing_phone, $sms_body_new_user ); //commented on 25-08-2021
            $obj             = array();
            $obj['number']   = $billing_phone;
            $obj['sms_body'] = $sms_body_new_user;
            SmsAlertcURLOTP::sendsms($obj);
        }

        if ('on' === $softeria_alerts_reg_admin_notify && ! empty($admin_phone_number) ) {
            $search = array(
            '[username]',
            '[store_name]',
            '[email]',
            '[billing_phone]',
            '[role]',
            );

            $replace = array(
            $user->user_login,
            $store_name,
            $user->user_email,
            $billing_phone,
            $role_display_name,
            );

            $sms_admin_body_new_user = str_replace($search, $replace, $sms_admin_body_new_user);
            $nos                     = explode(',', $admin_phone_number);
            $admin_phone_number      = array_diff($nos, array( 'postauthor', 'post_author' ));
            $admin_phone_number      = implode(',', $admin_phone_number);
            // do_action( 'sa_send_sms', $admin_phone_number, $sms_admin_body_new_user ); //commented on 25-08-2021.
            $obj             = array();
            $obj['number']   = $admin_phone_number;
            $obj['sms_body'] = $sms_admin_body_new_user;
            SmsAlertcURLOTP::sendsms($obj);
        }
    }
    
   
    public static function addTabs( $tabs = array() )
    {
        $signup_param = array(
        'checkTemplateFor' => 'signup_temp',
        'templates'        => self::getSignupTemplates(),
        );

        $new_user_reg_param = array(
        'checkTemplateFor' => 'new_user_reg_temp',
        'templates'        => self::getNewUserRegisterTemplates(),
        );

        $tabs['user_registration']['nav']  = 'New Users';
        $tabs['user_registration']['icon'] = 'dashicons-admin-users';

        $tabs['user_registration']['inner_nav']['wc_register']['title']        = __('Notify On Sign Up', 'softeria-sms-alerts');
        $tabs['user_registration']['inner_nav']['wc_register']['tab_section']  = 'signup_templates';
        $tabs['user_registration']['inner_nav']['wc_register']['first_active'] = true;

        $tabs['user_registration']['inner_nav']['wc_register']['tabContent'] = $signup_param;
        $tabs['user_registration']['inner_nav']['wc_register']['filePath']   = 'views/message-template.php';

        $tabs['user_registration']['inner_nav']['wc_register']['icon']   = 'dashicons-admin-users';
        $tabs['user_registration']['inner_nav']['wc_register']['params'] = $signup_param;

        $tabs['user_registration']['inner_nav']['new_user_reg']['title']       = 'Notify Admin';
        $tabs['user_registration']['inner_nav']['new_user_reg']['tab_section'] = 'newuserregtemplates';
        $tabs['user_registration']['inner_nav']['new_user_reg']['tabContent']  = $new_user_reg_param;
        $tabs['user_registration']['inner_nav']['new_user_reg']['filePath']    = 'views/message-template.php';
        $tabs['user_registration']['inner_nav']['new_user_reg']['params']      = $new_user_reg_param;

        return $tabs;
    }
    
    public static function getSignupTemplates()
    {
        $wc_user_roles = self::get_user_roles();

        $variables = array(
        '[username]'      => 'Username',
        '[store_name]'    => 'Store Name',
        '[email]'         => 'Email',
        '[billing_phone]' => 'Billing Phone',
        '[shop_url]'      => 'Shop Url',
        );

        $templates = array();
        foreach ( $wc_user_roles as $role_key  => $role ) {
            $current_val = softeria_alerts_get_option('wc_user_roles_' . $role_key, 'softeria_alerts_signup_general', 'on');

            $checkbox_name_id = 'softeria_alerts_signup_general[wc_user_roles_' . $role_key . ']';
            $textarea_name_id = 'softeria_alerts_signup_message[signup_sms_body_' . $role_key . ']';
            $text_body        = softeria_alerts_get_option('signup_sms_body_' . $role_key, 'softeria_alerts_signup_message', SmsAlertMessages::showMessage('DEFAULT_NEW_USER_REGISTER'));

            $templates[ $role_key ]['title']          = 'When ' . ucwords($role['name']) . ' is registered';
            $templates[ $role_key ]['enabled']        = $current_val;
            $templates[ $role_key ]['status']         = $role_key;
            $templates[ $role_key ]['text-body']      = $text_body;
            $templates[ $role_key ]['checkboxNameId'] = $checkbox_name_id;
            $templates[ $role_key ]['textareaNameId'] = $textarea_name_id;
            $templates[ $role_key ]['token']          = $variables;
        }
        return $templates;
    }

    public static function getNewUserRegisterTemplates()
    {
        $softeria_alerts_notification_reg_admin_msg = softeria_alerts_get_option('admin_registration_msg', 'softeria_alerts_general', 'on');
        $sms_body_registration_admin_msg     = softeria_alerts_get_option('sms_body_registration_admin_msg', 'softeria_alerts_message', SmsAlertMessages::showMessage('DEFAULT_ADMIN_NEW_USER_REGISTER'));

        $templates = array();

        $new_user_variables = array(
        '[username]'      => 'Username',
        '[store_name]'    => 'Store Name',
        '[email]'         => 'Email',
        '[billing_phone]' => 'Billing Phone',
        '[role]'          => 'Role',
        '[shop_url]'      => 'Shop Url',
        );

        $templates['new-user']['title']          = 'When a new user is registered';
        $templates['new-user']['enabled']        = $softeria_alerts_notification_reg_admin_msg;
        $templates['new-user']['status']         = 'new-user';
        $templates['new-user']['text-body']      = $sms_body_registration_admin_msg;
        $templates['new-user']['checkboxNameId'] = 'softeria_alerts_general[admin_registration_msg]';
        $templates['new-user']['textareaNameId'] = 'softeria_alerts_message[sms_body_registration_admin_msg]';
        $templates['new-user']['token']          = $new_user_variables;

        return $templates;
    }

 
    public static function addDefaultSetting( $defaults = array() )
    {
        $sms_body_registration_admin_msg = softeria_alerts_get_option('sms_body_registration_admin_msg', 'softeria_alerts_message', SmsAlertMessages::showMessage('DEFAULT_ADMIN_NEW_USER_REGISTER'));

        $wc_user_roles = self::get_user_roles();
        foreach ( $wc_user_roles as $role_key => $role ) {
            $defaults['softeria_alerts_signup_general'][ 'wc_user_roles_' . $role_key ]   = 'off';
            $defaults['softeria_alerts_signup_message'][ 'signup_sms_body_' . $role_key ] = $sms_body_registration_admin_msg;
        }
        return $defaults;
    }

   
    public static function get_user_roles( $system_name = null )
    {
        global $wp_roles;
        $roles = $wp_roles->roles;

        if (! empty($system_name) && array_key_exists($system_name, $roles) ) {
            return $roles[ $system_name ]['name'];
        } else {
            return $roles;
        }
    }
 
    public function instantiate()
    {
        spl_autoload_register(array( $this, 'smsproSmsAutoload' ));
        new softeria_alerts_Setting_Options();
    }

   
    public function smsproSmsAutoload( $class )
    {

        include_once 'handler/softeria_alerts_logic_interface.php';
        include_once 'handler/softeria_alerts_phone_logic.php';
        include_once 'helper/sessionVars.php';
        include_once 'helper/utility.php';
        include_once 'helper/constants.php';
        include_once 'helper/messages.php';
        include_once 'helper/curl.php';

        if (stripos($class, 'softeria_alerts_') !== false ) {

            $class_name = str_replace(array( 'softeria_alerts_', '_' ), array( '', '-' ), $class);
            $filename   = dirname(__FILE__) . '/classes/' . strtolower($class_name) . '.php';

            if (file_exists($filename) ) {
                include_once $filename;
            }
        }
    }

 
    public static function init()
    {
        static $instance = false;

        if (! $instance ) {            
            $instance = new SofteriaAlerts_WC_Order_SMS();
        }
        return $instance;
    }

    public function fnSaSendSms( $number, $content, $schedule = null )
    {
        $obj             = array();
        $obj['number']   = $number;
        $obj['sms_body'] = $content;
        $obj['schedule'] = $schedule;
        $response        = SmsAlertcURLOTP::sendsms($obj);
        return $response;
    }

    public function replaceCommonTokenName( $fields )
    {

        $search = array(
        '[store_name]',
        '[shop_url]',
        );

        $replace = array(
        get_bloginfo(),
        get_site_url(),
        );

        $fields['text'] = str_replace($search, $replace, $fields['text']);
        return $fields;
    }


    public function registerHookSendSms()
    {
        add_action('sa_send_sms', array( $this, 'fnSaSendSms' ), 10, 3);
    }

    public static function localization_setup()
    {
        load_plugin_textdomain('softeria-sms-alerts', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }


    public function adminEnqueueScripts()
    {
        wp_enqueue_style('admin-softeria-alert-styles', plugins_url('css/admin.css', __FILE__), array(), SmsAlertConstants::SA_VERSION);
        
        wp_enqueue_style('admin-modal-styles', plugins_url('css/softeria_alerts_customer_validation_style.css', __FILE__), array(), SmsAlertConstants::SA_VERSION);
    
        wp_enqueue_script('admin-softeria-alert-scripts', plugins_url('js/admin.js', __FILE__), array( 'jquery' ), SmsAlertConstants::SA_VERSION, true);
        wp_enqueue_script('admin-softeria-alert-taggedinput', plugins_url('js/tagged-input.js', __FILE__), array( 'jquery' ), SmsAlertConstants::SA_VERSION, false);
        $user_authorize = new softeria_alerts_Setting_Options();
        wp_localize_script(
            'admin-softeria-alert-scripts',
            'smspro',
            array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'whitelist_countries' => softeria_alerts_get_option('whitelist_country', 'softeria_alerts_general'),
            'allow_otp_countries' => softeria_alerts_get_option('allow_otp_country', 'softeria_alerts_general'),
            'sa_default_countrycode' => softeria_alerts_get_option('default_country_code', 'softeria_alerts_general'),
            'islogged' => $user_authorize->is_user_authorised(),
            'pattern' => SmsAlertConstants::PATTERN_PHONE,
			'nonce' => wp_create_nonce('softeria-alert-nonce')
            )
        );
    }

 
    public function pluginRowMetaLink( $plugin_meta, $plugin_file, $plugin_data, $status )
    {
        if (isset($plugin_data['slug']) && ( 'softeria-sms-alerts' === $plugin_data['slug'] ) && ! defined('softeria_alerts_DIR') ) {
            $plugin_meta[] = '<a href="https://sms.softeriatech.com/wordpress" target="_blank">' . __('Docs', 'softeria-sms-alerts') . '</a>';
            $plugin_meta[] = '<a href="https://wordpress.org/support/plugin/softeria-sms-alerts/reviews/#postform" target="_blank" class="wc-rating-link">★★★★★</a>';
        }
        return $plugin_meta;
    }


    public function addActionLinks( $links )
    {
        $links[] = sprintf('<a href="%s">Settings</a>', admin_url('admin.php?page=softeria-sms-alerts'));
        return $links;
    }

    public static function onlyCredit()
    {
        $trans_credit = [ "credit_balance"=> "0"];
        $credits      = SmsAlertcURLOTP::getCredits();
        if (is_array($credits) ) {
            $trans_credit=$credits;
        }
        return $trans_credit;
    }

    public static function runOnActivate()
    {
        
        if (! get_option('softeria_alerts_activation_date') ) {
            add_option('softeria_alerts_activation_date', date('Y-m-d'));
        }
        if (! wp_next_scheduled('softeria_alerts_balance_notify') ) {
            wp_schedule_event(time(), 'hourly', 'softeria_alerts_balance_notify');
        }
        if (!wp_next_scheduled('softeria_alerts_followup_sms') ) {
            $time_value = esc_attr(softeria_alerts_get_option('subscription_reminder_cron_time', 'softeria_alerts_general', '10:00'));
            wp_schedule_event(strtotime(get_gmt_from_date($time_value)), 'daily', 'softeria_alerts_followup_sms');
        }
        self::saCartActivate();
        
        //commented , use later for after plugin install.
        add_option('softeria_alerts_do_activation_redirect', true);
    }
   
    function smsproPluginRedirect()
    {
        if (get_option('softeria_alerts_do_activation_redirect', false)) {
            delete_option('softeria_alerts_do_activation_redirect');
            wp_redirect("admin.php?page=softeria-sms-alerts");
        }
    }                                  

   
    public static function saCartActivate()
    {
        global $wpdb, $table_name;

        $table_name      = $wpdb->prefix . CHECKOUT_VIEW_NAME;
        $tabl_name = $wpdb->prefix . "softeria_alerts_renewal_reminders";                                                    
        $reminder_table_name = $wpdb->prefix . "softeria_alerts_booking_reminder";                                                    
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id BIGINT(20) NOT NULL AUTO_INCREMENT,
			name VARCHAR(60),
			surname VARCHAR(60),
			email VARCHAR(100),
			phone VARCHAR(20),
			location VARCHAR(100),
			cart_contents LONGTEXT,
			cart_total DECIMAL(10,2),
			currency VARCHAR(10),
			time DATETIME DEFAULT '0000-00-00 00:00:00',
			session_id VARCHAR(60),
			msg_sent TINYINT NOT NULL DEFAULT 0,
			recovered TINYINT NOT NULL DEFAULT 0,
			other_fields LONGTEXT,
			PRIMARY KEY (id)
		) $charset_collate;";

        $sql1 = "CREATE TABLE IF NOT EXISTS $tabl_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			subscription_id mediumint(9) NOT NULL,
			subscription_text text NOT NULL,
			source VARCHAR(50),
			next_payment_date date DEFAULT '0000-00-00' NOT NULL,
			notification_sent_date date DEFAULT '0000-00-00' NOT NULL,
			PRIMARY KEY  (id)
        ) $charset_collate;";

        $sql2 = "CREATE TABLE IF NOT EXISTS $reminder_table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			booking_id mediumint(9) NOT NULL,
			phone VARCHAR(20),
			source VARCHAR(50),
			msg_sent TINYINT NOT NULL DEFAULT 0,
			start_date DATETIME DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id)
        ) $charset_collate;";        
        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql1);           
        dbDelta($sql2);           
        dbDelta($sql);

        // Resets table Auto increment index to 1.
        $sql = "ALTER TABLE $table_name AUTO_INCREMENT = 1";
        dbDelta($sql);

        $ab_cart_fc_captured_abandoned_cart_count = get_option('ab_cart_fc_captured_abandoned_cart_count');
        if ($ab_cart_fc_captured_abandoned_cart_count ) {
            update_option('cart_captured_abandoned_cart_count', $ab_cart_fc_captured_abandoned_cart_count);
        }
        delete_option('ab_cart_fc_captured_abandoned_cart_count');

        $user_settings_notification_frequency = softeria_alerts_get_option('customer_notify', 'softeria_alerts_abandoned_cart', 'on');
        $user_cod_settings_notification_frequency = softeria_alerts_get_option('customer_notify', 'softeria_alerts_cod_to_prepaid', 'on');
        $wcbk_reminder_frequency = softeria_alerts_get_option('customer_notify', 'softeria_alerts_wcbk_general', 'off');
        $bc_reminder_frequency = softeria_alerts_get_option('customer_notify', 'softeria_alerts_bc_general', 'off');
        $rr_reminder_frequency = softeria_alerts_get_option('customer_notify', 'softeria_alerts_rr_general', 'off');
        $qr_reminder_frequency = softeria_alerts_get_option('customer_notify', 'softeria_alerts_qr_general', 'off');
        $eap_reminder_frequency = softeria_alerts_get_option('customer_notify', 'softeria_alerts_eap_general', 'off');
        $bcc_reminder_frequency = softeria_alerts_get_option('customer_notify', 'softeria_alerts_bcc_general', 'off');
        $wcf_reminder_frequency = softeria_alerts_get_option('customer_notify', 'softeria_alerts_wcf_general', 'off');
        $sln_reminder_frequency = softeria_alerts_get_option('customer_notify', 'softeria_alerts_sln_general', 'off');
        $alb_reminder_frequency = softeria_alerts_get_option('customer_notify', 'softeria_alerts_alb_general', 'off');
        $ssa_reminder_frequency = softeria_alerts_get_option('customer_notify', 'softeria_alerts_ssa_general', 'off');

        if ('off' === $user_settings_notification_frequency) { // If SMS notifications have been disabled, we disable cron job.
            wp_clear_scheduled_hook('ab_cart_notification_sendsms_hook');
        } else {
            if (! wp_next_scheduled('ab_cart_notification_sendsms_hook') ) {
                wp_schedule_event(time(), 'sendsms_interval', 'ab_cart_notification_sendsms_hook');
            }
        }
        
        if ('off' === $user_cod_settings_notification_frequency ) { // If SMS notifications have been disabled, we disable cron job.
            wp_clear_scheduled_hook('cod_to_prepaid_cart_notification_sendsms_hook');
        } else {
            if (! wp_next_scheduled('cod_to_prepaid_cart_notification_sendsms_hook') ) {
                wp_schedule_event(time(), 'sendsms_interval',  'cod_to_prepaid_cart_notification_sendsms_hook');
            }
        }
        if (('off' === $wcbk_reminder_frequency && 'off' === $bc_reminder_frequency && 'off' === $rr_reminder_frequency && 'off' === $bcc_reminder_frequency && 'off' === $qr_reminder_frequency && 'off' === $eap_reminder_frequency && 'off' === $wcf_reminder_frequency && 'off' === $sln_reminder_frequency && 'off' === $alb_reminder_frequency && 'off' === $ssa_reminder_frequency ) ) { // If SMS notifications have been disabled, we disable cron job.
            wp_clear_scheduled_hook('booking_reminder_sendsms_hook');
        } else {
            if (! wp_next_scheduled('booking_reminder_sendsms_hook') ) {
                wp_schedule_event(time(), 'sendremindersms_interval', 'booking_reminder_sendsms_hook');
            }
        }
    }

    /**
     * Executes on plugin de-activate.
     *
     * @return void
     */
    public static function runOnDeactivate()
    {
        wp_clear_scheduled_hook('softeria_alerts_balance_notify');
        wp_clear_scheduled_hook('softeria_alerts_followup_sms');
        wp_clear_scheduled_hook('booking_reminder_sendsms_hook');
    }

    /**
     * Executes on plugin uninstall.
     *
     * @return void
     */
    public static function runOnUninstall()
    {
		$clear_all_data   = softeria_alerts_get_option('clear_all_data', 'softeria_alerts_general', 'off');
		if ('on' === $clear_all_data ) {
			global $wpdb;

			$main_table = $wpdb->prefix . 'captured_wc_input';
			$booking_table = $wpdb->prefix . 'softeria_alerts_booking_reminder';
			$renewal_table = $wpdb->prefix . 'softeria_alerts_renewal_reminders';

			$wpdb->query("DROP TABLE IF EXISTS $main_table,$booking_table,$renewal_table");

			delete_option('cart_captured_abandoned_cart_count');
			delete_option('softeria_alerts_message');
			delete_option('softeria_alerts_gateway');
			delete_option('softeria_alerts_general');
			delete_option('softeria_alerts_upgrade_settings');
			delete_option('widget_softeria_alerts_widget');
			delete_option('softeria_alerts_activation_date');
		}
    }

    public function backgroundTask()
    {
        $low_bal_alert   = softeria_alerts_get_option('low_bal_alert', 'softeria_alerts_general', 'off');
        $daily_bal_alert = softeria_alerts_get_option('daily_bal_alert', 'softeria_alerts_general', 'off');
        $user_authorize  = new softeria_alerts_Setting_Options();
        $islogged        = $user_authorize->is_user_authorised();
        $auto_sync       = softeria_alerts_get_option('auto_sync', 'softeria_alerts_general', 'off');
        if ($islogged ) {
            if ('on' === $auto_sync ) {
                self::syncCustomers();
            }
        }
        if ('on' === $low_bal_alert ) {
            self::sendSmsalertBalance();
        }
        if ('on' === $daily_bal_alert ) {
            self::dailyEmailAlert();
        }
    }

  
    public function saSyncGrpAction()
    {
        if (array_key_exists('option', $_GET) ) {
            switch ( trim(sanitize_text_field(wp_unslash($_GET['option']))) ) {
            case 'softeria-alert-group-sync':
                self::syncCustomers();
                exit;
            }
        }
    }

    public static function syncCustomers()
    {
        $group_name = softeria_alerts_get_option('group_auto_sync', 'softeria_alerts_general', '');
        $update_id  = softeria_alerts_get_option('last_sync_userId', 'softeria_alerts_sync', '');
        $username   = softeria_alerts_get_option('softeria_alerts_name', 'softeria_alerts_gateway');
        $password   = softeria_alerts_get_option('softeria_alerts_password', 'softeria_alerts_gateway');
        if (empty($group_name) ) {
            return;
        }

        $update_id = ! empty($update_id) ? $update_id : 0;
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->users} WHERE {$wpdb->users}.ID > %d order by ID asc limit 100",
            $update_id
        );

        $uids = $wpdb->get_col($sql);
        if (0 === count($uids) ) {
            wp_send_json( array(
                'status'      => true,
                'description' => array( 'cnt_member' => 0 ),
                )
            );
            exit;
        } else {
            $user_query = new WP_User_Query(
                array(
                    'include' => $uids,
                    'orderby' => 'id',
                    'order'   => 'ASC',
                )
            );
            if ($user_query->get_results() ) {
                $cnt = 0;
                $obj = array();
                foreach ( $user_query->get_results() as $ukey => $user ) {
                    $number                      = get_user_meta($user->ID, 'billing_phone', true);
                    $obj[ $ukey ]['person_name'] = $user->display_name;
                    $obj[ $ukey ]['number']      = $number;
                    $last_sync_id                = $user->ID;
                    $cnt++;
                }
                $resp = SmsAlertcURLOTP::createContact($obj, $group_name);
                update_option('softeria_alerts_sync', array( 'last_sync_userId' => $last_sync_id ));
                $result = $resp;
                if (true === $result['status'] ) {
                    wp_send_json(
                        array(
                        'status'      => 'success',
                        'description' => array( 'cnt_member' => $cnt ),
                        )
                    );
                    exit();
                }
            } else {
                wp_send_json(
                    array(
                    'status'      => 'success',
                    'description' => array( 'cnt_member' => 0 ),
                    )
                );
                exit();
            }
        }
    }

    public static function sendSmsalertBalance()
    {
        $date            = date('Y-m-d');
        $update_datetime = softeria_alerts_get_option('last_updated_lBal_alert', 'softeria_alerts_background_task', '');

        if ($update_datetime == $date ) {
            return;
        }

        $username     = softeria_alerts_get_option('softeria_alerts_name', 'softeria_alerts_gateway', '');
        $low_bal_val  = softeria_alerts_get_option('low_bal_val', 'softeria_alerts_general', '1000');
        $to_mail      = softeria_alerts_get_option('alert_email', 'softeria_alerts_general', '');
        $trans_credit = self::onlyCredit();

        if (! empty($trans_credit) ) {

            $params       = array(
            'trans_credit' => $trans_credit['credit_balance'],
            'username'     => $username,
            'admin_url'    => admin_url(),
            );
            $emailcontent = get_softeria_alerts_template('template/emails/softeria-alert-low-bal.php', $params, true);

            if ($trans_credit['credit_balance'] <= $low_bal_val ) {
                wp_mail($to_mail, '❗ ✱ Softeria Tech ✱ Low Balance Alert', $emailcontent, 'content-type:text/html');
            }

            update_option('softeria_alerts_background_task', array( 'last_updated_lBal_alert' => date('Y-m-d') ));// update last time and date.
        }
    }

    public function dailyEmailAlert()
    {
        $username        = softeria_alerts_get_option('softeria_alerts_name', 'softeria_alerts_gateway', '');
        $date            = date('Y-m-d');
        $to_mail         = softeria_alerts_get_option('alert_email', 'softeria_alerts_general', '');
        $update_datetime = softeria_alerts_get_option('last_updated_dBal_alert', 'softeria_alerts_background_dBal_task', '');

        if ($update_datetime == $date ) {
            return;
        }

        $daily_credits = self::onlyCredit();

        if (! empty($daily_credits) ) {
            $params            = array(
                'daily_credits' => $daily_credits['credit_balance'],
                'username'      => $username,
                'date'          => $date,
                'admin_url'     => admin_url(),
            );
            $dailyemailcontent = get_softeria_alerts_template('template/emails/daily-email-alert.php', $params, true);
            update_option('softeria_alerts_background_dBal_task', array( 'last_updated_dBal_alert' => date('Y-m-d') ));// update last time and date.
            wp_mail($to_mail, '✱ Softeria Tech ✱ Daily  Balance Alert ', $dailyemailcontent, 'content-type:text/html');
        }
    }

    public function buyerNotificationUpdateOrderMeta( $order_id )
    {
        if (! empty($_POST['buyer_sms_notify']) ) {
            update_post_meta($order_id, '_buyer_sms_notify', sanitize_text_field(wp_unslash($_POST['buyer_sms_notify'])));
        }
    }


    public function saWcOrderPlace( $order_id )
    {
        if (! $order_id ) {
            return;
        }
        WooCommerceCheckOutForm::trigger_after_order_place($order_id, 'pending', 'pending');
    }
} // SofteriaAlerts_WC_Order_SMS


add_action('plugins_loaded', 'loadSaWcOrderSms');
add_action('init', array('SofteriaAlerts_WC_Order_SMS','localization_setup'));


function additionalCronIntervals( $intervals )
{
    $intervals['sendsms_interval'] = array(
    'interval' => CHECKOUT_JOB_SCHECDULE * 60,
    'display'  => 'Every 10 minutes',
    );
    $intervals['sendremindersms_interval'] = array(
    'interval' => BOOKING_SCHECDULE_REMINDER * 60,
    'display'  => 'Every 60 minutes',
    );
    return $intervals;
}

add_filter('cron_schedules', 'additionalCronIntervals');

function loadSaWcOrderSms()
{
    $smspro = SofteriaAlerts_WC_Order_SMS::init();
}
register_activation_hook(__FILE__, array( 'softeria_alerts_WC_Order_SMS', 'runOnActivate' ));
register_deactivation_hook(__FILE__, array( 'softeria_alerts_WC_Order_SMS', 'runOnDeactivate' ));
register_uninstall_hook(__FILE__, array( 'softeria_alerts_WC_Order_SMS', 'runOnUninstall' ));