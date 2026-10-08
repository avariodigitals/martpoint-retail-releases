<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Printing Industry Model
 *
 * Configurable print-shop workflow: one domain model, many print businesses via
 * category presets (large format, digital imaging/DI, DTF, apparel, screen
 * print, stationery). Jobs are stable, mixed-category, spec-snapshotted and
 * carried on the existing db_custom_orders commercial parent.
 *
 * Distinct states that must NEVER collapse (each is its own column):
 *   artwork_status        — customer approval of a specific artwork version
 *   design_status         — designer technical clearance
 *   authorization_status  — internal print authorization (version-fingerprinted)
 *   payment_status        — deposit/collection verification (Finance gate)
 *   production_status     — production pipeline state
 *   fulfilment_status     — collection / delivery state
 *
 * Financial records use the shared ledger db_salespayments. Stock moves ride
 * db_stockadjustment. Authorizations are version-fingerprinted so a new artwork
 * version invalidates prior ones.
 */
class Printing_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /* ============================ vocabularies ============================ */

    public static function category_presets() {
        return [
            'large_format' => [
                'name' => 'Large Format', 'description' => 'Billboards, signage, posters, banners, vehicle graphics',
                'size_units' => ['mm', 'cm', 'in', 'm'], 'supplies_material' => 1,
                'stage_preset' => ['prepress', 'print', 'finishing', 'qc'],
                'spec_schema' => [
                    ['key' => 'width', 'label' => 'Width', 'type' => 'number', 'required' => 1],
                    ['key' => 'height', 'label' => 'Height', 'type' => 'number', 'required' => 1],
                    ['key' => 'dim_unit', 'label' => 'Dimension Unit', 'type' => 'select', 'options' => ['mm', 'cm', 'in', 'm'], 'required' => 1],
                    ['key' => 'material', 'label' => 'Material', 'type' => 'text', 'required' => 1, 'placeholder' => 'e.g. PVC Flex, Vinyl, Mesh'],
                    ['key' => 'finish', 'label' => 'Finish', 'type' => 'select', 'options' => ['None', 'Eyelets', 'Hem & Eyelets', 'Pole Pockets', 'Lamination'], 'optional' => 1],
                    ['key' => 'sided', 'label' => 'Sides', 'type' => 'select', 'options' => ['Single-sided', 'Double-sided'], 'optional' => 1],
                ],
            ],
            'digital_imaging' => [
                'name' => 'Digital Imaging (DI)', 'description' => 'Digital prints, photo and fine-art output, flatbed',
                'size_units' => ['mm', 'cm', 'in'], 'supplies_material' => 0,
                'stage_preset' => ['prepress', 'print', 'finishing', 'qc'],
                'spec_schema' => [
                    ['key' => 'paper_gsm', 'label' => 'Paper GSM', 'type' => 'number', 'required' => 1, 'placeholder' => 'e.g. 130, 170, 300'],
                    ['key' => 'paper_finish', 'label' => 'Paper Finish', 'type' => 'select', 'options' => ['Matte', 'Gloss', 'Satin', 'Uncoated'], 'required' => 1],
                    ['key' => 'trim_size', 'label' => 'Trim Size', 'type' => 'text', 'required' => 1, 'placeholder' => 'e.g. A4, A5, 210×297mm'],
                    ['key' => 'pages', 'label' => 'Pages', 'type' => 'number', 'required' => 1, 'placeholder' => 'Incl. cover'],
                    ['key' => 'binding', 'label' => 'Binding', 'type' => 'select', 'options' => ['None', 'Saddle stitch', 'Perfect bind', 'Spiral', 'Wire-o'], 'required' => 1],
                    ['key' => 'colour_spec', 'label' => 'Colour', 'type' => 'select', 'options' => ['Colour', 'B&W', 'Mixed'], 'optional' => 1],
                ],
            ],
            'dtf' => [
                'name' => 'DTF (Direct to Film)', 'description' => 'DTF film transfer / garment decoration',
                'size_units' => ['mm', 'cm', 'in'], 'supplies_material' => 1,
                'stage_preset' => ['prepress', 'print', 'powder_cure', 'transfer', 'qc'],
                'spec_schema' => [
                    ['key' => 'design_size', 'label' => 'Design Size', 'type' => 'text', 'required' => 1, 'placeholder' => 'e.g. 280×100mm'],
                    ['key' => 'garment_type', 'label' => 'Garment Type', 'type' => 'text', 'required' => 1, 'placeholder' => 'e.g. Cotton tee, Hoodie'],
                    ['key' => 'print_positions', 'label' => 'Print Positions', 'type' => 'positions', 'required' => 1],
                    ['key' => 'size_breakdown', 'label' => 'Size Breakdown', 'type' => 'sizes', 'required' => 1],
                    ['key' => 'colour', 'label' => 'Ink / Colour', 'type' => 'text', 'optional' => 1],
                ],
            ],
            'apparel' => [
                'name' => 'Apparel', 'description' => 'Garment decoration, embroidery, screen transfer',
                'size_units' => ['in', 'cm'], 'supplies_material' => 1,
                'stage_preset' => ['prepress', 'print', 'cure', 'finishing', 'qc'],
                'spec_schema' => [
                    ['key' => 'garment_type', 'label' => 'Garment Type', 'type' => 'text', 'required' => 1, 'placeholder' => 'e.g. Polo, Round-neck tee'],
                    ['key' => 'print_positions', 'label' => 'Print Positions', 'type' => 'positions', 'required' => 1],
                    ['key' => 'size_breakdown', 'label' => 'Size Breakdown', 'type' => 'sizes', 'required' => 1],
                    ['key' => 'colour', 'label' => 'Garment Colour', 'type' => 'text', 'required' => 1],
                    ['key' => 'method', 'label' => 'Decoration Method', 'type' => 'select', 'options' => ['Screen print', 'DTF', 'Embroidery', 'Vinyl'], 'optional' => 1],
                ],
            ],
            'screen_print' => [
                'name' => 'Screen Print', 'description' => 'Silkscreen / screen printing',
                'size_units' => ['mm', 'cm', 'in'], 'supplies_material' => 1,
                'stage_preset' => ['prepress', 'screen_make', 'print', 'cure', 'qc'],
                'spec_schema' => [
                    ['key' => 'colours', 'label' => 'Colour Count', 'type' => 'number', 'required' => 1],
                    ['key' => 'substrate', 'label' => 'Substrate', 'type' => 'text', 'required' => 1, 'placeholder' => 'e.g. Cotton, Cotton-poly'],
                    ['key' => 'print_positions', 'label' => 'Print Positions', 'type' => 'positions', 'optional' => 1],
                    ['key' => 'quantity', 'label' => 'Quantity', 'type' => 'number', 'required' => 1],
                ],
            ],
            'stationery' => [
                'name' => 'Stationery', 'description' => 'Business cards, letterheads, envelopes, brochures',
                'size_units' => ['mm', 'cm', 'in'], 'supplies_material' => 0,
                'stage_preset' => ['prepress', 'print', 'cutting', 'binding', 'qc'],
                'spec_schema' => [
                    ['key' => 'paper_gsm', 'label' => 'Paper GSM', 'type' => 'number', 'required' => 1],
                    ['key' => 'size', 'label' => 'Size', 'type' => 'text', 'required' => 1, 'placeholder' => 'e.g. 90×54mm'],
                    ['key' => 'sides', 'label' => 'Sides', 'type' => 'select', 'options' => ['Single-sided', 'Double-sided'], 'required' => 1],
                    ['key' => 'finish', 'label' => 'Finish', 'type' => 'select', 'options' => ['Matte', 'Gloss', 'Spot UV', 'Lamination', 'None'], 'optional' => 1],
                ],
            ],
        ];
    }

    public static function job_statuses() {
        return ['planned', 'in_progress', 'on_hold', 'completed', 'cancelled'];
    }

    /**
     * Configurable service / charge types. Design is just ONE entry — nothing is
     * hardcoded around it. Stores can extend this list (db_print_service_types).
     * Each type separates a customer CHARGE from an internal COST.
     */
    public static function service_types() {
        return [
            'design'       => ['label' => 'Design',            'mode_aware' => 1, 'icon' => 'fa-pencil-square-o'],
            'installation' => ['label' => 'Installation',      'mode_aware' => 0, 'icon' => 'fa-wrench'],
            'finishing'    => ['label' => 'Finishing',         'mode_aware' => 0, 'icon' => 'fa-scissors'],
            'delivery'     => ['label' => 'Delivery',          'mode_aware' => 0, 'icon' => 'fa-truck'],
            'rush'         => ['label' => 'Rush / Express',    'mode_aware' => 0, 'icon' => 'fa-bolt'],
            'mounting'     => ['label' => 'Mounting',          'mode_aware' => 0, 'icon' => 'fa-thumb-tack'],
            'setup'        => ['label' => 'Setup',             'mode_aware' => 0, 'icon' => 'fa-cogs'],
            'other'        => ['label' => 'Other Service',     'mode_aware' => 0, 'icon' => 'fa-plus'],
        ];
    }

    /** Merged service-type list = built-ins + any store-configured extras. */
    public function get_service_types($store_id = null) {
        $types = self::service_types();
        if ($this->db->table_exists('db_print_service_types')) {
            if (empty($store_id)) $store_id = get_current_store_id();
            $rows = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_print_service_types')->result();
            foreach ($rows as $r) {
                $types[$r->service_key] = ['label' => $r->label, 'mode_aware' => (int)$r->mode_aware, 'icon' => $r->icon ?: 'fa-plus'];
            }
        }
        return $types;
    }

    public static function stage_statuses() {
        return ['pending', 'in_progress', 'done', 'skipped', 'outsourced'];
    }

    /* ============================ categories ============================== */

    public function get_categories($store_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        return $this->db->where('store_id', $store_id)->where('status', 1)
            ->order_by('sort_order', 'asc')->order_by('name', 'asc')
            ->get('db_print_categories')->result();
    }

    public function seed_categories($store_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $created = 0;
        foreach (self::category_presets() as $key => $p) {
            $exists = $this->db->where('store_id', $store_id)->where('category_key', $key)
                ->get('db_print_categories')->row();
            if ($exists) {
                // Refresh system presets' schema/stage/units so schema changes in
                // code propagate without a manual DB edit. Custom (non-system)
                // rows are never touched.
                if (!empty($exists->is_system)) {
                    $this->db->where('id', $exists->id)->update('db_print_categories', [
                        'name' => $p['name'],
                        'description' => $p['description'],
                        'spec_schema_json' => json_encode($p['spec_schema']),
                        'stage_preset_json' => json_encode($p['stage_preset']),
                        'size_units_json' => json_encode($p['size_units']),
                        'supplies_material' => $p['supplies_material'] ? 1 : 0,
                    ]);
                }
                continue;
            }
            $this->db->insert('db_print_categories', [
                'store_id' => $store_id,
                'category_key' => $key,
                'name' => $p['name'],
                'description' => $p['description'],
                'spec_schema_json' => json_encode($p['spec_schema']),
                'stage_preset_json' => json_encode($p['stage_preset']),
                'size_units_json' => json_encode($p['size_units']),
                'supplies_material' => $p['supplies_material'] ? 1 : 0,
                'is_system' => 1,
                'status' => 1,
            ]);
            $created++;
        }
        return $created;
    }

    public function get_category($id) {
        return $this->db->where('id', $id)->get('db_print_categories')->row();
    }

    /** Ordered spec schema for a category (decoded from its stored JSON). */
    public function get_category_schema($category_id) {
        $cat = $this->get_category($category_id);
        if (!$cat) return [];
        return json_decode($cat->spec_schema_json ?: '[]', true) ?: [];
    }

    /**
     * Validate a line's structured spec against its category schema.
     * Returns ['ok'=>bool, 'errors'=>[field=>msg], 'clean'=>[key=>value]].
     * $strict = TRUE enforces required fields (use at quotation/production);
     * FALSE allows saving a draft with gaps.
     */
    public function validate_specs($category_id, $spec, $strict = false) {
        $schema = $this->get_category_schema($category_id);
        $errors = [];
        $clean = [];
        foreach ($schema as $f) {
            $key = $f['key'];
            $val = isset($spec[$key]) ? $spec[$key] : null;
            $required = !empty($f['required']) && empty($f['optional']);
            $is_positions = ($f['type'] === 'positions');
            $is_sizes = ($f['type'] === 'sizes');

            if ($is_positions || $is_sizes) {
                $arr = is_array($val) ? array_filter($val, function ($v) { return $v !== '' && $v !== null; }) : [];
                $clean[$key] = $arr;
                if ($strict && $required && empty($arr)) {
                    $errors[$key] = $f['label'] . ' is required.';
                }
                continue;
            }

            $val = is_string($val) ? trim($val) : $val;
            if ($val === '' || $val === null) {
                $clean[$key] = null;
                if ($strict && $required) $errors[$key] = $f['label'] . ' is required.';
                continue;
            }
            if ($f['type'] === 'number' && !is_numeric($val)) {
                $errors[$key] = $f['label'] . ' must be a number.';
                continue;
            }
            if ($f['type'] === 'select' && !empty($f['options']) && !in_array($val, $f['options'], true)) {
                $errors[$key] = $f['label'] . ' has an invalid value.';
                continue;
            }
            $clean[$key] = $val;
        }
        return ['ok' => empty($errors), 'errors' => $errors, 'clean' => $clean];
    }

    /**
     * Job-level validation by stage. Returns ['ok'=>bool, 'errors'=>[]].
     * stage = quote | production | fulfil
     */
    public function validate_stage($job_id, $stage) {
        $job = $this->get_job($job_id);
        if (!$job) return ['ok' => false, 'errors' => ['job' => 'Job not found.']];
        $errors = [];
        $lines = $this->get_lines($job_id);
        if (empty($lines)) $errors['lines'] = 'At least one print item is required.';

        if ($stage === 'quote') {
            foreach ($lines as $ln) {
                $spec = $ln->spec_json ? json_decode($ln->spec_json, true) : [];
                $v = $this->validate_specs($ln->category_id, $spec, true);
                if (!$v['ok']) foreach ($v['errors'] as $k => $m) $errors['line' . $ln->id . '_' . $k] = $m;
            }
            if ((float)$job->quote_amount <= 0) $errors['quote_amount'] = 'A quotation total is required before issue.';
        }

        if ($stage === 'production') {
            foreach ($lines as $ln) {
                $spec = $ln->spec_json ? json_decode($ln->spec_json, true) : [];
                $v = $this->validate_specs($ln->category_id, $spec, true);
                if (!$v['ok']) foreach ($v['errors'] as $k => $m) $errors['line' . $ln->id . '_' . $k] = $m;
            }
        }
        return ['ok' => empty($errors), 'errors' => $errors];
    }

    /* ============================ jobs ==================================== */

    public function get_job($id) {
        $this->db->select('j.*, c.customer_name, c.mobile, o.order_code');
        $this->db->from('db_print_jobs j');
        $this->db->join('db_customers c', 'c.id = j.customer_id', 'left');
        $this->db->join('db_custom_orders o', 'o.id = j.custom_order_id', 'left');
        $this->db->where('j.id', $id);
        return $this->db->get()->row();
    }

    public function get_jobs($store_id = null, $status = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $this->db->select('j.*, c.customer_name, o.order_code');
        $this->db->from('db_print_jobs j');
        $this->db->join('db_customers c', 'c.id = j.customer_id', 'left');
        $this->db->join('db_custom_orders o', 'o.id = j.custom_order_id', 'left');
        $this->db->where('j.store_id', $store_id);
        $this->db->order_by('j.created_at', 'desc');
        if ($status) $this->db->where('j.production_status', $status);
        return $this->db->get()->result();
    }

    public function get_lines($job_id) {
        return $this->db->where('job_id', $job_id)->order_by('id', 'asc')->get('db_print_job_lines')->result();
    }

    public function create_job($data, array $line_cfgs) {
        $store_id = get_current_store_id();
        $this->db->trans_begin();
        try {
            $prefix = 'PRJ-' . date('Ymd') . '-';
            $last = $this->db->like('job_code', $prefix, 'after')->where('store_id', $store_id)
                ->order_by('id', 'DESC')->limit(1)->get('db_print_jobs')->row();
            $next = $last ? ((int)substr($last->job_code, strrpos($last->job_code, '-') + 1) + 1) : 1;
            $data['job_code'] = $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
            $data['store_id'] = $store_id;
            if (empty($data['warehouse_id'])) $data['warehouse_id'] = get_store_warehouse_id();
            if (empty($data['owner_id'])) $data['owner_id'] = $this->session->userdata('inv_userid') ?: null;
            $data['created_by'] = $this->session->userdata('inv_username') ?: 'System';
            $this->db->insert('db_print_jobs', $data);
            $job_id = $this->db->insert_id();

            $stage_seq = 1;
            $stage_seeded = [];
            $line_total_all = 0;
            foreach ($line_cfgs as $lc) {
                $cat = $this->get_category($lc['category_id'] ?? 0);
                $cat_key = $cat ? $cat->category_key : null;
                // Structured spec: validated against the category schema (draft = non-strict).
                $spec = isset($lc['spec']) && is_array($lc['spec']) ? $lc['spec'] : [];
                if ($cat) {
                    $validated = $this->validate_specs($cat->id, $spec, !empty($data['_strict']));
                    $spec = $validated['clean'];
                }
                $qty = (float)($lc['qty'] ?? 0);
                $unit_price = (float)($lc['unit_price'] ?? 0);
                $line_total = round($qty * $unit_price, 2);
                $line_total_all += $line_total;
                $this->db->insert('db_print_job_lines', [
                    'store_id' => $store_id,
                    'job_id' => $job_id,
                    'category_id' => $cat ? $cat->id : null,
                    'category_key' => $cat_key,
                    'item_id' => $lc['item_id'] ?? null,
                    'description' => $lc['description'] ?? null,
                    'spec_json' => json_encode($spec),
                    'qty' => $qty,
                    'unit_id' => $lc['unit_id'] ?? null,
                    'width' => isset($spec['width']) && is_numeric($spec['width']) ? (float)$spec['width'] : null,
                    'height' => isset($spec['height']) && is_numeric($spec['height']) ? (float)$spec['height'] : null,
                    'dim_unit' => $spec['dim_unit'] ?? null,
                    'size_breakdown_json' => isset($spec['size_breakdown']) ? json_encode($spec['size_breakdown']) : null,
                    'supplied_material' => !empty($lc['supplied_material']) ? 1 : 0,
                    'unit_price' => $unit_price,
                    'line_total' => $line_total,
                ]);

                if ($cat && !isset($stage_seeded[$cat_key])) {
                    $stage_keys = json_decode($cat->stage_preset_json ?: '[]', true) ?: [];
                    foreach ($stage_keys as $sk) {
                        if ($sk === 'qc') continue; // QC is job-level, seeded once below.
                        $label = ucwords(str_replace('_', ' ', $sk));
                        $requires_gate = in_array($sk, ['print', 'transfer', 'screen_make'], true) ? 1 : 0;
                        $this->db->insert('db_print_stages', [
                            'store_id' => $store_id, 'job_id' => $job_id, 'seq' => $stage_seq++,
                            'stage_key' => $sk, 'stage_label' => $label,
                            'requires_artwork' => $requires_gate, 'requires_authorization' => $requires_gate,
                        ]);
                    }
                    $stage_seeded[$cat_key] = true;
                }
            }
            // Seed a single job-level QC stage (never duplicated per category).
            $this->db->insert('db_print_stages', [
                'store_id' => $store_id, 'job_id' => $job_id, 'seq' => $stage_seq++,
                'stage_key' => 'qc', 'stage_label' => 'Quality Check',
            ]);
            // Quotation total is DERIVED: line totals + explicit charges. Never an
            // independent editable field.
            $charges = isset($data['charges']) && is_array($data['charges']) ? $data['charges'] : [];
            $charge_total = 0;
            foreach ($charges as $c) { $charge_total += (float)($c['amount'] ?? 0); }
            $computed_total = round($line_total_all + $charge_total, 2);
            $update = ['quote_amount' => $computed_total];
            if (!empty($charges)) $update['notes'] = trim(($data['notes'] ?? '') . "\n[charges] " . json_encode($charges));
            $this->db->where('id', $job_id)->update('db_print_jobs', $update);
            $this->db->trans_commit();
            return $job_id;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Printing create_job failed: ' . $e->getMessage());
            return false;
        }
    }

    public function get_stages($job_id) {
        return $this->db->where('job_id', $job_id)->order_by('seq', 'asc')->get('db_print_stages')->result();
    }

    public function get_logs($job_id) {
        return $this->db->where('job_id', $job_id)->order_by('id', 'asc')->get('db_print_stage_logs')->result();
    }

    /* ============================ quotation ================================ */

    /**
     * The EXISTING quotation module is the one authoritative source for issued
     * prices, charges, discounts and taxes. Printing does not own a second
     * quote engine — it links to db_quotation and mirrors the agreed total onto
     * the job as a DERIVED CACHE (never independently editable).
     */
    public function quotation_for_job($job_id) {
        $job = $this->get_job($job_id);
        if (!$job || empty($job->quotation_id)) return null;
        return $this->db->where('id', (int)$job->quotation_id)
            ->where('store_id', $job->store_id)->get('db_quotation')->row();
    }

    /**
     * Authoritative document resolver for ANY quotation id.
     *
     * The kind of a quotation is decided by its PERSISTED linkage — is there a
     * print job pointing at it? — and never by the store's business type. That
     * way every entry point (job screen, quotation list, direct URL, PDF)
     * resolves the same document for the same record, and a general quotation
     * in a printing store still behaves like a general quotation.
     *
     * @return array ['kind' => 'print'|'general', 'job' => object|null,
     *                'quotation' => object|null]
     */
    public function quotation_doc_type($quotation_id) {
        $quotation_id = (int)$quotation_id;
        $out = ['kind' => 'general', 'job' => null, 'quotation' => null];
        if (!$quotation_id || !$this->db->table_exists('db_quotation')) return $out;

        $out['quotation'] = $this->db->where('id', $quotation_id)->get('db_quotation')->row();

        $job = $this->db->select('id,job_code,store_id,quotation_id,quotation_revision_accepted,quotation_status,production_status')
            ->where('quotation_id', $quotation_id)->get('db_print_jobs')->row();
        if ($job) {
            $out['kind'] = 'print';
            $out['job'] = $job;
        }
        return $out;
    }

    /**
     * Number of quotations in this store that are NOT linked to a print job.
     * Used for a review list only — these are never auto-attached to jobs and
     * historical documents are never rewritten.
     */
    public function unlinked_quotation_count($store_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        if (!$this->db->table_exists('db_print_jobs')) return 0;
        $r = $this->db->query(
            "SELECT COUNT(*) n FROM db_quotation q
             LEFT JOIN db_print_jobs j ON j.quotation_id = q.id
             WHERE q.store_id = ? AND j.id IS NULL",
            [$store_id]
        )->row();
        return $r ? (int)$r->n : 0;
    }

    /** Review list of quotations not linked to any print job. */
    public function unlinked_quotations($store_id = null, $limit = 100) {
        if (empty($store_id)) $store_id = get_current_store_id();
        if (!$this->db->table_exists('db_print_jobs')) return [];
        return $this->db->query(
            "SELECT q.id, q.quotation_code, q.reference_no, q.quotation_date,
                    q.grand_total, q.sales_status, q.created_by
             FROM db_quotation q
             LEFT JOIN db_print_jobs j ON j.quotation_id = q.id
             WHERE q.store_id = ? AND j.id IS NULL
             ORDER BY q.id DESC LIMIT ?",
            [$store_id, (int)$limit]
        )->result();
    }

    public function quotation_items($quotation_id) {
        return $this->db->where('quotation_id', $quotation_id)->order_by('id', 'asc')
            ->get('db_quotationitems')->result();
    }

    public function quotation_revisions($quotation_id) {
        if (!$this->db->table_exists('db_quotation_revisions')) return [];
        return $this->db->where('quotation_id', $quotation_id)->order_by('revision_no', 'desc')
            ->get('db_quotation_revisions')->result();
    }

    /** Read-only summary of the authoritative quotation (display + gates). */
    public function quotation_summary($job_id) {
        $q = $this->quotation_for_job($job_id);
        if (!$q) return null;
        $job = $this->get_job($job_id);
        return [
            'quotation_id' => (int)$q->id,
            'customer_id' => (int)$q->customer_id,
            'quotation_code' => $q->quotation_code,
            'revision_no' => (int)$q->revision_no,
            'quotation_status' => $q->quotation_status,
            'quotation_date' => $q->quotation_date,
            'expire_date' => $q->expire_date,
            'subtotal' => (float)$q->subtotal,
            'other_charges' => (float)$q->other_charges_amt,
            'discount' => (float)$q->tot_discount_to_all_amt,
            'discount_input' => $q->discount_to_all_input,
            'discount_type' => $q->discount_to_all_type,
            'round_off' => (float)$q->round_off,
            'grand_total' => (float)$q->grand_total,
            'note' => $q->quotation_note,
            'sales_status' => $q->sales_status,
            'converted_sales_id' => $q->converted_sales_id ?? null,
            'line_count' => count($this->quotation_items($q->id)),
            'accepted_revision' => ($job && $job->quotation_revision_accepted !== null) ? (int)$job->quotation_revision_accepted : null,
            'reaccept_required' => !empty($job->quote_reaccept_required),
            'reaccept_reason' => $job->quote_reaccept_reason ?? null,
        ];
    }

    /* ------------------------------- tax ---------------------------------- */

    /** Available tax rates for this store (db_tax), for the job tax picker. */
    public function available_taxes($store_id = null) {
        if (!$this->db->table_exists('db_tax')) return [];
        if (empty($store_id)) $store_id = get_current_store_id();
        return $this->db->where('status', 1)->order_by('tax_name', 'asc')->get('db_tax')->result();
    }

    /**
     * Resolve the tax that applies to a print job.
     *
     * Printing defaults to EXEMPT. Tax applies only when the job explicitly
     * opts in (tax_on = 1) AND a rate is selected — nothing becomes taxable by
     * accident. The rate is snapshotted onto the job when the quotation is
     * issued, so a later rate change cannot silently rewrite an issued quote.
     */
    public function job_tax_config($job_id) {
        $job = $this->get_job($job_id);
        if (!$job) return ['on' => false, 'rate' => 0.0, 'type' => null, 'tax_id' => null, 'name' => null];
        $on = !empty($job->tax_on) && !empty($job->tax_id);
        $rate = (float)($job->tax_rate ?? 0);
        $name = null;
        if (!empty($job->tax_id)) {
            $t = $this->db->select('tax,tax_name')->where('id', $job->tax_id)->get('db_tax')->row();
            if ($t) { $name = $t->tax_name; if (!$on) $rate = (float)$t->tax; }
        }
        return [
            'on' => $on,
            'rate' => $rate,
            'type' => $job->tax_type ?: 'Exclusive',
            'tax_id' => $job->tax_id ? (int)$job->tax_id : null,
            'name' => $name,
        ];
    }

    /**
     * Set the tax behaviour for a job. Turning tax ON snapshots the rate so the
     * issued document and the stored figure always agree.
     */
    public function set_job_tax($job_id, $on, $tax_id = null, $type = 'Exclusive') {
        $job = $this->get_job($job_id);
        if (!$job) return ['success' => false, 'message' => 'Job not found.'];
        if ($job->production_status === 'completed') {
            return ['success' => false, 'message' => 'This job is completed — tax cannot change.'];
        }
        $type = in_array($type, ['Inclusive', 'Exclusive'], true) ? $type : 'Exclusive';
        $rate = null; $name = null;
        if ($on && $tax_id) {
            $t = $this->db->select('tax,tax_name')->where('id', (int)$tax_id)->where('status', 1)->get('db_tax')->row();
            if (!$t) return ['success' => false, 'message' => 'That tax rate is not available.'];
            $rate = (float)$t->tax; $name = $t->tax_name;
        }
        $this->db->where('id', $job_id)->update('db_print_jobs', [
            'tax_on' => $on ? 1 : 0,
            'tax_id' => $on ? (int)$tax_id : null,
            'tax_type' => $on ? $type : null,
            'tax_rate' => $on ? $rate : null,
            'tax_amount' => 0,
        ]);
        return ['success' => true, 'message' => $on
            ? $name . ' (' . rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.') . '%) will apply to this quotation.'
            : 'Quotation is tax exempt.'];
    }

    /**
     * Compute tax for a quotation given its subtotal. Returns both the tax and
     * the resulting grand total. Inclusive tax is already inside the price;
     * Exclusive tax is added on top.
     */
    public function compute_tax($job_id, $subtotal) {
        $cfg = $this->job_tax_config($job_id);
        $subtotal = (float)$subtotal;
        if (!$cfg['on'] || $cfg['rate'] <= 0) {
            return ['on' => false, 'rate' => 0.0, 'type' => null, 'amount' => 0.0,
                    'grand_total' => round($subtotal, 2), 'name' => null];
        }
        $rate = (float)$cfg['rate'];
        if ($cfg['type'] === 'Inclusive') {
            // Tax is already within the subtotal.
            $tax = round($subtotal - ($subtotal * 100 / (100 + $rate)), 2);
            $grand = round($subtotal, 2);
        } else {
            $tax = round($subtotal * $rate / 100, 2);
            $grand = round($subtotal + $tax, 2);
        }
        return ['on' => true, 'rate' => $rate, 'type' => $cfg['type'], 'amount' => $tax,
                'grand_total' => $grand, 'name' => $cfg['name']];
    }

    /** Tax breakdown for a quotation, for display on the document. */
    public function quotation_tax_summary($quotation_id) {
        $q = $this->db->where('id', $quotation_id)->get('db_quotation')->row();
        if (!$q || empty($q->id)) return null;
        // The job that owns this quotation carries the tax configuration.
        $job = $this->db->where('quotation_id', $quotation_id)->get('db_print_jobs')->row();
        if (!$job) return null;
        $cfg = $this->job_tax_config($job->id);
        if (!$cfg['on'] || $cfg['rate'] <= 0) {
            return ['on' => false, 'rate' => 0, 'amount' => 0, 'type' => null, 'name' => null, 'label' => 'Tax exempt'];
        }
        $subtotal = (float)$q->subtotal + (float)$q->other_charges_amt - (float)$q->tot_discount_to_all_amt;
        $calc = $this->compute_tax($job->id, $subtotal);
        return [
            'on' => true,
            'rate' => $cfg['rate'],
            'type' => $cfg['type'],
            'name' => $cfg['name'],
            'amount' => $calc['amount'],
            'label' => ($cfg['name'] ?: 'Tax') . ' ' . rtrim(rtrim(number_format($cfg['rate'], 2, '.', ''), '0'), '.') . '%'
                . ($cfg['type'] === 'Inclusive' ? ' (included)' : ''),
        ];
    }

    /** Human-readable spec for a print line, for the customer-facing document. */
    public function line_spec_summary($line) {        $spec = !empty($line->spec_json) ? json_decode($line->spec_json, true) : [];
        if (!is_array($spec)) $spec = [];
        $bits = [];
        if (!empty($line->width) && !empty($line->height)) {
            $bits[] = rtrim(rtrim(number_format((float)$line->width, 3, '.', ''), '0'), '.')
                . ' × ' . rtrim(rtrim(number_format((float)$line->height, 3, '.', ''), '0'), '.')
                . ' ' . ($line->dim_unit ?: '');
        }
        foreach ($spec as $k => $v) {
            if ($v === '' || $v === null || is_array($v)) continue;
            $bits[] = ucwords(str_replace('_', ' ', $k)) . ': ' . $v;
        }
        if (!empty($line->size_breakdown_json)) {
            $sz = json_decode($line->size_breakdown_json, true);
            if (is_array($sz) && !empty($sz)) {
                $parts = [];
                foreach ($sz as $size => $n) {
                    if ((float)$n > 0) $parts[] = $size . '×' . rtrim(rtrim(number_format((float)$n, 3, '.', ''), '0'), '.');
                }
                if ($parts) $bits[] = 'Sizes: ' . implode(', ', $parts);
            }
        }
        if (!empty($line->customer_supplied_material) || !empty($line->supplied_material)) {
            $bits[] = 'Customer-supplied material';
        }
        return implode(' | ', $bits);
    }

    /**
     * Create or revise the authoritative quotation for a print job.
     *
     * Lines map 1:1 onto db_quotationitems so revisions and conversion keep the
     * relationship. Quotation lines require a real db_items row (the shared
     * model resolves seller points and refreshes item stock), so printing lines
     * resolve to a NON-STOCK service item — satisfying shared validation
     * without inventing misleading saleable inventory.
     */
    public function quote_to_quotation($job_id, array $opts = []) {
        $job = $this->get_job($job_id);
        if (!$job) return ['success' => false, 'message' => 'Job not found.'];
        if (!$this->db->table_exists('db_quotation')) {
            return ['success' => false, 'message' => 'The quotation module is not available.'];
        }
        $lines = $this->get_lines($job_id);
        if (empty($lines)) return ['success' => false, 'message' => 'Add at least one print item before issuing a quotation.'];

        $store_id = $job->store_id;
        $existing = $this->quotation_for_job($job_id);
        if ($existing && $existing->sales_status === 'Converted') {
            return ['success' => false, 'message' => 'Quotation ' . $existing->quotation_code . ' is already converted and cannot be revised.'];
        }

        $customer_id = (int)($opts['customer_id'] ?? $job->customer_id);
        if (!$customer_id) return ['success' => false, 'message' => 'A customer is required on the quotation.'];
        $customer = $this->db->where('id', $customer_id)->where('store_id', (int)$store_id)
            ->where('status', 1)->get('db_customers')->row();
        if (!$customer) return ['success' => false, 'message' => 'Select an active customer from this store.'];

        $cust_item = $this->resolve_quotation_item($store_id, $lines);
        if (empty($cust_item['success'])) return $cust_item;

        $subtotal = 0.0;
        $built = [];
        foreach ($lines as $ln) {
            $qty = (float)$ln->qty;
            $price = (float)$ln->unit_price;
            $gross = round($qty * $price, 2);

            // Per-line discount parity with the shared quotation builder.
            // A line may carry a percentage or a fixed discount; the net is
            // what feeds the subtotal and the tax base.
            $ln_disc_type = ($ln->discount_type ?? null) === 'fixed' ? 'fixed' : (($ln->discount_input ?? null) ? 'in_percentage' : null);
            $ln_disc_input = (float)($ln->discount_input ?? 0);
            $ln_disc_amt = 0.0;
            if ($ln_disc_type === 'in_percentage' && $ln_disc_input > 0) {
                $ln_disc_amt = round($gross * ($ln_disc_input / 100), 2);
            } elseif ($ln_disc_type === 'fixed' && $ln_disc_input > 0) {
                $ln_disc_amt = min(round($ln_disc_input, 2), $gross);
            }
            $line_net = round($gross - $ln_disc_amt, 2);
            $subtotal += $line_net;

            // Per-line tax parity. Falls back to the job-level tax when the
            // line does not specify its own.
            $ln_tax_id = !empty($ln->tax_id) ? (int)$ln->tax_id : null;
            $ln_tax_type = $ln->tax_type ?? null;
            $ln_tax_amt = 0.0;
            if ($ln_tax_id) {
                $t = $this->db->select('tax')->where('id', $ln_tax_id)->get('db_tax')->row();
                if ($t) {
                    $rate = (float)$t->tax;
                    $type = ($ln_tax_type === 'Inclusive') ? 'Inclusive' : 'Exclusive';
                    $ln_tax_amt = ($type === 'Inclusive')
                        ? round($line_net - ($line_net * 100 / (100 + $rate)), 2)
                        : round($line_net * $rate / 100, 2);
                }
            }
            $unit_total = $qty > 0 ? round(($line_net + ($ln_tax_type === 'Inclusive' ? 0 : $ln_tax_amt)) / $qty, 6) : $price;

            $built[] = [
                'line' => $ln, 'description' => $this->quotation_line_description($ln),
                'qty' => $qty, 'price' => $price, 'total_cost' => $line_net,
                'unit_total_cost' => $unit_total,
                'discount_type' => $ln_disc_type, 'discount_input' => $ln_disc_input ?: null,
                'discount_amt' => $ln_disc_amt ?: null,
                'tax_id' => $ln_tax_id, 'tax_type' => $ln_tax_type,
                'tax_amt' => $ln_tax_amt ?: null,
            ];
        }

        // Explicit charges the customer agreed to. A waived design charge is 0
        // here, while its internal cost stays untouched on the service record.
        $service_total = 0.0;
        $types = $this->get_service_types($store_id);
        foreach ($lines as $ln) {
            foreach ($this->get_item_services($ln->id) as $svc) {
                $net = $this->service_charge_net($ln->id, $svc->service_key);
                if ($net <= 0) continue;
                $label = $types[$svc->service_key]['label'] ?? ucfirst($svc->service_key);
                $built[] = [
                    'line' => $ln,
                    'description' => $label . ' — ' . ($ln->description ?: ('Line ' . $ln->id)),
                    'qty' => 1, 'price' => $net, 'total_cost' => $net, 'unit_total_cost' => $net,
                ];
                $service_total += $net;
            }
        }

        $extra = (float)($opts['other_charges'] ?? 0);
        $disc_input = (float)($opts['discount_input'] ?? 0);
        $disc_type = (($opts['discount_type'] ?? 'in_percentage') === 'fixed') ? 'fixed' : 'in_percentage';
        $disc_amount = 0.0;
        if ($disc_input > 0) {
            $disc_amount = $disc_type === 'in_percentage'
                ? round(($subtotal + $service_total + $extra) * ($disc_input / 100), 2)
                : $disc_input;
        }
        $net_before_tax = round($subtotal + $service_total + $extra - $disc_amount, 2);

        // Tax is controllable per job and defaults to EXEMPT. The rate is
        // snapshotted at issue time so a later rate change cannot rewrite an
        // already-issued quotation.
        $tax = $this->compute_tax($job_id, $net_before_tax);
        $grand = $tax['grand_total'];

        $this->db->trans_begin();

        $qdate = !empty($opts['quotation_date']) ? system_fromatted_date($opts['quotation_date']) : date('Y-m-d');
        $expire = !empty($opts['expire_date']) ? date('Y-m-d', strtotime($opts['expire_date'])) : null;
        if ($expire === '1970-01-01') $expire = null;
        // Default validity window: 30 days. A quotation with no expiry never
        // prompts the customer and never lapses, which is what left jobs open
        // indefinitely before.
        if ($expire === null) {
            $expire = date('Y-m-d', strtotime('+30 days'));
        }
        $note = $opts['note'] ?? $job->title;

        if ($existing) {
            // Snapshot the outgoing revision BEFORE overwriting so issued
            // versions remain retrievable (same contract as the shared model).
            $prev = $this->db->where('id', $existing->id)->get('db_quotation')->row_array();
            $prev_items = $this->db->where('quotation_id', $existing->id)->get('db_quotationitems')->result_array();
            if ($this->db->table_exists('db_quotation_revisions') && !empty($prev)) {
                $this->db->insert('db_quotation_revisions', [
                    'store_id' => $prev['store_id'],
                    'quotation_id' => $existing->id,
                    'revision_no' => (int)$prev['revision_no'],
                    'revision_note' => $prev['revision_note'] ?? null,
                    'header_json' => json_encode($prev),
                    'items_json' => json_encode($prev_items),
                    'created_by' => $this->session->userdata('inv_userid'),
                    'created_date' => date('Y-m-d'),
                    'created_time' => date('H:i:s'),
                ]);
            }
            $next_rev = (int)$existing->revision_no + 1;
            $this->db->where('id', $existing->id)->update('db_quotation', [
                'reference_no' => $job->job_code,
                'quotation_date' => $qdate,
                'expire_date' => $expire,
                'quotation_status' => 'Quotation',
                'customer_id' => $customer_id,
                'other_charges_input' => $extra ?: null,
                'other_charges_amt' => $extra ?: null,
                'discount_to_all_input' => $disc_input ?: null,
                'discount_to_all_type' => $disc_type,
                'tot_discount_to_all_amt' => $disc_amount ?: null,
                'subtotal' => $subtotal,
                'grand_total' => $grand,
                'quotation_note' => $note,
                'revision_no' => $next_rev,
                'revision_note' => $opts['revision_note'] ?? 'Print job revised',
            ]);
            $quotation_id = (int)$existing->id;
            $this->db->where('quotation_id', $quotation_id)->delete('db_quotationitems');
        } else {
            $this->db->insert('db_quotation', [
                'store_id' => $store_id,
                'warehouse_id' => function_exists('get_store_warehouse_id') ? get_store_warehouse_id() : null,
                'count_id' => function_exists('get_count_id') ? get_count_id('db_quotation') : null,
                'quotation_code' => function_exists('get_init_code') ? get_init_code('quotation') : ('QT' . time()),
                'reference_no' => $job->job_code,
                'revision_no' => 0,
                'quotation_date' => $qdate,
                'expire_date' => $expire,
                'quotation_status' => 'Quotation',
                'customer_id' => $customer_id,
                'other_charges_input' => $extra ?: null,
                'other_charges_amt' => $extra ?: null,
                'discount_to_all_input' => $disc_input ?: null,
                'discount_to_all_type' => $disc_type,
                'tot_discount_to_all_amt' => $disc_amount ?: null,
                'subtotal' => $subtotal,
                'grand_total' => $grand,
                'quotation_note' => $note,
                'created_date' => date('Y-m-d'),
                'created_time' => date('H:i:s'),
                'created_by' => $this->session->userdata('inv_username') ?: 'System',
                'system_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
                'system_name' => 'Printing',
                'status' => 1,
            ]);
            $quotation_id = (int)$this->db->insert_id();
            if (!$quotation_id) {
                $this->db->trans_rollback();
                return ['success' => false, 'message' => 'Could not create the quotation.'];
            }
        }

        foreach ($built as $b) {
            $this->db->insert('db_quotationitems', [
                'store_id' => $store_id,
                'quotation_id' => $quotation_id,
                'quotation_status' => 'Quotation',
                'item_id' => $cust_item['item_id'],
                'description' => $b['description'],
                'quotation_qty' => $b['qty'],
                'price_per_unit' => $b['price'],
                // Line-level discount + tax, same columns the shared builder
                // writes, so financial capability matches a general quotation.
                'discount_type' => $b['discount_type'],
                'discount_input' => $b['discount_input'],
                'discount_amt' => $b['discount_amt'],
                'tax_id' => $b['tax_id'],
                'tax_type' => $b['tax_type'],
                'tax_amt' => $b['tax_amt'],
                'unit_total_cost' => $b['unit_total_cost'],
                'total_cost' => $b['total_cost'],
                'status' => 1,
                'seller_points' => 0,
            ]);
            $qitem_id = $this->db->insert_id();
            // Stable line mapping survives revisions and conversion.
            if (!empty($b['line']) && $this->db->field_exists('quotation_item_id', 'db_print_job_lines')) {
                $this->db->where('id', $b['line']->id)->update('db_print_job_lines', ['quotation_item_id' => $qitem_id]);
            }
        }

        // The job keeps a DERIVED cache of the agreed total for its gates, plus
        // the tax figure that produced it.
        $this->db->where('id', $job_id)->update('db_print_jobs', [
            'quotation_id' => $quotation_id,
            'customer_id' => $customer_id,
            'quote_amount' => $grand,
            'tax_amount' => $tax['amount'],
            'quotation_status' => 'issued',
        ]);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Quotation could not be saved.'];
        }
        $this->db->trans_commit();

        return [
            'success' => true,
            'message' => $existing ? 'Quotation revised.' : 'Quotation issued.',
            'quotation_id' => $quotation_id,
            'revision_no' => $existing ? ((int)$existing->revision_no + 1) : 0,
            'grand_total' => $grand,
            'service_total' => $service_total,
        ];
    }

    /** Customer-facing description for one print line (spec included). */
    private function quotation_line_description($line) {
        $cat = $line->category_id
            ? $this->db->select('name')->where('id', $line->category_id)->get('db_print_categories')->row()
            : null;
        $desc = trim((string)$line->description) ?: 'Print item';
        if ($cat && !empty($cat->name)) $desc .= ' (' . $cat->name . ')';
        $spec = $this->line_spec_summary($line);
        if ($spec !== '') $desc .= "\n" . $spec;
        return $desc;
    }

    /**
     * Quotation lines require a real db_items row because the shared model
     * resolves seller points and refreshes item stock. Printing lines are
     * services, so they resolve to a NON-STOCK item (service_bit=1) — shared
     * validation is satisfied without creating saleable inventory.
     */
    public function resolve_quotation_item($store_id, $lines = []) {
        $code = '__print_service__';
        $item = $this->db->where('store_id', $store_id)->where('item_code', $code)->get('db_items')->row();
        if ($item) {
            $this->ensure_non_stock_item($item->id);
            return ['success' => true, 'item_id' => (int)$item->id];
        }
        $this->db->insert('db_items', [
            'store_id' => $store_id,
            'item_name' => 'Printing Service (Job)',
            'item_code' => $code,
            'description' => 'Non-stock carrier for printing quotation lines. Not for sale at POS.',
            'service_bit' => 1,
            'purchase_price' => 0,
            'sales_price' => 0,
            'stock' => 0,
            'status' => 1,
        ]);
        $item_id = (int)$this->db->insert_id();
        if (!$item_id) return ['success' => false, 'message' => 'Could not prepare the printing service item.'];
        return ['success' => true, 'item_id' => $item_id];
    }

    /**
     * Guarantee the printing service item never distorts inventory: the shared
     * quotation save calls pos_model->update_items_quantity(), which returns
     * early for service items. This enforces that flag.
     */
    private function ensure_non_stock_item($item_id) {
        $it = $this->db->select('service_bit')->where('id', $item_id)->get('db_items')->row();
        if ($it && (int)$it->service_bit !== 1) {
            $this->db->where('id', $item_id)->update('db_items', ['service_bit' => 1, 'stock' => 0]);
        }
    }

    /**
     * Accept the CURRENT revision of the authoritative quotation.
     * Acceptance and the deposit gate always reference the accepted revision.
     */
    public function accept_quotation_revision($job_id, $note = '') {
        $job = $this->get_job($job_id);
        if (!$job) return ['success' => false, 'message' => 'Job not found.'];
        $q = $this->quotation_for_job($job_id);
        if (!$q) return ['success' => false, 'message' => 'No quotation has been issued for this job.'];
        if ($q->sales_status === 'Converted') return ['success' => false, 'message' => 'This quotation is already converted.'];

        $policy = $this->deposit_policy();
        $deposit_due = round((float)$q->grand_total * ((float)$policy['deposit_percent'] / 100), 2);

        $this->db->where('id', $job_id)->update('db_print_jobs', [
            'quotation_status' => 'accepted',
            'quotation_revision_accepted' => (int)$q->revision_no,
            'quotation_accepted_at' => date('Y-m-d H:i:s'),
            'quote_reaccept_required' => 0,
            'quote_reaccept_reason' => null,
            // Cache refreshed FROM the authoritative record, never the reverse.
            'quote_amount' => (float)$q->grand_total,
            'deposit_amount' => $deposit_due,
            'deposit_policy_json' => json_encode($policy),
        ]);
        return [
            'success' => true,
            'message' => 'Revision R' . (int)$q->revision_no . ' accepted.',
            'revision_no' => (int)$q->revision_no,
            'grand_total' => (float)$q->grand_total,
            'deposit_amount' => $deposit_due,
        ];
    }

    /** TRUE when the current quotation revision is NOT the accepted one. */
    public function quotation_change_requires_reacceptance($job_id) {
        $job = $this->get_job($job_id);
        if (!$job || empty($job->quotation_id)) return false;
        $q = $this->quotation_for_job($job_id);
        if (!$q) return false;
        if ($job->quotation_revision_accepted === null) return false;
        return (int)$q->revision_no !== (int)$job->quotation_revision_accepted;
    }

    /**
     * Flag a job whose agreed specs/price/terms changed: customer reacceptance
     * is required and any standing print authorization is invalidated.
     */
    public function flag_quotation_change($job_id, $reason = null) {
        $job = $this->get_job($job_id);
        if (!$job) return ['success' => false, 'message' => 'Job not found.'];
        $q = $this->quotation_for_job($job_id);
        $update = [
            'quote_reaccept_required' => 1,
            'quote_reaccept_reason' => $reason,
            'quotation_status' => 'issued',
        ];
        if ($q) $update['quote_amount'] = (float)$q->grand_total;
        $this->db->where('id', $job_id)->update('db_print_jobs', $update);
        if ($job->authorization_status === 'authorized') {
            $this->db->where('id', $job_id)->update('db_print_jobs', ['authorization_status' => 'none']);
        }
        return ['success' => true, 'message' => 'Reacceptance required before production.'];
    }

    /** Mirror the authoritative total onto the job (derived cache only). */
    public function sync_quote_amount_from_quotation($job_id) {
        $q = $this->quotation_for_job($job_id);
        if (!$q) return null;
        $this->db->where('id', $job_id)->update('db_print_jobs', ['quote_amount' => (float)$q->grand_total]);
        return (float)$q->grand_total;
    }

    public function set_quotation($job_id, $amount, $note = '') {
        // LEGACY direct setter, retained for backward compatibility. It only
        // touches the derived cache; the authoritative record is the quotation.
        $job = $this->get_job($job_id);
        if (!$job) return ['success' => false, 'message' => 'Job not found.'];
        if (in_array($job->production_status, ['completed', 'cancelled'])) {
            return ['success' => false, 'message' => 'Job is ' . $job->production_status . '.'];
        }
        if (!empty($job->quotation_id)) {
            $q = $this->quotation_for_job($job_id);
            return ['success' => false, 'message' => 'This job has quotation ' . ($q->quotation_code ?? '')
                . '. Issue/revise the quotation instead so the agreed total comes from it.'];
        }
        $this->db->where('id', $job_id)->update('db_print_jobs', [
            'quotation_status' => 'issued',
            'quote_amount' => (float)$amount,
        ]);
        return ['success' => true, 'message' => 'Quotation issued.'];
    }

    public function accept_quotation($job_id) {
        $job = $this->get_job($job_id);
        if (!$job) return ['success' => false, 'message' => 'Job not found.'];
        if (!in_array($job->quotation_status, ['issued', 'draft'])) {
            return ['success' => false, 'message' => 'No issued quotation to accept.'];
        }
        // When backed by the real quotation module, acceptance must reference
        // that revision.
        if (!empty($job->quotation_id)) {
            return $this->accept_quotation_revision($job_id);
        }
        $policy = $this->deposit_policy();
        $deposit_due = round((float)$job->quote_amount * ((float)$policy['deposit_percent'] / 100), 2);
        $this->db->where('id', $job_id)->update('db_print_jobs', [
            'quotation_status' => 'accepted',
            'deposit_amount' => $deposit_due,
            'deposit_policy_json' => json_encode($policy),
        ]);
        return ['success' => true, 'message' => 'Quotation accepted.', 'deposit_due' => $deposit_due];
    }

    /* ============================ deposit policy ========================== */

    public function deposit_policy() {
        $defaults = [
            'deposit_percent' => 70,
            'quotation_valid_days' => 7,
            'require_verified_deposit' => 1,
        ];
        if ($this->db->table_exists('db_sitesettings')) {
            $row = $this->db->get('db_sitesettings')->row();
            if ($row) {
                if (!empty($row->print_deposit_percent)) $defaults['deposit_percent'] = (float)$row->print_deposit_percent;
                if (!empty($row->print_quote_valid_days)) $defaults['quotation_valid_days'] = (int)$row->print_quote_valid_days;
                if (isset($row->print_require_verified_deposit) && $row->print_require_verified_deposit !== null && $row->print_require_verified_deposit !== '') $defaults['require_verified_deposit'] = (int)$row->print_require_verified_deposit;
            }
        }
        return $defaults;
    }

    /* ============================ payments ================================ */

    public function record_payment($job_id, $kind, $amount, $method, $reference = '', $note = '') {
        $job = $this->get_job($job_id);
        if (!$job) return ['success' => false, 'message' => 'Job not found.'];
        $amount = (float)$amount;
        if ($amount <= 0) return ['success' => false, 'message' => 'Amount must be positive.'];
        if (!in_array($kind, ['deposit', 'collection', 'refund', 'credit'])) {
            return ['success' => false, 'message' => 'Unknown payment kind.'];
        }
        if ($kind === 'deposit' && !in_array($job->quotation_status, ['accepted', 'converted'])) {
            return ['success' => false, 'message' => 'Deposit requires an accepted quotation.'];
        }

        $store_id = get_current_store_id();
        $this->db->trans_begin();
        try {
            $signed = $kind === 'refund' ? -$amount : $amount;
            $this->db->insert('db_salespayments', [
                'store_id' => $store_id,
                'customer_id' => $job->customer_id,
                'payment_date' => date('Y-m-d'),
                'payment_type' => $method,
                'payment' => $signed,
                'payment_note' => 'Print job ' . $job->job_code . ' — ' . $kind . ($note ? ': ' . $note : ''),
                'status' => 1,
                'short_code' => 'PRINT ' . strtoupper($kind),
                'created_date' => date('Y-m-d'),
                'created_time' => date('H:i:s'),
                'created_by' => $this->session->userdata('inv_username') ?: 'System',
            ]);
            $ledger_id = $this->db->insert_id();

            $this->db->insert('db_print_payments', [
                'store_id' => $store_id,
                'job_id' => $job_id,
                'payment_kind' => $kind,
                'amount' => $amount,
                'method' => $method,
                'reference' => $reference,
                'status' => ($kind === 'refund') ? 'verified' : 'received',
                'ledger_payment_id' => $ledger_id,
                'created_by' => $this->session->userdata('inv_username') ?: 'System',
            ]);
            $print_payment_id = $this->db->insert_id();

            $this->_recalc_payment_state($job_id);
            $this->db->trans_commit();
            return ['success' => true, 'message' => ucfirst($kind) . ' recorded.', 'ledger_payment_id' => $ledger_id, 'print_payment_id' => $print_payment_id];
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Printing record_payment failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Payment failed to record.'];
        }
    }

    public function verify_payment($print_payment_id) {
        $p = $this->db->where('id', $print_payment_id)->get('db_print_payments')->row();
        if (!$p) return ['success' => false, 'message' => 'Payment not found.'];
        if ($p->status === 'verified') return ['success' => true, 'message' => 'Already verified.'];
        $this->db->where('id', $print_payment_id)->update('db_print_payments', [
            'status' => 'verified',
            'verified_by' => $this->session->userdata('inv_username') ?: 'System',
            'verified_at' => date('Y-m-d H:i:s'),
        ]);
        $this->_recalc_payment_state($p->job_id);
        return ['success' => true, 'message' => 'Receipt verified.'];
    }

    public function reverse_payment($print_payment_id, $reason = '') {
        $p = $this->db->where('id', $print_payment_id)->get('db_print_payments')->row();
        if (!$p) return ['success' => false, 'message' => 'Payment not found.'];
        if ($p->status === 'reversed') return ['success' => true, 'message' => 'Already reversed.'];
        $this->db->trans_begin();
        try {
            $job = $this->get_job($p->job_id);
            $this->db->insert('db_salespayments', [
                'store_id' => $p->store_id,
                'customer_id' => $job->customer_id,
                'payment_date' => date('Y-m-d'),
                'payment_type' => $p->method,
                'payment' => -((float)$p->amount),
                'payment_note' => 'REVERSAL of print ' . $p->payment_kind . ' #' . $p->id . ($reason ? ': ' . $reason : ''),
                'status' => 1,
                'short_code' => 'PRINT REVERSAL',
                'created_date' => date('Y-m-d'),
                'created_time' => date('H:i:s'),
                'created_by' => $this->session->userdata('inv_username') ?: 'System',
            ]);
            $this->db->where('id', $print_payment_id)->update('db_print_payments', ['status' => 'reversed']);
            $this->_recalc_payment_state($p->job_id);
            $this->db->trans_commit();
            return ['success' => true, 'message' => 'Payment reversed.'];
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Reversal failed.'];
        }
    }

    private function _recalc_payment_state($job_id) {
        $job = $this->get_job($job_id);
        $net = $this->net_verified_payments($job_id);
        $status = 'unpaid';
        if ((float)$job->quote_amount > 0 && $net >= (float)$job->quote_amount) {
            $status = 'paid';
        } elseif ((float)$job->deposit_amount > 0 && $net >= (float)$job->deposit_amount) {
            $status = 'verified';
        } elseif ($net > 0) {
            $status = 'partial';
        }
        $this->db->where('id', $job_id)->update('db_print_jobs', ['payment_status' => $status]);
        return $status;
    }

    public function net_verified_payments($job_id) {
        $in = (float)$this->db->select('COALESCE(SUM(amount),0) s')
            ->where('job_id', $job_id)->where('status', 'verified')
            ->where_in('payment_kind', ['deposit', 'collection'])
            ->get('db_print_payments')->row()->s;
        $out = (float)$this->db->select('COALESCE(SUM(amount),0) s')
            ->where('job_id', $job_id)->where('status', 'verified')
            ->where('payment_kind', 'refund')
            ->get('db_print_payments')->row()->s;
        return $in - $out;
    }

    public function deposit_gate_met($job_id) {
        $job = $this->get_job($job_id);
        if (!$job) return false;
        $policy = $job->deposit_policy_json ? json_decode($job->deposit_policy_json, true) : $this->deposit_policy();
        if (empty($policy['deposit_percent'])) return true;
        $deposit_due = round((float)$job->quote_amount * ((float)$policy['deposit_percent'] / 100), 2);
        return $this->net_verified_payments($job_id) >= $deposit_due;
    }

    /* ============================ artwork ================================= */

    public function get_artworks($job_id) {
        return $this->db->where('job_id', $job_id)->order_by('version_no', 'desc')->order_by('id', 'desc')->get('db_print_artworks')->result();
    }

    public function add_artwork($job_id, $file_name, $file_path, $file_hash, $mime_type = null) {
        $job = $this->get_job($job_id);
        if (!$job) return false;
        $last = $this->db->select_max('version_no')->where('job_id', $job_id)->get('db_print_artworks')->row();
        $this->db->insert('db_print_artworks', [
            'store_id' => $job->store_id,
            'job_id' => $job_id,
            'version_no' => (int)($last->version_no ?? 0) + 1,
            'file_name' => $file_name,
            'file_path' => $file_path,
            'file_hash' => $file_hash,
            'mime_type' => $mime_type,
            'status' => 'uploaded',
            'uploaded_by' => $this->session->userdata('inv_username') ?: 'System',
        ]);
        $art_id = $this->db->insert_id();
        $this->db->where('id', $job_id)->update('db_print_jobs', ['artwork_status' => 'uploaded']);
        return $art_id;
    }

    public function approve_artwork($artwork_id) {
        $art = $this->db->where('id', $artwork_id)->get('db_print_artworks')->row();
        if (!$art) return ['success' => false, 'message' => 'Artwork not found.'];
        $this->db->trans_begin();
        try {
            $this->db->where('job_id', $art->job_id)->where('status', 'approved')
                ->update('db_print_artworks', ['status' => 'uploaded', 'approved_at' => null, 'approved_by' => null, 'customer_approved' => 0]);
            $this->db->where('id', $artwork_id)->update('db_print_artworks', [
                'status' => 'approved', 'customer_approved' => 1,
                'customer_approved_at' => date('Y-m-d H:i:s'),
                'approved_by' => $this->session->userdata('inv_username') ?: 'System',
                'approved_at' => date('Y-m-d H:i:s'),
            ]);
            $this->db->where('id', $art->job_id)->update('db_print_jobs', [
                'artwork_status' => 'approved', 'design_status' => 'pending', 'authorization_status' => 'none',
            ]);
            $this->db->where('job_id', $art->job_id)->where('status', 'authorized')
                ->update('db_print_authorizations', ['status' => 'invalidated']);
            $this->db->trans_commit();
            return ['success' => true, 'message' => 'Artwork approved (version ' . $art->version_no . ').'];
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Approval failed.'];
        }
    }

    /* ============================ designer clearance ====================== */

    public function clearance_artwork($job_id, $decision, $reason = '') {
        if (!in_array($decision, ['cleared', 'rejected'])) {
            return ['success' => false, 'message' => 'Invalid decision.'];
        }
        $art = $this->db->where('job_id', $job_id)->where('status', 'approved')
            ->order_by('version_no', 'desc')->limit(1)->get('db_print_artworks')->row();
        if (!$art) return ['success' => false, 'message' => 'No approved artwork to clear.'];
        $this->db->insert('db_print_clearances', [
            'store_id' => get_current_store_id(),
            'job_id' => $job_id,
            'artwork_id' => $art->id,
            'decision' => $decision,
            'reason' => $reason,
            'cleared_by' => $this->session->userdata('inv_username') ?: 'System',
            'cleared_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->where('id', $job_id)->update('db_print_jobs', [
            'design_status' => $decision === 'cleared' ? 'cleared' : 'rejected',
        ]);
        return ['success' => true, 'message' => 'Design ' . $decision . '.'];
    }

    /* ============================ design service ========================== */

    /**
     * Save the design service for a job line. Customer CHARGE and internal COST
     * are separate. A waiver records original + waived + reason + approver and
     * NEVER erases the internal cost.
     */
    public function save_item_design($job_id, $line_id, array $d) {
        $job = $this->get_job($job_id);
        if (!$job) return ['success' => false, 'message' => 'Job not found.'];
        $modes = ['none', 'customer_supplied', 'new_design', 'modify_supplied', 'reuse_previous'];
        $mode = $d['design_mode'] ?? 'none';
        if (!in_array($mode, $modes, true)) return ['success' => false, 'message' => 'Invalid design mode.'];

        $charge = (float)($d['charge_amount'] ?? 0);
        $waived = !empty($d['charge_waived']);
        $waived_amount = $waived ? $charge : 0;
        if ($waived && trim((string)($d['waiver_reason'] ?? '')) === '') {
            return ['success' => false, 'message' => 'A waiver reason is required.'];
        }
        $existing = $this->db->where('line_id', $line_id)->get('db_print_item_design')->row();

        $row = [
            'store_id' => get_current_store_id(),
            'job_id' => $job_id,
            'line_id' => $line_id,
            'design_mode' => $mode,
            'instructions' => $d['instructions'] ?? null,
            'designer_id' => !empty($d['designer_id']) ? (int)$d['designer_id'] : null,
            'expected_date' => !empty($d['expected_date']) ? $d['expected_date'] : null,
            'source_artwork_id' => !empty($d['source_artwork_id']) ? (int)$d['source_artwork_id'] : null,
            'charge_amount' => $charge,
            'charge_waived' => $waived ? 1 : 0,
            'waived_amount' => $waived_amount,
            'waiver_reason' => $waived ? $d['waiver_reason'] : null,
            'waived_by' => $waived ? ($this->session->userdata('inv_username') ?: 'System') : null,
            'waived_at' => $waived ? date('Y-m-d H:i:s') : null,
            'internal_cost' => (float)($d['internal_cost'] ?? 0),
            'internal_cost_basis' => $d['internal_cost_basis'] ?? null,
        ];
        if ($existing) {
            $this->db->where('id', $existing->id)->update('db_print_item_design', $row);
            $id = $existing->id;
        } else {
            $this->db->insert('db_print_item_design', $row);
            $id = $this->db->insert_id();
        }
        return ['success' => true, 'message' => 'Design service saved.', 'id' => $id];
    }

    public function get_item_design($line_id) {
        return $this->db->where('line_id', $line_id)->get('db_print_item_design')->row();
    }

    /* ============================ generic services ======================== */

    /**
     * Save a per-line service (design, installation, finishing, delivery…).
     * Design is simply service_key='design' — nothing is hardcoded around it.
     * Customer CHARGE and internal COST are separate; a waiver records the audit
     * and NEVER erases the internal cost.
     */
    public function save_item_service($job_id, $line_id, array $d, $service_key = null) {
        $job = $this->get_job($job_id);
        if (!$job) return ['success' => false, 'message' => 'Job not found.'];
        $service_key = $service_key ?: (string)($d['service_key'] ?? 'other');
        $types = $this->get_service_types($job->store_id);
        if (!isset($types[$service_key])) return ['success' => false, 'message' => 'Unknown service type.'];
        $mode_aware = !empty($types[$service_key]['mode_aware']);

        $mode = $d['design_mode'] ?? null;
        if ($mode_aware && $mode !== null) {
            if (!in_array($mode, ['none', 'customer_supplied', 'new_design', 'modify_supplied', 'reuse_previous'], true)) {
                return ['success' => false, 'message' => 'Invalid design mode.'];
            }
        }
        $charge = (float)($d['charge_amount'] ?? 0);
        $waived = !empty($d['charge_waived']);
        if ($waived && trim((string)($d['waiver_reason'] ?? '')) === '') {
            return ['success' => false, 'message' => 'A waiver reason is required.'];
        }
        $row = [
            'store_id' => $job->store_id,
            'job_id' => $job_id,
            'line_id' => $line_id,
            'service_key' => $service_key,
            'design_mode' => $mode,
            'instructions' => $d['instructions'] ?? null,
            'assignee_id' => !empty($d['assignee_id']) ? (int)$d['assignee_id'] : (!empty($d['designer_id']) ? (int)$d['designer_id'] : null),
            'expected_date' => !empty($d['expected_date']) ? $d['expected_date'] : null,
            'source_artwork_id' => !empty($d['source_artwork_id']) ? (int)$d['source_artwork_id'] : null,
            'charge_amount' => $charge,
            'charge_waived' => $waived ? 1 : 0,
            'waived_amount' => $waived ? $charge : 0,
            'waiver_reason' => $waived ? $d['waiver_reason'] : null,
            'waived_by' => $waived ? ($this->session->userdata('inv_username') ?: 'System') : null,
            'waived_at' => $waived ? date('Y-m-d H:i:s') : null,
            'internal_cost' => (float)($d['internal_cost'] ?? 0),
            'internal_cost_basis' => $d['internal_cost_basis'] ?? null,
        ];
        $existing = $this->db->where('line_id', $line_id)->where('service_key', $service_key)->get('db_print_item_services')->row();
        if ($existing) {
            $this->db->where('id', $existing->id)->update('db_print_item_services', $row);
            $id = $existing->id;
        } else {
            $this->db->insert('db_print_item_services', $row);
            $id = $this->db->insert_id();
        }
        return ['success' => true, 'message' => $types[$service_key]['label'] . ' saved.', 'id' => $id];
    }

    public function get_item_services($line_id) {
        return $this->db->where('line_id', $line_id)->order_by('service_key', 'asc')->get('db_print_item_services')->result();
    }

    public function get_service($line_id, $service_key) {
        return $this->db->where('line_id', $line_id)->where('service_key', $service_key)->get('db_print_item_services')->row();
    }

    /** Net customer charge for a service after waiver (0 if waived/none). */
    public function service_charge_net($line_id, $service_key) {
        $r = $this->get_service($line_id, $service_key);
        if (!$r) return 0.0;
        if ((int)$r->charge_waived === 1) return 0.0;
        return (float)$r->charge_amount;
    }

    /** Total net customer service charges on a line (all service types). */
    public function line_service_charges_net($line_id) {
        $rows = $this->get_item_services($line_id);
        $sum = 0; foreach ($rows as $r) { $sum += ((int)$r->charge_waived === 1) ? 0 : (float)$r->charge_amount; }
        return round($sum, 2);
    }

    /** Net design charge billed to the customer (0 if waived or none). */
    public function design_charge_net($line_id) {
        $r = $this->get_item_design($line_id);
        if (!$r) return 0.0;
        if ((int)$r->charge_waived === 1) return 0.0;
        return (float)$r->charge_amount;
    }

    /* ============================ material plan =========================== */

    /**
     * Explicitly convert a quantity in $unit_id to the item's base/stock unit.
     * Uses the per-item selling-unit table first, then the unit family.
     * Returns null when no conversion is known — callers must NOT guess.
     */
    public function to_base_qty($item_id, $unit_id, $qty, $store_id = null) {
        $qty = (float)$qty;
        if (empty($store_id)) $store_id = get_current_store_id();
        $item = $this->db->select('unit_id')->where('id', $item_id)->get('db_items')->row();
        if (!$item) return null;
        if (empty($unit_id) || (int)$unit_id === (int)$item->unit_id) return $qty;

        if ($this->db->table_exists('db_item_selling_units')) {
            $su = $this->db->where('item_id', $item_id)->where('unit_id', $unit_id)
                ->where('store_id', $store_id)->where('status', 1)->get('db_item_selling_units')->row();
            if ($su && (float)$su->conversion_factor > 0) return $qty * (float)$su->conversion_factor;
        }
        if ($item->unit_id && function_exists('get_unit_family')) {
            foreach (get_unit_family($item->unit_id, $store_id) as $u) {
                if ((int)$u->id === (int)$unit_id && (float)$u->equivalent_qty > 0) {
                    return $qty / (float)$u->equivalent_qty;
                }
            }
        }
        return null; // no known conversion
    }

    /**
     * Add/update a material or operation plan row. Quantity MUST carry a unit;
     * conversion to the item base unit is explicit and validated (conversion_ok=0
     * flags rows needing manual review — never a guessed factor).
     */
    public function save_plan_row($job_id, $line_id, array $d) {
        $store_id = get_current_store_id();
        $type = ($d['plan_type'] ?? 'material') === 'operation' ? 'operation' : 'material';
        $plan_qty = (float)($d['plan_qty'] ?? 0);
        $plan_unit_id = !empty($d['plan_unit_id']) ? (int)$d['plan_unit_id'] : null;
        $item_id = !empty($d['item_id']) ? (int)$d['item_id'] : null;

        // Every material quantity must carry a unit.
        if ($type === 'material' && ($plan_unit_id === null || $plan_unit_id === 0)) {
            return ['success' => false, 'message' => 'A unit is required for every material quantity.'];
        }

        $base_qty = $plan_qty; $base_unit_id = $plan_unit_id; $conversion_factor = 1; $conversion_ok = 1;
        if ($item_id) {
            $item = $this->db->select('unit_id, purchase_price')->where('id', $item_id)->get('db_items')->row();
            $base_unit_id = $item->unit_id ?? null;
            $conv = $this->to_base_qty($item_id, $plan_unit_id, $plan_qty, $store_id);
            if ($conv === null) { $conversion_ok = 0; $base_qty = $plan_qty; }
            else { $base_qty = $conv; $conversion_factor = $plan_qty > 0 ? $conv / $plan_qty : 1; }
        }
        $est_unit_cost = (float)($d['est_unit_cost'] ?? 0);
        $wastage_pct = (float)($d['wastage_pct'] ?? 0);
        $wastage_qty = round($plan_qty * ($wastage_pct / 100), 4);
        $est_total_cost = round(($plan_qty + $wastage_qty) * $est_unit_cost, 2);

        $row = [
            'store_id' => $store_id,
            'job_id' => $job_id,
            'line_id' => $line_id,
            'plan_type' => $type,
            'item_id' => $item_id,
            'operation_key' => $d['operation_key'] ?? null,
            'description' => $d['description'] ?? null,
            'plan_qty' => $plan_qty,
            'plan_unit_id' => $plan_unit_id,
            'base_qty' => $base_qty,
            'base_unit_id' => $base_unit_id,
            'conversion_factor' => $conversion_factor,
            'conversion_ok' => $conversion_ok,
            'est_unit_cost' => $est_unit_cost,
            'est_total_cost' => $est_total_cost,
            'wastage_pct' => $wastage_pct,
            'wastage_qty' => $wastage_qty,
            'roll_width' => isset($d['roll_width']) ? (float)$d['roll_width'] : null,
            'roll_length' => isset($d['roll_length']) ? (float)$d['roll_length'] : null,
            'area_sqm' => isset($d['area_sqm']) ? (float)$d['area_sqm'] : null,
            'cost_basis' => $d['cost_basis'] ?? 'estimated',
            'template_key' => $d['template_key'] ?? null,
            'quotation_version' => (int)($d['quotation_version'] ?? 1),
            'sort_order' => (int)($d['sort_order'] ?? 0),
        ];
        if (!empty($d['id'])) {
            $this->db->where('id', (int)$d['id'])->where('store_id', $store_id)->update('db_print_item_plans', $row);
            $id = (int)$d['id'];
        } else {
            $this->db->insert('db_print_item_plans', $row);
            $id = $this->db->insert_id();
        }
        $this->recalc_line_estimate($line_id);
        return ['success' => true, 'message' => 'Plan saved.', 'id' => $id, 'conversion_ok' => $conversion_ok];
    }

    public function get_plans($job_id, $line_id = null) {
        $this->db->where('job_id', $job_id);
        if ($line_id) $this->db->where('line_id', $line_id);
        return $this->db->order_by('plan_type', 'asc')->order_by('sort_order', 'asc')->get('db_print_item_plans')->result();
    }

    public function delete_plan_row($id) {
        $row = $this->db->where('id', $id)->where('store_id', get_current_store_id())->get('db_print_item_plans')->row();
        if (!$row) return false;
        $ok = $this->db->where('id', $id)->delete('db_print_item_plans');
        if ($ok) $this->recalc_line_estimate($row->line_id);
        return $ok;
    }

    /** Estimated material + operation cost for a line. */
    public function line_estimate($line_id) {
        $row = $this->db->select('COALESCE(SUM(est_total_cost),0) s')->where('line_id', $line_id)->get('db_print_item_plans')->row();
        $costs = $this->db->select('COALESCE(SUM(CASE WHEN estimated=1 THEN amount ELSE 0 END),0) est, COALESCE(SUM(CASE WHEN estimated=0 THEN amount ELSE 0 END),0) meas')
            ->where('line_id', $line_id)->get('db_print_item_cost_estimates')->row();
        return [
            'material_est' => round((float)$row->s, 2),
            'other_est' => round((float)$costs->est, 2),
            'other_measured' => round((float)$costs->meas, 2),
            'total_est' => round((float)$row->s + (float)$costs->est, 2),
        ];
    }

    private function recalc_line_estimate($line_id) {
        // Line selling totals are set from qty × unit_price at save time. This
        // hook exists so plan changes can trigger any future derived recompute
        // without callers needing to know the internals.
        return true;
    }

    /** Estimated cost breakdown per job, for pricing/reporting (excludes customer charge). */
    public function job_cost_estimate($job_id) {
        $rows = $this->get_plans($job_id);
        $mat = 0; foreach ($rows as $r) { if ($r->plan_type === 'material') $mat += (float)$r->est_total_cost; }
        $ops = 0; foreach ($rows as $r) { if ($r->plan_type === 'operation') $ops += (float)$r->est_total_cost; }
        $costs = $this->db->select('COALESCE(SUM(CASE WHEN estimated=1 THEN amount ELSE 0 END),0) est, COALESCE(SUM(CASE WHEN estimated=0 THEN amount ELSE 0 END),0) meas')
            ->where('job_id', $job_id)->get('db_print_item_cost_estimates')->row();
        // Internal service costs (design + any other configured service).
        $svc_internal = 0;
        if ($this->db->table_exists('db_print_item_services')) {
            $svc_internal = (float)$this->db->select('COALESCE(SUM(internal_cost),0) s')->where('job_id', $job_id)->get('db_print_item_services')->row()->s;
        }
        $design_internal = (float)$this->db->select('COALESCE(SUM(internal_cost),0) s')->where('job_id', $job_id)->get('db_print_item_design')->row()->s;
        $internal_total = max($svc_internal, $design_internal); // avoid double-count if both were used
        return [
            'material_est' => round($mat, 2),
            'operations_est' => round($ops, 2),
            'other_est' => round((float)$costs->est, 2),
            'other_measured' => round((float)$costs->meas, 2),
            'design_internal_cost' => round($internal_total, 2),
            'total_est' => round($mat + $ops + (float)$costs->est + $internal_total, 2),
        ];
    }

    /* ================================================================== */
    /*  CATEGORY CALCULATORS                                              */
    /*  Quantity derivation per category family. Nothing universal is      */
    /*  hardcoded: pack size, roll width and ups-per-sheet come from the   */
    /*  category config (db_print_category_calcs) or the line specs.       */
    /* ================================================================== */

    /** Default calculator key for a category (configurable, never assumed). */
    public function category_calc($store_id, $category_id) {
        $row = $this->db->where('store_id', $store_id)->where('category_id', $category_id)
            ->get('db_print_category_calcs')->row();
        $cfg = $row && $row->config_json ? (json_decode($row->config_json, true) ?: []) : [];
        return [
            'calc_key' => $row ? $row->calc_key : null,
            'config' => $cfg,
        ];
    }

    public function save_category_calc($store_id, $category_id, $calc_key, array $config) {
        $allowed = ['none', 'sheet', 'roll_area', 'imposition', 'garment_sizes'];
        if (!in_array($calc_key, $allowed, true)) return ['success' => false, 'message' => 'Unknown calculator.'];
        $row = ['store_id' => $store_id, 'category_id' => (int)$category_id, 'calc_key' => $calc_key, 'config_json' => json_encode($config)];
        $ex = $this->db->where('store_id', $store_id)->where('category_id', $category_id)->get('db_print_category_calcs')->row();
        if ($ex) $this->db->where('id', $ex->id)->update('db_print_category_calcs', $row);
        else $this->db->insert('db_print_category_calcs', $row);
        return ['success' => true, 'message' => 'Calculator saved.'];
    }

    /**
     * SHEET FAMILY — paper, card, board.
     * Derives sheets from finished copies using ups-per-sheet and sides.
     * packs/reams are derived ONLY when a pack size is configured for the
     * category or given on the line — there is no universal ream constant.
     */
    public function calc_sheet(array $in) {
        $copies = (float)($in['copies'] ?? 0);
        $ups = (float)($in['ups_per_sheet'] ?? 1);          // finished pieces per printed sheet
        $sides = (float)($in['sides'] ?? 1);                // 1 or 2
        $pages = (float)($in['pages'] ?? 0);                // for booklets: pages per copy
        $per_copy_sheets = ($pages > 1 && $ups > 0) ? ceil($pages / $ups) : ($ups > 0 ? 1 / $ups : 0);
        $sheets = ceil($copies * max($per_copy_sheets, 0)) * max($sides, 1);
        $wastage_pct = (float)($in['wastage_pct'] ?? 0);
        $with_waste = $sheets;
        if ($wastage_pct > 0) $with_waste = ceil($sheets * (1 + $wastage_pct / 100));
        $out = [
            'calc' => 'sheet',
            'finished_copies' => $copies,
            'pages' => $pages ?: null,
            'ups_per_sheet' => $ups,
            'sides' => $sides,
            'sheets' => $sheets,
            'sheets_with_wastage' => $with_waste,
            'unit' => 'sheet',
            'derived' => true,
        ];
        $pack = (float)($in['sheets_per_pack'] ?? 0);
        if ($pack <= 0) {
            $out['packs'] = null;
            $out['pack_note'] = 'No pack size configured for this material — enter packs manually or set the pack size on the category.';
        } else {
            $out['sheets_per_pack'] = $pack;
            $out['packs'] = round($with_waste / $pack, 4);
        }
        return $out;
    }

    /**
     * ROLL FAMILY — banner, vinyl, lamination film, fabrics.
     * AREA REQUIRES WIDTH. A roll→area conversion without width is refused,
     * never guessed. Width may come from the line or the category config.
     */
    public function calc_roll_area(array $in) {
        $pieces = (float)($in['pieces'] ?? 0);
        $length = (float)($in['length'] ?? 0);     // length per piece
        $width = (float)($in['width'] ?? 0);       // width per piece (REQUIRED)
        $roll_width = (float)($in['roll_width'] ?? 0);   // falls back to width when omitted
        if ($roll_width <= 0) $roll_width = $width;
        $unit_div = (float)($in['unit_per_meter'] ?? 100); // cm→m
        if ($width <= 0 || $length <= 0) {
            return ['calc' => 'roll_area', 'error' => 'Roll width and length are required to compute area — area cannot be derived from length alone.', 'derived' => false];
        }
        if ($roll_width <= 0) {
            return ['calc' => 'roll_area', 'error' => 'A roll width is required to convert a roll to area.', 'derived' => false];
        }
        $area_piece = ($width / $unit_div) * ($length / $unit_div);
        $area = $area_piece * max($pieces, 1);
        $wastage_pct = (float)($in['wastage_pct'] ?? 0);
        $with_waste = $area * (1 + $wastage_pct / 100);
        $linear = $pieces * ($length / $unit_div);
        $out = [
            'calc' => 'roll_area',
            'pieces' => $pieces,
            'width_used' => $width,
            'roll_width' => $roll_width,
            'length_per_piece' => $length,
            'area_per_piece_sqm' => round($area_piece, 4),
            'area_sqm' => round($area, 4),
            'area_with_wastage_sqm' => round($with_waste, 4),
            'linear_meters' => round($linear, 4),
            'unit' => 'sqm',
            'derived' => true,
        ];
        $roll_len = (float)($in['roll_length'] ?? 0);
        if ($roll_len > 0) {
            // roll_length is expressed in the same unit as the roll width/length
            // input (default cm) — convert before dividing linear meters.
            $roll_len_m = $roll_len / $unit_div;
            if ($roll_len_m > 0) $out['rolls'] = round($linear / $roll_len_m, 4);
        }
        return $out;
    }

    /**
     * IMPOSITION FAMILY — digital/DTP + DI press.
     * Distinguishes finished copies, pages, production sheets and impressions.
     * Paper is derived from layout/imposition, sides and allowances. When the
     * numbers needed are unavailable the caller may pass a manual estimate —
     * clearly flagged as manual rather than silently assumed.
     */
    public function calc_imposition(array $in) {
        $copies = (float)($in['copies'] ?? 0);
        $pages = (float)($in['pages'] ?? 0);
        $ups = (float)($in['ups_per_sheet'] ?? 0);        // finished pieces per sheet
        $sides = (float)($in['sides'] ?? 1);
        $sheets_per_pack = (float)($in['sheets_per_pack'] ?? 0);
        $wastage_pct = (float)($in['wastage_pct'] ?? 0);
        $spoil = (float)($in['spoil_sheets'] ?? 0);
        $manual_sheets = isset($in['manual_sheets']) && $in['manual_sheets'] !== '' ? (float)$in['manual_sheets'] : null;

        $out = [
            'calc' => 'imposition',
            'finished_copies' => $copies,
            'pages' => $pages ?: null,
            'ups_per_sheet' => $ups ?: null,
            'sides' => $sides,
            'impressions' => null,
            'production_sheets' => null,
            'paper_sheets' => null,
            'manual_estimate' => false,
            'notes' => [],
        ];

        if ($ups <= 0 && $manual_sheets === null) {
            $out['notes'][] = 'Layout (ups per sheet) is required to derive paper automatically. Enter a manual sheet estimate to proceed.';
            return $out;
        }

        // impressions = printed sides pass through the press
        $sheets_for_copies = $ups > 0 ? ceil($copies / $ups) : 0;
        $impressions = $sheets_for_copies * max($sides, 1);
        $paper = $sheets_for_copies * max($sides, 1) / max($sides, 1); // paper sheets = production sheets for flat work
        $production_sheets = $sheets_for_copies;

        if ($manual_sheets !== null) {
            $out['manual_estimate'] = true;
            $out['production_sheets'] = $manual_sheets;
            $out['impressions'] = $impressions ?: ($manual_sheets * max($sides, 1));
            $paper = $manual_sheets;
        } else {
            $out['production_sheets'] = $production_sheets;
            $out['impressions'] = $impressions;
        }

        if ($spoil > 0) $paper += $spoil;
        if ($wastage_pct > 0) $paper = ceil($paper * (1 + $wastage_pct / 100));
        $out['paper_sheets'] = ceil($paper);
        if ($sheets_per_pack > 0) {
            $out['sheets_per_pack'] = $sheets_per_pack;
            $out['packs'] = round($out['paper_sheets'] / $sheets_per_pack, 4);
        }
        $out['derived'] = true;
        return $out;
    }

    /**
     * GARMENT FAMILY — size/colour breakdown must reconcile with item qty.
     * A mismatch is reported, not silently corrected.
     */
    public function calc_garment_sizes(array $in) {
        $qty = (float)($in['qty'] ?? 0);
        $breakdown = $in['size_breakdown'] ?? [];
        if (!is_array($breakdown)) $breakdown = [];
        $sum = 0; $clean = [];
        foreach ($breakdown as $k => $v) {
            $v = (float)$v;
            if ($v == 0) continue;
            $clean[$k] = $v;
            $sum += $v;
        }
        $colours = $in['colour_breakdown'] ?? [];
        $out = [
            'calc' => 'garment_sizes',
            'qty' => $qty,
            'size_breakdown' => $clean,
            'size_total' => $sum,
            'colours' => is_array($colours) ? $colours : [],
            'reconciled' => true,
            'derived' => true,
        ];
        if (empty($clean)) {
            $out['reconciled'] = false;
            $out['note'] = 'No size breakdown supplied — the item quantity is used as-is.';
        } elseif ($qty > 0 && abs($sum - $qty) > 0.0001) {
            $out['reconciled'] = false;
            $out['difference'] = round($qty - $sum, 4);
            $out['note'] = 'Size quantities total ' . $sum . ' but the item quantity is ' . $qty . '. Reconcile before confirming.';
        }
        return $out;
    }

    /** Dispatch a calculator by key. */
    public function run_calculator($calc_key, array $in) {
        switch ($calc_key) {
            case 'sheet': return $this->calc_sheet($in);
            case 'roll_area': return $this->calc_roll_area($in);
            case 'imposition': return $this->calc_imposition($in);
            case 'garment_sizes': return $this->calc_garment_sizes($in);
            default:
                return ['calc' => $calc_key ?: 'none', 'derived' => false, 'error' => 'No calculator configured for this category.'];
        }
    }

    /* ================================================================== */
    /*  ONE STOCK POSTING METHOD                                          */
    /*  reserve (no move) → issue to WIP (deduct ONCE) → consume / return. */
    /*  Nothing else may deduct: production logging and invoice conversion */
    /*  never post material moves. Reversals always post a counter-move.   */
    /* ================================================================== */

    /** Get (or create) the ledger row for a plan on a line. */
    public function get_material_issue($job_id, $line_id, $plan_id = null) {
        $this->db->where('job_id', $job_id)->where('line_id', $line_id);
        if ($plan_id) $this->db->where('plan_id', $plan_id);
        return $this->db->order_by('id', 'desc')->get('db_print_material_issues')->row();
    }

    public function get_material_issues($job_id) {
        return $this->db->where('job_id', $job_id)->order_by('line_id', 'asc')->order_by('id', 'asc')
            ->get('db_print_material_issues')->result();
    }

    /**
     * Availability check — usable stock before issuing.
     *
     * The table is `db_warehouseitems` (no underscore before "items") and the
     * column is `available_qty` — this previously read `db_warehouse_items` /
     * `qty`, which exist nowhere, so the table_exists() guard always returned
     * null and the caller silently saw "unknown availability". Matches the
     * sibling availability query in Nylon_model and the schema declared in
     * Stock_adjustment_model ($post_stock_tables).
     */
    public function material_available($item_id, $warehouse_id = null, $store_id = null) {
        if (!$this->db->table_exists('db_warehouseitems')) return null;
        if (empty($store_id)) $store_id = get_current_store_id();
        $this->db->select('COALESCE(SUM(available_qty),0) q')->where('item_id', $item_id)->where('store_id', $store_id);
        if ($warehouse_id) $this->db->where('warehouse_id', $warehouse_id);
        $r = $this->db->get('db_warehouseitems')->row();
        return $r ? (float)$r->q : 0.0;
    }

    /**
     * RESERVE — soft hold only. NO stock movement, so a reservation can never
     * double-deduct. Safe to call repeatedly (idempotent per plan).
     */
    public function reserve_material($job_id, $line_id, array $d) {
        $store_id = get_current_store_id();
        $job = $this->get_job($job_id);
        if (!$job) return ['success' => false, 'message' => 'Job not found.'];
        $plan_id = !empty($d['plan_id']) ? (int)$d['plan_id'] : null;
        $item_id = !empty($d['item_id']) ? (int)$d['item_id'] : null;
        if (!$item_id) return ['success' => false, 'message' => 'A stock item is required to reserve material.'];

        $planned = (float)($d['planned_qty'] ?? 0);
        $unit_id = !empty($d['unit_id']) ? (int)$d['unit_id'] : null;
        $base = $this->to_base_qty($item_id, $unit_id, $planned, $store_id);
        if ($base === null) return ['success' => false, 'message' => 'Unknown unit conversion — fix the plan unit before reserving.'];

        $row = [
            'store_id' => $store_id, 'job_id' => $job_id, 'line_id' => $line_id, 'plan_id' => $plan_id,
            'item_id' => $item_id, 'warehouse_id' => !empty($d['warehouse_id']) ? (int)$d['warehouse_id'] : null,
            'status' => 'reserved', 'planned_qty' => $base, 'reserved_qty' => $base,
            'note' => $d['note'] ?? null,
        ];
        $ex = $this->get_material_issue($job_id, $line_id, $plan_id);
        if ($ex && in_array($ex->status, ['reserved'], true)) {
            $this->db->where('id', $ex->id)->update('db_print_material_issues', $row);
            return ['success' => true, 'message' => 'Reservation updated (no stock moved).', 'id' => $ex->id, 'status' => 'reserved'];
        }
        if ($ex && in_array($ex->status, ['issued', 'partially_consumed', 'consumed'], true)) {
            return ['success' => false, 'message' => 'Material already issued for this plan — reverse the issue before re-reserving.'];
        }
        $this->db->insert('db_print_material_issues', $row);
        $id = $this->db->insert_id();
        return ['success' => true, 'message' => 'Reserved (no stock moved).', 'id' => $id, 'status' => 'reserved'];
    }

    /**
     * ISSUE TO WIP — the ONE place material leaves stock.
     * Posts a single deduction via the shared stock engine and records the
     * adjustment id so the move can always be reversed.
     */
    public function issue_material($issue_id, $qty = null, $user = null) {
        $store_id = get_current_store_id();
        $row = $this->db->where('id', $issue_id)->where('store_id', $store_id)->get('db_print_material_issues')->row();
        if (!$row) return ['success' => false, 'message' => 'Issue record not found.'];
        if (in_array($row->status, ['issued', 'partially_consumed', 'consumed'], true)) {
            return ['success' => false, 'message' => 'Material is already issued — stock was deducted once.'];
        }
        $qty = $qty === null ? (float)$row->reserved_qty : (float)$qty;
        if ($qty <= 0) return ['success' => false, 'message' => 'Issue quantity must be greater than zero.'];

        $job = $this->get_job($row->job_id);
        $unit_cost = $this->item_unit_cost($row->item_id);
        $adj = $this->post_stock_moves(
            $job->job_code . ' [WIP]',
            [['item_id' => $row->item_id, 'qty' => -$qty, 'description' => 'Print job ' . ($job->job_code ?? $row->job_id) . ' — issue to WIP']],
            'Print job ' . ($job->job_code ?? $row->job_id) . ' — material issued to production',
            $row->warehouse_id ?: null
        );
        if ($adj === false) return ['success' => false, 'message' => 'Stock posting failed.'];
        if (!$adj) return ['success' => false, 'message' => 'Nothing posted — check the item and quantity.'];

        $this->db->where('id', $issue_id)->update('db_print_material_issues', [
            'status' => 'issued',
            'issued_qty' => $qty,
            'unit_cost' => $unit_cost,
            'issued_cost' => round($qty * $unit_cost, 2),
            'issue_adjustment_id' => $adj,
            'issued_by' => $user ?: ($this->session->userdata('inv_username') ?: 'System'),
            'issued_at' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'message' => 'Issued to WIP — stock deducted once.', 'adjustment_id' => $adj, 'status' => 'issued'];
    }

    /**
     * CONSUME — record actual usage against the issued quantity. NO stock move:
     * the deduction already happened at issue. Anything not consumed is
     * expected back as an unused return.
     */
    public function consume_material($issue_id, $qty, $wastage = 0, $user = null) {
        $store_id = get_current_store_id();
        $row = $this->db->where('id', $issue_id)->where('store_id', $store_id)->get('db_print_material_issues')->row();
        if (!$row) return ['success' => false, 'message' => 'Issue record not found.'];
        if (!in_array($row->status, ['issued', 'partially_consumed', 'consumed'], true)) {
            return ['success' => false, 'message' => 'Issue the material before recording consumption.'];
        }
        $qty = (float)$qty; $wastage = (float)$wastage;
        if ($qty < 0 || $wastage < 0) return ['success' => false, 'message' => 'Consumed and wastage quantities cannot be negative.'];

        $consumed = (float)$row->consumed_qty + $qty;
        $waste_total = (float)$row->wastage_qty + $wastage;
        if ($consumed + $waste_total - (float)$row->returned_qty > (float)$row->issued_qty + 0.0001) {
            return ['success' => false, 'message' => 'Consumed + wastage exceeds the issued quantity. Record an unused return instead of over-consuming.'];
        }
        $status = ($consumed + $waste_total + (float)$row->returned_qty >= (float)$row->issued_qty - 0.0001) ? 'consumed' : 'partially_consumed';
        $this->db->where('id', $issue_id)->update('db_print_material_issues', [
            'consumed_qty' => $consumed,
            'wastage_qty' => $waste_total,
            'consumed_cost' => round($consumed * (float)$row->unit_cost, 2),
            'status' => $status,
            'consumed_by' => $user ?: ($this->session->userdata('inv_username') ?: 'System'),
            'consumed_at' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'message' => 'Consumption recorded (no extra stock movement).', 'status' => $status,
                'remaining' => round((float)$row->issued_qty - $consumed - $waste_total, 4)];
    }

    /**
     * UNUSED RETURN — the ONLY counter-posting. Returns an unused portion to
     * stock via one credit move. Reusable offcuts go back as stock; unusable
     * scrap is recorded as wastage and earns no credit.
     */
    public function return_unused_material($issue_id, $qty, $wastage = 0, $note = null, $user = null) {
        $store_id = get_current_store_id();
        $row = $this->db->where('id', $issue_id)->where('store_id', $store_id)->get('db_print_material_issues')->row();
        if (!$row) return ['success' => false, 'message' => 'Issue record not found.'];
        if (!in_array($row->status, ['issued', 'partially_consumed', 'consumed'], true)) {
            return ['success' => false, 'message' => 'Only issued material can be returned.'];
        }
        $qty = (float)$qty; $wastage = (float)$wastage;
        $available = (float)$row->issued_qty - (float)$row->consumed_qty - (float)$row->wastage_qty - (float)$row->returned_qty;
        if ($qty < 0 || $wastage < 0) return ['success' => false, 'message' => 'Return and wastage quantities cannot be negative.'];
        if ($qty + $wastage > $available + 0.0001) {
            return ['success' => false, 'message' => 'Return + wastage exceeds the unaccounted issued quantity (' . round($available, 4) . ').'];
        }
        $adj_id = null;
        if ($qty > 0) {
            $job = $this->get_job($row->job_id);
            $adj = $this->post_stock_moves(
                $job->job_code . ' [RET]',
                [['item_id' => $row->item_id, 'qty' => $qty, 'description' => 'Print job ' . ($job->job_code ?? $row->job_id) . ' — unused material returned']],
                'Print job ' . ($job->job_code ?? $row->job_id) . ' — unused material returned to stock',
                $row->warehouse_id ?: null
            );
            if ($adj === false) return ['success' => false, 'message' => 'Return posting failed.'];
            $adj_id = $adj ?: null;
        }
        $returned = (float)$row->returned_qty + $qty;
        $waste_total = (float)$row->wastage_qty + $wastage;
        $settled = (float)$row->consumed_qty + $waste_total + $returned;
        $status = $settled >= (float)$row->issued_qty - 0.0001 ? 'consumed' : 'partially_consumed';
        $this->db->where('id', $issue_id)->update('db_print_material_issues', [
            'returned_qty' => $returned,
            'wastage_qty' => $waste_total,
            'consumed_cost' => round((float)$row->consumed_qty * (float)$row->unit_cost, 2),
            'return_adjustment_id' => $adj_id,
            'status' => $status,
            'note' => $note ?: $row->note,
        ]);
        return ['success' => true, 'message' => $qty > 0 ? 'Unused material returned to stock.' : 'Scrap recorded.', 'status' => $status, 'adjustment_id' => $adj_id];
    }

    /**
     * RELEASE — drop a reservation that never left stock. Posts nothing.
     */
    public function release_material($issue_id) {
        $store_id = get_current_store_id();
        $row = $this->db->where('id', $issue_id)->where('store_id', $store_id)->get('db_print_material_issues')->row();
        if (!$row) return ['success' => false, 'message' => 'Record not found.'];
        if ((float)$row->issued_qty > 0) return ['success' => false, 'message' => 'Material already left stock — return the unused portion instead.'];
        $this->db->where('id', $issue_id)->update('db_print_material_issues', ['status' => 'released', 'reserved_qty' => 0]);
        return ['success' => true, 'message' => 'Reservation released (no stock impact).'];
    }

    /**
     * REVERSE — fully undo an issue. Returns every unaccounted unit to stock in
     * one counter-move and marks the ledger reversed. Historical costs stay.
     */
    public function reverse_material_issue($issue_id, $reason = null, $user = null) {
        $store_id = get_current_store_id();
        $row = $this->db->where('id', $issue_id)->where('store_id', $store_id)->get('db_print_material_issues')->row();
        if (!$row) return ['success' => false, 'message' => 'Record not found.'];
        if ((float)$row->issued_qty <= 0) return $this->release_material($issue_id);
        $unaccounted = (float)$row->issued_qty - (float)$row->consumed_qty - (float)$row->wastage_qty - (float)$row->returned_qty;
        if ($unaccounted > 0.0001) {
            $this->db->where('id', $issue_id)->update('db_print_material_issues', [
                'status' => 'issued', 'returned_qty' => (float)$row->returned_qty,
            ]);
            $res = $this->return_unused_material($issue_id, $unaccounted, 0, $reason ?: 'Issue reversed', $user);
            if (empty($res['success'])) return $res;
            $row = $this->db->where('id', $issue_id)->get('db_print_material_issues')->row();
        }
        $this->db->where('id', $issue_id)->update('db_print_material_issues', [
            'status' => 'returned',
            'reversal_adjustment_id' => $row->return_adjustment_id,
            'note' => $reason ?: $row->note,
        ]);
        return ['success' => true, 'message' => 'Issue reversed — unused stock returned.'];
    }

    /** Material cost actually consumed on a job (from the ledger, not estimates). */
    public function job_material_actual($job_id) {
        $r = $this->db->select('COALESCE(SUM(consumed_cost),0) consumed, COALESCE(SUM(wastage_qty * unit_cost),0) waste')
            ->where('job_id', $job_id)->get('db_print_material_issues')->row();
        return ['consumed' => round((float)$r->consumed, 2), 'wastage' => round((float)$r->waste, 2),
                'total' => round((float)$r->consumed + (float)$r->waste, 2)];
    }

    private function item_unit_cost($item_id) {
        $it = $this->db->select('purchase_price')->where('id', $item_id)->get('db_items')->row();
        return $it ? (float)$it->purchase_price : 0.0;
    }

    /* ============================ print authorization ===================== */

    /**
     * TRUE when this job owns a quotation that the customer has actually
     * accepted. Production may only be authorized against an accepted print
     * quotation — a general quotation (or none at all) must not release work
     * to the floor.
     */
    public function job_has_accepted_quotation($job_id) {
        $job = $this->get_job($job_id);
        if (!$job) return false;

        // A declined, cancelled or expired quotation can never release work,
        // regardless of any previously accepted revision.
        if (in_array($job->quote_lifecycle_status ?? '', ['declined', 'cancelled', 'expired'], true)) {
            return false;
        }

        // Linked to a real quotation: the accepted revision must still be current.
        if (!empty($job->quotation_id)) {
            $q = $this->quotation_for_job($job_id);
            if (!$q) return false;
            // An expired document is not acceptable even if a revision matched.
            if (($q->lifecycle_status ?? '') === 'expired') return false;
            return $job->quotation_revision_accepted !== null
                && (int)$job->quotation_revision_accepted === (int)$q->revision_no
                && !$this->quotation_change_requires_reacceptance($job_id);
        }

        // No quotation record: fall back to the in-job acceptance flag. A
        // quotation raised OUTSIDE the print workflow can never set this, so a
        // general quotation still cannot release production.
        return in_array($job->quotation_status, ['accepted', 'converted'], true);
    }

    /**
     * Explicitly raise a print job from an existing GENERAL quotation.
     *
     * This is the only supported way to take a non-production quotation into
     * production. It validates that the quotation is genuinely unlinked, copies
     * its lines onto the new job so the estimate carries over, and links the
     * two records. The original quotation document is never rewritten; its
     * revision history and any snapshots stay exactly as issued.
     */
    public function create_job_from_quotation($quotation_id, array $opts = []) {
        $quotation_id = (int)$quotation_id;
        $doc = $this->quotation_doc_type($quotation_id);
        if (!$doc['quotation']) return ['success' => false, 'message' => 'Quotation not found.'];
        if ($doc['kind'] === 'print') {
            return ['success' => false, 'message' => 'This quotation already belongs to print job '
                . ($doc['job']->job_code ?? '') . '.'];
        }
        $q = $doc['quotation'];
        if ($q->sales_status === 'Converted') {
            return ['success' => false, 'message' => 'This quotation is already converted to an invoice and cannot be raised as a print job.'];
        }
        if (!$q->customer_id) return ['success' => false, 'message' => 'The quotation needs a customer before it can become a print job.'];

        $items = $this->quotation_items($quotation_id);
        if (empty($items)) return ['success' => false, 'message' => 'The quotation has no lines to carry over.'];

        // Each carried line needs a print category so the job gets a stage plan.
        $cat_id = (int)($opts['category_id'] ?? 0);
        if (!$cat_id) {
            $cats = $this->get_categories($q->store_id);
            $cat_id = $cats ? (int)$cats[0]->id : 0;
        }
        if (!$cat_id) return ['success' => false, 'message' => 'No print category is configured for this store.'];

        $line_cfgs = [];
        foreach ($items as $it) {
            $line_cfgs[] = [
                'category_id' => $cat_id,
                'qty' => (float)$it->quotation_qty,
                'unit_price' => (float)$it->price_per_unit,
                'description' => $it->description,
                'spec' => [],
            ];
        }

        $job_id = $this->create_job([
            'customer_id' => (int)$q->customer_id,
            'title' => $opts['title'] ?? ('From quotation ' . $q->quotation_code),
            'due_date' => $opts['due_date'] ?? null,
            'quote_amount' => (float)$q->grand_total,
        ], $line_cfgs);
        if (!$job_id) return ['success' => false, 'message' => 'Could not create the print job.'];

        // Link the EXISTING quotation to the new job rather than issuing a new
        // one — that preserves the issued document and its revisions.
        $this->db->where('id', $job_id)->update('db_print_jobs', [
            'quotation_id' => $quotation_id,
            'quote_amount' => (float)$q->grand_total,
            'quotation_status' => 'issued',
        ]);

        if ($this->db->table_exists('db_print_quotation_review')) {
            $ex = $this->db->where('store_id', $q->store_id)->where('quotation_id', $quotation_id)
                ->get('db_print_quotation_review')->row();
            $row = [
                'store_id' => $q->store_id, 'quotation_id' => $quotation_id,
                'reason' => 'Raised as a print job from a general quotation',
                'decision' => 'linked',
                'reviewed_by' => $this->session->userdata('inv_username') ?: 'System',
                'reviewed_at' => date('Y-m-d H:i:s'),
            ];
            if ($ex) $this->db->where('id', $ex->id)->update('db_print_quotation_review', $row);
            else $this->db->insert('db_print_quotation_review', $row);
        }

        return ['success' => true, 'message' => 'Print job created from quotation ' . $q->quotation_code . '.',
                'job_id' => $job_id, 'quotation_id' => $quotation_id];
    }

    /* ---------------------- quotation lifecycle --------------------------- */

    /**
     * Customer declined the quotation. The job stops waiting for acceptance so
     * it does not sit open forever. Declining does NOT delete anything — the
     * issued document, its revisions and any payments remain intact.
     */
    public function decline_quotation($job_id, $reason = null, $channel = 'ui') {
        $job = $this->get_job($job_id);
        if (!$job) return ['success' => false, 'message' => 'Job not found.'];
        $q = $this->quotation_for_job($job_id);
        if ($q && $q->sales_status === 'Converted') {
            return ['success' => false, 'message' => 'This quotation is already converted to an invoice and cannot be declined.'];
        }
        if ($job->production_status === 'completed') {
            return ['success' => false, 'message' => 'This job is completed — the quotation cannot be declined.'];
        }
        $prev = $job->quote_lifecycle_status ?? 'issued';
        $this->db->where('id', $job_id)->update('db_print_jobs', [
            'quote_lifecycle_status' => 'declined',
            'quote_response_reason' => $reason,
            'quotation_status' => 'declined',
        ]);
        $this->set_quotation_lifecycle($job->quotation_id, 'declined', $reason, $channel, $job_id);
        $this->log_lifecycle($job->store_id, $job->quotation_id, $job_id, $prev, 'declined', $reason, $channel);
        return ['success' => true, 'message' => 'Quotation declined. The job is closed to production.'];
    }

    /**
     * Cancel the whole job (customer walked away, duplicate, etc.). Preserves
     * all history; simply stops the job occupying the pipeline.
     */
    public function cancel_job($job_id, $reason = null, $channel = 'ui') {
        $job = $this->get_job($job_id);
        if (!$job) return ['success' => false, 'message' => 'Job not found.'];
        if ($job->production_status === 'completed') {
            return ['success' => false, 'message' => 'This job is already completed and cannot be cancelled.'];
        }
        $prev = $job->quote_lifecycle_status ?? 'issued';
        $this->db->where('id', $job_id)->update('db_print_jobs', [
            'quote_lifecycle_status' => 'cancelled',
            'quote_response_reason' => $reason,
            'production_status' => 'cancelled',
        ]);
        $this->set_quotation_lifecycle($job->quotation_id, 'cancelled', $reason, $channel, $job_id);
        $this->log_lifecycle($job->store_id, $job->quotation_id, $job_id, $prev, 'cancelled', $reason, $channel);
        return ['success' => true, 'message' => 'Job cancelled. History and payments are preserved.'];
    }

    /** Reopen a declined/cancelled quotation for a fresh attempt. */
    public function reopen_quotation($job_id) {
        $job = $this->get_job($job_id);
        if (!$job) return ['success' => false, 'message' => 'Job not found.'];
        if (!in_array($job->quote_lifecycle_status, ['declined', 'cancelled', 'expired'], true)) {
            return ['success' => false, 'message' => 'Only a declined, cancelled or expired quotation can be reopened.'];
        }
        $prev = $job->quote_lifecycle_status;
        $this->db->where('id', $job_id)->update('db_print_jobs', [
            'quote_lifecycle_status' => 'issued',
            'quote_response_reason' => null,
            'quote_expired_at' => null,
            'quotation_status' => 'issued',
            'production_status' => $job->production_status === 'cancelled' ? 'planned' : $job->production_status,
        ]);
        $this->set_quotation_lifecycle($job->quotation_id, 'issued', null, 'ui', $job_id);
        $this->log_lifecycle($job->store_id, $job->quotation_id, $job_id, $prev, 'issued', 'reopened', 'ui');
        return ['success' => true, 'message' => 'Quotation reopened.'];
    }

    /** Mirror lifecycle onto the authoritative document. */
    private function set_quotation_lifecycle($quotation_id, $status, $reason = null, $channel = 'ui', $job_id = null) {
        if (empty($quotation_id) || !$this->db->table_exists('db_quotation')) return;
        $set = ['lifecycle_status' => $status];
        if (in_array($status, ['accepted', 'declined', 'cancelled'], true)) {
            $set['responded_at'] = date('Y-m-d H:i:s');
            $set['response_reason'] = $reason;
        } elseif ($status === 'expired') {
            $set['expired_at'] = date('Y-m-d H:i:s');
        }
        $this->db->where('id', (int)$quotation_id)->update('db_quotation', $set);
    }

    private function log_lifecycle($store_id, $quotation_id, $job_id, $from, $to, $reason, $channel = 'ui') {
        if (!$this->db->table_exists('db_quotation_lifecycle_log')) return;
        $this->db->insert('db_quotation_lifecycle_log', [
            'store_id' => $store_id,
            'quotation_id' => (int)$quotation_id,
            'job_id' => $job_id ? (int)$job_id : null,
            'from_status' => $from,
            'to_status' => $to,
            'reason' => $reason,
            'actor' => $this->session->userdata('inv_username') ?: 'System',
            'channel' => $channel,
        ]);
    }

    /**
     * Expire quotations whose expiry date has passed.
     *
     * Runs from cron. Only quotations still awaiting a decision are expired —
     * accepted, declined, cancelled and converted records are never touched.
     * Returns the number expired.
     */
    public function expire_due_quotations($store_id = null) {
        $today = date('Y-m-d');
        $this->db->select('q.id,q.store_id,q.expire_date,j.id job_id,j.quote_lifecycle_status')
            ->from('db_quotation q')
            ->join('db_print_jobs j', 'j.quotation_id = q.id', 'left')
            ->where('q.expire_date IS NOT NULL', null, false)
            ->where('q.expire_date <', $today)
            ->where_in('q.lifecycle_status', ['issued'])
            ->where('(q.sales_status IS NULL OR q.sales_status != "Converted")', null, false);
        if ($store_id) $this->db->where('q.store_id', (int)$store_id);
        $rows = $this->db->get()->result();

        $n = 0;
        foreach ($rows as $r) {
            $this->db->where('id', (int)$r->id)->update('db_quotation', [
                'lifecycle_status' => 'expired',
                'expired_at' => date('Y-m-d H:i:s'),
            ]);
            if ($r->job_id) {
                $this->db->where('id', (int)$r->job_id)->update('db_print_jobs', [
                    'quote_lifecycle_status' => 'expired',
                    'quote_expired_at' => date('Y-m-d H:i:s'),
                    'quotation_status' => 'expired',
                ]);
            }
            $this->log_lifecycle($r->store_id, $r->id, $r->job_id, 'issued', 'expired', 'Past expiry date', 'cron');
            $n++;
        }
        return $n;
    }

    /**
     * Turn an enquiry LEAD into a print job (and optionally issue its quotation).
     *
     * This is how a storefront request becomes real work. The lead's captured
     * specification is carried onto the job so nothing has to be re-typed, and
     * the lead is marked converted with a link to the job.
     */
    public function create_job_from_lead($lead_id, array $opts = []) {
        $lead_id = (int)$lead_id;
        if (!$this->db->table_exists('db_leads')) return ['success' => false, 'message' => 'Leads module not available.'];
        $lead = $this->db->where('id', $lead_id)->get('db_leads')->row();
        if (!$lead) return ['success' => false, 'message' => 'Enquiry not found.'];

        $store_id = (int)$lead->store_id;
        if (!empty($lead->converted_customer_id)) {
            // Already a customer — reuse them instead of creating a duplicate.
            $customer_id = (int)$lead->converted_customer_id;
        } else {
            $customer_id = $this->find_or_create_customer_from_lead($lead);
            if (!$customer_id) return ['success' => false, 'message' => 'Could not create a customer from this enquiry.'];
        }

        // Category: caller may choose; otherwise the first configured.
        $cat_id = (int)($opts['category_id'] ?? 0);
        if (!$cat_id) {
            $cats = $this->get_categories($store_id);
            $cat_id = $cats ? (int)$cats[0]->id : 0;
        }
        if (!$cat_id) return ['success' => false, 'message' => 'No print category is configured for this store.'];

        // Build a starting line from whatever the enquiry captured.
        $detail = trim((string)($lead->enquiry ?: $lead->interest ?: ''));
        $line = [
            'category_id' => $cat_id,
            'qty' => (float)($opts['qty'] ?? 1) ?: 1,
            'unit_price' => (float)($opts['unit_price'] ?? 0),
            'description' => $opts['title'] ?? ($lead->interest ?: 'Print job from enquiry'),
            'spec' => [],
        ];
        if ($detail !== '') $line['spec']['enquiry_notes'] = $detail;

        $job_id = $this->create_job([
            'customer_id' => $customer_id,
            'title' => $opts['title'] ?? ($lead->interest ?: ('Enquiry #' . $lead_id)),
            'due_date' => !empty($lead->preferred_date) ? $lead->preferred_date : ($opts['due_date'] ?? null),
        ], [$line]);
        if (!$job_id) return ['success' => false, 'message' => 'Could not create the print job.'];

        // Mark the lead converted and point it at the job.
        $upd = ['status' => 'converted'];
        if ($this->db->field_exists('converted_at', 'db_leads')) $upd['converted_at'] = date('Y-m-d H:i:s');
        if ($this->db->field_exists('converted_customer_id', 'db_leads')) $upd['converted_customer_id'] = $customer_id;
        $this->db->where('id', $lead_id)->update('db_leads', $upd);

        $result = ['success' => true, 'message' => 'Print job created from enquiry.', 'job_id' => $job_id, 'customer_id' => $customer_id];

        // Optionally issue the quotation straight away.
        if (!empty($opts['issue_quotation'])) {
            $q = $this->quote_to_quotation($job_id, ['customer_id' => $customer_id]);
            $result['quotation'] = $q;
            $result['message'] = !empty($q['success'])
                ? 'Print job and quotation created from enquiry.'
                : 'Print job created; quotation could not be issued: ' . ($q['message'] ?? '');
        }
        return $result;
    }

    /** Find an existing customer by email/phone, else create one, from a lead. */
    private function find_or_create_customer_from_lead($lead) {
        $store_id = (int)$lead->store_id;
        $existing = null;
        if (!empty($lead->email)) {
            $existing = $this->db->where('store_id', $store_id)->where('email', $lead->email)->get('db_customers')->row();
        }
        if (!$existing && !empty($lead->phone)) {
            $existing = $this->db->where('store_id', $store_id)
                ->where('(mobile = ? OR phone = ?)', null, false)
                ->get('db_customers')->row();
        }
        if ($existing) return (int)$existing->id;

        $this->db->insert('db_customers', [
            'store_id' => $store_id,
            'customer_name' => $lead->name ?: 'Enquiry customer',
            'mobile' => $lead->phone ?: null,
            'email' => $lead->email ?: null,
            'status' => 1,
        ]);
        $id = (int)$this->db->insert_id();
        return $id ?: 0;
    }

    /** Quotations needing an expiry reminder.
     *
     * Returns rows with a `due` flag of '3d' or '1d'. Each flag is sent at most
     * once — the sent timestamp is stamped by mark_reminder_sent(), so repeated
     * cron runs cannot spam the customer.
     */
    public function quotations_due_expiry_reminder($store_id = null, $days = [3, 1]) {
        $today = date('Y-m-d');
        $out = [];
        foreach ($days as $d) {
            $target = date('Y-m-d', strtotime('+' . (int)$d . ' days'));
            $col = ((int)$d === 3) ? 'q.reminder_3d_sent_at' : 'q.reminder_1d_sent_at';
            $this->db->select('q.id,q.store_id,q.quotation_code,q.expire_date,q.customer_id,q.grand_total,q.lifecycle_status,j.id job_id,j.job_code')
                ->from('db_quotation q')
                ->join('db_print_jobs j', 'j.quotation_id = q.id', 'left')
                ->where('q.expire_date', $target)
                ->where('q.lifecycle_status', 'issued')
                ->where('(' . $col . ' IS NULL)', null, false)
                ->where('(q.sales_status IS NULL OR q.sales_status != "Converted")', null, false);
            if ($store_id) $this->db->where('q.store_id', (int)$store_id);
            foreach ($this->db->get()->result() as $r) {
                $r->reminder_days = (int)$d;
                $out[] = $r;
            }
        }
        return $out;
    }

    /** Stamp a reminder as sent so it is never sent twice. */
    public function mark_reminder_sent($quotation_id, $days) {
        $col = ((int)$days === 3) ? 'reminder_3d_sent_at' : 'reminder_1d_sent_at';
        $this->db->where('id', (int)$quotation_id)->update('db_quotation', [$col => date('Y-m-d H:i:s')]);
        return true;
    }

    /** Days until expiry (negative = already past). Null when no expiry set. */
    public function days_to_expiry($expire_date) {
        if (empty($expire_date)) return null;
        $d = strtotime($expire_date);
        if (!$d) return null;
        $today = strtotime(date('Y-m-d'));
        return (int)floor(($d - $today) / 86400);
    }

    public function request_authorization($job_id, $approver_id, $backup_approver_id = null) {
        $job = $this->get_job($job_id);
        if (!$job) return ['success' => false, 'message' => 'Job not found.'];
        // Production gate: the customer must have ACCEPTED the quotation before
        // work can be authorised. A quotation raised outside the print workflow
        // (a general quotation) never sets this and can never release work.
        if (empty($job->quotation_id) && !in_array($job->quotation_status, ['accepted', 'converted'], true)) {
            return ['success' => false, 'message' => 'This job has no accepted quotation. Issue the quotation and obtain customer acceptance before requesting print authorization.'];
        }
        if (!$this->job_has_accepted_quotation($job_id)) {
            return ['success' => false, 'message' => 'The quotation must be accepted by the customer (at its current revision) before print authorization.'];
        }
        if ($job->artwork_status !== 'approved') return ['success' => false, 'message' => 'Artwork must be approved first.'];
        if ($job->design_status !== 'cleared') return ['success' => false, 'message' => 'Designer must clear the artwork first.'];
        $art = $this->db->where('job_id', $job_id)->where('status', 'approved')
            ->order_by('version_no', 'desc')->limit(1)->get('db_print_artworks')->row();
        $this->db->insert('db_print_authorizations', [
            'store_id' => get_current_store_id(),
            'job_id' => $job_id,
            'artwork_id' => $art->id,
            'artwork_version' => $art->version_no,
            'approver_id' => $approver_id,
            'backup_approver_id' => $backup_approver_id,
            'status' => 'requested',
            'requested_by' => $this->session->userdata('inv_username') ?: 'System',
        ]);
        $this->db->where('id', $job_id)->update('db_print_jobs', ['authorization_status' => 'requested']);
        return ['success' => true, 'message' => 'Authorization requested.'];
    }

    public function decide_authorization($auth_id, $decision, $reason = '', $urgent_override = false) {
        if (!in_array($decision, ['authorized', 'rejected', 'returned'])) {
            return ['success' => false, 'message' => 'Invalid decision.'];
        }
        $auth = $this->db->where('id', $auth_id)->get('db_print_authorizations')->row();
        if (!$auth || $auth->status !== 'requested') {
            return ['success' => false, 'message' => 'Authorization not pending.'];
        }
        $art = $this->db->where('job_id', $auth->job_id)->where('status', 'approved')
            ->order_by('version_no', 'desc')->limit(1)->get('db_print_artworks')->row();
        if (!$art || (int)$art->version_no !== (int)$auth->artwork_version) {
            $this->db->where('id', $auth_id)->update('db_print_authorizations', ['status' => 'invalidated']);
            $this->db->where('id', $auth->job_id)->update('db_print_jobs', ['authorization_status' => 'none']);
            return ['success' => false, 'message' => 'Artwork version changed — authorization invalidated. Re-request.'];
        }
        $this->db->where('id', $auth_id)->update('db_print_authorizations', [
            'status' => $decision,
            'reason' => $reason,
            'urgent_override' => $urgent_override ? 1 : 0,
            'decided_by' => $this->session->userdata('inv_username') ?: 'System',
            'decided_at' => date('Y-m-d H:i:s'),
        ]);
        $new_status = $decision === 'authorized' ? 'authorized' : $decision;
        $this->db->where('id', $auth->job_id)->update('db_print_jobs', ['authorization_status' => $new_status]);
        return ['success' => true, 'message' => 'Authorization ' . $decision . '.'];
    }

    public function authorization_valid($job_id) {
        $job = $this->get_job($job_id);
        if (!$job || $job->authorization_status !== 'authorized') return false;
        $art = $this->db->where('job_id', $job_id)->where('status', 'approved')
            ->order_by('version_no', 'desc')->limit(1)->get('db_print_artworks')->row();
        if (!$art) return false;
        $auth = $this->db->where('job_id', $job_id)->where('status', 'authorized')
            ->order_by('id', 'desc')->limit(1)->get('db_print_authorizations')->row();
        if (!$auth) return false;
        return (int)$auth->artwork_version === (int)$art->version_no;
    }

    /* ============================ production gates ========================= */

    public function production_prerequisites($job_id) {
        $job = $this->get_job($job_id);
        if (!$job) return ['ok' => false, 'missing' => ['job_not_found']];
        $missing = [];
        if ($job->artwork_status !== 'approved') $missing[] = 'artwork_approved';
        if ($job->design_status !== 'cleared') $missing[] = 'designer_cleared';
        if (!$this->authorization_valid($job_id)) $missing[] = 'print_authorized';
        if (!$this->deposit_gate_met($job_id)) $missing[] = 'deposit_verified';
        return ['ok' => empty($missing), 'missing' => $missing];
    }

    /**
     * Stage progress for the job header strip.
     *
     * Each stage reports:
     *   done   — the stage's condition is satisfied
     *   active — this is the stage the job is currently waiting on
     *   pending— not started yet
     *
     * Exactly one stage is 'active': the first stage that is not done. Once
     * every stage is done, none is active. This is what makes the strip show
     * live progress instead of a static list of labels.
     */
    public function stage_progress($job_id) {
        $job = $this->get_job($job_id);
        if (!$job) return [];
        $prereq = $this->production_prerequisites($job_id);

        // Quotation is done once the customer has accepted it. When backed by
        // the real module, a revision newer than the accepted one reopens it.
        $quote_done = in_array($job->quotation_status, ['accepted', 'converted'], true)
            && !$this->quotation_change_requires_reacceptance($job_id);

        $deposit_done = in_array($job->payment_status, ['verified', 'paid'], true);
        $artwork_done = ($job->artwork_status === 'approved');
        $design_done  = ($job->design_status === 'cleared');
        $auth_done    = $this->authorization_valid($job_id);
        $prod_done    = in_array($job->production_status, ['completed'], true);
        $prod_active  = ($job->production_status === 'in_progress');
        $coll_done    = in_array($job->fulfilment_status, ['collected', 'partially_collected', 'delivered'], true);

        $stages = [
            ['key' => 'quotation', 'label' => 'Quotation',  'done' => $quote_done,
             'hint' => $quote_done ? 'Accepted' : 'Awaiting customer acceptance'],
            ['key' => 'deposit',   'label' => 'Deposit',    'done' => $deposit_done,
             'hint' => $deposit_done ? 'Verified' : 'Awaiting verified deposit'],
            ['key' => 'artwork',   'label' => 'Artwork',    'done' => $artwork_done,
             'hint' => $artwork_done ? 'Approved' : 'Awaiting customer artwork approval'],
            ['key' => 'design',    'label' => 'Design',     'done' => $design_done,
             'hint' => $design_done ? 'Cleared' : 'Awaiting designer clearance'],
            ['key' => 'auth',      'label' => 'Print Auth', 'done' => $auth_done,
             'hint' => $auth_done ? 'Authorized' : 'Awaiting print authorization'],
            ['key' => 'production','label' => 'Production', 'done' => $prod_done,
             'hint' => $prod_done ? 'Completed' : ($prod_active ? 'In progress' : 'Starts after the gates above')],
            ['key' => 'collection','label' => 'Collection', 'done' => $coll_done,
             'hint' => $coll_done ? 'Handed over' : 'Awaiting collection / delivery'],
        ];

        // Mark the first unfinished stage as active. Production additionally
        // counts as active while it is running but not yet complete.
        $active_set = false;
        foreach ($stages as $i => $s) {
            if ($s['done']) { $stages[$i]['state'] = 'done'; continue; }
            if ($s['key'] === 'production' && $prod_active) {
                $stages[$i]['state'] = 'active';
                $active_set = true;
                continue;
            }
            if (!$active_set) {
                $stages[$i]['state'] = 'active';
                $active_set = true;
            } else {
                $stages[$i]['state'] = 'pending';
            }
        }

        // Blocked stages: once production is reached, the gates that are still
        // unsatisfied are shown as blocked so the reason is explicit.
        if (!$prereq['ok']) {
            foreach ($stages as $i => $s) {
                if (in_array($s['key'], ['artwork', 'design', 'auth', 'deposit'], true) && !$s['done']) {
                    $stages[$i]['blocked'] = true;
                }
            }
        }
        return $stages;
    }

    /* ============================ stock engine ============================= */

    /**
     * Post signed stock moves through db_stockadjustment (same engine as Nylon).
     * $moves = [ ['item_id'=>x, 'qty'=>±n, 'description'=>..], ... ].
     * Returns adjustment id (0 = nothing to post), false = error.
     *
     * PUBLIC posting entry point for the machine-consumables and customer-custody
     * workflows (Printing_ops_model).
     *
     * Deliberately a thin wrapper rather than a second implementation: there is
     * exactly ONE place in the application that writes stock for printing, so a
     * change to the posting rules can never miss a caller. The ops module must
     * never grow its own deduction logic.
     */
    public function post_stock_moves_public($reference, array $moves, $note, $warehouse_id = null) {
        return $this->post_stock_moves($reference, $moves, $note, $warehouse_id);
    }

    private function post_stock_moves($reference, array $moves, $note, $warehouse_id = null) {
        $moves = array_values(array_filter($moves, function ($m) { return !empty($m['item_id']) && (float)$m['qty'] != 0.0; }));
        if (empty($moves)) return 0;
        $store_id = get_current_store_id();
        if (empty($warehouse_id)) $warehouse_id = get_store_warehouse_id();

        $this->db->insert('db_stockadjustment', [
            'store_id' => $store_id,
            'warehouse_id' => $warehouse_id,
            'reference_no' => $reference,
            'adjustment_date' => date('Y-m-d'),
            'adjustment_note' => $note,
            'created_date' => date('Y-m-d'),
            'created_time' => date('H:i:s'),
            'created_by' => $this->session->userdata('inv_username') ?: 'System',
            'system_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            'system_name' => 'Printing Production',
            'status' => 1,
        ]);
        $adjustment_id = $this->db->insert_id();
        if (!$adjustment_id) return false;

        $affected = [];
        foreach ($moves as $m) {
            $this->db->insert('db_stockadjustmentitems', [
                'store_id' => $store_id,
                'warehouse_id' => $warehouse_id,
                'adjustment_id' => $adjustment_id,
                'item_id' => $m['item_id'],
                'adjustment_qty' => $m['qty'],
                'description' => $m['description'] ?? $note,
                'status' => 1,
            ]);
            $affected[] = [$m['item_id']];
        }
        $this->load->model('pos_model');
        foreach ($affected as $row) {
            $this->pos_model->update_items_quantity($row[0]);
        }
        if (function_exists('update_warehouse_items')) update_warehouse_items($affected);
        return $adjustment_id;
    }

    /* ============================ stage reporting ========================== */

    /**
     * Report a stage log: material issue (qty_in), good output, rework,
     * partial, rejects, waste, scrap recovery, outsourcing cost, labour cost.
     * Working stages post stock immediately; QC waits for supervisor approval.
     */
    public function report_stage($job_id, $stage_id, $d) {
        $job = $this->get_job($job_id);
        $stage = $this->db->where('id', $stage_id)->where('job_id', $job_id)->get('db_print_stages')->row();
        if (!$job || !$stage) return ['success' => false, 'message' => 'Job or stage not found.'];
        if (in_array($job->production_status, ['completed', 'cancelled'])) {
            return ['success' => false, 'message' => 'Job is ' . $job->production_status . '.'];
        }
        if (in_array($stage->status, ['done', 'skipped'])) {
            return ['success' => false, 'message' => 'This stage is already closed.'];
        }

        // Server-side gate for stages that require artwork + authorization (print/transfer/screen_make).
        if (!empty($stage->requires_authorization)) {
            $pre = $this->production_prerequisites($job_id);
            if (!$pre['ok']) {
                return ['success' => false, 'message' => 'Production blocked — missing: ' . implode(', ', $pre['missing'])];
            }
        }

        $qty_in = max(0, (float)($d['qty_in'] ?? 0));
        $good = max(0, (float)($d['good_qty'] ?? 0));
        $rework = max(0, (float)($d['rework_qty'] ?? 0));
        $partial = max(0, (float)($d['partially_done_qty'] ?? 0));
        $reject = max(0, (float)($d['reject_qty'] ?? 0));
        $waste = max(0, (float)($d['waste_qty'] ?? 0));
        $scrap = max(0, (float)($d['scrap_qty'] ?? 0));
        if ($qty_in <= 0 && $good <= 0 && $rework <= 0 && $reject <= 0 && $waste <= 0) {
            return ['success' => false, 'message' => 'Nothing to report.'];
        }

        $input_cost = 0;
        // Historic cost of consumed input = purchase price at posting time.
        if ($stage->input_item_id && $stage->input_item_id != $job->customer_id && $qty_in > 0) {
            $in_item = $this->db->select('purchase_price')->where('id', $stage->input_item_id)->get('db_items')->row();
            $input_cost = round($qty_in * (float)($in_item->purchase_price ?? 0), 2);
        }

        $log = [
            'store_id' => get_current_store_id(),
            'job_id' => $job_id,
            'stage_id' => $stage_id,
            'work_date' => !empty($d['work_date']) ? $d['work_date'] : date('Y-m-d'),
            'machine_id' => (int)($d['machine_id'] ?? 0) ?: null,
            'operator_id' => (int)($d['operator_id'] ?? 0) ?: null,
            'qty_in' => $qty_in,
            'good_qty' => $good,
            'rework_qty' => $rework,
            'partially_done_qty' => $partial,
            'reject_qty' => $reject,
            'waste_qty' => $waste,
            'scrap_qty' => $scrap,
            'scrap_item_id' => (int)($d['scrap_item_id'] ?? 0) ?: null,
            'input_cost' => $input_cost,
            'outsource_cost' => (float)($d['outsource_cost'] ?? 0),
            'labour_cost' => (float)($d['labour_cost'] ?? 0),
            'notes' => $d['notes'] ?? null,
            'status' => 'submitted',
            'submitted_by' => $this->session->userdata('inv_username') ?: 'System',
        ];
        $this->db->insert('db_print_stage_logs', $log);
        $log_id = $this->db->insert_id();
        $log['id'] = $log_id;

        if ($stage->status === 'pending') {
            $this->db->where('id', $stage_id)->update('db_print_stages', ['status' => 'in_progress', 'started_at' => date('Y-m-d H:i:s')]);
        }
        if ($job->production_status === 'planned') {
            $this->db->where('id', $job_id)->update('db_print_jobs', ['production_status' => 'in_progress']);
        }

        // Non-QC stages post stock now; QC waits for approval.
        if ($stage->stage_key !== 'qc') {
            $moves = $this->stage_stock_moves($job, $stage, (object)$log);
            $adj = $this->post_stock_moves($job->job_code . ' [PROD]', $moves, 'Print job ' . $job->job_code . ' — ' . $stage->stage_label, $job->warehouse_id);
            if ($adj === false) {
                log_message('error', 'Printing report_stage stock post failed for log ' . $log_id);
            } elseif ($adj > 0) {
                $this->db->where('id', $log_id)->update('db_print_stage_logs', ['adjustment_id' => $adj]);
            }
        }
        $this->_recalc_costs($job_id);
        return ['success' => true, 'message' => 'Report saved.', 'log_id' => $log_id];
    }

    /**
     * Compute signed stock moves for a stage log.
     *
     * DOUBLE-DEDUCTION GUARD: material that is already tracked in
     * db_print_material_issues (issue-to-WIP) is NOT deducted here — stock left
     * at issue time. This method only handles the finished-goods side and
     * scrap recovery. Report/approve paths never re-deduct issued material.
     */
    private function stage_stock_moves($job, $stage, $log) {
        $moves = [];
        $in = (int)$stage->input_item_id;
        $out = (int)$stage->output_item_id;
        $ref = $job->job_code . '/' . $stage->stage_key;
        // Input consumed (materials issue) — ONLY for inputs that are not managed
        // by the issue ledger. Supplied material is not our stock either.
        if ($stage->stage_key !== 'qc' && $in && (float)$log->qty_in > 0 && !$this->input_is_ledger_managed($job->id, $in)) {
            $moves[] = ['item_id' => $in, 'qty' => -(float)$log->qty_in, 'description' => $ref . ' — input consumed'];
        }
        // Good output credited to an output item (finished product) — but only
        // the QC stage releases saleable stock; intermediate output is WIP.
        if ($stage->stage_key === 'qc' && $out && (float)$log->good_qty > 0) {
            $moves[] = ['item_id' => $out, 'qty' => (float)$log->good_qty, 'description' => $ref . ' — QC-approved output'];
        }
        // Scrap recovery.
        if (!empty($log->scrap_item_id) && (float)$log->scrap_qty > 0) {
            $moves[] = ['item_id' => (int)$log->scrap_item_id, 'qty' => (float)$log->scrap_qty, 'description' => $ref . ' — reusable scrap'];
        }
        return $moves;
    }

    /**
     * TRUE when this item already has an issue ledger row on the job (so stock
     * has — or will be — deducted exactly once by the issue workflow).
     */
    public function input_is_ledger_managed($job_id, $item_id) {
        if (!$this->db->table_exists('db_print_material_issues')) return false;
        $r = $this->db->where('job_id', $job_id)->where('item_id', $item_id)
            ->where_in('status', ['reserved', 'issued', 'partially_consumed', 'consumed'])
            ->get('db_print_material_issues')->row();
        return !empty($r);
    }

    /** Supervisor approves a QC log — releases finished output into stock. */
    public function approve_stage_log($log_id) {
        $log = $this->db->where('id', $log_id)->get('db_print_stage_logs')->row();
        if (!$log || $log->status !== 'submitted') return ['success' => false, 'message' => 'Nothing to approve.'];
        $job = $this->get_job($log->job_id);
        $stage = $this->db->where('id', $log->stage_id)->get('db_print_stages')->row();
        if (!$job || !$stage) return ['success' => false, 'message' => 'Job or stage missing.'];

        if ($stage->stage_key === 'qc' && empty($log->adjustment_id)) {
            $adj = $this->post_stock_moves(
                $job->job_code . ' [QC]',
                $this->stage_stock_moves($job, $stage, $log),
                'Print job ' . $job->job_code . ' — QC-approved release',
                $job->warehouse_id
            );
            if ($adj === false) return ['success' => false, 'message' => 'Stock posting failed.'];
            if ($adj > 0) {
                $this->db->where('id', $log_id)->update('db_print_stage_logs', ['adjustment_id' => $adj]);
            }
        }
        $this->db->where('id', $log_id)->update('db_print_stage_logs', [
            'status' => 'approved', 'approved_by' => $this->session->userdata('inv_username') ?: 'System', 'approved_at' => date('Y-m-d H:i:s'),
        ]);
        $this->_recalc_costs($log->job_id);
        return ['success' => true, 'message' => 'Report approved.'];
    }

    /** Reverse a posted stage log — posts counter-adjustment, keeps original. */
    public function reverse_stage_log($log_id, $reason = '') {
        $log = $this->db->where('id', $log_id)->get('db_print_stage_logs')->row();
        if (!$log || $log->status === 'reversed') return ['success' => false, 'message' => 'Cannot reverse this report.'];
        $job = $this->get_job($log->job_id);
        $stage = $this->db->where('id', $log->stage_id)->get('db_print_stages')->row();
        if (!$job || !$stage) return ['success' => false, 'message' => 'Job or stage missing.'];

        if (!empty($log->adjustment_id)) {
            $moves = $this->stage_stock_moves($job, $stage, $log);
            foreach ($moves as &$m) { $m['qty'] = -$m['qty']; $m['description'] = 'REVERSAL — ' . $m['description']; }
            unset($m);
            $adj = $this->post_stock_moves($job->job_code . ' [REV]', $moves, 'Reversal of log #' . $log_id . ' on ' . $job->job_code . ($reason ? ': ' . $reason : ''), $job->warehouse_id);
            if ($adj === false) return ['success' => false, 'message' => 'Reversal posting failed.'];
            if ($adj > 0) $this->db->where('id', $log_id)->update('db_print_stage_logs', ['reversal_adjustment_id' => $adj]);
        }
        $this->db->where('id', $log_id)->update('db_print_stage_logs', [
            'status' => 'reversed', 'reversed_by' => $this->session->userdata('inv_username') ?: 'System',
            'reversed_at' => date('Y-m-d H:i:s'), 'reversal_reason' => $reason ?: null,
        ]);
        $this->_recalc_costs($log->job_id);
        return ['success' => true, 'message' => 'Report reversed and stock corrected.'];
    }

    /** Mark a stage done; optionally record an outsourcing (vendor + cost). */
    public function complete_stage($stage_id, $outsource_vendor = null, $outsource_cost = null) {
        $stage = $this->db->where('id', $stage_id)->get('db_print_stages')->row();
        if (!$stage || $stage->status === 'done') return false;
        $update = ['status' => 'done', 'completed_at' => date('Y-m-d H:i:s')];
        if ($outsource_vendor !== null || $outsource_cost !== null) {
            $update['status'] = 'outsourced';
            $update['outsourced'] = 1;
            $update['outsource_vendor'] = $outsource_vendor;
        }
        $this->db->where('id', $stage_id)->update('db_print_stages', $update);
        // Record the outsource direct cost (non-material).
        if ($outsource_cost !== null && (float)$outsource_cost > 0) {
            $log = [
                'store_id' => get_current_store_id(),
                'job_id' => $stage->job_id,
                'stage_id' => $stage_id,
                'work_date' => date('Y-m-d'),
                'outsource_cost' => (float)$outsource_cost,
                'notes' => 'Outsourced to ' . ($outsource_vendor ?: 'vendor'),
                'status' => 'approved',
                'submitted_by' => $this->session->userdata('inv_username') ?: 'System',
                'approved_by' => $this->session->userdata('inv_username') ?: 'System',
                'approved_at' => date('Y-m-d H:i:s'),
            ];
            $this->db->insert('db_print_stage_logs', $log);
        }
        $this->_recalc_costs($stage->job_id);
        return true;
    }

    /** Complete a job: all non-skipped stages done, QC approved. */
    public function complete_job($job_id) {
        $job = $this->get_job($job_id);
        if (!$job || $job->production_status === 'completed') return ['success' => false, 'message' => 'Job not found or already completed.'];
        foreach ($this->get_stages($job_id) as $s) {
            if (!in_array($s->status, ['done', 'skipped', 'outsourced'])) {
                return ['success' => false, 'message' => 'Stage "' . $s->stage_label . '" not finished.'];
            }
        }
        $pending_qc = $this->db->select('l.id')->from('db_print_stage_logs l')
            ->join('db_print_stages s', 's.id = l.stage_id')
            ->where('l.job_id', $job_id)->where('s.stage_key', 'qc')->where('l.status', 'submitted')
            ->count_all_results();
        if ($pending_qc > 0) return ['success' => false, 'message' => $pending_qc . ' QC report(s) still awaiting approval.'];

        $this->db->where('id', $job_id)->update('db_print_jobs', [
            'production_status' => 'completed', 'cost_complete' => 1,
        ]);
        if ($job->custom_order_id) {
            $this->load->model('custom_orders_model', 'custom_orders');
            $order = $this->custom_orders->get($job->custom_order_id);
            if ($order && !in_array($order->status, ['delivered', 'cancelled'])) {
                $this->custom_orders->save(['status' => 'ready'], $job->custom_order_id);
            }
        }
        $this->_recalc_costs($job_id);
        return ['success' => true, 'message' => 'Job completed.'];
    }

    /* ============================ costing ================================== */

    private function _recalc_costs($job_id) {
        $row = $this->db->select("
            COALESCE(SUM(CASE WHEN status != 'reversed' THEN input_cost ELSE 0 END),0) AS act_material,
            COALESCE(SUM(CASE WHEN status != 'reversed' THEN labour_cost ELSE 0 END),0) AS act_labour,
            COALESCE(SUM(CASE WHEN status != 'reversed' THEN outsource_cost ELSE 0 END),0) AS act_outsource
        ")->where('job_id', $job_id)->get('db_print_stage_logs')->row();
        $this->db->where('id', $job_id)->update('db_print_jobs', [
            'act_material_cost' => round((float)$row->act_material, 2),
            'act_labour_cost' => round((float)$row->act_labour, 2),
            'act_outsource_cost' => round((float)$row->act_outsource, 2),
        ]);
    }

    /** Per-job cost/estimate summary + margin against quote. */
    public function job_report($job_id) {
        $job = $this->get_job($job_id);
        if (!$job) return null;
        $actual = (float)$job->act_material_cost + (float)$job->act_labour_cost + (float)$job->act_outsource_cost;
        $estimated = (float)$job->est_material_cost + (float)$job->est_labour_cost + (float)$job->est_outsource_cost;
        $revenue = (float)$job->quote_amount;
        return [
            'job' => $job,
            'estimated_total' => round($estimated, 2),
            'actual_total' => round($actual, 2),
            'revenue' => $revenue,
            'gross_profit' => round($revenue - $actual, 2),
            'margin_pct' => $revenue > 0 ? round(($revenue - $actual) / $revenue * 100, 1) : null,
            'cost_complete' => (int)$job->cost_complete,
        ];
    }

    /* ============================ fulfilment =============================== */

    /**
     * Record partial/full collection or delivery with recipient evidence.
     * Preserves remaining fulfilment quantity. Debits balance on collection.
     */
    public function record_fulfilment($job_id, $qty, $kind, $recipient_name = null, $evidence_note = null) {
        $job = $this->get_job($job_id);
        if (!$job) return ['success' => false, 'message' => 'Job not found.'];
        if ($job->production_status !== 'completed') return ['success' => false, 'message' => 'Job must be completed before fulfilment.'];
        $qty = (float)$qty;
        if ($qty <= 0) return ['success' => false, 'message' => 'Quantity must be positive.'];

        $this->db->insert('db_print_fulfilments', [
            'store_id' => get_current_store_id(),
            'job_id' => $job_id,
            'qty' => $qty,
            'kind' => $kind,
            'recipient_name' => $recipient_name,
            'evidence_note' => $evidence_note,
            'fulfilled_by' => $this->session->userdata('inv_username') ?: 'System',
            'fulfilled_at' => date('Y-m-d H:i:s'),
        ]);

        $fulfilled = (float)$this->db->select('COALESCE(SUM(qty),0) s')->where('job_id', $job_id)->get('db_print_fulfilments')->row()->s;
        $planned = (float)$job->planned_qty;
        $status = 'pending';
        if ($planned > 0) {
            if ($fulfilled >= $planned) $status = 'collected';
            elseif ($fulfilled > 0) $status = 'partially_collected';
        }
        $this->db->where('id', $job_id)->update('db_print_jobs', ['fulfilment_status' => $status]);
        return ['success' => true, 'message' => 'Fulfilment recorded.', 'remaining' => max(0, $planned - $fulfilled)];
    }

    /** Outstanding balance release policy: remaining due after fulfilment. */
    public function outstanding_balance($job_id) {
        $job = $this->get_job($job_id);
        if (!$job) return null;
        $net = $this->net_verified_payments($job_id);
        return max(0, (float)$job->quote_amount - $net);
    }

    /* ============================ referrals ================================ */

    /**
     * Job-level referral attribution. Rule snapshot captured at attribution so
     * later policy changes cannot rewrite history. $rate = percentage (e.g. 5)
     * or ['value'=>fixed, 'type'=>'fixed']. $exclude_tax/$exclude_pass_through
     * carve those out of the eligible value.
     */
    public function attribute_referral($job_id, $partner_id, $rate, $source_ref = '', $exclude = []) {
        $job = $this->get_job($job_id);
        if (!$job) return ['success' => false, 'message' => 'Job not found.'];
        $exists = $this->db->where('job_id', $job_id)->get('db_print_referrals')->row();
        if ($exists) return ['success' => false, 'message' => 'Referral already attributed.'];

        $eligible = (float)$job->quote_amount;
        if (!empty($exclude['tax'])) $eligible -= (float)$exclude['tax'];
        if (!empty($exclude['pass_through'])) $eligible -= (float)$exclude['pass_through'];

        $rule = ['rate_type' => 'percent', 'rate' => (float)$rate];
        $projected = $eligible > 0 ? round($eligible * ((float)$rate / 100), 2) : 0;

        $this->db->insert('db_print_referrals', [
            'store_id' => get_current_store_id(),
            'job_id' => $job_id,
            'partner_id' => $partner_id,
            'source_ref' => $source_ref,
            'rule_json' => json_encode(array_merge($rule, ['exclude' => $exclude])),
            'eligible_value' => round($eligible, 2),
            'projected_amount' => $projected,
            'status' => 'attributed',
        ]);
        $this->db->where('id', $job_id)->update('db_print_jobs', [
            'referral_status' => 'attributed',
            'referral_eligible_value' => round($eligible, 2),
            'referral_amount' => $projected,
        ]);
        return ['success' => true, 'message' => 'Referral attributed.', 'projected' => $projected];
    }

    /** Earn only after FULL collection + fulfilment. Returns new status. */
    public function evaluate_referral($job_id) {
        $ref = $this->db->where('job_id', $job_id)->get('db_print_referrals')->row();
        if (!$ref) return ['success' => false, 'message' => 'No referral.'];
        $job = $this->get_job($job_id);
        $fully_paid = $this->net_verified_payments($job_id) >= (float)$job->quote_amount;
        $fully_fulfilled = $job->fulfilment_status === 'collected';
        if (!$fully_paid || !$fully_fulfilled) {
            return ['success' => true, 'message' => 'Not yet earned.', 'status' => $ref->status];
        }
        $this->db->where('id', $ref->id)->update('db_print_referrals', [
            'status' => 'earned', 'earned_amount' => $ref->projected_amount, 'payable_amount' => $ref->projected_amount,
        ]);
        $this->db->where('id', $job_id)->update('db_print_jobs', ['referral_status' => 'earned']);
        return ['success' => true, 'message' => 'Referral earned.', 'status' => 'earned'];
    }

    /** Approve payout once — payout-once guard. */
    public function pay_referral($referral_id) {
        $ref = $this->db->where('id', $referral_id)->get('db_print_referrals')->row();
        if (!$ref) return ['success' => false, 'message' => 'Referral not found.'];
        if (!in_array($ref->status, ['earned', 'payable'])) return ['success' => false, 'message' => 'Referral not payable.'];
        if ((float)$ref->paid_amount > 0) return ['success' => false, 'message' => 'Already paid (payout-once).'];

        $this->db->trans_begin();
        try {
            $this->db->insert('db_print_referral_entries', [
                'store_id' => $ref->store_id,
                'referral_id' => $ref->id,
                'kind' => 'payout',
                'amount' => (float)$ref->payable_amount,
                'created_by' => $this->session->userdata('inv_username') ?: 'System',
            ]);
            $this->db->where('id', $ref->id)->update('db_print_referrals', [
                'status' => 'paid', 'paid_amount' => (float)$ref->payable_amount,
                'payout_ref' => 'PAY-' . date('Ymd') . '-' . $ref->id,
                'paid_at' => date('Y-m-d H:i:s'), 'paid_by' => $this->session->userdata('inv_username') ?: 'System',
            ]);
            $this->db->where('id', $ref->job_id)->update('db_print_jobs', ['referral_status' => 'paid']);
            $this->db->trans_commit();
            return ['success' => true, 'message' => 'Referral paid.'];
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Payout failed.'];
        }
    }

    /** Refund after payout -> traceable recovery (negative) adjustment. */
    public function recover_referral($referral_id, $reason = '') {
        $ref = $this->db->where('id', $referral_id)->get('db_print_referrals')->row();
        if (!$ref) return ['success' => false, 'message' => 'Referral not found.'];
        if ((float)$ref->paid_amount <= 0) return ['success' => false, 'message' => 'Nothing paid to recover.'];
        $this->db->insert('db_print_referral_entries', [
            'store_id' => $ref->store_id,
            'referral_id' => $ref->id,
            'kind' => 'recovery',
            'amount' => -((float)$ref->paid_amount),
            'note' => $reason,
            'created_by' => $this->session->userdata('inv_username') ?: 'System',
        ]);
        $this->db->where('id', $ref->id)->update('db_print_referrals', ['status' => 'recovered']);
        $this->db->where('id', $ref->job_id)->update('db_print_jobs', ['referral_status' => 'recovered']);
        return ['success' => true, 'message' => 'Referral recovered.'];
    }

    /* ============================ dashboard KPIs =========================== */

    /**
     * Print-shop dashboard funnel. Counts jobs at each lifecycle gate so the
     * workspace surfaces exactly what is waiting on whom:
     *   intake → quoted → awaiting_deposit → awaiting_artwork →
     *   awaiting_design/clearance → awaiting_authorization → in_production →
     *   awaiting_qc → awaiting_collection → completed/cancelled.
     */
    public function dashboard_kpis($store_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $jobs = $this->get_jobs($store_id);

        $k = [
            'total' => 0,
            'open' => 0,
            'awaiting_quote' => 0,
            'awaiting_deposit' => 0,
            'awaiting_artwork' => 0,
            'awaiting_design' => 0,
            'awaiting_authorization' => 0,
            'in_production' => 0,
            'awaiting_fulfilment' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'due_soon' => 0,
            'overdue' => 0,
            'outstanding_balance' => 0.0,
            'collections_due' => 0.0,
            'referrals_earned' => 0.0,
        ];

        foreach ($jobs as $j) {
            $k['total']++;
            if ($j->production_status === 'cancelled') { $k['cancelled']++; continue; }
            if ($j->production_status === 'completed') {
                $k['completed']++;
                if ($j->fulfilment_status !== 'collected') $k['awaiting_fulfilment']++;
            } else {
                $k['open']++;
            }

            // Lifecycle gates on still-open jobs
            if ($j->quotation_status === 'none' || $j->quotation_status === 'draft') $k['awaiting_quote']++;
            else {
                if ($j->deposit_amount > 0 && $j->payment_status !== 'paid' && $j->payment_status !== 'verified' && !$this->deposit_gate_met($j->id)) $k['awaiting_deposit']++;
            }
            if ($j->artwork_status !== 'approved' && $j->production_status !== 'completed') $k['awaiting_artwork']++;
            if ($j->artwork_status === 'approved' && $j->design_status === 'pending') $k['awaiting_design']++;
            if ($j->design_status === 'cleared' && $j->authorization_status === 'requested') $k['awaiting_authorization']++;
            if ($j->production_status === 'in_progress') $k['in_production']++;

            // Due / overdue
            if (!empty($j->due_date) && !in_array($j->production_status, ['completed', 'cancelled'])) {
                $due = strtotime($j->due_date);
                $today = strtotime(date('Y-m-d'));
                if ($due < $today) $k['overdue']++;
                elseif ($due <= strtotime('+7 days')) $k['due_soon']++;
            }

            // Balance / collections
            $bal = max(0, (float)$j->quote_amount - $this->net_verified_payments($j->id));
            if ($bal > 0 && $j->production_status !== 'cancelled') {
                $k['outstanding_balance'] += $bal;
                if ($j->production_status === 'completed') $k['collections_due'] += $bal;
            }

            // Referrals earned (payable) value
            $ref = $this->db->where('job_id', $j->id)->get('db_print_referrals')->row();
            if ($ref && in_array($ref->status, ['earned', 'payable'])) {
                $k['referrals_earned'] += (float)$ref->payable_amount;
            }
        }

        $k['outstanding_balance'] = round($k['outstanding_balance'], 2);
        $k['collections_due'] = round($k['collections_due'], 2);
        $k['referrals_earned'] = round($k['referrals_earned'], 2);
        return $k;
    }

    /* ============================ date-aware reporting ===================== */

    /** Range info mirroring Dashboard_model::get_range_info (shared vocabulary). */
    public static function range_info($range) {
        $today = date('Y-m-d');
        switch ($range) {
            case '7Days':     return ['from' => date('Y-m-d', strtotime('-7 days')),  'label' => '7 Days'];
            case '30Days':    return ['from' => date('Y-m-d', strtotime('-30 days')), 'label' => '30 Days'];
            case 'ThisMonth': return ['from' => date('Y-m-01'), 'label' => 'This Month'];
            case 'ThisYear':  return ['from' => date('Y-01-01'), 'label' => 'This Year'];
            default:          return ['from' => $today, 'label' => 'Today'];
        }
        return ['from' => $today, 'label' => 'Today'];
    }

    /**
     * Date-aware print-shop summary. $range = Today|7Days|30Days|ThisMonth|ThisYear.
     * Counts jobs created/completed in range and money (collections, refunds,
     * referrals) received in range.
     */
    public function daily_report($store_id = null, $range = 'Today') {
        if (empty($store_id)) $store_id = get_current_store_id();
        $info = self::range_info($range);
        $from = $info['from'];
        $today = date('Y-m-d H:i:s');
        $from_dt = $from . ' 00:00:00';

        $jobs_created = (int)$this->db->where('store_id', $store_id)
            ->where('created_at >=', $from_dt)->where('created_at <=', $today)
            ->count_all_results('db_print_jobs');

        $jobs_completed = (int)$this->db->where('store_id', $store_id)
            ->where('production_status', 'completed')
            ->where('updated_at >=', $from_dt)->where('updated_at <=', $today)
            ->count_all_results('db_print_jobs');

        $collected = (float)$this->db->select('COALESCE(SUM(amount),0) s')
            ->where('store_id', $store_id)->where('status', 'verified')
            ->where_in('payment_kind', ['deposit', 'collection'])
            ->where('created_at >=', $from_dt)->where('created_at <=', $today)
            ->get('db_print_payments')->row()->s;

        $refunded = (float)$this->db->select('COALESCE(SUM(amount),0) s')
            ->where('store_id', $store_id)->where('status', 'verified')
            ->where('payment_kind', 'refund')
            ->where('created_at >=', $from_dt)->where('created_at <=', $today)
            ->get('db_print_payments')->row()->s;

        $referrals_paid = (float)$this->db->select('COALESCE(SUM(amount),0) s')
            ->where('store_id', $store_id)->where('kind', 'payout')
            ->where('created_at >=', $from_dt)->where('created_at <=', $today)
            ->get('db_print_referral_entries')->row()->s;

        $base = $this->dashboard_kpis($store_id);

        // Split verified receipts by method: cash vs bank/e-transfer/wallet.
        $cash = (float)$this->db->select('COALESCE(SUM(amount),0) s')
            ->where('store_id', $store_id)->where('status', 'verified')
            ->where_in('payment_kind', ['deposit', 'collection'])
            ->where('LOWER(method) LIKE', '%cash%')
            ->where('created_at >=', $from_dt)->where('created_at <=', $today)
            ->get('db_print_payments')->row()->s;

        return [
            'label' => $info['label'],
            'from' => $from,
            'jobs_created' => $jobs_created,
            'jobs_completed' => $jobs_completed,
            'collected' => round($collected, 2),
            'cash' => round($cash, 2),
            'bank' => round($collected - $cash, 2),
            'refunded' => round($refunded, 2),
            'net_collected' => round($collected - $refunded, 2),
            'referrals_paid' => round($referrals_paid, 2),
            'open_jobs' => $base['open'],
            'outstanding_balance' => $base['outstanding_balance'],
        ];
    }

    /**
     * Month-over-category breakdown for the reports screen.
     * Returns rows grouped by category + month (jobs, value, actual cost).
     */
    public function category_month_report($store_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $sql = "SELECT
                    DATE_FORMAT(j.created_at, '%Y-%m') AS ym,
                    COALESCE(l.category_key, 'uncategorised') AS category_key,
                    COUNT(DISTINCT j.id) AS jobs,
                    COALESCE(SUM(l.line_total), 0) AS value,
                    COALESCE(SUM(j.act_material_cost + j.act_labour_cost + j.act_outsource_cost), 0) AS cost
                FROM db_print_jobs j
                LEFT JOIN db_print_job_lines l ON l.job_id = j.id
                WHERE j.store_id = ?
                GROUP BY DATE_FORMAT(j.created_at, '%Y-%m'), COALESCE(l.category_key, 'uncategorised')
                ORDER BY ym DESC, value DESC";
        return $this->db->query($sql, [$store_id])->result();
    }

    /* ============================ dashboard widgets ======================= */

    /** Recent print jobs for the dashboard (range-aware on created_at). */
    public function recent_jobs($store_id = null, $range = 'Today', $limit = 6) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $info = self::range_info($range);
        $this->db->select('j.*, c.customer_name')
            ->from('db_print_jobs j')
            ->join('db_customers c', 'c.id = j.customer_id', 'left')
            ->where('j.store_id', $store_id);
        if ($range !== 'ThisYear') {
            $this->db->where('j.created_at >=', $info['from'] . ' 00:00:00');
        }
        return $this->db->order_by('j.id', 'desc')->limit($limit)->get()->result();
    }

    /** Recent activity feed derived from print jobs (created / completed). */
    public function recent_activity($store_id = null, $limit = 6) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $jobs = $this->db->select('j.id, j.job_code, j.title, j.quote_amount, j.created_at, j.updated_at, j.production_status, c.customer_name')
            ->from('db_print_jobs j')->join('db_customers c', 'c.id = j.customer_id', 'left')
            ->where('j.store_id', $store_id)->order_by('j.id', 'desc')->limit($limit)->get()->result();
        $out = [];
        foreach ($jobs as $j) {
            $out[] = [
                'type' => $j->production_status === 'completed' ? 'sale' : 'stock',
                'title' => $j->job_code . ' · ' . ($j->customer_name ?: 'Client'),
                'date' => $j->production_status === 'completed' ? ($j->updated_at ?: $j->created_at) : $j->created_at,
                'amount' => 0,
            ];
        }
        return $out;
    }

    /** Top print categories by value in range. */
    public function top_categories($store_id = null, $range = 'Today', $limit = 5) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $info = self::range_info($range);
        $sql = "SELECT COALESCE(l.category_key,'uncategorised') AS name,
                       COALESCE(SUM(l.qty),0) AS qty,
                       COALESCE(SUM(l.line_total),0) AS revenue
                FROM db_print_job_lines l
                JOIN db_print_jobs j ON j.id = l.job_id
                WHERE j.store_id = ?" . ($range !== 'ThisYear' ? " AND j.created_at >= ?" : "") . "
                GROUP BY COALESCE(l.category_key,'uncategorised')
                ORDER BY revenue DESC LIMIT " . (int)$limit;
        $params = ($range !== 'ThisYear') ? [$store_id, $info['from'] . ' 00:00:00'] : [$store_id];
        $rows = $this->db->query($sql, $params)->result();
        foreach ($rows as $r) { $r->name = ucwords(str_replace('_', ' ', $r->name)); }
        return $rows;
    }

    /** Top debtors across print jobs (unpaid balances). */
    public function top_debtors($store_id = null, $limit = 5) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $jobs = $this->db->select('j.id, j.quote_amount, c.customer_name')
            ->from('db_print_jobs j')->join('db_customers c', 'c.id = j.customer_id', 'left')
            ->where('j.store_id', $store_id)->where('j.production_status !=', 'cancelled')
            ->get()->result();
        $by_customer = [];
        foreach ($jobs as $j) {
            $bal = max(0, (float)$j->quote_amount - $this->net_verified_payments($j->id));
            if ($bal > 0) {
                $name = $j->customer_name ?: 'Walk-in';
                if (!isset($by_customer[$name])) $by_customer[$name] = 0;
                $by_customer[$name] += $bal;
            }
        }
        arsort($by_customer);
        $out = [];
        $i = 0;
        foreach ($by_customer as $name => $amt) {
            $out[] = ['name' => $name, 'amount' => $amt];
            if (++$i >= $limit) break;
        }
        return $out;
    }

    /**
     * Production analytics — the numbers a print shop is actually run on.
     *
     * Revenue and customer tables answer "how much did we bill"; these answer
     * "did we quote well, where is the work stuck, how much did we spoil, and
     * did the estimate hold". Those are the questions a print business has to
     * act on, and none of them are visible in a generic sales report.
     *
     * @param int   $store_id
     * @param array $range ['from' => 'Y-m-d', 'to' => 'Y-m-d']
     */
    public function production_analytics($store_id, array $range = []) {
        $from = !empty($range['from']) ? $range['from'] . ' 00:00:00' : null;
        $to   = !empty($range['to'])   ? $range['to'] . ' 23:59:59'   : null;

        return [
            'quote_funnel'   => $this->quote_funnel($store_id, $from, $to),
            'stage_bottleneck' => $this->stage_bottleneck($store_id, $from, $to),
            'quality'        => $this->print_quality($store_id, $from, $to),
            'cost_accuracy'  => $this->cost_accuracy($store_id, $from, $to),
            'turnaround'     => $this->turnaround($store_id, $from, $to),
        ];
    }

    /** Quoted → accepted/declined/expired, with the conversion rate. */
    public function quote_funnel($store_id, $from = null, $to = null) {
        $this->db->select("
            SUM(CASE WHEN quotation_status IN ('accepted','converted') THEN 1 ELSE 0 END) AS accepted,
            SUM(CASE WHEN quotation_status = 'declined' THEN 1 ELSE 0 END) AS declined,
            SUM(CASE WHEN quotation_status = 'expired'  THEN 1 ELSE 0 END) AS expired,
            SUM(CASE WHEN quotation_status IN ('issued','sent') THEN 1 ELSE 0 END) AS awaiting,
            SUM(CASE WHEN quotation_status = 'none' THEN 1 ELSE 0 END) AS not_quoted,
            COUNT(*) AS total
        ", false)->where('store_id', $store_id);
        $this->apply_created_range($from, $to);
        $r = $this->db->get('db_print_jobs')->row();

        $quoted = (int)$r->accepted + (int)$r->declined + (int)$r->expired + (int)$r->awaiting;
        return [
            'accepted'   => (int)$r->accepted,
            'declined'   => (int)$r->declined,
            'expired'    => (int)$r->expired,
            'awaiting'   => (int)$r->awaiting,
            'not_quoted' => (int)$r->not_quoted,
            'total'      => (int)$r->total,
            'quoted'     => $quoted,
            'won_rate'   => $quoted > 0 ? round((int)$r->accepted / $quoted * 100, 1) : null,
        ];
    }

    /**
     * Where the work is sitting, and how long each stage takes.
     * Slowest stage first — that is the bottleneck.
     *
     * db_print_stages has no created_at of its own, so the period filter is
     * applied to the parent job's created_at via a join.
     */
    public function stage_bottleneck($store_id, $from = null, $to = null) {
        $this->db->select("
            s.stage_key,
            COUNT(*) AS jobs,
            SUM(CASE WHEN s.status IN ('pending','in_progress') THEN 1 ELSE 0 END) AS open_jobs,
            SUM(CASE WHEN s.status = 'completed' THEN 1 ELSE 0 END) AS done_jobs,
            AVG(CASE WHEN s.completed_at IS NOT NULL AND s.started_at IS NOT NULL
                     THEN TIMESTAMPDIFF(HOUR, s.started_at, s.completed_at) END) AS avg_hours
        ", false)
            ->from('db_print_stages s')
            ->join('db_print_jobs j', 'j.id = s.job_id', 'inner')
            ->where('s.store_id', $store_id);
        if ($from) $this->db->where('j.created_at >=', $from);
        if ($to)   $this->db->where('j.created_at <=', $to);
        $rows = $this->db->group_by('s.stage_key')->get()->result();

        foreach ($rows as $r) {
            $r->avg_hours = $r->avg_hours !== null ? round((float)$r->avg_hours, 1) : null;
        }
        usort($rows, function ($a, $b) {
            return ($b->avg_hours ?? -1) <=> ($a->avg_hours ?? -1);
        });
        return $rows;
    }

    /**
     * Spoilage. Waste and rework percentages are the single most-watched
     * production number in a print shop — they are pure margin.
     */
    public function print_quality($store_id, $from = null, $to = null) {
        $this->db->select("
            COALESCE(SUM(qty_in),0) AS qty_in,
            COALESCE(SUM(good_qty),0) AS good_qty,
            COALESCE(SUM(rework_qty),0) AS rework_qty,
            COALESCE(SUM(reject_qty),0) AS reject_qty,
            COALESCE(SUM(waste_qty),0) AS waste_qty,
            COALESCE(SUM(scrap_qty),0) AS scrap_qty
        ", false)->where('store_id', $store_id);
        if ($from) $this->db->where('created_at >=', $from);
        if ($to)   $this->db->where('created_at <=', $to);
        $r = $this->db->get('db_print_stage_logs')->row();

        $in     = (float)$r->qty_in;
        $rework = (float)$r->rework_qty;
        $reject = (float)$r->reject_qty;
        $waste  = (float)$r->waste_qty;
        $scrap  = (float)$r->scrap_qty;
        $loss   = $rework + $reject + $waste + $scrap;

        return [
            'qty_in'         => $in,
            'good_qty'       => (float)$r->good_qty,
            'rework_qty'     => $rework,
            'reject_qty'     => $reject,
            'waste_qty'      => $waste,
            'scrap_qty'      => $scrap,
            'loss_qty'       => $loss,
            'rework_pct'     => $in > 0 ? round($rework / $in * 100, 2) : null,
            'waste_pct'      => $in > 0 ? round(($reject + $waste + $scrap) / $in * 100, 2) : null,
            'yield_pct'      => $in > 0 ? round((float)$r->good_qty / $in * 100, 2) : null,
        ];
    }

    /**
     * Did the estimate hold? A print job quotes off a spec, then real material,
     * labour and outsource costs land. The gap between the two is the shop's
     * costing accuracy, and it is invisible on a revenue report.
     */
    public function cost_accuracy($store_id, $from = null, $to = null) {
        $this->db->select("
            COUNT(*) AS jobs,
            COALESCE(SUM(est_material_cost + est_labour_cost + est_outsource_cost),0) AS estimated,
            COALESCE(SUM(act_material_cost + act_labour_cost + act_outsource_cost),0) AS actual,
            SUM(CASE WHEN cost_complete = 1 THEN 1 ELSE 0 END) AS costed_jobs,
            COALESCE(SUM(quote_amount),0) AS quoted_value
        ", false)->where('store_id', $store_id);
        $this->apply_created_range($from, $to);
        $r = $this->db->get('db_print_jobs')->row();

        $est = (float)$r->estimated;
        $act = (float)$r->actual;
        return [
            'jobs'         => (int)$r->jobs,
            'costed_jobs'  => (int)$r->costed_jobs,
            'estimated'    => $est,
            'actual'       => $act,
            'variance'     => round($act - $est, 2),
            'variance_pct' => $est > 0 ? round(($act - $est) / $est * 100, 1) : null,
            'quoted_value' => (float)$r->quoted_value,
            // Only meaningful once costs are in: how much of the quote survives.
            'margin_pct'   => (float)$r->quoted_value > 0 && $act > 0
                                ? round(((float)$r->quoted_value - $act) / (float)$r->quoted_value * 100, 1)
                                : null,
        ];
    }

    /**
     * On-time delivery: did completed jobs meet their promised date?
     * Measured against the last fulfilment, not the quote date.
     */
    public function turnaround($store_id, $from = null, $to = null) {
        $this->db->select('j.id, j.due_date, j.created_at, MAX(f.fulfilled_at) AS completed_on')
            ->from('db_print_jobs j')
            ->join('db_print_fulfilments f', 'f.job_id = j.id', 'left')
            ->where('j.store_id', $store_id)
            ->where('j.production_status', 'completed');
        $this->apply_created_range($from, $to, 'j.created_at');
        $rows = $this->db->group_by('j.id')->get()->result();

        $on_time = 0; $late = 0; $total_days = 0; $measured = 0;
        foreach ($rows as $r) {
            if (empty($r->completed_on)) continue;
            $measured++;
            $days = (strtotime($r->completed_on) - strtotime($r->created_at)) / 86400;
            $total_days += $days;
            if (empty($r->due_date) || strtotime($r->completed_on) <= strtotime($r->due_date . ' 23:59:59')) {
                $on_time++;
            } else {
                $late++;
            }
        }
        return [
            'measured'      => $measured,
            'on_time'       => $on_time,
            'late'          => $late,
            'on_time_pct'   => $measured > 0 ? round($on_time / $measured * 100, 1) : null,
            'avg_days'      => $measured > 0 ? round($total_days / $measured, 1) : null,
        ];
    }

    /** Shared created_at range filter for the analytics queries. */
    private function apply_created_range($from, $to, $column = 'created_at') {
        if ($from) $this->db->where($column . ' >=', $from);
        if ($to)   $this->db->where($column . ' <=', $to);
    }

    /**
     * Kanban board: open jobs bucketed into lifecycle columns.
     * Each card carries the job + its first line (dimensions/material) + gate state.
     */
    public function kanban_board($store_id = null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $columns = [
            'quote'      => ['label' => 'Awaiting Quote',    'jobs' => []],
            'deposit'    => ['label' => 'Awaiting Deposit',  'jobs' => []],
            'artwork'    => ['label' => 'Artwork',           'jobs' => []],
            'approval'   => ['label' => 'Print Approval',    'jobs' => []],
            'production' => ['label' => 'In Production',     'jobs' => []],
            'collection' => ['label' => 'Collection',        'jobs' => []],
        ];

        $jobs = $this->db->select('j.*, c.customer_name')
            ->from('db_print_jobs j')->join('db_customers c', 'c.id = j.customer_id', 'left')
            ->where('j.store_id', $store_id)
            ->where_not_in('j.production_status', ['cancelled'])
            ->order_by('j.due_date IS NULL', 'asc', false)->order_by('j.due_date', 'asc')
            ->get()->result();

        foreach ($jobs as $j) {
            // Decide the column by the earliest unmet gate.
            if ($j->production_status === 'completed') {
                $col = $j->fulfilment_status === 'collected' ? null : 'collection';
            } elseif (!in_array($j->quotation_status, ['accepted', 'converted'])) {
                $col = 'quote';
            } elseif ($j->deposit_amount > 0 && !$this->deposit_gate_met($j->id)) {
                $col = 'deposit';
            } elseif ($j->artwork_status !== 'approved') {
                $col = 'artwork';
            } elseif (!$this->authorization_valid($j->id)) {
                $col = 'approval';
            } else {
                $col = 'production';
            }
            if ($col === null || !isset($columns[$col])) continue;

            $line = $this->db->where('job_id', $j->id)->order_by('id', 'asc')->limit(1)->get('db_print_job_lines')->row();
            $spec = $line && $line->spec_json ? json_decode($line->spec_json, true) : [];
            $j->category_key = $line->category_key ?? null;
            $j->description = $line->description ?? null;
            $j->dimension = '';
            if (!empty($spec['width']) && !empty($spec['height'])) {
                $j->dimension = $spec['width'] . ' × ' . $spec['height'] . ' ' . ($spec['dim_unit'] ?? '');
            }
            $j->material = $spec['material'] ?? ($spec['paper_gsm'] ?? '');
            $j->balance = max(0, (float)$j->quote_amount - $this->net_verified_payments($j->id));
            $columns[$col]['jobs'][] = $j;
        }
        return $columns;
    }

    /**
     * Low stock materials — raw materials and film/paper consumables used in
     * print production. Reuses db_items.stock against db_nylon_item_specs only
     * for nylon; for printing we look at items flagged as materials via the
     * stock engine's low-stock rule (stock <= alert_qty).
     */
    public function low_stock_materials($store_id = null, $limit = 5) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $this->db->select('i.id, i.item_name AS name, i.stock, i.alert_qty, u.unit_name')
            ->from('db_items i')->join('db_units u', 'u.id = i.unit_id', 'left')
            ->where('i.store_id', $store_id)->where('i.status', 1)
            ->where('i.alert_qty >', 0)
            ->where('i.stock <= i.alert_qty')
            ->order_by('i.stock', 'asc')->limit($limit);
        $rows = $this->db->get()->result();
        return $rows;
    }

    /** Collections trend for the chart (last N days). */
    public function collections_trend($store_id = null, $days = 7) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $amt = (float)$this->db->select('COALESCE(SUM(amount),0) s')
                ->where('store_id', $store_id)->where('status', 'verified')
                ->where_in('payment_kind', ['deposit', 'collection'])
                ->where('DATE(created_at)', $d)->get('db_print_payments')->row()->s;
            $out[] = ['date' => $d, 'label' => date('d M', strtotime($d)), 'amount' => $amt];
        }
        return $out;
    }

    /* ============================ print invoice =========================== */

    /**
     * Turn an accepted quotation into a real sales invoice, and mark the
     * quotation Converted — the same end state the shared retail path produces.
     *
     * WHY THIS IS NOT JUST `redirect('sales/quotation/…')`
     *
     * The shared screen builds an invoice from post data the operator types.
     * For a print job the figures already exist and were already accepted, so
     * re-entering them is not only wasted work — it is a chance to bill a
     * different amount than the client agreed to. This reads the quotation.
     *
     * WHAT IT WRITES
     *
     *   db_sales        one row, quotation_id set, totals from the quotation
     *   db_salesitems   one row per quotation item (mapped 1:1, so the invoice
     *                   detail always reconciles to the quote)
     *   db_quotation    sales_status = 'Converted', so it cannot be converted twice
     *
     * All three are written in ONE transaction. A half-written conversion is
     * the worst outcome available here: an invoice with no lines, or a quote
     * marked Converted with no invoice behind it, would both be very hard to
     * unpick by hand.
     *
     * The line PRESENTATION (combined / detailed) is deliberately not part of
     * the write. Both styles group the same rows, so the stored invoice is
     * identical either way and the switch stays freely reversible.
     *
     * @return array {success, message, sales_id?}
     */
    public function create_invoice_from_quotation($job_id) {
        $store_id = (int) get_current_store_id();
        $job = $this->get_job((int) $job_id);
        if (!$job) return ['success' => false, 'message' => 'Print job not found.'];
        if ((int) $job->store_id !== $store_id) return ['success' => false, 'message' => 'That job is not on this store.'];

        $q = $this->quotation_for_job((int) $job_id);
        if (!$q) return ['success' => false, 'message' => 'This job has no quotation to bill.'];

        // Already billed? Return the existing invoice rather than making a second.
        $existing = $this->db->select('id')->where('quotation_id', (int) $q->id)
            ->where('store_id', $store_id)->get('db_sales')->row();
        if ($existing) {
            return ['success' => true, 'sales_id' => (int) $existing->id,
                    'message' => 'This job already has an invoice.', 'already' => true];
        }

        if ($q->sales_status === 'Converted') {
            return ['success' => false, 'message' => 'That quotation is already marked as converted.'];
        }

        // The gate that makes the whole quote-first flow meaningful: a quote
        // that changed after acceptance must be re-accepted before it is billed.
        if ($this->quotation_change_requires_reacceptance((int) $job_id)) {
            return ['success' => false, 'message' => 'The quotation changed since it was accepted. Obtain customer reacceptance before invoicing.'];
        }

        $items = $this->quotation_items((int) $q->id);
        if (empty($items)) return ['success' => false, 'message' => 'The quotation has no lines to invoice.'];

        if (!$this->db->table_exists('db_sales') || !$this->db->table_exists('db_salesitems')) {
            return ['success' => false, 'message' => 'The sales module is not available on this install.'];
        }

        $this->db->trans_begin();

        try {
            $init  = get_init_code('sales', $store_id, true);
            $count = get_count_id('db_sales', $store_id);
            $code  = $init . $count;

            $sales = [
                'store_id'      => $store_id,
                'warehouse_id'  => (int) ($job->warehouse_id ?? 0) ?: null,
                'init_code'     => $init,
                'count_id'      => $count,
                'sales_code'    => $code,
                'reference_no'  => (string) $job->job_code,
                'sales_date'    => date('Y-m-d'),
                'due_date'      => null,
                'sales_status'  => 'Final',
                'customer_id'   => (int) ($q->customer_id ?: $job->customer_id),
                'quotation_id'  => (int) $q->id,
                'subtotal'      => (float) $q->subtotal,
                'round_off'     => (float) ($q->round_off ?? 0),
                'grand_total'   => (float) $q->grand_total,
                'tot_discount_to_all_amt' => (float) ($q->tot_discount_to_all_amt ?? 0),
                'other_charges_amt'       => (float) ($q->other_charges_amt ?? 0),
                'payment_status' => 'Unpaid',
                'paid_amount'    => 0,
                'sales_note'     => trim((string) ($job->title ?? '')),
                'created_date'   => date('Y-m-d'),
                'created_time'   => date('h:i:s a'),
                'created_by'     => $this->session->userdata('inv_username') ?: 'System',
                'system_ip'      => $_SERVER['SERVER_ADDR'] ?? '',
                'system_name'    => gethostname() ?: '',
                'status'         => 1,
            ];
            // table_id is NOT NULL with a default; supply it so the insert is
            // explicit rather than relying on the column default.
            if ($this->db->field_exists('table_id', 'db_sales')) $sales['table_id'] = 0;

            $this->db->insert('db_sales', $sales);
            $sales_id = (int) $this->db->insert_id();
            if (!$sales_id) throw new Exception('db_sales insert produced no id.');

            foreach ($items as $it) {
                $this->db->insert('db_salesitems', [
                    'store_id'       => $store_id,
                    'sales_id'       => $sales_id,
                    'sales_status'   => 'Final',
                    'item_id'        => (int) ($it->item_id ?? 0) ?: null,
                    'description'    => (string) ($it->description ?? ''),
                    'sales_qty'      => (float) ($it->quotation_qty ?? 0),
                    'price_per_unit' => (float) ($it->price_per_unit ?? 0),
                    'tax_type'       => $it->tax_type ?? null,
                    'tax_id'         => $it->tax_id ?? null,
                    'tax_amt'        => (float) ($it->tax_amt ?? 0),
                    'discount_input' => (float) ($it->discount_input ?? 0),
                    'discount_amt'   => (float) ($it->discount_amt ?? 0),
                    'discount_type'  => $it->discount_type ?? null,
                    'unit_total_cost'=> (float) ($it->unit_total_cost ?? 0),
                    'total_cost'     => (float) ($it->total_cost ?? 0),
                    // No tier on a print invoice. The column is kept for
                    // historical rows but is not a choice here — see .117.
                    'price_type'     => 'retail',
                    'status'         => 1,
                ]);
            }

            $this->db->set('sales_status', 'Converted')->where('id', (int) $q->id)->update('db_quotation');

            if ($this->db->trans_status() === false) {
                throw new Exception('A write in the conversion transaction failed.');
            }
            $this->db->trans_commit();

            return ['success' => true, 'sales_id' => $sales_id,
                    'sales_code' => $code,
                    'message' => 'Invoice ' . $code . ' created from the accepted quotation.'];

        } catch (Throwable $e) {
            $this->db->trans_rollback();
            log_message('error', 'Print invoice conversion failed for job ' . (int) $job_id . ': ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not create the invoice: ' . $e->getMessage()];
        }
    }
}
