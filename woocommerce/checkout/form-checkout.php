<?php
/**
 * Checkout Page — PerfectWelding
 */
defined('ABSPATH') || exit;

$pw_headers_sent = ( did_action('wp_head') > 0 );
if ( ! $pw_headers_sent ) get_header();

if ( class_exists('WooCommerce') && WC()->payment_gateways() ) {
    WC()->payment_gateways()->init();
    $available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
    WC()->payment_gateways()->set_current_gateway($available_gateways);
}

?>

<div class="checkout-page">

  <!-- TOP BANNER -->
  <div class="pw-page-banner">
    <div class="pw-page-brand">PERFECTWELDING</div>
    <h1 class="pw-page-big-title">AFREKENEN</h1>
  </div>

  <!-- CHECKOUT HEADER -->
  <div class="shop-header pw-step-header">
    <div>
      <div class="shop-count">Stap 2 van 2</div>
      <h2 class="shop-title">AFREKENEN</h2>
    </div>
    <div class="pw-steps-bar">
      <div class="pw-step">
        <a href="<?php echo esc_url(wc_get_cart_url()); ?>">1. Winkelwagen</a>
      </div>
      <div class="pw-step pw-step--active">2. Gegevens</div>
      <div class="pw-step">3. Betaling</div>
    </div>
  </div>

  <?php do_action('woocommerce_before_checkout_form', $checkout); ?>

  <form name="checkout" method="post" class="checkout woocommerce-checkout"
    action="<?php echo esc_url(wc_get_checkout_url()); ?>"
    enctype="multipart/form-data">

    <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>

    <div class="checkout-grid">

      <!-- LEFT: BILLING / SHIPPING -->
      <div class="checkout-left">

        <?php if ($checkout->get_checkout_fields()) : ?>

        <!-- Login hint -->
        <?php if (!is_user_logged_in() && $checkout->is_registration_enabled()) : ?>
        <div class="pw-checkout-login-hint">
          <div class="checkout-section-title">AL EEN ACCOUNT?</div>
          <p><?php echo apply_filters('woocommerce_checkout_login_message', esc_html__('Heb je al een account?', 'perfectwelding')); ?>
            <a href="#" class="pw-show-login">Inloggen &rarr;</a>
          </p>
          <div id="pw-login-box" style="display:none;margin-top:12px">
            <?php woocommerce_login_form(['redirect' => wc_get_checkout_url(), 'hidden' => true]); ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- BILLING DETAILS -->
        <div class="checkout-section">
          <div class="checkout-section-title">Facturatiegegevens</div>
          <div class="checkout-fields">
            <?php do_action('woocommerce_checkout_billing'); ?>
          </div>
        </div>

        <!-- SHIPPING -->
        <?php if (WC()->cart->needs_shipping_address()) : ?>
        <div class="checkout-section pw-checkout-section--mt">
          <div class="pw-checkout-shipping-header">
            <div class="checkout-section-title">Verzendadres</div>
            <label class="pw-ship-toggle">
              <input type="checkbox" id="ship-to-different-address" name="ship_to_different_address" value="1">
              Ander adres
            </label>
          </div>
          <div id="shipping-address-section" style="display:none">
            <div class="checkout-fields">
              <?php do_action('woocommerce_checkout_shipping'); ?>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- ORDER NOTES -->
        <?php foreach ($checkout->get_checkout_fields('order') as $key => $field) : ?>
        <div class="checkout-section pw-checkout-section--mt">
          <div class="checkout-section-title">Opmerking</div>
          <?php woocommerce_form_field($key, $field, $checkout->get_value($key)); ?>
        </div>
        <?php endforeach; ?>

        <?php endif; ?>
      </div><!-- .checkout-left -->

      <!-- RIGHT: ORDER SUMMARY + PAYMENT -->
      <div class="checkout-right">

        <!-- ORDER REVIEW -->
        <div class="pw-checkout-summary">
          <div class="pw-summary-label">BESTELOVERZICHT</div>

          <div class="pw-summary-products">
            <?php foreach (WC()->cart->get_cart() as $cart_item) :
              $p   = $cart_item['data'];
              $qty = $cart_item['quantity'];
            ?>
            <div class="pw-checkout-product-row">
              <div class="pw-checkout-product-info">
                <div class="pw-checkout-thumb">
                  <?php echo $p->get_image('thumbnail'); ?>
                </div>
                <span class="pw-checkout-product-name">
                  <?php echo esc_html($p->get_name()); ?>
                  <?php if ($qty > 1) echo '<span class="pw-summary-qty"> ×' . $qty . '</span>'; ?>
                </span>
              </div>
              <span class="pw-summary-price"><?php echo WC()->cart->get_product_subtotal($p, $qty); ?></span>
            </div>
            <?php endforeach; ?>
          </div>

          <?php do_action('woocommerce_review_order_before_order_total'); ?>

          <div class="pw-summary-totals">
            <div class="pw-total-row">
              <span>Subtotaal excl. BTW</span>
              <span><?php echo WC()->cart->get_cart_subtotal(); ?></span>
            </div>

            <?php
            WC()->cart->calculate_shipping();
            WC()->cart->calculate_totals();
            $pw_packages       = WC()->shipping()->get_packages();
            $pw_chosen_methods = WC()->session->get('chosen_shipping_methods', []);
            $pw_has_shipping   = WC()->cart->needs_shipping();
            ?>
            <div class="pw-total-row">
              <span>Verzendkosten</span>
              <span class="pw-accent">
                <?php
                if (!$pw_has_shipping) {
                    echo 'Gratis';
                } elseif (empty($pw_packages)) {
                    echo '<span class="pw-muted-sm">Afhankelijk van adres</span>';
                } else {
                    $displayed = false;
                    foreach ($pw_packages as $pkg_key => $pkg) {
                        $pkg_chosen = $pw_chosen_methods[$pkg_key] ?? '';
                        $pkg_rates  = $pkg['rates'] ?? [];
                        if (!empty($pkg_chosen) && isset($pkg_rates[$pkg_chosen])) {
                            $r = $pkg_rates[$pkg_chosen];
                            echo $r->cost > 0 ? wc_price($r->cost) : 'Gratis';
                            $displayed = true; break;
                        } elseif (!empty($pkg_rates)) {
                            uasort($pkg_rates, function($a, $b){ return $a->cost <=> $b->cost; });
                            $first = reset($pkg_rates);
                            $pw_chosen_methods[$pkg_key] = $first->id;
                            WC()->session->set('chosen_shipping_methods', $pw_chosen_methods);
                            echo $first->cost > 0 ? wc_price($first->cost) : 'Gratis';
                            $displayed = true; break;
                        }
                    }
                    if (!$displayed) echo '<span class="pw-muted-sm">Voer adres in</span>';
                }
                ?>
              </span>
            </div>

            <?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
            <div class="pw-total-row">
              <span>BTW (<?php echo esc_html($tax->label); ?>)</span>
              <span><?php echo $tax->formatted_amount; ?></span>
            </div>
            <?php endforeach; ?>

            <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
            <div class="pw-total-row pw-total-row--discount">
              <span>Korting (<?php echo esc_html($code); ?>)</span>
              <span>−<?php echo wc_price(WC()->cart->get_coupon_discount_amount($code)); ?></span>
            </div>
            <?php endforeach; ?>
          </div>

          <?php do_action('woocommerce_review_order_after_order_total'); ?>

          <div class="pw-summary-grand-total">
            <span>TOTAAL INCL. BTW</span>
            <span class="pw-grand-total-price"><?php echo WC()->cart->get_total(); ?></span>
          </div>
        </div>

        <!-- PAYMENT -->
        <div id="order_review" class="pw-checkout-payment woocommerce-checkout-review-order">
          <div class="pw-summary-label">BETAALMETHODE</div>
          <?php
          do_action('woocommerce_checkout_before_order_review');
          if ( function_exists('woocommerce_checkout_payment') ) {
              woocommerce_checkout_payment();
          }
          do_action('woocommerce_checkout_after_order_review');
          ?>
        </div>

        <!-- Trust signals -->
        <div class="pw-checkout-trust">
          <?php foreach (['🔒 SSL beveiligd', '✓ iDEAL / PayPal / Visa', '↺ 14 dagen retour'] as $t) : ?>
          <div class="pw-trust-badge"><?php echo esc_html($t); ?></div>
          <?php endforeach; ?>
        </div>

      </div><!-- .checkout-right -->

    </div><!-- .checkout-grid -->

    <?php do_action('woocommerce_after_checkout_form', $checkout); ?>

  </form>
</div>

<script>
document.getElementById('ship-to-different-address')?.addEventListener('change', function() {
    document.getElementById('shipping-address-section').style.display = this.checked ? 'block' : 'none';
});
document.querySelector('.pw-show-login')?.addEventListener('click', function(e) {
    e.preventDefault();
    var box = document.getElementById('pw-login-box');
    if (box) box.style.display = box.style.display === 'none' ? 'block' : 'none';
});
</script>

<?php if (!$pw_headers_sent) get_footer(); ?>
