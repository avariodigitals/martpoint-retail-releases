<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.ac-items{max-width:320px;font-size:12px;color:var(--mp-muted);line-height:1.5}
.ac-recovery{display:flex;gap:6px;align-items:center}
.ac-recovery input{width:100%;font-size:12px;padding:6px 8px;border:1px solid var(--mp-border);border-radius:7px;background:var(--mp-surface);color:var(--mp-muted)}
.ac-empty{padding:40px;text-align:center;color:var(--mp-muted)}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Carts idle for <?= (int)$hours; ?>+ hours with customer contact details — reach out on WhatsApp with a restore link. <b>Manual recovery:</b> you send the message yourself; reminders are capped at 3 per cart, spaced 24h apart, and audited.</div>
  </div>
</div>

<div class="mp-table-wrap">
  <div class="mp-card-head"><h3>Abandoned Carts</h3></div>
  <div class="box-body">
    <?php if(!$enabled): ?>
      <div class="ac-empty">
        Cart recovery is turned off. Enable <strong>Track Abandoned Carts</strong> in
        <a href="<?= base_url('online_store/settings'); ?>">Online Store Settings</a>.
      </div>
    <?php elseif(empty($carts)): ?>
      <div class="ac-empty">No abandoned carts right now. Carts appear here after a customer leaves checkout for <?= (int)$hours; ?>+ hours.</div>
    <?php else: ?>
    <div class="mp-dt-scroll">
      <table class="table mp-dt-table" width="100%">
        <thead>
          <tr><th>Last active</th><th>Customer</th><th>Cart</th><th>Value</th><th>Nudged</th><th>Recovery link</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach($carts as $c):
            $items = json_decode($c->items_json, true) ?: [];
            $link = base_url('store/' . $store_slug . '/cart?cart=' . urlencode($c->cart_token));
            $waPhone = function_exists('mp_format_whatsapp_phone') ? mp_format_whatsapp_phone($c->customer_phone) : preg_replace('/[^0-9]/', '', (string)$c->customer_phone);
            $waMsg = 'Hello' . ($c->customer_name ? ' ' . $c->customer_name : '') . ', you left items in your cart at our store. Tap this link to restore your cart and complete your order: ' . $link;
          ?>
          <tr>
            <td><?= show_date($c->last_activity); ?> <small class="row-meta"><?= date('H:i', strtotime($c->last_activity)); ?></small></td>
            <td>
              <?= htmlspecialchars($c->customer_name ?: '—'); ?><br>
              <small class="row-meta"><?= htmlspecialchars($c->customer_phone ?: ''); ?><?= $c->customer_email ? ' · ' . htmlspecialchars($c->customer_email) : ''; ?></small>
            </td>
            <td><div class="ac-items">
              <?php foreach(array_slice($items, 0, 4) as $it): ?>
                <?= (int)($it['qty'] ?? 1); ?>× <?= htmlspecialchars($it['name'] ?? 'Item'); ?><br>
              <?php endforeach; ?>
              <?php if(count($items) > 4): ?>+<?= count($items) - 4; ?> more<?php endif; ?>
            </div></td>
            <td class="amt"><?= $CI->currency($c->subtotal ?: 0); ?><?= $c->coupon_code ? '<br><small class="row-meta">Coupon: '.htmlspecialchars($c->coupon_code).'</small>' : ''; ?></td>
            <td><?= (int)$c->reminder_count ?><br><small class="row-meta"><?= $c->reminder_sent_at ? date('M j H:i', strtotime($c->reminder_sent_at)) : ''; ?></small></td>
            <td><div class="ac-recovery">
              <input type="text" readonly value="<?= htmlspecialchars($link); ?>" onclick="this.select()">
              <button type="button" class="mp-qa-btn blue" onclick="copyAcLink(this)" title="Copy link"><i class="fa fa-copy"></i></button>
            </div></td>
            <td>
              <div class="mp-actions">
                <?php if($waPhone): ?>
                <a class="mp-edit" title="Nudge on WhatsApp" target="_blank" rel="noopener"
                   href="https://wa.me/<?= $waPhone; ?>?text=<?= urlencode($waMsg); ?>"
                   onclick="markAcReminded(<?= (int)$c->id; ?>)"><i class="fa fa-whatsapp"></i></a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function copyAcLink(btn){
  var input = btn.parentElement.querySelector('input');
  input.select();
  if(navigator.clipboard){ navigator.clipboard.writeText(input.value); }
  else { document.execCommand('copy'); }
}
function markAcReminded(id){
  var fd = new FormData();
  fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
  fd.append('cart_id', id);
  fd.append('channel', 'whatsapp');
  fetch('<?= base_url('online_store/mark_cart_reminded'); ?>', {method:'POST', body:fd}).catch(function(){});
}
</script>
