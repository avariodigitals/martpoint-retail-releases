<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vehicle_data extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load_global();
        $this->load->model('automobile_model', 'automobile');
    }

    private function _require_admin() {
        if (!is_admin() && !is_store_admin()) {
            $this->show_access_denied_page();
        }
    }

    public function index() {
        $this->_require_admin();
        $store_id = get_current_store_id();

        $makes = $this->db->where_in('store_id', [0, $store_id])->where('status', 1)->order_by('name', 'ASC')->get('db_vehicle_makes')->result();
        $models = $this->db->select('m.*, mk.name as make_name')->from('db_vehicle_models m')->join('db_vehicle_makes mk', 'mk.id = m.make_id')->where_in('m.store_id', [0, $store_id])->where('m.status', 1)->order_by('mk.name, m.name')->get()->result();
        $attributes = $this->db->where_in('store_id', [0, $store_id])->where('status', 1)->order_by('attribute_type, sort_order, attribute_value')->get('db_vehicle_attribute_options')->result();

        $editType = $this->input->get('edit');
        $editId = (int) $this->input->get('id');
        $edit = null;
        if ($editId && in_array($editType, ['make','model','attribute'])) {
            $table = $editType === 'attribute' ? 'db_vehicle_attribute_options' : ($editType === 'model' ? 'db_vehicle_models' : 'db_vehicle_makes');
            $edit = $this->db->where('id', $editId)->get($table)->row();
            if ($edit) { $edit->type = $editType; }
        }

        $data = $this->data;
        $data['page_title'] = 'Vehicle Master Data';
        $data['makes'] = $makes;
        $data['models'] = $models;
        $data['attributes'] = $attributes;
        $data['attribute_types'] = ['body_type','fuel_type','transmission','drivetrain','condition','color'];
        $data['edit'] = $edit;
        $data['content'] = $this->load->view('vehicle_data/index', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    public function save($type = '') {
        $this->_require_admin();
        $store_id = get_current_store_id();
        $id = (int) $this->input->post('id');
        $name = trim($this->input->post('name', TRUE));
        $now = date('Y-m-d H:i:s');

        if ($type === 'make') {
            if (empty($name)) { $this->session->set_flashdata('error', 'Make name is required.'); redirect('vehicle_data'); }
            $record = ['store_id' => $store_id, 'name' => $name, 'updated_at' => $now];
            if ($id) {
                $this->db->where('id', $id)->update('db_vehicle_makes', $record);
            } else {
                $record['created_at'] = $now;
                $record['status'] = 1;
                $this->db->insert('db_vehicle_makes', $record);
            }
            $this->session->set_flashdata('success', 'Make saved.');
        } elseif ($type === 'model') {
            $make_id = (int) $this->input->post('make_id');
            if (empty($name) || $make_id <= 0) { $this->session->set_flashdata('error', 'Model name and make are required.'); redirect('vehicle_data'); }
            $record = ['store_id' => $store_id, 'make_id' => $make_id, 'name' => $name, 'updated_at' => $now];
            if ($id) {
                $this->db->where('id', $id)->update('db_vehicle_models', $record);
            } else {
                $record['created_at'] = $now;
                $record['status'] = 1;
                $this->db->insert('db_vehicle_models', $record);
            }
            $this->session->set_flashdata('success', 'Model saved.');
        } elseif ($type === 'attribute') {
            $attr_type = trim($this->input->post('attribute_type', TRUE));
            $value = trim($this->input->post('attribute_value', TRUE));
            $sort = (int) $this->input->post('sort_order');
            if (empty($attr_type) || empty($value)) { $this->session->set_flashdata('error', 'Attribute type and value are required.'); redirect('vehicle_data'); }
            $record = ['store_id' => $store_id, 'attribute_type' => $attr_type, 'attribute_value' => $value, 'sort_order' => $sort, 'updated_at' => $now];
            if ($id) {
                $this->db->where('id', $id)->update('db_vehicle_attribute_options', $record);
            } else {
                $record['created_at'] = $now;
                $record['status'] = 1;
                $this->db->insert('db_vehicle_attribute_options', $record);
            }
            $this->session->set_flashdata('success', 'Attribute option saved.');
        }

        redirect('vehicle_data');
    }

    public function delete($type = '', $id = 0) {
        $this->_require_admin();
        $id = (int) $id;
        if ($id <= 0) { redirect('vehicle_data'); }

        if ($type === 'make') {
            $this->db->where('make_id', $id)->delete('db_vehicle_models');
            $this->db->where('id', $id)->delete('db_vehicle_makes');
            $this->session->set_flashdata('success', 'Make and its models deleted.');
        } elseif ($type === 'model') {
            $this->db->where('id', $id)->delete('db_vehicle_models');
            $this->session->set_flashdata('success', 'Model deleted.');
        } elseif ($type === 'attribute') {
            $this->db->where('id', $id)->delete('db_vehicle_attribute_options');
            $this->session->set_flashdata('success', 'Attribute option deleted.');
        }

        redirect('vehicle_data');
    }

    public function models_json() {
        $make_id = (int) $this->input->get('make_id');
        $store_id = get_current_store_id();
        $models = [];
        if ($make_id > 0) {
            $models = $this->db->where('make_id', $make_id)->where_in('store_id', [0, $store_id])->where('status', 1)->order_by('name', 'ASC')->get('db_vehicle_models')->result_array();
        }
        echo json_encode(['status' => 'success', 'models' => $models]);
    }
}
