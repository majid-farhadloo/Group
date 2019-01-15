/**
 * Initialize all modules
 */
;(function($, window, document, undefined){

    $( window ).on( 'elementor:init', function() {

        // Add auxin specific css class to elementor body
        $('.elementor-editor-active').addClass('auxin');

        // Make our custom css visible in the panel's front-end
        if( typeof elementorPro == 'undefined' ) {
            elementor.hooks.addFilter( 'editor/style/styleText', function( css, view ){
                var model = view.getEditModel(),
                    customCSS = model.get( 'settings' ).get( 'custom_css' );

                if ( customCSS ) {
                    css += customCSS.replace( /selector/g, '.elementor-element.elementor-element-' + view.model.id );
                }

                return css;
            });
        }

        var AuxControlBaseDataView = elementor.modules.controls.BaseData;

        /*!
         * ================== Auxin Visual Select Controller ===================
         **/
        var AuxControlVisualSelectItemView = AuxControlBaseDataView.extend( {
            onReady: function() {
                this.ui.select.avertaVisualSelect();
            },
            onBeforeDestroy: function() {
                this.ui.select.avertaVisualSelect( 'destroy' );
            }
        } );
        elementor.addControlView( 'aux-visual-select', AuxControlVisualSelectItemView );


        /*!
         * ================== Auxin Media Select Controller ===================
         **/
        var AuxControlMediaSelectItemView = AuxControlBaseDataView.extend( {
            ui: function() {
                var ui = AuxControlBaseDataView.prototype.ui.apply( this, arguments );

                ui.controlMedia = '.aux-elementor-control-media';
                ui.mediaImage = '.aux-elementor-control-media-attachment';
                ui.frameOpeners = '.aux-elementor-control-media-upload-button, .aux-elementor-control-media-attachment';
                ui.deleteButton = '.aux-elementor-control-media-delete';

                return ui;
            },

            events: function() {
                return _.extend( AuxControlBaseDataView.prototype.events.apply( this, arguments ), {
                    'click @ui.frameOpeners': 'openFrame',
                    'click @ui.deleteButton': 'deleteImage'
                } );
            },

            applySavedValue: function() {
                var control = this.getControlValue();

                this.ui.mediaImage.css( 'background-image', control.img ? 'url(' + control.img + ')' : '' );

                this.ui.controlMedia.toggleClass( 'elementor-media-empty', ! control.img );
            },

            openFrame: function() {
                if ( ! this.frame ) {
                    this.initFrame();
                }

                this.frame.open();
            },

            deleteImage: function() {
                this.setValue( {
                    url: '',
                    img: '',
                    id : ''
                } );

                this.applySavedValue();
            },

            /**
             * Create a media modal select frame, and store it so the instance can be reused when needed.
             */
            initFrame: function() {
                this.frame = wp.media( {
                    button: {
                        text: elementor.translate( 'insert_media' )
                    },
                    states: [
                        new wp.media.controller.Library( {
                            title: elementor.translate( 'insert_media' ),
                            library: wp.media.query( { type: this.ui.controlMedia.data('media-type') } ),
                            multiple: false,
                            date: false
                        } )
                    ]
                } );

                // When a file is selected, run a callback.
                this.frame.on( 'insert select', this.select.bind( this ) );
            },

            /**
             * Callback handler for when an attachment is selected in the media modal.
             * Gets the selected image information, and sets it within the control.
             */
            select: function() {
                this.trigger( 'before:select' );

                // Get the attachment from the modal frame.
                var attachment = this.frame.state().get( 'selection' ).first().toJSON();

                if ( attachment.url ) {
                    this.setValue( {
                        url: attachment.url,
                        img: attachment.image.src,
                        id : attachment.id
                    } );

                    this.applySavedValue();
                }

                this.trigger( 'after:select' );
            },

            onBeforeDestroy: function() {
                this.$el.remove();
            }
        } );
        elementor.addControlView( 'aux-media', AuxControlMediaSelectItemView );

        /*!
         * ================== Auxin Icon Select Controller ===================
         **/
        var AuxControlSelect2 = elementor.modules.controls.Select2;

        var ControlIconSelectItemView = AuxControlSelect2.extend( {
            initialize: function() {
                AuxControlSelect2View.prototype.initialize.apply( this, arguments );

                this.filterIcons();
            },

            filterIcons: function() {
                var icons = this.model.get( 'options' ),
                    include = this.model.get( 'include' ),
                    exclude = this.model.get( 'exclude' );

                if ( include ) {
                    var filteredIcons = {};

                    _.each( include, function( iconKey ) {
                        filteredIcons[ iconKey ] = icons[ iconKey ];
                    } );

                    this.model.set( 'options', filteredIcons );
                    return;
                }

                if ( exclude ) {
                    _.each( exclude, function( iconKey ) {
                        delete icons[ iconKey ];
                    } );
                }
            },

            iconsList: function( icon ) {
                if ( ! icon.id ) {
                    return icon.text;
                }

                return jQuery(
                    '<span><i class="' + icon.id + '"></i> ' + icon.text + '</span>'
                );
            },

            getSelect2Options: function() {
                return {
                    allowClear: true,
                    templateResult: this.iconsList.bind( this ),
                    templateSelection: this.iconsList.bind( this )
                };
            }
        } );
        elementor.addControlView( 'aux-icon', ControlIconSelectItemView );

        // ControlSelect2View prototype
        var AuxControlSelect2View = AuxControlSelect2.extend( {
            getSelect2Options: function() {
                return {
                    dir: elementor.config.is_rtl ? 'rtl' : 'ltr'
                };
            },

            templateHelpers: function() {
                var helpers = AuxControlSelect2View.prototype.templateHelpers.apply( this, arguments ),
                    fonts = this.model.get( 'options' );

                helpers.getFontsByGroups = function( groups ) {
                    var filteredFonts = {};

                    _.each( fonts, function( fontType, fontName ) {
                        if ( _.isArray( groups ) && _.contains( groups, fontType ) || fontType === groups ) {
                            filteredFonts[ fontName ] = fontName;
                        }
                    } );

                    return filteredFonts;
                };

                console.log(helpers);

                return helpers;
            }
        } );

    } );

})(jQuery, window, document);
