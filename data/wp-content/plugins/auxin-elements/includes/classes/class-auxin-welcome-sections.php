<?php


class Auxin_Welcome_Sections {

    /**
     * Instance of this class.
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Instance of main welcome class.
     *
     * @var      object
     */
    public $welcome = null;

    /**
     * The slug name to refer to this menu
     *
     * @since 1.0
     *
     * @var string
     */
    public $page_slug;

    /**
     * List of video tutorilas.
     *
     * @var      array
     */
    protected $tutorial_list = null;


    function __construct(){
        add_filter( 'auxin_admin_welcome_sections', array( $this, 'add_section_importer' ), 60 );
        add_filter( 'auxin_admin_welcome_sections', array( $this, 'add_section_plugins'  ), 70 );
        add_filter( 'auxin_admin_welcome_sections', array( $this, 'add_section_feedback' ), 100 );
        add_filter( 'auxin_admin_welcome_sections', array( $this, 'add_section_status'   ), 110 );

        if( ! defined('THEME_PRO') || ! THEME_PRO ) {
            add_filter( 'auxin_admin_welcome_sections', array( $this, 'add_section_go_pro'   ), 120 );
            add_action( 'auxin_admin_before_welcome_section_content', array( $this, 'maybe_add_dashboard_notice') );
        }

        add_action( 'auxin_admin_after_welcome_section_content' , array( $this, 'append_changelog') );
        add_action( 'auxin_admin_after_welcome_section_content' , array( $this, 'append_tutorials') );

        add_filter( 'auxin_admin_welcome_video_tutorial_list'   , array( $this, 'add_video_tutorial_list' ) );
    }

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


    /*-----------------------------------------------------------------------------------*/
    /*  Adds demos tab in theme about (welcome) page
    /*-----------------------------------------------------------------------------------*/

    public function add_section_importer( $sections ){

        if( ! empty( $sections['importer'] ) ){
            $sections['importer']['callback']       = 'setup_importer';
            $sections['importer']['add_admin_menu'] = true;
        }

        return $sections;
    }

    /**
     * Adds a notice after dashboard navigation
     *
     * @param array  $sections
     */
    public function maybe_add_dashboard_notice( $sections ){
        echo Auxin_Dashboard_Notice::get_instance()->get_notice( 'auxels-notice-info-dashboard' );
    }

    /**
     * Adds a new section to welcome page
     *
     * @param array  $sections
     */
    public function add_section_plugins( $sections ){

        $sections['plugins'] = array(
            'label'          => esc_html__( 'Plugins', 'auxin-elements' ),
            'description'    => '',
            'callback'       => 'setup_plugins',
            'add_admin_menu' => true
        );

        return $sections;
    }

    /**
     * Adds a new section to welcome page
     *
     * @param array  $sections
     */
    public function add_section_feedback( $sections ){

         $sections['feedback'] = array(
            'label'       => __( 'Feedback', 'auxin-elements' ),
            'description' => '',
            'callback'    => array( $this, 'render_feedback' )
        );

        return $sections;
    }

    /**
     * Adds a new section to welcome page
     *
     * @param array  $sections
     */
    public function add_section_status( $sections ){

        $sections['status'] = array(
            'label'       => __( 'System Status', 'auxin-elements' ),
            'description' => '',
            'callback'    => array( $this, 'render_system_status' )
        );

        return $sections;
    }

    /**
     * Adds a new section to welcome page
     *
     * @param array  $sections
     */
    public function add_section_go_pro( $sections ){

        $sections['go_pro'] = array(
            'label'          => esc_html__( 'Go Pro', 'auxin-elements' ),
            'description'    => '',
            'url'            => esc_url( 'http://phlox.pro/go-pro/?utm_source=phlox-welcome&utm_medium=phlox-free&utm_campaign=phlox-go-pro&utm_content=welcome-tab' ), // optional
            'target'         => '_blank',
            'image'          => AUXELS_ADMIN_URL . '/assets/images/welcome/rocket-pro.gif',
            'add_admin_menu' => true
        );

        return $sections;
    }


    /**
     * Content for status tab in welcome-about page in admin panel
     *
     * @return void
     */
    function render_system_status(){
        ?>
        <div class="aux-section-content-box">
            <h3 class="aux-content-title"><?php _e('Some informaition about your WordPress installation which can be helpful for debugging or monitoring your website.', 'auxin-elements' ); ?></h3>
            <div class="aux-status-wrapper">

                <table class="widefat" cellspacing="0">
                  <thead>
                    <tr>
                      <th colspan="3" data-export-label="WordPress Environment"><?php _e( 'WordPress Environment', 'auxin-elements' ); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td data-export-label="Home URL"><?php _e( 'Home URL', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'The URL of your site\'s homepage.', 'auxin-elements'  ) . '"> ? </a>'; ?></td>
                      <td><?php echo home_url(); ?></td>
                    </tr>
                    <tr>
                      <td data-export-label="Site URL"><?php _e( 'Site URL', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'The root URL of your site.', 'auxin-elements'  ) . '"> ? </a>'; ?></td>
                      <td><?php echo site_url(); ?></td>
                    </tr>
                    <tr>
                      <td data-export-label="WP Version"><?php _e( 'WP Version', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'The version of WordPress installed on your site.', 'auxin-elements'  ) . '"> ? </a>'; ?></td>
                      <td><?php bloginfo('version'); ?></td>
                    </tr>
                    <tr>
                      <td data-export-label="WP Multisite"><?php _e( 'WP Multisite', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'Whether or not you have WordPress Multisite enabled.', 'auxin-elements'  ) . '"> ? </a>'; ?></td>
                      <td><?php if ( is_multisite() ) echo '&#10004;'; else echo '&#10005;'; ?></td>
                    </tr>
                    <tr>
                      <td data-export-label="WP Memory Limit"><?php _e( 'WP Memory Limit', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'The maximum amount of memory (RAM) that your site can use at one time.', 'auxin-elements' ) . '"> ? </a>'; ?></td>
                      <td><?php
                      // This field need to make some changes
                        $server_memory = 0;
                        if( function_exists( 'ini_get' ) ) {
                          echo ( ini_get( 'memory_limit') );
                        }
                      ?></td>
                    </tr>
                    <tr>
                      <td data-export-label="WP Permalink"><?php _e( 'WP Permalink', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'The WordPress permalink structer.', 'auxin-elements'  ) . '"> ? </a>'; ?></td>
                      <td><?php  echo get_option( 'permalink_structure' ); ?></td>
                    </tr>
                    <tr>
                      <td data-export-label="WP Debug Mode"><?php _e( 'WP Debug Mode', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'Displays whether or not WordPress is in Debug Mode.', 'auxin-elements' ) . '"> ? </a>'; ?></td>
                      <td><?php if ( defined('WP_DEBUG') && WP_DEBUG ) echo '<mark class="yes">' . '&#10004;' . '</mark>'; else echo '<mark class="no">' . '&#10005;' . '</mark>'; ?></td>
                    </tr>
                    <tr>
                      <td data-export-label="Language"><?php _e( 'Language', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'The current language used by WordPress. Default = English', 'auxin-elements' ) . '"> ? </a>'; ?></td>
                      <td><?php echo get_locale() ?></td>
                    </tr>
                  </tbody>
                </table>

                <table class="widefat" cellspacing="0">
                  <thead>
                    <tr>
                      <th colspan="3" data-export-label="Server Environment"><?php _e( 'Server Environment', 'auxin-elements' ); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td data-export-label="Server Info"><?php _e( 'Server Info', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'Information about the web server that is currently hosting your site.', 'auxin-elements'  ) . '"> ? </a>'; ?></td>
                      <td><?php echo esc_html( $_SERVER['SERVER_SOFTWARE'] ); ?></td>
                    </tr>
                    <tr>
                      <td data-export-label="PHP Version"><?php _e( 'PHP Version', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'The version of PHP installed on your hosting server.', 'auxin-elements'  ) . '"> ? </a>'; ?></td>
                      <td><?php
                      // should add the cpmparsion check for version_compare(PHP_VERSION, '5.0.0', '<')
                      if ( function_exists( 'phpversion' ) ) echo esc_html( phpversion() ); ?></td>
                    </tr>
                    <tr>
                      <td data-export-label="Server Info"><?php _e( 'Server Info', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'Information about the web server that is currently hosting your site.', 'auxin-elements'  ) . '"> ? </a>'; ?></td>
                      <td><?php echo esc_html( $_SERVER['SERVER_SOFTWARE'] ); ?></td>
                    </tr>
                    <?php if ( function_exists( 'ini_get' ) ) : ?>
                    <tr>
                      <td data-export-label="PHP Post Max Size"><?php _e( 'PHP Post Max Size', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'The largest file size that can be contained in one post.', 'auxin-elements'  ) . '"> ? </a>'; ?></td>
                      <td></td>
                    </tr>
                    <tr>
                      <td data-export-label="PHP Time Limit"><?php _e( 'PHP Time Limit', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'The amount of time (in seconds) that your site will spend on a single operation before timing out (to avoid server lockups)', 'auxin-elements'  ) . '"> ? </a>'; ?></td>
                      <td><?php
                          $time_limit = ini_get('max_execution_time');
                          //should add the condition
                          if ( $time_limit < 60 && $time_limit != 0 ) {
                            echo '<mark class="server-status-error">' . sprintf( __( '%s - We recommend setting max execution time to at least 60. See: <a href="%s" target="_blank">Increasing max execution to PHP</a>', 'auxin-elements' ), $time_limit, 'http://codex.wordpress.org/Common_WordPress_Errors#Maximum_execution_time_exceeded' ) . '</mark>';
                          } else {
                            echo '<mark class="yes">' . $time_limit . '</mark>';
                          }
                        ?>
                      </td>
                    </tr>
                    <tr>
                      <td data-export-label="PHP Max Input Vars"><?php _e( 'PHP Max Input Vars', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'The maximum number of variables your server can use for a single function to avoid overloads.', 'auxin-elements'  ) . '"> ? </a>'; ?></td>
                      <td><?php echo ini_get('max_input_vars'); ?></td>
                    </tr>
                    <tr>
                      <td data-export-label="SUHOSIN Installed"><?php _e( 'SUHOSIN Installed', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'Suhosin is an advanced protection system for PHP installations. It was designed to protect your servers on the one hand against a number of well known problems in PHP applications and on the other hand against potential unknown vulnerabilities within these applications or the PHP core itself. If enabled on your server, Suhosin may need to be configured to increase its data submission limits.', 'auxin-elements'  ) . '"> ? </a>'; ?></td>
                      <td><?php echo extension_loaded( 'suhosin' ) ? '&#10004;' : '&#10005;'; ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                      <td data-export-label="MySQL Version"><?php _e( 'MySQL Version', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'The version of MySQL installed on your hosting server.', 'auxin-elements' ) . '"> ? </a>'; ?></td>
                      <td>
                        <?php
                        /** @global wpdb $wpdb */
                        global $wpdb;
                        echo $wpdb->db_version();
                        ?>
                      </td>
                    </tr>
                    <tr>
                      <td data-export-label="Max Upload Size"><?php _e( 'Max Upload Size', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'The largest file size that can be uploaded to your WordPress installation.', 'auxin-elements'  ) . '"> ? </a>'; ?></td>
                      <td><?php echo size_format( wp_max_upload_size() ); ?></td>
                    </tr>
                    <tr>
                      <td data-export-label="Default Timezone is UTC"><?php _e( 'Default Timezone is UTC', 'auxin-elements' ); ?>:</td>
                      <td class="help"><?php echo '<a href="#" class="help-tip" original-title="' . esc_attr__( 'The default timezone for your server.', 'auxin-elements' ) . '"> ? </a>'; ?></td>
                      <td><?php
                        $default_timezone = date_default_timezone_get();
                        if ( 'UTC' !== $default_timezone ) {
                          echo '<mark class="server-status-error">' . '&#10005; ' . sprintf( __( 'Default timezone is %s - it should be UTC', 'auxin-elements' ), $default_timezone ) . '</mark>';
                        } else {
                          echo '<mark class="yes">' . '&#10004;' . '</mark>';
                        } ?>
                      </td>
                    </tr>
                  </tbody>
                </table>

                <table class="widefat active-plugins" cellspacing="0" id="status">
                  <thead>
                    <tr>
                      <th colspan="3" data-export-label="Active Plugins (<?php echo count( (array) get_option( 'active_plugins' ) ); ?>)"><?php _e( 'Active Plugins', 'auxin-elements' ); ?> (<?php echo count( (array) get_option( 'active_plugins' ) ); ?>)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $active_plugins = (array) get_option( 'active_plugins', array() );

                    if ( is_multisite() ) {
                      $active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
                    }
                    foreach ( $active_plugins as $plugin ) {
                      $plugin_data    = @get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
                      $dirname        = dirname( $plugin );
                      $version_string = '';
                      $network_string = '';
                      if ( ! empty( $plugin_data['Name'] ) ) {
                        // link the plugin name to the plugin url if available
                        $plugin_name = esc_html( $plugin_data['Name'] );
                        if ( ! empty( $plugin_data['PluginURI'] ) ) {
                          $plugin_name = '<a href="' . esc_url( $plugin_data['PluginURI'] ) . '" title="' . __( 'Visit plugin homepage' , 'auxin-elements' ) . '" target="_blank">' . $plugin_name . '</a>';
                        }
                        ?>
                        <tr>
                          <td><?php echo $plugin_name; ?></td>
                          <td><?php echo sprintf( _x( 'by %s', 'by author', 'auxin-elements' ), $plugin_data['Author'] ) . ' Version &ndash; ' . esc_html( $plugin_data['Version'] ) . $version_string . $network_string; ?></td>
                        </tr>
                        <?php
                          }
                    }
                    ?>
                  </tbody>
                </table>
            </div>
        </div>
        <?php
    }


    function render_feedback(){
        // the previous rate of the client
        $previous_rate = auxin_get_option( 'user_rating' );

        $support_tab_url = self_admin_url( 'admin.php?page=auxin-welcome&tab=help' );
        ?>

        <div class="feature-section aux-welcome-page-feedback">
            <div class="aux-section-content-box">

                <div class="aux-columns-wrap">
                    <div class="aux-image-wrap"></div>
                    <div class="aux-form-wrap">
                        <form class="aux-feedback-form" action="<?php echo admin_url( 'admin.php?page=auxin-welcome&tab=feedback'); ?>" method="post" >

                            <div class="aux-rating-section">
                                <h3 class="aux-content-title"><?php _e('How likely are you to recommend Phlox to a friend?', 'auxin-elements' ); ?></h3>
                                <div class="aux-theme-ratings">
                                <?php
                                    for( $i = 1; $i <= 10; $i++ ){
                                        printf(
                                            '<div class="aux-rate-cell"><input type="radio" name="theme_rate" id="theme-rating%1$s" value="%1$s" %2$s/><label class="rating" for="theme-rating%1$s">%1$s</label></div>',
                                            $i, checked( $previous_rate, $i, false )
                                        );
                                    }
                                ?>

                                </div>
                                <div class="aux-ratings-measure">
                                    <p><?php _e( "Don't like it", 'auxin-elements' ); ?></p>
                                    <p><?php _e( "Like it so much", 'auxin-elements' ); ?></p>
                                </div>
                            </div>

                            <div class="aux-feedback-section aux-hide">
                                <div class="aux-notice-box aux-notice-blue aux-rate-us-offer aux-hide">
                                    <img src="<?php echo AUXELS_ADMIN_URL.'/assets/images/welcome/rate-like.svg'; ?>" />
                                    <p><?php printf(
                                        __('Thanks for using Phlox theme. If you are enjoying this theme, please support us by %s submitting 5 star rate here%s. That would be a huge help for us to continue developing this theme.'),
                                        '<a href="http://phlox.pro/rate/'.THEME_ID.'" target="_black">',
                                        '</a>'
                                    ); ?>
                                    </p>
                                </div>
                                <h3 class="aux-feedback-form-title aux-content-title"><?php _e('Please explain why you gave this score (optional)', 'auxin-elements'); ?></h3>
                                <h4 class="aux-feedback-form-subtitle">
                                    <?php
                                    printf( __( 'Please do not use this form to get support, in this case please check the %s help section %s', 'auxin-elements' ),
                                           '<a href="' .$this->welcome->get_tab_link('help'). '">', '</a>'  ); ?>
                                </h4>
                                <textarea placeholder="Enter your feedback here" rows="10" name="feedback" class="large-text"></textarea>
                                <input type="text" placeholder="Email address (Optional)" name="email" class="text-input" />
                                <?php wp_nonce_field( 'phlox_feedback' ); ?>

                                <input type="submit" class="aux-wl-button aux-round aux-blue aux-wide" value="<?php esc_attr_e( 'Submit', 'auxin-elements' ); ?>" />

                                <div class="aux-sending-status">
                                    <img  class="ajax-progress aux-hide" src="<?php echo AUXIN_URL; ?>/css/images/elements/saving.gif" />
                                    <span class="ajax-response aux-hide" ><?php _e( 'Submitting your feedback ..', 'auxin-elements' ); ?></span>
                                </div>

                            </div>

                            <?php $this->send_feedback_mail(); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }


    private function send_feedback_mail(){
        if  ( ! ( ! isset( $_POST['phlox_feedback'] ) || ! wp_verify_nonce( $_POST['phlox_feedback'], 'feedback_send') ) ) {

            $email    = ! empty( $_POST["email"]    ) ? sanitize_email( $_POST["email"]  ) : 'Empty';
            $feedback = ! empty( $_POST["feedback"] ) ? esc_textarea( $_POST["feedback"] ) : '';

            if( $feedback ){
                wp_mail( 'info@averta.net', 'feedback from phlox dashboard', $feedback . chr(0x0D).chr(0x0A) . 'Email: ' . $email );
                $text = __( 'Thanks for your feedback', 'auxin-elements' );
            } else{
                $text = __('Please try again and fill up at least the feedback field.', 'auxin-elements');
            }

            printf('<p class="notification">%s</p>', $text);
        }
    }


    /**
     * Display changelogs on welcome page
     *
     * @param  string  $tab  The tab that we intent to append this section to.
     * @return void
     */
    function append_changelog( $tab ){

        if( 'dashboard' !== $tab ){
            return;
        }

        // sanitize the theme id
        $theme_id = sanitize_key( THEME_ID );

        // get remote changelog
        if( false === $changelog_info = get_transient( "auxin_{$theme_id}__remote_changelog" ) ){

            $changelog_remote = $this->get_remote_changelog( $theme_id );

            if( is_wp_error( $changelog_remote ) ){
                echo $changelog_remote->get_error_message();
                return;
            } else {
                $changelog_info = $changelog_remote;
                set_transient( "auxin_{$theme_id}__remote_changelog", $changelog_remote, 2 * HOUR_IN_SECONDS );
            }

        }

        // print the changelog
        if( $changelog_info ){ ?>
            <div class="aux-changelog-wrap">
                <div class="aux-changelog-header">
                    <h2><?php _e( 'Changelog', 'auxin-elements' ); ?></h2>
                    <div class="aux-welcome-socials">
                        <span><?php _e('Follow Us', 'auxin-elements' ); ?></span>
                        <div class="aux-welcome-social-items">
                            <a href="http://www.twitter.com/averta_ltd" class="aux-social-item aux-social-twitter"  target="_blank" title="<?php _e('Follow us on Twitter', 'auxin-elements' ); ?>"></a>
                            <a href="http://www.facebook.com/averta" class="aux-social-item aux-social-facebook" target="_blank" title="<?php _e('Follow us on Facebook', 'auxin-elements' ); ?>"></a>
                            <a href="https://www.instagram.com/averta.co/" class="aux-social-item aux-social-instagram"target="_blank" title="<?php _e('Follow us on Instagram', 'auxin-elements' ); ?>"></a>
                            <a href="https://themeforest.net/user/averta" class="aux-social-item aux-social-envato"   target="_blank" title="<?php _e('Follow us on Envato', 'auxin-elements' ); ?>"></a>
                            <a href="https://www.youtube.com/playlist?list=PL7X-1Jmy1jcdekHe6adxB81SBcrHOmLRS" class="aux-social-item aux-social-youtube"   target="_blank" title="<?php _e('Subscribe to Phlox YouTube channel', 'auxin-elements' ); ?>"></a>
                        </div>
                        <ul>

                        </ul>
                    </div>
                </div>
                <div class="aux-changelog-content">
                    <div class="aux-changelog-list"><?php echo $changelog_info; ?></div>
                </div>
            </div>
            <?php
        }

    }


    /**
     * Display video tutorials on welcome page
     *
     * @param  string  $tab  The tab that we intent to append this section to.
     * @return void
     */
    function append_tutorials( $tab ){

        if( 'help' !== $tab ){ return; }
        $video_list = $this->get_video_tutorial_list();
        ?>
        <div class="aux-setup-content">
            <div class="aux-video-list aux-grid-list aux-isotope-list" >

        <?php foreach ( $video_list as $video_id => $video_title ) { ?>
                <div class="aux-grid-item aux-iso-item grid_4" >
                    <div class="aux-grid-item-inner">
                        <div class="aux-grid-item-media">
                            <iframe width="440" height="248" src="https://www.youtube-nocookie.com/embed/<?php echo $video_id; ?>?rel=0&amp;showinfo=0"
                            frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                        </div>
                        <div class="aux-grid-item-footer">
                            <a class="aux-grid-footer-text-link" href="<?php echo esc_url( 'https://youtu.be/'. $video_id ); ?>" target="_blank"><h3><?php echo esc_html( $video_title ); ?><span class="dashicons dashicons-external"></span></h3></a>
                        </div>
                    </div>
                </div>
        <?php } ?>

            </div>
        </div>
        <?php
    }

    /**
     * Collect the plugin filters
     *
     * @return array    plugin filters
     */
    private function get_video_tutorial_list(){
        if( empty( $this->tutorial_list ) ){
            $this->tutorial_list = apply_filters( 'auxin_admin_welcome_video_tutorial_list', array() );
        }

        return $this->tutorial_list;
    }

    /**
     * Collect the video tutorilas
     *
     * @return array    video tutorilas list
     */
    public function add_video_tutorial_list( $list ){

        $new_list = array(
            'W8jkMN7EEdo' => 'Installing Phlox Pro',
            'porrf6QgjuU' => 'Configuring Menu General Options',
            'irSajN7JXQQ' => 'Customizing Header Menu',
            'AU6qT84scSY' => 'Adding a Burger Menu',
            'gVm9EJ6BrAI' => 'Customizing Post Formats',
            'YkXKxgWruDk' => 'Customizing Post Options',
            'UIVE7ZWbSoI' => 'Displaying Blog Slider',
            'b37PUx76ejc' => 'Customizing Blog Page Options',
            '09pnnTaYAto' => 'Organizing Blog with Category',
            'NJDnhbI23P4' => 'Displaying About Author Box',
            'W-nqEKUk0Ss' => 'Displaying Related Posts on Blog',
            'qNVie3fELr4' => 'Customizing Page Options',
            'QutPg4W642A' => 'Creating Different Pages with Custom Pages',
            '8GiqLqtsWrU' => 'Configuring Layout and Design Options',
            'IWj6vbnjrUE' => 'Specifying Content and Titles Typography',
            'hNU85eRLCQg' => 'Customizing Header Section',
            'mo7hiMIQvv0' => 'Adding a Scroll to Top Button',
            'SefEG3KOYcI' => 'Customizing the Background',
            'RzVFT4UxXtw' => 'Customizing Audio and Video Player',
            'J3GO3Lt22dw' => 'Adding a Frame for Your Website',
            'DueARmwq1q4' => 'Customizing Footer Area',
            'w65-HRbMvMo' => 'Displaying Subfooter',
            'SOcYs6wJsao' => 'Displaying Subfooter Bar',
            'bcQS7iol000' => 'Customizing your Website Login Page',
            'Pi9121CAGgY' => 'Adding Custom CSS and JavaScript',
            //'A96MVeK1RCc' => 'Installing Phlox Pro',
            'kYh0z4jo6jM' => 'Creating Audio with Elementor',
            'DiiVuwhNwnU' => 'Creating Button with Elementor',
            'oi7R8iLRvCo' => 'Creating Video with Elementor',
            'gveFqSpfcQQ' => 'Creating Contact Form with Elementor',
            'ZKMypryYnto' => 'Creating Map with Elementor',
            'sOVsUu-2DHw' => 'Contact Box with Elementor'
        );

        return array_merge( $list, $new_list );
    }

    /**
     * Retrieves the changelog remotely
     *
     * @param  string $item_name  The name of the project that we intend to get the info of
     * @return string             The changelog context
     */
    private function get_remote_changelog( $item_name = '' ){

        if( empty( $item_name ) ){
            $item_name = THEME_ID;
        }

        global $wp_version;

        $args = array(
            'user-agent' => 'WordPress/'. $wp_version.'; '. get_site_url(),
            'timeout'    => ( ( defined('DOING_CRON') && DOING_CRON ) ? 30 : 10 ),
            'body'       => array(
                'action'    => 'text',
                'cat'       => 'changelog',
                'item-name' => $item_name,
                'content'   => 'list',
                'view'      => 'html',
                'limit'     => 5
            )
        );

        $request = wp_remote_get( 'http://api.averta.net/envato/items/', $args );

        if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) !== 200 ) {
            return new WP_Error( 'no_response', 'Error while receiving remote data' );
        }

        $response = $request['body'];

        return $response;
    }

}
