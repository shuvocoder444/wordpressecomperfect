/**
 * PerfectWelding — SPA-style page navigation (PJAX)
 * ─────────────────────────────────────────────────────────────────
 * Swaps only the <main id="pw-spa-content"> region via fetch() instead
 * of doing a full page reload, so browsing feels instant. Header, nav,
 * side-cart, and footer never get re-created.
 *
 * SAFETY RULES (do not relax these without careful testing):
 *   - Cart, checkout, my-account, wp-admin, wp-login are NEVER
 *     intercepted — those always do a normal full page load. This is
 *     deliberate: iDEAL/Mollie payment redirects and account/session
 *     handling must go through real page loads.
 *   - Any fetch failure falls back to a normal browser navigation —
 *     the visitor is never left on a broken/stuck page.
 *   - External links, downloads, new-tab links, and in-page anchors
 *     are left completely alone.
 */
(function () {
  'use strict';

  var CONTENT_ID = 'pw-spa-content';
  var contentEl = document.getElementById(CONTENT_ID);
  if (!contentEl) return; // Wrapper missing for some reason — do nothing, normal links still work.

  // ── Pages that must always be a real, full page load ─────────────
  var EXCLUDE_PATTERNS = [
    /\/cart\/?(\?|$)/i,
    /\/checkout\/?(\/|\?|$)/i,
    /\/my-account\/?(\/|\?|$)/i,
    /\/wp-admin/i,
    /\/wp-login\.php/i,
    /[?&]add-to-cart=/i,
    /[?&]wc-ajax=/i,
    /\.(pdf|zip|jpg|jpeg|png|gif|webp|svg|mp4|doc|docx|xls|xlsx)$/i
  ];

  function isExcludedUrl(pathAndQuery) {
    for (var i = 0; i < EXCLUDE_PATTERNS.length; i++) {
      if (EXCLUDE_PATTERNS[i].test(pathAndQuery)) return true;
    }
    return false;
  }

  function isEligibleLink(a) {
    if (!a || !a.getAttribute) return false;
    var rawHref = a.getAttribute('href');
    if (!rawHref || rawHref.charAt(0) === '#') return false;
    if (/^(mailto:|tel:|javascript:)/i.test(rawHref)) return false;
    if (a.hasAttribute('download')) return false;
    if (a.dataset && ('noSpa' in a.dataset)) return false;
    if (a.target && a.target !== '' && a.target !== '_self') return false;
    if (a.hasAttribute('rel') && /external/i.test(a.getAttribute('rel'))) return false;

    var url;
    try { url = new URL(a.href, window.location.href); } catch (e) { return false; }
    if (url.origin !== window.location.origin) return false;
    if (isExcludedUrl(url.pathname + url.search)) return false;

    return true;
  }

  /* ── Thin loading bar for feedback during fetch ────────────────── */
  var bar = null;
  function showBar() {
    if (!bar) {
      bar = document.createElement('div');
      bar.id = 'pw-spa-bar';
      bar.style.cssText = 'position:fixed;top:0;left:0;height:2px;width:0;background:var(--accent,#FF4D00);z-index:99999;transition:width .3s ease,opacity .2s ease;';
      document.body.appendChild(bar);
    }
    bar.style.opacity = '1';
    bar.style.width = '0';
    requestAnimationFrame(function () { bar.style.width = '65%'; });
  }
  function hideBar() {
    if (!bar) return;
    bar.style.width = '100%';
    setTimeout(function () {
      bar.style.opacity = '0';
      setTimeout(function () { bar.style.width = '0'; }, 200);
    }, 150);
  }

  /* ── Re-run the small set of init routines that target elements
     inside the swapped content. Everything else on this theme uses
     event delegation on `document`, so it keeps working automatically
     without any extra code. ─────────────────────────────────────── */
  function reinitContent() {
    if (typeof window.pwInitPageContent === 'function') {
      window.pwInitPageContent();
    }
    var lang = window.pwCurrentLanguage ? window.pwCurrentLanguage() : 'nl';
    if (lang !== 'nl' && typeof window.pwTranslateScope === 'function') {
      window.pwTranslateScope(contentEl, lang);
    }
  }

  var activeController = null;

  function navigate(url, addToHistory) {
    if (activeController) activeController.abort();
    activeController = ('AbortController' in window) ? new AbortController() : null;
    showBar();

    fetch(url, {
      credentials: 'same-origin',
      headers: { 'X-PW-SPA-Nav': '1' },
      signal: activeController ? activeController.signal : undefined
    })
      .then(function (res) {
        if (!res.ok) throw new Error('pw-spa: bad response status ' + res.status);
        return res.text().then(function (html) {
          return { html: html, finalUrl: res.url || url };
        });
      })
      .then(function (result) {
        var doc = new DOMParser().parseFromString(result.html, 'text/html');
        var newContent = doc.getElementById(CONTENT_ID);
        if (!newContent) throw new Error('pw-spa: no #' + CONTENT_ID + ' in response');

        // If the server redirected us somewhere excluded (e.g. login-wall,
        // checkout, account), don't render it inside the SPA shell —
        // do a real navigation there instead, same as a normal browser.
        var finalPath = '';
        try { finalPath = new URL(result.finalUrl, window.location.href).pathname + new URL(result.finalUrl, window.location.href).search; } catch (e) {}
        if (finalPath && isExcludedUrl(finalPath)) {
          window.location.href = result.finalUrl;
          return;
        }

        contentEl.innerHTML = newContent.innerHTML;
        document.title = doc.title || document.title;
        if (doc.body && doc.body.className) {
          document.body.className = doc.body.className;
        }

        // Replay the fade-in animation defined in main.css (CSS animations
        // don't auto-restart just from an innerHTML change).
        contentEl.style.animation = 'none';
        void contentEl.offsetHeight; // force reflow
        contentEl.style.animation = '';

        if (addToHistory) {
          window.history.pushState({ pwSpa: true }, '', result.finalUrl);
        }
        window.scrollTo(0, 0);
        reinitContent();
        document.dispatchEvent(new CustomEvent('pw_spa_navigated', { detail: { url: result.finalUrl } }));
      })
      .catch(function (err) {
        if (err && err.name === 'AbortError') return;
        // Never leave the visitor stuck — fall back to a real navigation.
        window.location.href = url;
      })
      .finally(function () {
        hideBar();
      });
  }

  document.addEventListener('click', function (e) {
    if (e.defaultPrevented || e.button !== 0) return;
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

    var a = e.target.closest('a');
    if (!isEligibleLink(a)) return;
    if (a.href === window.location.href) { e.preventDefault(); return; }

    e.preventDefault();
    navigate(a.href, true);
  });

  window.addEventListener('popstate', function () {
    navigate(window.location.href, false);
  });

  // Mark current entry so back-navigation to the very first page (no
  // pwSpa state) still works correctly.
  if (!window.history.state || !window.history.state.pwSpa) {
    window.history.replaceState({ pwSpa: true }, '', window.location.href);
  }
})();
