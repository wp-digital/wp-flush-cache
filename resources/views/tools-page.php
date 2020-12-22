<div class="wrap">
    <h1><?php _e( 'Cache Control', 'innocode-flush-cache' ) ?></h1>
    <table class="form-table" role="presentation">
        <?php foreach( apply_filters( 'innocode_flush_cache_buttons', [] ) as $key => $title ): ?>
            <tr>
                <th scope="row"><?php esc_html_e( $title ) ?></th>
                <td>
                    <form method="post" action="<?= admin_url( 'admin-ajax.php', 'relative' ) ?>" class="<?= Innocode\FlushCache\Plugin::ADMIN_PAGE_CACHE_CONTROL ?>__form">
                        <input type="hidden" name="action" value="<?= esc_attr( $key ) ?>">
                        <?php wp_nonce_field( $key ) ?>
                        <button type="submit" class="button"><?php _e( 'Flush', 'innocode-flush-cache' ) ?></button>
                        <span class="spinner" style="float: none"></span>
                    </form>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
</div>
