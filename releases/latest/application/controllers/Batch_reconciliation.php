<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Batch reconciliation — where the batch ledger and the stock figures disagree,
 * and the shop's decision about which one is the truth.
 *
 * Under FEFO/FIFO the till, the invoice screen and the online store all sell
 * from the batch ledger, so a disagreement stops a sale at the counter. This
 * screen finds those disagreements per location and balances them with one
 * recorded entry — never by editing a number behind the shop's back.
 */
class Batch_reconciliation extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('batch_reconciliation_model');
		if(!function_exists('mp_feature_enabled') || !mp_feature_enabled('batch_tracking')){
			$this->show_access_denied_page('Batch reconciliation is available when batch tracking is enabled for this business.');
			exit;
		}
	}

	private function storeId(){
		return (int)get_current_store_id();
	}

	private function whoId(){
		return (int)($this->session->userdata('inv_userid') ?: 0);
	}

	/** The list of disagreements, one location at a time, biggest gap first. */
	public function index(){
		$this->permission_check('batch_reconciliation_view');
		$storeId = $this->storeId();
		$warehouseId = (int)$this->input->get('warehouse');
		if($warehouseId > 0 && !$this->batch_reconciliation_model->in_scope_warehouse($storeId, $warehouseId)) $warehouseId = 0;
		$page = max(1, (int)$this->input->get('page'));
		$perPage = 25;
		$search = (string)$this->input->get('q');
		$ready = $this->batch_reconciliation_model->ready();

		$total = $ready ? $this->batch_reconciliation_model->count_mismatches($storeId, $warehouseId, $search) : 0;
		$data = array_merge($this->data, [
			'page_title' => 'Batch reconciliation',
			'ready' => $ready,
			'warehouses' => $this->batch_reconciliation_model->warehouses($storeId),
			'current_warehouse' => $warehouseId,
			'rotation' => $this->batch_reconciliation_model->rotationMethod($storeId),
			'search' => $search,
			'gaps' => $ready ? $this->batch_reconciliation_model->mismatches($storeId, $warehouseId, $perPage, ($page-1)*$perPage, $search)->result() : [],
			'gap_total' => $total,
			'page' => $page,
			'pages' => (int)ceil($total / $perPage),
			'can_manage' => $this->permissions('batch_reconciliation_manage'),
			'history' => $ready ? $this->batch_reconciliation_model->history($storeId, 10) : [],
			'message' => (string)$this->session->flashdata('recon_message'),
			'error' => (bool)$this->session->flashdata('recon_error'),
		]);
		$this->load->helper('pharmacy_workspace');
		inventory_batch_workspace_render($data, 'pharmacy/batch_reconciliation');
	}

	/** One item in one location: both sides, the batches behind them, and the choice. */
	public function item($itemId = 0){
		$this->permission_check('batch_reconciliation_view');
		$storeId = $this->storeId();
		$figures = $this->batch_reconciliation_model->figures($storeId, (int)$itemId, (int)$this->input->get('warehouse'));
		if(!$figures){ show_404(); return; }
		$data = array_merge($this->data, [
			'page_title' => 'Balance ' . $figures['item_name'],
			'ready' => $this->batch_reconciliation_model->ready(),
			'figures' => $figures,
			'lot_rows' => $this->batch_reconciliation_model->lot_rows($storeId, (int)$itemId, (int)$figures['warehouse_id']),
			'warehouses' => $this->batch_reconciliation_model->warehouses($storeId),
			'rotation' => $this->batch_reconciliation_model->rotationMethod($storeId),
			'wastage_reasons' => mp_get_wastage_reasons(),
			'can_manage' => $this->permissions('batch_reconciliation_manage'),
			'history' => $this->batch_reconciliation_model->history($storeId, 5),
			'message' => (string)$this->session->flashdata('recon_message'),
			'error' => (bool)$this->session->flashdata('recon_error'),
		]);
		$this->load->helper('pharmacy_workspace');
		inventory_batch_workspace_render($data, 'pharmacy/batch_reconciliation_item');
	}

	public function balance(){
		if($this->input->method(true) !== 'POST'){ redirect(base_url('batch_reconciliation')); return; }
		$this->permission_check_with_msg('batch_reconciliation_manage');
		$result = $this->batch_reconciliation_model->balance($this->storeId(), [
			'item_id' => (int)$this->input->post('item_id', TRUE),
			'warehouse_id' => (int)$this->input->post('warehouse_id', TRUE),
			'fix' => (string)$this->input->post('fix', TRUE),
			'batch_lot' => (string)$this->input->post('batch_lot', TRUE),
			'expire_date' => (string)$this->input->post('expire_date', TRUE),
			'reason_code' => (string)$this->input->post('reason_code', TRUE),
			'note' => (string)$this->input->post('note', TRUE),
		], $this->whoId());
		$clean = is_string($result) ? trim(strip_tags($result)) : '';
		$parts = explode('<<<###>>>', $clean);
		$itemId = (int)($parts[1] ?? 0);
		if(strpos($clean, 'success') === 0){
			$this->session->set_flashdata('recon_message', 'Balanced. The batch ledger and stock figures now agree for this product.');
		} else {
			$this->session->set_flashdata('recon_error', 1);
			$this->session->set_flashdata('recon_message', ltrim(substr($clean, 0, 6) === 'failed' ? substr($clean, 6) : '', ': ')
				?: 'The balance could not be saved. Nothing was changed.');
		}
		redirect(base_url('batch_reconciliation/item/' . ($itemId > 0 ? $itemId : '')
			. '?warehouse=' . (int)$this->input->post('warehouse_id', TRUE)));
	}
}
