<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quotation_model extends CI_Model {

    /** Reuses the public quotation response controller; only hashes are persisted. */
    public function issue_customer_response_token($id) {
        $store=(int)get_current_store_id();$this->db->trans_begin();
        try {
            $this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$store]);
            $quote=$this->db->query('SELECT * FROM db_quotation WHERE id=? AND store_id=? FOR UPDATE',[(int)$id,$store])->row();
            if (!$quote || trim((string)$quote->quotation_status)==='Draft' || $quote->lifecycle_status!=='issued' || ($quote->sales_status ?? '')==='Converted') throw new InvalidArgumentException('Only a current final, unanswered quotation can receive a response link.');
            $expiry=(string)($quote->expire_date ?? '');
            if ($expiry && $expiry!=='0000-00-00' && $expiry<date('Y-m-d')) throw new InvalidArgumentException('Update the expired quotation before requesting a response.');
            $token=bin2hex(random_bytes(32));$end=strtotime('+7 days');
            if ($expiry && $expiry!=='0000-00-00') $end=min($end,strtotime($expiry.' 23:59:59'));
            if (!$this->db->where('id',(int)$id)->where('store_id',$store)->update('db_quotation',['response_token_hash'=>hash('sha256',$token),'response_token_expires_at'=>date('Y-m-d H:i:s',$end)])) throw new RuntimeException('Could not create response link.');
            if ($this->db->trans_status()===false) throw new RuntimeException('Could not create response link.');
            $this->db->trans_commit();return $token;
        } catch (Throwable $e) { $this->db->trans_rollback();throw $e; }
    }


    /** Record an evidenced customer acceptance using the shared quotation audit. */
    public function record_customer_acceptance($id,$customerId,$revision,$evidence) {
        $evidence=trim((string)$evidence);$store=(int)get_current_store_id();
        if (!$evidence || strlen($evidence)>255) throw new InvalidArgumentException('Describe who approved, when and how (up to 255 characters).');
        $quote=$this->db->query('SELECT * FROM db_quotation WHERE id=? AND store_id=? AND customer_id=? FOR UPDATE',[(int)$id,$store,(int)$customerId])->row();
        if (!$quote || (int)$quote->revision_no!==(int)$revision || trim((string)$quote->quotation_status)==='Draft') throw new InvalidArgumentException('Choose the current final quotation revision.');
        if ($quote->lifecycle_status==='accepted') return true;
        $expiry=(string)($quote->expire_date ?? '');
        if (in_array($quote->lifecycle_status,['cancelled','declined','expired'],true) || ($expiry && $expiry!=='0000-00-00' && $expiry<date('Y-m-d'))) throw new InvalidArgumentException('This quotation is no longer available for approval.');
        if (!$this->db->where('id',(int)$id)->where('store_id',$store)->update('db_quotation',['lifecycle_status'=>'accepted','responded_at'=>date('Y-m-d H:i:s'),'response_reason'=>$evidence,'response_token_hash'=>null])) throw new RuntimeException('Could not record acceptance.');
        if (!$this->db->insert('db_quotation_lifecycle_log',['store_id'=>$store,'quotation_id'=>(int)$id,'from_status'=>$quote->lifecycle_status,'to_status'=>'accepted','reason'=>$evidence,
            'actor'=>substr((string)$this->session->userdata('inv_username'),0,60),'channel'=>'ui'])) throw new RuntimeException('Could not record approval history.');
        return true;
    }

	//Datatable start
	var $table = 'db_quotation as a';
	var $column_order = array( 
								'a.id',
								'a.quotation_date',
								'a.expire_date',
								'a.quotation_code',
								'a.reference_no',
								'b.customer_name',
								'a.grand_total',
								'a.created_by',
								'a.store_id',
								'a.sales_status',
								); //set column field database for datatable orderable
	var $column_search = array( 
								'a.id',
								'a.quotation_date',
								'a.expire_date',
								'a.quotation_code',
								'a.reference_no',
								'b.customer_name',
								'a.grand_total',
								'a.created_by',
								'a.store_id',
								'a.sales_status',
								); //set column field database for datatable searchable 
	var $order = array('a.id' => 'desc'); // default order  

	public function __construct()
	{
		parent::__construct();
		$CI =& get_instance();
	}

	private function _get_datatables_query()
	{
		
		$this->db->select($this->column_order);
		$this->db->from($this->table);
		$this->db->join('db_customers as b','b.id=a.customer_id','left');
		
		/*If warehouse selected*/
		$warehouse_id = $this->input->post('warehouse_id');
		if(!empty($warehouse_id)){
			$this->db->join('db_warehouse as w','w.id='.$warehouse_id,'left');
			$this->db->where('a.warehouse_id',$warehouse_id);
		}
		
		//if(!is_admin()){
	      $this->db->where("a.store_id",get_current_store_id());
	    //}
	      if(!is_admin()){
	      	if($this->session->userdata('role_id')!='2'){
	      		if(!permissions('show_all_users_quotations')){
	      			$this->db->where("upper(a.created_by)",strtoupper($this->session->userdata('inv_username')));
	      		}
	      	}
	      }
	     $quotation_from_date = $this->input->post('quotation_from_date');
	     $quotation_from_date = system_fromatted_date($quotation_from_date);
	     $quotation_to_date = $this->input->post('quotation_to_date');
	     $quotation_to_date = system_fromatted_date($quotation_to_date);
	     $users = $this->input->post('users');
	     /*if($users && !empty($users)){
	     	$this->db->where("upper(a.created_by)",strtoupper($users));
	     }*/
	     if($quotation_from_date!='1970-01-01'){
	     	$this->db->where("a.quotation_date>=",$quotation_from_date);
	     }
	     if($quotation_to_date!='1970-01-01'){
	     	$this->db->where("a.quotation_date<=",$quotation_to_date);
	     }
	   // echo $this->db->get_compiled_select();exit();
		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
			if(isset($_POST['search']['value']) && !empty($_POST['search']['value'])) // if datatable send POST for search
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
		
		if(isset($_POST['order']) && isset($_POST['order']['0']['column']) && isset($_POST['order']['0']['dir'])) // here order processing
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
		if(isset($_POST['length']) && $_POST['length'] != -1)
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

	/** Free-entry quotation lines for the processing plant workspace. */
	public function save_plant_quotation(){
		$store_id = (int)get_current_store_id();
		$customer_id = (int)$this->input->post('customer_id', TRUE);
		$customer = $this->db->select('id')->from('db_customers')->where('id', $customer_id)->where('store_id', $store_id)->get()->row();
		if(!$customer){ return 'Select a customer from this store.'; }
		// Draft lets the quote be reviewed before it is finalized and emailed.
		$quotation_status = trim((string)$this->input->post('quotation_status', TRUE)) === 'Draft' ? 'Draft' : 'Quotation';
		$raw_lines = $this->input->post('lines', TRUE);
		if(!is_array($raw_lines)){ return 'Add at least one quotation line.'; }
		$tax_id = (int)$this->input->post('tax_id', TRUE);
		$tax_rate = 0;
		if($tax_id){
			$tax = $this->db->select('id,tax')->from('db_tax')->where('id',$tax_id)->where('store_id',$store_id)->where('status',1)->get()->row();
			if(!$tax){ return 'Select a valid tax rate for this store.'; }
			$tax_rate = (float)$tax->tax;
		}
		$lines = array();
		$total = 0;
		$subtotal = 0;
		foreach($raw_lines as $raw){
			$name = trim((string)($raw['name'] ?? ''));
			if($name === ''){ continue; }
			$qty = filter_var($raw['qty'] ?? null, FILTER_VALIDATE_FLOAT);
			$price = filter_var($raw['price'] ?? null, FILTER_VALIDATE_FLOAT);
			if($qty === false || $qty <= 0 || $price === false || $price < 0){ return 'Each line needs a positive quantity and a valid unit price.'; }
			$amount = round($qty * $price, 2);
			$tax_amount = round($amount * $tax_rate / 100, 2);
			$item_id = (int)($raw['item_id'] ?? 0);
			if($item_id && !$this->db->select('id')->from('db_items')->where('id', $item_id)->where('store_id', $store_id)->get()->row()){
				return 'A quotation line refers to an item outside this store.';
			}
			$lines[] = array(
				'item_id' => $item_id ?: null,
				'manual_item_name' => mb_substr($name, 0, 255),
				'part_code' => mb_substr(trim((string)($raw['code'] ?? '')), 0, 100),
				'unit_label' => mb_substr(trim((string)($raw['unit'] ?? '')), 0, 40),
				'section_name' => mb_substr(trim((string)($raw['section'] ?? '')), 0, 160),
				'description' => trim((string)($raw['description'] ?? '')),
				'quotation_qty' => $qty,
				'price_per_unit' => $price,
				'unit_total_cost' => round($price * (1 + $tax_rate / 100), 4),
				'tax_id' => $tax_id ?: null,
				'tax_type' => $tax_id ? 'Exclusive' : null,
				'tax_amt' => $tax_amount,
				'total_cost' => $amount + $tax_amount,
				'quotation_status' => $quotation_status,
				'status' => 1,
				'seller_points' => 0,
				'store_id' => $store_id,
			);
			$subtotal += $amount;
			$total += $amount + $tax_amount;
		}
		if(!$lines){ return 'Add at least one quotation line with a description.'; }
		foreach(array('manual_item_name','part_code','unit_label','section_name') as $column){
			if(!$this->db->field_exists($column, 'db_quotationitems')){ return 'Plant quotation update is pending. Run the latest database migrations.'; }
		}
		$quote_id = (int)$this->input->post('quotation_id', TRUE);
		if($quote_id){
			$existing = $this->db->select('id, sales_status')->from('db_quotation')->where('id', $quote_id)->where('store_id', $store_id)->get()->row();
			if(!$existing){ return 'Quotation not found for this store.'; }
			if(!empty($existing->sales_status)){ return 'This quotation has already been converted to an invoice and cannot be edited.'; }
		}
		$raw_date = trim((string)$this->input->post('quotation_date', TRUE));
		if(strtotime(str_replace('/', '-', $raw_date)) === false){ return 'Enter a valid quotation date.'; }
		$quote_date = system_fromatted_date($raw_date);
		$expires = trim((string)$this->input->post('expire_date', TRUE));
		if($expires !== '' && strtotime(str_replace('/', '-', $expires)) === false){ return 'Enter a valid expiry date.'; }
		$expires = $expires !== '' ? system_fromatted_date($expires) : null;
		$header = array(
			'store_id' => $store_id,
			'warehouse_id' => get_store_warehouse_id(),
			'customer_id' => $customer_id,
			'quotation_date' => $quote_date,
			'expire_date' => $expires,
			'reference_no' => trim((string)$this->input->post('reference_no', TRUE)),
			'quotation_status' => $quotation_status,
			// Preserve literal payment terms such as "30% deposit". CI's XSS
			// cleaner treats "% de" as a spaced URL escape and changes the text.
			'quotation_note' => trim((string)$this->input->post('quotation_note', FALSE)),
			'subtotal' => round($subtotal, 2),
			'grand_total' => round($total, 2),
			'round_off' => 0,
			'other_charges_input' => null,
			'other_charges_amt' => null,
			'discount_to_all_input' => null,
			'tot_discount_to_all_amt' => null,
		);
		$this->db->trans_begin();
		if($quote_id){
			$before = $this->db->select('lifecycle_status')->where('id',$quote_id)->where('store_id',$store_id)->get('db_quotation')->row();
			$wasAccepted = $before && trim((string)($before->lifecycle_status ?? '')) === 'accepted';
			if($wasAccepted && $this->db->field_exists('lifecycle_status','db_quotation')){
				$header['lifecycle_status'] = 'issued';
				foreach(['responded_at','response_reason','response_token_hash','response_token_expires_at'] as $col){
					if($this->db->field_exists($col,'db_quotation')) $header[$col] = null;
				}
			}
			$this->db->where('id', $quote_id)->where('store_id', $store_id)->update('db_quotation', $header);
			$this->db->where('quotation_id', $quote_id)->where('store_id', $store_id)->delete('db_quotationitems');
			if($wasAccepted && $this->db->table_exists('db_quotation_lifecycle_log')){
				if(!$this->db->insert('db_quotation_lifecycle_log',[
					'store_id'=>(int)$store_id,'quotation_id'=>(int)$quote_id,'from_status'=>'accepted','to_status'=>'issued',
					'reason'=>'Quotation edited; previous customer acceptance is no longer valid.',
					'actor'=>(string)($this->session->userdata('inv_username') ?: 'system'),'channel'=>'admin',
				])){
					$this->db->trans_rollback();
					return 'Quotation saved but its acceptance history could not be recorded. Please try again.';
				}
			}
		}else{
			$header['quotation_code'] = get_init_code('quotation');
			$header['count_id'] = get_count_id('db_quotation');
			$header['created_date'] = date('Y-m-d');
			$header['created_time'] = date('H:i:s');
			$header['created_by'] = $this->session->userdata('inv_username') ?: $this->session->userdata('username');
			$header['status'] = 1;
			$this->db->insert('db_quotation', $header);
			$quote_id = (int)$this->db->insert_id();
		}
		foreach($lines as $line){
			$line['quotation_id'] = $quote_id;
			$this->db->insert('db_quotationitems', $line);
		}
		if(!$quote_id || $this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return 'Quotation could not be saved. Please try again.';
		}
		$this->db->trans_commit();
		return 'success<<<###>>>'.$quote_id;
	}

	//Save Quotation
	public function verify_save_and_update(){
		$command = $this->input->post_get('command', TRUE);
		$quotation_date = $this->input->post('quotation_date', TRUE);
		$expire_date = $this->input->post('expire_date', TRUE);
		$reference_no = $this->input->post('reference_no', TRUE);
		$quotation_status = $this->input->post('quotation_status', TRUE);
		$customer_id = $this->input->post('customer_id', TRUE);
		$other_charges_input = $this->input->post('other_charges_input', TRUE);
		$other_charges_tax_id = $this->input->post('other_charges_tax_id', TRUE);
		$other_charges_amt = $this->input->post_get('other_charges_amt', TRUE);
		$discount_to_all_input = $this->input->post('discount_to_all_input', TRUE);
		$discount_to_all_type = $this->input->post('discount_to_all_type', TRUE);
		$tot_discount_to_all_amt = $this->input->post_get('tot_discount_to_all_amt', TRUE);
		$tot_subtotal_amt = $this->input->post_get('tot_subtotal_amt', TRUE);
		$tot_round_off_amt = $this->input->post_get('tot_round_off_amt', TRUE);
		$tot_total_amt = $this->input->post_get('tot_total_amt', TRUE);
		$quotation_note = $this->input->post('quotation_note', FALSE);
		$rowcount = $this->input->post_get('rowcount', TRUE);
		$quotation_id = $this->input->post('quotation_id', TRUE);
		$warehouse_id = (int)$this->input->post('warehouse_id', TRUE);
		if(!$warehouse_id && function_exists('get_store_warehouse_id')) $warehouse_id=(int)get_store_warehouse_id();
		$store_id = $this->input->post('store_id', TRUE);
		//echo "<pre>";print_r($this->xss_html_filter(array_merge($this->data,$_POST,$_GET)));exit();
		
		$this->db->trans_begin();
		$quotation_date=system_fromatted_date($quotation_date);
		$expire_date=date('Y-m-d',strtotime($expire_date));
		$expire_date = ($expire_date=='1970-01-01') ? null : $expire_date;

		if($other_charges_input=='' || $other_charges_input==0){$other_charges_input=null;}
	    if($other_charges_tax_id=='' || $other_charges_tax_id==0){$other_charges_tax_id=null;}
	    if($other_charges_amt=='' || $other_charges_amt==0){$other_charges_amt=null;}
	    if($discount_to_all_input=='' || $discount_to_all_input==0){$discount_to_all_input=null;}
	    if($tot_discount_to_all_amt=='' || $tot_discount_to_all_amt==0){$tot_discount_to_all_amt=null;}
	    if($tot_round_off_amt=='' || $tot_round_off_amt==0){$tot_round_off_amt=null;}

	    $prev_item_ids = array();
	    
	    if($command=='save'){//Create quotation code unique if first time entry

			
		    $quotation_entry = array(
		    				'quotation_code' 				=> get_init_code('quotation'),
		    				'count_id' 					=> get_count_id('db_quotation'),  
		    				'reference_no' 				=> $reference_no, 
		    				'quotation_date' 				=> $quotation_date,
		    				'expire_date' 				=> $expire_date,
		    				'quotation_status' 				=> $quotation_status,
		    				'customer_id' 				=> $customer_id,
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
		    				'quotation_note' 				=> $quotation_note,
		    				/*System Info*/
		    				'created_date' 				=> $CUR_DATE,
		    				'created_time' 				=> $CUR_TIME,
		    				'created_by' 				=> $CUR_USERNAME,
		    				'system_ip' 				=> $SYSTEM_IP,
		    				'system_name' 				=> $SYSTEM_NAME,
		    				'status' 					=> 1,
		    			);
		    $quotation_entry['store_id']=(store_module() && is_admin()) ? $store_id : get_current_store_id();  	
		    $quotation_entry['warehouse_id']=(warehouse_module() && warehouse_count()>1) ? $warehouse_id : get_store_warehouse_id();
			$q1 = $this->db->insert('db_quotation', $quotation_entry);
			$quotation_id = $this->db->insert_id();
		}
		else if($command=='update'){	
			$quotation_entry = array(
		    				'reference_no' 				=> $reference_no, 
		    				'quotation_date' 			=> $quotation_date,
		    				'expire_date' 				=> $expire_date,
		    				'quotation_status' 			=> $quotation_status,
		    				'customer_id' 				=> $customer_id,
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
		    				'quotation_note' 			=> $quotation_note,
		    			);
			$quotation_entry['store_id']=(store_module() && is_admin()) ? $store_id : get_current_store_id();  	
			$quotation_entry['warehouse_id']=(warehouse_module() && warehouse_count()>1) ? $warehouse_id : get_store_warehouse_id();
			$before = $this->db->select('lifecycle_status')->where('id',$quotation_id)->where('store_id',$quotation_entry['store_id'])->get('db_quotation')->row();
			$wasAccepted = $before && trim((string)($before->lifecycle_status ?? '')) === 'accepted';
			if($wasAccepted && $this->db->field_exists('lifecycle_status','db_quotation')){
				$quotation_entry['lifecycle_status'] = 'issued';
				foreach(['responded_at','response_reason','response_token_hash','response_token_expires_at'] as $col){
					if($this->db->field_exists($col,'db_quotation')) $quotation_entry[$col] = null;
				}
			}
			$q1 = $this->db->where('id',$quotation_id)->update('db_quotation', $quotation_entry);
			if($wasAccepted && $this->db->table_exists('db_quotation_lifecycle_log')){
				if(!$this->db->insert('db_quotation_lifecycle_log',[
					'store_id'=>(int)$quotation_entry['store_id'],
					'quotation_id'=>(int)$quotation_id,
					'from_status'=>'accepted',
					'to_status'=>'issued',
					'reason'=>'Quotation edited; previous customer acceptance is no longer valid.',
					'actor'=>(string)($this->session->userdata('inv_username') ?: 'system'),
					'channel'=>'admin',
				])){
					$this->db->trans_rollback();
					return 'Quotation saved but its acceptance history could not be recorded. Please try again.';
				}
			}

			##############################################START
			//FIND THE PREVIOUSE ITEM LIST ID'S
			$prev_item_ids = $this->db->select("item_id")->from("db_quotationitems")->where("quotation_id",$quotation_id)->get()->result_array();
			##############################################END

			$q11=$this->db->query("delete from db_quotationitems where quotation_id='$quotation_id'");
			if(!$q11){
				return "failed";
			}
		}
		//end

		
		//Import post data from form
		for($i=1;$i<=$rowcount;$i++){
		
			if(isset($_REQUEST['tr_item_id_'.$i]) && !empty($_REQUEST['tr_item_id_'.$i])){

				$item_id 			=$this->xss_html_filter(trim($_REQUEST['tr_item_id_'.$i]));
				$quotation_qty			=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_3']));
				$price_per_unit 	=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_4']));
				$tax_id 			=$this->xss_html_filter(trim($_REQUEST['tr_tax_id_'.$i]));
				$tax_amt 			=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_11']));
				$unit_total_cost	=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_10']));
				//$discount_input	=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_8']));
				$total_cost			=$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_9']));
				$tax_type			=$this->xss_html_filter(trim($_REQUEST['tr_tax_type_'.$i]));
				$unit_tax			=$this->xss_html_filter(trim($_REQUEST['tr_tax_value_'.$i]));
				$description		=$this->xss_html_filter(trim($_REQUEST['description_'.$i]));
				$section_name = isset($_REQUEST['section_name_'.$i]) ? $this->xss_html_filter(trim($_REQUEST['section_name_'.$i])) : '';

                //$discount_input  =(empty($discount_input)) ? 0 : $discount_input;
				//$discount_amt 		=($quotation_qty * $unit_total_cost)*$discount_input/100;


				$discount_type 		=$this->xss_html_filter(trim($_REQUEST['item_discount_type_'.$i]));
				$discount_input 	=$this->xss_html_filter(trim($_REQUEST['item_discount_input_'.$i]));
				$discount_amt	    =$this->xss_html_filter(trim($_REQUEST['td_data_'.$i.'_8']));//Amount

				$discount_amt_per_unit = $discount_amt/$quotation_qty;
				if($tax_type=='Exclusive'){
					$single_unit_total_cost = $price_per_unit + ($unit_tax * $price_per_unit / 100);
				}
				else{//Inclusive
					$single_unit_total_cost =$price_per_unit;
				}
				$single_unit_total_cost -=$discount_amt_per_unit;


				if($tax_id=='' || $tax_id==0){$tax_id=null;}
				if($tax_amt=='' || $tax_amt==0){$tax_amt=null;}
				if($discount_input=='' || $discount_input==0){$discount_input=null;}
				//if($unit_total_cost=='' || $unit_total_cost==0){$unit_total_cost=null;}
				if($total_cost=='' || $total_cost==0){$total_cost=null;}
				
			
				
				$quotationitems_entry = array(
		    				'quotation_id' 			=> $quotation_id, 
		    				'quotation_status'		=> $quotation_status, 
		    				'item_id' 			=> $item_id, 
		    				'description' 		=> $description, 
		    				'quotation_qty' 		=> $quotation_qty,
		    				'price_per_unit' 	=> $price_per_unit,
		    				'tax_type' 			=> $tax_type,
		    				'tax_id' 			=> $tax_id,
		    				'tax_amt' 			=> $tax_amt,
		    				'discount_input' 	=> $discount_input,
		    				'discount_amt' 		=> $discount_amt,
		    				'discount_type' 	=> $discount_type,
		    				'unit_total_cost' 	=> $single_unit_total_cost,
		    				'total_cost' 		=> $total_cost,
		    				'status'	 		=> 1,
							'seller_points'		=> get_seller_points($item_id) * $quotation_qty,

						);
				if ($this->db->field_exists('section_name', 'db_quotationitems')) {
					$quotationitems_entry['section_name'] = $section_name !== '' ? $section_name : null;
				}
				
				$quotationitems_entry['store_id']=(store_module() && is_admin()) ? $store_id : get_current_store_id();  	
				$q2 = $this->db->insert('db_quotationitems', $quotationitems_entry);
				
				//UPDATE itemS QUANTITY IN itemS TABLE
				$this->load->model('pos_model');				
				$q6=$this->pos_model->update_items_quantity($item_id);
				if(!$q6){
					return "failed";
				}
				
			}
		
		}//for end

		$sms_info='';
		/*
		if(isset($send_sms) && $customer_id!=1){
			if(send_sms_using_template($quotation_id,1)==true){
				$sms_info = 'SMS Has been Sent!';
			}else{
				$sms_info = 'Failed to Send SMS';
			}
		}*/
		
		if($q1 === FALSE || $this->db->trans_status() === FALSE || empty($quotation_id)){
			$this->db->trans_rollback();
			$error = $this->db->error();
			$msg = !empty($error['message']) ? $error['message'] : 'Quotation could not be saved';
			return "Database error: " . $msg . " [q1=" . var_export($q1, true) . ", insert_id=" . var_export($quotation_id, true) . ", last_query=" . $this->db->last_query() . "]";
		}

        $this->load->helper('garden');
        $gardenLinkError=$command==='save' ? mp_garden_link_created_document('quotation',$quotation_id) : null;
        if ($gardenLinkError!==null) { $this->db->trans_rollback();return $gardenLinkError; }
		$this->db->trans_commit();
		$this->session->set_flashdata('success', 'Success!! Record Saved Successfully! '.$sms_info);
		return "success<<<###>>>$quotation_id";
		
	}//verify_save_and_update() function end

	


	//Get quotation_details
	public function get_details($id,$data){
		//Validate This quotation already exist or not
		$query=$this->db->query("select * from db_quotation where upper(id)=upper('$id')");
		if($query->num_rows()==0){
			show_404();exit;
		}
		else{
			$query=$query->row();
			$data['q_id']=$query->id;
			$data['item_code']=$query->item_code;
			$data['item_name']=$query->item_name;
			$data['category_name']=$query->category_name;
			$data['hsn']=$query->hsn;
			$data['unit_name']=$query->unit_name;
			$data['available_qty']=$query->available_qty;
			$data['alert_qty']=$query->alert_qty;
			$data['quotation_price']=$query->quotation_price;
			$data['quotation_price']=$query->quotation_price;
			$data['gst_percentage']=$query->gst_percentage;
			
			return $data;
		}
	}

	public function delete_quotation($ids){
      	$this->db->trans_begin();
      	
		//find the this item has the Purchase Return 
		$converted_rec = $this->db->select("*")->where("quotation_id in($ids)")->get("db_sales");
		if($converted_rec->num_rows()>0){
			$i=1;
			echo "Can't Delete!<br>These Quotations List Have the Sales Records!";
			foreach($converted_rec->result() as $res1){
				echo "<br>".$i++.". Sales ID:".$res1->sales_code;
			}
			exit;
		}

		#----------------------------------
		$this->db->where("id in ($ids)");
		//if not admin
		if(!is_admin()){
			$this->db->where("store_id",get_current_store_id());
		}

		$q3=$this->db->delete("db_quotation");
		#----------------------------------
		
		$this->db->trans_commit();
		return "success";
	}
	
	public function get_items_info($rowcount,$item_id){
		$customer_id = $this->input->post('customer_id', TRUE);
		$warehouse_id = (int)$this->input->post('warehouse_id', TRUE);
		if(!$warehouse_id && function_exists('get_store_warehouse_id')) $warehouse_id=(int)get_store_warehouse_id();

		$res1=$this->db->select('*')->from('db_items')->where('id',(int)$item_id)->where('store_id',(int)get_current_store_id())->where('status',1)->get()->row();
		if(!$res1) return '';
		// Tax is optional on legacy catalogue records. A missing tax must not
		// make the quotation row endpoint fail and silently lose the item.
		$q3=$res1->tax_id ? $this->db->where('id',(int)$res1->tax_id)->get('db_tax')->row() : null;
		$taxRate=(float)($q3->tax ?? 0);
		$taxType=in_array((string)($res1->tax_type ?? ''),['Inclusive','Exclusive'],true) ? (string)$res1->tax_type : 'Exclusive';

		//Get Customer Price 
		$quotation_price = get_price_level_price($customer_id,$res1->sales_price);
		$quotation_price = number_format($quotation_price,2,'.','');

		
		$item_available_qty = total_available_qty_items_of_warehouse($warehouse_id,null,$res1->id);// $res1->stock;
		
		$item_tax_amt = ($taxType=='Inclusive') ? calculate_inclusive($quotation_price,$taxRate) :calculate_exclusive($quotation_price,$taxRate);

		$info = array(
							'item_id' 					=> $res1->id, 
							'description' 				=> (string)($res1->description ?? ''),
							'item_name' 				=> $res1->item_name,
							'item_code' 				=> $res1->item_code,
							'item_available_qty' 		=> $item_available_qty,
							'item_price' 				=> $res1->price, 
							'item_quotation_price' 			=> $quotation_price, 
							'item_tax_name' 			=> $q3->tax_name ?? 'No Tax',
							'item_quotation_qty' 			=> 1, 
							'item_tax_id' 				=> (int)($q3->id ?? 0),
							'item_tax' 					=> $taxRate,
							'item_tax_type' 			=> $taxType,
							'item_tax_amt' 				=> $item_tax_amt, 
							'item_discount' 			=> 0, 
							'item_discount_type' 		=> 'Percentage', 
							'item_discount_input' 		=> 0, 
							'service_bit' 				=> $res1->service_bit, 
						);
		$this->return_row_with_data($rowcount,$info);
	}
	/* For Purchase Items List Retrieve*/
	public function return_quotation_list($quotation_id){
		$q1=$this->db->select('*')->from('db_quotationitems')->where("quotation_id=$quotation_id")->get();
		$rowcount =1;
		foreach ($q1->result() as $res1) {
			$res2=$this->db->query("select * from db_items where id=".$res1->item_id)->row();
			$q3=$this->db->query("select * from db_tax where id=".$res1->tax_id)->row();
			
			$info = array(
							'item_id' 					=> $res1->item_id, 
							'description' 				=> trim((string)($res1->description ?? '')) !== '' ? $res1->description : (string)($res2->description ?? ''),
							'section_name' 			=> isset($res1->section_name) ? $res1->section_name : '',
							'item_name' 				=> $res2->item_name,
							'item_code' 				=> $res2->item_code,
							'item_available_qty' 		=> $res2->stock,
							'item_price' 				=> $res2->price, 
							'item_quotation_price' 			=> $res1->price_per_unit, 
							'item_tax_name' 			=> $q3->tax_name, 
							'item_quotation_qty' 			=> $res1->quotation_qty, 
							'item_tax_id' 				=> $res1->tax_id, 
							'item_tax' 					=> $q3->tax, 
							'item_tax_type' 			=> $res1->tax_type, 
							'item_tax_amt' 				=> $res1->tax_amt, 
							'item_discount' 			=> $res1->discount_input, 
							'item_discount_type' 		=> $res1->discount_type, 
							'item_discount_input' 		=> $res1->discount_input, 
							'service_bit' 				=> $res2->service_bit, 
						);
			$result = $this->return_row_with_data($rowcount++,$info);
		}
		return $result;
	}

	public function return_row_with_data($rowcount,$info){
		$item_id = isset($info['item_id']) ? $info['item_id'] : '';
		$description = isset($info['description']) ? $info['description'] : '';
		$section_name = isset($info['section_name']) ? $info['section_name'] : '';
		$item_name = isset($info['item_name']) ? $info['item_name'] : '';
		$item_code = isset($info['item_code']) ? $info['item_code'] : '';
		$item_available_qty = isset($info['item_available_qty']) ? $info['item_available_qty'] : '';
		$item_price = isset($info['item_price']) ? $info['item_price'] : '';
		$item_quotation_price = isset($info['item_quotation_price']) ? $info['item_quotation_price'] : '';
		$item_tax_name = isset($info['item_tax_name']) ? $info['item_tax_name'] : '';
		$item_quotation_qty = isset($info['item_quotation_qty']) ? $info['item_quotation_qty'] : '';
		$item_tax_id = isset($info['item_tax_id']) ? $info['item_tax_id'] : '';
		$item_tax = isset($info['item_tax']) ? $info['item_tax'] : '';
		$item_tax_type = isset($info['item_tax_type']) ? $info['item_tax_type'] : '';
		$item_tax_amt = isset($info['item_tax_amt']) ? $info['item_tax_amt'] : '';
		$item_discount = isset($info['item_discount']) ? $info['item_discount'] : '';
		$item_discount_type = isset($info['item_discount_type']) ? $info['item_discount_type'] : '';
		$item_discount_input = isset($info['item_discount_input']) ? $info['item_discount_input'] : '';
		$service_bit = isset($info['service_bit']) ? $info['service_bit'] : '';
		$item_amount = ($item_quotation_price * $item_quotation_qty) + $item_tax_amt;
		?>
            <tr id="row_<?=$rowcount;?>" data-row='<?=$rowcount;?>'>
               <td id="td_<?=$rowcount;?>_1">
                  <label class='form-control' style='height:auto;' data-toggle="tooltip" title='Edit ?' >
                  <?php if(function_exists('mp_get_store_profile') && (mp_get_store_profile()['industry_type'] ?? '') === 'oil_processing_plant' && $item_code !== ''): ?><small style="color:#64748b;font-weight:700"><?=htmlspecialchars($item_code, ENT_QUOTES, 'UTF-8');?> · </small><?php endif; ?><a id="td_data_<?=$rowcount;?>_1" href="javascript:void()" onclick="show_quotation_item_modal(<?=$rowcount;?>)" title=""><?=$item_name;?></a>
                  		<i onclick="show_quotation_item_modal(<?=$rowcount;?>)" class="fa fa-edit pointer"></i>
                  	</label>
                  <?php if(trim((string)$description) !== ''): ?><small class="quotation-item-description" style="display:block;margin:4px 6px;color:#64748b;white-space:normal;line-height:1.35;"><?= nl2br(htmlspecialchars((string)$description, ENT_QUOTES, 'UTF-8')); ?></small><?php endif; ?>
                  <?php if (function_exists('mp_get_store_profile') && (mp_get_store_profile()['industry_type'] ?? '') === 'oil_processing_plant'): ?><small style="display:block;margin:5px 0 2px;color:#64748b;font-size:10px">Group heading (optional)</small><input type="text" class="form-control mp-form-control" style="font-size:11px" name="section_name_<?=$rowcount;?>" id="section_name_<?=$rowcount;?>" value="<?=htmlspecialchars($section_name, ENT_QUOTES, 'UTF-8');?>" placeholder="For example: EK-200K · First pressing"><?php endif; ?>
               </td>

               <!-- description  -->
               <!-- <td id="td_<?=$rowcount;?>_17">
                  
                  <textarea rows="1" type="text" style="font-weight: bold; height=34px;" id="td_data_<?=$rowcount;?>_17" name="td_data_<?=$rowcount;?>_17" class="form-control no-padding"><?=$description;?></textarea>
               </td> -->

               <!-- Qty -->
               <td id="td_<?=$rowcount;?>_3">
                  <div class="input-group ">
                     <span class="input-group-btn">
                     <button onclick="decrement_qty(<?=$rowcount;?>)" type="button" class="btn btn-default btn-flat"><i class="fa fa-minus text-danger"></i></button></span>
                     <input typ="text" value="<?=format_qty($item_quotation_qty);?>" class="form-control no-padding text-center" onkeyup="calculate_tax(<?=$rowcount;?>)" id="td_data_<?=$rowcount;?>_3" name="td_data_<?=$rowcount;?>_3">
                     <span class="input-group-btn">
                     <button onclick="increment_qty(<?=$rowcount;?>)" type="button" class="btn btn-default btn-flat"><i class="fa fa-plus text-success"></i></button></span>
                  </div>
               </td>
               
               <!-- Unit Cost Without Tax-->
               <td id="td_<?=$rowcount;?>_10"><input type="text" name="td_data_<?=$rowcount;?>_10" id="td_data_<?=$rowcount;?>_10" class="form-control text-right no-padding only_currency text-center" onkeyup="calculate_tax(<?=$rowcount;?>)" value="<?=store_number_format($item_quotation_price,0);?>"></td>

               <!-- Discount -->
               <td id="td_<?=$rowcount;?>_8">
                  <input type="text" data-toggle="tooltip" title="Click to Change" name="td_data_<?=$rowcount;?>_8" id="td_data_<?=$rowcount;?>_8" class="pointer form-control text-right no-padding only_currency text-center item_discount" value="<?=store_number_format($item_discount,0);?>" onclick="show_quotation_item_modal(<?=$rowcount;?>)" readonly>
               </td>

               <!-- Tax Amount -->
               <td id="td_<?=$rowcount;?>_11">
                  <input type="text" name="td_data_<?=$rowcount;?>_11" id="td_data_<?=$rowcount;?>_11" class="form-control text-right no-padding only_currency text-center" value="<?=store_number_format($item_tax_amt,2);?>" readonly>
               </td>

               <!-- Tax Details -->
               <td id="td_<?=$rowcount;?>_12">
                  <label class='form-control ' style='width:100%;padding-left:0px;padding-right:0px;'>
                  <a id="td_data_<?=$rowcount;?>_12" href="javascript:void()" data-toggle="tooltip" title='Click to Change' onclick="show_quotation_item_modal(<?=$rowcount;?>)" title=""><?=$item_tax_name ;?></a>
                  	</label>
               </td>

               <!-- Amount -->
               <td id="td_<?=$rowcount;?>_9"><input type="text" name="td_data_<?=$rowcount;?>_9" id="td_data_<?=$rowcount;?>_9" class="form-control text-right no-padding only_currency text-center" style="border-color: #f39c12;" readonly value="<?=store_number_format($item_amount,2);?>"></td>
               
               <!-- ADD button -->
               <td id="td_<?=$rowcount;?>_16" style="text-align: center;">
                  <a class=" fa fa-fw fa-minus-square text-red" style="cursor: pointer;font-size: 34px;" onclick="removerow(<?=$rowcount;?>)" title="Delete ?" name="td_data_<?=$rowcount;?>_16" id="td_data_<?=$rowcount;?>_16"></a>
               </td>
               <input type="hidden" id="td_data_<?=$rowcount;?>_4" name="td_data_<?=$rowcount;?>_4" value="<?=$item_quotation_price;?>">
               <input type="hidden" id="td_data_<?=$rowcount;?>_15" name="td_data_<?=$rowcount;?>_15" value="<?=$item_tax_id;?>">
               <input type="hidden" id="td_data_<?=$rowcount;?>_5" name="td_data_<?=$rowcount;?>_5" value="<?=$item_tax_amt;?>">
               <input type="hidden" id="tr_available_qty_<?=$rowcount;?>_13" value="<?=$item_available_qty;?>">
               <input type="hidden" id="tr_item_id_<?=$rowcount;?>" name="tr_item_id_<?=$rowcount;?>" value="<?=$item_id;?>">
               
               <input type="hidden" id="tr_tax_type_<?=$rowcount;?>" name="tr_tax_type_<?=$rowcount;?>" value="<?=$item_tax_type;?>">
               <input type="hidden" id="tr_tax_id_<?=$rowcount;?>" name="tr_tax_id_<?=$rowcount;?>" value="<?=$item_tax_id;?>">
               <input type="hidden" id="tr_tax_value_<?=$rowcount;?>" name="tr_tax_value_<?=$rowcount;?>" value="<?=$item_tax;?>">
               <input type="hidden" id="description_<?=$rowcount;?>" name="description_<?=$rowcount;?>" value="<?=htmlspecialchars((string)$description, ENT_QUOTES, 'UTF-8');?>">
               <input type="hidden" id="service_bit_<?=$rowcount;?>" name="service_bit_<?=$rowcount;?>" value="<?=$service_bit;?>">

               <input type="hidden" id="item_discount_type_<?=$rowcount;?>" name="item_discount_type_<?=$rowcount;?>" value="<?=$item_discount_type;?>">
               <input type="hidden" id="item_discount_input_<?=$rowcount;?>" name="item_discount_input_<?=$rowcount;?>" value="<?=$item_discount_input;?>">
            </tr>
		<?php

	}

	public function search_item($q){
		$json_array=array();
        $query1="select id,item_name from db_items where (upper(item_name) like upper('%$q%') or upper(item_code) like upper('%$q%'))";

        $q1=$this->db->query($query1);
        if($q1->num_rows()>0){
            foreach ($q1->result() as $value) {
            	$json_array[]=['id'=>(int)$value->id, 'text'=>$value->item_name];
            }
        }

        // Also search db_item_barcodes by barcode, serial, or imei
        $this->db->select('b.item_id, a.item_name, b.barcode, b.serial_number, b.imei_number');
        $this->db->from('db_item_barcodes b');
        $this->db->join('db_items a', 'a.id = b.item_id', 'left');
        $this->db->where('b.status', 1);
        $this->db->where("(LOWER(b.barcode) LIKE '%$q%' OR LOWER(b.serial_number) LIKE '%$q%' OR LOWER(b.imei_number) LIKE '%$q%')", null, false);
        $this->db->group_by('b.item_id');
        $q2 = $this->db->get();
        if($q2->num_rows()>0){
            foreach ($q2->result() as $value) {
                $label = $value->item_name;
                if($value->barcode) $label .= ' [BC:'.$value->barcode.']';
                if($value->serial_number) $label .= ' [S/N:'.$value->serial_number.']';
                if($value->imei_number) $label .= ' [IMEI:'.$value->imei_number.']';
                $json_array[]=['id'=>(int)$value->item_id, 'text'=>$label];
            }
        }
        return json_encode($json_array);
	}

	public function find_item_details($id){
		$json_array=array();
		$query1="select * from db_items where id=$id";
		$q1=$this->db->query($query1);
		if($q1->num_rows()>0){
			foreach ($q1->result() as $value) {
				$json_array[]=['id'=>$value->id, 'item_name'=>$value->item_name,'item_code'=>$value->item_code,'item_purchase_price'=>$value->purchase_price,'item_sales_price'=>$value->sales_price,'item_tax_id'=>$value->tax_id,'item_description'=>$value->description];
			}
		}
		return json_encode($json_array);
	}

}
