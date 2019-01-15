<?php
 /**
 * Adds fields for footer metabox
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


function auxin_metabox_fields_general_footer(){

    $model         = new Auxin_Metabox_Model();
    $model->id     = 'general-footer';
    $model->title  = __('Footer Setting', 'auxin-elements' );
    $model->fields = array(

        array(
            'title'         => __('Display Footer', 'auxin-elements'),
            'description'   => __( 'Enable it to display footer on this pages.', 'auxin-elements' ),
            'id'            => 'page_show_footer',
            'type'          => 'select',
            'default'       => 'default',
            'choices'       => array(
                'default' => __( 'Theme Default', 'auxin-elements' ),
                'yes'     => __( 'Yes', 'auxin-elements' ),
                'no'      => __( 'No', 'auxin-elements' ),
            ),
        ),

        array(
            'title'       => __( 'Footer Layout', 'auxin-elements' ),
            'description' => __( 'Specifies the footer layout.', 'auxin-elements' ),
            'id'          => 'page_footer_components_layout',
            'type'        => 'radio-image',
            'dependency' => array(
                array(
                     'id'      => 'page_show_footer',
                     'value'   => array('yes', 'default'),
                     'operator'=> '=='
                )
            ),
            'choices'     => array(
                'default' => array(
                    'label' => __( 'Theme Default', 'auxin-elements' ),
                    'image' => AUXIN_URL . 'images/visual-select/default3.svg'
                ),
                'footer_preset1' => array(
                    'label' => __( 'Footer Preset 1', 'auxin-elements' ),
                    'image' => AUXIN_URL . 'images/visual-select/footer-layout-1.svg'
                ),
                'footer_preset2' => array(
                    'label' => __( 'Footer Preset 2', 'auxin-elements' ),
                    'image' => AUXIN_URL . 'images/visual-select/footer-layout-2.svg'
                ),
                'footer_preset3' => array(
                    'label' => __( 'Footer Preset 3', 'auxin-elements' ),
                    'image' => AUXIN_URL . 'images/visual-select/footer-layout-3.svg'
                ),
                'footer_preset4' => array(
                    'label' => __( 'Footer Preset 4', 'auxin-elements' ),
                    'image' => AUXIN_URL . 'images/visual-select/footer-layout-4.svg'
                ),
                'footer_preset5' => array(
                    'label' => __( 'Footer Preset 5', 'auxin-elements' ),
                    'image' => AUXIN_URL . 'images/visual-select/footer-layout-4.svg'
                )
            ),
            'default'   => 'default'
        ),

        array(
            'title'       => __( 'Footer Copyright Text', 'auxin-elements' ),
            'description' => __( 'Enter your copyright text to display on footer.', 'auxin-elements' ),
            'id'          => 'page_footer_copyright',
            'dependency'  => array(
                array(
                     'id'      => 'page_show_footer',
                     'value'   => array('yes', 'default'),
                     'operator'=> '=='
                )
            ),
            'type'        => 'textarea'
        ),

        array(
            'title'       => __( 'Show Phlox attribution', 'auxin-elements' ),
            'description' => __( 'Show the "Powered By Phlox" text with link to Phlox homepage in footer.', 'auxin-elements' ),
            'id'          => 'page_footer_attribution',
            'dependency' => array(
                array(
                     'id'      => 'page_show_footer',
                     'value'   => array('yes', 'default'),
                     'operator'=> '=='
                )
            ),
            'type'          => 'select',
            'default'       => 'default',
            'choices'       => array(
                'default' => __( 'Theme Default', 'auxin-elements' ),
                'yes'     => __( 'Yes', 'auxin-elements' ),
                'no'      => __( 'No', 'auxin-elements' ),
            ),
        ),

        array(
            'title'       => __( 'Enbale Sticky Footer', 'auxin-elements' ),
            'description' => __( 'Enable this option to pin the footer and subfooter to bottom of the website.', 'auxin-elements' ),
            'id'          => 'page_footer_is_sticky',
            'dependency' => array(
                array(
                     'id'      => 'page_show_footer',
                     'value'   => array('yes', 'default'),
                     'operator'=> '=='
                )
            ),
            'type'          => 'select',
            'default'       => 'default',
            'choices'       => array(
                'default' => __( 'Theme Default', 'auxin-elements' ),
                'yes'     => __( 'Yes', 'auxin-elements' ),
                'no'      => __( 'No', 'auxin-elements' ),
            ),
        ),

        array(
            'title'       => __( 'Footer Background Color', 'auxin-elements' ),
            'description' => __( 'Specifies the background color for footer', 'auxin-elements' ),
            'id'          => 'page_footer_bg_color',
            'dependency'  => array(
                array(
                     'id'      => 'page_show_footer',
                     'value'   => array('yes', 'default'),
                     'operator'=> '=='
                )
            ),
            'default'     => '',
            'type'        => 'color',
            'style_callback' => function( $value = null ){
                return empty( $value ) ? '' : ".aux-site-footer { background-color:$value; }";
            }
        ),

        array(
            'title'       => __( 'Footer Top Border Color', 'auxin-elements' ),
            'id'          => 'page_footer_top_border_color',
            'description' => __( 'Specifies top border color of footer.', 'auxin-elements' ),
            'type'        => 'color',
            'dependency' => array(
                array(
                     'id'      => 'page_show_footer',
                     'value'   => array('yes', 'default'),
                     'operator'=> '=='
                )
            ),
            'style_callback' => function( $value = null ){
                return $value ? ".aux-site-footer { border-top:1px solid $value; }" : '';
            },
            'default'   => ''
        ),

        array(
            'title'         => __('Display Subfooter', 'auxin-elements'),
            'description'   => __( 'Enable it to display subfooter on this pages.', 'auxin-elements' ),
            'id'            => 'page_show_subfooter',
            'type'          => 'select',
            'default'       => 'default',
            'choices'       => array(
                'default' => __( 'Theme Default', 'auxin-elements' ),
                'yes'     => __( 'Yes', 'auxin-elements' ),
                'no'      => __( 'No', 'auxin-elements' ),
            ),
        ),


    );

    return $model;

}
