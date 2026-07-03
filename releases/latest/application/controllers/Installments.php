<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Installments extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!mp_feature_enabled('payplan')){
			$this->show_access_denied_page();
			return;
		}
		$this->load->model('installments_model','installments');
	}

	/* ─── LIST ALL PLANS ─── */
	public function index(){
		$this->permission_check('installment_plans');
		$data = $this->data;
		$data['page_title'] = 'Installment Plans';
		$this->load->view('installments/list', $data);
	}

	/* ─── AJAX LIST FOR DATATABLES ─── */
	public function ajax_list(){
		$this->permission_check('installment_plans');
		$list = $this->installments->get_plans();
		$data = array();
		$no = 0;
		foreach($list as $plan){
			$no++;
			$row = array();
			$row[] = $plan->plan_code;
			$row[] = '<a href="'.base_url('customers/edit/'.$plan->customer_id).'">'.$plan->customer_name.'</a><br><small>'.$plan->mobile.'</small>';
			$row[] = number_format($plan->total_amount, 2);
			$row[] = number_format($plan->down_payment_amount, 2) . ($plan->down_payment_paid ? ' <i class="fa fa-check text-green"></i>' : '');
			$row[] = $plan->installment_count . ' x ' . number_format($plan->installment_amount, 2);
			$row[] = ucfirst($plan->frequency);
			$row[] = show_date($plan->first_due_date);

			$status_badge = '';
			if($plan->status == 'active'){
				$status_badge = '<span class="label label-info">Active</span>';
			}elseif($plan->status == 'completed'){
				$status_badge = '<span class="label label-success">Completed</span>';
			}elseif($plan->status == 'cancelled'){
				$status_badge = '<span class="label label-default">Cancelled</span>';
			}elseif($plan->status == 'defaulted'){
				$status_badge = '<span class="label label-danger">Defaulted</span>';
			}
			$row[] = $status_badge;

			$balance = floatval($plan->total_amount) - floatval($plan->total_paid);
			$row[] = number_format($balance, 2);

			$actions = '<a href="'.base_url('installments/view/'.$plan->id).'" class="btn btn-xs btn-primary" title="View"><i class="fa fa-eye"></i></a>';
			if($plan->status == 'active'){
				$actions .= ' <a href="'.base_url('installments/pay/'.$plan->id).'" class="btn btn-xs btn-success" title="Pay"><i class="fa fa-money"></i></a>';
			}
			$row[] = $actions;
			$data[] = $row;
		}
		$output = array('data' => $data);
		echo json_encode($output);
	}

	/* ─── VIEW PLAN DETAILS ─── */
	public function view($plan_id){
		$this->permission_check('installment_plans');
		$data = $this->data;
		$data['page_title'] = 'Installment Plan Details';
		$data['plan'] = $this->installments->get_plan($plan_id);
		if(!$data['plan']){
			show_404();
		}
		$this->load->view('installments/view', $data);
	}

	/* ─── PAYMENT FORM ─── */
	public function pay($plan_id){
		$this->permission_check('installment_payment');
		$data = $this->data;
		$data['page_title'] = 'Record Installment Payment';
		$data['plan'] = $this->installments->get_plan($plan_id);
		if(!$data['plan']){
			show_404();
		}
		$data['accounts'] = $this->db->where('store_id', get_current_store_id())
			->where('status', 1)->get('ac_accounts')->result();
		$this->load->view('installments/pay', $data);
	}

	/* ─── SAVE PAYMENT ─── */
	public function save_payment(){
		$this->permission_check('installment_payment');
		$this->form_validation->set_rules('payment_id', 'Payment', 'trim|required');
		$this->form_validation->set_rules('amount_paid', 'Amount', 'trim|required|numeric');

		if($this->form_validation->run() == TRUE){
			$result = $this->installments->record_payment(array(
				'payment_id'    => $this->input->post('payment_id'),
				'amount_paid'   => $this->input->post('amount_paid'),
				'payment_type'  => $this->input->post('payment_type'),
				'payment_note'  => $this->input->post('payment_note'),
				'account_id'    => $this->input->post('account_id'),
			));
			if($result['status'] == 'success'){
				echo $result['message'];
			}else{
				echo $result['message'];
			}
		}else{
			echo "Please fill all required fields.";
		}
	}

	/* ─── CUSTOMER ACTIVE PLANS AJAX ─── */
	public function customer_plans($customer_id){
		$plans = $this->installments->get_customer_active_plans($customer_id);
		echo json_encode($plans);
	}

	/* ─── CREATE PLAN FROM POS (AJAX) ─── */
	public function create_from_pos(){
		$this->permission_check('installment_plans');
		$this->form_validation->set_rules('customer_id', 'Customer', 'trim|required');
		$this->form_validation->set_rules('total_amount', 'Total Amount', 'trim|required|numeric');
		$this->form_validation->set_rules('installment_count', 'Installment Count', 'trim|required|integer');
		$this->form_validation->set_rules('installment_amount', 'Installment Amount', 'trim|required|numeric');
		$this->form_validation->set_rules('first_due_date', 'First Due Date', 'trim|required');

		if($this->form_validation->run() == TRUE){
			$plan_id = $this->installments->create_plan(array(
				'store_id'            => get_current_store_id(),
				'sales_id'            => $this->input->post('sales_id'),
				'customer_id'         => $this->input->post('customer_id'),
				'total_amount'        => $this->input->post('total_amount'),
				'down_payment_amount' => $this->input->post('down_payment_amount'),
				'down_payment_paid'   => $this->input->post('down_payment_paid'),
				'down_payment_type'   => $this->input->post('down_payment_type'),
				'installment_count'   => $this->input->post('installment_count'),
				'installment_amount'  => $this->input->post('installment_amount'),
				'frequency'           => $this->input->post('frequency'),
				'first_due_date'      => date('Y-m-d', strtotime($this->input->post('first_due_date'))),
				'late_fee_per_day'    => $this->input->post('late_fee_per_day'),
			));
			if($plan_id){
				$plan = $this->installments->get_plan($plan_id);
				echo json_encode(array('status'=>'success','plan_id'=>$plan_id,'plan_code'=>$plan->plan_code));
			}else{
				echo json_encode(array('status'=>'error','message'=>'Failed to create plan'));
			}
		}else{
			echo json_encode(array('status'=>'error','message'=>validation_errors()));
		}
	}

	/* ─── DASHBOARD STATS ─── */
	public function dashboard_stats(){
		$stats = $this->installments->get_dashboard_stats();
		echo json_encode($stats);
	}

	/* ─── RUN OVERDUE CHECK ─── */
	public function check_overdue(){
		$count = $this->installments->check_overdue();
		echo json_encode(array('overdue_updated' => $count));
	}
}
