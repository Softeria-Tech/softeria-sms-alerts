<div class="softeria_alerts_wrapper cvt-accordion <?php echo $disablePlayground; ?>" style="padding: 5px 10px 10px 10px;">
    <strong><?php echo wp_kses_post($softeria_alerts_helper); ?></strong>
    <table class="form-table">
        
        <tr valign="top">
            <th scope="row"><?php esc_html_e('API KEY', 'soft-sms-alerts'); ?>
                <span class="tooltip" data-title="Enter SOFTSMSAlerts APIKEY"><span class="dashicons dashicons-info"></span></span>
            </th>
            <td>
                <?php
                if ($islogged ) {
                    echo '*****';
                }
                ?>
                <input type="text" name="softeria_alerts_gateway[softeria_alerts_password]" id="softeria_alerts_gateway[softeria_alerts_password]" value="<?php echo esc_attr($softeria_alerts_password); ?>" data-id="softeria_alerts_password" class="<?php echo esc_attr($hidden); ?>">
                <span class="<?php echo esc_attr($hidden); ?>"><?php esc_html_e('Your Softeria Tech API KEY', 'soft-sms-alerts'); ?></span>
            </td>
        </tr>
        <?php do_action('verify_senderid_button'); ?>
        <tr valign="top">
            <th scope="row">
                <?php esc_html_e('Sender Id', 'soft-sms-alerts'); ?>
                <span class="tooltip" data-title="Only available for transactional route"><span class="dashicons dashicons-info"></span></span>
            </th>
            <td>
                <?php if ($islogged ) { ?>
                    <?php echo esc_attr($softeria_alerts_api); ?>
                    <input type="hidden" value="<?php echo esc_attr($softeria_alerts_api); ?>" name="softeria_alerts_gateway[softeria_alerts_api]" id="softeria_alerts_gateway[softeria_alerts_api]">
                <?php } else { ?>
                <select parent_accordian="general" name="softeria_alerts_gateway[softeria_alerts_api]" id="softeria_alerts_gateway[softeria_alerts_api]" disabled>
                    <option value="SELECT"><?php esc_html_e('SELECT', 'soft-sms-alerts'); ?></option>
                </select>
                <span class="<?php echo esc_attr($hidden); ?>"><?php esc_html_e('display name for SMS\'s to be sent', 'soft-sms-alerts'); ?></span>
                <?php } ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
            </th>
            <td>
                <?php if ($islogged ) { ?>
                <a href="#" class="button-primary" onclick="logout(); return false;"><?php esc_html_e('Logout', 'soft-sms-alerts'); ?></a>
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
            <th scope="row"><?php esc_html_e('Notify Admin Through', 'soft-sms-alerts'); ?>
                <span class="tooltip" data-title="Please make sure that the number must be without country code (e.g.: 8010551055)"><span class="dashicons dashicons-info"></span></span>
            </th>
            <td>
                <select id="send_admin_sms_to" onchange="toggle_send_admin_alert(this);">
                    <option value=""><?php esc_html_e('Custom', 'soft-sms-alerts'); ?></option>
                    <option value="post_author" <?php echo ( trim($sms_admin_phone) === 'post_author' ) ? 'selected="selected"' : ''; ?>><?php esc_html_e('Post Author', 'soft-sms-alerts'); ?></option>
                    <?php if (is_plugin_active('woocommerce-shipping-local-pickup-plus/woocommerce-shipping-local-pickup-plus.php') ) { ?>
                    <option value="store_manager" <?php echo ( trim($sms_admin_phone) === 'store_manager' ) ? 'selected="selected"' : ''; ?>><?php esc_html_e('Store Manager', 'soft-sms-alerts'); ?></option>
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
                <input type="text" name="softeria_alerts_message[sms_admin_phone]" class="admin_no" id="softeria_alerts_message[sms_admin_phone]" <?php echo ( trim($sms_admin_phone) === 'post_author' ) ? 'readonly="readonly"' : ''; ?> value="<?php echo esc_attr($sms_admin_phone); ?>"><br /><br />
            </td>
        </tr>
    </table>
</div>
<?php  }?>
