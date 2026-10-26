<?php
/**
 * Food Express storefront shell.
 *
 * This is intentionally a self-contained visual system. The public food
 * storefront should feel like a mobile ordering app first, while remaining
 * comfortable on desktop. Product, category and merchant data still comes
 * from the existing storefront controllers and Theme_engine.
 */
$fxPrimary   = $this->theme_engine->resolveBrandColor('primary');
$fxSecondary = $this->theme_engine->resolveBrandColor('secondary');
$fxFont      = $this->settings->font_family ?: ($this->theme->default_font_family ?? 'Inter');
$fxBtnStyle  = $this->settings->button_style ?: 'rounded';
$fxRadius    = ($fxBtnStyle === 'pill') ? '9999px' : (($fxBtnStyle === 'square') ? '12px' : '20px');
$fxRadiusSm  = ($fxBtnStyle === 'pill') ? '9999px' : (($fxBtnStyle === 'square') ? '8px' : '14px');
?>
<style>
:root {
  --fx-cream: #faf8f0;
  --fx-cream-deep: #f2ecdc;
  --fx-green: <?= htmlspecialchars($fxPrimary); ?>;
  --fx-green-deep: #073b2c;
  --fx-green-soft: #dcefe2;
  --fx-gold: <?= htmlspecialchars($fxSecondary); ?>;
  --fx-yellow: #f4c62f;
  --fx-ink: #10221b;
  --fx-muted: #68766f;
  --fx-border: rgba(16,34,27,.12);
  --fx-white: #fffef9;
  --fx-danger: #c84b3d;
  --fx-success: #198754;
  --fx-shadow: 0 18px 45px rgba(7,59,44,.10);
  --fx-font: '<?= htmlspecialchars($fxFont); ?>', -apple-system, BlinkMacSystemFont, sans-serif;
  --fx-radius: <?= $fxRadius; ?>;
  --fx-radius-sm: <?= $fxRadiusSm; ?>;
}
* { box-sizing: border-box; }
html { scroll-behavior: smooth; }
body.theme-food_express { margin: 0; background: var(--fx-cream); color: var(--fx-ink); font-family: var(--fx-font); font-size: 15px; line-height: 1.5; -webkit-font-smoothing: antialiased; }
body.theme-food_express a { color: inherit; }
body.theme-food_express button, body.theme-food_express input, body.theme-food_express textarea, body.theme-food_express select { font: inherit; }

/* Header: location + order actions, like a real food app. */
.fx-announcement { background: var(--fx-green-deep) !important; padding: 8px 16px !important; font-size: 12px !important; letter-spacing: .01em; }
.fx-header { position: sticky; top: 0; z-index: 100; background: rgba(250,248,240,.94); border-bottom: 1px solid rgba(16,34,27,.08); backdrop-filter: blur(18px); }
.fx-header-inner { max-width: 1180px; margin: 0 auto; padding: 12px 24px 14px; }
.fx-header-top { display: flex; align-items: center; justify-content: space-between; gap: 18px; }
.fx-logo { display: inline-flex; align-items: center; gap: 10px; text-decoration: none; min-width: 0; }
.fx-logo-img { width: 42px; height: 42px; object-fit: contain; border-radius: 14px; }
.fx-logo-text { display: flex; flex-direction: column; min-width: 0; }
.fx-logo-name { color: var(--fx-green-deep); font-size: 20px; font-weight: 900; line-height: 1.02; letter-spacing: -.04em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.fx-logo-tagline { color: var(--fx-muted); font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; white-space: nowrap; }
.fx-location { display: inline-flex; align-items: center; gap: 8px; min-width: 0; padding: 6px 0; color: var(--fx-muted); font-size: 12px; text-align: right; }
.fx-location svg { flex: 0 0 auto; color: var(--fx-green); }
.fx-location-copy { display: flex; flex-direction: column; min-width: 0; }
.fx-location-label { font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
.fx-location-value { max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--fx-ink); font-weight: 800; }
.fx-header-actions { display: flex; align-items: center; gap: 10px; }
.fx-header-account { display: inline-flex; align-items: center; gap: 7px; color: var(--fx-green-deep); font-size: 13px; font-weight: 800; text-decoration: none; }
.fx-header-account svg { width: 22px; height: 22px; }
.fx-cart-btn { position: relative; display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 15px; background: var(--fx-green-deep); color: #fff !important; text-decoration: none; box-shadow: 0 8px 18px rgba(7,59,44,.2); transition: transform .18s, box-shadow .18s; }
.fx-cart-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(7,59,44,.25); }
.fx-cart-count { position: absolute; top: -6px; right: -6px; min-width: 20px; height: 20px; padding: 0 5px; display: flex; align-items: center; justify-content: center; border: 2px solid var(--fx-cream); border-radius: 999px; background: var(--fx-yellow); color: var(--fx-green-deep); font-size: 11px; font-weight: 900; }
.fx-cart-amount { display: none; }
.fx-mobile-menu-btn { display: none; width: 42px; height: 42px; align-items: center; justify-content: center; border: 1px solid var(--fx-border); border-radius: 14px; background: var(--fx-white); color: var(--fx-green-deep); }
.fx-header-search { display: none; }
.fx-search { flex: 1; max-width: 540px; margin: 0 12px; position: relative; }
.fx-search input, .fx-header-search input { width: 100%; padding: 12px 16px 12px 42px; border: 1px solid var(--fx-border); border-radius: 999px; background: var(--fx-white); color: var(--fx-ink); outline: none; box-shadow: 0 4px 14px rgba(7,59,44,.04); }
.fx-search input:focus, .fx-header-search input:focus { border-color: var(--fx-green); box-shadow: 0 0 0 4px rgba(30,107,64,.10); }
.fx-search-icon { position: absolute; left: 14px; top: 50%; width: 19px; height: 19px; transform: translateY(-50%); color: var(--fx-muted); pointer-events: none; }

/* Category rail */
.fx-nav { position: sticky; top: 72px; z-index: 90; overflow-x: auto; padding: 10px 0; background: rgba(250,248,240,.92); border-bottom: 1px solid rgba(16,34,27,.06); white-space: nowrap; scrollbar-width: none; }
.fx-nav::-webkit-scrollbar { display: none; }
.fx-nav-inner { max-width: 1180px; margin: 0 auto; padding: 0 24px; display: flex; gap: 8px; }
.fx-nav-link { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border: 1px solid transparent; border-radius: 999px; background: var(--fx-white); color: var(--fx-muted); font-size: 12px; font-weight: 800; text-decoration: none; box-shadow: 0 3px 10px rgba(7,59,44,.04); }
.fx-nav-link.active, .fx-nav-link:hover { background: var(--fx-green-deep); color: #fff; border-color: var(--fx-green-deep); }
.fx-nav-link svg { width: 15px; height: 15px; }

/* App-like hero */
.fx-hero { position: relative; overflow: hidden; padding: 32px 24px 56px; background: var(--fx-green-deep) !important; color: #fff; }
.fx-hero::before, .fx-hero::after { content: ''; position: absolute; border-radius: 50%; pointer-events: none; }
.fx-hero::before { width: 420px; height: 420px; right: -130px; top: -220px; background: var(--fx-yellow); }
.fx-hero::after { width: 320px; height: 320px; left: -180px; bottom: -220px; background: rgba(244,198,47,.16); }
.fx-hero-inner { position: relative; z-index: 1; max-width: 1180px; margin: 0 auto; display: grid; grid-template-columns: minmax(0,1fr) minmax(280px,420px); gap: 36px; align-items: center; text-align: left; }
.fx-hero-copy { max-width: 600px; }
.fx-hero-tagline { display: inline-flex; padding: 7px 12px; border-radius: 999px; background: rgba(244,198,47,.18); color: #ffe48a; font-size: 11px; font-weight: 900; letter-spacing: .09em; text-transform: uppercase; }
.fx-hero-title { max-width: 590px; margin: 16px 0 10px; color: #fff !important; font-size: clamp(38px, 6vw, 68px); font-weight: 950; line-height: .98; letter-spacing: -.065em; }
.fx-hero-subtitle { max-width: 510px; margin: 0 0 24px; color: rgba(255,255,255,.78); font-size: 17px; line-height: 1.55; }
.fx-hero-search { position: relative; max-width: 560px; }
.fx-hero-search input { width: 100%; padding: 16px 20px 16px 52px; border: 0; border-radius: 18px; background: var(--fx-white); color: var(--fx-ink); outline: none; box-shadow: 0 14px 28px rgba(0,0,0,.18); }
.fx-hero-search input::placeholder { color: #81908a; }
.fx-hero-search-icon { position: absolute; left: 17px; top: 50%; width: 22px; height: 22px; transform: translateY(-50%); color: var(--fx-green); pointer-events: none; }
.fx-hero-location { display: inline-flex; align-items: center; gap: 7px; margin-top: 12px; color: rgba(255,255,255,.70); font-size: 12px; }
.fx-hero-location svg { color: var(--fx-yellow); }
.fx-hero-location strong { color: #fff; font-weight: 800; }
.fx-hero-trust { display: flex; flex-wrap: wrap; gap: 18px; margin-top: 22px; }
.fx-hero-trust-item { display: inline-flex; align-items: center; gap: 7px; color: rgba(255,255,255,.76); font-size: 12px; font-weight: 700; }
.fx-hero-trust-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--fx-yellow); }
.fx-hero-art { position: relative; min-height: 310px; display: flex; align-items: center; justify-content: center; }
.fx-hero-art::before { content: ''; position: absolute; inset: 10% 4% 4%; border-radius: 46% 54% 48% 52%; background: var(--fx-yellow); transform: rotate(-8deg); }
.fx-hero-image { position: relative; z-index: 1; width: min(100%, 390px); max-height: 340px; object-fit: cover; border: 10px solid rgba(255,255,255,.8); border-radius: 32px; transform: rotate(4deg); box-shadow: 0 24px 44px rgba(0,0,0,.25); }
.fx-hero-art-placeholder { position: relative; z-index: 1; width: 240px; height: 240px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: var(--fx-yellow); color: var(--fx-green-deep); text-align: center; font-size: 24px; font-weight: 950; line-height: 1.05; transform: rotate(-7deg); box-shadow: 0 24px 44px rgba(0,0,0,.18); }

/* Sections and category cards */
.fx-section { padding: 54px 24px !important; }
.fx-section-inner { max-width: 1180px; margin: 0 auto; }
.fx-section-header { display: flex; align-items: end; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
.fx-section-overline { margin-bottom: 6px; color: var(--fx-green); font-size: 11px; font-weight: 900; letter-spacing: .12em; text-transform: uppercase; }
.fx-section-title { margin: 0; color: var(--fx-green-deep); font-size: clamp(26px, 4vw, 40px); font-weight: 950; line-height: 1.05; letter-spacing: -.045em; }
.fx-section-subtitle { color: var(--fx-muted); font-size: 14px; }
.fx-view-all { display: inline-flex; align-items: center; gap: 6px; color: var(--fx-green) !important; font-size: 13px; font-weight: 900; text-decoration: none; white-space: nowrap; }
.fx-view-all:hover { color: var(--fx-green-deep) !important; }
.fx-categories { display: grid; grid-template-columns: repeat(8, minmax(90px,1fr)); gap: 16px; }
.fx-cat-tile { min-width: 0; color: var(--fx-ink); text-align: center; text-decoration: none; }
.fx-cat-img-wrap { position: relative; width: 100%; aspect-ratio: 1; overflow: hidden; border: 4px solid var(--fx-white); border-radius: 50%; background: var(--fx-cream-deep); box-shadow: 0 5px 0 rgba(7,59,44,.08); transition: transform .2s, box-shadow .2s; }
.fx-cat-tile:hover .fx-cat-img-wrap, .fx-cat-tile.active .fx-cat-img-wrap { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(7,59,44,.14); }
.fx-cat-img { width: 100%; height: 100%; object-fit: cover; }
.fx-cat-img-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--fx-green-soft), var(--fx-cream-deep)); color: var(--fx-green); font-size: 28px; }
.fx-cat-name { overflow: hidden; margin-top: 10px; color: var(--fx-green-deep); font-size: 13px; font-weight: 900; text-overflow: ellipsis; white-space: nowrap; }
.fx-cat-count { margin-top: 2px; color: var(--fx-muted); font-size: 11px; }

/* Menu cards */
.fx-grid { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: 18px; }
.fx-card { overflow: hidden; display: flex; flex-direction: column; min-width: 0; border: 1px solid rgba(16,34,27,.08); border-radius: 22px; background: var(--fx-white); box-shadow: 0 5px 0 rgba(7,59,44,.06); text-decoration: none; transition: transform .2s, box-shadow .2s; }
.fx-card:hover { transform: translateY(-5px); box-shadow: 0 18px 45px rgba(7,59,44,.10); }
.fx-card-img-wrap { position: relative; aspect-ratio: 1.15; overflow: hidden; background: var(--fx-cream-deep); }
.fx-card-img { width: 100%; height: 100%; object-fit: cover; transition: transform .35s; }
.fx-card:hover .fx-card-img { transform: scale(1.04); }
.fx-card-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; padding: 16px; background: linear-gradient(135deg, var(--fx-green-soft), var(--fx-cream-deep)); color: var(--fx-green-deep); font-size: 14px; font-weight: 950; letter-spacing: -.02em; line-height: 1.15; text-align: center; }
.fx-card-badge { position: absolute; top: 12px; left: 12px; padding: 5px 9px; border-radius: 999px; font-size: 10px; font-weight: 950; letter-spacing: .04em; text-transform: uppercase; }
.fx-card-badge.sale { background: var(--fx-danger); color: #fff; }
.fx-card-badge.new { background: var(--fx-green-deep); color: #fff; }
.fx-card-badge.bestseller { background: var(--fx-yellow); color: var(--fx-green-deep); }
.fx-card-wishlist { position: absolute; top: 12px; right: 12px; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; border: 0; border-radius: 50%; background: rgba(255,255,255,.9); color: var(--fx-green-deep); cursor: pointer; opacity: 0; }
.fx-card:hover .fx-card-wishlist, .fx-card-wishlist:focus { opacity: 1; }
.fx-card-body { display: flex; flex: 1; flex-direction: column; padding: 15px 16px 16px; }
.fx-card-meta { min-height: 17px; margin-bottom: 6px; color: var(--fx-muted); font-size: 11px; }
.fx-card-rating { display: inline-flex; align-items: center; gap: 4px; color: #b48100; font-weight: 800; }
.fx-card-name { display: -webkit-box; overflow: hidden; margin: 0 0 8px; color: var(--fx-green-deep); font-size: 16px; font-weight: 900; line-height: 1.18; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
.fx-card-price-row { display: flex; flex-wrap: wrap; align-items: baseline; gap: 8px; margin-bottom: 10px; }
.fx-card-price { color: var(--fx-green); font-size: 17px; font-weight: 950; }
.fx-card-old-price { color: var(--fx-muted); font-size: 12px; text-decoration: line-through; }
.fx-card-discount { background: rgba(200,75,61,.12); color: var(--fx-danger); font-size: 10px; font-weight: 950; padding: 3px 6px; border-radius: 999px; }
.fx-card-footer { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: auto; padding-top: 10px; border-top: 1px solid rgba(16,34,27,.08); }
.fx-card-add { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 40px; padding: 10px 14px; border: 0; border-radius: 13px; background: var(--fx-green); color: #fff; font-size: 12px; font-weight: 900; cursor: pointer; transition: transform .15s, background .15s; }
.fx-card-add:hover:not(:disabled) { background: var(--fx-green-deep); transform: translateY(-1px); }
.fx-card-add:disabled { opacity: .5; cursor: not-allowed; }
.fx-card-wa { display: inline-flex; align-items: center; gap: 6px; padding: 10px 12px; border: 1px solid rgba(37,211,102,.3); border-radius: 13px; background: #f1fff5; color: #168a44; font-size: 11px; font-weight: 900; cursor: pointer; }

/* Fulfilment location cards keep the branch choice visible and touch-friendly. */
.fx-location-picker { display: grid; grid-template-columns: minmax(220px,.8fr) minmax(0,1.2fr); align-items: center; gap: 24px; margin-bottom: 24px; padding: 20px 22px; border-radius: 24px; background: var(--fx-green-deep); color: #fff; box-shadow: 0 14px 32px rgba(7,59,44,.12); }
.fx-location-picker .fx-section-overline { margin-bottom: 4px; color: var(--fx-yellow); }
.fx-location-picker h2 { margin: 0 0 5px; color: #fff; font-size: 20px; font-weight: 950; letter-spacing: -.03em; }
.fx-location-picker p { margin: 0; color: rgba(255,255,255,.72); font-size: 13px; }
.fx-outlet-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 10px; }
.fx-outlet-option { position: relative; display: flex; align-items: flex-start; gap: 10px; min-height: 68px; padding: 12px; border: 1px solid rgba(255,255,255,.24); border-radius: 16px; background: rgba(255,255,255,.09); color: #fff; cursor: pointer; transition: border-color .15s, background .15s, transform .15s; }
.fx-outlet-option:hover, .fx-outlet-option.active { border-color: var(--fx-yellow); background: rgba(244,198,47,.16); transform: translateY(-1px); }
.fx-outlet-option input { margin-top: 3px; accent-color: var(--fx-yellow); }
.fx-outlet-copy { min-width: 0; display: flex; flex-direction: column; gap: 3px; }
.fx-outlet-copy strong { color: #fff; font-size: 13px; font-weight: 900; }
.fx-outlet-copy small { overflow: hidden; color: rgba(255,255,255,.64); font-size: 11px; text-overflow: ellipsis; white-space: nowrap; }
.fx-outlet-check { display: none; margin-left: auto; flex: 0 0 auto; color: var(--fx-yellow); }
.fx-outlet-option.active .fx-outlet-check { display: block; }
.fx-location-picker-note { padding: 13px 15px !important; border: 1px solid rgba(255,255,255,.2); border-radius: 14px; background: rgba(255,255,255,.08); }

/* Detail, cart and service surfaces inherit the same rhythm. */
.fx-track-chip { display: inline-flex; align-items: center; gap: 6px; padding: 7px 11px; border-radius: 999px; font-size: 11px; font-weight: 900; }
.fx-track-chip.preparing { background: #fff4c7; color: #8c6900; }
.fx-track-chip.ready, .fx-track-chip.delivered { background: var(--fx-green-soft); color: var(--fx-green); }
.fx-track-chip.delivering { background: #e1efff; color: #2167ad; }
.fx-spice-levels { display: flex; flex-wrap: wrap; gap: 8px; }
.fx-spice-btn { min-height: 40px; padding: 9px 13px; border: 1px solid var(--fx-border); border-radius: 12px; background: var(--fx-white); color: var(--fx-ink); font-size: 12px; font-weight: 800; cursor: pointer; }
.fx-spice-btn.active, .fx-spice-btn:hover { border-color: var(--fx-green); background: var(--fx-green); color: #fff; }

/* Footer and mobile navigation */
.fx-footer { margin-top: 40px; padding: 54px 24px 24px; background: var(--fx-green-deep); color: rgba(255,255,255,.72); }
.fx-footer-inner { max-width: 1180px; margin: 0 auto; display: grid; grid-template-columns: 1.7fr repeat(3,1fr); gap: 34px; }
.fx-footer-brand { max-width: 310px; }
.fx-footer-logo { margin-bottom: 7px; color: #fff; font-size: 24px; font-weight: 950; letter-spacing: -.04em; }
.fx-footer-tagline { margin-bottom: 14px; color: var(--fx-yellow); font-size: 10px; font-weight: 900; letter-spacing: .12em; text-transform: uppercase; }
.fx-footer-desc { color: rgba(255,255,255,.65); font-size: 13px; line-height: 1.7; }
.fx-footer-col h4 { margin: 0 0 13px; color: var(--fx-yellow); font-size: 11px; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; }
.fx-footer-links { display: flex; flex-direction: column; gap: 9px; margin: 0; padding: 0; list-style: none; }
.fx-footer-links a { color: rgba(255,255,255,.7); font-size: 13px; text-decoration: none; }
.fx-footer-links a:hover { color: #fff; }
.fx-footer-bottom { max-width: 1180px; margin: 34px auto 0; padding-top: 20px; display: flex; align-items: center; justify-content: space-between; gap: 14px; border-top: 1px solid rgba(255,255,255,.14); color: rgba(255,255,255,.45); font-size: 11px; }
.fx-footer-social { display: flex; gap: 12px; }
.fx-footer-social a { color: rgba(255,255,255,.55); }
.fx-footer-social svg { width: 18px; height: 18px; }
.fx-mobile-nav { display: none; }

@media (max-width: 900px) {
  .fx-header-inner { padding-inline: 18px; }
  .fx-search { max-width: 360px; }
  .fx-hero-inner { grid-template-columns: 1fr 300px; gap: 20px; }
  .fx-grid { grid-template-columns: repeat(3, minmax(0,1fr)); }
  .fx-categories { grid-template-columns: repeat(6, minmax(80px,1fr)); }
  .fx-footer-inner { grid-template-columns: 1.5fr 1fr 1fr; }
  .fx-footer-col:last-child { grid-column: 1 / -1; }
}
@media (max-width: 680px) {
  body.theme-food_express { padding-bottom: 72px; }
  .fx-header-inner { padding: 10px 14px 12px; }
  .fx-header-top { gap: 10px; }
  .fx-mobile-menu-btn { display: inline-flex; order: -1; }
  .fx-logo-img { width: 36px; height: 36px; border-radius: 12px; }
  .fx-logo-name { max-width: 145px; font-size: 17px; }
  .fx-logo-tagline { display: none; }
  .fx-location { display: inline-flex; max-width: 145px; margin-left: auto; padding: 0; text-align: left; }
  .fx-location-value { max-width: 112px; }
  .fx-header-account { display: none; }
  .fx-search { display: none; }
  .fx-header-search { display: block; margin-top: 10px; }
  .fx-nav { top: 108px; padding: 8px 0; }
  .fx-nav-inner { padding-inline: 14px; }
  .fx-nav-link { padding: 8px 12px; }
  .fx-hero { padding: 28px 16px 38px; }
  .fx-hero-inner { display: block; }
  .fx-hero-title { max-width: 330px; font-size: clamp(38px, 12vw, 54px); }
  .fx-hero-subtitle { font-size: 15px; }
  .fx-hero-art { min-height: 190px; margin-top: 18px; }
  .fx-hero-art::before { inset: 10% 18% 0; }
  .fx-hero-image { width: min(88%, 330px); max-height: 220px; border-width: 7px; border-radius: 24px; }
  .fx-hero-art-placeholder { width: 170px; height: 170px; font-size: 18px; }
  .fx-section { padding: 36px 16px !important; }
  .fx-section-header { align-items: flex-start; }
  .fx-section-title { font-size: 28px; }
  .fx-categories { display: flex; gap: 14px; overflow-x: auto; padding: 3px 1px 12px; margin-inline: -2px; scrollbar-width: none; }
  .fx-categories::-webkit-scrollbar { display: none; }
  .fx-cat-tile { flex: 0 0 78px; }
  .fx-cat-name { font-size: 11px; }
  .fx-grid { grid-template-columns: repeat(2, minmax(0,1fr)); gap: 12px; }
  .fx-card { border-radius: 18px; }
  .fx-card-img-wrap { aspect-ratio: 1.05; }
  .fx-card-body { padding: 12px; }
  .fx-card-name { font-size: 14px; }
  .fx-card-price { font-size: 15px; }
  .fx-card-add { width: 100%; padding-inline: 10px; }
  .fx-card-wa { display: none; }
  .fx-section-inner > div[style*="grid-template-columns"] { grid-template-columns: 1fr !important; }
  .fx-section-inner [style*="position:sticky"] { position: static !important; top: auto !important; }
  .fx-location-picker { grid-template-columns: 1fr; gap: 16px; padding: 18px; border-radius: 20px; }
  .fx-outlet-grid { grid-template-columns: 1fr; }
  .fx-footer { margin-top: 24px; padding: 36px 16px 20px; }
  .fx-footer-inner { grid-template-columns: 1fr 1fr; gap: 24px 18px; }
  .fx-footer-brand, .fx-footer-col:last-child { grid-column: 1 / -1; }
  .fx-footer-bottom { flex-direction: column; align-items: flex-start; margin-top: 26px; }
  .fx-mobile-nav { position: fixed; left: 10px; right: 10px; bottom: 10px; z-index: 120; display: grid; grid-template-columns: repeat(4,1fr); padding: 8px; border: 1px solid rgba(7,59,44,.12); border-radius: 22px; background: rgba(255,254,249,.94); box-shadow: 0 16px 36px rgba(7,59,44,.2); backdrop-filter: blur(18px); }
  .fx-mobile-nav a { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 5px 2px; color: var(--fx-muted); font-size: 10px; font-weight: 800; text-decoration: none; }
  .fx-mobile-nav a.active, .fx-mobile-nav a:hover { color: var(--fx-green); }
  .fx-mobile-nav svg { width: 19px; height: 19px; }
}
@media (max-width: 390px) { .fx-logo-name { max-width: 118px; } .fx-card-body { padding: 10px; } .fx-card-add { font-size: 11px; } }

/* Final restaurant art direction: editorial type, generous rhythm and a clear
   location-first ordering journey. Kept here so merchant content and the
   existing homepage builder remain data-compatible. */
body.theme-food_express { background: #f8f7f2; font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif; color: #10261c; }
body.theme-food_express h1, body.theme-food_express h2, body.theme-food_express h3, body.theme-food_express h4, body.theme-food_express button, body.theme-food_express .fx-logo-name { font-family: 'Outfit', 'DM Sans', sans-serif; }
.fx-header { background: rgba(248,247,242,.90); border-bottom: 1px solid rgba(16,38,28,.10); box-shadow: 0 8px 30px rgba(16,38,28,.04); }
.fx-header-inner { max-width: 1240px; padding: 16px 28px 18px; }
.fx-header-top { display: grid; grid-template-columns: auto minmax(160px,1fr) auto auto; align-items: center; gap: 26px; }
.fx-logo { gap: 11px; }
.fx-logo-img { width: 44px; height: 44px; border-radius: 15px; }
.fx-logo-name { font-size: 21px; letter-spacing: -.045em; }
.fx-logo-tagline { font-size: 9px; letter-spacing: .14em; }
.fx-header-links { display: flex; align-items: center; justify-content: center; gap: 28px; }
.fx-header-links a { color: #64736b; font-size: 13px; font-weight: 700; text-decoration: none; transition: color .15s; }
.fx-header-links a:first-child, .fx-header-links a:hover { color: var(--fx-green-deep); }
.fx-location { justify-self: end; display: inline-flex; align-items: center; gap: 8px; min-width: 0; padding: 8px 12px; border: 1px solid rgba(16,38,28,.10); border-radius: 14px; background: rgba(255,255,255,.70); color: var(--fx-muted); text-align: left; cursor: pointer; }
.fx-location:hover { border-color: rgba(30,107,64,.35); background: #fff; }
.fx-location-chevron { margin-left: 2px; color: var(--fx-green); }
.fx-location-value { max-width: 155px; }
.fx-header-actions { justify-self: end; }
.fx-header-account { color: var(--fx-green-deep); }
.fx-cart-btn { width: 46px; height: 46px; border-radius: 16px; }
.fx-nav { top: 76px; padding: 11px 0; background: rgba(248,247,242,.92); }
.fx-nav-inner { max-width: 1240px; padding: 0 28px; }
.fx-nav-link { background: #fff; border-color: rgba(16,38,28,.08); box-shadow: 0 3px 10px rgba(16,38,28,.04); }

.fx-hero { padding: 58px 28px 76px; background: #0b4b38 !important; }
.fx-hero-inner { max-width: 1240px; min-height: 470px; grid-template-columns: minmax(0,1.05fr) minmax(320px,.95fr); gap: 64px; }
.fx-hero-copy { max-width: 650px; }
.fx-hero-tagline { padding: 8px 13px; background: rgba(245,200,75,.17); color: #f9d979; font-size: 10px; letter-spacing: .13em; }
.fx-hero-title { max-width: 650px; margin: 18px 0 16px; font-size: clamp(48px, 6.5vw, 82px); line-height: .94; letter-spacing: -.075em; }
.fx-hero-subtitle { max-width: 520px; margin-bottom: 28px; font-size: 18px; color: rgba(255,255,255,.74); }
.fx-hero-search { max-width: 580px; }
.fx-hero-search input { min-height: 60px; padding: 18px 22px 18px 54px; border-radius: 19px; box-shadow: 0 16px 38px rgba(0,0,0,.20); font-size: 15px; }
.fx-hero-location { margin-top: 16px; font-size: 13px; }
.fx-hero-trust { gap: 24px; margin-top: 28px; }
.fx-hero-trust-item { font-size: 12px; }
.fx-hero-art { min-height: 370px; }
.fx-hero-art::before { inset: 8% 2% 5%; width: 100%; height: auto; background: #f5c84b; transform: rotate(-9deg); }
.fx-hero-art::after { content: ''; position: absolute; right: 3%; bottom: 2%; width: 70px; height: 70px; border-radius: 50%; background: #0f7456; box-shadow: -300px -245px 0 -24px rgba(255,255,255,.85), -330px -120px 0 -30px rgba(255,255,255,.55); }
.fx-hero-image { width: min(100%, 430px); max-height: 380px; border: 8px solid rgba(255,255,255,.88); border-radius: 36px; transform: rotate(3deg); }
.fx-hero-art-placeholder { width: 270px; height: 270px; border: 12px solid rgba(255,255,255,.80); font-size: 28px; transform: rotate(-6deg); }

.fx-outlet-section { padding: 40px 28px 12px; background: #f8f7f2; }
.fx-outlet-section-inner { max-width: 1240px; margin: 0 auto; }
.fx-outlet-heading { display: flex; align-items: end; justify-content: space-between; gap: 24px; margin-bottom: 18px; }
.fx-outlet-heading h2 { max-width: 600px; margin: 0 0 7px; color: var(--fx-green-deep); font-size: clamp(27px, 4vw, 42px); line-height: 1.02; letter-spacing: -.055em; }
.fx-outlet-heading p { margin: 0; color: var(--fx-muted); font-size: 14px; }
.fx-location-grid { display: grid; grid-template-columns: repeat(5, minmax(0,1fr)); gap: 10px; }
.fx-location-card { display: flex; flex-direction: column; align-items: flex-start; min-height: 150px; padding: 17px; border: 1px solid rgba(16,38,28,.10); border-radius: 20px; background: #fff; color: var(--fx-ink); text-align: left; box-shadow: 0 6px 0 rgba(7,59,44,.05); cursor: pointer; transition: transform .18s, border-color .18s, box-shadow .18s; }
.fx-location-card:hover, .fx-location-card.active { border-color: var(--fx-green); box-shadow: 0 10px 24px rgba(7,59,44,.11); transform: translateY(-3px); }
.fx-location-card-pin { display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; margin-bottom: 18px; border-radius: 12px; background: #eef6e8; color: var(--fx-green); }
.fx-location-card-copy { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.fx-location-card-copy strong { color: var(--fx-green-deep); font-size: 15px; }
.fx-location-card-copy small { overflow: hidden; color: var(--fx-muted); font-size: 11px; text-overflow: ellipsis; white-space: nowrap; }
.fx-location-card-action { display: inline-flex; align-items: center; gap: 5px; margin-top: auto; padding-top: 12px; color: var(--fx-green); font-size: 11px; font-weight: 800; }
.fx-location-card.active .fx-location-card-action { color: #9a7400; }

.fx-section { padding: 70px 28px !important; }
.fx-section-inner { max-width: 1240px; }
.fx-section-header { margin-bottom: 27px; }
.fx-section-title { font-size: clamp(30px, 4vw, 46px); letter-spacing: -.06em; }
.fx-section-overline { font-size: 10px; letter-spacing: .16em; }
.fx-categories { grid-template-columns: repeat(8, minmax(92px,1fr)); gap: 20px; }
.fx-cat-img-wrap { border-width: 5px; box-shadow: 0 6px 0 rgba(7,59,44,.07); }
.fx-cat-name { margin-top: 11px; font-size: 12px; }
.fx-grid { grid-template-columns: repeat(4, minmax(0,1fr)); gap: 20px; }
.fx-card { border-color: rgba(16,38,28,.09); border-radius: 24px; box-shadow: 0 5px 0 rgba(7,59,44,.05); }
.fx-card-img-wrap { aspect-ratio: 1.08; }
.fx-card-body { padding: 17px 18px 18px; }
.fx-card-name { font-size: 17px; letter-spacing: -.02em; }
.fx-card-price { font-size: 18px; }
.fx-card-add { min-height: 42px; border-radius: 14px; background: var(--fx-green-deep); }
.fx-card-add:hover:not(:disabled) { background: #0d7a58; }

@media (max-width: 980px) {
  .fx-header-top { grid-template-columns: auto 1fr auto; }
  .fx-header-links { display: none; }
  .fx-location-grid { grid-template-columns: repeat(3, minmax(0,1fr)); }
  .fx-hero-inner { gap: 34px; }
  .fx-grid { grid-template-columns: repeat(3, minmax(0,1fr)); }
}
@media (max-width: 680px) {
  .fx-header-inner { padding: 10px 14px 13px; }
  .fx-header-top { display: flex; gap: 9px; }
  .fx-logo-img { width: 37px; height: 37px; }
  .fx-logo-name { max-width: 130px; font-size: 17px; }
  .fx-location { max-width: 142px; padding: 6px 7px; border: 0; background: transparent; }
  .fx-location-chevron { display: none; }
  .fx-location-value { max-width: 100px; }
  .fx-cart-btn { width: 42px; height: 42px; border-radius: 14px; }
  .fx-nav { top: 103px; }
  .fx-hero { padding: 40px 18px 46px; }
  .fx-hero-inner { min-height: 0; }
  .fx-hero-title { font-size: clamp(45px, 14vw, 62px); }
  .fx-hero-subtitle { font-size: 15px; }
  .fx-hero-art { min-height: 245px; margin-top: 30px; }
  .fx-hero-art-placeholder { width: 190px; height: 190px; font-size: 19px; border-width: 8px; }
  .fx-hero-image { width: min(90%, 340px); max-height: 250px; }
  .fx-outlet-section { padding: 35px 16px 8px; }
  .fx-outlet-heading { display: block; margin-bottom: 18px; }
  .fx-outlet-heading .fx-view-all { margin-top: 12px; }
  .fx-location-grid { display: flex; gap: 10px; overflow-x: auto; padding: 2px 2px 12px; scrollbar-width: none; }
  .fx-location-grid::-webkit-scrollbar { display: none; }
  .fx-location-card { flex: 0 0 218px; min-height: 140px; }
  .fx-section { padding: 48px 16px !important; }
  .fx-grid { grid-template-columns: repeat(2, minmax(0,1fr)); gap: 12px; }
  .fx-card { border-radius: 19px; }
  .fx-card-body { padding: 12px; }
  .fx-card-name { font-size: 14px; }
  .fx-card-price { font-size: 15px; }
}

.fx-branches-page { background: #f8f7f2; }
.fx-branches-hero { padding: 72px 28px 76px; background: #0b4b38; color: #fff; text-align: center; }
.fx-branches-hero .fx-section-overline { color: #f5d675; }
.fx-branches-hero h1 { max-width: 760px; margin: 14px auto 13px; color: #fff; font-size: clamp(42px, 6vw, 74px); line-height: .96; letter-spacing: -.07em; }
.fx-branches-hero p { max-width: 520px; margin: 0 auto; color: rgba(255,255,255,.74); font-size: 17px; }
.fx-branches-list { max-width: 1240px; margin: 0 auto; padding: 65px 28px 85px; }
.fx-branches-list-head { display: flex; align-items: end; justify-content: space-between; gap: 20px; margin-bottom: 26px; }
.fx-branches-list-head h2 { margin: 0; color: var(--fx-green-deep); font-size: clamp(29px, 4vw, 48px); line-height: 1; letter-spacing: -.06em; }
.fx-branches-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 18px; }
.fx-branch-card { display: flex; flex-direction: column; min-height: 245px; padding: 22px; border: 1px solid rgba(16,38,28,.10); border-radius: 24px; background: #fff; box-shadow: 0 6px 0 rgba(7,59,44,.05); transition: transform .18s, border-color .18s, box-shadow .18s; }
.fx-branch-card:hover, .fx-branch-card.active { border-color: var(--fx-green); box-shadow: 0 16px 32px rgba(7,59,44,.11); transform: translateY(-3px); }
.fx-branch-card-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 22px; }
.fx-branch-pin { display: flex; align-items: center; justify-content: center; width: 43px; height: 43px; border-radius: 14px; background: #eef6e8; color: var(--fx-green); }
.fx-branch-status { color: #568b4a; font-size: 11px; font-weight: 800; }
.fx-branch-card h3 { margin: 0 0 7px; color: var(--fx-green-deep); font-size: 22px; letter-spacing: -.035em; }
.fx-branch-card address { min-height: 40px; color: var(--fx-muted); font-size: 13px; font-style: normal; line-height: 1.55; }
.fx-branch-actions { display: flex; align-items: center; gap: 10px; margin-top: auto; padding-top: 20px; }
.fx-branch-actions .fx-card-add { flex: 1; }
.fx-branch-phone { display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; border: 1px solid rgba(16,38,28,.12); border-radius: 14px; color: var(--fx-green); }
.fx-branches-empty { padding: 60px 25px; border: 1px dashed rgba(16,38,28,.18); border-radius: 24px; background: #fff; text-align: center; }
.fx-branches-empty h2 { margin: 0 0 8px; color: var(--fx-green-deep); }
.fx-branches-empty p { margin: 0 0 20px; color: var(--fx-muted); }
.fx-cat-img-placeholder span { display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; border: 1px solid rgba(30,107,64,.16); border-radius: 50%; background: rgba(255,255,255,.55); color: var(--fx-green-deep); font-family: 'Outfit', sans-serif; font-size: 15px; font-weight: 800; letter-spacing: .04em; }
.fx-benefits-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 24px; text-align: center; }
.fx-benefits-grid > div { padding: 24px; }
.fx-benefit-icon { width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; border-radius: 20px; background: #eef6e8; color: var(--fx-green); }
.fx-empty-mark { display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; margin-bottom: 16px; border-radius: 20px; background: #eef6e8; color: var(--fx-green); }
.fx-service-mark { color: var(--fx-green-deep); font-size: 11px; font-weight: 900; letter-spacing: .12em; }
@media (max-width: 760px) {
  .fx-branches-hero { padding: 54px 18px 58px; }
  .fx-branches-hero p { font-size: 15px; }
  .fx-branches-list { padding: 48px 16px 65px; }
  .fx-branches-list-head { display: block; }
  .fx-branches-list-head .fx-view-all { display: inline-flex; margin-top: 14px; }
  .fx-branches-grid { grid-template-columns: 1fr; gap: 12px; }
  .fx-benefits-grid { grid-template-columns: 1fr; gap: 0; }
}

/* Premium restaurant direction. The storefront is intentionally restrained:
   less app chrome, fewer rounded surfaces, stronger type and real editorial
   hierarchy. Ordering remains available without making the page look like a
   generic marketplace. */
:root {
  --fx-cream: #f3efe7;
  --fx-cream-deep: #e7dfd1;
  --fx-white: #fbfaf6;
  --fx-green: #9e4d35;
  --fx-green-deep: #1b1d18;
  --fx-green-soft: #e5e4d9;
  --fx-gold: #b19564;
  --fx-yellow: #d9be82;
  --fx-ink: #1b1d18;
  --fx-muted: #77736a;
  --fx-border: rgba(27,29,24,.18);
  --fx-shadow: none;
  --fx-radius: 0;
  --fx-radius-sm: 0;
}
body.theme-food_express { background: var(--fx-cream); color: var(--fx-ink); font-family: 'DM Sans', sans-serif; }
body.theme-food_express h1,
body.theme-food_express h2,
body.theme-food_express h3,
body.theme-food_express h4,
body.theme-food_express .fx-logo-name { font-family: 'Cormorant Garamond', Georgia, serif; font-weight: 600; }
.fx-announcement { background: var(--fx-green-deep) !important; padding: 9px 18px !important; color: var(--fx-cream) !important; font-size: 10px !important; letter-spacing: .16em; text-transform: uppercase; }
.fx-header { position: relative; background: var(--fx-cream); border-bottom: 1px solid var(--fx-border); box-shadow: none; backdrop-filter: none; }
.fx-header-inner { max-width: 1320px; padding: 20px 34px 18px; }
.fx-header-top { display: grid; grid-template-columns: auto 1fr auto; gap: 28px; }
.fx-logo { gap: 12px; }
.fx-logo-img { width: 42px; height: 42px; border-radius: 0; }
.fx-logo-name { color: var(--fx-green-deep); font-size: 28px; line-height: .8; letter-spacing: -.04em; }
.fx-logo-tagline { margin-top: 5px; color: var(--fx-muted); font-size: 9px; letter-spacing: .17em; }
.fx-header-links { justify-content: flex-end; gap: 30px; }
.fx-header-links a { color: var(--fx-ink); font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
.fx-header-links a:first-child, .fx-header-links a:hover { color: var(--fx-green); }
.fx-location { order: -1; justify-self: start; padding: 0 0 0 24px; border-left: 1px solid var(--fx-border); border-radius: 0; background: transparent; color: var(--fx-muted); }
.fx-location:hover { border-color: var(--fx-green); background: transparent; }
.fx-location svg { color: var(--fx-green); }
.fx-location-label { font-size: 9px; letter-spacing: .16em; }
.fx-location-value { max-width: 180px; color: var(--fx-ink); font-size: 11px; font-weight: 600; }
.fx-header-actions { justify-self: end; gap: 24px; }
.fx-header-account { color: var(--fx-ink); font-size: 11px; letter-spacing: .12em; text-transform: uppercase; }
.fx-header-account svg { width: 18px; height: 18px; }
.fx-cart-btn { width: auto; height: auto; padding: 10px 0 10px 22px; border-left: 1px solid var(--fx-border); border-radius: 0; background: transparent; color: var(--fx-ink) !important; box-shadow: none; }
.fx-cart-btn:hover { transform: none; box-shadow: none; color: var(--fx-green) !important; }
.fx-cart-count { top: -2px; right: -10px; width: 16px; min-width: 16px; height: 16px; padding: 0; border: 0; border-radius: 50%; background: var(--fx-green); color: #fff; font-size: 9px; }
.fx-nav { position: relative; top: auto; padding: 0; background: var(--fx-cream); border-bottom: 1px solid var(--fx-border); }
.fx-nav-inner { max-width: 1320px; padding: 0 34px; gap: 26px; }
.fx-nav-link { padding: 13px 0 12px; border: 0; border-bottom: 1px solid transparent; border-radius: 0; background: transparent; color: var(--fx-muted); font-size: 10px; letter-spacing: .12em; text-transform: uppercase; box-shadow: none; }
.fx-nav-link.active, .fx-nav-link:hover { border-color: var(--fx-green); background: transparent; color: var(--fx-green); }
.fx-nav-link svg { display: none; }

.fx-hero { padding: 0 34px; background: var(--fx-cream) !important; color: var(--fx-ink); }
.fx-hero::before, .fx-hero::after { display: none; }
.fx-hero-inner { max-width: 1320px; min-height: 640px; grid-template-columns: minmax(0,.88fr) minmax(400px,1.12fr); gap: 74px; align-items: stretch; }
.fx-hero-copy { align-self: center; max-width: 610px; padding: 60px 0 66px; }
.fx-hero-kicker { margin-bottom: 25px; color: var(--fx-green); font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; }
.fx-hero-title { max-width: 620px; margin: 0 0 24px; color: var(--fx-green-deep) !important; font-size: clamp(64px, 7vw, 112px); font-weight: 600; line-height: .82; letter-spacing: -.07em; }
.fx-hero-subtitle { max-width: 435px; margin: 0 0 34px; color: var(--fx-muted); font-size: 16px; line-height: 1.7; }
.fx-hero-actions { display: flex; align-items: center; gap: 24px; }
.fx-hero-primary { display: inline-flex; align-items: center; gap: 22px; padding: 15px 18px; background: var(--fx-green-deep); color: var(--fx-cream) !important; font-size: 10px; font-weight: 700; letter-spacing: .14em; text-decoration: none; text-transform: uppercase; }
.fx-hero-primary:hover { background: var(--fx-green); }
.fx-hero-secondary { padding-bottom: 5px; border-bottom: 1px solid var(--fx-green-deep); color: var(--fx-green-deep) !important; font-size: 11px; font-weight: 700; letter-spacing: .12em; text-decoration: none; text-transform: uppercase; }
.fx-hero-note { display: flex; align-items: center; gap: 11px; margin-top: 54px; color: var(--fx-muted); font-size: 11px; }
.fx-hero-rule { display: inline-block; width: 34px; height: 1px; background: var(--fx-green); }
.fx-hero-art { min-height: 640px; margin-right: -34px; }
.fx-hero-art::before, .fx-hero-art::after { display: none; }
.fx-hero-art picture { width: 100%; height: 100%; display: block; }
.fx-hero-image { width: 100%; height: 640px; max-height: none; object-fit: cover; border: 0; border-radius: 0; transform: none; box-shadow: none; }
.fx-hero-art-placeholder { width: 100%; height: 640px; border: 0; border-radius: 0; background: var(--fx-green-deep); color: var(--fx-cream); transform: none; box-shadow: none; display: flex; flex-direction: column; align-items: flex-start; justify-content: flex-end; padding: 44px; text-align: left; }
.fx-hero-art-placeholder span { max-width: 360px; font-family: 'Cormorant Garamond', Georgia, serif; font-size: 74px; line-height: .82; letter-spacing: -.06em; }
.fx-hero-art-placeholder small { margin-top: 15px; color: var(--fx-yellow); font-size: 10px; letter-spacing: .18em; text-transform: uppercase; }
.fx-hero-caption { position: absolute; right: 20px; bottom: 20px; color: rgba(255,255,255,.9); font-size: 9px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; writing-mode: vertical-rl; }

.fx-outlet-section { padding: 24px 34px 0; background: var(--fx-cream); }
.fx-outlet-section-inner { max-width: 1320px; border-top: 1px solid var(--fx-border); padding: 28px 0 0; }
.fx-outlet-heading { align-items: baseline; margin-bottom: 18px; }
.fx-outlet-heading h2 { max-width: none; margin: 0 0 5px; color: var(--fx-green-deep); font-size: clamp(30px, 4vw, 54px); font-weight: 600; letter-spacing: -.06em; }
.fx-outlet-heading p { color: var(--fx-muted); font-size: 12px; }
.fx-location-grid { grid-template-columns: repeat(4, minmax(0,1fr)); gap: 0; border-top: 1px solid var(--fx-border); }
.fx-location-card { min-height: 120px; padding: 17px 18px 14px 0; border: 0; border-right: 1px solid var(--fx-border); border-radius: 0; background: transparent; box-shadow: none; }
.fx-location-card:not(:first-child) { padding-left: 18px; }
.fx-location-card:last-child { border-right: 0; }
.fx-location-card:hover, .fx-location-card.active { border-color: var(--fx-border); background: transparent; box-shadow: none; transform: none; }
.fx-location-card-pin { width: auto; height: auto; margin-bottom: 22px; border-radius: 0; background: transparent; color: var(--fx-green); }
.fx-location-card-copy strong { color: var(--fx-green-deep); font-family: 'Cormorant Garamond', Georgia, serif; font-size: 22px; font-weight: 600; }
.fx-location-card-copy small { color: var(--fx-muted); font-size: 11px; }
.fx-location-card-action { padding-top: 12px; color: var(--fx-green); font-size: 10px; letter-spacing: .12em; text-transform: uppercase; }

.fx-story-section { padding: 92px 34px 96px; background: var(--fx-green-deep); color: var(--fx-cream); }
.fx-story-inner { max-width: 1040px; margin: 0 auto; }
.fx-story-label { margin-bottom: 44px; color: var(--fx-yellow); font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; }
.fx-story-copy { display: grid; grid-template-columns: 1.15fr .85fr; gap: 100px; align-items: end; }
.fx-story-copy h2 { margin: 0; color: var(--fx-cream); font-size: clamp(54px, 7vw, 98px); font-weight: 500; line-height: .83; letter-spacing: -.06em; }
.fx-story-copy h2 em { color: var(--fx-yellow); font-style: italic; }
.fx-story-copy p { margin: 0 0 28px; color: rgba(243,239,231,.72); font-size: 16px; line-height: 1.75; }
.fx-text-link { color: var(--fx-cream) !important; font-size: 11px; font-weight: 700; letter-spacing: .15em; text-decoration: none; text-transform: uppercase; }
.fx-text-link span { color: var(--fx-yellow); font-size: 18px; margin-left: 9px; }

.fx-section { padding: 92px 34px !important; }
.fx-section-inner { max-width: 1320px; }
.fx-section-header { margin-bottom: 33px; }
.fx-section-overline { color: var(--fx-green); font-size: 10px; letter-spacing: .18em; }
.fx-section-title { color: var(--fx-green-deep); font-size: clamp(42px, 5vw, 68px); font-weight: 600; line-height: .88; letter-spacing: -.07em; }
.fx-view-all { color: var(--fx-green) !important; font-size: 10px; letter-spacing: .15em; text-transform: uppercase; }
.fx-categories { grid-template-columns: repeat(4, minmax(0,1fr)); gap: 24px; }
.fx-cat-img-wrap { aspect-ratio: 1.45; border: 0; border-radius: 0; background: var(--fx-cream-deep); box-shadow: none; }
.fx-cat-tile:hover .fx-cat-img-wrap, .fx-cat-tile.active .fx-cat-img-wrap { transform: none; box-shadow: none; }
.fx-cat-img-placeholder { background: var(--fx-cream-deep); color: var(--fx-green); }
.fx-cat-img-placeholder span { width: auto; height: auto; border: 0; border-radius: 0; background: transparent; font-family: 'Cormorant Garamond', Georgia, serif; font-size: 42px; font-weight: 600; }
.fx-cat-name { margin-top: 13px; color: var(--fx-green-deep); font-family: 'Cormorant Garamond', Georgia, serif; font-size: 23px; font-weight: 600; text-align: left; }
.fx-cat-count { text-align: left; font-size: 10px; letter-spacing: .1em; text-transform: uppercase; }
.fx-grid { grid-template-columns: repeat(4, minmax(0,1fr)); gap: 24px; }
.fx-card { border: 0; border-radius: 0; background: transparent; box-shadow: none; }
.fx-card:hover { transform: none; box-shadow: none; }
.fx-card-img-wrap { aspect-ratio: 1.08; background: var(--fx-cream-deep); }
.fx-card-img { transition: transform .45s ease; }
.fx-card:hover .fx-card-img { transform: scale(1.025); }
.fx-card-placeholder { background: var(--fx-cream-deep); color: var(--fx-green-deep); font-family: 'Cormorant Garamond', Georgia, serif; font-size: 28px; font-weight: 600; }
.fx-card-badge { top: 12px; left: 12px; border-radius: 0; padding: 7px 9px; background: var(--fx-green-deep); font-size: 9px; letter-spacing: .12em; text-transform: uppercase; }
.fx-card-body { padding: 16px 0 0; }
.fx-card-name { color: var(--fx-green-deep); font-size: 25px; font-weight: 600; line-height: .95; letter-spacing: -.025em; }
.fx-card-price { color: var(--fx-green); font-size: 14px; }
.fx-card-footer { margin-top: 15px; }
.fx-card-add { min-height: 38px; padding: 9px 12px; border: 1px solid var(--fx-green-deep); border-radius: 0; background: transparent; color: var(--fx-green-deep); font-size: 10px; letter-spacing: .12em; text-transform: uppercase; }
.fx-card-add:hover:not(:disabled) { background: var(--fx-green-deep); color: var(--fx-cream); }
.fx-card-wa { display: none; }
.fx-benefits-grid { gap: 0; border-top: 1px solid var(--fx-border); border-bottom: 1px solid var(--fx-border); text-align: left; }
.fx-benefits-grid > div { padding: 28px 28px 28px 0; border-right: 1px solid var(--fx-border); }
.fx-benefits-grid > div:not(:first-child) { padding-left: 28px; }
.fx-benefits-grid > div:last-child { border-right: 0; }
.fx-benefit-icon { width: auto; height: auto; margin: 0 0 22px; border-radius: 0; background: transparent; color: var(--fx-green); }
.fx-benefits-grid h3 { font-size: 28px !important; font-weight: 600 !important; }
.fx-benefits-grid p { color: var(--fx-muted) !important; font-size: 13px !important; }

.fx-footer { margin-top: 0; padding: 70px 34px 26px; background: var(--fx-green-deep); color: rgba(243,239,231,.65); }
.fx-footer-inner { max-width: 1320px; grid-template-columns: 1.8fr repeat(3,1fr); gap: 50px; }
.fx-footer-logo { color: var(--fx-cream); font-family: 'Cormorant Garamond', Georgia, serif; font-size: 34px; font-weight: 600; }
.fx-footer-tagline { color: var(--fx-yellow); font-size: 9px; letter-spacing: .18em; }
.fx-footer-col h4 { color: var(--fx-yellow); font-size: 9px; letter-spacing: .18em; }
.fx-footer-links { gap: 10px; }
.fx-footer-links a { color: rgba(243,239,231,.7); font-size: 12px; }
.fx-footer-bottom { max-width: 1320px; border-color: rgba(243,239,231,.18); }

@media (max-width: 980px) {
  .fx-header-top { grid-template-columns: auto 1fr auto; }
  .fx-location { order: 0; }
  .fx-hero-inner { grid-template-columns: 1fr 1fr; gap: 35px; }
  .fx-hero-art, .fx-hero-image, .fx-hero-art-placeholder { min-height: 560px; height: 560px; }
  .fx-grid { grid-template-columns: repeat(3, minmax(0,1fr)); }
  .fx-categories { grid-template-columns: repeat(4, minmax(0,1fr)); }
}
@media (max-width: 680px) {
  .fx-header-inner { padding: 14px 18px; }
  .fx-header-top { display: flex; gap: 14px; }
  .fx-mobile-menu-btn { display: inline-flex; order: -1; border: 0; border-radius: 0; background: transparent; }
  .fx-logo-img { width: 34px; height: 34px; }
  .fx-logo-name { font-size: 24px; }
  .fx-logo-tagline { display: none; }
  .fx-location { display: none; }
  .fx-header-links, .fx-header-account { display: none; }
  .fx-header-actions { margin-left: auto; }
  .fx-cart-btn { padding-left: 14px; }
  .fx-nav-inner { padding: 0 18px; gap: 22px; }
  .fx-nav-link { padding: 11px 0 10px; }
  .fx-hero { padding: 0 18px; }
  .fx-hero-inner { display: flex; flex-direction: column; gap: 0; min-height: 0; }
  .fx-hero-copy { padding: 54px 0 48px; }
  .fx-hero-kicker { margin-bottom: 21px; }
  .fx-hero-title { max-width: 340px; font-size: clamp(58px, 18vw, 82px); }
  .fx-hero-subtitle { font-size: 15px; }
  .fx-hero-actions { gap: 18px; }
  .fx-hero-primary { padding: 14px 14px; }
  .fx-hero-note { margin-top: 34px; }
  .fx-hero-art { min-height: 420px; height: 420px; margin: 0 -18px; }
  .fx-hero-image, .fx-hero-art-placeholder { min-height: 420px; height: 420px; }
  .fx-hero-art-placeholder { padding: 28px; }
  .fx-hero-art-placeholder span { font-size: 58px; }
  .fx-hero-caption { right: 14px; bottom: 14px; }
  .fx-outlet-section { padding: 22px 18px 0; }
  .fx-outlet-section-inner { padding-top: 25px; }
  .fx-outlet-heading { display: block; }
  .fx-outlet-heading h2 { font-size: 42px; }
  .fx-location-grid { display: flex; overflow-x: auto; }
  .fx-location-card, .fx-location-card:not(:first-child) { flex: 0 0 210px; min-height: 115px; padding: 14px 16px 13px 0; }
  .fx-location-card:not(:first-child) { padding-left: 16px; }
  .fx-story-section { padding: 64px 18px 68px; }
  .fx-story-label { margin-bottom: 32px; }
  .fx-story-copy { display: block; }
  .fx-story-copy h2 { margin-bottom: 34px; font-size: 68px; }
  .fx-section { padding: 64px 18px !important; }
  .fx-section-title { font-size: 48px; }
  .fx-section-header { align-items: baseline; }
  .fx-categories { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 24px 14px; }
  .fx-cat-name { font-size: 22px; }
  .fx-grid { grid-template-columns: repeat(2, minmax(0,1fr)); gap: 24px 14px; }
  .fx-card-name { font-size: 22px; }
  .fx-card-body { padding-top: 13px; }
  .fx-benefits-grid { display: block; }
  .fx-benefits-grid > div, .fx-benefits-grid > div:not(:first-child) { padding: 24px 0; border-right: 0; border-bottom: 1px solid var(--fx-border); }
  .fx-benefits-grid > div:last-child { border-bottom: 0; }
  .fx-footer { padding: 54px 18px 24px; }
  .fx-footer-inner { grid-template-columns: 1fr 1fr; gap: 30px 20px; }
  .fx-footer-brand, .fx-footer-col:last-child { grid-column: 1 / -1; }
}

/* Refinements from mobile and desktop review. */
.fx-header-top { grid-template-columns: auto minmax(160px, 1fr) auto auto auto; }
.fx-header-actions { display: contents; }
.fx-location { grid-column: 3; order: initial; justify-self: end; }
.fx-header-links { grid-column: 2; grid-row: 1; justify-self: center; }
.fx-header-account { grid-column: 4; }
.fx-cart-btn { grid-column: 5; }
.fx-header-search { position: relative; width: 100%; }
.fx-header-search input { display: block; width: 100%; padding-right: 18px; }
.fx-search-icon { z-index: 1; }

/* The outlet chooser reads as a promotional strip on desktop, not a row of
   dashboard cards. */
.fx-outlet-section { padding-top: 0; padding-bottom: 0; background: #cbb98e; }
.fx-outlet-section-inner { padding: 38px 0 0; border-top: 0; }
.fx-outlet-heading h2 { color: var(--fx-green-deep); }
.fx-outlet-heading p { color: rgba(27,29,24,.68); }
.fx-location-grid { border-color: rgba(27,29,24,.28); }
.fx-location-card { border-color: rgba(27,29,24,.28); }
.fx-location-card:hover, .fx-location-card.active { background: rgba(255,255,255,.18); border-color: rgba(27,29,24,.28); }
.fx-location-card-copy strong { color: var(--fx-green-deep); }
.fx-location-card-action { color: var(--fx-green-deep); }

/* The story is a full-width campaign band with left-aligned content. */
.fx-story-section { min-height: 380px; background: var(--fx-green); }
.fx-story-inner { max-width: 1320px; }
.fx-story-copy { grid-template-columns: 1.3fr .7fr; gap: 90px; }

.fx-footer-social-top { margin-top: 22px; justify-content: flex-start; }
.fx-footer-bottom { justify-content: flex-start; }
.fx-footer-legal { display: flex; flex-direction: column; gap: 5px; }
.fx-footer-legal small { color: rgba(243,239,231,.4); font-size: 10px; letter-spacing: .12em; text-transform: uppercase; }
.fx-footer-social-bottom { display: none; }

@media (max-width: 980px) {
  .fx-header-top { display: flex; }
  .fx-header-actions { display: flex; }
  .fx-header-links { display: none; }
  .fx-location { margin-left: auto; }
}
@media (max-width: 680px) {
  .fx-header-actions { display: flex; }
  .fx-header-search { display: block; margin-top: 14px; }
  .fx-header-search input { min-height: 48px; padding: 13px 16px 13px 46px; border-radius: 0; border-color: var(--fx-border); box-shadow: none; }
  .fx-search-icon { left: 15px; width: 19px; height: 19px; }

  .fx-hero-copy { text-align: center; }
  .fx-hero-title, .fx-hero-subtitle { margin-left: auto; margin-right: auto; }
  .fx-hero-actions { justify-content: center; }
  .fx-hero-note { justify-content: center; }

  .fx-outlet-section { padding: 0 18px; }
  .fx-outlet-section-inner { padding: 30px 0 34px; }
  .fx-outlet-heading h2 { font-size: 39px; }
  .fx-location-grid { display: grid; grid-template-columns: 1fr; gap: 0; overflow: visible; }
  .fx-location-card, .fx-location-card:not(:first-child) { width: 100%; min-height: 0; padding: 17px 0; border-right: 0; border-bottom: 1px solid rgba(27,29,24,.28); }
  .fx-location-card:last-child { border-bottom: 0; }
  .fx-location-card-pin { margin-bottom: 9px; }
  .fx-location-card-copy small { white-space: normal; }
  .fx-location-card-action { padding-top: 10px; }

  .fx-story-section { min-height: 0; padding: 58px 18px 62px; }
  .fx-story-copy h2 { font-size: 64px; text-align: left; }
  .fx-story-copy p { font-size: 15px; }

  .fx-benefits-grid { text-align: center; }
  .fx-benefit-icon { margin-left: auto; margin-right: auto; }
  .fx-footer-social-top { justify-content: center; }
  .fx-footer-bottom { justify-content: center; text-align: center; }
  .fx-footer-legal { align-items: center; }
}

/* Menu board treatment: smaller plate-like categories and ruled dish rows. */
.fx-nav-all-mark { position: relative; display: inline-block; width: 16px; height: 16px; border: 1px solid currentColor; border-radius: 50%; }
.fx-nav-all-mark::after { content: ''; position: absolute; left: 3px; right: 3px; bottom: 3px; height: 1px; background: currentColor; }
.fx-categories { grid-template-columns: repeat(6, minmax(0,1fr)); gap: 28px; }
.fx-cat-img-wrap { width: min(100%, 126px); margin: 0 auto; aspect-ratio: 1; border: 8px solid var(--fx-white); border-radius: 50%; box-shadow: 0 0 0 1px rgba(27,29,24,.18); }
.fx-cat-name { margin-top: 13px; font-size: 19px; text-align: center; }
.fx-cat-count { text-align: center; }
.fx-cat-tile:hover .fx-cat-img-wrap, .fx-cat-tile.active .fx-cat-img-wrap { transform: translateY(-2px); box-shadow: 0 0 0 1px var(--fx-green), 0 8px 18px rgba(27,29,24,.10); }
.fx-menu-rule { display: flex; align-items: baseline; justify-content: space-between; gap: 20px; margin: 20px 0 0; padding: 16px 0 12px; border-top: 1px solid var(--fx-border); border-bottom: 1px solid var(--fx-border); color: var(--fx-green-deep); font-family: 'Cormorant Garamond', Georgia, serif; font-size: 28px; font-weight: 600; }
.fx-menu-rule small { color: var(--fx-muted); font-family: 'DM Sans', sans-serif; font-size: 10px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
.fx-menu-list { border-bottom: 1px solid var(--fx-border); }
.fx-menu-item { display: grid; grid-template-columns: 164px minmax(0,1fr) auto; gap: 26px; align-items: center; padding: 22px 0; border-bottom: 1px solid var(--fx-border); }
.fx-menu-item:last-child { border-bottom: 0; }
.fx-menu-item-image { position: relative; display: block; width: 164px; aspect-ratio: 1.2; overflow: hidden; background: var(--fx-cream-deep); }
.fx-menu-item-image .fx-card-img { display: block; width: 100%; height: 100%; object-fit: cover; }
.fx-menu-item-image .fx-card-placeholder { font-size: 24px; }
.fx-menu-item-body { min-width: 0; }
.fx-menu-item-body .fx-card-name { margin-bottom: 8px; font-size: 31px; }
.fx-menu-item-description { max-width: 570px; margin: 0 0 14px; color: var(--fx-muted); font-size: 13px; line-height: 1.55; }
.fx-menu-item-body .fx-card-price-row { margin-bottom: 0; }
.fx-menu-item-action { justify-self: end; }
.fx-menu-item-action .fx-card-add { min-width: 124px; }

/* Give the desktop location band and campaign strip enough presence to read
   as sections rather than thin separators. */
@media (min-width: 981px) {
  .fx-outlet-section-inner { padding-bottom: 58px; }
  .fx-location-card { min-height: 168px; }
  .fx-story-section { min-height: 470px; padding-top: 112px; padding-bottom: 112px; }
}

@media (max-width: 980px) {
  .fx-categories { grid-template-columns: repeat(4, minmax(0,1fr)); }
}

@media (min-width: 681px) {
  .fx-footer-bottom { justify-content: space-between; }
  .fx-footer-legal { flex-direction: row; align-items: center; justify-content: space-between; width: 100%; }
}

@media (max-width: 680px) {
  .fx-categories { grid-template-columns: repeat(4, minmax(0,1fr)); gap: 18px 10px; }
  .fx-cat-img-wrap { width: min(100%, 78px); border-width: 5px; }
  .fx-cat-name { margin-top: 9px; font-size: 16px; }
  .fx-cat-count { font-size: 9px; }
  .fx-menu-rule { margin-top: 12px; padding: 14px 0 10px; font-size: 25px; }
  .fx-menu-item { grid-template-columns: 96px minmax(0,1fr); gap: 14px; align-items: start; padding: 17px 0; }
  .fx-menu-item-image { width: 96px; aspect-ratio: 1; grid-row: 1 / span 2; }
  .fx-menu-item-body .fx-card-name { margin: 1px 0 6px; font-size: 24px; }
  .fx-menu-item-description { display: -webkit-box; overflow: hidden; margin-bottom: 8px; font-size: 12px; line-height: 1.4; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
  .fx-menu-item-action { grid-column: 2; justify-self: start; margin-top: -2px; }
  .fx-menu-item-action .fx-card-add { min-width: 0; min-height: 34px; padding: 8px 10px; font-size: 9px; }
}

/* Dish detail heat selector: a quiet selection list, not a row of generic
   app buttons. The existing data-spice values remain the cart contract. */
.fx-spice-levels { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 0; border-top: 1px solid var(--fx-border); border-left: 1px solid var(--fx-border); }
.fx-spice-btn { display: grid; grid-template-columns: 30px minmax(0,1fr); grid-template-rows: auto auto; align-items: center; min-height: 68px; padding: 11px 13px; border: 0; border-right: 1px solid var(--fx-border); border-bottom: 1px solid var(--fx-border); border-left: 3px solid transparent; border-radius: 0; background: transparent; color: var(--fx-ink); font-size: 0; font-weight: 600; text-align: left; }
.fx-spice-btn svg { grid-column: 1; grid-row: 1 / span 2; width: 6px; height: 22px; margin: 0 2px 0 0 !important; color: var(--fx-cream-deep); }
.fx-spice-btn::before { grid-column: 2; grid-row: 1; content: attr(data-spice); color: var(--fx-green-deep); font-family: 'Cormorant Garamond', Georgia, serif; font-size: 21px; font-weight: 600; line-height: 1; text-transform: capitalize; }
.fx-spice-btn[data-spice="extra"]::before { content: 'Extra hot'; }
.fx-spice-btn::after { grid-column: 2; grid-row: 2; margin-top: 4px; color: var(--fx-muted); font-family: 'DM Sans', sans-serif; font-size: 10px; font-weight: 500; line-height: 1.2; }
.fx-spice-btn[data-spice="mild"]::after { content: 'Gentle warmth'; }
.fx-spice-btn[data-spice="medium"]::after { content: 'A little kick'; }
.fx-spice-btn[data-spice="hot"]::after { content: 'Full-bodied heat'; }
.fx-spice-btn[data-spice="extra"]::after { content: 'For heat lovers'; }
.fx-spice-btn.active { border-left-color: var(--fx-green); background: rgba(158,77,53,.07); }
.fx-spice-btn.active::before { color: var(--fx-green); }
.fx-spice-btn.active svg { color: var(--fx-green); }
@media (max-width: 680px) {
  .fx-spice-levels { grid-template-columns: 1fr; }
}

/* Product detail: a composed dish page instead of a generic product form. */
.fx-product-detail { display:grid; grid-template-columns:minmax(0,1.08fr) minmax(360px,500px); gap:clamp(42px,7vw,104px); align-items:start; }
.fx-product-gallery { min-width:0; }
.fx-product-hero-image { aspect-ratio:1 / 1; overflow:hidden; background:var(--fx-cream-deep); margin-bottom:12px; }
.fx-product-hero-image img { display:block; width:100%; height:100%; object-fit:cover; }
.fx-product-hero-image .fx-card-placeholder { width:100%; height:100%; }
.fx-product-thumbnails { display:flex; gap:8px; overflow-x:auto; padding-bottom:8px; scrollbar-width:none; }
.fx-product-thumbnails::-webkit-scrollbar { display:none; }
.fx-product-thumb { flex:0 0 82px; aspect-ratio:1; overflow:hidden; border:1px solid var(--fx-border); cursor:pointer; transition:border-color .18s ease, transform .18s ease; }
.fx-product-thumb:hover { border-color:var(--fx-green); transform:translateY(-2px); }
.fx-product-thumb img { display:block; width:100%; height:100%; object-fit:cover; }
.fx-product-info { position:sticky; top:100px; min-width:0; }
.fx-product-title { margin:0 0 13px !important; color:var(--fx-ink) !important; font-family:'Cormorant Garamond',Georgia,serif !important; font-size:clamp(48px,5.5vw,78px) !important; font-weight:600 !important; line-height:.86 !important; letter-spacing:-.055em; }
.fx-product-category { display:inline-block; margin-bottom:21px; padding:0 0 6px !important; border-bottom:1px solid var(--fx-green) !important; border-radius:0 !important; background:transparent !important; color:var(--fx-green) !important; font-size:10px !important; font-weight:700 !important; letter-spacing:.15em; line-height:1; text-transform:uppercase; }
.fx-product-description { max-width:500px; margin-bottom:22px; color:var(--fx-muted); font-size:14px; line-height:1.7; }
.fx-product-price { display:flex; align-items:baseline; gap:12px; flex-wrap:wrap; margin-bottom:24px; padding:20px 0 !important; border-top:1px solid var(--fx-border); border-bottom:1px solid var(--fx-border); }
.fx-product-price .fx-card-price { color:var(--fx-green-deep); font-family:'Cormorant Garamond',Georgia,serif; font-size:42px !important; font-weight:600; line-height:1; }
.fx-product-option { margin-bottom:28px !important; }
.fx-product-option > label { display:block; margin-bottom:11px; color:var(--fx-ink); font-size:10px; font-weight:700; letter-spacing:.14em; text-transform:uppercase; }
.fx-product-addons > div { display:flex; flex-direction:column; gap:0; border-top:1px solid var(--fx-border); }
.fx-product-addons > div > label { display:flex; align-items:center; justify-content:space-between; padding:13px 0 !important; border:0 !important; border-bottom:1px solid var(--fx-border) !important; border-radius:0 !important; background:transparent !important; cursor:pointer; }
.fx-product-addons > div > label > div { display:flex; flex-direction:column; gap:2px; }
.fx-product-addons > div > label > div span:first-child { color:var(--fx-ink) !important; font-size:14px !important; font-weight:600 !important; }
.fx-product-addons > div > label > div span:last-child { color:var(--fx-muted) !important; font-size:12px !important; }
.fx-product-addons .fx-addon-checkbox { width:18px; height:18px; accent-color:var(--fx-green); }
.fx-product-quantity { display:flex; align-items:center; gap:16px; flex-wrap:wrap; margin-bottom:16px; }
.fx-quantity-control { display:flex; align-items:center; border:1px solid var(--fx-border); border-radius:0; overflow:hidden; }
.fx-quantity-control button { width:44px; height:44px; display:flex; align-items:center; justify-content:center; border:0; background:var(--fx-cream-deep) !important; color:var(--fx-ink) !important; font-size:18px; }
.fx-quantity-control input { width:52px !important; height:44px !important; border:0 !important; background:var(--fx-white) !important; color:var(--fx-ink) !important; font-size:16px !important; font-weight:600; outline:none; text-align:center; }
.fx-product-add { width:100%; min-height:56px; padding:16px !important; border-radius:0 !important; background:var(--fx-green-deep) !important; color:var(--fx-cream) !important; font-size:14px !important; letter-spacing:.07em; text-transform:uppercase; }
.fx-product-add:hover { background:var(--fx-green) !important; }
.fx-product-whatsapp { width:100%; min-height:48px; margin-top:10px; padding:14px !important; border-radius:0 !important; }
.fx-product-meta { margin-top:25px; padding-top:17px; border-top:1px solid var(--fx-border); color:var(--fx-muted); font-size:12px; }
.fx-product-meta > div { display:flex; align-items:center; gap:8px; margin-bottom:7px; }
.fx-product-meta svg { color:var(--fx-green) !important; }
.fx-related-products { margin-top:68px; padding-top:34px; border-top:1px solid var(--fx-border); }
.fx-product-location { margin:0 0 28px; border-top:1px solid var(--fx-border); border-bottom:1px solid var(--fx-border); }
.fx-product-location summary { display:flex; align-items:center; gap:11px; min-height:58px; cursor:pointer; list-style:none; }
.fx-product-location summary::-webkit-details-marker { display:none; }
.fx-product-location-pin { display:inline-flex; color:var(--fx-green); }
.fx-product-location-copy { display:flex; flex:1; min-width:0; flex-direction:column; gap:3px; }
.fx-product-location-copy small { color:var(--fx-muted); font-size:9px; font-weight:700; letter-spacing:.14em; text-transform:uppercase; }
.fx-product-location-copy strong { overflow:hidden; color:var(--fx-ink); font-size:14px; text-overflow:ellipsis; white-space:nowrap; }
.fx-product-location-change { color:var(--fx-green); font-size:10px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; white-space:nowrap; }
.fx-product-location-chevron { color:var(--fx-muted); transition:transform .18s ease; }
.fx-product-location[open] .fx-product-location-chevron { transform:rotate(180deg); }
.fx-product-location-options { display:grid; gap:0; padding:0 0 10px 29px; }
.fx-product-location-option { display:flex; align-items:center; justify-content:space-between; gap:12px; width:100%; padding:12px 0; border:0; border-top:1px solid var(--fx-border); background:transparent; color:var(--fx-ink); cursor:pointer; text-align:left; }
.fx-product-location-option > span { display:flex; min-width:0; flex-direction:column; gap:3px; }
.fx-product-location-option strong { font-size:13px; font-weight:700; }
.fx-product-location-option small { color:var(--fx-muted); font-size:11px; }
.fx-product-location-option > svg { flex:0 0 auto; color:var(--fx-green); opacity:0; }
.fx-product-location-option.active > svg { opacity:1; }

/* Location page: editorial rows keep the restaurant experience out of the
   generic rounded-card look used by the old storefront. */
.fx-branches-page { background:var(--fx-cream); }
.fx-branches-hero { padding:clamp(76px,9vw,132px) 28px clamp(72px,8vw,118px); background:var(--fx-green-deep); color:var(--fx-cream); text-align:left; }
.fx-branches-hero-inner { display:grid; grid-template-columns:minmax(0,1fr) 280px; align-items:center; gap:clamp(42px,9vw,150px); max-width:1240px; margin:0 auto; }
.fx-branches-hero-copy { max-width:710px; }
.fx-branches-hero .fx-section-overline { color:var(--fx-gold); }
.fx-branches-hero h1 { max-width:700px; margin:16px 0 22px; color:var(--fx-cream); font-family:'Cormorant Garamond',Georgia,serif; font-size:clamp(64px,8.4vw,122px); font-weight:600; line-height:.82; letter-spacing:-.075em; }
.fx-branches-hero p { max-width:525px; margin:0; color:rgba(243,239,231,.76); font-size:16px; line-height:1.65; }
.fx-branches-hero-mark { position:relative; display:flex; min-height:230px; align-items:center; justify-content:center; color:var(--fx-gold); }
.fx-branches-hero-mark-ring { position:absolute; width:210px; height:210px; border:1px solid rgba(244,211,111,.45); border-radius:50%; }
.fx-branches-hero-mark-ring:before,.fx-branches-hero-mark-ring:after { position:absolute; content:''; border:1px solid rgba(244,211,111,.2); border-radius:50%; }
.fx-branches-hero-mark-ring:before { inset:20px; }
.fx-branches-hero-mark-ring:after { inset:47px; }
.fx-branches-hero-mark-pin { position:relative; display:flex; width:64px; height:64px; align-items:center; justify-content:center; border:1px solid var(--fx-gold); border-radius:50%; background:var(--fx-green-deep); }
.fx-branches-hero-mark-label { position:absolute; right:-2px; bottom:4px; color:rgba(243,239,231,.65); font-size:11px; letter-spacing:.08em; line-height:1.45; text-transform:uppercase; }
.fx-branches-hero-mark-label strong { color:var(--fx-cream); font-weight:600; }
.fx-branches-list { max-width:1240px; margin:0 auto; padding:clamp(64px,8vw,112px) 28px 120px; }
.fx-branches-list-head { display:flex; align-items:end; justify-content:space-between; gap:24px; margin-bottom:38px; padding-bottom:25px; border-bottom:1px solid var(--fx-border); }
.fx-branches-list-head h2 { margin:11px 0 0; color:var(--fx-ink); font-family:'Cormorant Garamond',Georgia,serif; font-size:clamp(40px,5vw,68px); font-weight:600; line-height:.88; letter-spacing:-.065em; }
.fx-branches-grid { display:grid; grid-template-columns:1fr; gap:0; }
.fx-branch-card { display:grid; grid-template-columns:70px minmax(0,1fr) minmax(260px,330px); gap:26px; align-items:center; min-height:190px; padding:28px 0; border:0; border-bottom:1px solid var(--fx-border); border-radius:0; background:transparent; box-shadow:none; transition:background .18s ease, padding .18s ease; }
.fx-branch-card:first-child { border-top:1px solid var(--fx-border); }
.fx-branch-card:hover,.fx-branch-card.active { padding-right:18px; padding-left:18px; background:rgba(255,255,255,.42); }
.fx-branch-index { align-self:start; padding-top:4px; color:var(--fx-green); font-size:11px; font-weight:800; letter-spacing:.12em; }
.fx-branch-card-main { min-width:0; }
.fx-branch-card-top { display:flex; align-items:center; gap:10px; margin-bottom:15px; }
.fx-branch-pin { display:inline-flex; color:var(--fx-green); }
.fx-branch-status { color:var(--fx-green); font-size:10px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
.fx-branch-card h3 { margin:0 0 8px; color:var(--fx-ink); font-family:'Cormorant Garamond',Georgia,serif; font-size:clamp(30px,3vw,43px); font-weight:600; line-height:.95; letter-spacing:-.045em; }
.fx-branch-card address { max-width:560px; color:var(--fx-muted); font-size:13px; font-style:normal; line-height:1.5; }
.fx-branch-actions { display:flex; align-items:center; gap:12px; justify-self:stretch; }
.fx-branch-actions .fx-card-add { flex:1; min-height:50px; justify-content:space-between; border-color:var(--fx-green-deep); border-radius:0; background:var(--fx-green-deep); color:var(--fx-cream); }
.fx-branch-actions .fx-card-add:hover { background:var(--fx-green); }
.fx-branch-phone { display:inline-flex; width:50px; height:50px; align-items:center; justify-content:center; border:1px solid var(--fx-border); border-radius:50%; color:var(--fx-green); }
.fx-branches-empty { max-width:680px; padding:56px 0; border:0; border-top:1px solid var(--fx-border); border-bottom:1px solid var(--fx-border); border-radius:0; background:transparent; text-align:left; }
.fx-branches-empty h2 { margin:0 0 10px; color:var(--fx-ink); font-family:'Cormorant Garamond',Georgia,serif; font-size:clamp(36px,4vw,56px); line-height:.92; letter-spacing:-.06em; }
.fx-branches-empty p { max-width:560px; margin:0 0 24px; color:var(--fx-muted); font-size:14px; line-height:1.65; }

@media (max-width:760px) {
  .fx-branches-hero { padding:58px 18px 62px; }
  .fx-branches-hero-inner { display:block; }
  .fx-branches-hero h1 { margin-top:14px; font-size:clamp(56px,17vw,76px); }
  .fx-branches-hero p { font-size:14px; }
  .fx-branches-hero-mark { min-height:118px; margin:18px 0 -8px; justify-content:flex-start; }
  .fx-branches-hero-mark-ring { width:142px; height:142px; }
  .fx-branches-hero-mark-pin { width:52px; height:52px; margin-left:45px; }
  .fx-branches-hero-mark-label { right:auto; left:170px; bottom:10px; }
  .fx-branches-list { padding:54px 18px 72px; }
  .fx-branches-list-head { display:block; margin-bottom:20px; padding-bottom:20px; }
  .fx-branches-list-head h2 { font-size:42px; }
  .fx-branches-list-head .fx-view-all { display:inline-flex; margin-top:18px; }
  .fx-branch-card { grid-template-columns:34px minmax(0,1fr); gap:14px; min-height:0; padding:25px 0; }
  .fx-branch-card:hover,.fx-branch-card.active { padding-right:10px; padding-left:10px; }
  .fx-branch-index { padding-top:4px; }
  .fx-branch-card-main { grid-column:2; }
  .fx-branch-card h3 { font-size:34px; }
  .fx-branch-actions { grid-column:2; width:100%; margin-top:10px; }
  .fx-branch-actions .fx-card-add { min-height:46px; }
  .fx-branch-phone { width:46px; height:46px; }
  .fx-branches-empty { padding:38px 0; }
}

@media (max-width: 680px) {
  .fx-product-detail { display:block; }
  .fx-product-gallery { margin:0 -18px 22px; }
  .fx-product-hero-image { aspect-ratio:1.18 / 1; margin-bottom:9px; }
  .fx-product-thumbnails { padding:0 18px 7px; }
  .fx-product-thumb { flex-basis:70px; }
  .fx-product-info { position:static; }
  .fx-product-title { max-width:100%; font-size:48px !important; line-height:.88 !important; }
  .fx-product-description { margin-bottom:14px; font-size:13px; line-height:1.55; }
  .fx-product-price { margin-bottom:17px; padding:14px 0 !important; }
  .fx-product-price .fx-card-price { font-size:36px !important; }
  .fx-product-option { margin-bottom:24px !important; }
  .fx-product-add { position:sticky; bottom:77px; z-index:10; box-shadow:0 8px 24px rgba(27,29,24,.18); }
  .fx-related-products { margin-top:48px; padding-top:28px; }
  .fx-related-products .fx-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
  .fx-product-location { margin-bottom:20px; }
  .fx-product-location summary { min-height:52px; }
  .fx-product-location-options { padding-left:29px; }
}

/* Final mobile app pass: keep the kitchen choice visible, reduce editorial
   desktop proportions, and give phone customers one clear path to order. */
@media (max-width: 680px) {
  body { padding-bottom: 92px; background: var(--fx-cream) !important; overflow-x: hidden; }
  .fx-header { border-bottom: 0; background: var(--fx-green-deep); }
  .fx-header-inner { padding: 14px 16px 10px; }
  .fx-header-top { display: grid !important; grid-template-columns: minmax(0,1fr) auto !important; grid-template-areas: 'brand actions' 'location location'; gap: 11px; }
  .fx-mobile-menu-btn { display: none !important; }
  .fx-logo { grid-area: brand; min-width: 0; gap: 9px; }
  .fx-logo-img { width: 36px; height: 36px; flex: 0 0 36px; }
  .fx-logo-text { min-width: 0; }
  .fx-logo-name { display: block; overflow: hidden; max-width: 205px; color: var(--fx-green-deep); font-family: 'DM Sans', 'Inter', sans-serif; font-size: 18px; font-weight: 900; letter-spacing: -.045em; text-overflow: ellipsis; white-space: nowrap; }
  .fx-logo-tagline { display: none; }
  .fx-header-actions { grid-area: actions; display: flex; align-items: center; gap: 8px; }
  .fx-header-links, .fx-header-account { display: none !important; }
  .fx-cart-btn { width: 42px; height: 42px; padding: 0; border: 0; border-radius: 14px; background: var(--fx-green-deep); }
  .fx-cart-amount { display: none; }
  .fx-location { grid-area: location; display: flex !important; justify-self: stretch !important; width: 100%; max-width: none !important; min-height: 50px; padding: 9px 12px !important; border: 0 !important; border-radius: 16px !important; background: var(--fx-green-soft) !important; text-align: left !important; }
  .fx-location svg { color: var(--fx-green); }
  .fx-location-copy { flex: 1 1 auto; }
  .fx-location-label { color: var(--fx-muted); font-size: 9px; letter-spacing: .12em; }
  .fx-location-value { display: block; max-width: none !important; color: var(--fx-ink); font-size: 13px; }
  .fx-location-chevron { display: block !important; margin-left: auto; }
  .fx-header-search { display: block !important; position: relative; width: 100%; margin: 2px 0 0; }
  .fx-header-search input { min-height: 50px; padding: 13px 14px 13px 44px; border: 1px solid rgba(16,34,27,.12); border-radius: 16px; background: #fff; box-shadow: none; font-size: 15px; }
  .fx-search-icon { left: 15px; width: 19px; height: 19px; color: var(--fx-muted); }
  .fx-nav { position: relative; top: auto; border-top: 0; border-bottom: 1px solid var(--fx-border); background: var(--fx-cream); }
  .fx-nav-inner { gap: 20px; padding: 9px 16px 11px; }
  .fx-nav-link { padding: 4px 0; color: var(--fx-muted); font-size: 11px; font-weight: 800; }
  .fx-nav-link.active { color: var(--fx-green); }

  .fx-hero { padding: 10px 16px 22px !important; background: var(--fx-cream) !important; }
  .fx-hero-inner { display: flex !important; flex-direction: column; gap: 0; min-height: 0 !important; }
  .fx-hero-copy { max-width: none; padding: 18px 0 16px !important; }
  .fx-hero-kicker { margin-bottom: 10px; color: var(--fx-green); font-size: 10px; letter-spacing: .13em; }
  .fx-hero-title { max-width: 330px; margin: 0 0 10px; color: var(--fx-green-deep) !important; font-family: 'DM Sans', 'Inter', sans-serif; font-size: clamp(42px, 12vw, 58px) !important; font-weight: 900; line-height: .91 !important; letter-spacing: -.075em; }
  .fx-hero-subtitle { max-width: 330px; margin-bottom: 18px; color: var(--fx-muted); font-size: 14px; line-height: 1.45; }
  .fx-hero-actions { display: flex; flex-wrap: wrap; gap: 11px; }
  .fx-hero-primary { padding: 14px 16px; border-radius: 14px; background: var(--fx-green-deep); font-size: 10px; letter-spacing: .09em; }
  .fx-hero-secondary { padding: 13px 0 4px; font-size: 10px; }
  .fx-hero-note { margin-top: 20px; font-size: 11px; }
  .fx-hero-art { min-height: 210px !important; height: 210px !important; margin: 0 !important; }
  .fx-hero-art::before, .fx-hero-art::after { display: none; }
  .fx-hero-image { width: 100%; height: 210px; max-height: none; border: 0; border-radius: 22px; transform: none; object-fit: cover; box-shadow: 0 14px 28px rgba(7,59,44,.14); }
  .fx-hero-art-placeholder { width: 100%; height: 210px; min-height: 210px; padding: 24px; border-radius: 22px; background: var(--fx-green-deep); }
  .fx-hero-art-placeholder span { font-family: 'DM Sans', 'Inter', sans-serif; font-size: 42px; }
  .fx-hero-caption { display: none; }

  .fx-outlet-section { padding: 18px 16px 8px !important; background: var(--fx-cream) !important; }
  .fx-outlet-section-inner { padding: 22px 0 24px !important; border-top: 1px solid var(--fx-border); }
  .fx-outlet-heading { display: block; margin-bottom: 15px; }
  .fx-outlet-heading h2 { max-width: 280px; margin: 5px 0 7px; color: var(--fx-green-deep); font-family: 'DM Sans', 'Inter', sans-serif; font-size: 25px; font-weight: 900; line-height: 1.02; letter-spacing: -.055em; }
  .fx-outlet-heading p { max-width: 310px; color: var(--fx-muted); font-size: 12px; line-height: 1.45; }
  .fx-location-grid { display: flex; gap: 12px; overflow-x: auto; padding: 2px 2px 12px; scrollbar-width: none; }
  .fx-location-grid::-webkit-scrollbar { display: none; }
  .fx-location-card, .fx-location-card:not(:first-child) { flex: 0 0 238px; min-height: 142px; padding: 16px !important; border: 1px solid var(--fx-border) !important; border-radius: 20px !important; background: #fff !important; box-shadow: 0 7px 0 rgba(7,59,44,.05) !important; }
  .fx-location-card:hover, .fx-location-card.active { border-color: var(--fx-green) !important; background: #fff !important; box-shadow: 0 9px 20px rgba(7,59,44,.10) !important; }
  .fx-location-card-pin { width: 34px; height: 34px; margin-bottom: 14px; border-radius: 11px; background: var(--fx-green-soft); }
  .fx-location-card-copy strong { font-family: 'DM Sans', 'Inter', sans-serif; font-size: 16px; }
  .fx-location-card-action { margin-top: auto; padding-top: 10px; font-size: 10px; }

  .fx-section { padding: 42px 16px !important; }
  .fx-section-header { margin-bottom: 19px; }
  .fx-section-title { color: var(--fx-green-deep); font-family: 'DM Sans', 'Inter', sans-serif; font-size: 31px !important; font-weight: 900; line-height: .98; letter-spacing: -.065em; }
  .fx-section-subtitle { margin-top: 8px; color: var(--fx-muted); font-size: 13px; }
  .fx-categories { display: flex !important; gap: 16px; overflow-x: auto; margin: 0 -16px 25px !important; padding: 0 16px 8px; scrollbar-width: none; }
  .fx-categories::-webkit-scrollbar { display: none; }
  .fx-cat-tile { flex: 0 0 78px; }
  .fx-cat-img-wrap { width: 78px; height: 78px; aspect-ratio: auto; border: 5px solid #fff; border-radius: 50%; background: var(--fx-cream-deep); box-shadow: 0 0 0 1px rgba(27,29,24,.12); }
  .fx-cat-name { margin-top: 9px; color: var(--fx-ink); font-family: 'DM Sans', 'Inter', sans-serif; font-size: 12px; font-weight: 800; text-align: center; }
  .fx-cat-count { display: none; }
  .fx-menu-rule { display: flex; align-items: baseline; justify-content: space-between; margin: 0 0 4px; padding-bottom: 11px; border-bottom: 1px solid var(--fx-border); color: var(--fx-green-deep); font-family: 'DM Sans', 'Inter', sans-serif; font-size: 20px; font-weight: 900; letter-spacing: -.04em; }
  .fx-menu-rule small { color: var(--fx-muted); font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
  .fx-menu-item { grid-template-columns: 92px minmax(0,1fr); gap: 14px; align-items: start; padding: 17px 0; }
  .fx-menu-item-image { width: 92px; aspect-ratio: 1; grid-row: 1 / span 2; border-radius: 16px; }
  .fx-menu-item-body .fx-card-name { margin: 1px 0 5px; color: var(--fx-ink); font-family: 'DM Sans', 'Inter', sans-serif; font-size: 18px; font-weight: 900; line-height: 1.02; }
  .fx-menu-item-description { display: -webkit-box; overflow: hidden; margin-bottom: 7px; color: var(--fx-muted); font-size: 12px; line-height: 1.35; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
  .fx-card-price { font-size: 15px; }
  .fx-menu-item-action { grid-column: 2; justify-self: start; margin-top: -1px; }
  .fx-menu-item-action .fx-card-add { min-width: 0; min-height: 34px; padding: 8px 12px; border-radius: 999px; background: var(--fx-green) !important; color: #fff !important; font-size: 10px; letter-spacing: 0; text-transform: none; }

  .fx-story-section { min-height: 0; padding: 54px 18px 58px; background: var(--fx-green-deep); }
  .fx-story-copy { display: block; }
  .fx-story-label { margin-bottom: 25px; }
  .fx-story-copy h2 { font-family: 'DM Sans', 'Inter', sans-serif; font-size: 46px; font-weight: 900; line-height: .91; }
  .fx-story-copy p { margin-top: 24px; font-size: 14px; line-height: 1.55; }

  .fx-product-detail { display: block; margin: 0 -16px; }
  .fx-product-gallery { margin: 0 0 0; }
  .fx-product-hero-image { height: 285px; aspect-ratio: auto; margin-bottom: 0; }
  .fx-product-hero-image .fx-main-img { width: 100%; height: 100%; object-fit: cover; }
  .fx-product-info { position: relative; z-index: 2; margin-top: -22px; padding: 25px 18px 105px; border-radius: 26px 26px 0 0; background: var(--fx-cream); }
  .fx-product-title { max-width: 100%; color: var(--fx-green-deep) !important; font-family: 'DM Sans', 'Inter', sans-serif; font-size: 34px !important; font-weight: 900; line-height: 1.02 !important; letter-spacing: -.06em; }
  .fx-product-category { display: inline-block; margin-top: 11px; color: var(--fx-green); font-size: 10px; font-weight: 900; letter-spacing: .13em; text-transform: uppercase; }
  .fx-product-description { margin: 12px 0 15px; color: var(--fx-muted); font-size: 14px; line-height: 1.5; }
  .fx-product-description:empty { display: none; }
  .fx-product-price { margin-bottom: 22px; padding: 14px 0 !important; border-top: 1px solid var(--fx-border); border-bottom: 1px solid var(--fx-border); }
  .fx-product-price .fx-card-price { color: var(--fx-green); font-size: 27px !important; }
  .fx-product-option { margin-bottom: 22px !important; }
  .fx-product-option > label { display: flex; justify-content: space-between; color: var(--fx-ink); font-size: 18px; font-weight: 900; }
  .fx-spice-levels { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 8px; margin-top: 10px; }
  .fx-spice-btn { min-height: 70px; padding: 8px 5px; border-radius: 16px; background: #fff; font-size: 11px; }
  .fx-spice-btn.active { background: var(--fx-green); }
  .fx-product-add { position: sticky; bottom: 77px; z-index: 10; width: 100%; min-height: 56px; border-radius: 17px; background: var(--fx-green) !important; color: #fff !important; box-shadow: 0 8px 24px rgba(7,59,44,.18); font-size: 14px; letter-spacing: 0; text-transform: none; }
  .fx-product-meta { margin-top: 24px; padding-top: 15px; border-top: 1px solid var(--fx-border); color: var(--fx-muted); font-size: 12px; }
  .fx-page-title { color: var(--fx-green-deep) !important; font-family: 'DM Sans', 'Inter', sans-serif; font-size: 32px !important; font-weight: 900 !important; letter-spacing: -.06em; }
  .fx-cart-empty { padding: 62px 20px !important; border: 1px solid var(--fx-border) !important; border-radius: 24px !important; background: #fffefa !important; box-shadow: 0 10px 26px rgba(16,23,20,.05); }
  .fx-cart-empty h2 { color: var(--fx-green-deep) !important; font-family: 'DM Sans', 'Inter', sans-serif; font-size: 24px !important; font-weight: 900 !important; letter-spacing: -.04em; }
  .fx-cart-layout { display: block !important; }
  .fx-cart-layout > div:first-child { overflow: visible !important; border: 0 !important; background: transparent !important; }
  .fx-cart-line { display: flex !important; margin-bottom: 12px; padding: 14px !important; border: 1px solid var(--fx-border) !important; border-radius: 20px; background: #fffefa; }
  .fx-cart-line h3 { color: var(--fx-ink) !important; font-family: 'DM Sans', 'Inter', sans-serif; font-size: 16px !important; font-weight: 900 !important; }
  .fx-cart-summary { position: static !important; margin-top: 16px; }
  .fx-cart-summary-card { padding: 20px !important; border: 1px solid var(--fx-border) !important; border-radius: 22px !important; background: #fffefa !important; }
  .fx-cart-summary-card .fx-card-add { min-height: 54px; border-radius: 16px; background: var(--fx-green) !important; color: #fff !important; font-size: 13px !important; letter-spacing: 0 !important; text-transform: none !important; }
}

/*
 * Restaurant storefronts are app-like before they are editorial.  The
 * previous mobile rules stopped at 680px, which left narrow browser frames
 * (and some tablets in portrait) using the desktop composition.  Keep the
 * same compact ordering surface through 820px so the breakpoint follows the
 * experience rather than a particular device width.
 */
@media (max-width: 820px) {
  body { padding-bottom: 92px; background: var(--fx-cream) !important; overflow-x: hidden; }

  .fx-header { border-bottom: 0; background: var(--fx-green-deep); }
  .fx-header-inner { padding: 14px 16px 10px; }
  .fx-header-top { display: grid !important; grid-template-columns: minmax(0,1fr) auto !important; grid-template-areas: 'brand actions' 'location location'; gap: 11px; }
  .fx-mobile-menu-btn { display: none !important; }
  .fx-logo { grid-area: brand; min-width: 0; gap: 9px; }
  .fx-logo-img { width: 36px; height: 36px; flex: 0 0 36px; }
  .fx-logo-text { min-width: 0; }
  .fx-logo-name { display: block; overflow: hidden; max-width: 260px; color: #fff; font-family: 'DM Sans', 'Inter', sans-serif !important; font-size: 16px !important; font-weight: 700 !important; letter-spacing: -.025em; text-overflow: ellipsis; white-space: nowrap; }
  .fx-header .fx-logo-name { color: #fff !important; }
  .fx-logo-tagline { display: none; }
  .fx-header-actions { grid-area: actions; display: flex; align-items: center; gap: 8px; }
  .fx-header-links, .fx-header-account { display: none !important; }
  .fx-cart-btn { display: inline-flex !important; align-items: center; justify-content: center; width: 42px !important; height: 42px !important; padding: 0 !important; border: 1px solid rgba(255,255,255,.25) !important; border-radius: 14px !important; background: rgba(255,255,255,.08) !important; color: #fff !important; }
  .fx-cart-btn svg { display: block; width: 21px; height: 21px; stroke: #fff; }
  .fx-cart-amount { display: none; }
  .fx-location { grid-area: location; display: flex !important; justify-self: stretch !important; width: 100%; max-width: none !important; min-height: 50px; padding: 9px 12px !important; border: 0 !important; border-radius: 16px !important; background: var(--fx-green-soft) !important; text-align: left !important; }
  .fx-location-copy { flex: 1 1 auto; }
  .fx-location-value { max-width: none !important; }
  .fx-location-chevron { display: block !important; margin-left: auto; }
  .fx-header-search { display: block !important; position: relative; width: 100%; margin: 2px 0 0; }
  .fx-header-search input { min-height: 50px; padding: 13px 14px 13px 44px; border: 1px solid rgba(16,34,27,.12); border-radius: 16px; background: #fff; box-shadow: none; font-size: 15px; }
  .fx-search-icon { left: 15px; width: 19px; height: 19px; color: var(--fx-muted); }

  .fx-nav { position: relative !important; top: auto !important; border-top: 0; border-bottom: 1px solid var(--fx-border); background: var(--fx-cream); overflow: hidden; }
  .fx-nav-inner { gap: 16px; padding: 9px 16px 11px; overflow-x: auto; scrollbar-width: none; white-space: nowrap; }
  .fx-nav-inner::-webkit-scrollbar { display: none; }
  .fx-nav-link { flex: 0 0 auto; padding: 4px 0; font-family: 'DM Sans', 'Inter', sans-serif !important; font-size: 11px !important; font-weight: 800; }

  .fx-hero { padding: 28px 18px 30px !important; background: #fff !important; }
  .fx-hero-inner { display: flex !important; flex-direction: column !important; gap: 0 !important; min-height: 0 !important; }
  .fx-hero-copy { width: 100% !important; max-width: none !important; align-self: stretch !important; padding: 0 0 24px !important; text-align: center !important; }
  .fx-hero-kicker, .fx-hero-note { display: none; }
  .fx-hero-title { max-width: 420px; margin: 0 auto 14px; color: var(--fx-green-deep) !important; font-family: 'DM Sans', 'Inter', sans-serif !important; font-size: clamp(36px, 9.5vw, 52px) !important; font-weight: 800 !important; line-height: 1.04 !important; letter-spacing: -.05em !important; text-wrap: balance; }
  .fx-hero-subtitle { max-width: 360px; margin: 0 auto 20px; font-size: 14px; line-height: 1.6; }
  .fx-hero-actions { justify-content: center; flex-wrap: wrap; gap: 16px; }
  .fx-hero-primary { border-radius: 14px; font-family: 'DM Sans', 'Inter', sans-serif !important; font-size: 10px; letter-spacing: .08em; }
  .fx-hero-note { margin-top: 20px; }
  .fx-hero-art { min-height: 0 !important; height: auto !important; margin: 0 !important; }
  .fx-hero-art::before, .fx-hero-art::after { display: none !important; }
  .fx-hero-image { display: block; width: 100%; height: clamp(230px, 65vw, 360px) !important; min-height: 0 !important; max-height: none; border: 0; border-radius: 20px; transform: none; object-fit: cover; box-shadow: none; }
  .fx-hero-art-placeholder { display: flex !important; flex-direction: column !important; align-items: flex-start !important; justify-content: flex-end !important; width: 100% !important; height: 170px !important; min-height: 170px !important; padding: 22px !important; border-radius: 22px !important; background: linear-gradient(135deg, #f6c94d 0%, #f4b933 52%, #0b4b38 52%, #0b4b38 100%) !important; color: #0b4b38 !important; }
  .fx-hero-art-placeholder span { max-width: 230px; color: #0b4b38 !important; font-family: 'DM Sans', 'Inter', sans-serif !important; font-size: 31px !important; font-weight: 900; line-height: .94; }
  .fx-hero-art-placeholder small { margin-top: 9px; color: #0b4b38 !important; font-family: 'DM Sans', 'Inter', sans-serif !important; font-size: 9px; letter-spacing: .16em; }
  .fx-hero-caption { display: none !important; }

  .fx-outlet-section { padding: 36px 18px !important; background: var(--fx-cream) !important; scroll-margin-top: 24px; }
  .fx-outlet-section-inner { max-width: 560px; padding: 0 !important; border: 0; }
  .fx-outlet-heading { display: block; margin-bottom: 24px; text-align: center; }
  .fx-outlet-heading h2 { max-width: 340px; margin: 10px auto 12px; color: var(--fx-green-deep); font-family: 'DM Sans', 'Inter', sans-serif !important; font-size: 29px !important; font-weight: 800 !important; line-height: 1.12 !important; letter-spacing: -.04em; text-wrap: balance; }
  .fx-outlet-heading p { max-width: 330px; margin: 0 auto; color: var(--fx-muted); font-size: 13px; line-height: 1.6; }
  .fx-view-all { display: inline-flex; margin-top: 14px; font-family: 'DM Sans', 'Inter', sans-serif !important; font-size: 10px; letter-spacing: .1em; }
  .fx-location-grid { display: grid !important; grid-template-columns: minmax(0,1fr); gap: 10px; overflow: visible; padding: 0; border: 0; }
  .fx-location-grid::-webkit-scrollbar { display: none; }
  .fx-location-card, .fx-location-card:not(:first-child) { display: grid; grid-template-columns: 36px minmax(0,1fr) auto; align-items: center; gap: 12px; width: 100%; min-height: 100px !important; padding: 18px 14px !important; border: 1px solid rgba(27,29,24,.1) !important; border-radius: 16px !important; background: #fff !important; box-shadow: none !important; }
  .fx-location-card:hover, .fx-location-card.active { border-color: var(--fx-green) !important; box-shadow: none !important; transform: none; }
  .fx-location-card:focus-visible { outline: 2px solid var(--fx-green); outline-offset: 3px; }
  .fx-location-card-pin { width: 36px; height: 36px; margin: 0; border-radius: 50%; background: var(--fx-cream); }
  .fx-location-card-copy strong { color: var(--fx-ink); font-family: 'DM Sans', 'Inter', sans-serif !important; font-size: 16px !important; white-space: normal; }
  .fx-location-card-copy small { display: block; max-width: none; overflow: visible; font-size: 12px; line-height: 1.5; white-space: normal; overflow-wrap: anywhere; }
  .fx-location-card-action { margin: 0; padding: 0; font-size: 9px; letter-spacing: .06em; }

  .fx-section { padding: 42px 16px !important; }
  .fx-section-title { color: var(--fx-green-deep); font-family: 'DM Sans', 'Inter', sans-serif !important; font-size: 31px !important; font-weight: 900 !important; line-height: .98 !important; letter-spacing: -.065em; }
  .fx-categories { display: flex !important; gap: 16px; overflow-x: auto; margin: 0 -16px 25px !important; padding: 0 16px 8px; scrollbar-width: none; }
  .fx-categories::-webkit-scrollbar { display: none; }
  .fx-cat-tile { flex: 0 0 78px; }
  .fx-cat-img-wrap { width: 78px; height: 78px; aspect-ratio: auto; border: 5px solid #fff; border-radius: 50%; }
  .fx-cat-name { font-family: 'DM Sans', 'Inter', sans-serif !important; font-size: 12px !important; text-align: center; }
  .fx-menu-item { grid-template-columns: 92px minmax(0,1fr); gap: 14px; align-items: start; padding: 17px 0; }
  .fx-menu-item-image { width: 92px; aspect-ratio: 1; grid-row: 1 / span 2; border-radius: 16px; }
  .fx-menu-item-body .fx-card-name { font-family: 'DM Sans', 'Inter', sans-serif !important; font-size: 18px !important; line-height: 1.02; }
  .fx-menu-item-action { grid-column: 2; justify-self: start; }
  .fx-menu-item-action .fx-card-add { min-width: 0; border-radius: 999px; background: var(--fx-green) !important; color: #fff !important; font-size: 10px; letter-spacing: 0; text-transform: none; }

  /* Alternate actual visible sections even when the merchant changes their order. */
  .fx-home-section--white { background: #fff; }
  .fx-home-section--cream { background: var(--fx-cream); }
  .fx-home-section > .fx-section,
  .fx-home-section > .fx-hero,
  .fx-home-section > .fx-outlet-section,
  .fx-home-section > .fx-story-section { background: transparent !important; }
  .fx-home-section .fx-story-section { min-height: 0; padding: 40px 18px; color: var(--fx-ink); text-align: center; }
  .fx-home-section .fx-story-copy { display: block; }
  .fx-home-section .fx-story-label { margin-bottom: 18px; color: var(--fx-green); }
  .fx-home-section .fx-story-copy h2 { margin: 0 auto 20px; color: var(--fx-ink); font-family: 'DM Sans', sans-serif; font-size: 38px; font-weight: 800; line-height: 1.1; text-align: center; letter-spacing: -.04em; }
  .fx-home-section .fx-story-copy h2 em { color: var(--fx-green); }
  .fx-home-section .fx-story-copy p { max-width: 360px; margin: 0 auto 22px; color: var(--fx-muted); font-size: 14px; line-height: 1.6; }
  .fx-home-section .fx-story-copy .fx-text-link { color: var(--fx-green) !important; }

  /* A centred footer at every mobile width, including portrait tablets. */
  .fx-footer { padding: 40px 20px 28px; text-align: center; }
  .fx-footer-inner { display: grid; grid-template-columns: minmax(0,1fr); gap: 28px; justify-items: center; }
  .fx-footer-col, .fx-footer-brand, .fx-footer-col:last-child { grid-column: 1; width: 100%; max-width: 420px; margin: 0 auto; text-align: center; }
  .fx-footer-brand img { display: block; margin-left: auto; margin-right: auto; }
  .fx-footer-logo { font-size: 30px; line-height: 1.15; }
  .fx-footer-desc { margin: 12px auto 0; }
  .fx-footer-social-top { justify-content: center; margin-top: 18px; }
  .fx-footer-social a { display: inline-flex; align-items: center; justify-content: center; min-width: 44px; min-height: 44px; }
  .fx-footer-col h4 { margin-bottom: 10px; font-family: 'DM Sans', sans-serif; font-size: 10px; }
  .fx-footer-links { flex-direction: row; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0 18px; }
  .fx-footer-links a { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; overflow-wrap: anywhere; }
  .fx-footer-bottom { display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; margin: 28px auto 0; padding-top: 22px; text-align: center; }
  .fx-footer-legal { display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; gap: 9px; text-align: center; }
  .fx-footer-legal > span { display: block; width: 100%; font-size: 11px; line-height: 1.7; }
  .fx-footer-legal > small { display: block; width: 100%; font-size: 10px; line-height: 1.6; text-align: center; }
}
</style>
