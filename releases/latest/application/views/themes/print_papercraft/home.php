<?php
/**
 * PaperCraft Atelier — printing home.
 *
 * Editorial, craft-led and calm. Warm paper tones and generous whitespace, for
 * stationery, packaging and bespoke finishing studios where the portfolio and
 * quality of work lead the sale.
 */
$slug = $settings->store_slug ?? '';
$wa   = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
$quoteMsg = rawurlencode('Hello, I would like to discuss a print project.');
$theme_css_extra = '
<style>
:root{--mp-primary:#7C5C3E;--mp-accent:#2F6F5F;--mp-radius:4px;--mp-radius-sm:4px;}
.pc-eyebrow{font-size:11px;letter-spacing:.2em;text-transform:uppercase;color:var(--mp-accent);font-weight:700;margin-bottom:12px}
.pc-hero{background:#faf6f1;border:1px solid #e8ded1;border-radius:6px;padding:56px 34px;text-align:center}
.pc-hero h1{font-size:40px;line-height:1.15;font-weight:400;margin:0 auto 16px;color:#2b2116;max-width:680px;letter-spacing:-.01em}
.pc-hero h1 em{font-style:italic;color:#7C5C3E}
.pc-hero p{font-size:16px;color:#6b5c4a;max-width:540px;margin:0 auto 26px;line-height:1.7}
.pc-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.pc-btn{display:inline-block;padding:13px 30px;border-radius:2px;font-weight:600;text-decoration:none;font-size:14px;letter-spacing:.02em}
.pc-btn.primary{background:#7C5C3E;color:#fff}
.pc-btn.ghost{background:transparent;color:#7C5C3E;border:1px solid #c9b8a4}
.pc-rule{width:52px;height:1px;background:#c9b8a4;margin:30px auto 0}
.pc-work{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:18px}
.pc-work-card{background:#fff;border:1px solid #e8ded1;border-radius:4px;overflow:hidden}
.pc-work-card .cap{padding:18px}
.pc-work-card .cap b{display:block;font-size:14.5px;margin-bottom:5px;color:#2b2116}
.pc-work-card .cap span{font-size:12.5px;color:#6b5c4a;line-height:1.65}
.pc-work-thumb{height:150px;background:linear-gradient(135deg,#efe6da,#e2d3c0);display:flex;align-items:center;justify-content:center;color:#a8927a;font-size:12px;letter-spacing:.1em;text-transform:uppercase}
.pc-steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:22px;text-align:center}
.pc-steps .n{font-size:22px;font-weight:400;color:#c9b8a4;margin-bottom:8px}
.pc-steps b{display:block;font-size:14px;margin-bottom:6px;color:#2b2116}
.pc-steps span{font-size:12.5px;color:#6b5c4a;line-height:1.65}
.pc-cta{background:#2b2116;color:#f5f0ea;border-radius:6px;padding:44px 30px;text-align:center}
.pc-cta h3{font-size:27px;font-weight:400;margin:0 0 10px;letter-spacing:-.01em}
.pc-cta p{color:#c3b5a4;margin:0 0 24px;font-size:14.5px}
</style>';

foreach ($homepage_sections as $key => $section) {
    if (!$section->is_enabled) continue;
    $baseKey = preg_replace('/_\d+$/', '', $key);
    switch ($baseKey) {
        case 'hero_banner': include(APPPATH . 'views/themes/shared/sections/hero.php'); break;
        case 'featured_categories': include(APPPATH . 'views/themes/shared/sections/featured_categories.php'); break;
        case 'featured_products': include(APPPATH . 'views/themes/shared/sections/featured_products.php'); break;
        case 'featured_services': include(APPPATH . 'views/themes/shared/sections/featured_services.php'); break;
        case 'promo_banner': include(APPPATH . 'views/themes/shared/sections/promo.php'); break;
        case 'testimonials': include(APPPATH . 'views/themes/shared/sections/testimonials.php'); break;
        case 'faqs': include(APPPATH . 'views/themes/shared/sections/faqs.php'); break;
        case 'contact_section': include(APPPATH . 'views/themes/shared/sections/contact.php'); break;
        case 'store_info': include(APPPATH . 'views/themes/shared/sections/store_info.php'); break;
        case 'brands': include(APPPATH . 'views/themes/shared/sections/brands.php'); break;
        case 'newsletter': include(APPPATH . 'views/themes/shared/sections/newsletter.php'); break;
    }
}
?>

<div class="mp-section">
  <div class="pc-hero">
    <div class="pc-eyebrow">Print Studio &amp; Bindery</div>
    <h1><?= htmlspecialchars($settings->store_headline ?: 'Considered print, made to be kept.'); ?></h1>
    <p><?= htmlspecialchars($settings->store_subheadline ?: 'Stationery, packaging and bespoke finishing produced with attention to paper, weight and detail.'); ?></p>
    <div class="pc-btns">
      <a class="pc-btn primary" href="<?= base_url('store/' . $slug . '/contact'); ?>">Start a Project</a>
      <a class="pc-btn ghost" href="<?= base_url('store/' . $slug . '/products'); ?>">View Materials</a>
    </div>
    <div class="pc-rule"></div>
  </div>
</div>

<div class="mp-section">
  <div class="mp-section-title">Selected Work</div>
  <div class="pc-work">
    <div class="pc-work-card"><div class="pc-work-thumb">Stationery</div><div class="cap"><b>Identity &amp; Stationery</b><span>Business cards, letterheads and compliment slips on premium uncoated stock.</span></div></div>
    <div class="pc-work-card"><div class="pc-work-thumb">Packaging</div><div class="cap"><b>Packaging &amp; Labels</b><span>Boxes, sleeves and labels that carry your brand through the unboxing.</span></div></div>
    <div class="pc-work-card"><div class="pc-work-thumb">Books</div><div class="cap"><b>Books &amp; Booklets</b><span>Saddle stitch, perfect bind and spiral for reports, menus and catalogues.</span></div></div>
    <div class="pc-work-card"><div class="pc-work-thumb">Finishing</div><div class="cap"><b>Bespoke Finishing</b><span>Foil, emboss, die-cut and edge painting applied by hand where it matters.</span></div></div>
  </div>
</div>

<div class="mp-section">
  <div class="mp-section-title">Our Process</div>
  <div class="pc-steps">
    <div><div class="n">01</div><b>Consultation</b><span>We discuss stock, quantity, finish and deadline.</span></div>
    <div><div class="n">02</div><b>Quotation</b><span>A written quote with the specification agreed in detail.</span></div>
    <div><div class="n">03</div><b>Proofing</b><span>You approve the artwork before anything is printed.</span></div>
    <div><div class="n">04</div><b>Production</b><span>Printed, finished, checked and packed by hand.</span></div>
  </div>
</div>

<div class="mp-section">
  <div class="pc-cta">
    <h3>Tell us about the project</h3>
    <p>Share your brief and we will come back with options and a price.</p>
    <div class="pc-btns">
      <a class="pc-btn primary" href="<?= base_url('store/' . $slug . '/contact'); ?>" style="background:#f5f0ea;color:#2b2116;">Start a Project</a>
      <?php if ($wa !== ''): ?>
      <a class="pc-btn ghost" href="https://wa.me/<?= $wa ?>?text=<?= $quoteMsg ?>" target="_blank" rel="noopener" style="border-color:#5b4a38;color:#f5f0ea;">WhatsApp Us</a>
      <?php endif; ?>
    </div>
  </div>
</div>
