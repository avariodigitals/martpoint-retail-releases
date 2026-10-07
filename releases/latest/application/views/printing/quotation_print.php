<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
/**
 * Printing quotation — PDF (standalone document for dompdf).
 *
 * The shared retail PDF template does not carry printing terms, so this
 * renders the SAME authoritative quotation records with the printing sections:
 * agreed specifications, explicit charges, tax, deposit/balance, turnaround
 * conditions and acceptance instructions.
 *
 * Internal costing, margins and referral data are deliberately absent.
 */
$CI =& get_instance();
$store_id = get_current_store_id();

$store_logo_path = mp_get_store_theme_setting($store_id, 'store_logo');
$store_logo = !empty($store_logo_path) ? $store_logo_path : store_demo_logo();
$logo_data = mp_store_logo_round_base64($store_logo, 90);
$terms = mp_get_store_receipt_setting($store_id, 'invoice_terms', '');

$s = $summary;
$dep = $deposit_policy;
$deposit_due = round((float)$s['grand_total'] * ((float)$dep['deposit_percent'] / 100), 2);
$balance_due = round((float)$s['grand_total'] - $deposit_due, 2);
$is_converted = ($s['sales_status'] === 'Converted' || !empty($s['converted_sales_id']));
$needs_reaccept = !empty($s['reaccept_required']);
$accepted = $s['accepted_revision'] !== null && (int)$s['accepted_revision'] === (int)$s['revision_no'];
$currency = $CI->currency();
$pct = rtrim(rtrim(number_format((float)$dep['deposit_percent'], 2, '.', ''), '0'), '.');
$store = $CI->db->where('id', $store_id)->get('db_store')->row();
$tax = $tax_summary;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Quotation <?= htmlspecialchars($s['quotation_code']) ?></title>
<style>
@page { margin: 26px 28px 46px 28px; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9.5pt; color: #1f2937; margin: 0; }
.head { width: 100%; border-bottom: 1.5pt solid #0e7490; padding-bottom: 8px; }
.head td { vertical-align: top; }
.logo { width: 66px; }
.store-name { font-size: 13pt; font-weight: bold; color: #0f172a; margin: 0 0 3px; }
.store-meta { font-size: 8pt; color: #64748b; line-height: 1.5; }
.meta { text-align: right; font-size: 8.5pt; color: #334155; }
.meta .qcode { font-size: 12.5pt; font-weight: bold; color: #0e7490; }
.meta .lbl { color: #94a3b8; font-size: 7.5pt; text-transform: uppercase; letter-spacing: .4pt; }
.banner { margin: 8px 0; padding: 6px 9px; font-size: 8.5pt; }
.banner.ok { background: #ecfdf5; border: 0.5pt solid #a7f3d0; color: #065f46; }
.banner.warn { background: #fffbeb; border: 0.5pt solid #fde68a; color: #92400e; }
.banner.info { background: #eff6ff; border: 0.5pt solid #bfdbfe; color: #1e40af; }
.party { margin: 10px 0 4px; }
.party .lbl { font-size: 7.5pt; text-transform: uppercase; letter-spacing: .4pt; color: #94a3b8; }
.party .nm { font-weight: bold; font-size: 10pt; color: #0f172a; }
.party .det { font-size: 8pt; color: #64748b; }
table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
table.items th { background: #f1f5f9; border: 0.5pt solid #e2e8f0; padding: 5px 6px; font-size: 7.5pt; text-transform: uppercase; letter-spacing: .3pt; color: #475569; text-align: left; }
table.items td { border: 0.5pt solid #e2e8f0; padding: 5px 6px; font-size: 8.5pt; vertical-align: top; }
table.items td.num, table.items td.qty { text-align: right; }
.spec { display: block; margin-top: 2px; font-size: 7.5pt; color: #0e7490; }
.sum { width: 232px; margin-left: auto; border-collapse: collapse; margin-top: 8px; }
.sum td { padding: 3.5px 6px; font-size: 8.5pt; border-bottom: 0.5pt solid #f1f5f9; }
.sum td.v { text-align: right; font-weight: bold; }
.sum tr.grand td { border-top: 1.5pt solid #0e7490; border-bottom: none; font-size: 10.5pt; font-weight: bold; color: #0e7490; }
.sum tr.dep td { color: #065f46; font-weight: bold; }
.sum tr.bal td { color: #92400e; font-weight: bold; }
.box { margin-top: 9px; border: 0.5pt solid #e2e8f0; padding: 7px 9px; background: #f8fafc; font-size: 8.5pt; }
.box h4 { margin: 0 0 4px; font-size: 8pt; text-transform: uppercase; letter-spacing: .4pt; color: #0e7490; }
.box ul { margin: 0; padding-left: 13px; }
.box li { margin-bottom: 2px; }
.sign { width: 100%; margin-top: 18px; }
.sign td { border-top: 0.5pt solid #94a3b8; padding-top: 4px; font-size: 8pt; color: #64748b; width: 33%; }
.foot { margin-top: 12px; padding-top: 6px; border-top: 0.5pt solid #e2e8f0; font-size: 7.5pt; color: #94a3b8; }
</style>
</head>
<body>

<table class="head">
  <tr>
    <td style="width:70%;">
      <table><tr>
        <?php if (!empty($logo_data)): ?><td style="width:74px;"><img src="<?= $logo_data ?>" class="logo" alt="logo"></td><?php endif; ?>
        <td>
          <div class="store-name"><?= htmlspecialchars($store->store_name ?? ($SITE_TITLE ?? 'MartPoint')) ?></div>
          <div class="store-meta">
            <?php if (!empty($store->address)): ?><?= htmlspecialchars($store->address) ?><?php endif; ?>
            <?php if (!empty($store->city)): ?>, <?= htmlspecialchars($store->city) ?><?php endif; ?>
            <?php if (!empty($store->postcode)): ?> - <?= htmlspecialchars($store->postcode) ?><?php endif; ?><br>
            <?php if (!empty($store->mobile)): ?>Phone: <?= htmlspecialchars($store->mobile) ?> <?php endif; ?>
            <?php if (!empty($store->email)): ?><br>Email: <?= htmlspecialchars($store->email) ?><?php endif; ?>
            <?php if (!empty($store->vat_no ?? null)): ?><br>VAT: <?= htmlspecialchars($store->vat_no) ?><?php endif; ?>
          </div>
        </td>
      </tr></table>
    </td>
    <td class="meta">
      <div class="lbl">Quotation</div>
      <div class="qcode"><?= htmlspecialchars($s['quotation_code']) ?><?php if ((int)$s['revision_no'] > 0): ?> R<?= (int)$s['revision_no'] ?><?php endif; ?></div>
      <div style="margin-top:5px;"><span class="lbl">Job</span><br><?= htmlspecialchars($job->job_code) ?></div>
      <div style="margin-top:5px;"><span class="lbl">Date</span><br><?= show_date($s['quotation_date']) ?></div>
      <?php if (!empty($s['expire_date'])): ?>
      <div style="margin-top:5px;"><span class="lbl">Valid until</span><br><?= show_date($s['expire_date']) ?></div>
      <?php endif; ?>
    </td>
  </tr>
</table>

<?php if ($is_converted): ?>
<div class="banner info">This quotation has been converted to a sales invoice. It is retained for your records.</div>
<?php elseif ($needs_reaccept): ?>
<div class="banner warn">This quotation was revised (now R<?= (int)$s['revision_no'] ?>). <?= htmlspecialchars($s['reaccept_reason'] ?: 'Customer reacceptance is required before production.') ?></div>
<?php elseif ($accepted): ?>
<div class="banner ok">Revision R<?= (int)$s['revision_no'] ?> accepted by the customer.</div>
<?php endif; ?>

<div class="party">
  <div class="lbl">Prepared for</div>
  <div class="nm"><?= htmlspecialchars($customer_name) ?></div>
  <div class="det">
    <?php if (!empty($customer_phone)): ?><?= htmlspecialchars($customer_phone) ?><?php endif; ?>
    <?php if (!empty($customer_email)): ?> &middot; <?= htmlspecialchars($customer_email) ?><?php endif; ?>
    <?php if (!empty($customer_address)): ?><br><?= htmlspecialchars($customer_address) ?><?php endif; ?>
    <?php if (!empty($customer_tax)): ?><br>Tax ID: <?= htmlspecialchars($customer_tax) ?><?php endif; ?>
  </div>
</div>

<table class="items">
  <thead>
    <tr>
      <th style="width:22px;">#</th>
      <th>Description &amp; agreed specification</th>
      <th class="qty" style="width:52px;">Qty</th>
      <th class="num" style="width:78px;">Unit price</th>
      <th class="num" style="width:84px;">Amount</th>
    </tr>
  </thead>
  <tbody>
  <?php $i = 1; foreach ($items as $it): ?>
    <?php
      $parts = explode("\n", (string)$it->description, 2);
      $title = trim($parts[0]);
      $spec  = isset($parts[1]) ? trim($parts[1]) : '';
    ?>
    <tr>
      <td><?= $i++ ?></td>
      <td><?= htmlspecialchars($title) ?><?php if ($spec !== ''): ?><span class="spec"><?= htmlspecialchars($spec) ?></span><?php endif; ?></td>
      <td class="qty"><?= format_qty($it->quotation_qty) ?></td>
      <td class="num"><?= $currency . number_format((float)$it->unit_total_cost, 2) ?></td>
      <td class="num"><?= $currency . number_format((float)$it->total_cost, 2) ?></td>
    </tr>
  <?php endforeach; ?>
  <?php if ($i === 1): ?><tr><td colspan="5" style="text-align:center;">No lines on this quotation.</td></tr><?php endif; ?>
  </tbody>
</table>

<table style="width:100%;"><tr>
  <td style="vertical-align:top;">
    <?php if (!empty($terms)): ?>
    <div class="box" style="margin-top:8px;">
      <h4>Terms &amp; conditions</h4>
      <?= nl2br(htmlspecialchars($terms)) ?>
    </div>
    <?php endif; ?>
  </td>
  <td style="vertical-align:top;">
    <table class="sum">
      <tr><td>Subtotal</td><td class="v"><?= $currency . number_format((float)$s['subtotal'], 2) ?></td></tr>
      <?php if ((float)$s['other_charges'] != 0): ?>
      <tr><td>Other charges</td><td class="v"><?= $currency . number_format((float)$s['other_charges'], 2) ?></td></tr>
      <?php endif; ?>
      <?php if ((float)$s['discount'] != 0): ?>
      <tr>
        <td>Discount <?= ($s['discount_type'] === 'in_percentage') ? '(' . rtrim(rtrim(number_format((float)$s['discount_input'], 2, '.', ''), '0'), '.') . '%)' : '[Fixed]' ?></td>
        <td class="v">- <?= $currency . number_format((float)$s['discount'], 2) ?></td>
      </tr>
      <?php endif; ?>
      <?php if ($tax && !empty($tax['on'])): ?>
      <tr><td><?= htmlspecialchars($tax['label']) ?></td><td class="v"><?= $currency . number_format((float)$tax['amount'], 2) ?></td></tr>
      <?php else: ?>
      <tr><td>Tax</td><td class="v">Exempt</td></tr>
      <?php endif; ?>
      <tr class="grand"><td>Total</td><td class="v"><?= $currency . number_format((float)$s['grand_total'], 2) ?></td></tr>
      <tr class="dep"><td>Deposit to start (<?= $pct ?>%)</td><td class="v"><?= $currency . number_format($deposit_due, 2) ?></td></tr>
      <tr class="bal"><td>Balance on collection / delivery</td><td class="v"><?= $currency . number_format($balance_due, 2) ?></td></tr>
    </table>
  </td>
</tr></table>

<div class="box">
  <h4>Amount in words</h4>
  <?= no_to_words((float)$s['grand_total']) ?> Only
</div>

<?php if (!empty($s['note'])): ?>
<div class="box">
  <h4>Job / specification notes</h4>
  <?= nl2br(htmlspecialchars($s['note'])) ?>
</div>
<?php endif; ?>

<div class="box">
  <h4>Deposit &amp; payment terms</h4>
  <ul>
    <li>A deposit of <?= $currency . number_format($deposit_due, 2) ?> (<?= $pct ?>%) is required before production starts.</li>
    <li>The balance of <?= $currency . number_format($balance_due, 2) ?> is payable on collection or delivery.</li>
    <li>Production begins only after the deposit is verified.</li>
  </ul>
</div>

<div class="box">
  <h4>Turnaround &amp; production conditions</h4>
  <ul>
    <li>Work starts after: deposit verified, artwork approved, and print authorization granted.</li>
    <li><?= $job->due_date ? 'Target completion date: ' . show_date($job->due_date) . '.' : 'Completion date to be confirmed once production starts.' ?></li>
    <li>Timelines are counted from the date the final artwork is approved &mdash; not from order date.</li>
    <li>Quantities delivered may vary by up to 5% on printed runs; the balance is adjusted accordingly.</li>
    <li>Colour reproduction may vary slightly between print runs and materials.</li>
    <?php if (!empty($job->customer_supplied_material)): ?>
    <li>Material supplied by the customer is used as received; the customer confirms it is fit for the job.</li>
    <?php endif; ?>
  </ul>
</div>

<div class="box">
  <h4>How to accept this quotation</h4>
  <ul>
    <li>Confirm acceptance of revision R<?= (int)$s['revision_no'] ?> in writing or by signing below.</li>
    <li>Pay the deposit of <?= $currency . number_format($deposit_due, 2) ?> to the account on your invoice.</li>
    <li>Quotation reference to quote when paying: <?= htmlspecialchars($s['quotation_code']) ?>.</li>
    <li>Any change to specification, quantity or delivery date will be re-quoted and needs fresh acceptance.</li>
  </ul>
</div>

<table class="sign">
  <tr>
    <td>Customer name &amp; signature</td>
    <td>Date</td>
    <td>For <?= htmlspecialchars($store->store_name ?? ($SITE_TITLE ?? 'MartPoint')) ?></td>
  </tr>
</table>

<div class="foot">
  Quotation <?= htmlspecialchars($s['quotation_code']) ?><?php if ((int)$s['revision_no'] > 0): ?> &middot; Revision R<?= (int)$s['revision_no'] ?><?php endif; ?>
  &nbsp;|&nbsp; This is a computer-generated quotation.
</div>

</body>
</html>
