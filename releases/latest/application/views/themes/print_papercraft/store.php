<?php
/**
 * PaperCraft Atelier — printing storefront, services first.
 * Editorial, calm, craft-led. Service list is the main content.
 */
$slug = $settings->store_slug ?? '';
$wa   = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
$svcs = $featured_services ?? [];
$enq  = base_url('store/' . $slug . '/contact');
?>
<style>
:root{--mp-primary:#7C5C3E;--mp-accent:#2F6F5F;--mp-radius:6px;--mp-radius-sm:4px;}
.pca{max-width:1080px;margin:0 auto}
.pca-hero{background:#faf6f1;border:1px solid #e8ded1;border-radius:6px;padding:48px 34px;text-align:center}
.pca-hero h1{font-size:36px;line-height:1.18;font-weight:400;margin:0 auto 14px;color:#2b2116;max-width:660px}
.pca-hero p{font-size:15.5px;color:#6b5c4a;max-width:560px;margin:0 auto 24px;line-height:1.75}
.pca-rule{width:48px;height:1px;background:#c9b8a4;margin:26px auto 0}
.pca-actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.pca-btn{display:inline-block;padding:12px 28px;border-radius:2px;font-weight:600;text-decoration:none;font-size:13.5px}
.pca-btn.solid{background:#7C5C3E;color:#fff}
.pca-btn.line{background:transparent;color:#7C5C3E;border:1px solid #c9b8a4}
.pca-svc{border-top:1px solid #e8ded1}
.pca-row{display:grid;grid-template-columns:230px 1fr auto;gap:20px;align-items:baseline;padding:20px 4px;border-bottom:1px solid #e8ded1;text-decoration:none;color:inherit}
.pca-row:hover{background:#faf6f1}
.pca-row .nm{font-size:16px;color:#2b2116;font-weight:600}
.pca-row .ds{font-size:13px;color:#6b5c4a;line-height:1.7}
.pca-row .mt{font-size:11.5px;color:#a8927a;letter-spacing:.04em;text-transform:uppercase;white-space:nowrap}
.pca-steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:24px;text-align:center}
.pca-steps .n{font-size:20px;color:#c9b8a4;margin-bottom:8px}
.pca-steps b{display:block;font-size:13.5px;margin-bottom:6px;color:#2b2116}
.pca-steps span{font-size:12.5px;color:#6b5c4a;line-height:1.7}
.pca-enq{background:#2b2116;color:#f5f0ea;border-radius:6px;padding:38px 30px;text-align:center}
.pca-enq h3{font-size:24px;font-weight:400;margin:0 0 10px}
.pca-enq p{color:#c3b5a4;margin:0 0 22px;font-size:14px}
.pca-enq .pca-btn.solid{background:#f5f0ea;color:#2b2116}
@media(max-width:820px){.pca-row{grid-template-columns:1fr;gap:6px}}
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

<div class="pca">

  <div class="mp-section">
    <div class="pca-hero">
      <h1><?= htmlspecialchars($settings->store_headline ?: 'Considered print, made to be kept.'); ?></h1>
      <p><?= htmlspecialchars($settings->store_subheadline ?: 'Stationery, packaging and bespoke finishing — produced with attention to paper, weight and detail.'); ?></p>
      <div class="pca-actions">
        <a class="pca-btn solid" href="<?= $enq ?>">Start a project</a>
        <?php if (!empty($svcs)): ?>
        <a class="pca-btn line" href="<?= base_url('store/' . $slug . '/services') ?>">All services</a>
        <?php endif; ?>
      </div>
      <div class="pca-rule"></div>
    </div>
  </div>

  <?php if (!empty($svcs)): ?>
  <div class="mp-section">
    <div class="mp-section-title">Services</div>
    <?= mp_print_thumb_css() ?>
    <div class="pca-svc">
      <?php foreach ($svcs as $svc):
        $price = $svc->effective_price ?? $svc->price;
        $desc = trim(preg_replace('/\s+/', ' ', (string)$svc->description));
      ?>
      <a class="pca-row" href="<?= base_url('store/' . $slug . '/service/' . (int)$svc->id) ?>">
        <div class="nm" style="display:flex;align-items:center;gap:10px;"><?= mp_print_service_thumb($svc, 36) ?><?= htmlspecialchars($svc->service_name) ?></div>
        <div class="ds"><?= htmlspecialchars(mb_substr($desc, 0, 150)) ?><?= mb_strlen($desc) > 150 ? '…' : '' ?></div>
        <div class="mt">
          <?php if ((float)$price > 0): ?><?= sf_currency($price, $store_currency ?? null) ?>
          <?php elseif (!empty($svc->service_duration)): ?><?= htmlspecialchars($svc->service_duration) ?>
          <?php else: ?>On specification<?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="mp-section">
    <div class="mp-section-title">Our Process</div>
    <div class="pca-steps">
      <div><div class="n">01</div><b>Consultation</b><span>We discuss stock, quantity, finish and deadline.</span></div>
      <div><div class="n">02</div><b>Quotation</b><span>A written quote with the specification agreed in detail.</span></div>
      <div><div class="n">03</div><b>Proofing</b><span>You approve the artwork before anything is printed.</span></div>
      <div><div class="n">04</div><b>Production</b><span>Printed, finished, checked and packed by hand.</span></div>
    </div>
  </div>

  <div class="mp-section">
    <?php
      // A service business takes a BRIEF, not a basket. The full specification
      // form replaces a bare "contact us" link.
      $enquiry_partial = APPPATH . 'views/printing/enquiry_form.php';
      if (file_exists($enquiry_partial)) {
          include($enquiry_partial);
      } else {
          include(APPPATH . 'views/themes/shared/sections/contact.php');
      }
    ?>
  </div>

</div>
