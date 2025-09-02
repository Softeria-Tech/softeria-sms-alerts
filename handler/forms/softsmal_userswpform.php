<?php
/**
 * This file handles wp forms via sms notification
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

if (! is_plugin_active('userswp/userswp.php') ) {
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
 * SOFTSMAL_UsersWpForm class.
 */
class SOFTSMAL_UsersWpForm extends FormInterface
{

    /**
     * Woocommerce default registration form key
     *
     * @var $form_session_var Woocommerce default registration form key
     */
    private $form_session_var = SOFTSMAL_FormSessionVars::USERSWP_FORM;
    /**
     * Woocommerce registration popup form key
     *
     * @var $form_session_var2 Woocommerce registration popup form key
     */
    private $form_session_var2 = SOFTSMAL_FormSessionVars::USERSWP_POPUP;

    /**
     * If OTP in popup is enabled or not
     *
     * @var $popup_enabled If OTP in popup is enabled or not
     */
    private $popup_enabled;

    /**
     * Handle OTP form
     *
     * @return void
     */
    public function handleForm()
    {
        $this->popup_enabled = ( 'on' === softeria_alerts_get_option('otp_in_popup', 'softeria_alerts_general', 'on') ) ? true : false;
        $buyer_signup_otp = softeria_alerts_get_option('buyer_signup_otp', 'softeria_alerts_general');
        if ('on' === $buyer_signup_otp ) {
            if (isset($_REQUEST['register']) ) {
                add_filter('uwp_validate_result', array( $this, 'uwpSiteRegistrationErrors' ), 10, 3);
            }
            add_action('uwp_template_fields', array( $this, 'addPhoneField' ), 10, 2);
        }
        add_action('uwp_template_after', array( $this, 'smsproDisplayLoginWithOtp' ), 10, 1);    
    }

    /**
     * This function shows registration error message.
     *
     * @param array  $errors    Errors array.
     * @param string $form_type Form type.
     * @param string $data      Data.
     *
     * @throws Exception Validation errors.
     *
     * @return void
     */
    public function uwpSiteRegistrationErrors( $errors, $form_type, $data )
    {
        SOFTSMAL_Utility::checkSession();
        $user_phone = ( ! empty($_POST['billing_phone']) ) ? sanitize_text_field(wp_unslash($_POST['billing_phone'])) : '';
        if (! SOFTSMAL_cURLOTP::validateCountryCode($user_phone)) {        
            return $errors;
        }
        if (isset($_SESSION['sa_mobile_verified']) ) {
            unset($_SESSION['sa_mobile_verified']);
            return $errors;
        }
        $verify = check_ajax_referer('uwp-register-nonce', 'uwp_register_nonce', false);
        if (!$verify) {
            return new WP_Error('registration-error-invalid-nonce', __('Sorry, nonce did not verify.', 'soft-sms-alerts'));
        }
        if (is_wp_error($errors) ) {
            return $errors;
        }
        $username = ! empty($_REQUEST['username']) ? sanitize_text_field(wp_unslash($_REQUEST['username'])) : '';
        $email    = ! empty($_REQUEST['email']) ? sanitize_text_field(wp_unslash($_REQUEST['email'])) : '';
        $password = ! empty($_REQUEST['password']) ? sanitize_text_field(wp_unslash($_REQUEST['password'])) : '';
        if (isset($_REQUEST['option']) && 'softeria_alerts_register_with_otp' === sanitize_text_field(wp_unslash($_REQUEST['option'])) ) {
            SOFTSMAL_Utility::initialize_transaction($this->form_session_var2);
        } else {
            SOFTSMAL_Utility::initialize_transaction($this->form_session_var);
        }       

        if ('on' !== softeria_alerts_get_option('allow_multiple_user', 'softeria_alerts_general') && ! SOFTSMAL_Utility::isBlank($user_phone) ) {

            $getusers = SOFTSMAL_Utility::getUsersByPhone('billing_phone', $user_phone);
            if (count($getusers) > 0 ) {
                return new WP_Error('registration-error-number-exists', __('An account is already registered with this mobile number. Please login.', 'soft-sms-alerts'));
            }
        }

        if (isset($user_phone) && SOFTSMAL_Utility::isBlank($user_phone) ) {
            return new WP_Error('registration-error-invalid-phone', __('Please enter phone number.', 'soft-sms-alerts'));
        }

        return $this->processFormFields($username, $email, $errors, $password);
    }

    /**
     * This function processed form fields.
     *
     * @param string $username User name.
     * @param string $email    Email Id.
     * @param array  $errors   Errors array.
     * @param string $password Password.
     *
     * @return void
     */
    public function processFormFields( $username, $email, $errors, $password )
    {
        global $phoneLogic;
        $phone_no  = ( ! empty($_POST['billing_phone']) ) ? sanitize_text_field(wp_unslash($_POST['billing_phone'])) : '';
        $phone_num = preg_replace('/[^0-9]/', '', $phone_no);

        if (! isset($phone_num) || ! SOFTSMAL_Utility::validatePhoneNumber($phone_num) ) {
            return new WP_Error('billing_phone_error', str_replace('##phone##', $phone_num, $phoneLogic->_get_otp_invalid_format_message()));
        }
        softeria_alerts_site_challenge_otp($username, $email, $errors, $phone_num, 'phone', $password);
    }

    /**
     * Display form phone field after form
     *
     * @param array $form_type form type.
     * @param array $args      form args.
     *
     * @return void
     */
    public function addPhoneField( $form_type, $args = array() )
    {
        if ('register' === $form_type ) {
            $id = 'billing_phone';
            if (wp_doing_ajax() ) {
                $id .= '_ajax';
            }
            echo aui()->input(
                array(
                'type'        => 'text',
                'id'          => esc_attr($id),
                'class'       => 'phone-valid sa-phone-field',
                'name'        => 'billing_phone',
                'value'       => '',
                'placeholder' => 'Phone *',
                'label'       => esc_html__('Phone', 'userswp'),
                )
            );
            echo '<input type="hidden" name="register" value="Register">';
        } 
    }

    /**
     * Display login with otp button
     *
     * @param array $form_type form type.
     *
     * @return void
     */
    public function smsproDisplayLoginWithOtp( $form_type )
    {
        $enabled_login_with_otp = softeria_alerts_get_option('login_with_otp', 'softeria_alerts_general');
        $enabled_login_popup    = softeria_alerts_get_option('otp_in_popup', 'softeria_alerts_general', 'on');
        $default_login_otp      = softeria_alerts_get_option('buyer_login_otp', 'softeria_alerts_general');
        $enabled_country          = softeria_alerts_get_option('checkout_show_country_code', 'softeria_alerts_general');
        $default_login_form = softeria_alerts_get_option('hide_default_login_form', 'softeria_alerts_general');
        $inline_script ='document.addEventListener("DOMContentLoaded", function() {';
        if ($this->popup_enabled && 'register' === $form_type ) {
            echo do_shortcode('[sa_verify phone_selector="#billing_phone" submit_selector= ".uwp_register_submit"]');
            $uniqueNo = rand();
            $this->addSmsalertModal();
            $inline_script .= 'if (jQuery(".uwp-auth-modal").hasClass("show") || jQuery(".uwp-auth-modal").hasClass("in")){
            add_softeria_alerts_button(".uwp_register_submit","#billing_phone","'.$uniqueNo.'");
            jQuery(document).on("click", "#sa_verify_'.$uniqueNo.'",function(event){
            event.preventDefault();
            send_otp(this,".uwp_register_submit","#billing_phone","","");
            });
            jQuery(document).on("keypress", "input", function(e){
				var pform     = jQuery(this).parents("form");
                if (e.which === 13 && pform.find("#sa_verify_'.$uniqueNo.'").length > 0)
                {
                    e.preventDefault();
                    pform.find("#sa_verify_'.$uniqueNo.'").trigger("click");
                }
            });            
            }';
        } elseif ('on' === $enabled_login_with_otp && 'login' === $form_type ) {
            $this->addLoginWithOtpPopup();
            $uniqueNo = rand();
            $inline_script .= 'if (!jQuery(".uwp-login-class .lwo-container button").hasClass("sa_myaccount_btn"))
            {
                jQuery(".uwp-login-form").addClass("login");    
                jQuery("<div class=\"lwo-container\"><div class=\"sa_or\">OR</div><button type=\"button\" class=\"button sa_myaccount_btn\" name=\"sa_myaccount_btn_login\" value=\"Login with OTP\" style=\"width: 100%;\">Login with OTP</button></div>").insertAfter(".uwp-login-class .uwp_login_submit");
            }
            if (!jQuery(".uwp-auth-modal .lwo-container button").hasClass("sa_myaccount_btn")){
                jQuery(".uwp-login-form").addClass("login");    
                jQuery("<div class=\"lwo-container\"><div class=\"sa_or\">OR</div><button type=\"button\" class=\"button sa_myaccount_btn\" name=\"sa_myaccount_btn_login\" value=\"Login with OTP\" style=\"width: 100%;\">Login with OTP</button></div>").insertAfter(".uwp-auth-modal .uwp_login_submit");
                if (jQuery(".uwp-auth-modal").hasClass("show") || jQuery(".uwp-auth-modal").hasClass("in")){
                add_softeria_alerts_button(".softeria_alerts_login_with_otp_btn",".sa_mobileno","'.$uniqueNo.'");
                jQuery(document).on("click", "#sa_verify_'.$uniqueNo.'",function(event){
                        event.preventDefault();
                send_otp(this,".softeria_alerts_login_with_otp_btn",".sa_mobileno","","");
                });    
                jQuery(document).on("keypress", "input", function(e){
					var pform     = jQuery(this).parents("form");
					if (e.which === 13 && pform.find("#sa_verify_'.$uniqueNo.'").length > 0)
					{
						e.preventDefault();
						pform.find("#sa_verify_'.$uniqueNo.'").trigger("click");
					}
                });                    
                }                
            }
            jQuery(".sa-lwo-form input[name=redirect]").val(jQuery(".uwp-login-form input[name=redirect_to]").val());';
            if ('on' === $default_login_form ) {
                $inline_script .= 'jQuery(".sa_myaccount_btn").trigger("click");
			    jQuery(".sa_default_login_form").hide();';
            }
        }
        
        if ('on' === $enabled_country && ( 'register' === $form_type || 'login' === $form_type )) { 
            $inline_script .= 'jQuery(".phone-valid").on("countrychange", function () {
					var default_cc = jQuery(this).intlTelInput("getSelectedCountryData");
					var fullnumber =  jQuery(this).intlTelInput("getNumber");
					var field_name = jQuery(this).attr("name");
					jQuery(this).parents("form").find("[name="+field_name+"]:hidden").val(fullnumber);
		    });
		    if( typeof sa_otp_settings !=  "undefined" && sa_otp_settings["show_countrycode"] == "on" )
			{
				initialiseCountrySelector(".modal .phone-valid");
			}';
        }
        if ('login' === $form_type && 'on' === $default_login_otp && 'on' === $enabled_login_popup && 'on' !== $default_login_form) {
            $uniqueNo = rand();
            $inline_script .= 'if (jQuery(".uwp-auth-modal").hasClass("show") || jQuery(".uwp-auth-modal").hasClass("in")){
            add_softeria_alerts_button(".uwp_login_submit","","'.$uniqueNo.'");
            jQuery(document).on("click", "#sa_verify_'.$uniqueNo.'",function(event){
                    event.preventDefault();
            send_otp(this,".uwp_login_submit","","#username","#password");
            });    
            jQuery(document).on("keypress", "input", function(e){
                var pform     = jQuery(this).parents("form");
                if (e.which === 13 && pform.find("#sa_verify_'.$uniqueNo.'").length > 0)
                {
                    e.preventDefault();
                    pform.find("#sa_verify_'.$uniqueNo.'").trigger("click");
                }
            });                     
            }';
        }
        $inline_script .= '});';
        if (! wp_script_is('sainlinescript-handle-footer', 'enqueued') ) {
            wp_register_script('sainlinescript-handle-footer', '', [], '', true);
            wp_enqueue_script('sainlinescript-handle-footer');
        }        
        wp_add_inline_script("sainlinescript-handle-footer", $inline_script);        
    }

    /**
     * Add login with otp form code in login form page.
     *
     * @return void
     */
    public function addLoginWithOtpPopup()
    {
        $enabled_login_popup    = softeria_alerts_get_option('otp_in_popup', 'softeria_alerts_general', 'on');
        $enabled_login_with_otp = softeria_alerts_get_option('login_with_otp', 'softeria_alerts_general');
        $default_login_otp      = softeria_alerts_get_option('buyer_login_otp', 'softeria_alerts_general');
        if ('on' === $enabled_login_popup && 'on' === $default_login_otp) {
            echo do_shortcode('[sa_verify user_selector="#username" pwd_selector="#password" submit_selector=".uwp_login_submit"]');
        }

        if ('on' === $enabled_login_with_otp ) {
            WPLogin::addLoginwithotpShortcode(); 
        }
        $this->addSmsalertModal();
    }
    
    /**
     * Add softsmsalerts modal.
     *
     * @return string
     */
    public static function addSmsalertModal()
    {
        $otp_modal = '
		document.addEventListener("DOMContentLoaded", function() {
        jQuery(".uwp-auth-modal").removeAttr("tabindex");
            if (jQuery(".modal.smsproModal").length==0 && jQuery(".uwp-auth-modal").hasClass("show"))    
            {            
            var popup = \''.str_replace(array("\n","\r","\r\n"), "", (get_softeria_alerts_template("template/otp-popup.php", array(), true))).'\';
            jQuery("body").append(popup);
            } 
		});			
        ';        
        if (! wp_script_is('sainlinescript-handle-footer', 'enqueued') ) {
               wp_register_script('sainlinescript-handle-footer', '', [], '', true);
               wp_enqueue_script('sainlinescript-handle-footer');
        }        
        wp_add_inline_script("sainlinescript-handle-footer", $otp_modal);    
    }

    /**
     * Check your otp setting is enabled or not.
     *
     * @return bool
     */
    public static function isFormEnabled()
    {
        $user_authorize = new softeria_alerts_Setting_Options();
        $islogged       = $user_authorize->is_user_authorised();
        return ( is_plugin_active('userswp/userswp.php') && $islogged && ( softeria_alerts_get_option('buyer_login_otp', 'softeria_alerts_general') === 'on' || softeria_alerts_get_option('login_with_otp', 'softeria_alerts_general') === 'on' ) ) ? true : false;
    }

    /**
     * Handle after failed verification
     *
     * @param object $user_login   users object.
     * @param string $user_email   user email.
     * @param string $phone_number phone number.
     *
     * @return void
     */
    public function handle_failed_verification( $user_login, $user_email, $phone_number )
    {
        SOFTSMAL_Utility::checkSession();
        if (! isset($_SESSION[ $this->form_session_var ]) && ! isset($_SESSION[ $this->form_session_var2 ]) ) {
            return;
        }
        if (isset($_SESSION[ $this->form_session_var ]) ) {
            softeria_alerts_site_otp_validation_form($user_login, $user_email, $phone_number, SOFTSMAL_Utility::_get_invalid_otp_method(), 'phone', false);
        }
        if (isset($_SESSION[ $this->form_session_var2 ]) ) {
            wp_send_json(SOFTSMAL_Utility::_create_json_response(SOFTSMAL_Messages::showMessage('INVALID_OTP'), 'error'));
        }
    }

    /**
     * Handle after post verification
     *
     * @param string $redirect_to  redirect url.
     * @param object $user_login   user object.
     * @param string $user_email   user email.
     * @param string $password     user password.
     * @param string $phone_number phone number.
     * @param string $extra_data   extra hidden fields.
     *
     * @return void
     */
    public function handle_post_verification( $redirect_to, $user_login, $user_email, $password, $phone_number, $extra_data )
    {
        SOFTSMAL_Utility::checkSession();
        if (! isset($_SESSION[ $this->form_session_var ]) && ! isset($_SESSION[ $this->form_session_var2 ]) ) {
            return;
        }
        $_SESSION['sa_mobile_verified'] = true;
        $_SESSION['sa_mobile_userswp']  = $phone_number;
        if (isset($_SESSION[ $this->form_session_var2 ]) ) {
            wp_send_json(SOFTSMAL_Utility::_create_json_response(SOFTSMAL_Messages::showMessage('VALID_OTP'), 'success'));
        }
    }

    /**
     * Clear otp session variable
     *
     * @return void
     */
    public function unsetOTPSessionVariables()
    {
        unset($_SESSION[ $this->tx_session_id ]);
        unset($_SESSION[ $this->form_session_var ]);
        unset($_SESSION[ $this->form_session_var2 ]);
    }

    /**
     * Check current form submission is ajax or not
     *
     * @param bool $is_ajax bool value for form type.
     *
     * @return bool
     */
    public function is_ajax_form_in_play( $is_ajax )
    {
        SOFTSMAL_Utility::checkSession();
        return isset($_SESSION[ $this->form_session_var2 ]) ? true : $is_ajax;
    }

    /**
     * Replace variables for sms contennt
     *
     * @param string $content   sms content to be sent.
     * @param array  $formdatas values of varibles.
     *
     * @return string
     */
    public static function parse_sms_content( $content = null, $formdatas = array() )
    {
        $datas = array();
        foreach ( $formdatas as $key => $data ) {
            if (is_array($data) ) {
                foreach ( $data as $k => $v ) {
                    $datas[ '[' . $k . ']' ] = $v;
                }
            } else {
                $datas[ '[' . $key . ']' ] = $data;
            }
        }
        $find    = array_keys($datas);
        $replace = array_values($datas);
        $content = str_replace($find, $replace, $content);
        return $content;
    }

    /**
     * Handle form for WordPress backend
     *
     * @return void
     */
    public function handleFormOptions()
    {

    }
}
new SOFTSMAL_UsersWpForm();