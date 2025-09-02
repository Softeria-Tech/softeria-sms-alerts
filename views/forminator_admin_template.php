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
$forminator_forms = SA_Forminator::getForminatorForms();
if (! empty($forminator_forms) ) {
	$disablePlayground     = SOFTSMAL_Utility::isPlayground()?"disablePlayground":"";
    ?>
<div class="cvt-accordion <?php echo $disablePlayground; ?>">
    <div class="accordion-section">
    <?php foreach ( $forminator_forms as $ks => $vs ) { ?>
        <div class="cvt-accordion-body-title" data-href="#accordion_<?php echo esc_attr($ks); ?>">
            <input type="checkbox" name="softeria_alerts_forminator_general[forminator_admin_notification_<?php echo esc_attr($ks); ?>]" id="softeria_alerts_forminator_general[forminator_admin_notification_<?php echo esc_attr($ks); ?>]" class="notify_box" <?php echo ( ( softeria_alerts_get_option('forminator_admin_notification_' . $ks, 'softeria_alerts_forminator_general', 'on') === 'on' ) ? "checked='checked'" : '' ); ?>/><label><?php echo esc_html(ucwords(str_replace('-', ' ', $vs))); ?></label>
            <span class="expand_btn"></span>
        </div>
        <div id="accordion_<?php echo esc_attr($ks); ?>" class="cvt-accordion-body-content">
            <table class="form-table">
                <tr valign="top" style="position:relative">
                <td>
                <a href="admin.php?page=forminator-cform-wizard&id=<?php echo $ks;?>" title="Edit Form" target="_blank" class="alignright"><small><?php esc_html_e('Edit Form', 'soft-sms-alerts')?></small></a>
                <div class="softeria_alerts_tokens">
        <?php
        $fields = SA_Forminator::getForminatorVariables($ks);
        foreach ( $fields as $key=>$value ) {
            echo  "<a href='#' data-val='[" . esc_attr($key) . "]'>".esc_attr($value)."</a> | ";
        }
        ?>
                </div>                
                <textarea data-parent_id="softeria_alerts_forminator_general[forminator_admin_notification_<?php echo esc_attr($ks); ?>]" name="softeria_alerts_forminator_message[forminator_admin_sms_body_<?php echo esc_attr($ks); ?>]" id="softeria_alerts_forminator_message[forminator_admin_sms_body_<?php echo esc_attr($ks); ?>]" <?php echo( ( softeria_alerts_get_option('forminator_admin_notification_' . esc_attr($ks), 'softeria_alerts_forminator_general', 'on') === 'on' ) ? '' : "readonly='readonly'" ); ?> class="token-area"><?php echo esc_textarea(softeria_alerts_get_option('forminator_admin_sms_body_' . $ks, 'softeria_alerts_forminator_message', SOFTSMAL_Messages::showMessage('DEFAULT_CONTACT_FORM_ADMIN_MESSAGE'))); ?></textarea>
                <div id="menu_forminator_admin_<?php echo esc_attr($ks); ?>" class="sa-menu-token" role="listbox"></div>
                </td>
                </tr>
            </table>
        </div>
    <?php } ?>
    </div>
</div>
    <?php
} else {
    echo '<h3>No Form(s) published</h3>';
}
?>
