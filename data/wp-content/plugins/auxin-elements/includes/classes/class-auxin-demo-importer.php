<?php
/**
 * Demo Importer for auxin framework
 *
 * 
 * @package    Auxin
 * @license    LICENSE.txt
 * @author     
 * @link       http://phlox.pro/
 * @copyright  (c) 2010-2018 
*/

// no direct access allowed
if ( ! defined('ABSPATH') )  exit;

class Auxin_Demo_Importer {

    /**
     * Instance of this class.
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Return an instance of this class.
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }


    function __construct() {

        add_action( 'wp_ajax_auxin_demo_data'   , array( $this, 'import') );
        add_action( 'wp_ajax_import_step'   , array( $this, 'import_step') );

    }

    /**
     * Main Import
     *
     *
     * @return  JSON
     */
    public function import() {

        $demo_ID = $_POST['ID'];

        if ( ! wp_verify_nonce( $_POST['verify'], 'aux-import-demo-' . $demo_ID ) ) {
            // This nonce is not valid.
            wp_send_json_error( array( 'message' => __( 'Invalid Nonce', 'auxin-elements' ) ) );
        }

        $data = $this->parse( 'http://api.phlox.pro/demos/get/' . $demo_ID );

        update_option( 'auxin_demo_data', $data );

        if ( ! empty( $data ) ) {

            $get_options = $_POST['options'];
            foreach ( $get_options as $key => $value ) {
                $options[ $value['name'] ] = $value['value'];
            }

            update_option( 'auxin_demo_options', $options );

            update_option( 'auxin_last_imported_demo', array( 'id' => $demo_ID, 'time' => current_time( 'mysql' ), 'status' => $options ) );

            flush_rewrite_rules();

            wp_send_json_success();
        }

        wp_send_json_error(  array( 'message' => __( 'Oops! Something went wrong.', 'auxin-elements' ) ) );

    }

    public function import_step() {

        if ( ! isset( $_POST['step'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Step Failed!', 'auxin-elements' ) ) );
        }

        $step    = $_POST['step'];
        $index   = isset( $_POST['index'] ) ? $_POST['index'] : '';
        $index   = $index === '' ? 0 : $index;

        $data    = get_option( 'auxin_demo_data', false );
        $options = get_option( 'auxin_demo_options', false );

        switch ( $step ) {
            case 'download':
                if ( 'complete' === $options['import']
                || ( 'custom' === $options['import'] && ( isset( $options['media'] ) && 'on' === $options['media'] ) ) ) {

                    // change to current node
                    $index++;
                    if( is_array( $data['media'] ) && $posts_number = count( $data['media'] ) ){

                        if( $index == 1 ){
                            $requests = $this->prepare_download( $data['media'] );
                        } else {
                            $requests = get_option( 'auxin_demo_media_requests' );
                        }

                        if( $index <= $posts_number ){
                            $this->download( array_slice( $requests, $index - 1, 1 ) );

                            if( $index < $posts_number ){
                                wp_send_json_success( array( 'message' => __( 'Downloading Medias', 'auxin-elements' ). ' ' . $index . '/' . $posts_number, 'next' => 'download', 'index' => $index ) );
                            }
                        }
                    }

                }
                wp_send_json_success( array( 'step' => 'download', 'next' => 'media', 'message' => __( 'Importing Media', 'auxin-elements' ) ) );

            case 'media':
                if ( 'complete' === $options['import']
                || ( 'custom' === $options['import'] && ( isset( $options['media'] ) && 'on' === $options['media'] ) ) ) {
                    return $this->import_media( $data['media'] );
                }

            case 'content':
                if ( 'complete' === $options['import']
                || ( 'custom' === $options['import'] && ( isset( $options['posts'] ) && 'on' === $options['posts'] ) ) ) {

                    // change to current node
                    $index++;
                    if( is_array( $data['content'] ) && $posts_number = count( $data['content'] ) ){
                        if( $index <= $posts_number ){
                            $this->import_posts( array_slice( $data['content'], $index - 1, 1 ) );
                            if( $index < $posts_number ){
                                wp_send_json_success( array( 'message' => __( 'Importing Contents', 'auxin-elements' ). ' '. $index . '/' . $posts_number, 'next' => 'content', 'index' => $index ) );
                            }
                        }
                    }

                }
                wp_send_json_success( array( 'step' => 'content', 'message' => __( 'Importing Options', 'auxin-elements' ), 'next' => 'auxin_options' ) );

            case 'auxin_options':
                if ( 'complete' === $options['import']
                || ( 'custom' === $options['import'] && ( isset( $options['options'] ) && 'on' === $options['options'] ) ) ) {
                    return $this->import_options( $data['auxin_options'], $data['site_options'], $data['theme_mods'], $data['plugin_options'] );
                }

            case 'menus':
                if ( 'complete' === $options['import']
                || ( 'custom' === $options['import'] && ( isset( $options['menus'] ) && 'on' === $options['menus'] ) ) ) {

                    $index++;
                    if( is_array( $data['menus'] ) && $menu_number = count( $data['menus'] ) ){
                        if( $index <= $menu_number ){
                            $this->import_menus( array_slice( $data['menus'], $index - 1, 1 ) );
                            if( $index < $menu_number ){
                                wp_send_json_success( array( 'message' => __( 'Importing Menus', 'auxin-elements' ). ' '. $index . '/' . $menu_number, 'next' => 'menus', 'index' => $index ) );
                            }
                        }
                    }

                }
                wp_send_json_success( array( 'step' => 'menus', 'next' => 'widgets', 'message' => __( 'Importing Widgets', 'auxin-elements' ) ) );

            case 'widgets':
                if ( 'complete' === $options['import']
                || ( 'custom' === $options['import'] && ( isset( $options['widgets'] ) && 'on' === $options['widgets'] ) ) ) {
                    return $this->import_widgets( $data['widgets'], $data['widgets_data'] );
                }

            case 'masterslider':
                if ( 'complete' === $options['import']
                || ( 'custom' === $options['import'] && ( isset( $options['masterslider'] ) && 'on' === $options['masterslider'] ) )
                && isset( $data['masterslider']['sliders'] ) ) {
                    return $this->import_sliders( $data['masterslider']['sliders'] );
                }

            case 'stylesheet':
                return $this->import_stylesheet( $data['custom_stylesheet'] );
        }
    }

    /**
     * Parse url
     *
     * @param   String $url
     *
     * @return  Array
     */
    public function parse( $url ) {
        //Get JSON
        $request    = wp_remote_get( $url,
            array(
                'timeout'     => 30,
                'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0'
            )
        );
        //If the remote request fails, wp_remote_get() will return a WP_Error
        if( is_wp_error( $request ) || ! current_user_can( 'import' ) ){
            wp_send_json_error( array( 'message' => __( 'Remote Request Fails', 'auxin-elements' ) ) );
        }

        //proceed to retrieving the data
        $body       = wp_remote_retrieve_body( $request );
        // Check for error
        if ( is_wp_error( $body ) ) {
            wp_send_json_error( array( 'message' => __( 'Retrieve Body Fails', 'auxin-elements' ) ) );
        }

        //translate the JSON into Array
        return json_decode( $body, true );
    }

    // Importers
    // =====================================================================

    /**
     * Import options ( Customizer & Site Options )
     *
     * @param   array $auxin_options
     * @param   array $site_options
     * @param   array $theme_mods
     *
     * @return  String
     */
    public function import_options( array $auxin_options, array $site_options, array $theme_mods, array $plugin_options ) {

        $auxin_custom_images   = $this->get_options_by_type( 'image' );
        $auxin_exclude_options = array( 'auxin_google_map_api_key' );

        foreach ( $auxin_options as $auxin_key => $auxin_value ) {
            if ( in_array( $auxin_key, $auxin_custom_images ) && ! empty( $auxin_value ) ) {
                // This line is for changing the old attachment ID with new one.
                $auxin_value    = $this->get_attachment_id( 'auxin_import_id', $auxin_value );
            }
            if( in_array( $auxin_key, $auxin_exclude_options ) ){
                // Exclude this items
                continue;
            }
            // Update exclusive auxin options
            auxin_update_option( $auxin_key , $auxin_value);
        }

        foreach ( $site_options as $site_key => $site_value ) {
            // If option value is empty, continue...
            if ( empty( $site_value ) ) continue;
            // Else change some values :)
            if( $site_key === 'page_on_front' || $site_key === 'page_for_posts' ) {
                // Retrieves page object given its title.
                $page           = get_page_by_title( $site_value );
                // Set $site_value to page ID
                $site_value     = is_object( $page ) ? $page->ID : NULL;
            }
            // Finally update options :)
            update_option( $site_key, $site_value );
        }

        foreach ( $theme_mods as $theme_mods_key => $theme_mods_value ) {
            // Start theme mods loop:
            if( $theme_mods_key === 'custom_logo' ) {
                // This line is for changing the old attachment ID with new one.
                $theme_mods_value = $this->get_attachment_id( 'auxin_import_id', $theme_mods_value );
            }
            // Update theme mods
            set_theme_mod( $theme_mods_key , $theme_mods_value );
        }

        foreach ( $plugin_options as $plugin => $options ) {
            foreach ( $options as $option => $value) {
                update_option( $option, $value );
            }
        }

        // Stores css content in custom css file
        auxin_save_custom_css();
        // Stores JavaScript content in custom js file
        auxin_save_custom_js();

        wp_send_json_success( array( 'step' => 'options', 'next' => 'menus', 'message' => __( 'Importing Menus', 'auxin-elements' ) ) );

    }

    /**
     * Import widgets data
     *
     * @param   array $widgets
     * @param   array $widgets_data
     *
     * @return  String
     */
    public function import_widgets( array $widgets, array $widgets_data ) {

        if ( ! function_exists( 'wp_get_sidebars_widgets' ) ) {
            require_once ABSPATH . WPINC . '/widgets.php';
        }

        $default_widgets = array();

        $widgets_data_str = wp_json_encode($widgets_data);

        preg_match_all( '/\s*"nav_menu"\s*:\s*(\d*)\s*/', $widgets_data_str, $matchs, PREG_SET_ORDER );

        foreach ( $matchs as $match ) {
            $new_menu_id = get_option( '_auxin_demo_menu_old_id_' . $match[1] );
            $new_widgets_data = str_replace( $match[1], $new_menu_id, $match[0] );
            $widgets_data_str = str_replace( $match[0], $new_widgets_data, $widgets_data_str );
        }

        preg_match_all( '/\s*"attach_id"\s*:\s*(\d*)\s*/', $widgets_data_str, $matchs, PREG_SET_ORDER );

        foreach ( $matchs as $match ) {
            $new_image_id = $this->get_attachment_id( 'auxin_import_id', $match[1] );
            $new_widgets_data = str_replace( $match[1], $new_image_id, $match[0] );
            $widgets_data_str = str_replace( $match[0], $new_widgets_data, $widgets_data_str );
        }

        $widgets_data = json_decode( $widgets_data_str, true );


        // Import widgets
        foreach (  $widgets as $key => $value ) {
            $default_widgets[$key]  = $value;
        }
        // Replace new widgets with old ones.
        wp_set_sidebars_widgets( $default_widgets );

        // Import widgets data
        foreach ( $widgets_data as $data_key => $data_values ) {

            foreach ( $data_values as $counter => $options ) {
                // This line is for changing the old attachment ID with new one.
                if( isset( $options['about_image'] ) ) {
                    $data_values[$counter]['about_image'] = $this->get_attachment_id( 'auxin_import_id', $options['about_image'] );
                }

            }
            // Finally update widgets data.
            update_option( $data_key, $data_values );
        }

        wp_send_json_success( array( 'step' => 'widgets', 'next' => 'masterslider', 'message' => __( 'Importing Sliders', 'auxin-elements' ) ) );

    }

    /**
     * Import menus data
     *
     * @param   array $args
     *
     * @return  Boolean
     */
    public function import_menus( array $args ) {

        foreach ( $args as $menu_name => $menu_data ) {

            $menu_exists = wp_get_nav_menu_object( $menu_name );
            update_option( '_auxin_demo_menu_old_id_' . $menu_data['id'], $menu_exists );

            // If it doesn't exist, let's create it.
            if( ! $menu_exists ) {

                $menu_id = wp_create_nav_menu( $menu_name );
                if( is_wp_error( $menu_id ) ) continue;

                update_option( '_auxin_demo_menu_old_id_' . $menu_data['id'], $menu_id );
                // Create menu items
                foreach ( $menu_data['items'] as $item_key => $item_value ) {
                    //Keep 'menu-meta' in a variable
                    $meta_data = $item_value['menu-meta'];
                    // $post_name = isset( $item_value['menu-item-object-id'] ) ? $item_value['menu-item-object-id'] : '';
                    //remove Non-standard items from nav_menu input array
                    unset( $item_value['menu-meta']             );
                    unset( $item_value['menu-item-attr-title']  );
                    unset( $item_value['menu-item-classes']     );
                    unset( $item_value['menu-item-description'] );

                    switch ( $item_value['menu-item-type'] ) {
                        case 'post_type':
                            $item_value['menu-item-object-id'] = $this->get_meta_post_id( 'auxin_import_post', $item_value['menu-item-object-id'] );
                            unset( $item_value['menu-item-url'] );
                            break;
                        case 'taxonomy':
                            $get_term       = get_term_by( 'name', $item_value['menu-item-title'], $item_value['menu-item-object'] );
                            $item_value['menu-item-object-id'] = is_object( $get_term ) ? (int) $get_term->term_id : 0;
                            unset( $item_value['menu-item-url'] );
                            break;
                        default:
                            if( strpos( $item_value['menu-item-url'], '{{demo_home_url}}' ) ) {
                                $item_value['menu-item-url'] = parse_url ( str_replace( "{{demo_home_url}}", get_site_url(), $item_value['menu-item-url'] ) );
                            }
                            $item_value['menu-item-object-id'] = 0;
                    }

                    // Import menu item
                    $item_id    = wp_update_nav_menu_item( $menu_id, 0, $item_value );
                    $post_id = $this->get_meta_post_id( 'page_header_menu', strval( $menu_data['id'] ) );

                    update_post_meta( $post_id, 'page_header_menu', $menu_id );

                    if ( is_wp_error( $item_id ) ) {
                        continue;
                    }

                    //Add 'meta-data' options for menu items
                    foreach ($meta_data as $meta_key => $meta_value) {

                        switch ( $meta_key ) {
                            case '_menu_item_object_id':
                                // Create a flag transient
                                set_transient( 'auxin_menu_item_old_parent_id_' . $meta_value, $item_id, 3600 );
                                // Change exporter's object ID value
                                switch ( $item_value['menu-item-type'] ) {
                                    case 'post_type':
                                    case 'taxonomy':
                                        $meta_value = $item_value['menu-item-object-id'];
                                        break;
                                }
                                break;

                            case '_menu_item_menu_item_parent':
                                if( (int) $meta_value != 0 ) {
                                    $meta_value     = get_transient( 'auxin_menu_item_old_parent_id_' . $meta_value );
                                }
                                break;
                            case '_menu_item_url':
                                if( ! empty( $meta_value ) ) {
                                    $meta_value     = $item_value['menu-item-url'];
                                }
                                break;
                        }

                        update_post_meta( $item_id, $meta_key, $meta_value );
                    }
                }

                if( is_array( $menu_data['location'] ) ) {
                    // Putting up menu locations on theme_mods_phlox
                    $locations = get_theme_mod( 'nav_menu_locations' );
                    foreach ( $menu_data['location'] as $location_id => $location_name ) {
                        $locations[$location_name] = $menu_id;
                    }
                    set_theme_mod( 'nav_menu_locations', $locations );
                }

            }

        }

        // wp_send_json_success( array( 'step' => 'menus', 'next' => 'widgets', 'message' => __( 'Importing Widgets', 'auxin-elements' ) ) );

    }

    /**
     * Flush post data
     *
     * @param   Integer $post_id
     *
     * @return  String
     */
    private function maybe_flush_post( $post_id ){
        if( class_exists( '\Elementor\Core\Files\CSS\Post' ) && get_post_meta( $post_id, '_elementor_version', true ) ){
            $post_css_file = new \Elementor\Core\Files\CSS\Post( $post_id );
            $post_css_file->update();
        }
    }

    /**
     * Import posts data
     *
     * @param   array $args
     *
     * @return  String
     */
    public function import_posts( array $args ) {

        foreach ( $args as $slug => $post ) {

            // If there is no post_type, then continue loop...
            if ( ! post_type_exists( $post['post_type'] ) ) {
                continue;
            }

            // Check post existence
            if( $this->post_exists( $post['post_title'], $post['ID'] ) ) {
                continue;
            }

            $content    = $this->shortcode_process( base64_decode( $post['post_content'] ) );
            $author_id  = get_current_user_id();

            // Add slashes to a string or array of custom_css
            if( $post['post_type'] == 'custom_css' ) {
                $content = wp_slash( $content );
            }

            $post_id = wp_insert_post(
                array(
                    'post_title'        => sanitize_text_field( $post['post_title'] ),
                    'post_content'      => $content,
                    'post_excerpt'      => $post['post_excerpt'],
                    'post_date'         => $post['post_date'],
                    'post_password'     => $post['post_password'],
                    'post_parent'       => $post['post_parent'],
                    'post_type'         => $post['post_type'],
                    'post_author'       => $author_id,
                    'post_status'       => 'publish',
                )
            );

            if ( ! is_wp_error( $post_id ) ) {

                //Check post terms existence
                if ( ! empty( $post['post_terms'] ) ){
                    // Start adding post terms
                    foreach ( $post['post_terms'] as $tax => $term ) {

                        if( $tax === 'post_format' ) {
                            // Get post_format key value
                            $term = array_keys( $term );
                            // Set post format (Video, Audio, Gallery, ...)
                            set_post_format( $post_id , $term[0] );

                        } else {

                            // If taxonomy not exists, then continue loop...
                            if( ! taxonomy_exists( $tax ) ){
                                continue;
                            }

                            $add_these_terms = array();

                            foreach ($term as $key => $value) {

                                $term               = term_exists( $key, $tax );
                                $add_these_terms[]  = intval($term['term_id']);

                                // If the taxonomy doesn't exist, then we create it
                                if ( ! $term ) {

                                    // Get parent term
                                    $parent_term    = $value != "0" ? get_term_by( 'name', $value, $tax ) : (object) array( 'term_id' => "0" );
                                    $parent_term_ID = isset( $parent_term->term_id ) ? (int) $parent_term->term_id : 0 ;
                                    $term_args      = $parent_term_ID ? array( 'parent' => $parent_term_ID ) : array();

                                    $term = wp_insert_term(
                                        $key,
                                        $tax,
                                        $term_args
                                    );

                                    if ( is_wp_error( $term ) ) {
                                        continue;
                                    }

                                    $add_these_terms[]  = intval($term['term_id']);
                                }

                            }

                            // Add post terms
                            wp_set_post_terms( $post_id, $add_these_terms, $tax, true );
                        }

                    }

                }

                if ( ! empty( $post['post_meta'] ) ){
                    // Add post meta data
                    foreach ( $post['post_meta'] as $meta_key => $meta_value ) {
                        // Unserialize when data is serialized
                        $meta_value = maybe_unserialize( $meta_value );

                        switch ( $meta_key ) {
                            case '_panels_data_preview':
                            case 'panels_data'  :
                                $meta_value = $this->siteorigin_data_update( $meta_value );
                                $auxin_custom_images    = $this->get_widget_by_type( array('attach_image', 'attach_images', 'aux_select_video', 'aux_select_audio') );
                                foreach ( $meta_value['widgets'] as $widgets_key => $widgets_value ) {
                                    foreach ($widgets_value as $panel_key => $panel_value) {
                                        if ( in_array( $panel_key, $auxin_custom_images ) && ! empty( $panel_key ) ) {
                                            $meta_value['widgets'][$widgets_key][$panel_key] = $this->update_gallery_ids( $panel_value );
                                        } elseif( $panel_key == 'cf7_shortcode' ) {
                                            $meta_value['widgets'][$widgets_key][$panel_key] = $this->shortcode_process( $panel_value );
                                        }
                                    }
                                }
                                break;

                            case '_thumbnail_id' :
                            case '_thumbnail_id2':
                            case '_format_audio_attachment':
                            case '_format_video_attachment':
                            case '_format_video_attachment_poster':
                            case '_format_gallery_type':
                            case 'aux_custom_bg_image':
                            case 'aux_title_bar_bg_image':
                            case 'aux_title_bar_bg_video_mp4':
                            case 'aux_title_bar_bg_video_ogg':
                            case 'aux_title_bar_bg_video_webm':
                            case '_product_image_gallery':
                                $meta_value    = $this->update_gallery_ids( $meta_value );
                                break;
                            case '_elementor_data':
                                // Update elementor data
                                $meta_value = $this->update_elementor_data( $meta_value );
                                // We need the `wp_slash` in order to avoid the unslashing during the `update_post_meta`
                                $meta_value = wp_slash( $meta_value );
                                break;
                            case 'aux_custom_logo':
                            case 'aux_custom_logo2':
                            case 'page_secondary_logo_image':
                                $meta_value = $this->get_attachment_id( 'auxin_import_id', $meta_value );
                                break;
                        }

                        update_post_meta( $post_id, $meta_key, $meta_value );
                    }
                }

                // Set default_form_id for mailchimp plugin
                if( $post['post_type'] == 'mc4wp-form' ){
                    // set default_form_id
                    update_option( 'mc4wp_default_form_id', $post_id );
                }

                if ( ! empty( $post['comments'] ) ){
                    // Add post comments
                    foreach ( $post['comments'] as $comment_key => $comment_values ) {
                        $comment_values['comment_post_ID']      = $post_id;
                        $comment_old_ID                         = $comment_values['comment_ID'];

                        if ( $comment_values['comment_parent'] != 0 ) {
                            $comment_values['comment_parent']   = get_transient( 'auxin_comment_new_comment_id_' . $comment_values['comment_parent'] );
                        }

                        unset( $comment_values['comment_ID'] );
                        $comment_ID = wp_insert_comment( $comment_values );
                        if ( is_wp_error( $comment_ID ) ) {
                            continue;
                        } else {
                            set_transient( 'auxin_comment_new_comment_id_' . $comment_old_ID, $comment_ID, 3600 );
                        }
                    }
                }

                //Add auxin meta flag
                add_post_meta( $post_id,  'auxin_import_post', $post['ID'] );

                if( $post['post_thumb'] != "" ){
                    /* Get Attachment ID */
                    $attachment_id    = $this->get_attachment_id( 'auxin_import_id', $post['post_thumb'] );

                    if ( $attachment_id ) {
                        set_post_thumbnail( $post_id, $attachment_id );
                    }

                }

                $this->maybe_flush_post( $post_id );

                // Trash the default WordPress Post, "Hello World," which has an ID of '1'.
                wp_trash_post( 1 );

            } else {

                continue;
            }

        }

        //wp_send_json_success( array( 'step' => 'content', 'next' => 'auxin_options', 'message' => __( 'Importing Options' ) ) );
    }


    public function prepare_download( array $args ) {

        $tmpname = $responses = $requests = array();

        // Preparing requests
        foreach ( $args as $import_id => $import_url ) {

            if ( $this->attachment_exist( pathinfo( $import_url['url'], PATHINFO_BASENAME ) ) ) {
                continue;
            }

            $url_filenames = basename( parse_url( $import_url['url'], PHP_URL_PATH ) );

            if ( ! isset( $tmpname[$import_url['url']] ) ) {
                $tmpname[$import_url['url']] = wp_tempnam( $url_filenames );
            }

            $requests[$import_url['url']] = array( 'url' => $import_url['url'], 'options' => array( 'timeout' => 300, 'stream' => true, 'filename' => $tmpname[$import_url['url']] ) );
            $args[$import_id]['tmp_name'] = $tmpname[$import_url['url']];

        }

        update_option( 'auxin_demo_media_args', $args );
        update_option( 'auxin_demo_media_requests', $requests );

        return $requests;
    }


    public function download( array $requests ) {

        if( ! empty( $requests ) ) {
            // Split requests
            return Requests::request_multiple( $requests );
        }

    }


    public function import_media() {
        $args = get_option( 'auxin_demo_media_args', false );
        // Process moving temp files and insert attachments
        foreach ( $args as $import_id => $import_url ) {
            if( ! isset( $import_url['tmp_name'] ) || empty( $import_url['tmp_name'] ) ) {
                continue;
            }
            $path = isset( $import_url['path'] ) ? $import_url['path'] : '';
            $this->insert_attachment( $import_id, $import_url['url'], $import_url['tmp_name'] , $path );
        }
        delete_option('auxin_demo_media_args');

        wp_send_json_success( array( 'step' => 'media', 'next' => 'content', 'message' => __( 'Importing Contents', 'auxin-elements' ) ) );
    }


    /**
     * Import custom stylesheet file
     *
     * @param   array $args
     *
     * @return  String
     */
    public function import_stylesheet( $file_url ) {

        if( empty( $file_url ) ){
            auxin_update_option( 'special_css_file_enabled', 0 );
        } elseif ( $basename = $this->insert_file( $file_url ) ) {
            auxin_update_option( 'special_css_file_enabled', 1 );
            auxin_update_option( 'special_css_file_name', $basename );
        }

        wp_send_json_success( array( 'step' => 'stylesheet', 'next' => 'final', 'message' => __( 'Preparing Site ...', 'auxin-elements' ) ) );
    }

    /**
     * Import master slider
     *
     * @param   array $args
     *
     * @return  String
     */
    public function import_sliders( $sliders ) {

        if ( class_exists( 'MSP_DB' ) && ! empty( $sliders ) ) {

            $ms_db = new MSP_DB;

            foreach ( $sliders as $slider ) {

                if ( isset( $slider['ID'] ) ) {
                    unset( $slider['ID'] );
                }

                $ms_db->add_slider( $slider );

            }

            if( function_exists( 'msp_save_custom_styles' ) ) {
                msp_save_custom_styles();
            }

        }

        wp_send_json_success( array( 'step' => 'masterslider', 'next' => 'stylesheet', 'message' => __( 'Importing Stylesheets', 'auxin-elements' ) ) );

    }

    // Custom Functionalities
    // =====================================================================

    /**
     * This will changing the old attachment IDs with new ones
     *
     * @param   string  $value
     *
     * @return  string
     */
    public function update_gallery_ids( $value ) {
        // This line is for changing the old attachment ID with new one.
        if( strpos( $value, ',' ) !== false ) {
            $value   = explode( ",", $value );
            $gallery = array();
            foreach ( $value as $gallery_key => $gallery_value ) {
                if ( $get_new_attachment = $this->get_attachment_id( 'auxin_import_id', $gallery_value ) ) {
                    $gallery[]   = $get_new_attachment;
                }
            }
            return implode( ",", $gallery );
        } else {
            return $this->get_attachment_id( 'auxin_import_id', $value );
        }

    }

    /**
     * Get options (ID) by type
     *
     * @param   string  $type
     * @param   array   $output
     *
     * @return  array | empty array
     */
    public function get_options_by_type( $type, $output = array() ) {

        $get_options    = auxin_get_defined_options();

        foreach ( $get_options['fields'] as $key => $value ) {
            if ( ! array_search(  $type, $value ) ) {
                continue;
            }
            $output[]   = $value['id'];
        }

        return $output;

    }

    /**
     * Get page builder (param_name) by type
     *
     * @param   string  $type
     * @param   array   $output
     *
     * @return  array | empty array
     */
    public function get_widget_by_type( array $type, $output = array() ) {

        $get_widgets    = Auxin_Widget_Shortcode_Map::get_instance()->get_master_array();

        foreach ( $get_widgets as $key => $value ) {
            foreach ( $value['params'] as $params_key => $params_value ) {
                if ( ! in_array( $params_value['type'], $type ) ) {
                    continue;
                }
                $output[]   = $params_value['param_name'];
            }
        }

        return $output;

    }

    /**
     * An attractive function to change the values of old IDs in the shortcode attributes.
     *
     * @param   string $content
     *
     * @return  String
     */
    public function shortcode_process( $content ) {
        // Return if not contain Shortcode
        if ( false === strpos( $content, '[' ) ) {
            return $content;
        }

        // Make a copy of content
        $new_content   = $content;
        // Detect shortcode usage
        $wp_preg_match = preg_match_all( '/'. get_shortcode_regex() .'/s', $new_content, $matches );

        // Get old ID from cf7 shortcode
        preg_match( '/contact-form-7 id="([^\"]*?)\"/', $new_content, $get_old_cf7_id );

        if ( isset( $get_old_cf7_id[1] ) ) {
            // Update values
            $new_content = preg_replace( '/contact-form-7 id="([^"]*)"/', 'contact-form-7 id="'. $this->get_attachment_id( 'auxin_import_post', $get_old_cf7_id[1] ) .'"', $content );
        }

        // Parse our elements in visual composer
        if ( $wp_preg_match && array_key_exists( 2, $matches ) && stripos( $new_content, "vc_row" ) !== false  ) {
            // Get the list of attachment options attribute names
            $widget_attributes = $this->get_widget_by_type( array('attach_image', 'attach_images', 'aux_select_video', 'aux_select_audio') );

            if ( ! is_array($widget_attributes) ) {
                return $new_content;
            }

            foreach ($widget_attributes as $key => $param) {
                // Find all target attributes by the following pattern
                preg_match_all('/'.$param.'="([^"]*)"/', $content, $attributes);
                // Then start the revolution by result matches
                foreach ( $attributes[1] as $attr => $val ) {
                    // This line is for changing the old attachment ID with new one.
                    if( strpos( $val, ',' ) !== false ) {
                        $stack_values   = explode( ",", $val );
                        $gallery_widget = array();
                        foreach ( $stack_values as $gallery_key => $gallery_value ) {
                            $get_new_attachment     = $this->get_attachment_id( 'auxin_import_id', $gallery_value );
                            if ( $get_new_attachment ) {
                                $gallery_widget[]   = $get_new_attachment;
                            }
                        }
                        $new_val = implode( ",", $gallery_widget );
                    } else {
                        $new_val = $this->get_attachment_id( 'auxin_import_id', $val );
                    }
                    if ( 'src' !== $param ) {
                        // Finally replace old values with new ones. Bravo :))
                        $new_content = preg_replace('/'.$param.'="'.$val.'"/', $param.'="'.$new_val.'"', $new_content);
                    }

                }

            }
        }

        return $new_content;
    }

    /**
     * Get the attachment ID
     *
     * @param   string $key
     * @param   string $value
     *
     * @return  ID | false
     */
    public function get_attachment_id( $key, $value ) {

        global $wpdb;

        $meta       =   $wpdb->get_results( "
                            SELECT *
                            FROM $wpdb->postmeta
                            WHERE
                            meta_key='".$key."'
                            AND
                            meta_value='".$value."'
                            OR
                            meta_key='auxin_attachment_has_duplicate_".$value."'
                        ");

        if ( is_array($meta) && !empty($meta) && isset($meta[0]) ) {
            $meta   =   $meta[0];
        }

        if ( is_object( $meta ) ) {
            return $meta->post_id;
        } else {
            return null;
        }

    }

    /**
     * check post existence
     *
     * @param   string  $title
     * @param   integer $post_ID
     * @param   string  $content
     * @param   string  $date
     *
     * @return  0 | post ID
     */
    public function post_exists( $title, $post_ID, $content = '', $date = '' ) {
        global $wpdb;

        $post_title   = wp_unslash( sanitize_post_field( 'post_title', $title, 0, 'db' ) );
        $post_content = wp_unslash( sanitize_post_field( 'post_content', $content, 0, 'db' ) );
        $post_date    = wp_unslash( sanitize_post_field( 'post_date', $date, 0, 'db' ) );

        $query = "SELECT ID FROM $wpdb->posts WHERE 1=1";
        $args = array();

        if ( !empty ( $date ) ) {
            $query .= ' AND post_date = %s';
            $args[] = $post_date;
        }

        if ( !empty ( $title ) ) {
            $query .= ' AND post_title = %s';
            $args[] = $post_title;
        }

        if ( !empty ( $content ) ) {
            $query .= ' AND post_content = %s';
            $args[] = $post_content;
        }

        if ( !empty ( $args ) ) {

            $results = $wpdb->get_results( $wpdb->prepare($query, $args) );

            if( $results != null ) {
                foreach ( $results as $key => $value ) {
                    if ( get_post_meta( $value->ID, 'auxin_import_post', true ) == $post_ID ) {
                        return $value->ID;
                    }
                }
            }

        }

        return 0;
    }

    /**
     * Get old id for posts, menus
     *
     * @param   string $key
     * @param   string $value
     *
     * @return  ID | false
     */
    public function get_meta_post_id( $key, $value ) {

        global $wpdb;

        $sql = $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key=%s AND meta_value=%s", $key, $value );

        $meta = $wpdb->get_results( $sql );

        if ( is_array($meta) && !empty($meta) && isset($meta[0]) ) {
            $meta   =   $meta[0];
        }

        if ( is_object( $meta ) ) {
            return $meta->post_id;
        } else {
            return 0;
        }

    }

    /**
     * Get the attachment ID by PATHINFO_BASENAME
     *
     * @param   string $path
     *
     * @return  ID | false
     */
    public function get_attachment_id_by_basename( $path ) {

        global $wpdb;

        $post       =   $wpdb->get_results( "
                            SELECT *
                            FROM $wpdb->posts
                            WHERE
                            guid LIKE '%".$path."%'
                        ");

        if ( is_array($post) && !empty($post) && isset($post[0]) ) {
            $post   =   $post[0];
        }

        if ( is_object( $post ) ) {
            return $post->ID;
        } else {
            return null;
        }

    }


    public function insert_file( $url ) {

        if ( ! isset( $url ) ) {
            return false;
        }

        $basename     = basename( $url );
        $uploads      = wp_get_upload_dir();
        $upload_path  = $uploads['basedir'] . '/' . THEME_ID . '/'. $basename;
        $get_contents = @file_get_contents( $url );

        if( $get_contents && file_put_contents( "$upload_path", $get_contents ) ) {
            return pathinfo( $url, PATHINFO_FILENAME );
        } else {
            return false;
        }

    }

    /**
     * Insert attachment from url
     *
     * @param   integer $import_id
     * @param   string  $url
     * @param   integer $post_id
     *
     * @return  Integer
     */
    public function insert_attachment( $import_id, $url, $file_name, $path = '', $post_id = 0 ) {
        // Check if media exist then get out
        if ( $this->attachment_exist( pathinfo( $url, PATHINFO_BASENAME ) ) ) {
            // Add meta data for duplicated videos
            if ( pathinfo( $url, PATHINFO_FILENAME ) == "video" ) {
                $imported_id    = $this->get_attachment_id_by_basename( pathinfo( $url, PATHINFO_BASENAME ) );
                update_post_meta( $imported_id, 'auxin_attachment_has_duplicate_' . $import_id , $import_id );
            }

            return;
        }

        if ( ! function_exists('wp_handle_sideload') ) {
            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
            require_once(ABSPATH . "wp-admin" . '/includes/file.php');
            require_once(ABSPATH . "wp-admin" . '/includes/media.php');
        }

        $file_array             = array();
        $file_array['name']     = basename( $url );
         // Download file to temp location.
        $file_array['tmp_name'] = $file_name;

        // If error storing temporarily, return the error.
        if ( is_wp_error( $file_array['tmp_name'] ) ) {
            return;
        }

        $overrides = array( 'test_form' => false );
        $time      = current_time( 'mysql' );
        $date      = explode( '/', $path );
        $year      = isset( $date[0] ) ? $date[0] : date("Y");
        $month     = isset( $date[1] ) ? $date[1] : date("n");

        if ( ! empty( $path ) ) {
            $time = date( "Y-m-d H:i:s", mktime( date("H"), date("i"), date("s"), $month, date("j"), $year ) );
        } elseif ( $post = get_post( $post_id ) ) {
                if ( substr( $post->post_date, 0, 4 ) > 0 )
                        $time = $post->post_date;
        }

        $file = wp_handle_sideload( $file_array, $overrides, $time );

        if ( isset( $file['error'] ) ) {
            return;
        }

        $url     = $file['url'];
        $type    = $file['type'];
        $file    = $file['file'];
        $title   = preg_replace('/\.[^.]+$/', '', basename($file));
        $content = '';

        // Use image exif/iptc data for title and caption defaults if possible.
        if ( $image_meta = @wp_read_image_metadata($file) ) {
            if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) ) {
                $title = $image_meta['title'];
            }
            if ( trim( $image_meta['caption'] ) ) {
                $content = $image_meta['caption'];
            }
        }

        if ( isset( $desc ) ) {
            $title = $desc;
        }

        // Construct the attachment array.
        $attachment = array(
            'post_mime_type' => $type,
            'guid'           => $url,
            'post_parent'    => $post_id,
            'post_title'     => $title,
            'post_content'   => $content
        );

        // This should never be set as it would then overwrite an existing attachment.
        unset( $attachment['ID'] );

        // Save the attachment metadata
        $attach_id = wp_insert_attachment( $attachment, $file, $post_id );

        $image_size = getimagesize( $file );

        if ( ! is_wp_error( $attach_id ) ) {
            wp_update_attachment_metadata( $attach_id, array( 'file' => $file, 'width' => $image_size[0], 'height' => $image_size[1], 'image_meta' => $image_meta ) );
        }

        //Add auxin meta flag on attachment
        update_post_meta( $attach_id, 'auxin_import_id', $import_id );

        return $attach_id;

    }

    /**
     * Check media existence
     *
     * @param   string $filename
     *
     * @return  boolean
     */
    public function attachment_exist( $filename ) {

        global $wpdb;

        return $wpdb->get_var( "
            SELECT COUNT(*)
            FROM
            $wpdb->posts    AS p,
            $wpdb->postmeta AS m
            WHERE
            p.ID = m.post_id
            AND p.post_type = 'attachment'
            AND m.meta_key  = 'auxin_import_id'
            AND p.guid LIKE '%/".$filename."%'
        " );

    }



    public function update_elementor_data( $meta ) {

        $matches     = array();
        $attach_keys = array( 'image', 'staff_img', 'customer_img', 'poster', 'media', 'src' );

        foreach ( $attach_keys as $attach_key ) {
            preg_match_all('/\s*"'.$attach_key.'"\s*:\s*(.+?)\s*\}/', $meta, $images, PREG_SET_ORDER );
            if( ! empty( $images ) ){
                $matches = array_merge( $matches, $images );
            }
        }

        preg_match_all('/\{\s*"wp_gallery"\s*:\s*(.+?)\s*\}\]/', $meta, $wp_gallery, PREG_SET_ORDER );
        if ( isset( $wp_gallery[0][0] ) ) {
            preg_match_all( '/\{\"id":.*?\}/' , $wp_gallery[0][0], $gallery );
            $matches[] = $gallery[0];
        }

        foreach ( $matches as $image ) {

            if( ! isset( $image[0] ) ) {
                continue;
            }

            $image = $image[0]; // We don't need subpattern matches

            preg_match('/\"id":(\d*)/', $image, $image_id );
            $image_id = strval($image_id[1]);

            preg_match('/\"url":\"(.*?)\"/', $image, $image_url );
            $image_url = $image_url[1];

            $new_image_id = $this->get_attachment_id( 'auxin_import_id', $image_id );
            $new_image_url = wp_get_attachment_url( $new_image_id );
            $new_image = str_replace( '"id":'. $image_id, '"id":'. $new_image_id, $image );
            $new_image = str_replace( '"url":"'. $image_url, '"url":"'. str_replace( '/', '\/', $new_image_url), $new_image );

            $meta = str_replace( $image , $new_image, $meta );

        }

        return $meta;
    }


    public function siteorigin_data_update( $meta ) {

        $meta_str = wp_json_encode( $meta );

        preg_match_all( '/\s*"background_image_attachment"\s*:\s*"(\d+?)\s*",?/', $meta_str, $matchs, PREG_SET_ORDER );

        foreach ( $matchs as $match ) {
            if ( sizeof( $match ) > 1 ) {
                $new_id = $this->get_attachment_id( 'auxin_import_id', $match[1] );
                $replaced_meta = str_replace( $match[1] , $new_id, $match[0]);
                $meta_str = str_replace( $match[0] , $replaced_meta, $meta_str );
            }
        }

        return json_decode( $meta_str, true );

    }



}//End class
