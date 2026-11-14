<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'libraries/Communication_designs.php';

/**
 * Email designs a store owns, plus the MartPoint design hub (store_id=0) that
 * Central keeps. This model only moves rows; Communication_designs validates the
 * blocks and builds the email, so a design from either place renders the same way.
 */
class Communication_template_model extends CI_Model {
    const HUB = 0;
    /** Designs one store may keep, so the gallery stays usable. */
    const STORE_CAP = 40;
    /** Rows a fleet is handed at once. */
    const HUB_LIMIT = 80;

    public function ready() { return $this->db->table_exists('db_communication_templates'); }

    /** Designs this store can send with. */
    public function forStore($storeId, $limit=self::STORE_CAP) {
        if (!$this->ready() || (int)$storeId < 1) return array();
        return $this->db->where('store_id',(int)$storeId)->where('enabled',1)
            ->order_by('category','asc')->order_by('name','asc')->limit((int)$limit)->get('db_communication_templates')->result_array();
    }

    public function get($storeId, $id) {
        if (!$this->ready() || (int)$id < 1) return null;
        $row=$this->db->where('id',(int)$id)->where('store_id',(int)$storeId)->get('db_communication_templates')->row_array();
        return $row ?: null;
    }

    /** Central's own shelf; a fleet reaches it over the signed API, never directly. */
    public function hub($limit=self::HUB_LIMIT, $enabledOnly=true) {
        if (!$this->ready()) return array();
        $this->db->where('store_id',self::HUB);
        if ($enabledOnly) $this->db->where('enabled',1);
        return $this->db->order_by('category','asc')->order_by('name','asc')->limit((int)$limit)->get('db_communication_templates')->result_array();
    }

    public function hubCount() {
        if (!$this->ready()) return 0;
        return (int)$this->db->where('store_id',self::HUB)->count_all_results('db_communication_templates');
    }

    /** Add every MartPoint design that is not on the hub yet. Safe to run twice. */
    public function seedHub($actor='system') {
        if (!$this->ready()) throw new RuntimeException('Email designs need an application update. Contact your Administrator.');
        $added=0;
        foreach (Communication_designs::catalogue() as $slug=>$design) {
            $this->db->where('store_id',self::HUB)->where('base_template_key',$slug);
            if ((int)$this->db->count_all_results('db_communication_templates') > 0) continue;
            $row=Communication_designs::normalize($design);
            $row['base_template_key']=$slug;
            $this->insert(self::HUB,$row,$actor,'prebuilt');
            $added++;
        }
        return $added;
    }

    /** Create or update one design. $storeId is 0 for Central's hub. */
    public function save($storeId, array $p, $actor, $id=0) {
        if (!$this->ready()) throw new RuntimeException('Email designs need an application update. Contact your Administrator.');
        $row=Communication_designs::sanitize($p);
        $storeId=(int)$storeId;
        $existing=$storeId > 0 ? $this->get($storeId,$id) : $this->getHub($id);
        if ((int)$id > 0 && !$existing) throw new RuntimeException('That design is not yours to change.');
        if ($storeId > 0 && !$existing && $this->count($storeId) >= self::STORE_CAP) {
            throw new RuntimeException('You can keep '.self::STORE_CAP.' email designs. Remove one you no longer use first.');
        }
        $other=$this->byName($storeId,$row['name']);
        if ($other && (int)$other['id'] !== (int)$id) throw new RuntimeException('You already have a design called '.$row['name'].'.');
        $columns=$this->columns($row);
        if ($existing) {
            unset($columns['base_template_key']);
            $this->db->trans_begin();
            $this->db->where('id',(int)$existing['id'])->where('store_id',$storeId)
                ->update('db_communication_templates',array_merge($columns,array('kind'=>$row['kind'],'updated_at'=>gmdate('Y-m-d H:i:s'))));
            if (!$this->db->trans_status()) { $this->db->trans_rollback(); throw new RuntimeException('The design could not be saved.'); }
            $this->db->trans_commit();
            return (int)$existing['id'];
        }
        return $this->insert($storeId,$row,$actor,$row['kind']);
    }

    /** A store takes its own copy, so later hub edits never change a campaign mid-flight. */
    public function adopt($storeId, array $hubDesign, $actor) {
        if (!$this->ready()) throw new RuntimeException('Email designs need an application update. Contact your Administrator.');
        $storeId=(int)$storeId;
        $hubId=(int)($hubDesign['id'] ?? 0);
        if ($hubId < 1 || (int)($hubDesign['store_id'] ?? self::HUB) !== self::HUB) throw new RuntimeException('Choose a MartPoint design.');
        $taken=$this->db->where('store_id',$storeId)->where('hub_template_id',$hubId)->get('db_communication_templates')->row_array();
        if ($taken) return (int)$taken['id'];
        if ($this->count($storeId) >= self::STORE_CAP) throw new RuntimeException('You can keep '.self::STORE_CAP.' email designs. Remove one you no longer use first.');
        $row=Communication_designs::normalize($hubDesign);
        $name=$row['name']; $suffix=2;
        while ($this->byName($storeId,$name)) $name=mb_substr($row['name'],0,110).' ('.$suffix++.')';
        $row['name']=$name;
        $id=$this->insert($storeId,$row,$actor,'prebuilt');
        $this->db->where('id',$id)->update('db_communication_templates',array('hub_template_id'=>$hubId));
        return $id;
    }

    public function setEnabled($storeId, $id, $enabled) {
        $row=(int)$storeId === self::HUB ? $this->getHub($id) : $this->get($storeId,$id);
        if (!$row) throw new RuntimeException('That design is not yours to change.');
        $this->db->where('id',(int)$row['id'])->where('store_id',(int)$row['store_id'])
            ->update('db_communication_templates',array('enabled'=>!empty($enabled)?1:0,'updated_at'=>gmdate('Y-m-d H:i:s')));
        return true;
    }

    /** Designs still feeding an unfinished campaign cannot be thrown away. */
    public function remove($storeId, $id) {
        $row=(int)$storeId === self::HUB ? $this->getHub($id) : $this->get($storeId,$id);
        if (!$row) throw new RuntimeException('That design is not yours to change.');
        if ($this->inUse((int)$row['store_id'],(int)$row['id'])) {
            throw new RuntimeException('A draft or scheduled campaign still uses this design. Send it or switch its design first.');
        }
        $this->db->where('id',(int)$row['id'])->where('store_id',(int)$row['store_id'])->delete('db_communication_templates');
        return true;
    }

    /** Hub rows and POST-ready rows arrive in two shapes; both store the same columns. */
    private function columns(array $row) {
        $d=Communication_designs::normalize($row);
        return array('name'=>$d['name'],'format'=>$d['format'],'category'=>$d['category'],
            'industry'=>$d['industry'],'occasion'=>$d['occasion'],'subject'=>$d['subject'],'preheader'=>$d['preheader'],
            'blocks_json'=>json_encode($d['blocks'],JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE),
            'accent'=>$d['accent'],'brand_display'=>$d['brand'],'show_unsubscribe'=>(int)$d['unsubscribe'],
            'cta_label'=>$d['cta_label'],'cta_url'=>$d['cta_url'],
            'base_template_key'=>mb_substr((string)($row['base_template_key'] ?? $d['key']),0,30));
    }

    /** One hub design, by id. Fleets receive this row through the signed API. */
    public function getHub($id) {
        return $this->get(self::HUB,$id);
    }

    private function count($storeId) {
        return (int)$this->db->where('store_id',(int)$storeId)->count_all_results('db_communication_templates');
    }

    private function inUse($storeId, $id) {
        if ((int)$storeId < 1 || !$this->db->table_exists('db_communication_campaign_designs') || !$this->db->table_exists('db_campaigns')) return false;
        $n=$this->db->query('SELECT COUNT(*) AS n FROM db_communication_campaign_designs d JOIN db_campaigns c ON c.id=d.campaign_id'
            .' WHERE d.template_id=? AND c.store_id=? AND c.status NOT IN (\'sent\',\'failed\')',array((int)$id,(int)$storeId))->row_array();
        return (int)($n['n'] ?? 0) > 0;
    }

    private function byName($storeId, $name) {
        $row=$this->db->where('store_id',(int)$storeId)->where('name',$name)->get('db_communication_templates')->row_array();
        return $row ?: null;
    }

    private function insert($storeId, array $row, $actor, $kind) {
        $this->db->trans_begin();
        $this->db->insert('db_communication_templates',array_merge($this->columns($row),
            array('store_id'=>(int)$storeId,'hub_template_id'=>0,'kind'=>$kind,'enabled'=>1,
                'created_by'=>mb_substr((string)$actor,0,60),'created_at'=>gmdate('Y-m-d H:i:s'),'updated_at'=>null)));
        if (!$this->db->trans_status()) { $this->db->trans_rollback(); throw new RuntimeException('The design could not be saved.'); }
        // mysqli reports the last insert id per statement, so read it before COMMIT clears it.
        $id=(int)$this->db->insert_id();
        $this->db->trans_commit();
        if ($id < 1) throw new RuntimeException('The design could not be saved.');
        return $id;
    }
}
