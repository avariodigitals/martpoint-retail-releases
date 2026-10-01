<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $j = $job ?? null; $pe = $prefill_equipment ?? null; ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Assign an engineer, link the equipment unit and schedule the visit</div>
  </div>
  <a href="<?= base_url('operations/service_jobs'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> All Jobs</a>
</div>

<div class="mp-card-form" style="margin-top:16px;max-width:960px;">
  <div class="mp-card-body">
    <form id="jobForm" class="mp-form-grid">
      <input type="hidden" name="job_id" value="<?= $j ? (int)$j->id : 0; ?>">

      <div class="mp-form-group">
        <label>Job type <span class="text-danger">*</span></label>
        <select name="job_type" class="mp-form-control">
          <?php foreach($job_types as $t): ?>
          <option value="<?= $t; ?>" <?= ($j && $j->job_type === $t) ? 'selected' : ''; ?>><?= ucfirst($t); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group">
        <label>Priority</label>
        <select name="priority" class="mp-form-control">
          <?php foreach(array('normal','high','urgent') as $p): ?>
          <option value="<?= $p; ?>" <?= ($j && $j->priority === $p) ? 'selected' : ''; ?>><?= ucfirst($p); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mp-form-group" style="grid-column:span 2;">
        <label><?= mp_label('customer'); ?> <span class="text-danger">*</span></label>
        <select name="customer_id" id="job_customer" class="mp-form-control" required>
          <option value="">— Select <?= strtolower(mp_label('customer')); ?> —</option>
          <?php foreach($customers as $c): ?>
          <option value="<?= $c->id; ?>" <?= (($j && $j->customer_id == $c->id) || (!$j && !empty($prefill_customer_id) && $prefill_customer_id == $c->id)) ? 'selected' : ''; ?>><?= htmlspecialchars($c->customer_name); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mp-form-group">
        <label>Equipment unit</label>
        <select name="equipment_id" id="job_equipment" class="mp-form-control">
          <option value="">— Not equipment-specific —</option>
          <?php if($pe): ?>
          <option value="<?= $pe->id; ?>" selected><?= htmlspecialchars(($pe->item_name ?? $pe->model).' — '.$pe->serial_number); ?></option>
          <?php elseif($j && $j->equipment_id): ?>
          <option value="<?= $j->equipment_id; ?>" selected><?= htmlspecialchars(($j->equipment_item ?? '').' — '.($j->equipment_serial ?? '')); ?></option>
          <?php endif; ?>
        </select>
      </div>
      <div class="mp-form-group">
        <label>Site</label>
        <select name="site_id" id="job_site" class="mp-form-control">
          <option value="">— Customer primary address —</option>
          <?php if($pe && $pe->site_id): ?>
          <option value="<?= $pe->site_id; ?>" selected><?= htmlspecialchars($pe->site_name ?? 'Site '.$pe->site_id); ?></option>
          <?php elseif($j && $j->site_id): ?>
          <option value="<?= $j->site_id; ?>" selected><?= htmlspecialchars($j->site_name ?? 'Site '.$j->site_id); ?></option>
          <?php endif; ?>
        </select>
      </div>

      <div class="mp-form-group">
        <label>Assign engineer</label>
        <select name="assigned_user_id" class="mp-form-control">
          <option value="">— Unassigned —</option>
          <?php foreach($engineers as $u): ?>
          <option value="<?= $u->id; ?>" <?= ($j && $j->assigned_user_id == $u->id) ? 'selected' : ''; ?>><?= htmlspecialchars($u->username); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group">
        <label>Status</label>
        <select name="status" class="mp-form-control">
          <?php foreach($job_statuses as $st): ?>
          <option value="<?= $st; ?>" <?= ($j && $j->status === $st) ? 'selected' : ((!$j && $st === 'scheduled') ? 'selected' : ''); ?>><?= ucwords(str_replace('_',' ',$st)); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mp-form-group">
        <label>Scheduled date</label>
        <input type="text" name="scheduled_date" class="mp-form-control datepicker" readonly value="<?= ($j && is_valid_date($j->scheduled_date)) ? show_date($j->scheduled_date) : ''; ?>">
      </div>
      <div class="mp-form-group">
        <label>Scheduled time</label>
        <input type="text" name="scheduled_time" class="mp-form-control" placeholder="e.g. 10:30" value="<?= $j->scheduled_time ?? ''; ?>">
      </div>

      <div class="mp-form-group" style="grid-column:span 2;">
        <label>Title</label>
        <input type="text" name="title" class="mp-form-control" placeholder="e.g. Install hematology analyzer — haematology lab" value="<?= $j ? ($j->title ?? '') : ($pe ? 'Service — '.htmlspecialchars($pe->item_name ?? '') : ''); ?>">
      </div>

      <div class="mp-form-group" style="grid-column:span 2;">
        <label>Description / work requested</label>
        <textarea name="description" class="mp-form-control" rows="3" placeholder="Scope of work, access notes, customer request…"><?= $j->description ?? ''; ?></textarea>
      </div>

      <div class="mp-form-group">
        <label>Labour charge</label>
        <input type="text" name="labour_charge" class="mp-form-control" value="<?= $j ? store_number_format($j->labour_charge, 0) : '0'; ?>">
      </div>
      <div class="mp-form-group">
        <label>Linked invoice</label>
        <input type="number" name="sales_id" class="mp-form-control" placeholder="Sales ID (optional)" value="<?= $j ? (int)$j->sales_id : ($pe ? (int)$pe->sales_id : ''); ?>">
      </div>

      <div class="mp-form-group" style="display:flex;align-items:flex-end;gap:8px;">
        <button type="button" class="mp-qa-btn green" onclick="saveJob()"><i class="fa fa-check"></i> <?= $j ? 'Update Job' : 'Create Job'; ?></button>
      </div>
    </form>
  </div>
</div>

<script>
function saveJob(){
  if(!$('#job_customer').val()){ toastr.error('Please select a <?= strtolower(mp_label('customer')); ?>.'); return; }
  $.post(base_url+'operations/service_job_save', $("#jobForm").serialize() + '&csrf_test_name='+csrf_token, function(res){
    if(res.indexOf('success') === 0){
      var parts = res.split('<<<###>>>');
      toastr.success('Service job saved.');
      window.location = base_url+'operations/service_job_view/'+(parts[1] || '');
    } else { toastr.error(res); }
  });
}
function loadCustomerLinked(customer_id){
  $.getJSON(base_url+'operations/ajax_customer_equipment', {customer_id: customer_id}, function(rows){
    var $e = $('#job_equipment'); var keep = $e.val();
    $e.html('<option value="">— Not equipment-specific —</option>');
    $.each(rows, function(i, r){ $e.append($('<option>').val(r.id).text((r.item_name || r.model || 'Unit')+' — '+(r.serial_number || ''))); });
    if(keep){ $e.val(keep); }
  });
  $.getJSON(base_url+'operations/ajax_customer_sites', {customer_id: customer_id}, function(rows){
    var $s = $('#job_site'); var keep = $s.val();
    $s.html('<option value="">— Customer primary address —</option>');
    $.each(rows, function(i, r){ $s.append($('<option>').val(r.id).text(r.label)); });
    if(keep){ $s.val(keep); }
  });
}
$('#job_customer').on('change', function(){ loadCustomerLinked($(this).val()); });
$(function(){
  if($.fn.datepicker){ $('.datepicker').datepicker({autoclose:true, format:'dd-mm-yyyy'}); }
  if($('#job_customer').val()){ loadCustomerLinked($('#job_customer').val()); }
});
</script>
