<?php
$service = $service ?? null;
if(!$service){ return; }
$price = $service->effective_price ?? $service->price;
$oldPrice = $service->original_price ?? $service->price;
$hasDiscount = $oldPrice > $price;
$discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
?>
<div class="fx-section" style="padding-top:16px;">
  <div class="fx-section-inner">
    <nav style="margin-bottom:24px; font-size:13px; color:var(--fx-muted);" aria-label="Breadcrumb">
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" style="color:inherit;">Home</a>
      <span style="margin:0 8px; color:var(--fx-border);">/</span>
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/services'); ?>" style="color:inherit;">Services</a>
      <span style="margin:0 8px; color:var(--fx-border);">/</span>
      <span style="color:var(--fx-ink); font-weight:600;"><?= htmlspecialchars($service->service_name); ?></span>
    </nav>

    <div style="display:grid; grid-template-columns:1fr 400px; gap:40px;">
      <div>
        <div style="aspect-ratio: 16 / 10; border-radius:var(--fx-radius); overflow:hidden; background:var(--fx-cream); border:1px solid var(--fx-border); margin-bottom:24px;">
          <?php if($service->service_image && file_exists($service->service_image)): ?>
            <img src="<?= base_url($service->service_image); ?>" alt="<?= htmlspecialchars($service->service_name); ?>" style="width:100%;height:100%;object-fit:cover;">
          <?php else: ?>
            <div class="fx-card-placeholder" aria-label="Service"><span class="fx-service-mark">SERVICE</span></div>
          <?php endif; ?>
        </div>

        <h1 style="font-size:clamp(24px, 3vw, 30px); font-weight:800; color:var(--fx-ink); line-height:1.2; margin-bottom:8px;"><?= htmlspecialchars($service->service_name); ?></h1>
        <?php if($service->category_name): ?>
        <span style="display:inline-block; padding:4px 12px; border-radius:9999px; background:var(--fx-cream); color:var(--fx-green); font-size:12px; font-weight:600; margin-bottom:20px;"><?= htmlspecialchars($service->category_name); ?></span>
        <?php endif; ?>

        <?php if($service->description): ?>
        <div style="font-size:15px; color:var(--fx-ink); line-height:1.7; margin-bottom:24px;">
          <?= nl2br(htmlspecialchars($service->description)); ?>
        </div>
        <?php endif; ?>

        <?php if($service->long_description): ?>
        <div style="font-size:15px; color:var(--fx-muted); line-height:1.7;">
          <?= nl2br(htmlspecialchars($service->long_description)); ?>
        </div>
        <?php endif; ?>
      </div>

      <div style="position:sticky; top:100px;">
        <div style="background:var(--fx-white); border-radius:var(--fx-radius); border:1px solid var(--fx-border); padding:24px;">
          <div style="display:flex; align-items:baseline; justify-content:space-between; gap:12px; margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid var(--fx-border);">
            <div>
              <span style="font-size:28px; font-weight:800; color:var(--fx-green);"><?= sf_currency($price, $store_currency ?? null); ?></span>
              <?php if($hasDiscount): ?>
                <span style="font-size:16px; color:var(--fx-muted); text-decoration:line-through; margin-left:12px;"><?= sf_currency($oldPrice, $store_currency ?? null); ?></span>
                <span class="fx-card-discount" style="margin-left:8px;">-<?= $discountPct; ?>%</span>
              <?php endif; ?>
            </div>
          </div>

          <?php if($service->duration): ?>
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:16px; padding:12px; background:var(--fx-cream); border-radius:var(--fx-radius-sm);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--fx-green);"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span style="font-weight:600; color:var(--fx-ink);">Duration: <?= htmlspecialchars($service->duration); ?></span>
          </div>
          <?php endif; ?>

          <?php if(!empty($service_addons)): ?>
          <div style="margin-bottom:20px;">
            <label style="display:block; font-size:13px; font-weight:600; color:var(--fx-ink); margin-bottom:12px;">Add-ons</label>
            <div style="display:flex; flex-direction:column; gap:10px;">
              <?php foreach($service_addons as $addon): ?>
              <label style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; background:var(--fx-cream); border:1px solid var(--fx-border); border-radius:var(--fx-radius-sm); cursor:pointer;">
                <div style="display:flex; flex-direction:column; gap:2px;">
                  <span style="font-size:14px; font-weight:600; color:var(--fx-ink);"><?= htmlspecialchars($addon->addon_name); ?></span>
                  <span style="font-size:12px; color:var(--fx-muted);">+<?= sf_currency($addon->price, $store_currency ?? null); ?></span>
                </div>
                <input type="checkbox" name="service_addon_<?= $addon->id; ?>" value="1" class="fx-addon-checkbox" data-price="<?= $addon->price; ?>">
              </label>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/checkout?service=' . $service->id); ?>" class="fx-card-add" style="width:100%; display:flex; align-items:center; justify-content:center; gap:8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 4v8M8 12h8"/></svg>
            Book This Service
          </a>

          <?php if(!empty($settings->whatsapp_number) && ($settings->allow_whatsapp ?? 1)): ?>
          <button class="fx-card-wa" style="width:100%; margin-top:10px; padding:14px;" onclick="orderServiceOnWhatsApp('<?= htmlspecialchars(addslashes($service->service_name)); ?>',<?= $price; ?>);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
            Enquire on WhatsApp
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
