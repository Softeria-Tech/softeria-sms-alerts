<?php


if (! defined('ABSPATH') ) {
    exit;
}

class SOFTSMAL_Backend
{

    /**
     * Construct function.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('admin_notices', array( $this, 'smsproReview' ), 10);
        $this->routeData();
    }

    /**
     * Called from constructor.
     *
     * @return void
     */
    public function routeData()
    {
        if (! array_key_exists('option', $_GET) ) {
            return;
        }
        switch ( trim(sanitize_text_field(wp_unslash($_GET['option']))) ) {
        case 'not-show-again':
            add_option('softeria_alerts_review_not_show_again', 0);
            break;
        case 'remind-later':
            $softeria_alerts_admin_notice_user_meta = array(
            'date-dismissed' => date('Y-m-d'),
            );
            update_user_meta(get_current_user_id(), 'softeria_alerts_review_remind_later', $softeria_alerts_admin_notice_user_meta);
            break;
        }
    }

    /**
     * Request for review.
     *
     * @return void
     */
    public function smsproReview()
    {
        $current_date = date('Y-m-d');
        $date         = get_option('softeria_alerts_activation_date', date('Y-m-d'));
        $show_date    = date('Y-m-d', strtotime('+1 month', strtotime($date)));
        $show         = get_option('softeria_alerts_review_not_show_again', 1);
        $user_meta    = get_user_meta(get_current_user_id(), 'softeria_alerts_review_remind_later');
        $remind       = 0;
        if (isset($user_meta[0]['date-dismissed']) ) {
            $date_1 = $user_meta[0]['date-dismissed'];
            $date_2 = date('Y-m-d', strtotime('+7 days', strtotime($date_1)));

            if ($current_date > $date_2 ) {
                $remind = 0;
            } else {
                $remind = 1;
            }
        }
        if ('1' === $show && '0' === $remind && $current_date > $show_date ) {
            $current_user = wp_get_current_user();
            ?>
            <?php
            echo '
			<script>
				jQuery(".softeria-alert-review").unbind("click").bind("click", function() {
					var type = jQuery(this).attr("option");
					var action_url = "' . esc_url(site_url()) . '/?option="+type;
					jQuery.ajax({
						url:action_url,
						type:"GET",
						crossDomain:!0,
						success:function(o){
							location.reload();
						}
					});
				});
			</script>';
        }
    }
}
new SOFTSMAL_Backend();
?>
