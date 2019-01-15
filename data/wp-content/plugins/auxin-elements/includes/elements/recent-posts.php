<?php
/**
 * Code highlighter element
 *
 * 
 * @package    auxin-elements
 * @license    LICENSE.txt
 * @author     
 * @link       https://bitbucket.org/averta/
 * @copyright  (c) 2010-2016 
 */

function auxin_get_recent_posts_master_array( $master_array ) {

    $categories = get_terms( 'category', 'orderby=count&hide_empty=0' );
    $categories_list = array( '' => __('All Categories', 'auxin-elements' ) )  ;
    foreach ( $categories as $key => $value ) {
        $categories_list[$value->term_id] = $value->name;
    }

    // $tags = get_terms( 'post_tag', 'orderby=count&hide_empty=0' );
    // $tags_list;
    // foreach ($tags as $key => $value) {
    //     $tags_list["$value->term_id"] = $value->name;
    // }


    $master_array['aux_recent_posts'] = array(
        'name'                          => __('[Phlox] Recent Posts', 'auxin-elements' ),
        'auxin_output_callback'         => 'auxin_widget_recent_posts_callback',
        'base'                          => 'aux_recent_posts',
        'description'                   => __('It adds recent posts in grid or carousel mode.', 'auxin-elements' ),
        'class'                         => 'aux-widget-recent-posts',
        'show_settings_on_create'       => true,
        'weight'                        => 1,
        'is_widget'                     => false,
        'is_shortcode'                  => true,
        'is_so'                         => true,
        'is_vc'                         => true,
        'category'                      => THEME_NAME,
        'group'                         => '',
        'admin_enqueue_js'              => '',
        'admin_enqueue_css'             => '',
        'front_enqueue_js'              => '',
        'front_enqueue_css'             => '',
        'icon'                          => 'auxin-element auxin-code',
        'custom_markup'                 => '',
        'js_view'                       => '',
        'html_template'                 => '',
        'deprecated'                    => '',
        'content_element'               => '',
        'as_parent'                     => '',
        'as_child'                      => '',
        'params' => array(
            array(
                'heading'          => __('Title','auxin-elements' ),
                'description'       => __('Recent post title, leave it empty if you don`t need title.', 'auxin-elements'),
                'param_name'       => 'title',
                'type'             => 'textfield',
                'std'              => '',
                'value'            => '',
                'holder'           => 'textfield',
                'class'            => 'title',
                'admin_label'      => true,
                'dependency'       => '',
                'weight'           => '',
                'group'            => '' ,
                'edit_field_class' => ''
            ),
            // array(
            //     'param_name'        => 'post_type',
            //     'type'              => 'dropdown',
            //     'def_value'         => 'post',
            //     'value'             => array(
            //         'post'  => __('Posts', 'auxin-elements' ),
            //         'page'   => __('Pages', 'auxin-elements' ),
            //     ),
            //     'holder'            => 'dropdown',
            //     'class'             => 'border',
            //     'heading'           => __('Create items from','auxin-elements' ),
            //     'description'       => '',
            //     'admin_label'       => true,
            //     'dependency'        => '',
            //     'weight'            => '',
            //     'group'             => '' ,
            //     'edit_field_class'  => ''
            // ),

            array(
                'param_name'        => 'cat',
                'type'              => 'dropdown',
                'def_value'         => '',
                'holder'            => 'dropdown',
                'class'             => 'cat',
                'heading'           => __('Categories', 'auxin-elements'),
                'description'       => __('Specifies a category that you want to show posts from it.', 'auxin-elements' ),
                'value'             => $categories_list,
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'           => __('Number of posts to show', 'auxin-elements'),
                'description'       => '',
                'param_name'        => 'num',
                'type'              => 'textfield',
                'def_value'         => '8',
                'holder'            => 'textfield',
                'class'             => 'num',
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'           => __('Exclude posts without media','auxin-elements' ),
                'description'       => '',
                'param_name'        => 'exclude_without_media',
                'type'              => 'aux_switch',
                'value'             => '0',
                'class'             => '',
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'           => __('Exclude custom post formats','auxin-elements' ),
                'description'       => '',
                'param_name'        => 'exclude_custom_post_formats',
                'type'              => 'aux_switch',
                'value'             => '0',
                'class'             => '',
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'           => __('Exclude qoute and link post formats','auxin-elements' ),
                'description'       => '',
                'param_name'        => 'exclude_qoute_link',
                'type'              => 'aux_switch',
                'value'             => '0',
                'class'             => '',
                'admin_label'       => true,
                'dependency'        => array(
                    'element'       => 'exclude_custom_post_formats',
                    'value'         => '0'
                ),
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'           => __('Exclude posts','auxin-elements' ),
                'description'       => __('Post IDs separated by comma (eg. 53,34,87,25)', 'auxin-elements' ),
                'param_name'        => 'exclude',
                'type'              => 'textfield',
                'value'             => '',
                'holder'            => 'textfield',
                'class'             => '',
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),
            array(
                'heading'           => __('Include posts','auxin-elements' ),
                'description'       => __('Post IDs separated by comma (eg. 53,34,87,25)', 'auxin-elements' ),
                'param_name'        => 'include',
                'type'              => 'textfield',
                'value'             => '',
                'holder'            => 'textfield',
                'class'             => '',
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),
            array(
                'heading'            => __('Order By', 'auxin-elements'),
                'description'        => '',
                'param_name'         => 'order_by',
                'type'               => 'dropdown',
                'def_value'          => 'date',
                'holder'             => 'dropdown',
                'class'              => 'order_by',
                'value'              => array (
                    'date'            => __('Date', 'auxin-elements'),
                    'menu_order date' => __('Menu Order', 'auxin-elements'),
                    'title'           => __('Title', 'auxin-elements'),
                    'ID'              => __('ID', 'auxin-elements'),
                    'rand'            => __('Random', 'auxin-elements'),
                    'comment_count'   => __('Comments', 'auxin-elements'),
                    'modified'        => __('Date Modified', 'auxin-elements'),
                    'author'          => __('Author', 'auxin-elements'),
                ),
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),
            array(
                'heading'           => __('Order', 'auxin-elements'),
                'description'       => '',
                'param_name'        => 'order',
                'type'              => 'dropdown',
                'def_value'         => 'DESC',
                'holder'            => 'dropdown',
                'class'             => 'order',
                'value'             =>array (
                    'DESC'          => __('Descending', 'auxin-elements'),
                    'ASC'           => __('Ascending', 'auxin-elements'),
                ),
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'       => __('Start offset','auxin-elements' ),
                'description'   => __('Number of post to displace or pass over.', 'auxin-elements' ),
                'param_name'    => 'offset',
                'type'          => 'textfield',
                'value'         => '',
                'holder'        => 'textfield',
                'class'         => '',
                'admin_label'   => true,
                'dependency'    => '',
                'weight'        => '',
                'group'         => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'           => __('Display post media (image, video, etc)', 'auxin-elements' ),
                'param_name'        => 'show_media',
                'type'              => 'aux_switch',
                'def_value'         => '',
                'value'             => '1',
                'holder'            => 'dropdown',
                'class'             => 'show_media',
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

             array(
                'heading'           => __('Image Aspect Ratio', 'auxin-elements'),
                'description'       => '',
                'param_name'        => 'image_aspect_ratio',
                'type'              => 'dropdown',
                'def_value'         => '0.75',
                'holder'            => 'dropdown',
                'class'             => 'order',
                'value'             =>array (
                    '0.75'          => __('Horizontal 4:3' , 'auxin-elements'),
                    '0.56'          => __('Horizontal 16:9', 'auxin-elements'),
                    '1.00'          => __('Square 1:1'     , 'auxin-elements'),
                    '1.33'          => __('Vertical 3:4'   , 'auxin-elements')
                ),
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'           => __('Insert post title','auxin-elements' ),
                'description'       => '',
                'param_name'        => 'show_title',
                'type'              => 'aux_switch',
                'value'             => '1',
                'class'             => '',
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'           => __('Insert post meta','auxin-elements' ),
                'description'       => '',
                'param_name'        => 'show_info',
                'type'              => 'aux_switch',
                'value'             => '1',
                'class'             => '',
                'admin_label'       => true,
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'           => __('Display Like Button','auxin-elements' ),
                'description'       => sprintf(__('Enable it to display %s like button%s on gride template blog. Please note WP Ulike plugin needs to be activaited to use this option.', 'auxin-elements'), '<strong>', '</strong>'),
                'param_name'        => 'display_like',
                'type'              => 'aux_switch',
                'value'             => '1',
                'holder'            => 'dropdown',
                'class'             => 'display_like',
                'admin_label'       => 1,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'           => __('Display Excerpt','auxin-elements' ),
                'description'       => __('Enable it to display post summary instead of full content.','auxin-elements' ),
                'param_name'        => 'show_excerpt',
                'type'              => 'aux_switch',
                'def_value'         => '',
                'value'             => '1',
                'holder'            => 'dropdown',
                'class'             => 'show_excerpt',
                'admin_label'       => 1,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'           => __('Content Layout', 'auxin-elements'),
                'description'       => __('Specifies the style of content for each post column.', 'auxin-elements' ),
                'param_name'        => 'content_layout',
                'type'              => 'dropdown',
                'def_value'         => 'default',
                'holder'            => 'dropdown',
                'class'             => 'content_layout',
                'value'             =>array (
                    'default'       => __('Full Content', 'auxin-elements'),
                    'entry-boxed'   => __('Boxed Content', 'auxin-elements')
                ),
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'           => __('Excerpt Length','auxin-elements' ),
                'description'       => __('Specify summary content in character.','auxin-elements' ),
                'param_name'        => 'excerpt_len',
                'type'              => 'textfield',
                'value'             => '160',
                'holder'            => 'textfield',
                'class'             => 'excerpt_len',
                'admin_label'       => 1,
                'dependency'        => array(
                    'element'  => 'show_excerpt',
                    'value'    => '1'
                ),
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'           => __('Insert read more button','auxin-elements' ),
                'description'       => '',
                'param_name'        => 'show_readmore',
                'type'              => 'aux_switch',
                'value'             => '1',
                'class'             => '',
                'admin_label'       => true,
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'           => __('Insert author name in bottom side','auxin-elements' ),
                'description'       => '',
                'param_name'        => 'show_author_footer',
                'type'              => 'aux_switch',
                'value'             => '1',
                'class'             => '',
                'dependency'        => array(
                    'element'  => 'show_readmore',
                    'value'    => '0'
                ),
                'admin_label'       => false,
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'     => __('Number of columns', 'auxin-elements'),
                'description' => '',
                'param_name'  => 'desktop_cnum',
                'type'        => 'dropdown',
                'def_value'   => '4',
                'holder'      => 'dropdown',
                'class'       => 'num',
                'value'       => array(
                    '1'  => '1' , '2' => '2'  , '3' => '3' ,
                    '4'  => '4' , '5' => '5'  , '6' => '6'
                ),
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => 'Layout' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'     => __('Number of columns in tablet size', 'auxin-elements'),
                'description' => '',
                'param_name'  => 'tablet_cnum',
                'type'        => 'dropdown',
                'def_value'   => 'inherit',
                'holder'      => 'dropdown',
                'class'       => 'num',
                'value'       => array(
                    'inherit' => 'Inherited from larger',
                    '1'  => '1' , '2' => '2'  , '3' => '3' ,
                    '4'  => '4'
                ),
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => 'Layout' ,
                'edit_field_class'  => ''
            ),

            array(
                'heading'     => __('Number of columns in phone size', 'auxin-elements'),
                'description' => '',
                'param_name'  => 'phone_cnum',
                'type'        => 'dropdown',
                'def_value'   => 'inherit',
                'holder'      => 'dropdown',
                'class'       => 'num',
                'value'       => array(
                    'inherit' => 'Inherited from larger',
                    '1'  => '1' , '2' => '2'
                ),
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => 'Layout' ,
                'edit_field_class'  => ''
            ),

            array(
                'param_name'  => 'preview_mode',
                'type'        => 'dropdown',
                'def_value'   => 'grid',
                'holder'      => 'dropdown',
                'class'       => 'num',
                'heading'     => __('Display items in', 'auxin-elements'),
                'description' => '',
                'value'       => array(
                    'grid'     => 'Grid',
                    'carousel' => 'Carousel'
                ),
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            // Carousel Options
            array(
                'param_name'        => 'carousel_space',
                'type'              => 'textfield',
                'value'             => '30',
                'holder'            => 'textfield',
                'class'             => 'excerpt_len',
                'heading'           => __('Column space','auxin-elements' ),
                'description'       => __('Specifies space between carousel columns in pixels', 'auxin-elements' ),
                'admin_label'       => 1,
                'dependency'        => array(
                    'element'  => 'preview_mode',
                    'value'    => 'carousel'
                ),
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'param_name'  => 'carousel_navigation',
                'type'        => 'dropdown',
                'def_value'   => 'peritem',
                'holder'      => 'dropdown',
                'class'       => 'num',
                'heading'     => __('Navigation type', 'auxin-elements'),
                'description' => '',
                'value'       => array(
                   'peritem' => __('Move per column', 'auxin-elements'),
                   'perpage' => __('Move per page', 'auxin-elements'),
                   'scroll'  => __('Smooth scroll', 'auxin-elements'),
                ),
                'admin_label'       => true,
                'dependency'        => array(
                    'element'  => 'preview_mode',
                    'value'    => 'carousel'
                ),
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'param_name'  => 'carousel_navigation_control',
                'type'        => 'dropdown',
                'def_value'   => 'arrows',
                'holder'      => 'dropdown',
                'class'       => 'num',
                'heading'     => __('Navigation control', 'auxin-elements'),
                'description' => '',
                'value'       => array(
                   'arrows'  => __('Arrows', 'auxin-elements'),
                   'bullets' => __('Bullets', 'auxin-elements'),
                   ''        => __('None', 'auxin-elements'),
                ),
                'dependency'        => array(
                    'element'  => 'preview_mode',
                    'value'    => 'carousel'
                ),
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'param_name'        => 'carousel_loop',
                'type'              => 'aux_switch',
                'value'             => '1',
                'class'             => '',
                'heading'           => __('Loop navigation','auxin-elements' ),
                'description'       => '',
                'dependency'        => array(
                    'element'  => 'preview_mode',
                    'value'    => 'carousel'
                ),
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'param_name'        => 'carousel_autoplay',
                'type'              => 'aux_switch',
                'value'             => '0',
                'class'             => '',
                'heading'           => __('Autoplay carousel','auxin-elements' ),
                'description'       => '',
                'admin_label'       => true,
                'dependency'        => array(
                    'element'  => 'preview_mode',
                    'value'    => 'carousel'
                ),
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),

            array(
                'param_name'        => 'carousel_autoplay_delay',
                'type'              => 'textfield',
                'value'             => '2',
                'holder'            => 'textfield',
                'class'             => 'excerpt_len',
                'heading'           => __('Autoplay delay','auxin-elements' ),
                'description'       => __('Specifies the delay between auto-forwarding in seconds', 'auxin-elements' ),
                'admin_label'       => 1,
                'dependency'        => array(
                    array(
                        'element'  => 'preview_mode',
                        'value'    => 'carousel'
                    ),
                    array(
                        'element'  => 'carousel_autoplay',
                        'value'    => 1
                    )
                ),
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),


            array(
                'heading'           => __('Extra class name','auxin-elements' ),
                'description'       => __('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'auxin-elements' ),
                'param_name'        => 'extra_classes',
                'type'              => 'textfield',
                'value'             => '',
                'def_value'         => '',
                'holder'            => 'textfield',
                'class'             => 'extra_classes',
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '',
                'edit_field_class'  => ''
            )
        )
    );

    return $master_array;
}

add_filter( 'auxin_master_array_shortcodes', 'auxin_get_recent_posts_master_array', 10, 1 );




/**
 * Element without loop and column
 * The front-end output of this element is returned by the following function
 *
 * @param  array  $atts              The array containing the parsed values from shortcode, it should be same as defined params above.
 * @param  string $shortcode_content The shorcode content
 * @return string                    The output of element markup
 */
function auxin_widget_recent_posts_callback( $atts, $shortcode_content = null ){

    global $aux_content_width;

    // Defining default attributes
    $default_atts = array(
        'title'                       => '', // header title
        'cat'                         => '',
        'num'                         => '4', // max generated entry
        'exclude'                     => '',
        'include'                     => '',
        'posts_per_page'              => -1,
        'offset'                      => '',
        'paged'                       => '',
        'order_by'                    => 'menu_order date',
        'order'                       => 'desc',
        'exclude_without_media'       => 0,
        'exclude_custom_post_formats' => 0,
        'exclude_qoute_link'          => 0,
        'show_media'                  => true,
        'display_like'                => true,
        'show_excerpt'                => true,
        'content_layout'              => '', // entry-boxed
        'excerpt_len'                 => '160',
        'show_title'                  => true,
        'show_info'                   => true,
        'show_readmore'               => true,
        'show_author_footer'          => false,
        'image_aspect_ratio'          => 0.75,
        'desktop_cnum'                => 4,
        'tablet_cnum'                 => 'inherit',
        'phone_cnum'                  => 'inherit',
        'preview_mode'                => 'grid',
        'tag'                         => '',
        'extra_classes'               => '',
        'custom_el_id'                => '',
        'carousel_space'              => '30',
        'carousel_autoplay'           => false,
        'carousel_autoplay_delay'     => '2',
        'carousel_navigation'         => 'peritem',
        'carousel_navigation_control' => 'arrows',
        'carousel_loop'               => 1,
        'reset_query'                 => true,
        'use_wp_query'                => false, // true to use the global wp_query, false to use internal custom query
        'base_class'                  => 'aux-widget-recent-posts'
    );

    $result = auxin_get_widget_scafold( $atts, $default_atts, $shortcode_content );
    extract( $result['parsed_atts'] );

    ob_start();

    global $wp_query;

    if( ! $use_wp_query ){

        // create wp_query to get latest items -----------
        $args = array(
            'post_type'             => 'post',
            'orderby'               => $order_by,
            'order'                 => $order,
            'offset'                => $offset,
            'paged'                 => $paged,
            'cat'                   => $cat,
            'post__not_in'          => array_filter( explode( ',', $exclude ) ),
            'post__in'              => array_filter( explode( ',', $include ) ),
            'post_status'           => 'publish',
            'posts_per_page'        => $posts_per_page,
            'ignore_sticky_posts'   => 1
        );

        $wp_query = new WP_Query( $args );
    }

    // widget header ------------------------------
    echo $result['widget_header'];
    echo $result['widget_title'];


    $phone_break_point  = 767;
    $tablet_break_point = 992;

    $show_comments      = true; // shows comments icon
    $post_counter       = 0;
    $column_class       = '';
    $item_class         = 'aux-col';
    $carousel_attrs     = '';

    if ( 'grid' == $preview_mode ) {
        // generate columns class
        $column_class  = 'aux-match-height aux-row aux-de-col' . $desktop_cnum;
        $column_class .= 'inherit' != $tablet_cnum  ? ' aux-tb-col'.$tablet_cnum : '';
        $column_class .= 'inherit' != $phone_cnum   ? ' aux-mb-col'.$phone_cnum : '';
        $column_class .= 'entry-boxed' == $content_layout  ? ' aux-entry-boxed' : '';

    } elseif ( 'carousel' == $preview_mode ) {
        $column_class    = 'master-carousel aux-no-js aux-mc-before-init';
        $item_class      = 'aux-mc-item';

        // genereate the master carousel attributes
        $carousel_attrs  =  'data-columns="' . $desktop_cnum . '"';
        $carousel_attrs .= ' data-autoplay="' . $carousel_autoplay . '"';
        $carousel_attrs .= ' data-delay="' . $carousel_autoplay_delay . '"';
        $carousel_attrs .= ' data-navigation="' . $carousel_navigation . '"';
        $carousel_attrs .= ' data-space="' . $carousel_space. '"';
        $carousel_attrs .= ' data-loop="' . $carousel_loop . '"';
        $carousel_attrs .= ' data-wrap-controls="true"';
        $carousel_attrs .= ' data-bullets="' . ('bullets' == $carousel_navigation_control ? 'true' : 'false') . '"';
        $carousel_attrs .= ' data-bullet-class="aux-bullets aux-small aux-mask"';
        $carousel_attrs .= ' data-arrows="' . ('arrows' == $carousel_navigation_control ? 'true' : 'false') . '"';
        $carousel_attrs .= ' data-match-height="true"';

        if ( 'inherit' != $tablet_cnum || 'inherit' != $phone_cnum ) {
            $carousel_attrs .= ' data-responsive="'. ( 'inherit' != $tablet_cnum  ? $tablet_break_point . ':' . $tablet_cnum . ',' : '' ).
                                                     ( 'inherit' != $phone_cnum   ? $phone_break_point  . ':' . $phone_cnum : '' ) . '"';
        }

    }

    $column_media_width = auxin_get_content_column_width( $desktop_cnum, 15 );

    $have_posts = $wp_query->have_posts();

    if( $have_posts ){
        ?><div class="<?php echo $column_class ?>" <?php echo $carousel_attrs ?>> <?php
        while ( $wp_query->have_posts() ) {

            // break the loop if it is reached to the limit
            if ( $post_counter < $num ) {
                $post_counter ++;
            } else {
                break;
            }

            $wp_query->the_post();
            $post = $wp_query->post;

            $post_vars = auxin_get_post_format_media(
                $post,
                array(
                    'request_from'       => 'archive',
                    'media_width'        => $phone_break_point,
                    'media_size'         => 'large',//array( 'width' =>     $column_media_width, 'height' =>     $column_media_width * $image_aspect_ratio ),
                    'upscale_image'      => true,
                    'image_from_content' => ! $exclude_without_media, // whether to try to get image from content or not
                    'no_gallery'         => 'carousel' == $preview_mode,
                    'add_image_hw'       => false, // whether add width and height attr or not
                    'image_sizes'        => array(
                        array( 'min' => '',      'max' => '767px', 'width' => '80vw' ),
                        array( 'min' => '768px', 'max' => '992px', 'width' => '40vw' ),
                        array( 'min' => ''     , 'max' => '',      'width' => $column_media_width.'px' )
                    ),
                    'srcset_sizes'  => array(
                        array( 'width' =>     $column_media_width, 'height' =>     $column_media_width * $image_aspect_ratio ),
                        array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width * $image_aspect_ratio ),
                        array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width * $image_aspect_ratio )
                    )
                )
            );

            extract( $post_vars );

            $the_format = get_post_format( $post );

            if(
                ( $exclude_custom_post_formats && !empty( $the_format ) ) ||
                ( $exclude_qoute_link          && ( 'link' == $the_format || 'quote' == $the_format ) ) ||
                ( $exclude_without_media       && ! $has_attach )
             ){
                $post_counter --;
                continue;
            }

            ?>
            <div class="<?php echo $item_class ?>">
                <?php include( locate_template( 'templates/theme-parts/entry/post-column.php' ) ); ?>
            </div>
            <?php
        }

        if ( 'carousel' == $preview_mode && 'arrows' == $carousel_navigation_control ) {
            ?>
                        <div class="aux-carousel-controls">
                            <div class="aux-next-arrow aux-arrow-nav aux-outline aux-hover-fill">
                                <span class="aux-svg-arrow aux-small-right"></span>
                                <span class="aux-hover-arrow aux-white aux-svg-arrow aux-small-right"></span>
                            </div>
                            <div class="aux-prev-arrow aux-arrow-nav aux-outline aux-hover-fill">
                                <span class="aux-svg-arrow aux-small-left"></span>
                                <span class="aux-hover-arrow aux-white aux-svg-arrow aux-small-left"></span>
                            </div>
                        </div>
            <?php
        }


        ?> </div> <?php
    }



    if( $reset_query ){
        wp_reset_query();
    }

    // return false if no result found
    if( ! $have_posts ){
        ob_get_clean();
        return false;
    }

    // widget footer ------------------------------
    echo $result['widget_footer'];

    return ob_get_clean();
}
