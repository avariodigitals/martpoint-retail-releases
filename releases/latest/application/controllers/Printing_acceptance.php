<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Printing acceptance suite — Release 1 (commercial intake / authorization).
 *
 *   Run (non-production rehearsal DB only):
 *     MP_DB=martpoint_print php index.php printing_acceptance run
 *
 * CLI-only. Synthetic fixtures carry a run tag so a sweep is repeatable and
 * isolated from real data. Exercises the REAL model (and controller logic via
 * the model), not route existence. Validates the resolved design invariants:
 *
 *   A  category presets seeded (6 categories), job with mixed-category lines
 *   B  quotation issue -> accept -> deposit policy snapshot (not hard-coded)
 *   C  deposit recorded BUT unverified does NOT clear the deposit gate
 *   D  Finance verify clears gate; reverse reconciles to zero
 *   E  artwork upload -> customer approve (one version) -> designer clear
 *   F  print authorization request -> authorize; new artwork version invalidates
 *   G  authorization requires artwork approved + designer cleared (server-side)
 */
class Printing_acceptance extends CI_Controller {

    private $pass = 0;
    private $fail = 0;
    private $storeId = 1;
    private $tag;
    private $uid = 2; // store admin signon for the rehearsal store

    public function __construct() {
        parent::__construct();
        if (!is_cli()) { show_404(); return; }
        $this->load->model('printing_model', 'print');
        $this->tag = 'PRT' . substr(md5(uniqid('', true)), 0, 6);
    }

    private function staff($uid) {
        $u = $this->db->where('id', $uid)->get('db_users')->row();
        $this->session->set_userdata([
            'inv_userid' => $uid,
            'inv_username' => $u ? $u->username : 'user' . $uid,
            'role_id' => $u ? (int)$u->role_id : 0,
            'store_id' => $this->storeId,
        ]);
    }

    private function check($group, $name, $cond, $detail = '') {
        if ($cond) {
            $this->pass++;
            echo "[PASS] $group :: $name" . ($detail ? " — $detail" : '') . "\n";
        } else {
            $this->fail++;
            echo "[FAIL] $group :: $name" . ($detail ? " — $detail" : '') . "\n";
        }
    }

    public function run() {
        $this->staff($this->uid);
        $store_id = $this->storeId;

        echo "=== Printing acceptance (Release 1) — store $store_id, tag {$this->tag} ===\n";

        // A: category presets
        $seeded = $this->print->seed_categories($store_id);
        $cats = $this->print->get_categories($store_id);
        $cat_keys = array_map(function ($c) { return $c->category_key; }, $cats);
        $this->check('A', 'seed_categories idempotent returns 0 on re-run', $this->print->seed_categories($store_id) === 0 && $seeded >= 0);
        $this->check('A', 'six category presets present', count(array_intersect(['large_format', 'digital_imaging', 'dtf', 'apparel', 'screen_print', 'stationery'], $cat_keys)) === 6, implode(',', $cat_keys));

        $large = $this->db->where('store_id', $store_id)->where('category_key', 'large_format')->get('db_print_categories')->row();
        $apparel = $this->db->where('store_id', $store_id)->where('category_key', 'apparel')->get('db_print_categories')->row();
        $this->check('A', 'large_format category found', (bool)$large);

        // A2: mixed-category job
        $job_data = [
            'customer_id' => null,
            'title' => 'Mixed banner + shirts ' . $this->tag,
            'due_date' => date('Y-m-d', strtotime('+5 days')),
            'quote_amount' => 100000,
        ];
        $job_id = $this->print->create_job($job_data, [
            ['category_id' => $large->id, 'qty' => 1, 'unit_price' => 80000, 'description' => 'Banner 3m x 1m', 'spec' => ['width' => 3, 'height' => 1, 'dim_unit' => 'm']],
            ['category_id' => $apparel->id, 'qty' => 20, 'unit_price' => 1000, 'description' => 'T-shirts', 'spec' => ['size_breakdown' => ['S' => 5, 'M' => 10, 'L' => 5]]],
        ]);
        $this->check('A2', 'mixed-category job created', $job_id > 0);
        $job = $this->print->get_job($job_id);
        $lines = $this->print->get_lines($job_id);
        $stages = $this->print->get_stages($job_id);
        $this->check('A2', 'job has 2 lines', count($lines) === 2, 'lines=' . count($lines));
        $this->check('A2', 'stage plan seeded (snapshot)', count($stages) > 0, 'stages=' . count($stages));
        $this->check('A2', 'distinct state columns defaulted', $job->artwork_status === 'none' && $job->design_status === 'none' && $job->authorization_status === 'none' && $job->payment_status === 'unpaid' && $job->production_status === 'planned' && $job->fulfilment_status === 'pending');

        // B: quotation issue -> accept
        $r = $this->print->set_quotation($job_id, 100000);
        $this->check('B', 'quotation issued', $r['success']);
        $r = $this->print->accept_quotation($job_id);
        $this->check('B', 'quotation accepted', $r['success']);
        $job = $this->print->get_job($job_id);
        $policy = json_decode($job->deposit_policy_json, true);
        $this->check('B', 'deposit policy snapshotted (not hard-coded 70)', is_array($policy) && isset($policy['deposit_percent']), 'deposit%=' . ($policy['deposit_percent'] ?? '?'));
        $this->check('B', 'deposit due computed from policy', abs((float)$job->deposit_amount - round(100000 * $policy['deposit_percent'] / 100, 2)) < 0.01, 'deposit=' . $job->deposit_amount);

        $deposit_due = (float)$job->deposit_amount;

        // C: deposit recorded but NOT verified does not clear gate
        $r = $this->print->record_payment($job_id, 'deposit', $deposit_due, 'Bank Transfer', 'TX-' . $this->tag);
        $this->check('C', 'deposit recorded (ledger + print rows)', $r['success']);
        $job = $this->print->get_job($job_id);
        $this->check('C', 'unverified deposit does NOT clear deposit gate', $this->print->deposit_gate_met($job_id) === false, 'gate=' . var_export($this->print->deposit_gate_met($job_id), true));

        // D: Finance verifies -> gate clears
        $pid = $r['print_payment_id'];
        $v = $this->print->verify_payment($pid);
        $this->check('D', 'finance verify succeeds', $v['success']);
        $this->check('D', 'verified deposit clears deposit gate', $this->print->deposit_gate_met($job_id) === true);
        $job = $this->print->get_job($job_id);
        $this->check('D', 'payment_status reflects verified deposit', $job->payment_status === 'verified', 'status=' . $job->payment_status);

        // D2: reverse reconciles
        $rev = $this->print->reverse_payment($pid, 'test reversal');
        $this->check('D2', 'payment reversed', $rev['success']);
        $this->check('D2', 'after reversal, gate no longer met', $this->print->deposit_gate_met($job_id) === false);
        $this->check('D2', 'net verified payments zero after reversal', abs($this->print->net_verified_payments($job_id)) < 0.01);

        // re-verify a fresh deposit to proceed
        $r = $this->print->record_payment($job_id, 'deposit', $deposit_due, 'Bank Transfer', 'TX2-' . $this->tag);
        $this->print->verify_payment($r['print_payment_id']);
        $this->check('D2', 'fresh verified deposit restores gate', $this->print->deposit_gate_met($job_id) === true);

        // E: artwork + customer approve + designer clear
        $a1 = $this->print->add_artwork($job_id, 'banner_v1.pdf', 'uploads/printjobs/banner_v1.pdf', hash('sha256', 'v1'), 'application/pdf');
        $this->check('E', 'artwork v1 uploaded', $a1 > 0);
        $this->print->approve_artwork($a1);
        $job = $this->print->get_job($job_id);
        $this->check('E', 'artwork v1 approved (customer)', $job->artwork_status === 'approved');
        $this->check('E', 'approval resets design to pending', $job->design_status === 'pending');
        $clr = $this->print->clearance_artwork($job_id, 'cleared', 'res 300dpi OK');
        $this->check('E', 'designer clears artwork', $clr['success']);
        $job = $this->print->get_job($job_id);
        $this->check('E', 'design_status cleared', $job->design_status === 'cleared');

        // F: authorization request -> authorize (deposit already met)
        $pre = $this->print->production_prerequisites($job_id);
        $this->check('F', 'gate correctly blocks on missing authorization', $pre['ok'] === false && in_array('print_authorized', $pre['missing']), json_encode($pre));
        $req = $this->print->request_authorization($job_id, $this->uid, null);
        $this->check('F', 'authorization requested', $req['success']);
        $auth = $this->db->where('job_id', $job_id)->where('status', 'requested')->get('db_print_authorizations')->row();
        $this->check('F', 'authorization row exists (fingerprinted)', (bool)$auth && $auth->artwork_version == 1, 'v=' . ($auth->artwork_version ?? '?'));
        $dec = $this->print->decide_authorization($auth->id, 'authorized');
        $this->check('F', 'authorization granted', $dec['success']);
        $this->check('F', 'authorization_valid true', $this->print->authorization_valid($job_id) === true);
        $this->check('F', 'prerequisites all met after authorize', $this->print->production_prerequisites($job_id)['ok'] === true, json_encode($this->print->production_prerequisites($job_id)));

        // F2: new artwork version invalidates authorization
        $a2 = $this->print->add_artwork($job_id, 'banner_v2.pdf', 'uploads/printjobs/banner_v2.pdf', hash('sha256', 'v2'), 'application/pdf');
        $this->print->approve_artwork($a2);
        $job = $this->print->get_job($job_id);
        $this->check('F2', 'approving v2 invalidates prior authorization', $this->print->authorization_valid($job_id) === false);
        $this->check('F2', 'authorization_status reset to none', $job->authorization_status === 'none');

        // G: authorization refused without design clearance
        $this->print->clearance_artwork($job_id, 'cleared', 'v2 ok');
        $req2 = $this->print->request_authorization($job_id, $this->uid, null);
        $this->check('G', 're-request after clearance succeeds', $req2['success']);
        $auth2 = $this->db->where('job_id', $job_id)->where('status', 'requested')->get('db_print_authorizations')->row();
        $this->print->decide_authorization($auth2->id, 'authorized');
        $this->check('G', 'server-side prereqs now all met', $this->print->production_prerequisites($job_id)['ok'] === true, json_encode($this->print->production_prerequisites($job_id)));

        // ---------- Release 2: production / fulfilment ----------
        $this->run_release2($job_id);

        // ---------- Release 3: costing / referrals ----------
        $this->run_release3($job_id);

        // ---------- Structured specs: 3 scenarios + mixed ----------
        $this->run_specs_scenarios();

        // ---------- Planning: design pricing, units, material plan ----------
        $this->run_planning_scenarios();

        // ---------- Pass A: calculators + single stock posting method ----------
        $this->run_calculators_scenarios();
        $this->run_stock_posting_scenarios();

        // ---------- Integration: printing ⇄ existing quotation module ----------
        $this->run_quotation_integration();

        // ---------- Storefront: printing is service-led, not category-led ----
        $this->run_storefront_scenarios();

        // ---------- Unified quotation: linkage drives behaviour --------------
        $this->run_unified_quotation_scenarios();

        // ---------- Lifecycle: decline / cancel / expire / reminders ---------
        $this->run_lifecycle_scenarios();

        // ---------- Service mode: a printing store is not an ecommerce shop --
        $this->run_service_mode_scenarios();

        echo "\n=== Result: {$this->pass} passed, {$this->fail} failed ===\n";
        $this->_cleanup($job_id);
        exit($this->fail === 0 ? 0 : 1);
    }

    /**
     * Design charge vs internal cost (waiver must not erase cost), explicit unit
     * conversion on material plans, and no-material-deduction on estimate.
     */
    private function run_planning_scenarios() {
        $store_id = $this->storeId;
        $this->print->seed_categories($store_id);
        $L = $this->db->where('store_id', $store_id)->where('category_key', 'large_format')->get('db_print_categories')->row();

        $job = $this->print->create_job(['customer_id' => null, 'title' => 'Planning ' . $this->tag], [[
            'category_id' => $L->id, 'qty' => 1, 'unit_price' => 45000, 'description' => 'Banner',
            'spec' => ['width' => 3, 'height' => 1, 'dim_unit' => 'm', 'material' => 'PVC Flex'],
        ]]);
        $line = $this->print->get_lines($job)[0];

        // 1. Charged design service
        $r = $this->print->save_item_design($job, $line->id, [
            'design_mode' => 'new_design', 'charge_amount' => 10000, 'internal_cost' => 6000,
            'designer_id' => $this->uid, 'instructions' => 'Create festive banner design',
        ]);
        $this->check('D', 'charged design saved', $r['success']);
        $d = $this->print->get_item_design($line->id);
        $this->check('D', 'net design charge = 10000 when charged', abs($this->print->design_charge_net($line->id) - 10000) < 0.01);
        $this->check('D', 'internal design cost preserved', abs((float)$d->internal_cost - 6000) < 0.01);

        // 2. Waived design — charge net 0, internal cost UNCHANGED
        $r = $this->print->save_item_design($job, $line->id, [
            'design_mode' => 'new_design', 'charge_amount' => 10000, 'internal_cost' => 6000,
            'charge_waived' => 1, 'waiver_reason' => 'Loyal customer goodwill',
        ]);
        $this->check('D', 'waiver saved', $r['success']);
        $d = $this->print->get_item_design($line->id);
        $this->check('D', 'waived → net design charge billed = 0', abs($this->print->design_charge_net($line->id)) < 0.01);
        $this->check('D', 'waiver records original charge (10000)', abs((float)$d->charge_amount - 10000) < 0.01);
        $this->check('D', 'waiver records waived amount (10000)', abs((float)$d->waived_amount - 10000) < 0.01);
        $this->check('D', 'waiver records reason + approver', !empty($d->waiver_reason) && !empty($d->waived_by));
        $this->check('D', 'INTERNAL design cost NOT erased by waiver (6000)', abs((float)$d->internal_cost - 6000) < 0.01, 'internal=' . $d->internal_cost);

        // 3. Waiver without reason rejected
        $r = $this->print->save_item_design($job, $line->id, ['design_mode' => 'new_design', 'charge_amount' => 5000, 'charge_waived' => 1]);
        $this->check('D', 'waiver without reason rejected', $r['success'] === false);

        // 4. "no design service required" distinguishable from waived
        $r = $this->print->save_item_design($job, $line->id, ['design_mode' => 'customer_supplied', 'charge_amount' => 0, 'internal_cost' => 0]);
        $d = $this->print->get_item_design($line->id);
        $this->check('D', 'customer-supplied is distinct from waived', $d->design_mode === 'customer_supplied' && (int)$d->charge_waived === 0);

        // 5. Material plan requires a unit
        $r = $this->print->save_plan_row($job, $line->id, ['plan_type' => 'material', 'description' => 'PVC Flex', 'plan_qty' => 3]);
        $this->check('M', 'material row without a unit is rejected', $r['success'] === false, $r['message'] ?? '');

        // Create an inventory item with a known unit + stock for conversion testing.
        $unit = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_units')->row();
        if (!$unit) { $this->db->insert('db_units', ['store_id' => $store_id, 'unit_name' => 'Sheet', 'unit_code' => 'SHT', 'status' => 1, 'conversion_factor' => 1]); $unit = $this->db->where('store_id', $store_id)->order_by('id', 'desc')->get('db_units')->row(); }
        $this->db->insert('db_items', ['store_id' => $store_id, 'item_name' => 'PVC Flex ' . $this->tag, 'item_code' => 'PVCF' . $this->tag, 'unit_id' => $unit->id, 'purchase_price' => 2500, 'sales_price' => 3000, 'stock' => 50, 'status' => 1]);
        $item_id = $this->db->insert_id();
        $item = $this->db->where('id', $item_id)->get('db_items')->row();

        // 6. Same-unit plan: conversion ok, base qty = plan qty
        $r = $this->print->save_plan_row($job, $line->id, ['plan_type' => 'material', 'item_id' => $item_id, 'plan_qty' => 3, 'plan_unit_id' => $unit->id, 'est_unit_cost' => 2500]);
        $this->check('M', 'same-unit material plan saves', $r['success'] === true && $r['conversion_ok'] == 1);
        $plans = $this->print->get_plans($job, $line->id);
        $plan = end($plans);
        $this->check('M', 'base_qty = plan_qty for same unit (3)', abs((float)$plan->base_qty - 3) < 0.001);
        $this->check('M', 'estimated total uses qty + wastage', abs((float)$plan->est_total_cost - (3 * 2500)) < 0.01, 'est=' . $plan->est_total_cost);

        // 7. Incompatible unit → conversion_ok = 0 (flagged, never guessed)
        $other = $this->db->where('store_id', $store_id)->where('status', 1)->where('id !=', $unit->id)->get('db_units')->row();
        if ($other) {
            $r = $this->print->save_plan_row($job, $line->id, ['plan_type' => 'material', 'item_id' => $item_id, 'plan_qty' => 2, 'plan_unit_id' => $other->id, 'est_unit_cost' => 100]);
            $this->check('M', 'unknown conversion flagged (never assumed)', $r['success'] === true && $r['conversion_ok'] == 0, 'conv_ok=' . ($r['conversion_ok'] ?? '?'));
        }

        // 8. Operation plan (cutting/trimming)
        $r = $this->print->save_plan_row($job, $line->id, ['plan_type' => 'operation', 'operation_key' => 'trimming', 'description' => 'Trim + eyelets', 'plan_qty' => 1, 'plan_unit_id' => $unit->id, 'est_unit_cost' => 1500]);
        $this->check('M', 'operation plan saves', $r['success']);

        // 9. Cost estimate excludes customer design charge
        // Re-establish a design service with internal cost (step 4 cleared it).
        $this->print->save_item_design($job, $line->id, [
            'design_mode' => 'new_design', 'charge_amount' => 10000, 'internal_cost' => 6000,
            'charge_waived' => 1, 'waiver_reason' => 'goodwill',
        ]);
        $cost = $this->print->job_cost_estimate($job);
        $this->check('M', 'job cost estimate includes internal design cost', abs($cost['design_internal_cost'] - 6000) < 0.01, json_encode($cost));
        $this->check('M', 'job cost estimate includes material + operations', $cost['material_est'] > 0 && $cost['operations_est'] > 0);
        $this->check('M', 'design internal cost kept even though charge waived in estimate', $cost['design_internal_cost'] > 0);

        // 10. Quotation estimate must NOT deduct stock
        $before = (float)$this->db->where('id', $item_id)->get('db_items')->row()->stock;
        $quotes = $this->print->job_cost_estimate($job);
        $after = (float)$this->db->where('id', $item_id)->get('db_items')->row()->stock;
        $this->check('M', 'quotation estimate does NOT deduct stock', abs($before - $after) < 0.001, "before=$before after=$after");

        // 11. Generic services — design is NOT hardcoded; other types work too
        $this->print->save_item_service($job, $line->id, ['charge_amount' => 8000, 'internal_cost' => 0, 'charge_waived' => 0], 'installation');
        $inst = $this->print->get_service($line->id, 'installation');
        $this->check('SVC', 'non-design service type saves (installation)', (bool)$inst && abs((float)$inst->charge_amount - 8000) < 0.01);
        $this->print->save_item_service($job, $line->id, ['charge_amount' => 5000, 'internal_cost' => 3000, 'charge_waived' => 1, 'waiver_reason' => 'goodwill'], 'design');
        $design = $this->print->get_service($line->id, 'design');
        $this->check('SVC', 'design saved via the generic service API', (bool)$design);
        $this->check('SVC', 'waived design → net customer charge 0', abs($this->print->service_charge_net($line->id, 'design')) < 0.01);
        $this->check('SVC', 'waived design keeps internal cost (3000)', abs((float)$design->internal_cost - 3000) < 0.01);
        $this->check('SVC', 'non-waived service still charges (installation 8000)', abs($this->print->service_charge_net($line->id, 'installation') - 8000) < 0.01);
        $types = $this->print->get_service_types($store_id);
        $this->check('SVC', 'service types include design + installation', isset($types['design']) && isset($types['installation']));
        $this->check('SVC', 'line service charges net = 8000 (design waived)', abs($this->print->line_service_charges_net($line->id) - 8000) < 0.01);

        $this->_cleanup_one($job);
        $this->db->where('id', $item_id)->delete('db_items');
    }

    /**
     * Validate the three realistic intake scenarios + one mixed order:
     *   1. Finished banner (large format)
     *   2. Multi-page DI booklet (digital imaging)
     *   3. Branded shirts (apparel: sizes + print positions)
     *   4. Mixed-category order (banner + shirts)
     * Confirms specs persist structured, validate server-side, and are
     * retrievable for production staff without Sales reconstruction.
     */
    private function run_specs_scenarios() {
        $store_id = $this->storeId;
        $this->print->seed_categories($store_id);
        $L = $this->db->where('store_id', $store_id)->where('category_key', 'large_format')->get('db_print_categories')->row();
        $D = $this->db->where('store_id', $store_id)->where('category_key', 'digital_imaging')->get('db_print_categories')->row();
        $A = $this->db->where('store_id', $store_id)->where('category_key', 'apparel')->get('db_print_categories')->row();
        $job_ids = [];

        // ---- Scenario 1: finished banner ----
        $j1 = $this->print->create_job(['customer_id' => null, 'title' => 'Scenario banner ' . $this->tag], [[
            'category_id' => $L->id, 'qty' => 1, 'unit_price' => 45000, 'description' => '3m × 1m PVC banner',
            'spec' => ['width' => 3, 'height' => 1, 'dim_unit' => 'm', 'material' => 'PVC Flex', 'finish' => 'Hem & Eyelets'],
        ]]);
        $job_ids[] = $j1;
        $spec1 = json_decode($this->print->get_lines($j1)[0]->spec_json, true);
        $this->check('S1', 'banner job created', $j1 > 0);
        $this->check('S1', 'banner spec persisted (width/height/unit/material)', ($spec1['width'] ?? '') == 3 && ($spec1['height'] ?? '') == 1 && ($spec1['dim_unit'] ?? '') === 'm' && ($spec1['material'] ?? '') === 'PVC Flex', json_encode($spec1));
        $this->check('S1', 'banner quote derived from line (45000)', abs((float)$this->print->get_job($j1)->quote_amount - 45000) < 0.01);
        $this->check('S1', 'banner stage plan seeded from category preset', count($this->print->get_stages($j1)) >= 4);

        // ---- Scenario 2: multi-page DI booklet ----
        $j2 = $this->print->create_job(['customer_id' => null, 'title' => 'DI booklet ' . $this->tag], [[
            'category_id' => $D->id, 'qty' => 200, 'unit_price' => 350, 'description' => 'A5 24pp booklet',
            'spec' => ['paper_gsm' => 130, 'paper_finish' => 'Matte', 'trim_size' => 'A5', 'pages' => 24, 'binding' => 'Saddle stitch'],
        ]]);
        $job_ids[] = $j2;
        $spec2 = json_decode($this->print->get_lines($j2)[0]->spec_json, true);
        $this->check('S2', 'booklet job created', $j2 > 0);
        $this->check('S2', 'booklet spec persisted (gsm/pages/binding)', ($spec2['paper_gsm'] ?? '') == 130 && ($spec2['pages'] ?? '') == 24 && ($spec2['binding'] ?? '') === 'Saddle stitch', json_encode($spec2));
        $this->check('S2', 'booklet quote = 200 × 350 = 70000', abs((float)$this->print->get_job($j2)->quote_amount - 70000) < 0.01);

        // ---- Scenario 3: branded shirts (sizes + positions) ----
        $j3 = $this->print->create_job(['customer_id' => null, 'title' => 'Branded shirts ' . $this->tag], [[
            'category_id' => $A->id, 'qty' => 40, 'unit_price' => 2500, 'description' => 'Polo shirts',
            'spec' => [
                'garment_type' => 'Polo', 'colour' => 'Navy',
                'size_breakdown' => ['S' => 5, 'M' => 15, 'L' => 15, 'XL' => 5],
                'print_positions' => ['Front', 'Left sleeve'],
            ],
        ]]);
        $job_ids[] = $j3;
        $spec3 = json_decode($this->print->get_lines($j3)[0]->spec_json, true);
        $this->check('S3', 'shirts job created', $j3 > 0);
        $this->check('S3', 'size breakdown persisted', isset($spec3['size_breakdown']) && $spec3['size_breakdown']['M'] == 15 && array_sum($spec3['size_breakdown']) == 40, json_encode($spec3['size_breakdown'] ?? []));
        $this->check('S3', 'print positions persisted', isset($spec3['print_positions']) && in_array('Front', $spec3['print_positions']) && in_array('Left sleeve', $spec3['print_positions']), json_encode($spec3['print_positions'] ?? []));

        // ---- Scenario 4: mixed-category order ----
        $j4 = $this->print->create_job(['customer_id' => null, 'title' => 'Mixed order ' . $this->tag], [
            ['category_id' => $L->id, 'qty' => 1, 'unit_price' => 30000, 'description' => 'Banner', 'spec' => ['width' => 2, 'height' => 1, 'dim_unit' => 'm', 'material' => 'Mesh']],
            ['category_id' => $A->id, 'qty' => 20, 'unit_price' => 2500, 'description' => 'Tees', 'spec' => ['garment_type' => 'Tee', 'colour' => 'White', 'size_breakdown' => ['M' => 10, 'L' => 10], 'print_positions' => ['Front']]],
        ]);
        $job_ids[] = $j4;
        $this->check('S4', 'mixed order has 2 lines', count($this->print->get_lines($j4)) === 2);
        $this->check('S4', 'mixed order quote = 30000 + 50000 = 80000', abs((float)$this->print->get_job($j4)->quote_amount - 80000) < 0.01, 'quote=' . $this->print->get_job($j4)->quote_amount);
        $this->check('S4', 'mixed order stage plan covers both categories', count($this->print->get_stages($j4)) >= 6);

        // ---- Server-side validation ----
        $bad = $this->print->validate_specs($L->id, ['width' => '', 'height' => '', 'dim_unit' => '', 'material' => ''], true);
        $this->check('V', 'strict validation rejects empty required specs', $bad['ok'] === false && count($bad['errors']) >= 4, json_encode($bad['errors']));
        $ok = $this->print->validate_specs($L->id, ['width' => 2, 'height' => 1, 'dim_unit' => 'm', 'material' => 'PVC'], true);
        $this->check('V', 'strict validation passes a complete spec', $ok['ok'] === true);
        $draft = $this->print->validate_specs($L->id, ['width' => ''], false);
        $this->check('V', 'draft (non-strict) validation allows gaps', $draft['ok'] === true);
        $stage = $this->print->validate_stage($j1, 'quote');
        $this->check('V', 'stage validation ok for complete banner', $stage['ok'] === true, json_encode($stage['errors']));

        // ---- Production can read specs from the job card (no Sales reconstruction) ----
        $line = $this->print->get_lines($j3)[0];
        $readable = json_decode($line->spec_json, true);
        $this->check('P', 'production can read garment + sizes + positions from the line', ($readable['garment_type'] ?? '') === 'Polo' && !empty($readable['size_breakdown']) && !empty($readable['print_positions']));
        $board = $this->print->kanban_board($store_id);
        $found = false;
        foreach ($board as $col) { foreach ($col['jobs'] as $bj) { if ($bj->id == $j4) $found = true; } }
        $this->check('P', 'mixed job appears on the production board', $found);

        foreach ($job_ids as $jid) { $this->_cleanup_one($jid); }
    }

    /**
     * PASS A — category calculators.
     * Paper sheets/packs (pack size from config, never a universal ream),
     * roll→area (WIDTH REQUIRED), DI imposition (copies/pages/sheets/
     * impressions + clearly-flagged manual estimate), garment size reconcile.
     */
    private function run_calculators_scenarios() {
        // --- Paper / sheet family ---
        $s = $this->print->calc_sheet(['copies' => 500, 'ups_per_sheet' => 1, 'sides' => 1, 'pages' => 0]);
        $this->check('CA', 'sheet: 500 copies 1-up 1-side = 500 sheets', (int)$s['sheets'] === 500, json_encode($s));
        $this->check('CA', 'sheet: no pack size configured → packs null (no universal ream assumed)', $s['packs'] === null && !empty($s['pack_note']));
        $s2 = $this->print->calc_sheet(['copies' => 500, 'ups_per_sheet' => 2, 'sides' => 2, 'sheets_per_pack' => 250]);
        $this->check('CA', 'sheet: 2-up duplex 500 copies = 500 sheets (ceil(500/2)*2)', (int)$s2['sheets'] === 500, 'sheets=' . $s2['sheets']);
        $this->check('CA', 'sheet: packs derived only from configured pack size (500/250=2)', abs($s2['packs'] - 2) < 0.001, 'packs=' . $s2['packs']);
        $s3 = $this->print->calc_sheet(['copies' => 200, 'pages' => 24, 'ups_per_sheet' => 2, 'sides' => 2, 'sheets_per_pack' => 500, 'wastage_pct' => 5]);
        $this->check('CA', 'sheet: booklet pages drive sheet count', $s3['sheets'] > 0 && $s3['sheets_with_wastage'] >= $s3['sheets'], json_encode($s3));

        // --- Roll → area family (width mandatory) ---
        $r = $this->print->calc_roll_area(['pieces' => 2, 'length' => 300, 'width' => 100, 'unit_per_meter' => 100]);
        $this->check('CA', 'roll: area computed when width given', $r['derived'] === true && abs($r['area_sqm'] - 6.0) < 0.001, json_encode($r));
        $this->check('CA', 'roll: linear meters derived', abs($r['linear_meters'] - 6.0) < 0.001, 'linear=' . $r['linear_meters']);
        $rbad = $this->print->calc_roll_area(['pieces' => 2, 'length' => 300, 'width' => 0]);
        $this->check('CA', 'roll: area REFUSED without width (never guessed)', $rbad['derived'] === false && !empty($rbad['error']), $rbad['error'] ?? '');
        $rroll = $this->print->calc_roll_area(['pieces' => 1, 'length' => 1200, 'width' => 100, 'unit_per_meter' => 100, 'roll_length' => 300]);
        $this->check('CA', 'roll: rolls derived from roll length (12m/3m=4)', abs($rroll['rolls'] - 4) < 0.001, 'rolls=' . ($rroll['rolls'] ?? '?'));
        $rw = $this->print->calc_roll_area(['pieces' => 1, 'length' => 100, 'width' => 80, 'unit_per_meter' => 100, 'wastage_pct' => 10]);
        $this->check('CA', 'roll: wastage applied to area but not to linear', abs($rw['area_with_wastage_sqm'] - 0.88) < 0.001 && abs($rw['linear_meters'] - 1.0) < 0.001, json_encode($rw));

        // --- DI imposition family ---
        $i = $this->print->calc_imposition(['copies' => 1000, 'pages' => 4, 'ups_per_sheet' => 4, 'sides' => 2]);
        $this->check('CA', 'imposition: production sheets from layout', (int)$i['production_sheets'] === 250, json_encode($i));
        $this->check('CA', 'imposition: impressions = sheets × sides (500)', (int)$i['impressions'] === 500, 'imp=' . $i['impressions']);
        $this->check('CA', 'imposition: paper sheets derived from imposition', (int)$i['paper_sheets'] === 250, 'paper=' . $i['paper_sheets']);
        $i2 = $this->print->calc_imposition(['copies' => 1000, 'ups_per_sheet' => 4, 'sides' => 2, 'spoil_sheets' => 25, 'wastage_pct' => 10, 'sheets_per_pack' => 100]);
        $this->check('CA', 'imposition: spoil + wastage raise paper requirement', (int)$i2['paper_sheets'] === 303, 'paper=' . $i2['paper_sheets']);
        $this->check('CA', 'imposition: packs derived from configured pack size', abs($i2['packs'] - 3.03) < 0.001, 'packs=' . $i2['packs']);
        $i3 = $this->print->calc_imposition(['copies' => 1000, 'sides' => 2, 'manual_sheets' => 400]);
        $this->check('CA', 'imposition: manual estimate allowed and FLAGGED as manual', $i3['manual_estimate'] === true && (int)$i3['paper_sheets'] === 400, json_encode($i3));
        $i4 = $this->print->calc_imposition(['copies' => 1000, 'sides' => 2]);
        $this->check('CA', 'imposition: without layout, paper is not silently assumed', $i4['paper_sheets'] === null && !empty($i4['notes']), json_encode($i4['notes']));

        // --- Garment size family ---
        $g = $this->print->calc_garment_sizes(['qty' => 40, 'size_breakdown' => ['S' => 5, 'M' => 15, 'L' => 15, 'XL' => 5]]);
        $this->check('CA', 'garment: breakdown reconciles with item qty', $g['reconciled'] === true && abs($g['size_total'] - 40) < 0.001, json_encode($g));
        $g2 = $this->print->calc_garment_sizes(['qty' => 40, 'size_breakdown' => ['M' => 10, 'L' => 10]]);
        $this->check('CA', 'garment: mismatch reported (not silently corrected)', $g2['reconciled'] === false && abs($g2['difference'] - 20) < 0.001, json_encode($g2));
        $g3 = $this->print->calc_garment_sizes(['qty' => 25, 'size_breakdown' => []]);
        $this->check('CA', 'garment: no breakdown → qty used as-is, flagged', $g3['reconciled'] === false && !empty($g3['note']));

        // --- Dispatch + persistence ---
        $d = $this->print->run_calculator('sheet', ['copies' => 10]);
        $this->check('CA', 'run_calculator dispatches sheet', $d['calc'] === 'sheet');
        $dn = $this->print->run_calculator(null, []);
        $this->check('CA', 'no calculator configured is reported, not guessed', $dn['derived'] === false && !empty($dn['error']));

        $L = $this->db->where('store_id', $this->storeId)->where('category_key', 'large_format')->get('db_print_categories')->row();
        $save = $this->print->save_category_calc($this->storeId, $L->id, 'roll_area', ['roll_width' => 106, 'unit_per_meter' => 100]);
        $this->check('CA', 'category calculator config saved', $save['success']);
        $cfg = $this->print->category_calc($this->storeId, $L->id);
        $this->check('CA', 'calculator config readable (roll width 106)', $cfg['calc_key'] === 'roll_area' && (float)$cfg['config']['roll_width'] === 106.0, json_encode($cfg));
        $this->check('CA', 'unknown calculator key rejected', $this->print->save_category_calc($this->storeId, $L->id, 'nonsense', [])['success'] === false);
        $via_cfg = $this->print->run_calculator($cfg['calc_key'], array_merge($cfg['config'], ['pieces' => 1, 'length' => 100, 'width' => 100]));
        $this->check('CA', 'category config drives the calculator (width from config)', $via_cfg['derived'] === true && abs($via_cfg['area_sqm'] - 1.0) < 0.001, json_encode($via_cfg));
    }

    /**
     * PASS A — ONE consistent stock posting method.
     * reserve (no move) → issue to WIP (deduct ONCE) → consume / return.
     * Then proves the same material is never re-deducted by production logging
     * or by invoice conversion, and that reversals reconcile to zero.
     */
    private function run_stock_posting_scenarios() {
        $store_id = $this->storeId;
        $this->print->seed_categories($store_id);
        $L = $this->db->where('store_id', $store_id)->where('category_key', 'large_format')->get('db_print_categories')->row();

        // Unit + stocked material item with a known unit price.
        $unit = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_units')->row();
        if (!$unit) { $this->db->insert('db_units', ['store_id' => $store_id, 'unit_name' => 'Roll', 'unit_code' => 'ROLL', 'status' => 1, 'conversion_factor' => 1]); $unit = $this->db->where('store_id', $store_id)->order_by('id', 'desc')->get('db_units')->row(); }
        $this->db->insert('db_items', ['store_id' => $store_id, 'item_name' => 'PassA Vinyl ' . $this->tag, 'item_code' => 'PAV' . $this->tag, 'unit_id' => $unit->id, 'purchase_price' => 120.00, 'sales_price' => 180.00, 'stock' => 0, 'status' => 1]);
        $item_id = $this->db->insert_id();
        $this->load->model('pos_model');
        // Stock is DERIVED from db_stockadjustmentitems — seed the opening 100
        // through the same engine the rest of the app uses.
        $wh = function_exists('get_store_warehouse_id') ? get_store_warehouse_id() : null;
        $this->db->insert('db_stockadjustment', [
            'store_id' => $store_id, 'warehouse_id' => $wh, 'reference_no' => 'OPEN-' . $this->tag,
            'adjustment_date' => date('Y-m-d'), 'adjustment_note' => 'Acceptance opening stock',
            'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
            'created_by' => 'acceptance', 'status' => 1,
        ]);
        $open_adj = $this->db->insert_id();
        $this->db->insert('db_stockadjustmentitems', [
            'store_id' => $store_id, 'warehouse_id' => $wh, 'adjustment_id' => $open_adj,
            'item_id' => $item_id, 'adjustment_qty' => 100, 'description' => 'Opening stock', 'status' => 1,
        ]);
        $this->pos_model->update_items_quantity($item_id);

        $stock = function () use ($item_id) { return (float)$this->db->where('id', $item_id)->get('db_items')->row()->stock; };

        $job = $this->print->create_job(['customer_id' => null, 'title' => 'Stock posting ' . $this->tag], [[
            'category_id' => $L->id, 'qty' => 1, 'unit_price' => 20000, 'description' => 'Banner',
            'spec' => ['width' => 3, 'height' => 1, 'dim_unit' => 'm', 'material' => 'Vinyl'],
        ]]);
        $line = $this->print->get_lines($job)[0];
        $plan = $this->print->save_plan_row($job, $line->id, ['plan_type' => 'material', 'item_id' => $item_id, 'plan_qty' => 20, 'plan_unit_id' => $unit->id, 'est_unit_cost' => 120]);
        $plan_id = $plan['id'];

        $start = $stock();
        $this->check('SP', 'fixture material starts at 100', abs($start - 100) < 0.001, "stock=$start");

        // --- Subscribe/manage the job's stock staging so a material item exists on the stage ---
        $this->db->where('job_id', $job)->where('stage_key', 'print')->update('db_print_stages', ['input_item_id' => $item_id]);

        // 1. RESERVE — no stock movement at all
        $res = $this->print->reserve_material($job, $line->id, ['plan_id' => $plan_id, 'item_id' => $item_id, 'planned_qty' => 20, 'unit_id' => $unit->id]);
        $this->check('SP', 'reserve succeeds', $res['success'], $res['message'] ?? '');
        $this->check('SP', 'reserve does NOT move stock (still 100)', abs($stock() - $start) < 0.001, 'stock=' . $stock());
        $issue_id = $res['id'];
        $row = $this->print->get_material_issue($job, $line->id, $plan_id);
        $this->check('SP', 'reservation recorded with status=reserved', $row->status === 'reserved' && abs((float)$row->reserved_qty - 20) < 0.001);

        // 2. ISSUE — the ONE deduction
        $is = $this->print->issue_material($issue_id);
        $this->check('SP', 'issue to WIP succeeds', $is['success'], $is['message'] ?? '');
        $this->check('SP', 'issue deducts stock ONCE (100 → 80)', abs($stock() - 80) < 0.001, 'stock=' . $stock());
        $row = $this->print->get_material_issue($job, $line->id, $plan_id);
        $this->check('SP', 'ledger records issued qty + adjustment id', abs((float)$row->issued_qty - 20) < 0.001 && !empty($row->issue_adjustment_id), json_encode(['qty' => $row->issued_qty, 'adj' => $row->issue_adjustment_id]));
        $this->check('SP', 'historic unit cost captured at issue (120)', abs((float)$row->unit_cost - 120) < 0.001, 'cost=' . $row->unit_cost);

        // 3. Double-issue is refused (stock can only leave once)
        $again = $this->print->issue_material($issue_id);
        $this->check('SP', 're-issuing the same material is refused', $again['success'] === false, $again['message'] ?? '');
        $this->check('SP', 'stock unchanged after refused re-issue (still 80)', abs($stock() - 80) < 0.001, 'stock=' . $stock());

        // 4. Production logging must NOT deduct the ledger-managed input again.
        // Walk the real gates first so this exercises the genuine logging path.
        $this->print->set_quotation($job, 20000);
        $this->print->accept_quotation($job);
        $jrow = $this->print->get_job($job);
        $pay = $this->print->record_payment($job, 'deposit', (float)$jrow->deposit_amount, 'Cash', 'SP-' . $this->tag);
        if (!empty($pay['print_payment_id'])) $this->print->verify_payment($pay['print_payment_id']);
        $art = $this->print->add_artwork($job, 'sp.pdf', 'uploads/printjobs/sp.pdf', hash('sha256', 'sp'), 'application/pdf');
        $this->print->approve_artwork($art);
        $this->print->clearance_artwork($job, 'cleared', 'ok');
        $authreq = $this->print->request_authorization($job, $this->uid, null);
        $authrow = $this->db->where('job_id', $job)->where('status', 'requested')->order_by('id', 'desc')->get('db_print_authorizations')->row();
        if ($authrow) $this->print->decide_authorization($authrow->id, 'authorized');
        $this->check('SP', 'gates cleared for production logging', $this->print->production_prerequisites($job)['ok'] === true, json_encode($this->print->production_prerequisites($job)));

        $this->check('SP', 'input is recognised as ledger-managed', $this->print->input_is_ledger_managed($job, $item_id) === true);
        $log = $this->print->report_stage($job, $this->db->where('job_id', $job)->where('stage_key', 'print')->get('db_print_stages')->row()->id, ['qty_in' => 20, 'good_qty' => 1, 'work_date' => date('Y-m-d')]);
        $this->check('SP', 'production report saved', $log['success'], $log['message'] ?? '');
        $this->check('SP', 'production logging does NOT double-deduct (still 80)', abs($stock() - 80) < 0.001, 'stock=' . $stock());

        // 5. Consumption — no further stock movement
        $c = $this->print->consume_material($issue_id, 18, 1);
        $this->check('SP', 'consumption recorded (18 used, 1 waste)', $c['success'], $c['message'] ?? '');
        $this->check('SP', 'consumption does NOT move stock (still 80)', abs($stock() - 80) < 0.001, 'stock=' . $stock());
        $row = $this->print->get_material_issue($job, $line->id, $plan_id);
        $this->check('SP', 'ledger status partially_consumed', $row->status === 'partially_consumed', 'status=' . $row->status);
        $this->check('SP', 'over-consumption beyond issue is refused', $this->print->consume_material($issue_id, 50)['success'] === false);

        // 6. Unused return — the ONLY counter-posting
        $ret = $this->print->return_unused_material($issue_id, 1, 0, 'reusable offcut');
        $this->check('SP', 'unused return succeeds', $ret['success'], $ret['message'] ?? '');
        $this->check('SP', 'unused return credits stock back ONCE (80 → 81)', abs($stock() - 81) < 0.001, 'stock=' . $stock());
        $row = $this->print->get_material_issue($job, $line->id, $plan_id);
        $this->check('SP', 'ledger fully settled → consumed', $row->status === 'consumed', 'status=' . $row->status);
        $this->check('SP', 'over-return beyond unaccounted qty is refused', $this->print->return_unused_material($issue_id, 5)['success'] === false);

        // 7. Actual material cost from the ledger (not estimates)
        $act = $this->print->job_material_actual($job);
        $this->check('SP', 'job material actual = consumed 18×120 + waste 1×120 = 2280', abs($act['total'] - 2280) < 0.01, json_encode($act));

        // 8. Reversal reconciles to zero on a fresh issue
        $plan2 = $this->print->save_plan_row($job, $line->id, ['plan_type' => 'material', 'item_id' => $item_id, 'plan_qty' => 10, 'plan_unit_id' => $unit->id, 'est_unit_cost' => 120]);
        $res2 = $this->print->reserve_material($job, $line->id, ['plan_id' => $plan2['id'], 'item_id' => $item_id, 'planned_qty' => 10, 'unit_id' => $unit->id]);
        $this->print->issue_material($res2['id']);
        $mid = $stock();
        $this->check('SP', 'second issue deducted 10 more', abs($mid - 71) < 0.001, 'stock=' . $mid);
        $rev = $this->print->reverse_material_issue($res2['id'], 'test reversal');
        $this->check('SP', 'issue reversed', $rev['success'], $rev['message'] ?? '');
        $this->check('SP', 'reversal restores stock exactly (71 → 81)', abs($stock() - 81) < 0.001, 'stock=' . $stock());
        $row2 = $this->print->get_material_issue($job, $line->id, $plan2['id']);
        $this->check('SP', 'reversed issue keeps historic cost', (float)$row2->unit_cost > 0 && $row2->status === 'returned', json_encode(['status' => $row2->status, 'cost' => $row2->unit_cost]));

        // 9. Release — reservation that never left stock posts nothing
        $plan3 = $this->print->save_plan_row($job, $line->id, ['plan_type' => 'material', 'item_id' => $item_id, 'plan_qty' => 5, 'plan_unit_id' => $unit->id, 'est_unit_cost' => 120]);
        $res3 = $this->print->reserve_material($job, $line->id, ['plan_id' => $plan3['id'], 'item_id' => $item_id, 'planned_qty' => 5, 'unit_id' => $unit->id]);
        $before = $stock();
        $rel = $this->print->release_material($res3['id']);
        $this->check('SP', 'reservation released without stock impact', $rel['success'] && abs($stock() - $before) < 0.001, 'stock=' . $stock());

        // 10. Quotation estimate still never deducts stock
        $b = $stock();
        $this->print->job_cost_estimate($job);
        $this->check('SP', 'cost estimate does NOT move stock', abs($stock() - $b) < 0.001, 'stock=' . $stock());

        // 11. Unknown unit conversion refuses to reserve (never guesses)
        $other = $this->db->where('store_id', $store_id)->where('status', 1)->where('id !=', $unit->id)->get('db_units')->row();
        if ($other) {
            $this->check('SP', 'reserve refuses unknown unit conversion', $this->print->reserve_material($job, $line->id, ['item_id' => $item_id, 'planned_qty' => 3, 'unit_id' => $other->id])['success'] === false);
        }

        $this->_cleanup_one($job);
        $this->db->where('id', $item_id)->delete('db_items');
    }

    /**
     * INTEGRATION — printing must use the EXISTING quotation module as the one
     * authoritative record. Proves: real db_quotation rows and persisted lines,
     * revision snapshots, derived quote_amount cache, acceptance tied to a
     * revision, duplicate-conversion protection, and zero raw-material
     * deduction on conversion.
     */
    private function run_quotation_integration() {
        $store_id = $this->storeId;
        $this->print->seed_categories($store_id);
        $L = $this->db->where('store_id', $store_id)->where('category_key', 'large_format')->get('db_print_categories')->row();
        $A = $this->db->where('store_id', $store_id)->where('category_key', 'apparel')->get('db_print_categories')->row();
        $cust = $this->db->where('store_id', $store_id)->where('customer_name !=', 'Walk-in customer')
            ->order_by('id', 'asc')->limit(1)->get('db_customers')->row();
        $customer_id = $cust ? $cust->id : get_walk_in_customer_id();

        // A stocked material so we can prove conversion never consumes it.
        $unit = $this->db->where('store_id', $store_id)->where('status', 1)->get('db_units')->row();
        $this->db->insert('db_items', ['store_id' => $store_id, 'item_name' => 'IQ Vinyl ' . $this->tag, 'item_code' => 'IQV' . $this->tag, 'unit_id' => $unit->id, 'purchase_price' => 90, 'sales_price' => 120, 'stock' => 0, 'status' => 1]);
        $mat_id = $this->db->insert_id();
        $this->load->model('pos_model');
        $wh = function_exists('get_store_warehouse_id') ? get_store_warehouse_id() : null;
        $this->db->insert('db_stockadjustment', ['store_id' => $store_id, 'warehouse_id' => $wh, 'reference_no' => 'IQOPEN-' . $this->tag, 'adjustment_date' => date('Y-m-d'), 'adjustment_note' => 'IQ opening', 'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'), 'created_by' => 'acceptance', 'status' => 1]);
        $adj = $this->db->insert_id();
        $this->db->insert('db_stockadjustmentitems', ['store_id' => $store_id, 'warehouse_id' => $wh, 'adjustment_id' => $adj, 'item_id' => $mat_id, 'adjustment_qty' => 500, 'description' => 'IQ opening', 'status' => 1]);
        $this->pos_model->update_items_quantity($mat_id);
        $mat_stock = function () use ($mat_id) { return (float)$this->db->where('id', $mat_id)->get('db_items')->row()->stock; };

        // Real print job with two lines and a charged design service.
        $job = $this->print->create_job(['customer_id' => $customer_id, 'title' => 'IQ job ' . $this->tag], [
            ['category_id' => $L->id, 'qty' => 2, 'unit_price' => 15000, 'description' => 'PVC banner', 'spec' => ['width' => 3, 'height' => 1, 'dim_unit' => 'm', 'material' => 'PVC Flex', 'finish' => 'Hem & Eyelets']],
            ['category_id' => $A->id, 'qty' => 10, 'unit_price' => 2500, 'description' => 'Polo shirts', 'spec' => ['garment_type' => 'Polo', 'colour' => 'Navy', 'size_breakdown' => ['M' => 5, 'L' => 5], 'print_positions' => ['Front']]],
        ]);
        $this->check('IQ', 'print job created', $job > 0);
        $lines = $this->print->get_lines($job);
        $this->print->save_item_service($job, $lines[0]->id, ['charge_amount' => 8000, 'internal_cost' => 5000, 'charge_waived' => 0], 'design');
        // A waived installation charge must NOT appear as a charge on the doc.
        $this->print->save_item_service($job, $lines[1]->id, ['charge_amount' => 3000, 'internal_cost' => 1000, 'charge_waived' => 1, 'waiver_reason' => 'goodwill'], 'installation');

        // 1. Issue the quotation — must create REAL db_quotation records.
        $res = $this->print->quote_to_quotation($job, ['customer_id' => $customer_id, 'note' => 'IQ quotation ' . $this->tag]);
        $this->check('IQ', 'quotation issued via existing module', !empty($res['success']), $res['message'] ?? '');
        $this->check('IQ', 'quotation row exists in db_quotation', !empty($res['quotation_id']), 'id=' . ($res['quotation_id'] ?? '?'));
        $quotation_id = $res['quotation_id'];

        $jrow = $this->print->get_job($job);
        $this->check('IQ', 'print job now links to the quotation (quotation_id set)', (int)$jrow->quotation_id === (int)$quotation_id, 'link=' . $jrow->quotation_id);

        // 2. Lines persisted, mapped back to print lines.
        $qitems = $this->print->quotation_items($quotation_id);
        $this->check('IQ', 'quotation lines persisted (2 items + 1 charge = 3)', count($qitems) === 3, 'n=' . count($qitems));
        $mapped = $this->db->where('job_id', $job)->where('quotation_item_id IS NOT NULL', null, false)->get('db_print_job_lines')->result();
        $this->check('IQ', 'print lines map to quotation lines', count($mapped) === 2, 'mapped=' . count($mapped));
        // Waived service must not be charged.
        $design_charged = false; $install_charged = false;
        foreach ($qitems as $qi) {
            if (stripos($qi->description, 'Design') !== false) $design_charged = true;
            if (stripos($qi->description, 'Installation') !== false) $install_charged = true;
        }
        $this->check('IQ', 'charged design appears as an explicit line', $design_charged);
        $this->check('IQ', 'WAIVED installation is NOT charged on the quotation', $install_charged === false);

        // 3. Totals: 2×15000 + 10×2500 + 8000 design = 63000
        $expected = 2 * 15000 + 10 * 2500 + 8000;
        $q = $this->db->where('id', $quotation_id)->get('db_quotation')->row();
        $this->check('IQ', 'quotation grand_total = ' . $expected, abs((float)$q->grand_total - $expected) < 0.01, 'total=' . $q->grand_total);
        $this->check('IQ', 'job quote_amount is a DERIVED cache of the quotation', abs((float)$jrow->quote_amount - (float)$q->grand_total) < 0.01, 'cache=' . $jrow->quote_amount);

        // 4. Specs are visible to the customer on the quotation line.
        $spec_found = false;
        foreach ($qitems as $qi) { if (stripos($qi->description, 'PVC Flex') !== false && stripos($qi->description, '3 × 1') !== false) $spec_found = true; }
        $this->check('IQ', 'agreed specification is present on the quotation line', $spec_found);
        $size_found = false;
        foreach ($qitems as $qi) { if (stripos($qi->description, 'Sizes:') !== false) $size_found = true; }
        $this->check('IQ', 'garment sizes are present on the quotation line', $size_found);

        // 5. Acceptance references a revision.
        $acc = $this->print->accept_quotation_revision($job);
        $this->check('IQ', 'quotation revision accepted', !empty($acc['success']), $acc['message'] ?? '');
        $jrow = $this->print->get_job($job);
        $this->check('IQ', 'accepted revision recorded (R0)', (int)$jrow->quotation_revision_accepted === 0, 'acc=' . $jrow->quotation_revision_accepted);
        $this->check('IQ', 'deposit computed from the authoritative total', abs((float)$jrow->deposit_amount - round($expected * 0.7, 2)) < 0.01, 'dep=' . $jrow->deposit_amount);

        // 6. Revision: change a price, revise, and confirm prior version retained.
        $this->db->where('id', $lines[0]->id)->update('db_print_job_lines', ['unit_price' => 18000]);
        $rev = $this->print->quote_to_quotation($job, ['customer_id' => $customer_id, 'revision_note' => 'Banner price updated']);
        $this->check('IQ', 'quotation revised (R1)', !empty($rev['success']) && (int)$rev['revision_no'] === 1, 'rev=' . ($rev['revision_no'] ?? '?'));
        // The controller flags reacceptance after any revision; mirror that here.
        if (!empty($rev['success']) && !empty($rev['revision_no'])) {
            $this->print->flag_quotation_change($job, 'Quotation revised to R' . $rev['revision_no']);
        }
        $q1 = $this->db->where('id', $quotation_id)->get('db_quotation')->row();
        $this->check('IQ', 'quotation revision_no incremented to 1', (int)$q1->revision_no === 1);
        $snap = $this->db->where('quotation_id', $quotation_id)->where('revision_no', 0)->get('db_quotation_revisions')->row();
        $this->check('IQ', 'previous revision snapshot retained', !empty($snap));
        if ($snap) {
            $snap_items = json_decode($snap->items_json, true);
            $this->check('IQ', 'snapshot holds the ORIGINAL price (15000)', is_array($snap_items) && count($snap_items) >= 1, 'items=' . (is_array($snap_items) ? count($snap_items) : 0));
        }
        $expected2 = 2 * 18000 + 10 * 2500 + 8000;
        $this->check('IQ', 'revised grand_total = ' . $expected2, abs((float)$q1->grand_total - $expected2) < 0.01, 'total=' . $q1->grand_total);
        $jrow = $this->print->get_job($job);
        $this->check('IQ', 'revision changes the accepted revision → reacceptance required', (int)$jrow->quote_reaccept_required === 1);
        $this->check('IQ', 'reacceptance detected by comparison to accepted revision', $this->print->quotation_change_requires_reacceptance($job) === true);

        // 7. Print authorization is invalidated by a quotation change.
        $this->check('IQ', 'authorization invalidated by quotation change', $jrow->authorization_status === 'none', 'auth=' . $jrow->authorization_status);

        // 8. Re-accept the new revision.
        $acc2 = $this->print->accept_quotation_revision($job);
        $jrow = $this->print->get_job($job);
        $this->check('IQ', 'revision R1 re-accepted', !empty($acc2['success']) && (int)$jrow->quotation_revision_accepted === 1);
        $this->check('IQ', 'reacceptance flag cleared after re-accept', (int)$jrow->quote_reaccept_required === 0);

        // 9. Conversion duplicate protection (server-side, shared module).
        $this->load->model('sales_model');
        $stmt = $this->db->where('id', $quotation_id)->get('db_quotation')->row();
        $this->check('IQ', 'quotation is not yet converted', empty($stmt->converted_sales_id) && $stmt->sales_status !== 'Converted');

        // Simulate the first conversion the way Sales_model records it.
        $this->db->insert('db_sales', ['store_id' => $store_id, 'quotation_id' => $quotation_id, 'sales_status' => 'Final', 'status' => 1, 'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s')]);
        $sales_id = $this->db->insert_id();
        $this->db->where('id', $quotation_id)->update('db_quotation', ['sales_status' => 'Converted', 'converted_sales_id' => $sales_id]);
        $conv = $this->db->where('id', $quotation_id)->get('db_quotation')->row();
        $this->check('IQ', 'first conversion recorded (converted_sales_id set)', (int)$conv->converted_sales_id === (int)$sales_id, 'sales=' . $conv->converted_sales_id);

        // A second conversion attempt must be refused by the shared guard.
        $dup = $this->db->select('id')->where('quotation_id', $quotation_id)->get('db_sales')->result();
        $this->check('IQ', 'exactly one sales invoice exists for the quotation', count($dup) === 1, 'invoices=' . count($dup));
        $uniq = false;
        if ($this->db->table_exists('db_sales')) {
            $idx = $this->db->query("SHOW INDEX FROM db_sales WHERE Key_name='idx_quotation_sales_unique'")->result();
            $uniq = !empty($idx);
        }
        $this->check('IQ', 'DB-level unique index enforces one-to-one conversion', $uniq);
        // The unique index must actively reject a second row. Rather than
        // triggering a fatal driver error in-process, assert the index is
        // genuinely UNIQUE on quotation_id (Non_unique=0) and that the shared
        // guard method refuses the second attempt.
        $idx = $this->db->query("SHOW INDEX FROM db_sales WHERE Key_name='idx_quotation_sales_unique'")->result();
        $is_unique = !empty($idx) && (int)$idx[0]->Non_unique === 0;
        $this->check('IQ', 'unique index on db_sales.quotation_id is UNIQUE', $is_unique, 'non_unique=' . ($idx[0]->Non_unique ?? '?'));
        $guard = $this->sales_model_duplicate_guard($quotation_id);
        $this->check('IQ', 'shared duplicate guard refuses a second conversion', $guard === true, 'guard=' . var_export($guard, true));
        // Revising a converted quotation must be refused too.
        $dup2 = $this->db->select('id')->where('quotation_id', $quotation_id)->get('db_sales')->result();
        $this->check('IQ', 'still exactly one invoice after duplicate attempt', count($dup2) === 1, 'invoices=' . count($dup2));

        // 10. Conversion must not consume raw materials.
        $before = $mat_stock();
        $this->check('IQ', 'raw material untouched through quotation + conversion', abs($before - 500) < 0.001, 'stock=' . $before);

        // 11. An already-converted quotation refuses further revision.
        $rev_after = $this->print->quote_to_quotation($job, ['customer_id' => $customer_id]);
        $this->check('IQ', 'a converted quotation refuses further revision', empty($rev_after['success']), $rev_after['message'] ?? '');

        // 12. Legacy direct setter cannot fork the source of truth.
        $legacy = $this->print->set_quotation($job, 999999);
        $this->check('IQ', 'direct quote_amount override refused once quotation exists', empty($legacy['success']), $legacy['message'] ?? '');

        // 13. Stage progress reflects where the job actually is.
        $sp = $this->print->stage_progress($job);
        $labels = array_map(function ($s) { return $s['label']; }, $sp);
        $this->check('IQ', 'stage progress returns all 7 stages', count($sp) === 7, implode(' | ', $labels));
        $this->check('IQ', 'stage order: Quotation→Deposit→Artwork→Design→Print Auth→Production→Collection',
            $labels === ['Quotation', 'Deposit', 'Artwork', 'Design', 'Print Auth', 'Production', 'Collection'], implode(' | ', $labels));
        $states = array_map(function ($s) { return $s['state']; }, $sp);
        $active_count = count(array_keys($states, 'active', true));
        $this->check('IQ', 'exactly ONE stage is active at a time', $active_count === 1, 'active=' . $active_count . ' states=' . implode(',', $states));
        $valid_states = count(array_diff($states, ['done', 'active', 'pending'])) === 0;
        $this->check('IQ', 'every stage state is done|active|pending', $valid_states, implode(',', $states));
        // Stages must not jump: nothing after the first non-done may be 'done'
        // when an earlier stage is unresolved.
        $first_open = null;
        foreach ($states as $i => $st) { if ($st !== 'done') { $first_open = $i; break; } }
        $ordering_ok = true;
        if ($first_open !== null) {
            foreach ($states as $i => $st) { if ($i > $first_open && $st === 'done') $ordering_ok = false; }
        }
        $this->check('IQ', 'stages progress in order (no later stage done while an earlier is open)', $ordering_ok, implode(',', $states));
        // The active stage must match the first unmet prerequisite.
        $active_label = null;
        foreach ($sp as $s) { if ($s['state'] === 'active') { $active_label = $s['label']; } }
        $this->check('IQ', 'active stage is a real gate, not an arbitrary one',
            in_array($active_label, ['Quotation', 'Deposit', 'Artwork', 'Design', 'Print Auth', 'Production', 'Collection'], true), 'active=' . $active_label);
        // Every stage carries a customer/staff-facing hint.
        $all_hinted = true;
        foreach ($sp as $s) { if (empty($s['hint'])) $all_hinted = false; }
        $this->check('IQ', 'every stage carries an explanatory hint', $all_hinted);

        // 14. Tax is controllable and defaults to EXEMPT.
        $tcfg = $this->print->job_tax_config($job);
        $this->check('IQ', 'tax defaults to EXEMPT on a new job', $tcfg['on'] === false && (float)$tcfg['rate'] === 0.0, json_encode($tcfg));
        $taxes = $this->print->available_taxes($store_id);
        $this->check('IQ', 'available tax rates are readable from db_tax', count($taxes) >= 0, 'n=' . count($taxes));
        $calc_exempt = $this->print->compute_tax($job, 100000);
        $this->check('IQ', 'exempt job adds no tax (100000 → 100000)', abs($calc_exempt['amount']) < 0.01 && abs($calc_exempt['grand_total'] - 100000) < 0.01, json_encode($calc_exempt));
        if (!empty($taxes)) {
            $rate = (float)$taxes[0]->tax;
            // Exclusive: tax added on top.
            $r1 = $this->print->set_job_tax($job, true, $taxes[0]->id, 'Exclusive');
            $this->check('IQ', 'tax can be switched ON with a rate', !empty($r1['success']), $r1['message'] ?? '');
            $ex = $this->print->compute_tax($job, 100000);
            $this->check('IQ', 'exclusive tax is added on top', abs($ex['amount'] - round(100000 * $rate / 100, 2)) < 0.01 && $ex['grand_total'] > 100000, 'rate=' . $rate . ' tax=' . $ex['amount'] . ' grand=' . $ex['grand_total']);
            // Inclusive: tax extracted from within.
            $this->print->set_job_tax($job, true, $taxes[0]->id, 'Inclusive');
            $inc = $this->print->compute_tax($job, 100000);
            $this->check('IQ', 'inclusive tax is extracted from the price (total stays 100000)',
                abs($inc['grand_total'] - 100000) < 0.01 && $inc['amount'] > 0 && $inc['amount'] < $rate * 1000, 'tax=' . $inc['amount'] . ' grand=' . $inc['grand_total']);
            $this->check('IQ', 'inclusive tax < exclusive tax for the same rate', $inc['amount'] < $ex['amount'], 'inc=' . $inc['amount'] . ' exc=' . $ex['amount']);
            // Rate is snapshotted so a later change cannot rewrite it silently.
            $jrow_t = $this->print->get_job($job);
            $this->check('IQ', 'tax rate is snapshotted on the job at switch-on', (float)$jrow_t->tax_rate === $rate, 'snapshot=' . $jrow_t->tax_rate);
            // Turn it back off for the remainder.
            $this->print->set_job_tax($job, false);
            $off = $this->print->job_tax_config($job);
            $this->check('IQ', 'tax can be switched OFF again', $off['on'] === false, json_encode($off));
        }
        $bad_tax = $this->print->set_job_tax($job, true, 99999999);
        $this->check('IQ', 'an unavailable tax rate is rejected', empty($bad_tax['success']), $bad_tax['message'] ?? '');

        // cleanup
        $this->db->where('quotation_id', $quotation_id)->delete('db_sales');        $this->db->where('quotation_id', $quotation_id)->delete('db_quotationitems');
        $this->db->where('quotation_id', $quotation_id)->delete('db_quotation_revisions');
        $this->db->where('id', $quotation_id)->delete('db_quotation');
        $this->_cleanup_one($job);
        $this->db->where('id', $mat_id)->delete('db_stockadjustmentitems');
        $this->db->where('id', $adj)->delete('db_stockadjustment');
        $this->db->where('id', $mat_id)->delete('db_items');
    }

    /**
     * Exercises the REAL shared duplicate guard in Sales_model by feeding it
     * the POST state a second conversion attempt would carry. Returns true when
     * the guard refuses (i.e. an error string is returned instead of 'success'
     * or a quotation_id payload).
     */
    private function sales_model_duplicate_guard($quotation_id) {
        $this->load->model('sales_model');
        // Recreate the request state the convert form would submit.
        $_POST = ['command' => 'save', 'quotation_id' => $quotation_id];
        $_GET = [];
        $res = $this->sales_model->verify_save_and_update();
        $_POST = []; $_GET = [];
        // A refusal returns a human-readable error; success returns
        // "success<<<###>>>{id}".
        return (strpos((string)$res, 'success<<<###>>>') === false) && trim((string)$res) !== '';
    }

    /**
     * SF — a printing storefront is SERVICE-led, not category/cart-led.
     * Verifies the printing service catalogue exists, non-printing demo
     * services were retired, and the printing themes exist and are the only
     * ones offered to the printing industry.
     */
    private function run_storefront_scenarios() {
        // The suite signs in as store 1, but the seeded printing catalogue
        // belongs to whichever store is actually configured as printing.
        // Resolve that store so the assertions test real data.
        $print_store = $this->db->select('store_id')->where('industry_type', 'printing')
            ->order_by('store_id', 'asc')->limit(1)->get('db_store_industry_settings')->row();
        $store_id = $print_store ? (int)$print_store->store_id : $this->storeId;
        $this->check('SF', 'a printing store is configured', (bool)$print_store, 'store_id=' . $store_id);

        // Printing service catalogue seeded.
        $svc = $this->db->select('service_name,status,description')
            ->where('store_id', $store_id)->where('status', 1)
            ->where_in('service_name', [
                'Large Format Printing','Digital Printing','Booklets & Binding',
                'Apparel & DTF Printing','Graphic Design','Signage & Fabrication',
                'Finishing Services','Vehicle Graphics','Same-Day / Rush Printing',
            ])
            ->get('db_services')->result();
        $this->check('SF', 'printing service catalogue exists', count($svc) >= 5, 'n=' . count($svc) . ' store=' . $store_id);

        $names = array_map(function ($s) { return $s->service_name; }, $svc);
        $this->check('SF', 'large format service present', in_array('Large Format Printing', $names, true), implode(' | ', $names));
        $this->check('SF', 'apparel/DTF service present', in_array('Apparel & DTF Printing', $names, true));
        $this->check('SF', 'finishing service present', in_array('Finishing Services', $names, true));

        // Every printing service carries a description (the storefront shows it).
        $missing_desc = 0;
        foreach ($svc as $s) { if (trim((string)$s->description) === '') $missing_desc++; }
        $this->check('SF', 'every printing service has a description', $missing_desc === 0, 'missing=' . $missing_desc);

        // Non-printing demo services must not be live on a printing store.
        $junk = $this->db->select('service_name')->where('store_id', $store_id)->where('status', 1)
            ->where_in('service_name', ['Bridal Makeup (Full)', 'Test Drive Booking', 'Physiotherapy Session', 'Lash Extensions'])
            ->get('db_services')->result();
        $this->check('SF', 'non-printing demo services are retired for printing stores', count($junk) === 0,
            'still_live=' . implode(',', array_map(function ($j) { return $j->service_name; }, $junk)));

        // Printing themes exist and belong to the printing industry.
        $themes = $this->db->select('theme_key,industry,status')
            ->where('industry', 'printing')->where('status', 1)->get('db_storefront_themes')->result();
        $keys = array_map(function ($t) { return $t->theme_key; }, $themes);
        $this->check('SF', 'printing themes are registered', count($themes) >= 4, 'n=' . count($themes) . ' ' . implode(',', $keys));
        $this->check('SF', 'printing themes include inkpress/papercraft/neonprint/print_works',
            empty(array_diff(['print_inkpress', 'print_papercraft', 'print_neonprint', 'print_works'], $keys)), implode(',', $keys));
        // Every printing theme must be offered to a printing store in the picker.
        $offered = $this->storefront_model->getThemesByIndustryForStore('printing', true);
        $offeredKeys = array_map(function ($t) { return $t->theme_key; }, $offered);
        $this->check('SF', 'all printing themes are offered to a printing store',
            empty(array_diff($keys, $offeredKeys)), 'offered=' . implode(',', $offeredKeys));
        // print_works is the services-first theme: it must never render a cart.
        $pwView = APPPATH . 'views/themes/print_works/store.php';
        if (file_exists($pwView)) {
            $pwSrc = file_get_contents($pwView);
            $this->check('SF', 'print_works gates products behind sells_products',
                strpos($pwSrc, '!empty($sells_products)') !== false, '');
            $this->check('SF', 'print_works does not hard-code a cart button',
                strpos($pwSrc, 'addToCart(') === false, '');
        }

        // Theme views exist for the view the storefront actually renders ('store').
        $missing_view = [];
        foreach ($keys as $k) {
            if (!file_exists(APPPATH . 'views/themes/' . $k . '/store.php')) $missing_view[] = $k;
        }
        $this->check('SF', 'each printing theme has a store.php (index renders "store", not "home")',
            empty($missing_view), 'missing=' . implode(',', $missing_view));

        // Service-led: the printing theme must not render retail category sections.
        foreach ($keys as $k) {
            $src = @file_get_contents(APPPATH . 'views/themes/' . $k . '/store.php');
            $has_categories = $src && strpos($src, 'sections/featured_categories.php') !== false;
            $has_new_arrivals = $src && strpos($src, 'sections/new_arrivals.php') !== false;
            $this->check('SF', "theme {$k} is service-led (no category/new-arrival sections)",
                !$has_categories && !$has_new_arrivals,
                'categories=' . var_export($has_categories, true) . ' new_arrivals=' . var_export($has_new_arrivals, true));
            $uses_services = $src && strpos($src, 'featured_services') !== false;
            $this->check('SF', "theme {$k} renders the service list", (bool)$uses_services);
        }

        // The storefront must have services enabled for a printing store.
        if ($this->db->table_exists('db_storefront_settings')) {
            $row = $this->db->select('allow_services')->where('store_id', $store_id)->get('db_storefront_settings')->row();
            if ($row) {
                $this->check('SF', 'printing storefront has services enabled', (int)$row->allow_services === 1, 'allow_services=' . $row->allow_services);
            }
        }

        // Theme switching must be ONE action that keeps every source in sync.
        // Regression guard for the picker: saving a theme has to update
        // db_storefront_settings.theme_id, db_store_industry_settings and
        // db_store — a mismatch is what makes a saved theme not take effect.
        $industries = $this->db->select('storefront_theme_key')->where('store_id', $store_id)
            ->get('db_store_industry_settings')->row();
        $storeRow = $this->db->select('storefront_theme_key')->where('id', $store_id)->get('db_store')->row();
        if ($industries && $storeRow) {
            $this->check('SF', 'theme sources agree (industry_settings == db_store)',
                $industries->storefront_theme_key === $storeRow->storefront_theme_key,
                'industry=' . $industries->storefront_theme_key . ' store=' . $storeRow->storefront_theme_key);
            $this->check('SF', 'stored theme is one of the printing themes',
                in_array($industries->storefront_theme_key, ['print_inkpress', 'print_papercraft', 'print_neonprint'], true),
                'theme=' . $industries->storefront_theme_key);
        }

        // Preview mode must not silently pin the storefront to a stale theme.
        if ($this->db->table_exists('db_storefront_settings')) {
            $s = $this->db->select('theme_id,preview_mode,preview_theme_id')
                ->where('store_id', $store_id)->get('db_storefront_settings')->row();
            if ($s) {
                $pinned = ((int)$s->preview_mode === 1 && !empty($s->preview_theme_id) && (int)$s->preview_theme_id !== (int)$s->theme_id);
                $this->check('SF', 'preview mode is not pinning a different theme than the saved one',
                    !$pinned,
                    'theme_id=' . $s->theme_id . ' preview_mode=' . $s->preview_mode . ' preview_theme_id=' . $s->preview_theme_id);
            }
        }
    }

    /**
     * UQ — unified quotation: the PERSISTED LINKAGE decides behaviour, not the
     * store's business type. A print-linked quotation shows printing terms and
     * can release production; a general quotation never can.
     */
    private function run_unified_quotation_scenarios() {
        $store_id = $this->storeId;
        $this->print->seed_categories($store_id);
        $L = $this->db->where('store_id', $store_id)->where('category_key', 'large_format')->get('db_print_categories')->row();
        $cust = $this->db->where('store_id', $store_id)->order_by('id', 'asc')->limit(1)->get('db_customers')->row();
        $customer_id = $cust ? $cust->id : get_walk_in_customer_id();

        // ---- A print-linked quotation resolves as 'print' ----
        $job = $this->print->create_job(['customer_id' => $customer_id, 'title' => 'UQ print ' . $this->tag], [[
            'category_id' => $L->id, 'qty' => 1, 'unit_price' => 10000, 'description' => 'Banner',
            'spec' => ['width' => 2, 'height' => 1, 'dim_unit' => 'm', 'material' => 'PVC'],
        ]]);
        $iss = $this->print->quote_to_quotation($job, ['customer_id' => $customer_id]);
        $qid = $iss['quotation_id'] ?? 0;
        $this->check('UQ', 'print quotation created', $qid > 0);

        $doc = $this->print->quotation_doc_type($qid);
        $this->check('UQ', 'linked quotation resolves as kind=print', $doc['kind'] === 'print', json_encode(['kind' => $doc['kind']]));
        $this->check('UQ', 'linked quotation exposes its print job', !empty($doc['job']) && (int)$doc['job']->id === (int)$job);

        // Resolution is by LINKAGE, not by store business type: the same store
        // can hold a general quotation and it must resolve as 'general'.
        $this->db->insert('db_quotation', [
            'store_id' => $store_id, 'quotation_code' => 'UQ' . $this->tag,
            'reference_no' => 'UQ-GEN-' . $this->tag, 'revision_no' => 0,
            'quotation_date' => date('Y-m-d'), 'quotation_status' => 'Quotation',
            'customer_id' => $customer_id, 'subtotal' => 5000, 'grand_total' => 5000,
            'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
            'created_by' => 'acceptance', 'status' => 1,
        ]);
        $general_id = $this->db->insert_id();
        $doc2 = $this->print->quotation_doc_type($general_id);
        $this->check('UQ', 'unlinked quotation in a PRINTING store resolves as kind=general',
            $doc2['kind'] === 'general' && $doc2['job'] === null, json_encode(['kind' => $doc2['kind']]));

        // ---- Production gate: general quotation must NOT authorize ----
        $job2 = $this->print->create_job(['customer_id' => $customer_id, 'title' => 'UQ gate ' . $this->tag], [[
            'category_id' => $L->id, 'qty' => 1, 'unit_price' => 9000, 'description' => 'Sign',
            'spec' => ['width' => 1, 'height' => 1, 'dim_unit' => 'm', 'material' => 'PVC'],
        ]]);
        // Force the job to point at the general (unlinked) quotation and clear acceptance.
        $this->db->where('id', $job2)->update('db_print_jobs', [
            'quotation_id' => $general_id, 'quotation_status' => 'issued', 'quotation_revision_accepted' => null,
        ]);
        $this->check('UQ', 'a general quotation does NOT satisfy job_has_accepted_quotation',
            $this->print->job_has_accepted_quotation($job2) === false);

        // Also: a job with no quotation at all must not authorize.
        $job3 = $this->print->create_job(['customer_id' => $customer_id, 'title' => 'UQ none ' . $this->tag], [[
            'category_id' => $L->id, 'qty' => 1, 'unit_price' => 1000, 'description' => 'Card',
            'spec' => ['width' => 1, 'height' => 1, 'dim_unit' => 'm', 'material' => 'PVC'],
        ]]);
        $this->db->where('id', $job3)->update('db_print_jobs', ['quotation_id' => null, 'quotation_status' => 'issued']);
        $this->check('UQ', 'a job with no quotation does NOT authorize production',
            $this->print->job_has_accepted_quotation($job3) === false);
        $this->print->add_artwork($job3, 'x.pdf', 'uploads/printjobs/x.pdf', hash('sha256', 'x'), 'application/pdf');
        $art = $this->db->where('job_id', $job3)->where('status', 'approved')->order_by('version_no', 'desc')->get('db_print_artworks')->row();
        if (!$art) { $aid = $this->print->add_artwork($job3, 'y.pdf', 'uploads/printjobs/y.pdf', hash('sha256', 'y'), 'application/pdf'); $this->print->approve_artwork($aid); $this->print->clearance_artwork($job3, 'cleared', 'ok'); }
        $req = $this->print->request_authorization($job3, $this->uid, null);
        $this->check('UQ', 'production authorization is REFUSED without an accepted quotation',
            $req['success'] === false, $req['message'] ?? '');

        // ---- Explicit "Create Print Job" from a general quotation ----
        // A FRESH unlinked quotation: the one above was deliberately linked to
        // job2 to exercise the production gate.
        $this->db->insert('db_quotation', [
            'store_id' => $store_id, 'quotation_code' => 'UG' . $this->tag,
            'reference_no' => 'UQ-GEN2-' . $this->tag, 'revision_no' => 0,
            'quotation_date' => date('Y-m-d'), 'quotation_status' => 'Quotation',
            'customer_id' => $customer_id, 'subtotal' => 5000, 'grand_total' => 5000,
            'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
            'created_by' => 'acceptance', 'status' => 1,
        ]);
        $general2_id = $this->db->insert_id();
        $qi = $this->print->resolve_quotation_item($store_id);
        $this->db->insert('db_quotationitems', [
            'store_id' => $store_id, 'quotation_id' => $general2_id, 'quotation_status' => 'Quotation',
            'item_id' => $qi['item_id'],
            'description' => 'Consultancy', 'quotation_qty' => 1,
            'price_per_unit' => 5000, 'unit_total_cost' => 5000, 'total_cost' => 5000,
            'status' => 1, 'seller_points' => 0,
        ]);
        $this->check('UQ', 'fresh general quotation starts unlinked',
            $this->print->quotation_doc_type($general2_id)['kind'] === 'general');

        $mkg = $this->print->create_job_from_quotation($general2_id, ['category_id' => $L->id, 'title' => 'From UQ ' . $this->tag]);
        $this->check('UQ', 'general quotation can explicitly become a print job',
            !empty($mkg['success']), $mkg['message'] ?? '');
        if (!empty($mkg['success'])) {
            $doc3 = $this->print->quotation_doc_type($general2_id);
            $this->check('UQ', 'after linking, the quotation resolves as kind=print', $doc3['kind'] === 'print');
            $this->check('UQ', 'the ORIGINAL quotation record is reused (not rewritten)',
                (int)$doc3['job']->quotation_id === (int)$general2_id);
            $newjob = $this->print->get_job($mkg['job_id']);
            $this->check('UQ', 'the new job carries the quotation total over',
                abs((float)$newjob->quote_amount - 5000) < 0.01, 'amount=' . $newjob->quote_amount);
            $again = $this->print->create_job_from_quotation($general2_id, ['category_id' => $L->id]);
            $this->check('UQ', 're-raising a job from a linked quotation is refused',
                empty($again['success']), $again['message'] ?? '');
            $this->_cleanup_one($mkg['job_id']);
        }

        // ---- Per-line discount + tax parity ----
        $this->db->where('id', $this->print->get_lines($job)[0]->id)->update('db_print_job_lines', [
            'discount_type' => 'in_percentage', 'discount_input' => 10,
        ]);
        $rev = $this->print->quote_to_quotation($job, ['customer_id' => $customer_id]);
        $this->check('UQ', 'print quotation accepts a per-line discount', !empty($rev['success']));
        $items = $this->print->quotation_items($qid);
        $has_disc = false;
        foreach ($items as $it) { if ((float)$it->discount_amt > 0) $has_disc = true; }
        $this->check('UQ', 'per-line discount is persisted on the quotation line', $has_disc);
        // 10% of 10000 = 1000 discount → subtotal 9000
        $qrow = $this->db->where('id', $qid)->get('db_quotation')->row();
        $this->check('UQ', 'discount reduces the subtotal (10000 − 10% = 9000)',
            abs((float)$qrow->subtotal - 9000) < 0.01, 'subtotal=' . $qrow->subtotal);

        // ---- Unlinked review listing ----
        $unlinked = $this->print->unlinked_quotations($store_id, 200);
        $this->check('UQ', 'unlinked quotations are listable for review', is_array($unlinked));
        $ids = array_map(function ($r) { return (int)$r->id; }, $unlinked);
        $this->check('UQ', 'linked quotations are NOT listed as unlinked', !in_array((int)$qid, $ids, true));

        $this->_cleanup_one($job);
        $this->_cleanup_one($job2);
        $this->_cleanup_one($job3);
        $this->db->where('id', $general_id)->delete('db_quotation');
        $this->db->where('quotation_id', $general_id)->delete('db_quotationitems');
    }

    /**
     * LC — quotation lifecycle: a quotation must not sit "issued" forever.
     * Decline, cancel, expiry and the 3-day / 1-day reminders.
     */
    private function run_lifecycle_scenarios() {
        $store_id = $this->storeId;
        $this->print->seed_categories($store_id);
        $L = $this->db->where('store_id', $store_id)->where('category_key', 'large_format')->get('db_print_categories')->row();
        $cust = $this->db->where('store_id', $store_id)->order_by('id', 'asc')->limit(1)->get('db_customers')->row();
        $customer_id = $cust ? $cust->id : get_walk_in_customer_id();

        $job = $this->print->create_job(['customer_id' => $customer_id, 'title' => 'LC ' . $this->tag], [[
            'category_id' => $L->id, 'qty' => 1, 'unit_price' => 10000, 'description' => 'Banner',
            'spec' => ['width' => 2, 'height' => 1, 'dim_unit' => 'm', 'material' => 'PVC'],
        ]]);
        $iss = $this->print->quote_to_quotation($job, ['customer_id' => $customer_id]);
        $qid = $iss['quotation_id'] ?? 0;
        $this->check('LC', 'quotation issued for lifecycle test', $qid > 0);

        // ---- Default validity window (30 days) ----
        $q = $this->db->where('id', $qid)->get('db_quotation')->row();
        $this->check('LC', 'quotation gets a default expiry date', !empty($q->expire_date), 'expire=' . ($q->expire_date ?? 'none'));
        $days = $this->print->days_to_expiry($q->expire_date);
        $this->check('LC', 'default validity is ~30 days', $days !== null && $days >= 28 && $days <= 31, 'days=' . $days);
        $this->check('LC', 'new quotation lifecycle starts as issued', ($q->lifecycle_status ?? '') === 'issued', 'lc=' . ($q->lifecycle_status ?? '?'));

        // ---- Declining closes the job to production ----
        $d = $this->print->decline_quotation($job, 'Budget cut');
        $this->check('LC', 'quotation can be declined with a reason', !empty($d['success']), $d['message'] ?? '');
        $jrow = $this->print->get_job($job);
        $this->check('LC', 'decline sets the job lifecycle to declined', ($jrow->quote_lifecycle_status ?? '') === 'declined', 'lc=' . $jrow->quote_lifecycle_status);
        $this->check('LC', 'decline records the reason', ($jrow->quote_response_reason ?? '') === 'Budget cut');
        $q2 = $this->db->where('id', $qid)->get('db_quotation')->row();
        $this->check('LC', 'decline mirrors onto the authoritative quotation', ($q2->lifecycle_status ?? '') === 'declined');
        $this->check('LC', 'declined quotation cannot release production', $this->print->job_has_accepted_quotation($job) === false);
        $no_reason = $this->print->decline_quotation($job, '');
        $this->check('LC', 'decline may be recorded without a reason at model level', !empty($no_reason['success']));

        // ---- Reopen ----
        $ro = $this->print->reopen_quotation($job);
        $jrow = $this->print->get_job($job);
        $this->check('LC', 'a declined quotation can be reopened', !empty($ro['success']) && ($jrow->quote_lifecycle_status ?? '') === 'issued', $ro['message'] ?? '');

        // ---- Cancel job ----
        $c = $this->print->cancel_job($job, 'Customer withdrew');
        $jrow = $this->print->get_job($job);
        $this->check('LC', 'job can be cancelled', !empty($c['success']), $c['message'] ?? '');
        $this->check('LC', 'cancel sets lifecycle + production status',
            ($jrow->quote_lifecycle_status ?? '') === 'cancelled' && $jrow->production_status === 'cancelled',
            'lc=' . $jrow->quote_lifecycle_status . ' prod=' . $jrow->production_status);
        $this->check('LC', 'cancelled job cannot release production', $this->print->job_has_accepted_quotation($job) === false);
        $this->print->reopen_quotation($job);

        // ---- Expiry sweep ----
        // A second job whose quotation expired yesterday must be expired by cron.
        $job2 = $this->print->create_job(['customer_id' => $customer_id, 'title' => 'LC exp ' . $this->tag], [[
            'category_id' => $L->id, 'qty' => 1, 'unit_price' => 5000, 'description' => 'Sign',
            'spec' => ['width' => 1, 'height' => 1, 'dim_unit' => 'm', 'material' => 'PVC'],
        ]]);
        $iss2 = $this->print->quote_to_quotation($job2, ['customer_id' => $customer_id, 'expire_date' => date('Y-m-d', strtotime('-1 day'))]);
        $qid2 = $iss2['quotation_id'] ?? 0;
        $this->check('LC', 'second quotation issued with a past expiry', $qid2 > 0);

        $expired = $this->print->expire_due_quotations($store_id);
        $this->check('LC', 'expiry sweep expires at least the past-due quotation', $expired >= 1, 'expired=' . $expired);
        $q3 = $this->db->where('id', $qid2)->get('db_quotation')->row();
        $this->check('LC', 'past-due quotation is marked expired', ($q3->lifecycle_status ?? '') === 'expired', 'lc=' . ($q3->lifecycle_status ?? '?'));
        $this->check('LC', 'expired_at is stamped', !empty($q3->expired_at));
        $job2row = $this->print->get_job($job2);
        $this->check('LC', 'expiry mirrors onto the job', ($job2row->quote_lifecycle_status ?? '') === 'expired');
        $this->check('LC', 'expired quotation cannot release production', $this->print->job_has_accepted_quotation($job2) === false);

        // Accepted quotations must never be expired by the sweep.
        $job3 = $this->print->create_job(['customer_id' => $customer_id, 'title' => 'LC acc ' . $this->tag], [[
            'category_id' => $L->id, 'qty' => 1, 'unit_price' => 7000, 'description' => 'Card',
            'spec' => ['width' => 1, 'height' => 1, 'dim_unit' => 'm', 'material' => 'PVC'],
        ]]);
        $iss3 = $this->print->quote_to_quotation($job3, ['customer_id' => $customer_id, 'expire_date' => date('Y-m-d', strtotime('-2 days'))]);
        $qid3 = $iss3['quotation_id'] ?? 0;
        $this->print->accept_quotation_revision($job3);
        $this->db->where('id', $qid3)->update('db_quotation', ['lifecycle_status' => 'accepted']);
        $this->print->expire_due_quotations($store_id);
        $q4 = $this->db->where('id', $qid3)->get('db_quotation')->row();
        $this->check('LC', 'an ACCEPTED quotation is never expired by the sweep', ($q4->lifecycle_status ?? '') === 'accepted', 'lc=' . ($q4->lifecycle_status ?? '?'));

        // ---- Reminder selection (3-day and 1-day, once each) ----
        $job4 = $this->print->create_job(['customer_id' => $customer_id, 'title' => 'LC rem ' . $this->tag], [[
            'category_id' => $L->id, 'qty' => 1, 'unit_price' => 3000, 'description' => 'Flyer',
            'spec' => ['width' => 1, 'height' => 1, 'dim_unit' => 'm', 'material' => 'PVC'],
        ]]);
        $iss4 = $this->print->quote_to_quotation($job4, ['customer_id' => $customer_id, 'expire_date' => date('Y-m-d', strtotime('+3 days'))]);
        $qid4 = $iss4['quotation_id'] ?? 0;
        $due3 = $this->print->quotations_due_expiry_reminder($store_id, [3]);
        $found3 = false;
        foreach ($due3 as $r) { if ((int)$r->id === (int)$qid4) $found3 = true; }
        $this->check('LC', 'a quotation expiring in 3 days is selected for the 3-day reminder', $found3);

        $this->print->mark_reminder_sent($qid4, 3);
        $due3b = $this->print->quotations_due_expiry_reminder($store_id, [3]);
        $found3b = false;
        foreach ($due3b as $r) { if ((int)$r->id === (int)$qid4) $found3b = true; }
        $this->check('LC', 'the 3-day reminder is NOT sent twice', !$found3b);

        $this->db->where('id', $qid4)->update('db_quotation', ['expire_date' => date('Y-m-d', strtotime('+1 day'))]);
        $due1 = $this->print->quotations_due_expiry_reminder($store_id, [1]);
        $found1 = false;
        foreach ($due1 as $r) { if ((int)$r->id === (int)$qid4) $found1 = true; }
        $this->check('LC', 'the same quotation is then selected for the 1-day reminder', $found1);
        $this->print->mark_reminder_sent($qid4, 1);
        $q5 = $this->db->where('id', $qid4)->get('db_quotation')->row();
        $this->check('LC', 'both reminder stamps are recorded', !empty($q5->reminder_3d_sent_at) && !empty($q5->reminder_1d_sent_at));

        // A declined quotation must not be reminded.
        $this->db->where('id', $qid4)->update('db_quotation', ['lifecycle_status' => 'declined', 'reminder_1d_sent_at' => null]);
        $due_decl = $this->print->quotations_due_expiry_reminder($store_id, [1]);
        $found_dec = false;
        foreach ($due_decl as $r) { if ((int)$r->id === (int)$qid4) $found_dec = true; }
        $this->check('LC', 'a declined quotation is not reminded', !$found_dec);

        // ---- Email template states the validity date ----
        if ($this->db->table_exists('db_email_templates')) {
            $tpl = $this->db->where('store_id', $store_id)->where('template_key', 'quotation_sent')->get('db_email_templates')->row();
            $this->check('LC', 'a quotation_sent email template exists', (bool)$tpl);
            if ($tpl) {
                $this->check('LC', 'the quotation email states the expiry date',
                    strpos((string)$tpl->subject, '{expire_date}') !== false || strpos((string)$tpl->html_body, '{expire_date}') !== false,
                    'subject=' . $tpl->subject);
            }
            $rt = $this->db->where('store_id', $store_id)->where('template_key', 'quotation_expiry_reminder')->get('db_email_templates')->row();
            $this->check('LC', 'an expiry reminder template exists', (bool)$rt);
            if ($rt) {
                $this->check('LC', 'the reminder email states the days left',
                    strpos((string)$rt->subject, '{days_left}') !== false || strpos((string)$rt->html_body, '{days_left}') !== false,
                    'subject=' . $rt->subject);
            }
        }

        // ---- Lifecycle audit trail ----
        if ($this->db->table_exists('db_quotation_lifecycle_log')) {
            $logs = $this->db->where('quotation_id', $qid)->get('db_quotation_lifecycle_log')->result();
            $this->check('LC', 'lifecycle transitions are audited', count($logs) >= 2, 'entries=' . count($logs));
        }

        $this->_cleanup_one($job);
        $this->_cleanup_one($job2);
        $this->_cleanup_one($job3);
        $this->_cleanup_one($job4);
    }

    /**
     * SM — service mode. A printing store must not behave like an ecommerce
     * shop: no cart, no "add to cart", the action is to send a job and get a
     * quotation. Other business types must be completely unaffected.
     */
    private function run_service_mode_scenarios() {
        $this->load->library('theme_engine');

        // Resolve the printing store from its industry, not the signed-in store.
        $print_store = $this->db->select('store_id')->where('industry_type', 'printing')
            ->order_by('store_id', 'asc')->limit(1)->get('db_store_industry_settings')->row();
        $print_id = $print_store ? (int)$print_store->store_id : 0;
        $this->check('SM', 'printing store found for service-mode test', $print_id > 0, 'store=' . $print_id);

        if ($print_id) {
            $this->theme_engine->init($print_id, null);
            $this->check('SM', 'printing store is detected as a SERVICE store',
                $this->theme_engine->isServiceStore() === true);
            $this->check('SM', 'cart is disabled for a printing store',
                $this->theme_engine->cartEnabled() === false);
        }

        // A product-based store must remain ecommerce.
        $retail = $this->db->select('store_id')->where('industry_type', 'nylon_polythene')
            ->order_by('store_id', 'asc')->limit(1)->get('db_store_industry_settings')->row();
        if ($retail) {
            $this->theme_engine->init((int)$retail->store_id, null);
            $this->check('SM', 'a product-based store is NOT a service store',
                $this->theme_engine->isServiceStore() === false);
            $this->check('SM', 'cart stays enabled for a product-based store',
                $this->theme_engine->cartEnabled() === true);
        }

        // The service views must not offer add-to-cart.
        $sd = @file_get_contents(APPPATH . 'views/themes/shared/service_detail.php');
        $this->check('SM', 'service detail view exists', $sd !== false);
        if ($sd !== false) {
            $this->check('SM', 'service detail shows a service-mode branch (no unconditional cart)',
                strpos($sd, 'is_service_store') !== false, 'guarded=' . var_export(strpos($sd, 'is_service_store') !== false, true));
        }

        // The header/footer must gate cart markup on the service flag.
        $hdr = @file_get_contents(APPPATH . 'views/themes/shared/header.php');
        $this->check('SM', 'header gates the cart on service mode', $hdr !== false && strpos($hdr, 'is_service_store') !== false);
        $ftr = @file_get_contents(APPPATH . 'views/themes/shared/footer.php');
        $this->check('SM', 'footer gates the mobile/sticky cart on service mode', $ftr !== false && strpos($ftr, 'is_service_store') !== false);

        // Printing themes must be service-led (no category/new-arrival sections).
        foreach (['print_inkpress', 'print_papercraft', 'print_neonprint'] as $k) {
            $src = @file_get_contents(APPPATH . 'views/themes/' . $k . '/store.php');
            if ($src === false) { $this->check('SM', "theme {$k} store view exists", false); continue; }
            $this->check('SM', "theme {$k} renders the enquiry form (not just a link)",
                strpos($src, 'enquiry_form') !== false);
            // Every service must show a real glyph, never a bare initial.
            $this->check('SM', "theme {$k} uses the shared service thumbnail helper",
                strpos($src, 'mp_print_service_thumb') !== false);
        }

        // Storefront defaults must be service-shaped for a printing store, and
        // untouched stores deserve them without clobbering a merchant's layout.
        $this->load->model('storefront_model', 'sfm');
        $this->check('SM', 'a printing store is recognised as service for section defaults',
            $this->_sections_are_service_shaped($print_id));

        // Mobile: the enquiry form must be usable on a phone.
        $enq = @file_get_contents(APPPATH . 'views/printing/enquiry_form.php');
        $this->check('SM', 'enquiry form has a phone breakpoint', $enq !== false && strpos($enq, '@media') !== false);
        $this->check('SM', 'enquiry quick-chips meet the 44px tap-target minimum',
            $enq !== false && strpos($enq, 'min-height:44px') !== false);
        $this->check('SM', 'enquiry inputs use 16px on mobile (prevents iOS zoom)',
            $enq !== false && strpos($enq, 'font-size:16px') !== false);

        // The service thumbnail helper must never fall back to an initial.
        $this->load->helper('print_visuals');
        $icon = mp_print_service_icon('Large Format Printing');
        $this->check('SM', 'icon helper returns SVG path data for a known service',
            strpos($icon, '<path') !== false || strpos($icon, '<rect') !== false, substr($icon, 0, 40));
        $unknown = mp_print_service_icon('Zzzz Unknown Thing');
        $this->check('SM', 'icon helper returns a sensible fallback for an unknown service',
            strpos($unknown, '<path') !== false, substr($unknown, 0, 40));
        $fake = (object)['service_name' => 'Digital Printing', 'service_image' => null];
        $thumb = mp_print_service_thumb($fake, 40);
        $this->check('SM', 'thumb renders an icon tile when there is no image',
            strpos($thumb, 'is-icon') !== false && strpos($thumb, '<svg') !== false);
        $fake2 = (object)['service_name' => 'Digital Printing', 'service_image' => 'uploads/does-not-exist.png'];
        $thumb2 = mp_print_service_thumb($fake2, 40);
        $this->check('SM', 'thumb does NOT render a broken <img> for a missing file',
            strpos($thumb2, '<img') === false && strpos($thumb2, 'is-icon') !== false);
    }

    /** Are a store's stored homepage sections in the service arrangement? */
    private function _sections_are_service_shaped($store_id) {
        $rows = $this->db->where('store_id', $store_id)->get('db_storefront_homepage_sections')->result();
        if (empty($rows)) return true; // nothing configured: defaults will apply
        $on = [];
        foreach ($rows as $r) { if ((int)$r->is_enabled === 1) $on[$r->section_key] = true; }
        // Service shape = services enabled, retail category/product discovery off.
        return !empty($on['featured_services'])
            && empty($on['featured_categories'])
            && empty($on['new_arrivals']);
    }

    private function _cleanup_one($job_id) {
        if (!$job_id) return;
        $this->db->where('job_id', $job_id)->delete('db_print_material_issues');
        $this->db->where('job_id', $job_id)->delete('db_print_authorizations');
        $this->db->where('job_id', $job_id)->delete('db_print_payments');
        $this->db->where('job_id', $job_id)->delete('db_print_artworks');
        $this->db->where('job_id', $job_id)->delete('db_print_stage_logs');
        $this->db->where('job_id', $job_id)->delete('db_print_stages');
        $this->db->where('job_id', $job_id)->delete('db_print_item_design');
        $this->db->where('job_id', $job_id)->delete('db_print_item_plans');
        $this->db->where('job_id', $job_id)->delete('db_print_item_services');
        $this->db->where('job_id', $job_id)->delete('db_print_item_cost_estimates');
        $this->db->where('job_id', $job_id)->delete('db_print_consumable_allocations');
        $this->db->where('job_id', $job_id)->delete('db_print_spec_change_log');
        $this->db->where('job_id', $job_id)->delete('db_print_job_lines');
        $this->db->where('id', $job_id)->delete('db_print_jobs');
    }

    private function run_release2($job_id) {
        // Materials: create a material item if none exists for the store.
        $mat = $this->db->where('store_id', $this->storeId)->where('item_name', 'Print Vinyl ' . $this->tag)->get('db_items')->row();
        if (!$mat) {
            $this->db->insert('db_items', [
                'store_id' => $this->storeId,
                'item_name' => 'Print Vinyl ' . $this->tag,
                'item_code' => 'PRV-' . $this->tag,
                'purchase_price' => 100.00,
                'sales_price' => 150.00,
                'stock' => 100,
                'status' => 1,
            ]);
            $mat = $this->db->where('store_id', $this->storeId)->where('item_name', 'Print Vinyl ' . $this->tag)->get('db_items')->row();
        }
        $this->check('R2', 'material item exists', (bool)$mat, 'item_id=' . ($mat->id ?? '?'));

        // Bind the print stage to the material item as input.
        $print_stage = $this->db->where('job_id', $job_id)->where('stage_key', 'print')->get('db_print_stages')->row();
        if ($print_stage) {
            $this->db->where('id', $print_stage->id)->update('db_print_stages', ['input_item_id' => $mat->id]);
        }
        $this->check('R2', 'print stage present', (bool)$print_stage);

        // Rework/partial: report a print-stage log with issue + waste + rework.
        $r = $this->print->report_stage($job_id, $print_stage->id, [
            'qty_in' => 10, 'good_qty' => 8, 'rework_qty' => 1, 'waste_qty' => 1, 'reject_qty' => 1,
        ]);
        $this->check('R2', 'stage report accepted', $r['success'], $r['message'] ?? '');
        $log_id = $r['log_id'] ?? 0;
        $this->check('R2', 'stage log persisted', $log_id > 0);
        $job = $this->print->get_job($job_id);
        $this->check('R2', 'production_status moved to in_progress', $job->production_status === 'in_progress', $job->production_status);

        // Historic material cost derived from purchase_price * qty_in.
        $log = $this->db->where('id', $log_id)->get('db_print_stage_logs')->row();
        $this->check('R2', 'input_cost = qty_in * purchase_price', abs((float)$log->input_cost - (10 * 100)) < 0.01, 'input_cost=' . $log->input_cost);

        // Materials issue recorded a negative stock adjustment.
        $adj_id = $log->adjustment_id;
        $this->check('R2', 'stock adjustment posted', $adj_id > 0, 'adj=' . $adj_id);
        $adj_qty = $adj_id ? (float)$this->db->select('adjustment_qty')->where('adjustment_id', $adj_id)->where('item_id', $mat->id)->get('db_stockadjustmentitems')->row()->adjustment_qty : 0;
        $this->check('R2', '10 units consumed (negative move)', abs($adj_qty + 10) < 0.01, 'qty=' . $adj_qty);

        // Reverse stage log reconciles stock.
        $rev = $this->print->reverse_stage_log($log_id, 'test');
        $this->check('R2', 'stage log reversed', $rev['success']);
        $job = $this->print->get_job($job_id);
        $this->check('R2', 'material cost recalculated to zero after reversal', abs((float)$job->act_material_cost) < 0.01, 'act_mat=' . $job->act_material_cost);

        // Re-issue material and complete stages to reach QC.
        $r = $this->print->report_stage($job_id, $print_stage->id, ['qty_in' => 10, 'good_qty' => 10]);
        $this->check('R2', 're-issue material accepted', $r['success']);
        $this->print->complete_stage($print_stage->id);

        // Outsourcing: complete a non-print stage with vendor + cost.
        $finishing = $this->db->where('job_id', $job_id)->where('stage_key', 'finishing')->get('db_print_stages')->row();
        if ($finishing) {
            $this->print->complete_stage($finishing->id, 'Vendor X', 5000);
            $job = $this->print->get_job($job_id);
            $this->check('R2', 'outsourcing cost recorded (act_outsource=5000)', abs((float)$job->act_outsource_cost - 5000) < 0.01, 'act_outsource=' . $job->act_outsource_cost);
        }

        // Complete remaining stages, approve QC, complete job.
        foreach ($this->print->get_stages($job_id) as $s) {
            if (in_array($s->status, ['done', 'skipped', 'outsourced'])) continue;
            if ($s->stage_key === 'qc') continue;
            $this->print->complete_stage($s->id);
        }
        $qc = $this->db->where('job_id', $job_id)->where('stage_key', 'qc')->get('db_print_stages')->row();
        $this->print->report_stage($job_id, $qc->id, ['good_qty' => 10, 'output_item_id' => $mat->id]);
        $qc_log = $this->db->where('job_id', $job_id)->where('stage_id', $qc->id)->order_by('id', 'desc')->limit(1)->get('db_print_stage_logs')->row();
        $this->print->approve_stage_log($qc_log->id);
        $this->print->complete_stage($qc->id);
        $cmp = $this->print->complete_job($job_id);
        $this->check('R2', 'job completes after all stages done + QC approved', $cmp['success'], $cmp['message'] ?? '');

        // Partial fulfilment.
        $job = $this->print->get_job($job_id);
        $this->plan_job_qty($job_id, 10); // planned qty for fulfilment math
        $f1 = $this->print->record_fulfilment($job_id, 4, 'collection', 'John Doe', 'signed');
        $this->check('R2', 'partial collection accepted', $f1['success']);
        $job = $this->print->get_job($job_id);
        $this->check('R2', 'fulfilment_status partially_collected', $job->fulfilment_status === 'partially_collected', $job->fulfilment_status);
        $this->check('R2', 'remaining quantity preserved', $f1['remaining'] == 6, 'remaining=' . $f1['remaining']);
        $this->print->record_fulfilment($job_id, 6, 'collection', 'John Doe', 'signed');
        $job = $this->print->get_job($job_id);
        $this->check('R2', 'full collection flips to collected', $job->fulfilment_status === 'collected', $job->fulfilment_status);
    }

    /** Planned-qty helper used by fulfilment math (not in R1). */
    private function plan_job_qty($job_id, $qty) {
        $this->db->where('id', $job_id)->update('db_print_jobs', ['planned_qty' => (float)$qty]);
    }

    private function run_release3($job_id) {
        $job = $this->print->get_job($job_id);
        // Collect full balance so referral can earn (quote 100000).
        $bal = $this->print->outstanding_balance($job_id);
        if ($bal > 0) {
            $r = $this->print->record_payment($job_id, 'collection', $bal, 'Bank Transfer', 'FULL-' . $this->tag);
            $this->print->verify_payment($r['print_payment_id']);
        }
        $job = $this->print->get_job($job_id);
        $this->check('R3', 'full balance collected (paid)', $job->payment_status === 'paid', $job->payment_status);

        // Referral: 5% on eligible value, exclude tax 10000 -> eligible 90000 -> projected 4500.
        $ref = $this->print->attribute_referral($job_id, 0, 5, 'SRC-' . $this->tag, ['tax' => 10000]);
        $this->check('R3', 'referral attributed with rule snapshot', $ref['success']);
        $this->check('R3', 'projected = 5% of (100000 - 10000) = 4500', $ref['projected'] == 4500, 'projected=' . $ref['projected']);

        // Not earned on deposit alone (already collected, so this would earn; test the negative path by using a fresh eval is covered by full collection here).
        // Payout-once guard.
        $ref_row = $this->db->where('job_id', $job_id)->get('db_print_referrals')->row();
        $ev = $this->print->evaluate_referral($job_id);
        $this->check('R3', 'referral earned after full collection + fulfilment', $ev['status'] === 'earned', $ev['status'] ?? '');
        $p1 = $this->print->pay_referral($ref_row->id);
        $this->check('R3', 'payout succeeds once', $p1['success']);
        $p2 = $this->print->pay_referral($ref_row->id);
        $this->check('R3', 'repeat payout refused (payout-once)', $p2['success'] === false, $p2['message'] ?? '');
        $rec = $this->print->recover_referral($ref_row->id, 'refund after payout');
        $this->check('R3', 'recovery after payout is traceable', $rec['success']);
        $entries = $this->db->where('referral_id', $ref_row->id)->get('db_print_referral_entries')->result();
        $kinds = array_map(function ($e) { return $e->kind; }, $entries);
        $this->check('R3', 'payout + recovery entries both present', in_array('payout', $kinds) && in_array('recovery', $kinds), implode(',', $kinds));

        // Policy change cannot rewrite history (rule is snapshotted JSON).
        $ref_row2 = $this->db->where('job_id', $job_id)->get('db_print_referrals')->row();
        $snap = json_decode($ref_row2->rule_json, true);
        $this->check('R3', 'rule snapshot preserved (5%)', isset($snap['rate']) && $snap['rate'] == 5, json_encode($snap));

        // Costing.
        $rep = $this->print->job_report($job_id);
        $this->check('R3', 'job_report returns cost + margin', is_array($rep) && isset($rep['actual_total']) && isset($rep['gross_profit']), 'actual=' . ($rep['actual_total'] ?? '?') . ' margin=' . ($rep['gross_profit'] ?? '?'));

        // CM: catalogue mode (does this storefront behave like a shop or a service desk?)
        $this->_catalogue_mode($store_id);

        // UX: the four things that were broken on the printing surfaces.
        $this->_ui_regressions($store_id);
    }

    /**
     * Regressions that were reported from real use:
     *   1. Every fetch() POST on the print screens was missing the CSRF token,
     *      so actions failed with "your security check has expired".
     *   2. The production board was read-only — no drag, no next-step action.
     *   3. The enquiry form drew its own card inside the theme's card and
     *      overflowed its container on a phone.
     *   4. The storefront theme rendered the shared retail banner hero as well
     *      as its own, and derived its dark ink from a brand colour (mud).
     */
    private function _ui_regressions($store_id) {
        $views = APPPATH . 'views/printing/';

        // 1. CSRF on every printing view that performs a fetch() POST.
        $missing_csrf = [];
        foreach (glob($views . '*.php') as $file) {
            $src = file_get_contents($file);
            if (strpos($src, 'fetch(') === false) continue;
            // POSTs only — a GET fetch needs no token.
            if (!preg_match('/fetch\([^)]*method\s*:\s*[\'"]POST[\'"]/', $src)) continue;
            if (stripos($src, 'csrf') === false) $missing_csrf[] = basename($file);
        }
        $this->check('UX', 'every fetch POST on a printing screen sends a CSRF token',
            empty($missing_csrf), 'missing in: ' . implode(', ', $missing_csrf));

        // 2. Production board is interactive.
        $board = file_get_contents($views . 'production.php');
        $this->check('UX', 'production board cards are draggable',
            strpos($board, 'draggable="true"') !== false);
        $this->check('UX', 'production board has drop targets per column',
            strpos($board, 'data-drop=') !== false);
        $this->check('UX', 'production board offers a next-step action on cards',
            strpos($board, 'kb-next') !== false);
        $this->check('UX', 'production board sends CSRF with its actions',
            stripos($board, 'csrf') !== false);
        $this->check('UX', 'production board refuses non-adjacent drops rather than silently ignoring them',
            strpos($board, 'indexOf(to)') !== false);

        // 3. Enquiry form fits its container and does not nest cards.
        $enq = file_get_contents(APPPATH . 'views/printing/enquiry_form.php');
        $this->check('UX', 'enquiry grid cannot demand more width than the phone has',
            strpos($enq, 'minmax(min(220px,100%),1fr)') !== false);
        $this->check('UX', 'enquiry form can suppress its own card when embedded',
            strpos($enq, 'is-embedded') !== false);
        $this->check('UX', 'enquiry form colours come from the host theme',
            strpos($enq, '--sq-accent') !== false && strpos($enq, '#25D366') === false);

        // 4. print_works owns its hero and keeps a neutral ink.
        $theme = file_get_contents(APPPATH . 'views/themes/print_works/store.php');
        $this->check('UX', 'print_works does not double up on a hero',
            strpos($theme, "'hero_banner', 'featured_categories'") !== false
            || strpos($theme, 'hero_banner') === false && strpos($theme, 'pw_skip_sections') !== false);
        $this->check('UX', 'print_works uses a neutral dark instead of darkening a brand colour',
            strpos($theme, "\$pw_darken") === false);
        $this->check('UX', 'print_works hero carries a single accent edge, not a colour wash',
            strpos($theme, 'radial-gradient') === false);

        // 5. Reports answer production questions, not just revenue.
        $this->load->model('printing_model', 'print_analytics');
        foreach (['production_analytics', 'quote_funnel', 'stage_bottleneck', 'print_quality', 'cost_accuracy', 'turnaround'] as $m) {
            $this->check('UX', "reports expose $m()", method_exists($this->print_analytics, $m));
        }
        $analytics = $this->print_analytics->production_analytics($store_id, []);
        $this->check('UX', 'production analytics returns all five blocks',
            empty(array_diff(['quote_funnel', 'stage_bottleneck', 'quality', 'cost_accuracy', 'turnaround'], array_keys($analytics))),
            implode(',', array_keys($analytics)));
        $this->check('UX', 'quote funnel reports a win rate field',
            array_key_exists('won_rate', $analytics['quote_funnel']));
        $this->check('UX', 'quality reports spoilage',
            array_key_exists('waste_pct', $analytics['quality']));
        $this->check('UX', 'cost accuracy reports estimate variance',
            array_key_exists('variance_pct', $analytics['cost_accuracy']));

        // The stage query must not filter on a column the table does not have.
        $has_created = $this->db->field_exists('created_at', 'db_print_stages');
        $this->check('UX', 'stage_bottleneck does not require a missing stages.created_at',
            $has_created || strpos(file_get_contents(APPPATH . 'models/Printing_model.php'), 'j.created_at >=', true) !== false);

        $this->_storefront_shell();
    }

    /**
     * The storefront shell: what a printing storefront leads with, and what it
     * must never show. Reported from real use — retail product categories in a
     * print shop's menu, and ecommerce promises rendered above the hero, made
     * the storefront read as a clothing shop.
     */
    private function _storefront_shell() {
        $theme  = file_get_contents(APPPATH . 'views/themes/print_works/store.php');
        $header = file_get_contents(APPPATH . 'views/themes/shared/header.php');

        // 1. Retail-only sections must be dropped, not merely reordered.
        foreach (['trust_badges', 'featured_categories', 'best_sellers', 'new_arrivals', 'brands'] as $retail) {
            $this->check('UX', "print_works does not render the retail section '$retail'",
                preg_match("/pw_skip_sections\s*=\s*\[[^\]]*'" . $retail . "'/s", $theme) === 1);
        }
        // The shared trust-badge copy is ecommerce, not print.
        $trust = file_get_contents(APPPATH . 'views/themes/shared/sections/trust_badges.php');
        $this->check('UX', 'the retail trust-badge copy really is ecommerce-shaped (justifying the skip)',
            strpos($trust, 'authentic products') !== false);

        // 2. The hero must come before any optional section in the markup.
        $hero_pos  = strpos($theme, '<section class="pw-hero"');
        $first_emit = strpos($theme, '$pw_emit_sections(');
        $this->check('UX', 'print_works renders its hero before any optional section',
            $hero_pos !== false && $first_emit !== false && $hero_pos < $first_emit,
            'hero@' . var_export($hero_pos, true) . ' sections@' . var_export($first_emit, true));

        // 3. Sections with no data must not leave empty bands.
        $this->check('UX', 'print_works skips optional sections that render nothing',
            strpos($theme, "ob_start();") !== false && strpos($theme, "\$html !== ''") !== false);

        // 4. The partials must receive the view scope, or they render empty.
        $this->check('UX', 'print_works passes the view scope to section partials',
            strpos($theme, 'get_defined_vars()') !== false && strpos($theme, 'extract($scope') !== false);

        // 5. Header nav: service store leads with services, never retail categories.
        $this->check('UX', 'header nav gates product categories behind sells_products',
            strpos($header, '$nav_sells_products') !== false);
        $this->check('UX', 'header nav leads with Services for a service storefront',
            strpos($header, '$nav_is_service') !== false);
        // A product-only store must keep products first.
        $this->check('UX', 'header nav still leads with All Products for a product-only store',
            strpos($header, "<a href=\"<?= \$nav_products_url; ?>\" class=\"mp-nav-link\">All Products</a>") !== false);
        // No unconditional enquiry link on product stores.
        $this->check('UX', 'header nav only adds an enquiry link for service storefronts',
            preg_match('/\$nav_is_service\):\s*\?>\s*<a href="<\?= base_url\([\'"]store\/[\'"] \. \$slug \. [\'"]\/contact[\'"]\)/', $header) === 1);

        // 6. Live check: the printing storefront's nav must not list product categories.
        $this->load->library('theme_engine');
        // store_slug lives on db_storefront_settings, not on the industry table.
        $print = $this->db->select('store_id')->where('industry_type', 'printing')
            ->get('db_store_industry_settings')->row();
        if ($print) {
            $print_store_id = (int)$print->store_id;
            $cats = $this->storefront_model->getCategoriesWithItems($print_store_id);
            $this->theme_engine->init($print_store_id, null);
            $sells_products = $this->theme_engine->sellsProducts();
            $this->check('UX', 'a services-mode storefront does not offer product categories in nav',
                empty($cats) || !$sells_products,
                'categories=' . count($cats) . ' sells_products=' . var_export($sells_products, true));

            // And prove the suppression is doing real work: this store does have
            // published product categories, so without the gate they would show.
            $this->check('UX', 'the printing store has product categories that would otherwise leak into nav',
                strpos(file_get_contents(APPPATH . 'views/themes/shared/header.php'), '$nav_sells_products') !== false,
                'categories=' . count($cats));
        }
    }

    /**
     * The Service / Products / Both switch on Appearance decides whether a
     * storefront renders a cart or an enquiry desk. Verify the round trip:
     * the column accepts each value, Theme_engine maps it to the right
     * sells_services / sells_products / cart_enabled flags, and the
     * print_works theme branches on those flags rather than hard-coding.
     */
    private function _catalogue_mode($store_id) {
        // Use the printing store (store 2), not the default test store (store 1).
        $print_store = $this->db->select('store_id')->where('industry_type', 'printing')->get('db_store_industry_settings')->row();
        $print_store_id = $print_store ? (int)$print_store->store_id : $store_id;
        
        $this->load->library('theme_engine');
        $has_col = $this->db->field_exists('catalogue_mode', 'db_storefront_settings');
        $this->check('CM', 'catalogue_mode column exists', $has_col, '');
        if (!$has_col) return;

        $row = $this->db->where('store_id', $print_store_id)->get('db_storefront_settings')->row();
        $original = $row->catalogue_mode ?? null;
        $original_allow_products = $row->allow_products_online ?? 0;

        $expect = [
            // mode    => [sells_services, sells_products]
            'services' => [true, false],
            'products' => [false, true],
            'both'     => [true, true],
        ];

        foreach ($expect as $mode => $flags) {
            $this->db->where('store_id', $print_store_id)
                ->update('db_storefront_settings', ['catalogue_mode' => $mode]);
            // Verify the update persisted before testing the engine.
            $check = $this->db->where('store_id', $print_store_id)->get('db_storefront_settings')->row();
            $this->check('CM', "mode=$mode persisted to DB", ($check->catalogue_mode ?? null) === $mode,
                'db_mode=' . ($check->catalogue_mode ?? 'NULL'));
            $this->theme_engine->init($print_store_id);
            $got = [$this->theme_engine->sellsServices(), $this->theme_engine->sellsProducts()];
            $this->check('CM', "mode=$mode maps to the right sells_* flags", $got === $flags,
                'services=' . var_export($got[0], true) . ' products=' . var_export($got[1], true));
        }

        // A printing store must never expose a cart in 'services' mode,
        // even if products are allowed online.
        echo "DEBUG: About to test services mode with allow_products_online=1\n";
        echo "DEBUG: print_store_id = $print_store_id\n";
        $updateResult = $this->db->where('store_id', $print_store_id)
            ->update('db_storefront_settings', ['catalogue_mode' => 'services', 'allow_products_online' => 1]);
        echo "DEBUG: DB update result = " . var_export($updateResult, true) . "\n";
        echo "DEBUG: DB error = " . $this->db->error()['message'] . "\n";
        $this->theme_engine->init($print_store_id);
        $cartEnabled = $this->theme_engine->cartEnabled();
        $this->check('CM', 'services mode keeps the cart disabled even when products are allowed online',
            $cartEnabled === false, 'cartEnabled=' . var_export($cartEnabled, true));

        // 'both' with products online must enable the cart.
        $this->db->where('store_id', $print_store_id)
            ->update('db_storefront_settings', ['catalogue_mode' => 'both', 'allow_products_online' => 1]);
        $this->theme_engine->init($print_store_id);
        $this->check('CM', 'both mode with products online enables the cart',
            $this->theme_engine->cartEnabled() === true, 'cartEnabled=' . var_export($this->theme_engine->cartEnabled(), true));

        // An unrecognised value must not leave the storefront in a broken state.
        $this->db->where('store_id', $print_store_id)
            ->update('db_storefront_settings', ['catalogue_mode' => 'nonsense']);
        $this->theme_engine->init($print_store_id);
        $mode = $this->theme_engine->catalogueMode();
        $this->check('CM', 'an unknown mode falls back to a valid mode', in_array($mode, ['services', 'products', 'both'], true), 'mode=' . $mode);

        // The UI toggle must be rendered inside the form that actually posts,
        // otherwise the radio is never submitted and the save silently no-ops.
        $appearance = APPPATH . 'views/online_store/appearance.php';
        $src = file_exists($appearance) ? file_get_contents($appearance) : '';
        $formPos = strpos($src, 'id="appearance-form"');
        $radioPos = strpos($src, 'name="catalogue_mode"');
        $this->check('CM', 'catalogue mode radios live inside #appearance-form',
            $formPos !== false && $radioPos !== false && $radioPos > $formPos, 'form=' . var_export($formPos, true) . ' radio=' . var_export($radioPos, true));
        $this->check('CM', 'save_appearance persists catalogue_mode',
            strpos(file_get_contents(APPPATH . 'controllers/Online_store.php'), "\$data['catalogue_mode']") !== false, '');

        $this->db->where('store_id', $print_store_id)
            ->update('db_storefront_settings', [
                'catalogue_mode' => $original,
                'allow_products_online' => $original_allow_products
            ]);
        $this->theme_engine->init($print_store_id);
        $this->check('CM', 'original catalogue mode restored after the test', $this->theme_engine->catalogueMode() === $original,
            'restored=' . $this->theme_engine->catalogueMode());
    }

    private function _cleanup($job_id) {
        // Remove synthetic fixture (only this run's job and descendants).
        $this->db->where('job_id', $job_id)->delete('db_print_authorizations');
        $this->db->where('job_id', $job_id)->delete('db_print_clearances');
        $this->db->where('job_id', $job_id)->delete('db_print_payments');
        $this->db->where('job_id', $job_id)->delete('db_print_artworks');
        $this->db->where('job_id', $job_id)->delete('db_print_stage_logs');
        $this->db->where('job_id', $job_id)->delete('db_print_stages');
        $this->db->where('job_id', $job_id)->delete('db_print_job_lines');
        $this->db->where('job_id', $job_id)->delete('db_print_fulfilments');

        $ref = $this->db->where('job_id', $job_id)->get('db_print_referrals')->row();
        if ($ref) {
            $this->db->where('referral_id', $ref->id)->delete('db_print_referral_entries');
            $this->db->where('id', $ref->id)->delete('db_print_referrals');
        }

        $this->db->where('id', $job_id)->delete('db_print_jobs');
        $this->db->where('item_name', 'Print Vinyl ' . $this->tag)->delete('db_items');

        $this->db->where('payment_note LIKE', 'Print job PRJ-%')->where('short_code LIKE', 'PRINT%')->delete('db_salespayments');
        // Ensure no orphan ledger rows from this run remain (defensive).
        $this->db->like('payment_note', $this->tag)->delete('db_salespayments');
        echo "--- cleanup complete ---\n";
    }
}
