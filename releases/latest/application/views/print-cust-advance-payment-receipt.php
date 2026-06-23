<!DOCTYPE html>
<html>
<head>
<!-- TABLES CSS CODE -->
<?php include"comman/code_css.php"; ?>
<style type="text/css">
	body{
		font-family: arial;
		font-size: 13px;
		font-weight: bold;
		padding-top:15px;
	}

	@media print {
        .no-print { display: none; }
    }
</style>
</head>
<body onload="window.print();"><!--   -->
	<?php
	$CI =& get_instance();
	
    //Current Store Records
    $store_rec = get_store_details();
    $store_logo=(!empty($store_rec->store_logo)) ? $store_rec->store_logo : store_demo_logo();

    //Sales Records
    $payment_rec = $this->db->select('*')->from('db_custadvance')->where('id',$payment_id)->get()->row();
    $customer_rec = get_customer_details($payment_rec->customer_id);



    $store_name		=$store_rec->store_name;
    $company_mobile		=$store_rec->mobile;
    $company_phone		=$store_rec->phone;
    $company_email		=$store_rec->email;
    $company_country	=$store_rec->country;
    $company_state		=$store_rec->state;
    $company_city		=$store_rec->city;
    $company_address	=$store_rec->address;
    $company_postcode	=$store_rec->postcode;
    $company_gst_no		=$store_rec->gst_no;//Goods and Service Tax Number (issued by govt.)
    $company_vat_number		=$store_rec->vat_no;//Goods and Service Tax Number (issued by govt.)
    

    ?>
	<table width="95%" align="center">
		<tr>
			<td align="center" width="30%">
				<img src="<?= base_url($store_logo);?>" width="30%" height="auto">
			</td>
		</tr>
		<tr>
			<td align="center">
				<span>													 
                <strong><?= $store_name; ?></strong><br>
                	<?php echo (!empty(trim($company_address))) ? $this->lang->line('company_address')."".$company_address."<br>" : '';?> 
		            <?= $company_city; ?>
		            <?php echo (!empty(trim($company_postcode))) ? "-".$company_postcode : '';?>
		            <br>
		            <?php echo (!empty(trim($company_gst_no)) && gst_number()) ? $this->lang->line('gst_number').": ".$company_gst_no."<br>" : '';?>
		            <?php echo (!empty(trim($company_vat_number)) && vat_number()) ? $this->lang->line('vat_number').": ".$company_vat_number."<br>" : '';?>
		            <?php if(!empty(trim($company_mobile))) 
		            		{ 
		            			echo $this->lang->line('phone').": ".$company_mobile;
		            			if(!empty($company_phone)){
		            				echo ",".$company_phone;
		            			}
		            			echo "<br>";
		            		}

		            ?> 
			</span>
			</td>
		</tr>
		<tr><td align="center"><strong>-----------------<?= $this->lang->line('advance_payment_receipt'); ?>-----------------</strong></td></tr>
		<tr>
			<td>
				<table width="100%">
					<tr>
						<td width="40%"><?= $this->lang->line('payment_id'); ?></td>
						<td><b>#<?= $payment_rec->payment_code; ?></b></td>
					</tr>
					<tr>
						<td><?= $this->lang->line('name'); ?></td>
						<td><?= $customer_rec->customer_name; ?></td>
					</tr>
					<tr>
						<td><?= $this->lang->line('date').":".show_date($payment_rec->payment_date); ?></td>
						<td style="text-align: right;"><?= $this->lang->line('time').":".$payment_rec->created_time; ?></td>
					</tr>
				</table>
				
			</td>
		</tr>
		<tr>
			<td>

				<table width="100%" cellpadding="0" cellspacing="0"  >
					<thead>
					<tr style="border-top-style: dashed;border-bottom-style: dashed;border-width: 0.1px;">
						<th style="font-size: 11px; text-align: left;padding-left: 2px; padding-right: 2px;">#</th>
						<th colspan=3 style="font-size: 11px; text-align: left;padding-left: 2px; padding-right: 2px;"><?= $this->lang->line('payment_type'); ?></th>
						<th style="font-size: 11px; text-align: right;padding-left: 2px; padding-right: 2px;"><?= $this->lang->line('payment'); ?></th>
					
					</tr>
					</thead>
					<tbody style="border-bottom-style: dashed;border-width: 0.1px;">
						<?php
			              $i=0;
			              $tot_qty=0;
			              $subtotal=0;
			              $tax_amt=0;
			              
			                  echo "<tr>";  
			                  echo "<td style='padding-left: 2px; padding-right: 2px;' valign='top'>".++$i."</td>";
			                  echo "<td colspan=3 style='padding-left: 2px; padding-right: 2px;'>".$payment_rec->payment_type."</td>";
			                  
			                  echo "<td style='text-align: right;padding-left: 2px; padding-right: 2px;'>".store_number_format($payment_rec->amount)."</td>";
			                  echo "</tr>";  
			            
			              ?>
					
				   </tbody>
				   <tfoot>
				   		<tr>
				   			<td style="padding-top: 50px;width:33%;" colspan="2"><?= $this->lang->line('recipient_signature'); ?></td>
				   			<td  style="padding-top: 50px;width:33%;"></td>
				   			<td style="padding-top: 50px;width:33%;text-align: right;" colspan="2"><?= $this->lang->line('customer_signature'); ?></td>
				   		</tr>
				   </tfoot>
				</table>
			</td>
		</tr>
	</table>
	<center >
  <div class="row no-print">
  <div class="col-md-12">
  <div class="col-md-2 col-md-offset-5 col-xs-4 col-xs-offset-4 form-group">
    <button type="button" id="" class="btn btn-block btn-success btn-xs" onclick="window.print();" title="Print">Print</button>
    <?php if(isset($_GET['redirect'])){ ?>
		<a href="<?= base_url().$_GET['redirect'];?>"><button type="button" class="btn btn-block btn-danger btn-xs" title="Back">Back</button></a>
	<?php } ?>
   </div>
   </div>
   </div>

</center>
</body>
</html>