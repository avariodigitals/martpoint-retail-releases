<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * RestaurantOS workspace controller.
 *
 * The restaurant-first shell for the restaurant/food industry presets. It does
 * NOT own a second sales ledger, catalogue or customer master — every figure
 * and list below reads the same tables the retail screens use (db_sales,
 * db_online_orders, db_kitchen_orders, db_tables, db_warehouse), so walk-in
 * and online orders report into one picture.
 *
 * Gating: feature flag `restaurant_workspace` (declared by the restaurant and
 * food presets) plus the per-screen permissions, which mirror the permissions
 * the destination controllers themselves check.
 */
class Restaurantos extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load_global();
        if (!function_exists('mp_feature_enabled') || !mp_feature_enabled('restaurant_workspace')) {
            $this->show_feature_not_activated('restaurant_workspace',
                'Switch on the RestaurantOS Workspace from Business Profile to open the restaurant workspace.');
        }
        $this->load->model('dashboard_model');
    }

    private function _render($page_title, $view, $data = []) {
        $d = $this->data ?? [];
        $d['page_title'] = $page_title;
        $d = array_merge($d, $data);
        $d['content'] = $this->load->view($view, $d, TRUE);
        $this->load->view('mp_layout', $d);
    }

    /**
     * Outlet switcher. Writes the same session key the retail dashboard uses,
     * so choosing an outlet here and choosing one on the dashboard cannot
     * drift apart, and validates against this store's own warehouses.
     */
    private function _selected_branch() {
        $store_id = get_current_store_id();
        $allowed = $this->_allowed_branch_ids();
        if ($this->input->get('branch_id') !== NULL) {
            $branch = (string) $this->input->get('branch_id');
            if ($branch !== '') {
                $ok = $this->db->where('id', (int) $branch)->where('store_id', $store_id)
                    ->count_all_results('db_warehouse') > 0;
                if ($ok && $allowed !== null && !in_array((int) $branch, $allowed, true)) {
                    $this->show_access_denied_page();
                    return '';
                }
                if (!$ok) $branch = '';
            }
            $this->session->set_userdata('selected_branch_id', $branch);
            return $branch;
        }
        $branch = (string) ($this->session->userdata('selected_branch_id') ?: '');
        if ($branch !== '') {
            $ok = $this->db->where('id', (int) $branch)->where('store_id', $store_id)
                ->count_all_results('db_warehouse') > 0;
            if (!$ok) {
                $branch = '';
                $this->session->set_userdata('selected_branch_id', '');
            }
            if ($branch !== '' && $allowed !== null && !in_array((int) $branch, $allowed, true)) {
                $this->session->set_userdata('selected_branch_id', '');
                $branch = '';
            }
        }
        if ($branch === '' && $allowed !== null && count($allowed) === 1) {
            $branch = (string) $allowed[0];
            $this->session->set_userdata('selected_branch_id', $branch);
        }
        return $branch;
    }

    /**
     * Null means consolidated access (admin/store owner). Non-admin staff are
     * restricted to the warehouses explicitly assigned in db_userswarehouses.
     */
    private function _allowed_branch_ids() {
        return function_exists('mp_allowed_warehouse_ids')
            ? mp_allowed_warehouse_ids(get_current_store_id())
            : null;
    }

    /** Apply the same branch scope to every query in this workspace. */
    private function _apply_branch_scope($branch, $column, $has_column = true) {
        if ($branch !== '') {
            if (!$has_column) {
                $this->db->where('1 = 0', null, false);
                return;
            }
            $this->db->where($column, (int) $branch);
            return;
        }
        $allowed = $this->_allowed_branch_ids();
        if ($allowed === null) return;
        if (!$has_column || empty($allowed)) {
            $this->db->where('1 = 0', null, false);
            return;
        }
        $this->db->where_in($column, $allowed);
    }

    /** Outlet rows for the switcher — only when the store really has several. */
    private function _branch_options() {
        $options = [];
        if (!warehouse_module() || !$this->db->table_exists('db_warehouse')) return $options;
        $rows = $this->db->where('store_id', get_current_store_id())->where('status', 1)
            ->order_by('warehouse_name', 'asc')->limit(24)->get('db_warehouse')->result();
        $allowed = $this->_allowed_branch_ids();
        if ($allowed !== null) {
            $rows = $this->db->where('store_id', get_current_store_id())->where('status', 1)->where_in('id', $allowed)
                ->order_by('warehouse_name', 'asc')->limit(24)->get('db_warehouse')->result();
        }
        if (count($rows) < 2) return $options;
        foreach ($rows as $w) {
            $options[] = ['id' => (int) $w->id, 'name' => $w->warehouse_name];
        }
        return $options;
    }

    /** Ranges the overview understands, mapped to the switcher labels. */
    const RANGES = [
        'Today'     => 'Today',
        '7Days'     => '7 days',
        '30Days'    => '30 days',
        'ThisMonth' => 'This month',
    ];

    /** How far back the "right now" kitchen boards look. Older open tickets are
     *  counted separately as backlog rather than shown as live service. */
    const LIVE_WINDOW_HOURS = 12;

    /* ========================== Restaurant Overview ======================= */

    /**
     * One scoped view of the canonical sales ledger for the overview.
     *
     * Legacy sales without source_channel are walk-in sales. They are deliberately
     * classified here rather than excluded, so the channel totals always
     * reconcile to the financial ledger total.
     */
    private function _sales_summary($store_id, $from, $to, $branch = '') {
        $empty = [
            'total' => 0.0, 'paid' => 0.0, 'outstanding' => 0.0,
            'count' => 0, 'channels' => [],
        ];
        $this->db->select(
            "source_channel, COUNT(*) AS cnt,
             COALESCE(SUM(grand_total),0) AS total,
             COALESCE(SUM(CASE WHEN payment_status = 'Paid' THEN grand_total ELSE COALESCE(paid_amount,0) END),0) AS paid,
             COALESCE(SUM(GREATEST(COALESCE(grand_total,0)-CASE WHEN payment_status = 'Paid' THEN grand_total ELSE COALESCE(paid_amount,0) END,0)),0) AS outstanding",
            FALSE
        );
        $this->db->where('store_id', $store_id);
        $this->db->where('sales_status', 'Final');
        if ($this->db->field_exists('status', 'db_sales')) $this->db->where('status', 1);
        $this->db->where('sales_date >=', $from);
        $this->db->where('sales_date <=', $to);
        if ($branch !== '') {
            $this->db->where('warehouse_id', (int) $branch);
        } else {
            $allowed = $this->_allowed_branch_ids();
            if ($allowed !== null) {
                if (empty($allowed)) return $empty;
                $this->db->where_in('warehouse_id', $allowed);
            }
        }
        $rows = $this->db->group_by('source_channel')->get('db_sales')->result();
        $summary = $empty;
        $channels = [
            'Walk In' => ['cnt' => 0, 'total' => 0.0, 'paid' => 0.0, 'outstanding' => 0.0],
            'Online' => ['cnt' => 0, 'total' => 0.0, 'paid' => 0.0, 'outstanding' => 0.0],
            'QR' => ['cnt' => 0, 'total' => 0.0, 'paid' => 0.0, 'outstanding' => 0.0],
            'Other' => ['cnt' => 0, 'total' => 0.0, 'paid' => 0.0, 'outstanding' => 0.0],
        ];
        foreach ($rows as $row) {
            $source = strtolower(trim((string) $row->source_channel));
            $channel = $source === 'web' ? 'Online' : ($source === 'qr' ? 'QR' : ($source === 'walk_in' || $source === '' ? 'Walk In' : 'Other'));
            $cnt = (int) $row->cnt;
            $total = (float) $row->total;
            $paid = (float) $row->paid;
            $outstanding = (float) $row->outstanding;
            $summary['count'] += $cnt;
            $summary['total'] += $total;
            $summary['paid'] += $paid;
            $summary['outstanding'] += $outstanding;
            $channels[$channel]['cnt'] += $cnt;
            $channels[$channel]['total'] += $total;
            $channels[$channel]['paid'] += $paid;
            $channels[$channel]['outstanding'] += $outstanding;
        }
        foreach ($channels as $name => $values) {
            if ($values['cnt'] > 0) $summary['channels'][$name] = $values;
        }
        return $summary;
    }

    public function index() {
        $this->permission_check('dashboard_view');
        $store_id = get_current_store_id();
        $branch = $this->_selected_branch();

        $range_key = (string) $this->input->get('range');
        if (!isset(self::RANGES[$range_key])) $range_key = 'Today';
        $info = $this->dashboard_model->get_range_info($range_key);
        $from = $info['from'];
        $to = $info['to'];

        // All financial headline values come from the canonical sales ledger.
        $sales = $this->_sales_summary($store_id, $from, $to, $branch);
        $previous_sales = $this->_sales_summary($store_id, $info['prev_from'], $info['prev_to'], $branch);
        $change = 0;
        if ($previous_sales['total'] > 0) {
            $change = round((($sales['total'] - $previous_sales['total']) / $previous_sales['total']) * 100, 1);
        }
        $perf = ['today' => $sales['total'], 'yesterday' => $previous_sales['total'], 'change' => $change];

        // Counter invoices, covers and open balances, one pass over the ledger.
        $counter = ['cnt' => 0, 'dinein' => 0, 'open_cnt' => 0, 'open_total' => 0.0];
        $this->db->select(
            "COUNT(*) AS cnt,
             SUM(CASE WHEN table_id IS NOT NULL AND table_id > 0 THEN 1 ELSE 0 END) AS dinein,
             SUM(CASE WHEN payment_status IN ('Unpaid','Partial') THEN 1 ELSE 0 END) AS open_cnt,
             COALESCE(SUM(CASE WHEN payment_status IN ('Unpaid','Partial') THEN grand_total ELSE 0 END),0) AS open_total", FALSE
        );
        $this->db->where('store_id', $store_id);
        $this->db->where('sales_status', 'Final');
        if ($this->db->field_exists('status', 'db_sales')) $this->db->where('status', 1);
        $this->db->where('sales_date >=', $from);
        $this->db->where('sales_date <=', $to);
        $this->_apply_branch_scope($branch, 'warehouse_id');
        $row = $this->db->get('db_sales')->row();
        if ($row) {
            $counter = [
                'cnt'        => (int) $row->cnt,
                'dinein'     => (int) $row->dinein,
                'open_cnt'   => (int) $row->open_cnt,
                'open_total' => (float) $row->open_total,
            ];
        }

        $online = ['cnt' => 0, 'sum' => 0.0, 'open' => 0, 'ready' => 0];
        if ($this->db->table_exists('db_online_orders')) {
            // An online order now names its outlet (migration 4.0.9.162), so the
            // outlet switcher has to move these figures the same way it moves the
            // counter ones — otherwise a focused board would still report the
            // whole group's online volume. Orders taken before locations were
            // recorded carry no outlet and only appear under All.
            $online_outlet_col = $this->db->field_exists('warehouse_id', 'db_online_orders');
            $this->db->select('COUNT(*) AS cnt, COALESCE(SUM(grand_total),0) AS total', FALSE);
            $this->db->where('store_id', $store_id);
            if ($this->db->field_exists('status', 'db_online_orders')) $this->db->where('status', 1);
            $this->db->where('created_at >=', $from . ' 00:00:00');
            $this->db->where('created_at <=', $to . ' 23:59:59');
            $this->_apply_branch_scope($branch, 'warehouse_id', $online_outlet_col);
            $orow = $this->db->get('db_online_orders')->row();
            if ($orow) {
                $online['cnt'] = (int) $orow->cnt;
                $online['sum'] = (float) $orow->total;
            }

            // The open backlog is "still owed", not "still in this period".
            $this->db->where('store_id', $store_id);
            if ($this->db->field_exists('status', 'db_online_orders')) $this->db->where('status', 1);
            $this->db->where_in('order_status', ['pending', 'processing']);
            $this->_apply_branch_scope($branch, 'warehouse_id', $online_outlet_col);
            $online['open'] = $this->db->count_all_results('db_online_orders');

            $this->db->where('store_id', $store_id);
            if ($this->db->field_exists('status', 'db_online_orders')) $this->db->where('status', 1);
            $this->db->where('order_status', 'ready');
            $this->_apply_branch_scope($branch, 'warehouse_id', $online_outlet_col);
            $online['ready'] = $this->db->count_all_results('db_online_orders');
        }

        $avg_ticket = $sales['count'] > 0 ? round($sales['total'] / $sales['count'], 2) : 0.0;

        // Kitchen queue — only for stores that run a kitchen, and synced first
        // so this period's counter sales are represented, exactly as the
        // Kitchen Display screen itself does on open.
        //
        // Two figures are kept apart on purpose: what is open *now* (tickets
        // raised in the live window) and the all-time queue, because tickets
        // left unclosed for weeks would otherwise read as today's rush.
        $kds       = ['new' => 0, 'preparing' => 0, 'ready' => 0, 'served' => 0, 'cancelled' => 0];
        $kds_live  = $kds;
        $kds_stale = 0;
        $cooking = [];
        $ready = [];
        if (mp_feature_enabled('kitchen_workflow')) {
            try {
                $this->load->model('kitchen_model', 'kitchen');
                $this->kitchen->sync_new_orders($store_id);
                $since = date('Y-m-d H:i:s', time() - self::LIVE_WINDOW_HOURS * 3600);
                $kds_has_warehouse = $this->db->field_exists('warehouse_id', 'db_kitchen_orders');
                $kds_sql = "SELECT kds_status, COUNT(*) AS cnt,
                                    SUM(created_at >= ?) AS live
                             FROM db_kitchen_orders WHERE store_id = ?";
                $kds_params = [$since, $store_id];
                $allowed = $this->_allowed_branch_ids();
                if ($branch !== '') {
                    if ($kds_has_warehouse) {
                        $kds_sql .= " AND warehouse_id = ?";
                        $kds_params[] = (int) $branch;
                    } else {
                        $kds_sql .= " AND 1 = 0";
                    }
                } elseif ($allowed !== null) {
                    if (empty($allowed) || !$kds_has_warehouse) {
                        $kds_sql .= " AND 1 = 0";
                    } else {
                        $kds_sql .= " AND warehouse_id IN (" . implode(',', array_fill(0, count($allowed), '?')) . ")";
                        $kds_params = array_merge($kds_params, array_map('intval', $allowed));
                    }
                }
                $kds_sql .= " GROUP BY kds_status";
                $q = $this->db->query($kds_sql, $kds_params);
                foreach (($q ? $q->result() : []) as $r) {
                    $kds[$r->kds_status] = (int) $r->cnt;
                    $kds_live[$r->kds_status] = (int) $r->live;
                }
                $this->db->where('store_id', $store_id);
                $this->db->where_in('kds_status', ['new', 'preparing', 'ready']);
                $this->db->where('created_at <', $since);
                $this->_apply_branch_scope($branch, 'warehouse_id', $kds_has_warehouse);
                $kds_stale = $this->db->count_all_results('db_kitchen_orders');
            } catch (Throwable $e) {
                log_message('error', 'RestaurantOS overview: kitchen counts failed — ' . $e->getMessage());
            }
            $tickets = $this->_kds_tickets($store_id, $branch);
            $cooking = $tickets['cooking'];
            $ready   = $tickets['ready'];
        }

        // Front of house (only when the store runs tables).
        $tables = null;
        $table_rows = [];
        if (mp_feature_enabled('table_management')) {
            try {
                $this->load->model('tables_model', 'tables');
                $tables = $this->tables->count_by_status($store_id);
                foreach (array_slice($this->tables->get_all($store_id), 0, 24) as $t) {
                    $table_rows[] = [
                        'name'     => $t->table_name,
                        'zone'     => trim((string) ($t->zone ?? '')),
                        'capacity' => (int) ($t->capacity ?? 0),
                        'status'   => $t->status,
                    ];
                }
            } catch (Throwable $e) {
                log_message('error', 'RestaurantOS overview: table counts failed — ' . $e->getMessage());
            }
        }

        $low_stock = 0;
        try { $low_stock = (int) $this->dashboard_model->get_low_stock_count($branch); } catch (Throwable $e) {}

        $day_parts  = $this->_day_parts($store_id, $from, $to, $branch);
        $outlets    = $this->_outlets_today($store_id, $from, $to);
        $recent     = array_slice($this->_merge_orders($store_id, $from, 60, $to, $branch), 0, 6);
        $insights   = $this->_insights($perf, $counter, $online, $kds_live, $kds_stale, $cooking, $ready, $low_stock, $range_key);

        $nav_items = function_exists('mp_restaurant_nav_items') ? mp_restaurant_nav_items() : [];
        $presets = function_exists('mp_get_restaurant_nav_presets') ? mp_get_restaurant_nav_presets() : [];
        $preset_key = function_exists('mp_restaurant_nav_preset') ? mp_restaurant_nav_preset() : 'quick_service';

        $this->_render('Restaurant Overview', 'restaurantos/overview', [
            'range_key'    => $range_key,
            'ranges'       => self::RANGES,
            'range_label'  => $info['label'],
            'branch'       => $branch,
            'branch_opts'  => $this->_branch_options(),
            'branch_can_view_all' => $this->_allowed_branch_ids() === null,
            'perf'         => $perf,
            'sales'        => $sales,
            'counter'      => $counter,
            'online'       => $online,
            'avg_ticket'   => $avg_ticket,
            'kds'          => $kds,
            'kds_live'     => $kds_live,
            'kds_stale'    => $kds_stale,
            'live_hours'   => self::LIVE_WINDOW_HOURS,
            'cooking'      => $cooking,
            'ready'        => $ready,
            'tables'       => $tables,
            'table_rows'   => $table_rows,
            'low_stock'    => $low_stock,
            'day_parts'    => $day_parts,
            'outlets'      => $outlets,
            'recent'       => $recent,
            'insights'     => $insights,
            'nav_items'    => $nav_items,
            'preset'       => $presets[$preset_key] ?? [],
            'preset_key'   => $preset_key,
            'can_settings' => $this->permissions('business_profile'),
        ]);
    }

    /** Ticket age in the words a floor manager would use. */
    private function _age_text($minutes) {
        $minutes = (int) $minutes;
        if ($minutes < 60) return $minutes . 'm';
        if ($minutes < 120) return '1h ' . ($minutes % 60) . 'm';
        if ($minutes < 1440) return floor($minutes / 60) . 'h';
        return floor($minutes / 1440) . 'd';
    }

    /**
     * Kitchen tickets that are still open, split into the two boards the pass
     * actually works from: what is ageing, and what is waiting to be handed
     * over. Minutes are measured from the ticket's own created_at.
     */
    private function _kds_tickets($store_id, $branch = '') {
        $empty = ['cooking' => [], 'ready' => []];
        if (!$this->db->table_exists('db_kitchen_orders')) return $empty;

        $has_online = $this->db->field_exists('online_order_id', 'db_kitchen_orders');
        $has_ktable = $this->db->field_exists('table_id', 'db_kitchen_orders');
        $has_kwarehouse = $this->db->field_exists('warehouse_id', 'db_kitchen_orders');
        $has_tables = $this->db->table_exists('db_tables');

        $tbl_expr = $has_online ? 'o.table_number' : 'NULL';
        $tbl_join = '';
        if ($has_tables) {
            $tbl_key = $has_ktable ? 'IFNULL(NULLIF(s.table_id, 0), k.table_id)' : 'NULLIF(s.table_id, 0)';
            $tbl_expr = 'COALESCE(t.table_name, ' . $tbl_expr . ')';
            $tbl_join = '
                LEFT JOIN db_tables t ON t.id = ' . $tbl_key;
        }

        $warehouse_expr = $has_kwarehouse ? 'k.warehouse_id' : 's.warehouse_id';
        if (!$has_kwarehouse && $has_online) $warehouse_expr = 'COALESCE(s.warehouse_id, o.warehouse_id)';
        $sql = 'SELECT k.id, k.kds_status, k.created_at,
                       COALESCE(s.sales_code' . ($has_online ? ', o.order_code' : '') . ', \'\') AS ref,
                       COALESCE(c.customer_name' . ($has_online ? ', o.customer_name' : '') . ', \'\') AS customer,
                       ' . $tbl_expr . ' AS tbl
                FROM db_kitchen_orders k
                LEFT JOIN db_sales s ON s.id = k.sales_id
                LEFT JOIN db_customers c ON c.id = s.customer_id'
                . ($has_online ? '
                LEFT JOIN db_online_orders o ON o.id = k.online_order_id' : '')
                . $tbl_join . '
                WHERE k.store_id = ? AND k.kds_status IN (\'new\',\'preparing\',\'ready\')
                  AND k.created_at >= ?';
        $params = [$store_id, date('Y-m-d H:i:s', time() - self::LIVE_WINDOW_HOURS * 3600)];
        if ($branch !== '') {
            $sql .= ' AND ' . $warehouse_expr . ' = ?';
            $params[] = (int) $branch;
        } else {
            $allowed = $this->_allowed_branch_ids();
            if ($allowed !== null) {
                if (empty($allowed)) {
                    $sql .= ' AND 1 = 0';
                } else {
                    $sql .= ' AND ' . $warehouse_expr . ' IN (' . implode(',', array_fill(0, count($allowed), '?')) . ')';
                    $params = array_merge($params, array_map('intval', $allowed));
                }
            }
        }
        $sql .= '
                ORDER BY FIELD(k.kds_status, \'ready\', \'preparing\', \'new\'), k.created_at ASC
                LIMIT 40';
        try {
            $q = $this->db->query($sql, $params);
        } catch (Throwable $e) {
            log_message('error', 'RestaurantOS overview: ticket board query failed — ' . $e->getMessage());
            return $empty;
        }

        $cooking = [];
        $ready = [];
        foreach (($q ? $q->result() : []) as $t) {
            $minutes = max(0, (int) floor((time() - strtotime($t->created_at)) / 60));
            $item = [
                'id'      => (int) $t->id,
                'ref'     => $t->ref !== '' ? $t->ref : ('#' . $t->id),
                'who'     => trim((string) ($t->tbl ?: '')) !== '' ? $t->tbl
                            : (trim((string) ($t->customer ?: '')) !== '' ? $t->customer : 'Walk In'),
                'status'  => $t->kds_status,
                'minutes' => $minutes,
                'age'     => $this->_age_text($minutes),
            ];
            if ($t->kds_status === 'ready') {
                if (count($ready) < 6) $ready[] = $item;
            } else {
                if (count($cooking) < 8) $cooking[] = $item;
            }
        }
        return ['cooking' => $cooking, 'ready' => $ready];
    }

    /**
     * Takings bucketed by service period, straight from the ledger's own
     * created_time. These are time-of-day reporting groups, not order channels
     * or bookings — they simply describe when the money rang up.
     */
    private function _day_parts($store_id, $from, $to, $branch) {
        $buckets = [
            'Breakfast'   => ['min' => 0,  'max' => 10, 'total' => 0.0, 'cnt' => 0],
            'Lunch'       => ['min' => 11, 'max' => 14, 'total' => 0.0, 'cnt' => 0],
            'Dinner'      => ['min' => 15, 'max' => 20, 'total' => 0.0, 'cnt' => 0],
            'Late Night'  => ['min' => 21, 'max' => 23, 'total' => 0.0, 'cnt' => 0],
        ];
        $sql = "SELECT HOUR(created_time) AS hr,
                       COALESCE(SUM(grand_total),0) AS takings, COUNT(*) AS cnt
                FROM db_sales
                WHERE store_id = ? AND sales_status = 'Final'
                  AND status = 1
                  AND sales_date >= ? AND sales_date <= ?";
        $params = [$store_id, $from, $to];
        if (!empty($branch)) {
            $sql .= " AND warehouse_id = ?";
            $params[] = (int) $branch;
        } else {
            $allowed = $this->_allowed_branch_ids();
            if ($allowed !== null) {
                if (empty($allowed)) {
                    $sql .= " AND 1 = 0";
                } else {
                    $sql .= " AND warehouse_id IN (" . implode(',', array_fill(0, count($allowed), '?')) . ")";
                    $params = array_merge($params, array_map('intval', $allowed));
                }
            }
        }
        $sql .= " GROUP BY hr";
        try {
            $q = $this->db->query($sql, $params);
        } catch (Throwable $e) {
            log_message('error', 'RestaurantOS overview: day-part query failed — ' . $e->getMessage());
            return [];
        }
        $unlogged = ['total' => 0.0, 'cnt' => 0];
        foreach (($q ? $q->result() : []) as $r) {
            if ($r->hr === null) { $unlogged['total'] += (float)$r->takings; $unlogged['cnt'] += (int)$r->cnt; continue; }
            $hour = (int) $r->hr;
            foreach ($buckets as $name => $b) {
                if ($hour >= $b['min'] && $hour <= $b['max']) {
                    $buckets[$name]['total'] += (float) $r->takings;
                    $buckets[$name]['cnt']   += (int) $r->cnt;
                    break;
                }
            }
        }
        $sum = array_sum(array_column($buckets, 'total')) + $unlogged['total'];
        $peak = '';
        $peak_total = 0;
        $out = [];
        foreach ($buckets as $name => $b) {
            $pct = $sum > 0 ? round(($b['total'] / $sum) * 100) : 0;
            $out[] = ['name' => $name, 'total' => $b['total'], 'cnt' => $b['cnt'], 'pct' => $pct];
            if ($b['total'] > $peak_total) { $peak_total = $b['total']; $peak = $name; }
        }
        if ($unlogged['cnt'] > 0) {
            $out[] = ['name' => 'Unstamped', 'total' => $unlogged['total'], 'cnt' => $unlogged['cnt'],
                      'pct' => $sum > 0 ? round(($unlogged['total'] / $sum) * 100) : 0];
        }
        return ['rows' => $out, 'peak' => $peak_total > 0 ? $peak : '', 'sum' => $sum];
    }

    /**
     * Per-outlet takings today — only built when the store genuinely runs more
     * than one outlet, and only when the warehouse module is on, so this can
     * never advertise outlets the side rail would refuse to open.
     */
    private function _outlets_today($store_id, $from = null, $to = null) {
        $outlets = [];
        if (!warehouse_module() || !$this->db->table_exists('db_warehouse')) return $outlets;
        $from = $from ?: date('Y-m-d');
        $to = $to ?: $from;
        $warehouse_rows = $this->db->where('store_id', $store_id)
            ->order_by('warehouse_name', 'asc')->limit(24)->get('db_warehouse')->result();
        $allowed = $this->_allowed_branch_ids();
        if ($allowed !== null) {
            $warehouse_rows = array_values(array_filter($warehouse_rows, function ($w) use ($allowed) {
                return in_array((int) $w->id, $allowed, true);
            }));
        }
        if (count($warehouse_rows) <= 1) return $outlets;

        $by_branch = [];
        $q = $this->db->query(
            "SELECT warehouse_id, COALESCE(SUM(grand_total),0) AS takings, COUNT(*) AS cnt
             FROM db_sales
             WHERE store_id = ? AND sales_date >= ? AND sales_date <= ? AND sales_status = 'Final' AND status = 1
             GROUP BY warehouse_id",
            [$store_id, $from, $to]
        );
        foreach (($q ? $q->result() : []) as $r) {
            $by_branch[(int) $r->warehouse_id] = $r;
        }
        $sum = 0.0;
        foreach ($warehouse_rows as $w) {
            $sum += (float) ($by_branch[(int) $w->id]->takings ?? 0);
        }
        foreach ($warehouse_rows as $w) {
            $agg = $by_branch[(int) $w->id] ?? null;
            $takings = (float) ($agg->takings ?? 0);
            $outlets[] = [
                'id'      => (int) $w->id,
                'name'    => $w->warehouse_name,
                'takings' => $takings,
                'orders'  => (int) ($agg->cnt ?? 0),
                'pct'     => $sum > 0 ? round(($takings / $sum) * 100) : 0,
            ];
        }
        usort($outlets, function ($a, $b) { return $b['takings'] <=> $a['takings']; });
        return $outlets;
    }

    /**
     * The banner at the top of the overview. Every line is a real signal from
     * the queues below it — nothing is inferred, and nothing is shown when the
     * service is genuinely quiet.
     */
    private function _insights($perf, $counter, $online, $kds, $kds_stale, $cooking, $ready, $low_stock, $range_key) {
        $notes = [];
        $oldest = 0;
        foreach ($cooking as $t) { $oldest = max($oldest, $t['minutes']); }
        if ($oldest >= 30) {
            $notes[] = ['level' => 'warn', 'icon' => 'fa-fire', 'label' => 'Kitchen is running behind',
                'text' => 'The oldest ticket in this service has been going ' . $this->_age_text($oldest) . '.',
                'link' => 'operations/kitchen', 'link_label' => 'Open the kitchen display'];
        }
        if (!empty($ready)) {
            $notes[] = ['level' => 'info', 'icon' => 'fa-bell', 'label' => 'Ready for handover',
                'text' => $kds['ready'] . ' ' . ($kds['ready'] === 1 ? 'ticket is' : 'tickets are') . ' plated and still sitting in the pass.',
                'link' => 'operations/kitchen', 'link_label' => 'Clear the pass'];
        }
        if ($online['open'] > 0) {
            $notes[] = ['level' => 'info', 'icon' => 'fa-globe', 'label' => 'Online orders waiting',
                'text' => $online['open'] . ' ' . ($online['open'] === 1 ? 'order has' : 'orders have') . ' not been completed yet.',
                'link' => 'online_store/orders', 'link_label' => 'Review them'];
        }
        if (!empty($online['ready'])) {
            $notes[] = ['level' => 'ok', 'icon' => 'fa-check-circle', 'label' => 'Ready for handover',
                'text' => $online['ready'] . ' online ' . ($online['ready'] === 1 ? 'order is' : 'orders are') . ' ready now — including shelf-only orders that skipped the kitchen.',
                'link' => 'online_store/orders', 'link_label' => 'Open order desk'];
        }
        if ($counter['open_cnt'] > 0) {
            $notes[] = ['level' => 'warn', 'icon' => 'fa-money', 'label' => 'Balances still open',
                'text' => $counter['open_cnt'] . ' ' . ($counter['open_cnt'] === 1 ? 'invoice is' : 'invoices are') . ' not fully settled in this period.',
                'link' => 'sales', 'link_label' => 'See invoices'];
        }
        if ($low_stock > 0) {
            $notes[] = ['level' => 'warn', 'icon' => 'fa-exclamation-triangle', 'label' => 'Low stock',
                'text' => $low_stock . ' ' . ($low_stock === 1 ? 'item is' : 'items are') . ' at or below their reorder level.',
                'link' => 'warehouse', 'link_label' => 'Check stock'];
        }
        if ($kds_stale > 0) {
            $notes[] = ['level' => 'warn', 'icon' => 'fa-clock-o', 'label' => 'Kitchen backlog',
                'text' => $kds_stale . ' ticket' . ($kds_stale === 1 ? '' : 's') . ' from earlier services are still open on the display and are not part of today\'s queue.',
                'link' => 'operations/kitchen', 'link_label' => 'Clear them off'];
        }
        if (!$notes && $counter['cnt'] === 0 && $online['cnt'] === 0) {
            $notes[] = ['level' => 'ok', 'icon' => 'fa-coffee', 'label' => 'Nothing to chase',
                'text' => 'No orders have come through' . ($range_key === 'Today' ? ' today' : ' in this period') . ' yet, and every queue is clear.',
                'link' => 'pos', 'link_label' => 'Open Walk In'];
        }
        if (!$notes && $perf['change'] <= -10) {
            // Point at the sales report only when the staff member can open it;
            // otherwise the order desk shows the same period without a denial.
            $can_report = $this->permissions('sales_report') || is_store_admin();
            $notes[] = ['level' => 'warn', 'icon' => 'fa-arrow-down', 'label' => 'Takings are down',
                'text' => 'This period is ' . abs((float) $perf['change']) . '% behind the one before it.',
                'link' => $can_report ? 'reports/sales' : 'restaurantos/orders',
                'link_label' => $can_report ? 'Read the sales report' : 'Look at this period\'s orders'];
        }
        return array_slice($notes, 0, 3);
    }

    /* ================================ Orders ============================== */

    /**
     * One list of every recent order however it arrived — counter invoices
     * and online/QR orders side by side. Read-only: it reports the existing
     * ledgers, it does not transition anything (lifecycle writes land with
     * the unified order engine).
     */
    public function orders() {
        if (!($this->permissions('sales_view') || $this->permissions('online_store_orders') || is_store_admin())) {
            $this->show_access_denied_page();
        }
        $store_id = get_current_store_id();
        $branch = $this->_selected_branch();

        $range_key = (string) $this->input->get('range');
        if (!isset(self::RANGES[$range_key])) $range_key = 'Today';
        $info = $this->dashboard_model->get_range_info($range_key);

        $rows = $this->_merge_orders($store_id, $info['from'], 150, $info['to'], $branch);

        // The filter chips are built from what actually arrived, so the desk
        // can never offer a channel or a fulfilment type this store doesn't use.
        $fulfil_options = [];
        foreach ($rows as $r) {
            if (!isset($fulfil_options[$r['fulfilment']])) {
                $fulfil_options[$r['fulfilment']] = 0;
            }
            $fulfil_options[$r['fulfilment']]++;
        }
        ksort($fulfil_options);

        $source = (string) $this->input->get('source');
        $fulfil = (string) $this->input->get('fulfilment');
        $pay    = (string) $this->input->get('pay');
        $q      = trim((string) $this->input->get('q'));

        $filtered = [];
        foreach ($rows as $r) {
            if ($source === 'counter' && $r['channel'] !== 'counter') continue;
            if ($source === 'online' && $r['channel'] !== 'online') continue;
            if ($fulfil !== '' && $fulfil !== 'all' && $r['fulfilment'] !== $fulfil) continue;
            if ($pay === 'settled' && $r['settled'] !== true) continue;
            if ($pay === 'open' && $r['settled'] !== false) continue;
            if ($q !== '') {
                $needle = strtolower($q);
                $hay = strtolower($r['ref'] . ' ' . $r['customer']);
                if (strpos($hay, $needle) === false) continue;
            }
            $filtered[] = $r;
        }

        $summary = ['cnt' => count($filtered), 'total' => 0.0, 'settled' => 0.0, 'open' => 0.0,
                    'counter' => 0, 'online' => 0, 'unassigned' => 0];
        foreach ($filtered as $r) {
            $summary['total'] += $r['total'];
            if ($r['settled']) $summary['settled'] += $r['total']; else $summary['open'] += $r['total'];
            $summary[$r['channel']]++;
            if ((int) ($r['outlet'] ?? 0) <= 0) $summary['unassigned']++;
        }

        $this->_render('Orders', 'restaurantos/orders', [
            'rows'       => array_slice($filtered, 0, 200),
            'shown'      => count($filtered),
            'branch'     => $branch,
            'branch_opts'=> $this->_branch_options(),
            'branch_can_view_all' => $this->_allowed_branch_ids() === null,
            'range_key'  => $range_key,
            'ranges'     => self::RANGES,
            'range_label'=> $info['label'],
            'from'       => $info['from'],
            'to'         => $info['to'],
            'fulfil_options' => $fulfil_options,
            'source'     => $source,
            'fulfil'     => $fulfil,
            'pay'        => $pay,
            'q'          => $q,
            'summary'    => $summary,
            'filters_on' => ($source !== '' || ($fulfil !== '' && $fulfil !== 'all') || $pay !== '' || $q !== ''),
        ]);
    }

    /**
     * Merge the two order books into one time-sorted list. Both branches read
     * the ledgers as they already stand — db_sales is the financial record and
     * db_online_orders stays the online book; nothing here writes to either.
     */
    private function _merge_orders($store_id, $from_date, $limit_each = 150, $to_date = null, $branch = '') {
        $rows = [];

        // Counter sales (the financial ledger).
        $this->db->select('s.id, s.sales_code, s.sales_date, s.created_time, s.grand_total, s.payment_status, s.table_id, s.warehouse_id, c.customer_name');
        $this->db->from('db_sales s');
        $this->db->join('db_customers c', 'c.id = s.customer_id', 'left');
        $this->db->where('s.store_id', $store_id);
        $this->db->where('s.sales_status', 'Final');
        if ($this->db->field_exists('status', 'db_sales')) $this->db->where('s.status', 1);
        $this->db->where('s.sales_date >=', $from_date);
        if ($to_date !== null) $this->db->where('s.sales_date <=', $to_date);
        $this->_apply_branch_scope($branch, 's.warehouse_id');
        $this->db->order_by('s.sales_date', 'desc')->order_by('s.id', 'desc')->limit((int) $limit_each);
        foreach ($this->db->get()->result() as $s) {
            $settled = ($s->payment_status === 'Paid');
            $rows[] = [
                'id'        => (int) $s->id,
                'channel'   => 'counter',
                'time'      => $s->sales_date . ' ' . trim((string) ($s->created_time ?? '')),
                'ref'       => $s->sales_code,
                'source'    => 'Walk In',
                'fulfilment'=> !empty($s->table_id) ? 'Dine-in' : 'Takeaway',
                'customer'  => $s->customer_name ?: 'Walk-in',
                'total'     => (float) $s->grand_total,
                'pay'       => $s->payment_status ?: '',
                'settled'   => $settled,
                // A walk-in sale with no KDS ticket is a shelf/pass item: it
                // does not enter the kitchen. Any real kitchen ticket below
                // replaces this with Queued/Cooking/Ready/Handed over.
                'prep'      => 'ready_now',
                'outlet'    => (int) $s->warehouse_id,
                'link'      => 'sales/invoice/' . (int) $s->id,
            ];
        }

        // Online / QR orders.
        if ($this->db->table_exists('db_online_orders')) {
            $has_shipping = $this->db->field_exists('shipping_method', 'db_online_orders');
            $has_outlet = $this->db->field_exists('warehouse_id', 'db_online_orders');
            $this->db->select('id, order_code, created_at, grand_total, payment_status, order_status, customer_name, table_number'
                . ($has_shipping ? ', shipping_method' : '')
                . ($has_outlet ? ', warehouse_id' : ''));
            $this->db->from('db_online_orders');
            $this->db->where('store_id', $store_id);
            if ($this->db->field_exists('status', 'db_online_orders')) $this->db->where('status', 1);
            $this->db->where('created_at >=', $from_date . ' 00:00:00');
            if ($to_date !== null) $this->db->where('created_at <=', $to_date . ' 23:59:59');
            // Since migration 4.0.9.162 an online order names the outlet it was
            // placed against, so the outlet switcher can finally mean the same
            // thing for both channels.
            $this->_apply_branch_scope($branch, 'warehouse_id', $has_outlet);
            $this->db->order_by('created_at', 'desc')->limit((int) $limit_each);
            foreach ($this->db->get()->result() as $o) {
                if (!empty($o->table_number)) {
                    $fulfilment = 'Table QR';
                } elseif ($has_shipping && !empty($o->shipping_method)) {
                    $fulfilment = ucfirst(str_replace('_', ' ', $o->shipping_method));
                } else {
                    $fulfilment = 'Collection';
                }
                $rows[] = [
                    'id'        => 0,
                    'oid'       => (int) $o->id,
                    'channel'   => 'online',
                    'time'      => $o->created_at,
                    'ref'       => $o->order_code,
                    'source'    => !empty($o->table_number) ? 'QR' : 'Online',
                    'fulfilment'=> $fulfilment,
                    'customer'  => $o->customer_name ?: 'Guest',
                    'total'     => (float) $o->grand_total,
                    'pay'       => $o->payment_status ?: '',
                    'settled'   => ($o->payment_status === 'paid'),
                    // The order book uses the customer-facing status values;
                    // the desk uses the same short operational vocabulary as
                    // the KDS so a shelf-only order still reads as Ready.
                    'prep'      => [
                        'pending' => 'new', 'paid' => 'new',
                        'processing' => 'preparing', 'ready' => 'ready',
                        'completed' => 'served',
                    ][$o->order_status] ?? '',
                    'outlet'    => (int) ($o->warehouse_id ?? 0),
                    'link'      => 'online_store/order_detail/' . (int) $o->id,
                ];
            }
        }

        // Kitchen prep state for counter sales, when the KDS table exists.
        if ($this->db->table_exists('db_kitchen_orders')) {
            $has_refs = false;
            foreach ($rows as $r) {
                if ($r['channel'] === 'counter') { $has_refs = true; break; }
            }
            if ($has_refs) {
                $q = $this->db->query(
                    "SELECT s.sales_code, k.online_order_id, k.kds_status
                     FROM db_kitchen_orders k
                     LEFT JOIN db_sales s ON s.id = k.sales_id
                     WHERE k.store_id = ?
                       AND (s.sales_status = 'Final' OR k.online_order_id > 0)",
                    [$store_id]
                );
                $prep = [];
                foreach (($q ? $q->result() : []) as $k) {
                    if (!empty($k->sales_code)) $prep['sales:' . $k->sales_code] = $k->kds_status;
                    if (!empty($k->online_order_id)) $prep['online:' . (int) $k->online_order_id] = $k->kds_status;
                }
                foreach ($rows as $i => $r) {
                    $key = $r['channel'] === 'counter'
                        ? 'sales:' . $r['ref']
                        : 'online:' . (int) ($r['oid'] ?? 0);
                    if (isset($prep[$key])) $rows[$i]['prep'] = $prep[$key];
                }
            }
        }

        usort($rows, function ($a, $b) {
            return strcmp($b['time'], $a['time']);
        });
        return $rows;
    }

    /* =============================== Settings ============================= */

    /**
     * Workspace navigation preset. Saved into industry_settings_json under
     * restaurant_workspace.nav_preset — written to the modular industry table
     * first and mirrored to the legacy columns, the same dual-write
     * Business_profile_model performs, so the profile readers cannot drift.
     */
    public function settings() {
        if (!($this->permissions('business_profile') || is_store_admin())) {
            $this->show_access_denied_page();
        }
        $store_id = get_current_store_id();
        $presets = mp_get_restaurant_nav_presets();

        if ($this->input->post('nav_preset')) {
            $key = $this->input->post('nav_preset', TRUE);
            if (!isset($presets[$key])) {
                $this->session->set_flashdata('error', 'Unknown navigation preset.');
                redirect('restaurantos/settings');
                return;
            }

            $profile = mp_get_store_profile();
            // Seed from the existing settings blob (exposed as 'settings' by
            // mp_get_store_profile) so saving the preset can never wipe
            // sibling namespaces like garden modes or workflow config.
            $json = (!empty($profile['settings']) && is_array($profile['settings'])) ? $profile['settings'] : [];
            $ws = isset($json['restaurant_workspace']) && is_array($json['restaurant_workspace'])
                ? $json['restaurant_workspace'] : [];
            $ws['nav_preset'] = $key;
            $json['restaurant_workspace'] = $ws;
            $encoded = json_encode($json);

            if ($this->db->table_exists('db_store_industry_settings')) {
                $exists = $this->db->where('store_id', $store_id)->count_all_results('db_store_industry_settings');
                if ($exists) {
                    $this->db->where('store_id', $store_id)
                        ->update('db_store_industry_settings', ['industry_settings_json' => $encoded]);
                } else {
                    $this->db->insert('db_store_industry_settings', [
                        'store_id' => $store_id,
                        'industry_type' => 'restaurant',
                        'industry_settings_json' => $encoded,
                    ]);
                }
            }
            if ($this->db->field_exists('industry_settings_json', 'db_store')) {
                $this->db->where('id', $store_id)->update('db_store', ['industry_settings_json' => $encoded]);
            }
            if ($this->db->table_exists('db_store_business_profile')) {
                $exists = $this->db->where('store_id', $store_id)->count_all_results('db_store_business_profile');
                if ($exists) {
                    $this->db->where('store_id', $store_id)
                        ->update('db_store_business_profile', ['industry_settings_json' => $encoded]);
                } else {
                    $this->db->insert('db_store_business_profile', [
                        'store_id' => $store_id,
                        'industry_settings_json' => $encoded,
                    ]);
                }
            }

            mp_audit_log('restaurantos', 'settings', null, 'Navigation preset set to ' . $presets[$key]['label']);
            $this->session->set_flashdata('success', 'Workspace layout updated — the navigation now follows the ' . $presets[$key]['label'] . ' preset.');
            redirect('restaurantos/settings');
            return;
        }

        $previews = [];
        $promises = [];
        foreach (array_keys($presets) as $k) {
            $previews[$k] = array_values(mp_restaurant_nav_items($k));
            // A layout can promise tables or outlets while this store's feature
            // flags withhold them. Say so next to the preview instead of
            // letting the picker look like it failed to apply.
            $held = [];
            if (!empty($presets[$k]['tables']) && !mp_feature_enabled('table_management')) {
                $held[] = 'Tables &amp; Reservations needs the Table Management feature switched on.';
            }
            if (!empty($presets[$k]['outlets'])) {
                if (!warehouse_module()) {
                    $held[] = 'Outlets needs the Warehouse &amp; Branches feature switched on.';
                } elseif (count($this->_branch_options()) === 0) {
                    $held[] = 'Outlets appears once this store has more than one outlet.';
                }
            }
            $promises[$k] = $held;
        }

        $this->_render('Restaurant Setup', 'restaurantos/settings', [
            'presets'  => $presets,
            'current'  => mp_restaurant_nav_preset(),
            // Each layout rendered through the same gating code the sidebar
            // uses, so the picker shows what the rail will really contain.
            'previews' => $previews,
            'promises' => $promises,
        ]);
    }
}
