<?php
namespace Auxin\Plugin\CoreElements\Elementor\Modules;

use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Scheme_Color;
use Elementor\Scheme_Typography;
use Elementor\Control_Media;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;


class Common {

    /**
     * Instance of this class.
     *
     * @var      object
     */
    protected static $instance = null;


    function __construct(){

        // Add new controls to advanced tab globally
        add_action( "elementor/element/after_section_end", array( $this, 'add_position_controls_section'  ), 11, 3 );
        // Go pro notice for parallax options
        add_action( "elementor/element/after_section_end", array( $this, 'add_parallax_go_pro_notice'     ), 15, 3 );
        add_action( "elementor/element/after_section_end", array( $this, 'add_transition_go_pro_notice'   ), 18, 3 );

        add_action( "elementor/element/after_section_end", array( $this, 'add_pseudo_background_controls' ), 20, 3 );
        add_action( "elementor/element/after_section_end", array( $this, 'add_custom_css_controls_section'), 25, 3 );

        // Renders attributes for all Elementor Elements
        // add_action( 'elementor/frontend/widget/before_render' , array( $this, 'render_widget_attributes' ) );

        // Render the custom CSS
        if ( ! defined('ELEMENTOR_PRO_VERSION') ) {
            add_action( 'elementor/element/parse_css', array( $this, 'add_post_css' ), 10, 2 );
        }
    }

    /**
     * Return an instance of this class.
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {
        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Add custom css control to all elements
     *
     * @return void
     */
    public function add_custom_css_controls_section( $widget, $section_id, $args ){

        if( 'section_custom_css_pro' !== $section_id ){
            return;
        }

        if( ! defined('ELEMENTOR_PRO_VERSION') ) {

            $widget->start_controls_section(
                'aux_core_common_custom_css_section',
                array(
                    'label'     => __( 'Custom CSS', 'auxin-elements' ),
                    'tab'       => Controls_Manager::TAB_ADVANCED
                )
            );

            $widget->add_control(
                'custom_css',
                array(
                    'type'        => Controls_Manager::CODE,
                    'label'       => __( 'Custom CSS', 'auxin-elements' ),
                    'label_block' => true,
                    'language'    => 'css'
                )
            );
            ob_start();?>
<pre>
Examples:
// To target main element
selector { color: red; }
// For child element
selector .child-element{ margin: 10px; }
</pre>
            <?php
            $example = ob_get_clean();

            $widget->add_control(
                'custom_css_description',
                array(
                    'raw'             => __( 'Use "selector" keyword to target wrapper element.', 'auxin-elements' ). $example,
                    'type'            => Controls_Manager::RAW_HTML,
                    'content_classes' => 'elementor-descriptor',
                    'separator'       => 'none'
                )
            );

            $widget->end_controls_section();
        }

    }



    /**
     * Add controls to advanced section for adding background image to pseudo elements
     *
     * @return void
     */
    public function add_pseudo_background_controls( $widget, $section_id, $args ){

        $target_sections = array('section_custom_css');

        if( ! defined('ELEMENTOR_PRO_VERSION') ) {
            $target_sections[] = 'section_custom_css_pro';
        }

        if( ! in_array( $section_id, $target_sections ) ){
            return;
        }

        if( in_array( $widget->get_name(), array('section') ) ){
            return;
        }

        // Adds general background options to pseudo elements
        // ---------------------------------------------------------------------
        $widget->start_controls_section(
            'aux_core_common_background_pseudo',
            array(
                'label'     => __( 'Pseudo Background (developers)', 'auxin-elements' ),
                'tab'       => Controls_Manager::TAB_ADVANCED
            )
        );

        $widget->add_control(
            'background_pseudo_description',
            array(
                'raw'  => __( 'Adds background to pseudo elements like ::before and ::after selectors. (developers only)', 'auxin-elements' ),
                'type' => Controls_Manager::RAW_HTML,
                'content_classes' => 'elementor-descriptor'
            )
        );

        $widget->add_control(
            'background_pseudo_before_heading',
            array(
                'label'     => __( 'Background ::before', 'auxin-elements' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before'
            )
        );

        $widget->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'background_pseudo_before',
                'types'    => array( 'classic', 'gradient'),
                'selector' => '{{WRAPPER}}:before'
            )
        );

        $widget->add_control(
            'background_pseudo_after_heading',
            array(
                'label'     => __( 'Background ::after', 'auxin-elements' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before'
            )
        );

        $widget->add_group_control(
            Group_Control_Background::get_type(),
            array(
                'name'     => 'background_pseudo_after',
                'types'    => array( 'classic', 'gradient'),
                'selector' => '{{WRAPPER}}:after'
            )
        );

        $widget->end_controls_section();
    }


    /**
     * Add parallax pro notice to advanced section
     *
     * @return void
     */
    public function add_parallax_go_pro_notice( $widget, $section_id, $args ){

        if( defined('THEME_PRO') && THEME_PRO ){
            return;
        }

        if( in_array( $widget->get_name(), array('section') ) ){
            return;
        }

        // Anchor element sections
        $target_sections = array('section_custom_css');

        if( ! defined('ELEMENTOR_PRO_VERSION') ) {
            $target_sections[] = 'section_custom_css_pro';
        }

        if( ! in_array( $section_id, $target_sections ) ){
            return;
        }

        $widget->start_controls_section(
            'aux_pro_common_parallax_notice',
            array(
                'label'     => __( 'Parallax', 'auxin-elements' ),
                'tab'       => Controls_Manager::TAB_ADVANCED
            )
        );

        $widget->add_control(
            'parallax_go_pro_notice',
            array(
                'type' => Controls_Manager::RAW_HTML,
                'content_classes' => 'elementor-descriptor',
                'raw' => '<div class="auxin-elementor-panel-notice">' .
                        '<i class="auxin-elementor-upgrade-notice-icon eicon-favorite" aria-hidden="true"></i>
                        <div class="auxin-elementor-panel-notice-title">' .
                            __( 'Parallax Effect', 'auxin-elements' ) .
                        '</div>
                        <div class="auxin-elementor-panel-notice-message">' .
                            __( 'Parallax options let you add parallax effect to any widget.', 'auxin-elements' ) .
                        '</div>
                        <div class="auxin-elementor-panel-notice-message">' .
                            __( 'This feature is only available on Phlox Pro.', 'auxin-elements' ) .
                        '</div>
                        <a class="auxin-elementor-panel-notice-link elementor-button elementor-button-default auxin-elementor-go-pro-link" href="http://phlox.pro/go-pro/?utm_source=elementor-panel&utm_medium=phlox-free&utm_campaign=phlox-go-pro&utm_content=parallax" target="_blank">' .
                            __( 'Get Phlox Pro', 'auxin-elements' ) .
                        '</a>
                        </div>'
            )
        );

        $widget->end_controls_section();
    }


    /**
     * Adds transition pro notice to advanced section
     *
     * @return void
     */
    public function add_transition_go_pro_notice( $widget, $section_id, $args ){

        if( defined('THEME_PRO') && THEME_PRO ){
            return;
        }

        // Anchor element sections
        $target_sections = array('section_custom_css');

        if( ! defined('ELEMENTOR_PRO_VERSION') ) {
            $target_sections[] = 'section_custom_css_pro';
        }

        if( ! in_array( $section_id, $target_sections ) ){
            return;
        }


        $widget->start_controls_section(
            'aux_pro_common_inview_transition_notice',
            array(
                'label'     => __( 'Entrance Animation', 'auxin-elements' ),
                'tab'       => Controls_Manager::TAB_ADVANCED
            )
        );

        $widget->add_control(
            'inview_transition_go_pro_notice',
            array(
                'type' => Controls_Manager::RAW_HTML,
                'content_classes' => 'elementor-descriptor',
                'raw' => '<div class="auxin-elementor-panel-notice">' .
                        '<i class="auxin-elementor-upgrade-notice-icon eicon-favorite" aria-hidden="true"></i>
                        <div class="auxin-elementor-panel-notice-title">' .
                            __( 'Entrance Animation', 'auxin-elements' ) .
                        '</div>
                        <div class="auxin-elementor-panel-notice-message">' .
                            __( 'Entrance animation options let you add entrance transitions to any widget.', 'auxin-elements' ) .
                        '</div>
                        <div class="auxin-elementor-panel-notice-message">' .
                            __( 'This feature is only available on Phlox Pro.', 'auxin-elements' ) .
                        '</div>
                        <a class="auxin-elementor-panel-notice-link elementor-button elementor-button-default auxin-elementor-go-pro-link" href="http://phlox.pro/go-pro/?utm_source=elementor-panel&utm_medium=phlox-free&utm_campaign=phlox-go-pro&utm_content=entrance-animation" target="_blank">' .
                            __( 'Get Phlox Pro', 'auxin-elements' ) .
                        '</a>
                        </div>'
            )
        );

        $widget->end_controls_section();
    }

    /**
     * Add extra controls for positioning to advanced section
     *
     * @return void
     */
    public function add_position_controls_section( $widget, $section_id, $args ){

        // Anchor element sections
        $target_sections = array('section_custom_css');

        if( ! defined('ELEMENTOR_PRO_VERSION') ) {
            $target_sections[] = 'section_custom_css_pro';
        }

        if( ! in_array( $section_id, $target_sections ) ){
            return;
        }

        // Adds general positioning options
        // ---------------------------------------------------------------------
        $widget->start_controls_section(
            'aux_core_common_position',
            array(
                'label'     => __( 'Positioning', 'auxin-elements' ),
                'tab'       => Controls_Manager::TAB_ADVANCED
            )
        );

        $widget->add_responsive_control(
            'aux_position_type',
            array(
                'label'       => __( 'Position Type', 'auxin-elements' ),
                'label_block' => true,
                'type'        => Controls_Manager::SELECT,
                'options'     => array(
                    ''         => __( 'Default', 'auxin-elements'  ),
                    'static'   => __( 'Static', 'auxin-elements'   ),
                    'relative' => __( 'Relative', 'auxin-elements' ),
                    'absolute' => __( 'Absolute', 'auxin-elements' )
                ),
                'default'      => '',
                'selectors'    => array(
                    '{{WRAPPER}}' => 'position:{{VALUE}};',
                )
            )
        );

        $widget->add_responsive_control(
            'aux_position_top',
            array(
                'label'      => __('Top','auxin-elements' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'em', '%'),
                'range'      => array(
                    'px' => array(
                        'min'  => -2000,
                        'max'  => 2000,
                        'step' => 1
                    ),
                    '%' => array(
                        'min'  => -100,
                        'max'  => 100,
                        'step' => 1
                    ),
                    'em' => array(
                        'min'  => -150,
                        'max'  => 150,
                        'step' => 1
                    )
                ),
                'selectors' => array(
                    '{{WRAPPER}}' => 'top:{{SIZE}}{{UNIT}};'
                ),
                'condition' => array(
                    'aux_position_type' => array('relative', 'absolute')
                )
            )
        );

        $widget->add_responsive_control(
            'aux_position_right',
            array(
                'label'      => __('Right','auxin-elements' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'em', '%'),
                'range'      => array(
                    'px' => array(
                        'min'  => -2000,
                        'max'  => 2000,
                        'step' => 1
                    ),
                    '%' => array(
                        'min'  => -100,
                        'max'  => 100,
                        'step' => 1
                    ),
                    'em' => array(
                        'min'  => -150,
                        'max'  => 150,
                        'step' => 1
                    )
                ),
                'selectors' => array(
                    '{{WRAPPER}}' => 'right:{{SIZE}}{{UNIT}};'
                ),
                'condition' => array(
                    'aux_position_type' => array('relative', 'absolute')
                ),
                'return_value' => ''
            )
        );

        $widget->add_responsive_control(
            'aux_position_bottom',
            array(
                'label'      => __('Bottom','auxin-elements' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'em', '%'),
                'range'      => array(
                    'px' => array(
                        'min'  => -2000,
                        'max'  => 2000,
                        'step' => 1
                    ),
                    '%' => array(
                        'min'  => -100,
                        'max'  => 100,
                        'step' => 1
                    ),
                    'em' => array(
                        'min'  => -150,
                        'max'  => 150,
                        'step' => 1
                    )
                ),
                'selectors' => array(
                    '{{WRAPPER}}' => 'bottom:{{SIZE}}{{UNIT}};'
                ),
                'condition' => array(
                    'aux_position_type' => array('relative', 'absolute')
                )
            )
        );

        $widget->add_responsive_control(
            'aux_position_left',
            array(
                'label'      => __('Left','auxin-elements' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'em', '%'),
                'range'      => array(
                    'px' => array(
                        'min'  => -2000,
                        'max'  => 2000,
                        'step' => 1
                    ),
                    '%' => array(
                        'min'  => -100,
                        'max'  => 100,
                        'step' => 1
                    ),
                    'em' => array(
                        'min'  => -150,
                        'max'  => 150,
                        'step' => 1
                    )
                ),
                'selectors' => array(
                    '{{WRAPPER}}' => 'left:{{SIZE}}{{UNIT}};'
                ),
                'condition' => array(
                    'aux_position_type' => array('relative', 'absolute')
                )
            )
        );

        $widget->add_responsive_control(
            'aux_position_from_center',
            array(
                'label'      => __('From Center','auxin-elements' ),
                'description'=> __('Please avoid using "From Center" and "Left" options at the same time.','auxin-elements' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => array('px', 'em', '%'),
                'range'      => array(
                    'px' => array(
                        'min'  => -1000,
                        'max'  => 1000,
                        'step' => 1
                    ),
                    '%' => array(
                        'min'  => -100,
                        'max'  => 100,
                        'step' => 1
                    ),
                    'em' => array(
                        'min'  => -150,
                        'max'  => 150,
                        'step' => 1
                    )
                ),
                'selectors' => array(
                    '{{WRAPPER}}' => 'left:calc( 50% + {{SIZE}}{{UNIT}} );'
                ),
                'condition' => array(
                    'aux_position_type' => array('relative', 'absolute')
                )
            )
        );

        $widget->end_controls_section();
    }


    /**
     * Modify the render of elementor elements
     *
     * @param  Widget_Base $widget Instance of Elementor Widget
     *
     * @return void
     */
    public function render_widget_attributes( $widget ){
        $settings = $widget->get_settings();
    }


    /**
     * Retrives the setting value or checkes whether the setting value
     * mathes with a value or not
     *
     * @param  array  $settings Settings in an array
     * @param  string $key      The setting key
     * @param  string $value    An optional value to compare with the setting value
     *
     * @return mixed           Setting value or a boolean value
     */
    private function setting_value( $settings, $key, $value = null ){
        if( ! isset( $settings[ $key ] ) ){
            return;
        }
        // Retrieves the setting value
        if( is_null( $value ) ){
            return $settings[ $key ];
        }
        // Validates the setting value
        return ! empty( $settings[ $key ] ) && $value == $settings[ $key ];
    }

    /**
     * Render Custom CSS for an Elementor Element
     *
     * @param $post_css Post_CSS_File
     * @param $element Element_Base
     */
    public function add_post_css( $post_css, $element ) {
        $element_settings = $element->get_settings();

        if ( empty( $element_settings['custom_css'] ) ) {
            return;
        }

        $css = trim( $element_settings['custom_css'] );

        if ( empty( $css ) ) {
            return;
        }
        $css = str_replace( 'selector', $post_css->get_element_unique_selector( $element ), $css );

        // Add a css comment
        $css = sprintf( '/* Start custom CSS for %s, class: %s */', $element->get_name(), $element->get_unique_selector() ) . $css . '/* End custom CSS */';

        $post_css->get_stylesheet()->add_raw_css( $css );
    }

}
