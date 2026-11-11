<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('reports_model','reports');
		// Production report views use the model's status labels/badges directly.
		// Load it here so the report is usable from an authenticated management
		// workspace instead of failing during view rendering.
		$this->load->model('production_batches_model');
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

	/** Member-group versus retail sales report with server-side pagination. */
	public function customer_group_sales(){
		$this->permission_check('sales_report');
		$data = $this->data;
		$data['page_title'] = 'Member & Retail Sales';
		$this->load->model('customer_pricing_model', 'customer_pricing');
		$data['customer_pricing_ready'] = $this->customer_pricing->installed();
		$data['customer_pricing_groups'] = $this->customer_pricing->groups(get_current_store_id());
		$data['sales_report_warehouses'] = $this->db->select('id, warehouse_name')->where('store_id', get_current_store_id())
			->where('status', 1)->order_by('warehouse_name')->get('db_warehouse')->result();
		$mobile = $this->input->get('screen') === 'mobile' || ($this->input->get('screen') !== 'desktop' && is_mobile());
		if($mobile){
			$data['SITE_TITLE'] = $data['store_name'] ?? $this->session->userdata('store_name') ?: 'MartPoint';
			$this->load->view('mobile/reports/customer_group_sales', $data);
			return;
		}
		$data['content'] = $this->load->view('reports/desktop/customer_group_sales', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function customer_group_sales_data(){
		$this->permission_check('sales_report');
		$filters = $this->_customer_group_sales_filters($this->input->post(NULL, TRUE));
		if(is_cashier() && !permissions('show_all_users_sales_invoices')) $filters['created_by'] = $this->session->userdata('inv_username');
		$start = max(0, (int)$this->input->post('start'));
		$length = max(1, min(200, (int)$this->input->post('length')));
		$search = trim((string)$this->input->post('search', TRUE));
		$result = $this->reports->customer_group_sales_page(get_current_store_id(), $filters, $start, $length, $search);
		$this->output->set_content_type('application/json')->set_output(json_encode([
			'draw'=>(int)$this->input->post('draw'), 'recordsTotal'=>$result['total'], 'recordsFiltered'=>$result['total'],
			'data'=>$result['rows'], 'summary'=>$result['summary'], 'csrf_hash'=>$this->security->get_csrf_hash()
		]));
	}

	/** Export the complete filtered result as CSV, Excel-compatible workbook, or PDF. */
	public function customer_group_sales_export($format = ''){
		$this->permission_check('sales_report');
		$format = strtolower((string)$format);
		if(!in_array($format, ['csv','excel','pdf'], TRUE)) show_404();
		$filters = $this->_customer_group_sales_filters($this->input->get(NULL, TRUE));
		if(is_cashier() && !permissions('show_all_users_sales_invoices')) $filters['created_by'] = $this->session->userdata('inv_username');
		$search = trim((string)$this->input->get('search', TRUE));
		$report = $this->reports->customer_group_sales_export_rows(get_current_store_id(), $filters, $search);
		$rows = $report['rows'];
		$summary = $report['summary'];
		if((int)$report['total'] > count($rows)){
			$this->output->set_status_header(413)->set_content_type('text/plain')->set_output('This export exceeds 50,000 sales lines. Narrow the date range or add a branch/group filter and export again.');
			return;
		}
		$filename = 'member-retail-sales-' . date('Ymd-His');
		$headers = ['Date','Invoice','Customer code','Customer','Item','Quantity','Unit price','Line total','Sale type','Customer group','Branch','Cashier'];
		if($format === 'csv'){
			$this->output->set_content_type('text/csv', 'UTF-8');
			$this->output->set_header('Content-Disposition: attachment; filename="'.$filename.'.csv"');
			$fp = fopen('php://temp', 'w+');
			fwrite($fp, "\xEF\xBB\xBF");
			fputcsv($fp, ['Member & Retail Sales']);
			fputcsv($fp, ['Member sales', (float)$summary['member_sales'], 'Individual adjustments', (float)$summary['individual_sales'], 'Retail sales', (float)$summary['retail_sales'], 'Total sales', (float)$summary['total_sales']]);
			fputcsv($fp, $headers);
			foreach($rows as $row) fputcsv($fp, $this->_customer_group_sales_export_row($row));
			rewind($fp); $csv = stream_get_contents($fp); fclose($fp);
			$this->output->set_output($csv); return;
		}
		if($format === 'excel'){
			$xml = '<?xml version="1.0" encoding="UTF-8"?><?mso-application progid="Excel.Sheet"?>';
			$xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Worksheet ss:Name="Member Retail Sales"><Table>';
			$xml .= '<Row><Cell><Data ss:Type="String">Member &amp; Retail Sales</Data></Cell></Row>';
			$xml .= '<Row><Cell><Data ss:Type="String">Member sales</Data></Cell><Cell><Data ss:Type="Number">'.(float)$summary['member_sales'].'</Data></Cell><Cell><Data ss:Type="String">Individual adjustments</Data></Cell><Cell><Data ss:Type="Number">'.(float)$summary['individual_sales'].'</Data></Cell><Cell><Data ss:Type="String">Retail sales</Data></Cell><Cell><Data ss:Type="Number">'.(float)$summary['retail_sales'].'</Data></Cell><Cell><Data ss:Type="String">Total sales</Data></Cell><Cell><Data ss:Type="Number">'.(float)$summary['total_sales'].'</Data></Cell></Row>';
			$xml .= '<Row>';
			foreach($headers as $header) $xml .= '<Cell><Data ss:Type="String">'.htmlspecialchars($header, ENT_XML1|ENT_QUOTES, 'UTF-8').'</Data></Cell>';
			$xml .= '</Row>';
			foreach($rows as $row){
				$xml .= '<Row>';
				foreach($this->_customer_group_sales_export_row($row) as $index=>$value){
					$type = in_array($index, [5,6,7], TRUE) ? 'Number' : 'String';
					$xml .= '<Cell><Data ss:Type="'.$type.'">'.htmlspecialchars((string)$value, ENT_XML1|ENT_QUOTES, 'UTF-8').'</Data></Cell>';
				}
				$xml .= '</Row>';
			}
			$xml .= '</Table></Worksheet></Workbook>';
			$this->output->set_content_type('application/vnd.ms-excel', 'UTF-8')->set_header('Content-Disposition: attachment; filename="'.$filename.'.xls"')->set_output($xml); return;
		}
		$this->load->helper('url');
		$CI =& get_instance();
		$currency = htmlspecialchars($CI->currency(), ENT_QUOTES, 'UTF-8');
		$html = '<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;font-size:9px;color:#172033}h1{font-size:18px}p{color:#536176}.summary{width:100%;margin:12px 0}.summary td{padding:8px;background:#eef3f8}.summary strong{display:block;font-size:12px}table.data{width:100%;border-collapse:collapse}th{background:#123b67;color:#fff}th,td{border:1px solid #d5dce5;padding:5px;text-align:left}td.num{text-align:right}tr:nth-child(even){background:#f6f8fb}</style></head><body>';
		$html .= '<h1>Member &amp; Retail Sales</h1><p>Generated '.date('Y-m-d H:i').' · All amounts in '.$currency.'</p>';
		$html .= '<table class="summary"><tr><td>Member sales<strong>'.htmlspecialchars(store_number_format($summary['member_sales']), ENT_QUOTES, 'UTF-8').'</strong></td><td>Individual adjustments<strong>'.htmlspecialchars(store_number_format($summary['individual_sales']), ENT_QUOTES, 'UTF-8').'</strong></td><td>Retail sales<strong>'.htmlspecialchars(store_number_format($summary['retail_sales']), ENT_QUOTES, 'UTF-8').'</strong></td><td>Total sales<strong>'.htmlspecialchars(store_number_format($summary['total_sales']), ENT_QUOTES, 'UTF-8').'</strong></td></tr></table><table class="data"><thead><tr>';
		foreach($headers as $header) $html .= '<th>'.htmlspecialchars($header, ENT_QUOTES, 'UTF-8').'</th>';
		$html .= '</tr></thead><tbody>';
		foreach($rows as $row){
			$html .= '<tr>';
			foreach($this->_customer_group_sales_export_row($row) as $index=>$value){
				$html .= '<td'.(in_array($index, [5,6,7], TRUE) ? ' class="num"' : '').'>'.htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8').'</td>';
			}
			$html .= '</tr>';
		}
		$html .= '</tbody></table></body></html>';
		$options = new \Dompdf\Options(); $options->set('isRemoteEnabled', false);
		$pdf = new \Dompdf\Dompdf($options); $pdf->loadHtml($html, 'UTF-8'); $pdf->setPaper('A4', 'landscape'); $pdf->render();
		$this->output->set_content_type('application/pdf')->set_header('Content-Disposition: attachment; filename="'.$filename.'.pdf"')->set_output($pdf->output());
	}

	private function _customer_group_sales_filters($input){
		$input = is_array($input) ? $input : [];
		$filters = [];
		foreach(['from_date','to_date'] as $key){
			$value = trim((string)($input[$key] ?? ''));
			if($value !== '' && preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $value) && date('Y-m-d', strtotime($value)) === $value) $filters[$key] = $value;
		}
		$filters['warehouse_id'] = max(0, (int)($input['warehouse_id'] ?? 0));
		$filters['group_id'] = max(0, (int)($input['group_id'] ?? 0));
		$filters['customer_id'] = max(0, (int)($input['customer_id'] ?? 0));
		$type = (string)($input['sale_type'] ?? 'all');
		$filters['sale_type'] = in_array($type, ['all','member','retail','other'], TRUE) ? $type : 'all';
		return $filters;
	}

	private function _customer_group_sales_export_row(array $row){
		return [
			(string)($row['sales_date'] ?? ''), (string)($row['sales_code'] ?? ''),
			(string)($row['customer_code'] ?? ''), (string)($row['customer_name'] ?? 'Walk-in'),
			(string)($row['item_name'] ?? ''), (float)($row['qty'] ?? 0), (float)($row['unit_price'] ?? 0), (float)($row['line_total'] ?? 0),
			($row['pricing_source'] ?? '') === 'group' ? 'Member' : ((($row['pricing_source'] ?? '') === 'customer') ? 'Individual adjustment' : 'Retail'),
			(string)(($row['pricing_source'] ?? '') === 'group' ? ($row['pricing_group_name'] ?? 'Member group') : ''),
			(string)($row['warehouse_name'] ?? ''), (string)($row['created_by'] ?? '')
		];
	}

	/** Dedicated monthly repeat-purchase trend report for the active store. */
	public function repeat_purchase_trends(){
		$this->permission_check('sales_report');
		$data = $this->data;
		$data['page_title'] = 'Repeat-Purchase Trends';
		$data['content'] = $this->load->view('reports/desktop/repeat_purchase_trends', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function show_repeat_purchase_trends(){
		$this->permission_check('sales_report');
		$this->output->set_content_type('text/html');
		$from = (string)$this->input->post('from_date', TRUE);
		$to = (string)$this->input->post('to_date', TRUE);
		if(!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $from) || !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $to) || strtotime($from) === false || strtotime($to) === false || $from > $to){
			echo '<tr><td colspan="8">Choose a valid date range.</td></tr>'; return;
		}
		$this->load->model('reports_model', 'reports');
		$rows = $this->reports->repeat_purchase_trends(get_current_store_id(), $from, $to);
		if(!$rows){ echo '<tr><td colspan="8" class="text-center">No completed customer sales found in this period.</td></tr>'; return; }
		$i = 0;
		foreach($rows as $row){
			$i++;
			$month = date('F Y', strtotime($row->month_start));
			echo '<tr><td>'.$i.'</td><td>'.htmlspecialchars($month, ENT_QUOTES, 'UTF-8').'</td>';
			echo '<td class="text-right">'.number_format((int)$row->active_customers).'</td>';
			echo '<td class="text-right">'.number_format((int)$row->repeat_customers).'</td>';
			echo '<td class="text-right">'.number_format((float)$row->repeat_rate, 1).'%</td>';
			echo '<td class="text-right">'.number_format((int)$row->repeat_orders).'</td>';
			echo '<td class="text-right">'.store_number_format($row->repeat_revenue).'</td>';
			echo '<td class="text-right">'.number_format((int)$row->completed_orders).'</td></tr>';
		}
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
		$this->permission_check('stock_report');
		if(strtoupper($this->input->method()) !== 'POST'){
			// This is an AJAX fragment endpoint; direct navigation should open the
			// complete, styled report instead of a table fragment.
			redirect('reports/stock');
			return;
		}
		echo $this->reports->show_stock_report();
	}
	public function brand_wise_stock(){
		$this->permission_check('stock_report');
		if(strtoupper($this->input->method()) !== 'POST'){
			redirect('reports/stock');
			return;
		}
		echo $this->reports->brand_wise_stock();
	}
	public function get_stock_report(){
		$this->permission_check('stock_report');
		if(strtoupper($this->input->method()) !== 'POST'){
			show_error('Method not allowed', 405);
			return;
		}
		$data = array(
			'item_wise_report' => $this->reports->show_stock_report(),
			'brand_wise_stock' => $this->reports->brand_wise_stock(),
			//'category_wise_stock' => $this->category_wise_stock(),
		);
		//print_r($data);exit;
		$this->output->set_content_type('application/json')->set_output(json_encode($data));
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
		redirect('expired_items_report');
	}
	public function show_expired_items_report(){
		$this->permission_check('expired_items_report');
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
		$this->permission_check('receivables_aging_report');
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

	//GST (Indian) reports removed - not applicable outside India
	
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
        if ((mp_get_store_profile()['industry_type'] ?? '')==='garden_centre_landscaping') { $this->permission_check('sales_report');redirect('garden/sales_report');return; }
		$this->permission_check('sales_report');
		$data=$this->data;
		$data['page_title']=$this->lang->line('sales_and_payments_report');
		$data['content'] = $this->load->view('reports/desktop/sales_and_payments', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function sales_and_payments_report(){
        $this->permission_check('sales_report');
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
		$this->permission_check('stock_transfer_report');
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

