<?php

function auxin_ajax_send_feedback(){

    // skip if the form data is not receiced
    if( empty( $_POST['form'] ) ){
        wp_send_json_error( __( 'Data cannot be delivered, please try again.', 'auxin-elements' ) );
    }

    $form_data = $_POST['form'];

    // extract the form data
    $rate     = ! empty( $form_data['theme_rate'] ) ? $form_data['theme_rate'] : '';
    $feedback = ! empty( $form_data['feedback']   ) ? $form_data['feedback']   : '';
    $email    = ! empty( $form_data['email']      ) ? $form_data['email']      : '';
    $nonce    = ! empty( $form_data['_wpnonce']   ) ? $form_data['_wpnonce']   : '';

    if( ! wp_verify_nonce( $nonce, 'phlox_feedback' ) ){
        wp_send_json_error( __( 'Authorization failed!', 'auxin-elements' ) );
    }

    if( $rate ){

        global $wp_version;

        $args = array(
            'user-agent' => 'WordPress/'.$wp_version.'; '. get_home_url(),
            'timeout'    => ( ( defined('DOING_CRON') && DOING_CRON ) ? 30 : 5),
            'body'       => array(
                'cat'       => 'rating',
                'action'    => 'submit',
                'item-slug' => 'phlox',
                'rate'      => $rate
            )
        );
        // send the rating through the api
        $request = wp_remote_post( 'http://api.averta.net/envato/items/', $args );

        // if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {}

        // store the user rating on the website
        auxin_update_option( 'user_rating', $rate );

        // send the feedback via email
        $message = 'Rate: '. $rate . "\r\n" . 'Email: <' . $email . ">\r\n\r\n" . $feedback;
        wp_mail( 'feedbacks'.'@'.'averta.net', 'Feedback from phlox dashboard:', $message );

        wp_send_json_success( __( 'Sent Successfully. Thanks for your feedback!', 'auxin-elements' ) );

    } else{
        wp_send_json_error( __( 'An error occurred. Feedback could not be delivered, please try again.', 'auxin-elements' ) );
    }

}

add_action( 'wp_ajax_send_feedback', 'auxin_ajax_send_feedback' );


function auxin_ajax_filter_get_content() {

    // Check nonce
    if ( ! isset( $_POST['n'] ) || ! wp_verify_nonce( $_POST['n'], 'aux_ajax_filter_request' ) ) {
        wp_send_json_error( 'Nonce check failed!', 403 );
    }

    $num         = $_POST['num'];
    $post_type   = 'product';
    $tax         = $_POST['taxonomy'];
    $term        = $_POST['term'];
    $image_class = 'aux-img-dynamic-dropshadow';
    $width       = $_POST['width'];
    $height      = $_POST['height'];
    $order       = $_POST['order'];
    $orderby     = $_POST['orderby'];
    $size        = array( 'width' => $width, 'height' => $height );

    /*
     * The WordPress Query class.
     *
     * @link http://codex.wordpress.org/Function_Reference/WP_Query
     */
    $args = array(
        // Type & Status Parameters
        'post_type'   => $post_type,
        'post_status' => 'publish',
        // Pagination Parameters
        'posts_per_page' => $num,
        'nopaging'       => false,
        'order'          => $order,
        'orderby'        => $orderby,
    );

    if ( 'all' !== $term ) {
        // Taxonomy Parameters
        $args['tax_query'] = array(
            array(
                'taxonomy'         => $tax,
                'field'            => 'slug',
                'terms'            => $term,
                'include_children' => true,
                'operator'         => 'IN',
            )
        );
    }

    $posts = get_posts( $args );

    foreach ( $posts as $post ) {

        $image_id = get_post_thumbnail_id( $post );
        $product = wc_get_product( $post->ID );

        $post->thumb = auxin_get_the_responsive_attachment(
            $image_id,
            array(
                'quality'      => 100,
                'upscale'      => true,
                'crop'         => true,
                'add_hw'       => true, // whether add width and height attr or not
                'attr'         => array(
                    'class'                => 'auxshp-product-image auxshp-attachment ' . $image_class,
                    'data-original-width'  => $width,
                    'data-original-height' => $height,
                    'data-original-src'    => wp_get_attachment_image_src( $image_id, 'full' )[0]
                ),
                'size'         => $size,
                'image_sizes'  => 'auto',
                'srcset_sizes' => 'auto',
                'original_src' => true
            )
        );

        $post->price = $product->get_price_html();
        $post->meta = wc_get_product_category_list( $product->get_id(), ', ', '<em class="auxshp-meta-terms">', '</em>' );
        $post->badge = $product->is_on_sale() ? true : false;

        if( auxin_get_option( 'product_index_ajax_add_to_cart', '0' ) ) {
            $class = 'button aux-ajax-add-to-cart add_to_cart_button';
        }

        $post->cart = apply_filters( 'woocommerce_loop_add_to_cart_link',
                    sprintf( '<a rel="nofollow" href="%s" data-quantity="1" data-product_id="%s" data-product_sku="%s" data-verify_nonce="%s" class="%s"><i class="aux-ico auxicon-handbag"></i> %s</a>',
                        esc_url( $product->add_to_cart_url() ),
                        esc_attr( $product->get_id() ),
                        esc_attr( $product->get_sku() ),
                        esc_attr( wp_create_nonce( 'aux_add_to_cart-' . $product->get_id() ) ),
                        esc_attr( isset( $class ) ? $class : 'button add_to_cart_button' ),
                        esc_html( $product->add_to_cart_text() )
                    ),
                $product );
    }

    wp_send_json_success( $posts );

}

add_action( 'wp_ajax_filter_get_content', 'auxin_ajax_filter_get_content' );
add_action( 'wp_ajax_noprive_filter_get_content', 'auxin_ajax_filter_get_content' );
