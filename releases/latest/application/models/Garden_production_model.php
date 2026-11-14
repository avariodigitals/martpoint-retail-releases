<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Garden operations over the existing recipe, production-run and stock sources. */
class Garden_production_model extends CI_Model {
    public function ready() { return $this->db->table_exists('db_garden_production_context') && $this->db->table_exists('db_garden_production_entries') && $this->db->table_exists('db_garden_production_templates'); }
    private function store() { return (int)get_current_store_id(); }
    private function scalar(array $in,$key,$default='') { $v=$in[$key] ?? $default;if(!is_scalar($v))throw new InvalidArgumentException('Invalid '.$key.'.');return (string)$v; }
    private function number($value,$positive=false,$whole=false) {
        if (!is_scalar($value) || !preg_match($whole ? '/^\d{1,7}$/D' : '/^\d{1,9}(?:\.\d{1,2})?$/D',(string)$value) || ($positive && (float)$value<=0)) throw new InvalidArgumentException('Enter a valid '.($positive?'positive ':'').'quantity or cost.');
        return $whole ? (int)$value : (float)$value;
    }
    private function date($raw,$required=false) { if($raw===''&&!$required)return null;$d=DateTimeImmutable::createFromFormat('!Y-m-d',$raw);if(!$d||$d->format('Y-m-d')!==$raw)throw new InvalidArgumentException('Choose a valid date.');return $raw; }
    private function key(array $in) { $key=$this->scalar($in,'request_key');if(!preg_match('/^[A-Za-z0-9_-]{16,80}$/D',$key))throw new InvalidArgumentException('Reload the form for a valid action reference.');return $key; }
    private function text(array $in,$key,$max=8000,$required=false) { $text=trim($this->scalar($in,$key));if(strlen($text)>$max||($required&&$text===''))throw new InvalidArgumentException('Enter '.$key.' (up to '.$max.' characters).');return $text; }
    private function transaction(callable $fn) {
        if(!$this->ready())throw new InvalidArgumentException('Run Garden migration 4.0.9.160 first.');
        $this->db->trans_begin();try{$this->db->query('SELECT id FROM db_store WHERE id=? FOR UPDATE',[$this->store()]);$result=$fn();if($this->db->trans_status()===false)throw new RuntimeException('Production could not be saved.');$this->db->trans_commit();return $result;}catch(Throwable $e){$this->db->trans_rollback();throw $e;}
    }
    private function insert($table,array $row) { if(!$this->db->insert($table,$row))throw new RuntimeException('Could not save production record.');return (int)$this->db->insert_id(); }
    private function item($id,$output=false) {
        $item=$this->db->where('id',(int)$id)->where('store_id',$this->store())->where('status',1)->get('db_items')->row();
        if(!$item || (int)$item->service_bit===1 || !empty($item->is_bundle) || ($output && !empty($item->not_for_sale)))throw new InvalidArgumentException('Choose an active physical '.($output?'saleable product':'material').' from this business.');return $item;
    }
    private function replay($table,$key,$hash) { $r=$this->db->where('store_id',$this->store())->where('request_key',$key)->get($table)->row();if($r&&!hash_equals($r->payload_hash,$hash))throw new InvalidArgumentException('This action reference was already used for different details.');return $r; }
    public function templates() {
        if(!$this->ready())return [];
        return $this->db->select('r.*,g.kind,i.item_name AS output_name')->from('db_recipes r')->join('db_garden_production_templates g','g.recipe_id=r.id AND g.store_id=r.store_id')->join('db_items i','i.id=r.product_item_id AND i.store_id=r.store_id')->where('r.store_id',$this->store())->where('r.status',1)->order_by('r.name')->get()->result();
    }
    public function ingredients($recipeId) {
        if(!$this->db->where('store_id',$this->store())->where('recipe_id',(int)$recipeId)->get('db_garden_production_templates')->row())throw new InvalidArgumentException('Materials template not found.');
        return $this->db->select('g.*,u.unit_name')->from('db_recipe_ingredients g')->join('db_items i','i.id=g.item_id')->join('db_units u','u.id=i.unit_id','left')->where('g.recipe_id',(int)$recipeId)->where('i.store_id',$this->store())->get()->result();
    }
    public function create_template(array $in) {
        $name=$this->text($in,'name',200,true);$kind=$this->scalar($in,'kind');if(!in_array($kind,['growing','assembly'],true))throw new InvalidArgumentException('Choose growing or assembly.');
        $output=(int)$this->number($in['product_item_id']??'',true,true);$yield=$this->number($in['yield_qty']??'',true,true);$key=$this->key($in);$notes=$this->text($in,'notes');
        $ids=$in['material_id']??[];$qtys=$in['material_qty']??[];if(!is_array($ids)||!is_array($qtys)||count($ids)>60)throw new InvalidArgumentException('Choose up to 60 material lines.');$materials=[];
        foreach($ids as $k=>$id){if($id==='')continue;$id=(int)$this->number($id,true,true);if($id===$output)throw new InvalidArgumentException('The finished product cannot be its own material.');$qty=$this->number($qtys[$k]??'',true);$materials[$id]=($materials[$id]??0)+$qty;}
        if(!$materials)throw new InvalidArgumentException('Add at least one material.');ksort($materials);$hash=hash('sha256',json_encode([$name,$kind,$output,$yield,$notes,$materials]));
        return $this->transaction(function()use($name,$kind,$output,$yield,$key,$notes,$materials,$hash){
            $old=$this->replay('db_garden_production_templates',$key,$hash);if($old)return (int)$old->recipe_id;$item=$this->item($output,true);
            $unit=$this->db->where('id',(int)$item->unit_id)->get('db_units')->row();
            $recipe=$this->insert('db_recipes',['store_id'=>$this->store(),'recipe_code'=>'GDN-'.bin2hex(random_bytes(6)),'name'=>$name,'category'=>$kind==='growing'?'Nursery growing':'Arrangements & assembly','product_item_id'=>$output,'yield_qty'=>$yield,'yield_unit'=>$unit?$unit->unit_name:'unit','margin_pct'=>0,'notes'=>$notes,'status'=>1]);
            foreach($materials as $id=>$qty){$material=$this->item($id);$u=$this->db->where('id',(int)$material->unit_id)->get('db_units')->row();$this->insert('db_recipe_ingredients',['recipe_id'=>$recipe,'item_id'=>$id,'item_name'=>$material->item_name,'qty'=>$qty,'unit'=>$u?$u->unit_name:'unit','cost_per_unit'=>(float)$material->purchase_price,'wastage_pct'=>0]);}
            $this->insert('db_garden_production_templates',['store_id'=>$this->store(),'recipe_id'=>$recipe,'kind'=>$kind,'request_key'=>$key,'payload_hash'=>$hash,'created_by'=>(int)$this->session->userdata('inv_userid')]);return $recipe;
        });
    }
    public function listing() {
        if(!$this->ready())return [];
        return $this->db->select('b.*,g.kind,g.planned_qty,g.lost_qty,g.saleable_qty,g.expected_date,g.unit_cost,i.item_name AS output_name')->from('db_production_batches b')->join('db_garden_production_context g','g.batch_id=b.id AND g.store_id=b.store_id')->join('db_items i','i.id=g.product_item_id AND i.store_id=g.store_id')->where('b.store_id',$this->store())->order_by('b.id','desc')->limit(250)->get()->result();
    }
    public function get($batchId) {
        if(!$this->ready())return null;
        return $this->db->select('g.*,b.batch_name,b.batch_code,b.status,b.scheduled_date,b.notes,i.item_name AS output_name,w.warehouse_name')->from('db_garden_production_context g')->join('db_production_batches b','b.id=g.batch_id AND b.store_id=g.store_id')->join('db_items i','i.id=g.product_item_id AND i.store_id=g.store_id')->join('db_warehouse w','w.id=g.warehouse_id AND w.store_id=g.store_id')->where('g.store_id',$this->store())->where('g.batch_id',(int)$batchId)->get()->row();
    }
    public function entries($batchId) { if(!$this->get($batchId))return [];return $this->db->select('e.*,i.item_name,u.username AS author')->from('db_garden_production_entries e')->join('db_items i','i.id=e.item_id AND i.store_id=e.store_id','left')->join('db_users u','u.id=e.created_by AND u.store_id=e.store_id','left')->where('e.store_id',$this->store())->where('e.batch_id',(int)$batchId)->order_by('e.id','desc')->get()->result(); }
    public function totals($batchId) { $cost=['material'=>0.0,'labour'=>0.0,'other'=>0.0,'total'=>0.0];foreach($this->entries($batchId) as $e)if(isset($cost[$e->kind])){$cost[$e->kind]+=(float)$e->cost;$cost['total']+=(float)$e->cost;}return $cost; }
    public function create_batch(array $in) {
        $recipe=(int)$this->number($in['recipe_id']??'',true,true);$warehouse=(int)$this->number($in['warehouse_id']??'',true,true);$planned=$this->number($in['planned_qty']??'',true,true);$name=$this->text($in,'name',200,true);$date=$this->date($this->scalar($in,'scheduled_date'),true);$expected=$this->date($this->scalar($in,'expected_date'));if($expected&&$expected<$date)throw new InvalidArgumentException('Expected completion cannot precede the start date.');$key=$this->key($in);$notes=$this->text($in,'notes');$hash=hash('sha256',json_encode([$recipe,$warehouse,$planned,$name,$date,$expected,$notes]));
        return $this->transaction(function()use($recipe,$warehouse,$planned,$name,$date,$expected,$key,$notes,$hash){
            $old=$this->replay('db_garden_production_context',$key,$hash);if($old)return (int)$old->batch_id;
            $template=$this->db->select('r.*,g.kind')->from('db_recipes r')->join('db_garden_production_templates g','g.recipe_id=r.id AND g.store_id=r.store_id')->where('r.store_id',$this->store())->where('r.id',$recipe)->where('r.status',1)->get()->row();
            if(!$template || !$this->db->where('store_id',$this->store())->where('id',$warehouse)->where('status',1)->get('db_warehouse')->row())throw new InvalidArgumentException('Choose a template and branch from this business.');$this->item($template->product_item_id,true);$materials=[];
            foreach($this->ingredients($recipe) as $line){$this->item($line->item_id);$materials[]=['item_id'=>(int)$line->item_id,'quantity'=>round((float)$line->qty*$planned/(float)$template->yield_qty,2)];}
            if(!$materials)throw new InvalidArgumentException('The template has no materials.');
            $batch=$this->insert('db_production_batches',['store_id'=>$this->store(),'batch_code'=>'GROW-'.bin2hex(random_bytes(6)),'batch_name'=>$name,'batch_type'=>'garden_'.$template->kind,'scheduled_date'=>$date,'status'=>'planned','notes'=>$notes]);
            $this->insert('db_production_batch_items',['batch_id'=>$batch,'item_type'=>'recipe_product','item_id'=>$recipe,'item_name'=>$template->name,'quantity'=>$planned]);
            $this->insert('db_garden_production_context',['store_id'=>$this->store(),'batch_id'=>$batch,'recipe_id'=>$recipe,'product_item_id'=>$template->product_item_id,'warehouse_id'=>$warehouse,'kind'=>$template->kind,'planned_qty'=>$planned,'materials_json'=>json_encode($materials),'expected_date'=>$expected,'request_key'=>$key,'payload_hash'=>$hash,'created_by'=>(int)$this->session->userdata('inv_userid'),'created_at'=>date('Y-m-d H:i:s')]);return $batch;
        });
    }
    private function movement($batch,array $lines,$note) {
        $this->load->model('pos_model');$ids=array_keys($lines);sort($ids);foreach($ids as $id)$this->db->query('SELECT id FROM db_items WHERE id=? AND store_id=? FOR UPDATE',[(int)$id,$this->store()]);
        foreach($lines as $id=>$qty){$item=$this->item($id,$qty>0);if($qty<0 && min((float)$item->stock,(float)total_available_qty_items_of_warehouse($batch->warehouse_id,$this->store(),$id))+0.00001<-$qty)throw new InvalidArgumentException('Insufficient stock for '.$item->item_name.' at this branch.');}
        $adj=$this->insert('db_stockadjustment',['store_id'=>$this->store(),'warehouse_id'=>$batch->warehouse_id,'reference_no'=>$batch->batch_code,'adjustment_date'=>date('Y-m-d'),'adjustment_note'=>$note,'created_date'=>date('Y-m-d'),'created_time'=>date('H:i:s'),'created_by'=>$this->session->userdata('inv_username') ?: 'Garden production','system_ip'=>$this->input->ip_address(),'system_name'=>'Garden production','status'=>1]);
        foreach($lines as $id=>$qty)$this->insert('db_stockadjustmentitems',['store_id'=>$this->store(),'warehouse_id'=>$batch->warehouse_id,'adjustment_id'=>$adj,'item_id'=>$id,'adjustment_qty'=>$qty,'description'=>$note,'status'=>1]);
        foreach($ids as $id)if(!$this->pos_model->update_items_quantity($id))throw new RuntimeException('Could not reconcile production stock.');if(!update_warehouse_items(array_map(function($id){return ['item_id'=>$id];},$ids)))throw new RuntimeException('Could not reconcile branch stock.');return $adj;
    }
    private function entry($batch,$kind,$qty,$unit,$cost,$item,$adjustment,$note,$key,$hash) { return $this->insert('db_garden_production_entries',['store_id'=>$this->store(),'batch_id'=>$batch->batch_id,'kind'=>$kind,'quantity'=>$qty,'unit_cost'=>$unit,'cost'=>$cost,'item_id'=>$item,'adjustment_id'=>$adjustment,'note'=>$note,'request_key'=>$key,'payload_hash'=>$hash,'created_by'=>(int)$this->session->userdata('inv_userid'),'created_at'=>date('Y-m-d H:i:s')]); }
    /** Material issue, WIP loss, cost allocation and output are atomic and replay safe. */
    public function act($batchId,array $in) {
        $action=$this->scalar($in,'action');$key=$this->key($in);$version=$this->number($in['version']??'',true,true);$note=$this->text($in,'note');$qty=$this->number($in['quantity']??0);$amount=$this->number($in['amount']??0);$itemId=(int)$this->number($in['item_id']??0,false,true);
        if(!in_array($action,['start','material','labour','other','loss','ready','complete','cancel'],true))throw new InvalidArgumentException('Choose a valid production action.');
        $hash=hash('sha256',json_encode([(int)$batchId,$action,$qty,$amount,$itemId,$note]));
        return $this->transaction(function()use($batchId,$action,$key,$version,$note,$qty,$amount,$itemId,$hash){
            $prior=$this->replay('db_garden_production_entries',$key,$hash);if($prior)return true;$batch=$this->get($batchId);if(!$batch)throw new InvalidArgumentException('Batch not found.');
            if((int)$batch->version!==$version)throw new InvalidArgumentException('This batch changed. Refresh before continuing.');if(in_array($batch->status,['completed','cancelled'],true))throw new InvalidArgumentException('This batch is closed.');$update=[];
            if($action==='start'){
                if($batch->status!=='planned')throw new InvalidArgumentException('Only a planned batch can start.');$lines=[];
                foreach(json_decode($batch->materials_json,true)?:[] as $line){$q=$this->number($line['quantity'],true);$lines[(int)$line['item_id']]=($lines[(int)$line['item_id']]??0)-$q;}
                if(!$lines)throw new InvalidArgumentException('No material inputs.');$adj=$this->movement($batch,$lines,'Materials issued to '.$batch->batch_code);
                foreach($lines as $id=>$q){$cost=(float)$this->item($id)->purchase_price;if($cost<0)throw new InvalidArgumentException('Material cost cannot be negative.');$this->entry($batch,'material',-$q,$cost,round(-$q*$cost,4),$id,$adj,'Initial input',hash('sha256',$key.'_'.$id),hash('sha256',$hash.$id));}
                $update=['started_at'=>date('Y-m-d H:i:s')];$status='in_production';
            }elseif($action==='cancel'){
                if($batch->status!=='planned')throw new InvalidArgumentException('Started batches retain their costs. Record losses and close the actual output.');$status='cancelled';
            }else{
                if(!in_array($batch->status,['in_production','ready'],true))throw new InvalidArgumentException('Start this batch before recording work.');$status=$batch->status;
                if(in_array($action,['labour','other'],true)){if($amount<=0||$note==='')throw new InvalidArgumentException('Enter a positive allocated cost and explain it.');$this->entry($batch,$action,0,0,$amount,null,null,$note,$key,$hash);}
                elseif($action==='material'){if($qty<=0||$itemId===(int)$batch->product_item_id)throw new InvalidArgumentException('Choose an input material and positive quantity.');$material=$this->item($itemId);$cost=(float)$material->purchase_price;if($cost<0)throw new InvalidArgumentException('Material cost cannot be negative.');$adj=$this->movement($batch,[$itemId=>-$qty],'Additional input for '.$batch->batch_code);$this->entry($batch,'material',$qty,$cost,round($qty*$cost,4),$itemId,$adj,$note,$key,$hash);}
                elseif($action==='loss'){if($qty<=0||floor($qty)!=$qty||$qty+(int)$batch->lost_qty>(int)$batch->planned_qty||$note==='')throw new InvalidArgumentException('Enter whole lost units within the batch quantity and a reason.');$update['lost_qty']=(int)$batch->lost_qty+(int)$qty;$this->entry($batch,'loss',$qty,0,0,null,null,$note,$key,$hash);}
                elseif($action==='ready')$status='ready';
                elseif($action==='complete'){
                    if(floor($qty)!=$qty||$qty<0||$qty+(int)$batch->lost_qty!==(float)$batch->planned_qty)throw new InvalidArgumentException('Saleable output plus recorded losses must equal the planned units. Record losses before completing.');
                    $cost=$this->totals($batchId)['total'];$adj=null;if($qty>0)$adj=$this->movement($batch,[(int)$batch->product_item_id=>$qty],'Finished output from '.$batch->batch_code);
                    $update=['saleable_qty'=>(int)$qty,'completed_cost'=>$cost,'unit_cost'=>$qty>0?round($cost/$qty,4):null,'completed_at'=>date('Y-m-d H:i:s'),'output_adjustment_id'=>$adj];
                    $this->insert('db_recipe_production_runs',['store_id'=>$this->store(),'recipe_id'=>$batch->recipe_id,'batch_id'=>$batch->batch_id,'planned_qty'=>$batch->planned_qty,'actual_yield'=>$qty,'actual_cost'=>$cost,'staff_id'=>(int)$this->session->userdata('inv_userid'),'notes'=>$note?:'Garden batch completed; inputs issued earlier.','run_date'=>date('Y-m-d')]);$status='completed';
                }
            }
            if(!in_array($action,['material','labour','other','loss'],true))$this->entry($batch,$action,$qty,0,0,null,$update['output_adjustment_id']??null,$note,$key,$hash);
            $this->db->where('batch_id',$batch->batch_id)->where('store_id',$this->store())->update('db_garden_production_context',$update+['version'=>$version+1]);$this->db->where('id',$batch->batch_id)->where('store_id',$this->store())->update('db_production_batches',['status'=>$status,'updated_at'=>date('Y-m-d H:i:s')]);return true;
        });
    }
}
