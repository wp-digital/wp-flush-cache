# Flush Cache Buttons

### Description

Helps to flush different types of cache.

### Install

- Preferable way is to use [Composer](https://getcomposer.org/):

    ````
    composer require innocode-digital/wp-flush-cache
    ````

  By default, it will be installed as [Must Use Plugin](https://codex.wordpress.org/Must_Use_Plugins).
  It's possible to control with `extra.installer-paths` in `composer.json`.

- Alternate way is to clone this repo to `wp-content/mu-plugins/` or `wp-content/plugins/`:

    ````
    cd wp-content/plugins/
    git clone git@github.com:innocode-digital/wp-flush-cache.git
    cd wp-flush-cache/
    composer install
    ````

If plugin was installed as regular plugin then activate **Flush Cache Buttons** from Plugins page
or [WP-CLI](https://make.wordpress.org/cli/handbook/): `wp plugin activate wp-flush-cache`.

### Usage

From the box this plugin adds possibility to flush object cache in case when site uses
[Persistent Caching](https://developer.wordpress.org/reference/classes/wp_object_cache/#persistent-caching),
if not, then [Transients](https://developer.wordpress.org/apis/handbook/transients/).

#### Notes

In [Network](https://wordpress.org/support/article/create-a-network/) global caches could be flushed from
network admin area and individual from each site admin area. Also, it's possible to flush individual cache
from sites list in network admin area.

### Documentation

Adds flush button with a callback to site admin area: `/wp-admin/tools.php?page=innocode_cache-control`
(**Tools** -> **Cache**).

```
flush_cache_add_button( string $title, callable $callback, string $description = '' );
```

Adds flush button with a callback to network admin area: `/wp-admin/network/admin.php?page=innocode_cache-control`.

```
flush_cache_add_network_button( string $title, callable $callback, string $description = '' );
```

Adds action link with a callback to network admin area to the sites list: `/wp-admin/network/sites.php`.

```
function flush_cache_add_sites_action_link( string $title, callable $callback, string $description = '' );
```
