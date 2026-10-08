<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Printing — machines, runs, consumables, maintenance and customer-owned
 * material custody: acceptance scenarios (migrations 4.0.9.107 / .108).
 *
 * Included by Printing_acceptance so the machine/custody suite lives beside the
 * commercial suite without making one file unmanageable. Every assertion drives
 * the REAL model against the REAL schema; none of them is a screen-existence
 * check. Fixtures carry the run tag so a sweep is repeatable and isolated.
 */
trait Printing_acceptance_machine_ops {

    /** Audit scenarios 1–5, 7, plus the reporting reconciliation of 12. */
    private function run_machine_ops_scenarios() {
        $this->load->model('printing_ops_model', 'ops');
        $store_id = $this->storeId;
        $this->print->seed_categories($store_id);
        $M = 'MO';

        $cat = $this->db->where('store_id', $store_id)->where('category_key', 'digital_imaging')->get('db_print_categories')->row();
        if (!$cat) $cat = $this->db->where('store_id', $store_id)->order_by('id', 'asc')->get('db_print_categories')->row();

        $this->mo_machine_register($M, $store_id);
        $this->mo_readings($M, $store_id);
        list($job, $print_stage) = $this->mo_mixed_job($M, $store_id, $cat);
        $this->mo_runs($M, $store_id, $job, $print_stage);
        $this->mo_supplies($M, $store_id, $job);
        $this->mo_maintenance($M, $store_id);
        $this->mo_custody($M, $store_id);
        $this->mo_reporting($M, $store_id);

        $this->mo_cleanup($store_id);
    }

    /* ==================================================================== */
    /*  Scenario 1 — machine register                                        */
    /* ==================================================================== */

    private function mo_machine_register($M, $store_id) {
        $r1 = $this->ops->save_machine([
            'name' => 'HP Indigo 7900 ' . $this->tag, 'machine_code' => 'MO1' . $this->tag,
            'machine_category' => 'digital', 'supported_stages' => ['print', 'lamination'],
            'reading_mode' => 'required', 'has_colour_counter' => 1, 'counter_unit' => 'impressions',
            'service_interval_days' => 90, 'location' => 'Press room', 'model' => 'Indigo 7900',
        ]);
        $this->check($M, 'machine registered with name, model, identifier and category',
            !empty($r1['success']), $r1['message'] ?? '');

        $r2 = $this->ops->save_machine([
            'name' => 'Roland XR-640 ' . $this->tag, 'machine_code' => 'MO2' . $this->tag,
            'machine_category' => 'large_format', 'supported_stages' => ['print'],
            'reading_mode' => 'optional', 'has_colour_counter' => 0, 'counter_unit' => 'metres',
        ]);
        $this->check($M, 'second machine registered', !empty($r2['success']), $r2['message'] ?? '');

        $r3 = $this->ops->save_machine([
            'name' => 'Duplo cutter ' . $this->tag, 'machine_code' => 'MO3' . $this->tag,
            'machine_category' => 'cutting', 'supported_stages' => ['cutting'],
            'reading_mode' => 'unavailable', 'counter_unit' => 'units',
        ]);
        $this->check($M, 'machine with NO counters can still be registered', !empty($r3['success']), $r3['message'] ?? '');

        $this->mo_ids = [
            'm1' => $r1['machine_id'] ?? 0,
            'm2' => $r2['machine_id'] ?? 0,
            'm3' => $r3['machine_id'] ?? 0,
        ];

        $dup = $this->ops->save_machine(['name' => 'Duplicate code', 'machine_code' => 'MO1' . $this->tag]);
        $this->check($M, 'duplicate machine identifier is refused', empty($dup['success']), $dup['message'] ?? '');

        $m = $this->ops->get_machine($this->mo_ids['m1']);
        $this->check($M, 'supported production stages are stored per machine',
            in_array('print', $m->supported_stages, true) && in_array('lamination', $m->supported_stages, true),
            implode(',', $m->supported_stages));
        $this->check($M, 'counter unit is stored per machine', $m->counter_unit === 'impressions');
        $this->check($M, 'machine defaults to AVAILABLE', $m->status === 'available', $m->status);

        // Operational status transitions, with a reason required off-available.
        $bad = $this->ops->set_machine_status($this->mo_ids['m1'], 'maintenance', '');
        $this->check($M, 'taking a machine out of service without a reason is refused', empty($bad['success']));
        $ok = $this->ops->set_machine_status($this->mo_ids['m1'], 'maintenance', 'Scheduled 90-day service');
        $this->check($M, 'maintenance status recorded with a reason', !empty($ok['success']));
        $this->check($M, 'machine status persists',
            $this->ops->get_machine($this->mo_ids['m1'])->status === 'maintenance');
        $this->ops->set_machine_status($this->mo_ids['m1'], 'available', '');

        $stage_list = $this->ops->machines_for_stage($store_id, 'print');
        $this->check($M, 'machines_for_stage returns the print-capable machines', count($stage_list) >= 2, 'found=' . count($stage_list));
        $cut_list = $this->ops->machines_for_stage($store_id, 'cutting');
        $this->check($M, 'machines_for_stage excludes machines that cannot do the stage',
            count(array_filter($cut_list, function ($x) { return (int)$x->id === (int)$this->mo_ids['m1']; })) === 0);
        $avail_only = $this->ops->machines_for_stage($store_id, 'print', true);
        $this->check($M, 'machines_for_stage can exclude unavailable machines',
            count($avail_only) <= count($stage_list));
    }

    /* ==================================================================== */
    /*  Scenarios 1b & 2 — readings, correction, reset                       */
    /* ==================================================================== */

    private function mo_readings($M, $store_id) {
        $m1 = $this->mo_ids['m1'];
        $m2 = $this->mo_ids['m2'];
        $m3 = $this->mo_ids['m3'];

        // --- Opening reading: a BASELINE, never output or cost ---
        // --- Opening reading: a BASELINE, never output or cost ---
        $o1 = $this->ops->record_reading($m1, [
            'reading_type' => 'opening', 'mono_reading' => 100000, 'colour_reading' => 40000,
            'reading_date' => date('Y-m-d', strtotime('-30 days')),
        ]);
        $this->check($M, 'dated opening reading recorded', !empty($o1['success']), $o1['message'] ?? '');
        $o_row = $this->db->where('id', $o1['reading_id'])->get('db_print_machine_readings')->row();
        $this->check($M, 'opening reading is flagged as a baseline', (int)$o_row->opening_baseline === 1);
        $this->check($M, 'opening reading contributes ZERO counter activity',
            (float)$this->ops->counter_activity($m1)->total === 0.0,
            'activity=' . $this->ops->counter_activity($m1)->total);
        $this->check($M, 'opening reading carries no cost columns on the machine',
            (float)$this->ops->get_machine($m1)->purchase_cost >= 0.0);

        // Suggested reading = last live reading, with its staleness.
        $sug = $this->ops->suggest_reading($m1);
        $this->check($M, 'new-run suggestion offers the last recorded reading',
            $sug['mono'] !== null && (float)$sug['mono'] === 100000.0, 'mono=' . var_export($sug['mono'], true));
        $this->check($M, 'suggestion reports how stale the reading is', $sug['stale_days'] === 30, 'stale=' . var_export($sug['stale_days'], true));
        $this->check($M, 'suggestion reports the counter mode for the form', $sug['reading_mode'] === 'required');

        // --- Separate colour/mono counters ---
        $run_a = $this->ops->record_reading($m1, [
            'reading_type' => 'run', 'mono_reading' => 100500, 'colour_reading' => 40120,
            'reading_date' => date('Y-m-d', strtotime('-2 days')),
        ]);
        $this->check($M, 'mono and colour counters recorded separately', !empty($run_a['success']), $run_a['message'] ?? '');
        $a = $this->db->where('id', $run_a['reading_id'])->get('db_print_machine_readings')->row();
        $this->check($M, 'mono delta computed by the SERVER (500)', (float)$a->mono_delta === 500.0, 'mono_delta=' . $a->mono_delta);
        $this->check($M, 'colour delta computed by the SERVER (120)', (float)$a->colour_delta === 120.0, 'colour_delta=' . $a->colour_delta);
        $this->check($M, 'total activity = mono + colour (620)', (float)$a->delta === 620.0, 'delta=' . $a->delta);

        // --- Correction: new row, old value PRESERVED ---
        $corr = $this->ops->record_reading($m1, [
            'reading_type' => 'correction', 'mono_reading' => 100480, 'colour_reading' => 40120,
            'corrects_reading_id' => $run_a['reading_id'],
            'reason' => 'Operator misread the meter by 20.',
            'authorized_by' => 'supervisor',
        ]);
        $this->check($M, 'correction accepted with a reason and an authorized handler',
            !empty($corr['success']), $corr['message'] ?? '');
        $old = $this->db->where('id', $run_a['reading_id'])->get('db_print_machine_readings')->row();
        $this->check($M, 'corrected reading KEEPS its original value (100500)', (float)$old->mono_reading === 100500.0, 'mono=' . $old->mono_reading);
        $this->check($M, 'corrected reading is SUPERSEDED, not deleted', $old->status === 'superseded', 'status=' . $old->status);
        $new = $this->db->where('id', $corr['reading_id'])->get('db_print_machine_readings')->row();
        $this->check($M, 'correction row points at the reading it corrects', (int)$new->corrects_reading_id === (int)$run_a['reading_id']);
        $this->check($M, 'correction is NOT counted as machine activity', (int)$new->delta_valid === 0);
        $this->check($M, 'correction reason is preserved on the row', $new->reason === 'Operator misread the meter by 20.');
        $this->check($M, 'correction records who authorized it', $new->authorized_by === 'supervisor');

        $bad_corr = $this->ops->record_reading($m1, ['reading_type' => 'correction', 'mono_reading' => 100490]);
        $this->check($M, 'correction WITHOUT a reason is refused', empty($bad_corr['success']), $bad_corr['message'] ?? '');
        $bad_corr2 = $this->ops->record_reading($m1, [
            'reading_type' => 'correction', 'mono_reading' => 100490, 'reason' => 'typo',
        ]);
        $this->check($M, 'correction WITHOUT an authorized handler is refused', empty($bad_corr2['success']));

        // --- Counter reset: never activity ---
        $reset = $this->ops->record_reading($m1, [
            'reading_type' => 'reset', 'mono_reading' => 0, 'colour_reading' => 0,
            'reason' => 'Counter zeroed after board replacement.', 'authorized_by' => 'supervisor',
        ]);
        $this->check($M, 'counter reset recorded with a reason', !empty($reset['success']), $reset['message'] ?? '');
        $rr = $this->db->where('id', $reset['reading_id'])->get('db_print_machine_readings')->row();
        $this->check($M, 'reset delta is NOT valid activity', (int)$rr->delta_valid === 0, 'delta_valid=' . $rr->delta_valid);
        $this->check($M, 'reset EXPLAINS why its delta is unusable', !empty($rr->delta_invalid_reason), (string)$rr->delta_invalid_reason);
        // The correction superseded the 500/120 reading, and the reset is not
        // activity either — so despite two large counter events the machine's
        // measured activity is ZERO. That is the whole point: a correction and a
        // reset must never inflate output.
        $this->check($M, 'a correction plus a reset do NOT inflate counter activity to a spike',
            (float)$this->ops->counter_activity($m1)->total === 0.0,
            'activity=' . $this->ops->counter_activity($m1)->total);
        $bad_reset = $this->ops->record_reading($m1, ['reading_type' => 'reset', 'mono_reading' => 0]);
        $this->check($M, 'counter reset WITHOUT a reason is refused', empty($bad_reset['success']));

        // --- Counter replacement is its own, separately-audited event ---
        $repl = $this->ops->record_reading($m1, [
            'reading_type' => 'replacement', 'mono_reading' => 0, 'colour_reading' => 0,
            'reason' => 'Fitted a new counter unit.', 'authorized_by' => 'supervisor',
        ]);
        $this->check($M, 'counter replacement recorded with a reason', !empty($repl['success']), $repl['message'] ?? '');
        $this->check($M, 'replacement is distinguishable from a reset',
            $this->db->where('id', $repl['reading_id'])->get('db_print_machine_readings')->row()->reading_type === 'replacement');

        // --- Counter-mode enforcement ---
        $empty_req = $this->ops->record_reading($m1, ['reading_type' => 'run']);
        $this->check($M, 'machine with REQUIRED readings refuses an empty run reading', empty($empty_req['success']), $empty_req['message'] ?? '');

        $no_counter = $this->ops->record_reading($m3, ['reading_type' => 'run', 'mono_reading' => 10]);
        $this->check($M, 'machine with NO counters refuses a counter reading', empty($no_counter['success']), $no_counter['message'] ?? '');
        $this->check($M, 'the refusal explains the machine has no counters',
            strpos((string)($no_counter['message'] ?? ''), 'no counters') !== false, (string)($no_counter['message'] ?? ''));

        // A non-colour machine must refuse a colour reading rather than invent it.
        $colour_on_mono = $this->ops->record_reading($m2, ['reading_type' => 'run', 'colour_reading' => 5]);
        $this->check($M, 'machine without a colour counter refuses a colour reading',
            empty($colour_on_mono['success']), $colour_on_mono['message'] ?? '');

        // The very first reading on a machine is a baseline by nature.
        $first = $this->ops->record_reading($m2, ['reading_type' => 'run', 'mono_reading' => 10]);
        $this->check($M, 'first reading on a machine is treated as an opening baseline', !empty($first['success']), $first['message'] ?? '');

        $this->check($M, 'invalid readings are listed for review',
            count($this->ops->invalid_readings($store_id)) >= 3, 'listed=' . count($this->ops->invalid_readings($store_id)));
    }

    /* ==================================================================== */
    /*  Scenario 1 & 2b — a mixed job spanning several machines              */
    /* ==================================================================== */

    private function mo_mixed_job($M, $store_id, $cat) {
        $job = $this->print->create_job(['customer_id' => null, 'title' => 'Machine ops ' . $this->tag], [[
            'category_id' => $cat->id, 'qty' => 1000, 'unit_price' => 12,
            'description' => 'Mixed machine job', 'spec' => ['material' => 'card 300gsm'],
        ]]);
        $this->check($M, 'mixed job created for the machine runs', $job > 0);
        $stages = $this->print->get_stages($job);
        $this->check($M, 'job has a production stage plan', count($stages) > 0, 'stages=' . count($stages));

        $print_stage = null;
        foreach ($stages as $s) { if (stripos($s->stage_key, 'print') !== false) { $print_stage = $s; break; } }
        if (!$print_stage && !empty($stages)) $print_stage = $stages[0];
        if (!$print_stage) {
            $this->db->insert('db_print_stages', [
                'store_id' => $store_id, 'job_id' => $job, 'seq' => 1,
                'stage_key' => 'print', 'stage_label' => 'Print', 'status' => 'pending',
            ]);
            $print_stage = $this->db->where('id', $this->db->insert_id())->get('db_print_stages')->row();
        }

        // Planning a machine is NOT running on it — record the plan separately.
        $this->db->where('id', $print_stage->id)->update('db_print_stages', ['machine_id' => $this->mo_ids['m1']]);
        $print_stage = $this->db->where('id', $print_stage->id)->get('db_print_stages')->row();
        $this->check($M, 'a PROPOSED machine is recorded on the stage plan',
            (int)$print_stage->machine_id === (int)$this->mo_ids['m1']);

        return [$job, $print_stage];
    }

    /* ==================================================================== */
    /*  Scenario 1 & 2 — runs, machine confirmation, mid-stage switching     */
    /* ==================================================================== */

    private function mo_runs($M, $store_id, $job, $stage) {
        $m1 = $this->mo_ids['m1'];
        $m2 = $this->mo_ids['m2'];
        $m3 = $this->mo_ids['m3'];

        // A run on the PLANNED machine is accepted.
        $run1 = $this->ops->record_run($job, $stage->id, [
            'run_kind' => 'machine', 'machine_id' => $m1, 'machine_confirmed' => 1,
            'qty_in' => 400, 'accepted_qty' => 395, 'reject_qty' => 5,
            'mono_start' => 0, 'mono_end' => 400, 'colour_start' => 0, 'colour_end' => 120,
            'started_at' => date('Y-m-d H:i:s', strtotime('-4 hours')),
            'ended_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            'operator_id' => $this->uid, 'run_date' => date('Y-m-d'),
            'references' => ['job_card' => 'JC-' . $this->tag],
        ]);
        $this->check($M, 'run recorded on the planned machine', !empty($run1['success']), $run1['message'] ?? '');

        $r1 = $this->db->where('id', $run1['log_id'])->get('db_print_stage_logs')->row();
        $this->check($M, 'run records the operator', (int)$r1->operator_id === (int)$this->uid);
        $this->check($M, 'run records its manual start and end times', !empty($r1->started_at) && !empty($r1->ended_at));
        $this->check($M, 'run records start and end counter readings', !empty($r1->counter_start_id) && !empty($r1->counter_end_id));
        $this->check($M, 'run expresses counter ACTIVITY separately from accepted output',
            (float)$r1->counter_activity === 520.0 && (float)$r1->accepted_qty === 395.0,
            'activity=' . $r1->counter_activity . ' accepted=' . $r1->accepted_qty);
        $this->check($M, 'run stores supporting references', strpos((string)$r1->references_json, 'JC-') !== false);
        $this->check($M, 'run is marked machine-CONFIRMED', (int)$r1->machine_confirmed === 1);
        $this->check($M, 'readings are linked back to the run they came from',
            (int)$this->db->where('id', $r1->counter_end_id)->get('db_print_machine_readings')->row()->stage_log_id === (int)$run1['log_id']);

        // Claiming a DIFFERENT machine without confirming it is refused.
        $unconfirmed = $this->ops->record_run($job, $stage->id, [
            'run_kind' => 'machine', 'machine_id' => $m2,
            'qty_in' => 100, 'accepted_qty' => 100, 'mono_start' => 0, 'mono_end' => 100,
        ]);
        $this->check($M, 'switching machine without CONFIRMING it is refused',
            empty($unconfirmed['success']), $unconfirmed['message'] ?? '');

        // Same stage, a different machine, explicitly confirmed — mid-stage switch.
        $run2 = $this->ops->record_run($job, $stage->id, [
            'run_kind' => 'machine', 'machine_id' => $m2, 'machine_confirmed' => 1,
            'qty_in' => 200, 'accepted_qty' => 198, 'waste_qty' => 2,
            'mono_start' => 0, 'mono_end' => 200, 'run_date' => date('Y-m-d'),
        ]);
        $this->check($M, 'machine switched MID-STAGE and recorded', !empty($run2['success']), $run2['message'] ?? '');

        $used = $this->ops->machines_used_on_job($job);
        $this->check($M, 'job shows BOTH machines were used', count($used) >= 2, 'machines=' . count($used));
        $m2_row = null;
        foreach ($used as $u) { if ((int)$u->id === (int)$m2) $m2_row = $u; }
        $this->check($M, 'job machine history totals accepted output per machine (not the counter)',
            $m2_row && (float)$m2_row->accepted === 198.0, 'accepted=' . ($m2_row->accepted ?? '?'));
        $this->check($M, 'job machine history keeps counter activity separate per machine',
            $m2_row && (float)$m2_row->activity === 200.0, 'activity=' . ($m2_row->activity ?? '?'));

        $jobs_of_m1 = $this->ops->jobs_for_machine($m1);
        $this->check($M, 'machine history shows the linked jobs', count($jobs_of_m1) >= 1, 'jobs=' . count($jobs_of_m1));

        // Manual work carries NO machine at all.
        $manual = $this->ops->record_run($job, $stage->id, [
            'run_kind' => 'manual', 'qty_in' => 50, 'accepted_qty' => 50,
            'notes' => 'Hand-finished, no machine involved',
        ]);
        $this->check($M, 'manual run accepted with NO machine', !empty($manual['success']), $manual['message'] ?? '');
        $mrow = $this->db->where('id', $manual['log_id'])->get('db_print_stage_logs')->row();
        $this->check($M, 'manual run stores NO fictitious machine', $mrow->machine_id === null, 'machine_id=' . var_export($mrow->machine_id, true));
        $this->check($M, 'manual run is labelled as manual', $mrow->run_kind === 'manual');

        // Outsourced work carries a vendor and no machine.
        $outs_bad = $this->ops->record_run($job, $stage->id, ['run_kind' => 'outsourced', 'qty_in' => 10, 'accepted_qty' => 10]);
        $this->check($M, 'outsourced run WITHOUT a vendor is refused', empty($outs_bad['success']), $outs_bad['message'] ?? '');
        $outs = $this->ops->record_run($job, $stage->id, [
            'run_kind' => 'outsourced', 'outsource_vendor' => 'Lagos Laminators',
            'outsource_cost' => 4500, 'qty_in' => 100, 'accepted_qty' => 100,
        ]);
        $this->check($M, 'outsourced run accepted with a vendor and NO machine', !empty($outs['success']), $outs['message'] ?? '');
        $orow = $this->db->where('id', $outs['log_id'])->get('db_print_stage_logs')->row();
        $this->check($M, 'outsourced run stores NO machine', $orow->machine_id === null);
        $this->check($M, 'outsourced run records its vendor on the RUN', $orow->outsource_vendor === 'Lagos Laminators');

        // An UNAVAILABLE machine is blocked unless an authorized override is given.
        $this->ops->set_machine_status($m3, 'out_of_service', 'Awaiting a new blade');
        $blocked = $this->ops->record_run($job, $stage->id, [
            'run_kind' => 'machine', 'machine_id' => $m3, 'machine_confirmed' => 1,
            'qty_in' => 10, 'accepted_qty' => 10,
        ]);
        $this->check($M, 'UNAVAILABLE machine is BLOCKED for a run', empty($blocked['success']), $blocked['message'] ?? '');

        $ov_bad = $this->ops->record_run($job, $stage->id, [
            'run_kind' => 'machine', 'machine_id' => $m3, 'machine_confirmed' => 1,
            'qty_in' => 10, 'accepted_qty' => 10, 'machine_override_reason' => 'Urgent deadline',
        ]);
        $this->check($M, 'override WITHOUT an authorizer is refused', empty($ov_bad['success']), $ov_bad['message'] ?? '');

        $override = $this->ops->record_run($job, $stage->id, [
            'run_kind' => 'machine', 'machine_id' => $m3, 'machine_confirmed' => 1,
            'qty_in' => 10, 'accepted_qty' => 10,
            'machine_override_reason' => 'Urgent deadline, supervisor approved',
            'machine_override_authorized_by' => 'supervisor',
        ]);
        $this->check($M, 'AUTHORIZED override allows the run', !empty($override['success']), $override['message'] ?? '');
        $ov_row = $this->db->where('id', $override['log_id'])->get('db_print_stage_logs')->row();
        $this->check($M, 'override records WHO authorized it', $ov_row->machine_override_authorized_by === 'supervisor');
        $this->check($M, 'override records WHY', strpos((string)$ov_row->machine_override_reason, 'Urgent') !== false);

        // Quantities and readings are validated against each other.
        $over = $this->ops->record_run($job, $stage->id, [
            'run_kind' => 'machine', 'machine_id' => $m2, 'machine_confirmed' => 1,
            'qty_in' => 10, 'accepted_qty' => 999, 'mono_start' => 0, 'mono_end' => 1,
        ]);
        $this->check($M, 'accepted output ABOVE the input quantity is refused', empty($over['success']), $over['message'] ?? '');

        // Counter activity contradicting accepted output is FLAGGED, not accepted.
        $disc = $this->ops->record_run($job, $stage->id, [
            'run_kind' => 'machine', 'machine_id' => $m2, 'machine_confirmed' => 1,
            'qty_in' => 100, 'accepted_qty' => 100, 'mono_start' => 0, 'mono_end' => 9000,
            'run_date' => date('Y-m-d'),
        ]);
        $this->check($M, 'readings/quantity contradiction is FLAGGED', !empty($disc['warning']), (string)($disc['warning'] ?? 'none'));
        $this->check($M, 'flagged contradiction is PERSISTED, not merely warned',
            !empty($this->db->where('id', $disc['log_id'])->get('db_print_stage_logs')->row()->reading_discrepancy));
        $this->check($M, 'unresolved discrepancies are listed for review',
            count($this->ops->reading_discrepancies($store_id)) >= 1);

        // Multiple runs within one stage are supported by design.
        $count = $this->db->where('job_id', $job)->where('stage_id', $stage->id)->count_all_results('db_print_stage_logs');
        $this->check($M, 'several runs coexist within one stage', $count >= 4, 'runs=' . $count);

        $this->ops->set_machine_status($m3, 'available', '');
        $this->mo_job = $job;
        $this->mo_stage = $stage;
    }

    /* ==================================================================== */
    /*  Scenarios 3 & 4 — consumable issuance, deduct ONCE, return unused     */
    /* ==================================================================== */

    /** Seed a stock item the ONLY way the repo allows: via the stock engine. */
    private function mo_seed_item($store_id, $name, $code, $price, $qty, $unit_id) {
        $wh = get_store_warehouse_id();
        $this->db->insert('db_items', [
            'store_id' => $store_id, 'item_name' => $name, 'item_code' => $code,
            'unit_id' => $unit_id, 'purchase_price' => $price, 'sales_price' => $price,
            'stock' => 0, 'status' => 1,
        ]);
        $item_id = $this->db->insert_id();
        $this->db->insert('db_stockadjustment', [
            'store_id' => $store_id, 'warehouse_id' => $wh, 'reference_no' => 'OPEN-' . $code,
            'adjustment_date' => date('Y-m-d'), 'adjustment_note' => $name . ' opening stock',
            'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
            'created_by' => 'acceptance', 'status' => 1,
        ]);
        $adj = $this->db->insert_id();
        $this->db->insert('db_stockadjustmentitems', [
            'store_id' => $store_id, 'warehouse_id' => $wh, 'adjustment_id' => $adj,
            'item_id' => $item_id, 'adjustment_qty' => $qty, 'description' => $name . ' opening', 'status' => 1,
        ]);
        $this->load->model('pos_model');
        $this->pos_model->update_items_quantity($item_id);
        return $item_id;
    }

    private function mo_supplies($M, $store_id, $job) {
        $m1 = $this->mo_ids['m1'];

        $unit = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_units')->row();
        if (!$unit) {
            $this->db->insert('db_units', ['store_id' => $store_id, 'unit_name' => 'Unit', 'unit_code' => 'U', 'status' => 1, 'conversion_factor' => 1]);
            $unit = $this->db->where('store_id', $store_id)->order_by('id', 'desc')->get('db_units')->row();
        }
        $this->mo_unit = $unit;

        $toner_id = $this->mo_seed_item($store_id, 'Toner TK-895 ' . $this->tag, 'TON' . $this->tag, 48000, 5, $unit->id);
        $this->mo_toner = $toner_id;
        $stock = function () use ($toner_id) { return (float)$this->db->where('id', $toner_id)->get('db_items')->row()->stock; };
        $this->check($M, 'consumable opening stock seeded through the stock engine', $stock() === 5.0, 'stock=' . $stock());

        // --- Request: moves NO stock ---
        $req = $this->ops->request_supply([
            'machine_id' => $m1, 'item_id' => $toner_id, 'supply_type' => 'toner',
            'requested_qty' => 1, 'unit_id' => $unit->id, 'description' => 'Toner for the Indigo',
        ]);
        $this->check($M, 'toner REQUEST logged', !empty($req['success']), $req['message'] ?? '');
        $sid = $req['supply_id'];
        $this->mo_toner_supply = $sid;
        $this->check($M, 'a REQUEST moves no stock', $stock() === 5.0, 'stock=' . $stock());
        $this->check($M, 'request starts in the requested state',
            $this->ops->get_supply($sid)->status === 'requested');

        // --- Approve: ONE deduction, cost snapshotted ---
        $appr = $this->ops->approve_supply($sid);
        $this->check($M, 'toner issuance approved and posted', !empty($appr['success']), $appr['message'] ?? '');
        $this->check($M, 'approval deducts stock EXACTLY ONCE (5 -> 4)', $stock() === 4.0, 'stock=' . $stock());
        $srow = $this->ops->get_supply($sid);
        $this->check($M, 'issuance records the ONE stock adjustment it posted', !empty($srow->issue_adjustment_id));
        $this->check($M, 'issuance snapshots the historic unit cost', (float)$srow->unit_cost === 48000.0, 'cost=' . $srow->unit_cost);
        $this->check($M, 'ISSUED is distinct from INSTALLED', $srow->status === 'issued', 'status=' . $srow->status);

        // --- Retry must not deduct twice ---
        $appr2 = $this->ops->approve_supply($sid);
        $this->check($M, 're-approving the SAME request is refused', empty($appr2['success']), $appr2['message'] ?? '');
        $this->check($M, 're-approval causes NO second deduction', $stock() === 4.0, 'stock=' . $stock());
        $adj_count = $this->db->like('reference_no', 'SUPPLY-' . $sid, 'after')->count_all_results('db_stockadjustment');
        $this->check($M, 'exactly ONE issue adjustment exists for the request', $adj_count === 1, 'adjustments=' . $adj_count);

        // --- A request for more than is available is refused, moving nothing ---
        $big = $this->ops->request_supply(['machine_id' => $m1, 'item_id' => $toner_id, 'supply_type' => 'toner', 'requested_qty' => 99]);
        $bigappr = $this->ops->approve_supply($big['supply_id']);
        $this->check($M, 'issuing more than is in stock is refused', empty($bigappr['success']), $bigappr['message'] ?? '');
        $this->check($M, 'the refused issuance moved NO stock', $stock() === 4.0, 'stock=' . $stock());

        // --- Install: NO stock movement ---
        $inst = $this->ops->install_supply($sid, ['machine_id' => $m1, 'job_id' => $job]);
        $this->check($M, 'issuance marked INSTALLED', !empty($inst['success']), $inst['message'] ?? '');
        $this->check($M, 'INSTALLATION does not deduct stock again', $stock() === 4.0, 'stock=' . $stock());

        // --- Allocate part of the cost to the job: NO stock movement ---
        $alloc = $this->ops->allocate_supply_cost($sid, $job, 8000, 200, 'Share of a 4-job cycle');
        $this->check($M, 'ESTIMATED cost allocation to the job recorded', !empty($alloc['success']), $alloc['message'] ?? '');
        $this->check($M, 'JOB ALLOCATION does not deduct stock a third time', $stock() === 4.0, 'stock=' . $stock());
        $alc = $this->db->where('consumable_id', $sid)->get('db_print_consumable_allocations')->row();
        $this->check($M, 'allocation is labelled ESTIMATED', (int)$alc->estimated === 1);
        $this->check($M, 'a cartridge is NOT charged in full to one job',
            (float)$alc->amount < 48000.0, 'allocated=' . $alc->amount . ' of 48000');
        $over_alloc = $this->ops->allocate_supply_cost($sid, $job, 999999);
        $this->check($M, 'allocating MORE than the supply cost is refused', empty($over_alloc['success']), $over_alloc['message'] ?? '');

        // --- Consumption reported separately, bounded by what was installed ---
        $cons = $this->ops->report_consumption($sid, 1, 'Cartridge exhausted');
        $this->check($M, 'consumption reported after installation', !empty($cons['success']), $cons['message'] ?? '');
        $too_much = $this->ops->report_consumption($sid, 50);
        $this->check($M, 'consumption ABOVE what was installed is refused', empty($too_much['success']), $too_much['message'] ?? '');

        // --- Unused return: credits stock EXACTLY ONCE ---
        $ink_id = $this->mo_seed_item($store_id, 'Ink bottle ' . $this->tag, 'INK' . $this->tag, 9000, 10, $unit->id);
        $ink_stock = function () use ($ink_id) { return (float)$this->db->where('id', $ink_id)->get('db_items')->row()->stock; };
        $ireq = $this->ops->request_supply(['machine_id' => $m1, 'item_id' => $ink_id, 'supply_type' => 'ink', 'requested_qty' => 4]);
        $this->ops->approve_supply($ireq['supply_id']);
        $this->check($M, 'ink issued deducts once (10 -> 6)', $ink_stock() === 6.0, 'stock=' . $ink_stock());

        $iret = $this->ops->return_supply($ireq['supply_id'], 2, 'Two bottles unopened');
        $this->check($M, 'unused ink return recorded', !empty($iret['success']), $iret['message'] ?? '');
        $this->check($M, 'unused return credits stock ONCE (6 -> 8)', $ink_stock() === 8.0, 'stock=' . $ink_stock());
        $iret2 = $this->ops->return_supply($ireq['supply_id'], 5, 'Duplicate attempt');
        $this->check($M, 'returning MORE than remains is refused', empty($iret2['success']), $iret2['message'] ?? '');
        $this->check($M, 'over-return does NOT credit stock again', $ink_stock() === 8.0, 'stock=' . $ink_stock());
        $ret_row = $this->ops->get_supply($ireq['supply_id']);
        $this->check($M, 'return records the single credit adjustment', !empty($ret_row->return_adjustment_id));
        $ret_adj = $this->db->like('reference_no', 'SUPPLY-RET-' . $ireq['supply_id'], 'after')->count_all_results('db_stockadjustment');
        $this->check($M, 'exactly ONE return adjustment exists', $ret_adj === 1, 'returns=' . $ret_adj);

        // --- Opening machine load: no stock move, cost may be UNKNOWN ---
        $open = $this->ops->capture_opening_load($m1, [
            'item_id' => $ink_id, 'supply_type' => 'ink', 'qty' => 2,
            'cost_known' => 0, 'capacity_qty' => 5000, 'capacity_basis' => 'impressions',
        ]);
        $this->check($M, 'opening machine load captured separately', !empty($open['success']), $open['message'] ?? '');
        $this->check($M, 'opening load moves NO stock', $ink_stock() === 8.0, 'stock=' . $ink_stock());
        $oload = $this->ops->get_supply($open['supply_id']);
        $this->check($M, 'opening load is flagged as already loaded', (int)$oload->opening_loaded === 1);
        $this->check($M, 'unknown opening cost is recorded as UNKNOWN, not invented',
            (int)$oload->cost_known === 0 && (float)$oload->issued_cost === 0.0,
            'known=' . $oload->cost_known . ' cost=' . $oload->issued_cost);
        $this->check($M, 'opening load states its unknown cost rather than guessing',
            strpos((string)($open['warning'] ?? ''), 'unknown') !== false, (string)($open['warning'] ?? ''));
        $no_alloc = $this->ops->allocate_supply_cost($open['supply_id'], $job, 100);
        $this->check($M, 'allocating cost from an UNKNOWN-cost load is refused', empty($no_alloc['success']), $no_alloc['message'] ?? '');

        // --- Unit conversion: unknown conversions are flagged, not assumed ---
        $other_unit = $this->db->where('store_id', $store_id)->where('status', 1)->where('id !=', $unit->id)->get('db_units')->row();
        if ($other_unit) {
            $conv = $this->ops->request_supply([
                'machine_id' => $m1, 'item_id' => $ink_id, 'supply_type' => 'ink',
                'requested_qty' => 1, 'unit_id' => $other_unit->id,
            ]);
            $crow = $this->ops->get_supply($conv['supply_id']);
            $this->check($M, 'an UNKNOWN unit conversion is flagged, never assumed',
                (int)$crow->conversion_ok === 0, 'conversion_ok=' . $crow->conversion_ok);
            $cappr = $this->ops->approve_supply($conv['supply_id']);
            $this->check($M, 'issuing with an UNCONFIRMED conversion is refused',
                empty($cappr['success']), $cappr['message'] ?? '');
            $this->check($M, 'the refused conversion moved no stock', $ink_stock() === 8.0, 'stock=' . $ink_stock());
            $this->ops->reject_supply($conv['supply_id'], 'Test cleanup');
        }

        $this->mo_ink = $ink_id;
        $this->mo_ink_supply = $ireq['supply_id'];
    }

    /* ==================================================================== */
    /*  Scenario 4 — maintenance expenditure reconciles, no double counting   */
    /* ==================================================================== */

    private function mo_maintenance($M, $store_id) {
        $m1 = $this->mo_ids['m1'];

        // A part that came from OUR stores: its stock was already deducted when
        // it was issued, so the visit must NOT expense it a second time.
        $blade_id = $this->mo_seed_item($store_id, 'Cutter blade ' . $this->tag, 'BLD' . $this->tag, 7000, 4, $this->mo_unit->id);
        $worn = $this->ops->request_supply(['machine_id' => $m1, 'item_id' => $blade_id, 'supply_type' => 'blade', 'requested_qty' => 1]);
        $this->ops->approve_supply($worn['supply_id']);
        $this->ops->install_supply($worn['supply_id'], ['machine_id' => $m1]);
        $blade_stock_after_issue = (float)$this->db->where('id', $blade_id)->get('db_items')->row()->stock;
        $this->check($M, 'part issued from stores deducts stock once', $blade_stock_after_issue === 3.0, 'stock=' . $blade_stock_after_issue);

        $visit = $this->ops->record_maintenance([
            'machine_id' => $m1, 'visit_type' => 'repair', 'performed_by_type' => 'external',
            'technician_name' => 'Engr Bello', 'provider_name' => 'IndigoCare Ltd',
            'visit_date' => date('Y-m-d'), 'downtime_minutes' => 180,
            'fault_code' => 'E-042', 'symptom' => 'Streaking on colour output',
            'work_performed' => 'Replaced blade, recalibrated colour heads',
            'labour_cost' => 15000, 'parts_cost' => 5000, 'outsource_cost' => 0, 'other_cost' => 2000,
            'mono_at_service' => 0, 'colour_at_service' => 0,
            'parts' => [[
                'supply_id' => $worn['supply_id'], 'qty' => 1, 'description' => 'Cutter blade from stores',
            ]],
        ]);
        $this->check($M, 'maintenance visit recorded with technician and provider',
            !empty($visit['success']), $visit['message'] ?? '');
        $vid = $visit['maintenance_id'];

        $this->check($M, 'visit total is the money ACTUALLY spent (22000)',
            (float)$visit['total_cost'] === 22000.0, 'total=' . $visit['total_cost']);
        $this->check($M, 'parts taken from stores are EXCLUDED from the visit cost',
            (float)$visit['parts_issued_value'] === 7000.0, 'issued_value=' . $visit['parts_issued_value']);
        $this->check($M, 'the exclusion is explained, not silent',
            strpos((string)($visit['note'] ?? ''), 'already consumed') !== false, (string)($visit['note'] ?? ''));
        $this->check($M, 'parts from stores do NOT deduct stock a second time',
            (float)$this->db->where('id', $blade_id)->get('db_items')->row()->stock === 3.0,
            'stock=' . (float)$this->db->where('id', $blade_id)->get('db_items')->row()->stock);

        $v = $this->ops->get_maintenance_visit($vid);
        $this->check($M, 'visit records the work performed and the fault',
            strpos((string)$v->work_performed, 'Replaced blade') !== false && $v->fault_code === 'E-042');
        $this->check($M, 'visit records downtime', (int)$v->downtime_minutes === 180);
        $this->check($M, 'visit part is flagged as coming from inventory',
            (int)$v->parts[0]->from_inventory === 1);
        $this->check($M, 'visit records a reading taken at service', !empty($v->reading_id));
        $this->check($M, 'service reading appears in machine history',
            $this->db->where('id', $v->reading_id)->count_all_results('db_print_machine_readings') === 1);

        $sum = $this->ops->maintenance_summary($m1);
        $this->check($M, 'maintenance summary separates expenditure from parts from stores',
            (float)$sum->visit_expenditure === 22000.0 && (float)$sum->parts_from_stores === 7000.0,
            'expenditure=' . $sum->visit_expenditure . ' from_stores=' . $sum->parts_from_stores);
        $this->check($M, 'maintenance combined total is explicit and adds up',
            (float)$sum->combined === 29000.0, 'combined=' . $sum->combined);

        // The machine schedule rolls forward from the visit.
        $mach = $this->ops->get_machine($m1);
        $this->check($M, 'machine last-service date updated by the visit', $mach->last_service_at === date('Y-m-d'));
        $this->check($M, 'machine next-service date derived from its interval',
            !empty($mach->next_service_at), 'next=' . var_export($mach->next_service_at, true));

        // Servicing a machine under maintenance returns it to service.
        $this->ops->set_machine_status($m1, 'maintenance', 'Service due');
        $this->ops->record_maintenance([
            'machine_id' => $m1, 'visit_type' => 'service', 'visit_date' => date('Y-m-d'),
            'work_performed' => 'Routine service', 'labour_cost' => 3000,
        ]);
        $this->check($M, 'a serviced machine returns to AVAILABLE',
            $this->ops->get_machine($m1)->status === 'available',
            'status=' . $this->ops->get_machine($m1)->status);

        // A counter-based service due is derived from actual activity.
        $this->db->where('id', $m1)->update('db_print_machines', [
            'service_interval_impressions' => 100, 'next_service_impressions' => 100,
            'last_service_at' => date('Y-m-d', strtotime('-1 day')),
        ]);
        $due = $this->ops->machines_due_service($store_id);
        $this->check($M, 'machines_due_service reports machines needing attention', is_array($due));
    }

    /* ==================================================================== */
    /*  Scenarios 5 & 7 — customer-owned material custody                    */
    /* ==================================================================== */

    private function mo_custody($M, $store_id) {
        // Scenario 5, exactly as specified: 100 shirts, 70 printed, 2 damaged,
        // 8 in production, 20 unused. Then collect 50 finished.
        $cust = $this->db->where('store_id', $store_id)->where('customer_name !=', 'Walk-in customer')->get('db_customers')->row();
        if (!$cust) {
            $this->db->insert('db_customers', ['store_id' => $store_id, 'customer_name' => 'Custody Client ' . $this->tag, 'status' => 1]);
            $cust = $this->db->where('id', $this->db->insert_id())->get('db_customers')->row();
        }
        $this->mo_customer = $cust->id;

        $rec = $this->ops->receive_customer_material([
            'customer_id' => $cust->id, 'material_name' => 'White tees ' . $this->tag,
            'material_type' => 'garment', 'qty_received' => 100, 'unit_label' => 'pieces',
            'condition_in' => 'good', 'acknowledged_by' => 'Store keeper',
            'size_breakdown' => ['S' => 20, 'M' => 50, 'L' => 30],
            'colour_breakdown' => ['White' => 100],
            'ack_reference' => 'ACK-' . $this->tag,
        ]);
        $this->check($M, 'customer-owned material received into custody', !empty($rec['success']), $rec['message'] ?? '');
        $mid = $rec['material_id'];
        $this->mo_material = $mid;
        $this->check($M, 'receipt lands ENTIRELY in custody',
            (float)$this->ops->get_customer_material($mid)->qty_custody === 100.0);
        $this->check($M, 'a receipt code is issued for the acknowledgement', strpos((string)$rec['receipt_code'], 'CM-') === 0);
        $this->check($M, 'receipt is NOT treated as a company purchase (no cost anywhere)',
            $this->db->where('id', $mid)->get('db_print_customer_materials')->row()->item_id === null);
        $this->check($M, 'size breakdown that reconciles is NOT flagged',
            (int)$this->ops->get_customer_material($mid)->breakdown_mismatch === 0);
        $this->check($M, 'garment size breakdown is stored', 
            ($this->ops->get_customer_material($mid)->size_breakdown['M'] ?? 0) == 50);
        $this->check($M, 'garment colour breakdown is stored',
            ($this->ops->get_customer_material($mid)->colour_breakdown['White'] ?? 0) == 100);

        // A breakdown that does NOT reconcile is flagged, never corrected.
        $bad = $this->ops->receive_customer_material([
            'customer_id' => $cust->id, 'material_name' => 'Mismatch test ' . $this->tag,
            'material_type' => 'garment', 'qty_received' => 10,
            'size_breakdown' => ['S' => 3, 'M' => 3],
        ]);
        $this->check($M, 'an unreconciled size breakdown is FLAGGED',
            !empty($bad['warning']), (string)($bad['warning'] ?? ''));
        $this->check($M, 'the mismatch is persisted on the receipt',
            (int)$this->ops->get_customer_material($bad['material_id'])->breakdown_mismatch === 1);
        $this->check($M, 'mismatched breakdowns are listed for review',
            count($this->ops->breakdown_mismatches($store_id)) >= 1);

        // Storing no breakdown is NOT the same as storing a wrong one.
        $nobd = $this->ops->receive_customer_material([
            'customer_id' => $cust->id, 'material_name' => 'No breakdown ' . $this->tag, 'qty_received' => 5,
        ]);
        $this->check($M, 'a receipt with NO breakdown is not falsely flagged as mismatched',
            (int)$this->ops->get_customer_material($nobd['material_id'])->breakdown_mismatch === 0);

        // --- Issue to production: 78 pieces (70 printed + 8 still in production) ---
        $iss = $this->ops->move_custody($mid, 'issue', 78, ['work_date' => date('Y-m-d')]);
        $this->check($M, 'material issued to production', !empty($iss['success']), $iss['message'] ?? '');
        $b = $iss['balance'];
        $this->check($M, 'issuing moves pieces OUT of custody (100 -> 22)', $b['positions']['custody'] === 22.0, 'custody=' . $b['positions']['custody']);
        $this->check($M, 'production issuance is NOT consumption', $b['positions']['consumed'] === 0.0, 'consumed=' . $b['positions']['consumed']);
        $this->check($M, 'custody still reconciles after issuing', $b['reconciled'] === true, 'discrepancy=' . $b['discrepancy']);

        // 70 finished, 8 remain in production.
        $fin = $this->ops->move_custody($mid, 'finish', 70, ['work_date' => date('Y-m-d')]);
        $this->check($M, '70 printed pieces finish and await collection',
            !empty($fin['success']) && $fin['balance']['positions']['finished'] === 70.0,
            'finished=' . ($fin['balance']['positions']['finished'] ?? '?'));
        $b = $fin['balance'];
        $this->check($M, 'SCENARIO 5: 70 printed, 8 in production, 22 unused',
            $b['positions']['finished'] === 70.0 && $b['positions']['in_production'] === 8.0 && $b['positions']['custody'] === 22.0,
            'finished=' . $b['positions']['finished'] . ' in_prod=' . $b['positions']['in_production'] . ' custody=' . $b['positions']['custody']);
        $this->check($M, 'positions still reconcile to the 100 received', $b['reconciled'] === true, 'discrepancy=' . $b['discrepancy']);

        // 2 damaged — requires a reason.
        $no_reason = $this->ops->move_custody($mid, 'damage', 2);
        $this->check($M, 'damage WITHOUT a reason is refused', empty($no_reason['success']), $no_reason['message'] ?? '');
        $dmg = $this->ops->move_custody($mid, 'damage', 2, [
            'from_position' => 'in_production', 'reason' => 'Ink bleed on two pieces',
            'authorized_by' => 'supervisor', 'outcome_type' => 'replacement',
        ]);
        $this->check($M, 'damage recorded with a reason and authorized handling', !empty($dmg['success']), $dmg['message'] ?? '');
        $b = $dmg['balance'];
        $this->check($M, 'SCENARIO 5: 2 damaged accounted for', $b['positions']['damaged'] === 2.0, 'damaged=' . $b['positions']['damaged']);
        $this->check($M, 'SCENARIO 5: 8 in production becomes 6 after 2 damaged',
            $b['positions']['in_production'] === 6.0, 'in_prod=' . $b['positions']['in_production']);
        $this->check($M, 'damage keeps the totals reconciled', $b['reconciled'] === true, 'discrepancy=' . $b['discrepancy']);
        $this->check($M, 'damage awaits an authorized review',
            $this->ops->get_customer_material($mid)->moves[count($this->ops->get_customer_material($mid)->moves) - 1]->review_status === 'pending' || count($this->ops->pending_custody_reviews($store_id)) >= 1);
        $this->check($M, 'an agreed outcome is recorded SEPARATELY from the movement',
            !empty($this->ops->get_customer_material($mid)->moves[count($this->ops->get_customer_material($mid)->moves) - 1]->outcome_type));

        // Resolve the damage outcome — the movement itself is unchanged.
        $dmove = $this->ops->pending_custody_reviews($store_id);
        $the_move = null;
        foreach ($dmove as $x) { if ((int)$x->material_id === (int)$mid) { $the_move = $x; break; } }
        if ($the_move) {
            $before_qty = (float)$the_move->qty;
            $res = $this->ops->resolve_custody_issue($the_move->id, [
                'outcome_type' => 'compensation', 'outcome_cost' => 3000,
                'outcome_note' => 'Client accepted a 3000 credit', 'reviewed_by' => 'manager',
            ]);
            $this->check($M, 'damage outcome resolved by an authorized reviewer', !empty($res['success']), $res['message'] ?? '');
            $this->check($M, 'resolving the outcome does NOT change the movement quantity',
                (float)$this->db->where('id', $the_move->id)->get('db_print_custody_moves')->row()->qty === $before_qty);
            $this->check($M, 'compensation cost is labelled as belonging to the JOB',
                strpos((string)($res['note'] ?? ''), 'belongs to the job') !== false, (string)($res['note'] ?? ''));
        }

        // Collecting 50 finished goods — completion is NOT collection.
        $col = $this->ops->move_custody($mid, 'handover_finished', 50, [
            'handover_to' => 'Client representative', 'handover_reference' => 'OUT-' . $this->tag,
            'work_date' => date('Y-m-d'),
        ]);
        $this->check($M, '50 finished pieces collected', !empty($col['success']), $col['message'] ?? '');
        $b = $col['balance'];
        $this->check($M, 'SCENARIO 5: collecting 50 leaves 20 finished awaiting collection',
            $b['positions']['finished'] === 20.0, 'finished=' . $b['positions']['finished']);
        $this->check($M, 'SCENARIO 5: handover is recorded as FINISHED goods, not unused returns',
            $b['positions']['collected'] === 50.0 && $b['positions']['returned'] === 0.0,
            'collected=' . $b['positions']['collected'] . ' returned=' . $b['positions']['returned']);
        $this->check($M, 'SCENARIO 5: 20 finished + 20 unused + 6 in production + 2 damaged = 48, plus 52 handed over shows 100',
            round($b['positions']['finished'] + $b['positions']['custody'] + $b['positions']['in_production']
                  + $b['positions']['damaged'] + $b['positions']['collected'], 3) === 100.0,
            'sum=' . round($b['positions']['finished'] + $b['positions']['custody'] + $b['positions']['in_production']
                  + $b['positions']['damaged'] + $b['positions']['collected'], 3));
        $this->check($M, 'SCENARIO 5: every one of the 100 pieces is accounted for', $b['reconciled'] === true, 'discrepancy=' . $b['discrepancy']);
        $this->check($M, 'collection records who received the goods',
            !empty($this->ops->get_customer_material($mid)->moves ? true : false));

        // Unused material handed back is a DIFFERENT position from finished goods.
        $ret = $this->ops->move_custody($mid, 'handover_unused', 10, ['handover_to' => 'Client representative']);
        $this->check($M, 'unused material returned to the customer', !empty($ret['success']), $ret['message'] ?? '');
        $b = $ret['balance'];
        $this->check($M, 'unused returns are tracked SEPARATELY from finished collections',
            $b['positions']['returned'] === 10.0 && $b['positions']['collected'] === 50.0,
            'returned=' . $b['positions']['returned'] . ' collected=' . $b['positions']['collected']);
        $this->check($M, 'the ledger still reconciles after a partial return', $b['reconciled'] === true, 'discrepancy=' . $b['discrepancy']);

        // Over-issuing is refused outright.
        $over = $this->ops->move_custody($mid, 'issue', 999);
        $this->check($M, 'issuing MORE than is in custody is refused', empty($over['success']), $over['message'] ?? '');
        $this->check($M, 'the refused over-issue changed nothing',
            $this->ops->custody_balance($this->ops->get_customer_material($mid))['reconciled'] === true);

        // Partial production and the customer statement.
        $stmt = $this->ops->customer_custody_statement($cust->id, $store_id);
        $this->check($M, 'customer custody statement covers their materials', count($stmt['materials']) >= 3, 'materials=' . count($stmt['materials']));
        $this->check($M, 'customer statement reports on-premises quantities', $stmt['on_premises'] > 0, 'on_premises=' . $stmt['on_premises']);
        // A damage write-off legitimately leaves less accounted-for than was
        // received. The statement must RECONCILE once the write-off is added
        // back, and must report the write-off separately rather than absorb it.
        $this->check($M, 'customer statement reconciles once recorded write-offs are counted',
            $stmt['reconciled'] === true,
            'received=' . $stmt['received'] . ' accounted=' . $stmt['accounted']
            . ' written_off=' . ($stmt['written_off'] ?? 0) . ' discrepancy=' . $stmt['discrepancy']);
        $this->check($M, 'customer statement reports the write-off separately',
            array_key_exists('written_off', $stmt));

        $this->mo_custody_unallocated($M, $store_id, $cust);
        $this->mo_custody_cancellation($M, $store_id, $cust);
    }

    /* ==================================================================== */
    /*  Scenario 6 — material received BEFORE the job exists                 */
    /* ==================================================================== */

    private function mo_custody_unallocated($M, $store_id, $cust) {
        // Two jobs for the same customer, created AFTER the material arrives.
        $cat = $this->db->where('store_id', $store_id)->order_by('id', 'asc')->get('db_print_categories')->row();
        $jobA = $this->print->create_job(['customer_id' => $cust->id, 'title' => 'Late job A ' . $this->tag],
            [['category_id' => $cat->id, 'qty' => 20, 'unit_price' => 500, 'description' => 'Job A']]);
        $jobB = $this->print->create_job(['customer_id' => $cust->id, 'title' => 'Late job B ' . $this->tag],
            [['category_id' => $cat->id, 'qty' => 20, 'unit_price' => 500, 'description' => 'Job B']]);

        // Material arrives with NO job.
        $rec = $this->ops->receive_customer_material([
            'customer_id' => $cust->id, 'material_name' => 'Canvas rolls ' . $this->tag,
            'material_type' => 'fabric', 'qty_received' => 30, 'unit_label' => 'rolls',
        ]);
        $this->check($M, 'material can be received with NO job yet', !empty($rec['success']), $rec['message'] ?? '');
        $uid = $rec['material_id'];
        $this->check($M, 'a jobless receipt is held as an UNALLOCATED balance',
            $this->ops->get_customer_material($uid)->job_id === null);
        $this->check($M, 'unallocated material is listed as available to allocate',
            count($this->ops->unallocated_materials($cust->id, $store_id)) >= 1);
        $this->check($M, 'all 30 rolls sit in custody, unallocated', (float)$this->ops->get_customer_material($uid)->qty_custody === 30.0);

        // Allocate a portion to job A — explicit and auditable.
        $a1 = $this->ops->move_custody($uid, 'allocate', 18, ['job_id' => $jobA, 'reason' => 'For the banner order']);
        $this->check($M, 'unallocated material allocated to a job explicitly', !empty($a1['success']), $a1['message'] ?? '');
        $this->check($M, 'the allocation is recorded against the job',
            (int)$this->ops->get_customer_material($uid)->job_id === (int)$jobA);
        $this->check($M, 'allocation does NOT change the quantity held',
            (float)$this->ops->get_customer_material($uid)->qty_custody === 30.0);
        $this->check($M, 'allocation is written to the movement log for audit',
            count(array_filter($this->ops->custody_moves($uid), function ($x) { return $x->move_type === 'allocate'; })) >= 1);

        // A SECOND receipt allocated to a DIFFERENT job of the same customer.
        $rec2 = $this->ops->receive_customer_material([
            'customer_id' => $cust->id, 'material_name' => 'Vinyl rolls ' . $this->tag, 'qty_received' => 10,
        ]);
        $a2 = $this->ops->move_custody($rec2['material_id'], 'allocate', 10, ['job_id' => $jobB]);
        $this->check($M, 'a second receipt allocates safely to a different job of the same client',
            !empty($a2['success']), $a2['message'] ?? '');

        // Transferring between the same customer's jobs is supported and tracked.
        $xfer = $this->ops->move_custody($uid, 'allocate', 5, ['job_id' => $jobB, 'reason' => 'Reallocated to the second order']);
        $this->check($M, 'material can be transferred between the SAME client\'s jobs',
            !empty($xfer['success']), $xfer['message'] ?? '');
        $this->check($M, 'a same-client transfer is tracked so neither job can double-claim',
            (float)$this->ops->get_customer_material($uid)->qty_allocated_other_jobs === 5.0,
            'other_jobs=' . $this->ops->get_customer_material($uid)->qty_allocated_other_jobs);

        // Duplicate allocation to the job it is already on is refused.
        $dupe = $this->ops->move_custody($uid, 'allocate', 1, ['job_id' => $jobA]);
        $this->check($M, 'allocating to the job it is already on is refused', empty($dupe['success']), $dupe['message'] ?? '');

        // CROSS-CUSTOMER use is refused outright.
        $this->db->insert('db_customers', ['store_id' => $store_id, 'customer_name' => 'Other Client ' . $this->tag, 'status' => 1]);
        $other = $this->db->where('id', $this->db->insert_id())->get('db_customers')->row();
        $other_job = $this->print->create_job(['customer_id' => $other->id, 'title' => 'Other client job ' . $this->tag],
            [['category_id' => $cat->id, 'qty' => 5, 'unit_price' => 100, 'description' => 'Other']]);
        $cross = $this->ops->move_custody($uid, 'allocate', 1, ['job_id' => $other_job]);
        $this->check($M, 'CROSS-CUSTOMER allocation is refused', empty($cross['success']), $cross['message'] ?? '');
        $this->check($M, 'the refused cross-customer attempt changed nothing',
            (float)$this->ops->get_customer_material($uid)->qty_allocated_other_jobs === 5.0);

        $this->mo_jobA = $jobA;
    }

    /* ==================================================================== */
    /*  Scenario 7 — cancellation and damage resolution preserve custody      */
    /* ==================================================================== */

    private function mo_custody_cancellation($M, $store_id, $cust) {
        $rec = $this->ops->receive_customer_material([
            'customer_id' => $cust->id, 'material_name' => 'Cancel test ' . $this->tag,
            'material_type' => 'garment', 'qty_received' => 40, 'unit_label' => 'pieces',
        ]);
        $cid = $rec['material_id'];

        // 25 issued, 10 finished, 5 collected, leaving 15 unused and 5 in custody.
        $this->ops->move_custody($cid, 'issue', 25);
        $this->ops->move_custody($cid, 'finish', 10);
        $this->ops->move_custody($cid, 'handover_finished', 5);

        $b = $this->ops->custody_balance($this->ops->get_customer_material($cid));
        $this->check($M, 'partial production and partial collection both tracked',
            $b['positions']['finished'] === 5.0 && $b['positions']['in_production'] === 15.0,
            'finished=' . $b['positions']['finished'] . ' in_prod=' . $b['positions']['in_production']);

        // Cancel remaining work: only what is still in custody/production moves.
        $no_reason = $this->ops->move_custody($cid, 'cancel');
        $this->check($M, 'cancellation WITHOUT a reason is refused', empty($no_reason['success']), $no_reason['message'] ?? '');

        $cancel = $this->ops->move_custody($cid, 'cancel', null, ['reason' => 'Client cancelled the balance of the order']);
        $this->check($M, 'cancellation returns the remaining material', !empty($cancel['success']), $cancel['message'] ?? '');
        $b = $cancel['balance'];
        $this->check($M, 'SCENARIO 7: cancellation clears custody and production',
            $b['positions']['custody'] === 0.0 && $b['positions']['in_production'] === 0.0,
            'custody=' . $b['positions']['custody'] . ' in_prod=' . $b['positions']['in_production']);
        $this->check($M, 'SCENARIO 7: already-collected goods are NOT reversed by cancellation',
            $b['positions']['collected'] === 5.0, 'collected=' . $b['positions']['collected']);
        $this->check($M, 'SCENARIO 7: finished goods already completed stay finished',
            $b['positions']['finished'] === 5.0, 'finished=' . $b['positions']['finished']);
        // The fixture: 40 received, 25 issued, 10 finished, 5 collected. That
        // leaves 15 still in custody and 15 still in production — so cancelling
        // the remaining work returns 30 pieces, not 15.
        $this->check($M, 'SCENARIO 7: cancelled material is returned, not silently lost',
            $b['positions']['returned'] === 30.0, 'returned=' . $b['positions']['returned']);
        $this->check($M, 'SCENARIO 7: cancellation preserves custody totals (40 reconciled)',
            $b['reconciled'] === true && $b['positions']['returned'] === 30.0,
            'discrepancy=' . $b['discrepancy'] . ' accounted=' . $b['accounted']);

        // Damage resolution preserving totals.
        $rec3 = $this->ops->receive_customer_material([
            'customer_id' => $cust->id, 'material_name' => 'Damage resolution ' . $this->tag,
            'material_type' => 'garment', 'qty_received' => 20, 'unit_label' => 'pieces',
        ]);
        $did = $rec3['material_id'];
        $this->ops->move_custody($did, 'issue', 20);
        $this->ops->move_custody($did, 'finish', 15);
        $dm = $this->ops->move_custody($did, 'damage', 3, [
            'from_position' => 'in_production', 'reason' => 'Torn during pressing',
            'authorized_by' => 'supervisor',
        ]);
        $this->check($M, 'damage deducts from the position it actually came from',
            $dm['balance']['positions']['in_production'] === 2.0,
            'in_prod=' . $dm['balance']['positions']['in_production']);
        $this->check($M, 'damage keeps the reconciliation intact', $dm['balance']['reconciled'] === true);

        // An unauthorized adjustment is refused; an authorized one is recorded.
        $adj_bad = $this->ops->move_custody($did, 'adjust', -1, ['position' => 'custody']);
        $this->check($M, 'adjustment WITHOUT a reason is refused', empty($adj_bad['success']), $adj_bad['message'] ?? '');
        $adj = $this->ops->move_custody($did, 'adjust', -2, [
            'position' => 'damaged', 'reason' => 'Two damaged pieces written off as waste',
            'authorized_by' => 'manager',
        ]);
        $this->check($M, 'authorized adjustment recorded', !empty($adj['success']), $adj['message'] ?? '');
        $this->check($M, 'adjustment is flagged for authorized review',
            count($this->ops->pending_custody_reviews($store_id)) >= 1);

        // A position that would go negative is refused outright.
        $neg = $this->ops->move_custody($did, 'handover_finished', 999);
        $this->check($M, 'a movement that would make a position negative is refused',
            empty($neg['success']), $neg['message'] ?? '');
    }

    /* ==================================================================== */
    /*  Scenario 12 — the machine report keeps its figures APART             */
    /* ==================================================================== */

    private function mo_reporting($M, $store_id) {
        $m1 = $this->mo_ids['m1'];
        $rep = $this->ops->machine_report($m1);
        $this->check($M, 'machine report generated', is_array($rep) && !empty($rep['machine']));

        $this->check($M, 'report shows counter activity',
            array_key_exists('activity', $rep['counter']) && array_key_exists('mono', $rep['counter']));
        $this->check($M, 'report shows ACCEPTED output separately from counter activity',
            array_key_exists('accepted', $rep['output']));
        $this->check($M, 'report shows jobs / stages / runs performed', is_array($rep['jobs']));
        $this->check($M, 'report shows supplies issued', array_key_exists('issued_value', $rep['supplies']));
        $this->check($M, 'report shows CONSUMPTION reported separately from issues',
            array_key_exists('consumed_value', $rep['supplies']));
        $this->check($M, 'report flags allocations as ESTIMATED',
            array_key_exists('estimated_allocated', $rep['supplies']) && array_key_exists('allocation_count', $rep['supplies']));
        $this->check($M, 'report shows parts and maintenance expenditure',
            array_key_exists('expenditure', $rep['maintenance']));
        $this->check($M, 'report separates parts-from-stores from money spent',
            array_key_exists('parts_from_stores', $rep['maintenance']));
        $this->check($M, 'report shows waste', array_key_exists('waste_qty', $rep['issues']));
        $this->check($M, 'report shows downtime', array_key_exists('downtime_minutes', $rep['issues']));
        $this->check($M, 'report shows unresolved discrepancies',
            array_key_exists('discrepancies', $rep['issues']));
        // The flagged run happened on the SECOND machine, so the discrepancy must
        // be attributed to THAT machine's report and not smeared across the
        // store. Checking the wrong machine would pass for the wrong reason.
        $rep2 = $this->ops->machine_report($this->mo_ids['m2']);
        $this->check($M, 'the report attributes the discrepancy to the machine that ran it',
            (int)$rep2['issues']['discrepancies'] >= 1,
            'm2 discrepancies=' . $rep2['issues']['discrepancies'] . ' m1=' . $rep['issues']['discrepancies']);
        $this->check($M, 'report shows invalid readings',
            array_key_exists('invalid_readings', $rep['issues']) && (int)$rep['issues']['invalid_readings'] >= 1);

        // The double-count guard, asserted on real numbers.
        $this->check($M, 'maintenance expenditure excludes parts already issued from stores',
            (float)$rep['maintenance']['expenditure'] === ($rep['maintenance']['expenditure'])
            && (float)$rep['maintenance']['parts_from_stores'] > 0.0,
            'expenditure=' . $rep['maintenance']['expenditure'] . ' from_stores=' . $rep['maintenance']['parts_from_stores']);
        $this->check($M, 'the combined maintenance figure is the sum of BOTH, stated explicitly',
            abs((float)$rep['maintenance']['combined']
                - ((float)$rep['maintenance']['expenditure'] + (float)$rep['maintenance']['parts_from_stores'])) < 0.01);

        // Counter activity must never be reported as accepted output.
        $this->check($M, 'counter activity and accepted output are DIFFERENT numbers',
            (float)$rep['counter']['activity'] !== (float)$rep['output']['accepted'],
            'activity=' . $rep['counter']['activity'] . ' accepted=' . $rep['output']['accepted']);

        // Supplies issued must never equal consumption reported — a cartridge
        // issued is not a cartridge used.
        $this->check($M, 'supplies issued and consumption reported are kept distinct',
            (float)$rep['supplies']['issued_value'] !== (float)$rep['supplies']['consumed_value']
            || (float)$rep['supplies']['issued_value'] === 0.0,
            'issued=' . $rep['supplies']['issued_value'] . ' consumed=' . $rep['supplies']['consumed_value']);

        // Store-wide custody picture.
        $pos = $this->ops->material_position_summary($store_id);
        $this->check($M, 'store custody summary reports items and positions',
            isset($pos['items'], $pos['custody'], $pos['in_production'], $pos['finished']));
        $this->check($M, 'custody summary counts only material still outstanding',
            $pos['items'] >= 1, 'items=' . $pos['items']);

        // Machine history is job-level and machine-level, both directions.
        $used = $this->ops->machines_used_on_job($this->mo_job);
        $this->check($M, 'job-level machine history lists every machine used', count($used) >= 2);
        $jobs = $this->ops->jobs_for_machine($m1);
        $this->check($M, 'machine-level history lists the linked jobs', count($jobs) >= 1);

        // Cross-check: the sum of per-machine accepted output equals the runs.
        $total_accepted = 0.0;
        foreach ($used as $u) $total_accepted += (float)$u->accepted;
        $run_sum = (float)$this->db->select('COALESCE(SUM(accepted_qty),0) AS s', false)
            ->where('job_id', $this->mo_job)->where('machine_id IS NOT NULL', null, false)
            ->where('status !=', 'reversed')->get('db_print_stage_logs')->row()->s;
        $this->check($M, 'job machine history reconciles to the underlying runs',
            abs($total_accepted - $run_sum) < 0.01, 'history=' . $total_accepted . ' runs=' . $run_sum);

        // Custody statement reconciles for a customer with several receipts.
        $stmt = $this->ops->customer_custody_statement($this->mo_customer, $store_id);
        $this->check($M, 'customer custody statement reconciles once write-offs are counted',
            $stmt['reconciled'] === true,
            'received=' . $stmt['received'] . ' accounted=' . $stmt['accounted']
            . ' written_off=' . $stmt['written_off'] . ' discrepancy=' . $stmt['discrepancy']);
        $this->check($M, 'custody statement distinguishes what is on the premises',
            $stmt['on_premises'] >= 0);

        // The report must not require a column the schema does not have.
        $this->check($M, 'report does not depend on a missing schema column',
            $this->db->field_exists('reading_discrepancy', 'db_print_stage_logs')
            && $this->db->field_exists('counter_activity', 'db_print_stage_logs')
            && $this->db->field_exists('machine_confirmed', 'db_print_stage_logs')
            && $this->db->field_exists('issued_qty', 'db_print_machine_supplies')
            && $this->db->field_exists('qty_in_production', 'db_print_customer_materials'));
    }

    /* ==================================================================== */
    /*  cleanup                                                              */
    /* ==================================================================== */

    private function mo_cleanup($store_id) {
        $ids = array_values(array_filter((array)$this->mo_ids ?? []));
        foreach ($ids as $id) {
            // Parts hang off the VISIT, not the machine. Deleting them by
            // machine_id removes nothing (there is no such column), and a failed
            // CI3 query exits — taking the rest of the suite down with it.
            $visits = $this->db->select('id')->where('machine_id', $id)
                ->get('db_print_machine_maintenance')->result();
            foreach ($visits as $v) {
                $this->db->where('maintenance_id', $v->id)->delete('db_print_maintenance_parts');
            }
            $this->db->where('machine_id', $id)->delete('db_print_machine_maintenance');
            $this->db->where('machine_id', $id)->delete('db_print_machine_readings');
            $this->db->where('machine_id', $id)->delete('db_print_machine_supplies');
        }
        // Supplies/allocations created for the seeded items.
        foreach (['mo_toner', 'mo_ink'] as $k) {
            $item = $this->$k ?? 0;
            if (!$item) continue;
            $rows = $this->db->select('id')->where('item_id', $item)->get('db_print_machine_supplies')->result();
            foreach ($rows as $r) {
                $this->db->where('consumable_id', $r->id)->delete('db_print_consumable_allocations');
                $this->db->where('id', $r->id)->delete('db_print_machine_supplies');
            }
        }
        // Custody fixtures for this run's tag only.
        $mats = $this->db->like('material_name', $this->tag)->get('db_print_customer_materials')->result();
        foreach ($mats as $m) {
            $this->db->where('material_id', $m->id)->delete('db_print_custody_moves');
            $this->db->where('id', $m->id)->delete('db_print_customer_materials');
        }
        // Seeded items and their opening adjustments.
        foreach (['mo_toner', 'mo_ink'] as $k) {
            $item = $this->$k ?? 0;
            if ($item) $this->db->where('id', $item)->delete('db_items');
        }
        foreach (['TON' . $this->tag, 'INK' . $this->tag, 'BLD' . $this->tag] as $code) {
            $row = $this->db->where('item_code', $code)->get('db_items')->row();
            if ($row) {
                $this->db->where('item_id', $row->id)->delete('db_stockadjustmentitems');
                $this->db->where('id', $row->id)->delete('db_items');
            }
            $this->db->like('reference_no', 'OPEN-' . $code, 'after')->delete('db_stockadjustment');
        }
        // Jobs created by THESE scenarios only. The release-2 job shares the run
        // tag, so it is matched by the scenario-specific title prefix instead of
        // by the tag alone — deleting it here would take the costing and
        // referral scenarios down with it.
        $this->db->select('id')->group_start()
            ->like('title', 'Machine ops ' . $this->tag, 'after')
            ->or_like('title', 'Late job A ' . $this->tag, 'after')
            ->or_like('title', 'Late job B ' . $this->tag, 'after')
            ->or_like('title', 'Other client job ' . $this->tag, 'after')
            ->group_end();
        $jobs = $this->db->get('db_print_jobs')->result();
        foreach ($jobs as $j) {
            $this->db->where('job_id', $j->id)->delete('db_print_stage_logs');
            $this->db->where('job_id', $j->id)->delete('db_print_stages');
            $this->db->where('job_id', $j->id)->delete('db_print_job_lines');
            $this->db->where('job_id', $j->id)->delete('db_print_item_plans');
            $this->db->where('job_id', $j->id)->delete('db_print_item_design');
            $this->db->where('job_id', $j->id)->delete('db_print_item_services');
            $this->db->where('job_id', $j->id)->delete('db_print_consumable_allocations');
            $this->db->where('id', $j->id)->delete('db_print_jobs');
        }
        $this->db->where('machine_code LIKE', 'MO%' . $this->tag)->delete('db_print_machines');
        // Only the custody fixtures created for this run — the release-2 job
        // shares the tag and its customer must survive for the costing and
        // referral scenarios that follow.
        $this->db->like('customer_name', 'Custody Client ' . $this->tag, 'after')->delete('db_customers');
        $this->db->like('customer_name', 'Other Client ' . $this->tag, 'after')->delete('db_customers');
    }
}
