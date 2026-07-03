<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loyalty_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    public function get_settings()
    {
        $store_id = get_current_store_id();
        if(!$this->db->table_exists('db_loyalty_settings')){
            return (object) array(
                'loyalty_enabled' => 0,
                'earning_type' => 'spend_based',
                'spend_amount' => 1000,
                'points_earned' => 1,
                'percentage_rate' => 2,
                'redemption_rate' => 10,
                'minimum_redemption_points' => 100,
                'maximum_redemption_per_sale' => 0,
                'allow_partial_redemption' => 1,
                'tier_calculation' => 'lifetime_spend',
                'flexpay_points_timing' => 'full_payment'
            );
        }
        $row = $this->db->where('store_id', $store_id)->get('db_loyalty_settings')->row();
        if(!$row){
            $this->init_settings($store_id);
            $row = $this->db->where('store_id', $store_id)->get('db_loyalty_settings')->row();
        }
        return $row;
    }

    private function init_settings($store_id)
    {
        if(!$this->db->table_exists('db_loyalty_settings')) return;
        $this->db->insert('db_loyalty_settings', array(
            'store_id' => $store_id,
            'loyalty_enabled' => 0,
            'earning_type' => 'spend_based',
            'spend_amount' => 1000,
            'points_earned' => 1,
            'percentage_rate' => 2,
            'redemption_rate' => 10,
            'minimum_redemption_points' => 100,
            'maximum_redemption_per_sale' => 0,
            'allow_partial_redemption' => 1,
            'tier_calculation' => 'lifetime_spend',
            'flexpay_points_timing' => 'full_payment',
            'created_date' => date('Y-m-d')
        ));
    }

    public function save_settings()
    {
        $store_id = get_current_store_id();
        $data = array(
            'loyalty_enabled' => $this->input->post('loyalty_enabled', TRUE) ? 1 : 0,
            'earning_type' => $this->input->post('earning_type', TRUE),
            'spend_amount' => $this->input->post('spend_amount', TRUE) ?: 1000,
            'points_earned' => $this->input->post('points_earned', TRUE) ?: 1,
            'percentage_rate' => $this->input->post('percentage_rate', TRUE) ?: 2,
            'redemption_rate' => $this->input->post('redemption_rate', TRUE) ?: 10,
            'minimum_redemption_points' => $this->input->post('minimum_redemption_points', TRUE) ?: 100,
            'maximum_redemption_per_sale' => $this->input->post('maximum_redemption_per_sale', TRUE) ?: 0,
            'allow_partial_redemption' => $this->input->post('allow_partial_redemption', TRUE) ? 1 : 0,
            'tier_calculation' => $this->input->post('tier_calculation', TRUE),
            'flexpay_points_timing' => $this->input->post('flexpay_points_timing', TRUE),
        );

        if(!$this->db->table_exists('db_loyalty_settings')) return 'failed';
        $exists = $this->db->where('store_id', $store_id)->get('db_loyalty_settings')->row();
        if($exists){
            $this->db->where('store_id', $store_id)->update('db_loyalty_settings', $data);
        } else {
            $data['store_id'] = $store_id;
            $data['created_date'] = date('Y-m-d');
            $this->db->insert('db_loyalty_settings', $data);
        }
        return ($this->db->affected_rows() >= 0) ? "success" : "failed";
    }

    public function get_tiers()
    {
        if(!$this->db->table_exists('db_loyalty_tiers')) return array();
        return $this->db->where('store_id', get_current_store_id())
                        ->where('status', 1)
                        ->order_by('sort_order', 'asc')
                        ->get('db_loyalty_tiers')
                        ->result();
    }

    public function save_tier()
    {
        if(!$this->db->table_exists('db_loyalty_tiers')) return 'failed: db_loyalty_tiers table missing';
        $id = $this->input->post('tier_id', TRUE);
        $store_id = get_current_store_id();
        $data = array(
            'store_id' => $store_id,
            'tier_name' => $this->input->post('tier_name', TRUE),
            'minimum_spend' => $this->input->post('minimum_spend', TRUE) ?: 0,
            'minimum_points' => $this->input->post('minimum_points', TRUE) ?: 0,
            'discount_percentage' => $this->input->post('discount_percentage', TRUE) ?: 0,
            'bonus_points_percentage' => $this->input->post('bonus_points_percentage', TRUE) ?: 0,
            'priority_service' => $this->input->post('priority_service', TRUE) ? 1 : 0,
            'birthday_reward_type' => $this->input->post('birthday_reward_type', TRUE),
            'birthday_reward_value' => $this->input->post('birthday_reward_value', TRUE) ?: 0,
            'sort_order' => $this->input->post('sort_order', TRUE) ?: 0,
            'status' => 1
        );

        if(!empty($id)){
            $this->db->where('id', $id)->where('store_id', $store_id)->update('db_loyalty_tiers', $data);
        } else {
            $data['created_date'] = date('Y-m-d');
            $this->db->insert('db_loyalty_tiers', $data);
        }
        if($this->db->error()['code'] != 0){
            return 'failed: ' . $this->db->error()['message'];
        }
        return ($this->db->affected_rows() >= 0) ? "success" : "failed: no rows affected";
    }

    public function delete_tier($id)
    {
        if(!$this->db->table_exists('db_loyalty_tiers')) return 'failed';
        $store_id = get_current_store_id();
        $this->db->where('id', $id)->where('store_id', $store_id)->update('db_loyalty_tiers', array('status' => 0));
        return ($this->db->affected_rows() >= 0) ? "success" : "failed";
    }

    public function get_bonus_rules()
    {
        if(!$this->db->table_exists('db_loyalty_bonus_rules')) return array();
        return $this->db->where('store_id', get_current_store_id())
                        ->where('status', 1)
                        ->get('db_loyalty_bonus_rules')
                        ->result();
    }

    public function save_bonus_rule()
    {
        if(!$this->db->table_exists('db_loyalty_bonus_rules')) return 'failed';
        $id = $this->input->post('rule_id', TRUE);
        $store_id = get_current_store_id();
        $data = array(
            'store_id' => $store_id,
            'rule_name' => $this->input->post('rule_name', TRUE),
            'rule_type' => $this->input->post('rule_type', TRUE),
            'multiplier' => $this->input->post('multiplier', TRUE) ?: 2,
            'bonus_points' => $this->input->post('bonus_points', TRUE) ?: 0,
            'start_date' => $this->input->post('start_date', TRUE) ?: null,
            'end_date' => $this->input->post('end_date', TRUE) ?: null,
            'days_of_week' => $this->input->post('days_of_week', TRUE) ?: null,
            'status' => 1
        );
        if(!empty($id)){
            $this->db->where('id', $id)->where('store_id', $store_id)->update('db_loyalty_bonus_rules', $data);
        } else {
            $data['created_date'] = date('Y-m-d');
            $this->db->insert('db_loyalty_bonus_rules', $data);
        }
        return ($this->db->affected_rows() >= 0) ? "success" : "failed";
    }

    public function delete_bonus_rule($id)
    {
        if(!$this->db->table_exists('db_loyalty_bonus_rules')) return 'failed';
        $store_id = get_current_store_id();
        $this->db->where('id', $id)->where('store_id', $store_id)->update('db_loyalty_bonus_rules', array('status' => 0));
        return ($this->db->affected_rows() >= 0) ? "success" : "failed";
    }

    public function get_customer_loyalty_summary($customer_id)
    {
        $select_cols = ['id', 'customer_name'];
        $possible = ['loyalty_points','lifetime_spend','loyalty_tier','store_credit_balance','gift_card_balance','referral_count','birthday','last_purchase_date','average_order_value'];
        foreach($possible as $c){
            if($this->db->field_exists($c, 'db_customers')) $select_cols[] = $c;
        }
        $customer = $this->db->select(implode(', ', $select_cols))
                            ->where('id', $customer_id)
                            ->get('db_customers')
                            ->row_array();
        if(!$customer) return array();
        if(!$this->db->table_exists('db_loyalty_tiers')){
            $customer['tier'] = null;
        } else {
            $customer['tier'] = $this->db->where('store_id', get_current_store_id())
                                          ->where('tier_name', $customer['loyalty_tier'] ?? '')
                                          ->get('db_loyalty_tiers')
                                          ->row();
        }
        return $customer;
    }

    public function calculate_points_for_sale($customer_id, $sale_total, $items=array())
    {
        $settings = $this->get_settings();
        if(!$settings || !$settings->loyalty_enabled) return 0;

        $points = 0;
        if($settings->earning_type == 'spend_based'){
            if($settings->spend_amount > 0){
                $points = floor($sale_total / $settings->spend_amount) * $settings->points_earned;
            }
        } elseif($settings->earning_type == 'percentage_based'){
            $points = ($sale_total * $settings->percentage_rate) / 100;
        }

        // Apply bonus rules
        $bonus_rules = $this->get_active_bonus_rules();
        foreach($bonus_rules as $rule){
            if($rule->rule_type == 'double_points_day' || $rule->rule_type == 'weekend_bonus' || $rule->rule_type == 'holiday_bonus' || $rule->rule_type == 'campaign_bonus'){
                $points = $points * $rule->multiplier;
            }
        }

        // Apply tier bonus
        $customer = $this->db->where('id', $customer_id)->get('db_customers')->row();
        if($customer && $customer->loyalty_tier){
            $tier = $this->db->where('store_id', get_current_store_id())
                            ->where('tier_name', $customer->loyalty_tier)
                            ->get('db_loyalty_tiers')
                            ->row();
            if($tier && $tier->bonus_points_percentage > 0){
                $points = $points + ($points * $tier->bonus_points_percentage / 100);
            }
        }

        return round($points, 2);
    }

    public function get_active_bonus_rules()
    {
        $today = date('Y-m-d');
        $dow = date('w');
        $this->db->where('store_id', get_current_store_id())
                 ->where('status', 1)
                 ->group_start()
                     ->where('start_date IS NULL', null, false)
                     ->or_where('start_date <=', $today)
                 ->group_end()
                 ->group_start()
                     ->where('end_date IS NULL', null, false)
                     ->or_where('end_date >=', $today)
                 ->group_end();
        $rules = $this->db->get('db_loyalty_bonus_rules')->result();
        // Filter by day of week if specified
        $filtered = array();
        foreach($rules as $rule){
            if(!empty($rule->days_of_week)){
                $days = explode(',', $rule->days_of_week);
                if(in_array($dow, $days)){
                    $filtered[] = $rule;
                }
            } else {
                $filtered[] = $rule;
            }
        }
        return $filtered;
    }

    public function record_points($customer_id, $sales_id, $points, $type='earn', $description='')
    {
        if($points == 0) return true;
        if(!$this->db->table_exists('db_customers')) return false;
        $store_id = get_current_store_id();
        $customer = $this->db->where('id', $customer_id)->get('db_customers')->row();
        if(!$customer) return false;

        $current_points = property_exists($customer, 'loyalty_points') ? (float)$customer->loyalty_points : 0;
        $new_balance = $current_points + ($type == 'earn' || $type == 'bonus' || $type == 'birthday' || $type == 'referral' || $type == 'tier_upgrade' ? $points : -$points);
        if($new_balance < 0) $new_balance = 0;

        if($this->db->table_exists('db_loyalty_points')){
            $data = array(
                'store_id' => $store_id,
                'customer_id' => $customer_id,
                'sales_id' => $sales_id,
                'transaction_type' => $type,
                'points' => $points,
                'points_balance' => $new_balance,
                'description' => $description,
                'created_date' => date('Y-m-d'),
                'created_time' => date('H:i:s'),
                'created_by' => $this->session->userdata('inv_username')
            );
            $this->db->insert('db_loyalty_points', $data);
        }

        // Update customer points if column exists
        if($this->db->field_exists('loyalty_points', 'db_customers')){
            $this->db->where('id', $customer_id)->update('db_customers', array('loyalty_points' => $new_balance));
        }

        // Update lifetime spend and check tier upgrade
        if($type == 'earn' && $sales_id){
            $sale = $this->db->where('id', $sales_id)->get('db_sales')->row();
            if($sale && $this->db->field_exists('lifetime_spend', 'db_customers')){
                $lifetime = (property_exists($customer, 'lifetime_spend') ? (float)$customer->lifetime_spend : 0) + $sale->grand_total;
                $update = array('lifetime_spend' => $lifetime);
                if($this->db->field_exists('last_purchase_date', 'db_customers')){
                    $update['last_purchase_date'] = date('Y-m-d');
                }
                $this->db->where('id', $customer_id)->update('db_customers', $update);
                $this->check_and_upgrade_tier($customer_id);
            }
        }

        return true;
    }

    public function check_and_upgrade_tier($customer_id)
    {
        if(!$this->db->table_exists('db_customers')) return;
        if(!$this->db->field_exists('loyalty_tier', 'db_customers')) return;
        $customer = $this->db->where('id', $customer_id)->get('db_customers')->row();
        if(!$customer) return;
        $settings = $this->get_settings();
        $tiers = $this->get_tiers();
        $current_tier = null;
        foreach($tiers as $tier){
            if($settings->tier_calculation == 'lifetime_spend' && property_exists($customer, 'lifetime_spend')){
                if($customer->lifetime_spend >= $tier->minimum_spend){
                    $current_tier = $tier;
                }
            } else if(property_exists($customer, 'loyalty_points')) {
                if($customer->loyalty_points >= $tier->minimum_points){
                    $current_tier = $tier;
                }
            }
        }
        if($current_tier && (!property_exists($customer, 'loyalty_tier') || $current_tier->tier_name != $customer->loyalty_tier)){
            $this->db->where('id', $customer_id)->update('db_customers', array('loyalty_tier' => $current_tier->tier_name));
            // Record tier upgrade
            if($this->db->table_exists('db_loyalty_points')){
                $this->record_points($customer_id, null, 0, 'tier_upgrade', 'Upgraded to '.$current_tier->tier_name);
            }
        }
    }

    public function adjust_points()
    {
        $customer_id = $this->input->post('customer_id', TRUE);
        $points = $this->input->post('points', TRUE);
        $type = $this->input->post('type', TRUE); // add or subtract
        $reason = $this->input->post('reason', TRUE);

        if(empty($customer_id) || empty($points)) return 'failed';

        $points_val = abs($points);
        $txn_type = ($type == 'add') ? 'adjust' : 'adjust';
        $this->record_points($customer_id, null, $points_val, $txn_type, $reason ?: 'Manual adjustment');
        return 'success';
    }

    public function redeem_points_ajax()
    {
        $customer_id = $this->input->post('customer_id', TRUE);
        $points = $this->input->post('points', TRUE);
        $sales_id = $this->input->post('sales_id', TRUE) ?: null;
        if(empty($customer_id) || empty($points)) return 'failed';
        $this->record_points($customer_id, $sales_id, abs($points), 'redeem', 'Points redeemed');
        return 'success';
    }

    public function get_dashboard_stats()
    {
        $store_id = get_current_store_id();
        $stats = array();
        $stats['active_members'] = 0;
        $stats['total_points_issued'] = 0;
        $stats['total_points_redeemed'] = 0;
        $stats['points_available'] = 0;
        $stats['store_credit_outstanding'] = 0;
        $stats['gift_card_liability'] = 0;

        if($this->db->table_exists('db_customers') && $this->db->field_exists('loyalty_points', 'db_customers')){
            $stats['active_members'] = $this->db->where('store_id', $store_id)->where('loyalty_points >', 0)->count_all_results('db_customers');
        }
        if($this->db->table_exists('db_loyalty_points')){
            $stats['total_points_issued'] = $this->db->select('COALESCE(SUM(points),0) as total')->where('store_id', $store_id)->where_in('transaction_type', array('earn','bonus','birthday','referral'))->get('db_loyalty_points')->row()->total;
            $stats['total_points_redeemed'] = $this->db->select('COALESCE(SUM(points),0) as total')->where('store_id', $store_id)->where('transaction_type', 'redeem')->get('db_loyalty_points')->row()->total;
            $stats['points_available'] = (int)$stats['total_points_issued'] - (int)$stats['total_points_redeemed'];
        }
        if($this->db->table_exists('db_store_credit')){
            $stats['store_credit_outstanding'] = $this->db->select('COALESCE(SUM(balance),0) as total')->where('store_id', $store_id)->where('status', 'active')->get('db_store_credit')->row()->total;
        }
        if($this->db->table_exists('db_gift_cards')){
            $stats['gift_card_liability'] = $this->db->select('COALESCE(SUM(current_balance),0) as total')->where('store_id', $store_id)->where('status', 'active')->get('db_gift_cards')->row()->total;
        }
        return $stats;
    }

    // Datatable methods for product points
    public function get_product_points_datatables()
    {
        if(!$this->db->table_exists('db_loyalty_product_points')) return array();
        $store_id = get_current_store_id();
        $this->db->select('a.*, b.item_name');
        $this->db->from('db_loyalty_product_points a');
        $this->db->join('db_items b', 'b.id = a.item_id', 'left');
        $this->db->where('a.store_id', $store_id);
        if($_POST['length'] != -1) $this->db->limit($_POST['length'], $_POST['start']);
        return $this->db->get()->result();
    }
    public function count_product_points_filtered(){ return $this->get_product_points_datatables_query()->num_rows(); }
    public function count_product_points_all()
    {
        if(!$this->db->table_exists('db_loyalty_product_points')) return 0;
        $this->db->where('store_id', get_current_store_id());
        return $this->db->count_all_results('db_loyalty_product_points');
    }
    private function get_product_points_datatables_query()
    {
        if(!$this->db->table_exists('db_loyalty_product_points')) return $this->db->query('SELECT 1 WHERE 1=0');
        $this->db->select('a.*, b.item_name');
        $this->db->from('db_loyalty_product_points a');
        $this->db->join('db_items b', 'b.id = a.item_id', 'left');
        $this->db->where('a.store_id', get_current_store_id());
        return $this->db->get();
    }

    public function save_product_points()
    {
        if(!$this->db->table_exists('db_loyalty_product_points')) return 'failed';
        $id = $this->input->post('id', TRUE);
        $data = array(
            'store_id' => get_current_store_id(),
            'item_id' => $this->input->post('item_id', TRUE),
            'bonus_points' => $this->input->post('bonus_points', TRUE) ?: 0,
            'bonus_type' => $this->input->post('bonus_type', TRUE),
            'status' => 1,
            'created_date' => date('Y-m-d')
        );
        if(!empty($id)){
            $this->db->where('id', $id)->update('db_loyalty_product_points', $data);
        } else {
            $this->db->insert('db_loyalty_product_points', $data);
        }
        return ($this->db->affected_rows() >= 0) ? "success" : "failed";
    }

    // Points history datatable
    public function get_points_history_datatables()
    {
        if(!$this->db->table_exists('db_loyalty_points')) return array();
        $store_id = get_current_store_id();
        $this->db->select('a.*, b.customer_name');
        $this->db->from('db_loyalty_points a');
        $this->db->join('db_customers b', 'b.id = a.customer_id', 'left');
        $this->db->where('a.store_id', $store_id);
        if(!empty($_POST['customer_id'])) $this->db->where('a.customer_id', $_POST['customer_id']);
        $this->db->order_by('a.id', 'desc');
        if($_POST['length'] != -1) $this->db->limit($_POST['length'], $_POST['start']);
        return $this->db->get()->result();
    }
    public function count_points_history_filtered()
    {
        if(!$this->db->table_exists('db_loyalty_points')) return 0;
        $store_id = get_current_store_id();
        $this->db->from('db_loyalty_points a');
        $this->db->where('a.store_id', $store_id);
        if(!empty($_POST['customer_id'])) $this->db->where('a.customer_id', $_POST['customer_id']);
        return $this->db->count_all_results();
    }
    public function count_points_history_all()
    {
        if(!$this->db->table_exists('db_loyalty_points')) return 0;
        $this->db->where('store_id', get_current_store_id());
        return $this->db->count_all_results('db_loyalty_points');
    }

    public function get_referral_settings()
    {
        $store_id = get_current_store_id();
        if(!$this->db->table_exists('db_loyalty_settings')) return new stdClass();
        $row = $this->db->where('store_id', $store_id)->get('db_loyalty_settings')->row();
        if(!$row) $row = new stdClass();
        return $row;
    }

    public function save_referral_settings()
    {
        if(!$this->db->table_exists('db_loyalty_settings')) return 'failed: db_loyalty_settings table missing';
        $store_id = get_current_store_id();
        $data = array(
            'referral_enabled' => $this->input->post('referral_enabled', TRUE) ? 1 : 0,
            'referrer_reward_type' => $this->input->post('referrer_reward_type', TRUE),
            'referrer_reward_value' => $this->input->post('referrer_reward_value', TRUE) ?: 0,
            'new_customer_reward_type' => $this->input->post('new_customer_reward_type', TRUE),
            'new_customer_reward_value' => $this->input->post('new_customer_reward_value', TRUE) ?: 0,
            'referral_approval_required' => $this->input->post('referral_approval_required', TRUE) ? 1 : 0,
        );
        $exists = $this->db->where('store_id', $store_id)->get('db_loyalty_settings')->row();
        if($exists){
            $this->db->where('store_id', $store_id)->update('db_loyalty_settings', $data);
        } else {
            $data['store_id'] = $store_id;
            $data['created_date'] = date('Y-m-d');
            $this->db->insert('db_loyalty_settings', $data);
        }
        if($this->db->error()['code'] != 0){
            return 'failed: ' . $this->db->error()['message'];
        }
        return ($this->db->affected_rows() >= 0) ? "success" : "failed: no rows affected";
    }
}
