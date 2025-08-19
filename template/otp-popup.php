<?php
/**
 * Otp popup 2 template.
 * PHP version 5
 *
 * @category Template
 * @package  SOFTSMSAlerts
 * @author   Softeria Tech <billing@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 */
 use Elementor\Frontend;
   
$uniqueNo                  = rand();        
$alt_form_id             = 'saFormNo_'.$uniqueNo;
$otp_template_style = softeria_alerts_get_option('otp_template_style', 'softeria_alerts_general', 'popup-4');
$otp_in_popup = softeria_alerts_get_option('otp_in_popup', 'softeria_alerts_general', 'on');
$popup_class = 'popup';
if ('on' !== $otp_in_popup) {
    $popup_class = '';
}
$modal_style = softeria_alerts_get_option('modal_style', 'softeria_alerts_general', '');
$otp_template_style = ('otp-popup-1.php'===$otp_template_style)?'popup-1':(('otp-popup-2.php'===$otp_template_style)?'popup-2':$otp_template_style);
$sa_values              = !empty(SmsAlertUtility::get_elementor_data("form_list"))?SmsAlertUtility::get_elementor_data("form_list"):$otp_template_style;
$form_id                = (isset($form_id) ? $form_id : $alt_form_id);
$post = get_page_by_path('modal_style', OBJECT, 'softeria-sms-alerts');
if (is_plugin_active('elementor/elementor.php') && !empty($post)) {  
    $post_id= $post->ID;    
    $frontent = new Frontend();
    $content =  $frontent->get_builder_content($post_id);
} else {
    if ($sa_values == 'popup-2') {
        $content = SAPopup::getModelStyle(array('otp_template_style'=>'popup-2'));
    } else if ($sa_values == 'popup-3') {
        $content = SAPopup::getModelStyle(array('otp_template_style'=>'popup-3'));
    } else if ($sa_values == 'popup-1') {
        $content = SAPopup::getModelStyle(array('otp_template_style'=>'popup-1'));
    } else {
        $content = SAPopup::getModelStyle(array('otp_template_style'=>'popup-4'));
    }
}
 echo ' <div class="modal smsproModal '.$modal_style.' '.$popup_class.' '.$form_id.' '. esc_attr($sa_values) . '" data-modal-close="' . esc_attr(substr($modal_style, 0, -2)) . '" data-form-id="'.$form_id.'">			
		'.$content.'
<div class="ring sa-hide"><span></span></div></div>'; 