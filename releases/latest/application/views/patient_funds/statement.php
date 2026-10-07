<?php $CI =& get_instance();
// Patient statement — separate sections, traceable lines, no clinical details.
$_store = $CI->db->where('id', get_current_store_id())->get('db_store')->row();
$_storeName = $_store->store_name ?? ($SITE_TITLE ?? 'MartPoint');
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Patient Statement — <?= htmlspecialchars($patient->patient_code); ?></title>
<style>
body{font-family:Arial,sans-serif;font-size:13px;color:#111;max-width:760px;margin:20px auto;padding:0 12px}
h1{font-size:18px;margin:0} h2{font-size:13px;text-transform:uppercase;letter-spacing:.5px;color:#555;border-bottom:1px solid #ddd;padding-bottom:4px;margin-top:22px}
table{width:100%;border-collapse:collapse;font-size:12px} th{text-align:left;padding:5px 6px;border-bottom:2px solid #333} td{padding:5px 6px;border-bottom:1px solid #eee}
.bal{display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin:14px 0}
.bal div{border:1px solid #ddd;border-radius:8px;padding:10px}
.bal .l{font-size:9px;text-transform:uppercase;color:#777}.bal .v{font-size:16px;font-weight:700}
.cr{color:#166534}.db{color:#991B1B}.mm{color:#555}
@media print{body{margin:0}}
</style></head>
<body>

<h1><?= htmlspecialchars($_storeName); ?> — Patient Statement</h1>
<div>Patient: <b><?= htmlspecialchars($patient->customer_name ?? ''); ?></b> (<?= htmlspecialchars($patient->patient_code); ?>) · <?= date('d M Y H:i'); ?></div>

<div class="bal">
  <div><div class="l">Available funds</div><div class="v cr"><?= $CI->currency($balances['available']); ?></div></div>
  <div><div class="l">Reserved</div><div class="v"><?= $CI->currency($balances['reserved']); ?></div></div>
  <div><div class="l">Pending verification</div><div class="v"><?= $CI->currency($balances['pending']); ?></div></div>
  <div><div class="l">Outstanding debt</div><div class="v db"><?= $CI->currency($outstanding); ?></div></div>
  <div><div class="l">Sessions remaining</div><div class="v"><?php $rem=0; foreach($entitlements as $e){ if($e->status==='active') $rem += $e->units_total - $e->units_used; } echo (float)$rem; ?></div></div>
</div>

<h2>Bills &amp; outstanding debt</h2>
<table><tr><th>Invoice</th><th>Date</th><th>Total</th><th>Paid</th><th>Due</th><th>Status</th></tr>
<?php foreach($bills as $b): $d = $b->grand_total - $b->paid_amount; ?>
<tr><td><?= htmlspecialchars($b->sales_code); ?></td><td><?= htmlspecialchars($b->sales_date); ?></td>
<td><?= $CI->currency($b->grand_total); ?></td><td><?= $CI->currency($b->paid_amount); ?></td>
<td class="<?= $d>0?'db':''; ?>"><?= $CI->currency($d); ?></td><td><?= htmlspecialchars($b->payment_status); ?></td></tr>
<?php endforeach; ?></table>

<h2>Session entitlements</h2>
<table><tr><th>Plan</th><th>Source</th><th>Total</th><th>Used</th><th>Remaining</th><th>Funded / consumed</th></tr>
<?php foreach($entitlements as $e): ?>
<tr><td><?= $e->plan_id ? '#'.(int)$e->plan_id : '—'; ?></td><td><?= htmlspecialchars($e->source); ?></td>
<td><?= (float)$e->units_total; ?></td><td><?= (float)$e->units_used; ?></td><td><?= (float)($e->units_total - $e->units_used); ?></td>
<td><?= $CI->currency($e->funded_amount); ?> / <?= $CI->currency($e->consumed_amount); ?></td></tr>
<?php endforeach; ?></table>

<h2>Funds ledger</h2>
<table><tr><th>#</th><th>Date</th><th>Type</th><th>Amount</th><th>Trace</th><th>Note</th></tr>
<?php foreach($ledger as $t):
  $cls = $t->direction==='credit'?'cr':($t->direction==='debit'?'db':'mm');
  $trace = $t->operation_key . ($t->salespayment_id ? ' · SP-'.$t->salespayment_id : '') . ($t->sales_id ? ' · SALE-'.$t->sales_id : '') . ($t->custadvance_id ? ' · ADV-'.$t->custadvance_id : '') . ($t->reservation_id ? ' · RES-'.$t->reservation_id : '');
?>
<tr><td><?= (int)$t->id; ?></td><td><?= htmlspecialchars($t->created_date.' '.$t->created_time); ?></td>
<td><?= htmlspecialchars($t->txn_type); ?></td>
<td class="<?= $cls; ?>"><?= $t->direction==='debit'?'−':($t->direction==='credit'?'+':''); ?><?= $CI->currency($t->amount); ?></td>
<td style="font-family:monospace;font-size:10px;"><?= htmlspecialchars($trace); ?></td>
<td><?= htmlspecialchars($t->note); ?></td></tr>
<?php endforeach; ?></table>

<h2>Pending payment evidence</h2>
<table><tr><th>Ref</th><th>Channel</th><th>Amount</th><th>Status</th></tr>
<?php foreach($evidence as $ev): if($ev->status!=='submitted') continue; ?>
<tr><td><?= htmlspecialchars($ev->payment_ref); ?></td><td><?= htmlspecialchars($ev->channel); ?></td>
<td><?= $CI->currency($ev->amount); ?></td><td>SUBMITTED</td></tr>
<?php endforeach; ?></table>

</body></html>
