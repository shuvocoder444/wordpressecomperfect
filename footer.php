<?php
/* ── Footer values ─────────────────────────────────────────── */
$footer_logo      = get_theme_mod('pw_footer_logo_text',   'PERFECT');
$footer_logo_acc  = get_theme_mod('pw_footer_logo_accent', 'WELDING');
$footer_tagline   = get_theme_mod('pw_footer_tagline',     'Specialized in premium TIG parts. Every product tested. Built for the serious welder.');
$footer_instagram = get_theme_mod('pw_footer_instagram',   'https://instagram.com/perfectweldingnl');
$footer_linkedin  = get_theme_mod('pw_footer_linkedin',    'https://www.linkedin.com/in/matthijs-brand-00413822b/');
$footer_kvk       = get_theme_mod('pw_footer_kvk',         '85191183');
$footer_city      = get_theme_mod('pw_footer_city',        'Hengelo, Nederland');
$footer_copyright_default = html_entity_decode('&copy; 2026 PerfectWelding &mdash; All rights reserved', ENT_QUOTES, 'UTF-8');
$footer_copyright = get_theme_mod('pw_footer_copyright', $footer_copyright_default);

$nl_show        = get_theme_mod('pw_nl_show',        true);
$nl_heading     = get_theme_mod('pw_nl_heading',     'STAY UPDATED');
$nl_sub         = get_theme_mod('pw_nl_subtext',     'New products, tips, and exclusive offers. No spam.');
$nl_placeholder = get_theme_mod('pw_nl_placeholder', 'your@email.com');
$nl_btn_text    = get_theme_mod('pw_nl_btn_text',    'Subscribe');

$col1_title = get_theme_mod('pw_footer_col1_title', 'SHOP');
$col1_links = get_theme_mod('pw_footer_col1_links', "All products|/shop\nCups|/product-category/cups\nBack Caps|/product-category/back-caps\nDiffusers|/product-category/diffusers\nTIGWARE|/product-category/tigware");
$col2_title = get_theme_mod('pw_footer_col2_title', 'INFO');
$col2_links = get_theme_mod('pw_footer_col2_links', "About us|/about\nFAQ & Shipping|/faq\nBlog|/blog\nContact|/contact");
$col3_title = get_theme_mod('pw_footer_col3_title', 'LEGAL');
$col3_links = get_theme_mod('pw_footer_col3_links', "Privacy Policy|/privacy-policy\nGeneral Terms|/terms\nCoC: 85191183|#");

$payment_icons = get_theme_mod('pw_footer_payment_icons', "iDEAL\nVISA\nMC\nPayPal\nKlarna");
$pay_items     = array_filter(array_map('trim', explode("\n", $payment_icons)));

/* Helper — parse "Label|/url" lines — uses unique name to avoid redeclaration */
if ( ! function_exists('pw_footer_parse_links') ) {
    function pw_footer_parse_links( $raw ) {
        $out = [];
        foreach ( explode("\n", $raw) as $line ) {
            $line = trim($line);
            if ( !$line ) continue;
            $p = explode('|', $line, 2);
            $out[] = [ 'label' => trim($p[0]), 'url' => isset($p[1]) ? trim($p[1]) : '#' ];
        }
        return $out;
    }
}
?>
</main>
<!-- ═══ End of SPA-swappable content (opened in header.php) ═══ -->

<?php if ( $nl_show ) : ?>
<!-- NEWSLETTER -->
<section class="pw-newsletter">
  <div class="pw-newsletter-text">
    <h3 class="pw-newsletter-heading"><?php echo esc_html($nl_heading); ?></h3>
    <p class="pw-newsletter-sub"><?php echo esc_html($nl_sub); ?></p>
  </div>
  <?php if ( class_exists('WooCommerce') ) : ?>
  <form class="pw-nl-form" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" novalidate>
    <input type="hidden" name="action" value="pw_newsletter">
    <?php wp_nonce_field('pw_newsletter','pw_nonce'); ?>
    <input class="pw-nl-input" type="email" name="email"
           placeholder="<?php echo esc_attr($nl_placeholder); ?>" required aria-label="Email">
    <button class="pw-nl-btn btn-primary" type="submit" data-pw-i18n="Subscribe"><?php echo esc_html($nl_btn_text); ?></button>
  </form>
  <?php endif; ?>
</section>
<?php endif; ?>

<!-- FOOTER -->
<footer class="pw-footer">

  <!-- Brand -->
  <div class="pw-footer-brand">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="pw-footer-logo">
      <?php echo esc_html($footer_logo); ?><span><?php echo esc_html($footer_logo_acc); ?></span>
    </a>
    <p class="pw-footer-tagline"><?php echo esc_html($footer_tagline); ?></p>
    <div class="pw-footer-social">
      <?php if ( $footer_instagram ) : ?>
      <a href="<?php echo esc_url($footer_instagram); ?>" target="_blank" rel="noopener" class="pw-social-btn">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/></svg>
        INSTAGRAM
      </a>
      <?php endif; ?>
      <?php if ( $footer_linkedin ) : ?>
      <a href="<?php echo esc_url($footer_linkedin); ?>" target="_blank" rel="noopener" class="pw-social-btn">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
        LINKEDIN
      </a>
      <?php endif; ?>
    </div>
    <?php if ( $footer_kvk || $footer_city ) : ?>
    <div class="pw-footer-meta">
      <?php if ($footer_city) echo '<span>' . esc_html($footer_city) . '</span>'; ?>
      <?php if ($footer_kvk)  echo '<span>KVK: ' . esc_html($footer_kvk) . '</span>'; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Col 1 -->
  <?php if ( $col1_title ) : ?>
  <div class="pw-footer-col">
    <h4 class="pw-footer-col-title"><?php echo esc_html($col1_title); ?></h4>
    <ul>
      <?php foreach ( pw_footer_parse_links($col1_links) as $lnk ) : ?>
        <li><a href="<?php echo esc_url(home_url($lnk['url'])); ?>"><?php echo esc_html($lnk['label']); ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <!-- Col 2 -->
  <?php if ( $col2_title ) : ?>
  <div class="pw-footer-col">
    <h4 class="pw-footer-col-title"><?php echo esc_html($col2_title); ?></h4>
    <ul>
      <?php foreach ( pw_footer_parse_links($col2_links) as $lnk ) : ?>
        <li><a href="<?php echo esc_url(home_url($lnk['url'])); ?>"><?php echo esc_html($lnk['label']); ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <!-- Col 3 -->
  <?php if ( $col3_title ) : ?>
  <div class="pw-footer-col">
    <h4 class="pw-footer-col-title"><?php echo esc_html($col3_title); ?></h4>
    <ul>
      <?php foreach ( pw_footer_parse_links($col3_links) as $lnk ) : ?>
        <li><a href="<?php echo esc_url(home_url($lnk['url'])); ?>"><?php echo esc_html($lnk['label']); ?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

</footer>

<!-- FOOTER BOTTOM -->
<div class="pw-footer-bottom">
  <span class="pw-footer-copy" data-pw-i18n="<?php echo esc_attr($footer_copyright_default); ?>"><?php echo esc_html($footer_copyright); ?></span>
  <?php if ( !empty($pay_items) ) : ?>
  <div class="pw-payment-icons" aria-label="Payment methods">
    <?php foreach ($pay_items as $icon) echo '<span class="pw-payment-icon">' . esc_html($icon) . '</span>'; ?>
  </div>
  <?php endif; ?>
</div>

<?php wp_footer(); ?>
</body>
</html>
