<?php
/**
 * PerfectWelding — Side Cart Functions
 * Include via: require_once get_template_directory() . '/inc/side-cart.php';
 *
 * @package PerfectWelding
 */

defined( 'ABSPATH' ) || exit;

/* ─── ENQUEUE ASSETS ───────────────────────────────────────────────────────── */

add_action( 'wp_enqueue_scripts', 'pw_side_cart_assets' );

function pw_side_cart_assets() {
    if ( ! class_exists( 'WooCommerce' ) ) return;

    wp_enqueue_style(
        'pw-side-cart',
        get_template_directory_uri() . '/assets/css/side-cart.min.css',
        [ 'pw-main' ],
        pw_asset_version('/assets/css/side-cart.min.css')
    );

    // side-cart.js depends on pw-main (which defines pwAddToCart originally)
    // we load AFTER pw-main so we can override the functions
    wp_enqueue_script(
        'pw-side-cart',
        get_template_directory_uri() . '/assets/js/side-cart.min.js',
        [ 'jquery', 'pw-main' ],
        pw_asset_version('/assets/js/side-cart.min.js'),
        true
    );
    wp_script_add_data('pw-side-cart', 'defer', true);

    // Localize script variables for side cart functionality
    wp_localize_script( 'pw-side-cart', 'pw_cart_vars', [
        'ajax_url'  => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'pw_cart_nonce' ),
        'nonce_add' => wp_create_nonce( 'pw_nonce' ), // for pw_add_to_cart handler
        'cart_url'  => wc_get_cart_url(),
        'checkout'  => wc_get_checkout_url(),
        'i18n'      => [
            'add_to_cart'    => esc_html__( 'Add to Cart', 'perfectwelding' ),
            'added'          => esc_html__( 'Product added to cart!', 'perfectwelding' ),
            'added_short'    => esc_html__( 'Added!', 'perfectwelding' ),
            'view_cart'      => esc_html__( 'View Cart', 'perfectwelding' ),
            'checkout'       => esc_html__( 'Checkout', 'perfectwelding' ),
            'cart_empty'     => esc_html__( 'Your cart is empty.', 'perfectwelding' ),
            'continue'       => esc_html__( 'Continue Shopping', 'perfectwelding' ),
            'subtotal'       => esc_html__( 'Subtotal', 'perfectwelding' ),
            'remove'         => esc_html__( 'Remove', 'perfectwelding' ),
            'quantity'       => esc_html__( 'Quantity', 'perfectwelding' ),
            'select_options' => esc_html__( 'Please select all options first.', 'perfectwelding' ),
            'error'          => esc_html__( 'Something went wrong. Please try again.', 'perfectwelding' ),
        ],
    ] );

}

/* ─── RENDER SIDE CART IN FOOTER ───────────────────────────────────────────── */

add_action( 'wp_footer', 'pw_render_side_cart', 5 );

function pw_render_side_cart() {
    if ( ! class_exists( 'WooCommerce' ) ) return;
    if ( ( function_exists('is_cart') && is_cart() ) || ( function_exists('is_checkout') && is_checkout() ) ) return;
    $tpl = get_template_directory() . '/template-parts/side-cart.php';
    if ( file_exists( $tpl ) ) include $tpl;
}

/* ─── AJAX: GET CART HTML ──────────────────────────────────────────────────── */

add_action( 'wp_ajax_pw_get_cart_html',        'pw_ajax_get_cart_html' );
add_action( 'wp_ajax_nopriv_pw_get_cart_html', 'pw_ajax_get_cart_html' );

function pw_ajax_get_cart_html() {
    check_ajax_referer( 'pw_cart_nonce', 'nonce' );

    if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart ) {
        wp_send_json_error( [ 'message' => 'WooCommerce not available.' ] );
    }

    WC()->cart->calculate_totals();

    wp_send_json_success( pw_get_side_cart_data() );
}

/**
 * Builds the side-cart items HTML + totals in one place, so every AJAX
 * handler (get/update/remove) can reuse it instead of each one doing its
 * own extra request just to re-fetch the markup. This is what used to
 * cause 2–3 separate AJAX round-trips (and DB queries) per single cart
 * action — now it's built once, in-process, and returned with whichever
 * action already ran.
 */
function pw_get_side_cart_data() {
    $cart_items = WC()->cart->get_cart();
    $count      = WC()->cart->get_cart_contents_count();

    ob_start();
    foreach ( $cart_items as $key => $item ) {
        $_product = apply_filters( 'woocommerce_cart_item_product', $item['data'], $item, $key );
        if ( ! $_product || ! $_product->exists() || $item['quantity'] === 0 ) continue;

        $pid       = apply_filters( 'woocommerce_cart_item_product_id', $item['product_id'], $item, $key );
        $name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $item, $key );
        $thumb     = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'thumbnail', [ 'class' => 'pw-sci__img' ] ), $item, $key );
        $price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $item, $key );
        $subtotal  = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $item['quantity'] ), $item, $key );
        $qty       = $item['quantity'];
        $max       = $_product->get_max_purchase_quantity();
        $max       = $max > 0 ? $max : 99;
        ?>
        <li class="pw-sci" data-key="<?php echo esc_attr( $key ); ?>">
            <div class="pw-sci__thumb">
                <a href="<?php echo esc_url( get_permalink( $pid ) ); ?>"><?php echo $thumb; ?></a>
            </div>
            <div class="pw-sci__info">
                <a class="pw-sci__name" href="<?php echo esc_url( get_permalink( $pid ) ); ?>"><?php echo esc_html( $name ); ?></a>
                <div class="pw-sci__price"><?php echo $price; ?></div>
                <div class="pw-sci__controls">
                    <div class="pw-sci__qty">
                        <button class="pw-sci__qty-btn" data-action="minus" data-key="<?php echo esc_attr( $key ); ?>" aria-label="<?php esc_attr_e( 'Decrease quantity', 'perfectwelding' ); ?>">−</button>
                        <input class="pw-sci__qty-val" type="number" min="1" max="<?php echo esc_attr( $max ); ?>" value="<?php echo esc_attr( $qty ); ?>" data-key="<?php echo esc_attr( $key ); ?>" aria-label="<?php esc_attr_e( 'Quantity', 'perfectwelding' ); ?>">
                        <button class="pw-sci__qty-btn" data-action="plus" data-key="<?php echo esc_attr( $key ); ?>" aria-label="<?php esc_attr_e( 'Increase quantity', 'perfectwelding' ); ?>">+</button>
                    </div>
                    <div class="pw-sci__subtotal"><?php echo $subtotal; ?></div>
                    <button class="pw-sci__remove" data-key="<?php echo esc_attr( $key ); ?>" aria-label="<?php esc_attr_e( 'Remove item', 'perfectwelding' ); ?>">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                        <?php esc_html_e( 'Remove', 'perfectwelding' ); ?>
                    </button>
                </div>
            </div>
        </li>
        <?php
    }
    $items_html = ob_get_clean();

    return [
        'items_html' => $items_html,
        'count'      => $count,
        'subtotal'   => WC()->cart->get_cart_subtotal(),
    ];
}

/* ─── AJAX: UPDATE QUANTITY ────────────────────────────────────────────────── */

add_action( 'wp_ajax_pw_update_cart_qty',        'pw_ajax_update_cart_qty' );
add_action( 'wp_ajax_nopriv_pw_update_cart_qty', 'pw_ajax_update_cart_qty' );

function pw_ajax_update_cart_qty() {
    check_ajax_referer( 'pw_cart_nonce', 'nonce' );

    $cart_key = sanitize_text_field( $_POST['cart_key'] ?? '' );
    $qty      = absint( $_POST['qty'] ?? 1 );

    if ( ! $cart_key ) wp_send_json_error( [ 'message' => 'Invalid cart key.' ] );

    if ( isset( WC()->cart->cart_contents[ $cart_key ] ) ) {
        WC()->cart->set_quantity( $cart_key, $qty > 0 ? $qty : 0, true );
        WC()->cart->calculate_totals();
        // Return items_html directly — the client used to make a second
        // AJAX call (pw_get_cart_html) right after this one just to get
        // the refreshed markup. Sending it back here halves the requests.
        wp_send_json_success( pw_get_side_cart_data() );
    } else {
        wp_send_json_error( [ 'message' => 'Cart item not found.' ] );
    }
}

/* ─── AJAX: REMOVE ITEM ────────────────────────────────────────────────────── */

add_action( 'wp_ajax_pw_remove_cart_item',        'pw_ajax_remove_cart_item' );
add_action( 'wp_ajax_nopriv_pw_remove_cart_item', 'pw_ajax_remove_cart_item' );

function pw_ajax_remove_cart_item() {
    check_ajax_referer( 'pw_cart_nonce', 'nonce' );

    $cart_key = sanitize_text_field( $_POST['cart_key'] ?? '' );
    if ( ! $cart_key ) wp_send_json_error( [ 'message' => 'Invalid cart key.' ] );

    if ( WC()->cart->remove_cart_item( $cart_key ) ) {
        WC()->cart->calculate_totals();
        // Same fix as above — one response instead of two.
        wp_send_json_success( pw_get_side_cart_data() );
    } else {
        wp_send_json_error( [ 'message' => 'Could not remove item.' ] );
    }
}

/* ─── WC FRAGMENTS: keep badges in sync ────────────────────────────────────── */

add_filter( 'woocommerce_add_to_cart_fragments', 'pw_side_cart_fragments' );

function pw_side_cart_fragments( $fragments ) {
    $count    = WC()->cart->get_cart_contents_count();
    $subtotal = WC()->cart->get_cart_subtotal();

    $fragments['.cart-badge']            = '<span class="cart-badge">' . $count . '</span>';
    $fragments['#pw-side-cart-count']    = '<span class="pw-side-cart__count" id="pw-side-cart-count">' . $count . '</span>';
    $fragments['#pw-side-cart-subtotal'] = '<span class="pw-side-cart__subtotal-val" id="pw-side-cart-subtotal">' . $subtotal . '</span>';

    return $fragments;
}
