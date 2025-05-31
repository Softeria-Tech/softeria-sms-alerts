<?php

/**
 * Booknetic helper.
 *
 * PHP version 5
 *
 * @category Handler
 * @package  SMSPro
 * @author   SMS Pro <support@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 */

if (defined('ABSPATH') === false) {
    exit;
}

if (is_plugin_active('booknetic/init.php') === false) {
    return;
}
use BookneticApp\Models\Customer;
use BookneticApp\Models\Service;
use BookneticApp\Models\Staff;
use BookneticApp\Models\Location;
use BookneticApp\Models\Appointment;
use BookneticApp\Providers\Helpers\Date;
/**
 * PHP version 5
 *
 * @category Handler
 * @package  SMSPro
 * @author   SMS Pro <support@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 * 
 * SMBooknetic class 
 */
class SMBooknetic extends FormInterface
{
    /**
     * 
     * Construct function.
     *     
     * @return void       
     */
    public function handleForm()
    {
        add_action('bkntc_appointment_created', array($this, 'sendSmsOn'), 5, 1);
        add_action('bkntc_payment_confirmed', array($this, 'sendSmsOnConfirmPayment'), 5, 1);
        add_action('bkntc_appointment_after_edit', array($this, 'sendSmsOn'), 5, 1);      
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
          $bookingStatuses = [
            'pending',
            'approved',
            'canceled',
        'rejected',            
          ];

          foreach ($bookingStatuses as $ks => $vs) {
              $defaults['smspro_bnc_general']['customer_bnc_notify_' . $vs]   = 'off';
              $defaults['smspro_bnc_message']['customer_sms_bnc_body_' . $vs] = '';
              $defaults['smspro_bnc_general']['admin_bnc_notify_' . $vs]      = 'off';
              $defaults['smspro_bnc_message']['admin_sms_bnc_body_' . $vs]    = '';
          }
          return $defaults;
    }



    /**
     * Add tabs to smspro settings at backend.
     *
     * @param array $tabs tabs.
     *
     * @return array
     */
    public static function add_tabs($tabs = array())
    {
        $customerParam = array(
            'checkTemplateFor' => 'bnc_customer',
            'templates'        => self::getCustomerTemplates(),
        );

        $admin_param = array(
            'checkTemplateFor' => 'bnc_admin',
            'templates'        => self::getAdminTemplates(),
        );


        $tabs['booknetic']['nav']           = 'Booknetic';
        $tabs['booknetic']['icon']          = 'dashicons-food';

        $tabs['booknetic']['inner_nav']['booknetic_cust']['title']          = 'Customer Notifications';
        $tabs['booknetic']['inner_nav']['booknetic_cust']['tab_section']    = 'bookneticcusttemplates';
        $tabs['booknetic']['inner_nav']['booknetic_cust']['first_active']   = true;
        $tabs['booknetic']['inner_nav']['booknetic_cust']['tabContent']     = $customerParam;
        $tabs['booknetic']['inner_nav']['booknetic_cust']['filePath']       = 'views/message-template.php';

        $tabs['booknetic']['inner_nav']['booknetic_admin']['title']         = 'Admin Notifications';
        $tabs['booknetic']['inner_nav']['booknetic_admin']['tab_section']   = 'bookneticadmintemplates';
        $tabs['booknetic']['inner_nav']['booknetic_admin']['tabContent']    = $admin_param;
        $tabs['booknetic']['inner_nav']['booknetic_admin']['filePath']      = 'views/message-template.php';
        
        return $tabs;
    }

    /**
     * Get customer templates.
     *
     * @return array
     */
    public static function getCustomerTemplates()
    {
       
        $bookingStatuses = array(
        'pending'  => 'Pending',
        'approved' => 'Approved',
        'canceled'    => 'Cancelled',
        'rejected'    => 'Rejected',       
        );

        $templates           = []; 

        foreach ($bookingStatuses as $ks => $vs) {

            $currentVal      = smspro_get_option('customer_bnc_notify_' . strtolower($ks), 'smspro_bnc_general', 'on');

            $checkboxNameId  = 'smspro_bnc_general[customer_bnc_notify_' . strtolower($ks) . ']';
            $textareaNameId  = 'smspro_bnc_message[customer_sms_bnc_body_' . strtolower($ks) . ']';

            $defaultTemplate = smspro_get_option('customer_sms_bnc_body_' . strtolower($ks), 'smspro_bnc_message', sprintf(__('Hello %1$s, status of your booking #%2$s with %3$s has been changed to %4$s.%5$sPowered by%6$ssms.softeriatech.com', 'sms-pro'), '[first_name]', '[service_name]', '[store_name]', $vs, PHP_EOL, PHP_EOL));


            $textBody       = smspro_get_option('customer_sms_bnc_body_' . strtolower($ks), 'smspro_bnc_message', $defaultTemplate);

            $templates[$ks]['title']          = 'When customer booking is ' . ucwords($vs);
            $templates[$ks]['enabled']        = $currentVal;
            $templates[$ks]['status']         = $ks;
            $templates[$ks]['text-body']      = $textBody;
            $templates[$ks]['checkboxNameId'] = $checkboxNameId;
            $templates[$ks]['textareaNameId'] = $textareaNameId;
            $templates[$ks]['token']          = self::geBookneticvariables();
        }
        return $templates;
    }

    /**
     * Get admin templates.
     *
     * @return array
     */
    public static function getAdminTemplates()
    { 
        $bookingStatuses = array(
        'pending'  => 'Pending',
        'approved' => 'Approved',
        'canceled'    => 'Cancelled',
        'rejected'    => 'Rejected',       
        );
        $templates           = []; 
        foreach ($bookingStatuses as $ks => $vs) {
       
            $currentVal      = smspro_get_option('admin_bnc_notify_' . strtolower($ks), 'smspro_bnc_general', 'on');
            $checkboxNameId  = 'smspro_bnc_general[admin_bnc_notify_' . strtolower($ks) . ']';
            $textareaNameId  = 'smspro_bnc_message[admin_sms_bnc_body_' . strtolower($ks) . ']';

            $defaultTemplate = smspro_get_option('admin_sms_bnc_body_' . strtolower($ks), 'smspro_bnc_message', sprintf(__('%1$s status of booking has been changed to %2$s.', 'sms-pro'), '[store_name]:', $vs));


            $textBody = smspro_get_option('admin_sms_bnc_body_' . strtolower($ks), 'smspro_bnc_message', $defaultTemplate);

            $templates[$ks]['title']          = 'When admin change status to ' . $vs;
            $templates[$ks]['enabled']        = $currentVal;
            $templates[$ks]['status']         = $ks;
            $templates[$ks]['text-body']      = $textBody;
            $templates[$ks]['checkboxNameId'] = $checkboxNameId;
            $templates[$ks]['textareaNameId'] = $textareaNameId;
            $templates[$ks]['token']          = self::geBookneticvariables();
        }
        return $templates;
    }
	
	public function sendSmsOnConfirmPayment($appointmentId){		
		 $appointmentData = Appointment::get($appointmentId);		 
		 $appointmentData['appointmentId'] = $appointmentId;
		 $appointmentData['customerData'] = Customer::get($appointmentData['customer_id']);
		 $appointmentData['customerData']['phone'] = Customer::get($appointmentData['customer_id'])->phone_number;		 
		 $appointmentData['date'] = Date::format( 'Y-m-d', $appointmentData['starts_at']);
		 $appointmentData['time'] = Date::format( 'H:i:s A', $appointmentData['starts_at']);		 
		$appointmentData['locationInf'] = Location::get($appointmentData['location_id'] );
		$appointmentData['serviceInf']   = Service::get($appointmentData['service_id']);
		$appointmentData['staffInf'] = Staff::get($appointmentData['staff_id'] ); 
		
		 $this->sendSmsOn($appointmentData);
	}

     /**
      * Send sms approved pending.
      *
      * @param int $appointmentData appointmentData
      *
      * @return void
      */
    public function sendSmsOn($appointmentData)
    {
		
        $dataId = $appointmentData->customerId;        
        $customerInfo = Customer::get($dataId);
        $bookingStatus     = $appointmentData->status; 		
        $buyerNumber     = !empty($appointmentData->customerData['phone']) ? $appointmentData->customerData['phone'] : $customerInfo->phone_number;

        $customerMessage   = smspro_get_option('customer_sms_bnc_body_' .$bookingStatus, 'smspro_bnc_message', '');

        $customerNotify    = smspro_get_option('customer_bnc_notify_' . $bookingStatus, 'smspro_bnc_general', 'on');
        if (($customerNotify === 'on' && $customerMessage !== '' && $buyerNumber != '')) {
            $buyerMessage = $this->parseSmsBody($customerInfo, $appointmentData, $customerMessage);
            do_action('sa_send_sms', $buyerNumber,  $buyerMessage);
        }
        
        // Send msg to admin.
        $adminPhoneNumber = smspro_get_option('sms_admin_phone', 'smspro_message', '');
        if (empty($adminPhoneNumber) === false) {
            $adminNotify        = smspro_get_option('admin_bnc_notify_' .$bookingStatus, 'smspro_bnc_general', 'on');
            $adminMessage       = smspro_get_option('admin_sms_bnc_body_' . $bookingStatus, 'smspro_bnc_message', '');
            $nos = explode(',', $adminPhoneNumber);
            $adminPhoneNumber   = array_diff($nos, array('postauthor', 'post_author'));
            $adminPhoneNumber   = implode(',', $adminPhoneNumber);
            if ($adminNotify === 'on' && $adminMessage !== '') {
                $adminMessage   = $this->parseSmsBody($customerInfo, $appointmentData, $adminMessage);
                do_action('sa_send_sms', $adminPhoneNumber, $adminMessage);
            }
        }
    }
    /**
     * Parse sms body.
     *
     * @param array  $customerInfo    customerInfo.
     * @param array  $appointmentData appointmentData.
     * @param string $content         content.
     *
     * @return string
     */
    public function parseSmsBody($customerInfo,$appointmentData, $content = null)
    {     
        $appointmentId =$appointmentData->appointmentId;
        $firstName         = !empty($appointmentData->customerData['first_name']) ? $appointmentData->customerData['first_name'] : $customerInfo->first_name;
        $lastName          = !empty($appointmentData->customerData['last_name']) ? $appointmentData->customerData['last_name'] : $customerInfo->last_name;
        $custEmail         = !empty($appointmentData->customerData['email']) ? $appointmentData->customerData['email'] : $customerInfo->email; 
        $custPhone         = !empty($appointmentData->customerData['phone']) ? $appointmentData->customerData['phone'] : $customerInfo->phone_number;
        $appointmentDate       = $appointmentData->date;
        $appointmentTime   = $appointmentData->time; 
        $serviceId         = $appointmentData->serviceInf['id']; 
        $serviceName       = $appointmentData->serviceInf['name'];
        $servicePrice      = $appointmentData->serviceInf['price'];
        $staffId           = $appointmentData->staffInf['id'];
        $staffName         = $appointmentData->staffInf['name'];
        $staffEmail        = $appointmentData->staffInf['email'];
        $staffPhone        = $appointmentData->staffInf['phone_number'];
        $locationId        = $appointmentData->locationInf['id'];
        $locationName      = $appointmentData->locationInf['name'];
        $locationAddress   = $appointmentData->locationInf['address'];
        $locationPhone     = $appointmentData->locationInf['phone_number'];    
        $postStatus        = $appointmentData->status;

        $find = array(
        '[appointmentId]',
            '[first_name]',
            '[last_name]',
            '[email]',
            '[phone]',
            '[booking_date]',
            '[booking_time]',
            '[service_id]',
            '[service_name]',
            '[service_price]',
            '[staff_id]',
            '[staff_name]',
            '[staff_email]',
            '[staff_Phone]',
            '[location_id]',
            '[location_name]',
            '[location_address]',
            '[location_phone]',
            '[status]',
            '[store_name]',
            '[shop_url]',
        );

        $replace = array(
        $appointmentId,
            $firstName,
            $lastName,
            $custEmail,
            $custPhone,
            $appointmentDate,
            $appointmentTime,
            $serviceId,
            $serviceName,
            $servicePrice,
            $staffId,
            $staffName,
            $staffEmail,
            $staffPhone,
            $locationId,
            $locationName,
            $locationAddress,
            $locationPhone,
            $postStatus,
            get_bloginfo('name'),
            get_site_url(),
        );
        $content = str_replace($find, $replace, $content);
        return $content;
    }

    /**
     * Get Restaurant Reservations variables.
     *
     * @return array
     */
    public static function geBookneticvariables()
    {
        $variable['[appointmentId]']        = 'Appointment Id';
        $variable['[first_name]']        = 'First Name';
        $variable['[last_name]']         = 'Last Name';
        $variable['[email]']             = 'Email';
        $variable['[phone]']             = 'Phone';
        $variable['[booking_date]']      = 'Booking Date';
        $variable['[booking_time]']      = 'Booking Time';
        $variable['[service_id]']        = 'Service Id';
        $variable['[service_name]']      = 'Service Name';
        $variable['[service_price]']     = 'Service Price';
        $variable['[staff_id]']          = 'Staff Id';
        $variable['[staff_name]']        = 'Staff Name';
        $variable['[staff_email]']       = 'Staff Email';
        $variable['[staff_Phone]']       = 'Staff Phone';
        $variable['[location_id]']       = 'Location Id';
        $variable['[location_name]']     = 'Location Name';
        $variable['[location_address]']  = 'Location Address';
        $variable['[location_phone]']    = 'Location Phone';
        $variable['[status]']               = 'Post Status';
        return $variable;
    }

    /**
     * Handle form for WordPress backend
     *
     * @return void
     */
    public function handleFormOptions()
    {

        if (is_plugin_active('booknetic/init.php') === true) {
            add_filter('sAlertDefaultSettings', __CLASS__ . '::add_default_setting', 1);
            add_action('sa_addTabs', array($this, 'add_tabs'), 10);
        }
    }

    /**
     * Check your otp setting is enabled or not.
     *
     * @return bool
     */
    public function isFormEnabled()
    {

        $userAuthorize = new smspro_Setting_Options();
        $islogged      = $userAuthorize->is_user_authorised();
        if ((is_plugin_active('booknetic/init.php') === true) && ($islogged === true)) {
            return true;
        } else {
            return false;
        }
    }

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
       
    }


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
       
    }


    /**
     * Clear otp session variable
     *
     * @return void
     */
    public function unsetOTPSessionVariables()
    {
        
    }


    /**
     * Check current form submission is ajax or not
     *
     * @param bool $isAjax bool value for form type.
     *
     * @return bool
     */
    public function is_ajax_form_in_play($isAjax)
    {
        return $isAjax;
    }
}
new SMBooknetic();