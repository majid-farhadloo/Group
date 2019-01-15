<?php
/**
 * Master Slider Admin Scripts Class.
 *
 * 
 * @package    Auxin
 * @license    LICENSE.txt
 * @author     
 * @link       http://phlox.pro/
 * @copyright  (c) 2010-2018 
*/

// no direct access allowed
if ( ! defined('ABSPATH') ) {
    die();
}

/**
 *  Class to load and print master slider panel scripts
 */
class Auxels_Admin_Assets {

    /**
     * __construct
     */
    function __construct() {
        // general assets
        add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
    }

    /**
     * Styles for admin
     *
     * @return void
     */
    public function load_styles() {
        // wp_enqueue_style( AUXELS_SLUG .'-admin-styles',   AUXELS_ADMIN_URL . '/assets/css/msp-general.css',  array(), AUXELS_VERSION );
    }

    /**
     * Scripts for admin
     *
     * @return void
     */
    public function load_scripts() {
        wp_enqueue_script( AUXELS_SLUG .'-admin', AUXELS_ADMIN_URL . '/assets/js/global.js', array('jquery'), AUXELS_VERSION, true );
    }

}
