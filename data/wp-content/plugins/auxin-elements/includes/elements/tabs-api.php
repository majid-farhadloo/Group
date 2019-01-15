<?php
/**
 * Tabs element if site origin bundle plugin is activaited
 *
 * 
 * @package    auxin-elements
 * @license    LICENSE.txt
 * @author     
 * @link       https://bitbucket.org/averta/
 * @copyright  (c) 2010-2016 
 */
function auxin_get_tabs_master_array( $master_array ) {

    $master_array['aux_tabs'] = array( // shortcode info here
        'name'                    => __('[Phlox] Tabs', 'auxin-elements'),
        'auxin_output_callback'   => 'auxin_widget_tabs_callback',
        'base'                    => 'aux_tabs',
        'description'             => __('It adds tabs element.', 'auxin-elements'),
        'class'                   => 'aux-widget-tabs',
        'show_settings_on_create' => true,
        'so_api'                  => true,
        'weight'                  => 1,
        'category'                => THEME_NAME,
        'group'                   => '',
        'admin_enqueue_js'        => '',
        'admin_enqueue_css'       => '',
        'front_enqueue_js'        => '',
        'front_enqueue_css'       => '',
        'icon'                    => 'auxin-element auxin-tab',
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
                'description'       => __('Tabs title, leave it empty if you don`t need title.', 'auxin-elements'),
                'param_name'        => 'title',
                'type'              => 'textfield',
                'value'             => '',
                'holder'            => 'textfield',
                'class'             => 'title',
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),
             array(
                'heading'          => __('Tab label','auxin-elements'),
                'description'      => __('Enter your tab item label.', 'auxin-elements'),
                'repeater'         => 'tabs',
                'repeater-label'   => __('Tabs', 'auxin-elements'),
                'section-name'     => __('Tabs Section', 'auxin-elements'),
                'param_name'       => 'tab_label',
                'type'             => 'textfield',
                'value'            => '',
                'holder'           => 'textfield',
                'class'            => 'tab_label',
                'admin_label'      => true,
                'dependency'       => '',
                'weight'           => '',
                'group'            => '' ,
                'edit_field_class' => ''
            ),
             array(
                'heading'          => __('Content', 'auxin-elements'),
                'description'      => __('Enter your tab item content.', 'auxin-elements'),
                'repeater'         => 'tabs', 
                'section-name'     => __('Tabs section', 'auxin-elements'),
                'repeater-label'   => __('Tabs', 'auxin-elements'),
                'param_name'       => 'content',
                'type'             => 'textarea_html',
                'value'            => '',
                'def_value'        => '',
                'holder'           => 'div',
                'class'            => 'content',
                'admin_label'      => true,
                'dependency'       => '',
                'weight'           => '',
                'group'            => '',
                'edit_field_class' => ''
            ),
             array(
                'heading'           => __('Extra class name','auxin-elements'),
                'description'       => __('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'auxin-elements'),
                'param_name'        => 'extra_classes',
                'type'              => 'textfield',
                'value'             => '',
                'def_value'         => '',
                'holder'            => 'textfield',
                'class'             => 'extra_classes',
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
                )
        )
    );


    return $master_array;
}

add_filter( 'auxin_master_array_shortcodes', 'auxin_get_tabs_master_array', 10, 1 );

/**
 * Sample element markup for front-end
 * In other words, the front-end output of this element is returned by the following function
 *
 *
 * @param  array  $atts              The array containing the parsed values from shortcode,it should be same as defined params above.
 * @param  string $shortcode_content The shorcode content
 * @return string                    The output of element markup
 */
function auxin_widget_tabs_callback( $atts, $shortcode_content = null ){

    // Defining default attributes
    $default_atts = array(
        'title'         => '', // header title
        'tabs'          => '', // header title
        'extra_classes' => '', // custom css class names for this element
        'custom_el_id'  => '', // custom id attribute for this element
        'base_class'    => 'aux-widget-tabs'  // base class name for container
    );

    $result = auxin_get_widget_scafold( $atts, $default_atts, $shortcode_content );
    extract( $result['parsed_atts'] );

    ob_start();

    // widget header ------------------------------
    echo $result['widget_header'];
    echo $result['widget_title'];

    // widget custom output -----------------------
    $output = '<section class="widget-tabs widget-container bordered">';
    $output .= '<div class="widget-inner ' . $extra_classes . '"> ';
    $tabs_markup = '<ul class="tabs">';
    $tabs_content = '<ul class="tabs-content">';
    foreach ($tabs as $key => $value) {
            $section_title =  $value['tab_label'];
            $tabs_markup .=  '<li><a href="">' . $section_title . '</a></li>';
            $section_content =  $value['content'];
            $tabs_content .= '<li><div class="entry-editor"><p>' . $section_content . '</p></div></li>';
    }
    $tabs_markup .= '</ul>';
    $tabs_content .= '</ul>';
    $output = $output . $tabs_markup . $tabs_content . '</div>' . '</section>';

    echo $output;

    // widget footer ------------------------------
    echo $result['widget_footer'];

    return ob_get_clean();
}
