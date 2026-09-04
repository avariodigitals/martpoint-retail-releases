<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mobile extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('dashboard_model','dashboard');
		$this->load->model('sales_model','sales');
	}

	public function test_variants()
	{
		// Bypass auth for CLI testing only
		if(!is_cli() && php_sapi_name() !== 'cli'){
			echo "CLI only";
			return;
		}
		// Simulate POST data for a variant product with 3 variants
		$_POST = array(
			'command' => 'save',
			'q_id' => '',
			'item_code' => '',
			'item_name' => 'CLI TEST VARIANT ' . date('H:i:s'),
			'brand_id' => '0',
			'category_id' => '82',
			'sku' => '',
			'hsn' => '',
			'unit_id' => '61',
			'alert_qty' => '0',
			'price' => '100',
			'tax_id' => '149',
			'purchase_price' => '100',
			'tax_type' => 'Inclusive',
			'profit_margin' => '0',
			'sales_price' => '120',
			'seller_points' => '0',
			'custom_barcode' => '',
			'description' => '',
			'item_group' => 'Variants',
			'discount_type' => 'Percentage',
			'discount' => '0',
			'mrp' => '0',
			'hidden_rowcount' => '3',
			'tr_variant_id_1' => '261',
			'td_data_1_2' => 'CLI-SKU-1',
			'td_data_1_3' => '100',
			'td_data_1_4' => '100',
			'td_data_1_5' => '0',
			'td_data_1_6' => '120',
			'td_data_1_8' => '',
			'td_data_1_9' => '',
			'td_data_1_10' => '0',
			'td_data_1_11' => '0',
			'count_id_1' => '',
			'item_code_1' => '',
			'tr_variant_id_2' => '262',
			'td_data_2_2' => 'CLI-SKU-2',
			'td_data_2_3' => '100',
			'td_data_2_4' => '100',
			'td_data_2_5' => '0',
			'td_data_2_6' => '120',
			'td_data_2_8' => '',
			'td_data_2_9' => '',
			'td_data_2_10' => '0',
			'td_data_2_11' => '0',
			'count_id_2' => '',
			'item_code_2' => '',
			'tr_variant_id_3' => '265',
			'td_data_3_2' => 'CLI-SKU-3',
			'td_data_3_3' => '100',
			'td_data_3_4' => '100',
			'td_data_3_5' => '0',
			'td_data_3_6' => '120',
			'td_data_3_8' => '',
			'td_data_3_9' => '',
			'td_data_3_10' => '0',
			'td_data_3_11' => '0',
			'count_id_3' => '',
			'item_code_3' => '',
		);
		$_REQUEST = array_merge($_GET, $_POST, $_COOKIE);

		$this->load->model('items_model','items');
		echo "Calling save_record...\n";
		$result = $this->items->save_record(array('command' => 'save'));
		echo "Result: $result\n";

		// Check what was saved
		$store_id = get_current_store_id();
		$parent = $this->db->where('store_id', $store_id)->where('item_group', 'Variants')->order_by('id', 'desc')->limit(1)->get('db_items')->row();
		if($parent){
			echo "Parent: id={$parent->id} name={$parent->item_name}\n";
			$children = $this->db->where('parent_id', $parent->id)->order_by('id', 'asc')->get('db_items')->result();
			echo "Children count: " . count($children) . "\n";
			foreach($children as $c){
				echo "  Child: id={$c->id} name={$c->item_name} sku={$c->sku} variant_id={$c->variant_id}\n";
			}
		}
	}

	public function diag()
	{
		header('Content-Type: text/plain');
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		echo "=== MartPoint Mobile Diagnostics ===\n";
		echo "PHP: " . PHP_VERSION . "\n";
		echo "CI: " . CI_VERSION . "\n";
		echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

		// Check session
		echo "=== SESSION ===\n";
		echo "user_id: " . $this->session->userdata('inv_userid') . "\n";
		echo "username: " . $this->session->userdata('inv_username') . "\n";
		echo "role: " . $this->session->userdata('role_name') . "\n";
		echo "store_id: " . get_current_store_id() . "\n\n";

		// Check walk-in customer
		echo "=== WALK-IN CUSTOMER ===\n";
		$wic_id = get_walk_in_customer_id();
		echo "walk_in_customer_id: " . ($wic_id ?: 'NULL - MISSING!') . "\n";
		if($wic_id){
			$wic = $this->db->where('id', $wic_id)->get('db_customers')->row();
			echo "  name: " . ($wic->customer_name ?? 'N/A') . "\n";
			echo "  store_id: " . ($wic->store_id ?? 'N/A') . "\n";
			echo "  delete_bit: " . ($wic->delete_bit ?? 'N/A') . "\n";
		} else {
			echo "  WARNING: No walk-in customer for store_id=" . get_current_store_id() . "!\n";
			echo "  Mobile POS will fail to save sales.\n";
		}

		// Check recent mobile sales
		echo "\n=== RECENT MOBILE SALES ===\n";
		$recent_sales = $this->db->query("SELECT s.id, s.sales_code, s.customer_id, s.store_id, s.sales_status, s.grand_total, s.paid_amount, c.customer_name, (SELECT COUNT(*) FROM db_salesitems si WHERE si.sales_id = s.id) as item_count FROM db_sales s LEFT JOIN db_customers c ON c.id=s.customer_id WHERE s.store_id = ? ORDER BY s.id DESC LIMIT 5", [get_current_store_id()])->result();
		foreach($recent_sales as $s){
			echo "  #{$s->id} code={$s->sales_code} cust_id={$s->customer_id} cust_name=" . ($s->customer_name ?? 'DELETED') . " items={$s->item_count} total={$s->grand_total} paid={$s->paid_amount} status={$s->sales_status}\n";
		}

		// Check critical tables exist
		echo "=== TABLE CHECK ===\n";
		$tables = ['db_sales','db_salesitems','db_salespayments','db_hold','db_holditems',
			'db_items','db_tax','db_category','db_brands','db_customers','db_warehouse',
			'db_store','db_payment_modes','db_installment_plans','db_installment_payments',
			'db_attendance','db_shifts','ac_accounts','ac_transactions','db_store_pos_settings'];
		$store_id = get_current_store_id();
		foreach($tables as $t){
			$exists = $this->db->query("SHOW TABLES LIKE '$t'")->num_rows() > 0;
			echo ($exists ? "OK  " : "MISS") . " $t";
			if(!$exists){ echo " <-- MISSING!"; }
			echo "\n";
		}

		// Check payment modes have data
		echo "\n=== PAYMENT MODES ===\n";
		$modes = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_payment_modes')->result();
		echo "Count: " . count($modes) . "\n";
		if(count($modes) === 0){
			echo "WARNING: No payment modes for store_id=$store_id! Split payment will fail.\n";
			echo "Run: INSERT INTO db_payment_modes (store_id, code, name, is_default, sort_order, status, created_date) VALUES ($store_id, 'cash', 'Cash', 1, 1, 1, CURDATE());\n";
		}
		foreach($modes as $m){
			echo "  $m->code => $m->name (id=$m->id)\n";
		}

		// Check critical columns on db_salesitems
		echo "\n=== SALESITEMS COLUMNS ===\n";
		$cols = $this->db->query("SHOW COLUMNS FROM db_salesitems")->result();
		$need = ['price_type','staff_id','commission_amount','barcode_id','sold_serial_number','sold_imei_number'];
		$have = [];
		foreach($cols as $c){ $have[$c->Field] = true; }
		foreach($need as $n){
			echo (isset($have[$n]) ? "OK  " : "MISS") . " db_salesitems.$n\n";
		}

		// Check db_salespayments columns
		echo "\n=== SALESPAYMENTS COLUMNS ===\n";
		$cols = $this->db->query("SHOW COLUMNS FROM db_salespayments")->result();
		$need = ['payment_reference','confirmation_status','payment_mode_id','advance_adjusted','cheque_number','cheque_period','cheque_status'];
		$have = [];
		foreach($cols as $c){ $have[$c->Field] = true; }
		foreach($need as $n){
			echo (isset($have[$n]) ? "OK  " : "MISS") . " db_salespayments.$n\n";
		}

		// Check db_payment_modes columns (cashflow report depends on affects_cash_in_hand)
		echo "\n=== PAYMENT MODES COLUMNS ===\n";
		$pm_cols = $this->db->query("SHOW COLUMNS FROM db_payment_modes")->result();
		$pm_need = ['affects_cash_in_hand','enabled','is_system','requires_reference','requires_confirmation','icon_class','description'];
		$pm_have = [];
		foreach($pm_cols as $c){ $pm_have[$c->Field] = true; }
		foreach($pm_need as $n){
			echo (isset($pm_have[$n]) ? "OK  " : "MISS") . " db_payment_modes.$n\n";
		}
		// Check if cash mode has affects_cash_in_hand=1
		$cash_mode = $this->db->where('store_id', $store_id)->where('LOWER(code)', 'cash')->get('db_payment_modes')->row();
		if($cash_mode){
			$affects = isset($cash_mode->affects_cash_in_hand) ? (int)$cash_mode->affects_cash_in_hand : 'N/A (column missing)';
			echo ($affects === 1 ? "OK  " : "WARN") . " cash mode affects_cash_in_hand=$affects\n";
			if($affects !== 1){
				echo "  -> Cashflow report will show 0. Run migration to fix.\n";
			}
		} else {
			echo "WARN No cash mode found for store_id=$store_id\n";
		}

		// Check recent sales items
		echo "\n=== RECENT SALES ITEMS ===\n";
		$recent = $this->db->query("SELECT si.sales_id, si.item_id, si.sales_qty, si.price_per_unit, si.store_id, s.sales_code, s.store_id as sale_store_id FROM db_salesitems si LEFT JOIN db_sales s ON s.id=si.sales_id ORDER BY si.id DESC LIMIT 5")->result();
		if(count($recent) === 0){
			echo "WARN No sales items found in db_salesitems at all!\n";
		}
		foreach($recent as $r){
			echo "  sale_id={$r->sales_id} code=" . ($r->sales_code ?? 'NULL') . " item_id={$r->item_id} qty={$r->sales_qty} price={$r->price_per_unit} item_store={$r->store_id} sale_store={$r->sale_store_id}\n";
		}

		// Check PHP extensions
		echo "\n=== PHP EXTENSIONS ===\n";
		$exts = ['mysqli','pdo_mysql','json','mbstring','openssl','curl','gd','zip'];
		foreach($exts as $e){
			echo (extension_loaded($e) ? "OK  " : "MISS") . " $e\n";
		}

		// Check writable dirs
		echo "\n=== WRITABLE DIRS ===\n";
		$dirs = ['application/logs','application/cache','uploads','updates/migrations'];
		foreach($dirs as $d){
			$path = FCPATH . $d;
			echo (is_writable($path) ? "OK  " : "NOWRITE") . " $d\n";
		}

		echo "\n=== DONE ===\n";
		echo "Delete this diagnostic after use by removing the diag() method from Mobile.php\n";
	}

	public function index()
	{
		if(stripos(trim($this->session->userdata('role_name') ?: ''), 'cashier') !== false){
			redirect(base_url().'mobile/pos');
		}
		$data = $this->data;
		$data['page_title'] = 'Mobile';
		$store_id = get_current_store_id();

		$from = $this->input->get('from', TRUE);
		$to = $this->input->get('to', TRUE);
		if(empty($from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)){
			$from = date('Y-m-d');
		}
		if(empty($to) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)){
			$to = $from;
		}
		$seven_days = date('Y-m-d', strtotime($to . ' +7 days'));
		$data['from'] = $from;
		$data['to'] = $to;

		// Sales in range
		$this->db->select("COALESCE(SUM(grand_total),0) as total");
		$this->db->where("sales_status", "Final");
		$this->db->where("store_id", $store_id);
		$this->db->where("sales_date >=", $from);
		$this->db->where("sales_date <=", $to . ' 23:59:59');
		$sales = $this->db->get("db_sales")->row()->total;

		// Transactions in range
		$this->db->select("COUNT(*) as total");
		$this->db->where("sales_status", "Final");
		$this->db->where("store_id", $store_id);
		$this->db->where("sales_date >=", $from);
		$this->db->where("sales_date <=", $to . ' 23:59:59');
		$transactions = $this->db->get("db_sales")->row()->total;

		// Profit in range (revenue - cost)
		$this->db->select("COALESCE(SUM(a.grand_total),0) as revenue");
		$this->db->from("db_sales a");
		$this->db->where("a.sales_status", "Final");
		$this->db->where("a.store_id", $store_id);
		$this->db->where("a.sales_date >=", $from);
		$this->db->where("a.sales_date <=", $to . ' 23:59:59');
		$revenue = $this->db->get()->row()->revenue;

		$this->db->select("COALESCE(SUM(CASE WHEN b.purchase_price > 0 THEN b.purchase_price ELSE COALESCE(c.purchase_price,0) END * b.sales_qty),0) as cost");
		$this->db->from("db_sales a");
		$this->db->join("db_salesitems b", "a.id = b.sales_id", "left");
		$this->db->join("db_items c", "c.id = b.item_id", "left");
		$this->db->where("a.sales_status", "Final");
		$this->db->where("a.store_id", $store_id);
		$this->db->where("a.sales_date >=", $from);
		$this->db->where("a.sales_date <=", $to . ' 23:59:59');
		$cost = $this->db->get()->row()->cost;
		$profit = $revenue - $cost;

		// Expenses for selected range
		$this->db->select("COALESCE(SUM(expense_amt),0) as total");
		$this->db->where("store_id", $store_id);
		$this->db->where("expense_date >=", $from);
		$this->db->where("expense_date <=", $to . ' 23:59:59');
		$this->db->where("status", 1);
		$expenses = $this->db->get("db_expense")->row()->total;

		// Outstanding debt
		$this->db->select("COALESCE(SUM(grand_total - paid_amount),0) as total_debt, COUNT(*) as debtor_count");
		$this->db->where("sales_status", "Final");
		$this->db->where("(grand_total - paid_amount) >", 0);
		$this->db->where("store_id", $store_id);
		$debt = $this->db->get("db_sales")->row();

		// Recent sales
		$this->db->select("a.id, a.sales_code, a.grand_total, a.paid_amount, a.created_time, a.customer_id, c.customer_name, (a.grand_total - a.paid_amount) as due");
		$this->db->from("db_sales a");
		$this->db->join("db_customers c", "c.id = a.customer_id", "left");
		$this->db->where("a.sales_status", "Final");
		$this->db->where("a.store_id", $store_id);
		$this->db->where("a.sales_date >=", $from);
		$this->db->where("a.sales_date <=", $to . ' 23:59:59');
		$this->db->order_by("a.id", "desc");
		$this->db->limit(10);
		$recent = $this->db->get()->result();

		// Cash in hand
		$warehouse_id = get_store_warehouse_id();
		$cash_in_hand = $this->dashboard->get_cash_in_hand($warehouse_id);

		// Low stock count
		$low_stock = $this->dashboard->get_low_stock_count($warehouse_id);

		// Unpaid invoices
		$this->db->select("COUNT(*) as count");
		$this->db->where("sales_status", "Final");
		$this->db->where("store_id", $store_id);
		$this->db->where("(grand_total - paid_amount) >", 0);
		$unpaid_count = $this->db->get("db_sales")->row()->count;

		// Top 3 selling products in range
		$this->db->select("a.item_id, MAX(b.item_name) as item_name, COALESCE(SUM(a.sales_qty),0) as qty");
		$this->db->from("db_salesitems a");
		$this->db->join("db_sales s", "s.id = a.sales_id", "left");
		$this->db->join("db_items b", "b.id = a.item_id", "left");
		$this->db->where("s.sales_status", "Final");
		$this->db->where("s.store_id", $store_id);
		$this->db->where("s.sales_date >=", $from);
		$this->db->where("s.sales_date <=", $to . ' 23:59:59');
		$this->db->group_by("a.item_id");
		$this->db->order_by("qty", "desc");
		$this->db->limit(3);
		$top_products = $this->db->get()->result();

		// Upcoming due collections (next 7 days)
		$this->db->select("a.id, a.sales_code, a.grand_total, a.paid_amount, a.due_date, c.customer_name, (a.grand_total - a.paid_amount) as due");
		$this->db->from("db_sales a");
		$this->db->join("db_customers c", "c.id = a.customer_id", "left");
		$this->db->where("a.sales_status", "Final");
		$this->db->where("a.store_id", $store_id);
		$this->db->where("(a.grand_total - a.paid_amount) >", 0);
		$this->db->where("a.due_date >=", $from);
		$this->db->where("a.due_date <=", $seven_days . ' 23:59:59');
		$this->db->order_by("a.due_date", "asc");
		$this->db->limit(5);
		$upcoming_due = $this->db->get()->result();

		// Daily sales target from site settings
		$site = get_site_details();
		$daily_target = (float)($site->sales_target ?? 50000);

		// Compute target based on selected date range
		$days_diff = (strtotime($to) - strtotime($from)) / 86400 + 1;
		if($days_diff <= 1){
			$target_label = 'Daily Target';
			$target = $daily_target;
		} else if($days_diff <= 7){
			$target_label = 'Weekly Target';
			$target = $daily_target * 7;
		} else if($days_diff <= 30){
			$target_label = 'Monthly Target';
			$target = $daily_target * 30;
		} else {
			$target_label = 'Custom Target';
			$target = $daily_target * $days_diff;
		}
		$raw_target_progress = ($target > 0) ? round(($sales / $target) * 100, 1) : 0;
		$target_progress = min(100, $raw_target_progress);
		if($raw_target_progress > 100){
			$target_status = 'surpass';
		} else if($raw_target_progress >= 100){
			$target_status = 'meet';
		} else {
			$target_status = 'behind';
		}

		// Greeting based on hour
		$hour = date('G');
		if($hour < 12) $greeting = 'Good morning';
		elseif($hour < 17) $greeting = 'Good afternoon';
		else $greeting = 'Good evening';

		// User profile picture
		$user_id = $this->session->userdata('inv_userid');
		$user = $this->db->select('profile_picture')->where('id', $user_id)->get('db_users')->row();
		$data['profile_picture'] = ($user && !empty($user->profile_picture)) ? $user->profile_picture : '';

		$data['sales'] = $this->currency($sales, true);
		$data['profit'] = $this->currency($profit, true);
		$data['expenses'] = $this->currency($expenses, true);
		$data['transactions'] = number_format($transactions);
		$data['debt'] = $this->currency($debt->total_debt, true);
		$data['recent_sales'] = is_array($recent) ? $recent : [];
		$data['cash_in_hand'] = $this->currency($cash_in_hand, true);
		$data['low_stock'] = $low_stock;
		$data['unpaid_count'] = $unpaid_count;
		$data['top_products'] = is_array($top_products) ? $top_products : [];
		$data['upcoming_due'] = is_array($upcoming_due) ? $upcoming_due : [];
		$data['daily_target'] = $daily_target;
		$data['target'] = $target;
		$data['target_label'] = $target_label;
		$data['target_progress'] = $target_progress;
		$data['target_status'] = $target_status;

		// Intelligence insights
		$insights = [];
		$days = (int) max(1, $days_diff);

		// Target vs actual
		if($target > 0){
			$target_pct = round(($sales / $target) * 100, 1);
			if($target_pct >= 100){
				$insights[] = 'Great job! Sales hit ' . number_format($target_pct) . '% of your ' . strtolower($target_label) . '.';
			} elseif($target_pct >= 75){
				$insights[] = 'On track: you are at ' . number_format($target_pct) . '% of your ' . strtolower($target_label) . '.';
			} else {
				$insights[] = 'Below target: sales reached ' . number_format($target_pct) . '% of your ' . strtolower($target_label) . '. Push for more sales.';
			}
		}

		// Period vs previous period
		$prev_from = date('Y-m-d', strtotime($from . ' -' . $days . ' days'));
		$prev_to = date('Y-m-d', strtotime($from . ' -1 day'));
		$this->db->select("COALESCE(SUM(grand_total),0) as total");
		$this->db->where("sales_status", "Final");
		$this->db->where("store_id", $store_id);
		$this->db->where("sales_date >=", $prev_from);
		$this->db->where("sales_date <=", $prev_to);
		$prev_sales = (float) $this->db->get("db_sales")->row()->total;
		if($prev_sales > 0){
			$change = round((($sales - $prev_sales) / $prev_sales) * 100, 1);
			$dir = $change >= 0 ? 'up' : 'down';
			$insights[] = 'Sales vs previous ' . $days . ' days: ' . $dir . ' ' . number_format(abs($change)) . '%.';
		}

		// Top product
		if(!empty($top_products)){
			$top = $top_products[0];
			$insights[] = 'Top seller: ' . $top->item_name . ' (' . $top->qty . ' sold).';
		}

		// Debt and cash
		if($debt->total_debt > 0){
			$insights[] = 'Outstanding dues: ' . store_number_format($debt->total_debt) . ' from ' . $debt->debtor_count . ' customer' . ($debt->debtor_count > 1 ? 's' : '') . '.';
		}
		if($cash_in_hand > 0){
			$insights[] = 'Cash in hand: ' . store_number_format($cash_in_hand) . '.';
		}

		// Expenses warning
		if($sales > 0 && $expenses > ($sales * 0.3)){
			$insights[] = 'Expenses are over 30% of sales this period. Review spending.';
		}

		// Low stock
		if($low_stock > 0){
			$insights[] = $low_stock . ' item' . ($low_stock > 1 ? 's' : '') . ' below reorder level. Restock soon.';
		}

		// Staff status (must be before insights)
		$this->db->select("COUNT(*) as total");
		$this->db->where("store_id", $store_id);
		$this->db->where("status", 1);
		$staff_count = $this->db->get("db_users")->row()->total ?? 0;
		$data['staff_count'] = $staff_count;

		// Staff
		if($staff_count > 0){
			$insights[] = $data['attendance_count'] . ' of ' . $staff_count . ' staff on duty today.';
		}

		$data['insights'] = $insights;

		// Today's attendance and clock-in status
		$this->load->model('attendance_model');
		$today = date('Y-m-d');
		$attendance = $this->attendance_model->getTodayAttendance($store_id, $today);
		$data['attendance_count'] = count(is_array($attendance) ? $attendance : []);
		$data['needs_clock_out'] = $this->attendance_model->needsClockOut($user_id, $today);

		$data['greeting'] = $greeting;
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['branch_name'] = get_store_name() . ' · Today';

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/dashboard', $data);
	}

	public function pos()
	{
		$this->permission_check('pos');
		if(!$this->is_cashier_clocked_in()){
			$this->session->set_flashdata('error', 'Please clock in before making a sale.');
			redirect('mobile/clock');
		}
		$data = $this->data;
		$data['page_title'] = 'POS';
		$data['active'] = 'pos';
		$data['init_code'] = get_only_init_code('sales');
		$data['count_id'] = get_last_count_id('db_sales');
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['branch_name'] = get_store_name() . ' · Today';
		$store_id = get_current_store_id();
		$data['payment_modes'] = $this->db->select('code, name, is_default')->where('store_id', $store_id)->where('status', 1)->order_by('sort_order', 'asc')->get('db_payment_modes')->result();
		$data['till_account_id'] = get_cash_account_id();
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/sale', $data);
	}

	public function sale($hold_id = 0)
	{
		$this->permission_check('sales_add');
		if(!$this->is_cashier_clocked_in()){
			$this->session->set_flashdata('error', 'Please clock in before making a sale.');
			redirect('mobile/clock');
		}
		$data = $this->data;
		$data['page_title'] = 'Quick Sale';
		$data['active'] = 'sale';
		$data['init_code'] = get_only_init_code('sales');
		$data['count_id'] = get_last_count_id('db_sales');
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['branch_name'] = get_store_name() . ' · Today';
		$store_id = get_current_store_id();
		$data['payment_modes'] = $this->db->select('code, name, is_default')->where('store_id', $store_id)->where('status', 1)->order_by('sort_order', 'asc')->get('db_payment_modes')->result();
		$data['till_account_id'] = get_cash_account_id();

		// Pre-load a held sale when recalling
		$hold_id = (int) ($hold_id ?: ($this->input->get('hold_id', TRUE) ?: 0));
		$data['hold'] = null;
		if($hold_id > 0){
			$this->db->where('id', $hold_id)->where('store_id', $store_id);
			$hold = $this->db->get('db_hold')->row();
			if($hold){
				$this->db->select('hi.*, i.item_name, i.purchase_price, t.tax, t.tax_name');
				$this->db->from('db_holditems hi');
				$this->db->join('db_items i', 'i.id = hi.item_id', 'left');
				$this->db->join('db_tax t', 't.id = hi.tax_id', 'left');
				$this->db->where('hi.hold_id', $hold_id);
				$hold->items = $this->db->get()->result();

				$customer = $this->db->where('id', $hold->customer_id)->get('db_customers')->row();
				$hold->customer_name = $customer ? $customer->customer_name : '';

				$hold_data = [
					'id' => $hold->id,
					'customer_id' => $hold->customer_id,
					'customer_name' => $hold->customer_name,
					'discount' => $hold->discount_to_all_input ?? 0,
					'sales_note' => $hold->sales_note ?? '',
					'price_type' => !empty($hold->items) && isset($hold->items[0]->price_type) ? $hold->items[0]->price_type : 'wholesale',
					'items' => []
				];
				foreach($hold->items as $it){
					$hold_data['items'][] = [
						'id' => (int) $it->item_id,
						'name' => (string) ($it->item_name ?? 'Item #'.$it->item_id),
						'price' => (float) $it->price_per_unit,
						'purchase_price' => (float) $it->purchase_price,
						'tax_value' => (float) $it->tax,
						'tax_id' => (int) $it->tax_id,
						'tax_name' => (string) ($it->tax_name ?? ''),
						'tax_type' => (string) $it->tax_type,
						'qty' => (float) $it->sales_qty,
					];
				}
				$data['hold'] = $hold_data;

				// A recalled hold should not remain in the hold list
				$this->load->model('pos_model');
				$this->pos_model->hold_invoice_delete($hold_id);
			}
		}

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/sale', $data);
	}

	public function due()
	{
		$data = $this->data;
		$data['page_title'] = 'Due Payments';
		$store_id = get_current_store_id();

		$this->db->select("a.id, a.sales_code, a.grand_total, a.paid_amount, a.due_date, a.created_date, c.customer_name, c.mobile, (a.grand_total - a.paid_amount) as due");
		$this->db->from("db_sales a");
		$this->db->join("db_customers c", "c.id = a.customer_id", "left");
		$this->db->where("a.sales_status", "Final");
		$this->db->where("a.store_id", $store_id);
		$this->db->where("(a.grand_total - a.paid_amount) >", 0);
		$this->db->order_by("a.due_date", "asc");
		$data['due_list'] = $this->db->get()->result();

		$data['total_due'] = array_sum(array_column($data['due_list'], 'due'));
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/due', $data);
	}

	public function pay_due($sales_id)
	{
		$this->permission_check('sales_view');
		$data = $this->data;
		$data['page_title'] = 'Receive Payment';
		$store_id = get_current_store_id();

		$this->db->where('id', $sales_id);
		$this->db->where('store_id', $store_id);
		$this->db->where('sales_status', 'Final');
		$data['sale'] = $this->db->get('db_sales')->row();
		if(empty($data['sale'])){
			redirect('mobile/due');
			return;
		}
		$data['due'] = $data['sale']->grand_total - $data['sale']->paid_amount;
		if($data['due'] <= 0){
			redirect('mobile/due');
			return;
		}
		$data['customer'] = $this->db->where('id', $data['sale']->customer_id)->get('db_customers')->row();
		$this->db->where('store_id', $store_id);
		$this->db->where('status', 1);
		$this->db->order_by('name', 'asc');
		$data['payment_modes'] = $this->db->get('db_payment_modes')->result();
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/pay_due', $data);
	}

	public function save_due_payment()
	{
		$this->permission_check('sales_add');
		$this->load->model('sales_model', 'sales');
		$result = $this->sales->save_payment();
		if($result == 'success'){
			$this->session->set_flashdata('success', 'Payment recorded successfully.');
		} else {
			$this->session->set_flashdata('error', $result ?: 'Payment could not be saved.');
		}
		redirect('mobile/due');
	}

	public function sales_list()
	{
		$this->permission_check('sales_view');
		$data = $this->data;
		$data['page_title'] = 'Sales List';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$store_id = get_current_store_id();

		$this->db->select('a.id, a.sales_code, a.sales_date, a.reference_no, a.grand_total, a.paid_amount, a.payment_status, a.sales_status, a.customer_id, c.customer_name');
		$this->db->from('db_sales a');
		$this->db->join('db_customers c', 'c.id = a.customer_id', 'left');
		$this->db->where('a.store_id', $store_id);
		$this->db->where('a.sales_status', 'Final');
		if(!$this->permissions('show_all_users_sales_invoices')){
			$this->db->where("upper(a.created_by)", strtoupper($this->session->userdata('inv_username')));
		}
		$this->db->order_by('a.id', 'desc');
		$this->db->limit(200);
		$data['records'] = $this->db->get()->result();

		$data['customers'] = get_customers_select_list('', $store_id);
		$statuses = [];
		foreach($data['records'] as $r){
			if(!empty($r->payment_status) && !in_array($r->payment_status, $statuses)){
				$statuses[] = $r->payment_status;
			}
		}
		$data['statuses'] = $statuses;

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/sales_list', $data);
	}

	public function staff()
	{
		$data = $this->data;
		$data['page_title'] = 'Staff Attendance';
		$store_id = get_current_store_id();

		$this->db->select("id, username, first_name, last_name, role_name, status, updated_at");
		$this->db->where("store_id", $store_id);
		$this->db->order_by("first_name", "asc");
		$data['staff_list'] = $this->db->get("db_users")->result();

		$this->load->model('attendance_model');
		$selected_date = date('Y-m-d');
		$attendance = $this->attendance_model->getTodayAttendance($store_id, $selected_date);
		$attendance = is_array($attendance) ? $attendance : [];
		$clocked_in = [];
		$on_time = 0;
		$late = 0;
		$on_duty = 0;
		$hours_total = 0;
		$hours_count = 0;
		foreach($attendance as $a){
			if(!empty($a->user_id)) $clocked_in[] = $a->user_id;
			if(!empty($a->clock_in) && !empty($a->start_time)){
				$ci = strtotime($a->clock_in);
				$st = strtotime($a->start_time);
				if($ci !== false && $st !== false && $ci > $st){
					$late++;
				} else {
					$on_time++;
				}
			} elseif(!empty($a->clock_in)) {
				$on_time++;
			}
			if(!empty($a->clock_in) && (empty($a->clock_out) || $a->clock_out === '00:00:00')){
				$on_duty++;
			}
			if(!empty($a->clock_in) && !empty($a->clock_out) && $a->clock_out !== '00:00:00'){
				$diff = strtotime($a->clock_out) - strtotime($a->clock_in);
				if($diff > 0){
					$hours_total += ($diff / 3600);
					$hours_count++;
				}
			}
		}
		$data['clocked_in'] = $clocked_in;
		$data['attendance_summary'] = [
			'on_time'     => $on_time,
			'late'        => $late,
			'on_duty'     => $on_duty,
			'avg_hours'   => $hours_count > 0 ? round($hours_total / $hours_count, 1) : 0,
			'total_staff' => count($data['staff_list'])
		];

		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/staff', $data);
	}

	public function customers()
	{
		$this->permission_check('customers_view');
		$data = $this->data;
		$data['page_title'] = 'Customers';
		$store_id = get_current_store_id();

		$this->db->select("id, customer_name, mobile, customer_code, email, location_link, credit_limit, opening_balance, sales_due, sales_return_due, tot_advance, loyalty_points, loyalty_tier, store_credit_balance, gift_card_balance, status, store_id");
		$this->db->where("store_id", $store_id);
		$this->db->where("status", 1);
		$this->db->where("delete_bit !=", 1);
		$this->db->order_by("customer_name", "asc");
		$data['customers'] = $this->db->get("db_customers")->result();

		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/customers', $data);
	}

	public function add_customer()
	{
		$this->permission_check('customers_add');
		$data = $this->data;
		$data['page_title'] = 'Add Customer';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$this->load->view('mobile/customer_form', $data);
	}

	public function edit_customer($id)
	{
		$this->belong_to('db_customers', $id);
		$this->permission_check('customers_edit');
		$data = $this->data;
		$data['page_title'] = 'Edit Customer';
		$this->load->model('customers_model', 'customers_m');
		$result = $this->customers_m->get_details($id, $data);
		$data = array_merge($data, $result);

		$customer = $this->db->where('id', $id)->get('db_customers')->row();
		$data['nin_bvn'] = $customer->nin_bvn ?? '';
		$data['nin_verified'] = $customer->nin_verified ?? 0;
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$this->load->view('mobile/customer_form', $data);
	}

	public function save_customer()
	{
		$q_id = $this->input->post('q_id', TRUE);
		$is_update = !empty($q_id);
		$this->permission_check($is_update ? 'customers_edit' : 'customers_add');
		$this->load->library('form_validation');
		$this->load->model('customers_model', 'customers_m');

		$this->form_validation->set_rules('customer_name', 'Customer Name', 'trim|required');
		$this->form_validation->set_rules('mobile', 'Mobile', 'trim|required');

		if($this->form_validation->run() == TRUE){
			if($is_update){
				$result = $this->customers_m->update_customers();
				if($result === 'success'){
					$nin_bvn = $this->input->post('nin_bvn', TRUE);
					$nin_verified = $this->input->post('nin_verified', TRUE);
					$this->db->where('id', $q_id)->update('db_customers', array(
						'nin_bvn' => $nin_bvn,
						'nin_verified' => (!empty($nin_verified)) ? 1 : 0
					));
					$this->session->set_flashdata('success', 'Customer Updated Successfully!');
					redirect('mobile/customer_profile/' . $q_id);
				}
				$this->session->set_flashdata('failed', $result);
				redirect('mobile/edit_customer/' . $q_id);
			} else {
				$result = $this->customers_m->verify_and_save();
				if($result === 'success'){
					$customer_id = $this->db->insert_id();
					$nin_bvn = $this->input->post('nin_bvn', TRUE);
					$nin_verified = $this->input->post('nin_verified', TRUE);
					$this->db->where('id', $customer_id)->update('db_customers', array(
						'nin_bvn' => $nin_bvn,
						'nin_verified' => (!empty($nin_verified)) ? 1 : 0
					));
					$this->session->set_flashdata('success', 'New Customer Added Successfully!');
					redirect('mobile/customers');
				}
				$this->session->set_flashdata('failed', $result);
				redirect('mobile/add_customer');
			}
		}
		if($is_update){
			$this->session->set_flashdata('failed', validation_errors());
			redirect('mobile/edit_customer/' . $q_id);
		}
		$this->session->set_flashdata('failed', validation_errors());
		redirect('mobile/add_customer');
	}

	public function delete_customer($id)
	{
		$this->permission_check('customers_delete');
		if($id == 1){
			$this->session->set_flashdata('failed', "Sorry! This Record Restricted! Can't Delete");
			redirect('mobile/customers');
		}
		if(set_status_of_table($id, 0, 'db_customers')){
			$this->session->set_flashdata('success', 'Customer removed successfully!');
		} else {
			$this->session->set_flashdata('failed', 'Failed to remove customer.');
		}
		redirect('mobile/customers');
	}

	public function customer_profile($id)
	{
		$this->belong_to('db_customers', $id);
		$this->permission_check('customers_view');
		$data = $this->data;
		$data['page_title'] = 'Customer Profile';
		$store_id = get_current_store_id();

		$customer = $this->db->where('id', $id)->get('db_customers')->row();
		if(!$customer) redirect('mobile/customers');
		$data['customer'] = $customer;

		$opening_balance = $customer->opening_balance ?? 0;
		$opening_balance -= get_paid_cob($id);
		$sales_due = $customer->sales_due ?? 0;
		$sales_return_due = $customer->sales_return_due ?? 0;
		$data['total_due'] = $opening_balance + $sales_due - $sales_return_due;

		$data['purchases'] = $this->db->where('customer_id', $id)
								  ->where('store_id', $store_id)
								  ->order_by('id', 'desc')
								  ->limit(20)
								  ->get('db_sales')
								  ->result();

		$data['payments'] = $this->db->where('customer_id', $id)
								 ->order_by('id', 'desc')
								 ->limit(20)
								 ->get('db_customer_payments')
								 ->result();

		if($this->db->table_exists('db_gift_cards')){
			$data['gift_cards'] = $this->db->where('customer_id', $id)
								  ->where('store_id', $store_id)
								  ->order_by('id', 'desc')
								  ->get('db_gift_cards')
								  ->result();
		} else { $data['gift_cards'] = array(); }

		if($this->db->table_exists('db_store_credit')){
			$data['store_credits'] = $this->db->where('customer_id', $id)
								     ->where('store_id', $store_id)
								     ->order_by('id', 'desc')
								     ->get('db_store_credit')
								     ->result();
		} else { $data['store_credits'] = array(); }

		if($this->db->table_exists('db_customer_coupons')){
			$data['coupons'] = $this->db->where('customer_id', $id)
								  ->where('store_id', $store_id)
								  ->order_by('id', 'desc')
								  ->get('db_customer_coupons')
								  ->result();
		} else { $data['coupons'] = array(); }

		if($this->db->table_exists('db_customer_memberships')){
			$this->load->model('membership_model','membership');
			$data['memberships'] = $this->membership->get_customer_memberships($id);
			$data['active_membership'] = $this->membership->get_customer_discount($id);
		} else {
			$data['memberships'] = array();
			$data['active_membership'] = null;
		}

		if(mp_feature_enabled('treatment_notes') && $this->db->table_exists('db_treatment_notes')){
			$this->load->model('treatment_notes_model','notes');
			$data['treatment_notes'] = $this->notes->get_by_customer($id);
		} else { $data['treatment_notes'] = array(); }

		if(mp_feature_enabled('medical_notes') && $this->db->table_exists('db_medical_notes')){
			$this->load->model('medical_notes_model','medical');
			$data['medical_notes'] = $this->medical->get_by_customer($id);
			$data['medical_allergies'] = $this->medical->get_allergies($id);
			$data['medical_enabled'] = true;
		} else {
			$data['medical_notes'] = array();
			$data['medical_allergies'] = array();
			$data['medical_enabled'] = false;
		}

		if($this->db->table_exists('db_custom_orders')){
			$this->load->model('custom_orders_model','custom_orders');
			$data['custom_orders'] = $this->custom_orders->get_by_customer($id);
		} else { $data['custom_orders'] = array(); }

		if(mp_feature_enabled('laundry_workflow') && $this->db->table_exists('db_laundry_orders')){
			$data['service_history'] = $this->db->query(
				"SELECT lo.id, lo.sales_id, lo.status, lo.tag_number, lo.created_at, lo.updated_at,
						s.sales_code, s.sales_date, s.grand_total, s.paid_amount,
						(SELECT GROUP_CONCAT(DISTINCT i.item_name SEPARATOR ', ')
						 FROM db_salesitems si
						 JOIN db_items i ON i.id = si.item_id
						 WHERE si.sales_id = lo.sales_id) as items_list
				 FROM db_laundry_orders lo
				 JOIN db_sales s ON s.id = lo.sales_id
				 WHERE s.customer_id = ? AND lo.store_id = ?
				 ORDER BY lo.created_at DESC",
				[$id, $store_id]
			)->result();
		} else { $data['service_history'] = array(); }

		if($this->db->table_exists('db_installment_plans')){
			$this->load->model('installments_model','installments');
			$data['payplans'] = $this->installments->get_customer_active_plans($id);
		} else { $data['payplans'] = array(); }

		$this->load->model('customers_model','customers');
		$statement = $this->customers->get_statement($id, $store_id);
		$data['opening'] = $statement['opening'];
		$data['statement'] = $statement['rows'];
		$data['statement_summary'] = $statement['summary'];

		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/customer_profile', $data);
	}

	public function save_customer_notes($customer_id)
	{
		$this->belong_to('db_customers', $customer_id);
		$this->permission_check('customers_edit');
		$notes = $this->input->post('notes', TRUE);
		$this->db->where('id', $customer_id)->update('db_customers', array('notes' => $notes));
		$this->session->set_flashdata('success', 'Customer notes updated.');
		redirect('mobile/customer_profile/' . $customer_id . '?tab=notes');
	}

	public function save_treatment_note($customer_id)
	{
		$this->belong_to('db_customers', $customer_id);
		$this->permission_check('customers_edit');
		$this->load->model('treatment_notes_model', 'treatment');
		$data = array(
			'customer_id' => $customer_id,
			'treatment_date' => $this->input->post('treatment_date', TRUE),
			'service_type' => $this->input->post('service_type', TRUE),
			'staff_name' => $this->input->post('staff_name', TRUE),
			'notes' => $this->input->post('notes', TRUE),
			'products_used' => $this->input->post('products_used', TRUE),
			'store_id' => get_current_store_id(),
			'created_at' => date('Y-m-d H:i:s')
		);
		$this->treatment->save($data);
		$this->session->set_flashdata('success', 'Treatment note added.');
		redirect('mobile/customer_profile/' . $customer_id . '?tab=treatment');
	}

	public function save_medical_note($customer_id)
	{
		$this->belong_to('db_customers', $customer_id);
		$this->permission_check('customers_edit');
		$this->load->model('medical_notes_model', 'medical');
		$data = array(
			'customer_id' => $customer_id,
			'note_date' => $this->input->post('note_date', TRUE),
			'prescribing_doctor' => $this->input->post('prescribing_doctor', TRUE),
			'diagnosis' => $this->input->post('diagnosis', TRUE),
			'allergies_flagged' => $this->input->post('allergies_flagged', TRUE),
			'notes' => $this->input->post('notes', TRUE),
			'refills_remaining' => (int)$this->input->post('refills_remaining', TRUE),
			'next_refill_date' => $this->input->post('next_refill_date', TRUE),
			'store_id' => get_current_store_id(),
			'created_at' => date('Y-m-d H:i:s')
		);
		$this->medical->save($data);
		$this->session->set_flashdata('success', 'Medical note added.');
		redirect('mobile/customer_profile/' . $customer_id . '?tab=medical');
	}

	public function save_custom_order($customer_id)
	{
		$this->belong_to('db_customers', $customer_id);
		$this->permission_check('customers_edit');
		$this->load->model('custom_orders_model', 'orders');
		$data = array(
			'customer_id' => $customer_id,
			'item_name' => $this->input->post('item_name', TRUE),
			'status' => $this->input->post('status', TRUE) ?: 'pending',
			'due_date' => $this->input->post('due_date', TRUE),
			'total_amount' => (float)$this->input->post('total_amount', TRUE),
			'deposit_amount' => (float)$this->input->post('deposit_amount', TRUE),
			'deposit_paid' => (float)$this->input->post('deposit_paid', TRUE),
			'store_id' => get_current_store_id(),
			'created_at' => date('Y-m-d H:i:s')
		);
		$this->orders->save($data);
		$this->session->set_flashdata('success', 'Custom order added.');
		redirect('mobile/customer_profile/' . $customer_id . '?tab=custom');
	}

	public function statement($customer_id)
	{
		$this->belong_to('db_customers', $customer_id);
		$this->permission_check('customers_view');
		$data = $this->data;
		$data['page_title'] = 'Customer Statement';
		$this->load->model('customers_model', 'customers');
		$data['customer'] = $this->db->where('id', $customer_id)->get('db_customers')->row();
		$statement = $this->customers->get_statement($customer_id);
		$data = array_merge($data, $statement);
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/statement', $data);
	}

	public function suppliers()
	{
		$this->permission_check('suppliers_view');
		$data = $this->data;
		$data['page_title'] = 'Suppliers';
		$store_id = get_current_store_id();

		$data['suppliers'] = $this->db->select('id, supplier_name, supplier_code, mobile, email, opening_balance, purchase_due, purchase_return_due, status')
								  ->where('store_id', $store_id)
								  ->where('status', 1)
								  ->order_by('supplier_name', 'asc')
								  ->get('db_suppliers')
								  ->result();
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$this->load->view('mobile/suppliers', $data);
	}

	public function add_supplier()
	{
		$this->permission_check('suppliers_add');
		$data = $this->data;
		$data['page_title'] = 'Add Supplier';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$this->load->view('mobile/supplier_form', $data);
	}

	public function edit_supplier($id)
	{
		$this->belong_to('db_suppliers', $id);
		$this->permission_check('suppliers_edit');
		$data = $this->data;
		$data['page_title'] = 'Edit Supplier';
		$this->load->model('suppliers_model', 'suppliers_m');
		$result = $this->suppliers_m->get_details($id, $data);
		$data = array_merge($data, $result);
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$this->load->view('mobile/supplier_form', $data);
	}

	public function save_supplier()
	{
		$q_id = $this->input->post('q_id', TRUE);
		$is_update = !empty($q_id);
		$this->permission_check($is_update ? 'suppliers_edit' : 'suppliers_add');
		$this->load->library('form_validation');
		$this->load->model('suppliers_model', 'suppliers_m');

		$this->form_validation->set_rules('supplier_name', 'Supplier Name', 'trim|required');

		if($this->form_validation->run() == TRUE){
			if($is_update){
				$result = $this->suppliers_m->update_suppliers();
				if($result === 'success'){
					$this->session->set_flashdata('success', 'Supplier Updated Successfully!');
					redirect('mobile/supplier_profile/' . $q_id);
				}
				$this->session->set_flashdata('failed', $result);
				redirect('mobile/edit_supplier/' . $q_id);
			} else {
				$result = $this->suppliers_m->verify_and_save();
				if($result === 'success'){
					$this->session->set_flashdata('success', 'New Supplier Added Successfully!');
					redirect('mobile/suppliers');
				}
				$this->session->set_flashdata('failed', $result);
				redirect('mobile/add_supplier');
			}
		}
		if($is_update){
			$this->session->set_flashdata('failed', validation_errors());
			redirect('mobile/edit_supplier/' . $q_id);
		}
		$this->session->set_flashdata('failed', validation_errors());
		redirect('mobile/add_supplier');
	}

	public function delete_supplier($id)
	{
		$this->permission_check('suppliers_delete');
		if(set_status_of_table($id, 0, 'db_suppliers')){
			$this->session->set_flashdata('success', 'Supplier removed successfully!');
		} else {
			$this->session->set_flashdata('failed', 'Failed to remove supplier.');
		}
		redirect('mobile/suppliers');
	}

	public function supplier_profile($id)
	{
		$this->belong_to('db_suppliers', $id);
		$this->permission_check('suppliers_view');
		$data = $this->data;
		$data['page_title'] = 'Supplier Profile';
		$store_id = get_current_store_id();

		$supplier = $this->db->where('id', $id)->get('db_suppliers')->row();
		if(!$supplier) redirect('mobile/suppliers');
		$data['supplier'] = $supplier;

		$opening_balance = $supplier->opening_balance ?? 0;
		$opening_balance -= get_paid_sob($id);
		$purchase_due = $supplier->purchase_due ?? 0;
		$purchase_return_due = $supplier->purchase_return_due ?? 0;
		$data['total_due'] = $opening_balance + $purchase_due - $purchase_return_due;

		$data['purchases'] = $this->db->where('supplier_id', $id)
								  ->where('store_id', $store_id)
								  ->order_by('id', 'desc')
								  ->limit(20)
								  ->get('db_purchase')
								  ->result();

		$data['payments'] = $this->db->where('supplier_id', $id)
								 ->order_by('id', 'desc')
								 ->limit(20)
								 ->get('db_supplier_payments')
								 ->result();

		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/supplier_profile', $data);
	}

	public function supplier_payment($id)
	{
		$this->belong_to('db_suppliers', $id);
		$this->permission_check('purchase_payment_add');
		$data = $this->data;
		$data['page_title'] = 'Supplier Payment';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$store_id = get_current_store_id();

		$supplier = $this->db->where('id', $id)->where('store_id', $store_id)->get('db_suppliers')->row();
		if(!$supplier){ show_404(); exit; }
		$data['supplier'] = $supplier;

		$opening_balance = ($supplier->opening_balance ?? 0) - get_paid_sob($id);
		$data['total_due'] = $opening_balance + ($supplier->purchase_due ?? 0) - ($supplier->purchase_return_due ?? 0);

		$data['payment_modes'] = $this->db->where('store_id', $store_id)->where('status', 1)->order_by('name')->get('db_payment_modes')->result();
		$data['accounts'] = $this->db->where('store_id', $store_id)->where('delete_bit', 0)->order_by('account_name')->get('ac_accounts')->result();

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/supplier_payment', $data);
	}

	public function clock()
	{
		$userId = (int)$this->session->userdata('inv_userid');
		$storeId = get_current_store_id();
		$date = date('Y-m-d');

		$this->load->model('attendance_model');
		$shift = $this->attendance_model->isOnDuty($userId, $storeId);
		if(!$shift){
			$shifts = $this->attendance_model->getShiftsByUser($userId, $storeId);
			$shift = $shifts[0] ?? null;
			if(!$shift){
				$this->session->set_flashdata('failed', 'You are not scheduled for any shift right now.');
			}
		}

		$needsClockOut = $this->attendance_model->needsClockOut($userId, $date);

		$data = $this->data;
		$data['shift'] = $shift;
		$data['needs_clock_out'] = $needsClockOut;
		$data['clock_action'] = $needsClockOut ? 'out' : 'in';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['page_title'] = $needsClockOut ? 'Clock Out' : 'Clock In';
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/clock', $data);
	}

	public function customer_search()
	{
		$term = $this->input->get('q', TRUE);
		$store_id = get_current_store_id();
		$this->db->select('id, customer_name, mobile, customer_code');
		$this->db->from('db_customers');
		$this->db->where('store_id', $store_id);
		$this->db->where('status', 1);
		if(!empty($term)){
			$this->db->group_start();
			$this->db->like('customer_name', $term);
			$this->db->or_like('mobile', $term);
			$this->db->or_like('customer_code', $term);
			$this->db->group_end();
		}
		$this->db->limit(20);
		$rows = $this->db->get()->result();
		echo json_encode($rows);
	}

	public function item_search()
	{
		$term = $this->input->get('q', TRUE);
		$recent = $this->input->get('recent', TRUE);
		$price_type = $this->input->get('price_type', TRUE);
		$category_id = $this->input->get('category', TRUE);
		$brand_id = $this->input->get('brand', TRUE);
		$limit = $this->input->get('limit', TRUE);
		$limit = is_numeric($limit) ? min((int)$limit, 200) : 20;
		$store_id = get_current_store_id();

		if(!empty($price_type) && $price_type == 'wholesale'){
			$this->db->where('a.sales_price >', 0);
		}

		if($recent == 1){
			$this->db->select('a.id, a.item_name, a.item_code, a.stock, COALESCE((SELECT mrp FROM db_item_barcodes bc WHERE bc.item_id = a.id AND bc.status = 1 ORDER BY bc.id ASC LIMIT 1), a.mrp) as mrp_price, COALESCE((SELECT sales_price FROM db_item_barcodes bc WHERE bc.item_id = a.id AND bc.status = 1 ORDER BY bc.id ASC LIMIT 1), a.sales_price) as sales_price, a.purchase_price, a.price as purchase_cost, a.tax_id, a.tax_type, a.item_image, b.tax as tax_value, b.tax_name, COUNT(c.id) as sold_count');
			$this->db->from('db_items a');
		$this->db->where('a.item_group !=', 'Variants');
			$this->db->join('db_tax b', 'b.id = a.tax_id', 'left');
			$this->db->join('db_salesitems c', 'c.item_id = a.id', 'left');
			$this->db->where('a.store_id', $store_id);
			$this->db->where('a.status', 1);
			if(!empty($category_id) && is_numeric($category_id)){
				$this->db->where('a.category_id', $category_id);
			}
			if(!empty($brand_id) && is_numeric($brand_id)){
				$this->db->where('a.brand_id', $brand_id);
			}
			$this->db->group_by('a.id');
			$this->db->order_by('sold_count', 'desc');
			$this->db->limit(10);
		}
		else {
			$this->db->select('a.id, a.item_name, a.item_code, a.stock, COALESCE((SELECT mrp FROM db_item_barcodes bc WHERE bc.item_id = a.id AND bc.status = 1 ORDER BY bc.id ASC LIMIT 1), a.mrp) as mrp_price, COALESCE((SELECT sales_price FROM db_item_barcodes bc WHERE bc.item_id = a.id AND bc.status = 1 ORDER BY bc.id ASC LIMIT 1), a.sales_price) as sales_price, a.purchase_price, a.price as purchase_cost, a.tax_id, a.tax_type, a.item_image, b.tax as tax_value, b.tax_name');
			$this->db->from('db_items a');
		$this->db->where('a.item_group !=', 'Variants');
			$this->db->join('db_tax b', 'b.id = a.tax_id', 'left');
			$this->db->where('a.store_id', $store_id);
			$this->db->where('a.status', 1);
			if(!empty($category_id) && is_numeric($category_id)){
				$this->db->where('a.category_id', $category_id);
			}
			if(!empty($brand_id) && is_numeric($brand_id)){
				$this->db->where('a.brand_id', $brand_id);
			}
			if(!empty($term)){
				$this->db->group_start();
				$this->db->like('a.item_name', $term);
				$this->db->or_like('a.item_code', $term);
				$this->db->or_like('a.sku', $term);
				$this->db->group_end();
			}
			$this->db->limit($limit);
		}
		$rows = $this->db->get()->result();

		// Apply active promotions (with margin protection) if module is available
		$has_promotions = FALSE;
		try { $has_promotions = $this->db->table_exists('db_promotions'); } catch(Exception $e) {}
		if($has_promotions){
			$this->load->model('promotions_model');
			$today = date('Y-m-d');
			foreach($rows as &$row){
				$base_price = ($price_type == 'retail' && !empty($row->mrp_price) && $row->mrp_price > 0) ? $row->mrp_price : $row->sales_price;
				try {
					$promo = $this->promotions_model->compute_effective_price($row->id, $base_price);
					if($promo['has_promo']){
						$row->sales_price = $promo['price'];
						$row->mrp_price = $promo['price'];
						$row->promo_name = $promo['promo_name'];
						$row->promo_discount = $promo['discount_amount'];
						$row->original_price = $base_price;
					}
				} catch(Exception $e) {}
			}
			unset($row);
		}

		header('Content-Type: application/json');
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		echo json_encode($rows);
	}

	public function categories()
	{
		$store_id = get_current_store_id();
		$this->db->select('id, category_name');
		$this->db->where('store_id', $store_id);
		$this->db->where('status', 1);
		$this->db->order_by('category_name', 'asc');
		$rows = $this->db->get('db_category')->result();
		echo json_encode($rows);
	}

	public function brands()
	{
		$store_id = get_current_store_id();
		$this->db->select('id, brand_name');
		$this->db->where('store_id', $store_id);
		$this->db->where('status', 1);
		$this->db->order_by('brand_name', 'asc');
		$rows = $this->db->get('db_brands')->result();
		echo json_encode($rows);
	}

	public function customer_due($customer_id)
	{
		$store_id = get_current_store_id();
		$this->db->select("COALESCE(SUM(grand_total - paid_amount),0) as due");
		$this->db->where('store_id', $store_id);
		$this->db->where('customer_id', $customer_id);
		$this->db->where('sales_status', 'Final');
		$due = $this->db->get('db_sales')->row()->due;
		$customer = $this->db->select('mobile, tot_advance, store_credit_balance, gift_card_balance, loyalty_points, credit_limit')->where('id', $customer_id)->get('db_customers')->row();
		echo json_encode([
			'due' => $due,
			'mobile' => $customer->mobile ?? '',
			'tot_advance' => $customer->tot_advance ?? 0,
			'store_credit_balance' => $customer->store_credit_balance ?? 0,
			'gift_card_balance' => $customer->gift_card_balance ?? 0,
			'loyalty_points' => $customer->loyalty_points ?? 0,
			'credit_limit' => $customer->credit_limit ?? 0,
		]);
	}

	public function hold()
	{
		header('Content-Type: application/json; charset=utf-8');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		ob_start();
		$this->permission_check('sales_add');

		$input = json_decode(file_get_contents('php://input'), true);
		if(empty($input)){
			$input = $this->input->post();
		}

		$csrf_token = $input['csrf_test_name'] ?? '';
		$csrf_cookie = $this->input->cookie('csrf_cookie_name');
		if(empty($csrf_token) || $csrf_token !== $csrf_cookie){
			ob_end_clean();
			echo json_encode(['status' => 'error', 'message' => 'CSRF token validation failed']);
			exit;
		}

		$store_id = get_current_store_id();
		$warehouse_id = (warehouse_module() && warehouse_count()>1) ? $input['warehouse_id'] : get_store_warehouse_id();
		$customer_id = $input['customer_id'] ?? 0;
		$cart = $input['cart'] ?? [];
		$discount = parse_amount($input['discount'] ?? 0);
		$sales_note = $input['sales_note'] ?? '';

		if(empty($cart) || count($cart) === 0){
			ob_end_clean();
			echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
			exit;
		}

		if(empty($warehouse_id)){
			ob_end_clean();
			echo json_encode(['status' => 'error', 'message' => 'No warehouse found for this store. Please set up a warehouse in Settings.']);
			exit;
		}

		$this->load->model('pos_model');

		$subtotal = 0;
		$tax_total = 0;
		$items = [];
		foreach($cart as $item){
			$item_id = (int) ($item['id'] ?? 0);
			$qty = (float) ($item['qty'] ?? 1);
			$price = (float) ($item['price'] ?? 0);
			$tax_value = (float) ($item['tax_value'] ?? 0);
			$tax_type = $item['tax_type'] ?? 'Exclusive';
			$price_type = $item['price_type'] ?? 'wholesale';
			$tax_id = (int) ($item['tax_id'] ?? 0);

			if($item_id <= 0){
				ob_end_clean();
				echo json_encode(['status' => 'error', 'message' => 'Invalid item in cart']);
				exit;
			}

			$line = $qty * $price;
			if($tax_type == 'Exclusive'){
				$tax_amount = ($line * $tax_value) / 100;
				$line_total_with_tax = $line + $tax_amount;
			} else {
				$tax_amount = $line - ($line / (1 + ($tax_value / 100)));
				$line_total_with_tax = $line;
				$line = $line - $tax_amount;
			}
			$subtotal += $line;
			$tax_total += $tax_amount;

			$item_details = get_item_details($item_id);
			if(!$item_details){
				ob_end_clean();
				echo json_encode(['status' => 'error', 'message' => 'Item not found: '.$item_id]);
				exit;
			}
			$service_bit = $item_details->service_bit ?? 0;
			$current_stock = total_available_qty_items_of_warehouse($warehouse_id, null, $item_id);
			if($current_stock < $qty && $service_bit == 0){
				ob_end_clean();
				echo json_encode(['status' => 'error', 'message' => $item_details->item_name.' has only '.$current_stock.' in stock']);
				exit;
			}

			$unit_total_cost = ($tax_type == 'Exclusive') ? ($price + ($tax_value * $price / 100)) : $price;

			$items[] = [
				'store_id' => $store_id,
				'item_id' => $item_id,
				'description' => '',
				'batch_lot' => '',
				'price_type' => $price_type,
				'sales_qty' => $qty,
				'price_per_unit' => $price,
				'tax_id' => $tax_id ? $tax_id : null,
				'tax_amt' => number_format($tax_amount, 2, '.', ''),
				'tax_type' => $tax_type,
				'discount_type' => 'Percentage',
				'discount_input' => 0,
				'discount_amt' => 0,
				'unit_total_cost' => number_format($unit_total_cost, 2, '.', ''),
				'total_cost' => number_format($line_total_with_tax, 2, '.', ''),
				'sold_serial_number' => '',
				'sold_imei_number' => '',
				'barcode_id' => 0,
			];
		}

		$total = $subtotal + $tax_total - $discount;
		$grand_total = round($total, 2);

		$this->db->trans_begin();

		$reference_id = trim($input['reference_id'] ?? '');
		if(empty($reference_id)){
			$reference_id = 'MOB-' . date('YmdHis') . '-' . mt_rand(100, 999);
		}

		$hold_entry = [
			'reference_id' => $reference_id,
			'store_id' => $store_id,
			'sales_date' => date('Y-m-d'),
			'sales_status' => 'Final',
			'customer_id' => $customer_id,
			'discount_to_all_input' => number_format($discount, 2, '.', ''),
			'discount_to_all_type' => 'Fixed',
			'tot_discount_to_all_amt' => number_format($discount, 2, '.', ''),
			'subtotal' => number_format($subtotal, 2, '.', ''),
			'round_off' => number_format($grand_total - ($subtotal + $tax_total), 2, '.', ''),
			'grand_total' => number_format($grand_total, 2, '.', ''),
			'pos' => 1,
			'sales_note' => $sales_note,
			'warehouse_id' => $warehouse_id,
		];

		if(!$this->db->insert('db_hold', $hold_entry)){
			$err = $this->db->error();
			$this->db->trans_rollback();
			log_message('error', 'Mobile hold() db_hold insert failed: ' . ($err['message'] ?? 'unknown'));
			ob_end_clean();
			echo json_encode(['status' => 'error', 'message' => 'Failed to create hold: ' . ($err['message'] ?? 'unknown')]);
			exit;
		}

		$hold_id = $this->db->insert_id();

		foreach($items as $it){
			$it['hold_id'] = $hold_id;
			if(!$this->db->insert('db_holditems', $it)){
				$err = $this->db->error();
				$this->db->trans_rollback();
				log_message('error', 'Mobile hold() db_holditems insert failed: ' . ($err['message'] ?? 'unknown'));
				ob_end_clean();
				echo json_encode(['status' => 'error', 'message' => 'Failed to save hold items: ' . ($err['message'] ?? 'unknown')]);
				exit;
			}
			$this->pos_model->update_items_quantity($it['item_id']);
		}

		$this->db->trans_commit();

		ob_end_clean();
		echo json_encode(['status' => 'success', 'message' => 'Sale held.', 'redirect' => base_url('mobile')]);
		exit;
	}

	public function get_hold($hold_id = 0)
	{
		header('Content-Type: application/json; charset=utf-8');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		$this->permission_check('sales_add');
		$hold_id = (int) ($hold_id ?: ($this->input->get('hold_id', TRUE) ?: 0));
		if($hold_id <= 0){
			ob_end_clean();
			echo json_encode(['status' => 'error', 'message' => 'Invalid hold ID']);
			exit;
		}
		$store_id = get_current_store_id();
		$this->db->where('id', $hold_id)->where('store_id', $store_id);
		$hold = $this->db->get('db_hold')->row();
		if(!$hold){
			ob_end_clean();
			echo json_encode(['status' => 'error', 'message' => 'Hold not found']);
			exit;
		}
		$this->db->select('hi.*, i.item_name, i.purchase_price, t.tax, t.tax_name');
		$this->db->from('db_holditems hi');
		$this->db->join('db_items i', 'i.id = hi.item_id', 'left');
		$this->db->join('db_tax t', 't.id = hi.tax_id', 'left');
		$this->db->where('hi.hold_id', $hold_id);
		$hold->items = $this->db->get()->result();
		$customer = $this->db->where('id', $hold->customer_id)->get('db_customers')->row();
		$hold->customer_name = $customer ? $customer->customer_name : '';
		$hold_data = [
			'id' => $hold->id,
			'customer_id' => $hold->customer_id,
			'customer_name' => $hold->customer_name,
			'discount' => $hold->discount_to_all_input ?? 0,
			'sales_note' => $hold->sales_note ?? '',
			'price_type' => !empty($hold->items) && isset($hold->items[0]->price_type) ? $hold->items[0]->price_type : 'wholesale',
			'items' => []
		];
		foreach($hold->items as $it){
			$hold_data['items'][] = [
				'id' => (int) $it->item_id,
				'name' => (string) ($it->item_name ?? 'Item #'.$it->item_id),
				'price' => (float) $it->price_per_unit,
				'purchase_price' => (float) $it->purchase_price,
				'tax_value' => (float) $it->tax,
				'tax_id' => (int) $it->tax_id,
				'tax_name' => (string) ($it->tax_name ?? ''),
				'tax_type' => (string) $it->tax_type,
				'qty' => (float) $it->sales_qty,
			];
		}
		ob_end_clean();
		echo json_encode($hold_data, JSON_INVALID_UTF8_SUBSTITUTE);
		exit;
	}

	public function save()
	{
		header('Content-Type: application/json; charset=utf-8');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		ob_start();
		// For JSON requests, manually verify CSRF token before permission check
		$input = json_decode(file_get_contents('php://input'), true);
		if(!empty($input) && isset($input['csrf_test_name'])){
			// Verify CSRF token manually for JSON requests
			$csrf_token = $input['csrf_test_name'];
			$csrf_cookie = $this->input->cookie('csrf_cookie_name');
			if(empty($csrf_token) || $csrf_token !== $csrf_cookie){
				ob_end_clean();
				echo json_encode(['status' => 'error', 'message' => 'CSRF token validation failed']);
				exit;
			}
		}
		
		$this->permission_check('sales_add');
		if(!$this->is_cashier_clocked_in()){
			ob_end_clean();
			echo json_encode(['status' => 'error', 'message' => 'Please clock in before making a sale.']);
			exit;
		}
		if(empty($input)){
			$input = $this->input->post();
		}

		$store_id = get_current_store_id();
		$warehouse_id = (warehouse_module() && warehouse_count()>1) ? $input['warehouse_id'] : get_store_warehouse_id();
		$init_code = get_only_init_code('sales');
		$count_id = get_last_count_id('db_sales');
		$sales_code = $init_code . $count_id;
		$sales_date = date('Y-m-d');
		$customer_id = $input['customer_id'];
		$payment_type = $input['payment_type'];
		$action = $input['action'] ?? 'pay';
		$cart = $input['cart'] ?? [];
		$discount = parse_amount($input['discount'] ?? 0);
		$sales_note = $input['sales_note'] ?? '';
		$amount_paid = parse_amount($input['amount_paid'] ?? 0);
		$is_hold = ($action === 'hold');
		$is_plan = ($action === 'plan');
		$is_split = ($action === 'split');
		$is_pay = ($action === 'pay');

		if(empty($warehouse_id)){
			ob_end_clean();
			echo json_encode(['status' => 'error', 'message' => 'No warehouse found for this store. Please set up a warehouse in Settings.']);
			exit;
		}

		$subtotal = 0;
		$tax_total = 0;
		$rowcount = count($cart);

		$_POST = [];
		$_POST['command'] = 'save';
		$_POST['sales_date'] = $sales_date;
		$_POST['due_date'] = '';
		$_POST['reference_no'] = '';
		$_POST['sales_status'] = $is_hold ? 'Pending' : 'Final';
		$_POST['customer_id'] = $customer_id;
		$_POST['other_charges_input'] = '0';
		$_POST['other_charges_tax_id'] = '';
		$_POST['other_charges_amt'] = '0';
		$_POST['discount_to_all_input'] = number_format($discount, 2, '.', '');
		$_POST['discount_to_all_type'] = 'in_fixed';
		$_POST['tot_discount_to_all_amt'] = number_format($discount, 2, '.', '');
		$_POST['sales_note'] = $sales_note;
		$_POST['rowcount'] = $rowcount;
		$_POST['sales_id'] = '';
		$_POST['warehouse_id'] = $warehouse_id;
		$_POST['store_id'] = $store_id;
		$_POST['count_id'] = $count_id;
		$_POST['init_code'] = $init_code;
		$_POST['coupon_code'] = $input['coupon_code'] ?? '';
		$_POST['coupon_discount_amt'] = '0';
		$_POST['invoice_terms'] = '';
		$_POST['quotation_id'] = '';

		for($i = 1; $i <= $rowcount; $i++){
			$item = $cart[$i - 1];
			$qty = (float) $item['qty'];
			$price = (float) $item['price'];
			$purchase_price = (float) ($item['purchase_price'] ?? 0);
			$tax_value = (float) ($item['tax_value'] ?? 0);
			$tax_type = $item['tax_type'] ?? 'Exclusive';
			$tax_id = $item['tax_id'] ?? 0;
			$tax_name = $item['tax_name'] ?? '';
			$discount = 0;

			$line_total = $qty * $price;
			if($tax_type == 'Exclusive'){
				$tax_amount = ($line_total * $tax_value) / 100;
				$line_total_with_tax = $line_total + $tax_amount;
			} else {
				$tax_amount = $line_total - ($line_total / (1 + ($tax_value/100)));
				$line_total_with_tax = $line_total;
				$line_total = $line_total - $tax_amount;
			}

			$subtotal += $line_total;
			$tax_total += $tax_amount;

			$_POST['tr_item_id_'.$i] = $item['id'];
			$_POST['td_data_'.$i.'_3'] = $qty;
			$_POST['td_data_'.$i.'_4'] = $price;
			$_POST['td_data_'.$i.'_5'] = $tax_value;
			$_POST['td_data_'.$i.'_7'] = number_format($tax_amount, 2, '.', '');
			$_POST['td_data_'.$i.'_8'] = '0.00';
			$_POST['td_data_'.$i.'_9'] = number_format($line_total_with_tax, 2, '.', '');
			$_POST['td_data_'.$i.'_10'] = $price;
			$_POST['td_data_'.$i.'_11'] = number_format($tax_amount, 2, '.', '');
			$_POST['td_data_'.$i.'_12'] = $tax_name;
			$_POST['td_data_'.$i.'_13'] = $purchase_price;
			$_POST['tr_tax_id_'.$i] = $tax_id;
			$_POST['tr_tax_value_'.$i] = $tax_value;
			$_POST['tr_tax_type_'.$i] = $tax_type;
			$_POST['item_discount_input_'.$i] = '0';
			$_POST['item_discount_type_'.$i] = 'Percentage';
			$_POST['description_'.$i] = '';
			$_POST['price_type_'.$i] = $item['price_type'] ?? 'retail';
		}

		$total = $subtotal + $tax_total - $discount;
		$round_off = round($total, 2) - $total;
		$grand_total = round($total, 2);

		if($is_hold){
			$paid = 0;
		} else if($is_plan){
			// PayPlan: down payment paid now; remainder scheduled as installments
			$paid = ($amount_paid > 0) ? min($amount_paid, $grand_total) : 0;
		} else if($is_pay){
			// Full payment by default; if amount_paid provided, record up to grand_total
			if($amount_paid > 0 && $amount_paid < ($grand_total - 0.01)){
				$paid = $amount_paid;
			} else {
				$paid = $grand_total;
			}
		} else if($is_split){
			// Split may come as a single amount or as multiple payment rows
			$payment_rows = $input['payment_rows'] ?? [];
			if(!empty($payment_rows) && is_array($payment_rows)){
				$row_total = 0;
				foreach($payment_rows as $pr){
					$row_total += (float) ($pr['amount'] ?? 0);
				}
				$paid = min($row_total, $grand_total);
			} else {
				$paid = ($amount_paid > 0) ? min($amount_paid, $grand_total) : 0;
			}
		} else {
			$paid = $grand_total;
		}

		$_POST['tot_subtotal_amt'] = number_format($subtotal, 2, '.', '');
		$_POST['tot_round_off_amt'] = number_format($round_off, 2, '.', '');
		$_POST['tot_total_amt'] = number_format($grand_total, 2, '.', '');
		if($is_split && !empty($payment_rows) && is_array($payment_rows)){
			// For split payments, calculate total paid from all rows for validation
			$split_total_paid = 0;
			foreach($payment_rows as $pr){
				$split_total_paid += (float) ($pr['amount'] ?? 0);
			}
			// Pass the actual paid amount for walk-in customer validation
			$_POST['amount'] = number_format(min($split_total_paid, $grand_total), 2, '.', '');
			$_POST['payment_type'] = '';
			$_POST['account_id'] = '';
			$_POST['payment_note'] = '';
		} else {
			$_POST['amount'] = number_format($paid, 2, '.', '');
			$_POST['payment_type'] = $payment_type;
			$_POST['payment_note'] = '';
			$_POST['account_id'] = $input['account_id'] ?? '';
		}
		$_POST['cheque_number'] = '';
		$_POST['cheque_period'] = '';
		$_POST['allow_tot_advance'] = ($input['allow_tot_advance'] === 'checked') ? 'checked' : '';

		// CRITICAL: Sales_model reads item fields from $_REQUEST (e.g. $_REQUEST['tr_item_id_1']).
		// $_REQUEST is a snapshot taken at request start and is NOT updated when $_POST is
		// assigned at runtime, so we must mirror $_POST into $_REQUEST or the item loop
		// finds nothing and the sale is saved without any items.
		foreach($_POST as $k => $v){
			$_REQUEST[$k] = $v;
		}

		ob_start();
		try {
			$result = $this->sales->verify_save_and_update();
		} catch (Throwable $e) {
			$result = 'EXCEPTION: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
			log_message('error', 'Mobile save() exception: ' . $result);
		}
		$ob_output = ob_get_clean();
		if($ob_output && stripos($result, 'success') === false){
			log_message('error', 'Mobile save() hidden output: ' . substr($ob_output, 0, 2000));
			$result = trim($result . "\n" . $ob_output);
		}

		if(stripos($result, 'success') !== false){
			$parts = explode('<<<###>>>', $result);
			$sales_id = isset($parts[1]) ? (int) $parts[1] : 0;

			// Create PayPlan / installment schedule when needed
			$plan_id = 0;
			if($is_plan && $sales_id && !empty($input['bnpl'])){
				$this->load->model('Installments_model','installments');
				$bnpl = $input['bnpl'];
				$plan_id = $this->installments->create_plan(array(
					'store_id'            => $store_id,
					'sales_id'            => $sales_id,
					'customer_id'         => $customer_id,
					'total_amount'        => $grand_total,
					'down_payment_amount' => $paid,
					'down_payment_paid'   => ($paid > 0),
					'down_payment_type'   => $payment_type,
					'installment_count'   => (int) ($bnpl['count'] ?? 0),
					'installment_amount'  => (float) ($bnpl['each_amt'] ?? 0),
					'frequency'           => $bnpl['frequency'] ?? 'biweekly',
					'first_due_date'      => date('Y-m-d', strtotime(($bnpl['first_due'] ?? date('Y-m-d')))),
					'late_fee_per_day'    => (float) ($bnpl['late_fee'] ?? 0),
				));
			}

			// Record multiple split-payment rows if provided
			if($is_split && !empty($payment_rows) && is_array($payment_rows) && $sales_id){
				$total_recorded = 0;
				foreach($payment_rows as $pr){
					$pm = (float) ($pr['amount'] ?? 0);
					if($pm <= 0) continue;
					// Don't record more than the invoice total
					if($total_recorded + $pm > $grand_total){
						$pm = $grand_total - $total_recorded;
					}
					if($pm <= 0) break;
					
					$pm_type = $pr['payment_type'] ?? get_default_payment_mode_code($store_id);
					$pm_row = $this->db->select('id')->where('store_id', $store_id)->where('code', $pm_type)->get('db_payment_modes')->row();
					$payment_mode_id = $pm_row ? $pm_row->id : null;
					$salespayments_entry = array(
						'payment_code'        => get_init_code('sales_payment'),
						'count_id'            => get_count_id('db_salespayments'),
						'store_id'            => $store_id,
						'sales_id'            => $sales_id,
						'payment_date'        => system_fromatted_date($sales_date),
						'payment_type'        => $pm_type,
						'payment_mode_id'     => $payment_mode_id,
						'payment'             => number_format($pm, 2, '.', ''),
						'payment_note'        => '',
						'payment_reference'   => ($pr['payment_reference'] ?? ''),
						'confirmation_status' => 1,
						'created_date'        => date('Y-m-d'),
						'created_time'        => date('H:i:s'),
						'created_by'          => $this->session->userdata('inv_username'),
						'system_ip'           => $_SERVER['REMOTE_ADDR'] ?? '',
						'system_name'         => gethostname() ?: '',
						'status'              => 1,
						'account_id'          => !empty($pr['account_id']) ? $pr['account_id'] : null,
						'customer_id'         => $customer_id,
						'cheque_number'       => '',
						'cheque_period'       => '',
						'cheque_status'       => 'Pending',
					);
					$this->db->insert('db_salespayments', $salespayments_entry);
					$total_recorded += $pm;
					if($total_recorded >= $grand_total) break;
				}
				$this->sales->update_sales_payment_status($sales_id, $customer_id);
			}

			// Record loyalty/store-credit/gift-card redemptions after the sale is saved
			if($sales_id){
				$redeem_points = (int) ($input['redeem_points'] ?? 0);
				$redeem_store_credit = parse_amount($input['redeem_store_credit'] ?? 0);
				$redeem_gift_card_id = (int) ($input['redeem_gift_card_id'] ?? 0);
				$old_post = $_POST;

				// Capture any output from redemption calls to prevent JSON corruption
				ob_start();
				if($redeem_points > 0){
					$this->load->model('loyalty_model','loyalty');
					$_POST = array('customer_id' => $customer_id, 'points' => $redeem_points, 'sales_id' => $sales_id);
					$this->loyalty->redeem_points_ajax();
				}
				if($redeem_store_credit > 0){
					$this->load->model('store_credit_model','store_credit');
					$_POST = array('customer_id' => $customer_id, 'amount' => $redeem_store_credit, 'sales_id' => $sales_id, 'store_id' => $store_id);
					$this->store_credit->redeem_ajax();
				}
				if($redeem_gift_card_id > 0){
					$this->load->model('gift_cards_model','gift_cards');
					$_POST = array('card_id' => $redeem_gift_card_id, 'amount' => (float) ($input['redeem_gift_card_amount'] ?? 0), 'sales_id' => $sales_id, 'customer_id' => $customer_id, 'store_id' => $store_id);
					$this->gift_cards->redeem_ajax();
				}

				ob_end_clean();
				$_POST = $old_post;
			}

			$msg = $plan_id ? 'Sale and PayPlan saved.' : 'Sale saved.';
			$whatsapp = ($sales_id) ? get_whatsapp_share_url('sales', $sales_id) : array('url' => '');
			ob_end_clean();
			echo json_encode(['status' => 'success', 'message' => $msg, 'sales_id' => $sales_id, 'plan_id' => $plan_id, 'whatsapp_url' => $whatsapp['url'] ?? '', 'redirect' => base_url('mobile')]); exit;
		} else {
			ob_end_clean();
			echo json_encode(['status' => 'error', 'message' => (string) $result]); exit;
		}
	}

	public function profile()
	{
		$data = $this->data;
		$data['page_title'] = 'My Profile';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['email'] = $this->session->userdata('email') ?: '';
		$data['branch_name'] = get_store_name();
		$user_id = $this->session->userdata('inv_userid');
		$user = $this->db->select('profile_picture')->where('id', $user_id)->get('db_users')->row();
		$data['profile_picture'] = ($user && !empty($user->profile_picture)) ? $user->profile_picture : '';
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/profile', $data);
	}

	public function update_profile_picture()
	{
		$user_id = $this->session->userdata('inv_userid');
		if(!$user_id){
			echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
			return;
		}
		if(empty($_FILES['avatar']['name'])){
			echo json_encode(['status' => 'error', 'message' => 'No file selected.']);
			return;
		}
		$upload_dir = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . (int)$user_id . DIRECTORY_SEPARATOR;
		if(!is_dir($upload_dir)){
			if(!@mkdir($upload_dir, 0777, true)){
				echo json_encode(['status' => 'error', 'message' => 'Could not create upload directory.']);
				return;
			}
		}
		if(!is_writable($upload_dir)){
			@chmod($upload_dir, 0777);
		}
		$config = [
			'upload_path' => $upload_dir,
			'allowed_types' => 'jpg|jpeg|png|gif',
			'encrypt_name' => TRUE,
		];
		$this->load->library('upload', $config);
		if(!$this->upload->do_upload('avatar')){
			$err = $this->upload->display_errors('', '');
			log_message('error', 'Avatar upload failed for user ' . $user_id . ': ' . $err);
			echo json_encode(['status' => 'error', 'message' => $err]);
			return;
		}
		$upload = $this->upload->data();
		$ext = strtolower($upload['file_ext'] ?: '.' . pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
		if(!in_array($ext, ['.jpg', '.jpeg', '.png', '.gif'])){
			$ext = '.jpg';
		}
		$new_name = 'avatar' . $ext;
		$new_path = $upload_dir . $new_name;
		if(file_exists($new_path)){
			@unlink($new_path);
		}
		if(!@rename($upload['full_path'], $new_path)){
			@unlink($upload['full_path']);
			echo json_encode(['status' => 'error', 'message' => 'Could not save uploaded file.']);
			return;
		}
		$forward_path = 'uploads/users/' . (int)$user_id . '/' . $new_name;
		$this->db->where('id', (int)$user_id)->update('db_users', ['profile_picture' => $forward_path]);
		echo json_encode(['status' => 'success', 'message' => 'Profile picture updated.', 'url' => base_url($forward_path)]);
	}

	public function update_password()
	{
		$old = $this->input->post('old_password', TRUE);
		$new = $this->input->post('new_password', TRUE);
		$confirm = $this->input->post('confirm_password', TRUE);
		$email = $this->session->userdata('email');

		if(empty($old) || empty($new) || empty($confirm)){
			echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
			return;
		}

		if($new !== $confirm){
			echo json_encode(['status' => 'error', 'message' => 'New passwords do not match.']);
			return;
		}

		if(strlen($new) < 6){
			echo json_encode(['status' => 'error', 'message' => 'Password must be at least 6 characters.']);
			return;
		}

		$user = $this->db->where('email', $email)->where('status', 1)->get('db_users')->row();
		if(!$user){
			echo json_encode(['status' => 'error', 'message' => 'User not found.']);
			return;
		}

		$valid = false;
		if(password_verify($old, $user->password)){
			$valid = true;
		} else if($user->password === md5($old)){
			$valid = true;
		}

		if(!$valid){
			echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
			return;
		}

		$this->load->model('login_model');
		if($this->login_model->change_password($new, $email)){
			echo json_encode(['status' => 'success', 'message' => 'Password changed successfully.']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Could not change password.']);
		}
	}

	public function stock()
	{
		$this->permission_check('items_view');
		$data = $this->data;
		$data['page_title'] = 'Stock';
		$store_id = get_current_store_id();
		$warehouse_id = get_store_warehouse_id();

		// Low-stock snapshot for the current branch/warehouse
		$data['low_stock_count'] = $this->dashboard->get_low_stock_count($warehouse_id);
		$data['low_stock_items'] = $this->dashboard->get_low_stock_items($warehouse_id);

		// All active products with their available quantity for the current warehouse
		$this->db->select("a.id, a.item_name, a.item_code, a.alert_qty, a.status, COALESCE(b.available_qty, a.stock, 0) as stock, c.category_name, d.brand_name");
		$this->db->from("db_items a");
		$this->db->join("db_warehouseitems b", "b.item_id = a.id AND b.warehouse_id = '{$warehouse_id}' AND b.store_id = {$store_id}", "left");
		$this->db->join("db_category c", "c.id = a.category_id", "left");
		$this->db->join("db_brands d", "d.id = a.brand_id", "left");
		$this->db->where("a.store_id", $store_id);
		$this->db->where("a.status", 1);
		$this->db->where("a.service_bit !=", 1);
		$this->db->where("(a.not_for_sale IS NULL OR a.not_for_sale = 0)", null, false);
		$this->db->order_by("a.item_name", "asc");
		$data['stock_items'] = $this->db->get()->result();

		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['branch_name'] = get_store_name();
		$data['warehouse_id'] = $warehouse_id;

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/stock', $data);
	}

	public function product($id = 0)
	{
		$id = (int)$id;
		$is_update = $id > 0;
		$this->permission_check($is_update ? 'items_edit' : 'items_add');
		if($is_update) $this->belong_to('db_items', $id);

		$data = $this->data;
		$data['page_title'] = $is_update ? 'Edit Product' : 'Add Product';
		$store_id = get_current_store_id();
		$data['store_id'] = $store_id;
		$data['item_code'] = $is_update ? '' : get_init_code('item');
		$data['q_id'] = $is_update ? $id : '';
		$data['command'] = $is_update ? 'update' : 'save';

		// Defaults (same as desktop items.php)
		$data['item_name'] = $data['sku'] = $data['hsn'] = '';
		$data['brand_id'] = $data['category_id'] = $data['tax_id'] = $data['unit_id'] = '';
		$data['price'] = $data['purchase_price'] = $data['sales_price'] = $data['mrp'] = $data['profit_margin'] = $data['discount'] = '';
		$data['alert_qty'] = $data['seller_points'] = $data['custom_barcode'] = $data['description'] = '';
		$data['discount_type'] = 'Percentage';
		$data['tax_type'] = 'Exclusive';
		$data['item_group'] = 'Single';
		$data['track_serial'] = $data['track_imei'] = $data['not_for_sale'] = 0;
		$data['consumable_unit'] = $data['serial_number'] = $data['imei_number'] = $data['warranty_months'] = '';
		$data['expire_date'] = $data['mfg_date'] = '';
		$data['adjustment_qty'] = '0';
		$data['batch_lot'] = '';

		if($is_update){
			$item = $this->db->where('id', $id)->where('store_id', $store_id)->where('status', 1)->get('db_items')->row();
			if(!$item){ show_404(); exit; }
			$data['item_code'] = $item->item_code;
			$data['item_name'] = $item->item_name;
			$data['sku'] = $item->sku ?? '';
			$data['hsn'] = $item->hsn ?? '';
			$data['brand_id'] = $item->brand_id;
			$data['category_id'] = $item->category_id;
			$data['tax_id'] = $item->tax_id;
			$data['unit_id'] = $item->unit_id;
			$data['price'] = $item->price;
			$data['purchase_price'] = $item->purchase_price;
			$data['sales_price'] = $item->sales_price;
			$data['mrp'] = $item->mrp;
			$data['profit_margin'] = $item->profit_margin ?? '';
			$data['discount'] = $item->discount;
			$data['alert_qty'] = $item->alert_qty;
			$data['seller_points'] = $item->seller_points;
			$data['custom_barcode'] = $item->custom_barcode ?? '';
			$data['description'] = $item->description ?? '';
			$data['discount_type'] = $item->discount_type;
			$data['tax_type'] = $item->tax_type;
			$data['item_group'] = $item->item_group;
			$data['track_serial'] = $item->track_serial;
			$data['track_imei'] = $item->track_imei;
			$data['not_for_sale'] = $item->not_for_sale;
			$data['consumable_unit'] = $item->consumable_unit ?? '';
			$data['serial_number'] = $item->serial_number ?? '';
			$data['imei_number'] = $item->imei_number ?? '';
			$data['warranty_months'] = $item->warranty_months;
			$data['expire_date'] = (is_valid_date($item->expire_date) && $item->expire_date != '1970-01-01' && $item->expire_date != '0000-00-00') ? date('Y-m-d', strtotime($item->expire_date)) : '';
			$data['mfg_date'] = (is_valid_date($item->mfg_date) && $item->mfg_date != '1970-01-01' && $item->mfg_date != '0000-00-00') ? date('Y-m-d', strtotime($item->mfg_date)) : '';
			$data['batch_lot'] = $item->batch_lot ?? '';
		}

		// Recipes (if enabled)
		$data['recipes_list'] = [];
		if(recipe_module() && $this->db->table_exists('db_recipes')){
			$this->load->model('recipe_model');
			$recipes = $this->db->where('store_id', $store_id)->where('status', 1)->order_by('name')->get('db_recipes')->result();
			foreach($recipes as $r){
				$r->cost_per_unit = $this->recipe_model->calculate_cost_per_unit($r->id);
			}
			$data['recipes_list'] = $recipes;
		}

		// Available master variants for this store
		$data['variants'] = $this->db->where('store_id', $store_id)->where('status', 1)->order_by('variant_name', 'asc')->get('db_variants')->result();

		// Attribute map
		$this->load->model('items_model');
		$data['attribute_map'] = $this->items_model->get_variant_attribute_map($store_id);

		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['branch_name'] = get_store_name();

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/product', $data);
	}

	public function save_product()
	{
		$command = $this->input->post('command', TRUE) ?: 'save';
		$this->permission_check($command == 'update' ? 'items_edit' : 'items_add');

		// Pre-validate image before items_model::save_record tries an exit-on-fail upload
		if(!empty($_FILES['item_image']['name'])){
			$allowed = ['image/jpeg','image/png','image/gif','image/webp'];
			$ext = strtolower(pathinfo($_FILES['item_image']['name'], PATHINFO_EXTENSION));
			if(!in_array($_FILES['item_image']['type'], $allowed) || !in_array($ext, ['jpg','jpeg','png','gif','webp'])){
				echo json_encode(['status' => 'error', 'message' => 'Invalid image type. Only jpg, png, gif, webp allowed.']);
				return;
			}
			if($_FILES['item_image']['size'] > (1024 * 1024)){
				echo json_encode(['status' => 'error', 'message' => 'Image must be smaller than 1MB.']);
				return;
			}
		}

		$this->load->library('form_validation');
		$this->form_validation->set_rules('item_name', 'Product Name', 'trim|required');
		$this->form_validation->set_rules('category_id', 'Category', 'trim|required');
		$this->form_validation->set_rules('unit_id', 'Unit', 'trim|required');
		$this->form_validation->set_rules('tax_id', 'Tax', 'trim|required');

		if($this->input->post('item_group') == 'Single'){
			$this->form_validation->set_rules('price', 'Base/Cost Price', 'trim|required');
			$this->form_validation->set_rules('purchase_price', 'Purchase Price', 'trim|required');
			$this->form_validation->set_rules('sales_price', 'Sale Price', 'trim|required');
		}

		if($this->form_validation->run() == FALSE){
			echo json_encode(['status' => 'error', 'message' => strip_tags(validation_errors())]);
			return;
		}

		if($this->input->post('item_group') == 'Variants' && (int)$this->input->post('hidden_rowcount') < 1){
			echo json_encode(['status' => 'error', 'message' => 'Add at least one variant for a variant product.']);
			return;
		}

		if($command == 'save'){
			$product_check = check_subscription_limit('product_limit');
			if($product_check !== true){
				echo json_encode(['status' => 'error', 'message' => strip_tags($product_check)]);
				return;
			}
		}

		// For new attribute-generated variants, create db_variants records and rewrite IDs
		$store_id = get_current_store_id();
		$rowcount = (int)$this->input->post('hidden_rowcount', TRUE);
		log_message('error', "[SAVE_PRODUCT] command=$command rowcount=$rowcount item_group=" . $this->input->post('item_group', TRUE));
		for($i = 1; $i <= $rowcount; $i++){
			log_message('error', "[SAVE_PRODUCT] row $i: tr_variant_id=" . var_export($_REQUEST['tr_variant_id_'.$i] ?? 'NOT SET', true) . " variant_name=" . var_export($this->input->post('variant_name_'.$i, TRUE), true) . " td_data_2=" . var_export($_REQUEST['td_data_'.$i.'_2'] ?? 'NOT SET', true));
			$vid = $_REQUEST['tr_variant_id_'.$i] ?? '';
			if($vid === 'new' || $vid === '' || !is_numeric($vid)){
				$variant_name = $this->input->post('variant_name_'.$i, TRUE);
				if(empty($variant_name)) continue;
				$existing = $this->db->where('store_id', $store_id)->where('variant_name', $variant_name)->where('status', 1)->get('db_variants')->row();
				if($existing){
					$vid = $existing->id;
				} else {
					$this->db->insert('db_variants', [
						'variant_name' => $variant_name,
						'store_id' => $store_id,
						'status' => 1,
						'created_date' => date('Y-m-d'),
						'created_time' => date('H:i:s')
					]);
					$vid = $this->db->insert_id();
				}
				$_POST['tr_variant_id_'.$i] = $vid;
				$_REQUEST['tr_variant_id_'.$i] = $vid;
			}
		}

		$this->load->model('items_model','items');
		try{
			$result = $this->items->save_record(array('command' => $command));
			if(stripos($result, 'success') !== false){
				echo json_encode(['status' => 'success', 'message' => 'Product saved successfully.', 'redirect' => base_url('mobile/stock')]);
			} else {
				echo json_encode(['status' => 'error', 'message' => strip_tags($result)]);
			}
		} catch(Throwable $e){
			log_message('error', 'mobile save_product error: '.$e->getMessage());
			echo json_encode(['status' => 'error', 'message' => 'Save failed: '.$e->getMessage()]);
		}
	}

	public function stock_adjustments()
	{
		$this->permission_check('stock_adjustment_view');
		$data = $this->data;
		$data['page_title'] = 'Stock Adjustments';
		$data['active'] = 'more';
		$store_id = get_current_store_id();

		$this->db->select('a.id, a.adjustment_date, a.reference_no, a.adjustment_note, a.created_by');
		$this->db->from('db_stockadjustment a');
		$this->db->where('a.store_id', $store_id);
		$this->db->where('a.status', 1);
		$this->db->order_by('a.id', 'desc');
		$adjustments = $this->db->get()->result();

		foreach($adjustments as $adj){
			$items = $this->db->select('SUM(adjustment_qty) as total_qty, COUNT(*) as item_count')
				->from('db_stockadjustmentitems')
				->where('adjustment_id', $adj->id)
				->where('status', 1)
				->get()->row();
			$adj->total_qty = $items->total_qty ?? 0;
			$adj->item_count = $items->item_count ?? 0;
		}

		$data['adjustments'] = $adjustments;
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['branch_name'] = get_store_name();

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/stock_adjustments', $data);
	}

	public function stock_transfers()
	{
		$this->permission_check('stock_transfer_view');
		$data = $this->data;
		$data['page_title'] = 'Stock Transfers';
		$data['active'] = 'more';
		$store_id = get_current_store_id();

		$this->db->select('a.id, a.transfer_date, a.note, a.warehouse_from, a.warehouse_to, a.created_by');
		$this->db->from('db_stocktransfer a');
		$this->db->where('a.store_id', $store_id);
		$this->db->where('a.status', 1);
		$this->db->order_by('a.id', 'desc');
		$transfers = $this->db->get()->result();

		foreach($transfers as $t){
			$items = $this->db->select('SUM(transfer_qty) as total_qty, COUNT(*) as item_count')
				->from('db_stocktransferitems')
				->where('stocktransfer_id', $t->id)
				->where('status', 1)
				->get()->row();
			$t->total_qty = $items->total_qty ?? 0;
			$t->item_count = $items->item_count ?? 0;
			$t->from_name = get_warehouse_name($t->warehouse_from);
			$t->to_name = get_warehouse_name($t->warehouse_to);
		}

		$data['transfers'] = $transfers;
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['branch_name'] = get_store_name();

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/stock_transfers', $data);
	}

	public function stock_transfer_form()
	{
		$this->permission_check('stock_transfer_add');
		$data = $this->data;
		$data['page_title'] = 'New Stock Transfer';
		$data['active'] = 'more';

		$data['transfer_date'] = date('Y-m-d');
		$data['warehouse_from'] = '';
		$data['warehouse_to'] = '';
		$data['note'] = '';

		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['branch_name'] = get_store_name();

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/stock_transfer_form', $data);
	}

	public function save_stock_transfer()
	{
		$this->permission_check_with_msg('stock_transfer_add');
		$this->load->model('stock_transfer_model', 'stock_transfer');

		$this->load->library('form_validation');
		$this->form_validation->set_rules('transfer_date', 'Transfer Date', 'trim|required');
		$this->form_validation->set_rules('warehouse_from', 'From Branch', 'trim|required');
		$this->form_validation->set_rules('warehouse_to', 'To Branch', 'trim|required');

		if($this->form_validation->run() == FALSE){
			echo json_encode(['status' => 'error', 'message' => strip_tags(validation_errors())]);
			return;
		}

		$warehouse_from = $this->input->post('warehouse_from', TRUE);
		$warehouse_to = $this->input->post('warehouse_to', TRUE);
		if($warehouse_from == $warehouse_to){
			echo json_encode(['status' => 'error', 'message' => 'From and To branches cannot be the same.']);
			return;
		}

		$result = $this->stock_transfer->verify_save_and_update();
		if(stripos($result, 'success') !== false){
			$parts = explode('<<<###>>>', $result);
			echo json_encode(['status' => 'success', 'message' => 'Stock transfer saved.', 'redirect' => base_url('mobile/stock_transfers'), 'transfer_id' => $parts[1] ?? '']);
		} else {
			echo json_encode(['status' => 'error', 'message' => strip_tags($result)]);
		}
	}

	public function delete_stock_transfer()
	{
		$this->permission_check_with_msg('stock_transfer_delete');
		$id = (int)$this->input->post('q_id', TRUE);
		if($id <= 0){
			echo json_encode(['status' => 'error', 'message' => 'Invalid stock transfer.']);
			return;
		}
		$this->belong_to('db_stocktransfer', $id);

		$this->load->model('stock_transfer_model', 'stock_transfer');
		$result = $this->stock_transfer->delete_stock($id);
		if($result == 'success'){
			echo json_encode(['status' => 'success', 'message' => 'Stock transfer deleted.']);
		} else {
			echo json_encode(['status' => 'error', 'message' => strip_tags($result)]);
		}
	}

	public function find_item_for_transfer()
	{
		$item_id = (int)$this->input->post('id', TRUE);
		$warehouse_id = (int)$this->input->post('warehouse_id', TRUE);
		if(!$item_id || !$warehouse_id){
			echo json_encode(['id' => 0, 'item_name' => '', 'available_qty' => 0]);
			return;
		}
		$item = $this->db->where('id', $item_id)->get('db_items')->row();
		if(!$item){
			echo json_encode(['id' => 0, 'item_name' => '', 'available_qty' => 0]);
			return;
		}
		$available = total_available_qty_items_of_warehouse($warehouse_id, '', $item_id);
		echo json_encode(['id' => $item->id, 'item_name' => $item->item_name, 'available_qty' => (float)$available]);
	}

	public function price_catalogue()
	{
		$this->permission_check('items_view');
		$data = $this->data;
		$data['page_title'] = 'Price Catalogue';
		$data['active'] = 'more';
		$store_id = get_current_store_id();

		$this->db->select('a.id, a.item_name, a.item_code, a.sales_price, a.online_price, a.discount_type, a.discount, a.stock, b.category_name, c.brand_name');
		$this->db->from('db_items a');
		$this->db->where('a.item_group !=', 'Variants');
		$this->db->join('db_category b', 'b.id = a.category_id', 'left');
		$this->db->join('db_brands c', 'c.id = a.brand_id', 'left');
		$this->db->where('a.store_id', $store_id);
		$this->db->where('a.status', 1);
		$this->db->where('a.service_bit !=', 1);
		$this->db->order_by('a.item_name', 'asc');
		$data['products'] = $this->db->get()->result();

		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['branch_name'] = get_store_name();

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/price_catalogue', $data);
	}

	public function catalogue($page = 1)
	{
		$this->permission_check('items_view');
		$data = $this->data;
		$data['page_title'] = 'Catalogue';
		$data['active'] = 'more';
		$store_id = get_current_store_id();
		$page = max(1, (int)$page);
		$limit = 50;
		$offset = ($page - 1) * $limit;
		$search = trim($this->input->get('search', TRUE) ?? '');
		$category_id = (int)($this->input->get('category', TRUE) ?? 0);
		$price_type = $this->input->get('price_type', TRUE);
		$price_type = ($price_type === 'wholesale') ? 'wholesale' : 'retail';
		$data['search'] = $search;
		$data['category_id'] = $category_id;
		$data['price_type'] = $price_type;

		$data['categories'] = $this->db->where('store_id', $store_id)->where('status', 1)
			->order_by('category_name', 'asc')->get('db_category')->result();

		$this->db->select('a.id, a.item_name, a.item_code, a.custom_barcode, a.mrp, a.sales_price, a.online_price, a.discount_type, a.discount, a.stock, a.category_id, a.brand_id, a.status, a.child_bit, a.item_group, b.category_name, c.brand_name');
		$this->db->from('db_items a');
		$this->db->where('(a.child_bit = 0 OR a.child_bit IS NULL)', null, false);
		$this->db->join('db_category b', 'b.id = a.category_id', 'left');
		$this->db->join('db_brands c', 'c.id = a.brand_id', 'left');
		$this->db->where('a.store_id', $store_id);
		$this->db->where('a.status', 1);
		if($search){
			$this->db->group_start();
			$this->db->like('a.item_name', $search);
			$this->db->or_like('a.item_code', $search);
			$this->db->or_like('a.custom_barcode', $search);
			$this->db->group_end();
		}
		if($category_id){
			$this->db->where('a.category_id', $category_id);
		}
		$this->db->order_by('a.item_name', 'asc');
		$this->db->limit($limit, $offset);
		$data['products'] = $this->db->get()->result();
		$data['page'] = $page;
		$data['limit'] = $limit;
		$data['has_more'] = count($data['products']) == $limit;

		$data['can_edit'] = $this->permissions('items_edit');
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['branch_name'] = get_store_name();

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/catalogue', $data);
	}

	public function update_catalogue_price()
	{
		$this->permission_check_with_msg('items_edit');
		$store_id = get_current_store_id();
		$id = (int)$this->input->post('item_id', TRUE);
		$sales_price = (float)$this->input->post('sales_price', TRUE);
		$online_price = (float)$this->input->post('online_price', TRUE);

		if($id <= 0){
			echo json_encode(['success' => false, 'message' => 'Invalid product.']);
			return;
		}

		$item = $this->db->where('id', $id)->where('store_id', $store_id)->get('db_items')->row();
		if(!$item){
			echo json_encode(['success' => false, 'message' => 'Product not found.']);
			return;
		}

		$this->db->where('id', $id)->update('db_items', [
			'sales_price' => $sales_price,
			'online_price' => $online_price,
		]);
		echo json_encode(['success' => true, 'message' => 'Prices updated.']);
	}

	public function product_view($id)
	{
		$this->permission_check('items_view');
		$this->belong_to('db_items', $id);
		$data = $this->data;
		$data['page_title'] = 'Product Details';
		$data['active'] = 'more';
		$store_id = get_current_store_id();

		$item = $this->db->where('id', $id)->where('store_id', $store_id)->where('status', 1)->get('db_items')->row();
		if(!$item){ show_404(); exit; }

		$data['item'] = $item;
		$data['category'] = $this->db->where('id', $item->category_id)->where('store_id', $store_id)->get('db_category')->row();
		$data['brand'] = $this->db->where('id', $item->brand_id)->where('store_id', $store_id)->get('db_brands')->row();

		$activities = [];

		// Sales
		$this->db->select('s.id as ref_id, s.sales_code as reference, s.sales_date as activity_date, c.customer_name as party, si.sales_qty as qty, si.unit_total_cost as amount, si.sold_serial_number as serial, si.sold_imei_number as imei');
		$this->db->from('db_salesitems si');
		$this->db->join('db_sales s', 's.id = si.sales_id', 'left');
		$this->db->join('db_customers c', 'c.id = s.customer_id', 'left');
		$this->db->where('si.item_id', $id);
		$this->db->where('s.sales_status', 'Final');
		$this->db->where('s.sales_date IS NOT NULL');
		$this->db->order_by('s.sales_date', 'DESC');
		$this->db->limit(15);
		foreach($this->db->get()->result() as $ts){
			$note = [];
			if(!empty($ts->serial)) $note[] = 'S/N: '.$ts->serial;
			if(!empty($ts->imei)) $note[] = 'IMEI: '.$ts->imei;
			$activities[] = (object)[
				'type' => 'Sale',
				'icon' => 'fa-shopping-cart',
				'color' => 'danger',
				'date' => $ts->activity_date,
				'reference' => $ts->reference,
				'party' => ($ts->party ?: 'Walk-in'),
				'qty' => (float)$ts->qty * -1,
				'amount' => $ts->amount,
				'note' => implode(' ', $note)
			];
		}

		// Purchases
		$this->db->select('p.id as ref_id, p.purchase_code as reference, p.purchase_date as activity_date, s.supplier_name as party, pi.purchase_qty as qty, pi.unit_total_cost as amount, pi.batch_lot as batch, pi.barcode as barcode');
		$this->db->from('db_purchaseitems pi');
		$this->db->join('db_purchase p', 'p.id = pi.purchase_id', 'left');
		$this->db->join('db_suppliers s', 's.id = p.supplier_id', 'left');
		$this->db->where('pi.item_id', $id);
		$this->db->where_in('pi.purchase_status', ['Received','Partially Received']);
		$this->db->where('p.purchase_date IS NOT NULL');
		$this->db->order_by('p.purchase_date', 'DESC');
		$this->db->limit(15);
		foreach($this->db->get()->result() as $tp){
			$note = [];
			if(!empty($tp->batch)) $note[] = 'Batch: '.$tp->batch;
			if(!empty($tp->barcode)) $note[] = 'Barcode: '.$tp->barcode;
			$activities[] = (object)[
				'type' => 'Purchase',
				'icon' => 'fa-truck',
				'color' => 'success',
				'date' => $tp->activity_date,
				'reference' => $tp->reference,
				'party' => ($tp->party ?: 'Supplier'),
				'qty' => (float)$tp->qty,
				'amount' => $tp->amount,
				'note' => implode(' ', $note)
			];
		}

		// Stock Adjustments
		$this->db->select('a.id as ref_id, a.reference_no as reference, a.adjustment_date as activity_date, a.adjustment_note as party, ai.adjustment_qty as qty, ai.description as note');
		$this->db->from('db_stockadjustmentitems ai');
		$this->db->join('db_stockadjustment a', 'a.id = ai.adjustment_id', 'left');
		$this->db->where('ai.item_id', $id);
		$this->db->where('a.status', 1);
		$this->db->where('ai.status', 1);
		$this->db->where('a.adjustment_date IS NOT NULL');
		$this->db->order_by('a.adjustment_date', 'DESC');
		$this->db->limit(15);
		foreach($this->db->get()->result() as $ta){
			$activities[] = (object)[
				'type' => 'Adjustment',
				'icon' => 'fa-sliders',
				'color' => 'warning',
				'date' => $ta->activity_date,
				'reference' => ($ta->reference ?: 'ADJ-'.$ta->ref_id),
				'party' => ($ta->party ?: 'Stock adjustment'),
				'qty' => (float)$ta->qty,
				'amount' => null,
				'note' => $ta->note
			];
		}

		// Stock Transfers
		$this->db->select('t.id as ref_id, t.note as reference, t.transfer_date as activity_date, ti.warehouse_from as from_id, ti.warehouse_to as to_id, wf.warehouse_name as wh_from, wt.warehouse_name as wh_to, ti.transfer_qty as qty');
		$this->db->from('db_stocktransferitems ti');
		$this->db->join('db_stocktransfer t', 't.id = ti.stocktransfer_id', 'left');
		$this->db->join('db_warehouse wf', 'wf.id = ti.warehouse_from', 'left');
		$this->db->join('db_warehouse wt', 'wt.id = ti.warehouse_to', 'left');
		$this->db->where('ti.item_id', $id);
		$this->db->where('t.status', 1);
		$this->db->where('ti.status', 1);
		$this->db->where('t.transfer_date IS NOT NULL');
		$this->db->order_by('t.transfer_date', 'DESC');
		$this->db->limit(15);
		foreach($this->db->get()->result() as $tt){
			$from = $tt->wh_from ?: 'Branch #'.$tt->from_id;
			$to = $tt->wh_to ?: 'Branch #'.$tt->to_id;
			$activities[] = (object)[
				'type' => 'Transfer',
				'icon' => 'fa-exchange',
				'color' => 'info',
				'date' => $tt->activity_date,
				'reference' => ($tt->reference ?: 'Transfer'),
				'party' => $from.' → '.$to,
				'qty' => (float)$tt->qty,
				'amount' => null,
				'note' => ''
			];
		}

		usort($activities, function($a, $b){
			return strtotime($b->date) - strtotime($a->date);
		});
		$data['activities'] = array_slice($activities, 0, 25);

		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['branch_name'] = get_store_name();

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/product_view', $data);
	}

	public function attributes()
	{
		$this->permission_check('attributes_view');
		$data = $this->data;
		$data['page_title'] = 'Attributes';
		$data['active'] = 'more';
		$store_id = get_current_store_id();

		$this->db->where('store_id', $store_id);
		$this->db->where('status', 1);
		$this->db->order_by('attribute_type', 'asc');
		$this->db->order_by('sort_order', 'asc');
		$this->db->order_by('attribute_value', 'asc');
		$data['attributes'] = $this->db->get('db_attributes')->result();

		$types = [];
		foreach($data['attributes'] as $a){
			if(!in_array($a->attribute_type, $types)) $types[] = $a->attribute_type;
		}
		$data['attribute_types'] = $types;

		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['branch_name'] = get_store_name();

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/attributes', $data);
	}

	public function attribute_form($id = 0)
	{
		$is_update = (int)$id > 0;
		$this->permission_check($is_update ? 'attributes_edit' : 'attributes_add');
		$data = $this->data;
		$data['page_title'] = $id > 0 ? 'Edit Attribute' : 'New Attribute';
		$data['active'] = 'more';
		$store_id = get_current_store_id();

		$data['q_id'] = 0;
		$data['attribute_type'] = '';
		$data['attribute_value'] = '';
		$data['sort_order'] = 0;

		$this->load->model('attributes_model', 'attributes');
		$data['attribute_types'] = $this->attributes->get_attribute_types($store_id);

		if($id > 0){
			$this->belong_to('db_attributes', $id);
			$detail = $this->attributes->get_details($id);
			if(!$detail){ show_404(); exit; }
			$data['q_id'] = $detail->id;
			$data['attribute_type'] = $detail->attribute_type;
			$data['attribute_value'] = $detail->attribute_value;
			$data['sort_order'] = $detail->sort_order;
		}

		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['branch_name'] = get_store_name();

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/attribute_form', $data);
	}

	public function save_attribute()
	{
		$command = $this->input->post('command', TRUE);
		$this->permission_check_with_msg($command == 'update' ? 'attributes_edit' : 'attributes_add');
		$this->load->model('attributes_model', 'attributes');

		$this->load->library('form_validation');
		$this->form_validation->set_rules('attribute_type', 'Attribute Type', 'trim|required');
		$this->form_validation->set_rules('attribute_value', 'Attribute Value', 'trim|required');

		if($this->form_validation->run() == FALSE){
			echo json_encode(['status' => 'error', 'message' => strip_tags(validation_errors())]);
			return;
		}

		if($command == 'update'){
			$result = $this->attributes->update_attribute();
		} else {
			$result = $this->attributes->verify_and_save();
		}

		if($result == 'success'){
			echo json_encode(['status' => 'success', 'message' => 'Attribute saved.', 'redirect' => base_url('mobile/attributes')]);
		} else {
			echo json_encode(['status' => 'error', 'message' => strip_tags($result)]);
		}
	}

	public function delete_attribute()
	{
		$this->permission_check_with_msg('attributes_delete');
		$this->load->model('attributes_model', 'attributes');
		$id = (int)$this->input->post('q_id', TRUE);
		if($id <= 0){
			echo 'Invalid attribute.';
			return;
		}
		echo $this->attributes->delete_attribute($id);
	}

	public function holds()
	{
		$this->permission_check('sales_add');
		$data = $this->data;
		$data['page_title'] = 'Held Sales';
		$data['active'] = 'holds';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$store_id = get_current_store_id();

		$this->db->select('h.id, h.reference_id, h.sales_date, h.grand_total, h.discount_to_all_input, h.customer_id, c.customer_name, (SELECT COUNT(*) FROM db_holditems hi WHERE hi.hold_id = h.id) as items_count', false);
		$this->db->from('db_hold h');
		$this->db->join('db_customers c', 'c.id = h.customer_id', 'left');
		$this->db->where('h.store_id', $store_id);
		$this->db->order_by('h.id', 'desc');
		$data['holds'] = $this->db->get()->result();

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/holds', $data);
	}

	public function delete_hold($id)
	{
		$this->permission_check('sales_add');
		$this->load->model('pos_model');
		$result = $this->pos_model->hold_invoice_delete($id);
		if($result == 'success'){
			$this->session->set_flashdata('success', 'Hold deleted.');
		} else {
			$this->session->set_flashdata('failed', 'Could not delete hold.');
		}
		redirect('mobile/holds');
	}

	public function more()
	{
		if(stripos(trim($this->session->userdata('role_name') ?: ''), 'cashier') !== false){
			redirect(base_url().'mobile/pos');
		}
		$data = $this->data;
		$data['page_title'] = 'More';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['branch_name'] = get_store_name();

		$menu_groups = [
			'Overview' => [
				['title' => 'Dashboard', 'desc' => "Home & today's summary", 'icon' => 'fa-home', 'url' => 'mobile', 'perm' => 'dashboard_view', 'color' => 'blue'],
			],
			'Sales' => [
				['title' => 'POS', 'desc' => 'Quick checkout', 'icon' => 'fa-calculator', 'url' => 'mobile/pos', 'perm' => 'pos', 'color' => 'primary'],
				['title' => 'New Sale', 'desc' => 'Create an invoice', 'icon' => 'fa-file-text-o', 'url' => 'mobile/sale', 'perm' => 'sales_add', 'color' => 'green'],
				['title' => 'New Quotation', 'desc' => 'Create a price quote', 'icon' => 'fa-quote-left', 'url' => 'mobile/quotation_form', 'perm' => 'quotation_add', 'color' => 'purple'],
				['title' => 'Quotations', 'desc' => 'Quotes & converted invoices', 'icon' => 'fa-list-alt', 'url' => 'mobile/quotations', 'perm' => 'quotation_view', 'color' => 'yellow'],
				['title' => 'Holds', 'desc' => 'Resume saved sales', 'icon' => 'fa-hand-paper-o', 'url' => 'mobile/holds', 'perm' => 'sales_add', 'color' => 'orange'],
				['title' => 'Sales List', 'desc' => 'View all sales', 'icon' => 'fa-list', 'url' => 'mobile/sales_list', 'perm' => 'sales_view', 'color' => 'blue'],
				['title' => 'Due Payments', 'desc' => 'Unpaid invoices', 'icon' => 'fa-money', 'url' => 'mobile/due', 'perm' => 'sales_view', 'color' => 'red'],
			],
			'Purchase' => [
				['title' => 'New Purchase', 'desc' => 'Create a purchase order', 'icon' => 'fa-plus-square', 'url' => 'mobile/purchase_form', 'perm' => 'purchase_add', 'color' => 'green'],
				['title' => 'Purchase History', 'desc' => 'View purchase invoices', 'icon' => 'fa-list', 'url' => 'mobile/purchase', 'perm' => 'purchase_view', 'color' => 'blue'],
			],
			'Inventory' => [
				['title' => 'Stock', 'desc' => 'Inventory levels', 'icon' => 'fa-cubes', 'url' => 'mobile/stock', 'perm' => 'items_view', 'color' => 'teal'],
				['title' => 'Add Product', 'desc' => 'Create a new item', 'icon' => 'fa-plus-circle', 'url' => 'mobile/product', 'perm' => 'items_add', 'color' => 'teal'],
				['title' => 'Stock Adjustments', 'desc' => 'View quantity adjustments', 'icon' => 'fa-sliders', 'url' => 'mobile/stock_adjustments', 'perm' => 'stock_adjustment_view', 'color' => 'teal'],
				['title' => 'Stock Transfers', 'desc' => 'Branch-to-branch transfers', 'icon' => 'fa-exchange', 'url' => 'mobile/stock_transfers', 'perm' => 'stock_transfer_view', 'color' => 'teal'],
				['title' => 'Price Catalogue', 'desc' => 'Product & service prices', 'icon' => 'fa-tags', 'url' => 'mobile/price_catalogue', 'perm' => 'items_view', 'color' => 'purple'],
				['title' => 'Catalogue', 'desc' => 'All items with editable prices', 'icon' => 'fa-book', 'url' => 'mobile/catalogue', 'perm' => 'items_view', 'color' => 'purple'],
				['title' => 'Attributes', 'desc' => 'Product variants & options', 'icon' => 'fa-cogs', 'url' => 'mobile/attributes', 'perm' => 'attributes_view', 'color' => 'purple'],
			],
			'Online Store' => [
				['title' => 'Store Dashboard', 'desc' => 'Online store overview', 'icon' => 'fa-dashboard', 'url' => 'mobile/online_store/dashboard', 'perm' => 'online_store_view', 'color' => 'blue'],
				['title' => 'Orders', 'desc' => 'Website & app orders', 'icon' => 'fa-shopping-cart', 'url' => 'mobile/online_store/orders', 'perm' => 'online_store_view', 'color' => 'primary'],
				['title' => 'Online Products', 'desc' => 'Products on the storefront', 'icon' => 'fa-cube', 'url' => 'mobile/online_store/products', 'perm' => 'online_store_view', 'color' => 'teal'],
				['title' => 'Services', 'desc' => 'Bookable services', 'icon' => 'fa-wrench', 'url' => 'mobile/online_store/services', 'perm' => 'online_store_view', 'color' => 'orange'],
				['title' => 'QR Codes', 'desc' => 'Scan-to-order QR codes', 'icon' => 'fa-qrcode', 'url' => 'mobile/online_store/qr_codes', 'perm' => 'online_store_view', 'color' => 'purple'],
				['title' => 'Appearance', 'desc' => 'Colours & storefront look', 'icon' => 'fa-paint-brush', 'url' => 'mobile/online_store/appearance', 'perm' => 'online_store_view', 'color' => 'yellow'],
				['title' => 'Banners', 'desc' => 'Storefront banners', 'icon' => 'fa-image', 'url' => 'mobile/online_store/banners', 'perm' => 'online_store_view', 'color' => 'green'],
				['title' => 'Homepage Builder', 'desc' => 'Drag & drop homepage', 'icon' => 'fa-th-large', 'url' => 'mobile/online_store/homepage_builder', 'perm' => 'online_store_view', 'color' => 'primary'],
				['title' => 'Domains', 'desc' => 'Custom web address', 'icon' => 'fa-globe', 'url' => 'mobile/online_store/domains', 'perm' => 'online_store_view', 'color' => 'blue'],
				['title' => 'Brands', 'desc' => 'Storefront brands', 'icon' => 'fa-copyright', 'url' => 'mobile/online_store/brands', 'perm' => 'online_store_view', 'color' => 'red'],
				['title' => 'Testimonials', 'desc' => 'Customer reviews', 'icon' => 'fa-comments', 'url' => 'mobile/online_store/testimonials', 'perm' => 'online_store_view', 'color' => 'purple'],
				['title' => 'Instagram', 'desc' => 'Instagram feed', 'icon' => 'fa-instagram', 'url' => 'mobile/online_store/instagram', 'perm' => 'online_store_view', 'color' => 'purple'],
				['title' => 'FAQs', 'desc' => 'Frequently asked questions', 'icon' => 'fa-question-circle', 'url' => 'mobile/online_store/faqs', 'perm' => 'online_store_view', 'color' => 'yellow'],
				['title' => 'Analytics', 'desc' => 'Store traffic & sales', 'icon' => 'fa-bar-chart', 'url' => 'mobile/online_store/analytics', 'perm' => 'online_store_view', 'color' => 'blue'],
				['title' => 'Store Settings', 'desc' => 'Configure online store', 'icon' => 'fa-cog', 'url' => 'mobile/online_store/settings', 'perm' => 'online_store_view', 'color' => 'primary'],
			],
			'Marketing' => (
					function_exists('marketing_menu_items')
						? array_map(function($item){
							return [
								'title' => $item['title'],
								'desc'  => $item['desc'],
								'icon'  => $item['icon'],
								'url'   => isset($item['url_mobile']) ? $item['url_mobile'] : (isset($item['url_desktop']) ? $item['url_desktop'] : ''),
								'perm'  => isset($item['perm']) ? $item['perm'] : '',
								'feature' => isset($item['feature']) ? $item['feature'] : null,
								'color' => isset($item['color']) ? $item['color'] : 'blue',
							];
						}, marketing_menu_items())
					: [
						['title' => 'Create Customer Coupon', 'desc' => 'Generate a customer coupon', 'icon' => 'fa-plus-square', 'url' => 'mobile/customer_coupon/generate', 'perm' => 'customerCouponAdd', 'color' => 'primary'],
						['title' => 'Customer Coupons List', 'desc' => 'All customer coupons', 'icon' => 'fa-list', 'url' => 'mobile/customer_coupon', 'perm' => 'customerCouponView', 'color' => 'blue'],
						['title' => 'Create Coupon', 'desc' => 'Create a discount coupon', 'icon' => 'fa-plus-square', 'url' => 'mobile/discount_coupon/add', 'perm' => 'discountCouponAdd', 'color' => 'primary'],
						['title' => 'Coupons Master', 'desc' => 'Manage all coupons', 'icon' => 'fa-list', 'url' => 'mobile/discount_coupon/view', 'perm' => 'discountCouponView', 'color' => 'blue'],
					]
				),
			'Customers' => [
				['title' => 'Customers', 'desc' => 'Customer directory', 'icon' => 'fa-users', 'url' => 'mobile/customers', 'perm' => 'customers_view', 'color' => 'purple'],
				['title' => 'Add Customer', 'desc' => 'Register a new customer', 'icon' => 'fa-user-plus', 'url' => 'mobile/add_customer', 'perm' => 'customers_add', 'color' => 'purple'],
			],
			'Suppliers' => [
				['title' => 'Suppliers', 'desc' => 'Supplier directory', 'icon' => 'fa-truck', 'url' => 'mobile/suppliers', 'perm' => 'suppliers_view', 'color' => 'yellow'],
				['title' => 'Add Supplier', 'desc' => 'Register a new supplier', 'icon' => 'fa-plus-circle', 'url' => 'mobile/add_supplier', 'perm' => 'suppliers_add', 'color' => 'yellow'],
			],
			'Finance' => [
				['title' => 'New Account', 'desc' => 'Create a new account', 'icon' => 'fa-plus-square', 'url' => 'mobile/finance/accounts/form', 'perm' => 'accounts_add', 'color' => 'green'],
				['title' => 'Account List', 'desc' => 'View accounts & books', 'icon' => 'fa-list', 'url' => 'mobile/finance/accounts', 'perm' => 'accounts_view', 'color' => 'blue'],
				['title' => 'Money Transfers', 'desc' => 'Transfer between accounts', 'icon' => 'fa-exchange', 'url' => 'mobile/finance/money_transfers', 'perm' => 'money_transfer_view', 'color' => 'purple'],
				['title' => 'Deposits', 'desc' => 'Record cash deposits', 'icon' => 'fa-download', 'url' => 'mobile/finance/money_deposits', 'perm' => 'money_deposit_view', 'color' => 'orange'],
				['title' => 'Cash Transactions', 'desc' => 'Sales, purchase & expense payments', 'icon' => 'fa-money', 'url' => 'mobile/finance/cash_transactions', 'perm' => 'accounts_view', 'color' => 'teal'],
				['title' => 'Tills / Cash-in-Hand', 'desc' => 'Tills and cash balances', 'icon' => 'fa-inbox', 'url' => 'mobile/finance/tills', 'perm' => 'tills_view', 'color' => 'yellow'],
				['title' => 'Expense List', 'desc' => 'Track business expenses', 'icon' => 'fa-credit-card', 'url' => 'mobile/finance/expenses', 'perm' => 'expense_view', 'color' => 'red'],
			],
			'Reports' => [
				['title' => 'Quotation Report', 'desc' => 'Quotation status summary', 'icon' => 'fa-quote-left', 'url' => 'mobile/quotation_report', 'perm' => 'quotation_view', 'color' => 'purple'],
				['title' => 'Profit & Loss Report', 'desc' => 'Income, expenses & profit', 'icon' => 'fa-line-chart', 'url' => 'mobile/report/profit_loss', 'perm' => 'profit_report', 'color' => 'blue'],
				['title' => 'Daily Business Summary', 'desc' => 'Daily sales overview', 'icon' => 'fa-calendar', 'url' => 'mobile/report/sales_summary', 'perm' => 'sales_summary_report', 'color' => 'green'],
				['title' => 'Receivables Aging', 'desc' => 'Customer dues by age', 'icon' => 'fa-hourglass', 'url' => 'mobile/report/receivables_aging', 'perm' => 'receivables_aging_report', 'color' => 'red'],
				['title' => 'Inventory Aging', 'desc' => 'Dead and slow stock', 'icon' => 'fa-cubes', 'url' => 'mobile/report/inventory_aging', 'perm' => 'inventory_aging_report', 'color' => 'teal'],
				['title' => 'Cash Flow Statement', 'desc' => 'Cash in vs out', 'icon' => 'fa-money', 'url' => 'mobile/report/cash_flow', 'perm' => 'cash_flow_report', 'color' => 'green'],
				['title' => 'Best Seller by Attribute', 'desc' => 'Best selling variants', 'icon' => 'fa-star', 'url' => 'mobile/report/variant_attribute', 'perm' => 'variant_attribute_report', 'color' => 'purple'],
				['title' => 'Sell-Through Report', 'desc' => 'Sell-through rates', 'icon' => 'fa-line-chart', 'url' => 'mobile/report/sell_through', 'perm' => 'sell_through_report', 'color' => 'orange'],
				['title' => 'Reorder Suggestions', 'desc' => 'Items to reorder', 'icon' => 'fa-shopping-basket', 'url' => 'mobile/report/reorder_suggestion', 'perm' => 'reorder_suggestion_report', 'color' => 'yellow'],
				['title' => 'Sales & Payment Report', 'desc' => 'Invoices and payments', 'icon' => 'fa-credit-card', 'url' => 'mobile/report/sales_and_payments', 'perm' => 'sales_report', 'color' => 'primary'],
				['title' => 'Tax Report', 'desc' => 'Sales tax summary', 'icon' => 'fa-percent', 'url' => 'mobile/report/sales_tax', 'perm' => 'sales_tax_report', 'color' => 'red'],
				['title' => 'Sales Report', 'desc' => 'Invoices, totals & dues', 'icon' => 'fa-shopping-cart', 'url' => 'mobile/report/sales', 'perm' => 'sales_report', 'color' => 'primary'],
				['title' => 'Sales Return', 'desc' => 'Returned sales', 'icon' => 'fa-undo', 'url' => 'mobile/report/sales_return', 'perm' => 'sales_return_report', 'color' => 'orange'],
				['title' => 'Purchase Report', 'desc' => 'Purchase invoices', 'icon' => 'fa-cart-arrow-down', 'url' => 'mobile/report/purchase', 'perm' => 'purchase_report', 'color' => 'green'],
				['title' => 'Purchase Return', 'desc' => 'Purchase returns', 'icon' => 'fa-reply', 'url' => 'mobile/report/purchase_return', 'perm' => 'purchase_return_report', 'color' => 'red'],
				['title' => 'Expense Report', 'desc' => 'Business expenses', 'icon' => 'fa-money', 'url' => 'mobile/report/expense', 'perm' => 'expense_report', 'color' => 'yellow'],
				['title' => 'Stock Report', 'desc' => 'Item & brand wise stock', 'icon' => 'fa-cubes', 'url' => 'mobile/report/stock', 'perm' => 'stock_report', 'color' => 'teal'],
				['title' => 'Item Sales Report', 'desc' => 'Best selling items', 'icon' => 'fa-barcode', 'url' => 'mobile/report/item_sales', 'perm' => 'item_sales_report', 'color' => 'purple'],
				['title' => 'Return Items', 'desc' => 'Items returned by customers', 'icon' => 'fa-refresh', 'url' => 'mobile/report/return_item', 'perm' => 'return_items_report', 'color' => 'orange'],
			],
			'Team' => [
				['title' => 'Staff', 'desc' => 'Team attendance', 'icon' => 'fa-id-badge', 'url' => 'mobile/staff', 'perm' => 'users_view', 'color' => 'yellow'],
				['title' => 'Users', 'desc' => 'Create & manage staff', 'icon' => 'fa-user-cog', 'url' => 'mobile/users', 'perm' => 'users_view', 'color' => 'purple'],
			],
			'Operations' => [
			['title' => 'Operations Hub', 'desc' => 'Workflows & tools', 'icon' => 'fa-cogs', 'url' => 'mobile/operations', 'perm' => null, 'color' => 'primary'],
		],
		];

		if(!mp_feature_enabled('online_store')){
			unset($menu_groups['Online Store']);
		} elseif(!mp_feature_enabled('qr_ordering') && isset($menu_groups['Online Store'])){
			$menu_groups['Online Store'] = array_values(array_filter($menu_groups['Online Store'], function($item){
				return $item['title'] !== 'QR Codes';
			}));
		}

		$data['menu_groups'] = [];
		foreach($menu_groups as $group => $items){
			$visible = [];
			foreach($items as $item){
				if(!empty($item['feature']) && !mp_feature_enabled($item['feature'])) continue;
			if(empty($item['perm']) || $this->permissions($item['perm'])){
					$visible[] = $item;
				}
			}
			if(!empty($visible)){
				$data['menu_groups'][$group] = $visible;
			}
		}

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/more', $data);
	}

	public function marketing()
	{
		$data = $this->data;
		$data['page_title'] = 'Marketing';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['branch_name'] = get_store_name();

		$data['marketing_items'] = function_exists('marketing_menu_items') ? marketing_menu_items() : [];

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/marketing', $data);
	}

	public function operations()
	{
		$data = $this->data;
		$data['page_title'] = 'Operations';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';

		$operations = [
			['title' => 'Custom Orders', 'desc' => 'Orders, quotes & deposits', 'icon' => 'fa-pencil-square-o', 'url' => 'operations/custom_orders', 'perm' => 'custom_orders_view', 'feature' => 'custom_orders', 'color' => 'primary'],
			['title' => 'Production', 'desc' => 'Batches & production schedule', 'icon' => 'fa-industry', 'url' => 'operations/production', 'perm' => 'production_batches_view', 'feature' => 'production_workflow', 'color' => 'orange'],
			['title' => 'Recipes', 'desc' => 'Kitchen & bakery recipes', 'icon' => 'fa-cutlery', 'url' => 'operations/recipes', 'perm' => 'recipes_view', 'feature' => 'recipe_tracking', 'color' => 'teal'],
			['title' => 'Memberships', 'desc' => 'Plans & customer members', 'icon' => 'fa-id-card', 'url' => 'operations/memberships', 'perm' => 'memberships_view', 'feature' => 'memberships', 'color' => 'purple'],
			['title' => 'Kitchen', 'desc' => 'Order status & kitchen display', 'icon' => 'fa-utensils', 'url' => 'operations/kitchen', 'perm' => 'store_view', 'feature' => 'kitchen_workflow', 'color' => 'orange'],
			['title' => 'Laundry', 'desc' => 'Laundry orders & pickup', 'icon' => 'fa-tint', 'url' => 'operations/laundry', 'perm' => 'store_view', 'feature' => 'laundry_workflow', 'color' => 'blue'],
			['title' => 'Table Management', 'desc' => 'Tables & dining status', 'icon' => 'fa-table', 'url' => 'operations/table_management', 'perm' => 'store_view', 'feature' => 'table_management', 'color' => 'green'],
			['title' => 'Staff Assignment', 'desc' => 'Assign staff to services', 'icon' => 'fa-user-md', 'url' => 'operations/staff_assignment', 'perm' => 'store_view', 'feature' => 'staff_assignment', 'color' => 'yellow'],
			['title' => 'Staff Commission', 'desc' => 'Track staff commissions', 'icon' => 'fa-percent', 'url' => 'operations/staff_commission', 'perm' => 'store_view', 'feature' => 'staff_commission', 'color' => 'yellow'],
			['title' => 'Delivery', 'desc' => 'Schedule & track deliveries', 'icon' => 'fa-truck', 'url' => 'operations/delivery_scheduling', 'perm' => 'store_view', 'feature' => 'delivery_scheduling', 'color' => 'purple'],
			['title' => 'Warranty Lookup', 'desc' => 'Check product warranty', 'icon' => 'fa-shield', 'url' => 'operations/warranty_lookup', 'perm' => 'store_view', 'feature' => 'warranty_tracking', 'color' => 'blue'],
			['title' => 'Treatment Notes', 'desc' => 'Service & treatment history', 'icon' => 'fa-heartbeat', 'url' => 'operations/treatment_notes', 'perm' => 'treatment_notes_view', 'feature' => 'treatment_notes', 'color' => 'red'],
			['title' => 'Medical Notes', 'desc' => 'Patient prescriptions', 'icon' => 'fa-file-medical-o', 'url' => 'operations/medical_notes', 'perm' => 'medical_notes_view', 'feature' => 'medical_notes', 'color' => 'red'],
			['title' => 'Public Catalogue', 'desc' => 'Storefront catalogue settings', 'icon' => 'fa-globe', 'url' => 'mobile/public_catalogue_settings', 'perm' => 'store_view', 'feature' => 'public_catalogue', 'color' => 'green'],
		];

		$data['operations'] = [];
		foreach($operations as $op){
			if(!empty($op['feature']) && !mp_feature_enabled($op['feature'])) continue;
			if(!empty($op['perm']) && !$this->permissions($op['perm'])) continue;
			$data['operations'][] = $op;
		}

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/operations', $data);
	}

	public function public_catalogue_settings()
	{
		$this->permission_check('store_view');
		$data = $this->data;
		$data['page_title'] = 'Public Catalogue';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$store_id = get_current_store_id();

		$this->load->model('business_profile_model', 'bp_model');
		$profile = $this->bp_model->get_profile($store_id);
		$settings = [];
		if (!empty($profile['industry_settings_json'])) {
			$decoded = json_decode($profile['industry_settings_json'], true);
			if (is_array($decoded) && isset($decoded['catalogue'])) {
				$settings = $decoded['catalogue'];
			}
		}

		$this->load->model('Storefront_model', 'storefront');
		$sf = $this->storefront->getSettings($store_id);
		$data['settings'] = $settings;
		$data['store_slug'] = ($sf ? $sf->store_slug : '');

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/public_catalogue_settings', $data);
	}

	public function business_profile()
	{
		$this->permission_check('store_edit');
		$data = $this->data;
		$data['page_title'] = 'Business Profile';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$store_id = get_current_store_id();

		$this->load->model('business_profile_model', 'bp_model');
		$data['profile'] = $this->bp_model->get_profile($store_id);
		$data['presets'] = $this->bp_model->get_available_presets();
		$data['business_types'] = mp_get_business_types();
		$data['business_models'] = mp_get_business_models();
		$data['feature_flags'] = mp_get_feature_flags();
		$data['label_defaults'] = mp_get_label_defaults();
		$data['workflow_templates'] = mp_get_workflow_templates();
		$data['dashboard_templates'] = mp_get_dashboard_templates();
		$this->load->model('storefront_model');
		$data['storefront_themes'] = $this->storefront_model->getThemesByIndustryForStore($data['profile']['industry_type'] ?? null, true);

		$data['current_flags'] = [];
		if (!empty($data['profile']['feature_flags_json'])) {
			$decoded = json_decode($data['profile']['feature_flags_json'], true);
			if (is_array($decoded)) { $data['current_flags'] = $decoded; }
		}
		$data['current_labels'] = [];
		if (!empty($data['profile']['label_overrides_json'])) {
			$decoded = json_decode($data['profile']['label_overrides_json'], true);
			if (is_array($decoded)) { $data['current_labels'] = $decoded; }
		}

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/business_profile', $data);
	}

	public function online_store($page = null, $id = null)
	{
		if(!mp_feature_enabled('online_store') || (!$this->permissions('online_store_view') && !is_admin() && !is_store_admin() && $this->session->userdata('role_id') != 1)){
			$this->show_access_denied_page();
			return;
		}

		$data = $this->data;
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['back_url'] = base_url('mobile/online_store');
		$data['can_edit'] = $this->permissions('online_store_edit') || is_admin() || is_store_admin() || $this->session->userdata('role_id') == 1;
		$store_id = get_current_store_id();

		$menu_items = [
			['title' => 'Store Dashboard', 'icon' => 'fa-dashboard', 'url' => 'mobile/online_store/dashboard'],
			['title' => 'Orders', 'icon' => 'fa-shopping-cart', 'url' => 'mobile/online_store/orders'],
			['title' => 'Online Products', 'icon' => 'fa-cube', 'url' => 'mobile/online_store/products'],
			['title' => 'Services', 'icon' => 'fa-wrench', 'url' => 'mobile/online_store/services'],
			['title' => 'QR Codes', 'icon' => 'fa-qrcode', 'url' => 'mobile/online_store/qr_codes'],
			['title' => 'Appearance', 'icon' => 'fa-paint-brush', 'url' => 'mobile/online_store/appearance'],
			['title' => 'Banners', 'icon' => 'fa-image', 'url' => 'mobile/online_store/banners'],
			['title' => 'Homepage Builder', 'icon' => 'fa-th-large', 'url' => 'mobile/online_store/homepage_builder'],
			['title' => 'Domains', 'icon' => 'fa-globe', 'url' => 'mobile/online_store/domains'],
			['title' => 'Brands', 'icon' => 'fa-copyright', 'url' => 'mobile/online_store/brands'],
			['title' => 'Testimonials', 'icon' => 'fa-comments', 'url' => 'mobile/online_store/testimonials'],
			['title' => 'Instagram', 'icon' => 'fa-instagram', 'url' => 'mobile/online_store/instagram'],
			['title' => 'FAQs', 'icon' => 'fa-question-circle', 'url' => 'mobile/online_store/faqs'],
			['title' => 'Analytics', 'icon' => 'fa-bar-chart', 'url' => 'mobile/online_store/analytics'],
			['title' => 'Store Settings', 'icon' => 'fa-cog', 'url' => 'mobile/online_store/settings'],
		];

		if(!mp_feature_enabled('qr_ordering')){
			$menu_items = array_values(array_filter($menu_items, function($item){
				return $item['title'] !== 'QR Codes';
			}));
		}

		if($page === null){
			$data['page_title'] = 'Online Store';
			$data['menu_items'] = $menu_items;

			header('Cache-Control: no-cache, must-revalidate, max-age=0');
			header('Pragma: no-cache');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			$this->load->view('mobile/online_store', $data);
			return;
		}

		$this->load->model('storefront_model');

		$page_titles = [
			'dashboard' => 'Store Dashboard',
			'orders' => 'Orders',
			'products' => 'Online Products',
			'services' => 'Services',
			'qr_codes' => 'QR Codes',
			'appearance' => 'Appearance',
			'banners' => 'Banners',
			'homepage_builder' => 'Homepage Builder',
			'domains' => 'Domains',
			'brands' => 'Brands',
			'testimonials' => 'Testimonials',
			'instagram' => 'Instagram',
			'faqs' => 'FAQs',
			'analytics' => 'Analytics',
			'settings' => 'Store Settings',
		];

		$title = $page_titles[$page] ?? 'Online Store';
		$data['page_title'] = $title;
		$data['current_page'] = $page;

		$view = 'mobile/online_store/coming_soon';

		switch($page){
			case 'dashboard':
				$period = $this->input->get('period', TRUE) ?: 'today';
				$from = $this->input->get('from', TRUE) ?: '';
				$to = $this->input->get('to', TRUE) ?: '';
				switch($period){
					case 'yesterday':
						$from = $to = date('Y-m-d', strtotime('-1 day'));
						break;
					case 'week':
						$from = date('Y-m-d', strtotime('-6 days'));
						$to = date('Y-m-d');
						break;
					case 'month':
						$from = date('Y-m-d', strtotime('-29 days'));
						$to = date('Y-m-d');
						break;
					case 'custom':
						if(!$from || !$to){ $from = $to = date('Y-m-d'); $period = 'today'; }
						break;
					default:
						$from = $to = date('Y-m-d');
						$period = 'today';
				}
				$data['period'] = $period;
				$data['from'] = $from;
				$data['to'] = $to;

				$stats = $this->db->query("SELECT COUNT(*) as total_orders, COALESCE(SUM(grand_total),0) as total_revenue, SUM(CASE WHEN order_status='pending' THEN 1 ELSE 0 END) as pending_orders, SUM(CASE WHEN payment_status='paid' THEN 1 ELSE 0 END) as paid_orders FROM db_online_orders WHERE store_id=? AND status=1 AND DATE(created_at) BETWEEN ? AND ?", [$store_id, $from, $to])->row();
				$data['stats'] = [
					'total_orders' => (int)$stats->total_orders,
					'total_revenue' => (float)$stats->total_revenue,
					'pending_orders' => (int)$stats->pending_orders,
					'paid_orders' => (int)$stats->paid_orders,
				];
				$data['recent_orders'] = $this->db->where('store_id', $store_id)->where('status', 1)->where('DATE(created_at) >=', $from)->where('DATE(created_at) <=', $to)->order_by('id', 'desc')->limit(10)->get('db_online_orders')->result();
				$data['top_products'] = $this->storefront_model->getTopOnlineProducts(null, 5);
				$view = 'mobile/online_store/dashboard';
				break;
			case 'orders':
				$status = $this->input->get('status', TRUE) ?: null;
				$data['current_status'] = $status;
				$data['orders'] = $this->storefront_model->getOrders(null, $status, 50, 0);
				$view = 'mobile/online_store/orders';
				break;
			case 'products':
			case 'products_online':
				$search = trim($this->input->get('search', TRUE) ?: '');
				$data['search'] = $search;
				$this->db->select('a.id, a.item_name, a.item_image, a.stock, a.sales_price, a.online_price, a.publish_online, a.is_featured, a.status, b.category_name');
				$this->db->from('db_items a');
				$this->db->join('db_category b', 'b.id=a.category_id', 'left');
				$this->db->where('a.store_id', $store_id);
				$this->db->where('a.service_bit', 0);
				$this->db->where("(a.item_group IS NULL OR a.item_group='Single')");
				if($search){
					$this->db->group_start();
					$this->db->like('a.item_name', $search);
					$this->db->or_like('a.item_code', $search);
					$this->db->group_end();
				}
				$this->db->order_by('a.id', 'desc');
				$this->db->limit(100);
				$data['products'] = $this->db->get()->result();
				$view = 'mobile/online_store/products';
				break;
			case 'services':
				$search = $this->input->get('search', TRUE) ?: '';
				$data['search'] = $search;
				$data['services'] = $this->storefront_model->getOnlineServices(null, null, $search, 50, 0);
				$view = 'mobile/online_store/services';
				break;
			case 'banners':
				$data['banners'] = $this->db->table_exists('db_storefront_banners') ? $this->storefront_model->getBanners() : [];
				$view = 'mobile/online_store/banners';
				break;
			case 'banner_form':
				if(!$data['can_edit']){ $this->show_access_denied_page(); return; }
				$banner_id = (int)$id;
				$data['banner'] = $banner_id ? $this->storefront_model->getBanner($banner_id, $store_id) : null;
				$data['page_title'] = $data['banner'] ? 'Edit Banner' : 'Add Banner';
				$data['back_url'] = base_url('mobile/online_store/banners');
				$view = 'mobile/online_store/banner_form';
				break;
			case 'delete_banner':
				if(!$data['can_edit']){ $this->show_access_denied_page(); return; }
				$banner_id = (int)$id;
				if($banner_id){
					$this->storefront_model->deleteBanner($banner_id, $store_id);
					$this->session->set_flashdata('success', 'Banner deleted.');
				}
				redirect('mobile/online_store/banners');
				return;
			case 'domains':
				$data['domains'] = $this->db->table_exists('db_storefront_domains') ? $this->storefront_model->getDomains() : [];
				$data['settings'] = $this->storefront_model->getSettings($store_id);
				$view = 'mobile/online_store/domains';
				break;
			case 'brands':
				$data['brands'] = $this->db->table_exists('db_storefront_brands') ? $this->storefront_model->getStorefrontBrands($store_id, false) : [];
				$view = 'mobile/online_store/brands';
				break;
			case 'testimonials':
				$data['testimonials'] = $this->db->table_exists('db_storefront_testimonials') ? $this->storefront_model->getStorefrontTestimonials($store_id, false) : [];
				$data['settings'] = $this->storefront_model->getSettings($store_id);
				$view = 'mobile/online_store/testimonials';
				break;
			case 'instagram':
				$data['posts'] = $this->db->table_exists('db_storefront_instagram') ? $this->storefront_model->getStorefrontInstagram($store_id, false) : [];
				$view = 'mobile/online_store/instagram';
				break;
			case 'qr_codes':
				$data['qr_codes'] = $this->db->table_exists('db_qr_codes') ? $this->storefront_model->getQrCodes() : [];
				$data['products'] = $this->db->table_exists('db_items') ? $this->storefront_model->getOnlineProducts() : [];
				$data['services'] = $this->db->table_exists('db_items') ? $this->storefront_model->getOnlineServices() : [];
				$data['categories'] = $this->db->table_exists('db_items') ? $this->storefront_model->getCategoriesWithItems() : [];
				$view = 'mobile/online_store/qr_codes';
				break;
			case 'faqs':
				if(!$data['can_edit']){ $this->show_access_denied_page(); return; }
				$data['faqs'] = $this->storefront_model->getStorefrontFaqs($store_id, false);
				$view = 'mobile/online_store/faqs';
				break;
			case 'faq_form':
				if(!$data['can_edit']){ $this->show_access_denied_page(); return; }
				$faq_id = (int)$this->input->get('id', TRUE);
				$data['faq'] = $faq_id ? $this->db->where('id', $faq_id)->where('store_id', $store_id)->get('db_storefront_faqs')->row() : null;
				$view = 'mobile/online_store/faq_form';
				break;
			case 'order':
				$order_id = (int)$id;
				$order = $order_id ? $this->storefront_model->getOrder($order_id) : null;
				if(!$order){ show_404(); exit; }
				$data['order'] = $order;
				$data['items'] = $this->storefront_model->getOrderItems($order_id);
				$data['back_url'] = base_url('mobile/online_store/orders');
				$data['page_title'] = 'Order #' . $order->order_code;
				$view = 'mobile/online_store/order';
				break;
			case 'banner_form':
				if(!$data['can_edit']){ $this->show_access_denied_page(); return; }
				$banner_id = (int)$id;
				$data['banner'] = $banner_id ? $this->storefront_model->getBanner($banner_id, $store_id) : null;
				$data['page_title'] = $data['banner'] ? 'Edit Banner' : 'Add Banner';
				$data['back_url'] = base_url('mobile/online_store/banners');
				$view = 'mobile/online_store/banner_form';
				break;
			case 'appearance':
				if(!$data['can_edit']){ $this->show_access_denied_page(); return; }
				$data['settings'] = $this->storefront_model->getSettings($store_id);
				$profile = mp_get_store_profile($store_id);
				$data['themes'] = $this->storefront_model->getThemesByIndustryForStore($profile['industry_type'] ?? null, true);
				$data['current_theme'] = $this->storefront_model->getTheme($data['settings']->theme_id ?? 0);
				$view = 'mobile/online_store/appearance';
				break;
			case 'settings':
				if(!$data['can_edit']){ $this->show_access_denied_page(); return; }
				$data['settings'] = $this->storefront_model->getSettings($store_id);
				$data['is_saved'] = $this->db->where('store_id', $store_id)->get('db_storefront_settings')->num_rows() > 0;
				$data['store'] = get_store_details($store_id);
				$data['categories'] = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_category')->result();
				$data['warehouses'] = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_warehouse')->result();
				$this->load->model('paystack_model','paystack');
				$data['paystack_enabled'] = $this->paystack->is_enabled();
				$view = 'mobile/online_store/settings';
				break;
			case 'homepage_builder':
				if(!$data['can_edit']){ $this->show_access_denied_page(); return; }
				$sections = $this->storefront_model->getHomepageSections($store_id);
				if(empty($sections)){
					$this->storefront_model->resetHomepageSections($store_id);
					$sections = $this->storefront_model->getHomepageSections($store_id);
				}
				$data['homepage_sections'] = $sections;
				$data['settings'] = $this->storefront_model->getSettings($store_id);
				$view = 'mobile/online_store/homepage_builder';
				break;
			case 'analytics':
				if(!$data['can_edit']){ $this->show_access_denied_page(); return; }
				$filter = $this->input->get('filter', TRUE) ?: 'month';
				$customStart = $this->input->get('start', TRUE);
				$customEnd = $this->input->get('end', TRUE);
				$startDate = $endDate = null; $rangeLabel = '';
				switch($filter){
					case 'today':
						$startDate = date('Y-m-d 00:00:00'); $endDate = date('Y-m-d 23:59:59'); $rangeLabel = 'Today'; break;
					case 'week':
						$startDate = date('Y-m-d 00:00:00', strtotime('monday this week'));
						$endDate = date('Y-m-d 23:59:59', strtotime('sunday this week'));
						$rangeLabel = 'This Week'; break;
					case 'month':
						$startDate = date('Y-m-01 00:00:00'); $endDate = date('Y-m-t 23:59:59'); $rangeLabel = date('F Y'); break;
					case 'year':
						$startDate = date('Y-01-01 00:00:00'); $endDate = date('Y-12-31 23:59:59'); $rangeLabel = date('Y'); break;
					case 'custom':
						$startDate = $customStart ? date('Y-m-d 00:00:00', strtotime($customStart)) : date('Y-m-d 00:00:00', strtotime('-30 days'));
						$endDate = $customEnd ? date('Y-m-d 23:59:59', strtotime($customEnd)) : date('Y-m-d 23:59:59');
						$rangeLabel = date('M j, Y', strtotime($startDate)) . ' - ' . date('M j, Y', strtotime($endDate));
						break;
					default:
						$startDate = date('Y-m-01 00:00:00'); $endDate = date('Y-m-t 23:59:59'); $rangeLabel = date('F Y'); $filter = 'month';
				}
				$chartData = []; $chartLabels = []; $chartType = 'day';
				if($filter == 'today'){
					$chartType = 'hour';
					$hourly = $this->storefront_model->getVisitsByHour($store_id, date('Y-m-d'));
					for($h=0; $h<24; $h++){
						$found = null;
						foreach($hourly as $row){ if((int)$row->hour === $h){ $found = $row; break; } }
						$chartLabels[] = sprintf('%02d:00', $h); $chartData[] = (int)($found->visits ?? 0);
					}
				}elseif($filter == 'year'){
					$chartType = 'month';
					$monthly = $this->storefront_model->getVisitsByMonth($store_id, $startDate, $endDate);
					for($m=1; $m<=12; $m++){
						$monthKey = date('Y') . '-' . sprintf('%02d', $m); $found = null;
						foreach($monthly as $row){ if($row->month == $monthKey){ $found = $row; break; } }
						$chartLabels[] = date('M', mktime(0,0,0,$m,1)); $chartData[] = (int)($found->visits ?? 0);
					}
				}else{
					$chartType = 'day';
					$daily = $this->storefront_model->getDailyVisits($store_id, $startDate, $endDate);
					$periodStart = new DateTime($startDate); $periodEnd = new DateTime($endDate);
					$interval = new DateInterval('P1D');
					$period = new DatePeriod($periodStart, $interval, $periodEnd->modify('+1 day'));
					foreach($period as $dt){
						$d = $dt->format('Y-m-d'); $found = null;
						foreach($daily as $row){ if($row->date == $d){ $found = $row; break; } }
						$chartLabels[] = $dt->format('j'); $chartData[] = (int)($found->visits ?? 0);
					}
				}
				$data['summary'] = $this->storefront_model->getAnalyticsSummary($store_id, $startDate, $endDate);
				$data['top_sources'] = $this->storefront_model->getTopSources($store_id, $startDate, $endDate);
				$data['top_pages'] = $this->storefront_model->getTopPages($store_id, $startDate, $endDate);
				$data['chart_labels'] = $chartLabels;
				$data['chart_data'] = $chartData;
				$data['chart_type'] = $chartType;
				$data['heatmap'] = $this->storefront_model->getHeatmapData($store_id, $startDate, $endDate);
				$data['devices'] = $this->storefront_model->getDeviceBreakdown($store_id, $startDate, $endDate);
				$data['search_terms'] = $this->storefront_model->getSearchTerms($store_id, $startDate, $endDate);
				$data['customers'] = $this->storefront_model->getCustomerVisits($store_id, $startDate, $endDate);
				$data['recent_visits'] = $this->storefront_model->getRecentVisits($store_id, 50);
				$data['filter'] = $filter;
				$data['range_label'] = $rangeLabel;
				$data['start_date'] = date('Y-m-d', strtotime($startDate));
				$data['end_date'] = date('Y-m-d', strtotime($endDate));
				$view = 'mobile/online_store/analytics';
				break;
			default:
				$data['menu_items'] = $menu_items;
				break;
		}

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view($view, $data);
	}

	public function reports()
	{
		$this->permission_check('dashboard_view');
		$data = $this->data;
		$data['page_title'] = 'Reports';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';

		$report_types = [
			['type' => 'approval_logs', 'title' => 'Approval Logs', 'desc' => 'Manager approval trail', 'icon' => 'fa-check-circle-o', 'perm' => 'approval_logs_view', 'color' => 'green', 'url' => 'mobile/approval_logs'],
			['type' => 'sales_return_payments', 'title' => 'Sales Return Payments', 'desc' => 'Refunds and return receipts', 'icon' => 'fa-undo', 'perm' => 'sales_payments_report', 'color' => 'orange'],
			['type' => 'stock_transfer', 'title' => 'Stock Transfer Report', 'desc' => 'Branch-to-branch transfers', 'icon' => 'fa-exchange', 'perm' => 'stock_transfer_report', 'color' => 'teal'],
			['type' => 'expired_items', 'title' => 'Expired Items Report', 'desc' => 'Expired and near-expiry stock', 'icon' => 'fa-files-o', 'perm' => 'expired_items_report', 'color' => 'red'],
			['type' => 'profit_loss', 'title' => 'Profit & Loss Report', 'desc' => 'Income, expenses & profit', 'icon' => 'fa-line-chart', 'perm' => 'profit_report', 'color' => 'blue'],
			['type' => 'sales_summary', 'title' => 'Daily Business Summary', 'desc' => 'Daily sales overview', 'icon' => 'fa-calendar', 'perm' => 'sales_summary_report', 'color' => 'green'],
			['type' => 'receivables_aging', 'title' => 'Receivables Aging', 'desc' => 'Customer dues by age', 'icon' => 'fa-hourglass', 'perm' => 'receivables_aging_report', 'color' => 'red'],
			['type' => 'inventory_aging', 'title' => 'Inventory Aging', 'desc' => 'Dead and slow stock', 'icon' => 'fa-cubes', 'perm' => 'inventory_aging_report', 'color' => 'teal'],
			['type' => 'cash_flow', 'title' => 'Cash Flow Statement', 'desc' => 'Cash in vs out', 'icon' => 'fa-money', 'perm' => 'cash_flow_report', 'color' => 'green'],
			['type' => 'variant_attribute', 'title' => 'Best Seller by Attribute', 'desc' => 'Best selling variants', 'icon' => 'fa-star', 'perm' => 'variant_attribute_report', 'color' => 'purple'],
			['type' => 'sell_through', 'title' => 'Sell-Through Report', 'desc' => 'Sell-through rates', 'icon' => 'fa-line-chart', 'perm' => 'sell_through_report', 'color' => 'orange'],
			['type' => 'reorder_suggestion', 'title' => 'Reorder Suggestions', 'desc' => 'Items to reorder', 'icon' => 'fa-shopping-basket', 'perm' => 'reorder_suggestion_report', 'color' => 'yellow'],
			['type' => 'sales_and_payments', 'title' => 'Sales & Payment Report', 'desc' => 'Invoices and payments', 'icon' => 'fa-credit-card', 'perm' => 'sales_report', 'color' => 'primary'],
			['type' => 'sales_tax', 'title' => 'Tax Report', 'desc' => 'Sales tax summary', 'icon' => 'fa-percent', 'perm' => 'sales_tax_report', 'color' => 'red'],
			['type' => 'sales', 'title' => 'Sales Report', 'desc' => 'Invoices, totals & dues', 'icon' => 'fa-shopping-cart', 'perm' => 'sales_report', 'color' => 'primary'],
			['type' => 'sales_return', 'title' => 'Sales Return', 'desc' => 'Returned sales', 'icon' => 'fa-undo', 'perm' => 'sales_return_report', 'color' => 'orange'],
			['type' => 'purchase', 'title' => 'Purchase Report', 'desc' => 'Purchase invoices', 'icon' => 'fa-cart-arrow-down', 'perm' => 'purchase_report', 'color' => 'green'],
			['type' => 'purchase_return', 'title' => 'Purchase Return', 'desc' => 'Purchase returns', 'icon' => 'fa-reply', 'perm' => 'purchase_return_report', 'color' => 'red'],
			['type' => 'expense', 'title' => 'Expense Report', 'desc' => 'Business expenses', 'icon' => 'fa-money', 'perm' => 'expense_report', 'color' => 'yellow'],
			['type' => 'stock', 'title' => 'Stock Report', 'desc' => 'Item & brand wise stock', 'icon' => 'fa-cubes', 'perm' => 'stock_report', 'color' => 'teal'],
			['type' => 'item_sales', 'title' => 'Item Sales Report', 'desc' => 'Best selling items', 'icon' => 'fa-barcode', 'perm' => 'item_sales_report', 'color' => 'purple'],
			['type' => 'return_item', 'title' => 'Return Items', 'desc' => 'Items returned by customers', 'icon' => 'fa-refresh', 'perm' => 'return_items_report', 'color' => 'orange'],
		];

		$data['report_types'] = [];
		foreach($report_types as $r){
			if($this->permissions($r['perm'])){
				$data['report_types'][] = $r;
			}
		}

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/reports', $data);
	}

	public function report($type = '')
	{
		$map = [
			'sales' => ['title' => 'Sales Report', 'perm' => 'sales_report', 'endpoint' => 'reports/show_sales_report'],
			'sales_return' => ['title' => 'Sales Return', 'perm' => 'sales_return_report', 'endpoint' => 'reports/show_sales_return_report'],
			'purchase' => ['title' => 'Purchase Report', 'perm' => 'purchase_report', 'endpoint' => 'reports/show_purchase_report'],
			'purchase_return' => ['title' => 'Purchase Return', 'perm' => 'purchase_return_report', 'endpoint' => 'reports/show_purchase_return_report'],
			'expense' => ['title' => 'Expense Report', 'perm' => 'expense_report', 'endpoint' => 'reports/show_expense_report'],
			'stock' => ['title' => 'Stock Report', 'perm' => 'stock_report', 'endpoint' => 'reports/get_stock_report'],
			'item_sales' => ['title' => 'Item Sales Report', 'perm' => 'item_sales_report', 'endpoint' => 'reports/show_item_sales_report'],
			'return_item' => ['title' => 'Return Items', 'perm' => 'return_items_report', 'endpoint' => 'reports/show_return_items_report'],
			'profit_loss' => ['title' => 'Profit & Loss', 'perm' => 'profit_report', 'endpoint' => 'reports/get_profit_loss_report'],
			'cash_flow' => ['title' => 'Cash Flow', 'perm' => 'cash_flow_report', 'endpoint' => 'reports/show_cash_flow_report'],
			'sales_summary' => ['title' => 'Daily Business Summary', 'perm' => 'sales_summary_report', 'endpoint' => 'reports/show_sales_summary_report', 'headers' => ['#', 'Item', 'Category', 'Qty']],
			'receivables_aging' => ['title' => 'Receivables Aging', 'perm' => 'receivables_aging_report', 'endpoint' => 'reports/show_receivables_aging_report', 'headers' => ['#', 'Customer', 'Mobile', '0-30 days', '31-60 days', '61-90 days', '90+ days', 'Total']],
			'inventory_aging' => ['title' => 'Inventory Aging', 'perm' => 'inventory_aging_report', 'endpoint' => 'reports/show_inventory_aging_report', 'headers' => ['#', 'Item', 'Category', 'Stock', 'Value', 'Last Sold', 'Days', 'Bucket']],
			'variant_attribute' => ['title' => 'Best Seller by Attribute', 'perm' => 'variant_attribute_report', 'endpoint' => 'reports/show_variant_attribute_report'],
			'sell_through' => ['title' => 'Sell-Through Report', 'perm' => 'sell_through_report', 'endpoint' => 'reports/show_sell_through_report'],
			'reorder_suggestion' => ['title' => 'Reorder Suggestions', 'perm' => 'reorder_suggestion_report', 'endpoint' => 'reports/show_reorder_suggestion_report'],
			'sales_and_payments' => ['title' => 'Sales & Payment Report', 'perm' => 'sales_report', 'endpoint' => 'reports/sales_and_payments_report'],
			'sales_tax' => ['title' => 'Tax Report', 'perm' => 'sales_tax_report', 'endpoint' => 'reports/show_sales_tax_report'],
			'sales_return_payments' => ['title' => 'Sales Return Payments', 'perm' => 'sales_payments_report', 'endpoint' => 'reports/show_sales_return_payments_report'],
			'stock_transfer' => ['title' => 'Stock Transfer Report', 'perm' => 'stock_transfer_report', 'endpoint' => 'reports/show_stock_transfer_report'],
			'expired_items' => ['title' => 'Expired Items Report', 'perm' => 'expired_items_report', 'endpoint' => 'reports/show_expired_items_report'],
		];
		if(!isset($map[$type])){
			show_404(); exit;
		}
		$this->permission_check($map[$type]['perm']);
		$data = $this->data;
		$data['page_title'] = $map[$type]['title'];
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$report = $map[$type];
		if(isset($report['headers']) && store_module() && is_admin()){
			array_splice($report['headers'], 1, 0, ['Store']);
		}
		$data['report'] = array_merge($report, ['type' => $type]);

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/report_viewer', $data);
	}

	public function users()
	{
		$this->permission_check('users_view');
		$data = $this->data;
		$data['page_title'] = 'Users';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$store_id = get_current_store_id();

		$this->db->select('a.*, b.role_name');
		$this->db->from('db_users a');
		$this->db->join('db_roles b', 'b.id = a.role_id', 'left');
		if(!is_admin()){
			$this->db->where('a.store_id', $store_id);
		}
		if(!is_admin() && !is_store_admin()){
			$this->db->where('a.role_id !=', store_admin_id());
		}
		$this->db->order_by('a.id', 'desc');
		$users = $this->db->get()->result();

		foreach($users as $u){
			$wh = $this->db->select('w.warehouse_name')
				->from('db_userswarehouses uw')
				->join('db_warehouse w', 'w.id = uw.warehouse_id', 'left')
				->where('uw.user_id', $u->id)
				->get()
				->result();
			$u->warehouse_names = array_column($wh, 'warehouse_name');
		}

		$user_used = get_user_usage();
		$user_limit = get_subscription_limit('user_limit');
		$data['user_used'] = $user_used;
		$data['user_limit'] = $user_limit;
		$data['at_limit'] = ($user_limit > 0 && $user_used >= $user_limit);
		$data['can_add'] = ($this->permissions('users_add') && !$data['at_limit']);
		$data['can_edit'] = $this->permissions('users_edit');
		$data['can_delete'] = $this->permissions('users_delete');
		$data['current_user_id'] = $this->session->userdata('inv_userid');
		$data['users'] = $users;

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/users', $data);
	}

	public function user_form($id = 0)
	{
		if($id == 0){
			$this->permission_check('users_add');
		} else {
			$this->permission_check('users_edit');
			if(!is_admin()){
				$user = $this->db->where('id', $id)->get('db_users')->row();
				if(!$user || $user->store_id != get_current_store_id()){
					show_error('Invalid Data', 403, 'You have entered Invalid Data!!');
					exit;
				}
			}
		}

		$this->load->model('users_model');
		$data = $this->data;
		$data['page_title'] = ($id == 0) ? 'Add User' : 'Edit User';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['q_id'] = $id;
		$data['store_id'] = get_current_store_id();

		if($id > 0){
			$d = $this->users_model->get_details($id);
			$data['username'] = $d['username'];
			$data['first_name'] = $d['first_name'];
			$data['last_name'] = $d['last_name'];
			$data['mobile'] = $d['mobile'];
			$data['email'] = $d['email'];
			$data['role_id'] = $d['role_id'];
			$data['profile_picture'] = $d['profile_picture'];
			$data['default_warehouse_id'] = $d['default_warehouse_id'];
			$data['store_id'] = $d['store_id'];
			$wh = $this->db->select('warehouse_id')->where('user_id', $id)->get('db_userswarehouses')->result();
			$data['user_warehouse_ids'] = array_column($wh, 'warehouse_id');
		} else {
			$data['username'] = '';
			$data['first_name'] = '';
			$data['last_name'] = '';
			$data['mobile'] = '';
			$data['email'] = '';
			$data['role_id'] = '';
			$data['profile_picture'] = '';
			$data['default_warehouse_id'] = '';
			$data['user_warehouse_ids'] = [];
		}

		if(warehouse_module() && warehouse_count() > 0){
			if(!is_admin() && !is_store_admin()){
				$allowed = $this->db->select('warehouse_id')->where('user_id', get_current_user_id())->get('db_userswarehouses')->result();
				$ids = array_column($allowed, 'warehouse_id');
				if(!empty($ids)){
					$this->db->where_in('id', $ids);
				} else {
					$this->db->where('id', 0);
				}
			}
			$data['warehouses'] = $this->db->where('store_id', get_current_store_id())->where('status', 1)->get('db_warehouse')->result();
		} else {
			$data['warehouses'] = [];
		}

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/user_form', $data);
	}

	public function finance($type = 'accounts', $action = 'list', $id = 0)
	{
		$valid = ['accounts','money_transfers','money_deposits','cash_transactions','tills','expenses'];
		if(!in_array($type, $valid)){
			show_404(); exit;
		}

		$store_id = get_current_store_id();
		$data = $this->data;
		$data['type'] = $type;
		$data['action'] = $action;
		$data['q_id'] = $id = (int)$id;
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';

		// Load accounts select for forms
		$data['accounts'] = $this->db->where('store_id', $store_id)->where('status', 1)->order_by('account_name')->get('ac_accounts')->result();

		// Permissions and data
		switch($type){
			case 'accounts':
				$data['page_title'] = ($action == 'form') ? (($id > 0) ? 'Edit Account' : 'New Account') : 'Account List';
				if($action == 'list'){
					$this->permission_check('accounts_view');
					$data['records'] = $this->db->where('store_id', $store_id)->where('delete_bit', 0)->order_by('account_name')->get('ac_accounts')->result();
				} else {
					$this->permission_check($id > 0 ? 'accounts_edit' : 'accounts_add');
					$this->load->model('accounts_model','accounts');
					if($id > 0){
						$detail = $this->accounts->get_details($id, []);
						$data = array_merge($data, $detail);
					} else {
						$data['account_code'] = $data['account_name'] = $data['note'] = '';
						$data['parent_id'] = '';
						$data['opening_balance'] = '0';
					}
				}
				break;

			case 'money_transfers':
				$data['page_title'] = ($action == 'form') ? (($id > 0) ? 'Edit Transfer' : 'New Transfer') : 'Money Transfers';
				if($action == 'list'){
					$this->permission_check('money_transfer_view');
					$data['records'] = $this->db->where('store_id', $store_id)->order_by('id','desc')->get('ac_moneytransfer')->result();
				} else {
					$this->permission_check($id > 0 ? 'money_transfer_edit' : 'money_transfer_add');
					$this->load->model('money_transfer_model','money_transfer');
					if($id > 0){
						$detail = $this->money_transfer->get_details($id, []);
						$data = array_merge($data, $detail);
					} else {
						$data['transfer_code'] = get_init_code('money_transfer');
						$data['transfer_date'] = date('Y-m-d');
						$data['reference_no'] = '';
						$data['debit_account_id'] = '';
						$data['credit_account_id'] = '';
						$data['amount'] = '';
						$data['note'] = '';
					}
				}
				break;

			case 'money_deposits':
				$data['page_title'] = ($action == 'form') ? (($id > 0) ? 'Edit Deposit' : 'New Deposit') : 'Deposits';
				if($action == 'list'){
					$this->permission_check('money_deposit_view');
					$data['records'] = $this->db->where('store_id', $store_id)->order_by('id','desc')->get('ac_moneydeposits')->result();
				} else {
					$this->permission_check($id > 0 ? 'money_deposit_edit' : 'money_deposit_add');
					$this->load->model('money_deposit_model','money_deposit');
					if($id > 0){
						$detail = $this->money_deposit->get_details($id, []);
						$data = array_merge($data, $detail);
					} else {
						$data['deposit_date'] = date('Y-m-d');
						$data['reference_no'] = '';
						$data['debit_account_id'] = '';
						$data['credit_account_id'] = '';
						$data['amount'] = '';
						$data['note'] = '';
					}
				}
				break;

			case 'cash_transactions':
				$this->permission_check('accounts_view');
				$data['page_title'] = 'Cash Transactions';
				$this->load->model('cash_transactions_model','cash');
				$_POST['from_date'] = date('Y-m-d', strtotime('-30 days'));
				$_POST['to_date'] = date('Y-m-d');
				$_POST['length'] = 100;
				$_POST['start'] = 0;
				$data['records'] = $this->cash->get_datatables();
				break;

			case 'tills':
				$data['page_title'] = ($action == 'form') ? (($id > 0) ? 'Edit Till' : 'New Till') : 'Tills / Cash-in-Hand';
				if($action == 'list'){
					$this->permission_check('tills_view');
					$this->load->model('tills_model','tills');
					$data['records'] = $this->tills->get_all();
				} else {
					$this->permission_check($id > 0 ? 'tills_edit' : 'tills_add');
					$this->load->model('tills_model','tills');
					$data['users'] = $this->db->where('status', 1)->where('store_id', $store_id)->get('db_users')->result();
					if($id > 0){
						$data['till'] = $this->tills->get($id);
						if(empty($data['till'])){ show_404(); exit; }
					} else {
						$data['till'] = (object)['till_name'=>'','cashier_user_id'=>'','account_id'=>'','is_default'=>0];
					}
				}
				break;

			case 'expenses':
				$data['page_title'] = ($action == 'form') ? (($id > 0) ? 'Edit Expense' : 'New Expense') : 'Expense List';
				if($action == 'list'){
					$this->permission_check('expense_view');
					$data['records'] = $this->db->select('e.*, c.category_name')->from('db_expense e')->join('db_expense_category c','c.id = e.category_id','left')->where('e.store_id', $store_id)->where('e.status', 1)->order_by('e.id','desc')->get()->result();
				} else {
					$this->permission_check($id > 0 ? 'expense_edit' : 'expense_add');
					$this->load->model('expense_model','expense');
					$this->load->model('expense_category_model','category');
					$data['categories'] = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_expense_category')->result();
					if($id > 0){
						$detail = $this->expense->get_details($id, []);
						$data = array_merge($data, $detail);
						$raw = $this->db->where('id', $id)->get('db_expense')->row();
						$data['expense_amt'] = $raw ? $raw->expense_amt : $data['expense_amt'];
						$data['payment_type'] = $data['payment_type'] ?? '';
						$data['account_id'] = $data['account_id'] ?? '';
					} else {
						$data['expense_date'] = date('Y-m-d');
						$data['category_id'] = '';
						$data['reference_no'] = '';
						$data['expense_for'] = '';
						$data['expense_amt'] = '';
						$data['payment_type'] = 'cash';
						$data['account_id'] = '';
						$data['note'] = '';
						$data['expense_code'] = get_init_code('expense');
					}
				}
				break;
		}

		$view = ($action == 'list') ? 'mobile/finance_list' : 'mobile/finance_form';
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view($view, $data);
	}

	public function save_till()
	{
		$id = (int) $this->input->post('id', TRUE);
		$this->permission_check($id > 0 ? 'tills_edit' : 'tills_add');
		$store_id = get_current_store_id();
		$data = array(
			'till_name'       => $this->input->post('till_name', TRUE),
			'cashier_user_id' => $this->input->post('cashier_user_id', TRUE),
			'account_id'      => $this->input->post('account_id', TRUE),
			'is_default'      => $this->input->post('is_default') ? 1 : 0,
			'store_id'        => $store_id,
		);
		$this->load->model('tills_model','tills');
		$save_id = $this->tills->save($data, $id);
		if($save_id){
			echo 'success';
		} else {
			echo 'failed';
		}
	}

	public function quotations()
	{
		$this->permission_check('quotation_view');
		$data = $this->data;
		$data['page_title'] = 'Quotations';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$store_id = get_current_store_id();
		$data['records'] = $this->db->select('q.*, c.customer_name')
			->from('db_quotation q')
			->join('db_customers c', 'c.id = q.customer_id', 'left')
			->where('q.store_id', $store_id)
			->order_by('q.id', 'desc')
			->get()
			->result();
		$this->load->view('mobile/quotations', $data);
	}

	public function quotation_form($id = 0)
	{
		$id = (int)$id;
		$is_update = $id > 0;
		$this->permission_check($is_update ? 'quotation_edit' : 'quotation_add');
		$data = $this->data;
		$data['page_title'] = $is_update ? 'Edit Quotation' : 'New Quotation';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$store_id = get_current_store_id();
		$data['customers'] = $this->db->where('store_id', $store_id)->where('status', 1)->order_by('customer_name')->get('db_customers')->result();
		$data['taxes'] = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_tax')->result();
		$data['items'] = $this->db->select('id, item_name, sales_price, tax_id')->where('store_id', $store_id)->where('status', 1)->where('item_group !=', 'Variants')->order_by('item_name')->limit(200)->get('db_items')->result();
		$data['warehouses'] = (warehouse_module() && warehouse_count() > 0)
			? $this->db->where('store_id', $store_id)->where('status', 1)->get('db_warehouse')->result()
			: [];

		if($is_update){
			$this->belong_to('db_quotation', $id);
			$q = $this->db->where('id', $id)->where('store_id', $store_id)->get('db_quotation')->row();
			if(!$q){ show_404(); exit; }
			$qi = $this->db->where('quotation_id', $id)->get('db_quotationitems')->row();
			$data['quotation_id'] = $id;
			$data['command'] = 'update';
			$data['quotation_date'] = $q->quotation_date;
			$data['expire_date'] = $q->expire_date;
			$data['customer_id'] = $q->customer_id;
			$data['reference_no'] = $q->reference_no;
			$data['warehouse_id'] = $q->warehouse_id;
			$data['quotation_note'] = $q->quotation_note;
			$data['other_charges_input'] = $q->other_charges_input ?: 0;
			$data['other_charges_tax_id'] = $q->other_charges_tax_id;
			$data['other_charges_amt'] = $q->other_charges_amt ?: 0;
			$data['rowcount'] = 1;
			if($qi){
				$data['item_id'] = $qi->item_id;
				$data['qty'] = $qi->quotation_qty;
				$data['price'] = $qi->price_per_unit;
				$data['tax_value'] = $qi->tax_per_unit;
				$data['tax_type'] = $qi->tax_type;
				$data['tax_id'] = $qi->tax_id;
				$data['discount'] = $qi->discount_input;
				$data['description'] = $qi->description;
			}
		} else {
			$data['quotation_id'] = '';
			$data['command'] = 'save';
			$data['quotation_date'] = date('Y-m-d');
			$data['expire_date'] = date('Y-m-d', strtotime('+7 days'));
			$data['customer_id'] = '';
			$data['reference_no'] = '';
			$data['warehouse_id'] = '';
			$data['quotation_note'] = '';
			$data['other_charges_input'] = 0;
			$data['other_charges_tax_id'] = '';
			$data['other_charges_amt'] = 0;
			$data['rowcount'] = 1;
		}
		$this->load->view('mobile/quotation_form', $data);
	}

	public function quotation_report()
	{
		$this->permission_check('quotation_view');
		$data = $this->data;
		$data['page_title'] = 'Quotation Report';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$store_id = get_current_store_id();
		$q = $this->db->where('store_id', $store_id)->get('db_quotation')->result();
		$data['total'] = count($q);
		$data['converted'] = count(array_filter($q, function($r){ return !empty($r->sales_status); }));
		$now = date('Y-m-d');
		$data['expired'] = count(array_filter($q, function($r) use ($now){ return empty($r->sales_status) && !empty($r->expire_date) && $r->expire_date < $now; }));
		$data['active'] = $data['total'] - $data['converted'] - $data['expired'];
		$data['records'] = $this->db->select('q.*, c.customer_name')
			->from('db_quotation q')
			->join('db_customers c', 'c.id = q.customer_id', 'left')
			->where('q.store_id', $store_id)
			->order_by('q.id', 'desc')
			->get()
			->result();
		$this->load->view('mobile/quotation_report', $data);
	}

	public function purchase_form($id = 0)
	{
		$id = (int) $id;
		$is_update = $id > 0;
		$this->permission_check($is_update ? 'purchase_edit' : 'purchase_add');
		$data = $this->data;
		$data['page_title'] = $is_update ? 'Edit Purchase' : 'New Purchase';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$store_id = get_current_store_id();

		$data['suppliers'] = $this->db->where('store_id', $store_id)->where('status', 1)->order_by('supplier_name')->get('db_suppliers')->result();
		$data['taxes'] = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_tax')->result();
		$data['items'] = $this->db->select('id, item_name, purchase_price, tax_id')->where('store_id', $store_id)->where('status', 1)->where('item_group !=', 'Variants')->order_by('item_name')->limit(200)->get('db_items')->result();
		$data['payment_modes'] = $this->db->where('store_id', $store_id)->where('status', 1)->order_by('name')->get('db_payment_modes')->result();
		$data['accounts'] = $this->db->where('store_id', $store_id)->where('delete_bit', 0)->order_by('account_name')->get('ac_accounts')->result();

		if(warehouse_module() && warehouse_count() > 1){
			if(!is_admin() && !is_store_admin()){
				$privileged = get_privileged_warehouses_ids();
				if(!empty($privileged)){
					$this->db->where("id in ($privileged)");
				} else {
					$this->db->where('id', 0);
				}
			}
			$data['warehouses'] = $this->db->where('store_id', $store_id)->where('status', 1)->order_by('warehouse_name')->get('db_warehouse')->result();
			$data['warehouse_id'] = '';
		} else {
			$data['warehouses'] = [];
			$data['warehouse_id'] = get_store_warehouse_id();
		}

		$data['purchase_id'] = 0;
		$data['command'] = 'save';
		$data['pur_date'] = date('Y-m-d');
		$data['purchase_status'] = 'Draft';
		$data['reference_no'] = '';
		$data['purchase_note'] = '';
		$data['supplier_id'] = '';
		$data['other_charges_input'] = '0';
		$data['other_charges_tax_id'] = '';
		$data['discount_to_all_input'] = '0';
		$data['discount_to_all_type'] = 'in_percentage';
		$data['payment_type'] = '';
		$data['cart_items'] = [];

		if($is_update){
			$this->belong_to('db_purchase', $id);
			$purchase = $this->db->where('id', $id)->where('store_id', $store_id)->get('db_purchase')->row();
			if(empty($purchase)){ show_404(); exit; }

			$data['purchase_id'] = $id;
			$data['command'] = 'update';
			$data['pur_date'] = $purchase->purchase_date;
			$data['purchase_status'] = $purchase->purchase_status;
			$data['reference_no'] = $purchase->reference_no;
			$data['purchase_note'] = $purchase->purchase_note;
			$data['supplier_id'] = $purchase->supplier_id;
			$data['warehouse_id'] = (warehouse_module() && warehouse_count() > 1) ? $purchase->warehouse_id : get_store_warehouse_id();
			$data['other_charges_input'] = store_number_format($purchase->other_charges_input, 0) ?: '0';
			$data['other_charges_tax_id'] = $purchase->other_charges_tax_id;
			$data['discount_to_all_input'] = $purchase->discount_to_all_input;
			$data['discount_to_all_type'] = $purchase->discount_to_all_type;

			$tax_map = [];
			foreach($data['taxes'] as $t){ $tax_map[$t->id] = $t->tax ?? 0; }

			$rows = $this->db->select('pi.*, i.item_name')
				->from('db_purchaseitems pi')
				->join('db_items i', 'i.id = pi.item_id', 'left')
				->where('pi.purchase_id', $id)
				->get()
				->result();

			foreach($rows as $r){
				$data['cart_items'][] = [
					'item_id' => $r->item_id,
					'name' => $r->item_name,
					'qty' => (float) $r->purchase_qty,
					'price' => (float) $r->price_per_unit,
					'tax_value' => (float) ($tax_map[$r->tax_id] ?? 0),
					'tax_id' => $r->tax_id,
					'tax_type' => $r->tax_type,
					'discount' => (float) $r->discount_input,
				];
			}
		}

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/purchase_form', $data);
	}

	public function purchase()
	{
		$this->permission_check('purchase_view');
		$data = $this->data;
		$data['page_title'] = 'Purchase History';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$store_id = get_current_store_id();

		$data['records'] = $this->db->select('p.*, s.supplier_name')
			->from('db_purchase p')
			->join('db_suppliers s', 's.id = p.supplier_id', 'left')
			->where('p.store_id', $store_id)
			->order_by('p.id', 'desc')
			->limit(200)
			->get()
			->result();

		$data['suppliers'] = get_suppliers_select_list('', $store_id, false);
		$statuses = [];
		foreach($data['records'] as $r){
			if(!empty($r->purchase_status) && !in_array($r->purchase_status, $statuses)){
				$statuses[] = $r->purchase_status;
			}
		}
		$data['statuses'] = $statuses;

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/purchase', $data);
	}

	public function purchase_view($id)
	{
		$this->belong_to('db_purchase', $id);
		$this->permission_check('purchase_view');
		$data = $this->data;
		$data['page_title'] = 'Purchase';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['p'] = $this->db->select('p.*, s.supplier_name')
			->from('db_purchase p')
			->join('db_suppliers s', 's.id = p.supplier_id', 'left')
			->where('p.id', $id)
			->get()
			->row();
		if(empty($data['p'])){ show_404(); exit; }

		$data['items'] = $this->db->select('pi.*, i.item_name, i.item_code')
			->from('db_purchaseitems pi')
			->join('db_items i', 'i.id = pi.item_id', 'left')
			->where('pi.purchase_id', $id)
			->get()
			->result();

		$data['payments'] = $this->db->where('purchase_id', $id)->order_by('id', 'desc')->get('db_purchasepayments')->result();

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/purchase_view', $data);
	}

	public function purchase_payment($purchase_id)
	{
		$this->belong_to('db_purchase', $purchase_id);
		$this->permission_check('purchase_payment_add');
		$data = $this->data;
		$data['page_title'] = 'Pay Purchase';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$store_id = get_current_store_id();

		$purchase = $this->db->where('id', $purchase_id)->where('store_id', $store_id)->get('db_purchase')->row();
		if(!$purchase){ show_404(); exit; }
		$data['purchase'] = $purchase;
		$data['due'] = $purchase->grand_total - $purchase->paid_amount;

		$data['payment_modes'] = $this->db->where('store_id', $store_id)->where('status', 1)->order_by('name')->get('db_payment_modes')->result();
		$data['accounts'] = $this->db->where('store_id', $store_id)->where('delete_bit', 0)->order_by('account_name')->get('ac_accounts')->result();

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/purchase_payment', $data);
	}

	public function purchase_status($purchase_id)
	{
		$this->belong_to('db_purchase', $purchase_id);
		$this->permission_check('purchase_edit');
		$store_id = get_current_store_id();
		$status = $this->input->post('purchase_status', TRUE);
		if(empty($status)){ show_404(); exit; }

		$purchase = $this->db->where('id', $purchase_id)->where('store_id', $store_id)->get('db_purchase')->row();
		if(empty($purchase)){ show_404(); exit; }

		$items = $this->db->where('purchase_id', $purchase_id)->get('db_purchaseitems')->result();

		// Rebuild the full purchase POST so verify_save_and_update handles totals, stock, etc.
		$_POST = array_merge($_POST, [
			'command' => 'update',
			'purchase_id' => $purchase_id,
			'store_id' => $purchase->store_id,
			'pur_date' => $purchase->purchase_date,
			'reference_no' => $purchase->reference_no,
			'purchase_status' => $status,
			'supplier_id' => $purchase->supplier_id,
			'warehouse_id' => $purchase->warehouse_id,
			'purchase_note' => $purchase->purchase_note,
			'other_charges_input' => store_number_format($purchase->other_charges_input, 0) ?: '0',
			'other_charges_tax_id' => $purchase->other_charges_tax_id,
			'other_charges_amt' => $purchase->other_charges_amt,
			'discount_to_all_input' => $purchase->discount_to_all_input,
			'discount_to_all_type' => $purchase->discount_to_all_type,
			'tot_discount_to_all_amt' => $purchase->tot_discount_to_all_amt,
			'tot_subtotal_amt' => $purchase->subtotal,
			'tot_round_off_amt' => $purchase->round_off,
			'tot_total_amt' => $purchase->grand_total,
			'rowcount' => count($items),
			'amount' => '0',
		]);

		foreach($items as $i => $it){
			$idx = $i + 1;
			$is_received = ($status === 'Received');
			$is_partial = ($status === 'Partially Received');
			$received_qty = null;
			if($is_received){
				$received_qty = $it->purchase_qty;
			} elseif($is_partial){
				$received_qty = $it->received_qty;
			}

			$_POST['tr_item_id_' . $idx] = $it->item_id;
			$_POST['td_data_' . $idx . '_3'] = $it->purchase_qty;
			$_POST['td_data_' . $idx . '_4'] = $it->price_per_unit;
			$_POST['tr_tax_id_' . $idx] = $it->tax_id;
			$_POST['td_data_' . $idx . '_5'] = $it->tax_amt;
			$_POST['tr_tax_type_' . $idx] = $it->tax_type;
			$_POST['td_data_' . $idx . '_10'] = $it->unit_total_cost;
			$_POST['td_data_' . $idx . '_9'] = $it->total_cost;
			$_POST['item_discount_type_' . $idx] = $it->discount_type;
			$_POST['item_discount_input_' . $idx] = $it->discount_input;
			$_POST['td_data_' . $idx . '_8'] = $it->discount_amt;
			$_POST['description_' . $idx] = $it->description;
			$_POST['received_qty_' . $idx] = $received_qty === null ? '' : $received_qty;
		}

		$_REQUEST = array_merge($_REQUEST, $_POST);

		$this->load->model('purchase_model', 'purchase_m');
		$result = $this->purchase_m->verify_save_and_update();
		if(strpos($result, 'success') === 0){
			$this->session->set_flashdata('success', 'Purchase status updated to ' . $status);
		} else {
			$this->session->set_flashdata('failed', $result ?: 'Update failed');
		}
		redirect('mobile/purchase_view/' . $purchase_id);
	}

	public function quotation_view($id)
	{
		$this->belong_to('db_quotation', $id);
		$this->permission_check('quotation_view');
		$data = $this->data;
		$data['page_title'] = 'Quotation';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['q'] = $this->db->select('q.*, c.customer_name, c.mobile, c.email')
			->from('db_quotation q')
			->join('db_customers c', 'c.id = q.customer_id', 'left')
			->where('q.id', $id)
			->get()
			->row();
		if(empty($data['q'])){ show_404(); exit; }
		$data['items'] = $this->db->select('qi.*, i.item_name, i.item_code')
			->from('db_quotationitems qi')
			->join('db_items i', 'i.id = qi.item_id', 'left')
			->where('qi.quotation_id', $id)
			->get()
			->result();
		$this->load->view('mobile/quotation_view', $data);
	}

	public function delete_quotation()
	{
		$this->permission_check_with_msg('quotation_delete');
		$id = (int)$this->input->post('q_id', TRUE);
		if($id <= 0){
			echo json_encode(['status' => 'error', 'message' => 'Invalid quotation.']);
			return;
		}
		$this->belong_to('db_quotation', $id);

		$converted = $this->db->where('quotation_id', $id)->get('db_sales')->row();
		if($converted){
			echo json_encode(['status' => 'error', 'message' => 'Cannot delete. This quotation has been converted to a sale.']);
			return;
		}

		$this->load->model('quotation_model', 'quotation');
		$result = $this->quotation->delete_quotation($id);
		if($result == 'success'){
			echo json_encode(['status' => 'success', 'message' => 'Quotation deleted.']);
		} else {
			echo json_encode(['status' => 'error', 'message' => strip_tags($result)]);
		}
	}

	public function approval_logs()
	{
		$this->permission_check('approval_logs_view');
		$data = $this->data;
		$data['page_title'] = 'Approval Logs';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$store_id = get_current_store_id();

		$this->load->model('approval_logs_model', 'approval_logs');
		$filters = [
			'store_id' => $store_id,
			'approval_type' => $this->input->get('type') ?: null,
			'status' => $this->input->get('status') ?: null,
			'date_from' => $this->input->get('date_from') ?: null,
			'date_to' => $this->input->get('date_to') ?: null,
		];
		$report = $this->approval_logs->getLogs($filters, 100, 0);
		$data['logs'] = $report['rows'];
		$data['total'] = $report['total'];
		$this->load->view('mobile/approval_logs', $data);
	}

	public function customer_coupon($page='index', $customer_id='')
	{
		$data = $this->data;
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['customer_id'] = $customer_id;
		$store_id = get_current_store_id();

		if($page === 'generate'){
			$this->permission_check('customerCouponAdd');
			$data['page_title'] = 'Create Customer Coupon';
			$this->db->select('id, name, type, value, expire_date');
			$this->db->from('db_coupons');
			$this->db->where('status', 1);
			$this->db->where('store_id', $store_id);
			$data['coupons'] = $this->db->get()->result();
			header('Cache-Control: no-cache, must-revalidate, max-age=0');
			header('Pragma: no-cache');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			$this->load->view('mobile/customer_coupon/generate', $data);
			return;
		}

		$this->permission_check('customerCouponView');
		$data['page_title'] = 'Customer Coupons';
		$this->db->select('a.id, c.customer_name, b.name as coupon_name, a.code, a.expire_date, a.value, a.type, a.description, a.status');
		$this->db->from('db_customer_coupons a');
		$this->db->join('db_coupons b', 'b.id=a.coupon_id', 'left');
		$this->db->join('db_customers c', 'c.id=a.customer_id', 'left');
		$this->db->where('a.store_id', $store_id);
		$this->db->order_by('a.id', 'desc');
		$data['coupons'] = $this->db->get()->result();
		$data['back_url'] = base_url('mobile/more');
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/customer_coupon/index', $data);
	}

	public function discount_coupon($page = 'view', $id = '')
	{
		$store_id = get_current_store_id();
		$data = $this->data;
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['back_url'] = base_url('mobile/more');

		if($page === 'add' || $page === 'update'){
			if($page === 'add'){
				$this->permission_check('discountCouponAdd');
				$data['coupon'] = (object)['name'=>'','expire_date'=>'','value'=>'','type'=>'','description'=>'','id'=>''];
				$data['page_title'] = 'Create General Coupon';
			} else {
				$this->permission_check('discountCouponEdit');
				$q_id = (int)$id;
				$data['coupon'] = $this->db->where('id', $q_id)->where('store_id', $store_id)->get('db_coupons')->row() ?: (object)['name'=>'','expire_date'=>'','value'=>'','type'=>'','description'=>'','id'=>''];
				$data['page_title'] = 'Edit Coupon';
			}
			$this->load->view('mobile/discount_coupon/form', $data);
			return;
		}

		$this->permission_check('discountCouponView');
		$data['page_title'] = 'Coupons Master';
		$this->db->select('id, name, expire_date, value, type, status, description');
		$this->db->from('db_coupons');
		$this->db->where('store_id', $store_id);
		$this->db->order_by('id', 'desc');
		$data['coupons'] = $this->db->get()->result();
		$this->load->view('mobile/discount_coupon/view', $data);
	}

	public function loyalty($page = 'index')
	{
		$allowed = ['index','settings','tiers','bonus_rules','product_points','points_history','referral_program'];
		if(!in_array($page, $allowed, TRUE)){
			show_404();
			return;
		}

		$perm = in_array($page, ['settings','referral_program'], TRUE) ? 'loyalty_edit' : 'loyalty_view';
		$this->permission_check($perm);

		$store_id = get_current_store_id();
		$data = $this->data;
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['back_url'] = base_url('mobile/more');
		$this->load->model('loyalty_model','loyalty');
		$this->load->model('customers_model','customers');

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

		if($page === 'index'){
			$data['page_title'] = 'Loyalty & Rewards';
			$data['settings'] = $this->loyalty->get_settings();
			$data['tiers'] = $this->loyalty->get_tiers();
			$data['stats'] = $this->loyalty->get_dashboard_stats();
			$this->load->view('mobile/loyalty/index', $data);
		}
		elseif($page === 'settings'){
			$data['page_title'] = 'Loyalty Settings';
			$data['settings'] = $this->loyalty->get_settings();
			$data['tiers'] = $this->loyalty->get_tiers();
			$this->load->view('mobile/loyalty/settings', $data);
		}
		elseif($page === 'tiers'){
			$data['page_title'] = 'Customer Tiers';
			$data['tiers'] = $this->loyalty->get_tiers();
			$this->load->view('mobile/loyalty/tiers', $data);
		}
		elseif($page === 'bonus_rules'){
			$data['page_title'] = 'Bonus Rules';
			$data['rules'] = $this->loyalty->get_bonus_rules();
			$this->load->view('mobile/loyalty/bonus_rules', $data);
		}
		elseif($page === 'product_points'){
			$data['page_title'] = 'Product Points';
			if($this->db->table_exists('db_loyalty_product_points')){
				$this->db->select('a.*, b.item_name');
				$this->db->from('db_loyalty_product_points a');
				$this->db->join('db_items b', 'b.id = a.item_id', 'left');
				$this->db->where('a.store_id', $store_id);
				$this->db->where('a.status', 1);
				$this->db->order_by('a.id', 'desc');
				$data['product_points'] = $this->db->get()->result();
			} else {
				$data['product_points'] = [];
			}
			$this->db->select('id, item_name');
			$this->db->where('store_id', $store_id);
			if($this->db->field_exists('status','db_items')){
				$this->db->where('status', 1);
			}
			$this->db->order_by('item_name','asc');
			$data['items'] = $this->db->get('db_items')->result();
			$this->load->view('mobile/loyalty/product_points', $data);
		}
		elseif($page === 'points_history'){
			$data['page_title'] = 'Points History';
			if($this->db->table_exists('db_loyalty_points')){
				$this->db->select('a.*, b.customer_name');
				$this->db->from('db_loyalty_points a');
				$this->db->join('db_customers b', 'b.id = a.customer_id', 'left');
				$this->db->where('a.store_id', $store_id);
				$this->db->order_by('a.id', 'desc');
				$this->db->limit(200);
				$data['history'] = $this->db->get()->result();
			} else {
				$data['history'] = [];
			}
			$this->load->view('mobile/loyalty/points_history', $data);
		}
		elseif($page === 'referral_program'){
			$data['page_title'] = 'Referral Program';
			$data['settings'] = $this->loyalty->get_referral_settings();
			$this->load->view('mobile/loyalty/referral_program', $data);
		}
	}

	public function sales_invoice($id)
	{
		$this->belong_to('db_sales', $id);
		if(!$this->permissions('sales_add') && !$this->permissions('sales_edit') && !$this->permissions('sales_view')){
			$this->show_access_denied_page();
			return;
		}
		$sale = $this->db->where('id', $id)->get('db_sales')->row();
		if($sale && !$this->permissions('show_all_users_sales_invoices')){
			if(strtoupper($sale->created_by) !== strtoupper($this->session->userdata('inv_username'))){
				$this->show_access_denied_page();
				return;
			}
		}
		$data = $this->data;
		$data['page_title'] = 'Invoice';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['sales_id'] = $id;
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/sales_invoice', $data);
	}

	public function account_book($account_id)
	{
		$this->belong_to('ac_accounts', $account_id);
		$this->permission_check('accounts_view');

		$store_id = get_current_store_id();
		$account = $this->db->where('id', $account_id)->where('store_id', $store_id)->get('ac_accounts')->row();
		if(!$account){
			$this->session->set_flashdata('failed', 'Account not found.');
			redirect('mobile/more');
		}

		$from = $this->input->get('from') ?: date('Y-m-01');
		$to = $this->input->get('to') ?: date('Y-m-d');
		$from_sql = date('Y-m-d', strtotime($from));
		$to_sql = date('Y-m-d', strtotime($to));
		if($from_sql == '1970-01-01') $from_sql = date('Y-m-01');
		if($to_sql == '1970-01-01') $to_sql = date('Y-m-d');

		$prev_balance = 0;
		if($from_sql != '1970-01-01'){
			$credit = (float) ($this->db->select('COALESCE(SUM(credit_amt),0) as amt')
				->where('credit_account_id', $account_id)
				->where('transaction_date <', $from_sql)
				->where('store_id', $store_id)
				->get('ac_transactions')->row()->amt ?? 0);
			$debit = (float) ($this->db->select('COALESCE(SUM(debit_amt),0) as amt')
				->where('debit_account_id', $account_id)
				->where('transaction_date <', $from_sql)
				->where('store_id', $store_id)
				->get('ac_transactions')->row()->amt ?? 0);
			$prev_balance = $credit - $debit;
		}

		$records = $this->db->where('store_id', $store_id)
			->group_start()
				->where('credit_account_id', $account_id)
				->or_where('debit_account_id', $account_id)
			->group_end()
			->where('transaction_date >=', $from_sql)
			->where('transaction_date <=', $to_sql)
			->order_by('id', 'asc')
			->limit(200)
			->get('ac_transactions')->result();

		$running = $prev_balance;
		$total_debit = 0;
		$total_credit = 0;
		$transactions = [];
		foreach($records as $t){
			$is_debit = ((int)$t->debit_account_id === (int)$account_id);
			$debit_amt = (float) $t->debit_amt;
			$credit_amt = (float) $t->credit_amt;
			if($is_debit){
				$running -= $debit_amt;
				$total_debit += $debit_amt;
				$amount = $debit_amt;
			} else {
				$running += $credit_amt;
				$total_credit += $credit_amt;
				$amount = $credit_amt;
			}
			$desc = ucwords(strtolower($t->transaction_type ?: ''));
			$transactions[] = (object) [
				'transaction_date' => $t->transaction_date,
				'transaction_type' => $t->transaction_type,
				'description' => $desc,
				'is_debit' => $is_debit,
				'amount' => $amount,
				'balance' => $running,
				'note' => $t->note,
				'created_by' => $t->created_by
			];
		}

		$data = $this->data;
		$data['page_title'] = 'Account Book';
		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['account'] = $account;
		$data['from'] = $from_sql;
		$data['to'] = $to_sql;
		$data['total_debit'] = $total_debit;
		$data['total_credit'] = $total_credit;
		$data['transactions'] = $transactions;

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/account_book', $data);
	}

	private function is_cashier_clocked_in()
	{
		$roleName = trim($this->session->userdata('role_name') ?: '');
		if(stripos($roleName, 'cashier') === false) return true;
		$this->load->model('attendance_model');
		$user_id = $this->session->userdata('inv_userid');
		try {
			return (bool) $this->attendance_model->needsClockOut($user_id);
		} catch (Throwable $e) {
			log_message('error', 'Cashier clock-in check failed: ' . $e->getMessage());
			return true;
		}
	}

	public function store_credit()
	{
		if(!mp_feature_enabled('store_credit')){
			$this->show_feature_not_activated('store_credit');
			return;
		}
		$this->permission_check('store_credit_view');
		$this->load->model('store_credit_model','store_credit');
		$data = $this->data;
		$data['page_title'] = 'Store Credit';
		$store_id = get_current_store_id();

		if($this->db->table_exists('db_store_credit')){
			$data['credits'] = $this->db->select('a.*, b.customer_name')
				->from('db_store_credit a')
				->join('db_customers b', 'b.id = a.customer_id', 'left')
				->where('a.store_id', $store_id)
				->order_by('a.id', 'desc')
				->get()
				->result();
		} else {
			$data['credits'] = [];
		}

		if($this->db->table_exists('db_customers')){
			$this->db->select('id, customer_name');
			$this->db->where('store_id', $store_id);
			$this->db->where('status', 1);
			$this->db->where('delete_bit !=', 1);
			$this->db->order_by('customer_name', 'asc');
			$data['customers'] = $this->db->get('db_customers')->result();
		} else {
			$data['customers'] = [];
		}

		$data['display_name'] = $this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'User';
		$data['currency_code'] = $this->session->userdata('currency_code') ?: '₦';

		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		$this->load->view('mobile/store_credit', $data);
	}

}
