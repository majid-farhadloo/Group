<?php
/**
 * Jetpack Compatibility File
 * See: http://jetpack.me/
 *
 * @package    auxin
 * @author     averta
 * @copyright  Copyright (c) averta co
 * @version    Release: 1.0
 * @link       http://www.averta.net
 */

/**
 * Add theme support for Infinite Scroll.
 * See: http://jetpack.me/support/infinite-scroll/
 */
function auxin_jetpack_setup() {
    add_theme_support( 'infinite-scroll', array(
        'container' => 'main',
        'footer'    => 'page',
    ) );
}

add_action( 'after_setup_theme', 'auxin_jetpack_setup' );
