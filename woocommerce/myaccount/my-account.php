<?php
/**
 * My Account page — PerfectWelding custom layout
 * Shows login/register for guests, dashboard for logged-in users
 */
defined('ABSPATH') || exit;

if ( is_user_logged_in() ) :
  // ── DASHBOARD ──────────────────────────────────────────────────
  $user      = wp_get_current_user();
  $firstname = $user->first_name ?: $user->display_name;
  $initial   = strtoupper( mb_substr( $firstname, 0, 1 ) );
  $email     = $user->user_email;

  // Order counts
  $order_count = wc_get_customer_order_count( $user->ID );
  $address     = get_user_meta( $user->ID, 'billing_address_1', true ) ? __('Opgeslagen', 'perfectwelding') : '—';

  $current_tab = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'dashboard';
  $base_url    = get_permalink( get_option('woocommerce_myaccount_page_id') );

  $nav_items = [
    'dashboard' => [
      'label' => 'Dashboard',
      'icon'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
      'url'   => $base_url,
    ],
    'orders' => [
      'label' => 'Bestellingen',
      'icon'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
      'url'   => wc_get_account_endpoint_url('orders'),
    ],
    'edit-address' => [
      'label' => 'Adressen',
      'icon'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>',
      'url'   => wc_get_account_endpoint_url('edit-address'),
    ],
    'edit-account' => [
      'label' => 'Account details',
      'icon'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
      'url'   => wc_get_account_endpoint_url('edit-account'),
    ],
  ];
?>
<div class="pw-dashboard-page pw-dashboard-wrap">

  <!-- ── SIDEBAR ─────────────────────────────────────────────── -->
  <aside class="pw-dash-sidebar">
    <div class="pw-dash-user">
      <div class="pw-dash-avatar"><?php echo esc_html($initial); ?></div>
      <div class="pw-dash-name"><?php echo esc_html(strtoupper($firstname)); ?></div>
      <div class="pw-dash-email"><?php echo esc_html($email); ?></div>
    </div>

    <nav class="pw-dash-nav">
      <?php foreach ($nav_items as $key => $item) :
        $is_active = (is_wc_endpoint_url($key)) || ($key === 'dashboard' && is_account_page() && !is_wc_endpoint_url('orders') && !is_wc_endpoint_url('edit-address') && !is_wc_endpoint_url('edit-account'));
      ?>
      <a href="<?php echo esc_url($item['url']); ?>" class="pw-dash-nav-item <?php echo $is_active ? 'active' : ''; ?>">
        <?php echo $item['icon']; ?>
        <?php echo esc_html($item['label']); ?>
      </a>
      <?php endforeach; ?>

      <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="pw-dash-nav-item logout">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Uitloggen
      </a>
    </nav>
  </aside>

  <!-- ── MAIN CONTENT ──────────────────────────────────────────── -->
  <div class="pw-dash-main">

    <?php if ( is_account_page() && !is_wc_endpoint_url('orders') && !is_wc_endpoint_url('edit-address') && !is_wc_endpoint_url('edit-account') && !is_wc_endpoint_url('view-order') ) : ?>
    <!-- Dashboard Home -->
    <div class="pw-dash-greeting">
      <div class="pw-dash-greeting-eyebrow">PerfectWelding Account</div>
      <h1>WELKOM,<br><span style="color:var(--accent)"><?php echo esc_html(strtoupper($firstname)); ?></span></h1>
      <p><?php _e('Beheer je bestellingen, adressen en accountgegevens.', 'perfectwelding'); ?></p>
    </div>

    <!-- Stats -->
    <div class="pw-dash-stats">
      <div class="pw-dash-stat">
        <div class="pw-dash-stat-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div class="pw-dash-stat-num"><?php echo esc_html($order_count); ?></div>
        <div class="pw-dash-stat-label">Bestellingen</div>
      </div>
      <div class="pw-dash-stat">
        <div class="pw-dash-stat-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <div class="pw-dash-stat-num"><?php echo $address !== '—' ? '1' : '0'; ?></div>
        <div class="pw-dash-stat-label">Adressen</div>
      </div>
      <div class="pw-dash-stat">
        <div class="pw-dash-stat-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div class="pw-dash-stat-num">1</div>
        <div class="pw-dash-stat-label">Account</div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="pw-dash-section-title">Recente bestellingen</div>
    <div class="pw-dash-orders">
      <div class="pw-dash-orders-header">
        <span class="pw-dash-orders-title">Overzicht</span>
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="pw-dash-orders-all">Alle bestellingen →</a>
      </div>
      <?php
      $orders = wc_get_orders(['customer' => $user->ID, 'limit' => 3, 'orderby' => 'date', 'order' => 'DESC']);
      if ( empty($orders) ) : ?>
      <div class="pw-dash-empty">
        <div class="pw-dash-empty-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        </div>
        <p><?php _e("Je hebt nog geen bestellingen geplaatst.", 'perfectwelding'); ?></p>
        <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="pw-dash-empty-btn">SHOP NOW →</a>
      </div>
      <?php else : ?>
      <table class="woocommerce-orders-table" style="padding:0 24px 24px;display:block">
        <thead><tr>
          <th>#</th><th>Datum</th><th>Status</th><th>Totaal</th><th></th>
        </tr></thead>
        <tbody>
        <?php foreach ($orders as $order) : ?>
        <tr>
          <td><a href="<?php echo esc_url($order->get_view_order_url()); ?>" style="color:var(--accent)">#<?php echo $order->get_order_number(); ?></a></td>
          <td><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></td>
          <td><span class="woocommerce-order-status status-<?php echo esc_attr($order->get_status()); ?>"><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></span></td>
          <td><?php echo wp_kses_post($order->get_formatted_order_total()); ?></td>
          <td><a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="woocommerce-button">Bekijken</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

    <!-- Quick Links -->
    <div class="pw-dash-section-title">Snelle toegang</div>
    <div class="pw-dash-quick">
      <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-address')); ?>" class="pw-dash-quick-card">
        <div class="pw-dash-quick-card-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <div class="pw-dash-quick-card-text">
          <strong>Adressen</strong>
          <span>Beheer je leverings- en factuuradressen</span>
        </div>
      </a>
      <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>" class="pw-dash-quick-card">
        <div class="pw-dash-quick-card-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div class="pw-dash-quick-card-text">
          <strong>Account details</strong>
          <span>Wijzig je naam, e-mail en wachtwoord</span>
        </div>
      </a>
      <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="pw-dash-quick-card">
        <div class="pw-dash-quick-card-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        </div>
        <div class="pw-dash-quick-card-text">
          <strong>Verder winkelen</strong>
          <span>Bekijk ons assortiment TIG-onderdelen</span>
        </div>
      </a>
      <a href="<?php echo esc_url(get_permalink(get_page_by_path('contact'))); ?>" class="pw-dash-quick-card">
        <div class="pw-dash-quick-card-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        </div>
        <div class="pw-dash-quick-card-text">
          <strong>Contact</strong>
          <span>Vragen? Neem contact met ons op</span>
        </div>
      </a>
    </div>

    <?php else : ?>
    <!-- Other WooCommerce account endpoints (orders, addresses, etc.) -->
    <div class="pw-dash-greeting">
      <div class="pw-dash-greeting-eyebrow">PerfectWelding Account</div>
    </div>
    <div class="woocommerce-MyAccount-content">
      <?php wc_print_notices(); do_action('woocommerce_account_content'); ?>
    </div>
    <?php endif; ?>

  </div>
</div>

<?php else : ?>

  <!-- ── LOGIN / REGISTER ──────────────────────────────────────── -->
  <?php
  $show_reg = isset($_GET['action']) && $_GET['action'] === 'register';
  $login_url = get_permalink(get_option('woocommerce_myaccount_page_id'));
  $reg_url   = add_query_arg('action', 'register', $login_url);
  ?>
  <div class="pw-auth-page pw-auth-wrap">

    <!-- Brand panel -->
    <div class="pw-auth-brand">
      <div class="pw-auth-brand-logo">PERFECT<span>WELDING</span></div>

      <div class="pw-auth-brand-headline">
        <h2>JOUW<br><span>ACCOUNT</span><br>JOUW<br>VOORDEEL</h2>
        <p>Log in om je bestellingen te beheren, adressen op te slaan en sneller af te rekenen bij PerfectWelding.</p>
      </div>

      <div class="pw-auth-brand-stats">
        <div class="pw-auth-stat-item">
          <div class="pw-auth-stat-num">500+</div>
          <div class="pw-auth-stat-label">Producten</div>
        </div>
        <div class="pw-auth-stat-item">
          <div class="pw-auth-stat-num">14</div>
          <div class="pw-auth-stat-label">Dag retour</div>
        </div>
        <div class="pw-auth-stat-item">
          <div class="pw-auth-stat-num">€50</div>
          <div class="pw-auth-stat-label">Gratis verzend</div>
        </div>
      </div>

      <!-- Decorative SVG -->
      <div class="pw-auth-deco">
        <svg viewBox="0 0 200 400" width="320" xmlns="http://www.w3.org/2000/svg">
          <path d="M50 30 L35 340 Q35 370 60 370 L140 370 Q165 370 165 340 L150 30 Z" fill="none" stroke="white" stroke-width="1.5"/>
          <path d="M70 40 L58 330 Q58 355 80 355 L120 355 Q142 355 142 330 L130 40 Z" fill="rgba(255,77,0,0.15)" stroke="rgba(255,77,0,0.4)" stroke-width="1"/>
          <rect x="91" y="5" width="18" height="30" rx="4" fill="rgba(255,255,255,0.08)" stroke="rgba(255,255,255,0.2)" stroke-width="1"/>
          <ellipse cx="100" cy="375" rx="24" ry="14" fill="rgba(255,184,0,0.6)"/>
          <ellipse cx="100" cy="375" rx="14" ry="8" fill="rgba(255,255,255,0.4)"/>
        </svg>
      </div>
    </div>

    <!-- Form panel -->
    <div class="pw-auth-form-panel">
      <div class="pw-auth-form-inner">

        <?php wc_print_notices(); ?>

        <?php if ( $show_reg ) : ?>

          <!-- ── REGISTER FORM ─────────────────────────────── -->
          <div class="pw-auth-badge">NIEUW ACCOUNT</div>
          <h3>REGISTREREN</h3>
          <p class="pw-auth-sub">Maak gratis een account aan en beheer je bestellingen.</p>

          <form method="post" class="woocommerce-form woocommerce-form-register register">
            <?php do_action('woocommerce_register_form_start'); ?>

            <?php if ( 'no' === get_option('woocommerce_registration_generate_username') ) : ?>
            <div class="pw-auth-field">
              <label for="reg_username">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="7" r="4"/><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/></svg>
                Gebruikersnaam
              </label>
              <input type="text" name="username" id="reg_username" autocomplete="username" placeholder="Jouw gebruikersnaam" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>">
            </div>
            <?php endif; ?>

            <div class="pw-auth-field">
              <label for="reg_email">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                E-mailadres
              </label>
              <input type="email" name="email" id="reg_email" autocomplete="email" placeholder="jouw@email.nl" value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>">
            </div>

            <?php if ( 'no' === get_option('woocommerce_registration_generate_password') ) : ?>
            <div class="pw-auth-field">
              <label for="reg_password">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                Wachtwoord
              </label>
              <input type="password" name="password" id="reg_password" autocomplete="new-password" placeholder="Minimaal 8 tekens">
            </div>
            <?php endif; ?>

            <?php do_action('woocommerce_register_form'); ?>

            <div style="display:none"><?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?><?php do_action('woocommerce_register_form_end'); ?></div>

            <!-- Primary submit -->
            <button type="submit" class="pw-auth-submit" name="register" value="Register">
              ACCOUNT AANMAKEN →
            </button>

            <!-- Divider -->
            <div class="pw-auth-divider">of</div>

            <!-- Secondary: go to login -->
            <a href="<?php echo esc_url($login_url); ?>" class="pw-auth-secondary-btn">
              AL EEN ACCOUNT? INLOGGEN
            </a>

          </form>

        <?php else : ?>

          <!-- ── LOGIN FORM ──────────────────────────────────── -->
          <div class="pw-auth-badge">MIJN ACCOUNT</div>
          <h3>WELKOM TERUG</h3>
          <p class="pw-auth-sub">Log in om je bestellingen en account te beheren.</p>

          <form class="woocommerce-form woocommerce-form-login login" method="post">
            <?php do_action('woocommerce_login_form_start'); ?>

            <div class="pw-auth-field">
              <label for="username">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                E-mailadres of gebruikersnaam
              </label>
              <input type="text" name="username" id="username" autocomplete="username email" placeholder="jouw@email.nl" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>">
            </div>

            <div class="pw-auth-field">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <label for="password" style="margin-bottom:0">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                  Wachtwoord
                </label>
                <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="pw-auth-forgot">Vergeten?</a>
              </div>
              <input type="password" name="password" id="password" autocomplete="current-password" placeholder="Jouw wachtwoord">
            </div>

            <label class="pw-auth-remember" style="margin-bottom:20px;display:flex">
              <input type="checkbox" name="rememberme" value="forever">
              Onthoud mij
            </label>

            <?php do_action('woocommerce_login_form'); ?>

            <div style="display:none">
              <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
              <input type="hidden" name="redirect" value="<?php echo esc_url(wc_get_account_endpoint_url('dashboard')); ?>">
            </div>

            <!-- Primary submit -->
            <button type="submit" class="pw-auth-submit" name="login" value="Login">
              INLOGGEN →
            </button>

            <!-- Divider -->
            <div class="pw-auth-divider">of</div>

            <!-- Secondary: go to register -->
            <a href="<?php echo esc_url($reg_url); ?>" class="pw-auth-secondary-btn">
              NOG GEEN ACCOUNT? REGISTREER GRATIS
            </a>

            <?php do_action('woocommerce_login_form_end'); ?>
          </form>

        <?php endif; ?>

      </div>
    </div>
  </div>

<?php endif; ?>
