<?php $this->load->view('finance/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<?php
$q3=$this->db->query("SELECT b.coupon_id,b.coupon_amt, b.due_date,b.quotation_id,b.store_id,b.customer_id,COALESCE(a.customer_name,'Walk-in Customer') as customer_name,a.mobile,a.phone,a.gstin,a.tax_number,a.email,a.shippingaddress_id,COALESCE(a.id,b.customer_id) as id,
                           a.opening_balance,a.country_id,a.state_id,a.city,
                           a.postcode,a.address,b.sales_date,b.created_time,b.reference_no,
                           b.sales_code,b.sales_status,b.sales_note,b.invoice_terms,
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
                           FROM db_sales b
                           LEFT JOIN db_customers a ON a.`id`=b.`customer_id`
                           WHERE b.`id`='$sales_id' AND b.store_id=".get_current_store_id());

$res3=$q3->row();
if($res3->store_id!=get_current_store_id()){
  $CI->show_access_denied_page();exit();
}
$customer_id=$res3->id;
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
$sales_date=$res3->sales_date;
$due_date=(!empty($res3->due_date)) ? show_date($res3->due_date) : '';
$created_time=$res3->created_time;
$reference_no=$res3->reference_no;
$sales_code=$res3->sales_code;
$sales_status=$res3->sales_status;
$sales_note=$res3->sales_note;
$invoice_terms=$res3->invoice_terms;
$quotation_id=$res3->quotation_id;

$coupon_id=$res3->coupon_id;
$coupon_amt=$res3->coupon_amt;
$coupon_code = '';
$coupon_type = '';
$coupon_value=0;
if(!empty($coupon_id)){
  $coupon_details = get_customer_coupon_details($coupon_id);
  $coupon_code = $coupon_details->code;
  $coupon_value = $coupon_details->value;
  $coupon_type = $coupon_details->type;
}

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

$shipping_country='';
$shipping_state='';
$shipping_city='';
$shipping_address='';
$shipping_postcode='';
if(!empty($res3->shippingaddress_id)){
    $Q2 = $this->db->select("c.country,s.state,a.city,a.postcode,a.address")
                    ->where("a.id",$res3->shippingaddress_id)
                    ->from("db_shippingaddress a")
                    ->join("db_country c","c.id = a.country_id",'left')
                    ->join("db_states s","s.id = a.state_id",'left')
                    ->get();
    if($Q2->num_rows()>0){
      $shipping_country=$Q2->row()->country;
      $shipping_state=$Q2->row()->state;
      $shipping_city=$Q2->row()->city;
      $shipping_address=$Q2->row()->address;
      $shipping_postcode=$Q2->row()->postcode;
    }
  }

$str2 = ($pos==1) ? 'pos/edit/' : 'sales/update/';
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
    <h2><?= $this->lang->line('sales_invoice'); ?> <span style="color:var(--mp-muted);font-weight:500;">#<?= htmlspecialchars($sales_code); ?></span></h2>
    <div class="mp-page-sub"><?= show_date($sales_date); ?> <?= $created_time; ?> &middot; <?= $this->lang->line('payment_status'); ?>: <?= ucfirst($payment_status); ?></div>
  </div>
  <div class="mp-quick-actions">
    <?php if($CI->permissions('sales_edit')) { ?>
      <a href="<?= base_url($str2.$sales_id); ?>" class="mp-qa-btn"><i class="fa fa-edit"></i> Edit</a>
    <?php } ?>
    <a href="<?= base_url('pos/print_invoice_pos/'.$sales_id); ?>" target="_blank" class="mp-qa-btn"><i class="fa fa-file-text"></i> POS Invoice</a>
    <a href="<?php $wa_share = get_whatsapp_share_url('sales',$sales_id); echo $wa_share['url']; ?>" target="_blank" class="mp-qa-btn" style="background:#25D366;color:#fff;"><i class="fa fa-whatsapp"></i> Share</a>
    <?php if($CI->permissions('sales_return_add')) { ?>
      <a href="<?= base_url('sales_return/add/'.$sales_id); ?>" class="mp-qa-btn" style="background:var(--mp-danger);color:#fff;"><i class="fa fa-undo"></i> Return</a>
    <?php } ?>
    <a href="<?= base_url('pdf/sales/'.$sales_id); ?>" target="_blank" class="mp-qa-btn"><i class="fa fa-file-pdf-o"></i> PDF</a>
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
          <h4><?= $this->lang->line('invoice_details'); ?></h4>
          <ul class="invoice-meta">
            <li><strong><?= $this->lang->line('reference_no'); ?>:</strong> <?= htmlspecialchars($reference_no); ?></li>
            <li><strong><?= $this->lang->line('due_date'); ?>:</strong> <?= $due_date; ?></li>
            <li><strong><?= $this->lang->line('payment_status'); ?>:</strong> <?= ucfirst($payment_status); ?></li>
            <?php if(!empty($quotation_id)){ ?>
            <li><strong><?= $this->lang->line('quotation'); ?>:</strong> <a href="<?= base_url('quotation/invoice/'.$quotation_id); ?>"><?= get_quotation_details($quotation_id)->quotation_code; ?></a></li>
            <?php } ?>
          </ul>
        </div>
      </div>

      <div class="invoice-grid">
        <div class="invoice-block">
          <h4><?= $this->lang->line('customer_address'); ?></h4>
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
        <div class="invoice-block">
          <h4><?= $this->lang->line('shipping_address'); ?> <a href="<?= base_url('customers/update/'.$customer_id); ?>" title="Edit Customer Details"><i class="fa fa-fw fa-edit text-red"></i></a></h4>
          <p>
            <strong><?= htmlspecialchars($customer_name); ?></strong><br>
            <?= $this->lang->line('address'); ?>: <?= htmlspecialchars($shipping_address); ?><br>
            <?= $this->lang->line('country'); ?>: <?= htmlspecialchars($shipping_country); ?><br>
            <?= $this->lang->line('state'); ?>: <?= htmlspecialchars($shipping_state); ?><br>
            <?= $this->lang->line('city'); ?>: <?= htmlspecialchars($shipping_city); ?><br>
            <?= $this->lang->line('postcode'); ?>: <?= htmlspecialchars($shipping_postcode); ?>
          </p>
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
              <th class="num"><?= $this->lang->line('price'); ?></th>
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
            $tot_sales_price=0;
            $tot_tax_amt=0;
            $tot_discount_amt=0;
            $tot_total_cost=0;
            $tot_price_per_unit=0;
            $sum_of_tot_price=0;

            $this->db->select(" a.description,c.mrp,COALESCE(c.item_name, a.description, 'Unknown Item') as item_name, a.sales_qty,a.tax_type,
                                  a.price_per_unit, b.tax,b.tax_name,a.tax_amt,
                                  a.discount_input,a.discount_amt, a.unit_total_cost,
                                  a.total_cost , d.unit_name,c.sku,c.hsn
                              ");
            $this->db->where("a.sales_id",$sales_id);
            $this->db->from("db_salesitems a");
            $this->db->join("db_tax b","b.id=a.tax_id","left");
            $this->db->join("db_items c","c.id=a.item_id","left");
            $this->db->join("db_units d","d.id = c.unit_id","left");
            $q2=$this->db->get();

            foreach ($q2->result() as $res2) {
                $str = ($res2->tax_type=='Inclusive')? 'Inc.' : 'Exc.';
                $discount = (empty($res2->discount_input)||$res2->discount_input==0)? '0': store_number_format($res2->discount_input)."%";
                $discount_amt = (empty($res2->discount_amt)||$res2->discount_input==0)? '0':$res2->discount_amt."";

                $price_per_unit = $res2->price_per_unit;
                if($res2->tax_type=='Inclusive'){
                  $price_per_unit -= ($res2->tax_amt/$res2->sales_qty);
                }

                $tot_price = $price_per_unit * $res2->sales_qty;
            ?>
            <tr>
              <td><?= ++$i; ?></td>
              <td>
                <?= htmlspecialchars($res2->item_name); ?>
                <?php if(!empty($res2->description)) { ?><span class="item-desc">[<?= nl2br(htmlspecialchars($res2->description)); ?>]</span><?php } ?>
              </td>
              <td class="num"><?= store_number_format($price_per_unit); ?></td>
              <td class="num"><?= format_qty($res2->sales_qty); ?></td>
              <td class="num"><?= store_number_format($tot_price); ?></td>
              <td class="num"><?= store_number_format($res2->tax); ?>%<br><?= htmlspecialchars($res2->tax_name); ?> [<?= $str; ?>]</td>
              <td class="num"><?= store_number_format($res2->tax_amt); ?></td>
              <td class="num"><?= $discount; ?></td>
              <td class="num"><?= store_number_format($discount_amt); ?></td>
              <td class="num"><?= store_number_format($res2->unit_total_cost); ?></td>
              <td class="num"><?= store_number_format($res2->total_cost); ?></td>
            </tr>
            <?php
                $tot_qty +=$res2->sales_qty;
                $tot_tax_amt +=$res2->tax_amt;
                $tot_discount_amt +=$res2->discount_amt;
                $tot_total_cost +=$res2->total_cost;
                $tot_price_per_unit +=$price_per_unit;
                $sum_of_tot_price +=$tot_price;
            }
            ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2" class="text-center">Total</td>
              <td class="num"><?= store_number_format($tot_price_per_unit); ?></td>
              <td class="num"><?= $tot_qty; ?></td>
              <td class="num"><?= store_number_format($sum_of_tot_price); ?></td>
              <td class="num">-</td>
              <td class="num"><?= store_number_format($tot_tax_amt); ?></td>
              <td class="num">-</td>
              <td class="num"><?= store_number_format($tot_discount_amt); ?></td>
              <td class="num">-</td>
              <td class="num"><?= store_number_format($tot_total_cost); ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  <div class="mp-card-form" style="margin-top:20px;">
    <div class="mp-card-head"><h3><i class="fa fa-money"></i> Payments</h3></div>
    <div class="mp-card-body" style="padding:0;">
      <div class="mp-dt-scroll">
        <table class="table mp-invoice-table mp-dt-table" style="margin:0;">
          <thead>
            <tr>
              <th>#</th>
              <th><?= $this->lang->line('date'); ?></th>
              <th><?= $this->lang->line('payment_type'); ?></th>
              <th><?= $this->lang->line('account'); ?></th>
              <th><?= $this->lang->line('payment_note'); ?></th>
              <th class="num"><?= $this->lang->line('payment'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php if(isset($sales_id)){
              $q3 = $this->db->query("select * from db_salespayments where sales_id=$sales_id");
              if($q3->num_rows()>0){
                $i=1;
                $total_paid = 0;
                foreach ($q3->result() as $res3) { ?>
            <tr>
              <td><?= $i++; ?></td>
              <td><?= show_date($res3->payment_date); ?></td>
              <td>
                <?= htmlspecialchars($res3->payment_type); ?>
                <?php if(!empty($res3->cheque_number)){ ?><br><small>Cheque no.: <?= htmlspecialchars($res3->cheque_number); ?> &middot; Period: <?= htmlspecialchars($res3->cheque_period); ?></small><?php } ?>
              </td>
              <td><?= get_account_name($res3->account_id); ?></td>
              <td><?= htmlspecialchars($res3->payment_note); ?></td>
              <td class="num"><?= store_number_format($res3->payment); ?></td>
            </tr>
            <?php $total_paid +=$res3->payment; } ?>
            <tr style="font-weight:700;background:rgba(0,87,255,.03);">
              <td colspan="5" class="num">Total</td>
              <td class="num"><?= store_number_format($total_paid); ?></td>
            </tr>
            <?php } else { ?>
            <tr>
              <td colspan="6" class="text-center">No Previous Payments Found</td>
            </tr>
            <?php } } else { ?>
            <tr>
              <td colspan="6" class="text-center">Payments Pending</td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="mp-form-grid" style="margin-top:20px;align-items:start;">
    <div class="mp-card-form notes-card">
      <div class="mp-card-head"><h3>Notes, Terms & Coupon</h3></div>
      <div class="mp-card-body">
        <?php if(!empty($coupon_code)) { ?>
        <div class="totals-row">
          <span class="label"><?= $this->lang->line('discountCouponCode'); ?></span>
          <span class="value"><?= getTruncatedCCNumber($coupon_code); ?></span>
        </div>
        <?php } ?>
        <?php if($discount_to_all_input > 0 || $tot_discount_to_all_amt > 0){ ?>
        <div class="totals-row">
          <span class="label"><?= $this->lang->line('discount_on_all'); ?></span>
          <span class="value"><?= store_number_format($discount_to_all_input); ?> (<?= $discount_to_all_type ?>)</span>
        </div>
        <?php } ?>
        <div class="totals-row" style="border-bottom:1px solid var(--mp-border);">
          <span class="label"><?= $this->lang->line('note'); ?></span>
          <span class="value" style="text-align:right;"><?= nl2br(htmlspecialchars($sales_note)); ?></span>
        </div>
        <div class="totals-row" style="border-bottom:none;">
          <span class="label"><?= $this->lang->line('invoiceTerms'); ?></span>
          <span class="value" style="text-align:right;"><?= nl2br(html_entity_decode(trim($invoice_terms))); ?></span>
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
        <?php if($coupon_amt > 0){ ?>
        <div class="totals-row">
          <span class="label"><?= $this->lang->line('couponDiscount'); ?> <?= ($coupon_type=='Percentage') ? $coupon_value .'%' : $coupon_value.' [Fixed]'; ?></span>
          <span class="value"><?= store_number_format($coupon_amt); ?></span>
        </div>
        <?php } ?>
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
      <a href="<?= base_url('sales'); ?>" class="mp-btn-secondary"><i class="fa fa-arrow-left"></i> Back to Sales</a>
    </div>
    <div style="display:flex;gap:12px;">
      <?php if($CI->permissions('sales_edit')) { ?>
        <a href="<?= base_url($str2.$sales_id); ?>" class="mp-btn-secondary"><i class="fa fa-edit"></i> Edit</a>
      <?php } ?>
      <a href="<?= base_url('pos/print_invoice_pos/'.$sales_id); ?>" target="_blank" class="mp-btn-secondary"><i class="fa fa-file-text"></i> POS Invoice</a>
      <a href="<?php $wa_share = get_whatsapp_share_url('sales',$sales_id); echo $wa_share['url']; ?>" target="_blank" class="mp-btn-secondary" style="background:#25D366;color:#fff;border-color:#25D366;"><i class="fa fa-whatsapp"></i> Share</a>
      <?php if($CI->permissions('sales_return_add')) { ?>
        <a href="<?= base_url('sales_return/add/'.$sales_id); ?>" class="mp-btn-primary" style="background:var(--mp-danger);border-color:var(--mp-danger);"><i class="fa fa-undo"></i> Sales Return</a>
      <?php } ?>
      <a href="<?= base_url('pdf/sales/'.$sales_id); ?>" target="_blank" class="mp-btn-secondary"><i class="fa fa-file-pdf-o"></i> PDF</a>
    </div>
  </div>
</div>

<script>$(".sales-list-active-li").addClass("active");</script>
