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

    public function configuration() {
        $this->configuration_access();
        $this->configuration_page((string)$this->session->flashdata('pharmacy_mode_message'));
    }

    private function configuration_access() {
        if (!pharmacy_workspace_is_admin()) {
            $this->show_access_denied_page('Critical pharmacy settings are restricted to administrators.'); exit;
        }
        $this->permission_check('business_profile');
    }

    private function configuration_page($message = '', $error = false) {
        $stored = mp_pharmacy_stored_mode();
        $this->render('Pharmacy configuration', 'configuration', [
            'pharmacy_mode' => mp_pharmacy_resolve_mode($stored),
            'legacy_mode' => $stored === 'legacy',
            'mode_ready' => $this->db->field_exists('pharmacy_mode', 'db_store'),
            'mode_message' => $message, 'mode_error' => $error,
        ]);
    }

    public function save_configuration() {
        $this->configuration_access();
        if ($this->input->method(true) !== 'POST') { show_error('Use the configuration form to change pharmacy mode.', 405); return; }
        $this->load->model('pharmacy_mode_model');
        try {
            if (!$this->pharmacy_mode_model->set_mode(get_current_store_id(), $this->input->post('pharmacy_mode'))) {
                throw new RuntimeException('The mode could not be saved. Please try again.');
            }
            $this->session->set_flashdata('pharmacy_mode_message', 'Pharmacy mode saved. Your inventory, patients and sales have been preserved.');
            $screen = $this->input->get('screen');
            redirect('pharmacy_workspace/configuration'.(in_array($screen, ['mobile', 'desktop'], true) ? '?screen='.$screen : ''));
        } catch (InvalidArgumentException $e) {
            $this->output->set_status_header(422);
            $this->configuration_page($e->getMessage(), true);
        } catch (RuntimeException $e) {
            $this->output->set_status_header(503);
            $this->configuration_page($e->getMessage(), true);
        }
    }

    public function locations() {
        $this->permission_check('store_view');
        $this->load->model('storefront_model');
        $this->render('Locations & fulfilment', 'locations', ['locations' => $this->storefront_model->getOrderableOutlets(get_current_store_id())]);
    }
}
