<?php
/**
 * NeonPrint Works — printing home.
 *
 * High-contrast and contemporary, for DTF, apparel decoration and short-run
 * print studios that want to feel fast, current and energetic.
 */
$slug = $settings->store_slug ?? '';
$wa   = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
$quoteMsg = rawurlencode('Hi, I need a fast print quote.');
$theme_css_extra = '
<style>
:root{--mp-primary:#111827;--mp-accent:#22D3EE;}
.np-hero{background:#111827;color:#fff;border-radius:var(--mp-radius);padding:54px 30px;position:relative;overflow:hidden}
.np-hero:before{content:"";position:absolute;top:-120px;right:-120px;width:340px;height:340px;border-radius:50%;background:radial-gradient(circle,rgba(34,211,238,.35),transparent 70%)}
.np-hero:after{content:"";position:absolute;bottom:-140px;left:-80px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(34,211,238,.18),transparent 70%)}
.np-hero .inner{position:relative;z-index:1}
.np-tag{display:inline-block;border:1px solid #22D3EE;color:#22D3EE;border-radius:999px;padding:5px 14px;font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;margin-bottom:18px}
.np-hero h1{font-size:42px;line-height:1.08;font-weight:800;margin:0 0 14px;letter-spacing:-.03em;max-width:660px}
.np-hero h1 span{color:#22D3EE}
.np-hero p{font-size:16px;color:#9ca3af;max-width:520px;margin:0 0 26px;line-height:1.65}
.np-btns{display:flex;gap:12px;flex-wrap:wrap}
.np-btn{display:inline-block;padding:14px 30px;border-radius:10px;font-weight:800;text-decoration:none;font-size:14px}
.np-btn.primary{background:#22D3EE;color:#0b1220}
.np-btn.ghost{background:rgba(255,255,255,.06);color:#fff;border:1px solid rgba(255,255,255,.18)}
.np-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-top:30px;position:relative;z-index:1}
.np-stat{border:1px solid rgba(255,255,255,.12);border-radius:10px;padding:14px}
.np-stat b{display:block;font-size:20px;color:#22D3EE;font-weight:800}
.np-stat span{font-size:11.5px;color:#9ca3af}
.np-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(215px,1fr));gap:14px}
.np-card{background:var(--mp-white);border:1px solid var(--mp-border);border-radius:12px;padding:20px;transition:border-color .15s}
.np-card:hover{border-color:#22D3EE}
.np-card b{display:block;font-size:15px;margin-bottom:6px}
.np-card span{font-size:12.5px;color:var(--mp-gray);line-height:1.65}
.np-cta{background:linear-gradient(120deg,#111827,#164e63);color:#fff;border-radius:var(--mp-radius);padding:42px 30px;display:flex;gap:20px;align-items:center;justify-content:space-between;flex-wrap:wrap}
.np-cta h3{margin:0 0 6px;font-size:25px;font-weight:800;letter-spacing:-.02em}
.np-cta p{margin:0;color:#9ca3af;font-size:14px}
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
        case 'trust_badges': include(APPPATH . 'views/themes/shared/sections/trust_badges.php'); break;
        case 'testimonials': include(APPPATH . 'views/themes/shared/sections/testimonials.php'); break;
        case 'faqs': include(APPPATH . 'views/themes/shared/sections/faqs.php'); break;
        case 'contact_section': include(APPPATH . 'views/themes/shared/sections/contact.php'); break;
        case 'whatsapp_cta': include(APPPATH . 'views/themes/shared/sections/whatsapp_cta.php'); break;
        case 'store_info': include(APPPATH . 'views/themes/shared/sections/store_info.php'); break;
        case 'instagram_gallery': include(APPPATH . 'views/themes/shared/sections/instagram.php'); break;
        case 'newsletter': include(APPPATH . 'views/themes/shared/sections/newsletter.php'); break;
    }
}
?>

<div class="mp-section">
  <div class="np-hero">
    <div class="inner">
      <div class="np-tag">Same-day quotes</div>
      <h1>Print faster.<br><span>Look sharper.</span></h1>
      <p><?= htmlspecialchars($settings->store_subheadline ?: 'DTF, apparel decoration and short-run print turned around in hours — not weeks.'); ?></p>
      <div class="np-btns">
        <a class="np-btn primary" href="<?= base_url('store/' . $slug . '/contact'); ?>">Get a Quote</a>
        <a class="np-btn ghost" href="<?= base_url('store/' . $slug . '/products'); ?>">See Materials</a>
      </div>
    </div>
    <div class="np-stats">
      <div class="np-stat"><b>24h</b><span>Typical turnaround</span></div>
      <div class="np-stat"><b>1pc</b><span>Minimum order</span></div>
      <div class="np-stat"><b>Free</b><span>Artwork check</span></div>
      <div class="np-stat"><b>Full</b><span>Colour range</span></div>
    </div>
  </div>
</div>

<div class="mp-section">
  <div class="mp-section-title">What We Do</div>
  <div class="np-grid">
    <div class="np-card"><b>DTF Transfers</b><span>Full-colour transfers for cotton, poly and blends. Single pieces welcome.</span></div>
    <div class="np-card"><b>Apparel Printing</b><span>Tees, polos, hoodies and workwear — front, back and sleeve positions.</span></div>
    <div class="np-card"><b>Short-run Print</b><span>Stickers, flyers, cards and posters in small batches, quickly.</span></div>
    <div class="np-card"><b>Signage</b><span>Banners, boards and roll-ups for events and storefronts.</span></div>
  </div>
</div>

<div class="mp-section">
  <div class="mp-section-title">Turnaround</div>
  <div class="np-grid">
    <div class="np-card"><b>1 · Send the file</b><span>Drop your artwork or describe the job and quantity.</span></div>
    <div class="np-card"><b>2 · Approve the quote</b><span>We confirm price and lead time the same day.</span></div>
    <div class="np-card"><b>3 · We print</b><span>Production starts once the deposit clears.</span></div>
    <div class="np-card"><b>4 · Pick up or ship</b><span>Collect in store or we send it out to you.</span></div>
  </div>
</div>

<div class="mp-section">
  <div class="np-cta">
    <div>
      <h3>Need it today?</h3>
      <p>Message us your job and we will confirm what is possible.</p>
    </div>
    <div class="np-btns">
      <a class="np-btn primary" href="<?= base_url('store/' . $slug . '/contact'); ?>">Get a Quote</a>
      <?php if ($wa !== ''): ?>
      <a class="np-btn ghost" href="https://wa.me/<?= $wa ?>?text=<?= $quoteMsg ?>" target="_blank" rel="noopener">WhatsApp</a>
      <?php endif; ?>
    </div>
  </div>
</div>
