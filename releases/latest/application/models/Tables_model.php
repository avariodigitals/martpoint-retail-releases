<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Table Management Model
 * Manage restaurant tables: zones, capacity, status, QR codes
 */
class Tables_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_all($store_id, $zone = null) {
        if (!$this->db->table_exists('db_tables')) {
            return [];
        }
        $this->db->where('store_id', $store_id);
        if (!empty($zone)) {
            $this->db->where('zone', $zone);
        }
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('table_name', 'ASC');
        return $this->db->get('db_tables')->result();
    }

    public function get_zones($store_id) {
        if (!$this->db->table_exists('db_tables')) {
            return [];
        }
        $query = $this->db->query(
            "SELECT DISTINCT zone FROM db_tables WHERE store_id = ? AND zone IS NOT NULL AND zone != '' ORDER BY zone",
            [$store_id]
        );
        return $query ? $query->result() : [];
    }

    public function get_by_id($id, $store_id) {
        if (!$this->db->table_exists('db_tables')) {
            return null;
        }
        $this->db->where('id', $id);
        $this->db->where('store_id', $store_id);
        return $this->db->get('db_tables')->row();
    }

    public function save($data, $id = null) {
        if (!$this->db->table_exists('db_tables')) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $data['updated_at'] = $now;

        if (empty($id)) {
            $data['created_at'] = $now;
            $this->db->insert('db_tables', $data);
            return $this->db->insert_id();
        } else {
            $this->db->where('id', $id);
            $this->db->where('store_id', $data['store_id']);
            $this->db->update('db_tables', $data);
            return $id;
        }
    }

    public function delete($id, $store_id) {
        if (!$this->db->table_exists('db_tables')) {
            return false;
        }
        $this->db->where('id', $id);
        $this->db->where('store_id', $store_id);
        return $this->db->delete('db_tables');
    }

    public function update_status($id, $store_id, $status) {
        if (!$this->db->table_exists('db_tables')) {
            return false;
        }
        $allowed = ['available', 'occupied', 'reserved', 'cleaning'];
        if (!in_array($status, $allowed)) {
            return false;
        }
        $this->db->where('id', $id);
        $this->db->where('store_id', $store_id);
        $this->db->update('db_tables', [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $this->db->affected_rows() > 0;
    }

    public function count_by_status($store_id) {
        if (!$this->db->table_exists('db_tables')) {
            return ['available' => 0, 'occupied' => 0, 'reserved' => 0, 'cleaning' => 0, 'total' => 0];
        }
        $query = $this->db->query(
            "SELECT status, COUNT(*) as cnt FROM db_tables WHERE store_id = ? GROUP BY status",
            [$store_id]
        );
        $result = $query ? $query->result() : [];
        $counts = ['available' => 0, 'occupied' => 0, 'reserved' => 0, 'cleaning' => 0, 'total' => 0];
        foreach ($result as $row) {
            $counts[$row->status] = (int)$row->cnt;
        }
        $counts['total'] = array_sum($counts);
        return $counts;
    }
}
