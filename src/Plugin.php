<?php

namespace Innocode\FlushCache;

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
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
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

    public function enqueue_scripts()
    {
        if ( get_current_screen()->id != 'tools_page_' . Plugin::ADMIN_PAGE_CACHE_CONTROL ) {
            return;
        }

        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_script(
            'innocode-flush-cache',
            plugins_url(
                "public/js/main$suffix.js",
                INNOCODE_FLUSH_CACHE_FILE
            ),
            [ 'jquery' ],
            INNOCODE_FLUSH_CACHE_VERSION,
            true
        );
        wp_localize_script( 'innocode-flush-cache', 'innocodeFlushCache', [
            'selector' => Plugin::ADMIN_PAGE_CACHE_CONTROL,
        ] );
    }
}
