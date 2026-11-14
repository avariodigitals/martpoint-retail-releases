<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_groups extends MY_Controller {
    public function __construct() {
        parent::__construct(); $this->load_global();
        $this->load->model('Customer_pricing_model', 'customer_pricing');
    }

    public function index() {
        $this->permission_check('customers_view');
        $data = $this->data;
        $data['page_title'] = 'Customer Pricing Groups';
        $data['groups'] = $this->customer_pricing->groups(get_current_store_id());
        $data['pricing_installed'] = $this->customer_pricing->installed();
        $data['can_manage'] = $this->permissions('customers_edit');
        if (is_mobile()) $this->load->view('mobile/customer_groups', $data);
        else {
            $data['content'] = $this->load->view('customers/desktop/groups', $data, true);
            $this->load->view('mp_layout', $data);
        }
    }

    public function save() {
        $this->permission_check('customers_edit');
        if ($this->input->method() !== 'post') show_404();
        try {
            $input = (array)$this->input->post(null, true);
            $isUpdate = !empty($input['id']);
            $this->customer_pricing->save_group(get_current_store_id(), $input);
            $this->session->set_flashdata('success', $isUpdate ? 'Customer pricing group updated.' : 'Customer pricing group created.');
        } catch (RuntimeException $e) { $this->session->set_flashdata('failed', $e->getMessage()); }
        redirect('customer_groups');
    }

    /** Read-only quote endpoint is available to sales/quotation staff, scoped to this store. */
    public function pricing() {
        if (!$this->permissions('sales_add') && !$this->permissions('sales_edit') && !$this->permissions('quotation_add') && !$this->permissions('quotation_edit')) {
            $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['error'=>'Pricing access denied.'])); return;
        }
        $store = get_current_store_id(); $customer = (int)$this->input->get('customer_id');
        try {
            // Existing invoices may reference a customer that has since been
            // deactivated. Keep pricing lookup store-scoped, but do not make
            // editing that invoice depend on the customer's active flag.
            if ($customer && !$this->db->where('store_id', $store)->where('id', $customer)->get('db_customers')->row()) throw new RuntimeException('Customer is not available in this store.');
            $lines = json_decode((string)$this->input->get('lines'), true) ?: [];
            if (count($lines) > 200) throw new RuntimeException('Too many pricing lines.');
            $quotes = [];
            foreach ($lines as $key=>$line) $quotes[$key] = $this->customer_pricing->quote($store, $customer,
                (int)($line['id'] ?? 0), $line['price_type'] ?? 'retail', (int)($line['unit_id'] ?? 0), (int)($line['barcode_id'] ?? 0));
            $result = ['policy'=>$this->customer_pricing->policy($store, $customer), 'quotes'=>$quotes];
        } catch (RuntimeException $e) { $result = ['error'=>$e->getMessage()]; }
        $this->output->set_header('Cache-Control: no-store')->set_content_type('application/json')->set_output(json_encode($result));
    }
}
