<?php
/**
 * Template Name: Elementor Full Width
 * Template Post Type: page
 *
 * Full-width layout — theme header + footer shown, but no inner content wrapper.
 * Ideal for pages where Elementor controls the full inner content.
 */
get_header(); ?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <?php the_content(); ?>
<?php endwhile; endif; ?>

<?php get_footer();
