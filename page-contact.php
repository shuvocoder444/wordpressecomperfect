<?php
/**
 * Template Name: Contact Page
 * Template Post Type: page
 *
 * Contact page — matches reference screenshot split layout.
 * All details editable via Customizer → PerfectWelding → Contact Page.
 */
get_header(); ?>

<?php
$page_title  = get_theme_mod('pw_contact_page_title',  'CONTACT');
$page_intro  = get_theme_mod('pw_contact_page_intro',  'Do you have a question about a product or order, or do you just want to chat about TIG welding? Send us a message.');

$email       = get_theme_mod('pw_contact_email',       'matthijsbrand@perfectwelding.nl');
$location    = get_theme_mod('pw_contact_location',    'Hengelo, Netherlands');
$phone       = get_theme_mod('pw_contact_phone',       '+31 6 181 77 682');
$coc         = get_theme_mod('pw_contact_coc',         '85191183');
$response    = get_theme_mod('pw_contact_response',    'Within 24 hours on business days');
$instagram   = get_theme_mod('pw_contact_instagram',   'https://instagram.com/perfectweldingnl');
$linkedin    = get_theme_mod('pw_contact_linkedin',    'https://www.linkedin.com/in/matthijs-brand-00413822b/');

$form_title    = get_theme_mod('pw_contact_form_title',    'SEND A MESSAGE');
$form_subject  = get_theme_mod('pw_contact_form_subject',  'Question about product');
$form_btn      = get_theme_mod('pw_contact_form_btn',      'SEND →');

// Use Contact Form 7 shortcode if available, else custom HTML form
$cf7_shortcode = get_theme_mod('pw_contact_cf7_shortcode', '');
?>

<div class="pw-contact-wrap">
  <!-- ══ LEFT: Info ════════════════════════════════════════════ -->
  <div class="pw-contact-info">
    <h1 class="pw-contact-title"><?php echo esc_html($page_title); ?></h1>
    <?php if ($page_intro) : ?>
      <p class="pw-contact-intro"><?php echo esc_html($page_intro); ?></p>
    <?php endif; ?>

    <ul class="pw-contact-details">
      <?php if ($email) : ?>
      <li class="pw-contact-detail">
        <span class="pw-contact-icon pw-icon-accent">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </span>
        <div>
          <span class="pw-contact-label">E-MAIL</span>
          <a href="mailto:<?php echo esc_attr($email); ?>" class="pw-contact-value"><?php echo esc_html($email); ?></a>
        </div>
      </li>
      <?php endif; ?>

      <?php if ($location) : ?>
      <li class="pw-contact-detail">
        <span class="pw-contact-icon pw-icon-accent">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </span>
        <div>
          <span class="pw-contact-label">CHAMBER OF COMMERCE</span>
          <span class="pw-contact-value"><?php echo esc_html($location); ?></span>
        </div>
      </li>
      <?php endif; ?>

      <?php if ($phone) : ?>
      <li class="pw-contact-detail">
        <span class="pw-contact-icon pw-icon-accent">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.79 19.79 0 012.12 4.18 2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
        </span>
        <div>
          <span class="pw-contact-label">PHONE / WHATSAPP</span>
          <a href="tel:<?php echo esc_attr(preg_replace('/\s+/','',$phone)); ?>" class="pw-contact-value"><?php echo esc_html($phone); ?></a>
        </div>
      </li>
      <?php endif; ?>

      <?php if ($coc) : ?>
      <li class="pw-contact-detail">
        <span class="pw-contact-icon pw-icon-accent">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
        </span>
        <div>
          <span class="pw-contact-label">CHAMBER OF COMMERCE</span>
          <span class="pw-contact-value"><?php echo esc_html($coc); ?></span>
        </div>
      </li>
      <?php endif; ?>

      <?php if ($response) : ?>
      <li class="pw-contact-detail">
        <span class="pw-contact-icon pw-icon-warning">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </span>
        <div>
          <span class="pw-contact-label">RESPONSE TIME</span>
          <span class="pw-contact-value"><?php echo esc_html($response); ?></span>
        </div>
      </li>
      <?php endif; ?>
    </ul>

    <div class="pw-contact-social">
      <?php if ($instagram) : ?>
        <a href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener" class="pw-contact-social-btn">INSTAGRAM</a>
      <?php endif; ?>
      <?php if ($linkedin) : ?>
        <a href="<?php echo esc_url($linkedin); ?>" target="_blank" rel="noopener" class="pw-contact-social-btn">LINKEDIN</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- ══ RIGHT: Form ═══════════════════════════════════════════ -->
  <div class="pw-contact-form-wrap">
    <h2 class="pw-contact-form-title"><?php echo esc_html($form_title); ?></h2>

    <?php if (!empty($cf7_shortcode)) : ?>
      <?php echo do_shortcode(wp_kses_post($cf7_shortcode)); ?>
    <?php else : ?>
      <!-- Default HTML form (replace with CF7 shortcode in Customizer) -->
      <form class="pw-contact-form" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
        <?php wp_nonce_field('pw_contact_form', 'pw_contact_nonce'); ?>
        <input type="hidden" name="action" value="pw_contact_submit">

        <div class="pw-cf-field">
          <label for="pw_cf_name">NAME</label>
          <input type="text" id="pw_cf_name" name="cf_name" placeholder="NAME" required>
        </div>
        <div class="pw-cf-field">
          <label for="pw_cf_email">E-MAIL</label>
          <input type="email" id="pw_cf_email" name="cf_email" placeholder="E-MAIL" required>
        </div>
        <div class="pw-cf-field">
          <label for="pw_cf_subject">SUBJECT</label>
          <input type="text" id="pw_cf_subject" name="cf_subject" value="<?php echo esc_attr($form_subject); ?>">
        </div>
        <div class="pw-cf-field">
          <label for="pw_cf_message">MESSAGE</label>
          <textarea id="pw_cf_message" name="cf_message" rows="6" placeholder="MESSAGE" required></textarea>
        </div>
        <button type="submit" class="btn-primary"><?php echo esc_html($form_btn); ?></button>
        <div class="pw-cf-status" id="pw-cf-status" aria-live="polite"></div>
      </form>

      <script>
      (function(){
        var form = document.querySelector('.pw-contact-form');
        if (!form) return;
        form.addEventListener('submit', function(e){
          e.preventDefault();
          var status = document.getElementById('pw-cf-status');
          var data   = new FormData(form);
          status.textContent = 'Sending…';
          fetch(form.action, { method: 'POST', body: data })
            .then(function(r){ return r.json(); })
            .then(function(res){
              if (res.success) {
                status.textContent = res.data || 'Message sent!';
                form.reset();
              } else {
                status.textContent = res.data || 'Something went wrong. Please try again.';
              }
            })
            .catch(function(){ status.textContent = 'Network error. Please try again.'; });
        });
      })();
      </script>
    <?php endif; ?>
  </div>
</div>

<?php get_footer(); ?>
