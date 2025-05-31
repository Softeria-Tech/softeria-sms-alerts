<header class="header">
    <input class="menu-btn" type="checkbox" id="menu-btn" />
    <label class="menu-icon" for="menu-btn"><span class="navicon"></span></label>
    <a href="" class="logo">SMS ALERT</a>
    <ul class="menu">
        <li tab_type="logo" onclick="return false;" class="hidemb">
            <img src="<?php echo esc_url(SA_MOV_URL); ?>images/sms.softeriatech.com.png" width="150px;" />
        </li>
        <li tab_type="global" onclick="SMSPro_change_nav(this, 'SMSPro_nav_global_box')" class="SMSPro_active">
            <a href="#general"><span class="dashicons-before dashicons-admin-generic"></span> <?php esc_html_e('General Settings', 'sms-pro'); ?> </a>
        </li>
        <?php
        $tabs = apply_filters('sa_addTabs', array());
        foreach ( $tabs as $tab ) {
            if (array_key_exists('inner_nav', $tab) ) {
                if (! empty($tab['nav']) ) {
                    ?>
        <li tab_type="<?php echo esc_attr(strtolower(str_replace(' ', '_', $tab['nav']))); ?>" onclick="SMSPro_change_nav(this, 'SMSPro_nav_<?php echo esc_attr(strtolower(str_replace(' ', '_', $tab['nav']))); ?>_box')" >
            <a href="#<?php echo esc_attr(strtolower(str_replace(' ', '_', $tab['nav']))); ?>"><span class="dashicons <?php echo esc_attr($tab['icon']); ?>"></span> <?php echo esc_attr($tab['nav']); ?></a>
        </li>
                    <?php
                }
            } else {
                ?>
        <li tab_type="<?php echo esc_attr($tab['tab_section']); ?>" onclick="SMSPro_change_nav(this, 'SMSPro_nav_<?php echo esc_attr($tab['tab_section']); ?>_box')" >
            <a href="#<?php echo esc_attr($tab['tab_section']); ?>"><span class="dashicons <?php echo esc_attr($tab['icon']); ?>"></span><?php esc_attr($tab['title']); ?></a>
        </li>
                <?php
            }
        }
        ?>
        <li tab_type="otpsection" onclick="SMSPro_change_nav(this, 'SMSPro_nav_otp_section_box')" >
            <a href="#otpsection"><span class="dashicons dashicons-admin-tools"></span> <?php esc_html_e('OTP Settings', 'sms-pro'); ?></a>
        </li>
        <li tab_type="callbacks" onclick="SMSPro_change_nav(this, 'SMSPro_nav_callbacks_box')" >
            <a href="#callbacks"><span class="dashicons-before dashicons-admin-settings"></span> <?php esc_html_e('Advanced Settings', 'sms-pro'); ?></a>
        </li>
        <li tab_type="credits" onclick="SMSPro_change_nav(this, 'SMSPro_nav_credits_box')" class="<?php echo esc_attr($credit_show); ?>">
            <a href="#credits"><span class="dashicons-before dashicons-admin-comments"></span> <?php esc_html_e('SMS Credits', 'sms-pro'); ?></a>
        </li>
        <li tab_type="support" onclick="SMSPro_change_nav(this, 'SMSPro_nav_support_box')" >
            <a href="#support"><span class="dashicons-before dashicons-editor-help"></span> <?php esc_html_e('Support', 'sms-pro'); ?></a>
        </li>
    </ul>
</header>
<script>
jQuery(document).ready(function (jQuery) {
    jQuery(".menu-icon").on("click", function () {
        jQuery(this).toggleClass("active");
    });
    jQuery(".menu").on("click", "li", function () {
        jQuery(".menu-icon").click();
    });
});
</script>
