<?php
/**
 * Emementer Widget helper.
 *
 * PHP version 5
 *
 * @category Handler
 * @package  SOFTSMSAlerts
 * @author   Softeria Tech <billing@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 */

use Helper\ElementorWidget;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Core\Schemes\Color as Scheme_Color;


if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Emementer Widget helper.
 *
 * PHP version 5
 *
 * @category Handler
 * @package  SOFTSMSAlerts
 * @author   Softeria Tech <billing@softeriatech.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://sms.softeriatech.com/
 * SAPopupWidget class
 */
class SAPopupWidget extends Widget_Base
{
    
    /**
     * Get name function.
     *
     * @return array
     */
    public function get_name()
    {
        return 'softeria-alert-modal-widget';
    }

    /**
     * Get title function.
     *
     * @return array
     */
    public function get_title()
    {
        return __('Softeria Tech Modal', 'soft-sms-alerts');
    }

    /**
     * Get icon function.
     *
     * @return array
     */
    public function get_icon()
    {
        return 'eicon-form-horizontal';
    }

    /**
     * Get keywords function.     
     *
     * @return array
     */
    public function get_keywords()
    {
        return [
            'smspromodal',
            'smspromodal',
            'smspros modal',
            'smspromodal modals',
            'contact form',
            'form',
            'elementor form',
        ];
    }

    /**
     * Get  categories function.     
     *
     * @return array
     */
    public function get_categories()
    {        
        return ['general'];
    }

    /**
     * Get style depends function.     
     *
     * @return array
     */
    public function get_style_depends()
    {
        return [
            'smspros-form-styles',
            'smspros-public-default',
        ];
    }
     
    /**
     * Get  scrip depends function.     
     *
     * @return array
     */
    public function get_script_depends()
    {
        return ['smspros-elementor'];
    }
    /**
     * Register controls function.     
     *
     * @return array
     */
    protected function register_controls()
    {
        $this->registerGeneralControls();        
        $this->registerFormContainerStyleControls();
        $this->registerEditBtnStyleControls();
        $this->registerLabelStyleControls();
        $this->registerInputTextareaStyleControls();       
        $this->registerAddressLineStyleControls();       
        $this->registerSubmitButtonStyleControls();
        
        
    }

    /**
     * Register general controls function.     
     *
     * @return array
     */
    protected function registerGeneralControls()
    {
        $this->start_controls_section(
            'section_smspros_form',
            [
                'label' => __('Softeria Tech Modal', 'soft-sms-alerts'),
            ]
        );

        $this->add_control(
            'form_list',
            [
                'label'       => esc_html__('Softeria Tech Modal', 'soft-sms-alerts'),
                'type'        => Controls_Manager::SELECT,
                'label_block' => true,
                'options'     => array('popup-1'=>'Style1', 'popup-2'=>'Style2', 'popup-3'=>'Style3', 'popup-4'=>'Style4'),
                'default' => 'popup-1',                
            ]
        );
 
        $this->add_control(
            'sa_ele_f_mobile_lbl',
            [    
                'label'        => __('Modal Text', 'soft-sms-alerts'),
                                
                'type'         => "textarea",
                'placeholder'      => 'Enter text',                              
                'condition' => [
                    'form_list' => ['popup-1','popup-2','popup-3','popup-4'],
                ],
                'description' => esc_html__('Use ##phone## for mobile number', 'soft-sms-alerts'),                
            ]
        );

        $this->add_control(
            'sa_ele_f_mobile_placeholder',
            [
                'label'        => __('Placeholder', 'soft-sms-alerts'),                
                'type'         => "text",
                'placeholder'      => 'Enter Placeholder', 
                'condition' => [
                    'form_list' => ['popup-1'],
                ],                
            ]
        );        

        $this->add_control(
            'sa_ele_f_mobile_botton',
            [
                'label'        => __('Button Text', 'soft-sms-alerts'),
                'type'         => "text",
                'placeholder'      => 'Enter Button Text',
                'condition' => [
                    'form_list' => ['popup-1','popup-2','popup-3','popup-4'],
                ],                
            ]
        );
        $this->add_control(
            'sa_ele_f_otp_resend',
            [
                'label'        => __('Resend Text', 'soft-sms-alerts'),
                'type'         => "text",
                'placeholder'      => 'Enter Resend Text',
                'condition' => [
                    'form_list' => ['popup-1','popup-2','popup-3','popup-4'],
                ],                
            ]
        );
        $this->add_control(
            'sa_ele_f_resend_btn',
            [
                'label'        => __('Resend Button Text', 'soft-sms-alerts'),
                'type'         => "text",
                'placeholder'      => 'Enter Resend Button Text',
                'condition' => [
                    'form_list' => ['popup-1','popup-2','popup-3','popup-4'],
                ],                
            ]
        );
        $this->add_control(
            'sa_otp_re_send_timer',
            [
                'label'        => __('OTP Re-send Timer', 'soft-sms-alerts'),
                'type'         => "number",
                'min'          => "15",
                'max'          => "300",                
                'placeholder'  => 'Enter Number',
                'condition' => [
                    'form_list' => ['popup-1','popup-2','popup-3','popup-4'],
                ],                
            ]
        );
        $this->add_control(
            'max_otp_resend_allowed',
            [
                'label'        => __('Max OTP Re-send Allowed', 'soft-sms-alerts'),
                'type'         => "number",
                  'min'          => "1",
                  'max'          => "5",
                'placeholder'  => 'Enter number',
                'condition' => [
                    'form_list' => ['popup-1','popup-2','popup-3','popup-4'],
                ],                
            ]
        );
        
        $this->add_control(
            'sa_edit_mobile_number',
            [
                'label'        => __('Edit Mobile Label', 'soft-sms-alerts'),                
                'type'         => "text",
                'placeholder'      => 'Edit Number', 
                'condition' => [
                    'form_list' => ['popup-4'],
                ],                
            ]
        );
        
        $this->add_control(
            'auto_validate',
            [
                'label'        => __('Auto Validate Otp', 'soft-sms-alerts'),
                'type'         =>  Controls_Manager::SWITCHER,
                'default'      => 'off',
                'label_on'     => __('on', 'soft-sms-alerts'),
                'label_off'    => __('off', 'soft-sms-alerts'),
                                'return_value' => 'on',
                'condition' => [
                    'form_list' => ['popup-1','popup-2','popup-3','popup-4'],
                ],
            ]
        );
        
        $this->add_control(
            'sa_edit_mobile_meaasege',
            [
                'label'        => __('Edit Message', 'soft-sms-alerts'),                
                'type'         => "textarea",
                'placeholder'      => 'Please Enter Text', 
                'condition' => [
                    'form_list' => ['popup-4'],
                ],                
            ]
        );        
        $this->end_controls_section();
    }

  
 
    /**
     * Register form container style controls function.     
     *
     * @return array
     */
    protected function registerFormContainerStyleControls()
    {
        $this->start_controls_section(
            'section_form_container_style',
            [
                'label' => __('Form Container', 'soft-sms-alerts'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'form_container_background',
                'label'    => __('Background', 'soft-sms-alerts'),
                'types'    => ['classic'],
                'selector' => '{{WRAPPER}} .smspromodal-widget-wrapper .modal-content',
                'exclude' => ['image'],        
            ]
        );   
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'form_container_border',
                'selector' => '{{WRAPPER}} .smspromodal-widget-wrapper .modal-content',
            'exclude' => ['Width'],
            ]
        );
        $this->add_control(
            'form_container_border_radius',
            [
                'label'      => esc_html__('Border Radius', 'soft-sms-alerts'),
                'type'       => Controls_Manager::DIMENSIONS,
                'separator'  => 'before',
                'size_units' => ['px'],
                'selectors'  => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper .modal-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );      
        $this->end_controls_section();
    }
    
    /**
     * Register Address style controls function.     
     *
     * @return array
     */
    protected function registerEditBtnStyleControls()
    {
        $this->start_controls_section(
            'section_form_address_style',
            [
                'label' => __('Edit Mobile Number', 'soft-sms-alerts'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'address_text_color',
            [
                'label'     => __('text Color', 'soft-sms-alerts'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper .saeditphone' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_section();
    }  
    /**
     * Register label style controls function.     
     *
     * @return array
     */
    protected function registerLabelStyleControls()
    {
        $this->start_controls_section(
            'section_form_label_style',
            [
                'label' => __('Modal Text', 'soft-sms-alerts'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'form_label_text_color',
            [
                'label'     => __('Text Color', 'soft-sms-alerts'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper .sa-message,.smspromodal-widget-wrapper .saeditmessage, .smspromodal-widget-wrapper .sa-lwo-form label' => 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'form_label_bg_color',
            [
                'label'     => __('Background Color', 'soft-sms-alerts'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper .sa-message,.smspromodal-widget-wrapper .saeditmessage, .smspromodal-widget-wrapper .sa-lwo-form label' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'form_label_typography',
                'label'    => __('Typography', 'soft-sms-alerts'),
                'selector' => '{{WRAPPER}} .smspromodal-widget-wrapper .sa-message,.smspromodal-widget-wrapper .saeditmessage,.smspromodal-widget-wrapper .sa-lwo-form label',
            ]
        );
        $this->end_controls_section();
    }

    /**
     * Register input textarea style controls function.     
     *
     * @return array
     */
    protected function registerInputTextareaStyleControls()
    {
        $this->start_controls_section(
            'section_form_fields_style',
            [
                'label' => __('OTP Field', 'soft-sms-alerts'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'input_alignment',
            [
                'label'   => __('Alignment', 'soft-sms-alerts'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'soft-sms-alerts'),
                        'icon'  => 'fa fa-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'soft-sms-alerts'),
                        'icon'  => 'fa fa-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'soft-sms-alerts'),
                        'icon'  => 'fa fa-align-right',
                    ],
                ],
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group textarea, {{WRAPPER}} .smspromodal-widget-wrapper .sa-message, .smspromodal-widget-wrapper .sa-el-group select' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_form_fields_style');

        $this->start_controls_tab(
            'tab_form_fields_normal',
            [
                'label' => __('Normal', 'soft-sms-alerts'),
            ]
        );

        $this->add_control(
            'form_field_bg_color',
            [
                'label'     => __('Background Color', 'soft-sms-alerts'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):not(.select2-search__field), {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group textarea, {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group select, {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group .select2-container--default .select2-selection--multiple' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'form_field_text_color',
            [
                'label'     => __('Text Color', 'soft-sms-alerts'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group textarea, {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group select' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'form_field_border',
                'label'       => __('Border', 'soft-sms-alerts'),
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .smspromodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):not(.select2-search__field), {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group textarea, {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group select,  {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group .select2-container--default .select2-selection--multiple',
                'separator'   => 'before',
            ]
        );

        $this->add_control(
            'form_field_radius',
            [
                'label'      => __('Border Radius', 'soft-sms-alerts'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group textarea, {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group select,  {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group .select2-container--default .select2-selection--multiple' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_field_text_indent',
            [
                'label' => __('Text Indent', 'soft-sms-alerts'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 60,
                        'step' => 1,
                    ],
                    '%' => [
                        'min'  => 0,
                        'max'  => 30,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group textarea, {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group select' => 'text-indent: {{SIZE}}{{UNIT}}',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'form_input_width',
            [
                'label' => __('Input Width', 'soft-sms-alerts'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1200,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group select' => 'width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_input_height',
            [
                'label' => __('Input Height', 'soft-sms-alerts'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 80,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group select' => 'height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_field_padding',
            [
                'label'      => __('Padding', 'soft-sms-alerts'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group textarea, {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_field_spacing',
            [
                'label' => __('Spacing', 'soft-sms-alerts'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 100,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'form_field_typography',
                'label'     => __('Typography', 'soft-sms-alerts'),
                'selector'  => '{{WRAPPER}} .smspromodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group textarea, {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group select',
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'form_field_box_shadow',
                'selector'  => '{{WRAPPER}} .smspromodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]), {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group textarea, {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group select',
                'separator' => 'before',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_form_fields_focus',
            [
                'label' => __('Focus', 'soft-sms-alerts'),
            ]
        );

        $this->add_control(
            'form_field_bg_color_focus',
            [
                'label'     => __('Background Color', 'soft-sms-alerts'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):focus, {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group textarea:focus' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'form_input_focus_border',
                'label'       => __('Border', 'soft-sms-alerts'),
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .smspromodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):focus, {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group textarea:focus',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'form_input_focus_box_shadow',
                'selector'  => '{{WRAPPER}} .smspromodal-widget-wrapper input:not([type=radio]):not([type=checkbox]):not([type=submit]):not([type=button]):not([type=image]):not([type=file]):focus, {{WRAPPER}} .smspromodal-widget-wrapper .sa-el-group textarea:focus',
                'separator' => 'before',
            ]
        );
        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }
    
    /**
     * Register Address style controls function.     
     *
     * @return array
     */
    protected function registerAddressLineStyleControls()
    {
        $this->start_controls_section(
            'section_form_address_line_style',
            [
                'label' => __('Resend Otp', 'soft-sms-alerts'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'address_line_text_color',
            [
                'label'     => __('text Color', 'soft-sms-alerts'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper .sa_resend_btn,.smspromodal-widget-wrapper .sa_forgot,.smspromodal-widget-wrapper .sa_timer' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'address_line_label_typography',
                'label'    => __('Typography', 'soft-sms-alerts'),
                'selector' => '{{WRAPPER}} .smspromodal-widget-wrapper .sa_resend_btn, .smspromodal-widget-wrapper .sa_forgot,.smspromodal-widget-wrapper .sa_timer',
            ]
        );
        $this->end_controls_section();
    }    
    
     /**
      * Register submit button style controls function.     
      *
      * @return array
      */
    protected function registerSubmitButtonStyleControls()
    {
        $this->start_controls_section(
            'section_form_submit_button_style',
            [
                'label' => __('Submit Button', 'soft-sms-alerts'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'form_submit_button_align',
            [
                'label'   => __('Alignment', 'soft-sms-alerts'),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'soft-sms-alerts'),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'soft-sms-alerts'),
                        'icon'  => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'soft-sms-alerts'),
                        'icon'  => 'eicon-h-align-right',
                    ],
                ],
                'default'      => '',
                'prefix_class' => 'smspromodal-widget-submit-button-',
                'condition'    => [
                    'form_submit_button_width_type' => 'custom',
                ],
            ]
        );

        $this->add_control(
            'form_submit_button_width_type',
            [
                'label'   => __('Width', 'soft-sms-alerts'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'custom',
                'options' => [
                    'full-width' => __('Full Width', 'soft-sms-alerts'),
                    'custom'     => __('Custom', 'soft-sms-alerts'),
                ],
                'prefix_class' => 'smspromodal-widget-submit-button-',
            ]
        );

        $this->add_responsive_control(
            'form_submit_button_width',
            [
                'label' => __('Width', 'soft-sms-alerts'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1200,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper .softeria_alerts_otp_validate_submit,.smspromodal-widget-wrapper .saresubmit' => 'width: {{SIZE}}{{UNIT}}', ],
                'condition' => [
                    'form_submit_button_width_type' => 'custom',
                ],
            ]
        );

        $this->start_controls_tabs('tabs_submit_button_style');

        $this->start_controls_tab(
            'tab_submit_button_normal',
            [
                'label' => __('Normal', 'soft-sms-alerts'),
            ]
        );

        $this->add_control(
            'form_submit_button_bg_color_normal',
            [
                'label'     => __('Background Color', 'soft-sms-alerts'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#409EFF',
                'selectors' => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper .softeria_alerts_otp_validate_submit,.smspromodal-widget-wrapper .saresubmit' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'form_submit_button_text_color_normal',
            [
                'label'     => __('Text Color', 'soft-sms-alerts'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper .softeria_alerts_otp_validate_submit,.smspromodal-widget-wrapper .saresubmit' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'form_submit_button_border_normal',
                'label'       => __('Border', 'soft-sms-alerts'),
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .smspromodal-widget-wrapper .softeria_alerts_otp_validate_submit,.smspromodal-widget-wrapper .saresubmit',
            ]
        );

        $this->add_control(
            'form_submit_button_border_radius',
            [
                'label'      => __('Border Radius', 'soft-sms-alerts'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper .softeria_alerts_otp_validate_submit,.smspromodal-widget-wrapper .saresubmit' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_submit_button_padding',
            [
                'label'      => __('Padding', 'soft-sms-alerts'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper .softeria_alerts_otp_validate_submit,.smspromodal-widget-wrapper .saresubmit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'form_submit_button_margin',
            [
                'label' => __('Margin Top', 'soft-sms-alerts'),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 150,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', 'em', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper .softeria_alerts_otp_validate_submit,.smspromodal-widget-wrapper .saresubmit' => 'margin-top: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'form_submit_button_typography',
                'label'     => __('Typography', 'soft-sms-alerts'),
                'selector'  => '{{WRAPPER}} .smspromodal-widget-wrapper .softeria_alerts_otp_validate_submit,.smspromodal-widget-wrapper .saresubmit',
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'form_submit_button_box_shadow',
                'selector'  => '{{WRAPPER}} .smspromodal-widget-wrapper.softeria_alerts_otp_validate_submit,.smspromodal-widget-wrapper .saresubmit',
                'separator' => 'before',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_submit_button_hover',
            [
                'label' => __('Hover', 'soft-sms-alerts'),
            ]
        );

        $this->add_control(
            'form_submit_button_bg_color_hover',
            [
                'label'     => __('Background Color', 'soft-sms-alerts'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper .softeria_alerts_otp_validate_submit:hover,.smspromodal-widget-wrapper .saresubmit:hover' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'form_submit_button_text_color_hover',
            [
                'label'     => __('Text Color', 'soft-sms-alerts'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper .softeria_alerts_otp_validate_submit:hover,.smspromodal-widget-wrapper .saresubmit:hover' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        
        $this->add_control(
            'form_submit_button_border_color_hover',
            [
                'label'     => __('Border Color', 'soft-sms-alerts'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .smspromodal-widget-wrapper .softeria_alerts_otp_validate_submit:hover,.smspromodal-widget-wrapper .saresubmit:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();
    }
    
    
    
    

    /**
     * Render the widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     *
     * @access protected
     *
     * @return array
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $settings = !empty($settings)?$settings:array();
        extract($settings);
        $this->add_render_attribute(
            'smspromodal_widget_wrapper',
            [
                'class' => [
                    'smspromodal-widget-wrapper',
                ],
            ]
        );         
        if (!empty($form_list)) { ?>
            <div <?php echo wp_kses_post($this->get_render_attribute_string('smspromodal_widget_wrapper')); ?>>
                   <?php    
                           
            
                    $values = $form_list;
         
                    switch ($values) {
                    case "popup-1": 
                        echo SAPopup::getModelStyle(array('sa_label'=>$sa_ele_f_mobile_lbl, 'placeholder' =>$sa_ele_f_mobile_placeholder, 'sa_button' =>$sa_ele_f_mobile_botton, 'sa_resend_otp' =>$sa_ele_f_otp_resend, 'sa_resend_btns' =>$sa_ele_f_resend_btn,'otp_template_style'=>'popup-1'));
                        break; 
                    case "popup-2":
                        echo SAPopup::getModelStyle(array('sa_label'=>$sa_ele_f_mobile_lbl, 'placeholder' =>$sa_ele_f_mobile_placeholder, 'sa_button' =>$sa_ele_f_mobile_botton, 'sa_resend_otp' =>$sa_ele_f_otp_resend, 'sa_resend_btns' =>$sa_ele_f_resend_btn,'otp_template_style'=>'popup-2'));
                        break;
                    case "popup-3":
                        echo SAPopup::getModelStyle(array('sa_label'=>$sa_ele_f_mobile_lbl, 'placeholder' =>$sa_ele_f_mobile_placeholder, 'sa_button' =>$sa_ele_f_mobile_botton, 'sa_resend_otp' =>$sa_ele_f_otp_resend, 'sa_resend_btns' =>$sa_ele_f_resend_btn,'otp_template_style'=>'popup-3'));
                        break;
                    case "popup-4":
                               echo SAPopup::getModelStyle(array('sa_mobile_meaasege'=>$sa_edit_mobile_meaasege,'edit_phone_label'=>$sa_edit_mobile_number,'sa_label'=>$sa_ele_f_mobile_lbl, 'placeholder' =>$sa_ele_f_mobile_placeholder, 'sa_button' =>$sa_ele_f_mobile_botton, 'sa_resend_otp' =>$sa_ele_f_otp_resend, 'sa_resend_btns' =>$sa_ele_f_resend_btn,'otp_template_style'=>'popup-4'));
                        break;
                
                    } 
        }
        
    }    

     /**
      * Content template function.     
      *
      * @return array
      */
    protected function content_template()
    {
    }
}