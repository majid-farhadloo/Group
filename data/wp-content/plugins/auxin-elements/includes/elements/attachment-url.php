<?php
/**
 * Retrieves attachment URL
 *
 * 
 * @package    Auxin
 * @license    LICENSE.txt
 * @author     
 * @link       http://phlox.pro/
 * @copyright  (c) 2010-2018 
 */

add_shortcode( 'aux_attach_url', 'auxin_shortcode_attach_id' );

function auxin_shortcode_attach_id( $atts, $content = null ) {
    extract( shortcode_atts( array( 'id' => '' ) , $atts ) );
    if( empty( $id ) ){
        return '';
    }
    return wp_get_attachment_url( $id );
}

