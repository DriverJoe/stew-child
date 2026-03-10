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

define( 'STEW_CHILD_VERSION', '1.0.0' );
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
add_action( 'wp_enqueue_scripts', 'stew_enqueue_assets', 20 );

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

/**
 * Trade pricing login prompt for non-logged-in users
 */
function stew_trade_pricing_notice() {
    if ( ! is_user_logged_in() && ( is_shop() || is_product_category() || is_product() ) ) {
        echo '<div class="stew-trade-notice">';
        echo '<p><a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">';
        echo esc_html__( 'Einloggen für Händlerpreise', 'stew-child' );
        echo '</a></p></div>';
    }
}
add_action( 'woocommerce_before_shop_loop', 'stew_trade_pricing_notice', 5 );
add_action( 'woocommerce_single_product_summary', 'stew_trade_pricing_notice', 15 );

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

/**
 * Disable WooCommerce default styles selectively
 */
function stew_dequeue_wc_styles( $enqueue_styles ) {
    // Keep core WooCommerce styles but let our CSS override
    return $enqueue_styles;
}
add_filter( 'woocommerce_enqueue_styles', 'stew_dequeue_wc_styles' );

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
