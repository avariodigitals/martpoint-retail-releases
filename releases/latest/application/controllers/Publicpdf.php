<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Publicpdf extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->helper(array('url', 'form', 'custom', 'inventory', 'accounts', 'appinfo', 'advance', 'saas', 'currency', 'foreign_currency'));
    }

    public function sales($sales_id = null){
        // Check record exists
        $invoice = $this->db->where('id', $sales_id)->get('db_sales')->row();
        if (!$invoice) {
            $this->show_access_error();
            return;
        }

        // Validate security token
        $token = $this->input->get('t');
        $expiry = $this->input->get('e');
        $expected = get_pdf_token('sales', $sales_id, $invoice->sales_code);
        if ($token !== $expected) {
            $this->show_access_error($invoice->store_id);
            return;
        }
        // Enforce token expiry if present
        if (!empty($expiry) && is_numeric($expiry) && $expiry < time()) {
            $this->show_access_error($invoice->store_id);
            return;
        }

        // Set store session if not logged in
        if(!$this->session->userdata('store_id')){
            $store = $this->db->where('id', $invoice->store_id)->get('db_store')->row();
            if($store){
                $this->session->set_userdata('store_id', $store->id);
                $this->session->set_userdata('store_name', $store->store_name);
            }
        }

        $data = array();
        $data['page_title'] = 'Sales Invoice';
        $data['sales_id'] = $sales_id;
        $data['theme_link'] = base_url().'theme/';
        $data['base_url'] = base_url();

        $invoice_format_id = get_invoice_format_id();
        if ($invoice_format_id == 4) {
            $html = $this->load->view('print-sales-invoice-4', $data, true);
        } else {
            $html = $this->load->view('print-sales-invoice-3', $data, true);
        }

        $this->generate_pdf($html, "Sales-invoice-{$sales_id}-" . date('M_d_Y'));
    }

    public function purchase($purchase_id = null){
        // Check record exists
        $invoice = $this->db->where('id', $purchase_id)->get('db_purchase')->row();
        if (!$invoice) {
            $this->show_access_error();
            return;
        }

        // Validate security token
        $token = $this->input->get('t');
        $expiry = $this->input->get('e');
        $expected = get_pdf_token('purchase', $purchase_id, $invoice->purchase_code);
        if ($token !== $expected) {
            $this->show_access_error($invoice->store_id);
            return;
        }
        // Enforce token expiry if present
        if (!empty($expiry) && is_numeric($expiry) && $expiry < time()) {
            $this->show_access_error($invoice->store_id);
            return;
        }

        // Set store session if not logged in
        if(!$this->session->userdata('store_id')){
            $store = $this->db->where('id', $invoice->store_id)->get('db_store')->row();
            if($store){
                $this->session->set_userdata('store_id', $store->id);
                $this->session->set_userdata('store_name', $store->store_name);
            }
        }

        $data = array();
        $data['page_title'] = 'Purchase Invoice';
        $data['purchase_id'] = $purchase_id;
        $data['theme_link'] = base_url().'theme/';
        $data['base_url'] = base_url();

        $html = $this->load->view('print-purchase-invoice-2', $data, true);

        $this->generate_pdf($html, "Purchase-invoice-{$purchase_id}-" . date('M_d_Y'));
    }

    private function generate_pdf($html, $filename){
        require_once(APPPATH . 'libraries/dompdf/autoload.inc.php');

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream($filename, array("Attachment" => 0));
        exit;
    }

    private function show_access_error($store_id = 0){
        $data = array('store_name' => '', 'store_phone' => '');
        if(!empty($store_id)){
            $store = $this->db->where('id', $store_id)->get('db_store')->row();
            if($store){
                $data['store_name'] = $store->store_name;
                $data['store_phone'] = !empty($store->mobile) ? $store->mobile : (!empty($store->phone) ? $store->phone : '');
            }
        }
        $this->load->view('publicpdf-error', $data);
    }
}
