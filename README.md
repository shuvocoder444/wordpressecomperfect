# PerfectWelding WordPress Theme

## Installation

1. **Upload** de `perfectwelding` map naar `/wp-content/themes/`
2. Ga naar **Uiterlijk → Thema's** en activeer **PerfectWelding**
3. Zorg dat **WooCommerce** is geïnstalleerd en actief

---

## Vereisten

- WordPress 6.0+
- WooCommerce 8.0+
- PHP 8.0+
- Plugins aanbevolen:
  - WooCommerce
  - WooCommerce Payments of Mollie (iDEAL)
  - WooCommerce PDF Invoices (optioneel)

---

## WooCommerce Setup

### Pagina's (automatisch aangemaakt door WooCommerce)
- Shop
- Winkelwagen
- Afrekenen
- Mijn account
- Bestellingen volgen

### Aanbevolen instellingen
- **Winkel → Algemeen**: Valuta op Euro (€), land op Nederland
- **Winkel → Belasting**: BTW inschakelen, 21% standaard
- **Winkel → Verzending**: Gratis verzending boven €50 instellen
- **Betaalmethoden**: Mollie (iDEAL, Visa, Mastercard, PayPal) of WooCommerce Payments

---

## Productstructuur

### Categorieën aanmaken
Ga naar **Producten → Categorieën** en maak aan:
- `cups` — Cups
- `diffusers` — Diffusers
- `back-caps` — Back Caps
- `handschoenen` — Handschoenen
- `tigware` — TIGWARE (sub-merk)

### Custom Fields
Voeg toe via **Producten → Product bewerken → Custom Fields**:
- `_brand` → waarde: `PerfectWelding` of `TIGWARE`
- `_pw_specs` → JSON-string met specificaties (optioneel)

---

## Menu's

Ga naar **Uiterlijk → Menu's**:
1. Maak menu **Primary** → wijs toe aan locatie "Primary Menu"
2. Voeg toe: Home, Shop, TIGWARE, Over ons, Contact
3. Maak menu **Footer** → wijs toe aan "Footer Menu"

---

## Pagina's aanmaken

Maak de volgende pagina's aan (slug = URL):
- `over-ons` — Over ons
- `contact` — Contact
- `verzending` — Verzending & levering
- `retourneren` — Retourneren
- `algemene-voorwaarden` — Algemene voorwaarden
- `privacybeleid` — Privacybeleid
- `cookiebeleid` — Cookiebeleid

---

## Kleuren & Fonts

| Variabele    | Waarde        |
|-------------|---------------|
| `--accent`  | `#FF4D00` (oranje) |
| `--accent2` | `#FFB800` (goud) |
| `--bg`      | `#080808` |
| Font display| Bebas Neue |
| Font body   | DM Sans |
| Font mono   | Space Mono |

---

## AJAX Cart

De AJAX winkelwagen werkt automatisch. Producten worden toegevoegd zonder pagina-herlaad. De winkelwagen-badge wordt live bijgewerkt.

---

## Betaalmethoden (Mollie aanbevolen)

1. Installeer **Mollie Payments for WooCommerce**
2. Verbind je Mollie account (gratis account)
3. Schakel in: iDEAL, Creditcard, PayPal, Klarna

---

## Bestandsstructuur

```
perfectwelding/
├── style.css              ← Thema-declaratie
├── functions.php          ← Setup, WooCommerce, AJAX
├── header.php             ← Nav + marquee strip
├── footer.php             ← Footer + newsletter
├── front-page.php         ← Homepage
├── index.php              ← Fallback
├── page.php               ← Standaard pagina
├── 404.php                ← 404 pagina
├── assets/
│   ├── css/
│   │   ├── main.css       ← Alle stijlen
│   │   └── woocommerce.css← WooCommerce overrides
│   └── js/
│       └── main.js        ← Interacties + AJAX
└── woocommerce/
    ├── archive-product.php   ← Shop overzicht
    ├── single-product.php    ← Productpagina
    ├── cart/
    │   └── cart.php          ← Winkelwagen
    ├── checkout/
    │   ├── form-checkout.php ← Afrekenen
    │   └── thankyou.php      ← Bedankt pagina
    └── myaccount/
        └── my-account.php    ← Mijn account
```

---

## Performance & CPU-gebruik (belangrijk voor shared hosting)

Als je hosting een melding geeft over "High CPU usage" en je site (tijdelijk)
schorst, zijn dit de stappen — de eerste vier zijn al in de thema-code
verwerkt, de rest moet je zelf instellen bij je hosting:

**Al in de code opgelost (functions.php / inc/side-cart.php):**
1. Heartbeat API uitgeschakeld op de voorkant, vertraagd in het admin-paneel
   (was elke 15s admin-ajax.php aan het aanroepen).
2. Side-cart AJAX-calls teruggebracht van 2–3 requests naar 1 per actie
   (qty wijzigen / item verwijderen), plus een korte debounce op de +/-
   knoppen zodat snel klikken niet meteen een burst aan requests stuurt.
3. Bestsellers-query op de homepage wordt nu 6 uur gecached (transient) in
   plaats van bij elke paginabezoek opnieuw te draaien.
4. Simpele rate-limiting op de publieke AJAX-endpoints tegen bots/scanners
   die admin-ajax.php herhaaldelijk aanroepen (ruim genoeg ingesteld zodat
   drukte van échte klanten er nooit tegenaan loopt).
5. CSS/JS geminificeerd — main.css/js, responsive.css, side-cart.css/js en
   woocommerce.css hebben nu ook een `.min.` versie (~40% kleiner), en dat
   zijn de bestanden die het thema daadwerkelijk laadt. Pas de originele
   (niet-geminificeerde) bestanden aan als je iets wijzigt, en minify ze
   opnieuw voor je uploadt (bv. met een online CSS/JS-minifier).
6. Een PHP-notice die op ELKE paginaweergave in het error-log terechtkwam
   (null-check bug bij het checkout-script) is gefixt — dat scheelt
   onnodige schrijf-I/O op drukke sites.
7. Een kapotte CSS-comment (ontbrekende `/*`) in main.css is gerepareerd —
   zorgde voor ongeldige CSS die de browser extra moest verwerken.

**Tweede optimalisatieronde (na verwijderen van Elementor, PageSpeed-audit):**
8. Betaalgateway-CSS/JS (iDEAL/Mollie) en WooCommerce-Blocks assets worden
   nu alleen geladen op winkelwagen/afreken/account-pagina's — niet meer
   overal, dit was een van de grootste "render-blocking" en "unused
   CSS/JS" boosdoeners in het PageSpeed-rapport.
9. Google Fonts en de side-cart CSS laden nu non-blocking (preload+swap
   techniek) in plaats van het eerste beeldscherm te blokkeren.
10. De taalwissel-functie (nl/en) deed bij ELKE paginaweergave en bij
    ELKE winkelwagen-wijziging een volledige doorloop van alle
    HTML-elementen op de pagina — dit was een directe oorzaak van de
    "long main-thread tasks" in het rapport. Dit gebeurt nu alleen nog
    wanneer het echt nodig is (bezoeker heeft Engels gekozen), en bij
    winkelwagen-updates alleen nog binnen het winkelwagen-paneel zelf,
    niet de hele pagina.
11. Zie `cache-headers-htaccess-snippet.txt` in de theme-map voor
    browser-cache headers (moet in je site-root .htaccess, niet de
    theme-map) — dit lost de "Use efficient cache lifetimes" waarschuwing
    op.
12. Product-afbeeldingen worden al met correcte, beperkte afmetingen
    aangevraagd (480×480 voor thumbnails, 800×800 voor productpagina's)
    met lazy-loading. Voor verdere winst op "Improve image delivery":
    installeer een WebP/AVIF-conversie plugin, of vraag je hosting of
    dit al automatisch gebeurt.

---

## SPA-style navigatie (PJAX)

De theme laadt nu pagina's via AJAX in plaats van een volledige
paginaherlading, voor een snellere "app-gevoel" ervaring tijdens het
browsen. Belangrijke punten:

- **Winkelwagen, afrekenen en mijn-account zijn volledig uitgesloten** —
  die laden altijd normaal, dit is bewust zo gehouden voor
  betalingsveiligheid (iDEAL/Mollie redirects) en sessie-afhandeling.
- Werkt via `assets/js/spa-nav.js`: onderschept klikken op interne links,
  haalt de nieuwe pagina op, en vervangt alleen het gedeelte binnen
  `<main id="pw-spa-content">` (geopend in header.php, gesloten in
  footer.php). Nav, mobiel menu, marquee, en winkelwagen-paneel blijven
  ongewijzigd staan.
- Bij een fetch-fout valt het automatisch terug op een normale
  paginaherlading — de bezoeker komt nooit vast te zitten.
- **Dit verhoogt de PageSpeed-score NIET** (die meet alleen de eerste
  paginalading) — het maakt browsen na de eerste lading wél merkbaar
  sneller voor échte bezoekers.
- Als je in de toekomst een template-bestand toevoegt: zolang het via
  `get_header()` / `get_footer()` werkt, wordt het automatisch correct
  meegenomen — er is niets extra's nodig per pagina.

**Zelf instellen (kan niet vanuit het thema):**
1. Voeg toe aan `wp-config.php` (boven "That's all, stop editing!"):
   ```php
   define('DISABLE_WP_CRON', true);
   ```
   en vraag je hosting om een echte server-cronjob die elke 5–15 minuten
   `wp-cron.php` aanroept.
2. Installeer een cache-plugin (bv. WP Super Cache, W3 Total Cache, of wat
   je hosting aanbeveelt) voor page caching.
3. Vraag je hosting om de CPU-logs van de suspensie-periode — dat laat
   precies zien welk proces/IP de piek veroorzaakte (vaak bots die
   admin-ajax.php of wp-login.php bestoken).
4. Overweeg een security-plugin (bv. Wordfence) met login-rate-limiting als
   brute-force pogingen op wp-login.php de oorzaak blijken.
5. Zorg voor object caching (Redis/Memcached) als je hosting dit
   ondersteunt — dit scheelt veel database-belasting bij WooCommerce.

---

Gemaakt door: PerfectWelding Theme Generator
Versie: 1.0.0
# wordpressecomperfect
