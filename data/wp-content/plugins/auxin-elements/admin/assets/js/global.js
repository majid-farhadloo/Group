(function($, window, document, undefined){
    "use strict";

    $('.aux-welcome-close-notice').on('click', function(event){
        event.preventDefault();
        var $this  = $(this),
            _id    = $this.data('id');

        jQuery.post(
            ajaxurl,
            {
                action    : 'aux_welcome_dismiss_notice', // the handler
                auxnonce  : aux_setup_params.wpnonce,
                _id       : _id
            },
            function(res){
                if( ! res.success ){
                    alert( res.data.message );
                } else {
                    $this.closest('.aux-welcome-notice-wrapper').hide();
                }
            }
        );

    });
})(jQuery, window, document);
