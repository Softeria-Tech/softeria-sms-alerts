<?php
/**
 * Smsalert phone logic 
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
 * Phone logic class.
 */
class PhoneLogic extends LogicInterface
{

    /**
     * Main Logic handler.
     *
     * @param string $user_login   User name.
     * @param string $user_email   User email id.
     * @param string $phone_number Phone number.
     * @param string $otp_type     OTP type.
     * @param string $form         Form name.
     *
     * @return void
     */
    public function _handle_logic( $user_login, $user_email, $phone_number, $otp_type, $form )
    {
        //$match = preg_match( SOFTSMAL_Constants::getPhonePattern(), $phone_number );
        
        /* switch ( $match ) {
        case 0:
        $this->_handle_not_matched( $phone_number, $otp_type, $form );
        break;
        case 1:
        $this->_handle_matched( $user_login, $user_email, $phone_number, $otp_type, $form );
        break;
        } */
        
        if (! SOFTSMAL_cURLOTP::checkPhoneNos($phone_number) ) {
            $this->_handle_not_matched($phone_number, $otp_type, $form);
        } else {
            $this->_handle_matched($user_login, $user_email, $phone_number, $otp_type, $form);
        }
    }

    /**
     * Handles OTP matched action.
     *
     * @param string $user_login   User name.
     * @param string $user_email   User email id.
     * @param string $phone_number Phone number.
     * @param string $otp_type     OTP type.
     * @param string $form         Form name.
     *
     * @return void
     */
    public function _handle_matched( $user_login, $user_email, $phone_number, $otp_type, $form )
    {
        
        $content = SOFTSMAL_cURLOTP::smsproSendOtpToken($form, '', $phone_number);
        $status  = array_key_exists('status', $content) ? $content['status'] : '';

        switch ( $status ) {
        case true:
            $this->_handle_otp_sent($user_login, $user_email, $phone_number, $otp_type, $form, $content);
            break;
        default:
            $this->_handle_otp_sent_failed($user_login, $user_email, $phone_number, $otp_type, $form, $content);
            break;
        }
    }

    /**
     * Handles OTP not matched action.
     *
     * @param string $phone_number Phone number.
     * @param string $otp_type     OTP type.
     * @param string $form         Form name.
     *
     * @return void
     */
    public function _handle_not_matched( $phone_number, $otp_type, $form )
    {
        SOFTSMAL_Utility::checkSession();

        $message = str_replace('##phone##', $phone_number, self::_get_otp_invalid_format_message());
        if (self::_is_ajax_form() ) {
            wp_send_json(SOFTSMAL_Utility::_create_json_response($message, SOFTSMAL_Constants::ERROR_JSON_TYPE));
        } else {
            softeria_alerts_site_otp_validation_form(null, null, null, $message, $otp_type, $form);
        }
    }

    /**
     * Handles OTP sent failed.
     *
     * @param string $user_login   user name.
     * @param string $user_email   User email id.
     * @param string $phone_number Phone number.
     * @param string $otp_type     OTP type.
     * @param string $form         Form name.
     * @param string $content      Content.
     *
     * @return void
     */
    public function _handle_otp_sent_failed( $user_login, $user_email, $phone_number, $otp_type, $form, $content )
    {
        SOFTSMAL_Utility::checkSession();
        if (isset($content['description']['desc']) ) {
            $message = $content['description']['desc'];
        } elseif (isset($content['description']) && ! is_array($content['description']) ) {
            $message = $content['description'];
        } else {
            $message = str_replace('##phone##', SOFTSMAL_cURLOTP::checkPhoneNos($phone_number), self::_get_otp_sent_failed_message());
        }

        if (self::_is_ajax_form() || ( 'ajax' === $form ) ) {
            wp_send_json(SOFTSMAL_Utility::_create_json_response($message, SOFTSMAL_Constants::ERROR_JSON_TYPE));
        } else {
            softeria_alerts_site_otp_validation_form(null, null, null, $message, $otp_type, $form);
        }
    }

    /**
     * Handles OTP sent success action.
     *
     * @param string $user_login   user name.
     * @param string $user_email   User email id.
     * @param string $phone_number Phone number.
     * @param string $otp_type     OTP type.
     * @param string $form         Form name.
     * @param string $content      Content.
     *
     * @return void
     */
    public function _handle_otp_sent( $user_login, $user_email, $phone_number, $otp_type, $form, $content )
    {
        SOFTSMAL_Utility::checkSession();

        if (! empty($_SESSION[ SOFTSMAL_FormSessionVars::WP_DEFAULT_LOST_PWD ]) ) {
            $number = SOFTSMAL_cURLOTP::checkPhoneNos($phone_number);
            $mob    = str_repeat('x', strlen($number) - 4) . substr($number, -4);
        } else {
            $mob = SOFTSMAL_cURLOTP::checkPhoneNos($phone_number);
        }

        $message = str_replace('##phone##', $mob, self::_get_otp_sent_message());
        if (self::_is_ajax_form() || ( 'ajax' === $form ) ) {
            wp_send_json(SOFTSMAL_Utility::_create_json_response($message, SOFTSMAL_Constants::SUCCESS_JSON_TYPE));
        } else {
            softeria_alerts_site_otp_validation_form($user_login, $user_email, $phone_number, $message, $otp_type, $form);
        }
    }

    /**
     * Gets OTP sent success message.
     *
     * @return void
     */
    public function _get_otp_sent_message()
    {
        if ( SOFTSMAL_Utility::isPlayground()) {
			 return SOFTSMAL_Messages::showMessage( 'OTP_SENT_PHONE' ).'</br> '.SOFTSMAL_Messages::showMessage('OTP_SENT_plarground');
		 }else{
			return !empty(SOFTSMAL_Utility::get_elementor_data("sa_ele_f_mobile_lbl")) ? SOFTSMAL_Utility::get_elementor_data("sa_ele_f_mobile_lbl") : SOFTSMAL_Messages::showMessage( 'OTP_SENT_PHONE' );
		 }
    }

    /**
     * Gets OTP sent failed message.
     *
     * @return void
     */
    public function _get_otp_sent_failed_message()
    {
        /* translators: %s: Plugin help URL */
        return wp_kses_post(sprintf(__("There was an error in sending the OTP to the given Phone Number. Please Try Again or contact site Admin. If you are the website admin, please browse <a href='%s' target='_blank'> here</a> for steps to resolve this error.", 'soft-sms-alerts'), 'https://sms.softeriatech.com/knowledgebase/unable-to-send-otp-from-wordpress-plugin/'));
    }

    /**
     * Gets OTP sent failed due to invalid number format message.
     *
     * @return mixed
     */
    public function _get_otp_invalid_format_message()
    {
        /* translators: %1$s: tag, %2$s: tag */
        return sprintf(__('%1$sphone%2$s is not a valid phone number. Please enter a valid Phone Number', 'soft-sms-alerts'), '##', '##');
    }
}
