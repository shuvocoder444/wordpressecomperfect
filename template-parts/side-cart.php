<?php
/**
 * PerfectWelding — Side Cart Drawer Template
 * Renders the slide-in cart panel from the right side.
 *
 * @package PerfectWelding
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) return;

$cart        = WC()->cart;
$cart_items  = $cart ? $cart->get_cart() : [];
$cart_count  = $cart ? $cart->get_cart_contents_count() : 0;
$cart_url    = wc_get_cart_url();
$checkout    = wc_get_checkout_url();
?>

<!-- ── SIDE CART OVERLAY ─────────────────────────────────────── -->
<div class="pw-cart-overlay" id="pw-cart-overlay" aria-hidden="true"></div>

<!-- ── SIDE CART DRAWER ──────────────────────────────────────── -->
<div class="pw-side-cart" id="pw-side-cart" role="dialog"
     aria-modal="true"
     aria-label="<?php esc_attr_e( 'Shopping Cart', 'perfectwelding' ); ?>">

    <!-- Header -->
    <div class="pw-side-cart__header">
        <div class="pw-side-cart__title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 01-8 0"/>
            </svg>
            <?php esc_html_e( 'Your Cart', 'perfectwelding' ); ?>
            <span data-pw-i18n="Your Cart" style="display:none"></span>
            <span class="pw-side-cart__count" id="pw-side-cart-count"><?php echo esc_html( $cart_count ); ?></span>
        </div>

        <button class="pw-side-cart__close" id="pw-cart-close"
                aria-label="<?php esc_attr_e( 'Close cart', 'perfectwelding' ); ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    <!-- Cart Body (scrollable) -->
    <div class="pw-side-cart__body" id="pw-side-cart-body">

        <?php if ( empty( $cart_items ) ) : ?>
            <div class="pw-side-cart__empty" id="pw-side-cart-empty">
                <div class="pw-side-cart__empty-icon">
                    <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 01-8 0"/>
                    </svg>
                </div>
                <p class="pw-side-cart__empty-text">
                    <?php esc_html_e( 'Your cart is empty.', 'perfectwelding' ); ?>
                </p>
                <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
                   class="pw-side-cart__continue">
                    <?php esc_html_e( 'Continue Shopping', 'perfectwelding' ); ?>
                </a>
            </div>
        <?php endif; ?>

        <!-- Cart Items List (re-rendered by AJAX) -->
        <ul class="pw-side-cart__items" id="pw-side-cart-items"
            <?php echo empty( $cart_items ) ? 'style="display:none"' : ''; ?>>

            <?php foreach ( $cart_items as $cart_item_key => $cart_item ) :
                $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] === 0 ) continue;

                $product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
                $product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                $thumbnail    = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'thumbnail', [ 'class' => 'pw-sci__img' ] ), $cart_item, $cart_item_key );
                $product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                $product_subtotal = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
                $qty          = $cart_item['quantity'];
                $remove_url   = wc_get_cart_remove_url( $cart_item_key );
            ?>
            <li class="pw-sci" data-key="<?php echo esc_attr( $cart_item_key ); ?>">

                <!-- Product Image -->
                <div class="pw-sci__thumb">
                    <a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>">
                        <?php echo $thumbnail; ?>
                    </a>
                </div>

                <!-- Product Info -->
                <div class="pw-sci__info">
                    <a class="pw-sci__name" href="<?php echo esc_url( get_permalink( $product_id ) ); ?>">
                        <?php echo esc_html( $product_name ); ?>
                    </a>

                    <div class="pw-sci__price"><?php echo $product_price; ?></div>

                    <!-- Quantity + Remove Row -->
                    <div class="pw-sci__controls">
                        <div class="pw-sci__qty">
                            <button class="pw-sci__qty-btn" data-action="minus"
                                    data-key="<?php echo esc_attr( $cart_item_key ); ?>"
                                    aria-label="<?php esc_attr_e( 'Decrease quantity', 'perfectwelding' ); ?>">−</button>
                            <input  class="pw-sci__qty-val"
                                    type="number"
                                    min="1"
                                    max="<?php echo esc_attr( $_product->get_max_purchase_quantity() ); ?>"
                                    value="<?php echo esc_attr( $qty ); ?>"
                                    data-key="<?php echo esc_attr( $cart_item_key ); ?>"
                                    aria-label="<?php esc_attr_e( 'Quantity', 'perfectwelding' ); ?>">
                            <button class="pw-sci__qty-btn" data-action="plus"
                                    data-key="<?php echo esc_attr( $cart_item_key ); ?>"
                                    aria-label="<?php esc_attr_e( 'Increase quantity', 'perfectwelding' ); ?>">+</button>
                        </div>

                        <div class="pw-sci__subtotal"><?php echo $product_subtotal; ?></div>

                        <button class="pw-sci__remove"
                                data-key="<?php echo esc_attr( $cart_item_key ); ?>"
                                aria-label="<?php esc_attr_e( 'Remove item', 'perfectwelding' ); ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                                <path d="M10 11v6M14 11v6"/>
                                <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
                            </svg>
                            <?php esc_html_e( 'Remove', 'perfectwelding' ); ?>
                        </button>
                    </div>
                </div>

            </li>
            <?php endforeach; ?>

        </ul>

    </div><!-- /.pw-side-cart__body -->

    <!-- Footer (Subtotal + Buttons) -->
    <div class="pw-side-cart__footer" id="pw-side-cart-footer"
         <?php echo empty( $cart_items ) ? 'style="display:none"' : ''; ?>>

        <div class="pw-side-cart__subtotal">
            <span data-pw-i18n="Subtotal"><?php esc_html_e( 'Subtotal', 'perfectwelding' ); ?></span>
            <span class="pw-side-cart__subtotal-val" id="pw-side-cart-subtotal">
                <?php echo WC()->cart->get_cart_subtotal(); ?>
            </span>
        </div>

        <div class="pw-side-cart__actions">
            <a href="<?php echo esc_url( $cart_url ); ?>" class="pw-side-cart__btn pw-side-cart__btn--secondary" data-pw-i18n="View Cart →">
                <?php esc_html_e( 'View Cart', 'perfectwelding' ); ?>
            </a>
            <a href="<?php echo esc_url( $checkout ); ?>" class="pw-side-cart__btn pw-side-cart__btn--primary" data-pw-i18n="Checkout">
                <?php esc_html_e( 'Checkout', 'perfectwelding' ); ?>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                </svg>
            </a>
        </div>

    </div><!-- /.pw-side-cart__footer -->

</div><!-- /.pw-side-cart -->
