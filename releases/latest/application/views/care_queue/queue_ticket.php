<?php $CI =& get_instance();
$_store = $CI->db->where('id', get_current_store_id())->get('db_store')->row();
$_storeName = $_store->store_name ?? ($SITE_TITLE ?? 'MartPoint');
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Queue Ticket <?= htmlspecialchars($enc->encounter_code); ?></title>
<style>
@page { size: 80mm auto; margin: 2mm; }
body{width:76mm;margin:0 auto;font-family:'Courier New',monospace;font-size:12px}
.t-center{text-align:center}.t-line{border-top:1px dashed #000;margin:6px 0}
.t-row{display:flex;justify-content:space-between;gap:8px}
.t-row span:last-child{text-align:right;font-weight:bold}
.t-big{font-size:22px;font-weight:bold;letter-spacing:2px}
@media screen{body{border:1px solid #ccc;padding:4mm}}
</style></head>
<body onload="window.print()">
<div class="t-center">
  <div style="font-weight:bold;font-size:15px;"><?= htmlspecialchars($_storeName); ?></div>
  <div>QUEUE TICKET</div>
</div>
<div class="t-line"></div>
<div class="t-row"><span>Patient</span><span><?= htmlspecialchars($patient->customer_name ?? '—'); ?></span></div>
<div class="t-row"><span>Patient no.</span><span><?= htmlspecialchars($patient->patient_code ?? '—'); ?></span></div>
<div class="t-row"><span>Visit</span><span><?= htmlspecialchars($enc->encounter_code); ?></span></div>
<div class="t-line"></div>
<div class="t-center">
  <div>QUEUE NUMBER</div>
  <div class="t-big"><?= (int)$queue_no; ?></div>
</div>
<div class="t-line"></div>
<div class="t-row"><span>Checked in</span><span><?= $enc->checkin_at ? date('d M Y H:i', strtotime($enc->checkin_at)) : '—'; ?></span></div>
<div class="t-row"><span>Stage</span><span><?= htmlspecialchars(mp_stage_label($enc->queue_stage)); ?></span></div>
<?php if(!empty($enc->clinician_name)): ?>
<div class="t-row"><span>With</span><span><?= htmlspecialchars(physio_staff_label($enc->clinician_name, $enc->clinician_role_id ?? null)); ?></span></div>
<?php endif; ?>
<div class="t-line"></div>
<?php if(empty($bills)): ?>
  <div class="t-center">No bill raised yet</div>
<?php else: ?>
  <?php foreach($bills as $b): ?>
    <div class="t-row"><span><?= htmlspecialchars($b->sales_code); ?></span><span><?= $CI->currency($b->grand_total); ?></span></div>
  <?php endforeach; ?>
  <div class="t-line"></div>
  <div class="t-row"><span>Billed</span><span><?= $CI->currency($bill_total); ?></span></div>
  <div class="t-row"><span>Paid</span><span><?= $CI->currency($bill_paid); ?></span></div>
  <div class="t-row" style="font-size:14px;"><span>Balance</span><span><?= $CI->currency($bill_due); ?></span></div>
<?php endif; ?>
<div class="t-line"></div>
<div class="t-center" style="font-size:10px;">
  Keep this ticket — your number is called in turn.<br>
  <?= date('d M Y H:i'); ?> · Issued by <?= htmlspecialchars($CI->session->userdata('inv_username') ?: 'Reception'); ?>
</div>
</body></html>
