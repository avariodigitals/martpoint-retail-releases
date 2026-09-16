<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Automobile_model extends CI_Model {

    var $table = 'db_vehicles';

    public function __construct() {
        parent::__construct();
    }

    public function get_all($store_id = null, $status = null, $limit = null) {
        if (empty($store_id)) {
            $store_id = get_current_store_id();
        }
        $this->db->where('store_id', $store_id);
        $this->db->where('is_deleted', 0);
        if (!empty($status)) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('id', 'desc');
        if (!empty($limit)) {
            $this->db->limit((int) $limit);
        }
        return $this->db->get($this->table)->result();
    }

    public function get_by_id($id) {
        $this->db->where('id', $id);
        $this->db->where('is_deleted', 0);
        $row = $this->db->get($this->table)->row();
        if ($row) {
            $row->images = $this->get_images($id);
        }
        return $row;
    }

    public function get_images($vehicle_id) {
        $this->db->where('vehicle_id', $vehicle_id);
        $this->db->order_by('is_primary', 'DESC');
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get('db_vehicle_images')->result();
    }

    public function add_image($vehicle_id, $image_path, $is_primary = 0) {
        if ($is_primary) {
            $this->db->where('vehicle_id', $vehicle_id)->update('db_vehicle_images', ['is_primary' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        }
        $this->db->insert('db_vehicle_images', [
            'vehicle_id' => $vehicle_id,
            'image_path' => $image_path,
            'is_primary' => $is_primary ? 1 : 0,
            'sort_order' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insert_id();
    }

    public function set_primary_image($image_id, $vehicle_id) {
        $this->db->where('vehicle_id', $vehicle_id)->update('db_vehicle_images', ['is_primary' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        $this->db->where('id', $image_id)->update('db_vehicle_images', ['is_primary' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
        $primary = $this->db->where('id', $image_id)->get('db_vehicle_images')->row();
        if ($primary) {
            $this->db->where('id', $vehicle_id)->update($this->table, ['image_path' => $primary->image_path, 'updated_at' => date('Y-m-d H:i:s')]);
        }
        return true;
    }

    public function delete_image($image_id) {
        $img = $this->db->where('id', $image_id)->get('db_vehicle_images')->row();
        if ($img) {
            $this->db->where('id', $image_id)->delete('db_vehicle_images');
            if (!empty($img->image_path) && file_exists(FCPATH . $img->image_path)) {
                @unlink(FCPATH . $img->image_path);
            }
            // Promote next image to primary if deleted was primary
            if ($img->is_primary) {
                $next = $this->db->where('vehicle_id', $img->vehicle_id)->order_by('id', 'ASC')->get('db_vehicle_images')->row();
                if ($next) {
                    $this->set_primary_image($next->id, $next->vehicle_id);
                }
            }
        }
        return true;
    }

    public function save($data, $id = null) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        if (!empty($id)) {
            $this->db->where('id', $id);
            return $this->db->update($this->table, $data);
        }
        $data['created_date'] = date('Y-m-d');
        $data['created_time'] = date('H:i:s');
        return $this->db->insert($this->table, $data) ? $this->db->insert_id() : false;
    }

    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, ['is_deleted' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function count_by_status($store_id = null) {
        if (empty($store_id)) {
            $store_id = get_current_store_id();
        }
        $this->db->select('status, COUNT(*) as total');
        $this->db->where('store_id', $store_id);
        $this->db->where('is_deleted', 0);
        $this->db->group_by('status');
        return $this->db->get($this->table)->result();
    }

    public function update_status($id, $status, $customer = []) {
        $data = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if (!empty($customer['customer_id'])) {
            $data['customer_id'] = $customer['customer_id'];
        }
        if (!empty($customer['customer_name'])) {
            $data['customer_name'] = $customer['customer_name'];
        }
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }
}
