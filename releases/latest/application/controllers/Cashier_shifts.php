<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cashier_shifts
 *
 * Z-Report (Cashier Shift Reconciliation) for MartPoint.
 * - index()        : Z-Report history (read-only list of shifts)
 * - manage()       : open / close landing for the current cashier
 * - open()         : AJAX — open a new shift
 * - close_form()   : count form for the cashier's open shift
 * - expected_api() : JSON — live expected amounts per payment method
 * - close()        : AJAX — close shift with counted amounts + sign-off
 * - view()         : detail of a single shift
 */
class Cashier_shifts extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!mp_feature_enabled('cashier_shifts')){
			$this->show_feature_not_activated('cashier_shifts');
		}
		$this->load->model('cashier_shifts_model','shifts');
	}

	/** Z-Report history list. */
	public function index(){
		$this->permission_check('z_report');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('z_report');
		$data['cashiers']   = $this->shifts->get_cashiers();
		$this->load->view('report-z-report', $data);
	}

	/** AJAX: returns HTML rows for the history table. */
	public function show_z_report(){
		$this->permission_check('z_report');
		$from_date  = $this->input->post('from_date', TRUE);
		$to_date    = $this->input->post('to_date', TRUE);
		$cashier_id = $this->input->post('cashier_id', TRUE);
		$store_id   = $this->input->post('store_id', TRUE);
		if(empty($from_date)){ $from_date = date('d-m-Y', strtotime('-7 days')); }
		if(empty($to_date)){ $to_date = date('d-m-Y'); }

		$shifts = $this->shifts->get_shifts_list($from_date, $to_date, $cashier_id, $store_id);
		if(empty($shifts)){
			$cs = (store_module() && is_admin()) ? 11 : 10;
			echo "<tr><td class='text-center text-info' colspan='".$cs."'>No shifts recorded in this period.</td></tr>";
			exit;
		}
		$i = 0;
		foreach($shifts as $s){
			$i++;
			$cashier = trim(($s->first_name ?: '').' '.($s->last_name ?: ''));
			if($cashier === ''){ $cashier = $s->cashier_username ?: $s->username ?: '-'; }
			$cash_var = floatval($s->cash_variance);
			$oth_var  = floatval($s->other_variance);
			$var_class = (abs($cash_var) > 0.001) ? 'text-danger' : 'text-success';
			$status_lbl = ($s->status === 'open')
				? '<span class="label label-warning">Open</span>'
				: '<span class="label label-success">Closed</span>';
			echo "<tr>";
			echo "<td>".$i."</td>";
			if(store_module() && is_admin()){ echo "<td>".get_store_name($s->store_id)."</td>"; }
			echo "<td>".htmlspecialchars($s->shift_code)."</td>";
			echo "<td>".htmlspecialchars($cashier)."</td>";
			echo "<td>".htmlspecialchars($s->till_label ?: '-')."</td>";
			echo "<td>".show_date(date('d-m-Y', strtotime($s->opened_at)))."</td>";
			echo "<td>".($s->closed_at ? date('d-m-Y H:i', strtotime($s->closed_at)) : '-')."</td>";
			echo "<td class='text-right'>".store_number_format($s->total_expected_cash)."</td>";
			echo "<td class='text-right'>".store_number_format($s->total_counted_cash)."</td>";
			echo "<td class='text-right ".$var_class." text-bold'>".store_number_format($cash_var)."</td>";
			echo "<td>".$status_lbl."</td>";
			echo "<td><a href='".base_url('cashier_shifts/view/'.$s->id)."' class='btn btn-xs btn-info'><i class='fa fa-eye'></i></a></td>";
			echo "</tr>";
		}
		exit;
	}

	/** Open / close landing for the current cashier. */
	public function manage(){
		$this->permission_check('cashier_shifts_manage');
		$data = $this->data;
		$data['page_title'] = $this->lang->line('cashier_shifts');
		$data['open_shift'] = $this->shifts->get_open_shift();
		$data['tills']      = $this->shifts->get_tills_for_user();
		$this->load->view('cashier_shift/manage', $data);
	}

	/** AJAX: open a new shift. */
	public function open(){
		$this->permission_check_with_msg('cashier_shifts_manage');
		$till_id        = $this->input->post('till_id', TRUE);
		$opening_float  = $this->input->post('opening_float', TRUE);
		if($opening_float === '' || $opening_float === null){ $opening_float = 0; }
		if(!is_numeric($opening_float)){
			echo json_encode(array('status'=>'error','message'=>'Opening float must be a valid number.'));
			exit;
		}
		$res = $this->shifts->open_shift($till_id, $opening_float);
		echo json_encode($res);
		exit;
	}

	/** Close form: shows the cashier's open shift + live expected amounts. */
	public function close_form(){
		$this->permission_check('cashier_shifts_manage');
		$shift = $this->shifts->get_open_shift();
		if(!$shift){
			$this->session->set_flashdata('failed', 'You have no open shift to close.');
			redirect(base_url('cashier_shifts/manage'),'refresh');
		}
		$data = $this->data;
		$data['page_title'] = 'Close Shift — '.$shift->shift_code;
		$data['shift']      = $shift;
		$data['expected']   = $this->shifts->compute_expected($shift);
		$this->load->view('cashier_shift/close', $data);
	}

	/** JSON: live expected amounts for the current open shift. */
	public function expected_api(){
		$this->permission_check_with_msg('cashier_shifts_manage');
		$shift = $this->shifts->get_open_shift();
		if(!$shift){
			echo json_encode(array('status'=>'error','message'=>'No open shift.'));
			exit;
		}
		echo json_encode(array('status'=>'success','shift_id'=>$shift->id,'expected'=>$this->shifts->compute_expected($shift)));
		exit;
	}

	/** AJAX: close the shift with counted amounts. */
	public function close(){
		$this->permission_check_with_msg('cashier_shifts_manage');
		$shift_id    = $this->input->post('shift_id', TRUE);
		$counts_json = $this->input->post('counts', TRUE);
		$manager_pin = $this->input->post('manager_pin', TRUE);
		$note        = $this->input->post('close_note', TRUE);

		if(empty($shift_id)){
			echo json_encode(array('status'=>'error','message'=>'Missing shift id.'));
			exit;
		}
		$counts = json_decode($counts_json, true);
		if(!is_array($counts)){
			echo json_encode(array('status'=>'error','message'=>'Invalid count data.'));
			exit;
		}
		$res = $this->shifts->close_shift($shift_id, $counts, $manager_pin, $note);
		echo json_encode($res);
		exit;
	}

	/** Detail view of a single shift. */
	public function view($id){
		$this->permission_check('z_report');
		$detail = $this->shifts->get_shift_detail($id);
		if(!$detail){
			show_error('Shift not found.', 404);
			return;
		}
		$data = $this->data;
		$data['page_title'] = 'Z-Report — '.$detail['shift']->shift_code;
		$data['detail']     = $detail;
		$this->load->view('cashier_shift/view', $data);
	}
}
