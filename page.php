<?php
/**
 * Template: Standard Page
 * Supports both classic WordPress editor and Elementor builder.
 */
get_header(); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <?php
    $is_woo_page = ( function_exists('is_woocommerce') && ( is_cart() || is_checkout() || is_account_page() ) );

    if ( $is_woo_page ) :
        // WooCommerce cart, checkout, my-account manage their own headers and layouts
        the_content();
    elseif ( ! function_exists('pw_is_built_with_elementor') || ! pw_is_built_with_elementor() ) : ?>
        <div class="pw-page-header">
            <div class="pw-page-eyebrow">PerfectWelding</div>
            <h1 class="pw-page-title"><?php the_title(); ?></h1>
        </div>
        <div class="pw-page-content">
            <?php the_content(); ?>
        </div>
    <?php else : ?>
        <?php
        // Elementor-built page: just output the_content() with no extra wrappers.
        // Elementor hooks into the_content() and renders its own sections & columns.
        the_content();
        ?>
    <?php endif; ?>

<?php endwhile; endif; ?>

<?php get_footer();
