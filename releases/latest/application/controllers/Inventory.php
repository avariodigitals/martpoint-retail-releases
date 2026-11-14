<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventory extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
	}

	/**
	 * Inventory desktop landing page.
	 * Summarises stock levels, adjustments and transfers.
	 */
	public function index()
	{
		if(!$this->permissions('stock_adjustment_view') && !$this->permissions('stock_transfer_view')){
			$this->show_access_denied_page();
			return;
		}

		$data = $this->data;
		$store_id = get_current_store_id();

		$CI =& get_instance();

		/* Active item count (exclude services) */
		$CI->db->where('store_id', $store_id);
		$CI->db->where('status', 1);
		$CI->db->where("(service_bit IS NULL OR service_bit != 1)", NULL, FALSE);
		$data['total_items'] = $CI->db->count_all_results('db_items');

		/* Low stock count */
		$CI->db->where('store_id', $store_id);
		$CI->db->where('status', 1);
		$CI->db->where("(service_bit IS NULL OR service_bit != 1)", NULL, FALSE);
		$CI->db->where('alert_qty >', 0);
		$CI->db->where('stock <= alert_qty', NULL, FALSE);
		$CI->db->where('stock >', 0);
		$data['low_stock_count'] = $CI->db->count_all_results('db_items');

		/* Out of stock count */
		$CI->db->where('store_id', $store_id);
		$CI->db->where('status', 1);
		$CI->db->where("(service_bit IS NULL OR service_bit != 1)", NULL, FALSE);
		$CI->db->where('stock <=', 0);
		$data['out_of_stock_count'] = $CI->db->count_all_results('db_items');

		/* Total stock value by purchase price */
		$stock_value_row = $CI->db->select('COALESCE(SUM(stock * purchase_price),0) as stock_value')
								  ->where('store_id', $store_id)
								  ->where('status', 1)
								  ->where("(service_bit IS NULL OR service_bit != 1)", NULL, FALSE)
								  ->get('db_items')
								  ->row();
		$data['total_stock_value'] = ($stock_value_row && isset($stock_value_row->stock_value)) ? (float)$stock_value_row->stock_value : 0;

		/* Adjustment and transfer counts */
		$data['adjustment_count'] = $CI->db->where('store_id', $store_id)->count_all_results('db_stockadjustment');
		$data['transfer_count']   = $CI->db->where('store_id', $store_id)->count_all_results('db_stocktransfer');

		/* Warehouse count */
		$data['warehouse_count'] = $CI->db->where('store_id', $store_id)->where('status', 1)->count_all_results('db_warehouse');

		/* Recent activity */
		$data['recent_adjustments'] = $CI->db->where('store_id', $store_id)
									  ->order_by('id', 'desc')
									  ->limit(5)
									  ->get('db_stockadjustment')
									  ->result();

		$data['recent_transfers'] = $CI->db->where('store_id', $store_id)
									->order_by('id', 'desc')
									->limit(5)
									->get('db_stocktransfer')
									->result();

		$data['page_title'] = $this->lang->line('inventory') ?: 'Inventory';
		$data['content'] = $this->load->view('inventory/desktop/dashboard', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
}
