<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * System_updates — Super Admin One-Click Auto-Update Controller
 * All endpoints are AJAX-driven to avoid cPanel execution timeouts.
 */
class System_updates extends MY_Controller {

    public function __construct() {
        parent::__construct();

        // auto_tick is a CHECK-IN ENDPOINT, not a panel page — it deliberately
        // skips load_global() and the admin gate.
        //
        // Two reasons, both load-bearing:
        //
        //  1. Central's wake ping carries no session. load_global() redirects
        //     anything unauthenticated to /logout, so gating it here would make
        //     the keyless wake impossible — which is why it appeared to do
        //     nothing even once the 404 was fixed.
        //
        //  2. It also avoids the DATABASE session lock entirely. Sessions use
        //     the database driver, so every request takes a per-session lock;
        //     an endpoint that may run a long update while holding one is the
        //     deadlock we already hit once. No session, no lock.
        //
        // Safety: this endpoint cannot choose what to install. It can only move
        // this install to the official release already configured in its own
        // update_channel_url, and that manifest is signature-verified before
        // anything is applied. Throttling (shouldAutoCheck) stops casual
        // repeat calls; `force=1` is used by Central, which is already the
        // authority for this install.
        if ($this->router->method === 'auto_tick') {
            $this->load->library('Updater');
            return;
        }

        $this->load_global();
        if (!is_admin() && !is_store_admin() && $this->session->userdata('role_id') != 1) {
            echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            exit;
        }
        $this->load->library('Updater');
    }

    /**
     * AJAX / wake — one auto-update tick.
     *
     * This method had FOUR callers and no implementation: mp_layout posts to it
     * on every admin page, and Fleet uses it as its keyless wake fallback. It
     * simply 404'd, so the browser-driven auto-update never started and Central's
     * keyless wake never landed — the visible symptom was an install that could
     * not update at all, with a "missing" 404 in the log.
     *
     * Return contract (mp_layout depends on it):
     *   {status:'update',    from, to}   an update is available — the page drives
     *                                    the steps itself via run_step()
     *   {status:'blocked',   message}    licence/plan refuses the update
     *   {status:'available', remote_version}  newer release exists, not auto-applied
     *   {status:'idle'}                  nothing to do (or throttled)
     *
     * Query flags:
     *   force=1           ignore the throttle
     *   commands_only=1   run queued fleet commands and stop — no update pipeline
     */
    public function auto_tick() {
        header('Content-Type: application/json');
        // Must cover a full server-side slice when Central forces one, and the
        // request may be cut off by the caller while the work continues.
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        @ignore_user_abort(true);
        // Release the session lock — on the database session driver a held lock
        // blocks every other request from the same browser, including the
        // run_step calls this method may trigger.
        if (function_exists('session_write_close')) { session_write_close(); }

        $force        = (int) $this->input->get('force') === 1;
        $commandsOnly = (int) $this->input->get('commands_only') === 1;

        try {
            // Heartbeat and queued commands first — cheap, and Central's wake
            // ping expects its commands to have run by the time this returns.
            $this->updater->sendHeartbeat();
            $commands = $this->updater->pollFleetCommands();
            if (!empty($commands)) {
                $this->updater->sendHeartbeat();
            }

            if ($commandsOnly) {
                echo json_encode(['status' => 'ok', 'commands' => $commands]);
                return;
            }

            $check = $this->updater->checkForUpdate();
            if (!empty($check['error'])) {
                echo json_encode(['status' => 'error', 'message' => $check['error'],
                    'commands' => $commands]);
                return;
            }
            if (!empty($check['blocked'])) {
                echo json_encode(['status' => 'blocked',
                    'message' => (string) ($check['block_reason'] ?? 'Update blocked.'),
                    'commands' => $commands]);
                return;
            }
            if (empty($check['available'])) {
                echo json_encode(['status' => 'idle', 'commands' => $commands]);
                return;
            }

            $from = (string) ($check['installed_version'] ?? '');
            $to   = (string) ($check['remote_version'] ?? '');

            if ($force) {
                // Central asked. Run server-side to completion, because no
                // browser is present to drive the steps.
                $r = $this->updater->runUpdateToCompletion(110);
                $this->updater->sendHeartbeat();
                echo json_encode([
                    'status'   => !empty($r['done']) ? 'updated' : 'update',
                    'from'     => $from,
                    'to'       => $to,
                    'message'  => (string) ($r['message'] ?? ''),
                    'commands' => $commands,
                ]);
                return;
            }

            // Browser path: report availability and let the page drive run_step,
            // which keeps a live progress bar and survives host time limits.
            //
            // Throttled so a page load does not start an update on its own; the
            // stamp is only touched when there is actually something to do.
            if (!$this->updater->shouldAutoCheck()) {
                echo json_encode(['status' => 'idle', 'reason' => 'throttled',
                    'remote_version' => $to, 'commands' => $commands]);
                return;
            }
            $this->updater->touchAutoCheck();

            echo json_encode([
                'status'   => 'update',
                'from'     => $from,
                'to'       => $to,
                'commands' => $commands,
            ]);
        } catch (Throwable $e) {
            // Never blank-500 this — mp_layout and Central both parse the body.
            log_message('error', 'auto_tick failed: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => get_class($e) . ': ' . $e->getMessage()]);
        }
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
