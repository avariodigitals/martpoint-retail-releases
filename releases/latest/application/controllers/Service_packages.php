<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Service_packages extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load_global();
        $this->load->model('service_package_model');
        if (!$this->permissions('service_packages_view')) {
            $this->show_access_denied_page();
        }
    }

    public function index() {
        $data = $this->data;
        $data['page_title'] = 'Service Packages';
        $this->load->view('service-packages-list', $data);
    }

    public function add() {
        if (!$this->permissions('service_packages_add')) {
            $this->show_access_denied_page();
        }
        $data = $this->data;
        $data['page_title'] = 'New Service Package';
        $data['command'] = 'save';
        $this->load->view('service-packages', $data);
    }

    public function update($id) {
        if (!$this->permissions('service_packages_edit')) {
            $this->show_access_denied_page();
        }
        $data = $this->data;
        $data = $this->service_package_model->get_details($id, $data);
        $data['page_title'] = 'Edit Service Package';
        $data['command'] = 'update';
        $this->load->view('service-packages', $data);
    }

    public function newpackage() {
        $this->form_validation->set_rules('package_name', 'Package Name', 'trim|required');
        $this->form_validation->set_rules('package_price', 'Package Price', 'trim|required|numeric');

        if ($this->form_validation->run() == TRUE) {
            $result = $this->service_package_model->save_and_update();
            echo $result;
        } else {
            echo 'Please fill in all required fields.';
        }
    }

    public function delete_package() {
        $id = $this->input->post('q_id');
        $result = $this->service_package_model->delete_packages($id);
        echo $result;
    }

    public function view($id) {
        $data = $this->data;
        $data = $this->service_package_model->get_details($id, $data);
        $data['package_items'] = $this->service_package_model->get_package_items($id);
        $data['page_title'] = 'View Service Package';
        $this->load->view('service-packages-view', $data);
    }

    // AJAX: Datatable
    public function ajax_list() {
        $list = $this->service_package_model->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $pkg) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $pkg->package_code;
            $row[] = '<b>' . $pkg->package_name . '</b>';
            $row[] = ucfirst($pkg->pricing_model);
            $row[] = $this->data['currency'] . ' ' . store_number_format($pkg->package_price);
            $row[] = ucfirst($pkg->redemption_type);
            $row[] = $pkg->expiry_type == 'none' ? 'No Expiry' : ($pkg->expiry_type == 'days' ? $pkg->expiry_days . ' Days' : show_date($pkg->expiry_date));
            $row[] = ($pkg->status == 1)
                ? '<span class="label label-success" style="cursor:pointer" onclick="update_status(' . $pkg->id . ',0)"> Active </span>'
                : '<span class="label label-danger" style="cursor:pointer" onclick="update_status(' . $pkg->id . ',1)"> Inactive </span>';

            $str = '';
            if ($this->permissions('service_packages_edit')) {
                $str .= '<a class="btn btn-sm btn-primary" href="' . base_url('service_packages/update/' . $pkg->id) . '" title="Update Record"><i class="fa fa-edit"></i></a> ';
            }
            if ($this->permissions('service_packages_view')) {
                $str .= '<a class="btn btn-sm btn-info" href="' . base_url('service_packages/view/' . $pkg->id) . '" title="View Record"><i class="fa fa-eye"></i></a> ';
            }
            if ($this->permissions('service_packages_delete')) {
                $str .= '<a class="btn btn-sm btn-danger" href="#" onclick="delete_package(' . $pkg->id . ')" title="Delete Record"><i class="fa fa-trash"></i></a>';
            }
            $row[] = $str;
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->service_package_model->count_all(),
            "recordsFiltered" => $this->service_package_model->count_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    // AJAX: Update status
    public function update_status() {
        $id = $this->input->post('id');
        $status = $this->input->post('status');
        $result = $this->service_package_model->update_status($id, $status);
        echo $result;
    }

    // AJAX: Get services for package builder
    public function ajax_get_services() {
        $store_id = get_current_store_id();
        $this->db->select('id, item_name, item_code, sales_price, service_bit');
        $this->db->from('db_items');
        $this->db->where('store_id', $store_id);
        $this->db->where('status', 1);
        $this->db->where('service_bit', 1);
        $this->db->where('package_bit', 0);
        $this->db->order_by('item_name', 'asc');
        $query = $this->db->get();
        echo json_encode($query->result());
    }

    // AJAX: Get products for package builder
    public function ajax_get_products() {
        $store_id = get_current_store_id();
        $this->db->select('id, item_name, item_code, sales_price, service_bit');
        $this->db->from('db_items');
        $this->db->where('store_id', $store_id);
        $this->db->where('status', 1);
        $this->db->where('service_bit', 0);
        $this->db->where('package_bit', 0);
        $this->db->order_by('item_name', 'asc');
        $query = $this->db->get();
        echo json_encode($query->result());
    }

    // === CUSTOMER PACKAGE REDEMPTION ===

    // View customer's packages
    public function customer_packages($customer_id) {
        $data = $this->data;
        $data['customer'] = $this->db->where('id', $customer_id)->get('db_customers')->row();
        $data['packages'] = $this->service_package_model->get_customer_packages($customer_id);
        $data['page_title'] = 'Customer Packages';
        $this->load->view('customer-packages', $data);
    }

    // AJAX: Redeem a package item
    public function ajax_redeem() {
        $customer_package_id = $this->input->post('customer_package_id', TRUE);
        $item_id = $this->input->post('item_id', TRUE);
        $qty = $this->input->post('qty', TRUE) ?? 1;
        $notes = $this->input->post('notes', TRUE);
        $service_order_id = $this->input->post('service_order_id', TRUE) ?? null;

        $result = $this->service_package_model->redeem_package_item($customer_package_id, $item_id, $qty, $notes, $service_order_id);
        echo json_encode($result);
    }

    // AJAX: Get package items available for redemption
    public function ajax_get_package_items_for_redeem() {
        $customer_package_id = $this->input->get('customer_package_id');
        $cp = $this->db->where('id', $customer_package_id)->get('db_customer_packages')->row();
        if (!$cp) { echo json_encode([]); return; }

        $this->db->select('spi.*, i.item_name');
        $this->db->from('db_service_package_items spi');
        $this->db->join('db_items i', 'i.id = spi.item_id', 'left');
        $this->db->where('spi.package_id', $cp->package_id);
        $this->db->order_by('spi.sort_order', 'asc');
        $query = $this->db->get();
        echo json_encode($query->result());
    }

    // AJAX: Get redemption history for a customer package
    public function ajax_redemptions() {
        $customer_package_id = $this->input->get('customer_package_id');
        $this->db->select('cpr.*, i.item_name');
        $this->db->from('db_customer_package_redemptions cpr');
        $this->db->join('db_items i', 'i.id = cpr.item_id', 'left');
        $this->db->where('cpr.customer_package_id', $customer_package_id);
        $this->db->order_by('cpr.redeemed_at', 'desc');
        $query = $this->db->get();
        echo json_encode($query->result());
    }
}
