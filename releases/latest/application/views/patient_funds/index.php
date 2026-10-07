<?php $this->load->view('admin/desktop/_styles'); $CI =& get_instance(); ?>
<style>
.pf-grid{display:grid!important;grid-template-columns:repeat(auto-fit,minmax(160px,1fr))!important;gap:10px!important;margin-bottom:16px!important}
.pf-bal{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important}
.pf-bal .v{font-size:22px!important;font-weight:800!important}
.pf-bal .l{font-size:10px!important;font-weight:700!important;text-transform:uppercase!important;color:var(--mp-muted)!important}
.pf-avail .v{color:#166534!important}.pf-resv .v{color:#B45309!important}.pf-pend .v{color:#1E40AF!important}
.pf-debt .v{color:#991B1B!important}.pf-retail .v{color:#6B21A8!important}
.pf-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:14px!important}
.pf-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 10px 0!important}
.pf-table{width:100%!important;border-collapse:collapse!important;font-size:12px!important}
.pf-table th{text-align:left!important;padding:5px 8px!important;border-bottom:1px solid var(--mp-border)!important;font-size:10px!important;text-transform:uppercase!important;color:var(--mp-muted)!important}
.pf-table td{padding:6px 8px!important;border-bottom:1px solid #F1F5F9!important}
.pf-in{color:#166534!important;font-weight:700!important}.pf-out{color:#991B1B!important;font-weight:700!important}.pf-memo{color:#6B7280!important}
</style>

<div class="mp-page-head">
  <div><h2><?= htmlspecialchars($page_title); ?></h2>
  <div class="mp-page-sub"><?= htmlspecialchars($patient->customer_name ?? ''); ?> · balances derived live from the ledger — available, reserved and pending are never mixed</div></div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <a class="mp-qa-btn blue" href="<?= base_url('patient_funds/statement/' . $patient->id); ?>" target="_blank"><i class="fa fa-print"></i> Statement</a>
    <a class="mp-qa-btn" href="<?= base_url('treatment_plans/index/' . $patient->id); ?>">Plans</a>
  </div>
</div>

<div class="pf-grid">
  <div class="pf-bal pf-avail"><div class="l">Available funds</div><div class="v"><?= $CI->currency($balances['available']); ?></div></div>
  <div class="pf-bal pf-resv"><div class="l">Reserved</div><div class="v"><?= $CI->currency($balances['reserved']); ?></div></div>
  <div class="pf-bal pf-pend"><div class="l">Pending verification</div><div class="v"><?= $CI->currency($balances['pending']); ?></div></div>
  <div class="pf-bal pf-debt"><div class="l">Outstanding debt</div><div class="v"><?= $CI->currency($outstanding); ?></div></div>
  <div class="pf-bal pf-retail"><div class="l">Retail store credit</div><div class="v"><?= $CI->currency($balances['retail_advance']); ?></div></div>
</div>

<?php if($can['add']): ?>
<div class="pf-box">
  <h4>Fund / transfer / reserve</h4>
  <div style="display:flex;gap:8px;flex-wrap:wrap;font-size:13px;align-items:center;">
    <input id="pf_amt" type="number" min="0" step="0.01" placeholder="Amount" style="width:110px;padding:6px;border:1px solid var(--mp-border);border-radius:8px;">
    <input id="pf_ref" placeholder="Ref (e.g. teller no.)" style="width:130px;padding:6px;border:1px solid var(--mp-border);border-radius:8px;">
    <button class="mp-qa-btn green" onclick="fund()">Cash fund</button>
    <button class="mp-qa-btn blue" onclick="evidence()">Submit transfer evidence</button>
    <button class="mp-qa-btn" onclick="xferIn()" title="Move retail advance into care funds">Advance → Wallet</button>
    <button class="mp-qa-btn" onclick="xferOut()">Wallet → Advance</button>
    <button class="mp-qa-btn" onclick="reserve()">Reserve</button>
    <?php if($can['refund']): ?><button class="mp-qa-btn" onclick="refund()">Refund (approval)</button><?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="pf-box">
  <h4>Payment evidence — pending verification</h4>
  <table class="pf-table">
    <tr><th>Ref</th><th>Channel</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
    <?php foreach($evidence as $ev): ?>
    <tr>
      <td><?= htmlspecialchars($ev->payment_ref); ?></td>
      <td><?= htmlspecialchars($ev->channel); ?></td>
      <td><?= $CI->currency($ev->amount); ?></td>
      <td><?= htmlspecialchars(mp_code_label($ev->status)); ?></td>
      <td>
        <?php if($ev->status === 'submitted' && $can['verify']): ?>
        <button class="mp-qa-btn green" onclick="verifyEv(<?= (int)$ev->id; ?>)">Verify</button>
        <button class="mp-qa-btn" onclick="rejectEv(<?= (int)$ev->id; ?>)">Reject</button>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

<div class="pf-box">
  <h4>Reservations</h4>
  <table class="pf-table">
    <tr><th>#</th><th>Plan</th><th>Reserved</th><th>Consumed</th><th>Status</th><th></th></tr>
    <?php foreach($reservations as $r): $left = $r->amount_reserved - $r->amount_consumed; ?>
    <tr>
      <td>R-<?= (int)$r->id; ?></td><td><?= $r->plan_id ? 'Plan #' . (int)$r->plan_id : '—'; ?></td>
      <td><?= $CI->currency($r->amount_reserved); ?></td><td><?= $CI->currency($r->amount_consumed); ?></td>
      <td><?= strtoupper($r->status); ?> (<?= $CI->currency($left); ?> open)</td>
      <td><?php if($r->status === 'active' && $can['add']): ?><button class="mp-qa-btn" onclick="release(<?= (int)$r->id; ?>)">Release</button><?php endif; ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

<div class="pf-box">
  <h4>Session entitlements</h4>
  <table class="pf-table">
    <tr><th>Plan</th><th>Source</th><th>Total</th><th>Used</th><th>Remaining</th><th>Funded</th><th>Status</th></tr>
    <?php foreach($entitlements as $e): ?>
    <tr><td><?= $e->plan_id ? '#' . (int)$e->plan_id : '—'; ?></td><td><?= htmlspecialchars($e->source); ?></td>
      <td><?= (float)$e->units_total; ?></td><td><?= (float)$e->units_used; ?></td>
      <td><b><?= (float)($e->units_total - $e->units_used); ?></b></td>
      <td><?= $CI->currency($e->funded_amount); ?></td><td><?= strtoupper($e->status); ?></td></tr>
    <?php endforeach; ?>
  </table>
</div>

<div class="pf-box">
  <h4>Ledger — every movement, traceable</h4>
  <table class="pf-table">
    <tr><th>#</th><th>Type</th><th>Dir</th><th>Amount</th><th>Operation key</th><th>Ref</th><th>When</th></tr>
    <?php foreach($ledger as $t):
      $cls = $t->direction === 'credit' ? 'pf-in' : ($t->direction === 'debit' ? 'pf-out' : 'pf-memo');
      $ref = $t->salespayment_id ? 'SP-' . $t->salespayment_id : ($t->sales_id ? 'SALE-' . $t->sales_id : ($t->custadvance_id ? 'ADV-' . $t->custadvance_id : ($t->reservation_id ? 'RES-' . $t->reservation_id : '')));
    ?>
    <tr>
      <td><?= (int)$t->id; ?></td><td><?= htmlspecialchars($t->txn_type); ?></td>
      <td class="<?= $cls; ?>"><?= strtoupper($t->direction); ?></td>
      <td class="<?= $cls; ?>"><?= $t->direction === 'debit' ? '−' : ($t->direction === 'credit' ? '+' : ''); ?><?= $CI->currency($t->amount); ?></td>
      <td style="font-family:monospace;font-size:11px;"><?= htmlspecialchars($t->operation_key); ?></td>
      <td><?= htmlspecialchars($ref); ?></td>
      <td><?= htmlspecialchars($t->created_date . ' ' . $t->created_time); ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

<script>
var CSRF = {'<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'};
var PID = <?= (int)$patient->id; ?>;
function post(url, d){ $.post(url, Object.assign(d||{}, CSRF), function(r){ alert(r.message); if(r.status==='success') location.reload(); }, 'json'); }
function amt(){ return parseFloat(document.getElementById('pf_amt').value) || 0; }
function ref(){ return document.getElementById('pf_ref').value; }
function fund(){ post('<?= base_url('patient_funds/fund/'); ?>'+PID, {amount: amt(), payment_ref: ref()}); }
function evidence(){ var ch = prompt('Channel (transfer/pos/cheque/online):','transfer'); if(!ch) return;
  var payer = prompt('Payer name (if different):','');
  post('<?= base_url('patient_funds/submit_evidence/'); ?>'+PID, {amount: amt(), payment_ref: ref(), channel: ch, payer_name: payer}); }
function verifyEv(id){ post('<?= base_url('patient_funds/verify_evidence/'); ?>'+id, {}); }
function rejectEv(id){ var r=prompt('Reject reason:'); if(!r) return; post('<?= base_url('patient_funds/reject_evidence/'); ?>'+id, {reason:r}); }
function xferIn(){ post('<?= base_url('patient_funds/transfer_from_advance/'); ?>'+PID, {amount: amt()}); }
function xferOut(){ post('<?= base_url('patient_funds/transfer_to_advance/'); ?>'+PID, {amount: amt()}); }
function reserve(){ var plan = prompt('Plan id (optional):',''); post('<?= base_url('patient_funds/reserve/'); ?>'+PID, {amount: amt(), plan_id: plan}); }
function release(id){ var r=prompt('Release reason:'); if(!r) return; post('<?= base_url('patient_funds/release/'); ?>'+id, {reason:r}); }
function refund(){ var r=prompt('Refund reason:'); if(!r) return; post('<?= base_url('patient_funds/refund/'); ?>'+PID, {amount: amt(), reason:r}); }
</script>
