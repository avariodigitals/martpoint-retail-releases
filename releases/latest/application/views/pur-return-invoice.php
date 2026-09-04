<?php $this->load->view('finance/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<style type="text/css">
  .mp-invoice-head{display:flex;justify-content:space-between;align-items:flex-start;gap:24px;padding:24px 28px;border-bottom:1px solid var(--mp-border);flex-wrap:wrap;}
  .mp-invoice-head .mp-party-title{font-size:10px;font-weight:700;color:var(--mp-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;}
  .mp-invoice-head .mp-party-name{font-size:15px;font-weight:700;color:var(--mp-ink);margin-bottom:4px;}
  .mp-invoice-head .mp-party-line{font-size:12px;color:var(--mp-muted);line-height:1.5;}
  .mp-invoice-head .mp-invoice-meta{text-align:right;}
  .mp-invoice-head .mp-invoice-code{font-size:22px;font-weight:800;color:var(--mp-ink);}
  .mp-invoice-head .mp-invoice-date{font-size:13px;color:var(--mp-muted);margin-top:4px;}
  .mp-status-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-top:8px;}
  .mp-status-badge.return{background:rgba(220,38,38,.1);color:var(--mp-danger);}
  .mp-status-badge.cancel{background:rgba(120,113,108,.1);color:var(--mp-muted);}
  .mp-invoice-table{width:100%;border-collapse:collapse;font-size:13px;}
  .mp-invoice-table thead th{background:var(--mp-bg);color:var(--mp-muted);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:10px 12px;text-align:left;border-bottom:1px solid var(--mp-border);white-space:nowrap;}
  .mp-invoice-table thead th.num{text-align:right;}
  .mp-invoice-table thead th.ctr{text-align:center;}
  .mp-invoice-table tbody td{padding:10px 12px;border-bottom:1px solid var(--mp-border);color:var(--mp-ink);vertical-align:top;}
  .mp-invoice-table tbody td.num{text-align:right;font-variant-numeric:tabular-nums;}
  .mp-invoice-table tbody td.ctr{text-align:center;}
  .mp-invoice-table tbody tr:hover{background:var(--mp-bg);}
  .mp-invoice-table tfoot td{padding:10px 12px;border-top:2px solid var(--mp-border);font-weight:700;color:var(--mp-ink);background:var(--mp-bg);font-size:13px;}
  .mp-invoice-table tfoot td.num{text-align:right;}
  .mp-invoice-table tfoot td.ctr{text-align:center;}
  .mp-invoice-totals{width:100%;max-width:380px;margin-left:auto;}
  .mp-invoice-totals tr td{padding:8px 12px;font-size:13px;}
  .mp-invoice-totals tr td:first-child{text-align:right;color:var(--mp-muted);font-weight:600;}
  .mp-invoice-totals tr td:last-child{text-align:right;font-weight:700;color:var(--mp-ink);font-variant-numeric:tabular-nums;}
  .mp-invoice-totals tr.grand td{border-top:2px solid var(--mp-border);padding-top:12px;font-size:16px;}
  .mp-invoice-totals tr.grand td:first-child{color:var(--mp-ink);}
  .mp-invoice-totals tr.grand td:last-child{color:var(--mp-primary);font-size:18px;}
  .mp-payments-table{width:100%;border-collapse:collapse;font-size:13px;}
  .mp-payments-table thead th{background:var(--mp-bg);color:var(--mp-muted);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:10px 12px;text-align:left;border-bottom:1px solid var(--mp-border);}
  .mp-payments-table tbody td{padding:10px 12px;border-bottom:1px solid var(--mp-border);}
  .mp-payments-table tbody tr:hover{background:var(--mp-bg);}
  .mp-note-row{display:flex;gap:12px;padding:8px 0;font-size:13px;}
  .mp-note-row .mp-note-label{color:var(--mp-muted);font-weight:600;min-width:140px;}
  .mp-note-row .mp-note-value{color:var(--mp-ink);}
  @media print{
    .mp-page-head,.mp-quick-actions,.no-print,.mp-sidebar,.mp-nav,.mp-footer,.mp-assist{display:none!important;}
    .mp-main{padding:0!important;margin:0!important;}
    .mp-card-form{border:none!important;box-shadow:none!important;page-break-inside:avoid;}
  }
</style>

<div class="mp-page-head">
  <div>
    <h2><?= $this->lang->line('purchase_return_invoice'); ?> #<?php echo $return_code; ?></h2>
    <div class="mp-page-sub"><?= show_date($return_date); ?> &middot; <?= $supplier_name; ?></div>
  </div>
  <div class="mp-quick-actions" style="gap:8px;">
    <a href="<?= base_url('purchase_return'); ?>" class="mp-qa-btn"><i class="fa fa-arrow-left"></i> Back</a>
    <?php if($CI->permissions('sales_edit')) { ?>
    <a href="<?= base_url('purchase_return/edit/'.$return_id); ?>" class="mp-qa-btn blue"><i class="fa fa-edit"></i> Edit</a>
    <?php } ?>
  </div>
</div>

<?php
$q3=$this->db->query("SELECT b.store_id,b.purchase_id,a.supplier_name,a.mobile,a.phone,
                       a.gstin,a.tax_number,a.email,
                       a.opening_balance,a.country_id,a.state_id,a.city,
                       a.postcode,a.address,b.return_date,b.reference_no,
                       b.return_code,b.return_status,b.return_note,
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
                       FROM db_suppliers a,
                       db_purchasereturn b 
                       WHERE 
                       a.`id`=b.`supplier_id` AND 
                       b.`id`='$return_id' AND b.store_id=".get_current_store_id());

$res3=$q3->row();

if($res3->store_id!=get_current_store_id()){
  $CI->show_access_denied_page();exit();
}

$purchase_id=$res3->purchase_id;
$supplier_name=$res3->supplier_name;
$supplier_mobile=$res3->mobile;
$supplier_phone=$res3->phone;
$supplier_email=$res3->email;
$supplier_state=$res3->state_id;
$supplier_city=$res3->city;
$supplier_address=$res3->address;
$supplier_postcode=$res3->postcode;
$supplier_gst_no=$res3->gstin;
$supplier_tax_number=$res3->tax_number;
$supplier_opening_balance=$res3->opening_balance;
$return_date=$res3->return_date;
$reference_no=$res3->reference_no;
$return_code=$res3->return_code;
$return_status=$res3->return_status;
$return_note=$res3->return_note;
$supplier_country=get_country($res3->country_id);
$supplier_state=get_state($res3->state_id);

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

$purchase_code = (!empty($purchase_id))?$this->db->query("select purchase_code from db_purchase where id=".$purchase_id)->row()->purchase_code:'';

$q1=$this->db->query("select * from db_store where id=".$res3->store_id."");
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

$status_class = ($return_status=='Cancel') ? 'cancel' : 'return';
?>

<!-- Flash message -->
<div class="mp-card-form" style="margin-bottom:16px;">
  <div class="mp-card-body" style="padding:12px 20px;">
    <?php if($this->session->flashdata('error')!=''){ ?>
      <div class="alert alert-danger text-left" style="margin:0;">
        <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <strong><?= $this->session->flashdata('error') ?></strong>
      </div>
    <?php } else { ?>
      <div class="alert alert-success text-left" style="margin:0;">
        <a href="javascript:void()" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <strong>
          <?php
          if(!empty($this->session->flashdata('success'))){
            echo $this->session->flashdata('success')."<br>";
          }
          if(!empty($purchase_id)){
            echo "<i class='fa fa-fw fa-hand-o-right'></i>Return Against Purchase Entry [Purchase Code is ".$this->db->select('purchase_code')->where('id',$purchase_id)->get('db_purchase')->row()->purchase_code.'].';
          } else {
            echo '<i class="fa fa-fw fa-hand-o-right"></i>Direct Return Invoice.';
          }
          ?>
        </strong>
      </div>
    <?php } ?>
  </div>
</div>

<div class="printableArea">

<!-- Invoice Header Card -->
<div class="mp-card-form">
  <div class="mp-invoice-head">
    <div style="flex:1;min-width:240px;">
      <div class="mp-party-title"><?= $this->lang->line('from'); ?></div>
      <div class="mp-party-name"><?= $store_name; ?></div>
      <div class="mp-party-line"><?= $company_address; ?>, <?= $this->lang->line('city'); ?>: <?= $company_city; ?></div>
      <div class="mp-party-line"><?= $this->lang->line('phone'); ?>: <?= $company_phone; ?>, <?= $this->lang->line('mobile'); ?>: <?= $company_mobile; ?></div>
      <?php if(!empty(trim($company_email))){ ?><div class="mp-party-line"><?= $this->lang->line('email'); ?>: <?= $company_email; ?></div><?php } ?>
      <?php if(!empty(trim($company_gst_no)) && gst_number()){ ?><div class="mp-party-line"><?= $this->lang->line('gst_number'); ?>: <?= $company_gst_no; ?></div><?php } ?>
      <?php if(!empty(trim($company_vat_no)) && vat_number()){ ?><div class="mp-party-line"><?= $this->lang->line('vat_number'); ?>: <?= $company_vat_no; ?></div><?php } ?>
      <?php if(!empty(trim($company_pan_no)) && pan_number()){ ?><div class="mp-party-line"><?= $this->lang->line('pan_number'); ?>: <?= $company_pan_no; ?></div><?php } ?>
    </div>
    <div style="flex:1;min-width:240px;">
      <div class="mp-party-title"><?= $this->lang->line('supplier_details'); ?></div>
      <div class="mp-party-name"><?= $supplier_name; ?></div>
      <?php if(!empty($supplier_address) || !empty($supplier_country) || !empty($supplier_state) || !empty($supplier_city) || !empty($supplier_postcode)){ ?>
      <div class="mp-party-line"><?php
        echo $supplier_address;
        echo $supplier_country;
        if(!empty($supplier_state)) echo ",".$supplier_state;
        if(!empty($supplier_city)) echo ",".$supplier_city;
        if(!empty($supplier_postcode)) echo "-".$supplier_postcode;
      ?></div>
      <?php } ?>
      <?php if(!empty(trim($supplier_mobile))){ ?><div class="mp-party-line"><?= $this->lang->line('mobile'); ?>: <?= $supplier_mobile; ?></div><?php } ?>
      <?php if(!empty(trim($supplier_phone))){ ?><div class="mp-party-line"><?= $this->lang->line('phone'); ?>: <?= $supplier_phone; ?></div><?php } ?>
      <?php if(!empty(trim($supplier_email))){ ?><div class="mp-party-line"><?= $this->lang->line('email'); ?>: <?= $supplier_email; ?></div><?php } ?>
      <?php if(!empty(trim($supplier_gst_no)) && gst_number()){ ?><div class="mp-party-line"><?= $this->lang->line('gst_number'); ?>: <?= $supplier_gst_no; ?></div><?php } ?>
      <?php if(!empty(trim($supplier_tax_number))){ ?><div class="mp-party-line"><?= $this->lang->line('tax_number'); ?>: <?= $supplier_tax_number; ?></div><?php } ?>
    </div>
    <div class="mp-invoice-meta">
      <div class="mp-invoice-code">#<?= $return_code; ?></div>
      <div class="mp-invoice-date"><?= $this->lang->line('date'); ?>: <?= show_date($return_date); ?></div>
      <?php if(!empty($reference_no)){ ?><div class="mp-invoice-date"><?= $this->lang->line('reference_no'); ?>: <?= $reference_no; ?></div><?php } ?>
      <?php if($purchase_code){ ?><div class="mp-invoice-date"><?= $this->lang->line('return_against_purchase'); ?>: #<?= $purchase_code; ?></div><?php } ?>
      <div class="mp-status-badge <?= $status_class; ?>"><?= $return_status; ?></div>
    </div>
  </div>
</div>

<!-- Items Table -->
<div class="mp-card-form" style="margin-top:16px;">
  <div class="mp-card-head">
    <h3><i class="fa fa-list"></i> Items</h3>
  </div>
  <div class="mp-card-body" style="padding:0;overflow-x:auto;">
    <table class="mp-invoice-table">
      <thead>
        <tr>
          <th class="ctr" style="width:4%;">#</th>
          <th><?= $this->lang->line('item_name'); ?></th>
          <th class="num"><?= $this->lang->line('purchase_price'); ?></th>
          <th class="ctr"><?= $this->lang->line('quantity'); ?></th>
          <th><?= $this->lang->line('tax'); ?></th>
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
        $tot_purchase_price=0;
        $tot_tax_amt=0;
        $tot_discount_amt=0;
        $tot_unit_total_cost=0;
        $tot_total_cost=0;
        $q2=$this->db->query("SELECT a.description, c.item_name, a.return_qty,a.tax_type,
                            a.price_per_unit, b.tax,b.tax_name,a.tax_amt,
                            a.discount_input,a.discount_amt, a.unit_total_cost,
                            a.total_cost 
                            FROM 
                            db_purchaseitemsreturn AS a,db_tax AS b,db_items AS c 
                            WHERE 
                            c.id=a.item_id AND b.id=a.tax_id AND a.return_id='$return_id'");
        foreach ($q2->result() as $res2) {
            $str = ($res2->tax_type=='Inclusive')? 'Inc.' : 'Exc.';
            $discount = (empty($res2->discount_input)||$res2->discount_input==0)? '-':store_number_format($res2->discount_input)."%";
            $discount_amt = (empty($res2->discount_amt)||$res2->discount_input==0)? '-':$res2->discount_amt."";
            echo "<tr>";
            echo "<td class='ctr'>".++$i."</td>";
            echo "<td>";
              echo $res2->item_name;
              echo (!empty($res2->description)) ? "<br><i style='color:var(--mp-muted);font-size:12px;'>[".nl2br($res2->description)."]</i>" : '';
            echo "</td>";
            echo "<td class='num'>".$CI->currency($res2->price_per_unit)."</td>";
            echo "<td class='ctr'>".format_qty($res2->return_qty)."</td>";
            echo "<td>".$res2->tax_name."[".$str."]</td>";
            echo "<td class='num'>".$CI->currency($res2->tax_amt)."</td>";
            echo "<td class='num'>".$discount."</td>";
            echo "<td class='num'>".$CI->currency($discount_amt)."</td>";
            echo "<td class='num'>".$CI->currency($res2->unit_total_cost)."</td>";
            echo "<td class='num'>".$CI->currency($res2->total_cost)."</td>";
            echo "</tr>";
            $tot_qty +=$res2->return_qty;
            $tot_purchase_price +=$res2->price_per_unit;
            $tot_tax_amt +=$res2->tax_amt;
            $tot_discount_amt +=$res2->discount_amt;
            $tot_unit_total_cost +=$res2->unit_total_cost;
            $tot_total_cost +=$res2->total_cost;
        }
        ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" class="ctr"><?= $this->lang->line('total'); ?></td>
          <td class="ctr"><?=format_qty($tot_qty);?></td>
          <td>-</td>
          <td class="num"><?=$CI->currency($tot_tax_amt);?></td>
          <td>-</td>
          <td class="num"><?= $CI->currency($tot_discount_amt);?></td>
          <td class="num"><?= $CI->currency($tot_unit_total_cost);?></td>
          <td class="num"><?= $CI->currency($tot_total_cost);?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<!-- Notes, Payments & Totals -->
<div class="mp-card-form" style="margin-top:16px;">
  <div class="mp-card-body">
    <div class="mp-row r-equal" style="align-items:flex-start;">

      <!-- Left: Notes + Payments -->
      <div>
        <?php if(!empty($discount_to_all_input) || !empty($return_note)){ ?>
        <div style="margin-bottom:20px;">
          <?php if(!empty($discount_to_all_input)){ ?>
          <div class="mp-note-row">
            <span class="mp-note-label"><?= $this->lang->line('discount_on_all'); ?></span>
            <span class="mp-note-value">: <?=$discount_to_all_input; ?> (<?= $discount_to_all_type ?>)</span>
          </div>
          <?php } ?>
          <?php if(!empty($return_note)){ ?>
          <div class="mp-note-row">
            <span class="mp-note-label"><?= $this->lang->line('note'); ?></span>
            <span class="mp-note-value">: <?= nl2br(htmlspecialchars($return_note));?></span>
          </div>
          <?php } ?>
        </div>
        <?php } ?>

        <h4 style="font-size:13px;font-weight:700;color:var(--mp-ink);margin:0 0 12px;"><?= $this->lang->line('payments_information'); ?></h4>
        <table class="mp-payments-table">
          <thead>
            <tr>
              <th>#</th>
              <th><?= $this->lang->line('date'); ?></th>
              <th><?= $this->lang->line('payment_type'); ?></th>
              <th><?= $this->lang->line('account'); ?></th>
              <th><?= $this->lang->line('payment_note'); ?></th>
              <th style="text-align:right;"><?= $this->lang->line('payment'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php
              if(isset($return_id)){
                $q3p = $this->db->query("select * from db_purchasepaymentsreturn where return_id=$return_id");
                if($q3p->num_rows()>0){
                  $pi=1;
                  $total_paid = 0;
                  foreach ($q3p->result() as $res3p) {
                    echo "<tr id='payment_row_".$res3p->id."'>";
                    echo "<td>".$pi++."</td>";
                    echo "<td>".show_date($res3p->payment_date)."</td>";
                    echo "<td>".$res3p->payment_type."</td>";
                    echo "<td>".get_account_name($res3p->account_id)."</td>";
                    echo "<td>".$res3p->payment_note."</td>";
                    echo "<td style='text-align:right;font-weight:600;'>".$CI->currency($res3p->payment)."</td>";
                    echo "</tr>";
                    $total_paid +=$res3p->payment;
                  }
                  echo "<tr><td colspan='5' style='text-align:right;font-weight:700;'>Total</td><td style='text-align:right;font-weight:700;'>".$CI->currency($total_paid)."</td></tr>";
                }
                else{
                  echo "<tr><td colspan='6' style='text-align:center;padding:24px;color:var(--mp-muted);'>No Previous Payments Found</td></tr>";
                }
              }
              else{
                echo "<tr><td colspan='6' style='text-align:center;padding:24px;color:var(--mp-muted);'>Payments Pending</td></tr>";
              }
            ?>
          </tbody>
        </table>
      </div>

      <!-- Right: Totals -->
      <div>
        <table class="mp-invoice-totals">
          <tr>
            <td><?= $this->lang->line('subtotal'); ?></td>
            <td id="subtotal_amt" name="subtotal_amt"><?=$CI->currency($subtotal);?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('other_charges'); ?></td>
            <td id="other_charges_amt" name="other_charges_amt"><?=$CI->currency($other_charges_amt);?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('discount_on_all'); ?></td>
            <td id="discount_to_all_amt" name="discount_to_all_amt"><?=$CI->currency($tot_discount_to_all_amt);?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('round_off'); ?></td>
            <td id="round_off_amt" name="tot_round_off_amt"><?=$CI->currency($round_off);?></td>
          </tr>
          <tr class="grand">
            <td><?= $this->lang->line('grand_total'); ?></td>
            <td id="total_amt" name="total_amt"><?=$CI->currency($grand_total);?></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>

</div><!-- /printableArea -->

<!-- Action Bar -->
<div class="mp-card-form no-print" style="margin-top:16px;">
  <div class="mp-card-body" style="padding:16px 20px;">
    <div class="mp-form-actions" style="justify-content:space-between;flex-wrap:wrap;gap:12px;">
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <?php if($CI->permissions('sales_edit')) { ?>
        <a href="<?= base_url('purchase_return/edit/'.$return_id); ?>" class="mp-btn-primary"><i class="fa fa-edit"></i> Edit</a>
        <?php } ?>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="<?= base_url('purchase_return/print_invoice/'.$return_id); ?>" target="_blank" class="mp-btn-secondary"><i class="fa fa-print"></i> Print</a>
        <a href="<?= base_url('purchase_return/pdf/'.$return_id); ?>" target="_blank" class="mp-btn-secondary"><i class="fa fa-file-pdf-o"></i> PDF</a>
      </div>
    </div>
  </div>
</div>

<script>$(".purchase-returns-list-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
