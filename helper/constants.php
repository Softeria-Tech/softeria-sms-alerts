<?php
/**
 * Constants helper.
 *
 * PHP version 5
 *
 * @category Handler
 * @package  SOFTSMSAlerts
 * @author   Softeria Tech <billing@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 */
if (! defined('ABSPATH') ) {
    exit;
}
/**
 * PHP version 5
 *
 * @category Handler
 * @package  SOFTSMSAlerts
 * @author   Softeria Tech <billing@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 * SmsAlertConstants class
 */
class SmsAlertConstants
{

    const SUCCESS                = 'SUCCESS';
    const FAILURE                = 'FAILURE';
    const TEXT_DOMAIN            = 'soft-sms-alerts';
    const PATTERN_PHONE          = '/^(\+)?(country_code)?0?\d+$/'; // '/^\d{10}$/';//'/\d{10}$/';
    const ERROR_JSON_TYPE        = 'error';
    const SUCCESS_JSON_TYPE      = 'success';
    const USERPRO_VER_FIELD_META = 'verification_form';
    const SA_VERSION             = '1.0.0';
    
    
    
    /**
     * Construct function.
     * 
     * @return string
     */
    function __construct()
    {
        $this->defineGlobal();
    }

    /**
     * Get Phone Pattern.
     * 
     * @return string
     */
    public static function getPhonePattern()
    {
        $country_code      = softeria_alerts_get_option('default_country_code', 'softeria_alerts_general');
        $sa_mobile_pattern = softeria_alerts_get_option('sa_mobile_pattern', 'softeria_alerts_general', '/^(\+)?(country_code)?0?\d{10}$/');
        $pattern           = ( '' !== $sa_mobile_pattern ) ? $sa_mobile_pattern : self::PATTERN_PHONE;
        $country_code      = str_replace('+', '', $country_code);
        $pattern_phone     = str_replace('country_code', $country_code, $pattern);
        return $pattern_phone;
    }

    /**
     * Define global function.
     * 
     * @return void
     */
    function defineGlobal()
    {
        global $phoneLogic;
        $phoneLogic = new PhoneLogic();
        define('SA_MOV_DIR', plugin_dir_path(dirname(__FILE__)));
        define('SA_MOV_URL', plugin_dir_url(dirname(__FILE__)));
        define('SA_MOV_CSS_URL', SA_MOV_URL . 'css/softeria_alerts_customer_validation_style.css');
        define('SA_MOV_LOADER_URL', SA_MOV_URL . 'images/ajax-loader.gif');
    }
}
new SmsAlertConstants();
