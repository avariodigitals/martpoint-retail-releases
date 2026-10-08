<?php
/**
 * MartPoint — PRINT LAYOUT (printing business type only)
 * ======================================================
 * Wraps printing storefront pages so a print shop gets NO retail chrome.
 *
 * WHY THIS EXISTS
 * ---------------
 * `themes/shared/layout.php` is the retail shell: an `mp-*` header, nav,
 * announcement bar, footer, and — decisively — its own `:root { --mp-primary:
 * ... }` emitted from cssVariables(), which reads
 * `db_storefront_settings.primary_color`. A print theme ships its OWN palette,
 * so the retail layout was painting over it: every printing theme rendered the
 * same colour no matter which one was selected, which is the "theme does not
 * change" complaint.
 *
 * The printing designs in `themes/printing/` are self-contained — they declare
 * `--pr-*` tokens, their own navline, hero, grid and footer. They do not read a
 * single `--mp-*` variable and never include `themes/shared/sections/*`. So
 * they must not be wrapped in the retail layout at all.
 *
 * WHAT THIS FILE DELIBERATELY DOES NOT DO
 * ---------------------------------------
 *   - It emits NO `:root` and NO `--mp-*` variables. The print theme's own
 *     `--pr-*` tokens are therefore the only ones in play, and the selected
 *     theme's palette survives untouched.
 *   - It renders no header, nav or announcement bar. The print theme owns those.
 *   - It does not load the retail stylesheet.
 *
 * It DOES render the shared storefront footer (`themes/shared/footer`), because
 * that footer is backend-driven — Store Settings → Footer, social links,
 * business hours — and a print-only replacement would stop honouring those
 * fields. See the note at the footer call site.
 *
 * It supplies only what a page genuinely cannot own for itself: the document
 * shell, SEO/OpenGraph meta, fonts, the icon set, and the closing scripts.
 *
 * SCOPE: loaded only when the resolved theme is one of the printing themes.
 * Every other industry keeps `themes/shared/layout.php` exactly as before.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php
  $seoTitle = $seo_title ?? ($settings->meta_title ?? ($store->store_name ?? 'Store'));
  $seoDesc  = $seo_description ?? ($settings->meta_description ?? ($settings->store_description ?? 'Print services and products'));
  $seoImage = $seo_image ?? ($logo_url ?? base_url('uploads/site/icon.webp'));
  $seoUrl   = $seo_canonical ?? current_url();
  $storeName = $store->store_name ?? 'Print Shop';
?>
  <title><?= htmlspecialchars($seoTitle); ?></title>
  <meta name="description" content="<?= htmlspecialchars($seoDesc); ?>">
<?php if(!empty($settings->meta_keywords)): ?>
  <meta name="keywords" content="<?= htmlspecialchars($settings->meta_keywords); ?>">
<?php endif; ?>
  <meta name="robots" content="<?= (($settings->robots_index ?? '1') == '1') ? 'index, follow' : 'noindex, nofollow'; ?>">
  <link rel="canonical" href="<?= htmlspecialchars($seoUrl); ?>">

  <!-- Open Graph -->
  <meta property="og:type" content="<?= htmlspecialchars($seo_type ?? 'website'); ?>">
  <meta property="og:title" content="<?= htmlspecialchars($seoTitle); ?>">
  <meta property="og:description" content="<?= htmlspecialchars($seoDesc); ?>">
  <meta property="og:image" content="<?= htmlspecialchars($seoImage); ?>">
  <meta property="og:url" content="<?= htmlspecialchars($seoUrl); ?>">
  <meta property="og:site_name" content="<?= htmlspecialchars($storeName); ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($seoTitle); ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($seoDesc); ?>">
  <meta name="twitter:image" content="<?= htmlspecialchars($seoImage); ?>">

  <meta name="theme-color" content="<?= htmlspecialchars($settings->theme_color ?? '#18211c'); ?>">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars($storeName); ?>">

<?php if(!empty($favicon_url ?? $settings->favicon ?? null)): ?>
  <link rel="icon" href="<?= htmlspecialchars($favicon_url ?? base_url('uploads/site/' . $settings->favicon)); ?>">
<?php endif; ?>

  <?php /* Fonts: the printing themes use Georgia/system serif for Atelier and a
           grotesque for Press/Desk, so load the family the store selected
           without forcing the retail default on it. */ ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="<?= $this->theme_engine->googleFontLink(); ?>" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('theme/css/font-awesome-4.7.0/css/font-awesome.min.css?v=4.7.0'); ?>">

<?php
  // The shared `mp-*` stylesheet layer.
  //
  // This is what styles the Homepage Builder sections (`themes/shared/sections/*`)
  // and the storefront footer — 17 of the 18 section templates carry no <style>
  // of their own, and hero.php alone uses 35 `mp-` classes. It lives in its own
  // partial precisely so this layout can include the SAME layer rather than a
  // copy: a printing shop's enabled sections must render exactly as they do on a
  // retail storefront.
  //
  // It is also where `--mp-*` tokens come from. The print design's own `--pr-*`
  // palette is unaffected — those are defined on `.pr-home`, which sits in the
  // body above this layer's reach, so the print theme still drives its own look.
  $this->load->view('themes/shared/_shared_css');
?>
</head>
<body>
<?= $content; ?>
<?php
  // The shop's footer — the SAME partial every other storefront uses.
  //
  // This layout originally rendered no footer on the reasoning that the print
  // design owned its own chrome. That was wrong: `themes/printing/home.php`
  // styles a `.pr-footer` in its stylesheet but never emits the element, so the
  // shop lost its footer completely — contact details, opening hours, social
  // links, policies, payment marks — with nothing replacing them.
  //
  // Reusing `themes/shared/footer` is deliberate. A storefront footer is not
  // decoration: it is edited from the backend (Store Settings → Footer, plus
  // social links and business hours), and every one of those fields must reach
  // the page. A second, print-only footer would silently stop honouring those
  // settings the moment anyone added a field — the exact class of drift that
  // made the printing theme unreachable in the first place.
  //
  // It needs $cart_enabled / $is_service_store to suppress the retail cart
  // entry points, both of which this layout already has from catalogueFlags().
  $this->load->view('themes/shared/footer');
?>
<?php
  // Closing scripts only — the retail layout's script bundle is coupled to its
  // own markup (cart drawer, sticky bar, mobile nav) and would throw against a
  // print page that has none of those elements.
  $this->load->view('themes/printing/scripts');
?>
</body>
</html>
