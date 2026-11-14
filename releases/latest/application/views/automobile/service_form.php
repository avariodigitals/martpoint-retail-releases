<?php $this->load->helper('form'); ?>
<?php
  $is_edit = !empty($record);
  $tyre = !empty($tyre_ctx);
  $v = $vehicle;
  $vlabel = trim($v->make . ' ' . $v->model);
  if (!empty($v->license_plate)) $vlabel .= ' · ' . $v->license_plate;
  if (!empty($v->year)) $vlabel .= ' · ' . $v->year;
?>
<style>
  .veh-service .mp-card { background:#fff; border:1px solid #E5E7EB; border-radius:16px; overflow:hidden; margin-top:24px; }
  .veh-service .mp-card-head { padding:16px 20px; border-bottom:1px solid #E5E7EB; background:#F9FAFB; }
  .veh-service .mp-card-head h3 { margin:0; font-size:16px; font-weight:700; color:#111827; }
  .veh-service .mp-card-body { padding:20px; }
  .veh-service .mp-form-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
  .veh-service .form-group { display:flex; flex-direction:column; gap:6px; margin-bottom:0; }
  .veh-service .form-group label { font-size:13px; font-weight:600; color:#374151; }
  .veh-service .form-control { border-radius:10px; border:1px solid #D1D5DB; padding:10px 12px; font-size:14px; }
  .veh-service .form-control:focus { border-color:#2563EB; box-shadow:0 0 0 3px rgba(37,99,235,0.1); }
  .veh-service .mp-form-actions { margin-top:24px; display:flex; gap:12px; }
  .veh-service .mp-btn { display:inline-flex; align-items:center; gap:6px; padding:10px 22px; border-radius:8px; border:1px solid #D1D5DB; background:#fff; color:#374151; font-size:14px; font-weight:600; text-decoration:none; cursor:pointer; }
  .veh-service .mp-btn.green { background:#10B981; border-color:#10B981; color:#fff; }
  .veh-service .hint { font-size:12px; color:#6B7280; font-weight:400; }
  @media(max-width:1024px){ .veh-service .mp-form-grid { grid-template-columns:repeat(2,1fr); } }
  @media(max-width:767px){ .veh-service .mp-form-grid { grid-template-columns:1fr; } }
</style>
<div class="veh-service">
<?= form_open('automobile/service_save', ['class'=>'mp-form']); ?>
<input type="hidden" name="id" value="<?= $is_edit ? (int)$record->id : ''; ?>">
<input type="hidden" name="vehicle_id" value="<?= (int)$v->id; ?>">

<div class="mp-page-head">
  <div>
    <h2><?= $is_edit ? 'Edit Service Record' : 'Record Service'; ?></h2>
    <div class="mp-page-sub"><?= htmlspecialchars($vlabel); ?><?= $tyre ? ' — fitting and maintenance history for this customer vehicle' : ''; ?></div>
  </div>
  <a href="<?= base_url('automobile/history/' . (int)$v->customer_id); ?>" class="mp-btn" style="margin-left:auto;">
    <i class="fa fa-arrow-left"></i> Back to History
  </a>
</div>

<?php include APPPATH . 'views/comman/code_flashdata.php'; ?>

<div class="mp-card">
  <div class="mp-card-head"><h3>Work Performed</h3></div>
  <div class="mp-card-body">
    <div class="mp-form-grid">
      <div class="form-group">
        <label>Service <span class="text-danger">*</span></label>
        <select name="service_type" class="form-control" required>
          <?php foreach ($service_types as $key => $label): ?>
          <option value="<?= $key; ?>" <?= $is_edit && $record->service_type === $key ? 'selected' : ''; ?>><?= htmlspecialchars($label); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Date of Service <span class="text-danger">*</span></label>
        <input type="date" name="service_date" class="form-control" required value="<?= $is_edit ? htmlspecialchars((string)$record->service_date) : date('Y-m-d'); ?>">
      </div>
      <div class="form-group">
        <label>Mileage at Service (km)</label>
        <input type="number" name="mileage_at_service" class="form-control" min="0" placeholder="e.g. 45000" value="<?= $is_edit && $record->mileage_at_service !== null ? (int)$record->mileage_at_service : ''; ?>">
        <span class="hint">A higher reading updates the vehicle's recorded mileage.</span>
      </div>
      <div class="form-group">
        <label>Technician</label>
        <select name="technician_id" class="form-control">
          <option value="">— Not assigned —</option>
          <?php foreach ($technicians as $t): $tname = trim($t->first_name . ' ' . $t->last_name); ?>
          <option value="<?= (int)$t->id; ?>" <?= $is_edit && (int)$record->technician_id === (int)$t->id ? 'selected' : ''; ?>><?= htmlspecialchars($tname); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" style="grid-column:span 2;">
        <label>Notes</label>
        <input type="text" name="notes" class="form-control" maxlength="600" placeholder="Parts used, readings taken, advice given" value="<?= $is_edit ? htmlspecialchars((string)$record->notes) : ''; ?>">
      </div>
    </div>
  </div>
</div>

<div class="mp-card">
  <div class="mp-card-head"><h3>Next Due — set the reminder yourself</h3></div>
  <div class="mp-card-body">
    <div class="mp-form-grid">
      <div class="form-group">
        <label>Next Due Date</label>
        <input type="date" name="next_due_date" class="form-control" value="<?= $is_edit && !empty($record->next_due_date) ? htmlspecialchars((string)$record->next_due_date) : ''; ?>">
        <span class="hint">Customers get a reminder when this date comes within range. Left empty, nothing is scheduled.</span>
      </div>
      <div class="form-group">
        <label>Next Due Mileage (km)</label>
        <input type="number" name="next_due_mileage" class="form-control" min="0" placeholder="e.g. 50000" value="<?= $is_edit && $record->next_due_mileage !== null ? (int)$record->next_due_mileage : ''; ?>">
        <span class="hint">A counter prompt — reminders follow the date, not mileage.</span>
      </div>
    </div>
    <div class="mp-form-actions">
      <button type="submit" class="mp-btn green"><i class="fa fa-check"></i> <?= $is_edit ? 'Update Service Record' : 'Save Service Record'; ?></button>
      <a href="<?= base_url('automobile/history/' . (int)$v->customer_id); ?>" class="mp-btn">Cancel</a>
    </div>
  </div>
</div>
<?= form_close(); ?>
</div>
