<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Explicit operator command; never exposed as a public seeding endpoint. */
class Demo_examples extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (PHP_SAPI !== 'cli') { show_404(); exit; }
        $this->load->database();
        $this->load->model('Demo_seeder', 'examples');
    }

    public function run($storeId = 0) {
        $storeId = (int)$storeId;
        $store = $this->db->select('id, industry_type')->where('id', $storeId)->get('db_store')->row();
        if (!$store) { echo "Store not found.\n"; exit(1); }
        $before = $this->snapshot($storeId);
        $result = $this->examples->seed_examples($storeId, $store->industry_type);
        $after = $this->snapshot($storeId);
        $unchanged = true;
        foreach ($before as $table => $rows) foreach ($rows as $id => $hash) {
            if (!isset($after[$table][$id]) || $after[$table][$id] !== $hash) $unchanged = false;
        }
        $result['existing_records_unchanged'] = $unchanged;
        $result['existing_records_checked'] = array_sum(array_map('count', $before));
        echo json_encode($result, JSON_PRETTY_PRINT).PHP_EOL;
        if (empty($result['ok']) || !$unchanged) exit(1);
    }

    private function snapshot($storeId) {
        $out = array();
        foreach (array('db_store', 'db_items', 'db_warehouseitems', 'db_warehouse', 'db_category', 'db_units', 'db_services', 'db_suppliers', 'db_customers', 'db_medical_notes', 'db_sales', 'db_salesitems', 'db_purchase', 'db_online_orders', 'db_storefront_settings') as $table) {
            if (!$this->db->table_exists($table) || !$this->db->field_exists('id', $table)) continue;
            if ($table === 'db_store') $this->db->where('id', $storeId);
            elseif ($this->db->field_exists('store_id', $table)) $this->db->where('store_id', $storeId);
            else continue;
            foreach ($this->db->get($table)->result_array() as $row) $out[$table][$row['id']] = hash('sha256', json_encode($row));
        }
        if ($this->db->table_exists('db_medical_note_items') && $this->db->table_exists('db_medical_notes')) {
            $rows = $this->db->select('i.*')->from('db_medical_note_items i')->join('db_medical_notes n', 'n.id=i.medical_note_id')->where('n.store_id', $storeId)->get()->result_array();
            foreach ($rows as $row) $out['db_medical_note_items'][$row['id']] = hash('sha256', json_encode($row));
        }
        return $out;
    }
}
