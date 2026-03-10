<?php
/**
 * Product card template for shop/category loops
 *
 * @package STEW_Child
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}

$is_new = ( ( time() - get_the_time( 'U' ) ) < ( 30 * DAY_IN_SECONDS ) );
?>
<li <?php wc_product_class( 'stew-product-card', $product ); ?>>
    <a href="<?php the_permalink(); ?>" class="stew-product-card__link">
        <div class="stew-product-card__image-wrap">
            <?php if ( ! $product->is_in_stock() ) : ?>
                <span class="stew-badge stew-badge--soldout"><?php esc_html_e( 'Ausverkauft', 'stew-child' ); ?></span>
            <?php elseif ( $is_new ) : ?>
                <span class="stew-badge stew-badge--new"><?php esc_html_e( 'Neu', 'stew-child' ); ?></span>
            <?php endif; ?>

            <?php if ( $product->is_on_sale() && $product->is_in_stock() ) : ?>
                <span class="stew-badge stew-badge--sale"><?php esc_html_e( 'Aktion', 'stew-child' ); ?></span>
            <?php endif; ?>

            <?php echo woocommerce_get_product_thumbnail( 'product-card' ); // phpcs:ignore ?>
        </div>

        <div class="stew-product-card__info">
            <h3 class="stew-product-card__name"><?php the_title(); ?></h3>

            <?php if ( '' === $product->get_price() || 0 == $product->get_price() ) : ?>
                <p class="stew-price-inquiry"><?php esc_html_e( 'Preis auf Anfrage', 'stew-child' ); ?></p>
            <?php else : ?>
                <p class="stew-product-card__price">
                    <?php echo $product->get_price_html(); // phpcs:ignore ?>
                </p>
            <?php endif; ?>
        </div>
    </a>
</li>
