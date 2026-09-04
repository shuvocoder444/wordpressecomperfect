<?php
/**
 * PerfectWelding Theme Functions
 * WooCommerce-integrated dark industrial theme
 */

defined('ABSPATH') || exit;

// ─── SIDE CART MODULE ────────────────────────────────────────────────────────
require_once get_template_directory() . '/inc/side-cart.php';

// ─── THEME SETUP ─────────────────────────────────────────────────────────────
function pw_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

    // ── Elementor Support ──────────────────────────────────────────────────
    add_theme_support('elementor');
    add_theme_support('elementor-pro');
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
    add_theme_support('custom-logo');
    add_theme_support('woocommerce', [
        'thumbnail_image_width' => 600,
        'single_image_width'    => 800,
        'product_grid'          => [
            'default_rows'    => 4,
            'min_rows'        => 1,
            'max_rows'        => 8,
            'default_columns' => 4,
            'min_columns'     => 2,
            'max_columns'     => 4,
        ],
    ]);
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    register_nav_menus([
        'primary' => __('Primary Menu', 'perfectwelding'),
        'footer'  => __('Footer Menu', 'perfectwelding'),
    ]);

    load_theme_textdomain('perfectwelding', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'pw_setup');

// ─── ENQUEUE ASSETS ──────────────────────────────────────────────────────────
function pw_asset_version($relative_path) {
    $file = get_template_directory() . $relative_path;
    return file_exists($file) ? (string) filemtime($file) : '1.0.0';
}

function pw_enqueue_assets() {
    // Google Fonts
    wp_enqueue_style('pw-fonts',
        'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&family=Space+Mono:wght@400;700&display=swap',
        [], null
    );

    // Main stylesheet (minified — see assets/css/main.css for source)
    wp_enqueue_style('pw-main',
        get_template_directory_uri() . '/assets/css/main.min.css',
        ['pw-fonts'], pw_asset_version('/assets/css/main.min.css')
    );

    // Responsive stylesheet — full breakpoint system + missing base styles
    wp_enqueue_style('pw-responsive',
        get_template_directory_uri() . '/assets/css/responsive.min.css',
        ['pw-main'], pw_asset_version('/assets/css/responsive.min.css')
    );

    // WooCommerce override styles (loaded after woo)
    if (class_exists('WooCommerce')) {
        wp_enqueue_style('pw-woo',
            get_template_directory_uri() . '/assets/css/woocommerce.min.css',
            ['pw-main', 'pw-responsive'], pw_asset_version('/assets/css/woocommerce.min.css')
        );
    }

    // Main JS (minified — see assets/js/main.js for source)
    wp_enqueue_script('pw-main',
        get_template_directory_uri() . '/assets/js/main.min.js',
        ['jquery'], pw_asset_version('/assets/js/main.min.js'), true
    );
    wp_script_add_data('pw-main', 'defer', true);

    // Pass WP data to JS
    $pw_vars = [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('pw_nonce'),
        'is_rtl'   => is_rtl(),
    ];

    // Only add WooCommerce-dependent vars if WooCommerce is active
    if ( class_exists('WooCommerce') ) {
        $pw_vars['cart_url'] = wc_get_cart_url();
        $pw_vars['shop_url'] = get_permalink( wc_get_page_id('shop') );
        $pw_vars['currency'] = get_woocommerce_currency_symbol();
    }

    wp_localize_script('pw-main', 'pw_vars', $pw_vars);

    // ── SPA-style page navigation (PJAX) ─────────────────────────
    // Deliberately NOT loaded at all on cart/checkout/account — those
    // pages always get a normal, full page load. See assets/js/spa-nav.js
    // for the full safety rules.
    if ( ! ( function_exists('is_cart') && is_cart() )
        && ! ( function_exists('is_checkout') && is_checkout() )
        && ! ( function_exists('is_account_page') && is_account_page() ) ) {
        wp_enqueue_script('pw-spa-nav',
            get_template_directory_uri() . '/assets/js/spa-nav.min.js',
            ['pw-main'], pw_asset_version('/assets/js/spa-nav.min.js'), true
        );
        wp_script_add_data('pw-spa-nav', 'defer', true);
    }

    // Elementor compatibility: prevent theme main.css from overriding Elementor
    // layout when Elementor canvas or full-width template is active
    if (
        defined('ELEMENTOR_VERSION') &&
        class_exists('\Elementor\Plugin') &&
        \Elementor\Plugin::$instance->preview->is_preview_mode()
    ) {
        wp_dequeue_style('pw-main');
    }
}
add_action('wp_enqueue_scripts', 'pw_enqueue_assets');

add_filter('wp_resource_hints', function($urls, $relation_type) {
    if ('preconnect' === $relation_type) {
        $urls[] = 'https://fonts.googleapis.com';
        $urls[] = [
            'href'        => 'https://fonts.gstatic.com',
            'crossorigin' => 'anonymous',
        ];
    }
    return $urls;
}, 10, 2);

add_action('init', function() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
});

add_action('wp_enqueue_scripts', function() {
    if (class_exists('WooCommerce') && ! is_cart() && ! is_checkout() && ! is_account_page()) {
        wp_dequeue_script('wc-cart-fragments');
    }
}, 100);

/* ═══════════════════════════════════════════════════════════════
   PERFORMANCE / CPU OPTIMIZATIONS
   Added after hosting suspension for "High CPU usage".
   These are safe, code-level fixes; hosting-level caching (see
   README notes) is still recommended on top of this.
   ═══════════════════════════════════════════════════════════════ */

// ── 1) Heartbeat API is one of the most common causes of CPU spikes on
// shared hosting: by default it polls admin-ajax.php every 15–60s while
// any wp-admin screen is open (post editor, dashboard widgets, etc).
// We slow it down everywhere and disable it completely on the front-end,
// where the theme does not use it at all.
add_action('init', function() {
    // Disable heartbeat on the public site (visitors never need it here)
    if ( ! is_admin() ) {
        wp_deregister_script('heartbeat');
    }
}, 1);

add_filter('heartbeat_settings', function($settings) {
    // Slow admin heartbeat from the 15s default to 60s
    $settings['interval'] = 60;
    return $settings;
});

// ── 2) NOTE on WP-Cron (fixed in wp-config.php, not here):
// By default, WP-Cron runs on EVERY front-end page load — a visitor
// loading the shop page silently triggers a background check for due
// scheduled tasks. On low-CPU-quota hosting this adds up fast. This
// cannot be safely disabled from the theme; add this line to
// wp-config.php (above "That's all, stop editing!"):
//     define('DISABLE_WP_CRON', true);
// then ask your host to add a real server cron job that hits
// wp-cron.php every 5–15 minutes instead. See README.md for details.

// ── 3) Simple abuse/flood protection for the theme's public AJAX
// endpoints. These endpoints (add to cart, cart HTML, contact form etc.)
// are reachable by anyone, including bots/scanners hitting admin-ajax.php
// directly and repeatedly — each hit boots the full WordPress + WooCommerce
// stack and runs DB queries, which is exactly the kind of load that racks
// up CPU usage on shared hosting. This throttles a single IP to a
// generous number of theme-AJAX requests per minute — high enough that
// real customers (including several behind the same office/mobile-carrier
// IP during a busy sale) never notice it, but bot floods still get capped.
function pw_ajax_rate_limit_check( $bucket, $limit = 20, $window = 60 ) {
    $ip  = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown';
    $key = 'pw_rl_' . $bucket . '_' . md5($ip);
    $count = (int) get_transient($key);
    if ( $count >= $limit ) {
        wp_send_json_error(['message' => __('Too many requests, please slow down.', 'perfectwelding')], 429);
    }
    set_transient($key, $count + 1, $window);
}
add_action('wp_ajax_nopriv_pw_add_to_cart',      function() { pw_ajax_rate_limit_check('cart', 90, 60); }, 1);
add_action('wp_ajax_nopriv_pw_get_cart_html',    function() { pw_ajax_rate_limit_check('cart', 90, 60); }, 1);
add_action('wp_ajax_nopriv_pw_update_cart_qty',  function() { pw_ajax_rate_limit_check('cart', 90, 60); }, 1);
add_action('wp_ajax_nopriv_pw_remove_cart_item', function() { pw_ajax_rate_limit_check('cart', 90, 60); }, 1);
add_action('wp_ajax_nopriv_pw_contact_submit',   function() { pw_ajax_rate_limit_check('contact', 8, 60); }, 1);

// Keep the homepage "bestsellers" cache (see front-page.php) fresh:
// clear it whenever an order completes/processes, since that's when
// total_sales actually changes.
add_action('woocommerce_order_status_changed', function() {
    delete_transient('pw_bestseller_ids');
});

/* ═══════════════════════════════════════════════════════════════
   FIRST-LOAD SPEED PASS (PageSpeed Insights follow-up)
   Goal: cut render-blocking requests without touching checkout.
   ═══════════════════════════════════════════════════════════════ */

// ── A) Payment-gateway (iDEAL/Mollie) and WooCommerce-Blocks CSS/JS
// only need to exist on cart/checkout/account pages, but plugins often
// enqueue them site-wide — including on the homepage, where PageSpeed
// flagged them as render-blocking + "unused CSS/JS". We scan the actual
// registered queue (matching by real file URL, not guessed handle names,
// so this stays correct even if a plugin renames its handles later) and
// drop anything gateway/blocks-related when we're not on a page that
// needs it. Checkout/cart/account are explicitly excluded from this —
// those pages keep 100% of their assets, untouched.
add_action('wp_enqueue_scripts', function() {
    if ( is_cart() || is_checkout() || is_account_page() ) return;

    $keywords = [ 'mollie', 'ideal', 'wc-gateway', 'wc-blocks', 'payment-gateway', 'wc-payment', 'blocks-checkout' ];

    global $wp_styles, $wp_scripts;

    foreach ( [ $wp_styles, $wp_scripts ] as $registry ) {
        if ( empty( $registry->queue ) ) continue;
        foreach ( $registry->queue as $handle ) {
            if ( empty( $registry->registered[ $handle ] ) ) continue;
            $src = strtolower( (string) $registry->registered[ $handle ]->src );
            if ( ! $src ) continue;
            foreach ( $keywords as $kw ) {
                if ( strpos( $src, $kw ) !== false ) {
                    if ( $registry === $wp_styles ) {
                        wp_dequeue_style( $handle );
                    } else {
                        wp_dequeue_script( $handle );
                    }
                    break;
                }
            }
        }
    }
}, 999);

// ── B) Google Fonts CSS made non-render-blocking. The stylesheet itself
// was blocking first paint even though @font-face already uses
// font-display:swap — swap only helps AFTER the CSS has loaded. We load
// it with the standard preload+onload swap trick (with a <noscript>
// fallback for the rare no-JS visitor) so text can paint immediately
// with the fallback font, then swap to the webfont once it's ready.
add_filter('style_loader_tag', function($html, $handle) {
    if ( 'pw-fonts' === $handle || 'pw-side-cart' === $handle ) {
        // Handles both quote styles WP core may output ('stylesheet' or "stylesheet")
        $preloaded = preg_replace(
            "/rel=(['\"])stylesheet\\1/",
            "rel=$1preload$1 as=$1style$1 onload=\"this.onload=null;this.rel='stylesheet'\"",
            $html
        );
        if ( $preloaded && $preloaded !== $html ) {
            $html = $preloaded . '<noscript>' . preg_replace(
                "/rel=(['\"])stylesheet\\1/",
                "rel=$1stylesheet$1",
                $html
            ) . '</noscript>';
        }
    }
    return $html;
}, 10, 2);

// ─── WOOCOMMERCE SUPPORT ─────────────────────────────────────────────────────

// Remove default WooCommerce styles (we use our own)
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

// Dequeue WooCommerce's native variation JS on single product pages.
// Our theme uses custom swatch buttons + pwResolveVariation() instead.
// The native wc-add-to-cart-variation.js expects a <form class="variations_form">
// which our template does not have — causing the error even when swatches ARE selected.
add_action('wp_enqueue_scripts', function() {
    if ( is_product() ) {
        wp_dequeue_script('wc-add-to-cart-variation');
        wp_deregister_script('wc-add-to-cart-variation');
    }
}, 100);

// Remove default WooCommerce wrappers so our templates control layout
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content',  'woocommerce_output_content_wrapper_end', 10);
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

// Add our wrappers
add_action('woocommerce_before_main_content', 'pw_woo_wrapper_start', 10);
add_action('woocommerce_after_main_content',  'pw_woo_wrapper_end', 10);

function pw_woo_wrapper_start() { echo '<div class="pw-woo-main">'; }
function pw_woo_wrapper_end()   { echo '</div>'; }

// Product columns
add_filter('loop_shop_columns', function() { return 4; });
add_filter('loop_shop_per_page', function() { return 20; });

// Thumbnail sizes
add_image_size('pw-product-thumb', 480, 480, true);
add_image_size('pw-product-single', 800, 800, false);

// ─── CART FRAGMENT (AJAX) ────────────────────────────────────────────────────
add_filter('woocommerce_add_to_cart_fragments', 'pw_cart_count_fragment');
function pw_cart_count_fragment($fragments) {
    $count = WC()->cart->get_cart_contents_count();
    $fragments['.cart-badge'] = '<span class="cart-badge">' . $count . '</span>';
    return $fragments;
}

// ─── CUSTOM AJAX ADD TO CART ─────────────────────────────────────────────────
add_action('wp_ajax_pw_add_to_cart',        'pw_ajax_add_to_cart');
add_action('wp_ajax_nopriv_pw_add_to_cart', 'pw_ajax_add_to_cart');
function pw_ajax_add_to_cart() {
    check_ajax_referer('pw_nonce', 'nonce');
    $product_id   = absint($_POST['product_id'] ?? 0);
    $qty          = max(1, absint($_POST['qty'] ?? 1));
    $variation_id = absint($_POST['variation_id'] ?? 0);

    if (!$product_id) wp_send_json_error(['message' => 'Invalid product']);

    $product = wc_get_product($product_id);
    if (!$product) wp_send_json_error(['message' => 'Product not found']);

    // ── Variable product handling ──────────────────────────────
    if ($product->is_type('variable')) {
        $posted_attrs = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'attribute_') === 0) {
                $clean_key = wc_clean( wp_unslash( $key ) );
                $clean_val = wc_clean( wp_unslash( $value ) );
                $posted_attrs[ $clean_key ] = $clean_val;
            }
        }

        // ── Step 1: If variation_id is missing, resolve it from posted attribute data.
        // The JS sends attribute_color, attribute_size etc. alongside variation_id.
        // Using WooCommerce's own find_matching_product_variation() is authoritative
        // and handles "Any" catch-all variations automatically.
        if (!$variation_id) {
            $posted_attrs = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'attribute_') === 0) {
                    // IMPORTANT: do NOT use sanitize_key() here — it converts hyphens
                    // to underscores, e.g. "attribute_elektrode-dikte" → "attribute_elektrode_dikte",
                    // which breaks find_matching_product_variation() because WooCommerce stores
                    // variation meta with hyphens intact.
                    $clean_key = wc_clean( wp_unslash( $key ) );   // preserves hyphens
                    $clean_val = wc_clean( wp_unslash( $value ) );
                    $posted_attrs[ $clean_key ] = $clean_val;
                }
            }

            if (!empty($posted_attrs)) {
                $data_store   = WC_Data_Store::load('product');
                $variation_id = (int) $data_store->find_matching_product_variation($product, $posted_attrs);
            }

            // Last resort: if product has exactly one variation, auto-select it.
            if (!$variation_id) {
                $available = $product->get_available_variations();
                if (count($available) === 1) {
                    $variation_id = absint($available[0]['variation_id']);
                }
            }
        }

        if (!$variation_id) {
            wp_send_json_error(['message' => __('Selecteer een variant voordat u toevoegt.', 'perfectwelding')]);
        }

        $variation = wc_get_product($variation_id);

        // Make sure the variation actually belongs to this parent product
        if (!$variation || $variation->get_parent_id() !== $product_id) {
            wp_send_json_error(['message' => __('Ongeldige variant.', 'perfectwelding')]);
        }

        if (!$variation->is_purchasable()) {
            wp_send_json_error(['message' => __('Deze variant is niet beschikbaar.', 'perfectwelding')]);
        }

        if (!$variation->is_in_stock()) {
            wp_send_json_error(['message' => __('Deze variant is uitverkocht.', 'perfectwelding')]);
        }

        // ── Step 2: Build variation_data from the resolved variation's own attributes.
        // WooCommerce's add_to_cart() needs keys prefixed with "attribute_".
        $raw_attrs      = $variation->get_variation_attributes();
        $variation_data = [];
        foreach ($raw_attrs as $key => $value) {
            $prefixed_key = (strpos($key, 'attribute_') === 0)
                ? $key
                : 'attribute_' . sanitize_title($key);
            $variation_data[$prefixed_key] = $value !== ''
                ? $value
                : ($posted_attrs[$prefixed_key] ?? $value);
        }

    } else {
        $variation_data = [];
    }

    // Clear any existing notices before attempting to add
    wc_clear_notices();

    $added = WC()->cart->add_to_cart($product_id, $qty, $variation_id, $variation_data);
    if ($added) {
        WC()->cart->calculate_totals();
        wp_send_json_success([
            'count'   => WC()->cart->get_cart_contents_count(),
            'total'   => WC()->cart->get_cart_total(),
            'message' => __('Product toegevoegd aan winkelwagen', 'perfectwelding'),
        ]);
    } else {
        $notices = wc_get_notices('error');
        $msg = !empty($notices)
            ? wp_strip_all_tags($notices[0]['notice'])
            : __('Kon niet toevoegen. Probeer opnieuw.', 'perfectwelding');
        wc_clear_notices();
        wp_send_json_error(['message' => $msg]);
    }
}

// ─── REMOVE WOOCOMMERCE BREADCRUMBS ──────────────────────────────────────────
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);

// ─── CUSTOM BREADCRUMB ───────────────────────────────────────────────────────
function pw_breadcrumb() {
    if (is_shop()) return;
    echo '<div class="pw-breadcrumb">';
    echo '<a href="' . get_permalink(wc_get_page_id('shop')) . '">&larr; ' . __('Back to shop', 'perfectwelding') . '</a>';
    echo '</div>';
}

// ─── PRODUCT BADGE ───────────────────────────────────────────────────────────
add_filter('woocommerce_sale_flash', 'pw_sale_badge', 10, 3);
function pw_sale_badge($html, $post, $product) {
    return '<span class="product-badge sale">SALE</span>';
}

// ─── REMOVE DEFAULT PRODUCT LOOP ELEMENTS ────────────────────────────────────
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
remove_action('woocommerce_shop_loop_item_title',        'woocommerce_template_loop_product_title', 10);
remove_action('woocommerce_after_shop_loop_item_title',  'woocommerce_template_loop_rating', 5);
remove_action('woocommerce_after_shop_loop_item_title',  'woocommerce_template_loop_price', 10);
remove_action('woocommerce_after_shop_loop_item',        'woocommerce_template_loop_add_to_cart', 10);

// ─── CUSTOM PRODUCT LOOP TEMPLATE ────────────────────────────────────────────
add_action('woocommerce_before_shop_loop_item', 'pw_loop_item_open', 5);
add_action('woocommerce_after_shop_loop_item',  'pw_loop_item_content', 10);
add_action('woocommerce_after_shop_loop_item',  'pw_loop_item_close', 20);

function pw_loop_item_open() {
    global $product;
    $link = get_permalink($product->get_id());
    echo '<a href="' . esc_url($link) . '" class="product-card-link">';
}

function pw_loop_item_content() {
    global $product;
    $image = $product->get_image('pw-product-thumb', [
        'class'    => 'product-img-el',
        'loading'  => 'lazy',
        'decoding' => 'async',
        'sizes'    => '(max-width: 768px) 100vw, 25vw',
    ]);
    $price = $product->get_price();
    $regular = $product->get_regular_price();
    $sale = $product->is_on_sale();
    $in_stock = $product->is_in_stock();
    $brand = get_post_meta($product->get_id(), '_brand', true) ?: 'PerfectWelding';
    $is_new = (strtotime($product->get_date_created()) > strtotime('-30 days'));

    echo '<div class="product-img">';
    echo '<div class="grid-lines"></div>';
    if ($sale) echo '<span class="product-badge sale">SALE</span>';
    elseif ($is_new) echo '<span class="product-badge">NEW</span>';
    elseif (!$in_stock) echo '<span class="product-badge out">UITVERKOCHT</span>';
    if ($image) echo $image;
    else echo '<div class="product-img-placeholder">' . pw_tig_svg() . '</div>';
    echo '</div>';

    echo '<div class="product-info">';
    echo '<div class="product-brand">' . esc_html($brand) . '</div>';
    echo '<div class="product-name">' . esc_html($product->get_name()) . '</div>';
    echo '<div class="product-footer">';
    if ($sale && $regular) {
        echo '<div><span class="product-price-old">&euro;' . number_format((float)$regular, 2, ',', '.') . '</span><span class="product-price">&euro;' . number_format((float)$price, 2, ',', '.') . '</span></div>';
    } else {
        echo '<div class="product-price">&euro;' . wc_format_decimal($price, 2, ',') . '</div>';
    }
    if ($in_stock) {
        echo '<button class="product-add pw-add-to-cart" data-id="' . esc_attr($product->get_id()) . '" onclick="event.stopPropagation(); pwAddToCart(this)">+ ' . esc_html__('Add to Cart', 'perfectwelding') . '</button>';
    } else {
        echo '<span class="product-add disabled">' . esc_html__('Out of Stock', 'perfectwelding') . '</span>';
    }
    echo '</div>';
    echo '</div>';
}

function pw_loop_item_close() {
    echo '</a>';
}

// ─── INLINE SVG PLACEHOLDER ──────────────────────────────────────────────────
function pw_tig_svg($color = 'rgba(255,77,0,0.4)') {
    return '<svg viewBox="0 0 90 180" width="90" xmlns="http://www.w3.org/2000/svg">
        <path d="M20 14 L12 155 Q12 165 22 165 L68 165 Q78 165 78 155 L70 14 Z" fill="#1a1a1a" stroke="' . $color . '" stroke-width="0.75"/>
        <path d="M28 20 L22 148 Q22 156 30 156 L60 156 Q68 156 68 148 L62 20 Z" fill="rgba(255,77,0,0.05)" stroke="rgba(255,77,0,0.2)" stroke-width="0.5"/>
        <rect x="41" y="2" width="8" height="14" rx="2" fill="#222" stroke="rgba(255,255,255,0.08)" stroke-width="0.5"/>
        <ellipse cx="45" cy="170" rx="10" ry="6" fill="rgba(255,184,0,0.5)"/>
    </svg>';
}

// ─── WOOCOMMERCE MESSAGES ─────────────────────────────────────────────────────
add_filter('woocommerce_add_to_cart_message_html', 'pw_add_to_cart_message', 10, 2);
function pw_add_to_cart_message($message, $products) {
    // Side cart drawer handles the add-to-cart notification via toast
    return '';
}

// ─── WIDGET AREAS ─────────────────────────────────────────────────────────────
function pw_register_sidebars() {
    register_sidebar([
        'name'          => __('Shop Sidebar', 'perfectwelding'),
        'id'            => 'shop-sidebar',
        'description'   => __('Widgets for shop page sidebar', 'perfectwelding'),
        'before_widget' => '<div class="sidebar-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<div class="sidebar-title">',
        'after_title'   => '</div>',
    ]);
}
add_action('widgets_init', 'pw_register_sidebars');

// ─── REMOVE WOO BREADCRUMB FROM SINGLE ───────────────────────────────────────
add_filter('woocommerce_show_page_title', '__return_false');

// ─── CUSTOM CHECKOUT FIELDS ───────────────────────────────────────────────────
add_filter('woocommerce_checkout_fields', 'pw_checkout_fields');
function pw_checkout_fields($fields) {
    // Keep standard fields, just style them
    return $fields;
}

// ─── REMOVE RELATED PRODUCTS (we add our own) ────────────────────────────────
// remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);

// ─── ACCOUNT ──────────────────────────────────────────────────────────────────
// Uses WooCommerce default My Account page

// ─── HELPER: CURRENCY SYMBOL ─────────────────────────────────────────────────
function pw_currency() {
    return get_woocommerce_currency_symbol();
}

// ─── FIX 1: PAYMENT GATEWAYS NOT SHOWING ─────────────────────────────────────
// Ensures payment gateways are initialized and available on all checkout pages
// including when [woocommerce_checkout] shortcode is used on any page.
add_action('woocommerce_checkout_before_order_review', function() {
    if ( ! class_exists('WooCommerce') ) return;
    $gateways = WC()->payment_gateways();
    if ( $gateways ) {
        // Re-init to make sure all gateways are loaded
        $gateways->init();
        $available = $gateways->get_available_payment_gateways();
        if ( ! empty($available) ) {
            $gateways->set_current_gateway( $available );
        }
    }
});

// ─── FIX: Force checkout scripts & styles for shortcode pages ─────────────────
// When [woocommerce_checkout] is used on a non-WC page, is_checkout() may
// return false before the shortcode runs. We hook late to catch this.
add_action('wp_enqueue_scripts', function() {
    $post = get_post();
    $has_checkout_shortcode = $post && has_shortcode( $post->post_content, 'woocommerce_checkout' );
    if ( is_checkout() || $has_checkout_shortcode ) {
        wp_enqueue_script('wc-checkout');
        wp_enqueue_script('woocommerce');
        wp_enqueue_script('wc-country-select');
        wp_enqueue_script('wc-address-i18n');
    }
}, 99);

// ─── FIX 2: SHIPPING RECALCULATE ON ADDRESS CHANGE ───────────────────────────
// Sync customer location when checkout fields update so shipping zones resolve.
add_action("woocommerce_checkout_update_order_review", function($post_data) {
    $data = [];
    parse_str($post_data, $data);

    $ship_diff = ! empty($data["ship_to_different_address"]);
    $country   = wc_clean( $data["billing_country"]  ?? "" );
    $state     = wc_clean( $data["billing_state"]     ?? "" );
    $postcode  = wc_clean( $data["billing_postcode"]  ?? "" );
    $city      = wc_clean( $data["billing_city"]      ?? "" );

    if ( $country ) {
        WC()->customer->set_billing_location( $country, $state, $postcode, $city );
        if ( $ship_diff ) {
            WC()->customer->set_shipping_location(
                wc_clean( $data["shipping_country"]  ?? $country ),
                wc_clean( $data["shipping_state"]    ?? $state ),
                wc_clean( $data["shipping_postcode"] ?? $postcode ),
                wc_clean( $data["shipping_city"]     ?? $city )
            );
        } else {
            WC()->customer->set_shipping_location( $country, $state, $postcode, $city );
        }
        WC()->customer->save();
    }
}, 10, 1);

// ─── SIDE CART DRAWER ────────────────────────────────────────────────────────
require_once get_template_directory() . '/inc/side-cart.php';

// ─── ELEMENTOR COMPATIBILITY ──────────────────────────────────────────────────

/**
 * Register Elementor locations so header/footer builder works
 * (requires Elementor Pro — safe to keep even without Pro)
 */
add_action('elementor/theme/register_locations', function($elementor_theme_manager) {
    $elementor_theme_manager->register_all_core_location();
});

/**
 * Hide the default PW page header when Elementor is rendering the page.
 * This prevents the "PerfectWelding" eyebrow + duplicate title from
 * appearing above Elementor's own content.
 */
function pw_is_built_with_elementor() {
    if (!defined('ELEMENTOR_VERSION') || !class_exists('\Elementor\Plugin')) {
        return false;
    }
    $post_id = get_the_ID();
    if (!$post_id) return false;
    $doc = \Elementor\Plugin::$instance->documents->get($post_id);
    return $doc && $doc->is_built_with_elementor();
}

/**
 * Add body classes for Elementor pages so CSS can target them
 */
add_filter('body_class', function($classes) {
    if (pw_is_built_with_elementor()) {
        $classes[] = 'pw-elementor-page';
    }
    return $classes;
});

/**
 * Elementor editor CSS tweaks — keeps the theme from breaking the editor UI
 */
add_action('elementor/editor/after_enqueue_styles', function() {
    echo '<style>
        /* Keep admin bar from overlapping Elementor panel */
        body.elementor-editor-active #wpadminbar { z-index: 99999; }
    </style>';
});


/* ═══════════════════════════════════════════════════════════════
   WORDPRESS CUSTOMIZER — PerfectWelding Page Options
   Appearance → Customize → PerfectWelding
   ═══════════════════════════════════════════════════════════════ */
add_action('customize_register', function( WP_Customize_Manager $wp_customize ) {

    // ── PANEL ──────────────────────────────────────────────────
    $wp_customize->add_panel('pw_panel', [
        'title'    => __('PerfectWelding', 'perfectwelding'),
        'priority' => 30,
    ]);


    /* ── ABOUT US ─────────────────────────────────────────────── */
    $wp_customize->add_section('pw_about', [
        'title' => __('About Us Page', 'perfectwelding'),
        'panel' => 'pw_panel',
    ]);

    $about_fields = [
        'pw_about_eyebrow'     => ['label' => 'Eyebrow text',        'default' => 'OUR STORY'],
        'pw_about_headline1'   => ['label' => 'Headline line 1',     'default' => 'FOR'],
        'pw_about_headline2'   => ['label' => 'Headline line 2 (accent)', 'default' => 'WELDERS.'],
        'pw_about_headline3'   => ['label' => 'Headline line 3',     'default' => 'BY'],
        'pw_about_headline4'   => ['label' => 'Headline line 4 (accent)', 'default' => 'WELDERS.'],
        'pw_about_since_label' => ['label' => 'TIG badge label',     'default' => 'TIG'],
        'pw_about_since_year'  => ['label' => 'Since year text',     'default' => 'SINCE 2019'],
        'pw_about_p1_num'      => ['label' => 'Pillar 1 number',     'default' => '01'],
        'pw_about_p1_title'    => ['label' => 'Pillar 1 title',      'default' => 'TESTED'],
        'pw_about_p1_desc'     => ['label' => 'Pillar 1 description','default' => 'Every product we sell is personally tested by us. No compromises, no unknown quality.', 'type' => 'textarea'],
        'pw_about_p2_num'      => ['label' => 'Pillar 2 number',     'default' => '02'],
        'pw_about_p2_title'    => ['label' => 'Pillar 2 title',      'default' => 'IMPROVED'],
        'pw_about_p2_desc'     => ['label' => 'Pillar 2 description','default' => "We don't just sell parts. We actively search for improved versions of standard TIG parts.", 'type' => 'textarea'],
        'pw_about_p3_num'      => ['label' => 'Pillar 3 number',     'default' => '03'],
        'pw_about_p3_title'    => ['label' => 'Pillar 3 title',      'default' => 'HONESTLY'],
        'pw_about_p3_desc'     => ['label' => 'Pillar 3 description','default' => "If something isn't right, we don't sell it. Period. Our reputation is our greatest asset.", 'type' => 'textarea'],
        'pw_about_story_title' => ['label' => 'Story section title', 'default' => 'THE STORY'],
        'pw_about_story_content' => ['label' => 'Story text',        'default' => 'PerfectWelding was born out of frustration...', 'type' => 'textarea'],
        'pw_about_tw_title'    => ['label' => 'TIGWARE title',       'default' => 'TIGWARE'],
        'pw_about_tw_content'  => ['label' => 'TIGWARE text',        'default' => 'TIGWARE is a premium brand whose parts we officially sell.', 'type' => 'textarea'],
        'pw_about_tw_btn_text' => ['label' => 'TIGWARE button text', 'default' => 'SHOP TIGWARE →'],
        'pw_about_tw_btn_url'  => ['label' => 'TIGWARE button URL',  'default' => ''],
    ];

    foreach ($about_fields as $id => $args) {
        $wp_customize->add_setting($id, ['default' => $args['default'], 'sanitize_callback' => 'sanitize_textarea_field', 'transport' => 'refresh']);
        $ctrl_args = ['section' => 'pw_about', 'label' => __($args['label'], 'perfectwelding'), 'settings' => $id];
        if (($args['type'] ?? '') === 'textarea') {
            $wp_customize->add_control(new WP_Customize_Control($wp_customize, $id . '_ctrl', array_merge($ctrl_args, ['type' => 'textarea'])));
        } else {
            $wp_customize->add_control($id . '_ctrl', $ctrl_args);
        }
    }


    /* ── BLOG PAGE ────────────────────────────────────────────── */
    $wp_customize->add_section('pw_blog', [
        'title' => __('Blog Page', 'perfectwelding'),
        'panel' => 'pw_panel',
    ]);

    $blog_fields = [
        'pw_blog_page_title'    => ['label' => 'Page title',            'default' => 'BLOG'],
        'pw_blog_page_subtitle' => ['label' => 'Subtitle text',         'default' => 'Insights, guides, and product deep-dives from the welding bench.'],
        'pw_blog_posts_per_page'=> ['label' => 'Posts per page',        'default' => '9'],
    ];

    foreach ($blog_fields as $id => $args) {
        $wp_customize->add_setting($id, ['default' => $args['default'], 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh']);
        $wp_customize->add_control($id . '_ctrl', ['section' => 'pw_blog', 'label' => __($args['label'], 'perfectwelding'), 'settings' => $id]);
    }


    /* ── FAQ PAGE ─────────────────────────────────────────────── */
    $wp_customize->add_section('pw_faq', [
        'title' => __('FAQ Page', 'perfectwelding'),
        'panel' => 'pw_panel',
    ]);

    $faq_fields = [
        'pw_faq_page_title'    => ['label' => 'Page title',   'default' => 'FAQ'],
        'pw_faq_page_subtitle' => ['label' => 'Subtitle',     'default' => 'Frequently asked questions about our products, shipping, and usage.'],
        'pw_faq_items'         => [
            'label'   => 'FAQ items (JSON array)',
            'default' => '',
            'type'    => 'textarea',
            'desc'    => 'Paste a JSON array: [{"cat":"Products","q":"Question?","a":"Answer."},…]',
        ],
    ];

    foreach ($faq_fields as $id => $args) {
        $wp_customize->add_setting($id, ['default' => $args['default'], 'sanitize_callback' => 'sanitize_textarea_field', 'transport' => 'refresh']);
        $ctrl_args = ['section' => 'pw_faq', 'label' => __($args['label'], 'perfectwelding'), 'settings' => $id];
        if (!empty($args['desc'])) $ctrl_args['description'] = $args['desc'];
        if (($args['type'] ?? '') === 'textarea') {
            $wp_customize->add_control(new WP_Customize_Control($wp_customize, $id . '_ctrl', array_merge($ctrl_args, ['type' => 'textarea'])));
        } else {
            $wp_customize->add_control($id . '_ctrl', $ctrl_args);
        }
    }


    /* ── CONTACT PAGE ─────────────────────────────────────────── */
    $wp_customize->add_section('pw_contact', [
        'title' => __('Contact Page', 'perfectwelding'),
        'panel' => 'pw_panel',
    ]);

    $contact_fields = [
        'pw_contact_page_title'   => ['label' => 'Page title',          'default' => 'CONTACT'],
        'pw_contact_page_intro'   => ['label' => 'Intro text',          'default' => 'Do you have a question about a product or order, or do you just want to chat about TIG welding? Send us a message.', 'type' => 'textarea'],
        'pw_contact_email'        => ['label' => 'Email address',       'default' => 'matthijsbrand@perfectwelding.nl'],
        'pw_contact_location'     => ['label' => 'Location',            'default' => 'Hengelo, Netherlands'],
        'pw_contact_phone'        => ['label' => 'Phone / WhatsApp',    'default' => '+31 6 181 77 682'],
        'pw_contact_coc'          => ['label' => 'Chamber of Commerce', 'default' => '85191183'],
        'pw_contact_response'     => ['label' => 'Response time',       'default' => 'Within 24 hours on business days'],
        'pw_contact_instagram'    => ['label' => 'Instagram URL',       'default' => 'https://instagram.com/perfectweldingnl'],
        'pw_contact_linkedin'     => ['label' => 'LinkedIn URL',        'default' => 'https://www.linkedin.com/in/matthijs-brand-00413822b/'],
        'pw_contact_form_title'   => ['label' => 'Form heading',        'default' => 'SEND A MESSAGE'],
        'pw_contact_form_subject' => ['label' => 'Default subject',     'default' => 'Question about product'],
        'pw_contact_form_btn'     => ['label' => 'Send button text',    'default' => 'SEND →'],
        'pw_contact_cf7_shortcode'=> ['label' => 'CF7 shortcode (optional)', 'default' => '', 'desc' => 'Paste Contact Form 7 shortcode here to replace the built-in form, e.g. [contact-form-7 id="123"]'],
    ];

    foreach ($contact_fields as $id => $args) {
        $wp_customize->add_setting($id, ['default' => $args['default'], 'sanitize_callback' => 'sanitize_textarea_field', 'transport' => 'refresh']);
        $ctrl_args = ['section' => 'pw_contact', 'label' => __($args['label'], 'perfectwelding'), 'settings' => $id];
        if (!empty($args['desc'])) $ctrl_args['description'] = $args['desc'];
        if (($args['type'] ?? '') === 'textarea') {
            $wp_customize->add_control(new WP_Customize_Control($wp_customize, $id . '_ctrl', array_merge($ctrl_args, ['type' => 'textarea'])));
        } else {
            $wp_customize->add_control($id . '_ctrl', $ctrl_args);
        }
    }

});


/* ═══════════════════════════════════════════════════════════════
   AJAX — Contact Form Handler
   ═══════════════════════════════════════════════════════════════ */
add_action('wp_ajax_pw_contact_submit',        'pw_handle_contact_form');
add_action('wp_ajax_nopriv_pw_contact_submit', 'pw_handle_contact_form');

function pw_handle_contact_form() {
    // Verify nonce
    if ( ! isset($_POST['pw_contact_nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash($_POST['pw_contact_nonce']) ), 'pw_contact_form' ) ) {
        wp_send_json_error(__('Security check failed.', 'perfectwelding'));
    }

    $name    = sanitize_text_field( wp_unslash($_POST['cf_name']    ?? '') );
    $email   = sanitize_email(      wp_unslash($_POST['cf_email']   ?? '') );
    $subject = sanitize_text_field( wp_unslash($_POST['cf_subject'] ?? 'Contact form') );
    $message = sanitize_textarea_field( wp_unslash($_POST['cf_message'] ?? '') );

    if ( empty($name) || ! is_email($email) || empty($message) ) {
        wp_send_json_error(__('Please fill in all required fields.', 'perfectwelding'));
    }

    $to      = get_theme_mod('pw_contact_email', get_option('admin_email'));
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $name . ' <' . $email . '>',
        'Reply-To: ' . $email,
    ];

    $body = sprintf(
        "Name: %s\nEmail: %s\nSubject: %s\n\nMessage:\n%s",
        $name, $email, $subject, $message
    );

    $sent = wp_mail( $to, '[PerfectWelding] ' . $subject, $body, $headers );

    if ($sent) {
        wp_send_json_success(__('Your message has been sent. We will reply within 24 hours.', 'perfectwelding'));
    } else {
        wp_send_json_error(__('Message could not be sent. Please try emailing us directly.', 'perfectwelding'));
    }
}


/* ═══════════════════════════════════════════════════════════════
   CUSTOMIZER — Header & Footer Options
   Appearance → Customize → PerfectWelding → Header / Footer
   ═══════════════════════════════════════════════════════════════ */
add_action('customize_register', function( WP_Customize_Manager $wp_customize ) {

    /* ── HEADER / NAVBAR ────────────────────────────────────────── */
    $wp_customize->add_section('pw_header', [
        'title'    => __('Header & Navbar', 'perfectwelding'),
        'panel'    => 'pw_panel',
        'priority' => 10,
    ]);

    // Logo text
    $wp_customize->add_setting('pw_nav_logo_text',   ['default' => 'PERFECT',  'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh']);
    $wp_customize->add_setting('pw_nav_logo_accent', ['default' => 'WELDING',  'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh']);
    $wp_customize->add_control('pw_nav_logo_text_ctrl',   ['section' => 'pw_header', 'label' => __('Logo: first word',  'perfectwelding'), 'settings' => 'pw_nav_logo_text']);
    $wp_customize->add_control('pw_nav_logo_accent_ctrl', ['section' => 'pw_header', 'label' => __('Logo: accent word (orange)', 'perfectwelding'), 'settings' => 'pw_nav_logo_accent']);

    // Colors
    $wp_customize->add_setting('pw_nav_bg_color',     ['default' => 'rgba(8,8,8,0.93)', 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh']);
    $wp_customize->add_setting('pw_nav_accent_color', ['default' => '#FF4D00',           'sanitize_callback' => 'sanitize_hex_color',   'transport' => 'refresh']);
    $wp_customize->add_control('pw_nav_bg_color_ctrl',     ['section' => 'pw_header', 'label' => __('Navbar background (rgba or hex)', 'perfectwelding'), 'settings' => 'pw_nav_bg_color']);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'pw_nav_accent_color_ctrl', ['section' => 'pw_header', 'label' => __('Accent colour', 'perfectwelding'), 'settings' => 'pw_nav_accent_color']));

    // Nav menu items
    $wp_customize->add_setting('pw_nav_menu_items', [
        'default'           => "Home|/\nShop|/shop\nAbout us|/about\nBlog|/blog\nFAQ|/faq\nContact|/contact",
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control(new WP_Customize_Control($wp_customize, 'pw_nav_menu_items_ctrl', [
        'section'     => 'pw_header',
        'label'       => __('Nav menu items', 'perfectwelding'),
        'description' => __('One item per line in format: Label|/url', 'perfectwelding'),
        'settings'    => 'pw_nav_menu_items',
        'type'        => 'textarea',
    ]));

    // Toggles
    foreach ([
        'pw_nav_show_lang'    => 'Show language switcher (EN/NL)',
        'pw_nav_show_cart'    => 'Show cart button',
        'pw_nav_show_account' => 'Show account button',
        'pw_nav_show_marquee' => 'Show marquee strip below navbar',
    ] as $id => $label) {
        $wp_customize->add_setting($id, ['default' => true, 'sanitize_callback' => 'pw_sanitize_checkbox', 'transport' => 'refresh']);
        $wp_customize->add_control($id . '_ctrl', ['section' => 'pw_header', 'label' => __($label, 'perfectwelding'), 'settings' => $id, 'type' => 'checkbox']);
    }

    // Marquee items
    $wp_customize->add_setting('pw_marquee_items', [
        'default'           => "✦ FREE SHIPPING ABOVE €100\n✦ TIGWARE PREMIUM PARTS\n✦ FAST DELIVERY\n✦ FOR WELDERS BY WELDERS\n✦ 14-DAY RETURN POLICY\n✦ 100% SELF-TESTED",
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport'         => 'refresh',
    ]);
    $wp_customize->add_control(new WP_Customize_Control($wp_customize, 'pw_marquee_items_ctrl', [
        'section'     => 'pw_header',
        'label'       => __('Marquee strip items', 'perfectwelding'),
        'description' => __('One item per line', 'perfectwelding'),
        'settings'    => 'pw_marquee_items',
        'type'        => 'textarea',
    ]));


    /* ── FOOTER ─────────────────────────────────────────────────── */
    $wp_customize->add_section('pw_footer_section', [
        'title'    => __('Footer', 'perfectwelding'),
        'panel'    => 'pw_panel',
        'priority' => 20,
    ]);

    $footer_fields = [
        'pw_footer_logo_text'   => ['label' => 'Logo: first word',    'default' => 'PERFECT'],
        'pw_footer_logo_accent' => ['label' => 'Logo: accent word',   'default' => 'WELDING'],
        'pw_footer_tagline'     => ['label' => 'Tagline text',        'default' => 'Specialized in premium TIG parts. Every product tested. Built for the serious welder.', 'type' => 'textarea'],
        'pw_footer_instagram'   => ['label' => 'Instagram URL',       'default' => 'https://instagram.com/perfectweldingnl'],
        'pw_footer_linkedin'    => ['label' => 'LinkedIn URL',        'default' => 'https://www.linkedin.com/in/matthijs-brand-00413822b/'],
        'pw_footer_kvk'         => ['label' => 'KVK number',          'default' => '85191183'],
        'pw_footer_city'        => ['label' => 'City / address',      'default' => 'Hengelo, Nederland'],
        'pw_footer_copyright'   => ['label' => 'Copyright text',      'default' => html_entity_decode('&copy; 2026 PerfectWelding &mdash; All rights reserved', ENT_QUOTES, 'UTF-8')],
        'pw_footer_col1_title'  => ['label' => 'Column 1 title',      'default' => 'SHOP'],
        'pw_footer_col1_links'  => ['label' => 'Column 1 links (Label|/url)', 'default' => "All products|/shop\nCups|/product-category/cups\nBack Caps|/product-category/back-caps\nTIGWARE|/product-category/tigware", 'type' => 'textarea'],
        'pw_footer_col2_title'  => ['label' => 'Column 2 title',      'default' => 'INFO'],
        'pw_footer_col2_links'  => ['label' => 'Column 2 links',      'default' => "About us|/about\nFAQ & Shipping|/faq\nBlog|/blog\nContact|/contact", 'type' => 'textarea'],
        'pw_footer_col3_title'  => ['label' => 'Column 3 title',      'default' => 'LEGAL'],
        'pw_footer_col3_links'  => ['label' => 'Column 3 links',      'default' => "Privacy Policy|/privacy-policy\nGeneral Terms|/terms\nCoC: 85191183|#", 'type' => 'textarea'],
        'pw_footer_payment_icons' => ['label' => 'Payment icons (one per line)', 'default' => "iDEAL\nVISA\nMC\nPayPal\nKlarna", 'type' => 'textarea'],
    ];

    foreach ($footer_fields as $id => $args) {
        $wp_customize->add_setting($id, ['default' => $args['default'], 'sanitize_callback' => 'sanitize_textarea_field', 'transport' => 'refresh']);
        $ctrl_args = ['section' => 'pw_footer_section', 'label' => __($args['label'], 'perfectwelding'), 'settings' => $id];
        if (($args['type'] ?? '') === 'textarea') {
            $wp_customize->add_control(new WP_Customize_Control($wp_customize, $id . '_ctrl', array_merge($ctrl_args, ['type' => 'textarea'])));
        } else {
            $wp_customize->add_control($id . '_ctrl', $ctrl_args);
        }
    }

    // Newsletter toggles + fields
    $wp_customize->add_setting('pw_nl_show', ['default' => true, 'sanitize_callback' => 'pw_sanitize_checkbox', 'transport' => 'refresh']);
    $wp_customize->add_control('pw_nl_show_ctrl', ['section' => 'pw_footer_section', 'label' => __('Show newsletter section', 'perfectwelding'), 'settings' => 'pw_nl_show', 'type' => 'checkbox']);

    foreach ([
        'pw_nl_heading'     => ['label' => 'Newsletter heading',     'default' => 'STAY UPDATED'],
        'pw_nl_subtext'     => ['label' => 'Newsletter subtext',     'default' => 'New products, tips, and exclusive offers. No spam.'],
        'pw_nl_placeholder' => ['label' => 'Email placeholder',      'default' => 'your@email.com'],
        'pw_nl_btn_text'    => ['label' => 'Subscribe button text',  'default' => 'Subscribe'],
    ] as $id => $args) {
        $wp_customize->add_setting($id, ['default' => $args['default'], 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh']);
        $wp_customize->add_control($id . '_ctrl', ['section' => 'pw_footer_section', 'label' => __($args['label'], 'perfectwelding'), 'settings' => $id]);
    }

});

/* Checkbox sanitizer helper */
if ( ! function_exists('pw_sanitize_checkbox') ) {
    function pw_sanitize_checkbox( $value ) {
        return (bool) $value;
    }
}

/* ─── SEO META TAGS ──────────────────────────────────────────────────────────
   Meta description, canonical, Open Graph en Product-schema.
   Vervangt de noodzaak van een SEO-plugin. */
add_action( 'wp_head', 'pw_seo_head', 1 );
function pw_seo_head() {

    $desc  = '';
    $title = wp_get_document_title();
    $url   = home_url( add_query_arg( array(), $GLOBALS['wp']->request ) );
    $image = '';

    if ( is_product() ) {
        global $post;
        $product = wc_get_product( $post->ID );
        $desc    = $product->get_short_description() ?: $product->get_description();
        $url     = get_permalink( $post->ID );
        $image   = wp_get_attachment_url( $product->get_image_id() );
    } elseif ( is_product_category() || is_product_tag() ) {
        $term = get_queried_object();
        $desc = $term->description ?: sprintf(
            '%s van PerfectWelding — %d producten, getest door lassers.',
            $term->name, $term->count
        );
        $url  = get_term_link( $term );
    } elseif ( is_shop() ) {
        $desc = 'Premium TIG-slijtdelen: cups, gaslenzen, diffusers, back caps en handschoenen. Zelf getest, snel geleverd vanuit Hengelo.';
        $url  = get_permalink( wc_get_page_id( 'shop' ) );
    } elseif ( is_front_page() ) {
        $desc = 'Professionele TIG-laskoppen, diffusers, back caps en handschoenen. Getest door lassers, voor lassers. Gratis verzending boven €100.';
        $url  = home_url( '/' );
    } elseif ( is_singular() ) {
        global $post;
        $desc  = has_excerpt( $post ) ? get_the_excerpt( $post ) : $post->post_content;
        $url   = get_permalink( $post->ID );
        $image = get_the_post_thumbnail_url( $post, 'large' );
    }

    $desc = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $desc ) ) );
    if ( mb_strlen( $desc ) > 155 ) {
        $desc = mb_substr( $desc, 0, 152 ) . '…';
    }
    if ( ! $image ) {
        $image = get_site_icon_url( 512 );
    }

    if ( $desc ) {
        echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
    }
    echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
    echo '<meta property="og:type" content="' . ( is_product() ? 'product' : 'website' ) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
    if ( $desc )  echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
    if ( $image ) echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}

/* ─── PRODUCT-SCHEMA (JSON-LD) ───────────────────────────────────────────────
   Zorgt dat prijs, voorraad en merk in je zoekresultaat kunnen verschijnen. */
add_action( 'wp_footer', 'pw_product_schema' );
function pw_product_schema() {
    if ( ! is_product() ) {
        return;
    }
    global $post;
    $p = wc_get_product( $post->ID );
    if ( ! $p ) {
        return;
    }

    $data = array(
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => wp_strip_all_tags( $p->get_name() ),
        'description' => trim( preg_replace( '/\s+/', ' ',
                          wp_strip_all_tags( $p->get_short_description() ?: $p->get_description() ) ) ),
        'url'         => get_permalink( $post->ID ),
        'brand'       => array( '@type' => 'Brand', 'name' => 'PerfectWelding' ),
        'offers'      => array(
            '@type'         => 'Offer',
            'url'           => get_permalink( $post->ID ),
            'price'         => wc_format_decimal( wc_get_price_to_display( $p ), 2 ),
            'priceCurrency' => get_woocommerce_currency(),
            'availability'  => $p->is_in_stock()
                                 ? 'https://schema.org/InStock'
                                 : 'https://schema.org/OutOfStock',
        ),
    );

    if ( $p->get_sku() ) {
        $data['sku'] = $p->get_sku();
    }
    if ( $p->get_image_id() ) {
        $data['image'] = wp_get_attachment_url( $p->get_image_id() );
    }

    echo '<script type="application/ld+json">'
       . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
       . '</script>' . "\n";
}