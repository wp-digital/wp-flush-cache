<?php

if ( ! wp_using_ext_object_cache() ) {

    flush_cache_add_button( __( 'Transient cache' ), function() {
        global $wpdb;
        $wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_%');" );
        $wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_site_transient_%');" );
    } );

}