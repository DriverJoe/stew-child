<?php
/**
 * STEW Child Theme Functions
 *
 * @package STEW_Child
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'STEW_CHILD_VERSION', '1.2.0' );
define( 'STEW_CHILD_DIR', get_stylesheet_directory() );
define( 'STEW_CHILD_URI', get_stylesheet_directory_uri() );

/**
 * Enqueue parent and child theme styles + scripts
 */
function stew_enqueue_assets() {
    // Parent theme style
    wp_enqueue_style(
        'salient-parent-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme( 'salient' )->get( 'Version' )
    );

    // Child theme style
    wp_enqueue_style(
        'stew-child-style',
        get_stylesheet_uri(),
        array( 'salient-parent-style' ),
        STEW_CHILD_VERSION
    );

    // Custom CSS
    wp_enqueue_style(
        'stew-custom-css',
        STEW_CHILD_URI . '/assets/css/stew-custom.css',
        array( 'stew-child-style' ),
        STEW_CHILD_VERSION
    );

    // Custom JS
    wp_enqueue_script(
        'stew-custom-js',
        STEW_CHILD_URI . '/assets/js/stew-custom.js',
        array( 'jquery' ),
        STEW_CHILD_VERSION,
        true
    );

    // Pass AJAX URL to JS
    wp_localize_script( 'stew-custom-js', 'stewAjax', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'stew_nonce' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'stew_enqueue_assets', 9999 );

/**
 * WooCommerce support
 */
function stew_woocommerce_support() {
    add_theme_support( 'woocommerce', array(
        'thumbnail_image_width' => 400,
        'single_image_width'    => 800,
        'product_grid'          => array(
            'default_rows'    => 4,
            'min_rows'        => 1,
            'default_columns' => 3,
            'min_columns'     => 1,
            'max_columns'     => 4,
        ),
    ) );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'stew_woocommerce_support' );

/**
 * Custom image sizes
 */
function stew_custom_image_sizes() {
    add_image_size( 'product-card', 400, 400, true );
    add_image_size( 'product-hero', 800, 800, false );
    add_image_size( 'category-card', 600, 450, true );
}
add_action( 'after_setup_theme', 'stew_custom_image_sizes' );

/**
 * Register custom nav menus
 */
function stew_register_menus() {
    register_nav_menus( array(
        'stew_footer_menu'  => __( 'Footer Navigation', 'stew-child' ),
        'stew_catalog_menu' => __( 'Katalog Navigation', 'stew-child' ),
    ) );
}
add_action( 'after_setup_theme', 'stew_register_menus' );

/**
 * "Preis auf Anfrage" — products with price 0 or empty
 */
function stew_price_auf_anfrage( $price, $product ) {
    if ( '' === $product->get_price() || 0 == $product->get_price() ) {
        return '<span class="stew-price-inquiry">Preis auf Anfrage</span>';
    }
    return $price;
}
add_filter( 'woocommerce_get_price_html', 'stew_price_auf_anfrage', 10, 2 );

/**
 * Change "Add to cart" button text for price-on-request products
 */
function stew_add_to_cart_text( $text, $product ) {
    if ( '' === $product->get_price() || 0 == $product->get_price() ) {
        return __( 'Anfragen', 'stew-child' );
    }
    return $text;
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'stew_add_to_cart_text', 10, 2 );
add_filter( 'woocommerce_product_add_to_cart_text', 'stew_add_to_cart_text', 10, 2 );

/**
 * Redirect "Anfragen" button to contact page for price-on-request products
 */
function stew_add_to_cart_url( $url, $product ) {
    if ( '' === $product->get_price() || 0 == $product->get_price() ) {
        return home_url( '/kontakt/?anfrage=' . urlencode( $product->get_name() ) );
    }
    return $url;
}
add_filter( 'woocommerce_product_add_to_cart_url', 'stew_add_to_cart_url', 10, 2 );

/**
 * Remove default WooCommerce sidebar
 */
function stew_remove_wc_sidebar() {
    if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
        remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
    }
}
add_action( 'wp', 'stew_remove_wc_sidebar' );

/**
 * Custom excerpt length for product cards
 */
function stew_custom_excerpt_length( $length ) {
    if ( is_woocommerce() || is_shop() ) {
        return 20;
    }
    return $length;
}
add_filter( 'excerpt_length', 'stew_custom_excerpt_length', 999 );

/**
 * Custom customer roles for tiered pricing
 */
function stew_register_customer_roles() {
    if ( get_option( 'stew_roles_created' ) ) {
        return;
    }

    add_role( 'wholesale', __( 'Händler (Wholesale)', 'stew-child' ), array(
        'read' => true,
    ) );

    add_role( 'vip_partner', __( 'VIP Partner', 'stew-child' ), array(
        'read' => true,
    ) );

    update_option( 'stew_roles_created', true );
}
add_action( 'init', 'stew_register_customer_roles' );

/**
 * Add WooCommerce customer capabilities to custom roles
 */
function stew_add_customer_caps() {
    $customer = get_role( 'customer' );
    if ( ! $customer ) {
        return;
    }

    $roles = array( 'wholesale', 'vip_partner' );
    foreach ( $roles as $role_name ) {
        $role = get_role( $role_name );
        if ( $role ) {
            foreach ( $customer->capabilities as $cap => $granted ) {
                $role->add_cap( $cap, $granted );
            }
        }
    }
}
add_action( 'admin_init', 'stew_add_customer_caps' );

/* Trade pricing notice is handled by import/role-based-pricing.php */

/**
 * ACF JSON save point
 */
function stew_acf_json_save_point( $path ) {
    return STEW_CHILD_DIR . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'stew_acf_json_save_point' );

/**
 * ACF JSON load point
 */
function stew_acf_json_load_point( $paths ) {
    $paths[] = STEW_CHILD_DIR . '/acf-json';
    return $paths;
}
add_filter( 'acf/settings/load_json', 'stew_acf_json_load_point' );

/**
 * Include role-based pricing module
 */
$role_pricing_file = STEW_CHILD_DIR . '/import/role-based-pricing.php';
if ( file_exists( $role_pricing_file ) ) {
    require_once $role_pricing_file;
}

/**
 * Set WooCommerce products per page
 */
function stew_products_per_page( $cols ) {
    return 24;
}
add_filter( 'loop_shop_per_page', 'stew_products_per_page', 20 );

/**
 * Set WooCommerce product columns
 */
function stew_loop_columns() {
    return 3;
}
add_filter( 'loop_shop_columns', 'stew_loop_columns' );

/* WooCommerce default styles are kept; our CSS overrides them via higher priority */

/**
 * Add body classes for STEW styling
 */
function stew_body_classes( $classes ) {
    $classes[] = 'stew-theme';
    if ( is_woocommerce() ) {
        $classes[] = 'stew-woocommerce';
    }
    return $classes;
}
add_filter( 'body_class', 'stew_body_classes' );

/**
 * Dimming type labels
 */
function stew_dimming_label( $value ) {
    $labels = array(
        'not_dimmable' => 'Nicht dimmbar (ON/OFF)',
        'dali'         => 'DALI / DALI 2 dimmbar',
        '1_10v'        => '1-10V dimmbar',
        'casambi'      => 'Casambi BLE',
        'touchdim'     => 'TouchDIM',
        'nfc'          => 'NFC programmierbar',
        'one4all'      => 'one4all',
        'zigbee'       => 'Zigbee',
    );
    return isset( $labels[ $value ] ) ? $labels[ $value ] : $value;
}

/**
 * Product page: Add highlights box after add-to-cart
 */
function stew_product_highlights() {
    global $product;
    $pid = $product->get_id();

    $power   = get_post_meta( $pid, 'power_watts', true );
    $ip      = get_post_meta( $pid, 'ip_protection', true );
    $dimming = get_post_meta( $pid, 'dimming_type', true );
    $voltage = get_post_meta( $pid, 'input_voltage', true );
    $sku     = $product->get_sku();

    $items = array();
    if ( $power )   $items[] = $power . 'W Leistung';
    if ( $ip )      $items[] = 'Schutzart ' . $ip;
    if ( $dimming ) {
        if ( is_array( $dimming ) ) {
            $items[] = implode( ', ', array_map( 'stew_dimming_label', $dimming ) );
        } else {
            $items[] = stew_dimming_label( $dimming );
        }
    }
    if ( $voltage ) $items[] = $voltage;
    if ( $sku )     $items[] = 'Art.-Nr.: ' . $sku;

    if ( empty( $items ) ) return;

    echo '<div class="stew-product-highlights">';
    foreach ( $items as $item ) {
        echo '<div style="display:flex;align-items:center;gap:0.5rem;padding:0.35rem 0;font-size:0.875rem;">';
        echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#C9A96E" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
        echo '<span>' . esc_html( $item ) . '</span>';
        echo '</div>';
    }
    echo '</div>';

    // Datasheet button
    $datasheet = get_post_meta( $pid, 'datasheet_pdf', true );
    if ( $datasheet ) {
        echo '<div style="margin-top:1rem;">';
        echo '<a href="' . esc_url( $datasheet ) . '" target="_blank" rel="noopener" class="stew-datasheet-btn">';
        echo esc_html__( 'Datenblatt herunterladen', 'stew-child' );
        echo '</a></div>';
    }
}
add_action( 'woocommerce_single_product_summary', 'stew_product_highlights', 35 );

/**
 * Product page: Add manufacturer name below title
 */
function stew_product_manufacturer() {
    $manufacturer = get_post_meta( get_the_ID(), 'manufacturer_brand', true );
    if ( $manufacturer ) {
        echo '<p style="color:#737373;font-size:0.875rem;margin:-0.5rem 0 1rem;">' . esc_html( $manufacturer ) . '</p>';
    }
}
add_action( 'woocommerce_single_product_summary', 'stew_product_manufacturer', 6 );

/**
 * Related products: show 3 in one row
 */
function stew_related_products_args( $args ) {
    $args['posts_per_page'] = 3;
    $args['columns']        = 3;
    return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'stew_related_products_args' );

/**
 * Add Technische Spezifikationen tab to WooCommerce product tabs
 */
function stew_product_specs_tab( $tabs ) {
    global $product;
    if ( ! $product ) return $tabs;

    $pid   = $product->get_id();
    $power = get_post_meta( $pid, 'power_watts', true );
    $ip    = get_post_meta( $pid, 'ip_protection', true );

    // Only add tab if product has specs
    if ( $power || $ip ) {
        $tabs['stew_specs'] = array(
            'title'    => __( 'Technische Daten', 'stew-child' ),
            'priority' => 15,
            'callback' => 'stew_product_specs_tab_content',
        );
    }
    return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'stew_product_specs_tab' );

function stew_product_specs_tab_content() {
    global $product;
    $pid = $product->get_id();

    $fields = array(
        'cc_cv_type'           => 'CC/CV Typ',
        'power_watts'          => 'Leistung',
        'output_current_ma'    => 'Ausgangsstrom',
        'output_channels'      => 'Ausgangskanäle',
        'ip_protection'        => 'IP-Schutzart',
        'input_voltage'        => 'Eingangsspannung',
        'series_type'          => 'Serien-Typ',
        'manufacturer_brand'   => 'Hersteller',
        'manufacturer_part_number' => 'Hersteller-Art.-Nr.',
    );

    // Add dimensions if available
    $dim_l = get_post_meta( $pid, 'dimensions_length_mm', true );
    $dim_w = get_post_meta( $pid, 'dimensions_width_mm', true );
    $dim_h = get_post_meta( $pid, 'dimensions_height_mm', true );

    echo '<table class="stew-specs-table" style="width:100%;border-collapse:collapse;">';
    echo '<tbody>';
    foreach ( $fields as $key => $label ) {
        $val = get_post_meta( $pid, $key, true );
        if ( empty( $val ) ) continue;
        if ( is_array( $val ) ) {
            $val = implode( ', ', array_map( 'stew_dimming_label', $val ) );
        }
        if ( $key === 'power_watts' ) $val .= ' W';
        echo '<tr>';
        echo '<th style="padding:0.75rem 1rem;font-size:0.875rem;font-weight:500;color:#737373;text-align:left;width:40%;border-bottom:1px solid #E8E5E0;">' . esc_html( $label ) . '</th>';
        echo '<td style="padding:0.75rem 1rem;font-size:0.875rem;border-bottom:1px solid #E8E5E0;">' . esc_html( $val ) . '</td>';
        echo '</tr>';
    }
    // Dimensions row
    if ( $dim_l && $dim_w && $dim_h ) {
        echo '<tr>';
        echo '<th style="padding:0.75rem 1rem;font-size:0.875rem;font-weight:500;color:#737373;text-align:left;width:40%;border-bottom:1px solid #E8E5E0;">Abmessungen (L×B×H)</th>';
        echo '<td style="padding:0.75rem 1rem;font-size:0.875rem;border-bottom:1px solid #E8E5E0;">' . esc_html( $dim_l . ' × ' . $dim_w . ' × ' . $dim_h . ' mm' ) . '</td>';
        echo '</tr>';
    }
    // Dimming
    $dimming = get_post_meta( $pid, 'dimming_type', true );
    if ( $dimming ) {
        $dim_val = is_array( $dimming ) ? implode( ', ', array_map( 'stew_dimming_label', $dimming ) ) : stew_dimming_label( $dimming );
        echo '<tr>';
        echo '<th style="padding:0.75rem 1rem;font-size:0.875rem;font-weight:500;color:#737373;text-align:left;width:40%;border-bottom:1px solid #E8E5E0;">Dimmung</th>';
        echo '<td style="padding:0.75rem 1rem;font-size:0.875rem;border-bottom:1px solid #E8E5E0;">' . esc_html( $dim_val ) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}
