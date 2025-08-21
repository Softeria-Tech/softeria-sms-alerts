<?php
/**
 * Template.
 * PHP version 5
 *
 * @category View
 * @package  SOFTSMSAlerts
 * @author   Softeria Tech <billing@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 */
global $pagenow;
if (empty($pagenow) || 'plugins.php' != $pagenow ) {
    return false;
}

    $form_fields = apply_filters('softeria_alerts_deactivation_form_fields', array());
?>
<?php if (! empty($form_fields) ) : ?>
    <div class="smsf-onboarding-section">
        <div class="smsf-on-boarding-wrapper-background">
        <div class="smsf-on-boarding-wrapper">
            <div class="smsf-on-boarding-close-btn">
                <a href="#" onclick="return false;">
                    <span class="close-form">x</span>
                </a>
            </div>
            <h3 class="smsf-on-boarding-heading"></h3>
            <p class="smsf-on-boarding-desc"><?php esc_html_e('May we have a little info about why you are deactivating?', 'soft-sms-alerts'); ?></p>
            <form action="#" method="post" class="smsf-on-boarding-form">
                <?php foreach ( $form_fields as $key => $field_attr ) : ?>
                    <?php $this->renderFieldHtml($field_attr, 'deactivating'); ?>
                <?php endforeach; ?>
                <div class="smsf-on-boarding-form-btn__wrapper">
                    <div class="smsf-on-boarding-form-submit smsf-on-boarding-form-verify ">
                    <button type="submit" class="smsf-on-boarding-submit smsf-on-boarding-verify button button-danger"><span class="button__text">SUBMIT AND DEACTIVATE</span></button>
                </div>
                <div class="smsf-on-boarding-form-no_thanks">
                    <a href="#" onclick="return false;" class="smsf-deactivation-no_thanks"><?php esc_html_e('Skip and Deactivate Now', 'soft-sms-alerts'); ?></a>
                </div>
                </div>
            </form>
        </div>
    </div>
    </div>
<?php endif; ?>
