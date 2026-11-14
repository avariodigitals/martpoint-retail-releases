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
     * Verify membership tables exist; log a warning if they don't.
     */
    private function _ensure_tables() {
        $tables = ['db_membership_plans', 'db_customer_memberships', 'db_membership_payments'];
        foreach ($tables as $table) {
            if (!$this->db->table_exists($table)) {
                log_message('error', 'Missing required table: ' . $table . '. Run the 4.0.2 migration via login.');
            }
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
