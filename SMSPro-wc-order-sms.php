<?php
/**
 * This is a WooCommerce add-on. By Using this plugin admin and buyer can get notification after placing order via sms using SMS Pro.
 * PHP version 5
 *
 * @category Helper
 * @package  SMSPro
 * @author   SMS Pro <support@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 * Plugin Name: SMSPro - WooCommerce
 * Plugin URI: https://wordpress.org/plugins/sms-pro/
 * Description: This is a WooCommerce add-on. By Using this plugin admin and buyer can get notification after placing order via sms using SMS Pro.
 * Version: 3.8.3
 * Author: Softeria Technologies Ltd.
 * Author URI: https://sms.softeriatech.com
 * WC requires at least: 4.6
 * WC tested up to: 9.8
 * Text Domain: sms-pro
 * License: GPLv2
 */

/**
/**
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly.

if (! defined('ABSPATH') ) {
    exit;
}
if (! defined('SMSPRO_TEXT_DOMAIN') ) {
    define('SMSPRO_TEXT_DOMAIN', 'sms-pro');
}
if (! defined('SMSPRO_PLUGIN_NAME') ) {
    define('SMSPRO_PLUGIN_NAME', 'SMS Pro Order Notifications – WooCommerce');
}
if (! defined('SMSPRO_ABANDONED') ) {
    define('SMSPRO_ABANDONED', 'smspro_abandoned');
}
if (! defined('SMSPRO_PLUGIN_NAME_SLUG') ) {
    define('SMSPRO_PLUGIN_NAME_SLUG', 'sms-pro');
}
if (! defined('SA_CART_TABLE_NAME') ) {
    define('SA_CART_TABLE_NAME', 'sa_captured_wc_fields');
}
if (! defined('CART_CRON_INTERVAL') ) {
    define('CART_CRON_INTERVAL', 10);// run ab cart cron every 10 min.
}
if (! defined('BOOKING_REMINDER_CRON_INTERVAL') ) {
    define('BOOKING_REMINDER_CRON_INTERVAL', 10);// run booking reminder cron every 10 min.
}
// In minutes. Defines the interval at which msg function is fired.
if (! defined('CART_STILL_SHOPPING') ) {
    define('CART_STILL_SHOPPING', 10); // In minutes. Defines the time period after which an msg notice will be sent and the cart is presumed abandoned.
}
if (! defined('CART_NEW_STATUS_NOTICE') ) {
    define('CART_NEW_STATUS_NOTICE', 240); // Defining time in minutes how long New status is shown in the table.
}

if (! defined('CART_ENCRYPTION_KEY') ) {
    define('CART_ENCRYPTION_KEY', 'SgVkYp3s6v9y$B&M)H+MbQeThWmZq4t9');
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

/**
 * Sanitizes Array of vaues.
 *
 * @param array $arr Values to be sanitized.
 *
 * @return array
 */
function smspro_sanitize_array( $arr )
{
    global $wp_version;
    $older_version = ( $wp_version < '4.7' ) ? true : false;
    if (! is_array($arr) ) {
        return ( ( $older_version ) ? stripcslashes(sanitize_text_field($arr)) : stripcslashes(sanitize_textarea_field($arr)) );
    }

    $result = array();
    foreach ( $arr as $key => $val ) {
        $result[ $key ] = is_array($val) ? smspro_sanitize_array($val) : ( ( $older_version ) ? stripcslashes(sanitize_text_field($val)) : stripcslashes(sanitize_textarea_field($val)) );
    }

    return $result;
}

/**
 * Creates a cookie.
 *
 * @param string $cookie_key   Cookie Key name.
 * @param string $cookie_value Cookie Value.
 *
 * @return array
 */
function create_smspro_cookie( $cookie_key, $cookie_value )
{
    ob_start();
    setcookie($cookie_key, $cookie_value, time() + ( 15 * 60 ));
    ob_get_clean();
}

/**
 * Clears a cookie.
 *
 * @param string $cookie_key Cookie Key name.
 *
 * @return array
 */
function clear_smspro_cookie( $cookie_key )
{
    if (isset($_COOKIE[ $cookie_key ]) ) {
        unset($_COOKIE[ $cookie_key ]);
        setcookie($cookie_key, '', time() - ( 15 * 60 ));
    }
}

/**
 * Gets a cookie.
 *
 * @param string $cookie_key Cookie Key name.
 *
 * @return array
 */
function get_smspro_cookie( $cookie_key )
{
    if (! isset($_COOKIE[ $cookie_key ]) ) {
        return false;
    } else {
        return sanitize_text_field(wp_unslash($_COOKIE[ $cookie_key ]));
    }
}

/**
 * Gets key value from database.
 *
 * @param string $option  Option.
 * @param string $section Section.
 * @param string $default Default value.
 *
 * @return array
 */
function smspro_get_option( $option, $section, $default = '' )
{
    $options = get_option($section);

    if (isset($options[ $option ]) ) {
        return $options[ $option ];
    }
    return $default;
}

/**
 * Gets a template.
 *
 * @param string  $filepath File path.
 * @param array   $datas    Values to be used in template.
 * @param boolean $ret      Return as string.
 *
 * @return array
 */
function get_smspro_template( $filepath, $datas, $ret = false )
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

/**
 * PHP version 5
 *
 * @category Helper
 * @package  SMSPro
 * @author   SMS Pro <support@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 * Plugin Name: SMSPro - WooCommerce
 * Plugin URI: https://wordpress.org/plugins/sms-pro/
 * Main class for plugin.
 */
class smspro_WC_Order_SMS
{

    /**
     * Constructor for the smspro_WC_Order_SMS class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @return array
     */
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
        add_action('smspro_after_update_new_user_phone', array( $this, 'smsproAfterUserRegister' ), 10, 2);

        include_once 'helper/formlist.php';
        include_once 'views/common-elements.php';
        include_once 'handler/forms/FormInterface.php';
        include_once 'handler/smspro_form_handler.php';
        include_once 'helper/shortcode.php';

        if (is_admin() ) {
            add_action('admin_enqueue_scripts', array( $this, 'adminEnqueueScripts' ));
            add_filter('plugin_row_meta', array( $this, 'pluginRowMetaLink' ), 10, 4);
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'addActionLinks' ));
        }

        /*code to notify for daily balance begins */
        add_action('smspro_balance_notify', array( $this, 'backgroundTask' ));
        self::saSyncGrpAction();
        add_filter('sa_before_send_sms', array( $this, 'replaceCommonTokenName' ), 100, 1);
        //commented later we use for redirect after install , plugin.
        add_action('admin_init', array($this, 'smsproPluginRedirect'));
        add_action('sa_addTabs', array( $this, 'addTabs' ), 10);
        add_filter('sAlertDefaultSettings', array( $this, 'addDefaultSetting' ), 1);
        //add_action( 'activated_plugin', array($this, 'cyb_activation_redirect') ); //testing part
    }
    
    //testing part
    //commented later we use for redirect after install the plugin.
    // public function cyb_activation_redirect( $plugin ) {
    // if ( $plugin == plugin_basename( __FILE__ ) ) {
    // exit( wp_redirect( admin_url( 'admin.php?page=sms-pro' ) ) );
    // }
    // }
    
    /**
     * Onpage modify billing phone at checkout page when country code is enabled
     *
     * @param string $value Value of the field.
     * @param string $input Name of the field.
     *
     * @return void
     */
    public function modifyBillingPhoneField( $value, $input )
    {
        if ('billing_phone' === $input && ! empty($value) ) {
            return SmsAlertUtility::formatNumberForCountryCode($value);
        }
    }
    
    /**
     * This function is executed after a user is created.
     *
     * @param int $user_id User id of the user.
     *
     * @return void
     */
    public function wcUserCreated( $user_id )
    {
        $billing_phone = ( ! empty($_POST['billing_phone']) ) ? sanitize_text_field(wp_unslash($_POST['billing_phone'])) : null;
        $billing_phone = apply_filters('sa_get_user_phone_no', $billing_phone, $user_id);
        $billing_phone = SmsAlertcURLOTP::checkPhoneNos($billing_phone);
        update_user_meta($user_id, 'billing_phone', $billing_phone);
        do_action('smspro_after_update_new_user_phone', $user_id, $billing_phone);
    }
    
    /**
     * This function is executed after a user has been registered.
     *
     * @param int    $user_id       Userid of the user.
     * @param string $billing_phone Phone number of the user.
     *
     * @return void
     */
    public function smsproAfterUserRegister( $user_id, $billing_phone )
    {
        $user                = get_userdata($user_id);
        $role                = ( ! empty($user->roles[0]) ) ? $user->roles[0] : '';
        $role_display_name   = ( ! empty($role) ) ? self::get_user_roles($role) : '';
        $smspro_reg_notify = smspro_get_option('wc_user_roles_' . $role, 'smspro_signup_general', 'off');
        $sms_body_new_user   = smspro_get_option('signup_sms_body_' . $role, 'smspro_signup_message', SmsAlertMessages::showMessage('DEFAULT_NEW_USER_REGISTER'));

        $smspro_reg_admin_notify = smspro_get_option('admin_registration_msg', 'smspro_general', 'off');
        $sms_admin_body_new_user   = smspro_get_option('sms_body_registration_admin_msg', 'smspro_message', SmsAlertMessages::showMessage('DEFAULT_ADMIN_NEW_USER_REGISTER'));
        $admin_phone_number        = smspro_get_option('sms_admin_phone', 'smspro_message', '');

        $store_name = trim(get_bloginfo());

        if ('on' === $smspro_reg_notify && ! empty($billing_phone) ) {
            $search = array(
            '[username]',
            '[email]',
            '[billing_phone]',
            );

            $replace           = array(
            $user->user_login,
            $user->user_email,
            $billing_phone,
            );
            $sms_body_new_user = str_replace($search, $replace, $sms_body_new_user);
            // do_action( 'sa_send_sms', $billing_phone, $sms_body_new_user ); //commented on 25-08-2021
            $obj             = array();
            $obj['number']   = $billing_phone;
            $obj['sms_body'] = $sms_body_new_user;
            SmsAlertcURLOTP::sendsms($obj);
        }

        if ('on' === $smspro_reg_admin_notify && ! empty($admin_phone_number) ) {
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
    
    /**
     * This function adds tabs.
     *
     * @param array $tabs Default tabs.
     *
     * @return void
     */
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

        $tabs['user_registration']['nav']  = 'User Registration';
        $tabs['user_registration']['icon'] = 'dashicons-admin-users';

        $tabs['user_registration']['inner_nav']['wc_register']['title']        = __('Sign Up Notifications', 'sms-pro');
        $tabs['user_registration']['inner_nav']['wc_register']['tab_section']  = 'signup_templates';
        $tabs['user_registration']['inner_nav']['wc_register']['first_active'] = true;

        $tabs['user_registration']['inner_nav']['wc_register']['tabContent'] = $signup_param;
        $tabs['user_registration']['inner_nav']['wc_register']['filePath']   = 'views/message-template.php';

        $tabs['user_registration']['inner_nav']['wc_register']['icon']   = 'dashicons-admin-users';
        $tabs['user_registration']['inner_nav']['wc_register']['params'] = $signup_param;

        $tabs['user_registration']['inner_nav']['new_user_reg']['title']       = 'Admin Notifications';
        $tabs['user_registration']['inner_nav']['new_user_reg']['tab_section'] = 'newuserregtemplates';
        $tabs['user_registration']['inner_nav']['new_user_reg']['tabContent']  = $new_user_reg_param;
        $tabs['user_registration']['inner_nav']['new_user_reg']['filePath']    = 'views/message-template.php';
        $tabs['user_registration']['inner_nav']['new_user_reg']['params']      = $new_user_reg_param;

        return $tabs;
    }
    
    /**
     * Gets signup template.
     *
     * @return void
     */
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
            $current_val = smspro_get_option('wc_user_roles_' . $role_key, 'smspro_signup_general', 'on');

            $checkbox_name_id = 'smspro_signup_general[wc_user_roles_' . $role_key . ']';
            $textarea_name_id = 'smspro_signup_message[signup_sms_body_' . $role_key . ']';
            $text_body        = smspro_get_option('signup_sms_body_' . $role_key, 'smspro_signup_message', SmsAlertMessages::showMessage('DEFAULT_NEW_USER_REGISTER'));

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

    /**
     * Gets new user registration template.
     *
     * @return void
     */
    public static function getNewUserRegisterTemplates()
    {
        $smspro_notification_reg_admin_msg = smspro_get_option('admin_registration_msg', 'smspro_general', 'on');
        $sms_body_registration_admin_msg     = smspro_get_option('sms_body_registration_admin_msg', 'smspro_message', SmsAlertMessages::showMessage('DEFAULT_ADMIN_NEW_USER_REGISTER'));

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
        $templates['new-user']['enabled']        = $smspro_notification_reg_admin_msg;
        $templates['new-user']['status']         = 'new-user';
        $templates['new-user']['text-body']      = $sms_body_registration_admin_msg;
        $templates['new-user']['checkboxNameId'] = 'smspro_general[admin_registration_msg]';
        $templates['new-user']['textareaNameId'] = 'smspro_message[sms_body_registration_admin_msg]';
        $templates['new-user']['token']          = $new_user_variables;

        return $templates;
    }

    /**
     * This function Adds default settings in configuration.
     *
     * @param array $defaults Default values.
     *
     * @return void
     */
    public static function addDefaultSetting( $defaults = array() )
    {
        $sms_body_registration_admin_msg = smspro_get_option('sms_body_registration_admin_msg', 'smspro_message', SmsAlertMessages::showMessage('DEFAULT_ADMIN_NEW_USER_REGISTER'));

        $wc_user_roles = self::get_user_roles();
        foreach ( $wc_user_roles as $role_key => $role ) {
            $defaults['smspro_signup_general'][ 'wc_user_roles_' . $role_key ]   = 'off';
            $defaults['smspro_signup_message'][ 'signup_sms_body_' . $role_key ] = $sms_body_registration_admin_msg;
        }
        return $defaults;
    }

    
    /**
     * This function gets role display name from system name.
     *
     * @param bool $system_name System name of the role.
     *
     * @return void
     */
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
    
    /**
     * Instantiate necessary Class
     *
     * @return void
     */
    public function instantiate()
    {
        spl_autoload_register(array( $this, 'smsproSmsAutoload' ));
        new smspro_Setting_Options();
    }

    /**
     * Autoload class files on demand.
     *
     * @param string $class requested class name.
     *
     * @return void
     */
    public function smsproSmsAutoload( $class )
    {

        include_once 'handler/smspro_logic_interface.php';
        include_once 'handler/smspro_phone_logic.php';
        include_once 'helper/sessionVars.php';
        include_once 'helper/utility.php';
        include_once 'helper/constants.php';
        include_once 'helper/messages.php';
        include_once 'helper/curl.php';

        if (stripos($class, 'smspro_') !== false ) {

            $class_name = str_replace(array( 'smspro_', '_' ), array( '', '-' ), $class);
            $filename   = dirname(__FILE__) . '/classes/' . strtolower($class_name) . '.php';

            if (file_exists($filename) ) {
                include_once $filename;
            }
        }
    }

    /**
     * Initializes the SMSPro_WC_Order_SMS() class
     *
     * Checks for an existing SMSPro_WC_Order_SMS() instance
     * and if it doesn't find one, creates it.
     *
     * @return void
     */
    public static function init()
    {
        static $instance = false;

        if (! $instance ) {            
            $instance = new SMSPro_WC_Order_SMS();
        }
        return $instance;
    }

    /**
     * Sends an SMS.
     *
     * @param string $number   Number to send SMS.
     * @param string $content  Text of SMS to be sent.
     * @param string $schedule SMS schedule time.
     *
     * @return void
     */
    public function fnSaSendSms( $number, $content, $schedule = null )
    {
        $obj             = array();
        $obj['number']   = $number;
        $obj['sms_body'] = $content;
        $obj['schedule'] = $schedule;
        $response        = SmsAlertcURLOTP::sendsms($obj);
        return $response;
    }

    /**
     * Replaces common variables in SMS Text.
     *
     * @param string $fields Array containing text key.
     *
     * @return void
     */
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

    /**
     * Registers the send SMS hook.
     *
     * @return void
     */
    public function registerHookSendSms()
    {
        add_action('sa_send_sms', array( $this, 'fnSaSendSms' ), 10, 3);
    }
    
    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     *
     * @return void
     */
    public static function localization_setup()
    {
        load_plugin_textdomain('sms-pro', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * Enques scripts to be loaded in admin section.
     *
     * @return void
     */
    public function adminEnqueueScripts()
    {
        wp_enqueue_style('admin-smspro-styles', plugins_url('css/admin.css', __FILE__), array(), SmsAlertConstants::SA_VERSION);
        
        wp_enqueue_style('admin-modal-styles', plugins_url('css/sms_pro_customer_validation_style.css', __FILE__), array(), SmsAlertConstants::SA_VERSION);
    
        wp_enqueue_script('admin-smspro-scripts', plugins_url('js/admin.js', __FILE__), array( 'jquery' ), SmsAlertConstants::SA_VERSION, true);
        wp_enqueue_script('admin-smspro-taggedinput', plugins_url('js/tagged-input.js', __FILE__), array( 'jquery' ), SmsAlertConstants::SA_VERSION, false);
        $user_authorize = new smspro_Setting_Options();
        wp_localize_script(
            'admin-smspro-scripts',
            'smspro',
            array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'whitelist_countries' => smspro_get_option('whitelist_country', 'smspro_general'),
            'allow_otp_countries' => smspro_get_option('allow_otp_country', 'smspro_general'),
            'sa_default_countrycode' => smspro_get_option('default_country_code', 'smspro_general'),
            'islogged' => $user_authorize->is_user_authorised(),
            'pattern' => SmsAlertConstants::PATTERN_PHONE,
			'nonce' => wp_create_nonce('smspro-nonce')
            )
        );
    }

    /**
     * Adds a meta row to plugin.
     *
     * @param string $plugin_meta Array of plugin meta.
     * @param string $plugin_file plugin base file.
     * @param string $plugin_data Array containing information about plugin.
     * @param string $status      status.
     *
     * @return void
     */
    public function pluginRowMetaLink( $plugin_meta, $plugin_file, $plugin_data, $status )
    {
        if (isset($plugin_data['slug']) && ( 'sms-pro' === $plugin_data['slug'] ) && ! defined('smspro_DIR') ) {
            $plugin_meta[] = '<a href="https://sms.softeriatech.com/wordpress" target="_blank">' . __('Docs', 'sms-pro') . '</a>';
            $plugin_meta[] = '<a href="https://wordpress.org/support/plugin/sms-pro/reviews/#postform" target="_blank" class="wc-rating-link">★★★★★</a>';
        }
        return $plugin_meta;
    }

    /**
     * Adds an action link in admin section.
     *
     * @param array $links Array of action links.
     *
     * @return void
     */
    public function addActionLinks( $links )
    {
        $links[] = sprintf('<a href="%s">Settings</a>', admin_url('admin.php?page=sms-pro'));
        return $links;
    }

    /**
     * Returns the credit in users account.
     *
     * @return void
     */
    public static function onlyCredit()
    {
        $trans_credit = [ "credit_balance"=> "0"];
        $credits      = SmsAlertcURLOTP::getCredits();
        if (is_array($credits) ) {
            $trans_credit=$credits;
        }
        return $trans_credit;
    }

    /**
     * This function is executed on plugin activate.
     *
     * @return void
     */
    public static function runOnActivate()
    {
        
        if (! get_option('smspro_activation_date') ) {
            add_option('smspro_activation_date', date('Y-m-d'));
        }
        if (! wp_next_scheduled('smspro_balance_notify') ) {
            wp_schedule_event(time(), 'hourly', 'smspro_balance_notify');
        }
        if (!wp_next_scheduled('smspro_followup_sms') ) {
            $time_value = esc_attr(smspro_get_option('subscription_reminder_cron_time', 'smspro_general', '10:00'));
            wp_schedule_event(strtotime(get_gmt_from_date($time_value)), 'daily', 'smspro_followup_sms');
        }
        self::saCartActivate();
        
        //commented , use later for after plugin install.
        add_option('smspro_do_activation_redirect', true);
    }
    /**
     * Commented , use later for after plugin install.
     *
     * @return void
     */
    function smsproPluginRedirect()
    {
        if (get_option('smspro_do_activation_redirect', false)) {
            delete_option('smspro_do_activation_redirect');
            wp_redirect("admin.php?page=sms-pro");
        }
    }                                  

    /**
     * This function is executed on plugin activate to create table for abondoned cart functionality.
     *
     * @return void
     */
    public static function saCartActivate()
    {
        global $wpdb, $table_name;

        $table_name      = $wpdb->prefix . SA_CART_TABLE_NAME;
        $tabl_name = $wpdb->prefix . "smspro_renewal_reminders";                                                    
        $reminder_table_name = $wpdb->prefix . "smspro_booking_reminder";                                                    
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

        $user_settings_notification_frequency = smspro_get_option('customer_notify', 'smspro_abandoned_cart', 'on');
        $user_cod_settings_notification_frequency = smspro_get_option('customer_notify', 'smspro_cod_to_prepaid', 'on');
        $wcbk_reminder_frequency = smspro_get_option('customer_notify', 'smspro_wcbk_general', 'off');
        $bc_reminder_frequency = smspro_get_option('customer_notify', 'smspro_bc_general', 'off');
        $rr_reminder_frequency = smspro_get_option('customer_notify', 'smspro_rr_general', 'off');
        $qr_reminder_frequency = smspro_get_option('customer_notify', 'smspro_qr_general', 'off');
        $eap_reminder_frequency = smspro_get_option('customer_notify', 'smspro_eap_general', 'off');
        $bcc_reminder_frequency = smspro_get_option('customer_notify', 'smspro_bcc_general', 'off');
        $wcf_reminder_frequency = smspro_get_option('customer_notify', 'smspro_wcf_general', 'off');
        $sln_reminder_frequency = smspro_get_option('customer_notify', 'smspro_sln_general', 'off');
        $alb_reminder_frequency = smspro_get_option('customer_notify', 'smspro_alb_general', 'off');
        $ssa_reminder_frequency = smspro_get_option('customer_notify', 'smspro_ssa_general', 'off');

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
        wp_clear_scheduled_hook('smspro_balance_notify');
        wp_clear_scheduled_hook('smspro_followup_sms');
        wp_clear_scheduled_hook('booking_reminder_sendsms_hook');
    }

    /**
     * Executes on plugin uninstall.
     *
     * @return void
     */
    public static function runOnUninstall()
    {
		$clear_all_data   = smspro_get_option('clear_all_data', 'smspro_general', 'off');
		if ('on' === $clear_all_data ) {
			global $wpdb;

			$main_table = $wpdb->prefix . 'sa_captured_wc_fields';
			$booking_table = $wpdb->prefix . 'smspro_booking_reminder';
			$renewal_table = $wpdb->prefix . 'smspro_renewal_reminders';

			$wpdb->query("DROP TABLE IF EXISTS $main_table,$booking_table,$renewal_table");

			delete_option('cart_captured_abandoned_cart_count');
			delete_option('smspro_message');
			delete_option('smspro_gateway');
			delete_option('smspro_general');
			delete_option('smspro_upgrade_settings');
			delete_option('widget_smspro_widget');
			delete_option('smspro_activation_date');
		}
    }

    /**
     * Background scheduler function to send email for low balance, etc.
     *
     * @return void
     */
    public function backgroundTask()
    {
        $low_bal_alert   = smspro_get_option('low_bal_alert', 'smspro_general', 'off');
        $daily_bal_alert = smspro_get_option('daily_bal_alert', 'smspro_general', 'off');
        $user_authorize  = new smspro_Setting_Options();
        $islogged        = $user_authorize->is_user_authorised();
        $auto_sync       = smspro_get_option('auto_sync', 'smspro_general', 'off');
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

    /**
     * Syncs contacts to SMS Pro group.
     *
     * @return void
     */
    public function saSyncGrpAction()
    {
        if (array_key_exists('option', $_GET) ) {
            switch ( trim(sanitize_text_field(wp_unslash($_GET['option']))) ) {
            case 'smspro-group-sync':
                self::syncCustomers();
                exit;
            }
        }
    }

    /**
     * Syncs contacts to SMS Pro group.
     *
     * @return void
     */
    public static function syncCustomers()
    {
        $group_name = smspro_get_option('group_auto_sync', 'smspro_general', '');
        $update_id  = smspro_get_option('last_sync_userId', 'smspro_sync', '');
        $username   = smspro_get_option('smspro_name', 'smspro_gateway');
        $password   = smspro_get_option('smspro_password', 'smspro_gateway');
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
                update_option('smspro_sync', array( 'last_sync_userId' => $last_sync_id ));
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

    /**
     * Sends SMS Pro balance.
     *
     * @return void
     */
    public static function sendSmsalertBalance()
    {
        $date            = date('Y-m-d');
        $update_datetime = smspro_get_option('last_updated_lBal_alert', 'smspro_background_task', '');

        if ($update_datetime == $date ) {
            return;
        }

        $username     = smspro_get_option('smspro_name', 'smspro_gateway', '');
        $low_bal_val  = smspro_get_option('low_bal_val', 'smspro_general', '1000');
        $to_mail      = smspro_get_option('alert_email', 'smspro_general', '');
        $trans_credit = self::onlyCredit();

        if (! empty($trans_credit) ) {

            $params       = array(
            'trans_credit' => $trans_credit['credit_balance'],
            'username'     => $username,
            'admin_url'    => admin_url(),
            );
            $emailcontent = get_smspro_template('template/emails/smspro-low-bal.php', $params, true);

            if ($trans_credit['credit_balance'] <= $low_bal_val ) {
                wp_mail($to_mail, '❗ ✱ SMS Pro ✱ Low Balance Alert', $emailcontent, 'content-type:text/html');
            }

            update_option('smspro_background_task', array( 'last_updated_lBal_alert' => date('Y-m-d') ));// update last time and date.
        }
    }

    /**
     * Sends SMS Pro balance daily job.
     *
     * @return void
     */
    public function dailyEmailAlert()
    {
        $username        = smspro_get_option('smspro_name', 'smspro_gateway', '');
        $date            = date('Y-m-d');
        $to_mail         = smspro_get_option('alert_email', 'smspro_general', '');
        $update_datetime = smspro_get_option('last_updated_dBal_alert', 'smspro_background_dBal_task', '');

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
            $dailyemailcontent = get_smspro_template('template/emails/daily-email-alert.php', $params, true);
            update_option('smspro_background_dBal_task', array( 'last_updated_dBal_alert' => date('Y-m-d') ));// update last time and date.
            wp_mail($to_mail, '✱ SMS Pro ✱ Daily  Balance Alert ', $dailyemailcontent, 'content-type:text/html');
        }
    }
    /**
     * Update Order buyer notify meta in checkout page
     *
     * @param integer $order_id Order id.
     *
     * @return void
     */
    public function buyerNotificationUpdateOrderMeta( $order_id )
    {
        if (! empty($_POST['buyer_sms_notify']) ) {
            update_post_meta($order_id, '_buyer_sms_notify', sanitize_text_field(wp_unslash($_POST['buyer_sms_notify'])));
        }
    }

    /**
     * Executes on order place event from woocommerce.
     *
     * @param integer $order_id Order id.
     *
     * @return void
     */
    public function saWcOrderPlace( $order_id )
    {
        if (! $order_id ) {
            return;
        }
        WooCommerceCheckOutForm::trigger_after_order_place($order_id, 'pending', 'pending');
    }
} // SMSPro_WC_Order_SMS

/**
 * Loaded after all plugin initialize
 *
     * @return void
 */
add_action('plugins_loaded', 'loadSaWcOrderSms');
add_action('init', array('SMSPro_WC_Order_SMS','localization_setup'));

/**
 * Sets cron schedules.
 *
 * @param integer $intervals Interval at which cron to be executed.
 *
 * @return void
 */
function additionalCronIntervals( $intervals )
{
    $intervals['sendsms_interval'] = array(
    'interval' => CART_CRON_INTERVAL * 60,
    'display'  => 'Every 10 minutes',
    );
    $intervals['sendremindersms_interval'] = array(
    'interval' => BOOKING_REMINDER_CRON_INTERVAL * 60,
    'display'  => 'Every 60 minutes',
    );
    return $intervals;
}

add_filter('cron_schedules', 'additionalCronIntervals');

/**
 * Executed on plugin load.
 *
 * @return void
 */
function loadSaWcOrderSms()
{
    $smspro = SMSPro_WC_Order_SMS::init();
}
register_activation_hook(__FILE__, array( 'smspro_WC_Order_SMS', 'runOnActivate' ));
register_deactivation_hook(__FILE__, array( 'smspro_WC_Order_SMS', 'runOnDeactivate' ));
register_uninstall_hook(__FILE__, array( 'smspro_WC_Order_SMS', 'runOnUninstall' ));