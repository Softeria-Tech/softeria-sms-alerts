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
$Jet_forms = JetForm::getJetformForms();
if (! empty($Jet_forms) ) {
    ?>
<!-- accordion -->
<div class="cvt-accordion">
    <div class="accordion-section">
    <?php foreach ( $Jet_forms as $ks => $vs ) { ?>
        <div class="cvt-accordion-body-title" data-href="#accordion_cust_<?php echo esc_attr($ks); ?>">
            <input type="checkbox" name="smspro_jetform_general[jetform_order_status_<?php echo esc_attr($ks); ?>]" id="smspro_jetform_general[jetform_order_status_<?php echo esc_attr($ks); ?>]" class="notify_box" <?php echo ( ( smspro_get_option('jetform_order_status_' . esc_attr($ks), 'smspro_jetform_general', 'on') === 'on' ) ? "checked='checked'" : '' ); ?>/><label><?php echo esc_attr(ucwords(str_replace('-', ' ', $vs))); ?></label>
            <span class="expand_btn"></span>
        </div>
        <div id="accordion_cust_<?php echo esc_attr($ks); ?>" class="cvt-accordion-body-content">
            <table class="form-table">
                <tr>
                    <td><input data-parent_id="smspro_jetform_general[jetform_order_status_<?php echo esc_attr($ks); ?>]" type="checkbox" name="smspro_jetform_general[jetform_message_<?php echo esc_attr($ks); ?>]" id="smspro_jetform_general[jetform_message_<?php echo esc_attr($ks); ?>]" class="notify_box" <?php echo ( ( smspro_get_option('jetform_message_' . esc_attr($ks), 'smspro_jetform_general', 'on') === 'on' ) ? "checked='checked'" : '' ); ?>/><label for="smspro_jetform_general[jetform_message_<?php echo esc_attr($ks); ?>]">Enable Message</label>
                    <a href="post.php?post=<?php echo $ks;?>&action=edit" title="Edit Form" target="_blank" class="alignright"><small><?php esc_html_e('Edit Form', 'sms-pro')?></small></a>
                    </td>                    
                    </tr>
                <tr valign="top"  style="position:relative">
                    <td>
                        <div class="smspro_tokens">
        <?php
        $fields = JetForm::getJetformVariables($ks);
        foreach ( $fields as $key=>$value ) {
            echo  "<a href='#' data-val='[" . esc_attr($key) . "]'>".esc_attr($value)."</a> | ";
        }
        ?>
                        </div>
                        <textarea data-parent_id="smspro_jetform_general[jetform_message_<?php echo esc_attr($ks); ?>]" name="smspro_jetform_message[jetform_sms_body_<?php echo esc_attr($ks); ?>]" id="smspro_jetform_message[jetform_sms_body_<?php echo esc_attr($ks); ?>]" <?php echo( ( smspro_get_option('jetform_order_status_' . esc_attr($ks), 'smspro_jetform_general', 'on') === 'on' ) ? '' : "readonly='readonly'" ); ?> class="token-area"><?php echo esc_textarea(smspro_get_option('jetform_sms_body_' . esc_attr($ks), 'smspro_jetform_message', SmsAlertMessages::showMessage('DEFAULT_CONTACT_FORM_CUSTOMER_MESSAGE'))); ?></textarea>
                        <div id="menu_jetform_cust_<?php echo esc_attr($ks); ?>" class="sa-menu-token" role="listbox"></div>
                    </td>
                </tr>                
                <tr>
                    <td>
                        Select Phone Field : <select name="smspro_jetform_general[jetform_sms_phone_<?php echo esc_attr($ks); ?>]">
        <?php
        foreach ( $fields as $key=>$value ) {
            ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php echo ( trim(smspro_get_option('jetform_sms_phone_' . $ks, 'smspro_jetform_general', '')) === $key ) ? 'selected="selected"' : ''; ?>><?php echo esc_attr($value); ?></option>
            <?php
        }
        ?>
                        </select>
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
