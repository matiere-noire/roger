<?php
/**
 * WooCommerce integration.
 *
 * This file integrates the theme with WooCommerce.
 *
 * @package   Mythic
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

namespace Mythic;

use function Hybrid\Template\path;

/**
 * Adds theme support for the WooCommerce plugin.
 *
 * @since  1.0.0
 * @access public
 * @return void
 */
add_action( 'after_setup_theme', function() {

    add_theme_support( 'woocommerce' );
} );

/**
 * This overrides the top-level WooCommerce templates that would normally go in
 * the theme root. By default, we're looking for a `resources/views/woocommerce.php`
 * template, which falls back to `resources/views/index.php`.
 *
 * @since  1.0.0
 * @access public
 * @param  array  $files
 * @return array
 */
add_filter( 'woocommerce_template_loader_files', function( $files ) {

    return [
        path( 'woocommerce.php' ),
        path( 'index.php' )
    ];

}, PHP_INT_MAX );

/**
 * Filters the path to the `woocommerce` template parts folder.  This filter
 * moves that folder to `resources/views/woocommerce`.
 *
 * @since  1.0.0
 * @access public
 * @param  string  $path
 * @return string
 */
add_filter( 'woocommerce_template_path', function( $path ) {

    return path( $path );
} );