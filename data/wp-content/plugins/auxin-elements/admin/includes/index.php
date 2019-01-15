<?php // load admin related classes & functions

// load admin related functions
include_once( 'admin-the-functions.php' );

include_once( 'compatibility/visual-composer/vc.php' );
include_once( 'compatibility/uvca/uvca.php' );


do_action( 'auxels_admin_classes_loaded' );

// load admin related functions
include_once( 'admin-hooks.php' );

// init the class for extending the menu nav in back-end
Auxin_Master_Nav_Menu_Admin::get_instance();
new Auxels_Archive_Menu_Links();

// custom permalink setting fields for custom post types
function auxin_init_permalinks(  ){
    $aux_permalink = new Auxin_Permalink();
    $aux_permalink->setup();

    new Auxin_Install();
    //new Auxin_Admin_Dashboard();
}
add_action( 'auxin_ready', 'auxin_init_permalinks' );
