<?php
/**
 * Countdown element
 *
 * 
 * @package    Auxin
 * @license    LICENSE.txt
 * @author     
 * @link       http://averta.net/phlox/
 * @copyright  (c) 2010-2018 
 */
function auxin_get_countdown_master_array( $master_array ) {

    $master_array['aux_countdown'] = array(
        'name'                    => __('Countdown ', 'auxin-elements' ),
        'auxin_output_callback'   => 'auxin_widget_countdown_callback',
        'base'                    => 'aux_countdown',
        'description'             => __('Countdown Widget', 'auxin-elements' ),
        'class'                   => 'aux-widget-gmaps',
        'show_settings_on_create' => true,
        'weight'                  => 1,
        'is_widget'               => false,
        'is_shortcode'            => true,
        'is_so'                   => false,
        'is_vc'                   => true,
        'category'                => THEME_NAME,
        'group'                   => '',
        'admin_enqueue_js'        => '',
        'admin_enqueue_css'       => '',
        'front_enqueue_js'        => '',
        'front_enqueue_css'       => '',
        'icon'                    => 'aux-element auxicon-calendar',
        'custom_markup'           => '',
        'js_view'                 => '',
        'html_template'           => '',
        'deprecated'              => '',
        'content_element'         => '',
        'as_parent'               => '',
        'as_child'                => '',
        'params'                  => array(
            array(
                'heading'           => __('Title','auxin-elements' ),
                'description'       => __('Countdown title, leave it empty if you don`t need title.', 'auxin-elements'),
                'param_name'        => 'title',
                'type'              => 'textfield',
                'value'             => '',
                'holder'            => 'textfield',
                'class'             => 'title',
                'admin_label'       => false,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),
            array(
                'heading'           => __('View as','auxin-elements' ),
                'description'       => '',
                'param_name'        => 'countdown_style',
                'type'              => 'dropdown',
                'def_value'         => 'block',
                'value'             => array(
                   'block'  => __('Block'            , 'auxin-elements' ),
                   'inline' => __('Inline'               , 'auxin-elements' ),
                ),
                'holder'            => '',
                'class'             => '',
                'admin_label'       => false,
                'weight'            => '',
                'edit_field_class'  => ''
            ),

            array(
                'heading'           => __('Target Time For Countdown','auxin-elements' ),
                'description'       => __('Date and time format (yyyy/mm/dd hh:mm:ss).', 'auxin-elements'),
                'param_name'        => 'datetime',
                'type'              => 'datetimepicker',
                'value'             => '',
                'class'             => 'datetime',
                'admin_label'       => false,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'           => __('Seperator','auxin-elements'),
                'description'       => __('Countdown seperator, leave it empty if you don`t need seperator.', 'auxin-elements'),
                'param_name'        => 'seperator',
                'type'              => 'textfield',
                'value'             => '',
                'def_value'         => '',
                'holder'            => 'textfield',
                'class'             => 'extra_classes',
                'admin_label'       => false,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),
            array(
                'heading'           => __('Display Years','auxin-elements' ),
                'description'       => '',
                'param_name'        => 'show_year',
                'type'              => 'aux_switch',
                'value'             => '1',
                'class'             => '',
                'admin_label'       => false,
                'weight'            => '',
                'edit_field_class'  => ''
            ),
            array(
                'heading'           => __('Display Months','auxin-elements' ),
                'description'       => '',
                'param_name'        => 'show_month',
                'type'              => 'aux_switch',
                'value'             => '1',
                'class'             => '',
                'admin_label'       => false,
                'weight'            => '',
                'edit_field_class'  => ''
            ),
            array(
                'heading'           => __('Display Days','auxin-elements' ),
                'description'       => '',
                'param_name'        => 'show_day',
                'type'              => 'aux_switch',
                'value'             => '1',
                'class'             => '',
                'admin_label'       => false,
                'weight'            => '',
                'edit_field_class'  => ''
            ),
            array(
                'heading'           => __('Display Hours','auxin-elements' ),
                'description'       => '',
                'param_name'        => 'show_hour',
                'type'              => 'aux_switch',
                'value'             => '1',
                'class'             => '',
                'admin_label'       => false,
                'weight'            => '',
                'edit_field_class'  => ''
            ),
            array(
                'heading'           => __('Display Mintues','auxin-elements' ),
                'description'       => '',
                'param_name'        => 'show_min',
                'type'              => 'aux_switch',
                'value'             => '1',
                'class'             => '',
                'admin_label'       => false,
                'weight'            => '',
                'edit_field_class'  => ''
            ),
            array(
                'heading'           => __('Display Seconds','auxin-elements' ),
                'description'       => '',
                'param_name'        => 'show_sec',
                'type'              => 'aux_switch',
                'value'             => '1',
                'class'             => '',
                'admin_label'       => false,
                'weight'            => '',
                'edit_field_class'  => ''
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
                'admin_label'       => false,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            )
        )
    );


    return $master_array;
}

add_filter( 'auxin_master_array_shortcodes', 'auxin_get_countdown_master_array', 10, 1 );


/**
 * The front-end output of this element is returned by the following function
 *
 * @param  array  $atts              The array containing the parsed values from shortcode, it should be same as defined params above.
 * @param  string $shortcode_content The shorcode content
 * @return string                    The output of element markup
 */
function auxin_widget_countdown_callback( $atts, $shortcode_content = null ){


    // Defining default attributes
    $default_atts = array(
        'title'         => '', // header title
        'datetime'      => '',
        'show_year'     => '1',
        'show_month'    => '1',
        'show_day'      => '1',
        'show_hour'     => '1',
        'show_min'      => '1',
        'show_sec'      => '1',
        'seperator'     => '',
        'countdown_style'=> 'block',
        'extra_classes' => '', // custom css class names for this element
        'custom_el_id'  => '', // custom id attribute for this element
        'base_class'    => 'aux-widget-countdown'  // base class name for container
    );

    $result = auxin_get_widget_scafold( $atts, $default_atts, $shortcode_content );
    extract( $result['parsed_atts'] );
    
    // widget header ------------------------------
    $output  = $result['widget_header'];
    $output .= $result['widget_title'];

    $data_attr = '';
    $date_attr = array();
    $attr_markup = '';

    $datetime = explode( " ", $datetime );
    
    $date = $datetime[0];
    $time = $datetime[1];

    $date = explode( "/", $date );
    $time = explode( ":", $time );


    $date_attr = array(
        'year' => array(
            'value'   => $date[0],
            'display' => $show_year,
            'title'   => __('Years','auxin-elements'),
        ),
        'month' => array(
            'value'   => $date[1],
            'display' => $show_month,
            'title'   => __('Months','auxin-elements'),
        ),
        'day' => array(
            'value'   => $date[2],
            'display' => $show_day,
            'title'   => __('Days','auxin-elements'),
        ),
        'hour' => array(
            'value'   => $time[0],
            'display' => $show_hour,
            'title'   => __('Hours','auxin-elements'),
        ),
        'min' => array(
            'value'   => $time[1],
            'display' => $show_min,
            'title'   => __('Mintues','auxin-elements'),
        ),
        'sec' => array(
            'value'   => $time[2],
            'display' => $show_sec,
            'title'   => __('Seconds','auxin-elements'),
        ),            
    );


    foreach( $date_attr as $attr => $key ) {

        $data_attr .= 'data-countdown-' . $attr . '="' . $key['value'] . '"' ;
        
        if ( $key['display'] ) {
            $attr_markup .= '<div class="aux-countdown-item">';
            $attr_markup .= '<span class="aux-countdown-value aux-countdown-' . $attr . '">'. __('0','auxin-elements') . '</span>';
            $attr_markup .= 'inline' === $countdown_style && ! empty ($seperator )? '<span class="aux-countdown-seperator">' . esc_attr($seperator) . '</span>' : '';
            $attr_markup .= '<span class="aux-countdown-title">' . $key['title'] . '</span>';
            $attr_markup .= '</div>';
            $attr_markup .= 'block' === $countdown_style && ! empty ( $seperator ) &&  'sec' !== $attr ? '<span class="aux-countdown-seperator">' . esc_attr($seperator) . '</span>' : '';
        }
        
    }

    $extra_classes .= 'inline' === $countdown_style ? ' aux-countdown-inline' : 'aux-countdown-block';
    
    $output .= '<div class="aux-countdown-wrapper ' . $extra_classes .'"' . $data_attr . '>';
    $output .= $attr_markup;    
    $output .= '</div>';
    // widget footer ------------------------------
    $output .= $result['widget_footer'];

    return $output;
}
