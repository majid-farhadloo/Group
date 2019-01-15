<?php
 /**
 * Adds fields for top header metabox
 *
 * 
 * @package    Auxin
 * @license    LICENSE.txt
 * @author     
 * @link       http://phlox.pro/
 * @copyright  (c) 2010-2018 
*/

 // no direct access allowed
if ( ! defined('ABSPATH') )  exit;


function auxin_metabox_fields_general_top_header(){

    $model         = new Auxin_Metabox_Model();
    $model->id     = 'general-top-header';
    $model->title  = __('Top Header Setting', 'auxin-elements');
    $model->fields = array(

        array(
            'title'       => __( 'Display Top Header bar', 'auxin-elements' ),
            'description' => __( 'Whether to display top header bar on this page or not.', 'auxin-elements' ),
            'id'          => 'aux_show_topheader',
            'default'     => 'default',
            'choices'     => array(
                'default' => __( 'Theme Default', 'auxin-elements' ),
                'yes'     => __( 'Yes', 'auxin-elements' ),
                'no'      => __( 'No', 'auxin-elements' ),
            ),
            'type'        => 'select'
        ),

        array(
            'title'       => __( 'Top Header Bar Layout', 'auxin-elements' ),
            'description' => __( 'Specifies top header bar layout for this page.', 'auxin-elements' ),
            'id'          => 'aux_topheader_layout',
            'type'        => 'radio-image',
            'dependency'  => array(
                array(
                     'id'      => 'aux_show_topheader',
                     'value'   => array('yes'),
                     'operator'=> ''
                )
            ),
            'choices' => array(
                'default' => array(
                    'label' => __( 'Theme Default', 'auxin-elements' ),
                    'image' => AUXIN_URL . 'images/visual-select/default3.svg'
                ),
                'topheader1' => array(
                    'label'     => __( 'Menu left. Social and search right', 'auxin-elements' ),
                    'image' => AUXIN_URL . 'images/visual-select/top-header-layout-1.svg'
                ),
                'topheader2' => array(
                    'label'     => __( 'Message left. Menu and language right', 'auxin-elements' ),
                    'image' => AUXIN_URL . 'images/visual-select/top-header-layout-2.svg'
                ),
                'topheader3' => array(
                    'label'     => __( 'Social left. Cart and search right', 'auxin-elements' ),
                    'image' => AUXIN_URL . 'images/visual-select/top-header-layout-3.svg'
                ),
                'topheader4' => array(
                    'label'     => __( 'Menu left. Message, social, cart, search and language right', 'auxin-elements' ),
                    'image' => AUXIN_URL . 'images/visual-select/top-header-layout-4.svg'
                ),
                'topheader5' => array(
                    'label'     => __( 'Language left. Social, cart and search right', 'auxin-elements' ),
                    'image' => AUXIN_URL . 'images/visual-select/top-header-layout-5.svg'
                ),
                'topheader6' => array(
                    'label'     => __( 'Message left. Social, cart and search right', 'auxin-elements' ),
                    'image' => AUXIN_URL . 'images/visual-select/top-header-layout-6.svg'
                ),
                'topheader7' => array(
                    'label'     => __( 'Menu left. Social, cart and search right', 'auxin-elements' ),
                    'image' => AUXIN_URL . 'images/visual-select/top-header-layout-7.svg'
                ),
                'topheader8' => array(
                    'label'     => __( 'Language and menu left. Message, social, cart and search right', 'auxin-elements' ),
                    'image' => AUXIN_URL . 'images/visual-select/top-header-layout-8.svg'
                )
            ),
            'default'   => 'default'
        ),

        array(
            'title'       => __( 'Top Header Bar Background Color', 'auxin-elements' ),
            'description' => __( 'Specifies the background color of top header bar. No color means the default theme color for this section.', 'auxin-elements' ),
            'id'          => 'aux_topheader_background_color',
            'dependency'  => array(
                array(
                     'id'      => 'aux_show_topheader',
                     'value'   => array('yes'),
                     'operator'=> ''
                )
            ),
            'transport'      => 'postMessage',
            'style_callback' => function( $value = null ){
                $selector  = ".aux-top-header { background-color:%s; }";
                return $value ? sprintf( $selector , $value ) : '';
            },
            'default'   => '',
            'type'      => 'color'
        ),

        array(
            'title'       => __( 'Display Message on Top Header Bar', 'auxin-elements' ),
            'description' => __( 'Whether to display default message on top header or a custom message for this page.', 'auxin-elements' ),
            'id'          => 'aux_show_topheader_message',
            'dependency'  => array(
                array(
                     'id'      => 'aux_show_topheader',
                     'value'   => array('yes'),
                     'operator'=> ''
                )
            ),
            'choices'     => array(
                'default' => __( 'Theme Default', 'auxin-elements' ),
                'yes'     => __( 'Yes, Custom', 'auxin-elements' ),
                'no'      => __( 'No, Hide it', 'auxin-elements' ),
            ),
            'default'   => 'default',
            'type'      => 'select'
        ),

        array(
            'title'       => __( 'Message on Top Header Bar', 'auxin-elements' ),
            'description' => __( 'Add a custom message to be displayed on top header bar of this page only.', 'auxin-elements' ),
            'id'          => 'aux_topheader_message',
            'dependency'  => array(
                array(
                     'id'      => 'aux_show_topheader',
                     'value'   => array('yes'),
                     'operator'=> ''
                ),
                array(
                     'id'      => 'aux_show_topheader_message',
                     'value'   => array('yes'),
                     'operator'=> ''
                )
            ),
            'default'   => '',
            'type'      => 'textarea'
        )

    );

    return $model;
}
