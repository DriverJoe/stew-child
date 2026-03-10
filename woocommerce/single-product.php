<?php
/**
 * Single Product template
 *
 * @package STEW_Child
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header( 'shop' ); ?>

<div class="stew-container stew-section">

    <?php woocommerce_breadcrumb(); ?>

    <?php while ( have_posts() ) : the_post(); ?>

        <?php
        global $product;

        // ACF fields
        $power_watts       = get_field( 'power_watts' );
        $cc_cv_type        = get_field( 'cc_cv_type' );
        $dimming_type      = get_field( 'dimming_type' );
        $output_current    = get_field( 'output_current_ma' );
        $output_channels   = get_field( 'output_channels' );
        $ip_protection     = get_field( 'ip_protection' );
        $dimensions_l      = get_field( 'dimensions_length_mm' );
        $dimensions_w      = get_field( 'dimensions_width_mm' );
        $dimensions_h      = get_field( 'dimensions_height_mm' );
        $input_voltage     = get_field( 'input_voltage' );
        $series_type       = get_field( 'series_type' );
        $additional_funcs  = get_field( 'additional_functions' );
        $manufacturer      = get_field( 'manufacturer_brand' );
        $part_number       = get_field( 'manufacturer_part_number' );
        $datasheet         = get_field( 'datasheet_pdf' );

        // Light-specific fields
        $ugr_rating        = get_field( 'ugr_rating' );
        $cri_value         = get_field( 'cri_value' );
        $cct_colour_temp   = get_field( 'cct_colour_temp' );
        $beam_angle        = get_field( 'beam_angle' );
        $lumen_output      = get_field( 'lumen_output' );
        ?>

        <div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'stew-single-product', $product ); ?>>

            <!-- Product Gallery -->
            <div class="stew-product-gallery">
                <?php
                /**
                 * Hook: woocommerce_before_single_product_summary (gallery)
                 */
                do_action( 'woocommerce_before_single_product_summary' );
                ?>
            </div>

            <!-- Product Info -->
            <div class="stew-product-info">

                <h1 class="stew-product-info__title"><?php the_title(); ?></h1>

                <?php if ( $manufacturer ) : ?>
                    <p class="stew-text-muted stew-mb-1"><?php echo esc_html( $manufacturer ); ?></p>
                <?php endif; ?>

                <div class="stew-product-info__price">
                    <?php echo $product->get_price_html(); // phpcs:ignore ?>
                </div>

                <div class="stew-product-info__description">
                    <?php the_excerpt(); ?>
                </div>

                <!-- Add to Cart -->
                <?php if ( $product->is_in_stock() ) : ?>
                    <?php woocommerce_template_single_add_to_cart(); ?>
                <?php else : ?>
                    <p class="stew-badge stew-badge--soldout" style="display:inline-block;">
                        <?php esc_html_e( 'Ausverkauft', 'stew-child' ); ?>
                    </p>
                <?php endif; ?>

                <!-- Highlights -->
                <div class="stew-product-highlights">
                    <ul>
                        <?php if ( $power_watts ) : ?>
                            <li><?php printf( esc_html__( '%s Watt Leistung', 'stew-child' ), esc_html( $power_watts ) ); ?></li>
                        <?php endif; ?>
                        <?php if ( $ip_protection ) : ?>
                            <li><?php printf( esc_html__( 'Schutzart %s', 'stew-child' ), esc_html( $ip_protection ) ); ?></li>
                        <?php endif; ?>
                        <?php if ( $dimming_type && is_array( $dimming_type ) ) : ?>
                            <li><?php echo esc_html( implode( ', ', $dimming_type ) ); ?></li>
                        <?php endif; ?>
                        <?php if ( $input_voltage ) : ?>
                            <li><?php echo esc_html( $input_voltage ); ?></li>
                        <?php endif; ?>
                        <?php if ( $product->get_sku() ) : ?>
                            <li><?php printf( esc_html__( 'Art.-Nr.: %s', 'stew-child' ), esc_html( $product->get_sku() ) ); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Datasheet Download -->
                <?php if ( $datasheet ) : ?>
                    <a href="<?php echo esc_url( $datasheet ); ?>" class="stew-datasheet-btn" target="_blank" rel="noopener">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <?php esc_html_e( 'Datenblatt herunterladen', 'stew-child' ); ?>
                    </a>
                <?php endif; ?>

                <?php
                /**
                 * Hook: woocommerce_single_product_summary
                 */
                do_action( 'woocommerce_product_meta_start' );
                woocommerce_template_single_meta();
                do_action( 'woocommerce_product_meta_end' );
                ?>

            </div><!-- .stew-product-info -->

        </div><!-- .stew-single-product -->

        <!-- Technical Specifications -->
        <?php
        $specs = array();
        if ( $cc_cv_type )      $specs[ __( 'CC/CV Typ', 'stew-child' ) ] = $cc_cv_type;
        if ( $power_watts )     $specs[ __( 'Leistung', 'stew-child' ) ] = $power_watts . ' W';
        if ( $output_current )  $specs[ __( 'Ausgangsstrom', 'stew-child' ) ] = $output_current;
        if ( $output_channels ) $specs[ __( 'Ausgangskanäle', 'stew-child' ) ] = $output_channels;
        if ( $ip_protection )   $specs[ __( 'IP-Schutzart', 'stew-child' ) ] = $ip_protection;
        if ( $dimensions_l || $dimensions_w || $dimensions_h ) {
            $specs[ __( 'Abmessungen (L×B×H)', 'stew-child' ) ] = $dimensions_l . ' × ' . $dimensions_w . ' × ' . $dimensions_h . ' mm';
        }
        if ( $input_voltage )   $specs[ __( 'Eingangsspannung', 'stew-child' ) ] = $input_voltage;
        if ( $series_type )     $specs[ __( 'Serien-Typ', 'stew-child' ) ] = $series_type;
        if ( $dimming_type && is_array( $dimming_type ) ) {
            $specs[ __( 'Dimmung', 'stew-child' ) ] = implode( ', ', $dimming_type );
        }
        if ( $additional_funcs && is_array( $additional_funcs ) ) {
            $specs[ __( 'Zusatzfunktionen', 'stew-child' ) ] = implode( ', ', $additional_funcs );
        }
        if ( $manufacturer )    $specs[ __( 'Hersteller', 'stew-child' ) ] = $manufacturer;
        if ( $part_number )     $specs[ __( 'Hersteller-Art.-Nr.', 'stew-child' ) ] = $part_number;

        // Light-specific specs
        if ( $ugr_rating )       $specs[ __( 'UGR-Wert', 'stew-child' ) ] = $ugr_rating;
        if ( $cri_value )        $specs[ __( 'CRI-Wert', 'stew-child' ) ] = $cri_value;
        if ( $cct_colour_temp )  $specs[ __( 'Farbtemperatur', 'stew-child' ) ] = $cct_colour_temp;
        if ( $beam_angle )       $specs[ __( 'Abstrahlwinkel', 'stew-child' ) ] = $beam_angle;
        if ( $lumen_output )     $specs[ __( 'Lichtstrom', 'stew-child' ) ] = $lumen_output;
        ?>

        <?php if ( ! empty( $specs ) ) : ?>
            <div class="stew-section">
                <h2 class="stew-section-heading"><?php esc_html_e( 'Technische Spezifikationen', 'stew-child' ); ?></h2>
                <table class="stew-specs-table">
                    <tbody>
                        <?php foreach ( $specs as $label => $value ) : ?>
                            <tr>
                                <th><?php echo esc_html( $label ); ?></th>
                                <td><?php echo esc_html( $value ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Product Description -->
        <?php if ( $product->get_description() ) : ?>
            <div class="stew-section">
                <h2 class="stew-section-heading"><?php esc_html_e( 'Beschreibung', 'stew-child' ); ?></h2>
                <div class="stew-product-description">
                    <?php the_content(); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Related Products -->
        <div class="stew-related-products">
            <?php
            woocommerce_related_products( array(
                'posts_per_page' => 4,
                'columns'        => 4,
            ) );
            ?>
        </div>

    <?php endwhile; ?>

</div><!-- .stew-container -->

<?php
get_footer( 'shop' );
