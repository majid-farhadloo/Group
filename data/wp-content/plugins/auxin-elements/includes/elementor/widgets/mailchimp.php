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
 * Elementor 'MailChimp' widget.
 *
 * Elementor widget that displays an 'MailChimp' with lightbox.
 *
 * @since 1.0.0
 */
class MailChimp extends Widget_Base {

    /**
     * Get widget name.
     *
     * Retrieve 'MailChimp' widget name.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'aux_mailchimp';
    }

    /**
     * Get widget title.
     *
     * Retrieve 'MailChimp' widget title.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return __('MailChimp', 'auxin-elements' );
    }

    /**
     * Get widget icon.
     *
     * Retrieve 'MailChimp' widget icon.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-mailchimp auxin-badge';
    }

    /**
     * Get forms list.
     *
     * Retrieve 'MailChimp' widget icon.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_forms() {
        $options = array( 0 => __('Select the form to show', 'auxin-elements' ) ) ;

        if( ! function_exists('mc4wp_get_forms') ){
            return $options;
        }

        $forms   = mc4wp_get_forms();
        foreach( $forms as $form ) {
            $options[ $form->ID ] = $form->name;
        }

        return $options;
    }

    /**
     * Get widget categories.
     *
     * Retrieve 'MailChimp' widget icon.
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
     * Register 'MailChimp' widget controls.
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

        $this->start_controls_section(
            'forms_section',
            array(
                'label'      => __('Form', 'auxin-elements' ),
            )
        );

        $this->add_control(
            'form_type',
            array(
                'label'       => __( 'Form Type', 'auxin-elements' ),
                'type'        => Controls_Manager::SELECT,
                'default'     => 'default',
                'options'     => array(
                    'default' => __( 'Defaults'  , 'auxin-elements' ),
                    'custom'  => __( 'Custom'  , 'auxin-elements' )
                )
            )
        );

        $this->add_control(
            'form_id',
            array(
                'label'       => __( 'MailChimp Sign-Up Form', 'auxin-elements' ),
                'label_block' => true,
                'type'        => Controls_Manager::SELECT,
                'default'     => 0,
                'options'     => $this->get_forms(),
                'condition'   => array(
                    'form_type' => array('default')
                )
            )
        );

        $this->add_control(
            'html',
            array(
                'label'       => __( 'Custom Form', 'auxin-elements' ),
                'type'        => Controls_Manager::CODE,
                'language'    => 'html',
                'description' => __( 'Enter your custom form markup', 'auxin-elements' ),
                'condition'   => array(
                    'form_type' => array('custom')
                )
            )
        );

        $this->end_controls_section();
    }

  /**
   * Render mailchimp custom form markup
   *
   *
   * @since 1.0.0
   * @access protected
   */
  public function custom_form( $content ) {
    $settings   = $this->get_settings_for_display();

    if( ! empty( $settings['html'] ) ) {
        $content = $settings['html'];
    }

    return $content;
  }

    /**
    * Render mailchimp widget output on the frontend.
    *
    * Written in PHP and used to generate the final HTML.
    *
    * @since 1.0.0
    * @access protected
    */
    protected function render() {
        // Check whether required resources are available
        if( ! function_exists('mc4wp_show_form') ) {
            auxin_elementor_plugin_missing_notice( array( 'plugin_name' => __( 'MailChimp', 'auxin-elements' ) ) );
            return;
        }

        $settings = $this->get_settings_for_display();

        if(  $settings['form_type'] === 'custom' ) {
            add_filter( 'mc4wp_form_content', array( $this, 'custom_form'), 10, 1 );
            $settings['form_id'] = 0;
        }

        return mc4wp_show_form( $settings['form_id'] );
    }

}
