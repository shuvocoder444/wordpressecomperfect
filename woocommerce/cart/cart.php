<?php
/**
 * Cart Page — PerfectWelding
 */
defined('ABSPATH') || exit;
?>

<div class="cart-page">

  <!-- TOP BANNER -->
  <div class="pw-page-banner">
    <div class="pw-page-brand">PERFECTWELDING</div>
    <h1 class="pw-page-big-title">WINKELWAGEN</h1>
  </div>

  <!-- CART HEADER / STEPS -->
  <div class="shop-header pw-step-header">
    <div>
      <div class="shop-count">Stap 1 van 2</div>
      <h2 class="shop-title">WINKELWAGEN</h2>
    </div>
    <div class="pw-steps-bar">
      <div class="pw-step pw-step--active">1. Winkelwagen</div>
      <div class="pw-step"><a href="<?php echo esc_url(wc_get_checkout_url()); ?>">2. Gegevens</a></div>
      <div class="pw-step">3. Betaling</div>
    </div>
  </div>

  <?php wc_print_notices(); ?>

  <?php if (WC()->cart->is_empty()) : ?>
  <!-- EMPTY CART -->
  <div class="pw-cart-empty">
    <div class="pw-cart-empty-bg">LEEG</div>
    <p>Je winkelwagen is nog leeg.</p>
    <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn-primary">Ga naar shop &rarr;</a>
  </div>

  <?php else : ?>

  <div class="pw-cart-grid">

    <!-- CART ITEMS -->
    <div class="pw-cart-items-col">

      <?php do_action('woocommerce_before_cart_table'); ?>

      <form method="post" action="<?php echo esc_url(wc_get_cart_url()); ?>" id="pw-cart-form">

        <div class="cart-items-list">
          <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
            $product_obj = $cart_item['data'];
            $product_id  = $cart_item['product_id'];
            $qty         = $cart_item['quantity'];
            $subtotal    = WC()->cart->get_product_subtotal($product_obj, $qty);
            $img         = $product_obj->get_image('thumbnail', ['class' => 'cart-item-img-el']);
            $product_url = get_permalink($product_id);
          ?>
          <div class="cart-item-row" data-key="<?php echo esc_attr($cart_item_key); ?>">

            <div class="cart-item-img">
              <a href="<?php echo esc_url($product_url); ?>">
                <?php if ($img) echo $img; else echo '<div class="cart-img-placeholder">' . pw_tig_svg() . '</div>'; ?>
              </a>
            </div>

            <div class="cart-item-info">
              <a href="<?php echo esc_url($product_url); ?>" class="cart-item-name">
                <?php echo esc_html($product_obj->get_name()); ?>
              </a>
              <?php echo WC()->cart->get_item_data($cart_item); ?>
              <div class="cart-item-price"><?php echo $subtotal; ?></div>
            </div>

            <div class="cart-item-qty">
              <div class="qty-wrap pw-qty-sm">
                <button type="button" class="qty-btn" onclick="pwCartQtyChange(this, -1)">−</button>
                <input type="number" name="cart[<?php echo esc_attr($cart_item_key); ?>][qty]"
                  value="<?php echo esc_attr($qty); ?>" min="0" max="99"
                  class="qty-input cart-qty-input"
                  data-key="<?php echo esc_attr($cart_item_key); ?>"
                  onchange="this.form.submit()">
                <button type="button" class="qty-btn" onclick="pwCartQtyChange(this, 1)">+</button>
              </div>
            </div>

            <a href="<?php echo esc_url(wc_get_cart_remove_url($cart_item_key)); ?>"
               class="cart-item-remove" title="Verwijder">&times;</a>

          </div>
          <?php endforeach; ?>
        </div>

        <div class="pw-cart-actions">
          <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
          <button type="submit" name="update_cart" value="1" class="btn-ghost pw-btn-sm">
            ↻ Bijwerken
          </button>
        </div>

      </form>

      <?php do_action('woocommerce_after_cart_table'); ?>

      <!-- Coupon -->
      <div class="pw-cart-coupon">
        <div class="pw-coupon-label">KORTINGSCODE</div>
        <div class="pw-coupon-row">
          <input type="text" id="pw-coupon-code" class="pw-coupon-field" placeholder="Code">
          <button type="button" onclick="pwApplyCoupon()" class="btn-ghost pw-btn-sm">Toepassen</button>
        </div>
        <div id="pw-coupon-msg" class="pw-coupon-msg"></div>
      </div>

      <!-- Navigation -->
      <div class="pw-cart-nav">
        <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn-ghost pw-btn-sm">
          &larr; Verder winkelen
        </a>
        <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="btn-primary">
          Doorgaan naar gegevens &rarr;
        </a>
      </div>

    </div><!-- end cart items col -->

    <!-- ORDER SUMMARY -->
    <div class="pw-cart-summary-col">

      <div class="pw-summary-label">BESTELOVERZICHT</div>

      <div class="pw-summary-products">
        <?php foreach (WC()->cart->get_cart() as $cart_item) :
          $p   = $cart_item['data'];
          $qty = $cart_item['quantity'];
        ?>
        <div class="pw-summary-row">
          <span class="pw-summary-name">
            <?php echo esc_html($p->get_name()); ?>
            <?php if ($qty > 1) echo '<span class="pw-summary-qty"> ×' . $qty . '</span>'; ?>
          </span>
          <span class="pw-summary-price"><?php echo WC()->cart->get_product_subtotal($p, $qty); ?></span>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="pw-summary-totals">
        <div class="pw-total-row">
          <span>Subtotaal excl. BTW</span>
          <span><?php echo WC()->cart->get_cart_subtotal(); ?></span>
        </div>
        <div class="pw-total-row">
          <span>BTW (21%)</span>
          <span><?php echo wc_price(WC()->cart->get_cart_tax()); ?></span>
        </div>
        <div class="pw-total-row">
          <span>Verzendkosten</span>
          <span class="pw-accent">
            <?php
            $shipping_total = WC()->cart->get_shipping_total();
            echo $shipping_total > 0 ? wc_price($shipping_total) : 'Gratis';
            ?>
          </span>
        </div>
        <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
        <div class="pw-total-row pw-total-row--discount">
          <span>Korting (<?php echo esc_html($code); ?>)</span>
          <span>−<?php echo wc_price(WC()->cart->get_coupon_discount_amount($code)); ?></span>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="pw-summary-grand-total">
        <span>TOTAAL INCL. BTW</span>
        <span class="pw-grand-total-price"><?php echo WC()->cart->get_total(); ?></span>
      </div>

      <?php
      $cart_total = WC()->cart->get_cart_contents_total();
      $threshold  = 100;
      $remaining  = $threshold - $cart_total;
      ?>
      <div class="pw-shipping-notice <?php echo $remaining > 0 ? 'pw-notice--warn' : 'pw-notice--success'; ?>">
        <?php if ($remaining > 0) : ?>
          Nog &euro;<?php echo number_format($remaining, 2, ',', '.'); ?> tot gratis verzending!
        <?php else : ?>
          ✓ Je hebt gratis verzending!
        <?php endif; ?>
      </div>

      <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="btn-primary pw-summary-checkout-btn">
        Afrekenen &rarr;
      </a>

    </div><!-- end order summary col -->

  </div><!-- end pw-cart-grid -->

  <?php endif; ?>

</div><!-- .cart-page -->
