/**
 * PerfectWelding — Main JS
 * AJAX cart, UI interactions, animations
 */

(function($) {
  'use strict';

  // ─── MOBILE NAV ─────────────────────────────────────────────
  // Single authoritative controller – no inline script in header.php
  (function() {
    function initMobileNav() {
      var toggle  = document.getElementById('nav-toggle');
      var nav     = document.getElementById('mobile-nav');
      var closeBtn= document.getElementById('nav-close');
      var overlay = document.getElementById('nav-overlay');
      if (!toggle || !nav) return;

      function openNav() {
        nav.classList.add('open');
        if (overlay) { overlay.classList.add('open'); overlay.setAttribute('aria-hidden','false'); }
        nav.setAttribute('aria-hidden','false');
        toggle.setAttribute('aria-expanded','true');
        document.body.style.overflow = 'hidden';
        // Animate hamburger → X
        var spans = toggle.querySelectorAll('span');
        if (spans[0]) spans[0].style.transform = 'rotate(45deg) translate(4px, 4px)';
        if (spans[1]) spans[1].style.opacity = '0';
        if (spans[2]) spans[2].style.transform = 'rotate(-45deg) translate(4px, -4px)';
      }

      function closeNav() {
        nav.classList.remove('open');
        if (overlay) { overlay.classList.remove('open'); overlay.setAttribute('aria-hidden','true'); }
        nav.setAttribute('aria-hidden','true');
        toggle.setAttribute('aria-expanded','false');
        document.body.style.overflow = '';
        // Reset hamburger
        var spans = toggle.querySelectorAll('span');
        if (spans[0]) spans[0].style.transform = '';
        if (spans[1]) spans[1].style.opacity = '';
        if (spans[2]) spans[2].style.transform = '';
      }

      // Remove any previously bound listeners by cloning
      var newToggle = toggle.cloneNode(true);
      toggle.parentNode.replaceChild(newToggle, toggle);
      toggle = newToggle;

      toggle.addEventListener('click', function(e) {
        e.stopPropagation();
        nav.classList.contains('open') ? closeNav() : openNav();
      });
      if (closeBtn) closeBtn.addEventListener('click', closeNav);
      if (overlay)  overlay.addEventListener('click', closeNav);
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && nav.classList.contains('open')) closeNav();
      });

      // Close nav when a link is tapped
      nav.querySelectorAll('a').forEach(function(a) {
        a.addEventListener('click', function() {
          setTimeout(closeNav, 120);
        });
      });
    }

    // Run immediately if DOM ready, else wait
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initMobileNav);
    } else {
      initMobileNav();
    }
  })();

  // ─── NAV SCROLL EFFECT ───────────────────────────────────────
  const nav = document.getElementById('pw-nav');
  if (nav) {
    window.addEventListener('scroll', function() {
      if (window.scrollY > 40) {
        nav.style.borderBottomColor = 'rgba(255,77,0,0.15)';
      } else {
        nav.style.borderBottomColor = 'rgba(255,255,255,0.07)';
      }
    }, { passive: true });
  }

  // ─── TOAST NOTIFICATION ──────────────────────────────────────
  function pwShowToast(message, link) {
    let toast = document.querySelector('.pw-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.className = 'pw-toast';
      document.body.appendChild(toast);
    }
    toast.innerHTML = '<span class="pw-toast-icon">✓</span><span>' + message +
      (link ? ' <a href="' + link + '">Bekijk winkelwagen →</a>' : '') + '</span>';
    toast.classList.add('show');
    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(function() {
      toast.classList.remove('show');
    }, 3800);
  }

  // ─── AJAX ADD TO CART (product cards) ───────────────────────
  window.pwAddToCart = function(btn) {
    if (!btn || btn.disabled) return;
    const id    = btn.dataset.id;
    const nonce = btn.dataset.nonce || (pw_vars && pw_vars.nonce);
    if (!id) return;

    btn.classList.add('loading');
    btn.textContent = '…';

    $.ajax({
      url: pw_vars.ajax_url,
      method: 'POST',
      data: { action: 'pw_add_to_cart', product_id: id, qty: 1, nonce: nonce },
      success: function(res) {
        if (res.success) {
          // Update cart badge
          document.querySelectorAll('.cart-badge').forEach(function(el) {
            el.textContent = res.data.count;
          });
          pwShowToast('Toegevoegd aan winkelwagen!', pw_vars.cart_url);
          btn.textContent = '✓ TOEGEVOEGD';
          btn.style.background = '#1a5c1a';
          setTimeout(function() {
            btn.textContent = '+ WINKELWAGEN';
            btn.style.background = '';
            btn.classList.remove('loading');
          }, 2000);
          // Trigger WC fragment refresh
          $(document.body).trigger('wc_fragment_refresh');
          $(document.body).trigger('added_to_cart', [res.data, undefined, btn]);
        } else {
          btn.textContent = '+ WINKELWAGEN';
          btn.classList.remove('loading');
          pwShowToast(res.data ? res.data.message : 'Fout. Probeer opnieuw.');
        }
      },
      error: function() {
        btn.textContent = '+ WINKELWAGEN';
        btn.classList.remove('loading');
      }
    });
  };

  // ─── PRODUCT CARD ADD TO CART (event delegation) ─────────────
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.pw-add-to-cart, .product-add');
    if (btn) {
      if (btn.classList.contains('pw-add-to-cart')) {
        e.preventDefault();
        e.stopPropagation();
        pwAddToCart(btn);
      }
      return;
    }

    // ─── PRODUCT CARD CLICK THROUGH ───
    const card = e.target.closest('.product-card');
    if (card && !e.target.closest('a, button, input, select, label')) {
      const link = card.querySelector('a.product-card-link, a.product-img-link, .product-name a, a');
      if (link && link.href) {
        window.location.href = link.href;
      }
    }
  });

  // ─── DETAIL PAGE: QTY ────────────────────────────────────────
  window.pwQtyChange = function(delta) {
    const input = document.getElementById('pw-qty');
    if (!input) return;
    const val = parseInt(input.value) || 1;
    const next = Math.max(1, Math.min(99, val + delta));
    input.value = next;
  };

  // ─── DETAIL PAGE: ADD TO CART ────────────────────────────────
  window.pwAddToCartDetail = function(btn) {
    if (!btn || btn.disabled) return;
    const id    = btn.dataset.id;
    const nonce = btn.dataset.nonce || pw_vars.nonce;
    const qty   = parseInt(document.getElementById('pw-qty')?.value) || 1;

    // Check variation selection for variable products
    const varBtns = document.querySelectorAll('.variant-btn');
    let variationId = parseInt(btn.dataset.variationId) || 0;

    // attrData holds selected attribute values to send to server
    // This lets PHP resolve variation_id server-side as a reliable fallback
    var attrData = {};

    if (varBtns.length > 0) {
      // Count unique attrs
      const allAttrs = {};
      varBtns.forEach(function(b) { allAttrs[b.dataset.attr] = true; });
      const selectedBtns = document.querySelectorAll('.variant-btn.selected');
      const selectedAttrs = {};
      selectedBtns.forEach(function(b) {
        selectedAttrs[b.dataset.attr] = true;
        // Prefix with "attribute_" so PHP/WooCommerce can match directly
        var attrKey = b.dataset.attr && b.dataset.attr.indexOf('attribute_') === 0
          ? b.dataset.attr
          : 'attribute_' + b.dataset.attr;
        attrData[attrKey] = b.dataset.val;
      });

      if (Object.keys(selectedAttrs).length < Object.keys(allAttrs).length) {
        pwShowToast('Selecteer alle opties eerst.');
        // Highlight unselected attribute groups
        document.querySelectorAll('.variant-label').forEach(function(label) {
          var nextEl = label.nextElementSibling;
          if (nextEl && nextEl.classList.contains('variant-options')) {
            var attrName = nextEl.querySelector('.variant-btn') && nextEl.querySelector('.variant-btn').dataset.attr;
            if (attrName && !selectedAttrs[attrName]) {
              nextEl.style.outline = '2px solid var(--accent)';
              setTimeout(function() { nextEl.style.outline = ''; }, 2000);
            }
          }
        });
        return;
      }

      if (!variationId) {
        // Try one more time to resolve from client-side data
        pwResolveVariation();
        variationId = parseInt(btn.dataset.variationId) || 0;
      }

      // If still 0, we rely on server-side resolution via attrData.
      // If attrData is also empty (no swatches selected), bail out.
      if (!variationId && Object.keys(attrData).length === 0) {
        pwShowToast('Selecteer alle opties eerst.');
        return;
      }
    }

    btn.classList.add('loading');
    btn.textContent = 'Toevoegen…';

    // Build AJAX data: include both variation_id AND raw attribute values
    // so the server can resolve variation_id if client-side matching failed
    var ajaxData = $.extend(
      { action: 'pw_add_to_cart', product_id: id, qty: qty, variation_id: variationId, nonce: nonce },
      attrData
    );

    $.ajax({
      url: pw_vars.ajax_url,
      method: 'POST',
      data: ajaxData,
      success: function(res) {
        if (res.success) {
          document.querySelectorAll('.cart-badge').forEach(function(el) {
            el.textContent = res.data.count;
          });
          btn.textContent = '✓ Toegevoegd!';
          btn.style.background = '#1a5c1a';
          setTimeout(function() {
            btn.textContent = 'Toevoegen aan winkelwagen';
            btn.style.background = '';
            btn.classList.remove('loading');
          }, 2200);
          pwShowToast('Toegevoegd aan winkelwagen!', pw_vars.cart_url);
          $(document.body).trigger('wc_fragment_refresh');
        } else {
          btn.textContent = 'Toevoegen aan winkelwagen';
          btn.classList.remove('loading');
          pwShowToast(res.data ? res.data.message : 'Kon niet toevoegen. Probeer opnieuw.');
        }
      },
      error: function() {
        btn.textContent = 'Toevoegen aan winkelwagen';
        btn.classList.remove('loading');
      }
    });
  };

  // ─── VARIANT SELECTION ───────────────────────────────────────
  window.pwSelectVariant = function(btn) {
    if (!btn) return;
    const attr = btn.dataset.attr;
    document.querySelectorAll('.variant-btn[data-attr="' + attr + '"]').forEach(function(b) {
      b.classList.remove('selected');
    });
    btn.classList.add('selected');
    pwResolveVariation();
  };

  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.variant-btn');
    if (!btn) return;
    pwSelectVariant(btn);
  });

  // ─── NORMALIZE ATTRIBUTE KEY & VALUE ─────────────────────────
  function pwNormalizeAttr(key) {
    if (!key) return '';
    return key.toLowerCase()
      .replace(/^attribute_pa_/, '')
      .replace(/^attribute_/, '')
      .replace(/^pa_/, '')
      .replace(/[\s_\-]+/g, '');
  }

  function pwNormalizeVal(val) {
    if (val === undefined || val === null) return '';
    return String(val).toLowerCase()
      .replace(/[\s_\-]+/g, '')
      .replace(/,/g, '.');
  }

  // ─── RESOLVE VARIATION ID FROM SELECTED ATTRS ────────────────
  function pwResolveVariation() {
    var selected = {};
    document.querySelectorAll('.variant-btn.selected').forEach(function(b) {
      selected[b.dataset.attr] = b.dataset.val;
    });

    var addBtn = document.getElementById('pd-add-btn');

    if (Object.keys(selected).length === 0) {
      return;
    }

    // Find matching variation from inline data
    var variations = window.pwVariations || [];
    var matchedVariation = null;

    for (var i = 0; i < variations.length; i++) {
      var v = variations[i];
      var match = true;

      for (var attr in selected) {
        var selectedVal = selected[attr];
        var attrNorm = pwNormalizeAttr(attr);

        var vAttrVal = '';
        for (var vKey in v.attributes) {
          if (pwNormalizeAttr(vKey) === attrNorm) {
            vAttrVal = v.attributes[vKey];
            break;
          }
        }

        // Empty string means "any value" (catch-all variation)
        if (vAttrVal !== '' && pwNormalizeVal(vAttrVal) !== pwNormalizeVal(selectedVal)) {
          match = false;
          break;
        }
      }

      if (match) {
        matchedVariation = v;
        break;
      }
    }

    if (matchedVariation) {
      if (addBtn) {
        addBtn.dataset.variationId = matchedVariation.variation_id;
        addBtn.disabled = false;
        addBtn.style.opacity = '1';
        addBtn.classList.remove('disabled', 'wc-variation-is-unavailable');
      }
      var variationInput = document.querySelector('input.variation_id');
      if (variationInput) variationInput.value = matchedVariation.variation_id;
      
      // Update price display
      if (matchedVariation.display_price !== undefined) {
        var priceEl = document.getElementById('pd-price');
        if (priceEl) {
          if (matchedVariation.display_regular_price && matchedVariation.display_regular_price !== matchedVariation.display_price) {
            priceEl.innerHTML = '<span style="text-decoration:line-through;font-size:28px;color:var(--muted);margin-right:12px">&euro;' +
              parseFloat(matchedVariation.display_regular_price).toFixed(2).replace('.', ',') + '</span>' +
              '&euro;' + parseFloat(matchedVariation.display_price).toFixed(2).replace('.', ',');
          } else {
            priceEl.innerHTML = '&euro;' + parseFloat(matchedVariation.display_price).toFixed(2).replace('.', ',');
          }
        }
      }
    } else {
      if (addBtn) {
        addBtn.disabled = false;
        addBtn.style.opacity = '1';
        addBtn.classList.remove('disabled', 'wc-variation-is-unavailable');
      }
    }
  }

  // ─── AUTO-SELECT SINGLE OPTION VARIANTS ──────────────────────
  // If an attribute group has only ONE option, auto-select it on page load
  // so users don't have to click it manually before adding to cart.
  function pwAutoSelectSingleVariants() {
    var attrGroups = {};
    document.querySelectorAll('.variant-btn').forEach(function(b) {
      if (!attrGroups[b.dataset.attr]) attrGroups[b.dataset.attr] = [];
      attrGroups[b.dataset.attr].push(b);
    });
    var autoSelected = false;
    Object.keys(attrGroups).forEach(function(attr) {
      if (attrGroups[attr].length === 1) {
        attrGroups[attr][0].classList.add('selected');
        autoSelected = true;
      }
    });
    if (autoSelected) pwResolveVariation();
  }
  window.pwAutoSelectSingleVariants = pwAutoSelectSingleVariants;

  // ─── IMAGE GALLERY & ZOOM / LIGHTBOX SYSTEM ────────────────
  window.pwCurrentGalleryIndex = 0;

  window.pwGetGalleryItems = function() {
    var items = [];
    var thumbs = document.querySelectorAll('.pd-thumb');
    if (thumbs.length > 0) {
      thumbs.forEach(function(t, idx) {
        items.push({
          single: t.dataset.src || '',
          full: t.dataset.full || t.dataset.src || '',
          srcset: t.dataset.srcset || '',
          sizes: t.dataset.sizes || ''
        });
      });
    } else {
      var mainImg = document.querySelector('.pd-img-el');
      if (mainImg) {
        items.push({
          single: mainImg.src,
          full: mainImg.dataset.full || mainImg.src,
          srcset: mainImg.srcset || '',
          sizes: mainImg.sizes || ''
        });
      }
    }
    return items;
  };

  window.pwSwitchImage = function(thumb, index) {
    if (!thumb) return;
    var src = thumb.dataset.src;
    var full = thumb.dataset.full || src;
    var srcset = thumb.dataset.srcset || '';
    var sizes = thumb.dataset.sizes || '';
    if (!src) return;

    if (typeof index === 'number') {
      window.pwCurrentGalleryIndex = index;
    } else if (thumb.dataset.index) {
      window.pwCurrentGalleryIndex = parseInt(thumb.dataset.index, 10) || 0;
    }

    var mainImg = document.querySelector('.pd-img-el');
    var mainWrap = document.getElementById('pd-main-img-wrap');
    if (mainImg) {
      mainImg.style.opacity = '0';
      setTimeout(function() {
        if (srcset) {
          mainImg.srcset = srcset;
          if (sizes) mainImg.sizes = sizes;
        } else {
          mainImg.removeAttribute('srcset');
          mainImg.removeAttribute('sizes');
        }
        mainImg.src = src;
        mainImg.dataset.full = full;
        if (mainWrap) mainWrap.dataset.full = full;
        mainImg.style.opacity = '1';
      }, 120);
    }

    document.querySelectorAll('.pd-thumb').forEach(function(t) { t.classList.remove('active'); });
    thumb.classList.add('active');
  };

  // Hover Pan Zoom on Desktop
  function pwInitProductZoom() {
    var wrap = document.getElementById('pd-main-img-wrap');
    var img = document.getElementById('pd-main-img');
    if (!wrap || !img) return;

    function handleMouseMove(e) {
      if (window.innerWidth <= 768) return; // disable hover zoom on touch devices
      var rect = wrap.getBoundingClientRect();
      var x = ((e.clientX - rect.left) / rect.width) * 100;
      var y = ((e.clientY - rect.top) / rect.height) * 100;
      x = Math.max(0, Math.min(100, x));
      y = Math.max(0, Math.min(100, y));

      img.style.transformOrigin = x + '% ' + y + '%';
      img.style.transform = 'scale(2.2)';
      wrap.classList.add('is-zoomed');
    }

    function handleMouseLeave() {
      img.style.transform = 'scale(1)';
      img.style.transformOrigin = 'center center';
      wrap.classList.remove('is-zoomed');
    }

    wrap.removeEventListener('mousemove', wrap._pwZoomMove);
    wrap.removeEventListener('mouseleave', wrap._pwZoomLeave);

    wrap._pwZoomMove = handleMouseMove;
    wrap._pwZoomLeave = handleMouseLeave;

    wrap.addEventListener('mousemove', handleMouseMove);
    wrap.addEventListener('mouseleave', handleMouseLeave);
  }
  window.pwInitProductZoom = pwInitProductZoom;

  // ─── FULLSCREEN LIGHTBOX MODAL ──────────────────────────────
  var pwLightboxEl = null;
  var pwLightboxZoomed = false;

  function pwCreateLightbox() {
    if (document.getElementById('pw-lightbox')) {
      pwLightboxEl = document.getElementById('pw-lightbox');
      return;
    }

    var html = '<div id="pw-lightbox" class="pd-lightbox" role="dialog" aria-modal="true" aria-label="Afbeelding vergroten" style="display:none;">' +
      '<div class="pd-lightbox-overlay"></div>' +
      '<div class="pd-lightbox-top">' +
        '<div class="pd-lightbox-counter" id="pw-lightbox-counter">1 / 1</div>' +
        '<div class="pd-lightbox-actions">' +
          '<button type="button" class="pd-lightbox-btn" id="pw-lightbox-zoom-btn" title="Zoom in / out" aria-label="Zoom">' +
            '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>' +
          '</button>' +
          '<button type="button" class="pd-lightbox-btn" id="pw-lightbox-close" title="Sluiten (Esc)" aria-label="Sluiten">' +
            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>' +
          '</button>' +
        '</div>' +
      '</div>' +
      '<div class="pd-lightbox-body">' +
        '<button type="button" class="pd-lightbox-nav prev" id="pw-lightbox-prev" aria-label="Vorige afbeelding">‹</button>' +
        '<div class="pd-lightbox-img-wrap" id="pw-lightbox-img-wrap">' +
          '<img src="" alt="" class="pd-lightbox-img" id="pw-lightbox-img" />' +
        '</div>' +
        '<button type="button" class="pd-lightbox-nav next" id="pw-lightbox-next" aria-label="Volgende afbeelding">›</button>' +
      '</div>' +
    '</div>';

    document.body.insertAdjacentHTML('beforeend', html);
    pwLightboxEl = document.getElementById('pw-lightbox');

    // Event listeners
    document.getElementById('pw-lightbox-close').addEventListener('click', pwCloseLightbox);
    pwLightboxEl.querySelector('.pd-lightbox-overlay').addEventListener('click', pwCloseLightbox);
    document.getElementById('pw-lightbox-prev').addEventListener('click', function(e) {
      e.stopPropagation();
      pwLightboxNav(-1);
    });
    document.getElementById('pw-lightbox-next').addEventListener('click', function(e) {
      e.stopPropagation();
      pwLightboxNav(1);
    });
    document.getElementById('pw-lightbox-zoom-btn').addEventListener('click', function(e) {
      e.stopPropagation();
      pwToggleLightboxZoom();
    });

    var lbImg = document.getElementById('pw-lightbox-img');
    lbImg.addEventListener('click', function(e) {
      e.stopPropagation();
      pwToggleLightboxZoom();
    });

    document.addEventListener('keydown', function(e) {
      if (!pwLightboxEl || pwLightboxEl.style.display === 'none') return;
      if (e.key === 'Escape') pwCloseLightbox();
      if (e.key === 'ArrowLeft') pwLightboxNav(-1);
      if (e.key === 'ArrowRight') pwLightboxNav(1);
    });
  }

  window.pwOpenLightbox = function(index) {
    pwCreateLightbox();
    var items = pwGetGalleryItems();
    if (items.length === 0) return;

    var idx = typeof index === 'number' ? index : (window.pwCurrentGalleryIndex || 0);
    if (idx < 0) idx = 0;
    if (idx >= items.length) idx = items.length - 1;
    window.pwCurrentGalleryIndex = idx;

    pwUpdateLightboxView();
    pwLightboxEl.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(function() {
      pwLightboxEl.classList.add('active');
    });
  };

  window.pwCloseLightbox = function() {
    if (!pwLightboxEl) return;
    pwLightboxEl.classList.remove('active');
    pwLightboxZoomed = false;
    var img = document.getElementById('pw-lightbox-img');
    if (img) {
      img.classList.remove('zoomed');
      img.style.transform = '';
    }
    setTimeout(function() {
      pwLightboxEl.style.display = 'none';
      document.body.style.overflow = '';
    }, 200);
  };

  function pwLightboxNav(delta) {
    var items = pwGetGalleryItems();
    if (items.length <= 1) return;
    var nextIdx = window.pwCurrentGalleryIndex + delta;
    if (nextIdx < 0) nextIdx = items.length - 1;
    if (nextIdx >= items.length) nextIdx = 0;
    window.pwCurrentGalleryIndex = nextIdx;

    // Also sync the main gallery on the page
    var thumbs = document.querySelectorAll('.pd-thumb');
    if (thumbs[nextIdx]) {
      pwSwitchImage(thumbs[nextIdx], nextIdx);
    }
    pwUpdateLightboxView();
  }

  function pwToggleLightboxZoom() {
    var img = document.getElementById('pw-lightbox-img');
    if (!img) return;
    pwLightboxZoomed = !pwLightboxZoomed;
    img.classList.toggle('zoomed', pwLightboxZoomed);
  }

  function pwUpdateLightboxView() {
    var items = pwGetGalleryItems();
    var idx = window.pwCurrentGalleryIndex || 0;
    var item = items[idx] || items[0];
    if (!item) return;

    var img = document.getElementById('pw-lightbox-img');
    var counter = document.getElementById('pw-lightbox-counter');
    var prevBtn = document.getElementById('pw-lightbox-prev');
    var nextBtn = document.getElementById('pw-lightbox-next');

    pwLightboxZoomed = false;
    if (img) {
      img.classList.remove('zoomed');
      img.src = item.full || item.single;
    }
    if (counter) {
      counter.textContent = (idx + 1) + ' / ' + items.length;
    }
    if (prevBtn && nextBtn) {
      var showNav = items.length > 1;
      prevBtn.style.display = showNav ? 'flex' : 'none';
      nextBtn.style.display = showNav ? 'flex' : 'none';
    }
  }

  // Sync variations with gallery
  $(document).on('found_variation', function(event, variation) {
    if (variation && variation.image && variation.image.src) {
      var mainImg = document.querySelector('.pd-img-el');
      var mainWrap = document.getElementById('pd-main-img-wrap');
      if (mainImg) {
        mainImg.style.opacity = '0';
        setTimeout(function() {
          if (variation.image.srcset) {
            mainImg.srcset = variation.image.srcset;
            if (variation.image.sizes) mainImg.sizes = variation.image.sizes;
          } else {
            mainImg.removeAttribute('srcset');
            mainImg.removeAttribute('sizes');
          }
          mainImg.src = variation.image.src;
          mainImg.dataset.full = variation.image.full_src || variation.image.src;
          if (mainWrap) mainWrap.dataset.full = variation.image.full_src || variation.image.src;
          mainImg.style.opacity = '1';
        }, 120);
      }
    }
  });

  // ─── PRODUCT TABS ────────────────────────────────────────────
  document.addEventListener('click', function(e) {
    const tabBtn = e.target.closest('.pw-tab-btn');
    if (!tabBtn) return;
    const tabId = tabBtn.dataset.tab;
    document.querySelectorAll('.pw-tab-btn').forEach(function(b) { b.classList.remove('active'); });
    document.querySelectorAll('.pw-tab-content').forEach(function(c) { c.classList.remove('active'); });
    tabBtn.classList.add('active');
    const content = document.getElementById('tab-' + tabId);
    if (content) content.classList.add('active');
  });

  // ─── CART QTY (inline) ──────────────────────────────────────
  window.pwCartQtyChange = function(btn, delta) {
    const row = btn.closest('.cart-item-row');
    const input = row ? row.querySelector('.cart-qty-input') : null;
    if (!input) return;
    const val = parseInt(input.value) || 1;
    const next = Math.max(0, val + delta);
    input.value = next;
    // Submit the form to update
    const form = document.getElementById('pw-cart-form');
    if (form) form.submit();
  };

  // ─── COUPON ──────────────────────────────────────────────────
  window.pwApplyCoupon = function() {
    const input = document.querySelector('#pw-coupon-code, .pw-coupon-input input, input[name="coupon_code"]');
    const msg   = document.getElementById('pw-coupon-msg');
    if (!input || !input.value.trim()) return;

    const code = input.value.trim();
    if (msg) { msg.textContent = 'Bezig…'; msg.style.color = 'var(--muted)'; }

    $.ajax({
      url: pw_vars.ajax_url,
      method: 'POST',
      data: {
        action: 'woocommerce_apply_coupon',
        security: pw_vars.nonce,
        coupon_code: code
      },
      success: function(res) {
        if (msg) {
          if (res.indexOf('error') !== -1 || res.indexOf('Error') !== -1) {
            msg.textContent = 'Ongeldige code.';
            msg.style.color = '#ff6b6b';
          } else {
            msg.textContent = '✓ Kortingscode toegepast!';
            msg.style.color = 'var(--accent)';
            setTimeout(function() { location.reload(); }, 1000);
          }
        }
      },
      error: function() {
        if (msg) { msg.textContent = 'Fout. Probeer opnieuw.'; msg.style.color = '#ff6b6b'; }
      }
    });
  };

  // ─── MARQUEE PAUSE ON HOVER ──────────────────────────────────
  const marqueeInner = document.getElementById('marquee-inner');
  if (marqueeInner) {
    marqueeInner.addEventListener('mouseenter', function() {
      this.style.animationPlayState = 'paused';
    });
    marqueeInner.addEventListener('mouseleave', function() {
      this.style.animationPlayState = 'running';
    });
  }

  // ─── ENTRANCE ANIMATIONS ────────────────────────────────────
  function pwFadeIn() {
    const observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('pw-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08 });

    document.querySelectorAll('.product-card, .cat-card, .testimonial, .trust-item, .banner > div').forEach(function(el) {
      el.classList.add('pw-fade');
      observer.observe(el);
    });
  }
  window.pwFadeIn = pwFadeIn;

  // Add fade CSS dynamically
  const fadeStyle = document.createElement('style');
  fadeStyle.textContent = `
    .pw-fade { opacity: 0; transform: translateY(18px); transition: opacity .45s ease, transform .45s ease; }
    .pw-visible { opacity: 1; transform: none; }
  `;
  document.head.appendChild(fadeStyle);

  // ─── WC FRAGMENT REFRESH HOOK ─────────────────────────────────
  $(document.body).on('wc_fragments_refreshed', function() {
    // Badge already updated via AJAX response. Only re-translate if the
    // visitor isn't on the default language — avoids an unnecessary
    // full-page DOM walk on every fragment refresh.
    var lang = window.pwCurrentLanguage ? window.pwCurrentLanguage() : 'nl';
    if (lang !== 'nl' && typeof window.pwApplyLanguage === 'function') {
      window.pwApplyLanguage(lang);
    }
  });

  // ─── CHECKOUT: SHIPPING TOGGLE ───────────────────────────────
  const shipToggle = document.getElementById('ship-to-different-address');
  if (shipToggle) {
    shipToggle.addEventListener('change', function() {
      const section = document.getElementById('shipping-address-section');
      if (section) section.style.display = this.checked ? 'block' : 'none';
    });
  }

  // ─── LANGUAGE SWITCHER ───────────────────────────────────────
  // All translatable strings: EN and NL
  var pwTranslations = {
    en: {
      // Nav links
      'Home':        'Home',
      'Shop':        'Shop',
      'TIGWARE':     'TIGWARE',
      'Over ons':    'About us',
      'Contact':     'Contact',
      // Cart / buttons
      'Winkelwagen': 'Cart',
      'Mijn account':'My Account',
      // Marquee
      'GRATIS VERZENDING BOVEN €50': 'FREE SHIPPING ABOVE €50',
      'TIGWARE PREMIUM ONDERDELEN':  'TIGWARE PREMIUM PARTS',
      'SNELLE LEVERING':             'FAST DELIVERY',
      'DOOR LASSERS VOOR LASSERS':   'BY WELDERS FOR WELDERS',
      '14 DAGEN RETOURRECHT':        '14-DAY RETURNS',
      'FREE SHIPPING ABOVE €50':     'FREE SHIPPING ABOVE €50',
      'PREMIUM TIG CONSUMABLES':     'PREMIUM TIG CONSUMABLES',
      'FAST DELIVERY':               'FAST DELIVERY',
      // Product
      'In Winkelwagen':              'Add to Cart',
      '+ In Winkelwagen':            '+ Add to Cart',
      'Uitverkocht':                 'Out of Stock',
      'UITVERKOCHT':                 'OUT OF STOCK',
      'Toegevoegd aan winkelwagen!': 'Added to cart!',
      'Terug naar winkel':           'Back to shop',
      // Side cart
      'Uw Winkelwagen':              'Your Cart',
      'Uw winkelwagen is leeg.':     'Your cart is empty.',
      'Verder Winkelen':             'Continue Shopping',
      'Subtotaal':                   'Subtotal',
      'Winkelwagen bekijken →':      'View Cart →',
      'Afrekenen':                   'Checkout',
      'Verwijderen':                 'Remove',
    },
    nl: {
      'Home':        'Home',
      'Shop':        'Shop',
      'TIGWARE':     'TIGWARE',
      'About us':    'Over ons',
      'Contact':     'Contact',
      'Cart':        'Winkelwagen',
      'My Account':  'Mijn account',
      'FREE SHIPPING ABOVE €50':    'GRATIS VERZENDING BOVEN €50',
      'TIGWARE PREMIUM PARTS':      'TIGWARE PREMIUM ONDERDELEN',
      'FAST DELIVERY':              'SNELLE LEVERING',
      'BY WELDERS FOR WELDERS':     'DOOR LASSERS VOOR LASSERS',
      '14-DAY RETURNS':             '14 DAGEN RETOURRECHT',
      'PREMIUM TIG CONSUMABLES':    'PREMIUM TIG CONSUMABLES',
      'Add to Cart':                'In Winkelwagen',
      '+ Add to Cart':              '+ In Winkelwagen',
      'Out of Stock':               'Uitverkocht',
      'OUT OF STOCK':               'UITVERKOCHT',
      'Added to cart!':             'Toegevoegd aan winkelwagen!',
      'Back to shop':               'Terug naar winkel',
      'Your Cart':                  'Uw Winkelwagen',
      'Your cart is empty.':        'Uw winkelwagen is leeg.',
      'Continue Shopping':          'Verder Winkelen',
      'Subtotal':                   'Subtotaal',
      'View Cart →':                'Winkelwagen bekijken →',
      'Checkout':                   'Afrekenen',
      'Remove':                     'Verwijderen',
    }
  };

  [
    ['Over ons', 'About us'],
    ['Winkelwagen', 'Cart'],
    ['Mijn account', 'My Account'],
    ['GRATIS VERZENDING BOVEN €50', 'FREE SHIPPING ABOVE €50'],
    ['GRATIS VERZENDING BOVEN â‚¬50', 'FREE SHIPPING ABOVE â‚¬50'],
    ['TIGWARE PREMIUM ONDERDELEN', 'TIGWARE PREMIUM PARTS'],
    ['SNELLE LEVERING', 'FAST DELIVERY'],
    ['DOOR LASSERS VOOR LASSERS', 'BY WELDERS FOR WELDERS'],
    ['14 DAGEN RETOURRECHT', '14-DAY RETURNS'],
    ['Premium TIG Onderdelen', 'Premium TIG Parts'],
    ['LAS PERFECT. ELKE KEER.', 'PERFECT WELD EVERY TIME'],
    ['Professionele TIG-laskoppes, diffusors, back caps en handschoenen. Getest door lassers, voor lassers.', 'Professional TIG torch cups, diffusers, back caps and gloves. Tested by welders, for welders.'],
    ['Shop nu →', 'Shop now →'],
    ['Shop nu â†’', 'Shop now â†’'],
    ['Ontdek TIGWARE', 'Discover TIGWARE'],
    ['Producten', 'Products'],
    ['Klanten', 'Customers'],
    ['Beoordeling', 'Rating'],
    ['CATEGORIEËN', 'CATEGORIES'],
    ['CATEGORIEÃ‹N', 'CATEGORIES'],
    ['Bekijk alles →', 'View all →'],
    ['Bekijk alles â†’', 'View all â†’'],
    ['Alle producten →', 'All products →'],
    ['Alle producten â†’', 'All products â†’'],
    ['Alle producten', 'All products'],
    ['producten', 'products'],
    ['Handschoenen', 'Gloves'],
    ['+ WINKELWAGEN', '+ CART'],
    ['+ In Winkelwagen', '+ Add to Cart'],
    ['In Winkelwagen', 'Add to Cart'],
    ['Toevoegen aan winkelwagen', 'Add to cart'],
    ['Toevoegen…', 'Adding...'],
    ['Toevoegenâ€¦', 'Adding...'],
    ['✓ Toegevoegd!', '✓ Added!'],
    ['âœ“ Toegevoegd!', 'âœ“ Added!'],
    ['Toegevoegd aan winkelwagen!', 'Added to cart!'],
    ['Product toegevoegd aan winkelwagen', 'Product added to cart'],
    ['Selecteer alle opties eerst.', 'Please select all options first.'],
    ['SELECTEER', 'SELECT'],
    ['Uitverkocht', 'Out of Stock'],
    ['UITVERKOCHT', 'OUT OF STOCK'],
    ['Terug naar shop', 'Back to shop'],
    ['Terug naar winkel', 'Back to shop'],
    ['Excl. BTW — Gratis verzending boven €50', 'Excl. VAT — Free shipping over €50'],
    ['Excl. BTW — Gratis verzending boven €50', 'Excl. VAT — Free shipping over €50'],
    ['Kies een optie', 'Choose an option'],
    ['Op voorraad', 'In stock'],
    ['Gratis verzending boven €50', 'Free shipping over €50'],
    ['Specificaties', 'Specifications'],
    ['Beschrijving', 'Description'],
    ['Gewicht', 'Weight'],
    ['Categorie', 'Category'],
    ['Prijs', 'Price'],
    ['Merk', 'Brand'],
    ['Alles', 'All'],
    ['Filters wissen', 'Clear filters'],
    ['LEEG', 'EMPTY'],
    ['Geen producten gevonden voor deze selectie.', 'No products found for this selection.'],
    ['Bekijk alle producten', 'View all products'],
    ['Stap 1 van 2', 'Step 1 of 2'],
    ['WINKELWAGEN', 'CART'],
    ['1. Winkelwagen', '1. Cart'],
    ['2. Gegevens', '2. Details'],
    ['3. Betaling', '3. Payment'],
    ['Je winkelwagen is nog leeg.', 'Your cart is still empty.'],
    ['Ga naar shop →', 'Go to shop →'],
    ['Ga naar shop â†’', 'Go to shop â†’'],
    ['Winkelwagen bijwerken', 'Update cart'],
    ['KORTINGSCODE', 'DISCOUNT CODE'],
    ['Toepassen', 'Apply'],
    ['Verder winkelen', 'Continue shopping'],
    ['Doorgaan naar gegevens →', 'Continue to details →'],
    ['Doorgaan naar gegevens â†’', 'Continue to details â†’'],
    ['BESTELOVERZICHT', 'ORDER SUMMARY'],
    ['Subtotaal excl. BTW', 'Subtotal excl. VAT'],
    ['BTW (21%)', 'VAT (21%)'],
    ['Verzendkosten', 'Shipping'],
    ['Gratis', 'Free'],
    ['Korting', 'Discount'],
    ['TOTAAL INCL. BTW', 'TOTAL INCL. VAT'],
    ['tot gratis verzending!', 'to free shipping!'],
    ['Je hebt gratis verzending!', 'You have free shipping!'],
    ['Nieuwe producten, tips en exclusieve aanbiedingen. Geen spam.', 'New products, tips and exclusive offers. No spam.'],
    ['STAY UPDATED', 'Subscribe'],
    ['\u00a9 2026 PerfectWelding \u2014 Alle rechten voorbehouden', '\u00a9 2026 PerfectWelding \u2014 All rights reserved'],
    ['Premium TIG-onderdelen voor professionals en hobbyisten. Door lassers, voor lassers.', 'Premium TIG parts for professionals and hobbyists. By welders, for welders.'],
    ['INFORMATIE', 'INFORMATION'],
    ['Verzending', 'Shipping'],
    ['Retourneren', 'Returns'],
    ['JURIDISCH', 'LEGAL'],
    ['Algemene voorwaarden', 'Terms and conditions'],
    ['Privacybeleid', 'Privacy policy'],
    ['Cookiebeleid', 'Cookie policy'],
    ['Shop nu →', 'Shop now →'],
    ['Ontdek TIGWARE', 'Discover TIGWARE'],
    ['Uw Winkelwagen', 'Your Cart'],
    ['Uw winkelwagen is leeg.', 'Your cart is empty.'],
    ['Verder Winkelen', 'Continue Shopping'],
    ['Winkelwagen bekijken →', 'View Cart →'],
    ['Winkelwagen bekijken â†’', 'View Cart â†’'],
    ['Afrekenen', 'Checkout'],
    ['Subtotaal', 'Subtotal'],
    ['Verwijderen', 'Remove'],
    ['Winkelwagen sluiten', 'Close cart'],
    ['Perfectwelding is ons eigen premium merk. Elk product is ontworpen voor maximale gasdekking, langere levensduur en een betere lasresultaat. Van teflon back caps tot pyrex cups.', 'Perfectwelding is our own premium brand. Every product is designed for maximum gas coverage, longer service life and better weld results. From teflon back caps to pyrex cups.'],
    ['Drievoudig diffusorsysteem voor maximale gasdekking', 'Triple diffuser system for maximum gas coverage'],
    ['Pyrex borosilicaat glas — onbreekbaar en hittebestendig', 'Pyrex borosilicate glass — shatter-resistant and heat-resistant'],
    ['Pyrex borosilicaat glas â€” onbreekbaar en hittebestendig', 'Pyrex borosilicate glass â€” shatter-resistant and heat-resistant'],
    ['Getest op RVS, titanium en aluminium', 'Tested on stainless steel, titanium and aluminium'],
    ['Compatibel met WP17, WP18 en WP26 toortsen', 'Compatible with WP17, WP18 and WP26 torches'],
    ['Snelle levering', 'Fast delivery'],
    ['Besteld voor 15:00, morgen in huis in Nederland.', 'Ordered before 15:00, delivered tomorrow in the Netherlands.'],
    ['14 dagen retour', '14-day returns'],
    ['Niet tevreden? Stuur het gewoon terug.', 'Not satisfied? Simply send it back.'],
    ['Kwaliteitsgarantie', 'Quality guarantee'],
    ['Beste kwaliteit in zijn klasse.', 'Best quality in its class.'],
    ['Door lassers, voor lassers.', 'By welders, for welders.'],
    ['WAT LASSERS ZEGGEN', 'WHAT WELDERS SAY'],
    ['jouw@email.nl', 'your@email.com'],
    ['Verwijder', 'Remove']
  ].forEach(function(pair) {
    pwTranslations.en[pair[0]] = pair[1];
    pwTranslations.nl[pair[1]] = pair[0];
  });

  function pwGetCookie(name) {
    var match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : null;
  }

  function pwSetCookie(name, value, days) {
    var d = new Date();
    d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + d.toUTCString() + '; path=/';
  }

  function pwApplyLanguageLegacy(lang) {
    var trans = pwTranslations[lang];
    if (!trans) return;

    // Translate nav links
    document.querySelectorAll('.nav-links a, .mobile-nav a').forEach(function(el) {
      var txt = el.textContent.trim();
      if (trans[txt]) el.textContent = trans[txt];
    });

    // Translate marquee items
    document.querySelectorAll('.marquee-item').forEach(function(el) {
      var txt = el.textContent.replace('✦ ', '').trim();
      if (trans[txt]) el.textContent = '✦ ' + trans[txt];
    });

    // Translate any product buttons visible on page
    document.querySelectorAll('.product-add, .pw-add-to-cart').forEach(function(el) {
      var txt = el.textContent.trim();
      if (trans[txt]) el.textContent = trans[txt];
    });

    // Translate side cart labels (elements with data-pw-i18n store key as original EN text)
    document.querySelectorAll('[data-pw-i18n]').forEach(function(el) {
      var key = el.getAttribute('data-pw-i18n');
      // Look up in current lang translations OR fall back to the key itself if EN
      var translated = trans[key];
      if (translated) {
        // Handle elements with formatting tags like <em> and <br>
        if (el.children.length > 0) {
          // For hero title and similar - replace entire content with translated text
          el.textContent = translated;
        } else if (el.children.length === 0) {
          // Only replace text nodes, not elements that have child SVGs etc.
          el.childNodes.forEach(function(node) {
            if (node.nodeType === 3 && node.textContent.trim()) {
              node.textContent = translated + ' ';
            }
          });
        }
      }
    });

    // Update html lang attribute
    document.documentElement.lang = lang === 'nl' ? 'nl-NL' : 'en-US';

    // Update active button state
    document.querySelectorAll('.pw-lang-btn').forEach(function(btn) {
      btn.classList.toggle('active', btn.dataset.lang === lang);
    });
  }

  function pwTranslateText(text, lang) {
    var trans = pwTranslations[lang] || {};
    var value = (text || '').replace(/\s+/g, ' ').trim();
    if (!value) return text;
    if (trans[value]) return trans[value];

    var productMatch = value.match(/^(\d+)\s+(producten|products)$/i);
    if (productMatch) return productMatch[1] + ' ' + (lang === 'en' ? 'products' : 'producten');

    var cartMatch = value.match(/^(Winkelwagen|Cart)\s*\((\d+)\)$/i);
    if (cartMatch) return (lang === 'en' ? 'Cart' : 'Winkelwagen') + ' (' + cartMatch[2] + ')';

    var freeShipMatch = value.match(/^(Nog|Only)\s+(.+?)\s+(tot gratis verzending!|to free shipping!)$/i);
    if (freeShipMatch) return lang === 'en' ? 'Only ' + freeShipMatch[2] + ' to free shipping!' : 'Nog ' + freeShipMatch[2] + ' tot gratis verzending!';

    var discountMatch = value.match(/^(Korting|Discount)\s+\((.+)\)$/i);
    if (discountMatch) return (lang === 'en' ? 'Discount' : 'Korting') + ' (' + discountMatch[2] + ')';

    if (/^(✓|âœ“)\s*(Je hebt gratis verzending!|You have free shipping!)$/i.test(value)) {
      return (value.indexOf('âœ“') === 0 ? 'âœ“ ' : '✓ ') + (lang === 'en' ? 'You have free shipping!' : 'Je hebt gratis verzending!');
    }

    return text;
  }

  function pwTranslateElementText(el, lang) {
    if (!el || el.closest('script, style, svg, noscript')) return;
    if (el.matches('input, textarea')) return;
    var hasElementChild = Array.prototype.some.call(el.childNodes, function(node) {
      return node.nodeType === 1 && !node.matches('br, svg');
    });
    if (hasElementChild) return;

    var hasIconChild = Array.prototype.some.call(el.childNodes, function(node) {
      return node.nodeType === 1 && node.matches('svg');
    });
    if (hasIconChild) {
      Array.prototype.forEach.call(el.childNodes, function(node) {
        if (node.nodeType !== 3) return;
        var translatedNode = pwTranslateText(node.textContent, lang);
        if (translatedNode !== node.textContent) node.textContent = ' ' + translatedNode;
      });
      return;
    }

    var original = el.textContent;
    var translated = pwTranslateText(original, lang);
    if (translated !== original) el.textContent = translated;
  }

  function pwTranslateAttributes(lang) {
    document.querySelectorAll('[placeholder]').forEach(function(el) {
      var translated = pwTranslateText(el.getAttribute('placeholder'), lang);
      if (translated) el.setAttribute('placeholder', translated);
    });

    ['title', 'aria-label'].forEach(function(attr) {
      document.querySelectorAll('[' + attr + ']').forEach(function(el) {
        var translated = pwTranslateText(el.getAttribute(attr), lang);
        if (translated) el.setAttribute(attr, translated);
      });
    });
  }

  function pwUpdateRuntimeMessages(lang) {
    if (typeof pw_cart_vars !== 'undefined' && pw_cart_vars.i18n) {
      pw_cart_vars.i18n.add_to_cart = lang === 'en' ? 'Add to Cart' : 'In Winkelwagen';
      pw_cart_vars.i18n.added = lang === 'en' ? 'Product added to cart!' : 'Product toegevoegd aan winkelwagen!';
      pw_cart_vars.i18n.added_short = lang === 'en' ? 'Added!' : 'Toegevoegd!';
      pw_cart_vars.i18n.view_cart = lang === 'en' ? 'View Cart' : 'Winkelwagen bekijken';
      pw_cart_vars.i18n.checkout = lang === 'en' ? 'Checkout' : 'Afrekenen';
      pw_cart_vars.i18n.cart_empty = lang === 'en' ? 'Your cart is empty.' : 'Uw winkelwagen is leeg.';
      pw_cart_vars.i18n.continue = lang === 'en' ? 'Continue Shopping' : 'Verder Winkelen';
      pw_cart_vars.i18n.subtotal = lang === 'en' ? 'Subtotal' : 'Subtotaal';
      pw_cart_vars.i18n.remove = lang === 'en' ? 'Remove' : 'Verwijderen';
      pw_cart_vars.i18n.quantity = lang === 'en' ? 'Quantity' : 'Aantal';
      pw_cart_vars.i18n.select_options = lang === 'en' ? 'Please select all options first.' : 'Selecteer alle opties eerst.';
      pw_cart_vars.i18n.error = lang === 'en' ? 'Something went wrong. Please try again.' : 'Er ging iets mis. Probeer opnieuw.';
    }
  }

  function pwApplyLanguage(lang) {
    if (!pwTranslations[lang]) return;

    pwUpdateRuntimeMessages(lang);

    document.querySelectorAll('[data-pw-i18n]').forEach(function(el) {
      var html = el.getAttribute('data-pw-i18n-html-' + lang);
      if (html) {
        el.innerHTML = html;
        return;
      }

      var key = el.getAttribute('data-pw-i18n');
      var translated = pwTranslateText(key, lang);
      if (translated) el.textContent = translated;
    });

    document.querySelectorAll('body *').forEach(function(el) {
      pwTranslateElementText(el, lang);
    });

    pwTranslateAttributes(lang);
    document.documentElement.lang = lang === 'nl' ? 'nl-NL' : 'en-US';

    document.querySelectorAll('.pw-lang-btn').forEach(function(btn) {
      btn.classList.toggle('active', btn.dataset.lang === lang);
    });

    document.dispatchEvent(new CustomEvent('pw_language_changed', { detail: { lang: lang } }));
  }

  // Lightweight variant of pwApplyLanguage that only walks a small
  // subtree (e.g. the side-cart panel after it re-renders) instead of
  // the entire document body. Re-running the full-page translation walk
  // on every add-to-cart / qty-update / remove was showing up as a
  // repeated long main-thread task — this keeps cart translation working
  // without re-scanning the whole page each time.
  function pwTranslateScope(root, lang) {
    if (!root || !pwTranslations[lang]) return;

    root.querySelectorAll('[data-pw-i18n]').forEach(function(el) {
      var html = el.getAttribute('data-pw-i18n-html-' + lang);
      if (html) {
        el.innerHTML = html;
        return;
      }
      var key = el.getAttribute('data-pw-i18n');
      var translated = pwTranslateText(key, lang);
      if (translated) el.textContent = translated;
    });

    root.querySelectorAll('*').forEach(function(el) {
      pwTranslateElementText(el, lang);
    });
  }
  window.pwTranslateScope = pwTranslateScope;

  window.pwApplyLanguage = pwApplyLanguage;
  window.pwCurrentLanguage = function() {
    return pwGetCookie('pw_language') || 'nl';
  };

  // Init language switcher
  document.addEventListener('DOMContentLoaded', function() {
    // There are two switcher instances in the markup — one in the desktop
    // nav (has id="pw-lang-switcher") and one inside the mobile drawer
    // (no id, see .mobile-nav-lang). Bail out only if NEITHER exists.
    var anySwitcher = document.querySelector('.pw-lang-switcher');
    if (!anySwitcher) return;

    var savedLang = pwGetCookie('pw_language') || 'nl';

    // Site content is authored in Dutch by default, so on the (by far
    // most common) case where the visitor's language is already 'nl',
    // there is nothing to translate — skip the full-page DOM walk
    // (document.querySelectorAll('body *') + per-element text checks)
    // entirely. This was running unconditionally on EVERY page load and
    // showed up as a long main-thread task in PageSpeed Insights. We
    // still set the correct active button + lang attribute, which is
    // effectively free.
    if (savedLang === 'nl') {
      document.documentElement.lang = 'nl-NL';
      document.querySelectorAll('.pw-lang-btn').forEach(function(btn) {
        btn.classList.toggle('active', btn.dataset.lang === 'nl');
      });
    } else {
      pwApplyLanguage(savedLang);
    }

    // Delegated on `document` (not bound to the desktop switcher's id)
    // so BOTH the desktop nav switcher AND the mobile-drawer switcher
    // work — previously only the desktop one (which has the id) had a
    // click listener attached, which is why phones had no working
    // language toggle even though the buttons were visible there.
    document.addEventListener('click', function(e) {
      var btn = e.target.closest('.pw-lang-btn');
      if (!btn) return;
      var lang = btn.dataset.lang;
      if (!lang) return;

      pwSetCookie('pw_language', lang, 365);
      pwApplyLanguage(lang);
    });
  });

  // ─── INIT (also re-run after every SPA navigation — see spa-nav.js) ──
  function pwInitPageContent() {
    pwFadeIn();

    // Smooth image transitions
    document.querySelectorAll('.pd-img-el').forEach(function(img) {
      img.style.transition = 'opacity .15s ease, transform .15s ease';
    });

    // Initialize product hover zoom
    pwInitProductZoom();

    // Auto-select single-option variant groups on page load
    pwAutoSelectSingleVariants();
  }
  window.pwInitPageContent = pwInitPageContent;

  document.addEventListener('DOMContentLoaded', pwInitPageContent);

})(jQuery);
