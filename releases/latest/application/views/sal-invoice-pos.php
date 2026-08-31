<!DOCTYPE html>
<html>
<head>
	<title>Default Invoice Format</title>
<?php include"comman/code_css.php"; ?>
<style type="text/css">
	body{
		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
		font-size: 9px;
		line-height: 1.35;
		color: #1f2937;
		padding: 6px 3px;
		max-width: 58mm;
		margin: 0 auto;
	}

	@media print {
        .no-print { display: none; }
        table.items tr, table.totals tr { page-break-inside: avoid; }
    }

    .brand { text-align: center; margin-bottom: 6px; }
    .brand img { max-width: 42px; height: auto; margin-bottom: 3px; }
    .brand h2 { font-size: 13px; margin: 0 0 2px; }
    .brand p { margin: 0; color: #6b7280; font-size: 8px; line-height: 1.3; }

    table.meta { width: 100%; border-collapse: collapse; font-size: 8px; margin-bottom: 4px; table-layout: fixed; }
    table.meta td { padding: 1px 0; vertical-align: top; width: 50%; }
    table.meta td:first-child { font-weight: 600; }
    table.meta td:last-child { text-align: right; white-space: nowrap; }

    .divider { border: none; border-top: 0.5px dashed #d1d5db; margin: 4px 0; }

    table.items { width: 100%; border-collapse: collapse; font-size: 8px; margin-bottom: 3px; table-layout: fixed; }
    table.items th { text-align: left; color: #6b7280; font-weight: 600; padding: 2px 0; border-bottom: 0.5px dashed #d1d5db; }
    table.items td { padding: 1px 0; vertical-align: top; word-wrap: break-word; }
    table.items th:nth-child(3), table.items td:nth-child(3) { text-align: center; }
    table.items th:nth-child(4), table.items td:nth-child(4),
    table.items th:nth-child(5), table.items td:nth-child(5),
    table.items th:nth-child(6), table.items td:nth-child(6) { text-align: right; }
    table.items th, table.items td { padding-left: 3px; padding-right: 3px; }
    table.items th:first-child, table.items td:first-child { padding-left: 0; }
    table.items th:last-child, table.items td:last-child { padding-right: 0; }
    table.items td small { font-size: 7px; color: #6b7280; }

    table.totals { width: 100%; border-collapse: collapse; font-size: 9px; table-layout: fixed; margin-top: 3px; }
    table.totals td { padding: 1px 0; vertical-align: top; }
    table.totals td:first-child { width: 55%; text-align: left; }
    table.totals td:last-child { width: 45%; text-align: right; white-space: nowrap; }
    table.totals tr.grand td { font-size: 11px; font-weight: 700; padding-top: 3px; border-top: 0.5px dashed #d1d5db; }

    .note { margin-top: 3px; font-size: 8px; }
    .footer { text-align: center; margin-top: 8px; padding-top: 5px; border-top: 0.5px dashed #d1d5db; color: #6b7280; font-size: 8px; }
    .reprint { font-weight: 700; color: #dc2626; }
</style>
</head>
<body onload="window.print();"><!--  -->
	<?php
	$CI =& get_instance();

    $q3=$this->db->query("SELECT b.coupon_id,b.coupon_amt, b.created_by, b.customer_previous_due,b.customer_total_due,b.store_id,b.customer_id,COALESCE(a.customer_name,'Walk-in Customer') as customer_name,a.mobile,a.phone,a.gstin,a.tax_number,a.email,a.delete_bit,b.invoice_terms,
                           a.opening_balance,a.country_id,a.state_id,
                           a.postcode,a.address,b.sales_date,b.created_time,b.reference_no,
                           b.sales_code,b.sales_note,a.sales_due,
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

                           FROM db_sales b
                           LEFT JOIN db_customers a ON a.`id`=b.`customer_id`
                           WHERE
                           b.`id`='$sales_id'
                           ");

    $res3=$q3->row();
    $customer_name=$res3->customer_name;
    $customer_mobile=$res3->mobile;
    $customer_phone=$res3->phone;
    $customer_email=$res3->email;
    $customer_country=$res3->country_id;
    $customer_state=$res3->state_id;
    $customer_address=$res3->address;
    $customer_postcode=$res3->postcode;
    $customer_gst_no=$res3->gstin;
    $customer_tax_number=$res3->tax_number;
    $customer_opening_balance=$res3->opening_balance;
    $sales_date=show_date($res3->sales_date);
    $reference_no=$res3->reference_no;
    $created_time=show_time($res3->created_time);
    $sales_code=$res3->sales_code;
    $sales_note=$res3->sales_note;
    $customer_delete_bit=$res3->delete_bit;

    $previous_due=$res3->sales_due-($res3->grand_total-$res3->paid_amount);
    $previous_due = ($previous_due>0) ? $previous_due : 0;
    $total_due=$res3->sales_due;

    $coupon_id=$res3->coupon_id;
    $coupon_amt=$res3->coupon_amt;

    $coupon_code = '';
    $coupon_type = '';
    $coupon_value=0;
    if(!empty($coupon_id)){
    	$coupon_details =get_customer_coupon_details($coupon_id);
    	$coupon_code =$coupon_details->code;
    	$coupon_value =$coupon_details->value;
    	$coupon_type =$coupon_details->type;
    }

    $subtotal=$res3->subtotal;
    $grand_total=$res3->grand_total;
    $other_charges_input=$res3->other_charges_input;
    $other_charges_tax_id=$res3->other_charges_tax_id;
    $other_charges_amt=$res3->other_charges_amt;
    $paid_amount=$res3->paid_amount;
    $discount_to_all_input=$res3->discount_to_all_input;
    $discount_to_all_type=$res3->discount_to_all_type;
    $tot_discount_to_all_amt=$res3->tot_discount_to_all_amt;
    $round_off=$res3->round_off;
    $payment_status=$res3->payment_status;

    if(!empty($customer_state)){
      $q6 = $this->db->query("select state from db_states where id='$customer_state'");
      if($q6->num_rows()>0){
      	$customer_state = $q6->row()->state;
      }
    }

    $overall_discounted = $tot_discount_to_all_amt + $coupon_amt;

    if($this->db->table_exists('db_store_receipt_settings')){
      $q1=$this->db->query("SELECT s.*, r.sales_invoice_footer_text FROM db_store s LEFT JOIN db_store_receipt_settings r ON r.store_id=s.id WHERE s.id=".$res3->store_id." ");
    }else{
      $q1=$this->db->query("select * from db_store where id=".$res3->store_id." ");
    }
    $res1=$q1->row();
    $store_name		=$res1->store_name;
    $company_mobile		=$res1->mobile;
    $company_phone		=$res1->phone;
    $company_email		=$res1->email;
    $company_country	=$res1->country;
    $company_state		=$res1->state;
    $company_city		=$res1->city;
    $company_address	=$res1->address;
    $company_postcode	=$res1->postcode;
    $company_gst_no		=$res1->gst_no;
    $company_vat_number		=$res1->vat_no;
    $store_logo=(!empty($res1->store_logo)) ? $res1->store_logo : store_demo_logo();
    $store_website		=$res1->store_website;
    $mrp_column		=$res1->mrp_column;
    $previous_balance_bit	=$res1->previous_balance_bit;
    $pos_invoice_format_id	=$res1->pos_invoice_format_id;
    $t_and_c_status_pos	=$res1->t_and_c_status_pos ?? 0;
    $sales_invoice_footer_text	=isset($res1->sales_invoice_footer_text) ? $res1->sales_invoice_footer_text : 'Thank you for shopping with us!';

    $pm = $this->db->query("SELECT payment_type FROM db_salespayments WHERE sales_id=$sales_id GROUP BY payment_type LIMIT 1");
    $payment_method = ($pm->num_rows()>0) ? $pm->row()->payment_type : 'Unpaid';

    $printed_by = $CI->session->userdata('display_name');
    if(empty($printed_by)){
      $u = get_user_details(get_current_user_id());
      $printed_by = !empty($u->username) ? $u->username : ucfirst($res3->created_by);
    }
    $is_reprint = (isset($_GET['reprint']) && $_GET['reprint']=='1');
    $print_time = date('Y-m-d H:i:s');
    ?>

    <div class="brand">
        <img src="<?= base_url($store_logo);?>" alt="" width="40" height="auto"><br>
        <h2><?= $store_name; ?></h2>
        <p>
            <?php echo (!empty(trim($company_address))) ? $company_address."<br>" : '';?>
            <?= $company_city; ?><?php echo (!empty(trim($company_postcode))) ? " - ".$company_postcode : '';?><br>
            <?php if(!empty(trim($company_mobile))): ?>Tel: <?= $company_mobile; ?><?= !empty($company_phone) ? ", ".$company_phone : ''; ?><br><?php endif; ?>
            <?= (!empty($company_email)) ? $company_email."<br>" : ''; ?>
            <?= (!empty($store_website)) ? $store_website : ''; ?>
        </p>
    </div>

    <table class="meta">
        <tr>
            <td><?= $this->lang->line('invoice'); ?>: #<?= $sales_code; ?></td>
            <td><?= $sales_date; ?> <?= $created_time; ?></td>
        </tr>
        <?php if(!empty($customer_name) && $customer_name != 'Walk-in Customer' && $customer_name != 'Walk In Customer'): ?>
        <tr>
            <td colspan="2"><?= $this->lang->line('name'); ?>: <?= $customer_name; ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td><?= $this->lang->line('seller'); ?>: <?= ucfirst($res3->created_by); ?></td>
            <td style="text-align:right;"><?= ($this->lang->line('cashier') ?: 'Cashier') ?>: <?= $printed_by; ?></td>
        </tr>
    </table>

    <hr class="divider">

    <table class="items">
        <thead>
        <tr>
            <th style="width:5%">#</th>
            <th style="width:<?=($mrp_column?'30%':'42%')?>"><?= $this->lang->line('description'); ?></th>
            <th style="width:10%"><?= $this->lang->line('qty'); ?></th>
            <?php if($mrp_column){ ?><th style="width:11%"><?= $this->lang->line('mrp'); ?></th><?php } ?>
            <th style="width:13%"><?= $this->lang->line('rate'); ?></th>
            <th style="width:26%"><?= $this->lang->line('total'); ?></th>
        </tr>
        </thead>
        <tbody>
            <?php
              $i=0;
              $tot_qty=0;
              $subtotal=0;
              $tax_amt=0;
              $this->db->select(" a.description,c.mrp,COALESCE(c.item_name, a.description, 'Unknown Item') as item_name, a.sales_qty,a.tax_type,
                                  a.price_per_unit, b.tax,b.tax_name,a.tax_amt,
                                  a.discount_input,a.discount_amt, a.unit_total_cost,
                                  a.total_cost , d.unit_name,c.sku,c.hsn,
                                  a.sold_serial_number, a.sold_imei_number
                              ");
              $this->db->where("a.sales_id",$sales_id);
              $this->db->from("db_salesitems a");
              $this->db->join("db_tax b","b.id=a.tax_id","left");
              $this->db->join("db_items c","c.id=a.item_id","left");
              $this->db->join("db_units d","d.id = c.unit_id","left");
              $q2=$this->db->get();
              foreach ($q2->result() as $res2) {
                  echo "<tr>";
                  echo "<td style='text-align:left;'>".++$i."</td>";
                  echo "<td style='text-align:left;'>".$res2->item_name;
                  if(!empty($res2->sold_serial_number)){
                    echo "<br><small>S/N: ".htmlspecialchars($res2->sold_serial_number)."</small>";
                  }
                  if(!empty($res2->sold_imei_number)){
                    echo "<br><small>IMEI: ".htmlspecialchars($res2->sold_imei_number)."</small>";
                  }
                  echo "</td>";
                  echo "<td style='text-align:center;'>".format_qty($res2->sales_qty)."</td>";
                  if($mrp_column){
                  	echo "<td style='text-align:right;'>".$CI->currency($res2->mrp)."</td>";
                  }
                  echo "<td style='text-align:right;'>".$CI->currency($res2->unit_total_cost)."</td>";
                  echo "<td style='text-align:right;'>".$CI->currency($res2->total_cost)."</td>";
                  echo "</tr>";
                  $subtotal+=($res2->total_cost);
                  $tax_amt+=$res2->tax_amt;
                  $overall_discounted+=$res2->discount_amt;
              }
              $before_tax = $subtotal-$tax_amt;
            ?>
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td><?= $this->lang->line('before_tax'); ?></td>
            <td><?= $CI->currency($before_tax); ?></td>
        </tr>

        <?php
            if(get_store_details()->pos_invoice_format_id == 1){
        ?>
            <tr>
                <td><?= $this->lang->line('tax_amount'); ?></td>
                <td><?= $CI->currency($tax_amt); ?></td>
            </tr>
        <?php
            }
            else{
                $this->db->select("
                                b.tax,
                                b.tax_name,
                                COALESCE(SUM(a.tax_amt),0) AS sum_of_tax_amt,
                                c.tax_type
                             ");
                $this->db->where("a.sales_id",$sales_id);
                $this->db->from("db_salesitems a");
                $this->db->join("db_tax b","b.id=a.tax_id","left");
                $this->db->join("db_items c","c.id=a.item_id","left");
                $this->db->group_by("a.tax_id");
                $q5=$this->db->get();

                if($q5->num_rows()>0){
                    foreach($q5->result() as $row){
                        $tax_per = $row->tax;
                        $sum_of_tax_amt = $row->sum_of_tax_amt;

                        if( $customer_delete_bit==1 || (strtoupper($customer_state) == strtoupper($company_state))){
                            $sgst_per = $cgst_per = ($tax_per/2)."%";
                            $sgst_amt = $cgst_amt = $sum_of_tax_amt / 2;
                            ?>
                            <tr>
                                <td><?= $this->lang->line('cgst'); ?> <?= $sgst_per; ?></td>
                                <td><?= $CI->currency($cgst_amt); ?></td>
                            </tr>
                            <tr>
                                <td><?= $this->lang->line('sgst'); ?> <?= $cgst_per; ?></td>
                                <td><?= $CI->currency($sgst_amt); ?></td>
                            </tr>
                            <?php
                        }else{
                            $igst_per = $tax_per."%";
                            $igst_amt = $sum_of_tax_amt;
                            ?>
                            <tr>
                                <td><?= $this->lang->line('igst'); ?> <?= $igst_per; ?></td>
                                <td><?= $CI->currency($igst_amt); ?></td>
                            </tr>
                            <?php
                        }
                    }
                }
            }
        ?>

        <?php if(!empty($coupon_code)) {?>
        <tr>
            <td><?= $this->lang->line('couponDiscount'); ?> <?= ($coupon_type=='Percentage') ? $coupon_value .'%' : '[Fixed]' ;?></td>
            <td><?= $CI->currency($coupon_amt); ?></td>
        </tr>
        <?php } ?>

        <?php if(!empty($tot_discount_to_all_amt) && $tot_discount_to_all_amt!=0) {?>
        <tr>
            <td><?= $this->lang->line('discount'); ?> <?= ($discount_to_all_type=='in_percentage') ? $discount_to_all_input .'%' : '[Fixed]' ;?></td>
            <td><?= $CI->currency($tot_discount_to_all_amt); ?></td>
        </tr>
        <?php } ?>

        <tr class="grand">
            <td><?= $this->lang->line('total'); ?></td>
            <td><?= $CI->currency($grand_total); ?></td>
        </tr>
        <tr>
            <td><?= $this->lang->line('paid_amount'); ?> - <?= $payment_method; ?></td>
            <td><?= $CI->currency($paid_amount); ?></td>
        </tr>

        <?php if(change_return_status()) {
            $change_return_amount = get_change_return_amount($sales_id); ?>
            <tr>
                <td><?= $this->lang->line('refund'); ?></td>
                <td><?= $CI->currency($change_return_amount); ?></td>
            </tr>
        <?php } ?>

        <?php if($previous_balance_bit==1) {?>
        <tr>
            <td><?= $this->lang->line('previous_due'); ?></td>
            <td><?= $CI->currency($previous_due); ?></td>
        </tr>
        <tr>
            <td><?= $this->lang->line('total_due_amount'); ?></td>
            <td><?= $CI->currency($total_due); ?></td>
        </tr>
        <?php } ?>
    </table>

    <?php if(!empty($coupon_code)) {?>
    <div class="note"><b><?= $this->lang->line('couponCode'); ?>:</b> <?=getTruncatedCCNumber($coupon_code);?></div>
    <?php }?>

    <?php if(!empty(trim($sales_note))) {?>
    <div class="note"><b>Note:</b> <?= $sales_note; ?></div>
    <?php }?>

    <?php if($t_and_c_status_pos){ ?>
    <div class="note"><b><?= $this->lang->line('invoiceTerms'); ?>:</b> <?=nl2br(get_invoice_terms_for_pos());?></div>
    <?php } ?>

    <div class="footer">
        <span class="reprint"><?= $is_reprint ? '*** REPRINT ***' : ''; ?></span><br>
        <?= $sales_invoice_footer_text; ?><br>
        <?= $is_reprint ? 'Reprinted' : 'Printed' ?>: <?= show_date($print_time); ?> <?= show_time($print_time); ?> by <?= $printed_by; ?>
    </div>

	<center class="no-print" style="margin-top: 12px;">
        <div style="padding: 0 10px; max-width: 300px; margin: 0 auto;">
            <button type="button" class="btn btn-success btn-block" onclick="window.print();" style="white-space: normal; width: 100%; font-size: 14px; padding: 10px 0;">
                Print Receipt
            </button>
            <?php if(isset($_GET['redirect'])){ ?>
                <a href="<?= base_url().$_GET['redirect']; ?>" class="btn btn-danger btn-block" style="white-space: normal; width: 100%; font-size: 14px; padding: 10px 0; margin-top: 8px;">
                    Back
                </a>
            <?php } ?>
        </div>
    </center>
</body>
</html>
