<?php $CI =& get_instance();
if(!function_exists('mp_get_store_receipt_setting')){ $this->load->helper('store_settings'); }
$storeId = get_current_store_id();
$_store = $CI->db->where('id', $storeId)->get('db_store')->row();
$_store = mp_document_store($_store, 'db_sales', $bill->id);
$_storeName = $_store->store_name ?? ($SITE_TITLE ?? 'MartPoint');
$_addr = trim(implode(', ', array_filter(array(
	trim((string)($_store->address ?? '')), trim((string)($_store->city ?? '')),
))));
$_phone = trim((string)($_store->phone ?: ($_store->mobile ?? '')));
$_footer = function_exists('mp_get_store_receipt_setting')
	? mp_get_store_receipt_setting($storeId, 'sales_invoice_footer_text', '') : '';
$careType = $bill->care_type ?? null;
$careLabel = $careType === 'inpatient' ? 'Inpatient' : ($careType === 'outpatient' ? 'Outpatient' : 'Not marked');
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Bill <?= htmlspecialchars($bill->sales_code); ?></title>
<style>
@page { size: 80mm auto; margin: 2mm; }
body{width:76mm;margin:0 auto;font-family:'Courier New',monospace;font-size:12px}
.t-center{text-align:center}.t-line{border-top:1px dashed #000;margin:6px 0}
.t-row{display:flex;justify-content:space-between;gap:8px}
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
  <?php if($_addr !== ''): ?><div style="font-size:10px;"><?= htmlspecialchars($_addr); ?></div><?php endif; ?>
  <?php if($_phone !== ''): ?><div style="font-size:10px;"><?= htmlspecialchars($_phone); ?></div><?php endif; ?>
  <div style="margin-top:4px;">PATIENT BILL</div>
</div>
<div class="t-line"></div>
<div class="t-row"><span>Bill no</span><b><?= htmlspecialchars($bill->sales_code); ?></b></div>
<div class="t-row"><span>Date</span><b><?= htmlspecialchars($bill->sales_date ?? $bill->created_date ?? ''); ?></b></div>
<div class="t-row"><span>Patient</span><b><?= htmlspecialchars($patient_code !== '' ? $patient_code : ($patient_name !== '' ? $patient_name : 'C-'.$bill->customer_id)); ?></b></div>
<?php if($patient_name !== ''): ?>
<div class="t-row"><span>Name</span><b><?= htmlspecialchars($patient_name); ?></b></div>
<?php endif; ?>
<div class="t-row"><span>Care type</span><b><?= htmlspecialchars($careLabel); ?></b></div>
<div class="t-line"></div>

<div><b>ITEMISED SERVICES</b></div>
<?php foreach($items as $it): ?>
<div class="t-item">
  <div class="t-desc"><?= htmlspecialchars($it->description); ?></div>
  <div class="t-amt"><span><?= (float)$it->qty; ?> x <?= $CI->currency($it->unit_price); ?></span><b><?= $CI->currency($it->total); ?></b></div>
</div>
<?php endforeach; ?>
<?php if(empty($items)): ?><div class="t-item">No itemised lines.</div><?php endif; ?>

<div class="t-line"></div>
<div class="t-row"><span>Subtotal</span><b><?= $CI->currency($bill->subtotal); ?></b></div>
<?php if((float)$bill->tot_discount_to_all_amt > 0): ?>
<div class="t-row"><span>Discount</span><b>-<?= $CI->currency($bill->tot_discount_to_all_amt); ?></b></div>
<?php endif; ?>
<div class="t-row t-total"><span>BILL TOTAL</span><b><?= $CI->currency($bill->grand_total); ?></b></div>
<div class="t-line"></div>

<div><b>PAYMENTS SO FAR</b></div>
<?php foreach($payments as $p): ?>
<div class="t-row"><span><?= htmlspecialchars($p->payment_date); ?> <?= htmlspecialchars(mp_code_label($p->payment_type)); ?></span><b><?= $CI->currency($p->payment); ?></b></div>
<?php endforeach; ?>
<?php if(empty($payments)): ?><div class="t-item">No payments yet.</div><?php endif; ?>

<div class="t-line"></div>
<div class="t-row"><span>Paid to date</span><b><?= $CI->currency($paid); ?></b></div>
<div class="t-row t-due"><span>BALANCE OUTSTANDING</span><b><?= $CI->currency(max(0, $due)); ?></b></div>
<div class="t-line"></div>
<div class="t-center" style="font-size:10px;">
  <?= $due > 0 ? 'Balance is payable at the next visit.' : 'This bill is fully paid. Thank you.' ?>
  <?php if(trim($_footer) !== ''): ?><br><?= nl2br(htmlspecialchars($_footer)); ?><?php endif; ?>
</div>
</body></html>
