<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Which vehicles a part fits (db_item_vehicle_compat).
 *
 * Fitment data is product knowledge, not customer data: db_vehicles remains the
 * customer's vehicle register, while this table only says "this part fits a
 * 2015-2020 Corolla". The part form replaces the whole list on save, the same
 * way the unit/barcode rows behave.
 */
class Item_vehicle_compat_model extends CI_Model {

    /** Cap on fitment rows per part — a parts sheet, not an entire catalogue. */
    const MAX_ROWS = 60;

    public function __construct() {
        parent::__construct();
    }

    public function rows_for_item($store_id, $item_id) {
        if (!$this->db->table_exists('db_item_vehicle_compat')) return [];
        return $this->db->where('store_id', (int) $store_id)
            ->where('item_id', (int) $item_id)
            ->order_by('make', 'ASC')->order_by('model', 'ASC')->order_by('id', 'ASC')
            ->get('db_item_vehicle_compat')->result();
    }

    /**
     * Replace the fitment list from the part form's posted arrays.
     * Returns false when the form never showed the section, so a save from
     * another business type leaves the stored fitments untouched.
     */
    public function replace_from_post($store_id, $item_id) {
        if ($this->input->post('vehicle_compat_section') === false) return false;
        $this->delete_for_item($store_id, $item_id);
        $rows = $this->posted_rows();
        foreach ($rows as $row) {
            $row['store_id'] = (int) $store_id;
            $row['item_id'] = (int) $item_id;
            $row['created_date'] = date('Y-m-d');
            $row['created_time'] = date('H:i:s');
            $this->db->insert('db_item_vehicle_compat', $row);
        }
        return true;
    }

    /** Posted fitment rows, blank lines dropped, make required. */
    public function posted_rows() {
        $makes  = (array) $this->input->post('compat_make', TRUE);
        $models = (array) $this->input->post('compat_model', TRUE);
        $froms  = (array) $this->input->post('compat_year_from', TRUE);
        $tos    = (array) $this->input->post('compat_year_to', TRUE);
        $engines= (array) $this->input->post('compat_engine', TRUE);
        $notes  = (array) $this->input->post('compat_note', TRUE);
        $rows = [];
        $count = min(count($makes), self::MAX_ROWS);
        for ($i = 0; $i < $count; $i++) {
            $make = mb_strtoupper(trim((string) ($makes[$i] ?? '')));
            if ($make === '') continue;
            $model = trim((string) ($models[$i] ?? ''));
            $year_from = $this->_year($froms[$i] ?? '');
            $year_to = $this->_year($tos[$i] ?? '');
            if ($year_from !== null && $year_to !== null && $year_to < $year_from) {
                $swap = $year_from; $year_from = $year_to; $year_to = $swap;
            }
            $rows[] = [
                'make'       => $this->_clip($make, 100),
                'model'      => $model === '' ? null : $this->_clip($model, 100),
                'year_from'  => $year_from,
                'year_to'    => $year_to,
                'engine'     => $this->_clip(trim((string) ($engines[$i] ?? '')), 100) ?: null,
                'note'       => $this->_clip(trim((string) ($notes[$i] ?? '')), 255) ?: null,
            ];
        }
        return $rows;
    }

    public function delete_for_item($store_id, $item_id) {
        if (!$this->db->table_exists('db_item_vehicle_compat')) return;
        $this->db->where('store_id', (int) $store_id)->where('item_id', (int) $item_id)
            ->delete('db_item_vehicle_compat');
    }

    /**
     * Parts in this store that fit the given vehicle.
     *
     * $ctx keys: make (required), model, year, engine. A stored fitment matches
     * when it names the make and leaves the fields the customer's vehicle does
     * not specify free — "Corolla, any year" fits a 2016 Corolla, and a row with
     * no model fits every model of that make.
     */
    public function items_for_vehicle($store_id, array $ctx, $limit = 100) {
        $make = mb_strtoupper(trim((string) ($ctx['make'] ?? '')));
        if ($make === '' || !$this->db->table_exists('db_item_vehicle_compat')) return [];
        $model = trim((string) ($ctx['model'] ?? ''));
        $year = $this->_year($ctx['year'] ?? '');
        $engine = trim((string) ($ctx['engine'] ?? ''));

        $this->db->distinct();
        $this->db->select('a.id, a.item_name, a.item_code, a.manufacturer_part_no, a.oem_reference, a.sales_price, a.stock');
        $this->db->from('db_item_vehicle_compat c');
        $this->db->join('db_items a', 'a.id = c.item_id', 'inner');
        $this->db->where('c.store_id', (int) $store_id);
        $this->db->where('a.store_id', (int) $store_id);
        $this->db->where('a.status', 1);
        $this->db->where('a.service_bit', 0);
        $this->db->where('UPPER(TRIM(c.make))', $make);
        if ($model !== '') {
            $this->db->group_start();
            $this->db->where('c.model IS NULL', null, false);
            $this->db->or_where('UPPER(TRIM(c.model))', mb_strtoupper($model));
            $this->db->group_end();
        }
        if ($year) {
            $this->db->group_start();
            $this->db->where('(c.year_from IS NULL OR c.year_from <= ' . (int) $year . ')', null, false);
            $this->db->where('(c.year_to IS NULL OR c.year_to >= ' . (int) $year . ')', null, false);
            $this->db->group_end();
        }
        if ($engine !== '') {
            $this->db->group_start();
            $this->db->where('c.engine IS NULL', null, false);
            $this->db->or_like('c.engine', $engine);
            $this->db->group_end();
        }
        $this->db->order_by('a.item_name', 'ASC');
        $this->db->limit((int) $limit);
        return $this->db->get()->result();
    }

    /** Fitment rows that match a vehicle, for the parts screen's detail column. */
    public function fitments_for_items($store_id, array $item_ids, array $ctx) {
        $out = [];
        if (empty($item_ids) || !$this->db->table_exists('db_item_vehicle_compat')) return $out;
        $rows = $this->db->where('store_id', (int) $store_id)
            ->where_in('item_id', $item_ids)->get('db_item_vehicle_compat')->result();
        $make = mb_strtoupper(trim((string) ($ctx['make'] ?? '')));
        $model = mb_strtoupper(trim((string) ($ctx['model'] ?? '')));
        $year = $this->_year($ctx['year'] ?? '');
        foreach ($rows as $r) {
            if ($make !== '' && mb_strtoupper((string) $r->make) !== $make) continue;
            if ($model !== '' && (string) $r->model !== '' && mb_strtoupper((string) $r->model) !== $model) continue;
            if ($year && ((int) $r->year_from > 0 && $year < (int) $r->year_from)) continue;
            if ($year && ((int) $r->year_to > 0 && $year > (int) $r->year_to)) continue;
            $out[(int) $r->item_id][] = $r;
        }
        return $out;
    }

    private function _year($value) {
        $value = (int) trim((string) $value);
        return ($value >= 1900 && $value <= 2200) ? $value : null;
    }

    private function _clip($value, $len) {
        return mb_substr(preg_replace('/\s+/', ' ', trim((string) $value)), 0, $len);
    }
}
