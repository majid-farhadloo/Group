<?php

// no direct access allowed
if ( ! defined('ABSPATH') )  exit;

/**
 * Auxin_Wizard class
 */
class Auxin_Wizard {

	/**
	 * The class version number.
	 *
	 * @since 1.0
	 * @access private
	 *
	 * @var string
	 */
	protected $version 			= '1.1';

	/** @var string Current theme name, used as namespace in actions. */
	protected $theme_id 		= '';

	/** @var string Current Step */
	protected $step 			= '';

	/** @var array Steps for the setup wizard */
	protected $steps 			= array();

	/**
	 * The slug name to refer to this menu
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	protected $page_slug;

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
	 * Holds the current instance of the theme manager
	 *
	 * @since 1.0
	 * @var Auxin_Wizard
	 */
	private static $instance 	= null;

	/**
	 * @since 1.0
	 *
	 * @return Auxin_Wizard
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance 	= new self;
		}

		return self::$instance;
	}


	/**
	 * A dummy constructor to prevent this class from being loaded more than once.
	 *
	 * @see Auxin_Wizard::instance()
	 *
	 * @since 1.0
	 * @access private
	 */
	public function __construct() {
		$this->init_globals();
		$this->init_actions();
	}

	/**
	 * Setup the class globals.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function init_globals() {
		$this->theme_id      	= THEME_ID;
		$this->page_slug       	= 'auxin-wizard';
		$this->parent_slug     	= 'auxin-welcome';
	}

	/**
	 * Setup the hooks, actions and filters.
	 *
	 * @uses add_action() To add actions.
	 * @uses add_filter() To add filters.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function init_actions() {
		if ( current_user_can( 'manage_options' ) ) {
			// Disable redirect for "related posts for WordPress" plugin
            update_option('rp4wp_do_install', 0);
            // Disable redirect for the "WooCommerce" plugin
            delete_transient( '_wc_activation_redirect' );

			if ( class_exists( 'TGM_Plugin_Activation' ) && isset( $GLOBALS['tgmpa'] ) ) {
				add_action( 'init'					, array( $this, 'get_tgmpa_instanse' ), 30 );
				add_action( 'init'					, array( $this, 'set_tgmpa_url' ), 40 );
			}

            if( ! class_exists( 'Auxin_Demo_Importer' ) ){
                require_once( 'class-auxin-demo-importer.php' );
            }
			// Get instance of Auxin_Demo_Importer Class
			Auxin_Demo_Importer::get_instance();

            add_action( 'admin_menu'				, array( $this, 'admin_menus' ), 15 );
            add_action( 'admin_enqueue_scripts'		, array( $this, 'enqueue_scripts' ) );
            add_action( 'admin_init'				, array( $this, 'init_wizard_steps' ), 30 );
            add_filter( 'tgmpa_load'				, array( $this, 'tgmpa_load' ), 10, 1 );
            add_action( 'wp_ajax_aux_setup_plugins'	, array( $this, 'ajax_plugins' ) );
            add_action( 'admin_enqueue_scripts'		, array( $this, 'maybe_add_body_class_name' ) );

			if( isset( $_POST['action'] ) && $_POST['action'] === "aux_setup_plugins" && wp_doing_ajax() ) {
				add_filter( 'wp_redirect', '__return_false', 999 );
			}

		}
	}

    /**
     * Checks and adds a constant class names to body on wizard page
     */
    public function maybe_add_body_class_name(){
        $screen = get_current_screen();
        if( is_object( $screen ) && false !== strpos( $screen->id, '_' . $this->page_slug ) ){
            add_action( 'admin_body_class', array( $this, 'add_body_class_name' ) );
        }
    }

    /**
     * Adds a constant class names to body on wizard page
     */
    public function add_body_class_name( $classes ){
        $classes .= ' auxin-wizard-panel';
        // Add PRO selector, for some probable custom styles
        if( defined('THEME_PRO' ) && THEME_PRO ) {
        	$classes .= ' auxin-wizard-pro';
        }
        return $classes;
    }

	/**
	 * Enqueue admin scripts
	 *
	 * @since 1.0
	 * @access public
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'auxin-admin-plugins'	, AUXELS_ADMIN_URL . '/assets/js/plugins.min.js'	, array(
			'jquery'
		), '1.7.2', true );

		wp_enqueue_script( 'auxin-wizard'		, AUXELS_ADMIN_URL . '/assets/js/wizard.js'  			, array(
			'jquery',
			'jquery-masonry',
			'auxin_plugins',
			'auxin-admin-plugins'
		), $this->version );

		// Add Raleway font
		wp_enqueue_style( 'auxin-raleway-font', 'https://fonts.googleapis.com/css?family=Raleway:300,300i,400,400i,500,500i,600,600i,700,700i,800', array(), null  );

		wp_localize_script( 'auxin-wizard'		, 'aux_setup_params'								, array(
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
			'nextstep_text'    => esc_html__( 'Next Step', 'auxin-elements' ),
			'activate_text'    => esc_html__( 'Install Plugins', 'auxin-elements' ),
			'makedemo_text'    => esc_html__( 'Install Demo', 'auxin-elements' ),
			'btnworks_text'    => esc_html__( 'Work In Progress...', 'auxin-elements' ),
			'onbefore_text'    => esc_html__( 'Please do not refresh or leave the page during the wizard\'s process.', 'auxin-elements' )
		) );

	}

    /**
     * Check for TGMPA load
     */
	public function tgmpa_load( $status ) {
		return is_admin() || current_user_can( 'install_themes' );
	}

	/**
	 * Get configured TGMPA instance
	 *
	 * @access public
	 * @since 1.0
	 */
	public function get_tgmpa_instanse() {
		$this->tgmpa_instance 	= call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
	}

	/**
	 * Update $tgmpa_menu_slug and $tgmpa_parent_slug from TGMPA instance
	 *
	 * @access public
	 * @since 1.0
	 */
	public function set_tgmpa_url() {

		$this->tgmpa_menu_slug 	= ( property_exists( $this->tgmpa_instance, 'menu' ) ) ? $this->tgmpa_instance->menu : $this->tgmpa_menu_slug;
		$this->tgmpa_menu_slug 	= apply_filters( $this->theme_id . '_theme_setup_wizard_tgmpa_menu_slug', $this->tgmpa_menu_slug );

		$tgmpa_parent_slug 		= ( property_exists( $this->tgmpa_instance, 'parent_slug' ) && $this->tgmpa_instance->parent_slug !== 'themes.php' ) ? 'admin.php' : 'themes.php';

		$this->tgmpa_url 		= apply_filters( $this->theme_id . '_theme_setup_wizard_tgmpa_url', $tgmpa_parent_slug . '?page=' . $this->tgmpa_menu_slug );

	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {

        add_submenu_page(
            $this->parent_slug,
            sprintf( esc_html__( 'Demo Importer'		, 'auxin-elements' ), THEME_NAME_I18N ),
            sprintf( esc_html__( 'Demo Importer'	, 'auxin-elements' ), THEME_NAME_I18N ),
            apply_filters( 'auxin_theme_wizard_capability', 'manage_options' ),
            $this->page_slug,
            array( $this, 'setup_wizard')
        );

	}

    /**
     * Whether the parent menu is appearance submenu or not
     *
     * @return boolean
     */
	public function is_appearance_submenu() {
		return empty ( $GLOBALS['admin_page_hooks'][$this->parent_slug] );
	}

    /**
     * Retrieves the wizard page url
     *
     * @return string     Page url
     */
    public function get_page_link(){
        return admin_url( 'admin.php?page=' . $this->page_slug );
    }

	/**
	 * Setup steps.
	 *
	 * @since 1.0
	 * @access public
	 * @return array
	 */
	public function init_wizard_steps() {
		// First Step (Welcome)
		$this->steps = array(
			'introduction' => array(
				'name'    => esc_html__( 'Welcome', 'auxin-elements' ),
				'view'    => array( $this, 'setup_introduction' ),
				'handler' => array( $this, '' ),
			),
		);

		// Second Step (Tutorials)
		// $this->steps['tutorial_tour']       = array(
		// 	'name'    => esc_html__( 'General Setup', 'auxin-elements' ),
		// 	'view'    => array( $this, 'setup_tutorial' ),
		// 	'handler' => '',
		// );

		// Third step (Plugin installation)
		if ( class_exists( 'TGM_Plugin_Activation' ) && isset( $GLOBALS['tgmpa'] ) ) {
			$this->steps['default_plugins'] = array(
				'name'    => esc_html__( 'Plugins', 'auxin-elements' ),
				'view'    => array( $this, 'setup_plugins' ),
				'handler' => '',
			);
		}
		// Fourth step (Online Demo Importer)
		$this->steps['default_content'] = array(
			'name'    => esc_html__( 'Demo Importer', 'auxin-elements' ),
			'view'    => array( $this, 'setup_importer' ),
			'handler' => '',
		);
		// Fifth step (Final message)
		$this->steps['final_step']      = array(
			'name'    => esc_html__( 'Ready!', 'auxin-elements' ),
			'view'    => array( $this, 'setup_ready' ),
			'handler' => '',
		);

		// $this->steps = apply_filters( $this->theme_id . '_theme_setup_wizard_steps', $this->steps );

	}

	/*-----------------------------------------------------------------------------------*/
	/*  Start Setup Wizard
	/*-----------------------------------------------------------------------------------*/
	public function setup_wizard() {

		if ( empty( $_GET['page'] ) || $this->page_slug !== $_GET['page'] ) {
			return;
		}

		$this->step 		= isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

		echo '<div class="auxin-wizard-wrap">';

		$this->setup_wizard_steps();

		$show_content 		= true;
		echo '<div class="aux-setup-content">';
		if ( ! empty( $_REQUEST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
			$show_content 	= call_user_func( $this->steps[ $this->step ]['handler'] );
		}
		if ( $show_content ) {
			$this->setup_wizard_content();
		}
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Get step URL
	 */
	public function get_step_link( $step ) {
		return add_query_arg( 'step', $step, $this->get_page_link() );
	}

	/**
	 * Get next step URL
	 */
	public function get_next_step_link() {
		$keys = array_keys( $this->steps );

		return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ], remove_query_arg( 'translation_updated' ) );
	}

	/**
	 * Get next step URL
	 */
	public function get_prev_step_link() {
		$keys = array_keys( $this->steps );

		return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) - 1 ], remove_query_arg( 'translation_updated' ) );
	}

	/**
	 * Output the steps
	 */
	public function setup_wizard_steps() {
		$ouput_steps = $this->steps;
		?>
		<ol class="aux-setup-steps">
			<?php foreach ( $ouput_steps as $step_key => $step ) : ?>
				<li class="<?php
				if ( $step_key === $this->step ) {
					echo 'active';
				} elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
					echo 'done';
				} else {
					echo 'queue';
				}
				?>">
				<a href="<?php echo esc_url( $this->get_step_link( $step_key ) ); ?>"><span><?php echo esc_html( $step['name'] ); ?></span></a>
				</li>
			<?php endforeach; ?>
		</ol>
		<?php
	}

	/**
	 * Output the content for the current step
	 */
	public function setup_wizard_content() {
		isset( $this->steps[ $this->step ] ) ? call_user_func( $this->steps[ $this->step ]['view'] ) : false;
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


	/*-----------------------------------------------------------------------------------*/
	/*  First Step (Welcome)
	/*-----------------------------------------------------------------------------------*/
	public function setup_introduction() {
		if ( 0 && get_transient( 'aux_setup_complete' ) ) {
			?>
			<div class="aux-welcome-step aux-fadein-animation">
				<h1><?php printf( __( 'Welcome to Phlox %s WordPress theme', 'auxin-elements' ), '<br />' ); ?></h1>
				<p><?php esc_html_e( 'It looks like you have already run the setup!', 'auxin-elements' ); ?><br><?php esc_html_e( 'Would you like to continue this step?', 'auxin-elements' ); ?></p>
				<img src="<?php echo esc_url( AUXELS_ADMIN_URL . '/assets/images/wizard/start.png' ); ?>" width="1012" height="875">
				<div class="aux-setup-actions step">
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
						   class="aux-button aux-primary button-next"><?php esc_html_e( 'Yes & keep going', 'auxin-elements' ); ?></a>
				</div>
			</div>
			<?php
		} else {
			?>
			<div class="aux-welcome-step aux-fadein-animation">
				<div class="grid_5">
					<h1><?php printf( __( 'Welcome to Phlox %s WordPress theme', 'auxin-elements' ), '<br />' ); ?></h1>
	                <p><?php esc_html_e( 'Start Building Your Website in a Few Simple Steps', 'auxin-elements' ); ?></p>
					<div class="aux-setup-actions left step">
						<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
						   class="aux-button aux-primary button-next"><?php esc_html_e( 'Get Started', 'auxin-elements' ); ?></a>

					</div>
				</div>
				<div class="grid_7">
					<img src="<?php echo esc_url( AUXELS_ADMIN_URL . '/assets/images/wizard/start.png' ); ?>" width="1012" height="875">
				</div>
			</div>
			<?php
		}
	}

	/*-----------------------------------------------------------------------------------*/
	/*  Second Step (Tutorials)
	/*-----------------------------------------------------------------------------------*/
	public function setup_tutorial() {
		?>
		<div class="aux-tutorial-step aux-fadein-animation">

			<div class="aux-section">
				<h2><?php esc_html_e( '1. Child Theme (Optional)' ); ?></h2>
				<p>
					If you are going to make changes to the theme source code please use a Child Theme rather than modifying the main theme HTML/CSS/PHP code. This allows the parent theme to get updates without overwriting your source code changes.
				</p>
                <?php
                /* Use the form below to create and activate the Child Theme.
				<a href="#"
				   class="aux-button aux-left aux-primary"><?php esc_html_e( 'Download Child Theme' ); ?></a>
				<a href="#"
				   class="aux-button aux-left aux-outline"><?php esc_html_e( 'Quick Tour' ); ?></a>
                 */
			     ?>
            </div>

			<div class="clear"></div>

			<div class="aux-section">
				<h2><?php esc_html_e( '2. Theme Customizer' ); ?></h2>
				<p>
                    <?php printf( __('%1$s theme is extremely customizable by plenty of theme options. You can simply customize your theme by changing the options in WordPress Customizer and preview your changes instantly without affecting your live website. Just navigate to %2$s Appearance > Customize %3$s in order to change logo, colors, sizes and so on. You can almost customize everything in %1$s theme!', 'auxin-elements' ), __('Phlox', 'auxin-elements'), '<a href="'. admin_url('customize.php') .'" target="_blank">', '</a>' ); ?>
				</p>
		        <div class="aux-grid">
			        <div class="grid_6 first">
			            <div class="media-container">
			                <img src="<?php echo esc_url( AUXELS_ADMIN_URL . '/assets/images/wizard/customizer.png' ); ?>" width="667" height="379">
			            </div>
			        </div>

		        </div>
	        </div>

 			<div class="clear"></div>

			<div class="aux-section">
				<h2><?php esc_html_e( '2. Front Page Setup' ); ?></h2>
				<p>
					<?php printf( __('Front page is the main page of your website that includes many content blocks such as call to action, contact form, recent posts, services, recent projects and many other content blocks.', 'auxin-elements' ) );
                    echo "<br />";
                    _e( 'Here is how to setup your front page:', 'auxin-elements' );
                    ?>
				</p>
                <br /><br />

                <h3><?php esc_html_e( 'a. Create Front Page' ); ?></h3>
		        <div class="aux-grid">
			        <div class="grid_6 first">
			            <div class="media-container">
			                <img src="<?php echo esc_url( AUXELS_ADMIN_URL . '/assets/images/wizard/frontpage-1.png' ); ?>" width="667" height="379">
			            </div>
			        </div>
			        <div class="grid_6 aux-middle">
			        	<ol>
			        		<li>Create New Page</li>
			        		<li>Name it “Home“ or “Front Page“</li>
			        		<li>Choose "Front Page“ template</li>
			        		<li>Press Publish button </li>
			        	</ol>
			        </div>
		        </div>

                <div class="clear"></div>

                <h3><?php esc_html_e( 'b. Set Front Page' ); ?></h3>
                <div class="aux-grid">
                    <div class="grid_6 first">
                        <div class="media-container">
                            <img src="<?php echo esc_url( AUXELS_ADMIN_URL . '/assets/images/wizard/frontpage-2.png' ); ?>" width="667" height="379">
                        </div>
                    </div>
                    <div class="grid_6 aux-middle">
                        <ol>
                            <li>Navigate to Setting > Reading</li>
                            <li>Set "Front page display" to "A static page"</li>
                            <li>Set "Front Page“ to "Home" or "Front page" you have created in first step.</li>
                            <li>Click save button to save changes.</li>
                        </ol>
                    </div>
                </div>

                <div class="clear"></div>

                <h3><?php esc_html_e( 'c. Set Posts/Blog Page' ); ?></h3>
                <div class="aux-grid">
                    <div class="grid_6 first">
                        <div class="media-container">
                            <img src="<?php echo esc_url( AUXELS_ADMIN_URL . '/assets/images/wizard/frontpage-3.png' ); ?>" width="667" height="379">
                        </div>
                    </div>
                    <div class="grid_6 aux-middle">
                        <ol>
                            <li>Create a page and name it "Blog"</li>
                            <li>In Setting > Reading Set "Posts Page" to "Blog"</li>
                            <li>Click save button to save changes.</li>
                        </ol>
                    </div>
                </div>

                <div class="clear"></div>

	        </div>

	        <div class="clear"></div>

 			<div class="aux-sticky">

				<div class="aux-setup-actions step">
					<a href="<?php echo esc_url( $this->get_prev_step_link() ); ?>"
				   		class="aux-button aux-left aux-has-icon aux-outline button-next"><i class="axicon-angle-left"></i><span><?php esc_html_e( 'Previous Step', 'auxin-elements' ); ?></span></a>

					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
						class="aux-button aux-primary aux-has-icon button-next"><i class="axicon-angle-right"></i><span><?php esc_html_e( 'Next Step', 'auxin-elements' ); ?></span></a>
				</div>

			</div>

		</div>

		<?php
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
			'auxin-the-news'     => 'Adds the functionality to publish the news easily and beautifully with Phlox theme.',
			'auxin-pro-tools'    => 'Premium features for Phlox theme.',
			'auxin-shop'         => 'Make a shop in easiest way using phlox theme.',
			'envato-market'      => 'WP Theme Updater based on the Envato WordPress Toolkit Library and Pixelentity class from ThemeForest forums.'
		);

		/* If we arrive here, we have the filesystem */

		?>
		<div class="aux-plugins-step aux-has-required-plugins aux-fadein-animation">
			<h2><?php esc_html_e( 'Recommended Plugins', 'auxin-elements' ); ?></h2>

			<?php
			if ( count( $plugins['all'] ) ) {
				?>
				<p><?php esc_html_e( 'You can install exclusive and recommended plugins for Phlox theme here, and add or remove them later on WordPress plugins page.', 'auxin-elements' ); ?></p>

				<div class="aux-table">
					<table class="auxin-list-table widefat">
					    <thead>
					        <tr>
					            <td id="cb" class="manage-column column-cb check-column" width="10%">
					                <label class="screen-reader-text"
					                for="cb-select-all"><?php esc_html_e( 'Select All', 'auxin-elements' ); ?></label>
					                <input
					                id="cb-select-all" type="checkbox">
					            </td>
					            <th class="manage-column column-thumbnail"></th>
					            <th scope="col" id="name"
					            class="manage-column column-name" width="15%"><?php esc_html_e( 'Name', 'auxin-elements' ); ?></th>
					            <th scope="col" id="description"
					            class="manage-column column-description" width="50%"><?php esc_html_e( 'Description', 'auxin-elements' ); ?></th>
					            <th scope="col" id="status"
					            class="manage-column column-status" width="17%"><?php esc_html_e( 'Status', 'auxin-elements' ); ?></th>
					            <th scope="col" id="version"
					            class="manage-column column-version" width="8%"><?php esc_html_e( 'Version', 'auxin-elements' ); ?></th>
					        </tr>
					    </thead>
					    <tbody class="aux-wizard-plugins">
							<?php
							foreach ( $plugins['all'] as $slug => $plugin ) {
								if( $this->tgmpa_instance->is_plugin_installed( $slug ) ) {
									$plugin_data 	= get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin['file_path'] );
								} else {
									$plugin_data 	= $this->get_plugin_data_by_slug( $slug );
								}
							?>
								<tr class="aux-plugin" data-slug="<?php echo esc_attr( $slug ); ?>">
						            <th scope="row" class="check-column">
						                <input class="aux-check-column" name="plugin[]" value="<?php echo esc_attr($slug); ?>" type="checkbox">
						                <div class="spinner"></div>
						            </th>
						            <td class="thumbnail column-thumbnail"
						            data-colname="Thumbnail">
						            	<?php
						            		$thumbnail_size = '128x128';
								            if( 'instagram-feed' == $plugin['slug'] || 'yellow-pencil-visual-theme-customizer' == $plugin['slug'] ){
								                $thumbnail_size = '128x128';
								            }
								            if( in_array( $plugin['slug'], array("js_composer", "Ultimate_VC_Addons", "masterslider", "go_pricing", "auxin-pro-tools", "auxin-shop", "auxin-the-news") ) ) {
								                $thumbnail = AUXIN_URL . 'images/welcome/' . $plugin['slug'] . '-plugin.png';
								            }
								            elseif( in_array( $plugin['slug'], array( "autoptimize", "custom-facebook-feed", "flickr-justified-gallery", "wp-smushit", "wordpress-importer", "customizer-export-import", "envato-market", "auxin-fonts" ) ) ) {
								                $thumbnail = AUXIN_URL . 'images/welcome/def-plugin.png';
								            } else{
								                $thumbnail = 'https://ps.w.org/'. $plugin['slug'] .'/assets/icon-'. $thumbnail_size .'.png';
								            }

								            ?>
	        							<img src="<?php echo esc_url( $thumbnail ); ?>" width="64" height="64">
						            </td>
						            <td class="name column-name"
						            data-colname="Plugin">
						            	<?php echo esc_html( $plugin['name'] ); ?>
						            </td>
						            <td class="description column-description"
						            data-colname="Description">
						            <?php
						            	if( isset( $plugin_data['Description'] ) ) {
						            		echo '<p>' . $plugin_data['Description'] . '</p>';
						            	} else if ( isset( $embeds_plugins_desc[ $plugin['slug'] ] ) ){
						            		echo '<p>' . $embeds_plugins_desc[ $plugin['slug'] ] . '</p>';
						            	} else {
						            		echo 'A simple WordPress Plugin.';
						            	}
										if ( strpos( $slug, 'auxin' ) !== false ) {
										    echo '<div class="aux-label">' . esc_html__('Exclusive', 'auxin-elements') . '</div>';
										}
						            ?>
						            </td>
						            <td class="status column-status"
						            data-colname="Status">
										<span>
		    								<?php
											    if ( isset( $plugins['install'][ $slug ] ) ) {
												    echo esc_html__( 'Not Installed', 'auxin-elements' );
											    } elseif ( isset( $plugins['activate'][ $slug ] ) ) {
												    echo esc_html__( 'Not Activated', 'auxin-elements' );
											    }
										    ?>
		    							</span>
						            </td>
					                <td class="version column-version"
					                data-colname="Version">
					                	<?php if( isset( $plugin_data['Version'] ) ) { ?>
					                    <span><?php echo esc_html( $plugin_data['Version'] ); ?></span>
					                    <?php } ?>
					                </td>
								</tr>
							<?php } ?>
					    </tbody>
					</table>
				</div>

				<div class="clear"></div>

				<div class="aux-sticky">

					<div class="aux-setup-actions step">
						<a href="<?php echo esc_url( $this->get_prev_step_link() ); ?>"
					   		class="aux-button aux-left aux-has-icon aux-outline button-next"><i class="axicon-angle-left"></i><span><?php esc_html_e( 'Previous Step', 'auxin-elements' ); ?></span></a>
						<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
						   class="aux-button aux-primary install-plugins disabled button-next"
						   data-callback="install_plugins"><?php esc_html_e( 'Install Plugins', 'auxin-elements' ); ?></a>
						<?php wp_nonce_field( 'aux-setup' ); ?>
						<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
						   class="aux-button aux-outline aux-has-icon skip-next button-next"><i class="axicon-angle-right"></i><span><?php esc_html_e( 'Skip This Step', 'auxin-elements' ); ?></span></a>
					</div>

				</div>

			<?php
			} else { ?>

	 			<?php $this->display_alerts( esc_html__( 'Good news! All plugins are already installed and up to date. Please continue.', 'auxin-elements'  ) , 'success' ); ?>

				<div class="clear"></div>

				<div class="aux-setup-actions step">
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
						class="aux-button aux-primary button-next"><?php esc_html_e( 'Next Step', 'auxin-elements' ); ?></a>
					<a href="<?php echo esc_url( $this->get_prev_step_link() ); ?>"
				   		class="aux-button aux-outline button-next"><?php esc_html_e( 'Previous Step', 'auxin-elements' ); ?></a>
				</div>
			<?php
			} ?>
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
			} elseif( $this->tgmpa_instance->is_plugin_active( $slug ) ) {
				// No need to display plugins if they are installed, up-to-date and active.
				continue;
			} else {
				$plugins['all'][ $slug ] = $plugin;

				if ( ! $this->tgmpa_instance->is_plugin_installed( $slug ) ) {
					$plugins['install'][ $slug ] = $plugin;
				} else {

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
					'message'       => esc_html__( 'Activating...', 'auxin-elements' ),
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
					'message'       => esc_html__( 'Installing...', 'auxin-elements' ),
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
	/*  Fourth step (Online Demo Importer)
	/*-----------------------------------------------------------------------------------*/
	public function setup_importer() {
		// Get the available demos list from Averta API
		$data = $this->parse_json( 'http://api.phlox.pro/demos/all' );
	?>
		<div class="aux-demo-importer-step aux-fadein-animation">
			<div class="aux-section">
				<h2><?php esc_html_e( 'Demo Importer', 'auxin-elements' ); ?></h2>
				<p>
					<?php esc_html_e( 'You can select & import demo content for Phlox theme from the following list. Please click on a demo to start.', 'auxin-elements' ); ?>
				</p>
			</div>

			<div class="aux-demo-list">
			<?php
				foreach ( $data as $key => $args ) {
					echo '<div data-demo-id="demo-'.$args['site_id'].'" class="aux-demo-item grid_4">';
					echo '<div class="aux-demo-select">';
					echo '<img class="demo_thumbnail" src='.$args['thumbnail'].'>';
					if( $args['type'] !== 'free' ) {
						echo '<img class="premium_badge" alt="This is a premium demo" src="'. esc_url( AUXELS_ADMIN_URL . '/assets/images/wizard/badge.png' ) .'">';
					}
					echo '<h2>' . $args['title'] . '</h2>';
					echo '</div>';

					$plugins = $this->get_plugins( $args['plugins'] );
					$has_plugin_required = ! empty($args['plugins'] ) && ! empty( $plugins['all'] );

					if( ( defined('THEME_PRO' ) && THEME_PRO ) || $args['type'] === 'free' ) {
				?>
					<div id="demo-<?php echo esc_attr( $args['site_id'] ); ?>" class="demo-lightbox">
						<div class="aux-modal-item clearfix aux-has-required-plugins">
							<div class="grid_5 no-gutter">
								<img src="<?php echo esc_url( $args['screen'] ); ?>">
							</div>
							<div class="grid_7 no-gutter">
								<div class="aux-setup-demo-content">
									<?php if ( $has_plugin_required ) : ?>
										<div class="first-step">
											<h2><?php esc_html_e( 'The Following Plugins Are Required To Import.' ); ?></h2>
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
														    $keys 	= esc_html__( 'Not Installed', 'auxin-elements' );
														    $class  = 'install';
													    }
													    if ( isset( $plugins['activate'][ $slug ] ) ) {
														    $keys 	= esc_html__( 'Not Activated', 'auxin-elements' );
														    $class  = 'activate';
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
									<?php endif; ?>
									<div class="second-step <?php if ( $has_plugin_required ) echo 'hide';  ?>">
										<h2><?php esc_html_e( 'Import Demo Content of Phlox Theme.' ); ?></h2>

										<form id="aux-import-data-<?php echo esc_attr( $args['site_id'] ); ?>">
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
									    		<div class="one_half no-gutter">
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
								</div>
								<div class="aux-setup-demo-actions">
									<div class="aux-return-back">
							 			<?php if ( $has_plugin_required ) : ?>
								 			<div class="aux-alert">
								 				<p><?php esc_html_e( 'You need to activate all above plugins.', 'auxin-elements' ); ?></p>
								 			</div>
											<a 	href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
												class="aux-button aux-medium install-plugins aux-primary button-next"
												data-callback="install_plugins"
												data-nonce="<?php echo wp_create_nonce( 'aux-import-demo-' . $args['site_id'] ); ?>"
												data-import-id="<?php echo esc_attr( $args['site_id'] ); ?>"
											><?php esc_html_e( 'Install Plugins', 'auxin-elements' ); ?></a>
										<?php else: ?>
											<div class="aux-alert" style="display: none;"></div>
											<a 	href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
												class="aux-button aux-medium aux-primary button-next"
												data-callback="install_demos"
												data-nonce="<?php echo wp_create_nonce( 'aux-import-demo-' . $args['site_id'] ); ?>"
												data-import-id="<?php echo esc_attr( $args['site_id'] ); ?>"
											><?php esc_html_e( 'Install Demo', 'auxin-elements' ); ?></a>
										<?php endif; ?>
									</div>
									<div class="aux-progress hide">
										<div class="aux-big">
											<div class="aux-progress-bar aux-progress-info aux-progress-active" data-percent="100" style="transition: none; width: 100%;">
												<span class="aux-progress-label"><?php esc_html_e( 'Please wait, this may take several minutes ..', 'auxin-elements' ); ?></span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php
					} else {
				?>
					<div id="demo-<?php echo esc_attr( $args['site_id'] ); ?>" class="demo-lightbox">
						<div class="aux-modal-item aux-pro-version" style="background: url(<?php echo esc_url( $args['background'] ); ?>); background-size: cover;">
							<div class="grid_6 image_col no-gutter">
								<img class="preview_image" alt="screen" src="<?php echo esc_url( $args['preview'] ); ?>">
								<img class="premium_badge" alt="premium" src="<?php echo esc_url( AUXELS_ADMIN_URL . '/assets/images/wizard/badge.png' ); ?>">
							</div>
							<div class="grid_6 text_col no-gutter">
								<div class="aux-setup-demo-content">
									<div class="phlox_logo">
										<img alt="phlox theme" src="<?php echo esc_url( AUXELS_ADMIN_URL . '/assets/images/wizard/logo.png' ); ?>">
									</div>
									<h2 class="demo_title"><?php echo $args['title']; ?></h2>
								<?php if( isset( $args['excerpt'] ) && ! empty( $args['excerpt'] ) ) : ?>
									<p class="demo_desc"><i class="axicon-info-circled"></i> <?php echo $args['excerpt']; ?></p>
								<?php endif; ?>
									<a target="_blank" href="<?php echo isset( $args['purchase'] ) && ! empty( $args['purchase'] ) ? esc_url( $args['purchase'] ) : '#'; ?>"
									   class="aux-button aux-block aux-primary"><?php esc_html_e( 'Purchase', 'auxin-elements' ); ?></a>
									<a target="_blank" href="<?php echo isset( $args['url'] ) && ! empty( $args['url'] ) ? esc_url( $args['url'] ) : '#'; ?>"
									   class="aux-button aux-block aux-primary aux-white"><?php esc_html_e( 'See In Action', 'auxin-elements' ); ?></a>
								</div>
							</div>
						</div>
					</div>
				<?php
					}
					echo '</div>';
				}
			?>
			</div>

			<div class="clear"></div>

			<div class="aux-sticky">

				<div class="aux-setup-actions step">
					<a href="<?php echo esc_url( $this->get_prev_step_link() ); ?>"
				   		class="aux-button aux-left aux-has-icon aux-outline button-next"><i class="axicon-angle-left"></i><span><?php esc_html_e( 'Previous Step', 'auxin-elements' ); ?></span></a>
					<a href="#"
					   class="aux-button aux-install-demo aux-primary disabled"><?php esc_html_e( 'Install Demo', 'auxin-elements' ); ?></a>
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="aux-button aux-outline aux-has-icon skip-next button-next"><i class="axicon-angle-right"></i><span><?php esc_html_e( 'Skip This Step', 'auxin-elements' ); ?></span></a>
				</div>

			</div>

		</div>

	<?php
	}

	/**
	 * Parse the demos list API
	 */
    public function parse_json( $url ) {

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
	/*  Fifth step (Final message)
	/*-----------------------------------------------------------------------------------*/
	public function setup_ready() {

        set_transient( 'aux_setup_complete', time(), 4 * YEAR_IN_SECONDS );
        set_transient( 'auxin_hide_core_plugin_notice', time(), 4 * YEAR_IN_SECONDS );
		?>

		<div class="aux-final-step aux-fadein-animation">
			<i class="auxicon-big auxicon-check-mark-circle-outline"></i>
			<h2><?php esc_html_e( 'Your Website is Ready!', 'auxin-elements' ); ?></h2>
			<p><?php esc_html_e( 'Congratulations! You website has been successfully configured.', 'auxin-elements' ); ?></p>
			<div class="aux-group">
			<a target="_blank" href="http://support.averta.net/en/e-item/phlox-wordpress-theme/?b=33826,34922"
			   class="aux-button aux-center aux-outline"><?php esc_html_e( 'Documentaion'	, 'auxin-elements' ); ?></a>
			<a target="_blank" href="<?php echo get_home_url(); ?>"
			   class="aux-button aux-center aux-primary"><?php esc_html_e( 'Visit Your Site', 'auxin-elements' ); ?></a>
			</div>

			<div class="aux-social">
				<p><?php esc_html_e( 'If you have any questions, read our documentation. Also, follow us at:', 'auxin-elements' ); ?></p>
				<ul>
					<li>
						<a target="_blank" class="twitter" href="http://www.twitter.com/averta_ltd">
							<i class="axicon-twitter"></i>
						</a>
					</li>
					<li>
						<a target="_blank" class="facebook" href="http://www.facebook.com/averta">
							<i class="axicon-facebook"></i>
						</a>
					</li>
					<li>
						<a target="_blank" class="youtube" href="http://www.youtube.com/averta.cast">
							<i class="axicon-youtube"></i>
						</a>
					</li>
					<li>
						<a target="_blank" class="link" href="http://averta.net/">
							<i class="axicon-link"></i>
						</a>
					</li>
				</ul>
			</div>
		</div>
		<?php
	}

}
