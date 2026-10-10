<?php $this->load->view('admin/desktop/_styles'); $CI =& get_instance(); ?>
<style>
.ib-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:14px!important}
.ib-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 10px 0!important}
.ib-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px}
.ib-bed{border:1px solid var(--mp-border);border-radius:12px;padding:12px;text-align:center;font-size:12px}
.ib-bed .lbl{font-weight:800;font-size:14px}
.ib-bed .st{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;margin-top:4px}
.ib-available{border-color:#059669;background:#ECFDF5;color:#065F46}
.ib-occupied{border-color:#2563EB;background:#EFF6FF;color:#1E40AF}
.ib-held{border-color:#D97706;background:#FFFBEB;color:#92400E}
.ib-maintenance{border-color:#6B7280;background:#F9FAFB;color:#6B7280}
.ib-inp{padding:6px 9px!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;font-size:12px!important;margin-right:6px!important;background:var(--mp-bg,#fff)!important;color:inherit!important}
</style>

<div class="mp-page-head">
  <div><h2>Bed Board</h2>
  <div class="mp-page-sub">Ward occupancy, patient admissions and bed accounts.</div></div>
  <div><a class="mp-qa-btn" href="<?= base_url('inpatient'); ?>">Admissions</a></div>
</div>

<?php
$byWard = array();
foreach($beds as $b){ $byWard[$b->ward_id][] = $b; }
foreach($wards as $w): ?>
<div class="ib-box">
  <h4><?= htmlspecialchars($w->name); ?></h4>
  <div class="ib-grid">
    <?php foreach(($byWard[$w->id] ?? array()) as $b): ?>
    <div class="ib-bed ib-<?= htmlspecialchars($b->status); ?>">
      <div class="lbl"><?= htmlspecialchars($b->bed_label); ?></div>
      <div class="st"><?= htmlspecialchars($b->status); ?></div>
      <?php if($b->patient_name): ?><div style="margin-top:4px"><?= htmlspecialchars($b->patient_name); ?><br><small>ADM #<?= (int)$b->admission_id; ?></small></div><?php endif; ?>
      <?php if($b->daily_rate !== null): ?><div style="margin-top:4px;font-size:10px;color:var(--mp-muted)"><?= $CI->currency($b->daily_rate); ?>/day</div><?php endif; ?>
      <?php if($b->admission_id && $can['open']): ?><a style="display:block;margin-top:8px" href="<?= base_url('inpatient/view/'.(int)$b->admission_id); ?>">Open admission</a><?php endif; ?>
      <?php if($can['finance'] && $b->payment_account_id): ?><a style="display:block;margin-top:8px" href="<?= base_url('inpatient/bed_ledger/'.(int)$b->payment_account_id); ?>">Bed account</a><?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php if(empty($byWard[$w->id])): ?><div style="color:var(--mp-muted);font-size:12px">No beds in this ward.</div><?php endif; ?>
  </div>
</div>
<?php endforeach; ?>
<?php if(empty($wards)): ?><div class="ib-box" style="color:var(--mp-muted)">No wards configured yet.</div><?php endif; ?>

<?php if($can['manage']): ?>
<div class="ib-box">
  <h4>Add ward / bed</h4>
  <form style="display:inline" onsubmit="return ibPost(event,'inpatient/save_ward','Ward saved')">
    <input class="ib-inp" name="name" placeholder="Ward name" required><button class="mp-qa-btn">Add ward</button>
  </form>
  <form style="display:inline" onsubmit="return ibPost(event,'inpatient/save_bed','Bed saved')">
    <select class="ib-inp" name="ward_id" required>
      <?php foreach($wards as $w): ?><option value="<?= (int)$w->id; ?>"><?= htmlspecialchars($w->name); ?></option><?php endforeach; ?>
    </select>
    <input class="ib-inp" name="label" placeholder="Bed label" required>
    <input class="ib-inp" name="daily_rate" placeholder="Rate override (opt)" style="width:140px">
    <button class="mp-qa-btn green">Add bed</button>
  </form>
</div>
<script>
function ibPost(e, url, ok){ e.preventDefault(); var fd = new FormData(e.target);
  fetch('<?= base_url(); ?>'+url, {method:'POST', body:fd}).then(r=>r.json())
    .then(d=>{ alert(d.message||ok); if(d.status==='success') location.reload(); }); return false; }
</script>
<?php endif; ?>
