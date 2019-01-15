<?php
/**
 * General Hooks
 *
 * 
 * @package    Auxin
 * @author     averta (c) 2014-2018
 * @link       http://averta.net
 */

/**
 * Defining default font if custom typography was not enabled
 *
 * @param  array  $default_fonts The list of default fonts
 * @return array                 The list of default fonts after modification
 */
function auxin_define_default_fonts( $default_fonts ){
    // If custom typography was not enabled, use default fonts
    if( ! auxin_get_option( 'enable_custom_typography', 0 ) ){
        $default_fonts['auxin_body_font'] = '_gof_Raleway:regular';
    }
    return $default_fonts;
}

add_filter( 'auxin_get_default_fonts_info', 'auxin_define_default_fonts' );

