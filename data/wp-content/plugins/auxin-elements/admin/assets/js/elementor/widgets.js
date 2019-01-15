/**
 * Init Elements in Elementor Frontend
 *
 */
;(function($, window, document, undefined){
    "use strict";

    $(window).on('elementor/frontend/init', function (){
        // Before after element
        elementorFrontend.hooks.addAction( 'frontend/element_ready/aux-before-after.default', $.fn.AuxinBeforeAfterInit );

        elementorFrontend.hooks.addAction( 'frontend/element_ready/aux-gallery.default', $.fn.AuxinTriggerResize );
        elementorFrontend.hooks.addAction( 'frontend/element_ready/aux-gallery.default', $.fn.AuxinIsotopeImageLayoutsInit );

        elementorFrontend.hooks.addAction( 'frontend/element_ready/aux_accordion.default', $.fn.AuxinAccordionInit );

        elementorFrontend.hooks.addAction( 'frontend/element_ready/aux_tabs.default', $.fn.AuxinLiveTabsInit );


        elementorFrontend.hooks.addAction( 'frontend/element_ready/aux_video.default', function( $scope ){ $scope.find('video').mediaelementplayer(); } );
        elementorFrontend.hooks.addAction( 'frontend/element_ready/aux_audio.default', function( $scope ){ $scope.find('audio').mediaelementplayer(); } );


        elementorFrontend.hooks.addAction( 'frontend/element_ready/aux_recent_portfolios_grid.default',
            function( $scope ){ $.fn.AuxinIsotopeLayoutInit( $('body') ); }
        );

        elementorFrontend.hooks.addAction( 'frontend/element_ready/aux_recent_portfolios_masonry.default',
            function( $scope ){ $.fn.AuxinIsotopeLayoutInit( $('body') ); }
        );

        elementorFrontend.hooks.addAction( 'frontend/element_ready/aux_recent_portfolios_tile.default',
            function( $scope ){ $.fn.AuxinIsotopeTilesInit( $('body') ); }
        );

        elementorFrontend.hooks.addAction( 'frontend/element_ready/aux_recent_portfolios_grid_carousel.default',
            function( $scope ){ $.fn.AuxinCarouselInit( $('body') ); }
        );

        elementorFrontend.hooks.addAction( 'frontend/element_ready/aux_recent_news_grid.default',
            function( $scope ){ $.fn.AuxinCarouselInit( $('body') ); }
        );

        elementorFrontend.hooks.addAction( 'frontend/element_ready/global', 
            function( $scope ) { $.fn.AuxinPageCoverAnimationInit( $scope );}
        
        );

    });

})(jQuery, window, document);

