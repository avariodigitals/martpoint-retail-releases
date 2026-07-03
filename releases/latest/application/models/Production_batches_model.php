<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Production Batches Model
 * Bakery batch scheduling, kitchen production, made-to-order manufacturing
 */
class Production_batches_model extends CI_Model {

    var $table = 'db_production_batches as a';
    var $column_order = array('a.id','a.batch_code','a.batch_name','a.scheduled_date','a.status','a.equipment','a.created_at');
    var $column_search = array('a.batch_code','a.batch_name','a.equipment','a.notes');
    var $order = array('a.scheduled_date' => 'asc');

    public function __construct() {
        parent::__construct();
        $this->_ensure_tables();
    }

    private function _ensure_tables() {
        if (!$this->db->table_exists('db_production_batches')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_production_batches (
                id INT AUTO_INCREMENT PRIMARY KEY,
                store_id INT NOT NULL DEFAULT 1,
                batch_code VARCHAR(50) NOT NULL,
                batch_name VARCHAR(255) NOT NULL,
                batch_type VARCHAR(50) DEFAULT 'general',
                scheduled_date DATE NOT NULL,
                scheduled_time VARCHAR(20) NULL,
                equipment VARCHAR(255) NULL,
                staff_id INT NULL DEFAULT 0,
                staff_name VARCHAR(255) NULL,
                status VARCHAR(50) NOT NULL DEFAULT 'planned',
                notes TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_store_id (store_id),
                INDEX idx_scheduled_date (scheduled_date),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        if (!$this->db->table_exists('db_production_batch_items')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_production_batch_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                batch_id INT NOT NULL,
                item_type VARCHAR(50) NOT NULL DEFAULT 'custom_order',
                item_id INT NOT NULL,
                item_name VARCHAR(255) NULL,
                quantity INT NOT NULL DEFAULT 1,
                notes TEXT NULL,
                status VARCHAR(50) DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_batch_id (batch_id),
                INDEX idx_item_type (item_type, item_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
    }

    // ========== Workflow helpers ==========
    public static function get_statuses() {
        return ['planned','prepping','in_production','cooling','decorating','ready','completed','cancelled'];
    }

    public static function status_label($status) {
        $labels = [
            'planned' => 'Planned', 'prepping' => 'Prepping',
            'in_production' => 'In Production', 'cooling' => 'Cooling',
            'decorating' => 'Decorating', 'ready' => 'Ready',
            'completed' => 'Completed', 'cancelled' => 'Cancelled',
        ];
        return $labels[$status] ?? ucfirst(str_replace('_',' ',$status));
    }

    public static function status_badge($status) {
        $map = [
            'planned' => 'default', 'prepping' => 'info',
            'in_production' => 'primary', 'cooling' => 'warning',
            'decorating' => 'warning', 'ready' => 'success',
            'completed' => 'success', 'cancelled' => 'danger',
        ];
        return $map[$status] ?? 'default';
    }

    // ========== CRUD ==========
    public function save($data, $id = null) {
        $this->db->trans_begin();
        try {
            if ($id) {
                $this->db->where('id', $id);
                $this->db->update('db_production_batches', $data);
                $batch_id = $id;
            } else {
                $store_id = $data['store_id'] ?? get_current_store_id();
                $prefix = 'BATCH-' . date('Ymd') . '-';
                $last = $this->db->like('batch_code', $prefix, 'after')
                    ->where('store_id', $store_id)
                    ->order_by('id', 'DESC')
                    ->limit(1)
                    ->get('db_production_batches')->row();
                $next_num = $last ? ((int)substr($last->batch_code, strrpos($last->batch_code, '-')+1) + 1) : 1;
                $data['batch_code'] = $prefix . str_pad($next_num, 3, '0', STR_PAD_LEFT);
                $this->db->insert('db_production_batches', $data);
                $batch_id = $this->db->insert_id();
            }
            $this->db->trans_commit();
            return $batch_id;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            throw $e;
        }
    }

    public function save_items($batch_id, $items) {
        $this->db->where('batch_id', $batch_id);
        $this->db->delete('db_production_batch_items');
        foreach ($items as $item) {
            $item['batch_id'] = $batch_id;
            $this->db->insert('db_production_batch_items', $item);
        }
    }

    public function get($id) {
        $this->db->select('a.*, u.first_name, u.last_name');
        $this->db->from('db_production_batches a');
        $this->db->join('db_users u', 'u.id = a.staff_id', 'left');
        $this->db->where('a.id', $id);
        return $this->db->get()->row();
    }

    public function get_items($batch_id) {
        $this->db->where('batch_id', $batch_id);
        return $this->db->get('db_production_batch_items')->result();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('db_production_batches');
        $this->db->where('batch_id', $id);
        $this->db->delete('db_production_batch_items');
    }

    /**
     * Complete a production batch:
     * 1. Group items by recipe
     * 2. Scale ingredient qty by (produce_qty / recipe_yield_qty)
     * 3. Deduct raw materials from stock
     * 4. Add final product to stock
     * 5. Record production run
     */
    public function complete_batch($batch_id) {
        $this->load->model('recipe_model', 'recipe');
        $this->load->model('pos_model');
        $batch = $this->get($batch_id);
        if (!$batch) {
            return false;
        }

        // Prevent double-processing
        if (!empty($batch->status) && $batch->status === 'completed') {
            return true;
        }

        $store_id = $batch->store_id;
        $warehouse_id = get_store_warehouse_id();
        $items = $this->get_items($batch_id);

        $affected_item_ids = [];
        $adjustment_items = [];

        foreach ($items as $item) {
            $recipe_id = null;
            $product_item_id = null;
            $produce_qty = (float)$item->quantity;

            if ($item->item_type == 'custom_order') {
                $co = $this->db->where('id', $item->item_id)->get('db_custom_orders')->row();
                if (!$co || !$co->item_id) continue;
                $product = $this->db->where('id', $co->item_id)->get('db_items')->row();
                if (!$product || !$product->recipe_id) continue;
                $recipe_id = $product->recipe_id;
                $product_item_id = $product->id;
            } else if ($item->item_type == 'recipe_product') {
                $recipe = $this->db->where('id', $item->item_id)->get('db_recipes')->row();
                if (!$recipe) continue;
                $recipe_id = $recipe->id;
                $product_item_id = $recipe->product_item_id;
            }

            if (!$recipe_id || $produce_qty <= 0) continue;

            $recipe = $this->recipe->get($recipe_id);
            if (!$recipe || $recipe->yield_qty <= 0) continue;

            $ings = $this->recipe->get_ingredients($recipe_id);
            $scale = $produce_qty / (float)$recipe->yield_qty;
            $run_cost = 0;

            foreach ($ings as $ing) {
                if (!$ing->item_id) continue;
                $deduct_qty = (float)$ing->qty * $scale;
                if ($deduct_qty <= 0) continue;

                $ing_cost = $deduct_qty * (float)$ing->cost_per_unit;
                $run_cost += $ing_cost;

                $affected_item_ids[] = $ing->item_id;
                $adjustment_items[] = [
                    'store_id'        => $store_id,
                    'warehouse_id'    => $warehouse_id,
                    'item_id'         => $ing->item_id,
                    'adjustment_qty'  => -$deduct_qty,
                    'description'     => 'Production: ' . ($batch->batch_code ?? $batch_id) . ' / Recipe: ' . $recipe->name,
                ];
            }

            if ($product_item_id) {
                $affected_item_ids[] = $product_item_id;
                $adjustment_items[] = [
                    'store_id'        => $store_id,
                    'warehouse_id'    => $warehouse_id,
                    'item_id'         => $product_item_id,
                    'adjustment_qty'  => $produce_qty,
                    'description'     => 'Produced from batch ' . ($batch->batch_code ?? $batch_id) . ' / Recipe: ' . $recipe->name,
                ];

                // Auto-update sales price if recipe has a margin set
                if (!empty($recipe->margin_pct) && $recipe->margin_pct > 0 && $produce_qty > 0) {
                    $cost_per_unit = $run_cost / $produce_qty;
                    $new_sales_price = $cost_per_unit * (1 + ((float)$recipe->margin_pct / 100));
                    $this->db->where('id', $product_item_id);
                    $this->db->update('db_items', ['sales_price' => round($new_sales_price, 2)]);
                }
            }

            // Record production run
            $this->recipe->record_production_run([
                'store_id'    => $store_id,
                'recipe_id'   => $recipe_id,
                'batch_id'    => $batch_id,
                'planned_qty' => $produce_qty,
                'actual_yield'=> $produce_qty,
                'actual_cost' => round($run_cost, 2),
                'staff_id'    => $batch->staff_id ?? null,
                'notes'       => 'Completed from batch ' . ($batch->batch_code ?? $batch_id),
                'run_date'    => date('Y-m-d'),
            ]);
        }

        // Create a stock adjustment record so the existing stock engine tracks production
        if (!empty($adjustment_items)) {
            $this->db->trans_begin();

            try {
                $adj = [
                    'store_id'        => $store_id,
                    'warehouse_id'    => $warehouse_id,
                    'reference_no'    => ($batch->batch_code ?? 'BATCH-'.$batch_id) . ' [PROD]',
                    'adjustment_date' => date('Y-m-d'),
                    'adjustment_note' => 'Production batch completion',
                    'created_date'    => date('Y-m-d'),
                    'created_time'    => date('H:i:s'),
                    'created_by'      => 'System',
                    'system_ip'       => '127.0.0.1',
                    'system_name'     => 'Production',
                    'status'          => 1,
                ];
                if (!$this->db->insert('db_stockadjustment', $adj)) {
                    throw new Exception('Failed to create stock adjustment');
                }
                $adjustment_id = $this->db->insert_id();
                if (!$adjustment_id) {
                    throw new Exception('Failed to get adjustment ID');
                }

                foreach ($adjustment_items as $ai) {
                    $ai['adjustment_id'] = $adjustment_id;
                    $ai['status'] = 1;
                    if (!$this->db->insert('db_stockadjustmentitems', $ai)) {
                        throw new Exception('Failed to insert adjustment item for item_id ' . $ai['item_id']);
                    }
                }

                // Update item quantities and warehouse stock using existing engine
                $unique_ids = array_values(array_unique($affected_item_ids));
                foreach ($unique_ids as $uid) {
                    if (!$this->pos_model->update_items_quantity($uid)) {
                        throw new Exception('Failed to update item quantity for item_id ' . $uid);
                    }
                }

                // Pass array-of-arrays as expected by update_warehouse_items / get_in_comma_delimited
                $two_array = [];
                foreach ($unique_ids as $uid) {
                    $two_array[] = [$uid];
                }
                if (!update_warehouse_items($two_array)) {
                    throw new Exception('Failed to update warehouse items');
                }

                $this->db->trans_commit();
                return true;
            } catch (Exception $e) {
                $this->db->trans_rollback();
                log_message('error', 'complete_batch failed for batch ' . $batch_id . ': ' . $e->getMessage());
                return false;
            }
        }

        return true;
    }

    /**
     * Validate that all ingredients for a batch have sufficient stock.
     * Returns array of shortages: [['item_name'=>'Flour','needed'=>5,'available'=>2],...]
     */
    public function validate_stock_for_batch($batch_id) {
        $this->load->model('recipe_model', 'recipe');
        $items = $this->get_items($batch_id);
        $warehouse_id = get_store_warehouse_id();
        $store_id = get_current_store_id();
        $shortages = [];

        foreach ($items as $item) {
            $recipe_id = null;
            $produce_qty = (float)$item->quantity;

            if ($item->item_type == 'custom_order') {
                $co = $this->db->where('id', $item->item_id)->get('db_custom_orders')->row();
                if (!$co || !$co->item_id) continue;
                $product = $this->db->where('id', $co->item_id)->get('db_items')->row();
                if (!$product || !$product->recipe_id) continue;
                $recipe_id = $product->recipe_id;
            } else if ($item->item_type == 'recipe_product') {
                $recipe = $this->db->where('id', $item->item_id)->get('db_recipes')->row();
                if (!$recipe) continue;
                $recipe_id = $recipe->id;
            }

            if (!$recipe_id || $produce_qty <= 0) continue;
            $recipe = $this->recipe->get($recipe_id);
            if (!$recipe || $recipe->yield_qty <= 0) continue;

            $ings = $this->recipe->get_ingredients($recipe_id);
            $scale = $produce_qty / (float)$recipe->yield_qty;

            foreach ($ings as $ing) {
                if (!$ing->item_id) continue;
                $needed = (float)$ing->qty * $scale;
                if ($needed <= 0) continue;

                $available = total_available_qty_items_of_warehouse($warehouse_id, $store_id, $ing->item_id);
                if ($available < $needed) {
                    $shortages[] = [
                        'item_name' => $ing->item_name,
                        'needed'    => round($needed, 3),
                        'available' => round($available, 3),
                    ];
                }
            }
        }
        return $shortages;
    }

    // ========== Scheduling ==========
    public function get_schedule($store_id, $date_from, $date_to, $status = null) {
        $this->db->select('a.*, u.first_name, u.last_name');
        $this->db->from('db_production_batches a');
        $this->db->join('db_users u', 'u.id = a.staff_id', 'left');
        $this->db->where('a.store_id', $store_id);
        $this->db->where('a.scheduled_date >=', $date_from);
        $this->db->where('a.scheduled_date <=', $date_to);
        if ($status) {
            $this->db->where('a.status', $status);
        }
        $this->db->order_by('a.scheduled_date', 'ASC');
        $this->db->order_by('a.scheduled_time', 'ASC');
        return $this->db->get()->result();
    }

    public function get_pending_items($store_id) {
        if (!$this->db->table_exists('db_custom_orders')) {
            return [];
        }
        $this->db->select('a.*, c.customer_name');
        $this->db->from('db_custom_orders a');
        $this->db->join('db_customers c', 'c.id = a.customer_id', 'left');
        $this->db->where('a.store_id', $store_id);
        $this->db->where_in('a.status', ['new','confirmed','deposit_paid','quoted']);
        $this->db->order_by('a.due_date', 'ASC');
        return $this->db->get()->result();
    }

    public function count_by_status($store_id, $status) {
        $this->db->where('store_id', $store_id);
        $this->db->where('status', $status);
        return $this->db->count_all_results('db_production_batches');
    }

    // ========== Datatables ==========
    private function _get_datatables_query() {
        $store_id = get_current_store_id();
        $this->db->select('a.*, u.first_name, u.last_name');
        $this->db->from($this->table);
        $this->db->join('db_users u', 'u.id = a.staff_id', 'left');
        $this->db->where('a.store_id', $store_id);

        $i = 0;
        foreach ($this->column_search as $item) {
            if (isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $_POST['search']['value']);
                } else {
                    $this->db->or_like($item, $_POST['search']['value']);
                }
                if (count($this->column_search) - 1 == $i) {
                    $this->db->group_end();
                }
            }
            $i++;
        }
        if (isset($_POST['order'])) {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    public function get_datatables() {
        $this->_get_datatables_query();
        if ($_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        return $this->db->get()->result();
    }

    public function count_filtered() {
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all() {
        $store_id = get_current_store_id();
        $this->db->from('db_production_batches');
        $this->db->where('store_id', $store_id);
        return $this->db->count_all_results();
    }
}
