<?php
/**
 * PrintWorks — printing storefront.
 *
 * Design intent (per brief):
 *   - Restrained, modern, confident. NOT a colour riot.
 *   - ONE accent colour that blends with the store's branding. Everything else
 *     is ink, paper and grey, so the theme never fights a logo.
 *   - Single-row header, few menu items.
 *   - NO blog, NO pricing tables, NO team grid — agency tropes, not print-shop.
 *   - Services-first; products appear only when catalogue mode allows.
 *
 * Colours come from the store's saved Appearance settings so branding leads.
 */
$slug = $settings->store_slug ?? '';
$wa   = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
$svcs = $featured_services ?? [];

/**
 * Accent resolution goes through Theme_engine, not `$settings->primary_color`.
 *
 * Reading the raw column meant this theme always painted the store's stored
 * colour — which is seeded to '#3B82F6' on every settings row, so a PrintWorks
 * shop that never touched Appearance came up retail-blue instead of its own
 * designed accent. resolveBrandColor() gives a deliberate merchant override
 * priority while letting the theme's design govern otherwise.
 */
$theEngine = $this->theme_engine ?? null;
$accent = $theEngine ? $theEngine->resolveBrandColor('primary') : '#0E7490';

/**
 * The hero and closing band need a calm, DARK ink.
 *
 * Deriving that from the store's secondary colour was a mistake: darkening a
 * warm brand colour (amber, orange, red) yields mud — a brown hero that looks
 * dirty no matter what accent sits on it. The ink is therefore a fixed neutral
 * near-black; branding shows through the ACCENT instead, which is the one
 * colour the eye should land on.
 */
$ink = '#0D1B24';

/** Validate a merchant colour, falling back when it is not a usable hex. */
$pw_hex = function ($hex, $fallback) {
    $hex = ltrim(trim((string)$hex), '#');
    if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    return preg_match('/^[0-9a-fA-F]{6}$/', $hex) ? '#' . $hex : $fallback;
};
$accent = $pw_hex($accent, '#0E7490');

/**
 * Light wash of the accent, for icon tiles and hover fills.
 *
 * Mixing straight towards white at $amount (0-1) keeps the hue recognisable
 * while staying pale enough to read dark text and a full-strength icon on top.
 * A high mix is used because merchant accents are often vivid.
 */
$pw_tint = function ($hex, $amount = 0.88) use ($pw_hex) {
    $hex = ltrim($pw_hex($hex, '#0E7490'), '#');
    $mix = function ($channel) use ($amount) {
        return (int)round($channel + (255 - $channel) * $amount);
    };
    return sprintf('#%02X%02X%02X',
        $mix(hexdec(substr($hex, 0, 2))),
        $mix(hexdec(substr($hex, 2, 2))),
        $mix(hexdec(substr($hex, 4, 2)))
    );
};
$tint = $pw_tint($accent);

$mode   = $catalogue_mode ?? 'services';
$enq    = base_url('store/' . $slug . '/contact');
$svc_url = base_url('store/' . $slug . '/services');
?>
<style>
/* ==========================================================================
   PrintWorks — one accent from the store's branding, everything else is
   ink / paper / grey so the theme never fights a logo.
   ========================================================================== */
.pw{--pw-accent:<?= htmlspecialchars($accent) ?>;--pw-ink:<?= htmlspecialchars($ink) ?>;
  --pw-line:#e5eaee;--pw-muted:#5f7380;--pw-soft:#f5f8f9;--pw-card:#fff;
  --pw-radius:14px;--pw-shadow:0 1px 2px rgba(16,42,53,.05),0 8px 24px -12px rgba(16,42,53,.14);
  max-width:1160px;margin:0 auto;padding:0 20px;color:var(--pw-ink);
  font-size:15px;line-height:1.6;-webkit-font-smoothing:antialiased}
.pw *{box-sizing:border-box}
.pw img{max-width:100%;display:block}

/* ---------- hero: a deep neutral anchor, one accent hairline ---------- */
.pw-hero{position:relative;overflow:hidden;border-radius:22px;margin-top:22px;
  background:var(--pw-ink);color:#fff;padding:56px 52px 0}
.pw-hero-inner{position:relative;display:grid;grid-template-columns:1.15fr .85fr;gap:44px;align-items:center}
.pw-kicker{display:inline-flex;align-items:center;gap:9px;font-size:11px;font-weight:800;
  letter-spacing:.16em;text-transform:uppercase;color:#fff;opacity:.82;margin-bottom:16px}
.pw-kicker:before{content:"";width:24px;height:2px;background:currentColor;display:block;opacity:.7}
.pw-hero h1{font-size:clamp(30px,4.4vw,50px);line-height:1.06;font-weight:800;
  letter-spacing:-.03em;margin:0 0 18px;color:#fff;text-wrap:balance}
.pw-hero-lede{font-size:16.5px;line-height:1.7;color:rgba(255,255,255,.8);margin:0 0 30px;max-width:540px}
.pw-actions{display:flex;gap:12px;flex-wrap:wrap}
.pw-btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:14px 26px;
  border-radius:10px;font-weight:700;font-size:14.5px;text-decoration:none;border:1px solid transparent;
  min-height:50px;transition:transform .12s ease,background .15s ease,border-color .15s ease,color .15s ease;
  cursor:pointer;white-space:nowrap}
.pw-btn:active{transform:translateY(1px)}
.pw-btn.solid{background:var(--pw-accent);color:#fff}
.pw-btn.solid:hover{filter:brightness(1.08)}
.pw-btn.ghost{background:rgba(255,255,255,.1);color:#fff;border-color:rgba(255,255,255,.28)}
.pw-btn.ghost:hover{background:rgba(255,255,255,.18);border-color:rgba(255,255,255,.5)}
.pw-btn.outline{background:#fff;color:var(--pw-ink);border-color:var(--pw-line)}
.pw-btn.outline:hover{border-color:var(--pw-accent);color:var(--pw-accent)}

/* Hero aside: quiet, honest facts — no invented metrics */
.pw-aside{border-radius:16px;background:rgba(255,255,255,.07);
  border:1px solid rgba(255,255,255,.16);backdrop-filter:blur(6px);overflow:hidden}
.pw-aside-top{padding:15px 20px;border-bottom:1px solid rgba(255,255,255,.14);
  font-size:11px;font-weight:800;letter-spacing:.13em;text-transform:uppercase;color:rgba(255,255,255,.7)}
.pw-aside-row{display:flex;justify-content:space-between;align-items:baseline;gap:16px;
  padding:14px 20px;border-bottom:1px solid rgba(255,255,255,.1)}
.pw-aside-row:last-child{border-bottom:none}
.pw-aside-row span{font-size:13.5px;color:rgba(255,255,255,.72)}
.pw-aside-row b{font-size:14px;font-weight:700;color:#fff;text-align:right}

/* Facts strip pinned to the bottom of the hero — real service counts */
.pw-facts{position:relative;display:grid;grid-template-columns:repeat(4,1fr);gap:0;
  margin-top:44px;border-top:1px solid rgba(255,255,255,.16)}
.pw-fact{padding:20px 22px 24px;border-right:1px solid rgba(255,255,255,.12)}
.pw-fact:last-child{border-right:none}
.pw-fact b{display:block;font-size:22px;font-weight:800;letter-spacing:-.02em;color:#fff;line-height:1.15}
.pw-fact span{display:block;font-size:12.5px;color:rgba(255,255,255,.68);margin-top:5px}

/* ---------- section rhythm ---------- */
.pw-sec{padding:64px 0 0}
.pw-sec-head{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;
  margin-bottom:30px;flex-wrap:wrap}
.pw-eyebrow{font-size:11px;font-weight:800;letter-spacing:.15em;text-transform:uppercase;
  color:var(--pw-accent);margin-bottom:10px}
.pw-sec-head h2{font-size:clamp(23px,2.6vw,32px);font-weight:800;letter-spacing:-.025em;
  margin:0;color:var(--pw-ink);line-height:1.15;text-wrap:balance}
.pw-sec-sub{font-size:15px;color:var(--pw-muted);margin:10px 0 0;max-width:600px;line-height:1.65}
.pw-more{display:inline-flex;align-items:center;gap:7px;font-size:14px;font-weight:700;
  color:var(--pw-accent);text-decoration:none;padding:10px 0}
.pw-more:hover{gap:11px}
.pw-more svg{transition:transform .15s ease}

/* ---------- services: real cards, not bare rows ---------- */
.pw-svcs{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
.pw-svc{display:flex;flex-direction:column;gap:14px;padding:24px;border-radius:var(--pw-radius);
  border:1px solid var(--pw-line);background:var(--pw-card);text-decoration:none;color:inherit;
  transition:border-color .15s ease,box-shadow .18s ease,transform .18s ease;height:100%}
.pw-svc:hover{border-color:var(--pw-accent);box-shadow:var(--pw-shadow);transform:translateY(-2px)}
.pw-svc-body{flex:1;display:flex;flex-direction:column;min-width:0}
.pw-svc-name{font-size:16.5px;font-weight:750;letter-spacing:-.012em;margin-bottom:7px;color:var(--pw-ink)}
.pw-svc:hover .pw-svc-name{color:var(--pw-accent)}
.pw-svc-desc{font-size:13.5px;line-height:1.65;color:var(--pw-muted);margin-bottom:16px;flex:1}
.pw-svc-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;
  padding-top:14px;border-top:1px solid var(--pw-soft)}
.pw-svc-meta{font-size:12.5px;color:var(--pw-muted);font-weight:650}
.pw-svc-price{font-size:13px;font-weight:800;color:var(--pw-accent)}

/* ---------- process: connected numbered rail ---------- */
.pw-steps{display:grid;grid-template-columns:repeat(4,1fr);gap:0;position:relative}
.pw-step{padding:0 26px 0 0;position:relative}
.pw-step:last-child{padding-right:0}
.pw-step-n{width:38px;height:38px;border-radius:50%;background:var(--pw-ink);color:#fff;
  display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;
  margin-bottom:16px;position:relative;z-index:1}
.pw-step:not(:last-child):after{content:"";position:absolute;top:19px;left:44px;right:14px;
  height:1px;background:var(--pw-line)}
.pw-step b{display:block;font-size:15.5px;font-weight:750;margin-bottom:7px;color:var(--pw-ink)}
.pw-step span{font-size:13.5px;line-height:1.65;color:var(--pw-muted);display:block}

/* ---------- products (only when the store sells them) ---------- */
.pw-prods{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:18px}
.pw-prod{border:1px solid var(--pw-line);border-radius:var(--pw-radius);overflow:hidden;
  text-decoration:none;color:inherit;background:var(--pw-card);display:flex;flex-direction:column;
  transition:border-color .15s ease,box-shadow .18s ease,transform .18s ease}
.pw-prod:hover{border-color:var(--pw-accent);box-shadow:var(--pw-shadow);transform:translateY(-2px)}
.pw-prod-img{aspect-ratio:4/3;background:var(--pw-soft);display:flex;align-items:center;
  justify-content:center;color:#a9b7c0;overflow:hidden}
.pw-prod-img img{width:100%;height:100%;object-fit:cover}
.pw-prod-body{padding:15px 17px 17px;flex:1;display:flex;flex-direction:column;gap:6px}
.pw-prod-name{font-size:14px;font-weight:700;line-height:1.45;color:var(--pw-ink)}
.pw-prod-price{font-size:15px;color:var(--pw-accent);font-weight:800;margin-top:auto}

/* ---------- enquiry: context beside the form ---------- */
.pw-enq{display:grid;grid-template-columns:.85fr 1.15fr;gap:44px;align-items:start;
  border:1px solid var(--pw-line);border-radius:20px;background:var(--pw-soft);padding:40px 40px 44px}
.pw-enq h2{font-size:clamp(22px,2.4vw,29px);font-weight:800;letter-spacing:-.025em;margin:0 0 14px;line-height:1.18}
.pw-enq p{font-size:14.5px;line-height:1.7;color:var(--pw-muted);margin:0 0 22px}
.pw-enq-list{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:13px}
.pw-enq-list li{display:flex;gap:11px;font-size:14px;color:var(--pw-ink);line-height:1.55}
.pw-enq-list svg{flex:0 0 auto;margin-top:2px;color:var(--pw-accent)}
.pw-enq-panel{background:#fff;border:1px solid var(--pw-line);border-radius:16px;
  padding:26px;box-shadow:var(--pw-shadow);
  /* Hand the enquiry form our accent so it blends instead of imposing teal. */
  --sq-accent:var(--pw-accent);--sq-tint:<?= htmlspecialchars($tint) ?>}
.pw-wa{display:inline-flex;align-items:center;gap:9px;margin-top:18px;font-size:14px;
  font-weight:700;color:#128C7E;text-decoration:none;padding:10px 0}

/* ---------- closing band: same neutral treatment as the hero ---------- */
.pw-band{margin-top:56px;border-radius:20px;background:var(--pw-ink);color:#fff;
  padding:38px 40px;display:flex;gap:26px;align-items:center;justify-content:space-between;flex-wrap:wrap;
  position:relative;overflow:hidden}
.pw-band:before{content:"";position:absolute;left:0;top:0;bottom:0;width:3px;
  background:linear-gradient(180deg,var(--pw-accent) 0%,var(--pw-accent) 34%,transparent 34%)}
.pw-band>*{position:relative}
.pw-band h3{margin:0 0 8px;font-size:clamp(19px,2.2vw,25px);font-weight:800;letter-spacing:-.02em;color:#fff}
.pw-band p{margin:0;font-size:14.5px;color:rgba(255,255,255,.78);max-width:520px;line-height:1.6}

.pw-foot-note{padding:44px 0 12px;text-align:center;font-size:12.5px;color:#93a3ad}

/* ============================ responsive ============================== */
@media (max-width:1024px){
  .pw-hero{padding:44px 36px 0}
  .pw-hero-inner{grid-template-columns:1fr;gap:34px}
  .pw-aside{max-width:520px}
  .pw-svcs{grid-template-columns:repeat(2,1fr)}
  .pw-enq{grid-template-columns:1fr;gap:30px;padding:32px}
}
@media (max-width:820px){
  .pw-steps{grid-template-columns:1fr 1fr;gap:28px 0}
  .pw-step{padding-right:20px}
  .pw-step:nth-child(2n):after{display:none}
  .pw-step:nth-child(2n){padding-right:0}
}
@media (max-width:640px){
  .pw{padding:0 16px;font-size:14.5px}
  .pw-hero{padding:34px 22px 0;border-radius:18px;margin-top:14px}
  .pw-hero-lede{font-size:15px}
  .pw-facts{grid-template-columns:1fr 1fr;margin-top:34px}
  .pw-fact{padding:16px 0;border-right:none;border-bottom:1px solid rgba(255,255,255,.12)}
  .pw-fact:nth-child(odd){padding-right:16px;border-right:1px solid rgba(255,255,255,.12)}
  .pw-fact b{font-size:19px}
  .pw-sec{padding-top:46px}
  .pw-svcs{grid-template-columns:1fr}
  .pw-svc{padding:20px}
  .pw-steps{grid-template-columns:1fr}
  .pw-step{padding-right:0}
  .pw-step:after{display:none}
  .pw-actions{flex-direction:column;align-items:stretch}
  .pw-btn{width:100%}
  .pw-enq{padding:24px 20px;border-radius:16px}
  .pw-enq-panel{padding:20px}
  .pw-band{padding:28px 22px;border-radius:16px;margin-top:40px}
  .pw-prods{grid-template-columns:repeat(2,1fr);gap:13px}
  .pw-more{display:flex;align-items:center;justify-content:flex-end;min-height:44px;width:100%}
}
</style>

<?php
/**
 * Resolve an enabled section key to its shared partial, or null.
 * Retail-only blocks are dropped outright: on a print shop they are not just
 * redundant, they are wrong — trust_badges defaults to "100% authentic
 * products / Quick shipping nationwide", featured_categories lists apparel
 * categories, and best_sellers / new_arrivals / brands are merchandising a
 * service business does not have.
 */
$pw_skip_sections = [
    'hero_banner', 'featured_categories', 'featured_services', 'featured_products',
    'best_sellers', 'new_arrivals', 'brands', 'trust_badges', 'promo_banner',
    'newsletter',
];
$pw_section_map = [
    'testimonials'      => 'testimonials',
    'instagram_gallery' => 'instagram',
    'store_info'        => 'store_info',
    'store_hours'       => 'store_hours',
    'faqs'              => 'faqs',
];
$pw_partial_path = function ($base) use ($homepage_sections, $pw_skip_sections, $pw_section_map) {
    if (!isset($pw_section_map[$base])) return null;
    foreach ($homepage_sections as $key => $section) {
        if (!$section->is_enabled) continue;
        if (preg_replace('/_\d+$/', '', $key) !== $base) continue;
        $partial = APPPATH . 'views/themes/shared/sections/' . $pw_section_map[$base] . '.php';
        return file_exists($partial) ? $partial : null;
    }
    return null;
};

/**
 * Emit the named optional sections, in the given order.
 *
 * The shared partials read view variables ($faqs, $products, $settings…), so
 * the caller's scope is passed in and restored before each include — a closure
 * alone would see only its own `use`d variables and every section would render
 * empty. Sections that produce no output are skipped entirely rather than
 * leaving a band of dead space.
 */
$pw_emit_sections = function (array $bases, array $scope) use ($pw_partial_path) {
    foreach ($bases as $base) {
        $partial = $pw_partial_path($base);
        if (!$partial) continue;
        extract($scope, EXTR_SKIP);
        ob_start();
        include($partial);
        $html = trim(ob_get_clean());
        if ($html !== '') echo '<section class="pw-sec pw-extra">' . $html . '</section>';
    }
};
?>

<div class="pw">

  <!-- Hero: dark anchor, real store facts, honest claims only -->
  <section class="pw-hero">
    <div class="pw-hero-inner">
      <div>
        <div class="pw-kicker"><?= $mode === 'both' ? 'Print &amp; Supplies' : 'Print Studio' ?></div>
        <h1><?= htmlspecialchars($settings->store_headline ?: 'Print, produced properly.') ?></h1>
        <p class="pw-hero-lede"><?= htmlspecialchars($settings->store_subheadline ?: 'Large format, digital print, apparel and finishing. Tell us what you need and we will confirm the specification, price and turnaround.') ?></p>
        <div class="pw-actions">
          <a class="pw-btn solid" href="<?= $enq ?>">Request a quote</a>
          <?php if (!empty($svcs) && !empty($sells_services)): ?>
          <a class="pw-btn ghost" href="<?= $svc_url ?>">See our services</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="pw-aside">
        <div class="pw-aside-top">How we work</div>
        <div class="pw-aside-row"><span>Turnaround</span><b>Confirmed with your quote</b></div>
        <div class="pw-aside-row"><span>Minimum order</span><b>1 piece</b></div>
        <div class="pw-aside-row"><span>Artwork check</span><b>Included</b></div>
        <div class="pw-aside-row"><span>Handover</span><b>Collection or delivery</b></div>
      </div>
    </div>
    <div class="pw-facts">
      <div class="pw-fact">
        <b><?= count($svcs) > 0 ? count($svcs) : '—' ?></b>
        <span>Services available</span>
      </div>
      <div class="pw-fact">
        <b><?= !empty($settings->business_hours) ? 'Open' : 'By appointment' ?></b>
        <span><?= !empty($settings->business_hours) ? htmlspecialchars(mb_substr(trim(preg_replace('/\s+/', ' ', $settings->business_hours)), 0, 34)) : 'Contact us to book a slot' ?></span>
      </div>
      <div class="pw-fact">
        <b>Quoted</b>
        <span>Written spec before we print</span>
      </div>
      <div class="pw-fact">
        <b><?= $wa !== '' ? 'WhatsApp' : 'Enquiry' ?></b>
        <span><?= $wa !== '' ? 'Send your brief directly' : 'Send us your brief' ?></span>
      </div>
    </div>
  </section>

  <!-- Services -->
  <?php if (!empty($svcs) && !empty($sells_services)): ?>
  <section class="pw-sec">
    <div class="pw-sec-head">
      <div>
        <div class="pw-eyebrow">Services</div>
        <h2>What we print</h2>
        <p class="pw-sec-sub">Every job is quoted against its own specification — size, material, quantity and finish.</p>
      </div>
      <a class="pw-more" href="<?= $svc_url ?>">All services
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="14" height="14"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
    </div>
    <?= mp_print_thumb_css($tint, $accent) ?>
    <div class="pw-svcs">
      <?php foreach ($svcs as $svc):
        $price = $svc->effective_price ?? $svc->price;
        $desc = trim(preg_replace('/\s+/', ' ', (string)$svc->description));
      ?>
      <a class="pw-svc" href="<?= base_url('store/' . $slug . '/service/' . (int)$svc->id) ?>">
        <?= mp_print_service_thumb($svc, 52) ?>
        <div class="pw-svc-body">
          <div class="pw-svc-name"><?= htmlspecialchars($svc->service_name) ?></div>
          <?php if ($desc !== ''): ?>
          <div class="pw-svc-desc"><?= htmlspecialchars(mb_substr($desc, 0, 120)) ?><?= mb_strlen($desc) > 120 ? '…' : '' ?></div>
          <?php endif; ?>
          <div class="pw-svc-foot">
            <span class="pw-svc-meta"><?php if (!empty($svc->service_duration)): ?><?= htmlspecialchars($svc->service_duration) ?><?php else: ?>Quoted per job<?php endif; ?></span>
            <span class="pw-svc-price"><?php if ((float)$price > 0): ?><?= sf_currency($price, $store_currency ?? null) ?><?php else: ?>On request<?php endif; ?></span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Process -->
  <section class="pw-sec">
    <div class="pw-sec-head">
      <div>
        <div class="pw-eyebrow">Process</div>
        <h2>How it works</h2>
      </div>
    </div>
    <div class="pw-steps">
      <div class="pw-step"><div class="pw-step-n">1</div><b>Send your brief</b><span>Size, quantity, material, finish and deadline — or send existing artwork.</span></div>
      <div class="pw-step"><div class="pw-step-n">2</div><b>We quote it</b><span>A written specification and price with the turnaround stated.</span></div>
      <div class="pw-step"><div class="pw-step-n">3</div><b>You approve</b><span>Approve the proof and settle the deposit to schedule production.</span></div>
      <div class="pw-step"><div class="pw-step-n">4</div><b>We deliver</b><span>Printed, finished and quality-checked. Collect or we deliver.</span></div>
    </div>
  </section>

  <!-- Proof: customer words and past work, when the merchant has them -->
  <?php $pw_emit_sections(['testimonials', 'instagram_gallery'], get_defined_vars()); ?>

  <!-- Products: only when the store actually sells them -->
  <?php if (!empty($sells_products) && !empty($products)): ?>
  <section class="pw-sec">
    <div class="pw-sec-head">
      <div>
        <div class="pw-eyebrow"><?= $mode === 'both' ? 'Also available' : 'Shop' ?></div>
        <h2><?= $mode === 'both' ? 'Materials &amp; supplies' : 'Shop' ?></h2>
      </div>
      <a class="pw-more" href="<?= base_url('store/' . $slug . '/products') ?>">All products
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="14" height="14"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
    </div>
    <div class="pw-prods">
      <?php foreach (array_slice($products, 0, 8) as $p):
        $pp = $p->effective_price ?? $p->sales_price ?? 0;
      ?>
      <a class="pw-prod" href="<?= base_url('store/' . $slug . '/product/' . ($p->slug ?? $p->id)) ?>">
        <div class="pw-prod-img">
          <?php if (!empty($p->image)): ?>
            <img src="<?= base_url($p->image) ?>" alt="<?= htmlspecialchars($p->item_name ?? '') ?>" loading="lazy">
          <?php else: ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="34" height="34"><path d="M7 8V3h10v5"/><rect x="4" y="8" width="16" height="8" rx="2"/><path d="M7 16h10v5H7z"/></svg>
          <?php endif; ?>
        </div>
        <div class="pw-prod-body">
          <div class="pw-prod-name"><?= htmlspecialchars($p->item_name ?? '') ?></div>
          <div class="pw-prod-price"><?= sf_currency($pp, $store_currency ?? null) ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Enquiry: context beside the form, then the closing band -->
  <section class="pw-sec">
    <div class="pw-enq">
      <div>
        <div class="pw-eyebrow">Get a quote</div>
        <h2>Tell us what you need printed</h2>
        <p>Send the specification and we will come back with a written price and turnaround. No account needed.</p>
        <ul class="pw-enq-list">
          <li>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" width="16" height="16"><path d="M20 6L9 17l-5-5"/></svg>
            <span>Written specification before anything goes to press</span>
          </li>
          <li>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" width="16" height="16"><path d="M20 6L9 17l-5-5"/></svg>
            <span>Artwork checked and proofed at no extra cost</span>
          </li>
          <li>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" width="16" height="16"><path d="M20 6L9 17l-5-5"/></svg>
            <span>Turnaround confirmed with the quote, not after</span>
          </li>
        </ul>
        <?php if ($wa !== ''): ?>
        <a class="pw-wa" href="https://wa.me/<?= $wa ?>" target="_blank" rel="noopener">
          <svg viewBox="0 0 24 24" fill="currentColor" width="17" height="17"><path d="M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2zm0 18a8 8 0 01-4.1-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8 8 0 1112 20zm4.6-5.9c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.5 6.5 0 01-1.9-1.2 7.2 7.2 0 01-1.3-1.7c-.1-.2 0-.4.1-.5l.5-.6.1-.4v-.4l-.7-1.7c-.2-.4-.4-.4-.6-.4h-.5a1 1 0 00-.7.3c-.3.3-.9 1-.9 2.3s1 2.7 1.1 2.9c.1.2 1.9 3 4.6 4.1 2.3 1 2.7.8 3.2.7.5 0 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2 0-.1-.2-.2-.4-.3z"/></svg>
          Prefer WhatsApp? Send your brief directly
        </a>
        <?php endif; ?>
      </div>
      <div class="pw-enq-panel">
        <?php
          // The panel is already a card and this section already has a heading,
          // so tell the partial to drop its own so we don't nest boxes.
          $sq_embedded = true;
          $enquiry_partial = APPPATH . 'views/printing/enquiry_form.php';
          if (file_exists($enquiry_partial)) include($enquiry_partial);
          $sq_embedded = false;
        ?>
      </div>
    </div>
  </section>

  <!-- Closing band -->
  <section class="pw-band">
    <div>
      <h3>Ready when you are.</h3>
      <p>Bring the job, the deadline or just the idea — we will work out the specification together.</p>
    </div>
    <div class="pw-actions">
      <a class="pw-btn solid" href="<?= $enq ?>">Request a quote</a>
      <?php if ($wa !== ''): ?>
      <a class="pw-btn ghost" href="https://wa.me/<?= $wa ?>" target="_blank" rel="noopener">WhatsApp</a>
      <?php endif; ?>
    </div>
  </section>

  <!-- Practical detail last: where we are, when we are open, common questions -->
  <?php $pw_emit_sections(['store_info', 'store_hours', 'faqs'], get_defined_vars()); ?>

  <div class="pw-foot-note"><?= htmlspecialchars($settings->store_headline ?: 'Printing services') ?> — quoted per job, printed to specification.</div>

</div>
