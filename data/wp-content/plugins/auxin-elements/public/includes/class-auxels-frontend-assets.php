<?php
/**
 * Load frontend scripts and styles
 *
 * 
 * @package    Auxin
 * @license    LICENSE.txt
 * @author     
 * @link       http://phlox.pro/
 * @copyright  (c) 2010-2018 
 */

/**
* Constructor
*/
class AUXELS_Frontend_Assets {


	/**
	 * Construct
	 */
	public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'load_assets'  ) );
	}

    /**
     * Styles for admin
     *
     * @return void
     */
    public function load_assets() {

        if( $google_map_api_key = auxin_get_option( 'auxin_google_map_api_key') ){
            wp_enqueue_script( 'mapapi', esc_url( set_url_scheme( 'http://maps.googleapis.com/maps/api/js?v=3&key='. $google_map_api_key ) ) , null, null, TRUE );
        }

        //wp_enqueue_style( AUXELS_SLUG .'-main',   AUXELS_PUB_URL . '/assets/css/main.css',  array(), AUXELS_VERSION );
        wp_enqueue_script( AUXELS_SLUG .'-plugins', AUXELS_PUB_URL . '/assets/js/plugins.min.js', array('jquery'), AUXELS_VERSION, true );
    }

}
return new AUXELS_Frontend_Assets();





