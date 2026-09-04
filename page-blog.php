<?php
/**
 * Template Name: Blog Archive Page
 * Template Post Type: page
 *
 * Custom Blog page — matches the reference screenshot grid design.
 * Customizable via Customizer → PerfectWelding → Blog Page.
 */
get_header(); ?>

<?php
$page_title    = get_theme_mod('pw_blog_page_title',    'BLOG');
$page_subtitle = get_theme_mod('pw_blog_page_subtitle', 'Insights, guides, and product deep-dives from the welding bench.');
$posts_per_page = (int) get_theme_mod('pw_blog_posts_per_page', 9);

$query = new WP_Query([
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => $posts_per_page,
    'paged'          => max(1, get_query_var('paged')),
]);
?>

<!-- ══ BLOG HEADER ══════════════════════════════════════════════ -->
<section class="pw-blog-header">
  <div class="grid-lines"></div>
  <div class="pw-blog-header-inner">
    <span class="pw-blog-count"><?php echo $query->found_posts; ?> articles</span>
    <h1 class="pw-blog-title"><?php echo esc_html($page_title); ?></h1>
    <?php if ($page_subtitle) : ?>
      <p class="pw-blog-subtitle"><?php echo esc_html($page_subtitle); ?></p>
    <?php endif; ?>
  </div>
</section>

<!-- ══ BLOG GRID ════════════════════════════════════════════════ -->
<section class="pw-blog-grid-section">
  <?php if ($query->have_posts()) : ?>
    <div class="pw-blog-grid">
      <?php while ($query->have_posts()) : $query->the_post();
        $cats = get_the_category();
        $cat_name = $cats ? strtoupper($cats[0]->name) : '';
        $cat_color = get_theme_mod('pw_blog_cat_color_' . sanitize_key($cat_name), '');
      ?>
        <article class="pw-blog-card">
          <a href="<?php the_permalink(); ?>" class="pw-blog-card-link">
            <div class="pw-blog-card-thumb">
              <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('large', ['class' => 'pw-blog-thumb-img']); ?>
              <?php else : ?>
                <div class="pw-blog-thumb-placeholder">
                  <span class="pw-blog-thumb-text"><?php echo esc_html(substr(get_the_title(), 0, 2)); ?></span>
                </div>
              <?php endif; ?>
              <?php if ($cat_name) : ?>
                <span class="pw-blog-cat-tag" <?php if ($cat_color) echo 'style="background:' . esc_attr($cat_color) . '"'; ?>>
                  <?php echo esc_html($cat_name); ?>
                </span>
              <?php endif; ?>
            </div>
            <div class="pw-blog-card-body">
              <time class="pw-blog-date"><?php echo get_the_date('j M Y'); ?></time>
              <h2 class="pw-blog-card-title"><?php the_title(); ?></h2>
              <p class="pw-blog-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20, ''); ?></p>
              <span class="pw-blog-readmore">Read more →</span>
            </div>
          </a>
        </article>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>

    <?php
    // Pagination
    $big = 999999999;
    $pagination = paginate_links([
        'base'    => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format'  => '?paged=%#%',
        'current' => max(1, get_query_var('paged')),
        'total'   => $query->max_num_pages,
        'type'    => 'array',
        'prev_text' => '← PREV',
        'next_text' => 'NEXT →',
    ]);
    if ($pagination) : ?>
      <nav class="pw-blog-pagination">
        <?php foreach ($pagination as $page) echo $page; ?>
      </nav>
    <?php endif; ?>

  <?php else : ?>
    <p class="pw-blog-empty">No articles published yet.</p>
  <?php endif; ?>
</section>

<?php get_footer(); ?>
