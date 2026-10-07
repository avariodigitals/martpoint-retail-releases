<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
/**
 * Printing quotation — customer-facing document.
 *
 * Reads the AUTHORITATIVE records (db_quotation + db_quotationitems) and
 * renders them with the same shared helpers as the standard quotation
 * (store logo, receipt settings, amount in words).
 *
 * Shows: agreed specifications, explicit charges, deposit/balance terms,
 * turnaround conditions and acceptance instructions.
 * Hides: internal costing, margins and referral information.
 */
$CI =& get_instance();
$store_id = get_current_store_id();

$store_logo_path = mp_get_store_theme_setting($store_id, 'store_logo');
$store_logo = !empty($store_logo_path) ? $store_logo_path : store_demo_logo();
$logo_data = mp_store_logo_round_base64($store_logo, 96);
$invoice_terms = mp_get_store_receipt_setting($store_id, 'invoice_terms', '');

$s = $summary;
$dep = $deposit_policy;
$deposit_due = round((float)$s['grand_total'] * ((float)$dep['deposit_percent'] / 100), 2);
$balance_due = round((float)$s['grand_total'] - $deposit_due, 2);
$is_converted = ($s['sales_status'] === 'Converted' || !empty($s['converted_sales_id']));
$needs_reaccept = !empty($s['reaccept_required']);
$accepted = $s['accepted_revision'] !== null && (int)$s['accepted_revision'] === (int)$s['revision_no'];
$currency = $CI->currency();
?>
<style>
.pq-wrap { max-width: 860px; margin: 0 auto 30px; background: #fff; padding: 28px 32px 40px; border: 1px solid #e5e7eb; border-radius: 8px; }
.pq-head { display: flex; justify-content: space-between; gap: 20px; border-bottom: 2px solid #0e7490; padding-bottom: 14px; }
.pq-logo { width: 96px; height: 96px; border-radius: 50%; object-fit: cover; }
.pq-store h3 { margin: 0 0 4px; color: #0f172a; font-size: 17px; font-weight: 700; }
.pq-store div { color: #64748b; font-size: 12px; line-height: 1.6; }
.pq-meta { text-align: right; font-size: 12px; color: #334155; }
.pq-meta .code { font-size: 16px; font-weight: 700; color: #0e7490; }
.pq-meta .rev { display: inline-block; background: #0e7490; color: #fff; border-radius: 3px; padding: 1px 6px; font-size: 11px; margin-left: 4px; }
.pq-meta .lbl { color: #94a3b8; text-transform: uppercase; font-size: 10px; letter-spacing: .04em; }
.pq-banner { margin: 14px 0; padding: 9px 12px; border-radius: 5px; font-size: 12.5px; }
.pq-banner.ok { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
.pq-banner.warn { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
.pq-banner.info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
.pq-party { margin: 16px 0 6px; }
.pq-party .lbl { font-size: 10px; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; }
.pq-party .nm { font-weight: 700; color: #0f172a; font-size: 13.5px; }
.pq-party .det { color: #64748b; font-size: 12px; }
table.pq-items { width: 100%; border-collapse: collapse; margin-top: 12px; }
table.pq-items th { background: #f1f5f9; border: 1px solid #e2e8f0; padding: 8px 9px; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #475569; }
table.pq-items td { border: 1px solid #e2e8f0; padding: 8px 9px; font-size: 12.5px; color: #1f2937; vertical-align: top; }
table.pq-items .num, table.pq-items .qty { text-align: right; }
.pq-spec { display: block; margin-top: 3px; font-size: 11.5px; color: #0e7490; white-space: pre-wrap; }
.pq-totals { width: 100%; margin-top: 14px; }
.pq-totals td { vertical-align: top; }
table.pq-sum { width: 280px; margin-left: auto; border-collapse: collapse; }
table.pq-sum td { padding: 5px 8px; font-size: 12.5px; border-bottom: 1px solid #f1f5f9; }
table.pq-sum td.v { text-align: right; font-weight: 600; color: #0f172a; }
table.pq-sum tr.grand td { border-top: 2px solid #0e7490; border-bottom: none; font-size: 14px; font-weight: 700; color: #0e7490; padding-top: 8px; }
table.pq-sum tr.dep td { color: #065f46; font-weight: 600; }
table.pq-sum tr.bal td { color: #92400e; font-weight: 600; }
.pq-box { margin-top: 14px; border: 1px solid #e2e8f0; border-radius: 5px; padding: 11px 13px; background: #f8fafc; font-size: 12.5px; color: #334155; }
.pq-box h4 { margin: 0 0 6px; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: #0e7490; }
.pq-box ul { margin: 0; padding-left: 18px; }
.pq-box li { margin-bottom: 3px; }
.pq-sign { display: flex; gap: 30px; margin-top: 26px; }
.pq-sign div { flex: 1; border-top: 1px solid #94a3b8; padding-top: 5px; font-size: 11px; color: #64748b; }
.pq-foot { margin-top: 18px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 11px; color: #94a3b8; display: flex; justify-content: space-between; }
.pq-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; max-width: 860px; margin: 0 auto 12px; }
.pq-actions .btn { line-height: 1.4; }
@media print { .pq-actions, .mp-sidebar, .mp-header, .topbar { display: none !important; } .pq-wrap { border: none; padding: 0; } }
</style>

<div class="pq-actions">
  <a href="<?= base_url('printing/job/' . (int)$job->id) ?>" class="btn btn-default btn-sm">← Back to job</a>
  <button onclick="window.print()" class="btn btn-default btn-sm"><i class="fa fa-print"></i> Print</button>
  <a href="<?= base_url('printing/quote_pdf/' . (int)$job->id) ?>" class="btn btn-default btn-sm" target="_blank"><i class="fa fa-file-pdf-o"></i> PDF</a>
  <?php if ($is_converted && !empty($s['converted_sales_id'])): ?>
    <a href="<?= base_url('sales/invoice/' . (int)$s['converted_sales_id']) ?>" class="btn btn-primary btn-sm">View Sales Invoice</a>
  <?php elseif (!$needs_reaccept): ?>
    <a href="<?= base_url('printing/quote_convert/' . (int)$job->id) ?>" class="btn btn-primary btn-sm">Convert to Invoice</a>
  <?php endif; ?>
</div>

<div class="pq-wrap">

  <div class="pq-head">
    <div style="display:flex;gap:14px;align-items:flex-start;">
      <?php $r1 = $CI->db->where('id', $store_id)->get('db_store')->row(); ?>
      <?php if (!empty($logo_data)): ?><img src="<?= $logo_data ?>" class="pq-logo" alt="store logo"><?php endif; ?>
      <div class="pq-store">
        <h3><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint') ?></h3>
        <div>
          <?php if (!empty($r1)): ?>
            <?php if (!empty(trim((string)$r1->address))): ?><?= htmlspecialchars($r1->address) ?><?php endif; ?>
            <?php if (!empty($r1->city)): ?>, <?= htmlspecialchars($r1->city) ?><?php endif; ?><br>
            <?php if (!empty($r1->mobile)): ?>Phone: <?= htmlspecialchars($r1->mobile) ?><br><?php endif; ?>
            <?php if (!empty($r1->email)): ?>Email: <?= htmlspecialchars($r1->email) ?><?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="pq-meta">
      <div class="lbl">Quotation</div>
      <div class="code"><?= htmlspecialchars($s['quotation_code']) ?><?php if ((int)$s['revision_no'] > 0): ?><span class="rev">R<?= (int)$s['revision_no'] ?></span><?php endif; ?></div>
      <div style="margin-top:6px;"><span class="lbl">Job</span><br><?= htmlspecialchars($job->job_code) ?></div>
      <div style="margin-top:6px;"><span class="lbl">Date</span><br><?= show_date($s['quotation_date']) ?></div>
      <?php if (!empty($s['expire_date'])): ?>
      <div style="margin-top:6px;"><span class="lbl">Valid until</span><br><?= show_date($s['expire_date']) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($is_converted): ?>
    <div class="pq-banner info"><i class="fa fa-check-circle"></i> This quotation has been converted to a sales invoice. It is retained here for your records.</div>
  <?php elseif ($needs_reaccept): ?>
    <div class="pq-banner warn"><i class="fa fa-exclamation-triangle"></i> This quotation was revised (now R<?= (int)$s['revision_no'] ?>). <?= htmlspecialchars($s['reaccept_reason'] ?: 'Customer reacceptance is required before production.') ?></div>
  <?php elseif ($accepted): ?>
    <div class="pq-banner ok"><i class="fa fa-check"></i> Revision R<?= (int)$s['revision_no'] ?> accepted by the customer.</div>
  <?php endif; ?>

  <div class="pq-party">
    <div class="lbl">Prepared for</div>
    <div class="nm"><?= htmlspecialchars($customer_name ?? 'Customer') ?></div>
    <div class="det">
      <?php if (!empty($customer_phone)): ?><?= htmlspecialchars($customer_phone) ?><?php endif; ?>
      <?php if (!empty($customer_email)): ?> · <?= htmlspecialchars($customer_email) ?><?php endif; ?>
    </div>
  </div>

  <table class="pq-items">
    <thead>
      <tr>
        <th style="width:34px;">#</th>
        <th>Description &amp; agreed specification</th>
        <th class="qty" style="width:78px;">Qty</th>
        <th class="num" style="width:110px;">Unit price</th>
        <th class="num" style="width:120px;">Amount</th>
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
        <td>
          <?= htmlspecialchars($title) ?>
          <?php if ($spec !== ''): ?><span class="pq-spec"><?= htmlspecialchars($spec) ?></span><?php endif; ?>
        </td>
        <td class="qty"><?= format_qty($it->quotation_qty) ?></td>
        <td class="num"><?= $currency . number_format((float)$it->unit_total_cost, 2) ?></td>
        <td class="num"><?= $currency . number_format((float)$it->total_cost, 2) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if ($i === 1): ?>
      <tr><td colspan="5" style="text-align:center;color:#94a3b8;">No lines on this quotation.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>

  <table class="pq-totals">
    <tr>
      <td>
        <?php if (!empty($invoice_terms)): ?>
          <div class="pq-box" style="margin-top:0;">
            <h4>Terms &amp; conditions</h4>
            <?= nl2br(htmlspecialchars($invoice_terms)) ?>
          </div>
        <?php endif; ?>
      </td>
      <td>
        <table class="pq-sum">
          <tr><td>Subtotal</td><td class="v"><?= $currency . number_format((float)$s['subtotal'], 2) ?></td></tr>
          <?php if ((float)$s['other_charges'] != 0): ?>
          <tr><td>Other charges</td><td class="v"><?= $currency . number_format((float)$s['other_charges'], 2) ?></td></tr>
          <?php endif; ?>
          <?php if ((float)$s['discount'] != 0): ?>
          <tr>
            <td>Discount <?= ($s['discount_type'] === 'in_percentage') ? '(' . rtrim(rtrim(number_format((float)$s['discount_input'], 2, '.', ''), '0'), '.') . '%)' : '[Fixed]' ?></td>
            <td class="v">− <?= $currency . number_format((float)$s['discount'], 2) ?></td>
          </tr>
          <?php endif; ?>
          <?php if ((float)$s['round_off'] != 0): ?>
          <tr><td>Round off</td><td class="v"><?= $currency . number_format((float)$s['round_off'], 2) ?></td></tr>
          <?php endif; ?>
          <?php if (!empty($tax_summary) && !empty($tax_summary['on'])): ?>
          <tr><td><?= htmlspecialchars($tax_summary['label']) ?></td><td class="v"><?= $currency . number_format((float)$tax_summary['amount'], 2) ?></td></tr>
          <?php else: ?>
          <tr><td>Tax</td><td class="v">Exempt</td></tr>
          <?php endif; ?>
          <tr class="grand"><td>Total</td><td class="v"><?= $currency . number_format((float)$s['grand_total'], 2) ?></td></tr>
          <tr class="dep"><td>Deposit to start (<?= rtrim(rtrim(number_format((float)$dep['deposit_percent'], 2, '.', ''), '0'), '.') ?>%)</td><td class="v"><?= $currency . number_format($deposit_due, 2) ?></td></tr>
          <tr class="bal"><td>Balance on collection / delivery</td><td class="v"><?= $currency . number_format($balance_due, 2) ?></td></tr>
        </table>
      </td>
    </tr>
  </table>

  <div class="pq-box">
    <h4>Amount in words</h4>
    <?= no_to_words((float)$s['grand_total']) ?> Only
  </div>

  <?php if (!empty($s['note'])): ?>
  <div class="pq-box">
    <h4>Job / specification notes</h4>
    <?= nl2br(htmlspecialchars($s['note'])) ?>
  </div>
  <?php endif; ?>

  <div class="pq-box">
    <h4>Deposit &amp; payment terms</h4>
    <ul>
      <li>A deposit of <?= $currency . number_format($deposit_due, 2) ?> (<?= rtrim(rtrim(number_format((float)$dep['deposit_percent'], 2, '.', ''), '0'), '.') ?>%) is required before production starts.</li>
      <li>The balance of <?= $currency . number_format($balance_due, 2) ?> is payable on collection or delivery.</li>
      <li>Production begins only after the deposit is verified.</li>
    </ul>
  </div>

  <div class="pq-box">
    <h4>Turnaround &amp; production conditions</h4>
    <ul>
      <li>Work starts after: deposit verified, artwork approved, and print authorization granted.</li>
      <li><?= $job->due_date ? 'Target completion date: <strong>' . show_date($job->due_date) . '</strong>.' : 'Completion date to be confirmed once production starts.' ?></li>
      <li>Timelines are counted from the date the final artwork is approved — not from order date.</li>
      <li>Quantities delivered may vary by up to 5% on printed runs; the balance is adjusted accordingly.</li>
      <li>Colour reproduction may vary slightly between print runs and materials.</li>
      <?php if (!empty($job->customer_supplied_material)): ?>
      <li>Material supplied by the customer is used as received; the customer confirms it is fit for the job.</li>
      <?php endif; ?>
    </ul>
  </div>

  <div class="pq-box">
    <h4>How to accept this quotation</h4>
    <ul>
      <li>Confirm acceptance of <strong>revision R<?= (int)$s['revision_no'] ?></strong> in writing or by signing below.</li>
      <li>Pay the deposit of <?= $currency . number_format($deposit_due, 2) ?> to the account on your invoice.</li>
      <li>Quotation reference to quote when paying: <strong><?= htmlspecialchars($s['quotation_code']) ?></strong>.</li>
      <li>Any change to specification, quantity or delivery date will be re-quoted and needs fresh acceptance.</li>
    </ul>
  </div>

  <div class="pq-sign">
    <div>Customer name &amp; signature</div>
    <div>Date</div>
    <div>For <?= htmlspecialchars($SITE_TITLE ?? 'MartPoint') ?></div>
  </div>

  <div class="pq-foot">
    <span>Quotation <?= htmlspecialchars($s['quotation_code']) ?><?php if ((int)$s['revision_no'] > 0): ?> · Revision R<?= (int)$s['revision_no'] ?><?php endif; ?></span>
    <span>This is a computer-generated quotation.</span>
  </div>
</div>


