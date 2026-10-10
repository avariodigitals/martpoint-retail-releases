<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Commercial measures shared by the dedicated clinic and plant dashboards. */
class Workspace_commercial_model extends CI_Model {

    public function snapshot($store_id, $clinic = false, $range = 'Today') {
        $out = array('invoices_today'=>0, 'billed_today'=>0.0, 'billed_yesterday'=>0.0,
            'collected_today'=>0.0, 'overdue_invoices'=>0, 'outstanding'=>0.0);
        if (!$this->db->table_exists('db_sales')) return $out;
        $this->load->model('dashboard_model');
        $rangeInfo = $this->dashboard_model->get_range_info($range);
        $today = $rangeInfo['to'];
        $from = $rangeInfo['from'];
        $yesterday = date('Y-m-d', strtotime($from.' -1 day'));
        $base = function() use ($store_id, $clinic) {
            $this->db->from('db_sales s')->where('s.store_id', (int)$store_id)
                ->where('s.sales_status', 'Final');
            if ($clinic) {
                $this->db->join('db_patients p', 'p.customer_id = s.customer_id AND p.store_id = s.store_id');
                $this->db->where('s.plan_id IS NOT NULL', null, false);
            }
        };
        if ($clinic && (!$this->db->table_exists('db_patients') || !$this->db->field_exists('plan_id', 'db_sales'))) return $out;
        foreach (array('today'=>array($from, $today), 'yesterday'=>array($yesterday, $yesterday)) as $period=>$dates) {
            $base();
            $row = $this->db->select('COUNT(s.id) AS n, COALESCE(SUM(s.grand_total),0) AS amount', false)
                ->where('s.sales_date >=', $dates[0])->where('s.sales_date <=', $dates[1])->get()->row();
            $out['billed_'.$period] = (float)($row->amount ?? 0);
            if ($period === 'today') $out['invoices_today'] = (int)($row->n ?? 0);
        }
        if ($this->db->field_exists('paid_amount', 'db_sales')) {
            $base();
            $row = $this->db->select('COUNT(s.id) AS n, COALESCE(SUM(GREATEST(s.grand_total - COALESCE(s.paid_amount,0),0)),0) AS amount', false)
                ->where('s.due_date <', $today)->where('s.grand_total > COALESCE(s.paid_amount,0)', null, false)
                ->get()->row();
            $out['overdue_invoices'] = (int)($row->n ?? 0);
            $base();
            $row = $this->db->select('COALESCE(SUM(GREATEST(s.grand_total - COALESCE(s.paid_amount,0),0)),0) AS amount', false)->get()->row();
            $out['outstanding'] = (float)($row->amount ?? 0);
        }
        if ($this->db->table_exists('db_salespayments')) {
            $this->db->from('db_salespayments sp')
                ->join('db_sales s', 's.id = sp.sales_id AND s.store_id = sp.store_id')
                ->where('s.store_id', (int)$store_id)->where('s.sales_status', 'Final')
                ->where('sp.payment_date >=', $from)->where('sp.payment_date <=', $today)->where('sp.status', 1);
            if ($clinic) {
                $this->db->join('db_patients p', 'p.customer_id = s.customer_id AND p.store_id = s.store_id');
                $this->db->where('s.plan_id IS NOT NULL', null, false);
            }
            $row = $this->db->select('COALESCE(SUM(sp.payment),0) AS amount', false)->get()->row();
            $out['collected_today'] = (float)($row->amount ?? 0);
        }
        return $out;
    }

    public function insights(array $snapshot, $clinic = false, array $operations = array(), $mobile = false, $rangeLabel = 'Today') {
        $out = array();
        $url = $clinic ? ($mobile ? null : base_url('patient_billing')) : base_url($mobile ? 'mobile/sale' : 'sales');
        if ($snapshot['overdue_invoices'] > 0) {
            $out[] = array('tone'=>'warn','icon'=>'fa-clock-o',
                'text'=>$snapshot['overdue_invoices'].' '.($clinic ? 'patient bill' : 'customer invoice').($snapshot['overdue_invoices'] === 1 ? ' is' : 's are').' past the due date and still has a balance.',
                'action'=>array('label'=>$clinic ? 'Patient accounts' : 'Customer invoices','url'=>$url));
        }
        if ($snapshot['billed_today'] > 0 && $snapshot['billed_yesterday'] > 0) {
            $out[] = array('tone'=>'info','icon'=>'fa-line-chart',
                'text'=>($clinic ? 'Patient billing' : 'Final invoices').' for '.$rangeLabel.' totals '.mp_format_money($snapshot['billed_today']).'.',
                'action'=>array('label'=>$clinic ? 'Patient accounts' : 'Customer invoices','url'=>$url));
        }
        if ($snapshot['collected_today'] > 0) {
            $out[] = array('tone'=>'good','icon'=>'fa-money',
                'text'=>mp_format_money($snapshot['collected_today']).' in invoice payments has been recorded for '.$rangeLabel.'.',
                'action'=>array('label'=>$clinic ? 'Patient accounts' : 'Customer invoices','url'=>$url));
        }
        if (!$clinic && !empty($operations['low_materials'])) {
            $out[] = array('tone'=>'warn','icon'=>'fa-cubes',
                'text'=>(int)$operations['low_materials'].' production input item'.((int)$operations['low_materials'] === 1 ? ' is' : 's are').' at or below its reorder level.',
                'action'=>array('label'=>'Review materials','url'=>base_url($mobile ? 'mobile/stock' : 'items')));
        }
        if (!$clinic && !empty($operations['open_quotes'])) {
            $out[] = array('tone'=>'info','icon'=>'fa-file-text-o',
                'text'=>(int)$operations['open_quotes'].' customer quotation'.((int)$operations['open_quotes'] === 1 ? ' is' : 's are').' still active.',
                'action'=>array('label'=>'Review quotations','url'=>base_url($mobile ? 'mobile/quotations' : 'quotation')));
        }
        return array_slice($out, 0, 5);
    }
}
