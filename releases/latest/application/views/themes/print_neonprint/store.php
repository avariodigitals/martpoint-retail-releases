<?php
/**
 * NeonPrint Works — printing storefront, services first.
 * High-contrast and contemporary, for DTF / apparel / short-run studios.
 */
$slug = $settings->store_slug ?? '';
$wa   = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
$svcs = $featured_services ?? [];
$enq  = base_url('store/' . $slug . '/contact');
?>
<style>
:root{--mp-primary:#111827;--mp-accent:#22D3EE;}
.npn{max-width:1140px;margin:0 auto}
.npn-hero{background:#111827;color:#fff;border-radius:14px;padding:48px 32px;position:relative;overflow:hidden}
.npn-hero h1{font-size:36px;line-height:1.14;font-weight:800;margin:0 0 12px;letter-spacing:-.03em;max-width:600px;position:relative;z-index:1}
.npn-hero h1 span{color:#22D3EE}
.npn-hero p{font-size:15px;color:#9ca3af;max-width:520px;margin:0 0 22px;line-height:1.7;position:relative;z-index:1}
.npn-actions{display:flex;gap:10px;flex-wrap:wrap;position:relative;z-index:1}
.npn-btn{display:inline-block;padding:12px 24px;border-radius:9px;font-weight:800;text-decoration:none;font-size:13.5px}
.npn-btn.solid{background:#22D3EE;color:#0b1220}
.npn-btn.line{background:rgba(255,255,255,.06);color:#fff;border:1px solid rgba(255,255,255,.2)}
.npn-tiles{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:14px}
.npn-tile{background:#fff;border:1px solid #e2edf1;border-radius:12px;padding:20px;text-decoration:none;color:inherit;transition:border-color .15s,transform .15s}
.npn-tile:hover{border-color:#22D3EE;transform:translateY(-2px)}
.npn-tile .tag{display:inline-block;font-size:10.5px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#0e7490;background:#e6f4f8;border-radius:999px;padding:3px 10px;margin-bottom:10px}
.npn-tile b{display:block;font-size:15px;margin-bottom:6px;color:#0f172a}
.npn-tile span{font-size:12.5px;color:#5b7280;line-height:1.65;display:block}
.npn-tile .meta{margin-top:9px;font-size:11.5px;color:#94a3b8}
.npn-steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px}
.npn-step{background:#fff;border:1px solid #e2edf1;border-radius:12px;padding:18px}
.npn-step em{display:inline-flex;width:22px;height:22px;border-radius:50%;background:#111827;color:#22D3EE;font-style:normal;font-size:11px;font-weight:800;align-items:center;justify-content:center;margin-bottom:9px}
.npn-step b{display:block;font-size:13px;margin-bottom:5px}
.npn-step span{font-size:12.5px;color:#5b7280;line-height:1.6}
.npn-enq{background:linear-gradient(120deg,#111827,#164e63);color:#fff;border-radius:14px;padding:34px 30px;display:grid;grid-template-columns:1fr auto;gap:20px;align-items:center}
.npn-enq h3{margin:0 0 6px;font-size:22px;font-weight:800;letter-spacing:-.02em}
.npn-enq p{margin:0;color:#9ca3af;font-size:13.5px;max-width:500px;line-height:1.6}
@media(max-width:860px){.npn-enq{grid-template-columns:1fr}}
</style>

<?php foreach ($homepage_sections as $key => $section) {
    if (!$section->is_enabled) continue;
    $baseKey = preg_replace('/_\d+$/', '', $key);
    switch ($baseKey) {
        case 'hero_banner': include(APPPATH . 'views/themes/shared/sections/hero.php'); break;
        case 'trust_badges': include(APPPATH . 'views/themes/shared/sections/trust_badges.php'); break;
        case 'promo_banner': include(APPPATH . 'views/themes/shared/sections/promo.php'); break;
        case 'testimonials': include(APPPATH . 'views/themes/shared/sections/testimonials.php'); break;
        case 'store_info': include(APPPATH . 'views/themes/shared/sections/store_info.php'); break;
        case 'store_hours': include(APPPATH . 'views/themes/shared/sections/store_hours.php'); break;
        case 'brands': include(APPPATH . 'views/themes/shared/sections/brands.php'); break;
        case 'instagram_gallery': include(APPPATH . 'views/themes/shared/sections/instagram.php'); break;
        case 'faqs': include(APPPATH . 'views/themes/shared/sections/faqs.php'); break;
        case 'newsletter': include(APPPATH . 'views/themes/shared/sections/newsletter.php'); break;
        case 'contact_section': include(APPPATH . 'views/themes/shared/sections/contact.php'); break;
        case 'whatsapp_cta': include(APPPATH . 'views/themes/shared/sections/whatsapp_cta.php'); break;
    }
} ?>

<div class="npn">

  <div class="mp-section">
    <div class="npn-hero">
      <h1>Print faster.<br><span>Look sharper.</span></h1>
      <p><?= htmlspecialchars($settings->store_subheadline ?: 'DTF, apparel decoration and short-run print turned around in hours — not weeks.'); ?></p>
      <div class="npn-actions">
        <a class="npn-btn solid" href="<?= $enq ?>">Start a job</a>
        <?php if (!empty($svcs)): ?>
        <a class="npn-btn line" href="<?= base_url('store/' . $slug . '/services') ?>">All services</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if (!empty($svcs)): ?>
  <div class="mp-section">
    <div class="mp-section-title">What We Print</div>
    <?= mp_print_thumb_css() ?>
    <div class="npn-tiles">
      <?php foreach ($svcs as $svc):
        $price = $svc->effective_price ?? $svc->price;
        $desc = trim(preg_replace('/\s+/', ' ', (string)$svc->description));
      ?>
      <a class="npn-tile" href="<?= base_url('store/' . $slug . '/service/' . (int)$svc->id) ?>">
        <?= mp_print_service_thumb($svc, 40) ?>
        <?php if (!empty($svc->service_duration)): ?><span class="tag"><?= htmlspecialchars($svc->service_duration) ?></span><?php endif; ?>
        <b><?= htmlspecialchars($svc->service_name) ?></b>
        <span><?= htmlspecialchars(mb_substr($desc, 0, 130)) ?><?= mb_strlen($desc) > 130 ? '…' : '' ?></span>
        <div class="meta"><?php if ((float)$price > 0): ?><?= sf_currency($price, $store_currency ?? null) ?><?php else: ?>Priced on specification<?php endif; ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="mp-section">
    <div class="mp-section-title">Turnaround</div>
    <div class="npn-steps">
      <div class="npn-step"><em>1</em><b>Send the file</b><span>Drop your artwork or describe the job and quantity.</span></div>
      <div class="npn-step"><em>2</em><b>Approve the quote</b><span>We confirm price and lead time the same day.</span></div>
      <div class="npn-step"><em>3</em><b>We print</b><span>Production starts once the deposit clears.</span></div>
      <div class="npn-step"><em>4</em><b>Pick up or ship</b><span>Collect in store or we send it out to you.</span></div>
    </div>
  </div>

  <div class="mp-section">
    <?php
      // Service business: capture a brief, never a basket.
      $enquiry_partial = APPPATH . 'views/printing/enquiry_form.php';
      if (file_exists($enquiry_partial)) {
          include($enquiry_partial);
      } else {
          include(APPPATH . 'views/themes/shared/sections/contact.php');
      }
    ?>
  </div>

</div>
