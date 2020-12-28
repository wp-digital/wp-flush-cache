<?php
/**
 * Plugin Name: Flush Cache Buttons
 * Description: Helps to flush different types of cache.
 * Plugin URI: https://github.com/innocode-digital/wp-google-datastudio
 * Version: 2.3.0
 * Author: Oleksandr Strikha, Innocode
 * Author URI: https://innocode.com
 * Tested up to: 5.6
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

use Innocode\FlushCache;

define( 'INNOCODE_FLUSH_CACHE_VERSION', '2.3.0' );
define( 'INNOCODE_FLUSH_CACHE_FILE', __FILE__ );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

if ( ! function_exists( 'flush_cache_add_button' ) ) {
    /**
     * Adds flush button with a callback to site admin area.
     *
     * @param string   $title
     * @param callable $callback
     * @param string   $description
     */
    function flush_cache_add_button( $title, callable $callback, $description = '' ) {
        FlushCache\Helpers::add_action( 'buttons', $title, $callback, $description );
    }
}

if ( ! function_exists( 'flush_cache_add_network_button' ) ) {
    /**
     * Adds flush button with a callback to network admin area.
     *
     * @param string   $title
     * @param callable $callback
     * @param string   $description
     */
    function flush_cache_add_network_button( $title, callable $callback, $description = '' ) {
        FlushCache\Helpers::add_action( 'network_buttons', $title, $callback, $description );
    }
}

if ( ! function_exists( 'flush_cache_add_sites_action_link' ) ) {
    /**
     * Adds action link with a callback to network admin area to the sites list.
     *
     * @param string   $title
     * @param callable $callback
     * @param string   $description
     */
    function flush_cache_add_sites_action_link( $title, callable $callback, $description = '' ) {
        FlushCache\Helpers::add_action( 'sites_action_links', $title, $callback, $description );
    }
}

$innocode_flush_cache = new FlushCache\Plugin( __DIR__ );
$innocode_flush_cache->run();

if ( wp_using_ext_object_cache() ) {
    if ( is_multisite() ) {
        flush_cache_add_network_button(
            __( 'Object cache', 'innocode-flush-cache' ),
            'wp_cache_flush'
        );
    } else {
        flush_cache_add_button(
            __( 'Object cache', 'innocode-flush-cache' ),
            'wp_cache_flush'
        );
    }
} else {
    flush_cache_add_button(
        __( 'Transient cache', 'innocode-flush-cache' ),
        [ 'Innocode\FlushCache\Helpers', 'delete_all_transients' ]
    );

    if ( is_multisite() ) {
        flush_cache_add_network_button(
            __( 'Transient cache', 'innocode-flush-cache' ),
            [ 'Innocode\FlushCache\Helpers', 'delete_all_transients' ]
        );
        flush_cache_add_sites_action_link(
            __( 'Transient cache', 'innocode-flush-cache' ),
            [ 'Innocode\FlushCache\Helpers', 'delete_all_transients' ]
        );
    }
}
