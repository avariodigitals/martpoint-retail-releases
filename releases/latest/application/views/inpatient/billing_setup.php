<?php $this->load->view('admin/desktop/_styles'); $CI =& get_instance(); ?>
<style>
.bs-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:14px!important}
.bs-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 10px 0!important}
.bs-table{width:100%!important;border-collapse:collapse!important;font-size:12px!important}
.bs-table th{text-align:left!important;padding:5px 8px!important;border-bottom:1px solid var(--mp-border)!important;font-size:10px!important;text-transform:uppercase!important;color:var(--mp-muted)!important}
.bs-table td{padding:6px 8px!important;border-bottom:1px solid #F1F5F9!important;vertical-align:top!important}
.bs-inp{padding:6px 9px!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;font-size:12px!important;margin-right:6px!important;background:var(--mp-bg,#fff)!important;color:inherit!important}
.bs-current{background:#D1FAE5;color:#065F46;padding:1px 7px;border-radius:8px;font-size:10px;font-weight:800}
</style>

<div class="mp-page-head">
  <div><h2>Inpatient Billing Setup</h2>
  <div class="mp-page-sub">Versioned daily rates and charging policies. Rates apply by effective date — a new version never rewrites history.</div></div>
  <?php if($can['run']): ?><div><button class="mp-qa-btn green" onclick="bsRun()">Run today's billing</button></div><?php endif; ?>
</div>

<div class="bs-box">
  <h4>Daily rates (versioned)</h4>
  <table class="bs-table">
    <tr><th>Code</th><th>Name</th><th>Amount</th><th>Effective from</th><th>To</th><th></th></tr>
    <?php foreach($rates as $r): ?>
    <tr><td><?= htmlspecialchars($r->rate_code); ?></td><td><?= htmlspecialchars($r->name); ?></td>
        <td><?= $CI->currency($r->amount); ?></td><td><?= htmlspecialchars($r->effective_from); ?></td>
        <td><?= htmlspecialchars($r->effective_to ?? '—'); ?></td>
        <td><?= $r->effective_to === null ? '<span class="bs-current">current</span>' : ''; ?></td></tr>
    <?php endforeach; ?>
    <?php if(empty($rates)): ?><tr><td colspan="6" style="color:var(--mp-muted)">No rates configured — set a bed rate below before running billing.</td></tr><?php endif; ?>
  </table>
  <?php if($can['run']): ?>
  <form style="margin-top:8px" onsubmit="return bsRate(event)">
    <select class="bs-inp" name="rate_code"><option value="bed">bed</option><option value="nursing">nursing</option><option value="leave">leave</option><option value="other">other</option></select>
    <input class="bs-inp" name="name" placeholder="Rate name" required>
    <input class="bs-inp" name="amount" placeholder="Amount" required>
    <input class="bs-inp" name="effective_from" value="<?= date('Y-m-d'); ?>">
    <button class="mp-qa-btn green">Save new version</button>
  </form>
  <?php endif; ?>
</div>

<div class="bs-box">
  <h4>Bed payment accounts</h4>
  <p>Record service charges and payments against the patient's bed at the time of each transaction. Outpatients use the outpatient account. Transfers affect new transactions; existing ledger history stays on its original account.</p>
  <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
    <input type="checkbox" <?= ($policies['bed_accounts_enabled'] ?? '0')==='1' ? 'checked' : ''; ?> <?= !$can['run'] ? 'disabled' : ''; ?> onchange="bsBedAccounts(this)">
    Enable bed accounts for this workspace
  </label>
  <p style="color:var(--mp-muted);margin-top:8px">Off by default. Switching off stops new account assignments and preserves recorded history.</p>
</div>

<div class="bs-box">
  <h4>Charging policies</h4>
  <table class="bs-table">
    <tr><th>Policy</th><th>Value</th><th>Meaning</th></tr>
    <?php
    $explain = array(
      'billing_boundary'      => 'Charge unit — calendar day from admission date',
      'admission_day_charge'  => 'Whether the admission day itself is billed',
      'discharge_day_charge'  => 'none = departure day not billed; full = billed',
      'leave_billing'         => 'Bed charge while on authorised leave: full | half | none',
      'morning_due'           => 'Morning nursing task due time',
      'evening_due'           => 'Evening nursing task due time',
      'auto_settle_wallet'    => '1 = invoice auto-settles from available wallet after each run',
    );
    $defaults = array('billing_boundary'=>'calendar','admission_day_charge'=>'full','discharge_day_charge'=>'none','leave_billing'=>'half','morning_due'=>'08:00','evening_due'=>'20:00','auto_settle_wallet'=>'1');
    foreach($defaults as $k => $dv): $v = $policies[$k] ?? $dv; ?>
    <tr><td><code><?= $k; ?></code></td>
        <td><?php if($can['run']): ?><input class="bs-inp" style="width:130px" value="<?= htmlspecialchars($v); ?>" onchange="bsPolicy('<?= $k; ?>', this.value)"><?php else: ?><strong><?= htmlspecialchars($v); ?></strong><?php endif; ?></td>
        <td style="color:var(--mp-muted)"><?= htmlspecialchars($explain[$k] ?? ''); ?></td></tr>
    <?php endforeach; ?>
  </table>
</div>

<script>
function bsBedAccounts(input){ var before=!input.checked; input.disabled=true;
  var fd=new FormData(); fd.append('policy_key','bed_accounts_enabled'); fd.append('policy_value',input.checked?'1':'0');
  if(window.csrfName) fd.append(window.csrfName,window.csrfHash);
  fetch('<?= base_url('inpatient/save_policy'); ?>',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
    if(d.status!=='success'){input.checked=before;alert(d.message||'Could not save');}
    else {location.reload();}
  }).catch(()=>{input.checked=before;alert('Could not save. Try again.');}).finally(()=>{input.disabled=false;});
}
function bsRun(){ var fd = new FormData(); fd.append('date', prompt('Run for date (Y-m-d)','<?= date('Y-m-d'); ?>')||'');
  fetch('<?= base_url('inpatient/run_billing'); ?>', {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>alert(d.message||'')); }
function bsRate(e){ e.preventDefault(); var fd = new FormData(e.target);
  fetch('<?= base_url('inpatient/save_rate'); ?>', {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); return false; }
function bsPolicy(k, v){ var fd = new FormData(); fd.append('policy_key', k); fd.append('policy_value', v);
  fetch('<?= base_url('inpatient/save_policy'); ?>', {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>alert(d.message||'')); }
</script>
