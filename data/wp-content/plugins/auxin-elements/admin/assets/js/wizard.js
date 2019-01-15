/**
 * AuxWizard functionality
 */
(function( $ ){
    $.fn.AuxWizard = function( element ) {

        var $progressLabel,
            importProcess = false,
            demoID,
            modalElement,
            parentElement,
            progressBar,
            progressLabel,
            $progressLabel,
            nonceField,
            demoOptions,
            demoProgress;

        // callbacks from form button clicks.
        var callbacks = {
            install_plugins: function(btn){
                var plugins = new pluginManager();
                plugins.init(btn);
            },
            install_demos: function(btn){
                var content = new demoManager();
                content.init(btn);
            }
        };

        function window_loaded(){
            // init button clicks
            $(document).on('click', '.button-next', function(e) {
                e.preventDefault();

                if($(this).data('callback') && typeof callbacks[$(this).data('callback')] != 'undefined'){
                    // we have to process a callback before continue with form submission
                    callbacks[$(this).data('callback')](this);
                }

            });

            // init button clicks:
            $(document).on('click', '.aux-next-step', function(e) {
                e.preventDefault();

                if( $(this).data('callback') !== 'undefined' ){
                    var step = new stepManager();
                    step.init( this );
                }
            });

            // general isotope layout
            $('.aux-isotope-list').AuxIsotope({
                itemSelector:'.aux-iso-item',
                revealTransitionDuration  : 600,
                revealBetweenDelay        : 50,
                revealTransitionDelay     : 0,
                hideTransitionDuration    : 300,
                hideBetweenDelay          : 0,
                hideTransitionDelay       : 0,
                updateUponResize          : true,
                transitionHelper          : true,
                filters                   : '.aux-filters',
                slug                      : 'filter',
                imgSizes                  : true
            });

            $('.aux-isotope-plugins-list').AuxIsotope({
                itemSelector:'.aux-iso-item',
                revealTransitionDuration  : 600,
                revealBetweenDelay        : 50,
                revealTransitionDelay     : 50,
                hideTransitionDuration    : 100,
                hideBetweenDelay          : 0,
                hideTransitionDelay       : 0,
                updateUponResize          : true,
                transitionHelper          : true,
                filters                   : '.aux-filters',
                slug                      : 'filter',
                imgSizes                  : true
            });

            $('.aux-togglable').AuxinToggleSelected();

            // init plugins border effect
            $('.aux-wizard-plugins input[name="plugin[]"]').each(function() {
                if($(this).is(':checked')) {
                    $(this).closest('th').addClass('is-checked');
                } else {
                     $(this).closest('th').removeClass('is-checked');
                }
                $(this).click(function() {
                    if($(this).is(':checked')) {
                        $(this).closest('th').addClass('is-checked');
                    } else {
                         $(this).closest('th').removeClass('is-checked');
                    }
                });
            });

            // Install plugins button display depends on user's checkbox selection
            $('.aux-plugins-step input[type=checkbox]').change(function(){
                if ($('.aux-wizard-plugins input[name="plugin[]"]').filter(':checked').length > 0) {
                    $('.install-plugins').removeClass('disabled');
                } else {
                    $('.install-plugins').addClass('disabled');
                }
            });

            // Install demos button display depends on user's checkbox selection
            $(document).on('click', '.aux-install-demos input[type=checkbox]', function(e) {
                if ($('.featherlight-content').find('input[type=checkbox]').filter(':checked').length > 0) {
                    $('.featherlight-content').find('.button-next').removeClass('aux-next-step').data('callback', 'install_demos').attr('data-callback', 'install_demos').text(aux_setup_params.makedemo_text);
                } else {
                   $('.featherlight-content').find('.button-next').addClass('aux-next-step').text(aux_setup_params.nextstep_text).data('callback', null).removeAttr('data-callback');
                }
            });

            // init plugins select all border effect
            $('#cb-select-all').click(function(e) {
                if($(this).is(':checked')) {
                    $('.aux-wizard-plugins .check-column').addClass('is-checked');
                } else {
                    $('.aux-wizard-plugins .check-column').removeClass('is-checked');
                }
            });

            // init plugins select all border effect
            $(document).on('click', '.aux-radio', function(e) {
                $(this).closest('form').find('.aux-border').removeClass('is-checked');
                $(this).parent('.aux-border').addClass('is-checked');
            });

            // Display modal demo on click button
            $(".aux-install-demo").featherlight({
                targetAttr    : 'href',
                closeOnEsc    : false,
                closeOnClick  : false,
                contentFilters: ['ajax'],
                loading       : '<svg width="90" height="30" viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg" fill="#505050"><circle cx="10" cy="10" r="10"><animate attributeName="r" from="10" to="10" begin="0s" dur="0.8s" values="10;9;10" calcMode="linear" repeatCount="indefinite" /><animate attributeName="fill-opacity" from="1" to="1" begin="0s" dur="0.8s" values="1;.5;1" calcMode="linear" repeatCount="indefinite" /></circle><circle cx="50" cy="10" r="9" fill-opacity="0.3"><animate attributeName="r" from="9" to="9" begin="0s" dur="0.8s" values="9;10;9" calcMode="linear" repeatCount="indefinite" /><animate attributeName="fill-opacity" from="0.5" to="0.5" begin="0s" dur="0.8s" values=".5;1;.5" calcMode="linear" repeatCount="indefinite" /></circle><circle cx="90" cy="10" r="10"><animate attributeName="r" from="10" to="10" begin="0s" dur="0.8s" values="10;9;10" calcMode="linear" repeatCount="indefinite" /><animate attributeName="fill-opacity" from="1" to="1" begin="0s" dur="0.8s" values="1;.5;1" calcMode="linear" repeatCount="indefinite" /></circle></svg>',
                otherClose    : '.aux-pp-close',
                afterOpen     : function(event){
                    // init PerfectScrollbar
                    if( $('.featherlight .aux-wizard-plugins').length ) {
                        var PScrollbar = new PerfectScrollbar('.featherlight .aux-wizard-plugins');
                    }
                }
            });

        }

        function pluginManager(){

            var parentElement;
            var selectedPlugins;
            var complete;
            var items_completed = 0;
            var current_item = '';
            var $current_node;
            var current_item_hash = '';

            function ajax_callback(response){
                if(typeof response === 'object' && typeof response.message !== 'undefined'){
                    $current_node.find('.column-status span').text(response.message);
                    if(typeof response.url !== 'undefined'){
                        // we have an ajax url action to perform.
                        if(response.hash == current_item_hash){
                            $current_node.find('.column-status span').text("failed");
                            find_next();
                        }else {
                            current_item_hash = response.hash;
                            jQuery.post(response.url, response, function(response2) {
                                process_current();
                                $current_node.find('.column-status span').text( response.message );
                            }).fail(ajax_callback);
                        }

                    }else if(typeof response.done !== 'undefined'){
                        // finished processing this plugin, move onto next
                        $current_node.addClass('aux-success').find('.aux-check-column').remove();
                        $current_node.find('.check-column').append('<i class="aux-success-icon auxicon-check-mark-circle-outline"></i>');
                        find_next();
                    }else{
                        // error processing this plugin
                        find_next();
                    }
                }else{
                    // error - try again with next plugin
                    $current_node.addClass('aux-error').find('.column-status span').text("Ajax Error!");
                    find_next();
                }
            }
            function process_current(){
                if(current_item){
                    var getPlugins = $(parentElement).find('.aux-wizard-plugins input[name="plugin[]"]:checked').map(function(){
                      return $(this).val();
                    }).get();
                    // query our ajax handler to get the ajax to send to TGM
                    // if we don't get a reply we can assume everything worked and continue onto the next one.
                    jQuery.post(aux_setup_params.ajaxurl, {
                        action: 'aux_setup_plugins',
                        wpnonce: aux_setup_params.wpnonce,
                        slug: current_item,
                        plugins: getPlugins,
                    }, ajax_callback).fail(ajax_callback);
                }
            }
            function find_next(){
                var do_next = false;
                if($current_node){
                    if(!$current_node.data('done_item')){
                        items_completed++;
                        $current_node.data('done_item',1);
                    }
                    $current_node.find('.spinner').css('visibility','hidden');
                }
                var $list = $(parentElement).find('.aux-plugin');
                $list.each(function(){
                    if(current_item === '' || do_next){
                        if( $(this).find('input[name="plugin[]"]').is(":checked") ) {
                            $(this).addClass('work-in-progress');
                            current_item = $(this).data('slug');
                            $current_node = $(this);
                            $current_node.find('.spinner').css('visibility','visible');
                            process_current();
                            do_next = false;
                        }
                    }else if($(this).data('slug') === current_item){
                        $(this).removeClass('work-in-progress');
                        do_next = true;
                    }
                });
                if( items_completed >= selectedPlugins ){
                    // finished all plugins!
                    complete();
                }
                // Scroll on each plugin progress in modal view
                $('.aux-modal-item .aux-wizard-plugins').each(function(){
                    $(this).scrollTo( $(this).find('.work-in-progress'), 400 );
                });
            }

            return {
                init: function(btn){
                    parentElement       = $(btn).closest('.aux-has-required-plugins');
                    $(parentElement).find('.aux-wizard-plugins').addClass('installing');
                    // Add disable class on button
                    selectedPlugins     = $(parentElement).find('.aux-plugin input[name="plugin[]"]:checked').length;
                    $(btn).text(aux_setup_params.btnworks_text).addClass('disabled');

                    // Prevent the refresh when the ajax is in progress
                    $(window).on('beforeunload', function (e){
                      return aux_setup_params.onbefore_text;
                    });
                    // Deactivate tb_remove
                    tb_control('off');
                    complete = function(){
                        // Remove installing class
                        $(parentElement).find('.aux-wizard-plugins').removeClass('installing');
                        // Disable beforeunload
                        $(window).off('beforeunload');
                        // Reactivate tb_remove
                        tb_control('on');
                        // Remove disable class from button
                        $(btn).text(aux_setup_params.activate_text);
                        // Change the text of "Skip This Step" button to "Next Step"
                        $(parentElement).find('.skip-next').text(aux_setup_params.nextstep_text);
                        // Continue loading process
                        if( $(parentElement).find('.aux-plugin').not('.aux-success').length == 0 ){
                            // Change button text and data value if all required plugins has been installed & activated
                            if( $(parentElement).hasClass('aux-modal-item') ){
                                // Goto final step
                                var final_step = new stepManager();
                                final_step.init( btn );
                            }
                        }
                    };
                    find_next();
                }
            };
        }

        function tb_control( type ){
            // Else bind/unbind the click event
            switch( type ) {
                case 'on':
                    $(document).on("keydown keypress keyup");
                    $('.aux-pp-close').removeClass('hide');
                    break;
                default:
                    $(document).off("keydown keypress keyup");
                    $('.aux-pp-close').addClass('hide');
            }
        }

        function demoImport( $step, $message = '', btn, index = '' ) {
            if ( $step ) {
                $progressLabel.text( $message );
                $.post(
                    aux_setup_params.ajaxurl,
                {
                    'action': 'import_step',
                    'step'  : $step,
                    'index' : index
                },
                function( res ) {
                    if ( res.data.next !== 'final' ) {
                        var current = res.data.hasOwnProperty('index') ? res.data.index : '';
                        demoImport( res.data.next, res.data.message, parentElement, current );
                    } else {
                        $progressLabel.text( res.data.message );
                        setTimeout(function () {
                            // Disable beforeunload
                            $(window).off('beforeunload');
                            // Reactivate tb_remove
                            tb_control('on');
                            // Goto final step
                            var final_step = new stepManager();
                            final_step.init( btn.context );
                        }, 1000);
                    }
                } );
            }
        }

        function demoManager(){

            function ajax_callback( btn ){

                demoID          = $( btn ).data('import-id');
                modalElement    = $( btn ).closest('.aux-modal-item');
                parentElement   = $( btn ).closest('.aux-setup-demo-actions');
                progressBar     = $( parentElement ).find('.aux-progress');
                progressLabel   = $( parentElement ).find('.aux-progress .aux-progress-label');
                $progressLabel  = $(progressLabel);
                nonceField      = $( btn ).data('nonce');
                demoOptions     = $( modalElement ).find( '.aux-install-demos' ).addClass('hide');
                demoProgress    = $( modalElement ).find( '.aux-install-demos-waiting' ).removeClass('hide');

                $(parentElement).find('.aux-return-back').addClass('hide');
                $(progressBar).removeClass('hide');
                $progressLabel.text( 'Getting Demo Data ...' );

                // Prevent the refresh when the ajax is in progress
                $(window).on('beforeunload', function (e){
                    return aux_setup_params.onbefore_text;
                });
                // Deactivate tb_remove
                tb_control('off');

                $('.featherlight-close-icon').addClass('disabled');

                $.ajax({
                    url : aux_setup_params.ajaxurl,
                    type : 'post',
                    data : {
                        action : 'auxin_demo_data',
                        verify : nonceField,
                        ID     : demoID,
                        options: $(demoOptions).find('.aux-import-parts').serializeArray()
                    }
                }).done(function(response) {
                    if ( response.success ) {
                        demoImport('download', 'Downloading Media ...', btn );
                    } else {
                        $progressLabel.text( response.data.message );
                        console.log( response );
                        setTimeout(function () {
                            // Disable beforeunload
                            $(window).off('beforeunload');
                            // Reactivate tb_remove
                            tb_control('on');
                            // Display demo options
                            $(demoOptions).removeClass('hide');
                            // Hide demo progress
                            $(demoProgress).addClass('hide');
                            // Hide Progressbar
                            $(progressBar).addClass('hide');
                            // Display control buttons
                            $(parentElement).find('.aux-return-back').removeClass('hide');
                        }, 2000);
                    }
                });

            }

            return {
                init: function(btn){
                    ajax_callback( btn );
                }
            };

        }

        function stepManager(){

            function ajax_callback( btn ){

                var nextStep = $( btn ).data('next-step'),
                nonceField   = $( btn ).data('step-nonce'),
                argsData     = $( btn ).data('args'),
                boxContainer = $( btn ).closest('.aux-steps-col').addClass('aux-step-in-progress');
                // Deactivate tb_remove
                tb_control('off');
                // Start Ajax Process
                $.ajax({
                    url : aux_setup_params.ajaxurl,
                    type : 'post',
                    data : {
                        action   : 'aux_step_manager',
                        next_step: nextStep,
                        nonce    : nonceField,
                        args     : argsData
                    }
                }).done(function(response) {
                    // Reactivate tb_remove
                    tb_control('on');
                    if ( response.success ) {
                        $(boxContainer).removeClass('aux-step-in-progress').html( response.data.markup );
                    } else {
                        console.log(response);
                    }
                });

            }

            return {
                init: function(btn){
                    ajax_callback( btn );
                }
            };

        }

        return {
            init: function(){
                $(window_loaded);
            },
            callback: function(func){
                console.log(func);
                console.log(this);
            }
        };
    };
})( jQuery );

/**
 * Run the scripts
 */
(function( $ ) {
    var wizard = $('.aux-welcome-page-importer').AuxWizard();
    wizard.init();
})( jQuery );
