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


if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

/**
 * Elementor 'Contact_Form' widget.
 *
 * Elementor widget that displays an 'Contact_Form' with lightbox.
 *
 * @since 1.0.0
 */
class ContactForm extends Widget_Base {

    /**
     * Get widget name.
     *
     * Retrieve 'Contact_Form' widget name.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'aux_contact_form';
    }

    /**
     * Get widget title.
     *
     * Retrieve 'Contact_Form' widget title.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return __('Contact Form', 'auxin-elements' );
    }

    /**
     * Get widget icon.
     *
     * Retrieve 'Contact_Form' widget icon.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-form-horizontal auxin-badge';
    }

    /**
     * Get widget categories.
     *
     * Retrieve 'Contact_Form' widget icon.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_categories() {
        return array ('auxin-core');
    }

    /**
     * Register 'Contact_Form' widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function _register_controls() {

        /*-----------------------------------------------------------------------------------*/
        /*  contact_section
        /*-----------------------------------------------------------------------------------*/

        $this->start_controls_section(
            'contact_section',
            array(
                'label'      => __('Contact', 'auxin-elements' ),
            )
        );

        $this->add_control(
            'type',
            array(
                'label'       => __('Contact form type','auxin-elements' ),
                'description' => __('Specifies contact form element\'s type. Whether to use built-in form or Contact Form 7.', 'auxin-elements' ),
                'label_block' => true,
                'type'        => Controls_Manager::SELECT,
                'default'     => 'phlox',
                'options'     => array(
                    'phlox' => __('Phlox Contact Form', 'auxin-elements' ),
                    'cf7'   => __('Contact Form 7 plugin', 'auxin-elements' ),
                )
            )
        );

        $this->add_control(
            'cf7_shortcode',
            array(
                'label'       => __('Contact form 7 shortcode','auxin-elements' ),
                'description' => __('Put one of your Contact form 7 shortcodes that you created.','auxin-elements' ),
                'type'        => Controls_Manager::TEXT,
                'condition'    => array(
                    'type' => array('cf7'),
                )
            )
        );

        $this->add_control(
            'email',
            array(
                'label'       => __('Email','auxin-elements' ),
                'description' => __('Email address of message\'s recipient', 'auxin-elements' ),
                'type'        => Controls_Manager::TEXT,
                'input_type'  => 'email',
                'condition'    => array(
                    'type' => array('phlox'),
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
            'type'          => $settings['type'],
            'email'         => $settings['email'],
            'cf7_shortcode' => $settings['cf7_shortcode']
        );

        // get the shortcode base blog page
        echo auxin_widget_contact_form_callback( $args );
    }

}
