<?php
 function get_accounts_select_list($select_id='',$parent_id=0){
 	  $CI =& get_instance();

	 
	   $CI->db->where("store_id",get_current_store_id());

	  $CI->db->select("*")->where("status=1")->from("ac_accounts");
	  $CI->db->order_by("sort_code");
	  $q1=$CI->db->get();

	  $str='';
	   if($q1->num_rows($q1)>0)
	    {  
	    	//$str.='<option value="">-Select-</option>'; 
	        foreach($q1->result() as $res1)
	      { 
	        $selected = ($select_id==$res1->id)? 'selected' : '';
	        
        		$str.= ($res1->parent_id==0) ? '<optgroup class="bg-yellow" label="'.$res1->account_name.'">' : '';
        	$str.="<option $selected data-account-name='".$res1->account_name."' value='".$res1->id."'>";
        			$str.=add_dash($res1->account_name,$res1->parent_id,$res1->sort_code);
        		
        	$str.="</option>";	
        		$str.= ($res1->parent_id==0) ? '</optgroup>' : '';
	        
	      //  echo get_accounts_select_list_sub(null,$res1->id);
	      }
	    }
	    else
	    {
	    	//$str.='<option value="">No Records Found</option>'; 
	    }
	    return $str;
 }

 function add_dash($value,$parent_id,$sort_code){
 	if($parent_id==0){
 		return $value;
 	}
 	else{
 		$dash='';
 		$count = count(explode(".", $sort_code));
 		for ($i=0; $i < $count-2; $i++) { 
 			$dash .= "&nbsp;&nbsp;&nbsp;";
 		}
 		return $dash."--".$value;
 	}
 	
 }


function insert_account_transaction($data=array()){
	
		$transaction_type 			= (empty($data['transaction_type'])) ? '' : $data['transaction_type'];
		$reference_table_id			= $data['reference_table_id'];
		$debit_account_id 			= (empty($data['debit_account_id'])) ? NULL : $data['debit_account_id'];
		$credit_account_id 			= (empty($data['credit_account_id'])) ? NULL : $data['credit_account_id'];
		$debit_amt 					= (empty($data['debit_amt'])) ? 0 : $data['debit_amt'];
		$credit_amt 				= (empty($data['credit_amt'])) ? 0 : $data['credit_amt'];
		$process 					= (empty($data['process'])) ? 'SAVE' : $data['process'];
		$note 						= (empty($data['note'])) ? '' : $data['note'];
		$transaction_date			= $data['transaction_date'];
		$payment_code 				= (empty($data['payment_code'])) ? '' : $data['payment_code'];
		$customer_id 				= (empty($data['customer_id'])) ? NULL : $data['customer_id'];
		$supplier_id 				= (empty($data['supplier_id'])) ? NULL : $data['supplier_id'];
	

	$CI =& get_instance();

	$transaction = array();
	if($transaction_type=='EXPENSE PAYMENT'){
		if($process=='UPDATE'){
			//delete previouse data of the transactions
			$CI->db->where("ref_expense_id",$reference_table_id)->delete("ac_transactions");
		}
		$transaction = array( 
								"transaction_type" 		=> $transaction_type,
								"ref_expense_id" 	=> $reference_table_id,
								"debit_account_id" 		=> $debit_account_id,
								"debit_amt"		 		=> $debit_amt,
							);
	}
	else if($transaction_type=='PURCHASE PAYMENT RETURN'){
		$transaction = array( 
								"transaction_type" 		=> $transaction_type,
								"ref_purchasepaymentsreturn_id" 	=> $reference_table_id,
								"credit_account_id" 	=> $credit_account_id,
								"credit_amt"		 	=> $credit_amt,
							);
	}
	else if($transaction_type=='PURCHASE PAYMENT'){
		$transaction = array( 
								"transaction_type" 		=> $transaction_type,
								"ref_purchasepayments_id" 	=> $reference_table_id,
								"debit_account_id" 		=> $debit_account_id,
								"debit_amt"		 		=> $debit_amt,
							);
	}
	else if($transaction_type=='SALES PAYMENT RETURN'){
		$transaction = array( 
								"transaction_type" 		=> $transaction_type,
								"ref_salespaymentsreturn_id" 	=> $reference_table_id,
								"debit_account_id" 		=> $debit_account_id,
								"debit_amt"		 		=> $debit_amt,
							);
	}
	else if($transaction_type=='SALES PAYMENT' || $transaction_type=='SALES PAYMENT & OB'){
		//CUSTOMER BULK PAYMENT INCLUDES OB PAYMENT
		$transaction = array( 
								"transaction_type" 		=> $transaction_type,
								"ref_salespayments_id" 	=> $reference_table_id,
								"credit_account_id" 	=> $credit_account_id,
								"credit_amt"		 	=> $credit_amt,
							);
		
		
	}
	else if($transaction_type=='OPENING BALANCE PAID' && !empty($supplier_id)){
		$transaction = array( 
								"transaction_type" 		=> $transaction_type,
								"ref_purchasepayments_id" 		=> $reference_table_id,
								"debit_account_id" 		=> $debit_account_id,
								"debit_amt"		 		=> $debit_amt,
							);
	}
	
	else if($transaction_type=='OPENING BALANCE PAID' && !empty($customer_id)){
		//SALES PAYMENTS
		$transaction = array( 
								"transaction_type" 		=> $transaction_type,
								"ref_salespayments_id" 		=> $reference_table_id,
								"credit_account_id" 	=> $credit_account_id,
								"credit_amt"		 	=> $credit_amt,
							);
	}
	
	else if($transaction_type=='OPENING BALANCE' && empty($customer_id) && empty($supplier_id)){
		//WHILE CREATING ACCOUNT
		$transaction = array( 
								"transaction_type" 		=> $transaction_type,
								"ref_accounts_id" 		=> $reference_table_id,
								"credit_account_id" 	=> $credit_account_id,
								"credit_amt"		 	=> $credit_amt,
							);
	}
	
	else if($transaction_type=='DEPOSIT'){
		if($process=='UPDATE'){
			//delete previouse data of the transactions
			$CI->db->where("ref_moneydeposits_id",$reference_table_id)->delete("ac_transactions");
		}
		$transaction = array( 
								"transaction_type" 		=> $transaction_type,
								"ref_moneydeposits_id" 	=> $reference_table_id,
								"debit_account_id" 		=> $debit_account_id,
								"credit_account_id" 	=> $credit_account_id,
								"debit_amt"		 		=> $debit_amt,
								"credit_amt"		 	=> $credit_amt,
							);
	}
	else if($transaction_type=='TRANSFER'){
		if($process=='UPDATE'){
			//delete previouse data of the transactions
			$CI->db->where("ref_moneytransfer_id",$reference_table_id)->delete("ac_transactions");
		}
		$transaction = array( 
								"transaction_type" 		=> $transaction_type,
								"ref_moneytransfer_id" 	=> $reference_table_id,
								"debit_account_id" 		=> $debit_account_id,
								"credit_account_id" 	=> $credit_account_id,
								"debit_amt"		 		=> $debit_amt,
								"credit_amt"		 	=> $credit_amt,
							);
	}
	else{
		//"Invalid Transaction Type";
		return false;
	}

	$transaction['store_id'] = get_current_store_id();
	$transaction['created_by'] = $CI->session->userdata('inv_username');
	$transaction['created_date'] = date("Y-m-d");
	$transaction['transaction_date'] = $transaction_date;
	$transaction['note'] = $note;
	$transaction['payment_code'] = $payment_code;
	$transaction['customer_id'] = $customer_id;
	$transaction['supplier_id'] = $supplier_id;

	if($CI->db->insert("ac_transactions",$transaction)){

		if(!empty($debit_account_id)){
			if(!update_account_balance($debit_account_id)){
				return false;
			}
		}

		if(!empty($credit_account_id)){
			if(!update_account_balance($credit_account_id)){
				return false;
			}
		}


		return true;
	}
	return false;
}



function get_cash_account_id(){
	$CI =& get_instance();
	$store_id = get_current_store_id();

	// 1. Try store setting if column exists
	$cols = $CI->db->query("SHOW COLUMNS FROM db_store LIKE 'cash_account_id'")->result();
	if(count($cols) > 0){
		$store = $CI->db->select('cash_account_id')->where('id', $store_id)->get('db_store')->row();
		if($store && !empty($store->cash_account_id)){
			return $store->cash_account_id;
		}
	}

	// 2. Find existing Cash Account by name (case-insensitive via query)
	$q1 = $CI->db->query("SELECT id FROM ac_accounts WHERE store_id = ? AND status = 1 AND (LOWER(account_name) LIKE '%cash%' OR LOWER(account_name) LIKE '%till%') LIMIT 1", array($store_id));
	if($q1->num_rows() > 0){
		$cash_id = $q1->row()->id;
		auto_link_existing_cash($cash_id);
		return $cash_id;
	}

	// 3. Create Cash Account if not found
	$max_sort = $CI->db->query("SELECT COALESCE(MAX(sort_code),0)+1 as next_sort FROM ac_accounts WHERE store_id = ?", array($store_id))->row()->next_sort;
	$insert = array(
		'count_id' => $max_sort,
		'store_id' => $store_id,
		'sort_code' => $max_sort,
		'account_code' => 'CASH',
		'parent_id' => 0,
		'account_name' => 'Cash Account',
		'note' => 'Default cash/till account',
		'created_date' => date('Y-m-d'),
		'created_time' => date('H:i:s'),
		'created_by' => $CI->session->userdata('inv_username') ?: 'System',
		'system_ip' => $CI->input->ip_address(),
		'system_name' => 'System',
		'status' => 1,
	);
	$CI->db->insert('ac_accounts', $insert);
	$cash_account_id = $CI->db->insert_id();

	// 4. Auto-link all existing cash transactions to this new Cash Account
	auto_link_existing_cash($cash_account_id);

	return $cash_account_id;
}

function auto_link_existing_cash($cash_account_id){
	$CI =& get_instance();
	$store_id = get_current_store_id();
	if(empty($cash_account_id) || empty($store_id)) return;

	// Update payment tables
	$CI->db->query("UPDATE db_salespayments SET account_id = ? WHERE store_id = ? AND payment_type = 'cash' AND (account_id IS NULL OR account_id = '' OR account_id = 0)", array($cash_account_id, $store_id));
	$CI->db->query("UPDATE db_purchasepayments SET account_id = ? WHERE store_id = ? AND payment_type = 'cash' AND (account_id IS NULL OR account_id = '' OR account_id = 0)", array($cash_account_id, $store_id));
	$CI->db->query("UPDATE db_expense SET account_id = ? WHERE store_id = ? AND payment_type = 'cash' AND (account_id IS NULL OR account_id = '' OR account_id = 0)", array($cash_account_id, $store_id));
	$CI->db->query("UPDATE db_salespaymentsreturn SET account_id = ? WHERE store_id = ? AND payment_type = 'cash' AND (account_id IS NULL OR account_id = '' OR account_id = 0)", array($cash_account_id, $store_id));
	$CI->db->query("UPDATE db_purchasepaymentsreturn SET account_id = ? WHERE store_id = ? AND payment_type = 'cash' AND (account_id IS NULL OR account_id = '' OR account_id = 0)", array($cash_account_id, $store_id));
	$CI->db->query("UPDATE ac_moneydeposits SET debit_account_id = ? WHERE store_id = ? AND debit_account_id != ? AND debit_account_id IN (SELECT id FROM ac_accounts WHERE store_id = ? AND (LOWER(account_name) LIKE '%cash%' OR LOWER(account_name) LIKE '%till%'))", array($cash_account_id, $store_id, $cash_account_id, $store_id));

	$created_by = $CI->session->userdata('inv_username') ?: 'System';
	$created_date = date('Y-m-d');

	// 1. Rebuild ac_transactions for cash sales
	$payments = $CI->db->query("
		SELECT sp.id, sp.payment as amount, sp.payment_note as note, sp.payment_code as code, sp.customer_id, COALESCE(sp.payment_date, sp.created_date, CURDATE()) as txn_date
		FROM db_salespayments sp
		WHERE sp.store_id = ? AND sp.payment_type = 'cash' AND sp.account_id = ?
		  AND NOT EXISTS (
			  SELECT 1 FROM ac_transactions at
			  WHERE at.ref_salespayments_id = sp.id AND at.transaction_type = 'SALES PAYMENT'
		  )
	", array($store_id, $cash_account_id))->result();
	foreach($payments as $p){
		$CI->db->insert('ac_transactions', array(
			'transaction_type'   => 'SALES PAYMENT',
			'ref_salespayments_id'  => $p->id,
			'credit_account_id'  => $cash_account_id,
			'credit_amt'         => $p->amount,
			'store_id'           => $store_id,
			'created_by'         => $created_by,
			'created_date'       => $created_date,
			'transaction_date'   => $p->txn_date,
			'note'               => $p->note,
			'payment_code'       => $p->code,
			'customer_id'        => $p->customer_id,
		));
	}

	// 2. Rebuild ac_transactions for cash expenses
	$expenses = $CI->db->query("
		SELECT e.id, e.expense_amt as amount, e.note, e.expense_code as code, COALESCE(e.expense_date, e.created_date, CURDATE()) as txn_date
		FROM db_expense e
		WHERE e.store_id = ? AND e.payment_type = 'cash' AND e.account_id = ?
		  AND NOT EXISTS (
			  SELECT 1 FROM ac_transactions at
			  WHERE at.ref_expense_id = e.id AND at.transaction_type = 'EXPENSE PAYMENT'
		  )
	", array($store_id, $cash_account_id))->result();
	foreach($expenses as $e){
		$CI->db->insert('ac_transactions', array(
			'transaction_type'   => 'EXPENSE PAYMENT',
			'ref_expense_id'     => $e->id,
			'debit_account_id'   => $cash_account_id,
			'debit_amt'          => $e->amount,
			'store_id'           => $store_id,
			'created_by'         => $created_by,
			'created_date'       => $created_date,
			'transaction_date'   => $e->txn_date,
			'note'               => $e->note,
			'payment_code'       => $e->code,
		));
	}

	// 3. Rebuild ac_transactions for cash purchase payments
	$purchases = $CI->db->query("
		SELECT pp.id, pp.payment as amount, pp.payment_note as note, pp.payment_code as code, pp.supplier_id, COALESCE(pp.payment_date, pp.created_date, CURDATE()) as txn_date
		FROM db_purchasepayments pp
		WHERE pp.store_id = ? AND pp.payment_type = 'cash' AND pp.account_id = ?
		  AND NOT EXISTS (
			  SELECT 1 FROM ac_transactions at
			  WHERE at.ref_purchasepayments_id = pp.id AND at.transaction_type = 'PURCHASE PAYMENT'
		  )
	", array($store_id, $cash_account_id))->result();
	foreach($purchases as $pp){
		$CI->db->insert('ac_transactions', array(
			'transaction_type'   => 'PURCHASE PAYMENT',
			'ref_purchasepayments_id' => $pp->id,
			'debit_account_id'   => $cash_account_id,
			'debit_amt'          => $pp->amount,
			'store_id'           => $store_id,
			'created_by'         => $created_by,
			'created_date'       => $created_date,
			'transaction_date'   => $pp->txn_date,
			'note'               => $pp->note,
			'payment_code'       => $pp->code,
			'supplier_id'        => $pp->supplier_id,
		));
	}

	// 4. Rebuild ac_transactions for cash sales returns (money going OUT of cash)
	$sales_returns = $CI->db->query("
		SELECT sr.id, sr.payment as amount, sr.payment_note as note, sr.payment_code as code, sr.customer_id, COALESCE(sr.payment_date, sr.created_date, CURDATE()) as txn_date
		FROM db_salespaymentsreturn sr
		WHERE sr.store_id = ? AND sr.payment_type = 'cash' AND sr.account_id = ?
		  AND NOT EXISTS (
			  SELECT 1 FROM ac_transactions at
			  WHERE at.ref_salespaymentsreturn_id = sr.id AND at.transaction_type = 'SALES PAYMENT RETURN'
		  )
	", array($store_id, $cash_account_id))->result();
	foreach($sales_returns as $sr){
		$CI->db->insert('ac_transactions', array(
			'transaction_type'   => 'SALES PAYMENT RETURN',
			'ref_salespaymentsreturn_id' => $sr->id,
			'debit_account_id'   => $cash_account_id,
			'debit_amt'          => $sr->amount,
			'store_id'           => $store_id,
			'created_by'         => $created_by,
			'created_date'       => $created_date,
			'transaction_date'   => $sr->txn_date,
			'note'               => $sr->note,
			'payment_code'       => $sr->code,
			'customer_id'        => $sr->customer_id,
		));
	}

	// 5. Rebuild ac_transactions for cash purchase returns (money coming IN to cash)
	$purchase_returns = $CI->db->query("
		SELECT pr.id, pr.payment as amount, pr.payment_note as note, pr.payment_code as code, pr.supplier_id, COALESCE(pr.payment_date, pr.created_date, CURDATE()) as txn_date
		FROM db_purchasepaymentsreturn pr
		WHERE pr.store_id = ? AND pr.payment_type = 'cash' AND pr.account_id = ?
		  AND NOT EXISTS (
			  SELECT 1 FROM ac_transactions at
			  WHERE at.ref_purchasepaymentsreturn_id = pr.id AND at.transaction_type = 'PURCHASE PAYMENT RETURN'
		  )
	", array($store_id, $cash_account_id))->result();
	foreach($purchase_returns as $pr){
		$CI->db->insert('ac_transactions', array(
			'transaction_type'   => 'PURCHASE PAYMENT RETURN',
			'ref_purchasepaymentsreturn_id' => $pr->id,
			'credit_account_id'  => $cash_account_id,
			'credit_amt'         => $pr->amount,
			'store_id'           => $store_id,
			'created_by'         => $created_by,
			'created_date'       => $created_date,
			'transaction_date'   => $pr->txn_date,
			'note'               => $pr->note,
			'payment_code'       => $pr->code,
			'supplier_id'        => $pr->supplier_id,
		));
	}

	update_account_balance($cash_account_id);
}

function get_account_balance($account_id){
	$CI =& get_instance();
	$debit = $CI->db->select("coalesce(sum(debit_amt),0) as debit")->where('debit_account_id',$account_id)->get("ac_transactions")->row()->debit;
	$credit = $CI->db->select("coalesce(sum(credit_amt),0) as credit")->where('credit_account_id',$account_id)->get("ac_transactions")->row()->credit;
	$balance = $credit-$debit;
	return $balance;
}
function update_account_balance($account_id){
	$CI =& get_instance();
	$balance = get_account_balance($account_id);
	$q1=$CI->db->set('balance',$balance)->where("id",$account_id)->update("ac_accounts");
	if(!$q1){
		return false;
	}
	return true;
}
