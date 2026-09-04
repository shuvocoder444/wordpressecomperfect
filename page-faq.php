<?php
/**
 * Template Name: FAQ Page
 * Template Post Type: page
 *
 * FAQ accordion page — matches reference screenshot.
 * All items editable via WordPress Customizer → PerfectWelding → FAQ Page.
 */
get_header(); ?>

<?php
$page_title    = get_theme_mod('pw_faq_page_title',    'FAQ');
$page_subtitle = get_theme_mod('pw_faq_page_subtitle', 'Frequently asked questions about our products, shipping, and usage.');

// FAQ items stored as JSON in customizer
$faq_items_raw = get_theme_mod('pw_faq_items', '');
if (!empty($faq_items_raw)) {
    $faq_items = json_decode($faq_items_raw, true);
} else {
    $faq_items = [
        ['cat' => 'Products', 'q' => 'What is the difference between a Pyrex and a ceramic cup?',
         'a' => 'Pyrex cups are transparent, allowing you to see the arc during welding. This is especially useful for precision work. Ceramic cups are more robust and more resistant to heat. For Titanium and stainless steel, we recommend Pyrex due to better visibility.'],
        ['cat' => 'Products', 'q' => 'Does the cup fit my torch?',
         'a' => 'Our cups fit standard WP9, WP17, WP18 and WP26 TIG torches. Check the product page for the exact thread size. If you are unsure, contact us.'],
        ['cat' => 'Dispatch', 'q' => 'What are the shipping costs?',
         'a' => 'We ship free on orders above €100. Below that, flat shipping rates apply based on your country. See the Shipping page for current rates.'],
        ['cat' => 'Dispatch', 'q' => 'How long does delivery take?',
         'a' => 'Orders placed before 15:00 on business days are dispatched the same day. Delivery within the Netherlands takes 1–2 business days. International delivery: 3–7 business days.'],
        ['cat' => 'Usage', 'q' => 'What is the TIGWARE diffuser system?',
         'a' => 'A standard cup has one flow surface. TIGWARE has three. Not a marketing term — that is just how it works. This creates a more even gas shield and significantly improves weld quality.'],
        ['cat' => 'Return', 'q' => 'Can I return products?',
         'a' => 'Yes, within 14 days of receipt. Products must be unused and in original packaging. Contact us first before sending anything back.'],
        ['cat' => 'Products', 'q' => 'Do you also sell to businesses?',
         'a' => 'Yes. We offer business pricing for recurring orders. Contact us for a quote.'],
    ];
}

// Gather all unique categories
$cats = [];
foreach ($faq_items as $item) {
    $c = $item['cat'] ?? 'General';
    if (!in_array($c, $cats)) $cats[] = $c;
}
?>

<!-- ══ FAQ HEADER ═══════════════════════════════════════════════ -->
<section class="pw-faq-header">
  <div class="grid-lines"></div>
  <h1 class="pw-faq-title"><?php echo esc_html($page_title); ?></h1>
  <?php if ($page_subtitle) : ?>
    <p class="pw-faq-subtitle"><?php echo esc_html($page_subtitle); ?></p>
  <?php endif; ?>
</section>

<!-- ══ FAQ BODY ══════════════════════════════════════════════════ -->
<section class="pw-faq-body">
  <!-- Category sidebar -->
  <nav class="pw-faq-cats" aria-label="FAQ categories">
    <span class="pw-faq-cats-label">CATEGORIES</span>
    <?php foreach ($cats as $cat) : ?>
      <button class="pw-faq-cat-btn" data-cat="<?php echo esc_attr($cat); ?>">
        <?php echo esc_html($cat); ?>
      </button>
    <?php endforeach; ?>
  </nav>

  <!-- Accordion -->
  <div class="pw-faq-accordion" id="pw-faq-accordion">
    <?php foreach ($faq_items as $index => $item) :
      $q   = $item['q']   ?? '';
      $a   = $item['a']   ?? '';
      $cat = $item['cat'] ?? 'General';
      if (!$q) continue;
    ?>
      <div class="pw-faq-item" data-cat="<?php echo esc_attr($cat); ?>" <?php echo $index === 0 ? 'data-open="true"' : ''; ?>>
        <button class="pw-faq-question" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>">
          <span><?php echo esc_html($q); ?></span>
          <span class="pw-faq-icon"><?php echo $index === 0 ? '−' : '+'; ?></span>
        </button>
        <div class="pw-faq-answer" <?php echo $index === 0 ? '' : 'hidden'; ?>>
          <p><?php echo nl2br(esc_html($a)); ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<script>
(function(){
  // Accordion toggle
  document.querySelectorAll('.pw-faq-question').forEach(function(btn){
    btn.addEventListener('click', function(){
      var item   = this.closest('.pw-faq-item');
      var answer = item.querySelector('.pw-faq-answer');
      var icon   = item.querySelector('.pw-faq-icon');
      var isOpen = item.dataset.open === 'true';
      // close all
      document.querySelectorAll('.pw-faq-item').forEach(function(i){
        i.dataset.open = 'false';
        i.querySelector('.pw-faq-question').setAttribute('aria-expanded','false');
        i.querySelector('.pw-faq-answer').hidden = true;
        i.querySelector('.pw-faq-icon').textContent = '+';
      });
      if (!isOpen) {
        item.dataset.open = 'true';
        btn.setAttribute('aria-expanded','true');
        answer.hidden = false;
        icon.textContent = '−';
      }
    });
  });

  // Category filter
  var catBtns = document.querySelectorAll('.pw-faq-cat-btn');
  catBtns.forEach(function(btn){
    btn.addEventListener('click', function(){
      catBtns.forEach(function(b){ b.classList.remove('active'); });
      this.classList.add('active');
      var cat = this.dataset.cat;
      document.querySelectorAll('.pw-faq-item').forEach(function(item){
        item.style.display = (item.dataset.cat === cat) ? '' : 'none';
      });
    });
  });
  // Activate first cat
  if (catBtns.length) catBtns[0].classList.add('active');
})();
</script>

<?php get_footer(); ?>
