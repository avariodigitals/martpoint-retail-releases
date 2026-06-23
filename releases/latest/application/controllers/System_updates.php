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
        if (!special_access()) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            exit;
        }
        $this->load->library('Updater');
    }

    /**
     * Render the Super Admin update panel
     */
    public function index() {
        // Access already verified by constructor's special_access() check
        $data = $this->data;
        $data['page_title'] = 'System Update';
        $this->load->view('system-updates', $data);
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
     * AJAX: Start update job and run a specific step
     * POST params: step (1-8)
     */
    public function run_step() {
        $step = (int) $this->input->post('step');
        if ($step < 1 || $step > 8) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid step']);
            return;
        }

        // Fetch manifest
        $manifest = $this->updater->fetchManifest();
        if (!$manifest) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot fetch manifest.']);
            return;
        }

        // On step 1, create a new job record
        if ($step === 1) {
            $installed = $this->updater->getInstalledVersion();
            $remote = $manifest['version'] ?? '0.0';
            $this->updater->startJob($installed, $remote);
        }

        // Preview needed for steps 2-5
        $preview = $this->updater->previewChanges($manifest);

        // Run the step
        $result = $this->updater->runStep($step, $manifest, $preview);
        echo json_encode($result);
    }

    /**
     * AJAX: Restore from last backup
     */
    public function restore() {
        $result = $this->updater->restore();
        echo json_encode($result);
    }

    /**
     * AJAX: Poll current progress
     */
    public function progress() {
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
     * AJAX: Save update channel URL
     */
    public function save_channel() {
        $url = trim($this->input->post('url'));
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid URL']);
            return;
        }

        // Ensure column exists (MySQL 5.7 safe)
        try {
            $this->db->where('id', 1)->update('db_sitesettings', [
                'update_channel_url' => $url,
            ]);
        } catch (Exception $e) {
            $this->ensureUpdateChannelColumn();
            $this->db->where('id', 1)->update('db_sitesettings', [
                'update_channel_url' => $url,
            ]);
        }

        echo json_encode(['status' => 'ok', 'message' => 'Update channel saved.']);
    }

    /**
     * AJAX: Get current update channel URL
     */
    public function get_channel() {
        try {
            $row = $this->db->select('update_channel_url')
                ->from('db_sitesettings')
                ->where('id', 1)
                ->get()
                ->row();
            $url = $row ? ($row->update_channel_url ?? '') : '';
        } catch (Exception $e) {
            $url = '';
        }
        echo json_encode([
            'status' => 'ok',
            'url' => $url,
        ]);
    }

    protected function ensureUpdateChannelColumn() {
        $db = $this->db->database;
        $exists = $this->db->query("SELECT COUNT(*) AS c FROM information_schema.columns WHERE table_schema = ? AND table_name = 'db_sitesettings' AND column_name = 'update_channel_url'", [$db])->row()->c;
        if ($exists == 0) {
            $this->db->query("ALTER TABLE `db_sitesettings` ADD COLUMN `update_channel_url` VARCHAR(500) DEFAULT NULL");
        }
    }
}
