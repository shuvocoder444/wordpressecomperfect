<?php
/**
 * Single Product Page
 * Replicates the original product detail design exactly
 * Fixed: Strict global WooCommerce taxonomy formatting (attribute_pa_...) to bypass variation validation failures
 */
defined('ABSPATH') || exit;
get_header();

while (have_posts()) : the_post();
global $product;

$price     = $product->get_price();
$regular   = $product->get_regular_price();
$sale      = $product->is_on_sale();
$in_stock  = $product->is_in_stock();
$brand     = get_post_meta($product->get_id(), '_brand', true) ?: 'PerfectWelding';
$is_tigware = ($brand === 'TIGWARE');
$stroke    = $is_tigware ? 'rgba(255,184,0,0.45)' : 'rgba(255,77,0,0.4)';
$fill      = $is_tigware ? 'rgba(255,184,0,0.07)' : 'rgba(255,77,0,0.07)';
$ring      = $is_tigware ? 'rgba(255,184,0,0.3)'  : 'rgba(255,77,0,0.3)';

// Gallery images: collect featured image + attachments in unified array
$post_thumbnail_id = $product->get_image_id();
$attachment_ids    = $product->get_gallery_image_ids() ?: [];
$gallery_images    = [];

if ($post_thumbnail_id) {
    $gallery_images[] = $post_thumbnail_id;
}
foreach ($attachment_ids as $att_id) {
    if (!in_array($att_id, $gallery_images)) {
        $gallery_images[] = $att_id;
    }
}
$has_images = !empty($gallery_images);
?>

<div class="pw-breadcrumb">
  <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>">&larr; Terug naar shop</a>
</div>

<div class="product-detail">

  <div class="product-detail-img" id="pd-img">
    <div class="grid-lines"></div>
    <?php if ($has_images) : 
      $first_id = $gallery_images[0];
      $first_single_url = wp_get_attachment_image_url($first_id, 'pw-product-single') ?: wp_get_attachment_url($first_id);
      $first_full_url   = wp_get_attachment_image_url($first_id, 'full') ?: $first_single_url;
      $first_srcset     = wp_get_attachment_image_srcset($first_id, 'pw-product-single');
    ?>
      <div class="pd-gallery">
        <div class="pd-main-img" id="pd-main-img-wrap" data-full="<?php echo esc_url($first_full_url); ?>" onclick="pwOpenLightbox(window.pwCurrentGalleryIndex || 0)">
          <img src="<?php echo esc_url($first_single_url); ?>"
               <?php if ($first_srcset) : ?>srcset="<?php echo esc_attr($first_srcset); ?>" sizes="(max-width: 768px) 100vw, 50vw"<?php endif; ?>
               alt="<?php echo esc_attr($product->get_name()); ?>"
               class="pd-img-el"
               id="pd-main-img"
               data-full="<?php echo esc_url($first_full_url); ?>"
               loading="eager"
               decoding="async"
               fetchpriority="high" />
          <div class="pd-zoom-hint" title="Vergroten">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
            <span>Zoom</span>
          </div>
        </div>

        <?php if (count($gallery_images) > 1) : ?>
        <div class="pd-thumbs">
          <?php foreach ($gallery_images as $idx => $id) : 
            $single_url = wp_get_attachment_image_url($id, 'pw-product-single') ?: wp_get_attachment_url($id);
            $full_url   = wp_get_attachment_image_url($id, 'full') ?: $single_url;
            $srcset     = wp_get_attachment_image_srcset($id, 'pw-product-single');
          ?>
          <div class="pd-thumb <?php echo $idx === 0 ? 'active' : ''; ?>"
               onclick="pwSwitchImage(this, <?php echo $idx; ?>)"
               data-index="<?php echo $idx; ?>"
               data-src="<?php echo esc_url($single_url); ?>"
               data-full="<?php echo esc_url($full_url); ?>"
               data-srcset="<?php echo esc_attr($srcset); ?>"
               data-sizes="(max-width: 768px) 100vw, 50vw">
            <?php echo wp_get_attachment_image($id, [80, 80], false, [
              'class'    => 'pd-thumb-img',
              'loading'  => 'lazy',
              'decoding' => 'async',
            ]); ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    <?php else : ?>
      <div class="pd-svg-wrap" style="position:relative;z-index:1">
        <svg viewBox="0 0 120 240" width="180" xmlns="http://www.w3.org/2000/svg">
          <path d="M30 18 L20 200 Q20 216 34 216 L86 216 Q100 216 100 200 L90 18 Z" fill="#1a1a1a" stroke="<?php echo $stroke; ?>" stroke-width="0.75"/>
          <path d="M38 26 L30 192 Q30 206 40 206 L80 206 Q90 206 90 192 L82 26 Z" fill="<?php echo $fill; ?>" stroke="<?php echo $ring; ?>" stroke-width="0.5"/>
          <ellipse cx="60" cy="80" rx="20" ry="3" fill="none" stroke="<?php echo $ring; ?>" stroke-width="0.5"/>
          <ellipse cx="60" cy="115" rx="22" ry="3" fill="none" stroke="<?php echo $ring; ?>" stroke-width="0.5"/>
          <ellipse cx="60" cy="150" rx="24" ry="3" fill="none" stroke="<?php echo $ring; ?>" stroke-width="0.5"/>
          <rect x="55" y="6" width="10" height="20" rx="2" fill="#222" stroke="rgba(255,255,255,0.08)" stroke-width="0.5"/>
          <ellipse cx="60" cy="222" rx="14" ry="8" fill="rgba(255,184,0,0.55)"/>
          <ellipse cx="60" cy="225" rx="9" ry="5" fill="rgba(255,220,100,0.7)"/>
        </svg>
        <?php if ($is_tigware) : ?>
        <div style="position:absolute;top:16px;left:16px;font-family:var(--fm);font-size:10px;color:var(--accent2);letter-spacing:2px">TIGWARE — PREMIUM</div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="product-detail-info">
    <div class="product-detail-brand"><?php echo esc_html($brand); ?></div>
    <h1 class="product-detail-name"><?php the_title(); ?></h1>

    <div class="product-detail-price" id="pd-price">
      <?php if ($sale && $regular) : ?>
        <span style="text-decoration:line-through;font-size:28px;color:var(--muted);margin-right:12px">&euro;<?php echo number_format((float)$regular, 2, ',', '.'); ?></span>
        &euro;<?php echo number_format((float)$price, 2, ',', '.'); ?>
      <?php elseif ($product->is_type('variable')) : ?>
        <?php echo $product->get_price_html(); ?>
      <?php else : ?>
        &euro;<?php echo number_format((float)$price, 2, ',', '.'); ?>
      <?php endif; ?>
    </div>

    <div class="product-detail-tax">Excl. BTW &mdash; Gratis verzending boven &euro;100</div>

    <div class="product-detail-desc">
      <?php echo wp_kses_post($product->get_short_description() ?: $product->get_description()); ?>
    </div>

    <?php if ($product->is_type('variable')) : 
      $available_variations = $product->get_available_variations();
      $attributes = $product->get_variation_attributes();
      
      $variations_json = wp_json_encode($available_variations);
      $variations_attr = function_exists('wc_esc_json') ? wc_esc_json($variations_json) : _wp_specialchars($variations_json, ENT_QUOTES, 'UTF-8', true);
    ?>
      <style>
        .variations_form table.variations, 
        .variations_form .single_variation_wrap { display: none !important; }
      </style>

      <form class="variations_form cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint($product->get_id()); ?>" data-product_variations="<?php echo $variations_attr; ?>">
        
        <?php do_action('woocommerce_before_add_to_cart_form'); ?>

        <div class="product-variants">
          <?php foreach ($attributes as $attr_name => $options) : 
            $label = wc_attribute_label($attr_name);
            $is_taxonomy = taxonomy_exists($attr_name);
            $normalized_name = (strpos($attr_name, 'pa_') === 0) ? 'attribute_' . $attr_name : 'attribute_' . sanitize_title($attr_name);

            // Build options array with value (slug/value) and name (label)
            $attr_opts = [];
            if ($is_taxonomy) {
                $terms = wc_get_product_terms($product->get_id(), $attr_name, ['fields' => 'all']);
                if (!empty($terms) && !is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        if (in_array($term->slug, $options, true) || in_array($term->name, $options, true)) {
                            $attr_opts[] = [
                                'val'  => $term->slug,
                                'name' => $term->name,
                            ];
                        }
                    }
                }
            }
            if (empty($attr_opts)) {
                foreach ($options as $opt) {
                    $attr_opts[] = [
                        'val'  => is_string($opt) ? $opt : (isset($opt->slug) ? $opt->slug : (string)$opt),
                        'name' => is_string($opt) ? $opt : (isset($opt->name) ? $opt->name : (string)$opt),
                    ];
                }
            }
          ?>
          <div class="variant-label"><?php echo esc_html(strtoupper($label)); ?></div>
          <div class="variant-options" data-attr-group="<?php echo esc_attr($normalized_name); ?>">
            
            <select id="<?php echo esc_attr($normalized_name); ?>" name="<?php echo esc_attr($normalized_name); ?>" class="pw-wc-target-select" data-attribute_name="<?php echo esc_attr($normalized_name); ?>" style="display:none !important;">
              <option value=""><?php echo esc_html__('Kies een optie', 'woocommerce'); ?></option>
              <?php foreach ($attr_opts as $opt) : ?>
                <option value="<?php echo esc_attr($opt['val']); ?>"><?php echo esc_html($opt['name']); ?></option>
              <?php endforeach; ?>
            </select>

            <?php foreach ($attr_opts as $opt) : ?>
            <button type="button" class="variant-btn"
              data-attr="<?php echo esc_attr($normalized_name); ?>"
              data-attr-target="<?php echo esc_attr($normalized_name); ?>"
              data-val="<?php echo esc_attr($opt['val']); ?>"
              onclick="pwSelectVariantBridge(this)">
              <?php echo esc_html($opt['name']); ?>
            </button>
            <?php endforeach; ?>

          </div>
          <?php endforeach; ?>
        </div>

        <div style="display:flex;gap:12px;margin-bottom:12px;margin-top:20px;">
          <div class="qty-wrap">
            <button type="button" class="qty-btn" onclick="pwQtyChangeForm(-1)">−</button>
            <input type="number" id="pw-qty" name="quantity" value="1" min="1" max="99" class="qty-input">
            <button type="button" class="qty-btn" onclick="pwQtyChangeForm(1)">+</button>
          </div>
          
          <button type="button"
            class="single_add_to_cart_button button alt add-to-cart-btn"
            id="pd-add-btn"
            data-id="<?php echo esc_attr($product->get_id()); ?>"
            data-nonce="<?php echo esc_attr(wp_create_nonce('pw_nonce')); ?>"
            onclick="pwAddToCartDetail(this)"
            style="flex:1;">
            Toevoegen aan winkelwagen
          </button>
        </div>

        <input type="hidden" name="add-to-cart" value="<?php echo absint($product->get_id()); ?>" />
        <input type="hidden" name="product_id" value="<?php echo absint($product->get_id()); ?>" />
        <input type="hidden" name="variation_id" class="variation_id" value="0" />

        <?php do_action('woocommerce_after_add_to_cart_form'); ?>
      </form>

      <script>
      window.pwVariations = <?php echo wp_json_encode($available_variations); ?>;

      function pwSelectVariantBridge(buttonElement) {
          const targetAttributeId = buttonElement.getAttribute('data-attr-target');
          const valueChosen = buttonElement.getAttribute('data-val');
          
          // Toggle layout UI classes
          const wrapper = buttonElement.closest('.variant-options');
          if (wrapper) {
              wrapper.querySelectorAll('.variant-btn').forEach(btn => {
                  btn.classList.remove('active');
                  btn.classList.remove('selected');
              });
              buttonElement.classList.add('active');
              buttonElement.classList.add('selected');
          }
          
          // Select native dropdown option and fire WooCommerce Core event monitors
          const realSelect = document.getElementById(targetAttributeId);
          if (realSelect) {
              realSelect.value = valueChosen;
              
              if (window.jQuery) {
                  const $form = window.jQuery(buttonElement).closest('form.variations_form');
                  window.jQuery(realSelect).trigger('change');
                  $form.trigger('check_variations');
              } else {
                  realSelect.dispatchEvent(new Event('change', { bubbles: true }));
              }
          }

          if (typeof pwResolveVariation === 'function') {
              pwResolveVariation();
          }

          const addBtn = document.getElementById('pd-add-btn');
          if (addBtn) {
              addBtn.disabled = false;
              addBtn.style.opacity = '1';
              addBtn.classList.remove('disabled', 'wc-variation-is-unavailable');
          }
      }
      </script>

    <?php else : ?>
      
      <form class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
        <?php do_action('woocommerce_before_add_to_cart_form'); ?>
        
        <div style="display:flex;gap:12px;margin-bottom:12px">
          <div class="qty-wrap">
            <button type="button" class="qty-btn" onclick="pwQtyChangeForm(-1)">−</button>
            <input type="number" id="pw-qty" name="quantity" value="1" min="1" max="99" class="qty-input">
            <button type="button" class="qty-btn" onclick="pwQtyChangeForm(1)">+</button>
          </div>
          
          <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="single_add_to_cart_button button alt add-to-cart-btn" id="pd-add-btn" style="flex:1;" <?php echo !$in_stock ? 'disabled style="opacity:0.4"' : ''; ?>>
            <?php echo $in_stock ? 'Toevoegen aan winkelwagen' : 'Uitverkocht'; ?>
          </button>
        </div>

        <?php do_action('woocommerce_after_add_to_cart_form'); ?>
      </form>
    <?php endif; ?>

    <script>
    function pwQtyChangeForm(amount) {
        const qtyField = document.getElementById('pw-qty');
        if (qtyField) {
            let currentQuantity = parseInt(qtyField.value) || 1;
            currentQuantity += amount;
            if (currentQuantity < 1) currentQuantity = 1;
            qtyField.value = currentQuantity;
        }
    }

    // Capture found variations to swap out display prices seamlessly
    document.addEventListener("DOMContentLoaded", function() {
        if (window.jQuery) {
            window.jQuery(document).on('found_variation', function(event, variation) {
                if (variation.price_html) {
                    const priceContainer = document.getElementById('pd-price');
                    if (priceContainer) priceContainer.innerHTML = variation.price_html;
                }
            });
        }
    });
    </script>

    <div style="display:flex;gap:24px;margin-bottom:32px;font-size:12px;color:var(--muted);font-family:var(--fm)">
      <span style="color:<?php echo $in_stock ? 'var(--accent)' : 'var(--muted)'; ?>">
        ● <?php echo $in_stock ? 'Op voorraad' : 'Uitverkocht'; ?>
      </span>
      <span>✓ Gratis verzending boven €100</span>
      <span>↺ 14 dagen retour</span>
    </div>

    <?php
    $specs_raw = get_post_meta($product->get_id(), '_pw_specs', true);
    $attributes_display = $product->get_attributes();
    ?>
    <?php if (!empty($attributes_display)) : ?>
    <div class="product-specs">
      <div style="font-family:var(--fm);font-size:11px;color:var(--muted);letter-spacing:2px;text-transform:uppercase;margin-bottom:16px">Specificaties</div>
      <?php foreach ($attributes_display as $attribute) :
        if ($attribute->get_visible()) :
          $name = wc_attribute_label($attribute->get_name());
          $values = array_map('esc_html', wc_get_product_terms($product->get_id(), $attribute->get_name(), ['fields' => 'names']));
          if (empty($values)) $values = array_map('esc_html', $attribute->get_options());
      ?>
      <div class="spec-row">
        <span class="spec-key"><?php echo esc_html($name); ?></span>
        <span class="spec-val"><?php echo implode(', ', $values); ?></span>
      </div>
      <?php endif; endforeach; ?>
      <?php if ($product->get_weight()) : ?>
      <div class="spec-row">
        <span class="spec-key">Gewicht</span>
        <span class="spec-val"><?php echo esc_html($product->get_weight()); ?> kg</span>
      </div>
      <?php endif; ?>
      <?php if ($product->has_dimensions()) : ?>
      <div class="spec-row">
        <span class="spec-key">Afmetingen</span>
        <span class="spec-val"><?php echo esc_html(implode(' × ', array_filter([$product->get_length(), $product->get_width(), $product->get_height()]))); ?> cm</span>
      </div>
      <?php endif; ?>
      <div class="spec-row">
        <span class="spec-key">SKU</span>
        <span class="spec-val"><?php echo esc_html($product->get_sku() ?: '—'); ?></span>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<div class="pw-product-tabs">
  <div class="pw-tabs-nav">
    <button class="pw-tab-btn active" data-tab="description">Beschrijving</button>
    <button class="pw-tab-btn" data-tab="reviews">Reviews (<?php echo $product->get_review_count(); ?>)</button>
  </div>
  <div class="pw-tab-content active" id="tab-description">
    <div class="pw-description">
      <?php echo wp_kses_post($product->get_description()); ?>
    </div>
  </div>
  <div class="pw-tab-content" id="tab-reviews">
    <?php comments_template(); ?>
  </div>
</div>

<?php
$related_ids = wc_get_related_products($product->get_id(), 3);
if (!empty($related_ids)) :
  $related_products = array_map('wc_get_product', $related_ids);
  $related_products = array_filter($related_products);
?>
<section class="section" style="border-top:0.5px solid var(--border)">
  <div class="section-header">
    <h2 class="section-title">GERELATEERD</h2>
    <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="section-link">Bekijk alles &rarr;</a>
  </div>
  <div class="product-grid">
    <?php foreach ($related_products as $rp) :
      $r_price    = $rp->get_price();
      $r_regular  = $rp->get_regular_price();
      $r_sale     = $rp->is_on_sale();
      $r_in_stock = $rp->is_in_stock();
      $r_brand    = get_post_meta($rp->get_id(), '_brand', true) ?: 'PerfectWelding';
      $r_img      = $rp->get_image('pw-product-thumb', [
        'class'    => 'product-img-el',
        'loading'  => 'lazy',
        'decoding' => 'async',
        'sizes'    => '(max-width: 768px) 100vw, 33vw',
      ]);
    ?>
    <div class="product-card" onclick="window.location='<?php echo esc_url(get_permalink($rp->get_id())); ?>'">
      <div class="product-img">
        <div class="grid-lines"></div>
        <?php if ($r_sale) : ?><span class="product-badge sale">SALE</span><?php endif; ?>
        <?php if ($r_img) echo $r_img; else echo '<div class="product-img-placeholder">' . pw_tig_svg() . '</div>'; ?>
      </div>
      <div class="product-info">
        <div class="product-brand"><?php echo esc_html($r_brand); ?></div>
        <div class="product-name"><?php echo esc_html($rp->get_name()); ?></div>
        <div class="product-footer">
          <div>
            <?php if ($r_sale && $r_regular) : ?>
              <span class="product-price-old">&force;&euro;<?php echo number_format((float)$r_regular, 2, ',', '.'); ?></span>
              <span class="product-price">&euro;<?php echo number_format((float)$r_price, 2, ',', '.'); ?></span>
            <?php else : ?>
              <span class="product-price">&euro;<?php echo number_format((float)$r_price, 2, ',', '.'); ?></span>
            <?php endif; ?>
          </div>
          <?php if ($r_in_stock) : ?>
          <button class="product-add pw-add-to-cart"
            onclick="event.stopPropagation(); pwAddToCart(this)"
            data-id="<?php echo esc_attr($rp->get_id()); ?>"
            data-nonce="<?php echo esc_attr(wp_create_nonce('pw_nonce')); ?>">+ WINKELWAGEN</button>
          <?php else : ?>
          <span class="product-add disabled">UITVERKOCHT</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php
endwhile;
get_footer();
?>
