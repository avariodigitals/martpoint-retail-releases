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
        $this->load->model('Vehicle_service_model', 'vehicle_service');
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
        // Echo before exit — a bare load->view() only fills CI_Output, which
        // exit discards, producing a blank 200.
        echo $this->load->view('mp_layout', $d, TRUE);
        exit;
    }

    private function _render($page_title, $view, $data = []) {
        $d = $this->data ?? [];
        $d['page_title'] = $page_title;
        $d = array_merge($d, $data);
        $d['content'] = $this->load->view($view, $d, TRUE);
        $this->load->view('mp_layout', $d);
    }

    /**
     * Tyre shops and auto-parts shops keep this module as a customer-vehicle
     * register (fitments, plates, service history), not a car-for-sale
     * inventory — views use this to hide dealership furniture (price, sell,
     * status). The tyre_ctx view variable name is retained for the views.
     */
    private function _tyre_ctx() {
        return mp_vehicle_register_mode();
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
        $tyre_ctx = $this->_tyre_ctx();
        $this->_render($tyre_ctx ? 'Customer Vehicles' : 'Automobile Dashboard', 'automobile/dashboard.php', [
            'counts' => $counts,
            'recent' => $recent,
            'tyre_ctx' => $tyre_ctx,
        ]);
    }

    public function list() {
        $this->_check_feature();
        $store_id = get_current_store_id();
        $status = $this->input->get('status');
        $vehicles = $this->automobile->get_all($store_id, $status);
        $tyre_ctx = $this->_tyre_ctx();
        $this->_render($tyre_ctx ? 'Customer Vehicles' : 'Vehicle Inventory', 'automobile/list.php', [
            'vehicles' => $vehicles,
            'status'   => $status,
            'tyre_ctx' => $tyre_ctx,
        ]);
    }

    public function add() {
        $this->_check_feature();
        $tyre_ctx = $this->_tyre_ctx();
        $this->_render($tyre_ctx ? 'Add Customer Vehicle' : 'Add Vehicle', 'automobile/form.php', array_merge([
            'vehicle' => null,
            'tyre_ctx' => $tyre_ctx,
        ], $this->_vehicle_master_data()));
    }

    public function edit($id = '') {
        $this->_check_feature();
        $vehicle = $this->automobile->get_by_id($id);
        if (empty($vehicle)) {
            $this->session->set_flashdata('error', 'Vehicle not found.');
            redirect(base_url('automobile/list'));
        }
        $tyre_ctx = $this->_tyre_ctx();
        $this->_render($tyre_ctx ? 'Edit Customer Vehicle' : 'Edit Vehicle', 'automobile/form.php', array_merge([
            'vehicle' => $vehicle,
            'tyre_ctx' => $tyre_ctx,
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

        $tyre_ctx = $this->_tyre_ctx();
        if ($tyre_ctx) {
            // The tyre register hides price/status; keep required columns valid.
            if (!isset($data['status']) || !in_array($data['status'], ['available', 'reserved', 'sold'], true)) {
                $data['status'] = !empty($data['status']) ? $data['status'] : 'available';
            }
            $data['price'] = 0.0;
            $data['cost'] = 0.0;

            // Fit-to-vehicle at the till and the service history both key on
            // customer_id, so link the owner record the register only typed
            // a name for. Exact match, same store; unknown names stay free text.
            $owner_id = (int) $this->input->post('customer_id', TRUE);
            $owner_name = trim((string) ($data['customer_name'] ?? ''));
            if ($owner_id <= 0 && $owner_name !== '') {
                $owner = $this->db->select('id')
                    ->where('store_id', get_current_store_id())
                    ->where('UPPER(customer_name)', strtoupper($owner_name))
                    ->where('status', 1)
                    ->limit(1)->get('db_customers')->row();
                if ($owner) $owner_id = (int) $owner->id;
            }
            if ($owner_id > 0) $data['customer_id'] = $owner_id;
        }

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

    /** Small screen-friendly label for a vehicle row. */
    private function _vehicle_label($v) {
        $name = trim($v->make . ' ' . $v->model);
        if ($name === '') $name = 'Vehicle';
        if (!empty($v->license_plate)) $name .= ' · ' . $v->license_plate;
        if (!empty($v->year)) $name .= ' · ' . $v->year;
        return $name;
    }

    /** JSON: the customer's vehicles for the POS "fit to vehicle" picker. */
    public function customer_vehicles($customer_id = 0) {
        $this->_check_feature();
        $store_id = get_current_store_id();
        $vehicles = $this->automobile->for_customer($store_id, (int) $customer_id);
        $out = [];
        foreach ($vehicles as $v) {
            $out[] = ['id' => (int) $v->id, 'label' => $this->_vehicle_label($v)];
        }
        $this->output
            ->set_content_type('application_json')
            ->set_output(json_encode(['vehicles' => $out]));
    }

    /** Fitment history: every sale recorded against one of the customer's vehicles. */
    public function history($customer_id = 0) {
        $this->_check_feature();
        $store_id = get_current_store_id();
        $customer_id = (int) $customer_id;
        if ($customer_id <= 0) {
            $this->session->set_flashdata('error', 'Customer not found.');
            redirect(base_url('automobile/list'));
        }
        $customer = $this->db->select('id, customer_name')
            ->where('id', $customer_id)
            ->where('store_id', $store_id)
            ->get('db_customers')->row();
        if (empty($customer)) {
            $this->session->set_flashdata('error', 'Customer not found.');
            redirect(base_url('automobile/list'));
        }
        if (!$this->db->field_exists('vehicle_id', 'db_sales')) {
            $this->session->set_flashdata('error', 'Fitment history is not available on this install yet.');
            redirect(base_url('automobile/list'));
        }
        $vehicles = $this->automobile->for_customer($store_id, $customer_id);
        $vehicle_ids = [];
        $labels = [];
        foreach ($vehicles as $v) {
            $vehicle_ids[] = (int) $v->id;
            $labels[(int) $v->id] = $this->_vehicle_label($v);
        }
        $groups = [];
        if (!empty($vehicle_ids)) {
            $sales = $this->db->select('id, sales_code, sales_date, grand_total, vehicle_id, sales_status')
                ->where('store_id', $store_id)
                ->where_in('vehicle_id', $vehicle_ids)
                ->order_by('id', 'desc')
                ->limit(200)
                ->get('db_sales')->result();
            $lines = [];
            if (!empty($sales)) {
                $sale_ids = array_map(function ($s) { return (int) $s->id; }, $sales);
                // sale_ids above are already store-scoped, so the line lookup
                // inherits its tenant guard from them.
                $q = $this->db->select('si.sales_id, si.description, si.sales_qty, si.price_per_unit, i.item_name')
                    ->from('db_salesitems si')
                    ->join('db_items i', 'i.id = si.item_id', 'left')
                    ->where_in('si.sales_id', $sale_ids)
                    ->order_by('si.id', 'asc')
                    ->get();
                foreach ($q->result() as $ln) {
                    $lines[(int) $ln->sales_id][] = $ln;
                }
            }
            foreach ($sales as $s) {
                $sid = (int) $s->id;
                $vid = (int) $s->vehicle_id;
                if (!isset($groups[$vid])) $groups[$vid] = [];
                $groups[$vid][] = ['sale' => $s, 'lines' => $lines[$sid] ?? []];
            }
        }
        $services = $this->vehicle_service->for_vehicles($store_id, $vehicle_ids);
        $rcfg = $this->vehicle_service->reminder_config($store_id);
        $this->_render('Fitting History', 'automobile/history.php', [
            'customer' => $customer,
            'groups' => $groups,
            'labels' => $labels,
            'vehicles' => $vehicles,
            'services' => $services,
            'service_types' => Vehicle_service_model::TYPES,
            'reminders_on' => ($rcfg && (int) $rcfg->enabled === 1),
            'tyre_ctx' => $this->_tyre_ctx(),
        ]);
    }

    /** ── Vehicle service history ────────────────────────────────────────── **/

    /** Add/edit form for a service record on one vehicle. */
    public function service_form($vehicle_id = 0, $record_id = '') {
        $this->_check_feature();
        $store_id = get_current_store_id();
        $vehicle = $this->automobile->get_by_id((int) $vehicle_id);
        if (empty($vehicle) || (int) $vehicle->store_id !== (int) $store_id) {
            $this->session->set_flashdata('error', 'Vehicle not found.');
            redirect(base_url('automobile/list'));
        }
        $record = null;
        if (!empty($record_id)) {
            $record = $this->vehicle_service->get($store_id, $record_id);
            if (empty($record) || (int) $record->vehicle_id !== (int) $vehicle->id) {
                $this->session->set_flashdata('error', 'Service record not found.');
                redirect(base_url('automobile/history/' . (int) $vehicle->customer_id));
            }
        }
        $technicians = $this->db->select('id, first_name, last_name')
            ->where('store_id', $store_id)->where('status', 1)
            ->order_by('first_name', 'ASC')
            ->get('db_users')->result();
        $this->_render($record ? 'Edit Service Record' : 'Record Vehicle Service', 'automobile/service_form.php', [
            'vehicle' => $vehicle,
            'record' => $record,
            'technicians' => $technicians,
            'service_types' => Vehicle_service_model::TYPES,
            'tyre_ctx' => $this->_tyre_ctx(),
        ]);
    }

    public function service_save() {
        $this->_check_feature();
        $store_id = get_current_store_id();
        $vehicle_id = (int) $this->input->post('vehicle_id', TRUE);
        $id = (int) $this->input->post('id', TRUE);
        $vehicle = $this->automobile->get_by_id($vehicle_id);
        if (empty($vehicle) || (int) $vehicle->store_id !== (int) $store_id) {
            $this->session->set_flashdata('error', 'Vehicle not found.');
            redirect(base_url('automobile/list'));
        }
        $existing = $id ? $this->vehicle_service->get($store_id, $id) : null;
        if ($id && empty($existing)) {
            $this->session->set_flashdata('error', 'Service record not found.');
            redirect(base_url('automobile/list'));
        }

        $type_key = (string) $this->input->post('service_type', TRUE);
        if (!isset(Vehicle_service_model::TYPES[$type_key])) $type_key = 'general';
        $service_date = (string) $this->input->post('service_date', TRUE);
        if (!$service_date) $service_date = date('Y-m-d');
        $mileage = $this->input->post('mileage_at_service', TRUE);
        $next_due_date = (string) $this->input->post('next_due_date', TRUE);
        $next_due_km = $this->input->post('next_due_mileage', TRUE);

        $technician_id = (int) $this->input->post('technician_id', TRUE);
        $technician_name = '';
        if ($technician_id > 0) {
            $tech = $this->db->select('id, first_name, last_name')
                ->where('id', $technician_id)->where('store_id', $store_id)->where('status', 1)
                ->get('db_users')->row();
            if ($tech) {
                $technician_name = trim($tech->first_name . ' ' . $tech->last_name);
            } else {
                $technician_id = 0;
            }
        }

        $data = [
            'store_id'          => $store_id,
            'vehicle_id'        => $vehicle_id,
            'customer_id'       => (int) $vehicle->customer_id ?: null,
            'service_type'      => $type_key,
            'service_date'      => $service_date,
            'mileage_at_service'=> ($mileage !== '' && $mileage !== null) ? (int) $mileage : null,
            'technician_id'     => $technician_id ?: null,
            'technician_name'   => $technician_name ?: null,
            'notes'             => $this->input->post('notes', TRUE),
            'next_due_date'     => $next_due_date !== '' ? $next_due_date : null,
            'next_due_mileage'  => ($next_due_km !== '' && $next_due_km !== null) ? (int) $next_due_km : null,
        ];
        // A changed due date re-arms the reminder; the queue key includes the
        // slot too, this just keeps the skip logic cheap and honest.
        $due_changed = !$existing
            || (string) $existing->next_due_date !== (string) $data['next_due_date'];
        if ($due_changed) $data['last_reminder_slot'] = null;

        $saved = $this->vehicle_service->save($data, $id ?: null);
        if ($saved) {
            // The service visit showed the odometer — keep the register current.
            if (!empty($data['mileage_at_service']) && (int) $data['mileage_at_service'] > (int) $vehicle->mileage) {
                $this->automobile->save(['mileage' => (int) $data['mileage_at_service']], $vehicle_id);
            }
            $this->session->set_flashdata('success', 'Service recorded.');
            redirect(base_url('automobile/history/' . (int) $vehicle->customer_id));
        }
        $this->session->set_flashdata('error', 'Could not save the service record.');
        redirect(base_url('automobile/service_form/' . $vehicle_id));
    }

    public function service_delete($record_id = 0) {
        $this->_check_feature();
        $store_id = get_current_store_id();
        $record = $this->vehicle_service->get($store_id, (int) $record_id);
        if (empty($record)) {
            $this->session->set_flashdata('error', 'Service record not found.');
            redirect(base_url('automobile/list'));
        }
        $this->vehicle_service->soft_delete($store_id, $record->id);
        $this->session->set_flashdata('success', 'Service record removed.');
        redirect(base_url('automobile/history/' . (int) $record->customer_id));
    }

    /** ── Service reminders ──────────────────────────────────────────────── **/

    /**
     * Reminders message the store's own customers, so the switch belongs to the
     * merchant. The Business Owner role is seeded with the reminder permission
     * keys, but is neither Admin nor Store Admin — gating on those role names
     * alone would lock the shop owner out of their own messaging settings.
     */
    private function _can_edit_reminders() {
        return is_admin() || is_store_admin() || $this->permissions('debt_reminder_manage');
    }

    /** Store-wide switch for the daily service-due reminder sweep. */
    public function reminders() {
        $this->_check_feature();
        $store_id = get_current_store_id();
        $cfg = $this->vehicle_service->reminder_config($store_id);
        $due_count = 0;
        if ($cfg && (int) $cfg->enabled === 1) {
            $due = $this->vehicle_service->due_for_reminder(max(0, (int) $cfg->days_before), [$store_id], 500);
            $due_count = count($due);
        }
        $this->_render('Service Reminders', 'automobile/reminders.php', [
            'cfg' => $cfg,
            'due_count' => $due_count,
            'can_edit' => $this->_can_edit_reminders(),
            'tyre_ctx' => $this->_tyre_ctx(),
        ]);
    }

    public function reminders_save() {
        $this->_check_feature();
        if (!$this->_can_edit_reminders()) {
            $this->session->set_flashdata('error', 'You do not have permission to change messaging settings. Please ask a store admin.');
            redirect(base_url('automobile/reminders'));
        }
        $store_id = get_current_store_id();
        $days = (int) $this->input->post('days_before', TRUE);
        $data = [
            'enabled'       => $this->input->post('enabled', TRUE) ? 1 : 0,
            'days_before'   => max(0, min(90, $days)),
            'email_enabled' => $this->input->post('email_enabled', TRUE) ? 1 : 0,
            'sms_enabled'   => $this->input->post('sms_enabled', TRUE) ? 1 : 0,
        ];
        if ($data['enabled'] && !$data['email_enabled'] && !$data['sms_enabled']) {
            $this->session->set_flashdata('error', 'Choose at least one way to reach customers — email, SMS, or both.');
            redirect(base_url('automobile/reminders'));
        }
        if (!$data['email_enabled'] && !$data['sms_enabled']) {
            // No channel left on: the switch cannot honestly say "on".
            $data['enabled'] = 0;
        }
        $this->vehicle_service->save_reminder_config($store_id, $data);
        $this->session->set_flashdata('success', $data['enabled']
            ? 'Service reminders are on. Customers due back within ' . $data['days_before'] . ' day(s) will be messaged once a day.'
            : 'Service reminders are off.');
        redirect(base_url('automobile/reminders'));
    }

    /** ── Parts that fit a vehicle ───────────────────────────────────────── **/

    /**
     * "What do we have for this car?" — reads the fitment data recorded on the
     * parts themselves. Opened from a customer's vehicle, or typed at the
     * counter with make, model and year.
     */
    public function parts($vehicle_id = 0) {
        $this->_check_feature();
        $store_id = get_current_store_id();
        $vehicle = null;
        $vehicle_id = (int) $vehicle_id;
        if ($vehicle_id > 0) {
            $vehicle = $this->automobile->get_by_id($vehicle_id);
            if (empty($vehicle) || (int) $vehicle->store_id !== (int) $store_id) {
                $this->session->set_flashdata('error', 'Vehicle not found.');
                redirect(base_url('automobile/list'));
            }
        }
        $ctx = [
            'make'   => $vehicle ? (string) $vehicle->make : trim((string) $this->input->get('make', TRUE)),
            'model'  => $vehicle ? (string) $vehicle->model : trim((string) $this->input->get('model', TRUE)),
            'year'   => $vehicle ? $vehicle->year : trim((string) $this->input->get('year', TRUE)),
            // A workshop's engine description rarely matches ours verbatim, so the
            // engine only filters when someone types it here deliberately.
            'engine' => trim((string) $this->input->get('engine', TRUE)),
        ];
        $items = [];
        $fitments = [];
        $stock = [];
        if ($ctx['make'] !== '') {
            $this->load->model('item_vehicle_compat_model', 'compat');
            $items = $this->compat->items_for_vehicle($store_id, $ctx);
            $ids = array_map(function ($i) { return (int) $i->id; }, $items);
            if (!empty($ids)) {
                $fitments = $this->compat->fitments_for_items($store_id, $ids, $ctx);
                $rows = $this->db->select('item_id, SUM(available_qty) as qty')
                    ->where('store_id', $store_id)->where_in('item_id', $ids)
                    ->group_by('item_id')->get('db_warehouseitems')->result();
                foreach ($rows as $row) {
                    $stock[(int) $row->item_id] = (float) $row->qty;
                }
            }
        }
        $this->_render('Parts For Vehicle', 'automobile/parts.php', [
            'vehicle'  => $vehicle,
            'ctx'      => $ctx,
            'items'    => $items,
            'fitments' => $fitments,
            'stock'    => $stock,
            'tyre_ctx' => $this->_tyre_ctx(),
        ]);
    }
}
