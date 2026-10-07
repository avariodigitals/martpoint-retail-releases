<?php $CI =& get_instance();
// 80mm session ticket — no clinical details (no diagnosis/assessment/notes).
$_store = $CI->db->where('id', get_current_store_id())->get('db_store')->row();
$_storeName = $_store->store_name ?? ($SITE_TITLE ?? 'MartPoint');
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Session Ticket</title>
<style>
@page { size: 80mm auto; margin: 2mm; }
body { width: 76mm; margin: 0 auto; font-family: 'Courier New', monospace; font-size: 12px; color: #000; }
.t-center { text-align: center; }
.t-line { border-top: 1px dashed #000; margin: 6px 0; }
.t-row { display: flex; justify-content: space-between; }
.t-big { font-size: 16px; font-weight: bold; }
@media screen { body { border: 1px solid #ccc; padding: 4mm; } }
</style></head>
<body onload="window.print()">

<div class="t-center">
  <div class="t-big"><?= htmlspecialchars($_storeName); ?></div>
  <div>TREATMENT SESSION TICKET</div>
</div>
<div class="t-line"></div>

<div class="t-row"><span>Patient</span><b><?= htmlspecialchars($patient->patient_code); ?></b></div>
<div class="t-row"><span>Visit Ref</span><b>SS-<?= (int)$session->id; ?></b></div>
<div class="t-row"><span>Date</span><b><?= date('d M Y H:i', strtotime($session->scheduled_at ?: $session->created_date)); ?></b></div>
<div class="t-line"></div>

<div class="t-center t-big"><?= htmlspecialchars($treatment); ?></div>
<div class="t-center" style="font-size:15px;margin:4px 0;">Session <?= (int)$session->session_no; ?> of <?= (int)$session->units_total; ?></div>
<div class="t-line"></div>

<div class="t-row"><span>Session fee</span><b><?= $CI->currency($session->fee); ?></b></div>
<div class="t-row"><span>Status</span><b><?= htmlspecialchars(mp_code_label($session->status)); ?></b></div>
<div class="t-row"><span>Payment</span><b><?= strtoupper($payment_status); ?></b></div>
<div class="t-line"></div>
<div class="t-center" style="font-size:10px;">Keep this ticket — it is your session record.<br>Printed <?= date('d M Y H:i'); ?></div>

</body></html>
