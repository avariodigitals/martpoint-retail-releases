<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gift_cards_model extends CI_Model {

    var $table = 'db_gift_cards as a';
    var $column_order = array('a.id','a.card_number','b.customer_name','a.initial_value','a.current_balance','a.issue_date','a.expiry_date','a.card_type','a.status');
    var $column_search = array('a.card_number','b.customer_name','a.initial_value','a.current_balance','a.status');
    var $order = array('a.id' => 'desc');

    public function __construct()
    {
        parent::__construct();
    }

    private function _get_datatables_query()
    {
        if(!$this->db->table_exists('db_gift_cards')) { $this->db->from('db_gift_cards'); return; }
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
        if(!$this->db->table_exists('db_gift_cards')) return array();
        $this->_get_datatables_query();
        if($_POST['length'] != -1)
        $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }

    function count_filtered()
    {
        if(!$this->db->table_exists('db_gift_cards')) return 0;
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all()
    {
        if(!$this->db->table_exists('db_gift_cards')) return 0;
        $this->db->where("store_id", get_current_store_id());
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }

    public function get_card($id)
    {
        if(!$this->db->table_exists('db_gift_cards')) return null;
        return $this->db->where('id', $id)->where('store_id', get_current_store_id())->get('db_gift_cards')->row();
    }

    public function get_card_by_number($card_number)
    {
        if(!$this->db->table_exists('db_gift_cards')) return null;
        return $this->db->where('card_number', $card_number)->where('store_id', get_current_store_id())->get('db_gift_cards')->row();
    }

    public function save_card()
    {
        $store_id = get_current_store_id();
        $card_number = $this->input->post('card_number', TRUE) ?: $this->generate_card_number();
        $initial_value = $this->input->post('initial_value', TRUE) ?: 0;
        $expiry_days = $this->input->post('expiry_days', TRUE) ?: 0;
        $expiry_date = ($expiry_days > 0) ? date('Y-m-d', strtotime("+$expiry_days days")) : null;

        $data = array(
            'store_id' => $store_id,
            'card_number' => $card_number,
            'customer_id' => $this->input->post('customer_id', TRUE) ?: null,
            'initial_value' => $initial_value,
            'current_balance' => $initial_value,
            'issue_date' => date('Y-m-d'),
            'expiry_date' => $expiry_date,
            'card_type' => $this->input->post('card_type', TRUE) ?: 'physical',
            'status' => 'active',
            'notes' => $this->input->post('notes', TRUE),
            'created_date' => date('Y-m-d'),
            'created_time' => date('H:i:s'),
            'created_by' => $this->session->userdata('inv_username')
        );
        $this->db->insert('db_gift_cards', $data);
        if($this->db->error()['code'] != 0) return 'failed: ' . $this->db->error()['message'];
        return ($this->db->affected_rows() > 0) ? "success" : "failed: no rows affected";
    }

    public function update_card()
    {
        $id = $this->input->post('card_id', TRUE);
        $store_id = get_current_store_id();
        $data = array(
            'customer_id' => $this->input->post('customer_id', TRUE) ?: null,
            'notes' => $this->input->post('notes', TRUE),
        );
        $this->db->where('id', $id)->where('store_id', $store_id)->update('db_gift_cards', $data);
        if($this->db->error()['code'] != 0) return 'failed: ' . $this->db->error()['message'];
        return ($this->db->affected_rows() >= 0) ? "success" : "failed: no rows affected";
    }

    public function cancel_card($id)
    {
        $store_id = get_current_store_id();
        $this->db->where('id', $id)->where('store_id', $store_id)->where('status', 'active')->update('db_gift_cards', array('status' => 'cancelled'));
        return ($this->db->affected_rows() > 0) ? "success" : "failed";
    }

    public function redeem_ajax()
    {
        $card_id = $this->input->post('card_id', TRUE);
        $amount = $this->input->post('amount', TRUE);
        $sales_id = $this->input->post('sales_id', TRUE);
        $customer_id = $this->input->post('customer_id', TRUE);
        $store_id = get_current_store_id();

        if(empty($card_id) || empty($amount)) return 'failed';
        $card = $this->db->where('id', $card_id)->where('store_id', $store_id)->get('db_gift_cards')->row();
        if(!$card || $card->current_balance < $amount) return 'failed';

        $new_balance = $card->current_balance - $amount;
        $status = ($new_balance <= 0) ? 'redeemed' : 'active';
        $this->db->where('id', $card_id)->update('db_gift_cards', array(
            'current_balance' => $new_balance,
            'status' => $status
        ));

        $this->db->insert('db_gift_card_usage', array(
            'store_id' => $store_id,
            'card_id' => $card_id,
            'customer_id' => $customer_id,
            'sales_id' => $sales_id,
            'amount_used' => $amount,
            'created_date' => date('Y-m-d'),
            'created_time' => date('H:i:s')
        ));

        // Update customer gift card balance
        $customer = $this->db->where('id', $customer_id)->get('db_customers')->row();
        if($customer){
            $new_cust_balance = max(0, $customer->gift_card_balance - $amount);
            $this->db->where('id', $customer_id)->update('db_customers', array('gift_card_balance' => $new_cust_balance));
        }
        return 'success';
    }

    private function generate_card_number()
    {
        $prefix = 'GC';
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
        return $prefix . $random;
    }
}
