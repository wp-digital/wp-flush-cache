<?php
/*
Plugin Name: Flush Cache Buttons
Description: WordPress plugin for flushing cache
Version: 1.1
Plugin URI: https://github.com/shtrihstr/wp-flush-cache
Author: Oleksandr Strikha
Author URI: https://github.com/shtrihstr
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


function flush_cache_add_button( $title, $callback ) {

    $key = 'flush_cache_' . sanitize_key( $title );
    add_action( 'wp_ajax_' . $key, $callback );

    add_filter( 'flush_cache_buttons', function ( $buttons ) use ( $key, $title ) {
        $buttons[ $key ] = $title;
        return $buttons;
    } );
}

add_action( 'admin_menu', function() {

    add_submenu_page( 'tools.php', __( 'Cache Control' ), __( 'Cache' ), 'manage_options', 'cache-control', function() {

        $buttons = apply_filters( 'flush_cache_buttons', [] );
        ?>

        <div class="wrap">
            <div id="icon-tools" class="icon32"></div>
            <h2><?php _e( 'Cache Control' ) ?></h2>
            <table class="form-table">

                <?php foreach( $buttons as $key => $title ): ?>
                    <tr valign="top">
                        <th scope="row"><?= esc_html( $title ) ?></th>
                        <td>
                            <button type="button" data-role="flush" data-action="<?= esc_attr( $key ) ?>" class="button"><?php _e( 'Flush' ) ?></button>
                            <span class="spinner" style="float: none"></span>
                        </td>
                    </tr>
                <?php endforeach ?>

            </table>
        </div>
        <script>
            jQuery(function($) {
                $('[data-role="flush"]').click(function(e) {
                    e.preventDefault();
                    var _btn = $(this);
                    var _gif = _btn.next('.spinner');
                    var _done = function() {
                        _btn.show();
                        _gif.removeClass('is-active');
                    };
                    _btn.hide();
                    _gif.addClass('is-active');

                    wp.ajax.send( _btn.data('action'), {
                        success: _done,
                        error: _done
                    } );
                });
            });
        </script>

    <?php

    } );
} );

add_action( 'admin_enqueue_scripts', function() {
    if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'cache-control' ) {
        wp_enqueue_script( 'wp-util' );
    }
} );


require_once __DIR__ . '/default/wp-transient-cache.php';
require_once __DIR__ . '/default/wp-object-cache.php';