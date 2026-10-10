<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Store-scoped operating measures for the palm / vegetable oil workspace. */
class Oil_processing_model extends CI_Model {

    public function dashboard_stats($store_id) {
        $stats = [
            'open_runs' => 0, 'today_runs' => 0, 'completed_month' => 0,
            'inputs' => 0, 'finished_products' => 0, 'low_materials' => 0,
            'awaiting_receipt' => 0, 'open_quotes' => 0,
            'sales_month' => 0, 'customer_due' => 0,
        ];
        $month_start = date('Y-m-01');
        $month_end = date('Y-m-t');

        if ($this->db->table_exists('db_production_batches')) {
            $stats['open_runs'] = (int)$this->db->where('store_id', $store_id)
                ->where_not_in('status', ['completed', 'cancelled'])->count_all_results('db_production_batches');
            $stats['today_runs'] = (int)$this->db->where('store_id', $store_id)
                ->where('scheduled_date', date('Y-m-d'))->where_not_in('status', ['completed', 'cancelled'])
                ->count_all_results('db_production_batches');
            $stats['completed_month'] = (int)$this->db->where('store_id', $store_id)
                ->where('status', 'completed')->where('scheduled_date >=', $month_start)
                ->where('scheduled_date <=', $month_end)->count_all_results('db_production_batches');
        }

        if ($this->db->table_exists('db_items')) {
            $has_sale_flag = $this->db->field_exists('not_for_sale', 'db_items');
            $items_query = $this->db->where('store_id', $store_id)->where('status', 1);
            if ($has_sale_flag) $items_query->where('not_for_sale', 1);
            $stats['inputs'] = (int)$items_query->count_all_results('db_items');
            if ($has_sale_flag) {
                $stats['finished_products'] = (int)$this->db->where('store_id', $store_id)
                    ->where('status', 1)->where('not_for_sale', 0)->count_all_results('db_items');
            }
            if ($has_sale_flag && $this->db->field_exists('alert_qty', 'db_items') && $this->db->field_exists('stock', 'db_items')) {
                $stats['low_materials'] = (int)$this->db->where('store_id', $store_id)->where('status', 1)
                    ->where('not_for_sale', 1)->where('alert_qty >', 0)->where('stock <= alert_qty', null, false)
                    ->count_all_results('db_items');
            }
        }

        if ($this->db->table_exists('db_purchase') && $this->db->field_exists('purchase_status', 'db_purchase')) {
            $stats['awaiting_receipt'] = (int)$this->db->where('store_id', $store_id)
                ->where_in('purchase_status', ['Draft', 'Ordered', 'Partially Received'])->count_all_results('db_purchase');
        }
        if ($this->db->table_exists('db_quotation') && $this->db->field_exists('sales_status', 'db_quotation')) {
            $stats['open_quotes'] = (int)$this->db->where('store_id', $store_id)
                ->where_not_in('sales_status', ['Converted', 'Expired', 'Cancelled'])->count_all_results('db_quotation');
        }
        if ($this->db->table_exists('db_sales') && $this->db->field_exists('sales_date', 'db_sales')
            && $this->db->field_exists('grand_total', 'db_sales') && $this->db->field_exists('sales_status', 'db_sales')) {
            $due_sql = $this->db->field_exists('paid_amount', 'db_sales')
                ? 'COALESCE(SUM(GREATEST(grand_total - paid_amount,0)),0) AS due'
                : '0 AS due';
            $row = $this->db->select('COALESCE(SUM(grand_total),0) AS amount', false)
                ->where('store_id', $store_id)->where('sales_status', 'Final')
                ->where('sales_date >=', $month_start)->where('sales_date <=', $month_end)->get('db_sales')->row();
            if ($row) $stats['sales_month'] = (float)$row->amount;
            if ($this->db->field_exists('paid_amount', 'db_sales')) {
                $due = $this->db->select($due_sql, false)->where('store_id', $store_id)
                    ->where('sales_status', 'Final')->get('db_sales')->row();
                if ($due) $stats['customer_due'] = (float)$due->due;
            }
        }
        return $stats;
    }
}
