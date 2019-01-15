<?php




/*-----------------------------------------------------------------------------------*/
/*  Add shortcode button to tinymce
/*-----------------------------------------------------------------------------------*/

function auxin_register_shortcode_button( $buttons ) {
    array_push( $buttons, '|', 'phlox_shortcodes_button' );
    return $buttons;
}

/**
 * Add the shortcode button to TinyMCE
 *
 * @param array $plugin_array
 * @return array
 */
function auxin_add_elements_tinymce_plugin( $plugin_array ) {
    $wp_version = get_bloginfo( 'version' );

    $plugin_array['phlox_shortcodes_button'] = AUXELS_ADMIN_URL."/assets/js/tinymce/plugins/auxin-btns.js";

    return $plugin_array;
}


function auxels_init_shortcode_manager(){
    if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
        return;

    add_filter( 'mce_external_plugins', 'auxin_add_elements_tinymce_plugin' );
    add_filter( 'mce_buttons', 'auxin_register_shortcode_button' );
}
add_action("init", "auxels_init_shortcode_manager");


/*-----------------------------------------------------------------------------------*/
/*  Wizard admin notice
/*-----------------------------------------------------------------------------------*/

/**
 * Skip the notice for running the setup wizard
 *
 * @return void
 */
function auxels_hide_wizard_notice() {
    if ( isset( $_GET['auxels-hide-wizard-notice'] ) && isset( $_GET['_notice_nonce'] ) ) {
        if ( ! wp_verify_nonce( $_GET['_notice_nonce'], 'auxels_hide_notices_nonce' ) ) {
            wp_die( __( 'Authorization failed. Please refresh the page and try again.', 'auxin-elements' ) );
        }
        auxin_update_option( 'auxels_hide_wizard_notice', 1 );
    }
}
add_action( 'wp_loaded', 'auxels_hide_wizard_notice' );

/*-----------------------------------------------------------------------------------*/
/*  Add Editor styles
/*-----------------------------------------------------------------------------------*/

function auxin_register_mce_buttons_style(){
    wp_register_style('auxin_mce_buttons'  , AUXELS_ADMIN_URL. '/assets/css/editor.css', NULL, '1.1');
    wp_enqueue_style('auxin_mce_buttons');
}
add_action('admin_enqueue_scripts', 'auxin_register_mce_buttons_style');

/*-----------------------------------------------------------------------------------*/
/* Adding a plugin to plugin recommendation list
/*-----------------------------------------------------------------------------------*/

function auxels_plugin_register_recommended_plugins() {

    if( is_rtl() ){
        $plugins = array(
            array(
                'name'      => __('Phlox RTL Fonts',  'auxin-elements'),
                'slug'      => 'auxin-fonts',
                'version'   => '1.0.0',
                'source'    => AUXELS_DIR . '/embeds/plugins/auxin-fonts.zip', // The "internal" source of the plugin.
                'required'  => false
            )
        );
        tgmpa( $plugins );
    }

}

add_action( 'tgmpa_register', 'auxels_plugin_register_recommended_plugins', 12 );

/*-----------------------------------------------------------------------------------*/
/*  Adds subtitle meta field to 'Title setting' tab
/*-----------------------------------------------------------------------------------*/

function auxin_add_metabox_field_to_title_setting_tab( $fields, $id, $type ){

    if( 'general-title' == $id ){
        array_splice(
            $fields,
            1, 0,
            array(
                array(
                    'title'         => __('Subtitle for Title Bar', 'auxin-elements'),
                    'description'   => __('Second Title for title bar (optional). Note: You have to enable "Display Title Bar Section" option in order to display the subtitle.', 'auxin-elements'),
                    'id'            => 'page_subtitle',
                    'type'          => 'editor',
                    'default'       => '',
                    'dependency'    => array(
                        array(
                             'id'      => 'aux_title_bar_show',
                             'value'   => array('default', 'yes'),
                             'operator'=> '=='
                        )
                    )
                ),
                array(
                    'title'         => __('Subtitle Position', 'auxin-elements'),
                    'description'   => '',
                    'id'            => 'subtitle_position',
                    'type'          => 'select',
                    'default'       => 'after',
                    'choices'       => array(
                        'before' => __( 'Before Title', 'auxin-elements' ),
                        'after'  => __( 'After Title', 'auxin-elements' ),
                    ),
                    'dependency'    => array(
                        array(
                             'id'      => 'aux_title_bar_show',
                             'value'   => array('default', 'yes'),
                             'operator'=> '=='
                        )
                    )
                )
            )
        );
    }

    return $fields;
}
add_filter( 'auxin_metabox_fields', 'auxin_add_metabox_field_to_title_setting_tab', 10, 3 );


/*-----------------------------------------------------------------------------------*/
/*  Registers special theme admin menu
/*-----------------------------------------------------------------------------------*/

function auxin_elements_admin_bar_add_upgrade_phlox( $wp_admin_bar ){

    // Skip for Pro version
    if( defined('THEME_PRO') && THEME_PRO ){
        return;
    }

    $wp_admin_bar->add_menu( array(
        'id'     => 'phlox-upgrade',
        'title'  => __( 'Upgrade Phlox', 'auxin-elements' ),
        'parent' => 'top-secondary',
        'href'   => esc_url( 'http://phlox.pro/go-pro/?utm_source=phlox-welcome&utm_medium=phlox-free&utm_campaign=phlox-go-pro&utm_content=adminbar' ),
        'meta'   => array(
            'class'  => 'auxin-upgrade-top-bar',
            'target' => '_blank'
        )
    ));

}
add_action( 'admin_bar_menu', 'auxin_elements_admin_bar_add_upgrade_phlox', 199 );


/**
 * Remove theme submenu under appearance
 *
 * @return void
 */
function auxin_elements_remnove_theme_submenu(){
    remove_submenu_page( "themes.php", "tgmpa-install-plugins");
}
add_action( "admin_menu", "auxin_elements_remnove_theme_submenu", 12 );


/*-----------------------------------------------------------------------------------*/
/*  Check Bundled Plugins Updates
/*-----------------------------------------------------------------------------------*/


/**
 * Add a submenu to TGMPA plugins update page
 *
 * @return void
 */
function auxin_register_update_plugins_submenu(){
    global $menu;

    if( ! defined('THEME_PRO') || ! THEME_PRO ) {
        return;
    }

    // Update Plugins SubMenu
    if( $tgmpa_counter  = auxin_count_bundled_plugins_have_update() ) {
        add_submenu_page(
            'auxin-welcome',
            esc_attr__( 'Update Plugins' , 'auxin-elements' ),
            sprintf( __( 'Update Plugins %s' , 'auxin-elements' ), " <span class='update-plugins count-1'><span class='update-count'>". number_format_i18n( $tgmpa_counter ) ."</span></span>" ),
            'manage_options',
            'auxin-update',
            'auxin_get_tgmpa_plugins_page'
        );
    }
}
add_action( 'admin_menu', 'auxin_register_update_plugins_submenu', 30 );


/**
 * Remove transient on plugin upgrade
 *
 * @return void
 */
function auxin_remove_bundled_plugins_update_transient(){
    delete_transient( 'auxin_count_bundled_plugins_have_update' );
}
add_action( 'upgrader_process_complete', 'auxin_remove_bundled_plugins_update_transient' );
add_action( 'auxin_updated'            , 'auxin_remove_bundled_plugins_update_transient' );

/**
 * Add bundled plugins update count to admin theme menu
 *
 * @param  int   $count  The number if bubble count
 * @return int   $count
 */
function auxin_add_bundled_plugins_update_count_to_theme_menu( $count ){
    // Only count for Pro version
    if( ! defined('THEME_PRO') || ! THEME_PRO ) {
        return $count;
    }
    if( $tgmpa_counter = auxin_count_bundled_plugins_have_update() ) {
        $count = $count + $tgmpa_counter;
    }
    return $count;
}
add_action( 'auxin_theme_menu_update_count', 'auxin_add_bundled_plugins_update_count_to_theme_menu' );

/*-----------------------------------------------------------------------------------*/
/*  Define demo info list / for auxin-element plugin
/*-----------------------------------------------------------------------------------*/

/**
 * Retrieves the list of available demos for current theme
 *
 * @return array List of demos
 */
function auxels_add_to_demo_info_list( $default_demos ){

    $demos_list = array(
        'the-journey' => array(
            'id'            => 'the-journey',
            'title'         => __('The Journey', 'auxin-elements'),
            'desc'          => __('Create your awesome Journey Website using this demo as a starter. Best choice for adventure looks.', 'auxin-elements'),
            'preview_url'   => 'http://averta.net/phlox/demo/journey/',
            'thumb_url'     => AUXELS_URL . '/embeds/demos/journey-blog/banner.jpg',
            'file'          => AUXELS_DIR . '/embeds/demos/journey-blog/data.xml'
        ),
        'classic-blog' => array(
            'id'            => 'classic-blog',
            'title'         => __('Classic Blog', 'auxin-elements'),
            'desc'          => __('Create your classic good looking Blog using this demo as a starter. Best choice for a classic blogger.', 'auxin-elements'),
            'preview_url'   => 'http://averta.net/phlox/demo/classic-blog/',
            'thumb_url'     => AUXELS_URL . '/embeds/demos/classic-blog/banner.jpg',
            'file'          => AUXELS_DIR . '/embeds/demos/classic-blog/data.xml'
        ),
        'food-blog' => array(
            'id'            => 'food-blog',
            'title'         => __('Food Blog', 'auxin-elements'),
            'desc'          => __('Create your awesome Food Website using this demo as a starter. Best choice for restaurant looks.', 'auxin-elements'),
            'preview_url'   => 'http://averta.net/phlox/demo/food/',
            'thumb_url'     => AUXELS_URL . '/embeds/demos/food-blog/banner.jpg',
            'file'          => AUXELS_DIR . '/embeds/demos/food-blog/data.xml'
        ),
        'portfolio' => array(
            'id'            => 'portfolio',
            'title'         => __('Protfolio', 'auxin-elements'),
            'desc'          => __('A stunning demo for Phlox portfolio that represents your projects in a modern and stylish way.', 'auxin-elements'),
            'preview_url'   => 'http://averta.net/phlox/demo/portfolio/',
            'thumb_url'     => AUXELS_URL . '/embeds/demos/portfolio/banner.jpg',
            'file'          => AUXELS_DIR . '/embeds/demos/food-blog/data.xml'
        ),
        'default' => array(
            'id'            => 'default',
            'title'         => __('Default', 'auxin-elements'),
            'desc'          => __('An excellent example to get familiar with all available layouts, elements, shortcodes and other features of Phlox.', 'auxin-elements'),
            'preview_url'   => 'http://averta.net/phlox/demo/default/',
            'thumb_url'     => AUXELS_URL . '/embeds/demos/default/banner.jpg',
            'file'          => AUXELS_DIR . '/embeds/demos/default/data.xml'
        )
    );

    return array_merge( $default_demos, $demos_list );
}

add_filter( 'auxin_get_demo_info_list', 'auxels_add_to_demo_info_list' );

/*-----------------------------------------------------------------------------------*/
/*  Adding fallback for deprecated theme option name
/*-----------------------------------------------------------------------------------*/

function auxels_sync_deprecated_options(){

    $old_theme_options = get_option( THEME_ID . '_formatted_options' );
    if( false === $old_theme_options ){
        return;
    }

    $new_theme_options = get_option( THEME_ID . '_theme_options' );
    if( false === $new_theme_options ){
        update_option( THEME_ID . '_theme_options', $old_theme_options );
    }
}
add_action( 'admin_init', 'auxels_sync_deprecated_options' );

/*-----------------------------------------------------------------------------------*/
/*  Add post format related metafields to post
/*-----------------------------------------------------------------------------------*/

function auxels_add_post_metabox_models( $models ){

    // Load general metabox models
    include_once( 'metaboxes/metabox-fields-post-audio.php'   );
    include_once( 'metaboxes/metabox-fields-post-gallery.php' );
    include_once( 'metaboxes/metabox-fields-post-quote.php'   );
    include_once( 'metaboxes/metabox-fields-post-video.php'   );

    $models[] = array(
        'model'     => auxin_metabox_fields_post_gallery(),
        'priority'  => 20
    );

    $models[] = array(
        'model'     => auxin_metabox_fields_post_video(),
        'priority'  => 22
    );

    $models[] = array(
        'model'     => auxin_metabox_fields_post_audio(),
        'priority'  => 24
    );

    $models[] = array(
        'model'     => auxin_metabox_fields_post_quote(),
        'priority'  => 26
    );

    $models[] = array(
        'model'     => auxin_metabox_fields_general_advanced(),
        'priority'  => 36
    );

    return $models;
}

add_filter( 'auxin_admin_metabox_models_post', 'auxels_add_post_metabox_models' );

/*-----------------------------------------------------------------------------------*/
/*  Add advanced metafields to page
/*-----------------------------------------------------------------------------------*/

function auxels_add_page_metabox_models( $models ){

    include_once( 'metaboxes/metabox-fields-general-top-header.php');
    include_once( 'metaboxes/metabox-fields-general-header.php');
    include_once( 'metaboxes/metabox-fields-general-footer.php');
    include_once( 'metaboxes/metabox-fields-page-template.php');

    $models[] = array(
        'model'     => auxin_metabox_fields_general_top_header(),
        'priority'  => 13
    );

    $models[] = array(
        'model'     => auxin_metabox_fields_general_header(),
        'priority'  => 16
    );

    $models[] = array(
        'model'     => auxin_metabox_fields_general_footer(),
        'priority'  => 20
    );

    $models[] = array(
        'model'     => auxin_metabox_fields_page_template(),
        'priority'  => 15
    );

    $models[] = array(
        'model'     => auxin_metabox_fields_general_advanced(),
        'priority'  => 36
    );


    return $models;
}

add_filter( 'auxin_admin_metabox_models_page', 'auxels_add_page_metabox_models' );

/*-----------------------------------------------------------------------------------*/
/*  Add theme tab in siteorigin page builder
/*-----------------------------------------------------------------------------------*/

function auxin_add_widget_tabs($tabs) {
    $tabs[] = array(
        'title'  => THEME_NAME,
        'filter' => array(
            'groups' => array('auxin')
        )
    );

    if (isset($tabs['recommended'])){
        unset($tabs['recommended']);
    }


    return $tabs;
}
add_filter( 'siteorigin_panels_widget_dialog_tabs', 'auxin_add_widget_tabs', 20 );

// =============================================================================

function auxin_admin_footer_text( $footer_text ) {

    // the admin pages that we intent to display theme footer text on
    $admin_pages = array(
        'toplevel_page_auxin',
        'appearance_page_auxin',
        'toplevel_page_auxin-welcome',
        'appearance_page_auxin-welcome',
        'page',
        'post',
        'widgets',
        'dashboard',
        'edit-post',
        'edit-page',
        'edit-portfolio',
        'edit-faq',
        'edit-product'
    );

    if( ! ( function_exists('auxin_is_theme_admin_page') && auxin_is_theme_admin_page( $admin_pages ) ) ){
        return $footer_text;
    }

    $welcome_tab_url  = self_admin_url( 'admin.php?page=auxin-welcome&tab=' );
    $setup_wizard_url = self_admin_url( 'admin.php?page=auxin-wizard' );


    $auxin_text = sprintf(
        __( 'Quick access to %s %sdashboard%s, %sdemo importer%s, %soptions%s, and %ssupport%s page.', 'auxin-elements' )
        ,
        '<strong>' . THEME_NAME_I18N . '</strong>',
        '<a href="'. Auxin_Welcome::get_instance()->get_tab_link('') .'" title="'. sprintf( esc_attr__( '%s theme version %s', 'auxin-elements' ), THEME_NAME_I18N, THEME_VERSION ) .'" >',
        '</a>',
        '<a href="'. Auxin_Welcome::get_instance()->get_tab_link('importer') .'" title="'. __('Theme Demo Importer', 'auxin-elements' ) .'" >',
        '</a>',
        '<a href="'. esc_url( self_admin_url( 'customize.php' ) ) .'" title="'. __('Theme Customizer', 'auxin-elements' ) .'" >',
        '</a>',
        '<a href="'. Auxin_Welcome::get_instance()->get_tab_link('help') .'">',
        '</a>'
    );

    return '<span id="footer-thankyou">' . $auxin_text . '</span>';
}
add_filter( 'admin_footer_text',  'auxin_admin_footer_text' );




/*-----------------------------------------------------------------------------------*/
/*  Dashboard "Right Now" modification
/*-----------------------------------------------------------------------------------*/

function auxin_add_2_rightnow_bottom() {
    $p_theme = auxin_get_main_theme();

    echo '<div class="aux-dashboard-widget-footer">';

    echo '<span class="aux-footer-using">';
    printf(
        __( 'You are using %1$s theme version %2$s.', 'auxin-elements'),
        '<strong>'. $p_theme->display('Name'). '</strong>',
        '<strong>'. $p_theme->display('Version'). '</strong>'
    );
    echo '</span>';

    if( ! ( defined('THEME_PRO') && THEME_PRO ) ){
        echo '<span class="aux-footer-rate">'.
        sprintf(
            __( 'Please support us to continue this project by rating it %s', 'auxin-elements' ),
            '<a href="https://wordpress.org/support/theme/phlox/reviews/?filter=5#new-post" target="_blank">★★★★★</a>'
        ).
        '</span>';
    }

    $link = 'http://support.averta.net/en/e-item/'. THEME_ID .'-wordpress-theme/?utm_source=wp-dashboard-widget&utm_medium=phlox-free&utm_content=wp-glance-widget&utm_term=documentation&utm_campaign=docs';
    echo '<a class="aux-dashboard-widget-footer-link" href="'.$link.'" target="_blank">Help<span class="screen-reader-text">(opens in a new window)</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>';

    echo '</div>';
}

add_action( 'rightnow_end', 'auxin_add_2_rightnow_bottom' );

/*-----------------------------------------------------------------------------------*/
/*  Assign menus on start or after demo import
/*-----------------------------------------------------------------------------------*/

/**
 * Automatically assigns the appropriate menus to menu locations
 * Known Locations:
 *  - header-primary  : There should be a menu with the word "Primary" Or "Mega" in its name
 *  - header-secondary: There should be a menu with the word "Secondary" in its name
 *  - footer          : There should be a menu with the word "Footer" in its name
 *
 * @return bool         True if at least one menu was assigned, false otherwise
 */
function auxin_assign_default_menus(){

    $assinged = false;
    $locations = get_theme_mod('nav_menu_locations');
    $nav_menus = wp_get_nav_menus();

    foreach ( $nav_menus as $nav_menu ) {
        $menu_name = strtolower( $nav_menu->name );

        if( empty( $locations['header-secondary'] ) && preg_match( '(secondary)', $menu_name ) ){
            $locations['header-secondary'] = $nav_menu->term_id;
            $assinged = true;
        } elseif( empty( $locations['header-primary'] ) && preg_match( '(primary|mega|header)', $menu_name ) ){
            $locations['header-primary'] = $nav_menu->term_id;
            $assinged = true;
        } elseif( empty( $locations['footer'] ) && preg_match( '(footer)', $menu_name ) ){
            $locations['footer'] = $nav_menu->term_id;
            $assinged = true;
        }
    }

    set_theme_mod( 'nav_menu_locations', $locations );
    return $assinged;
}

add_action( 'after_switch_theme', 'auxin_assign_default_menus' ); // triggers when theme will be actived, WP 3.3
add_action( 'import_end', 'auxin_assign_default_menus' ); // triggers when the theme data was imported

/*-----------------------------------------------------------------------------------*/
/*  Remove any script tag fromt custom js (if user used them in the script content)
/*-----------------------------------------------------------------------------------*/

/**
 * Strip <script> tags
 *
 * @param  string $js_string  The custom js string
 * @return string             The sanitized custom js code
 */
function auxels_strip_script_tags_from_custom_js( $js_string ){
    if ( false !== stripos( $js_string, '</script>' ) ) {
        $js_string = str_replace( array( "<script>", "</script>" ), array('', ''), $js_string );
    }
    return $js_string;
}
add_filter( 'auxin_custom_js_string', 'auxels_strip_script_tags_from_custom_js' );

/*-----------------------------------------------------------------------------------*/
/*  Remove any style tag fromt custom css (if user used them in the style content)
/*-----------------------------------------------------------------------------------*/

/**
 * Strip <style> tags
 *
 * @param  string $css_string  The custom css string
 * @return string             The sanitized custom css code
 */
function auxels_strip_style_tags_from_custom_css( $css_string ){
    if ( false !== stripos( $css_string, '</style>' ) ) {
        $css_string = str_replace( array( "<style>", "</style>" ), array('', ''), $css_string );
    }
    return $css_string;
}
add_filter( 'auxin_custom_css_string', 'auxels_strip_style_tags_from_custom_css' );

/*-----------------------------------------------------------------------------------*/

/**
 * Recreate custom css and js files after updating auxin plugins
 *
 * @param  $flush  Whether to flush rewrite rules after plugin update or not
 * @return void
 */
function auxels_update_custom_js_css_file_on_auxin_plugin_update( $flush = true ){
    auxin_save_custom_js();
    auxin_save_custom_css();
    if( $flush )
        flush_rewrite_rules();
}
add_action( "auxin_plugin_updated", "auxels_update_custom_js_css_file_on_auxin_plugin_update" );


/**
 * Triggers an action after plugin was updated to new version.
 *
 * @return void
 */
function auxels_after_plugin_update(){
    if( AUXELS_VERSION !== get_transient( 'auxin_' . AUXELS_SLUG . '_version' ) ){
        set_transient( 'auxin_' . AUXELS_SLUG . '_version', AUXELS_VERSION, MONTH_IN_SECONDS );

        do_action( 'auxin_plugin_updated', false   , AUXELS_SLUG, AUXELS_VERSION, AUXELS_BASE_NAME );
        do_action( 'auxin_updated'       , 'plugin', AUXELS_SLUG, AUXELS_VERSION, AUXELS_BASE_NAME );
    }
}
add_action( 'admin_init', 'auxels_after_plugin_update', 9 );



add_action( 'admin_init', function(){

    $plugin_update_check = new AUXELS_Plugin_Check_Update (
        AUXELS_VERSION,                         // current version
        'http://api.averta.net/envato/items/',  // update path
        AUXELS_BASE_NAME,                       // plugin file slug
        'auxin-elements',                       // plugin slug
        'auxin-elements',                       // item request name
        AUXELS_DIR . '/auxin-elements.php'      // plugin file
    );

    $plugin_update_check->plugin_id = '1238506';
} );

/**
 * Disable the query monitor on vc frontend editor
 *
 * @return bool                Whether to displatche the debug report or not
 */
function auxin_disable_query_monitor_on_vc_fronteditor( $debug_enabled ){
    return ( function_exists( 'vc_is_frontend_editor' ) && vc_is_frontend_editor() ) ? false : $debug_enabled;
}
add_filter( 'qm/dispatch/ajax', "auxin_disable_query_monitor_on_vc_fronteditor" );
add_filter( 'qm/dispatch/html', "auxin_disable_query_monitor_on_vc_fronteditor" );


function auxin_meida_setting_requires_modification(){
    echo '<div class="aux-admin-error notice notice-warning notice-large">';
    _e( 'Please make sure the image aspect ratio for all image sizes are the same.', 'auxin-elements' );
    echo '</div>';
}

/**
 *
 *
 * @return void
 */
function auxels_after_media_setting_updated(){

    $image_sizes = array('thumbnail', 'medium', 'medium_large', 'large');
    $same_ratio = true;
    $ratio = '';

    foreach ( $image_sizes as $image_size ) {
        $width = get_option( $image_size. '_size_w' );

        if( $height = get_option( $image_size. '_size_h' ) ){
            if( ! empty( $ratio ) && $ratio != ( $width / $height ) ){
                $same_ratio = false;
                break;
            }
            $ratio = $width / $height;
        }

    }

    if( $same_ratio && $ratio ){
        if( ! get_option( 'medium_large_size_h') ){
            update_option( 'medium_large_size_h', get_option( 'medium_large_size_w' ) * $ratio );
        }
        set_theme_mod( 'auxin_wp_image_sizes_ratio', $ratio );
    } elseif( ! $same_ratio ) {
        add_action( 'admin_notices', 'auxin_meida_setting_requires_modification' );
    }

}

add_action( "load-options-media.php", "auxels_after_media_setting_updated");
add_action( "auxin_plugin_updated"  , "auxels_after_media_setting_updated" );


/*-----------------------------------------------------------------------------------*/
/*  Adds Custom Footer Metafields to 'Layout Options' tab
/*-----------------------------------------------------------------------------------*/

function auxin_add_metabox_field_to_layout_setting_tab( $fields, $id, $type ){

    if( 'layout-options' == $id ){

        $fields[] = array(
            'title'       => __('Footer Brand Image', 'auxin-elements'),
            'description' => __('This image appears as site brand image on footer section.', 'auxin-elements'),
            'id'          => 'page_secondary_logo_image',
            'section'     => 'footer-section-footer',
            'dependency'  => array(
                array(
                     'id'      => 'page_show_footer',
                     'value'   => array('yes', 'default'),
                     'operator'=> '=='
                )
            ),
            'type'        => 'image'
        );
    }

    return $fields;
}
add_filter( 'auxin_metabox_fields', 'auxin_add_metabox_field_to_layout_setting_tab', 10, 3 );
