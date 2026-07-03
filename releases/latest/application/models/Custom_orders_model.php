<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Custom Orders Model
 * Made-to-order products: furniture, cakes, tailored items, bespoke services
 */
class Custom_orders_model extends CI_Model {

    var $table = 'db_custom_orders as a';
    var $column_order = array('a.id','a.order_code','c.customer_name','b.item_name','a.status','a.due_date','a.total_amount','a.created_at');
    var $column_search = array('a.order_code','c.customer_name','c.mobile','b.item_name','a.status','a.notes');
    var $order = array('a.created_at' => 'desc');

    public function __construct() {
        parent::__construct();
        $this->_ensure_tables();
    }

    private function _ensure_tables() {
        if (!$this->db->table_exists('db_custom_orders')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_custom_orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                store_id INT NOT NULL DEFAULT 1,
                order_code VARCHAR(50) NOT NULL,
                customer_id INT NOT NULL,
                item_id INT NOT NULL,
                item_name VARCHAR(255) NULL,
                specifications_json JSON NULL,
                quoted_price DECIMAL(12,2) DEFAULT 0,
                deposit_amount DECIMAL(12,2) DEFAULT 0,
                deposit_paid DECIMAL(12,2) DEFAULT 0,
                total_amount DECIMAL(12,2) DEFAULT 0,
                balance_due DECIMAL(12,2) DEFAULT 0,
                status VARCHAR(50) NOT NULL DEFAULT 'new',
                workflow_template_key VARCHAR(50) DEFAULT 'standard',
                notes TEXT NULL,
                staff_id INT NULL DEFAULT 0,
                staff_name VARCHAR(255) NULL,
                order_date DATE NOT NULL,
                due_date DATE NULL,
                delivery_date DATE NULL,
                sales_id INT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_store_id (store_id),
                INDEX idx_customer_id (customer_id),
                INDEX idx_item_id (item_id),
                INDEX idx_status (status),
                INDEX idx_order_code (order_code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        if (!$this->db->table_exists('db_custom_order_history')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_custom_order_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                custom_order_id INT NOT NULL,
                old_status VARCHAR(50) NULL,
                new_status VARCHAR(50) NOT NULL,
                note TEXT NULL,
                changed_by INT NULL,
                changed_by_name VARCHAR(255) NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_custom_order_id (custom_order_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
    }

    // ========== Workflow helpers ==========

    public static function get_workflow($key='standard') {
        $workflows = [
            'standard' => ['new','quoted','deposit_paid','in_production','ready','delivered','cancelled'],
            'food'     => ['new','confirmed','deposit_paid','baking','ready','picked_up','cancelled'],
            'furniture'=> ['new','quoted','deposit_paid','in_production','qc_passed','delivered','cancelled'],
        ];
        return $workflows[$key] ?? $workflows['standard'];
    }

    public static function status_label($status) {
        $labels = [
            'new' => 'New', 'quoted' => 'Quoted', 'deposit_paid' => 'Deposit Paid',
            'in_production' => 'In Production', 'baking' => 'Baking',
            'ready' => 'Ready', 'qc_passed' => 'QC Passed',
            'delivered' => 'Delivered', 'picked_up' => 'Picked Up',
            'cancelled' => 'Cancelled', 'confirmed' => 'Confirmed',
        ];
        return $labels[$status] ?? ucfirst(str_replace('_',' ',$status));
    }

    public static function status_badge($status) {
        $map = [
            'new' => 'default', 'quoted' => 'info', 'deposit_paid' => 'warning',
            'in_production' => 'primary', 'baking' => 'primary',
            'ready' => 'success', 'qc_passed' => 'success',
            'delivered' => 'success', 'picked_up' => 'success',
            'cancelled' => 'danger', 'confirmed' => 'info',
        ];
        return $map[$status] ?? 'default';
    }

    // ========== CRUD ==========

    public function save($data, $id = null) {
        $this->db->trans_begin();
        try {
            $old_status = null;
            if ($id) {
                $existing = $this->db->where('id', $id)->get('db_custom_orders')->row();
                if ($existing) $old_status = $existing->status;
                $this->db->where('id', $id);
                $this->db->update('db_custom_orders', $data);
                $order_id = $id;
            } else {
                // Generate order code
                $store_id = $data['store_id'] ?? get_current_store_id();
                $prefix = 'CO-' . date('Y') . '-';
                $last = $this->db->like('order_code', $prefix, 'after')
                    ->where('store_id', $store_id)
                    ->order_by('id', 'DESC')
                    ->limit(1)
                    ->get('db_custom_orders')->row();
                $next_num = $last ? ((int)substr($last->order_code, strrpos($last->order_code, '-')+1) + 1) : 1;
                $data['order_code'] = $prefix . str_pad($next_num, 4, '0', STR_PAD_LEFT);
                $this->db->insert('db_custom_orders', $data);
                $order_id = $this->db->insert_id();
            }
            // Log status change
            if ($id && $old_status && isset($data['status']) && $data['status'] != $old_status) {
                $this->log_history($order_id, $old_status, $data['status'], 'Status updated');
            }
            $this->db->trans_commit();
            return $order_id;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            throw $e;
        }
    }

    public function log_history($order_id, $old_status, $new_status, $note='') {
        $this->db->insert('db_custom_order_history', [
            'custom_order_id' => $order_id,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'note' => $note,
            'changed_by' => $this->session->userdata('user_id') ?? 0,
            'changed_by_name' => $this->session->userdata('username') ?? 'System',
        ]);
    }

    public function get($id) {
        $this->db->select('a.*, c.customer_name, c.mobile, c.customer_code, b.item_name as template_item_name, b.custom_order_fields_json');
        $this->db->from('db_custom_orders a');
        $this->db->join('db_customers c', 'c.id = a.customer_id', 'left');
        $this->db->join('db_items b', 'b.id = a.item_id', 'left');
        $this->db->where('a.id', $id);
        return $this->db->get()->row();
    }

    public function get_history($order_id) {
        $this->db->where('custom_order_id', $order_id);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('db_custom_order_history')->result();
    }

    public function get_by_customer($customer_id, $limit = 50) {
        $this->db->select('a.*, b.item_name as template_item_name');
        $this->db->from('db_custom_orders a');
        $this->db->join('db_items b', 'b.id = a.item_id', 'left');
        $this->db->where('a.customer_id', $customer_id);
        $this->db->order_by('a.created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('db_custom_orders');
    }

    // ========== Datatables ==========

    private function _get_datatables_query() {
        $store_id = get_current_store_id();
        $this->db->select('a.*, c.customer_name, c.mobile, c.customer_code, b.item_name as template_item_name');
        $this->db->from($this->table);
        $this->db->join('db_customers c', 'c.id = a.customer_id', 'left');
        $this->db->join('db_items b', 'b.id = a.item_id', 'left');
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
        $this->db->from('db_custom_orders');
        $this->db->where('store_id', $store_id);
        return $this->db->count_all_results();
    }

    // ========== Stats ==========
    public function count_by_customer($customer_id) {
        $this->db->where('customer_id', $customer_id);
        return $this->db->count_all_results('db_custom_orders');
    }

    public function count_by_status($store_id, $status) {
        $this->db->where('store_id', $store_id);
        $this->db->where('status', $status);
        return $this->db->count_all_results('db_custom_orders');
    }
}
