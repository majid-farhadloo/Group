<?php
/**
 *
 * 
 * @package    Auxin
 * @license    LICENSE.txt
 * @author     
 * @link       http://phlox.pro/
 * @copyright  (c) 2010-2018 
 */

/**
 * Whether a plugin is active or not
 *
 * @param  string $plugin_basename  plugin directory name and mail file address
 * @return bool                     True if plugin is active and FALSE otherwise
 */
if( ! function_exists( 'auxin_is_plugin_active' ) ){
    function auxin_is_plugin_active( $plugin_basename ){
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        return is_plugin_active( $plugin_basename );
    }
}


/**
 * Retrieves the markup for footer logo element
 *
 * @param  array  $args The properties fort this element
 *
 * @return string       The markup for logo element
 */
function auxin_get_footer_logo_block( $args = array() ){

    $defaults = array(
        'css_class'      => '',
        'middle'         => true
    );

    $args = wp_parse_args( $args, $defaults );

ob_start();
?>
    <div class="aux-logo <?php echo $args['css_class']; ?>">
        <a class="aux-logo-anchor <?php echo ($args['middle'] ? 'aux-middle' : ''); ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
            <?php echo _auxin_get_footer_logo_image(); ?>
        </a>
    </div><!-- end logo aux-fold -->

<?php
    return ob_get_clean();
}


/**
 * Retrieves the footer logo image tag
 *
 * @return string        The markup for logo image
 */
function _auxin_get_footer_logo_image(){
    global $post;

    if( ! $img_id = auxin_get_post_meta( $post, 'page_secondary_logo_image' ) ){
        $img_id = auxin_get_option( 'site_secondary_logo_image');
    }

    return wp_get_attachment_image( $img_id, 'full', false, array(
        'class'    => 'aux-logo-image aux-logo-dark',
        'itemprop' => 'logo',
        'alt'      => esc_attr( get_bloginfo( 'name', 'display' ) )
    ) );

}

//// is absolute url ///////////////////////////////////////////////////////////////////

/**
 * Whether it's absolute url or not
 *
 * @param  string $url  The URL
 * @return bool   TRUE if the URL is absolute
 */

if( ! function_exists( "auxin_is_absolute_url" ) ){

    function auxin_is_absolute_url( $url ){
        return preg_match( "~^(?:f|ht)tps?://~i", $url );
    }

}


//// create absolute url if the url is relative ////////////////////////////////////////

/**
 * Print absolute URL for media file even if the URL is relative
 *
 * @param  string $url  The link to media file
 * @return void
 */
function auxin_the_absolute_image_url( $url ){
    echo auxin_get_the_absolute_image_url( $url );
}

    /**
     * Get absolute URL for media file event if the URL is relative
     *
     * @param  string $url  The link to media file
     * @return string   The absolute URL to media file
     */
    if( ! function_exists( 'auxin_get_the_absolute_image_url' ) ){

        function auxin_get_the_absolute_image_url( $url ){
            if( ! isset( $url ) || empty( $url ) )    return '';

            if( auxin_is_absolute_url( $url ) || auxin_contains_upload_dir( $url ) ) return $url;

            $uploads = wp_get_upload_dir();
            return trailingslashit( $uploads['baseurl'] ) . $url;
        }

    }

//// get all registerd siderbar ids ///////////////////////////////////////////////////

if( ! function_exists( 'auxin_get_all_sidebar_ids' ) ){

    function auxin_get_all_sidebar_ids(){
        $sidebars = get_theme_mod( 'auxin_sidebars');
        $output   = array();

        if( isset( $auxin_sidebars ) && ! empty( $auxin_sidebars ) ){
            foreach( $sidebars as $key => $value ) {
                $output[] = THEME_ID .'-'. strtolower( str_replace(' ', '-', $value) );
            }
        }
        return $output;
    }

}

//// remove all auto generated p tags from shortcode content //////////////////////////

if( ! function_exists( "auxin_do_cleanup_shortcode" ) ){

    function auxin_do_cleanup_shortcode( $content ) {

        /* Parse nested shortcodes and add formatting. */
        $content = trim( wpautop( do_shortcode( $content ) ) );

        /* Remove any instances of '<p>' '</p>'. */
        $content = auxin_cleanup_content( $content );

        return $content;
    }

}


//// remove all p tags from string ////////////////////////////////////////////////////

if( ! function_exists( 'auxin_cleanup_content' ) ){

    function auxin_cleanup_content( $content ) {
        /* Remove any instances of '<p>' '</p>'. */
        return str_replace( array('<p>','</p>'), array('','') , $content );
    }

}

/**
 * Generates and may print a notice for missing required plugins in elementor
 *
 * @param  array $args
 * @return string       May return the notice markup
 */
function auxin_elementor_plugin_missing_notice( $args ){
    // default params
    $defaults = array(
        'plugin_name' => '',
        'echo'        => true
    );
    $args = wp_parse_args( $args, $defaults );

    ob_start();
    ?>
    <div class="elementor-alert elementor-alert-danger" role="alert">
        <span class="elementor-alert-title">
            <?php echo sprintf( esc_html__( '"%s" Plugin is Not Activated!', 'auxin-elements' ), $args['plugin_name'] ); ?>
        </span>
        <span class="elementor-alert-description">
            <?php esc_html_e( 'In order to use this element, you need to install and activate this plugin.', 'auxin-elements' ); ?>
        </span>
    </div>
    <?php
    $notice =  ob_get_clean();

    if( $args['echo'] ){
        echo $notice;
    } else {
        return $notice;
    }
}

/*-----------------------------------------------------------------------------------*/
/*  A function that makes excluding featured image, post formats and post ids simpler
/*-----------------------------------------------------------------------------------*/

if( ! function_exists( 'auxin_parse_query_args' ) ){


    /**
     * A function that makes excluding featured image, post formats and post ids simpler
     *
     * @param  array $args  The list of options and query params
     * @return array        The parsed args as array
     */
    function auxin_parse_query_args( $args ){

        $defaults = array(
            'post_type'               => 'post',
            'post_status'             => 'publish',
            'posts_per_page'          => -1,
            'ignore_sticky_posts'     => 1,

            'posts__in'               => '', // display only these post IDs. array or string comma separated
            'posts__not_in'           => '', // exclude these post IDs from result. array or string comma separated

            'include_posts__in'       => '', // include these post IDs in result too. array or string comma separated
            'exclude_without_media'   => 0,  // exclude the posts without featured image
            'exclude_post_formats_in' => '', // exclude the post with these post formats
        );

        // parse and merge the passed args
        $parsed_args = wp_parse_args( $args, $defaults );


        // Exclude post formats ----------------------------------------------------

        // exclude post formats if specified
        if( ! empty( $parsed_args['exclude_post_formats_in'] ) ){

            // generate post-format terms (i.e post-format-aside)
            $post_format_terms = array();
            foreach ( $parsed_args['exclude_post_formats_in'] as $_post_format ) {
                $post_format_terms[] = 'post-format-' . $_post_format;
            }

            // exclude the redundant taxonomies (post-format)
            $parsed_args['tax_query'] = array(
                array(
                    'taxonomy' => 'post_format',
                    'field'    => 'slug',
                    'terms'    => $post_format_terms,
                    'operator' => 'NOT IN'
                )
            );

        }

        // Exclude posts without featured image ------------------------------------

        // whether to exclude posts without featured-image or not
        if( $parsed_args['exclude_without_media'] ){
            $parsed_args['meta_query'] = array(
                array(
                    'key'       => '_thumbnail_id',
                    'value'     => '',
                    'compare'   => '!='
                )
            );
        }


        // Include, Exclude & Replace Post IDs -------------------------------------

        // get the list of custom post ids to display
        $only_posts    = $parsed_args['posts__in'] ? wp_parse_id_list( $parsed_args['posts__in'] ) : array();

        // get the list of custom post ids to include
        $include_posts = $parsed_args['include_posts__in'] ? wp_parse_id_list( $parsed_args['include_posts__in'] ) : array();

        // get the list of custom post ids to exclude
        $exclude_posts = $parsed_args['posts__not_in'] ? wp_parse_id_list( $parsed_args['posts__not_in'] ) : array();


        // if both of post__in and post__not_in options are defined, we have to array_diff the arrays,
        // because we cannot use post__in & post__not_in at the time in WordPress

        if( $only_posts ){

            if( $exclude_posts ){
                // remove the excluded post ids from post__in list
                if( $only_posts = array_filter( array_diff( $only_posts, $exclude_posts ) ) ){
                    $parsed_args['post__in'] = $only_posts;
                }
            } else {
                $parsed_args['post__in'] = $only_posts;
            }
            $parsed_args['posts__not_in'] = '';

        // if include_posts__in was specified
        } elseif( $include_posts ){

            $extra_query_args = $parsed_args;

            // query the posts other than the ones we intend to include
            $include_and_exclude_posts = array_unique( array_filter( array_merge( $include_posts, $exclude_posts ) ) );
            // remove the excluded post ids from include_posts__in list
            $the_posts_to_include      = array_diff( $include_posts, $exclude_posts );

            $extra_query_args['fields']       = 'ids'; // just get IDs, for better performance
            $extra_query_args['post__in']     = '';    // forget post__in in this pre query
            $extra_query_args['post__not_in'] = $include_and_exclude_posts; // dont select our include adn exclude posts in this query, we will prepend them later

            // get the post ids other than our include and exclude list
            $other_post_ids  = get_posts( $extra_query_args );

            // prepend the included post ids
            $merged_post_ids = array_merge( $the_posts_to_include, $other_post_ids );

            // change the main query base on previous result
            $parsed_args = array(
                'post__in'            => $merged_post_ids,
                'orderby'             => 'post__in', // query base on the order of defined post ids
                'posts_per_page'      => $parsed_args['posts_per_page'],
                'ignore_sticky_posts' => 1
            );

        // if just "post__not_in" option is specified
        } elseif( $exclude_posts ) {
            $parsed_args['post__not_in'] = $exclude_posts;
        }

        // Remove extra query args -------------------------------------------------

        unset( $parsed_args['include_posts__in'] );
        unset( $parsed_args['exclude_without_media'] );
        unset( $parsed_args['exclude_post_formats_in'] );

        return $parsed_args;
    }

}



/*-----------------------------------------------------------------------------------*/
/*  Extract only raw text - remove all special charecters, html tags, js and css codes
/*-----------------------------------------------------------------------------------*/

function auxin_extract_text( $content = null ) {
    // decode encoded html tags
    $content = htmlspecialchars_decode($content);
    // remove script tag and inline js content
    $content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);
    // remove style tag and inline css content
    $content = preg_replace('#<style(.*?)>(.*?)</style>#is'  , '', $content);
    // remove iframe content
    $content = preg_replace('#<if'.'rame(.*?)>(.*?)</iframe>#is', '', $content);
    // remove extra white spaces
    $content = preg_replace('/[\s]+/', ' ', $content );
    // strip html tags and escape special charecters
    $content = esc_attr(strip_tags($content));
    // remove double space
    $content = preg_replace('/\s{3,}/',' ', $content );
    return $content;
}

/*-----------------------------------------------------------------------------------*/
/*  Big Grid layout patterns
/*-----------------------------------------------------------------------------------*/

function auxin_get_grid_pattern( $pattern, $index, $column_media_width ) {

    switch ( $pattern ) {
        case 'pattern-1':
            return auxin_big_grid_pattern_1( $index, $column_media_width );
        break;
        case 'pattern-2';
            return auxin_big_grid_pattern_2( $index, $column_media_width );
        break;
        case 'pattern-3';
            return auxin_big_grid_pattern_3( $index, $column_media_width );
        break;
        case 'pattern-4';
            return auxin_big_grid_pattern_4( $index, $column_media_width );
        break;
        case 'pattern-5';
            return auxin_big_grid_pattern_5( $index, $column_media_width );
        break;
        case 'pattern-6';
            return auxin_big_grid_pattern_6( $index, $column_media_width );
        break;
        case 'pattern-7';
            return auxin_big_grid_pattern_7( $index, $column_media_width );
        break;
        default:
            return auxin_get_big_grid_pattern( 'default', $index, $column_media_width );
        break;
    }

}

/**
 * Defines the size of post tile based on pattern and given index
 * @param  string   $pattern
 * @param  int      $index
 * @return Array    classname, image size
 */
function auxin_get_big_grid_pattern( $pattern = 'default', $index, $column_media_width ) {

    $div_index = $index % 12;
    $return_value = array();

    switch ( $div_index ) {

        // large squares
        case 0:
        case 7:
            $return_value = array(
                'classname'  => 'aux-big-grid-6-6 aux-t-big-grid-12-8 aux-m-big-grid-12-8',
                'size' => array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                'image_sizes' => array(
                    array( 'min' => '' , 'max' => '992px', 'width' => '100vw' ),
                    array( 'min' => '' , 'max' => '',      'width' => 2 * $column_media_width . 'px' )
                ),
                'srcset_sizes'  => array(
                    array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                    array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width ),
                    array( 'width' => 8 * $column_media_width, 'height' => 8 * $column_media_width )
                )
            );
            break;

        // full width
        case 5:
        case 11:
            $return_value = array(
                'classname'  => 'aux-big-grid-12-6',
                'size' => array( 'width' =>     $column_media_width, 'height' =>     $column_media_width * 0.5 ),
                'image_sizes' => array(

                ),
                'srcset_sizes'  => array(
                    array( 'width' =>     $column_media_width, 'height' =>     $column_media_width * 0.5),
                    array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width * 0.5),
                    array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width * 0.5 )
                )
            );
            break;

        // small squares
        default:
            $return_value = array(
                'classname'  => 'aux-big-grid-3-3 aux-t-big-grid-6-5 aux-m-big-grid-12-8',
                'size' => array( 'width' =>     $column_media_width, 'height' =>     $column_media_width ),
                'image_sizes' => array(
                    array( 'min' => '',  'max' => '767px', 'width' => '100vw' ),
                    array( 'min' => '' , 'max' => '992px', 'width' => '50vw' ),
                    array( 'min' => '' , 'max' => '',      'width' => $column_media_width . 'px' )
                ),
                'srcset_sizes'  => array(
                    array( 'width' =>     $column_media_width, 'height' =>     $column_media_width ),
                    array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                    array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width )
                )
            );

    }

    return $return_value;
}


function auxin_big_grid_pattern_1( $index, $column_media_width ) {

    $return_value = array();

    $return_value = array(
        'classname'  => 'aux-big-grid-4-4 aux-t-big-grid-6-6 aux-m-big-grid-12-6',
        'size' => array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
        'image_sizes' => array(
            array( 'min' => '' , 'max' => '992px', 'width' => '100vw' ),
            array( 'min' => '' , 'max' => '
                ',      'width' => 2 * $column_media_width . 'px' )
        ),
        'srcset_sizes'  => array(
            array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
            array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width ),
            array( 'width' => 8 * $column_media_width, 'height' => 8 * $column_media_width )
        )
    );

    return $return_value;

}


function auxin_big_grid_pattern_2( $index, $column_media_width ) {

    $div_index = $index % 5;
    $return_value = array();

    switch ( $div_index ) {

        // large squares
        case 1:
            $return_value = array(
                'classname'  => 'aux-big-grid-6-6 aux-t-big-grid-12-6 aux-m-big-grid-12-6',
                'size' => array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                'image_sizes' => array(
                    array( 'min' => '' , 'max' => '992px', 'width' => '100vw' ),
                    array( 'min' => '' , 'max' => '',      'width' => 2 * $column_media_width . 'px' )
                ),
                'srcset_sizes'  => array(
                    array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                    array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width ),
                    array( 'width' => 8 * $column_media_width, 'height' => 8 * $column_media_width )
                )
            );
            break;

        // small squares
        default:
            $return_value = array(
                'classname'  => 'aux-big-grid-3-3 aux-t-big-grid-6-6 aux-m-big-grid-12-6',
                'size' => array( 'width' =>     $column_media_width, 'height' =>     $column_media_width ),
                'image_sizes' => array(
                    array( 'min' => '',  'max' => '767px', 'width' => '100vw' ),
                    array( 'min' => '' , 'max' => '992px', 'width' => '50vw' ),
                    array( 'min' => '' , 'max' => '',      'width' => $column_media_width . 'px' )
                ),
                'srcset_sizes'  => array(
                    array( 'width' =>     $column_media_width, 'height' =>     $column_media_width ),
                    array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                    array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width )
                )
            );

    }

    return $return_value;

}


function auxin_big_grid_pattern_3( $index, $column_media_width ) {

    $div_index = $index % 3;
    $return_value = array();

    switch ( $div_index ) {

        case 0:
            $return_value = array(
                'classname'  => 'aux-big-grid-6-6 aux-t-big-grid-12-6 aux-m-big-grid-12-6',
                'size' => array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                'image_sizes' => array(
                    array( 'min' => '' , 'max' => '992px', 'width' => '100vw' ),
                    array( 'min' => '' , 'max' => '',      'width' => 2 * $column_media_width . 'px' )
                ),
                'srcset_sizes'  => array(
                    array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                    array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width ),
                    array( 'width' => 8 * $column_media_width, 'height' => 8 * $column_media_width )
                )
            );
            break;

        default:
            $return_value = array(
                'classname'  => 'aux-big-grid-6-3 aux-t-big-grid-12-6 aux-m-big-grid-12-6',
                'size' => array( 'width' =>     $column_media_width, 'height' =>     $column_media_width ),
                'image_sizes' => array(
                    array( 'min' => '',  'max' => '767px', 'width' => '100vw' ),
                    array( 'min' => '' , 'max' => '992px', 'width' => '50vw' ),
                    array( 'min' => '' , 'max' => '',      'width' => $column_media_width . 'px' )
                ),
                'srcset_sizes'  => array(
                    array( 'width' =>     $column_media_width, 'height' =>     $column_media_width ),
                    array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                    array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width )
                )
            );

    }

    return $return_value;

}


function auxin_big_grid_pattern_4( $index, $column_media_width ) {

    $div_index = $index % 4;
    $return_value = array();

    switch ( $div_index ) {

        // large squares
        case 0:
            $return_value = array(
                'classname'  => 'aux-big-grid-6-6 aux-t-big-grid-12-6 aux-m-big-grid-12-6',
                'size' => array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                'image_sizes' => array(
                    array( 'min' => '' , 'max' => '992px', 'width' => '100vw' ),
                    array( 'min' => '' , 'max' => '',      'width' => 2 * $column_media_width . 'px' )
                ),
                'srcset_sizes'  => array(
                    array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                    array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width ),
                    array( 'width' => 8 * $column_media_width, 'height' => 8 * $column_media_width )
                )
            );
            break;

        case 1:
            $return_value = array(
                'classname'  => 'aux-big-grid-6-3 aux-t-big-grid-12-6 aux-m-big-grid-12-6',
                'size' => array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width ),
                'image_sizes' => array(
                    array( 'min' => '' , 'max' => '992px', 'width' => '100vw' ),
                    array( 'min' => '' , 'max' => '',      'width' => 2 * $column_media_width . 'px' )
                ),
                'srcset_sizes'  => array(
                    array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                    array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width ),
                    array( 'width' => 8 * $column_media_width, 'height' => 8 * $column_media_width )
                )
            );
            break;

        // small squares
        default:
            $return_value = array(
                'classname'  => 'aux-big-grid-3-3 aux-t-big-grid-6-6 aux-m-big-grid-12-6',
                'size' => array( 'width' =>     $column_media_width, 'height' =>     $column_media_width ),
                'image_sizes' => array(
                    array( 'min' => '',  'max' => '767px', 'width' => '100vw' ),
                    array( 'min' => '' , 'max' => '992px', 'width' => '50vw' ),
                    array( 'min' => '' , 'max' => '',      'width' => $column_media_width . 'px' )
                ),
                'srcset_sizes'  => array(
                    array( 'width' =>     $column_media_width, 'height' =>     $column_media_width ),
                    array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                    array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width )
                )
            );

    }

    return $return_value;
}


function auxin_big_grid_pattern_5( $index, $column_media_width ) {
    $div_index = $index % 6;
    $return_value = array();

    switch ( $div_index ) {

        // large squares
        case 0:
        case 1:
            $return_value = array(
                'classname'  => 'aux-big-grid-6-6 aux-t-big-grid-12-6 aux-m-big-grid-12-6',
                'size' => array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                'image_sizes' => array(
                    array( 'min' => '' , 'max' => '992px', 'width' => '100vw' ),
                    array( 'min' => '' , 'max' => '',      'width' => 2 * $column_media_width . 'px' )
                ),
                'srcset_sizes'  => array(
                    array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                    array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width ),
                    array( 'width' => 8 * $column_media_width, 'height' => 8 * $column_media_width )
                )
            );
            break;

        // small squares
        default:
            $return_value = array(
                'classname'  => 'aux-big-grid-3-3 aux-t-big-grid-6-6 aux-m-big-grid-12-6',
                'size' => array( 'width' =>     $column_media_width, 'height' =>     $column_media_width ),
                'image_sizes' => array(
                    array( 'min' => '',  'max' => '767px', 'width' => '100vw' ),
                    array( 'min' => '' , 'max' => '992px', 'width' => '50vw' ),
                    array( 'min' => '' , 'max' => '',      'width' => $column_media_width . 'px' )
                ),
                'srcset_sizes'  => array(
                    array( 'width' =>     $column_media_width, 'height' =>     $column_media_width ),
                    array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                    array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width )
                )
            );

    }

    return $return_value;

}


function auxin_big_grid_pattern_6( $index, $column_media_width ) {
    $return_value = array();

    $return_value = array(
        'classname'  => 'aux-big-grid-lg-4-4 aux-t-big-grid-lg-6-6 aux-m-big-grid-lg-12-6',
        'size' => array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
        'image_sizes' => array(
            array( 'min' => '' , 'max' => '992px', 'width' => '100vw' ),
            array( 'min' => '' , 'max' => '
                ',      'width' => 2 * $column_media_width . 'px' )
        ),
        'srcset_sizes'  => array(
            array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
            array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width ),
            array( 'width' => 8 * $column_media_width, 'height' => 8 * $column_media_width )
        )
    );

    return $return_value;

}


function auxin_big_grid_pattern_7( $index, $column_media_width ) {
    $return_value = array();

    $return_value = array(
        'classname'  => 'aux-big-grid-sg-4-4 aux-t-big-grid-sg-6-6 aux-m-big-grid-sg-12-6',
        'size' => array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
        'image_sizes' => array(
            array( 'min' => '' , 'max' => '992px', 'width' => '100vw' ),
            array( 'min' => '' , 'max' => '
                ',      'width' => 2 * $column_media_width . 'px' )
        ),
        'srcset_sizes'  => array(
            array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
            array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width ),
            array( 'width' => 8 * $column_media_width, 'height' => 8 * $column_media_width )
        )
    );

    return $return_value;

}


/*-----------------------------------------------------------------------------------*/
/*  Tiles layout patterns
/*-----------------------------------------------------------------------------------*/


/**
 * Defines the size of post tile based on pattern and given index
 * @param  string   $pattern
 * @param  int      $index
 * @return Array    classname, image size
 */
function auxin_get_tile_pattern( $pattern, $index, $column_media_width ) {

    switch ( $pattern ) {

        case 'pattern-1':
            $pattern = auxin_tile_pattern_1( $index, $column_media_width );
        break;
        case 'pattern-2':
            $pattern = auxin_tile_pattern_2( $index, $column_media_width );
        break;
        case 'pattern-3':
            $pattern = auxin_tile_pattern_3( $index, $column_media_width );
        break;
        case 'pattern-4':
            $pattern = auxin_tile_pattern_4( $index, $column_media_width );
        break;
        case 'pattern-5':
            $pattern = auxin_tile_pattern_5( $index, $column_media_width );
        break;
        case 'pattern-6':
            $pattern = auxin_tile_pattern_6( $index, $column_media_width );
        break;
        case 'pattern-7':
            $pattern = auxin_tile_pattern_7( $index, $column_media_width );
        break;
        case 'pattern-8':
            $pattern = auxin_tile_pattern_8( $index, $column_media_width );
        break;
        default:
            $pattern = auxin_tile_pattern_default( $index, $column_media_width );
        break;
    }

    if( empty( $pattern['image_sizes'] ) ){
        $pattern['image_sizes'] = 'auto';
    }
    if( empty( $pattern['srcset_sizes'] ) ){
        $pattern['srcset_sizes'] = 'auto';
    }

    return $pattern;
}

function auxin_tile_pattern_default( $index, $column_media_width ) {

    $div_index = $index % 12;
    $return_value = array();

    switch ( $div_index ) {

        // large squares
        case 0:
        case 7:
            $return_value = array(
                'classname'  => 'aux-tile-6-6 aux-t-tile-6-6 aux-m-tile-12-12',
                'size' => array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                'image_sizes' => array(
                    array( 'min' => '' , 'max' => '992px', 'width' => '100vw' ),
                    array( 'min' => '' , 'max' => '',      'width' => 2 * $column_media_width . 'px' )
                ),
                'srcset_sizes'  => array(
                    array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                    array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width ),
                    array( 'width' => 8 * $column_media_width, 'height' => 8 * $column_media_width )
                )
            );
            break;

        // full width
        case 5:
        case 11:
            $return_value = array(
                'classname'  => 'aux-tile-12-6',
                'size' => array( 'width' =>     $column_media_width, 'height' =>     $column_media_width * 0.5 ),
                'image_sizes' => array(

                ),
                'srcset_sizes'  => array(
                    array( 'width' =>     $column_media_width, 'height' =>     $column_media_width * 0.5),
                    array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width * 0.5),
                    array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width * 0.5 )
                )
            );
            break;

        // small squares
        default:
            $return_value = array(
                'classname'  => 'aux-tile-3-3 aux-t-tile-3-3 aux-m-tile-6-6',
                'size' => array( 'width' =>     $column_media_width, 'height' =>     $column_media_width ),
                'image_sizes' => array(
                    array( 'min' => '',  'max' => '767px', 'width' => '100vw' ),
                    array( 'min' => '' , 'max' => '992px', 'width' => '50vw' ),
                    array( 'min' => '' , 'max' => '',      'width' => $column_media_width . 'px' )
                ),
                'srcset_sizes'  => array(
                    array( 'width' =>     $column_media_width, 'height' =>     $column_media_width ),
                    array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
                    array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width )
                )
            );

    }

    return $return_value;

}

function auxin_tile_pattern_1( $index, $column_media_width ) {

    return array(
        'classname'    => 'aux-tile-4-4 aux-t-tile-6-6 aux-m-tile-12-6',
        'size'         => array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width )
    );
}

function auxin_tile_pattern_2( $index, $column_media_width ) {

    $div_index = $index % 5;
    $return_value = array();

    switch ( $div_index ) {

        // large squares
        case 1:
            $return_value = array(
                'classname'    => 'aux-tile-6-6 aux-t-tile-12-6 aux-m-tile-12-6',
                'size'         => array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width )
            );
            break;

        // small squares
        default:
            $return_value = array(
                'classname'    => 'aux-tile-3-3 aux-t-tile-6-6 aux-m-tile-12-6',
                'size'         => array( 'width' => $column_media_width, 'height' => $column_media_width )
            );

    }

    return $return_value;

}

function auxin_tile_pattern_3( $index, $column_media_width ) {

    $div_index = $index % 3;
    $return_value = array();

    switch ( $div_index ) {

        case 0:
            $return_value = array(
                'classname'  => 'aux-tile-6-6 aux-t-tile-12-6 aux-m-tile-12-6',
                'size' => array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width )
            );
            break;

        default:
            $return_value = array(
                'classname'  => 'aux-tile-6-3 aux-t-tile-12-6 aux-m-tile-12-6',
                'size' => array( 'width' =>     $column_media_width, 'height' =>     $column_media_width )
            );

    }

    return $return_value;

}


function auxin_tile_pattern_4( $index, $column_media_width ) {

    $div_index = $index % 4;
    $return_value = array();

    switch ( $div_index ) {

        // large squares
        case 0:
            $return_value = array(
                'classname'  => 'aux-tile-6-6 aux-t-tile-12-6 aux-m-tile-12-12',
                'size' => array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
            );
            break;

        case 1:
            $return_value = array(
                'classname'  => 'aux-tile-6-3 aux-t-tile-12-6 aux-m-tile-12-6',
                'size' => array( 'width' => 4 * $column_media_width, 'height' => 4 * $column_media_width ),
            );
            break;

        // small squares
        default:
            $return_value = array(
                'classname'  => 'aux-tile-3-3 aux-t-tile-6-6 aux-m-tile-6-6',
                'size' => array( 'width' =>     $column_media_width, 'height' =>     $column_media_width ),
            );

    }

    return $return_value;
}


function auxin_tile_pattern_5( $index, $column_media_width ) {

    $div_index = $index % 6;
    $return_value = array();

    switch ( $div_index ) {

        // large squares
        case 0:
        case 1:
            $return_value = array(
                'classname'  => 'aux-tile-6-6 aux-t-tile-12-6 aux-m-tile-12-6',
                'size' => array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
            );
            break;

        // small squares
        default:
            $return_value = array(
                'classname'  => 'aux-tile-3-3 aux-t-tile-6-6 aux-m-tile-12-6',
                'size' => array( 'width' =>     $column_media_width, 'height' =>     $column_media_width ),
            );

    }

    return $return_value;

}


function auxin_tile_pattern_6( $index, $column_media_width ) {

    $return_value = array();

    $return_value = array(
        'classname'  => 'aux-tile-lg-4-4 aux-t-tile-lg-6-6 aux-m-tile-lg-12-6',
        'size' => array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
    );

    return $return_value;

}


function auxin_tile_pattern_7( $index, $column_media_width ) {
    $return_value = array();

    $return_value = array(
        'classname'  => 'aux-tile-sg-4-4 aux-t-tile-sg-6-6 aux-m-tile-sg-12-6',
        'size' => array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
    );

    return $return_value;

}
function auxin_tile_pattern_8( $index, $column_media_width ) {

    $return_value = array();

    $return_value = array(
        'classname'  => 'aux-tile-6-6 aux-t-tile-6-6 aux-m-tile-12-6',
        'size' => array( 'width' => 2 * $column_media_width, 'height' => 2 * $column_media_width ),
    );

    return $return_value;

}
/*-----------------------------------------------------------------------------------*/
/*  Retrieves the provider from an embed code link
/*-----------------------------------------------------------------------------------*/


function auxin_extract_embed_provider_name( $src ){
    require_once( ABSPATH . WPINC . '/class-oembed.php' );
    $oembed   = _wp_oembed_get_object();
    if( ! $provider = $oembed->get_provider( $src ) ){
        return '';
    }

    $provider_info = parse_url( $provider );
    if( $provider_info['host'] ){
        $host_parts = explode( '.', $provider_info['host'] );
        $host_parts_num = count( $host_parts );
        if( $host_parts_num > 1 ){
            return $host_parts[ $host_parts_num -2 ];
        }
    }

    return '';
}



//// Store content in file  ////////////////////////////////////////////////////////

/**
 * Creates and stores content in a file (#admin)
 *
 * @param  string $content    The content for writing in the file
 * @param  string $file_location  The address that we plan to create the file in.
 *
 * @return boolean            Returns true if the file is created and updated successfully, false on failure
 */
function auxin_put_contents( $content, $file_location = '', $chmode = 0644 ){

    if( empty( $file_location ) ){
        return false;
    }

    /**
     * Initialize the WP_Filesystem
     */
    global $wp_filesystem;
    if ( empty( $wp_filesystem ) ) {
        require_once ( ABSPATH.'/wp-admin/includes/file.php' );
        WP_Filesystem();
    }

    // Write the content, if possible
    if ( wp_mkdir_p( dirname( $file_location ) ) && ! $wp_filesystem->put_contents( $file_location, $content, $chmode ) ) {
        // If writing the content in the file was not successful
        return false;
    } else {
        return true;
    }

}


//// Stores content in custom js file   /////////////////////////////////////////////


/**
 * Stores JavaScript content in custom js file (#admin)
 *
 * @return boolean            Returns true if the file is created and updated successfully, false on failure
 */
function auxin_save_custom_js(){

    $js_string = get_theme_mod( 'custom_js_string' );

    ob_start();
    ?>
/*
===============================================================
 #CUSTOM JavaScript
- Please do not edit this file. This file is generated from admin area.
- Every changes here will be overwritten by theme
===============================================================*/
    <?php

    $js_string = ob_get_clean() . $js_string;


    if ( auxin_put_contents_dir( $js_string, 'custom.js' ) ) {
        set_theme_mod( 'custom_js_ver', rand(10, 99)/10 ); // disable inline css output
        set_theme_mod( 'use_inline_custom_js' , false ); // disable inline css output

        return true;
    } else {
        // if the directory is not writable, try inline css fallback
        set_theme_mod( 'use_inline_custom_js' , true ); // save css rules as option to print as inline css

        return false;
    }
}


/**
 * Removes an specific content from custom js file (#admin)
 *
 * @param  string $ref_name   The reference name for referring a content in $js_array array
 *
 * @return boolean            Returns true if the content was removed successfully, false on failure
 */
function auxin_remove_custom_js( $ref_name = '' ){

    // retrieve the js array list
    $js_array = get_theme_mod( 'custom_js_array', array() );

    if( isset( $js_array[ $ref_name ] ) ){
        unset( $js_array[ $ref_name ] );

        set_theme_mod( 'custom_js_array'  , $js_array  );
        // update the file content too
        auxin_add_custom_js();
    }
}


/**
 * Retrieves the list of custom scripts generated with themes options
 *
 * @param  string $exclude_ref_names   The reference names that are expected to be excluded from result
 *
 * @return boolean    The list of custom scripts generated with themes options
 */
function auxin_get_custom_js_array( $exclude_ref_names = array() ){
    // retrieve the css array list
    $js_array = get_theme_mod( 'custom_js_array', array() );

    return array_diff_key( $js_array, array_flip( (array) $exclude_ref_names ) );
}



//// Stores content in custom css file  /////////////////////////////////////////////


/**
 * Stores css content in custom css file (#admin)
 *
 * @return boolean            Returns true if the file is created and updated successfully, false on failure
 */
function auxin_save_custom_css(){

    $css_string = get_theme_mod( 'custom_css_string' );

    ob_start();
    ?>
/*
===============================================================
 #CUSTOM CSS
- Please do not edit this file. This file is generated from admin area.
- Every changes here will be overwritten by theme
===============================================================*/
    <?php

    $css_string = ob_get_clean() . $css_string;

    if ( auxin_put_contents_dir( $css_string, 'custom.css' ) ) {

        set_theme_mod( 'custom_css_ver', rand(10, 99)/10 );
        set_theme_mod( 'use_inline_custom_css' , false ); // disable inline css output

        return true;
    // if the directory is not writable, try inline css fallback
    } else {
        set_theme_mod( 'use_inline_custom_css' , true ); // save css rules as option to print as inline css
        return false;
    }
}


/**
 * Removes an specific content from custom css file (#admin)
 *
 * @param  string $ref_name   The reference name for referring a content in $css_array array
 *
 * @return boolean            Returns true if the content was removed successfully, false on failure
 */
function auxin_remove_custom_css( $ref_name = '' ){

    // retrieve the css array list
    $css_array = get_theme_mod( 'custom_css_array', array() );

    if( isset( $css_array[ $ref_name ] ) ){
        unset( $css_array[ $ref_name ] );

        set_theme_mod( 'custom_css_array', $css_array  );
        // update the file content too
        auxin_add_custom_css();
    }
}


/**
 * Retrieves the list of custom styles generated with themes options
 *
 * @param  string $exclude_ref_names   The reference names that are expected to be excluded from result
 *
 * @return boolean    The list of custom styles generated with themes options
 */
function auxin_get_custom_css_array( $exclude_ref_names = array() ){
    // retrieve the css array list
    $css_array = get_theme_mod( 'custom_css_array', array() );

    return array_diff_key( $css_array, array_flip( (array) $exclude_ref_names ) );
}


/**
 * Retrieves the custom styles generated with themes options
 *
 * @param  string $exclude_ref_names   The css reference names that are expected to be excluded from result
 *
 * @return boolean    The custom styles generated with themes options
 */
function auxin_get_custom_css_string( $exclude_ref_names = array() ){
    // retrieve the css array list
    $css_array  = auxin_get_custom_css_array( (array) $exclude_ref_names );
    $css_string = '';

    $sep_comment = apply_filters( 'auxin_custom_css_sep_comment', "/* %s \n=========================*/\n" );

    // Convert the contents in array to string
    if( is_array( $css_array ) ){
        foreach ( $css_array as $node_ref => $node_content ) {
            if( ! is_numeric( $node_ref) ){
                $css_string .= sprintf( $sep_comment, str_replace( '_', '-', $node_ref ) );
            }
            $css_string .= "$node_content\n";
        }
    }

    // Remove <style> if user used them in the style content
    return str_replace( array( "<style>", "</style>" ), array('', ''), $css_string );
}


/**
 * Extract numbers from string
 *
 * @param  string $str     The string that contains numbers
 * @param  int    $default The number which should be returned if no number found in the string
 * @return int             The extracted numbers
 */
function auxin_get_numerics( $str, $default = null ) {
    if( empty( $str ) ){
        return is_numeric( $default ) ? $default: '';
    }
    preg_match('/\d+/', $str, $matches);
    return $matches[0];
}


/**
 * Prints JS variable
 *
 * @param  string $object_name   The object or variable name
 * @param  array  $object_value  The object value
 */
function auxin_print_script_object( $object_name, $object_value = array() ){

    if( empty( $object_name ) ){
        _doing_it_wrong( __FUNCTION__, 'The object name cannot be empty' );
        return;
    }
    // remove unespected chars
    $object_name = trim( $object_name, '.' );

    if( false !== strpos( $object_name, '.') ){
        $script = sprintf( 'auxinNS("%1$s"); %1$s=%2$s;', esc_js( $object_name ), wp_json_encode( $object_value ) );
    } else {
        $script = sprintf( 'var %1$s=%2$s;', esc_js( $object_name ), wp_json_encode( $object_value ) );
    }

    echo $script ? '<script>'. $script .'</script>' : '';
}

/*-----------------------------------------------------------------------------------*/
/*  Returns post type menu name
/*-----------------------------------------------------------------------------------*/

if( ! function_exists( 'auxin_get_post_type_name' ) ){

    // returns post type menu name
    function auxin_get_post_type_name( $post_type = '' ){
        $post_type     = empty( $post_type ) ? get_post_type() : $post_type;
        $post_type_obj = get_post_type_object( $post_type );

        return apply_filters( 'auxin_get_post_type_name', $post_type_obj->labels->menu_name, $post_type );
    }

}

/**
 * Generates and retrieves a random token
 *
 * @param  integer $length The token length
 * @return strinf          The random token
 */
function auxin_random_token( $length = 32 ){
    $length = ! is_numeric( $length ) ? 4 : $length;
    $length = $length < 1 ? 32 : $length;

    if ( function_exists('random_bytes') ) {
        return bin2hex(random_bytes( $length ));
    }
    if (function_exists('mcrypt_create_iv')) {
        return bin2hex(mcrypt_create_iv( $length, MCRYPT_DEV_URANDOM ));
    }
    if ( function_exists('openssl_random_pseudo_bytes') ) {
        return bin2hex(openssl_random_pseudo_bytes( $length ));
    }
}


/*-----------------------------------------------------------------------------------*/
/*  A function to generate header and footer for all widgets
/*-----------------------------------------------------------------------------------*/

function auxin_get_widget_scafold( $atts, $default_atts, $shortcode_content = '' ){

    $result = array(
        'parsed_atts'   => '',
        'widget_info'   => '',
        'widget_header' => '',
        'widget_title'  => '',
        'widget_footer' => '',
        'ajax_data'     => ''
    );

    // ----
    if( ! isset( $default_atts['extra_classes'] ) ){
        $default_atts['extra_classes'] = '';
    }
    if( ! isset( $default_atts['custom_el_id'] ) ){
        $default_atts['custom_el_id'] = '';
    }
    if( ! isset( $default_atts['content'] ) ){
        $default_atts['content'] = '';
    }
    if( empty( $default_atts['universal_id'] ) ){
        $default_atts['universal_id'] = 'au'.auxin_random_token(4);
    }
    if( ! isset( $default_atts['skip_wrappers'] ) ){
        $default_atts['skip_wrappers'] = false;
    }
    if( ! isset( $default_atts['loadmore_type'] ) ){
        $default_atts['loadmore_type'] = '';
    }
    if( ! isset( $default_atts['base'] ) ){
        $default_atts['base'] = '';
    }
    if( ! isset( $default_atts['content_width'] ) ){
        if( ! did_action( 'wp' ) && function_exists( 'auxin_set_content_width' ) ){
            $default_atts['content_width'] = auxin_set_content_width();
        } else {
            global $aux_content_width;
            $default_atts['content_width'] = $aux_content_width;
        }
    }

    // animation options
    if( ! isset( $default_atts['inview_transition'] ) ){
        $default_atts['inview_transition'] = 'none';
    }
    if( ! isset( $default_atts['inview_duration'] ) ){
        $default_atts['inview_duration'] = '';
    }
    if( ! isset( $default_atts['inview_delay'] ) ){
        $default_atts['inview_delay'] = '';
    }
    if( ! isset( $default_atts['inview_repeat'] ) ){
        $default_atts['inview_repeat'] = 'no';
    }
    if( ! isset( $default_atts['inview_offset'] ) ){
        $default_atts['inview_offset'] = '';
    }

    // What called the widget fallback function
    if( ! isset( $default_atts['called_from'] ) ){
        $default_atts['called_from'] = '';
    }

    // prevent nested query while placing a recent posts widget in the same post
    if( isset( $atts['exclude'] ) && ! empty( $atts['exclude'] ) ){
        global $post;
        if( ! empty( $post->ID ) ){
            $atts['exclude'] .= ',' . $post->ID;
        }
    }

    // Widget general info
    $before_widget = $after_widget  = '';
    $before_title  = $after_title   = '';

    // If widget info is passed, extract them in above variables
    if( isset( $atts['widget_info'] ) ){
        $result['widget_info'] = $atts['widget_info'];
        extract( $atts['widget_info'] );
    }
    // CSS class names for section -------------

    // The default CSS classes for widget container
    // Note that 'widget-container' should be in all element
    $_css_classes = array( 'widget-container' );

    // Parse shortcode attributes
    $parsed_atts = shortcode_atts( $default_atts, $atts, __FUNCTION__ );

    if( empty( $parsed_atts['content'] ) ){
        $parsed_atts['content'] = $shortcode_content;
    }
    if( empty( $parsed_atts['loadmore_per_page'] ) ){
        $parsed_atts['loadmore_per_page'] = ! empty( $parsed_atts['num'] ) ? $parsed_atts['num'] : 12;
    }

    $result['parsed_atts'] = $parsed_atts;

    // make the result params filterable prior to generating markup variables
    $result = apply_filters( 'auxin_pre_widget_scafold_params', $result, $atts, $default_atts, $shortcode_content );

    if( $result['parsed_atts']['skip_wrappers'] ){
        return $result;
    }

    if( ! empty( $result['parsed_atts']['loadmore_type'] ) ){

        if( empty( $result['parsed_atts']["base"] ) ){
            _doing_it_wrong( __FUNCTION__, 'For using ajax load more feature, "base" parameter in element default attributes is required.' );
        }

        // Enqueue wp-mediaelement
        wp_enqueue_style ( 'wp-mediaelement' );
        wp_enqueue_script( 'wp-mediaelement' );

        $ajax_args = $result['parsed_atts'];

        // remove redundant ajax args
        unset( $ajax_args['base'] );
        unset( $ajax_args['base_class'] );
        unset( $ajax_args['use_wp_query'] );

        // force the element not to render wrappers for ajax handler
        $ajax_args['skip_wrappers'] = true;

        $result['ajax_data'] = array(
            'nonce'   => wp_create_nonce('auxin_front_load_more'),
            'args'    => $ajax_args,
            'handler' => $result['parsed_atts']["base"],
            'per_page'=> $parsed_atts['loadmore_per_page']
        );

        $_css_classes[] = 'aux-ajax-type-' . $result['parsed_atts']['loadmore_type'];
        if ( 'infinite-scroll' === $result['parsed_atts']['loadmore_type'] ) {
            $_css_classes[] = 'aux-ajax-type-scroll';
        }
    }

    // Defining extra class names --------------

    // Add extra class names to class list here - widget-{element_name}
    $_css_classes[] = $result['parsed_atts']['base_class'];

    $_css_classes[] = 'aux-parent-' . $result['parsed_atts']['universal_id'];

    $_widget_attrs  = '';
    $_widget_styles = '';

    if( ! empty( $result['parsed_atts']['inview_transition'] ) && 'none' !== $result['parsed_atts']['inview_transition'] ){
        $_css_classes[] = 'aux-appear-watch';
        $_css_classes[] = esc_attr( $result['parsed_atts']['inview_transition'] );

        if( ! empty( $result['parsed_atts']['inview_duration'] ) && 600 != $result['parsed_atts']['inview_duration'] ){
            $_widget_styles .= 'animation-duration:'  . esc_attr( rtrim( $result['parsed_atts']['inview_duration'], 'ms') ) . 'ms;';
            $_widget_styles .= 'transition-duration:' . esc_attr( rtrim( $result['parsed_atts']['inview_duration'], 'ms') ) . 'ms;';
        }
        if( ! empty( $result['parsed_atts']['inview_delay'] ) ){
            $_widget_styles .= 'animation-delay:'  . esc_attr( rtrim( $result['parsed_atts']['inview_delay'], 'ms') ) . 'ms;';
            $_widget_styles .= 'transition-delay:' . esc_attr( rtrim( $result['parsed_atts']['inview_delay'], 'ms') ) . 'ms;';
        }
        if( ! empty( $result['parsed_atts']['inview_repeat'] ) && 'no' !== $result['parsed_atts']['inview_repeat'] ){
            $_css_classes[] = 'aux-appear-repeat';
        }
        if( ! empty( $result['parsed_atts']['inview_offset'] ) ){
            $offset = $result['parsed_atts']['inview_offset'];
            if( false === strpos( $offset, '%' ) ){
                $offset = trim( $offset, 'px' ) . 'px';
            }
            $_widget_attrs .= 'data-offset="' . esc_attr( $offset ) . '" ';
        }
    }

    $_widget_classes = auxin_merge_css_classes( $_css_classes, $result['parsed_atts']['extra_classes'] );
    $_widget_classes = esc_attr( trim( join( ' ', array_unique( $_widget_classes ) ) ) );

    // Generate the opening tags for widget or shortcode element
    if( $before_widget ){

        $result['widget_header'] .= str_replace(
            array( 'class="', '>','<div'),
            array( 'class="'.$_widget_classes.' ', ' style="'. $_widget_styles .'" '. $_widget_attrs .' >', '<section' ),
            $before_widget
        );
    } elseif ( !empty($result['parsed_atts']['custom_el_id']) ){
        $result['widget_header'] .= sprintf('<section id="%s" class="%s" style="%s" %s>', $result['parsed_atts']['custom_el_id'], $_widget_classes, $_widget_styles, $_widget_attrs );
    } else {
        $result['widget_header'] .= sprintf('<section class="%s" style="%s" %s>', $_widget_classes, $_widget_styles, $_widget_attrs );
    }

    // Generate the title for widget or shortcode element
    if( ! empty( $result['parsed_atts']['title'] ) ){
        if( $before_title ){
            $result['widget_title'] .= $before_title . $result['parsed_atts']['title'] . $after_title;
        } elseif( ! empty( $result['parsed_atts']['title'] ) ){
            $result['widget_title'] .= '<h3 class="widget-title">'. $result['parsed_atts']['title'] .'</h3>';
        }
    }

    // Generate the close tags for widget or shortcode element
    if( $after_widget ){
        // fix for the difference in end tag in siteorigin page builder
        $result['widget_footer'] .= str_replace( '</div', '</section', $after_widget );
    } else {
        $result['widget_footer'] .= '</section><!-- widget-container -->';
    }

    // Enable filtering the result variable
    $result =  apply_filters( 'auxin_widget_scafold_params', $result, $atts, $default_atts, $shortcode_content );

    // Prints the javascript variable if load more is enabled
    // We can modify the ajax args using "auxin_widget_scafold_params" filter
    if( ! empty( $result['parsed_atts']['loadmore_type'] ) ){
        // echo js dependencies
        auxin_print_script_object( "auxin.content.loadmore." . $result['parsed_atts']['universal_id'], $result['ajax_data'] );
    }

    return $result;
}


/*----------------------------------------------------------------------------*/
/*  Retrieves remote data
/*----------------------------------------------------------------------------*/

/**
 * Retrieves a URL using the HTTP POST method
 *
 * @return mixed|boolean    The body content
 */
function auxin_remote_post( $url, $args = array() ) {
    $request = wp_remote_post( $url, $args );

    if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
        return $request['body'];
    }
    return false;
}

/**
 * Retrieves a URL using the HTTP GET method
 *
 * @return mixed|boolean    The body content
 */
function auxin_remote_get( $url, $args ) {
    $request = wp_remote_get( $url, $args );

    if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
        return $request['body'];
    }
    return false;
}

/*----------------------------------------------------------------------------*/
/*  Removes a class method from a specified filter hook.
/*----------------------------------------------------------------------------*/

if( ! function_exists( 'auxin_remove_filter_from_class' ) ){

    /**
     * Removes a class method from a specified filter hook.
     *
     * @param  string  $hook_name   The filter hook to which the function to be removed is hooked
     * @param  string  $class_name  The name of class which its method should be removed.
     * @param  string  $method_name The name of the method which should be removed.
     * @param  integer $priority    Optional. The priority of the function. Default 10.
     * @return bool                 Whether the function existed before it was removed.
     */
    function auxin_remove_filter_from_class( $hook_name = '', $class_name ='', $method_name = '', $priority = 10 ) {
        global $wp_filter;

        // Take only filters on right hook name and priority
        if ( !isset($wp_filter[$hook_name][$priority]) || !is_array($wp_filter[$hook_name][$priority]) )
            return false;

        // Loop on filters registered
        foreach( (array) $wp_filter[$hook_name][$priority] as $unique_id => $filter_array ) {
            // Test if filter is an array ! (always for class/method)
            if ( isset($filter_array['function']) && is_array($filter_array['function']) ) {
                // Test if object is a class, class and method is equal to param !
                if ( is_object($filter_array['function'][0]) && get_class($filter_array['function'][0]) && get_class($filter_array['function'][0]) == $class_name && $filter_array['function'][1] == $method_name ) {
                    unset($wp_filter[$hook_name][$priority][$unique_id]);
                }
            }

        }

        return false;
    }

}


if( ! function_exists( 'auxin_remove_action_from_class' ) ){

    /**
     * Removes a class method from a specified filter hook.
     *
     * @param  string  $hook_name   The filter hook to which the function to be removed is hooked
     * @param  string  $class_name  The name of class which its method should be removed.
     * @param  string  $method_name The name of the method which should be removed.
     * @param  integer $priority    Optional. The priority of the function. Default 10.
     * @return bool                 Whether the function existed before it was removed.
     */
    function auxin_remove_action_from_class( $hook_name = '', $class_name ='', $method_name = '', $priority = 10 ) {
        global $wp_action;

        // Take only filters on right hook name and priority
        if ( !isset($wp_action[$hook_name][$priority]) || !is_array($wp_action[$hook_name][$priority]) )
            return false;

        // Loop on filters registered
        foreach( (array) $wp_action[$hook_name][$priority] as $unique_id => $filter_array ) {
            // Test if filter is an array ! (always for class/method)
            if ( isset($filter_array['function']) && is_array($filter_array['function']) ) {
                // Test if object is a class, class and method is equal to param !
                if ( is_object($filter_array['function'][0]) && get_class($filter_array['function'][0]) && get_class($filter_array['function'][0]) == $class_name && $filter_array['function'][1] == $method_name ) {
                    unset($wp_action[$hook_name][$priority][$unique_id]);
                }
            }

        }

        return false;
    }

}



/*-----------------------------------------------------------------------------------*/
/*  Auxin Load More
/*-----------------------------------------------------------------------------------*/

/**
 * A callback for Auxin Load More functionality
 *
 * @return string
 */
function auxin_get_load_more_controller( $type, $label = 'text' ) {
    // Return null when loadmore type is empty
    if ( empty( $type ) ) return;
    // Check load more type
    if ( $type == "next-prev" ) {
        return '
        <div class="aux-ajax-controller">
            <nav class="aux-next-prev-posts nav-skin-minimal">
                <section class="aux-load-next-prev hidden np-prev-section">
                    <div class="np-arrow">
                        <div class="aux-arrow-nav aux-hover-slide aux-round aux-outline aux-medium">
                            <span class="aux-overlay"></span>
                            <span class="aux-svg-arrow aux-medium-left"></span>
                            <span class="aux-hover-arrow aux-svg-arrow aux-medium-left aux-white"></span>
                        </div>
                    </div>
                    <p class="np-nav-text">'.__('Previous', 'auxin-elements').'</p>
                    <h4 class="np-title">'.__('page', 'auxin-elements').'</h4>
                </section>
                <section class="aux-load-next-prev np-next-section">
                    <div class="np-arrow">
                        <div class="aux-arrow-nav aux-hover-slide aux-round aux-outline aux-medium">
                            <span class="aux-overlay"></span>
                            <span class="aux-svg-arrow aux-medium-right"></span>
                            <span class="aux-hover-arrow aux-svg-arrow aux-medium-right aux-white"></span>
                        </div>
                    </div>
                    <p class="np-nav-text">'.__('Next', 'auxin-elements').'</p>
                    <h4 class="np-title">'.__('Page', 'auxin-elements').'</h4>
                </section>

            </nav>
        </div>';

    } elseif ( $type == "scroll" || $type == "infinite-scroll" || $type == "next" ) {

        switch ( $label ) {
            case 'text':
                $label = __('LOAD MORE', 'auxin-elements');
                break;

            case 'arrow':
                $label = '<span class="aux-svg-arrow aux-h-large-down"></span>';
                break;

            case 'text-arrow':
                $label = '<span class="aux-svg-arrow aux-small-down"></span>' . __('MORE', 'auxin-elements');
                break;
        }

        return '
        <div class="aux-ajax-controller">
            <nav class="aux-load-more center">
              <svg class="aux-circle" width="100%" height="100%" viewBox="0 0 102 102">
                <circle class="aux-progress-bg" r="50" cx="51" cy="51" fill="none" transform="rotate(-90 51 51)"></circle>
                <circle class="aux-progress" r="50" cx="51" cy="51" fill="none" transform="rotate(-90 51 51)"></circle>
              </svg>
              <div class="aux-label-text">'.apply_filters( 'auxin_loadmore_label', $label ).'</div>
              <span class="aux-loading-label">'.__('LOADING', 'auxin-elements').'</span>
            </nav>
        </div>';

    }
}


/*-----------------------------------------------------------------------------------*/
/*  A function to get the custom style of Google maps
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'auxin_get_gmap_style' ) ) {

    function auxin_get_gmap_style () {

        $styler = '[{"featureType":"landscape.man_made","elementType":"geometry.fill","stylers":[{"color":"#e9e5dc"}]},{"featureType":"landscape.natural","elementType":"geometry.fill","stylers":[{"visibility":"on"},{"color":"#b8cb93"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi.business","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"poi.medical","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"poi.park","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#ccdca1"}]},{"featureType":"poi.sports_complex","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"road","elementType":"geometry.fill","stylers":[{"hue":"#ff0000"},{"saturation":-100},{"lightness":99}]},{"featureType":"road","elementType":"geometry.stroke","stylers":[{"color":"#808080"},{"lightness":54},{"visibility":"off"}]},{"featureType":"road","elementType":"labels.text.fill","stylers":[{"color":"#767676"}]},{"featureType":"road","elementType":"labels.text.stroke","stylers":[{"color":"#ffffff"}]},{"featureType":"water","elementType":"all","stylers":[{"saturation":43},{"lightness":-11},{"color":"#89cada"}]}]';

        return apply_filters( 'auxin_gmap_style', $styler );
    }
}

/**
 * Retrieves markup for a header button
 *
 * @param  $button_id_num  The ID of the header button (1 or 2)
 * @return string
 */
function auxin_get_header_button( $button_id_num = 1 ){

    if( empty( $button_id_num ) || ! is_numeric( $button_id_num ) ){
        _doing_it_wrong( __FUNCTION__, "A numeric button id is required." );
    }
    if( ! auxin_get_option( "site_header_show_btn" . $button_id_num ) ){
        return '';
    }

    $btn_args = apply_filters( 'auxin_header_button_args', array(
        'label'         => auxin_get_option( 'site_header_btn'. $button_id_num .'_label' ),
        'size'          => auxin_get_option( 'site_header_btn'. $button_id_num .'_size', 'large' ),
        'border'        => auxin_get_option( 'site_header_btn'. $button_id_num .'_shape'  ),
        'style'         => auxin_get_option( 'site_header_btn'. $button_id_num .'_style'  ),
        'dark'          => auxin_get_option( 'site_header_btn'. $button_id_num .'_darken', 0 ),
        'icon'          => auxin_get_option( 'site_header_btn'. $button_id_num .'_icon'  ),
        'icon_align'    => auxin_get_option( 'site_header_btn'. $button_id_num .'_icon_align' ),
        'color_name'    => auxin_get_option( 'site_header_btn'. $button_id_num .'_color_name' ),
        'link'          => auxin_get_option( 'site_header_btn'. $button_id_num .'_link', "#" ),
        'target'        => auxin_get_option( 'site_header_btn'. $button_id_num .'_target' ),
        'btn_attrs'     => sprintf( 'data-colorname-default{%s};data-colorname-sticky{%s}',
            auxin_get_option( 'site_header_btn'. $button_id_num .'_color_name' ),
            auxin_get_option( 'site_header_btn'. $button_id_num .'_color_name_on_sticky' )
        ),
        'uppercase'     => '0',
        'extra_classes' => 'aux-ac-btn'. $button_id_num
    ), $button_id_num );

    return auxin_widget_button_callback( $btn_args );
}


/**
 * Returns the second custom logo, linked to home.
 *
 * @param  integer $blog_id  The site id on multisite
 * @return string            Markup for second custom logo.
 */
function auxin_get_custom_logo2( $blog_id = 0, $args = array() ) {

    $defaults = array(
        'anchor_extra_classes' => 'aux-middle aux-logo-sticky',
        'image_extra_classes'  => 'aux-logo-light'
    );

    $args = wp_parse_args( $args, $defaults );

    $html = '';
    $switched_blog = false;

    if ( is_multisite() && ! empty( $blog_id ) && (int) $blog_id !== get_current_blog_id() ) {
        switch_to_blog( $blog_id );
        $switched_blog = true;
    }

    // make the secondary logo image modifiable
    $custom_logo_id = apply_filters( 'auxin_secondary_logo_id', auxin_get_option('custom_logo2'), $args, $blog_id );

    // We have a logo. Logo is go.
    if ( $custom_logo_id ) {
        $custom_logo_attr = array(
            'class'    => 'custom-logo aux-logo-image aux-logo-image2 '. $args['image_extra_classes'],
            'itemprop' => 'logo',
        );

        /*
         * If the logo alt attribute is empty, get the site title and explicitly
         * pass it to the attributes used by wp_get_attachment_image().
         */
        $image_alt = get_post_meta( $custom_logo_id, '_wp_attachment_image_alt', true );
        if ( empty( $image_alt ) ) {
            $custom_logo_attr['alt'] = get_bloginfo( 'name', 'display' );
        }

        /*
         * If the alt attribute is not empty, there's no need to explicitly pass
         * it because wp_get_attachment_image() already adds the alt attribute.
         */
        $html = sprintf( '<a href="%1$s" class="custom-logo-link aux-logo-anchor aux-logo-anchor2 aux-has-logo %2$s" rel="home" itemprop="url">%3$s</a>',
            esc_url( home_url( '/' ) ),
            esc_attr( $args['anchor_extra_classes'] ),
            wp_get_attachment_image( $custom_logo_id, 'full', false, $custom_logo_attr )
        );
    }

    if ( $switched_blog ) {
        restore_current_blog();
    }

    /**
     * Filters the custom logo output.
     *
     * @param string $html    Custom logo HTML output.
     * @param int    $blog_id ID of the blog to get the custom logo for.
     */
    return apply_filters( 'auxin_get_custom_logo2', $html, $blog_id );
}


/**
 * Determines whether the site has the second custom logo.
 *
 * @param  integer $blog_id  The site id on multisite
 * @return bool              Whether the site has the custom logo or not.
 */
function auxin_has_custom_logo2( $blog_id = 0 ) {
    $switched_blog = false;

    if ( is_multisite() && ! empty( $blog_id ) && (int) $blog_id !== get_current_blog_id() ) {
        switch_to_blog( $blog_id );
        $switched_blog = true;
    }

    $custom_logo_id = get_theme_mod( 'custom_logo2' );

    if ( $switched_blog ) {
        restore_current_blog();
    }

    return (bool) $custom_logo_id;
}


/**
 * Removes all generate images from uploads directory
 *
 * @return void
 */
function auxin_remove_all_generate_images( $remove = true ){
    if( is_multisite() ){
        return;
    }
    $upload_dir = wp_get_upload_dir();

    $all_images = auxin_find_all_files( $upload_dir['basedir'] );
    $generated_images = array();

    foreach( $all_images as $key => $file ) {
        if( 1 == preg_match("#-(\d+)x(\d+).(png|jpg|bmp|gif)$#", $file) ) {
            $generated_images[] = $file;
            if( $remove ){
                unlink( $file );
            }
        }
    }
    echo count( $all_images ) . " / " . count( $generated_images );
}

/**
 * Find all files within a directory
 *
 * @param  string $dir The directory which we intend to serach in
 * @return array       List of files
 */
function auxin_find_all_files( $dir, $recursive = true ){
    $root   = scandir( $dir );
    $result = array();

    foreach( $root as $file ){
        if( $file === '.' || $file === '..') {
            continue;
        }
        if( is_file( "$dir/$file" ) ){
            $result[] = "$dir/$file";
            continue;
        } elseif( $recursive && is_dir( "$dir/$file" ) ){
            $sub_dir_files = auxin_find_all_files( "$dir/$file" );
            $result = array_merge( $result, $sub_dir_files );
        }
    }
    return $result;
}


/**
 * Flatten an array
 * Used for flatten grouped data in SiteOrigin widgets
 * @param  array $keys Keys that we need to extract and merge with main array
 * @param  array $array The array that we need to flatten
 * @return array
 */
function auxin_flatten_array( $keys = array(), $arr = array() ) {
    foreach ( $keys as $key ) {
        if ( isset( $arr[$key] ) ) {
            $temp = $arr[$key];
            unset($arr[$key]);
            $arr = array_merge( $arr, $temp );
        }
    }
    return $arr;
}


/**
 * Get All Pages Id and Title
 * Used for Customizer Options for 404 ,Maintance and Coming soon Section
 * @return array
 */
function auxin_get_all_pages() {

    $pages = array();
    $args = array(
        'post_type'   => 'page',
        'post_status' => 'publish',
    );

    $pages['default'] = __( 'Theme Default', 'auxin-elements' ) ; // Default Page For 404, Maintance , And Coming Soon

    $pages_array = get_posts( $args );

    foreach ( $pages_array as $page ) {
        $pages[ $page->ID ] = $page->post_title ;
    }

    return $pages;
}

/**
 * Get the size of numeric controls
 *
 * @param  string|array  $value  The controller value
 * @return int                   The size
 */
function auxin_get_control_size( $value ){
    return isset( $value['size'] ) ? $value['size'] : $value;
}

/**
 * Change search uery based on options
 *
 * @param object $query A WP_Query object
 * @return object $query
 */
function auxin_custom_search_results( $query ) {

    if ( ! is_admin() && $query->is_main_query() ) {
        if ($query->is_search) {

            $all_post_types = get_post_types( array( 'public' => true, 'exclude_from_search' => false ) );

            $excluded_post_types = (array) auxin_get_option( 'auxin_search_exclude_post_types', array() );

            $post_types = array_diff( $all_post_types , $excluded_post_types );

            $posts_in = auxin_get_option( 'auxin_search_pinned_contents', '' );

            if ( auxin_get_option( 'auxin_search_exclude_no_media' ) ) {
                $query->set( 'meta_query', array(array('key' => '_thumbnail_id')) );
            }

        }
    }
    return $query;

}

add_filter( 'pre_get_posts', 'auxin_custom_search_results' );

/**
 * Page Cover Markup
 */

function auxin_cover() {
    global $post;

    ob_start();
    if ( auxin_is_true( auxin_get_post_meta( $post, 'display_page_header_cover', false ) ) ) {
        $cover_image = auxin_get_post_meta( $post, 'page_header_cover_image', '' );
        $image = auxin_get_the_responsive_attachment( $cover_image,
            array(
                'quality'         => 100,
                'preloadable'     => false,
                'preload_preview' => false,
                'size'            => 'full',
                'crop'            => true,
                'add_hw'          => true,
                'attr'            => array( 'data-object-fit' => 'cover', 'class' => 'auxin-page-cover-image' )
            )
        );
        $cover_title = auxin_get_post_meta( $post, 'page_header_cover_title', '' );
        $discover_text = auxin_get_post_meta( $post, 'page_header_discover_text', '' );
        ?>
        <div class="aux-page-cover-wrapper">
            <?php echo $image ;?>
            <?php _e( $cover_title, 'auxin-elements' ); ?>
            <div class="aux-page-cover-footer">
                <div class="aux-page-cover-footer-text">
                    <a href="#" title="<?php echo $discover_text ?>"><?php echo $discover_text ?></a>
                </div>
            </div>
        </div>
    <?php }

    echo ob_get_clean();

}

add_action( 'auxin_after_body_open', 'auxin_cover' );
