<?php $buttons = is_network_admin() ?
    Innocode\FlushCache\Helpers::get_network_buttons() :
    Innocode\FlushCache\Helpers::get_buttons(); ?>
<div class="wrap">
    <h1><?php _e( 'Cache Control', 'innocode-flush-cache' ) ?></h1>
    <table class="form-table" role="presentation">
        <?php foreach( $buttons as $key => $button ): ?>
            <tr>
                <th scope="row"><?php esc_html_e( $button['title'] ) ?></th>
                <td>
                    <form method="post" action="<?= admin_url( 'admin-ajax.php', 'relative' ) ?>" class="<?= esc_attr( Innocode\FlushCache\Plugin::ADMIN_PAGE_CACHE_CONTROL ) ?>__form">
                        <input type="hidden" name="action" value="<?= esc_attr( $key ) ?>">
                        <?php wp_nonce_field( $key ) ?>
                        <button type="submit" class="button"><?php _e( 'Flush', 'innocode-flush-cache' ) ?></button>
                        <span class="spinner" style="float: none"></span>
                    </form>
                    <?php if ( ! empty( $button['description'] ) ) : ?>
                        <p class="description">
                            <?= $button['description'] ?>
                        </p>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
</div>
