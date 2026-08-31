<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Cashier_shifts_model
 *
 * Cashier Shift Reconciliation (Z-Report) for MartPoint.
 * Handles till open/close, expected-vs-counted variance per
 * payment method, and optional manager sign-off.
 *
 * Attribution: db_salespayments.created_by stores the cashier
 * USERNAME (varchar). Shifts are scoped to a cashier by matching
 * that username within the shift's date window.
 */
class Cashier_shifts_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	/** Ensure the shift tables exist (graceful for installs not yet migrated). */
	private function ensure_tables(){
		if(!$this->db->table_exists('db_cashier_shifts')){
			$sql = @file_get_contents(APPPATH.'../scripts/sql/martpoint_cashier_shifts.sql');
			if($sql){
				$statements = array_filter(array_map('trim', explode(';', $sql)));
				foreach($statements as $s){ if(stripos($s, 'CREATE TABLE') === 0){ $this->db->query($s); } }
			}
		}
	}

	/** Get the currently open shift for a user (or store-wide for managers). */
	public function get_open_shift($store_id = null, $user_id = null){
		$this->ensure_tables();
		if(!$this->db->table_exists('db_cashier_shifts')){ return null; }
		if(empty($store_id)){ $store_id = get_current_store_id(); }
		if(empty($user_id)){ $user_id = get_current_user_id(); }
		$this->db->select("s.*, t.till_name, a.account_name, a.balance as till_balance");
		$this->db->from("db_cashier_shifts s");
		$this->db->join("db_tills t", "t.id = s.till_id", "left");
		$this->db->join("ac_accounts a", "a.id = s.cash_account_id", "left");
		$this->db->where('s.store_id', $store_id);
		$this->db->where('s.cashier_user_id', $user_id);
		$this->db->where('s.status', 'open');
		$this->db->order_by('s.id', 'desc');
		return $this->db->get()->row();
	}

	/** Open a new shift for the current cashier. */
	public function open_shift($till_id, $opening_float){
		$this->ensure_tables();
		if(!$this->db->table_exists('db_cashier_shifts')){
			return array('status'=>'error','message'=>'Shift reconciliation tables are not installed. Run scripts/sql/martpoint_cashier_shifts.sql.');
		}
		$store_id  = get_current_store_id();
		$user_id   = get_current_user_id();
		$username  = $this->session->userdata('inv_username');

		$existing = $this->get_open_shift($store_id, $user_id);
		if($existing){
			return array('status'=>'error','message'=>'You already have an open shift. Close it before opening a new one.');
		}

		// Resolve the selected till
		$till_label = '';
		$cash_account_id = null;
		if($this->db->table_exists('db_tills') && !empty($till_id)){
			$till = $this->db->where('id', $till_id)->where('store_id', $store_id)->where('status', 1)->get('db_tills')->row();
			if(!$till){
				return array('status'=>'error','message'=>'Selected till is not available.');
			}
			$till_label = $till->till_name;
			$cash_account_id = $till->account_id;
		} else {
			// Backward compat / no till table: use default cash account
			$cash_account_id = get_cash_account_id();
		}

		$opening_float = floatval($opening_float);
		$now = date('Y-m-d H:i:s');

		$this->db->insert('db_cashier_shifts', array(
			'store_id'           => $store_id,
			'cashier_user_id'    => $user_id,
			'cashier_username'   => $username,
			'till_id'            => $till_id,
			'cash_account_id'    => $cash_account_id,
			'till_label'         => $till_label,
			'opening_float'      => $opening_float,
			'opened_at'          => $now,
			'status'             => 'open',
			'total_expected_cash'=> $opening_float,
			'approval_status'    => 'not_required',
		));

		$id = $this->db->insert_id();
		$code = 'ZR/'.date('Y/m').'/'.str_pad($id, 4, '0', STR_PAD_LEFT);
		$this->db->where('id', $id)->update('db_cashier_shifts', array('shift_code' => $code));

		// Post opening float to the till cash account as an opening-balance credit
		if(!empty($cash_account_id) && $opening_float > 0){
			$payment_code = get_init_code('opening_float');
			insert_account_transaction(array(
				'transaction_type'   => 'OPENING BALANCE',
				'reference_table_id' => $id,
				'credit_account_id'  => $cash_account_id,
				'credit_amt'         => $opening_float,
				'debit_account_id'   => null,
				'debit_amt'          => 0,
				'transaction_date'   => date('Y-m-d'),
				'payment_code'       => $payment_code,
				'note'               => 'Opening float for shift '.$code.($till_label ? ' ('.$till_label.')' : ''),
			));
		}

		return array('status'=>'success','shift_id'=>$id,'shift_code'=>$code,'cash_account_id'=>$cash_account_id);
	}

	/**
	 * Compute expected amounts per payment method for a shift,
	 * based on payments attributed to the cashier within the
	 * shift's date window.
	 */
	public function compute_expected($shift){
		if(!$shift){ return array('methods'=>array(),'expected_cash'=>0,'expected_other'=>0,'transactions'=>0); }

		$store_id  = $shift->store_id;
		$username  = $shift->cashier_username;
		$from_date = date('Y-m-d', strtotime($shift->opened_at));
		$to_date   = $shift->closed_at ? date('Y-m-d', strtotime($shift->closed_at)) : date('Y-m-d');
		$opening   = floatval($shift->opening_float);

		// Per-method map: payment_type => [expected, affects_cash, txn_count]
		$methods = array();

		$add_expected = function($payment_type, $amount, $affects_cash, $txn=0) use (&$methods){
			$key = $payment_type ?: 'Unknown';
			if(!isset($methods[$key])){
				$methods[$key] = array('payment_type'=>$key,'affects_cash'=>intval($affects_cash),'expected'=>0.0,'txn_count'=>0);
			}
			$methods[$key]['expected'] += floatval($amount);
			$methods[$key]['txn_count'] += intval($txn);
		};

		// Cash-affecting flag lookup per code
		$modes = $this->db->where('store_id', $store_id)->get('db_payment_modes')->result();
		$affects_map = array();
		foreach($modes as $m){ $affects_map[$m->code] = intval($m->affects_cash_in_hand); }
		$affects_of = function($code) use ($affects_map){ return isset($affects_map[$code]) ? $affects_map[$code] : 0; };

		// Sales payments (in)
		if($this->db->table_exists('db_salespayments')){
			$this->db->select("payment_type, SUM(payment) as amt, COUNT(*) as txn");
			$this->db->from("db_salespayments");
			$this->db->where("store_id", $store_id);
			$this->db->where("created_by", $username);
			$this->db->where("payment_date >=", $from_date);
			$this->db->where("payment_date <=", $to_date);
			$this->db->group_by("payment_type");
			foreach($this->db->get()->result() as $r){
				$add_expected($r->payment_type, $r->amt, $affects_of($r->payment_type), $r->txn);
			}
		}

		// Sales return payments (out — subtract)
		if($this->db->table_exists('db_salespaymentsreturn')){
			$this->db->select("payment_type, SUM(payment) as amt, COUNT(*) as txn");
			$this->db->from("db_salespaymentsreturn");
			$this->db->where("store_id", $store_id);
			$this->db->where("created_by", $username);
			$this->db->where("payment_date >=", $from_date);
			$this->db->where("payment_date <=", $to_date);
			$this->db->group_by("payment_type");
			foreach($this->db->get()->result() as $r){
				$add_expected($r->payment_type, -$r->amt, $affects_of($r->payment_type), 0);
			}
		}

		// Expenses paid by this cashier (out — subtract, cash-affecting only)
		if($this->db->table_exists('db_expense')){
			$this->db->select("payment_type, SUM(expense_amt) as amt");
			$this->db->from("db_expense");
			$this->db->where("store_id", $store_id);
			$this->db->where("created_by", $username);
			$this->db->where("expense_date >=", $from_date);
			$this->db->where("expense_date <=", $to_date);
			$this->db->where("payment_type IN (SELECT code FROM db_payment_modes WHERE store_id=".$this->db->escape($store_id)." AND affects_cash_in_hand=1)", NULL, FALSE);
			$this->db->group_by("payment_type");
			foreach($this->db->get()->result() as $r){
				$add_expected($r->payment_type, -$r->amt, 1, 0);
			}
		}

		// Add opening float to the cash method
		$cash_code = null;
		foreach($affects_map as $code => $aff){ if($aff === 1){ $cash_code = $code; break; } }
		if($cash_code && $opening > 0){
			if(!isset($methods[$cash_code])){
				$methods[$cash_code] = array('payment_type'=>$cash_code,'affects_cash'=>1,'expected'=>0.0,'txn_count'=>0);
			}
			$methods[$cash_code]['expected'] += $opening;
		}

		// Totals
		$expected_cash = 0.0; $expected_other = 0.0; $transactions = 0;
		foreach($methods as $m){
			if($m['affects_cash'] === 1){ $expected_cash += $m['expected']; }
			else { $expected_other += $m['expected']; }
			$transactions += $m['txn_count'];
		}

		return array(
			'methods'         => array_values($methods),
			'expected_cash'   => $expected_cash,
			'expected_other'  => $expected_other,
			'transactions'    => $transactions,
		);
	}

	/**
	 * Close a shift: record counted amounts per method, compute
	 * variance, optionally verify a manager sign-off PIN.
	 *
	 * $counts = array of [payment_type, counted_amount]
	 * $manager_pin = optional PIN/password for sign-off
	 */
	public function close_shift($shift_id, $counts, $manager_pin = '', $note = ''){
		$this->ensure_tables();
		if(!$this->db->table_exists('db_cashier_shifts')){
			return array('status'=>'error','message'=>'Shift tables not installed.');
		}
		$store_id = get_current_store_id();
		$shift = $this->db->where('id', $shift_id)->where('store_id', $store_id)->get('db_cashier_shifts')->row();
		if(!$shift){ return array('status'=>'error','message'=>'Shift not found.'); }
		if($shift->status !== 'open'){ return array('status'=>'error','message'=>'This shift is already closed.'); }

		// Permission: cashier can close own shift; others need cashier_shifts_manage
		if(intval($shift->cashier_user_id) !== intval(get_current_user_id())){
			if(!$this->permissions('cashier_shifts_manage')){
				return array('status'=>'error','message'=>'You can only close your own shift.');
			}
		}

		$expected = $this->compute_expected($shift);
		$expected_map = array();
		foreach($expected['methods'] as $m){ $expected_map[$m['payment_type']] = $m; }

		// Build count rows from submitted counts; merge in any expected methods not counted
		$seen = array();
		$count_rows = array();
		$tot_counted_cash = 0.0; $tot_counted_other = 0.0;

		foreach($counts as $c){
			$pt = trim($c['payment_type']);
			$counted = floatval($c['counted_amount']);
			if($pt === '' && $counted == 0){ continue; }
			$seen[$pt] = true;
			$exp = isset($expected_map[$pt]) ? $expected_map[$pt] : array('expected'=>0,'affects_cash'=>0,'txn_count'=>0);
			$affects = intval($exp['affects_cash']);
			$variance = $counted - floatval($exp['expected']);
			$count_rows[] = array(
				'shift_id'           => $shift_id,
				'store_id'           => $store_id,
				'payment_type'       => $pt,
				'affects_cash_in_hand'=> $affects,
				'expected_amount'    => floatval($exp['expected']),
				'counted_amount'     => $counted,
				'variance'           => $variance,
				'txn_count'          => intval(isset($exp['txn_count'])?$exp['txn_count']:0),
			);
			if($affects === 1){ $tot_counted_cash += $counted; } else { $tot_counted_other += $counted; }
		}
		// Include expected methods the cashier did not count (variance = -expected)
		foreach($expected_map as $pt => $exp){
			if(isset($seen[$pt])){ continue; }
			$count_rows[] = array(
				'shift_id'           => $shift_id,
				'store_id'           => $store_id,
				'payment_type'       => $pt,
				'affects_cash_in_hand'=> intval($exp['affects_cash']),
				'expected_amount'    => floatval($exp['expected']),
				'counted_amount'     => 0.0,
				'variance'           => -floatval($exp['expected']),
				'txn_count'          => intval($exp['txn_count']),
			);
		}

		$tot_expected_cash  = floatval($expected['expected_cash']);
		$tot_expected_other = floatval($expected['expected_other']);

		// Manager sign-off (optional)
		$manager_user_id = null; $manager_username = null; $approval_status = 'not_required';
		if(!empty($manager_pin)){
			$approval_status = 'pending';
			if($this->db->table_exists('db_users')){
				$this->db->select("u.id, u.username, u.password, u.approval_pin, r.role_name");
				$this->db->from("db_users u");
				$this->db->join("db_roles r", "r.id = u.role_id", "left");
				$this->db->where("u.status", 1);
				$users = $this->db->get()->result();
				foreach($users as $u){
					$role_ok = ($u->id == 1 || stripos($u->role_name, 'Admin') !== false ||
						stripos($u->role_name, 'Owner') !== false || stripos($u->role_name, 'Manager') !== false);
					if(!$role_ok){ continue; }
					$pin_hash = !empty($u->approval_pin) ? $u->approval_pin : $u->password;
					if(md5($manager_pin) === $pin_hash){
						$manager_user_id = $u->id;
						$manager_username = $u->username;
						$approval_status = 'approved';
						break;
					}
				}
				if($approval_status === 'pending'){
					return array('status'=>'error','message'=>'Manager sign-off failed: PIN did not match any authorised manager.');
				}
			}
		}

		$now = date('Y-m-d H:i:s');
		$this->db->trans_begin();

		$this->db->where('id', $shift_id)->update('db_cashier_shifts', array(
			'status'              => 'closed',
			'closed_at'           => $now,
			'total_expected_cash' => $tot_expected_cash,
			'total_counted_cash'  => $tot_counted_cash,
			'cash_variance'       => $tot_counted_cash - $tot_expected_cash,
			'total_expected_other'=> $tot_expected_other,
			'total_counted_other' => $tot_counted_other,
			'other_variance'      => $tot_counted_other - $tot_expected_other,
			'transactions'        => intval($expected['transactions']),
			'manager_user_id'     => $manager_user_id,
			'manager_username'    => $manager_username,
			'approval_status'     => $approval_status,
			'close_note'          => $note,
		));

		if(!empty($count_rows)){
			$this->db->insert_batch('db_cashier_shift_counts', $count_rows);
		}

		// Log to approval logs if sign-off happened
		if($manager_user_id && $this->db->table_exists('db_approval_logs')){
			$this->db->insert('db_approval_logs', array(
				'store_id'           => $store_id,
				'action_type'        => 'shift_close',
				'approval_type'      => 'shift_close',
				'reference_id'       => $shift_id,
				'reference_table'    => 'db_cashier_shifts',
				'requesting_user_id' => $shift->cashier_user_id,
				'approving_user_id'  => $manager_user_id,
				'status'             => 'approved',
				'notes'              => 'Z-Report close signed off: '.$shift->shift_code,
				'created_at'         => $now,
			));
		}

		// Post closing cash variance adjustment to the till account so account balance = counted
		$cash_variance = floatval($tot_counted_cash - $tot_expected_cash);
		if(!empty($shift->cash_account_id) && abs($cash_variance) > 0.001){
			$payment_code = get_init_code('shift_close');
			if($cash_variance > 0){
				// Overage: credit till to bring balance up to counted
				insert_account_transaction(array(
					'transaction_type'   => 'SHIFT CLOSE',
					'reference_table_id' => $shift_id,
					'credit_account_id'  => $shift->cash_account_id,
					'credit_amt'         => $cash_variance,
					'transaction_date'   => date('Y-m-d'),
					'payment_code'       => $payment_code,
					'note'               => 'Cash overage on close of '.$shift->shift_code,
				));
			} else {
				// Shortage: debit till to bring balance down to counted
				insert_account_transaction(array(
					'transaction_type'   => 'SHIFT CLOSE',
					'reference_table_id' => $shift_id,
					'debit_account_id'   => $shift->cash_account_id,
					'debit_amt'          => abs($cash_variance),
					'transaction_date'   => date('Y-m-d'),
					'payment_code'       => $payment_code,
					'note'               => 'Cash shortage on close of '.$shift->shift_code,
				));
			}
		}

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return array('status'=>'error','message'=>'Failed to close shift. Please try again.');
		}
		$this->db->trans_commit();

		return array(
			'status'        => 'success',
			'shift_id'      => $shift_id,
			'cash_variance' => $tot_counted_cash - $tot_expected_cash,
		);
	}

	/** Get a single shift with its per-method counts. */
	public function get_shift_detail($shift_id){
		$this->ensure_tables();
		if(!$this->db->table_exists('db_cashier_shifts')){ return null; }
		$store_id = get_current_store_id();
		$shift = $this->db->where('id', $shift_id)->where('store_id', $store_id)->get('db_cashier_shifts')->row();
		if(!$shift){ return null; }
		$counts = $this->db->where('shift_id', $shift_id)->order_by('affects_cash_in_hand','desc')->get('db_cashier_shift_counts')->result();
		return array('shift'=>$shift, 'counts'=>$counts);
	}

	/** Datatable-friendly list of shifts (history). */
	public function get_shifts_list($from_date, $to_date, $cashier_id = '', $store_id = ''){
		$this->ensure_tables();
		if(!$this->db->table_exists('db_cashier_shifts')){
			return array();
		}
		if(empty($store_id)){ $store_id = get_current_store_id(); }
		$from_db = system_fromatted_date($from_date);
		$to_db   = system_fromatted_date($to_date);

		$this->db->select("s.*, u.username, u.first_name, u.last_name");
		$this->db->from("db_cashier_shifts s");
		$this->db->join("db_users u", "u.id = s.cashier_user_id", "left");
		$this->db->where("s.store_id", $store_id);
		$this->db->where("DATE(s.opened_at) >=", $from_db, FALSE);
		$this->db->where("DATE(s.opened_at) <=", $to_db, FALSE);
		if(!empty($cashier_id)){ $this->db->where("s.cashier_user_id", $cashier_id); }
		$this->db->order_by("s.opened_at", "desc");
		return $this->db->get()->result();
	}

	/** List active tills for the current cashier, optionally including shared tills. */
	public function get_tills_for_user($user_id = null){
		if(empty($user_id)){ $user_id = get_current_user_id(); }
		$store_id = get_current_store_id();
		if(!$this->db->table_exists('db_tills')){ return array(); }
		$this->db->select("t.*, a.account_name, a.balance");
		$this->db->from("db_tills t");
		$this->db->join("ac_accounts a", "a.id = t.account_id", "left");
		$this->db->where("t.store_id", $store_id);
		$this->db->where("t.status", 1);
		$this->db->where("t.cashier_user_id", (int)$user_id);
		$this->db->order_by("t.is_default", "desc");
		$this->db->order_by("t.till_name", "asc");
		return $this->db->get()->result();
	}

	/** List active cashiers (users) for the filter dropdown. */
	public function get_cashiers(){
		$store_id = get_current_store_id();
		$this->db->select("u.id, u.username, u.first_name, u.last_name");
		$this->db->from("db_users u");
		$this->db->where("u.status", 1);
		$this->db->order_by("u.username", "asc");
		return $this->db->get()->result();
	}

	/** Quick permission helper (mirrors MY_Controller). */
	private function permissions($key){
		$CI =& get_instance();
		return $CI->permissions($key);
	}
}
