<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Installments_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	/* ─── CREATE INSTALLMENT PLAN FROM SALE ─── */
	public function create_plan($data){
		$this->db->trans_begin();

		$plan_code = 'IN' . date('Y') . '/' . str_pad(get_current_store_id(), 2, '0', STR_PAD_LEFT) . '/';
		$count_id  = get_count_id('db_installment_plans');
		$plan_code = $plan_code . $count_id;

		$plan = array(
			'store_id'            => $data['store_id'],
			'sales_id'            => isset($data['sales_id']) ? $data['sales_id'] : null,
			'customer_id'         => $data['customer_id'],
			'plan_code'           => $plan_code,
			'total_amount'        => $data['total_amount'],
			'down_payment_amount' => $data['down_payment_amount'],
			'down_payment_paid'   => $data['down_payment_paid'] ? 1 : 0,
			'installment_count'   => $data['installment_count'],
			'installment_amount'  => $data['installment_amount'],
			'frequency'           => $data['frequency'],
			'first_due_date'      => $data['first_due_date'],
			'late_fee_per_day'    => isset($data['late_fee_per_day']) ? $data['late_fee_per_day'] : 0,
			'created_by'          => $this->session->userdata('inv_username'),
			'created_date'        => date('Y-m-d'),
			'created_time'        => date('h:i:s a'),
		);

		$this->db->insert('db_installment_plans', $plan);
		$plan_id = $this->db->insert_id();

		if(!$plan_id){
			$this->db->trans_rollback();
			return false;
		}

		// Generate installment schedule
		$schedule = $this->_generate_schedule(
			$plan_id,
			$data['first_due_date'],
			$data['installment_count'],
			$data['installment_amount'],
			$data['frequency']
		);

		if(!empty($schedule)){
			$this->db->insert_batch('db_installment_payments', $schedule);
		}

		// If down payment was paid at POS, record it
		if($data['down_payment_paid'] && $data['down_payment_amount'] > 0){
			$dp_payment = array(
				'plan_id'            => $plan_id,
				'installment_number' => 0,
				'due_date'           => date('Y-m-d'),
				'amount_due'         => $data['down_payment_amount'],
				'amount_paid'        => $data['down_payment_amount'],
				'paid_date'          => date('Y-m-d'),
				'status'             => 'paid',
				'payment_type'       => isset($data['down_payment_type']) ? $data['down_payment_type'] : 'Cash',
				'created_by'         => $this->session->userdata('inv_username'),
				'created_date'       => date('Y-m-d'),
				'created_time'       => date('h:i:s a'),
			);
			$this->db->insert('db_installment_payments', $dp_payment);
		}

		$this->db->trans_commit();
		return $plan_id;
	}

	/* ─── GENERATE SCHEDULE ─── */
	private function _generate_schedule($plan_id, $first_due_date, $count, $amount, $frequency){
		$schedule = array();
		$interval = '+1 week';
		if($frequency == 'biweekly') $interval = '+2 weeks';
		if($frequency == 'monthly') $interval = '+1 month';

		$due = new DateTime($first_due_date);
		for($i = 1; $i <= $count; $i++){
			$schedule[] = array(
				'plan_id'            => $plan_id,
				'installment_number' => $i,
				'due_date'           => $due->format('Y-m-d'),
				'amount_due'         => $amount,
				'amount_paid'        => 0,
				'late_fee'           => 0,
				'status'             => 'pending',
			);
			$due->modify($interval);
		}
		return $schedule;
	}

	/* ─── RECORD PAYMENT ─── */
	public function record_payment($data){
		$this->db->trans_begin();

		$payment_id = $data['payment_id'];
		$amount_paid = floatval($data['amount_paid']);
		$payment_type = isset($data['payment_type']) ? $data['payment_type'] : 'Cash';
		$payment_note = isset($data['payment_note']) ? $data['payment_note'] : '';
		$account_id = isset($data['account_id']) ? $data['account_id'] : null;

		$installment = $this->db->where('id', $payment_id)->get('db_installment_payments')->row();
		if(!$installment){
			$this->db->trans_rollback();
			return array('status'=>'error','message'=>'Installment not found');
		}

		$plan = $this->db->where('id', $installment->plan_id)->get('db_installment_plans')->row();
		if(!$plan){
			$this->db->trans_rollback();
			return array('status'=>'error','message'=>'Plan not found');
		}

		$new_paid = floatval($installment->amount_paid) + $amount_paid;
		$status = ($new_paid >= floatval($installment->amount_due)) ? 'paid' : 'partial';

		$this->db->where('id', $payment_id)->update('db_installment_payments', array(
			'amount_paid'      => $new_paid,
			'paid_date'        => date('Y-m-d'),
			'status'           => $status,
			'payment_type'     => $payment_type,
			'payment_note'     => $payment_note,
			'account_id'       => $account_id,
		));

		// Update plan totals
		$this->_recalculate_plan($installment->plan_id);

		// Insert account transaction
		if(!empty($account_id)){
			$payment_code = get_init_code('installment_payment');
			$insert_bit = insert_account_transaction(array(
				'transaction_type'   => 'INSTALLMENT PAYMENT',
				'reference_table_id' => $payment_id,
				'debit_account_id'   => null,
				'credit_account_id'  => $account_id,
				'debit_amt'          => 0,
				'credit_amt'         => $amount_paid,
				'process'            => 'SAVE',
				'note'               => $payment_note,
				'transaction_date'   => date('Y-m-d'),
				'payment_code'       => $payment_code,
				'customer_id'        => $plan->customer_id,
				'supplier_id'        => null,
			));
			if(!$insert_bit){
				$this->db->trans_rollback();
				return array('status'=>'error','message'=>'Account transaction failed');
			}
		}

		$this->db->trans_commit();
		return array('status'=>'success','message'=>'Payment recorded','plan_id'=>$installment->plan_id);
	}

	/* ─── RECALCULATE PLAN TOTALS ─── */
	private function _recalculate_plan($plan_id){
		$total_paid = $this->db->select('COALESCE(SUM(amount_paid),0) as total')
			->where('plan_id', $plan_id)->get('db_installment_payments')->row()->total;

		$all_paid = $this->db->where('plan_id', $plan_id)
			->where('status !=', 'paid')->where('status !=', 'partial')
			->count_all_results('db_installment_payments') == 0;

		$status = $all_paid ? 'completed' : 'active';

		$this->db->where('id', $plan_id)->update('db_installment_plans', array(
			'total_paid' => $total_paid,
			'status'     => $status
		));
	}

	/* ─── CHECK OVERDUE INSTALLMENTS ─── */
	public function check_overdue(){
		$overdue = $this->db->where('status', 'pending')
			->where('due_date <', date('Y-m-d'))
			->get('db_installment_payments')->result();

		foreach($overdue as $row){
			$days_late = (new DateTime($row->due_date))->diff(new DateTime())->days;
			$plan = $this->db->where('id', $row->plan_id)->get('db_installment_plans')->row();
			$late_fee = $days_late * floatval($plan->late_fee_per_day);

			$this->db->where('id', $row->id)->update('db_installment_payments', array(
				'status'   => 'overdue',
				'late_fee' => $late_fee
			));
		}
		return count($overdue);
	}

	/* ─── LIST PLANS ─── */
	public function get_plans($filters = array()){
		$this->db->select('p.*, c.customer_name, c.mobile, c.sales_due');
		$this->db->from('db_installment_plans p');
		$this->db->join('db_customers c', 'c.id = p.customer_id', 'left');
		$this->db->where('p.store_id', get_current_store_id());

		if(!empty($filters['customer_id'])){
			$this->db->where('p.customer_id', $filters['customer_id']);
		}
		if(!empty($filters['status'])){
			$this->db->where('p.status', $filters['status']);
		}

		$this->db->order_by('p.id', 'DESC');
		return $this->db->get()->result();
	}

	/* ─── GET PLAN DETAILS ─── */
	public function get_plan($plan_id){
		$this->db->select('p.*, c.customer_name, c.mobile, c.sales_due');
		$this->db->from('db_installment_plans p');
		$this->db->join('db_customers c', 'c.id = p.customer_id', 'left');
		$this->db->where('p.id', $plan_id);
		$plan = $this->db->get()->row();
		if($plan){
			$plan->payments = $this->db->where('plan_id', $plan_id)
				->order_by('installment_number', 'ASC')
				->get('db_installment_payments')->result();
		}
		return $plan;
	}

	/* ─── GET CUSTOMER ACTIVE PLANS ─── */
	public function get_customer_active_plans($customer_id){
		return $this->db->where('customer_id', $customer_id)
			->where('status', 'active')
			->order_by('id', 'DESC')
			->get('db_installment_plans')->result();
	}

	/* ─── GET DASHBOARD STATS ─── */
	public function get_dashboard_stats(){
		$store_id = get_current_store_id();
		$stats = array();

		$stats['total_plans'] = $this->db->where('store_id', $store_id)
			->count_all_results('db_installment_plans');

		$stats['active_plans'] = $this->db->where('store_id', $store_id)
			->where('status', 'active')
			->count_all_results('db_installment_plans');

		$stats['total_outstanding'] = $this->db->select('COALESCE(SUM(total_amount - total_paid),0) as total')
			->where('store_id', $store_id)
			->where('status', 'active')
			->get('db_installment_plans')->row()->total;

		$stats['overdue_count'] = $this->db->where('status', 'overdue')
			->count_all_results('db_installment_payments');

		return $stats;
	}
}
