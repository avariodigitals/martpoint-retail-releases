<?php $CI =& get_instance();
$_store = $CI->db->where('id', get_current_store_id())->get('db_store')->row();
$_storeName = $_store->store_name ?? ($SITE_TITLE ?? 'MartPoint');
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Receipt <?= htmlspecialchars($payment->payment_code); ?></title>
<style>
@page { size: 80mm auto; margin: 2mm; }
body{width:76mm;margin:0 auto;font-family:'Courier New',monospace;font-size:12px}
.t-center{text-align:center}.t-line{border-top:1px dashed #000;margin:6px 0}
.t-row{display:flex;justify-content:space-between}
@media screen{body{border:1px solid #ccc;padding:4mm}}
</style></head>
<body onload="window.print()">
<div class="t-center">
  <div style="font-weight:bold;font-size:15px;"><?= htmlspecialchars($_storeName); ?></div>
  <div>PAYMENT RECEIPT</div>
</div>
<div class="t-line"></div>
<div class="t-row"><span>Receipt</span><b><?= htmlspecialchars($payment->payment_code); ?></b></div>
<div class="t-row"><span>Invoice</span><b><?= htmlspecialchars($bill->sales_code); ?></b></div>
<div class="t-row"><span>Patient</span><b><?= htmlspecialchars($patient ? $patient->patient_code : 'C-'.$bill->customer_id); ?></b></div>
<div class="t-row"><span>Date</span><b><?= htmlspecialchars($payment->payment_date . ' ' . $payment->created_time); ?></b></div>
<div class="t-row"><span>Method</span><b><?= htmlspecialchars(mp_code_label($payment->payment_type)); ?></b></div>
<div class="t-line"></div>
<div class="t-row" style="font-size:15px;"><span>RECEIVED</span><b><?= $CI->currency($payment->payment); ?></b></div>
<div class="t-row"><span>Bill balance</span><b><?= $CI->currency($bill->grand_total - $bill->paid_amount); ?></b></div>
<div class="t-line"></div>
<div class="t-center" style="font-size:10px;">Received by <?= htmlspecialchars($payment->created_by); ?><br>Thank you.</div>
</body></html>
