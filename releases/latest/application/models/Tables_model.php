<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Table Management Model
 * Manage restaurant tables: zones, capacity, status, QR codes
 *
 * A table can also belong to a waiter (its "section"). That is the only new
 * idea here: the assignment lives on the table, and a tab opened on the table
 * reads it, so a floor that was divided up at the start of the night attributes
 * every order without anyone typing a name again. No second floor-plan table and
 * no change to how tabs, rounds or stock are written.
 */
class Tables_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_all($store_id, $zone = null) {
        if (!$this->db->table_exists('db_tables')) {
            return [];
        }
        $this->db->where('store_id', $store_id);
        if (!empty($zone)) {
            $this->db->where('zone', $zone);
        }
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('table_name', 'ASC');
        return $this->db->get('db_tables')->result();
    }

    public function get_zones($store_id) {
        if (!$this->db->table_exists('db_tables')) {
            return [];
        }
        $query = $this->db->query(
            "SELECT DISTINCT zone FROM db_tables WHERE store_id = ? AND zone IS NOT NULL AND zone != '' ORDER BY zone",
            [$store_id]
        );
        return $query ? $query->result() : [];
    }

    public function get_by_id($id, $store_id) {
        if (!$this->db->table_exists('db_tables')) {
            return null;
        }
        $this->db->where('id', $id);
        $this->db->where('store_id', $store_id);
        return $this->db->get('db_tables')->row();
    }

    public function save($data, $id = null) {
        if (!$this->db->table_exists('db_tables')) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $data['updated_at'] = $now;

        if (empty($id)) {
            $data['created_at'] = $now;
            $this->db->insert('db_tables', $data);
            return $this->db->insert_id();
        } else {
            $this->db->where('id', $id);
            $this->db->where('store_id', $data['store_id']);
            $this->db->update('db_tables', $data);
            return $id;
        }
    }

    public function delete($id, $store_id) {
        if (!$this->db->table_exists('db_tables')) {
            return false;
        }
        $this->db->where('id', $id);
        $this->db->where('store_id', $store_id);
        return $this->db->delete('db_tables');
    }

    public function update_status($id, $store_id, $status) {
        if (!$this->db->table_exists('db_tables')) {
            return false;
        }
        $allowed = ['available', 'occupied', 'reserved', 'cleaning'];
        if (!in_array($status, $allowed)) {
            return false;
        }
        $this->db->where('id', $id);
        $this->db->where('store_id', $store_id);
        $this->db->update('db_tables', [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $this->db->affected_rows() > 0;
    }

    public function count_by_status($store_id) {
        if (!$this->db->table_exists('db_tables')) {
            return ['available' => 0, 'occupied' => 0, 'reserved' => 0, 'cleaning' => 0, 'total' => 0];
        }
        $query = $this->db->query(
            "SELECT status, COUNT(*) as cnt FROM db_tables WHERE store_id = ? GROUP BY status",
            [$store_id]
        );
        $result = $query ? $query->result() : [];
        $counts = ['available' => 0, 'occupied' => 0, 'reserved' => 0, 'cleaning' => 0, 'total' => 0];
        foreach ($result as $row) {
            $counts[$row->status] = (int)$row->cnt;
        }
        $counts['total'] = array_sum($counts);
        return $counts;
    }

    /* ===================== WAITER SECTIONS =====================
     * A section is one column on db_tables plus these reads and writes. It is
     * deliberately not a new hierarchy: the floor already has tables and zones,
     * and a waiter owns some of them tonight.
     */

    /** True only once migration 4.0.9.235 has run; until then no section UI is offered. */
    public function has_waiter_column() {
        return $this->db->table_exists('db_tables')
            && $this->db->field_exists('waiter_user_id', 'db_tables');
    }

    /** May this id hold a section? 0 means "back to the floor, unassigned". */
    public function waiter_is_assignable($user_id, $store_id) {
        $user_id = (int)$user_id;
        if($user_id === 0) return true;
        if($user_id < 0 || !$this->db->table_exists('db_users')) return false;
        return $this->db->where('id', $user_id)->where('store_id', (int)$store_id)
            ->where('status', 1)->count_all_results('db_users') > 0;
    }

    /**
     * Give one table to a waiter, or take the section off it.
     *
     * The store scope is in the WHERE, so a posted table id from another tenant
     * cannot be re-pointed at someone else's floor.
     */
    public function set_waiter($table_id, $store_id, $user_id) {
        if(!$this->has_waiter_column()) {
            return ['ok' => false, 'message' => 'Waiter sections are not available on this store yet.', 'count' => 0];
        }
        $table = $this->get_by_id((int)$table_id, (int)$store_id);
        if(!$table) {
            return ['ok' => false, 'message' => 'That table is not on your floor.', 'count' => 0];
        }
        $user_id = (int)$user_id;
        if(!$this->waiter_is_assignable($user_id, $store_id)) {
            return ['ok' => false, 'message' => 'That staff member does not work in this store.', 'count' => 0];
        }
        $this->db->where('id', (int)$table->id)->where('store_id', (int)$store_id)
            ->update('db_tables', ['waiter_user_id' => $user_id > 0 ? $user_id : null,
                                   'updated_at' => date('Y-m-d H:i:s')]);
        return ['ok' => true, 'count' => 1,
            'message' => $user_id > 0
                ? $table->table_name . ' is ' . ($this->waiter_names($store_id)[$user_id] ?? 'their') . "'s section."
                : $table->table_name . ' is back on the floor.'];
    }

    /**
     * Hand a whole zone to one waiter — the way a section is actually given out
     * at the start of the night, table by table being too slow for a busy floor.
     */
    public function set_zone_waiter($store_id, $zone, $user_id) {
        if(!$this->has_waiter_column()) {
            return ['ok' => false, 'message' => 'Waiter sections are not available on this store yet.', 'count' => 0];
        }
        $zone = trim(strip_tags((string)$zone));
        if($zone === '') {
            return ['ok' => false, 'message' => 'Choose the area to hand over.', 'count' => 0];
        }
        $user_id = (int)$user_id;
        if(!$this->waiter_is_assignable($user_id, $store_id)) {
            return ['ok' => false, 'message' => 'That staff member does not work in this store.', 'count' => 0];
        }
        $tables = $this->get_all((int)$store_id, $zone);
        if(empty($tables)) {
            return ['ok' => false, 'message' => 'No tables are in that area yet.', 'count' => 0];
        }
        $this->db->where('store_id', (int)$store_id)->where('zone', $zone)
            ->update('db_tables', ['waiter_user_id' => $user_id > 0 ? $user_id : null,
                                   'updated_at' => date('Y-m-d H:i:s')]);
        $who = $user_id > 0 ? ($this->waiter_names($store_id)[$user_id] ?? 'the waiter') : 'the floor';
        return ['ok' => true, 'count' => count($tables),
            'message' => count($tables) . ' table(s) in ' . $zone . ' now sit with ' . $who . '.'];
    }

    /**
     * Who is standing behind what.
     *
     * Unassigned tables come back as their own line so the manager can see the
     * part of the floor nobody has claimed yet, instead of counting cards. A
     * section held by somebody who has left the books says so out loud, because
     * that is a table that still needs handing over tonight.
     */
    public function section_summary($store_id) {
        if(!$this->has_waiter_column() || !$this->db->table_exists('db_users')) return [];
        $this->db->select('t.waiter_user_id AS waiter_id, COUNT(*) AS table_count,
                           GROUP_CONCAT(DISTINCT NULLIF(t.zone, \'\') SEPARATOR \', \') AS zones,
                           MAX(u.status) AS staff_status', FALSE);
        $this->db->join('db_users u', 'u.id = t.waiter_user_id AND u.store_id = t.store_id', 'left');
        $this->db->where('t.store_id', (int)$store_id);
        $this->db->group_by('t.waiter_user_id');
        $this->db->order_by('waiter_id', 'asc');
        /* The summary query has to finish before the name lookup runs: CI's
         * builder carries clauses until a get() clears them, and a nested lookup
         * in between would compile both queries as one. */
        $rows = $this->db->get('db_tables t');
        $rows = $rows ? $rows->result() : [];
        $names = $this->waiter_names($store_id);
        $out = [];
        foreach($rows as $row) {
            $id = (int)$row->waiter_id;
            $out[] = (object) [
                'waiter_id'   => $id,
                'waiter_name' => $id > 0
                    ? ((int)$row->staff_status === 1 ? ($names[$id] ?? 'a waiter') : 'no longer on the floor')
                    : 'Unassigned tables',
                'table_count' => (int)$row->table_count,
                'zones'       => (string)($row->zones ?? ''),
            ];
        }
        return $out;
    }

    /**
     * id => first name for everyone on this store's books, including staff who
     * have since left: a section assigned last month must still read as a
     * person, and never as a login or a bare id.
     */
    public function waiter_names($store_id) {
        if(!$this->db->table_exists('db_users')) return [];
        $this->db->select('id, first_name')->where('store_id', (int)$store_id);
        $got = $this->db->get('db_users');
        $out = [];
        foreach($got ? $got->result() : [] as $row) {
            $name = trim((string)($row->first_name ?? ''));
            $out[(int)$row->id] = $name !== '' ? $name : 'a waiter';
        }
        return $out;
    }
}
