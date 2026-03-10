/**
 * STEW Custom JavaScript
 *
 * @package STEW_Child
 * @version 1.0.0
 */

(function ($) {
    'use strict';

    /**
     * Smooth scroll for anchor links
     */
    function initSmoothScroll() {
        $('a[href^="#"]').on('click', function (e) {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 600);
            }
        });
    }

    /**
     * FacetWP integration — refresh layout on filter change
     */
    function initFacetWP() {
        if (typeof FWP === 'undefined') return;

        $(document).on('facetwp-loaded', function () {
            // Re-initialize product card hover effects after AJAX load
            initProductCards();

            // Update product count display
            var count = FWP.settings.pager.total_rows || 0;
            $('.stew-shop-topbar__count').text(count + ' Artikel');
        });
    }

    /**
     * Product card hover enhancements
     */
    function initProductCards() {
        $('.stew-product-card, .products li.product').each(function () {
            var $card = $(this);
            $card.off('mouseenter.stew mouseleave.stew');

            $card.on('mouseenter.stew', function () {
                $(this).addClass('stew-card-hover');
            }).on('mouseleave.stew', function () {
                $(this).removeClass('stew-card-hover');
            });
        });
    }

    /**
     * Newsletter form AJAX
     */
    function initNewsletter() {
        $('.stew-newsletter-form').on('submit', function (e) {
            var $form = $(this);
            // Let CF7 handle submission if present
            if ($form.find('.wpcf7-form').length) return;

            e.preventDefault();
            var email = $form.find('input[type="email"]').val();
            if (!email) return;

            var $btn = $form.find('[type="submit"]');
            $btn.prop('disabled', true).text('...');

            $.ajax({
                url: stewAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'stew_newsletter_signup',
                    email: email,
                    nonce: stewAjax.nonce
                },
                success: function () {
                    $form.find('input[type="email"]').val('');
                    $btn.prop('disabled', false).text('Anmelden');
                    alert('Vielen Dank für Ihre Anmeldung!');
                },
                error: function () {
                    $btn.prop('disabled', false).text('Anmelden');
                    alert('Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.');
                }
            });
        });
    }

    /**
     * Mobile filter toggle
     */
    function initMobileFilters() {
        var $toggle = $('<button class="stew-filter-toggle stew-btn stew-btn--outline">Filter anzeigen</button>');
        var $sidebar = $('.stew-filter-sidebar');

        if (!$sidebar.length) return;

        // Only on mobile
        if ($(window).width() >= 1024) return;

        $sidebar.before($toggle);
        $sidebar.hide();

        $toggle.on('click', function () {
            $sidebar.slideToggle(300);
            var text = $sidebar.is(':visible') ? 'Filter ausblenden' : 'Filter anzeigen';
            $(this).text(text);
        });
    }

    /**
     * Initialize on DOM ready
     */
    $(document).ready(function () {
        initSmoothScroll();
        initFacetWP();
        initProductCards();
        initNewsletter();
        initMobileFilters();
    });

})(jQuery);
