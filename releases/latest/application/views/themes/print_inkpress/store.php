<?php
/**
 * InkPress Studio — printing storefront, SERVICES FIRST.
 *
 * A printing company sells services, not retail categories. The page leads
 * with what we print, then how it works, then the work/materials. Every CTA
 * asks the customer to send a job — but the primary path is the enquiry form
 * (contact), not a heavy "request quote" banner repeated everywhere.
 *
 * Storefront::index() renders the 'store' view, so all markup lives here.
 */
$slug = $settings->store_slug ?? '';
$wa   = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
$svcs = $featured_services ?? [];
$enq  = base_url('store/' . $slug . '/contact');
?>
<style>
:root{--mp-primary:#0E7490;--mp-accent:#F59E0B;}
.ipk-wrap{max-width:1140px;margin:0 auto}
/* --- hero: calm, informative, one clear action --- */
.ipk-hero{background:linear-gradient(135deg,#0b3a47 0%,#0E7490 100%);color:#fff;border-radius:14px;padding:46px 34px;display:grid;grid-template-columns:1.15fr .85fr;gap:30px;align-items:center}
.ipk-hero h1{font-size:33px;line-height:1.16;font-weight:800;margin:0 0 12px;letter-spacing:-.02em}
.ipk-hero p{font-size:15px;line-height:1.7;opacity:.9;margin:0 0 22px;max-width:520px}
.ipk-actions{display:flex;gap:10px;flex-wrap:wrap}
.ipk-btn{display:inline-block;padding:12px 22px;border-radius:8px;font-weight:700;text-decoration:none;font-size:14px}
.ipk-btn.solid{background:#fff;color:#0b3a47}
.ipk-btn.line{background:transparent;color:#fff;border:1px solid rgba(255,255,255,.45)}
.ipk-facts{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.16);border-radius:10px;padding:18px}
.ipk-facts div{display:flex;justify-content:space-between;gap:12px;padding:7px 0;font-size:12.5px;border-bottom:1px solid rgba(255,255,255,.10)}
.ipk-facts div:last-child{border-bottom:none}
.ipk-facts span{opacity:.75}
.ipk-facts b{font-weight:700}
/* --- service list: the heart of the page --- */
.ipk-svc{display:grid;grid-template-columns:repeat(auto-fit,minmax(290px,1fr));gap:14px}
.ipk-svc-card{background:#fff;border:1px solid #e2edf1;border-radius:12px;padding:18px 20px;display:flex;gap:14px;text-decoration:none;color:inherit;transition:border-color .15s,box-shadow .15s}
.ipk-svc-card:hover{border-color:#0E7490;box-shadow:0 2px 12px rgba(14,116,144,.10)}
.ipk-svc-ico{flex:0 0 42px;width:42px;height:42px;border-radius:10px;background:#e6f4f8;color:#0E7490;display:flex;align-items:center;justify-content:center}
.ipk-svc-name{font-weight:700;font-size:14.5px;margin-bottom:4px;color:#0f172a}
.ipk-svc-desc{font-size:12.5px;color:#5b7280;line-height:1.6}
.ipk-svc-meta{margin-top:7px;font-size:11.5px;color:#94a3b8}
/* --- steps --- */
.ipk-steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;counter-reset:s}
.ipk-step{background:#fff;border:1px solid #e2edf1;border-radius:10px;padding:16px 18px}
.ipk-step b{display:block;font-size:13px;margin-bottom:5px}
.ipk-step span{font-size:12.5px;color:#5b7280;line-height:1.6}
.ipk-step em{display:inline-flex;width:22px;height:22px;border-radius:50%;background:#0E7490;color:#fff;font-style:normal;font-size:11px;font-weight:800;align-items:center;justify-content:center;margin-bottom:8px}
/* --- enquiry strip: one place, not repeated --- */
.ipk-enq{background:#0b3a47;color:#fff;border-radius:14px;padding:30px 28px;display:grid;grid-template-columns:1fr auto;gap:20px;align-items:center}
.ipk-enq h3{margin:0 0 6px;font-size:21px;font-weight:800;letter-spacing:-.01em}
.ipk-enq p{margin:0;font-size:13.5px;opacity:.8;max-width:520px;line-height:1.6}
.ipk-mats{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px}
.ipk-mat{background:#fff;border:1px solid #e2edf1;border-radius:10px;padding:14px}
.ipk-mat b{display:block;font-size:12.5px;margin-bottom:4px}
.ipk-mat span{font-size:11.5px;color:#5b7280;line-height:1.6}
@media(max-width:900px){.ipk-hero{grid-template-columns:1fr}.ipk-enq{grid-template-columns:1fr}}
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
        // featured_categories / featured_products / new_arrivals deliberately
        // not rendered here: a print shop is a service business.
    }
} ?>

<div class="ipk-wrap">

  <!-- Hero: who we are, what to do next -->
  <div class="mp-section">
    <div class="ipk-hero">
      <div>
        <h1><?= htmlspecialchars($settings->store_headline ?: 'Print, signage and branded work — produced properly.'); ?></h1>
        <p><?= htmlspecialchars($settings->store_subheadline ?: 'Large format, digital print, apparel and finishing. Tell us what you need and we will confirm specification, price and turnaround.'); ?></p>
        <div class="ipk-actions">
          <a class="ipk-btn solid" href="<?= $enq ?>">Send us your job</a>
          <?php if (!empty($svcs)): ?>
          <a class="ipk-btn line" href="<?= base_url('store/' . $slug . '/services') ?>">See all services</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="ipk-facts">
        <div><span>Turnaround</span><b><?= htmlspecialchars($settings->store_info_turnaround ?? 'From 24 hours'); ?></b></div>
        <div><span>Minimum order</span><b>1 piece</b></div>
        <div><span>Artwork check</span><b>Included</b></div>
        <div><span>Collection</span><b>In store or delivered</b></div>
      </div>
    </div>
  </div>

  <!-- Services: the main content -->
  <?php if (!empty($svcs)): ?>
  <div class="mp-section">
    <div class="mp-section-title">Our Printing Services</div>
    <?= mp_print_thumb_css() ?>
    <div class="ipk-svc">
      <?php
      foreach ($svcs as $svc):
        $price = $svc->effective_price ?? $svc->price;
        $desc = trim(preg_replace('/\s+/', ' ', (string)$svc->description));
      ?>
      <a class="ipk-svc-card" href="<?= base_url('store/' . $slug . '/service/' . (int)$svc->id) ?>">
        <?= mp_print_service_thumb($svc) ?>
        <div>
          <div class="ipk-svc-name"><?= htmlspecialchars($svc->service_name) ?></div>
          <?php if ($desc !== ''): ?>
          <div class="ipk-svc-desc"><?= htmlspecialchars(mb_substr($desc, 0, 120)) ?><?= mb_strlen($desc) > 120 ? '…' : '' ?></div>
          <?php endif; ?>
          <div class="ipk-svc-meta">
            <?php if (!empty($svc->service_duration)): ?><?= htmlspecialchars($svc->service_duration) ?> &middot; <?php endif; ?>
            <?php if ((float)$price > 0): ?><?= sf_currency($price, $store_currency ?? null) ?><?php else: ?>Priced on specification<?php endif; ?>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Process -->
  <div class="mp-section">
    <div class="mp-section-title">How We Work</div>
    <div class="ipk-steps">
      <div class="ipk-step"><em>1</em><b>Send your specification</b><span>Size, quantity, material, finish and deadline — or send the artwork you already have.</span></div>
      <div class="ipk-step"><em>2</em><b>We confirm and quote</b><span>You get a written specification and price, with the turnaround stated clearly.</span></div>
      <div class="ipk-step"><em>3</em><b>Approve and pay deposit</b><span>Approve the proof and settle the deposit — production is scheduled on that basis.</span></div>
      <div class="ipk-step"><em>4</em><b>Produced and handed over</b><span>Printed, finished and quality-checked. Collect in store or we deliver.</span></div>
    </div>
  </div>

  <!-- Materials we work with -->
  <div class="mp-section">
    <div class="mp-section-title">Materials &amp; Finishes</div>
    <div class="ipk-mats">
      <div class="ipk-mat"><b>Rigid &amp; Flexible Substrates</b><span>PVC flex, mesh, vinyl, acrylic, composite board.</span></div>
      <div class="ipk-mat"><b>Papers &amp; Board</b><span>Gloss, matte, satin, uncoated and card stock.</span></div>
      <div class="ipk-mat"><b>Garments</b><span>Cotton, poly and blends for print and transfer.</span></div>
      <div class="ipk-mat"><b>Finishing</b><span>Lamination, mounting, eyelets, hemming, binding.</span></div>
    </div>
  </div>

  <!-- Enquiry: a service business takes a BRIEF, not a basket. -->
<div class="mp-section">
  <?php
    // Dedicated specification-capturing enquiry form (falls back to the shared
    // contact section if the printing partial is unavailable).
    $enquiry_partial = APPPATH . 'views/printing/enquiry_form.php';
    if (file_exists($enquiry_partial)) {
        include($enquiry_partial);
    } else {
        include(APPPATH . 'views/themes/shared/sections/contact.php');
    }
  ?>
</div>

</div>
