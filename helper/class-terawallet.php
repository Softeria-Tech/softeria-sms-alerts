<?php
/**
 * Woo wallet notification helper.
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
if (! is_plugin_active('woo-wallet/woo-wallet.php') ) {
    return;
}
    /**
     * PHP version 5
     *
     * @category Handler
     * @package  SOFTSMSAlerts
     * @author   Softeria Tech <billing@softeriatech.com>
     * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
     * @link     https://sms.softeriatech.com/
     * TeraWallet class
     */
class TeraWallet
{

    /**
     * Construct function
     *
     * @return array
     */
    public function __construct()
    {
        add_action('sa_addTabs', array( $this, 'addTabs' ), 100);
        add_filter('sAlertDefaultSettings', __CLASS__ . '::add_default_setting', 1);
        add_action('woo_wallet_transaction_recorded', array( $this, 'smsproSendMsgWalletTransaction' ), 11, 4);
    }

    /**
     * Add tabs to smspro settings at backend.
     *
     * @param array $tabs tabs.
     *
     * @return array
     */
    public static function addTabs( $tabs = array() )
    {
        $woowallet_param = array(
        'checkTemplateFor' => 'wc_woowalletnotification',
        'templates'        => self::getWcWoowalletTemplates(),
        );

        $tabs['woocommerce']['inner_nav']['wc_woowalletnotification']['title']       = __('TeraWallet Notifications', 'softeria-sms-alerts');
        $tabs['woocommerce']['inner_nav']['wc_woowalletnotification']['tab_section'] = 'woowallettemplates';
        $tabs['woocommerce']['inner_nav']['wc_woowalletnotification']['tabContent']  = $woowallet_param;
        $tabs['woocommerce']['inner_nav']['wc_woowalletnotification']['filePath']    = 'views/message-template.php';
        $tabs['woocommerce']['inner_nav']['wc_woowalletnotification']['icon']        = 'dashicons-products';
        return $tabs;
    }

    /**
     * Add default settings to savesetting in setting-options.
     *
     * @param array $defaults defaults.
     *
     * @return array
     */
    public static function add_default_setting( $defaults = array() )
    {
        $defaults['softeria_alerts_general']['woo_wallet_credited_msg']          = 'off';
        $defaults['softeria_alerts_general']['sms_body_woo_wallet_credited_msg'] = '';
        $defaults['softeria_alerts_general']['woo_wallet_debited_msg']           = 'off';
        $defaults['softeria_alerts_general']['sms_body_woo_wallet_debited_msg']  = '';
        return $defaults;
    }

    /**
     * Get wc woowallet templates.
     *
     * @return array
     */
    public static function getWcWoowalletTemplates()
    {
        $woo_wallet_credited_msg          = softeria_alerts_get_option('woo_wallet_credited_msg', 'softeria_alerts_general', 'on');
        $sms_body_woo_wallet_credited_msg = softeria_alerts_get_option('sms_body_woo_wallet_credited_msg', 'softeria_alerts_message', sprintf(__('Dear %1$s, %2$s has been credited to your wallet. Current wallet balance is : %3$s.%4$sPowered by%5$ssms.softeriatech.com', 'softeria-sms-alerts'), '[username]', '[amount]', '[wallet_amount]', PHP_EOL, PHP_EOL));

        $woo_wallet_debited_msg          = softeria_alerts_get_option('woo_wallet_debited_msg', 'softeria_alerts_general', 'on');
        $sms_body_woo_wallet_debited_msg = softeria_alerts_get_option('sms_body_woo_wallet_debited_msg', 'softeria_alerts_message', sprintf(__('Dear %1$s, %2$s has been debited from your wallet. Current wallet balance is : %3$s.%4$sPowered by%5$ssms.softeriatech.com', 'softeria-sms-alerts'), '[username]', '[amount]', '[wallet_amount]', PHP_EOL, PHP_EOL));

        $templates = array();

        $woo_wallet_variables                               = array(
        '[username]'      => 'Username',
        '[amount]'        => 'Amount',
        '[wallet_amount]' => 'Wallet Amount',
        '[store_name]'    => 'Store Name',
        '[shop_url]'      => 'Shop Url',
        );
        $templates['woo-wallet-credited']['title']          = 'When wallet is credited';
        $templates['woo-wallet-credited']['enabled']        = $woo_wallet_credited_msg;
        $templates['woo-wallet-credited']['status']         = 'woo-wallet-credited';
        $templates['woo-wallet-credited']['text-body']      = $sms_body_woo_wallet_credited_msg;
        $templates['woo-wallet-credited']['checkboxNameId'] = 'softeria_alerts_general[woo_wallet_credited_msg]';
        $templates['woo-wallet-credited']['textareaNameId'] = 'softeria_alerts_message[sms_body_woo_wallet_credited_msg]';
        $templates['woo-wallet-credited']['token']          = $woo_wallet_variables;

        $templates['woo-wallet-debited']['title']          = 'When wallet is debited';
        $templates['woo-wallet-debited']['enabled']        = $woo_wallet_debited_msg;
        $templates['woo-wallet-debited']['status']         = 'woo-wallet-debited';
        $templates['woo-wallet-debited']['text-body']      = $sms_body_woo_wallet_debited_msg;
        $templates['woo-wallet-debited']['checkboxNameId'] = 'softeria_alerts_general[woo_wallet_debited_msg]';
        $templates['woo-wallet-debited']['textareaNameId'] = 'softeria_alerts_message[sms_body_woo_wallet_debited_msg]';
        $templates['woo-wallet-debited']['token']          = $woo_wallet_variables;

        return $templates;
    }

    /**
     * Smsalert send sms on low stock function.
     *
     * @param int    $transaction_id Transaction Id.
     * @param int    $user_id        User Id.
     * @param int    $amount         Amount.
     * @param string $type           Transaction Type.
     *
     * @return array
     */
    public function smsproSendMsgWalletTransaction( $transaction_id, $user_id, $amount, $type )
    {
        if ('credit' === $type ) {
            $message                 = softeria_alerts_get_option('sms_body_woo_wallet_credited_msg', 'softeria_alerts_message', '');
            $message                 = $this->parseSmsBody($user_id, $amount, $message);
            $woo_wallet_credited_msg = softeria_alerts_get_option('woo_wallet_credited_msg', 'softeria_alerts_general', 'on');
            if ('on' === $woo_wallet_credited_msg && '' !== $message ) {
                $user_phone = get_user_meta($user_id, 'billing_phone', true);
                //do_action( 'sa_send_sms', $user_phone, $message );
                $obj             = array();
                $obj['number']   = $user_phone;
                $obj['sms_body'] = $message;
                SmsAlertcURLOTP::sendsms($obj);
            }
        } elseif ('debit' === $type ) {
            $message                = softeria_alerts_get_option('sms_body_woo_wallet_debited_msg', 'softeria_alerts_message', '');
            $message                = $this->parseSmsBody($user_id, $amount, $message);
            $woo_wallet_debited_msg = softeria_alerts_get_option('woo_wallet_debited_msg', 'softeria_alerts_general', 'on');
            if ('on' === $woo_wallet_debited_msg && '' !== $message ) {
                $user_phone = get_user_meta($user_id, 'billing_phone', true);
                //do_action( 'sa_send_sms', $user_phone, $message );
                $obj             = array();
                $obj['number']   = $user_phone;
                $obj['sms_body'] = $message;
                SmsAlertcURLOTP::sendsms($obj);
            }
        }
    }

    /**
     * Parse sms body function
     *
     * @param int    $user_id user id.
     * @param int    $amount  amount.
     * @param string $message message.
     *
     * @return string
     */
    public function parseSmsBody( $user_id, $amount, $message )
    {
        $find      = array(
        '[username]',
        '[amount]',
        '[wallet_amount]',
        '[store_name]',
        '[shop_url]',
        );
        $user_info = get_userdata($user_id);
        $replace   = array(
        $user_info->user_login,
        $amount,
        woo_wallet()->wallet->get_wallet_balance($user_id, 'edit'),
        get_bloginfo('name'),
        get_site_url(),
        );
        $message   = str_replace($find, $replace, $message);
        return $message;
    }
}
new TeraWallet();
