<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Memberships extends MY_Controller {

    public function __construct(){
        parent::__construct();
        $this->load_global();
        if(!mp_feature_enabled('memberships')){
            show_404();
            return;
        }
        $this->load->model('creator_membership_model', 'membership_model');
    }

    public function index(){
        $storeId = get_current_store_id();
        $today = date('Y-m-d');
        $data = $this->data;
        $data['page_title'] = 'Memberships';
        $memberships = $this->membership_model->getMemberships($storeId, 100);
        $hasSubs = $this->db->table_exists('db_membership_subscriptions');
        foreach($memberships as $m){
            $m->item = $this->db->where('id', $m->item_id)->get('db_items')->row();
            $m->active_count = $hasSubs ? $this->db->where('membership_id', $m->id)->where('status', 'active')->where('end_date >=', $today)->count_all_results('db_membership_subscriptions') : 0;
        }
        $data['memberships'] = $memberships;
        $data['content'] = $this->load->view('memberships/index', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    public function add(){
        $storeId = get_current_store_id();
        $data = $this->data;
        $data['page_title'] = 'Add Membership';
        $data['membership'] = null;
        $data['preselect_item'] = (int)$this->input->get('item_id');
        $data['items'] = $this->db->where('store_id', $storeId)->where('product_type', 'membership')->where('status', 1)->get('db_items')->result();
        $data['content'] = $this->load->view('memberships/form', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    public function edit($id = ''){
        $storeId = get_current_store_id();
        $id = (int)$id;
        $membership = $this->membership_model->get($id, $storeId);
        if(!$membership){
            $this->session->set_flashdata('error', 'Membership not found.');
            redirect('memberships');
            return;
        }

        $data = $this->data;
        $data['page_title'] = 'Edit Membership';
        $data['membership'] = $membership;
        $data['preselect_item'] = 0;
        $data['items'] = $this->db->where('store_id', $storeId)->where('product_type', 'membership')->where('status', 1)->get('db_items')->result();
        $data['content'] = $this->load->view('memberships/form', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    public function save($id = ''){
        $storeId = get_current_store_id();
        $id = (int)$id;

        $itemId = (int)$this->input->post('item_id', TRUE);
        $name = trim($this->input->post('membership_name', TRUE));
        $description = trim($this->input->post('description', TRUE));
        $interval = $this->input->post('billing_interval', TRUE);
        $trialDays = (int)$this->input->post('trial_days', TRUE);
        $status = $this->input->post('status') ? 1 : 0;

        if(empty($itemId) || empty($name) || empty($interval)){
            $this->session->set_flashdata('error', 'Item, Name and Billing Interval are required.');
            $redirect = $id ? 'memberships/edit/'.$id : 'memberships/add';
            redirect($redirect);
            return;
        }

        $data = [
            'store_id'         => $storeId,
            'item_id'          => $itemId,
            'membership_name'  => $name,
            'description'      => $description,
            'billing_interval' => $interval,
            'trial_days'       => $trialDays,
            'status'           => $status
        ];

        $this->membership_model->saveMembership($data, $id ?: null);
        $this->session->set_flashdata('success', 'Membership saved successfully.');
        redirect('memberships');
    }

    public function delete($id = ''){
        $storeId = get_current_store_id();
        $id = (int)$id;
        $this->membership_model->saveMembership(['status' => 0], $id);
        $this->session->set_flashdata('success', 'Membership deleted.');
        redirect('memberships');
    }
}
