<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Perfume Controller — the Perfume Lab.
 * Blending pipeline overview, maceration tracker, and the wastage ledger.
 * Requires the perfumery_workflow feature (enabled by the Perfume Shop preset).
 */
class Perfume extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load_info();
        $this->load->model('perfume_model', 'perfume');
        $this->load->model('production_batches_model', 'pb');
    }

    private function _check_feature($flag = 'perfumery_workflow') {
        if (!mp_feature_enabled($flag)) {
            set_status_header(403);
            $d = $this->data ?? [];
            $d['page_title']      = mp_feature_label($flag);
            $d['feature_label']   = $d['page_title'];
            $d['feature_key']     = $flag;
            $d['description']     = '';
            $d['enable_url']      = base_url('business_profile');
            $d['back_url']        = base_url('dashboard');
            $d['content']         = $this->load->view('operations/feature_not_activated.php', $d, TRUE);
            $this->load->view('mp_layout', $d);
            exit;
        }
    }

    private function _render($page_title, $view, $data = []) {
        $d = $this->data ?? [];
        $d['page_title'] = $page_title;
        $d = array_merge($d, $data);
        $d['content'] = $this->load->view($view, $d, TRUE);
        $this->load->view('mp_layout', $d);
    }

    /**
     * Lab dashboard — pipeline KPIs, maceration watch, wastage snapshot.
     */
    public function index() {
        $this->_check_feature();
        $store_id = get_current_store_id();
        $this->_render('Perfume Lab', 'perfume/dashboard.php', [
            'stats'      => $this->perfume->get_lab_stats($store_id),
            'macerating' => $this->perfume->get_macerating($store_id),
            'pipeline'   => $this->perfume->get_pipeline($store_id),
            'wastage'    => $this->perfume->get_wastage($store_id, 8),
            'waste_summary' => $this->perfume->get_wastage_summary($store_id),
        ]);
    }

    /**
     * Maceration tracker — every batch resting in the cellar, when it
     * started, when it is ready, and how far through the wait it is.
     */
    public function maceration() {
        $this->_check_feature();
        $store_id = get_current_store_id();
        $this->_render('Maceration Tracker', 'perfume/maceration.php', [
            'macerating' => $this->perfume->get_macerating($store_id),
            'pipeline'   => $this->perfume->get_pipeline($store_id, ['planned','sourcing','blending']),
            'finishing'  => $this->perfume->get_pipeline($store_id, ['filtering','bottling','ready']),
        ]);
    }

    /**
     * Wastage ledger — log a loss (also posts the stock adjustment) and
     * analyse where material is being lost and what it costs.
     */
    public function wastage() {
        $this->_check_feature();
        $store_id = get_current_store_id();
        $message = '';

        if ($this->input->post('item_id')) {
            $saved = $this->perfume->log_wastage([
                'item_id'   => (int)$this->input->post('item_id', TRUE),
                'batch_id'  => (int)$this->input->post('batch_id', TRUE) ?: null,
                'stage'     => $this->input->post('stage', TRUE) ?: 'other',
                'qty'       => (float)$this->input->post('qty', TRUE),
                'unit_name' => $this->input->post('unit_name', TRUE),
                'unit_cost' => $this->input->post('unit_cost', TRUE),
                'reason'    => $this->input->post('reason', TRUE),
                'created_by'=> $this->session->userdata('username') ?: 'System',
            ]);
            $message = ($saved !== false)
                ? 'Loss recorded and stock adjusted.'
                : 'Could not record the loss — please check the item and quantity.';
        }

        $this->_render('Wastage & Losses', 'perfume/wastage.php', [
            'message'    => $message,
            'items'      => $this->perfume->get_losable_items($store_id),
            'batches'    => $this->perfume->get_open_batches($store_id),
            'stages'     => Perfume_model::wastage_stages(),
            'wastage'    => $this->perfume->get_wastage($store_id, 60),
            'summary'    => $this->perfume->get_wastage_summary($store_id),
            'variance'   => $this->perfume->get_production_variance($store_id),
        ]);
    }

    /**
     * Move a batch along the blending pipeline.
     * Entering 'macerating' stamps maceration_started automatically (model).
     * Entering 'completed' consumes raw materials via the production engine.
     */
    public function batch_status() {
        $this->_check_feature();
        $id     = (int)$this->input->post('id', TRUE);
        $status = $this->input->post('status', TRUE);
        $batch  = $id ? $this->pb->get($id) : null;
        $allowed = Production_batches_model::get_statuses('perfume_shop', $batch ? $batch->batch_type : null);
        if (!$id || !$batch || !in_array($status, $allowed, true)) {
            echo json_encode(['success' => false, 'message' => 'Missing or invalid data']);
            return;
        }
        if ($status === 'completed') {
            $shortages = $this->pb->validate_stock_for_batch($id);
            if (!empty($shortages)) {
                $msg = 'Cannot complete — not enough stock: ';
                foreach ($shortages as $s) {
                    $msg .= $s['item_name'] . ' (need ' . $s['needed'] . ', have ' . $s['available'] . '); ';
                }
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }
            if (!$this->pb->complete_batch($id)) {
                echo json_encode(['success' => false, 'message' => 'Stock update failed. Please check the error log or contact support.']);
                return;
            }
        }
        $this->pb->save(['status' => $status], $id);
        echo json_encode([
            'success'   => true,
            'message'   => 'Batch moved to ' . Production_batches_model::status_label($status),
            'csrf_hash' => $this->security->get_csrf_hash(),
        ]);
    }
}
