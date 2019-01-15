<?php
/**
 * Testomonial Widget
 *
 * 
 * @package    Auxin
 * @license    LICENSE.txt
 * @author     
 * @link       http://averta.net/phlox/
 * @copyright  (c) 2010-2017 
 */

function  auxin_get_testomonial_master_array( $master_array )  {

     $master_array['aux_testomonial'] = array(
        'name'                    => __('Testomonial ', 'auxin-elements'),
        'auxin_output_callback'   => 'auxin_widget_testomonial_callback',
        'base'                    => 'aux_testomonial',
        'description'             => __('Testomonial Element', 'auxin-elements'),
        'class'                   => 'aux-widget-testomonial',
        'show_settings_on_create' => true,
        'weight'                  => 1,
        'is_widget'               => true,
        'is_shortcode'            => true,
        'is_so'                   => false,
        'is_vc'                   => false,
        'category'                => THEME_NAME,
        'group'                   => '',
        'so_api'                  => false,
        'admin_enqueue_js'        => '',
        'admin_enqueue_css'       => '',
        'front_enqueue_js'        => '',
        'front_enqueue_css'       => '',
        'icon'                    => 'aux-element aux-pb-icons-testomonial',
        'custom_markup'           => '',
        'js_view'                 => '',
        'html_template'           => '',
        'deprecated'              => '',
        'content_element'         => '',
        'as_parent'               => '',
        'as_child'                => '',
        'params'                  => array(
            array(
                'heading'          => __( 'Testomonial Templates','auxin-elements' ),
                'description'      => '',
                'param_name'       => 'template',
                'type'             => 'aux_visual_select',
                'def_value'        => 'default',
                'holder'           => '',
                'class'            => 'template',
                'admin_label'      => false,
                'dependency'       => '',
                'weight'           => '',
                'group'            => '',
                'edit_field_class' => '',
                'choices'          => array(
                    'default'    => array(
                        'label'    => __( 'Default Template', 'auxin-elements' ),
                        'image'    => AUXIN_URL . 'images/visual-select/button-normal.svg'
                    ),
                    'def-img'  => array(
                        'label'    => __( 'Default Template With Image', 'auxin-elements' ),
                        'image'    => AUXIN_URL . 'images/visual-select/button-curved.svg'
                    ),
                    'bordered'  => array(
                        'label'    => __( 'Bordered On Content', 'auxin-elements' ),
                        'image'    => AUXIN_URL . 'images/visual-select/button-curved.svg'
                    ),
                    'qoute'  => array(
                        'label'    => __( 'Quotation Mark ln Top of the Content', 'auxin-elements' ),
                        'image'    => AUXIN_URL . 'images/visual-select/button-curved.svg'
                    ),
                    'info-top'  => array(
                        'label'    => __( 'Show Info on Top of Widget', 'auxin-elements' ),
                        'image'    => AUXIN_URL . 'images/visual-select/button-curved.svg'
                    ),
                )
            ),
            array(
                'heading'          => __('Customer Name','auxin-elements'),
                'description'      => __('Customer Name, leave it empty if you don`t need title.', 'auxin-elements'),
                'param_name'       => 'title',
                'type'             => 'textfield',
                'value'            => '',
                'holder'           => 'textfield',
                'class'            => 'title',
                'admin_label'      => true,
                'dependency'       => '',
                'weight'           => '',
                'group'            => '' ,
                'edit_field_class' => ''
            ),
            array(
                'heading'          => __('Customer Link','auxin-elements'),
                'description'      => __('Customer Link, leave it empty if you don`t need it', 'auxin-elements'),
                'param_name'       => 'link',
                'type'             => 'textfield',
                'value'            => '',
                'holder'           => 'textfield',
                'class'            => 'title',
                'admin_label'      => true,
                'dependency'       => '',
                'weight'           => '',
                'group'            => '' ,
                'edit_field_class' => ''
            ),
            array(
                'heading'          => __('Customer Occupation','auxin-elements'),
                'description'      => __('Customer Occupation, leave it empty if you don`t need it.', 'auxin-elements'),
                'param_name'       => 'subtitle',
                'type'             => 'textfield',
                'value'            => '',
                'holder'           => 'textfield',
                'class'            => 'subtitle',
                'admin_label'      => true,
                'dependency'       => '',
                'weight'           => '',
                'group'            => '' ,
                'edit_field_class' => ''
            ),
            array(
                'heading'           => __('Customer Image', 'auxin-elements'),
                'description'       => '',
                'param_name'        => 'customer_img',
                'type'              => 'attach_image',
                'def_value'         => '',
                'value'             => '',
                'holder'            => '',
                'class'             => 'customer-img',
                'admin_label'       => true,
                'dependency'        => array(
                            'element' => 'template',
                            'value' => array('bordered', 'def-img', 'info-top')
                ),
                'weight'            => '',
                'group'             => '',
                'edit_field_class'  => ''
            ),
           array(
                'heading'           => __('Content','auxin-elements'),
                'description'       => __('Enter a text as a text content.','auxin-elements'),
                'param_name'        => 'content',
                'type'              => 'textarea_html',
                'value'             => '',
                'def_value'         => '',
                'holder'            => 'div',
                'class'             => 'content',
                'admin_label'       => true,
                'dependency'        => '',
                'weight'            => '',
                'group'             => '' ,
                'edit_field_class'  => ''
            ),
        )
    );

    return $master_array;
}

add_filter( 'auxin_master_array_shortcodes', 'auxin_get_testomonial_master_array', 10, 1 );


/**
 * Testomonial Widget Markup
 *
 * The front-end output of this element is returned by the following function
 *
 * @param  array  $atts              The array containing the parsed values from shortcode, it should be same as defined params above.
 * @param  string $shortcode_content The shorcode content
 * @return string                    The output of element markup
 */
function auxin_widget_testomonial_callback( $atts, $shortcode_content = null ){

    // Defining default attributes
    $default_atts = array(

        'template'      => 'default',
        'title'         => '',
        'subtitle'      => '',
        'link'          => '',
        'customer_img'  => '',
        'content'       => '',
        'extra_classes' => '', // custom css class names for this element
        'custom_el_id'  => '', // custom id attribute for this element
        'base_class'    => 'aux-widget-testomonial'  // base class name for container

    );

    $result = auxin_get_widget_scafold( $atts, $default_atts );

    extract( $result['parsed_atts'] );

    $image      = wp_get_attachment_image( $customer_img, 'thumbnail', "", array( "class" => "img-square" ) );
    $content    = empty( $content ) ? $shortcode_content : $content ;
    $template   = ' aux-testomonial-' . $template ;
    $main_class = $base_class . $template;

    ob_start();

    // widget header ------------------------------
    echo $result['widget_header'];
?>
    <div class=" <?php echo esc_attr( $main_class );?> ">
        <?php if( ! empty( $content ) ) { ?>
        <div class="aux-testomonial-content">
            <div class="entry-content">
                <?php $encoding_flag =  defined('ENT_HTML401') ? ENT_HTML401 : ENT_QUOTES; ?>
                <?php echo do_shortcode( html_entity_decode( $content, $encoding_flag, 'UTF-8') ); ?>
            </div>
        </div>
        <?php } ?>
        <div class="aux-testomonial-infobox">
            <?php if ( !empty( $image ) ) { ?>
            <div class="aux-testomonial-image">
                    <?php echo $image ;?>
            </div>
            <?php } ?>
            <div class="aux-testomonial-info">
                <?php if( ! empty( $title ) && empty( $link ) ) { ?>
                <h4 class="col-title"><?php echo $title; ?></h4>
                <?php } elseif( ! empty( $title ) && ! empty( $link ) ) {?>
                <h4 class="col-title"><a href="<?php echo esc_url( $link ); ?>">
                <?php echo $title; ?></a>
                </h4>
                <?php } if( ! empty( $subtitle ) ) { ?>
                <h5 class="col-subtitle"><?php echo $subtitle; ?></h5>
                <?php } ?>
            </div>
        </div>
    </div>

<?php

    // widget footer ------------------------------
    echo $result['widget_footer'];
    return ob_get_clean();

}
