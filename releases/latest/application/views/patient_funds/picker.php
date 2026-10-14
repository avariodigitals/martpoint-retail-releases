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
$summaries = isset($account_summaries) && is_array($account_summaries) ? $account_summaries : array();
$CI =& get_instance();
?>
<style>
.pa-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px}
.pa-head h1{margin:0 0 5px;font:400 28px/1.15 'DM Serif Display',Georgia,serif;color:var(--mp-text)}
.pa-head p{margin:0;color:var(--mp-muted);font-size:14px}
.pa-search{display:flex;gap:8px;margin-bottom:18px}
.pa-search input{flex:1;min-width:0;padding:11px 14px;border:1px solid var(--mp-border);border-radius:9px;font-size:14px}
.pa-search button{padding:11px 18px;border:0;border-radius:9px;background:var(--mp-primary);color:#fff;font-size:14px;font-weight:600}
.pa-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:10px}
.pa-item{display:block;padding:13px 14px;border:1px solid var(--mp-border);border-radius:11px;background:var(--mp-surface);color:var(--mp-text);text-decoration:none}
.pa-item:hover{border-color:#91b9a6;background:#f7fbf9;color:var(--mp-text);text-decoration:none}
.pa-person{display:flex;align-items:center;gap:12px}
.pa-avatar{width:38px;height:38px;flex:0 0 38px;display:grid;place-items:center;border-radius:50%;background:#e3efe9;color:#104d40;font-weight:700;font-size:14px}
.pa-name{font-size:14px;font-weight:600;display:block}
.pa-meta{font-size:12px;color:var(--mp-muted);margin-top:2px;display:block}
.pa-money{display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-top:12px}
.pa-chip{border:1px solid var(--mp-border);border-radius:9px;padding:7px 8px;background:#fff;min-width:0}
.pa-chip b{display:block;font-size:12px;color:var(--mp-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pa-chip span{display:block;font-size:9px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;color:var(--mp-muted);margin-bottom:2px}
.pa-chip.pkg b{color:#B45309}.pa-chip.wallet b{color:#166534}.pa-chip.res b{color:#92400E}.pa-chip.pending b{color:#1D4ED8}
.pa-empty{padding:26px;text-align:center;color:var(--mp-muted);font-size:14px;border:1px dashed var(--mp-border);border-radius:11px}
.pa-note{display:flex;align-items:center;gap:11px;margin-bottom:18px;padding:13px 15px;border-left:3px solid #a96e24;background:#f8f0e3;color:#76572e;font-size:13.5px;border-radius:0 8px 8px 0}
.pa-note a{color:#76572e;font-weight:700;text-decoration:underline}
@media(max-width:600px){.pa-list{grid-template-columns:minmax(0,1fr)}.pa-money{grid-template-columns:minmax(0,1fr)}.pa-head h1{font-size:23px}}
</style>

<div class="pa-head">
  <div>
    <h1>Patient accounts</h1>
    <p>Choose a patient to open their wallet ledger, package-held funds, entitlements and bills.</p>
  </div>
</div>

<div class="pa-note">
  <i class="fa fa-info-circle"></i>
  <span><b>Wallet</b> money appears in the ledger and can move to Retail advance from the patient page. <b>Package held</b> money is tied to paid sessions, so it appears under entitlements even when the wallet ledger has no rows.</span>
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
    $s = isset($summaries[(int)$p->id]) ? $summaries[(int)$p->id] : array();
    $packageHeld = (float)($s['package_held'] ?? 0);
    $walletAvail = (float)($s['available'] ?? 0);
    $walletReserved = (float)($s['reserved'] ?? 0);
    $pending = (float)($s['pending'] ?? 0);
    $sessionsLeft = (float)($s['sessions_left'] ?? 0);
  ?>
    <a class="pa-item" href="<?= base_url('patient_funds/index/' . (int)$p->id); ?>">
      <span class="pa-person">
        <span class="pa-avatar"><?= htmlspecialchars($initial); ?></span>
        <span style="min-width:0;">
          <span class="pa-name"><?= htmlspecialchars($name); ?></span>
          <span class="pa-meta"><?= htmlspecialchars(implode(' · ', $bits)); ?></span>
        </span>
      </span>
      <span class="pa-money">
        <span class="pa-chip pkg"><span>Package held</span><b><?= $CI->currency($packageHeld); ?></b><?= $sessionsLeft > 0 ? '<small>' . htmlspecialchars((string)$sessionsLeft) . ' session(s) left</small>' : ''; ?></span>
        <span class="pa-chip wallet"><span>Wallet available</span><b><?= $CI->currency($walletAvail); ?></b></span>
        <span class="pa-chip res"><span>Wallet reserved</span><b><?= $CI->currency($walletReserved); ?></b></span>
        <span class="pa-chip pending"><span>Pending</span><b><?= $CI->currency($pending); ?></b></span>
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
