<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Table_report extends MY_Controller {

    public function __construct(){
        parent::__construct();
        $this->load_global();
    }

    public function index(){
        $this->permission_check('sales_report');
        $store_id = get_current_store_id();

        // POS sales by table
        $pos = $this->db->query("SELECT t.id, t.table_name, t.zone, t.capacity, COUNT(s.id) as pos_orders, COALESCE(SUM(s.grand_total),0) as pos_total
            FROM db_tables t
            LEFT JOIN db_sales s ON s.table_id = t.id AND s.store_id = t.store_id AND s.sales_status = 'Final'
            WHERE t.store_id = ?
            GROUP BY t.id
            ORDER BY t.sort_order, t.table_name", [$store_id])->result();

        // Online / QR orders by table number
        $online = $this->db->query("SELECT o.table_number, COUNT(o.id) as online_orders, COALESCE(SUM(o.grand_total),0) as online_total
            FROM db_online_orders o
            WHERE o.store_id = ?
              AND o.table_number != ''
              AND o.order_status != 'cancelled'
            GROUP BY o.table_number", [$store_id])->result();

        // Build map keyed by table name, including tables with no sales
        $rows = [];
        foreach ($pos as $p) {
            $key = $p->table_name;
            $rows[$key] = (object)[
                'table_name' => $p->table_name,
                'zone' => $p->zone,
                'pos_orders' => (int)$p->pos_orders,
                'pos_total' => (float)$p->pos_total,
                'online_orders' => 0,
                'online_total' => 0,
            ];
        }
        foreach ($online as $o) {
            $key = $o->table_number;
            if (!isset($rows[$key])) {
                $rows[$key] = (object)[
                    'table_name' => $o->table_number,
                    'zone' => '',
                    'pos_orders' => 0,
                    'pos_total' => 0,
                    'online_orders' => 0,
                    'online_total' => 0,
                ];
            }
            $rows[$key]->online_orders = (int)$o->online_orders;
            $rows[$key]->online_total = (float)$o->online_total;
        }

        // Totals
        $totals = (object)[
            'pos_orders' => array_sum(array_column($rows, 'pos_orders')),
            'pos_total' => array_sum(array_column($rows, 'pos_total')),
            'online_orders' => array_sum(array_column($rows, 'online_orders')),
            'online_total' => array_sum(array_column($rows, 'online_total')),
        ];

        $data = $this->data;
        $data['page_title'] = 'Table Report';
        $data['rows'] = $rows;
        $data['totals'] = $totals;
        $data['content'] = $this->load->view('reports/desktop/table_report', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }
}