<?php $this->load->view('admin/desktop/_styles'); $this->load->view('components/dialog'); $CI =& get_instance(); ?>
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

<?php
/* A waiver is an md_discount request tagged waiver=1. Track the live one so
   the top of the screen can show it as pending / decided. */
$waiver = null;
foreach($approvals as $a){
	if($a->approval_type !== 'md_discount') continue;
	$nv = json_decode((string)$a->new_value, true);
	if(empty($nv['waiver'])) continue;
	if(in_array($a->status, array('pending','approved'))){ $waiver = $a; break; }
}
$waiverOpen = $waiver && $waiver->status === 'pending';
$waiverApplied = $waiver && $waiver->status === 'applied';
?>

<div class="mp-page-head">
  <div>
    <h2>Bill <?= htmlspecialchars($bill->sales_code); ?>
      <span class="pb-badge <?= $bill->payment_status === 'Paid' ? 'ap-approved' : ($bill->payment_status === 'Unpaid' ? 'ap-superseded' : 'ap-pending'); ?>"><?= htmlspecialchars(mp_code_label($bill->payment_status)); ?></span>
      <?php
        $careType = $bill->care_type ?? null;
        if($careType === 'inpatient'){ $ctCls = 'ap-applied'; $ctTxt = 'Inpatient'; }
        elseif($careType === 'outpatient'){ $ctCls = 'ap-approved'; $ctTxt = 'Outpatient'; }
        else { $ctCls = ''; $ctTxt = 'Care type not marked'; }
      ?>
      <span class="pb-badge <?= $ctCls; ?>" style="<?= $ctCls ? '' : 'background:#F1F5F9;color:#64748B;'; ?>"><?= $ctTxt; ?></span></h2>
    <div class="mp-page-sub"><?= $patient ? htmlspecialchars($patient->customer_name . ' · ' . $patient->patient_code) : htmlspecialchars(mp_label('customer')) . ' #' . (int)$bill->customer_id; ?>
      <?= $plan ? ' · Plan ' . htmlspecialchars($plan->plan_code) . ' v' . (int)$plan->version : ''; ?></div>
  </div>
  <div style="display:flex;gap:10px;align-items:center;">
    <?php if($due > 0 && !$waiver && $can['add']): ?>
      <button class="mp-qa-btn" style="border:1.5px solid #F59E0B;color:#92400E;font-weight:700;" onclick="reqWaiver()">
        <i class="fa fa-hand-paper-o"></i> Waive this bill
      </button>
    <?php endif; ?>
    <a class="mp-qa-btn" href="<?= base_url('patient_billing/invoice/' . $bill->id); ?>" target="_blank">
      <i class="fa fa-print"></i> Print bill
    </a>
    <a class="mp-qa-btn" href="<?= base_url('patient_billing/invoice_a4/' . $bill->id); ?>" target="_blank">
      <i class="fa fa-file-pdf-o"></i> Print A4
    </a>
    <?php if($plan): ?><a class="mp-qa-btn" href="<?= base_url('treatment_plans/view/' . $plan->id); ?>">Plan</a><?php endif; ?>
    <?php if($patient): ?><a class="mp-qa-btn blue" href="<?= base_url('patient_funds/index/' . $patient->id); ?>">Funds</a><?php endif; ?>
  </div>
</div>

<?php if($waiverOpen): ?>
<div class="pb-box" style="border-left:4px solid #F59E0B;background:#FFFBEB;">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
    <div>
      <div style="font-size:11px;text-transform:uppercase;color:#92400E;font-weight:800;letter-spacing:.4px;">
        <i class="fa fa-hourglass-half"></i> Waiver pending approval
      </div>
      <div style="font-size:13px;color:#78350F;margin-top:5px;">
        <b><?= $CI->currency($waiver->amount); ?></b> requested by <?= htmlspecialchars($waiver->requesting_user_name); ?>
        <?= $waiver->reason ? ' — ' . htmlspecialchars($waiver->reason) : ''; ?>
      </div>
      <div style="font-size:12px;color:#92400E;margin-top:5px;">
        The Clinical Director decides: <b>approve</b> and the patient pays nothing, <b>decline</b> and this bill stays payable.
      </div>
    </div>
    <?php if($can['md']): ?>
    <div style="display:flex;gap:8px;">
      <button class="mp-qa-btn green" onclick="approveDisc(<?= (int)$waiver->id; ?>)">Approve waiver</button>
      <button class="mp-qa-btn" style="border-color:#EF4444;color:#991B1B;" onclick="declineDisc(<?= (int)$waiver->id; ?>)">Decline</button>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php elseif($waiverApplied): ?>
<div class="pb-box" style="border-left:4px solid #10B981;background:#F0FDF4;">
  <div style="font-size:13px;color:#065F46;">
    <i class="fa fa-check-circle"></i> <b>Waiver approved</b> — <?= $CI->currency($waiver->amount); ?> forgiven
    <?= $waiver->reason ? ' (' . htmlspecialchars($waiver->reason) . ')' : ''; ?>. The patient owes nothing on this bill.
  </div>
</div>
<?php endif; ?>

<?php $this->load->view('patient_billing/system_account', get_defined_vars()); ?>
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
    <?php foreach($approvals as $a): $nvA = json_decode((string)$a->new_value, true); $isWaiver = is_array($nvA) && !empty($nvA['waiver']); ?>
    <tr><td><?= (int)$a->id; ?></td>
      <td><?= $isWaiver ? '<b style="color:#92400E;">Waiver</b>' : htmlspecialchars($a->approval_type); ?></td>
      <td><?= $a->amount ? $CI->currency($a->amount) : '—'; ?></td>
      <td><?= htmlspecialchars($a->requesting_user_name); ?></td>
      <td><span class="pb-badge ap-<?= $a->status; ?>"><?= strtoupper($a->status); ?></span></td>
      <td><?= htmlspecialchars($a->approving_user_name ?: '—'); ?></td>
      <td>
        <?php if($a->status === 'pending' && $a->approval_type === 'md_discount' && $can['md']): ?>
          <button class="mp-qa-btn green" onclick="approveDisc(<?= (int)$a->id; ?>)">Approve</button>
          <button class="mp-qa-btn" style="border-color:#EF4444;color:#991B1B;" onclick="declineDisc(<?= (int)$a->id; ?>)">Decline</button>
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
function done(x){
  cqToast(x.message || 'Done', x.status === 'success' ? 'success' : 'danger');
  if(x.status === 'success') setTimeout(function(){ location.reload(); }, 800);
}
function pay(){
  $.post('<?= base_url('patient_billing/add_payment/'); ?>'+SID,
    Object.assign({amount: document.getElementById('pay_amt').value, payment_type: document.getElementById('pay_type').value, payment_ref: document.getElementById('pay_ref').value, account_id: document.getElementById('physio_account_choice') ? document.getElementById('physio_account_choice').value : ''}, CSRF),
    done, 'json');
}
function reqDiscount(){
  cqPrompt({ title:'Request a discount', message:'How much should come off this bill?', placeholder:'Discount amount', required:true, okText:'Continue' })
    .then(function(a){
      if(a === null) return;
      return cqPrompt({ title:'Reason for the discount', message:'The approver sees this, and it stays on the bill record.', placeholder:'Reason', multiline:true, required:true, okText:'Send Request' })
        .then(function(r){
          if(r === null) return;
          $.post('<?= base_url('patient_billing/request_discount/'); ?>'+SID, Object.assign({amount:a, reason:r}, CSRF), done, 'json');
        });
    });
}
function approveDisc(id){
  cqPrompt({ title:'Approve discount', message:'Confirm with your approval PIN or password.', placeholder:'Approval PIN or password', required:true, okText:'Approve' })
    .then(function(pin){
      if(pin === null) return;
      $.post('<?= base_url('patient_billing/approve_discount/'); ?>'+id, Object.assign({pin:pin}, CSRF), done, 'json');
    });
}
function declineDisc(id){
  cqConfirm('Decline this request? The bill stays payable and the requester is notified.', { title:'Decline request?', okText:'Decline', danger:true })
    .then(function(ok){
      if(!ok) return;
      $.post('<?= base_url('patient_billing/decline_discount/'); ?>'+id, CSRF, done, 'json');
    });
}
function reqWaiver(){
  cqPrompt({
    title: 'Waive this bill',
    message: 'The patient will owe nothing if the Clinical Director approves. State why the clinic should absorb this cost.',
    placeholder: 'Reason for the waiver',
    multiline: true,
    required: true,
    danger: true,
    okText: 'Send for Approval'
  }).then(function(r){
    if(r === null) return;
    $.post('<?= base_url('patient_billing/request_waiver/'); ?>'+SID, Object.assign({reason:r}, CSRF), done, 'json');
  });
}
function applyDisc(){
  $.post('<?= base_url('patient_billing/apply_discount/'); ?>'+SID, CSRF, done, 'json');
}
function reqCredit(){
  cqPrompt({ title:'Credit exception', message:'Why should this patient be allowed to owe? The approver sees this reason.', placeholder:'Reason', multiline:true, required:true, okText:'Send Request' })
    .then(function(r){
      if(r === null) return;
      $.post('<?= base_url('patient_billing/request_credit/'); ?>'+SID, Object.assign({reason:r}, CSRF), done, 'json');
    });
}
function pauseReminder(){
  cqPrompt({ title:'Pause reminders', message:'Why are reminders being paused for this invoice?', placeholder:'Reason', multiline:true, required:true, okText:'Continue' })
    .then(function(reason){
      if(reason === null) return;
      return cqPrompt({ title:'Resume at', message:'Leave blank to keep it paused until someone resumes it manually.', placeholder:'Y-m-d H:i', okText:'Pause Reminders' })
        .then(function(resume){
          if(resume === null) return;
          $.post('<?= base_url('debt_reminders/pause'); ?>', Object.assign({scope:'invoice', target:SID, reason:reason, resume_at:resume}, CSRF), done, 'json');
        });
    });
}
function resumeReminder(id){
  $.post('<?= base_url('debt_reminders/resume/'); ?>'+id, CSRF, done, 'json');
}
</script>
