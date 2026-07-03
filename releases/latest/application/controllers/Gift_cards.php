<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gift_cards extends MY_Controller {

    public function __construct(){
        parent::__construct();
        $this->load_global();
        if(!mp_feature_enabled('gift_cards')){
            $this->show_access_denied_page();
            return;
        }
        $this->load->model('gift_cards_model','gift_cards');
    }

    public function index()
    {
        $this->permission_check('gift_cards_view');
        $data=$this->data;
        $data['page_title']='Gift Cards';
        $this->load->view('gift_cards/list',$data);
    }

    public function add()
    {
        $this->permission_check('gift_cards_add');
        $data=$this->data;
        $data['page_title']='Add Gift Card';
        $this->load->view('gift_cards/form',$data);
    }

    public function edit($id)
    {
        $this->permission_check('gift_cards_edit');
        $data=$this->data;
        $data['page_title']='Edit Gift Card';
        $data['card']=$this->gift_cards->get_card($id);
        $this->load->view('gift_cards/form',$data);
    }

    public function save()
    {
        $this->permission_check_with_msg('gift_cards_add');
        echo $this->gift_cards->save_card();
    }

    public function update()
    {
        $this->permission_check_with_msg('gift_cards_edit');
        echo $this->gift_cards->update_card();
    }

    public function ajax_list()
    {
        $list = $this->gift_cards->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $card) {
            $no++;
            $status_label = '';
            if($card->status == 'active') $status_label = '<span class="label label-success">Active</span>';
            elseif($card->status == 'redeemed') $status_label = '<span class="label label-info">Redeemed</span>';
            elseif($card->status == 'expired') $status_label = '<span class="label label-warning">Expired</span>';
            elseif($card->status == 'cancelled') $status_label = '<span class="label label-danger">Cancelled</span>';

            $row = array();
            $row[] = $no;
            $row[] = $card->card_number;
            $row[] = $card->customer_name ?: '-';
            $row[] = store_number_format($card->initial_value);
            $row[] = store_number_format($card->current_balance);
            $row[] = show_date($card->issue_date);
            $row[] = show_date($card->expiry_date) ?: 'Never';
            $row[] = ucfirst($card->card_type);
            $row[] = $status_label;
            $action = '';
            if($this->permissions('gift_cards_view') && $card->status == 'active'){
                $action .= '<a href="'.base_url('gift_cards/print_card/'.$card->id).'" class="btn btn-sm btn-default" target="_blank" title="Print Card"><i class="fa fa-print"></i></a> ';
            }
            if($this->permissions('gift_cards_edit') && $card->status == 'active'){
                $action .= '<a href="'.base_url('gift_cards/edit/'.$card->id).'" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a> ';
            }
            if($this->permissions('gift_cards_delete') && $card->status == 'active'){
                $action .= '<button class="btn btn-sm btn-danger" onclick="cancel_card('.$card->id.')"><i class="fa fa-ban"></i></button>';
            }
            $row[] = $action;
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->gift_cards->count_all(),
            "recordsFiltered" => $this->gift_cards->count_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function print_card($id)
    {
        $this->permission_check('gift_cards_view');
        $data = $this->data;
        $data['page_title'] = 'Print Gift Card';
        $data['card'] = $this->gift_cards->get_card($id);
        if(!$data['card'] || $data['card']->status != 'active'){
            show_error('This card cannot be printed because it is cancelled or expired.', 403, 'Not Printable');
            return;
        }
        $data['customer'] = null;
        if($data['card'] && $data['card']->customer_id){
            $data['customer'] = $this->db->where('id', $data['card']->customer_id)->get('db_customers')->row();
        }
        // Get store settings for card design
        $data['card_color'] = '#fdfbf7';
        $data['brand_name'] = $data['SITE_TITLE'] ?? 'MartPoint';
        $this->load->view('gift_cards/print_card', $data);
    }

    public function cancel_card()
    {
        $this->permission_check_with_msg('gift_cards_delete');
        $id = $this->input->post('id', TRUE);
        echo $this->gift_cards->cancel_card($id);
    }

    public function validate_card_ajax()
    {
        $card_number = $this->input->post('card_number', TRUE);
        $card = $this->gift_cards->get_card_by_number($card_number);
        if($card && $card->status == 'active' && (!$card->expiry_date || $card->expiry_date >= date('Y-m-d'))){
            echo json_encode(array('valid'=>true, 'balance'=>$card->current_balance, 'card_id'=>$card->id));
        } else {
            echo json_encode(array('valid'=>false));
        }
    }

    public function redeem_ajax()
    {
        $this->permission_check_with_msg('gift_cards_edit');
        echo $this->gift_cards->redeem_ajax();
    }
}
