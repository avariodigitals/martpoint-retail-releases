<?php
/**
 * Skin Core — design system + header.
 * Loaded on every storefront page. Emits the skin fonts, the complete
 * mobile-first stylesheet, header markup, drawer, and the WhatsApp
 * order modal + JS shared by every page. Page views are markup only.
 */
include APPPATH . 'views/themes/skin_core/_skin.php';

$slug     = $settings->store_slug ?? '';
$logo     = $logo_url ?? null;
$skWaNum  = ($settings->allow_whatsapp ?? 1) ? preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '') : '';
$tk       = $SK['theme_key'];
$announce = trim($settings->announcement_bar ?? '');
$centered = $SK['header_style'] === 'center';

/* Appearance-resolved tokens (skin defaults when the admin never customised) */
$skBtnAcc   = !empty($SK['btn_bg']) ? $SK['btn_bg'] : $SK['accent'];
$skBtnAccIn = !empty($SK['btn_bg']) ? $SK['btn_ink'] : $SK['accent_ink'];
$skBtnAdd   = !empty($SK['btn_bg']) ? $SK['btn_bg'] : $SK['ink'];
$skBtnAddIn = !empty($SK['btn_bg']) ? $SK['btn_ink'] : $SK['bg'];
$skFootBg   = !empty($SK['footer_bg']) ? $SK['footer_bg'] : $SK['soft'];
$skFootTxt  = !empty($SK['footer_text']) ? $SK['footer_text'] : $SK['muted'];
$skFootHd   = !empty($SK['footer_head']) ? $SK['footer_head'] : $SK['ink'];
$skFootBrd  = !empty($SK['footer_bg']) ? 'rgba(255,255,255,0.14)' : $SK['border'];
$skFootSoc  = !empty($SK['footer_bg']) ? 'rgba(255,255,255,0.08)' : $SK['surface'];

/* Announcement marquee items (Appearance → Announcement Marquee) */
$skMarquee = [];
if(!empty($settings->marquee_items)){
    foreach(preg_split('/\r\n|\r|\n/', $settings->marquee_items) as $mi){
        $mi = trim($mi);
        if($mi !== '') $skMarquee[] = $mi;
    }
}
?>
<link rel="stylesheet" href="<?= $SK['fonts_url']; ?>">
<style>
/* ============================================================
   SKIN — light, calm commerce system for organic skincare
   Mobile-first; scales up at 640px / 960px / 1200px
   ============================================================ */
.theme-<?= $tk; ?> .mp-topbar,
.theme-<?= $tk; ?> .mp-announcement,
.theme-<?= $tk; ?> .mp-nav,
.theme-<?= $tk; ?> .mp-header,
.theme-<?= $tk; ?> .mp-mobile-menu-btn { display:none !important; }
.theme-<?= $tk; ?> .mp-footer-space { height:0 !important; }

body.theme-<?= $tk; ?> {
  background: <?= $SK['bg']; ?>;
  color: <?= $SK['ink']; ?>;
  font-family: <?= $SK['font_body']; ?>;
  -webkit-font-smoothing: antialiased;
  text-rendering: optimizeLegibility;
}

/* Shared chrome restyle (light) */
.theme-<?= $tk; ?> .mp-footer { background: <?= $skFootBg; ?>; color: <?= $skFootTxt; ?>; }
.theme-<?= $tk; ?> .mp-footer-brand, .theme-<?= $tk; ?> .mp-footer-heading { color: <?= $skFootHd; ?>; font-family: <?= $SK['font_display']; ?>; }
.theme-<?= $tk; ?> .mp-footer-links a { color: <?= $skFootTxt; ?>; }
.theme-<?= $tk; ?> .mp-footer-links a:hover { color: <?= $SK['accent']; ?>; }
.theme-<?= $tk; ?> .mp-footer-bottom { border-top-color: <?= $skFootBrd; ?>; color: <?= $skFootTxt; ?>; }
.theme-<?= $tk; ?> .mp-footer-social a { background: <?= $skFootSoc; ?>; color: <?= $skFootHd; ?>; border:1px solid <?= $skFootBrd; ?>; }
.theme-<?= $tk; ?> .mp-footer-desc, .theme-<?= $tk; ?> .mp-footer-contact-item, .theme-<?= $tk; ?> .mp-footer-contact-item span { color: <?= $skFootTxt; ?>; }
.theme-<?= $tk; ?> .mp-footer-social a:hover { background: <?= $SK['accent']; ?>; color: <?= $SK['accent_ink']; ?>; border-color: <?= $SK['accent']; ?>; transform:translateY(-2px); }
.theme-<?= $tk; ?> .mp-mobile-nav { background:#fff; border-top-color: <?= $SK['border']; ?>; }
.theme-<?= $tk; ?> .mp-mobile-nav-item { color: <?= $SK['muted']; ?>; }
.theme-<?= $tk; ?> .mp-mobile-nav-item.active { color: <?= $SK['accent']; ?>; }
.theme-<?= $tk; ?> .mp-sticky-cart { background:#fff; border-top-color: <?= $SK['border']; ?>; box-shadow:0 -4px 20px rgba(0,0,0,.06); }
.theme-<?= $tk; ?> .mp-sticky-cart-items { color: <?= $SK['muted']; ?>; }
.theme-<?= $tk; ?> .mp-sticky-cart-total { color: <?= $SK['ink']; ?>; }
.theme-<?= $tk; ?> .mp-sticky-cart-btn { background: <?= $skBtnAcc; ?>; color: <?= $skBtnAccIn; ?>; border-radius: <?= $SK['btn_radius']; ?>; }
.theme-<?= $tk; ?> .mp-modal { background:#fff; color: <?= $SK['ink']; ?>; border:1px solid <?= $SK['border']; ?>; border-radius: <?= $SK['radius']; ?>; }
.theme-<?= $tk; ?> .mp-modal-title { color: <?= $SK['ink']; ?>; font-family: <?= $SK['font_display']; ?>; }
.theme-<?= $tk; ?> .mp-modal-add { background: <?= $skBtnAcc; ?>; color: <?= $skBtnAccIn; ?>; border-radius: <?= $SK['btn_radius']; ?>; }
.theme-<?= $tk; ?> .mp-toast { background: <?= $SK['ink']; ?>; color:#fff; }
.theme-<?= $tk; ?> .mp-backtop { background:#fff; color: <?= $SK['ink']; ?>; border-color: <?= $SK['border']; ?>; }

/* ---------- Layout ---------- */
.sk-wrap { width:100%; max-width:1200px; margin:0 auto; padding:0 16px; }
@media(min-width:960px){ .sk-wrap { padding:0 24px; } }

/* ---------- Type ---------- */
.sk-kicker { font-family: <?= $SK['font_kicker']; ?>; font-size:10px; font-weight:600; letter-spacing:.18em; text-transform:uppercase; color: <?= $SK['kicker_color']; ?>; }
.sk-h2 { font-family: <?= $SK['font_display']; ?>; font-size:clamp(24px,6.4vw,36px); font-weight:700; color: <?= $SK['ink']; ?>; margin:0; line-height:1.15; letter-spacing:-.01em; <?= $SK['display_italic'] ? 'font-style:italic;' : ''; ?> <?= $SK['display_case'] === 'uppercase' ? 'text-transform:uppercase;' : ''; ?> }
.sk-sec-sub { font-size:13px; color: <?= $SK['muted']; ?>; margin-top:6px; }

/* ---------- Buttons ---------- */
.sk-btn { display:inline-flex; align-items:center; justify-content:center; gap:9px; min-height:50px; padding:14px 28px; border-radius: <?= $SK['btn_radius']; ?>; font-family: <?= $SK['font_body']; ?>; font-size:13px; font-weight:600; letter-spacing:.02em; border:none; cursor:pointer; text-decoration:none; transition:opacity .2s, transform .15s, box-shadow .2s; }
.sk-btn:active { transform:scale(.97); }
.sk-btn-accent { background: <?= $skBtnAcc; ?>; color: <?= $skBtnAccIn; ?>; box-shadow:0 4px 14px <?= $SK['accent_soft']; ?>; }
.sk-btn-accent:hover { opacity:.92; }
.sk-btn-ink { background: <?= $SK['ink']; ?>; color: <?= $SK['bg']; ?>; }
.sk-btn-ghost { background:transparent; color: <?= $SK['ink']; ?>; border:1.5px solid <?= $SK['border']; ?>; }
.sk-btn-ghost:hover { border-color: <?= $SK['ink']; ?>; }
.sk-btn-wa-full { background:#25D366; color:#fff; box-shadow:0 4px 14px rgba(37,211,102,.35); }
.sk-btn-wa-full:hover { background:#1EBE5B; }
.sk-btn-wa-full svg { width:17px; height:17px; fill:currentColor; }
.sk-btn-row { display:flex; flex-wrap:wrap; gap:10px; }
.sk-btn-row .sk-btn { flex:1 1 45%; }
@media(min-width:640px){ .sk-btn-row .sk-btn { flex:0 1 auto; } }

/* ---------- Header ---------- */
<?php if($announce || $skMarquee):
$announceBg = !empty($settings->announcement_bar_color) ? $settings->announcement_bar_color : $SK['ink'];
?>
.skh-announce { background: <?= $announceBg; ?>; color: <?= sk_contrast($announceBg); ?>; text-align:center; padding:9px 16px; font-family: <?= $SK['font_kicker']; ?>; font-size:10px; letter-spacing:.14em; text-transform:uppercase; }
.skh-marquee { padding:0; overflow:hidden; }
.skh-marquee-track { display:inline-flex; align-items:center; white-space:nowrap; padding:9px 0; animation:skmq 26s linear infinite; will-change:transform; }
.skh-marquee:hover .skh-marquee-track { animation-play-state:paused; }
.skh-marquee-track span { display:inline-flex; align-items:center; padding:0 18px; }
.skh-marquee-track span::after { content:''; width:4px; height:4px; border-radius:50%; background:currentColor; opacity:.45; margin-left:36px; }
@keyframes skmq { to { transform:translateX(-50%); } }
<?php endif; ?>
.skh { position:sticky; top:0; z-index:200; background: <?= $SK['bg']; ?>EB; backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px); border-bottom:1px solid <?= $SK['border']; ?>; }
.skh-bar { display:flex; align-items:center; justify-content:space-between; gap:8px; min-height:58px; }
.skh-logo { display:flex; align-items:center; gap:10px; text-decoration:none; min-width:0; }
.skh-logo img { max-height:32px; max-width:140px; object-fit:contain; }
.skh-logo-name { font-family: <?= $SK['font_display']; ?>; font-size:19px; font-weight:700; color: <?= $SK['ink']; ?>; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; letter-spacing:-.01em; <?= $SK['display_italic'] ? 'font-style:italic;' : ''; ?> <?= $SK['display_case'] === 'uppercase' ? 'text-transform:uppercase; font-size:15px; letter-spacing:.08em;' : ''; ?> }
.skh-actions { display:flex; align-items:center; gap:2px; }
.skh-ic { width:44px; height:44px; display:flex; align-items:center; justify-content:center; color: <?= $SK['ink']; ?>; text-decoration:none; position:relative; background:none; border:none; cursor:pointer; border-radius:50%; transition:background .2s; }
.skh-ic:hover { background: <?= $SK['soft']; ?>; }
.skh-ic svg { width:20px; height:20px; stroke:currentColor; stroke-width:1.7; fill:none; stroke-linecap:round; stroke-linejoin:round; }
.skh-ic svg.fill { fill:currentColor; stroke:none; }
.skh-count { position:absolute; top:5px; right:5px; background: <?= $SK['accent']; ?>; color: <?= $SK['accent_ink']; ?>; font-size:10px; font-weight:700; min-width:16px; height:16px; border-radius:50%; display:flex; align-items:center; justify-content:center; padding:0 4px; }
.skh-nav { display:none; }
@media(min-width:960px){
  .skh-menu { display:none; }
  .skh-bar { min-height:72px; <?= $centered ? 'flex-direction:column; justify-content:center; gap:12px; padding:14px 0 0; position:relative;' : ''; ?> }
  <?php if($centered): ?>
  .skh-bar .skh-logo { order:2; }
  .skh-bar .skh-actions { order:3; position:absolute; left:24px; right:24px; top:16px; justify-content:flex-end; }
  .skh-bar .skh-actions > .skh-ic:first-child { margin-right:auto; }
  .skh-logo-name { font-size:27px; }
  .skh-logo img { max-height:48px; max-width:210px; }
  .skh-nav { display:flex; align-items:center; justify-content:center; gap:32px; width:100%; padding:12px 0 14px; order:4; }
  <?php else: ?>
  .skh-nav { display:flex; align-items:center; gap:28px; flex:1; justify-content:center; }
  .skh-logo-name { font-size:20px; }
  .skh-logo img { max-height:42px; max-width:190px; }
  <?php endif; ?>
  .skh-nav-link { font-family: <?= $SK['font_body']; ?>; font-size:13px; font-weight:500; letter-spacing:.01em; color: <?= $SK['muted']; ?>; text-decoration:none; transition:color .2s; }
  .skh-nav-link:hover { color: <?= $SK['ink']; ?>; }
}

/* ---------- Drawer ---------- */
.skh-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:900; }
.skh-overlay.open { display:block; }
.skh-drawer { position:fixed; top:0; left:0; bottom:0; width:310px; max-width:88vw; background:#fff; z-index:901; padding:22px 20px; overflow-y:auto; transform:translateX(-100%); transition:transform .28s ease; }
.skh-drawer.open { transform:translateX(0); }
.skh-drawer-title { font-family: <?= $SK['font_display']; ?>; font-size:19px; font-weight:700; color: <?= $SK['ink']; ?>; margin-bottom:16px; <?= $SK['display_italic'] ? 'font-style:italic;' : ''; ?> }
.skh-drawer-link { display:block; padding:14px 0; font-family: <?= $SK['font_body']; ?>; font-size:14px; font-weight:500; color: <?= $SK['ink']; ?>; text-decoration:none; border-bottom:1px solid <?= $SK['border']; ?>; }
.skh-drawer-sec-title { font-family: <?= $SK['font_kicker']; ?>; font-size:10px; letter-spacing:.18em; text-transform:uppercase; color: <?= $SK['kicker_color']; ?>; font-weight:600; margin:20px 0 4px; }
.skh-drawer-wa { display:flex; align-items:center; justify-content:center; gap:8px; margin-top:22px; min-height:50px; background:#25D366; color:#fff; font-size:13px; font-weight:600; text-decoration:none; border-radius: <?= $SK['btn_radius']; ?>; }
.skh-drawer-wa svg { width:18px; height:18px; fill:#fff; }

/* ---------- Hero: full-bleed banner, text overlaid ---------- */
.sk-hero { position:relative; }
.sk-hero-title { font-family: <?= $SK['font_display']; ?>; font-size:clamp(32px,8.6vw,60px); line-height:1.08; font-weight:600; margin:14px 0 16px; letter-spacing:-.015em; <?= $SK['display_italic'] ? 'font-style:italic;' : ''; ?> <?= $SK['display_case'] === 'uppercase' ? 'text-transform:uppercase; letter-spacing:-.02em; font-weight:800;' : ''; ?> }
.sk-hero-lead { font-size:15px; line-height:1.7; color: <?= $SK['muted']; ?>; max-width:520px; margin:0 0 26px; }
.sk-hero-tags { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:22px; }
.sk-hero-tag { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:999px; border:1px solid <?= $SK['border']; ?>; background: <?= $SK['surface']; ?>CC; font-family: <?= $SK['font_kicker']; ?>; font-size:10px; font-weight:600; letter-spacing:.1em; text-transform:uppercase; color: <?= $SK['muted']; ?>; }

.sk-hero-full { position:relative; background: <?= $SK['soft']; ?>; overflow:hidden; }
.sk-hero-slide { position:relative; padding:64px 0; transition:opacity .55s ease; }
.sk-hero-slide.has-media { min-height:420px; display:flex; align-items:center; }
.sk-hero-slide:not(.active) { position:absolute; inset:0; opacity:0; pointer-events:none; }
.sk-hero-full-bg { position:absolute; inset:0; }
.sk-hero-full-bg img { width:100%; height:100%; object-fit:cover; display:block; }
.sk-hero-slide .sk-wrap { position:relative; z-index:2; width:100%; }
.sk-hero-slide .sk-hero-body { max-width:640px; }
.sk-hero-dots { position:absolute; left:0; right:0; bottom:18px; z-index:5; display:flex; justify-content:center; gap:8px; }
.sk-hero-dot { width:9px; height:9px; border-radius:50%; border:none; padding:0; background: <?= $SK['ink']; ?>; opacity:.25; cursor:pointer; transition:all .2s; }
.sk-hero-dot.active { opacity:1; transform:scale(1.2); }
<?php if($SK['hero_align'] === 'center'): ?>
.sk-hero-slide { text-align:center; }
.sk-hero-slide .sk-hero-body { margin:0 auto; }
.sk-hero-slide .sk-hero-lead { margin-left:auto; margin-right:auto; }
.sk-hero-slide .sk-btn-row { justify-content:center; }
.sk-hero-slide .sk-hero-tags { justify-content:center; }
.sk-hero-full-bg::after { content:''; position:absolute; inset:0; background:radial-gradient(ellipse at center, <?= $SK['bg']; ?>F2 0%, <?= $SK['bg']; ?>C4 60%, <?= $SK['bg']; ?>6E 100%); }
<?php else: ?>
.sk-hero-full-bg::after { content:''; position:absolute; inset:0; background:linear-gradient(100deg, <?= $SK['soft']; ?>F5 0%, <?= $SK['soft']; ?>C4 48%, <?= $SK['soft']; ?>50 100%); }
<?php endif; ?>
@media(min-width:960px){
  .sk-hero-slide { padding:88px 0; }
  .sk-hero-slide.has-media { min-height:560px; }
}

/* ---------- Ritual (signature 3-step strip) ---------- */
.sk-ritual { display:grid; grid-template-columns:1fr; gap:12px; }
@media(min-width:768px){ .sk-ritual { grid-template-columns:repeat(3,1fr); gap:18px; } }
.sk-ritual-step { background: <?= $SK['card']; ?>; border:1px solid <?= $SK['border']; ?>; border-radius: <?= $SK['radius']; ?>; padding:26px 22px; position:relative; overflow:hidden; }
.sk-ritual-n { font-family: <?= $SK['font_display']; ?>; font-size:34px; font-weight:600; color: <?= $SK['accent']; ?>; line-height:1; margin-bottom:14px; <?= $SK['display_italic'] ? 'font-style:italic;' : ''; ?> }
.sk-ritual-t { font-family: <?= $SK['font_body']; ?>; font-size:15px; font-weight:700; color: <?= $SK['ink']; ?>; margin-bottom:6px; }
.sk-ritual-d { font-size:13px; line-height:1.7; color: <?= $SK['muted']; ?>; }

/* ---------- Product rail (horizontal scroll w/ arrows) ---------- */
.sk-rail { position:relative; }
.sk-rail-track { display:flex; gap:12px; overflow-x:auto; scroll-snap-type:x mandatory; scrollbar-width:none; -webkit-overflow-scrolling:touch; padding-bottom:6px; }
.sk-rail-track::-webkit-scrollbar { display:none; }
.sk-rail-track .sk-card { flex:0 0 64%; scroll-snap-align:start; }
@media(min-width:640px){ .sk-rail-track .sk-card { flex-basis:31%; } .sk-rail-track { gap:18px; } }
@media(min-width:960px){ .sk-rail-track .sk-card { flex-basis:23.5%; } .sk-rail-track { gap:22px; } }
.sk-rail-nav { display:flex; align-items:center; gap:8px; }
.sk-rail-btn { width:44px; height:44px; border-radius:50%; border:1.5px solid <?= $SK['border']; ?>; background: <?= $SK['surface']; ?>; color: <?= $SK['ink']; ?>; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; transition:all .2s; flex-shrink:0; }
.sk-rail-btn:hover { border-color: <?= $SK['ink']; ?>; }
.sk-rail-btn svg { width:16px; height:16px; stroke:currentColor; stroke-width:2; fill:none; }
.sk-sec-side { display:flex; align-items:center; gap:14px; }

/* ---------- Story split (promo / about) ---------- */
.sk-story { display:grid; grid-template-columns:1fr; border-radius: <?= $SK['radius']; ?>; overflow:hidden; }
.sk-story-media { min-height:240px; background: <?= $SK['accent_soft']; ?>; position:relative; }
.sk-story-media img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
.sk-story-txt { padding:32px 24px; display:flex; flex-direction:column; justify-content:center; background: <?= $SK['soft']; ?>; }
.sk-story-invert .sk-story-txt { background: <?= $SK['ink']; ?>; color: <?= $SK['bg']; ?>; }
.sk-story-invert .sk-story-title { color: <?= $SK['bg']; ?>; }
.sk-story-invert .sk-story-lead { color: <?= $SK['bg']; ?>B3; }
.sk-story-title { font-family: <?= $SK['font_display']; ?>; font-size:clamp(22px,5.6vw,32px); font-weight:700; color: <?= $SK['ink']; ?>; margin:10px 0 12px; line-height:1.15; letter-spacing:-.01em; <?= $SK['display_italic'] ? 'font-style:italic;' : ''; ?> }
.sk-story-lead { font-size:14px; line-height:1.7; color: <?= $SK['muted']; ?>; margin-bottom:22px; }
@media(min-width:768px){ .sk-story { grid-template-columns:1fr 1fr; } .sk-story-txt { padding:48px 44px; } .sk-story-media { min-height:340px; } }

/* ---------- Journal cards (editorial image + caption) ---------- */
.sk-journal-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
@media(min-width:768px){ .sk-journal-grid { grid-template-columns:repeat(4,1fr); gap:18px; } }
.sk-journal { background: <?= $SK['card']; ?>; border:1px solid <?= $SK['border']; ?>; border-radius: <?= $SK['radius']; ?>; overflow:hidden; text-decoration:none; display:block; transition:transform .25s, box-shadow .25s; }
.sk-journal:hover { transform:translateY(-3px); box-shadow:0 10px 30px rgba(0,0,0,.08); }
.sk-journal-img { aspect-ratio:4/3; overflow:hidden; background: <?= $SK['soft']; ?>; }
.sk-journal-img img { width:100%; height:100%; object-fit:cover; transition:transform .5s; }
.sk-journal:hover .sk-journal-img img { transform:scale(1.05); }
.sk-journal-body { padding:14px 16px 16px; }
.sk-journal-kicker { font-family: <?= $SK['font_kicker']; ?>; font-size:9px; letter-spacing:.14em; text-transform:uppercase; color: <?= $SK['kicker_color']; ?>; margin-bottom:6px; }
.sk-journal-title { font-size:13px; font-weight:600; color: <?= $SK['ink']; ?>; line-height:1.45; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }

/* ---------- Sections ---------- */
.sk-sec { padding:52px 0; }
@media(min-width:960px){ .sk-sec { padding:72px 0; } }
.sk-band { background: <?= $SK['soft']; ?>; }
.sk-sec-head { display:flex; flex-direction:column; gap:6px; margin-bottom:26px; }
.sk-sec-link { font-family: <?= $SK['font_body']; ?>; font-size:13px; font-weight:600; color: <?= $SK['accent']; ?>; text-decoration:none; align-self:flex-start; display:inline-flex; align-items:center; gap:5px; }
.sk-sec-link:hover { opacity:.8; }
@media(min-width:640px){ .sk-sec-head { flex-direction:row; align-items:flex-end; justify-content:space-between; gap:16px; } }
.sk-sec-head-center { text-align:center; align-items:center; }
.sk-rule { width:44px; height:2px; background: <?= $SK['accent']; ?>; margin:12px auto 0; border-radius:2px; }

/* ---------- Product cards ---------- */
.sk-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
@media(min-width:640px){ .sk-grid { grid-template-columns:repeat(3,1fr); gap:18px; } }
@media(min-width:960px){ .sk-grid { grid-template-columns:repeat(4,1fr); gap:22px; } }
.sk-card { position:relative; background: <?= $SK['card']; ?>; border-radius: <?= $SK['radius']; ?>; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.05), 0 8px 24px rgba(0,0,0,.05); transition:transform .25s, box-shadow .25s; }
.sk-card:hover { transform:translateY(-4px); box-shadow:0 4px 12px rgba(0,0,0,.07), 0 20px 44px rgba(0,0,0,.10); }
.sk-badge { position:absolute; top:10px; z-index:2; font-family: <?= $SK['font_kicker']; ?>; font-size:10px; font-weight:700; letter-spacing:.06em; padding:5px 10px; border-radius:999px; }
.sk-badge-sale { left:10px; background: <?= $SK['accent']; ?>; color: <?= $SK['accent_ink']; ?>; }
.sk-badge-new { left:10px; background: <?= $SK['ink']; ?>; color: <?= $SK['bg']; ?>; }
.sk-badge-out { right:10px; background:#EF4444; color:#fff; }
.sk-card-media { display:block; position:relative; aspect-ratio:4/5; overflow:hidden; background: <?= $SK['soft']; ?>; }
.sk-card-media img { width:100%; height:100%; object-fit:cover; transition:transform .5s ease; }
.sk-card:hover .sk-card-media img { transform:scale(1.05); }
.sk-card-ph { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color: <?= $SK['accent']; ?>; }
.sk-card-ph span { font-family: <?= $SK['font_display']; ?>; font-size:40px; font-weight:600; <?= $SK['display_italic'] ? 'font-style:italic;' : ''; ?> }
.sk-card-body { padding:13px 14px 14px; }
@media(min-width:960px){ .sk-card-body { padding:16px 18px 18px; } }
.sk-card-range { font-family: <?= $SK['font_kicker']; ?>; font-size:9px; letter-spacing:.14em; text-transform:uppercase; color: <?= $SK['kicker_color']; ?>; margin-bottom:4px; }
.sk-card-name { font-family: <?= $SK['font_body']; ?>; font-size:14px; font-weight:600; color: <?= $SK['ink']; ?>; line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:38px; text-decoration:none; margin-bottom:10px; }
.sk-card-name:hover { color: <?= $SK['accent']; ?>; }
.sk-card-foot { display:flex; flex-direction:column; gap:10px; }
.sk-card-price { font-family: <?= $SK['font_body']; ?>; font-size:16px; font-weight:700; color: <?= $SK['ink']; ?>; letter-spacing:-.01em; }
.sk-card-price .old { font-size:12px; color: <?= $SK['muted']; ?>; text-decoration:line-through; margin-left:7px; font-weight:400; }
.sk-card-actions { display:flex; gap:8px; }
.sk-btn-add { flex:1; min-height:44px; border-radius: <?= $SK['btn_radius']; ?>; background: <?= $skBtnAdd; ?>; color: <?= $skBtnAddIn; ?>; border:none; font-family: <?= $SK['font_body']; ?>; font-size:12px; font-weight:600; cursor:pointer; transition:opacity .2s, transform .15s; }
.sk-btn-add:hover { opacity:.85; }
.sk-btn-add:active { transform:scale(.97); }
.sk-btn-add:disabled { opacity:.3; cursor:not-allowed; }
.sk-btn-wa { width:48px; min-height:44px; flex-shrink:0; border-radius: <?= $SK['btn_radius']; ?>; background:#25D366; color:#fff; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s, transform .15s; }
.sk-btn-wa:hover { background:#1EBE5B; }
.sk-btn-wa:active { transform:scale(.95); }

/* ---------- Category cards ---------- */
.sk-fam-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
@media(min-width:640px){ .sk-fam-grid { grid-template-columns:repeat(3,1fr); gap:14px; } }
@media(min-width:960px){ .sk-fam-grid { grid-template-columns:repeat(4,1fr); gap:18px; } }
.sk-fam { position:relative; display:block; overflow:hidden; border-radius: <?= $SK['radius']; ?>; aspect-ratio:4/4.6; text-decoration:none; background: <?= $SK['soft']; ?>; }
.sk-fam-media { position:absolute; inset:0; }
.sk-fam-media img { width:100%; height:100%; object-fit:cover; transition:transform .6s ease; }
.sk-fam:hover .sk-fam-media img { transform:scale(1.06); }
.sk-fam-ph { width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-family: <?= $SK['font_display']; ?>; font-size:52px; color: <?= $SK['accent']; ?>; <?= $SK['display_italic'] ? 'font-style:italic;' : ''; ?> }
.sk-fam-veil { position:absolute; inset:0; background:linear-gradient(180deg, transparent 45%, rgba(0,0,0,.68) 100%); }
.sk-fam-body { position:absolute; left:0; right:0; bottom:0; padding:16px; z-index:2; }
.sk-fam-name { font-family: <?= $SK['font_body']; ?>; font-size:15px; font-weight:600; color:#fff; }
.sk-fam-count { font-family: <?= $SK['font_kicker']; ?>; font-size:9px; letter-spacing:.14em; text-transform:uppercase; color:rgba(255,255,255,.8); margin-top:4px; }

/* ---------- Values / trust ---------- */
.sk-values { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
@media(min-width:960px){ .sk-values { grid-template-columns:repeat(4,1fr); gap:16px; } }
.sk-value { text-align:center; padding:24px 14px; border-radius: <?= $SK['radius']; ?>; background: <?= $SK['card']; ?>; border:1px solid <?= $SK['border']; ?>; }
.sk-value-icon { font-size:20px; color: <?= $SK['kicker_color']; ?>; margin-bottom:10px; }
.sk-value-title { font-family: <?= $SK['font_body']; ?>; font-size:13px; font-weight:700; color: <?= $SK['ink']; ?>; margin-bottom:4px; }
.sk-value-text { font-size:12px; color: <?= $SK['muted']; ?>; line-height:1.6; }

/* ---------- Promo ---------- */
.sk-promo { display:flex; flex-direction:column; border-radius: <?= $SK['radius']; ?>; overflow:hidden; background: <?= $SK['card']; ?>; box-shadow:0 1px 3px rgba(0,0,0,.05), 0 10px 30px rgba(0,0,0,.06); }
.sk-promo-media { min-height:200px; background: <?= $SK['soft']; ?>; }
.sk-promo-media img { width:100%; height:100%; object-fit:cover; display:block; }
.sk-promo-body { padding:28px 22px; display:flex; flex-direction:column; justify-content:center; }
.sk-promo-title { font-family: <?= $SK['font_display']; ?>; font-size:clamp(20px,5vw,30px); font-weight:700; color: <?= $SK['ink']; ?>; margin:8px 0 16px; <?= $SK['display_italic'] ? 'font-style:italic;' : ''; ?> }
@media(min-width:768px){ .sk-promo { flex-direction:row; } .sk-promo-media { flex:1; } .sk-promo-body { flex:1; padding:44px; } }

/* ---------- CTA ---------- */
.sk-cta { text-align:center; padding:48px 20px; border-radius: <?= $SK['radius']; ?>; background: <?= $SK['accent_soft']; ?>; }
.sk-cta-title { font-family: <?= $SK['font_display']; ?>; font-size:clamp(24px,6vw,36px); font-weight:700; color: <?= $SK['ink']; ?>; margin:0 0 12px; letter-spacing:-.01em; <?= $SK['display_italic'] ? 'font-style:italic;' : ''; ?> }
.sk-cta-text { color: <?= $SK['muted']; ?>; max-width:480px; margin:0 auto 24px; font-size:14px; line-height:1.7; }
.sk-cta .sk-btn-row { justify-content:center; }

/* ---------- Testimonials ---------- */
.sk-testi-grid { display:grid; grid-template-columns:1fr; gap:14px; }
@media(min-width:768px){ .sk-testi-grid { grid-template-columns:repeat(3,1fr); gap:18px; } }
.sk-testi { background: <?= $SK['card']; ?>; border:1px solid <?= $SK['border']; ?>; border-radius: <?= $SK['radius']; ?>; padding:24px; }
.sk-testi-stars { color:#F59E0B; letter-spacing:3px; font-size:14px; margin-bottom:12px; }
.sk-testi-text { font-size:14px; color: <?= $SK['muted']; ?>; line-height:1.7; margin-bottom:14px; }
.sk-testi-author { font-size:12px; font-weight:700; color: <?= $SK['ink']; ?>; letter-spacing:.04em; text-transform:uppercase; }

/* ---------- Brands / insta / faq / contact / hours ---------- */
.sk-brands { display:flex; flex-wrap:wrap; gap:10px; align-items:center; justify-content:center; }
.sk-brand { padding:10px 20px; border:1px solid <?= $SK['border']; ?>; border-radius:999px; font-family: <?= $SK['font_display']; ?>; font-size:14px; color: <?= $SK['ink']; ?>; background: <?= $SK['card']; ?>; <?= $SK['display_italic'] ? 'font-style:italic;' : ''; ?> }
.sk-insta-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
@media(min-width:768px){ .sk-insta-grid { grid-template-columns:repeat(5,1fr); } }
.sk-insta-item { aspect-ratio:1; overflow:hidden; border-radius: <?= $SK['radius']; ?>; }
.sk-insta-item img { width:100%; height:100%; object-fit:cover; transition:transform .35s; }
.sk-insta-item:hover img { transform:scale(1.06); }
.sk-faq-list { max-width:720px; margin:0 auto; }
.sk-faq { background: <?= $SK['card']; ?>; border:1px solid <?= $SK['border']; ?>; border-radius: <?= $SK['radius']; ?>; margin-bottom:10px; overflow:hidden; }
.sk-faq-q { padding:16px 18px; font-size:14px; font-weight:600; color: <?= $SK['ink']; ?>; cursor:pointer; display:flex; justify-content:space-between; align-items:center; min-height:52px; }
.sk-faq-q::after { content:'+'; color: <?= $SK['kicker_color']; ?>; font-size:20px; }
.sk-faq.open .sk-faq-q::after { content:'\2212'; }
.sk-faq-a { max-height:0; overflow:hidden; transition:max-height .3s ease; }
.sk-faq.open .sk-faq-a { max-height:320px; }
.sk-faq-a p { padding:0 18px 16px; font-size:14px; color: <?= $SK['muted']; ?>; line-height:1.7; margin:0; }
.sk-contact-grid { display:grid; grid-template-columns:1fr; gap:12px; }
@media(min-width:768px){ .sk-contact-grid { grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; } }
.sk-contact { text-align:center; padding:24px; border:1px solid <?= $SK['border']; ?>; border-radius: <?= $SK['radius']; ?>; background: <?= $SK['card']; ?>; }
.sk-contact-ic { width:44px; height:44px; margin:0 auto 12px; border-radius:50%; background: <?= $SK['accent_soft']; ?>; display:flex; align-items:center; justify-content:center; color: <?= $SK['kicker_color']; ?>; }
.sk-contact-ic svg { width:20px; height:20px; }
.sk-contact-l { font-family: <?= $SK['font_kicker']; ?>; font-size:9px; letter-spacing:.16em; text-transform:uppercase; color: <?= $SK['kicker_color']; ?>; margin-bottom:4px; }
.sk-contact-v { font-size:14px; font-weight:600; color: <?= $SK['ink']; ?>; word-break:break-word; }
.sk-hours { max-width:520px; margin:0 auto; }
.sk-hours-row { padding:12px 0; border-bottom:1px solid <?= $SK['border']; ?>; font-size:14px; color: <?= $SK['muted']; ?>; text-align:center; }
.sk-hours-row:last-child { border-bottom:none; }

/* ---------- Newsletter ---------- */
.sk-news { text-align:center; padding:44px 20px; border-radius: <?= $SK['radius']; ?>; background: <?= $SK['ink']; ?>; color: <?= $SK['bg']; ?>; }
.sk-news-title { font-family: <?= $SK['font_display']; ?>; font-size:clamp(20px,5vw,30px); font-weight:700; margin-bottom:8px; <?= $SK['display_italic'] ? 'font-style:italic;' : ''; ?> }
.sk-news-text { opacity:.7; max-width:420px; margin:0 auto 22px; font-size:14px; }
.sk-news-form { display:flex; flex-direction:column; gap:10px; max-width:440px; margin:0 auto; }
.sk-news-form input { min-height:50px; padding:14px 18px; border:none; border-radius: <?= $SK['btn_radius']; ?>; font-size:15px; outline:none; font-family: <?= $SK['font_body']; ?>; box-sizing:border-box; }
.sk-news-form button { min-height:50px; border:none; border-radius: <?= $SK['btn_radius']; ?>; background: <?= $skBtnAcc; ?>; color: <?= $skBtnAccIn; ?>; font-family: <?= $SK['font_body']; ?>; font-weight:600; font-size:13px; cursor:pointer; }
@media(min-width:640px){ .sk-news-form { flex-direction:row; } .sk-news-form input { flex:1; } }

/* ---------- Breadcrumbs ---------- */
.sk-crumbs { padding:20px 0 0; font-family: <?= $SK['font_kicker']; ?>; font-size:10px; letter-spacing:.14em; text-transform:uppercase; color: <?= $SK['muted']; ?>; }
.sk-crumbs a { color: <?= $SK['muted']; ?>; text-decoration:none; }
.sk-crumbs a:hover { color: <?= $SK['accent']; ?>; }
.sk-crumbs .sep { margin:0 8px; color: <?= $SK['border']; ?>; }

/* ---------- Catalogue ---------- */
.sk-page { padding:24px 0 72px; }
.sk-h1 { font-family: <?= $SK['font_display']; ?>; font-size:clamp(26px,7vw,40px); font-weight:700; color: <?= $SK['ink']; ?>; margin:0; letter-spacing:-.015em; <?= $SK['display_italic'] ? 'font-style:italic;' : ''; ?> <?= $SK['display_case'] === 'uppercase' ? 'text-transform:uppercase;' : ''; ?> }
.sk-count { font-family: <?= $SK['font_kicker']; ?>; font-size:10px; letter-spacing:.12em; text-transform:uppercase; color: <?= $SK['muted']; ?>; margin-top:8px; }
.sk-filters { margin:24px 0 28px; display:flex; flex-direction:column; gap:12px; }
.sk-search { position:relative; }
.sk-search input { width:100%; min-height:50px; padding:13px 14px 13px 44px; border:1.5px solid <?= $SK['border']; ?>; border-radius: <?= $SK['btn_radius']; ?>; font-size:15px; background: <?= $SK['surface']; ?>; color: <?= $SK['ink']; ?>; outline:none; font-family: <?= $SK['font_body']; ?>; box-sizing:border-box; }
.sk-search input:focus { border-color: <?= $SK['accent']; ?>; }
.sk-search input::placeholder { color: <?= $SK['muted']; ?>; }
.sk-search svg { position:absolute; left:15px; top:50%; transform:translateY(-50%); color: <?= $SK['muted']; ?>; }
.sk-chips { display:flex; gap:8px; overflow-x:auto; scrollbar-width:none; padding-bottom:4px; -webkit-overflow-scrolling:touch; }
.sk-chips::-webkit-scrollbar { display:none; }
.sk-chip { flex-shrink:0; min-height:44px; display:inline-flex; align-items:center; padding:10px 18px; border-radius:999px; border:1.5px solid <?= $SK['border']; ?>; background: <?= $SK['surface']; ?>; font-family: <?= $SK['font_body']; ?>; font-size:13px; font-weight:500; color: <?= $SK['muted']; ?>; cursor:pointer; text-decoration:none; transition:all .2s; white-space:nowrap; }
.sk-chip:hover { border-color: <?= $SK['accent']; ?>; color: <?= $SK['ink']; ?>; }
.sk-chip.active { background: <?= $SK['ink']; ?>; color: <?= $SK['bg']; ?>; border-color: <?= $SK['ink']; ?>; }
@media(min-width:640px){ .sk-filters { flex-direction:row; align-items:center; } .sk-search { flex:1; max-width:380px; } }
.sk-pagination { display:flex; justify-content:center; gap:8px; margin-top:40px; flex-wrap:wrap; }
.sk-page-btn { min-width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center; padding:0 14px; border-radius:999px; border:1.5px solid <?= $SK['border']; ?>; background: <?= $SK['surface']; ?>; font-size:14px; font-weight:500; color: <?= $SK['muted']; ?>; text-decoration:none; }
.sk-page-btn.active { background: <?= $SK['ink']; ?>; color: <?= $SK['bg']; ?>; border-color: <?= $SK['ink']; ?>; }
.sk-empty { text-align:center; padding:80px 20px; }
.sk-empty-title { font-family: <?= $SK['font_display']; ?>; font-size:22px; font-weight:700; color: <?= $SK['ink']; ?>; margin-bottom:8px; }
.sk-empty-text { color: <?= $SK['muted']; ?>; font-size:14px; margin-bottom:22px; }

/* ---------- Product detail ---------- */
.sk-pd { padding:28px 0 72px; }
.sk-pd-layout { display:grid; grid-template-columns:1fr; gap:28px; align-items:start; }
@media(min-width:960px){ .sk-pd-layout { grid-template-columns:1.05fr 1fr; gap:52px; } }
.sk-pd-media { aspect-ratio:4/5; overflow:hidden; background: <?= $SK['soft']; ?>; border-radius: <?= $SK['radius']; ?>; position:relative; }
@media(min-width:960px){ .sk-pd-media { position:sticky; top:90px; } }
.sk-pd-media img { width:100%; height:100%; object-fit:cover; }
.sk-pd-ph { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color: <?= $SK['accent']; ?>; font-family: <?= $SK['font_display']; ?>; font-size:60px; font-weight:600; }
.sk-pd-badge { position:absolute; top:14px; left:14px; z-index:2; background: <?= $SK['accent']; ?>; color: <?= $SK['accent_ink']; ?>; font-family: <?= $SK['font_kicker']; ?>; font-size:10px; font-weight:700; letter-spacing:.06em; padding:6px 12px; border-radius:999px; }
.sk-pd-name { font-family: <?= $SK['font_display']; ?>; font-size:clamp(28px,7.4vw,42px); font-weight:700; color: <?= $SK['ink']; ?>; line-height:1.1; margin:0 0 12px; letter-spacing:-.015em; <?= $SK['display_italic'] ? 'font-style:italic;' : ''; ?> <?= $SK['display_case'] === 'uppercase' ? 'text-transform:uppercase; font-size:clamp(24px,6.4vw,36px);' : ''; ?> }
.sk-pd-price { font-family: <?= $SK['font_body']; ?>; font-size:26px; font-weight:700; color: <?= $SK['ink']; ?>; margin-bottom:8px; letter-spacing:-.01em; }
.sk-pd-price .old { font-size:16px; color: <?= $SK['muted']; ?>; text-decoration:line-through; margin-left:10px; font-weight:400; }
.sk-pd-stock { font-size:12px; font-weight:600; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
.sk-pd-stock::before { content:''; width:8px; height:8px; border-radius:50%; background:currentColor; }
.sk-pd-stock.in { color:#16A34A; }
.sk-pd-stock.out { color:#DC2626; }
.sk-pd-desc { font-size:15px; line-height:1.8; color: <?= $SK['muted']; ?>; margin-bottom:24px; }
.sk-pd-tags { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:22px; }
.sk-pd-tag { padding:7px 13px; border:1px solid <?= $SK['border']; ?>; border-radius:999px; font-size:12px; color: <?= $SK['muted']; ?>; background: <?= $SK['surface']; ?>; }
.sk-pd-tag b { color: <?= $SK['ink']; ?>; font-weight:600; }
.sk-pd-qty { display:inline-flex; align-items:center; gap:2px; border:1.5px solid <?= $SK['border']; ?>; border-radius: <?= $SK['btn_radius']; ?>; padding:3px; margin-bottom:18px; }
.sk-pd-qty button { width:44px; height:44px; border-radius: <?= $SK['btn_radius']; ?>; border:none; background:transparent; font-size:19px; cursor:pointer; color: <?= $SK['ink']; ?>; transition:background .2s; }
.sk-pd-qty button:hover { background: <?= $SK['soft']; ?>; }
.sk-pd-qty span { font-size:16px; font-weight:700; min-width:36px; text-align:center; color: <?= $SK['ink']; ?>; }
.sk-pd-actions { display:flex; flex-direction:column; gap:10px; }
@media(min-width:640px){ .sk-pd-actions { flex-direction:row; } .sk-pd-actions .sk-btn { flex:1; } }
.sk-pd-trust { display:flex; flex-wrap:wrap; gap:8px 18px; margin-top:20px; }
.sk-pd-trust span { display:inline-flex; align-items:center; gap:7px; font-size:12px; color: <?= $SK['muted']; ?>; font-weight:500; }
.sk-pd-trust svg { width:15px; height:15px; color: <?= $SK['kicker_color']; ?>; flex-shrink:0; }
.sk-pd-specs { margin-top:30px; border-top:1px solid <?= $SK['border']; ?>; padding-top:18px; }
.sk-pd-specs-title { font-family: <?= $SK['font_kicker']; ?>; font-size:10px; letter-spacing:.18em; text-transform:uppercase; color: <?= $SK['kicker_color']; ?>; font-weight:600; margin-bottom:8px; }
.sk-pd-spec { display:flex; justify-content:space-between; gap:12px; padding:11px 0; border-bottom:1px solid <?= $SK['border']; ?>; font-size:14px; color: <?= $SK['muted']; ?>; }
.sk-pd-spec b { color: <?= $SK['ink']; ?>; font-weight:600; text-align:right; }
.sk-pd-extra { margin-top:44px; }
.sk-pd-extra .sk-grid { grid-template-columns:repeat(2,1fr); gap:12px; }
@media(min-width:960px){ .sk-pd-extra .sk-grid { gap:16px; } }

/* ---------- WhatsApp order modal (bottom sheet on mobile) ---------- */
.skwa-modal { display:none; position:fixed; inset:0; z-index:9999; }
.skwa-modal.open { display:block; }
.skwa-veil { position:absolute; inset:0; background:rgba(0,0,0,.5); backdrop-filter:blur(2px); }
.skwa-card { position:absolute; left:0; right:0; bottom:0; max-height:92vh; overflow-y:auto; background:#fff; border-radius:20px 20px 0 0; }
@media(min-width:640px){ .skwa-card { left:50%; right:auto; bottom:auto; top:50%; transform:translate(-50%,-50%); width:460px; border-radius: <?= $SK['radius']; ?>; box-shadow:0 30px 80px rgba(0,0,0,.25); } }
.skwa-grip { width:40px; height:4px; border-radius:99px; background: <?= $SK['border']; ?>; margin:10px auto 0; }
@media(min-width:640px){ .skwa-grip { display:none; } }
.skwa-head { display:flex; align-items:center; justify-content:space-between; padding:16px 20px; border-bottom:1px solid <?= $SK['border']; ?>; }
.skwa-title { font-family: <?= $SK['font_display']; ?>; font-size:18px; font-weight:700; color: <?= $SK['ink']; ?>; margin:0; <?= $SK['display_italic'] ? 'font-style:italic;' : ''; ?> }
.skwa-x { width:40px; height:40px; border-radius:50%; border:none; background: <?= $SK['soft']; ?>; color: <?= $SK['ink']; ?>; cursor:pointer; font-size:18px; }
.skwa-body { padding:20px; }
.skwa-prod { display:flex; gap:14px; align-items:center; padding:12px; background: <?= $SK['soft']; ?>; border-radius: <?= $SK['radius']; ?>; margin-bottom:18px; }
.skwa-prod img { width:54px; height:54px; border-radius:10px; object-fit:cover; flex-shrink:0; }
.skwa-prod-name { font-size:14px; font-weight:600; color: <?= $SK['ink']; ?>; margin:0 0 4px; line-height:1.3; }
.skwa-prod-price { font-size:16px; font-weight:700; color: <?= $SK['kicker_color']; ?>; }
.skwa-fields { display:flex; flex-direction:column; gap:13px; }
.skwa-fields label { font-size:11px; font-weight:600; color: <?= $SK['muted']; ?>; margin-bottom:5px; display:block; text-transform:uppercase; letter-spacing:.08em; }
.skwa-fields input, .skwa-fields textarea { width:100%; min-height:50px; padding:13px 14px; border:1.5px solid <?= $SK['border']; ?>; border-radius:12px; font-size:15px; background:#fff; color: <?= $SK['ink']; ?>; outline:none; box-sizing:border-box; font-family: <?= $SK['font_body']; ?>; }
.skwa-fields input:focus, .skwa-fields textarea:focus { border-color: <?= $SK['accent']; ?>; }
.skwa-fields textarea { resize:vertical; min-height:60px; }
.skwa-actions { display:flex; flex-direction:column; gap:10px; margin-top:18px; padding-bottom:env(safe-area-inset-bottom); }
.skwa-send { min-height:52px; border-radius: <?= $SK['btn_radius']; ?>; border:none; background:#25D366; color:#fff; font-size:14px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; }
.skwa-send svg { width:17px; height:17px; fill:currentColor; }
.skwa-cancel { min-height:48px; border-radius: <?= $SK['btn_radius']; ?>; border:1.5px solid <?= $SK['border']; ?>; background:transparent; color: <?= $SK['ink']; ?>; font-size:14px; font-weight:600; cursor:pointer; }
@media(min-width:640px){ .skwa-actions { flex-direction:row; } .skwa-send { flex:1; } }

/* ---------- Media shape per skin ---------- */
<?php if($SK['media_shape'] === 'arch'): ?>
.sk-card-media { border-radius:110px 110px 0 0; }
.sk-card { border-radius:110px 110px <?= $SK['radius']; ?> <?= $SK['radius']; ?>; }
.sk-fam { border-radius:110px 110px <?= $SK['radius']; ?> <?= $SK['radius']; ?>; }
.sk-pd-media { border-radius:140px 140px <?= $SK['radius']; ?> <?= $SK['radius']; ?>; }
<?php elseif($SK['media_shape'] === 'sharp'): ?>
.sk-card, .sk-fam, .sk-promo, .sk-cta, .sk-news, .sk-value, .sk-contact, .sk-testi, .sk-faq, .sk-journal, .sk-story, .sk-ritual-step { border-radius:2px; }
.sk-card-media, .sk-pd-media { border-radius:0; }
<?php endif; ?>

/* ---------- Appearance: header text colour ---------- */
<?php if(!empty($SK['header_ink'])): ?>
.theme-<?= $tk; ?> .skh-logo-name, .theme-<?= $tk; ?> .skh-ic, .theme-<?= $tk; ?> .skh-nav-link, .theme-<?= $tk; ?> .skh-drawer-link, .theme-<?= $tk; ?> .skh-drawer-title, .theme-<?= $tk; ?> .skh-count { color: <?= $SK['header_ink']; ?> !important; }
<?php endif; ?>
</style>

<?php if($skMarquee): ?>
<div class="skh-announce skh-marquee"><div class="skh-marquee-track">
  <?php for($i = 0; $i < 2; $i++): foreach($skMarquee as $mi): ?><span><?= htmlspecialchars($mi); ?></span><?php endforeach; endfor; ?>
</div></div>
<?php elseif($announce): ?>
<div class="skh-announce"><?= htmlspecialchars($announce); ?></div>
<?php endif; ?>

<header class="skh">
  <div class="sk-wrap skh-bar">
    <button class="skh-ic skh-menu" onclick="skMenu(true)" aria-label="Menu">
      <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <a href="<?= base_url('store/' . $slug); ?>" class="skh-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>">
      <?php else: ?>
        <span class="skh-logo-name"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Store')); ?></span>
      <?php endif; ?>
    </a>
    <nav class="skh-nav">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="skh-nav-link">Shop</a>
      <?php if(($settings->show_categories ?? 1) && !empty($categories)): foreach(array_slice($categories, 0, 3) as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="skh-nav-link"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; endif; ?>
      <a href="<?= base_url('store/' . $slug . '/about'); ?>" class="skh-nav-link">About</a>
      <?php if($settings->allow_services ?? false): ?>
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="skh-nav-link">Services</a>
      <?php endif; ?>
    </nav>
    <div class="skh-actions">
      <?php if($settings->show_search ?? 1): ?>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="skh-ic" aria-label="Search">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      </a>
      <?php endif; ?>
      <?php if($skWaNum): ?>
      <a href="https://wa.me/<?= $skWaNum; ?>" target="_blank" class="skh-ic" aria-label="WhatsApp">
        <svg class="fill" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
      </a>
      <?php endif; ?>
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="skh-ic" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="skh-count" id="cart-count">0</span>
      </a>
    </div>
  </div>
</header>

<div class="skh-overlay" id="skh-overlay" onclick="skMenu(false)"></div>
<div class="skh-drawer" id="skh-drawer">
  <div class="skh-drawer-title">
    <?php if($logo): ?><img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>" style="max-height:34px;max-width:150px;display:block;margin-bottom:10px;"><?php endif; ?>
    <?= htmlspecialchars($store->store_name ?? 'Menu'); ?>
  </div>
  <a href="<?= base_url('store/' . $slug); ?>" class="skh-drawer-link" onclick="skMenu(false)">Home</a>
  <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="skh-drawer-link" onclick="skMenu(false)">Shop</a>
  <a href="<?= base_url('store/' . $slug . '/about'); ?>" class="skh-drawer-link" onclick="skMenu(false)">About</a>
  <?php if($settings->allow_services ?? false): ?>
  <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="skh-drawer-link" onclick="skMenu(false)">Services</a>
  <?php endif; ?>
  <?php if(($settings->show_categories ?? 1) && !empty($categories)): ?>
  <div class="skh-drawer-sec-title">Collections</div>
  <?php foreach(array_slice($categories, 0, 8) as $cat): ?>
  <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="skh-drawer-link" onclick="skMenu(false)"><?= htmlspecialchars($cat->category_name); ?></a>
  <?php endforeach; ?>
  <?php endif; ?>
  <?php if(!empty($settings->store_phone)): ?>
  <div class="skh-drawer-sec-title">Contact</div>
  <a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>" class="skh-drawer-link"><?= htmlspecialchars($settings->store_phone); ?></a>
  <?php endif; ?>
  <?php if($skWaNum): ?>
  <a href="https://wa.me/<?= $skWaNum; ?>" target="_blank" class="skh-drawer-wa" onclick="skMenu(false)">
    <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
    Chat on WhatsApp
  </a>
  <?php endif; ?>
</div>

<!-- WhatsApp order modal — bottom sheet on mobile, centered card on desktop -->
<div class="skwa-modal" id="skwa-modal">
  <div class="skwa-veil" onclick="skWaClose()"></div>
  <div class="skwa-card">
    <div class="skwa-grip"></div>
    <div class="skwa-head">
      <h3 class="skwa-title">Order via WhatsApp</h3>
      <button class="skwa-x" onclick="skWaClose()" aria-label="Close">&times;</button>
    </div>
    <div class="skwa-body">
      <div class="skwa-prod">
        <img id="skwa-img" src="" alt="" style="display:none;">
        <div>
          <p class="skwa-prod-name" id="skwa-name"></p>
          <div class="skwa-prod-price" id="skwa-price"></div>
        </div>
      </div>
      <div class="skwa-fields">
        <div><label>Your Name</label><input type="text" id="skwa-cname" placeholder="Enter your name" autocomplete="name"></div>
        <div><label>Phone Number</label><input type="tel" id="skwa-cphone" placeholder="Enter your phone number" autocomplete="tel"></div>
        <div><label>Quantity</label><input type="number" id="skwa-qty" value="1" min="1" inputmode="numeric"></div>
        <div><label>Note (optional)</label><textarea id="skwa-note" placeholder="Skin type, gift wrap, occasion..."></textarea></div>
      </div>
      <div class="skwa-actions">
        <button class="skwa-send" onclick="skWaSend()"><svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg> Send via WhatsApp</button>
        <button class="skwa-cancel" onclick="skWaClose()">Cancel</button>
      </div>
    </div>
  </div>
</div>

<script>
  function skRail(id, dir){
    var el = document.getElementById(id);
    if(el) el.scrollBy({left: dir * el.clientWidth * .8, behavior: 'smooth'});
  }
  function skMenu(open){
    document.getElementById('skh-drawer').classList.toggle('open', open);
    document.getElementById('skh-overlay').classList.toggle('open', open);
    document.body.style.overflow = open ? 'hidden' : '';
  }
  var skWaProduct = null;
  function skWaOrder(id, name, price, image, qty){
    skWaProduct = {id:id, name:name, price:price, image:image};
    document.getElementById('skwa-name').textContent = name;
    document.getElementById('skwa-price').textContent = formatMoney(price);
    var img = document.getElementById('skwa-img');
    if(image){ img.src = '<?= base_url(); ?>' + image; img.style.display='block'; } else { img.style.display='none'; }
    document.getElementById('skwa-qty').value = qty || 1;
    document.getElementById('skwa-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function skWaClose(){
    document.getElementById('skwa-modal').classList.remove('open');
    document.body.style.overflow = '';
  }
  function skWaSend(){
    if(!skWaProduct) return;
    var name  = document.getElementById('skwa-cname').value.trim();
    var phone = document.getElementById('skwa-cphone').value.trim();
    var qty   = parseInt(document.getElementById('skwa-qty').value) || 1;
    var note  = document.getElementById('skwa-note').value.trim();
    if(!name || !phone){ showToast('Please enter your name and phone number'); return; }
    var waNum = '<?= $skWaNum; ?>';
    if(!waNum){ showToast('WhatsApp ordering is not available'); return; }
    var msg = 'Hello <?= htmlspecialchars(addslashes($store->store_name ?? '')); ?>, I would like to order:';
    msg += '\n\n' + qty + ' x ' + skWaProduct.name + ' — ' + formatMoney(skWaProduct.price * qty);
    msg += '\n\nName: ' + name + '\nPhone: ' + phone;
    if(note) msg += '\nNote: ' + note;
    msg += '\n\nThank you.';
    window.open('https://wa.me/' + waNum + '?text=' + encodeURIComponent(msg), '_blank');
    skWaClose();
    showToast('Opening WhatsApp with your order...');
  }
</script>
