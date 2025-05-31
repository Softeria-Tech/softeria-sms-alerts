<?php
/**
 * Login with otp form template.
 * PHP version 5
 *
 * @category Template
 * @package  SMSPro
 * @author   SMS Pro <support@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 */
 
$redirect = isset($_GET['redirect_to'])?$_GET['redirect_to']:$redirect_url;
?>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label for="username"><?php esc_html_e($label_field, 'sms-pro'); ?><span class="required">*</span></label>
    <input type="tel" placeholder = "<?php esc_html_e($placeholder_field, 'sms-pro'); ?>" class="woocommerce-Input woocommerce-Input--text input-text sa_mobileno phone-valid" name="username"  value="">
    <input type="hidden" class="woocommerce-Input woocommerce-Input--text input-text" name="redirect" value="<?php esc_html_e($redirect, 'sms-pro'); ?>">
</p>
<?php 
echo apply_filters('gglcptch_display_recaptcha', '', 'sa_lwo_form');
?>
<p class="form-row">
    <button type="submit" class="button smspro_login_with_otp_btn" name="smspro_login_with_otp_btn" value="<?php echo esc_html_e($button_field, 'sms-pro'); ?>"><span class="button__text"><?php echo esc_html_e($button_field, 'sms-pro'); ?></span></button>    
    <a href="#" onclick="return false;" class="sa_default_login_form" data-parentForm="login"><?php esc_html_e('Back', 'sms-pro'); ?></a>
</p>