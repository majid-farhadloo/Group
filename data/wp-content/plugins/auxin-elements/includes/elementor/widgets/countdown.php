<?php
namespace Auxin\Plugin\CoreElements\Elementor\Elements;

use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Scheme_Color;
use Elementor\Scheme_Typography;
use Elementor\Utils;
use Elementor\Control_Media;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Background;

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

/**
 * Elementor 'Countdown' widget.
 *
 * Elementor widget that displays an 'Countdown' with lightbox.
 *
 * @since 1.0.0
 */
class Countdown extends Widget_Base {

    /**
     * Get widget name.
     *
     * Retrieve 'Countdown' widget name.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'aux_countdown';
    }

    /**
     * Get widget title.
     *
     * Retrieve 'Countdown' widget title.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return __('Countdown', 'auxin-elements' );
    }

    /**
     * Get widget icon.
     *
     * Retrieve 'Countdown' widget icon.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-countdown';
    }

    /**
     * Get widget categories.
     *
     * Retrieve 'Countdown' widget icon.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_categories() {
        return array( 'auxin-core' );
    }

    /**
     * Register 'Countdown' widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function _register_controls() {

        /*-----------------------------------------------------------------------------------*/
        /*  Content TAB
        /*-----------------------------------------------------------------------------------*/

        /*  Countdown Section
        /*-------------------------------------*/

        $this->start_controls_section(
            'countdown_section',
            array(
                'label'      => __('Countdown', 'auxin-elements' ),
            )
        );

        $this->add_control(
            'countdown_style',
            array(
                'label'       => __('View as', 'auxin-elements'),
                'type'        => Controls_Manager::SELECT,
                'default'     => 'inline',
                'options'     => array(
                    'block'  => __( 'Block'  , 'auxin-elements' ),
                    'inline' => __( 'Inline' , 'auxin-elements' )
                )
            )
        );

        $this->add_control(
            'datetime',
            array(
                'label'       => __('Target Time For Countdown', 'auxin-elements'),
                'type'        => Controls_Manager::DATE_TIME,
                'default'     => '2022-11-21 23:42',
                'picker_options' => array(
                    'dateFormat' => 'Y/m/d H:i:S'
                )
            )
        );

        $this->add_control(
            'seperator',
            array(
                'label'       => __('Seperator','auxin-elements' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => '/'
            )
        );

        $this->add_control(
            'show_year',
            array(
                'label'        => __('Display Years','auxin-elements' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'On', 'auxin-elements' ),
                'label_off'    => __( 'Off', 'auxin-elements' ),
                'return_value' => '1',
                'default'      => '1'
            )
        );

        $this->add_control(
            'show_month',
            array(
                'label'        => __('Display Month','auxin-elements' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'On', 'auxin-elements' ),
                'label_off'    => __( 'Off', 'auxin-elements' ),
                'return_value' => '1',
                'default'      => '1'
            )
        );

        $this->add_control(
            'show_day',
            array(
                'label'        => __('Display Days','auxin-elements' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'On', 'auxin-elements' ),
                'label_off'    => __( 'Off', 'auxin-elements' ),
                'return_value' => '1',
                'default'      => '1'
            )
        );

        $this->add_control(
            'show_hour',
            array(
                'label'        => __('Display Hours','auxin-elements' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'On', 'auxin-elements' ),
                'label_off'    => __( 'Off', 'auxin-elements' ),
                'return_value' => '1',
                'default'      => '1'
            )
        );

        $this->add_control(
            'show_min',
            array(
                'label'        => __('Display Mintues','auxin-elements' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'On', 'auxin-elements' ),
                'label_off'    => __( 'Off', 'auxin-elements' ),
                'return_value' => '1',
                'default'      => '1'
            )
        );

        $this->add_control(
            'show_sec',
            array(
                'label'        => __('Display Seconds','auxin-elements' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'On', 'auxin-elements' ),
                'label_off'    => __( 'Off', 'auxin-elements' ),
                'return_value' => '1',
                'default'      => '1'
            )
        );

        $this->end_controls_section();

        /*-----------------------------------------------------------------------------------*/
        /*  Style TAB
        /*-----------------------------------------------------------------------------------*/

        /*  Item Wrapper Style
        /*-------------------------------------*/

        $this->start_controls_section(
            'item_wrapper_section',
            array(
                'label' => __('Item Wrapper', 'auxin-elements' ),
                'tab'   => Controls_Manager::TAB_STYLE
            )
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name' => 'item_wrapper_background',
                'label' => __( 'Background', 'auxin-elements' ),
                'types' => array( 'classic', 'gradient' ),
                'selector' => '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-item'
            )
        );

        $this->add_responsive_control(
            'item_wrapper_border_radius',
            array(
                'label'      => __( 'Border Radius', 'auxin-elements' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                )
            )
        );

        $this->add_responsive_control(
            'item_wrapper_margin',
            array(
                'label'      => __( 'Margin', 'auxin-elements' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                )
            )
        );

        $this->add_responsive_control(
            'item_wrapper_padding',
            array(
                'label'      => __( 'Padding', 'auxin-elements' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                )
            )
        );



        $this->end_controls_section();


        /*  Value Style
        /*-------------------------------------*/


        $this->start_controls_section(
            'value_style_section',
            array(
                'label' => __('Value', 'auxin-elements' ),
                'tab'   => Controls_Manager::TAB_STYLE
            )
        );

        $this->start_controls_tabs( 'value_colors' );

        $this->start_controls_tab(
            'value_color_normal',
            array(
                'label' => __( 'Normal' , 'auxin-elements' ),
            )
        );

        $this->add_control(
            'value_color',
            array(
                'label' => __( 'Color', 'auxin-elements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-value' => 'color: {{VALUE}} !important;',
                ),
            )
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'value_color_hover',
            array(
                'label' => __( 'Hover' , 'auxin-elements' ),
            )
        );

        $this->add_control(
            'value_hover_color',
            array(
                'label' => __( 'Color', 'auxin-elements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-value:hover' => 'color: {{VALUE}} !important;',
                ),
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name' => 'value_typography',
                'scheme' => Scheme_Typography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-value',
            )
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            array(
                'name' => 'value_shadow',
                'label' => __( 'Text Shadow', 'auxin-elements' ),
                'selector' => '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-value',
            )
        );

        $this->end_controls_section();


        /*  Seperator Style
        /*-------------------------------------*/


        $this->start_controls_section(
            'seperator_style_section',
            array(
                'label' => __('Seperator', 'auxin-elements' ),
                'tab'   => Controls_Manager::TAB_STYLE
            )
        );

        $this->start_controls_tabs( 'seperator_colors' );

        $this->start_controls_tab(
            'seperator_color_normal',
            array(
                'label' => __( 'Normal' , 'auxin-elements' ),
            )
        );

        $this->add_control(
            'seperator_color',
            array(
                'label' => __( 'Color', 'auxin-elements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-seperator' => 'color: {{VALUE}} !important;',
                ),
            )
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'seperator_color_hover',
            array(
                'label' => __( 'Hover' , 'auxin-elements' ),
            )
        );

        $this->add_control(
            'seperator_hover_color',
            array(
                'label' => __( 'Color', 'auxin-elements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-seperator:hover' => 'color: {{VALUE}} !important;',
                ),
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name' => 'seperator_typography',
                'scheme' => Scheme_Typography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-seperator',
            )
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            array(
                'name' => 'seperator_shadow',
                'label' => __( 'Text Shadow', 'auxin-elements' ),
                'selector' => '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-seperator',
            )
        );

        $this->add_responsive_control(
            'seperator_padding',
            array(
                'label'      => __( 'Padding', 'auxin-elements' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .aux-countdown-wrapper.aux-countdown-inline .aux-countdown-seperator' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
                'condition'   => array(
                    'countdown_style' => array('inline')
                )
            )
        );

        $this->add_responsive_control(
            'seperator_margin',
            array(
                'label'      => __( 'Margin', 'auxin-elements' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-seperator' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                )
            )
        );

        $this->end_controls_section();

        /*  Title Style
        /*-------------------------------------*/


        $this->start_controls_section(
            'title_style_section',
            array(
                'label' => __('Title', 'auxin-elements' ),
                'tab'   => Controls_Manager::TAB_STYLE
            )
        );

        $this->start_controls_tabs( 'title_colors' );

        $this->start_controls_tab(
            'title_color_normal',
            array(
                'label' => __( 'Normal' , 'auxin-elements' ),
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label' => __( 'Color', 'auxin-elements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-title' => 'color: {{VALUE}} !important;',
                ),
            )
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'title_color_hover',
            array(
                'label' => __( 'Hover' , 'auxin-elements' ),
            )
        );

        $this->add_control(
            'title_hover_color',
            array(
                'label' => __( 'Color', 'auxin-elements' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-title:hover' => 'color: {{VALUE}} !important;',
                ),
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            array(
                'name' => 'title_typography',
                'scheme' => Scheme_Typography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-title',
            )
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            array(
                'name' => 'title_shadow',
                'label' => __( 'Text Shadow', 'auxin-elements' ),
                'selector' => '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-title',
            )
        );

        $this->add_responsive_control(
            'title_margin',
            array(
                'label'      => __( 'Margin', 'auxin-elements' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .aux-countdown-wrapper .aux-countdown-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                )
            )
        );

        $this->end_controls_section();


    }

  /**
   * Render image box widget output on the frontend.
   *
   * Written in PHP and used to generate the final HTML.
   *
   * @since 1.0.0
   * @access protected
   */
  protected function render() {

    $settings   = $this->get_settings_for_display();


    $args       = array(
        'countdown_style' => $settings['countdown_style'],
        'datetime'        => $settings['datetime'],
        'show_year'       => $settings['show_year'],
        'show_month'      => $settings['show_month'],
        'show_day'        => $settings['show_day'],
        'show_hour'       => $settings['show_hour'],
        'show_min'        => $settings['show_min'],
        'show_sec'        => $settings['show_sec'],
        'seperator'       => $settings['seperator'],
    );

    // get the shortcode base blog page
    echo auxin_widget_countdown_callback( $args );

  }

}
