<?php $this->load->view('finance/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<?php
$q3=$this->db->query("SELECT b.expire_date, b.sales_status, b.store_id,COALESCE(a.customer_name,'Walk-in Customer') as customer_name,a.mobile,a.phone,a.gstin,a.tax_number,a.email,
                           a.opening_balance,a.country_id,a.state_id,a.city,
                           a.postcode,a.address,b.quotation_date,b.created_time,b.reference_no,
                           b.quotation_code,b.quotation_status,b.quotation_note,
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
                           b.payment_status,b.pos
                           FROM db_quotation b
                           LEFT JOIN db_customers a ON a.`id`=b.`customer_id`
                           WHERE b.`id`='$quotation_id' AND b.store_id=".get_current_store_id());

$res3=$q3->row();
if($res3->store_id!=get_current_store_id()){
  $CI->show_access_denied_page();exit();
}
$customer_name=$res3->customer_name;
$customer_mobile=$res3->mobile;
$customer_phone=$res3->phone;
$customer_email=$res3->email;
$customer_country=get_country($res3->country_id);
$customer_state=get_state($res3->state_id);
$customer_city=$res3->city;
$customer_address=$res3->address;
$customer_postcode=$res3->postcode;
$customer_gst_no=$res3->gstin;
$customer_tax_number=$res3->tax_number;
$customer_opening_balance=$res3->opening_balance;
$quotation_date=$res3->quotation_date;
$expire_date=(!empty($res3->expire_date)) ? show_date($res3->expire_date) : '';
$created_time=$res3->created_time;
$reference_no=$res3->reference_no;
$quotation_code=$res3->quotation_code;
$quotation_status=$res3->quotation_status;
$quotation_note=$res3->quotation_note;
$sales_status=$res3->sales_status;

$subtotal=$res3->subtotal;
$grand_total=$res3->grand_total;
$other_charges_input=$res3->other_charges_input;
$other_charges_tax_id=$res3->other_charges_tax_id;
$other_charges_amt=$res3->other_charges_amt;
$paid_amount=$res3->paid_amount;
$discount_to_all_input=$res3->discount_to_all_input;
$discount_to_all_type=$res3->discount_to_all_type;
$discount_to_all_type = ($discount_to_all_type=='in_percentage') ? '%' : 'Fixed';
$tot_discount_to_all_amt=$res3->tot_discount_to_all_amt;
$round_off=$res3->round_off;
$payment_status=$res3->payment_status;
$pos=$res3->pos;

$q1=$this->db->query("select * from db_store where id=".$res3->store_id." ");
$res1=$q1->row();
$store_name=$res1->store_name;
$company_mobile=$res1->mobile;
$company_phone=$res1->phone;
$company_email=$res1->email;
$company_city=$res1->city;
$company_address=$res1->address;
$company_gst_no=$res1->gst_no;
$company_vat_no=$res1->vat_no;
$company_pan_no=$res1->pan_no;

$str2 = ($pos==1) ? 'pos/edit/' : 'quotation/update/';
?>

<style type="text/css">
.invoice-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
@media (max-width: 900px) { .invoice-grid { grid-template-columns: 1fr; } }
.invoice-block { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 12px; padding: 18px; }
.invoice-block h4 { font-size: 13px; font-weight: 700; color: var(--mp-muted); text-transform: uppercase; letter-spacing: .03em; margin: 0 0 12px; }
.invoice-block p, .invoice-block address { margin: 0; font-size: 13px; line-height: 1.6; color: var(--mp-text); font-style: normal; }
.invoice-block strong { color: var(--mp-ink); font-weight: 700; }
.invoice-meta { list-style: none; margin: 0; padding: 0; font-size: 13px; }
.invoice-meta li { margin-bottom: 6px; color: var(--mp-text); }
.invoice-meta li strong { color: var(--mp-ink); }
.mp-invoice-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; }
.mp-invoice-table th, .mp-invoice-table td { padding: 12px 14px; text-align: left; vertical-align: top; border-bottom: 1px solid var(--mp-border); }
.mp-invoice-table thead th { background: var(--mp-bg); color: var(--mp-muted); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; white-space: nowrap; }
.mp-invoice-table tbody tr:last-child td, .mp-invoice-table tfoot tr:last-child td { border-bottom: none; }
.mp-invoice-table td.num, .mp-invoice-table th.num { text-align: right; }
.mp-invoice-table tfoot td { font-weight: 700; color: var(--mp-ink); background: rgba(0,87,255,.03); }
.mp-invoice-table .item-desc { font-size: 11px; color: var(--mp-muted); margin-top: 2px; display: block; }
.totals-card { max-width: 420px; margin-left: auto; }
.totals-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--mp-border); font-size: 14px; }
.totals-row:last-child { border-bottom: none; }
.totals-row .label { color: var(--mp-muted); font-weight: 500; }
.totals-row .value { font-weight: 700; color: var(--mp-text); }
.totals-row.grand { padding: 16px 0; margin-top: 4px; border-top: 2px solid var(--mp-border); }
.totals-row.grand .label { font-size: 16px; font-weight: 700; color: var(--mp-text); }
.totals-row.grand .value { font-size: 22px; font-weight: 800; color: var(--mp-primary); }
.notes-card { max-width: 420px; }
.notes-card p { margin: 0; font-size: 13px; line-height: 1.6; color: var(--mp-text); }
@media print {
  .mp-page-head .mp-quick-actions, .mp-form-actions, .mp-sidebar, .mp-header, .mp-footer { display: none !important; }
  .mp-section { padding: 0 !important; }
  .mp-card-form, .invoice-block, .mp-card { box-shadow: none !important; border: 1px solid #ddd !important; }
}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= $this->lang->line('quotation_invoice'); ?> <span style="color:var(--mp-muted);font-weight:500;">#<?= htmlspecialchars($quotation_code); ?></span></h2>
    <div class="mp-page-sub"><?= show_date($quotation_date); ?> <?= $created_time; ?> &middot; <?= $this->lang->line('status'); ?>: <?= ucfirst($quotation_status); ?></div>
  </div>
  <div class="mp-quick-actions">
    <?php if($CI->permissions('quotation_edit')) { ?>
      <a href="<?= base_url($str2.$quotation_id); ?>" class="mp-qa-btn"><i class="fa fa-edit"></i> Edit</a>
    <?php } ?>
    <?php if($CI->permissions('sales_add') && $sales_status=='') { ?>
      <a href="<?= base_url('sales/quotation/'.$quotation_id); ?>" class="mp-qa-btn"><i class="fa fa-undo"></i> Convert to Invoice</a>
    <?php } else { ?>
      <a href="<?= base_url('sales/invoice/'.get_sales_id_of_quotation($quotation_id)); ?>" class="mp-qa-btn"><i class="fa fa-eye"></i> View Sales Invoice</a>
    <?php } ?>
    <a href="<?= base_url('quotation/print_invoice/'.$quotation_id); ?>" target="_blank" class="mp-qa-btn"><i class="fa fa-print"></i> Print</a>
    <a href="<?= base_url('quotation/pdf/'.$quotation_id); ?>" target="_blank" class="mp-qa-btn"><i class="fa fa-file-pdf-o"></i> PDF</a>
  </div>
</div>

<div class="mp-section">
  <div class="mp-card-form" id="printableArea">
    <div class="mp-card-body">
      <div class="invoice-grid">
        <div class="invoice-block">
          <h4><?= $this->lang->line('from'); ?></h4>
          <p>
            <strong><?= htmlspecialchars($store_name); ?></strong><br>
            <?= htmlspecialchars($company_address); ?><br>
            <?= $this->lang->line('city'); ?>: <?= htmlspecialchars($company_city); ?><br>
            <?= $this->lang->line('phone'); ?>: <?= htmlspecialchars($company_phone); ?> &middot; <?= $this->lang->line('mobile'); ?>: <?= htmlspecialchars($company_mobile); ?><br>
            <?php if(!empty(trim($company_email))) { ?><?= $this->lang->line('email'); ?>: <?= htmlspecialchars($company_email); ?><br><?php } ?>
            <?php if(!empty(trim($company_gst_no)) && gst_number()) { ?><?= $this->lang->line('gst_number'); ?>: <?= htmlspecialchars($company_gst_no); ?><br><?php } ?>
            <?php if(!empty(trim($company_vat_no)) && vat_number()) { ?><?= $this->lang->line('vat_number'); ?>: <?= htmlspecialchars($company_vat_no); ?><br><?php } ?>
            <?php if(!empty(trim($company_pan_no)) && pan_number()) { ?><?= $this->lang->line('pan_number'); ?>: <?= htmlspecialchars($company_pan_no); ?><br><?php } ?>
          </p>
        </div>
        <div class="invoice-block">
          <h4><?= $this->lang->line('customer_details'); ?></h4>
          <p>
            <strong><?= htmlspecialchars($customer_name); ?></strong><br>
            <?= htmlspecialchars($customer_address); ?>
            <?= htmlspecialchars($customer_country); ?>
            <?= (!empty($customer_state)) ? ', '.htmlspecialchars($customer_state) : ''; ?>
            <?= (!empty($customer_city)) ? ', '.htmlspecialchars($customer_city) : ''; ?>
            <?= (!empty($customer_postcode)) ? '-'.htmlspecialchars($customer_postcode) : ''; ?><br>
            <?php if(!empty(trim($customer_mobile))) { ?><?= $this->lang->line('mobile'); ?>: <?= htmlspecialchars($customer_mobile); ?><br><?php } ?>
            <?php if(!empty(trim($customer_phone))) { ?><?= $this->lang->line('phone'); ?>: <?= htmlspecialchars($customer_phone); ?><br><?php } ?>
            <?php if(!empty(trim($customer_email))) { ?><?= $this->lang->line('email'); ?>: <?= htmlspecialchars($customer_email); ?><br><?php } ?>
            <?php if(!empty(trim($customer_gst_no)) && gst_number()) { ?><?= $this->lang->line('gst_number'); ?>: <?= htmlspecialchars($customer_gst_no); ?><br><?php } ?>
            <?php if(!empty(trim($customer_tax_number))) { ?><?= $this->lang->line('tax_number'); ?>: <?= htmlspecialchars($customer_tax_number); ?><br><?php } ?>
          </p>
        </div>
      </div>

      <div class="invoice-grid">
        <div class="invoice-block">
          <h4><?= $this->lang->line('quotation_details'); ?></h4>
          <ul class="invoice-meta">
            <li><strong><?= $this->lang->line('reference_no'); ?>:</strong> <?= htmlspecialchars($reference_no); ?></li>
            <li><strong><?= $this->lang->line('expire_date'); ?>:</strong> <?= $expire_date; ?></li>
            <li><strong><?= $this->lang->line('payment_status'); ?>:</strong> <?= ucfirst($payment_status); ?></li>
            <?php if($sales_status=='Converted'){ ?>
            <li><strong><?= $this->lang->line('sales_status'); ?>:</strong> <?= $this->lang->line('converted'); ?> &mdash; <a href="<?= base_url('sales/invoice/'.get_sales_id_of_quotation($quotation_id)); ?>">View Sales Invoice</a></li>
            <?php } ?>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="mp-card-form">
    <div class="mp-card-head"><h3><i class="fa fa-list"></i> Items</h3></div>
    <div class="mp-card-body" style="padding:0;">
      <div class="mp-dt-scroll">
        <table class="table mp-invoice-table mp-dt-table" style="margin:0;">
          <thead>
            <tr>
              <th>#</th>
              <th><?= $this->lang->line('item_name'); ?></th>
              <th class="num"><?= $this->lang->line('unit_price'); ?></th>
              <th class="num"><?= $this->lang->line('quantity'); ?></th>
              <th class="num"><?= $this->lang->line('net_cost'); ?></th>
              <th class="num"><?= $this->lang->line('tax'); ?></th>
              <th class="num"><?= $this->lang->line('tax_amount'); ?></th>
              <th class="num"><?= $this->lang->line('discount'); ?></th>
              <th class="num"><?= $this->lang->line('discount_amount'); ?></th>
              <th class="num"><?= $this->lang->line('unit_cost'); ?></th>
              <th class="num"><?= $this->lang->line('total_amount'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i=0;
            $tot_qty=0;
            $tot_quotation_price=0;
            $tot_tax_amt=0;
            $tot_discount_amt=0;
            $tot_total_cost=0;

            $q2=$this->db->query("SELECT a.description,c.item_name, a.quotation_qty,a.tax_type,
                                  a.price_per_unit, b.tax,b.tax_name,a.tax_amt,
                                  a.discount_input,a.discount_amt, a.unit_total_cost,
                                  a.total_cost
                                  FROM
                                  db_quotationitems AS a,db_tax AS b,db_items AS c
                                  WHERE
                                  c.id=a.item_id AND b.id=a.tax_id AND a.quotation_id='$quotation_id'");
            foreach ($q2->result() as $res2) {
                $str = ($res2->tax_type=='Inclusive')? 'Inc.' : 'Exc.';
                $discount = (empty($res2->discount_input)||$res2->discount_input==0)? '0':$res2->discount_input."%";
                $discount_amt = (empty($res2->discount_amt)||$res2->discount_input==0)? '0':$res2->discount_amt."";
            ?>
            <tr>
              <td><?= ++$i; ?></td>
              <td>
                <?= htmlspecialchars($res2->item_name); ?>
                <?php if(!empty($res2->description)) { ?><span class="item-desc">[<?= nl2br(htmlspecialchars($res2->description)); ?>]</span><?php } ?>
              </td>
              <td class="num"><?= $CI->currency($res2->price_per_unit); ?></td>
              <td class="num"><?= format_qty($res2->quotation_qty); ?></td>
              <td class="num"><?= $CI->currency($res2->price_per_unit * $res2->quotation_qty); ?></td>
              <td class="num"><?= store_number_format($res2->tax); ?>%<br><?= htmlspecialchars($res2->tax_name); ?> [<?= $str; ?>]</td>
              <td class="num"><?= $CI->currency($res2->tax_amt); ?></td>
              <td class="num"><?= $discount; ?></td>
              <td class="num"><?= $CI->currency($discount_amt); ?></td>
              <td class="num"><?= $CI->currency($res2->unit_total_cost); ?></td>
              <td class="num"><?= $CI->currency($res2->total_cost); ?></td>
            </tr>
            <?php
                $tot_qty +=$res2->quotation_qty;
                $tot_quotation_price +=$res2->price_per_unit;
                $tot_tax_amt +=$res2->tax_amt;
                $tot_discount_amt +=$res2->discount_amt;
                $tot_total_cost +=$res2->total_cost;
            }
            ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2" class="text-center">Total</td>
              <td class="num"><?= $CI->currency($tot_quotation_price); ?></td>
              <td class="num"><?= format_qty($tot_qty); ?></td>
              <td class="num">-</td>
              <td class="num">-</td>
              <td class="num"><?= $CI->currency($tot_tax_amt); ?></td>
              <td class="num">-</td>
              <td class="num"><?= $CI->currency($tot_discount_amt); ?></td>
              <td class="num">-</td>
              <td class="num"><?= $CI->currency($tot_total_cost); ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  <div class="mp-form-grid" style="margin-top:20px;align-items:start;">
    <div class="mp-card-form notes-card">
      <div class="mp-card-head"><h3>Notes & Discount</h3></div>
      <div class="mp-card-body">
        <?php if($discount_to_all_input > 0 || $tot_discount_to_all_amt > 0){ ?>
          <div class="totals-row">
            <span class="label"><?= $this->lang->line('discount_on_all'); ?></span>
            <span class="value"><?= store_number_format($discount_to_all_input); ?> (<?= $discount_to_all_type ?>)</span>
          </div>
        <?php } ?>
        <div class="totals-row" style="border-bottom:none;">
          <span class="label"><?= $this->lang->line('note'); ?></span>
          <span class="value" style="text-align:right;"><?= nl2br(htmlspecialchars($quotation_note)); ?></span>
        </div>
      </div>
    </div>

    <div class="mp-card-form totals-card">
      <div class="mp-card-head"><h3>Totals</h3></div>
      <div class="mp-card-body">
        <div class="totals-row">
          <span class="label"><?= $this->lang->line('subtotal'); ?></span>
          <span class="value"><?= store_number_format($subtotal); ?></span>
        </div>
        <div class="totals-row">
          <span class="label"><?= $this->lang->line('other_charges'); ?></span>
          <span class="value"><?= store_number_format($other_charges_amt); ?></span>
        </div>
        <div class="totals-row">
          <span class="label"><?= $this->lang->line('discount_on_all'); ?></span>
          <span class="value"><?= store_number_format($tot_discount_to_all_amt); ?></span>
        </div>
        <div class="totals-row">
          <span class="label"><?= $this->lang->line('round_off'); ?></span>
          <span class="value"><?= store_number_format($round_off); ?></span>
        </div>
        <div class="totals-row grand">
          <span class="label"><?= $this->lang->line('grand_total'); ?></span>
          <span class="value"><?= store_number_format($grand_total); ?></span>
        </div>
      </div>
    </div>
  </div>

  <div class="mp-form-actions" style="justify-content:space-between;margin-top:24px;">
    <div>
      <a href="<?= base_url('quotation'); ?>" class="mp-btn-secondary"><i class="fa fa-arrow-left"></i> Back to List</a>
    </div>
    <div style="display:flex;gap:12px;">
      <?php if($CI->permissions('quotation_edit')) { ?>
        <a href="<?= base_url($str2.$quotation_id); ?>" class="mp-btn-secondary"><i class="fa fa-edit"></i> Edit</a>
      <?php } ?>
      <?php if($CI->permissions('sales_add') && $sales_status=='') { ?>
        <a href="<?= base_url('sales/quotation/'.$quotation_id); ?>" class="mp-btn-primary"><i class="fa fa-undo"></i> Convert to Invoice</a>
      <?php } else { ?>
        <a href="<?= base_url('sales/invoice/'.get_sales_id_of_quotation($quotation_id)); ?>" class="mp-btn-primary"><i class="fa fa-eye"></i> View Sales Invoice</a>
      <?php } ?>
      <a href="<?= base_url('quotation/print_invoice/'.$quotation_id); ?>" target="_blank" class="mp-btn-secondary"><i class="fa fa-print"></i> Print</a>
      <a href="<?= base_url('quotation/pdf/'.$quotation_id); ?>" target="_blank" class="mp-btn-secondary"><i class="fa fa-file-pdf-o"></i> PDF</a>
    </div>
  </div>
</div>

<script>$(".quotation_list-active-li").addClass("active");</script>
