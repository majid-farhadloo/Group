/**
 * AuxWizard functionality
 */
(function( $ ){
    $.fn.AuxWizard = function( element ) {
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
            // init button clicks:
            $('.button-next').click( function( e ) {
                if($(this).data('callback') && typeof callbacks[$(this).data('callback')] != 'undefined'){
                    // we have to process a callback before continue with form submission
                    callbacks[$(this).data('callback')](this);
                } else {
                    loading_content(this.href);
                }

                return false;
            });

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
            $('.aux-setup-demo-content .second-step input[type=checkbox]').change(function () {
                if ($('#TB_ajaxContent').find('input[type=checkbox]').filter(':checked').length > 0) {
                    $('#TB_ajaxContent').find('.button-next').data('callback', 'install_demos').attr('data-callback', 'install_demos').text(aux_setup_params.makedemo_text);
                } else {
                   $('#TB_ajaxContent').find('.button-next').text(aux_setup_params.nextstep_text).data('callback', null).removeAttr('data-callback');
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

            // init demo manager step
            $('.aux-demo-item').click(function() {
                $('.aux-demo-item').find('.is-active').removeClass('is-active');
                $(this).find('img').addClass('is-active');
                $('.aux-install-demo').attr("href", "#TB_inline?width=640&height=450&inlineId=" + $(this).data('demo-id') ).removeClass('disabled');
            });

            // init plugins select all border effect
            $('.aux-radio').click(function() {
                $(this).closest('form').find('.aux-border').removeClass('is-checked');
                $(this).parent('.aux-border').addClass('is-checked');
            });

            // init PerfectScrollbar
            $(".aux-install-demo").on('click', function(){
                setTimeout(function () {
                    if( $('#TB_window .aux-wizard-plugins').length ) {
                        var PScrollbar = new PerfectScrollbar('#TB_window .aux-wizard-plugins');
                    }                 
                });                     
            });            

        }

        function loading_content(pageUrl){
            // Close thickbox popup when page is loading
            if ( $('#TB_window').is(':visible') ) {
                tb_remove();
            }

            // Scroll to top
            $('html, body').animate({
                scrollTop: 0
            }, 1000);  

            // Display transparent loading block
            $('.aux-setup-content').block({
                message: null,
                overlayCSS: {
                    background: '#ffffff',
                    opacity: 0.6
                }
            });

            // Ajax page loading next/prev page load
            $('body').load(pageUrl, function(){ 
                window.history.pushState(undefined, '', pageUrl);      
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
            }

            return {
                init: function(btn){
                    parentElement       = $(btn).closest('.aux-has-required-plugins');
                    $(parentElement).find('.aux-wizard-plugins').addClass('installing');
                    // Add disable class on button
                    var oldButtonText   = $(btn).text();
                    selectedPlugins     = $(parentElement).find('.aux-plugin input[name="plugin[]"]:checked').length;
                    $(btn).text(aux_setup_params.btnworks_text).addClass('disabled');
                    // Prevent the refresh when the ajax is in progress
                    $(window).on('beforeunload', function (e){
                      return aux_setup_params.onbefore_text; 
                    });
                    // Deactivate tb_remove
                    tb_control('off');
                    complete = function(){
                        // Disable beforeunload
                        $(window).off('beforeunload');
                        // Reactivate tb_remove
                        tb_control('on');
                        // Remove disable class from button
                        $(btn).text(oldButtonText).removeClass('disabled');
                        if( $(parentElement).find('.aux-plugin').not('.aux-success').length === 0 ){
                            // Change button text and data value if all required plugins has been installed & activated
                            if( $(parentElement).hasClass('aux-modal-item') ){
                                $(btn).data('callback', 'install_demos').attr('data-callback', 'install_demos').text(aux_setup_params.makedemo_text);
                                $(btn).parent('.aux-return-back').find('.aux-alert').hide();
                                $(parentElement).find('.first-step').addClass('hide');
                                $(parentElement).find('.second-step').removeClass('hide');
                            } else {
                                loading_content(btn.href);
                            }
                        }
                    };
                    find_next();
                }
            };
        }


        function tb_control( type ){
            switch( type ) {
                case 'on':
                    $("#TB_overlay, #TB_closeWindowButton").bind( "click", tb_remove );
                    $(document).on("keydown keypress keyup");
                    break;
                default:
                    $("#TB_overlay, #TB_closeWindowButton").unbind( "click" );
                    $(document).off("keydown keypress keyup");
            }
        }

        function demoManager(){

            function ajax_callback( btn ){

                var demoID          = $( btn ).data('import-id');
                var modalElement    = $( btn ).closest('.aux-modal-item ');
                var parentElement   = $( btn ).closest('.aux-setup-demo-actions');
                var progressBar     = $( parentElement ).find('.aux-progress');
                var nonceField      = $( btn ).data('nonce');
                var demoOptions     = $( '#aux-import-data-' + demoID ).serializeArray();

                $(parentElement).find('.aux-return-back').addClass('hide');
                $(progressBar).removeClass('hide');
                // Prevent the refresh when the ajax is in progress
                $(window).on('beforeunload', function (e){
                    return aux_setup_params.onbefore_text; 
                });
                // Deactivate tb_remove
                tb_control('off');

                $.ajax({
                    url : aux_setup_params.ajaxurl,
                    type : 'post',
                    data : {
                        action : 'auxin_demo_data',
                        verify : nonceField,
                        ID     : demoID,
                        options: demoOptions
                    }   
                }).done(function(response) {
                    setTimeout(function () {
                        // Disable beforeunload
                        $(window).off('beforeunload');
                        // Reactivate tb_remove
                        tb_control('off');                
                        // Hide Progressbar
                        $(progressBar).addClass('hide');
                        // Display control buttons
                        $(parentElement).find('.aux-return-back').removeClass('hide').find('.button-next').text(aux_setup_params.nextstep_text).data('callback', null).removeAttr('data-callback');
                        // Remove checked attributes
                        $(modalElement).find('input:checkbox').removeAttr('checked');
                        // Display Message
                        if( response.success ){
                            $(parentElement).find('.aux-alert').addClass('success').html('<p>' + aux_setup_params.imported_done + '</p>').show();
                        } else {
                            $(parentElement).find('.aux-alert').html('<p>' + aux_setup_params.imported_fail + '</p>').show();
                        }
                    }, 2000);
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
    var wizard = $('.auxin-wizard-wrap').AuxWizard();
    wizard.init();
})( jQuery );