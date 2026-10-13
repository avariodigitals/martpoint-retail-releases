<?php $CI =& get_instance();
if(!function_exists('mp_get_store_receipt_setting')){ $this->load->helper('store_settings'); }
$storeId = get_current_store_id();
$_store = $CI->db->where('id', $storeId)->get('db_store')->row();
$_storeName = $_store->store_name ?? ($SITE_TITLE ?? 'MartPoint');
$_addr = trim(implode(', ', array_filter(array(
	trim((string)($_store->address ?? '')), trim((string)($_store->city ?? '')), trim((string)($_store->postcode ?? '')),
))));
$_phone = trim(implode(' / ', array_filter(array(
	trim((string)($_store->phone ?? '')), trim((string)($_store->mobile ?? '')),
))));
$_email = trim((string)($_store->email ?? ''));
$_logo = function_exists('mp_get_store_theme_setting') ? mp_get_store_theme_setting($storeId, 'store_logo') : '';
if(empty($_logo) && function_exists('store_demo_logo')){ $_logo = store_demo_logo(); }
$_footer = function_exists('mp_get_store_receipt_setting')
	? mp_get_store_receipt_setting($storeId, 'sales_invoice_footer_text', '') : '';
$careType = $bill->care_type ?? null;
$careLabel = $careType === 'inpatient' ? 'Inpatient' : ($careType === 'outpatient' ? 'Outpatient' : 'Not marked');
$_billDate = $bill->sales_date ?? $bill->created_date ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Bill <?= htmlspecialchars($bill->sales_code); ?> — <?= htmlspecialchars($_storeName); ?></title>
<style>
@page { size: A4; margin: 14mm; }
* { box-sizing: border-box; }
body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 10pt; color: #1f2937; margin: 0; }
.head { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
.head td { vertical-align: middle; padding: 0; }
.head .logo { width: 96px; }
.head .logo img { max-width: 96px; max-height: 72px; }
.head .info { padding-left: 12px; }
.head .right { text-align: right; vertical-align: top; }
.store-name { font-size: 15pt; font-weight: bold; color: #111827; }
.store-meta { font-size: 9pt; color: #4b5563; line-height: 1.5; }
.doc-title { font-size: 14pt; font-weight: bold; color: #111827; text-transform: uppercase; letter-spacing: .04em; }
.doc-sub { font-size: 9pt; color: #6b7280; }

.panels { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
.panels td { width: 50%; vertical-align: top; padding: 10px 12px; border: 0.5pt solid #e5e7eb; }
.panel-title { font-size: 8pt; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
.panel-name { font-size: 11pt; font-weight: bold; color: #111827; }
.panel-detail { font-size: 9pt; color: #374151; line-height: 1.5; }

.items { width: 100%; border-collapse: collapse; border: 0.5pt solid #d1d5db; }
.items th { background: #f3f4f6; color: #374151; font-size: 9pt; font-weight: bold; padding: 8px 8px; border-bottom: 0.5pt solid #d1d5db; text-align: left; }
.items td { padding: 8px 8px; border-bottom: 0.5pt solid #e5e7eb; font-size: 9.5pt; vertical-align: top; }
.items .num { text-align: right; white-space: nowrap; }
.items .qty { text-align: center; white-space: nowrap; }
.items .sl { width: 6%; text-align: center; color: #6b7280; }

.totals { width: 100%; border-collapse: collapse; margin-top: 12px; }
.totals td { padding: 0; }
.totals-inner { width: 58%; margin-left: auto; border-collapse: collapse; }
.totals-inner td { padding: 6px 8px; font-size: 9.5pt; border-bottom: 0.5pt solid #e5e7eb; }
.totals-inner .label { text-align: right; color: #374151; white-space: nowrap; }
.totals-inner .value { text-align: right; font-weight: bold; white-space: nowrap; width: 42%; }
.totals-inner .grand td { font-size: 12pt; color: #111827; border-top: 1pt solid #111827; border-bottom: none; padding-top: 8px; }
.totals-inner .discount .value { color: #991B1B; }

.balance { margin-top: 14px; padding: 12px 14px; border: 1pt solid #111827; }
.balance .label { font-size: 8pt; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; }
.balance .value { font-size: 16pt; font-weight: bold; color: #111827; }
.balance.settled .value { color: #166534; }

.section-title { font-size: 10pt; font-weight: bold; color: #111827; text-transform: uppercase; letter-spacing: .04em; margin: 18px 0 6px; }

.foot { margin-top: 22px; }
.foot .note { font-size: 9pt; color: #4b5563; background: #f9fafb; border: 0.5pt solid #e5e7eb; padding: 10px 12px; }
.foot .legal { margin-top: 16px; padding-top: 10px; border-top: 0.5pt solid #e5e7eb; font-size: 8pt; color: #6b7280; text-align: center; }
.sign { margin-top: 34px; width: 100%; border-collapse: collapse; }
.sign td { width: 50%; padding-top: 6px; font-size: 8.5pt; color: #6b7280; }
.sign td:last-child { text-align: right; }
.sign .line { border-top: 0.5pt solid #9ca3af; padding-top: 5px; }
</style>
</head>
<body onload="window.print()">

<table class="head">
  <tr>
    <?php if(!empty($_logo)): ?><td class="logo"><img src="<?= htmlspecialchars($_logo); ?>" alt=""></td><?php endif; ?>
    <td class="info">
      <div class="store-name"><?= htmlspecialchars($_storeName); ?></div>
      <div class="store-meta">
        <?php if($_addr !== ''): ?><?= htmlspecialchars($_addr); ?><br><?php endif; ?>
        <?php if($_phone !== ''): ?><?= htmlspecialchars($_phone); ?><br><?php endif; ?>
        <?php if($_email !== ''): ?><?= htmlspecialchars($_email); ?><?php endif; ?>
      </div>
    </td>
    <td class="right">
      <div class="doc-title">Patient Bill</div>
      <div class="doc-sub">Bill no: <b><?= htmlspecialchars($bill->sales_code); ?></b></div>
    </td>
  </tr>
</table>

<table class="panels">
  <tr>
    <td>
      <div class="panel-title">Billed to</div>
      <div class="panel-name"><?= htmlspecialchars($patient_name !== '' ? $patient_name : ($patient_code !== '' ? $patient_code : 'Customer #'.$bill->customer_id)); ?></div>
      <div class="panel-detail">
        <?php if($patient_code !== ''): ?>Patient code: <?= htmlspecialchars($patient_code); ?><br><?php endif; ?>
        <?php if($customer && !empty($customer->mobile)): ?>Phone: <?= htmlspecialchars($customer->mobile); ?><br><?php endif; ?>
        <?php if($customer && !empty($customer->address)): ?><?= htmlspecialchars($customer->address); ?><?php endif; ?>
      </div>
    </td>
    <td>
      <div class="panel-title">Bill details</div>
      <div class="panel-detail">
        Bill date: <b><?= htmlspecialchars($_billDate); ?></b><br>
        Care type: <b><?= htmlspecialchars($careLabel); ?></b><br>
        Status: <b><?= htmlspecialchars(mp_code_label($bill->payment_status)); ?></b>
      </div>
    </td>
  </tr>
</table>

<table class="items">
  <thead>
    <tr>
      <th class="sl">#</th>
      <th>Service</th>
      <th class="qty">Qty</th>
      <th class="num">Unit price</th>
      <th class="num">Amount</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($items as $i => $it): ?>
    <tr>
      <td class="sl"><?= (int)$i + 1; ?></td>
      <td><?= htmlspecialchars($it->description); ?></td>
      <td class="qty"><?= (float)$it->qty; ?></td>
      <td class="num"><?= $CI->currency($it->unit_price); ?></td>
      <td class="num"><?= $CI->currency($it->total); ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($items)): ?>
    <tr><td colspan="5" style="color:#6b7280;">No itemised service lines on this bill.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<table class="totals">
  <tr><td>
    <table class="totals-inner">
      <tr><td class="label">Subtotal</td><td class="value"><?= $CI->currency($bill->subtotal); ?></td></tr>
      <?php if((float)$bill->tot_discount_to_all_amt > 0): ?>
      <tr class="discount"><td class="label">Approved discount</td><td class="value">-<?= $CI->currency($bill->tot_discount_to_all_amt); ?></td></tr>
      <?php endif; ?>
      <tr class="grand"><td class="label">Bill total</td><td class="value"><?= $CI->currency($bill->grand_total); ?></td></tr>
    </table>
  </td></tr>
</table>

<div class="section-title">Payments received</div>
<table class="items">
  <thead>
    <tr>
      <th class="sl">#</th>
      <th>Receipt</th>
      <th>Date</th>
      <th>Method</th>
      <th class="num">Amount</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($payments as $i => $p): ?>
    <tr>
      <td class="sl"><?= (int)$i + 1; ?></td>
      <td><?= htmlspecialchars($p->payment_code); ?></td>
      <td><?= htmlspecialchars($p->payment_date); ?></td>
      <td><?= htmlspecialchars(mp_code_label($p->payment_type)); ?></td>
      <td class="num"><?= $CI->currency($p->payment); ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($payments)): ?>
    <tr><td colspan="5" style="color:#6b7280;">No payments received yet.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<div class="balance <?= $due > 0 ? '' : 'settled'; ?>">
  <div class="label"><?= $due > 0 ? 'Balance outstanding' : 'Balance'; ?></div>
  <div class="value"><?= $CI->currency(max(0, $due)); ?></div>
  <div style="font-size:9pt;color:#4b5563;margin-top:4px;">
    Bill total <?= $CI->currency($bill->grand_total); ?> &minus; paid to date <?= $CI->currency($paid); ?>
  </div>
</div>

<div class="foot">
  <?php if(trim($_footer) !== ''): ?>
  <div class="note"><?= nl2br(htmlspecialchars($_footer)); ?></div>
  <?php endif; ?>
  <table class="sign">
    <tr>
      <td><div class="line">Prepared by</div></td>
      <td><div class="line">Authorised signature</div></td>
    </tr>
  </table>
  <div class="legal">Generated by <?= htmlspecialchars($_storeName); ?> &middot; <?= htmlspecialchars(date('d M Y H:i')); ?></div>
</div>

</body>
</html>
