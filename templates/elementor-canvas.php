<?php
/**
 * Template Name: Elementor Canvas
 * Template Post Type: page
 *
 * Blank canvas — no header, no footer, no wrapper.
 * Use this for landing pages or popup pages built fully in Elementor.
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <?php the_content(); ?>
<?php endwhile; endif; ?>

<?php wp_footer(); ?>
</body>
</html>
