<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars(mp_label('custom_order','Job Orders')); ?></h2>
    <div class="mp-page-sub">Customer orders with specifications, artwork approval and deposits</div>
  </div>
</div>

<?php if ($can_add): ?>
<div class="mp-card-form" style="margin-bottom:20px;">
  <div class="mp-card-head"><h3><i class="fa fa-plus-circle"></i> New Job Order</h3></div>
  <div class="mp-card-body">
    <form id="nyOrderForm" onsubmit="return nySaveOrder(event);">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <div class="mp-form-grid" style="grid-template-columns:repeat(4,1fr);gap:14px;">
        <div><label>Customer *</label>
          <select name="customer_id" class="form-control" required>
            <option value="">— select —</option>
            <?php foreach ($customers as $c): ?><option value="<?= $c->id; ?>"><?= htmlspecialchars($c->customer_name); ?><?= $c->mobile ? ' ('.$c->mobile.')' : ''; ?></option><?php endforeach; ?>
          </select></div>
        <div><label>Product *</label>
          <select name="item_id" id="nyOrderItem" class="form-control" required onchange="nyOrderItemSpec(this)">
            <option value="">— select —</option>
            <optgroup label="Finished Goods">
              <?php foreach ($products as $p): ?><option value="<?= $p->item_id; ?>" data-print="<?= htmlspecialchars($p->print_type); ?>" data-design="<?= htmlspecialchars($p->design_ref); ?>" data-price="<?= $p->sales_price; ?>"><?= htmlspecialchars($p->item_name); ?></option><?php endforeach; ?>
            </optgroup>
            <optgroup label="Film Rolls">
              <?php foreach ($rolls as $p): ?><option value="<?= $p->item_id; ?>" data-print="none" data-design="" data-price="<?= $p->sales_price; ?>"><?= htmlspecialchars($p->item_name); ?></option><?php endforeach; ?>
            </optgroup>
          </select></div>
        <div><label>Quantity *</label><input type="number" step="any" name="order_qty" class="form-control" required></div>
        <div><label>Selling unit</label>
          <select name="order_unit_id" class="form-control">
            <option value="">base unit</option>
            <?php foreach ($units as $u): ?><option value="<?= $u->id; ?>"><?= htmlspecialchars($u->unit_name); ?></option><?php endforeach; ?>
          </select></div>
        <div><label>Agreed total price</label><input type="number" step="any" name="total_amount" class="form-control" required></div>
        <div><label>Deposit required</label><input type="number" step="any" name="deposit_amount" class="form-control"></div>
        <div><label>Deposit paid</label><input type="number" step="any" name="deposit_paid" class="form-control"></div>
        <div><label>Due date</label><input type="date" name="due_date" class="form-control"></div>
        <div><label>Order date</label><input type="date" name="order_date" class="form-control" value="<?= date('Y-m-d'); ?>"></div>
        <div><label>Design reference</label><input type="text" name="design_ref" id="nyOrderDesign" class="form-control"></div>
        <div><label>Status</label>
          <select name="status" class="form-control">
            <?php foreach ($workflow as $st): ?><option value="<?= $st; ?>" <?= $st==='new'?'selected':''; ?>><?= Custom_orders_model::status_label($st); ?></option><?php endforeach; ?>
          </select></div>
        <div><label>&nbsp;</label><label style="font-weight:400;"><input type="checkbox" name="artwork_required" id="nyOrderArtwork" value="1"> Artwork approval needed</label></div>
      </div>
      <div class="mp-form-grid" style="grid-template-columns:repeat(4,1fr);gap:14px;margin-top:6px;">
        <?php foreach (['Material','Width (cm)','Length (cm)','Thickness (micron)','Colour','Bag Type','Print Colours','Packaging'] as $lbl): ?>
        <div><label><?= $lbl; ?></label>
          <input type="hidden" name="spec_label[]" value="<?= $lbl; ?>">
          <input type="text" name="spec_value[]" class="form-control"></div>
        <?php endforeach; ?>
      </div>
      <div><label>Notes</label><input type="text" name="notes" class="form-control"></div>
      <div style="margin-top:14px;"><button type="submit" class="mp-qa-btn"><i class="fa fa-save"></i> Save Order</button></div>
      <div id="nyOrderMsg" style="margin-top:8px;font-size:13px;"></div>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="mp-card-form">
  <div class="mp-card-head"><h3><i class="fa fa-list"></i> Orders</h3></div>
  <div class="mp-card-body" style="padding:0!important;">
    <table class="mp-static-table">
      <thead><tr><th>Order</th><th>Customer</th><th>Product</th><th class="text-right">Qty</th><th class="text-right">Total</th><th class="text-right">Balance</th><th>Artwork</th><th>Status</th><th>Due</th><th></th></tr></thead>
      <tbody>
      <?php if (empty($orders)): ?>
        <tr><td colspan="10" style="padding:20px;color:var(--mp-muted);">No job orders yet.</td></tr>
      <?php endif; ?>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td><a href="<?= base_url('nylon/order_view/'.$o->id); ?>"><strong><?= htmlspecialchars($o->order_code); ?></strong></a><?= $o->repeat_of_id ? '<br><small class="text-muted">repeat order</small>' : ''; ?></td>
          <td><?= htmlspecialchars($o->customer_name); ?><br><small class="text-muted"><?= htmlspecialchars($o->mobile); ?></small></td>
          <td><small><?= htmlspecialchars($o->item_name); ?></small></td>
          <td class="text-right"><?= $o->order_qty !== null ? format_qty($o->order_qty) : '—'; ?><?= $o->dispatched_qty > 0 ? '<br><small class="text-muted">sent '.format_qty($o->dispatched_qty).'</small>' : ''; ?></td>
          <td class="text-right"><?= $CI->currency($o->total_amount); ?></td>
          <td class="text-right <?= $o->balance_due > 0 ? 'text-danger' : 'text-success'; ?>"><?= $CI->currency($o->balance_due); ?></td>
          <td><?php if ($o->artwork_required): ?><span class="label label-<?= $o->artwork_approved ? 'success' : 'warning'; ?>"><?= $o->artwork_approved ? 'Approved' : 'Pending'; ?></span><?php else: ?><small class="text-muted">—</small><?php endif; ?></td>
          <td><span class="label label-<?= Custom_orders_model::status_badge($o->status); ?>"><?= Custom_orders_model::status_label($o->status); ?></span></td>
          <td><small><?= $o->due_date ? show_date($o->due_date) : '—'; ?></small></td>
          <td><a href="<?= base_url('nylon/order_view/'.$o->id); ?>" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
var NY_CSRF_NAME = <?= json_encode($this->security->get_csrf_token_name()); ?>;
function nyOrderItemSpec(sel){
  var o = sel.options[sel.selectedIndex];
  var art = document.getElementById('nyOrderArtwork');
  var design = document.getElementById('nyOrderDesign');
  if (o.dataset.print && o.dataset.print !== 'none' && o.dataset.print !== '') { art.checked = true; } else { art.checked = false; }
  design.value = o.dataset.design || '';
}
function nySaveOrder(ev){
  ev.preventDefault();
  var f = document.getElementById('nyOrderForm');
  var msg = document.getElementById('nyOrderMsg');
  fetch('<?= base_url('nylon/order_save'); ?>', {method:'POST', body:new FormData(f)})
    .then(function(r){return r.json();})
    .then(function(d){
      msg.innerHTML = '<span class="'+(d.success?'text-success':'text-danger')+'">'+d.message+'</span>';
      if (d.csrf_hash) { f.querySelector('input[name="'+NY_CSRF_NAME+'"]').value = d.csrf_hash; }
      if (d.success && d.id) setTimeout(function(){ location.href='<?= base_url('nylon/order_view'); ?>/'+d.id; }, 700);
    });
  return false;
}
</script>
