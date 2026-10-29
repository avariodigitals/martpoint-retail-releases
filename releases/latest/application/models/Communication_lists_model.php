<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Named audience lists. Contacts stay in the shared import pool, so a person is
 * stored once and simply appears in every list they belong to.
 */
class Communication_lists_model extends CI_Model {
    const GROUP = 'My audience lists';
    /** Named lists a store may keep; 0 from the administrator means no limit. */
    const DEFAULT_CAP = 25; // used only when the allowance cannot be read from MartPoint

    public function ready() { return $this->db->table_exists('db_communication_lists'); }

    public static function key($id) { return 'list_'.(int)$id; }

    /** List id carried by a segment key such as list_14, or 0 for other segments. */
    public static function id($segmentKey) { return preg_match('/^list_(\d{1,10})$/D',(string)$segmentKey,$m)?(int)$m[1]:0; }

    public function all($storeId,$channel) {
        if (!$this->ready()) return array();
        $rows=$this->db->where('store_id',(int)$storeId)->where('channel',$channel)->order_by('name','asc')->limit(250)->get('db_communication_lists')->result_array();
        foreach($rows as $i=>$row) $rows[$i]['key']=self::key($row['id']);
        return $rows;
    }

    public function get($storeId,$listId) {
        if (!$this->ready() || (int)$listId<1) return null;
        $row=$this->db->where('id',(int)$listId)->where('store_id',(int)$storeId)->get('db_communication_lists')->row_array();
        if (!$row) return null; $row['key']=self::key((int)$row['id']); return $row;
    }

    public function create($storeId,$name,$channel,$note,$actor,$cap=0) {
        if (!$this->ready()) throw new RuntimeException('Audience lists are not available yet. Ask for an app update.');
        if (!in_array($channel,array('email','sms'),true)) throw new InvalidArgumentException('Choose an email or SMS list.');
        $name=$this->clean($name,120); $note=$this->clean($note,500);
        if ($name==='') throw new RuntimeException('Give the audience list a name, such as VIP customers.');
        $cap=(int)$cap;
        if ($cap>0 && count($this->all($storeId,$channel)) >= $cap) throw new RuntimeException('You can keep '.$cap.' '.($channel==='sms'?'SMS':'email').' audience lists. Rename or remove one before adding another.');
        if ($this->getByName($storeId,$name)) throw new RuntimeException('You already have an audience list called '.$name.'.');
        $this->db->trans_begin();
        $ok=$this->db->insert('db_communication_lists',array('store_id'=>(int)$storeId,'name'=>$name,'channel'=>$channel,'note'=>$note,
            'contact_count'=>0,'created_by'=>mb_substr((string)$actor,0,60),'created_at'=>gmdate('Y-m-d H:i:s')));
        if (!$ok || !$this->db->trans_status()) { $this->db->trans_rollback(); throw new RuntimeException('The audience list could not be saved.'); }
        // mysqli reports the last insert id per statement, so read it before COMMIT clears it.
        $listId=(int)$this->db->insert_id();
        $this->db->trans_commit();
        return $listId;
    }

    public function rename($storeId,$listId,$name,$note) {
        $list=$this->get($storeId,$listId); if (!$list) throw new RuntimeException('That audience list is not in your store.');
        $name=$this->clean($name,120); $note=$this->clean($note,500);
        if ($name==='') throw new RuntimeException('Give the audience list a name.');
        $other=$this->getByName($storeId,$name);
        if ($other && (int)$other['id']!==(int)$list['id']) throw new RuntimeException('You already have an audience list called '.$name.'.');
        if (!$this->db->where('id',(int)$list['id'])->where('store_id',(int)$storeId)->update('db_communication_lists',
            array('name'=>$name,'note'=>$note,'updated_at'=>gmdate('Y-m-d H:i:s')))) throw new RuntimeException('The audience list could not be updated.');
        return true;
    }

    /** Removes the grouping only; the contacts themselves stay in your imports. */
    public function remove($storeId,$listId) {
        $list=$this->get($storeId,$listId); if (!$list) throw new RuntimeException('That audience list is not in your store.');
        $this->db->trans_begin();
        $this->db->where('store_id',(int)$storeId)->where('list_id',(int)$list['id'])->delete('db_communication_list_contacts');
        $this->db->where('id',(int)$list['id'])->where('store_id',(int)$storeId)->delete('db_communication_lists');
        if (!$this->db->trans_status()) { $this->db->trans_rollback(); throw new RuntimeException('The audience list could not be removed.'); }
        $this->db->trans_commit();
        return true;
    }

    /**
     * Attach imported recipients to a list. Chunked because an import can carry
     * 10,000 rows, and a contact that is not in the store's pool is never linked.
     */
    public function attachRecipients($storeId,$listId,$channel,array $recipients) {
        if (!$this->ready() || (int)$listId<1) return 0;
        $field = $channel==='sms'?'phone':'email';
        $attached=0;
        foreach(array_chunk(array_values(array_unique(array_filter($recipients))),500) as $chunk) {
            $placeholders=implode(',',array_fill(0,count($chunk),'?'));
            $sql='INSERT IGNORE INTO db_communication_list_contacts (store_id,list_id,contact_id,created_at)'
                .' SELECT ?,?,c.id,UTC_TIMESTAMP() FROM db_communication_contacts c'
                .' WHERE c.store_id=? AND c.'.$field.' IN ('.$placeholders.')';
            if (!$this->db->query($sql,array_merge(array((int)$storeId,(int)$listId,(int)$storeId),$chunk))) throw new RuntimeException('Contacts could not be added to the audience list.');
            $attached+=$this->db->affected_rows();
        }
        $this->refresh($storeId,$listId);
        return $attached;
    }

    public function detach($storeId,$listId,$contactId) {
        $this->db->where('store_id',(int)$storeId)->where('list_id',(int)$listId)->where('contact_id',(int)$contactId)->delete('db_communication_list_contacts');
        $this->refresh($storeId,$listId);
    }

    /** Rows shaped like every other audience source, so previews and sends reuse them. */
    public function contacts($storeId,$listId,$channel='email') {
        if (!$this->ready() || !in_array($channel,array('email','sms'),true)) return array();
        $rows=$this->db->select('c.id,c.name,c.email,c.phone')->from('db_communication_list_contacts l')
            ->join('db_communication_contacts c','c.id=l.contact_id')
            ->where('l.store_id',(int)$storeId)->where('l.list_id',(int)$listId)->where('c.store_id',(int)$storeId)
            ->order_by('c.id','asc')->limit(10000)->get()->result();
        return $rows===null?array():$rows;
    }

    public function refresh($storeId,$listId) {
        if (!$this->ready() || (int)$listId<1) return 0;
        $this->db->query('UPDATE db_communication_lists l SET l.contact_count=(SELECT COUNT(*) FROM db_communication_list_contacts x'
            .' WHERE x.list_id=l.id AND x.store_id=?) WHERE l.id=? AND l.store_id=?',array((int)$storeId,(int)$listId,(int)$storeId));
        $list=$this->get($storeId,$listId);
        return $list?(int)$list['contact_count']:0;
    }

    private function getByName($storeId,$name) {
        $row=$this->db->where('store_id',(int)$storeId)->where('name',$name)->get('db_communication_lists')->row_array();
        return $row?:null;
    }

    private function clean($value,$limit) {
        $value=preg_replace('/[\x00-\x1F\x7F]/u',' ',(string)$value);
        $value=trim(preg_replace('/\s+/u',' ',$value));
        return mb_substr($value,0,(int)$limit);
    }
}
