<?php
/**
 * Master Slider Admin Scripts Class.
 *
 * @
*/

// no direct access allowed
if ( ! defined('ABSPATH') ) {
    die();
}

/**
 *  Class to load and print master slider panel scripts
 */
class AUXELS_Admin_Assets {


  /**
   * __construct
   */
  function __construct() {
        // general assets
        $this->load_styles();
        $this->load_scripts();
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
    //wp_enqueue_script( AUXELS_SLUG .'-admin-scripts', AUXELS_ADMIN_URL . '/assets/js/admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-dialog'), AUXELS_VERSION, true );
  }

}
