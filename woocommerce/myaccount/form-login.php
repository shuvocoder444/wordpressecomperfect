<?php
/**
 * PerfectWelding — Custom Login / Register form
 * Overrides WooCommerce default form-login.php
 */
defined('ABSPATH') || exit;

$login_url = get_permalink(get_option('woocommerce_myaccount_page_id'));
$reg_url   = add_query_arg('action', 'register', $login_url);
$show_reg  = isset($_GET['action']) && $_GET['action'] === 'register';
?>

<div class="pw-auth-page pw-auth-wrap">

  <!-- ══ LEFT BRAND PANEL ══════════════════════════════════════ -->
  <div class="pw-auth-brand">
    <div class="pw-auth-brand-logo">PERFECT<span>WELDING</span></div>

    <div class="pw-auth-brand-headline">
      <h2>DOOR<br>LASSERS<br>VOOR<br><span>LASSERS</span></h2>
      <p>Beheer je bestellingen, sla adressen op en reken sneller af bij PerfectWelding — het platform voor TIG-specialisten.</p>
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

    <!-- Decorative TIG torch SVG -->
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

  <!-- ══ RIGHT FORM PANEL ═══════════════════════════════════════ -->
  <div class="pw-auth-form-panel">
    <div class="pw-auth-form-inner">

      <?php wc_print_notices(); ?>

      <?php if ( $show_reg ) : ?>
      <!-- ─── REGISTER ──────────────────────────────────────── -->

        <div class="pw-auth-badge">✦ NIEUW ACCOUNT</div>
        <h3>REGISTREREN</h3>
        <p class="pw-auth-sub">Maak gratis een account aan en beheer al je bestellingen op één plek.</p>

        <form method="post" class="pw-register-form">
          <?php do_action('woocommerce_register_form_start'); ?>

          <?php if ( 'no' === get_option('woocommerce_registration_generate_username') ) : ?>
          <div class="pw-auth-field">
            <label for="reg_username">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="7" r="4"/><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/></svg>
              Gebruikersnaam
            </label>
            <input
              type="text"
              name="username"
              id="reg_username"
              autocomplete="username"
              placeholder="Kies een gebruikersnaam"
              value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>"
            >
          </div>
          <?php endif; ?>

          <div class="pw-auth-field">
            <label for="reg_email">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              E-mailadres
            </label>
            <input
              type="email"
              name="email"
              id="reg_email"
              autocomplete="email"
              placeholder="jouw@email.nl"
              value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>"
            >
          </div>

          <?php if ( 'no' === get_option('woocommerce_registration_generate_password') ) : ?>
          <div class="pw-auth-field">
            <label for="reg_password">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
              Wachtwoord
            </label>
            <input
              type="password"
              name="password"
              id="reg_password"
              autocomplete="new-password"
              placeholder="Minimaal 8 tekens"
            >
          </div>
          <?php endif; ?>

          <?php do_action('woocommerce_register_form'); ?>

          <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
          <?php do_action('woocommerce_register_form_end'); ?>

          <button type="submit" class="pw-auth-submit" name="register" value="Register">
            ACCOUNT AANMAKEN →
          </button>

          <div class="pw-auth-divider">of</div>

          <a href="<?php echo esc_url($login_url); ?>" class="pw-auth-secondary-btn">
            AL EEN ACCOUNT? INLOGGEN
          </a>

        </form>

      <?php else : ?>
      <!-- ─── LOGIN ─────────────────────────────────────────── -->

        <div class="pw-auth-badge">✦ MIJN ACCOUNT</div>
        <h3>WELKOM TERUG</h3>
        <p class="pw-auth-sub">Log in om je bestellingen en accountgegevens te bekijken.</p>

        <form name="loginform" method="post" class="pw-login-form">
          <?php do_action('woocommerce_login_form_start'); ?>

          <div class="pw-auth-field">
            <label for="username">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              E-mailadres of gebruikersnaam
            </label>
            <input
              type="text"
              name="username"
              id="username"
              autocomplete="username email"
              placeholder="jouw@email.nl"
              value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>"
            >
          </div>

          <div class="pw-auth-field">
            <div class="pw-auth-field-header">
              <label for="password">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                Wachtwoord
              </label>
              <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="pw-auth-forgot">Vergeten?</a>
            </div>
            <input
              type="password"
              name="password"
              id="password"
              autocomplete="current-password"
              placeholder="Jouw wachtwoord"
            >
          </div>

          <label class="pw-auth-remember">
            <input type="checkbox" name="rememberme" value="forever">
            Onthoud mij op dit apparaat
          </label>

          <?php do_action('woocommerce_login_form'); ?>

          <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
          <input type="hidden" name="redirect" value="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">

          <button type="submit" class="pw-auth-submit" name="login" value="Login">
            INLOGGEN →
          </button>

          <div class="pw-auth-divider">of</div>

          <a href="<?php echo esc_url($reg_url); ?>" class="pw-auth-secondary-btn">
            NOG GEEN ACCOUNT? GRATIS REGISTREREN
          </a>

          <?php do_action('woocommerce_login_form_end'); ?>
        </form>

      <?php endif; ?>

    </div><!-- /.pw-auth-form-inner -->
  </div><!-- /.pw-auth-form-panel -->

</div><!-- /.pw-auth-wrap -->
