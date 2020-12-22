<?php
/**
 * Plugin Name: Flush Cache Buttons
 * Description: Helps to flush different types of cache.
 * Plugin URI: https://github.com/innocode-digital/wp-google-datastudio
 * Version: 2.0.0
 * Author: Oleksandr Strikha, Innocode
 * Author URI: https://innocode.com
 * Tested up to: 5.6
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

use Innocode\FlushCache;

define( 'INNOCODE_FLUSH_CACHE_VERSION', '2.0.0' );
define( 'INNOCODE_FLUSH_CACHE_FILE', __FILE__ );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

if ( ! function_exists( 'flush_cache_add_button' ) ) {
    /**
     * @param string   $title
     * @param callable $callback
     */
    function flush_cache_add_button( string $title, callable $callback ) {
        $key = 'innocode_flush_cache_' . sanitize_key( $title );

        add_action( "wp_ajax_$key", function () use ( $title, $key, $callback ) {
            check_ajax_referer( $key );

            $success = $callback();

            wp_send_json( [
                'success' => $success,
                'data'    => sprintf(
                    $success
                        ? __( '%s flushed.', 'innocode-flush-cache' )
                        : __( '%s could not be flushed.', 'innocode-flush-cache' ),
                    $title
                )
            ] );
        } );

        add_filter( 'innocode_flush_cache_buttons', function ( $buttons ) use ( $key, $title ) {
            $buttons[ $key ] = $title;

            return $buttons;
        } );
    }
}

if ( ! function_exists( 'flush_cache_delete_all_transients' ) ) {
    function flush_cache_delete_all_transients() : bool {
        global $wpdb;

        $wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_%');" );
        $wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_site_transient_%');" );

        return true;
    }
}

$innocode_flush_cache = new FlushCache\Plugin( __DIR__ );
$innocode_flush_cache->run();

if ( wp_using_ext_object_cache() ) {
    flush_cache_add_button(
        __( 'Object cache', 'innocode-flush-cache' ),
        'wp_cache_flush'
    );
} else {
    flush_cache_add_button(
        __( 'Transient cache', 'innocode-flush-cache' ),
        'flush_cache_delete_all_transients'
    );
}
