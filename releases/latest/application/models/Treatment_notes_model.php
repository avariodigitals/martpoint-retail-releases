<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Treatment Notes Model
 * Per-customer service history for Beauty, Spas, Salons
 */
class Treatment_notes_model extends CI_Model {

    var $table = 'db_treatment_notes as a';
    var $column_order = array('a.id','a.treatment_date','c.customer_name','a.service_type','a.staff_name','a.created_at');
    var $column_search = array('c.customer_name','c.mobile','a.service_type','a.notes','a.products_used','a.staff_name');
    var $order = array('a.treatment_date' => 'desc');

    public function __construct() {
        parent::__construct();
        $this->_ensure_tables();
    }

    /**
     * Auto-create treatment notes table if it doesn't exist
     */
    private function _ensure_tables() {
        if (!$this->db->table_exists('db_treatment_notes')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_treatment_notes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                store_id INT NOT NULL DEFAULT 1,
                customer_id INT NOT NULL,
                service_type VARCHAR(255) NOT NULL,
                notes TEXT NULL,
                treatment_date DATE NOT NULL,
                staff_id INT NULL DEFAULT 0,
                staff_name VARCHAR(255) NULL,
                products_used TEXT NULL,
                recommendations TEXT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_store_id (store_id),
                INDEX idx_customer_id (customer_id),
                INDEX idx_treatment_date (treatment_date),
                INDEX idx_service_type (service_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        if (!$this->db->table_exists('db_treatment_note_items')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS db_treatment_note_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                treatment_note_id INT NOT NULL,
                item_id INT NOT NULL,
                qty DECIMAL(12,3) NOT NULL DEFAULT 0,
                item_name VARCHAR(255) NULL,
                consumable_unit VARCHAR(50) NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_treatment_note_id (treatment_note_id),
                INDEX idx_item_id (item_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
    }

    // ========== CRUD ==========

    public function save($data, $id = null, $consumables = []) {
        $this->db->trans_begin();
        try {
            if ($id) {
                $this->db->where('id', $id);
                $this->db->update('db_treatment_notes', $data);
                $note_id = $id;
                // Reverse previous stock deductions
                $this->_reverse_stock($note_id);
                // Clear old consumables
                $this->db->where('treatment_note_id', $note_id);
                $this->db->delete('db_treatment_note_items');
            } else {
                $this->db->insert('db_treatment_notes', $data);
                $note_id = $this->db->insert_id();
            }
            // Save consumables and deduct stock
            foreach ($consumables as $c) {
                $item_id = (int) $c['item_id'];
                $qty = floatval($c['qty']);
                if ($item_id <= 0 || $qty <= 0) continue;
                $item = $this->db->where('id', $item_id)->get('db_items')->row();
                if (!$item) continue;
                $this->db->insert('db_treatment_note_items', [
                    'treatment_note_id' => $note_id,
                    'item_id' => $item_id,
                    'qty' => $qty,
                    'item_name' => $item->item_name,
                    'consumable_unit' => $item->consumable_unit ?? '',
                ]);
                // Deduct from db_items stock
                $this->db->set('stock', 'stock - ' . $qty, FALSE);
                $this->db->where('id', $item_id);
                $this->db->update('db_items');
            }
            $this->db->trans_commit();
            return $note_id;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            throw $e;
        }
    }

    private function _reverse_stock($note_id) {
        $items = $this->db->where('treatment_note_id', $note_id)->get('db_treatment_note_items')->result();
        foreach ($items as $it) {
            $this->db->set('stock', 'stock + ' . $it->qty, FALSE);
            $this->db->where('id', $it->item_id);
            $this->db->update('db_items');
        }
    }

    public function get_consumables($note_id) {
        $this->db->select('a.*, i.item_name, i.consumable_unit');
        $this->db->from('db_treatment_note_items a');
        $this->db->join('db_items i', 'i.id = a.item_id', 'left');
        $this->db->where('a.treatment_note_id', $note_id);
        return $this->db->get()->result();
    }

    public function get($id) {
        $this->db->select('a.*, c.customer_name, c.mobile, c.customer_code');
        $this->db->from('db_treatment_notes a');
        $this->db->join('db_customers c', 'c.id = a.customer_id', 'left');
        $this->db->where('a.id', $id);
        $row = $this->db->get()->row();
        if ($row) {
            $row->consumables = $this->get_consumables($id);
        }
        return $row;
    }

    public function get_by_customer($customer_id, $limit = 50) {
        $this->db->select('a.*, c.customer_name, c.mobile');
        $this->db->from('db_treatment_notes a');
        $this->db->join('db_customers c', 'c.id = a.customer_id', 'left');
        $this->db->where('a.customer_id', $customer_id);
        $this->db->order_by('a.treatment_date', 'DESC');
        $this->db->limit($limit);
        $results = $this->db->get()->result();
        foreach ($results as $r) {
            $r->consumables = $this->get_consumables($r->id);
        }
        return $results;
    }

    public function delete($id) {
        $this->db->trans_begin();
        try {
            // Reverse stock deductions
            $this->_reverse_stock($id);
            // Delete consumable records
            $this->db->where('treatment_note_id', $id);
            $this->db->delete('db_treatment_note_items');
            // Delete note
            $this->db->where('id', $id);
            $this->db->delete('db_treatment_notes');
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
        $this->db->select('a.*, c.customer_name, c.mobile, c.customer_code');
        $this->db->from('db_treatment_notes a');
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
        $this->db->from('db_treatment_notes');
        $this->db->where('store_id', $store_id);
        return $this->db->count_all_results();
    }

    // ========== Stats ==========

    public function count_by_customer($customer_id) {
        $this->db->where('customer_id', $customer_id);
        return $this->db->count_all_results('db_treatment_notes');
    }

    public function count_this_month($store_id) {
        $this->db->where('store_id', $store_id);
        $this->db->where('treatment_date >=', date('Y-m-01'));
        return $this->db->count_all_results('db_treatment_notes');
    }

    public function get_latest($store_id, $limit = 5) {
        $this->db->select('a.*, c.customer_name');
        $this->db->from('db_treatment_notes a');
        $this->db->join('db_customers c', 'c.id = a.customer_id', 'left');
        $this->db->where('a.store_id', $store_id);
        $this->db->order_by('a.treatment_date', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }
}
