<?php

defined('BASEPATH') OR exit('No direct script access allowed');

function is_loyalty_enabled($store_id = null)
{
    $CI =& get_instance();
    if(empty($store_id)) $store_id = get_current_store_id();
    if(!$CI->db->table_exists('db_loyalty_settings')) return false;
    $row = $CI->db->where('store_id', $store_id)->get('db_loyalty_settings')->row();
    return ($row && $row->loyalty_enabled == 1);
}

function get_loyalty_settings($store_id = null)
{
    $CI =& get_instance();
    if(empty($store_id)) $store_id = get_current_store_id();
    if(!$CI->db->table_exists('db_loyalty_settings')){
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
    $row = $CI->db->where('store_id', $store_id)->get('db_loyalty_settings')->row();
    if(!$row){
        $CI->db->insert('db_loyalty_settings', array(
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
        $row = $CI->db->where('store_id', $store_id)->get('db_loyalty_settings')->row();
    }
    return $row;
}

function get_customer_loyalty_summary($customer_id)
{
    $CI =& get_instance();
    $select_cols = ['id', 'customer_name'];
    $possible = ['loyalty_points','lifetime_spend','loyalty_tier','store_credit_balance','gift_card_balance','referral_count','birthday','last_purchase_date','average_order_value'];
    foreach($possible as $c){
        if($CI->db->field_exists($c, 'db_customers')) $select_cols[] = $c;
    }
    return $CI->db->select(implode(', ', $select_cols))
                  ->where('id', $customer_id)
                  ->get('db_customers')
                  ->row_array();
}

function calculate_loyalty_points($customer_id, $sale_total, $items = array())
{
    $CI =& get_instance();
    $CI->load->model('loyalty_model');
    return $CI->loyalty_model->calculate_points_for_sale($customer_id, $sale_total, $items);
}

function get_loyalty_tier_discount($tier_name, $store_id = null)
{
    $CI =& get_instance();
    if(empty($store_id)) $store_id = get_current_store_id();
    if(!$CI->db->table_exists('db_loyalty_tiers')) return 0;
    $tier = $CI->db->where('store_id', $store_id)->where('tier_name', $tier_name)->get('db_loyalty_tiers')->row();
    return $tier ? floatval($tier->discount_percentage) : 0;
}

function get_active_loyalty_bonus_rules($store_id = null)
{
    $CI =& get_instance();
    $CI->load->model('loyalty_model');
    return $CI->loyalty_model->get_active_bonus_rules();
}

function format_loyalty_points($points)
{
    return number_format($points, 0);
}

function generate_referral_code($customer_id)
{
    $prefix = 'REF';
    $code = $prefix . str_pad($customer_id, 6, '0', STR_PAD_LEFT) . strtoupper(substr(md5(uniqid()), 0, 4));
    return $code;
}
