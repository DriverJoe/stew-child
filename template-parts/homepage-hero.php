<?php
/**
 * Homepage Hero Section
 *
 * @package STEW_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<section class="stew-hero" style="background-image: url('<?php echo esc_url( STEW_CHILD_URI . '/assets/images/hero-lighting.jpg' ); ?>');">
    <div class="stew-hero__overlay"></div>
    <div class="stew-hero__content">
        <h1 class="stew-hero__title">
            <?php esc_html_e( 'Professionelle LED-Beleuchtung für anspruchsvolle Projekte', 'stew-child' ); ?>
        </h1>
        <p class="stew-hero__subtitle">
            <?php esc_html_e( 'Hochwertige Leuchten und LED-Treiber für Architekten, Lichtplaner und Elektroinstallateure', 'stew-child' ); ?>
        </p>
        <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="stew-btn stew-btn--gold">
            <?php esc_html_e( 'Katalog ansehen', 'stew-child' ); ?>
        </a>
    </div>
</section>
