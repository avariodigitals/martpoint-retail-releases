<?php
/**
 * Back-in-stock subscribe block — include from a theme's product_detail.php
 * where the out-of-stock state is handled:
 *   <?php if($sfOos) $this->load->view('themes/shared/restock_subscribe', ['sf_item_id' => $product->id]); ?>
 * Posts to storefront/notify_restock; unsubscribes via token link in the email.
 */
?>
<div class="mp-restock-box" style="margin-bottom:12px; padding:12px; border:1px solid var(--mp-border, #E2E8F0); border-radius:var(--mp-radius-sm, 8px);">
  <div style="font-size:13px; font-weight:600; margin-bottom:8px; color:var(--mp-dark, #0F172A);">Get notified when it's back in stock</div>
  <div style="display:flex; flex-wrap:wrap; gap:8px;">
    <input type="email" class="mp-restock-email" placeholder="Your email address" style="flex:1 1 160px; min-width:0; padding:10px 12px; border:1px solid var(--mp-border, #E2E8F0); border-radius:var(--mp-radius-sm, 8px); font-size:14px;">
    <button type="button" onclick="mpSubscribeRestock(this, <?= (int)($sf_item_id ?? 0); ?>)" style="padding:10px 16px; border-radius:var(--mp-radius-sm, 8px); background:var(--mp-dark, #0F172A); color:#fff; border:none; font-weight:600; cursor:pointer; white-space:nowrap;">Notify me</button>
  </div>
  <div class="mp-restock-msg" style="font-size:12px; margin-top:6px; color:var(--mp-gray, #64748B);"></div>
</div>
<script>
if(typeof mpSubscribeRestock === 'undefined'){
  function mpSubscribeRestock(btn, itemId){
    var box = btn.closest('.mp-restock-box');
    var emailEl = box.querySelector('.mp-restock-email');
    var msgEl = box.querySelector('.mp-restock-msg');
    var email = (emailEl.value || '').trim();
    if(!email){ msgEl.textContent = 'Enter your email address.'; return; }
    // In select-mode pickers, subscribe to the chosen variant child —
    // the picker exposes it via window.sfPickedVariant. Otherwise the
    // page's own item id is used (a variant parent covers any-child
    // restocks via effective-stock semantics).
    var targetId = (window.sfPickedVariant && window.sfPickedVariant.id) ? window.sfPickedVariant.id : itemId;
    var fd = new FormData();
    fd.append('store_id', '<?= (int)($settings->store_id ?? 0); ?>');
    fd.append('item_id', String(targetId));
    fd.append('email', email);
    fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
    fetch('<?= base_url('storefront/notify_restock'); ?>', {method: 'POST', body: fd})
      .then(function(r){ return r.json(); })
      .then(function(r){
        msgEl.textContent = r.message || (r.status ? 'Subscribed.' : 'Failed.');
        msgEl.style.color = r.status ? '#059669' : '#DC2626';
        if(r.status) emailEl.value = '';
      })
      .catch(function(){ msgEl.textContent = 'Could not subscribe right now.'; });
  }
}
</script>
