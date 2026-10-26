<?php if(empty($food_pairings) || !array_filter($food_pairings)) return; ?>
<section class="fx-related-products fx-pairings" id="fx-pairings" aria-labelledby="fx-pairings-title">
  <div class="fx-section-header">
    <div><h2 id="fx-pairings-title">Goes well with</h2><p>A few suggestions to complete your order.</p></div>
  </div>
  <button type="button" id="fx-pairings-location-prompt" class="fx-pairings-location-prompt" onclick="const picker=document.getElementById('fx-product-location-picker'); if(picker){picker.open=true;picker.scrollIntoView({behavior:'smooth',block:'center'});picker.querySelector('summary').focus();}">Choose a kitchen to see available pairings <span aria-hidden="true">→</span></button>
  <?php foreach($food_pairings as $outletId => $suggestions): ?>
  <div class="fx-pairings-panel" data-pairing-outlet="<?= (int)$outletId; ?>" hidden>
    <?php foreach($suggestions as $suggestion):
      $image = (string)($suggestion->item_image ?? '');
      $imageUrl = preg_match('~^https?://~i', $image) ? $image : base_url(ltrim($image, '/'));
    ?>
    <a class="fx-pairing" data-pairing-item="<?= (int)$suggestion->id; ?>" href="<?= base_url('store/' . $settings->store_slug . '/product/' . (int)$suggestion->id); ?>">
      <?php if($image && sf_product_image_exists($image)): ?>
      <img src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($suggestion->item_name, ENT_QUOTES, 'UTF-8'); ?>" width="88" height="88" loading="lazy">
      <?php else: ?><span class="fx-pairing-brand"><?= htmlspecialchars($store->store_name ?? ''); ?></span><?php endif; ?>
      <span class="fx-pairing-copy"><small><?= htmlspecialchars($suggestion->pairing_reason); ?></small><strong><?= htmlspecialchars($suggestion->item_name); ?></strong><span><?= sf_currency($suggestion->effective_price, $store_currency ?? null); ?></span></span>
      <span class="fx-pairing-open" aria-label="View options" aria-hidden="true">→</span>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>
</section>
<style>
.fx-pairings{margin-top:40px;border-top:1px solid var(--fx-border);padding-top:28px}
.fx-pairings .fx-section-header{margin-bottom:16px;text-align:left}
.fx-pairings h2{font-family:'DM Sans',sans-serif!important;font-size:24px;letter-spacing:-.6px;margin:0 0 6px;font-weight:750;line-height:1.2}
.fx-pairings .fx-section-header p{font-size:14px;color:var(--fx-muted);margin:0}
.fx-pairings-panel{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 32px}
.fx-pairings [hidden],.fx-pairings[hidden]{display:none!important}
.fx-pairing{display:flex;gap:14px;align-items:center;min-width:0;text-decoration:none;color:var(--fx-ink);padding:16px 0;border-bottom:1px solid var(--fx-border)}
.fx-pairing>img,.fx-pairing-brand{width:88px;height:88px;object-fit:cover;border-radius:14px;flex-shrink:0}
.fx-pairing-brand{display:grid;place-items:center;background:var(--fx-cream);color:var(--fx-green);font-size:12px;text-align:center;padding:8px;box-sizing:border-box}
.fx-pairing-copy{display:grid;gap:5px;min-width:0;flex:1}
.fx-pairing-copy small{color:var(--fx-muted);font-size:12px;line-height:1.3}
.fx-pairing-copy strong{font-size:16px;line-height:1.35;font-weight:650}
.fx-pairing-copy>span{font-weight:700;font-size:14px}
.fx-pairing-open{width:32px;height:32px;flex-shrink:0;border:1px solid var(--fx-border);border-radius:50%;display:grid;place-items:center}
.fx-pairings-location-prompt{border:1px solid var(--fx-border);border-radius:12px;padding:14px 16px;color:var(--fx-ink);background:transparent;font:inherit;font-size:14px;text-align:left;width:100%;cursor:pointer}
.fx-pairing:focus-visible,.fx-pairings-location-prompt:focus-visible{outline:2px solid var(--fx-green);outline-offset:4px}
@media(max-width:820px){.fx-pairings-panel{grid-template-columns:minmax(0,1fr)}.fx-pairings{margin-top:28px;padding-top:22px}.fx-pairing>img,.fx-pairing-brand{width:76px;height:76px}.fx-pairing{gap:12px}.fx-pairing-copy strong{font-size:15px}}
</style>
