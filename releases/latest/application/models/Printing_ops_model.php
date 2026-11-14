<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Printing — machines, production runs, machine supplies, maintenance and
 * customer-owned material custody.
 *
 * This model extends the print-shop engine with the physical side of the
 * business. It deliberately does NOT own a stock engine: every stock movement
 * it needs is delegated to Printing_model::post_stock_moves(), which is the one
 * posting method already guarded against double deduction by
 * input_is_ledger_managed(). Nothing here deducts stock a second time.
 *
 * Invariants enforced here (each one has cost real money to learn):
 *
 *   Counter activity is NOT accepted output.
 *     A machine's counter difference says the machine turned over. It says
 *     nothing about how much of that was saleable. They are separate columns and
 *     separate sums.
 *
 *   Issued is NOT installed, and installed is NOT consumed.
 *     A cartridge that leaves the store is issued. A cartridge put into a
 *     machine is installed. How much has actually been used is consumed.
 *     Charging a whole cartridge to whichever job was running at replacement
 *     time is exactly the error this model exists to prevent.
 *
 *   Production issuance is NOT consumption, and completion is NOT collection.
 *     Customer-owned goods move between positions; each position is a real
 *     holding and the arithmetic must always reconcile.
 *
 *   Opening readings are a BASELINE.
 *     They must never create historical output or cost.
 *
 *   A correction never rewrites history.
 *     Correcting a reading writes a new row and supersedes the old one; the old
 *     value stays readable forever.
 */
class Printing_ops_model extends CI_Model {

    /** Reading types that REQUIRE a reason and an authorized handler. */
    const PRIVILEGED_READINGS = ['correction', 'reset', 'replacement'];

    /** Reading types whose delta must never be read as activity. */
    const NON_ACTIVITY_READINGS = ['opening', 'reset', 'replacement', 'correction'];

    /** Custody positions and the column that holds each one. */
    private $position_column = [
        'custody'       => 'qty_custody',
        'in_production' => 'qty_in_production',
        'finished'      => 'qty_finished',
        'damaged'       => 'qty_damaged',
        'returned'      => 'qty_returned',
        'collected'     => 'qty_collected_finished',
        'consumed'      => 'qty_consumed',
        'other_jobs'    => 'qty_allocated_other_jobs',
    ];

    public function __construct() {
        parent::__construct();
        $this->load->model('printing_model', 'print');
    }

    /* ==================================================================== */
    /*  vocabularies                                                        */
    /* ==================================================================== */

    public static function machine_statuses() {
        return [
            'available'      => 'Available',
            'maintenance'    => 'Under maintenance',
            'out_of_service' => 'Out of service',
        ];
    }

    public static function machine_categories() {
        return [
            'press'        => 'Press (offset / litho)',
            'digital'      => 'Digital press',
            'large_format' => 'Large format',
            'cutting'      => 'Cutting / die-cutting',
            'finishing'    => 'Finishing',
            'binding'      => 'Binding',
            'other'        => 'Other',
        ];
    }

    public static function reading_modes() {
        return [
            'required'    => 'Readings required',
            'optional'    => 'Readings optional',
            'unavailable' => 'No counters',
        ];
    }

    public static function reading_types() {
        return [
            'opening'     => 'Opening reading (baseline)',
            'run'         => 'Run reading',
            'service'     => 'Reading at service',
            'correction'  => 'Correction',
            'reset'       => 'Counter reset',
            'replacement' => 'Counter replaced',
            'manual'      => 'Manual reading',
        ];
    }

    public static function run_kinds() {
        return [
            'machine'    => 'Machine run',
            'manual'     => 'Manual work (no machine)',
            'outsourced' => 'Outsourced (no machine)',
        ];
    }

    public static function supply_types() {
        return [
            'ink'       => 'Ink',
            'toner'     => 'Toner',
            'printhead' => 'Printhead',
            'drum'      => 'Drum',
            'blade'     => 'Blade',
            'fuser'     => 'Fuser',
            'part'      => 'Replacement part',
            'other'     => 'Other',
        ];
    }

    public static function custody_positions() {
        return [
            'custody'       => 'Unused in custody',
            'in_production' => 'Issued to production / in progress',
            'finished'      => 'Processed, awaiting collection',
            'damaged'       => 'Damaged / rejected',
            'returned'      => 'Handed over — unused material',
            'collected'     => 'Handed over — finished goods',
            'consumed'      => 'Consumed by production',
            'other_jobs'    => 'Transferred to another job of the same client',
        ];
    }

    /* ==================================================================== */
    /*  SECTION 1 — machine register                                        */
    /* ==================================================================== */

    public function get_machines($store_id = null, array $f = []) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $this->db->where('store_id', $store_id);
        if (!empty($f['status'])) $this->db->where('status', $f['status']);
        if (!empty($f['category'])) $this->db->where('machine_category', $f['category']);
        if (!empty($f['branch_id'])) $this->db->where('branch_id', (int)$f['branch_id']);
        if (empty($f['include_retired'])) $this->db->where('status_active', 1);
        if (!empty($f['search'])) {
            $q = $f['search'];
            $this->db->group_start()
                ->like('name', $q)->or_like('machine_code', $q)
                ->or_like('model', $q)->or_like('serial_no', $q)
                ->group_end();
        }
        return $this->db->order_by('machine_code', 'asc')->get('db_print_machines')->result();
    }

    public function get_machine($id) {
        $m = $this->db->where('id', $id)->get('db_print_machines')->row();
        if (!$m) return null;
        $m->supported_stages = !empty($m->supported_stages_json)
            ? json_decode($m->supported_stages_json, true) : [];
        if (!is_array($m->supported_stages)) $m->supported_stages = [];
        return $m;
    }

    /**
     * Machines that can perform a stage. Optionally require them to be
     * available — this builds the "pick the machine you actually ran on" list
     * and drives the explanation for why an unavailable machine needs an
     * override.
     */
    public function machines_for_stage($store_id, $stage_key, $available_only = false) {
        $rows = $this->db->where('store_id', $store_id)->where('status_active', 1)->get('db_print_machines')->result();
        $out = [];
        foreach ($rows as $m) {
            $stages = !empty($m->supported_stages_json) ? json_decode($m->supported_stages_json, true) : [];
            if (!is_array($stages) || !in_array($stage_key, $stages, true)) continue;
            if ($available_only && $m->status !== 'available') continue;
            $out[] = $m;
        }
        return $out;
    }

    /**
     * Validate a counter pair against what the machine supports.
     * Returns server-computed deltas — a client-supplied delta is never trusted.
     */
    public function compute_reading($machine_id, $mono, $colour, $opts = []) {
        $m = $this->get_machine($machine_id);
        if (!$m) return ['success' => false, 'message' => 'Machine not found.'];

        $mode = (string)$m->reading_mode;
        $is_priv = in_array((string)($opts['reading_type'] ?? 'run'), self::PRIVILEGED_READINGS, true);

        if ($mode === 'unavailable' && !$is_priv) {
            return ['success' => false, 'message' => $this->machine_label($m) . ' has no counters. Record the run as manual work, or change the machine\'s counter setting.'];
        }
        if ($mode === 'required' && ($mono === null || $mono === '') && ($colour === null || $colour === '')) {
            return ['success' => false, 'message' => $this->machine_label($m) . ' requires a counter reading for every run.'];
        }
        if (!$m->has_colour_counter && $colour !== null && $colour !== '') {
            return ['success' => false, 'message' => $this->machine_label($m) . ' has no separate colour counter.'];
        }

        $mono = ($mono === null || $mono === '') ? null : (float)$mono;
        $colour = ($colour === null || $colour === '') ? null : (float)$colour;
        if (($mono !== null && $mono < 0) || ($colour !== null && $colour < 0)) {
            return ['success' => false, 'message' => 'A counter reading cannot be negative.'];
        }

        // The previous live reading. Append-only, so a correction chain always
        // resolves to whatever is currently 'recorded'.
        $prev = $this->db->where('machine_id', $machine_id)
            ->where('status', 'recorded')
            ->order_by('reading_date', 'desc')->order_by('id', 'desc')
            ->limit(1)->get('db_print_machine_readings')->row();

        $out = [
            'success'              => true,
            'prev'                 => $prev,
            'prev_reading_id'      => $prev ? (int)$prev->id : null,
            'mono_delta'           => null,
            'colour_delta'         => null,
            'delta'                => null,
            'delta_valid'          => 0,
            'delta_invalid_reason' => null,
            'opening_baseline'     => 0,
            'suggested_mono'       => ($prev && $prev->mono_reading !== null) ? (float)$prev->mono_reading : null,
            'suggested_colour'     => ($prev && $prev->colour_reading !== null) ? (float)$prev->colour_reading : null,
        ];

        if (!$prev) {
            $out['delta_invalid_reason'] = 'No previous reading — this is the opening baseline.';
            return $out;
        }

        // A non-activity reading type never produces a delta that may be summed.
        if (in_array((string)($opts['reading_type'] ?? 'run'), self::NON_ACTIVITY_READINGS, true)) {
            $out['delta_valid'] = 0;
            $out['delta_invalid_reason'] = 'A "' . str_replace('_', ' ', (string)($opts['reading_type'] ?? '')) . '" reading is not machine activity.';
            return $out;
        }

        $rollover = ($m->counter_rollover_at !== null && (float)$m->counter_rollover_at > 0)
            ? (float)$m->counter_rollover_at : null;

        $invalid_reason = null;
        $diff = function ($now, $before) use ($rollover, &$invalid_reason) {
            if ($now === null || $before === null) return null;
            $d = $now - $before;
            if ($d < 0) {
                // A backwards counter is EITHER a genuine rollover or an error.
                // A rollover is only accepted when the machine declares one.
                if ($rollover !== null) {
                    return ($rollover - $before) + $now;
                }
                $invalid_reason = 'Reading is lower than the previous one and this machine declares no counter rollover. Use "Counter reset" or "Correction" with a reason.';
                return null;
            }
            return $d;
        };

        $md = $diff($mono, $prev->mono_reading !== null ? (float)$prev->mono_reading : null);
        $cd = $diff($colour, $prev->colour_reading !== null ? (float)$prev->colour_reading : null);
        $out['mono_delta'] = $md;
        $out['colour_delta'] = $cd;
        if ($invalid_reason !== null) {
            $out['delta_valid'] = 0;
            $out['delta_invalid_reason'] = $invalid_reason;
        } elseif ($md !== null || $cd !== null) {
            // A comparable previous reading existed and the difference is sane,
            // so this delta IS real machine activity. Without this the counter
            // reported zero forever, silently discarding every genuine run.
            $out['delta_valid'] = 1;
        }
        if ($md !== null || $cd !== null) {
            $out['delta'] = (float)($md ?? 0) + (float)($cd ?? 0);
        }
        return $out;
    }

    /**
     * Record a counter reading. Append-only.
     *
     * A correction supersedes the row it corrects and writes a NEW row; it never
     * edits or deletes. Privileged reading types require a reason and an
     * authorized handler.
     */
    public function record_reading($machine_id, array $d) {
        $m = $this->get_machine($machine_id);
        if (!$m) return ['success' => false, 'message' => 'Machine not found.'];
        $store_id = (int)$m->store_id;

        $type = (string)($d['reading_type'] ?? 'run');
        if (!array_key_exists($type, self::reading_types())) {
            return ['success' => false, 'message' => 'Unknown reading type.'];
        }
        $reason = trim((string)($d['reason'] ?? ''));
        $authorized_by = trim((string)($d['authorized_by'] ?? ''));

        if (in_array($type, self::PRIVILEGED_READINGS, true)) {
            if ($reason === '') {
                return ['success' => false, 'message' => 'A ' . str_replace('_', ' ', $type) . ' needs a reason — this is what preserves the audit trail.'];
            }
            if ($authorized_by === '') {
                return ['success' => false, 'message' => 'A ' . str_replace('_', ' ', $type) . ' needs an authorized handler.'];
            }
        }

        $compute_type = ($type === 'opening') ? 'run' : $type;
        $calc = $this->compute_reading($machine_id, $d['mono_reading'] ?? null, $d['colour_reading'] ?? null, [
            'reading_type' => $compute_type,
        ]);
        if (empty($calc['success'])) return $calc;

        $is_opening = ($type === 'opening');
        $is_non_activity = in_array($type, self::NON_ACTIVITY_READINGS, true);
        // An opening reading and a reset are not activity, by definition.
        if ($is_non_activity || $is_opening) {
            $calc['delta_valid'] = 0;
        }

        $row = [
            'store_id'             => $store_id,
            'machine_id'           => (int)$machine_id,
            'reading_type'         => $type,
            'reading_date'         => !empty($d['reading_date']) ? $d['reading_date'] : date('Y-m-d'),
            'reading_time'         => !empty($d['reading_time']) ? $d['reading_time'] : date('H:i:s'),
            'mono_reading'         => ($d['mono_reading'] ?? null) === '' ? null : ($d['mono_reading'] ?? null),
            'colour_reading'       => ($d['colour_reading'] ?? null) === '' ? null : ($d['colour_reading'] ?? null),
            'counter_unit'         => $m->counter_unit,
            'prev_reading_id'      => $calc['prev_reading_id'],
            'mono_delta'           => $calc['mono_delta'],
            'colour_delta'         => $calc['colour_delta'],
            'delta'                => $calc['delta'],
            'delta_valid'          => (int)($calc['delta_valid'] ?? 1),
            'delta_invalid_reason' => $calc['delta_invalid_reason'],
            'opening_baseline'     => $is_opening ? 1 : 0,
            'job_id'               => !empty($d['job_id']) ? (int)$d['job_id'] : null,
            'stage_id'             => !empty($d['stage_id']) ? (int)$d['stage_id'] : null,
            'stage_log_id'         => !empty($d['stage_log_id']) ? (int)$d['stage_log_id'] : null,
            'corrects_reading_id'  => !empty($d['corrects_reading_id']) ? (int)$d['corrects_reading_id'] : null,
            'reason'               => $reason !== '' ? $reason : null,
            'authorized_by'        => $authorized_by !== '' ? $authorized_by : null,
            'status'               => 'recorded',
            'recorded_by'          => $this->session->userdata('inv_username') ?: 'System',
            'notes'                => !empty($d['notes']) ? $d['notes'] : null,
        ];

        $this->db->trans_begin();
        $this->db->insert('db_print_machine_readings', $row);
        $new_id = $this->db->insert_id();

        // The corrected row keeps its value but stops being the live one. It is
        // SUPERSEDED, never deleted — a correction must remain visible.
        if (!empty($d['corrects_reading_id'])) {
            $this->db->where('id', (int)$d['corrects_reading_id'])
                ->where('machine_id', (int)$machine_id)
                ->update('db_print_machine_readings', ['status' => 'superseded']);
        }
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Could not record the reading.'];
        }
        $this->db->trans_commit();

        return [
            'success'     => true,
            'reading_id'  => $new_id,
            'message'     => 'Reading recorded.',
            'activity'    => ((int)($calc['delta_valid'] ?? 1)) ? $calc['delta'] : null,
            'delta_valid' => (int)($calc['delta_valid'] ?? 1),
            'warning'     => empty($calc['delta_valid']) ? $calc['delta_invalid_reason'] : null,
        ];
    }

    /**
     * The reading to SUGGEST when an operator opens a new run: the last live
     * reading for the machine and its date, so staleness is visible. Suggested,
     * never assumed — the operator still submits the real number.
     */
    public function suggest_reading($machine_id) {
        $m = $this->get_machine($machine_id);
        if (!$m) return ['success' => false, 'message' => 'Machine not found.'];
        $prev = $this->db->where('machine_id', $machine_id)->where('status', 'recorded')
            ->order_by('reading_date', 'desc')->order_by('id', 'desc')
            ->limit(1)->get('db_print_machine_readings')->row();
        return [
            'success'      => true,
            'reading_mode' => $m->reading_mode,
            'has_colour'   => (int)$m->has_colour_counter,
            'counter_unit' => $m->counter_unit,
            'mono'         => ($prev && $prev->mono_reading !== null) ? (float)$prev->mono_reading : null,
            'colour'       => ($prev && $prev->colour_reading !== null) ? (float)$prev->colour_reading : null,
            'as_of'        => $prev ? $prev->reading_date : null,
            'stale_days'   => $prev ? (int)floor((strtotime(date('Y-m-d')) - strtotime($prev->reading_date)) / 86400) : null,
            'last_type'    => $prev ? $prev->reading_type : null,
        ];
    }

    public function get_readings($machine_id, $limit = 100) {
        return $this->db->where('machine_id', $machine_id)
            ->order_by('reading_date', 'desc')->order_by('id', 'desc')
            ->limit($limit)->get('db_print_machine_readings')->result();
    }

    /**
     * Counter activity for a machine over a window.
     *
     * Deliberately sums ONLY delta_valid rows. An opening reading, a reset, a
     * replacement and a correction are all excluded: none of them is machine
     * activity, and including them produces an output figure with no relation
     * to reality.
     */
    public function counter_activity($machine_id, $from = null, $to = null) {
        $this->db->select('COALESCE(SUM(mono_delta),0) AS mono, COALESCE(SUM(colour_delta),0) AS colour, COALESCE(SUM(delta),0) AS total, COUNT(*) AS readings', false);
        $this->db->where('machine_id', $machine_id)
            ->where('status', 'recorded')
            ->where('delta_valid', 1)
            ->where('opening_baseline', 0);
        if ($from) $this->db->where('reading_date >=', $from);
        if ($to) $this->db->where('reading_date <=', $to);
        return $this->db->get('db_print_machine_readings')->row();
    }

    /** Readings that could not be trusted as activity — the discrepancy list. */
    public function invalid_readings($store_id = null, $limit = 50) {
        if (empty($store_id)) $store_id = get_current_store_id();
        return $this->db->select('r.*, m.name AS machine_name, m.machine_code')
            ->from('db_print_machine_readings r')
            ->join('db_print_machines m', 'm.id = r.machine_id', 'left')
            ->where('r.store_id', $store_id)->where('r.delta_valid', 0)
            ->order_by('r.reading_date', 'desc')->limit($limit)
            ->get()->result();
    }

    public function set_machine_status($machine_id, $status, $reason = '') {
        if (!array_key_exists($status, self::machine_statuses())) {
            return ['success' => false, 'message' => 'Unknown status.'];
        }
        if ($status !== 'available' && trim($reason) === '') {
            return ['success' => false, 'message' => 'Give a reason when taking a machine out of service.'];
        }
        $this->db->where('id', $machine_id)->update('db_print_machines', [
            'status'            => $status,
            'status_reason'     => trim($reason) ?: null,
            'status_changed_at' => date('Y-m-d H:i:s'),
            'status_changed_by' => $this->session->userdata('inv_username') ?: 'System',
        ]);
        return ['success' => true, 'message' => 'Machine is now ' . strtolower(self::machine_statuses()[$status]) . '.'];
    }

    public function save_machine($data, $id = null) {
        $store_id = get_current_store_id();
        $stages = $data['supported_stages'] ?? [];
        if (!is_array($stages)) $stages = array_values(array_filter(array_map('trim', explode(',', (string)$stages))));

        $row = [
            'store_id'         => $store_id,
            'branch_id'        => (int)($data['branch_id'] ?? 0),
            'name'             => trim((string)($data['name'] ?? '')),
            'model'            => trim((string)($data['model'] ?? '')) ?: null,
            'manufacturer'     => trim((string)($data['manufacturer'] ?? '')) ?: null,
            'serial_no'        => trim((string)($data['serial_no'] ?? '')) ?: null,
            'category_id'      => !empty($data['category_id']) ? (int)$data['category_id'] : null,
            'machine_category' => array_key_exists((string)($data['machine_category'] ?? ''), self::machine_categories()) ? $data['machine_category'] : 'other',
            'location'         => trim((string)($data['location'] ?? '')) ?: null,
            'supported_stages_json' => json_encode(array_values($stages)),
            'reading_mode'     => in_array(($data['reading_mode'] ?? ''), array_keys(self::reading_modes()), true) ? $data['reading_mode'] : 'optional',
            'has_colour_counter' => !empty($data['has_colour_counter']) ? 1 : 0,
            'counter_unit'     => trim((string)($data['counter_unit'] ?? '')) ?: 'impressions',
            'counter_rollover_at' => (string)($data['counter_rollover_at'] ?? '') !== '' ? (float)$data['counter_rollover_at'] : null,
            'supports_multiple_loads' => array_key_exists('supports_multiple_loads', $data) ? (int)!!$data['supports_multiple_loads'] : 1,
            'service_interval_days' => (string)($data['service_interval_days'] ?? '') !== '' ? (int)$data['service_interval_days'] : null,
            'service_interval_impressions' => (string)($data['service_interval_impressions'] ?? '') !== '' ? (float)$data['service_interval_impressions'] : null,
            'purchase_date'    => !empty($data['purchase_date']) ? $data['purchase_date'] : null,
            'purchase_cost'    => (float)($data['purchase_cost'] ?? 0),
            'notes'            => trim((string)($data['notes'] ?? '')) ?: null,
        ];
        if ($row['name'] === '') return ['success' => false, 'message' => 'Machine name is required.'];

        if ($id) {
            // The identifier may be corrected, but never silently to something
            // another machine already uses.
            $code = trim((string)($data['machine_code'] ?? ''));
            if ($code !== '') {
                $clash = $this->db->where('store_id', $store_id)->where('machine_code', $code)->where('id !=', $id)->count_all_results('db_print_machines');
                if ($clash) return ['success' => false, 'message' => 'Identifier "' . $code . '" is already used by another machine.'];
                $row['machine_code'] = $code;
            }
            $this->db->where('id', $id)->update('db_print_machines', $row);
            return ['success' => true, 'machine_id' => (int)$id, 'message' => 'Machine updated.'];
        }

        $code = trim((string)($data['machine_code'] ?? ''));
        if ($code === '') {
            $code = 'M' . str_pad((string)($this->db->where('store_id', $store_id)->count_all_results('db_print_machines') + 1), 3, '0', STR_PAD_LEFT);
        }
        $exists = $this->db->where('store_id', $store_id)->where('machine_code', $code)->count_all_results('db_print_machines');
        if ($exists) return ['success' => false, 'message' => 'Identifier "' . $code . '" is already used by another machine.'];
        $row['machine_code'] = $code;
        $row['created_by'] = $this->session->userdata('inv_username') ?: 'System';
        $this->db->insert('db_print_machines', $row);
        return ['success' => true, 'machine_id' => $this->db->insert_id(), 'message' => 'Machine added.'];
    }

    public function retire_machine($machine_id) {
        $this->db->where('id', $machine_id)->update('db_print_machines', ['status_active' => 0]);
        return ['success' => true, 'message' => 'Machine retired. Its history is preserved.'];
    }

    private function machine_label($m) {
        return trim(($m->machine_code ? $m->machine_code . ' — ' : '') . $m->name);
    }

    /**
     * Resolve the unit a run's quantities are expressed in. An explicit unit
     * wins; otherwise the job's own unit; otherwise NULL rather than a made-up
     * default, so a missing unit is visible instead of silently wrong.
     */
    private function resolve_unit_id($explicit, $fallback) {
        if (!empty($explicit)) return (int)$explicit;
        if (!empty($fallback)) return (int)$fallback;
        return null;
    }

    /**
     * On-hand stock for an item.
     *
     * db_items.stock is DERIVED (Pos_model::update_items_quantity recomputes it
     * from the stock-adjustment, purchase, sales and return ledgers). Reading it
     * is therefore correct for an availability CHECK, and the authoritative
     * deduction is still the stock posting itself. There is no db_warehouseitems
     * table in this repo — the shared printing helper that reads it always
     * reports zero, which is why this model does its own check.
     *
     * Pass $for_update to re-read the row under a lock inside a transaction, so
     * two concurrent issuances cannot both see the same stock as available.
     */
    private function stock_on_hand($item_id, $for_update = false) {
        if ($for_update) {
            $r = $this->db->query('SELECT stock FROM db_items WHERE id = ? FOR UPDATE', [(int)$item_id])->row();
        } else {
            $r = $this->db->select('stock')->where('id', (int)$item_id)->get('db_items')->row();
        }
        return $r ? (float)$r->stock : 0.0;
    }

    /* ==================================================================== */
    /*  SECTION 2 — production runs on a machine                            */
    /* ==================================================================== */

    /**
     * Record a production run against a stage.
     *
     * The operator MUST confirm the machine: planning a machine is not running
     * on it. An unavailable machine is BLOCKED unless the caller supplies an
     * authorized override, and the override records WHO authorized it.
     *
     * Run kinds 'manual' and 'outsourced' carry NO machine at all, so a run is
     * never given a fictitious machine assignment just to make a form pass.
     */
    public function record_run($job_id, $stage_id, array $d) {
        $job = $this->print->get_job($job_id);
        $stage = $this->db->where('id', $stage_id)->where('job_id', $job_id)->get('db_print_stages')->row();
        if (!$job || !$stage) return ['success' => false, 'message' => 'Job or stage not found.'];

        $kind = (string)($d['run_kind'] ?? 'machine');
        if (!array_key_exists($kind, self::run_kinds())) {
            return ['success' => false, 'message' => 'Unknown run type.'];
        }

        $machine_id = !empty($d['machine_id']) ? (int)$d['machine_id'] : null;
        $override_reason = trim((string)($d['machine_override_reason'] ?? ''));
        $override_by = trim((string)($d['machine_override_authorized_by'] ?? ''));
        $machine = null;
        $planned = (int)($stage->machine_id ?? 0);

        if ($kind === 'machine') {
            if (!$machine_id) {
                return ['success' => false, 'message' => 'Confirm which machine ran this stage.'];
            }
            $machine = $this->get_machine($machine_id);
            if (!$machine || (int)$machine->store_id !== (int)$job->store_id) {
                return ['success' => false, 'message' => 'That machine does not belong to this store.'];
            }
            // The operator must confirm the actual machine, not inherit the plan.
            $confirmed = !empty($d['machine_confirmed']) || (int)$machine_id === $planned;
            if (!$confirmed) {
                return ['success' => false, 'message' => 'Confirm the machine you actually ran on — it differs from the planned machine.'];
            }
            if ($machine->status !== 'available') {
                if ($override_reason === '' || $override_by === '') {
                    return ['success' => false, 'message' => $this->machine_label($machine) . ' is ' . strtolower(str_replace('_', ' ', $machine->status)) . '. An authorized override and a reason are required.'];
                }
            }
        } else {
            // Manual and outsourced work has no machine — and must not be given
            // one to make a report look complete.
            $machine_id = null;
            $machine = null;
            if ($kind === 'outsourced' && trim((string)($d['outsource_vendor'] ?? '')) === '') {
                return ['success' => false, 'message' => 'Name the vendor for an outsourced run.'];
            }
        }

        $qty_in   = max(0, (float)($d['qty_in'] ?? 0));
        $accepted = max(0, (float)($d['accepted_qty'] ?? 0));
        $reject   = max(0, (float)($d['reject_qty'] ?? 0));
        $waste    = max(0, (float)($d['waste_qty'] ?? 0));
        $rework   = max(0, (float)($d['rework_qty'] ?? 0));
        $partial  = max(0, (float)($d['partially_done_qty'] ?? 0));
        if ($qty_in <= 0 && $accepted <= 0) {
            return ['success' => false, 'message' => 'Record at least an input quantity or an accepted output.'];
        }
        if ($qty_in > 0 && $accepted > $qty_in) {
            return ['success' => false, 'message' => 'Accepted output (' . $accepted . ') cannot exceed the input quantity (' . $qty_in . ').'];
        }

        // Counter readings — start and end, taken from the run itself. The
        // reading helper recomputes the delta from the machine's own history, so
        // a client cannot send a delta of its choosing.
        $start_reading_id = null;
        $end_reading_id = null;
        $activity = null;
        $reading_warning = null;

        if ($kind === 'machine' && $machine->reading_mode !== 'unavailable') {
            $has_start = (string)($d['mono_start'] ?? '') !== '' || (string)($d['colour_start'] ?? '') !== '';
            $has_end   = (string)($d['mono_end'] ?? '') !== '' || (string)($d['colour_end'] ?? '') !== '';

            if ($machine->reading_mode === 'required' && !$has_end) {
                return ['success' => false, 'message' => $this->machine_label($machine) . ' requires a closing counter reading.'];
            }
            $run_date = !empty($d['run_date']) ? $d['run_date'] : date('Y-m-d');
            if ($has_start) {
                $r = $this->record_reading($machine_id, [
                    'reading_type'   => 'run',
                    'mono_reading'   => $d['mono_start'] ?? null,
                    'colour_reading' => $d['colour_start'] ?? null,
                    'reading_date'   => $run_date,
                    'job_id'         => $job_id,
                    'stage_id'       => $stage_id,
                    'notes'          => 'Run start',
                ]);
                if (empty($r['success'])) return $r;
                $start_reading_id = $r['reading_id'];
            }
            if ($has_end) {
                $r = $this->record_reading($machine_id, [
                    'reading_type'   => 'run',
                    'mono_reading'   => $d['mono_end'] ?? null,
                    'colour_reading' => $d['colour_end'] ?? null,
                    'reading_date'   => $run_date,
                    'job_id'         => $job_id,
                    'stage_id'       => $stage_id,
                    'notes'          => 'Run end',
                ]);
                if (empty($r['success'])) return $r;
                $end_reading_id = $r['reading_id'];
                if (!empty($r['delta_valid'])) {
                    $activity = $r['activity'];
                } else {
                    $reading_warning = $r['warning'];
                }
            }
        }


        // A run whose counter activity bears no relation to its accepted output
        // is FLAGGED for review rather than silently believed. This is the whole
        // point of keeping the two numbers apart.
        $discrepancy = null;
        if ($activity !== null && $activity > 0) {
            $tolerance = max(1.0, $accepted * 0.02); // 2%, floor of 1 unit
            if ($activity + $tolerance < $accepted) {
                $discrepancy = 'Counter activity (' . $activity . ') is lower than accepted output (' . $accepted . ') — the reading or the quantity needs review.';
            } elseif ($accepted > 0 && $activity > ($accepted * 50)) {
                $discrepancy = 'Counter activity (' . $activity . ') is more than 50x the accepted output — check the readings.';
            }
        }
        if ($reading_warning !== null) {
            $discrepancy = $reading_warning;
        }

        $log = [
            'store_id'                => (int)$job->store_id,
            'job_id'                  => (int)$job_id,
            'stage_id'                => (int)$stage_id,
            'machine_id'              => $machine_id,
            'machine_id_planned'      => $planned ?: null,
            'machine_confirmed'       => $kind === 'machine' ? 1 : 0,
            'machine_override_reason' => $override_reason !== '' ? $override_reason : null,
            'machine_override_authorized_by' => $override_by !== '' ? $override_by : null,
            'run_kind'                => $kind,
            'operator_id'             => !empty($d['operator_id']) ? (int)$d['operator_id'] : null,
            'work_date'               => !empty($d['run_date']) ? $d['run_date'] : date('Y-m-d'),
            'started_at'              => !empty($d['started_at']) ? $d['started_at'] : null,
            'ended_at'                => !empty($d['ended_at']) ? $d['ended_at'] : null,
            'counter_start_id'        => $start_reading_id,
            'counter_end_id'          => $end_reading_id,
            'counter_activity'        => $activity,
            'qty_in'                  => $qty_in,
            'good_qty'                => $accepted,
            'accepted_qty'            => $accepted,
            'rework_qty'              => $rework,
            'partially_done_qty'      => $partial,
            'reject_qty'              => $reject,
            'waste_qty'               => $waste,
            'qty_unit_id'             => $this->resolve_unit_id($d['qty_unit_id'] ?? null, $job->unit_id ?? null),
            'input_cost'              => max(0, (float)($d['input_cost'] ?? 0)),
            'outsource_cost'          => max(0, (float)($d['outsource_cost'] ?? 0)),
            'labour_cost'             => max(0, (float)($d['labour_cost'] ?? 0)),
            'outsource_vendor'        => $kind === 'outsourced' ? trim((string)($d['outsource_vendor'] ?? '')) : null,
            'references_json'         => !empty($d['references']) ? json_encode($d['references']) : null,
            'reading_discrepancy'     => $discrepancy,
            'notes'                   => !empty($d['notes']) ? $d['notes'] : null,
            'status'                  => 'submitted',
            'submitted_by'            => $this->session->userdata('inv_username') ?: 'System',
        ];

        $this->db->trans_begin();
        $this->db->insert('db_print_stage_logs', $log);
        $log_id = $this->db->insert_id();

        if (!$log_id) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Could not save the run.'];
        }

        if ($stage->status === 'pending') {
            $this->db->where('id', $stage_id)->update('db_print_stages', [
                'status' => 'in_progress',
                'started_at' => date('Y-m-d H:i:s'),
            ]);
        }
        if ($job->production_status === 'planned') {
            $this->db->where('id', $job_id)->update('db_print_jobs', ['production_status' => 'in_progress']);
        }

        // Tie the readings back to the run so machine history can show them.
        foreach ([$start_reading_id, $end_reading_id] as $rid) {
            if ($rid) $this->db->where('id', $rid)->update('db_print_machine_readings', ['stage_log_id' => $log_id]);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Could not save the run — no changes were made.'];
        }
        $this->db->trans_commit();

        return [
            'success'     => true,
            'log_id'      => $log_id,
            'message'     => 'Run recorded.',
            'warning'     => $discrepancy,
            'activity'    => $activity,
            'accepted'    => $accepted,
        ];
    }

    /** All runs on a job, with machine and operator resolved. */
    public function get_runs($job_id, $stage_id = null) {
        $this->db->select('l.*, m.name AS machine_name, m.machine_code, m.counter_unit AS machine_unit,
                           s.stage_key, s.stage_label, u.username AS operator_name', false);
        $this->db->from('db_print_stage_logs l');
        $this->db->join('db_print_machines m', 'm.id = l.machine_id', 'left');
        $this->db->join('db_print_stages s', 's.id = l.stage_id', 'left');
        $this->db->join('db_users u', 'u.id = l.operator_id', 'left');
        $this->db->where('l.job_id', $job_id);
        if ($stage_id) $this->db->where('l.stage_id', $stage_id);
        return $this->db->order_by('l.work_date', 'asc')->order_by('l.id', 'asc')->get()->result();
    }

    /** Distinct machines used on a job — the job's machine history summary. */
    public function machines_used_on_job($job_id) {
        return $this->db->select('m.id, m.machine_code, m.name, m.machine_category, m.status,
                                  COUNT(l.id) AS runs, SUM(l.counter_activity) AS activity,
                                  SUM(l.accepted_qty) AS accepted', false)
            ->from('db_print_stage_logs l')
            ->join('db_print_machines m', 'm.id = l.machine_id', 'inner')
            ->where('l.job_id', $job_id)
            ->group_by(['m.id', 'm.machine_code', 'm.name', 'm.machine_category', 'm.status'])
            ->order_by('m.machine_code', 'asc')
            ->get()->result();
    }

    /** Jobs a machine has been used on — the machine's job history. */
    public function jobs_for_machine($machine_id, $limit = 100) {
        return $this->db->select('j.id, j.job_code, j.title, j.production_status, c.customer_name,
                                  COUNT(l.id) AS runs, SUM(l.accepted_qty) AS accepted,
                                  COALESCE(SUM(l.counter_activity),0) AS activity,
                                  MAX(l.work_date) AS last_run', false)
            ->from('db_print_stage_logs l')
            ->join('db_print_jobs j', 'j.id = l.job_id', 'inner')
            ->join('db_customers c', 'c.id = j.customer_id', 'left')
            ->where('l.machine_id', $machine_id)
            ->group_by(['j.id', 'j.job_code', 'j.title', 'j.production_status', 'c.customer_name'])
            ->order_by('last_run', 'desc')
            ->limit($limit)
            ->get()->result();
    }

    /** Unresolved reading discrepancies, so they cannot quietly disappear. */
    public function reading_discrepancies($store_id = null, $limit = 50) {
        if (empty($store_id)) $store_id = get_current_store_id();
        return $this->db->select('l.*, j.job_code, j.title, s.stage_label, m.name AS machine_name, m.machine_code', false)
            ->from('db_print_stage_logs l')
            ->join('db_print_jobs j', 'j.id = l.job_id', 'left')
            ->join('db_print_stages s', 's.id = l.stage_id', 'left')
            ->join('db_print_machines m', 'm.id = l.machine_id', 'left')
            ->where('l.store_id', $store_id)
            ->where('l.reading_discrepancy IS NOT NULL', null, false)
            ->where('l.status !=', 'reversed')
            ->order_by('l.work_date', 'desc')->limit($limit)
            ->get()->result();
    }

    /* ==================================================================== */
    /*  SECTION 3 — consumable and replacement-part issuance                */
    /* ==================================================================== */

    /**
     * Request a consumable or part for a machine.
     *
     * This creates ONLY a request. Nothing leaves stock until approve_supply()
     * runs, so asking is always free and never moves inventory.
     */
    public function request_supply(array $d) {
        $store_id = get_current_store_id();
        $machine_id = !empty($d['machine_id']) ? (int)$d['machine_id'] : null;
        $item_id = !empty($d['item_id']) ? (int)$d['item_id'] : null;
        if (!$item_id && trim((string)($d['description'] ?? '')) === '') {
            return ['success' => false, 'message' => 'Pick a stock item or describe the consumable.'];
        }
        $qty = (float)($d['requested_qty'] ?? 0);
        if ($qty <= 0) return ['success' => false, 'message' => 'Enter a quantity greater than zero.'];

        $base_qty = $qty;
        $factor = 1;
        $conv_ok = 1;
        // An explicit unit conversion is applied when the requested unit differs
        // from the item's own unit. to_base_qty() returns null when no conversion
        // is KNOWN — which is a different thing from a conversion of 1. Guessing
        // a factor silently corrupts both stock and cost, so an unknown
        // conversion is flagged and blocks approval instead.
        if ($item_id && !empty($d['unit_id'])) {
            $converted = $this->print->to_base_qty($item_id, (int)$d['unit_id'], $qty, $store_id);
            if ($converted === null) {
                $conv_ok = 0;
            } else {
                $base_qty = (float)$converted;
                $factor = $qty > 0 ? $base_qty / $qty : 1;
            }
        }

        $row = [
            'store_id'       => $store_id,
            'machine_id'     => $machine_id,
            'item_id'        => $item_id,
            'supply_type'    => array_key_exists((string)($d['supply_type'] ?? ''), self::supply_types()) ? $d['supply_type'] : 'other',
            'description'    => trim((string)($d['description'] ?? '')) ?: null,
            'requested_qty'  => $qty,
            'unit_id'        => !empty($d['unit_id']) ? (int)$d['unit_id'] : null,
            'base_qty'       => $base_qty,
            'conversion_factor' => $factor,
            'conversion_ok'  => $conv_ok,
            'status'         => 'requested',
            'cost_known'     => 1,
            'opening_loaded' => 0,
            'capacity_basis' => trim((string)($d['capacity_basis'] ?? '')) ?: null,
            'capacity_qty'   => (float)($d['capacity_qty'] ?? 0),
            'remaining_qty'  => (float)($d['capacity_qty'] ?? 0),
            'job_id'         => !empty($d['job_id']) ? (int)$d['job_id'] : null,
            'stage_id'       => !empty($d['stage_id']) ? (int)$d['stage_id'] : null,
            'stage_log_id'   => !empty($d['stage_log_id']) ? (int)$d['stage_log_id'] : null,
            'warehouse_id'   => !empty($d['warehouse_id']) ? (int)$d['warehouse_id'] : null,
            'requested_by'   => $this->session->userdata('inv_username') ?: 'System',
            'requested_at'   => date('Y-m-d H:i:s'),
            'note'           => trim((string)($d['note'] ?? '')) ?: null,
        ];
        $this->db->insert('db_print_machine_supplies', $row);
        return [
            'success'    => true,
            'supply_id'  => $this->db->insert_id(),
            'message'    => 'Request logged.',
            'warning'    => $conv_ok ? null : 'No known conversion between the requested unit and the stock unit — a human must confirm the quantity before it is issued.',
        ];
    }

    /**
     * Capture consumables already loaded in a machine at go-live.
     *
     * These are a STARTING POSITION, not a new issue, so no stock moves. The
     * shop may or may not know what they cost: cost_known = 0 records "unknown"
     * honestly instead of inventing a number that would later corrupt costing.
     */
    public function capture_opening_load($machine_id, array $d) {
        $machine = $this->get_machine($machine_id);
        if (!$machine) return ['success' => false, 'message' => 'Machine not found.'];
        $qty = (float)($d['qty'] ?? 0);
        if ($qty <= 0) return ['success' => false, 'message' => 'Enter how much is loaded.'];

        $cost_known = array_key_exists('cost_known', $d) ? (int)!!$d['cost_known'] : 1;
        $unit_cost = (float)($d['unit_cost'] ?? 0);
        if (!$cost_known) $unit_cost = 0;

        $capacity = (float)($d['capacity_qty'] ?? 0);
        $row = [
            'store_id'       => (int)$machine->store_id,
            'machine_id'     => (int)$machine_id,
            'item_id'        => !empty($d['item_id']) ? (int)$d['item_id'] : null,
            'supply_type'    => array_key_exists((string)($d['supply_type'] ?? ''), self::supply_types()) ? $d['supply_type'] : 'other',
            'description'    => trim((string)($d['description'] ?? '')) ?: null,
            'requested_qty'  => $qty,
            'base_qty'       => $qty,
            'issued_qty'     => $qty,
            'installed_qty'  => $qty,
            'status'         => 'installed',
            'unit_cost'      => $unit_cost,
            'issued_cost'    => $cost_known ? round($qty * $unit_cost, 2) : 0,
            'cost_known'     => $cost_known,
            'opening_loaded' => 1,
            'capacity_basis' => trim((string)($d['capacity_basis'] ?? '')) ?: null,
            'capacity_qty'   => $capacity,
            'remaining_qty'  => $capacity,
            'issued_by'      => $this->session->userdata('inv_username') ?: 'System',
            'issued_at'      => date('Y-m-d H:i:s'),
            'installed_by'   => $this->session->userdata('inv_username') ?: 'System',
            'installed_at'   => date('Y-m-d H:i:s'),
            'note'           => 'Opening load captured at go-live.',
        ];
        $this->db->insert('db_print_machine_supplies', $row);
        return [
            'success'   => true,
            'supply_id' => $this->db->insert_id(),
            'message'   => 'Opening load captured — no stock was moved.',
            'warning'   => $cost_known ? null : 'Cost recorded as unknown. Nothing will be charged to a job for this load.',
        ];
    }

    /**
     * Approve a supply request and post stock — ATOMICALLY, and exactly ONCE.
     *
     * Stock availability is checked and the deduction posted in the SAME
     * transaction as the status change, guarded by a conditional UPDATE on the
     * request being still pending. A double-click or a retry therefore cannot
     * deduct twice: the second caller's UPDATE matches zero rows and it bails.
     */
    public function approve_supply($supply_id, array $d = []) {
        $s = $this->db->where('id', $supply_id)->get('db_print_machine_supplies')->row();
        if (!$s) return ['success' => false, 'message' => 'Request not found.'];
        if ($s->status !== 'requested') {
            return ['success' => false, 'message' => 'This request is already ' . str_replace('_', ' ', $s->status) . ' — it cannot be approved twice.'];
        }
        if (!$s->conversion_ok) {
            return ['success' => false, 'message' => 'The unit conversion for this request is unconfirmed. Fix the quantity before approving — guessing it would corrupt stock.'];
        }

        $qty = (float)$s->base_qty;
        if ($qty <= 0) return ['success' => false, 'message' => 'Nothing to issue.'];

        $warehouse_id = $s->warehouse_id ?: get_store_warehouse_id();
        $item = null;
        if ($s->item_id) {
            $item = $this->db->where('id', $s->item_id)->get('db_items')->row();
            if (!$item) return ['success' => false, 'message' => 'The stock item no longer exists.'];
            // Availability is checked BEFORE the posting, and the same value is
            // re-read inside the transaction below so a concurrent issue cannot
            // slip through the gap. db_items.stock is the derived, authoritative
            // on-hand figure for this repo (there is no db_warehouseitems table).
            $avail = $this->stock_on_hand($s->item_id);
            if ($qty > $avail + 0.0001) {
                return ['success' => false, 'message' => 'Only ' . $avail . ' ' . ($item->unit_name ?? 'units') . ' available; ' . $qty . ' requested. Nothing was deducted.'];
            }
        }

        $unit_cost = $item ? (float)$item->purchase_price : (float)($d['unit_cost'] ?? $s->unit_cost);
        if (array_key_exists('unit_cost', $d) && $d['unit_cost'] !== '' && !$s->item_id) {
            $unit_cost = (float)$d['unit_cost'];
        }

        $this->db->trans_begin();

        // Re-check availability under a row lock, so two concurrent approvals of
        // two different requests for the same item cannot both see the same
        // stock as available and both deduct it.
        if ($s->item_id) {
            $locked = $this->stock_on_hand($s->item_id, true);
            if ($qty > $locked + 0.0001) {
                $this->db->trans_rollback();
                $item_name = $item ? ($item->unit_name ?? 'units') : 'units';
                return ['success' => false, 'message' => 'Only ' . $locked . ' ' . $item_name . ' remained at the moment of approval; ' . $qty . ' requested. Nothing was deducted.'];
            }
        }

        // Conditional claim — the ONE gate that makes this single-shot.
        $claimed = $this->db
            ->where('id', $supply_id)
            ->where('status', 'requested')
            ->update('db_print_machine_supplies', [
                'status'      => 'approved',
                'approved_by' => $this->session->userdata('inv_username') ?: 'System',
                'approved_at' => date('Y-m-d H:i:s'),
                'unit_cost'   => $unit_cost,
            ]);
        if (!$claimed || $this->db->affected_rows() !== 1) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'This request was already approved. No second deduction was made.'];
        }

        // The ONE stock deduction, through the shared posting method.
        $issue_adj = null;
        if ($s->item_id) {
            $issue_adj = $this->print->post_stock_moves_public(
                'SUPPLY-' . $supply_id,
                [['item_id' => (int)$s->item_id, 'qty' => -$qty, 'description' => 'Machine consumable issue #' . $supply_id]],
                'Issue of machine consumable/part #' . $supply_id,
                $warehouse_id
            );
            if ($issue_adj === false) {
                $this->db->trans_rollback();
                return ['success' => false, 'message' => 'Stock posting failed — nothing was changed.'];
            }
        }

        $issued_cost = round($qty * $unit_cost, 2);
        $this->db->where('id', $supply_id)->update('db_print_machine_supplies', [
            'status'              => 'issued',
            'issued_qty'          => $qty,
            'issued_cost'         => $issued_cost,
            'issue_adjustment_id' => $issue_adj ?: null,
            'issued_by'           => $this->session->userdata('inv_username') ?: 'System',
            'issued_at'           => date('Y-m-d H:i:s'),
            'remaining_qty'       => (float)$s->capacity_qty > 0 ? (float)$s->capacity_qty : $qty,
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Approval failed and was rolled back — stock is unchanged.'];
        }
        $this->db->trans_commit();

        return [
            'success'    => true,
            'message'    => 'Approved and issued from stores.' . ($s->cost_known ? '' : ' Cost is unknown, so nothing will be charged to a job.'),
            'issued_qty' => $qty,
            'unit_cost'  => $unit_cost,
            'adjustment_id' => $issue_adj,
        ];
    }

    public function reject_supply($supply_id, $reason = '') {
        $claimed = $this->db->where('id', $supply_id)->where('status', 'requested')
            ->update('db_print_machine_supplies', [
                'status'          => 'rejected',
                'rejected_reason' => trim($reason) ?: null,
                'approved_by'     => $this->session->userdata('inv_username') ?: 'System',
                'approved_at'     => date('Y-m-d H:i:s'),
            ]);
        if (!$claimed || $this->db->affected_rows() !== 1) {
            return ['success' => false, 'message' => 'Only a pending request can be rejected.'];
        }
        return ['success' => true, 'message' => 'Request rejected. No stock was moved.'];
    }

    /**
     * Record that issued stock was physically installed/loaded into a machine.
     *
     * NO stock movement. The deduction already happened at approval; doing it
     * again here is precisely the double deduction this lifecycle forbids.
     */
    public function install_supply($supply_id, array $d = []) {
        $s = $this->db->where('id', $supply_id)->get('db_print_machine_supplies')->row();
        if (!$s) return ['success' => false, 'message' => 'Supply record not found.'];
        if (!in_array($s->status, ['issued', 'partially_installed', 'approved'], true)) {
            return ['success' => false, 'message' => 'Only issued stock can be installed. This record is ' . str_replace('_', ' ', $s->status) . '.'];
        }
        $qty = ($d['qty'] ?? '') !== '' ? (float)$d['qty'] : (float)$s->issued_qty;
        if ($qty <= 0) return ['success' => false, 'message' => 'Enter how much was installed.'];
        $installed = (float)$s->installed_qty + $qty;
        if ($installed > (float)$s->issued_qty + 0.0001) {
            return ['success' => false, 'message' => 'Cannot install ' . $qty . ' — only ' . ((float)$s->issued_qty - (float)$s->installed_qty) . ' is still uninstalled.'];
        }

        $this->db->where('id', $supply_id)->update('db_print_machine_supplies', [
            'installed_qty' => $installed,
            'status'        => ($installed >= (float)$s->issued_qty - 0.0001) ? 'installed' : 'partially_installed',
            'machine_id'    => !empty($d['machine_id']) ? (int)$d['machine_id'] : $s->machine_id,
            'installed_by'  => $this->session->userdata('inv_username') ?: 'System',
            'installed_at'  => date('Y-m-d H:i:s'),
            'stage_log_id'  => !empty($d['stage_log_id']) ? (int)$d['stage_log_id'] : $s->stage_log_id,
            'job_id'        => !empty($d['job_id']) ? (int)$d['job_id'] : $s->job_id,
        ]);
        return ['success' => true, 'message' => 'Marked installed. No stock was moved — it was deducted when issued.', 'installed_qty' => $installed];
    }

    /**
     * Report how much of an installed supply has actually been used.
     *
     * Consumption is reported, never inferred from installation. A cartridge
     * installed during a job is not a cartridge consumed by that job.
     */
    public function report_consumption($supply_id, $qty, $note = '') {
        $s = $this->db->where('id', $supply_id)->get('db_print_machine_supplies')->row();
        if (!$s) return ['success' => false, 'message' => 'Supply record not found.'];
        $qty = (float)$qty;
        if ($qty <= 0) return ['success' => false, 'message' => 'Enter the quantity consumed.'];

        $consumed = (float)$s->consumed_qty + $qty;
        $available_to_consume = (float)$s->installed_qty > 0 ? (float)$s->installed_qty : (float)$s->issued_qty;
        if ($consumed > $available_to_consume + 0.0001) {
            return ['success' => false, 'message' => 'Reported consumption (' . $consumed . ') exceeds what was installed (' . $available_to_consume . ').'];
        }

        $unit_cost = (float)$s->unit_cost;
        $this->db->where('id', $supply_id)->update('db_print_machine_supplies', [
            'consumed_qty'  => $consumed,
            'consumed_cost' => round($consumed * $unit_cost, 2),
            'remaining_qty' => (float)$s->capacity_qty > 0
                ? max(0, (float)$s->capacity_qty - $consumed)
                : max(0, $available_to_consume - $consumed),
            'status'        => $consumed >= $available_to_consume - 0.0001 ? 'consumed' : $s->status,
            'note'          => trim($note) ?: $s->note,
        ]);
        return ['success' => true, 'message' => 'Consumption reported.', 'consumed_qty' => $consumed];
    }

    /**
     * Return unused stock to stores.
     *
     * Credits stock back EXACTLY ONCE, guarded by the same conditional-claim
     * pattern as approval, and records the single credit adjustment id. A second
     * return call finds the row already in a returned state and refuses.
     */
    public function return_supply($supply_id, $qty, $reason = '') {
        $s = $this->db->where('id', $supply_id)->get('db_print_machine_supplies')->row();
        if (!$s) return ['success' => false, 'message' => 'Supply record not found.'];
        if (in_array($s->status, ['returned', 'rejected', 'cancelled', 'requested'], true)) {
            return ['success' => false, 'message' => 'This record is ' . str_replace('_', ' ', $s->status) . ' and cannot be returned.'];
        }
        $qty = (float)$qty;
        if ($qty <= 0) return ['success' => false, 'message' => 'Enter the quantity being returned.'];

        $returnable = (float)$s->issued_qty - (float)$s->consumed_qty - (float)$s->returned_qty;
        if ($qty > $returnable + 0.0001) {
            return ['success' => false, 'message' => 'Only ' . max(0, $returnable) . ' can still be returned (issued ' . (float)$s->issued_qty . ' − consumed ' . (float)$s->consumed_qty . ' − already returned ' . (float)$s->returned_qty . ').'];
        }

        $this->db->trans_begin();

        // Claim the return before crediting, so a retry cannot credit twice.
        $new_returned = (float)$s->returned_qty + $qty;
        $claim = $this->db
            ->where('id', $supply_id)
            ->where('returned_qty', $s->returned_qty)
            ->update('db_print_machine_supplies', ['returned_qty' => $new_returned]);
        if (!$claim || $this->db->affected_rows() !== 1) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Another return was recorded at the same moment. Nothing was double-credited — try again.'];
        }

        $return_adj = null;
        if ($s->item_id && $qty > 0) {
            $return_adj = $this->print->post_stock_moves_public(
                'SUPPLY-RET-' . $supply_id,
                [['item_id' => (int)$s->item_id, 'qty' => $qty, 'description' => 'Unused machine consumable returned #' . $supply_id]],
                'Unused return of machine consumable/part #' . $supply_id,
                $s->warehouse_id ?: get_store_warehouse_id()
            );
            if ($return_adj === false) {
                $this->db->trans_rollback();
                return ['success' => false, 'message' => 'Stock credit failed — nothing was changed.'];
            }
        }

        $this->db->where('id', $supply_id)->update('db_print_machine_supplies', [
            'return_adjustment_id' => $return_adj ?: null,
            'returned_by'          => $this->session->userdata('inv_username') ?: 'System',
            'returned_at'          => date('Y-m-d H:i:s'),
            'return_reason'        => trim($reason) ?: null,
            'status'               => ($new_returned >= (float)$s->issued_qty - (float)$s->consumed_qty - 0.0001)
                ? 'returned' : 'partially_returned',
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Return failed and was rolled back.'];
        }
        $this->db->trans_commit();

        return ['success' => true, 'message' => 'Unused quantity returned to stores.', 'returned_qty' => $new_returned, 'adjustment_id' => $return_adj];
    }

    /**
     * Allocate part of a supply's cost to a JOB as an ESTIMATED allocation.
     *
     * This is the correction for "the whole toner cartridge went on one job".
     * Allocation never exceeds the remaining allocatable capacity, and it is
     * flagged estimated so it can never be mistaken for a measured cost. It also
     * refuses to allocate more than the supply actually cost — the total
     * allocated can never exceed issued_cost.
     */
    public function allocate_supply_cost($supply_id, $job_id, $amount, $basis_qty = 0, $note = '') {
        $s = $this->db->where('id', $supply_id)->get('db_print_machine_supplies')->row();
        if (!$s) return ['success' => false, 'message' => 'Supply record not found.'];
        $amount = (float)$amount;
        if ($amount <= 0) return ['success' => false, 'message' => 'Enter an allocation amount.'];
        if (!$s->cost_known) {
            return ['success' => false, 'message' => 'This supply has no known cost, so nothing can be allocated to a job.'];
        }

        $allocatable = (float)$s->issued_cost - (float)$s->allocated_cost;
        if ($amount > $allocatable + 0.0001) {
            return ['success' => false, 'message' => 'Only ' . round($allocatable, 2) . ' of this supply is still unallocated. Allocating ' . $amount . ' would charge more than it cost.'];
        }

        $this->db->trans_begin();
        $claim = $this->db->where('id', $supply_id)
            ->where('allocated_cost', $s->allocated_cost)
            ->update('db_print_machine_supplies', ['allocated_cost' => (float)$s->allocated_cost + $amount]);
        if (!$claim || $this->db->affected_rows() !== 1) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'A concurrent allocation changed this supply. Nothing was allocated — try again.'];
        }

        $this->db->insert('db_print_consumable_allocations', [
            'store_id'      => (int)$s->store_id,
            'consumable_id' => (int)$supply_id,
            'job_id'        => (int)$job_id,
            'basis_qty'     => (float)$basis_qty,
            'estimated'     => 1,
            'amount'        => $amount,
            'created_by'    => $this->session->userdata('inv_username') ?: 'System',
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Allocation failed and was rolled back.'];
        }
        $this->db->trans_commit();
        return ['success' => true, 'message' => 'Estimated allocation recorded against the job.'];
    }

    public function get_supplies($store_id = null, array $f = []) {
        if (empty($store_id)) $store_id = get_current_store_id();
        // db_items has no `unit_name` column — the unit lives in `consumable_unit`
        // (with `unit_id` pointing at db_units). Selecting a column that does not
        // exist made this query return FALSE, and the ->result() on false threw
        // "Call to a member function result() on bool" — so get_supplies() had
        // never once succeeded, on any install.
        //
        // COALESCE prefers the item's own unit when set, then falls back to the
        // unit the supply line was requested in, which is what a storekeeper
        // actually reads off the record.
        $this->db->select('s.*, m.name AS machine_name, m.machine_code,
                           i.item_name,
                           COALESCE(i.consumable_unit, u.unit_name) AS unit_name', false);
        $this->db->from('db_print_machine_supplies s');
        $this->db->join('db_print_machines m', 'm.id = s.machine_id', 'left');
        $this->db->join('db_items i', 'i.id = s.item_id', 'left');
        $this->db->join('db_units u', 'u.id = COALESCE(s.unit_id, i.unit_id)', 'left');
        $this->db->join('db_print_jobs j', 'j.id = s.job_id', 'left');
        $this->db->where('s.store_id', $store_id);
        if (!empty($f['status'])) $this->db->where('s.status', $f['status']);
        if (!empty($f['machine_id'])) $this->db->where('s.machine_id', (int)$f['machine_id']);
        if (!empty($f['type'])) $this->db->where('s.supply_type', $f['type']);
        if (!empty($f['job_id'])) $this->db->where('s.job_id', (int)$f['job_id']);
        return $this->db->order_by('s.id', 'desc')->get()->result();
    }

    public function get_supply($id) {
        return $this->db->select('s.*, m.name AS machine_name, m.machine_code, i.item_name', false)
            ->from('db_print_machine_supplies s')
            ->join('db_print_machines m', 'm.id = s.machine_id', 'left')
            ->join('db_items i', 'i.id = s.item_id', 'left')
            ->where('s.id', $id)->get()->row();
    }

    /* ==================================================================== */
    /*  SECTION 4 — maintenance visits                                       */
    /* ==================================================================== */

    /**
     * Record a maintenance visit.
     *
     * The money guard: any part already ISSUED from stores carries its own
     * issue_adjustment_id, which means its stock was consumed and, for costing
     * purposes, already expensed. Such a part is attached with
     * from_inventory = 1 and its value is accumulated into parts_issued_value —
     * which is NOT added to total_cost. Expensing it again here would double
     * count the same money, and the visit total would be a fiction.
     *
     * A counter reading at service is recorded through the normal reading path,
     * so it appears in machine history like any other reading.
     */
    public function record_maintenance(array $d) {
        $machine_id = (int)($d['machine_id'] ?? 0);
        $machine = $this->get_machine($machine_id);
        if (!$machine) return ['success' => false, 'message' => 'Machine not found.'];

        $visit_date = !empty($d['visit_date']) ? $d['visit_date'] : date('Y-m-d');
        $labour = max(0, (float)($d['labour_cost'] ?? 0));
        $parts_bought = max(0, (float)($d['parts_cost'] ?? 0));
        $outsource = max(0, (float)($d['outsource_cost'] ?? 0));
        $other = max(0, (float)($d['other_cost'] ?? 0));

        $parts = $d['parts'] ?? [];
        if (!is_array($parts)) $parts = [];

        // Split the parts list into "came from our stores" and "bought for the
        // visit". Only the latter is expenditure on this visit.
        $parts_from_inventory_value = 0;
        $prepared_parts = [];
        foreach ($parts as $p) {
            if (empty($p['supply_id']) && empty($p['item_id'])) continue;
            $qty = (float)($p['qty'] ?? 0);
            if ($qty <= 0) continue;
            $from_inv = 0;
            $unit_cost = (float)($p['unit_cost'] ?? 0);
            $supply = null;
            if (!empty($p['supply_id'])) {
                $supply = $this->db->where('id', (int)$p['supply_id'])->get('db_print_machine_supplies')->row();
                if ($supply && !empty($supply->issue_adjustment_id)) {
                    // Stock already deducted by its issuance — do NOT expense again.
                    $from_inv = 1;
                    $unit_cost = (float)$supply->unit_cost;
                    $parts_from_inventory_value += round($qty * $unit_cost, 2);
                }
            }
            $prepared_parts[] = [
                'supply_id'      => !empty($p['supply_id']) ? (int)$p['supply_id'] : null,
                'item_id'        => !empty($p['item_id']) ? (int)$p['item_id'] : ($supply->item_id ?? null),
                'description'    => trim((string)($p['description'] ?? ($supply->description ?? ''))) ?: null,
                'qty'            => $qty,
                'unit_id'        => !empty($p['unit_id']) ? (int)$p['unit_id'] : ($supply->unit_id ?? null),
                'unit_cost'      => $unit_cost,
                'total_cost'     => round($qty * $unit_cost, 2),
                'from_inventory' => $from_inv,
            ];
        }

        $total = round($labour + $parts_bought + $outsource + $other, 2);

        $this->db->trans_begin();

        $row = [
            'store_id'          => (int)$machine->store_id,
            'machine_id'        => $machine_id,
            'visit_type'        => array_key_exists((string)($d['visit_type'] ?? ''), ['service','repair','fault','inspection','calibration','install','other'])
                                    ? $d['visit_type'] : 'service',
            'performed_by_type' => (($d['performed_by_type'] ?? 'internal') === 'external') ? 'external' : 'internal',
            'technician_name'   => trim((string)($d['technician_name'] ?? '')) ?: null,
            'provider_name'     => trim((string)($d['provider_name'] ?? '')) ?: null,
            'provider_contact'  => trim((string)($d['provider_contact'] ?? '')) ?: null,
            'visit_date'        => $visit_date,
            'started_at'        => !empty($d['started_at']) ? $d['started_at'] : null,
            'ended_at'          => !empty($d['ended_at']) ? $d['ended_at'] : null,
            'downtime_minutes'  => max(0, (int)($d['downtime_minutes'] ?? 0)),
            'fault_code'        => trim((string)($d['fault_code'] ?? '')) ?: null,
            'symptom'           => trim((string)($d['symptom'] ?? '')) ?: null,
            'work_performed'    => trim((string)($d['work_performed'] ?? '')) ?: null,
            'labour_cost'       => $labour,
            'parts_cost'        => $parts_bought,
            'outsource_cost'    => $outsource,
            'other_cost'        => $other,
            'total_cost'        => $total,
            'parts_issued_value' => $parts_from_inventory_value,
            'next_service_at'   => !empty($d['next_service_at']) ? $d['next_service_at'] : null,
            'next_service_impressions' => (string)($d['next_service_impressions'] ?? '') !== '' ? (float)$d['next_service_impressions'] : null,
            'status'            => 'closed',
            'notes'             => trim((string)($d['notes'] ?? '')) ?: null,
            'recorded_by'       => $this->session->userdata('inv_username') ?: 'System',
        ];

        $this->db->insert('db_print_machine_maintenance', $row);
        $maintenance_id = $this->db->insert_id();
        if (!$maintenance_id) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Could not record the visit.'];
        }

        // Attach the parts. uq_visit_supply stops one supply being expensed
        // through two different visits.
        foreach ($prepared_parts as $p) {
            $p['store_id'] = (int)$machine->store_id;
            $p['maintenance_id'] = $maintenance_id;
            $this->db->insert('db_print_maintenance_parts', $p);
        }

        // Readings taken at service. A visit is a legitimate reading point, so it
        // flows through the SAME reading path — no special-case arithmetic.
        $reading_id = null;
        $mono = (string)($d['mono_at_service'] ?? '') !== '' ? (float)$d['mono_at_service'] : null;
        $colour = (string)($d['colour_at_service'] ?? '') !== '' ? (float)$d['colour_at_service'] : null;
        if ($mono !== null || $colour !== null) {
            $r = $this->record_reading($machine_id, [
                'reading_type'   => 'service',
                'mono_reading'   => $mono,
                'colour_reading' => $colour,
                'reading_date'   => $visit_date,
                'notes'          => 'Reading at service visit #' . $maintenance_id,
            ]);
            if (!empty($r['success'])) {
                $reading_id = $r['reading_id'];
                $this->db->where('id', $maintenance_id)->update('db_print_machine_maintenance', [
                    'reading_id'      => $reading_id,
                    'mono_at_service' => $mono,
                    'colour_at_service' => $colour,
                ]);
            }
        }

        // Roll the machine's own service schedule forward.
        $machine_update = ['last_service_at' => $visit_date];
        if (!empty($d['next_service_at'])) {
            $machine_update['next_service_at'] = $d['next_service_at'];
        } elseif (!empty($machine->service_interval_days)) {
            $machine_update['next_service_at'] = date('Y-m-d', strtotime($visit_date . ' +' . (int)$machine->service_interval_days . ' days'));
        }
        if ((string)($d['next_service_impressions'] ?? '') !== '') {
            $machine_update['next_service_impressions'] = (float)$d['next_service_impressions'];
        }
        if ((string)($d['next_service_at'] ?? '') !== '' || !empty($machine->service_interval_days)) {
            // A machine that has just been serviced is back in service.
            if ($machine->status === 'maintenance') {
                $machine_update['status'] = 'available';
                $machine_update['status_reason'] = null;
                $machine_update['status_changed_at'] = date('Y-m-d H:i:s');
                $machine_update['status_changed_by'] = $this->session->userdata('inv_username') ?: 'System';
            }
        }
        $this->db->where('id', $machine_id)->update('db_print_machines', $machine_update);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'The visit could not be saved — nothing was changed.'];
        }
        $this->db->trans_commit();

        return [
            'success'        => true,
            'maintenance_id' => $maintenance_id,
            'message'        => 'Maintenance visit recorded.',
            'total_cost'     => $total,
            'parts_issued_value' => $parts_from_inventory_value,
            'note'           => $parts_from_inventory_value > 0
                ? 'Parts worth ' . $parts_from_inventory_value . ' came from stores and are NOT included in the visit cost — their stock was already consumed when issued.'
                : null,
        ];
    }

    public function get_maintenance($machine_id = null, $store_id = null, $limit = 200) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $this->db->select('v.*, m.name AS machine_name, m.machine_code', false);
        $this->db->from('db_print_machine_maintenance v');
        $this->db->join('db_print_machines m', 'm.id = v.machine_id', 'left');
        $this->db->where('v.store_id', $store_id);
        if ($machine_id) $this->db->where('v.machine_id', $machine_id);
        return $this->db->order_by('v.visit_date', 'desc')->limit($limit)->get()->result();
    }

    public function get_maintenance_visit($id) {
        $v = $this->db->select('v.*, m.name AS machine_name, m.machine_code', false)
            ->from('db_print_machine_maintenance v')
            ->join('db_print_machines m', 'm.id = v.machine_id', 'left')
            ->where('v.id', $id)->get()->row();
        if (!$v) return null;
        $v->parts = $this->db->where('maintenance_id', $id)->get('db_print_maintenance_parts')->result();
        return $v;
    }

    /**
     * Maintenance costs for a machine, split so nothing is double counted.
     *
     *   visit_expenditure  — money the visit actually spent
     *   parts_from_stores  — value of parts already consumed from stock (NOT in the above)
     *   combined           — both, for a total picture that is still explicit
     */
    public function maintenance_summary($machine_id, $from = null, $to = null) {
        $this->db->select('COUNT(*) AS visits, COALESCE(SUM(labour_cost),0) AS labour,
                           COALESCE(SUM(parts_cost),0) AS parts_bought,
                           COALESCE(SUM(outsource_cost),0) AS outsource,
                           COALESCE(SUM(other_cost),0) AS other,
                           COALESCE(SUM(total_cost),0) AS total,
                           COALESCE(SUM(parts_issued_value),0) AS parts_from_stores,
                           COALESCE(SUM(downtime_minutes),0) AS downtime', false);
        $this->db->where('machine_id', $machine_id);
        if ($from) $this->db->where('visit_date >=', $from);
        if ($to) $this->db->where('visit_date <=', $to);
        $r = $this->db->get('db_print_machine_maintenance')->row();
        if ($r) {
            $r->visit_expenditure = (float)$r->total;
            $r->combined = round((float)$r->total + (float)$r->parts_from_stores, 2);
        }
        return $r;
    }

    /**
     * Machines due for service — by calendar OR by counter.
     *
     * A counter-based due date can only be evaluated when readings exist; a
     * machine with no readings yet is reported as "no reading" rather than
     * silently treated as current.
     */
    public function machines_due_service($store_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $rows = $this->db->where('store_id', $store_id)->where('status_active', 1)->get('db_print_machines')->result();
        $today = date('Y-m-d');
        $due = [];
        foreach ($rows as $m) {
            if (!empty($m->next_service_at) && $m->next_service_at <= $today) {
                $due[] = (object)[
                    'machine_id' => $m->id, 'machine_code' => $m->machine_code, 'name' => $m->name,
                    'reason' => 'Service due ' . $m->next_service_at, 'basis' => 'calendar',
                    'status' => $m->status,
                ];
                continue;
            }
            if (!empty($m->service_interval_impressions) && !empty($m->next_service_impressions)) {
                $act = $this->counter_activity($m->id);
                $since = $this->db->where('machine_id', $m->id)->where('status', 'recorded')
                    ->where('delta_valid', 1)->where('opening_baseline', 0);
                if (!empty($m->last_service_at)) {
                    $since = $since->where('reading_date >=', $m->last_service_at);
                }
                $col = $since->select('COALESCE(SUM(mono_delta),0) AS mono', false)->get('db_print_machine_readings')->row();
                $have = (float)($col->mono ?? 0);
                if ($have >= (float)$m->next_service_impressions) {
                    $due[] = (object)[
                        'machine_id' => $m->id, 'machine_code' => $m->machine_code, 'name' => $m->name,
                        'reason' => 'Counter reached ' . $have . ' since last service (target ' . (float)$m->next_service_impressions . ')',
                        'basis' => 'counter', 'status' => $m->status,
                    ];
                }
            }
        }
        return $due;
    }

    /**
     * Machine report — the sections the audit requires kept SEPARATE.
     *
     * Counter activity is reported next to accepted output and never merged into
     * it. Supplies issued are reported next to consumption reported. Allocations
     * are labelled estimated. Parts and maintenance expenditure are shown with
     * parts-from-stores called out so inventory cost, job cost and expense can
     * be told apart.
     */
    public function machine_report($machine_id, $from = null, $to = null) {
        $machine = $this->get_machine($machine_id);
        if (!$machine) return null;

        $store_id = (int)$machine->store_id;

        // 1. Counter activity (delta_valid rows only) and accepted output (from runs).
        $activity = $this->counter_activity($machine_id, $from, $to);

        $this->db->select('COUNT(*) AS runs, COALESCE(SUM(l.accepted_qty),0) AS accepted,
                           COALESCE(SUM(l.qty_in),0) AS input_qty,
                           COALESCE(SUM(l.reject_qty),0) AS rejects,
                           COALESCE(SUM(l.waste_qty),0) AS waste,
                           COALESCE(SUM(l.rework_qty),0) AS rework,
                           COALESCE(SUM(l.counter_activity),0) AS run_activity', false);
        $this->db->where('l.machine_id', $machine_id)->where('l.status !=', 'reversed');
        if ($from) $this->db->where('l.work_date >=', $from);
        if ($to) $this->db->where('l.work_date <=', $to);
        $output = $this->db->get('db_print_stage_logs l')->row();

        // 2. Jobs / stages / runs performed.
        $this->db->select('j.id, j.job_code, j.title, s.stage_label, COUNT(l.id) AS runs,
                           COALESCE(SUM(l.accepted_qty),0) AS accepted,
                           COALESCE(SUM(l.counter_activity),0) AS activity', false);
        $this->db->from('db_print_stage_logs l');
        $this->db->join('db_print_jobs j', 'j.id = l.job_id', 'left');
        $this->db->join('db_print_stages s', 's.id = l.stage_id', 'left');
        $this->db->where('l.machine_id', $machine_id)->where('l.status !=', 'reversed');
        if ($from) $this->db->where('l.work_date >=', $from);
        if ($to) $this->db->where('l.work_date <=', $to);
        $jobs = $this->db->group_by(['j.id', 'j.job_code', 'j.title', 's.stage_label'])
            ->order_by('j.job_code', 'desc')->get()->result();

        // 3. Supplies issued vs consumption reported.
        $this->db->select('COALESCE(SUM(issued_cost),0) AS issued_value,
                           COALESCE(SUM(consumed_cost),0) AS consumed_value,
                           COALESCE(SUM(allocated_cost),0) AS allocated_value,
                           COUNT(*) AS records', false);
        $this->db->where('machine_id', $machine_id)->where('opening_loaded', 0);
        if ($from) $this->db->where('DATE(issued_at) >=', $from);
        if ($to) $this->db->where('DATE(issued_at) <=', $to);
        $supplies = $this->db->get('db_print_machine_supplies')->row();

        $this->db->select('COALESCE(SUM(amount),0) AS estimated_allocated, COUNT(*) AS allocations', false);
        $this->db->from('db_print_consumable_allocations a');
        $this->db->join('db_print_machine_supplies s', 's.id = a.consumable_id', 'inner');
        $this->db->where('s.machine_id', $machine_id);
        $alloc = $this->db->get()->row();

        // 4. Parts and maintenance expenditure.
        $maint = $this->maintenance_summary($machine_id, $from, $to);

        // 5. Waste, downtime and unresolved discrepancies.
        $this->db->select('COALESCE(SUM(downtime_minutes),0) AS downtime', false);
        $this->db->where('machine_id', $machine_id);
        if ($from) $this->db->where('visit_date >=', $from);
        if ($to) $this->db->where('visit_date <=', $to);
        $downtime = $this->db->get('db_print_machine_maintenance')->row();

        $discrepancies = $this->db->where('machine_id', $machine_id)
            ->where('reading_discrepancy IS NOT NULL', null, false)
            ->where('status !=', 'reversed')->count_all_results('db_print_stage_logs');
        $invalid = $this->db->where('machine_id', $machine_id)->where('delta_valid', 0)
            ->where('status', 'recorded')->count_all_results('db_print_machine_readings');

        return [
            'machine'        => $machine,
            'counter'        => [
                'mono'     => (float)($activity->mono ?? 0),
                'colour'   => (float)($activity->colour ?? 0),
                'activity' => (float)($activity->total ?? 0),
                'readings' => (int)($activity->readings ?? 0),
            ],
            'output'         => [
                'accepted' => (float)($output->accepted ?? 0),
                'input'    => (float)($output->input_qty ?? 0),
                'rejects'  => (float)($output->rejects ?? 0),
                'waste'    => (float)($output->waste ?? 0),
                'rework'   => (float)($output->rework ?? 0),
                'runs'     => (int)($output->runs ?? 0),
            ],
            'jobs'           => $jobs,
            'supplies'       => [
                'issued_value'      => (float)($supplies->issued_value ?? 0),
                'consumed_value'    => (float)($supplies->consumed_value ?? 0),
                'allocated_value'   => (float)($supplies->allocated_value ?? 0),
                'records'           => (int)($supplies->records ?? 0),
                'estimated_allocated' => (float)($alloc->estimated_allocated ?? 0),
                'allocation_count'  => (int)($alloc->allocations ?? 0),
            ],
            'maintenance'    => [
                'visits'            => (int)($maint->visits ?? 0),
                'labour'            => (float)($maint->labour ?? 0),
                'parts_bought'      => (float)($maint->parts_bought ?? 0),
                'outsource'         => (float)($maint->outsource ?? 0),
                'other'             => (float)($maint->other ?? 0),
                'expenditure'       => (float)($maint->total ?? 0),
                'parts_from_stores' => (float)($maint->parts_from_stores ?? 0),
                'combined'          => (float)($maint->combined ?? 0),
            ],
            'issues'         => [
                'downtime_minutes' => (int)($downtime->downtime ?? 0),
                'waste_qty'        => (float)($output->waste ?? 0),
                'discrepancies'    => (int)$discrepancies,
                'invalid_readings' => (int)$invalid,
            ],
            'range'          => ['from' => $from, 'to' => $to],
        ];
    }

    /* ==================================================================== */
    /*  SECTION 5 — customer-owned material custody                          */
    /* ==================================================================== */

    /**
     * Receive customer-owned material into custody.
     *
     * A job is OPTIONAL on purpose: material genuinely arrives before the job is
     * written up, and demanding a job first is how shops end up recording it
     * nowhere. When job_id is absent the receipt sits as an UNALLOCATED customer
     * balance and is allocated later, explicitly.
     *
     * A garment receipt carries size and/or colour breakdowns. When a breakdown
     * is given and does not reconcile to the received quantity, the mismatch is
     * FLAGGED — never silently corrected, because the discrepancy is real
     * information about what the customer actually handed over.
     */
    public function receive_customer_material(array $d) {
        $store_id = get_current_store_id();
        $name = trim((string)($d['material_name'] ?? ''));
        $qty = (float)($d['qty_received'] ?? 0);
        if ($name === '') return ['success' => false, 'message' => 'Describe the material being received.'];
        if ($qty <= 0) return ['success' => false, 'message' => 'Enter the quantity received.'];

        $sizes = $this->normalise_breakdown($d['size_breakdown'] ?? []);
        $colours = $this->normalise_breakdown($d['colour_breakdown'] ?? []);

        // Sizes and colours describe the SAME pieces, so each breakdown must
        // reconcile to the receipt on its own; adding them together would count
        // the garments twice.
        $mismatch = 0;
        if ($sizes && abs(array_sum($sizes) - $qty) > 0.0001) $mismatch = 1;
        if ($colours && abs(array_sum($colours) - $qty) > 0.0001) $mismatch = 1;
        $breakdown_total = max(array_sum($sizes), array_sum($colours));

        $job_id = !empty($d['job_id']) ? (int)$d['job_id'] : null;

        $this->db->trans_begin();

        $receipt_code = $this->next_receipt_code($store_id);
        $row = [
            'store_id'        => $store_id,
            'customer_id'     => !empty($d['customer_id']) ? (int)$d['customer_id'] : null,
            'job_id'          => $job_id,
            'line_id'         => !empty($d['line_id']) ? (int)$d['line_id'] : null,
            'receipt_code'    => $receipt_code,
            'material_name'   => $name,
            'material_type'   => array_key_exists((string)($d['material_type'] ?? ''), ['garment','fabric','paper','substrate','other'])
                                    ? $d['material_type'] : 'other',
            'item_id'         => !empty($d['item_id']) ? (int)$d['item_id'] : null,
            'unit_id'         => !empty($d['unit_id']) ? (int)$d['unit_id'] : null,
            'unit_label'      => trim((string)($d['unit_label'] ?? '')) ?: null,
            'qty_received'    => $qty,
            'condition_in'    => array_key_exists((string)($d['condition_in'] ?? ''), ['good','fair','damaged','mixed','unknown'])
                                    ? $d['condition_in'] : 'good',
            'condition_note'  => trim((string)($d['condition_note'] ?? '')) ?: null,
            'size_breakdown_json'   => $sizes ? json_encode($sizes) : null,
            'colour_breakdown_json' => $colours ? json_encode($colours) : null,
            'breakdown_total' => $breakdown_total,
            'breakdown_mismatch' => $mismatch,
            'received_by'     => $this->session->userdata('inv_username') ?: 'System',
            'received_at'     => date('Y-m-d H:i:s'),
            'acknowledged_by' => trim((string)($d['acknowledged_by'] ?? '')) ?: null,
            'acknowledged_at' => !empty($d['acknowledged_by']) ? date('Y-m-d H:i:s') : null,
            'ack_reference'   => trim((string)($d['ack_reference'] ?? '')) ?: null,
            'status'          => $job_id ? 'allocated' : 'open',
            // The receipt lands entirely in CUSTODY — it has not been issued to
            // production yet, and receiving is not producing.
            'qty_custody'     => $qty,
            'notes'           => trim((string)($d['notes'] ?? '')) ?: null,
            'created_by'      => $this->session->userdata('inv_username') ?: 'System',
        ];
        $this->db->insert('db_print_customer_materials', $row);
        $material_id = $this->db->insert_id();

        if (!$material_id) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Could not record the receipt.'];
        }

        $this->insert_custody_move([
            'material_id'   => $material_id,
            'move_type'     => 'receive',
            'from_position' => null,
            'to_position'   => 'custody',
            'qty'           => $qty,
            'size_breakdown'   => $sizes,
            'colour_breakdown' => $colours,
            'work_date'     => date('Y-m-d'),
            'evidence_note' => 'Initial receipt',
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Receipt failed — nothing was recorded.'];
        }
        $this->db->trans_commit();

        return [
            'success'      => true,
            'material_id'  => $material_id,
            'receipt_code' => $receipt_code,
            'message'      => 'Customer material received into custody.',
            'warning'      => $mismatch
                ? 'The size/colour breakdown does not reconcile to ' . $qty . '. It has been recorded and flagged for review rather than corrected.'
                : null,
        ];
    }

    /**
     * Move customer-owned material between positions.
     *
     * Every move is a signed pair of position deltas written in ONE transaction
     * with a running-balance check, so the positions always reconcile against the
     * receipt. A move that would take a position negative is refused outright —
     * material cannot be issued twice just because two screens asked.
     */
    public function move_custody($material_id, $move_type, $qty = null, array $d = []) {
        $mat = $this->db->where('id', $material_id)->get('db_print_customer_materials')->row();
        if (!$mat) return ['success' => false, 'message' => 'Material record not found.'];

        $map = [
            'issue'             => ['custody', 'in_production'],
            'finish'            => ['in_production', 'finished'],
            'damage'            => [null, 'damaged'],
            'consume'           => ['in_production', 'consumed'],
            'handover_finished' => ['finished', 'collected'],
            'handover_unused'   => ['custody', 'returned'],
            'cancel'            => [null, null],
            'adjust'            => [null, null],
            'allocate'          => [null, null],
        ];
        if (!array_key_exists($move_type, $map)) {
            return ['success' => false, 'message' => 'Unknown custody movement.'];
        }

        $qty = ($qty === null || $qty === '') ? 0 : (float)$qty;

        // Damage, adjustment and cancellation are the movements that LOSE
        // material, so they require a reason and an authorized review.
        $needs_reason = in_array($move_type, ['damage', 'adjust', 'cancel'], true);
        $reason = trim((string)($d['reason'] ?? ''));
        if ($needs_reason && $reason === '') {
            return ['success' => false, 'message' => 'A reason is required for a ' . $move_type . ' — the discrepancy must be explainable.'];
        }

        $deltas = [];
        $from_override = null;
        $to_override = null;

        if ($move_type === 'damage') {
            if ($qty <= 0) return ['success' => false, 'message' => 'Enter how many pieces were damaged.'];
            // Damage may come from custody or from production — it must not be
            // forced through a position the pieces never occupied.
            $from_pos = (string)($d['from_position'] ?? '');
            if ($from_pos !== '' && $from_pos !== 'custody' && $from_pos !== 'in_production') {
                return ['success' => false, 'message' => 'Damaged material comes from custody or from production.'];
            }
            if ($from_pos === '') {
                $from_pos = ((float)$mat->qty_custody >= $qty) ? 'custody' : 'in_production';
            }
            if ((float)$mat->{$this->position_column[$from_pos]} < $qty - 0.0001) {
                return ['success' => false, 'message' => 'Only ' . (float)$mat->{$this->position_column[$from_pos]} . ' is in that position; ' . $qty . ' cannot be moved to damaged.'];
            }
            $deltas[$from_pos] = -$qty;
            $deltas['damaged'] = $qty;
            $from_override = $from_pos;
            $to_override = 'damaged';

        } elseif ($move_type === 'adjust') {
            if ($qty == 0) return ['success' => false, 'message' => 'Enter the adjustment quantity (positive or negative).'];
            $pos = (string)($d['position'] ?? 'custody');
            if (!isset($this->position_column[$pos]) || $pos === 'other_jobs') {
                return ['success' => false, 'message' => 'Choose a valid position to adjust.'];
            }
            $deltas[$pos] = $qty;
            // Direction is what distinguishes an addition from a write-off, and
            // the movement log stores absolute quantities — so the positions are
            // recorded explicitly. A negative adjustment leaves the ledger.
            $from_override = $qty < 0 ? $pos : null;
            $to_override = $qty > 0 ? $pos : null;

        } elseif ($move_type === 'cancel') {
            // Cancelling a job returns everything still in custody or in
            // production to the customer as UNUSED material. Pieces already
            // finished or handed over are untouched — cancelling remaining work
            // is not a reversal of work already done.
            $qty = (float)$mat->qty_custody + (float)$mat->qty_in_production;
            if ($qty <= 0) return ['success' => false, 'message' => 'Nothing is still in custody or in production to cancel.'];
            $deltas['custody'] = -(float)$mat->qty_custody;
            $deltas['in_production'] = -(float)$mat->qty_in_production;
            $deltas['returned'] = $qty;
            $from_override = 'custody+in_production';
            $to_override = 'returned';

        } elseif ($move_type === 'allocate') {
            return $this->allocate_custody($mat, $d, $qty);

        } else {
            list($from_pos, $to_pos) = $map[$move_type];
            if ($qty <= 0) return ['success' => false, 'message' => 'Enter a quantity for this movement.'];
            $deltas[$from_pos] = -$qty;
            $deltas[$to_pos] = $qty;
            $from_override = $from_pos;
            $to_override = $to_pos;
        }

        // Reconcile before writing: no position may go negative.
        foreach ($deltas as $pos => $delta) {
            if (!isset($this->position_column[$pos])) continue;
            $current = (float)$mat->{$this->position_column[$pos]};
            if ($current + $delta < -0.0001) {
                $label = self::custody_positions()[$pos] ?? $pos;
                return ['success' => false, 'message' => 'Not enough material in "' . $label . '" — ' . number_format($current, 3) . ' available, ' . number_format(abs($delta), 3) . ' requested.'];
            }
        }

        $this->db->trans_begin();

        if ($deltas) {
            // Conditional claim on the running positions so two concurrent
            // movements cannot both consume the same holding.
            $this->db->where('id', $material_id);
            foreach ($deltas as $pos => $delta) {
                if (!isset($this->position_column[$pos])) continue;
                $this->db->where($this->position_column[$pos], $mat->{$this->position_column[$pos]});
            }
            $sets = [];
            foreach ($deltas as $pos => $delta) {
                if (!isset($this->position_column[$pos])) continue;
                $col = $this->position_column[$pos];
                $sets[$col] = (float)$mat->$col + $delta;
            }
            $this->db->update('db_print_customer_materials', $sets);
            if ($this->db->affected_rows() !== 1) {
                $this->db->trans_rollback();
                return ['success' => false, 'message' => 'Another movement changed this material at the same moment. Nothing was recorded — reload and try again.'];
            }
        }

        $this->insert_custody_move([
            'material_id'   => $material_id,
            'move_type'     => $move_type,
            'from_position' => $from_override,
            'to_position'   => $to_override,
            'qty'           => abs($qty),
            'size_breakdown'   => $this->normalise_breakdown($d['size_breakdown'] ?? []),
            'colour_breakdown' => $this->normalise_breakdown($d['colour_breakdown'] ?? []),
            'stage_id'      => !empty($d['stage_id']) ? (int)$d['stage_id'] : null,
            'stage_log_id'  => !empty($d['stage_log_id']) ? (int)$d['stage_log_id'] : null,
            'work_date'     => !empty($d['work_date']) ? $d['work_date'] : date('Y-m-d'),
            'reason'        => $reason ?: null,
            'authorized_by' => trim((string)($d['authorized_by'] ?? '')) ?: null,
            'review_status' => $needs_reason ? 'pending' : 'n/a',
            'outcome_type'  => $needs_reason ? (trim((string)($d['outcome_type'] ?? '')) ?: 'undecided') : 'n/a',
            'outcome_note'  => trim((string)($d['outcome_note'] ?? '')) ?: null,
            'outcome_cost'  => (float)($d['outcome_cost'] ?? 0),
            'handover_to'   => trim((string)($d['handover_to'] ?? '')) ?: null,
            'handover_reference' => trim((string)($d['handover_reference'] ?? '')) ?: null,
            'evidence_note' => trim((string)($d['evidence_note'] ?? '')) ?: null,
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'The movement failed and was rolled back.'];
        }
        $this->db->trans_commit();

        return [
            'success' => true,
            'message' => 'Custody updated.',
            'balance' => $this->custody_balance($this->db->where('id', $material_id)->get('db_print_customer_materials')->row()),
            'warning' => $needs_reason
                ? 'Recorded with an authorized review still pending. The agreed outcome can be added later without changing the movement.'
                : null,
        ];
    }

    /**
     * Attach an UNALLOCATED customer balance to a job, or move it between two of
     * the same customer's jobs. Cross-customer use is refused outright.
     *
     * This is the path that makes "material arrived before the job existed" safe:
     * the balance is visibly unallocated until somebody allocates it on purpose.
     */
    private function allocate_custody($mat, array $d, $qty = null) {
        $target_job = !empty($d['job_id']) ? (int)$d['job_id'] : 0;
        if (!$target_job) return ['success' => false, 'message' => 'Choose the job to allocate to.'];
        // The quantity arrives as the positional argument from move_custody; the
        // array form is still honoured so callers can pass it either way.
        $qty = ($qty === null || $qty === '') ? (float)($d['qty'] ?? 0) : (float)$qty;
        if ($qty <= 0) return ['success' => false, 'message' => 'Enter how much to allocate.'];

        $job = $this->db->select('id, customer_id, store_id, job_code')->where('id', $target_job)->get('db_print_jobs')->row();
        if (!$job) return ['success' => false, 'message' => 'Job not found.'];
        if ((int)$job->store_id !== (int)$mat->store_id) {
            return ['success' => false, 'message' => 'That job belongs to a different store.'];
        }
        if ($mat->customer_id && (int)$job->customer_id && (int)$mat->customer_id !== (int)$job->customer_id) {
            return ['success' => false, 'message' => 'This material belongs to a different customer and cannot be used on that job.'];
        }
        if ((float)$mat->qty_custody < $qty - 0.0001) {
            return ['success' => false, 'message' => 'Only ' . (float)$mat->qty_custody . ' is available to allocate; ' . $qty . ' requested.'];
        }

        if (empty($mat->job_id)) {
            // An unallocated receipt is attached to a job. The pieces do not
            // move — only which job owns the balance changes — so no position
            // delta is written, and the movement log records the event.
            $this->db->where('id', $mat->id)->update('db_print_customer_materials', [
                'job_id' => $target_job,
                'status' => 'allocated',
            ]);
            $this->insert_custody_move([
                'material_id'   => $mat->id,
                'move_type'     => 'allocate',
                'from_position' => 'unallocated',
                'to_position'   => 'job',
                'qty'           => $qty,
                'job_id'        => $target_job,
                'reason'        => trim((string)($d['reason'] ?? '')) ?: null,
                'work_date'     => !empty($d['work_date']) ? $d['work_date'] : date('Y-m-d'),
                'evidence_note' => 'Unallocated balance allocated to ' . ($job->job_code ?: ('job #' . $target_job)),
            ]);
            return [
                'success' => true,
                'message' => 'Unallocated customer material allocated to ' . ($job->job_code ?: ('job #' . $target_job)) . '.',
                'balance' => $this->custody_balance($this->db->where('id', $mat->id)->get('db_print_customer_materials')->row()),
            ];
        }

        if ((int)$mat->job_id === $target_job) {
            return ['success' => false, 'message' => 'This material is already on that job.'];
        }

        // Between two of the same customer's jobs: stays in the customer's
        // custody, tracked separately so neither job can claim it twice.
        $deltas = ['custody' => -$qty, 'other_jobs' => $qty];
        foreach ($deltas as $pos => $delta) {
            if ((float)$mat->{$this->position_column[$pos]} + $delta < -0.0001) {
                return ['success' => false, 'message' => 'Only ' . (float)$mat->qty_custody . ' is available to transfer.'];
            }
        }

        $this->db->trans_begin();
        $this->db->where('id', $mat->id);
        foreach ($deltas as $pos => $delta) {
            $this->db->where($this->position_column[$pos], $mat->{$this->position_column[$pos]});
        }
        $sets = [];
        foreach ($deltas as $pos => $delta) {
            $col = $this->position_column[$pos];
            $sets[$col] = (float)$mat->$col + $delta;
        }
        $this->db->update('db_print_customer_materials', $sets);
        if ($this->db->affected_rows() !== 1) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Another movement changed this material at the same moment. Nothing was recorded.'];
        }
        $this->insert_custody_move([
            'material_id'   => $mat->id,
            'move_type'     => 'transfer_in',
            'from_position' => 'custody', 'to_position' => 'other_jobs',
            'qty'           => $qty, 'job_id' => $target_job,
            'reason'        => trim((string)($d['reason'] ?? '')) ?: null,
            'work_date'     => !empty($d['work_date']) ? $d['work_date'] : date('Y-m-d'),
            'evidence_note' => 'Transferred to ' . ($job->job_code ?: ('job #' . $target_job)),
        ]);
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'The transfer failed and was rolled back.'];
        }
        $this->db->trans_commit();
        return [
            'success' => true,
            'message' => 'Material transferred to ' . ($job->job_code ?: ('job #' . $target_job)) . '.',
            'balance' => $this->custody_balance($this->db->where('id', $mat->id)->get('db_print_customer_materials')->row()),
        ];
    }

    /**
     * Record the agreed OUTCOME of a damage / adjustment / discrepancy.
     *
     * Kept separate from the movement on purpose: the movement says what
     * happened to the goods, the outcome says what was agreed about it
     * (disposal, replacement, compensation). Compensation cost belongs to the
     * JOB, never to company material, because the goods were never ours.
     */
    public function resolve_custody_issue($move_id, array $d) {
        $move = $this->db->where('id', $move_id)->get('db_print_custody_moves')->row();
        if (!$move) return ['success' => false, 'message' => 'Movement not found.'];
        if ($move->review_status === 'n/a') {
            return ['success' => false, 'message' => 'This movement has no issue to resolve.'];
        }
        $outcome = (string)($d['outcome_type'] ?? '');
        if (!in_array($outcome, ['disposal','replacement','compensation','discount','no_liability','undecided'], true)) {
            return ['success' => false, 'message' => 'Choose an agreed outcome.'];
        }
        $reviewed_by = trim((string)($d['reviewed_by'] ?? ''));
        if ($reviewed_by === '') {
            return ['success' => false, 'message' => 'Record who reviewed and agreed the outcome.'];
        }

        $this->db->where('id', $move_id)->update('db_print_custody_moves', [
            'outcome_type'  => $outcome,
            'outcome_note'  => trim((string)($d['outcome_note'] ?? '')) ?: null,
            'outcome_cost'  => max(0, (float)($d['outcome_cost'] ?? 0)),
            'reviewed_by'   => $reviewed_by,
            'reviewed_at'   => date('Y-m-d H:i:s'),
            'review_status' => 'reviewed',
        ]);
        return [
            'success' => true,
            'message' => 'Outcome recorded.',
            'note'    => ((float)($d['outcome_cost'] ?? 0) > 0)
                ? 'Compensation cost belongs to the job — it is not a cost of company material.'
                : null,
        ];
    }

    public function get_customer_materials($store_id = null, array $f = []) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $this->db->select('cm.*, c.customer_name, j.job_code, j.title', false);
        $this->db->from('db_print_customer_materials cm');
        $this->db->join('db_customers c', 'c.id = cm.customer_id', 'left');
        $this->db->join('db_print_jobs j', 'j.id = cm.job_id', 'left');
        $this->db->where('cm.store_id', $store_id);
        if (!empty($f['customer_id'])) $this->db->where('cm.customer_id', (int)$f['customer_id']);
        if (!empty($f['job_id'])) $this->db->where('cm.job_id', (int)$f['job_id']);
        if (!empty($f['status'])) $this->db->where('cm.status', $f['status']);
        if (!empty($f['unallocated'])) $this->db->where('cm.job_id IS NULL', null, false);
        if (!empty($f['outstanding'])) {
            // Anything still physically with us: custody, in production, or
            // finished awaiting collection. Returned/collected/consumed is done.
            $this->db->where('(cm.qty_custody > 0 OR cm.qty_in_production > 0 OR cm.qty_finished > 0)', null, false);
        }
        return $this->db->order_by('cm.id', 'desc')->get()->result();
    }

    public function get_customer_material($id) {
        $m = $this->db->select('cm.*, c.customer_name, j.job_code, j.title', false)
            ->from('db_print_customer_materials cm')
            ->join('db_customers c', 'c.id = cm.customer_id', 'left')
            ->join('db_print_jobs j', 'j.id = cm.job_id', 'left')
            ->where('cm.id', $id)->get()->row();
        if (!$m) return null;
        $m->size_breakdown = !empty($m->size_breakdown_json) ? json_decode($m->size_breakdown_json, true) : [];
        $m->colour_breakdown = !empty($m->colour_breakdown_json) ? json_decode($m->colour_breakdown_json, true) : [];
        $m->moves = $this->custody_moves($id);
        $m->balance = $this->custody_balance($m);
        return $m;
    }

    /** Unallocated customer balance: material received before a job existed. */
    public function unallocated_materials($customer_id = null, $store_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $this->db->select('cm.*, c.customer_name', false)
            ->from('db_print_customer_materials cm')
            ->join('db_customers c', 'c.id = cm.customer_id', 'left')
            ->where('cm.store_id', $store_id)
            ->where('cm.job_id IS NULL', null, false)
            ->where('cm.qty_custody >', 0)
            ->where('cm.status !=', 'cancelled');
        if ($customer_id) $this->db->where('cm.customer_id', (int)$customer_id);
        return $this->db->order_by('cm.id', 'asc')->get()->result();
    }

    /**
     * Position summary for a material — the reconciliation.
     *
     * received == accounted is the invariant for material that is still held or
     * has been handed back. A deliberate WRITE-OFF (an authorized negative
     * adjustment) legitimately breaks it: the goods are gone and the ledger must
     * say so rather than hide it. That difference is reported as
     * `written_off` and leaves `reconciled` false so a human sees it, instead of
     * being quietly absorbed.
     */
    public function custody_balance($mat) {
        if (!$mat) return null;
        $p = [
            'custody'       => (float)$mat->qty_custody,
            'in_production' => (float)$mat->qty_in_production,
            'finished'      => (float)$mat->qty_finished,
            'damaged'       => (float)$mat->qty_damaged,
            'returned'      => (float)$mat->qty_returned,
            'collected'     => (float)$mat->qty_collected_finished,
            'consumed'      => (float)$mat->qty_consumed,
            'other_jobs'    => (float)$mat->qty_allocated_other_jobs,
        ];
        $accounted = array_sum($p);
        $received = (float)$mat->qty_received;

        // Authorized negative adjustments are recorded write-offs: material that
        // deliberately left the ledger. They are summed separately so the
        // reconciliation can be explained instead of merely failing.
        //
        // The movement log stores the absolute quantity (a movement is a
        // quantity), so a write-off is identified by its direction instead:
        // an 'adjust' whose FROM position is set and whose TO position is not
        // took material out of the ledger.
        $written_off = 0.0;
        if ($this->db->table_exists('db_print_custody_moves')) {
            $r = $this->db->select('COALESCE(SUM(qty),0) AS q', false)
                ->where('material_id', $mat->id)->where('move_type', 'adjust')
                ->where('from_position IS NOT NULL', null, false)
                ->where('to_position IS NULL', null, false)
                ->get('db_print_custody_moves')->row();
            $written_off = abs((float)($r->q ?? 0));
        }

        $discrepancy = round($received - $accounted, 3);
        return [
            'positions'   => $p,
            'received'    => $received,
            'accounted'   => round($accounted, 3),
            'written_off' => $written_off,
            'discrepancy' => $discrepancy,
            // Reconciled when the only gap is explained by recorded write-offs.
            'reconciled'  => abs($received - ($accounted + $written_off)) < 0.0001,
            'fully_accounted' => abs($discrepancy) < 0.0001,
            // "Still with us" is not "not yet accounted for" — these are the
            // pieces physically on the premises.
            'on_premises' => round($p['custody'] + $p['in_production'] + $p['finished'], 3),
        ];
    }

    /** Customer-level custody statement across all their materials. */
    public function customer_custody_statement($customer_id, $store_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $rows = $this->get_customer_materials($store_id, ['customer_id' => $customer_id]);
        $totals = array_fill_keys(array_keys(self::custody_positions()), 0.0);
        $received = 0.0;
        $accounted = 0.0;
        $written_off = 0.0;
        $materials = [];
        foreach ($rows as $r) {
            $b = $this->custody_balance($r);
            foreach ($b['positions'] as $k => $v) $totals[$k] += $v;
            $received += $b['received'];
            $accounted += $b['accounted'];
            $written_off += $b['written_off'];
            $r->balance = $b;
            $materials[] = $r;
        }
        return [
            'customer_id' => $customer_id,
            'materials'   => $materials,
            'totals'      => array_map(function ($v) { return round($v, 3); }, $totals),
            'received'    => round($received, 3),
            'accounted'   => round($accounted, 3),
            'written_off' => round($written_off, 3),
            'discrepancy' => round($received - $accounted, 3),
            'reconciled'  => abs($received - ($accounted + $written_off)) < 0.0001,
            'on_premises' => round($totals['custody'] + $totals['in_production'] + $totals['finished'], 3),
        ];
    }

    /** Movements for a material, oldest first. */
    public function custody_moves($material_id) {
        return $this->db->where('material_id', $material_id)->order_by('id', 'asc')->get('db_print_custody_moves')->result();
    }

    /** Damage/adjustment movements still awaiting an authorized review. */
    public function pending_custody_reviews($store_id = null, $limit = 100) {
        if (empty($store_id)) $store_id = get_current_store_id();
        return $this->db->select('mv.*, cm.material_name, cm.receipt_code, cm.qty_received, c.customer_name, j.job_code', false)
            ->from('db_print_custody_moves mv')
            ->join('db_print_customer_materials cm', 'cm.id = mv.material_id', 'left')
            ->join('db_customers c', 'c.id = cm.customer_id', 'left')
            ->join('db_print_jobs j', 'j.id = cm.job_id', 'left')
            ->where('mv.store_id', $store_id)
            ->where('mv.review_status', 'pending')
            ->order_by('mv.created_at', 'desc')->limit($limit)->get()->result();
    }

    /** Breakdowns whose recorded sizes/colours do not reconcile to the receipt. */
    public function breakdown_mismatches($store_id = null, $limit = 100) {
        if (empty($store_id)) $store_id = get_current_store_id();
        return $this->db->where('store_id', $store_id)->where('breakdown_mismatch', 1)
            ->order_by('id', 'desc')->limit($limit)->get('db_print_customer_materials')->result();
    }

    /** Store-wide position summary for the workshop dashboard. */
    public function material_position_summary($store_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $rows = $this->get_customer_materials($store_id, ['outstanding' => 1]);
        $out = ['custody' => 0.0, 'in_production' => 0.0, 'finished' => 0.0, 'items' => count($rows)];
        foreach ($rows as $r) {
            $out['custody'] += (float)$r->qty_custody;
            $out['in_production'] += (float)$r->qty_in_production;
            $out['finished'] += (float)$r->qty_finished;
        }
        foreach (['custody', 'in_production', 'finished'] as $k) $out[$k] = round($out[$k], 3);
        return $out;
    }

    /* ---------------------------------------------------------------- */
    /*  custody internals                                                */
    /* ---------------------------------------------------------------- */

    private function insert_custody_move(array $d) {
        $mat = $this->db->where('id', $d['material_id'])->get('db_print_customer_materials')->row();
        if (!$mat) return null;
        $row = [
            'store_id'      => (int)$mat->store_id,
            'material_id'   => (int)$d['material_id'],
            'customer_id'   => $mat->customer_id,
            'job_id'        => !empty($d['job_id']) ? (int)$d['job_id'] : $mat->job_id,
            'batch_ref'     => !empty($d['batch_ref']) ? $d['batch_ref'] : ('CM' . $d['material_id'] . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 8))),
            'move_type'     => $d['move_type'],
            'from_position' => $d['from_position'] ?? null,
            'to_position'   => $d['to_position'] ?? null,
            'qty'           => (float)($d['qty'] ?? 0),
            'size_breakdown_json'   => !empty($d['size_breakdown']) ? json_encode($d['size_breakdown']) : null,
            'colour_breakdown_json' => !empty($d['colour_breakdown']) ? json_encode($d['colour_breakdown']) : null,
            'stage_id'      => $d['stage_id'] ?? null,
            'stage_log_id'  => $d['stage_log_id'] ?? null,
            'work_date'     => $d['work_date'] ?? date('Y-m-d'),
            'reason'        => $d['reason'] ?? null,
            'authorized_by' => $d['authorized_by'] ?? null,
            'reviewed_by'   => $d['reviewed_by'] ?? null,
            'reviewed_at'   => $d['reviewed_at'] ?? null,
            'review_status' => $d['review_status'] ?? 'n/a',
            'outcome_type'  => $d['outcome_type'] ?? 'n/a',
            'outcome_note'  => $d['outcome_note'] ?? null,
            'outcome_cost'  => (float)($d['outcome_cost'] ?? 0),
            'reverses_move_id' => $d['reverses_move_id'] ?? null,
            'handover_to'   => $d['handover_to'] ?? null,
            'handover_reference' => $d['handover_reference'] ?? null,
            'evidence_note' => $d['evidence_note'] ?? null,
            'recorded_by'   => $this->session->userdata('inv_username') ?: 'System',
        ];
        $this->db->insert('db_print_custody_moves', $row);
        return $this->db->insert_id();
    }

    /** Size/colour breakdowns must be positive quantities keyed by label. */
    private function normalise_breakdown($raw) {
        if (empty($raw)) return [];
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }
        $out = [];
        foreach ((array)$raw as $k => $v) {
            $label = trim((string)$k);
            $qty = (float)$v;
            if ($label === '' || $qty <= 0) continue;
            $out[$label] = $qty;
        }
        return $out;
    }

    private function next_receipt_code($store_id) {
        $prefix = 'CM-' . date('Ymd') . '-';
        $last = $this->db->like('receipt_code', $prefix, 'after')->where('store_id', $store_id)
            ->order_by('id', 'DESC')->limit(1)->get('db_print_customer_materials')->row();
        $next = $last ? ((int)substr($last->receipt_code, strrpos($last->receipt_code, '-') + 1) + 1) : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
