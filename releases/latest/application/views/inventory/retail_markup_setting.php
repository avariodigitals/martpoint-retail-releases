<?php $retail_markup_percent = mp_get_store_setting(get_current_store_id(), 'inventory', 'retail_markup_percent', ''); ?>
<div style="margin:10px 0 16px;">
  <label for="retail_markup_percent" style="display:block;margin-bottom:6px;">Retail markup (%)</label>
  <input type="number" name="retail_markup_percent" id="retail_markup_percent" class="form-control" min="0" max="10000" step="0.01" placeholder="e.g. 20" value="<?= htmlspecialchars((string)$retail_markup_percent, ENT_QUOTES, 'UTF-8'); ?>" style="width:100%;max-width:220px;padding:10px;">
  <p style="font-size:12px;color:var(--mp-muted,#64748b);margin-top:6px;">When Automatic Retail Pricing is on, retail = wholesale + this percentage of wholesale. At 20%, 12,000 becomes 14,400. Leave blank for manual pricing. Existing products are recalculated when saved or imported; saving this setting does not change the catalogue. Purchase cost and online price stay separate.</p>
</div>
