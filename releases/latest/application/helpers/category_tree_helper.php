<?php defined('BASEPATH') OR exit('No direct script access allowed');
function mp_category_tree_model(){ $ci=&get_instance();$ci->load->model('category_tree_model');return $ci->category_tree_model; }
function mp_category_ids($id,$storeId=null,$activeOnly=false){return mp_category_tree_model()->ids($storeId?:get_current_store_id(),$id,$activeOnly);}
function mp_category_filter($column,$id,$storeId=null,$activeOnly=false){$ci=&get_instance();$ci->db->where_in($column,mp_category_ids($id,$storeId,$activeOnly));}
function mp_category_sql($column,$id,$storeId=null){if(!preg_match('/^[a-zA-Z0-9_.`]+$/D',$column))throw new InvalidArgumentException('Invalid category column');return $column.' IN ('.implode(',',array_map('intval',mp_category_ids($id,$storeId))).')';}

function mp_category_ancestors($id,$storeId=null){return mp_category_tree_model()->ancestors($storeId?:get_current_store_id(),$id);}
