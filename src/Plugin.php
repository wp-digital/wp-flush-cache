<?php

namespace Innocode\FlushCache;

use WP_Admin_Bar;

/**
 * Class Plugin
 * @package Innocode\FlushCache
 */
final class Plugin
{
    const ADMIN_PAGE_CACHE_CONTROL = 'innocode_cache-control';

    /**
     * @var string
     */
    private $path;

    /**
     * Plugin constructor.
     * @param string $path
     */
    public function __construct( string $path )
    {
        $this->path = $path;
    }

    public function run()
    {
        add_action( 'admin_menu', [ $this, 'add_admin_page' ] );
        add_action( 'network_admin_menu',  [ $this, 'add_network_admin_page' ] );
        add_action( 'admin_bar_menu', [ $this, 'add_admin_bar_menu_item' ], 100 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        add_filter( 'manage_sites_action_links', [ $this, 'add_sites_action_links' ], 10, 2 );
    }

    /**
     * @return string
     */
    public function get_path() : string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function get_views_dir() : string
    {
        return "{$this->get_path()}/resources/views";
    }

    /**
     * @param string $name
     * @return string
     */
    public function get_view_file( string $name ) : string
    {
        return "{$this->get_views_dir()}/$name";
    }

    /**
     * @param string $name
     */
    public function view( string $name )
    {
        $file = $this->get_view_file( "$name.php" );

        require_once $file;
    }

    public function add_admin_page()
    {
        $buttons = Helpers::get_buttons();

        if ( empty( $buttons ) ) {
            return;
        }

        add_submenu_page(
            'tools.php',
            __( 'Cache Control', 'innocode-flush-cache' ),
            __( 'Cache', 'innocode-flush-cache' ),
            'manage_options',
            Plugin::ADMIN_PAGE_CACHE_CONTROL,
            function () {
                $this->view( 'tools-page' );
            }
        );
    }

    public function add_network_admin_page()
    {
        $buttons = Helpers::get_network_buttons();

        if ( empty( $buttons ) ) {
            return;
        }

        add_menu_page(
            __( 'Cache Control', 'innocode-flush-cache' ),
            __( 'Cache', 'innocode-flush-cache' ),
            'manage_network',
            Plugin::ADMIN_PAGE_CACHE_CONTROL,
            function () {
                $this->view( 'tools-page' );
            },
            'dashicons-database'
        );
    }

    /**
     * @param WP_Admin_Bar $wp_admin_bar
     */
    public function add_admin_bar_menu_item( WP_Admin_Bar $wp_admin_bar )
    {
        $buttons = Helpers::get_network_buttons();

        if ( empty( $buttons ) || ! is_multisite() || ! is_super_admin() ) {
            return;
        }

        $wp_admin_bar->add_menu( [
            'id'     => Plugin::ADMIN_PAGE_CACHE_CONTROL,
            'parent' => 'network-admin',
            'title'  => __( 'Cache', 'innocode-flush-cache' ),
            'href'   => network_admin_url( 'admin.php?page=' . Plugin::ADMIN_PAGE_CACHE_CONTROL ),
        ] );
    }

    public function enqueue_scripts()
    {
        $buttons = Helpers::get_buttons();
        $network_buttons = Helpers::get_network_buttons();
        $sites_action_links = Helpers::get_sites_action_links();

        if ( (
            empty( $buttons ) ||
            get_current_screen()->id != 'tools_page_' . Plugin::ADMIN_PAGE_CACHE_CONTROL
        ) && (
            empty( $network_buttons ) ||
            get_current_screen()->id != 'toplevel_page_' . Plugin::ADMIN_PAGE_CACHE_CONTROL . '-network'
        ) && (
            empty( $sites_action_links ) ||
            get_current_screen()->id != 'sites-network'
        ) ) {
            return;
        }

        // Domain mapping processes mu-plugins directory wrong.
        $has_domain_mapping = remove_filter( 'plugins_url', 'domain_mapping_plugins_uri', 1 );

        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $script_url = plugins_url( "public/js/main$suffix.js", INNOCODE_FLUSH_CACHE_FILE );

        if ( $has_domain_mapping ) {
            add_filter( 'plugins_url', 'domain_mapping_plugins_uri', 1 );
        }

        wp_enqueue_script(
            'innocode-flush-cache',
            $script_url,
            [ 'jquery' ],
            INNOCODE_FLUSH_CACHE_VERSION,
            true
        );
        wp_localize_script( 'innocode-flush-cache', 'innocodeFlushCache', [
            'selector' => Plugin::ADMIN_PAGE_CACHE_CONTROL,
        ] );
    }

    /**
     * @param array $actions
     * @param int   $blog_id
     * @return array
     */
    public function add_sites_action_links( array $actions, int $blog_id ) : array
    {
        foreach ( Helpers::get_sites_action_links() as $key => $link ) {
            $actions[ $key ] = sprintf(
                '<a href="%1$s" title="%2$s" class="aria-button-if-js %3$s__link">%4$s</a><span class="%3$s__link-spinner" style="display: none;">%5$s</span>',
                wp_nonce_url( add_query_arg( [
                    'action'  => $key,
                    'blog_id' => $blog_id,
                ], admin_url( 'admin-ajax.php' ) ), $key ),
                !empty( $link['description'] ) ? esc_html( $link['description'] ) : '',
                esc_attr( Plugin::ADMIN_PAGE_CACHE_CONTROL ),
                sprintf( __( 'Flush %s', 'innocode-flush-cache' ), esc_html( $link['title'] ) ),
                __( 'Flushing...', 'innocode-flush-cache' )
            );
        }

        return $actions;
    }
}
