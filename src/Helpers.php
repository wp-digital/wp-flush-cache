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
    public static function add_action( string $type, string $title, callable $callback, string $description = '' )
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

        add_filter( $hook, function ( array $buttons ) use ( $action, $title, $description ) : array {
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
    public static function get_buttons() : array
    {
        return apply_filters( 'innocode_flush_cache_buttons', [] );
    }

    /**
     * @return array
     */
    public static function get_network_buttons() : array
    {
        return apply_filters( 'innocode_flush_cache_network_buttons', [] );
    }

    /**
     * @return array
     */
    public static function get_sites_action_links() : array
    {
        return apply_filters( 'innocode_flush_cache_sites_action_links', [] );
    }

    /**
     * @return bool
     */
    public static function delete_all_transients() : bool
    {
        global $wpdb;

        $wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_%');" );
        $wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_site_transient_%');" );

        return true;
    }

    /**
     * @param callable $callable
     * @return string
     */
    public static function callable_to_string( callable $callable ) : string
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
