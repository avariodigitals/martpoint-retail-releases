<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Patient accounts — patient picker.
 *
 * The funds dashboard is per-patient, so this is the landing for the "Patient
 * accounts" nav entry: choose a patient, then the ledger, reservations,
 * payment evidence, entitlements and bills open on the real dashboard.
 */
$rows = isset($patients) && is_array($patients) ? $patients : array();
$openingCount = (int)($openings_pending ?? 0);
?>
<style>
.pa-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px}
.pa-head h1{margin:0 0 5px;font:400 28px/1.15 'DM Serif Display',Georgia,serif;color:var(--mp-text)}
.pa-head p{margin:0;color:var(--mp-muted);font-size:14px}
.pa-search{display:flex;gap:8px;margin-bottom:18px}
.pa-search input{flex:1;min-width:0;padding:11px 14px;border:1px solid var(--mp-border);border-radius:9px;font-size:14px}
.pa-search button{padding:11px 18px;border:0;border-radius:9px;background:var(--mp-primary);color:#fff;font-size:14px;font-weight:600}
.pa-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:10px}
.pa-item{display:flex;align-items:center;gap:12px;padding:13px 14px;border:1px solid var(--mp-border);border-radius:11px;background:var(--mp-surface);color:var(--mp-text);text-decoration:none}
.pa-item:hover{border-color:#91b9a6;background:#f7fbf9;color:var(--mp-text);text-decoration:none}
.pa-avatar{width:38px;height:38px;flex:0 0 38px;display:grid;place-items:center;border-radius:50%;background:#e3efe9;color:#104d40;font-weight:700;font-size:14px}
.pa-name{font-size:14px;font-weight:600;display:block}
.pa-meta{font-size:12px;color:var(--mp-muted);margin-top:2px;display:block}
.pa-empty{padding:26px;text-align:center;color:var(--mp-muted);font-size:14px;border:1px dashed var(--mp-border);border-radius:11px}
.pa-note{display:flex;align-items:center;gap:11px;margin-bottom:18px;padding:13px 15px;border-left:3px solid #a96e24;background:#f8f0e3;color:#76572e;font-size:13.5px;border-radius:0 8px 8px 0}
.pa-note a{color:#76572e;font-weight:700;text-decoration:underline}
@media(max-width:600px){.pa-list{grid-template-columns:minmax(0,1fr)}.pa-head h1{font-size:23px}}
</style>

<div class="pa-head">
  <div>
    <h1>Patient accounts</h1>
    <p>Choose a patient to open their funds, ledger, entitlements and bills.</p>
  </div>
</div>

<?php if($openingCount > 0): ?>
<div class="pa-note">
  <i class="fa fa-sign-in"></i>
  <span><?= (int)$openingCount; ?> opening position(s) still need review or approval.
    <a href="<?= base_url('patient_funds/openings'); ?>">Review opening positions</a></span>
</div>
<?php endif; ?>

<form class="pa-search" method="get" action="<?= base_url('patient_funds'); ?>">
  <input type="text" name="search" value="<?= htmlspecialchars($search ?? ''); ?>"
         placeholder="Search by name, patient code or phone" aria-label="Search patients">
  <button type="submit"><i class="fa fa-search"></i> Search</button>
</form>

<?php if($rows): ?>
<div class="pa-list">
  <?php foreach($rows as $p):
    $name = trim((string)($p->customer_name ?? '')) ?: ($p->patient_code ?? 'Patient');
    $initial = strtoupper(substr($name, 0, 1));
    $bits = array_filter(array($p->patient_code ?? null, $p->mobile ?? null));
  ?>
    <a class="pa-item" href="<?= base_url('patient_funds/index/' . (int)$p->id); ?>">
      <span class="pa-avatar"><?= htmlspecialchars($initial); ?></span>
      <span style="min-width:0;">
        <span class="pa-name"><?= htmlspecialchars($name); ?></span>
        <span class="pa-meta"><?= htmlspecialchars(implode(' · ', $bits)); ?></span>
      </span>
    </a>
  <?php endforeach; ?>
</div>
<?php else: ?>
<div class="pa-empty">
  <i class="fa fa-user-o" style="display:block;font-size:26px;margin-bottom:9px;"></i>
  No active patients match. Try a different search, or add the patient from the register.
</div>
<?php endif; ?>
