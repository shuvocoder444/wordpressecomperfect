<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="google-site-verification" content="Bf9siHC4q5s6m8OKCYrTCRKK03dBNF9JR3bLJP0oibM" />
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
$logo_text    = get_theme_mod('pw_nav_logo_text',    'PERFECT');
$logo_accent  = get_theme_mod('pw_nav_logo_accent',  'WELDING');
$show_lang    = get_theme_mod('pw_nav_show_lang',    true);
$show_cart    = get_theme_mod('pw_nav_show_cart',    true);
$show_account = get_theme_mod('pw_nav_show_account', true);
$show_marquee = get_theme_mod('pw_nav_show_marquee', true);
$nav_bg       = get_theme_mod('pw_nav_bg_color',     'rgba(8,8,8,0.93)');
$nav_accent   = get_theme_mod('pw_nav_accent_color', '#FF4D00');

/* ── BUILD NAV ITEMS ────────────────────────────────────────────
   1st priority: Appearance → Menus  (primary location)
   2nd priority: Customizer textarea fallback
   ────────────────────────────────────────────────────────────── */
$nav_items = [];

// Check if WP menu is assigned to 'primary' location
$locations = get_nav_menu_locations();
if ( ! empty( $locations['primary'] ) ) {
    $menu_obj   = wp_get_nav_menu_object( $locations['primary'] );
    $menu_items = $menu_obj ? wp_get_nav_menu_items( $menu_obj->term_id ) : [];
    if ( ! empty( $menu_items ) ) {
        foreach ( $menu_items as $mi ) {
            if ( (int) $mi->menu_item_parent !== 0 ) continue; // top-level only
            $nav_items[] = [
                'label' => $mi->title,
                'url'   => $mi->url,   // already absolute
                'abs'   => true,
            ];
        }
    }
}

// Fallback: Customizer textarea (Label|/path  per line)
if ( empty( $nav_items ) ) {
    $raw = get_theme_mod( 'pw_nav_menu_items',
        "Home|/\nShop|/shop\nOver ons|/about\nBlog|/blog\nFaq|/faq\nContact|/contact" );
    foreach ( explode( "\n", $raw ) as $line ) {
        $line = trim( $line );
        if ( ! $line ) continue;
        $parts = explode( '|', $line, 2 );
        if ( count( $parts ) === 2 ) {
            $nav_items[] = [ 'label' => trim( $parts[0] ), 'url' => trim( $parts[1] ), 'abs' => false ];
        }
    }
}

// Current path for active detection
$cur = rtrim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' ) ?: '/';

function pw_nav_href_h( $item ) {
    return ! empty( $item['abs'] ) ? esc_url( $item['url'] ) : esc_url( home_url( $item['url'] ) );
}
function pw_nav_is_active( $item, $cur ) {
    $path = rtrim( parse_url( $item['url'], PHP_URL_PATH ), '/' ) ?: '/';
    return ( $cur === $path );
}

// Marquee
$marquee_raw   = get_theme_mod( 'pw_marquee_items',
    "✦ FREE SHIPPING ABOVE €100\n✦ TIGWARE PREMIUM PARTS\n✦ FAST DELIVERY\n✦ FOR WELDERS BY WELDERS\n✦ 14-DAY RETURN POLICY\n✦ 100% SELF-TESTED" );
$marquee_items = array_filter( array_map( 'trim', explode( "\n", $marquee_raw ) ) );

$current_lang  = isset( $_COOKIE['pw_language'] ) ? sanitize_text_field( $_COOKIE['pw_language'] ) : 'nl';
?>

<!-- NAVBAR -->
<nav id="pw-nav" style="--nav-bg:<?php echo esc_attr($nav_bg); ?>;--nav-accent:<?php echo esc_attr($nav_accent); ?>">

  <a href="<?php echo esc_url(home_url('/')); ?>" class="nav-logo">
    <?php echo esc_html($logo_text); ?><span><?php echo esc_html($logo_accent); ?></span>
  </a>

  <div class="nav-links" role="navigation" aria-label="Primary navigation">
    <?php foreach ( $nav_items as $item ) :
      $active = pw_nav_is_active( $item, $cur );
    ?>
      <a href="<?php echo pw_nav_href_h($item); ?>"
         data-pw-i18n="<?php echo esc_attr($item['label']); ?>"
         <?php echo $active ? 'class="active" aria-current="page"' : ''; ?>>
        <?php echo esc_html( $item['label'] ); ?>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="nav-right">
    <?php if ( $show_lang ) : ?>
    <div class="pw-lang-switcher" id="pw-lang-switcher" role="group" aria-label="Language">
      <button class="pw-lang-btn <?php echo $current_lang==='en'?'active':''; ?>" data-lang="en">EN</button>
      <button class="pw-lang-btn <?php echo $current_lang==='nl'?'active':''; ?>" data-lang="nl">NL</button>
    </div>
    <?php endif; ?>

    <?php if ( $show_cart && class_exists('WooCommerce') ) : ?>
    <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="cart-btn" id="pw-cart-btn" aria-label="Cart">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      <span class="cart-badge" id="pw-cart-count"><?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?></span>
    </a>
    <?php endif; ?>

    <?php if ( $show_account && class_exists('WooCommerce') ) : ?>
    <a href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>" class="account-btn" aria-label="My account">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    </a>
    <?php endif; ?>

    <button class="nav-mobile-toggle" id="nav-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-nav">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- MOBILE DRAWER -->
<div class="mobile-nav" id="mobile-nav" aria-hidden="true" role="dialog" aria-label="Menu">
  <div class="mobile-nav-header">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="mobile-nav-logo">
      <?php echo esc_html($logo_text); ?><span><?php echo esc_html($logo_accent); ?></span>
    </a>
    <button class="mobile-nav-close" id="nav-close" aria-label="Close menu">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </button>
  </div>

  <nav class="mobile-nav-links">
    <?php foreach ( $nav_items as $item ) :
      $active = pw_nav_is_active( $item, $cur );
    ?>
      <a href="<?php echo pw_nav_href_h($item); ?>"
         data-pw-i18n="<?php echo esc_attr($item['label']); ?>"
         <?php echo $active ? 'class="active"' : ''; ?>>
        <?php echo esc_html( $item['label'] ); ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <?php if ( $show_lang ) : ?>
  <div class="mobile-nav-lang">
    <span class="mobile-nav-lang-label">LANGUAGE</span>
    <div class="pw-lang-switcher" role="group">
      <button class="pw-lang-btn <?php echo $current_lang==='en'?'active':''; ?>" data-lang="en">EN</button>
      <button class="pw-lang-btn <?php echo $current_lang==='nl'?'active':''; ?>" data-lang="nl">NL</button>
    </div>
  </div>
  <?php endif; ?>

  <?php if ( $show_cart && class_exists('WooCommerce') ) : ?>
  <div class="mobile-nav-footer">
    <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="mobile-nav-cart">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
        <line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
      </svg>
      Winkelwagen
      <?php $cnt = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
        if ( $cnt > 0 ) echo '<span class="mobile-cart-badge">' . $cnt . '</span>'; ?>
    </a>
  </div>
  <?php endif; ?>
</div>
<div class="mobile-nav-overlay" id="nav-overlay" aria-hidden="true"></div>

<?php if ( $show_marquee && !empty($marquee_items) ) : ?>
<div class="marquee-strip" aria-hidden="true">
  <div class="marquee-inner" id="marquee-inner">
    <?php $all = array_merge($marquee_items,$marquee_items,$marquee_items);
    foreach ( $all as $i ) echo '<span class="marquee-item">' . esc_html($i) . '</span>'; ?>
  </div>
</div>
<?php endif; ?>

<!-- ═══ Everything from here until the matching </main> in footer.php
     is what AJAX navigation (assets/js/spa-nav.js) swaps out. Nav,
     mobile menu, marquee, and side-cart above this line stay on the
     page across navigations and never get re-created. ═══ -->
<main id="pw-spa-content">

<?php if ( function_exists('woocommerce_output_all_notices') ) : ?>
<div class="pw-notices-wrap"><?php woocommerce_output_all_notices(); ?></div>
<?php endif; ?>
