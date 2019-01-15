<?php
// admin related functions

// Include advanced metabox tab
require_once( 'metaboxes/metabox-fields-general-advanced.php' );


/**
 * Searchs and removes unexpected fields and sections from metabox hub models
 *
 * @param  array  $models The list of metabox models
 * @param  array  $args   The metabox field and sections which should be dropped
 * @return        List of models
 */
function auxin_remove_from_metabox_hub( $models, $args = array() ){

    if( empty( $models ) ){
        return;
    }

    $defaults = array(
        'model_ids'  => array(), // the list of model IDs to be dropped
        'field_ids'  => array()  // the list of field IDs to be dropped
    );

    $args = wp_parse_args( $args, $defaults );

    $args['model_ids' ] = (array) $args['model_ids'];
    $args['field_ids' ] = (array) $args['field_ids'];

    foreach ( $models as $model_info_index => $model_info ) {
        // if similar field id detected, drop it
        if( in_array( $model_info['model']->id, $args['model_ids' ] )  ){
            unset( $models[ $model_info_index ] );
            continue;
        }

        $fields = $model_info['model']->fields;

        if( ! empty( $fields ) ){
            foreach ( $fields as $field_index => $field ) {
                if( empty( $field["id"] ) ){
                    continue;
                }
                if( in_array( $field["id"], $args['field_ids' ] ) ){
                    unset( $fields[ $field_index ] );
                    $models[ $model_info_index ]['model']->fields = $fields;
                }
            }
        }
    }

    return $models;
}



/*----------------------------------------------------------------------------*/
/*  TGMPA plugin update functions
/*----------------------------------------------------------------------------*/

/**
 * Count the number of bundled plugins having new updates
 *
 * @return int|bool  The umber of plugins having update
 */
function auxin_count_bundled_plugins_have_update(){
    // Check transient
    if ( false === ( $tgmpa_counter = auxin_get_transient( 'auxin_count_bundled_plugins_have_update' ) ) ) {
        // Get instance
        $tgmpa_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
        // Reset Counter
        $tgmpa_counter  = 0;
        // Check plugins list
        foreach ( $tgmpa_instance->plugins as $slug => $plugin ) {
            if ( $plugin['source_type'] === 'bundled' && $tgmpa_instance->is_plugin_active( $slug ) && $tgmpa_instance->does_plugin_have_update( $slug ) ) {
                $tgmpa_counter++;
            }
        }
        auxin_set_transient( 'auxin_count_bundled_plugins_have_update', $tgmpa_counter, 12 * HOUR_IN_SECONDS );
    }

    return $tgmpa_counter;
}

/**
 * Get tgmpa install plugins page
 */
function auxin_get_tgmpa_plugins_page(){
    // Get instance
    $tgmpa_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
    return $tgmpa_instance->install_plugins_page();
}

