<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_credit extends MY_Controller {

    public function __construct(){
        parent::__construct();
        $this->load_global();
        if(!mp_feature_enabled('store_credit')){
            $this->show_access_denied_page();
            return;
        }
        $this->load->model('store_credit_model','store_credit');
    }

    public function index()
    {
        $this->permission_check('store_credit_view');
        $data=$this->data;
        $data['page_title']='Store Credit';
        $this->load->view('store_credit/list',$data);
    }

    public function add()
    {
        $this->permission_check('store_credit_add');
        $data=$this->data;
        $data['page_title']='Issue Store Credit';
        $this->load->view('store_credit/form',$data);
    }

    public function save()
    {
        $this->permission_check_with_msg('store_credit_add');
        echo $this->store_credit->save_credit();
    }

    public function ajax_list()
    {
        $list = $this->store_credit->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $row) {
            $no++;
            $status_label = '';
            if($row->status == 'active') $status_label = '<span class="label label-success">Active</span>';
            elseif($row->status == 'used') $status_label = '<span class="label label-info">Used</span>';
            elseif($row->status == 'expired') $status_label = '<span class="label label-warning">Expired</span>';
            elseif($row->status == 'cancelled') $status_label = '<span class="label label-danger">Cancelled</span>';

            $row_arr = array();
            $row_arr[] = $no;
            $row_arr[] = $row->credit_code;
            $row_arr[] = $row->customer_name;
            $row_arr[] = store_number_format($row->amount);
            $row_arr[] = store_number_format($row->balance);
            $row_arr[] = ucfirst(str_replace('_',' ',$row->source));
            $row_arr[] = show_date($row->expiry_date) ?: 'Never';
            $row_arr[] = $status_label;
            $action = '';
            if($this->permissions('store_credit_view') && $row->status == 'active'){
                $action .= '<a href="'.base_url('store_credit/print_credit/'.$row->id).'" class="btn btn-sm btn-default" target="_blank" title="Print Card"><i class="fa fa-print"></i></a> ';
            }
            if($this->permissions('store_credit_delete') && $row->status == 'active'){
                $action .= '<button class="btn btn-sm btn-danger" onclick="cancel_credit('.$row->id.')"><i class="fa fa-ban"></i></button>';
            }
            $row_arr[] = $action;
            $data[] = $row_arr;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->store_credit->count_all(),
            "recordsFiltered" => $this->store_credit->count_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function print_credit($id)
    {
        $this->permission_check('store_credit_view');
        $data = $this->data;
        $data['page_title'] = 'Print Store Credit';
        $data['credit'] = $this->db->where('id', $id)->get('db_store_credit')->row();
        if(!$data['credit'] || $data['credit']->status != 'active'){
            show_error('This credit cannot be printed because it is cancelled or expired.', 403, 'Not Printable');
            return;
        }
        $data['customer'] = null;
        if($data['credit'] && $data['credit']->customer_id){
            $data['customer'] = $this->db->where('id', $data['credit']->customer_id)->get('db_customers')->row();
        }
        $data['card_color'] = '#fdfbf7';
        $data['brand_name'] = $data['SITE_TITLE'] ?? 'MartPoint';
        $this->load->view('store_credit/print_credit', $data);
    }

    public function cancel_credit()
    {
        $this->permission_check_with_msg('store_credit_delete');
        $id = $this->input->post('id', TRUE);
        echo $this->store_credit->cancel_credit($id);
    }

    public function get_customer_balance_ajax()
    {
        $customer_id = $this->input->post('customer_id', TRUE);
        if(empty($customer_id)){ echo json_encode(array('balance'=>0)); return; }
        $customer = $this->db->select('store_credit_balance')->where('id', $customer_id)->get('db_customers')->row();
        echo json_encode(array('balance' => $customer ? floatval($customer->store_credit_balance) : 0));
    }

    public function redeem_ajax()
    {
        $this->permission_check_with_msg('store_credit_edit');
        echo $this->store_credit->redeem_ajax();
    }
}
