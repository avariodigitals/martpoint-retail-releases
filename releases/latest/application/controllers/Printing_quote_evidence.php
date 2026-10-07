<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Evidence helper — builds a REAL printing quotation through the application
 * so the customer-facing document and the shared quotation records can be
 * inspected. CLI only. Safe: it creates its own tagged fixtures and can clean
 * them up with `cleanup`.
 *
 *   MP_DB=martpoint php index.php printing_quote_evidence seed
 *   MP_DB=martpoint php index.php printing_quote_evidence cleanup
 */
class Printing_quote_evidence extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if (!is_cli()) { show_404(); return; }
        $this->load->model('printing_model', 'print');
    }

    public function seed() {
        $store_id = 2;
        $this->session->set_userdata([
            'inv_userid' => 121, 'inv_username' => 'print_review',
            'role_id' => 2, 'store_id' => $store_id,
        ]);
        $this->print->seed_categories($store_id);

        $cust = $this->db->where('store_id', $store_id)
            ->where('customer_name !=', 'Walk-in customer')
            ->order_by('id', 'asc')->limit(1)->get('db_customers')->row();
        if (!$cust) { echo "No customer in store $store_id\n"; return; }
        echo "customer_id={$cust->id} ({$cust->customer_name})\n";

        $L = $this->db->where('store_id', $store_id)->where('category_key', 'large_format')->get('db_print_categories')->row();
        $A = $this->db->where('store_id', $store_id)->where('category_key', 'apparel')->get('db_print_categories')->row();

        $job = $this->print->create_job([
            'customer_id' => $cust->id,
            'title' => 'EVIDENCE Shopfront banner + staff polos',
            'due_date' => date('Y-m-d', strtotime('+10 days')),
        ], [
            ['category_id' => $L->id, 'qty' => 2, 'unit_price' => 18500, 'description' => 'PVC Flex banner',
             'spec' => ['width' => 3, 'height' => 1, 'dim_unit' => 'm', 'material' => 'PVC Flex 440gsm', 'finish' => 'Hem & Eyelets']],
            ['category_id' => $A->id, 'qty' => 24, 'unit_price' => 3200, 'description' => 'Polo shirts',
             'spec' => ['garment_type' => 'Polo', 'colour' => 'Navy', 'print_positions' => ['Front', 'Left sleeve'],
                        'size_breakdown' => ['S' => 4, 'M' => 8, 'L' => 8, 'XL' => 4]]],
        ]);
        echo "job_id=$job\n";

        $lines = $this->print->get_lines($job);
        // Charged design + a waived installation (must not appear as a charge).
        $this->print->save_item_service($job, $lines[0]->id, [
            'charge_amount' => 12000, 'internal_cost' => 7000, 'charge_waived' => 0,
            'instructions' => 'Design 3m banner from supplied logo',
        ], 'design');
        $this->print->save_item_service($job, $lines[1]->id, [
            'charge_amount' => 6000, 'internal_cost' => 2500, 'charge_waived' => 1,
            'waiver_reason' => 'Loyalty — installation waived',
        ], 'installation');

        $res = $this->print->quote_to_quotation($job, [
            'customer_id' => $cust->id,
            'enquiry_note' => null,
            'note' => 'EVIDENCE: 2× PVC banner (3m × 1m, hem & eyelets) and 24× navy polo shirts with front + left sleeve print.',
            'expire_date' => date('Y-m-d', strtotime('+30 days')),
        ]);
        echo "quotation: " . json_encode($res) . "\n";
        echo "JOB_ID=$job\nQUOTATION_ID=" . ($res['quotation_id'] ?? 0) . "\n";
    }

    public function cleanup() {
        $id = (int)$this->input->get('job') ?: (int)getenv('JOB_ID');
        if (!$id) { echo "Pass ?job=<id> or JOB_ID\n"; return; }
        $job = $this->print->get_job($id);
        if ($job && !empty($job->quotation_id)) {
            $qid = (int)$job->quotation_id;
            $this->db->where('quotation_id', $qid)->delete('db_quotationitems');
            $this->db->where('quotation_id', $qid)->delete('db_quotation_revisions');
            $this->db->where('id', $qid)->delete('db_quotation');
            echo "removed quotation $qid\n";
        }
        foreach (['db_print_item_services', 'db_print_item_plans', 'db_print_material_issues',
                  'db_print_stage_logs', 'db_print_stages', 'db_print_job_lines'] as $t) {
            $this->db->where('job_id', $id)->delete($t);
        }
        $this->db->where('id', $id)->delete('db_print_jobs');
        echo "removed job $id\n";
    }
}
