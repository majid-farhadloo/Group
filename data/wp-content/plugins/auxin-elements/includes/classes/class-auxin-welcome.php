<?php

// no direct access allowed
if ( ! defined('ABSPATH') )  exit;

/**
 * Auxin_Welcome class
 */
class Auxin_Welcome extends Auxin_Welcome_Base {

    /**
     * Current step
     *
     * @var string
     */
    protected $step     = '';

    /** @var array Steps for the setup wizard */
    protected $steps    = array();

	/**
	 * TGMPA instance storage
	 *
	 * @var object
	 */
	protected $tgmpa_instance;

	/**
	 * TGMPA Menu slug
	 *
	 * @var string
	 */
	protected $tgmpa_menu_slug 	= 'tgmpa-install-plugins';

	/**
	 * TGMPA Menu url
	 *
	 * @var string
	 */
	protected $tgmpa_url 		= 'themes.php?page=tgmpa-install-plugins';

    /**
     * Plugin filters
     *
     * @var array
     */
    protected $plugin_filters   = array();


	/**
	 * Holds the current instance of the theme manager
	 *
	 */
	protected static $instance 	= null;

	/**
	 * Retrieves class instance
	 *
	 * @return Auxin_Welcome
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance 	= new self;
		}

		return self::$instance;
	}


	/**
	 * Constructor
	 */
	public function __construct() {
        parent::__construct();

		$this->init_globals();
		$this->init_actions();
	}

	/**
	 * Setup the class globals.
	 *
	 */
	public function init_globals() {
		$this->page_slug       	= 'auxin-welcome';
        $this->parent_slug      = 'auxin-welcome';
	}

	/**
	 * Setup the hooks, actions and filters.
	 *
	 */
	public function init_actions() {
        // Call the parent method
        parent::init_actions();

		if ( current_user_can( 'manage_options' ) ) {

			// Disable redirect for "related posts for WordPress" plugin
            update_option('rp4wp_do_install', 0);
            // Disable redirect for the "WooCommerce" plugin
            delete_transient( '_wc_activation_redirect' );
            // Disable redirect for Phlox Pro plugin
            remove_action( 'init', 'auxpro_redirect_to_welcome_page_on_first_activation' );

			if ( class_exists( 'TGM_Plugin_Activation' ) && isset( $GLOBALS['tgmpa'] ) ) {
				add_action( 'init'					, array( $this, 'get_tgmpa_instanse' ), 30 );
				add_action( 'init'					, array( $this, 'set_tgmpa_url' ), 40 );
			}

            if( ! class_exists( 'Auxin_Demo_Importer' ) ){
                require_once( 'class-auxin-demo-importer.php' );
            }

			// Get instance of Auxin_Demo_Importer Class
			Auxin_Demo_Importer::get_instance();

            add_action( 'admin_enqueue_scripts'		, array( $this, 'enqueue_scripts' ) );
            add_filter( 'tgmpa_load'				, array( $this, 'tgmpa_load' ), 10, 1 );
            add_action( 'wp_ajax_aux_setup_plugins'	, array( $this, 'ajax_plugins' ) );

            add_action( 'wp_ajax_aux_ajax_lightbox' , array( $this, 'ajax_lightbox') );
            add_action( 'wp_ajax_aux_step_manager'  , array( $this, 'step_manager' ) );

            add_action( 'wp_ajax_aux_welcome_dismiss_notice'  , array( $this, 'dimiss_dashboard_notice' ) );

			if( isset( $_POST['action'] ) && $_POST['action'] === "aux_setup_plugins" && wp_doing_ajax() ) {
				add_filter( 'wp_redirect', '__return_false', 999 );
			}

            Auxin_Welcome_Sections::get_instance()->page_slug = $this->page_slug;
            Auxin_Welcome_Sections::get_instance()->welcome   = $this;
		}
	}

    /**
     * Adds a constant class names to body on wizard page
     */
    public function add_body_class( $classes ){
        $classes = parent::add_body_class( $classes );

        if( $this->current_tab( 'importer', 'plugins' ) ){
            $classes .= ' auxin-wizard-panel';

            // Add PRO selector, for some probable custom styles
            if( defined('THEME_PRO' ) && THEME_PRO ) {
            	$classes .= ' auxin-wizard-pro';
            }
        }

        return $classes;
    }

	/**
	 * Enqueue admin scripts
	 *
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'auxin-admin-plugins'	, AUXELS_ADMIN_URL . '/assets/js/plugins.min.js', array('jquery'), '1.7.2', true );

        if( $this->current_tab( 'importer', 'plugins' ) ){
    		wp_enqueue_script( 'auxin-wizard'		, AUXELS_ADMIN_URL . '/assets/js/wizard.js'  			, array(
    			'jquery',
    			'jquery-masonry',
    			'auxin_plugins',
    			'auxin-admin-plugins'
    		), $this->version );

    		wp_localize_script( 'auxin-wizard', 'aux_setup_params', array(
    			'tgm_plugin_nonce' => array(
    				'update'  => wp_create_nonce( 'tgmpa-update' ),
    				'install' => wp_create_nonce( 'tgmpa-install' ),
    			),
    			'tgm_bulk_url'     => admin_url( $this->tgmpa_url ),
    			'ajaxurl'          => admin_url( 'admin-ajax.php' ),
    			'wpnonce'          => wp_create_nonce( 'aux_setup_nonce' ),
    			'imported_done'    => esc_html__( 'This demo has been successfully imported.', 'auxin-elements' ),
    			'imported_fail'    => esc_html__( 'Whoops! There was a problem in demo importing.', 'auxin-elements' ),
    			'progress_text'    => esc_html__( 'Processing: Download', 'auxin-elements' ),
    			'nextstep_text'    => esc_html__( 'Continue', 'auxin-elements' ),
    			'activate_text'    => esc_html__( 'Install Plugins', 'auxin-elements' ),
    			'makedemo_text'    => esc_html__( 'Import Content', 'auxin-elements' ),
    			'btnworks_text'    => esc_html__( 'Installing...', 'auxin-elements' ),
    			'onbefore_text'    => esc_html__( 'Please do not refresh or leave the page during the wizard\'s process.', 'auxin-elements' )
    		) );
        }

	}

    /**
     * Check for TGMPA load
     *
     */
	public function tgmpa_load( $status ) {
		return is_admin() || current_user_can( 'install_themes' );
	}

	/**
	 * Get configured TGMPA instance
	 *
	 */
	public function get_tgmpa_instanse() {
		$this->tgmpa_instance 	= call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
	}

	/**
	 * Update $tgmpa_menu_slug and $tgmpa_parent_slug from TGMPA instance
	 *
	 */
	public function set_tgmpa_url() {
		$this->tgmpa_menu_slug 	= ( property_exists( $this->tgmpa_instance, 'menu' ) ) ? $this->tgmpa_instance->menu : $this->tgmpa_menu_slug;
		$this->tgmpa_menu_slug 	= apply_filters( $this->theme_id . '_theme_setup_wizard_tgmpa_menu_slug', $this->tgmpa_menu_slug );

		$tgmpa_parent_slug 		= ( property_exists( $this->tgmpa_instance, 'parent_slug' ) && $this->tgmpa_instance->parent_slug !== 'themes.php' ) ? 'admin.php' : 'themes.php';

		$this->tgmpa_url 		= apply_filters( $this->theme_id . '_theme_setup_wizard_tgmpa_url', $tgmpa_parent_slug . '?page=' . $this->tgmpa_menu_slug );
	}

    /**
     * Register the admin menu
     *
     * @return void
     */
    public function register_admin_menu() {

        $menu_args = $this->get_admin_menu_args();

        /*  Register root setting menu
        /*-----------------------------*/
        add_menu_page(
            $menu_args['title'],         // [Title]    The title to be displayed on the corresponding page for this menu
            $menu_args['name'],          // [Text]     The text to be displayed for this actual menu item
            $menu_args['compatibility'],
            $this->page_slug,            // [ID/slug]  The unique ID - that is, the slug - for this menu item
            array( $this, 'render'),      // [Callback] The name of the function to call when rendering the menu for this page
            '',                          // icon_url
            3                            // [Position] The position in the menu order this menu should appear 3 means after dashboard
        );

        /*  Add a menu separator
        /*-----------------------------*/
        add_menu_page(
            '',
            '',
            'read',
            'wp-menu-separator',
            '',
            '',
            4
        );

        $this->add_submenus();
    }


    /**
     * Add submenu for admin menu
     *
     * @return void
     */
    protected function add_submenus(){

        global $submenu;

        $menu_args = $this->get_admin_menu_args();

        $sections  = $this->get_sections();

        foreach ( $sections as $section_id => $section ) {
            if( ! empty( $section['add_admin_menu'] ) && $section['add_admin_menu'] ){

                if( ! empty( $section['url'] ) ){

                    $submenu[ $this->page_slug ][] = array(
                        $section['label'],
                        $menu_args['compatibility'],
                        esc_url( $section['url'] )
                    );

                } else {
                    add_submenu_page(
                        $this->page_slug,
                        $section['label'],
                        $section['label'],
                        $menu_args['compatibility'],
                        $this->get_page_rel_tab( $section_id )
                    );
                }

            }
        }

        $submenu[ $this->page_slug ]['0']['0'] = __( 'Dashboard', 'auxin-elements' );
        unset( $submenu[ $this->page_slug ]['1'] );
    }

    /**
     * Dimiss and close dashboard notice
     *
     * @return void
     */
    public function dimiss_dashboard_notice(){
        // verify nonce
        if ( ! isset( $_POST['auxnonce'] ) || ! wp_verify_nonce( $_POST['auxnonce'], "aux_setup_nonce") ) {
            wp_send_json_error( array( 'message' => __( 'Authorization failed! Notice cannot be closed.', 'auxin-elements' ) ) );
        }

        $notice_id = ! empty( $_POST['_id'] ) ? $_POST['_id'] : '';

        if ( empty( $notice_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Notice cannot be closed. Notice ID is required ..', 'auxin-elements' ) ) );
        }

        if( Auxin_Dashboard_Notice::get_instance()->disable_notice( $notice_id ) ){
            wp_send_json_success( array( 'message' => __( 'Successfully dismissed ..', 'auxin-elements' ) ) );
        }

        wp_send_json_error( array( 'message' => __( 'Notice cannot be closed. Invalid notice ID is required.', 'auxin-elements' ) ) );
    }

	/*-----------------------------------------------------------------------------------*/
	/*  Start Setup Wizard
	/*-----------------------------------------------------------------------------------*/

    /**
     * Retrieves the welcome page relative path
     *
     * @return string     Page relative path
     */
    public function get_page_rel_path(){
        return 'admin.php?page=' . $this->page_slug;
    }

	/**
	 * Display Alert Message
	 */
	public function display_alerts( $message_body = '', $class_name = '' ){
	?>
		<div class="aux-alert <?php echo esc_attr( $class_name ); ?>">
			<p>
				<?php
					if( empty($message_body ) ) {
						echo sprintf("<strong>%s</strong> %s", esc_html__( 'Note:', 'auxin-elements' ), __( 'You are recommended to install Phlox exclusive plugins in order to enable all features.', 'auxin-elements' ) );
					} else {
						echo esc_html( $message_body );
					}
				?>
			</p>
		</div>
	<?php
	}


    /**
     * Collect the plugin filters
     *
     * @return array    plugin filters
     */
    private function get_plugins_categories_localized(){
        if( empty( $this->plugin_filters ) ){
            $this->plugin_filters = apply_filters( 'auxin_admin_welcome_plugins_categories_localized', array() );
        }

        return $this->plugin_filters;
    }


    /**
     * Collect all plugin categories from bundled plugins
     *
     * @return array    plugin categories
     */
    private function get_plugins_categories( $all_plugins ){
        $plugin_categories = array();

        foreach ( $all_plugins as $slug => $plugin ) {
            $filter_terms = '';
            if( ! empty( $plugin['categories'] ) ){
                if( is_array( $plugin['categories'] ) ){
                    $plugin_categories = array_merge( $plugin_categories, $plugin['categories'] );
                }
            }
        }

        return array_unique( $plugin_categories );
    }


	/*-----------------------------------------------------------------------------------*/
	/*  Third step (Plugin installation)
	/*-----------------------------------------------------------------------------------*/
	public function setup_plugins() {

		tgmpa_load_bulk_installer();
		// install plugins with TGM.
		if ( ! class_exists( 'TGM_Plugin_Activation' ) || ! isset( $GLOBALS['tgmpa'] ) ) {
			die( 'Failed to find TGM' );
		}
		$url     = wp_nonce_url( add_query_arg( array( 'plugins' => 'go' ) ), 'aux-setup' );
		$plugins = $this->get_plugins();

		// copied from TGM

		$method = ''; // Leave blank so WP_Filesystem can populate it as necessary.
		$fields = array_keys( $_POST ); // Extra fields to pass to WP_Filesystem.

		if ( false === ( $creds = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields ) ) ) {
			return true; // Stop the normal page form from displaying, credential request form will be shown.
		}

		// Now we have some credentials, setup WP_Filesystem.
		if ( ! WP_Filesystem( $creds ) ) {
			// Our credentials were no good, ask the user for them again.
			request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields );

			return true;
		}

		$embeds_plugins_desc = array(
			'js_composer'        => 'Drag and drop page builder for WordPress. Take full control over your WordPress site, build any layout you can imagine – no programming knowledge required.',
			'Ultimate_VC_Addons' => 'Includes Visual Composer premium addon elements like Icon, Info Box, Interactive Banner, Flip Box, Info List & Counter. Best of all - provides A Font Icon Manager allowing users to upload / delete custom icon fonts.',
			'masterslider'       => 'Master Slider is the most advanced responsive HTML5 WordPress slider plugin with touch swipe navigation that works smoothly on devices too.',
			'go_pricing'         => 'The New Generation Pricing Tables. If you like traditional Pricing Tables, but you would like get much more out of it, then this rodded product is a useful tool for you.',
            'waspthemes-yellow-pencil'      => 'The most advanced visual CSS editor. Customize any page in real-time without coding.',
			'auxin-the-news'     => 'Publish news easily and beautifully with Phlox theme.',
			'auxin-pro-tools'    => 'Premium features for Phlox theme.',
			'auxin-shop'         => 'Make a shop in easiest way using phlox theme.',
			'envato-market'      => 'WP Theme Updater based on the Envato WordPress Toolkit Library and Pixelentity class from ThemeForest forums.'
		);

		/* If we arrive here, we have the filesystem */

		?>
        <div class="aux-setup-content">
            <div class="aux-section-content-box">
                <h3 class="aux-content-title"><?php _e('Recommended Plugins', 'auxin-elements' ); ?></h3>
                <p style="margin-bottom:0;"><?php esc_html_e( 'The following is a list of best integrated plugins for Phlox theme, you can install them from here and add or remove them later on WordPress plugins page.', 'auxin-elements' ); ?></p>
        		<p><?php esc_html_e( 'We recommend you to install only the plugins under "Essential" tab, and avoid installing all of plugins.', 'auxin-elements' ); ?></p>

                <div class="aux-plugins-step aux-has-required-plugins aux-fadein-animation">
                    <?php
                    if ( count( $plugins['all'] ) ) {

                        $plugin_categories           = $this->get_plugins_categories( $plugins['all'] );
                        $plugin_categories_localized = $this->get_plugins_categories_localized();

                        // -----------------------------------------------------
                        ?>

        				<div class="aux-table">
        					<section class="auxin-list-table">

                                <div class="aux-isotope-filters aux-filters aux-underline aux-clearfix aux-togglable aux-clearfix aux-center">
                                    <div class="aux-select-overlay"></div>
                                    <ul>
                                        <li data-filter="all"><a href="#" class="aux-selected"><span data-select="<?php _e('Recent', 'auxin-elements'); ?>"><?php _e('Recent', 'auxin-elements'); ?></span></a></li>
                                    <?php
                                        foreach ( $plugin_categories_localized as $filter_slug => $filter_label ) {
                                            if( in_array( $filter_slug, $plugin_categories ) ){
                                                echo '<li data-filter="'. esc_attr( $filter_slug . '-plugins' ) .'"><a href="#"><span data-select="'. $filter_label .'">'. $filter_label .'</span></a></li>';
                                            }
                                        }
                                    ?>
                                    </ul>
                                </div>

                                <header class="aux-table-heading aux-table-row aux-clearfix">
                                    <div id="cb" class="manage-column aux-column-cell column-cb check-column">
                                        <label class="screen-reader-text" for="cb-select-all"><?php esc_html_e( 'Select All', 'auxin-elements' ); ?></label>
                                        <input id="cb-select-all" type="checkbox" style="display:none;">
                                    </div>
                                    <div class="manage-column aux-column-cell column-thumbnail"></div>
                                    <div scope="col" id="name" class="manage-column aux-column-cell column-name"><?php esc_html_e( 'Name', 'auxin-elements' ); ?></div>
                                    <div scope="col" id="description" class="manage-column aux-column-cell column-description"><?php esc_html_e( 'Description', 'auxin-elements' ); ?></div>
                                    <div scope="col" id="status" class="manage-column aux-column-cell column-status"><?php esc_html_e( 'Status', 'auxin-elements' ); ?></div>
                                    <div scope="col" id="version" class="manage-column aux-column-cell column-version"><?php esc_html_e( 'Version', 'auxin-elements' ); ?></div>
                                </header>

        					    <div class="aux-wizard-plugins aux-table-body aux-isotope-plugins-list aux-clearfix">
        							<?php
        							foreach ( $plugins['all'] as $slug => $plugin ) {

                                        // Collect plugin filters for current item
                                        $filter_terms = '';
                                        if( ! empty( $plugin['categories'] ) ){
                                            if( is_array( $plugin['categories'] ) ){
                                                foreach ( $plugin['categories'] as $category ) {
                                                    $filter_terms .=  $category . '-plugins ';
                                                }
                                            }
                                        }

        								if( $this->tgmpa_instance->is_plugin_installed( $slug ) ) {
        									$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin['file_path'] );
        								} else {
        									$plugin_data = $this->get_plugin_data_by_slug( $slug );
        								}
        							?>
        								<div class="aux-plugin aux-table-row aux-iso-item <?php echo esc_attr( $filter_terms ); ?>" data-slug="<?php echo esc_attr( $slug ); ?>">
        						            <div scope="row" class="check-column aux-column-cell">
        						                <input class="aux-check-column" name="plugin[]" value="<?php echo esc_attr( $slug ); ?>" type="checkbox">
        						                <div class="spinner"></div>
        						            </div>
        						            <div class="thumbnail column-thumbnail aux-column-cell" data-colname="Thumbnail">
        						            	<?php
                                                    $thumbnail = "https://ps.w.org/{$plugin['slug']}/assets/icon-128x128.png";

        								            if( isset( $plugin['thumbnail'] ) ){
                                                        if( 'custom' == $plugin['thumbnail'] ){
                                                            $thumbnail = AUXELS_ADMIN_URL . '/assets/images/welcome/' . $plugin['slug'] . '-plugin.png';
                                                        } elseif( 'default' === $plugin['thumbnail'] ){
                                                            $thumbnail = AUXELS_ADMIN_URL . '/assets/images/welcome/def-plugin.png';
                                                        } elseif( ! empty( $plugin['thumbnail'] ) ){
                                                            $thumbnail = $plugin['thumbnail'];
                                                        }
                                                    }
        								            ?>
        	        							<img src="<?php echo esc_url( $thumbnail ); ?>" width="64" height="64" />
        						            </div>
        						            <div class="name column-name aux-column-cell" data-colname="Plugin"><?php echo esc_html( $plugin['name'] ); ?></div>
        						            <div class="description column-description aux-column-cell" data-colname="Description">
        						            <?php
        						            	$description = '';
                                                if( isset( $plugin_data['Description'] ) ) {
                                                    $description = $plugin_data['Description'];
                                                } else if ( isset( $embeds_plugins_desc[ $plugin['slug'] ] ) ){
                                                    $description = $embeds_plugins_desc[ $plugin['slug'] ];
                                                }
                                                if( $description ){
                                                    echo '<p>'. $description .'</p>';
                                                }
        										if ( ! empty( $plugin['badge'] ) ) {
        										    echo '<span class="aux-label aux-exclusive-label">' . esc_html( $plugin['badge'] ) . '</span>';
        										}
        						            ?>
        						            </div>
        						            <div class="status column-status aux-column-cell" data-colname="Status">
        										<span>
    		    								<?php
    											    if ( isset( $plugins['install'][ $slug ] ) ) {
    												    echo esc_html__( 'Not Installed', 'auxin-elements' );
    											    } elseif ( isset( $plugins['activate'][ $slug ] ) ) {
    												    echo esc_html__( 'Not Activated', 'auxin-elements' );
    											    }
    										    ?>
        		    							</span>
        						            </div>
        					                <div class="version column-version aux-column-cell" data-colname="Version">
        					                	<?php if( isset( $plugin_data['Version'] ) ) { ?>
        					                    <span><?php echo esc_html( $plugin_data['Version'] ); ?></span>
        					                    <?php } ?>
        					                </div>
        								</div>
        							<?php } ?>
        					    </div>
        					</section>
        				</div>

        				<div class="clear"></div>

        				<div class="aux-sticky">
        					<div class="aux-setup-actions step">
        						<a href="#"
        						   class="aux-button aux-primary install-plugins disabled button-next"
        						   data-callback="install_plugins"><?php esc_html_e( 'Install Plugins', 'auxin-elements' ); ?></a>
        						<?php wp_nonce_field( 'aux-setup' ); ?>
        					</div>
        				</div>

        			<?php
        			} else { ?>

        	 			<?php $this->display_alerts( esc_html__( 'Good news! All plugins are already installed and up to date. Please continue.', 'auxin-elements'  ) , 'success' ); ?>

        			<?php
        			} ?>
        		</div>
            </div>
        </div>
		<?php
	}

	/**
	 * Output the tgmpa plugins list
	 */
	private function get_plugins( $custom_list = array() ) {

		$plugins  = array(
			'all'      => array(), // Meaning: all plugins which still have open actions.
			'install'  => array(),
			'update'   => array(),
			'activate' => array(),
		);

		foreach ( $this->tgmpa_instance->plugins as $slug => $plugin ) {

			if( ! empty( $custom_list ) && ! in_array( $slug, $custom_list ) ){
				// This condition is for custom requests lists
				continue;
			} elseif( $this->tgmpa_instance->is_plugin_active( $slug ) && false === $this->tgmpa_instance->does_plugin_have_update( $slug ) ) {
				// No need to display plugins if they are installed, up-to-date and active.
				continue;
			} else {
				$plugins['all'][ $slug ] = $plugin;

				if ( ! $this->tgmpa_instance->is_plugin_installed( $slug ) ) {
					$plugins['install'][ $slug ] = $plugin;
				} else {

					if ( false !== $this->tgmpa_instance->does_plugin_have_update( $slug ) ) {
						$plugins['update'][ $slug ] = $plugin;
					}
					if ( $this->tgmpa_instance->can_plugin_activate( $slug ) ) {
						$plugins['activate'][ $slug ] = $plugin;
					}

				}
			}
		}

		return $plugins;
	}

	/**
	 * Returns the plugin data from WP.org API
	 */
	private function get_plugin_data_by_slug( $slug = '' ) {

		if ( empty( $slug ) ) {
			return false;
		}

	    $key = sanitize_key( 'auxin_plugin_data_'.$slug );

	    if ( false === ( $plugins = get_transient( $key ) ) ) {
			$args = array(
				'slug' => $slug,
				'fields' => array(
			 		'short_description' => true
				)
			);
			$response = wp_remote_post(
				'http://api.wordpress.org/plugins/info/1.0/',
				array(
					'body' => array(
						'action' => 'plugin_information',
						'request' => serialize( (object) $args )
					)
				)
			);
			$data    = unserialize( wp_remote_retrieve_body( $response ) );

			$plugins = is_object( $data ) ? array( 'Description' => $data->short_description , 'Version' => $data->version ) : false;

			// Set transient for next time... keep it for 24 hours
			set_transient( $key, $plugins, 24 * HOUR_IN_SECONDS );

	    }

	    return $plugins;
	}

	/**
	 * Plugins AJAX Process
	 */
	public function ajax_plugins() {
		if ( ! check_ajax_referer( 'aux_setup_nonce', 'wpnonce' ) || empty( $_POST['slug'] ) ) {
			wp_send_json_error( array( 'error' => 1, 'message' => esc_html__( 'No Slug Found', 'auxin-elements' ) ) );
		}
		$json = array();
		// send back some json we use to hit up TGM
		$plugins = $this->get_plugins();
		// what are we doing with this plugin?
		foreach ( $plugins['activate'] as $slug => $plugin ) {
			if ( $slug === 'related-posts-for-wp' ) {
				update_option( 'rp4wp_do_install', false );
			}
			if ( $_POST['slug'] == $slug ) {
				$json = array(
					'url'           => admin_url( $this->tgmpa_url ),
					'plugin'        => array( $slug ),
					'tgmpa-page'    => $this->tgmpa_menu_slug,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-activate',
					'action2'       => - 1,
					'message'       => esc_html__( 'Activating', 'auxin-elements' ),
				);
				break;
			}
		}
		foreach ( $plugins['update'] as $slug => $plugin ) {
			if ( $_POST['slug'] == $slug ) {
				$json = array(
					'url'           => admin_url( $this->tgmpa_url ),
					'plugin'        => array( $slug ),
					'tgmpa-page'    => $this->tgmpa_menu_slug,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-update',
					'action2'       => - 1,
					'message'       => esc_html__( 'Updating', 'auxin-elements' ),
				);
				break;
			}
		}
		foreach ( $plugins['install'] as $slug => $plugin ) {
			if ( $_POST['slug'] == $slug ) {
				$json = array(
					'url'           => admin_url( $this->tgmpa_url ),
					'plugin'        => array( $slug ),
					'tgmpa-page'    => $this->tgmpa_menu_slug,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-install',
					'action2'       => - 1,
					'message'       => esc_html__( 'Installing', 'auxin-elements' ),
				);
				break;
			}
		}

		if ( $json ) {
			$json['hash'] = md5( serialize( $json ) ); // used for checking if duplicates happen, move to next plugin
			wp_send_json( $json );
		} else {
			wp_send_json( array( 'done' => 1, 'message' => esc_html__( 'Activated', 'auxin-elements' ) ) );
		}
		exit;

	}


	/*-----------------------------------------------------------------------------------*/
	/*  Online Demo Importer
	/*-----------------------------------------------------------------------------------*/

	public function setup_importer() {
		// Get the available demos list from Averta API
		$data = $this->parse_json();
	?>
        <div class="aux-setup-content">

		<div class="aux-demo-importer-step aux-fadein-animation">

            <div class="aux-demo-list aux-grid-list aux-isotope-list">
			<?php
				foreach ( $data as $key => $args ) {

                    // Get last imported demo data
                    $last_demo_imported = get_option( 'auxin_last_imported_demo' );
                    $last_demo_imported = ! empty( $last_demo_imported ) && $last_demo_imported['id'] == $args['site_id'] ? ' aux-last-imported-demo' : '';

                    $is_demo_allowed = ( defined('THEME_PRO' ) && THEME_PRO ) || $args['type'] === 'free';

					echo '<div data-demo-id="demo-'.$args['site_id'].'" class="aux-grid-item aux-iso-item grid_4'.$last_demo_imported.'">';
                    echo '<div class="aux-grid-item-inner">';
                        echo '<div class="aux-grid-item-media">';
				            echo '<img width="579" class="demo_thumbnail" src='.$args['thumbnail'].'>';
					if( ! $is_demo_allowed ) {
                        echo '<img class="premium_badge" alt="This is a premium demo" src="'. esc_url( AUXELS_ADMIN_URL . '/assets/images/welcome/pro-badge.png' ) .'">';
					}
                        echo '</div>';
                    ?>
                        <div class="aux-grid-item-footer">
                            <h3><?php echo $args['title']; ?></h3>
                            <div class="aux-grid-item-buttons aux-clearfix">
                            <?php
                            if( $is_demo_allowed ) {
                                $color_class    = " aux-install-demo aux-iconic-action aux-green2";
                                $btn_label      = esc_html__( 'Import', 'auxin-elements' );
                                $import_btn_url = add_query_arg( array( 'action' => 'aux_ajax_lightbox', 'key' => $key, 'nonce' => wp_create_nonce( 'aux-open-lightbox' ) ), admin_url( 'admin-ajax.php' ));
                            } else {
                                $color_class    = " aux-blue aux-pro-demo aux-locked-demo aux-iconic-action";
                                $btn_label      = esc_html__( 'Unlock', 'auxin-elements' );
                                $import_btn_url = esc_url( 'http://phlox.pro/go-pro/?utm_source=phlox-welcome&utm_medium=phlox-free&utm_campaign=phlox-go-pro&utm_content=demo-unlock&utm_term='. $args['site_id'] );
                            }

                            ?>
                                <a target="_blank" href="<?php echo $import_btn_url; ?>"
                                    class="aux-wl-button aux-outline aux-round aux-large <?php echo esc_attr( $color_class ); ?>"><?php echo $btn_label; ?></a>
                                <a target="_blank" href="<?php echo ! empty( $args['url'] ) ? esc_url( $args['url'] .'&utm_term='.$args['site_id'] ) : '#'; ?>"
                                   class="aux-wl-button aux-outline aux-round aux-transparent aux-large aux-preview"><?php esc_html_e( 'Preview', 'auxin-elements' ); ?></a>
                           </div>
                        </div>
                    </div>
					<?php
					echo '</div>';
				}
			?>
			</div>

			<div class="clear"></div>

		</div>

        </div>

	<?php
	}

	/**
	 * Parse the demos list API
	 */
    public function parse_json( $url = 'http://api.phlox.pro/demos/all' ) {

    	// $url = 'http://demo.phlox.pro/api/?demo_list&demo=beta&key=averta_avtdph';
    	$key = sanitize_key('auxin_available_demos');

        if ( ! get_transient( $key ) || isset( $_GET['remove_transient'] ) ) {
            //Get JSON
            $request    = wp_remote_get( $url );
            //If the remote request fails, wp_remote_get() will return a WP_Error
            if( is_wp_error( $request ) || ! current_user_can( 'import' ) ) wp_die();
            //proceed to retrieving the data
            $body       = wp_remote_retrieve_body( $request );
            //translate the JSON into Array
            $data       = json_decode( $body, true );
            //Add transient
            set_transient( $key, $data, 24 * HOUR_IN_SECONDS );
        }

        return get_transient( $key );

    }


	/*-----------------------------------------------------------------------------------*/
	/*  Step manager in modal
	/*-----------------------------------------------------------------------------------*/

	public function ajax_lightbox() {

        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'aux-open-lightbox' ) ) {
            // This nonce is not valid.
            wp_die( 'Security Token Error!' );
        }

		$data = $this->parse_json();

		if( ! isset( $_GET['key'] ) || empty( $data ) || ! array_key_exists( $_GET['key'] , $data ) ) {
			wp_die( 'An Error Occurred!' );
		}

		$args = $data[ $_GET['key'] ];

		ob_start();

	?>
		<div id="demo-<?php echo esc_attr( $args['site_id'] ); ?>" class="aux-demo-lightbox">
			<div class="aux-modal-item clearfix aux-has-required-plugins">
				<div class="grid_5 no-gutter aux-media-col" style="background-image: url(<?php echo esc_url( $args['screen'] ); ?>);" >
				</div>
				<div class="grid_7 no-gutter aux-steps-col">
					<div class="aux-setup-demo-content aux-content-col aux-step-import-notice">
                        <img src="<?php echo esc_url( AUXELS_ADMIN_URL . '/assets/images/welcome/import-notice.svg' ); ?>" />
                        <div><h2 class="aux-step-import-title aux-iconic-title"><?php esc_html_e( 'Notice' ); ?></h2></div>
                        <p class="aux-step-description">
                        <?php esc_html_e( "For better and faster result, it's recommended to install the demo on a clean WordPress website.", 'auxin-elements' ); ?>
                        </p>
					</div>
					<div class="aux-setup-demo-actions">
						<div class="aux-return-back">
                            <a href="#" data-next-step="2" class="aux-button aux-next-step aux-primary aux-medium" data-args="<?php echo htmlspecialchars( wp_json_encode( $args ), ENT_QUOTES, 'UTF-8' ); ?>" data-step-nonce="<?php echo wp_create_nonce( 'aux-step-manager' ); ?>">
                            	<?php _e( 'Continue', 'auxin-elements' ); ?>
                       		</a>
                            <a href="#" class="aux-button aux-outline aux-round aux-transparent aux-medium aux-pp-close">
                            	<?php _e( 'Cancel', 'auxin-elements' ); ?>
                       		</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php

		wp_die( ob_get_clean() );
	}


	public function step_manager() {
		$next_step = $_POST['next_step'];
		$nonce     = $_POST['nonce'];
		$args      = $_POST['args'];

		$steps     = array(
        	'1' => array(
				'method' => 'first_step',
				'next'   => '2'
        	),
        	'2' => array(
				'method' => 'second_step',
				'next'   => '3'
        	),
        	'3' => array(
				'method' => 'third_step',
				'next'   => '4'
        	),
        	'4' => array(
				'method' => 'fourth_step',
				'next'   => ''
        	)
        );

        if ( ! wp_verify_nonce( $nonce, 'aux-step-manager' ) ) {
            // This nonce is not valid.
            wp_send_json_error( esc_html__( 'An error occurred!', 'auxin-elements' ) );
        } elseif( ! $next_step || $steps[$next_step]['method'] == '' ){
        	wp_send_json_error( esc_html__( 'Method not exist!', 'auxin-elements' ) );
        }

		wp_send_json_success(
			array(
				'markup' => call_user_func( array( $this, $steps[$next_step]['method'] ), $args, $steps[$next_step]['next'] )
			)
		);

	}

	public function first_step( array $args, $next_step ) {
		ob_start();
		?>
			<div class="aux-setup-demo-content aux-content-col">
			    <h2><?php esc_html_e( 'Required Plugins for this demo.' ); ?></h2>
                <p class="aux-step-description">
                <?php esc_html_e( "For better and faster install proccess it's recommanded to install demo on a clean wordpress website.", 'auxin-elements' ); ?>
                </p>
			</div>
			<div class="aux-setup-demo-actions">
				<div class="aux-return-back">
                    <a href="#" data-next-step="<?php echo esc_attr( $next_step ); ?>" class="aux-button aux-next-step aux-primary aux-medium" data-args="<?php echo htmlspecialchars( wp_json_encode($args), ENT_QUOTES, 'UTF-8' ); ?>" data-step-nonce="<?php echo wp_create_nonce( 'aux-step-manager' ); ?>">
                    	<?php _e( 'Continue', 'auxin-elements' ); ?>
               		</a>
                    <a href="#" class="aux-button aux-outline aux-round aux-transparent aux-medium aux-pp-close">
                    	<?php _e( 'Cancel', 'auxin-elements' ); ?>
               		</a>
				</div>
			</div>
		<?php
		return ob_get_clean();
	}

	public function second_step( array $args, $next_step ) {

        // Goto next step, if no required plugins found
        if( ! isset( $args['plugins'] ) ) {
            return call_user_func( array( $this, 'third_step' ), $args, '4' );
        }

		$plugins = $this->get_plugins( $args['plugins'] );
		$has_plugin_required = ! empty($args['plugins'] ) && ! empty( $plugins['all'] );

		if( $has_plugin_required ) :
			ob_start();
		?>
				<div class="aux-setup-demo-content aux-content-col aux-install-plugins">
			        <h2><?php esc_html_e( 'Required Plugins for this demo.' ); ?></h2>
					<p class="aux-step-description"><?php esc_html_e( 'The following plugins are required to be installed for this demo.', 'auxin-elements' ); ?></p>
					<ul class="aux-wizard-plugins">
					<?php
					foreach ( $plugins['all'] as $slug => $plugin ) { ?>
						<li class="aux-plugin" data-slug="<?php echo esc_attr( $slug ); ?>">
							<label class="aux-control aux-checkbox">
								<?php echo esc_html( $plugin['name'] ); ?>
								<input name="plugin[]" value="<?php echo esc_attr($slug); ?>" type="checkbox" checked>
								<div class="aux-indicator"></div>
							</label>
				            <div class="status column-status">
							<?php
							    $keys = $class = '';
							    if ( isset( $plugins['install'][ $slug ] ) ) {
								    $keys 	= esc_html__( 'Ready to install', 'auxin-elements' );
								    $class  = 'install';
							    }
							    if ( isset( $plugins['activate'][ $slug ] ) ) {
								    $keys 	= esc_html__( 'Not activated', 'auxin-elements' );
								    $class  = 'activate';
							    }
							    if ( isset( $plugins['update'][ $slug ] ) ) {
								    $keys 	= esc_html__( 'Ready to update', 'auxin-elements' );
								    $class  = 'update';
							    }
						    ?>
								<span class="<?php echo $class ?>">
									<?php echo $keys; ?>
								</span>
								<div class="spinner"></div>
				            </div>
						</li>
					<?php
					}
					?>
					</ul>
				</div>
				<div class="aux-setup-demo-actions">
					<div class="aux-return-back">
						<a 	href="#"
							class="aux-button aux-medium install-plugins aux-primary button-next"
							data-callback="install_plugins"
							data-next-step="<?php echo esc_attr( $next_step ); ?>"
							data-args="<?php echo htmlspecialchars( wp_json_encode($args), ENT_QUOTES, 'UTF-8' ); ?>"
							data-step-nonce="<?php echo wp_create_nonce( 'aux-step-manager' ); ?>"
						><?php esc_html_e( 'Install Plugins', 'auxin-elements' ); ?></a>
	                    <a href="#" class="aux-button aux-outline aux-round aux-transparent aux-medium aux-pp-close">
	                    	<?php _e( 'Cancel', 'auxin-elements' ); ?>
	               		</a>
					</div>
				</div>
		<?php
			return ob_get_clean();
		else :
			return call_user_func( array( $this, 'third_step' ), $args, '4' );
		endif;
	}

	public function third_step( array $args, $next_step ) {
		ob_start();
		?>
			<div class="aux-setup-demo-content aux-content-col aux-install-demos">
				<h2><?php esc_html_e( 'Import Demo Content of Phlox Theme.' ); ?></h2>

				<form id="aux-import-data-<?php echo esc_attr( $args['site_id'] ); ?>" class="aux-import-parts">
					<div class="complete aux-border is-checked">
					    <label class="aux-control aux-radio">
					    	<?php esc_html_e( 'Complete pre-build Website', 'auxin-elements' ); ?>
					      	<input type="radio" name="import" value="complete" checked="checked" />
					      	<div class="aux-indicator"></div>
					    </label>
					    <label class="aux-control aux-checkbox">
					    	<?php esc_html_e( 'Import media (images, videos, etc.)', 'auxin-elements' ); ?>
					      	<input type="checkbox" name="import-media" checked="checked" />
					      	<div class="aux-indicator"></div>
					    </label>
					</div>
					<div class="custom aux-border">
					    <label class="aux-control aux-radio">
					    	<?php esc_html_e( 'Selected Data Only', 'auxin-elements' ); ?>
					      	<input type="radio" name="import" value="custom" />
					      	<div class="aux-indicator"></div>
					    </label>
						<div class="one_half no-gutter">
						    <label class="aux-control aux-checkbox">
						    	<?php esc_html_e( 'Posts/Pages', 'auxin-elements' ); ?>
						      	<input type="checkbox" name="posts" />
						      	<div class="aux-indicator"></div>
						    </label>
					    	<label class="aux-control aux-checkbox">
						    	<?php esc_html_e( 'Media', 'auxin-elements' ); ?>
						      	<input type="checkbox" name="media" />
						      	<div class="aux-indicator"></div>
						    </label>
					    	<label class="aux-control aux-checkbox">
						    	<?php esc_html_e( 'Widgets', 'auxin-elements' ); ?>
						      	<input type="checkbox" name="widgets" />
						      	<div class="aux-indicator"></div>
						    </label>
			    		</div>
			    		<div class="one_half no-gutter right-half">
					    	<label class="aux-control aux-checkbox">
						    	<?php esc_html_e( 'Menus', 'auxin-elements' ); ?>
						      	<input type="checkbox" name="menus" />
						      	<div class="aux-indicator"></div>
						    </label>
					    	<label class="aux-control aux-checkbox">
						    	<?php esc_html_e( 'Theme Options', 'auxin-elements' ); ?>
						      	<input type="checkbox" name="options" />
						      	<div class="aux-indicator"></div>
						    </label>
					    	<label class="aux-control aux-checkbox">
						    	<?php esc_html_e( 'MasterSlider (If Available)', 'auxin-elements' ); ?>
						      	<input type="checkbox" name="masterslider" />
						      	<div class="aux-indicator"></div>
						    </label>
			    		</div>
					</div>
				</form>
			</div>
            <div class="aux-setup-demo-content aux-content-col aux-install-demos-waiting hide">
                <img src="<?php echo esc_url( AUXELS_ADMIN_URL . '/assets/images/welcome/importing-cloud.svg' ); ?>" />
                <h2><?php esc_html_e( 'Importing Demo Content is in Progress...' ); ?></h2>
                <p class="aux-step-description"><?php esc_html_e( 'This process may take 20 to 30 minutes to complete, please do not close or refresh this page.', 'auxin-elements' ); ?></p>
            </div>
			<div class="aux-setup-demo-actions">
				<div class="aux-return-back">
					<a 	href="#"
						class="aux-button aux-medium aux-primary button-next"
						data-nonce="<?php echo wp_create_nonce( 'aux-import-demo-' . $args['site_id'] ); ?>"
						data-import-id="<?php echo esc_attr( $args['site_id'] ); ?>"
						data-callback="install_demos"
						data-next-step="<?php echo esc_attr( $next_step ); ?>"
						data-args="<?php echo htmlspecialchars( wp_json_encode($args), ENT_QUOTES, 'UTF-8' ); ?>"
						data-step-nonce="<?php echo wp_create_nonce( 'aux-step-manager' ); ?>"
					><?php esc_html_e( 'Import Content', 'auxin-elements' ); ?></a>
                    <a href="#" class="aux-button aux-outline aux-round aux-transparent aux-medium aux-pp-close">
                    	<?php _e( 'Cancel', 'auxin-elements' ); ?>
               		</a>
				</div>
				<div class="aux-progress hide">
					<div class="aux-big">
						<div class="aux-progress-bar aux-progress-info aux-progress-active" data-percent="100" style="transition: none; width: 100%;">
							<span class="aux-progress-label"><?php esc_html_e( 'Please wait, this may take several minutes ..', 'auxin-elements' ); ?></span>
						</div>
					</div>
				</div>
			</div>
		<?php
		return ob_get_clean();
	}

	public function fourth_step( array $args, $next_step ) {
		ob_start();
		?>
			<div class="aux-setup-demo-content aux-content-col aux-step-import-completed">
                <img src="<?php echo esc_url( AUXELS_ADMIN_URL . '/assets/images/welcome/completed.svg' ); ?>" />
                <div><h2 class="aux-step-import-title"><?php esc_html_e( 'Congratulations!' ); ?></h2></div>
                <p class="aux-step-description"><?php esc_html_e( "Demo has been successfully imported.", 'auxin-elements' ); ?></p>
            </div>
			<div class="aux-setup-demo-actions">
				<div class="aux-return-back">
                    <a href="<?php echo self_admin_url('customize.php'); ?>" class="aux-button aux-primary aux-medium" target="_blank">
                        <?php _e( 'Customize', 'auxin-elements' ); ?>
                    </a>
                    <a href="<?php echo home_url(); ?>" class="aux-button aux-round aux-green aux-medium" target="_blank">
                        <?php _e( 'Preview', 'auxin-elements' ); ?>
                    </a>
                    <a href="#" class="aux-button aux-outline aux-round aux-transparent aux-medium aux-pp-close">
                        <?php _e( 'Close', 'auxin-elements' ); ?>
                    </a>
				</div>
			</div>
		<?php
		return ob_get_clean();
	}

}
