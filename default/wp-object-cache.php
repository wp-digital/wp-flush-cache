<?php

if ( wp_using_ext_object_cache() ) {

    flush_cache_add_button( __( 'Object cache' ), 'wp_cache_flush' );
}