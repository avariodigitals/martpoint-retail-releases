<?php $CI =& get_instance();
$storeId = get_current_store_id();
$_store = $CI->db->where('id', $storeId)->get('db_store')->row();
$_storeName = $_store->store_name ?? ($SITE_TITLE ?? 'MartPoint');
$patientScope = !empty($bill_patient)
  ? trim((string)(($bill_patient->customer_name ?? '') . (!empty($bill_patient->patient_code) ? ' · ' . $bill_patient->patient_code : '')))
  : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Patient Bills — <?= htmlspecialchars($bill_window ?? 'MartPoint'); ?></title>
<style>
@page { size: A4 landscape; margin: 12mm; }
* { box-sizing: border-box; }
body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 10pt; color: #1f2937; margin: 0; padding: 16px; background: #fff; }
.head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; gap: 12px; flex-wrap: wrap; }
.store-name { font-size: 15pt; font-weight: bold; color: #111827; }
.doc-title { font-size: 13pt; font-weight: bold; color: #111827; text-transform: uppercase; letter-spacing: .04em; }
.doc-sub { font-size: 9pt; color: #6b7280; }
table.items { width: 100%; border-collapse: collapse; border: 0.5pt solid #d1d5db; }
table.items th { background: #f3f4f6; color: #374151; font-size: 9pt; font-weight: bold; padding: 7px 8px; border-bottom: 0.5pt solid #d1d5db; text-align: left; }
table.items td { padding: 6px 8px; border-bottom: 0.5pt solid #e5e7eb; font-size: 9.5pt; }
table.items .num { text-align: right; white-space: nowrap; }
tr.due td { color: #991B1B; font-weight: bold; }
tfoot td { font-weight: bold; border-top: 1pt solid #111827; background: #f9fafb; }
.empty { color: #6b7280; padding: 14px 0; }
@media print { body { padding: 0; } }
</style>
</head>
<body onload="window.print()">
<div class="head">
  <div>
    <div class="store-name"><?= htmlspecialchars($_storeName); ?></div>
    <div class="doc-sub">Generated <?= date('d M Y H:i'); ?></div>
  </div>
  <div style="text-align:right;">
    <div class="doc-title">Patient Bills Statement</div>
    <?php if($patientScope !== ''): ?><div class="doc-sub"><?= htmlspecialchars($patientScope); ?></div><?php endif; ?>
    <div class="doc-sub"><?= htmlspecialchars($bill_window); ?> · <?= (int)$bill_totals['count']; ?> bill(s)</div>
  </div>
</div>
<?php if(empty($bills)): ?>
  <p class="empty">No bills in this window.</p>
<?php else: ?>
<table class="items">
  <thead>
    <tr><th>Invoice</th><th>Patient</th><th>Patient Code</th><th>Care</th><th>Date</th><th class="num">Total</th><th class="num">Paid</th><th class="num">Due</th><th>Status</th></tr>
  </thead>
  <tbody>
  <?php foreach($bills as $b): $due = round((float)$b->grand_total - (float)$b->paid_amount, 2);
    $ct = $b->care_type ?? null; ?>
    <tr>
      <td><?= htmlspecialchars($b->sales_code); ?></td>
      <td><?= htmlspecialchars($b->patient_name); ?></td>
      <td><?= htmlspecialchars($b->patient_code); ?></td>
      <td><?= $ct ? ucfirst(htmlspecialchars($ct)) : 'Not marked'; ?></td>
      <td><?= htmlspecialchars($b->sales_date); ?></td>
      <td class="num"><?= $CI->currency($b->grand_total); ?></td>
      <td class="num"><?= $CI->currency($b->paid_amount); ?></td>
      <td class="num<?= $due > 0 ? ' due' : ''; ?>"><?= $CI->currency($due); ?></td>
      <td><?= htmlspecialchars($b->payment_status); ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="5">Totals — <?= htmlspecialchars($bill_window); ?></td>
      <td class="num"><?= $CI->currency($bill_totals['charged']); ?></td>
      <td class="num"><?= $CI->currency($bill_totals['collected']); ?></td>
      <td class="num"><?= $CI->currency($bill_totals['outstanding']); ?></td>
      <td></td>
    </tr>
  </tfoot>
</table>
<?php endif; ?>
</body>
</html>
