<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<section class="mp-product-addons" data-parent-type="<?= htmlspecialchars($parent_type, ENT_QUOTES, 'UTF-8'); ?>" data-parent-id="<?= (int)$parent_id; ?>" aria-label="Optional extras">
  <h2>Optional extras</h2>
  <div class="mp-product-addons-list">
    <?php foreach($addons as $addon): ?>
      <div class="mp-product-addon">
        <label class="mp-product-addon-main">
          <input class="mp-addon-choice" type="checkbox" value="<?= (int)$addon->id; ?>" data-name="<?= htmlspecialchars($addon->name, ENT_QUOTES, 'UTF-8'); ?>" data-price="<?= (float)$addon->price; ?>" data-max="<?= max(1, (int)$addon->max_qty); ?>">
          <span><strong><?= htmlspecialchars($addon->name, ENT_QUOTES, 'UTF-8'); ?></strong><small><?= sf_currency($addon->price, $store_currency ?? null); ?></small></span>
        </label>
        <label class="mp-product-addon-qty">Qty <input class="mp-addon-qty" type="number" min="1" max="<?= max(1, (int)$addon->max_qty); ?>" step="1" value="1" disabled aria-label="Quantity for <?= htmlspecialchars($addon->name, ENT_QUOTES, 'UTF-8'); ?>"></label>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<style>
.mp-product-addons{max-width:680px;margin:18px 0;padding:16px;border:1px solid var(--mp-border,#dce2ea);border-radius:6px;background:var(--mp-white,#fff)}.mp-product-addons h2{font-size:16px;margin-bottom:10px}.mp-product-addons-list{display:grid;gap:8px}.mp-product-addon{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 0;border-top:1px solid var(--mp-border,#dce2ea)}.mp-product-addon-main{display:flex;align-items:center;gap:10px;min-width:0;cursor:pointer}.mp-product-addon-main input{width:18px;height:18px;accent-color:var(--mp-primary,#2563eb);flex:0 0 auto}.mp-product-addon-main span{display:grid;gap:3px}.mp-product-addon-main strong{font-size:14px;overflow-wrap:anywhere}.mp-product-addon-main small{font-size:12px;color:var(--mp-gray,#667085)}.mp-product-addon-qty{display:flex;align-items:center;gap:6px;color:var(--mp-gray,#667085);font-size:12px;flex:0 0 auto}.mp-product-addon-qty input{width:62px;padding:7px;border:1px solid var(--mp-border,#dce2ea);border-radius:4px;font:inherit;color:var(--mp-dark,#172033)}
</style>
<script>
(function(){
  var section=document.querySelector('.mp-product-addons');if(!section)return;
  section.querySelectorAll('.mp-product-addon').forEach(function(row){
    var check=row.querySelector('.mp-addon-choice');var qty=row.querySelector('.mp-addon-qty');
    check.addEventListener('change',function(){qty.disabled=!check.checked;});
    qty.addEventListener('change',function(){var max=parseInt(qty.max,10)||1;var val=parseInt(qty.value,10)||1;qty.value=Math.min(max,Math.max(1,val));});
  });
  var primary=Array.from(document.querySelectorAll('button')).find(function(button){return /add to cart|add to bag|buy now|book this service/i.test(button.textContent||'');});
  if(primary&&section.previousElementSibling!==primary)primary.parentNode.insertBefore(section,primary);
})();
</script>
