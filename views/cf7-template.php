<?php
/**
 * Cf7 template.
 * PHP version 5
 *
 * @category View
 * @package  SMSPro
 * @author   SMS Pro <support@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 */
$wpcf7 = WPCF7_ContactForm::get_current();
if (empty($wpcf7->id()) ) {
    echo '<h3>';
    esc_html_e('Please save your contact form 7 once.', 'sms-pro');
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
<section id="smspro_settings">
<div class="cvt-accordion">
    <div class="accordion-section">
        <div class="cvt-accordion-body-title" data-href="#accordion_wc_visitor_notification">
            <input type="checkbox" name="wpcf7smspro-settings[visitor_notification]" id="wpcf7smspro-settings[visitor_notification]" class="notify_box" <?php echo ( ( 'on' === $visitor_msg_enable ) ? "checked='checked'" : '' ); ?> ><label>Visitor SMS Notification</label>
            <span class="expand_btn"></span>
        </div>
        <div id="accordion_wc_visitor_notification" class="cvt-accordion-body-content" style="display: none;">
            <table class="form-table">
                <tbody><tr valign="top">
                    <td>
                       <div class="smspro_tokens">
                        <?php
                        foreach ( $form_fields as $form_field ) {
                                           $field = json_decode(wp_json_encode($form_field), true);
                            if ('' !== $field['name'] ) {
                                echo  "<a href='#' data-val='[" . esc_attr($field['name']) . "]'>".esc_attr(ucwords(str_replace('-', ' ', $field['name'])))."</a> | ";
                            }
                        }
                        ?>
                        </div>
                        <textarea id="visitor_wpcf7-mail-body" name="wpcf7smspro-settings[visitorMessage]" data-parent_id="wpcf7smspro-settings[visitor_notification]" pre_modified_txt="<?php echo esc_textarea($visitor_msg); ?>" style="width: 100%;" class="token-area"><?php echo esc_textarea($visitor_msg); ?></textarea>
                        <div id="menu_cf7_cust" class="sa-menu-token" role="listbox"></div>
                    </td>
                </tr>
            </tbody></table>
        </div>
        <div class="cvt-accordion-body-title" data-href="#accordion_wc_admin_notification">
            <input type="checkbox" name="wpcf7smspro-settings[admin_notification]" id="wpcf7smspro-settings[admin_notification]" class="notify_box" <?php echo ( ( 'on' === $admin_msg_enable ) ? "checked='checked'" : '' ); ?> ><label>Admin SMS Notification</label>
            <span class="expand_btn"></span>
        </div>
        <div id="accordion_wc_admin_notification" class="cvt-accordion-body-content" style="display: none;">
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row" style="width:155px;">
                        <label for="wpcf7smspro-settings[phoneno]"><?php esc_html_e('Admin Mobile Number:', 'sms-pro'); ?></label>
                    </th>
                    <td data-parent_id="wpcf7smspro-settings[admin_notification]">
                        <input type="text" id="wpcf7smspro-settings[phoneno]" name="wpcf7smspro-settings[phoneno]" class="wide" size="70" value="<?php echo esc_attr($data['phoneno']); ?>"><span class="tooltip" data-title="<?php esc_html_e('Admin sms notifications will be sent to this number.', 'sms-pro'); ?>"><span class="dashicons dashicons-info"></span></span>
                    </td>
                </tr>
                <tr valign="top">
                    <td colspan="2">
                       <div class="smspro_tokens">
                        <?php
                        foreach ( $form_fields as $form_field ) {
                                           $field = json_decode(wp_json_encode($form_field), true);
                            if ('' !== $field['name'] ) {
                                echo  "<a href='#' data-val='[" . esc_attr($field['name']) . "]'>".esc_attr(ucwords(str_replace('-', ' ', $field['name'])))."</a> | ";
                            }
                        }
                        ?>
                        </div>
                        <textarea id="admin_wpcf7-mail-body" name="wpcf7smspro-settings[text]" data-parent_id="wpcf7smspro-settings[admin_notification]" pre_modified_txt="<?php echo esc_textarea($admin_message); ?>" style="width: 100%;" class="token-area"><?php echo esc_textarea($admin_message); ?></textarea>
                        <div id="menu_cf7_admin" class="sa-menu-token" role="listbox"></div>
                    </td>
                </tr>
            </tbody></table>
        </div> 
        <div style="padding: 5px 10px 10px 10px;">    
            <table class="form-table">
                <tr>
                    <td scope="row" class="td-heading">
                        <label for="wpcf7-mail-body"><?php esc_html_e('Visitor Mobile:', 'sms-pro'); ?></label>
                    </td>
                    <td>
                        <select name="wpcf7smspro-settings[visitorNumber]" id="visitorNumber">
                        <option value=""><?php esc_attr_e("--select phone field--", "sms-pro");?></option>
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
                        <span class="tooltip" data-title="<?php esc_html_e('Select phone field.', 'sms-pro'); ?>"><span class="dashicons dashicons-info"></span></span>
                    </td>
                </tr>
                 <tr class="top-border">
                <?php
                $auto_sync = ( isset($data['auto_sync']) ) ? $data['auto_sync'] : "off";
                ?>
                    <td scope="row" class="SMSPro_box td-heading">
                      <input type="checkbox" name="wpcf7smspro-settings[auto_sync]" id="wpcf7smspro-settings[auto_sync]" class="SMSPro_box sync_group" <?php echo ( ( 'on' === $auto_sync ) ? "checked='checked'" : '' ); ?> />
                        <label for="wpcf7-mail-body"><?php esc_html_e('Sync Data To Group:', 'sms-pro'); ?></label>
                    </td>
                    <td>
                        <select name="wpcf7smspro-settings[smspro_group]" id="smspro_group" data-parent_id="wpcf7smspro-settings[auto_sync]">
                        <?php
                        $groups = SmsAlertcURLOTP::groupList();
                        if (! is_array($groups['data']) ) {
                            ?>
                            <option value=""><?php esc_html_e('SELECT', 'sms-pro'); ?></option>
                            <?php
                        } else {
                            foreach ( $groups['data'] as $group ) {
                                $smspro_grp = ( ! empty($data['smspro_group']) ) ? $data['smspro_group'] : "";
                                
                                ?>
                            <option value="<?php echo esc_attr($group['name']); ?>" <?php echo ( $smspro_grp === $group['name'] ) ? 'selected="selected"' : ''; ?>><?php echo esc_attr($group['name']); ?></option>
                                <?php
                            }
                        }
                        ?>
                        </select>
                        <span class="tooltip" data-title="<?php esc_html_e('Select group in which data will be synced.', 'sms-pro'); ?>"><span class="dashicons dashicons-info"></span></span>
                        <?php
                        if (! empty($groups) && ( ! is_array($groups['data'])) ) {
                            ?>
                            <a href="#" onclick="create_group(this);" id="create_group" style="text-decoration: none;"><?php esc_html_e('Create Group', 'sms-pro'); ?></a>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td scope="row" class="td-heading">
                        <label for="wpcf7-mail-body"><?php esc_html_e('Name Field:', 'sms-pro'); ?></label>
                    </td>
                    <td>
                        <select name="wpcf7smspro-settings[smspro_name]" id="smspro_name" data-parent_id="wpcf7smspro-settings[auto_sync]">
                        <?php
                        $username = smspro_get_option('smspro_name', 'smspro_gateway');
                        $password = smspro_get_option('smspro_password', 'smspro_gateway');

                        $wpcf7        = WPCF7_ContactForm::get_current();
                        $contact_form = WPCF7_ContactForm::get_instance($wpcf7->id());
                        $form_fields  = $contact_form->scan_form_tags();
                        if (! empty($form_fields) ) {
                            foreach ( $form_fields as $form_field ) {
                                $field = json_decode(wp_json_encode($form_field), true);
                                if ('' !== $field['name'] ) {
                                
                                    $smspro_name = ( ! empty($data['smspro_name']) ) ? $data['smspro_name'] : "";
                                    ?>
                            <option value="<?php echo '[' . esc_attr($field['name']) . ']'; ?>" <?php echo ( '[' . $field['name'] . ']' === $smspro_name ) ? 'selected="selected"' : ''; ?>><?php echo esc_attr($field['name']); ?></option>
                                                   <?php
                                }
                            }
                        }
                        ?>
                        <input type="hidden" name="smspro_gateway[smspro_name]" id="smspro_gateway[smspro_name]" value="<?php echo esc_attr($username); ?>" data-id="smspro_name" class="hidden">
                        <input type="hidden" name="smspro_gateway[smspro_password]" id="smspro_gateway[smspro_password]" value="<?php echo esc_attr($password); ?>" data-id="smspro_password" class="hidden">
                        </select>
                        <span class="tooltip" data-title="<?php esc_html_e('Select name field.', 'sms-pro'); ?>"><span class="dashicons dashicons-info"></span></span>
                    </td>
                </tr>
                
                 <tr class="top-border">
                    <td scope="row" class="td-heading">
                        <label for="wpcf7-mail-body"></label>
                    </td>
                    <td>
                        <a href="https://www.youtube.com/watch?v=FFslKn_Stmc" target="_blank" class="btn-outline"><span class="dashicons dashicons-video-alt3" style="font-size: 21px"></span>  Youtube</a>

                        <a href="https://sms.softeriatech.com/knowledgebase/integrate-otp-verification-with-contactform7/" target="_blank" class="btn-outline"><span class="dashicons dashicons-format-aside"></span> Documentation</a>
                    </td>
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
    #smspro_settings select{max-width: 200px;}
    </style>
<script>
var adminnumber = "<?php echo esc_attr($data['phoneno']); ?>";
var tagInput1     = new TagsInput({
    selector: 'wpcf7smspro-settings[phoneno]',
    duplicate : false,
    max : 10,
});
var number = (adminnumber!='') ? adminnumber.split(",") : [];
if(number.length > 0){
    tagInput1.addData(number);
}    
</script>
<?php } ?>
