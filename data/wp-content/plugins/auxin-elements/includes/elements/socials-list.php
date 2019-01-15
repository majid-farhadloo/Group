<?php
/**
 * Social List
 *
 * 
 * @package    Auxin
 * @license    LICENSE.txt
 * @author     
 * @link       http://phlox.pro/
 * @copyright  (c) 2010-2018 
 */
function auxin_get_social_master_array( $master_array ) {

    $master_array['aux_socials_list'] = array(
         'name'                    => __("Socials", 'auxin-elements' ),
         'auxin_output_callback'   => 'auxin_widget_socials_list_callback',
         'base'                    => 'aux_socials_list',
         'description'             => __('It shows the website socials icons which you can configure it by the customizer.', 'auxin-elements'),
         'class'                   => 'aux-widget-socials',
         'show_settings_on_create' => true,
         'weight'                  => 1,
         'is_widget'               => true,
         'is_shortcode'            => true,
         'is_so'                   => true,
         'is_vc'                   => true,
         'category'                => THEME_NAME,
         'group'                   => '',
         'admin_enqueue_js'        => '',
         'admin_enqueue_css'       => '',
         'front_enqueue_js'        => '',
         'front_enqueue_css'       => '',
         'icon'                    => 'aux-element aux-pb-icons-socials',
         'custom_markup'           => '',
         'js_view'                 => '',
         'html_template'           => '',
         'deprecated'              => '',
         'content_element'         => '',
         'as_parent'               => '',
         'as_child'                => '',
         'params'                  => array(
            array(
                'heading'           => __('Title','auxin-elements'),
                'description'       => __('Socials title, leave it empty if you don`t need title.', 'auxin-elements'),
                'param_name'        => 'title',
                'type'              => 'textfield',
                'value'             => '',
                'def_value'         => '',
                'holder'            => 'textfield',
                'class'             => 'id',
                'admin_label'       => false,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '',
                'edit_field_class'  => ''
            ),
            array(
                'heading'           => __('Size of social icons','auxin-elements'),
                'description'       => '',
                'param_name'        => 'size',
                'type'              => 'dropdown',
                'def_value'         => 'medium',
                'value'             => array(
                    'small'         => __('Small' , 'auxin-elements'),
                    'medium'        => __('Medium' , 'auxin-elements'),
                    'large'         => __('Large' , 'auxin-elements'),
                    'extra-large'   => __('Extra large' , 'auxin-elements')
                ),
                'holder'            => '',
                'class'             => 'size',
                'admin_label'       => false,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '',
                'edit_field_class'  => ''
            ),
            array(
                'heading'           => __('Directon of socials list','auxin-elements'),
                'description'       => '',
                'param_name'        => 'direction',
                'type'              => 'dropdown',
                'def_value'         => 'horizontal',
                'value'             => array(
                    'horizontal'    => __('Horizontal' , 'auxin-elements'),
                    'vertical'      => __('Vertical' , 'auxin-elements')
                ),
                'holder'            => '',
                'class'             => 'direction',
                'admin_label'       => false,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '',
                'edit_field_class'  => ''
            )

        )
    );

    return $master_array;
}

add_filter( 'auxin_master_array_shortcodes', 'auxin_get_social_master_array', 10, 1 );


// This is the widget call back in fact the front end out put of this widget comes from this function
function auxin_widget_socials_list_callback( $atts, $shortcode_content = null ){

    // Defining default attributes
    $default_atts = array(
        'title'         => '',
        'direction'     => 'horizontal',
        'size'          => 'medium',

        'extra_classes' => '',
        'custom_el_id'  => '',
        'base_class'    => 'aux-widget-socials'
    );


    $result = auxin_get_widget_scafold( $atts, $default_atts, $shortcode_content );
    extract( $result['parsed_atts'] );

    ob_start();

    // widget header ------------------------------
    echo $result['widget_header'];
    echo $result['widget_title'];

    // widget output -----------------------
    echo auxin_the_socials(array(
        'direction' => $direction,
        'size' => $size
        ));

    // widget footer ------------------------------
    echo $result['widget_footer'];

    return ob_get_clean();
}
