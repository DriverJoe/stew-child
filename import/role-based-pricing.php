<?php
/**
 * STEW Webshop — Role-Based Pricing & Wholesale System
 *
 * Funktionen:
 * - Registrierung der Rollen 'wholesale' und 'vip_partner'
 * - Rollenbasierte Preisrabatte (Haendler 15%, VIP 25%)
 * - Hinweis "Einloggen fuer Haendlerpreise" fuer nicht eingeloggte Besucher
 * - Registrierungsformular-Erweiterung (Kontotyp, Firma)
 * - Admin-Benachrichtigung bei neuen Haendler-Registrierungen
 * - Admin-Spalte fuer ausstehende Haendler-Antraege
 * - Admin-Einstellungsseite unter WooCommerce
 *
 * Einbindung in functions.php:
 * require_once get_stylesheet_directory() . '/import/role-based-pricing.php';
 *
 * @package STEW
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ============================================================================
 * 1. ROLLEN REGISTRIERUNG
 * ============================================================================
 */

/**
 * Benutzerdefinierte Rollen bei Theme-Aktivierung registrieren.
 */
function stew_register_custom_roles() {
    // WooCommerce Customer-Capabilities als Basis
    $customer_caps = array(
        'read'                   => true,
        'edit_posts'             => false,
        'delete_posts'           => false,
    );

    // Wholesale-Rolle (Haendler)
    add_role(
        'wholesale',
        __( 'Haendler', 'stew' ),
        $customer_caps
    );

    // VIP-Partner-Rolle
    add_role(
        'vip_partner',
        __( 'VIP-Partner', 'stew' ),
        $customer_caps
    );

    // Pending-Wholesale-Rolle (wartend auf Freigabe)
    add_role(
        'pending_wholesale',
        __( 'Haendler (ausstehend)', 'stew' ),
        $customer_caps
    );
}
add_action( 'after_setup_theme', 'stew_register_custom_roles' );

/**
 * Rollen bei Theme-Deaktivierung entfernen.
 */
function stew_remove_custom_roles() {
    remove_role( 'wholesale' );
    remove_role( 'vip_partner' );
    remove_role( 'pending_wholesale' );
}
add_action( 'switch_theme', 'stew_remove_custom_roles' );


/**
 * ============================================================================
 * 2. ADMIN-EINSTELLUNGSSEITE
 * ============================================================================
 */

/**
 * Einstellungsseite unter WooCommerce registrieren.
 */
function stew_add_pricing_settings_page() {
    add_submenu_page(
        'woocommerce',
        __( 'Rollenbasierte Preise', 'stew' ),
        __( 'Rollenbasierte Preise', 'stew' ),
        'manage_woocommerce',
        'stew-role-pricing',
        'stew_render_pricing_settings_page'
    );
}
add_action( 'admin_menu', 'stew_add_pricing_settings_page' );

/**
 * Einstellungen registrieren.
 */
function stew_register_pricing_settings() {
    register_setting( 'stew_pricing_options', 'stew_wholesale_discount', array(
        'type'              => 'number',
        'sanitize_callback' => 'absint',
        'default'           => 15,
    ) );
    register_setting( 'stew_pricing_options', 'stew_vip_discount', array(
        'type'              => 'number',
        'sanitize_callback' => 'absint',
        'default'           => 25,
    ) );
    register_setting( 'stew_pricing_options', 'stew_trade_notice_enabled', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => 'yes',
    ) );
    register_setting( 'stew_pricing_options', 'stew_trade_notice_text', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => 'Einloggen fuer Haendlerpreise',
    ) );
    register_setting( 'stew_pricing_options', 'stew_admin_notify_email', array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_email',
        'default'           => get_option( 'admin_email' ),
    ) );
}
add_action( 'admin_init', 'stew_register_pricing_settings' );

/**
 * Admin-Einstellungsseite rendern.
 */
function stew_render_pricing_settings_page() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( esc_html__( 'Keine Berechtigung.', 'stew' ) );
    }

    $wholesale_discount   = get_option( 'stew_wholesale_discount', 15 );
    $vip_discount         = get_option( 'stew_vip_discount', 25 );
    $trade_notice_enabled = get_option( 'stew_trade_notice_enabled', 'yes' );
    $trade_notice_text    = get_option( 'stew_trade_notice_text', 'Einloggen fuer Haendlerpreise' );
    $admin_notify_email   = get_option( 'stew_admin_notify_email', get_option( 'admin_email' ) );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__( 'Rollenbasierte Preise — STEW', 'stew' ); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields( 'stew_pricing_options' ); ?>
            <?php wp_nonce_field( 'stew_pricing_nonce_action', 'stew_pricing_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="stew_wholesale_discount">
                            <?php echo esc_html__( 'Haendler-Rabatt (%)', 'stew' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number" id="stew_wholesale_discount" name="stew_wholesale_discount"
                               value="<?php echo esc_attr( $wholesale_discount ); ?>"
                               min="0" max="100" step="1" class="small-text" />
                        <p class="description">
                            <?php echo esc_html__( 'Prozentualer Rabatt fuer Haendler (Standard: 15%)', 'stew' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="stew_vip_discount">
                            <?php echo esc_html__( 'VIP-Partner-Rabatt (%)', 'stew' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number" id="stew_vip_discount" name="stew_vip_discount"
                               value="<?php echo esc_attr( $vip_discount ); ?>"
                               min="0" max="100" step="1" class="small-text" />
                        <p class="description">
                            <?php echo esc_html__( 'Prozentualer Rabatt fuer VIP-Partner (Standard: 25%)', 'stew' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="stew_trade_notice_enabled">
                            <?php echo esc_html__( 'Haendlerhinweis anzeigen', 'stew' ); ?>
                        </label>
                    </th>
                    <td>
                        <select id="stew_trade_notice_enabled" name="stew_trade_notice_enabled">
                            <option value="yes" <?php selected( $trade_notice_enabled, 'yes' ); ?>>
                                <?php echo esc_html__( 'Ja', 'stew' ); ?>
                            </option>
                            <option value="no" <?php selected( $trade_notice_enabled, 'no' ); ?>>
                                <?php echo esc_html__( 'Nein', 'stew' ); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php echo esc_html__( 'Zeigt einen Hinweis auf Shop- und Produktseiten fuer nicht eingeloggte Besucher.', 'stew' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="stew_trade_notice_text">
                            <?php echo esc_html__( 'Hinweistext', 'stew' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" id="stew_trade_notice_text" name="stew_trade_notice_text"
                               value="<?php echo esc_attr( $trade_notice_text ); ?>"
                               class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="stew_admin_notify_email">
                            <?php echo esc_html__( 'Benachrichtigungs-E-Mail', 'stew' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="email" id="stew_admin_notify_email" name="stew_admin_notify_email"
                               value="<?php echo esc_attr( $admin_notify_email ); ?>"
                               class="regular-text" />
                        <p class="description">
                            <?php echo esc_html__( 'E-Mail-Adresse fuer Benachrichtigungen bei neuen Haendler-Registrierungen.', 'stew' ); ?>
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button( __( 'Einstellungen speichern', 'stew' ) ); ?>
        </form>

        <hr />
        <h2><?php echo esc_html__( 'Ausstehende Haendler-Antraege', 'stew' ); ?></h2>
        <?php stew_render_pending_wholesale_table(); ?>
    </div>
    <?php
}

/**
 * Tabelle der ausstehenden Haendler-Antraege rendern.
 */
function stew_render_pending_wholesale_table() {
    $pending_users = get_users( array(
        'role'    => 'pending_wholesale',
        'orderby' => 'registered',
        'order'   => 'DESC',
    ) );

    if ( empty( $pending_users ) ) {
        echo '<p>' . esc_html__( 'Keine ausstehenden Antraege.', 'stew' ) . '</p>';
        return;
    }
    ?>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php echo esc_html__( 'Name', 'stew' ); ?></th>
                <th><?php echo esc_html__( 'E-Mail', 'stew' ); ?></th>
                <th><?php echo esc_html__( 'Firma', 'stew' ); ?></th>
                <th><?php echo esc_html__( 'Registriert am', 'stew' ); ?></th>
                <th><?php echo esc_html__( 'Aktionen', 'stew' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $pending_users as $user ) : ?>
                <tr>
                    <td><?php echo esc_html( $user->display_name ); ?></td>
                    <td><?php echo esc_html( $user->user_email ); ?></td>
                    <td><?php echo esc_html( get_user_meta( $user->ID, 'billing_company', true ) ); ?></td>
                    <td><?php echo esc_html( wp_date( 'd.m.Y H:i', strtotime( $user->user_registered ) ) ); ?></td>
                    <td>
                        <?php
                        $approve_url = wp_nonce_url(
                            admin_url( 'admin-post.php?action=stew_approve_wholesale&user_id=' . $user->ID ),
                            'stew_approve_wholesale_' . $user->ID,
                            'stew_nonce'
                        );
                        $reject_url = wp_nonce_url(
                            admin_url( 'admin-post.php?action=stew_reject_wholesale&user_id=' . $user->ID ),
                            'stew_reject_wholesale_' . $user->ID,
                            'stew_nonce'
                        );
                        ?>
                        <a href="<?php echo esc_url( $approve_url ); ?>" class="button button-primary button-small">
                            <?php echo esc_html__( 'Freigeben', 'stew' ); ?>
                        </a>
                        <a href="<?php echo esc_url( $reject_url ); ?>" class="button button-small"
                           onclick="return confirm('<?php echo esc_js( __( 'Antrag wirklich ablehnen?', 'stew' ) ); ?>');">
                            <?php echo esc_html__( 'Ablehnen', 'stew' ); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
}


/**
 * ============================================================================
 * 3. ADMIN-AKTIONEN: HAENDLER FREIGEBEN / ABLEHNEN
 * ============================================================================
 */

/**
 * Haendler-Antrag freigeben.
 */
function stew_handle_approve_wholesale() {
    $user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;

    if ( ! $user_id || ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( esc_html__( 'Keine Berechtigung.', 'stew' ) );
    }

    if ( ! isset( $_GET['stew_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['stew_nonce'] ) ), 'stew_approve_wholesale_' . $user_id ) ) {
        wp_die( esc_html__( 'Sicherheitspruefung fehlgeschlagen.', 'stew' ) );
    }

    $user = get_userdata( $user_id );
    if ( ! $user ) {
        wp_die( esc_html__( 'Benutzer nicht gefunden.', 'stew' ) );
    }

    // Rolle aendern
    $user->set_role( 'wholesale' );
    update_user_meta( $user_id, 'stew_wholesale_approved', current_time( 'mysql' ) );
    update_user_meta( $user_id, 'stew_wholesale_approved_by', get_current_user_id() );

    // Benachrichtigung an den Benutzer
    $subject = __( 'Ihr Haendlerkonto wurde freigeschaltet — STEW', 'stew' );
    $message = sprintf(
        /* translators: %s: user display name */
        __(
            "Guten Tag %s,\n\n" .
            "Ihr Haendlerkonto bei STEW wurde freigeschaltet. Sie profitieren ab sofort von unseren Haendlerpreisen.\n\n" .
            "Loggen Sie sich ein, um die reduzierten Preise zu sehen:\n" .
            "%s\n\n" .
            "Freundliche Gruesse\n" .
            "Ihr STEW Team",
            'stew'
        ),
        $user->display_name,
        wp_login_url( wc_get_page_permalink( 'shop' ) )
    );
    wp_mail( $user->user_email, $subject, $message );

    wp_safe_redirect( admin_url( 'admin.php?page=stew-role-pricing&approved=1' ) );
    exit;
}
add_action( 'admin_post_stew_approve_wholesale', 'stew_handle_approve_wholesale' );

/**
 * Haendler-Antrag ablehnen.
 */
function stew_handle_reject_wholesale() {
    $user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;

    if ( ! $user_id || ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( esc_html__( 'Keine Berechtigung.', 'stew' ) );
    }

    if ( ! isset( $_GET['stew_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['stew_nonce'] ) ), 'stew_reject_wholesale_' . $user_id ) ) {
        wp_die( esc_html__( 'Sicherheitspruefung fehlgeschlagen.', 'stew' ) );
    }

    $user = get_userdata( $user_id );
    if ( ! $user ) {
        wp_die( esc_html__( 'Benutzer nicht gefunden.', 'stew' ) );
    }

    // Zurueck auf Customer-Rolle setzen
    $user->set_role( 'customer' );
    update_user_meta( $user_id, 'stew_wholesale_rejected', current_time( 'mysql' ) );

    // Benachrichtigung an den Benutzer
    $subject = __( 'Ihr Haendlerantrag bei STEW', 'stew' );
    $message = sprintf(
        /* translators: %s: user display name */
        __(
            "Guten Tag %s,\n\n" .
            "Leider konnten wir Ihren Haendlerantrag nicht freigeben. " .
            "Bitte kontaktieren Sie uns fuer weitere Informationen:\n" .
            "info@stew.ch\n\n" .
            "Freundliche Gruesse\n" .
            "Ihr STEW Team",
            'stew'
        ),
        $user->display_name
    );
    wp_mail( $user->user_email, $subject, $message );

    wp_safe_redirect( admin_url( 'admin.php?page=stew-role-pricing&rejected=1' ) );
    exit;
}
add_action( 'admin_post_stew_reject_wholesale', 'stew_handle_reject_wholesale' );

/**
 * Admin-Notices fuer Freigabe/Ablehnung.
 */
function stew_admin_notices_wholesale() {
    $screen = get_current_screen();
    if ( ! $screen || 'woocommerce_page_stew-role-pricing' !== $screen->id ) {
        return;
    }

    if ( isset( $_GET['approved'] ) && '1' === $_GET['approved'] ) {
        echo '<div class="notice notice-success is-dismissible"><p>';
        echo esc_html__( 'Haendlerkonto wurde erfolgreich freigeschaltet.', 'stew' );
        echo '</p></div>';
    }

    if ( isset( $_GET['rejected'] ) && '1' === $_GET['rejected'] ) {
        echo '<div class="notice notice-warning is-dismissible"><p>';
        echo esc_html__( 'Haendlerantrag wurde abgelehnt.', 'stew' );
        echo '</p></div>';
    }
}
add_action( 'admin_notices', 'stew_admin_notices_wholesale' );


/**
 * ============================================================================
 * 4. PREISFILTER — ROLLENBASIERTE RABATTE
 * ============================================================================
 */

/**
 * Rabattprozentsatz fuer die aktuelle Benutzerrolle ermitteln.
 *
 * @return float Rabatt in Prozent (0-100).
 */
function stew_get_role_discount() {
    if ( ! is_user_logged_in() ) {
        return 0;
    }

    $user  = wp_get_current_user();
    $roles = (array) $user->roles;

    if ( in_array( 'vip_partner', $roles, true ) ) {
        return floatval( get_option( 'stew_vip_discount', 25 ) );
    }

    if ( in_array( 'wholesale', $roles, true ) ) {
        return floatval( get_option( 'stew_wholesale_discount', 15 ) );
    }

    return 0;
}

/**
 * Produkt-Preis mit Rabatt berechnen.
 *
 * @param float $price Originalpreis.
 * @return float Reduzierter Preis.
 */
function stew_calculate_discounted_price( $price ) {
    $discount = stew_get_role_discount();
    if ( $discount > 0 && $price > 0 ) {
        $price = $price * ( 1 - ( $discount / 100 ) );
        $price = round( $price, 2 );
    }
    return $price;
}

/**
 * WooCommerce Preis-Hooks: Einzelprodukt-Preis filtern.
 *
 * @param string     $price   Der Preis.
 * @param WC_Product $product Das Produkt.
 * @return string Gefilterter Preis.
 */
function stew_filter_product_price( $price, $product ) {
    if ( is_admin() && ! wp_doing_ajax() ) {
        return $price;
    }

    $discount = stew_get_role_discount();
    if ( $discount <= 0 ) {
        return $price;
    }

    // Sale-Preis nicht doppelt rabattieren
    if ( $product->is_on_sale() ) {
        return $price;
    }

    return stew_calculate_discounted_price( floatval( $price ) );
}
add_filter( 'woocommerce_product_get_price', 'stew_filter_product_price', 99, 2 );
add_filter( 'woocommerce_product_get_regular_price', 'stew_filter_product_price', 99, 2 );

/**
 * Variationspreise filtern.
 *
 * @param string              $price     Der Preis.
 * @param WC_Product_Variation $variation Die Variation.
 * @param WC_Product_Variable  $product   Das Produkt.
 * @return string Gefilterter Preis.
 */
function stew_filter_variation_price( $price, $variation, $product ) {
    if ( is_admin() && ! wp_doing_ajax() ) {
        return $price;
    }

    $discount = stew_get_role_discount();
    if ( $discount <= 0 ) {
        return $price;
    }

    if ( $variation->is_on_sale() ) {
        return $price;
    }

    return stew_calculate_discounted_price( floatval( $price ) );
}
add_filter( 'woocommerce_product_variation_get_price', 'stew_filter_variation_price', 99, 3 );
add_filter( 'woocommerce_product_variation_get_regular_price', 'stew_filter_variation_price', 99, 3 );

/**
 * Variationspreis-Hash anpassen fuer korrektes Caching.
 *
 * @param array      $hash    Der Preis-Hash.
 * @param WC_Product $product Das Produkt.
 * @return array Angepasster Hash.
 */
function stew_variation_prices_hash( $hash, $product ) {
    $discount = stew_get_role_discount();
    if ( $discount > 0 ) {
        $hash[] = 'stew_discount_' . $discount;
    }
    return $hash;
}
add_filter( 'woocommerce_get_variation_prices_hash', 'stew_variation_prices_hash', 99, 2 );

/**
 * Rabatt-Hinweis unter dem Preis auf der Einzelproduktseite anzeigen.
 */
function stew_display_discount_badge() {
    if ( ! is_user_logged_in() ) {
        return;
    }

    $discount = stew_get_role_discount();
    if ( $discount <= 0 ) {
        return;
    }

    $user  = wp_get_current_user();
    $roles = (array) $user->roles;

    $label = '';
    if ( in_array( 'vip_partner', $roles, true ) ) {
        $label = __( 'VIP-Partner-Preis', 'stew' );
    } elseif ( in_array( 'wholesale', $roles, true ) ) {
        $label = __( 'Haendlerpreis', 'stew' );
    }

    if ( $label ) {
        printf(
            '<div class="stew-discount-badge"><span class="stew-discount-badge__label">%s</span> <span class="stew-discount-badge__value">-%s%%</span></div>',
            esc_html( $label ),
            esc_html( $discount )
        );
    }
}
add_action( 'woocommerce_single_product_summary', 'stew_display_discount_badge', 11 );

/**
 * Warenkorb-Preis mit Rabatt anpassen (fuer korrekte Berechnung).
 *
 * @param WC_Cart $cart Der Warenkorb.
 */
function stew_adjust_cart_prices( $cart ) {
    if ( is_admin() && ! wp_doing_ajax() ) {
        return;
    }

    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
        return;
    }

    $discount = stew_get_role_discount();
    if ( $discount <= 0 ) {
        return;
    }

    foreach ( $cart->get_cart() as $cart_item ) {
        $product = $cart_item['data'];

        // Sale-Preis nicht doppelt rabattieren
        if ( $product->is_on_sale() ) {
            continue;
        }

        $original_price   = floatval( $product->get_regular_price() );
        $discounted_price = stew_calculate_discounted_price( $original_price );
        $cart_item['data']->set_price( $discounted_price );
    }
}
add_action( 'woocommerce_before_calculate_totals', 'stew_adjust_cart_prices', 99 );


/**
 * ============================================================================
 * 5. HAENDLERHINWEIS FUER NICHT EINGELOGGTE BESUCHER
 * ============================================================================
 */

/**
 * Hinweis "Einloggen fuer Haendlerpreise" auf Shop- und Produktseiten anzeigen.
 */
function stew_display_trade_pricing_notice() {
    // Nur fuer nicht eingeloggte Besucher
    if ( is_user_logged_in() ) {
        return;
    }

    // Pruefen ob aktiviert
    if ( 'yes' !== get_option( 'stew_trade_notice_enabled', 'yes' ) ) {
        return;
    }

    // Nur auf Shop- und Produktseiten
    if ( ! is_shop() && ! is_product() && ! is_product_category() && ! is_product_tag() ) {
        return;
    }

    $notice_text = get_option( 'stew_trade_notice_text', 'Einloggen fuer Haendlerpreise' );
    $login_url   = wc_get_page_permalink( 'myaccount' );

    printf(
        '<div class="stew-trade-notice">'
        . '<a href="%s" class="stew-trade-notice__link">'
        . '<span class="stew-trade-notice__icon">&#128274;</span> '
        . '<span class="stew-trade-notice__text">%s</span>'
        . '</a>'
        . '</div>',
        esc_url( $login_url ),
        esc_html( $notice_text )
    );
}
add_action( 'woocommerce_before_shop_loop', 'stew_display_trade_pricing_notice', 5 );
add_action( 'woocommerce_single_product_summary', 'stew_display_trade_pricing_notice', 6 );


/**
 * ============================================================================
 * 6. REGISTRIERUNGSFORMULAR-ERWEITERUNG
 * ============================================================================
 */

/**
 * Zusaetzliche Felder im WooCommerce-Registrierungsformular.
 */
function stew_registration_form_fields() {
    ?>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="stew_account_type">
            <?php echo esc_html__( 'Kontotyp', 'stew' ); ?>&nbsp;<span class="required">*</span>
        </label>
        <select name="stew_account_type" id="stew_account_type" class="woocommerce-Input woocommerce-Input--select input-select" required>
            <option value="private"><?php echo esc_html__( 'Privatkunde', 'stew' ); ?></option>
            <option value="wholesale"><?php echo esc_html__( 'Haendler / Gewerbe', 'stew' ); ?></option>
        </select>
    </p>

    <div id="stew-company-fields" style="display:none;">
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="stew_company_name">
                <?php echo esc_html__( 'Firmenname', 'stew' ); ?>&nbsp;<span class="required">*</span>
            </label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
                   name="stew_company_name" id="stew_company_name"
                   placeholder="<?php echo esc_attr__( 'Firmenname', 'stew' ); ?>" />
        </p>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="stew_company_uid">
                <?php echo esc_html__( 'UID-Nummer (optional)', 'stew' ); ?>
            </label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
                   name="stew_company_uid" id="stew_company_uid"
                   placeholder="<?php echo esc_attr__( 'CHE-XXX.XXX.XXX', 'stew' ); ?>" />
        </p>
    </div>

    <script>
    (function() {
        var select = document.getElementById('stew_account_type');
        var fields = document.getElementById('stew-company-fields');
        if (select && fields) {
            select.addEventListener('change', function() {
                fields.style.display = this.value === 'wholesale' ? 'block' : 'none';
                var companyInput = document.getElementById('stew_company_name');
                if (companyInput) {
                    companyInput.required = this.value === 'wholesale';
                }
            });
        }
    })();
    </script>
    <?php
}
add_action( 'woocommerce_register_form', 'stew_registration_form_fields' );

/**
 * Registrierungsformular-Felder validieren.
 *
 * @param WP_Error $errors Validierungsfehler.
 * @param string   $username Benutzername.
 * @param string   $email E-Mail.
 * @return WP_Error
 */
function stew_validate_registration_fields( $errors, $username, $email ) {
    $account_type = isset( $_POST['stew_account_type'] ) ? sanitize_text_field( wp_unslash( $_POST['stew_account_type'] ) ) : 'private';

    if ( 'wholesale' === $account_type ) {
        $company_name = isset( $_POST['stew_company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['stew_company_name'] ) ) : '';
        if ( empty( $company_name ) ) {
            $errors->add(
                'stew_company_required',
                __( 'Bitte geben Sie Ihren Firmennamen an.', 'stew' )
            );
        }
    }

    return $errors;
}
add_filter( 'woocommerce_registration_errors', 'stew_validate_registration_fields', 10, 3 );

/**
 * Registrierungsdaten speichern.
 *
 * @param int $user_id Benutzer-ID.
 */
function stew_save_registration_fields( $user_id ) {
    $account_type = isset( $_POST['stew_account_type'] ) ? sanitize_text_field( wp_unslash( $_POST['stew_account_type'] ) ) : 'private';

    update_user_meta( $user_id, 'stew_account_type', $account_type );

    if ( 'wholesale' === $account_type ) {
        $company_name = isset( $_POST['stew_company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['stew_company_name'] ) ) : '';
        $company_uid  = isset( $_POST['stew_company_uid'] ) ? sanitize_text_field( wp_unslash( $_POST['stew_company_uid'] ) ) : '';

        update_user_meta( $user_id, 'billing_company', $company_name );
        update_user_meta( $user_id, 'stew_company_uid', $company_uid );

        // Rolle auf pending_wholesale setzen
        $user = new WP_User( $user_id );
        $user->set_role( 'pending_wholesale' );

        // Admin benachrichtigen
        stew_notify_admin_new_wholesale( $user_id, $company_name );
    }
}
add_action( 'woocommerce_created_customer', 'stew_save_registration_fields' );


/**
 * ============================================================================
 * 7. ADMIN-BENACHRICHTIGUNG BEI NEUEN HAENDLER-REGISTRIERUNGEN
 * ============================================================================
 */

/**
 * Admin per E-Mail ueber neue Haendler-Registrierung benachrichtigen.
 *
 * @param int    $user_id      Benutzer-ID.
 * @param string $company_name Firmenname.
 */
function stew_notify_admin_new_wholesale( $user_id, $company_name ) {
    $admin_email = get_option( 'stew_admin_notify_email', get_option( 'admin_email' ) );
    $user        = get_userdata( $user_id );

    if ( ! $user ) {
        return;
    }

    $subject = sprintf(
        /* translators: %s: company name */
        __( '[STEW] Neuer Haendlerantrag: %s', 'stew' ),
        $company_name
    );

    $approve_url = admin_url( 'admin.php?page=stew-role-pricing' );

    $message = sprintf(
        __(
            "Neuer Haendlerantrag eingegangen:\n\n" .
            "Name: %1\$s\n" .
            "E-Mail: %2\$s\n" .
            "Firma: %3\$s\n" .
            "UID: %4\$s\n" .
            "Registriert: %5\$s\n\n" .
            "Antrag pruefen und freigeben:\n%6\$s",
            'stew'
        ),
        $user->display_name,
        $user->user_email,
        $company_name,
        get_user_meta( $user_id, 'stew_company_uid', true ),
        wp_date( 'd.m.Y H:i', strtotime( $user->user_registered ) ),
        $approve_url
    );

    wp_mail( $admin_email, $subject, $message );
}


/**
 * ============================================================================
 * 8. ADMIN-BENUTZERLISTE: SPALTEN ERWEITERN
 * ============================================================================
 */

/**
 * Spalte "Kontotyp" zur Benutzerliste hinzufuegen.
 *
 * @param array $columns Spalten.
 * @return array Erweiterte Spalten.
 */
function stew_add_user_columns( $columns ) {
    $columns['stew_account_type'] = __( 'Kontotyp', 'stew' );
    $columns['stew_company']      = __( 'Firma', 'stew' );
    return $columns;
}
add_filter( 'manage_users_columns', 'stew_add_user_columns' );

/**
 * Spalteninhalt fuer "Kontotyp" und "Firma" rendern.
 *
 * @param string $value       Aktueller Wert.
 * @param string $column_name Spaltenname.
 * @param int    $user_id     Benutzer-ID.
 * @return string Spalteninhalt.
 */
function stew_render_user_columns( $value, $column_name, $user_id ) {
    if ( 'stew_account_type' === $column_name ) {
        $user  = get_userdata( $user_id );
        $roles = (array) $user->roles;

        if ( in_array( 'vip_partner', $roles, true ) ) {
            return '<span style="color:#d4af37;font-weight:bold;">' . esc_html__( 'VIP-Partner', 'stew' ) . '</span>';
        }
        if ( in_array( 'wholesale', $roles, true ) ) {
            return '<span style="color:#2196f3;font-weight:bold;">' . esc_html__( 'Haendler', 'stew' ) . '</span>';
        }
        if ( in_array( 'pending_wholesale', $roles, true ) ) {
            return '<span style="color:#ff9800;font-weight:bold;">' . esc_html__( 'Haendler (ausstehend)', 'stew' ) . '</span>';
        }
        return esc_html__( 'Privatkunde', 'stew' );
    }

    if ( 'stew_company' === $column_name ) {
        $company = get_user_meta( $user_id, 'billing_company', true );
        return $company ? esc_html( $company ) : '—';
    }

    return $value;
}
add_filter( 'manage_users_custom_column', 'stew_render_user_columns', 10, 3 );

/**
 * Spalten sortierbar machen.
 *
 * @param array $columns Sortierbare Spalten.
 * @return array Erweiterte Spalten.
 */
function stew_sortable_user_columns( $columns ) {
    $columns['stew_account_type'] = 'stew_account_type';
    $columns['stew_company']      = 'stew_company';
    return $columns;
}
add_filter( 'manage_users_sortable_columns', 'stew_sortable_user_columns' );


/**
 * ============================================================================
 * 9. ADMIN-DASHBOARD: AUSSTEHENDE ANTRAEGE ANZEIGEN
 * ============================================================================
 */

/**
 * Dashboard-Widget fuer ausstehende Haendler-Antraege.
 */
function stew_add_dashboard_widget() {
    wp_add_dashboard_widget(
        'stew_pending_wholesale',
        __( 'Ausstehende Haendler-Antraege', 'stew' ),
        'stew_render_dashboard_widget'
    );
}
add_action( 'wp_dashboard_setup', 'stew_add_dashboard_widget' );

/**
 * Dashboard-Widget-Inhalt rendern.
 */
function stew_render_dashboard_widget() {
    $pending_users = get_users( array(
        'role'       => 'pending_wholesale',
        'number'     => 10,
        'orderby'    => 'registered',
        'order'      => 'DESC',
    ) );

    $count = count( get_users( array( 'role' => 'pending_wholesale', 'fields' => 'ID' ) ) );

    if ( empty( $pending_users ) ) {
        echo '<p>' . esc_html__( 'Keine ausstehenden Antraege.', 'stew' ) . '</p>';
        return;
    }

    printf(
        '<p><strong>%s</strong></p>',
        /* translators: %d: number of pending applications */
        sprintf( esc_html__( '%d Antrag/Antraege ausstehend', 'stew' ), $count )
    );

    echo '<ul>';
    foreach ( $pending_users as $user ) {
        $company = get_user_meta( $user->ID, 'billing_company', true );
        printf(
            '<li><strong>%s</strong> (%s) — %s</li>',
            esc_html( $user->display_name ),
            esc_html( $user->user_email ),
            $company ? esc_html( $company ) : esc_html__( 'Keine Firma', 'stew' )
        );
    }
    echo '</ul>';

    printf(
        '<p><a href="%s" class="button">%s</a></p>',
        esc_url( admin_url( 'admin.php?page=stew-role-pricing' ) ),
        esc_html__( 'Alle Antraege ansehen', 'stew' )
    );
}


/**
 * ============================================================================
 * 10. ZAHLUNGSMETHODE "RECHNUNG" NUR FUER HAENDLER
 * ============================================================================
 */

/**
 * Zahlungsmethode "Rechnung" nur fuer Wholesale/VIP-Rollen anzeigen.
 *
 * @param array $available_gateways Verfuegbare Zahlungsmethoden.
 * @return array Gefilterte Zahlungsmethoden.
 */
function stew_restrict_invoice_payment( $available_gateways ) {
    if ( is_admin() ) {
        return $available_gateways;
    }

    // "Rechnung" (cheque) nur fuer Haendler und VIP-Partner
    if ( isset( $available_gateways['cheque'] ) ) {
        if ( ! is_user_logged_in() ) {
            unset( $available_gateways['cheque'] );
            return $available_gateways;
        }

        $user  = wp_get_current_user();
        $roles = (array) $user->roles;

        $allowed_roles = array( 'wholesale', 'vip_partner', 'administrator' );
        $has_access    = ! empty( array_intersect( $roles, $allowed_roles ) );

        if ( ! $has_access ) {
            unset( $available_gateways['cheque'] );
        }
    }

    return $available_gateways;
}
add_filter( 'woocommerce_available_payment_gateways', 'stew_restrict_invoice_payment' );


/**
 * ============================================================================
 * 11. STYLING
 * ============================================================================
 */

/**
 * Inline-CSS fuer den Haendlerhinweis und Rabatt-Badge.
 */
function stew_role_pricing_inline_styles() {
    if ( ! is_shop() && ! is_product() && ! is_product_category() && ! is_product_tag() ) {
        return;
    }

    $css = '
        .stew-trade-notice {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 8px;
            padding: 12px 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .stew-trade-notice__link {
            color: #00d4ff !important;
            text-decoration: none !important;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.2s ease;
        }
        .stew-trade-notice__link:hover {
            opacity: 0.85;
        }
        .stew-discount-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
            color: #fff;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 8px;
        }
        .stew-discount-badge__value {
            background: rgba(0,0,0,0.2);
            padding: 2px 8px;
            border-radius: 3px;
        }
    ';

    wp_add_inline_style( 'woocommerce-general', $css );
}
add_action( 'wp_enqueue_scripts', 'stew_role_pricing_inline_styles', 99 );
