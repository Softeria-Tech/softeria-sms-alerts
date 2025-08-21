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

if (! is_plugin_active('wpforms-lite/wpforms.php') && ! is_plugin_active('wpforms/wpforms.php') ) {
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
 * WpForm class.
 */
class WpForm extends FormInterface
{

    /**
     * Form Session Variable.
     *
     * @return void
     */
    private $form_session_var = FormSessionVars::WPFORM;

    /**
     * Handle OTP form
     *
     * @return void
     */
    public function handleForm()
    {
        add_action('wpforms_process_complete', array( $this, 'wpfDevProcessComplete' ), 10, 4);
        add_filter('wpforms_field_properties', array( $this, 'wpfAddPhoneClass' ), 10, 3);
        add_filter('wpforms_display_field_after', array( $this, 'wpfDevProcessFilter' ), 10, 2);
        add_filter('wpforms_save_form_args', array( $this, 'smsproWpformShowWarnings' ), 10, 3); 
        add_action('wpforms_process', array( $this, 'validateFields' ), 20, 3);     
        add_filter('wpforms_process_bypass_captcha', array( $this, 'beforeValidateFields' ), 10, 3);
        add_filter('sa_get_user_phone_no', array( $this, 'saUpdateBillingPhone' ), 10, 2);    
        $user_authorize = new softeria_alerts_Setting_Options();
        if ($user_authorize->is_user_authorised() ) {
            add_action('wpforms_form_settings_panel_content', array( $this, 'customWpformsFormSettingsPanelContent' ), 10, 1);
            add_filter('wpforms_builder_settings_sections', array( $this, 'customWpformsBuilderSettingsSections' ), 10, 2);
        }    
    }
    
    /**
     * This function by Pass Fields.
     *
     * @param $fals      Form fals
     * @param $entry     entry
     * @param $form_data form_data
     *
     * @return void.
     */
    public function beforeValidateFields( $fals, $entry, $form_data)
    {
        SmsAlertUtility::checkSession();         
        if (isset($_SESSION['sa_mobile_verified'])  ) {
            unset($_SESSION['sa_mobile_verified']);           
            return $entry;
        }
         
    }
    /**
     * Update phone field
     *
     * @param string $billing_phone billing phone
     * @param int    $user_id       user id
     *
     * @return void
     */
    public function saUpdateBillingPhone($billing_phone, $user_id)
    {        
        $form_id = !empty($_POST['wpforms']['id'])?$_POST['wpforms']['id']:'';
        if ($form_id != '') {
            $form_data = wpforms()->get('form')->get($form_id, ['content_only' => true,]);
            if (!empty($form_data['settings']['smspro']['visitor_phone'])) {
                $phone_field     = $form_data['settings']['smspro']['visitor_phone'];
                $phone_field_id  = preg_replace('/[^0-9]/', '', $phone_field);
                $phone = '';
                if (! empty($phone_field_id) ) {
                    $datas = array();            
                    foreach ($_POST['wpforms']['complete'] as $key => $field ) {                    
                        $datas[ '{field_id="' . $key . '"}' ] = $field['value'];                 
                        if ($phone_field_id == $key ) {
                               $phone = $field['value'];                        
                        } 
                    }
                }
                if (!empty($phone)) {
                    return $phone;
                }
            }
        }
        return $billing_phone;
    }
    
    /**
     * This function shows validation error message.
     *
     * @param $fields    Form fields
     * @param $entry     entry
     * @param $form_data form_data
     *
     * @return void.
     */
    public function validateFields($fields, $entry, $form_data)
    {
        if (isset($_REQUEST['option']) && 'softeria_alerts_wpforms_otp' === sanitize_text_field(wp_unslash($_REQUEST['option']))) {
            SmsAlertUtility::initialize_transaction($this->form_session_var);
        } else {
            return;
        }        
        $phone_field     = !empty($form_data['settings']['smspro']['visitor_phone'])?$form_data['settings']['smspro']['visitor_phone']:'';        
        $phone_field_id  = preg_replace('/[^0-9]/', '', $phone_field);
        $phone = '';
        if (! empty($phone_field_id) ) {
            $datas = array();
            foreach ( $fields as $key => $field ) {
                $datas[ '{field_id="' . $key . '"}' ] = $field['value'];
                if ($phone_field_id == $key ) {
                    $phone = $field['value'];   
                }
            }
        }
        if (isset($phone) && SmsAlertUtility::isBlank($phone)) {            
            wp_send_json(SmsAlertUtility::_create_json_response(__('Please enter phone number.', 'softeria-sms-alerts'), SmsAlertConstants::ERROR_JSON_TYPE));
            exit();
        }

        return $this->processFormFields($phone);
            
    }
    
    /**
     * This function processed form fields.
     *
     * @param string $phone User phone.
     *
     * @return bool
     */
    public function processFormFields( $phone )
    {
        global $phoneLogic;
        $phone_num = preg_replace('/[^0-9]/', '', $phone);

        if (! isset($phone_num) || ! SmsAlertUtility::validatePhoneNumber($phone_num) ) {
            wp_send_json(SmsAlertUtility::_create_json_response(str_replace('##phone##', $phone_num, $phoneLogic->_get_otp_invalid_format_message()), SmsAlertConstants::ERROR_JSON_TYPE));
            exit();
        }
        
        softeria_alerts_site_challenge_otp('test', null, null, $phone_num, 'phone', null, null, 'ajax');
    }
    

    /**
     * Show warning if phone field not selected.
     *
     * @param array $form form_data.
     * @param array $data data.
     * @param array $args args.
     *
     * @return void
     */    
    public function smsproWpformShowWarnings($form, $data, $args)
    {
        $is_msg_enabled   = !empty($data['settings']['smspro']['message_enable'])?$data['settings']['smspro']['message_enable']:'';
        $is_otp_enable    = !empty($data['settings']['smspro']['otp_enable'])?$data['settings']['smspro']['otp_enable']:''; 
        $is_visitor_phone = !empty($data['settings']['smspro']['visitor_phone'])?$data['settings']['smspro']['visitor_phone']:'';
        
        if ((!empty($is_msg_enabled) || !empty($is_otp_enable)) && empty($is_visitor_phone)) {
            wp_send_json_error(esc_html__('Please choose Softeria Tech phone field in Softeria Tech tab.', 'softeria-sms-alerts'));
        }
        return $form;
    } 
    
     
    /**
     * Wpf dev process filter.
     *
     * @param array $field     field.
     * @param array $form_data form_data.
     *
     * @return void
     */      
    public function wpfDevProcessFilter( $field, $form_data )
    {
        $unique_class    = 'sa-class-'.mt_rand(1, 100);
        $user_authorize  = new softeria_alerts_Setting_Options();
        $islogged        = $user_authorize->is_user_authorised();
        $phone_field     = !empty($form_data['settings']['smspro']['visitor_phone'])?$form_data['settings']['smspro']['visitor_phone']:'';
        $phone_field_id  = preg_replace('/[^0-9]/', '', $phone_field);
        $enabled_country = softeria_alerts_get_option('checkout_show_country_code', 'softeria_alerts_general', '');
        $inline_script = 'document.addEventListener("DOMContentLoaded", function() {';
        if (isset($form_data['settings']['smspro']['otp_enable']) && $islogged && ($field['id'] === $phone_field_id) ) {
            
            $otp_enable = $form_data['settings']['smspro']['otp_enable'];
            
            if ($otp_enable ) {
                $inline_script .= 'jQuery("form#wpforms-form-' . esc_attr($form_data['id']) . '").each(function () 
				{
				  	if(!jQuery(this).hasClass("sa-wp-form"))
					{
					jQuery(this).addClass("'.$unique_class.' sa-wp-form");
					}		
				});';
                echo do_shortcode('[sa_verify id="" phone_selector=".softeria-alert-phone #wpforms-' . esc_attr($form_data['id']) . '-field_' . esc_attr($phone_field_id) . '" submit_selector= ".'.$unique_class.' .wpforms-submit" ]');
            }
        }
        
        if (isset($form_data['settings']['smspro']) && 'on' === $enabled_country && !array_key_exists('otp_enable', (array)$form_data['settings']['smspro']) ) {
            $inline_script .= 'jQuery(document).ready(function(){
				initialiseCountrySelector(".softeria-alert-phone #wpforms-' . esc_attr($form_data['id']) . '-field_' . esc_attr($phone_field_id) . '");
			});';            
        }
        $inline_script .= '});';
        if (! wp_script_is('sainlinescript-handle-footer', 'enqueued') ) {
               wp_register_script('sainlinescript-handle-footer', '', [], '', true);
               wp_enqueue_script('sainlinescript-handle-footer');
        }        
        wp_add_inline_script("sainlinescript-handle-footer", $inline_script);
    }

    /**
     * Add Tab smspro setting in wpform builder section
     *
     * @param array $sections  form section.
     * @param array $form_data form datas.
     *
     * @return array
     */
    public function customWpformsBuilderSettingsSections( $sections, $form_data )
    {
        $sections['smspro'] = 'Softeria Tech';
        return $sections;
    }

    /**
     * Add Tab panel smspro setting in wpform builder section
     *
     * @param object $instance tab panel object.
     *
     * @return void
     */
    public function customWpformsFormSettingsPanelContent( $instance )
    {
        $form_data = $instance->form_data;
        echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-smspro">';

        echo '<div class="wpforms-panel-content-section-title"><span id="wpforms-builder-settings-notifications-title">Softeria Tech Message Configuration</span>
		</div>';
        $plugin_file = is_plugin_active('wpforms-lite/wpforms.php')?'/wpforms-lite/wpforms.php':'/wpforms/wpforms.php';
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR.$plugin_file);
        $checkbox = (!empty($plugin_data['Version']) && $plugin_data['Version'] < '1.6.2.3') ? 'checkbox':'toggle';
        wpforms_panel_field(
            $checkbox,
            'smspro',
            'message_enable',
            $instance->form_data,
            esc_html__('Enable Message', 'softeria-sms-alerts'),
            array( 'parent' => 'settings' )
        );
        wpforms_panel_field(
            $checkbox,
            'smspro',
            'otp_enable',
            $instance->form_data,
            esc_html__('Enable Mobile Verification', 'softeria-sms-alerts'),
            array( 'parent' => 'settings' )
        );
        wpforms_panel_field(
            'text',
            'smspro',
            'admin_number',
            $instance->form_data,
            __('Send Admin SMS To', 'softeria-sms-alerts'),
            array(
            'default' => '',
            'parent'  => 'settings',
            'after'   => '<p class="note">' .
                                __('Admin sms notifications will be sent to this number.', 'softeria-sms-alerts') . '</p>',
            )
        );
        wpforms_panel_field(
            'textarea',
            'smspro',
            'admin_message',
            $instance->form_data,
            __('Admin Message', 'softeria-sms-alerts'),
            array(
            'rows'      => 6,
            'default'   => SmsAlertMessages::showMessage('DEFAULT_CONTACT_FORM_ADMIN_MESSAGE'),
            'smarttags' => array(
            'type' => 'all',
            ),
            'parent'    => 'settings',
            'class'     => 'email-msg',

            )
        );
        wpforms_panel_field(
            'text',
            'smspro',
            'visitor_phone',
            $instance->form_data,
            __('Select Phone Field', 'softeria-sms-alerts'),
            array(
            'default'   => '',
            'smarttags' => array(
            'type' => 'all',
            ),
            'parent'    => 'settings',
            )
        );
        wpforms_panel_field(
            'textarea',
            'smspro',
            'visitor_message',
            $instance->form_data,
            __('Visitor Message', 'softeria-sms-alerts'),
            array(
            'rows'      => 6,
            'default'   => SmsAlertMessages::showMessage('DEFAULT_CONTACT_FORM_CUSTOMER_MESSAGE'),
            'smarttags' => array(
            'type' => 'all',
            ),
            'parent'    => 'settings',
            'class'     => 'email-msg',
            )
        );
        $admin_number = isset($form_data['settings']['smspro']['admin_number'])?$form_data['settings']['smspro']['admin_number']:'';    
        echo '</div>';
        echo "<script>
		var adminnumber = '" . $admin_number . "';
		var tagInput1 	= new TagsInput({
			selector: 'wpforms-panel-field-softeria-alert-admin_number',
			duplicate : false,
			max : 10,
		});
		var number = (adminnumber!='') ? adminnumber.split(',') : [];
		if(number.length > 0){
			tagInput1.addData(number);
		}	
		</script>";
    }

    /**
     * Process wp form submission and send sms
     *
     * @param array $properties properties.
     * @param array $field      field.
     * @param array $form_data  form data.
     *
     * @return void
     */
    public function wpfAddPhoneClass( $properties, $field, $form_data )
    {
        $phone_field    = !empty($form_data['settings']['smspro']['visitor_phone'])?$form_data['settings']['smspro']['visitor_phone']:'';
        $phone_field_id = preg_replace('/[^0-9]/', '', $phone_field);
        if ($field['id'] === $phone_field_id) {
            $properties['container']['class'][] = 'softeria-alert-phone';
        }
        return $properties;
    }

    /**
     * Process wp form submission and send sms
     *
     * @param array $fields    form fields.
     * @param array $entry     form entries.
     * @param array $form_data form data.
     * @param int   $entry_id  entity id.
     *
     * @return void
     */
    public function wpfDevProcessComplete( $fields, $entry, $form_data, $entry_id )
    {
        
        $user_authorize = new softeria_alerts_Setting_Options();
        $islogged       = $user_authorize->is_user_authorised();
        $msg_enable     = !empty($form_data['settings']['smspro']['message_enable'])?$form_data['settings']['smspro']['message_enable']:'';
        if ($msg_enable && $islogged ) {
            $phone_field     = $form_data['settings']['smspro']['visitor_phone'];
            $admin_number    = $form_data['settings']['smspro']['admin_number'];
            $visitor_message = $form_data['settings']['smspro']['visitor_message'];
            $admin_message   = $form_data['settings']['smspro']['admin_message'];
            $phone_field_id  = preg_replace('/[^0-9]/', '', $phone_field);
            if (! empty($phone_field_id) ) {
                $phone = '';
                $datas = array();
                foreach ( $fields as $key => $field ) {
                    $datas[ '{field_id="' . $key . '"}' ] = $field['value'];
                    //Please do not use === triple equal to here(Key does not match after use).
                    if ($phone_field_id == $key ) {
                        $phone = $field['value'];
                    }
                }
                do_action('sa_send_sms', $phone, self::parseSmsContent($visitor_message, $datas));
                if (! empty($admin_number) ) {
                    do_action('sa_send_sms', $admin_number, self::parseSmsContent($admin_message, $datas));
                }
            }
        }
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
        return ( $islogged && (is_plugin_active('wpforms-lite/wpforms.php') || is_plugin_active('wpforms/wpforms.php') )) ? true : false;
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
        SmsAlertUtility::checkSession();
        if (! isset($_SESSION[ $this->form_session_var ]) ) {
            return;
        }
        if (! empty($_REQUEST['option']) && sanitize_text_field(wp_unslash($_REQUEST['option'])) === 'softeria-alert-validate-otp-form' ) {
            wp_send_json(SmsAlertUtility::_create_json_response(SmsAlertMessages::showMessage('INVALID_OTP'), 'error'));
            exit();
        } else {
            $_SESSION[ $this->form_session_var ] = 'verification_failed';
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
        SmsAlertUtility::checkSession();
        if (! isset($_SESSION[ $this->form_session_var ]) ) {
            return;
        }
        $_SESSION['sa_mobile_verified'] = true;
        if (! empty($_REQUEST['option']) && sanitize_text_field(wp_unslash($_REQUEST['option'])) === 'softeria-alert-validate-otp-form' ) {
            wp_send_json(SmsAlertUtility::_create_json_response(SmsAlertMessages::showMessage('VALID_OTP'), 'success'));
            exit();
        } else {
            $_SESSION[ $this->form_session_var ] = 'validated';
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
        SmsAlertUtility::checkSession();
        return isset($_SESSION[ $this->form_session_var ]) ? true : $is_ajax;
    }

    /**
     * Replace variables for sms contennt
     *
     * @param string $content sms content to be sent.
     * @param array  $datas   values of varibles.
     *
     * @return string
     */
    public static function parseSmsContent( $content = null, $datas = array() )
    {
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
new WpForm();