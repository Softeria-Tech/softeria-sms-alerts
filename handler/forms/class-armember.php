<?php
/**
 * Armember helper.
 *
 * PHP version 5
 *
 * @category Handler
 * @package  SMSPro
 * @author   SMS Pro <support@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 */

namespace SMS_ALERT;
use FormInterface;
use smspro_Setting_Options;
use FormSessionVars;
use SmsAlertUtility;
use SmsAlertMessages;

if (defined('ABSPATH') === false) {
    exit;
}

if (is_plugin_active('armember-membership/armember-membership.php') === false) {
    return;
}

/**
 * PHP version 5
 *
 * @category Handler
 * @package  SMSPro
 * @author   SMS Pro <support@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 * 
 * Armember class 
 */
class Armember extends FormInterface
{
    /**
     * Form Session Variable.
     *
     * @var stirng
     */
    private $form_session_var = FormSessionVars::AR_MEMBER_FORM;
    
    /**
     * 
     * Construct function.
     *
     * @return void
     */
    public function handleForm()
    {   
        add_action('arm_cancel_subscription_gateway_action', array( $this, 'sendSmsOnStatusCancelSubscription'), 10, 2);
        add_action('arm_after_user_plan_change',              array($this, 'sendSmsOnStatusAfterUserPlanChange'), 10, 2);
        add_action('arm_after_user_plan_change_by_admin',     array($this, 'sendSmsOnStatusAfterUserPlanChange'), 10, 2);
        add_action('arm_after_user_plan_renew',               array( $this, 'sendSmsOnStatusAfterUserPlanRenew'), 10, 2);
        add_action('arm_after_user_plan_renew_by_admin',      array( $this, 'sendSmsOnStatusAfterUserPlanRenew'), 10, 2);
        add_filter('arm_change_content_before_field', array( $this, 'addPhoneField'), 10, 2);
        add_filter('arm_change_content_after_field', array( $this, 'addLoginOtp'), 10, 2);
    }
    
    /**
     * Add Shortcode for login OTP
     *
     * @param string $field_content field_content.
     * @param object $form          form.
     *
     * @return string 
     * */
    public function addLoginOtp($field_content, $form)
    {
        $default_login_otp = smspro_get_option('buyer_login_otp', 'smspro_general');
        if ($form->type=='login' && 'on' ===$default_login_otp) {                
            $field_content.= do_shortcode('[sa_verify user_selector="user_login" pwd_selector="user_pass" submit_selector=".arm_form_field_submit_button"]');    
        }
        return $field_content;
    }
    
    /**
     * Add Shortcode for signup OTP
     *
     * @param string $content content.
     * @param object $form    form.
     *
     * @return string
     * */
    public function addPhoneField($content, $form)
    {
        if ($form->type == 'registration') {
            $form->fields[]['arm_form_field_option'] = Array
                (
                    'id' => 'billing_phone',
                    'label' => 'Phone',
                    'placeholder' =>'', 
                    'type' => 'text',
                    'meta_key' => 'billing_phone',
                    'required' => 1,
                    'blank_message' => 'Phone can not be left blank.'
                );
            $buyer_signup_otp = smspro_get_option('buyer_signup_otp', 'smspro_general');
            if ('on' === $buyer_signup_otp ) {                
                $content.=do_shortcode('[sa_verify phone_selector="billing_phone" submit_selector= ".arm_form_field_submit_button"]');    
            }            
        }
        return $content;
    }
   
    /**
     * Add default settings to savesetting in setting-options.
     *
     * @param array $defaults defaults.
     *
     * @return array
     */
    public static function add_default_setting($defaults = array())
    {
        $bookingStatuses = array('cancelled', 'changed', 'renewed');

        foreach ($bookingStatuses as $ks => $vs) {
            $defaults['smspro_arm_general']['customer_arm_notify_' . $vs] = 'off';
            $defaults['smspro_arm_message']['customer_sms_arm_body_' . $vs] = '';
            $defaults['smspro_arm_general']['admin_arm_notify_' . $vs]    = 'off';
            $defaults['smspro_arm_message']['admin_sms_arm_body_' . $vs]  = '';
        }
        return $defaults;

    }//end add_default_setting()


    /**
     * Add tabs to smspro settings at backend.
     *
     * @param array $tabs tabs.
     *
     * @return array
     */
    public static function addTabs($tabs= array())
    {
        $customerParam = array(
            'checkTemplateFor' => 'arm_customer',
            'templates'        => self::getCustomerTemplates(),
        );

        $admin_param = array(
            'checkTemplateFor' => 'arm_admin',
            'templates'        => self::getAdminTemplates(),
        );

        $tabs['ar_member']['nav']  = 'AR Member';
        $tabs['ar_member']['icon'] = 'dashicons-groups';

        $tabs['ar_member']['inner_nav']['ar_member_cust']['title'] = 'Customer Notifications';
        $tabs['ar_member']['inner_nav']['ar_member_cust']['tab_section']  = 'armembercusttemplates';
        $tabs['ar_member']['inner_nav']['ar_member_cust']['first_active'] = true;
        $tabs['ar_member']['inner_nav']['ar_member_cust']['tabContent']= $customerParam;
        $tabs['ar_member']['inner_nav']['ar_member_cust']['filePath']     = 'views/message-template.php';

        $tabs['ar_member']['inner_nav']['ar_member_admin']['title']       = 'Admin Notifications';
        $tabs['ar_member']['inner_nav']['ar_member_admin']['tab_section'] = 'armemberadmintemplates';
        $tabs['ar_member']['inner_nav']['ar_member_admin']['tabContent']  = $admin_param;
        $tabs['ar_member']['inner_nav']['ar_member_admin']['filePath']    = 'views/message-template.php';
        $tabs['ar_member']['help_links'] = [
            /* 'youtube_link' => [
                'href'   => 'https://youtu.be/4BXd_XZt9zM',
                'target' => '_blank',
                'alt'    => 'Watch steps on Youtube',
                'class'  => 'btn-outline',
                'label'  => 'Youtube',
                'icon'   => '<span class="dashicons dashicons-video-alt3" style="font-size: 21px;"></span> ',

            ], */
            'kb_link'      => [
                'href'   => 'https://sms.softeriatech.com/knowledgebase/armember-sms-integration/',
                'target' => '_blank',
                'alt'    => 'Read how to integrate with armember',
                'class'  => 'btn-outline',
                'label'  => 'Documentation',
                'icon'   => '<span class="dashicons dashicons-format-aside"></span>',
            ],
        ];
        return $tabs;
    }//end addTabs()

    /**
     * Get customer templates.
     *
     * @return array
     */
    public static function getCustomerTemplates()
    {
        $bookingStatuses = array(
            '[cancelled]'          => 'Cancelled',
            '[plan_changed]'     => 'Changed',
            '[renewed]'         => 'Renewed',
           
        );

        $templates = array();
        foreach ($bookingStatuses as $ks  => $vs) {
            $currentVal = smspro_get_option('customer_arm_notify_' . strtolower($vs), 'smspro_arm_general', 'on');

            $checkboxNameId = 'smspro_arm_general[customer_arm_notify_' . strtolower($vs) . ']';
            $textareaNameId = 'smspro_arm_message[customer_sms_arm_body_' . strtolower($vs) . ']';

            $defaultTemplate = smspro_get_option('admin_sms_arm_body_' . strtolower($vs), 'smspro_arm_message', sprintf(__('Hello %1$s, status of your plan %2$s with %3$s has been %4$s.%5$sPowered by%6$ssms.softeriatech.com', 'sms-pro'), '[member_name]', '[plan_name]', '[store_name]', $vs, PHP_EOL, PHP_EOL));

            $textBody = smspro_get_option('customer_sms_arm_body_' . strtolower($vs), 'smspro_arm_message', $defaultTemplate);

            $templates[$ks]['title']          = 'When Users subscription ' . ucwords($vs);
            $templates[$ks]['enabled']        = $currentVal;
            $templates[$ks]['status']         = $vs;
            $templates[$ks]['text-body']      = $textBody;
            $templates[$ks]['checkboxNameId'] = $checkboxNameId;
            $templates[$ks]['textareaNameId'] = $textareaNameId;
            $templates[$ks]['token']          = self::getArMembervariables();
        }
        return $templates;
    }//end getCustomerTemplates()

    /**
     * Get admin templates.
     *
     * @return array
     */
    public static function getAdminTemplates()
    {
        $bookingStatuses = array(
            '[cancelled]'          => 'Cancelled',
            '[plan_changed]'     => 'Changed',
            '[renewed]'         => 'Renewed',
           
        );

        $templates = array();
        foreach ($bookingStatuses as $ks  => $vs) {

            $currentVal     = smspro_get_option('admin_arm_notify_' . strtolower($vs), 'smspro_arm_general', 'on');
            $checkboxNameId = 'smspro_arm_general[admin_arm_notify_' . strtolower($vs) . ']';
            $textareaNameId = 'smspro_arm_message[admin_sms_arm_body_' . strtolower($vs) . ']';

            $defaultTemplate = smspro_get_option('admin_sms_arm_body_' . strtolower($vs), 'smspro_arm_message', sprintf(__('Hello admin, status of your plan %1$s with %2$s has been changed to %3$s. %4$sPowered by%5$ssms.softeriatech.com', 'sms-pro'), '[plan_name]', '[store_name]', $vs, PHP_EOL, PHP_EOL));


            $textBody = smspro_get_option('admin_sms_arm_body_' . strtolower($vs), 'smspro_arm_message', $defaultTemplate);

            $templates[$ks]['title']          = 'When admin Users subscription ' . ucwords($vs);
            $templates[$ks]['enabled']        = $currentVal;
            $templates[$ks]['status']         = $vs;
            $templates[$ks]['text-body']      = $textBody;
            $templates[$ks]['checkboxNameId'] = $checkboxNameId;
            $templates[$ks]['textareaNameId'] = $textareaNameId;
            $templates[$ks]['token']          = self::getArMembervariables();
        }
        return $templates;
    }

    
    /**
     * Send sms subscription renew.
     *
     * @param int    $user_id user_id    
     * @param string $plan_id plan_id
     *
     * @return void
     */
    public function sendSmsOnStatusAfterUserPlanRenew($user_id, $plan_id)
    {
        $user_phone   = get_user_meta($user_id, 'billing_phone', true);
        $planData     = get_user_meta($user_id, "arm_user_plan_{$plan_id}", true);
        $buyerSmsData = array();                  
        $customerMessage  = smspro_get_option('customer_sms_arm_body_renewed', 'smspro_arm_message', '');
        $customerRrNotify = smspro_get_option('customer_arm_notify_renewed', 'smspro_arm_general', 'on');
        if ($customerRrNotify === 'on' && $customerMessage !== '') {
            $buyerMessage = $this->parseSmsBody($planData, $user_id, $customerMessage);
            do_action('sa_send_sms', $user_phone, $buyerMessage);
        }

        // Send msg to admin.
        $adminPhoneNumber = smspro_get_option('sms_admin_phone', 'smspro_message', '');
        $nos              = explode(',', $adminPhoneNumber);
        $adminPhoneNumber = array_diff($nos, array('postauthor', 'post_author'));
        $adminPhoneNumber = implode(',', $adminPhoneNumber);

        if (empty($adminPhoneNumber) === false) {
            $adminRrNotify = smspro_get_option('admin_arm_notify_renewed', 'smspro_arm_general', 'on');
            $adminMessage   = smspro_get_option('admin_sms_arm_body_renewed', 'smspro_arm_message', '');
            if ('on' === $adminRrNotify && '' !== $adminMessage) {
                $adminMessage = $this->parseSmsBody($planData, $user_id, $adminMessage);
                do_action('sa_send_sms', $adminPhoneNumber, $adminMessage);
            }
        }
       
    }

    /**
     * Send sms cancel subscription.
     *
     * @param int $user_id user_id
     * @param int $plan_id plan_id
     *
     * @return void
     */
    public function sendSmsOnStatusCancelSubscription($user_id, $plan_id)
    {
        $user_phone   = get_user_meta($user_id, 'billing_phone', true);
        $planData     = get_user_meta($user_id, "arm_user_plan_{$plan_id}", true);
        $buyerSmsData = array();                  
        $customerMessage  = smspro_get_option('customer_sms_arm_body_cancelled', 'smspro_arm_message', '');
        $customerRrNotify = smspro_get_option('customer_arm_notify_cancelled', 'smspro_arm_general', 'on');
        if ($customerRrNotify === 'on' && $customerMessage !== '') {
            $buyerMessage = $this->parseSmsBody($planData, $user_id, $customerMessage);
            do_action('sa_send_sms', $user_phone, $buyerMessage);
        }

        // Send msg to admin.
        $adminPhoneNumber = smspro_get_option('sms_admin_phone', 'smspro_message', '');
        $nos              = explode(',', $adminPhoneNumber);
        $adminPhoneNumber = array_diff($nos, array('postauthor', 'post_author'));
        $adminPhoneNumber = implode(',', $adminPhoneNumber);

        if (empty($adminPhoneNumber) === false) {
            $adminRrNotify = smspro_get_option('admin_arm_notify_cancelled', 'smspro_arm_general', 'on');
            $adminMessage   = smspro_get_option('admin_sms_arm_body_cancelled', 'smspro_arm_message', '');
            if ('on' === $adminRrNotify && '' !== $adminMessage) {
                $adminMessage = $this->parseSmsBody($planData, $user_id, $adminMessage);
                do_action('sa_send_sms', $adminPhoneNumber, $adminMessage);
            }
        }
    }
    
    /**
     * Send sms plan change.
     *
     * @param int $user_id user_id
     * @param int $plan_id plan_id
     *
     * @return void
     */
    public function sendSmsOnStatusAfterUserPlanChange($user_id, $plan_id)
    {
        $user_phone    = get_user_meta($user_id, 'billing_phone', true);
        $planData = get_user_meta($user_id, "arm_user_plan_{$plan_id}", true);
        $buyerNumber   = $user_phone;
        $subscriptionstatus   = strtolower('changed');     
        $customerMessage = smspro_get_option('customer_sms_arm_body_' . $subscriptionstatus, 'smspro_arm_message', '');
        $customerNotify = smspro_get_option('customer_arm_notify_' . $subscriptionstatus, 'smspro_arm_general', 'on');
        if (($customerNotify === 'on' && $customerMessage !== '')) {
            $buyerMessage = $this->parseSmsBody($planData, $user_id, $customerMessage);
            do_action('sa_send_sms', $buyerNumber, $buyerMessage);
        }

        // Send msg to admin.
        $adminPhoneNumber = smspro_get_option('sms_admin_phone', 'smspro_message', '');

        if (empty($adminPhoneNumber) === false) {
            $adminNotify  = smspro_get_option('admin_arm_notify_' . $subscriptionstatus, 'smspro_arm_general', 'on');
            $adminMessage = smspro_get_option('admin_sms_arm_body_' . $subscriptionstatus, 'smspro_arm_message', '');
            $nos = explode(',', $adminPhoneNumber);
            $adminPhoneNumber = array_diff($nos, array('postauthor', 'post_author'));
            $adminPhoneNumber = implode(',', $adminPhoneNumber);
            if ($adminNotify === 'on' && $adminMessage !== '') {
                $adminMessage = $this->parseSmsBody($planData, $user_id, $adminMessage);
                do_action('sa_send_sms', $adminPhoneNumber, $adminMessage);
            }
        }
    
    }//end sendsms_booking_update()

    /**
     * Parse sms body.
     *
     * @param array  $data    data.
     * @param int    $user_id user_id.
     * @param string $content content.
     *
     * @return string
     */
    public function parseSmsBody($data,$user_id, $content = null)
    {
        $plan_name = $data['arm_current_plan_detail']['arm_subscription_plan_name'];
        $plan_type = $data['arm_current_plan_detail']['arm_subscription_plan_type'];
        $created_date = $data['arm_current_plan_detail']['arm_subscription_plan_created_date'];
        $start_plan        = $data['arm_start_plan'];
        $expire_plan       = $data['arm_expire_plan'];
        $trial_start       = $data['arm_trial_start'];
        $trial_end         = $data['arm_trial_end'];
        $cencelled_plan    = $data['arm_cencelled_plan'];
        $started_plan_date = $data['arm_started_plan_date'];
        $user_info = get_userdata($user_id);
        $find = array(
            '[member_name]',
            '[member_email]',
            '[plan_name]',
            '[plan_type]',
            '[created_date]',
            '[start_plan]',
            '[expire_plan]',
            '[trial_start]',
            '[trial_end]',
            '[cencelled_plan]',
            '[started_plan_date]',
            '[store_name]',
            '[shop_url]',
        );

        $replace = array(
        $user_info->first_name,
        $user_info->user_email,
            $plan_name,
            $plan_type,
            $created_date,
            $start_plan,
            $expire_plan, 
            $trial_start,
            $trial_end,
            $cencelled_plan,
            $started_plan_date,
            get_bloginfo('name'),
            get_site_url(),
        );
        
        $content = str_replace($find, $replace, $content);
        return $content;
    }//end parseSmsBody()


    /**
     * Get ArMember variables.
     *
     * @return array
     */
    public static function getArMembervariables()
    {
        $variable['[member_name]']    = 'Member Name';
        $variable['[member_email]']    = 'Member Email';
        $variable['[plan_name]']    = 'Plan name';
        $variable['[plan_type]']    = 'Plan type';
        $variable['[created_date]'] = 'Created date';
        $variable['[start_plan]']   = 'Start plan';
        $variable['[expire_plan]']  = 'Expire plan';
        $variable['[trial_start]']  = 'Trial start';
        $variable['[trial_end]']    = 'Trial end';
        $variable['[cencelled_plan]']    = 'Cencelled plan';
        $variable['[started_plan_date]'] = 'Started plan date';
       
        return $variable;
    }//end

    /**
     * Handle form for WordPress backend
     *
     * @return void
     */
    public function handleFormOptions()
    {
        if (is_plugin_active('armember-membership/armember-membership.php') === true) {
            add_filter('sAlertDefaultSettings', __CLASS__ . '::add_default_setting', 1);
            add_action('sa_addTabs', array($this, 'addTabs'), 10);
        }
    }//end handleFormOptions()

    /**
     * Check your otp setting is enabled or not.
     *
     * @return bool
     */
    public function isFormEnabled()
    {
        $userAuthorize = new smspro_Setting_Options();
        $islogged      = $userAuthorize->is_user_authorised();
        if ((is_plugin_active('armember-membership/armember-membership.php') === true) && ($islogged === true)) {
            return true;
        } else {
            return false;
        }
    }//end isFormEnabled()

    /**
     * Handle after failed verification
     *
     * @param object $userLogin   users object.
     * @param string $userEmail   user email.
     * @param string $phoneNumber phone number.
     *
     * @return void
     */
    public function handle_failed_verification($userLogin, $userEmail, $phoneNumber)
    {
        SmsAlertUtility::checkSession();
        if (isset($_SESSION[$this->form_session_var]) === false) {
            return;
        }
        if ((empty($_REQUEST['option']) === false) && sanitize_text_field(wp_unslash($_REQUEST['option'])) === 'smspro-validate-otp-form') {
            wp_send_json(SmsAlertUtility::_create_json_response(SmsAlertMessages::showMessage('INVALID_OTP'), 'error'));
            exit();
        } else {
            $_SESSION[$this->form_session_var] = 'verification_failed';
        }

    }//end handle_failed_verification()


    /**
     * Handle after post verification
     *
     * @param string $redirectTo  redirect url.
     * @param object $userLogin   user object.
     * @param string $userEmail   user email.
     * @param string $password    user password.
     * @param string $phoneNumber phone number.
     * @param string $extraData   extra hidden fields.
     *
     * @return void
     */
    public function handle_post_verification($redirectTo, $userLogin, $userEmail, $password, $phoneNumber, $extraData)
    {
        SmsAlertUtility::checkSession();
        if (isset($_SESSION[$this->form_session_var]) === false) {
            return;
        }
        if ((empty($_REQUEST['option']) === false ) && sanitize_text_field(wp_unslash($_REQUEST['option'])) === 'smspro-validate-otp-form') {
            wp_send_json(SmsAlertUtility::_create_json_response(SmsAlertMessages::showMessage('VALID_OTP'), 'success'));
            exit();
        } else {
            $_SESSION[$this->form_session_var] = 'validated';
        }
        
    }//end handle_post_verification()


    /**
     * Clear otp session variable
     *
     * @return void
     */
    public function unsetOTPSessionVariables()
    {
        unset($_SESSION[$this->form_session_var]);

    }//end unsetOTPSessionVariables()


    /**
     * Check current form submission is ajax or not
     *
     * @param bool $isAjax bool value for form type.
     *
     * @return bool
     */
    public function is_ajax_form_in_play($isAjax)
    {
        SmsAlertUtility::checkSession();
        if ($_SESSION[$this->form_session_var] === true) {
            return true;
        } else {
            return $isAjax;
        }

    }//end is_ajax_form_in_play()


}//end class
new armember();
