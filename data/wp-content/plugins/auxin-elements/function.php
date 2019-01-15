<?php 
add_action( 'vc_load_default_templates_action','phlox_vc_page_templates' ); // Hook in
function phlox_vc_page_templates() {
    $data               = array(); // Create new array
    $data['name']       = __( 'Phlox Blog template', 'Phlox' ); // Assign name for your custom template
    $data['weight']     = 0; // Weight of your template in the template list
    // $data['image_path'] = preg_replace( '/\s/', '%20', plugins_url( 'images/custom_template_thumbnail.jpg', __FILE__ ) ); // Always use preg replace to be sure that "space" will not break logic. Thumbnail should have this dimensions: 114x154px
    $data['image_path'] = preg_replace( '/\s/', '%20', plugins_url( 'images/temp1.jpeg', __FILE__ ) ); // Always use preg replace to be sure that "space" will not break logic. Thumbnail should have this dimensions: 114x154px
    // $data['custom_class'] = 'custom_template_for_vc_custom_template'; // CSS class name
    $data['content']    = '[vc_row][vc_column][aux_quote]Proin eget tortor risus. Curabitur aliquet quam id dui posuere blandit. Cras ultricies ligula sed magna dictum porta. Nulla quis lorem ut libero malesuada feugiat. Praesent sapien massa, convallis a pellentesque nec, egestas non nisi. Praesent sapien ma[/aux_quote][/vc_column][/vc_row][vc_row][vc_column width="1/2"][aux_contact_box title="kamtar shenasim" email="jame@bade.ghese" telephone="021.54868745" address="25 Ave" extra_classes="customm"][/vc_column][vc_column width="1/2"][aux_search title="shipping them out"][aux_divider style=""][aux_button label="sabzeie khake ma" icon="fa fa-pied-piper"][/vc_column][/vc_row][vc_row][vc_column][aux_recent_posts_land_style title="oooW" num="4"][/vc_column][/vc_row]';
  
    vc_add_default_templates( $data );
    
    $template               = array();
    $template['name']       = __( 'Phlox contt template', 'Phlox' ); 
    $data['image_path'] = preg_replace( '/\s/', '%20', plugins_url( 'images/temp2.jpg', __FILE__ ) );

    // $template['custom_class'] = 'custom_template_for_vc_custom_template'; // CSS class name
    $template['content']    = '[vc_row][vc_column][aux_quote type=""]Proin eget tortor risus. Curabitur aliquet quam id dui posuere blandit. Cras ultricies ligula sed magna dictum porta. Nulla quis lorem ut libero malesuada feugiat. Praesent sapien massa, convallis a pellentesque nec, egestas non nisi. Praesent sapien ma[/aux_quote][/vc_column][/vc_row][vc_row][vc_column width="1/2"][aux_gallery layout="masonry" columns="1" tablet_cnum="inherit" phone_cnum="1" link="none" include="81,71,109,153,155,147,42,6"][/vc_column][vc_column width="1/2"][aux_search title="shipping them out"][aux_divider style=""][aux_button label="sabzeie khake ma" border="" style="" icon="fa fa-pied-piper" color_name=""][/vc_column][/vc_row][vc_row][vc_column][aux_recent_posts_land_style title="oooW" num="4"][/vc_column][/vc_row]';
    
    vc_add_default_templates( $template );
}

// @TODO: VC page template sample of reordering
// add_filter( 'vc_load_default_templates', 'my_custom_template_at_first_position' ); // Hook in
// function my_custom_template_at_first_position( $data ) {
//     $template               = array();
//     $template['name']       = __( 'Phlox contt template', 'Phlox' ); 
//     $data['image_path'] = preg_replace( '/\s/', '%20', plugins_url( 'images/temp2.jpg', __FILE__ ) );

//     // $template['custom_class'] = 'custom_template_for_vc_custom_template'; // CSS class name
//     $template['content']    = '[vc_row][vc_column][aux_quote type=""]Proin eget tortor risus. Curabitur aliquet quam id dui posuere blandit. Cras ultricies ligula sed magna dictum porta. Nulla quis lorem ut libero malesuada feugiat. Praesent sapien massa, convallis a pellentesque nec, egestas non nisi. Praesent sapien ma[/aux_quote][/vc_column][/vc_row][vc_row][vc_column width="1/2"][aux_gallery layout="masonry" columns="1" tablet_cnum="inherit" phone_cnum="1" link="none" include="81,71,109,153,155,147,42,6"][/vc_column][vc_column width="1/2"][aux_search title="shipping them out"][aux_divider style=""][aux_button label="sabzeie khake ma" border="" style="" icon="fa fa-pied-piper" color_name=""][/vc_column][/vc_row][vc_row][vc_column][aux_recent_posts_land_style title="oooW" num="4"][/vc_column][/vc_row]';
//     array_unshift( $data, $template );

   
    
//     return $data;
// }
