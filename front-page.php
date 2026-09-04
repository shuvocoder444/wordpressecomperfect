<?php
/**
 * Front Page Template — PerfectWelding Homepage
 */
get_header();
?>

<!-- ═══════════════ HERO ═══════════════ -->
<section class="hero">
  <div class="hero-left" style="position:relative">
    <div class="grid-lines"></div>
    <div style="position:relative;z-index:1">
      <p class="hero-eyebrow">Premium TIG Onderdelen</p>
      <h1 class="hero-title"
          data-pw-i18n="PERFECT WELD EVERY TIME"
          data-pw-i18n-html-en="PERFECT &lt;em&gt;WELD&lt;/em&gt;&lt;br&gt;EVERY &lt;em&gt;TIME.&lt;/em&gt;"
          data-pw-i18n-html-nl="LAS &lt;em&gt;PERFECT.&lt;/em&gt;&lt;br&gt;ELKE &lt;em&gt;KEER.&lt;/em&gt;">PERFECT <em>WELD</em><br>EVERY <em>TIME.</em></h1>
      <p class="hero-sub">Professionele TIG-laskoppes, diffusors, back caps en handschoenen. Getest door lassers, voor lassers.</p>
      <div class="hero-actions">
        <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn-primary" data-pw-i18n="Shop now →">Shop nu &rarr;</a>
        <?php
        $tigware_term = get_term_by('slug', 'tigware', 'product_cat');
        $tigware_hero = $tigware_term ? get_term_link($tigware_term) : get_permalink(wc_get_page_id('shop'));
        if (is_wp_error($tigware_hero)) {
            $tigware_hero = get_permalink(wc_get_page_id('shop'));
        }
        ?>
        <a href="<?php echo esc_url($tigware_hero); ?>" class="btn-ghost" data-pw-i18n="Discover TIGWARE">Ontdek TIGWARE</a>
      </div>
    </div>
  </div>
  <div class="hero-right">
    <div class="grid-lines"></div>
    <div class="hero-visual" style="position:relative;z-index:1">
      <?php echo pw_tig_svg_large(); ?>
      <div class="spec-tag spec-1">PYREX GLASS<br>BOROSILICATE</div>
      <div class="spec-tag spec-2">TRIPLE<br>DIFFUSER</div>
      <div class="spec-tag spec-3">TEFLON<br>COATED</div>
    </div>
    <div class="hero-stats">
      <div class="hero-stat"><span class="stat-num"></span><span class="stat-label"> </span></div>
      <div class="hero-stat"><span class="stat-num"></span><span class="stat-label"></span></div>
      <div class="hero-stat"><span class="stat-num"> </span><span class="stat-label"></span></div>
    </div>
  </div>
</section>

<!-- ═══════════════ CATEGORIES ═══════════════ -->
<section class="section" style="padding-bottom:0">
  <div class="section-header">
    <h2 class="section-title">CATEGORIEËN</h2>
    <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="section-link">Bekijk alles &rarr;</a>
  </div>
  <div class="categories">
    <?php
    $cats = [
        ['cups',          '⬡', 'Cups',         '10 producten'],
        ['diffusers',     '◈', 'Diffusers',     '4 producten'],
        ['back-caps',     '◉', 'Back Caps',     '2 producten'],
        ['handschoenen',  '◈', 'Handschoenen',  '2 producten'],
    ];
    foreach ($cats as [$slug, $icon, $name, $count]) :
        $term = get_term_by('slug', $slug, 'product_cat');
        $url  = $term ? get_term_link($term) : get_permalink(wc_get_page_id('shop'));
    ?>
      <a class="cat-card" href="<?php echo esc_url($url); ?>">
      <div class="cat-icon"><?php echo $icon; ?></div>
      <div class="cat-name"><?php echo esc_html($name); ?></div>
      <div class="cat-count"><?php echo esc_html($term ? $term->count . ' producten' : $count); ?></div>
      <span class="cat-arrow">&rarr;</span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ═══════════════ FEATURED PRODUCTS ═══════════════ -->
<section class="section">
  <div class="section-header">
    <h2 class="section-title">BESTSELLERS</h2>
    <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="section-link">Alle producten &rarr;</a>
  </div>
  <div class="product-grid">
    <?php
    // The total_sales/meta_value_num query below requires a filesort over
    // the postmeta table (a known WooCommerce heavy-query pattern) and this
    // block ran fresh on EVERY homepage visit. We now cache the resulting
    // product IDs for a few hours with a transient, so the expensive query
    // only runs occasionally instead of on every page load. The cache is
    // cleared automatically whenever an order is placed (stock/sales
    // change), so bestsellers stay accurate.
    $bestseller_ids = get_transient('pw_bestseller_ids');
    if ( false === $bestseller_ids || empty($bestseller_ids) ) {
        $bs_query = new WP_Query([
            'post_type'      => 'product',
            'posts_per_page' => 6,
            'meta_key'       => 'total_sales',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'post_status'    => 'publish',
            'no_found_rows'  => true,
            'update_post_term_cache' => false,
            'fields'         => 'ids',
        ]);
        $bestseller_ids = $bs_query->posts;

        // Fallback: if no products have sales records yet, display latest published products
        if ( empty($bestseller_ids) ) {
            $fallback_query = new WP_Query([
                'post_type'      => 'product',
                'posts_per_page' => 6,
                'post_status'    => 'publish',
                'no_found_rows'  => true,
                'update_post_term_cache' => false,
                'fields'         => 'ids',
            ]);
            $bestseller_ids = $fallback_query->posts;
        }

        set_transient('pw_bestseller_ids', $bestseller_ids, 6 * HOUR_IN_SECONDS);
    }

    $args = [
        'post_type'      => 'product',
        'post__in'       => ! empty($bestseller_ids) ? $bestseller_ids : [0],
        'orderby'        => 'post__in',
        'posts_per_page' => 6,
        'post_status'    => 'publish',
        'no_found_rows'  => true,
        'update_post_term_cache' => false,
    ];
    $products = new WP_Query($args);
    if ($products->have_posts()) :
        while ($products->have_posts()) : $products->the_post();
            global $product;
            $image   = $product->get_image('pw-product-thumb', [
                'class'    => 'product-img-el',
                'loading'  => 'lazy',
                'decoding' => 'async',
                'sizes'    => '(max-width: 768px) 100vw, 33vw',
            ]);
            $price   = $product->get_price();
            $regular = $product->get_regular_price();
            $sale    = $product->is_on_sale();
            $in_stock = $product->is_in_stock();
            $brand   = get_post_meta($product->get_id(), '_brand', true) ?: 'PerfectWelding';
            $is_new  = (strtotime($product->get_date_created()) > strtotime('-30 days'));
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
            <?php else : ?>
              <span class="product-price">&euro;<?php echo number_format((float)$price, 2, ',', '.'); ?></span>
            <?php endif; ?>
          </div>
                   <?php if (!$in_stock) : ?>
          <span class="product-add disabled">UITVERKOCHT</span>
          <?php elseif ($product->is_type('variable')) : ?>
          <a class="product-add" href="<?php echo esc_url(get_permalink()); ?>">SELECTEER</a>
          <?php else : ?>
          <button class="product-add pw-add-to-cart"
            data-id="<?php echo esc_attr($product->get_id()); ?>"
            data-nonce="<?php echo esc_attr(wp_create_nonce('pw_nonce')); ?>">+ WINKELWAGEN</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endwhile; wp_reset_postdata(); endif; ?>
  </div>
</section>

<!-- ═══════════════ TIGWARE BANNER ═══════════════ -->
<div class="banner">
  <div>
    <h2 class="banner-title">TIGWARE<br><em>PREMIUM</em><br>LIJN.</h2>
    <p class="banner-text">Perfectwelding is ons eigen premium merk. Elk product is ontworpen voor maximale gasdekking, langere levensduur en een betere lasresultaat. Van teflon back caps tot pyrex cups.</p>
    <div class="banner-features">
      <div class="banner-feat"><div class="feat-dot"></div><span>Drievoudig diffusorsysteem voor maximale gasdekking</span></div>
      <div class="banner-feat"><div class="feat-dot"></div><span>Pyrex borosilicaat glas — onbreekbaar en hittebestendig</span></div>
      <div class="banner-feat"><div class="feat-dot"></div><span>Getest op RVS, titanium en aluminium</span></div>
      <div class="banner-feat"><div class="feat-dot"></div><span>Compatibel met WP17, WP18 en WP26 toortsen</span></div>
    </div>
    <?php
    $tigware_url = get_term_link(get_term_by('slug', 'tigware', 'product_cat'));
    if (!is_wp_error($tigware_url)) :
    ?>
    <a href="<?php echo esc_url($tigware_url); ?>" class="btn-primary">Shop TIGWARE &rarr;</a>
    <?php endif; ?>
  </div>
  <div class="banner-visual">
    <div class="tigware-bg-text">TIGWARE</div>
    <div class="banner-label">TIGWARE — PREMIUM LINE</div>
    <div style="position:relative;z-index:1">
      <?php echo pw_tig_svg_large('rgba(255,184,0,0.45)', 'rgba(255,184,0,0.07)', 'rgba(255,184,0,0.3)'); ?>
    </div>
  </div>
</div>

<!-- ═══════════════ TRUST BAR ═══════════════ -->
<div class="trust-bar">
  <div class="trust-item">
    <div class="trust-icon">&#9685;</div>
    <div class="trust-title">Snelle levering</div>
    <div class="trust-sub">Besteld voor 15:00, morgen in huis in Nederland.</div>
  </div>
  <div class="trust-item">
    <div class="trust-icon">&#10227;</div>
    <div class="trust-title">14 dagen retour</div>
    <div class="trust-sub">Niet tevreden? Stuur het gewoon terug.</div>
  </div>
  <div class="trust-item">
    <div class="trust-icon">&#10003;</div>
    <div class="trust-title">Kwaliteitsgarantie</div>
    <div class="trust-sub">Beste kwaliteit in zijn klasse.</div>
  </div>
  <div class="trust-item">
    <div class="trust-icon">&#9776;</div>
    <div class="trust-title">Expert support</div>
    <div class="trust-sub">Door lassers, voor lassers.</div>
  </div>
</div>

<!-- ═══════════════ TESTIMONIALS ═══════════════ -->
<section class="section">
  <div class="section-header">
    <h2 class="section-title">WAT LASSERS ZEGGEN</h2>
  </div>
  <div class="testimonial-grid">
    <div class="testimonial">
      <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
      <p class="testimonial-text">"De pyrex cup set heeft mijn TIG-lassen op titanium compleet veranderd. De gasdekking is dag en nacht vergeleken met standaard onderdelen."</p>
      <div class="testimonial-author">Arjan V. — Rotterdam</div>
    </div>
    <div class="testimonial">
      <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
      <p class="testimonial-text">"TIGWARE triple diffuser is echt iets bijzonders. Schone lassen op RVS, elke keer opnieuw."</p>
      <div class="testimonial-author">Daan M. — Eindhoven</div>
    </div>
    <div class="testimonial">
      <div class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
      <p class="testimonial-text">"Snelle levering, uitstekende kwaliteit. De teflon back caps staan geweldig op mijn toorts."</p>
      <div class="testimonial-author">Kevin B. — Utrecht</div>
    </div>
  </div>
</section>

<?php
get_footer();

// Helper: Large TIG SVG
function pw_tig_svg_large($stroke = 'rgba(255,77,0,0.4)', $fill = 'rgba(255,77,0,0.07)', $ring = 'rgba(255,77,0,0.3)') {
    return '<svg viewBox="0 0 120 240" width="150" xmlns="http://www.w3.org/2000/svg">
        <path d="M30 18 L20 200 Q20 216 34 216 L86 216 Q100 216 100 200 L90 18 Z" fill="#1a1a1a" stroke="' . $stroke . '" stroke-width="0.75"/>
        <path d="M38 26 L30 192 Q30 206 40 206 L80 206 Q90 206 90 192 L82 26 Z" fill="' . $fill . '" stroke="' . $ring . '" stroke-width="0.5"/>
        <ellipse cx="60" cy="80" rx="20" ry="3" fill="none" stroke="' . $ring . '" stroke-width="0.5"/>
        <ellipse cx="60" cy="115" rx="22" ry="3" fill="none" stroke="' . $ring . '" stroke-width="0.5"/>
        <ellipse cx="60" cy="150" rx="24" ry="3" fill="none" stroke="' . $ring . '" stroke-width="0.5"/>
        <rect x="55" y="6" width="10" height="20" rx="2" fill="#222" stroke="rgba(255,255,255,0.08)" stroke-width="0.5"/>
        <ellipse cx="60" cy="222" rx="14" ry="8" fill="rgba(255,184,0,0.55)"/>
        <ellipse cx="60" cy="225" rx="9" ry="5" fill="rgba(255,220,100,0.7)"/>
    </svg>';
}
?>
