<?php

namespace Innocode\FlushCache;

use Closure;

/**
 * Class Helpers
 * @package Innocode\FlushCache
 */
final class Helpers
{
    /**
     * @var int
     */
    private static $closures_count = 0;

    /**
     * @param string   $type
     * @param string   $title
     * @param callable $callback
     * @param string   $description
     */
    public static function add_action( $type, $title, callable $callback, $description = '' )
    {
        $hook = "innocode_flush_cache_$type";
        $callable_name = Helpers::callable_to_string( $callback );

        if ( $callable_name == 'Closure' ) {
            $callable_name .= Helpers::$closures_count;
            Helpers::$closures_count++;
        }

        $action = "{$hook}_" . sanitize_key( $callable_name );

        add_action( "wp_ajax_$action", function () use ( $action, $title, $callback ) {
            check_ajax_referer( $action );

            if ( is_multisite() ) {
                $blog_id = isset( $_REQUEST['blog_id'] ) ? absint( $_REQUEST['blog_id'] ) : get_current_blog_id();

                switch_to_blog( $blog_id );
                $success = $callback() !== false;
                restore_current_blog();
            } else {
                $success = $callback() !== false;
            }

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

        add_filter( $hook, function ( array $buttons ) use ( $action, $title, $description ) {
            $buttons[ $action ] = [
                'title'       => $title,
                'description' => $description,
            ];

            return $buttons;
        } );
    }

    /**
     * @return array
     */
    public static function get_buttons()
    {
        return apply_filters( 'innocode_flush_cache_buttons', [] );
    }

    /**
     * @return array
     */
    public static function get_network_buttons()
    {
        return apply_filters( 'innocode_flush_cache_network_buttons', [] );
    }

    /**
     * @return array
     */
    public static function get_sites_action_links()
    {
        return apply_filters( 'innocode_flush_cache_sites_action_links', [] );
    }

    /**
     * @return int
     */
    public static function delete_all_transients()
    {
        global $wpdb;

        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->options WHERE option_name LIKE %s",
                $wpdb->esc_like( '_transient_' ) . '%'
            )
        );

        if ( ! is_multisite() ) {
            // Single site stores site transients in the options table.
            $deleted .= $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM $wpdb->options WHERE option_name LIKE %s",
                    $wpdb->esc_like( '_site_transient_' ) . '%'
                )
            );
        } elseif ( is_multisite() && is_main_site() && is_main_network() ) {
            // Multisite stores site transients in the sitemeta table.
            $deleted .= $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM $wpdb->sitemeta WHERE meta_key LIKE %s",
                    $wpdb->esc_like( '_site_transient_' ) . '%'
                )
            );
        }

        return $deleted;
    }

    /**
     * @param callable $callable
     * @return string
     */
    public static function callable_to_string( callable $callable )
    {
        if ( is_string( $callable ) ) {
            return trim( $callable );
        }

        if ( is_array( $callable ) ) {
            if ( is_object( $callable[0] ) ) {
                return sprintf( "%s::%s", get_class( $callable[0] ), trim( $callable[1] ) );
            } else {
                return sprintf( "%s::%s", trim( $callable[0] ), trim( $callable[1] ) );
            }
        }

        if ( $callable instanceof Closure ) {
            return 'Closure';
        }

        return 'Unknown';
    }
}
