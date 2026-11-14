<?php $this->load->view('admin/desktop/_styles'); ?>
<?php
  $warranty_ok = !empty($eq->warranty_end) && $eq->warranty_end >= date('Y-m-d');
  $cal_overdue = !empty($eq->next_calibration_date) && $eq->next_calibration_date < date('Y-m-d');
?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($eq->item_name ?? $eq->model ?? 'Equipment'); ?></h2>
    <div class="mp-page-sub">
      Serial <code><?= htmlspecialchars($eq->serial_number ?? '—'); ?></code>
      &middot; <a href="<?= base_url('customers/profile/'.$eq->customer_id); ?>"><?= htmlspecialchars($eq->customer_name ?? '-'); ?></a>
      <?php if(!empty($eq->sales_code)): ?> &middot; Invoice <a href="<?= base_url('sales/invoice/'.$eq->sales_id); ?>"><?= htmlspecialchars($eq->sales_code); ?></a><?php endif; ?>
    </div>
  </div>
  <div>
    <a href="<?= base_url('operations/equipment_register'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Register</a>
    <a href="<?= base_url('operations/service_job_form?equipment_id='.$eq->id); ?>" class="mp-qa-btn green"><i class="fa fa-wrench"></i> New Service Job</a>
    <button class="mp-qa-btn purple" onclick="scheduleCalibration()"><i class="fa fa-sliders"></i> Schedule Calibration</button>
  </div>
</div>

<div class="mp-kpi-grid" style="grid-template-columns:repeat(4,1fr);margin-top:16px;">
  <div class="mp-kpi-card summary">
    <div class="mp-kpi-icon"><i class="fa fa-map-marker"></i></div>
    <div class="mp-kpi-label">Site</div>
    <div class="mp-kpi-value" style="font-size:15px;"><?= htmlspecialchars($eq->site_name ?? ($eq->site_address ? trim($eq->site_address.', '.$eq->site_city) : 'Customer primary')); ?></div>
  </div>
  <div class="mp-kpi-card sales">
    <div class="mp-kpi-icon"><i class="fa fa-calendar-check-o"></i></div>
    <div class="mp-kpi-label">Installed / Commissioned</div>
    <div class="mp-kpi-value" style="font-size:15px;"><?= is_valid_date($eq->install_date) ? show_date($eq->install_date) : '—'; ?> / <?= is_valid_date($eq->commissioned_date) ? show_date($eq->commissioned_date) : '—'; ?></div>
  </div>
  <div class="mp-kpi-card <?= $warranty_ok ? 'success' : 'debt'; ?>">
    <div class="mp-kpi-icon"><i class="fa fa-shield"></i></div>
    <div class="mp-kpi-label">Warranty (<?= htmlspecialchars($eq->coverage_type ?? 'standard'); ?>)</div>
    <div class="mp-kpi-value" style="font-size:15px;"><?= !empty($eq->warranty_end) ? show_date($eq->warranty_end) : '—'; ?> <?= $warranty_ok ? '<span class="mp-pill paid">active</span>' : ''; ?></div>
  </div>
  <div class="mp-kpi-card <?= $cal_overdue ? 'debt' : 'success'; ?>">
    <div class="mp-kpi-icon"><i class="fa fa-sliders"></i></div>
    <div class="mp-kpi-label">Next Calibration</div>
    <div class="mp-kpi-value" style="font-size:15px;"><?= !empty($eq->next_calibration_date) ? show_date($eq->next_calibration_date) : '—'; ?> <?= $cal_overdue ? '<span class="mp-pill unpaid">overdue</span>' : ''; ?></div>
  </div>
</div>

<div class="mp-grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;">
  <!-- Edit card -->
  <div class="mp-card-form">
    <div class="mp-card-head"><h3><i class="fa fa-edit"></i> Register Details</h3></div>
    <div class="mp-card-body">
      <form id="eqForm" class="mp-form-grid">
        <input type="hidden" name="equipment_id" value="<?= (int)$eq->id; ?>">
        <div class="mp-form-group">
          <label>Service / delivery site</label>
          <select name="site_id" class="mp-form-control">
            <option value="">— Customer primary address —</option>
            <?php foreach($sites as $s): ?>
            <option value="<?= $s->id; ?>" <?= ($eq->site_id == $s->id) ? 'selected' : ''; ?>><?= htmlspecialchars(trim(($s->site_name ? $s->site_name.' — ' : '').$s->address)); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mp-form-group">
          <label>Installation date</label>
          <input type="text" name="install_date" class="mp-form-control datepicker" readonly value="<?= is_valid_date($eq->install_date) ? show_date($eq->install_date) : ''; ?>">
        </div>
        <div class="mp-form-group">
          <label>Commissioning date</label>
          <input type="text" name="commissioned_date" class="mp-form-control datepicker" readonly value="<?= is_valid_date($eq->commissioned_date) ? show_date($eq->commissioned_date) : ''; ?>">
        </div>
        <div class="mp-form-group">
          <label>Status</label>
          <select name="equipment_status" class="mp-form-control">
            <?php foreach(array('delivered','installed','operational','under_repair','decommissioned') as $st): ?>
            <option value="<?= $st; ?>" <?= ($eq->equipment_status === $st) ? 'selected' : ''; ?>><?= ucwords(str_replace('_',' ',$st)); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mp-form-group">
          <label>Coverage</label>
          <input type="text" name="coverage_type" class="mp-form-control" value="<?= $eq->coverage_type ?? 'standard'; ?>" placeholder="standard / parts+labour / SLA">
        </div>
        <div class="mp-form-group">
          <label>Warranty start / end</label>
          <div style="display:flex;gap:8px;">
            <input type="text" name="warranty_start" class="mp-form-control datepicker" readonly value="<?= is_valid_date($eq->warranty_start) ? show_date($eq->warranty_start) : ''; ?>">
            <input type="text" name="warranty_end" class="mp-form-control datepicker" readonly value="<?= is_valid_date($eq->warranty_end) ? show_date($eq->warranty_end) : ''; ?>">
          </div>
        </div>
        <div class="mp-form-group">
          <label>Calibration interval (months)</label>
          <input type="number" name="calibration_interval_months" class="mp-form-control" min="1" value="<?= (int)($eq->calibration_interval_months ?? 12); ?>">
        </div>
        <div class="mp-form-group">
          <label>Next calibration due</label>
          <input type="text" name="next_calibration_date" class="mp-form-control datepicker" readonly value="<?= is_valid_date($eq->next_calibration_date) ? show_date($eq->next_calibration_date) : ''; ?>">
        </div>
        <div class="mp-form-group" style="grid-column:span 2;">
          <label>Notes</label>
          <textarea name="notes" class="mp-form-control" rows="2"><?= $eq->notes ?? ''; ?></textarea>
        </div>
        <div class="mp-form-group" style="display:flex;align-items:flex-end;">
          <button type="button" class="mp-qa-btn green" onclick="saveEquipment()"><i class="fa fa-check"></i> Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Service history -->
  <div class="mp-card-form">
    <div class="mp-card-head"><h3><i class="fa fa-wrench"></i> Service History</h3></div>
    <div class="mp-card-body" style="padding:0;">
      <table class="table mp-dt-table" style="margin:0;">
        <thead><tr><th>Job</th><th>Type</th><th>Scheduled</th><th>Engineer</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php if(!empty($jobs)): foreach($jobs as $j): ?>
          <tr>
            <td><code><?= htmlspecialchars($j->job_code); ?></code></td>
            <td><?= ucfirst($j->job_type); ?></td>
            <td><?= is_valid_date($j->scheduled_date) ? show_date($j->scheduled_date) : '—'; ?></td>
            <td><?= htmlspecialchars($j->engineer ?? '—'); ?></td>
            <td><span class="mp-pill <?= $j->status === 'completed' ? 'paid' : ($j->status === 'cancelled' ? 'unpaid' : 'partial'); ?>"><?= ucwords(str_replace('_',' ',$j->status)); ?></span></td>
            <td><a href="<?= base_url('operations/service_job_view/'.$j->id); ?>" class="mp-qa-btn teal" style="padding:4px 8px;"><i class="fa fa-eye"></i></a></td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="6" class="text-center text-muted" style="padding:24px;">No service jobs yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function saveEquipment(){
  $.post(base_url+'operations/equipment_save', $("#eqForm").serialize() + '&csrf_test_name='+csrf_token, function(res){
    if(res === 'success'){ toastr.success('Equipment record updated.'); }
    else { toastr.error(res); }
  });
}
function scheduleCalibration(){
  var d = prompt('Calibration date (dd-mm-yyyy):', '<?= !empty($eq->next_calibration_date) ? show_date($eq->next_calibration_date) : show_date(date('Y-m-d', strtotime('+12 months'))); ?>');
  if(!d){ return; }
  $.post(base_url+'operations/equipment_schedule_calibration/<?= (int)$eq->id; ?>', {scheduled_date: d, csrf_test_name: csrf_token}, function(res){
    if(res.indexOf('success') === 0){ var parts = res.split('<<<###>>>'); toastr.success('Calibration job scheduled.'); if(parts[1]){ window.location = base_url+'operations/service_job_view/'+parts[1]; } else { location.reload(); } }
    else { toastr.error(res); }
  });
}
$(function(){
  if($.fn.datepicker){ $('.datepicker').datepicker({autoclose:true, format:'dd-mm-yyyy'}); }
});
</script>
