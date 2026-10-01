<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $closed = in_array($job->status, array('completed','cancelled')); ?>

<div class="mp-page-head">
  <div>
    <h2><code><?= htmlspecialchars($job->job_code); ?></code> — <?= $job->title; ?></h2>
    <div class="mp-page-sub">
      <?= ucfirst($job->job_type); ?> &middot; <a href="<?= base_url('customers/profile/'.$job->customer_id); ?>"><?= htmlspecialchars($job->customer_name ?? '-'); ?></a>
      <?php if($job->equipment_serial): ?> &middot; <a href="<?= base_url('operations/equipment_view/'.$job->equipment_id); ?>"><code><?= htmlspecialchars($job->equipment_serial); ?></code></a><?php endif; ?>
      <?php if($job->sales_code): ?> &middot; Invoice <a href="<?= base_url('sales/invoice/'.$job->sales_id); ?>"><?= htmlspecialchars($job->sales_code); ?></a><?php endif; ?>
    </div>
  </div>
  <div>
    <a href="<?= base_url('operations/service_jobs'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> All Jobs</a>
    <?php if(!$closed): ?>
    <a href="<?= base_url('operations/service_job_form/'.$job->id); ?>" class="mp-qa-btn purple"><i class="fa fa-edit"></i> Edit</a>
    <?php endif; ?>
  </div>
</div>

<div class="mp-kpi-grid" style="grid-template-columns:repeat(4,1fr);margin-top:16px;">
  <div class="mp-kpi-card summary">
    <div class="mp-kpi-icon"><i class="fa fa-flag"></i></div>
    <div class="mp-kpi-label">Status / Priority</div>
    <div class="mp-kpi-value" style="font-size:15px;"><?= ucwords(str_replace('_',' ',$job->status)); ?> &middot; <?= ucfirst($job->priority); ?></div>
  </div>
  <div class="mp-kpi-card sales">
    <div class="mp-kpi-icon"><i class="fa fa-user-md"></i></div>
    <div class="mp-kpi-label">Engineer</div>
    <div class="mp-kpi-value" style="font-size:15px;"><?= htmlspecialchars($job->engineer ?? 'Unassigned'); ?></div>
  </div>
  <div class="mp-kpi-card summary">
    <div class="mp-kpi-icon"><i class="fa fa-calendar"></i></div>
    <div class="mp-kpi-label">Scheduled</div>
    <div class="mp-kpi-value" style="font-size:15px;"><?= is_valid_date($job->scheduled_date) ? show_date($job->scheduled_date) : '—'; ?> <?= $job->scheduled_time ? htmlspecialchars($job->scheduled_time) : ''; ?></div>
  </div>
  <div class="mp-kpi-card success">
    <div class="mp-kpi-icon"><i class="fa fa-money"></i></div>
    <div class="mp-kpi-label">Labour / Parts</div>
    <div class="mp-kpi-value" style="font-size:15px;"><?= store_number_format($job->labour_charge); ?> / <?= store_number_format($job->parts_total ?? 0); ?></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;">
  <!-- Visits -->
  <div class="mp-card-form">
    <div class="mp-card-head"><h3><i class="fa fa-sticky-note"></i> Visit Notes</h3></div>
    <div class="mp-card-body">
      <?php if(!$closed): ?>
      <form id="visitForm" class="mp-form-grid" style="margin-bottom:16px;">
        <input type="hidden" name="job_id" value="<?= (int)$job->id; ?>">
        <div class="mp-form-group">
          <label>Visit date</label>
          <input type="text" name="visit_date" class="mp-form-control datepicker" readonly value="<?= show_date(date('Y-m-d')); ?>">
        </div>
        <div class="mp-form-group">
          <label>Engineer on site</label>
          <select name="engineer_id" class="mp-form-control">
            <?php foreach($engineers as $u): ?>
            <option value="<?= $u->id; ?>" <?= ($job->assigned_user_id == $u->id) ? 'selected' : ''; ?>><?= htmlspecialchars($u->username); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mp-form-group" style="grid-column:span 2;">
          <label>Notes</label>
          <textarea name="visit_notes" class="mp-form-control" rows="2" placeholder="Work done, findings, readings…"></textarea>
        </div>
        <div class="mp-form-group">
          <label>Outcome</label>
          <select name="outcome" class="mp-form-control">
            <option value="work_done">Work done</option>
            <option value="follow_up_required">Follow-up required</option>
            <option value="parts_required">Parts required</option>
            <option value="customer_unavailable">Customer unavailable</option>
          </select>
        </div>
        <div class="mp-form-group" style="display:flex;align-items:flex-end;">
          <button type="button" class="mp-qa-btn green" onclick="saveVisit()"><i class="fa fa-plus"></i> Log Visit</button>
        </div>
      </form>
      <?php endif; ?>
      <?php if(!empty($visits)): foreach($visits as $v): ?>
      <div style="border-left:3px solid var(--mp-primary);padding:8px 12px;margin-bottom:10px;background:var(--mp-surface);border-radius:6px;">
        <div style="font-size:12px;color:var(--mp-muted);"><?= show_date($v->visit_date); ?> &middot; <?= htmlspecialchars($v->engineer_name ?? ''); ?> &middot; <em><?= ucwords(str_replace('_',' ',$v->outcome ?? '')); ?></em></div>
        <div style="margin-top:4px;"><?= nl2br($v->notes); ?></div>
      </div>
      <?php endforeach; else: ?>
      <p class="text-muted" style="text-align:center;padding:16px;">No visit notes logged.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Parts -->
  <div class="mp-card-form">
    <div class="mp-card-head"><h3><i class="fa fa-cogs"></i> Parts Used</h3></div>
    <div class="mp-card-body">
      <?php if(!$closed): ?>
      <form id="partForm" class="mp-form-grid" style="margin-bottom:16px;">
        <input type="hidden" name="job_id" value="<?= (int)$job->id; ?>">
        <input type="hidden" name="item_id" id="part_item_id">
        <div class="mp-form-group" style="grid-column:span 2;">
          <label>Part / consumable</label>
          <input type="text" id="part_item_search" class="mp-form-control" placeholder="Type item name…" autocomplete="off">
        </div>
        <div class="mp-form-group">
          <label>Qty</label>
          <input type="text" name="qty" class="mp-form-control" value="1">
        </div>
        <div class="mp-form-group">
          <label>Unit price</label>
          <input type="text" name="price_per_unit" id="part_price" class="mp-form-control" value="0">
        </div>
        <div class="mp-form-group" style="display:flex;align-items:flex-end;">
          <button type="button" class="mp-qa-btn green" onclick="savePart()"><i class="fa fa-plus"></i> Add Part</button>
        </div>
      </form>
      <?php endif; ?>
      <table class="table mp-dt-table" style="margin:0;">
        <thead><tr><th>Item</th><th>Serial</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
        <tbody>
          <?php if(!empty($parts)): foreach($parts as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p->item_name ?? '-'); ?></td>
            <td><?= $p->serial_number ? '<code>'.htmlspecialchars($p->serial_number).'</code>' : '—'; ?></td>
            <td><?= format_qty($p->qty); ?></td>
            <td><?= store_number_format($p->price_per_unit); ?></td>
            <td><?= store_number_format($p->total); ?></td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="5" class="text-center text-muted" style="padding:16px;">No parts recorded.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      <p class="mp-form-hint" style="margin-top:8px;">Parts are deducted from warehouse stock automatically via a stock-adjustment document.</p>
    </div>
  </div>
</div>

<?php if(!$closed): ?>
<div class="mp-card-form" style="margin-top:16px;">
  <div class="mp-card-head"><h3><i class="fa fa-tasks"></i> Update Status</h3></div>
  <div class="mp-card-body">
    <form id="statusForm" class="mp-form-grid">
      <input type="hidden" name="job_id" value="<?= (int)$job->id; ?>">
      <div class="mp-form-group">
        <label>New status</label>
        <select name="status" class="mp-form-control">
          <?php foreach($job_statuses as $st): ?>
          <option value="<?= $st; ?>" <?= ($job->status === $st) ? 'selected' : ''; ?>><?= ucwords(str_replace('_',' ',$st)); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group" style="grid-column:span 2;">
        <label>Resolution notes (for completed jobs)</label>
        <textarea name="resolution_notes" class="mp-form-control" rows="2" placeholder="Final readings, certificates issued, customer sign-off…"><?= $job->resolution_notes ?? ''; ?></textarea>
      </div>
      <div class="mp-form-group" style="display:flex;align-items:flex-end;">
        <button type="button" class="mp-qa-btn green" onclick="saveStatus()"><i class="fa fa-check"></i> Update Status</button>
      </div>
    </form>
    <p class="mp-form-hint">Completing an installation/commissioning job stamps the equipment register; completing a calibration job rolls the next calibration date forward by the configured interval.</p>
  </div>
</div>
<?php endif; ?>

<script>
function saveVisit(){
  $.post(base_url+'operations/service_job_visit_save', $("#visitForm").serialize() + '&csrf_test_name='+csrf_token, function(res){
    if(res === 'success'){ location.reload(); } else { toastr.error(res); }
  });
}
function savePart(){
  if(!$('#part_item_id').val()){ toastr.error('Pick a part from the search results.'); return; }
  $.post(base_url+'operations/service_job_part_save', $("#partForm").serialize() + '&csrf_test_name='+csrf_token, function(res){
    if(res === 'success'){ location.reload(); } else { toastr.error(res); }
  });
}
function saveStatus(){
  $.post(base_url+'operations/service_job_status', $("#statusForm").serialize() + '&csrf_test_name='+csrf_token, function(res){
    if(res === 'success'){ toastr.success('Status updated.'); location.reload(); } else { toastr.error(res); }
  });
}
$(function(){
  if($.fn.datepicker){ $('.datepicker').datepicker({autoclose:true, format:'dd-mm-yyyy'}); }
  $('#part_item_search').autocomplete({
    source: function(req, resp){
      $.getJSON(base_url+'operations/ajax_item_search', {term: req.term}, function(rows){ resp(rows); });
    },
    minLength: 2,
    select: function(ev, ui){
      $('#part_item_id').val(ui.item.id);
      $('#part_price').val(ui.item.price);
    }
  });
});
</script>
