<?php
/**
 * Verdant — design system + header.
 * Loaded on every storefront page: fonts, the full mobile-first stylesheet,
 * header, drawer, WhatsApp order sheet and shared JS. Page views are markup.
 */
include APPPATH . 'views/themes/verdant/_theme.php';

$slug     = $settings->store_slug ?? '';
$logo     = $logo_url ?? null;
$waNum    = ($settings->allow_whatsapp ?? 1) ? preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '') : '';
$tk       = $VD['theme_key'];
$announce = trim($settings->announcement_bar ?? '');
$storeNm  = $store->store_name ?? 'Store';

$marquee = [];
if(!empty($settings->marquee_items)){
    foreach(preg_split('/\r\n|\r|\n/', $settings->marquee_items) as $mi){ $mi = trim($mi); if($mi !== '') $marquee[] = $mi; }
}
$announceBg = !empty($settings->announcement_bar_color) ? $settings->announcement_bar_color : $VD['forest'];
?>
<link rel="stylesheet" href="<?= $VD['fonts_url']; ?>">
<style>
/* ============================================================
   VERDANT — calm editorial skincare system
   Mobile-first; scales at 640 / 900 / 1200
   ============================================================ */
.theme-<?= $tk; ?> .mp-topbar, .theme-<?= $tk; ?> .mp-announcement, .theme-<?= $tk; ?> .mp-nav,
.theme-<?= $tk; ?> .mp-header, .theme-<?= $tk; ?> .mp-mobile-menu-btn { display:none !important; }
.theme-<?= $tk; ?> .mp-footer-space { height:0 !important; }

body.theme-<?= $tk; ?> { background:<?= $VD['bg']; ?>; color:<?= $VD['ink']; ?>; font-family:<?= $VD['font_body']; ?>; -webkit-font-smoothing:antialiased; text-rendering:optimizeLegibility; font-size:15px; line-height:1.6; }
.theme-<?= $tk; ?> ::selection { background:<?= $VD['sage']; ?>; color:<?= $VD['ink']; ?>; }

/* ---------- shared chrome restyle ---------- */
.theme-<?= $tk; ?> .mp-footer { background:<?= $VD['foot_bg']; ?>; color:<?= $VD['foot_text']; ?>; padding:64px 20px 28px; margin:24px 12px 12px; border-radius:<?= $VD['radius']; ?>; }
@media(min-width:900px){ .theme-<?= $tk; ?> .mp-footer { margin:40px 16px 16px; padding:80px 56px 32px; } }
.theme-<?= $tk; ?> .mp-footer-inner { max-width:1180px; margin:0 auto; display:grid; grid-template-columns:1fr; gap:36px; }
@media(min-width:900px){ .theme-<?= $tk; ?> .mp-footer-inner { grid-template-columns:1.6fr 1fr 1fr 1fr; gap:48px; } }
.theme-<?= $tk; ?> .mp-footer-brand { font-family:<?= $VD['font_display']; ?>; font-size:44px; line-height:1; color:<?= $VD['foot_head']; ?>; letter-spacing:-.02em; margin-bottom:14px; font-weight:400; }
.theme-<?= $tk; ?> .mp-footer-inner > div > img { background:transparent !important; padding:0 !important; }
.theme-<?= $tk; ?> .mp-footer-desc { font-size:13px; line-height:1.7; max-width:340px; color:<?= $VD['foot_text']; ?>; }
.theme-<?= $tk; ?> .mp-footer-heading { font-family:<?= $VD['font_display']; ?>; font-size:20px; color:<?= $VD['foot_head']; ?>; margin-bottom:14px; font-weight:400; }
.theme-<?= $tk; ?> .mp-footer-links { list-style:none; padding:0; margin:0; }
.theme-<?= $tk; ?> .mp-footer-links li { margin-bottom:10px; }
.theme-<?= $tk; ?> .mp-footer-links a { color:<?= $VD['foot_text']; ?>; font-size:13px; text-decoration:none; transition:color .2s; }
.theme-<?= $tk; ?> .mp-footer-links a:hover { color:<?= $VD['foot_head']; ?>; }
.theme-<?= $tk; ?> .mp-footer-contact-item { font-size:13px; color:<?= $VD['foot_text']; ?>; display:flex; gap:8px; align-items:flex-start; margin-bottom:10px; line-height:1.5; }
.theme-<?= $tk; ?> .mp-footer-contact-item a { color:inherit; }
.theme-<?= $tk; ?> .mp-footer-social { display:flex; gap:8px; margin-top:20px; }
.theme-<?= $tk; ?> .mp-footer-social a { width:40px; height:40px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,.06); color:<?= $VD['foot_head']; ?>; border:1px solid rgba(255,255,255,.14); transition:all .2s; }
.theme-<?= $tk; ?> .mp-footer-social a:hover { background:<?= $VD['sage']; ?>; color:<?= $VD['ink']; ?>; border-color:<?= $VD['sage']; ?>; }
.theme-<?= $tk; ?> .mp-footer-bottom { max-width:1180px; margin:48px auto 0; padding-top:22px; border-top:1px solid rgba(255,255,255,.12); font-size:12px; color:<?= $VD['foot_text']; ?>; text-align:left; }
.theme-<?= $tk; ?> .mp-footer-compact { display:block; }
.theme-<?= $tk; ?> .mp-footer-compact-row { font-size:13px; margin-top:12px; display:flex; flex-wrap:wrap; gap:10px 16px; align-items:center; }
.theme-<?= $tk; ?> .mp-footer-compact-row a { color:<?= $VD['foot_text']; ?>; }
.theme-<?= $tk; ?> .mp-footer-compact-label { font-family:<?= $VD['font_display']; ?>; font-size:17px; color:<?= $VD['foot_head']; ?>; }
.theme-<?= $tk; ?> .mp-mobile-nav { background:<?= $VD['surface']; ?>; border-top:1px solid <?= $VD['border']; ?>; }
.theme-<?= $tk; ?> .mp-mobile-nav-item { color:<?= $VD['muted']; ?>; }
.theme-<?= $tk; ?> .mp-mobile-nav-item.active { color:<?= $VD['accent']; ?>; }
.theme-<?= $tk; ?> .mp-sticky-cart { background:<?= $VD['surface']; ?>; border-top:1px solid <?= $VD['border']; ?>; box-shadow:0 -4px 20px rgba(0,0,0,.06); }
.theme-<?= $tk; ?> .mp-sticky-cart-items { color:<?= $VD['muted']; ?>; }
.theme-<?= $tk; ?> .mp-sticky-cart-total { color:<?= $VD['ink']; ?>; }
.theme-<?= $tk; ?> .mp-sticky-cart-btn { background:<?= $VD['btn']; ?>; color:<?= $VD['btn_text']; ?>; border-radius:<?= $VD['btn_radius']; ?>; }
.theme-<?= $tk; ?> .mp-modal { background:<?= $VD['surface']; ?>; color:<?= $VD['ink']; ?>; border:1px solid <?= $VD['border']; ?>; border-radius:<?= $VD['radius']; ?>; }
.theme-<?= $tk; ?> .mp-modal-title { color:<?= $VD['ink']; ?>; font-family:<?= $VD['font_display']; ?>; font-weight:400; font-size:24px; }
.theme-<?= $tk; ?> .mp-modal-add { background:<?= $VD['btn']; ?>; color:<?= $VD['btn_text']; ?>; border-radius:<?= $VD['btn_radius']; ?>; }
.theme-<?= $tk; ?> .mp-toast { background:<?= $VD['forest']; ?>; color:#fff; border-radius:12px; }
.theme-<?= $tk; ?> .mp-backtop { background:<?= $VD['surface']; ?>; color:<?= $VD['ink']; ?>; border-color:<?= $VD['border']; ?>; }
.theme-<?= $tk; ?> .mp-sticky-wa { box-shadow:0 10px 30px rgba(37,211,102,.35); }

/* ---------- layout & type ---------- */
.vd-wrap { width:100%; max-width:1240px; margin:0 auto; padding:0 16px; }
@media(min-width:900px){ .vd-wrap { padding:0 28px; } }
.vd-kicker { font-family:<?= $VD['font_body']; ?>; font-size:11px; font-weight:600; letter-spacing:.16em; text-transform:uppercase; color:<?= $VD['accent']; ?>; }
.vd-display { font-family:<?= $VD['font_display']; ?>; font-weight:400; letter-spacing:-.02em; line-height:1.02; color:<?= $VD['ink']; ?>; margin:0; }
.vd-display em, .vd-h2 em { font-style:italic; }
.vd-h2 { font-family:<?= $VD['font_display']; ?>; font-weight:400; font-size:clamp(30px,6.4vw,50px); line-height:1.04; letter-spacing:-.02em; color:<?= $VD['ink']; ?>; margin:0; }
.vd-h3 { font-family:<?= $VD['font_display']; ?>; font-weight:400; font-size:clamp(24px,4.6vw,34px); line-height:1.08; letter-spacing:-.015em; color:<?= $VD['ink']; ?>; margin:0; }
.vd-lead { font-size:15px; line-height:1.7; color:<?= $VD['muted']; ?>; }
@media(min-width:900px){ .vd-lead { font-size:16px; } }
.vd-sec { padding:56px 0; }
@media(min-width:900px){ .vd-sec { padding:84px 0; } }
.vd-sec-tight { padding-top:0; }
.vd-sec-head { display:flex; flex-direction:column; gap:10px; margin-bottom:28px; }
@media(min-width:640px){ .vd-sec-head { flex-direction:row; align-items:flex-end; justify-content:space-between; gap:24px; margin-bottom:36px; } .vd-sec-head > div:first-child { max-width:640px; } }
.vd-sec-head .vd-kicker { display:block; margin-bottom:12px; }
.vd-sec-head .vd-lead { margin-top:12px; max-width:560px; }
.vd-sec-head-center { text-align:center; align-items:center; justify-content:center; }
.vd-sec-head-center > div:first-child { margin:0 auto; }
.vd-sec-head-center .vd-lead { margin-left:auto; margin-right:auto; }
.vd-link { display:inline-flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:<?= $VD['ink']; ?>; text-decoration:none; white-space:nowrap; border-bottom:1px solid <?= $VD['ink']; ?>; padding-bottom:2px; transition:opacity .2s; }
.vd-link:hover { opacity:.7; }
.vd-link svg { width:14px; height:14px; }

/* ---------- buttons ---------- */
.vd-btn { display:inline-flex; align-items:center; justify-content:center; gap:10px; min-height:50px; padding:14px 26px; border-radius:<?= $VD['btn_radius']; ?>; font-family:<?= $VD['font_body']; ?>; font-size:14px; font-weight:600; border:1.5px solid transparent; cursor:pointer; text-decoration:none; transition:transform .15s, opacity .2s, background .2s, border-color .2s; white-space:nowrap; }
.vd-btn:active { transform:scale(.97); }
.vd-btn svg { width:16px; height:16px; }
.vd-btn-primary { background:<?= $VD['btn']; ?>; color:<?= $VD['btn_text']; ?>; }
.vd-btn-primary:hover { opacity:.9; }
.vd-btn-ghost { background:transparent; color:<?= $VD['ink']; ?>; border-color:<?= $VD['ink']; ?>33; }
.vd-btn-ghost:hover { border-color:<?= $VD['ink']; ?>; }
.vd-btn-sage { background:<?= $VD['sage']; ?>; color:<?= $VD['ink']; ?>; }
.vd-btn-sage:hover { opacity:.9; }
.vd-btn-light { background:#fff; color:<?= $VD['ink']; ?>; }
.vd-btn-outline-light { background:transparent; color:#fff; border-color:rgba(255,255,255,.35); }
.vd-btn-outline-light:hover { border-color:#fff; }
.vd-btn-wa { background:#25D366; color:#fff; }
.vd-btn-wa:hover { background:#1EBE5B; }
.vd-btn-wa svg { fill:currentColor; }
.vd-btn-row { display:flex; flex-wrap:wrap; gap:10px; }
.vd-btn-row .vd-btn { flex:1 1 100%; }
@media(min-width:640px){ .vd-btn-row .vd-btn { flex:0 1 auto; } }

/* ---------- announcement ---------- */
.vdh-announce { background:<?= $announceBg; ?>; color:<?= vd_contrast($announceBg); ?>; text-align:center; padding:10px 16px; font-size:12px; font-weight:500; letter-spacing:.02em; }
.vdh-marquee { padding:0; overflow:hidden; }
.vdh-marquee-track { display:inline-flex; align-items:center; white-space:nowrap; padding:10px 0; animation:vdmq 28s linear infinite; will-change:transform; }
.vdh-marquee:hover .vdh-marquee-track { animation-play-state:paused; }
.vdh-marquee-track span { display:inline-flex; align-items:center; padding:0 20px; }
.vdh-marquee-track span::after { content:''; width:4px; height:4px; border-radius:50%; background:currentColor; opacity:.5; margin-left:40px; }
@keyframes vdmq { to { transform:translateX(-50%); } }

/* ---------- header ---------- */
.vdh { position:sticky; top:0; z-index:200; background:<?= $VD['bg']; ?>E6; backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); border-bottom:1px solid <?= $VD['border']; ?>; }
.vdh-bar { display:flex; align-items:center; justify-content:space-between; gap:8px; min-height:60px; }
.vdh-logo { display:flex; align-items:center; gap:10px; text-decoration:none; min-width:0; }
.vdh-logo img { max-height:34px; max-width:150px; object-fit:contain; }
.vdh-logo-name { font-family:<?= $VD['font_display']; ?>; font-size:26px; color:<?= $VD['ink']; ?>; letter-spacing:-.02em; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; line-height:1; }
.vdh-nav { display:none; }
.vdh-actions { display:flex; align-items:center; gap:2px; }
.vdh-ic { width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center; color:<?= $VD['ink']; ?>; text-decoration:none; position:relative; background:none; border:none; cursor:pointer; border-radius:50%; transition:background .2s; }
.vdh-ic:hover { background:<?= $VD['soft']; ?>; }
.vdh-ic svg { width:20px; height:20px; stroke:currentColor; stroke-width:1.6; fill:none; stroke-linecap:round; stroke-linejoin:round; }
.vdh-ic svg.fill { fill:currentColor; stroke:none; }
.vdh-count { position:absolute; top:6px; right:4px; background:<?= $VD['forest']; ?>; color:#fff; font-size:10px; font-weight:700; min-width:17px; height:17px; border-radius:50%; display:flex; align-items:center; justify-content:center; padding:0 4px; }
.vdh-cta { display:none; }
@media(min-width:900px){
  .vdh-menu { display:none; }
  .vdh-bar { min-height:76px; }
  .vdh-logo img { max-height:42px; max-width:200px; }
  .vdh-logo-name { font-size:30px; }
  .vdh-nav { display:flex; align-items:center; gap:30px; flex:1; justify-content:center; }
  .vdh-nav-link { font-size:14px; font-weight:500; color:<?= $VD['ink']; ?>; text-decoration:none; opacity:.78; transition:opacity .2s; }
  .vdh-nav-link:hover { opacity:1; }
  .vdh-cta { display:inline-flex; min-height:42px; padding:0 20px; font-size:13px; margin-left:8px; }
}
<?php if(!empty($VD['header_ink'])): ?>
.theme-<?= $tk; ?> .vdh-logo-name, .theme-<?= $tk; ?> .vdh-ic, .theme-<?= $tk; ?> .vdh-nav-link { color:<?= $VD['header_ink']; ?> !important; }
<?php endif; ?>

/* ---------- drawer ---------- */
.vdh-overlay { display:none; position:fixed; inset:0; background:rgba(22,41,31,.5); z-index:900; }
.vdh-overlay.open { display:block; }
.vdh-drawer { position:fixed; top:0; left:0; bottom:0; width:320px; max-width:88vw; background:<?= $VD['bg']; ?>; z-index:901; padding:22px 22px 40px; overflow-y:auto; transform:translateX(-100%); transition:transform .3s cubic-bezier(.2,.8,.2,1); }
.vdh-drawer.open { transform:translateX(0); }
.vdh-drawer-title { font-family:<?= $VD['font_display']; ?>; font-size:28px; color:<?= $VD['ink']; ?>; margin-bottom:18px; letter-spacing:-.02em; }
.vdh-drawer-link { display:block; padding:14px 0; font-size:15px; font-weight:500; color:<?= $VD['ink']; ?>; text-decoration:none; border-bottom:1px solid <?= $VD['border']; ?>; }
.vdh-drawer-sec { font-size:11px; letter-spacing:.16em; text-transform:uppercase; color:<?= $VD['accent']; ?>; font-weight:600; margin:22px 0 4px; }
.vdh-drawer .vd-btn { width:100%; margin-top:22px; }

/* ---------- hero ---------- */
.vd-hero { padding:36px 0 20px; }
@media(min-width:900px){ .vd-hero { padding:56px 0 24px; } }
.vd-hero-grid { display:grid; grid-template-columns:1fr; gap:28px; align-items:center; }
@media(min-width:900px){ .vd-hero-grid { grid-template-columns:1.05fr .95fr; gap:56px; } .vd-hero-grid.no-media { grid-template-columns:1fr; max-width:820px; margin:0 auto; text-align:center; } .vd-hero-grid.no-media .vd-lead, .vd-hero-grid.no-media .vd-hero-sub { margin-left:auto; margin-right:auto; } .vd-hero-grid.no-media .vd-btn-row, .vd-hero-grid.no-media .vd-hero-proof { justify-content:center; } }
.vd-hero-title { font-size:clamp(34px,8.6vw,84px); overflow-wrap:break-word; }
.vd-hero-title em { color:<?= $VD['accent']; ?>; }
.vd-hero-sub { font-size:16px; line-height:1.6; color:<?= $VD['ink']; ?>; opacity:.82; max-width:560px; margin:20px 0 0; display:-webkit-box; -webkit-line-clamp:4; line-clamp:4; -webkit-box-orient:vertical; overflow:hidden; }
@media(min-width:900px){ .vd-hero-sub { font-size:18px; } }
.vd-hero-copy .vd-lead { max-width:520px; margin:22px 0 0; }
.vd-hero-copy .vd-btn-row { margin-top:34px; }
.vd-hero-proof { display:flex; flex-wrap:wrap; gap:8px 22px; margin-top:34px; padding-top:22px; border-top:1px solid <?= $VD['border']; ?>; }
.vd-hero-proof span { display:inline-flex; align-items:center; gap:8px; font-size:12.5px; font-weight:500; color:<?= $VD['muted']; ?>; }
.vd-hero-proof svg { width:14px; height:14px; color:<?= $VD['accent']; ?>; flex-shrink:0; }
@media(max-width:899.98px){
  .vd-hero-copy { text-align:center; }
  .vd-hero-sub { margin-top:14px; }
  .vd-hero-copy .vd-hero-sub, .vd-hero-copy .vd-lead { margin-left:auto; margin-right:auto; }
  .vd-hero-copy .vd-lead { margin:14px auto 0; }
  .vd-hero-copy .vd-btn-row { justify-content:center; margin-top:28px; }
  .vd-hero-copy .vd-hero-proof { justify-content:center; }
}
.vd-hero-media { position:relative; border-radius:<?= $VD['radius']; ?>; overflow:hidden; background:<?= $VD['sage_soft']; ?>; aspect-ratio:4/5; max-height:620px; }
@media(min-width:900px){ .vd-hero-media { aspect-ratio:5/6; } }
.vd-hero-slide { position:absolute; inset:0; opacity:0; transition:opacity .7s ease; }
.vd-hero-slide.active { opacity:1; }
.vd-hero-slide img { width:100%; height:100%; object-fit:cover; display:block; }
.vd-hero-slide-cap { position:absolute; left:16px; right:16px; bottom:16px; background:rgba(255,255,255,.92); backdrop-filter:blur(8px); border-radius:<?= $VD['radius_sm']; ?>; padding:14px 16px; display:flex; align-items:center; justify-content:space-between; gap:12px; }
.vd-hero-slide-cap b { display:block; font-family:<?= $VD['font_display']; ?>; font-weight:400; font-size:20px; line-height:1.1; color:<?= $VD['ink']; ?>; }
.vd-hero-slide-cap small { font-size:12px; color:<?= $VD['muted']; ?>; }
.vd-hero-slide-cap a { flex-shrink:0; width:40px; height:40px; border-radius:50%; background:<?= $VD['forest']; ?>; color:#fff; display:inline-flex; align-items:center; justify-content:center; }
.vd-hero-slide-cap a svg { width:16px; height:16px; }
.vd-hero-dots { position:absolute; top:16px; right:16px; display:flex; gap:6px; z-index:3; }
.vd-hero-dot { width:8px; height:8px; border-radius:50%; border:none; padding:0; background:#fff; opacity:.45; cursor:pointer; transition:all .2s; }
.vd-hero-dot.active { opacity:1; width:22px; border-radius:4px; }

/* ---------- concern / category cards ---------- */
.vd-concern-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
@media(min-width:640px){ .vd-concern-grid { grid-template-columns:repeat(3,1fr); gap:18px; } }
@media(min-width:900px){ .vd-concern-grid { gap:24px; } }
.vd-concern { position:relative; display:flex; flex-direction:column; border-radius:<?= $VD['radius']; ?>; overflow:hidden; background:<?= $VD['surface']; ?>; text-decoration:none; border:1px solid <?= $VD['border']; ?>; transition:transform .3s, box-shadow .3s; }
.vd-concern:hover { transform:translateY(-4px); box-shadow:0 20px 50px rgba(27,42,34,.10); }
.vd-concern-media { flex:0 0 auto; width:100%; aspect-ratio:1/1; background:<?= $VD['soft']; ?>; overflow:hidden; }
.vd-concern-media img { width:100%; height:100%; object-fit:cover; transition:transform .6s ease; }
.vd-concern:hover .vd-concern-media img { transform:scale(1.05); }
.vd-concern-ph { width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-family:<?= $VD['font_display']; ?>; font-size:44px; color:<?= $VD['accent']; ?>; background:linear-gradient(160deg, <?= $VD['sage_soft']; ?>, <?= $VD['soft']; ?>); }
.vd-concern-body { padding:16px 16px 18px; display:flex; align-items:flex-end; justify-content:space-between; gap:12px; min-height:82px; }
.vd-concern-name { font-family:<?= $VD['font_display']; ?>; font-size:22px; line-height:1.1; color:<?= $VD['ink']; ?>; letter-spacing:-.01em; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.vd-concern-count { font-size:12px; color:<?= $VD['muted']; ?>; margin-top:4px; }
.vd-concern-arrow { position:absolute; top:12px; right:12px; z-index:2; width:36px; height:36px; border-radius:50%; border:none; background:#fff; box-shadow:0 4px 14px rgba(27,42,34,.16); display:inline-flex; align-items:center; justify-content:center; color:<?= $VD['ink']; ?>; transition:all .2s; }
.vd-concern:hover .vd-concern-arrow { background:<?= $VD['forest']; ?>; color:#fff; }
.vd-concern-arrow svg { width:15px; height:15px; }
/* texture / wide variant */
.vd-texture-grid { display:grid; grid-template-columns:1fr; gap:14px; }
@media(min-width:640px){ .vd-texture-grid { grid-template-columns:repeat(2,1fr); gap:24px; } }
.vd-texture { position:relative; display:block; border-radius:<?= $VD['radius']; ?>; overflow:hidden; aspect-ratio:16/10; background:<?= $VD['soft']; ?>; text-decoration:none; }
.vd-texture img { width:100%; height:100%; object-fit:cover; transition:transform .6s ease; }
.vd-texture:hover img { transform:scale(1.04); }
.vd-texture::after { content:''; position:absolute; inset:0; background:linear-gradient(180deg, transparent 40%, rgba(22,41,31,.7) 100%); }
.vd-texture-body { position:absolute; left:20px; right:20px; bottom:18px; z-index:2; display:flex; align-items:flex-end; justify-content:space-between; gap:12px; color:#fff; }
.vd-texture-name { font-family:<?= $VD['font_display']; ?>; font-size:28px; line-height:1; letter-spacing:-.01em; }
.vd-texture-count { font-size:12px; opacity:.8; margin-top:6px; }
.vd-texture .vd-concern-arrow { border-color:rgba(255,255,255,.4); color:#fff; }
.vd-texture .vd-concern-ph { position:absolute; inset:0; }

/* ---------- dark band (values / story) ---------- */
.vd-band { background:<?= $VD['forest']; ?>; color:#fff; border-radius:<?= $VD['radius']; ?>; padding:48px 22px; margin:0 12px; }
@media(min-width:900px){ .vd-band { padding:72px 64px; margin:0 16px; } }
.vd-band .vd-kicker { color:<?= $VD['sage']; ?>; }
.vd-band .vd-h2, .vd-band .vd-h3 { color:#fff; }
.vd-band .vd-lead { color:rgba(255,255,255,.72); }
.vd-values { display:grid; grid-template-columns:1fr; gap:28px; }
@media(min-width:900px){ .vd-values { grid-template-columns:1fr 1.4fr; gap:64px; align-items:start; } }
.vd-values-list { display:grid; grid-template-columns:1fr; gap:0; border-top:1px solid rgba(255,255,255,.14); }
@media(min-width:640px){ .vd-values-list { grid-template-columns:1fr 1fr; gap:0 40px; } }
.vd-value { padding:20px 0; border-bottom:1px solid rgba(255,255,255,.14); display:flex; gap:16px; align-items:flex-start; }
.vd-value-ic { flex-shrink:0; width:40px; height:40px; border-radius:50%; background:rgba(255,255,255,.08); display:inline-flex; align-items:center; justify-content:center; color:<?= $VD['sage']; ?>; font-size:16px; }
.vd-value-ic i { font-size:15px; }
.vd-value-t { font-size:15px; font-weight:600; color:#fff; margin-bottom:4px; }
.vd-value-d { font-size:13px; line-height:1.65; color:rgba(255,255,255,.66); }

/* story split */
.vd-story { display:grid; grid-template-columns:1fr; gap:28px; align-items:center; }
@media(min-width:900px){ .vd-story { grid-template-columns:1fr 1fr; gap:64px; } .vd-story.invert .vd-story-media { order:2; } }
.vd-story-media { border-radius:<?= $VD['radius_sm']; ?>; overflow:hidden; aspect-ratio:4/4.6; background:rgba(255,255,255,.06); }
.vd-story-media img { width:100%; height:100%; object-fit:cover; display:block; }
.vd-story-copy .vd-kicker { display:block; margin-bottom:16px; }
.vd-story-copy .vd-lead { margin:20px 0 28px; max-width:500px; }
.vd-story-facts { display:flex; flex-wrap:wrap; gap:10px; margin-top:24px; }
.vd-story-facts span { padding:9px 14px; border-radius:999px; border:1px solid rgba(255,255,255,.22); font-size:12.5px; color:rgba(255,255,255,.85); }

/* ---------- product cards ---------- */
.vd-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; }
@media(min-width:640px){ .vd-grid { grid-template-columns:repeat(3,1fr); gap:20px; } }
@media(min-width:900px){ .vd-grid { grid-template-columns:repeat(4,1fr); gap:24px; } }
.vd-card { position:relative; display:flex; flex-direction:column; background:transparent; }
.vd-card-media { position:relative; display:flex; align-items:center; justify-content:center; aspect-ratio:1/1; border-radius:<?= $VD['radius']; ?>; overflow:hidden; background:<?= $VD['soft']; ?>; }
.vd-card-media img { width:100%; height:100%; object-fit:cover; transition:transform .55s ease; }
.vd-card:hover .vd-card-media img { transform:scale(1.05); }
.vd-card-ph { font-family:<?= $VD['font_display']; ?>; font-size:48px; color:<?= $VD['accent']; ?>; }
.vd-tag { position:absolute; top:12px; left:12px; z-index:2; font-size:11px; font-weight:600; letter-spacing:.04em; padding:6px 11px; border-radius:999px; background:#fff; color:<?= $VD['ink']; ?>; }
.vd-tag-sale { background:<?= $VD['clay']; ?>; color:#fff; }
.vd-tag-new { background:<?= $VD['forest']; ?>; color:#fff; }
.vd-tag-out { left:auto; right:12px; background:#fff; color:<?= $VD['muted']; ?>; }
.vd-card-body { padding:14px 4px 0; display:flex; flex-direction:column; flex:1; }
.vd-card-range { font-size:11px; letter-spacing:.12em; text-transform:uppercase; color:<?= $VD['muted']; ?>; margin-bottom:6px; font-weight:500; }
.vd-card-name { font-family:<?= $VD['font_display']; ?>; font-size:20px; line-height:1.15; color:<?= $VD['ink']; ?>; text-decoration:none; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; letter-spacing:-.01em; margin-bottom:10px; }
.vd-card-name:hover { color:<?= $VD['accent']; ?>; }
.vd-card-foot { margin-top:auto; display:flex; flex-direction:column; gap:12px; }
.vd-card-price { font-size:15px; font-weight:600; color:<?= $VD['ink']; ?>; }
.vd-card-price s { margin-left:8px; font-size:12.5px; color:<?= $VD['muted']; ?>; font-weight:400; }
.vd-card-actions { display:flex; gap:8px; }
.vd-add { flex:1; min-height:44px; border-radius:<?= $VD['btn_radius']; ?>; background:<?= $VD['surface']; ?>; color:<?= $VD['ink']; ?>; border:1.5px solid <?= $VD['border']; ?>; font-family:<?= $VD['font_body']; ?>; font-size:13px; font-weight:600; cursor:pointer; transition:all .2s; }
.vd-add:hover { background:<?= $VD['btn']; ?>; color:<?= $VD['btn_text']; ?>; border-color:<?= $VD['btn']; ?>; }
.vd-add:disabled { opacity:.45; cursor:not-allowed; }
.vd-add:disabled:hover { background:<?= $VD['surface']; ?>; color:<?= $VD['ink']; ?>; border-color:<?= $VD['border']; ?>; }
.vd-wa-ic { width:44px; min-height:44px; flex-shrink:0; border-radius:<?= $VD['btn_radius']; ?>; background:#25D366; color:#fff; border:none; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; transition:background .2s; }
.vd-wa-ic:hover { background:#1EBE5B; }
.vd-wa-ic svg { width:17px; height:17px; }

/* rail */
.vd-rail { position:relative; }
.vd-rail-track { display:flex; gap:14px; overflow-x:auto; scroll-snap-type:x mandatory; scrollbar-width:none; -webkit-overflow-scrolling:touch; padding-bottom:6px; }
.vd-rail-track::-webkit-scrollbar { display:none; }
.vd-rail-track .vd-card { flex:0 0 66%; scroll-snap-align:start; }
@media(min-width:640px){ .vd-rail-track .vd-card { flex-basis:32%; } .vd-rail-track { gap:20px; } }
@media(min-width:900px){ .vd-rail-track .vd-card { flex-basis:23.2%; } .vd-rail-track { gap:24px; } }
.vd-rail-nav { display:flex; gap:8px; }
.vd-rail-btn { width:44px; height:44px; border-radius:50%; border:1px solid <?= $VD['border']; ?>; background:<?= $VD['surface']; ?>; color:<?= $VD['ink']; ?>; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; transition:all .2s; }
.vd-rail-btn:hover { background:<?= $VD['forest']; ?>; color:#fff; border-color:<?= $VD['forest']; ?>; }
.vd-rail-btn svg { width:16px; height:16px; stroke:currentColor; stroke-width:1.8; fill:none; }
.vd-sec-side { display:flex; align-items:center; gap:16px; }

/* ---------- routine set / promo ---------- */
.vd-set { background:<?= $VD['sage_soft']; ?>; border-radius:<?= $VD['radius']; ?>; padding:22px; display:grid; grid-template-columns:1fr; gap:22px; align-items:center; }
@media(min-width:900px){ .vd-set { grid-template-columns:1fr 1fr; gap:56px; padding:44px; } .vd-set.invert .vd-set-media { order:2; } }
.vd-set-media { border-radius:<?= $VD['radius_sm']; ?>; overflow:hidden; aspect-ratio:5/4; background:<?= $VD['surface']; ?>; display:flex; align-items:center; justify-content:center; }
.vd-set-media img { width:100%; height:100%; object-fit:cover; display:block; }
.vd-set-copy .vd-kicker { display:block; margin-bottom:14px; }
.vd-set-copy .vd-lead { margin:16px 0 26px; }
.vd-set-steps { list-style:none; padding:0; margin:0 0 26px; border-top:1px solid <?= $VD['ink']; ?>1F; }
.vd-set-steps li { display:flex; gap:14px; padding:12px 0; border-bottom:1px solid <?= $VD['ink']; ?>1F; font-size:14px; color:<?= $VD['ink']; ?>; align-items:center; }
.vd-set-steps li b { font-family:<?= $VD['font_display']; ?>; font-weight:400; font-size:18px; color:<?= $VD['accent']; ?>; min-width:28px; }

/* ---------- journal ---------- */
.vd-journal { display:grid; grid-template-columns:1fr; gap:16px; }
@media(min-width:900px){ .vd-journal { grid-template-columns:1.25fr 1fr; grid-auto-rows:minmax(0,1fr); gap:24px; } .vd-journal-lead { grid-row:span 3; } }
.vd-post { display:flex; gap:16px; text-decoration:none; background:<?= $VD['surface']; ?>; border:1px solid <?= $VD['border']; ?>; border-radius:<?= $VD['radius_sm']; ?>; overflow:hidden; padding:12px; transition:transform .25s, box-shadow .25s; align-items:center; }
.vd-post:hover { transform:translateY(-2px); box-shadow:0 14px 36px rgba(27,42,34,.08); }
.vd-post-img { flex:0 0 96px; width:96px; aspect-ratio:1; border-radius:10px; overflow:hidden; background:<?= $VD['soft']; ?>; }
.vd-post-img img { width:100%; height:100%; object-fit:cover; }
.vd-post-k { font-size:11px; letter-spacing:.14em; text-transform:uppercase; color:<?= $VD['accent']; ?>; font-weight:600; margin-bottom:6px; }
.vd-post-t { font-family:<?= $VD['font_display']; ?>; font-size:20px; line-height:1.15; color:<?= $VD['ink']; ?>; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.vd-journal-lead { flex-direction:column; padding:0; align-items:stretch; }
.vd-journal-lead .vd-post-img { flex:1 1 auto; width:100%; aspect-ratio:4/3; border-radius:0; }
@media(min-width:900px){ .vd-journal-lead .vd-post-img { aspect-ratio:auto; min-height:320px; } }
.vd-journal-lead .vd-post-body { padding:20px 22px 24px; }
.vd-journal-lead .vd-post-t { font-size:30px; -webkit-line-clamp:3; line-clamp:3; }

/* ---------- testimonials ---------- */
.vd-quotes { display:grid; grid-template-columns:1fr; gap:14px; }
@media(min-width:768px){ .vd-quotes { grid-template-columns:repeat(3,1fr); gap:20px; } }
.vd-quote { background:<?= $VD['surface']; ?>; border:1px solid <?= $VD['border']; ?>; border-radius:<?= $VD['radius']; ?>; padding:28px 26px; display:flex; flex-direction:column; }
.vd-quote-stars { color:<?= $VD['clay']; ?>; letter-spacing:2px; font-size:13px; margin-bottom:14px; }
.vd-quote-text { font-family:<?= $VD['font_display']; ?>; font-size:22px; line-height:1.3; color:<?= $VD['ink']; ?>; letter-spacing:-.01em; flex:1; }
.vd-quote-by { margin-top:20px; display:flex; align-items:center; gap:12px; font-size:13px; font-weight:600; color:<?= $VD['muted']; ?>; }
.vd-quote-by img, .vd-quote-by span.ph { width:36px; height:36px; border-radius:50%; object-fit:cover; background:<?= $VD['sage_soft']; ?>; display:inline-flex; align-items:center; justify-content:center; color:<?= $VD['accent']; ?>; font-family:<?= $VD['font_display']; ?>; font-size:17px; }

/* ---------- brands / faq / contact / hours / newsletter / cta ---------- */
.vd-brands { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; }
.vd-brand { display:inline-flex; align-items:center; gap:10px; padding:10px 20px; border:1px solid <?= $VD['border']; ?>; border-radius:999px; background:<?= $VD['surface']; ?>; font-family:<?= $VD['font_display']; ?>; font-size:18px; color:<?= $VD['ink']; ?>; text-decoration:none; }
.vd-brand img { height:22px; width:auto; object-fit:contain; }
.vd-faq-list { max-width:820px; margin:0 auto; }
.vd-faq { background:<?= $VD['surface']; ?>; border:1px solid <?= $VD['border']; ?>; border-radius:<?= $VD['radius_sm']; ?>; margin-bottom:10px; overflow:hidden; }
.vd-faq-q { width:100%; text-align:left; padding:18px 20px; font-family:<?= $VD['font_body']; ?>; font-size:15px; font-weight:600; color:<?= $VD['ink']; ?>; background:none; border:none; cursor:pointer; display:flex; justify-content:space-between; align-items:center; gap:16px; min-height:56px; }
.vd-faq-q::after { content:'+'; font-family:<?= $VD['font_display']; ?>; font-size:26px; line-height:1; color:<?= $VD['ink']; ?>; transition:transform .25s; }
.vd-faq.open .vd-faq-q::after { transform:rotate(45deg); }
.vd-faq-a { max-height:0; overflow:hidden; transition:max-height .3s ease; }
.vd-faq.open .vd-faq-a { max-height:600px; }
.vd-faq-a p { padding:0 20px 20px; font-size:14.5px; color:<?= $VD['muted']; ?>; line-height:1.75; margin:0; }
.vd-contact-grid { display:grid; grid-template-columns:1fr; gap:12px; }
@media(min-width:640px){ .vd-contact-grid { grid-template-columns:repeat(2,1fr); gap:16px; } }
@media(min-width:900px){ .vd-contact-grid { grid-template-columns:repeat(4,1fr); } }
.vd-contact { padding:26px 22px; border:1px solid <?= $VD['border']; ?>; border-radius:<?= $VD['radius']; ?>; background:<?= $VD['surface']; ?>; }
.vd-contact-ic { width:42px; height:42px; border-radius:50%; background:<?= $VD['sage_soft']; ?>; display:inline-flex; align-items:center; justify-content:center; color:<?= $VD['accent']; ?>; margin-bottom:16px; }
.vd-contact-ic svg { width:18px; height:18px; }
.vd-contact-l { font-size:11px; letter-spacing:.14em; text-transform:uppercase; color:<?= $VD['muted']; ?>; font-weight:600; margin-bottom:6px; }
.vd-contact-v { font-size:15px; font-weight:500; color:<?= $VD['ink']; ?>; word-break:break-word; line-height:1.5; }
.vd-contact-v a { color:inherit; text-decoration:none; }
.vd-hours { max-width:560px; margin:0 auto; background:<?= $VD['surface']; ?>; border:1px solid <?= $VD['border']; ?>; border-radius:<?= $VD['radius']; ?>; padding:8px 24px; }
.vd-hours-row { padding:14px 0; border-bottom:1px solid <?= $VD['border']; ?>; font-size:14.5px; color:<?= $VD['ink']; ?>; display:flex; justify-content:space-between; gap:16px; }
.vd-hours-row:last-child { border-bottom:none; }
.vd-hours-row span:last-child { color:<?= $VD['muted']; ?>; }
.vd-news { background:<?= $VD['forest']; ?>; border-radius:<?= $VD['radius']; ?>; padding:18px; display:grid; grid-template-columns:1fr; gap:22px; align-items:center; color:#fff; margin:0 12px; }
@media(min-width:900px){ .vd-news { grid-template-columns:.9fr 1.1fr; gap:48px; padding:22px 60px 22px 22px; margin:0 16px; } }
.vd-news.no-media { grid-template-columns:1fr; text-align:center; padding:56px 22px; }
@media(min-width:900px){ .vd-news.no-media { padding:80px 60px; } .vd-news.no-media .vd-news-copy { max-width:640px; margin:0 auto; } .vd-news.no-media .vd-news-form { margin:0 auto; } }
.vd-news-media { border-radius:<?= $VD['radius_sm']; ?>; overflow:hidden; aspect-ratio:1/1; background:rgba(255,255,255,.06); }
.vd-news-media img { width:100%; height:100%; object-fit:cover; display:block; }
.vd-news-copy .vd-kicker { display:block; color:<?= $VD['sage']; ?>; margin-bottom:16px; }
.vd-news-copy .vd-h2 { color:#fff; }
.vd-news-copy .vd-lead { color:rgba(255,255,255,.72); margin:18px 0 24px; max-width:480px; }
.vd-news-form { display:flex; flex-direction:column; gap:10px; max-width:520px; }
.vd-news-form input { min-height:52px; padding:14px 20px; border:none; border-radius:<?= $VD['btn_radius']; ?>; font-size:15px; outline:none; font-family:<?= $VD['font_body']; ?>; background:#fff; color:<?= $VD['ink']; ?>; box-sizing:border-box; flex:1; }
.vd-news-form input:focus { box-shadow:0 0 0 3px <?= $VD['sage']; ?>66; }
.vd-news-form button { min-height:52px; padding:0 24px; border:none; border-radius:<?= $VD['btn_radius']; ?>; background:<?= $VD['sage']; ?>; color:<?= $VD['ink']; ?>; font-family:<?= $VD['font_body']; ?>; font-weight:600; font-size:14px; cursor:pointer; white-space:nowrap; }
.vd-news-form button:hover { opacity:.92; }
@media(min-width:640px){ .vd-news-form { flex-direction:row; } }
.vd-news-note { font-size:11.5px; color:rgba(255,255,255,.5); margin-top:12px; }
.vd-cta { background:<?= $VD['sage_soft']; ?>; border-radius:<?= $VD['radius']; ?>; padding:44px 22px; text-align:center; }
@media(min-width:900px){ .vd-cta { padding:64px 40px; } }
.vd-cta .vd-lead { max-width:520px; margin:14px auto 26px; }
.vd-cta .vd-btn-row { justify-content:center; }

/* ---------- ritual / how-to strip (services) ---------- */
.vd-service-grid { display:grid; grid-template-columns:1fr; gap:14px; }
@media(min-width:640px){ .vd-service-grid { grid-template-columns:repeat(2,1fr); gap:20px; } }
@media(min-width:900px){ .vd-service-grid { grid-template-columns:repeat(4,1fr); } }

/* ---------- catalogue ---------- */
.vd-crumbs { padding:22px 0 0; font-size:12px; letter-spacing:.08em; text-transform:uppercase; color:<?= $VD['muted']; ?>; display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
.vd-crumbs a { color:<?= $VD['muted']; ?>; text-decoration:none; }
.vd-crumbs a:hover { color:<?= $VD['ink']; ?>; }
.vd-crumbs .sep { opacity:.4; }
.vd-page { padding:22px 0 80px; }
.vd-page-head { display:flex; flex-direction:column; gap:8px; margin-bottom:26px; }
.vd-count { font-size:13px; color:<?= $VD['muted']; ?>; }
.vd-filters { margin:0 0 30px; display:flex; flex-direction:column; gap:12px; }
.vd-search { position:relative; }
.vd-search input { width:100%; min-height:52px; padding:13px 16px 13px 46px; border:1px solid <?= $VD['border']; ?>; border-radius:<?= $VD['btn_radius']; ?>; font-size:15px; background:<?= $VD['surface']; ?>; color:<?= $VD['ink']; ?>; outline:none; font-family:<?= $VD['font_body']; ?>; box-sizing:border-box; }
.vd-search input:focus { border-color:<?= $VD['accent']; ?>; }
.vd-search input::placeholder { color:<?= $VD['muted']; ?>; }
.vd-search svg { position:absolute; left:17px; top:50%; transform:translateY(-50%); color:<?= $VD['muted']; ?>; width:17px; height:17px; }
.vd-chips { display:flex; gap:8px; overflow-x:auto; scrollbar-width:none; padding-bottom:4px; -webkit-overflow-scrolling:touch; }
.vd-chips::-webkit-scrollbar { display:none; }
.vd-chip { flex-shrink:0; min-height:44px; display:inline-flex; align-items:center; padding:10px 18px; border-radius:999px; border:1px solid <?= $VD['border']; ?>; background:<?= $VD['surface']; ?>; font-size:13.5px; font-weight:500; color:<?= $VD['ink']; ?>; text-decoration:none; transition:all .2s; white-space:nowrap; }
.vd-chip:hover { border-color:<?= $VD['ink']; ?>; }
.vd-chip.active { background:<?= $VD['forest']; ?>; color:#fff; border-color:<?= $VD['forest']; ?>; }
@media(min-width:640px){ .vd-filters { flex-direction:row; align-items:center; } .vd-search { flex:0 0 360px; } .vd-chips { flex:1; } }
.vd-pagination { display:flex; justify-content:center; gap:8px; margin-top:44px; flex-wrap:wrap; }
.vd-page-btn { min-width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center; padding:0 14px; border-radius:999px; border:1px solid <?= $VD['border']; ?>; background:<?= $VD['surface']; ?>; font-size:14px; font-weight:500; color:<?= $VD['ink']; ?>; text-decoration:none; }
.vd-page-btn.active { background:<?= $VD['forest']; ?>; color:#fff; border-color:<?= $VD['forest']; ?>; }
.vd-empty { text-align:center; padding:80px 20px; max-width:480px; margin:0 auto; }
.vd-empty .vd-h3 { margin-bottom:10px; }
.vd-empty .vd-lead { margin-bottom:24px; }

/* ---------- product detail ---------- */
.vd-pd { padding:24px 0 80px; }
.vd-pd-layout { display:grid; grid-template-columns:1fr; gap:28px; align-items:start; }
@media(min-width:900px){ .vd-pd-layout { grid-template-columns:1.05fr 1fr; gap:64px; } }
.vd-pd-media { position:relative; aspect-ratio:1/1; border-radius:<?= $VD['radius']; ?>; overflow:hidden; background:<?= $VD['soft']; ?>; display:flex; align-items:center; justify-content:center; }
@media(min-width:900px){ .vd-pd-media { position:sticky; top:96px; aspect-ratio:4/4.4; } }
.vd-pd-media img { width:100%; height:100%; object-fit:cover; }
.vd-pd-media .vd-card-ph { font-size:90px; }
.vd-pd-media .vd-tag { top:16px; left:16px; }
.vd-pd-copy .vd-kicker { display:block; margin-bottom:14px; }
.vd-pd-name { font-size:clamp(34px,7.4vw,56px); margin-bottom:16px; }
.vd-pd-price { font-size:24px; font-weight:600; color:<?= $VD['ink']; ?>; display:flex; align-items:baseline; gap:12px; flex-wrap:wrap; }
.vd-pd-price s { font-size:16px; color:<?= $VD['muted']; ?>; font-weight:400; }
.vd-pd-stock { display:inline-flex; align-items:center; gap:8px; font-size:12.5px; font-weight:600; margin:12px 0 22px; padding:6px 12px; border-radius:999px; background:<?= $VD['surface']; ?>; border:1px solid <?= $VD['border']; ?>; }
.vd-pd-stock::before { content:''; width:7px; height:7px; border-radius:50%; background:#16A34A; }
.vd-pd-stock.out::before { background:#DC2626; }
.vd-pd-desc { font-size:15.5px; line-height:1.8; color:<?= $VD['muted']; ?>; margin-bottom:26px; }
.vd-pd-buy { background:<?= $VD['surface']; ?>; border:1px solid <?= $VD['border']; ?>; border-radius:<?= $VD['radius']; ?>; padding:18px; display:flex; flex-direction:column; gap:14px; }
@media(min-width:640px){ .vd-pd-buy { padding:22px; } }
.vd-pd-buy-row { display:flex; gap:10px; flex-wrap:wrap; }
.vd-qty { display:inline-flex; align-items:center; border:1px solid <?= $VD['border']; ?>; border-radius:<?= $VD['btn_radius']; ?>; padding:2px; flex:0 0 auto; }
.vd-qty button { width:44px; height:44px; border-radius:<?= $VD['btn_radius']; ?>; border:none; background:transparent; font-size:19px; cursor:pointer; color:<?= $VD['ink']; ?>; }
.vd-qty button:hover { background:<?= $VD['soft']; ?>; }
.vd-qty span { min-width:36px; text-align:center; font-weight:600; font-size:15px; }
.vd-pd-buy-row .vd-btn { flex:1 1 140px; }
.vd-pd-trust { display:flex; flex-wrap:wrap; gap:8px 20px; margin-top:20px; }
.vd-pd-trust span { display:inline-flex; align-items:center; gap:8px; font-size:12.5px; color:<?= $VD['muted']; ?>; font-weight:500; }
.vd-pd-trust svg { width:14px; height:14px; color:<?= $VD['accent']; ?>; }
.vd-pd-specs { margin-top:30px; }
.vd-pd-specs-title { font-family:<?= $VD['font_display']; ?>; font-size:22px; color:<?= $VD['ink']; ?>; margin-bottom:8px; }
.vd-pd-spec { display:flex; justify-content:space-between; gap:12px; padding:12px 0; border-bottom:1px solid <?= $VD['border']; ?>; font-size:14px; color:<?= $VD['muted']; ?>; }
.vd-pd-spec b { color:<?= $VD['ink']; ?>; font-weight:600; text-align:right; }
.vd-pd-extra { margin-top:64px; }
.vd-pd-extra .vd-sec-head { margin-bottom:24px; }

/* ---------- WhatsApp order sheet ---------- */
.vdwa { display:none; position:fixed; inset:0; z-index:9999; }
.vdwa.open { display:block; }
.vdwa-veil { position:absolute; inset:0; background:rgba(22,41,31,.55); backdrop-filter:blur(2px); }
.vdwa-card { position:absolute; left:0; right:0; bottom:0; max-height:92vh; overflow-y:auto; background:<?= $VD['bg']; ?>; border-radius:24px 24px 0 0; }
@media(min-width:640px){ .vdwa-card { left:50%; right:auto; bottom:auto; top:50%; transform:translate(-50%,-50%); width:470px; border-radius:<?= $VD['radius']; ?>; box-shadow:0 30px 80px rgba(0,0,0,.28); } }
.vdwa-grip { width:40px; height:4px; border-radius:99px; background:<?= $VD['border']; ?>; margin:10px auto 0; }
@media(min-width:640px){ .vdwa-grip { display:none; } }
.vdwa-head { display:flex; align-items:center; justify-content:space-between; padding:18px 22px 14px; }
.vdwa-title { font-family:<?= $VD['font_display']; ?>; font-size:26px; color:<?= $VD['ink']; ?>; margin:0; letter-spacing:-.01em; }
.vdwa-x { width:40px; height:40px; border-radius:50%; border:none; background:<?= $VD['soft']; ?>; color:<?= $VD['ink']; ?>; cursor:pointer; font-size:20px; }
.vdwa-body { padding:0 22px 22px; }
.vdwa-prod { display:flex; gap:14px; align-items:center; padding:12px; background:<?= $VD['surface']; ?>; border:1px solid <?= $VD['border']; ?>; border-radius:<?= $VD['radius_sm']; ?>; margin-bottom:18px; }
.vdwa-prod img { width:56px; height:56px; border-radius:10px; object-fit:cover; flex-shrink:0; }
.vdwa-prod-name { font-family:<?= $VD['font_display']; ?>; font-size:19px; color:<?= $VD['ink']; ?>; margin:0 0 2px; line-height:1.2; }
.vdwa-prod-price { font-size:14px; font-weight:600; color:<?= $VD['accent']; ?>; }
.vdwa-fields { display:flex; flex-direction:column; gap:12px; }
.vdwa-fields label { font-size:11px; font-weight:600; color:<?= $VD['muted']; ?>; margin-bottom:6px; display:block; text-transform:uppercase; letter-spacing:.1em; }
.vdwa-fields input, .vdwa-fields textarea { width:100%; min-height:50px; padding:13px 16px; border:1px solid <?= $VD['border']; ?>; border-radius:12px; font-size:15px; background:#fff; color:<?= $VD['ink']; ?>; outline:none; box-sizing:border-box; font-family:<?= $VD['font_body']; ?>; }
.vdwa-fields input:focus, .vdwa-fields textarea:focus { border-color:<?= $VD['accent']; ?>; }
.vdwa-fields textarea { resize:vertical; min-height:64px; }
.vdwa-actions { display:flex; flex-direction:column; gap:10px; margin-top:18px; padding-bottom:env(safe-area-inset-bottom); }
@media(min-width:640px){ .vdwa-actions { flex-direction:row; } .vdwa-actions .vd-btn { flex:1; } }
</style>

<?php if($marquee): ?>
<div class="vdh-announce vdh-marquee"><div class="vdh-marquee-track">
  <?php for($i = 0; $i < 2; $i++): foreach($marquee as $mi): ?><span><?= htmlspecialchars($mi); ?></span><?php endforeach; endfor; ?>
</div></div>
<?php elseif($announce): ?>
<div class="vdh-announce"><?= htmlspecialchars($announce); ?></div>
<?php endif; ?>

<header class="vdh">
  <div class="vd-wrap vdh-bar">
    <button class="vdh-ic vdh-menu" onclick="vdMenu(true)" aria-label="Menu">
      <svg viewBox="0 0 24 24"><line x1="3" y1="7" x2="21" y2="7"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="17" x2="21" y2="17"/></svg>
    </button>
    <a href="<?= base_url('store/' . $slug); ?>" class="vdh-logo" aria-label="<?= htmlspecialchars($storeNm); ?>">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($storeNm); ?>">
      <?php else: ?>
        <span class="vdh-logo-name"><?= htmlspecialchars($storeNm); ?></span>
      <?php endif; ?>
    </a>
    <nav class="vdh-nav" aria-label="Primary">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="vdh-nav-link">Shop all</a>
      <?php if(($settings->show_categories ?? 1) && !empty($categories)): foreach(array_slice($categories, 0, 4) as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="vdh-nav-link"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; endif; ?>
      <?php if($settings->allow_services ?? false): ?>
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="vdh-nav-link">Services</a>
      <?php endif; ?>
      <a href="<?= base_url('store/' . $slug . '/about'); ?>" class="vdh-nav-link">About</a>
    </nav>
    <div class="vdh-actions">
      <?php if($settings->show_search ?? 1): ?>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="vdh-ic" aria-label="Search">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7.5"/><line x1="21" y1="21" x2="16.5" y2="16.5"/></svg>
      </a>
      <?php endif; ?>
      <?php if($waNum): ?>
      <a href="https://wa.me/<?= $waNum; ?>" target="_blank" rel="noopener" class="vdh-ic" aria-label="WhatsApp">
        <svg class="fill" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
      </a>
      <?php endif; ?>
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="vdh-ic" aria-label="Bag">
        <svg viewBox="0 0 24 24"><path d="M6 8h12l1 13H5L6 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
        <span class="vdh-count" id="cart-count">0</span>
      </a>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="vd-btn vd-btn-primary vdh-cta">Shop the range</a>
    </div>
  </div>
</header>

<div class="vdh-overlay" id="vdh-overlay" onclick="vdMenu(false)"></div>
<div class="vdh-drawer" id="vdh-drawer" aria-label="Menu">
  <div class="vdh-drawer-title">
    <?php if($logo): ?><img src="<?= $logo; ?>" alt="<?= htmlspecialchars($storeNm); ?>" style="max-height:36px;max-width:160px;display:block;margin-bottom:10px;"><?php else: ?><?= htmlspecialchars($storeNm); ?><?php endif; ?>
  </div>
  <a href="<?= base_url('store/' . $slug); ?>" class="vdh-drawer-link" onclick="vdMenu(false)">Home</a>
  <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="vdh-drawer-link" onclick="vdMenu(false)">Shop all</a>
  <?php if($settings->allow_services ?? false): ?>
  <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="vdh-drawer-link" onclick="vdMenu(false)">Services</a>
  <?php endif; ?>
  <a href="<?= base_url('store/' . $slug . '/about'); ?>" class="vdh-drawer-link" onclick="vdMenu(false)">About</a>
  <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="vdh-drawer-link" onclick="vdMenu(false)">Bag</a>
  <?php if(($settings->show_categories ?? 1) && !empty($categories)): ?>
  <div class="vdh-drawer-sec">Shop by concern</div>
  <?php foreach(array_slice($categories, 0, 8) as $cat): ?>
  <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="vdh-drawer-link" onclick="vdMenu(false)"><?= htmlspecialchars($cat->category_name); ?></a>
  <?php endforeach; ?>
  <?php endif; ?>
  <?php if(!empty($settings->store_phone)): ?>
  <div class="vdh-drawer-sec">Contact</div>
  <a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>" class="vdh-drawer-link"><?= htmlspecialchars($settings->store_phone); ?></a>
  <?php endif; ?>
  <?php if($waNum): ?>
  <a href="https://wa.me/<?= $waNum; ?>" target="_blank" rel="noopener" class="vd-btn vd-btn-wa" onclick="vdMenu(false)"><?= vd_wa_svg(); ?> Chat on WhatsApp</a>
  <?php endif; ?>
</div>

<!-- WhatsApp order sheet -->
<div class="vdwa" id="vdwa">
  <div class="vdwa-veil" onclick="vdWaClose()"></div>
  <div class="vdwa-card" role="dialog" aria-modal="true" aria-labelledby="vdwa-title">
    <div class="vdwa-grip"></div>
    <div class="vdwa-head">
      <h3 class="vdwa-title" id="vdwa-title">Order on WhatsApp</h3>
      <button class="vdwa-x" onclick="vdWaClose()" aria-label="Close">&times;</button>
    </div>
    <div class="vdwa-body">
      <div class="vdwa-prod">
        <img id="vdwa-img" src="" alt="" style="display:none;">
        <div>
          <p class="vdwa-prod-name" id="vdwa-name"></p>
          <div class="vdwa-prod-price" id="vdwa-price"></div>
        </div>
      </div>
      <div class="vdwa-fields">
        <div><label for="vdwa-cname">Your name</label><input type="text" id="vdwa-cname" placeholder="Full name" autocomplete="name"></div>
        <div><label for="vdwa-cphone">Phone number</label><input type="tel" id="vdwa-cphone" placeholder="Phone number" autocomplete="tel"></div>
        <div><label for="vdwa-qty">Quantity</label><input type="number" id="vdwa-qty" value="1" min="1" inputmode="numeric"></div>
        <div><label for="vdwa-note">Note (optional)</label><textarea id="vdwa-note" placeholder="Skin type, delivery area, anything we should know"></textarea></div>
      </div>
      <div class="vdwa-actions">
        <button class="vd-btn vd-btn-wa" onclick="vdWaSend()"><?= vd_wa_svg(); ?> Send order</button>
        <button class="vd-btn vd-btn-ghost" onclick="vdWaClose()">Cancel</button>
      </div>
    </div>
  </div>
</div>

<script>
  (function(){
    var el = document.getElementById('cart-count');
    if(!el) return;
    var sync = function(){ el.style.display = ((parseInt(el.textContent, 10) || 0) > 0) ? 'flex' : 'none'; };
    sync();
    if(window.MutationObserver) new MutationObserver(sync).observe(el, {childList:true, characterData:true, subtree:true});
  })();
  function vdRail(id, dir){ var el = document.getElementById(id); if(el) el.scrollBy({left: dir * el.clientWidth * .8, behavior:'smooth'}); }
  function vdMenu(open){
    document.getElementById('vdh-drawer').classList.toggle('open', open);
    document.getElementById('vdh-overlay').classList.toggle('open', open);
    document.body.style.overflow = open ? 'hidden' : '';
  }
  var vdWaProduct = null;
  function vdWaOrder(id, name, price, image, qty){
    vdWaProduct = {id:id, name:name, price:price, image:image};
    document.getElementById('vdwa-name').textContent = name;
    document.getElementById('vdwa-price').textContent = formatMoney(price);
    var img = document.getElementById('vdwa-img');
    if(image){ img.src = '<?= base_url(); ?>' + image; img.style.display = 'block'; } else { img.style.display = 'none'; }
    document.getElementById('vdwa-qty').value = qty || 1;
    document.getElementById('vdwa').classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function vdWaClose(){ document.getElementById('vdwa').classList.remove('open'); document.body.style.overflow = ''; }
  function vdWaSend(){
    if(!vdWaProduct) return;
    var name  = document.getElementById('vdwa-cname').value.trim();
    var phone = document.getElementById('vdwa-cphone').value.trim();
    var qty   = parseInt(document.getElementById('vdwa-qty').value) || 1;
    var note  = document.getElementById('vdwa-note').value.trim();
    if(!name || !phone){ showToast('Please add your name and phone number'); return; }
    var waNum = '<?= $waNum; ?>';
    if(!waNum){ showToast('WhatsApp ordering is not available'); return; }
    var msg = 'Hello <?= htmlspecialchars(addslashes($storeNm)); ?>, I would like to order:';
    msg += '\n\n' + qty + ' x ' + vdWaProduct.name + ' — ' + formatMoney(vdWaProduct.price * qty);
    msg += '\n\nName: ' + name + '\nPhone: ' + phone;
    if(note) msg += '\nNote: ' + note;
    msg += '\n\nThank you.';
    window.open('https://wa.me/' + waNum + '?text=' + encodeURIComponent(msg), '_blank');
    vdWaClose();
    showToast('Opening WhatsApp with your order…');
  }
  function vdFaq(btn){ var f = btn.closest('.vd-faq'); var open = f.classList.toggle('open'); btn.setAttribute('aria-expanded', open ? 'true' : 'false'); }
</script>
