<?php
/**
 * InkPress Studio — printing home.
 *
 * Press-room aesthetic: strong type, work-first grid, quote request up front.
 * Printing is service-based, so the primary action is "Request a quote" and the
 * secondary is the catalogue (for shops that also sell materials).
 */
$slug = $settings->store_slug ?? '';
$wa   = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
$quoteMsg = rawurlencode('Hello, I would like a quote for a print job.');
$theme_css_extra = '
<style>
:root{--mp-primary:#0E7490;--mp-accent:#F59E0B;}
.ip-eyebrow{font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--mp-accent);font-weight:800;margin-bottom:10px}
.ip-hero{background:#0E7490;color:#fff;border-radius:var(--mp-radius);padding:52px 28px;position:relative;overflow:hidden}
.ip-hero h1{font-size:38px;line-height:1.12;font-weight:800;margin:0 0 14px;letter-spacing:-.02em;max-width:640px}
.ip-hero p{font-size:16px;opacity:.9;max-width:560px;margin:0 0 24px}
.ip-btns{display:flex;gap:12px;flex-wrap:wrap}
.ip-btn{display:inline-block;padding:13px 26px;border-radius:999px;font-weight:700;text-decoration:none}
.ip-btn.primary{background:var(--mp-accent);color:#1f2937}
.ip-btn.ghost{background:rgba(255,255,255,.14);color:#fff;border:1px solid rgba(255,255,255,.4)}
.ip-cap{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-top:26px}
.ip-cap-card{background:var(--mp-white);border:1px solid var(--mp-border);border-radius:var(--mp-radius-sm);padding:20px}
.ip-cap-card .num{font-size:11px;font-weight:800;color:var(--mp-accent);letter-spacing:.1em}
.ip-cap-card h4{margin:8px 0 6px;font-size:15px;font-weight:700}
.ip-cap-card p{margin:0;font-size:13px;color:var(--mp-gray);line-height:1.6}
.ip-steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px}
.ip-step{background:var(--mp-white);border:1px solid var(--mp-border);border-left:3px solid var(--mp-primary);border-radius:var(--mp-radius-sm);padding:18px}
.ip-step b{display:block;font-size:13px;margin-bottom:5px}
.ip-step span{font-size:12.5px;color:var(--mp-gray);line-height:1.6}
.ip-cta{background:#0b3a47;color:#fff;border-radius:var(--mp-radius);padding:40px 26px;text-align:center}
.ip-cta h3{margin:0 0 8px;font-size:26px;font-weight:800}
.ip-cta p{opacity:.85;margin:0 0 22px}
.ip-spec{display:flex;gap:10px;flex-wrap:wrap;justify-content:center;margin-top:22px;font-size:12.5px;opacity:.8}
.ip-spec span{border:1px solid rgba(255,255,255,.25);border-radius:999px;padding:5px 13px}
</style>';

// Shared sections first (banners / featured), then the printing-specific blocks.
foreach ($homepage_sections as $key => $section) {
    if (!$section->is_enabled) continue;
    $baseKey = preg_replace('/_\d+$/', '', $key);
    switch ($baseKey) {
        case 'hero_banner': include(APPPATH . 'views/themes/shared/sections/hero.php'); break;
        case 'featured_categories': include(APPPATH . 'views/themes/shared/sections/featured_categories.php'); break;
        case 'featured_products': include(APPPATH . 'views/themes/shared/sections/featured_products.php'); break;
        case 'featured_services': include(APPPATH . 'views/themes/shared/sections/featured_services.php'); break;
        case 'promo_banner': include(APPPATH . 'views/themes/shared/sections/promo.php'); break;
        case 'trust_badges': include(APPPATH . 'views/themes/shared/sections/trust_badges.php'); break;
        case 'testimonials': include(APPPATH . 'views/themes/shared/sections/testimonials.php'); break;
        case 'faqs': include(APPPATH . 'views/themes/shared/sections/faqs.php'); break;
        case 'contact_section': include(APPPATH . 'views/themes/shared/sections/contact.php'); break;
        case 'whatsapp_cta': include(APPPATH . 'views/themes/shared/sections/whatsapp_cta.php'); break;
        case 'store_info': include(APPPATH . 'views/themes/shared/sections/store_info.php'); break;
        case 'brands': include(APPPATH . 'views/themes/shared/sections/brands.php'); break;
        case 'newsletter': include(APPPATH . 'views/themes/shared/sections/newsletter.php'); break;
    }
}
// Banners render via the shared hero section above; this press-studio band
// always renders so the quote-first message is never lost.
?>

<div class="mp-section">
  <div class="ip-hero">
    <div class="ip-eyebrow">Print &amp; Signage</div>
    <h1><?= htmlspecialchars($settings->store_headline ?: 'Print that gets you noticed.'); ?></h1>
    <p><?= htmlspecialchars($settings->store_subheadline ?: 'Banners, signage, business stationery, branded apparel and digital print — quoted fast, delivered on time.'); ?></p>
    <div class="ip-btns">
      <a class="ip-btn primary" href="<?= base_url('store/' . $slug . '/contact'); ?>">Request a Quote</a>
      <a class="ip-btn ghost" href="<?= base_url('store/' . $slug . '/products'); ?>">Browse Materials</a>
    </div>
    <div class="ip-spec">
      <span>Same-day quotes</span>
      <span>Free artwork check</span>
      <span>Nationwide delivery</span>
      <span>Bulk discounts</span>
    </div>
  </div>
</div>

<!-- Capabilities -->
<div class="mp-section">
  <div class="mp-section-title">What We Print</div>
  <div class="ip-cap">
    <div class="ip-cap-card"><div class="num">01</div><h4>Large Format</h4><p>Banners, billboards, vehicle wraps, roll-up stands and site signage.</p></div>
    <div class="ip-cap-card"><div class="num">02</div><h4>Digital &amp; Stationery</h4><p>Business cards, letterheads, flyers, booklets and short-run documents.</p></div>
    <div class="ip-cap-card"><div class="num">03</div><h4>Apparel &amp; DTF</h4><p>Branded tees, polos, hoodies and workwear with durable transfers.</p></div>
    <div class="ip-cap-card"><div class="num">04</div><h4>Signage &amp; Finishing</h4><p>Acrylic, PVC, mounting, lamination, hemming and eyeleting.</p></div>
  </div>
</div>

<!-- How it works -->
<div class="mp-section">
  <div class="mp-section-title">How It Works</div>
  <div class="ip-steps">
    <div class="ip-step"><b>1 · Send your brief</b><span>Tell us size, quantity, material and deadline — or upload artwork.</span></div>
    <div class="ip-step"><b>2 · Get a quote</b><span>We price it, confirm the specification and issue a formal quotation.</span></div>
    <div class="ip-step"><b>3 · Approve</b><span>Approve the artwork and pay the deposit to release the job to production.</span></div>
    <div class="ip-step"><b>4 · Collect or deliver</b><span>We print, finish, quality-check and hand over on the agreed date.</span></div>
  </div>
</div>

<!-- Quote CTA -->
<div class="mp-section">
  <div class="ip-cta">
    <h3>Have artwork ready?</h3>
    <p>Send it over and we will confirm price and turnaround the same working day.</p>
    <div class="ip-btns" style="justify-content:center;">
      <a class="ip-btn primary" href="<?= base_url('store/' . $slug . '/contact'); ?>">Request a Quote</a>
      <?php if ($wa !== ''): ?>
      <a class="ip-btn ghost" href="https://wa.me/<?= $wa ?>?text=<?= $quoteMsg ?>" target="_blank" rel="noopener">Chat on WhatsApp</a>
      <?php endif; ?>
    </div>
  </div>
</div>
