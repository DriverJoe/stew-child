<?php
/**
 * Additional Information tab — shows ACF technical specs
 *
 * @package STEW_Child
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $product;

$spec_fields = array(
    'cc_cv_type'              => __( 'CC/CV Typ', 'stew-child' ),
    'power_watts'             => __( 'Leistung (Watt)', 'stew-child' ),
    'output_current_ma'       => __( 'Ausgangsstrom (mA)', 'stew-child' ),
    'dimming_type'            => __( 'Dimmung', 'stew-child' ),
    'output_channels'         => __( 'Ausgangskanäle', 'stew-child' ),
    'ip_protection'           => __( 'IP-Schutzart', 'stew-child' ),
    'dimensions_length_mm'    => __( 'Länge (mm)', 'stew-child' ),
    'dimensions_width_mm'     => __( 'Breite (mm)', 'stew-child' ),
    'dimensions_height_mm'    => __( 'Höhe (mm)', 'stew-child' ),
    'dimension_category'      => __( 'Grössenkategorie', 'stew-child' ),
    'input_voltage'           => __( 'Eingangsspannung', 'stew-child' ),
    'series_type'             => __( 'Serien-Typ', 'stew-child' ),
    'additional_functions'    => __( 'Zusatzfunktionen', 'stew-child' ),
    'manufacturer_brand'      => __( 'Hersteller', 'stew-child' ),
    'manufacturer_part_number'=> __( 'Hersteller-Artikelnummer', 'stew-child' ),
    'ugr_rating'              => __( 'UGR-Wert', 'stew-child' ),
    'cri_value'               => __( 'CRI-Wert', 'stew-child' ),
    'cct_colour_temp'         => __( 'Farbtemperatur (K)', 'stew-child' ),
    'beam_angle'              => __( 'Abstrahlwinkel', 'stew-child' ),
    'lumen_output'            => __( 'Lichtstrom (lm)', 'stew-child' ),
    'tilt_range'              => __( 'Schwenkbereich', 'stew-child' ),
    'cutout_diameter'         => __( 'Einbaudurchmesser', 'stew-child' ),
    'ceiling_thickness'       => __( 'Deckendicke', 'stew-child' ),
    'recess_depth'            => __( 'Einbautiefe', 'stew-child' ),
    'led_source'              => __( 'LED-Quelle', 'stew-child' ),
    'system_wattage'          => __( 'Systemleistung', 'stew-child' ),
    'lifetime'                => __( 'Lebensdauer', 'stew-child' ),
);

// Collect non-empty specs
$filled_specs = array();
foreach ( $spec_fields as $field_key => $label ) {
    $value = get_post_meta( get_the_ID(), $field_key, true );
    if ( ! empty( $value ) ) {
        if ( is_array( $value ) ) {
            $value = implode( ', ', $value );
        }
        $filled_specs[ $label ] = $value;
    }
}

// Also include WooCommerce default attributes
$attributes = $product->get_attributes();
?>

<?php if ( ! empty( $filled_specs ) || ! empty( $attributes ) ) : ?>
    <h2 class="stew-section-heading"><?php esc_html_e( 'Technische Daten', 'stew-child' ); ?></h2>

    <table class="stew-specs-table woocommerce-product-attributes shop_attributes">
        <tbody>
            <?php foreach ( $filled_specs as $label => $value ) : ?>
                <tr>
                    <th><?php echo esc_html( $label ); ?></th>
                    <td><?php echo esc_html( $value ); ?></td>
                </tr>
            <?php endforeach; ?>

            <?php
            // WooCommerce product attributes
            foreach ( $attributes as $attribute ) :
                if ( ! $attribute->get_visible() ) continue;
            ?>
                <tr>
                    <th><?php echo esc_html( wc_attribute_label( $attribute->get_name() ) ); ?></th>
                    <td>
                        <?php
                        $values = array();
                        if ( $attribute->is_taxonomy() ) {
                            $attribute_taxonomy = $attribute->get_taxonomy_object();
                            $attribute_values   = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'all' ) );
                            foreach ( $attribute_values as $attribute_value ) {
                                $values[] = esc_html( $attribute_value->name );
                            }
                        } else {
                            $values = $attribute->get_options();
                        }
                        echo esc_html( implode( ', ', $values ) );
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php
    // Datasheet download
    $datasheet = get_post_meta( get_the_ID(), 'datasheet_pdf', true );
    if ( $datasheet ) :
    ?>
        <p style="margin-top: 1.5rem;">
            <a href="<?php echo esc_url( $datasheet ); ?>" class="stew-datasheet-btn" target="_blank" rel="noopener">
                <?php esc_html_e( 'Datenblatt herunterladen (PDF)', 'stew-child' ); ?>
            </a>
        </p>
    <?php endif; ?>

<?php else : ?>
    <p><?php esc_html_e( 'Keine technischen Daten verfügbar.', 'stew-child' ); ?></p>
<?php endif; ?>
