<?php
/**
 * Add page option meta box
 *
 * 
 * @package    Auxin
 * @author     averta (c) 2014-2018
 * @link       http://averta.net
*/

/*======================================================================*/

function auxin_push_metabox_models_page( $models ){

    // Attach general common metabox models to hub
    $models[] = array(
        'model'     => auxin_metabox_fields_general_layout(),
        'priority'  => 10
    );
    $models[] = array(
        'model'     => auxin_metabox_fields_general_title(),
        'priority'  => 10
    );
    $models[] = array(
        'model'     => auxin_metabox_fields_general_background(),
        'priority'  => 10
    );
    $models[] = array(
        'model'     => auxin_metabox_fields_general_slider(),
        'priority'  => 10
    );
    return $models;
}

add_filter( 'auxin_admin_metabox_models_page', 'auxin_push_metabox_models_page' );
