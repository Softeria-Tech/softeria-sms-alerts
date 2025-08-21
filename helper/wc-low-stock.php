<?php
/**
 * Woocommerce low stock helper.
 * PHP version 5
 *
 * @category Helper
 * @package  SOFTSMSAlerts
 * @author   Softeria Tech <billing@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 */

if (! defined('ABSPATH') ) {
    exit;
}
if (! is_plugin_active('woocommerce/woocommerce.php') ) {
    return;
}
    /**
     * PHP version 5
     *
     * @category Helper
     * @package  SOFTSMSAlerts
     * @author   Softeria Tech <billing@softeriatech.com>
     * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
     * @link     https://sms.softeriatech.com/
     * WCLowStock class
     */
class WCLowStock
{
    /**
     * Construct function
     *
     * @return array
     */
    public function __construct()
    {
        add_action('sa_addTabs', array( $this, 'addTabs' ), 100);
        add_action('woocommerce_low_stock', array( $this, 'smsproSendMsgLowStock' ), 11);
        add_action('woocommerce_no_stock', array( $this, 'smsproSendMsgOutOfStock' ), 10);
    }

    /**
     * Add tabs to softsmsalerts settings at backend.
     *
     * @param array $tabs tabs.
     *
     * @return array
     */
    public static function addTabs( $tabs = array() )
    {
        $backinstock_param = array(
        'checkTemplateFor' => 'wc_stocknotification',
        'templates'        => self::getWcStockTemplates(),
        );

        $tabs['woocommerce']['inner_nav']['wc_stocknotification']['title']       = __('Stock', 'soft-sms-alerts');
        $tabs['woocommerce']['inner_nav']['wc_stocknotification']['tab_section'] = 'backinstocktemplates';
        $tabs['woocommerce']['inner_nav']['wc_stocknotification']['tabContent']  = $backinstock_param;
        $tabs['woocommerce']['inner_nav']['wc_stocknotification']['filePath']    = 'views/message-template.php';
        $tabs['woocommerce']['inner_nav']['wc_stocknotification']['icon']        = 'dashicons-products';
        return $tabs;
    }

    /**
     * Get wc stock templates.
     *
     * @return array
     */
    public static function getWcStockTemplates()
    {
        $softeria_alerts_low_stock_admin_msg = softeria_alerts_get_option('admin_low_stock_msg', 'softeria_alerts_general', 'on');
        $sms_body_admin_low_stock_msg = softeria_alerts_get_option('sms_body_admin_low_stock_msg', 'softeria_alerts_message', SmsAlertMessages::showMessage('DEFAULT_ADMIN_LOW_STOCK_MSG'));

        $softeria_alerts_out_of_stock_admin_msg = softeria_alerts_get_option('admin_out_of_stock_msg', 'softeria_alerts_general', 'on');
        $sms_body_admin_out_of_stock_msg = softeria_alerts_get_option('sms_body_admin_out_of_stock_msg', 'softeria_alerts_message', SmsAlertMessages::showMessage('DEFAULT_ADMIN_OUT_OF_STOCK_MSG'));

        $templates = array();

        $low_stock_variables                      = array(
        '[item_name]'  => 'Product Name',
        '[store_name]' => 'Store Name',
        '[item_qty]'   => 'Quantity',
        '[shop_url]'   => 'Shop Url',
        );
        $templates['low-stock']['title']          = 'When product is in low stock';
        $templates['low-stock']['enabled']        = $softeria_alerts_low_stock_admin_msg;
        $templates['low-stock']['status']         = 'low-stock';
        $templates['low-stock']['text-body']      = $sms_body_admin_low_stock_msg;
        $templates['low-stock']['checkboxNameId'] = 'softeria_alerts_general[admin_low_stock_msg]';
        $templates['low-stock']['textareaNameId'] = 'softeria_alerts_message[sms_body_admin_low_stock_msg]';
        $templates['low-stock']['token']          = $low_stock_variables;

        $out_of_stock_variables                      = array(
        '[item_name]'  => 'Product Name',
        '[store_name]' => 'Store Name',
        '[item_qty]'   => 'Quantity',
        '[shop_url]'   => 'Shop Url',
        );
        $templates['out-of-stock']['title']          = 'When product is out of stock';
        $templates['out-of-stock']['enabled']        = $softeria_alerts_out_of_stock_admin_msg;
        $templates['out-of-stock']['status']         = 'out-of-stock';
        $templates['out-of-stock']['text-body']      = $sms_body_admin_out_of_stock_msg;
        $templates['out-of-stock']['checkboxNameId'] = 'softeria_alerts_general[admin_out_of_stock_msg]';
        $templates['out-of-stock']['textareaNameId'] = 'softeria_alerts_message[sms_body_admin_out_of_stock_msg]';
        $templates['out-of-stock']['token']          = $out_of_stock_variables;

        return $templates;
    }

    /**
     * Smsalert send sms on low stock function.
     *
     * @param object $product product.
     *
     * @return array
     */
    public function smsproSendMsgLowStock( $product )
    {
        $message = softeria_alerts_get_option('sms_body_admin_low_stock_msg', 'softeria_alerts_message', '');
        $message = $this->parseSmsBody($product, $message);

        $sms_admin_phone = softeria_alerts_get_option('sms_admin_phone', 'softeria_alerts_message', '');

        $softeria_alerts_notification_low_stock_admin_msg = softeria_alerts_get_option('admin_low_stock_msg', 'softeria_alerts_general', 'on');

        if ('on' === $softeria_alerts_notification_low_stock_admin_msg && '' !== $message ) {
            $admin_phone_number = str_replace('postauthor', 'post_author', $sms_admin_phone);
            $author_no          = apply_filters('sa_post_author_no', $product->get_id());
            if (( strpos($admin_phone_number, 'post_author') !== false ) && ! empty($author_no) ) {
                $admin_phone_number = str_replace('post_author', $author_no, $admin_phone_number);
            }

            do_action('sa_send_sms', $admin_phone_number, $message);
        }
    }

    /**
     * Smsalert send sms on out of stock function.
     *
     * @param object $product product.
     *
     * @return array
     */
    public function smsproSendMsgOutOfStock( $product )
    {
        $message = softeria_alerts_get_option('sms_body_admin_out_of_stock_msg', 'softeria_alerts_message', '');
        $message = $this->parseSmsBody($product, $message);

        $sms_admin_phone = softeria_alerts_get_option('sms_admin_phone', 'softeria_alerts_message', '');

        $softeria_alerts_notification_out_of_stock_admin_msg = softeria_alerts_get_option('admin_out_of_stock_msg', 'softeria_alerts_general', 'on');
        if ('on' === $softeria_alerts_notification_out_of_stock_admin_msg && '' !== $message ) {
            $admin_phone_number = str_replace('postauthor', 'post_author', $sms_admin_phone);
            $author_no          = apply_filters('sa_post_author_no', $product->get_id());

            if (( strpos($admin_phone_number, 'post_author') !== false ) && ! empty($author_no) ) {
                $admin_phone_number = str_replace('post_author', $author_no, $admin_phone_number);
            }

            do_action('sa_send_sms', $admin_phone_number, $message);
        }
    }

    /**
     * Parse sms body function
     *
     * @param object $product product.
     * @param string $message message.
     *
     * @return string
     */
    public function parseSmsBody( $product, $message )
    {

        $item_name = $product->get_name();
        $item_qty  = $product->get_stock_quantity();

        $find = array(
        '[item_name]',
        '[item_qty]',
        '[store_name]',
        '[shop_url]',
        );

        $replace = array(
            $item_name,
            $item_qty,
            get_bloginfo('name'),
            get_site_url(),
        );

        $message = str_replace($find, $replace, $message);
        return $message;
    }
}
new WCLowStock();
