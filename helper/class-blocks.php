<?php
/**
 * Shortcode helper.
 *
 * PHP version 5
 *
 * @category Handler
 * @package  SMSPro
 * @author   SMS Pro <support@softeriatech.com>
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
 * @package  SMSPro
 * @author   SMS Pro <support@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 * SABlocks class
 */
class SABlocks
{

    /**
     * Blocks constructor.
     */
    public function __construct()
    {
        add_action('init', array( &$this, 'block_editor_render' ));
    }

    /**
     * Register SMS Pro Blocks.
     *
     * @uses register_block_type_from_metadata()
     *
     * @return string
     */
    public function block_editor_render()
    {
            
        $blocks = array(
        'sms-pro/sa-loginwithotp'     => array(
        'render_callback' => array( $this, 'sa_loginwithotp_render' ),
        ),
        'sms-pro/sa-signupwithmobile'     => array(
        'render_callback' => array( $this, 'sa_signupwithmobile_render' ),
        ),
        'sms-pro/sa-sharecart'     => array(
        'render_callback' => array( $this, 'sa_sharecart_render' ),
        )
        );

        foreach ( $blocks as $k => $block_data ) {
            $block_type = str_replace('sms-pro/', '', $k);
            register_block_type_from_metadata(SA_MOV_DIR . 'blocks/' . $block_type, $block_data);
        }
    }

    /**
     * Renders SMS Pro Login With OTP form block.
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
     * Renders SMS Pro Share Cart block.
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
     * Renders SMS Pro Signup With Mobile form block.
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
new SABlocks();
?>
