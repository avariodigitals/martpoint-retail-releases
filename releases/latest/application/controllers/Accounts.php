<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Accounts extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('accounts_model','accounts');
	}
	public function index()
	{
		$this->permission_check('accounts_view');
		$data=$this->data;
		$data['page_title']=$this->lang->line('accounts_list');
		$this->load->view('accounts/accounts_list',$data);
	}

	public function book($account_id)
	{
		$this->belong_to('ac_accounts',$account_id);
		$this->permission_check('accounts_view');
		$data=$this->data;
		$data['page_title']=$this->lang->line('account_book');
		$data['account_id']=$account_id;
		$this->load->view('accounts/account_book',$data);
	}
	public function cash_transactions()
	{
		$this->permission_check('accounts_view');
		$data=$this->data;
		$data['page_title']=$this->lang->line('cash_transactions');
		$this->load->view('accounts/cash_transactions',$data);
	}

	public function cash_ledger()
	{
		$this->permission_check('accounts_view');
		$this->load->model('dashboard_model');

		$from_date = $this->input->get('from_date');
		$to_date = $this->input->get('to_date');
		// Convert d-m-Y (datepicker) to Y-m-d (SQL)
		if(!empty($from_date)){
			$from_date = date('Y-m-d', strtotime(str_replace('/', '-', $from_date)));
		} else {
			$from_date = date('Y-m-d');
		}
		if(!empty($to_date)){
			$to_date = date('Y-m-d', strtotime(str_replace('/', '-', $to_date)));
		} else {
			$to_date = date('Y-m-d');
		}

		$store_id = get_current_store_id();

		// Load all accounts for dropdowns
		$accounts = $this->db->where('store_id', $store_id)
							   ->where('status', 1)
							   ->order_by('sort_code')
							   ->get('ac_accounts')
							   ->result();

		// Find Cash At Hand account (by name match)
		$cash_account_id = '';
		foreach($accounts as $acc){
			if(stripos($acc->account_name, 'cash') !== false || stripos($acc->account_name, 'hand') !== false || stripos($acc->account_name, 'till') !== false){
				$cash_account_id = $acc->id;
				break;
			}
		}

		$data = $this->data;
		$data['page_title'] = 'Cash In Hand Ledger';
		$data['from_date'] = $from_date;
		$data['to_date'] = $to_date;
		$data['opening_balance'] = $this->dashboard_model->get_cash_balance_before($from_date);
		$data['ledger'] = $this->dashboard_model->get_cash_ledger($from_date, $to_date);
		$data['accounts'] = $accounts;
		$data['cash_account_id'] = $cash_account_id;
		$this->load->view('accounts/cash_ledger', $data);
	}

	public function add()
	{
		$this->permission_check('accounts_add');
		$data=$this->data;
		$data['page_title']=$this->lang->line('add_account');
		$this->load->view('accounts/accounts',$data);
	}
	
	
	public function newaccounts(){
		$this->permission_check('accounts_add');
		$this->form_validation->set_rules('account_code', 'Account Code', 'trim|required');
		$this->form_validation->set_rules('account_name', 'Account Name', 'trim|required');
		$this->form_validation->set_rules('opening_balance', 'Oprning Balance', 'trim|required');
		
		if ($this->form_validation->run() == TRUE) {
			$result=$this->accounts->verify_and_save();
			echo $result;
		} else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}
	}
	public function update($id){
		$this->permission_check('accounts_edit');
		$this->belong_to('ac_accounts',$id);
		$data=$this->data;
		$result=$this->accounts->get_details($id,$data);
		$data=array_merge($data,$result);
		$data['page_title']=$this->lang->line('accounts');
		$this->load->view('accounts/accounts', $data);
	}
	public function update_accounts(){
		$this->form_validation->set_rules('account_code', 'Account Code', 'trim|required');
		$this->form_validation->set_rules('account_name', 'Account Name', 'trim|required');
		//$this->form_validation->set_rules('opening_balance', 'Oprning Balance', 'trim|required');

		if ($this->form_validation->run() == TRUE) {
			$result=$this->accounts->update_accounts();
			echo $result;
		} else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}
	}

	public function ajax_list()
	{
		$list = $this->accounts->get_datatables();
		
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $accounts) {
			$no++;
			$row = array();
			$row[] = ($accounts->delete_bit) ? '<span data-toggle="tooltip" title="Resticted" class="text-danger fa fa-fw fa-ban"></span>' : '<input type="checkbox" name="checkbox[]" value='.$accounts->id.' class="checkbox column_checkbox" >';
			//$row[] = get_store_name($accounts->store_id);
			$row[] = $accounts->account_code;
			$row[] = $accounts->account_name;
			$row[] = get_account_name($accounts->parent_id);
			$row[] = store_number_format($accounts->balance);
			
			$row[] = ($accounts->created_by);			
				     $str2 = '<div class="btn-group" title="View Account">
										<a class="btn btn-primary btn-o dropdown-toggle" data-toggle="dropdown" href="#">
											Action <span class="caret"></span>
										</a>
										<ul role="menu" class="dropdown-menu dropdown-light pull-right">';

											if($this->permissions('accounts_edit'))
											$str2.='<li>
												<a data-toggle="tooltip" title="Edit Record ?" href="'.base_url().'accounts/update/'.$accounts->id.'">
													<i class="fa fa-fw fa-edit text-blue"></i>Edit
												</a>
											</li>';

											if($this->permissions('accounts_view'))
											$str2.='<li>
												<a data-toggle="tooltip" title="Click to check Account!" href="'.base_url().'accounts/book/'.$accounts->id.'">
													<i class="fa fa-fw fa-book text-blue"></i>Account Book
												</a>
											</li>';

											if($this->permissions('accounts_delete') && !$accounts->delete_bit)
											$str2.='<li>
												<a style="cursor:pointer" data-toggle="tooltip" title="Delete Record ?" onclick="delete_accounts('.$accounts->id.')">
													<i class="fa fa-fw fa-trash text-red"></i>Delete
												</a>
											</li>
											
										</ul>
									</div>';			
			$row[] = $str2;

			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->accounts->count_all(),
						"recordsFiltered" => $this->accounts->count_filtered(),
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}
	
	public function delete_accounts(){
		$this->permission_check_with_msg('accounts_delete');
		$id=$this->input->post('q_id');
		echo $this->accounts->delete_accounts_from_table($id);
	}
	public function multi_delete_accounts(){
		$this->permission_check_with_msg('accounts_delete');
		$ids=implode (",",$_POST['checkbox']);
		echo $this->accounts->delete_accounts_from_table($ids);
	}
	

	public function migrate_cash_accounts(){
		$this->permission_check('accounts_add');
		$store_id = get_current_store_id();
		$cash_account_id = get_cash_account_id();

		$updated = 0;

		// 1. Update db_salespayments: set account_id for cash payments without one
		$this->db->query("UPDATE db_salespayments SET account_id = ? WHERE store_id = ? AND payment_type = 'cash' AND (account_id IS NULL OR account_id = '' OR account_id = 0)", array($cash_account_id, $store_id));
		$updated += $this->db->affected_rows();

		// 2. Update db_purchasepayments
		$this->db->query("UPDATE db_purchasepayments SET account_id = ? WHERE store_id = ? AND payment_type = 'cash' AND (account_id IS NULL OR account_id = '' OR account_id = 0)", array($cash_account_id, $store_id));
		$updated += $this->db->affected_rows();

		// 3. Update db_expense
		$this->db->query("UPDATE db_expense SET account_id = ? WHERE store_id = ? AND payment_type = 'cash' AND (account_id IS NULL OR account_id = '' OR account_id = 0)", array($cash_account_id, $store_id));
		$updated += $this->db->affected_rows();

		// 4. Update db_salespaymentsreturn
		$this->db->query("UPDATE db_salespaymentsreturn SET account_id = ? WHERE store_id = ? AND payment_type = 'cash' AND (account_id IS NULL OR account_id = '' OR account_id = 0)", array($cash_account_id, $store_id));
		$updated += $this->db->affected_rows();

		// 5. Update db_purchasepaymentsreturn
		$this->db->query("UPDATE db_purchasepaymentsreturn SET account_id = ? WHERE store_id = ? AND payment_type = 'cash' AND (account_id IS NULL OR account_id = '' OR account_id = 0)", array($cash_account_id, $store_id));
		$updated += $this->db->affected_rows();

		// 6. Update ac_moneydeposits where debit_account_id matches cash-like names but isn't set to the proper Cash Account
		$this->db->query("UPDATE ac_moneydeposits SET debit_account_id = ? WHERE store_id = ? AND debit_account_id != ? AND debit_account_id IN (SELECT id FROM ac_accounts WHERE store_id = ? AND (LOWER(account_name) LIKE '%cash%' OR LOWER(account_name) LIKE '%till%'))", array($cash_account_id, $store_id, $cash_account_id, $store_id));
		$updated += $this->db->affected_rows();

		// 7. Rebuild ac_transactions for cash sales that now have an account_id
		$payments = $this->db->query("
			SELECT sp.id, sp.payment as amount, sp.payment_note as note, sp.payment_code as code, sp.customer_id, sp.created_date, sp.created_time
			FROM db_salespayments sp
			WHERE sp.store_id = ? AND sp.payment_type = 'cash' AND sp.account_id = ?
			  AND NOT EXISTS (
				  SELECT 1 FROM ac_transactions at
				  WHERE at.ref_salespayments_id = sp.id AND at.transaction_type = 'SALES PAYMENT'
			  )
		", array($store_id, $cash_account_id))->result();
		foreach($payments as $p){
			insert_account_transaction(array(
				'transaction_type'   => 'SALES PAYMENT',
				'reference_table_id'  => $p->id,
				'debit_account_id'  => null,
				'credit_account_id'  => $cash_account_id,
				'debit_amt'          => 0,
				'credit_amt'         => $p->amount,
				'process'            => 'SAVE',
				'note'               => $p->note,
				'transaction_date'   => $p->created_date,
				'payment_code'        => $p->code,
				'customer_id'        => $p->customer_id,
				'supplier_id'        => null,
			));
			$updated++;
		}

		// 8. Rebuild ac_transactions for cash expenses without one
		$expenses = $this->db->query("
			SELECT e.id, e.expense_amt as amount, e.note, e.expense_code as code, e.created_date, e.created_time
			FROM db_expense e
			WHERE e.store_id = ? AND e.payment_type = 'cash' AND e.account_id = ?
			  AND NOT EXISTS (
				  SELECT 1 FROM ac_transactions at
				  WHERE at.ref_expense_id = e.id AND at.transaction_type = 'EXPENSE PAYMENT'
			  )
		", array($store_id, $cash_account_id))->result();
		foreach($expenses as $e){
			insert_account_transaction(array(
				'transaction_type'   => 'EXPENSE PAYMENT',
				'reference_table_id'  => $e->id,
				'debit_account_id'  => $cash_account_id,
				'credit_account_id'  => null,
				'debit_amt'          => $e->amount,
				'credit_amt'         => 0,
				'process'            => 'SAVE',
				'note'               => $e->note,
				'transaction_date'   => $e->created_date,
				'payment_code'        => $e->code,
				'customer_id'        => null,
				'supplier_id'        => null,
			));
			$updated++;
		}

		update_account_balance($cash_account_id);

		echo "Migration complete. Cash Account ID: ".$cash_account_id." | Records touched: ".$updated;
	}

}
