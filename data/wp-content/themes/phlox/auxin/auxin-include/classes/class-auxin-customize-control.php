<?php
/**
 * Auxin Customize Control Class
 *
 * 
 * @package    Auxin
 * @author     averta (c) 2014-2018
 * @link       http://averta.net
*/


/**
 * Customize Base Control class.
 */
class Auxin_Customize_Control extends WP_Customize_Control {

    // The control dependencies
    protected $dependency = array();
    public $devices;
    public $device;


    public function __construct( $manager, $id, $args = array() ) {
        parent::__construct( $manager, $id, $args );
        if( isset( $this->dependency['relation'] ) ){
            $this->dependency[] = array( 'relation' => $this->dependency['relation'] );
            unset( $this->dependency['relation'] );
        } elseif ( is_array( $this->dependency ) ){
            $this->dependency[] = array( 'relation' => 'and' );
        }

        add_action( 'customize_preview_init' , array( $this, 'preview_script' ) );
    }


    /**
     * Adds javascript for preview on changes for each control
     */
    public function preview_script(){
        wp_enqueue_script( 'customize-preview' );

        ob_start();
        ?>
        // will trigger on changes for all controls
        ;( function( $ ) {
            wp.customize( '<?php echo esc_js( $this->setting->id ); ?>', function( value ) {
                value.bind( function( to ) {
                    $(window).trigger('resize');
                });
            });
        } )( jQuery );
        <?php
        $js = ob_get_clean();

        wp_add_inline_script( 'customize-preview', $js, 'after' );
    }


    /**
     * Enqueue scripts/styles for the color picker.
     */
    public function enqueue() {
        wp_enqueue_script('wp-util');
        wp_enqueue_script('auxin_plugins');
        wp_enqueue_script('auxin_script');
    }

    /**
     * Refresh the parameters passed to the JavaScript via JSON.
     */
    public function to_json() {
        parent::to_json();

        $field_dependencies = array();

        if( ! empty( $this->dependency ) ){
            $dependencies = (array) $this->dependency;

            foreach ( $dependencies as $target_id => $target ) {

                if( 'relation' === $target_id ) {
                    continue;
                }

                if( empty( $target['id'] ) || ! ( isset( $target['value'] ) && ! empty( $target['value'] ) ) ){ continue; }

                // make sure there is no duplication in values array
                if( is_array( $target['value'] ) ){
                    $target['value'] = array_unique( $target['value'] );
                }

                // if the operator was not defined or was defined as '=' by mistake
                $target['operator'] = ! empty( $target['operator'] ) && ( '=' !== $target['operator'] )  ? $target['operator'] : '==';

                $field_dependencies[ $target_id ] = $target;
            }

            $field_dependencies[ $target_id ] = $target;
        }

        $this->json['dependencies'] = $field_dependencies;
    }

}


class Auxin_Customize_Code_Control extends Auxin_Customize_Control {
    /**
     * @access public
     * @var string
     */
    public $type = 'auxin_code';

    public $mode = 'javascript';

    public $button_labels = array();



    public function __construct( $manager, $id, $args = array() ) {
        parent::__construct( $manager, $id, $args );

        $this->button_labels = wp_parse_args( $this->button_labels, array(
            'description'  => __( 'The description', 'phlox' ),
            'label'        => __( 'Submit', 'phlox' )
        ));

        add_action( 'customize_preview_init' , array( $this, 'custom_script' ) );
    }

    public function custom_script(){
        if( 'javascript' !== $this->mode ){
            return;
        }

        wp_enqueue_script( 'customize-preview' );

        ob_start();
        ?>
        /**
         * Note: This the only solution that we found in order to use customizer API to create live custom JS editor
         * This section only executes in customizer preview, not in admin or front end side
         */
        ;( function( $ ) {

            wp.customize( '<?php echo esc_js( $this->setting->id ); ?>', function( value ) {
                value.bind( function( to ) {
                    var $body  = $( 'body' ),
                    dom_id = '<?php echo esc_js( $this->setting->id ); ?>_script';
                    $body.find( '#' + dom_id ).remove();
                    $body.append( '<' + 'script id=\"'+ dom_id +'\" >try{ ' + to + ' } catch(ex) { console.error( "Custom JS:", ex.message ); }</script' + '>' ).find( '#' + dom_id );
                });
            });

        } )( jQuery );
        <?php
        $js = ob_get_clean();

        wp_add_inline_script( 'customize-preview', $js, 'after' );
    }

    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     */
    public function render_content() {
            // editoe mode
            $editor_mode = ! empty( $this->mode ) ? $this->mode : 'javascript';
            $data_device = isset( $this->device ) ? 'data-device="' . esc_attr( $this->device ) . '"' : '';
    ?>
        <label <?php echo $data_device; ?>>
            <?php if ( ! empty( $this->label ) ) : ?>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php endif;
            if ( ! empty( $this->description ) ) : ?>
                <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>

            <textarea id="<?php echo esc_attr( $this->setting->id ); ?>" class="code_editor" rows="5" <?php $this->link(); ?> placeholder="<?php esc_attr( $this->setting->default ); ?>"
            data-code-editor="<?php echo esc_attr( $editor_mode ); ?>" ><?php echo stripslashes( $this->value() ); ?></textarea>

            <?php if( 'javascript' == $this->mode && $this->button_labels['label'] ){ ?>
            <button class="<?php echo esc_attr( $this->setting->id ); ?>-submit button button-primary"><?php echo esc_html( $this->button_labels['label'] ); ?></button>
            <?php } ?>
        </label>
        <hr />
    <?php
    }
}


/**
 * Customize Typography Control class.
 */
class Auxin_Customize_Typography_Control extends Auxin_Customize_Control {
    /**
     * @access public
     * @var string
     */
    public $type = 'auxin_typography';




     public function __construct( $manager, $id, $args = array() ) {
        parent::__construct( $manager, $id, $args );

        add_action( 'customize_preview_init' , array( $this, 'live_google_font_loading_script' ) );
    }


    /**
     * Adds javascript for preview on changes for each control
     */
    public function live_google_font_loading_script(){
        wp_enqueue_script( 'customize-preview' );

        ob_start();
        ?>
        /**
         * Note: This section just preloads the google fonts for preview in customizer typography controls
         *       It does not load the fonts in front end or admin area
         */
        ;( function( $ ) {
            wp.customize( '<?php echo esc_js( $this->setting->id ); ?>', function( value ) {
                value.bind( function( to ) {
                    var components = to.match("_gof_(.*):");
                    if( components && components.length > 1 ){
                        var face = components[1];
                        face = face.split(' ').join('+'); // convert spaces to "+" char

                        var google_font_url = '//fonts.googleapis.com/css?family='+ face +
                                              ':400,900italic,900,800italic,800,700italic,700,600italic,600,500italic,500,400italic,300italic,300,200italic,200,100italic,100';

                        var $body  = $( 'body' ),
                        dom_id = '<?php echo esc_js( $this->setting->id ); ?>_font';
                        $body.find( '#' + dom_id ).remove();
                        $body.append( '<link rel=\"stylesheet\" id=\"' + dom_id + '\" href=\"' + google_font_url + '\" type=\"text/css\" />' );
                    }
                });
            });
        } )( jQuery );
        <?php
        $js = ob_get_clean();

        wp_add_inline_script( 'customize-preview', $js, 'after' );
    }


    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     */
    public function render_content() {
    ?>
        <label>
            <?php if ( ! empty( $this->label ) ) : ?>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php endif;
            if ( ! empty( $this->description ) ) : ?>
                <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif;


            $fields_output     = '';

            // Font face and thickness

            // Get default value for font info
            if( ! $typo_info = auxin_get_option( $this->id ) ){ // get stored value if available
                 // otherwise use default value
                $typo_info = isset( $this->default ) ? $this->default : '';
            }

            // temporary fix for compatibility with old stored data. will deprecated in 1.3
            if( isset( $typo_info['font'] ) ){
                $typo_info = $typo_info['font'];
            }

            $fields_output .= '<div class="typo_fields_wrapper typo_font_wrapper" >';
            $fields_output .= '<input type="text" class="axi-font-field" name="'.esc_attr( $this->id ).'" id="'. esc_attr( $this->id ).'" ' . $this->get_link() . ' value="'.esc_attr( $typo_info ).'"  />';
            $fields_output .= '</div>';

            $fields_output .= "</label><hr />";

        echo $fields_output;
    }

}


/**
 * Customize Radio_Image Control class.
 */
class Auxin_Customize_Radio_Image_Control extends Auxin_Customize_Control {

    /**
     * Control type
     *
     * @var string
     */
    public $type = 'auxin_radio_image';

    /**
     * Control Presets
     *
     * @var array
     */
    public $presets = array();

    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     */
    public function render_content() {
        $data_device = isset( $this->device ) ? 'data-device="' . esc_attr( $this->device ) . '"' : '';
    ?>
        <label <?php echo $data_device; ?>>
            <?php if ( ! empty( $this->label ) ) : ?>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php endif;
            if ( ! empty( $this->description ) ) : ?>
                <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>

            <select class="visual-select-wrapper" <?php $this->link(); ?>>
                <?php
                $presets = array();

                foreach ( $this->choices as $choice_id => $choice_info ){

                    $data_class  = isset( $choice_info['css_class'] ) && ! empty( $choice_info['css_class'] ) ? 'data-class="'. esc_attr( $choice_info['css_class'] ).'"' : '';
                    $data_symbol = ! empty( $choice_info['image'] ) ? 'data-symbol="'. esc_attr( $choice_info['image'] ).'"' : '';
                    $data_video  = ! empty( $choice_info['video_src'] ) ? 'data-video-src="'. esc_attr( $choice_info['video_src'] ).'"' : '';

                    if( isset( $choice_info['presets'] ) && ! empty( $choice_info['presets'] ) ){
                        $presets[ $choice_id ] = $choice_info['presets'];
                    }

                    echo sprintf( '<option value="%s" %s %s %s %s>%s</option>', esc_attr( $choice_id ),
                        selected( $this->value(), $choice_id, false ) ,
                        $data_symbol, $data_video, $data_class, esc_html( $choice_info['label'] )
                    );
                }

                // Define the presets if was defined
                if( ! empty( $presets ) ){
                    $this->presets = $presets;
                }
                ?>
            </select>
        </label>
        <hr />
    <?php
    }


    /**
     * Refresh the parameters passed to the JavaScript via JSON.
     */
    public function to_json() {
        parent::to_json();

        $this->json['presets'] = $this->presets;
    }

}


/**
 * Customize Icon Control class.
 */
class Auxin_Customize_Icon_Control extends Auxin_Customize_Control {
    /**
     * @access public
     * @var string
     */
    public $type = 'auxin_icon';


    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     */
    public function render_content() {
        $font_icons = Auxin()->Font_Icons->get_icons_list('fontastic');
        $data_device = isset( $this->device ) ? 'data-device="' . esc_attr( $this->device ) . '"' : '';
    ?>
        <label <?php echo $data_device; ?>>
            <?php if ( ! empty( $this->label ) ) : ?>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php endif;
            if ( ! empty( $this->description ) ) : ?>
                <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>

            <select <?php $this->link(); ?> class="meta-select aux-fonticonpicker">
                <?php
                echo '<option value="">' . __('Choose ..', 'phlox') . '</option>';

                if( is_array( $font_icons ) ){
                    foreach ( $font_icons as $icon ) {
                        $icon_id = trim( $icon->classname, '.' );
                        echo '<option value="'. esc_attr( $icon_id ) .'" '. selected( $this->value(), $icon_id, false ) .' >'. esc_html( $icon->name ) . '</option>';
                    }
                }
                ?>
            </select>
        </label>
        <hr />
    <?php
    }
}


/**
 * Customize Textarea Control class.
 */
class Auxin_Customize_Textarea_Control extends Auxin_Customize_Control {
    /**
     * @access public
     * @var string
     */
    public $type = 'auxin_textarea';

    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     */
    public function render_content() {
        $data_device = isset( $this->device ) ? 'data-device="' . esc_attr( $this->device ) . '"' : '';
    ?>
        <label <?php echo $data_device; ?>>
            <?php if ( ! empty( $this->label ) ) : ?>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php endif;
            if ( ! empty( $this->description ) ) : ?>
                <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>
            <textarea rows="5" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
        </label>

        <hr />
    <?php
    }
}


/**
 * Customize Editor Control class.
 */
class Auxin_Customize_Editor_Control extends Auxin_Customize_Control {
    /**
     * @access public
     * @var string
     */
    public $type = 'auxin_editor';

    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     */
    public function render_content() {
        $data_device = isset( $this->device ) ? 'data-device="' . esc_attr( $this->device ) . '"' : '';
    ?>
        <label <?php echo $data_device; ?>>
            <?php if ( ! empty( $this->label ) ) : ?>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php endif;
            if ( ! empty( $this->description ) ) : ?>
                <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>

            <?php wp_editor( stripslashes( $this->value() ), $this->id, array( 'media_buttons' => false ) ); ?>
        </label>
        <hr />
    <?php
    }
}


/**
 * Customize Select2 Multiple Control class.
 */
class Auxin_Customize_Select2_Multiple_Control extends Auxin_Customize_Control {
    /**
     * @access public
     * @var string
     */
    public $type = 'auxin_select2_multiple';



    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     */
    public function render_content() {
        $data_device = isset( $this->device ) ? 'data-device="' . esc_attr( $this->device ) . '"' : '';
    ?>
        <label <?php echo $data_device; ?>>
            <?php if ( ! empty( $this->label ) ) : ?>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php endif;
            if ( ! empty( $this->description ) ) : ?>
                <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>

            <select class="aux-orig-select2 aux-admin-select2 aux-select2-multiple" multiple="multiple" <?php $this->link(); ?>>
                <?php
                foreach ( $this->choices as $value => $label )
                    echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . '>' .esc_html( $label ) . '</option>';
                ?>
            </select>
        </label>
        <hr />
    <?php
    }
}


/**
 *
 */
class Auxin_Customize_Select2_Post_Types_Control extends Auxin_Customize_Control {

    /**
     * @access public
     * @var string
     */
    public $type = 'auxin_select2_multiple';




    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     */
    public function render_content() {

        $this->choices = get_post_types( array( 'public' => true, 'exclude_from_search' => false ) );
        $data_device = isset( $this->device ) ? 'data-device="' . esc_attr( $this->device ) . '"' : '';
    ?>
        <label <?php echo $data_device; ?>>
            <?php if ( ! empty( $this->label ) ) : ?>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php endif;
            if ( ! empty( $this->description ) ) : ?>
                <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>

            <select class="aux-orig-select2 aux-admin-select2 aux-select2-multiple" multiple="multiple" style="width: 100%" <?php $this->link(); ?>>
                <?php
                foreach ( $this->choices as $value )
                    echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . '>' .esc_html( $value ) . '</option>';
                ?>
            </select>
        </label>
        <hr />
    <?php
    }

}

/**
 * Customize Select2 Control class.
 */
class Auxin_Customize_Select2_Control extends Auxin_Customize_Control {
    /**
     * @access public
     * @var string
     */
    public $type = 'auxin_select2';



    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     */
    public function render_content() {
        $data_device = isset( $this->device ) ? 'data-device="' . esc_attr( $this->device ) . '"' : '';
    ?>
        <label <?php echo $data_device; ?>>
            <?php if ( ! empty( $this->label ) ) : ?>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php endif;
            if ( ! empty( $this->description ) ) : ?>
                <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>

            <select class="aux-orig-select2 aux-admin-select2 aux-select2-single" <?php $this->link(); ?>>
                <?php
                foreach ( $this->choices as $value => $label )
                    echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . '>' .esc_html( $label ) . '</option>';
                ?>
            </select>
        </label>
        <hr />
    <?php
    }
}


/**
 * Customize Select Control class.
 */
class Auxin_Customize_Select_Control extends Auxin_Customize_Control {
    /**
     * @access public
     * @var string
     */
    public $type = 'auxin_select';



    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     */
    public function render_content() {
        $data_device = isset( $this->device ) ? 'data-device="' . esc_attr( $this->device ) . '"' : '';
    ?>
        <label <?php echo $data_device; ?>>
            <?php if ( ! empty( $this->label ) ) : ?>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php endif;
            if ( ! empty( $this->description ) ) : ?>
                <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>

            <select <?php $this->link(); ?>>
                <?php
                foreach ( $this->choices as $value => $label )
                    echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . '>' .esc_html( $label ) . '</option>';
                ?>
            </select>
        </label>
        <hr />
    <?php
    }
}



/**
 * Customize Media Control class.
 */
class Auxin_Customize_Media_Control extends Auxin_Customize_Control {

    /**
     * Control type
     */
    public $type = 'auxin_media';

    /**
     * Media control mime type.
     */
    public $mime_type = 'image';

    /**
     * Max number of attachments
     */
    public $limit = 9999;

    /**
     * Allow multiple uploads
     */
    public $multiple = true;


    /**
     * Button labels.
     *
     * @var array
     */
    public $button_labels = array();



    /**
     * Constructor.
     *
     * @param WP_Customize_Manager $manager Customizer bootstrap instance.
     * @param string               $id      Control ID.
     * @param array                $args    Optional. Arguments to override class property defaults.
     */
    public function __construct( $manager, $id, $args = array() ) {
        parent::__construct( $manager, $id, $args );

        $this->button_labels = wp_parse_args( $this->button_labels, array(
            'add'          => esc_attr__( 'Add File', 'phlox' ),
            'change'       => esc_attr__( 'Change File', 'phlox' ),
            'submit'       => esc_attr__( 'Select File', 'phlox' ),
            'remove'       => esc_attr__( 'Remove', 'phlox' ),
            'frame_title'  => esc_attr__( 'Select File', 'phlox' ),
            'frame_button' => esc_attr__( 'Choose File', 'phlox' )
        ));

    }


    /**
     * Enqueue control related scripts/styles.
     *
     */
    public function enqueue() {
        wp_enqueue_media();
    }

    /**
     * Refresh the parameters passed to the JavaScript via JSON.
     *
     * @since 3.4.0
     */
    public function to_json() {
        parent::to_json();

        $this->json['settings'] = array();
        foreach ( $this->settings as $key => $setting ) {
            $this->json['settings'][ $key ] = $setting->id;
        }

        $value = $this->value();

        $this->json['type']           = $this->type;
        $this->json['priority']       = $this->priority;
        $this->json['active']         = $this->active();
        $this->json['section']        = $this->section;
        $this->json['content']        = $this->get_content();
        $this->json['label']          = $this->label;
        $this->json['description']    = $this->description;
        $this->json['instanceNumber'] = $this->instance_number;

        $this->json['mime_type']      = $this->mime_type;
        $this->json['button_labels']  = $this->button_labels;

        $this->json['canUpload']      = current_user_can( 'upload_files' );
        $this->json['value']          = $value;
        $this->json['attachments']    = array('-4' => '' );

        if ( $value ) {
            if( $att_ids = explode( ',', $value ) ){
                $this->json['attachments'] += auxin_get_the_resized_attachment_src( (array) $att_ids, 80, 80, true );
            }
        }

    }


    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     */
    public function render_content() {
        $data_device = isset( $this->device ) ? 'data-device="' . esc_attr( $this->device ) . '"' : '';
    ?>
        <label <?php echo $data_device; ?>>
        <?php if ( ! empty( $this->label ) ) : ?>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php endif;
            if ( ! empty( $this->description ) ) : ?>
                <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>

            <div class="axi-attachmedia-wrapper" >

                <input type="text" class="white" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?>
                                   data-media-type="<?php echo esc_attr( $this->mime_type ); ?>" data-limit="<?php echo esc_attr( $this->limit ); ?>" data-multiple="<?php echo esc_attr( $this->multiple ); ?>"
                                   data-add-to-list="<?php echo esc_attr( $this->button_labels['add'] ); ?>"
                                   data-uploader-submit="<?php echo esc_attr( $this->button_labels['submit'] ); ?>"
                                   data-uploader-title="<?php echo esc_attr( $this->button_labels['frame_title'] ); ?>"
                                   />
            <?php
                // Store attachment src for avertaAttachMedia field
                if( $att_ids = explode( ',', $this->value() ) ){
                    $this->manager->attach_ids_list += auxin_get_the_resized_attachment_src( $att_ids, 80, 80, true );
                }
            ?>
            </div>

        </label>
        <hr />
    <?php
    }
}


/**
 * Customize Switch Control class.
 */
class Auxin_Customize_Switch_Control extends Auxin_Customize_Control {
    /**
     * @access public
     * @var string
     */
    public $type = 'auxin_switch';


    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     */
    public function render_content() {
        $data_device = isset( $this->device ) ? 'data-device="' . esc_attr( $this->device ) . '"' : '';
    ?>
        <label <?php echo $data_device; ?>>
        <?php if ( ! empty( $this->label ) ) : ?>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php endif;
            if ( ! empty( $this->description ) ) : ?>
                <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>
            <input type="checkbox" class="aux_switch" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); checked( $this->value() ); ?> />

        </label>
        <hr />
    <?php
    }
}


/**
 * Customize Color Control class.
 */
class Auxin_Customize_Color_Control extends Auxin_Customize_Control {
    /**
     * @access public
     * @var string
     */
    public $type = 'auxin_color';

    /**
     * @access public
     * @var array
     */
    public $statuses;

    /**
     * Constructor.
     *
     * @param WP_Customize_Manager $manager Customizer bootstrap instance.
     * @param string               $id      Control ID.
     * @param array                $args    Optional. Arguments to override class property defaults.
     */
    public function __construct( $manager, $id, $args = array() ) {
        $this->statuses = '';
        parent::__construct( $manager, $id, $args );
    }

    /**
     * Refresh the parameters passed to the JavaScript via JSON.
     */
    public function to_json() {
        parent::to_json();
        $this->json['statuses'] = $this->statuses;
        $this->json['defaultValue'] = $this->setting->default;
    }

    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     */
    public function render_content() {
        $data_device = isset( $this->device ) ? 'data-device="' . esc_attr( $this->device ) . '"' : '';
        ?>
        <label <?php echo $data_device; ?>>
            <?php if ( ! empty( $this->label ) ) : ?>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php endif; ?>
            <?php if ( ! empty( $this->description ) ) : ?>
                <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>
            <div class="mini-color-wrapper">
                <input type="text" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
            </div>
        </label>
        <hr />
        <?php
    }
}

/**
 * Customize Color Control class.
 */
class Auxin_Customize_Gradient_Control extends Auxin_Customize_Control {
    /**
     * @access public
     * @var string
     */
    public $type = 'auxin_gradient';

    /**
     * @access public
     * @var array
     */
    public $statuses;

    /**
     * Constructor.
     *
     * @param WP_Customize_Manager $manager Customizer bootstrap instance.
     * @param string               $id      Control ID.
     * @param array                $args    Optional. Arguments to override class property defaults.
     */
    public function __construct( $manager, $id, $args = array() ) {
        $this->statuses = '';
        parent::__construct( $manager, $id, $args );
    }

    /**
     * Refresh the parameters passed to the JavaScript via JSON.
     */
    public function to_json() {
        parent::to_json();
        $this->json['statuses'] = $this->statuses;
        $this->json['defaultValue'] = $this->setting->default;
    }

    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     */
    public function render_content() {
        $data_device = isset( $this->device ) ? 'data-device="' . esc_attr( $this->device ) . '"' : '';
        ?>
        <label <?php echo $data_device; ?>>
            <?php if ( ! empty( $this->label ) ) : ?>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php endif; ?>
            <?php if ( ! empty( $this->description ) ) : ?>
                <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>
            <div class="mini-gradient-wrapper">
                <div class="aux-grapick">
                  <div class="aux-grapick-colors"></div>
                  <div class="aux-grapick-inputs">
                    <select class="aux-gradient-type">
                      <option value="">- Select Type -</option>
                      <option value="radial">Radial</option>
                      <option value="linear" selected>Linear</option>
                      <option value="repeating-radial">Repeating Radial</option>
                      <option value="repeating-linear">Repeating Linear</option>
                    </select>
                    <select class="aux-gradient-direction">
                      <option value="">- Select Direction -</option>
                      <option value="top">Top</option>
                      <option value="right" selected>Right</option>
                      <option value="center">Center</option>
                      <option value="bottom">Bottom</option>
                      <option value="left">Left</option>
                    </select>
                  </div>
                </div>
                <input type="text" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
            </div>
        </label>
        <hr />
        <?php
    }
}

/**
 * Customize Sortable_Input Control class.
 */
class Auxin_Customize_Sortable_Input_Control extends Auxin_Customize_Control {
    /**
     * @access public
     * @var string
     */
    public $type = 'aux_sortable_input';

    /**
     * @access public
     * @var array
     */
    public $statuses;

    /**
     * Constructor.
     *
     * @param WP_Customize_Manager $manager Customizer bootstrap instance.
     * @param string               $id      Control ID.
     * @param array                $args    Optional. Arguments to override class property defaults.
     */
    public function __construct( $manager, $id, $args = array() ) {
        $this->statuses = '';
        parent::__construct( $manager, $id, $args );
    }

    /**
     * Refresh the parameters passed to the JavaScript via JSON.
     */
    public function to_json() {
        parent::to_json();
        $this->json['statuses'] = $this->statuses;
        $this->json['defaultValue'] = $this->setting->default;
    }

    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     */
    public function render_content() {
        $list_items = array();

        if( ! empty( $this->choices ) ){
            foreach( $this->choices as $_node_id => $_node_label ){
                $list_items[] = array( 'id' => $_node_id, 'label' => $_node_label );
            }
        }

        $list_items = wp_json_encode( $list_items );
        $data_device = isset( $this->device ) ? 'data-device="' . esc_attr( $this->device ) . '"' : '';

        ?>
        <label <?php echo $data_device; ?>>
            <?php if ( ! empty( $this->label ) ) : ?>
                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php endif;
            if ( ! empty( $this->description ) ) : ?>
                <span class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>
            <div class="aux-sortin-container">
                <input type="text" class="aux-sortable-input" value="<?php echo esc_attr( wp_specialchars_decode( $this->value() ) ); ?>" <?php $this->link(); ?> data-fields="<?php echo esc_attr( wp_specialchars_decode( $list_items ) ); ?>" />
            </div>
        </label>
        <hr />
        <?php
    }
}


/**
 * Customize Base Control class.
 */
class Auxin_Customize_Input_Control extends Auxin_Customize_Control {
    /**
     *
     */
    public $type = 'auxin_base';

    /**
     * Don't render the control content from PHP, as it's rendered via JS on load.
     */
    public function render_content() {
        $real_type  = $this->type;

        if( isset( $this->input_attrs['type'] ) ){
            $this->type = $this->input_attrs['type'];
        }

        parent::render_content();

        $this->type = $real_type;
        echo "<hr />";
    }
}



