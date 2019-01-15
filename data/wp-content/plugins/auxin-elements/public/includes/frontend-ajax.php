<?php

/**
 * Load more ajax handler for "Recent Posts Grid" element
 *
 * @return void
 */
function auxels_ajax_handler_element_load_more(){
    if( ! defined( 'AUXIN_INC' ) ){
        wp_send_json_success("Phlox theme is required.");
    }
    if( empty( $_POST["handler"] ) ){
        wp_send_json_success("Please specify a handler.");
    }
    // Direct call is not alloweded
    if( empty( $_POST['action'] ) ){
        wp_send_json_error( __( 'Ajax action not found.', 'auxin-elements' ) );
    }
    if( empty( $_POST['args'] ) ){
        wp_send_json_error( __( 'Ajax args is required.', 'auxin-elements' ) );
    }
    // Authorize the call
    if( ! wp_verify_nonce( $_POST['nonce'], 'auxin_front_load_more' ) ){
        wp_send_json_error( __( 'Authorization failed.', 'auxin-elements' ) );
    }

    $ajax_args      = $_POST['args'];
    $element_markup = '';

    // include the required resources
    require_once( AUXELS_INC_DIR . '/general-functions.php' );
    require_once( THEME_DIR . AUXIN_INC . 'include/functions.php' );
    require_once( THEME_DIR . AUXIN_INC . 'include/templates/templates-post.php' );

    // take required actions based on custom handler (element base name)
    switch( $_POST['handler'] ) {

        case 'aux_recent_posts':
            require_once( AUXELS_INC_DIR . '/elements/recent-posts-grid-carousel.php'    );

            // Get the element markup
            $element_markup = auxin_widget_recent_posts_callback( $ajax_args );
            break;

        case 'aux_recent_posts_land_style':
            require_once( AUXELS_INC_DIR . '/elements/recent-posts-land-style.php'    );

            // Get the element markup
            $element_markup = auxin_widget_recent_posts_land_style_callback( $ajax_args );
            break;

        case 'aux_recent_posts_masonry':
            require_once( AUXELS_INC_DIR . '/elements/recent-posts-masonry.php'    );

            // Get the element markup
            $element_markup = auxin_widget_recent_posts_masonry_callback( $ajax_args );
            break;

        case 'aux_recent_posts_tiles':
            require_once( AUXELS_INC_DIR . '/elements/recent-posts-tiles.php'    );

            // Get the element markup
            $element_markup = auxin_widget_recent_posts_tiles_callback( $ajax_args );
            break;

        case 'aux_recent_posts_timeline':
            require_once( AUXELS_INC_DIR . '/elements/recent-posts-timeline.php'    );

            // Get the element markup
            $element_markup = auxin_widget_recent_posts_timeline_callback( $ajax_args );
            break;

        case 'aux_recent_news':
            require_once( AUXNEW_INC_DIR . '/elements/recent-news.php'    );

            // Get the element markup
            $element_markup = auxin_widget_recent_news_callback( $ajax_args );
            break;            

        case 'aux_recent_news_big_grid':
            require_once( AUXNEW_INC_DIR . '/elements/recent-news-big-grid.php'    );

            // Get the element markup
            $element_markup = auxin_widget_recent_news_big_grid_callback( $ajax_args );
            break;   

        case 'aux_recent_portfolios_grid':
            require_once( AUXPFO_INC_DIR . '/elements/recent-portfolios.php'    );

            // Get the element markup
            $element_markup = auxin_widget_recent_portfolios_grid_callback( $ajax_args );
            break;            

        default:
            wp_send_json_error( __( 'Not a valid handler.', 'auxin-elements' ) );
            break;
    }

    // if the output is empty
    if( empty( $element_markup ) ){
        wp_send_json_error( __( 'No data received.', 'auxin-elements' ) );
    }

    wp_send_json_success( $element_markup );
}

add_action( 'wp_ajax_load_more_element', 'auxels_ajax_handler_element_load_more' );
add_action( 'wp_ajax_nopriv_load_more_element', 'auxels_ajax_handler_element_load_more' );