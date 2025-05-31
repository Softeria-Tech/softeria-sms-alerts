<div class="smspro_wrapper cvt-accordion <?php echo $disablePlayground; ?>" style="padding: 5px 10px 10px 10px;">
    <strong><?php echo wp_kses_post($smspro_helper); ?></strong>
    <table class="form-table">
        
        <tr valign="top">
            <th scope="row"><?php esc_html_e('SMS PRO API KEY', 'sms-pro'); ?>
                <span class="tooltip" data-title="Enter SMSPro APIKEY"><span class="dashicons dashicons-info"></span></span>
            </th>
            <td>
                <?php
                if ($islogged ) {
                    echo '*****';
                }
                ?>
                <input type="text" name="smspro_gateway[smspro_password]" id="smspro_gateway[smspro_password]" value="<?php echo esc_attr($smspro_password); ?>" data-id="smspro_password" class="<?php echo esc_attr($hidden); ?>">
                <span class="<?php echo esc_attr($hidden); ?>"><?php esc_html_e('Your SMS Pro API KEY', 'sms-pro'); ?></span>
            </td>
        </tr>
        <?php do_action('verify_senderid_button'); ?>
        <tr valign="top">
            <th scope="row">
                <?php esc_html_e('SMS PRO Sender Id', 'sms-pro'); ?>
                <span class="tooltip" data-title="Only available for transactional route"><span class="dashicons dashicons-info"></span></span>
            </th>
            <td>
                <?php if ($islogged ) { ?>
                    <?php echo esc_attr($smspro_api); ?>
                    <input type="hidden" value="<?php echo esc_attr($smspro_api); ?>" name="smspro_gateway[smspro_api]" id="smspro_gateway[smspro_api]">
                <?php } else { ?>
                <select parent_accordian="general" name="smspro_gateway[smspro_api]" id="smspro_gateway[smspro_api]" disabled>
                    <option value="SELECT"><?php esc_html_e('SELECT', 'sms-pro'); ?></option>
                </select>
                <span class="<?php echo esc_attr($hidden); ?>"><?php esc_html_e('display name for SMS\'s to be sent', 'sms-pro'); ?></span>
                <?php } ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
            </th>
            <td>
                <?php if ($islogged ) { ?>
                <a href="#" class="button-primary" onclick="logout(); return false;"><?php esc_html_e('Logout', 'sms-pro'); ?></a>
                <?php } ?>
            </td>
        </tr>
    </table>
</div>
<br>
<?php if ($islogged ) { ?>
<div class="cvt-accordion" style="padding: 0px 10px 10px 10px;">
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Send Admin SMS To', 'sms-pro'); ?>
                <span class="tooltip" data-title="Please make sure that the number must be without country code (e.g.: 8010551055)"><span class="dashicons dashicons-info"></span></span>
            </th>
            <td>
                <select id="send_admin_sms_to" onchange="toggle_send_admin_alert(this);">
                    <option value=""><?php esc_html_e('Custom', 'sms-pro'); ?></option>
                    <option value="post_author" <?php echo ( trim($sms_admin_phone) === 'post_author' ) ? 'selected="selected"' : ''; ?>><?php esc_html_e('Post Author', 'sms-pro'); ?></option>
                    <?php if (is_plugin_active('woocommerce-shipping-local-pickup-plus/woocommerce-shipping-local-pickup-plus.php') ) { ?>
                    <option value="store_manager" <?php echo ( trim($sms_admin_phone) === 'store_manager' ) ? 'selected="selected"' : ''; ?>><?php esc_html_e('Store Manager', 'sms-pro'); ?></option>
                    <?php } ?>
                </select>
                <script>
                function toggle_send_admin_alert(obj)
                {
                    if(obj.value == "post_author")
                    {
                        tagInput1.addTag(obj.value);
                    }
                    if(obj.value == "store_manager")
                    {
                        tagInput1.addTag(obj.value);
                    }
                }
                </script>
                <input type="text" name="smspro_message[sms_admin_phone]" class="admin_no" id="smspro_message[sms_admin_phone]" <?php echo ( trim($sms_admin_phone) === 'post_author' ) ? 'readonly="readonly"' : ''; ?> value="<?php echo esc_attr($sms_admin_phone); ?>"><br /><br />
                <span><?php esc_html_e('Admin order sms notifications will be sent to this number.', 'sms-pro'); ?></span>
            </td>
        </tr>
    </table>
</div>
<?php  }?>
