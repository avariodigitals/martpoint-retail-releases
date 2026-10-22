<?php defined('BASEPATH') OR exit('No direct script access allowed');
/** Shared catalogue hierarchy. Existing item/service category IDs remain unchanged. */
class Category_tree_model extends CI_Model {
    private $cache=[];
    public function __construct(){parent::__construct();if(!$this->db->field_exists('parent_id','db_category'))$this->db->query('ALTER TABLE db_category ADD parent_id INT NULL DEFAULT NULL, ADD KEY category_parent(store_id,parent_id)');}
    public function rows($storeId){
        $storeId=(int)$storeId;if(!isset($this->cache[$storeId]))$this->cache[$storeId]=$this->db->query('SELECT id,store_id,parent_id,category_name,category_image,status FROM db_category WHERE store_id=? ORDER BY category_name,id',[$storeId])->result();return $this->cache[$storeId];
    }
    public function clear(){ $this->cache=[]; }
    public function ids($storeId,$root,$activeOnly=false){
        $map=[];foreach($this->rows($storeId) as $r)$map[(int)$r->id]=$r;$root=(int)$root;if(!isset($map[$root]))return [0];$ids=[];$queue=[$root];
        while($queue){$id=array_shift($queue);if(isset($ids[$id]))continue;if($activeOnly&&(int)$map[$id]->status!==1)continue;$ids[$id]=$id;foreach($map as $r)if((int)$r->parent_id===$id)$queue[]=(int)$r->id;}return array_values($ids)?:[0];
    }
    public function ancestors($storeId,$id){
        $map=[];foreach($this->rows($storeId) as $r)$map[(int)$r->id]=$r;$ids=[];$id=(int)$id;while($id&&isset($map[$id])&&!isset($ids[$id])){$ids[$id]=$id;$id=(int)$map[$id]->parent_id;}return array_values($ids)?:[0];
    }
    public function options($storeId,$exclude=0){
        $map=[];foreach($this->rows($storeId) as $r)$map[(int)$r->id]=$r;$blocked=$exclude?array_flip($this->ids($storeId,$exclude)):[];$out=[];
        foreach($map as $id=>$r){if(isset($blocked[$id]))continue;$names=[$r->category_name];$seen=[$id=>true];$parent=(int)$r->parent_id;while($parent&&isset($map[$parent])&&!isset($seen[$parent])){$seen[$parent]=true;array_unshift($names,$map[$parent]->category_name);$parent=(int)$map[$parent]->parent_id;}$copy=clone $r;$copy->category_path=implode(' / ',$names);$out[]=$copy;}
        usort($out,function($a,$b){return strnatcasecmp($a->category_path,$b->category_path);});return $out;
    }
    public function validate_parent($storeId,$id,$parent){
        if(!is_scalar($parent)||!preg_match('/^\d*$/D',(string)$parent))throw new InvalidArgumentException('Choose a valid parent category.');$parent=(int)$parent;if(!$parent)return null;
        $map=[];foreach($this->rows($storeId) as $r)$map[(int)$r->id]=$r;if(!isset($map[$parent])||(int)$map[$parent]->status!==1)throw new InvalidArgumentException('Choose an active parent category from this business.');
        $seen=[];$cursor=$parent;while($cursor){if($cursor===(int)$id||isset($seen[$cursor]))throw new InvalidArgumentException('A category cannot be placed inside itself or its subcategories.');$seen[$cursor]=true;if(!isset($map[$cursor]))throw new InvalidArgumentException('The parent category hierarchy is invalid.');$cursor=(int)$map[$cursor]->parent_id;}
        return $parent;
    }
    public function public_tree($storeId,array $published){
        $map=[];foreach($this->rows($storeId) as $r)$map[(int)$r->id]=$r;$keep=[];
        foreach($published as $row){$id=(int)$row->id;$chain=[];while($id&&isset($map[$id])&&!isset($chain[$id])&&(int)$map[$id]->status===1){$chain[$id]=true;$id=(int)$map[$id]->parent_id;}if($id)continue;$keep+=$chain;}
        $build=function($parent,$seen=[])use(&$build,$map,$keep){$nodes=[];foreach($map as $id=>$row){if(!isset($keep[$id])||(int)$row->parent_id!==$parent||isset($seen[$id]))continue;$node=clone $row;$node->children=$build($id,$seen+[$id=>true]);$nodes[]=$node;}return $nodes;};return $build(0);
    }
}
