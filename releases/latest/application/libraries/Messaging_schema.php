<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Readiness checks and explicit, additive recovery of the .208 messaging schema. */
class Messaging_schema {
    private $CI;
    public function __construct() { $this->CI =& get_instance(); }
    private function migration() {
        $path = APPPATH.'../updates/migrations/4.0.9.208_messaging_credits.sql';
        if (!is_readable($path)) throw new RuntimeException('The messaging migration file is missing. Re-upload the complete Central package.');
        return preg_replace('/^\s*--[^\n]*$/m', '', file_get_contents($path));
    }
    public function status() {
        $db = $this->CI->db;
        // Metadata can be stale after a setup in this same request.
        $db->data_cache = array();
        $missing = array(); $columns = array();
        preg_match_all('/CREATE TABLE IF NOT EXISTS (db_messaging_\w+)\s*\((.*?)\) ENGINE=/s', $this->migration(), $tables, PREG_SET_ORDER);
        if (count($tables) !== 9) throw new RuntimeException('The messaging migration is incomplete. Re-upload the complete Central package.');
        foreach ($tables as $table) {
            if (!$db->table_exists($table[1])) { $missing[] = $table[1]; continue; }
            preg_match_all('/^\s*(\w+)\s+(?:BIGINT|TINYINT|INT|VARCHAR|CHAR|TEXT|DATETIME)\b/m', $table[2], $fields);
            foreach ($fields[1] as $field) if (!$db->field_exists($field, $table[1])) $columns[] = $table[1].'.'.$field;
        }
        $settingsMissing = false;
        if (!in_array('db_messaging_settings', $missing, true) && !$columns) {
            $row = $db->query('SELECT id FROM db_messaging_settings WHERE id=1');
            if (!$row) throw new RuntimeException('Messaging database could not be checked. Review the Central PHP error log.');
            $settingsMissing = $row->num_rows() === 0;
        }
        return array('ready'=>!$missing && !$columns && !$settingsMissing, 'missing_tables'=>$missing,
            'missing_columns'=>$columns, 'settings_missing'=>$settingsMissing, 'can_repair'=>!$columns);
    }
    public function repair() {
        if (!mp_is_central() || !special_access()) throw new RuntimeException('Central administrator access is required.');
        if (strtolower($this->CI->input->method()) !== 'post') throw new RuntimeException('Use the messaging setup form.');
        $before = $this->status();
        if (!$before['can_repair']) throw new RuntimeException('An existing messaging table is incomplete. Restore its schema from backup or have support review it; no balances have been changed.');
        $db = $this->CI->db; $debug = $db->db_debug; $db->db_debug = false;
        $lock = false;
        try {
            $q = $db->query("SELECT GET_LOCK('mp_messaging_schema_setup',5) AS acquired");
            $lock = $q && (int)$q->row()->acquired === 1;
            if (!$lock) throw new RuntimeException('Another messaging setup is running. Retry shortly.');
            // This fixed, application-owned migration contains only table creation
            // and an INSERT IGNORE default. Never accept a filename or SQL from HTTP.
            foreach (explode(';', $this->migration()) as $sql) {
                $sql = trim($sql); if ($sql === '') continue;
                if (!preg_match('/^(CREATE TABLE IF NOT EXISTS db_messaging_\w+\s*\(|INSERT IGNORE INTO db_messaging_settings \(id\) VALUES \(1\)$)/s', $sql)) {
                    throw new RuntimeException('Unexpected messaging migration statement; setup was stopped.');
                }
                // Do not use multi_query: pending result sets break CI sessions.
                if (!$db->query($sql)) {
                    log_message('error', 'Messaging setup SQL failed: '.json_encode($db->error()));
                    throw new RuntimeException('Messaging setup could not finish. Review the Central PHP error log; existing balances are preserved.');
                }
            }
            $after = $this->status();
            if (!$after['ready']) throw new RuntimeException('Messaging setup is incomplete. Review the Central PHP error log.');
            // Do not stamp the global DB version or skip unrelated pending migrations.
            return $after;
        } finally {
            if ($lock) $db->query("SELECT RELEASE_LOCK('mp_messaging_schema_setup')");
            $db->db_debug = $debug;
        }
    }
}
