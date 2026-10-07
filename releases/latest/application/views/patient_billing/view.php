<?php $this->load->view('admin/desktop/_styles'); $CI =& get_instance(); ?>
<style>
.pb-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:14px!important}
.pb-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 10px 0!important}
.pb-table{width:100%!important;border-collapse:collapse!important;font-size:13px!important}
.pb-table th{text-align:left!important;padding:6px 8px!important;border-bottom:1px solid var(--mp-border)!important;font-size:10px!important;text-transform:uppercase!important;color:var(--mp-muted)!important}
.pb-table td{padding:7px 8px!important;border-bottom:1px solid #F1F5F9!important}
.pb-due{font-size:20px!important;font-weight:800!important;color:#991B1B!important}
.pb-ok{color:#166534!important;font-weight:700!important}
.ap-pending{background:#FEF3C7!important;color:#92400E!important}.ap-approved{background:#DCFCE7!important;color:#166534!important}
.ap-superseded,.ap-rejected{background:#FEE2E2!important;color:#991B1B!important}.ap-applied{background:#E0E7FF!important;color:#4338CA!important}
.pb-badge{font-size:10px!important;font-weight:700!important;padding:2px 8px!important;border-radius:12px!important}
</style>

<div class="mp-page-head">
  <div>
    <h2>Bill <?= htmlspecialchars($bill->sales_code); ?>
      <span class="pb-badge <?= $bill->payment_status === 'Paid' ? 'ap-approved' : ($bill->payment_status === 'Unpaid' ? 'ap-superseded' : 'ap-pending'); ?>"><?= htmlspecialchars(mp_code_label($bill->payment_status)); ?></span></h2>
    <div class="mp-page-sub"><?= $patient ? htmlspecialchars($patient->customer_name . ' · ' . $patient->patient_code) : 'Customer #' . (int)$bill->customer_id; ?>
      <?= $plan ? ' · Plan ' . htmlspecialchars($plan->plan_code) . ' v' . (int)$plan->version : ''; ?></div>
  </div>
  <div style="display:flex;gap:10px;">
    <?php if($plan): ?><a class="mp-qa-btn" href="<?= base_url('treatment_plans/view/' . $plan->id); ?>">Plan</a><?php endif; ?>
    <?php if($patient): ?><a class="mp-qa-btn blue" href="<?= base_url('patient_funds/index/' . $patient->id); ?>">Funds</a><?php endif; ?>
  </div>
</div>

<div class="pb-box">
  <h4>Itemised lines</h4>
  <table class="pb-table">
    <tr><th>Service</th><th>Qty</th><th>Unit price</th><th>Total</th><th>Entitlement</th></tr>
    <?php foreach($items as $it): ?>
    <tr><td><?= htmlspecialchars($it->description); ?></td><td><?= (float)$it->qty; ?></td>
      <td><?= $CI->currency($it->unit_price); ?></td><td><?= $CI->currency($it->total); ?></td>
      <td><?= $it->entitlement_id ? 'ENT-' . (int)$it->entitlement_id : '—'; ?></td></tr>
    <?php endforeach; ?>
    <tr><td colspan="3" style="text-align:right;">Subtotal</td><td><?= $CI->currency($bill->subtotal); ?></td><td></td></tr>
    <?php if($bill->tot_discount_to_all_amt > 0): ?>
    <tr><td colspan="3" style="text-align:right;color:#991B1B;">Approved discount</td><td class="db">−<?= $CI->currency($bill->tot_discount_to_all_amt); ?></td><td></td></tr>
    <?php endif; ?>
    <tr><td colspan="3" style="text-align:right;font-weight:800;">Grand total</td><td style="font-weight:800;"><?= $CI->currency($bill->grand_total); ?></td><td></td></tr>
  </table>
</div>

<div class="pb-box" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
  <div>
    <div style="font-size:11px;text-transform:uppercase;color:var(--mp-muted);">Outstanding</div>
    <div class="<?= $due > 0 ? 'pb-due' : 'pb-ok'; ?>"><?= $CI->currency($due); ?></div>
  </div>
  <?php if($can['add'] && $due > 0): ?>
  <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
    <input id="pay_amt" type="number" step="0.01" value="<?= $due; ?>" style="width:110px;padding:6px;border:1px solid var(--mp-border);border-radius:8px;">
    <select id="pay_type" style="padding:6px;border:1px solid var(--mp-border);border-radius:8px;">
      <option value="cash">Cash</option><option value="transfer">Transfer</option>
      <option value="patient_wallet">Patient wallet</option><option value="cust_advance">Store credit</option>
    </select>
    <input id="pay_ref" placeholder="Ref (optional)" style="width:110px;padding:6px;border:1px solid var(--mp-border);border-radius:8px;">
    <button class="mp-qa-btn green" onclick="pay()">Receive payment</button>
    <button class="mp-qa-btn" onclick="reqDiscount()">Request discount</button>
    <button class="mp-qa-btn" onclick="reqCredit()">Credit exception</button>
  </div>
  <?php endif; ?>
</div>

<div class="pb-box">
  <h4>Payments</h4>
  <table class="pb-table">
    <tr><th>Code</th><th>Date</th><th>Type</th><th>Amount</th><th>Receipt</th></tr>
    <?php foreach($payments as $p): ?>
    <tr><td><?= htmlspecialchars($p->payment_code); ?></td><td><?= htmlspecialchars($p->payment_date); ?></td>
      <td><?= htmlspecialchars($p->payment_type); ?></td><td class="pb-ok"><?= $CI->currency($p->payment); ?></td>
      <td><a class="mp-qa-btn" href="<?= base_url('patient_billing/receipt/' . $p->id); ?>" target="_blank"><i class="fa fa-print"></i></a></td></tr>
    <?php endforeach; ?>
    <?php if(empty($payments)): ?><tr><td colspan="5" style="color:var(--mp-muted);">No payments yet.</td></tr><?php endif; ?>
  </table>
</div>

<?php if(isset($reminder_pause)): ?>
<div class="pb-box">
  <h4>Debt reminders</h4>
  <?php if($reminder_pause): ?>
    <div><span class="pb-badge ap-superseded">Paused</span>
      <?= $reminder_pause->reason ? htmlspecialchars($reminder_pause->reason) : ''; ?>
      <?= $reminder_pause->resume_at ? ' · resumes ' . htmlspecialchars($reminder_pause->resume_at) : ''; ?></div>
    <?php if($can['reminder']): ?><button class="mp-qa-btn" style="margin-top:8px" onclick="resumeReminder(<?= (int)$reminder_pause->id; ?>)">Resume reminders</button><?php endif; ?>
  <?php else: ?>
    <div><span class="pb-badge ap-approved">Active</span> — reminders send while this invoice has an outstanding balance.</div>
    <?php if($can['reminder']): ?><button class="mp-qa-btn" style="margin-top:8px" onclick="pauseReminder()">Pause reminders</button><?php endif; ?>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="pb-box">
  <h4>Approvals — changes supersede prior approval</h4>
  <table class="pb-table">
    <tr><th>#</th><th>Type</th><th>Amount</th><th>Requested by</th><th>Status</th><th>Approved by</th><th></th></tr>
    <?php foreach($approvals as $a): ?>
    <tr><td><?= (int)$a->id; ?></td><td><?= htmlspecialchars($a->approval_type); ?></td>
      <td><?= $a->amount ? $CI->currency($a->amount) : '—'; ?></td>
      <td><?= htmlspecialchars($a->requesting_user_name); ?></td>
      <td><span class="pb-badge ap-<?= $a->status; ?>"><?= strtoupper($a->status); ?></span></td>
      <td><?= htmlspecialchars($a->approving_user_name ?: '—'); ?></td>
      <td>
        <?php if($a->status === 'pending' && $a->approval_type === 'md_discount' && $can['md']): ?>
          <button class="mp-qa-btn green" onclick="approveDisc(<?= (int)$a->id; ?>)">Approve</button>
        <?php endif; ?>
        <?php if($a->status === 'approved' && $a->approval_type === 'md_discount' && $can['md']): ?>
          <button class="mp-qa-btn blue" onclick="applyDisc()">Apply</button>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($approvals)): ?><tr><td colspan="7" style="color:var(--mp-muted);">No approvals on this bill.</td></tr><?php endif; ?>
  </table>
</div>

<script>
var CSRF = {'<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'};
var SID = <?= (int)$bill->id; ?>;
function pay(){
  $.post('<?= base_url('patient_billing/add_payment/'); ?>'+SID,
    Object.assign({amount: document.getElementById('pay_amt').value, payment_type: document.getElementById('pay_type').value, payment_ref: document.getElementById('pay_ref').value}, CSRF),
    function(r){ alert(r.message); if(r.status==='success') location.reload(); }, 'json');
}
function reqDiscount(){
  var a = prompt('Discount amount:'); if(!a) return;
  var r = prompt('Reason (required):'); if(!r) return;
  $.post('<?= base_url('patient_billing/request_discount/'); ?>'+SID, Object.assign({amount:a, reason:r}, CSRF),
    function(x){ alert(x.message); if(x.status==='success') location.reload(); }, 'json');
}
function approveDisc(id){
  var pin = prompt('Your approval PIN or password:');
  if(!pin) return;
  $.post('<?= base_url('patient_billing/approve_discount/'); ?>'+id, Object.assign({pin:pin}, CSRF),
    function(x){ alert(x.message); if(x.status==='success') location.reload(); }, 'json');
}
function applyDisc(){
  $.post('<?= base_url('patient_billing/apply_discount/'); ?>'+SID, CSRF,
    function(x){ alert(x.message); if(x.status==='success') location.reload(); }, 'json');
}
function reqCredit(){
  var r = prompt('Credit exception reason (required):'); if(!r) return;
  $.post('<?= base_url('patient_billing/request_credit/'); ?>'+SID, Object.assign({reason:r}, CSRF),
    function(x){ alert(x.message); if(x.status==='success') location.reload(); }, 'json');
}
function pauseReminder(){
  var reason = prompt('Pause reason (required):'); if(!reason) return;
  var resume = prompt('Resume at (optional, Y-m-d H:i)','')||'';
  $.post('<?= base_url('debt_reminders/pause'); ?>', Object.assign({scope:'invoice', target:SID, reason:reason, resume_at:resume}, CSRF),
    function(x){ alert(x.message); if(x.status==='success') location.reload(); }, 'json');
}
function resumeReminder(id){
  $.post('<?= base_url('debt_reminders/resume/'); ?>'+id, CSRF,
    function(x){ alert(x.message); if(x.status==='success') location.reload(); }, 'json');
}
</script>
