<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pharmacy_workspace extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load_global();
        $industry = mp_get_store_profile()['industry_type'] ?? '';
        if (!in_array($industry, ['pharmacy', 'medical_store', 'healthcare'], true)) {
            $this->show_access_denied_page('This workspace is for pharmacy businesses.'); exit;
        }
        $this->load->helper('pharmacy_workspace');
    }

    private function render($title, $view, array $extra = []) {
        pharmacy_workspace_render(array_merge($this->data, ['page_title' => $title], $extra), 'pharmacy/'.$view);
    }

    public function more() {
        $this->permission_check('dashboard_view');
        $this->render('More tools', 'more', ['tool_groups' => pharmacy_workspace_tools()]);
    }

    public function locations() {
        $this->permission_check('store_view');
        $this->load->model('storefront_model');
        $this->render('Locations & fulfilment', 'locations', ['locations' => $this->storefront_model->getOrderableOutlets(get_current_store_id())]);
    }
}
