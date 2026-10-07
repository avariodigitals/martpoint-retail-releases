<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Printing Industry Controller
 *
 * Print-shop workspace. One dependable workflow across many print businesses,
 * configured via category presets and server-enforced gates.
 *
 * Endpoints must enforce prerequisites SERVER-SIDE (model methods re-check),
 * never rely on the view being the only gate.
 *
 * Permissions: print_view, print_jobs_add/edit/delete, print_quote, print_artwork,
 * print_design, print_authorize, print_payments (Finance verify), print_production,
 * print_report, print_costing, print_referral.
 */
class Printing extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load_global();
        $this->load->model('printing_model', 'print');
    }

    private function _check_feature() {
        if (!mp_feature_enabled('printing_workflow')) {
            $this->show_feature_not_activated('printing_workflow',
                'Enable the Printing module from Business Profile, then pick category presets.');
        }
    }

    private function _render($page_title, $view, $data = []) {
        $d = $this->data ?? [];
        $d['page_title'] = $page_title;
        $d = array_merge($d, $data);
        $d['content'] = $this->load->view($view, $d, TRUE);
        $this->load->view('mp_layout', $d);
    }

    private function _json($arr) {
        $arr['csrf_hash'] = $this->security->get_csrf_hash();
        header('Content-Type: application/json');
        echo json_encode($arr);
    }

    /* ============================ dashboard ============================== */

    public function index() {
        $this->_check_feature();
        $this->permission_check('print_view');
        $store_id = get_current_store_id();
        $this->print->seed_categories($store_id);
        $jobs = $this->print->get_jobs($store_id);
        $counts = ['planned' => 0, 'in_progress' => 0, 'on_hold' => 0, 'completed' => 0, 'cancelled' => 0];
        foreach ($jobs as $j) {
            if (isset($counts[$j->production_status])) $counts[$j->production_status]++;
        }
        $range = $this->input->get('range') ?: 'Today';
        // Sales-drive figures for the dashboard cards.
        $site = $this->db->select('sales_target')->where('id', 1)->get('db_sitesettings')->row();
        $daily_target = (float)($site->sales_target ?? 0);
        $daily = $this->print->daily_report($store_id, $range);
        $range_days = ['Today'=>1,'7Days'=>7,'30Days'=>30][$range] ?? 1;
        $range_target = $daily_target * $range_days;
        $range_progress = $range_target > 0 ? min(100, round(($daily['collected'] / $range_target) * 100, 1)) : 0;
        $data = [
            'jobs' => $jobs,
            'counts' => $counts,
            'categories' => $this->print->get_categories($store_id),
            'kpis' => $this->print->dashboard_kpis($store_id),
            'daily' => $daily,
            'range' => $range,
            'ranges' => ['Today', '7Days', '30Days', 'ThisMonth', 'ThisYear'],
            'daily_target' => $daily_target,
            'range_target' => $range_target,
            'range_progress' => $range_progress,
            'recent_jobs' => $this->print->recent_jobs($store_id, $range, 6),
            'recent_activity' => $this->print->recent_activity($store_id, 5),
            'top_categories' => $this->print->top_categories($store_id, $range, 5),
            'top_debtors' => $this->print->top_debtors($store_id, 5),
            'low_stock' => $this->print->low_stock_materials($store_id, 5),
            'trend' => $this->print->collections_trend($store_id, 7),
            'can_quote' => $this->permissions('print_quote'),
            'can_artwork' => $this->permissions('print_artwork'),
            'can_design' => $this->permissions('print_design'),
            'can_authorize' => $this->permissions('print_authorize'),
            'can_payments' => $this->permissions('print_payments'),
            'can_production' => $this->permissions('print_production'),
            'can_costing' => $this->permissions('print_costing'),
            'can_referral' => $this->permissions('print_referral'),
        ];
        $this->_render('Printing', 'printing/dashboard', $data);
    }

    /* ============================ list views =============================== */

    public function jobs() {
        $this->_check_feature();
        $this->permission_check('print_view');
        $store_id = get_current_store_id();
        $this->_render('Print Jobs', 'printing/jobs', [
            'jobs' => $this->print->get_jobs($store_id),
            'statuses' => Printing_model::job_statuses(),
        ]);
    }

    public function artworks() {
        $this->_check_feature();
        $this->permission_check('print_view');
        $store_id = get_current_store_id();

        $status = $this->input->get('status');
        $job_id = (int)$this->input->get('job_id');
        $search = trim((string)$this->input->get('q'));

        $artworks = $this->_all_artworks($store_id, [
            'status' => $status,
            'job_id' => $job_id,
            'search' => $search,
        ]);

        // Status counts drive the filter chips. Counted over ALL artworks,
        // not the filtered set, so the chips don't vanish as you filter.
        $all = $this->_all_artworks($store_id);
        $counts = ['all' => count($all), 'pending' => 0, 'approved' => 0, 'rejected' => 0];
        foreach ($all as $a) {
            if (isset($counts[$a->status])) $counts[$a->status]++;
        }

        // Jobs awaiting artwork — the ones an upload would sensibly target.
        $awaiting = $this->db->select('id, job_code, title, artwork_status, design_status')
            ->where('store_id', $store_id)
            ->where_in('artwork_status', ['none', 'pending', 'rejected'])
            ->where_not_in('production_status', ['completed', 'cancelled'])
            ->order_by('id', 'desc')
            ->get('db_print_jobs')->result();

        $this->_render('Artwork', 'printing/artworks', [
            'artworks' => $artworks,
            'counts' => $counts,
            'awaiting' => $awaiting,
            'jobs' => $this->db->select('id, job_code, title')->where('store_id', $store_id)
                ->order_by('id', 'desc')->limit(200)->get('db_print_jobs')->result(),
            'filters' => ['status' => $status, 'job_id' => $job_id, 'q' => $search],
            'can_approve' => $this->permissions('print_artwork'),
            'can_design' => $this->permissions('print_design'),
        ]);
    }

    public function authorizations() {
        $this->_check_feature();
        $this->permission_check('print_authorize');
        $store_id = get_current_store_id();
        $auths = $this->db->select('a.*, j.job_code, j.title, c.customer_name')
            ->from('db_print_authorizations a')
            ->join('db_print_jobs j', 'j.id = a.job_id', 'left')
            ->join('db_customers c', 'c.id = j.customer_id', 'left')
            ->where('a.store_id', $store_id)
            ->order_by('a.id', 'desc')->get()->result();
        $this->_render('Print Authorization', 'printing/authorizations', [
            'auths' => $auths,
            'users' => $this->db->where('store_id', $store_id)->where('status', 1)->get('db_users')->result(),
        ]);
    }

    public function payments() {
        $this->_check_feature();
        $this->permission_check('print_payments');
        $store_id = get_current_store_id();
        $payments = $this->db->select('p.*, j.job_code, j.title')
            ->from('db_print_payments p')
            ->join('db_print_jobs j', 'j.id = p.job_id', 'left')
            ->where('p.store_id', $store_id)
            ->order_by('p.id', 'desc')->get()->result();
        $this->_render('Print Payments', 'printing/payments', [
            'payments' => $payments,
            'can_verify' => $this->permissions('print_payments_verify'),
        ]);
    }

    public function production() {
        $this->_check_feature();
        $this->permission_check('print_view');
        $store_id = get_current_store_id();
        // Each column on the board represents the job's earliest unmet gate, so
        // advancing a job means performing that gate's action. The board needs
        // to know which actions this user may actually perform.
        $this->_render('Production Board', 'printing/production', [
            'board' => $this->print->kanban_board($store_id),
            'can_production' => $this->permissions('print_production'),
            'can_quote' => $this->permissions('print_quote'),
            'can_payments' => $this->permissions('print_payments'),
            'can_artwork' => $this->permissions('print_artwork'),
            'can_authorize' => $this->permissions('print_authorize'),
        ]);
    }

    public function reports() {
        $this->_check_feature();
        $this->permission_check('print_costing');
        $store_id = get_current_store_id();
        
        // Get filter parameters
        $date_from = $this->input->get('date_from') ?: date('Y-m-01');
        $date_to = $this->input->get('date_to') ?: date('Y-m-d');
        $category = $this->input->get('category');
        $customer_id = $this->input->get('customer_id');
        $status = $this->input->get('status');
        
        // Get categories for filter dropdown
        $categories = $this->print->get_categories($store_id);
        
        // Get customers for filter dropdown
        $customers = $this->db->select('id, customer_name')
            ->where('store_id', $store_id)
            ->where('status', 1)
            ->order_by('customer_name')
            ->get('db_customers')->result();
        
        // Build job query with filters
        $this->db->select('j.*')
            ->from('db_print_jobs j')
            ->where('j.store_id', $store_id);
        
        if ($date_from) {
            $this->db->where('j.created_at >=', $date_from . ' 00:00:00');
        }
        if ($date_to) {
            $this->db->where('j.created_at <=', $date_to . ' 23:59:59');
        }
        if ($category) {
            // Filter by category through job lines
            $this->db->join('db_print_job_lines jl', 'jl.job_id = j.id', 'inner');
            $this->db->where('jl.category_id', $category);
            $this->db->group_by('j.id');
        }
        if ($customer_id) {
            $this->db->where('j.customer_id', $customer_id);
        }
        if ($status) {
            $this->db->where('j.production_status', $status);
        }
        
        $jobs = $this->db->order_by('j.created_at', 'desc')->get()->result();
        
        $reports = [];
        foreach ($jobs as $j) {
            $reports[] = $this->print->job_report($j->id);
        }
        
        // Get filtered category report - join through job lines since categories are at line level
        $this->db->select('DATE_FORMAT(j.created_at, "%Y-%m") as ym, COALESCE(l.category_key, "uncategorised") as category_key, COUNT(DISTINCT j.id) as jobs, SUM(j.quote_amount) as value, SUM(j.act_material_cost + j.act_labour_cost + j.act_outsource_cost) as cost')
            ->from('db_print_jobs j')
            ->join('db_print_job_lines l', 'l.job_id = j.id', 'left')
            ->where('j.store_id', $store_id);
        
        if ($date_from) {
            $this->db->where('j.created_at >=', $date_from . ' 00:00:00');
        }
        if ($date_to) {
            $this->db->where('j.created_at <=', $date_to . ' 23:59:59');
        }
        if ($category) {
            $this->db->where('l.category_id', $category);
        }
        if ($customer_id) {
            $this->db->where('j.customer_id', $customer_id);
        }
        if ($status) {
            $this->db->where('j.production_status', $status);
        }
        
        $cat_month = $this->db->group_by(['ym', 'l.category_key'])
            ->order_by('ym', 'desc')
            ->get()->result();
        
        // Calculate comprehensive KPIs
        $total_jobs = count($jobs);
        $total_revenue = array_sum(array_map(function($r) { return $r['revenue']; }, $reports));
        $total_cost = array_sum(array_map(function($r) { return $r['actual_total']; }, $reports));
        $total_profit = $total_revenue - $total_cost;
        $avg_job_value = $total_jobs > 0 ? $total_revenue / $total_jobs : 0;
        $profit_margin = $total_revenue > 0 ? ($total_profit / $total_revenue) * 100 : 0;
        
        // Status breakdown
        $status_counts = [];
        foreach ($jobs as $j) {
            $s = $j->production_status ?: 'pending';
            if (!isset($status_counts[$s])) $status_counts[$s] = 0;
            $status_counts[$s]++;
        }
        
        // Top customers by revenue
        $this->db->select('c.customer_name, SUM(j.quote_amount) as total_revenue, COUNT(*) as job_count')
            ->from('db_print_jobs j')
            ->join('db_customers c', 'c.id = j.customer_id')
            ->where('j.store_id', $store_id);
        
        if ($date_from) {
            $this->db->where('j.created_at >=', $date_from . ' 00:00:00');
        }
        if ($date_to) {
            $this->db->where('j.created_at <=', $date_to . ' 23:59:59');
        }
        
        $top_customers = $this->db->group_by('c.id')
            ->order_by('total_revenue', 'desc')
            ->limit(5)
            ->get()->result();
        
        // Top categories by revenue — categories live on job LINES, not jobs.
        $this->db->select('COALESCE(l.category_key, "uncategorised") as category_key, SUM(j.quote_amount) as total_revenue, COUNT(DISTINCT j.id) as job_count')
            ->from('db_print_jobs j')
            ->join('db_print_job_lines l', 'l.job_id = j.id', 'left')
            ->where('j.store_id', $store_id);
        
        if ($date_from) {
            $this->db->where('j.created_at >=', $date_from . ' 00:00:00');
        }
        if ($date_to) {
            $this->db->where('j.created_at <=', $date_to . ' 23:59:59');
        }
        
        $top_categories = $this->db->group_by('l.category_key')
            ->order_by('total_revenue', 'desc')
            ->limit(5)
            ->get()->result();
        
        // Monthly trend data for charts
        $this->db->select('DATE_FORMAT(j.created_at, "%Y-%m") as month, SUM(j.quote_amount) as revenue, COUNT(*) as jobs')
            ->from('db_print_jobs j')
            ->where('j.store_id', $store_id)
            ->where('j.created_at >=', date('Y-m-01', strtotime('-6 months')));
        
        $monthly_trend = $this->db->group_by('month')
            ->order_by('month', 'asc')
            ->get()->result();
        
        $kpis = $this->print->dashboard_kpis($store_id);
        // Print-shop analytics: quote conversion, stage bottlenecks, spoilage,
        // estimate accuracy and on-time delivery.
        $analytics = $this->print->production_analytics($store_id, [
            'from' => $date_from,
            'to'   => $date_to,
        ]);

        $this->_render('Reports & Costing', 'printing/reports', [
            'reports' => $reports,
            'analytics' => $analytics,
            'cat_month' => $cat_month,
            'kpis' => $kpis,
            'categories' => $categories,
            'customers' => $customers,
            'filters' => [
                'date_from' => $date_from,
                'date_to' => $date_to,
                'category' => $category,
                'customer_id' => $customer_id,
                'status' => $status,
            ],
            'summary' => [
                'total_jobs' => $total_jobs,
                'total_revenue' => $total_revenue,
                'total_cost' => $total_cost,
                'total_profit' => $total_profit,
                'avg_job_value' => $avg_job_value,
                'profit_margin' => $profit_margin,
                'status_counts' => $status_counts,
            ],
            'top_customers' => $top_customers,
            'top_categories' => $top_categories,
            'monthly_trend' => $monthly_trend,
        ]);
    }

    private function _all_artworks($store_id, $filters = []) {
        $this->db->select('a.*, j.job_code, j.title, j.design_status, j.production_status')
            ->from('db_print_artworks a')
            ->join('db_print_jobs j', 'j.id = a.job_id', 'left')
            ->where('a.store_id', $store_id);

        if (!empty($filters['status'])) {
            $this->db->where('a.status', $filters['status']);
        }
        if (!empty($filters['job_id'])) {
            $this->db->where('a.job_id', (int)$filters['job_id']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start()
                ->like('j.job_code', $filters['search'])
                ->or_like('j.title', $filters['search'])
                ->or_like('a.file_name', $filters['search'])
                ->group_end();
        }

        return $this->db->order_by('a.id', 'desc')->get()->result();
    }

    /* ============================ production actions ======================= */

    public function stage_report() {
        $this->_check_feature();
        $this->permission_check('print_production');
        $job_id = (int)$this->input->post('job_id', TRUE);
        $stage_id = (int)$this->input->post('stage_id', TRUE);
        $d = [
            'qty_in' => $this->input->post('qty_in', TRUE),
            'good_qty' => $this->input->post('good_qty', TRUE),
            'rework_qty' => $this->input->post('rework_qty', TRUE),
            'partially_done_qty' => $this->input->post('partially_done_qty', TRUE),
            'reject_qty' => $this->input->post('reject_qty', TRUE),
            'waste_qty' => $this->input->post('waste_qty', TRUE),
            'scrap_qty' => $this->input->post('scrap_qty', TRUE),
            'outsource_cost' => $this->input->post('outsource_cost', TRUE),
            'labour_cost' => $this->input->post('labour_cost', TRUE),
            'notes' => $this->input->post('notes', TRUE),
        ];
        $res = $this->print->report_stage($job_id, $stage_id, $d);
        $this->_json($res);
    }

    public function stage_complete() {
        $this->_check_feature();
        $this->permission_check('print_production');
        $stage_id = (int)$this->input->post('stage_id', TRUE);
        $results = $this->print->complete_stage($stage_id, $this->input->post('outsource_vendor', TRUE), $this->input->post('outsource_cost', TRUE));
        $this->_json(['success' => $results !== false, 'message' => $results !== false ? 'Stage closed.' : 'Failed to close stage.']);
    }

    public function stage_approve() {
        $this->_check_feature();
        $this->permission_check('print_production');
        $log_id = (int)$this->input->post('log_id', TRUE);
        $res = $this->print->approve_stage_log($log_id);
        $this->_json($res);
    }

    public function job_complete() {
        $this->_check_feature();
        $this->permission_check('print_production');
        $job_id = (int)$this->input->post('job_id', TRUE);
        $res = $this->print->complete_job($job_id);
        $this->_json($res);
    }

    public function fulfil() {
        $this->_check_feature();
        $this->permission_check('print_production');
        $job_id = (int)$this->input->post('job_id', TRUE);
        $qty = (float)$this->input->post('qty', TRUE);
        $kind = $this->input->post('kind', TRUE) ?: 'collection';
        $res = $this->print->record_fulfilment($job_id, $qty, $kind, $this->input->post('recipient_name', TRUE), $this->input->post('evidence_note', TRUE));
        $this->_json($res);
    }

    /* ============================ services (design etc.) ================== */

    public function service_save() {
        $this->_check_feature();
        $this->permission_check('print_jobs_edit');
        $job_id = (int)$this->input->post('job_id', TRUE);
        $line_id = (int)$this->input->post('line_id', TRUE);
        $key = $this->input->post('service_key', TRUE) ?: 'other';
        $d = [
            'service_key' => $key,
            'design_mode' => $this->input->post('design_mode', TRUE),
            'instructions' => $this->input->post('instructions', TRUE),
            'assignee_id' => $this->input->post('assignee_id', TRUE),
            'expected_date' => $this->input->post('expected_date', TRUE),
            'charge_amount' => (float)$this->input->post('charge_amount', TRUE),
            'charge_waived' => (int)$this->input->post('charge_waived', TRUE),
            'waiver_reason' => $this->input->post('waiver_reason', TRUE),
            'internal_cost' => (float)$this->input->post('internal_cost', TRUE),
            'internal_cost_basis' => $this->input->post('internal_cost_basis', TRUE),
        ];
        $res = $this->print->save_item_service($job_id, $line_id, $d, $key);
        $this->_json($res);
    }

    /* ============================ material plan =========================== */

    public function plan_save() {
        $this->_check_feature();
        $this->permission_check('print_jobs_edit');
        $job_id = (int)$this->input->post('job_id', TRUE);
        $line_id = (int)$this->input->post('line_id', TRUE);
        $d = [
            'id' => (int)$this->input->post('plan_id', TRUE) ?: null,
            'plan_type' => $this->input->post('plan_type', TRUE) ?: 'material',
            'item_id' => $this->input->post('item_id', TRUE),
            'operation_key' => $this->input->post('operation_key', TRUE),
            'description' => $this->input->post('description', TRUE),
            'plan_qty' => (float)$this->input->post('plan_qty', TRUE),
            'plan_unit_id' => $this->input->post('plan_unit_id', TRUE),
            'est_unit_cost' => (float)$this->input->post('est_unit_cost', TRUE),
            'wastage_pct' => (float)$this->input->post('wastage_pct', TRUE),
            'roll_width' => $this->input->post('roll_width', TRUE),
            'roll_length' => $this->input->post('roll_length', TRUE),
            'area_sqm' => $this->input->post('area_sqm', TRUE),
        ];
        $res = $this->print->save_plan_row($job_id, $line_id, $d);
        $this->_json($res);
    }

    public function plan_delete() {
        $this->_check_feature();
        $this->permission_check('print_jobs_edit');
        $id = (int)$this->input->post('plan_id', TRUE);
        $ok = $this->print->delete_plan_row($id);
        $this->_json(['success' => (bool)$ok]);
    }

    /* ===================== calculators (quantity derivation) ============== */

    /**
     * Run a category calculator server-side so the planned quantity matches the
     * rules (sheets/packs, roll→area, DI imposition, garment sizes). Nothing
     * universal is assumed — pack size, roll width and ups come from config.
     */
    public function calc_run() {
        $this->_check_feature();
        $this->permission_check('print_view');
        $key = $this->input->post('calc_key', TRUE) ?: null;
        $in = $this->input->post('input') ?: [];
        if (!is_array($in)) $in = json_decode((string)$this->input->post('input'), true) ?: [];
        $cat_id = (int)$this->input->post('category_id', TRUE);
        if (!$key && $cat_id) {
            $cfg = $this->print->category_calc(get_current_store_id(), $cat_id);
            $key = $cfg['calc_key'];
            $in = array_merge($cfg['config'], $in);
        }
        $res = $this->print->run_calculator($key, $in);
        $this->_json(['success' => empty($res['error']), 'result' => $res]);
    }

    public function calc_save() {
        $this->_check_feature();
        $this->permission_check('print_settings');
        $cat_id = (int)$this->input->post('category_id', TRUE);
        $key = $this->input->post('calc_key', TRUE);
        $cfg = $this->input->post('config') ?: [];
        if (!is_array($cfg)) $cfg = json_decode((string)$this->input->post('config'), true) ?: [];
        $res = $this->print->save_category_calc(get_current_store_id(), $cat_id, $key, $cfg);
        $this->_json($res);
    }

    /* ============ material issue ledger (single posting method) ========== */

    public function material_reserve() {
        $this->_check_feature();
        $this->permission_check('print_production');
        $res = $this->print->reserve_material(
            (int)$this->input->post('job_id', TRUE),
            (int)$this->input->post('line_id', TRUE),
            [
                'plan_id' => (int)$this->input->post('plan_id', TRUE),
                'item_id' => (int)$this->input->post('item_id', TRUE),
                'planned_qty' => (float)$this->input->post('planned_qty', TRUE),
                'unit_id' => (int)$this->input->post('unit_id', TRUE),
                'warehouse_id' => (int)$this->input->post('warehouse_id', TRUE),
                'note' => $this->input->post('note', TRUE),
            ]
        );
        $this->_json($res);
    }

    public function material_issue() {
        $this->_check_feature();
        $this->permission_check('print_production');
        $qty = $this->input->post('qty', TRUE);
        $res = $this->print->issue_material((int)$this->input->post('issue_id', TRUE), $qty === '' || $qty === null ? null : (float)$qty);
        $this->_json($res);
    }

    public function material_consume() {
        $this->_check_feature();
        $this->permission_check('print_production');
        $res = $this->print->consume_material(
            (int)$this->input->post('issue_id', TRUE),
            (float)$this->input->post('consumed_qty', TRUE),
            (float)$this->input->post('wastage_qty', TRUE)
        );
        $this->_json($res);
    }

    public function material_return() {
        $this->_check_feature();
        $this->permission_check('print_production');
        $res = $this->print->return_unused_material(
            (int)$this->input->post('issue_id', TRUE),
            (float)$this->input->post('return_qty', TRUE),
            (float)$this->input->post('wastage_qty', TRUE),
            $this->input->post('note', TRUE)
        );
        $this->_json($res);
    }

    public function material_release() {
        $this->_check_feature();
        $this->permission_check('print_production');
        $res = $this->print->release_material((int)$this->input->post('issue_id', TRUE));
        $this->_json($res);
    }

    public function material_reverse() {
        $this->_check_feature();
        $this->permission_check('print_production');
        $res = $this->print->reverse_material_issue(
            (int)$this->input->post('issue_id', TRUE),
            $this->input->post('reason', TRUE)
        );
        $this->_json($res);
    }

    /* ============ quotation (existing module is authoritative) =========== */

    /* ============================ referral ================================ */

    /**
     * Explicitly raise a print job from an existing GENERAL quotation.
     * The only supported route from a non-production quotation into production.
     */
    public function job_from_quotation() {
        $this->_check_feature();
        $this->permission_check('print_jobs_add');
        $quotation_id = (int)$this->input->post('quotation_id', TRUE);
        $res = $this->print->create_job_from_quotation($quotation_id, [
            'category_id' => (int)$this->input->post('category_id', TRUE),
            'title' => $this->input->post('title', TRUE),
            'due_date' => $this->input->post('due_date', TRUE),
        ]);
        $this->_json($res);
    }

    /** Review list of quotations not linked to any print job. Never auto-attaches. */
    public function unlinked_quotations() {
        $this->_check_feature();
        $this->permission_check('print_view');
        $store_id = get_current_store_id();
        $data = $this->data ?? [];
        $data['page_title'] = 'Unlinked Quotations';
        $data['rows'] = $this->print->unlinked_quotations($store_id);
        $data['count'] = count($data['rows']);
        $data['categories'] = $this->print->get_categories($store_id);
        $data['content'] = $this->load->view('printing/unlinked_quotations', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    /**
     * Default "New Quotation" for a printing business.
     *
     * A print quotation must be attached to a job, so this page asks which job
     * (existing or new) rather than dropping the user into a free-form builder
     * that would bypass the deposit policy, production gates and printing
     * terms. The general (non-production) quotation remains available as a
     * labelled secondary option.
     */
    public function new_quotation() {
        $this->_check_feature();
        $this->permission_check('quotation_add');
        $store_id = get_current_store_id();
        $data = $this->data ?? [];
        $data['page_title'] = 'New Quotation';
        // Jobs that do not yet have a quotation are the ones we can price.
        $data['jobs_without_quote'] = $this->db->select('id,job_code,title,customer_id,due_date')
            ->where('store_id', $store_id)
            ->where('(quotation_id IS NULL OR quotation_id = 0)', null, false)
            ->where_not_in('production_status', ['completed', 'cancelled'])
            ->order_by('id', 'desc')->limit(50)->get('db_print_jobs')->result();
        $data['customers'] = $this->db->where('store_id', $store_id)->where('status', 1)
            ->order_by('customer_name', 'asc')->get('db_customers')->result();
        $data['unlinked_count'] = $this->print->unlinked_quotation_count($store_id);
        $data['content'] = $this->load->view('printing/new_quotation', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    /** Customer declined the quotation — closes the job to production. */
    public function quote_decline() {
        $this->_check_feature();
        $this->permission_check('print_quote');
        $job_id = (int)$this->input->post('job_id', TRUE);
        $reason = trim((string)$this->input->post('reason', TRUE));
        if ($reason === '') {
            $this->_json(['success' => false, 'message' => 'A reason is required when declining a quotation.']);
            return;
        }
        $this->_json($this->print->decline_quotation($job_id, $reason));
    }

    /** Cancel the whole job. */
    public function job_cancel() {
        $this->_check_feature();
        $this->permission_check('print_quote');
        $job_id = (int)$this->input->post('job_id', TRUE);
        $reason = trim((string)$this->input->post('reason', TRUE));
        if ($reason === '') {
            $this->_json(['success' => false, 'message' => 'A reason is required when cancelling a job.']);
            return;
        }
        $this->_json($this->print->cancel_job($job_id, $reason));
    }

    /** Reopen a declined / cancelled / expired quotation. */
    public function quote_reopen() {
        $this->_check_feature();
        $this->permission_check('print_quote');
        $this->_json($this->print->reopen_quotation((int)$this->input->post('job_id', TRUE)));
    }

    /** Set or change the quotation expiry date. */
    public function quote_expiry() {
        $this->_check_feature();
        $this->permission_check('print_quote');
        $job_id = (int)$this->input->post('job_id', TRUE);
        $expire = trim((string)$this->input->post('expire_date', TRUE));
        $job = $this->print->get_job($job_id);
        if (!$job) { $this->_json(['success' => false, 'message' => 'Job not found.']); return; }
        $q = $this->print->quotation_for_job($job_id);
        if (!$q) { $this->_json(['success' => false, 'message' => 'Issue the quotation first.']); return; }
        if ($expire !== '') {
            $ts = strtotime($expire);
            if (!$ts) { $this->_json(['success' => false, 'message' => 'That expiry date is not valid.']); return; }
            if ($ts < strtotime(date('Y-m-d'))) {
                $this->_json(['success' => false, 'message' => 'The expiry date cannot be in the past.']);
                return;
            }
            $expire = date('Y-m-d', $ts);
        } else {
            $expire = null;
        }
        $this->db->where('id', (int)$q->id)->update('db_quotation', [
            'expire_date' => $expire,
            // A new validity window resets the reminder stamps so the customer
            // is nudged again against the new date.
            'reminder_3d_sent_at' => null,
            'reminder_1d_sent_at' => null,
        ]);
        $this->_json([
            'success' => true,
            'message' => $expire ? 'Quotation valid until ' . show_date($expire) . '.' : 'Expiry date cleared.',
        ]);
    }

    /** Turn an enquiry lead into a print job (and optionally its quotation). */
    public function job_from_lead() {
        $this->_check_feature();
        $this->permission_check('print_jobs_add');
        $lead_id = (int)$this->input->post('lead_id', TRUE);
        $res = $this->print->create_job_from_lead($lead_id, [
            'category_id' => (int)$this->input->post('category_id', TRUE) ?: null,
            'title' => $this->input->post('title', TRUE),
            'issue_quotation' => (int)$this->input->post('issue_quotation', TRUE) === 1,
        ]);
        $this->_json($res);
    }

    public function referral_attribute() {
        $this->_check_feature();
        $this->permission_check('print_referral');
        $job_id = (int)$this->input->post('job_id', TRUE);
        $rate = (float)$this->input->post('rate', TRUE);
        $exclude_tax = (float)$this->input->post('exclude_tax', TRUE);
        $res = $this->print->attribute_referral($job_id, 0, $rate, $this->input->post('source_ref', TRUE), ['tax' => $exclude_tax]);
        $this->_json($res);
    }

    public function referral_pay() {
        $this->_check_feature();
        $this->permission_check('print_referral');
        $referral_id = (int)$this->input->post('referral_id', TRUE);
        $res = $this->print->pay_referral($referral_id);
        $this->_json($res);
    }

    /* ============================ jobs ==================================== */

    public function job($id = null) {
        $this->_check_feature();
        $this->permission_check('print_view');
        $store_id = get_current_store_id();
        $data['job'] = $id ? $this->print->get_job($id) : null;
        $data['lines'] = $id ? $this->print->get_lines($id) : [];
        $data['artworks'] = $id ? $this->print->get_artworks($id) : [];
        $data['stages'] = $id ? $this->print->get_stages($id) : [];
        $data['logs'] = $id ? $this->print->get_logs($id) : [];
        $data['categories'] = $this->print->get_categories($store_id);
        // Full spec schema map (category_id => schema) for the dynamic builder.
        $schema_map = [];
        foreach ($data['categories'] as $c) {
            $schema_map[$c->id] = json_decode($c->spec_schema_json ?: '[]', true) ?: [];
        }
        $data['schema_map'] = $schema_map;
        $data['customers'] = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_customers')->result();
        $data['users'] = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_users')->result();
        $data['prereqs'] = $id ? $this->print->production_prerequisites($id) : ['ok' => false, 'missing' => []];
        $data['stage_progress'] = $id ? $this->print->stage_progress($id) : [];
        // Quotation is owned by the shared module; the job view shows a link to
        // its document plus the tax controls that shape it.
        $data['quotation_summary'] = $id ? $this->print->quotation_summary($id) : null;
        $data['tax_summary'] = $id && $data['quotation_summary']
            ? $this->print->quotation_tax_summary($data['quotation_summary']['quotation_id'])
            : null;
        $data['tax_rates'] = $this->print->available_taxes($store_id);
        // Validity countdown for the lifecycle panel.
        $expire = $data['quotation_summary']['expire_date'] ?? null;
        $data['expiry_days_left'] = $this->print->days_to_expiry($expire);
        $data['can_adjust_quote'] = $this->permissions('print_quote');
        $data['service_types'] = $this->print->get_service_types($store_id);
        $data['items'] = $this->db->where('store_id', $store_id)->where('status', 1)->order_by('item_name', 'asc')->get('db_items')->result();
        $data['units'] = $this->db->where('store_id', $store_id)->where('status', 1)->order_by('unit_name', 'asc')->get('db_units')->result();
        $data['plans'] = $id ? $this->print->get_plans($id) : [];
        $data['services_map'] = [];
        $data['plans_map'] = [];
        if ($id) {
            foreach ($data['lines'] as $ln) {
                $data['services_map'][$ln->id] = $this->print->get_item_services($ln->id);
                $data['plans_map'][$ln->id] = [];
                foreach ($data['plans'] as $p) { if ($p->line_id == $ln->id) $data['plans_map'][$ln->id][] = $p; }
            }
        }
        $data['cost_estimate'] = $id ? $this->print->job_cost_estimate($id) : null;
        $this->_render($id ? 'Print Job ' . ($data['job']->job_code ?? '') : 'New Print Job', 'printing/job', $data);
    }

    public function job_save() {
        $this->_check_feature();
        $this->permission_check('print_jobs_add');
        $store_id = get_current_store_id();
        $this->print->seed_categories($store_id);

        $draft = (int)$this->input->post('is_draft', TRUE) === 1;

        $this->form_validation->set_rules('customer_id', 'Customer', 'trim|required|numeric');
        $this->form_validation->set_rules('title', 'Title', 'trim');
        if ($this->form_validation->run() == FALSE) {
            $this->_json(['success' => false, 'message' => validation_errors()]);
            return;
        }

        // Each line is posted as line_json[i] => {category_id, qty, unit_price, description, item_id, spec{...}}
        $line_jsons = $this->input->post('line_json', TRUE) ?: [];
        $line_cfgs = [];
        $validation_errors = [];
        foreach ($line_jsons as $raw) {
            $lc = is_array($raw) ? $raw : json_decode($raw, true);
            if (empty($lc) || empty($lc['category_id'])) continue;
            $cat = $this->print->get_category((int)$lc['category_id']);
            if (!$cat) continue;
            $spec = isset($lc['spec']) && is_array($lc['spec']) ? $lc['spec'] : [];
            // Server-side spec validation (strict unless saving a draft).
            $v = $this->print->validate_specs($cat->id, $spec, !$draft);
            if (!$v['ok']) {
                foreach ($v['errors'] as $k => $m) $validation_errors[] = $cat->name . ': ' . $m;
            }
            $line_cfgs[] = [
                'category_id' => $cat->id,
                'qty' => $lc['qty'] ?? 0,
                'unit_price' => $lc['unit_price'] ?? 0,
                'description' => $lc['description'] ?? null,
                'item_id' => !empty($lc['item_id']) ? (int)$lc['item_id'] : null,
                'spec' => $spec,
                'supplied_material' => !empty($lc['supplied_material']),
            ];
        }
        if (empty($line_cfgs)) {
            $this->_json(['success' => false, 'message' => 'Add at least one print item.']);
            return;
        }
        if (!empty($validation_errors)) {
            $this->_json(['success' => false, 'message' => "Specification errors:\n- " . implode("\n- ", $validation_errors)]);
            return;
        }

        // Explicit charges (delivery, installation, rush…) — feed the derived total.
        $charges = [];
        $charge_labels = $this->input->post('charge_label', TRUE) ?: [];
        $charge_amts = $this->input->post('charge_amount', TRUE) ?: [];
        for ($i = 0; $i < count($charge_labels); $i++) {
            if ($charge_labels[$i] === '' && empty($charge_amts[$i])) continue;
            $charges[] = ['label' => $charge_labels[$i], 'amount' => (float)($charge_amts[$i] ?? 0)];
        }

        $data = [
            'customer_id' => (int)$this->input->post('customer_id', TRUE),
            'custom_order_id' => (int)($this->input->post('custom_order_id', TRUE) ?: null) ?: null,
            'title' => $this->input->post('title', TRUE),
            'due_date' => $this->input->post('due_date', TRUE) ?: null,
            'priority' => $this->input->post('priority', TRUE) ?: 'normal',
            'notes' => $this->input->post('notes', TRUE),
            'quotation_status' => $draft ? 'draft' : 'none',
            'charges' => $charges,
        ];

        $job_id = $this->print->create_job($data, $line_cfgs);
        if (!$job_id) {
            $this->_json(['success' => false, 'message' => 'Failed to create job.']);
            return;
        }
        $this->_json(['success' => true, 'id' => $job_id, 'message' => $draft ? 'Draft saved.' : 'Job created.']);
    }

    /**
     * Permission-controlled quotation adjustment with an audit reason.
     * Replaces the old independent editable quote amount.
     */
    public function quote_adjust() {
        $this->_check_feature();
        $this->permission_check('print_quote');
        $job_id = (int)$this->input->post('job_id', TRUE);
        $amount = (float)$this->input->post('amount', TRUE);
        $reason = trim((string)$this->input->post('reason', TRUE));
        if ($reason === '') {
            $this->_json(['success' => false, 'message' => 'An audit reason is required to adjust the quotation.']);
            return;
        }
        $job = $this->print->get_job($job_id);
        if (!$job) { $this->_json(['success' => false, 'message' => 'Job not found.']); return; }
        // Once the job is backed by the quotation module, the agreed total is
        // owned by db_quotation. A direct adjustment here would fork the source
        // of truth, so it is refused and the user is sent to the quotation.
        if (!empty($job->quotation_id)) {
            $q = $this->print->quotation_for_job($job_id);
            $this->_json([
                'success' => false,
                'message' => 'This job is priced by quotation ' . ($q->quotation_code ?? '')
                    . '. Revise the quotation so the price change is versioned and re-agreed.',
            ]);
            return;
        }
        $old = (float)$job->quote_amount;
        $this->db->where('id', $job_id)->update('db_print_jobs', [
            'quote_amount' => $amount,
            'notes' => trim(($job->notes ?? '') . "\n[quote adjust] " . $old . ' → ' . $amount . ': ' . $reason),
        ]);
        $this->_json(['success' => true, 'message' => 'Quotation adjusted and logged.']);
    }

    /* ============================ quotation ================================ */

    /** Issue/revise the authoritative quotation (existing module records). */
    public function quote_issue() {
        $this->_check_feature();
        $this->permission_check('print_quote');
        $this->permission_check('quotation_add');
        $job_id = (int)$this->input->post('job_id', TRUE);
        $res = $this->print->quote_to_quotation($job_id, [
            'customer_id' => (int)$this->input->post('customer_id', TRUE),
            'quotation_date' => $this->input->post('quotation_date', TRUE),
            'expire_date' => $this->input->post('expire_date', TRUE),
            'other_charges' => (float)$this->input->post('other_charges', TRUE),
            'discount_input' => (float)$this->input->post('discount_input', TRUE),
            'discount_type' => $this->input->post('discount_type', TRUE),
            'note' => $this->input->post('note', TRUE),
            'revision_note' => $this->input->post('revision_note', TRUE),
        ]);
        // A revision that changes agreed specs/price/terms requires customer
        // reacceptance and invalidates a standing authorization.
        if (!empty($res['success']) && !empty($res['revision_no'])) {
            $this->print->flag_quotation_change($job_id, 'Quotation revised to R' . $res['revision_no']);
        }
        $this->_json($res);
    }

    /** Customer-facing quotation document rendered from the shared records. */
    public function quote_view($job_id = null) {
        $this->_check_feature();
        $this->permission_check('print_view');
        $this->permission_check('quotation_view');
        $job_id = (int)$job_id;
        $job = $this->print->get_job($job_id);
        if (!$job) { show_404(); return; }
        $summary = $this->print->quotation_summary($job_id);
        if (!$summary) {
            $this->session->set_flashdata('error', 'No quotation has been issued for this job yet.');
            redirect('printing/job/' . $job_id);
        }
        $data = $this->_quote_doc_data($job, $summary);
        $data['content'] = $this->load->view('printing/quotation_view', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    /**
     * PDF of the printing quotation.
     *
     * The shared retail PDF template does not carry the printing terms
     * (deposit/balance, turnaround, acceptance), so printing renders its OWN
     * document through dompdf — same data, same shared quotation records.
     */
    public function quote_pdf($job_id = null) {
        $this->_check_feature();
        $this->permission_check('print_view');
        $this->permission_check('quotation_view');
        $job_id = (int)$job_id;
        $job = $this->print->get_job($job_id);
        if (!$job) { show_404(); return; }
        $summary = $this->print->quotation_summary($job_id);
        if (!$summary) { show_404(); return; }

        $data = $this->_quote_doc_data($job, $summary);
        $data['for_pdf'] = true;
        $html = $this->load->view('printing/quotation_print', $data, TRUE);

        require_once(APPPATH . 'libraries/dompdf/autoload.inc.php');
        mb_internal_encoding('UTF-8');
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream(
            'Quotation_' . $summary['quotation_code'] . '_' . date('Ymd'),
            ['Attachment' => 0]
        );
        exit;
    }

    /** Shared data for both the on-screen and PDF quotation documents. */
    private function _quote_doc_data($job, $summary) {
        $data = $this->data ?? [];
        $data['page_title'] = 'Quotation ' . $summary['quotation_code'];
        $data['job'] = $job;
        $data['summary'] = $summary;
        $data['items'] = $this->print->quotation_items($summary['quotation_id']);
        $data['lines'] = $this->print->get_lines($job->id);
        $data['revisions'] = $this->print->quotation_revisions($summary['quotation_id']);
        $data['deposit_policy'] = $this->print->deposit_policy();
        $data['tax_summary'] = $this->print->quotation_tax_summary($summary['quotation_id']);
        $cust = $this->db->select('customer_name,mobile,phone,email,address,gstin,tax_number')
            ->where('id', $job->customer_id)->get('db_customers')->row();
        $data['customer_name'] = $cust->customer_name ?? 'Customer';
        $data['customer_phone'] = $cust->mobile ?? ($cust->phone ?? null);
        $data['customer_email'] = $cust->email ?? null;
        $data['customer_address'] = $cust->address ?? null;
        $data['customer_tax'] = $cust->gstin ?? ($cust->tax_number ?? null);
        return $data;
    }

    /**
     * Convert using the EXISTING workflow. Duplicate protection lives in
     * Sales_model, so a repeated/concurrent request cannot create a second
     * invoice. Conversion never consumes raw materials.
     */
    public function quote_convert($job_id = null) {
        $this->_check_feature();
        $this->permission_check('print_quote');
        $this->permission_check('sales_add');
        $job_id = (int)$job_id;
        $job = $this->print->get_job($job_id);
        if (!$job) { show_404(); return; }
        $q = $this->print->quotation_for_job($job_id);
        if (!$q) {
            $this->session->set_flashdata('error', 'No quotation to convert.');
            redirect('printing/job/' . $job_id);
        }
        if (!empty($q->converted_sales_id)) {
            redirect('sales/invoice/' . (int)$q->converted_sales_id);
        }
        if ($q->sales_status === 'Converted') {
            $existing = $this->db->select('id')->where('quotation_id', (int)$q->id)->get('db_sales')->row();
            if ($existing) { redirect('sales/invoice/' . (int)$existing->id); }
            $this->session->set_flashdata('error', 'This quotation has already been converted.');
            redirect('printing/job/' . $job_id);
        }
        if ($this->print->quotation_change_requires_reacceptance($job_id)) {
            $this->session->set_flashdata('error', 'The quotation changed since it was accepted. Obtain customer reacceptance before converting.');
            redirect('printing/quote_view/' . $job_id);
        }
        if ($this->print->quotation_items($q->id) === []) {
            $this->session->set_flashdata('error', 'The quotation has no lines to convert.');
            redirect('printing/quote_view/' . $job_id);
        }
        redirect('sales/quotation/' . (int)$q->id);
    }

    public function quote_save() {
        $this->_check_feature();
        $this->permission_check('print_quote');
        $job_id = (int)$this->input->post('job_id', TRUE);
        $amount = (float)$this->input->post('amount', TRUE);
        $res = $this->print->set_quotation($job_id, $amount);
        $this->_json($res);
    }

    /**
     * Control tax on a print job. Printing defaults to EXEMPT, so tax only
     * applies when explicitly switched on for the job.
     */
    public function tax_set() {
        $this->_check_feature();
        $this->permission_check('print_quote');
        $job_id = (int)$this->input->post('job_id', TRUE);
        $on = (int)$this->input->post('tax_on', TRUE) === 1;
        $tax_id = (int)$this->input->post('tax_id', TRUE);
        $type = $this->input->post('tax_type', TRUE) ?: 'Exclusive';
        $res = $this->print->set_job_tax($job_id, $on, $tax_id, $type);
        // Keep the issued quotation consistent with the new tax behaviour.
        if (!empty($res['success'])) {
            $job = $this->print->get_job($job_id);
            if ($job && !empty($job->quotation_id)) {
                $re = $this->print->quote_to_quotation($job_id, ['customer_id' => $job->customer_id]);
                if (!empty($re['success'])) {
                    $res['message'] .= ' Quotation updated to ' . $this->currency() . number_format($re['grand_total'], 2) . '.';
                }
            }
        }
        $this->_json($res);
    }

    public function quote_accept() {
        $this->_check_feature();
        $this->permission_check('print_quote');
        $job_id = (int)$this->input->post('job_id', TRUE);
        // When the job is backed by the real quotation, acceptance references
        // that revision; otherwise the legacy path is used unchanged.
        $res = $this->print->accept_quotation($job_id);
        $this->_json($res);
    }

    /* ============================ payments ================================ */

    public function payment_save() {
        $this->_check_feature();
        $this->permission_check('print_payments');
        $job_id = (int)$this->input->post('job_id', TRUE);
        $kind = $this->input->post('kind', TRUE);
        $amount = (float)$this->input->post('amount', TRUE);
        $method = $this->input->post('method', TRUE) ?: 'Bank Transfer';
        $reference = $this->input->post('reference', TRUE);
        $note = $this->input->post('note', TRUE);
        $res = $this->print->record_payment($job_id, $kind, $amount, $method, $reference, $note);
        $this->_json($res);
    }

    public function payment_verify() {
        $this->_check_feature();
        $this->permission_check('print_payments_verify');
        $id = (int)$this->input->post('payment_id', TRUE);
        $res = $this->print->verify_payment($id);
        $this->_json($res);
    }

    public function payment_reverse() {
        $this->_check_feature();
        $this->permission_check('print_payments_verify');
        $id = (int)$this->input->post('payment_id', TRUE);
        $reason = $this->input->post('reason', TRUE);
        $res = $this->print->reverse_payment($id, $reason);
        $this->_json($res);
    }

    /* ============================ artwork ================================= */

    public function artwork_save() {
        $this->_check_feature();
        $this->permission_check('print_artwork');
        $job_id = (int)$this->input->post('job_id', TRUE);

        $config = [
            'upload_path' => FCPATH . 'uploads/printjobs/',
            'allowed_types' => 'jpg|jpeg|png|pdf|ai|eps|svg|tif|tiff',
            'max_size' => 51200,
        ];
        if (!is_dir($config['upload_path'])) @mkdir($config['upload_path'], 0775, true);

        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('artwork_file')) {
            $this->_json(['success' => false, 'message' => $this->upload->display_errors('', '')]);
            return;
        }
        $up = $this->upload->data();
        $file_hash = hash_file('sha256', $up['full_path']);
        $art_id = $this->print->add_artwork(
            $job_id,
            $up['file_name'],
            'uploads/printjobs/' . $up['file_name'],
            $file_hash,
            $up['file_type']
        );
        $this->_json(['success' => (bool)$art_id, 'artwork_id' => $art_id, 'version' => $art_id ? $this->db->where('id', $art_id)->get('db_print_artworks')->row()->version_no : null]);
    }

    public function artwork_approve() {
        $this->_check_feature();
        $this->permission_check('print_artwork');
        $id = (int)$this->input->post('artwork_id', TRUE);
        $res = $this->print->approve_artwork($id);
        $this->_json($res);
    }

    /* ============================ designer clearance ====================== */

    public function design_clear() {
        $this->_check_feature();
        $this->permission_check('print_design');
        $job_id = (int)$this->input->post('job_id', TRUE);
        $decision = $this->input->post('decision', TRUE);
        $reason = $this->input->post('reason', TRUE);
        $res = $this->print->clearance_artwork($job_id, $decision, $reason);
        $this->_json($res);
    }

    /* ============================ authorization =========================== */

    public function auth_request() {
        $this->_check_feature();
        $this->permission_check('print_authorize');
        $job_id = (int)$this->input->post('job_id', TRUE);
        $approver_id = (int)$this->input->post('approver_id', TRUE);
        $backup_id = (int)($this->input->post('backup_approver_id', TRUE) ?: null) ?: null;
        $res = $this->print->request_authorization($job_id, $approver_id, $backup_id);
        $this->_json($res);
    }

    public function auth_decide() {
        $this->_check_feature();
        $this->permission_check('print_authorize');
        $auth_id = (int)$this->input->post('auth_id', TRUE);
        $decision = $this->input->post('decision', TRUE);
        $reason = $this->input->post('reason', TRUE);
        $urgent = (int)$this->input->post('urgent_override', TRUE) ? true : false;
        $res = $this->print->decide_authorization($auth_id, $decision, $reason, $urgent);
        $this->_json($res);
    }

    /* ============================ prerequisites (read-only) =============== */

    public function prerequisites() {
        $this->_check_feature();
        $this->permission_check('print_view');
        $job_id = (int)$this->input->get('job_id', TRUE);
        $this->_json($this->print->production_prerequisites($job_id));
    }
}
