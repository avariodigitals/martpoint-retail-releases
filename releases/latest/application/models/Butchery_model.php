<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Butchery & Frozen Food Model
 * Carcass templates, cuts, shares, freezer locations, temperature logs
 */
class Butchery_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /* ===================== TEMPLATES ===================== */

    public function get_templates($store_id = null) {
        $store_id = $store_id ?? get_current_store_id();
        $this->db->where('store_id', $store_id);
        $this->db->order_by('template_name', 'asc');
        return $this->db->get('db_item_carcass_templates')->result();
    }

    public function get_template($id) {
        return $this->db->where('id', $id)->get('db_item_carcass_templates')->row();
    }

    public function save_template($data, $id = null) {
        $store_id = $data['store_id'] ?? get_current_store_id();
        $data['store_id'] = $store_id;

        if ($id) {
            $this->db->where('id', $id)->where('store_id', $store_id);
            $this->db->update('db_item_carcass_templates', $data);
            return $id;
        }

        $prefix = 'TMPL-' . date('Ymd') . '-';
        $last = $this->db->like('template_code', $prefix, 'after')
            ->where('store_id', $store_id)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('db_item_carcass_templates')->row();
        $next_num = $last ? ((int)substr($last->template_code, strrpos($last->template_code, '-') + 1) + 1) : 1;
        $data['template_code'] = $prefix . str_pad($next_num, 3, '0', STR_PAD_LEFT);

        $this->db->insert('db_item_carcass_templates', $data);
        return $this->db->insert_id();
    }

    public function delete_template($id) {
        $this->db->where('template_id', $id)->delete('db_item_carcass_template_cuts');
        $this->db->where('id', $id)->delete('db_item_carcass_templates');
    }

    public function get_template_cuts($template_id) {
        $this->db->where('template_id', $template_id);
        $this->db->order_by('sort_order', 'asc');
        return $this->db->get('db_item_carcass_template_cuts')->result();
    }

    public function save_template_cuts($template_id, $cuts) {
        $this->db->where('template_id', $template_id)->delete('db_item_carcass_template_cuts');
        foreach ($cuts as $i => $cut) {
            if (empty($cut['cut_name'])) continue;
            $cut['template_id'] = $template_id;
            $cut['sort_order'] = $i;
            $this->db->insert('db_item_carcass_template_cuts', $cut);
        }
    }

    /* ===================== SHARES ===================== */

    public function get_shares($store_id = null, $status = null) {
        $store_id = $store_id ?? get_current_store_id();
        $this->db->where('store_id', $store_id);
        if ($status) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('created_date', 'desc');
        return $this->db->get('db_carcass_shares')->result();
    }

    public function get_share($id) {
        return $this->db->where('id', $id)->get('db_carcass_shares')->row();
    }

    public function save_share($data, $id = null) {
        $data['store_id'] = $data['store_id'] ?? get_current_store_id();

        if (empty($data['customer_id'])) {
            $data['customer_id'] = null;
        }

        if (empty($data['created_date'])) {
            $data['created_date'] = date('Y-m-d');
        }
        if (empty($data['created_time'])) {
            $data['created_time'] = date('H:i:s');
        }

        if ($id) {
            $this->db->where('id', $id)->where('store_id', $data['store_id']);
            $this->db->update('db_carcass_shares', $data);
            return $id;
        }

        $this->db->insert('db_carcass_shares', $data);
        return $this->db->insert_id();
    }

    public function delete_share($id) {
        $this->db->where('id', $id)->delete('db_carcass_shares');
    }

    /* ===================== FREEZERS & TEMPERATURE ===================== */

    public function get_freezer_locations($store_id = null) {
        $store_id = $store_id ?? get_current_store_id();
        $this->db->where('store_id', $store_id);
        $this->db->where('status', 1);
        return $this->db->get('db_freezer_locations')->result();
    }

    public function get_freezer_location($id) {
        return $this->db->where('id', $id)->get('db_freezer_locations')->row();
    }

    public function save_freezer_location($data, $id = null) {
        $data['store_id'] = $data['store_id'] ?? get_current_store_id();

        if ($id) {
            $this->db->where('id', $id)->where('store_id', $data['store_id']);
            $this->db->update('db_freezer_locations', $data);
            return $id;
        }

        $this->db->insert('db_freezer_locations', $data);
        return $this->db->insert_id();
    }

    public function delete_freezer_location($id) {
        $this->db->where('id', $id)->update('db_freezer_locations', ['status' => 0]);
    }

    public function save_temperature_log($data) {
        $data['store_id'] = $data['store_id'] ?? get_current_store_id();
        if (empty($data['recorded_at'])) {
            $data['recorded_at'] = date('Y-m-d H:i:s');
        }
        $this->db->insert('db_temperature_logs', $data);
        return $this->db->insert_id();
    }

    public function get_recent_temperature_logs($limit = 50, $store_id = null) {
        $store_id = $store_id ?? get_current_store_id();
        $this->db->where('store_id', $store_id);
        $this->db->order_by('recorded_at', 'desc');
        $this->db->limit($limit);
        return $this->db->get('db_temperature_logs')->result();
    }

    /* ===================== RECEIVED CARCASSES ===================== */

    public function get_received_carcasses($store_id = null, $status = null) {
        $store_id = $store_id ?? get_current_store_id();
        $this->db->where('store_id', $store_id);
        if ($status) {
            $this->db->where('status', $status);
        }
        $this->db->order_by('id', 'desc');
        return $this->db->get('db_received_carcasses')->result();
    }

    public function get_received_carcass($id) {
        return $this->db->where('id', $id)->get('db_received_carcasses')->row();
    }

    public function save_received_carcass($data, $id = null) {
        $data['store_id'] = $data['store_id'] ?? get_current_store_id();

        if (empty($data['created_date'])) {
            $data['created_date'] = date('Y-m-d');
        }
        if (empty($data['created_time'])) {
            $data['created_time'] = date('H:i:s');
        }

        if ($id) {
            $this->db->where('id', $id)->where('store_id', $data['store_id']);
            $this->db->update('db_received_carcasses', $data);
            return $id;
        }

        $this->db->insert('db_received_carcasses', $data);
        return $this->db->insert_id();
    }

    public function update_carcass_status($id, $status) {
        $this->db->where('id', $id)->update('db_received_carcasses', ['status' => $status]);
    }

    /* ===================== CARCASS CUT RECORDS ===================== */

    public function get_cut_records($received_carcass_id) {
        $this->db->where('received_carcass_id', $received_carcass_id);
        $this->db->order_by('sort_order', 'asc');
        return $this->db->get('db_carcass_cut_records')->result();
    }

    public function save_cut_records($received_carcass_id, $cuts) {
        $this->db->where('received_carcass_id', $received_carcass_id)->delete('db_carcass_cut_records');
        foreach ($cuts as $i => $cut) {
            if (empty($cut['cut_name'])) continue;
            $this->db->insert('db_carcass_cut_records', [
                'received_carcass_id' => $received_carcass_id,
                'cut_name'            => $cut['cut_name'],
                'expected_weight'     => isset($cut['expected_weight']) ? (float) $cut['expected_weight'] : 0,
                'actual_weight'       => isset($cut['actual_weight']) ? (float) $cut['actual_weight'] : 0,
                'packs'               => isset($cut['packs']) ? (int) $cut['packs'] : 1,
                'waste'               => isset($cut['waste']) ? (float) $cut['waste'] : 0,
                'notes'               => $cut['notes'] ?? '',
                'sort_order'          => $i,
            ]);
        }
    }

    public function delete_cut_records($received_carcass_id) {
        $this->db->where('received_carcass_id', $received_carcass_id)->delete('db_carcass_cut_records');
    }

    /**
     * Complete the cutting and create sellable items for each cut.
     * This is a lightweight stock integration: cuts become db_items with stock = packs.
     */
    public function complete_cut_to_stock($received_carcass_id, $cuts) {
        $this->db->trans_begin();
        try {
            $this->save_cut_records($received_carcass_id, $cuts);
            $this->update_carcass_status($received_carcass_id, 'completed');

            $carcass = $this->get_received_carcass($received_carcass_id);
            if (!$carcass) {
                $this->db->trans_rollback();
                return false;
            }

            $store_id = $carcass->store_id;
            $category_id = $this->_get_or_create_category($store_id, 'Butchery Cuts');
            $unit_id     = $this->_get_or_create_unit($store_id, 'KG');
            $created_items = [];

            foreach ($cuts as $i => $cut) {
                if (empty($cut['cut_name']) || empty($cut['packs'])) continue;

                $item_name = trim($cut['cut_name']);
                $item_code = 'CUT-' . $carcass->id . '-' . ($i + 1);

                $actual_weight = (float) ($cut['actual_weight'] ?? 0);
                $packs = (int) $cut['packs'];
                $purchase_price = (float) ($cut['purchase_price'] ?? 0);
                $sales_price    = (float) ($cut['sales_price'] ?? 0);

                // Avoid duplicate item code on re-complete by deleting previous cut items for this carcass
                $this->db->where('item_code', $item_code)->where('store_id', $store_id)->delete('db_items');

                $this->db->insert('db_items', [
                    'store_id'             => $store_id,
                    'item_code'            => $item_code,
                    'item_name'            => $item_name,
                    'category_id'          => $category_id,
                    'unit_id'              => $unit_id,
                    'lot_number'           => $carcass->lot_number,
                    'price'                => $sales_price,
                    'purchase_price'       => $purchase_price,
                    'sales_price'          => $sales_price,
                    'stock'                => $actual_weight > 0 ? $actual_weight : $packs,
                    'status'               => 1,
                    'created_date'         => date('Y-m-d'),
                    'created_time'         => date('H:i:s'),
                    'created_by'           => get_current_user_id(),
                    'item_group'           => 'Single',
                    'item_production_mode' => 'batch',
                    'is_carcass'           => 0,
                    'portion_of_item_id'   => 0,
                    'workflow_template_key'=> 'retail_standard',
                ]);
                $created_items[] = $this->db->insert_id();
            }

            $this->db->trans_commit();
            return $created_items;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Butchery complete cut failed: ' . $e->getMessage());
            return false;
        }
    }

    private function _get_or_create_category($store_id, $category_name) {
        $row = $this->db->where('store_id', $store_id)->where('category_name', $category_name)->where('status', 1)->get('db_category')->row();
        if ($row) return $row->id;

        $count = (int) $this->db->where('store_id', $store_id)->count_all_results('db_category') + 1;
        $code = 'BC/' . str_pad($store_id, 2, '0', STR_PAD_LEFT) . '/' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $this->db->insert('db_category', [
            'store_id'      => $store_id,
            'count_id'      => $count,
            'category_code' => $code,
            'category_name' => $category_name,
            'status'        => 1,
        ]);
        return $this->db->insert_id();
    }

    private function _get_or_create_unit($store_id, $unit_name) {
        $row = $this->db->where('store_id', $store_id)->where('unit_name', $unit_name)->where('status', 1)->get('db_units')->row();
        if ($row) return $row->id;

        $this->db->insert('db_units', [
            'store_id'      => $store_id,
            'unit_name'     => $unit_name,
            'description'   => 'Kilogram — used for butcher cut weight tracking',
            'status'        => 1,
            'is_default'    => 0,
        ]);
        return $this->db->insert_id();
    }
}
