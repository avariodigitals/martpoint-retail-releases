<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Perfume Model — Perfume Lab
 * Blending pipeline (raw materials -> blend -> macerate -> filter -> bottle),
 * maceration tracking, and the categorized wastage ledger.
 *
 * Reuses db_recipes (formulas), db_production_batches (blend batches) and the
 * stock adjustment engine; adds db_perfume_wastage for loss accounting.
 */
class Perfume_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->_ensure_tables();
    }

    private function _ensure_tables() {
        $migrations = [
            'db_perfume_wastage' => '4.0.9.24_perfumery_module.sql',
            'db_bottling_runs'   => '4.0.9.26_bottling_runs.sql',
        ];
        foreach ($migrations as $table => $file) {
            if ($this->db->table_exists($table)) continue;
            $this->_run_migration_file($file);
        }
        // Bottled-SKU link columns on db_items (fill_qty, bottle_item_id, capacity_ml)
        if (!$this->db->field_exists('fill_qty', 'db_items')) {
            $this->_run_migration_file('4.0.9.27_bottling_sku_link.sql');
        }
    }

    private function _run_migration_file($file) {
        $sql_path = APPPATH . '../updates/migrations/' . $file;
        if (file_exists($sql_path)) {
            $sql = file_get_contents($sql_path);
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $stmt) {
                if (!empty($stmt)) {
                    $this->db->query($stmt);
                }
            }
        } else {
            log_message('error', 'Missing perfumery migration SQL file: ' . $file);
        }
    }

    // ========== Wastage stage helpers ==========

    public static function wastage_stages() {
        return [
            'blending'   => 'Blending / Mixing',
            'maceration' => 'Maceration (Evaporation)',
            'filtering'  => 'Filtering',
            'bottling'   => 'Bottling & Filling',
            'decanting'  => 'Decanting / Samples',
            'breakage'   => 'Breakage',
            'tester'     => 'Counter Tester',
            'expired'    => 'Expired / Off-stock',
            'other'      => 'Other',
        ];
    }

    public static function stage_label($stage) {
        $stages = self::wastage_stages();
        return $stages[$stage] ?? ucfirst(str_replace('_', ' ', $stage));
    }

    public static function stage_badge($stage) {
        $map = [
            'blending' => 'primary', 'maceration' => 'warning',
            'filtering' => 'info', 'bottling' => 'primary',
            'decanting' => 'info', 'breakage' => 'danger',
            'tester' => 'info', 'expired' => 'danger', 'other' => 'default',
        ];
        return $map[$stage] ?? 'default';
    }

    // ========== Lab dashboard ==========

    /**
     * Headline figures for the Perfume Lab dashboard.
     */
    public function get_lab_stats($store_id = null) {
        $store_id = $store_id ?? get_current_store_id();
        $stats = [
            'active_batches'   => 0,
            'macerating'       => 0,
            'ready_this_week'  => 0,
            'formulas'         => 0,
            'waste_cost_month' => 0.0,
            'low_materials'    => 0,
        ];

        if ($this->db->table_exists('db_production_batches')) {
            $stats['active_batches'] = (int)$this->db
                ->where('store_id', $store_id)
                ->where_not_in('status', ['completed', 'cancelled'])
                ->count_all_results('db_production_batches');

            $stats['macerating'] = (int)$this->db
                ->where('store_id', $store_id)
                ->where('status', 'macerating')
                ->count_all_results('db_production_batches');

            $stats['ready_this_week'] = count(array_filter(
                $this->get_macerating($store_id),
                function ($b) { return $b->days_left !== null && $b->days_left <= 7; }
            ));
        }

        if ($this->db->table_exists('db_recipes')) {
            $stats['formulas'] = (int)$this->db
                ->where('store_id', $store_id)->where('status', 1)
                ->count_all_results('db_recipes');
        }

        if ($this->db->table_exists('db_perfume_wastage')) {
            $row = $this->db->select('COALESCE(SUM(total_cost),0) AS c')
                ->where('store_id', $store_id)
                ->where('status', 1)
                ->where("DATE_FORMAT(created_date,'%Y-%m') =", date('Y-m'))
                ->get('db_perfume_wastage')->row();
            $stats['waste_cost_month'] = (float)($row->c ?? 0);
        }

        // Raw materials (not_for_sale) at or below their alert level
        if ($this->db->field_exists('not_for_sale', 'db_items') && $this->db->field_exists('alert_qty', 'db_items')) {
            $stats['low_materials'] = (int)$this->db
                ->where('store_id', $store_id)->where('status', 1)
                ->where('not_for_sale', 1)->where('alert_qty >', 0)
                ->where('stock <= alert_qty', null, false)
                ->count_all_results('db_items');
        }

        return $stats;
    }

    /**
     * Production batches in the blending pipeline (not yet completed),
     * enriched with recipe info and maceration timing.
     */
    public function get_pipeline($store_id = null, $statuses = null) {
        $store_id = $store_id ?? get_current_store_id();
        if (!$this->db->table_exists('db_production_batches')) return [];
        if ($statuses === null) {
            $statuses = ['planned','sourcing','blending','macerating','filtering','bottling','ready'];
        }
        $batches = $this->db
            ->where('store_id', $store_id)
            ->where_in('status', $statuses)
            ->order_by('scheduled_date', 'asc')
            ->get('db_production_batches')->result();
        return $this->_enrich_batches($batches);
    }

    /**
     * Batches currently resting in maceration, with ready-date math.
     */
    public function get_macerating($store_id = null) {
        $store_id = $store_id ?? get_current_store_id();
        if (!$this->db->table_exists('db_production_batches')) return [];
        if (!$this->db->field_exists('maceration_started', 'db_production_batches')) return [];
        $batches = $this->db
            ->where('store_id', $store_id)
            ->where('status', 'macerating')
            ->order_by('maceration_started', 'asc')
            ->get('db_production_batches')->result();
        return $this->_enrich_batches($batches);
    }

    /**
     * Attach recipe names, required maceration days, ready date and days left.
     */
    private function _enrich_batches(array $batches) {
        if (empty($batches)) return $batches;
        $this->load->model('production_batches_model', 'pb');
        $has_mac = $this->db->field_exists('maceration_days', 'db_recipes');
        $today = new DateTime(date('Y-m-d'));

        foreach ($batches as $b) {
            $b->recipes = [];
            $required_days = isset($b->maceration_days) ? (int)$b->maceration_days : 0;
            $items = $this->pb->get_items($b->id);
            foreach ($items as $bi) {
                if ($bi->item_type === 'recipe_product') {
                    $rec = $this->db->where('id', $bi->item_id)->get('db_recipes')->row();
                    if ($rec) {
                        $b->recipes[] = $rec->name;
                        if ($has_mac && (int)$rec->maceration_days > $required_days) {
                            $required_days = (int)$rec->maceration_days;
                        }
                    }
                } elseif ($bi->item_type === 'custom_order' && $this->db->table_exists('db_custom_orders')) {
                    $co = $this->db->where('id', $bi->item_id)->get('db_custom_orders')->row();
                    if ($co) $b->recipes[] = 'Order ' . $co->order_code;
                }
            }
            $b->required_days = $required_days;

            $b->ready_date = null;
            $b->days_in = null;
            $b->days_left = null;
            if (!empty($b->maceration_started)) {
                $start = new DateTime($b->maceration_started);
                $b->days_in = max(0, $start->diff($today)->days);
                if ($required_days > 0) {
                    $b->ready_date = (clone $start)->modify('+' . $required_days . ' days')->format('Y-m-d');
                    $b->days_left = $required_days - $b->days_in;
                }
            }
        }
        return $batches;
    }

    // ========== Wastage ledger ==========

    /**
     * Record a physical loss: writes the ledger row AND posts a matching
     * negative stock adjustment so inventory and the ledger never diverge.
     * Returns the wastage row id, or false on failure.
     */
    public function log_wastage(array $data) {
        $store_id = $data['store_id'] ?? get_current_store_id();
        $warehouse_id = $data['warehouse_id'] ?? get_store_warehouse_id();
        $item_id = (int)($data['item_id'] ?? 0);
        $qty = (float)($data['qty'] ?? 0);
        if ($item_id <= 0 || $qty <= 0) return false;

        $item = $this->db->where('id', $item_id)->get('db_items')->row();
        if (!$item) return false;

        $unit_cost = isset($data['unit_cost']) && $data['unit_cost'] !== ''
            ? (float)$data['unit_cost'] : (float)$item->purchase_price;

        $record = [
            'store_id'     => $store_id,
            'warehouse_id' => $warehouse_id,
            'batch_id'     => !empty($data['batch_id']) ? (int)$data['batch_id'] : null,
            'item_id'      => $item_id,
            'item_name'    => $item->item_name,
            'stage'        => $data['stage'] ?? 'other',
            'qty'          => $qty,
            'unit_name'    => $data['unit_name'] ?? null,
            'unit_cost'    => $unit_cost,
            'total_cost'   => round($qty * $unit_cost, 2),
            'reason'       => $data['reason'] ?? null,
            'created_date' => date('Y-m-d'),
            'created_time' => date('H:i:s'),
            'created_by'   => $data['created_by'] ?? ($this->session->userdata('username') ?: 'System'),
            'status'       => 1,
        ];

        $this->db->trans_begin();
        try {
            // Stock adjustment first — same engine production uses.
            $adj = [
                'store_id'        => $store_id,
                'warehouse_id'    => $warehouse_id,
                'reference_no'    => 'WASTE-' . date('Ymd') . '-' . $item_id,
                'adjustment_date' => date('Y-m-d'),
                'adjustment_note' => 'Perfumery loss: ' . self::stage_label($record['stage']) . ($record['reason'] ? ' — ' . $record['reason'] : ''),
                'created_date'    => date('Y-m-d'),
                'created_time'    => date('H:i:s'),
                'created_by'      => $record['created_by'],
                'system_ip'       => '127.0.0.1',
                'system_name'     => 'Perfume Lab',
                'status'          => 1,
            ];
            if (!$this->db->insert('db_stockadjustment', $adj)) {
                throw new Exception('Failed to create stock adjustment');
            }
            $adjustment_id = $this->db->insert_id();
            if (!$adjustment_id) throw new Exception('Failed to get adjustment ID');

            $ok = $this->db->insert('db_stockadjustmentitems', [
                'store_id'       => $store_id,
                'warehouse_id'   => $warehouse_id,
                'adjustment_id'  => $adjustment_id,
                'item_id'        => $item_id,
                'adjustment_qty' => -$qty,
                'description'    => 'Perfumery loss: ' . self::stage_label($record['stage']),
                'status'         => 1,
            ]);
            if (!$ok) throw new Exception('Failed to insert adjustment item');

            $record['stock_adjustment_id'] = $adjustment_id;
            if (!$this->db->insert('db_perfume_wastage', $record)) {
                throw new Exception('Failed to write wastage record');
            }
            $wastage_id = $this->db->insert_id();

            // Refresh materialized stock on the item + warehouse aggregates.
            $this->load->model('pos_model');
            if (!$this->pos_model->update_items_quantity($item_id)) {
                throw new Exception('Failed to refresh item stock');
            }
            if (!update_warehouse_items([[$item_id]])) {
                throw new Exception('Failed to refresh warehouse stock');
            }

            $this->db->trans_commit();
            return $wastage_id;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Perfume_model::log_wastage failed: ' . $e->getMessage());
            return false;
        }
    }

    public function get_wastage($store_id = null, $limit = 50) {
        $store_id = $store_id ?? get_current_store_id();
        if (!$this->db->table_exists('db_perfume_wastage')) return [];
        return $this->db->select('w.*, b.batch_code')
            ->from('db_perfume_wastage w')
            ->join('db_production_batches b', 'b.id = w.batch_id', 'left')
            ->where('w.store_id', $store_id)
            ->where('w.status', 1)
            ->order_by('w.id', 'desc')
            ->limit($limit)
            ->get()->result();
    }

    /**
     * Waste economics: totals, cost by stage, and the costliest items.
     */
    public function get_wastage_summary($store_id = null) {
        $store_id = $store_id ?? get_current_store_id();
        $summary = ['total_cost' => 0.0, 'month_cost' => 0.0, 'by_stage' => [], 'top_items' => []];
        if (!$this->db->table_exists('db_perfume_wastage')) return $summary;

        $row = $this->db->select('COALESCE(SUM(total_cost),0) AS c')
            ->where('store_id', $store_id)->where('status', 1)
            ->get('db_perfume_wastage')->row();
        $summary['total_cost'] = (float)($row->c ?? 0);

        $row = $this->db->select('COALESCE(SUM(total_cost),0) AS c')
            ->where('store_id', $store_id)->where('status', 1)
            ->where("DATE_FORMAT(created_date,'%Y-%m') =", date('Y-m'))
            ->get('db_perfume_wastage')->row();
        $summary['month_cost'] = (float)($row->c ?? 0);

        $summary['by_stage'] = $this->db
            ->select('stage, COUNT(*) AS events, SUM(qty) AS qty_lost, SUM(total_cost) AS cost')
            ->where('store_id', $store_id)->where('status', 1)
            ->group_by('stage')->order_by('cost', 'desc')
            ->get('db_perfume_wastage')->result();

        $summary['top_items'] = $this->db
            ->select('item_id, item_name, SUM(qty) AS qty_lost, SUM(total_cost) AS cost, COUNT(*) AS events')
            ->where('store_id', $store_id)->where('status', 1)
            ->group_by('item_id, item_name')->order_by('cost', 'desc')->limit(8)
            ->get('db_perfume_wastage')->result();

        return $summary;
    }

    /**
     * Process variance: planned vs actual yield on recent production runs.
     * This is the "invisible" loss — what the blend shed during maceration
     * and transfer before it ever hit a bottle.
     */
    public function get_production_variance($store_id = null, $limit = 12) {
        $store_id = $store_id ?? get_current_store_id();
        if (!$this->db->table_exists('db_recipe_production_runs')) return [];
        $runs = $this->db->select('r.*, rc.name AS recipe_name, rc.yield_unit, b.batch_code')
            ->from('db_recipe_production_runs r')
            ->join('db_recipes rc', 'rc.id = r.recipe_id', 'left')
            ->join('db_production_batches b', 'b.id = r.batch_id', 'left')
            ->where('r.store_id', $store_id)
            ->order_by('r.run_date', 'desc')->limit($limit)
            ->get()->result();
        foreach ($runs as $run) {
            $planned = (float)$run->planned_qty;
            $actual = $run->actual_yield !== null ? (float)$run->actual_yield : null;
            $run->variance_qty = ($actual !== null) ? round($actual - $planned, 3) : null;
            $run->variance_pct = ($actual !== null && $planned > 0)
                ? round((($actual - $planned) / $planned) * 100, 1) : null;
        }
        return $runs;
    }

    /**
     * Items the lab can lose: raw materials (not_for_sale) plus finished
     * fragrances — spillage hits oils, breakage hits bottles.
     */
    public function get_losable_items($store_id = null) {
        $store_id = $store_id ?? get_current_store_id();
        return $this->db->select('a.id, a.item_name, a.stock, a.purchase_price, a.not_for_sale, u.unit_name')
            ->from('db_items a')
            ->join('db_units u', 'u.id = a.unit_id', 'left')
            ->where('a.store_id', $store_id)
            ->where('a.status', 1)
            ->where('a.service_bit', 0)
            ->order_by('a.item_name', 'asc')
            ->get()->result();
    }

    /**
     * Batches a loss can be attributed to (active pipeline only).
     */
    public function get_open_batches($store_id = null) {
        $store_id = $store_id ?? get_current_store_id();
        if (!$this->db->table_exists('db_production_batches')) return [];
        return $this->db->select('id, batch_code, batch_name, status')
            ->where('store_id', $store_id)
            ->where_not_in('status', ['completed', 'cancelled'])
            ->order_by('id', 'desc')
            ->get('db_production_batches')->result();
    }

    // ========== Bottling runs ==========

    /**
     * Bulk liquid items a batch can be bottled from: the product_item_id of
     * every recipe linked to the batch, enriched with unit + live stock.
     */
    public function get_batch_bulk_items($batch_id, $store_id = null, $warehouse_id = null) {
        $store_id = $store_id ?? get_current_store_id();
        $warehouse_id = $warehouse_id ?? get_store_warehouse_id();
        $items = $this->db->where('batch_id', $batch_id)
            ->where('item_type', 'recipe_product')
            ->get('db_production_batch_items')->result();
        $bulk = [];
        foreach ($items as $bi) {
            $recipe = $this->db->where('id', $bi->item_id)->get('db_recipes')->row();
            if (!$recipe || !$recipe->product_item_id || isset($bulk[$recipe->product_item_id])) continue;
            $item = $this->db->select('a.id, a.item_name, u.unit_name')
                ->from('db_items a')
                ->join('db_units u', 'u.id = a.unit_id', 'left')
                ->where('a.id', $recipe->product_item_id)
                ->get()->row();
            if (!$item) continue;
            $item->available = total_available_qty_items_of_warehouse($warehouse_id, $store_id, $item->id);
            $item->recipe_name = $recipe->name;
            $bulk[$item->id] = $item;
        }
        return array_values($bulk);
    }

    /**
     * Record a bottling run: takes empty bottles and bulk liquid out of
     * stock and puts the finished bottled product in — all inside one
     * stock adjustment so inventory and the ledger stay in lock-step.
     * Returns the run id, or false on failure.
     */
    public function record_bottling(array $data) {
        $store_id = $data['store_id'] ?? get_current_store_id();
        $warehouse_id = $data['warehouse_id'] ?? get_store_warehouse_id();
        $batch_id = (int)($data['batch_id'] ?? 0) ?: null;
        $product_item_id = (int)($data['product_item_id'] ?? 0);
        $bottle_item_id = (int)($data['bottle_item_id'] ?? 0);
        $bulk_item_id = (int)($data['bulk_item_id'] ?? 0);
        $fill_qty = (float)($data['fill_qty'] ?? 0);
        $bottles = (float)($data['bottles_filled'] ?? 0);
        if ($product_item_id <= 0 || $bottle_item_id <= 0 || $bottles <= 0) return false;
        if ($bulk_item_id > 0 && $fill_qty <= 0) return false;

        $product = $this->db->where('id', $product_item_id)->get('db_items')->row();
        $bottle  = $this->db->where('id', $bottle_item_id)->get('db_items')->row();
        $bulk    = $bulk_item_id ? $this->db->where('id', $bulk_item_id)->get('db_items')->row() : null;
        if (!$product || !$bottle || ($bulk_item_id > 0 && !$bulk)) return false;

        $bulk_used = ($bulk && $fill_qty > 0) ? round($fill_qty * $bottles, 3) : 0;
        $batch = $batch_id ? $this->db->where('id', $batch_id)->get('db_production_batches')->row() : null;

        $unit_cost = (float)$bottle->purchase_price;
        if ($bulk) $unit_cost += $fill_qty * (float)$bulk->purchase_price;

        $created_by = $data['created_by'] ?? ($this->session->userdata('username') ?: 'System');

        $this->db->trans_begin();
        try {
            $adj = [
                'store_id'        => $store_id,
                'warehouse_id'    => $warehouse_id,
                'reference_no'    => 'BOTTLE-' . ($batch->batch_code ?? date('Ymd')) . '-' . $product_item_id,
                'adjustment_date' => date('Y-m-d'),
                'adjustment_note' => 'Bottling run: ' . $bottles . ' x ' . $product->item_name,
                'created_date'    => date('Y-m-d'),
                'created_time'    => date('H:i:s'),
                'created_by'      => $created_by,
                'system_ip'       => '127.0.0.1',
                'system_name'     => 'Perfume Lab',
                'status'          => 1,
            ];
            if (!$this->db->insert('db_stockadjustment', $adj)) {
                throw new Exception('Failed to create stock adjustment');
            }
            $adjustment_id = $this->db->insert_id();
            if (!$adjustment_id) throw new Exception('Failed to get adjustment ID');

            $affected = [$bottle_item_id, $product_item_id];
            $lines = [
                [$bottle_item_id, -$bottles, 'Bottling: ' . $bottles . ' empty bottles consumed'],
                [$product_item_id, $bottles, 'Bottling: ' . $bottles . ' finished units stocked in'],
            ];
            if ($bulk_used > 0) {
                $affected[] = $bulk_item_id;
                $lines[] = [$bulk_item_id, -$bulk_used, 'Bottling: ' . $bulk_used . ' bulk liquid drawn'];
            }
            foreach ($lines as $line) {
                $ok = $this->db->insert('db_stockadjustmentitems', [
                    'store_id'       => $store_id,
                    'warehouse_id'   => $warehouse_id,
                    'adjustment_id'  => $adjustment_id,
                    'item_id'        => $line[0],
                    'adjustment_qty' => $line[1],
                    'description'    => $line[2],
                    'status'         => 1,
                ]);
                if (!$ok) throw new Exception('Failed to insert adjustment item ' . $line[0]);
            }

            $run = [
                'store_id'            => $store_id,
                'warehouse_id'        => $warehouse_id,
                'batch_id'            => $batch_id,
                'product_item_id'     => $product_item_id,
                'product_name'        => $product->item_name,
                'bottle_item_id'      => $bottle_item_id,
                'bottle_name'         => $bottle->item_name,
                'bulk_item_id'        => $bulk ? $bulk_item_id : null,
                'bulk_name'           => $bulk ? $bulk->item_name : null,
                'fill_qty'            => $fill_qty,
                'bottles_filled'      => $bottles,
                'bulk_used'           => $bulk_used,
                'unit_cost'           => round($unit_cost, 2),
                'total_cost'          => round($unit_cost * $bottles, 2),
                'notes'               => $data['notes'] ?? null,
                'stock_adjustment_id' => $adjustment_id,
                'created_date'        => date('Y-m-d'),
                'created_time'        => date('H:i:s'),
                'created_by'          => $created_by,
                'status'              => 1,
            ];
            if (!$this->db->insert('db_bottling_runs', $run)) {
                throw new Exception('Failed to write bottling run');
            }
            $run_id = $this->db->insert_id();

            $this->load->model('pos_model');
            $unique_ids = array_values(array_unique($affected));
            foreach ($unique_ids as $uid) {
                if (!$this->pos_model->update_items_quantity($uid)) {
                    throw new Exception('Failed to refresh item stock ' . $uid);
                }
            }
            $two_array = [];
            foreach ($unique_ids as $uid) { $two_array[] = [$uid]; }
            if (!update_warehouse_items($two_array)) {
                throw new Exception('Failed to refresh warehouse stock');
            }

            $this->db->trans_commit();
            return $run_id;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Perfume_model::record_bottling failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Bottling runs, newest first. Pass a batch_id to scope to one batch.
     */
    public function get_bottling_runs($store_id = null, $batch_id = null, $limit = 50) {
        $store_id = $store_id ?? get_current_store_id();
        if (!$this->db->table_exists('db_bottling_runs')) return [];
        $q = $this->db->select('r.*, b.batch_code')
            ->from('db_bottling_runs r')
            ->join('db_production_batches b', 'b.id = r.batch_id', 'left')
            ->where('r.store_id', $store_id)
            ->where('r.status', 1)
            ->order_by('r.id', 'desc')
            ->limit($limit);
        if ($batch_id) $q->where('r.batch_id', $batch_id);
        return $q->get()->result();
    }
}
