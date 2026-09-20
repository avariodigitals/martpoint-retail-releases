<?php
/**
 * Parfum Core — design system + header.
 * Loaded on every storefront page. Emits the skin fonts, the complete
 * mobile-first stylesheet, header markup, drawer, and the WhatsApp
 * order modal + JS shared by every page. Page views are markup only.
 */
include APPPATH . 'views/themes/parfum_core/_skin.php';

$slug     = $settings->store_slug ?? '';
$logo     = $logo_url ?? null;
$pfWaNum  = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
$tk       = $PF['theme_key'];
$announce = trim($settings->announcement_bar ?? '');
$centered = $PF['header_style'] === 'center';
?>
<link rel="stylesheet" href="<?= $PF['fonts_url']; ?>">
<style>
/* ============================================================
   PARFUM — light, modern commerce system
   Mobile-first; scales up at 640px / 960px / 1200px
   ============================================================ */
.theme-<?= $tk; ?> .mp-topbar,
.theme-<?= $tk; ?> .mp-announcement,
.theme-<?= $tk; ?> .mp-nav,
.theme-<?= $tk; ?> .mp-header,
.theme-<?= $tk; ?> .mp-mobile-menu-btn { display:none !important; }
.theme-<?= $tk; ?> .mp-footer-space { height:0 !important; }

body.theme-<?= $tk; ?> {
  background: <?= $PF['bg']; ?>;
  color: <?= $PF['ink']; ?>;
  font-family: <?= $PF['font_body']; ?>;
  -webkit-font-smoothing: antialiased;
  text-rendering: optimizeLegibility;
}

/* Shared chrome restyle (light) */
.theme-<?= $tk; ?> .mp-footer { background: <?= $PF['soft']; ?>; color: <?= $PF['muted']; ?>; }
.theme-<?= $tk; ?> .mp-footer-brand, .theme-<?= $tk; ?> .mp-footer-heading { color: <?= $PF['ink']; ?>; font-family: <?= $PF['font_display']; ?>; }
.theme-<?= $tk; ?> .mp-footer-links a { color: <?= $PF['muted']; ?>; }
.theme-<?= $tk; ?> .mp-footer-links a:hover { color: <?= $PF['accent']; ?>; }
.theme-<?= $tk; ?> .mp-footer-bottom { border-top-color: <?= $PF['border']; ?>; color: <?= $PF['muted']; ?>; }
.theme-<?= $tk; ?> .mp-footer-social a { background: <?= $PF['surface']; ?>; color: <?= $PF['ink']; ?>; border:1px solid <?= $PF['border']; ?>; }
.theme-<?= $tk; ?> .mp-footer-social a:hover { background: <?= $PF['accent']; ?>; color: <?= $PF['accent_ink']; ?>; border-color: <?= $PF['accent']; ?>; transform:translateY(-2px); }
.theme-<?= $tk; ?> .mp-mobile-nav { background:#fff; border-top-color: <?= $PF['border']; ?>; }
.theme-<?= $tk; ?> .mp-mobile-nav-item { color: <?= $PF['muted']; ?>; }
.theme-<?= $tk; ?> .mp-mobile-nav-item.active { color: <?= $PF['accent']; ?>; }
.theme-<?= $tk; ?> .mp-sticky-cart { background:#fff; border-top-color: <?= $PF['border']; ?>; box-shadow:0 -4px 20px rgba(0,0,0,.06); }
.theme-<?= $tk; ?> .mp-sticky-cart-items { color: <?= $PF['muted']; ?>; }
.theme-<?= $tk; ?> .mp-sticky-cart-total { color: <?= $PF['ink']; ?>; }
.theme-<?= $tk; ?> .mp-sticky-cart-btn { background: <?= $PF['accent']; ?>; color: <?= $PF['accent_ink']; ?>; border-radius: <?= $PF['btn_radius']; ?>; }
.theme-<?= $tk; ?> .mp-modal { background:#fff; color: <?= $PF['ink']; ?>; border:1px solid <?= $PF['border']; ?>; border-radius: <?= $PF['radius']; ?>; }
.theme-<?= $tk; ?> .mp-modal-title { color: <?= $PF['ink']; ?>; font-family: <?= $PF['font_display']; ?>; }
.theme-<?= $tk; ?> .mp-modal-add { background: <?= $PF['accent']; ?>; color: <?= $PF['accent_ink']; ?>; border-radius: <?= $PF['btn_radius']; ?>; }
.theme-<?= $tk; ?> .mp-toast { background: <?= $PF['ink']; ?>; color:#fff; }
.theme-<?= $tk; ?> .mp-backtop { background:#fff; color: <?= $PF['ink']; ?>; border-color: <?= $PF['border']; ?>; }

/* ---------- Layout ---------- */
.pf-wrap { width:100%; max-width:1200px; margin:0 auto; padding:0 16px; }
@media(min-width:960px){ .pf-wrap { padding:0 24px; } }

/* ---------- Type ---------- */
.pf-kicker { font-family: <?= $PF['font_kicker']; ?>; font-size:10px; font-weight:600; letter-spacing:.18em; text-transform:uppercase; color: <?= $PF['kicker_color']; ?>; }
.pf-h2 { font-family: <?= $PF['font_display']; ?>; font-size:clamp(24px,6.4vw,36px); font-weight:700; color: <?= $PF['ink']; ?>; margin:0; line-height:1.15; letter-spacing:-.01em; <?= $PF['display_italic'] ? 'font-style:italic;' : ''; ?> <?= $PF['display_case'] === 'uppercase' ? 'text-transform:uppercase;' : ''; ?> }
.pf-sec-sub { font-size:13px; color: <?= $PF['muted']; ?>; margin-top:6px; }

/* ---------- Buttons ---------- */
.pf-btn { display:inline-flex; align-items:center; justify-content:center; gap:9px; min-height:50px; padding:14px 28px; border-radius: <?= $PF['btn_radius']; ?>; font-family: <?= $PF['font_body']; ?>; font-size:13px; font-weight:600; letter-spacing:.02em; border:none; cursor:pointer; text-decoration:none; transition:opacity .2s, transform .15s, box-shadow .2s; }
.pf-btn:active { transform:scale(.97); }
.pf-btn-accent { background: <?= $PF['accent']; ?>; color: <?= $PF['accent_ink']; ?>; box-shadow:0 4px 14px <?= $PF['accent_soft']; ?>; }
.pf-btn-accent:hover { opacity:.92; }
.pf-btn-ink { background: <?= $PF['ink']; ?>; color: <?= $PF['bg']; ?>; }
.pf-btn-ghost { background:transparent; color: <?= $PF['ink']; ?>; border:1.5px solid <?= $PF['border']; ?>; }
.pf-btn-ghost:hover { border-color: <?= $PF['ink']; ?>; }
.pf-btn-wa-full { background:#25D366; color:#fff; box-shadow:0 4px 14px rgba(37,211,102,.35); }
.pf-btn-wa-full:hover { background:#1EBE5B; }
.pf-btn-wa-full svg { width:17px; height:17px; fill:currentColor; }
.pf-btn-row { display:flex; flex-wrap:wrap; gap:10px; }
.pf-btn-row .pf-btn { flex:1 1 45%; }
@media(min-width:640px){ .pf-btn-row .pf-btn { flex:0 1 auto; } }

/* ---------- Header ---------- */
<?php if($announce):
$announceBg = !empty($settings->announcement_bar_color) ? $settings->announcement_bar_color : $PF['ink'];
?>
.pfh-announce { background: <?= $announceBg; ?>; color: <?= $PF['bg']; ?>; text-align:center; padding:9px 16px; font-family: <?= $PF['font_kicker']; ?>; font-size:10px; letter-spacing:.14em; text-transform:uppercase; }
<?php endif; ?>
.pfh { position:sticky; top:0; z-index:200; background: <?= $PF['bg']; ?>EB; backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px); border-bottom:1px solid <?= $PF['border']; ?>; }
.pfh-bar { display:flex; align-items:center; justify-content:space-between; gap:8px; min-height:58px; }
.pfh-logo { display:flex; align-items:center; gap:10px; text-decoration:none; min-width:0; }
.pfh-logo img { max-height:32px; max-width:140px; object-fit:contain; }
.pfh-logo-name { font-family: <?= $PF['font_display']; ?>; font-size:19px; font-weight:700; color: <?= $PF['ink']; ?>; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; letter-spacing:-.01em; <?= $PF['display_italic'] ? 'font-style:italic;' : ''; ?> <?= $PF['display_case'] === 'uppercase' ? 'text-transform:uppercase; font-size:15px; letter-spacing:.08em;' : ''; ?> }
.pfh-actions { display:flex; align-items:center; gap:2px; }
.pfh-ic { width:44px; height:44px; display:flex; align-items:center; justify-content:center; color: <?= $PF['ink']; ?>; text-decoration:none; position:relative; background:none; border:none; cursor:pointer; border-radius:50%; transition:background .2s; }
.pfh-ic:hover { background: <?= $PF['soft']; ?>; }
.pfh-ic svg { width:20px; height:20px; stroke:currentColor; stroke-width:1.7; fill:none; stroke-linecap:round; stroke-linejoin:round; }
.pfh-ic svg.fill { fill:currentColor; stroke:none; }
.pfh-count { position:absolute; top:5px; right:5px; background: <?= $PF['accent']; ?>; color: <?= $PF['accent_ink']; ?>; font-size:10px; font-weight:700; min-width:16px; height:16px; border-radius:50%; display:flex; align-items:center; justify-content:center; padding:0 4px; }
.pfh-nav { display:none; }
@media(min-width:960px){
  .pfh-menu { display:none; }
  .pfh-bar { min-height:72px; <?= $centered ? 'flex-direction:column; justify-content:center; gap:12px; padding:14px 0 0; position:relative;' : ''; ?> }
  <?php if($centered): ?>
  .pfh-bar .pfh-logo { order:2; }
  .pfh-bar .pfh-actions { order:3; position:absolute; right:24px; top:16px; }
  .pfh-logo-name { font-size:27px; }
  .pfh-logo img { max-height:48px; max-width:210px; }
  .pfh-nav { display:flex; align-items:center; justify-content:center; gap:32px; width:100%; padding:12px 0 14px; order:4; }
  <?php else: ?>
  .pfh-nav { display:flex; align-items:center; gap:28px; flex:1; justify-content:center; }
  .pfh-logo-name { font-size:20px; }
  .pfh-logo img { max-height:42px; max-width:190px; }
  <?php endif; ?>
  .pfh-nav-link { font-family: <?= $PF['font_body']; ?>; font-size:13px; font-weight:500; letter-spacing:.01em; color: <?= $PF['muted']; ?>; text-decoration:none; transition:color .2s; }
  .pfh-nav-link:hover { color: <?= $PF['ink']; ?>; }
}

/* ---------- Drawer ---------- */
.pfh-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:900; }
.pfh-overlay.open { display:block; }
.pfh-drawer { position:fixed; top:0; left:0; bottom:0; width:310px; max-width:88vw; background:#fff; z-index:901; padding:22px 20px; overflow-y:auto; transform:translateX(-100%); transition:transform .28s ease; }
.pfh-drawer.open { transform:translateX(0); }
.pfh-drawer-title { font-family: <?= $PF['font_display']; ?>; font-size:19px; font-weight:700; color: <?= $PF['ink']; ?>; margin-bottom:16px; <?= $PF['display_italic'] ? 'font-style:italic;' : ''; ?> }
.pfh-drawer-link { display:block; padding:14px 0; font-family: <?= $PF['font_body']; ?>; font-size:14px; font-weight:500; color: <?= $PF['ink']; ?>; text-decoration:none; border-bottom:1px solid <?= $PF['border']; ?>; }
.pfh-drawer-sec-title { font-family: <?= $PF['font_kicker']; ?>; font-size:10px; letter-spacing:.18em; text-transform:uppercase; color: <?= $PF['kicker_color']; ?>; font-weight:600; margin:20px 0 4px; }
.pfh-drawer-wa { display:flex; align-items:center; justify-content:center; gap:8px; margin-top:22px; min-height:50px; background:#25D366; color:#fff; font-size:13px; font-weight:600; text-decoration:none; border-radius: <?= $PF['btn_radius']; ?>; }
.pfh-drawer-wa svg { width:18px; height:18px; fill:#fff; }

/* ---------- Hero: full-bleed banner, text overlaid ---------- */
.pf-hero { position:relative; }
.pf-hero-title { font-family: <?= $PF['font_display']; ?>; font-size:clamp(32px,8.6vw,60px); line-height:1.08; font-weight:600; margin:14px 0 16px; letter-spacing:-.015em; <?= $PF['display_italic'] ? 'font-style:italic;' : ''; ?> <?= $PF['display_case'] === 'uppercase' ? 'text-transform:uppercase; letter-spacing:-.02em; font-weight:800;' : ''; ?> }
.pf-hero-lead { font-size:15px; line-height:1.7; color: <?= $PF['muted']; ?>; max-width:520px; margin:0 0 26px; }

.pf-hero-full { position:relative; background: <?= $PF['soft']; ?>; padding:64px 0; overflow:hidden; }
.pf-hero-full.has-media { min-height:420px; display:flex; align-items:center; }
.pf-hero-full-bg { position:absolute; inset:0; }
.pf-hero-full-bg img { width:100%; height:100%; object-fit:cover; display:block; }
.pf-hero-full .pf-wrap { position:relative; z-index:2; width:100%; }
.pf-hero-full .pf-hero-body { max-width:640px; }
<?php if($PF['hero_align'] === 'center'): ?>
.pf-hero-full { text-align:center; }
.pf-hero-full .pf-hero-body { margin:0 auto; }
.pf-hero-full .pf-hero-lead { margin-left:auto; margin-right:auto; }
.pf-hero-full .pf-btn-row { justify-content:center; }
.pf-hero-full-bg::after { content:''; position:absolute; inset:0; background:radial-gradient(ellipse at center, <?= $PF['bg']; ?>F2 0%, <?= $PF['bg']; ?>C4 60%, <?= $PF['bg']; ?>6E 100%); }
<?php else: ?>
.pf-hero-full-bg::after { content:''; position:absolute; inset:0; background:linear-gradient(100deg, <?= $PF['soft']; ?>F5 0%, <?= $PF['soft']; ?>C4 48%, <?= $PF['soft']; ?>50 100%); }
<?php endif; ?>
@media(min-width:960px){
  .pf-hero-full { padding:88px 0; }
  .pf-hero-full.has-media { min-height:560px; }
}

/* ---------- Product rail (horizontal scroll w/ arrows) ---------- */
.pf-rail { position:relative; }
.pf-rail-track { display:flex; gap:12px; overflow-x:auto; scroll-snap-type:x mandatory; scrollbar-width:none; -webkit-overflow-scrolling:touch; padding-bottom:6px; }
.pf-rail-track::-webkit-scrollbar { display:none; }
.pf-rail-track .pf-card { flex:0 0 64%; scroll-snap-align:start; }
@media(min-width:640px){ .pf-rail-track .pf-card { flex-basis:31%; } .pf-rail-track { gap:18px; } }
@media(min-width:960px){ .pf-rail-track .pf-card { flex-basis:23.5%; } .pf-rail-track { gap:22px; } }
.pf-rail-nav { display:flex; align-items:center; gap:8px; }
.pf-rail-btn { width:44px; height:44px; border-radius:50%; border:1.5px solid <?= $PF['border']; ?>; background: <?= $PF['surface']; ?>; color: <?= $PF['ink']; ?>; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; transition:all .2s; flex-shrink:0; }
.pf-rail-btn:hover { border-color: <?= $PF['ink']; ?>; }
.pf-rail-btn svg { width:16px; height:16px; stroke:currentColor; stroke-width:2; fill:none; }
.pf-sec-side { display:flex; align-items:center; gap:14px; }

/* ---------- Story split (promo / about) ---------- */
.pf-story { display:grid; grid-template-columns:1fr; border-radius: <?= $PF['radius']; ?>; overflow:hidden; }
.pf-story-media { min-height:240px; background: <?= $PF['accent_soft']; ?>; position:relative; }
.pf-story-media img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
.pf-story-txt { padding:32px 24px; display:flex; flex-direction:column; justify-content:center; background: <?= $PF['soft']; ?>; }
.pf-story-invert .pf-story-txt { background: <?= $PF['ink']; ?>; color: <?= $PF['bg']; ?>; }
.pf-story-invert .pf-story-title { color: <?= $PF['bg']; ?>; }
.pf-story-invert .pf-story-lead { color: <?= $PF['bg']; ?>B3; }
.pf-story-title { font-family: <?= $PF['font_display']; ?>; font-size:clamp(22px,5.6vw,32px); font-weight:700; color: <?= $PF['ink']; ?>; margin:10px 0 12px; line-height:1.15; letter-spacing:-.01em; <?= $PF['display_italic'] ? 'font-style:italic;' : ''; ?> }
.pf-story-lead { font-size:14px; line-height:1.7; color: <?= $PF['muted']; ?>; margin-bottom:22px; }
@media(min-width:768px){ .pf-story { grid-template-columns:1fr 1fr; } .pf-story-txt { padding:48px 44px; } .pf-story-media { min-height:340px; } }

/* ---------- Journal cards (editorial image + caption) ---------- */
.pf-journal-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
@media(min-width:768px){ .pf-journal-grid { grid-template-columns:repeat(4,1fr); gap:18px; } }
.pf-journal { background: <?= $PF['card']; ?>; border:1px solid <?= $PF['border']; ?>; border-radius: <?= $PF['radius']; ?>; overflow:hidden; text-decoration:none; display:block; transition:transform .25s, box-shadow .25s; }
.pf-journal:hover { transform:translateY(-3px); box-shadow:0 10px 30px rgba(0,0,0,.08); }
.pf-journal-img { aspect-ratio:4/3; overflow:hidden; background: <?= $PF['soft']; ?>; }
.pf-journal-img img { width:100%; height:100%; object-fit:cover; transition:transform .5s; }
.pf-journal:hover .pf-journal-img img { transform:scale(1.05); }
.pf-journal-body { padding:14px 16px 16px; }
.pf-journal-kicker { font-family: <?= $PF['font_kicker']; ?>; font-size:9px; letter-spacing:.14em; text-transform:uppercase; color: <?= $PF['kicker_color']; ?>; margin-bottom:6px; }
.pf-journal-title { font-size:13px; font-weight:600; color: <?= $PF['ink']; ?>; line-height:1.45; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }

/* ---------- Sections ---------- */
.pf-sec { padding:52px 0; }
@media(min-width:960px){ .pf-sec { padding:72px 0; } }
.pf-band { background: <?= $PF['soft']; ?>; }
.pf-sec-head { display:flex; flex-direction:column; gap:6px; margin-bottom:26px; }
.pf-sec-link { font-family: <?= $PF['font_body']; ?>; font-size:13px; font-weight:600; color: <?= $PF['accent']; ?>; text-decoration:none; align-self:flex-start; display:inline-flex; align-items:center; gap:5px; }
.pf-sec-link:hover { opacity:.8; }
@media(min-width:640px){ .pf-sec-head { flex-direction:row; align-items:flex-end; justify-content:space-between; gap:16px; } }
.pf-sec-head-center { text-align:center; align-items:center; }
.pf-rule { width:44px; height:2px; background: <?= $PF['accent']; ?>; margin:12px auto 0; border-radius:2px; }

/* ---------- Product cards ---------- */
.pf-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
@media(min-width:640px){ .pf-grid { grid-template-columns:repeat(3,1fr); gap:18px; } }
@media(min-width:960px){ .pf-grid { grid-template-columns:repeat(4,1fr); gap:22px; } }
.pf-card { position:relative; background: <?= $PF['card']; ?>; border-radius: <?= $PF['radius']; ?>; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.05), 0 8px 24px rgba(0,0,0,.05); transition:transform .25s, box-shadow .25s; }
.pf-card:hover { transform:translateY(-4px); box-shadow:0 4px 12px rgba(0,0,0,.07), 0 20px 44px rgba(0,0,0,.10); }
.pf-badge { position:absolute; top:10px; z-index:2; font-family: <?= $PF['font_kicker']; ?>; font-size:10px; font-weight:700; letter-spacing:.06em; padding:5px 10px; border-radius:999px; }
.pf-badge-sale { left:10px; background: <?= $PF['accent']; ?>; color: <?= $PF['accent_ink']; ?>; }
.pf-badge-out { right:10px; background:#EF4444; color:#fff; }
.pf-card-media { display:block; position:relative; aspect-ratio:4/5; overflow:hidden; background: <?= $PF['soft']; ?>; }
.pf-card-media img { width:100%; height:100%; object-fit:cover; transition:transform .5s ease; }
.pf-card:hover .pf-card-media img { transform:scale(1.05); }
.pf-card-ph { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color: <?= $PF['accent']; ?>; }
.pf-card-ph span { font-family: <?= $PF['font_display']; ?>; font-size:40px; font-weight:600; <?= $PF['display_italic'] ? 'font-style:italic;' : ''; ?> }
.pf-card-body { padding:13px 14px 14px; }
@media(min-width:960px){ .pf-card-body { padding:16px 18px 18px; } }
.pf-card-fam { font-family: <?= $PF['font_kicker']; ?>; font-size:9px; letter-spacing:.14em; text-transform:uppercase; color: <?= $PF['kicker_color']; ?>; margin-bottom:4px; }
.pf-card-name { font-family: <?= $PF['font_body']; ?>; font-size:14px; font-weight:600; color: <?= $PF['ink']; ?>; line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:38px; text-decoration:none; margin-bottom:10px; }
.pf-card-name:hover { color: <?= $PF['accent']; ?>; }
.pf-card-foot { display:flex; flex-direction:column; gap:10px; }
.pf-card-price { font-family: <?= $PF['font_body']; ?>; font-size:16px; font-weight:700; color: <?= $PF['ink']; ?>; letter-spacing:-.01em; }
.pf-card-price .old { font-size:12px; color: <?= $PF['muted']; ?>; text-decoration:line-through; margin-left:7px; font-weight:400; }
.pf-card-actions { display:flex; gap:8px; }
.pf-btn-add { flex:1; min-height:44px; border-radius: <?= $PF['btn_radius']; ?>; background: <?= $PF['ink']; ?>; color: <?= $PF['bg']; ?>; border:none; font-family: <?= $PF['font_body']; ?>; font-size:12px; font-weight:600; cursor:pointer; transition:opacity .2s, transform .15s; }
.pf-btn-add:hover { opacity:.85; }
.pf-btn-add:active { transform:scale(.97); }
.pf-btn-add:disabled { opacity:.3; cursor:not-allowed; }
.pf-btn-wa { width:48px; min-height:44px; flex-shrink:0; border-radius: <?= $PF['btn_radius']; ?>; background:#25D366; color:#fff; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s, transform .15s; }
.pf-btn-wa:hover { background:#1EBE5B; }
.pf-btn-wa:active { transform:scale(.95); }

/* ---------- Family cards ---------- */
.pf-fam-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
@media(min-width:640px){ .pf-fam-grid { grid-template-columns:repeat(3,1fr); gap:14px; } }
@media(min-width:960px){ .pf-fam-grid { grid-template-columns:repeat(4,1fr); gap:18px; } }
.pf-fam { position:relative; display:block; overflow:hidden; border-radius: <?= $PF['radius']; ?>; aspect-ratio:4/4.6; text-decoration:none; background: <?= $PF['soft']; ?>; }
.pf-fam-media { position:absolute; inset:0; }
.pf-fam-media img { width:100%; height:100%; object-fit:cover; transition:transform .6s ease; }
.pf-fam:hover .pf-fam-media img { transform:scale(1.06); }
.pf-fam-ph { width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-family: <?= $PF['font_display']; ?>; font-size:52px; color: <?= $PF['accent']; ?>; <?= $PF['display_italic'] ? 'font-style:italic;' : ''; ?> }
.pf-fam-veil { position:absolute; inset:0; background:linear-gradient(180deg, transparent 45%, rgba(0,0,0,.68) 100%); }
.pf-fam-body { position:absolute; left:0; right:0; bottom:0; padding:16px; z-index:2; }
.pf-fam-name { font-family: <?= $PF['font_body']; ?>; font-size:15px; font-weight:600; color:#fff; }
.pf-fam-count { font-family: <?= $PF['font_kicker']; ?>; font-size:9px; letter-spacing:.14em; text-transform:uppercase; color:rgba(255,255,255,.8); margin-top:4px; }

/* ---------- Values / trust ---------- */
.pf-values { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
@media(min-width:960px){ .pf-values { grid-template-columns:repeat(4,1fr); gap:16px; } }
.pf-value { text-align:center; padding:24px 14px; border-radius: <?= $PF['radius']; ?>; background: <?= $PF['card']; ?>; border:1px solid <?= $PF['border']; ?>; }
.pf-value-icon { font-size:20px; color: <?= $PF['kicker_color']; ?>; margin-bottom:10px; }
.pf-value-title { font-family: <?= $PF['font_body']; ?>; font-size:13px; font-weight:700; color: <?= $PF['ink']; ?>; margin-bottom:4px; }
.pf-value-text { font-size:12px; color: <?= $PF['muted']; ?>; line-height:1.6; }

/* ---------- Promo ---------- */
.pf-promo { display:flex; flex-direction:column; border-radius: <?= $PF['radius']; ?>; overflow:hidden; background: <?= $PF['card']; ?>; box-shadow:0 1px 3px rgba(0,0,0,.05), 0 10px 30px rgba(0,0,0,.06); }
.pf-promo-media { min-height:200px; background: <?= $PF['soft']; ?>; }
.pf-promo-media img { width:100%; height:100%; object-fit:cover; display:block; }
.pf-promo-body { padding:28px 22px; display:flex; flex-direction:column; justify-content:center; }
.pf-promo-title { font-family: <?= $PF['font_display']; ?>; font-size:clamp(20px,5vw,30px); font-weight:700; color: <?= $PF['ink']; ?>; margin:8px 0 16px; <?= $PF['display_italic'] ? 'font-style:italic;' : ''; ?> }
@media(min-width:768px){ .pf-promo { flex-direction:row; } .pf-promo-media { flex:1; } .pf-promo-body { flex:1; padding:44px; } }

/* ---------- CTA ---------- */
.pf-cta { text-align:center; padding:48px 20px; border-radius: <?= $PF['radius']; ?>; background: <?= $PF['accent_soft']; ?>; }
.pf-cta-title { font-family: <?= $PF['font_display']; ?>; font-size:clamp(24px,6vw,36px); font-weight:700; color: <?= $PF['ink']; ?>; margin:0 0 12px; letter-spacing:-.01em; <?= $PF['display_italic'] ? 'font-style:italic;' : ''; ?> }
.pf-cta-text { color: <?= $PF['muted']; ?>; max-width:480px; margin:0 auto 24px; font-size:14px; line-height:1.7; }
.pf-cta .pf-btn-row { justify-content:center; }

/* ---------- Testimonials ---------- */
.pf-testi-grid { display:grid; grid-template-columns:1fr; gap:14px; }
@media(min-width:768px){ .pf-testi-grid { grid-template-columns:repeat(3,1fr); gap:18px; } }
.pf-testi { background: <?= $PF['card']; ?>; border:1px solid <?= $PF['border']; ?>; border-radius: <?= $PF['radius']; ?>; padding:24px; }
.pf-testi-stars { color:#F59E0B; letter-spacing:3px; font-size:14px; margin-bottom:12px; }
.pf-testi-text { font-size:14px; color: <?= $PF['muted']; ?>; line-height:1.7; margin-bottom:14px; }
.pf-testi-author { font-size:12px; font-weight:700; color: <?= $PF['ink']; ?>; letter-spacing:.04em; text-transform:uppercase; }

/* ---------- Brands / insta / faq / contact / hours ---------- */
.pf-brands { display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:center; }
.pf-brand { padding:10px 20px; border:1px solid <?= $PF['border']; ?>; border-radius:999px; font-family: <?= $PF['font_display']; ?>; font-size:14px; color: <?= $PF['ink']; ?>; background: <?= $PF['card']; ?>; <?= $PF['display_italic'] ? 'font-style:italic;' : ''; ?> }
.pf-insta-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
@media(min-width:768px){ .pf-insta-grid { grid-template-columns:repeat(5,1fr); } }
.pf-insta-item { aspect-ratio:1; overflow:hidden; border-radius: <?= $PF['radius']; ?>; }
.pf-insta-item img { width:100%; height:100%; object-fit:cover; transition:transform .35s; }
.pf-insta-item:hover img { transform:scale(1.06); }
.pf-faq-list { max-width:720px; margin:0 auto; }
.pf-faq { background: <?= $PF['card']; ?>; border:1px solid <?= $PF['border']; ?>; border-radius: <?= $PF['radius']; ?>; margin-bottom:10px; overflow:hidden; }
.pf-faq-q { padding:16px 18px; font-size:14px; font-weight:600; color: <?= $PF['ink']; ?>; cursor:pointer; display:flex; justify-content:space-between; align-items:center; min-height:52px; }
.pf-faq-q::after { content:'+'; color: <?= $PF['kicker_color']; ?>; font-size:20px; }
.pf-faq.open .pf-faq-q::after { content:'\2212'; }
.pf-faq-a { max-height:0; overflow:hidden; transition:max-height .3s ease; }
.pf-faq.open .pf-faq-a { max-height:320px; }
.pf-faq-a p { padding:0 18px 16px; font-size:14px; color: <?= $PF['muted']; ?>; line-height:1.7; margin:0; }
.pf-contact-grid { display:grid; grid-template-columns:1fr; gap:12px; }
@media(min-width:768px){ .pf-contact-grid { grid-template-columns:repeat(3,1fr); gap:16px; } }
.pf-contact { text-align:center; padding:24px; border:1px solid <?= $PF['border']; ?>; border-radius: <?= $PF['radius']; ?>; background: <?= $PF['card']; ?>; }
.pf-contact-ic { width:44px; height:44px; margin:0 auto 12px; border-radius:50%; background: <?= $PF['accent_soft']; ?>; display:flex; align-items:center; justify-content:center; color: <?= $PF['kicker_color']; ?>; }
.pf-contact-ic svg { width:20px; height:20px; }
.pf-contact-l { font-family: <?= $PF['font_kicker']; ?>; font-size:9px; letter-spacing:.16em; text-transform:uppercase; color: <?= $PF['kicker_color']; ?>; margin-bottom:4px; }
.pf-contact-v { font-size:14px; font-weight:600; color: <?= $PF['ink']; ?>; word-break:break-word; }
.pf-hours { max-width:520px; margin:0 auto; }
.pf-hours-row { padding:12px 0; border-bottom:1px solid <?= $PF['border']; ?>; font-size:14px; color: <?= $PF['muted']; ?>; text-align:center; }
.pf-hours-row:last-child { border-bottom:none; }

/* ---------- Newsletter ---------- */
.pf-news { text-align:center; padding:44px 20px; border-radius: <?= $PF['radius']; ?>; background: <?= $PF['ink']; ?>; color: <?= $PF['bg']; ?>; }
.pf-news-title { font-family: <?= $PF['font_display']; ?>; font-size:clamp(20px,5vw,30px); font-weight:700; margin-bottom:8px; <?= $PF['display_italic'] ? 'font-style:italic;' : ''; ?> }
.pf-news-text { opacity:.7; max-width:420px; margin:0 auto 22px; font-size:14px; }
.pf-news-form { display:flex; flex-direction:column; gap:10px; max-width:440px; margin:0 auto; }
.pf-news-form input { min-height:50px; padding:14px 18px; border:none; border-radius: <?= $PF['btn_radius']; ?>; font-size:15px; outline:none; font-family: <?= $PF['font_body']; ?>; box-sizing:border-box; }
.pf-news-form button { min-height:50px; border:none; border-radius: <?= $PF['btn_radius']; ?>; background: <?= $PF['accent']; ?>; color: <?= $PF['accent_ink']; ?>; font-family: <?= $PF['font_body']; ?>; font-weight:600; font-size:13px; cursor:pointer; }
@media(min-width:640px){ .pf-news-form { flex-direction:row; } .pf-news-form input { flex:1; } }

/* ---------- Breadcrumbs ---------- */
.pf-crumbs { padding:20px 0 0; font-family: <?= $PF['font_kicker']; ?>; font-size:10px; letter-spacing:.14em; text-transform:uppercase; color: <?= $PF['muted']; ?>; }
.pf-crumbs a { color: <?= $PF['muted']; ?>; text-decoration:none; }
.pf-crumbs a:hover { color: <?= $PF['accent']; ?>; }
.pf-crumbs .sep { margin:0 8px; color: <?= $PF['border']; ?>; }

/* ---------- Catalogue ---------- */
.pf-page { padding:24px 0 72px; }
.pf-h1 { font-family: <?= $PF['font_display']; ?>; font-size:clamp(26px,7vw,40px); font-weight:700; color: <?= $PF['ink']; ?>; margin:0; letter-spacing:-.015em; <?= $PF['display_italic'] ? 'font-style:italic;' : ''; ?> <?= $PF['display_case'] === 'uppercase' ? 'text-transform:uppercase;' : ''; ?> }
.pf-count { font-family: <?= $PF['font_kicker']; ?>; font-size:10px; letter-spacing:.12em; text-transform:uppercase; color: <?= $PF['muted']; ?>; margin-top:8px; }
.pf-filters { margin:24px 0 28px; display:flex; flex-direction:column; gap:12px; }
.pf-search { position:relative; }
.pf-search input { width:100%; min-height:50px; padding:13px 14px 13px 44px; border:1.5px solid <?= $PF['border']; ?>; border-radius: <?= $PF['btn_radius']; ?>; font-size:15px; background: <?= $PF['surface']; ?>; color: <?= $PF['ink']; ?>; outline:none; font-family: <?= $PF['font_body']; ?>; box-sizing:border-box; }
.pf-search input:focus { border-color: <?= $PF['accent']; ?>; }
.pf-search input::placeholder { color: <?= $PF['muted']; ?>; }
.pf-search svg { position:absolute; left:15px; top:50%; transform:translateY(-50%); color: <?= $PF['muted']; ?>; }
.pf-chips { display:flex; gap:8px; overflow-x:auto; scrollbar-width:none; padding-bottom:4px; -webkit-overflow-scrolling:touch; }
.pf-chips::-webkit-scrollbar { display:none; }
.pf-chip { flex-shrink:0; min-height:44px; display:inline-flex; align-items:center; padding:10px 18px; border-radius:999px; border:1.5px solid <?= $PF['border']; ?>; background: <?= $PF['surface']; ?>; font-family: <?= $PF['font_body']; ?>; font-size:13px; font-weight:500; color: <?= $PF['muted']; ?>; cursor:pointer; text-decoration:none; transition:all .2s; white-space:nowrap; }
.pf-chip:hover { border-color: <?= $PF['accent']; ?>; color: <?= $PF['ink']; ?>; }
.pf-chip.active { background: <?= $PF['ink']; ?>; color: <?= $PF['bg']; ?>; border-color: <?= $PF['ink']; ?>; }
@media(min-width:640px){ .pf-filters { flex-direction:row; align-items:center; } .pf-search { flex:1; max-width:380px; } }
.pf-pagination { display:flex; justify-content:center; gap:8px; margin-top:40px; flex-wrap:wrap; }
.pf-page-btn { min-width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center; padding:0 14px; border-radius:999px; border:1.5px solid <?= $PF['border']; ?>; background: <?= $PF['surface']; ?>; font-size:14px; font-weight:500; color: <?= $PF['muted']; ?>; text-decoration:none; }
.pf-page-btn.active { background: <?= $PF['ink']; ?>; color: <?= $PF['bg']; ?>; border-color: <?= $PF['ink']; ?>; }
.pf-empty { text-align:center; padding:80px 20px; }
.pf-empty-title { font-family: <?= $PF['font_display']; ?>; font-size:22px; font-weight:700; color: <?= $PF['ink']; ?>; margin-bottom:8px; }
.pf-empty-text { color: <?= $PF['muted']; ?>; font-size:14px; margin-bottom:22px; }

/* ---------- Product detail ---------- */
.pf-pd { padding:28px 0 72px; }
.pf-pd-layout { display:grid; grid-template-columns:1fr; gap:28px; align-items:start; }
@media(min-width:960px){ .pf-pd-layout { grid-template-columns:1.05fr 1fr; gap:52px; } }
.pf-pd-media { aspect-ratio:4/5; overflow:hidden; background: <?= $PF['soft']; ?>; border-radius: <?= $PF['radius']; ?>; position:relative; }
@media(min-width:960px){ .pf-pd-media { position:sticky; top:90px; } }
.pf-pd-media img { width:100%; height:100%; object-fit:cover; }
.pf-pd-ph { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color: <?= $PF['accent']; ?>; font-family: <?= $PF['font_display']; ?>; font-size:60px; font-weight:600; }
.pf-pd-badge { position:absolute; top:14px; left:14px; z-index:2; background: <?= $PF['accent']; ?>; color: <?= $PF['accent_ink']; ?>; font-family: <?= $PF['font_kicker']; ?>; font-size:10px; font-weight:700; letter-spacing:.06em; padding:6px 12px; border-radius:999px; }
.pf-pd-name { font-family: <?= $PF['font_display']; ?>; font-size:clamp(28px,7.4vw,42px); font-weight:700; color: <?= $PF['ink']; ?>; line-height:1.1; margin:0 0 12px; letter-spacing:-.015em; <?= $PF['display_italic'] ? 'font-style:italic;' : ''; ?> <?= $PF['display_case'] === 'uppercase' ? 'text-transform:uppercase; font-size:clamp(24px,6.4vw,36px);' : ''; ?> }
.pf-pd-price { font-family: <?= $PF['font_body']; ?>; font-size:26px; font-weight:700; color: <?= $PF['ink']; ?>; margin-bottom:8px; letter-spacing:-.01em; }
.pf-pd-price .old { font-size:16px; color: <?= $PF['muted']; ?>; text-decoration:line-through; margin-left:10px; font-weight:400; }
.pf-pd-stock { font-size:12px; font-weight:600; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
.pf-pd-stock::before { content:''; width:8px; height:8px; border-radius:50%; background:currentColor; }
.pf-pd-stock.in { color:#16A34A; }
.pf-pd-stock.out { color:#DC2626; }
.pf-pd-desc { font-size:15px; line-height:1.8; color: <?= $PF['muted']; ?>; margin-bottom:24px; }
.pf-pd-tags { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:22px; }
.pf-pd-tag { padding:7px 13px; border:1px solid <?= $PF['border']; ?>; border-radius:999px; font-size:12px; color: <?= $PF['muted']; ?>; background: <?= $PF['surface']; ?>; }
.pf-pd-tag b { color: <?= $PF['ink']; ?>; font-weight:600; }
.pf-pd-qty { display:inline-flex; align-items:center; gap:2px; border:1.5px solid <?= $PF['border']; ?>; border-radius: <?= $PF['btn_radius']; ?>; padding:3px; margin-bottom:18px; }
.pf-pd-qty button { width:44px; height:44px; border-radius: <?= $PF['btn_radius']; ?>; border:none; background:transparent; font-size:19px; cursor:pointer; color: <?= $PF['ink']; ?>; transition:background .2s; }
.pf-pd-qty button:hover { background: <?= $PF['soft']; ?>; }
.pf-pd-qty span { font-size:16px; font-weight:700; min-width:36px; text-align:center; color: <?= $PF['ink']; ?>; }
.pf-pd-actions { display:flex; flex-direction:column; gap:10px; }
@media(min-width:640px){ .pf-pd-actions { flex-direction:row; } .pf-pd-actions .pf-btn { flex:1; } }
.pf-pd-trust { display:flex; flex-wrap:wrap; gap:8px 18px; margin-top:20px; }
.pf-pd-trust span { display:inline-flex; align-items:center; gap:7px; font-size:12px; color: <?= $PF['muted']; ?>; font-weight:500; }
.pf-pd-trust svg { width:15px; height:15px; color: <?= $PF['kicker_color']; ?>; flex-shrink:0; }
.pf-pd-specs { margin-top:30px; border-top:1px solid <?= $PF['border']; ?>; padding-top:18px; }
.pf-pd-specs-title { font-family: <?= $PF['font_kicker']; ?>; font-size:10px; letter-spacing:.18em; text-transform:uppercase; color: <?= $PF['kicker_color']; ?>; font-weight:600; margin-bottom:8px; }
.pf-pd-spec { display:flex; justify-content:space-between; gap:12px; padding:11px 0; border-bottom:1px solid <?= $PF['border']; ?>; font-size:14px; color: <?= $PF['muted']; ?>; }
.pf-pd-spec b { color: <?= $PF['ink']; ?>; font-weight:600; text-align:right; }
.pf-pd-extra { margin-top:44px; }
.pf-pd-extra .pf-grid { grid-template-columns:repeat(2,1fr); gap:12px; }
@media(min-width:960px){ .pf-pd-extra .pf-grid { gap:16px; } }

/* ---------- WhatsApp order modal (bottom sheet on mobile) ---------- */
.pfwa-modal { display:none; position:fixed; inset:0; z-index:9999; }
.pfwa-modal.open { display:block; }
.pfwa-veil { position:absolute; inset:0; background:rgba(0,0,0,.5); backdrop-filter:blur(2px); }
.pfwa-card { position:absolute; left:0; right:0; bottom:0; max-height:92vh; overflow-y:auto; background:#fff; border-radius:20px 20px 0 0; }
@media(min-width:640px){ .pfwa-card { left:50%; right:auto; bottom:auto; top:50%; transform:translate(-50%,-50%); width:460px; border-radius: <?= $PF['radius']; ?>; box-shadow:0 30px 80px rgba(0,0,0,.25); } }
.pfwa-grip { width:40px; height:4px; border-radius:99px; background: <?= $PF['border']; ?>; margin:10px auto 0; }
@media(min-width:640px){ .pfwa-grip { display:none; } }
.pfwa-head { display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid <?= $PF['border']; ?>; }
.pfwa-title { font-family: <?= $PF['font_display']; ?>; font-size:18px; font-weight:700; color: <?= $PF['ink']; ?>; margin:0; <?= $PF['display_italic'] ? 'font-style:italic;' : ''; ?> }
.pfwa-x { width:40px; height:40px; border-radius:50%; border:none; background: <?= $PF['soft']; ?>; color: <?= $PF['ink']; ?>; cursor:pointer; font-size:18px; }
.pfwa-body { padding:20px; }
.pfwa-prod { display:flex; gap:14px; align-items:center; padding:12px; background: <?= $PF['soft']; ?>; border-radius: <?= $PF['radius']; ?>; margin-bottom:18px; }
.pfwa-prod img { width:54px; height:54px; border-radius:10px; object-fit:cover; flex-shrink:0; }
.pfwa-prod-name { font-size:14px; font-weight:600; color: <?= $PF['ink']; ?>; margin:0 0 4px; line-height:1.3; }
.pfwa-prod-price { font-size:16px; font-weight:700; color: <?= $PF['kicker_color']; ?>; }
.pfwa-fields { display:flex; flex-direction:column; gap:13px; }
.pfwa-fields label { font-size:11px; font-weight:600; color: <?= $PF['muted']; ?>; margin-bottom:5px; display:block; text-transform:uppercase; letter-spacing:.08em; }
.pfwa-fields input, .pfwa-fields textarea { width:100%; min-height:50px; padding:13px 14px; border:1.5px solid <?= $PF['border']; ?>; border-radius:12px; font-size:15px; background:#fff; color: <?= $PF['ink']; ?>; outline:none; box-sizing:border-box; font-family: <?= $PF['font_body']; ?>; }
.pfwa-fields input:focus, .pfwa-fields textarea:focus { border-color: <?= $PF['accent']; ?>; }
.pfwa-fields textarea { resize:vertical; min-height:60px; }
.pfwa-actions { display:flex; flex-direction:column; gap:10px; margin-top:18px; padding-bottom:env(safe-area-inset-bottom); }
.pfwa-send { min-height:52px; border-radius: <?= $PF['btn_radius']; ?>; border:none; background:#25D366; color:#fff; font-size:14px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; }
.pfwa-send svg { width:17px; height:17px; fill:currentColor; }
.pfwa-cancel { min-height:48px; border-radius: <?= $PF['btn_radius']; ?>; border:1.5px solid <?= $PF['border']; ?>; background:transparent; color: <?= $PF['ink']; ?>; font-size:14px; font-weight:600; cursor:pointer; }
@media(min-width:640px){ .pfwa-actions { flex-direction:row; } .pfwa-send { flex:1; } }

/* ---------- Media shape per skin ---------- */
<?php if($PF['media_shape'] === 'arch'): ?>
.pf-card-media { border-radius:110px 110px 0 0; }
.pf-card { border-radius:110px 110px <?= $PF['radius']; ?> <?= $PF['radius']; ?>; }
.pf-fam { border-radius:110px 110px <?= $PF['radius']; ?> <?= $PF['radius']; ?>; }
.pf-pd-media { border-radius:140px 140px <?= $PF['radius']; ?> <?= $PF['radius']; ?>; }
<?php elseif($PF['media_shape'] === 'sharp'): ?>
.pf-card, .pf-fam, .pf-promo, .pf-cta, .pf-news, .pf-value, .pf-contact, .pf-testi, .pf-faq, .pf-journal, .pf-story { border-radius:2px; }
.pf-card-media, .pf-pd-media { border-radius:0; }
<?php endif; ?>
</style>

<?php if($announce): ?>
<div class="pfh-announce"><?= htmlspecialchars($announce); ?></div>
<?php endif; ?>

<header class="pfh">
  <div class="pf-wrap pfh-bar">
    <button class="pfh-ic pfh-menu" onclick="pfMenu(true)" aria-label="Menu">
      <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <a href="<?= base_url('store/' . $slug); ?>" class="pfh-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>">
      <?php else: ?>
        <span class="pfh-logo-name"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Store')); ?></span>
      <?php endif; ?>
    </a>
    <nav class="pfh-nav">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pfh-nav-link">Shop</a>
      <?php if(!empty($categories)): foreach(array_slice($categories, 0, 3) as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="pfh-nav-link"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; endif; ?>
      <a href="<?= base_url('store/' . $slug . '/about'); ?>" class="pfh-nav-link">About</a>
      <?php if($settings->allow_services ?? false): ?>
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="pfh-nav-link">Services</a>
      <?php endif; ?>
    </nav>
    <div class="pfh-actions">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pfh-ic" aria-label="Search">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      </a>
      <?php if($pfWaNum): ?>
      <a href="https://wa.me/<?= $pfWaNum; ?>" target="_blank" class="pfh-ic" aria-label="WhatsApp">
        <svg class="fill" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
      </a>
      <?php endif; ?>
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="pfh-ic" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="pfh-count" id="cart-count">0</span>
      </a>
    </div>
  </div>
</header>

<div class="pfh-overlay" id="pfh-overlay" onclick="pfMenu(false)"></div>
<div class="pfh-drawer" id="pfh-drawer">
  <div class="pfh-drawer-title">
    <?php if($logo): ?><img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>" style="max-height:34px;max-width:150px;display:block;margin-bottom:10px;"><?php endif; ?>
    <?= htmlspecialchars($store->store_name ?? 'Menu'); ?>
  </div>
  <a href="<?= base_url('store/' . $slug); ?>" class="pfh-drawer-link" onclick="pfMenu(false)">Home</a>
  <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pfh-drawer-link" onclick="pfMenu(false)">Shop</a>
  <a href="<?= base_url('store/' . $slug . '/about'); ?>" class="pfh-drawer-link" onclick="pfMenu(false)">About</a>
  <?php if($settings->allow_services ?? false): ?>
  <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="pfh-drawer-link" onclick="pfMenu(false)">Services</a>
  <?php endif; ?>
  <?php if(!empty($categories)): ?>
  <div class="pfh-drawer-sec-title">Fragrance Families</div>
  <?php foreach(array_slice($categories, 0, 8) as $cat): ?>
  <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="pfh-drawer-link" onclick="pfMenu(false)"><?= htmlspecialchars($cat->category_name); ?></a>
  <?php endforeach; ?>
  <?php endif; ?>
  <?php if(!empty($settings->store_phone)): ?>
  <div class="pfh-drawer-sec-title">Contact</div>
  <a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>" class="pfh-drawer-link"><?= htmlspecialchars($settings->store_phone); ?></a>
  <?php endif; ?>
  <?php if($pfWaNum): ?>
  <a href="https://wa.me/<?= $pfWaNum; ?>" target="_blank" class="pfh-drawer-wa" onclick="pfMenu(false)">
    <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
    Chat on WhatsApp
  </a>
  <?php endif; ?>
</div>

<!-- WhatsApp order modal — bottom sheet on mobile, centered card on desktop -->
<div class="pfwa-modal" id="pfwa-modal">
  <div class="pfwa-veil" onclick="pfWaClose()"></div>
  <div class="pfwa-card">
    <div class="pfwa-grip"></div>
    <div class="pfwa-head">
      <h3 class="pfwa-title">Order via WhatsApp</h3>
      <button class="pfwa-x" onclick="pfWaClose()" aria-label="Close">&times;</button>
    </div>
    <div class="pfwa-body">
      <div class="pfwa-prod">
        <img id="pfwa-img" src="" alt="" style="display:none;">
        <div>
          <p class="pfwa-prod-name" id="pfwa-name"></p>
          <div class="pfwa-prod-price" id="pfwa-price"></div>
        </div>
      </div>
      <div class="pfwa-fields">
        <div><label>Your Name</label><input type="text" id="pfwa-cname" placeholder="Enter your name" autocomplete="name"></div>
        <div><label>Phone Number</label><input type="tel" id="pfwa-cphone" placeholder="Enter your phone number" autocomplete="tel"></div>
        <div><label>Quantity</label><input type="number" id="pfwa-qty" value="1" min="1" inputmode="numeric"></div>
        <div><label>Note (optional)</label><textarea id="pfwa-note" placeholder="Gift wrap, size, occasion..."></textarea></div>
      </div>
      <div class="pfwa-actions">
        <button class="pfwa-send" onclick="pfWaSend()"><svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> Send via WhatsApp</button>
        <button class="pfwa-cancel" onclick="pfWaClose()">Cancel</button>
      </div>
    </div>
  </div>
</div>

<script>
  function pfRail(id, dir){
    var el = document.getElementById(id);
    if(el) el.scrollBy({left: dir * el.clientWidth * .8, behavior: 'smooth'});
  }
  function pfMenu(open){
    document.getElementById('pfh-drawer').classList.toggle('open', open);
    document.getElementById('pfh-overlay').classList.toggle('open', open);
    document.body.style.overflow = open ? 'hidden' : '';
  }
  var pfWaProduct = null;
  function pfWaOrder(id, name, price, image, qty){
    pfWaProduct = {id:id, name:name, price:price, image:image};
    document.getElementById('pfwa-name').textContent = name;
    document.getElementById('pfwa-price').textContent = formatMoney(price);
    var img = document.getElementById('pfwa-img');
    if(image){ img.src = '<?= base_url(); ?>' + image; img.style.display='block'; } else { img.style.display='none'; }
    document.getElementById('pfwa-qty').value = qty || 1;
    document.getElementById('pfwa-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function pfWaClose(){
    document.getElementById('pfwa-modal').classList.remove('open');
    document.body.style.overflow = '';
  }
  function pfWaSend(){
    if(!pfWaProduct) return;
    var name  = document.getElementById('pfwa-cname').value.trim();
    var phone = document.getElementById('pfwa-cphone').value.trim();
    var qty   = parseInt(document.getElementById('pfwa-qty').value) || 1;
    var note  = document.getElementById('pfwa-note').value.trim();
    if(!name || !phone){ showToast('Please enter your name and phone number'); return; }
    var waNum = '<?= $pfWaNum; ?>';
    if(!waNum){ showToast('WhatsApp ordering is not available'); return; }
    var msg = 'Hello <?= htmlspecialchars(addslashes($store->store_name ?? '')); ?>, I would like to order:';
    msg += '\n\n' + qty + ' x ' + pfWaProduct.name + ' — ' + formatMoney(pfWaProduct.price * qty);
    msg += '\n\nName: ' + name + '\nPhone: ' + phone;
    if(note) msg += '\nNote: ' + note;
    msg += '\n\nThank you.';
    window.open('https://wa.me/' + waNum + '?text=' + encodeURIComponent(msg), '_blank');
    pfWaClose();
    showToast('Opening WhatsApp with your order...');
  }
</script>
