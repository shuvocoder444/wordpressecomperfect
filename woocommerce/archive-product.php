<?php
/**
 * WooCommerce Archive Product (Shop Page)
 * Replicates the original shop page design exactly
 */
defined('ABSPATH') || exit;
get_header();

$current_cat  = is_product_category() ? get_queried_object() : null;
global $wp_query;
$total = wc_get_loop_prop('total');
if (!$total && $wp_query instanceof WP_Query) {
  $total = (int) $wp_query->found_posts;
}
$total = (int) $total;
?>

<div class="shop-page">

  <!-- SHOP HEADER -->
  <div class="shop-header">
    <div>
      <div class="shop-count"><?php echo esc_html($total); ?> producten</div>
      <h1 class="shop-title"><?php echo $current_cat ? esc_html($current_cat->name) : 'SHOP'; ?></h1>
    </div>
    <div class="shop-header-right">
      <!-- Brand filter pills -->
      <div class="shop-filters">
        <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>"
           class="filter-btn <?php echo (!is_product_category() && !isset($_GET['brand'])) ? 'active' : ''; ?>">Alles</a>
        <?php
        $brands = ['perfectwelding' => 'PerfectWelding', 'tigware' => 'TIGWARE'];
        foreach ($brands as $slug => $label) :
            $active = (isset($_GET['brand']) && $_GET['brand'] === $slug);
        ?>
        <a href="<?php echo esc_url(add_query_arg('brand', $slug, get_permalink(wc_get_page_id('shop')))); ?>"
           class="filter-btn <?php echo $active ? 'active' : ''; ?>"><?php echo esc_html($label); ?></a>
        <?php endforeach; ?>
        <?php
        $sale_url = add_query_arg('orderby', 'price', get_permalink(wc_get_page_id('shop')));
        ?>
        <a href="<?php echo esc_url(add_query_arg('on_sale', '1', get_permalink(wc_get_page_id('shop')))); ?>"
           class="filter-btn <?php echo isset($_GET['on_sale']) ? 'active' : ''; ?>">Sale</a>
      </div>
      <!-- Sort -->
      <div class="shop-sort">
        <?php woocommerce_catalog_ordering(); ?>
      </div>
    </div>
  </div>

  <div class="shop-body">

    <!-- SIDEBAR -->
    <div class="shop-sidebar">
      <div class="sidebar-title">Categorie</div>
      <?php
      $cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0]);
      $all_active = !is_product_category();
      ?>
      <div class="sidebar-item <?php echo $all_active ? 'active' : ''; ?>"
           onclick="window.location='<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>'">
        <span>Alle producten</span>
        <span class="sidebar-item-count"><?php echo wp_count_posts('product')->publish; ?></span>
      </div>
      <?php if (!is_wp_error($cats)) foreach ($cats as $cat) : ?>
      <div class="sidebar-item <?php echo ($current_cat && $current_cat->slug === $cat->slug) ? 'active' : ''; ?>"
           onclick="window.location='<?php echo esc_url(get_term_link($cat)); ?>'">
        <span><?php echo esc_html($cat->name); ?></span>
        <span class="sidebar-item-count"><?php echo $cat->count; ?></span>
      </div>
      <?php endforeach; ?>

      <div style="margin-top:32px">
        <div class="sidebar-title">Prijs</div>
        <?php
        $price_ranges = [
            ['0',  '10',  'Onder €10'],
            ['10', '25',  '€10 – €25'],
            ['25', '50',  '€25 – €50'],
            ['50', '999', 'Boven €50'],
        ];
        foreach ($price_ranges as [$min, $max, $label]) :
            $active = (isset($_GET['min_price']) && $_GET['min_price'] == $min);
        ?>
        <div class="sidebar-item <?php echo $active ? 'active' : ''; ?>"
             onclick="window.location='<?php echo esc_url(add_query_arg(['min_price' => $min, 'max_price' => $max])); ?>'">
          <span><?php echo esc_html($label); ?></span>
        </div>
        <?php endforeach; ?>
      </div>

      <div style="margin-top:32px">
        <div class="sidebar-title">Merk</div>
        <?php foreach ($brands as $slug => $label) :
            $active = (isset($_GET['brand']) && $_GET['brand'] === $slug);
        ?>
        <div class="sidebar-item <?php echo $active ? 'active' : ''; ?>"
             onclick="window.location='<?php echo esc_url(add_query_arg('brand', $slug, get_permalink(wc_get_page_id('shop')))); ?>'">
          <span><?php echo esc_html($label); ?></span>
        </div>
        <?php endforeach; ?>
      </div>

      <?php if (isset($_GET['min_price']) || isset($_GET['brand']) || isset($_GET['on_sale'])) : ?>
      <div style="margin-top:24px">
        <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn-ghost" style="font-size:11px;padding:8px 14px;display:block;text-align:center">
          ✕ Filters wissen
        </a>
      </div>
      <?php endif; ?>
    </div>

    <!-- PRODUCTS GRID -->
    <div class="shop-products">
      <?php if (woocommerce_product_loop()) : ?>
      <div class="product-grid cols-4">
        <?php
        while (have_posts()) : the_post();
          global $product;
          $image    = $product->get_image('pw-product-thumb', [
            'class'    => 'product-img-el',
            'loading'  => 'lazy',
            'decoding' => 'async',
            'sizes'    => '(max-width: 768px) 100vw, 25vw',
          ]);
          $price    = $product->get_price();
          $regular  = $product->get_regular_price();
          $sale     = $product->is_on_sale();
          $in_stock = $product->is_in_stock();
          $brand    = get_post_meta($product->get_id(), '_brand', true) ?: 'PerfectWelding';
          $is_new   = (strtotime($product->get_date_created()) > strtotime('-30 days'));
        ?>
        <div class="product-card" onclick="if(!event.target.closest('a, button, input, select')) window.location='<?php echo esc_url(get_permalink()); ?>'">
          <div class="product-img">
            <a class="product-img-link" href="<?php echo esc_url(get_permalink()); ?>" style="display:block;width:100%;height:100%;">
              <div class="grid-lines"></div>
              <?php if ($sale) : ?><span class="product-badge sale">SALE</span><?php endif; ?>
              <?php if ($is_new && !$sale) : ?><span class="product-badge">NEW</span><?php endif; ?>
              <?php if (!$in_stock) : ?><span class="product-badge out">UITVERKOCHT</span><?php endif; ?>
              <?php if ($image) echo $image; else echo '<div class="product-img-placeholder">' . pw_tig_svg() . '</div>'; ?>
            </a>
          </div>
          <div class="product-info">
            <div class="product-brand"><?php echo esc_html($brand); ?></div>
            <div class="product-name"><a class="product-card-link" href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a></div>
            <div class="product-footer">
              <div>
                <?php if ($sale && $regular) : ?>
                  <span class="product-price-old">&euro;<?php echo number_format((float)$regular, 2, ',', '.'); ?></span>
                  <span class="product-price">&euro;<?php echo number_format((float)$price, 2, ',', '.'); ?></span>
                <?php elseif ($product->is_type('variable')) : ?>
                  <?php $var_price = $product->get_price(); ?>
                  <span class="product-price">&euro;<?php echo number_format((float)$var_price, 2, ',', '.'); ?></span>
                <?php else : ?>
                  <span class="product-price">&euro;<?php echo number_format((float)$price, 2, ',', '.'); ?></span>
                <?php endif; ?>
              </div>
              <?php if ($in_stock && !$product->is_type('variable')) : ?>
              <button class="product-add pw-add-to-cart"
                data-id="<?php echo esc_attr($product->get_id()); ?>"
                onclick="event.stopPropagation(); pwAddToCart(this)"
                data-nonce="<?php echo esc_attr(wp_create_nonce('pw_nonce')); ?>">+ WINKELWAGEN</button>
              <?php elseif ($product->is_type('variable')) : ?>
          <a class="product-add" href="<?php echo esc_url(get_permalink()); ?>">SELECTEER</a>
              <?php else : ?>
              <span class="product-add disabled">UITVERKOCHT</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>

      <!-- PAGINATION -->
      <div class="pw-pagination">
        <?php woocommerce_pagination(); ?>
      </div>

      <?php else : ?>
      <div class="shop-empty">
        <div style="font-family:var(--fd);font-size:48px;color:rgba(255,255,255,0.05);margin-bottom:16px">LEEG</div>
        <p style="color:var(--muted);margin-bottom:24px">Geen producten gevonden voor deze selectie.</p>
        <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn-primary">Bekijk alle producten</a>
      </div>
      <?php endif; ?>
    </div>

  </div><!-- .shop-body -->
</div><!-- .shop-page -->

<?php get_footer(); ?>
