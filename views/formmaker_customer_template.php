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
$formmarker_forms = SAFormMaker::getFormMaker();
if (! empty($formmarker_forms) ) {
	$disablePlayground     = SOFTSMAL_Utility::isPlayground()?"disablePlayground":"";
    ?>
<!-- accordion -->
<div class="cvt-accordion">
    <div class="accordion-section">
    <?php foreach ( $formmarker_forms as $ks => $vs ) { ?>
        <div class="cvt-accordion-body-title" data-href="#accordion_cust_<?php echo esc_attr($ks); ?>">
            <input type="checkbox" name="softeria_alerts_formmarker_general[formmarker_order_status_<?php echo esc_attr($ks); ?>]" id="softeria_alerts_formmarker_general[formmarker_order_status_<?php echo esc_attr($ks); ?>]" class="notify_box" <?php echo ( ( softeria_alerts_get_option('formmarker_order_status_' . esc_attr($ks), 'softeria_alerts_formmarker_general', 'on') === 'on' ) ? "checked='checked'" : '' ); ?>/><label><?php echo esc_attr(ucwords(str_replace('-', ' ', $vs))); ?></label>
            <span class="expand_btn"></span>
        </div>
        <div id="accordion_cust_<?php echo esc_attr($ks); ?>" class="cvt-accordion-body-content">
            <table class="form-table">
                <tr>
                    <td><input data-parent_id="softeria_alerts_formmarker_general[formmarker_order_status_<?php echo esc_attr($ks); ?>]" type="checkbox" name="softeria_alerts_formmarker_general[formmarker_message_<?php echo esc_attr($ks); ?>]" id="softeria_alerts_formmarker_general[formmarker_message_<?php echo esc_attr($ks); ?>]" class="notify_box" <?php echo ( ( softeria_alerts_get_option('formmarker_message_' . esc_attr($ks), 'softeria_alerts_formmarker_general', 'on') === 'on' ) ? "checked='checked'" : '' ); ?>/><label for="softeria_alerts_formmarker_general[formmarker_message_<?php echo esc_attr($ks); ?>]">Enable Message</label>
                    <a href="admin.php?page=manage_fm&task=edit&current_id=<?php echo $ks;?>" title="Edit Form" target="_blank" class="alignright"><small><?php esc_html_e('Edit Form', 'soft-sms-alerts')?></small></a>
                    </td>
                    </tr>
                <tr valign="top"  style="position:relative">
                   <td class="<?php echo $disablePlayground ?>">
                        <div class="softeria_alerts_tokens">
        <?php
        $fields = SAFormMaker::getFormMakerVariables($ks);
        foreach ( $fields as $key=>$value ) {
            echo  "<a href='#' data-val='[" . esc_attr($key) . "]'>".esc_attr($value)."</a> | ";
        }
        ?>
                        </div>
                        <textarea data-parent_id="softeria_alerts_formmarker_general[formmarker_message_<?php echo esc_attr($ks); ?>]" name="softeria_alerts_formmarker_message[formmarker_sms_body_<?php echo esc_attr($ks); ?>]" id="softeria_alerts_formmarker_message[formmarker_sms_body_<?php echo esc_attr($ks); ?>]" <?php echo( ( softeria_alerts_get_option('formmarker_order_status_' . esc_attr($ks), 'softeria_alerts_formmarker_general', 'on') === 'on' ) ? '' : "readonly='readonly'" ); ?> class="token-area"><?php echo esc_textarea(softeria_alerts_get_option('formmarker_sms_body_' . esc_attr($ks), 'softeria_alerts_formmarker_message', SOFTSMAL_Messages::showMessage('DEFAULT_CONTACT_FORM_CUSTOMER_MESSAGE'))); ?></textarea>
                        <div id="menu_formmarker_cust_<?php echo esc_attr($ks); ?>" class="sa-menu-token" role="listbox"></div>
                    </td>
                </tr>
                <tr>
                    <td>
                        Select Phone Field : <select name="softeria_alerts_formmarker_general[formmarker_sms_phone_<?php echo esc_attr($ks); ?>]">
        <?php
        foreach ( $fields as $key=>$value ) {
            ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php echo ( trim(softeria_alerts_get_option('formmarker_sms_phone_' . $ks, 'softeria_alerts_formmarker_general', '')) === $key ) ? 'selected="selected"' : ''; ?>><?php echo esc_attr($value); ?></option>
            <?php
        }
        ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input data-parent_id="softeria_alerts_formmarker_general[formmarker_order_status_<?php echo esc_attr($ks); ?>]" type="checkbox" name="softeria_alerts_formmarker_general[formmarker_otp_<?php echo esc_attr($ks); ?>]" id="softeria_alerts_formmarker_general[formmarker_otp_<?php echo esc_attr($ks); ?>]" class="notify_box" <?php echo ( ( softeria_alerts_get_option('formmarker_otp_' . esc_attr($ks), 'softeria_alerts_formmarker_general', 'off') === 'on' ) ? "checked='checked'" : '' ); ?>/><label for="softeria_alerts_formmarker_general[formmarker_otp_<?php echo esc_attr($ks); ?>]">Enable Mobile Verification</label>
                    </td>
                </tr>
            </table>
        </div>
    <?php } ?>
    </div>
</div>
<!--end accordion-->
    <?php
} else {
    echo '<h3>No Form(s) published</h3>';
}
?>
