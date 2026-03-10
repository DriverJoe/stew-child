<?php
/**
 * Homepage Category Cards Section
 *
 * @package STEW_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$categories = array(
    array(
        'name' => __( 'Einbauleuchten', 'stew-child' ),
        'slug' => 'einbauleuchten',
        'desc' => __( 'Recessed Lights', 'stew-child' ),
    ),
    array(
        'name' => __( 'Aufbauleuchten', 'stew-child' ),
        'slug' => 'aufbauleuchten',
        'desc' => __( 'Surface-Mounted Lights', 'stew-child' ),
    ),
    array(
        'name' => __( 'Dekorative Leuchten', 'stew-child' ),
        'slug' => 'dekorative-leuchten',
        'desc' => __( 'Decorative Lights', 'stew-child' ),
    ),
    array(
        'name' => __( 'LED Treiber', 'stew-child' ),
        'slug' => 'led-treiber',
        'desc' => __( 'LED Drivers', 'stew-child' ),
    ),
);
?>
<section class="stew-section">
    <div class="stew-container">
        <h2 class="stew-section-heading stew-text-center">
            <?php esc_html_e( 'Nach Kollektion einkaufen', 'stew-child' ); ?>
        </h2>

        <div class="stew-categories">
            <?php foreach ( $categories as $cat ) :
                $term = get_term_by( 'slug', $cat['slug'], 'product_cat' );
                $link = $term ? get_term_link( $term ) : home_url( '/produktkategorie/' . $cat['slug'] . '/' );
                $thumbnail_id = $term ? get_term_meta( $term->term_id, 'thumbnail_id', true ) : 0;
                $image = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'category-card' ) : wc_placeholder_img_src( 'category-card' );
            ?>
                <a href="<?php echo esc_url( $link ); ?>" class="stew-category-card">
                    <div class="stew-category-card__image">
                        <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $cat['name'] ); ?>" loading="lazy" />
                    </div>
                    <div class="stew-category-card__info">
                        <span class="stew-category-card__name"><?php echo esc_html( $cat['name'] ); ?></span>
                        <svg class="stew-category-card__arrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
