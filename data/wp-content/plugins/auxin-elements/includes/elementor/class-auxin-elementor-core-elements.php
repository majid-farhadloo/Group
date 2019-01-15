<?php
namespace Auxin\Plugin\CoreElements\Elementor;


/**
 * Auxin Elementor Elements
 *
 * Custom Elementor extension.
 *
 * 
 * @package    Auxin
 * @license    LICENSE.txt
 * @author     
 * @link       http://phlox.pro/
 * @copyright  (c) 2010-2018 
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

/**
 * Main Auxin Elementor Elements Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.0.0
 */
final class Elements {

    /**
     * Plugin Version
     *
     * @since 1.0.0
     *
     * @var string The plugin version.
     */
    const VERSION = '1.0.0';

    /**
     * Minimum Elementor Version
     *
     * @since 1.0.0
     *
     * @var string Minimum Elementor version required to run the plugin.
     */
    const MINIMUM_ELEMENTOR_VERSION = '2.0.0';

    /**
     * Minimum PHP Version
     *
     * @since 1.0.0
     *
     * @var string Minimum PHP version required to run the plugin.
     */
    const MINIMUM_PHP_VERSION = '5.6.0';

    /**
     * Default elementor dir path
     *
     * @since 1.0.0
     *
     * @var string The defualt path to elementor dir on this plugin.
     */
    private $dir_path = '';


    /**
     * Instance
     *
     * @since 1.0.0
     *
     * @access private
     * @static
     *
     * @var Auxin_Elementor_Core_Elements The single instance of the class.
    */
    private static $_instance = null;

    /**
     * Instance
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @since 1.0.0
     *
     * @access public
     * @static
     *
     * @return Auxin_Elementor_Core_Elements An instance of the class.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
          self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
    }

    /**
     * Initialize the plugin
     *
     * Load the plugin only after Elementor (and other plugins) are loaded.
     *
     * Fired by `plugins_loaded` action hook.
     *
     * @since 1.0.0
     *
     * @access public
    */
    public function init() {

        // Check if Elementor installed and activated
        if ( ! did_action( 'elementor/loaded' ) ) {
            return;
        }

        // Check for required Elementor version
        if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
            add_action( 'admin_notices', array( $this, 'admin_notice_minimum_elementor_version' ) );
            return;
        }

        // Check for required PHP version
        if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
            add_action( 'admin_notices', array( $this, 'admin_notice_minimum_php_version' ) );
            return;
        }

        // Define elementor dir path
        $this->dir_path = AUXELS_INC_DIR . '/elementor';

        // Include core files
        $this->includes();

        // Add required hooks
        $this->hooks();
    }

    /**
     * Include Files
     *
     * Load required core files.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function includes() {
        $this->load_modules();
    }

    /**
     * Add hooks
     *
     * Add required hooks for extending the Elementor.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function hooks() {

        // Register controls, widgets, and categories
        add_action( 'elementor/elements/categories_registered' , array( $this, 'register_categories' ) );
        add_action( 'elementor/widgets/widgets_registered'     , array( $this, 'register_widgets'    ) );
        add_action( 'elementor/controls/controls_registered'   , array( $this, 'register_controls'   ) );

        // Register Widget Styles
        add_action( 'elementor/frontend/after_enqueue_styles'  , array( $this, 'widget_styles' ) );

        // Register Widget Scripts
        add_action( 'elementor/frontend/after_register_scripts', array( $this, 'widget_scripts' ) );

        // Register Admin Scripts
        add_action( 'elementor/editor/before_enqueue_scripts'  , array( $this, 'editor_scripts' ) );
    }

    /**
     * Register widgets
     *
     * Register all auxin widgets which are in widgets list.
     *
     * @access public
     */
    public function register_widgets( $widgets_manager ) {

        $widgets = array(

            /*  Dynamic Elements
            /*-------------------------------------*/
            '10' => array(
                'file'  => $this->dir_path . '/widgets/recent-posts-grid-carousel.php',
                'class' => 'Elements\RecentPostsGridCarousel'
            ),
            '20' => array(
                'file'  => $this->dir_path . '/widgets/recent-posts-masonry.php',
                'class' => 'Elements\RecentPostsMasonry'
            ),
            '30' => array(
                'file'  => $this->dir_path . '/widgets/recent-posts-land-style.php',
                'class' => 'Elements\RecentPostsLand'
            ),
            '40' => array(
                'file'  => $this->dir_path . '/widgets/recent-posts-timeline.php',
                'class' => 'Elements\RecentPostsTimeline'
            ),
            '50' => array(
                'file'  => $this->dir_path . '/widgets/recent-posts-tiles.php',
                'class' => 'Elements\RecentPostsTiles'
            ),
            '60' => array(
                'file'  => $this->dir_path . '/widgets/recent-posts-tiles-carousel.php',
                'class' => 'Elements\RecentPostsTilesCarousel'
            ),
            '70' => array(
                'file'  => $this->dir_path . '/widgets/recent-products.php',
                'class' => 'Elements\RecentProducts'
            ),

            /*  General Elements
            /*-------------------------------------*/
            '88'  => array(
                'file'  => $this->dir_path . '/widgets/heading-modern.php',
                'class' => 'Elements\ModernHeading'
            ),
            '89'  => array(
                'file'  => $this->dir_path . '/widgets/icon.php',
                'class' => 'Elements\Icon'
            ),
            '90'  => array(
                'file'  => $this->dir_path . '/widgets/image.php',
                'class' => 'Elements\Image'
            ),
            '100' => array(
                'file'  => $this->dir_path . '/widgets/gallery.php',
                'class' => 'Elements\Gallery'
            ),
            '110' => array(
                'file'  => $this->dir_path . '/widgets/text.php',
                'class' => 'Elements\Text'
            ),
            '120' => array(
                'file'  => $this->dir_path . '/widgets/divider.php',
                'class' => 'Elements\Divider'
            ),
            '130' => array(
                'file'  => $this->dir_path . '/widgets/button.php',
                'class' => 'Elements\Button'
            ),
            '134' => array(
                'file'  => $this->dir_path . '/widgets/accordion.php',
                'class' => 'Elements\Accordion'
            ),
            '137' => array(
                'file'  => $this->dir_path . '/widgets/tabs.php',
                'class' => 'Elements\Tabs'
            ),
            '140' => array(
                'file'  => $this->dir_path . '/widgets/audio.php',
                'class' => 'Elements\Audio'
            ),
            '150' => array(
                'file'  => $this->dir_path . '/widgets/video.php',
                'class' => 'Elements\Video'
            ),
            '160' => array(
                'file'  => $this->dir_path . '/widgets/quote.php',
                'class' => 'Elements\Quote'
            ),
            '170' => array(
                'file'  => $this->dir_path . '/widgets/testimonial.php',
                'class' => 'Elements\Testimonial'
            ),
            '180' => array(
                'file'  => $this->dir_path . '/widgets/contact-form.php',
                'class' => 'Elements\ContactForm'
            ),
            '190' => array(
                'file'  => $this->dir_path . '/widgets/contact-box.php',
                'class' => 'Elements\ContactBox'
            ),
            '200' => array(
                'file'  => $this->dir_path . '/widgets/touch-slider.php',
                'class' => 'Elements\TouchSlider'
            ),
            '220' => array(
                'file'  => $this->dir_path . '/widgets/before-after.php',
                'class' => 'Elements\BeforeAfter'
            ),
            '230' => array(
                'file'  => $this->dir_path . '/widgets/staff.php',
                'class' => 'Elements\Staff'
            ),
            '240' => array(
                'file'  => $this->dir_path . '/widgets/gmap.php',
                'class' => 'Elements\Gmap'
            ),
            '260' => array(
                'file'  => $this->dir_path . '/widgets/custom-list.php',
                'class' => 'Elements\CustomList'
            ),
            '270' => array(
                'file'  => $this->dir_path . '/widgets/mailchimp.php',
                'class' => 'Elements\MailChimp'
            )

            /*
            '250' => array(
                'file'  => $this->dir_path . '/widgets/search.php',
                'class' => 'Elements\Search'
            )*/

        );

        // sort the widgets by priority number
        ksort( $widgets );

        // making the list of widgets filterable
        $widgets = apply_filters( 'auxin/core_elements/elementor/widgets_list', $widgets, $widgets_manager );

        foreach ( $widgets as $widget ) {
            if( ! empty( $widget['file'] ) && ! empty( $widget['class'] ) ){
                include_once( $widget['file'] );
                if( class_exists( $widget['class'] ) ){
                    $class_name = $widget['class'];
                } elseif( class_exists( __NAMESPACE__ . '\\' . $widget['class'] ) ){
                    $class_name = __NAMESPACE__ . '\\' . $widget['class'];
                } else {
                    auxin_error( sprintf( __('Element class "%s" not found.', 'auxin-elements' ), $class_name ) );
                    continue;
                }
                $widgets_manager->register_widget_type( new $class_name() );
            }
        }
    }

    /**
     * Load Modules
     *
     * Load all auxin elementor modules.
     *
     * @since 1.0.0
     *
     * @access public
     */
    private function load_modules() {

        $modules = array(
            array(
                'file'  => $this->dir_path . '/modules/common.php',
                'class' => 'Modules\Common'
            ),
            array(
                'file'  => $this->dir_path . '/modules/section.php',
                'class' => 'Modules\Section'
            ),
            array(
                'file'  => $this->dir_path . '/modules/column.php',
                'class' => 'Modules\Column'
            )
        );

        foreach ( $modules as $module ) {
            if( ! empty( $module['file'] ) && ! empty( $module['class'] ) ){
                include_once( $module['file'] );

                if( class_exists( __NAMESPACE__ . '\\' . $module['class'] ) ){
                    $class_name = __NAMESPACE__ . '\\' . $module['class'];
                } else {
                    auxin_error( sprintf( __('Module class "%s" not found.', 'auxin-elements' ), $class_name ) );
                    continue;
                }
                new $class_name();
            }
        }
    }


    /**
     * Register controls
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function register_controls( $controls_manager ) {

        $controls = array(
            'aux-visual-select' => array(
                'file'  => $this->dir_path . '/controls/visual-select.php',
                'class' => 'Controls\Control_Visual_Select'
            ),
            'aux-media' => array(
                'file'  => $this->dir_path . '/controls/media-select.php',
                'class' => 'Controls\Control_Media_Select'
            ),
            'aux-icon' => array(
                'file'  => $this->dir_path . '/controls/icon-select.php',
                'class' => 'Controls\Control_Icon_Select'
            )
        );

        foreach ( $controls as $control_type => $control_info ) {
            if( ! empty( $control_info['file'] ) && ! empty( $control_info['class'] ) ){
                include_once( $control_info['file'] );
                if( class_exists( $control_info['class'] ) ){
                    $class_name = $control_info['class'];
                } elseif( class_exists( __NAMESPACE__ . '\\' . $control_info['class'] ) ){
                    $class_name = __NAMESPACE__ . '\\' . $control_info['class'];
                }
                $controls_manager->register_control( $control_type, new $class_name() );
            }
        }
    }

    /**
     * Register categories
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function register_categories( $categories_manager ) {

        $categories_manager->add_category(
            'auxin-core',
            array(
                'title' => sprintf( __( '%s - General', 'auxin-elements' ), '<strong>'. THEME_NAME_I18N .'</strong>' ),
                'icon' => 'eicon-font',
            )
        );

        $categories_manager->add_category(
            'auxin-pro',
            array(
                'title' => sprintf( __( '%s - Featured', 'auxin-elements' ), '<strong>'. THEME_NAME_I18N .'</strong>' ),
                'icon' => 'eicon-font',
            )
        );

        $categories_manager->add_category(
            'auxin-dynamic',
            array(
                'title' => sprintf( __( '%s - Posts', 'auxin-elements' ), '<strong>'. THEME_NAME_I18N .'</strong>' ),
                'icon' => 'eicon-font',
            )
        );

        $categories_manager->add_category(
            'auxin-portfolio',
            array(
                'title' => sprintf( __( '%s - Portfolio', 'auxin-elements' ), '<strong>'. THEME_NAME_I18N .'</strong>' ),
                'icon' => 'eicon-font',
            )
        );

    }

    /**
     * Enqueue styles.
     *
     * Enqueue all the frontend styles.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function widget_styles() {
        // Add auxin custom styles
        wp_enqueue_style( 'auxin-elementor-widgets' , AUXELS_ADMIN_URL . '/assets/css/elementor-widgets.css' );
    }

    /**
     * Enqueue scripts.
     *
     * Enqueue all the frontend scripts.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function widget_scripts() {
        $dependencies = array('jquery', 'auxin-plugins');

        if( defined('MSWP_AVERTA_VERSION') ){
            $dependencies[] = 'masterslider-core';
        }
        wp_enqueue_script( 'auxin-elementor-widgets' , AUXELS_ADMIN_URL . '/assets/js/elementor/widgets.js' , $dependencies, AUXELS_VERSION, TRUE );
    }

    /**
     * Enqueue scripts.
     *
     * Enqueue all the backend scripts.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function editor_scripts() {
        // Auxin Icons
        wp_register_style( 'auxin-front-icon' , THEME_URL . 'css/auxin-icon.css', null, AUXELS_VERSION );
        // Elementor Custom Style
        wp_register_style(  'auxin-elementor-editor', AUXELS_ADMIN_URL . '/assets/css/elementor-editor.css', array(), AUXELS_VERSION );
        // Elementor Custom Scripts
        wp_register_script( 'auxin-elementor-editor', AUXELS_ADMIN_URL . '/assets/js/elementor/editor.js', array(), AUXELS_VERSION );
    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have a minimum required Elementor version.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function admin_notice_minimum_elementor_version() {

        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

        $message = sprintf(
          esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'auxin-elements' ),
          '<strong>' . esc_html__( 'Phlox Core Elements', 'auxin-elements' ) . '</strong>',
          '<strong>' . esc_html__( 'Elementor', 'auxin-elements' ) . '</strong>',
           self::MINIMUM_ELEMENTOR_VERSION
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
    }

    /**
     * Admin notice
     *
     * Warning when the site doesn't have a minimum required PHP version.
     *
     * @since 1.0.0
     *
     * @access public
     */
    public function admin_notice_minimum_php_version() {

        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

        $message = sprintf(
          /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
          esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'auxin-elements' ),
          '<strong>' . esc_html__( 'Phlox Core Elements', 'auxin-elements' ) . '</strong>',
          '<strong>' . esc_html__( 'PHP', 'auxin-elements' ) . '</strong>',
           self::MINIMUM_PHP_VERSION
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
    }

}

Elements::instance();
