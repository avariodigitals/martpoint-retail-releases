<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_credit_model extends CI_Model {

    var $table = 'db_store_credit as a';
    var $column_order = array('a.id','a.credit_code','b.customer_name','a.amount','a.balance','a.source','a.expiry_date','a.status');
    var $column_search = array('a.credit_code','b.customer_name','a.amount','a.source','a.status');
    var $order = array('a.id' => 'desc');

    public function __construct()
    {
        parent::__construct();
    }

    private function _get_datatables_query()
    {
        if(!$this->db->table_exists('db_store_credit')) { $this->db->from('db_store_credit'); return; }
        $this->db->select('a.*, b.customer_name');
        $this->db->from($this->table);
        $this->db->join('db_customers b', 'b.id = a.customer_id', 'left');
        $this->db->where("a.store_id", get_current_store_id());
        $i = 0;
        foreach ($this->column_search as $item) {
            if($_POST['search']['value']) {
                if($i===0) {
                    $this->db->group_start();
                    $this->db->like($item, $_POST['search']['value']);
                } else {
                    $this->db->or_like($item, $_POST['search']['value']);
                }
                if(count($this->column_search) - 1 == $i) $this->db->group_end();
            }
            $i++;
        }
        if(isset($_POST['order'])) {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if(isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function get_datatables()
    {
        if(!$this->db->table_exists('db_store_credit')) return array();
        $this->_get_datatables_query();
        if($_POST['length'] != -1)
        $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }

    function count_filtered()
    {
        if(!$this->db->table_exists('db_store_credit')) return 0;
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all()
    {
        if(!$this->db->table_exists('db_store_credit')) return 0;
        $this->db->where("store_id", get_current_store_id());
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }

    public function save_credit()
    {
        if(!$this->db->table_exists('db_store_credit')) return 'failed: db_store_credit table missing';
        $store_id = get_current_store_id();
        $customer_id = $this->input->post('customer_id', TRUE);
        $amount = $this->input->post('amount', TRUE) ?: 0;
        $source = $this->input->post('source', TRUE) ?: 'manual';
        $expiry_days = $this->input->post('expiry_days', TRUE) ?: 0;
        $expiry_date = ($expiry_days > 0) ? date('Y-m-d', strtotime("+$expiry_days days")) : null;
        $notes = $this->input->post('notes', TRUE);
        $sales_id = $this->input->post('sales_id', TRUE) ?: null;

        $credit_code = 'SC' . strtoupper(substr(md5(uniqid()), 0, 10));

        $data = array(
            'store_id' => $store_id,
            'customer_id' => $customer_id,
            'credit_code' => $credit_code,
            'amount' => $amount,
            'balance' => $amount,
            'source' => $source,
            'sales_id' => $sales_id,
            'expiry_date' => $expiry_date,
            'notes' => $notes,
            'status' => 'active',
            'created_date' => date('Y-m-d'),
            'created_time' => date('H:i:s'),
            'created_by' => $this->session->userdata('inv_username')
        );
        $this->db->insert('db_store_credit', $data);
        if($this->db->error()['code'] != 0) return 'failed: ' . $this->db->error()['message'];
        $inserted = $this->db->affected_rows() > 0;

        // Update customer store credit balance
        if($inserted && $this->db->table_exists('db_customers') && $this->db->field_exists('store_credit_balance', 'db_customers')){
            $customer = $this->db->where('id', $customer_id)->get('db_customers')->row();
            if($customer){
                $new_balance = $customer->store_credit_balance + $amount;
                $this->db->where('id', $customer_id)->update('db_customers', array('store_credit_balance' => $new_balance));
            }
        }
        return $inserted ? "success" : "failed: no rows affected";
    }

    public function cancel_credit($id)
    {
        if(!$this->db->table_exists('db_store_credit')) return 'failed';
        $store_id = get_current_store_id();
        $credit = $this->db->where('id', $id)->where('store_id', $store_id)->get('db_store_credit')->row();
        if(!$credit || $credit->status != 'active') return 'failed';

        $this->db->where('id', $id)->update('db_store_credit', array('status' => 'cancelled'));

        // Reverse customer balance
        if($this->db->table_exists('db_customers') && $this->db->field_exists('store_credit_balance', 'db_customers')){
            $customer = $this->db->where('id', $credit->customer_id)->get('db_customers')->row();
            if($customer){
                $new_balance = max(0, $customer->store_credit_balance - $credit->balance);
                $this->db->where('id', $credit->customer_id)->update('db_customers', array('store_credit_balance' => $new_balance));
            }
        }
        return 'success';
    }

    public function redeem_ajax()
    {
        $customer_id = $this->input->post('customer_id', TRUE);
        $amount = $this->input->post('amount', TRUE);
        $sales_id = $this->input->post('sales_id', TRUE);
        $store_id = get_current_store_id();

        if(empty($customer_id) || empty($amount)) return 'failed';
        $amount = floatval($amount);

        // Get active credits for customer ordered by expiry date
        $credits = $this->db->where('store_id', $store_id)
                            ->where('customer_id', $customer_id)
                            ->where('status', 'active')
                            ->where('balance >', 0)
                            ->order_by('expiry_date', 'asc')
                            ->get('db_store_credit')
                            ->result();

        $remaining = $amount;
        foreach($credits as $credit){
            if($remaining <= 0) break;
            $use = min($remaining, $credit->balance);
            $new_balance = $credit->balance - $use;
            $status = ($new_balance <= 0) ? 'used' : 'active';
            $this->db->where('id', $credit->id)->update('db_store_credit', array(
                'balance' => $new_balance,
                'status' => $status
            ));
            $this->db->insert('db_store_credit_usage', array(
                'store_id' => $store_id,
                'credit_id' => $credit->id,
                'customer_id' => $customer_id,
                'sales_id' => $sales_id,
                'amount_used' => $use,
                'created_date' => date('Y-m-d'),
                'created_time' => date('H:i:s')
            ));
            $remaining -= $use;
        }

        // Update customer balance
        $customer = $this->db->where('id', $customer_id)->get('db_customers')->row();
        if($customer){
            $new_cust_balance = max(0, $customer->store_credit_balance - $amount);
            $this->db->where('id', $customer_id)->update('db_customers', array('store_credit_balance' => $new_cust_balance));
        }
        return 'success';
    }
}
