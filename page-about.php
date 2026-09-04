<?php
/**
 * Template Name: About Us Page
 * Template Post Type: page
 *
 * "About Us" page — mirrors the "FOR WELDERS. BY WELDERS." design from the reference image.
 * All text is editable via WordPress Customizer → PerfectWelding → About Us Page.
 */
get_header(); ?>

<?php
// ── Customizer values with sensible defaults ────────────────────
$eyebrow     = get_theme_mod('pw_about_eyebrow',     'OUR STORY');
$headline1   = get_theme_mod('pw_about_headline1',   'FOR');
$headline2   = get_theme_mod('pw_about_headline2',   'WELDERS.');
$headline3   = get_theme_mod('pw_about_headline3',   'BY');
$headline4   = get_theme_mod('pw_about_headline4',   'WELDERS.');
$since_label = get_theme_mod('pw_about_since_label', 'TIG');
$since_year  = get_theme_mod('pw_about_since_year',  'SINCE 2019');

$p1_num   = get_theme_mod('pw_about_p1_num',   '01');
$p1_title = get_theme_mod('pw_about_p1_title', 'TESTED');
$p1_desc  = get_theme_mod('pw_about_p1_desc',  'Every product we sell is personally tested by us. No compromises, no unknown quality.');

$p2_num   = get_theme_mod('pw_about_p2_num',   '02');
$p2_title = get_theme_mod('pw_about_p2_title', 'IMPROVED');
$p2_desc  = get_theme_mod('pw_about_p2_desc',  "We don't just sell parts. We actively search for improved versions of standard TIG parts.");

$p3_num   = get_theme_mod('pw_about_p3_num',   '03');
$p3_title = get_theme_mod('pw_about_p3_title', 'HONESTLY');
$p3_desc  = get_theme_mod('pw_about_p3_desc',  "If something isn't right, we don't sell it. Period. Our reputation is our greatest asset.");

$story_title   = get_theme_mod('pw_about_story_title',   'THE STORY');
$story_content = get_theme_mod('pw_about_story_content', 'PerfectWelding was born out of frustration. Standard TIG parts simply didn\'t perform well enough — poor gas coverage, short lifespan, disappointing results on stainless steel and titanium. We started by testing alternatives. Pyrex cups, improved diffusers, Teflon back caps. After months of testing, we had a set of products that really worked. We sell those products now.');

$tw_title   = get_theme_mod('pw_about_tw_title',   'TIGWARE');
$tw_content = get_theme_mod('pw_about_tw_content', 'TIGWARE is a premium brand whose parts we officially sell. It is not our own brand, but we fully stand behind the quality — which is why we carry it. Every TIGWARE product is designed for maximum welding protection. From Teflon back caps to Pyrex cups — every TIGWARE product has its own application and benefit. Unique, unbreakable, and high quality.');
$tw_btn_text = get_theme_mod('pw_about_tw_btn_text', 'SHOP TIGWARE →');
$tw_btn_url  = get_theme_mod('pw_about_tw_btn_url',  '');
if ( empty($tw_btn_url) ) {
    $term_link = get_term_link('tigware', 'product_cat');
    $tw_btn_url = ! is_wp_error($term_link) ? $term_link : get_permalink(wc_get_page_id('shop'));
}
?>

<!-- ══ ABOUT HERO ══════════════════════════════════════════════ -->
<section class="pw-about-hero">
  <div class="grid-lines"></div>
  <div class="pw-about-hero-left">
    <span class="pw-about-eyebrow"><?php echo esc_html($eyebrow); ?></span>
    <h1 class="pw-about-headline">
      <?php echo esc_html($headline1); ?><br>
      <span class="pw-about-headline-accent"><?php echo esc_html($headline2); ?></span><br>
      <?php echo esc_html($headline3); ?><br>
      <span class="pw-about-headline-accent"><?php echo esc_html($headline4); ?></span>
    </h1>
  </div>
  <div class="pw-about-hero-right">
    <div class="pw-about-tig-badge">
      <span class="pw-about-tig-text"><?php echo esc_html($since_label); ?></span>
      <span class="pw-about-tig-since"><?php echo esc_html($since_year); ?></span>
    </div>
  </div>
</section>

<!-- ══ THREE PILLARS ════════════════════════════════════════════ -->
<section class="pw-about-pillars">
  <div class="pw-about-pillar">
    <span class="pw-pillar-num"><?php echo esc_html($p1_num); ?></span>
    <h3 class="pw-pillar-title"><?php echo esc_html($p1_title); ?></h3>
    <p class="pw-pillar-desc"><?php echo esc_html($p1_desc); ?></p>
  </div>
  <div class="pw-about-pillar">
    <span class="pw-pillar-num"><?php echo esc_html($p2_num); ?></span>
    <h3 class="pw-pillar-title"><?php echo esc_html($p2_title); ?></h3>
    <p class="pw-pillar-desc"><?php echo esc_html($p2_desc); ?></p>
  </div>
  <div class="pw-about-pillar">
    <span class="pw-pillar-num"><?php echo esc_html($p3_num); ?></span>
    <h3 class="pw-pillar-title"><?php echo esc_html($p3_title); ?></h3>
    <p class="pw-pillar-desc"><?php echo esc_html($p3_desc); ?></p>
  </div>
</section>

<!-- ══ STORY + TIGWARE ══════════════════════════════════════════ -->
<section class="pw-about-stories">
  <div class="pw-about-story-col">
    <h2 class="pw-about-section-title"><?php echo esc_html($story_title); ?></h2>
    <p class="pw-about-story-text"><?php echo nl2br(esc_html($story_content)); ?></p>
  </div>
  <div class="pw-about-story-col">
    <h2 class="pw-about-section-title"><?php echo esc_html($tw_title); ?></h2>
    <p class="pw-about-story-text"><?php echo nl2br(esc_html($tw_content)); ?></p>
    <a href="<?php echo esc_url($tw_btn_url); ?>" class="btn-primary pw-about-cta"><?php echo esc_html($tw_btn_text); ?></a>
  </div>
</section>

<?php get_footer(); ?>
