<?php $CI =& get_instance();
$_store = $CI->db->where('id', get_current_store_id())->get('db_store')->row();
$_store = mp_document_store($_store, 'db_sales', $bill->id);
$_storeName = $_store->store_name ?? ($SITE_TITLE ?? 'MartPoint');
$_paidBefore = round((float)$bill->paid_amount - (float)$payment->payment, 2);
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Receipt <?= htmlspecialchars($payment->payment_code); ?></title>
<style>
@page { size: 80mm auto; margin: 2mm; }
body{width:76mm;margin:0 auto;font-family:'Courier New',monospace;font-size:12px}
.t-center{text-align:center}.t-line{border-top:1px dashed #000;margin:6px 0}
.t-row{display:flex;justify-content:space-between;gap:8px}
.t-row span:first-child{flex:0 0 auto}
.t-row b{text-align:right}
.t-item{margin:3px 0}
.t-item .t-desc{word-wrap:break-word}
.t-item .t-amt{display:flex;justify-content:space-between;font-size:11px}
.t-total{font-size:15px}
.t-due{font-size:14px;font-weight:bold}
@media screen{body{border:1px solid #ccc;padding:4mm}}
</style></head>
<body onload="window.print()">
<div class="t-center">
  <div style="font-weight:bold;font-size:15px;"><?= htmlspecialchars($_storeName); ?></div>
  <?= mp_document_location_html($_store); ?>
  <div>PAYMENT RECEIPT</div>
</div>
<div class="t-line"></div>
<div class="t-row"><span>Receipt</span><b><?= htmlspecialchars($payment->payment_code); ?></b></div>
<div class="t-row"><span>Bill</span><b><?= htmlspecialchars($bill->sales_code); ?></b></div>
<div class="t-row"><span>Patient</span><b><?= htmlspecialchars($patient_code !== '' ? $patient_code : ($patient_name !== '' ? $patient_name : 'C-'.$bill->customer_id)); ?></b></div>
<div class="t-row"><span>Date</span><b><?= htmlspecialchars($payment->payment_date . ' ' . $payment->created_time); ?></b></div>
<div class="t-row"><span>Method</span><b><?= htmlspecialchars(mp_code_label($payment->payment_type)); ?></b></div>
<div class="t-line"></div>

<div><b>THIS BILL COVERS</b></div>
<?php foreach($items as $it): ?>
<div class="t-item">
  <div class="t-desc"><?= htmlspecialchars($it->description); ?></div>
  <div class="t-amt"><span><?= (float)$it->qty; ?> x <?= $CI->currency($it->unit_price); ?></span><b><?= $CI->currency($it->total); ?></b></div>
</div>
<?php endforeach; ?>
<?php if(empty($items)): ?><div class="t-item">No itemised lines.</div><?php endif; ?>

<div class="t-line"></div>
<div class="t-row"><span>Bill total</span><b><?= $CI->currency($bill->grand_total); ?></b></div>
<div class="t-row"><span>Paid before today</span><b><?= $CI->currency($_paidBefore); ?></b></div>
<div class="t-row t-total"><span>RECEIVED TODAY</span><b><?= $CI->currency($payment->payment); ?></b></div>
<div class="t-row"><span>Paid to date</span><b><?= $CI->currency($bill->paid_amount); ?></b></div>
<div class="t-line"></div>
<div class="t-row t-due"><span>BALANCE OUTSTANDING</span><b><?= $CI->currency(max(0, $bill->grand_total - $bill->paid_amount)); ?></b></div>
<div class="t-line"></div>
<div class="t-center" style="font-size:10px;">
  Received by <?= htmlspecialchars($payment->by_name ?: 'staff'); ?><br>
  This receipt confirms the payment above towards bill <?= htmlspecialchars($bill->sales_code); ?>.<br>
  Thank you.
</div>
</body></html>
