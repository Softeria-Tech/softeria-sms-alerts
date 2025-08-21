<?php
/**
 * Cf7 template.
 * PHP version 5
 *
 * @category View
 * @package  SOFTSMSAlerts
 * @author   Softeria Tech <billing@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 */
$wpcf7 = WPCF7_ContactForm::get_current();
if (empty($wpcf7->id()) ) {
    echo '<h3>';
    esc_html_e('Please save your contact form 7 once.', 'soft-sms-alerts');
    echo '</h3>';
} else {
    $contact_form = WPCF7_ContactForm::get_instance($wpcf7->id());
    $form_fields  = $contact_form->scan_form_tags();
    $visitor_msg_enable = ( isset($data['visitor_notification']) ) ? $data['visitor_notification'] : "off";
    $admin_msg_enable = ( isset($data['admin_notification']) ) ? $data['admin_notification'] : "off";
    $admin_message = ( ! empty($data['text']) ) ? trim($data['text']) : SmsAlertMessages::showMessage('DEFAULT_CONTACT_FORM_ADMIN_MESSAGE');
    $visitor_no = ( ! empty($data['visitorNumber']) ) ? $data['visitorNumber'] : "[billing_phone]";
    $visitor_msg = ( ! empty($data['visitorMessage']) ) ? $data['visitorMessage'] :SmsAlertMessages::showMessage('DEFAULT_CONTACT_FORM_CUSTOMER_MESSAGE');
    ?>    
<div id="cf7si-sms-sortables" class="meta-box-sortables ui-sortable">
 <div class="tab-panels woocommerce">
<section id="softeria_alerts_settings">
<div class="cvt-accordion">
    <div class="accordion-section">
        <div class="cvt-accordion-body-title" data-href="#accordion_wc_visitor_notification">
            <input type="checkbox" name="wpcf7softeria-alert-settings[visitor_notification]" id="wpcf7softeria-alert-settings[visitor_notification]" class="notify_box" <?php echo ( ( 'on' === $visitor_msg_enable ) ? "checked='checked'" : '' ); ?> ><label>Visitor SMS Notification</label>
            <span class="expand_btn"></span>
        </div>
        <div id="accordion_wc_visitor_notification" class="cvt-accordion-body-content" style="display: none;">
            <table class="form-table">
                <tbody><tr valign="top">
                    <td>
                       <div class="softeria_alerts_tokens">
                        <?php
                        foreach ( $form_fields as $form_field ) {
                                           $field = json_decode(wp_json_encode($form_field), true);
                            if ('' !== $field['name'] ) {
                                echo  "<a href='#' data-val='[" . esc_attr($field['name']) . "]'>".esc_attr(ucwords(str_replace('-', ' ', $field['name'])))."</a> | ";
                            }
                        }
                        ?>
                        </div>
                        <textarea id="visitor_wpcf7-mail-body" name="wpcf7softeria-alert-settings[visitorMessage]" data-parent_id="wpcf7softeria-alert-settings[visitor_notification]" pre_modified_txt="<?php echo esc_textarea($visitor_msg); ?>" style="width: 100%;" class="token-area"><?php echo esc_textarea($visitor_msg); ?></textarea>
                        <div id="menu_cf7_cust" class="sa-menu-token" role="listbox"></div>
                    </td>
                </tr>
            </tbody></table>
        </div>
        <div class="cvt-accordion-body-title" data-href="#accordion_wc_admin_notification">
            <input type="checkbox" name="wpcf7softeria-alert-settings[admin_notification]" id="wpcf7softeria-alert-settings[admin_notification]" class="notify_box" <?php echo ( ( 'on' === $admin_msg_enable ) ? "checked='checked'" : '' ); ?> ><label>Admin SMS Notification</label>
            <span class="expand_btn"></span>
        </div>
        <div id="accordion_wc_admin_notification" class="cvt-accordion-body-content" style="display: none;">
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row" style="width:155px;">
                        <label for="wpcf7softeria-alert-settings[phoneno]"><?php esc_html_e('Admin Mobile Number:', 'soft-sms-alerts'); ?></label>
                    </th>
                    <td data-parent_id="wpcf7softeria-alert-settings[admin_notification]">
                        <input type="text" id="wpcf7softeria-alert-settings[phoneno]" name="wpcf7softeria-alert-settings[phoneno]" class="wide" size="70" value="<?php echo esc_attr($data['phoneno']); ?>"><span class="tooltip" data-title="<?php esc_html_e('Admin sms notifications will be sent to this number.', 'soft-sms-alerts'); ?>"><span class="dashicons dashicons-info"></span></span>
                    </td>
                </tr>
                <tr valign="top">
                    <td colspan="2">
                       <div class="softeria_alerts_tokens">
                        <?php
                        foreach ( $form_fields as $form_field ) {
                                           $field = json_decode(wp_json_encode($form_field), true);
                            if ('' !== $field['name'] ) {
                                echo  "<a href='#' data-val='[" . esc_attr($field['name']) . "]'>".esc_attr(ucwords(str_replace('-', ' ', $field['name'])))."</a> | ";
                            }
                        }
                        ?>
                        </div>
                        <textarea id="admin_wpcf7-mail-body" name="wpcf7softeria-alert-settings[text]" data-parent_id="wpcf7softeria-alert-settings[admin_notification]" pre_modified_txt="<?php echo esc_textarea($admin_message); ?>" style="width: 100%;" class="token-area"><?php echo esc_textarea($admin_message); ?></textarea>
                        <div id="menu_cf7_admin" class="sa-menu-token" role="listbox"></div>
                    </td>
                </tr>
            </tbody></table>
        </div> 
        <div style="padding: 5px 10px 10px 10px;">    
            <table class="form-table">
                <tr>
                    <td scope="row" class="td-heading">
                        <label for="wpcf7-mail-body"><?php esc_html_e('Visitor Mobile:', 'soft-sms-alerts'); ?></label>
                    </td>
                    <td>
                        <select name="wpcf7softeria-alert-settings[visitorNumber]" id="visitorNumber">
                        <option value=""><?php esc_attr_e("--select phone field--", "soft-sms-alerts");?></option>
                        <?php
                        if (! empty($form_fields) ) {
                            foreach ( $form_fields as $form_field ) {
                                $field = json_decode(wp_json_encode($form_field), true);
                                if ('' !== $field['name'] ) {
                                    ?>
                            
                            
                            <option value="<?php echo '[' . esc_attr($field['name']) . ']'; ?>" <?php echo ( '[' . $field['name'] . ']' === $visitor_no ) ? 'selected="selected"' : ''; ?>><?php echo esc_attr($field['name']); ?></option>
                                                   <?php
                                }
                            }
                        }
                        ?>
                        </select>
                        <span class="tooltip" data-title="<?php esc_html_e('Select phone field.', 'soft-sms-alerts'); ?>"><span class="dashicons dashicons-info"></span></span>
                    </td>
                </tr>
                 <tr class="top-border">
                <?php
                $auto_sync = ( isset($data['auto_sync']) ) ? $data['auto_sync'] : "off";
                ?>
                    <td scope="row" class="SofteriaAlerts_box td-heading">
                      <input type="checkbox" name="wpcf7softeria-alert-settings[auto_sync]" id="wpcf7softeria-alert-settings[auto_sync]" class="SofteriaAlerts_box sync_group" <?php echo ( ( 'on' === $auto_sync ) ? "checked='checked'" : '' ); ?> />
                        <label for="wpcf7-mail-body"><?php esc_html_e('Sync Data To Group:', 'soft-sms-alerts'); ?></label>
                    </td>
                    <td>
                        <select name="wpcf7softeria-alert-settings[softeria_alerts_group]" id="softeria_alerts_group" data-parent_id="wpcf7softeria-alert-settings[auto_sync]">
                        <?php
                        $groups = SmsAlertcURLOTP::groupList();
                        if (! is_array($groups['data']) ) {
                            ?>
                            <option value=""><?php esc_html_e('SELECT', 'soft-sms-alerts'); ?></option>
                            <?php
                        } else {
                            foreach ( $groups['data'] as $group ) {
                                $softeria_alerts_grp = ( ! empty($data['softeria_alerts_group']) ) ? $data['softeria_alerts_group'] : "";
                                
                                ?>
                            <option value="<?php echo esc_attr($group['name']); ?>" <?php echo ( $softeria_alerts_grp === $group['name'] ) ? 'selected="selected"' : ''; ?>><?php echo esc_attr($group['name']); ?></option>
                                <?php
                            }
                        }
                        ?>
                        </select>
                        <span class="tooltip" data-title="<?php esc_html_e('Select group in which data will be synced.', 'soft-sms-alerts'); ?>"><span class="dashicons dashicons-info"></span></span>
                        <?php
                        if (! empty($groups) && ( ! is_array($groups['data'])) ) {
                            ?>
                            <a href="#" onclick="create_group(this);" id="create_group" style="text-decoration: none;"><?php esc_html_e('Create Group', 'soft-sms-alerts'); ?></a>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td scope="row" class="td-heading">
                        <label for="wpcf7-mail-body"><?php esc_html_e('Name Field:', 'soft-sms-alerts'); ?></label>
                    </td>
                    <td>
                        <select name="wpcf7softeria-alert-settings[softeria_alerts_name]" id="softeria_alerts_name" data-parent_id="wpcf7softeria-alert-settings[auto_sync]">
                        <?php
                        $username = softeria_alerts_get_option('softeria_alerts_name', 'softeria_alerts_gateway');
                        $password = softeria_alerts_get_option('softeria_alerts_password', 'softeria_alerts_gateway');

                        $wpcf7        = WPCF7_ContactForm::get_current();
                        $contact_form = WPCF7_ContactForm::get_instance($wpcf7->id());
                        $form_fields  = $contact_form->scan_form_tags();
                        if (! empty($form_fields) ) {
                            foreach ( $form_fields as $form_field ) {
                                $field = json_decode(wp_json_encode($form_field), true);
                                if ('' !== $field['name'] ) {
                                
                                    $softeria_alerts_name = ( ! empty($data['softeria_alerts_name']) ) ? $data['softeria_alerts_name'] : "";
                                    ?>
                            <option value="<?php echo '[' . esc_attr($field['name']) . ']'; ?>" <?php echo ( '[' . $field['name'] . ']' === $softeria_alerts_name ) ? 'selected="selected"' : ''; ?>><?php echo esc_attr($field['name']); ?></option>
                                                   <?php
                                }
                            }
                        }
                        ?>
                        <input type="hidden" name="softeria_alerts_gateway[softeria_alerts_name]" id="softeria_alerts_gateway[softeria_alerts_name]" value="<?php echo esc_attr($username); ?>" data-id="softeria_alerts_name" class="hidden">
                        <input type="hidden" name="softeria_alerts_gateway[softeria_alerts_password]" id="softeria_alerts_gateway[softeria_alerts_password]" value="<?php echo esc_attr($password); ?>" data-id="softeria_alerts_password" class="hidden">
                        </select>
                        <span class="tooltip" data-title="<?php esc_html_e('Select name field.', 'soft-sms-alerts'); ?>"><span class="dashicons dashicons-info"></span></span>
                    </td>
                </tr>
                
                 <tr class="top-border">
                    <td scope="row" class="td-heading">
                        <label for="wpcf7-mail-body"></label>
                    </td>
                    <td></td>
                </td>
                
                </tr>
                <tr>
            </table>
        </div>        
    </div>
    </div>
    </section>                                
    </div>
    </div>
    <style>
    .top-border {border-top: 1px dashed #b4b9be;}
    #softeria_alerts_settings select{max-width: 200px;}
    </style>
<script>
var adminnumber = "<?php echo esc_attr($data['phoneno']); ?>";
var tagInput1     = new TagsInput({
    selector: 'wpcf7softeria-alert-settings[phoneno]',
    duplicate : false,
    max : 10,
});
var number = (adminnumber!='') ? adminnumber.split(",") : [];
if(number.length > 0){
    tagInput1.addData(number);
}    
</script>
<?php } ?>
