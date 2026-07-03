<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Recipe Model
 * Ingredient costing, yield tracking, recipe management for bakeries & restaurants
 */
class Recipe_model extends CI_Model {

    var $table = 'db_recipes as a';
    var $column_order = array('a.id','a.recipe_code','a.name','a.category','a.yield_qty','a.yield_unit','a.status','a.created_at');
    var $column_search = array('a.recipe_code','a.name','a.category','a.notes');
    var $order = array('a.name' => 'asc');

    public function __construct() {
        parent::__construct();
        $this->_ensure_tables();
    }

    private function _ensure_tables() {
        if (!$this->db->table_exists('db_recipe_categories')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_recipe_categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                store_id INT NOT NULL DEFAULT 1,
                name VARCHAR(100) NOT NULL,
                status TINYINT NOT NULL DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_store_id (store_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        if (!$this->db->table_exists('db_recipes')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_recipes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                store_id INT NOT NULL DEFAULT 1,
                recipe_code VARCHAR(50) NOT NULL,
                name VARCHAR(255) NOT NULL,
                category VARCHAR(100) NULL,
                description TEXT NULL,
                product_item_id INT NULL COMMENT 'db_items id of final product',
                yield_qty DECIMAL(10,3) NOT NULL DEFAULT 1,
                yield_unit VARCHAR(50) NOT NULL DEFAULT 'piece',
                prep_time INT NULL COMMENT 'minutes',
                cook_time INT NULL COMMENT 'minutes',
                notes TEXT NULL,
                status TINYINT NOT NULL DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_store_id (store_id),
                INDEX idx_category (category),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        // Ensure product_item_id column exists on existing db_recipes
        if ($this->db->table_exists('db_recipes')) {
            $colExists = $this->db->query("SHOW COLUMNS FROM `db_recipes` LIKE 'product_item_id'")->num_rows();
            if ($colExists == 0) {
                $this->db->query("ALTER TABLE `db_recipes` ADD COLUMN `product_item_id` INT NULL COMMENT 'db_items id of final product'");
            }
            $colExists2 = $this->db->query("SHOW COLUMNS FROM `db_recipes` LIKE 'margin_pct'")->num_rows();
            if ($colExists2 == 0) {
                $this->db->query("ALTER TABLE `db_recipes` ADD COLUMN `margin_pct` DECIMAL(5,2) NOT NULL DEFAULT 30 COMMENT 'Sales margin % applied to production cost'");
            }
        }
        if (!$this->db->table_exists('db_recipe_ingredients')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_recipe_ingredients (
                id INT AUTO_INCREMENT PRIMARY KEY,
                recipe_id INT NOT NULL,
                item_id INT NULL COMMENT 'db_items id if linked',
                item_name VARCHAR(255) NOT NULL,
                qty DECIMAL(15,3) NOT NULL DEFAULT 0,
                unit VARCHAR(50) NOT NULL DEFAULT 'gram',
                cost_per_unit DECIMAL(15,2) NOT NULL DEFAULT 0,
                wastage_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_recipe_id (recipe_id),
                INDEX idx_item_id (item_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        if (!$this->db->table_exists('db_recipe_production_runs')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_recipe_production_runs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                store_id INT NOT NULL DEFAULT 1,
                recipe_id INT NOT NULL,
                batch_id INT NULL COMMENT 'db_production_batches id if linked',
                planned_qty DECIMAL(10,3) NOT NULL DEFAULT 0,
                actual_yield DECIMAL(10,3) NULL,
                actual_cost DECIMAL(15,2) NULL,
                staff_id INT NULL,
                notes TEXT NULL,
                run_date DATE NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_store_id (store_id),
                INDEX idx_recipe_id (recipe_id),
                INDEX idx_batch_id (batch_id),
                INDEX idx_run_date (run_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
    }

    // ========== CRUD ==========
    public function save($data, $id = null) {
        $this->db->trans_begin();
        try {
            if ($id) {
                $this->db->where('id', $id);
                $this->db->update('db_recipes', $data);
                $recipe_id = $id;
            } else {
                $store_id = $data['store_id'] ?? get_current_store_id();
                $prefix = 'REC-' . date('Ymd') . '-';
                $last = $this->db->like('recipe_code', $prefix, 'after')
                    ->where('store_id', $store_id)
                    ->order_by('id', 'DESC')
                    ->limit(1)
                    ->get('db_recipes')->row();
                $next_num = $last ? ((int)substr($last->recipe_code, strrpos($last->recipe_code, '-')+1) + 1) : 1;
                $data['recipe_code'] = $prefix . str_pad($next_num, 3, '0', STR_PAD_LEFT);
                $this->db->insert('db_recipes', $data);
                $recipe_id = $this->db->insert_id();
            }
            $this->db->trans_commit();
            return $recipe_id;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            throw $e;
        }
    }

    public function save_ingredients($recipe_id, $ingredients) {
        $this->db->where('recipe_id', $recipe_id);
        $this->db->delete('db_recipe_ingredients');
        foreach ($ingredients as $ing) {
            $ing['recipe_id'] = $recipe_id;
            $this->db->insert('db_recipe_ingredients', $ing);
        }
        // Update cost for all items linked to this recipe
        $this->update_linked_items_cost($recipe_id);
    }

    public function get($id) {
        $this->db->where('id', $id);
        return $this->db->get('db_recipes')->row();
    }

    public function get_active_categories($store_id) {
        $this->db->where('store_id', $store_id);
        $this->db->where('status', 1);
        $this->db->order_by('name', 'asc');
        return $this->db->get('db_recipe_categories')->result();
    }

    public function get_ingredients($recipe_id) {
        $this->db->where('recipe_id', $recipe_id);
        return $this->db->get('db_recipe_ingredients')->result();
    }

    public function get_production_runs($recipe_id, $limit = 10) {
        $this->db->select('a.*, r.name as recipe_name, b.batch_code, u.first_name, u.last_name');
        $this->db->from('db_recipe_production_runs a');
        $this->db->join('db_recipes r', 'r.id = a.recipe_id', 'left');
        $this->db->join('db_production_batches b', 'b.id = a.batch_id', 'left');
        $this->db->join('db_users u', 'u.id = a.staff_id', 'left');
        $this->db->where('a.recipe_id', $recipe_id);
        $this->db->order_by('a.run_date', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        $this->db->delete('db_recipes');
        $this->db->where('recipe_id', $id);
        $this->db->delete('db_recipe_ingredients');
    }

    // ========== Cost Calculations ==========
    public function calculate_cost($recipe_id) {
        $ingredients = $this->get_ingredients($recipe_id);
        $total = 0;
        foreach ($ingredients as $ing) {
            $qty = (float)$ing->qty;
            $cost = (float)$ing->cost_per_unit;
            $wastage = (float)$ing->wastage_pct;
            $ing_cost = $qty * $cost;
            if ($wastage > 0) {
                $ing_cost = $ing_cost * (1 + ($wastage / 100));
            }
            $total += $ing_cost;
        }
        return round($total, 2);
    }

    public function calculate_cost_per_unit($recipe_id) {
        $recipe = $this->get($recipe_id);
        if (!$recipe || $recipe->yield_qty <= 0) return 0;
        $total_cost = $this->calculate_cost($recipe_id);
        return round($total_cost / (float)$recipe->yield_qty, 2);
    }

    // ========== Propagate cost changes to linked items ==========
    public function update_linked_items_cost($recipe_id) {
        $recipe = $this->get($recipe_id);
        if (!$recipe) return;
        $cost_per_unit = $this->calculate_cost_per_unit($recipe_id);
        if ($cost_per_unit <= 0) return;

        // Find all items linked to this recipe (by recipe_id or by being the product_item_id)
        $this->db->where('recipe_id', $recipe_id);
        $items = $this->db->get('db_items')->result();

        // Also find item linked via product_item_id on the recipe
        if (!empty($recipe->product_item_id)) {
            $prod_item = $this->db->where('id', $recipe->product_item_id)->get('db_items')->row();
            if ($prod_item && !in_array($prod_item, $items)) {
                $items[] = $prod_item;
            }
        }

        foreach ($items as $item) {
            $margin = (float)($item->recipe_margin_pct ?? 0);
            $new_sales = $margin > 0 ? ($cost_per_unit * (1 + $margin / 100)) : $item->sales_price;

            $this->db->where('id', $item->id);
            $this->db->update('db_items', [
                'purchase_price' => $cost_per_unit,
                'price' => $cost_per_unit,
                'sales_price' => $new_sales,
            ]);
        }
    }

    // ========== Production Run ==========
    public function save_production_run($data, $id = null) {
        if ($id) {
            $this->db->where('id', $id);
            $this->db->update('db_recipe_production_runs', $data);
        } else {
            $this->db->insert('db_recipe_production_runs', $data);
            $id = $this->db->insert_id();
        }
        return $id;
    }

    public function record_production_run($data) {
        return $this->save_production_run($data);
    }

    // ========== Datatables ==========
    private function _get_datatables_query() {
        $store_id = get_current_store_id();
        $this->db->from($this->table);
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
        return $this->db->get()->num_rows();
    }

    public function count_all() {
        $store_id = get_current_store_id();
        $this->db->from('db_recipes');
        $this->db->where('store_id', $store_id);
        return $this->db->count_all_results();
    }
}
