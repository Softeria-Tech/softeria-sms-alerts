<?php
/**
 * Shortcode helper.
 *
 * PHP version 5
 *
 * @category Handler
 * @package  SOFTSMSAlerts
 * @author   Softeria Tech <billing@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 */

if (! defined('ABSPATH') ) {
    exit;
}
    
/**
 * PHP version 5
 *
 * @category Handler
 * @package  SOFTSMSAlerts
 * @author   Softeria Tech <billing@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 * SOFTSMAL_blocks class
 */
class SOFTSMAL_blocks
{

    /**
     * Blocks constructor.
     */
    public function __construct()
    {
        add_action('init', array( &$this, 'block_editor_render' ));
    }

    /**
     * Register Softeria Tech Blocks.
     *
     * @uses register_block_type_from_metadata()
     *
     * @return string
     */
    public function block_editor_render()
    {
            
        $blocks = array(
        'soft-sms-alerts/softsmal-loginwithotp'     => array(
        'render_callback' => array( $this, 'sa_loginwithotp_render' ),
        ),
        'soft-sms-alerts/softsmal-signupwithotp'     => array(
        'render_callback' => array( $this, 'sa_signupwithmobile_render' ),
        ),
        'soft-sms-alerts/softsmal-share'     => array(
        'render_callback' => array( $this, 'sa_sharecart_render' ),
        )
        );

        foreach ( $blocks as $k => $block_data ) {
            $block_type = str_replace('soft-sms-alerts/', '', $k);
            register_block_type_from_metadata(SA_MOV_DIR . 'blocks/' . $block_type, $block_data);
        }
    }

    /**
     * Renders Softeria Tech Login With OTP form block.
     *
     * @return string
     *
     * @uses apply_shortcodes()
     */
    public function sa_loginwithotp_render()
    {
        $shortcode = '[sa_loginwithotp]';

        return apply_shortcodes($shortcode);
    }
        
    /**
     * Renders Softeria Tech Share Cart block.
     *
     * @return string
     *
     * @uses apply_shortcodes()
     */
    public function sa_sharecart_render()
    {
        $shortcode = '[sa_sharecart]';

        return apply_shortcodes($shortcode);
    }
        
    /**
     * Renders Softeria Tech Signup With Mobile form block.
     *
     * @return string
     *
     * @uses apply_shortcodes()
     */
    public function sa_signupwithmobile_render()
    {
        $shortcode = '[sa_signupwithmobile]';

        return apply_shortcodes($shortcode);
    }
}
new SOFTSMAL_blocks();
?>
