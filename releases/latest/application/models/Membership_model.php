<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Membership Model
 * Handles membership plans, customer memberships, and POS discount lookups
 * for recurring plans, auto-renewal, and benefits (Spas, Gyms, Salons)
 */
class Membership_model extends CI_Model {

    // Datatable config for plans
    var $plan_table = 'db_membership_plans as a';
    var $plan_column_order = array('a.id','a.plan_name','a.plan_code','a.price','a.billing_cycle','a.discount_percent','a.status');
    var $plan_column_search = array('a.plan_name','a.plan_code','a.description');
    var $plan_order = array('a.id' => 'desc');

    public function __construct() {
        parent::__construct();
        $this->_ensure_tables();
    }

    /**
     * Auto-create membership tables if they don't exist
     */
    private function _ensure_tables() {
        if (!$this->db->table_exists('db_membership_plans')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_membership_plans (
                id INT AUTO_INCREMENT PRIMARY KEY,
                store_id INT NOT NULL DEFAULT 1,
                plan_name VARCHAR(255) NOT NULL,
                plan_code VARCHAR(100) NOT NULL,
                description TEXT NULL,
                price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                billing_cycle ENUM('monthly','quarterly','annual') NOT NULL DEFAULT 'monthly',
                discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                free_services_per_period INT NOT NULL DEFAULT 0,
                priority_booking TINYINT(1) NOT NULL DEFAULT 0,
                status TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_store_id (store_id),
                INDEX idx_status (status),
                INDEX idx_billing_cycle (billing_cycle)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        if (!$this->db->table_exists('db_customer_memberships')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_customer_memberships (
                id INT AUTO_INCREMENT PRIMARY KEY,
                store_id INT NOT NULL DEFAULT 1,
                customer_id INT NOT NULL,
                plan_id INT NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                next_billing_date DATE NULL,
                auto_renew TINYINT(1) NOT NULL DEFAULT 0,
                status ENUM('active','expired','cancelled','pending') NOT NULL DEFAULT 'active',
                payment_status ENUM('paid','overdue','pending') NOT NULL DEFAULT 'paid',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_store_id (store_id),
                INDEX idx_customer_id (customer_id),
                INDEX idx_plan_id (plan_id),
                INDEX idx_status (status),
                INDEX idx_end_date (end_date),
                INDEX idx_next_billing (next_billing_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        if (!$this->db->table_exists('db_membership_payments')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_membership_payments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                membership_id INT NOT NULL,
                customer_id INT NOT NULL,
                plan_id INT NOT NULL,
                amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                payment_date DATE NOT NULL,
                payment_method VARCHAR(50) NULL,
                payment_period_start DATE NOT NULL,
                payment_period_end DATE NOT NULL,
                status ENUM('success','failed','pending') NOT NULL DEFAULT 'success',
                notes TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_membership_id (membership_id),
                INDEX idx_customer_id (customer_id),
                INDEX idx_payment_date (payment_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
    }

    // ========== PLAN CRUD ==========

    public function save_plan($data, $id = null) {
        if (empty($id)) {
            // Generate plan code if not provided
            if (empty($data['plan_code'])) {
                $data['plan_code'] = $this->_generate_plan_code();
            }
            $this->db->insert('db_membership_plans', $data);
            return $this->db->insert_id();
        } else {
            $this->db->where('id', $id);
            $this->db->update('db_membership_plans', $data);
            return $id;
        }
    }

    public function get_plan($id) {
        return $this->db->where('id', $id)->get('db_membership_plans')->row();
    }

    public function delete_plan($id) {
        $this->db->where('id', $id);
        $this->db->update('db_membership_plans', ['status' => 0]);
        return $this->db->affected_rows() > 0;
    }

    public function toggle_plan_status($id, $status) {
        $this->db->where('id', $id);
        $this->db->update('db_membership_plans', ['status' => $status]);
        return 'success';
    }

    private function _generate_plan_code() {
        $prefix = 'MEM';
        $this->db->select_max('id');
        $query = $this->db->get('db_membership_plans');
        $max = $query->row()->id ?? 0;
        return $prefix . str_pad($max + 1, 5, '0', STR_PAD_LEFT);
    }

    // ========== PLAN DATATABLE ==========

    private function _get_plan_datatables_query() {
        $this->db->select('a.*');
        $this->db->from($this->plan_table);
        $this->db->where('a.store_id', get_current_store_id());

        $i = 0;
        foreach ($this->plan_column_search as $item) {
            if (!empty($_POST['search']['value'])) {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $_POST['search']['value']);
                } else {
                    $this->db->or_like($item, $_POST['search']['value']);
                }
                if (count($this->plan_column_search) - 1 == $i)
                    $this->db->group_end();
            }
            $i++;
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($this->plan_column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->plan_order)) {
            $order = $this->plan_order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    public function get_plan_datatables() {
        $this->_get_plan_datatables_query();
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }

    public function count_plan_all() {
        $this->db->from('db_membership_plans');
        $this->db->where('store_id', get_current_store_id());
        return $this->db->count_all_results();
    }

    public function count_plan_filtered() {
        $this->_get_plan_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function get_active_plans($store_id = null) {
        $store_id = $store_id ?: get_current_store_id();
        return $this->db->where('store_id', $store_id)->where('status', 1)->get('db_membership_plans')->result();
    }

    // ========== CUSTOMER MEMBERSHIP CRUD ==========

    public function assign_membership($data) {
        $this->db->insert('db_customer_memberships', $data);
        $id = $this->db->insert_id();
        $this->_record_payment($id, $data);
        return $id;
    }

    public function renew_membership($membership_id, $data) {
        $this->db->where('id', $membership_id);
        $this->db->update('db_customer_memberships', $data);
        $this->_record_payment($membership_id, $data);
        return true;
    }

    public function cancel_membership($membership_id) {
        $this->db->where('id', $membership_id);
        $this->db->update('db_customer_memberships', ['status' => 'cancelled']);
        return true;
    }

    public function get_customer_membership($id) {
        $this->db->select('cm.*, c.customer_name, c.mobile, mp.plan_name, mp.plan_code, mp.price, mp.billing_cycle, mp.discount_percent, mp.free_services_per_period');
        $this->db->from('db_customer_memberships cm');
        $this->db->join('db_customers c', 'c.id = cm.customer_id', 'left');
        $this->db->join('db_membership_plans mp', 'mp.id = cm.plan_id', 'left');
        $this->db->where('cm.id', $id);
        return $this->db->get()->row();
    }

    public function get_customer_memberships($customer_id) {
        $this->db->select('cm.*, mp.plan_name, mp.plan_code, mp.price, mp.billing_cycle, mp.discount_percent');
        $this->db->from('db_customer_memberships cm');
        $this->db->join('db_membership_plans mp', 'mp.id = cm.plan_id', 'left');
        $this->db->where('cm.customer_id', $customer_id);
        $this->db->order_by('cm.created_at', 'desc');
        return $this->db->get()->result();
    }

    public function get_store_memberships($status = null, $expiring_days = null) {
        $this->db->select('cm.*, c.customer_name, c.mobile, mp.plan_name, mp.plan_code, mp.price, mp.billing_cycle');
        $this->db->from('db_customer_memberships cm');
        $this->db->join('db_customers c', 'c.id = cm.customer_id', 'left');
        $this->db->join('db_membership_plans mp', 'mp.id = cm.plan_id', 'left');
        $this->db->where('cm.store_id', get_current_store_id());
        if ($status) {
            $this->db->where('cm.status', $status);
        }
        if ($expiring_days) {
            $this->db->where('cm.end_date <=', date('Y-m-d', strtotime("+$expiring_days days")));
            $this->db->where('cm.end_date >=', date('Y-m-d'));
            $this->db->where('cm.status', 'active');
        }
        $this->db->order_by('cm.end_date', 'asc');
        return $this->db->get()->result();
    }

    public function update_expired_memberships() {
        $this->db->where('end_date <', date('Y-m-d'));
        $this->db->where('status', 'active');
        $this->db->update('db_customer_memberships', ['status' => 'expired']);
        return $this->db->affected_rows();
    }

    public function count_active_memberships($store_id = null) {
        $store_id = $store_id ?: get_current_store_id();
        return $this->db->where('store_id', $store_id)->where('status', 'active')->count_all_results('db_customer_memberships');
    }

    public function count_expiring_soon($days = 7) {
        $this->db->where('store_id', get_current_store_id());
        $this->db->where('status', 'active');
        $this->db->where('end_date <=', date('Y-m-d', strtotime("+$days days")));
        $this->db->where('end_date >=', date('Y-m-d'));
        return $this->db->count_all_results('db_customer_memberships');
    }

    // ========== POS DISCOUNT INTEGRATION ==========

    /**
     * Get active membership discount for a customer
     * Returns object with discount_percent, plan_name, or null
     */
    public function get_customer_discount($customer_id, $store_id = null) {
        $store_id = $store_id ?: get_current_store_id();
        $today = date('Y-m-d');

        $this->db->select('cm.id as membership_id, cm.plan_id, mp.plan_name, mp.discount_percent, mp.free_services_per_period');
        $this->db->from('db_customer_memberships cm');
        $this->db->join('db_membership_plans mp', 'mp.id = cm.plan_id', 'left');
        $this->db->where('cm.customer_id', $customer_id);
        $this->db->where('cm.store_id', $store_id);
        $this->db->where('cm.status', 'active');
        $this->db->where('cm.start_date <=', $today);
        $this->db->where('cm.end_date >=', $today);
        $this->db->order_by('mp.discount_percent', 'desc');
        $this->db->limit(1);

        $result = $this->db->get()->row();
        return $result;
    }

    // ========== PRIVATE HELPERS ==========

    private function _record_payment($membership_id, $data) {
        if (!empty($data['amount_paid']) && $data['amount_paid'] > 0) {
            $payment = [
                'membership_id' => $membership_id,
                'customer_id' => $data['customer_id'],
                'plan_id' => $data['plan_id'],
                'amount' => $data['amount_paid'],
                'payment_date' => date('Y-m-d'),
                'payment_method' => $data['payment_method'] ?? 'cash',
                'payment_period_start' => $data['start_date'],
                'payment_period_end' => $data['end_date'],
                'status' => 'success',
                'notes' => $data['notes'] ?? ''
            ];
            $this->db->insert('db_membership_payments', $payment);
        }
    }
}
