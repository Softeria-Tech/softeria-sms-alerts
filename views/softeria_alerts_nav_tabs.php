<header class="header">
    <input class="menu-btn" type="checkbox" id="menu-btn" />
    <label class="menu-icon" for="menu-btn"><span class="navicon"></span></label>
    <a href="" class="logo">Settings</a>
    <ul class="menu">
        <li tab_type="logo" onclick="return false;" class="hidemb">
            <img src="<?php echo esc_url(SA_MOV_URL); ?>images/sms.softeriatech.com.png" width="150px;" />
        </li>
        <li tab_type="global" onclick="SofteriaAlerts_change_nav(this, 'SofteriaAlerts_nav_global_box')" class="SofteriaAlerts_active">
            <a href="#general"><span class="dashicons-before dashicons-admin-generic"></span> <?php esc_html_e('API KEYS', 'soft-sms-alerts'); ?> </a>
        </li>
        <?php
        $tabs = apply_filters('softsmal_addTabs', array());
        foreach ( $tabs as $tab ) {
            if (array_key_exists('inner_nav', $tab) ) {
                if (! empty($tab['nav']) ) {
                    ?>
        <li tab_type="<?php echo esc_attr(strtolower(str_replace(' ', '_', $tab['nav']))); ?>" onclick="SofteriaAlerts_change_nav(this, 'SofteriaAlerts_nav_<?php echo esc_attr(strtolower(str_replace(' ', '_', $tab['nav']))); ?>_box')" >
            <a href="#<?php echo esc_attr(strtolower(str_replace(' ', '_', $tab['nav']))); ?>"><span class="dashicons <?php echo esc_attr($tab['icon']); ?>"></span> <?php echo esc_attr($tab['nav']); ?></a>
        </li>
                    <?php
                }
            } else {
                ?>
        <li tab_type="<?php echo esc_attr($tab['tab_section']); ?>" onclick="SofteriaAlerts_change_nav(this, 'SofteriaAlerts_nav_<?php echo esc_attr($tab['tab_section']); ?>_box')" >
            <a href="#<?php echo esc_attr($tab['tab_section']); ?>"><span class="dashicons <?php echo esc_attr($tab['icon']); ?>"></span><?php esc_attr($tab['title']); ?></a>
        </li>
                <?php
            }
        }
        ?>
        <li tab_type="otpsection" onclick="SofteriaAlerts_change_nav(this, 'SofteriaAlerts_nav_otp_section_box')" >
            <a href="#otpsection"><span class="dashicons dashicons-admin-tools"></span> <?php esc_html_e('OTP Codes', 'soft-sms-alerts'); ?></a>
        </li>
        <li tab_type="credits" onclick="SofteriaAlerts_change_nav(this, 'SofteriaAlerts_nav_credits_box')" class="<?php echo esc_attr($credit_show); ?>">
            <a href="#credits"><span class="dashicons-before dashicons-admin-comments"></span> <?php esc_html_e('SMS Balance', 'soft-sms-alerts'); ?></a>
        </li>
        <li tab_type="support" onclick="SofteriaAlerts_change_nav(this, 'SofteriaAlerts_nav_support_box')" >
            <a href="#support"><span class="dashicons-before dashicons-editor-help"></span> <?php esc_html_e('Contact us', 'soft-sms-alerts'); ?></a>
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
