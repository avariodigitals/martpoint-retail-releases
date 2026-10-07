<?php $this->load->view('admin/desktop/_styles'); $CI =& get_instance(); ?>
<style>
.tp-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:14px!important}
.tp-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 10px 0!important}
.tp-table{width:100%!important;border-collapse:collapse!important;font-size:13px!important}
.tp-table th{font-size:11px!important;text-transform:uppercase!important;color:var(--mp-muted)!important;text-align:left!important;padding:6px 8px!important;border-bottom:1px solid var(--mp-border)!important}
.tp-table td{padding:8px!important;border-bottom:1px solid #F1F5F9!important}
.tp-badge{font-size:10px!important;font-weight:700!important;padding:2px 8px!important;border-radius:12px!important}
.st-scheduled{background:#E0E7FF!important;color:#4338CA!important}.st-checked_in{background:#FEF3C7!important;color:#92400E!important}
.st-in_progress{background:#DBEAFE!important;color:#1E40AF!important}.st-completed{background:#DCFCE7!important;color:#166534!important}
.st-cancelled,.st-no_show{background:#FEE2E2!important;color:#991B1B!important}.st-interrupted{background:#FFEDD5!important;color:#9A3412!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?> <span class="tp-badge st-<?= $plan->status; ?>"><?= htmlspecialchars(mp_code_label($plan->status)); ?></span></h2>
    <div class="mp-page-sub"><?= htmlspecialchars($patient->customer_name ?? ''); ?> · <?= htmlspecialchars($patient->patient_code); ?> · <?= ucfirst($plan->care_setting); ?> · v<?= (int)$plan->version; ?></div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <?php if($can['billing'] && !$bill): ?><button class="mp-qa-btn green" onclick="createBill()"><i class="fa fa-file-invoice"></i> Create Bill</button><?php endif; ?>
    <?php if($bill && $can['billing_view']): ?><a class="mp-qa-btn blue" href="<?= base_url('patient_billing/view/' . $bill->id); ?>"><i class="fa fa-file-invoice"></i> Bill <?= htmlspecialchars($bill->sales_code); ?> (<?= htmlspecialchars(mp_code_label($bill->payment_status)); ?>)</a><?php endif; ?>
    <a class="mp-qa-btn" href="<?= base_url('treatment_plans/index/' . $patient->id); ?>">All plans</a>
  </div>
</div>

<div class="tp-box">
  <h4>Billable lines — v<?= (int)$plan->version; ?> snapshot</h4>
  <table class="tp-table">
    <tr><th>Service</th><th>Qty</th><th>Sessions/unit</th><th>Unit price</th><th>Line total</th></tr>
    <?php $tot = 0; foreach($items as $it): $lt = $it->qty * $it->unit_price; $tot += $lt; ?>
    <tr><td><?= htmlspecialchars($it->item_name); ?></td><td><?= (float)$it->qty; ?></td><td><?= (float)$it->sessions_per_unit; ?></td>
        <td><?= $CI->currency($it->unit_price); ?></td><td><?= $CI->currency($lt); ?></td></tr>
    <?php endforeach; ?>
    <tr><td colspan="4" style="text-align:right;font-weight:800;">Total</td><td style="font-weight:800;"><?= $CI->currency($tot); ?></td></tr>
  </table>
  <?php if($can['amend'] && in_array($plan->status, ['draft','active'])): ?>
  <div style="margin-top:8px;"><button class="mp-qa-btn" onclick="amendPlan()"><i class="fa fa-edit"></i> Amend (new version)</button></div>
  <?php endif; ?>
</div>

<?php if(!empty($review_points)): ?>
<div class="tp-box"><h4>Review points</h4>
  <ul style="margin:0;padding-left:18px;font-size:13px;">
    <?php foreach($review_points as $rp): ?><li><?= htmlspecialchars(is_array($rp) ? ($rp['note'] ?? '') : $rp); ?></li><?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<div class="tp-box">
  <h4>Session entitlements (per-plan counters)</h4>
  <?php if(empty($entitlements)): ?><div style="font-size:13px;color:var(--mp-muted);">None yet — entitlements appear when bill lines are paid/funded.</div><?php endif; ?>
  <?php foreach($entitlements as $e): $rem = $e->units_total - $e->units_used; ?>
  <div style="display:flex;justify-content:space-between;align-items:center;padding:8px;border:1px solid var(--mp-border);border-radius:8px;margin-bottom:6px;font-size:13px;">
    <div><b><?= (float)$e->units_total; ?> sessions</b> · used <?= (float)$e->units_used; ?> · <b><?= (float)$rem; ?> remaining</b>
      <span class="tp-badge st-scheduled"><?= htmlspecialchars($e->source); ?></span>
      <span class="tp-badge st-<?= $e->status === 'active' ? 'completed' : 'cancelled'; ?>"><?= strtoupper($e->status); ?></span>
      <div style="font-size:11px;color:var(--mp-muted);">Funded <?= $CI->currency($e->funded_amount); ?> · consumed <?= $CI->currency($e->consumed_amount); ?></div>
    </div>
    <?php if($e->status === 'active' && $rem > 0 && $can['sessions']): ?>
    <button class="mp-qa-btn blue" onclick="scheduleSession(<?= (int)$e->id; ?>, <?= (float)$e->funded_amount / max(1,(float)$e->units_total); ?>)">Schedule session</button>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<div class="tp-box">
  <h4>Sessions</h4>
  <?php if(empty($sessions)): ?><div style="font-size:13px;color:var(--mp-muted);">No sessions scheduled.</div><?php endif; ?>
  <table class="tp-table">
    <tr><th>#</th><th>Scheduled</th><th>Status</th><th>Fee</th><th>Actions</th></tr>
    <?php foreach($sessions as $s): ?>
    <tr>
      <td>Session <?= (int)$s->session_no; ?> of <?= (int)$s->units_total; ?></td>
      <td><?= htmlspecialchars($s->scheduled_at); ?></td>
      <td><span class="tp-badge st-<?= $s->status; ?>"><?= strtoupper(str_replace('_',' ',$s->status)); ?></span><?= $s->fee_posted ? ' ✓fee' : ''; ?></td>
      <td><?= $CI->currency($s->fee); ?></td>
      <td style="white-space:nowrap;">
        <?php if($s->status === 'scheduled' && $can['sessions']): ?>
          <button class="mp-qa-btn" onclick="sessAct(<?= (int)$s->id; ?>,'checkin')">Check in</button>
        <?php endif; ?>
        <?php if($s->status === 'checked_in'): ?><button class="mp-qa-btn" onclick="sessAct(<?= (int)$s->id; ?>,'start')">Start</button><?php endif; ?>
        <?php if(in_array($s->status, ['in_progress','interrupted']) && $can['sessions']): ?>
          <button class="mp-qa-btn green" onclick="sessAct(<?= (int)$s->id; ?>,'complete')">Complete</button>
        <?php endif; ?>
        <?php if(in_array($s->status, ['scheduled','checked_in','in_progress','interrupted'])): ?>
          <button class="mp-qa-btn" onclick="sessCancel(<?= (int)$s->id; ?>)">Cancel</button>
        <?php endif; ?>
        <?php if($s->status === 'completed' && !$s->reversed): ?>
          <button class="mp-qa-btn" onclick="sessReverse(<?= (int)$s->id; ?>)">Reverse</button>
        <?php endif; ?>
        <a class="mp-qa-btn" href="<?= base_url('sessions/ticket/' . $s->id); ?>" target="_blank">Ticket</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

<div class="tp-box">
  <h4>Version history</h4>
  <?php foreach($versions as $v): ?>
  <div style="font-size:12px;padding:6px 0;border-bottom:1px solid #F1F5F9;">
    <b>v<?= (int)$v->version; ?></b> — <?= htmlspecialchars($v->change_reason ?? ''); ?>
    <span style="color:var(--mp-muted);"><?= htmlspecialchars($v->created_date . ' ' . $v->created_time); ?></span>
  </div>
  <?php endforeach; ?>
</div>

<script>
var CSRF = {'<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'};
function createBill(){
  $.post('<?= base_url('patient_billing/create_bill/' . $plan->id); ?>', CSRF, function(r){
    if(r.status==='success'){ location.href='<?= base_url('patient_billing/view/'); ?>'+r.sales_id; } else alert(r.message);
  }, 'json');
}
function scheduleSession(entId, fee){
  var when = prompt('Schedule for (YYYY-MM-DD HH:MM):', '<?= date('Y-m-d H:i'); ?>');
  if(!when) return;
  $.post('<?= base_url('sessions/schedule'); ?>', Object.assign({entitlement_id: entId, scheduled_at: when, fee: fee.toFixed(2)}, CSRF), function(r){
    alert(r.message); if(r.status==='success') location.reload();
  }, 'json');
}
function sessAct(id, act){
  $.post('<?= base_url('sessions/'); ?>' + act + '/' + id, CSRF, function(r){ alert(r.message); if(r.status==='success') location.reload(); }, 'json');
}
function sessCancel(id){
  var reason = prompt('Cancellation reason (required):'); if(!reason) return;
  $.post('<?= base_url('sessions/cancel/'); ?>' + id, Object.assign({reason: reason}, CSRF), function(r){ alert(r.message); if(r.status==='success') location.reload(); }, 'json');
}
function sessReverse(id){
  var reason = prompt('Reversal reason (required):'); if(!reason) return;
  $.post('<?= base_url('sessions/reverse/'); ?>' + id, Object.assign({reason: reason}, CSRF), function(r){ alert(r.message); if(r.status==='success') location.reload(); }, 'json');
}
function amendPlan(){
  var reason = prompt('Amendment reason:'); if(reason === null) return;
  $.post('<?= base_url('treatment_plans/amend/' . $plan->id); ?>', Object.assign({reason: reason}, CSRF), function(r){ alert(r.message); if(r.status==='success') location.reload(); }, 'json');
}
</script>
