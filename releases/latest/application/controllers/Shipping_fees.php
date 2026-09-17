<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shipping_fees extends MY_Controller {

    public function __construct(){
        parent::__construct();
        $this->load_global();
        if(!mp_feature_enabled('manual_shipping')){
            show_404();
        }
        if(!$this->db->table_exists('db_shipping_fees')){
            show_404();
        }
    }

    public function index(){
        $this->permission_check('sales_view');
        $data = $this->data;
        $data['page_title'] = 'Shipping Fees';
        $store_id = get_current_store_id();
        $data['fees'] = $this->db->where('store_id', $store_id)
            ->order_by('sort_order', 'asc')->order_by('id', 'asc')
            ->get('db_shipping_fees')->result();
        $data['content'] = $this->load->view('shipping_fees', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    /**
     * JSON list of enabled fees — consumed by POS.
     */
    public function fees(){
        header('Content-Type: application/json');
        $store_id = get_current_store_id();
        $rows = $this->db->where('store_id', $store_id)->where('is_enabled', 1)
            ->order_by('sort_order', 'asc')->order_by('id', 'asc')
            ->get('db_shipping_fees')->result();
        $out = [];
        foreach($rows as $r){
            $out[] = [
                'id'       => (int)$r->id,
                'label'    => $r->label,
                'location' => $r->location,
                'fee'      => (float)$r->fee,
            ];
        }
        echo json_encode(['status' => 'success', 'fees' => $out]);
    }

    public function save(){
        header('Content-Type: application/json');
        $this->permission_check('sales_add');
        $store_id = get_current_store_id();
        $id       = (int)$this->input->post('fee_id');
        $label    = trim($this->input->post('label', TRUE));
        $location = trim($this->input->post('location', TRUE));
        $fee      = parse_amount($this->input->post('fee', TRUE));
        $enabled  = $this->input->post('is_enabled') ? 1 : 0;

        if($label === ''){
            echo json_encode(['status' => 'error', 'message' => 'Shipping name is required (e.g. Lekki Delivery)']);
            return;
        }
        if($fee < 0){
            echo json_encode(['status' => 'error', 'message' => 'Fee cannot be negative']);
            return;
        }

        $data = [
            'store_id'   => $store_id,
            'label'      => $label,
            'location'   => $location,
            'fee'        => $fee,
            'is_enabled' => $enabled,
            'sort_order' => (int)$this->input->post('sort_order'),
        ];

        if($id){
            $this->db->where('id', $id)->where('store_id', $store_id)->update('db_shipping_fees', $data);
        } else {
            $this->db->insert('db_shipping_fees', $data);
        }
        echo json_encode(['status' => 'success', 'message' => 'Shipping fee saved']);
    }

    public function delete($id = 0){
        header('Content-Type: application/json');
        $this->permission_check('sales_delete');
        $this->db->where('id', (int)$id)->where('store_id', get_current_store_id())->delete('db_shipping_fees');
        echo json_encode(['status' => 'success', 'message' => 'Shipping fee deleted']);
    }
}
