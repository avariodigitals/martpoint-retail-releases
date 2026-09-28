<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($order->order_code); ?></h2>
    <div class="mp-page-sub"><?= htmlspecialchars($order->customer_name); ?> · <?= htmlspecialchars($order->item_name); ?></div>
  </div>
  <div style="display:flex;gap:8px;">
    <a href="<?= base_url('nylon/orders'); ?>" class="mp-qa-btn" style="background:var(--mp-muted);">← Orders</a>
    <?php if ($can_add ?? false): ?><?php endif; ?>
    <?php if ($can_jobs): ?><a href="<?= base_url('nylon/jobs?order_id='.$order->id); ?>" class="mp-qa-btn"><i class="fa fa-industry"></i> Create Job</a><?php endif; ?>
  </div>
</div>

<div class="mp-form-grid" style="grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
  <div>
    <div class="mp-card-form" style="margin-bottom:20px;">
      <div class="mp-card-head"><h3><i class="fa fa-file-text-o"></i> Order Details</h3>
        <span class="label label-<?= Custom_orders_model::status_badge($order->status); ?>" style="font-size:12px;"><?= Custom_orders_model::status_label($order->status); ?></span></div>
      <div class="mp-card-body">
        <table class="mp-static-table">
          <tr><td style="width:160px;" class="text-muted">Customer</td><td><?= htmlspecialchars($order->customer_name); ?> <?= $order->mobile ? '· '.$order->mobile : ''; ?></td></tr>
          <tr><td class="text-muted">Product</td><td><?= htmlspecialchars($order->item_name); ?><?= $order->design_ref ? ' · Design '.htmlspecialchars($order->design_ref) : ''; ?></td></tr>
          <tr><td class="text-muted">Quantity</td><td><?= $order->order_qty !== null ? format_qty($order->order_qty).' '.htmlspecialchars($order->unit_name ?: 'units') : '—'; ?><?= $order->dispatched_qty > 0 ? ' <small class="text-muted">('.format_qty($order->dispatched_qty).' dispatched)</small>' : ''; ?></td></tr>
          <tr><td class="text-muted">Value</td><td><?= $CI->currency($order->total_amount); ?> · deposit <?= $CI->currency($order->deposit_paid); ?> · balance <strong class="<?= $order->balance_due>0?'text-danger':'text-success'; ?>"><?= $CI->currency($order->balance_due); ?></strong></td></tr>
          <tr><td class="text-muted">Dates</td><td>Ordered <?= show_date($order->order_date); ?><?= $order->due_date ? ' · due '.show_date($order->due_date) : ''; ?><?= $order->delivery_date ? ' · delivered '.show_date($order->delivery_date) : ''; ?></td></tr>
          <?php if (!empty($order->specs)): ?>
          <tr><td class="text-muted">Specification</td><td><?php foreach ($order->specs as $k=>$v): if($v==='' ) continue; ?><span class="label label-default" style="margin-right:4px;"><?= htmlspecialchars($k); ?>: <?= htmlspecialchars($v); ?></span><?php endforeach; ?></td></tr>
          <?php endif; ?>
          <?php if ($order->notes): ?><tr><td class="text-muted">Notes</td><td><?= nl2br(htmlspecialchars($order->notes)); ?></td></tr><?php endif; ?>
        </table>
        <?php if ($can_edit): ?>
        <div style="display:flex;gap:10px;margin-top:12px;flex-wrap:wrap;align-items:flex-end;">
          <div><label style="font-size:12px;">Set status</label>
            <select id="nyOrderStatus" class="form-control" style="min-width:170px;">
              <?php foreach ($workflow as $st): ?><option value="<?= $st; ?>" <?= $order->status===$st?'selected':''; ?>><?= Custom_orders_model::status_label($st); ?></option><?php endforeach; ?>
            </select></div>
          <button class="mp-qa-btn" onclick="nySetStatus(<?= $order->id; ?>)">Update</button>
          <div><label style="font-size:12px;">Dispatch qty</label><input type="number" step="any" id="nyDispatchQty" class="form-control" style="width:120px;" placeholder="0"></div>
          <button class="mp-qa-btn" style="background:#0d9488;" onclick="nyDispatch(<?= $order->id; ?>)"><i class="fa fa-truck"></i> Record Dispatch</button>
          <button class="mp-qa-btn" style="background:#7C3AED;" onclick="nyRepeat(<?= $order->id; ?>)"><i class="fa fa-copy"></i> Repeat Order</button>
        </div>
        <?php endif; ?>
        <div id="nyMsg" style="margin-top:8px;font-size:13px;"></div>
      </div>
    </div>

    <div class="mp-card-form" style="margin-bottom:20px;">
      <div class="mp-card-head"><h3><i class="fa fa-paint-brush"></i> <?= mp_label('artwork','Artwork'); ?> &amp; Approval</h3>
        <?php if ($order->artwork_required): ?>
          <span class="label label-<?= $order->artwork_approved ? 'success' : 'warning'; ?>"><?= $order->artwork_approved ? 'Approved — printable' : 'Awaiting approval — cannot print'; ?></span>
        <?php else: ?><span class="label label-default">Not required</span><?php endif; ?></div>
      <div class="mp-card-body">
        <?php if (!empty($order->artworks)): ?>
        <table class="mp-static-table">
          <thead><tr><th>Version</th><th>File</th><th>Status</th><th>Uploaded</th><th>Approved</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($order->artworks as $a): ?>
            <tr>
              <td>v<?= (int)$a->version_no; ?></td>
              <td><a href="<?= base_url($a->file_path); ?>" target="_blank"><?= htmlspecialchars($a->file_name); ?></a><?= $a->note ? '<br><small class="text-muted">'.htmlspecialchars($a->note).'</small>' : ''; ?></td>
              <td><span class="label label-<?= ['approved'=>'success','rejected'=>'danger','pending'=>'warning'][$a->status] ?? 'default'; ?>"><?= ucfirst($a->status); ?></span></td>
              <td><small><?= htmlspecialchars($a->uploaded_by); ?> · <?= show_date($a->created_at); ?></small></td>
              <td><small><?= $a->approved_by ? htmlspecialchars($a->approved_by).' · '.show_date($a->approved_at) : '—'; ?></small></td>
              <td style="white-space:nowrap;">
                <?php if ($can_approve && $a->status !== 'approved'): ?><button class="btn btn-xs btn-success" onclick="nyArtwork(<?= $a->id; ?>,'approved')"><i class="fa fa-check"></i> Approve</button><?php endif; ?>
                <?php if ($can_approve && $a->status !== 'rejected'): ?><button class="btn btn-xs btn-danger" onclick="nyArtwork(<?= $a->id; ?>,'rejected')"><i class="fa fa-times"></i> Reject</button><?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
          <p class="mp-muted" style="font-size:13px;">No artwork uploaded yet.</p>
        <?php endif; ?>
        <?php if ($can_artwork): ?>
        <form method="post" action="<?= base_url('nylon/artwork_upload'); ?>" enctype="multipart/form-data" style="margin-top:10px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
          <input type="hidden" name="order_id" value="<?= $order->id; ?>">
          <input type="file" name="artwork_file" class="form-control" style="max-width:280px;" required>
          <input type="text" name="note" class="form-control" style="max-width:220px;" placeholder="Note (optional)">
          <button type="submit" class="mp-qa-btn"><i class="fa fa-upload"></i> Upload Artwork</button>
        </form>
        <?php endif; ?>
      </div>
    </div>

    <div class="mp-card-form">
      <div class="mp-card-head"><h3><i class="fa fa-industry"></i> Linked Production Jobs</h3>
        <?php if ($can_jobs): ?><a href="<?= base_url('nylon/jobs?order_id='.$order->id); ?>" class="mp-qa-btn" style="font-size:12px;">New job for this order</a><?php endif; ?></div>
      <div class="mp-card-body" style="padding:0!important;">
        <?php if (!empty($jobs)): ?>
        <table class="mp-static-table">
          <thead><tr><th>Job</th><th class="text-right">Planned</th><th>Status</th><th>Due</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($jobs as $j): ?>
            <tr>
              <td><?= htmlspecialchars($j->job_code); ?></td>
              <td class="text-right"><?= format_qty($j->planned_qty); ?></td>
              <td><span class="label label-<?= Nylon_model::job_status_badge($j->status); ?>"><?= Nylon_model::job_status_label($j->status); ?></span></td>
              <td><small><?= $j->due_date ? show_date($j->due_date) : '—'; ?></small></td>
              <td><a href="<?= base_url('nylon/job_view/'.$j->id); ?>" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?><p style="padding:16px;color:var(--mp-muted);font-size:13px;margin:0;">No production jobs linked to this order yet.</p><?php endif; ?>
      </div>
    </div>
  </div>

  <div class="mp-card-form" style="margin-bottom:0;">
    <div class="mp-card-head"><h3><i class="fa fa-history"></i> History</h3></div>
    <div class="mp-card-body" style="padding:0!important;">
      <?php if (!empty($history)): ?>
      <table class="mp-static-table">
        <tbody>
        <?php foreach ($history as $h): ?>
          <tr><td><small><strong><?= Custom_orders_model::status_label($h->new_status); ?></strong><?= $h->note ? ' — '.htmlspecialchars($h->note) : ''; ?><br><span class="text-muted"><?= htmlspecialchars($h->changed_by_name); ?> · <?= show_date($h->created_at); ?></span></small></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?><p style="padding:16px;color:var(--mp-muted);font-size:13px;margin:0;">No history yet.</p><?php endif; ?>
    </div>
  </div>
</div>

<script>
var NY_CSRF_NAME = <?= json_encode($this->security->get_csrf_token_name()); ?>;
var NY_CSRF_HASH = <?= json_encode($this->security->get_csrf_hash()); ?>;
function nyPost(url, data){
  var fd = new FormData();
  for (var k in data) fd.append(k, data[k]);
  fd.append(NY_CSRF_NAME, NY_CSRF_HASH);
  return fetch('<?= base_url(); ?>'+url, {method:'POST', body:fd}).then(function(r){return r.json();});
}
function nyDone(d){
  var m = document.getElementById('nyMsg');
  if (d.csrf_hash) NY_CSRF_HASH = d.csrf_hash;
  if (m) m.innerHTML = '<span class="'+(d.success?'text-success':'text-danger')+'">'+(d.message||'')+'</span>';
  if (d.success) setTimeout(function(){ location.reload(); }, 800);
}
function nySetStatus(id){ nyPost('nylon/order_status',{id:id,status:document.getElementById('nyOrderStatus').value}).then(nyDone); }
function nyDispatch(id){
  var q = document.getElementById('nyDispatchQty').value;
  if(!q || q <= 0){ alert('Enter a dispatch quantity'); return; }
  nyPost('nylon/order_dispatch',{id:id,dispatch_qty:q}).then(nyDone);
}
function nyRepeat(id){ if(confirm('Create a repeat order from this approved specification?')) nyPost('nylon/order_repeat',{id:id}).then(function(d){ if(d.success && d.id){ location.href='<?= base_url('nylon/order_view'); ?>/'+d.id; } else { nyDone(d); } }); }
function nyArtwork(id,st){ nyPost('nylon/artwork_status',{id:id,status:st}).then(nyDone); }
</script>
