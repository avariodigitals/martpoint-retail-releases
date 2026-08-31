<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($page_title ?? 'Purchase Invoice'); ?> - A4</title>
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= htmlspecialchars($og_site_name ?? 'MartPoint'); ?>">
<meta property="og:title" content="<?= htmlspecialchars($og_title ?? ($page_title ?? 'Purchase Invoice')); ?>">
<meta property="og:description" content="<?= htmlspecialchars($og_description ?? ''); ?>">
<meta property="og:url" content="<?= $og_url ?? current_url(); ?>">
<meta property="og:image" content="<?= $og_image ?? base_url('uploads/site/icon.webp'); ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($og_title ?? ($page_title ?? 'Purchase Invoice')); ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($og_description ?? ''); ?>">
<meta name="twitter:image" content="<?= $og_image ?? base_url('uploads/site/icon.webp'); ?>">
<style>
@page { size: A4; margin: 14mm 14mm 16mm 14mm; }
body { font-family: "DejaVu Sans", sans-serif; font-size: 10pt; color: #1f2937; margin: 0; padding: 0; }

.header { width: 100%; margin-bottom: 18px; }
.header td { vertical-align: middle; padding: 0; }
.header .logo { width: 120px; }
.header .info { padding-left: 12px; }
.header .meta { text-align: right; vertical-align: top; }

.info-table, .meta-table, .party-table, .note-table, .footer-table { width: 100%; border-collapse: collapse; }
.info-table td, .meta-table td, .party-table td { padding: 1px 0; }

.store-name { font-size: 14pt; font-weight: bold; color: #111827; }
.store-meta { font-size: 9pt; color: #4b5563; }
.meta-label { font-size: 8pt; color: #6b7280; text-transform: uppercase; }
.meta-value { font-size: 14pt; font-weight: bold; color: #111827; }
.meta-sm { font-size: 10pt; color: #1f2937; }

.parties { width: 100%; margin-bottom: 18px; }
.parties td { width: 50%; vertical-align: top; padding: 10px; border: 0.5pt solid #e5e7eb; }
.party-title { font-size: 8pt; color: #6b7280; text-transform: uppercase; }
.party-name { font-size: 11pt; font-weight: bold; color: #111827; }
.party-detail { font-size: 9pt; color: #374151; line-height: 1.4; }

.items { width: 100%; border: 0.5pt solid #d1d5db; }
.items th { background: #f3f4f6; color: #374151; font-size: 9pt; font-weight: bold; padding: 8px 6px; border-bottom: 0.5pt solid #d1d5db; text-align: left; }
.items td { padding: 8px 6px; border-bottom: 0.5pt solid #e5e7eb; font-size: 9.5pt; vertical-align: top; }
.items .num { text-align: right; }
.items .qty { text-align: center; }
.items .sl { width: 5%; text-align: center; }

.totals { width: 100%; margin-top: 12px; }
.totals td { padding: 4px; }
.totals-inner { width: auto; border-collapse: collapse; }
.totals-inner td { padding: 5px 4px; font-size: 9.5pt; border-bottom: 0.5pt solid #e5e7eb; }
.totals-inner .label { text-align: right; color: #374151; white-space: nowrap; }
.totals-inner .value { text-align: right; font-weight: bold; width: 120px; }
.totals-inner .grand td { font-size: 12pt; color: #111827; border-top: 1pt solid #111827; border-bottom: none; padding-top: 8px; }

.note-table { margin-top: 14px; }
.note-table td { font-size: 9pt; color: #374151; padding: 10px; background: #f9fafb; border: 0.5pt solid #e5e7eb; }
.footer-table { margin-top: 20px; }
.footer-table td { text-align: center; font-size: 8pt; color: #6b7280; padding-top: 10px; border-top: 0.5pt solid #e5e7eb; }
</style>
</head>
<body <?php if(!empty($auto_print)): ?>onload="window.print();"<?php endif; ?> <?php if(!empty($download_url)): ?>style="width:100%; max-width:210mm; margin:0 auto; padding:14mm 14mm 80px 14mm; box-sizing:border-box; background:#fff; box-shadow:0 0 12px rgba(0,0,0,0.08);"<?php endif; ?>>

<?php
$CI =& get_instance();

$q1 = $this->db->query("SELECT * FROM db_store WHERE status=1 AND id=".get_current_store_id());
$res1 = $q1->row();
$store_name = $res1->store_name;
$company_mobile = $res1->mobile;
$company_phone = $res1->phone;
$company_email = $res1->email;
$company_city = $res1->city;
$company_address = $res1->address;
$company_postcode = $res1->postcode;
$company_gst_no = $res1->gst_no;
$company_vat_no = $res1->vat_no;
$company_pan_no = $res1->pan_no;
$store_logo_path = mp_get_store_theme_setting(get_current_store_id(), 'store_logo');
$store_logo = !empty($store_logo_path) ? $store_logo_path : store_demo_logo();
$store_website = $res1->store_website;

$q3 = $this->db->query("SELECT a.supplier_name, a.mobile, a.phone, a.gstin, a.tax_number, a.email,
                           a.opening_balance, a.country_id, a.state_id, a.created_by,
                           a.postcode, a.address, b.purchase_date, b.created_time, b.reference_no,
                           b.purchase_code, b.purchase_note, b.purchase_status,
                           coalesce(b.grand_total,0) as grand_total,
                           coalesce(b.subtotal,0) as subtotal,
                           coalesce(b.paid_amount,0) as paid_amount,
                           coalesce(b.other_charges_input,0) as other_charges_input,
                           other_charges_tax_id,
                           coalesce(b.other_charges_amt,0) as other_charges_amt,
                           discount_to_all_input,
                           b.discount_to_all_type,
                           coalesce(b.tot_discount_to_all_amt,0) as tot_discount_to_all_amt,
                           coalesce(b.round_off,0) as round_off,
                           b.payment_status
                           FROM db_suppliers a, db_purchase b
                           WHERE a.id=b.supplier_id AND b.id='$purchase_id'");
$res3 = $q3->row();

$supplier_name = $res3->supplier_name;
$supplier_mobile = $res3->mobile;
$supplier_phone = $res3->phone;
$supplier_email = $res3->email;
$supplier_country = get_country($res3->country_id);
$supplier_state = get_state($res3->state_id);
$supplier_address = $res3->address;
$supplier_postcode = $res3->postcode;
$supplier_gst_no = $res3->gstin;
$supplier_tax_number = $res3->tax_number;
$purchase_date = $res3->purchase_date;
$created_time = $res3->created_time;
$reference_no = $res3->reference_no;
$purchase_code = $res3->purchase_code;
$purchase_note = $res3->purchase_note;
$purchase_status = $res3->purchase_status;
$created_by = $res3->created_by;

$subtotal = $res3->subtotal;
$grand_total = $res3->grand_total;
$other_charges_input = $res3->other_charges_input;
$other_charges_tax_id = $res3->other_charges_tax_id;
$other_charges_amt = $res3->other_charges_amt;
$paid_amount = $res3->paid_amount;
$discount_to_all_input = $res3->discount_to_all_input;
$discount_to_all_type = $res3->discount_to_all_type;
$discount_to_all_type = ($discount_to_all_type == 'in_percentage') ? '%' : 'Fixed';
$tot_discount_to_all_amt = $res3->tot_discount_to_all_amt;
$round_off = $res3->round_off;
$payment_status = $res3->payment_status;

$q_payments = $this->db->query("SELECT * FROM db_purchasepayments WHERE purchase_id='$purchase_id' ORDER BY id");
$first_payment = $q_payments->row();

// Map payment-mode codes (and legacy names) to human-readable names
$pm_map = [];
foreach($this->db->where('store_id', get_current_store_id())->where('status', 1)->get('db_payment_modes')->result() as $pm){
    $pm_map[strtolower($pm->code)] = $pm->name;
    $pm_map[strtolower($pm->name)] = $pm->name;
}
$payment_method = ($q_payments->num_rows() > 0) ? ($pm_map[strtolower($first_payment->payment_type)] ?? $first_payment->payment_type) : 'Unpaid';
$payment_note_display = '';
if($q_payments->num_rows() > 0 && !empty($first_payment->payment_note)){
    $pn = $first_payment->payment_note;
    $payment_note_display = (stripos($pn, 'via') !== false) ? 'Created ' . strstr($pn, 'via') : $pn;
}
$paid_amount = 0;
foreach($q_payments->result() as $p){ $paid_amount += $p->payment; }

$logo_data = mp_store_logo_round_base64($store_logo, 120);
?>

<table class="header" width="100%">
  <tr>
    <td class="logo" width="100">
      <?php if(!empty($logo_data)): ?>
        <img src="<?= $logo_data; ?>" width="100" height="100" alt="store logo">
      <?php endif; ?>
    </td>
    <td class="info">
      <table class="info-table">
        <tr><td class="store-name"><?= $store_name; ?></td></tr>
        <tr><td class="store-meta">
          <?php if(!empty(trim($company_address))): ?><?= $company_address; ?>, <?php endif; ?>
          <?php if(!empty($company_city)): ?><?= $company_city; ?><?php endif; ?>
          <?php if(!empty(trim($company_postcode))): ?> - <?= $company_postcode; ?><?php endif; ?>
        </td></tr>
        <tr><td class="store-meta">
          <?php if(!empty(trim($company_mobile))): ?>Phone: <?= $company_mobile; ?> <?php endif; ?>
        </td></tr>
        <?php if(!empty($company_email) || !empty($store_website)): ?>
        <tr><td class="store-meta">
          <?php if(!empty($company_email)): ?>Email: <?= $company_email; ?><?php endif; ?>
          <?php if(!empty($company_email) && !empty($store_website)): ?> | <?php endif; ?>
          <?php if(!empty($store_website)): ?><?= $store_website; ?><?php endif; ?>
        </td></tr>
        <?php endif; ?>
      </table>
    </td>
    <td class="meta" width="140">
      <table class="meta-table">
        <tr><td class="meta-label"><?= $this->lang->line('purchase_invoice'); ?></td></tr>
        <tr><td class="meta-value"><?= $purchase_code; ?></td></tr>
        <tr><td class="meta-label" style="padding-top: 8px;"><?= $this->lang->line('date'); ?></td></tr>
        <tr><td class="meta-sm"><?= show_date($purchase_date); ?></td></tr>
      </table>
    </td>
  </tr>
</table>

<table class="parties">
  <tr>
    <td>
      <div class="party-name" style="font-size:11pt; font-weight:bold; color:#111827;"><?= $supplier_name; ?></div>
      <div class="party-detail" style="font-size:8pt; color:#6b7280; line-height:1.4; margin-top:2px;">
        <?php if(!empty($supplier_mobile)): ?><?= $this->lang->line('mobile'); ?>: <?= $supplier_mobile; ?><?php endif; ?>
        <?php if(!empty($supplier_mobile) && !empty($supplier_email)): ?> | <?php endif; ?>
        <?php if(!empty($supplier_email)): ?>Email: <?= $supplier_email; ?><?php endif; ?>
        <?php if(!empty($supplier_gst_no) && gst_number()): ?> | <?= $this->lang->line('gst_number'); ?>: <?= $supplier_gst_no; ?><?php endif; ?>
      </div>
    </td>
    <td style="text-align: right;">
      <div class="party-title" style="font-size:8pt; color:#6b7280; text-transform:uppercase;"><?= $this->lang->line('payment_status'); ?></div>
      <div class="party-name" style="font-size:11pt; font-weight:bold; color:#111827; margin-top:2px;"><?= $payment_status; ?> : <?= $payment_method; ?></div>
      <?php if(!empty($payment_note_display)): ?>
      <div class="party-detail" style="font-size:8pt; color:#6b7280; margin-top:2px;">Note: <?= $payment_note_display; ?></div>
      <?php endif; ?>
    </td>
  </tr>
</table>

<table class="items">
  <thead>
    <tr>
      <th class="sl">#</th>
      <th><?= $this->lang->line('description'); ?></th>
      <th class="qty"><?= $this->lang->line('qty'); ?></th>
      <th class="num"><?= $this->lang->line('unit_cost'); ?></th>
      <th class="num"><?= $this->lang->line('amount'); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php
    $i = 1;
    $tot_qty = 0;
    $tot_tax_amt = 0;
    $tot_discount_amt = 0;
    $tot_total_cost = 0;

    $this->db->select("a.description, c.item_name, a.purchase_qty, a.tax_type,
                       a.price_per_unit, b.tax, b.tax_name, a.tax_amt,
                       a.discount_input, a.discount_amt, a.unit_total_cost,
                       a.total_cost, d.unit_name, c.sku, c.hsn");
    $this->db->where("a.purchase_id", $purchase_id);
    $this->db->from("db_purchaseitems a");
    $this->db->join("db_tax b", "b.id=a.tax_id", "left");
    $this->db->join("db_items c", "c.id=a.item_id", "left");
    $this->db->join("db_units d", "d.id=c.unit_id", "left");
    $q2 = $this->db->get();

    foreach($q2->result() as $res2){
        $discount = (empty($res2->discount_input) || $res2->discount_input == 0) ? '0' : $res2->discount_input . '%';
    ?>
    <tr>
      <td class="sl"><?= $i++; ?></td>
      <td>
        <?= $res2->item_name; ?>
        <?php if(!empty($res2->description)): ?><br><small style="font-size: 8pt; color: #6b7280;">[<?= nl2br($res2->description); ?>]</small><?php endif; ?>
      </td>
      <td class="qty"><?= format_qty($res2->purchase_qty); ?></td>
      <td class="num"><?= $CI->currency($res2->unit_total_cost); ?></td>
      <td class="num"><?= $CI->currency($res2->total_cost); ?></td>
    </tr>
    <?php
        $tot_qty += $res2->purchase_qty;
        $tot_tax_amt += $res2->tax_amt;
        $tot_discount_amt += $res2->discount_amt;
        $tot_total_cost += $res2->total_cost;
    }
    ?>
  </tbody>
</table>

<table class="totals">
  <tr>
    <td width="100%">&nbsp;</td>
    <td align="right">
      <table class="totals-inner">
        <tr><td class="label"><?= $this->lang->line('subtotal'); ?></td><td class="value"><?= $CI->currency($subtotal); ?></td></tr>
        <tr><td class="label"><?= $this->lang->line('other_charges'); ?></td><td class="value"><?= $CI->currency($other_charges_amt); ?></td></tr>
        <?php if(!empty($tot_discount_to_all_amt) && $tot_discount_to_all_amt != 0): ?>
        <tr><td class="label"><?= $this->lang->line('discount'); ?> <?= ($discount_to_all_type == '%') ? $discount_to_all_input . '%' : '[Fixed]'; ?></td><td class="value"><?= $CI->currency($tot_discount_to_all_amt); ?></td></tr>
        <?php endif; ?>
        <tr><td class="label"><?= $this->lang->line('round_off'); ?></td><td class="value"><?= $CI->currency($round_off); ?></td></tr>
        <?php if(!empty($tot_tax_amt) && $tot_tax_amt != 0): ?>
        <tr><td class="label"><?= $this->lang->line('tax'); ?></td><td class="value"><?= $CI->currency($tot_tax_amt); ?></td></tr>
        <?php endif; ?>
        <tr class="grand"><td class="label"><?= $this->lang->line('grand_total'); ?></td><td class="value"><?= $CI->currency($grand_total); ?></td></tr>
        <tr><td class="label"><?= $this->lang->line('paid_amount'); ?></td><td class="value"><?= $CI->currency($paid_amount); ?></td></tr>
        <?php $balance = $grand_total - $paid_amount; if($balance > 0): ?>
        <tr><td class="label">Balance Due</td><td class="value"><?= $CI->currency($balance); ?></td></tr>
        <?php endif; ?>
      </table>
    </td>
  </tr>
</table>

<?php if(!empty($download_url) && $q_payments->num_rows() > 0): ?>
<table class="items" style="margin-top: 14px;">
  <thead>
    <tr>
      <th class="sl">#</th>
      <th><?= $this->lang->line('date'); ?></th>
      <th><?= $this->lang->line('payment_type'); ?></th>
      <th><?= $this->lang->line('account'); ?></th>
      <th><?= $this->lang->line('payment_note'); ?></th>
      <th class="num"><?= $this->lang->line('payment'); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php $pi=1; foreach($q_payments->result() as $p): ?>
    <tr>
      <td class="sl"><?= $pi++; ?></td>
      <td><?= show_date($p->payment_date); ?></td>
      <td>
        <?= $pm_map[strtolower($p->payment_type)] ?? $p->payment_type; ?>
        <?php if(!empty($p->cheque_number)): ?><br><small>Cheque no.: <?= $p->cheque_number; ?></small><?php endif; ?>
      </td>
      <td><?= get_account_name($p->account_id); ?></td>
      <td><?= $p->payment_note; ?></td>
      <td class="num"><?= $CI->currency($p->payment); ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<table class="note-table">
  <tr><td>
    <strong><?= $this->lang->line('amount_in_words'); ?>:</strong>
    <?= no_to_words($grand_total); ?> Only
  </td></tr>
</table>

<?php if(!empty(trim($purchase_note)) && trim($purchase_note) !== 'Created via MartPoint Assist'): ?>
<table class="note-table" style="margin-top: 10px;">
  <tr><td><strong><?= $this->lang->line('note'); ?>:</strong> <?= nl2br($purchase_note); ?></td></tr>
</table>
<?php endif; ?>

<table class="footer-table">
  <tr><td>
    <?= $this->lang->line('thank_you_for_your_business'); ?><br>
    <?= $this->lang->line('this_is_a_computer_generated_invoice'); ?>
  </td></tr>
</table>

<?php if(!empty($download_url)): ?>
<div style="position:fixed; bottom:0; left:0; right:0; text-align:center; padding:14px; background:#f3f4f6; border-top:1px solid #e5e7eb; font-family: system-ui, sans-serif; z-index:100;">
  <a href="<?= $download_url; ?>" style="display:inline-block; padding:12px 22px; background:#111827; color:#fff; border-radius:6px; text-decoration:none; font-weight:600;">Download PDF</a>
</div>
<?php endif; ?>

</body>
</html>
