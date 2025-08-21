<?php
/**
 * Curl helper.
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
 * SmsAlertcURLOTP class 
 */
class SmsAlertcURLOTP
{
    protected static $url="https://sms.softeriatech.com/api/v1/bulksms";

    /**
     * Add tabs to smspro settings at backend.
     *
     * @param string $template template.
     *
     * @return void
     */
    public static function sendtemplatemismatchemail( $template )
    {
        $username = softeria_alerts_get_option('softeria_alerts_name', 'softeria_alerts_gateway', '');
        $to_mail  = softeria_alerts_get_option('alert_email', 'softeria_alerts_general', '');

        // Email template with content
        $params       = array(
        'template'    => nl2br($template),
        'username'    => $username,
        'server_name' => ( ( ! empty($_SERVER['SERVER_NAME']) ) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : '' ),
        'admin_url'   => admin_url(),
        );
        $emailcontent = get_softeria_alerts_template('template/emails/mismatch-template.php', $params, true);
        wp_mail($to_mail, 'Softeria Tech - Template Mismatch', $emailcontent, 'content-type:text/html');
    }

    /**
     * Send email For Invalid Credentials.
     *
     * @param string $template template.
     *
     * @return void
     */
    public static function sendemailForInvalidCred( $template )
    {
        $username = softeria_alerts_get_option('softeria_alerts_name', 'softeria_alerts_gateway', '');
        $to_mail  = softeria_alerts_get_option('alert_email', 'softeria_alerts_general', '');

        // Email template with content
        $params       = array(
        'template'    => nl2br($template),
        'username'    => $username,
        'server_name' => ( ( ! empty($_SERVER['SERVER_NAME']) ) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : '' ),
        'admin_url'   => admin_url(),
        );
        $emailcontent = get_softeria_alerts_template('template/emails/invalid-credentials.php', $params, true);
        wp_mail($to_mail, 'Softeria Tech - Wrong Credentials', $emailcontent, 'content-type:text/html');
    }
    
    /**
     * Send email For Dormant Account.
     *
     * @param string $template template.
     *
     * @return void
     */
    public static function sendemailForDormant( $template )
    {
        $username = softeria_alerts_get_option('softeria_alerts_name', 'softeria_alerts_gateway', '');
        $to_mail  = softeria_alerts_get_option('alert_email', 'softeria_alerts_general', '');

        // Email template with content
        $params       = array(
        'template'    => nl2br($template),
        'username'    => $username,
        'server_name' => ( ( ! empty($_SERVER['SERVER_NAME']) ) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : '' ),
        'admin_url'   => admin_url(),
        );
        $emailcontent = get_softeria_alerts_template('template/emails/dormant-account.php', $params, true);
        wp_mail($to_mail, 'Softeria Tech - Dormant Account', $emailcontent, 'content-type:text/html');
    }

    /**
     * Check Phone Numbers.
     *
     * @param string  $nos          numbers.
     * @param boolean $force_prefix force_prefix.
     *
     * @return string
     */
    public static function checkPhoneNos( $nos = null, $force_prefix = true )
    {
        $country_code         = softeria_alerts_get_option('default_country_code', 'softeria_alerts_general');
        $country_code_enabled = softeria_alerts_get_option('checkout_show_country_code', 'softeria_alerts_general');
        $nos                  = !empty($nos)?explode(',', $nos):'';
        $valid_no             = array();
        if (is_array($nos) ) {
            foreach ( $nos as $no ) {
                $no = ltrim(ltrim($no, '+'), '0'); // remove leading + and 0
                $no = preg_replace('/[^0-9]/', '', $no);// remove spaces and special characters

                if (! empty($no) ) {

                    //if ( 'on' === $country_code_enabled ) {
                    //$valid_no[] = $no;
                    //} 
                    //else {
                    if (! $force_prefix ) {
                        $no = ( substr($no, 0, strlen($country_code)) == $country_code ) ? substr($no, strlen($country_code)) : $no;
                    } else {
                        $no = ( substr($no, 0, strlen($country_code)) != $country_code ) ? $country_code . $no : $no;
                    }
                    $match = preg_match(SmsAlertConstants::getPhonePattern(), $no);
                    if ($match ) {
                        $valid_no[] = $no;
                    }
                    //}
                }
            }
        }
        if (sizeof($valid_no) > 0 ) {
            return implode(',', $valid_no);
        } else {
            return false;
        }
    }

    /**
     * Send sms.
     *
     * @param array $sms_data sms_data.
     *
     * @return array
     */
    public static function sendsms( $sms_data )
    {
        $response = false;
        $username = softeria_alerts_get_option('softeria_alerts_name', 'softeria_alerts_gateway');
        $password = softeria_alerts_get_option('softeria_alerts_password', 'softeria_alerts_gateway');
        $senderid = softeria_alerts_get_option('softeria_alerts_api', 'softeria_alerts_gateway');

        $enable_short_url = softeria_alerts_get_option('enable_short_url', 'softeria_alerts_general');

        $phone = self::checkPhoneNos($sms_data['number']);
        if ($phone === false ) {
            $data                = array();
            $data['status']      = 'error';
            $data['description'] = 'phone number not valid';
            return json_encode($data);
        }
        $text = htmlspecialchars_decode($sms_data['sms_body']);
        // bail out if nothing provided
        if (empty($password) || empty($senderid) || empty($text) ) {
            return $response;
        }

        $url    = self::$url.'/send';
        $fields = array(
        'user'     => $username,
        'pro_api_key'=> $password,
        'mobiles' => $phone,
        'sender_name'=> $senderid,
        'message' => $text,
        );

        if (! empty($sms_data['schedule']) ) {
            $fields['schedule'] = $sms_data['schedule'];
        } //add on 27-08-20
        if ($enable_short_url === 'on' ) {
            $fields['shortenurl'] = 1;
        }
        $fields       = apply_filters('sa_before_send_sms', $fields);
        $response     = self::callAPI($url, $fields, null);
        $response_arr = $response;

        $text = ! empty($fields['text']) ? $fields['text'] : $text;
        apply_filters('sa_after_send_sms', $response_arr);

        if ($response_arr['status'] === 'error' ) {
            $error = ( is_array($response_arr['description']) ) ? $response_arr['description']['desc'] : $response_arr['description'];
            if ($error === 'Invalid Template Match' ) {
                self::sendtemplatemismatchemail($text);
            }
        }
        return $response;
    }
    
    /**
     * Validate Country Code.
     *
     * @param string $phone phone.
     *
     * @return array
     */
    public static function validateCountryCode($phone)
    {        
        $phone                  = self::checkPhoneNos($phone);                
        $allow_otp_country      = (array) softeria_alerts_get_option('allow_otp_country', 'softeria_alerts_general', null);
        $allow_otp_verification = softeria_alerts_get_option('allow_otp_verification', 'softeria_alerts_general', 'off');
        $flag = false;
        if ('on' === $allow_otp_verification && '' !== $allow_otp_country) {
            foreach ($allow_otp_country as $country_code) {
                if (substr(trim($phone, "+"), 0, strlen($country_code)) == $country_code) {    
                    $flag = true;
                    break;
                }
            }
        } else {
            $flag = true;
        }
        return $flag;          
    }

    /**
     * Smsalert send otp token.
     *
     * @param string $form  form.
     * @param string $email email.
     * @param string $phone phone.
     *
     * @return array
     */
    public static function smsproSendOtpToken( $form, $email = '', $phone = '' )
    {
		if (SmsAlertUtility::isPlayground()) {
		 return  '{"status": "success","description": {"desc": "1 messages scheduled for delievery"}}';
		} 
        $phone                  = self::checkPhoneNos($phone);        
        $cookie_value           = get_softeria_alerts_cookie($phone);
        $max_otp_resend_allowed = !empty(SmsAlertUtility::get_elementor_data("max_otp_resend_allowed"))?SmsAlertUtility::get_elementor_data("max_otp_resend_allowed"):softeria_alerts_get_option('max_otp_resend_allowed', 'softeria_alerts_general', '4');

        if ($cookie_value >= $max_otp_resend_allowed ) {
            $data                        = array();
            $data['status']              = 'error';
            $data['data']['msg'] = __('Maximum OTP limit exceeded', 'softeria-sms-alerts');
            return json_encode($data);
        }

        $response = [];
        $username = softeria_alerts_get_option('softeria_alerts_name', 'softeria_alerts_gateway');
        $password = softeria_alerts_get_option('softeria_alerts_password', 'softeria_alerts_gateway');
        $senderid = softeria_alerts_get_option('softeria_alerts_api', 'softeria_alerts_gateway');
        $template = softeria_alerts_get_option('sms_otp_send', 'softeria_alerts_message', SmsAlertMessages::showMessage('DEFAULT_BUYER_OTP'));
        $template = str_replace(array('[store_name]','[shop_url]'), array(get_bloginfo(),get_site_url()), $template);

        if ($phone === false ) {
            $data                        = array();
            $data['status']              = 'error';
            $data['data']['msg'] = __('phone number not valid', 'softeria-sms-alerts');
            return $data;
        }

        if (empty($password) || empty($senderid) ) {
            $data                        = array();
            $data['status']              = 'error';
            $data['data']['msg'] = __('Wrong SOFTSMSAlerts credentials', 'softeria-sms-alerts');
            return $data;
        }
        $url = self::$url.'/mverify';

        $fields= array(
            'user'     => $username,
            'pro_api_key'=> $password,
            'mobiles' => $phone,
            'sender_name'   => $senderid,
            'template' => $template,
        );
        $response     = self::callAPI($url, $fields, null);
        $response_arr = $response;
        if (array_key_exists('status', $response_arr) && $response_arr['status'] === 'error' ) {
            $error = ( is_array($response_arr['data']) ) ? $response_arr['data']['msg'] : $response_arr['data'];
            if ($error == 'Invalid Template Match' ) {
                self::sendtemplatemismatchemail($template);
                $response = [];
            }
        } else {
            create_softeria_alerts_cookie($phone, $cookie_value + 1);
        }

        return $response;
    }

    /**
     * Smsalert validate otp token.
     *
     * @param string $mobileno mobileno.
     * @param string $otpToken otpToken.
     *
     * @return array
     */
    public static function validateOtpToken( $mobileno, $otpToken )
    {
        if (empty($otpToken) ) {
            return [];
        }
        if (SmsAlertUtility::isPlayground()) {
			if ( $otpToken == '1234') {
			   $response = '{"status": "success","description": {"desc": "Code Matched successfully."}}';
			} else {
				 $response = '{"status": "success","description": {"desc": "Code does not match."}}';
			}
			return $response;
	    } 
        $response = [];
        $username = softeria_alerts_get_option('softeria_alerts_name', 'softeria_alerts_gateway');
        $password = softeria_alerts_get_option('softeria_alerts_password', 'softeria_alerts_gateway');
        $senderid = softeria_alerts_get_option('softeria_alerts_api', 'softeria_alerts_gateway');
        $mobileno = self::checkPhoneNos($mobileno);
        if ($mobileno === false ) {
            $data                = array();
            $data['status']      = 'error';
            $data['data'] = 'phone number not valid';
            return  $data;
        }

        if (empty($password) || empty($senderid) ) {
            return $response;
        }
        $url = self::$url.'/mverify';

        $fields = array(
            'user'     => $username,
            'pro_api_key'      => $password,
            'mobiles' => $mobileno,
            'code'     => $otpToken,
        );

        $response = self::callAPI($url, $fields, null);
        if (array_key_exists('status', $response) && $response['status'] === 'error' ) {
            clear_softeria_alerts_cookie($mobileno);
        }

        return $response;
    }

    /**
     * Get senderids.
     *
     * @param string $username username.
     * @param string $password password.
     *
     * @return array
     */
    public static function getSenderids( $username = null, $password = null )
    {
		if (SmsAlertUtility::isPlayground()) {			
			return	true;			
		}
		
        if (empty($password) ) {
            return '';
        }

        $url = self::$url.'/senderids';

        $fields = array(
        'user' => $username,
        'pro_api_key'  => $password,
        );

        $response = self::callAPI($url, $fields, null);
        return $response;
    }

    /**
     * Get templates.
     *
     * @param string $username username.
     * @param string $password password.
     *
     * @return array
     */
    public static function getTemplates( $username = null, $password = null )
    {
        if (empty($username) || empty($password) ) {
            return '';
        }
        $url = 'http://sms.softeriatech.com/api/templatelist.json';

        $fields = array(
        'user'  => $username,
        'pro_api_key'   => $password,
        'limit' => 100,
        );

        $response = self::callAPI($url, $fields, null);
        return $response;
    }

    /**
     * Get credits.
     *
     * @return array
     */
    public static function getCredits()
    {
		if (SmsAlertUtility::isPlayground()) {	
            error_log('Softeria Tech: Playground mode, skipping getCredits call');		
			return	true;			
		}
        $response = [];
        $username = softeria_alerts_get_option('softeria_alerts_name', 'softeria_alerts_gateway');
        $password = softeria_alerts_get_option('softeria_alerts_password', 'softeria_alerts_gateway');

        if (empty($password) ) {
            return $response;
        }

        $url = self::$url.'/units';

        $fields   = array(
            'user' => $username,
            'pro_api_key'  => $password,
        );
        $response = self::callAPI($url, $fields, null);
        return $response;
    }

    /**
     * Group list.
     *
     * @return array
     */
    public static function groupList()
    {
        $username = softeria_alerts_get_option('softeria_alerts_name', 'softeria_alerts_gateway');
        $password = softeria_alerts_get_option('softeria_alerts_password', 'softeria_alerts_gateway');

        if (empty($password) ) {
            return '';
        }

        $url = self::$url.'/grouplist';

        $fields = array(
        'user' => $username,
        'pro_api_key'  => $password,
        );

        $response = self::callAPI($url, $fields, null);
        return $response;
    }

    /**
     * Get country list.
     *
     * @return array
     */
    public static function country_list()
    {
		if (SmsAlertUtility::isPlayground()) {			
			return	true;			
		}
        $url= self::$url.'/countries';
        $response = self::callAPI($url, null, null);
        return $response;
    } 

    /**
     * Create group.
     *
     * @return array
     */
    public static function creategrp()
    {
        $username = softeria_alerts_get_option('softeria_alerts_name', 'softeria_alerts_gateway');
        $password = softeria_alerts_get_option('softeria_alerts_password', 'softeria_alerts_gateway');

        if (empty($password) ) {
            return '';
        }

        $url = 'http://sms.softeriatech.com/api/creategroup.json';

        $fields = array(
        'user' => $username,
        'pro_api_key'  => $password,
        'name' => $_SERVER['SERVER_NAME'],
        );

        $response = self::callAPI($url, $fields, null);
        return $response;
    }

    /**
     * Create contact.
     *
     * @param array  $sms_datas    sms_datas.
     * @param string $group_name   group_name.
     * @param array  $extra_fields extra_fields.
     *
     * @return array
     */
    public static function createContact( $sms_datas, $group_name, $extra_fields = array() )
    {
        if (is_array($sms_datas) && sizeof($sms_datas) == 0 ) {
            return [
                'status'      => 'error',
                'description' => 'No Contacts to add',
            ];
        }

        if (empty($group_name) ) {
            return [
                'status'      => 'error',
                'description' => 'Group name is required',
            ];
        }

        $username = softeria_alerts_get_option('softeria_alerts_name', 'softeria_alerts_gateway');
        $password = softeria_alerts_get_option('softeria_alerts_password', 'softeria_alerts_gateway');

        $fields=[
            'user' => $username,
            'pro_api_key'  => $password,
            'name' => $group_name,
        ];

        $mobiles= [];
        $cnt     = 0;
        foreach ( $sms_datas as $sms_data ) {
            $phone = self::checkPhoneNos($sms_data['number']);

            if ($phone !== false ) {
                $mobiles[] = $phone;                
                $cnt++;
            }
        }

        $fields['contacts'] = implode(',', $mobiles);
        $url     = self::$url.'/updatecontacts';
        if ($cnt > 0 ) {
            $response = self::callAPI($url, $fields, null);
        } else {
            $response = json_encode(
                array(
                'status'      => 'error',
                'description' => 'Invalid WC Users Contact Numbers',
                )
            );
        }

        return $response;
    }

    /**
     * Send sms xml.
     *
     * @param array $sms_datas sms_datas.
     * @param array $senderid  senderid.
     * @param array $route     route.
     *
     * @return array
     */
    public static function sendSmsXml( $sms_datas, $senderid='', $route='' )
    {
        if (is_array($sms_datas) && sizeof($sms_datas) == 0 ) {
            return false;
        }

        $username = softeria_alerts_get_option('softeria_alerts_name', 'softeria_alerts_gateway');
        $password = softeria_alerts_get_option('softeria_alerts_password', 'softeria_alerts_gateway');
        $senderid = !empty($senderid)?$senderid:softeria_alerts_get_option('softeria_alerts_api', 'softeria_alerts_gateway');
        $xmlstr = <<<XML
        <?xml version='1.0' encoding='UTF-8'?>
        <message>
        </message>
        XML;
        $msg    = new SimpleXMLElement($xmlstr);
        $user   = $msg->addChild('user');
        $user->addAttribute('username', $username);
        $user->addAttribute('password', $password);
        if ($route!='') {
            $user->addAttribute('route', $route);
        }
        $enable_short_url = softeria_alerts_get_option('enable_short_url', 'softeria_alerts_general');
        if ($enable_short_url === 'on' ) {
            $user->addAttribute('shortenurl', 1);
        }

        $cnt = 0;
        foreach ( $sms_datas as $sms_data ) {
            $phone = self::checkPhoneNos($sms_data['number']);
            if ($phone !== false ) {
                $sms = $msg->addChild('sms');

                $datas = apply_filters('sa_before_send_sms', array( 'text' => $sms_data['sms_body'] ));

                if (! empty($datas['text']) ) {
                    $sms_data['sms_body'] = $datas['text'];
                }

                $sms->addAttribute('text', $sms_data['sms_body']);

                $address = $sms->addChild('address');
                $address->addAttribute('from', $senderid);
                $address->addAttribute('to', $phone);
                $cnt++;
            }
        }

        if ($msg->count() <= 1 ) {
            return false;
        }

        $xmldata = $msg->asXML();
        $url     = 'http://sms.softeriatech.com/api/xmlpush.json?';
        $fields  = array( 'data' => $xmldata );
        if ($cnt > 0 ) {
            $response = self::callAPI($url, $fields, null);
        } else {
            $response = json_encode(
                array(
                'status'      => 'error',
                'description' => 'Invalid WC Users Contact Numbers',
                )
            );
        }

        return $response;
    }

    /**
     * CallAPI function.
     *
     * @param string $url     url.
     * @param array  $params  params.
     * @param array  $headers headers.
     *
     * @return array
     */
    public static function callAPI( $url, $params, $headers = array( 'Content-Type: application/json' ) )
    {
        error_log("callAPI::".json_encode([
            'url'     => $url,
            'params'  => $params,
            'headers' => $headers
        ]));

        $extra_params = array(
        'plugin'  => 'woocommerce',
        'website' => ( ( ! empty($_SERVER['SERVER_NAME']) ) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : '' ),
        'version' =>SmsAlertConstants::SA_VERSION
        );
        $params       = ( ! is_null($params) ) ? array_merge($params, $extra_params) : $extra_params;
        $args         = array(
            'body'    => $params,
            'timeout' => 15,
        );
        $request = wp_remote_post($url, $args);
        
        error_log("callAPI::Response: ".json_encode($request));

        if (is_wp_error($request) ) {
            $data['status']      = 'error';
            $data['description'] = $request->get_error_message();

            $code = wp_remote_retrieve_response_code( $request );
            if( $code== 401){
                $template = 'you are using wrong credentials of Softeria Tech. Please check once.';
                self::sendemailForInvalidCred($template);
                softeria_alerts_Setting_Options::logout();
            }elseif( $code == 400 ) {
                $template = $data['description'];
                self::sendemailForDormant($template);
            }
            return $data;
        }

        $resp     = wp_remote_retrieve_body($request);

        if (empty($resp) ) {
            return [];
        }

        return json_decode($resp,true);
    }
}
