<?php
/**
 * Shop / Product Category archive template
 *
 * @package STEW_Child
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header( 'shop' ); ?>

<div class="stew-container stew-section">

    <?php
    /**
     * Breadcrumbs
     */
    woocommerce_breadcrumb();
    ?>

    <?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
        <h1 class="woocommerce-products-header__title page-title stew-section-title">
            <?php woocommerce_page_title(); ?>
        </h1>
    <?php endif; ?>

    <?php
    /**
     * Archive description
     */
    do_action( 'woocommerce_archive_description' );
    ?>

    <div class="stew-shop-layout">

        <!-- Filter Sidebar -->
        <aside class="stew-filter-sidebar">
            <h3><?php esc_html_e( 'Filter', 'stew-child' ); ?></h3>

            <?php if ( function_exists( 'facetwp_display' ) ) : ?>

                <div class="stew-filter-group">
                    <h4><?php esc_html_e( 'Suche', 'stew-child' ); ?></h4>
                    <?php echo facetwp_display( 'facet', 'product_search' ); ?>
                </div>

                <div class="stew-filter-group">
                    <h4><?php esc_html_e( 'CC/CV Typ', 'stew-child' ); ?></h4>
                    <?php echo facetwp_display( 'facet', 'cc_cv_type' ); ?>
                </div>

                <div class="stew-filter-group">
                    <h4><?php esc_html_e( 'Leistung (Watt)', 'stew-child' ); ?></h4>
                    <?php echo facetwp_display( 'facet', 'power_watts' ); ?>
                </div>

                <div class="stew-filter-group">
                    <h4><?php esc_html_e( 'Dimmung', 'stew-child' ); ?></h4>
                    <?php echo facetwp_display( 'facet', 'dimming_type' ); ?>
                </div>

                <div class="stew-filter-group">
                    <h4><?php esc_html_e( 'IP-Schutzart', 'stew-child' ); ?></h4>
                    <?php echo facetwp_display( 'facet', 'ip_protection' ); ?>
                </div>

                <div class="stew-filter-group">
                    <h4><?php esc_html_e( 'Baugrösse', 'stew-child' ); ?></h4>
                    <?php echo facetwp_display( 'facet', 'dimension_category' ); ?>
                </div>

                <div class="stew-filter-group">
                    <h4><?php esc_html_e( 'Serien-Typ', 'stew-child' ); ?></h4>
                    <?php echo facetwp_display( 'facet', 'series_type' ); ?>
                </div>

                <div class="stew-filter-group">
                    <h4><?php esc_html_e( 'Hersteller', 'stew-child' ); ?></h4>
                    <?php echo facetwp_display( 'facet', 'manufacturer' ); ?>
                </div>

                <div class="stew-filter-group">
                    <h4><?php esc_html_e( 'Preis (CHF)', 'stew-child' ); ?></h4>
                    <?php echo facetwp_display( 'facet', 'price_range' ); ?>
                </div>

                <div class="stew-filter-group">
                    <h4><?php esc_html_e( 'Verfügbarkeit', 'stew-child' ); ?></h4>
                    <?php echo facetwp_display( 'facet', 'availability' ); ?>
                </div>

            <?php else : ?>
                <!-- Fallback: WooCommerce layered nav widgets -->
                <?php dynamic_sidebar( 'shop-sidebar' ); ?>
            <?php endif; ?>
        </aside>

        <!-- Product Grid -->
        <div class="stew-shop-content">

            <!-- Top Bar -->
            <div class="stew-shop-topbar">
                <span class="stew-shop-topbar__count">
                    <?php
                    $total = $wp_query->found_posts;
                    printf(
                        esc_html( _n( '%s Artikel', '%s Artikel', $total, 'stew-child' ) ),
                        esc_html( number_format_i18n( $total ) )
                    );
                    ?>
                </span>
                <div class="stew-shop-topbar__sort">
                    <?php if ( function_exists( 'facetwp_display' ) ) : ?>
                        <?php echo facetwp_display( 'facet', 'sort_by' ); ?>
                    <?php else : ?>
                        <?php woocommerce_catalog_ordering(); ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php
            if ( woocommerce_product_loop() ) {

                /**
                 * Hook: woocommerce_before_shop_loop
                 */
                do_action( 'woocommerce_before_shop_loop' );

                woocommerce_product_loop_start();

                if ( wc_get_loop_prop( 'total' ) ) {
                    while ( have_posts() ) {
                        the_post();

                        /**
                         * Hook: woocommerce_shop_loop
                         */
                        do_action( 'woocommerce_shop_loop' );

                        wc_get_template_part( 'content', 'product' );
                    }
                }

                woocommerce_product_loop_end();

                /**
                 * Hook: woocommerce_after_shop_loop (pagination)
                 */
                do_action( 'woocommerce_after_shop_loop' );

            } else {
                /**
                 * No products found
                 */
                do_action( 'woocommerce_no_products_found' );
            }
            ?>

        </div><!-- .stew-shop-content -->

    </div><!-- .stew-shop-layout -->

</div><!-- .stew-container -->

<?php
get_footer( 'shop' );
