<?php
/**
 * Template.
 * PHP version 5
 *
 * @category View
 * @package  SMSPro
 * @author   SMS Pro <support@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 */
$ninja_forms = NinjaForm::getNinjaForms();
if (! empty($ninja_forms) ) {
    ?>
<div class="cvt-accordion">
    <div class="accordion-section">
    <?php foreach ( $ninja_forms as $ks => $vs ) { ?>
        <div class="cvt-accordion-body-title" data-href="#accordion_<?php echo esc_attr($ks); ?>">
            <input type="checkbox" name="smspro_ninja_general[ninja_admin_notification_<?php echo esc_attr($ks); ?>]" id="smspro_ninja_general[ninja_admin_notification_<?php echo esc_attr($ks); ?>]" class="notify_box" <?php echo ( ( smspro_get_option('ninja_admin_notification_' . $ks, 'smspro_ninja_general', 'on') === 'on' ) ? "checked='checked'" : '' ); ?>/><label><?php echo esc_html(ucwords(str_replace('-', ' ', $vs))); ?></label>
            <span class="expand_btn"></span>
        </div>
        <div id="accordion_<?php echo esc_attr($ks); ?>" class="cvt-accordion-body-content">
            <table class="form-table">
                <tr valign="top" style="position:relative">
                <td>
                <a href="admin.php?page=ninja-forms&form_id=<?php echo $ks;?>" title="Edit Form" target="_blank" class="alignright"><small><?php esc_html_e('Edit Form', 'sms-pro')?></small></a>
                <div class="smspro_tokens">
        <?php
        $fields = NinjaForm::getNinjavariables($ks);
        foreach ( $fields as $field ) {
            if (! is_array($field) ) {
                echo  "<a href='#' data-val='[" . esc_attr($field) . "]'>".esc_attr($field)."</a> | ";
            } else {    
                $field = isset($field['cells'][0]['fields'][0])?$field['cells'][0]['fields'][0]:'';
                if ($field!='') {
                    echo  "<a href='#' data-val='[" . esc_attr($field) . "]'>".esc_attr($field)."</a> | ";
                }
            }
        }
        ?>
                </div>
                <textarea data-parent_id="smspro_ninja_general[ninja_admin_notification_<?php echo esc_attr($ks); ?>]" name="smspro_ninja_message[ninja_admin_sms_body_<?php echo esc_attr($ks); ?>]" id="smspro_ninja_message[ninja_admin_sms_body_<?php echo esc_attr($ks); ?>]" <?php echo( ( smspro_get_option('ninja_admin_notification_' . esc_attr($ks), 'smspro_ninja_general', 'on') === 'on' ) ? '' : "readonly='readonly'" ); ?>><?php echo esc_textarea(smspro_get_option('ninja_admin_sms_body_' . $ks, 'smspro_ninja_message', SmsAlertMessages::showMessage('DEFAULT_CONTACT_FORM_ADMIN_MESSAGE'))); ?></textarea>
                <div id="menu_ninja_admin_<?php echo esc_attr($ks); ?>" class="sa-menu-token" role="listbox"></div>
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