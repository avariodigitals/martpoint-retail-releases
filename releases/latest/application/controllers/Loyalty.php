<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Loyalty extends MY_Controller {

    public function __construct(){
        parent::__construct();
        $this->load_global();
        if(!mp_feature_enabled('loyalty')){
            $this->show_access_denied_page();
            return;
        }
        $this->load->model('loyalty_model','loyalty');
        $this->load->model('customers_model','customers');
    }

    public function index()
    {
        $this->permission_check('loyalty_view');
        $data=$this->data;
        $data['page_title']='Loyalty & Rewards';
        $data['settings']=$this->loyalty->get_settings();
        $data['tiers']=$this->loyalty->get_tiers();
        $data['stats']=$this->loyalty->get_dashboard_stats();
        $this->load->view('loyalty/dashboard',$data);
    }

    public function settings()
    {
        $this->permission_check('loyalty_edit');
        $data=$this->data;
        $data['page_title']='Loyalty Settings';
        $data['settings']=$this->loyalty->get_settings();
        $data['tiers']=$this->loyalty->get_tiers();
        $this->load->view('loyalty/settings',$data);
    }

    public function save_settings()
    {
        $this->permission_check_with_msg('loyalty_edit');
        echo $this->loyalty->save_settings();
    }

    public function tiers()
    {
        $this->permission_check('loyalty_view');
        $data=$this->data;
        $data['page_title']='Customer Tiers';
        $data['tiers']=$this->loyalty->get_tiers();
        $this->load->view('loyalty/tiers',$data);
    }

    public function save_tier()
    {
        $this->permission_check_with_msg('loyalty_edit');
        echo $this->loyalty->save_tier();
    }

    public function delete_tier($id)
    {
        $this->permission_check_with_msg('loyalty_delete');
        echo $this->loyalty->delete_tier($id);
    }

    public function bonus_rules()
    {
        $this->permission_check('loyalty_view');
        $data=$this->data;
        $data['page_title']='Bonus Rules';
        $data['rules']=$this->loyalty->get_bonus_rules();
        $this->load->view('loyalty/bonus_rules',$data);
    }

    public function save_bonus_rule()
    {
        $this->permission_check_with_msg('loyalty_edit');
        echo $this->loyalty->save_bonus_rule();
    }

    public function delete_bonus_rule($id)
    {
        $this->permission_check_with_msg('loyalty_delete');
        echo $this->loyalty->delete_bonus_rule($id);
    }

    public function product_points()
    {
        $this->permission_check('loyalty_view');
        $data=$this->data;
        $data['page_title']='Product Points';
        $this->load->view('loyalty/product_points',$data);
    }

    public function ajax_product_points_list()
    {
        $list = $this->loyalty->get_product_points_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $row) {
            $no++;
            $data[] = array(
                $no,
                $row->item_name,
                $row->bonus_points,
                $row->bonus_type,
                '<button class="btn btn-sm btn-primary" onclick="edit_product_points('.$row->id.')">Edit</button>'
            );
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->loyalty->count_product_points_all(),
            "recordsFiltered" => $this->loyalty->count_product_points_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function save_product_points()
    {
        $this->permission_check_with_msg('loyalty_edit');
        echo $this->loyalty->save_product_points();
    }

    public function points_history($customer_id='')
    {
        $this->permission_check('loyalty_view');
        $data=$this->data;
        $data['page_title']='Points History';
        $data['customer_id']=$customer_id;
        $this->load->view('loyalty/points_history',$data);
    }

    public function ajax_points_history()
    {
        $list = $this->loyalty->get_points_history_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $row) {
            $no++;
            $data[] = array(
                $no,
                $row->customer_name,
                $row->transaction_type,
                $row->points,
                $row->points_balance,
                $row->description,
                show_date($row->created_date)
            );
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->loyalty->count_points_history_all(),
            "recordsFiltered" => $this->loyalty->count_points_history_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function adjust_points()
    {
        $this->permission_check_with_msg('loyalty_edit');
        echo $this->loyalty->adjust_points();
    }

    public function get_customer_loyalty_json()
    {
        $customer_id = $this->input->post('customer_id', TRUE);
        if(empty($customer_id)){ echo json_encode(array()); return; }
        $data = $this->loyalty->get_customer_loyalty_summary($customer_id);
        echo json_encode($data ?: array());
    }

    public function redeem_points_ajax()
    {
        $this->permission_check_with_msg('loyalty_edit');
        echo $this->loyalty->redeem_points_ajax();
    }

    public function referral_program()
    {
        $this->permission_check('loyalty_view');
        $data=$this->data;
        $data['page_title']='Referral Program';
        $data['settings']=$this->loyalty->get_referral_settings();
        $this->load->view('loyalty/referral_program',$data);
    }

    public function save_referral_settings()
    {
        $this->permission_check_with_msg('loyalty_edit');
        echo $this->loyalty->save_referral_settings();
    }
}
