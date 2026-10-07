<?php $this->load->view('admin/desktop/_styles'); $CI =& get_instance(); ?>
<style>
.pb-table{width:100%!important;border-collapse:collapse!important;font-size:13px!important;background:#fff!important;border-radius:14px!important;overflow:hidden!important}
.pb-table th{text-align:left!important;padding:8px 10px!important;border-bottom:1px solid var(--mp-border)!important;font-size:11px!important;text-transform:uppercase!important;color:var(--mp-muted)!important}
.pb-table td{padding:9px 10px!important;border-bottom:1px solid #F1F5F9!important}
.pb-due{color:#991B1B!important;font-weight:700!important}
.pb-badge{font-size:10px!important;font-weight:700!important;padding:2px 8px!important;border-radius:12px!important}
.pb-Paid{background:#DCFCE7!important;color:#166534!important}.pb-Unpaid{background:#FEE2E2!important;color:#991B1B!important}.pb-Partial{background:#FEF3C7!important;color:#92400E!important}
</style>

<div class="mp-page-head">
  <div><h2><?= htmlspecialchars($page_title); ?></h2>
  <div class="mp-page-sub">Plan-generated bills · partial payments · visible outstanding debt</div></div>
</div>

<table class="pb-table">
  <tr><th>Invoice</th><th>Patient</th><th>Date</th><th>Total</th><th>Paid</th><th>Due</th><th>Status</th><th></th></tr>
  <?php foreach($bills as $b): $due = $b->grand_total - $b->paid_amount; ?>
  <tr>
    <td><a href="<?= base_url('patient_billing/view/' . $b->id); ?>"><?= htmlspecialchars($b->sales_code); ?></a></td>
    <td><?= htmlspecialchars($b->patient_name); ?> <span style="color:var(--mp-muted);font-size:11px;"><?= htmlspecialchars($b->patient_code); ?></span></td>
    <td><?= htmlspecialchars($b->sales_date); ?></td>
    <td><?= $CI->currency($b->grand_total); ?></td>
    <td><?= $CI->currency($b->paid_amount); ?></td>
    <td class="<?= $due>0?'pb-due':''; ?>"><?= $CI->currency($due); ?></td>
    <td><span class="pb-badge pb-<?= $b->payment_status; ?>"><?= htmlspecialchars($b->payment_status); ?></span></td>
    <td><a class="mp-qa-btn" href="<?= base_url('patient_billing/view/' . $b->id); ?>">Open</a></td>
  </tr>
  <?php endforeach; ?>
</table>
