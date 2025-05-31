<?php
/**
 * SMSPro Widgets helper.
 *
 * PHP version 5
 *
 * @category HELPER
 * @package  SMSPro
 * @author   SMS Pro <support@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 */

/**
 * PHP version 5
 *
 * @category HELPER
 * @package  SMSPro
 * @author   SMS Pro <support@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 * SMSProWidgets
 */
class SMSProWidgets extends \WP_Widget
{
    /**
     * Construct function
     *
     * @return array
     */
    function __construct()
    {
        parent::__construct(
            'smspro_widget',
            esc_html__('SMSPro', 'sms-pro'),
            array('description' => esc_html__('Add smspro form', 'sms-pro'),)
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
function smspro_register_widgets()
{
    register_widget('SMSProWidgets');
}

add_action('widgets_init', 'smspro_register_widgets');
