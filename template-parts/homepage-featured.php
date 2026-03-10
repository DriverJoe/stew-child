<?php
/**
 * Homepage Featured Products Section
 *
 * @package STEW_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<section class="stew-section">
    <div class="stew-container">
        <h2 class="stew-section-heading stew-text-center">
            <?php esc_html_e( 'Ausgewählte Produkte', 'stew-child' ); ?>
        </h2>

        <?php
        echo do_shortcode( '[products limit="4" columns="4" visibility="featured"]' );
        ?>

        <div class="stew-text-center stew-mb-3" style="margin-top: 2rem;">
            <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="stew-btn stew-btn--outline">
                <?php esc_html_e( 'Alle Produkte ansehen', 'stew-child' ); ?>
                <svg style="margin-left:0.5rem;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    </div>
</section>
