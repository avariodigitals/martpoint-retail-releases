<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Purchase_model extends CI_Model {
	//Datatable start
	var $table = 'db_purchase as a';
	var $column_order = array( 
								'a.id',
								'a.purchase_date',
								'a.purchase_code',
								'a.purchase_status',
								'a.reference_no',
								'b.supplier_name',
								'a.grand_total',
								'a.paid_amount',
								'a.payment_status',
								'a.created_by',
								'a.return_bit',
								'a.store_id'
								); //set column field database for datatable orderable
	var $column_search = array( 
								'a.id',
								'a.purchase_date',
								'a.purchase_code',
								'a.purchase_status',
								'a.reference_no',
								'b.supplier_name',
								'a.grand_total',
								'a.paid_amount',
								'a.payment_status',
								'a.created_by',
								'a.return_bit',
								'a.store_id'
								); //set column field database for datatable searchable 
	var $order = array('a.id' => 'desc'); // default order 

	public function __construct()
	{
		parent::__construct();
	}

	private function _get_datatables_query()
	{
		$this->db->select($this->column_order);
		$this->db->from($this->table);
		$this->db->join('db_suppliers as b','b.id=a.supplier_id','left');
		
		/*If warehouse selected*/
		$warehouse_id = $this->input->post('warehouse_id');
		if(!empty($warehouse_id)){
			$this->db->join('db_warehouse as w','w.id='.$warehouse_id,'left');
			$this->db->where('a.warehouse_id',$warehouse_id);
		}

		if(!is_admin()){
	      	if($this->session->userdata('role_id')!='2'){
	      		if(!permissions('show_all_users_purchase_invoices')){
	      			$this->db->where("upper(a.created_by)",strtoupper($this->session->userdata('inv_username')));
	      		}
	      	}
	      }
		
		//if not admin
	    /*if(!is_admin()){*/
	      $this->db->where("a.store_id",get_current_store_id());
	    /*}*/
		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if($_POST['search']['value']) // if datatable send POST for search
			{
				
				

				if($i===0) // first loop
				{
					$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.

					$this->db->like($item, $_POST['search']['value']);

				}
				else
				{
					$this->db->or_like($item, $_POST['search']['value']);
				}

				


				if(count($this->column_search) - 1 == $i) //last loop
					$this->db->group_end(); //close bracket
			}
			$i++;
		}
		
		if(isset($_POST['order'])) // here order processing
		{
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} 
		else if(isset($this->order))
		{
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables()
	{
		$this->_get_datatables_query();
		if($_POST['length'] != -1)
		$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	function count_filtered()
	{
		$this->_get_datatables_query();
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function count_all()
	{
		$this->db->where("store_id",get_current_store_id());
		$this->db->from($this->table);
		return $this->db->count_all_results();
	}
	//Datatable end

	public function xss_html_filter($input){
		return $this->security->xss_clean(html_escape($input));
	}

	//Save Cutomers
	public function verify_save_and_update(){
		$command = $this->input->post('command', TRUE);
		$pur_date = $this->input->post('pur_date', TRUE);
		$reference_no = $this->input->post('reference_no', TRUE);
		$purchase_status = $this->input->post('purchase_status', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$other_charges_input = $this->input->post('other_charges_input', TRUE);
		$other_charges_tax_id = $this->input->post('other_charges_tax_id', TRUE);
		$other_charges_amt = $this->input->post('other_charges_amt', TRUE);
		$discount_to_all_input = $this->input->post('discount_to_all_input', TRUE);
		$discount_to_all_type = $this->input->post('discount_to_all_type', TRUE);
		$tot_discount_to_all_amt = $this->input->post('tot_discount_to_all_amt', TRUE);
		$tot_subtotal_amt = $this->input->post('tot_subtotal_amt', TRUE);
		$tot_round_off_amt = $this->input->post('tot_round_off_amt', TRUE);
		$tot_total_amt = $this->input->post('tot_total_amt', TRUE);
		$purchase_note = $this->input->post('purchase_note', TRUE);
		$rowcount = $this->input->post('rowcount', TRUE);
		$purchase_id = $this->input->post('purchase_id', TRUE);
		$warehouse_id = $this->input->post('warehouse_id', TRUE);
		$store_id = $this->input->post('store_id', TRUE);
		$amount = $this->input->post('amount', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$payment_note = $this->input->post('payment_note', TRUE);
		$account_id = $this->input->post('account_id', TRUE);
		//echo "<pre>";print_r($this->xss_html_filter(array_merge($this->data,$_POST,$_GET)));exit();
		
		//varify max sales usage of the package subscription
		validate_package_offers('max_invoices','db_purchase');
		//END

		$this->db->trans_begin();
		$pur_date=system_fromatted_date($pur_date);

		if($other_charges_input=='' || $other_charges_input==0){$other_charges_input=null;}
	    if($other_charges_tax_id=='' || $other_charges_tax_id==0){$other_charges_tax_id=null;}
	    if($other_charges_amt=='' || $other_charges_amt==0){$other_charges_amt=null;}
	    if($discount_to_all_input=='' || $discount_to_all_input==0){$discount_to_all_input=null;}
	    if($tot_discount_to_all_amt=='' || $tot_discount_to_all_amt==0){$tot_discount_to_all_amt=null;}
	    if($tot_round_off_amt=='' || $tot_round_off_amt==0){$tot_round_off_amt=null;}

	    $prev_item_ids = array();
	    
	    if($command=='save'){//Create purchase code unique if first time entry
		    
			$this->db->query("ALTER TABLE db_purchase AUTO_INCREMENT = 1");
			
		    $purchase_entry = array(
		    				'purchase_code' 			=> get_init_code('purchase'), 
		    				'count_id' 					=> get_count_id('db_purchase'), 
		    				'reference_no' 				=> $reference_no, 
		    				'purchase_date' 			=> $pur_date,
		    				'purchase_status' 			=> $purchase_status,
		    				'supplier_id' 				=> $supplier_id,
		    				/*Other Charges*/
		    				'other_charges_input' 		=> $other_charges_input,
		    				'other_charges_tax_id' 		=> $other_charges_tax_id,
		    				'other_charges_amt' 		=> $other_charges_amt,
		    				/*Discount*/
		    				'discount_to_all_input' 	=> $discount_to_all_input,
		    				'discount_to_all_type' 		=> $discount_to_all_type,
		    				'tot_discount_to_all_amt' 	=> $tot_discount_to_all_amt,
		    				/*Subtotal & Total */
		    				'subtotal' 					=> $tot_subtotal_amt,
		    				'round_off' 				=> $tot_round_off_amt,
		    				'grand_total' 				=> $tot_total_amt,
		    				'purchase_note' 			=> $purchase_note,
		    				/*System Info*/
		    				'created_date' 				=> $CUR_DATE,
		    				'created_time' 				=> $CUR_TIME,
		    				'created_by' 				=> $CUR_USERNAME,
		    				'system_ip' 				=> $SYSTEM_IP,
		    				'system_name' 				=> $SYSTEM_NAME,
		    				'status' 					=> 1,
		    			);
		    $purchase_entry['store_id']=(store_module() && is_admin()) ? $store_id : get_current_store_id();  	
		    $purchase_entry['warehouse_id']=(warehouse_module() && warehouse_count()>1) ? $warehouse_id : get_store_warehouse_id();  	
			$q1 = $this->db->insert('db_purchase', $purchase_entry);
			$purchase_id = $this->db->insert_id();
		}
		else if($command=='update'){	
			$purchase_entry = array(
		    				'reference_no' 				=> $reference_no, 
		    				'purchase_date' 			=> $pur_date,
		    				'purchase_status' 			=> $purchase_status,
		    				'supplier_id' 				=> $supplier_id,
		    				/*'warehouse_id' 				=> $warehouse_id,*/
		    				/*Other Charges*/
		    				'other_charges_input' 		=> $other_charges_input,
		    				'other_charges_tax_id' 		=> $other_charges_tax_id,
		    				'other_charges_amt' 		=> $other_charges_amt,
		    				/*Discount*/
		    				'discount_to_all_input' 	=> $discount_to_all_input,
		    				'discount_to_all_type' 		=> $discount_to_all_type,
		    				'tot_discount_to_all_amt' 	=> $tot_discount_to_all_amt,
		    				/*Subtotal & Total */
		    				'subtotal' 					=> $tot_subtotal_amt,
		    				'round_off' 				=> $tot_round_off_amt,
		    				'grand_total' 				=> $tot_total_amt,
		    				'purchase_note' 			=> $purchase_note,
		    			);
			$purchase_entry['store_id']=(store_module() && is_admin()) ? $store_id : get_current_store_id();  
			$purchase_entry['warehouse_id']=(warehouse_module() && warehouse_count()>1) ? $warehouse_id : get_store_warehouse_id();  		
			$q1 = $this->db->where('id',$purchase_id)->update('db_purchase', $purchase_entry);

			##############################################START
			//FIND THE PREVIOUSE ITEM LIST ID'S
			$prev_item_ids = $this->db->select("item_id")->from("db_purchaseitems")->where("purchase_id",$purchase_id)->get()->result_array();
			##############################################END

			$q11=$this->db->query("delete from db_purchaseitems where purchase_id='$purchase_id'");
			if(!$q11){
				return "failed";
			}
		}
		//end

		

		//Import post data from form
		for($i=1;$i<=$rowcount;$i++){
		
			if(isset($_REQUEST['tr_item_id_'.$i]) && !empty($_REQUEST['tr_item_id_'.$i])){

				$item_id 			=$this->xss_html_filter(trim($_REQUEST['tr_item_id_'.$i]));
				$purchase_qty		=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_3']));
				$price_per_unit 	=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_4']));
				$tax_id 			=$this->xss_html_filter(trim($_REQUEST['tr_tax_id_'.$i]));
				$tax_amt 			=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_5']));
				$tax_type			=$this->xss_html_filter(trim($_REQUEST['tr_tax_type_'.$i]));
				
				$unit_total_cost	=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_10']));
				$total_cost			=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_9']));
				
				$discount_type 		=$this->xss_html_filter(trim($_REQUEST['item_discount_type_'.$i]));
				$discount_input 	=$this->xss_html_filter(trim($_REQUEST['item_discount_input_'.$i]));
				$discount_amt	    =$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_8']));//Amount
				$description		=$this->xss_html_filter(trim($_REQUEST['description_'.$i]));
				$batch_lot			=$this->xss_html_filter(trim($_REQUEST['batch_lot_'.$i] ?? ''));
				$barcode			=$this->xss_html_filter(trim($_REQUEST['barcode_'.$i] ?? ''));
				$expire_date		=$this->xss_html_filter(trim($_REQUEST['expire_date_'.$i] ?? ''));
				$mfg_date			=$this->xss_html_filter(trim($_REQUEST['mfg_date_'.$i] ?? ''));
				$received_qty		=$this->xss_html_filter(trim($_REQUEST['received_qty_'.$i] ?? ''));

				// Determine received quantity based on status
				if($purchase_status == 'Received'){
					$received_qty = $purchase_qty;
				} else if($purchase_status == 'Partially Received'){
					$received_qty = (!empty($received_qty) && is_numeric($received_qty)) ? $received_qty : 0;
				} else {
					$received_qty = null; // Draft / Ordered: nothing received yet
				}

				// Format dates
				if(!empty($expire_date)){
					$expire_date = system_fromatted_date($expire_date);
				}
				if(!empty($mfg_date)){
					$mfg_date = system_fromatted_date($mfg_date);
				}

				$purchaseitems_entry = array(
		    				'purchase_id' 		=> $purchase_id,
		    				'purchase_status'	=> $purchase_status,
		    				'item_id' 			=> $item_id,
		    				'purchase_qty' 		=> $purchase_qty,
		    				'price_per_unit' 	=> $price_per_unit,
		    				'tax_id' 			=> $tax_id,
		    				'tax_amt' 			=> $tax_amt,
		    				'tax_type' 			=> $tax_type,
		    				'discount_input' 	=> $discount_input,
		    				'discount_amt' 		=> $discount_amt,
		    				'unit_total_cost' 	=> $unit_total_cost,
		    				'total_cost' 		=> $total_cost,
		    				'discount_type' 	=> $discount_type,
		    				'description' 		=> $description,
		    				'batch_lot' 		=> $batch_lot,
		    				'barcode' 			=> $barcode,
		    				'expire_date' 		=> !empty($expire_date) ? $expire_date : null,
		    				'mfg_date' 			=> !empty($mfg_date) ? $mfg_date : null,
		    				'received_qty' 		=> $received_qty,
		    				'status'			=> 1,
		    			);
				$purchaseitems_entry['store_id']=(store_module() && is_admin()) ? $store_id : get_current_store_id();
				
				$q2 = $this->db->insert('db_purchaseitems', $purchaseitems_entry);
				if(!$q2){
					return "failed";
				}
				
				// Only update stock and create barcode records if stock is being received
				if($purchase_status == 'Received' || $purchase_status == 'Partially Received'){
					//UPDATE itemS QUANTITY IN itemS TABLE
					$this->load->model('pos_model');				
					$q6=$this->pos_model->update_items_quantity($item_id);
					if(!$q6){
						return "failed";
					}

					// Create or update barcode/batch record in db_item_barcodes
					if(!empty($barcode) && $received_qty > 0){
						$existing_bc = $this->db->where('item_id',$item_id)
											->where('barcode',$barcode)
											->where('batch_lot',$batch_lot)
											->get('db_item_barcodes')->row();
						if($existing_bc){
							// Add to existing batch quantity
							$new_qty = $existing_bc->qty + $received_qty;
							$this->db->where('id',$existing_bc->id)->update('db_item_barcodes', array(
								'qty' => $new_qty,
								'purchase_price' => store_number_format($price_per_unit,0),
								'expire_date' => !empty($expire_date) ? $expire_date : $existing_bc->expire_date,
								'mfg_date' => !empty($mfg_date) ? $mfg_date : $existing_bc->mfg_date,
							));
						} else {
							// Get item sales_price and mrp for the barcode record
							$item_details = $this->db->select('sales_price,mrp')->where('id',$item_id)->get('db_items')->row();
							$this->db->insert('db_item_barcodes', array(
								'item_id' => $item_id,
								'barcode' => $barcode,
								'batch_lot' => $batch_lot,
								'purchase_price' => store_number_format($price_per_unit,0),
								'sales_price' => store_number_format($item_details->sales_price ?? 0,0),
								'mrp' => store_number_format($item_details->mrp ?? 0,0),
								'qty' => $received_qty,
								'expire_date' => !empty($expire_date) ? $expire_date : null,
								'mfg_date' => !empty($mfg_date) ? $mfg_date : null,
								'warehouse_id' => $warehouse_id,
								'status' => 1,
								'created_date' => date('Y-m-d'),
								'created_time' => date('H:i:s'),
							));
						}
					}
				}

				
			}
		
		}//for end

		if($amount=='' || $amount==0){$amount=null;}
		if($amount>0 && !empty($payment_type)){

			if($amount>$tot_total_amt){
				echo "Payble amount should not be exceeds Invoice Amount!!";exit;
			}

			/**
			 * @update
			 * Verifieng previous and current payment total with invoice amount
			*/
			if($command=='update'){
				$tot_payment = $this->db->select('coalesce(sum(payment),0) as payment')->where('purchase_id',$purchase_id)->get('db_purchasepayments')->row()->payment;
				if(($tot_payment+$amount)>$tot_total_amt){
					echo "Payble amount should not be exceeds Invoice Amount!!\nPlease check previous payments as well.";exit;
				}
			}


			$payment_code=get_init_code('purchase_payment');
			$purchasepayments_entry = array(
					'payment_code' 		=> $payment_code,
		    		'count_id'	  		=> get_count_id('db_purchasepayments'),
					'purchase_id' 		=> $purchase_id, 
					'payment_date'		=> $pur_date,//Current Payment with Purchase entry
					'payment_type' 		=> $payment_type,
					'payment' 			=> $amount,
					'payment_note' 		=> $payment_note,
					'created_date' 		=> $CUR_DATE,
    				'created_time' 		=> $CUR_TIME,
    				'created_by' 		=> $CUR_USERNAME,
    				'system_ip' 		=> $SYSTEM_IP,
    				'system_name' 		=> $SYSTEM_NAME,
    				'status' 			=> 1,
    				'account_id' 		=> (empty($account_id)) ? null : $account_id,
    				'supplier_id' 		=> $supplier_id,
				);
			$purchasepayments_entry['store_id']=(store_module() && is_admin()) ? $store_id : get_current_store_id();  	
			$q3 = $this->db->insert('db_purchasepayments', $purchasepayments_entry);

			//Set the payment to specified account
			if(!empty($account_id)){
				//ACCOUNT INSERT
				$insert_bit = insert_account_transaction(array(
															'transaction_type'  	=> 'PURCHASE PAYMENT',
															'reference_table_id'  	=> $this->db->insert_id(),
															'debit_account_id'  	=> $account_id,
															'credit_account_id'  	=> null,
															'debit_amt'  			=> $amount,
															'credit_amt'  			=> 0,
															'process'  				=> 'SAVE',
															'note'  				=> $payment_note,
															'transaction_date'  	=> $CUR_DATE,
															'payment_code'  		=> $payment_code,
															'customer_id'  			=> null,
															'supplier_id'  			=> $supplier_id,
													));
				if(!$insert_bit){
					return "failed";
				}
			}
			//end
			
		}
		
		$q10=$this->update_purchase_payment_status($purchase_id);
		if($q10!=1){
			return "failed";
		}

		##############################################START
		//FIND THE PREVIOUSE ITEM LIST ID'S
		$curr_item_ids = $this->db->select("item_id")->from("db_purchaseitems")->where("purchase_id",$purchase_id)->get()->result_array();
		$two_array = array_merge($prev_item_ids,$curr_item_ids);

		/*Update items in all warehouses of the item*/
		$q7=update_warehouse_items($two_array);
		if(!$q7){
			return "failed";
		}
		##############################################END
		
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return "failed";
		}
		$this->db->trans_commit();
		$this->session->set_flashdata('success', 'Success!! Record Saved Successfully!');
		return "success<<<###>>>$purchase_id";
		
	}//verify_save_and_update() function end

	public function delete_payment($payment_id){
        $this->db->trans_begin();
         //ACCOUNT RESET
		$reset_accounts = $this->db->select("debit_account_id,credit_account_id")
									->where("ref_purchasepayments_id in ($payment_id)")
									->group_by("debit_account_id,credit_account_id")
									->get("ac_transactions");
		//ACCOUNT RESET END

		$purchase_id = $this->db->query("select purchase_id from db_purchasepayments where id=$payment_id")->row()->purchase_id;

		$q1=$this->db->query("delete from db_purchasepayments where id='$payment_id'");
		$q2=$this->update_purchase_payment_status($purchase_id);

		//ACCOUNT RESET
        if($reset_accounts->num_rows()>0){
        	foreach ($reset_accounts->result() as $res1) {
        		if(!update_account_balance($res1->debit_account_id)){
					return 'failed';
				}

				if(!update_account_balance($res1->credit_account_id)){
					return 'failed';
				}

        	}
        }
        //ACCOUNT RESET END


		if($q1!=1 || $q2!=1)
		{
			$this->db->trans_rollback();
		    return "failed";
		}
		else{
			$this->db->trans_commit();
		        return "success";
		}
	}

	public function update_purchase_payment_status_by_purchase_id($purchase_id){
		$q8=$this->db->query("select COALESCE(SUM(payment),0) as payment from db_purchasepayments where purchase_id='$purchase_id'");
		$sum_of_payments=$q8->row()->payment;
		

		$q9=$this->db->query("select coalesce(grand_total,0) as total from db_purchase where id='$purchase_id'");
		$payble_total=$q9->row()->total;
		
		//$pending_amt=$payble_total-$sum_of_payments;

		$payment_status='';
		if($payble_total==$sum_of_payments){
			$payment_status="Paid";
		}
		else if($sum_of_payments!=0 && ($sum_of_payments<$payble_total)){
			$payment_status="Partial";
		}
		else if($sum_of_payments==0){
			$payment_status="Unpaid";
		}


		$q7=$this->db->query("update db_purchase set 
							payment_status='$payment_status',
							paid_amount=$sum_of_payments 
							where id='$purchase_id'");
		$supplier_id =$this->db->query("select supplier_id from db_purchase where id=$purchase_id")->row()->supplier_id;

		$purchase_due =$this->db->query("select COALESCE(SUM(grand_total),0)-COALESCE(SUM(paid_amount),0) as purchase_due from db_purchase where supplier_id=$supplier_id and purchase_status IN ('Received','Partially Received')")->row()->purchase_due;
		
		$q12 = $this->db->query("update db_suppliers set purchase_due=$purchase_due where id=$supplier_id");
		if(!$q7 || !$q12)
		{
			return false;
		}
		else{
			return true;
		}
	}

	function update_purchase_payment_status($purchase_id=null){
	//UPDATE PRODUCTS QUANTITY IN PRODUCTS TABLE
		if(empty($purchase_id)){ //If purchase ID not exist you need setup all the suppliers purchase due
			$q11=$this->db->query("select id from db_suppliers");
			if($q11->num_rows()>0){
				foreach ($q11->result() as $res) {
					$q12=$this->db->query("select id from db_purchase where supplier_id=".$res->id);
					if($q12->num_rows()>0){
						foreach ($q12->result() as $res12) {
							if(!$this->update_purchase_payment_status_by_purchase_id($res12->id)){
								return false;
							}
						}
					}
					else{
						$q13=$this->db->query("update db_suppliers set purchase_due=0 where id=".$res->id);
						if(!$q13){
							return false;
						}
					}
				}
			}
			return true;
		}
		else{
					if(!$this->update_purchase_payment_status_by_purchase_id($purchase_id)){
						return false;
					}
					return true;
		}
	}

	function fun_temp_update_suppliers_purchase_due(){
		
		
		$q11=$this->db->query("select id from db_suppliers");
		if($q11->num_rows()>0){
			foreach ($q11->result() as $res) {

				$q12=$this->db->query("select id from db_purchase where supplier_id=".$res->id);
				if($q12->num_rows()>0){
					foreach ($q12->result() as $res12) {
						if(!$this->purchase_model->update_purchase_payment_status($res12->id)){
							return false;
						}
					}
				}
				else{
					$q13=$this->db->query("update db_suppliers set purchase_due=0 where id=".$res->id);
					if(!$q13){
						return false;
					}
				}
			}
		}
		return true;
	}



	public function delete_purchase($ids){
      	$this->db->trans_begin();

      	//ACCOUNT RESET
		$reset_accounts = $this->db->select("debit_account_id,credit_account_id")
									->where("ref_purchasepayments_id in ($ids)")
									->group_by("debit_account_id,credit_account_id")
									->get("ac_transactions");
		//ACCOUNT RESET END

      	##############################################START
		//FIND THE PREVIOUSE ITEM LIST ID'S
		$prev_item_ids = $this->db->select("item_id")->from("db_purchaseitems")->where("purchase_id in ($ids)")->get()->result_array();
		##############################################END

		$q8=$this->db->query("SELECT COUNT(*) AS tot_invoices,a.purchase_id,b.purchase_code FROM db_purchasereturn AS a INNER JOIN db_purchase AS b ON b.id=a.purchase_id WHERE purchase_id IN($ids) GROUP BY purchase_id");

		if($q8->num_rows()>0){
			$i=1;
			echo "Sorry! Records Not Deleted! Return Invoices Found Against Purchases:";
			foreach($q8->result() as $res){
				echo "<br>".$i++.".Return Invoice Against Purchase Id:".$res->purchase_code;
			}
			echo "<br>To Delete Purchase! You need to Delete Purchase Return Invoices!";
			exit();
		}
		
		#----------------------------------
		$this->db->where("id in ($ids)");
		//if not admin
		if(!is_admin()){
			$this->db->where("store_id",get_current_store_id());
		}

		$q3=$this->db->delete("db_purchase");
		#----------------------------------
		#----------------------------------
		$this->db->where("purchase_id in ($ids)");
		//if not admin
		if(!is_admin()){
			$this->db->where("store_id",get_current_store_id());
		}

		$q5=$this->db->delete("db_purchasepayments");
		#----------------------------------
		$this->db->where("purchase_id in ($ids)");
		//if not admin
		if(!is_admin()){
			$this->db->where("store_id",get_current_store_id());
		}

		$q7=$this->db->delete("db_purchaseitems");
		#----------------------------------

		$q6=$this->db->query("select id from db_items");
		if($q6->num_rows()>0){
			$this->load->model('pos_model');				
			foreach ($q6->result() as $res6) {
				$q6=$this->pos_model->update_items_quantity($res6->id);
				if(!$q6){
					return "failed";
				}
			}
		}

		##############################################START
		/*Update items in all warehouses of the item*/
		$q7=update_warehouse_items($prev_item_ids);
		if(!$q7){
			return "failed";
		}
		##############################################END
		
		$q2=$this->update_purchase_payment_status();

		//ACCOUNT RESET
        if($reset_accounts->num_rows()>0){
        	foreach ($reset_accounts->result() as $res1) {
        		if(!update_account_balance($res1->debit_account_id)){
					return 'failed';
				}

				if(!update_account_balance($res1->credit_account_id)){
					return 'failed';
				}

        	}
        }
        //ACCOUNT RESET END

		if($q3!=1 || $q5!=1 || $q2!=1)
		{
			$this->db->trans_rollback();
		    return "failed";
		}
		else{
			$this->db->trans_commit();
		        return "success";
		}
	}
	public function search_item($q){
		$json_array=array();
        $query1="select id,item_name from db_items where (upper(item_name) like upper('%$q%') or upper(item_code) like upper('%$q%')) and store_id=$store_id";

        $q1=$this->db->query($query1);
        if($q1->num_rows()>0){
            foreach ($q1->result() as $value) {
            	$json_array[]=['id'=>$value->id, 'text'=>$value->item_name];
            }
        }
        return json_encode($json_array);
	}
	
	public function find_item_details($id){
		$json_array=array();
        $query1="select id,item_name,tax_id,sales_price,price,stock,tax_type,profit_margin from db_items where id=$id";

        $q1=$this->db->query($query1);
        if($q1->num_rows()>0){
            foreach ($q1->result() as $value) {
            	$json_array=['id'=>$value->id, 
        			 'item_name'=>$value->item_name,
        			 'purchase_price'=>$value->price,
        			 'sales_price'=>$value->sales_price,
        			 'tax_id'=>$value->tax_id,
        			 'stock'=>$value->stock,
        			 'profit_margin'=>$value->profit_margin,
        			 'tax_type'=>$value->tax_type,
        			];
            }
        }
        return json_encode($json_array);
	}

	

	
	public function inclusive($price='',$tax_per){
		return $price/(($tax_per/100)+1)/10;
	}

	
	public function get_items_info($rowcount,$item_id){
		$res1=$this->db->select('*')->from('db_items')->where("id=$item_id")->get()->row();
		$q3=$this->db->query("select * from db_tax where id=".$res1->tax_id)->row();
		$item_tax_amt = ($res1->tax_type=='Inclusive') ? calculate_inclusive($res1->sales_price,$q3->tax) :calculate_exclusive($res1->sales_price,$q3->tax);

		$info = array(
							'item_id' 					=> $res1->id, 
							'description' 				=> '', 
							'item_name' 				=> $res1->item_name,
							'item_available_qty' 		=> $res1->stock,
							'item_price' 				=> $res1->price, 
							'item_purchase_price' 		=> $res1->price, 
							'item_tax_name' 			=> $q3->tax_name, 
							'item_purchase_qty' 		=> 1, 
							'item_tax_id' 				=> $res1->tax_id, 
							'item_tax' 					=> $q3->tax, 
							'item_tax_type' 			=> $res1->tax_type, 
							'item_tax_amt' 				=> $item_tax_amt, 
							'item_discount' 			=> 0, 
							'item_discount_type' 		=> 'Percentage', 
							'item_discount_input' 		=> 0, 
							'service_bit' 				=> $res1->service_bit, 
						);

		$this->return_row_with_data($rowcount,$info);
	}

	/* For Purchase Items List Retrieve*/
	public function return_purchase_list($purchase_id){
		$q1=$this->db->select('*')->from('db_purchaseitems')->where("purchase_id=$purchase_id")->get();
		$rowcount =1;
		foreach ($q1->result() as $res1) {
			$res2=$this->db->query("select * from db_items where id=".$res1->item_id)->row();
			$q3=$this->db->query("select * from db_tax where id=".$res1->tax_id)->row();
			
			
			$info = array(
							'item_id' 					=> $res1->item_id,
							'description' 				=> $res1->description,
							'item_name' 				=> $res2->item_name,
							'item_available_qty' 		=> $res2->stock,
							'item_price' 				=> $res2->price,
							'item_purchase_price' 		=> $res1->price_per_unit,
							'item_tax_name' 			=> $q3->tax_name,
							'item_purchase_qty' 		=> $res1->purchase_qty,
							'item_tax_id' 				=> $res1->tax_id,
							'item_tax' 					=> $q3->tax,
							'item_tax_type' 			=> $res1->tax_type,
							'item_tax_amt' 				=> $res1->tax_amt,
							'item_discount' 			=> $res1->discount_input,
							'item_discount_type' 		=> $res1->discount_type,
							'item_discount_input' 		=> $res1->discount_input,
							'service_bit' 				=> $res2->service_bit,
							'batch_lot' 				=> $res1->batch_lot,
							'barcode' 					=> $res1->barcode,
							'expire_date' 				=> $res1->expire_date,
							'mfg_date' 					=> $res1->mfg_date,
							'received_qty' 				=> $res1->received_qty,
						);

			$result = $this->return_row_with_data($rowcount++,$info);
		}
		return $result;
	}

	public function return_row_with_data($rowcount,$info){
		$item_id = isset($info['item_id']) ? $info['item_id'] : '';
		$description = isset($info['description']) ? $info['description'] : '';
		$item_name = isset($info['item_name']) ? $info['item_name'] : '';
		$item_available_qty = isset($info['item_available_qty']) ? $info['item_available_qty'] : '';
		$item_price = isset($info['item_price']) ? $info['item_price'] : '';
		$item_purchase_price = isset($info['item_purchase_price']) ? $info['item_purchase_price'] : '';
		$item_tax_name = isset($info['item_tax_name']) ? $info['item_tax_name'] : '';
		$item_purchase_qty = isset($info['item_purchase_qty']) ? $info['item_purchase_qty'] : '';
		$item_tax_id = isset($info['item_tax_id']) ? $info['item_tax_id'] : '';
		$item_tax = isset($info['item_tax']) ? $info['item_tax'] : '';
		$item_tax_type = isset($info['item_tax_type']) ? $info['item_tax_type'] : '';
		$item_tax_amt = isset($info['item_tax_amt']) ? $info['item_tax_amt'] : '';
		$item_discount = isset($info['item_discount']) ? $info['item_discount'] : '';
		$item_discount_type = isset($info['item_discount_type']) ? $info['item_discount_type'] : '';
		$item_discount_input = isset($info['item_discount_input']) ? $info['item_discount_input'] : '';
		$service_bit = isset($info['service_bit']) ? $info['service_bit'] : '';
		$batch_lot = isset($info['batch_lot']) ? $info['batch_lot'] : '';
		$barcode = isset($info['barcode']) ? $info['barcode'] : '';
		$expire_date = isset($info['expire_date']) ? $info['expire_date'] : '';
		$mfg_date = isset($info['mfg_date']) ? $info['mfg_date'] : '';
		$received_qty = isset($info['received_qty']) ? $info['received_qty'] : '';

		$item_unit_cost = $item_purchase_price+$item_tax_amt;
		$item_amount = $item_unit_cost * $item_purchase_qty;
	
		?>
            <!-- PURCHASE ITEM CARD -->
            <div class="purchase-item-card" id="row_<?=$rowcount;?>" data-row="<?=$rowcount;?>">
               <div class="card-main">
                  <div class="item-image-wrap"><i class="fa fa-cube item-icon"></i></div>
                  <div class="item-info">
                     <div class="item-name">
                        <a id="td_data_<?=$rowcount;?>_1" href="javascript:void()" onclick="show_purchase_item_modal(<?=$rowcount;?>)" title=""><?=$item_name;?></a>
                        <i onclick="show_purchase_item_modal(<?=$rowcount;?>)" class="fa fa-edit pointer"></i>
                     </div>
                     <div class="item-meta"><?=$item_tax_name;?></div>
                  </div>
                  <div class="item-total">
                     <small>Total</small>
                     <span id="td_data_<?=$rowcount;?>_9_display"><?=store_number_format($item_amount,0);?></span>
                  </div>
                  <div class="item-actions">
                     <a class="btn-delete fa fa-minus-square" onclick="removerow(<?=$rowcount;?>)" title="Delete" name="td_data_<?=$rowcount;?>_16" id="td_data_<?=$rowcount;?>_16"></a>
                  </div>
               </div>
               <div class="card-body">
                  <!-- Qty -->
                  <div class="field-group">
                     <label>Qty</label>
                     <div class="qty-control">
                        <button onclick="decrement_qty(<?=$rowcount;?>)" type="button" class="btn-qty"><i class="fa fa-minus text-danger"></i></button>
                        <input type="text" value="<?=format_qty($item_purchase_qty);?>" class="qty-input" onkeyup="calculate_tax(<?=$rowcount;?>)" id="td_data_<?=$rowcount;?>_3" name="td_data_<?=$rowcount;?>_3">
                        <button onclick="increment_qty(<?=$rowcount;?>)" type="button" class="btn-qty"><i class="fa fa-plus text-success"></i></button>
                     </div>
                  </div>
                  <!-- Purchase Price -->
                  <div class="field-group">
                     <label>Cost Price</label>
                     <input type="text" name="td_data_<?=$rowcount;?>_4" id="td_data_<?=$rowcount;?>_4" class="form-control only_currency" onkeyup="calculate_tax(<?=$rowcount;?>)" value="<?=store_number_format($item_purchase_price,0);?>">
                  </div>
                  <!-- Discount -->
                  <div class="field-group">
                     <label>Discount</label>
                     <input type="text" data-toggle="tooltip" title="Click to Change" name="td_data_<?=$rowcount;?>_8" id="td_data_<?=$rowcount;?>_8" class="form-control only_currency item_discount" value="<?=store_number_format($item_discount,0);?>" onclick="show_purchase_item_modal(<?=$rowcount;?>)" readonly>
                  </div>
                  <!-- Tax Amount -->
                  <div class="field-group">
                     <label>Tax</label>
                     <input type="text" name="td_data_<?=$rowcount;?>_5" id="td_data_<?=$rowcount;?>_5" class="form-control only_currency" readonly value="<?=store_number_format($item_tax_amt,0);?>">
                  </div>
                  <!-- Total -->
                  <div class="field-group">
                     <label>Total</label>
                     <input type="text" name="td_data_<?=$rowcount;?>_9" id="td_data_<?=$rowcount;?>_9" class="form-control only_currency" style="border-color:#f39c12;" title="Total" readonly value="<?=store_number_format($item_amount,0);?>">
                  </div>
               </div>
               <!-- Hidden inputs -->
               <input type="hidden" id="tr_available_qty_<?=$rowcount;?>_13" value="<?=$item_available_qty;?>">
               <input type="hidden" id="tr_item_id_<?=$rowcount;?>" name="tr_item_id_<?=$rowcount;?>" value="<?=$item_id;?>">
               <input type="hidden" id="tr_tax_type_<?=$rowcount;?>" name="tr_tax_type_<?=$rowcount;?>" value="<?=$item_tax_type;?>">
               <input type="hidden" id="tr_tax_id_<?=$rowcount;?>" name="tr_tax_id_<?=$rowcount;?>" value="<?=$item_tax_id;?>">
               <input type="hidden" id="tr_tax_value_<?=$rowcount;?>" name="tr_tax_value_<?=$rowcount;?>" value="<?=$item_tax;?>">
               <input type="hidden" id="description_<?=$rowcount;?>" name="description_<?=$rowcount;?>" value="<?=$description;?>">
               <input type="hidden" id="service_bit_<?=$rowcount;?>" name="service_bit_<?=$rowcount;?>" value="<?=$service_bit;?>">
               <input type="hidden" id="item_discount_type_<?=$rowcount;?>" name="item_discount_type_<?=$rowcount;?>" value="<?=$item_discount_type;?>">
               <input type="hidden" id="item_discount_input_<?=$rowcount;?>" name="item_discount_input_<?=$rowcount;?>" value="<?=store_number_format($item_discount_input,0);?>">
               <!-- Hidden fields for JS compatibility -->
               <input type="hidden" id="td_data_<?=$rowcount;?>_10" name="td_data_<?=$rowcount;?>_10" value="<?=store_number_format($item_unit_cost,0);?>">
               <span style="display:none;" id="td_data_<?=$rowcount;?>_15"><?=$item_tax_name;?></span>

               <button type="button" class="btn-expand"><i class="fa fa-chevron-down"></i> Additional Details</button>

               <!-- BATCH / RECEIPT ROW -->
               <div class="card-advanced" id="row_<?=$rowcount;?>_batch">
                  <div class="card-advanced-grid">
                     <div class="field-group">
                        <label>Batch/Lot</label>
                        <input type="text" name="batch_lot_<?=$rowcount;?>" id="batch_lot_<?=$rowcount;?>" class="form-control" value="<?=$batch_lot;?>" placeholder="Batch/Lot">
                     </div>
                     <div class="field-group">
                        <label>Barcode</label>
                        <input type="text" name="barcode_<?=$rowcount;?>" id="barcode_<?=$rowcount;?>" class="form-control" value="<?=$barcode;?>" placeholder="Barcode">
                     </div>
                     <div class="field-group">
                        <label>Rcv Qty</label>
                        <input type="text" name="received_qty_<?=$rowcount;?>" id="received_qty_<?=$rowcount;?>" class="form-control" value="<?=($received_qty!=='' && $received_qty!==null) ? format_qty($received_qty) : '';?>" placeholder="Received">
                     </div>
                     <div class="field-group">
                        <label>Expiry</label>
                        <input type="text" name="expire_date_<?=$rowcount;?>" id="expire_date_<?=$rowcount;?>" class="form-control datepicker" value="<?=(!empty($expire_date) && $expire_date!='0000-00-00') ? show_date($expire_date) : '';?>" placeholder="Expiry" readonly>
                     </div>
                     <div class="field-group">
                        <label>MFG Date</label>
                        <input type="text" name="mfg_date_<?=$rowcount;?>" id="mfg_date_<?=$rowcount;?>" class="form-control datepicker" value="<?=(!empty($mfg_date) && $mfg_date!='0000-00-00') ? show_date($mfg_date) : '';?>" placeholder="MFG Date" readonly>
                     </div>
                  </div>
               </div>
            </div>
		<?php

	}

	public function show_pay_now_modal($purchase_id){
		$q1=$this->db->query("select * from db_purchase where id=$purchase_id");
		$res1=$q1->row();
		$supplier_id = $res1->supplier_id;
		$q2=$this->db->query("select * from db_suppliers where id=$supplier_id");
		$res2=$q2->row();

		$supplier_name=$res2->supplier_name;
	    $supplier_mobile=$res2->mobile;
	    $supplier_phone=$res2->phone;
	    $supplier_email=$res2->email;
	    $supplier_state=$res2->state_id;
	    $supplier_address=$res2->address;
	    $supplier_postcode=$res2->postcode;
	    $supplier_gst_no=$res2->gstin;
	    $supplier_tax_number=$res2->tax_number;
	    $supplier_opening_balance=$res2->opening_balance;

	    $purchase_date=$res1->purchase_date;
	    $reference_no=$res1->reference_no;
	    $purchase_code=$res1->purchase_code;
	    $purchase_note=$res1->purchase_note;
	    $grand_total=$res1->grand_total;
	    $paid_amount=$res1->paid_amount;
	    $due_amount =$grand_total - $paid_amount;

	    $supplier_country = $this->db->query("select country from db_country where id=".$res2->country_id)->row()->country;
	    if(!empty($supplier_state)){
    		$supplier_state = $this->db->query("select state from db_states where id=".$res2->state_id)->row()->state;
    	}


		?>
		<div class="modal fade" id="pay_now">
		  <div class="modal-dialog ">
		    <div class="modal-content">
		      <div class="modal-header header-custom">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title text-center"><?= $this->lang->line('payments'); ?></h4>
		      </div>
		      <div class="modal-body">
		        
		    <div class="row">
		      <div class="col-md-12">
		      	<div class="row invoice-info">
			        <div class="col-sm-4 invoice-col">
			          <?= $this->lang->line('supplier_details'); ?>
			          <address>
			            <strong><?php echo  $supplier_name; ?></strong><br>
			            <?php echo (!empty(trim($supplier_mobile))) ? $this->lang->line('mobile').": ".$supplier_mobile."<br>" : '';?>
			            <?php echo (!empty(trim($supplier_phone))) ? $this->lang->line('phone').": ".$supplier_phone."<br>" : '';?>
			            <?php echo (!empty(trim($supplier_email))) ? $this->lang->line('email').": ".$supplier_email."<br>" : '';?>
			            <?php echo (!empty(trim($supplier_gst_no))) ? $this->lang->line('gst_number').": ".$supplier_gst_no."<br>" : '';?>
			            <?php echo (!empty(trim($supplier_tax_number))) ? $this->lang->line('tax_number').": ".$supplier_tax_number."<br>" : '';?>
			          </address>
			        </div>
			        <!-- /.col -->
			        <div class="col-sm-4 invoice-col">
			          <?= $this->lang->line('purchase_details'); ?>:
			          <address>
			            <b><?= $this->lang->line('invoice'); ?> #<?php echo  $purchase_code; ?></b><br>
			            <b><?= $this->lang->line('date'); ?> :<?php echo  show_date($purchase_date); ?></b><br>
			            <b><?= $this->lang->line('grand_total'); ?> :<?php echo $grand_total; ?></b><br>
			          </address>
			        </div>
			        <!-- /.col -->
			        <div class="col-sm-4 invoice-col">
			          <b><?= $this->lang->line('paid_amount'); ?> :<span><?php echo number_format($paid_amount,decimals(),'.',''); ?></span></b><br>
			          <b><?= $this->lang->line('due_amount'); ?> :<span id='due_amount_temp'><?php echo number_format($due_amount,decimals(),'.',''); ?></span></b><br>
			         
			        </div>
			        <!-- /.col -->
			      </div>
			      <!-- /.row -->
		      </div>
		      <div class="col-md-12">
		        <div>
		        <input type="hidden" name="payment_row_count" id='payment_row_count' value="1">
		        <div class="col-md-12  payments_div">
		          <div class="box box-solid bg-gray">
		            <div class="box-body">
		              <div class="row">
		         		<div class="col-md-6">
		                  <div class="">
		                  <label for="payment_date"><?= $this->lang->line('date'); ?></label>
		                    <div class="input-group date">
			                      <div class="input-group-addon">
			                      <i class="fa fa-calendar"></i>
			                      </div>
			                      <input type="text" class="form-control pull-right datepicker" value="<?= show_date(date("d-m-Y")); ?>" id="payment_date" name="payment_date" readonly>
			                    </div>
		                      <span id="payment_date_msg" style="display:none" class="text-danger"></span>
		                </div>
		               </div>
		                <div class="col-md-6">
		                  <div class="">
		                  <label for="amount"><?= $this->lang->line('amount'); ?></label>
		                    <input type="text" class="form-control text-right paid_amt" id="amount" name="amount" placeholder="" value="<?=$due_amount;?>" onkeyup="calculate_payments()">
		                      <span id="amount_msg" style="display:none" class="text-danger"></span>
		                </div>
		               </div>
		                <div class="col-md-6">
		                  <div class="">
		                    <label for="payment_type"><?= $this->lang->line('payment_type'); ?></label>
		                    <select class="form-control" id='payment_type' name="payment_type">
		                      <?php
		                        $q1=$this->db->query("select * from db_paymenttypes where status=1 and store_id=".get_current_store_id());
		                         if($q1->num_rows()>0){
		                             foreach($q1->result() as $res1){
		                             echo "<option value='".$res1->payment_type."'>".$res1->payment_type ."</option>";
		                           }
		                         }
		                         else{
		                            echo "No Records Found";
		                         }
		                        ?>
		                    </select>
		                    <span id="payment_type_msg" style="display:none" class="text-danger"></span>
		                  </div>
		                </div>
		                <div class="col-md-6">
		                  <div class="">
		                    <label for="account_id"><?= $this->lang->line('account'); ?></label>
		                    <select class="form-control" id='account_id' name="account_id">
		                      <?php
                                echo '<option value="">-None-</option>'; 
                                echo get_accounts_select_list();
                                ?>
		                    </select>
		                    <span id="account_id_msg" style="display:none" class="text-danger"></span>
		                  </div>
		                </div>
		            <div class="clearfix"></div>
		        </div>  
		        <div class="row">
		               <div class="col-md-12">
		                  <div class="">
		                    <label for="payment_note"><?= $this->lang->line('payment_note'); ?></label>
		                    <textarea type="text" class="form-control" id="payment_note" name="payment_note" placeholder="" ></textarea>
		                    <span id="payment_note_msg" style="display:none" class="text-danger"></span>
		                  </div>
		               </div>
		                
		            <div class="clearfix"></div>
		        </div>   
		        </div>
		        </div>
		      </div><!-- col-md-12 -->
		    </div>
		      </div><!-- col-md-9 -->
		      <!-- RIGHT HAND -->
		    </div>
		      </div>
		      <div class="modal-footer">
		      	<input type="hidden" id="supplier_id" value="<?=$supplier_id?>">
		        <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">Close</button>
		        <button type="button" onclick="save_payment(<?=$purchase_id;?>)" class="btn bg-green btn-lg place_order btn-lg payment_save">Save<i class="fa  fa-check "></i></button>
		      </div>
		    </div>
		    <!-- /.modal-content -->
		  </div>
		  <!-- /.modal-dialog -->
		</div>
		<?php
	}

	public function save_payment(){
		$amount = $this->input->post('amount', TRUE);
		$payment_type = $this->input->post('payment_type', TRUE);
		$payment_date = $this->input->post('payment_date', TRUE);
		$payment_note = $this->input->post('payment_note', TRUE);
		$purchase_id = $this->input->post('purchase_id', TRUE);
		$supplier_id = $this->input->post('supplier_id', TRUE);
		$account_id = $this->input->post('account_id', TRUE);
		//print_r($this->xss_html_filter(array_merge($this->data,$_POST,$_GET)));exit();
    	if($amount=='' || $amount==0){$amount=null;}
		if($amount>0 && !empty($payment_type)){

			$payment_code=get_init_code('purchase_payment');

			$purchasepayments_entry = array(
					'payment_code' 		=> $payment_code,
		    		'count_id'	  		=> get_count_id('db_purchasepayments'),
					'purchase_id' 		=> $purchase_id, 
					'payment_date'		=> system_fromatted_date($payment_date),//Current Payment with Purchase entry
					'payment_type' 		=> $payment_type,
					'payment' 			=> $amount,
					'payment_note' 		=> $payment_note,
					'created_date' 		=> $CUR_DATE,
    				'created_time' 		=> $CUR_TIME,
    				'created_by' 		=> $CUR_USERNAME,
    				'system_ip' 		=> $SYSTEM_IP,
    				'system_name' 		=> $SYSTEM_NAME,
    				'status' 			=> 1,
    				'account_id' 		=> (empty($account_id)) ? null : $account_id,
    				'supplier_id' 		=> $supplier_id,
				);
			$purchasepayments_entry['store_id']=$this->db->select("store_id")->where('id',$purchase_id)->get('db_purchase')->row()->store_id;
			$q3 = $this->db->insert('db_purchasepayments', $purchasepayments_entry);
			//Set the payment to specified account
			if(!empty($account_id)){
				//ACCOUNT INSERT
				$insert_bit = insert_account_transaction(array(
															'transaction_type'  	=> 'PURCHASE PAYMENT',
															'reference_table_id'  	=> $this->db->insert_id(),
															'debit_account_id'  	=> $account_id,
															'credit_account_id'  	=> null,
															'debit_amt'  			=> $amount,
															'credit_amt'  			=> 0,
															'process'  				=> 'SAVE',
															'note'  				=> $payment_note,
															'transaction_date'  	=> $CUR_DATE,
															'payment_code'  		=> $payment_code,
															'customer_id'  			=> null,
															'supplier_id'  			=> $supplier_id,
													));
				if(!$insert_bit){
					return "failed";
				}
			}
			//end
		}
		else{
			return "Please Enter Valid Amount!";
		}
		
		$q10=$this->update_purchase_payment_status($purchase_id);
		if($q10!=1){
			return "failed";
		}
		return "success";

	}
	
	public function view_payments_modal($purchase_id){
		$q1=$this->db->query("select * from db_purchase where id=$purchase_id");
		$res1=$q1->row();
		$supplier_id = $res1->supplier_id;
		$q2=$this->db->query("select * from db_suppliers where id=$supplier_id");
		$res2=$q2->row();

		$supplier_name=$res2->supplier_name;
	    $supplier_mobile=$res2->mobile;
	    $supplier_phone=$res2->phone;
	    $supplier_email=$res2->email;
	    $supplier_state=$res2->state_id;
	    $supplier_address=$res2->address;
	    $supplier_postcode=$res2->postcode;
	    $supplier_gst_no=$res2->gstin;
	    $supplier_tax_number=$res2->tax_number;
	    $supplier_opening_balance=$res2->opening_balance;

	    $purchase_date=$res1->purchase_date;
	    $reference_no=$res1->reference_no;
	    $purchase_code=$res1->purchase_code;
	    $purchase_note=$res1->purchase_note;
	    $grand_total=$res1->grand_total;
	    $paid_amount=$res1->paid_amount;
	    $due_amount =$grand_total - $paid_amount;

	    $supplier_country = $this->db->query("select country from db_country where id=".$res2->country_id)->row()->country;
	    $supplier_state=(!empty($supplier_state)) ? $this->db->query("select state from db_states where id=".$res2->state_id)->row()->state : '';
	    

		?>
		<div class="modal fade" id="view_payments_modal">
		  <div class="modal-dialog modal-lg">
		    <div class="modal-content">
		      <div class="modal-header header-custom">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title text-center"><?= $this->lang->line('payments'); ?></h4>
		      </div>
		      <div class="modal-body">
		        
		    <div class="row">
		      <div class="col-md-12">
		      	<div class="row invoice-info">
			        <div class="col-sm-4 invoice-col">
			          <?= $this->lang->line('supplier_details'); ?>
			          <address>
			            <strong><?php echo  $supplier_name; ?></strong><br>
			            <?php echo (!empty(trim($supplier_mobile))) ? $this->lang->line('mobile').": ".$supplier_mobile."<br>" : '';?>
			            <?php echo (!empty(trim($supplier_phone))) ? $this->lang->line('phone').": ".$supplier_phone."<br>" : '';?>
			            <?php echo (!empty(trim($supplier_email))) ? $this->lang->line('email').": ".$supplier_email."<br>" : '';?>
			            <?php echo (!empty(trim($supplier_gst_no))) ? $this->lang->line('gst_number').": ".$supplier_gst_no."<br>" : '';?>
			            <?php echo (!empty(trim($supplier_tax_number))) ? $this->lang->line('tax_number').": ".$supplier_tax_number."<br>" : '';?>
			          </address>
			        </div>
			        <!-- /.col -->
			        <div class="col-sm-4 invoice-col">
			         <?= $this->lang->line('purchase_details'); ?>:
			          <address>
			            <b><?= $this->lang->line('invoice'); ?> #<?php echo  $purchase_code; ?></b><br>
			            <b><?= $this->lang->line('date'); ?> :<?=  show_date($purchase_date); ?></b><br>
			            <b><?= $this->lang->line('grand_total'); ?> :<?php echo $grand_total; ?></b><br>
			          </address>
			        </div>
			        <!-- /.col -->
			        <div class="col-sm-4 invoice-col">
			          <b><?= $this->lang->line('paid_amount'); ?> :<span><?php echo number_format($paid_amount,decimals(),'.',''); ?></span></b><br>
			          <b><?= $this->lang->line('due_amount'); ?> :<span id='due_amount_temp'><?php echo number_format($due_amount,decimals(),'.',''); ?></span></b><br>
			         
			        </div>
			        <!-- /.col -->
			      </div>
			      <!-- /.row -->
		      </div>
		      <div class="col-md-12">
		       
		     
		              <div class="row">
		         		<div class="col-md-12">
		                  
		                      <table class="table table-bordered">
                                  <thead>
                                  <tr class="bg-primary">
                                    <th>#</th>
                                    <th><?= $this->lang->line('payment_date'); ?></th>
                                    <th><?= $this->lang->line('payment'); ?></th>
                                    <th><?= $this->lang->line('payment_type'); ?></th>
                                    <th><?= $this->lang->line('account'); ?></th>
                                    <th><?= $this->lang->line('payment_note'); ?></th>
                                    <th><?= $this->lang->line('created_by'); ?></th>
                                    <th><?= $this->lang->line('action'); ?></th>
                                  </tr>
                                </thead>
                                <tbody>
                                	<?php
                                	$q1=$this->db->query("select * from db_purchasepayments where purchase_id=$purchase_id");
									$i=1;
									$str = '';
									if($q1->num_rows()>0){
										foreach ($q1->result() as $res1) {
											echo "<tr>";
											echo "<td>".$i++."</td>";
											echo "<td>".show_date($res1->payment_date)."</td>";
											echo "<td>".$res1->payment."</td>";
											echo "<td>".$res1->payment_type."</td>";
											echo "<td>".get_account_name($res1->account_id)."</td>";
											echo "<td>".$res1->payment_note."</td>";
											echo "<td>".ucfirst($res1->created_by)."</td>";
										
											echo "<td><a onclick='delete_purchase_payment(".$res1->id.")' class='pointer btn  btn-danger' ><i class='fa fa-trash'></i></</td>";	
											echo "</tr>";
										}
									}
									else{
										echo "<tr><td colspan='7' class='text-danger text-center'>No Records Found</td></tr>";
									}
									?>
                                </tbody>
                            </table>
		               
		               </div>
		            <div class="clearfix"></div>
		        </div>    
		       
		     
		   
		      </div><!-- col-md-9 -->
		      <!-- RIGHT HAND -->
		    </div>
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">Close</button>
		        
		      </div>
		    </div>
		    <!-- /.modal-content -->
		  </div>
		  <!-- /.modal-dialog -->
		</div>
		<?php
	}

	public function show_change_status_modal($purchase_id){
		$q1=$this->db->query("select * from db_purchase where id=$purchase_id");
		$res1=$q1->row();
		$current_status = $res1->purchase_status;
		$purchase_code = $res1->purchase_code;

		$q2=$this->db->query("select * from db_purchaseitems where purchase_id=$purchase_id");
		?>
		<div class="modal fade" id="change_status_modal">
		  <div class="modal-dialog modal-lg">
		    <div class="modal-content">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title">Change Purchase Status — <?= $purchase_code; ?></h4>
		      </div>
		      <div class="modal-body">
		        <form id="change-status-form">
		        <input type="hidden" name="purchase_id" id="cs_purchase_id" value="<?= $purchase_id; ?>">
		        <div class="row">
		          <div class="col-md-6">
		            <div class="form-group">
		              <label>Current Status</label>
		              <input type="text" class="form-control" value="<?= $current_status; ?>" readonly>
		            </div>
		          </div>
		          <div class="col-md-6">
		            <div class="form-group">
		              <label>New Status <span class="text-danger">*</span></label>
		              <select class="form-control" name="new_status" id="cs_new_status" onchange="toggle_cs_batch_fields()">
		                <option value="Draft">Draft (Requisition)</option>
		                <option value="Ordered">Ordered (PO)</option>
		                <option value="Partially Received">Partially Received</option>
		                <option value="Received">Received & Completed</option>
		              </select>
		            </div>
		          </div>
		        </div>

		        <div id="cs_batch_section" style="display:none;">
		          <hr>
		          <h4 class="text-orange"><i class="fa fa-barcode"></i> Enter Receipt / Batch Details</h4>
		          <div class="table-responsive">
		            <table class="table table-bordered table-hover">
		              <thead class="bg-primary">
		                <tr>
		                  <th>Item</th>
		                  <th style="width:12%">Ordered Qty</th>
		                  <th style="width:15%">Received Qty</th>
		                  <th style="width:15%">Batch/Lot</th>
		                  <th style="width:15%">Barcode</th>
		                  <th style="width:12%">Expiry</th>
		                  <th style="width:12%">MFG Date</th>
		                </tr>
		              </thead>
		              <tbody>
		                <?php foreach($q2->result() as $res2):
		                  $item_name = $this->db->query("select item_name from db_items where id=".$res2->item_id)->row()->item_name;
		                ?>
		                <tr>
		                  <td>
		                    <?= $item_name; ?>
		                    <input type="hidden" name="cs_item_id[]" value="<?= $res2->item_id; ?>">
		                    <input type="hidden" name="cs_item_row_id[]" value="<?= $res2->id; ?>">
		                  </td>
		                  <td><?= format_qty($res2->purchase_qty); ?></td>
		                  <td>
		                    <input type="text" name="cs_received_qty[]" class="form-control text-center cs-received-qty" value="<?= ($res2->received_qty!==null && $res2->received_qty!=='') ? format_qty($res2->received_qty) : format_qty($res2->purchase_qty); ?>">
		                  </td>
		                  <td>
		                    <input type="text" name="cs_batch_lot[]" class="form-control text-center" value="<?= $res2->batch_lot; ?>" placeholder="Batch/Lot">
		                  </td>
		                  <td>
		                    <input type="text" name="cs_barcode[]" class="form-control text-center" value="<?= $res2->barcode; ?>" placeholder="Barcode">
		                  </td>
		                  <td>
		                    <input type="text" name="cs_expire_date[]" class="form-control text-center datepicker" value="<?= (!empty($res2->expire_date) && $res2->expire_date!='0000-00-00') ? show_date($res2->expire_date) : ''; ?>" placeholder="dd-mm-yyyy" readonly>
		                  </td>
		                  <td>
		                    <input type="text" name="cs_mfg_date[]" class="form-control text-center datepicker" value="<?= (!empty($res2->mfg_date) && $res2->mfg_date!='0000-00-00') ? show_date($res2->mfg_date) : ''; ?>" placeholder="dd-mm-yyyy" readonly>
		                  </td>
		                </tr>
		                <?php endforeach; ?>
		              </tbody>
		            </table>
		          </div>
		        </div>
		        </form>
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
		        <button type="button" class="btn btn-primary" id="btn_change_status_save">Update Status</button>
		      </div>
		    </div>
		  </div>
		</div>
		<script>
		function toggle_cs_batch_fields(){
		  var status = $("#cs_new_status").val();
		  if(status == 'Partially Received' || status == 'Received'){
		    $("#cs_batch_section").show();
		    if(status == 'Received'){
		      $(".cs-received-qty").each(function(){
		        $(this).prop('readonly', true);
		      });
		    } else {
		      $(".cs-received-qty").each(function(){
		        $(this).prop('readonly', false);
		      });
		    }
		  } else {
		    $("#cs_batch_section").hide();
		  }
		}
		// Initialize datepickers inside modal
		$('#change_status_modal .datepicker').datepicker({
		    autoclose: true,
		    format: 'dd-mm-yyyy',
		    todayHighlight: true
		});
		</script>
		<?php
	}

	public function change_status(){
		$purchase_id = $this->input->post('purchase_id', TRUE);
		$new_status = $this->input->post('new_status', TRUE);
		$item_row_ids = $this->input->post('cs_item_row_id', TRUE);
		$received_qtys = $this->input->post('cs_received_qty', TRUE);
		$batch_lots = $this->input->post('cs_batch_lot', TRUE);
		$barcodes = $this->input->post('cs_barcode', TRUE);
		$expire_dates = $this->input->post('cs_expire_date', TRUE);
		$mfg_dates = $this->input->post('cs_mfg_date', TRUE);

		$this->db->trans_begin();

		// Update purchase status
		$q1 = $this->db->query("update db_purchase set purchase_status='$new_status' where id=$purchase_id");
		if(!$q1){ $this->db->trans_rollback(); return "failed"; }

		// Update items if status is Partially Received or Received
		if($new_status == 'Partially Received' || $new_status == 'Received'){
			for($i=0; $i<count($item_row_ids); $i++){
				$row_id = $item_row_ids[$i];
				$item_id = $this->db->query("select item_id from db_purchaseitems where id=$row_id").row()->item_id;
				$purchase_qty = $this->db->query("select purchase_qty from db_purchaseitems where id=$row_id").row()->purchase_qty;

				$rcv_qty = (!empty($received_qtys[$i]) && is_numeric($received_qtys[$i])) ? $received_qtys[$i] : 0;
				if($new_status == 'Received'){
					$rcv_qty = $purchase_qty;
				}

				$exp_date = (!empty($expire_dates[$i])) ? system_fromatted_date($expire_dates[$i]) : null;
				$mfg_date_val = (!empty($mfg_dates[$i])) ? system_fromatted_date($mfg_dates[$i]) : null;
				$batch_lot_val = (!empty($batch_lots[$i])) ? $batch_lots[$i] : null;
				$barcode_val = (!empty($barcodes[$i])) ? $barcodes[$i] : null;

				$q2 = $this->db->query("update db_purchaseitems set
									purchase_status='$new_status',
									received_qty=$rcv_qty,
									batch_lot=".($batch_lot_val ? "'$batch_lot_val'" : "null").",
									barcode=".($barcode_val ? "'$barcode_val'" : "null").",
									expire_date=".($exp_date ? "'$exp_date'" : "null").",
									mfg_date=".($mfg_date_val ? "'$mfg_date_val'" : "null")."
									where id=$row_id");
				if(!$q2){ $this->db->trans_rollback(); return "failed"; }

				// Update stock
				$this->load->model('pos_model');
				$q3 = $this->pos_model->update_items_quantity($item_id);
				if(!$q3){ $this->db->trans_rollback(); return "failed"; }

				// Create/update barcode record
				if(!empty($barcode_val) && !empty($rcv_qty) && $rcv_qty > 0){
					$this->db->where('item_id',$item_id)
							 ->where('barcode',$barcode_val)
							 ->where('batch_lot',$batch_lot_val)
							 ->where('store_id',get_current_store_id());
					$barcode_exists = $this->db->get('db_item_barcodes')->row();
					if(!empty($barcode_exists)){
						$new_barcode_qty = $barcode_exists->qty + $rcv_qty;
						$this->db->where('id',$barcode_exists->id)->update('db_item_barcodes', array(
							'qty' => $new_barcode_qty,
							'expire_date' => $exp_date,
							'mfg_date' => $mfg_date_val
						));
					} else {
						$this->db->insert('db_item_barcodes', array(
							'item_id' => $item_id,
							'barcode' => $barcode_val,
							'batch_lot' => $batch_lot_val,
							'qty' => $rcv_qty,
							'expire_date' => $exp_date,
							'mfg_date' => $mfg_date_val,
							'store_id' => get_current_store_id(),
							'status' => 1
						));
					}
				}
			}
		} else {
			// For Draft/Ordered: just update purchase_status on items, clear received_qty
			$q4 = $this->db->query("update db_purchaseitems set purchase_status='$new_status', received_qty=null where purchase_id=$purchase_id");
			if(!$q4){ $this->db->trans_rollback(); return "failed"; }
			// Re-calc stock since we may have removed received quantities
			$item_ids = $this->db->query("select item_id from db_purchaseitems where purchase_id=$purchase_id").result_array();
			foreach($item_ids as $itm){
				$this->load->model('pos_model');
				$this->pos_model->update_items_quantity($itm['item_id']);
			}
		}

		// Update supplier due
		$q5=$this->update_purchase_payment_status_by_purchase_id($purchase_id);
		if(!$q5){ $this->db->trans_rollback(); return "failed"; }

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return "failed";
		}
		$this->db->trans_commit();
		$this->session->set_flashdata('success', 'Status updated successfully!');
		return "success";
	}
}
