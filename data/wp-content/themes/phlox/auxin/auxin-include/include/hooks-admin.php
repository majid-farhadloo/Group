<?php
/**
 * Admin hooks
 *
 * 
 * @package    Auxin
 * @author     averta (c) 2014-2018
 * @link       http://averta.net
*/

function auxin_update_font_icons_list(){
    // parse and cache the list of fonts
    $fonts = Auxin()->Font_Icons;
    $fonts->update();
}
add_action( 'after_switch_theme', 'auxin_update_font_icons_list' );


// make the customizer avaialble while requesting via ajax
if ( defined('DOING_AJAX') && DOING_AJAX && version_compare( PHP_VERSION, '5.3.0', '>=') ){
    Auxin_Customizer::get_instance();
}


/**
 * Include the Welcome page admin menu
 *
 * @return void
 */
function auxin_setup_admin_welcome_page() {
    if( class_exists('Auxin_Welcome') ){
        Auxin_Welcome::get_instance();
    } else {
        Auxin_Welcome_Base::get_instance();
    }
}
add_action( 'auxin_admin_loaded', 'auxin_setup_admin_welcome_page' );


/*------------------------------------------------------------------------*/

/**
 * Update the deprecated option ids
 */
function auxn_update_last_checked_version(){

    $last_checked_version = auxin_get_theme_mod( 'last_checked_version', '1.0.0' );

    if( version_compare( $last_checked_version, THEME_VERSION, '>=') ){
        return;
    }

    do_action( 'auxin_theme_updated', $last_checked_version );
    do_action( 'auxin_updated'      , 'theme', THEME_ID, THEME_VERSION, THEME_ID );

    set_theme_mod( 'last_checked_version', THEME_VERSION );
}
add_action( 'auxin_loaded', 'auxn_update_last_checked_version' );


/**
 * Skip the notice for core plugin if skip btn clicked
 *
 * @return void
 */
function auxin_hide_core_plugin_notice() {

    if ( isset( $_GET['aux-hide-core-plugin-notice'] ) && isset( $_GET['_notice_nonce'] ) ) {
        if ( ! wp_verify_nonce( $_GET['_notice_nonce'], 'auxin_hide_notices_nonce' ) ) {
            wp_die( __( 'Authorization failed. Please refresh the page and try again.', 'phlox' ) );
        }
        auxin_set_transient( 'auxin_hide_the_core_plugin_notice_' . THEME_ID, 1, 4 * YEAR_IN_SECONDS );
    }
}
add_action( 'wp_loaded', 'auxin_hide_core_plugin_notice' );


/**
 * Display a notice for installing theme core plugin
 *
 * @return void
 */
function auxin_core_plugin_notice(){

    if( defined( 'AUXELS_VERSION' ) || auxin_get_transient( 'auxin_hide_the_core_plugin_notice_' . THEME_ID ) ){
        return;
    }

    $plugin_base_name = 'auxin-elements/auxin-elements.php';
    $plugin_slug      = 'auxin-elements';
    $plugin_filename  = 'auxin-elements.php';
    $plugin_title     = __('Phlox Core Plugin', 'phlox');

    $links_attrs = array(
        'class'                 => array( 'button', 'button-primary', 'auxin-install-now', 'aux-not-installed' ),
        'data-plugin-slug'      => $plugin_slug,

        'data-activating-label' => __('Activating ..', 'phlox'),
        'data-activate-url'     => auxin_get_plugin_activation_link( $plugin_base_name, $plugin_slug, $plugin_filename ),
        'data-activate-label'   => sprintf( __('Activate %s', 'phlox'), $plugin_title ),

        'data-install-url'      => auxin_get_plugin_install_link( $plugin_slug ),
        'data-install-label'    => sprintf( __('Install %s', 'phlox' ), $plugin_title ),

        'data-redirect-url'     => self_admin_url( 'admin.php?page=auxin-welcome' )
    );

    $installed_plugins  = get_plugins();

    if( ! isset( $installed_plugins[ $plugin_base_name ] ) ){
        $links_attrs['data-action'] = 'install';
        $links_attrs['href'] = $links_attrs['data-install-url'];
        $button_label = sprintf( esc_html__( 'Install %s', 'phlox' ), $plugin_title );
    } elseif( ! auxin_is_plugin_active( $plugin_base_name ) ) {
        $links_attrs['data-action'] = 'activate';
        $links_attrs['href'] = $links_attrs['data-activate-url'];
        $button_label = sprintf( esc_html__( 'Activate %s', 'phlox' ), $plugin_title );
    } else {
        return;
    }
?>
    <div class="updated auxin-message aux-notice-wrapper aux-notice-install-now">
        <h3 class="aux-notice-title"><?php printf( __( 'Thanks for choosing %s', 'phlox' ), THEME_NAME_I18N ); ?></h3>
        <p class="aux-notice-description"><?php printf( __( 'To take full advantages of Phlox theme and enabling demo importer, please install %s.', 'phlox' ), '<strong>'. $plugin_title .'</strong>' ); ?></p>
        <p class="submit">
            <a <?php echo auxin_make_html_attributes( $links_attrs ); ?> ><?php echo $button_label; ?></a>
            <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'aux-hide-core-plugin-notice', 'install' ), 'auxin_hide_notices_nonce', '_notice_nonce' ) ); ?>" class="notice-dismiss aux-close-notice"><span class="screen-reader-text"><?php _e( 'Skip', 'phlox' ); ?></span></a>
        </p>
    </div>
<?php
}
add_action( 'admin_notices', 'auxin_core_plugin_notice' );




function auxin_customizer_device_options( $obj ) {
    if ( isset( $obj->devices ) && is_array( $obj->devices ) && ! empty( $obj->devices ) ): ?>
        <div class="axi-devices-option-wrapper" data-option-id="<?php echo esc_attr( $obj->id ); ?>">
            <span class="axi-devices-option axi-devices-option-desktop axi-selected" data-select-device="desktop">
                <img src="<?php echo esc_url( AUXIN_URL . 'images/visual-select/desktop.svg' ); ?>">
            </span>
            <?php foreach ( $obj->devices as $device => $title ): ?>
            <span class="axi-devices-option axi-devices-option-<?php echo esc_attr( $device ); ?>" data-select-device="<?php echo esc_attr( $device ); ?>">
                <img src="<?php echo esc_url( AUXIN_URL . 'images/visual-select/' . $device . '.svg' ); ?>" >
            </span>
            <?php endforeach ?>
        </div>
    <?php endif;
}

add_action( 'customize_render_control', 'auxin_customizer_device_options' );


