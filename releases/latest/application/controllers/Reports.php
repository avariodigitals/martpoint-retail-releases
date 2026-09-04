<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('reports_model','reports');
	}
	
	
	//Supplier Items Report 
	public function supplier_items(){
		$this->permission_check('supplier_items_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('supplier_items_report');
		$data['content'] = $this->load->view('reports/desktop/supplier_items', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_supplier_items_report(){
		echo $this->reports->show_supplier_items_report();
	}
	
	//Sales Report 
	public function sales(){
		$this->permission_check('sales_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_report');
		$data['content'] = $this->load->view('reports/desktop/sales', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_sales_report(){
		echo $this->reports->show_sales_report();
	}

	//Sales Return Report 
	public function sales_return(){
		$this->permission_check('sales_return_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_return_report');
		$data['content'] = $this->load->view('reports/desktop/sales_return', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_sales_return_report(){
		echo $this->reports->show_sales_return_report();
	}

	//Purchase report
	public function purchase(){
		$this->permission_check('purchase_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('purchase_report');
		$data['content'] = $this->load->view('reports/desktop/purchase', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_purchase_report(){
		echo $this->reports->show_purchase_report();
	}

	//Purchase Return report
	public function purchase_return(){
		$this->permission_check('purchase_return_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('purchase_return_report');
		$data['content'] = $this->load->view('reports/desktop/purchase_return', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_purchase_return_report(){
		echo $this->reports->show_purchase_return_report();
	}

	//Expense report
	public function expense(){
		$this->permission_check('expense_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('expense_report');
		$data['content'] = $this->load->view('reports/desktop/expense', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_expense_report(){
		echo $this->reports->show_expense_report();
	}
	//Profit report
	public function profit_loss(){
		$this->permission_check('profit_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('profit_and_loss_report');
		$data['content'] = $this->load->view('reports/desktop/profit_loss', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function get_profit_by_item(){
		echo $this->reports->get_profit_by_item();
	}
	public function get_profit_by_invoice(){
		echo $this->reports->get_profit_by_invoice();
	}

	//Summary report
	public function stock(){
		$this->permission_check('stock_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('stock_report');
		$data['content'] = $this->load->view('reports/desktop/stock', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	/*Stock Report*/
	public function show_stock_report(){
		return $this->reports->show_stock_report();
	}
	public function brand_wise_stock(){
		return $this->reports->brand_wise_stock();
	}
	public function get_stock_report(){
		$data = array(
			'item_wise_report' => $this->show_stock_report(),
			'brand_wise_stock' => $this->brand_wise_stock(),
			//'category_wise_stock' => $this->category_wise_stock(),
		);
		//print_r($data);exit;
		echo json_encode($data); 
	}

	//Item Sales Report 
	public function item_sales(){
		$this->permission_check('item_sales_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('item_sales_report');
		$data['content'] = $this->load->view('reports/desktop/sales_item', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_item_sales_report(){
		echo $this->reports->show_item_sales_report();
	}
	//Return Item Report 
	public function return_item(){
		$this->permission_check('return_items_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('return_items_report');
		$data['content'] = $this->load->view('reports/desktop/return_item', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_return_items_report(){
		echo $this->reports->show_return_items_report();
	}
	
	//Purchase Payments report
	public function purchase_payments(){
		$this->permission_check('purchase_payments_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('purchase_payments_report');
		$data['content'] = $this->load->view('reports/desktop/purchase_payments', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_purchase_payments_report(){
		echo $this->reports->show_purchase_payments_report();
	}

	//Sales Payments report
	public function sales_payments(){
		$this->permission_check('sales_payments_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_payments_report');
		$data['content'] = $this->load->view('reports/desktop/sales_payments', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_sales_payments_report(){
		echo $this->reports->show_sales_payments_report();
	}

	//Sales Return Payments report
	public function sales_return_payments(){
		$this->permission_check('sales_payments_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_return_payments_report');
		$data['content'] = $this->load->view('reports/desktop/sales_return_payments', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_sales_return_payments_report(){
		echo $this->reports->show_sales_return_payments_report();
	}

	//Expired Items Report 
	public function expired_items(){
		$this->permission_check('expired_items_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('expired_items_report');
		$data['content'] = $this->load->view('reports/desktop/expired_items', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_expired_items_report(){
		echo $this->reports->show_expired_items_report();
	}
	public function get_profit_loss_report(){
		echo json_encode($this->reports->get_profit_loss_report());
	}

	//Receivables Aging Report
	public function receivables_aging(){
		$this->permission_check('receivables_aging_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('receivables_aging_report');
		$data['content'] = $this->load->view('reports/desktop/receivables_aging', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_receivables_aging_report(){
		echo $this->reports->show_receivables_aging_report();
	}

	//Inventory Aging / Dead Stock Report
	public function inventory_aging(){
		$this->permission_check('inventory_aging_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('inventory_aging_report');
		$data['content'] = $this->load->view('reports/desktop/inventory_aging', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_inventory_aging_report(){
		echo $this->reports->show_inventory_aging_report();
	}

	//Cash Flow Statement Report
	public function cash_flow(){
		$this->permission_check('cash_flow_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('cash_flow_report');
		$data['content'] = $this->load->view('reports/desktop/cash_flow', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_cash_flow_report(){
		echo json_encode($this->reports->show_cash_flow_report());
	}

	//Best Sellers by Variant Attribute (Size / Colour)
	public function variant_attribute(){
		$this->permission_check('variant_attribute_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('variant_attribute_report');
		$data['content'] = $this->load->view('reports/desktop/variant_attribute', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_variant_attribute_report(){
		echo $this->reports->show_variant_attribute_report();
	}

	//Sell-Through Report
	public function sell_through(){
		$this->permission_check('sell_through_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('sell_through_report');
		$data['content'] = $this->load->view('reports/desktop/sell_through', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_sell_through_report(){
		echo $this->reports->show_sell_through_report();
	}

	//Reorder Suggestion Report
	public function reorder_suggestion(){
		$this->permission_check('reorder_suggestion_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('reorder_suggestion_report');
		$data['content'] = $this->load->view('reports/desktop/reorder_suggestion', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_reorder_suggestion_report(){
		echo $this->reports->show_reorder_suggestion_report();
	}


	//Item Sales Report
	public function seller_points(){
		$this->permission_check('seller_points_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('seller_points_report');
		$data['content'] = $this->load->view('reports/desktop/seller_points', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_seller_points_report(){
		echo $this->reports->show_seller_points_report();
	}
	
	//Sales Tax Report 
	public function sales_tax(){
		$this->permission_check('sales_tax_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_tax_report');
		$data['content'] = $this->load->view('reports/desktop/sales_tax', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_sales_tax_report(){
		echo $this->reports->show_sales_tax_report();
	}

	//purchase Tax Report 
	public function purchase_tax(){
		$this->permission_check('purchase_tax_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('purchase_tax_report');
		$data['content'] = $this->load->view('reports/desktop/purchase_tax', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_purchase_tax_report(){
		echo $this->reports->show_purchase_tax_report();
	}

	//GSTR-1 Report 
	public function gstr_1(){
		$this->permission_check('gstr_1_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('gstr_1_report');
		$data['content'] = $this->load->view('reports/desktop/gstr_1', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_gstr_1_report(){
		echo $this->reports->show_gstr_1_report();
	}
	//GSTR-2 Report 
	public function gstr_2(){
		$this->permission_check('gstr_2_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('gstr_2_report');
		$data['content'] = $this->load->view('reports/desktop/gstr_2', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_gstr_2_report(){
		echo $this->reports->show_gstr_2_report();
	}

	//Customer Sales Item GST Report 
	public function sales_gst_report(){
		$this->permission_check('sales_gst_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_gst_report');
		$data['content'] = $this->load->view('reports/desktop/sales_gst', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_sales_gst_report(){
		echo $this->reports->show_sales_gst_report();
	}
	//Purchase Item GST Report 
	public function purchase_gst_report(){
		$this->permission_check('purchase_gst_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('purchase_gst_report');
		$data['content'] = $this->load->view('reports/desktop/purchase_gst', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_purchase_gst_report(){
		echo $this->reports->show_purchase_gst_report();
	}
	
	//Sales Report 
	public function customer_orders(){
		$this->permission_check('customer_orders_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('customer_orders');
		$data['content'] = $this->load->view('reports/desktop/customer_orders', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_customer_orders(){
		echo $this->reports->show_customer_orders();
	}

	//Delivery sheet report
	public function delivery_sheet(){
		if(!mp_feature_enabled('delivery_scheduling')){
			$this->show_feature_not_activated('delivery_scheduling');
			return;
		}
		$this->permission_check('delivery_sheet_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('delivery_sheet_report');
		$data['content'] = $this->load->view('reports/desktop/delivery_sheet', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_delivery_sheet(){
		if(!mp_feature_enabled('delivery_scheduling')){
			echo json_encode(['data'=>[]]);
			return;
		}
		echo $this->reports->show_delivery_sheet();
	}

	//Load sheet report
	public function load_sheet(){
		$this->permission_check('load_sheet_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('load_sheet_report');
		$data['content'] = $this->load->view('reports/desktop/load_sheet', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_load_sheet(){
		echo $this->reports->show_load_sheet();
	}
	
	//Sales & payments records 
	public function sales_and_payments(){
		$this->permission_check('sales_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_and_payments_report');
		$data['content'] = $this->load->view('reports/desktop/sales_and_payments', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function sales_and_payments_report(){
		echo $this->reports->sales_and_payments_report();
	}

	//Item Sales Report 
	public function stock_transfer(){
		$this->permission_check('stock_transfer_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('stock_transfer_report');
		$data['content'] = $this->load->view('reports/desktop/stock_transfer', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_stock_transfer_report(){
		echo $this->reports->show_stock_transfer_report();
	}

	// Sales Summary Report 
	public function sales_summary(){
		$this->permission_check('sales_summary_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_summary_report');
		$data['content'] = $this->load->view('reports/desktop/sales_summary', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_sales_summary_report(){
		echo $this->reports->show_sales_summary_report();
	}

	/* ===================== PRODUCTION REPORTS ===================== */
	public function production_summary(){
		if(!mp_feature_enabled('production_workflow')){ $this->show_access_denied_page(); return; }
		$this->permission_check('production_batches_view');
		$data=$this->data;
		$data['page_title']='Production Summary Report';
		$data['content'] = $this->load->view('reports/desktop/production_summary', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_production_summary_report(){
		if(!mp_feature_enabled('production_workflow')){ echo ''; return; }
		echo $this->reports->show_production_summary_report();
	}

	public function ingredient_usage(){
		if(!mp_feature_enabled('production_workflow')){ $this->show_access_denied_page(); return; }
		$this->permission_check('production_batches_view');
		$data=$this->data;
		$data['page_title']='Ingredient Usage Report';
		$data['content'] = $this->load->view('reports/desktop/ingredient_usage', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_ingredient_usage_report(){
		if(!mp_feature_enabled('production_workflow')){ echo ''; return; }
		echo $this->reports->show_ingredient_usage_report();
	}

	public function recipe_costing(){
		if(!mp_feature_enabled('recipe_tracking')){ $this->show_access_denied_page(); return; }
		$this->permission_check('recipes_view');
		$data=$this->data;
		$data['page_title']='Recipe Costing Report';
		$data['content'] = $this->load->view('reports/desktop/recipe_costing', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_recipe_costing_report(){
		if(!mp_feature_enabled('recipe_tracking')){ echo ''; return; }
		echo $this->reports->show_recipe_costing_report();
	}

	public function production_runs(){
		if(!mp_feature_enabled('production_workflow')){ $this->show_access_denied_page(); return; }
		$this->permission_check('production_batches_view');
		$data=$this->data;
		$data['page_title']='Production Runs Report';
		$data['content'] = $this->load->view('reports/desktop/production_runs', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function show_production_runs_report(){
		if(!mp_feature_enabled('production_workflow')){ echo ''; return; }
		echo $this->reports->show_production_runs_report();
	}

	//Cash in Hand summary page
	public function cash_in_hand(){
		$this->permission_check('dashboard_view');
		$this->load->model('dashboard_model','dashboard');
		$data=$this->data;
		$data['page_title']='Cash in Hand';
		$warehouse_id = get_store_warehouse_id();

		$selected_date = $this->input->get('date', TRUE);
		if(empty($selected_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)){
			$selected_date = date('Y-m-d');
		}
		$selected_date = min($selected_date, date('Y-m-d'));

		$b = $this->dashboard->get_cash_in_hand_breakdown($warehouse_id, $selected_date);
		$data['cash_in_hand'] = $this->currency($b['net_cash'], false);
		$data['cash_in_total'] = $this->currency($b['cash_in_sales'] + $b['cash_in_purchase_returns'], false);
		$data['cash_out_total'] = $this->currency($b['cash_out_expenses'] + $b['cash_out_purchases'] + $b['cash_out_sales_returns'] + $b['cash_out_deposits'], false);
		$data['breakdown'] = [
			['label' => 'Cash Sales', 'amount' => $this->currency($b['cash_in_sales'], false), 'type' => 'in'],
			['label' => 'Purchase Returns', 'amount' => $this->currency($b['cash_in_purchase_returns'], false), 'type' => 'in'],
			['label' => 'Expenses', 'amount' => $this->currency($b['cash_out_expenses'], false), 'type' => 'out'],
			['label' => 'Purchase Payments', 'amount' => $this->currency($b['cash_out_purchases'], false), 'type' => 'out'],
			['label' => 'Sales Return Refunds', 'amount' => $this->currency($b['cash_out_sales_returns'], false), 'type' => 'out'],
			['label' => 'Bank Deposits', 'amount' => $this->currency($b['cash_out_deposits'], false), 'type' => 'out'],
		];
		$data['selected_date'] = $selected_date;
		$data['selected_date_label'] = show_date($selected_date);
		$data['updated_at'] = 'As of ' . show_date($selected_date);
		$this->load->view('mobile/cash_in_hand', $data);
	}

}

