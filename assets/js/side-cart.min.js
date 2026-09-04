/**
 * PerfectWelding — Side Cart Drawer JS
 * Fixed version: hooks into existing pwAddToCart & pwAddToCartDetail
 */

(function ($) {
    'use strict';

    if (typeof pw_cart_vars === 'undefined') return;

    /* ─── SELECTORS & HELPERS ───────────────────────────────── */
    function getCart() { return $('#pw-side-cart'); }
    function getOverlay() { return $('#pw-cart-overlay'); }

    /* ─── OPEN / CLOSE ───────────────────────────────────────── */
    function isCartOrCheckoutPage() {
        return $('body').hasClass('woocommerce-cart') || 
               $('body').hasClass('woocommerce-checkout') || 
               window.location.pathname.indexOf('/cart') !== -1 || 
               window.location.pathname.indexOf('/checkout') !== -1;
    }

    function openCart() {
        if (isCartOrCheckoutPage()) return;
        var $c = getCart();
        var $o = getOverlay();
        if (!$c.length) return;
        $c.addClass('is-open');
        $o.addClass('is-active');
        $('body').addClass('pw-cart-open');
        setTimeout(function () { $('#pw-cart-close').trigger('focus'); }, 360);
    }

    function closeCart() {
        getCart().removeClass('is-open');
        getOverlay().removeClass('is-active');
        $('body').removeClass('pw-cart-open');
    }

    function isOpen() { return getCart().hasClass('is-open'); }

    /* ─── LOADING ────────────────────────────────────────────── */
    function startLoading() {
        $('#pw-side-cart-body').addClass('is-loading');
        if (!$('#pw-sc-spinner').length) {
            $('#pw-side-cart-items').after('<div class="pw-side-cart__spinner" id="pw-sc-spinner"></div>');
        }
    }

    function stopLoading() {
        $('#pw-side-cart-body').removeClass('is-loading');
        $('#pw-sc-spinner').remove();
    }

    /* ─── BADGE COUNT ────────────────────────────────────────── */
    function bumpCount(count) {
        $('.cart-badge, #pw-side-cart-count').text(count);
        $('#pw-side-cart-count').addClass('bump');
        setTimeout(function () { $('#pw-side-cart-count').removeClass('bump'); }, 300);
    }

    /* ─── TOAST ──────────────────────────────────────────────── */
    function showToast(message, link) {
        var $toast = $('.pw-toast');
        if (!$toast.length) {
            $toast = $('<div class="pw-toast"></div>').appendTo('body');
        }
        var linkHtml = link ? ' <a href="' + link + '">' + pw_cart_vars.i18n.view_cart + ' →</a>' : '';
        $toast.html('<span class="pw-toast-icon">✓</span><span>' + message + linkHtml + '</span>').addClass('show');
        clearTimeout($toast.data('t'));
        $toast.data('t', setTimeout(function () { $toast.removeClass('show'); }, 3800));
    }

    /* ─── REFRESH CART CONTENTS ──────────────────────────────── */
    function renderCartData(d) {
        $('#pw-side-cart-items').html(d.items_html);
        $('#pw-side-cart-subtotal').html(d.subtotal);
        bumpCount(d.count);

        if (d.count > 0) {
            $('#pw-side-cart-empty').hide();
            $('#pw-side-cart-items').show();
            $('#pw-side-cart-footer').show();
        } else {
            $('#pw-side-cart-empty').show();
            $('#pw-side-cart-items').hide();
            $('#pw-side-cart-footer').hide();
        }

        // Only translate if the visitor isn't on the default (Dutch) language,
        // and only within the cart panel — not the whole page. This used to
        // call the full-page translation walk on every cart change.
        var currentLang = window.pwCurrentLanguage ? window.pwCurrentLanguage() : 'nl';
        if (currentLang !== 'nl' && typeof window.pwTranslateScope === 'function') {
            window.pwTranslateScope(document.getElementById('pw-side-cart'), currentLang);
        }
    }

    function refreshCart(callback) {
        startLoading();
        $.ajax({
            url:  pw_cart_vars.ajax_url,
            type: 'POST',
            data: { action: 'pw_get_cart_html', nonce: pw_cart_vars.nonce },
            success: function (res) {
                stopLoading();
                if (res.success && res.data) renderCartData(res.data);
                if (typeof callback === 'function') callback();
            },
            error: stopLoading
        });
    }

    /* ─── OVERRIDE pwAddToCart (defined in main.js) ──────────── */
    // Wait for DOM ready so main.js has already defined the function
    $(document).ready(function () {

        // Save original if exists
        var _originalAddToCart = window.pwAddToCart;
        var _originalAddToCartDetail = window.pwAddToCartDetail;

        // Override pwAddToCart (used by shop loop buttons: onclick="pwAddToCart(this)")
        window.pwAddToCart = function (btn) {
            if (!btn || btn.disabled) return;
            var $btn = $(btn);
            var id   = btn.dataset.id;
            if (!id) return;

            var origText = $btn.text();
            $btn.addClass('loading').text('…').prop('disabled', true);

            $.ajax({
                url:  pw_cart_vars.ajax_url,
                type: 'POST',
                data: { action: 'pw_add_to_cart', product_id: id, qty: 1, nonce: pw_cart_vars.nonce_add },
                success: function (res) {
                    if (res.success) {
                        bumpCount(res.data.count);
                        $('#pw-cart-btn').addClass('pw-added');
                        setTimeout(function () { $('#pw-cart-btn').removeClass('pw-added'); }, 500);

                        $btn.text('✓').css('background', '#1a5c1a');
                        setTimeout(function () {
                            $btn.text(origText).css('background', '').prop('disabled', false).removeClass('loading');
                        }, 2000);

                        showToast(pw_cart_vars.i18n.added, pw_cart_vars.cart_url);
                        $(document.body).trigger('wc_fragment_refresh');
                        $(document.body).trigger('added_to_cart', [res.data, undefined, btn]);

                        refreshCart(function () { openCart(); });
                    } else {
                        $btn.text(origText).css('background', '').prop('disabled', false).removeClass('loading');
                        showToast((res.data && res.data.message) ? res.data.message : pw_cart_vars.i18n.error);
                    }
                },
                error: function () {
                    $btn.text(origText).css('background', '').prop('disabled', false).removeClass('loading');
                }
            });
        };

        // Override pwAddToCartDetail (used by single product page)
        window.pwAddToCartDetail = function (btn) {
            if (!btn || btn.disabled) return;
            var $btn = $(btn);
            var id   = btn.dataset.id;
            var qty  = parseInt($('#pw-qty').val()) || 1;
            var origText = $btn.text();
            var ajaxData = {
                action: 'pw_add_to_cart',
                product_id: id,
                qty: qty,
                variation_id: parseInt(btn.dataset.variationId || $('.variation_id').val(), 10) || 0,
                nonce: pw_cart_vars.nonce_add
            };

            // Variant check
            var $varBtns = $('.variant-btn');
            if ($varBtns.length) {
                var attrs = {};
                $varBtns.each(function () { attrs[$(this).data('attr')] = true; });
                var selectedAttrs = {};
                $varBtns.filter('.selected').each(function () {
                    var attr = $(this).data('attr');
                    selectedAttrs[attr] = true;
                    ajaxData[attr] = $(this).data('val');
                });
                if (Object.keys(selectedAttrs).length < Object.keys(attrs).length) {
                    showToast(pw_cart_vars.i18n.select_options);
                    return;
                }
            }

            $btn.addClass('loading').text('…').prop('disabled', true);

            $.ajax({
                url:  pw_cart_vars.ajax_url,
                type: 'POST',
                data: ajaxData,
                success: function (res) {
                    if (res.success) {
                        bumpCount(res.data.count);
                        $btn.text('✓ ' + pw_cart_vars.i18n.added_short).css('background', '#1a5c1a');
                        setTimeout(function () {
                            $btn.text(origText).css('background', '').prop('disabled', false).removeClass('loading');
                        }, 2200);
                        showToast(pw_cart_vars.i18n.added, pw_cart_vars.cart_url);
                        $(document.body).trigger('wc_fragment_refresh');
                        refreshCart(function () { openCart(); });
                    } else {
                        $btn.text(origText).css('background', '').prop('disabled', false).removeClass('loading');
                        showToast((res.data && res.data.message) ? res.data.message : pw_cart_vars.i18n.error);
                    }
                },
                error: function () {
                    $btn.text(origText).css('background', '').prop('disabled', false).removeClass('loading');
                }
            });
        };

    }); // end ready

    /* ─── CART ICON → open drawer (prevent page nav) ─────────── */
    $(document).on('click', '#pw-cart-btn, .cart-btn, .mobile-nav-cart', function (e) {
        if (isCartOrCheckoutPage() || !getCart().length) {
            return; // Normal link navigation to cart page
        }
        e.preventDefault();
        if (isOpen()) { closeCart(); } else { refreshCart(function () { openCart(); }); }
    });

    /* ─── CLOSE TRIGGERS & DRAWER ACTION LINKS ─────────────────── */
    $(document).on('click', '#pw-cart-close', closeCart);
    $(document).on('click', '#pw-cart-overlay', closeCart);
    $(document).on('click', '.pw-side-cart__btn, .pw-side-cart__actions a, .pw-side-cart__continue', function () {
        closeCart();
    });
    $(document).on('keydown', function (e) { if (e.key === 'Escape' && isOpen()) closeCart(); });

    /* ─── ITEM: QTY BUTTONS (debounced so rapid clicks send ONE
       request instead of one per click) ─────────────────────── */
    var qtyDebounceTimers = {};
    function debouncedUpdateQty(key, qty) {
        clearTimeout(qtyDebounceTimers[key]);
        qtyDebounceTimers[key] = setTimeout(function () {
            updateQty(key, qty);
        }, 400);
    }

    $('#pw-side-cart-body').on('click', '.pw-sci__qty-btn', function () {
        var action  = $(this).data('action');
        var key     = $(this).data('key');
        var $input  = $(this).siblings('.pw-sci__qty-val');
        var current = parseInt($input.val()) || 1;
        var next    = action === 'plus' ? current + 1 : Math.max(1, current - 1);
        $input.val(next);
        debouncedUpdateQty(key, next);
    });

    $('#pw-side-cart-body').on('change', '.pw-sci__qty-val', function () {
        var key = $(this).data('key');
        var val = Math.max(1, parseInt($(this).val()) || 1);
        $(this).val(val);
        updateQty(key, val);
    });

    /* ─── ITEM: REMOVE ───────────────────────────────────────── */
    $('#pw-side-cart-body').on('click', '.pw-sci__remove', function () {
        var key   = $(this).data('key');
        var $item = $(this).closest('.pw-sci');
        $item.addClass('is-removing');
        setTimeout(function () { removeItem(key); }, 290);
    });

    /* ─── AJAX: UPDATE QTY ───────────────────────────────────── */
    // Server now returns items_html + count + subtotal in ONE response,
    // so there's no need for the extra pw_get_cart_html round-trip that
    // used to run right after this (and the wc_fragment_refresh trigger
    // used to fire yet ANOTHER request on cart/checkout/account pages —
    // it's redundant here since renderCartData already updates the badge).
    function updateQty(cartKey, qty) {
        startLoading();
        $.ajax({
            url:  pw_cart_vars.ajax_url,
            type: 'POST',
            data: { action: 'pw_update_cart_qty', nonce: pw_cart_vars.nonce, cart_key: cartKey, qty: qty },
            success: function (res) { stopLoading(); if (res.success && res.data) renderCartData(res.data); },
            error: stopLoading
        });
    }

    /* ─── AJAX: REMOVE ITEM ──────────────────────────────────── */
    function removeItem(cartKey) {
        startLoading();
        $.ajax({
            url:  pw_cart_vars.ajax_url,
            type: 'POST',
            data: { action: 'pw_remove_cart_item', nonce: pw_cart_vars.nonce, cart_key: cartKey },
            success: function (res) { stopLoading(); if (res.success && res.data) renderCartData(res.data); },
            error: stopLoading
        });
    }

    /* ─── WC FRAGMENT SYNC ───────────────────────────────────── */
    $(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function () {
        var count = parseInt($('.cart-badge').first().text()) || 0;
        bumpCount(count);
        // Note: no translation call needed here — only a number changed.
    });

    /* ─── FOCUS TRAP ─────────────────────────────────────────── */
    $cart.on('keydown', function (e) {
        if (e.key !== 'Tab') return;
        var $focusable = $cart.find('a, button, input').filter(':visible');
        if (!$focusable.length) return;
        var first = $focusable.first()[0], last = $focusable.last()[0];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    });

    /* ─── GLOBAL HELPERS ─────────────────────────────────────── */
    window.pwOpenSideCart  = openCart;
    window.pwCloseSideCart = closeCart;

})(jQuery);
