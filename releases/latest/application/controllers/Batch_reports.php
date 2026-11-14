<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Batch ledger reports — the shop's batches, read from the batch ledger.
 *
 * The stock figure says how many units a product has; it cannot say which
 * batch they belong to, when each one runs out, how long the unsold ones have
 * been sitting, or which invoice a batch was handed over on. Under FEFO/FIFO
 * the counter, the invoice screen and the online store all sell from the batch
 * ledger, so those are the numbers the shop needs to plan against — and this
 * screen reads them with the same rules the till spends, so a report can never
 * promise what the counter would refuse.
 */
class Batch_reports extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('pharmacy_batch_report_model', 'reports');
		if(!function_exists('mp_feature_enabled') || !mp_feature_enabled('batch_tracking')){
			$this->show_access_denied_page('Batch ledger reports are available when batch tracking is enabled for this business.');
			exit;
		}
	}

	public function index(){
		$this->permission_check('pharmacy_batch_report_view');
		$storeId = (int)get_current_store_id();
		$tab = $this->reports->tab($this->input->get('report'));
		$filters = $this->reports->filters($storeId, $this->input->get());
		$ready = $this->reports->ready($tab);

		$result = ['rows' => [], 'total' => 0, 'totals' => []];
		if($ready){
			if($tab === 'hold') $result = $this->reports->lot_stock($storeId, $filters);
			if($tab === 'expiring') $result = $this->reports->expiring($storeId, $filters);
			if($tab === 'sold') $result = $this->reports->batch_sales($storeId, $filters);
			if($tab === 'ageing') $result = $this->reports->ageing($storeId, $filters);
		}
		// Say *why* a batch the counter would refuse is on this list, using the
		// same rule the refusal comes from rather than a guess made in the view.
		$blocksExpired = $this->_blocksExpired($storeId);
		$today = date('Y-m-d');
		foreach($result['rows'] as $row){
			if(isset($row->sellable)) $row->held_reason = $this->reports->held_reason($row, $blocksExpired, $today);
		}

		$data = array_merge($this->data, [
			'page_title' => 'Batch ledger reports',
			'ready' => $ready,
			'tab' => $tab,
			'tabs' => [
				'hold' => ['On the shelf', 'Every batch you hold, and what can still be sold from it'],
				'expiring' => ['Running out of date', 'Batches already past, or past within your warning window'],
				'sold' => ['Sold by batch', 'Which batch each sale came from, and what came back'],
				'ageing' => ['How long they have sat', 'Unsold batches by the day they arrived'],
			],
			'warehouses' => $this->reports->locations($storeId),
			'filters' => $filters,
			'rows' => $result['rows'],
			'row_total' => (int)$result['total'],
			'pages' => (int)ceil((int)$result['total'] / max(1, (int)$filters['per_page'])),
			'totals' => $result['totals'],
			'bands' => $result['bands'] ?? [],
			'buckets' => $result['buckets'] ?? [],
			'rotation' => $this->reports->rotationMethod($storeId),
			'blocks_expired' => $blocksExpired,
			'today' => $today,
		]);
		$this->load->helper('pharmacy_workspace');
		inventory_batch_workspace_render($data, 'pharmacy/batch_reports');
	}

	private function _blocksExpired($storeId){
		$this->load->model('inventory_rotation_model');
		return $this->inventory_rotation_model->blocks_expired_sales((int)$storeId);
	}
}
