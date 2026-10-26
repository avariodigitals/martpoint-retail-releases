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
     * A kitchen ticket is operational work, not proof that a sale exists.
     * Only items explicitly routed to Kitchen or Bar belong on the board;
     * Bar currently uses the same board until a dedicated bar lane exists.
     * Before the routing migration exists, retain the legacy behaviour so an
     * older installation does not silently stop showing kitchen work.
     */
    private function route_column_available() {
        return $this->db->field_exists('fulfilment_route', 'db_items');
    }

    public function sale_requires_kitchen($sales_id) {
        if (!$this->route_column_available()) return true;
        return $this->db->query(
            "SELECT 1 FROM db_salesitems si
             JOIN db_items i ON i.id = si.item_id
             WHERE si.sales_id = ?
               AND COALESCE(i.fulfilment_route, 'counter') IN ('kitchen','bar')
             LIMIT 1",
            [(int) $sales_id]
        )->num_rows() > 0;
    }

    public function online_order_requires_kitchen($online_order_id) {
        if (!$this->route_column_available()) return true;
        return $this->db->query(
            "SELECT 1 FROM db_online_order_items oi
             JOIN db_items i ON i.id = oi.item_id
             WHERE oi.order_id = ?
               AND COALESCE(i.fulfilment_route, 'counter') IN ('kitchen','bar')
             LIMIT 1",
            [(int) $online_order_id]
        )->num_rows() > 0;
    }

    /**
     * Auto-create kitchen_order entries for today's Final sales that don't have one yet
     */
    public function sync_new_orders($store_id) {
        if (!$this->db->table_exists('db_kitchen_orders')) {
            return 0;
        }
        // Sync recent Final sales that contain at least one made-to-order line.
        // (not just today — so existing sales appear immediately after enabling KDS)
        $has_table_col = $this->db->field_exists('table_id', 'db_kitchen_orders') && $this->db->field_exists('table_id', 'db_sales');
        $has_warehouse_col = $this->db->field_exists('warehouse_id', 'db_kitchen_orders');
        $kitchen_sale_filter = $this->route_column_available()
            ? " AND EXISTS (
                    SELECT 1 FROM db_salesitems si
                    JOIN db_items i ON i.id = si.item_id
                    WHERE si.sales_id = s.id
                      AND COALESCE(i.fulfilment_route, 'counter') IN ('kitchen','bar')
                )"
            : '';
        if ($has_table_col && $has_warehouse_col) {
            $sql = "INSERT INTO db_kitchen_orders (sales_id, store_id, warehouse_id, table_id, kds_status, created_at)
                    SELECT s.id, s.store_id, s.warehouse_id, s.table_id, 'new', NOW()
                    FROM db_sales s
                    LEFT JOIN db_kitchen_orders ko ON ko.sales_id = s.id
                    WHERE s.store_id = ?
                      AND s.sales_status = 'Final'
                      AND ko.id IS NULL
                      AND s.status = 1" . $kitchen_sale_filter;
        } elseif ($has_table_col) {
            $sql = "INSERT INTO db_kitchen_orders (sales_id, store_id, table_id, kds_status, created_at)
                    SELECT s.id, s.store_id, s.table_id, 'new', NOW()
                    FROM db_sales s
                    LEFT JOIN db_kitchen_orders ko ON ko.sales_id = s.id
                    WHERE s.store_id = ? AND s.sales_status = 'Final' AND ko.id IS NULL AND s.status = 1" . $kitchen_sale_filter;
        } elseif ($has_warehouse_col) {
            $sql = "INSERT INTO db_kitchen_orders (sales_id, store_id, warehouse_id, kds_status, created_at)
                    SELECT s.id, s.store_id, s.warehouse_id, 'new', NOW()
                    FROM db_sales s
                    LEFT JOIN db_kitchen_orders ko ON ko.sales_id = s.id
                    WHERE s.store_id = ? AND s.sales_status = 'Final' AND ko.id IS NULL AND s.status = 1" . $kitchen_sale_filter;
        } else {
            $sql = "INSERT INTO db_kitchen_orders (sales_id, store_id, kds_status, created_at)
                    SELECT s.id, s.store_id, 'new', NOW()
                    FROM db_sales s
                    LEFT JOIN db_kitchen_orders ko ON ko.sales_id = s.id
                    WHERE s.store_id = ?
                      AND s.sales_status = 'Final'
                      AND ko.id IS NULL
                      AND s.status = 1" . $kitchen_sale_filter;
        }
        $this->db->query($sql, [$store_id]);

        // Sync pending table QR / online orders into the kitchen queue
        if ($this->db->table_exists('db_kitchen_orders') && $this->db->field_exists('online_order_id', 'db_kitchen_orders')) {
            $onlineInsert = $has_warehouse_col
                ? "INSERT INTO db_kitchen_orders (sales_id, online_order_id, store_id, warehouse_id, table_id, kds_status, created_at)
                SELECT 0, o.id, o.store_id, o.warehouse_id, 0, 'new', o.created_at"
                : "INSERT INTO db_kitchen_orders (sales_id, online_order_id, store_id, table_id, kds_status, created_at)
                SELECT 0, o.id, o.store_id, 0, 'new', o.created_at";
            $this->db->query($onlineInsert . "
                FROM db_online_orders o
                LEFT JOIN db_kitchen_orders ko ON ko.online_order_id = o.id
                WHERE o.store_id = ?
                  AND o.order_type IN ('product','mixed')
                  AND o.order_status IN ('pending','paid','processing')
                  AND ko.id IS NULL
                  AND NOT EXISTS (
                      SELECT 1 FROM db_kitchen_orders linked
                      WHERE linked.store_id = o.store_id
                        AND linked.sales_id = o.sales_id
                        AND o.sales_id > 0
                  )" . ($this->route_column_available() ? "
                  AND EXISTS (
                      SELECT 1 FROM db_online_order_items oi
                      JOIN db_items i ON i.id = oi.item_id
                      WHERE oi.order_id = o.id
                        AND COALESCE(i.fulfilment_route, 'counter') IN ('kitchen','bar')
                  )" : ''), [$store_id]);

            // Shelf-only online orders are already ready for handover once
            // accepted/paid; they do not need a phantom kitchen ticket.
            if ($this->route_column_available()) {
                $this->db->query(
                    "UPDATE db_online_orders o
                     SET order_status = 'ready', updated_at = NOW()
                     WHERE o.store_id = ?
                       AND o.order_type IN ('product','mixed')
                       AND o.order_status IN ('paid','processing')
                       AND NOT EXISTS (
                           SELECT 1 FROM db_online_order_items oi
                           JOIN db_items i ON i.id = oi.item_id
                           WHERE oi.order_id = o.id
                             AND COALESCE(i.fulfilment_route, 'counter') IN ('kitchen','bar')
                       )",
                    [$store_id]
                );
            }
        }

        return $this->db->affected_rows();
    }

    /**
     * Get kitchen orders with their items, optionally filtered by status
     */
    public function get_orders($store_id, $status = null, $limit = 50, $direction = 'DESC', $branch_id = '', $allowed_branch_ids = null) {
        if (!$this->db->table_exists('db_kitchen_orders')) {
            return [];
        }

        // Build orders one by one to avoid JOIN column mismatch issues
        // Keep the order discriminator in the row we hydrate. Online/QR
        // tickets are stored with sales_id=0 and online_order_id set; when
        // this column is omitted they are silently read as counter tickets,
        // which means the lifecycle model never updates the customer's order.
        $select = 'id, sales_id, kds_status, created_at, updated_at';
        if ($this->db->field_exists('online_order_id', 'db_kitchen_orders')) {
            $select .= ', online_order_id';
        }
        if ($this->db->field_exists('warehouse_id', 'db_kitchen_orders')) {
            $select .= ', warehouse_id';
        }
        $this->db->select($select);
        $this->db->from('db_kitchen_orders');
        $this->db->where('store_id', $store_id);

        $has_warehouse_col = $this->db->field_exists('warehouse_id', 'db_kitchen_orders');
        if ($has_warehouse_col) {
            if ($branch_id !== '') {
                $this->db->where('warehouse_id', (int) $branch_id);
            } elseif ($allowed_branch_ids !== null) {
                if (empty($allowed_branch_ids)) {
                    $this->db->where('1 = 0', null, false);
                } else {
                    $this->db->where_in('warehouse_id', array_map('intval', $allowed_branch_ids));
                }
            }
        } elseif ($branch_id !== '' || $allowed_branch_ids !== null) {
            // An installation without the location migration cannot safely
            // claim that a ticket belongs to a particular outlet.
            $this->db->where('1 = 0', null, false);
        }

        if ($this->route_column_available()) {
            $route_sql = "((sales_id > 0 AND EXISTS (
                SELECT 1 FROM db_salesitems si
                JOIN db_items i ON i.id = si.item_id
                WHERE si.sales_id = db_kitchen_orders.sales_id
                  AND COALESCE(i.fulfilment_route, 'counter') IN ('kitchen','bar')
            ))";
            if ($this->db->field_exists('online_order_id', 'db_kitchen_orders')) {
                $route_sql .= " OR (online_order_id > 0 AND EXISTS (
                    SELECT 1 FROM db_online_order_items oi
                    JOIN db_items i ON i.id = oi.item_id
                    WHERE oi.order_id = db_kitchen_orders.online_order_id
                      AND COALESCE(i.fulfilment_route, 'counter') IN ('kitchen','bar')
                ))";
            }
            $this->db->where($route_sql . ')', null, false);
        }

        if (!empty($status)) {
            if (is_array($status)) {
                $this->db->where_in('kds_status', $status);
            } else {
                $this->db->where('kds_status', $status);
            }
        }

        // Newest first. The board is a live service view: loading the oldest 50
        // rows meant a store with a backlog opened the KDS to tickets from
        // months ago and never saw the order that just landed.
        $this->db->order_by('created_at', $direction === 'ASC' ? 'ASC' : 'DESC');
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
            $order->online_order_id = $ko->online_order_id ?? 0;
            $order->warehouse_id = (int) ($ko->warehouse_id ?? 0);
            $order->elapsed_seconds = time() - strtotime($ko->created_at);

            if ($order->online_order_id > 0) {
                // Online / table QR order
                $online = $this->db->query("SELECT id, order_code, customer_name, table_number, created_at, order_status FROM db_online_orders WHERE id = ?", [$order->online_order_id])->row();
                if ($online) {
                    $order->sales_code = $online->order_code;
                    $order->customer_id = null;
                    $order->customer_name = $online->customer_name ?: 'Walk-in';
                    $order->table_id = 0;
                    $order->table_name = $online->table_number;
                } else {
                    $order->sales_code = 'N/A';
                    $order->customer_id = null;
                    $order->customer_name = 'Walk-in';
                    $order->table_name = '';
                }
                $order->items = $this->get_order_items(0, $order->online_order_id);
            } else {
                // In-store POS sale
                $sale = $this->db->query("SELECT id, sales_code, customer_id, table_id FROM db_sales WHERE id = ?", [$ko->sales_id])->row();
                if ($sale) {
                    $order->sales_code = $sale->sales_code;
                    $order->customer_id = $sale->customer_id;
                    $order->table_id = $sale->table_id ?? 0;
                    $order->table_name = '';
                    if ($order->table_id > 0 && $this->db->table_exists('db_tables')) {
                        $tbl = $this->db->where('id', $order->table_id)->get('db_tables')->row();
                        if ($tbl) { $order->table_name = $tbl->table_name; }
                    }
                    // Look up customer separately
                    $cust = $this->db->query("SELECT customer_name FROM db_customers WHERE id = ?", [$sale->customer_id])->row();
                    $order->customer_name = $cust ? $cust->customer_name : 'Walk-in';
                } else {
                    $order->sales_code = 'N/A';
                    $order->customer_id = null;
                    $order->customer_name = 'Walk-in';
                }
                $order->items = $this->get_order_items($order->sales_id);
            }
            // Older versions created tickets for every sale. Once routing is
            // enabled, an empty filtered ticket is a historical phantom, not
            // kitchen work, so keep it off the board without deleting history.
            if ($this->route_column_available() && empty($order->items)) {
                continue;
            }
            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * Get items for a specific sales order
     */
    public function get_order_items($sales_id, $online_order_id = 0) {
        if ($online_order_id > 0) {
            $this->db->select('oi.item_id, oi.qty as sales_qty, "" as item_note, oi.item_name, "" as item_code');
            $this->db->from('db_online_order_items oi');
            if ($this->route_column_available()) {
                $this->db->join('db_items i', 'i.id = oi.item_id', 'left');
                $this->db->where_in('i.fulfilment_route', ['kitchen','bar']);
            }
            $this->db->where('oi.order_id', $online_order_id);
            return $this->db->get()->result();
        }
        $this->db->select('si.item_id, si.sales_qty, si.description as item_note, i.item_name, i.item_code');
        $this->db->from('db_salesitems si');
        $this->db->join('db_items i', 'i.id = si.item_id', 'left');
        if ($this->route_column_available()) {
            $this->db->where_in('i.fulfilment_route', ['kitchen','bar']);
        }
        $this->db->where('si.sales_id', $sales_id);
        return $this->db->get()->result();
    }

    /**
     * Update kitchen order status
     * Status flow: new → preparing → ready → served
     *
     * The kitchen board is one view of the order's lifecycle, not a second
     * one: the move is checked and recorded by Order_lifecycle_model, which
     * also moves the order's own status so the customer's tracking page and
     * the board cannot tell different stories. Tickets are scoped to the
     * signed-in store, so a ticket id from another business changes nothing.
     */
    public $last_move = null;

    public function get_order_branch_id($kitchen_order_id) {
        if (!$this->db->table_exists('db_kitchen_orders')
            || !$this->db->field_exists('warehouse_id', 'db_kitchen_orders')) {
            return null;
        }
        $row = $this->db->select('warehouse_id')
            ->where('id', (int) $kitchen_order_id)
            ->where('store_id', get_current_store_id())
            ->get('db_kitchen_orders')->row();
        return $row ? (int) $row->warehouse_id : null;
    }

    public function update_status($kitchen_order_id, $new_status) {
        $this->load->model('order_lifecycle_model');
        $this->last_move = $this->order_lifecycle_model->move_ticket(
            get_current_store_id(), $kitchen_order_id, $new_status, ['channel' => 'kds']
        );
        return !empty($this->last_move['ok']);
    }

    /**
     * Count orders by status for badge/display
     */
    public function count_by_status($store_id, $branch_id = '', $allowed_branch_ids = null) {
        // 'cancelled' is part of the shape even though no board column shows
        // it, so a dead ticket is counted somewhere rather than vanishing.
        $shape = ['new' => 0, 'preparing' => 0, 'ready' => 0, 'served' => 0, 'cancelled' => 0];
        if (!$this->db->table_exists('db_kitchen_orders')) {
            return $shape;
        }
        $route_filter = '';
        if ($this->route_column_available()) {
            $route_filter = " AND (
                (ko.sales_id > 0 AND EXISTS (
                    SELECT 1 FROM db_salesitems si
                    JOIN db_items i ON i.id = si.item_id
                    WHERE si.sales_id = ko.sales_id
                      AND COALESCE(i.fulfilment_route, 'counter') IN ('kitchen','bar')
                ))";
            if ($this->db->field_exists('online_order_id', 'db_kitchen_orders')) {
                $route_filter .= " OR (ko.online_order_id > 0 AND EXISTS (
                    SELECT 1 FROM db_online_order_items oi
                    JOIN db_items i ON i.id = oi.item_id
                    WHERE oi.order_id = ko.online_order_id
                      AND COALESCE(i.fulfilment_route, 'counter') IN ('kitchen','bar')
                ))";
            }
            $route_filter .= ' )';
        }
        $warehouse_filter = '';
        $params = [$store_id];
        if ($this->db->field_exists('warehouse_id', 'db_kitchen_orders')) {
            if ($branch_id !== '') {
                $warehouse_filter = ' AND ko.warehouse_id = ?';
                $params[] = (int) $branch_id;
            } elseif ($allowed_branch_ids !== null) {
                if (empty($allowed_branch_ids)) {
                    $warehouse_filter = ' AND 1 = 0';
                } else {
                    $ids = array_map('intval', $allowed_branch_ids);
                    $warehouse_filter = ' AND ko.warehouse_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')';
                    foreach ($ids as $id) $params[] = $id;
                }
            }
        } elseif ($branch_id !== '' || $allowed_branch_ids !== null) {
            $warehouse_filter = ' AND 1 = 0';
        }

        $query = $this->db->query(
            "SELECT ko.kds_status, COUNT(*) as cnt
             FROM db_kitchen_orders ko
             WHERE ko.store_id = ?" . $warehouse_filter . $route_filter . "
             GROUP BY ko.kds_status",
            $params
        );
        $result = $query ? $query->result() : [];

        $counts = $shape;
        foreach ($result as $row) {
            $counts[$row->kds_status] = (int)$row->cnt;
        }
        return $counts;
    }

    /**
     * Get recent served orders (for reference/completion view)
     */
    public function get_served_orders($store_id, $limit = 20, $branch_id = '', $allowed_branch_ids = null) {
        if (!$this->db->table_exists('db_kitchen_orders')) {
            return [];
        }
        return $this->get_orders($store_id, 'served', $limit, 'DESC', $branch_id, $allowed_branch_ids);
    }
}
