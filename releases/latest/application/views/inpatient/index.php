<?php $this->load->view('admin/desktop/_styles'); $CI =& get_instance(); ?>
<style>
.ip-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:14px!important}
.ip-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 10px 0!important}
.ip-table{width:100%!important;border-collapse:collapse!important;font-size:12px!important}
.ip-table th{text-align:left!important;padding:5px 8px!important;border-bottom:1px solid var(--mp-border)!important;font-size:10px!important;text-transform:uppercase!important;color:var(--mp-muted)!important}
.ip-table td{padding:6px 8px!important;border-bottom:1px solid #F1F5F9!important;vertical-align:top!important}
.ip-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:800;text-transform:uppercase}
.ip-active{background:#D1FAE5;color:#065F46}.ip-discharged{background:#DBEAFE;color:#1E40AF}
.ip-deceased{background:#111827;color:#F9FAFB}.ip-transferred_out{background:#FEF3C7;color:#92400E}
.ip-inp{padding:7px 10px!important;border:1px solid var(--mp-border)!important;border-radius:9px!important;font-size:12px!important;width:100%!important;margin-bottom:8px!important;background:var(--mp-bg,#fff)!important;color:inherit!important}
.ip-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
</style>

<div class="mp-page-head">
  <div><h2>Admissions</h2>
  <div class="mp-page-sub">Inpatient stays — bed allocation, nursing care, daily billing and closure.</div></div>
  <div><a class="mp-qa-btn blue" href="<?= base_url('inpatient/beds'); ?>">Bed board</a>
       <a class="mp-qa-btn" href="<?= base_url('inpatient/tasks'); ?>">Ward tasks</a>
       <a class="mp-qa-btn" href="<?= base_url('inpatient/referrals'); ?>">Referrals</a>
       <?php if($can['manage']): ?><a class="mp-qa-btn green" href="#adm-form">New admission</a><?php endif; ?></div>
</div>

<div class="ip-box">
  <h4>Register</h4>
  <table class="ip-table">
    <tr><th>Admission</th><th>Patient</th><th>Admitted</th><th>Ward / Bed</th><th>Package decision</th><th>Status</th><th>Closed</th><th></th></tr>
    <?php foreach($admissions as $a): ?>
    <tr>
      <td><strong><?= htmlspecialchars($a->admission_code); ?></strong></td>
      <td><?= htmlspecialchars(($a->patient_code ?? '') . ' ' . ($a->full_name ?? '')); ?></td>
      <td><?= htmlspecialchars($a->admitted_at); ?></td>
      <td><?= htmlspecialchars(trim(($a->ward_name ?? '—') . ' / ' . ($a->bed_label ?? '—'))); ?></td>
      <td><?= htmlspecialchars($a->outpatient_decision); ?></td>
      <td><span class="ip-badge ip-<?= htmlspecialchars($a->status); ?>"><?= htmlspecialchars($a->status); ?></span></td>
      <td><?= htmlspecialchars($a->closed_at ?? '—'); ?></td>
      <td><a class="mp-qa-btn" href="<?= base_url('inpatient/view/' . (int)$a->id); ?>">Open</a></td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($admissions)): ?><tr><td colspan="8" style="color:var(--mp-muted)">No admissions yet.</td></tr><?php endif; ?>
  </table>
</div>

<?php if($can['manage']): ?>
<div class="ip-box" id="adm-form">
  <h4>New admission — existing patient</h4>
  <form onsubmit="return ipAdmit(event)">
    <div class="ip-grid">
      <div>
        <select class="ip-inp" name="patient_id" required>
          <option value="">— Patient —</option>
          <?php foreach($patients as $p): ?>
            <option value="<?= (int)$p->id; ?>"><?= htmlspecialchars($p->patient_code . ' — ' . $p->full_name); ?></option>
          <?php endforeach; ?>
        </select>
        <select class="ip-inp" name="bed_id">
          <option value="">— Assign bed now (optional) —</option>
          <?php foreach($beds as $b): ?>
            <option value="<?= (int)$b->id; ?>"><?= htmlspecialchars('Bed ' . $b->bed_label . ' (ward ' . $b->ward_id . ')'); ?></option>
          <?php endforeach; ?>
        </select>
        <input class="ip-inp" name="admitted_at" placeholder="Admitted at (Y-m-d H:i, blank = now)">
      </div>
      <div>
        <input class="ip-inp" name="reason" placeholder="Admission reason" required>
        <textarea class="ip-inp" name="care_plan" placeholder="Care plan" rows="2"></textarea>
        <select class="ip-inp" name="outpatient_decision" required>
          <option value="">— Outpatient packages decision (required) —</option>
          <option value="continue">Continue outpatient packages</option>
          <option value="pause">Pause packages during admission (resume at discharge)</option>
          <option value="replace">Replace packages — entitlements end, reservations release</option>
        </select>
        <input class="ip-inp" name="outpatient_decision_note" placeholder="Decision note (optional)">
      </div>
    </div>
    <button class="mp-qa-btn green" type="submit">Admit</button>
  </form>
</div>
<script>
function ipAdmit(e){
  e.preventDefault();
  var fd = new FormData(e.target);
  fetch('<?= base_url('inpatient/admit'); ?>', {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); });
  return false;
}
</script>
<?php endif; ?>
