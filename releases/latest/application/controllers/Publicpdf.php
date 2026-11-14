<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Publicpdf extends MY_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->helper(array('url', 'form', 'custom', 'inventory', 'accounts', 'appinfo', 'advance', 'saas', 'currency', 'foreign_currency', 'invoice_logo'));
    }

    private function set_nocache_headers(){
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");
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
        $this->load_info();

        $data = $this->build_invoice_data('sales', $sales_id, $invoice);
        $data['sales_id'] = $sales_id;

        if ($this->input->get('download')) {
            unset($data['download_url'], $data['og_url'], $data['og_image']);
            $html = $this->load->view('print-sales-invoice-whatsapp', $data, true);
            $this->set_nocache_headers();
            $this->generate_pdf($html, "Sales-invoice-{$sales_id}-" . date('M_d_Y'));
            return;
        }

        $this->set_nocache_headers();
        $this->load->view('print-sales-invoice-whatsapp', $data);
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
        $this->load_info();

        $data = $this->build_invoice_data('purchase', $purchase_id, $invoice);
        $data['purchase_id'] = $purchase_id;

        if ($this->input->get('download')) {
            unset($data['download_url'], $data['og_url'], $data['og_image']);
            $html = $this->load->view('print-purchase-invoice-whatsapp', $data, true);
            $this->set_nocache_headers();
            $this->generate_pdf($html, "Purchase-invoice-{$purchase_id}-" . date('M_d_Y'));
            return;
        }

        $this->set_nocache_headers();
        $this->load->view('print-purchase-invoice-whatsapp', $data);
    }

    private function build_invoice_data($type, $id, $invoice){
        $store_id = !empty($invoice->store_id) ? $invoice->store_id : 0;
        $store = $this->db->where('id', $store_id)->get('db_store')->row();
        $store_name = !empty($store->store_name) ? $store->store_name : 'MartPoint';

        $store_logo_path = mp_get_store_theme_setting($store_id, 'store_logo');
        $store_logo = !empty($store_logo_path) ? $store_logo_path : store_demo_logo();
        $og_image = !empty($store_logo) ? base_url($store_logo) : base_url('uploads/site/icon.webp');

        if($type == 'sales'){
            $contact = $this->db->where('id', $invoice->customer_id)->get('db_customers')->row();
            $contact_name = !empty($contact->customer_name) ? $contact->customer_name : 'Walk-in customer';
            $code = $invoice->sales_code;
            $label = 'Receipt';
            $amount = $invoice->grand_total;
            $status = $invoice->payment_status;
        } else {
            $contact = $this->db->where('id', $invoice->supplier_id)->get('db_suppliers')->row();
            $contact_name = !empty($contact->supplier_name) ? $contact->supplier_name : 'Supplier';
            $code = $invoice->purchase_code;
            $label = 'Invoice';
            $amount = $invoice->grand_total;
            $status = $invoice->payment_status;
        }

        $total = is_numeric($amount) ? store_number_format($amount) : $amount;
        $desc = "{$store_name} {$label} {$code} | {$contact_name} | {$status} | Total: {$total}";

        $page_title = "{$store_name} | {$label} {$code}";
        $base_path = 'publicpdf/' . $type . '/' . $id;
        $base_url = base_url($base_path);

        $query = $this->input->get();
        unset($query['download']);
        $og_url = $base_url . (!empty($query) ? '?' . http_build_query($query) : '');

        $download_query = $this->input->get();
        $download_query['download'] = 1;
        $download_url = $base_url . '?' . http_build_query($download_query);

        return array(
            'page_title' => $page_title,
            'og_site_name' => $store_name,
            'og_title' => $page_title,
            'og_description' => $desc,
            'og_url' => $og_url,
            'og_image' => $og_image,
            'download_url' => $download_url,
            'theme_link' => base_url().'theme/',
            'base_url' => base_url(),
        );
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
