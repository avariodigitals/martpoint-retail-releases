<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Recalls & quarantine — freeze a medicine batch across every location and
 * trace the invoices it already reached.
 */
class Recall extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('recall_model');
		if(!function_exists('mp_pharmacy_industry')
			|| !mp_pharmacy_industry(mp_get_store_profile()['industry_type'] ?? '')){
			$this->show_access_denied_page('Recalls are part of the pharmacy workspace.');
			exit;
		}
	}

	private function who(){
		return (string)($this->session->userdata('display_name')
			?: $this->session->userdata('username')
			?: $this->session->userdata('inv_username') ?: 'Staff');
	}

	public function index(){
		$this->permission_check('recall_view');
		$store_id = (int)get_current_store_id();
		$status = (string)$this->input->get('status');
		$data = array_merge($this->data, [
			'page_title' => 'Recalls & quarantine',
			'recalls' => $this->recall_model->ready() ? $this->recall_model->list_recalls($store_id, $status ?: 'active') : [],
			'current_status' => $status ?: 'active',
			'ready' => $this->recall_model->ready(),
			'message' => (string)$this->session->flashdata('recall_message'),
			'error' => (bool)$this->session->flashdata('recall_error'),
		]);
		$this->load->helper('pharmacy_workspace');
		pharmacy_workspace_render($data, 'pharmacy/recalls');
	}

	/** Detail drawer: frozen units plus every invoice the batch reached. */
	public function view($recall_id = 0){
		$this->permission_check('recall_view');
		$store_id = (int)get_current_store_id();
		$recall = $this->recall_model->ready() ? $this->recall_model->get_recall($store_id, (int)$recall_id) : null;
		if(!$recall){ show_404(); return; }
		$data = array_merge($this->data, [
			'page_title' => 'Recall ' . $recall->recall_code,
			'recall' => $recall,
			'lots' => $this->recall_model->lots_for_recall((int)$recall->id),
			'sales' => $this->recall_model->affected_sales($store_id, (int)$recall->id),
			'strays' => (string)$recall->status === 'active' ? $this->recall_model->sellable_strays($store_id, $recall) : 0,
			'ready' => true,
			'message' => (string)$this->session->flashdata('recall_message'),
			'error' => (bool)$this->session->flashdata('recall_error'),
		]);
		$this->load->helper('pharmacy_workspace');
		pharmacy_workspace_render($data, 'pharmacy/recall_detail');
	}

	public function create(){
		$this->permission_check_with_msg('recall_add');
		if($this->input->method(true) !== 'POST'){ redirect(base_url('recall')); return; }
		$result = $this->recall_model->initiate((int)get_current_store_id(), [
			'batch_lot' => $this->input->post('batch_lot', TRUE),
			'reason' => $this->input->post('reason', TRUE),
			'notes' => $this->input->post('recall_notes', TRUE),
			'item_id' => (int)$this->input->post('item_id', TRUE),
			'barcode_id' => (int)$this->input->post('barcode_id', TRUE),
		], $this->who());
		$this->flash_result($result, 'Recall opened — the batch is frozen across every location.');
		$parts = explode('<<<###>>>', is_string($result) ? $result : '');
		$new_id = (int)($parts[1] ?? 0);
		redirect(base_url($new_id > 0 ? 'recall/view/' . $new_id : 'recall'));
	}

	public function close_recall($recall_id = 0){
		$this->permission_check_with_msg('recall_add');
		if($this->input->method(true) !== 'POST'){ redirect(base_url('recall/view/' . (int)$recall_id)); return; }
		$result = $this->recall_model->close((int)get_current_store_id(), (int)$recall_id, $this->who());
		$this->flash_result($result, 'Recall closed.');
		redirect(base_url('recall/view/' . (int)$recall_id));
	}

	/** Model answers are 'success<<<###>>>...' or 'failed: plain English'. */
	private function flash_result($result, $ok_label){
		$clean = is_string($result) ? trim(strip_tags($result)) : '';
		if(strpos($clean, 'success') === 0){
			$this->session->set_flashdata('recall_message', $ok_label);
		} else {
			$this->session->set_flashdata('recall_error', 1);
			$msg = $clean !== '' && stripos($clean, 'failed') === 0 ? substr($clean, 6) : '';
			$this->session->set_flashdata('recall_message', ltrim($msg, ': ') ?: 'The recall could not be saved. Please try again.');
		}
	}
}
