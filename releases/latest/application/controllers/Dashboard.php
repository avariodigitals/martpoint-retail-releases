<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		if($this->get_current_version_of_db()!=app_version() && special_access()){
			$this->session->set_flashdata('warning', 'Database update available. Please use Settings &rarr; System Update.');
		}
	}
	public function dashboard_values(){
		$this->load->model('dashboard_model');//Model
		$data=$this->dashboard_model->breadboard_values();//Model->Method
		echo json_encode($data);
	}

	public function index($val='')
	{ 	
		$this->load->model('dashboard_model');//Model

		// Branch / Warehouse Filter
		$selected_branch = '';
		if($this->input->get('branch_id') !== NULL){
			$selected_branch = $this->input->get('branch_id');
			// Validate branch belongs to current store
			if(!empty($selected_branch)){
				$branch_exists = $this->db->where('id', $selected_branch)->where('store_id', get_current_store_id())->count_all_results('db_warehouse') > 0;
				if(!$branch_exists){
					$selected_branch = '';
				}
			}
			$this->session->set_userdata('selected_branch_id', $selected_branch);
		} else if($this->session->userdata('selected_branch_id') !== NULL){
			$selected_branch = $this->session->userdata('selected_branch_id');
			// Validate stale session branch belongs to current store
			if(!empty($selected_branch)){
				$branch_exists = $this->db->where('id', $selected_branch)->where('store_id', get_current_store_id())->count_all_results('db_warehouse') > 0;
				if(!$branch_exists){
					$selected_branch = '';
					$this->session->set_userdata('selected_branch_id', '');
				}
			}
		}

		// Date range filter
		$valid_ranges = ['Today','7Days','30Days','LastMonth','ThisMonth','ThisYear'];
		$range = 'Today';
		if($this->input->get('range') !== NULL && in_array($this->input->get('range'), $valid_ranges)){
			$range = $this->input->get('range');
		}
		$range_info = $this->dashboard_model->get_range_info($range);
		$range_label = $range_info['label'];

		$data=array_merge($this->data,$this->dashboard_model->get_bar_chart(),$this->dashboard_model->get_pie_chart($selected_branch));
		$data['range'] = $range;
		$data['range_label'] = $range_label;
		if(is_admin()){
			$data = array_merge($data,$this->dashboard_model->get_subscription_chart());
		}
		$data['selected_branch'] = $selected_branch;
		// MartPoint Retail Dashboard V2 - KPI Data (range-aware)
		$data['today_sales']     = $this->dashboard_model->get_sales_by_range($range, $selected_branch);
		$data['today_profit']    = $this->dashboard_model->get_profit_by_range($range, $selected_branch);
		$data['today_expenses']  = $this->dashboard_model->get_expenses_by_range($range, $selected_branch);
		$data['outstanding']     = $this->dashboard_model->get_outstanding_debts($selected_branch);
		$data['low_stock_count'] = $this->dashboard_model->get_low_stock_count($selected_branch);
		$data['low_stock_items'] = $this->dashboard_model->get_low_stock_items($selected_branch);
		$data['top_debtors']     = $this->dashboard_model->get_top_debtors($selected_branch);
		$data['top_products']    = $this->dashboard_model->get_top_selling_products($selected_branch, $range);
		$data['cash_in_hand']    = $this->dashboard_model->get_cash_in_hand($selected_branch);
		$data['recent_activities'] = $this->dashboard_model->get_recent_activities($selected_branch);
		$data['insights']        = $this->dashboard_model->get_insights($selected_branch);
		$data['branch_performance'] = $this->dashboard_model->get_branch_performance($range);
		$data['page_title']=$this->lang->line('dashboard');

		// Clock-in status for dashboard (all non-admin staff)
		$data['needs_clock_in'] = false;
		if(!is_admin()){
			$this->load->model('attendance_model');
			$data['needs_clock_in'] = !$this->attendance_model->needsClockOut($this->session->userdata('inv_userid'));
		}

		if(isset($_POST['store_id'])){
			$data['store_id'] =$_POST['store_id'];
		}
		if(!$this->permissions('dashboard_view')){
			$this->load->view('role/dashboard_empty',$data);
		}
		else{
			$this->load->view('dashboard',$data);
		}
		
	}
	public function get_storewise_details($from='All'){

			//$from= $this->input->get_post('from');
			if(is_user()){
				$this->db->where("id!=1");
			}
			$q1=$this->db->select("*")->get("db_store");
		        if($q1->num_rows()>0){
		          $i=1;
		          foreach ($q1->result() as $row){
		          	
		          	/*SALES TOTAL*/
		            if($from=='Today'){
		          		$this->db->where("sales_date > DATE_SUB(NOW(), INTERVAL 1 DAY)");
		          	}
		          	if($from=='Weekly'){
		          		$this->db->where("sales_date > DATE_SUB(NOW(), INTERVAL 1 WEEK)");
		          	}
		          	if($from=='Monthly'){
		          		$this->db->where("sales_date > DATE_SUB(NOW(), INTERVAL 1 MONTH)");
		          	}
		          	if($from=='Yearly'){
		          		$this->db->where("sales_date > DATE_SUB(NOW(), INTERVAL 1 YEAR)");
		          	}
		            $this->db->where("store_id",$row->id); 
		            $this->db->select("COALESCE(sum(grand_total),0) AS tot_sal_grand_total");
		            $this->db->from("db_sales");
		            $this->db->where("sales_status='Final'");
		            $sal_total=$this->db->get()->row()->tot_sal_grand_total;
		      		
		      		/*SALES DUE*/
		            if($from=='Today'){
		          		$this->db->where("sales_date > DATE_SUB(NOW(), INTERVAL 1 DAY)");
		          	}
		          	if($from=='Weekly'){
		          		$this->db->where("sales_date > DATE_SUB(NOW(), INTERVAL 1 WEEK)");
		          	}
		          	if($from=='Monthly'){
		          		$this->db->where("sales_date > DATE_SUB(NOW(), INTERVAL 1 MONTH)");
		          	}
		          	if($from=='Yearly'){
		          		$this->db->where("sales_date > DATE_SUB(NOW(), INTERVAL 1 YEAR)");
		          	}
		            $this->db->where("store_id",$row->id); 
		            $this->db->select("COALESCE(sum(grand_total),0)-COALESCE(sum(paid_amount),0) AS sales_due_total");
		            $this->db->from("db_sales");
		            $this->db->where("sales_status='Final'");
		            $sales_due_total=$this->db->get()->row()->sales_due_total;

		            /*EXPENSE */
		            if($from=='Today'){
		          		$this->db->where("expense_date > DATE_SUB(NOW(), INTERVAL 1 WEEK)");
		          	}
		          	if($from=='Weekly'){
		          		$this->db->where("expense_date > DATE_SUB(NOW(), INTERVAL 1 WEEK)");
		          	}
		          	if($from=='Monthly'){
		          		$this->db->where("expense_date > DATE_SUB(NOW(), INTERVAL 1 MONTH)");
		          	}
		          	if($from=='Yearly'){
		          		$this->db->where("expense_date > DATE_SUB(NOW(), INTERVAL 1 YEAR)");
		          	}
		            $this->db->where("store_id",$row->id); 
		            $this->db->select("COALESCE(SUM(expense_amt),0) AS exp_total");
		            $this->db->from("db_expense");
		            $exp_total=$this->db->get()->row()->exp_total;


		            echo "<tr>";
		            echo "<td>".$i++."</td>";
		            echo "<td>".$row->store_name."</td>";
		            echo "<td>".$this->store_wise_currency($row->id,store_number_format($sal_total))."</td>";
		            echo "<td>".$this->store_wise_currency($row->id,store_number_format($exp_total))."</td>";
		            echo "<td>".$this->store_wise_currency($row->id,store_number_format($sales_due_total))."</td>";
		            echo "</tr>";
		          }//foreach
		        }
		
	}

	public function ajax_list() {
		$this->load->model('dashboard_model','items');
		$list = $this->items->get_datatables();

		$data = array();
		$no = $_POST['start'];
		foreach ($list as $items) {
			$no++;
			$row = array();
			$row[] = $items->item_code;
			$row[] = $items->item_name;
			$row[] = $items->category_name;
			$row[] = $items->brand_name;
			$row[] = $items->stock;
			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->items->count_all(),
			"recordsFiltered" => $this->items->count_filtered(),
			"data" => $data,
		);
		//output to json format
		echo json_encode($output);
	}

	/**
	 * Daily Business Summary Report
	 */
	public function daily_summary(){
		if(!$this->permissions('dashboard_view')){
			redirect(base_url('dashboard'),'refresh');
		}

		$this->load->model('dashboard_model');

		$date = $this->input->get('date');
		$date_from = $this->input->get('date_from');
		$date_to = $this->input->get('date_to');

		// Support single date legacy param OR range params
		if(!empty($date_from) && !empty($date_to)){
			$summary = $this->dashboard_model->get_daily_summary($date_from, $date_to);
			$selected_date = $date_from;
			$selected_date_to = $date_to;
		} else {
			if(empty($date)){
				$date = date('Y-m-d');
			}
			$summary = $this->dashboard_model->get_daily_summary($date);
			$selected_date = $date;
			$selected_date_to = $date;
		}

		$data = $this->data;
		$data['summary'] = $summary;
		$data['selected_date'] = $selected_date;
		$data['selected_date_to'] = $selected_date_to;
		$data['store_name'] = $this->db->select('store_name')->where('id',get_current_store_id())->get('db_store')->row()->store_name;
		$data['page_title'] = 'Daily Business Summary';

		$this->load->view('daily_summary', $data);
	}

	/**
	 * API endpoint for daily summary data (JSON)
	 */
	public function daily_summary_api(){
		if(!$this->permissions('dashboard_view')){
			echo json_encode(array('error'=>'Unauthorized'));
			exit;
		}

		$this->load->model('dashboard_model');
		$date = $this->input->get('date');
		$date_from = $this->input->get('date_from');
		$date_to = $this->input->get('date_to');

		if(!empty($date_from) && !empty($date_to)){
			$summary = $this->dashboard_model->get_daily_summary($date_from, $date_to);
		} else {
			if(empty($date)){ $date = date('Y-m-d'); }
			$summary = $this->dashboard_model->get_daily_summary($date);
		}

		header('Content-Type: application/json');
		echo json_encode($summary);
	}

	/**
	 * Send daily summary email via EmailService (template-based)
	 */
	public function send_summary_email(){
		if(!$this->permissions('dashboard_view')){
			echo json_encode(array('status'=>'error','message'=>'Unauthorized'));
			exit;
		}

		$to_email = $this->input->post('to_email');
		$date = $this->input->post('date');
		$date_to = $this->input->post('date_to');
		if(empty($to_email) || empty($date)){
			echo json_encode(array('status'=>'error','message'=>'Email and date are required'));
			exit;
		}

		$this->load->model('dashboard_model');
		$this->load->model('email_service');

		$summary = (!empty($date_to) && $date_to !== $date)
			? $this->dashboard_model->get_daily_summary($date, $date_to)
			: $this->dashboard_model->get_daily_summary($date);
		$store_rec = get_store_details();

		$reportDateLabel = $summary['date_label'];

		// Build top products string
		$topProducts = '';
		if(count($summary['top_products']) > 0){
			$topProducts .= "<ul>";
			foreach($summary['top_products'] as $p){
				$topProducts .= "<li>" . htmlspecialchars($p['name']) . " — Qty: " . number_format($p['qty']) . " — Revenue: " . $this->currency($p['revenue']) . "</li>";
			}
			$topProducts .= "</ul>";
		} else {
			$topProducts = "<p>No top products for this period.</p>";
		}

		// Build low stock string
		$lowStock = '';
		if(count($summary['low_stock_items']) > 0){
			$lowStock .= "<ul>";
			foreach($summary['low_stock_items'] as $item){
				$lowStock .= "<li>" . htmlspecialchars($item['name']) . " — " . number_format($item['qty']) . " left (reorder at " . number_format($item['min']) . ")</li>";
			}
			$lowStock .= "</ul>";
		} else {
			$lowStock = "<p>No low stock items.</p>";
		}

		// Attendance summary for email
		$attendance_str = '';
		$attendance_html = '';
		$attendance_text = '';
		if(($summary['attendance']['total_staff'] ?? 0) > 0){
			$attendance_str = $summary['attendance']['present'] . '/' . $summary['attendance']['total_staff'] . ' present';
			$attendance_html = "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse;font-size:14px;'>";
			$attendance_html .= "<tr style='background:#f5f5f5;'><th align='left'>Name</th><th align='left'>Position</th><th align='left'>Status</th></tr>";
			$attendance_text = "Staff Attendance:\n";
			foreach($summary['attendance']['staff_list'] as $st){
				$attendance_html .= "<tr><td>" . htmlspecialchars($st['name']) . "</td><td>" . htmlspecialchars($st['position']) . "</td><td>" . ($st['status']==='Present' ? '✅ Present' : '❌ Absent') . "</td></tr>";
				$attendance_text .= "- " . $st['name'] . " (" . $st['position'] . ") — " . $st['status'] . "\n";
			}
			$attendance_html .= "</table>";
			$attendance_text .= "\n";
		}

		// Patch existing template to include attendance detail if missing (one-time migration)
		$this->load->model('email_template_model');
		$tpl = $this->email_template_model->getByKey('daily_business_summary');
		if($tpl && strpos($tpl->html_body, '{attendance_detail_html}') === false){
			$html = str_replace(
				'<p><strong>Top Selling Products:</strong>',
				'<p><strong>Attendance:</strong> {attendance_summary}</p>{attendance_detail_html}<p><strong>Top Selling Products:</strong>',
				$tpl->html_body
			);
			if(strpos($html, '{attendance_detail_html}') === false){
				// fallback: insert before Outstanding Debts or at end of body
				$html = str_replace(
					'<p><strong>Outstanding Debts:</strong>',
					'<p><strong>Attendance:</strong> {attendance_summary}</p>{attendance_detail_html}<p><strong>Outstanding Debts:</strong>',
					$html
				);
			}
			$text = str_replace(
				'Outstanding Debts: {outstanding_debts}\n\n',
				'Outstanding Debts: {outstanding_debts}\n{attendance_detail_text}\n',
				$tpl->text_body
			);
			if(strpos($text, '{attendance_detail_text}') === false){
				$text = str_replace(
					'Top Selling Products:',
					'Attendance: {attendance_summary}\n{attendance_detail_text}Top Selling Products:',
					$text
				);
			}
			$this->email_template_model->update($tpl->id, [
				'html_body' => $html,
				'text_body' => $text
			]);
		}

		$result = $this->email_service->sendTemplate(
			'daily_business_summary',
			$to_email,
			[
				'store_name'            => $store_rec->store_name,
				'report_date'           => $reportDateLabel,
				'total_sales'           => $this->currency($summary['sales']['total'] ?? 0),
				'total_profit'          => ($summary['profit']['available'] ?? false) ? $this->currency($summary['profit']['gross_profit']) : 'N/A',
				'total_expenses'        => $this->currency($summary['expenses']['total'] ?? 0),
				'net_position'          => $this->currency($summary['net_position'] ?? 0),
				'cash_expected'         => $this->currency($summary['sales']['cash_expected'] ?? 0),
				'outstanding_debts'     => $this->currency($summary['outstanding_debts']['total'] ?? 0),
				'transaction_count'     => $summary['sales']['transactions'] ?? 0,
				'top_selling_products'  => $topProducts,
				'low_stock_items'       => $lowStock,
				'attendance_present'    => $summary['attendance']['present'] ?? 0,
				'attendance_total'      => $summary['attendance']['total_staff'] ?? 0,
				'attendance_summary'    => $attendance_str,
				'attendance_detail_html'=> $attendance_html,
				'attendance_detail_text'=> $attendance_text,
			],
			['related_module' => 'daily_summary', 'related_record_id' => $date . ($date_to ? '_' . $date_to : '')]
		);

		if($result['success']){
			echo json_encode(array('status'=>'success','message'=>'Email sent successfully'));
		} else {
			// Return fallback flag so client can use mailto
			echo json_encode(array('status'=>'fallback','message'=>$result['message']));
		}
	}

}
