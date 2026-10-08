<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
	}
	public function dashboard_values(){
		$this->load->model('dashboard_model');//Model
		$data=$this->dashboard_model->breadboard_values();//Model->Method
		echo json_encode($data);
	}

	public function dismiss_update_warning(){
		$this->session->set_userdata('db_update_dismissed', 1);
		echo json_encode(['status' => 'ok']);
	}

	public function index($val='')
	{ 	
		if(stripos(trim($this->session->userdata('role_name') ?: ''), 'cashier') !== false){
			redirect(base_url('pos'));
		}
		// The vendor's central domain gets a SaaS fleet-analytics dashboard —
		// the retail dashboard is meaningless there (no sales data lives here).
		// central_dashboard.php hosts the fleet KPIs, the Central-vs-channel
		// version line and the "Slim menu (hide retail menus)" checkbox that
		// mp_sidebar.php reads. Without this branch Central falls through to
		// the retail dashboard and that screen is orphaned.
		if(function_exists('mp_is_central') && mp_is_central()){
			$this->centralConsole();
			return;
		}
		// Creator / Digital Store businesses land on the Creator Workspace (use ?classic=1 for the retail dashboard)
		if(function_exists('mp_get_store_profile') && $this->input->get('classic') === NULL){
			$bp = mp_get_store_profile();
			if(($bp['industry_type'] ?? '') === 'creator'){
				redirect(base_url('creator'));
			}
		}
		// A print shop's workspace is the Printing module, not the retail
		// dashboard. Its own preset has declared `dashboard_template =>
		// service_business` all along, but /dashboard had no branch for it, so
		// switching a store to printing changed nothing on screen — the store
		// said "printing" and the dashboard still showed retail sales, stock
		// and profit.
		//
		// Redirected the same way Creator is, and for the same reason: the
		// printing workspace owns its own layout and navigation, so it is a
		// destination rather than a branch rendered in place. `?classic=1`
		// still gives the retail dashboard for anyone who wants it (Creator
		// honours the same escape hatch).
		//
		// Gated on `production_workflow`, the flag the printing preset actually
		// declares — NOT `printing_workflow`, which exists nowhere and would
		// have made this redirect permanently dead. The feature check stays in
		// Printing::_check_feature() too, so an unactivated module explains
		// itself instead of dead-ending.
		if(function_exists('mp_get_store_profile') && $this->input->get('classic') === NULL){
			$bp_print = mp_get_store_profile();
			if(($bp_print['industry_type'] ?? '') === 'printing'
				&& function_exists('mp_feature_enabled')
				&& mp_feature_enabled('production_workflow')){
				redirect(base_url('printing'));
			}
		}
		if(!function_exists('physio_enabled')) $this->load->helper('physio');
		if(physio_enabled()){
			$this->clinicDashboard();
			return;
		}
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

		$data=array_merge($this->data,$this->dashboard_model->get_bar_chart($range, $selected_branch),$this->dashboard_model->get_pie_chart($selected_branch));
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

		$site = get_site_details();
		$daily_target = (float)($site->sales_target ?? 50000);
		$today = $data['today_sales']['today'] ?? 0;
		$data['daily_target'] = $daily_target;
		$data['daily_target_progress'] = ($daily_target > 0) ? min(100, round(($today / $daily_target) * 100, 1)) : 0;

		$data['outstanding']     = $this->dashboard_model->get_outstanding_debts($selected_branch);
		$data['low_stock_count'] = $this->dashboard_model->get_low_stock_count($selected_branch);
		$data['low_stock_items'] = $this->dashboard_model->get_low_stock_items($selected_branch);
		$data['top_debtors']     = $this->dashboard_model->get_top_debtors($selected_branch);
		$data['top_products']    = $this->dashboard_model->get_top_selling_products($selected_branch, $range);
		$data['cash_in_hand']    = $this->dashboard_model->get_cash_in_hand($selected_branch);
		$data['recent_activities'] = $this->dashboard_model->get_recent_activities($selected_branch);
		$data['insights']        = $this->dashboard_model->get_insights($selected_branch);

		// Intelligence Report. Distinct from $insights above: get_insights()
		// restates what is happening now, whereas Intelligence_model reasons over
		// the store's history to say what is about to happen and why — stock
		// cover at the real selling rate, customer payment behaviour, tied-up
		// cash. Kept as its own key so neither has to compromise its wording.
		// Wrapped so an insight failure can never take the dashboard down.
		$data['intel'] = [];
		try {
			$this->load->model('intelligence_model', 'intel');
			// A print shop that reaches the retail dashboard still gets print
			// insights. Decided by mp_is_print_shop() (the same flag the printing
			// module gates on) rather than the industry label, which can drift
			// from what the store actually does.
			$data['intel'] = mp_is_print_shop()
				? $this->intel->for_printing(get_current_store_id())
				: $this->intel->for_retail(get_current_store_id());
		} catch (Throwable $e) {
			log_message('error', 'Dashboard: intelligence failed — ' . $e->getMessage());
		}
		$data['branch_performance'] = $this->dashboard_model->get_branch_performance($range);
		$data['best_selling_variant'] = $this->dashboard_model->get_best_selling_variant($selected_branch, $range);
		// Range-aware invoice + new-customer counts (respond to the date filter)
		$data['invoices_range']  = $this->dashboard_model->get_invoices_by_range($range, $selected_branch);
		$data['new_customers_range'] = $this->dashboard_model->get_new_customers_by_range($range, $selected_branch);
		$data['range_info']      = $range_info;
		$data['page_title']=$this->lang->line('dashboard');

		// Clock-in status for dashboard (all non-admin staff; Store Admin is exempt)
		$data['needs_clock_in'] = false;
		if(!is_admin() && !is_store_admin()){
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
			$data['content'] = $this->load->view('dashboard',$data, TRUE);
			$this->load->view('mp_layout', $data);
		}
		
	}
	/**
	 * Central-only landing page — SaaS analytics over the fleet registry.
	 * Everything derives from db_fleet_installs rows written by install
	 * heartbeats: freshness (last_seen), versions, license state, region
	 * (store_state/country reported by the client), and usage quotas.
	 */
	private function centralConsole(){
		$data = $this->data;
		$data['page_title'] = 'Central Dashboard';

		$empty = [
			'total' => 0, 'active_24h' => 0, 'active_7d' => 0, 'stale' => 0, 'never' => 0,
			'licensed' => 0, 'expiring' => 0, 'expired' => 0, 'suspended' => 0, 'unlicensed' => 0,
			'by_version' => [], 'by_plan' => [], 'by_region' => [], 'growth' => [],
			'usage' => [], 'recent' => [], 'outdated' => 0,
		];

		if (!$this->db->table_exists('db_fleet_installs')) {
			$data['stats'] = $empty;
			$data['content'] = $this->load->view('central_dashboard', $data, TRUE);
			$this->load->view('mp_layout', $data);
			return;
		}

		$installs = $this->db->order_by('last_seen', 'desc')->get('db_fleet_installs')->result();
		$now = time();
		$s = $empty;
		// The "latest release" baseline is the manifest Central itself published
		// (release_build/release-manifest.json) — a local file read, not a live
		// GitHub fetch, so this page never waits on the network. Falls back to
		// the highest install-reported version when no manifest exists yet.
		$latest = '';
		$data['channel_version'] = null;
		$manifestFile = FCPATH . 'release_build/release-manifest.json';
		if (is_file($manifestFile)) {
			$m = json_decode((string) @file_get_contents($manifestFile), true);
			$data['channel_version'] = $m['version'] ?? null;
		}
		foreach ($installs as $r) {
			if ($latest === '' && !empty($r->version)) { $latest = $r->version; }
			if (!empty($r->version) && version_compare($r->version, $latest, '>')) { $latest = $r->version; }
		}
		if (!empty($data['channel_version'])) { $latest = $data['channel_version']; }

		foreach ($installs as $r) {
			$s['total']++;
			$seen = !empty($r->last_seen) ? strtotime($r->last_seen) : 0;
			if ($seen <= 0) {
				$s['never']++;
			} elseif ($seen >= $now - 86400) {
				$s['active_24h']++;
			} elseif ($seen >= $now - 7 * 86400) {
				$s['active_7d']++;
			} else {
				$s['stale']++;
			}

			$st = strtoupper((string) ($r->license_status ?? ''));
			if ($st === 'ACTIVE') { $s['licensed']++; }
			elseif ($st === 'EXPIRING_SOON') { $s['licensed']++; $s['expiring']++; }
			elseif ($st === 'EXPIRED') { $s['expired']++; }
			elseif ($st === 'SUSPENDED') { $s['suspended']++; }
			else { $s['unlicensed']++; }

			$v = trim((string) ($r->version ?? '')) ?: 'unknown';
			$s['by_version'][$v] = ($s['by_version'][$v] ?? 0) + 1;
			if ($latest !== '' && $v !== 'unknown' && version_compare($v, $latest, '<')) { $s['outdated']++; }

			$p = trim((string) ($r->plan_name ?? '')) ?: 'No plan';
			$s['by_plan'][$p] = ($s['by_plan'][$p] ?? 0) + 1;

			$region = trim((string) ($r->store_state ?? '')) ?: (trim((string) ($r->store_country ?? '')) ?: 'Unreported');
			$s['by_region'][$region] = ($s['by_region'][$region] ?? 0) + 1;

			if (!empty($r->created_at)) {
				$mk = date('Y-m', strtotime($r->created_at));
				$s['growth'][$mk] = ($s['growth'][$mk] ?? 0) + 1;
			}

			// Aggregate live usage from heartbeat quotas ({key, used, limit}).
			foreach ((array) json_decode((string) ($r->usage_json ?? ''), true) as $q) {
				$k = (string) ($q['key'] ?? '');
				if ($k === '') { continue; }
				if (!isset($s['usage'][$k])) {
					$s['usage'][$k] = ['label' => (string) ($q['label'] ?? $k), 'used' => 0, 'limit' => 0, 'unit' => (string) ($q['unit'] ?? '')];
				}
				$s['usage'][$k]['used']  += (int) ($q['used'] ?? 0);
				$s['usage'][$k]['limit'] += (int) ($q['limit'] ?? 0);
			}

			if (count($s['recent']) < 10) {
				$s['recent'][] = $r;
			}
		}
		ksort($s['growth']);
		arsort($s['by_region']);
		arsort($s['by_version']);
		arsort($s['by_plan']);
		$s['latest_version'] = $latest;

		$data['stats'] = $s;

		// Central's own freshness vs the published release channel — the
		// banner on the dashboard drives the same chunked update installs run.
		$data['central_version'] = function_exists('app_version') ? app_version() : '';
		$data['central_slim_menu'] = 1;
		if ($this->db->field_exists('central_slim_menu', 'db_sitesettings')) {
			$slim = $this->db->select('central_slim_menu')->where('id', 1)->get('db_sitesettings')->row();
			$data['central_slim_menu'] = $slim ? (int) $slim->central_slim_menu : 1;
		}
		// Central vs the published channel — a plain version compare against the
		// manifest read above; no network call, no license gate on Central.
		$data['central_update_available'] = !empty($data['channel_version'])
			&& !empty($data['central_version'])
			&& version_compare($data['channel_version'], $data['central_version'], '>');
		$data['central_update_blocked'] = null;

		$data['content'] = $this->load->view('central_dashboard', $data, TRUE);
		$this->load->view('mp_layout', $data);
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
		            $this->db->where("sales_status IN ('Final','Opening')");
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
			$row[] = $no;
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

		// Prevent stale cached versions from showing the old desktop UI on mobile
		$this->output->set_header('Cache-Control: no-cache, must-revalidate, max-age=0');
		$this->output->set_header('Pragma: no-cache');
		$this->output->set_header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

		$force_mobile = (is_mobile() || $this->input->get('mobile') === '1');
		if($force_mobile){
			$this->load->view('mobile/daily_summary', $data);
		} else {
			$data['content'] = $this->load->view('daily_summary', $data, TRUE);
			$this->load->view('mp_layout', $data);
		}
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

	public function help()
	{
		if(is_cashier()){
			redirect(base_url('pos'));
		}
		$data = $this->data;
		$data['page_title'] = 'Help Center';
		$data['content'] = $this->load->view('help', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function support()
	{
		if(is_cashier()){
			redirect(base_url('pos'));
		}
		$data = $this->data;
		$data['page_title'] = 'Support';
		$data['content'] = $this->load->view('support', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}


	/** Clinical landing page; every destination keeps its controller permission gate. */
	private function clinicDashboard(){
		$access = array(
			'patients' => physio_can('patients_view'),
			'appointments' => physio_can('appointments_view'),
			'queue' => physio_can('care_queue_view'),
			'sessions' => physio_can('sessions_view'),
			'nursing' => physio_can_any(array('nursing_tasks_view','porter_tasks_view')),
			'ward' => physio_can('admissions_view'),
			'accounts' => physio_can_any(array('patient_billing_view','patient_funds_view')),
			'procurement' => $this->permissions('suppliers_view') || $this->permissions('purchase_view'),
			'administration' => $this->permissions('business_setup') || $this->permissions('store_edit')
				|| $this->permissions('users_view') || $this->permissions('roles_view')
				|| physio_can_any(array('assessment_templates_manage','imports_view','portal_manage')),
		);
		if(!in_array(true, $access, true)){
			$this->show_access_denied_page();
			return;
		}
		$data = $this->data;
		$data['page_title'] = 'Clinic overview';
		$data['clinic_access'] = $access;
		$data['clinic_stats'] = $this->clinicStats($access);
		$data['clinic_activity'] = $this->clinicActivity($access);
		/*
		 * The shell shows business insights in the topbar. Every other business
		 * type gets them from dashboard_model; the clinic branch returned early
		 * and never set them, so the band was permanently empty.
		 * get_insights() reads sales/profit figures, so it is only consulted for
		 * a viewer who may see the dashboard at all.
		 */
		$data['insights'] = array();
		if($this->permissions('dashboard_view')){
			$this->load->model('dashboard_model');
			$data['insights'] = $this->dashboard_model->get_insights('');
		}
		$data['content'] = $this->load->view('physio_dashboard', array_merge($data, array(
			'clinic_access' => $access,
		)), TRUE);
		$this->load->view('mp_layout', $data);
	}


	/**
	 * Clinical KPI figures for the clinic dashboard.
	 *
	 * Every figure is read from the same table the corresponding screen uses,
	 * and a figure is only computed when the user holds the grant for that
	 * screen — a role that cannot see billing is never shown billing totals.
	 * Counts are store-scoped; the user's branch scope is applied on the
	 * screens themselves so the dashboard never widens access.
	 */
	private function clinicStats(array $access){
		$storeId = (int)get_current_store_id();
		$out = array();
		$count = function($table, array $where = array()) use ($storeId){
			if(!$this->db->table_exists($table)) return null;
			$this->db->where('store_id', $storeId);
			foreach($where as $k => $v){ $this->db->where($k, $v); }
			return (int)$this->db->count_all_results($table);
		};

		if($access['patients']){
			$row = $this->db->select('COUNT(*) total, SUM(status=1 AND deceased=0) active, SUM(deceased=1) deceased')
				->where('store_id', $storeId)->get('db_patients')->row();
			$out['patients'] = array(
				'label' => 'Active patients',
				'value' => (int)($row->active ?? 0),
				'sub'   => number_format((int)($row->total ?? 0)) . ' on register',
				'url'   => 'patients',
				'icon'  => 'fa-address-book-o',
			);
		}
		if($access['appointments']){
			$out['appointments'] = array(
				'label' => 'Booked today',
				'value' => (int)$this->db->where('store_id', $storeId)
					->where('DATE(scheduled_at)', date('Y-m-d'))->count_all_results('db_appointments'),
				'sub'   => 'appointments in the diary',
				'url'   => 'appointments',
				'icon'  => 'fa-calendar',
			);
		}
		if($access['queue']){
			$queue = $this->db->select('queue_stage, COUNT(*) n')->where('store_id', $storeId)
				->where('queue_stage IS NOT NULL', null, false)->where('queue_stage !=', 'closed')
				->group_by('queue_stage')->get('db_encounters')->result();
			$total = 0; foreach($queue as $q){ $total += (int)$q->n; }
			$out['queue'] = array(
				'label' => 'In care queue',
				'value' => $total,
				'sub'   => $total ? 'awaiting triage, treatment or payment' : 'queue is clear',
				'url'   => 'care_queue',
				'icon'  => 'fa-list-ol',
				'alert' => $total > 0,
			);
		}
		if($access['sessions'] && $this->db->table_exists('db_treatment_sessions')){
			$today = (int)$this->db->where('store_id', $storeId)
				->where('DATE(scheduled_at)', date('Y-m-d'))->count_all_results('db_treatment_sessions');
			$upcoming = (int)$this->db->where('store_id', $storeId)->where('status', 'scheduled')
				->count_all_results('db_treatment_sessions');
			$out['sessions'] = array(
				'label' => 'Sessions today',
				'value' => $today,
				'sub'   => number_format($upcoming) . ' scheduled overall',
				'url'   => 'sessions',
				'icon'  => 'fa-stethoscope',
			);
		}
		if($access['nursing'] && $this->db->table_exists('db_nursing_tasks')){
			$open = (int)$this->db->where('store_id', $storeId)->where('status', 'open')
				->count_all_results('db_nursing_tasks');
			$overdue = (int)$this->db->where('store_id', $storeId)->where('status', 'open')
				->where('due_at <', date('Y-m-d H:i:s'))->count_all_results('db_nursing_tasks');
			$out['tasks'] = array(
				'label' => 'Ward tasks open',
				'value' => $open,
				'sub'   => $overdue ? $overdue . ' overdue right now' : 'nothing overdue',
				'url'   => 'inpatient/tasks',
				'icon'  => 'fa-heartbeat',
				'alert' => $overdue > 0,
			);
		}
		if($access['ward']){
			$beds = $count('db_beds');
			$inUse = $this->db->table_exists('db_bed_occupancy')
				? (int)$this->db->where('store_id', $storeId)->where('to_at IS NULL', null, false)
					->count_all_results('db_bed_occupancy') : 0;
			if($beds !== null){
				$out['beds'] = array(
					'label' => 'Beds free',
					'value' => max(0, $beds - $inUse),
					'sub'   => $inUse . ' of ' . $beds . ' occupied',
					'url'   => 'inpatient/beds',
					'icon'  => 'fa-bed',
				);
			}
		}
		// Money figures keep the same split as the screens: billing data belongs
		// to patient_billing_view, held funds to patient_funds_view. Each KPI
		// links to a route its own grant can actually open, so no figure is
		// ever presented behind a locked door.
		if(physio_can('patient_billing_view')){
			$due = (float)$this->db->select('COALESCE(SUM(s.grand_total - s.paid_amount),0) AS due', false)
				->from('db_sales s')
				->join('db_patients p', 'p.customer_id = s.customer_id AND p.store_id = s.store_id')
				->where('s.store_id', $storeId)
				->where('(s.plan_id IS NOT NULL OR s.reference_no LIKE "OPENING-%")', null, false)
				->where('s.grand_total > s.paid_amount', null, false)
				->get()->row()->due;
			$out['due'] = array(
				'label' => 'Bills outstanding',
				'value' => $this->currency($due, true),
				'sub'   => 'across patient accounts',
				'url'   => 'patient_billing',
				'icon'  => 'fa-file-text-o',
				'money' => true,
			);
		}
		if(physio_can('patient_funds_view') && $this->db->table_exists('db_fund_reservations')){
			$reserved = (float)$this->db->select('COALESCE(SUM(amount_reserved - amount_consumed),0) AS s', false)
				->where('store_id', $storeId)->where('status', 'active')
				->get('db_fund_reservations')->row()->s;
			$out['funds'] = array(
				'label' => 'Funds held',
				'value' => $this->currency($reserved, true),
				'sub'   => 'reserved against treatment plans',
				'url'   => 'patient_funds',
				'icon'  => 'fa-money',
				'money' => true,
			);
		}
		return $out;
	}

	/** Recent clinical activity — the day's arrivals and latest registrations. */
	private function clinicActivity(array $access){
		$storeId = (int)get_current_store_id();
		$feed = array();
		if($access['patients'] && $this->db->table_exists('db_patients')){
			$rows = $this->db->select('p.id, p.patient_code, p.created_date, c.customer_name')
				->from('db_patients p')
				->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
				->where('p.store_id', $storeId)->order_by('p.id', 'desc')->limit(5)->get()->result();
			foreach($rows as $r){
				$feed[] = array(
					'icon'  => 'fa-user-plus',
					'title' => trim((string)$r->customer_name) ?: ('Patient #' . $r->id),
					'meta'  => 'Registered ' . ($r->created_date ? date('M j, Y', strtotime($r->created_date)) : '—'),
				);
			}
		}
		return $feed;
	}

}
