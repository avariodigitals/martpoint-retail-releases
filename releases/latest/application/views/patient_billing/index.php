<?php $this->load->view('admin/desktop/_styles'); $CI =& get_instance(); ?>
<style>
.pb-table{width:100%!important;border-collapse:collapse!important;font-size:13px!important;background:#fff!important;border-radius:14px!important;overflow:hidden!important}
.pb-table th{text-align:left!important;padding:8px 10px!important;border-bottom:1px solid var(--mp-border)!important;font-size:11px!important;text-transform:uppercase!important;color:var(--mp-muted)!important}
.pb-table td{padding:9px 10px!important;border-bottom:1px solid #F1F5F9!important}
.pb-due{color:#991B1B!important;font-weight:700!important}
.pb-badge{font-size:10px!important;font-weight:700!important;padding:2px 8px!important;border-radius:12px!important}
.pb-Paid{background:#DCFCE7!important;color:#166534!important}.pb-Unpaid{background:#FEE2E2!important;color:#991B1B!important}.pb-Partial{background:#FEF3C7!important;color:#92400E!important}
.pb-days{display:flex!important;align-items:center!important;gap:6px!important;flex-wrap:wrap!important;margin:0 0 14px!important}
.pb-days-lbl{font-size:10px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin-right:2px!important}
.pb-days a{font-size:11px!important;font-weight:700!important;padding:5px 11px!important;border-radius:999px!important;border:1px solid var(--mp-border)!important;color:var(--mp-muted)!important;text-decoration:none!important;background:#fff!important}
.pb-days a.on{background:#EEF2FF!important;border-color:#6366F1!important;color:#4338CA!important}
.pb-days-note{font-size:10px!important;color:#94a3b8!important;font-weight:600!important;margin-left:6px!important}
.pb-search{width:100%!important;max-width:420px!important;font-size:13px!important;padding:8px 12px!important;border:1px solid var(--mp-border,#E2E8F0)!important;border-radius:10px!important;background:#fff!important;margin:0 0 12px!important}
.pb-table tr.pb-hidden{display:none!important}
.pb-nomatch{display:none!important;color:var(--mp-muted)!important;padding:16px 10px!important}
.pb-totals{display:flex!important;gap:8px!important;flex-wrap:wrap!important;margin:0 0 12px!important}
.pb-totals span{font-size:11px!important;font-weight:700!important;padding:5px 11px!important;border-radius:999px!important;border:1px solid var(--mp-border)!important;background:#fff!important;color:var(--mp-muted)!important}
.pb-totals b{color:#0F172A!important}
.pb-totals .pb-actions{margin-left:auto;display:flex!important;gap:6px!important}
.pb-btn{font-size:11px!important;font-weight:700!important;padding:5px 11px!important;border-radius:999px!important;border:1px solid var(--mp-border)!important;color:var(--mp-muted)!important;text-decoration:none!important;background:#fff!important;white-space:nowrap!important}
.pb-btn:hover{border-color:#6366F1!important;color:#4338CA!important}
</style>

<div class="mp-page-head">
  <div><h2><?= htmlspecialchars($page_title); ?></h2>
  <div class="mp-page-sub">Plan-generated bills · partial payments · visible outstanding debt</div></div>
</div>

<div class="pb-days">
  <span class="pb-days-lbl">Show</span>
  <?php foreach(array(1 => 'Today', 7 => '7 days', 30 => '30 days', 365 => 'This year', 0 => 'All') as $d => $lbl): ?>
    <a class="<?= (int)$bill_days === $d ? 'on' : ''; ?>" href="<?= base_url('patient_billing') . '?days=' . $d; ?>"><?= $lbl; ?></a>
  <?php endforeach; ?>
  <span class="pb-days-note">Bills raised in this window.
    <?php if((int)$bill_hidden > 0): ?>
      <b style="color:#B45309"><?= (int)$bill_hidden ?> older bill(s) are outside it</b> &mdash;
      <a href="<?= base_url('patient_billing') . '?days=0'; ?>" style="text-decoration:underline">show everything</a>.
    <?php endif; ?>
  </span>
</div>

<div class="pb-totals">
  <span>Bills in window: <b><?= (int)$bill_totals['count']; ?></b></span>
  <span>Charged: <b><?= $CI->currency($bill_totals['charged']); ?></b></span>
  <span>Collected: <b><?= $CI->currency($bill_totals['collected']); ?></b></span>
  <span>Outstanding: <b style="color:#991B1B;"><?= $CI->currency($bill_totals['outstanding']); ?></b></span>
  <span class="pb-actions">
    <a class="pb-btn" href="<?= base_url('patient_billing/export') . '?days=' . (int)$bill_days; ?>"><i class="fa fa-download"></i> CSV</a>
    <a class="pb-btn" href="<?= base_url('patient_billing/export_pdf') . '?days=' . (int)$bill_days; ?>" target="_blank"><i class="fa fa-file-pdf-o"></i> PDF</a>
  </span>
</div>

<input type="text" id="pbSearch" class="pb-search" placeholder="Search invoice no, patient name or code, status…" autocomplete="off">

<table class="pb-table">
  <tr><th>Invoice</th><th>Patient</th><th>Care</th><th>Date</th><th>Total</th><th>Paid</th><th>Due</th><th>Status</th><th></th></tr>
  <?php if(empty($bills)): ?>
  <tr><td colspan="9" style="color:var(--mp-muted);padding:16px 10px;">No bills in this window<?php if((int)$bill_hidden > 0): ?> — use the choices above to see older ones<?php endif; ?>.</td></tr>
  <?php endif; ?>
  <tr id="pbNoMatch" style="display:none;"><td colspan="9" style="color:var(--mp-muted);padding:16px 10px;">No bills in this list match your search. Clear the box or widen the window above.</td></tr>
  <?php foreach($bills as $b): $due = $b->grand_total - $b->paid_amount;
    $ct = $b->care_type ?? null; ?>
  <tr class="pb-row">
    <td><a href="<?= base_url('patient_billing/view/' . $b->id); ?>"><?= htmlspecialchars($b->sales_code); ?></a></td>
    <td><?= htmlspecialchars($b->patient_name); ?> <span style="color:var(--mp-muted);font-size:11px;"><?= htmlspecialchars($b->patient_code); ?></span></td>
    <td><span class="pb-badge" style="<?= $ct === 'inpatient' ? 'background:#EEF2FF;color:#4338CA;' : ($ct === 'outpatient' ? 'background:#ECFDF5;color:#065F46;' : 'background:#F1F5F9;color:#64748B;'); ?>"><?= $ct ? ucfirst($ct) : 'Not marked'; ?></span></td>
    <td><?= htmlspecialchars($b->sales_date); ?></td>
    <td><?= $CI->currency($b->grand_total); ?></td>
    <td><?= $CI->currency($b->paid_amount); ?></td>
    <td class="<?= $due>0?'pb-due':''; ?>"><?= $CI->currency($due); ?></td>
    <td><span class="pb-badge pb-<?= $b->payment_status; ?>"><?= htmlspecialchars($b->payment_status); ?></span></td>
    <td><a class="mp-qa-btn" href="<?= base_url('patient_billing/view/' . $b->id); ?>">Open</a></td>
  </tr>
  <?php endforeach; ?>
</table>

<script>
$('#pbSearch').on('input', function(){
  var q = $.trim($(this).val()).toLowerCase(), hits = 0;
  $('.pb-row').each(function(){
    var hit = q === '' || $(this).text().toLowerCase().indexOf(q) !== -1;
    $(this).toggleClass('pb-hidden', !hit);
    if(hit) hits++;
  });
  $('#pbNoMatch').toggle(q !== '' && hits === 0);
});
</script>
