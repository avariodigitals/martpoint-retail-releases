<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Laundry Workflow Model — Per-Item Status Tracking
 */
class Laundry_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Sync new POS sales into laundry queue.
     * Also creates per-item entries in db_laundry_order_items.
     */
    public function sync_new_orders($store_id) {
        if (!$this->db->table_exists('db_laundry_orders')) return;

        // Sync order-level records
        $sql = "INSERT INTO db_laundry_orders (sales_id, store_id, status, created_at, updated_at)
                SELECT s.id, s.store_id, 'dropped_off', NOW(), NOW()
                FROM db_sales s
                LEFT JOIN db_laundry_orders lo ON lo.sales_id = s.id
                WHERE s.store_id = ?
                  AND s.sales_status = 'Final'
                  AND s.status = 1
                  AND lo.id IS NULL";
        $this->db->query($sql, [$store_id]);

        // Sync per-item entries for newly created orders
        if ($this->db->table_exists('db_laundry_order_items')) {
            $this->sync_order_items($store_id);
            // Re-sync service_type for existing rows when item config has changed
            $this->db->query(
                "UPDATE db_laundry_order_items li
                 JOIN db_items i ON i.id = li.item_id
                 SET li.service_type = i.laundry_service_type,
                     li.updated_at = NOW()
                 WHERE i.laundry_service_type IS NOT NULL
                   AND i.laundry_service_type != ''
                   AND li.service_type != i.laundry_service_type"
            );
        }
    }

    /**
     * Create per-item laundry tracking entries for new orders
     */
    private function sync_order_items($store_id) {
        // Find laundry orders that don't have item entries yet
        $new_orders = $this->db->query(
            "SELECT lo.id as laundry_order_id, lo.sales_id, lo.status as order_status
             FROM db_laundry_orders lo
             LEFT JOIN db_laundry_order_items li ON li.laundry_order_id = lo.id
             WHERE lo.store_id = ? AND li.id IS NULL",
            [$store_id]
        )->result();

        foreach ($new_orders as $order) {
            $sale_items = $this->db->where('sales_id', $order->sales_id)->get('db_salesitems')->result();
            foreach ($sale_items as $si) {
                $service_type = $this->detect_service_type($si->item_id, $si->description);
                // Derive item status from order status so old orders retain their workflow position
                $item_status = $this->derive_item_status_from_order($order->order_status, $service_type);
                // Defensive: db_salesitems may not have an 'id' column
                $salesitem_id = property_exists($si, 'id') ? $si->id : 0;
                $this->db->insert('db_laundry_order_items', [
                    'laundry_order_id' => $order->laundry_order_id,
                    'salesitem_id' => $salesitem_id,
                    'item_id' => $si->item_id,
                    'service_type' => $service_type,
                    'item_status' => $item_status,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }

    /**
     * Detect laundry service type from item config or description
     */
    private function detect_service_type($item_id, $description = '') {
        // 1. Configured value from item record (set at creation for laundry businesses)
        $item = $this->db->where('id', $item_id)->get('db_items')->row();
        if ($item && !empty($item->laundry_service_type)) {
            return $item->laundry_service_type;
        }

        // 2. Fallback: detect from description/name for backward compat / non-laundry
        $text = strtolower($description ?? '');
        if (strpos($text, 'dry clean') !== false || strpos($text, 'dryclean') !== false) {
            return 'dry_clean';
        }
        if (strpos($text, 'wash') !== false && strpos($text, 'iron') !== false) {
            return 'wash_iron';
        }
        if (strpos($text, 'iron') !== false || strpos($text, 'press') !== false) {
            return 'iron_only';
        }
        if (strpos($text, 'wash') !== false || strpos($text, 'clean') !== false) {
            return 'wash_only';
        }

        return 'wash_iron'; // safe default
    }

    /**
     * Derive per-item status from the order's current status.
     * Preserves workflow position for orders already moved to washing/ironing.
     */
    private function derive_item_status_from_order($order_status, $service_type) {
        switch ($order_status) {
            case 'washing':
                // Items that need washing should already be washing
                if (in_array($service_type, ['wash_only', 'wash_iron', 'dry_clean'])) {
                    return 'washing';
                }
                // Iron-only items skip washing → completed
                if ($service_type === 'iron_only') {
                    return 'completed';
                }
                return 'pending';

            case 'ironing':
                // Wash+iron items have finished washing
                if (in_array($service_type, ['wash_iron', 'dry_clean'])) {
                    return 'ironing';
                }
                // Iron-only items start at ironing
                if ($service_type === 'iron_only') {
                    return 'ironing';
                }
                // Wash-only items still pass through ironing stage for visual flow
                if ($service_type === 'wash_only') {
                    return 'ironing';
                }
                return 'pending';

            case 'ready':
            case 'collected':
                return 'completed';

            case 'dropped_off':
            default:
                return 'pending';
        }
    }

    /**
     * Get orders with per-item status
     */
    public function get_orders($store_id, $status = null, $limit = 50) {
        if (!$this->db->table_exists('db_laundry_orders')) return [];

        $this->db->select('id as laundry_order_id, sales_id, status, tag_number, service_type, notes,
                           created_at as laundry_created, updated_at');
        $this->db->from('db_laundry_orders');
        $this->db->where('store_id', $store_id);
        if (!empty($status)) {
            if (is_array($status)) {
                $this->db->where_in('status', $status);
            } else {
                $this->db->where('status', $status);
            }
        }
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        $result = $this->db->get();
        $orders = $result ? $result->result() : [];

        foreach ($orders as $o) {
            $sale = $this->db->where('id', $o->sales_id)->get('db_sales')->row();
            if ($sale) {
                $o->sales_code = $sale->sales_code;
                $o->customer_id = $sale->customer_id;
                $cust = $this->db->where('id', $sale->customer_id)->get('db_customers')->row();
                $o->customer_name = $cust ? $cust->customer_name : 'Walk-in';
            } else {
                $o->sales_code = 'N/A';
                $o->customer_name = 'Walk-in';
            }
            $o->items = $this->get_order_items_with_status($o->laundry_order_id, $o->sales_id);
            $o->item_summary = $this->summarize_item_status($o->items);
            // Defensive: avoid 1970 epoch when created_at is NULL
            $created_ts = (!empty($o->laundry_created) && $o->laundry_created !== '0000-00-00 00:00:00')
                ? strtotime($o->laundry_created) : time();
            $o->elapsed_seconds = time() - $created_ts;
            $o->drop_off_time = date('M j, g:i A', $created_ts);
        }
        return $orders;
    }

    /**
     * Get items for a laundry order with their per-item status
     */
    public function get_order_items_with_status($laundry_order_id, $sales_id) {
        if ($this->db->table_exists('db_laundry_order_items')) {
            // Use per-item tracking
            // Join via sales_id + item_id because db_salesitems may not have a standalone 'id'
            $items = $this->db->query(
                "SELECT li.id as laundry_item_id,
                        COALESCE(NULLIF(i.laundry_service_type, ''), li.service_type) as service_type,
                        li.item_status, si.sales_qty, si.description, i.item_name
                 FROM db_laundry_order_items li
                 LEFT JOIN db_salesitems si ON si.sales_id = ? AND si.item_id = li.item_id
                 LEFT JOIN db_items i ON i.id = li.item_id
                 WHERE li.laundry_order_id = ?",
                [$sales_id, $laundry_order_id]
            )->result();
            if (!empty($items)) return $items;
        }
        // Fallback: basic items without per-item status
        return $this->get_order_items_fallback($sales_id);
    }

    /**
     * Fallback item fetch without per-item tracking
     */
    private function get_order_items_fallback($sales_id) {
        $this->db->select('si.sales_qty, si.description, i.item_name, i.item_id');
        $this->db->from('db_salesitems si');
        $this->db->join('db_items i', 'i.id = si.item_id', 'left');
        $this->db->where('si.sales_id', $sales_id);
        $items = $this->db->get()->result();
        foreach ($items as $itm) {
            $itm->service_type = $this->detect_service_type($itm->item_id, $itm->description);
            $itm->item_status = 'pending';
        }
        return $items;
    }

    /**
     * Summarize item statuses to determine available actions
     */
    public function summarize_item_status($items) {
        $summary = [
            'has_wash_items' => false,
            'has_iron_items' => false,
            'pending_wash' => 0,
            'washing' => 0,
            'washed' => 0,
            'pending_iron' => 0,
            'ironing' => 0,
            'ironed' => 0,
            'completed' => 0,
            'total' => count($items)
        ];

        foreach ($items as $itm) {
            $needs_wash = in_array($itm->service_type, ['wash_only', 'wash_iron', 'dry_clean']);
            $needs_iron = in_array($itm->service_type, ['iron_only', 'wash_iron', 'dry_clean']);

            if ($needs_wash) $summary['has_wash_items'] = true;
            if ($needs_iron) $summary['has_iron_items'] = true;

            switch ($itm->item_status) {
                case 'pending':
                    if ($needs_wash) $summary['pending_wash']++;
                    elseif ($needs_iron) $summary['pending_iron']++;
                    break;
                case 'washing':
                    $summary['washing']++;
                    break;
                case 'washed':
                    $summary['washed']++;
                    break;
                case 'ironing':
                    $summary['ironing']++;
                    break;
                case 'ironed':
                    $summary['ironed']++;
                    break;
                case 'completed':
                    $summary['completed']++;
                    break;
            }
        }

        // Determine next action
        $summary['can_start_washing'] = $summary['pending_wash'] > 0;
        $summary['can_finish_washing'] = $summary['washing'] > 0 && $summary['pending_wash'] == 0;
        $summary['can_start_ironing'] = $summary['washed'] > 0 || ($summary['pending_iron'] > 0 && !$summary['has_wash_items']);
        $summary['can_finish_ironing'] = $summary['ironing'] > 0;
        $summary['all_completed'] = $summary['completed'] == $summary['total'];

        return $summary;
    }

    /**
     * Update order status and item statuses based on action
     */
    public function update_status($laundry_order_id, $store_id, $action) {
        if (!$this->db->table_exists('db_laundry_orders')) return false;

        // Map action to new order status
        $action_map = [
            'start_washing' => 'washing',
            'finish_washing' => 'washing',
            'start_ironing' => 'ironing',
            'finish_ironing' => 'ironing',
            'mark_ready' => 'ready',
            'collected' => 'collected'
        ];

        if (!isset($action_map[$action])) return false;
        $new_status = $action_map[$action];

        // Update order status
        $this->db->where('id', $laundry_order_id);
        $this->db->where('store_id', $store_id);
        $this->db->update('db_laundry_orders', [
            'status' => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Update per-item statuses
        if ($this->db->table_exists('db_laundry_order_items')) {
            $this->update_item_statuses($laundry_order_id, $action);
        }

        return true;
    }

    /**
     * Update individual item statuses based on action
     */
    private function update_item_statuses($laundry_order_id, $action) {
        switch ($action) {
            case 'start_washing':
                // All pending wash items → washing
                $this->db->query(
                    "UPDATE db_laundry_order_items
                     SET item_status = 'washing', updated_at = NOW()
                     WHERE laundry_order_id = ? AND item_status = 'pending'
                       AND service_type IN ('wash_only', 'wash_iron', 'dry_clean')",
                    [$laundry_order_id]
                );
                // Iron-only items that don't need wash → completed
                $this->db->query(
                    "UPDATE db_laundry_order_items
                     SET item_status = 'completed', updated_at = NOW()
                     WHERE laundry_order_id = ? AND item_status = 'pending'
                       AND service_type = 'iron_only'",
                    [$laundry_order_id]
                );
                break;

            case 'finish_washing':
                // wash_iron / dry_clean items → washed (still need ironing)
                $this->db->query(
                    "UPDATE db_laundry_order_items
                     SET item_status = 'washed', updated_at = NOW()
                     WHERE laundry_order_id = ? AND item_status = 'washing'
                       AND service_type IN ('wash_iron', 'dry_clean')",
                    [$laundry_order_id]
                );
                // wash_only items → ironing (skip washed, just pass through visually)
                $this->db->query(
                    "UPDATE db_laundry_order_items
                     SET item_status = 'ironing', updated_at = NOW()
                     WHERE laundry_order_id = ? AND item_status = 'washing'
                       AND service_type = 'wash_only'",
                    [$laundry_order_id]
                );
                break;

            case 'start_ironing':
                // All washed items needing iron → ironing
                $this->db->query(
                    "UPDATE db_laundry_order_items
                     SET item_status = 'ironing', updated_at = NOW()
                     WHERE laundry_order_id = ? AND item_status = 'washed'
                       AND service_type IN ('wash_iron', 'dry_clean', 'wash_only')",
                    [$laundry_order_id]
                );
                // Iron-only items that were pending (no wash needed) → ironing
                $this->db->query(
                    "UPDATE db_laundry_order_items
                     SET item_status = 'ironing', updated_at = NOW()
                     WHERE laundry_order_id = ? AND item_status = 'pending'
                       AND service_type = 'iron_only'",
                    [$laundry_order_id]
                );
                break;

            case 'finish_ironing':
                // All ironing items → completed
                $this->db->query(
                    "UPDATE db_laundry_order_items
                     SET item_status = 'completed', updated_at = NOW()
                     WHERE laundry_order_id = ? AND item_status = 'ironing'",
                    [$laundry_order_id]
                );
                break;

            case 'mark_ready':
                // All items → completed
                $this->db->query(
                    "UPDATE db_laundry_order_items
                     SET item_status = 'completed', updated_at = NOW()
                     WHERE laundry_order_id = ? AND item_status != 'completed'",
                    [$laundry_order_id]
                );
                break;
        }
    }

    public function count_by_status($store_id) {
        if (!$this->db->table_exists('db_laundry_orders')) {
            return ['dropped_off' => 0, 'washing' => 0, 'ironing' => 0, 'ready' => 0, 'collected' => 0];
        }
        $query = $this->db->query(
            "SELECT status, COUNT(*) as cnt FROM db_laundry_orders WHERE store_id = ? GROUP BY status",
            [$store_id]
        );
        $result = $query ? $query->result() : [];
        $counts = ['dropped_off' => 0, 'washing' => 0, 'ironing' => 0, 'ready' => 0, 'collected' => 0];
        foreach ($result as $row) {
            $counts[$row->status] = (int)$row->cnt;
        }
        return $counts;
    }

    public function get_collected_orders($store_id, $limit = 10) {
        return $this->get_orders($store_id, 'collected', $limit);
    }
}
