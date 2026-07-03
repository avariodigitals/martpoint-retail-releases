<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kitchen Display System (KDS) Model
 * Tracks order status through kitchen workflow: new → preparing → ready → served
 */
class Kitchen_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Auto-create kitchen_order entries for today's Final sales that don't have one yet
     */
    public function sync_new_orders($store_id) {
        if (!$this->db->table_exists('db_kitchen_orders')) {
            return 0;
        }
        // Sync ALL recent Final sales that don't have a kitchen_order yet
        // (not just today — so existing sales appear immediately after enabling KDS)
        $sql = "INSERT INTO db_kitchen_orders (sales_id, store_id, kds_status, created_at)
                SELECT s.id, s.store_id, 'new', NOW()
                FROM db_sales s
                LEFT JOIN db_kitchen_orders ko ON ko.sales_id = s.id
                WHERE s.store_id = ?
                  AND s.sales_status = 'Final'
                  AND ko.id IS NULL
                  AND s.status = 1";
        $this->db->query($sql, [$store_id]);
        return $this->db->affected_rows();
    }

    /**
     * Get kitchen orders with their items, optionally filtered by status
     */
    public function get_orders($store_id, $status = null, $limit = 50) {
        if (!$this->db->table_exists('db_kitchen_orders')) {
            return [];
        }

        // Build orders one by one to avoid JOIN column mismatch issues
        $this->db->select('id, sales_id, kds_status, created_at, updated_at');
        $this->db->from('db_kitchen_orders');
        $this->db->where('store_id', $store_id);

        if (!empty($status)) {
            if (is_array($status)) {
                $this->db->where_in('kds_status', $status);
            } else {
                $this->db->where('kds_status', $status);
            }
        }

        $this->db->order_by('created_at', 'ASC');
        $this->db->limit($limit);
        $ko_rows = $this->db->get()->result();

        $orders = [];
        foreach ($ko_rows as $ko) {
            $order = new stdClass();
            $order->kitchen_order_id = $ko->id;
            $order->kds_status = $ko->kds_status;
            $order->kitchen_created = $ko->created_at;
            $order->updated_at = $ko->updated_at;
            $order->sales_id = $ko->sales_id;
            $order->elapsed_seconds = time() - strtotime($ko->created_at);

            // Look up sale separately
            $sale = $this->db->query("SELECT id, sales_code, customer_id FROM db_sales WHERE id = ?", [$ko->sales_id])->row();
            if ($sale) {
                $order->sales_code = $sale->sales_code;
                $order->customer_id = $sale->customer_id;
                // Look up customer separately
                $cust = $this->db->query("SELECT customer_name FROM db_customers WHERE id = ?", [$sale->customer_id])->row();
                $order->customer_name = $cust ? $cust->customer_name : 'Walk-in';
            } else {
                $order->sales_code = 'N/A';
                $order->customer_id = null;
                $order->customer_name = 'Walk-in';
            }

            $order->items = $this->get_order_items($order->sales_id);
            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * Get items for a specific sales order
     */
    public function get_order_items($sales_id) {
        $this->db->select('si.item_id, si.sales_qty, si.description as item_note, i.item_name, i.item_code');
        $this->db->from('db_salesitems si');
        $this->db->join('db_items i', 'i.id = si.item_id', 'left');
        $this->db->where('si.sales_id', $sales_id);
        return $this->db->get()->result();
    }

    /**
     * Update kitchen order status
     * Status flow: new → preparing → ready → served
     */
    public function update_status($kitchen_order_id, $new_status) {
        $allowed = ['new', 'preparing', 'ready', 'served'];
        if (!in_array($new_status, $allowed)) {
            return false;
        }

        $this->db->where('id', $kitchen_order_id);
        $this->db->update('db_kitchen_orders', [
            'kds_status' => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Count orders by status for badge/display
     */
    public function count_by_status($store_id) {
        if (!$this->db->table_exists('db_kitchen_orders')) {
            return ['new' => 0, 'preparing' => 0, 'ready' => 0, 'served' => 0];
        }
        $query = $this->db->query(
            "SELECT kds_status, COUNT(*) as cnt 
             FROM db_kitchen_orders 
             WHERE store_id = ? 
             GROUP BY kds_status",
            [$store_id]
        );
        $result = $query ? $query->result() : [];

        $counts = ['new' => 0, 'preparing' => 0, 'ready' => 0, 'served' => 0];
        foreach ($result as $row) {
            $counts[$row->kds_status] = (int)$row->cnt;
        }
        return $counts;
    }

    /**
     * Get recent served orders (for reference/completion view)
     */
    public function get_served_orders($store_id, $limit = 20) {
        if (!$this->db->table_exists('db_kitchen_orders')) {
            return [];
        }
        return $this->get_orders($store_id, 'served', $limit);
    }
}
