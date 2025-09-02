<?php
/**
 * SOFTSMSAlerts Widgets helper.
 *
 * PHP version 5
 *
 * @category HELPER
 * @package  SOFTSMSAlerts
 * @author   Softeria Tech <billing@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 */

/**
 * PHP version 5
 *
 * @category HELPER
 * @package  SOFTSMSAlerts
 * @author   Softeria Tech <billing@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 * SOFTSMAL_Widgets
 */
class SOFTSMAL_Widgets extends \WP_Widget
{
    /**
     * Construct function
     *
     * @return array
     */
    function __construct()
    {
        parent::__construct(
            'softeria_alerts_widget',
            esc_html__('SOFTSMSAlerts', 'soft-sms-alerts'),
            array('description' => esc_html__('Add softsmsalerts form', 'soft-sms-alerts'),)
        );
    }

    /**
     * Widget
     *
     * @param array $args     args.
     * @param array $instance instance.
     *
     * @return array
     */
    public function widget($args, $instance)
    {
        $selectedForm = empty($instance['sa_shortcode']) ? '' : $instance['sa_shortcode'];
        if (!$selectedForm) {
            return;
        }
        echo isset($args['before_widget'])?$args['before_widget']:'';
        if ($selectedForm != '') {
            echo ($selectedForm==1)?do_shortcode("[sa_signupwithmobile]"):(($selectedForm==2)?do_shortcode("[sa_loginwithotp]"):do_shortcode("[sa_sharecart]"));
        }
        echo isset($args['after_widget'])?$args['after_widget']:'';

    }

    /**
     * Form
     *
     * @param array $instance instance.
     *
     * @return array
     */
       public function form($instance)
    {
        $selectedForm = empty($instance['sa_shortcode']) ? '' : $instance['sa_shortcode'];
        $forms = array(''=>'Select Form','1'=>'Signup With Mobile','2'=>'Login With Otp','3'=>'Share Cart Button');
        ?>
        
        <label for="<?php echo $this->get_field_id('sa_shortcode'); ?>">Form:
            <select style="margin-bottom: 12px;" class='widefat' id="<?php echo $this->get_field_id('sa_shortcode'); ?>"
                    name="<?php echo $this->get_field_name('sa_shortcode'); ?>" type="text"
            >
                <?php
                foreach ($forms as $key=>$item) {
                    ?>
                    <option <?php if ($key == $selectedForm) {
                        echo 'selected';
    } ?> value='<?php echo $key; ?>'>
                        <?php echo $item; ?>
                    </option>
                    <?php
                }
                ?>
            </select>
        </label>
            <?php
    } 

    /**
     * Update
     *
     * @param array $new_instance new_instance.
     * @param array $old_instance old_instance.
     *
     * @return array
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['sa_shortcode'] = intval($new_instance['sa_shortcode']);
        return $instance;
    }
}

/**
 * Smsalert register widgets
 *
 * @return array
 */
function softeria_alerts_register_widgets()
{
    register_widget('SOFTSMAL_Widgets');
}

add_action('widgets_init', 'softeria_alerts_register_widgets');
