<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); $preselect_order = (int)$this->input->get('order_id'); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars(mp_label('production','Production Jobs')); ?></h2>
    <div class="mp-page-sub">Stage-tracked jobs linked to customer orders or stock replenishment</div>
  </div>
  <form method="get" action="<?= base_url('nylon/jobs'); ?>" style="display:flex;gap:8px;">
    <select name="branch_id" class="form-control" onchange="this.form.submit()">
      <option value="">All <?= htmlspecialchars(mp_label('warehouse','Factories')); ?></option>
      <?php foreach ($warehouses as $w): ?><option value="<?= $w->id; ?>" <?= $warehouse_id==$w->id?'selected':''; ?>><?= htmlspecialchars($w->warehouse_name); ?></option><?php endforeach; ?>
    </select>
    <select name="status" class="form-control" onchange="this.form.submit()">
      <option value="">All statuses</option>
      <?php foreach (Nylon_model::job_statuses() as $s): ?><option value="<?= $s; ?>" <?= $status_filter===$s?'selected':''; ?>><?= Nylon_model::job_status_label($s); ?></option><?php endforeach; ?>
    </select>
  </form>
</div>

<?php if ($can_add): ?>
<div class="mp-card-form" style="margin-bottom:20px;">
  <div class="mp-card-head"><h3><i class="fa fa-plus-circle"></i> New Production Job</h3></div>
  <div class="mp-card-body">
    <form id="nyJobForm" onsubmit="return nySaveJob(event);">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <div class="mp-form-grid" style="grid-template-columns:repeat(4,1fr);gap:14px;">
        <div><label>Customer order</label>
          <select name="custom_order_id" id="nyJobOrder" class="form-control" onchange="nyJobOrderPicked(this)">
            <option value="">— stock replenishment (no order) —</option>
            <?php foreach ($open_orders as $o): ?>
              <option value="<?= $o->id; ?>" data-item="<?= $o->item_id; ?>" data-qty="<?= $o->order_qty; ?>" data-unit="<?= $o->order_unit_id; ?>" <?= $preselect_order===$o->id?'selected':''; ?>><?= htmlspecialchars($o->order_code.' — '.$o->customer_name); ?><?= $o->artwork_approved ? '' : ' ⚠ artwork pending'; ?></option>
            <?php endforeach; ?>
          </select></div>
        <div><label>Product to produce *</label>
          <select name="product_item_id" id="nyJobProduct" class="form-control" required>
            <option value="">— select —</option>
            <?php foreach ($products as $p): ?><option value="<?= $p->item_id; ?>" data-class="<?= $p->item_class; ?>" data-print="<?= htmlspecialchars($p->print_type); ?>"><?= htmlspecialchars($p->item_name); ?> (<?= $p->item_class === 'film_roll' ? 'film roll' : 'finished'; ?>)</option><?php endforeach; ?>
          </select></div>
        <div><label>Planned qty *</label><input type="number" step="any" id="nyJobQty" name="planned_qty" class="form-control" required></div>
        <div><label>Planned unit</label>
          <select name="planned_unit_id" id="nyJobUnit" class="form-control">
            <option value="">product base unit</option>
            <?php foreach ($units as $u): ?><option value="<?= $u->id; ?>"><?= htmlspecialchars($u->unit_name); ?></option><?php endforeach; ?>
          </select></div>
        <div><label>Input material <?= $mode['extrusion'] ? '(resin)' : '(purchased roll)'; ?></label>
          <select name="input_item_id" class="form-control">
            <option value="">— select —</option>
            <?php if ($mode['extrusion']): ?><optgroup label="Raw materials"><?php foreach ($materials as $m): ?><option value="<?= $m->item_id; ?>"><?= htmlspecialchars($m->item_name); ?> (<?= format_qty($m->stock).' '.$m->unit_name; ?>)</option><?php endforeach; ?></optgroup><?php endif; ?>
            <optgroup label="Film rolls"><?php foreach ($rolls as $m): ?><option value="<?= $m->item_id; ?>"><?= htmlspecialchars($m->item_name); ?> (<?= format_qty($m->stock); ?> rolls)</option><?php endforeach; ?></optgroup>
          </select></div>
        <div><label>Film roll item (extrusion output / conversion input)</label>
          <select name="roll_item_id" class="form-control">
            <option value="">— same as input —</option>
            <?php foreach ($rolls as $m): ?><option value="<?= $m->item_id; ?>"><?= htmlspecialchars($m->item_name); ?></option><?php endforeach; ?>
          </select></div>
        <div><label>Planned input qty</label><input type="number" step="any" name="planned_input_qty" class="form-control" placeholder="e.g. kg of resin"></div>
        <div><label>Factory / warehouse</label>
          <select name="warehouse_id" class="form-control">
            <?php foreach ($warehouses as $w): ?><option value="<?= $w->id; ?>"><?= htmlspecialchars($w->warehouse_name); ?></option><?php endforeach; ?>
          </select></div>
        <div><label>Due date</label><input type="date" name="due_date" class="form-control"></div>
        <div><label>Priority</label>
          <select name="priority" class="form-control"><option value="normal">Normal</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
        <div><label>Est. material cost</label><input type="number" step="any" name="est_material_cost" class="form-control"></div>
        <div><label>Est. other cost</label><input type="number" step="any" name="est_other_cost" class="form-control"></div>
        <?php if ($mode['conversion']): ?><div><label>&nbsp;</label><label style="font-weight:400;"><input type="checkbox" name="print" value="1"> Job includes printing</label></div><?php endif; ?>
      </div>
      <div><label>Notes</label><input type="text" name="notes" class="form-control"></div>
      <p class="mp-muted" style="font-size:12px;margin-top:8px;">The stage plan is built automatically from the factory mode and the product spec: material allocation<?= $mode['extrusion'] ? ' → extrusion' : ''; ?><?= $mode['conversion'] ? ' → printing (if printed) → cutting/sealing → packing' : ''; ?> → quality check. Finished stock is credited only at QC approval.</p>
      <div style="margin-top:10px;"><button type="submit" class="mp-qa-btn"><i class="fa fa-save"></i> Create Job</button></div>
      <div id="nyJobMsg" style="margin-top:8px;font-size:13px;"></div>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="mp-card-form">
  <div class="mp-card-head"><h3><i class="fa fa-list"></i> Jobs</h3></div>
  <div class="mp-card-body" style="padding:0!important;">
    <table class="mp-static-table">
      <thead><tr><th>Job</th><th>Product</th><th>Source</th><th>Pipeline</th><th class="text-right">Planned</th><th>Due</th><th>Status</th><th>Factory</th><th></th></tr></thead>
      <tbody>
      <?php if (empty($jobs)): ?><tr><td colspan="9" style="padding:20px;color:var(--mp-muted);">No jobs match this filter.</td></tr><?php endif; ?>
      <?php foreach ($jobs as $j): $late = $j->due_date && $j->due_date < date('Y-m-d') && !in_array($j->status,['completed','cancelled']); ?>
        <tr>
          <td><a href="<?= base_url('nylon/job_view/'.$j->id); ?>"><strong><?= htmlspecialchars($j->job_code); ?></strong></a></td>
          <td><small><?= htmlspecialchars($j->item_name); ?></small></td>
          <td><small><?= $j->order_code ? htmlspecialchars($j->order_code.' — '.$j->customer_name) : '<em>Stock run</em>'; ?></small></td>
          <td><small class="text-muted"><?= htmlspecialchars(str_replace(',', ' → ', $j->pipeline ?: '')); ?></small></td>
          <td class="text-right"><?= format_qty($j->planned_qty); ?></td>
          <td><small class="<?= $late?'text-danger':''; ?>"><?= $j->due_date ? show_date($j->due_date) : '—'; ?></small></td>
          <td><span class="label label-<?= Nylon_model::job_status_badge($j->status); ?>"><?= Nylon_model::job_status_label($j->status); ?></span></td>
          <td><small><?= htmlspecialchars($j->warehouse_name ?? ''); ?></small></td>
          <td><a href="<?= base_url('nylon/job_view/'.$j->id); ?>" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
var NY_CSRF_NAME = <?= json_encode($this->security->get_csrf_token_name()); ?>;
function nyJobOrderPicked(sel){
  var o = sel.options[sel.selectedIndex];
  if (o.value && o.dataset.item) {
    var p = document.getElementById('nyJobProduct');
    for (var i=0;i<p.options.length;i++){ if (p.options[i].value === o.dataset.item){ p.selectedIndex=i; break; } }
    if (o.dataset.qty) document.getElementById('nyJobQty').value = o.dataset.qty;
    if (o.dataset.unit) document.getElementById('nyJobUnit').value = o.dataset.unit;
  }
}
<?php if ($preselect_order): ?>nyJobOrderPicked(document.getElementById('nyJobOrder'));<?php endif; ?>
function nySaveJob(ev){
  ev.preventDefault();
  var f = document.getElementById('nyJobForm');
  var msg = document.getElementById('nyJobMsg');
  fetch('<?= base_url('nylon/job_save'); ?>', {method:'POST', body:new FormData(f)})
    .then(function(r){return r.json();})
    .then(function(d){
      msg.innerHTML = '<span class="'+(d.success?'text-success':'text-danger')+'">'+d.message+'</span>';
      if (d.csrf_hash) { f.querySelector('input[name="'+NY_CSRF_NAME+'"]').value = d.csrf_hash; }
      if (d.success && d.id) setTimeout(function(){ location.href='<?= base_url('nylon/job_view'); ?>/'+d.id; }, 700);
    });
  return false;
}
</script>
