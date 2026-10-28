<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Explicit additive recovery; customer inbox and billed dispatches are separate. */
class Messaging_schema {
    private $CI;
    public function __construct() { $this->CI =& get_instance(); }
    private function migration() {
        $path = APPPATH.'../updates/migrations/4.0.9.208_messaging_credits.sql';
        if (!is_readable($path)) throw new RuntimeException('The messaging migration file is missing. Re-upload the complete Central package.');
        $separation = APPPATH.'../updates/migrations/4.0.9.213_messaging_table_separation.sql';
        if (!is_readable($separation)) throw new RuntimeException('The messaging separation migration is missing. Re-upload the complete Central package.');
        // .208 reused the .171 inbox table name. Its historical SQL stays intact;
        // explicit setup creates the billing shape under its own canonical name.
        $sql = str_replace('CREATE TABLE IF NOT EXISTS db_messaging_messages (',
            'CREATE TABLE IF NOT EXISTS db_messaging_dispatches (',file_get_contents($path));
        return preg_replace('/^\s*--[^\n]*$/m', '', $sql."\n".file_get_contents($separation));
    }
    private function legacy($metadata=null) {
        $db=$this->CI->db;
        $hasTable=function($table)use($db,$metadata){return $metadata===null?$db->table_exists($table):isset($metadata[$table]);};
        $hasField=function($field,$table)use($db,$metadata){return $metadata===null?$db->field_exists($field,$table):isset($metadata[$table][$field]);};
        $result=array('pending'=>false,'problem'=>'','count'=>0,'max_id'=>0);
        if (!$hasTable('db_messaging_messages') || !$hasField('account_id','db_messaging_messages')) return $result;
        if (!$hasField('id','db_messaging_messages')) {
            $result['problem']='The old billing table has no message identity. Support must review it before setup can continue.';
            return $result;
        }
        $stats=$db->query('SELECT COUNT(*) AS n,COALESCE(MAX(id),0) AS max_id FROM db_messaging_messages')->row();
        if (!$stats || !(int)$stats->n) return $result;
        $result['count']=(int)$stats->n;$result['max_id']=(int)$stats->max_id;
        if ($hasField('conversation_id','db_messaging_messages')) {
            $result['problem']='The old message table mixes inbox and billing fields. Support must review it; no history will be guessed.';
            return $result;
        }
        preg_match('/CREATE TABLE IF NOT EXISTS db_messaging_dispatches\s*\((.*?)\) ENGINE=/s',$this->migration(),$definition);
        preg_match_all('/^\s*(\w+)\s+(?:BIGINT|TINYINT|INT|VARCHAR|CHAR|TEXT|DATETIME)\b/m',$definition[1],$fields);
        foreach($fields[1] as $field) if(!$hasField($field,'db_messaging_messages')) {
            $result['problem']='The old billing history is incomplete. Support must review its schema before copying it.';
            return $result;
        }
        $record=$hasTable('db_messaging_schema_imports') && $hasField('source_table','db_messaging_schema_imports')
            ? $db->where('source_table','db_messaging_messages')->get('db_messaging_schema_imports')->row_array() : null;
        $result['pending']=!$record || (int)($record['source_count'] ?? -1)!==$result['count'] || (int)($record['source_max_id'] ?? -1)!==$result['max_id'];
        return $result;
    }
    public function status() {
        $db = $this->CI->db;
        // Metadata can be stale after a setup in this same request.
        $db->data_cache = array();
        $missing = array(); $columns = array();
        preg_match_all('/CREATE TABLE IF NOT EXISTS (db_messaging_\w+)\s*\((.*?)\) ENGINE=/s', $this->migration(), $tables, PREG_SET_ORDER);
        $unique=array();foreach($tables as $table)$unique[$table[1]]=$table;
        if (count($unique) !== 10) throw new RuntimeException('The messaging migration is incomplete. Re-upload the complete Central package.');
        // One metadata read, instead of repeated SHOW COLUMNS round trips on
        // shared hosting. No merchant message content is read for readiness.
        $names=array_merge(array_keys($unique),array('db_messaging_messages'));
        $placeholders=implode(',',array_fill(0,count($names),'?'));
        $result=$db->query('SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('.$placeholders.')',$names);
        if(!$result)throw new RuntimeException('Messaging table metadata could not be read. Retry setup or review database locks.');
        $metadata=array();foreach($result->result_array() as $row)$metadata[$row['TABLE_NAME']][$row['COLUMN_NAME']]=true;
        foreach ($unique as $table) {
            if (!isset($metadata[$table[1]])) { $missing[] = $table[1]; continue; }
            preg_match_all('/^\s*(\w+)\s+(?:BIGINT|TINYINT|INT|VARCHAR|CHAR|TEXT|DATETIME)\b/m', $table[2], $fields);
            foreach ($fields[1] as $field) if (!isset($metadata[$table[1]][$field])) $columns[] = $table[1].'.'.$field;
        }
        $settingsMissing = false;
        if (!in_array('db_messaging_settings', $missing, true) && !$columns) {
            $row = $db->query('SELECT id FROM db_messaging_settings WHERE id=1');
            if (!$row) throw new RuntimeException('Messaging database could not be checked. Review the Central PHP error log.');
            $settingsMissing = $row->num_rows() === 0;
        }
        $legacy=$this->legacy($metadata);
        return array('ready'=>!$missing && !$columns && !$settingsMissing && !$legacy['pending'] && !$legacy['problem'], 'missing_tables'=>$missing,
            'missing_columns'=>$columns, 'settings_missing'=>$settingsMissing, 'can_repair'=>!$columns && !$legacy['problem'],
            'legacy_import_pending'=>$legacy['pending'],'legacy_message_count'=>$legacy['count'],'message'=>$legacy['problem']);
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
            if (!$this->status()['can_repair']) throw new RuntimeException('The schema changed while setup was starting. Review the setup diagnostics before continuing.');
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
            $this->copyLegacyBilling();
            $after = $this->status();
            if (!$after['ready']) throw new RuntimeException('Messaging setup is incomplete. Review the Central PHP error log.');
            // Do not stamp the global DB version or skip unrelated pending migrations.
            return $after;
        } finally {
            if ($lock) $db->query("SELECT RELEASE_LOCK('mp_messaging_schema_setup')");
            $db->db_debug = $debug;
        }
    }
    /** One short resumable browser step. Never do a full setup in one request. */
    public function repairStep() {
        if(!mp_is_central() || !special_access())throw new RuntimeException('Central administrator access is required.');
        if(strtolower($this->CI->input->method())!=='post')throw new RuntimeException('Use the messaging setup form.');
        $db=$this->CI->db;$debug=$db->db_debug;$db->db_debug=false;$lock=false;$timeouts=null;
        try {
            $q=$db->query('SELECT @@SESSION.lock_wait_timeout AS metadata_wait,@@SESSION.innodb_lock_wait_timeout AS row_wait');
            if(!$q)throw new RuntimeException('Database lock limits could not be checked. No setup step was started.');
            $timeouts=$q->row_array();
            if(!$db->query('SET SESSION lock_wait_timeout=3') || !$db->query('SET SESSION innodb_lock_wait_timeout=3'))throw new RuntimeException('Short database lock limits could not be applied. No setup step was started.');
            $q=$db->query("SELECT GET_LOCK('mp_messaging_schema_setup',0) AS acquired");
            $lock=$q && (int)$q->row()->acquired===1;
            if(!$lock)throw new RuntimeException('Another setup request is still running. Wait briefly, then press Resume; do not submit several requests.');
            $state=$this->status();
            if(!$state['can_repair'])throw new RuntimeException($state['message'] ?: 'An existing billing table is incomplete. Review the missing columns; setup will not rewrite it.');
            $message='Messaging database setup is complete.';
            if($state['missing_tables']){
                $table=$state['missing_tables'][0];$create=null;
                foreach(explode(';',$this->migration()) as $statement){
                    $statement=trim($statement);
                    if(preg_match('/^CREATE TABLE IF NOT EXISTS (db_messaging_\w+)\s*\(/',$statement,$match) && $match[1]===$table){$create=$statement;break;}
                }
                if(!$create)throw new RuntimeException('The fixed setup definition for this table is missing.');
                if(!$db->query($create))throw new RuntimeException('Creating '.$table.' could not finish within the database lock limit. Check database locks or hosting load, then Resume. Existing records are preserved.');
                $message='Created '.$table.'.';
            }elseif($state['settings_missing']){
                if(!$db->query('INSERT IGNORE INTO db_messaging_settings (id) VALUES (1)'))throw new RuntimeException('Default messaging controls could not be created. Resume after checking database locks.');
                $message='Initialised messaging controls. Paid channels remain off by default.';
            }elseif($state['legacy_import_pending']){
                $this->copyLegacyBilling(50);
                $message='Copied the next verified billing-history batch; original history and balances are unchanged.';
            }
            $after=$this->status();$after['step_message']=$message;
            return $after;
        }finally{
            if($lock)$db->query("SELECT RELEASE_LOCK('mp_messaging_schema_setup')");
            if($timeouts){$db->query('SET SESSION lock_wait_timeout='.(int)$timeouts['metadata_wait']);$db->query('SET SESSION innodb_lock_wait_timeout='.(int)$timeouts['row_wait']);}
            $db->db_debug=$debug;
        }
    }
    private function copyLegacyBilling($batchLimit=0) {
        $legacy=$this->legacy();
        if($legacy['problem'])throw new RuntimeException($legacy['problem']);
        if(!$legacy['pending'])return;
        $db=$this->CI->db;
        $engines=$db->query("SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('db_messaging_messages','db_messaging_dispatches','db_messaging_schema_imports') AND ENGINE='InnoDB'")->row();
        if(!$engines || (int)$engines->n!==3)throw new RuntimeException('Billing history copy requires transactional tables. No balances have been changed.');
        $db->trans_begin();
        try {
            $progress=$db->where('source_table','db_messaging_messages:progress')->get('db_messaging_schema_imports')->row_array();
            $cursor=$batchLimit>0?(int)($progress['source_max_id'] ?? 0):0;
            $range='';$lastId=$cursor;
            if($batchLimit>0){
                $batch=$db->query('SELECT id FROM db_messaging_messages WHERE id>? ORDER BY id LIMIT '.(int)$batchLimit.' FOR UPDATE',array($cursor));
                if(!$batch)throw new RuntimeException('Billing-history batch could not be locked. Resume after checking database load.');
                $ids=array();foreach($batch->result_array() as $row){$ids[]=(int)$row['id'];$lastId=(int)$row['id'];}
                $range=$ids?' AND o.id IN ('.implode(',',$ids).')':' AND 1=0';
                if($ids)$db->query('SELECT id FROM db_messaging_dispatches WHERE id IN ('.implode(',',$ids).') FOR UPDATE');
            }else{
                $db->query('SELECT id FROM db_messaging_messages FOR UPDATE');
                $db->query('SELECT id FROM db_messaging_dispatches FOR UPDATE');
            }
            preg_match('/CREATE TABLE IF NOT EXISTS db_messaging_dispatches\s*\((.*?)\) ENGINE=/s',$this->migration(),$definition);
            preg_match_all('/^\s*(\w+)\s+(?:BIGINT|TINYINT|INT|VARCHAR|CHAR|TEXT|DATETIME)\b/m',$definition[1],$canonical);
            $fields=$canonical[1];
            $quoted=array();$compare=array();$source=array();
            foreach($fields as $field){$quoted[]='`'.$field.'`';$compare[]='NOT (n.`'.$field.'` <=> o.`'.$field.'`)';$source[]='o.`'.$field.'`';}
            $conflictQuery=$db->query('SELECT COUNT(*) AS n FROM db_messaging_messages o JOIN db_messaging_dispatches n ON n.id=o.id WHERE ('.implode(' OR ',$compare).')'.$range);
            $conflict=$conflictQuery?$conflictQuery->row():null;
            if(!$conflict || (int)$conflict->n)throw new RuntimeException('Existing dispatch IDs differ from the old billing history. Setup stopped without overwriting either history.');
            $copy='INSERT INTO db_messaging_dispatches ('.implode(',',$quoted).') SELECT '.implode(',',$source).' FROM db_messaging_messages o LEFT JOIN db_messaging_dispatches n ON n.id=o.id WHERE n.id IS NULL'.$range;
            if(!$db->query($copy))throw new RuntimeException('Billing history could not be copied without a conflict. Both histories are preserved.');
            $stats=$db->query('SELECT COUNT(*) AS n,COALESCE(MAX(id),0) AS max_id FROM db_messaging_messages')->row();
            $actor=isset($this->CI->session) ? (string)$this->CI->session->userdata('inv_username') : 'Central setup';
            $complete=$batchLimit===0 || $lastId>=(int)$stats->max_id;
            $key=$complete?'db_messaging_messages':'db_messaging_messages:progress';
            if(!$db->query('INSERT INTO db_messaging_schema_imports (source_table,source_count,source_max_id,imported_at,reviewed_by) VALUES (?,?,?,UTC_TIMESTAMP(),?) ON DUPLICATE KEY UPDATE source_count=VALUES(source_count),source_max_id=VALUES(source_max_id),imported_at=VALUES(imported_at),reviewed_by=VALUES(reviewed_by)',
                array($key,(int)$stats->n,$complete?(int)$stats->max_id:$lastId,mb_substr($actor,0,100))) || !$db->trans_status())throw new RuntimeException('Could not record the billing-history copy. No copy was committed.');
            if($complete && !$db->where('source_table','db_messaging_messages:progress')->delete('db_messaging_schema_imports'))throw new RuntimeException('Could not finish the history-copy checkpoint.');
            $db->trans_commit();
        }catch(Throwable $e){$db->trans_rollback();throw $e;}
    }
}
