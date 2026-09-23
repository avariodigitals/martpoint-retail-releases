<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * System_updates — Super Admin One-Click Auto-Update Controller
 * All endpoints are AJAX-driven to avoid cPanel execution timeouts.
 */
class System_updates extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load_global();
        if (!is_admin() && !is_store_admin() && $this->session->userdata('role_id') != 1) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            exit;
        }
        $this->load->library('Updater');
    }

    /**
     * Render the Super Admin update panel
     */
    public function index() {
        $data = $this->data;
        $data['page_title'] = 'System Update';
        $data['content'] = $this->load->view('system-updates', $data, TRUE);
        $this->load->view('mp_layout', $data);
    }

    /**
     * AJAX: Check if update is available
     */
    public function check() {
        $result = $this->updater->checkForUpdate();
        echo json_encode($result);
    }

    /**
     * AJAX: Preview what will change
     */
    public function preview() {
        $manifest = $this->updater->fetchManifest();
        if (!$manifest) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot fetch manifest.']);
            return;
        }
        $preview = $this->updater->previewChanges($manifest);
        echo json_encode([
            'status' => 'ok',
            'preview' => $preview,
            'manifest' => $manifest,
        ]);
    }

    /**
     * AJAX: Start or resume one chunk of the update
     * POST params: step (1-8), resume (1 to resume current step)
     *
     * Each call only processes a small batch so it never exceeds cPanel timeout.
     * The frontend keeps calling until the returned `done` is true, then moves on.
     */
    public function run_step() {
        // Allow long-running but never too long — each chunk resets its own timer
        @set_time_limit(0);
        @ini_set('max_execution_time', 0);
        @ignore_user_abort(true);

        // Release session lock so the progress poller can run concurrently
        if (function_exists('session_write_close')) {
            session_write_close();
        }

        $manifest = $this->updater->fetchManifest();
        if (!$manifest) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot fetch manifest.']);
            return;
        }

        $preview = $this->updater->previewChanges($manifest);

        // We no longer require a posted step. The Updater's persisted state
        // knows the current step and resume point. Accept it if sent, otherwise
        // the Updater will use its internal state.
        $state = $this->updater->getPersistedState();
        $step = ($state['step'] ?? 0) > 0 ? ($state['step'] ?? 1) : 1;
        $postedStep = (int) $this->input->post('step');
        if ($postedStep >= 1 && $postedStep <= 8) {
            $step = $postedStep;
        }

        $result = $this->updater->runStep($step, $manifest, $preview);
        echo json_encode($result);
    }

    /**
     * AJAX: Poll current progress
     */
    public function progress() {
        // Release session lock so the poller never blocks the updater
        if (function_exists('session_write_close')) {
            session_write_close();
        }

        $job = $this->updater->getProgress();
        if (!$job) {
            echo json_encode(['status' => 'idle']);
            return;
        }
        echo json_encode([
            'status' => $job->status,
            'current_step' => (int) $job->current_step,
            'total_steps' => (int) $job->total_steps,
            'step_label' => $job->step_label,
            'from_version' => $job->from_version,
            'to_version' => $job->to_version,
            'error_message' => $job->error_message,
            'log' => $job->log,
            'completed_at' => $job->completed_at,
        ]);
    }

    /**
     * AJAX: Restore from last backup
     */
    public function restore() {
        $result = $this->updater->restore();
        echo json_encode($result);
    }

    /**
     * AJAX: Save update channel URL
     */
    public function save_channel() {
        $url = trim($this->input->post('url'));
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid URL']);
            return;
        }

        $this->db->where('id', 1)->update('db_sitesettings', [
            'update_channel_url' => $url,
        ]);

        echo json_encode(['status' => 'ok', 'message' => 'Update channel saved.']);
    }

    /**
     * AJAX: Rebuild the stock ledger (db_warehouseitems) and item stock from
     * transaction history. Chunked like the updater — the browser keeps
     * calling with an increasing offset until done. Needed on installs where
     * earlier fatals left the warehouse ledger unpopulated: items show stock
     * on the edit screen but the Stock Report is empty.
     */
    public function rebuild_stock() {
        @set_time_limit(60);
        if (function_exists('session_write_close')) {
            session_write_close();
        }

        $offset = (int) $this->input->post('offset', TRUE);
        $batch  = 150;
        $total  = (int) $this->db->count_all('db_items');

        $items = $this->db->select('id, store_id')
            ->order_by('id', 'ASC')
            ->limit($batch, $offset)
            ->get('db_items');

        if (!$items) {
            $err = $this->db->error();
            echo json_encode(['status' => 'error', 'message' => 'Item query failed: ' . ($err['message'] ?? 'unknown')]);
            return;
        }

        $this->load->model('pos_model');
        $errors = 0;
        foreach ($items->result() as $item) {
            // Recompute the item master stock from transactions
            if (!$this->pos_model->update_items_quantity($item->id)) {
                $errors++;
            }
            // Rebuild the warehouse ledger for every warehouse of the store
            $whs = $this->db->select('id')->where('store_id', $item->store_id)->get('db_warehouse');
            if ($whs) {
                foreach ($whs->result() as $w) {
                    if (update_warehousewise_items_qty($item->id, $w->id, $item->store_id) === false) {
                        $errors++;
                    }
                }
            }
        }

        $processed = min($offset + $batch, $total);
        echo json_encode([
            'status'    => 'ok',
            'done'      => ($offset + $batch) >= $total,
            'progress'  => $processed,
            'total'     => $total,
            'errors'    => $errors,
            'message'   => "Rebuilt stock for {$processed} of {$total} items" . ($errors ? " ({$errors} item errors)" : ''),
        ]);
    }

    /**
     * AJAX: Lazy auto-update tick — called by the layout on admin page loads.
     * Throttled server-side (~6h); resumes a mid-flight update immediately.
     * Returns a small instruction set; the JS drives run_step itself.
     */
    public function auto_tick() {
        if (function_exists('session_write_close')) {
            session_write_close();
        }

        if (!$this->updater->shouldAutoCheck()) {
            echo json_encode(['status' => 'idle']);
            return;
        }
        $this->updater->touchAutoCheck();

        // checkForUpdate() also applies manifest-delivered settings
        // (fleet_url/fleet_key) — run it before the heartbeat so the first
        // contact already knows where to phone home.
        $check = $this->updater->checkForUpdate();
        $this->updater->sendHeartbeat();
        // Pick up commands queued on central (e.g. "update now") — remote
        // control without anyone logging into this install.
        $this->updater->pollFleetCommands();

        if (!empty($check['error'])) {
            echo json_encode(['status' => 'idle', 'reason' => $check['error']]);
            return;
        }

        $resuming = ($this->updater->getPersistedState()['step'] ?? 0) > 0;

        if (empty($check['available']) && !$resuming) {
            echo json_encode(['status' => 'none', 'installed_version' => $check['installed_version'] ?? null]);
            return;
        }
        if (!$resuming && !empty($check['blocked'])) {
            echo json_encode([
                'status' => 'blocked',
                'message' => $check['block_reason'],
                'remote_version' => $check['remote_version'],
            ]);
            return;
        }
        if (!$this->updater->autoUpdateEnabled()) {
            echo json_encode([
                'status' => 'available',
                'remote_version' => $check['remote_version'],
                'installed_version' => $check['installed_version'],
            ]);
            return;
        }

        echo json_encode([
            'status' => 'update',
            'from' => $check['installed_version'],
            'to' => $check['remote_version'],
        ]);
    }

    /**
     * AJAX: Enable/disable unattended updates
     */
    public function toggle_auto() {
        $enabled = (int) $this->input->post('enabled') === 1 ? 1 : 0;
        if ($this->db->field_exists('auto_update_enabled', 'db_sitesettings')) {
            $this->db->where('id', 1)->update('db_sitesettings', [
                'auto_update_enabled' => $enabled,
            ]);
        }
        echo json_encode(['status' => 'ok', 'enabled' => $enabled]);
    }

    /**
     * AJAX: Current auto-update setting (for the panel toggle)
     */
    public function get_auto() {
        echo json_encode([
            'status' => 'ok',
            'enabled' => $this->updater->autoUpdateEnabled() ? 1 : 0,
            'fleet_url' => $this->db->field_exists('fleet_url', 'db_sitesettings')
                ? (string) ($this->db->select('fleet_url')->where('id', 1)->get('db_sitesettings')->row()->fleet_url ?? '')
                : '',
        ]);
    }

    /**
     * AJAX: Get current update channel URL
     */
    public function get_channel() {
        $row = $this->db->select('update_channel_url')
            ->from('db_sitesettings')
            ->where('id', 1)
            ->get()
            ->row();
        $url = $row ? ($row->update_channel_url ?? '') : '';
        echo json_encode([
            'status' => 'ok',
            'url' => $url,
        ]);
    }
}
