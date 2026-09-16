<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Creator_membership_model extends CI_Model {

    public function __construct(){
        parent::__construct();
    }

    public function get($id, $storeId = null){
        $storeId = $storeId ?: get_current_store_id();
        return $this->db->where('id', $id)->where('store_id', $storeId)->where('status', 1)->get('db_memberships')->row();
    }

    public function getByItemId($itemId, $storeId = null){
        $storeId = $storeId ?: get_current_store_id();
        return $this->db->where('item_id', $itemId)->where('store_id', $storeId)->where('status', 1)->get('db_memberships')->row();
    }

    public function getMemberships($storeId = null, $limit = 50, $offset = 0){
        $storeId = $storeId ?: get_current_store_id();
        return $this->db->where('store_id', $storeId)->where('status', 1)->order_by('id','desc')->limit($limit, $offset)->get('db_memberships')->result();
    }

    public function saveMembership($data, $id = null){
        if($id){
            $this->db->where('id', $id)->update('db_memberships', array_merge($data, ['updated_at' => date('Y-m-d H:i:s')]));
            return $id;
        }
        $this->db->insert('db_memberships', array_merge($data, ['created_at' => date('Y-m-d H:i:s')]));
        return $this->db->insert_id();
    }

    public function getSubscription($customerId, $membershipId, $storeId = null){
        $storeId = $storeId ?: get_current_store_id();
        return $this->db->where('customer_id', $customerId)->where('membership_id', $membershipId)->where('store_id', $storeId)->where_in('status',['active','paid'])->get('db_membership_subscriptions')->row();
    }

    public function getSubscriptionsByCustomer($customerId, $storeId = null){
        $storeId = $storeId ?: get_current_store_id();
        return $this->db->where('customer_id', $customerId)->where('store_id', $storeId)->order_by('id','desc')->get('db_membership_subscriptions')->result();
    }

    public function createSubscription($customerId, $membershipId, $itemId, $orderId, $storeId = null){
        $storeId = $storeId ?: get_current_store_id();
        $existing = $this->getSubscription($customerId, $membershipId, $storeId);
        if($existing) return $existing->id;

        $membership = $this->getByItemId($itemId, $storeId);
        $interval = $membership ? $membership->billing_interval : 'monthly';
        $start = date('Y-m-d');
        $end = $this->_addInterval($start, $interval);

        $this->db->insert('db_membership_subscriptions', [
            'store_id'      => $storeId,
            'customer_id'   => $customerId,
            'membership_id' => $membershipId,
            'item_id'       => $itemId,
            'order_id'      => $orderId,
            'start_date'    => $start,
            'end_date'      => $end,
            'next_billing_at' => $end,
            'status'        => 'active',
            'payment_status' => 'paid',
            'created_at'    => date('Y-m-d H:i:s')
        ]);
        return $this->db->insert_id();
    }

    public function isActive($subscription){
        if(!$subscription || $subscription->status !== 'active') return false;
        if(!empty($subscription->end_date) && $subscription->end_date < date('Y-m-d')) return false;
        return true;
    }

    private function _addInterval($date, $interval){
        $ts = strtotime($date);
        switch($interval){
            case 'weekly':  return date('Y-m-d', strtotime('+1 week', $ts));
            case 'monthly': return date('Y-m-d', strtotime('+1 month', $ts));
            case 'yearly':  return date('Y-m-d', strtotime('+1 year', $ts));
            default:        return date('Y-m-d', strtotime('+1 month', $ts));
        }
    }
}
