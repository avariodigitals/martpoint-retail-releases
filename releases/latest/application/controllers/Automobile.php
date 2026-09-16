<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Automobile Controller — Vehicle inventory, reservations, and sales
 * Requires automobile_workflow license flag.
 */
class Automobile extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load_info();
        $this->load->model('Automobile_model', 'automobile');
    }

    private function _check_feature($flag = 'automobile_workflow') {
        if (!mp_feature_enabled($flag)) {
            $this->_render_feature_not_activated($flag);
        }
    }

    private function _render_feature_not_activated($flag, $description = '') {
        set_status_header(403);
        $d = $this->data ?? [];
        $d['page_title']      = mp_feature_label($flag);
        $d['feature_label']   = $d['page_title'];
        $d['feature_key']     = $flag;
        $d['description']     = $description;
        $d['enable_url']      = base_url('business_profile');
        $d['back_url']        = base_url('dashboard');
        $d['content']         = $this->load->view('operations/feature_not_activated.php', $d, TRUE);
        $this->load->view('mp_layout', $d);
        exit;
    }

    private function _render($page_title, $view, $data = []) {
        $d = $this->data ?? [];
        $d['page_title'] = $page_title;
        $d = array_merge($d, $data);
        $d['content'] = $this->load->view($view, $d, TRUE);
        $this->load->view('mp_layout', $d);
    }

    private function _vehicle_master_data($vehicle_id = null) {
        $store_id = get_current_store_id();
        $makes = $this->db->where_in('store_id', [0, $store_id])->where('status', 1)->order_by('name', 'ASC')->get('db_vehicle_makes')->result();
        $models = $this->db->where_in('store_id', [0, $store_id])->where('status', 1)->order_by('name', 'ASC')->get('db_vehicle_models')->result();
        $attrs = $this->db->where_in('store_id', [0, $store_id])->where('status', 1)->order_by('sort_order, attribute_value')->get('db_vehicle_attribute_options')->result();
        $attributes = [];
        foreach ($attrs as $a) {
            if (!isset($attributes[$a->attribute_type])) $attributes[$a->attribute_type] = [];
            $attributes[$a->attribute_type][] = $a;
        }
        $images = [];
        if (!empty($vehicle_id)) {
            $images = $this->automobile->get_images($vehicle_id);
        }
        return ['makes' => $makes, 'models' => $models, 'attributes' => $attributes, 'images' => $images];
    }

    public function index() {
        $this->dashboard();
    }

    public function dashboard() {
        $this->_check_feature();
        $store_id = get_current_store_id();
        $counts = ['available' => 0, 'reserved' => 0, 'sold' => 0];
        foreach ($this->automobile->count_by_status($store_id) as $row) {
            $counts[$row->status] = (int) $row->total;
        }
        $recent = $this->automobile->get_all($store_id, null, 5);
        $this->_render('Automobile Dashboard', 'automobile/dashboard.php', [
            'counts' => $counts,
            'recent' => $recent,
        ]);
    }

    public function list() {
        $this->_check_feature();
        $store_id = get_current_store_id();
        $status = $this->input->get('status');
        $vehicles = $this->automobile->get_all($store_id, $status);
        $this->_render('Vehicle Inventory', 'automobile/list.php', [
            'vehicles' => $vehicles,
            'status'   => $status,
        ]);
    }

    public function add() {
        $this->_check_feature();
        $this->_render('Add Vehicle', 'automobile/form.php', array_merge([
            'vehicle' => null,
        ], $this->_vehicle_master_data()));
    }

    public function edit($id = '') {
        $this->_check_feature();
        $vehicle = $this->automobile->get_by_id($id);
        if (empty($vehicle)) {
            $this->session->set_flashdata('error', 'Vehicle not found.');
            redirect(base_url('automobile/list'));
        }
        $this->_render('Edit Vehicle', 'automobile/form.php', array_merge([
            'vehicle' => $vehicle,
        ], $this->_vehicle_master_data($vehicle->id)));
    }

    public function save() {
        $this->_check_feature();
        $id = $this->input->post('id', TRUE);
        $data = [
            'store_id'          => get_current_store_id(),
            'vehicle_code'      => $this->input->post('vehicle_code', TRUE),
            'make'              => $this->input->post('make', TRUE),
            'model'             => $this->input->post('model', TRUE),
            'year'              => (int) $this->input->post('year', TRUE),
            'color'             => $this->input->post('color', TRUE),
            'mileage'           => (int) $this->input->post('mileage', TRUE),
            'fuel_type'         => $this->input->post('fuel_type', TRUE),
            'transmission'      => $this->input->post('transmission', TRUE),
            'vehicle_condition' => $this->input->post('vehicle_condition', TRUE),
            'body_type'         => $this->input->post('body_type', TRUE),
            'engine_capacity'   => $this->input->post('engine_capacity', TRUE),
            'drivetrain'        => $this->input->post('drivetrain', TRUE),
            'trim_level'        => $this->input->post('trim_level', TRUE),
            'number_of_owners'  => (int) $this->input->post('number_of_owners', TRUE),
            'registration_date' => $this->input->post('registration_date', TRUE),
            'vin'               => $this->input->post('vin', TRUE),
            'license_plate'     => $this->input->post('license_plate', TRUE),
            'description'       => $this->input->post('description', TRUE),
            'price'             => (float) $this->input->post('price', TRUE),
            'cost'              => (float) $this->input->post('cost', TRUE),
            'status'            => $this->input->post('status', TRUE),
            'customer_name'     => $this->input->post('customer_name', TRUE),
            'created_by'        => get_current_user_id(),
        ];

        $existing = !empty($id) ? $this->automobile->get_by_id($id) : null;
        $existing_images = !empty($existing) ? $this->automobile->get_images($id) : [];

        // Handle multiple image uploads
        $data['image_path'] = '';
        $new_image_paths = [];
        if (!empty($_FILES['vehicle_images']['name']) && is_array($_FILES['vehicle_images']['name']) && !empty($_FILES['vehicle_images']['name'][0])) {
            $upload_path = FCPATH . 'uploads/vehicles/';
            if (!is_dir($upload_path)) {
                @mkdir($upload_path, 0755, true);
            }
            $config = [
                'upload_path'   => $upload_path,
                'allowed_types' => 'jpg|jpeg|png|webp',
                'max_size'      => 2048,
                'overwrite'     => false,
            ];
            $this->load->library('upload');
            $file_count = count($_FILES['vehicle_images']['name']);
            for ($i = 0; $i < $file_count; $i++) {
                if (empty($_FILES['vehicle_images']['name'][$i])) continue;
                $_FILES['vimg_single'] = [
                    'name'     => $_FILES['vehicle_images']['name'][$i],
                    'type'     => $_FILES['vehicle_images']['type'][$i],
                    'tmp_name' => $_FILES['vehicle_images']['tmp_name'][$i],
                    'error'    => $_FILES['vehicle_images']['error'][$i],
                    'size'     => $_FILES['vehicle_images']['size'][$i],
                ];
                $config['file_name'] = time() . '_' . $i . '_' . uniqid() . '_' . get_current_store_id();
                $this->upload->initialize($config, true);
                if ($this->upload->do_upload('vimg_single')) {
                    $up = $this->upload->data();
                    $new_image_paths[] = 'uploads/vehicles/' . $up['file_name'];
                } else {
                    $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
                    redirect(base_url('automobile/list'));
                }
            }
        }

        if (!empty($new_image_paths)) {
            if (empty($existing_images)) {
                $data['image_path'] = $new_image_paths[0];
            } elseif (!empty($existing->image_path)) {
                $data['image_path'] = $existing->image_path;
            }
        } elseif (!empty($existing) && !empty($existing->image_path)) {
            $data['image_path'] = $existing->image_path;
        }

        $saved_id = $this->automobile->save($data, $id);
        if ($saved_id) {
            $vehicle_id = !empty($id) ? $id : $saved_id;
            $is_first_primary = empty($existing_images);
            foreach ($new_image_paths as $idx => $path) {
                $this->automobile->add_image($vehicle_id, $path, ($idx === 0 && $is_first_primary) ? 1 : 0);
            }
            $this->session->set_flashdata('success', 'Vehicle saved successfully.');
        } else {
            $this->session->set_flashdata('error', 'Could not save vehicle.');
        }
        redirect(base_url('automobile/list'));
    }

    public function delete($id) {
        $this->_check_feature();
        if ($this->automobile->delete($id)) {
            $this->session->set_flashdata('success', 'Vehicle deleted.');
        } else {
            $this->session->set_flashdata('error', 'Could not delete vehicle.');
        }
        redirect(base_url('automobile/list'));
    }

    public function delete_image($image_id) {
        $this->_check_feature();
        $img = $this->db->where('id', $image_id)->get('db_vehicle_images')->row();
        if (empty($img)) { redirect(base_url('automobile/list')); }
        $this->automobile->delete_image($image_id);
        $this->session->set_flashdata('success', 'Image removed.');
        redirect(base_url('automobile/edit/' . $img->vehicle_id));
    }

    public function set_primary_image($image_id) {
        $this->_check_feature();
        $img = $this->db->where('id', $image_id)->get('db_vehicle_images')->row();
        if (empty($img)) { redirect(base_url('automobile/list')); }
        $this->automobile->set_primary_image($image_id, $img->vehicle_id);
        $this->session->set_flashdata('success', 'Primary image updated.');
        redirect(base_url('automobile/edit/' . $img->vehicle_id));
    }

    public function status($id, $status) {
        $this->_check_feature();
        $customer = [
            'customer_id'   => (int) $this->input->post('customer_id', TRUE),
            'customer_name' => $this->input->post('customer_name', TRUE),
        ];
        if (in_array($status, ['available', 'reserved', 'sold'])) {
            $this->automobile->update_status($id, $status, $customer);
            $this->session->set_flashdata('success', 'Vehicle status updated.');
        } else {
            $this->session->set_flashdata('error', 'Invalid status.');
        }
        redirect(base_url('automobile/list'));
    }

    public function sell($id) {
        $this->_check_feature();
        $vehicle = $this->automobile->get_by_id($id);
        if (empty($vehicle)) {
            $this->session->set_flashdata('error', 'Vehicle not found.');
            redirect(base_url('automobile/list'));
        }
        if ($vehicle->status === 'sold') {
            $this->session->set_flashdata('error', 'This vehicle is already sold.');
            redirect(base_url('automobile/list'));
        }
        $this->_render('Sell Vehicle', 'automobile/sell.php', ['vehicle' => $vehicle]);
    }

    public function save_sale() {
        $this->_check_feature();
        $id = (int) $this->input->post('vehicle_id', TRUE);
        $vehicle = $this->automobile->get_by_id($id);
        if (empty($vehicle)) {
            $this->session->set_flashdata('error', 'Vehicle not found.');
            redirect(base_url('automobile/list'));
        }
        if ($vehicle->status === 'sold') {
            $this->session->set_flashdata('error', 'This vehicle is already sold.');
            redirect(base_url('automobile/list'));
        }

        $data = [
            'status'         => 'sold',
            'customer_name'  => $this->input->post('customer_name', TRUE),
            'customer_id'    => (int) $this->input->post('customer_id', TRUE),
            'sold_date'      => date('Y-m-d H:i:s'),
            'amount_paid'    => (float) $this->input->post('amount_paid', TRUE),
            'payment_method' => $this->input->post('payment_method', TRUE),
            'sold_by'        => get_current_user_id(),
        ];

        $this->automobile->save($data, $id);
        $this->session->set_flashdata('success', 'Vehicle sold. Receipt ready.');
        redirect(base_url('automobile/receipt/' . $id));
    }

    public function receipt($id) {
        $this->_check_feature();
        $vehicle = $this->automobile->get_by_id($id);
        if (empty($vehicle)) {
            $this->session->set_flashdata('error', 'Vehicle not found.');
            redirect(base_url('automobile/list'));
        }
        $d = $this->data ?? [];
        $d['page_title'] = 'Vehicle Receipt';
        $d['vehicle'] = $vehicle;
        $this->load->view('automobile/receipt', $d);
    }
}
