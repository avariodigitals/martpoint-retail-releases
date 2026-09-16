<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Butchery Controller — Carcass receiving, cutting worksheet, share management
 * Requires meat_butchery_workflow license flag.
 */
class Butchery extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load_info();
        $this->load->model('Items_model', 'items');
        $this->load->model('butchery_model', 'butchery');
    }

    private function _check_feature($flag = 'meat_butchery_workflow') {
        if (!mp_feature_enabled($flag)) {
            $this->_render_feature_not_activated($flag);
        }
    }

    private function _render_feature_not_activated($flag, $description = '') {
        set_status_header(403);
        $d = $this->data ?? [];
        $d['page_title']      = mp_feature_label($flag);
        $d['feature_label']   = $d['page_title'];
        $d['feature_key']     = $flag;
        $d['description']     = $description;
        $d['enable_url']      = base_url('business_profile');
        $d['back_url']        = base_url('dashboard');
        $d['content']         = $this->load->view('operations/feature_not_activated.php', $d, TRUE);
        $this->load->view('mp_layout', $d);
        exit;
    }

    private function _render($page_title, $view, $data = []) {
        $d = $this->data ?? [];
        $d['page_title'] = $page_title;
        $d = array_merge($d, $data);
        $d['content'] = $this->load->view($view, $d, TRUE);
        $this->load->view('mp_layout', $d);
    }

    public function index() {
        $this->_check_feature();
        $this->_render('Butchery Dashboard', 'butchery/dashboard.php', [
            'received_count'  => count($this->butchery->get_received_carcasses()),
            'completed_count' => count($this->butchery->get_received_carcasses(null, 'completed')),
            'shares_count'    => count($this->butchery->get_shares()),
        ]);
    }

    public function receive() {
        $this->_check_feature();

        $message = '';
        if ($this->input->post('carcass_name')) {
            $data = [
                'supplier_name'       => $this->input->post('supplier_name'),
                'carcass_name'        => $this->input->post('carcass_name'),
                'lot_number'          => $this->input->post('lot_number'),
                'receiving_weight'    => (float) $this->input->post('receiving_weight'),
                'slaughter_date'      => $this->input->post('slaughter_date'),
                'expected_yield_pct'  => (float) $this->input->post('expected_yield_pct'),
                'freezer_location_id' => (int) $this->input->post('freezer_location_id'),
                'carcass_template_id' => (int) $this->input->post('carcass_template_id'),
                'notes'               => $this->input->post('notes'),
                'created_by'          => get_current_user_id(),
            ];
            $this->butchery->save_received_carcass($data);
            $message = 'Carcass received successfully.';
        }

        $this->_render('Receive Carcass', 'butchery/receive.php', [
            'carcasses' => $this->butchery->get_received_carcasses(),
            'templates' => $this->butchery->get_templates(),
            'freezers'  => $this->butchery->get_freezer_locations(),
            'message'   => $message,
        ]);
    }

    public function cut($received_carcass_id = '') {
        $this->_check_feature();

        $carcass = $this->butchery->get_received_carcass((int) $received_carcass_id);
        if (!$carcass) {
            show_404();
            return;
        }

        $message = '';
        if ($this->input->post('cuts')) {
            $cuts = $this->input->post('cuts') ?? [];
            $created = $this->butchery->complete_cut_to_stock((int) $received_carcass_id, $cuts);
            $message = ($created !== false) ? 'Cutting saved and cuts added to stock.' : 'Could not save cutting worksheet.';
        }

        $template_cuts = [];
        if ($carcass->carcass_template_id) {
            $template_cuts = $this->butchery->get_template_cuts($carcass->carcass_template_id);
        }

        $this->_render('Carcass Cutting Worksheet', 'butchery/cut.php', [
            'carcass'       => $carcass,
            'template_cuts' => $template_cuts,
            'records'       => $this->butchery->get_cut_records((int) $received_carcass_id),
            'message'       => $message,
        ]);
    }

    public function shares() {
        $this->_check_feature();

        $message = '';
        if ($this->input->post('customer_name')) {
            $data = [
                'carcass_item_id'  => (int) $this->input->post('carcass_item_id'),
                'customer_name'    => $this->input->post('customer_name'),
                'customer_id'      => (int) $this->input->post('customer_id') ?: null,
                'share_fraction'   => $this->input->post('share_fraction') ?: '1/4',
                'reserved_weight'  => (float) $this->input->post('reserved_weight'),
                'reserved_amount'  => (float) $this->input->post('reserved_amount'),
                'deposit_amount'   => (float) $this->input->post('deposit_amount'),
                'status'           => $this->input->post('status') ?: 'reserved',
                'created_by'       => get_current_user_id(),
            ];
            $this->butchery->save_share($data);
            $message = 'Share reserved successfully.';
        }

        $this->_render('Carcass Shares', 'butchery/shares.php', [
            'shares'    => $this->butchery->get_shares(),
            'carcasses' => $this->butchery->get_received_carcasses(null, 'received'),
            'message'   => $message,
        ]);
    }

    public function temperature() {
        $this->_check_feature();

        $message = '';
        if ($this->input->post('freezer_location_id')) {
            $freezer = $this->butchery->get_freezer_location((int) $this->input->post('freezer_location_id'));
            $temp = (float) $this->input->post('temperature_c');
            $status = 'normal';
            if ($freezer) {
                if ($freezer->temp_min !== null && $temp < (float) $freezer->temp_min) $status = 'warning';
                if ($freezer->temp_max !== null && $temp > (float) $freezer->temp_max) $status = 'warning';
            }
            $this->butchery->save_temperature_log([
                'freezer_location_id' => (int) $this->input->post('freezer_location_id'),
                'temperature_c'       => $temp,
                'recorded_by'         => get_current_user_id(),
                'notes'               => $this->input->post('notes'),
                'status'              => $status,
            ]);
            $message = 'Temperature recorded.';
        }

        $freezers = $this->butchery->get_freezer_locations();
        $logs = $this->butchery->get_recent_temperature_logs(50);

        $this->_render('Cold Chain Temperature Log', 'butchery/temperature.php', [
            'freezers' => $freezers,
            'logs'     => $logs,
            'message'  => $message,
        ]);
    }

    public function freezers() {
        $this->_check_feature();

        $message = '';
        if ($this->input->post('location_name')) {
            $data = [
                'location_name'   => $this->input->post('location_name'),
                'location_code'   => $this->input->post('location_code'),
                'temp_min'        => $this->input->post('temp_min') !== '' ? (float) $this->input->post('temp_min') : null,
                'temp_max'        => $this->input->post('temp_max') !== '' ? (float) $this->input->post('temp_max') : null,
                'capacity_volume' => (float) $this->input->post('capacity_volume'),
                'status'          => 1,
            ];
            $this->butchery->save_freezer_location($data);
            $message = 'Freezer / cold room saved.';
        }

        $this->_render('Freezer Locations', 'butchery/freezers.php', [
            'freezers' => $this->butchery->get_freezer_locations(),
            'message'  => $message,
        ]);
    }
}
