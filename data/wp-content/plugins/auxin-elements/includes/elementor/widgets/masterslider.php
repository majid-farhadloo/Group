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
class MasterSlider extends Widget_Base {

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
        return 'aux_masterslider';
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
        return __('Master Slider', 'auxin-elements' );
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
        return 'eicon-slider-device auxin-badge';
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
        $options = array(  0 => __( 'Select slider', 'auxin-elements' ) ) ;

        if( ! function_exists('get_masterslider_names') ){
            return $options;
        }

        $masterslider_names = get_masterslider_names( 'title-alias' );

        foreach ( $masterslider_names as $key => $value ) {
            $options[$value] = $key;
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
            'alias',
            array(
                'label'       => __( 'Master Slider', 'auxin-elements' ),
                'label_block' => true,
                'type'        => Controls_Manager::SELECT,
                'options'     => $this->get_forms(),
                'default'     => 0,
            )
        );

        $this->end_controls_section();

        // Auxin hook for custom register controls
        do_action( 'auxin/elementor/register_controls', $this );

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
    $settings = $this->get_settings_for_display();

    if( ! function_exists('get_masterslider_names') ) {
        ob_start();
    ?>
        <div class="elementor-alert elementor-alert-danger" role="alert">
            <span class="elementor-alert-title">
                <?php echo sprintf( esc_html__( '%s Is Not Activated!', 'auxin-elements' ), __( 'Master Slider', 'auxin-elements' ) ); ?>
            </span>
            <span class="elementor-alert-description">
                <?php esc_html_e( 'In order for the element to work, you must install and activate the plugin for it.', 'auxin-elements' ); ?>
            </span>
        </div>
    <?php
        echo ob_get_clean();
    } else {
        
        return masterslider( $settings['alias'] );
    }

  }

}
