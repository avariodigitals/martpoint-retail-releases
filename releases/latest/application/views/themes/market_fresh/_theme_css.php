<?php
/**
 * Market Fresh — design system.
 *
 * Loaded once from themes/market_fresh/header.php, which the shared layout
 * renders on EVERY storefront screen (home, catalogue, product, cart,
 * checkout, track, about, contact). That is how the theme reaches the shared
 * surfaces it does not own: this file re-declares the platform's --mp-*
 * tokens on the theme's body class, so the shared cart, checkout, footer,
 * mobile nav, product modal and toast all inherit the same palette.
 *
 * Colours are never hardcoded over the merchant's: the two accents come from
 * Theme_engine::resolveBrandColor(), which already resolves
 * per-theme override > merchant's deliberate choice > the theme's own design.
 */

$mfEngine = isset($this->theme_engine) ? $this->theme_engine : null;

/** Reject anything that is not a hex colour before it reaches the stylesheet. */
$mfHex = function ($value, $fallback) {
    $value = trim((string)$value);
    if ($value !== '' && $value[0] !== '#') $value = '#' . $value;
    return preg_match('/^#[0-9a-f]{6}$/i', $value) ? $value : $fallback;
};
$mfShade = function ($hex, $percent) {
    $hex = ltrim($hex, '#');
    $out = '';
    for ($i = 0; $i < 3; $i++) {
        $c = (int)round(hexdec(substr($hex, $i * 2, 2)) * (1 - $percent / 100));
        $out .= str_pad(dechex(max(0, min(255, $c))), 2, '0', STR_PAD_LEFT);
    }
    return '#' . strtoupper($out);
};
$mfRgb = function ($hex) {
    $hex = ltrim($hex, '#');
    return hexdec(substr($hex, 0, 2)) . ',' . hexdec(substr($hex, 2, 2)) . ',' . hexdec(substr($hex, 4, 2));
};
$mfTint = function ($hex, $percent) {
    $hex = ltrim($hex, '#');
    $out = '';
    for ($i = 0; $i < 3; $i++) {
        $v = hexdec(substr($hex, $i * 2, 2));
        $c = (int)round($v + (255 - $v) * $percent / 100);
        $out .= str_pad(dechex(max(0, min(255, $c))), 2, '0', STR_PAD_LEFT);
    }
    return '#' . strtoupper($out);
};

/** WCAG relative luminance and contrast ratio, so the palette adapts to any accent. */
$mfLum = function ($hex) {
    $hex = ltrim($hex, '#');
    $sum = 0.0;
    $weights = array(0.2126, 0.7152, 0.0722);
    for ($i = 0; $i < 3; $i++) {
        $v = hexdec(substr($hex, $i * 2, 2)) / 255;
        $sum += $weights[$i] * ($v <= 0.03928 ? $v / 12.92 : pow(($v + 0.055) / 1.055, 2.4));
    }
    return $sum;
};
$mfRatio = function ($a, $b) use ($mfLum) {
    $la = $mfLum($a);
    $lb = $mfLum($b);
    return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
};

$mfFresh  = $mfHex($mfEngine ? $mfEngine->resolveBrandColor('primary') : '', '#16A34A');
$mfAccent = $mfHex($mfEngine ? $mfEngine->resolveBrandColor('secondary') : '', '#F46B25');

// The merchant picks these two colours, and can pick a pale one. Rather than let
// white-on-pale-teal ship, every foreground is derived from a real WCAG ratio.
/** Composite a translucent brand tint over an opaque surface, so the derived
    ink is checked against what the eye actually sees rather than the raw token. */
$mfBlend = function ($fgHex, $alpha, $bgHex) {
    $fg = ltrim($fgHex, '#');
    $bg = ltrim($bgHex, '#');
    $out = '';
    for ($i = 0; $i < 3; $i++) {
        $c = (int)round(hexdec(substr($fg, $i * 2, 2)) * $alpha + hexdec(substr($bg, $i * 2, 2)) * (1 - $alpha));
        $out .= str_pad(dechex(max(0, min(255, $c))), 2, '0', STR_PAD_LEFT);
    }
    return '#' . strtoupper($out);
};

/** No neutral in this theme is hardcoded. Every surface, ink and shadow is the
    merchant's own primary pushed toward white or black, so a teal shop reads
    teal and a plum shop reads plum instead of wearing the theme's own green. */
// A very pale brand colour cannot carry a neutral ramp — tinting white toward
// white leaves invisible borders — so fall back to the accent, then to a slate.
$mfNeutralBase = $mfLum($mfFresh) <= 0.72 ? $mfFresh
    : ($mfLum($mfAccent) <= 0.72 ? $mfAccent : '#334155');

$mfDeepInk = $mfShade($mfNeutralBase, 88);
$mfDeep    = $mfShade($mfNeutralBase, 68);
$mfInk     = $mfShade($mfNeutralBase, 80);
$mfCanvas  = $mfTint($mfNeutralBase, 97);
$mfSoft    = $mfTint($mfNeutralBase, 93);
$mfLine    = $mfTint($mfNeutralBase, 88);
$mfInkRgb  = $mfRgb($mfDeepInk);

/** White when the brand colour is dark enough to carry it, else the theme's deep
    ink. A mid-tone sits in a narrow band where neither reaches AA, and that band
    is exactly where a label would otherwise ship at 4.3:1 — so black is the last
    resort there rather than a contrast failure. */
$mfReadableOn = function ($hex) use ($mfRatio, $mfDeepInk) {
    if ($mfRatio($hex, '#FFFFFF') >= 4.5) return '#FFFFFF';
    if ($mfRatio($hex, $mfDeepInk) >= 4.5) return $mfDeepInk;
    return $mfRatio($hex, '#000000') >= $mfRatio($hex, '#FFFFFF') ? '#000000' : '#FFFFFF';
};
$mfReadableAs = function ($hex, $bg) use ($mfRatio, $mfShade) {
    for ($step = 0; $step <= 8; $step++) {
        $candidate = $step === 0 ? $hex : $mfShade($hex, $step * 9);
        if ($mfRatio($candidate, $bg) >= 4.5) return $candidate;
    }
    return $mfShade($hex, 72);
};
$mfOnAccent   = $mfReadableOn($mfAccent);
$mfAccentText = $mfReadableAs($mfAccent, $mfCanvas);
$mfOnFresh    = $mfReadableOn($mfFresh);
$mfFreshText  = $mfReadableAs($mfFresh, $mfCanvas);
$mfAccentDark = $mfShade($mfAccent, 14);

// The brand-art tile paints its own translucent tint over the soft surface, so
// its watermark ink is derived against that composite, not against the canvas.
$mfArtAlpha = 0.14;
$mfArtBg    = $mfBlend($mfFresh, $mfArtAlpha, $mfSoft);
$mfArtInk   = $mfReadableAs($mfFresh, $mfArtBg);

/** Nudge a colour toward white or black until it clears $target on $bg, keeping
    the merchant's hue — they chose a colour, not a contrast failure. */
$mfReadableBoth = function ($hex, $bg, $target = 4.5) use ($mfRatio, $mfShade, $mfTint, $mfLum) {
    if ($mfRatio($hex, $bg) >= $target) return $hex;
    $lighten = $mfLum($bg) < 0.2;
    for ($step = 1; $step <= 10; $step++) {
        $candidate = $lighten ? $mfTint($hex, $step * 10) : $mfShade($hex, $step * 9);
        if ($mfRatio($candidate, $bg) >= $target) return $candidate;
    }
    return $lighten ? '#FFFFFF' : $mfShade($hex, 80);
};

// Derived against the darkest light surface (--mf-line), so muted text also
// clears AA where the platform puts it on a rule or a divider chip.
$mfMuted = $mfReadableBoth($mfTint($mfDeepInk, 45), $mfLine, 4.5);

// The merchant's announcement colour wins; otherwise the strip is the same deep
// brand tone the rest of the chrome uses. Either way the text is derived, so a
// pale announcement colour cannot ship white-on-pale.
$mfAnnounce = $mfHex($settings->announcement_bar_color ?? '', '');
$mfBarBg    = $mfAnnounce !== '' ? $mfAnnounce : $mfDeep;
$mfBarInk   = $mfRatio('#FFFFFF', $mfBarBg) >= 4.5 ? '#FFFFFF' : $mfReadableBoth($mfDeepInk, $mfBarBg, 4.5);

$mfBtnStyle = strtolower(trim((string)($settings->button_style ?? '')));
$mfRadius   = in_array($mfBtnStyle, ['pill', 'square'], true) ? null : '14px';
$mfRadiusSm = in_array($mfBtnStyle, ['pill', 'square'], true) ? null : '10px';

// The merchant's own footer/button colours stay authoritative.
$mfFooterBg   = $mfHex($settings->footer_bg_color ?? '', '');
$mfFooterText = $mfHex($settings->footer_text_color ?? '', '');
$mfButton     = $mfHex($settings->button_color ?? '', '');

// …authoritative on hue, not on legibility: a merchant who picks a pale grey for
// a mid-green footer gets that grey lifted until it reads, rather than shipped
// as a 3:1 wash. Their colour is kept whenever it already clears AA.
$mfFooterBgHex = $mfFooterBg !== '' ? $mfFooterBg : $mfDeep;
$mfFooterInk   = $mfReadableBoth($mfFooterText !== '' ? $mfFooterText : $mfTint($mfDeepInk, 62), $mfFooterBgHex);
$mfOnFooter    = $mfReadableOn($mfFooterBgHex);
$mfFooterVeil  = $mfBlend($mfOnFooter, 0.08, $mfFooterBgHex);
$mfFooterRule  = $mfBlend($mfOnFooter, 0.14, $mfFooterBgHex);

/** The platform's shared CSS paints white ON --mp-primary and also paints
    --mp-primary AS text on light surfaces. One deepened variant of the
    merchant's accent satisfies both, so the bridge ships a colour that is
    legible wherever the platform happens to put it. An accent that is already
    dark enough is passed through untouched — the merchant keeps their colour. */
$mfStrong = function ($hex) use ($mfRatio, $mfShade, $mfCanvas) {
    for ($step = 0; $step <= 8; $step++) {
        $candidate = $step === 0 ? $hex : $mfShade($hex, $step * 9);
        if ($mfRatio('#FFFFFF', $candidate) >= 4.5 && $mfRatio($candidate, $mfCanvas) >= 4.5) return $candidate;
    }
    return $mfShade($hex, 72);
};
$mfAccentStrong = $mfStrong($mfAccent);
$mfFreshStrong  = $mfStrong($mfFresh);
$mfButtonStrong = $mfButton !== '' ? $mfStrong($mfButton) : '';

// The platform repaints its call-to-action buttons with the merchant's own
// button colour, so the foreground has to be derived against that, not the accent.
$mfButtonHex = $mfButton !== '' ? $mfButtonStrong : $mfAccentStrong;
$mfOnButton  = $mfReadableOn($mfButtonHex);

// The shared reviews partial reads --fx-* tokens with hardcoded fallbacks, so
// declaring them here pulls that block into this palette without touching the
// shared markup or any other theme.
$mfReviewAccent = $mfReadableAs($mfAccent, $mfSoft);
$mfStarOff      = $mfReadableBoth($mfLine, $mfSoft, 3.0);
// "Low stock" is a warning, not a brand colour: it stays amber, darkened only as
// far as it needs to be to read on a white card.
$mfWarn         = $mfReadableBoth('#B4690E', '#FFFFFF', 4.5);

$mfFont = trim((string)($settings->font_family ?: ($theme->default_font_family ?? 'Inter')));
// Manrope is the theme's designed display face. A merchant who chose a font
// gets their font for headings too — the design must follow their setting.
$mfDisplay = ($mfFont === 'Inter' || $mfFont === '') ? "'Manrope'" : "'" . addslashes($mfFont) . "'";
$mfBody    = "'" . addslashes($mfFont !== '' ? $mfFont : 'Inter') . "'";
?>
<style>
.theme-market_fresh {
  /* Canvas & ink — all derived from the merchant's primary */
  --mf-bg:<?= $mfCanvas; ?>;
  --mf-card:#FFFFFF;
  --mf-ink:<?= $mfInk; ?>;
  --mf-muted:<?= $mfMuted; ?>;
  --mf-line:<?= $mfLine; ?>;
  --mf-soft:<?= $mfSoft; ?>;
  --mf-deep:<?= $mfDeep; ?>;
  --mf-deep-ink:<?= $mfDeepInk; ?>;
  --mf-deep-ink-rgb:<?= $mfInkRgb; ?>;
  --mf-bar-bg:<?= $mfBarBg; ?>;
  --mf-bar-ink:<?= $mfBarInk; ?>;
  --mf-wa:#25D366;
  --mf-wa-dark:#1DA851;
  --mf-danger:#C0392B;

  /* Brand — resolved, never hardcoded over the merchant */
  --mf-accent:<?= $mfAccent; ?>;
  --mf-accent-dark:<?= $mfAccentDark; ?>;
  --mf-accent-rgb:<?= $mfRgb($mfAccent); ?>;
  --mf-on-accent:<?= $mfOnAccent; ?>;
  --mf-on-accent-dark:<?= $mfReadableOn($mfAccentDark); ?>;
  --mf-accent-text:<?= $mfAccentText; ?>;
  --mf-accent-strong:<?= $mfAccentStrong; ?>;
  --mf-fresh:<?= $mfFresh; ?>;
  --mf-fresh-dark:<?= $mfShade($mfFresh, 14); ?>;
  --mf-fresh-rgb:<?= $mfRgb($mfFresh); ?>;
  --mf-fresh-strong:<?= $mfFreshStrong; ?>;
  --mf-on-fresh:<?= $mfOnFresh; ?>;
  --mf-fresh-text:<?= $mfFreshText; ?>;
  --mf-art-bg:<?= $mfArtBg; ?>;
  --mf-art-ink:<?= $mfArtInk; ?>;
  --mf-on-footer:<?= $mfOnFooter; ?>;
  --mf-footer-veil:<?= $mfFooterVeil; ?>;
  --mf-footer-rule:<?= $mfFooterRule; ?>;
  --mf-on-wa:<?= $mfReadableOn('#25D366'); ?>;
  --mf-wa-text:<?= $mfReadableAs('#1DA851', $mfCanvas); ?>;
  --mf-star-off:<?= $mfStarOff; ?>;
  --mf-warn:<?= $mfWarn; ?>;
  --mf-on-button:<?= $mfOnButton; ?>;

  /* The shared reviews partial reads --fx-* tokens; supply them so that block
     wears this theme's palette instead of another theme's hardcoded cream. */
  --fx-cream:var(--mf-soft);
  --fx-cream-deep:var(--mf-star-off);
  --fx-ink:var(--mf-ink);
  --fx-muted:var(--mf-muted);
  --fx-border:var(--mf-line);
  --fx-green:<?= $mfReviewAccent; ?>;
  --fx-green-deep:var(--mf-deep);

  /* Type */
  --mf-display:<?= $mfDisplay; ?>,<?= $mfBody; ?>,-apple-system,BlinkMacSystemFont,sans-serif;
  --mf-body:<?= $mfBody; ?>,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;

  /* Rhythm */
  --mf-space:8px;
  --mf-r:18px;
  --mf-r-sm:12px;
  --mf-shadow:0 1px 2px rgba(<?= $mfInkRgb; ?>,.04),0 12px 32px rgba(<?= $mfInkRgb; ?>,.06);
  --mf-shadow-lift:0 2px 4px rgba(<?= $mfInkRgb; ?>,.05),0 20px 44px rgba(<?= $mfInkRgb; ?>,.12);
  --mf-scrim:rgba(<?= $mfInkRgb; ?>,.5);
  --mf-scrim-soft:rgba(<?= $mfInkRgb; ?>,.42);

  /* ---- Bridge onto the platform tokens so shared screens flow ----
     The platform paints white on these AND uses them as text on light surfaces,
     so the deepened variant goes across the bridge; the theme's own components
     keep the merchant's exact accent because they set their own foreground. */
  --mp-primary:<?= $mfAccentStrong; ?>;
  --mp-primary-dark:<?= $mfShade($mfAccentStrong, 14); ?>;
  --mp-secondary:<?= $mfFreshStrong; ?>;
  --mp-dark:var(--mf-deep);
  --mp-gray:var(--mf-muted);
  --mp-border:var(--mf-line);
  --mp-light-gray:var(--mf-soft);
  --mp-white:var(--mf-card);
  --mp-danger:var(--mf-danger);
  --mp-success:<?= $mfFreshStrong; ?>;
  --mp-button:<?= $mfButton !== '' ? $mfButtonStrong : $mfAccentStrong; ?>;
  --mp-button-dark:<?= $mfShade($mfButton !== '' ? $mfButtonStrong : $mfAccentStrong, 14); ?>;
  --mp-footer-bg:<?= $mfFooterBg !== '' ? $mfFooterBg : 'var(--mf-deep)'; ?>;
  --mp-footer-text:<?= $mfFooterInk; ?>;
<?php if($mfRadius !== null): ?>
  --mp-radius:<?= $mfRadius; ?>;
  --mp-radius-sm:<?= $mfRadiusSm; ?>;
<?php endif; ?>
}

/* The shared retail chrome is replaced by this theme's own header. */
.theme-market_fresh .mp-topbar,
.theme-market_fresh .mp-announcement,
.theme-market_fresh .mp-nav,
.theme-market_fresh .mp-header,
.theme-market_fresh .mp-mobile-menu-btn { display:none !important; }

.theme-market_fresh { background:var(--mf-bg); color:var(--mf-ink); font-family:var(--mf-body); -webkit-font-smoothing:antialiased; }
/* :where() keeps these element defaults at zero specificity, so a component class
   further down (a white hero title, an accent "View all" link) still wins. */
.theme-market_fresh :where(h1,h2,h3,h4) { font-family:var(--mf-display); letter-spacing:-.02em; color:var(--mf-ink); }
.theme-market_fresh :where(a) { color:inherit; }
.theme-market_fresh ::selection { background:rgba(var(--mf-accent-rgb),.2); }
.theme-market_fresh :focus-visible { outline:2px solid var(--mf-accent); outline-offset:2px; border-radius:6px; }
.theme-market_fresh img { max-width:100%; }

/* Small text links keep their line box but get a finger-sized hit area (WCAG 2.5.8). */
.mf-crumbs a, .mf-detail-cat, .mf-sec-link { padding:5px 0; }

/* ---------- Layout ---------- */
.mf-sr-only { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
.mf-wrap { max-width:1360px; margin:0 auto; padding:0 clamp(16px,3vw,32px); }
.mf-sec { padding:clamp(40px,6vw,72px) 0; }
.mf-sec--soft { background:var(--mf-soft); }
.mf-sec--card { background:var(--mf-card); border-top:1px solid var(--mf-line); border-bottom:1px solid var(--mf-line); }
.mf-sec-head { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:28px; }
.mf-sec-head--center { flex-direction:column; align-items:center; text-align:center; }
.mf-kicker { font-family:var(--mf-body); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.16em; color:var(--mf-accent-text); margin-bottom:8px; }
.mf-title { font-size:clamp(24px,3.4vw,34px); font-weight:800; margin:0; line-height:1.15; }
.mf-lead { font-size:15px; line-height:1.65; color:var(--mf-muted); margin:8px 0 0; max-width:56ch; }
.mf-sec-link { font-family:var(--mf-body); font-size:14px; font-weight:600; color:var(--mf-accent-text); text-decoration:none; white-space:nowrap; display:inline-flex; align-items:center; gap:6px; }
.mf-sec-link:hover { color:var(--mf-accent-text); text-decoration:underline; }

/* ---------- Buttons ---------- */
.mf-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:14px 26px; border-radius:999px;
  font-family:var(--mf-body); font-size:14px; font-weight:600; line-height:1; text-decoration:none; cursor:pointer;
  border:1px solid transparent; transition:background .18s,color .18s,border-color .18s,transform .12s,box-shadow .18s; }
.mf-btn:active { transform:scale(.985); }
.mf-btn svg { width:18px; height:18px; flex-shrink:0; }
.mf-btn--primary { background:var(--mf-accent); color:var(--mf-on-accent); box-shadow:0 8px 20px rgba(var(--mf-accent-rgb),.22); }
.mf-btn--primary:hover { background:var(--mf-accent-dark); }
.mf-btn--deep { background:var(--mf-deep); color:#fff; }
.mf-btn--deep:hover { background:var(--mf-deep-ink); }
.mf-btn--fresh { background:var(--mf-fresh); color:var(--mf-on-fresh); }
.mf-btn--fresh:hover { background:var(--mf-fresh-dark); }
.mf-btn--wa { background:var(--mf-wa); color:var(--mf-on-wa); }
.mf-btn--wa:hover { background:var(--mf-wa-dark); }
.mf-btn--outline { background:transparent; color:var(--mf-ink); border-color:var(--mf-line); }
.mf-btn--outline:hover { border-color:var(--mf-accent); color:var(--mf-accent-text); }
/* The ghost chip only ever sits on --mf-deep or the deep gradient, both of which
   shade() drives dark enough that white clears AA on any merchant palette. */
.mf-btn--ghost { background:rgba(255,255,255,.16); color:#fff; border-color:rgba(255,255,255,.42); }
.mf-btn--ghost:hover { background:rgba(255,255,255,.26); }
/* On the accent panel a translucent wash of its own label colour cannot reach AA
   on a mid-tone brand colour, so the chip inverts and inherits the same ratio
   --mf-on-accent already guarantees for the panel's text. */
.mf-panel--accent .mf-btn--ghost { background:var(--mf-on-accent); border-color:var(--mf-on-accent); color:var(--mf-accent); }
.mf-panel--accent .mf-btn--ghost:hover { background:var(--mf-on-accent); box-shadow:0 6px 18px rgba(var(--mf-deep-ink-rgb),.26); }
.mf-btn--sm { padding:10px 18px; font-size:13px; }
.mf-btn--block { width:100%; }
.mf-btn[disabled],.mf-btn[aria-disabled="true"] { opacity:.5; cursor:not-allowed; box-shadow:none; }

/* ---------- Product card ---------- */
.mf-products { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:20px; }
@media(max-width:1100px){ .mf-products { grid-template-columns:repeat(3,minmax(0,1fr)); } }
@media(max-width:820px){ .mf-products { grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; } }

.mf-p { position:relative; display:flex; flex-direction:column; background:var(--mf-card); border:1px solid var(--mf-line);
  border-radius:var(--mf-r); overflow:hidden; transition:transform .2s,box-shadow .2s,border-color .2s; }
.mf-p:hover { transform:translateY(-4px); box-shadow:var(--mf-shadow-lift); border-color:rgba(var(--mf-fresh-rgb),.35); }
.mf-p--link { text-decoration:none; color:inherit; }
.mf-p-media { position:relative; aspect-ratio:1/1; background:var(--mf-soft); overflow:hidden; }
.mf-p-media img { width:100%; height:100%; object-fit:cover; transition:transform .45s ease; }
.mf-p:hover .mf-p-media img { transform:scale(1.04); }
.mf-p-flag { position:absolute; top:10px; left:10px; z-index:2; background:var(--mf-accent); color:#fff;
  font-family:var(--mf-body); font-size:11px; font-weight:700; letter-spacing:.04em; padding:5px 10px; border-radius:999px; }
.mf-p-flag--fresh { background:var(--mf-fresh); }
.mf-p-flag--deep { background:var(--mf-deep); }
.mf-p-body { display:flex; flex-direction:column; gap:8px; padding:14px 14px 16px; flex:1; }
.mf-p-cat { font-family:var(--mf-body); font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:var(--mf-muted); }
.mf-p-name { font-family:var(--mf-display); font-size:15px; font-weight:700; line-height:1.35; color:var(--mf-ink);
  display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:2.7em; }
.mf-p-unit { font-family:var(--mf-body); font-size:12px; color:var(--mf-muted); }
.mf-p-price { font-family:var(--mf-display); font-size:18px; font-weight:800; color:var(--mf-ink); display:flex; align-items:baseline; gap:8px; flex-wrap:wrap; }
.mf-p-price .was { font-size:13px; font-weight:500; color:var(--mf-muted); text-decoration:line-through; }
.mf-p-actions { display:flex; gap:8px; margin-top:auto; padding-top:6px; }
.mf-p-add { flex:1; min-width:0; }
.mf-p-wa { flex:0 0 44px; padding:0; width:44px; height:44px; border-radius:999px; }
.mf-p-stock { font-family:var(--mf-body); font-size:11px; font-weight:600; letter-spacing:.04em; text-transform:uppercase; color:var(--mf-fresh-dark); display:flex; align-items:center; gap:6px; }
.mf-p-stock::before { content:''; width:6px; height:6px; border-radius:50%; background:currentColor; }
.mf-p-stock--out { color:var(--mf-danger); }
.mf-p-stock--low { color:var(--mf-warn); }
@media(max-width:480px){
  .mf-p-body { padding:12px; }
  .mf-p-name { font-size:14px; min-height:2.6em; }
  .mf-p-price { font-size:16px; }
}

/* ---------- Categories ---------- */
.mf-cats { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:14px; }
@media(max-width:1100px){ .mf-cats { grid-template-columns:repeat(4,minmax(0,1fr)); } }
@media(max-width:820px){
  .mf-cats { display:flex; grid-template-columns:none; gap:12px; overflow-x:auto; padding-bottom:8px;
    scroll-snap-type:x mandatory; -webkit-overflow-scrolling:touch; scrollbar-width:none; }
  .mf-cats::-webkit-scrollbar { display:none; }
  .mf-cat { flex:0 0 148px; scroll-snap-align:start; }
}
.mf-cat { position:relative; display:block; aspect-ratio:1/1; border-radius:var(--mf-r); overflow:hidden;
  text-decoration:none; color:#fff; background:var(--mf-soft); border:1px solid var(--mf-line);
  transition:transform .2s,box-shadow .2s; }
.mf-cat:hover { transform:translateY(-4px); box-shadow:var(--mf-shadow-lift); }
.mf-cat img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; transition:transform .45s ease; }
.mf-cat:hover img { transform:scale(1.06); }
/* The veil has to be dark where the label actually sits — on a short card the
   name lands well above the bottom edge, so the ramp reaches full early. */
.mf-cat::after { content:''; position:absolute; inset:0; background:linear-gradient(180deg,rgba(var(--mf-deep-ink-rgb),0) 0%,rgba(var(--mf-deep-ink-rgb),.28) 38%,rgba(var(--mf-deep-ink-rgb),.86) 66%,rgba(var(--mf-deep-ink-rgb),.93) 100%); }
.mf-cat-body { position:absolute; z-index:2; left:0; right:0; bottom:0; padding:14px; }
.mf-cat-name { font-family:var(--mf-display); font-size:15px; font-weight:700; line-height:1.25; }
.mf-cat-count { font-family:var(--mf-body); font-size:12px; color:rgba(255,255,255,.78); margin-top:3px; }
/* Brand-only artwork, matching the platform placeholder rule: a tint of the
   resolved primary with the brand name in a shade that reads against it. The
   tint is pre-composited over the soft surface so the ink is provably legible. */
.mf-art { position:absolute; inset:0; z-index:1; display:flex; align-items:center; justify-content:center; text-align:center; padding:16px;
  background:var(--mf-art-bg); color:var(--mf-art-ink); }
.mf-art span { font-family:var(--mf-display); font-weight:700; line-height:1.2; font-size:clamp(11px,1.5vw,17px); overflow-wrap:anywhere; }

/* ---------- Trust strip ---------- */
.mf-trust { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
@media(max-width:900px){ .mf-trust { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:520px){ .mf-trust { grid-template-columns:1fr; } }
.mf-trust-item { display:flex; gap:14px; align-items:flex-start; background:var(--mf-card); border:1px solid var(--mf-line);
  border-radius:var(--mf-r); padding:20px; }
.mf-trust-ico { flex:0 0 42px; width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center;
  background:rgba(var(--mf-fresh-rgb),.1); color:var(--mf-fresh-dark); }
.mf-trust-ico svg { width:20px; height:20px; }
.mf-trust-title { font-family:var(--mf-display); font-size:15px; font-weight:700; margin-bottom:3px; }
.mf-trust-text { font-family:var(--mf-body); font-size:13px; line-height:1.55; color:var(--mf-muted); }

/* ---------- Steps ---------- */
.mf-steps { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }
@media(max-width:820px){ .mf-steps { grid-template-columns:1fr; } }
.mf-step { background:var(--mf-card); border:1px solid var(--mf-line); border-radius:var(--mf-r); padding:24px; }
.mf-step-n { width:34px; height:34px; border-radius:50%; background:var(--mf-deep); color:#fff; display:flex; align-items:center;
  justify-content:center; font-family:var(--mf-display); font-size:14px; font-weight:700; margin-bottom:14px; }
.mf-step-title { font-family:var(--mf-display); font-size:16px; font-weight:700; margin-bottom:6px; }
.mf-step-text { font-family:var(--mf-body); font-size:14px; line-height:1.6; color:var(--mf-muted); }

/* ---------- Panels / banners ---------- */
.mf-panel { border-radius:24px; overflow:hidden; position:relative; }
.mf-panel--deep { background:var(--mf-deep); color:#fff; }
/* The WhatsApp panel wears the brand's own deep tone: green is reserved for the
   WhatsApp button itself, so the page is not washed in a second colour. */
.mf-panel--wa { background:linear-gradient(135deg,var(--mf-deep),var(--mf-deep-ink)); color:#fff; }
.mf-panel--accent { background:linear-gradient(135deg,var(--mf-accent),var(--mf-accent-dark)); color:var(--mf-on-accent); }
.mf-split { display:grid; grid-template-columns:1.05fr .95fr; align-items:stretch; }
@media(max-width:900px){ .mf-split { grid-template-columns:1fr; } }
.mf-split-body { padding:clamp(28px,4vw,52px); }
.mf-split-media { position:relative; min-height:260px; background:rgba(255,255,255,.08); }
.mf-split-media img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
.mf-panel .mf-title, .mf-panel h2, .mf-panel h3 { color:inherit; }
.mf-panel .mf-kicker { color:inherit; opacity:.72; }
.mf-panel-text { font-family:var(--mf-body); font-size:15px; line-height:1.65; opacity:.92; margin:10px 0 22px; max-width:52ch; }

/* ---------- Forms ---------- */
.mf-field { width:100%; padding:13px 16px; border:1px solid var(--mf-line); border-radius:var(--mf-r-sm);
  background:var(--mf-card); font-family:var(--mf-body); font-size:14px; color:var(--mf-ink); outline:none;
  transition:border-color .18s,box-shadow .18s; box-sizing:border-box; }
.mf-field::placeholder { color:var(--mf-muted); }
.mf-field:focus { border-color:var(--mf-accent); box-shadow:0 0 0 3px rgba(var(--mf-accent-rgb),.14); }
textarea.mf-field { resize:vertical; min-height:88px; line-height:1.55; }
.mf-label { display:block; font-family:var(--mf-body); font-size:12px; font-weight:600; letter-spacing:.04em;
  text-transform:uppercase; color:var(--mf-muted); margin-bottom:6px; }

/* Native selects pop outside the app container on mobile — the platform rule
   is an inline custom list. This one expands in flow, capped and scrollable. */
.mf-select { position:relative; }
.mf-select-btn { width:100%; display:flex; align-items:center; justify-content:space-between; gap:10px; padding:12px 14px;
  border:1px solid var(--mf-line); border-radius:999px; background:var(--mf-card); font-family:var(--mf-body); font-size:13px;
  font-weight:600; color:var(--mf-ink); cursor:pointer; text-align:left; }
.mf-select-btn svg { width:14px; height:14px; color:var(--mf-muted); flex-shrink:0; }
.mf-select-list { display:none; position:absolute; z-index:40; left:0; right:0; top:calc(100% + 6px); max-height:280px;
  overflow-y:auto; background:var(--mf-card); border:1px solid var(--mf-line); border-radius:var(--mf-r-sm);
  box-shadow:var(--mf-shadow); padding:6px; }
.mf-select.open .mf-select-list { display:block; }
.mf-select-opt { display:flex; align-items:center; justify-content:space-between; gap:10px; width:100%; padding:10px 12px;
  border:none; background:none; border-radius:8px; font-family:var(--mf-body); font-size:14px; color:var(--mf-ink); cursor:pointer; text-align:left; }
.mf-select-opt:hover { background:var(--mf-soft); }
.mf-select-opt[aria-selected="true"] { color:var(--mf-accent-text); font-weight:700; }

.mf-chips { display:flex; gap:8px; flex-wrap:wrap; }
.mf-chip { display:inline-flex; align-items:center; gap:6px; padding:9px 16px; border-radius:999px; border:1px solid var(--mf-line);
  background:var(--mf-card); font-family:var(--mf-body); font-size:13px; font-weight:600; color:var(--mf-muted);
  text-decoration:none; cursor:pointer; transition:all .18s; white-space:nowrap; }
.mf-chip:hover { border-color:var(--mf-accent); color:var(--mf-accent-text); }
.mf-chip[aria-pressed="true"],.mf-chip.is-active { background:var(--mf-deep); border-color:var(--mf-deep); color:#fff; }
.mf-chip .n { font-size:11px; opacity:.7; font-weight:500; }
/* On a phone the chip row scrolls instead of stacking into a wall of filters. */
@media(max-width:820px){
  .mf-chips { flex-wrap:nowrap; overflow-x:auto; scroll-snap-type:x mandatory; -webkit-overflow-scrolling:touch;
    scrollbar-width:none; padding-bottom:6px; }
  .mf-chips::-webkit-scrollbar { display:none; }
  .mf-chips > .mf-chip { flex:0 0 auto; scroll-snap-align:start; }
}

/* Scroll rail (mobile carousels) */
.mf-rail { display:flex; gap:12px; overflow-x:auto; scroll-snap-type:x mandatory; -webkit-overflow-scrolling:touch;
  padding-bottom:8px; scrollbar-width:none; }
.mf-rail::-webkit-scrollbar { display:none; }
.mf-rail > * { scroll-snap-align:start; }

/* ---------- Empty state ---------- */
.mf-empty { text-align:center; padding:72px 20px; background:var(--mf-card); border:1px dashed var(--mf-line); border-radius:var(--mf-r); }
.mf-empty-ico { width:56px; height:56px; margin:0 auto 16px; border-radius:50%; background:var(--mf-soft); color:var(--mf-fresh-dark);
  display:flex; align-items:center; justify-content:center; }
.mf-empty-ico svg { width:24px; height:24px; }
.mf-empty-title { font-family:var(--mf-display); font-size:20px; font-weight:700; margin-bottom:6px; }
.mf-empty-text { font-family:var(--mf-body); font-size:14px; color:var(--mf-muted); margin:0 auto 20px; max-width:44ch; }

/* ---------- Breadcrumb ---------- */
.mf-crumbs { display:flex; align-items:center; gap:8px; flex-wrap:wrap; padding:20px 0 0;
  font-family:var(--mf-body); font-size:13px; color:var(--mf-muted); }
.mf-crumbs a { color:var(--mf-muted); text-decoration:none; }
.mf-crumbs a:hover { color:var(--mf-accent-text); }
.mf-crumbs .sep { color:var(--mf-muted); }
.mf-crumbs [aria-current="page"] { color:var(--mf-ink); font-weight:600; }

/* ---------- Catalogue toolbar (search, filters, sort, count) ---------- */
.mf-toolbar { display:flex; flex-direction:column; gap:14px; margin-bottom:24px; }
.mf-toolbar-row { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
.mf-toolbar-row--between { justify-content:space-between; }
.mf-search { position:relative; flex:1 1 320px; min-width:0; max-width:520px; }
.mf-search svg { position:absolute; left:16px; top:50%; transform:translateY(-50%); width:17px; height:17px;
  color:var(--mf-muted); pointer-events:none; }
.mf-search input { width:100%; padding:13px 44px 13px 44px; border:1px solid var(--mf-line); border-radius:999px;
  background:var(--mf-card); font-family:var(--mf-body); font-size:14px; color:var(--mf-ink); }
.mf-search input::placeholder { color:var(--mf-muted); }
.mf-search input:focus { outline:none; border-color:var(--mf-accent); box-shadow:0 0 0 3px rgba(var(--mf-accent-rgb),.14); }
.mf-search-clear { position:absolute; right:8px; top:50%; transform:translateY(-50%); width:30px; height:30px;
  border:0; border-radius:50%; background:var(--mf-soft); color:var(--mf-muted); cursor:pointer;
  display:flex; align-items:center; justify-content:center; }
.mf-search-clear:hover { color:var(--mf-ink); }
.mf-search-clear svg { position:static; transform:none; width:14px; height:14px; }
.mf-note { font-family:var(--mf-body); font-size:13px; color:var(--mf-muted); }
.mf-sort { position:relative; flex:0 0 auto; min-width:200px; }
.mf-sort .mf-select-list { max-height:260px; overflow-y:auto; }
@media(max-width:640px){
  .mf-search { flex-basis:100%; max-width:none; }
  .mf-sort { flex:1 1 100%; }
}

/* ---------- Pagination ---------- */
.mf-pager { display:flex; justify-content:center; align-items:center; gap:6px; flex-wrap:wrap; margin-top:40px; }
.mf-page-btn { min-width:42px; height:42px; padding:0 14px; border-radius:999px; border:1px solid var(--mf-line);
  background:var(--mf-card); color:var(--mf-muted); font-family:var(--mf-body); font-size:14px; font-weight:600;
  text-decoration:none; display:inline-flex; align-items:center; justify-content:center; transition:color .2s,border-color .2s,background .2s; }
.mf-page-btn:hover { border-color:var(--mf-accent); color:var(--mf-accent-text); }
.mf-page-btn[aria-current="page"] { background:var(--mf-deep); border-color:var(--mf-deep); color:#fff; }
.mf-page-btn[aria-disabled="true"] { opacity:.4; pointer-events:none; }
.mf-page-gap { padding:0 4px; color:var(--mf-line); }

/* ---------- Product detail ---------- */
.mf-detail { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:clamp(24px,4vw,56px);
  align-items:start; padding:clamp(24px,4vw,44px) 0 clamp(40px,6vw,72px); }
@media(max-width:900px){ .mf-detail { grid-template-columns:1fr; gap:28px; } }
.mf-detail-media { position:relative; aspect-ratio:1/1; border-radius:var(--mf-r); overflow:hidden;
  background:var(--mf-soft); border:1px solid var(--mf-line); }
.mf-detail-media img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
.mf-detail-flag { position:absolute; top:14px; left:14px; z-index:2; background:var(--mf-accent); color:#fff;
  font-family:var(--mf-body); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em;
  padding:6px 12px; border-radius:999px; }
.mf-detail-info { display:flex; flex-direction:column; gap:16px; min-width:0; }
.mf-detail-cat { font-family:var(--mf-body); font-size:11px; font-weight:700; text-transform:uppercase;
  letter-spacing:.14em; color:var(--mf-accent-text); text-decoration:none; }
.mf-detail-cat:hover { color:var(--mf-accent-text); text-decoration:underline; }
.mf-detail-name { font-family:var(--mf-display); font-size:clamp(26px,3.6vw,38px); font-weight:800;
  line-height:1.14; letter-spacing:-.02em; margin:0; color:var(--mf-ink); }
.mf-detail-price { display:flex; align-items:baseline; gap:10px; flex-wrap:wrap; font-family:var(--mf-display);
  font-size:clamp(24px,3vw,32px); font-weight:800; color:var(--mf-ink); }
.mf-detail-price .was { font-size:16px; font-weight:500; color:var(--mf-muted); text-decoration:line-through; }
.mf-detail-save { font-family:var(--mf-body); font-size:12px; font-weight:700; color:#fff;
  background:var(--mf-fresh); padding:5px 11px; border-radius:999px; }
.mf-detail-desc { font-family:var(--mf-body); font-size:15px; line-height:1.72; color:var(--mf-muted); margin:0; }
.mf-detail-desc a { color:var(--mf-accent-text); }

.mf-buy { display:flex; flex-direction:column; gap:14px; padding-top:18px; margin-top:4px; border-top:1px solid var(--mf-line); }
.mf-buy-row { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
.mf-qty { display:inline-flex; align-items:center; border:1px solid var(--mf-line); border-radius:999px;
  background:var(--mf-card); overflow:hidden; }
.mf-qty button { width:44px; height:46px; border:0; background:transparent; color:var(--mf-ink); font-size:18px;
  line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; }
.mf-qty button:hover:not([disabled]) { background:var(--mf-soft); color:var(--mf-accent-text); }
.mf-qty button[disabled] { opacity:.35; cursor:not-allowed; }
.mf-qty-val { min-width:46px; text-align:center; font-family:var(--mf-display); font-size:16px; font-weight:700; }
.mf-buy-actions { display:flex; gap:10px; flex-wrap:wrap; }
.mf-buy-actions .mf-btn { flex:1 1 200px; }

/* Facts the platform actually recorded — unit, category, brand, item code. */
.mf-facts { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; margin:0; padding:0; list-style:none; }
@media(max-width:520px){ .mf-facts { grid-template-columns:1fr; } }
.mf-fact { display:flex; gap:10px; align-items:flex-start; padding:12px 14px; background:var(--mf-soft); border-radius:var(--mf-r-sm); }
.mf-fact-ico { flex:0 0 18px; width:18px; height:18px; color:var(--mf-fresh-dark); margin-top:2px; }
.mf-fact-ico svg { width:18px; height:18px; }
.mf-fact-body { min-width:0; }
.mf-fact-k { display:block; font-family:var(--mf-body); font-size:11px; font-weight:700; text-transform:uppercase;
  letter-spacing:.08em; color:var(--mf-muted); }
.mf-fact-v { font-family:var(--mf-body); font-size:14px; font-weight:600; color:var(--mf-ink); overflow-wrap:anywhere; }

/* ---------- WhatsApp order modal (single instance, every screen) ---------- */
.mf-modal { display:none; position:fixed; inset:0; z-index:1200; }
.mf-modal.open { display:block; }
.mf-modal-bg { position:absolute; inset:0; background:var(--mf-scrim); backdrop-filter:blur(2px); }
.mf-modal-card { position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); width:460px; max-width:calc(100vw - 28px);
  max-height:92vh; overflow-y:auto; background:var(--mf-card); border:1px solid var(--mf-line); border-radius:22px;
  box-shadow:0 32px 80px rgba(var(--mf-deep-ink-rgb),.3); }
@media(max-width:520px){ .mf-modal-card { left:0; right:0; top:auto; bottom:0; transform:none; width:auto; max-width:none;
  border-radius:22px 22px 0 0; max-height:88vh; } }
.mf-modal-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:20px 22px; border-bottom:1px solid var(--mf-line); }
.mf-modal-title { font-family:var(--mf-display); font-size:18px; font-weight:800; margin:0; }
.mf-modal-x { width:34px; height:34px; border-radius:50%; border:none; background:var(--mf-soft); color:var(--mf-ink);
  cursor:pointer; display:flex; align-items:center; justify-content:center; }
.mf-modal-x:hover { background:var(--mf-line); }
.mf-modal-body { padding:22px; display:flex; flex-direction:column; gap:16px; }
.mf-modal-line { display:flex; gap:12px; align-items:center; padding:12px; background:var(--mf-soft); border-radius:var(--mf-r-sm); }
.mf-modal-line img { width:52px; height:52px; border-radius:10px; object-fit:cover; background:var(--mf-card); flex-shrink:0; }
.mf-modal-line-name { font-family:var(--mf-display); font-size:14px; font-weight:700; line-height:1.3; }
.mf-modal-line-price { font-family:var(--mf-body); font-size:14px; font-weight:700; color:var(--mf-fresh-dark); margin-top:3px; }
.mf-modal-summary { max-height:220px; overflow-y:auto; display:flex; flex-direction:column; gap:10px;
  padding:14px; background:var(--mf-soft); border-radius:var(--mf-r-sm); }
.mf-modal-row { display:flex; justify-content:space-between; gap:12px; font-family:var(--mf-body); font-size:13px; line-height:1.5; }
.mf-modal-row .q { color:var(--mf-muted); flex-shrink:0; }
.mf-modal-total { display:flex; justify-content:space-between; gap:12px; padding-top:12px; margin-top:2px; border-top:1px solid var(--mf-line);
  font-family:var(--mf-display); font-size:16px; font-weight:800; }
.mf-modal-note { font-family:var(--mf-body); font-size:12px; line-height:1.55; color:var(--mf-muted); }
.mf-modal-actions { display:flex; gap:10px; }
.mf-modal-fields { display:flex; flex-direction:column; gap:14px; }

/* ---------- Mini cart drawer ---------- */
.mf-drawer { position:fixed; top:0; right:0; bottom:0; width:min(420px,100vw); background:var(--mf-card); z-index:1101;
  display:flex; flex-direction:column; box-shadow:-16px 0 48px rgba(var(--mf-deep-ink-rgb),.18);
  transform:translateX(102%); transition:transform .28s cubic-bezier(.4,0,.2,1); }
.mf-drawer.open { transform:none; }
.mf-drawer-bg { position:fixed; inset:0; background:var(--mf-scrim-soft); z-index:1100; opacity:0; visibility:hidden; transition:opacity .24s,visibility .24s; }
.mf-drawer-bg.open { opacity:1; visibility:visible; }
.mf-drawer-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:18px 20px; border-bottom:1px solid var(--mf-line); }
.mf-drawer-body { flex:1; overflow-y:auto; padding:16px 20px; display:flex; flex-direction:column; gap:12px; }
.mf-drawer-foot { padding:16px 20px calc(16px + env(safe-area-inset-bottom)); border-top:1px solid var(--mf-line); display:flex; flex-direction:column; gap:10px; background:var(--mf-card); }
.mf-drawer-line { display:flex; gap:12px; align-items:center; }
.mf-drawer-line img { width:56px; height:56px; border-radius:10px; object-fit:cover; background:var(--mf-soft); flex-shrink:0; }
.mf-drawer-line-info { flex:1; min-width:0; }
.mf-drawer-line-name { font-family:var(--mf-display); font-size:14px; font-weight:600; line-height:1.3;
  display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.mf-drawer-line-meta { font-family:var(--mf-body); font-size:12px; color:var(--mf-muted); margin-top:3px; }

/* ---------- Hero ---------- */
.mf-hero { position:relative; background:var(--mf-soft); color:var(--mf-ink); overflow:hidden; }
.mf-hero::after { content:none; }
.mf-hero-in { position:relative; z-index:2; display:grid; grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr); gap:clamp(32px,6vw,88px);
  align-items:center; padding:clamp(48px,7vw,96px) 0; }
@media(max-width:900px){ .mf-hero-in { grid-template-columns:1fr; gap:28px; padding:clamp(32px,7vw,56px) 0; } }
.mf-hero-kicker { font-family:var(--mf-body); font-size:12px; font-weight:700; letter-spacing:.16em; text-transform:uppercase;
  color:var(--mf-fresh-dark); margin-bottom:16px; }
.mf-hero-title { font-family:var(--mf-display); font-size:clamp(36px,5.2vw,64px); font-weight:750; line-height:1.04;
  letter-spacing:-.035em; margin:0 0 20px; color:var(--mf-ink); max-width:13ch; }
.mf-hero-lead { font-family:var(--mf-body); font-size:clamp(16px,1.6vw,18px); line-height:1.65; color:var(--mf-muted);
  margin:0 0 28px; max-width:46ch; }
.mf-hero-actions { display:flex; gap:12px; flex-wrap:wrap; }
.mf-hero-figure { position:relative; border-radius:var(--mf-r); overflow:hidden; aspect-ratio:5/4; background:var(--mf-card); }
.mf-hero-figure picture { display:block; position:absolute; inset:0; }
.mf-hero-figure img { display:block; width:100%; height:100%; object-fit:cover; }
@media(max-width:900px){ .mf-hero-title { max-width:16ch; font-size:clamp(34px,9vw,48px); } .mf-hero-lead { margin-bottom:22px; } }
@media(max-width:700px){ .mf-hero-figure { aspect-ratio:16/10; border-radius:var(--mf-r-sm); } }

/* ---------- Content blocks: brands, quotes, gallery, faq, hours, contact ---------- */
.mf-brands { display:flex; flex-wrap:wrap; gap:10px; justify-content:center; }
/* Named -chip so it cannot collide with the header's .mf-brand logo link, which
   this stylesheet reaches on every screen. */
.mf-brand-chip { font-family:var(--mf-display); font-size:14px; font-weight:700; color:var(--mf-ink);
  border:1px solid var(--mf-line); background:var(--mf-card); border-radius:999px; padding:10px 20px; }

.mf-quotes { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }
@media(max-width:900px){ .mf-quotes { grid-template-columns:1fr; } }
.mf-quote { background:var(--mf-card); border:1px solid var(--mf-line); border-radius:var(--mf-r); padding:24px;
  display:flex; flex-direction:column; gap:12px; }
.mf-quote-stars { color:var(--mf-accent-text); font-size:14px; letter-spacing:.14em; }
.mf-quote-text { font-family:var(--mf-body); font-size:14.5px; line-height:1.65; color:var(--mf-ink); margin:0; }
.mf-quote-who { font-family:var(--mf-display); font-size:14px; font-weight:700; }
.mf-quote-role { font-family:var(--mf-body); font-size:12.5px; color:var(--mf-muted); }

.mf-gallery { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:10px; }
@media(max-width:900px){ .mf-gallery { grid-template-columns:repeat(3,minmax(0,1fr)); } }
@media(max-width:520px){ .mf-gallery { grid-template-columns:repeat(2,minmax(0,1fr)); } }
.mf-gallery a { display:block; position:relative; aspect-ratio:1/1; border-radius:var(--mf-r-sm); overflow:hidden; background:var(--mf-soft); }
.mf-gallery img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
.mf-gallery a:hover img { transform:scale(1.06); }

.mf-faq { display:flex; flex-direction:column; gap:10px; max-width:820px; margin:0 auto; }
.mf-faq-item { background:var(--mf-card); border:1px solid var(--mf-line); border-radius:var(--mf-r-sm); overflow:hidden; }
.mf-faq-q { width:100%; display:flex; align-items:center; justify-content:space-between; gap:16px; text-align:left;
  background:none; border:0; cursor:pointer; padding:18px 20px; font-family:var(--mf-display); font-size:15px; font-weight:700; color:var(--mf-ink); }
.mf-faq-q:hover { color:var(--mf-accent-text); }
.mf-faq-q svg { width:18px; height:18px; flex-shrink:0; transition:transform .2s ease; color:var(--mf-muted); }
.mf-faq-item.open .mf-faq-q svg { transform:rotate(180deg); color:var(--mf-accent-text); }
.mf-faq-a { display:none; padding:0 20px 18px; font-family:var(--mf-body); font-size:14px; line-height:1.7; color:var(--mf-muted); }
.mf-faq-item.open .mf-faq-a { display:block; }

.mf-info { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
@media(max-width:820px){ .mf-info { grid-template-columns:1fr; } }
.mf-info-card { background:var(--mf-card); border:1px solid var(--mf-line); border-radius:var(--mf-r); padding:24px; }
.mf-info-title { font-family:var(--mf-display); font-size:16px; font-weight:700; margin-bottom:14px; }
.mf-rows { display:flex; flex-direction:column; gap:10px; }
.mf-row { display:flex; align-items:flex-start; gap:12px; font-family:var(--mf-body); font-size:14px; color:var(--mf-muted); }
.mf-row svg { width:17px; height:17px; flex-shrink:0; margin-top:2px; color:var(--mf-fresh-dark); }
.mf-row a { color:var(--mf-ink); text-decoration:none; }
.mf-row a:hover { color:var(--mf-accent-text); text-decoration:underline; }
.mf-row-between { display:flex; align-items:baseline; justify-content:space-between; gap:16px;
  font-family:var(--mf-body); font-size:14px; padding:9px 0; border-bottom:1px dashed var(--mf-line); }
.mf-row-between:last-child { border-bottom:0; }
.mf-row-between span:first-child { color:var(--mf-muted); }
.mf-row-between span:last-child { color:var(--mf-ink); font-weight:600; text-align:right; }

.mf-news { display:flex; align-items:center; justify-content:space-between; gap:28px; flex-wrap:wrap; }
.mf-news-form { display:flex; gap:10px; flex:1 1 340px; min-width:0; }
.mf-news-form input { flex:1; min-width:0; border:1px solid rgba(255,255,255,.3); background:rgba(255,255,255,.12);
  color:#fff; border-radius:999px; padding:14px 20px; font-family:var(--mf-body); font-size:14px; }
.mf-news-form input::placeholder { color:rgba(255,255,255,.66); }
.mf-news-form input:focus { outline:2px solid #fff; outline-offset:2px; }
@media(max-width:520px){ .mf-news-form { flex-direction:column; } .mf-news-form .mf-btn { width:100%; } }

/* ---------- Element resets for the semantic tags the sections use ---------- */
.mf-hero-facts, .mf-brands, .mf-rows, .mf-steps, .mf-gallery { margin:0; padding:0; list-style:none; }
.mf-quote, .mf-hero-figure { margin:0; }
.mf-quote figcaption { display:flex; flex-direction:column; gap:2px; }
.mf-quote-who, .mf-quote-role { display:block; }
.mf-cat-name, .mf-cat-count { display:block; }
.mf-faq-a p { margin:0; }

.mf-hero-body { min-width:0; }

.mf-cat-media { position:absolute; inset:0; }
.mf-cat-media img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; transition:transform .45s ease; }
.mf-cat:hover .mf-cat-media img { transform:scale(1.06); }

.mf-gallery-item { display:block; position:relative; aspect-ratio:1/1; border-radius:var(--mf-r-sm); overflow:hidden; background:var(--mf-soft); }
.mf-gallery-item img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
.mf-gallery-item:hover img { transform:scale(1.06); }

.mf-row-ico { flex-shrink:0; display:flex; margin-top:2px; }
.mf-row-ico svg { width:17px; height:17px; color:var(--mf-fresh-dark); }

/* Numbered list on a coloured panel: no card chrome, light-on-dark. */
.mf-steps--plain { display:flex; flex-direction:column; gap:12px; }
.mf-steps--plain .mf-step { display:flex; align-items:flex-start; gap:14px; padding:16px 18px;
  background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.22); }
.mf-steps--plain .mf-step-n { flex-shrink:0; background:#fff; color:var(--mf-deep); }
.mf-steps--plain .mf-step-text { margin:0; color:#fff; opacity:.94; }

/* =====================================================================
   Shared platform surfaces — cart, checkout, footer, nav, modal, toast.
   The token bridge above carries most of it; these rules cover the parts
   where the shared stylesheet hardcodes a colour or a shape.
   ===================================================================== */
.theme-market_fresh .mp-footer { background:var(--mp-footer-bg); color:var(--mp-footer-text); padding:56px 24px 24px; font-family:var(--mf-body); }
.theme-market_fresh .mp-footer-inner { max-width:1360px; gap:36px; }
.theme-market_fresh .mp-footer-brand { font-family:var(--mf-display); font-size:19px; letter-spacing:-.01em; }
.theme-market_fresh .mp-footer-heading { font-family:var(--mf-display); color:var(--mf-on-footer); font-size:14px; text-transform:uppercase; letter-spacing:.1em; }
.theme-market_fresh .mp-footer-links a, .theme-market_fresh .mp-footer-desc, .theme-market_fresh .mp-footer-contact-item { color:var(--mp-footer-text); }
.theme-market_fresh .mp-footer-links a:hover { color:var(--mf-on-footer); }
.theme-market_fresh .mp-footer-social a { background:var(--mf-footer-veil); color:var(--mf-on-footer); }
.theme-market_fresh .mp-footer-social a:hover { background:var(--mf-accent); color:var(--mf-on-accent); }
.theme-market_fresh .mp-footer-bottom { border-top:1px solid var(--mf-footer-rule); color:var(--mp-footer-text); font-size:13px; }

.theme-market_fresh .mp-mobile-nav { background:var(--mf-card); border-top:1px solid var(--mf-line); box-shadow:0 -2px 16px rgba(var(--mf-deep-ink-rgb),.06); }
.theme-market_fresh .mp-mobile-nav-item { font-family:var(--mf-body); font-weight:500; color:var(--mf-muted); }
.theme-market_fresh .mp-mobile-nav-item.active { color:var(--mf-accent-text); }

/* The sticky cart bar and the bottom nav both sit at bottom:0; the nav wins
   on z-index and hid the bar. Lift the bar clear of it. */
@media(max-width:767px){
  .theme-market_fresh .mp-sticky-cart { bottom:64px; border-top:1px solid var(--mf-line); border-radius:16px 16px 0 0; }
  .theme-market_fresh .mp-sticky-wa { bottom:132px; }
}
.theme-market_fresh .mp-sticky-cart { background:var(--mf-card); }
.theme-market_fresh .mp-sticky-cart-items { font-family:var(--mf-body); }
.theme-market_fresh .mp-sticky-cart-total { font-family:var(--mf-display); color:var(--mf-ink); }
.theme-market_fresh .mp-sticky-cart-btn { background:var(--mf-accent); border-radius:999px; font-family:var(--mf-body); }
.theme-market_fresh .mp-sticky-cart-btn:hover { background:var(--mf-accent-dark); }
.theme-market_fresh .mp-sticky-wa { box-shadow:0 8px 24px rgba(37,211,102,.34); }
.theme-market_fresh .mp-backtop { background:var(--mf-deep); color:#fff; border-radius:999px; }

/* Cart + checkout */
.theme-market_fresh .mp-cart-layout, .theme-market_fresh .mp-section { font-family:var(--mf-body); }
.theme-market_fresh .mp-items-card, .theme-market_fresh .mp-cart-aside, .theme-market_fresh .mp-checkout-card,
.theme-market_fresh .mp-order-summary, .theme-market_fresh .mp-cart-upsells {
  background:var(--mf-card); border:1px solid var(--mf-line); border-radius:var(--mf-r); box-shadow:none; }
.theme-market_fresh .mp-cart-row, .theme-market_fresh .mp-item { border-bottom:1px solid var(--mf-line); }
.theme-market_fresh .mp-item-name, .theme-market_fresh .mp-cart-total, .theme-market_fresh .mp-order-summary-title { font-family:var(--mf-display); }
.theme-market_fresh .mp-item-img { border-radius:var(--mf-r-sm); object-fit:cover; background:var(--mf-soft); }
.theme-market_fresh .mp-item-meta, .theme-market_fresh .mp-cart-hint, .theme-market_fresh .mp-quote-note { color:var(--mf-muted); }
.theme-market_fresh .mp-cart-input { border:1px solid var(--mf-line); border-radius:999px; font-family:var(--mf-body); }
.theme-market_fresh .mp-item-remove { color:var(--mf-muted); }
.theme-market_fresh .mp-item-remove:hover { color:var(--mf-danger); }
.theme-market_fresh .mp-cart-checkout, .theme-market_fresh .mp-success-btn, .theme-market_fresh .mp-modal-add {
  background:var(--mf-accent); color:var(--mf-on-accent); border-radius:999px; font-family:var(--mf-body); font-weight:600; }
.theme-market_fresh .mp-cart-checkout:hover, .theme-market_fresh .mp-success-btn:hover, .theme-market_fresh .mp-modal-add:hover {
  background:var(--mf-accent-dark); color:var(--mf-on-accent-dark); }
.theme-market_fresh .mp-continue-shopping { color:var(--mf-accent-text); font-family:var(--mf-body); font-weight:600; text-decoration:none; }
.theme-market_fresh .mp-payment-option { border:1px solid var(--mf-line); border-radius:var(--mf-r-sm); background:var(--mf-card); }
.theme-market_fresh .mp-payment-option.selected, .theme-market_fresh .mp-payment-option:has(input:checked) { border-color:var(--mf-accent); background:rgba(var(--mf-accent-rgb),.05); }
.theme-market_fresh .mp-pay-label, .theme-market_fresh .mp-cart-label { font-family:var(--mf-body); }
/* Three classes: the platform rule is .mp-checkout-card-title .mp-step-num and
   its stylesheet is emitted after the theme header, so a tie would go to it. */
.theme-market_fresh .mp-checkout-card-title .mp-step-num { background:var(--mf-deep); color:#fff; }
.theme-market_fresh .mp-stepper { font-family:var(--mf-body); }
.theme-market_fresh .mp-summary-item { border-bottom:1px solid var(--mf-line); }
.theme-market_fresh .mp-coupon-box input, .theme-market_fresh .mp-cart-input, .theme-market_fresh .mp-checkout-fields input,
.theme-market_fresh .mp-checkout-fields textarea, .theme-market_fresh .mp-checkout-fields select { border-radius:var(--mf-r-sm); }
.theme-market_fresh .mp-order-success-card { background:var(--mf-card); border:1px solid var(--mf-line); border-radius:var(--mf-r); }
.theme-market_fresh .mp-success-icon-wrap { background:rgba(var(--mf-fresh-rgb),.12); color:var(--mf-fresh-dark); }
.theme-market_fresh .mp-success-title { font-family:var(--mf-display); }
.theme-market_fresh .mp-empty { background:var(--mf-card); border:1px dashed var(--mf-line); border-radius:var(--mf-r); }
.theme-market_fresh .mp-citysel-btn { border-radius:999px; }
.theme-market_fresh .mp-citysel-opt:hover { background:var(--mf-soft); }

/* Shared quick-view modal + toast */
.theme-market_fresh .mp-modal-overlay { background:var(--mf-scrim); backdrop-filter:blur(2px); }
.theme-market_fresh .mp-modal { background:var(--mf-card); border:1px solid var(--mf-line); border-radius:22px; box-shadow:0 32px 80px rgba(var(--mf-deep-ink-rgb),.3); }
.theme-market_fresh .mp-modal-title { font-family:var(--mf-display); }
.theme-market_fresh .mp-modal-img { border-radius:var(--mf-r-sm); object-fit:cover; background:var(--mf-soft); }
.theme-market_fresh .mp-modal-qty button { background:var(--mf-soft); border-radius:999px; }
.theme-market_fresh .mp-modal-close { color:var(--mf-muted); }
.theme-market_fresh .mp-toast { background:var(--mf-deep); color:#fff; border-radius:999px; font-family:var(--mf-body); }

/* Shared variant picker + restock + reviews, so the detail page stays coherent */
.theme-market_fresh .sf-variant-chip { border:1px solid var(--mf-line); border-radius:999px; background:var(--mf-card);
  font-family:var(--mf-body); font-size:13px; font-weight:600; color:var(--mf-ink); padding:9px 16px; cursor:pointer; transition:all .18s; }
.theme-market_fresh .sf-variant-chip:hover { border-color:var(--mf-accent); color:var(--mf-accent-text); }
.theme-market_fresh .sf-variant-chip.active { background:var(--mf-deep); border-color:var(--mf-deep); color:#fff; }
.theme-market_fresh .mp-product-addon, .theme-market_fresh .sf-restock { border:1px solid var(--mf-line); border-radius:var(--mf-r-sm); background:var(--mf-card); }
.theme-market_fresh .mp-review-card, .theme-market_fresh .mp-product-review { border:1px solid var(--mf-line); border-radius:var(--mf-r-sm); background:var(--mf-card); }
/* The reviews partial brings its own display face, an 82px section pad and a
   1320px measure. Adopt this theme's type scale and wrap so the detail page
   reads as one design system rather than two stitched together. */
.theme-market_fresh .fx-review-section { max-width:1360px; padding:clamp(40px,6vw,72px) clamp(16px,3vw,32px); }
.theme-market_fresh .fx-review-section .mp-product-reviews-head h2,
.theme-market_fresh .fx-review-section .mp-product-reviews-score strong,
.theme-market_fresh .fx-review-section .mp-product-review-meta strong,
.theme-market_fresh .fx-review-section .mp-product-review h3,
.theme-market_fresh .fx-review-section .mp-product-review-form h3 { font-family:var(--mf-display); letter-spacing:-.02em; }
.theme-market_fresh .fx-review-section .mp-product-reviews-head h2 { font-size:clamp(26px,3.4vw,34px); font-weight:700; line-height:1.15; }
.theme-market_fresh .fx-review-section .mp-product-reviews-score strong { font-size:26px; font-weight:700; }
.theme-market_fresh .fx-review-section .mp-product-review h3,
.theme-market_fresh .fx-review-section .mp-product-review-form h3 { font-size:20px; font-weight:700; }
.theme-market_fresh .fx-review-section .mp-product-review-meta strong { font-size:17px; font-weight:700; }
.theme-market_fresh .fx-review-section .mp-product-review-form { border-top-color:var(--mf-line); }
.theme-market_fresh .fx-review-section .mp-product-review-form button { border-radius:999px; background:var(--mf-accent); color:var(--mf-on-accent); font-family:var(--mf-body); }
.theme-market_fresh .fx-review-section .mp-product-review-form button:hover { background:var(--mf-accent-dark); color:var(--mf-on-accent-dark); }

/* Shared services screen — platform markup, dressed in this theme. The button
   keeps the merchant's own button colour; only its foreground is derived. */
.theme-market_fresh .mp-service-card-v { border:1px solid var(--mf-line); border-radius:var(--mf-r); background:var(--mf-card); }
.theme-market_fresh .mp-service-img-wrap, .theme-market_fresh .mp-card-img-wrap { background:var(--mf-art-bg); }
.theme-market_fresh .mp-card-placeholder { color:var(--mf-art-ink); }
.theme-market_fresh .mp-service-btn { background:var(--mp-button); color:var(--mf-on-button); border-radius:999px; font-family:var(--mf-body); }
.theme-market_fresh .mp-service-btn:hover { background:var(--mp-button-dark); color:var(--mf-on-button); }

/* Shared category navigation strip */
.theme-market_fresh .sf-category-navigation { background:var(--mf-card); border-bottom:1px solid var(--mf-line); }

@media(max-width:767px){
  .theme-market_fresh .mp-footer { padding:40px 18px 20px; }
  .theme-market_fresh .mp-footer-inner { grid-template-columns:1fr; gap:26px; }
  /* The partial hardcodes a cream card here; keep it on this theme's surface. */
  .theme-market_fresh .fx-review-section .mp-product-review-form { background:var(--mf-card); }
}
@media print { .mf-drawer,.mf-drawer-bg,.mf-modal { display:none !important; } }
</style>
