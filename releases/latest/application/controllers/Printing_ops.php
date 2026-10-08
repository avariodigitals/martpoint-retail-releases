<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Printing — Machine & Ops Workspace.
 *
 * Migrations .107/.108 built the schema and Printing_ops_model the logic for
 * machines, counter readings, consumables, maintenance and customer-owned
 * material custody — but no controller and no screen were ever written for any
 * of it. The tables sat behind a model only the acceptance suite called, so a
 * print shop had no way to see its own presses.
 *
 * This controller is that missing screen. It deliberately holds NO business
 * rules: every number, derived figure and state transition comes from
 * Printing_ops_model, which the suite already exercises against the real
 * schema. A rule that needs changing belongs in the model, not here.
 *
 * SIGNATURES ARE NOT GUESSED. Each call below was verified against the model:
 *   get_machines($store_id, array $f)
 *   get_machine($id)                    — no store argument; ownership is
 *                                         checked here via _owned_machine()
 *   save_machine(array $data, $id)      — store comes from the session
 *   set_machine_status($machine_id, $status, $reason)
 *   retire_machine($machine_id)
 *   get_readings($machine_id, $limit)
 *   record_reading($machine_id, array $d)
 *   suggest_reading($machine_id) / counter_activity($machine_id, $from, $to)
 *   invalid_readings($store_id, $limit)
 *   machines_due_service($store_id) / material_position_summary($store_id)
 *   machine_report($machine_id, $from, $to)   — per MACHINE, not per store
 *   maintenance_summary($machine_id, $from, $to)
 *   get_supplies($store_id, array $f)
 *   get_maintenance($machine_id, $store_id, $limit)
 *   record_maintenance(array $d) / request_supply(array $d)
 *   receive_customer_material(array $d)
 *   get_customer_materials($store_id, array $f)
 *   unallocated_materials($customer_id, $store_id)
 *   breakdown_mismatches($store_id, $limit)
 *   customer_custody_statement($customer_id, $store_id)
 *   custody_balance($material_ROW)   — takes a ROW, not an id
 *   custody_moves($material_id)      — takes a material id
 */
class Printing_ops extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load_global();
        $this->load->model('printing_model', 'print');
        $this->load->model('printing_ops_model', 'ops');
    }

    /**
     * The module gate, matching Printing::_check_feature().
     *
     * `production_workflow` is the flag the printing preset actually declares.
     * Printing.php previously read `printing_workflow`, a key that exists
     * nowhere, which made its whole workspace unreachable. This method exists
     * so that mistake cannot be copied into a second file.
     */
    private function _check_feature() {
        if (!mp_feature_enabled('production_workflow') && !mp_feature_enabled('printing_workflow')) {
            $this->show_feature_not_activated('production_workflow',
                'Enable the Printing module from Business Profile, then pick category presets.');
        }
    }

    private function _render($page_title, $view, $data = []) {
        $d = $this->data ?? [];
        $d['page_title'] = $page_title;
        $d = array_merge($d, $data);
        $d['content'] = $this->load->view($view, $d, TRUE);
        $this->load->view('mp_layout', $d);
    }

    private function _json($arr) {
        $arr['csrf_hash'] = $this->security->get_csrf_hash();
        header('Content-Type: application/json');
        echo json_encode($arr);
    }

    /**
     * Load a machine and prove it belongs to the session's store.
     *
     * get_machine($id) is keyed on id alone, so without this a crafted id would
     * expose another store's machine. Centralising the check means no endpoint
     * can forget it.
     */
    private function _owned_machine($id) {
        $id = (int) $id;
        if ($id <= 0) return null;
        $m = $this->ops->get_machine($id);
        if (!$m) return null;
        if ((int) $m->store_id !== (int) get_current_store_id()) return null;
        return $m;
    }

    /* ========================= machines ========================= */

    public function machines() {
        $this->_check_feature();
        $this->permission_check('print_view');
        $store_id = get_current_store_id();

        $f = array_filter([
            'status'   => $this->input->get('status'),
            'category' => $this->input->get('category'),
        ], function ($v) { return $v !== null && $v !== ''; });

        $this->_render('Machines', 'printing/machines', [
            'machines'   => $this->ops->get_machines($store_id, $f),
            'due'        => $this->ops->machines_due_service($store_id),
            'can_manage' => $this->permissions('print_production'),
        ]);
    }

    public function machine($id = 0) {
        $this->_check_feature();
        $this->permission_check('print_view');

        $machine = $this->_owned_machine($id);
        if (!$machine) {
            show_error('That machine is not on this store.', 404, 'Machine not found');
            return;
        }

        $mid = (int) $machine->id;
        $this->_render($machine->name ?: 'Machine', 'printing/machine', [
            'machine'     => $machine,
            'readings'    => $this->ops->get_readings($mid, 50),
            'supplies'    => $this->ops->get_supplies(null, ['machine_id' => $mid]),
            'maintenance' => $this->ops->get_maintenance($mid),
            'activity'    => $this->ops->counter_activity($mid),
            'suggest'     => $this->ops->suggest_reading($mid),
            'summary'     => $this->ops->maintenance_summary($mid),
            'report'      => $this->ops->machine_report($mid),
            'can_manage'  => $this->permissions('print_production'),
        ]);
    }

    public function machine_save() {
        $this->_check_feature();
        $this->permission_check('print_production');

        $id   = (int) $this->input->post('id');
        $data = [
            'name'                  => trim((string) $this->input->post('name')),
            'machine_code'          => trim((string) $this->input->post('machine_code')),
            'model'                 => trim((string) $this->input->post('model')),
            'manufacturer'          => trim((string) $this->input->post('manufacturer')),
            'serial_no'             => trim((string) $this->input->post('serial_no')),
            'location'              => trim((string) $this->input->post('location')),
            'reading_mode'          => $this->input->post('reading_mode') ?: 'counter',
            'counter_unit'          => trim((string) $this->input->post('counter_unit')) ?: 'impressions',
            'service_interval_days' => (int) $this->input->post('service_interval_days'),
            'notes'                 => trim((string) $this->input->post('notes')),
        ];

        // A machine with no name is unidentifiable in every list and report, so
        // this is the one field worth refusing outright.
        if ($data['name'] === '') {
            $this->_json(['status' => 'error', 'message' => 'Give the machine a name.']);
            return;
        }

        // Editing must target a machine this store owns.
        if ($id > 0 && !$this->_owned_machine($id)) {
            $this->_json(['status' => 'error', 'message' => 'That machine is not on this store.']);
            return;
        }

        $res = $this->ops->save_machine($data, $id > 0 ? $id : null);
        if (!empty($res['success'])) {
            $this->_json([
                'status'  => 'ok',
                'message' => $id > 0 ? 'Machine updated.' : 'Machine added.',
                'id'      => (int) ($res['id'] ?? $id),
            ]);
        } else {
            $this->_json(['status' => 'error', 'message' => $res['message'] ?? 'Could not save the machine.']);
        }
    }

    public function machine_status() {
        $this->_check_feature();
        $this->permission_check('print_production');

        $id = (int) $this->input->post('id');
        if (!$this->_owned_machine($id)) {
            $this->_json(['status' => 'error', 'message' => 'That machine is not on this store.']);
            return;
        }

        $res = $this->ops->set_machine_status(
            $id,
            (string) $this->input->post('status'),
            trim((string) $this->input->post('reason'))
        );
        $this->_json(!empty($res['success'])
            ? ['status' => 'ok', 'message' => 'Status updated.']
            : ['status' => 'error', 'message' => $res['message'] ?? 'Could not change status.']);
    }

    public function machine_retire() {
        $this->_check_feature();
        $this->permission_check('print_production');

        $id = (int) $this->input->post('id');
        if (!$this->_owned_machine($id)) {
            $this->_json(['status' => 'error', 'message' => 'That machine is not on this store.']);
            return;
        }

        $this->ops->retire_machine($id);
        $this->_json(['status' => 'ok', 'message' => 'Machine retired.']);
    }

    /* ========================= readings ========================= */

    public function readings() {
        $this->_check_feature();
        $this->permission_check('print_view');
        $store_id = get_current_store_id();

        $this->_render('Counter Readings', 'printing/readings', [
            'machines'   => $this->ops->get_machines($store_id),
            'invalid'    => $this->ops->invalid_readings($store_id),
            'can_record' => $this->permissions('print_production'),
        ]);
    }

    public function reading_save() {
        $this->_check_feature();
        $this->permission_check('print_production');

        $machine_id = (int) $this->input->post('machine_id');
        $value      = $this->input->post('reading');

        if (!$this->_owned_machine($machine_id)) {
            $this->_json(['status' => 'error', 'message' => 'Pick a machine on this store.']);
            return;
        }
        // A blank reading must be refused: (float)'' is 0.0, and a zero would be
        // stored as a real counter value, silently corrupting run arithmetic.
        if ($value === null || trim((string) $value) === '') {
            $this->_json(['status' => 'error', 'message' => 'Enter a reading.']);
            return;
        }

        $res = $this->ops->record_reading($machine_id, [
            'reading' => (float) $value,
            'notes'   => trim((string) $this->input->post('notes')),
            'run_id'  => (int) $this->input->post('run_id') ?: null,
        ]);

        $this->_json(!empty($res['success'])
            ? ['status' => 'ok', 'message' => 'Reading recorded.', 'data' => $res]
            : ['status' => 'error', 'message' => $res['message'] ?? 'Could not record the reading.']);
    }

    /* ========================= consumables ========================= */

    public function supplies() {
        $this->_check_feature();
        $this->permission_check('print_view');
        $store_id = get_current_store_id();

        $this->_render('Consumables', 'printing/supplies', [
            'supplies'   => $this->ops->get_supplies($store_id),
            'machines'   => $this->ops->get_machines($store_id),
            'can_manage' => $this->permissions('print_production'),
        ]);
    }

    public function supply_save() {
        $this->_check_feature();
        $this->permission_check('print_production');

        $machine_id = (int) $this->input->post('machine_id');
        $desc       = trim((string) $this->input->post('description'));
        if (!$this->_owned_machine($machine_id) || $desc === '') {
            $this->_json(['status' => 'error', 'message' => 'Pick a machine and name the consumable.']);
            return;
        }

        // request_supply() reads store_id from the session itself, and expects
        // `description` + `requested_qty` (not item/qty) — the record may be a
        // free-text request or a linked stock item, so the field names reflect
        // that rather than assuming an item exists.
        $res = $this->ops->request_supply([
            'machine_id'    => $machine_id,
            'description'   => $desc,
            'requested_qty' => (float) $this->input->post('requested_qty'),
            'unit_id'       => (int) $this->input->post('unit_id') ?: null,
        ]);

        $this->_json(!empty($res['success'])
            ? ['status' => 'ok', 'message' => 'Requested.']
            : ['status' => 'error', 'message' => $res['message'] ?? 'Could not record the request.']);
    }

    /* ========================= maintenance ========================= */

    public function maintenance() {
        $this->_check_feature();
        $this->permission_check('print_view');
        $store_id = get_current_store_id();

        $this->_render('Maintenance', 'printing/maintenance', [
            'due'        => $this->ops->machines_due_service($store_id),
            'visits'     => $this->ops->get_maintenance(null, $store_id),
            'machines'   => $this->ops->get_machines($store_id),
            'can_manage' => $this->permissions('print_production'),
        ]);
    }

    public function maintenance_save() {
        $this->_check_feature();
        $this->permission_check('print_production');

        $machine_id = (int) $this->input->post('machine_id');
        if (!$this->_owned_machine($machine_id)) {
            $this->_json(['status' => 'error', 'message' => 'Pick a machine on this store.']);
            return;
        }

        // record_maintenance() takes one array and derives the store itself.
        $res = $this->ops->record_maintenance([
            'machine_id'   => $machine_id,
            'performed_at' => trim((string) $this->input->post('performed_at')) ?: date('Y-m-d'),
            'engineer'     => trim((string) $this->input->post('engineer')),
            'description'  => trim((string) $this->input->post('description')),
            'cost'         => (float) $this->input->post('cost'),
            'reading'      => $this->input->post('reading'),
        ]);

        $this->_json(!empty($res['success'])
            ? ['status' => 'ok', 'message' => 'Maintenance logged.']
            : ['status' => 'error', 'message' => $res['message'] ?? 'Could not log the visit.']);
    }

    /* ========================= customer material custody ==================== */

    public function custody() {
        $this->_check_feature();
        $this->permission_check('print_view');
        $store_id = get_current_store_id();

        $this->_render('Customer Materials', 'printing/custody', [
            'materials'  => $this->ops->get_customer_materials($store_id),
            'unalloc'    => $this->ops->unallocated_materials(null, $store_id),
            'mismatch'   => $this->ops->breakdown_mismatches($store_id),
            'position'   => $this->ops->material_position_summary($store_id),
            'can_manage' => $this->permissions('print_production'),
        ]);
    }

    public function custody_receive() {
        $this->_check_feature();
        $this->permission_check('print_production');

        $customer_id = (int) $this->input->post('customer_id');
        $item        = trim((string) $this->input->post('material_name'));
        if ($customer_id <= 0 || $item === '') {
            $this->_json(['status' => 'error', 'message' => 'Choose a client and describe the material.']);
            return;
        }

        // receive_customer_material() expects `material_name` + `qty_received`.
        // It also validates that any size/colour breakdown reconciles to the
        // received quantity — a mismatch is stored as a flag rather than a
        // refusal, so the record survives but the discrepancy is visible.
        $res = $this->ops->receive_customer_material([
            'customer_id'  => $customer_id,
            'job_id'       => (int) $this->input->post('job_id') ?: null,
            'material_name'=> $item,
            'material_type'=> trim((string) $this->input->post('material_type')),
            'qty_received' => (float) $this->input->post('qty_received'),
            'unit_label'   => trim((string) $this->input->post('unit_label')),
            'received_at'  => trim((string) $this->input->post('received_at')) ?: date('Y-m-d'),
            'condition_in' => trim((string) $this->input->post('condition_in')),
            'notes'        => trim((string) $this->input->post('notes')),
        ]);

        $this->_json(!empty($res['success'])
            ? ['status' => 'ok', 'message' => 'Material received.']
            : ['status' => 'error', 'message' => $res['message'] ?? 'Could not record the material.']);
    }

    /**
     * One client's custody statement.
     *
     * A client with nothing in custody returns null from the model, which is
     * indistinguishable from "not found". A zero-balance client is a real
     * thing, so this renders the page with an empty ledger rather than a 404.
     */
    public function custody_statement($customer_id = 0) {
        $this->_check_feature();
        $this->permission_check('print_view');
        $store_id = get_current_store_id();
        $customer_id = (int) $customer_id;

        if ($customer_id <= 0) {
            show_error('Pick a client.', 404, 'Not found');
            return;
        }

        $customer = $this->db->select('customer_name, mobile')->where('id', $customer_id)
            ->where('store_id', $store_id)->get('db_customers')->row();

        $this->_render('Material Statement', 'printing/custody_statement', [
            'customer_id' => $customer_id,
            'customer'    => $customer,
            'statement'   => $this->ops->customer_custody_statement($customer_id, $store_id),
            'materials'   => $this->ops->get_customer_materials($store_id, ['customer_id' => $customer_id]),
            'unalloc'     => $this->ops->unallocated_materials($customer_id, $store_id),
        ]);
    }

    /** Move ledger for one material line. custody_moves() takes a material id. */
    public function custody_moves_json() {
        $this->_check_feature();
        $this->permission_check('print_view');
        $material_id = (int) $this->input->get('material_id');

        $moves = $material_id > 0 ? $this->ops->custody_moves($material_id) : [];

        // custody_balance() takes the MATERIAL ROW, not an id — passing an id
        // would be treated as "no row" and silently return null.
        $row = $material_id > 0
            ? $this->db->where('id', $material_id)->get('db_print_customer_materials')->row()
            : null;

        $this->_json([
            'status'  => 'ok',
            'moves'   => $moves,
            'balance' => $row ? $this->ops->custody_balance($row) : null,
        ]);
    }

    /* ========================= equipment report ========================= */

    /**
     * What each machine produced over a period.
     *
     * machine_report() is per MACHINE, so this loops the store's machines and
     * collects one report each. The per-machine method is deliberately not
     * changed: it is already exercised by the acceptance suite, and a
     * store-wide variant would be a second implementation of the same
     * arithmetic — the classic way two figures for one number start disagreeing.
     */
    public function equipment_report() {
        $this->_check_feature();
        $this->permission_check('print_view');
        $store_id = get_current_store_id();

        $from = trim((string) $this->input->get('from')) ?: null;
        $to   = trim((string) $this->input->get('to')) ?: null;

        $report = [];
        foreach ($this->ops->get_machines($store_id) as $m) {
            $r = $this->ops->machine_report((int) $m->id, $from, $to);

            // machine_report() returns a NESTED structure —
            // ['machine'=>…, 'output'=>['accepted','input','rejects','waste','runs'], …]
            // — not flat figures. Flatten the output block here so the view
            // reads plain keys and cannot silently render blanks by looking one
            // level too high.
            $out = is_array($r) ? ($r['output'] ?? []) : [];
            $report[] = [
                'machine'   => $m,
                'runs'      => (int) ($out['runs'] ?? 0),
                'accepted'  => (float) ($out['accepted'] ?? 0),
                'input_qty' => (float) ($out['input'] ?? 0),
                'rejects'   => (float) ($out['rejects'] ?? 0),
                'waste'     => (float) ($out['waste'] ?? 0),
                'rework'    => (float) ($out['rework'] ?? 0),
            ];
        }

        $this->_render('Equipment Report', 'printing/equipment_report', [
            'report' => $report,
            'due'    => $this->ops->machines_due_service($store_id),
            'from'   => $from,
            'to'     => $to,
        ]);
    }

    /* ========================= JSON helpers for panels ===================== */

    public function readings_json() {
        $this->_check_feature();
        $this->permission_check('print_view');
        $machine_id = (int) $this->input->get('machine_id');

        if (!$this->_owned_machine($machine_id)) {
            $this->_json(['status' => 'error', 'message' => 'Machine not found.']);
            return;
        }

        $this->_json([
            'status'   => 'ok',
            'readings' => $this->ops->get_readings($machine_id, 100),
            'activity' => $this->ops->counter_activity($machine_id),
            'suggest'  => $this->ops->suggest_reading($machine_id),
        ]);
    }

    public function machine_json() {
        $this->_check_feature();
        $this->permission_check('print_view');
        $machine = $this->_owned_machine($this->input->get('machine_id'));
        $this->_json($machine
            ? ['status' => 'ok', 'machine' => $machine]
            : ['status' => 'error', 'message' => 'Machine not found.']);
    }
}
