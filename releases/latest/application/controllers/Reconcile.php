<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Payment reconciliation — exception queue dashboard.
 *
 * Surfaces payment anomalies (unmatched provider receipts, amount
 * discrepancies, partial payments, refunds, disputes) detected by
 * webhooks and the on-demand scan. Resolution actions are audit-trailed.
 */
class Reconcile extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('payment_reconcile_model','recon');
	}

	private function _can_view(){
		return $this->permissions('payments_reconcile') || is_admin() || is_store_admin() || $this->session->userdata('role_id') == 1;
	}

	public function index(){
		if(!$this->_can_view()){ $this->show_access_denied_page(); return; }
		$store_id = get_current_store_id();
		$status = $this->input->get('status', TRUE) ?: 'open';
		$type   = $this->input->get('type', TRUE) ?: null;
		if(!in_array($status, array('open','acknowledged','resolved','dismissed','all'), true)){ $status = 'open'; }

		$data = $this->data;
		$data['page_title'] = 'Payment Reconciliation';
		$data['counts']     = $this->recon->counts($store_id);
		$data['exceptions'] = $this->recon->get_exceptions($store_id, $status, $type);
		$data['f_status']   = $status;
		$data['f_type']     = $type;
		$data['content']    = $this->load->view('payments/reconcile', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	// Run the on-demand scan for this store (POST).
	public function scan(){
		if(!$this->_can_view()){ echo json_encode(array('status'=>'error','message'=>'Access denied')); return; }
		$found = $this->recon->scan(get_current_store_id());
		echo json_encode(array('status'=>'success','found'=>$found,
			'csrf_hash' => $this->security->get_csrf_hash()));
	}

	// Transition an exception: acknowledge | resolve | dismiss | dispute (POST, JSON).
	public function resolve(){
		if(!$this->_can_view()){ echo json_encode(array('status'=>'error','message'=>'Access denied')); return; }
		$id     = (int)$this->input->post('id');
		$action = $this->input->post('action', TRUE);
		$note   = trim((string)$this->input->post('note', TRUE));
		$user   = isset($this->data['CUR_USERNAME']) ? $this->data['CUR_USERNAME'] : $this->session->userdata('inv_username');

		if($id <= 0 || !in_array($action, array('acknowledged','resolved','dismissed','dispute'), true)){
			echo json_encode(array('status'=>'error','message'=>'Invalid request'));
			return;
		}
		$ok = $this->recon->resolve($id, get_current_store_id(), $action, $note, $user);
		echo json_encode(array('status'=> $ok ? 'success' : 'error',
			'message'=> $ok ? 'Updated' : 'Exception not found',
			'csrf_hash' => $this->security->get_csrf_hash()));
	}
}
