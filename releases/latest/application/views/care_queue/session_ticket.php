<?php $CI =& get_instance();
$_store = $CI->db->where('id', get_current_store_id())->get('db_store')->row();
$_storeName = $_store->store_name ?? ($SITE_TITLE ?? 'MartPoint');
$_total = (float)$ent->units_total;
$_used  = (float)$ent->units_used;
$_left  = max(0, $_total - $_used);
$today  = isset($today) ? $today : null;
$note   = isset($note) ? $note : null;

/* The count on this slip is money, not arrival: a paid session is drawn only
   when the physio marks the visit complete. Say what today does to the number,
   otherwise a checked-in patient reads "5 of 5" as if nothing happened. */
$todayStatus = 'NOT STARTED';
$drawsToday  = true;
if($today){
	if(!empty($today->reversed)){ $todayStatus = 'REVERSED'; $drawsToday = false; }
	elseif($today->status === 'completed'){ $todayStatus = 'COMPLETED'; $drawsToday = false; }
	elseif($today->status === 'cancelled'){ $todayStatus = 'CANCELLED'; $drawsToday = false; }
	elseif($today->status === 'no_show'){ $todayStatus = 'DID NOT ATTEND'; $drawsToday = false; }
	else{ $todayStatus = 'IN PROGRESS'; }
}
$afterToday = $drawsToday ? max(0, (int)$_left - 1) : null;
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Session Ticket <?= htmlspecialchars($patient->patient_code ?? ''); ?></title>
<style>
@page { size: 80mm auto; margin: 2mm; }
body{width:76mm;margin:0 auto;font-family:'Courier New',monospace;font-size:12px}
.t-center{text-align:center}.t-line{border-top:1px dashed #000;margin:6px 0}
.t-row{display:flex;justify-content:space-between;gap:8px}
.t-row span:last-child{text-align:right;font-weight:bold}
.t-big{font-size:26px;font-weight:bold;letter-spacing:2px}
.t-bar{border:1px solid #000;padding:4px 6px;margin:4px 0}
@media screen{body{border:1px solid #ccc;padding:4mm}}
</style></head>
<body onload="window.print()">
<div class="t-center">
  <div style="font-weight:bold;font-size:15px;"><?= htmlspecialchars($_storeName); ?></div>
  <div>SESSION TICKET</div>
</div>
<div class="t-line"></div>
<div class="t-row"><span>Patient</span><span><?= htmlspecialchars($patient->customer_name ?? '—'); ?></span></div>
<div class="t-row"><span>Patient no.</span><span><?= htmlspecialchars($patient->patient_code ?? '—'); ?></span></div>
<div class="t-row"><span>Package</span><span><?= htmlspecialchars($ent->treatment_name ?: 'Treatment sessions'); ?></span></div>
<?php if($enc): ?>
<div class="t-row"><span>Visit</span><span><?= htmlspecialchars($enc->encounter_code); ?></span></div>
<div class="t-row"><span>Checked in</span><span><?= $enc->checkin_at ? date('d M H:i', strtotime($enc->checkin_at)) : '—'; ?></span></div>
<?php endif; ?>
<div class="t-line"></div>
<div class="t-center">
  <div>SESSIONS REMAINING</div>
  <div class="t-big"><?= (int)$_left; ?></div>
  <div>of <?= (int)$_total; ?> in this package</div>
</div>
<div class="t-bar">
  <div class="t-row"><span>Today’s session</span><span><?= $todayStatus; ?></span></div>
  <?php if($today && (int)$today->session_no > 0): ?>
    <div class="t-row"><span>Which one</span><span><?= (int)$today->session_no; ?> of <?= (int)$today->units_total; ?></span></div>
  <?php endif; ?>
  <?php if(!empty($note)): ?>
    <div style="border-top:1px dashed #000;margin-top:4px;padding-top:4px;">
      <div class="t-row"><span>Treatment given</span><span><?= htmlspecialchars(mb_substr((string)$note->treatment_offered, 0, 42)); ?></span></div>
      <?php if(!empty($note->treated_by_name) && $note->treated_by_name !== $note->signed_by_name): ?>
      <div class="t-row"><span>Given by</span><span><?= htmlspecialchars($note->treated_by_name); ?></span></div>
      <?php endif; ?>
      <div class="t-row"><span>Signed by</span><span><?= htmlspecialchars($note->signed_by_name ?: '—'); ?></span></div>
    </div>
  <?php endif; ?>
  <?php if($afterToday !== null): ?>
    <div style="font-size:10px;">It is counted when the physio marks this visit complete — <b><?= $afterToday; ?> of <?= (int)$_total; ?></b> will remain.</div>
  <?php else: ?>
    <div style="font-size:10px;">Already reflected in the figure above.</div>
  <?php endif; ?>
</div>
<div class="t-bar"><div class="t-row"><span>Completed so far</span><span><?= (int)$_used; ?></span></div></div>
<div class="t-line"></div>
<?php if($ent->sales_code): ?>
<div class="t-row"><span>Package bill</span><span><?= htmlspecialchars($ent->sales_code); ?></span></div>
<div class="t-row"><span>Bill status</span><span><?= htmlspecialchars(mp_code_label($ent->payment_status ?: 'Paid')); ?></span></div>
<?php endif; ?>
<?php if($ent->expiry_date): ?>
<div class="t-row"><span>Valid until</span><span><?= htmlspecialchars($ent->expiry_date); ?></span></div>
<?php endif; ?>
<div class="t-line"></div>
<div class="t-center" style="font-size:10px;">
  Present this ticket at each session — one session is counted when a visit is completed.<br>
  <?= date('d M Y H:i'); ?> · Issued by <?= htmlspecialchars($CI->session->userdata('inv_username') ?: 'Reception'); ?>
</div>
</body></html>
