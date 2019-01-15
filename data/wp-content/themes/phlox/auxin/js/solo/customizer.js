( function( $ ) {
    var api = wp.customize;

    // Site title and description.
    api( 'blogname', function( value ) {
        value.bind( function( to ) {
            $( '.site-title a' ).html( to );
        });
    });

    api( 'blogdescription', function( value ) {
        value.bind( function( to ) {
            $( '.site-description' ).html( to );
        });
    });

    api( 'auxin_user_custom_css', function( value ) {
        // Remove the custom css file from the page (since the custom css will be added to page instantly)
        $('#auxin-custom-css').remove();

        var styleDomId = 'auxin-customizer-css-auxin_user_custom_css';
        style = $( '#' + styleDomId );
        if ( ! style.length ) {
            style = $( 'head' ).append( '<style type=\"text/css\" id=\"' + styleDomId + '\" />' )
                               .find( '#' + styleDomId );
        }
        // append the custom styles on start if this is the setting for user_custom_css
        style.html(  wp.customize.settings.values.auxin_user_custom_css );
    });

} )( jQuery );




function inArray( needle, haystack ) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return true;
    }
    return false;
}

function auxinIsTrue( variable ) {
    if( typeof(variable) === "boolean" ){
        return variable;
    }

    if( typeof(variable) === "string" ){
        variable = variable.toLowerCase();
        if( inArray( variable, ['yes', 'on', 'true', 'checked'] ) ){
            return true;
        }
    }
    // if is nummeric
    if ( !isNaN(parseFloat(variable)) && isFinite(variable) ) {
        return Boolean(variable);
    }

    return false;
}


/**
 * jQuery alterClass plugin             | Copyright (c) 2011 Pete Boere (the-echoplex.net)
 * Free under terms of the MIT license  | https://gist.github.com/peteboere/1517285
 */
!function(s){s.fn.alterClass=function(a,e){var r=this;if(-1===a.indexOf("*"))return r.removeClass(a),e?r.addClass(e):r;var n=new RegExp("\\s"+a.replace(/\*/g,"[A-Za-z0-9-_]+").split(" ").join("\\s|\\s")+"\\s","g");return r.each(function(a,e){for(var r=" "+e.className+" ";n.test(r);)r=r.replace(n," ");e.className=s.trim(r)}),e?r.addClass(e):r};s.fn.auxToggle=function(a){this.toggle(auxinIsTrue(a));}}(jQuery);
