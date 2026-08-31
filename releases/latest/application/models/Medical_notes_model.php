<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Medical Notes Model
 * Per-patient prescription & dispensing records for Pharmacy workflows
 */
class Medical_notes_model extends CI_Model {

    var $table = 'db_medical_notes as a';
    var $column_order = array('a.id','a.note_date','c.customer_name','a.prescribing_doctor','a.diagnosis','a.status','a.created_at');
    var $column_search = array('c.customer_name','c.mobile','a.prescribing_doctor','a.diagnosis','a.prescription_ref','a.allergies_flagged');
    var $order = array('a.note_date' => 'desc');

    public function __construct() {
        parent::__construct();
        $this->_ensure_tables();
    }

    private function _ensure_tables() {
        if (!$this->db->table_exists('db_medical_notes')) {
            $sql_path = APPPATH . '../updates/migrations/4.0.2_medical_notes.sql';
            if (file_exists($sql_path)) {
                $sql = file_get_contents($sql_path);
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($statements as $stmt) {
                    if (!empty($stmt)) {
                        $this->db->query($stmt);
                    }
                }
            } else {
                log_message('error', 'Missing medical_notes migration SQL file.');
            }
        }

        if ($this->db->table_exists('db_medical_notes')) {
            $col_exists = $this->db->field_exists('prescription_file', 'db_medical_notes');
            if (!$col_exists) {
                $this->db->query("ALTER TABLE `db_medical_notes` ADD COLUMN `prescription_file` varchar(255) DEFAULT NULL AFTER `sales_id`");
            }
        }
    }

    // ========== CRUD ==========

    public function save($data, $id = null, $items = []) {
        $this->db->trans_begin();
        try {
            if ($id) {
                $this->db->where('id', $id);
                $this->db->update('db_medical_notes', $data);
                $note_id = $id;
                $this->db->where('medical_note_id', $note_id);
                $this->db->delete('db_medical_note_items');
            } else {
                $this->db->insert('db_medical_notes', $data);
                $note_id = $this->db->insert_id();
            }
            foreach ($items as $it) {
                $item_id = (int)$it['item_id'];
                if ($item_id <= 0) continue;
                $item = $this->db->where('id', $item_id)->get('db_items')->row();
                $this->db->insert('db_medical_note_items', [
                    'medical_note_id' => $note_id,
                    'item_id' => $item_id,
                    'item_name' => $item ? $item->item_name : ($it['item_name'] ?? ''),
                    'qty' => floatval($it['qty'] ?? 1),
                    'dosage' => $it['dosage'] ?? null,
                    'duration' => $it['duration'] ?? null,
                    'instructions' => $it['instructions'] ?? null,
                ]);
            }
            $this->db->trans_commit();
            return $note_id;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            throw $e;
        }
    }

    public function get_items($note_id) {
        $this->db->select('a.*, i.item_code, i.stock');
        $this->db->from('db_medical_note_items a');
        $this->db->join('db_items i', 'i.id = a.item_id', 'left');
        $this->db->where('a.medical_note_id', $note_id);
        return $this->db->get()->result();
    }

    public function get($id) {
        $this->db->select('a.*, c.customer_name, c.mobile, c.customer_code, c.email, c.address');
        $this->db->from('db_medical_notes a');
        $this->db->join('db_customers c', 'c.id = a.customer_id', 'left');
        $this->db->where('a.id', $id);
        $row = $this->db->get()->row();
        if ($row) {
            $row->items = $this->get_items($id);
        }
        return $row;
    }

    public function get_by_customer($customer_id, $limit = 50) {
        $this->db->select('a.*, c.customer_name, c.mobile');
        $this->db->from('db_medical_notes a');
        $this->db->join('db_customers c', 'c.id = a.customer_id', 'left');
        $this->db->where('a.customer_id', $customer_id);
        $this->db->order_by('a.note_date', 'DESC');
        $this->db->limit($limit);
        $results = $this->db->get()->result();
        foreach ($results as $r) {
            $r->items = $this->get_items($r->id);
        }
        return $results;
    }

    public function delete($id) {
        $this->db->trans_begin();
        try {
            $this->db->where('medical_note_id', $id);
            $this->db->delete('db_medical_note_items');
            $this->db->where('id', $id);
            $this->db->delete('db_medical_notes');
            $this->db->trans_commit();
            return true;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            throw $e;
        }
    }

    // ========== Datatables ==========

    private function _get_datatables_query() {
        $store_id = get_current_store_id();
        $this->db->select('a.*, c.customer_name, c.mobile, c.customer_code, a.prescription_file');
        $this->db->from('db_medical_notes a');
        $this->db->join('db_customers c', 'c.id = a.customer_id', 'left');
        $this->db->where('a.store_id', $store_id);

        $i = 0;
        foreach ($this->column_search as $item) {
            if (isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $_POST['search']['value']);
                } else {
                    $this->db->or_like($item, $_POST['search']['value']);
                }
                if (count($this->column_search) - 1 == $i) {
                    $this->db->group_end();
                }
            }
            $i++;
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    public function get_datatables() {
        $this->_get_datatables_query();
        if ($_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function count_filtered() {
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all() {
        $store_id = get_current_store_id();
        $this->db->from('db_medical_notes');
        $this->db->where('store_id', $store_id);
        return $this->db->count_all_results();
    }

    // ========== Stats ==========

    public function count_by_customer($customer_id) {
        $this->db->where('customer_id', $customer_id);
        return $this->db->count_all_results('db_medical_notes');
    }

    public function count_this_month($store_id) {
        $this->db->where('store_id', $store_id);
        $this->db->where('note_date >=', date('Y-m-01'));
        return $this->db->count_all_results('db_medical_notes');
    }

    public function get_latest($store_id, $limit = 5) {
        $this->db->select('a.*, c.customer_name');
        $this->db->from('db_medical_notes a');
        $this->db->join('db_customers c', 'c.id = a.customer_id', 'left');
        $this->db->where('a.store_id', $store_id);
        $this->db->order_by('a.note_date', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function get_refill_reminders($store_id, $days_ahead = 7) {
        $this->db->select('a.*, c.customer_name, c.mobile');
        $this->db->from('db_medical_notes a');
        $this->db->join('db_customers c', 'c.id = a.customer_id', 'left');
        $this->db->where('a.store_id', $store_id);
        $this->db->where('a.status', 'active');
        $this->db->where('a.refills_remaining >', 0);
        $this->db->where('a.next_refill_date <=', date('Y-m-d', strtotime("+$days_ahead days")));
        $this->db->order_by('a.next_refill_date', 'ASC');
        return $this->db->get()->result();
    }

    public function get_allergies($customer_id) {
        $this->db->select('a.allergies_flagged, a.note_date');
        $this->db->from('db_medical_notes a');
        $this->db->where('a.customer_id', $customer_id);
        $this->db->where('a.allergies_flagged IS NOT NULL');
        $this->db->where('a.allergies_flagged !=', '');
        $this->db->order_by('a.note_date', 'DESC');
        $this->db->limit(10);
        return $this->db->get()->result();
    }
}
