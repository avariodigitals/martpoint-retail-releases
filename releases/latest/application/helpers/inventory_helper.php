<?php
if (!function_exists('mp_shipping_zone_states')) {
 function mp_shipping_zone_states($store_id=0){
  $CI =& get_instance();$states=[];
  if($CI->db->table_exists('ng_states')) $states=$CI->db->select('id,name,capital,type')->from('ng_states')->order_by('name','ASC')->get()->result_array();
  if($states)return $states;
  if(!$CI->db->table_exists('db_states'))return [];
  $CI->db->select('id,state AS name')->from('db_states')->where('status',1)->where('country','Nigeria')->where('store_id',NULL)->order_by('state','ASC');
  $states=$CI->db->get()->result_array();
  if(!$states && (int)$store_id>0)$states=$CI->db->select('id,state AS name')->from('db_states')->where('status',1)->where('country','Nigeria')->where('store_id',(int)$store_id)->order_by('state','ASC')->get()->result_array();
  if(!$states)$states=$CI->db->select('id,state AS name')->from('db_states')->where('status',1)->where('country','Nigeria')->order_by('state','ASC')->get()->result_array();
  $seen=[];$out=[];
  foreach($states as $state){$name=trim((string)($state['name']??''));$key=strtolower($name);if($name===''||isset($seen[$key]))continue;$seen[$key]=true;$out[]=['id'=>'ng-legacy-'.(int)$state['id'],'name'=>$name,'capital'=>'','type'=>'state'];}
  return $out;
 }
}

if (!function_exists('mp_shipping_zone_editor_rows')) {
 function mp_shipping_zone_editor_rows($json){
  $rows=json_decode((string)$json,true);if(!is_array($rows)||!$rows)return [['name'=>'','country'=>'','fee'=>'','min_order'=>'','free_over'=>'','states'=>[],'cities'=>[],'wildcard'=>false]];
  $groups=[];
  foreach($rows as $index=>$row){if(!is_array($row))continue;
   $name=trim((string)($row['name']??''));$country=trim((string)($row['country']??''));$fee=(float)($row['fee']??0);$min=(float)($row['min_order']??0);$free=(float)($row['free_over']??0);
   $key=$name!==''?hash('sha256',json_encode([$name,$country,$fee,$min,$free])):'legacy-'.$index;
   if(!isset($groups[$key]))$groups[$key]=['name'=>$name,'country'=>$country,'fee'=>$fee,'min_order'=>$min,'free_over'=>$free,'states'=>[],'cities'=>[],'wildcard'=>false];
   $state=trim((string)($row['state']??''));$city=trim((string)($row['city']??''));
   if($state!=='')$groups[$key]['states'][$state]=$state;
   if($city==='*')$groups[$key]['wildcard']=true;
   elseif($city!=='')$groups[$key]['cities'][$state.'|'.$city]=['name'=>$city,'state'=>$state];
  }
  foreach($groups as &$group){$group['states']=array_values($group['states']);$group['cities']=array_values($group['cities']);}unset($group);
  return array_values($groups)?:[['name'=>'','country'=>'','fee'=>'','min_order'=>'','free_over'=>'','states'=>[],'cities'=>[],'wildcard'=>false]];
 }
}

 function get_brands_select_list($select_id='',$store_id=''){
 	  $CI =& get_instance();

	   $store_id = (!empty($store_id)) ? $store_id : get_current_store_id();

	   $CI->db->where("store_id",$store_id);

	  // Prefer the store's flagged default, then fall back to first-created.
	  if($CI->db->field_exists('is_default','db_brands')){
	    $CI->db->order_by('is_default','desc');
	  }
	  $CI->db->order_by('id','asc');

	  $q1=$CI->db->select("*")->where("status=1")->from("db_brands")->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {
	        // When no brand is saved yet AND a default is flagged, pre-select it.
	        // If nothing is flagged, behaviour is unchanged (no option marked selected).
	        $rows = $q1->result();
	        if(empty($select_id) && $CI->db->field_exists('is_default','db_brands')){
	          foreach($rows as $res1){
	            if(!empty($res1->is_default)){ $select_id = $res1->id; break; }
	          }
	        }
	        //$str.='<option value="">-Select-</option>';
	        foreach($rows as $res1)
	      {
	        $selected = ($select_id==$res1->id)? 'selected' : '';
	        $str.="<option $selected value='".$res1->id."'>".$res1->brand_name."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>';
	    }
	    return $str;
 }
  function get_items_select_list($select_id='',$store_id='',$item_type=''){
 	  $CI =& get_instance();

	  //if not admin
	  if(!empty($store_id)){
	    $CI->db->where("store_id",$store_id);
	  }

	  if($item_type == 'Services'){
	  	$CI->db->where("service_bit=1");
	  }
	  if($item_type == 'Items'){
	  	$CI->db->where("service_bit=''");
	  }

	  $q1=$CI->db->select("*")->where("status=1")->from("db_items")->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {  
	    	$str='';
	        foreach($q1->result() as $res1)
	      { 
	        // Filter expired items from sales dropdown
	        try {
	        	$CI->load->model('expiry_settings_model');
	        	$expiry_settings = $CI->expiry_settings_model->get_settings();
	        	if(is_valid_date($res1->expire_date) && $expiry_settings->stop_selling_expired == 1 && $res1->expire_date < date('Y-m-d')){
	        		continue; // skip expired item
	        	}
	        } catch (Exception $e) { /* Expiry settings not ready yet */ }
	        $selected = ($select_id==$res1->id)? 'selected' : '';
	        $str.="<option $selected value='".$res1->id."'>".$res1->item_name."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>'; 
	    }
	    return $str;
 }
  function get_categories_select_list($select_id='',$store_id=''){
 	  $CI =& get_instance();

	  $store_id = (!empty($store_id)) ? $store_id : get_current_store_id();

	  $CI->db->where("store_id",$store_id);
	  
	  $CI->db->select("*")->where("status=1")->from("db_category");


	  $q1=$CI->db->get();

	  $str='';
	   if($q1->num_rows($q1)>0)
	    {  

	    	//$str.='<option value="">-Select-</option>'; 

	        foreach($q1->result() as $res1)
	      { 
	        $selected = ($select_id==$res1->id)? 'selected' : '';
	        $str.="<option $selected value='".$res1->id."'>".$res1->category_name."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>'; 
	    }
	    return $str;
 }

 function get_payment_types_select_list($select_id='',$store_id=''){
 	  $CI =& get_instance();

	  $store_id = (!empty($store_id)) ? $store_id : get_current_store_id();

	  $CI->db->where("store_id",$store_id);
	  
	  $q1=$CI->db->select("*")->where("status=1")->from("db_paymenttypes")->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {  

	    	//$str.='<option value="">-Select-</option>'; 

	        foreach($q1->result() as $res1)
	      { 
	        $selected = ($select_id==$res1->payment_type)? 'selected' : '';
	        $str.="<option $selected value='".$res1->payment_type."'>".$res1->payment_type."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>'; 
	    }
	    return $str;
 }
 function get_units_select_list($select_id=''){
 	  $CI =& get_instance();

	  //if not admin
	  //if(!is_admin()){
	    $CI->db->where("store_id",get_current_store_id());
	  //}

	  // Prefer the store's flagged default, then fall back to first-created.
	  if($CI->db->field_exists('is_default','db_units')){
	    $CI->db->order_by('is_default','desc');
	  }
	  $CI->db->order_by('id','asc');

	  $q1=$CI->db->select("*")->where("status=1")->from("db_units")->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {
	        // When no unit is saved yet AND a default is flagged, pre-select it.
	        // If nothing is flagged, behaviour is unchanged (-Select- placeholder shown).
	        $rows = $q1->result();
	        if(empty($select_id) && $CI->db->field_exists('is_default','db_units')){
	          foreach($rows as $res1){
	            if(!empty($res1->is_default)){ $select_id = $res1->id; break; }
	          }
	        }
	        $str.='<option value="">-Select-</option>';
	        foreach($rows as $res1)
	      {
	        $selected = ($select_id==$res1->id)? 'selected' : '';
	        $str.="<option $selected value='".$res1->id."'>".$res1->unit_name."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>';
	    }
	    return $str;
 }
 function get_tax_select_list($select_id='',$store_id=''){
 	  $CI =& get_instance();

 	  //if not admin
	  if(!empty($store_id)){
	    $CI->db->where("store_id",$store_id);
	  }

	  //if not admin
	 // if(!is_admin()){
	    $CI->db->where("store_id",get_current_store_id());
	  //}

	  // Prefer the store's flagged default, then fall back to first-created.
	  if($CI->db->field_exists('is_default','db_tax')){
	    $CI->db->order_by('is_default','desc');
	  }
	  $CI->db->order_by('id','asc');

	  $q1=$CI->db->select("*")->where("status=1")->from("db_tax")->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {
	        // When no tax is saved yet AND a default is flagged, pre-select it.
	        // If nothing is flagged, behaviour is unchanged (-Select- placeholder shown).
	        $rows = $q1->result();
	        if(empty($select_id) && $CI->db->field_exists('is_default','db_tax')){
	          foreach($rows as $res1){
	            if(!empty($res1->is_default)){ $select_id = $res1->id; break; }
	          }
	        }

	        $str.='<option value="">-Select-</option>';
	        foreach($rows as $res1)
	      {
	        $selected = ($select_id==$res1->id)? 'selected' : '';
	        $str.="<option $selected data-tax='".$res1->tax."' value='".$res1->id."'>".$res1->tax_name."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>';
	    }
	    return $str;
 }

   function get_country_select_list($select_id='',$show_select_option=false){
 	  $CI =& get_instance();

	  // West Africa + UK + US whitelist
	  $allowed = array(
	    'Nigeria','Ghana','Benin','Burkina Faso','Cape Verde',
	    "Cote d'Ivoire","Côte d'Ivoire",'Gambia','Guinea',
	    'Guinea-Bissau','Liberia','Mali','Mauritania','Niger',
	    'Senegal','Sierra Leone','Togo','Cameroon',
	    'United Kingdom','UK','Great Britain',
	    'United States','USA','United States of America','US'
	  );

	  $q1=$CI->db->select("*")->where("status=1")
	             ->where_in("country",$allowed)
	             ->from("db_country")->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {  
	    	 $str.= ($show_select_option) ? '<option value="">-Select-</option>' : ''; 
	        foreach($q1->result() as $res1)
	      { 
	        $selected = ($select_id==$res1->id)? 'selected' : '';
	        $str.="<option $selected value='".$res1->id."'>".$res1->country."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>'; 
	    }
	    return $str;
 }

 function get_state_select_list_by_country($country_name='',$select_id=''){
 	  $CI =& get_instance();
	  // Prefer one shared canonical Nigeria state list over duplicated
	  // workspace-specific rows. IDs remain db_states IDs for legacy forms.
	  if(strcasecmp(trim((string)$country_name),'Nigeria')===0 && $CI->db->table_exists('ng_states')){
	    $global=$CI->db->select('*')->from('db_states')->where('status',1)->where('store_id',NULL)
	      ->where('country','Nigeria')->order_by('state','asc')->get();
	    if($global->num_rows()>0){
	      $str='<option value="">Select State</option>';
	      foreach($global->result() as $res){
	        $selected=((string)$select_id===(string)$res->id)?'selected':'';
	        $str.='<option '.$selected.' value="'.htmlspecialchars((string)$res->id,ENT_QUOTES,'UTF-8').'">'.htmlspecialchars((string)$res->state,ENT_QUOTES,'UTF-8').'</option>';
	      }
	      return $str;
	    }
	  }

	  $q1=$CI->db->select("*")
	             ->where("status=1")
	             ->where("country",$country_name)
	             ->from("db_states")
	             ->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {  $str.='<option value="">Select State</option>'; 
	        foreach($q1->result() as $res1)
	      { 
	        $selected = ($select_id==$res1->id)? 'selected' : '';
	        $str.="<option $selected value='".$res1->id."'>".$res1->state."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>'; 
	    }
	    return $str;
 }

 function get_city_select_list($state_id='',$select_id=''){
 	  $CI =& get_instance();

	  if(empty($state_id)){
	    return '<option value="">Select State First</option>';
	  }
	  // Nigeria's complete place source is shared and searchable in the database;
	  // legacy address fields use the curated shortlist to keep forms responsive.
	  if($CI->db->table_exists('ng_places') && $CI->db->table_exists('ng_states')){
	    $state=$CI->db->select('state,state_code,country')->from('db_states')->where('id',(int)$state_id)->where('status',1)->get()->row();
	    if($state && strcasecmp(trim((string)$state->country),'Nigeria')===0 && strpos((string)$state->state_code,'NG')===0){
	      $adminCode=substr((string)$state->state_code,2);
	      $geo=$CI->db->select('id')->from('ng_states')->where('geonames_admin1_code',$adminCode)->get()->row();
      if(!$geo){
        $stateName=trim((string)$state->state);
        $CI->db->from('ng_states');
        if(strcasecmp($stateName,'FCT')===0){$CI->db->where('id','ng-federal-capital-territory');}
        else{$CI->db->where('name',$stateName);}
        $geo=$CI->db->select('id')->get()->row();
      }
	      if($geo){
	        $places=$CI->db->select('name,MAX(is_state_capital) AS is_capital')->from('ng_places')
	          ->where('state_id',$geo->id)->where('is_shortlisted',1)->group_by('name')
	          ->order_by('is_capital','DESC')->order_by('name','ASC')->get()->result();
	        $str='<option value="">Select City / Town</option>';
	        $hasSelected=false;
	        foreach($places as $place){
	          $name=(string)$place->name;$selected=((string)$select_id===$name)?'selected':'';
	          if($selected)$hasSelected=true;
	          $safe=htmlspecialchars($name,ENT_QUOTES,'UTF-8');
	          $str.='<option '.$selected.' value="'.$safe.'">'.$safe.'</option>';
	        }
	        if(trim((string)$select_id)!=='' && !$hasSelected){
	          $safe=htmlspecialchars((string)$select_id,ENT_QUOTES,'UTF-8');
	          $str.='<option selected value="'.$safe.'">'.$safe.' (saved)</option>';
	        }
	        return $str;
	      }
	    }
	  }

	  $q1=$CI->db->select("*")
	             ->where("status=1")
	             ->where("state_id",$state_id)
	             ->from("db_cities")
	             ->get();
	  // Fallback: if no cities mapped to this state, show all active cities
	  if($q1->num_rows()==0){
	    $q1=$CI->db->select("*")
	               ->where("status=1")
	               ->from("db_cities")
	               ->get();
	  }
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {  $str.='<option value="">Select City</option>'; 
	        foreach($q1->result() as $res1)
	      { 
	        $selected = ($select_id==$res1->city)? 'selected' : '';
	        $str.="<option $selected value='".$res1->city."'>".$res1->city."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>'; 
	    }
	    return $str;
 }
 function get_state_select_list($select_id=''){
 	  $CI =& get_instance();
	  // Registration has no country selector, so use the shared Nigerian state
	  // catalogue instead of duplicating per-workspace rows when it is installed.
	  if($CI->db->table_exists('ng_states')){
	    $global=$CI->db->select('*')->from('db_states')->where('status',1)->where('store_id',NULL)
	      ->where('country','Nigeria')->order_by('state','asc')->get();
	    if($global->num_rows()>0){
	      $str='<option value="">Select State</option>';
	      foreach($global->result() as $res){
	        $selected=((string)$select_id===(string)$res->id)?'selected':'';
	        $str.='<option '.$selected.' value="'.htmlspecialchars((string)$res->id,ENT_QUOTES,'UTF-8').'">'.htmlspecialchars((string)$res->state,ENT_QUOTES,'UTF-8').'</option>';
	      }
	      return $str;
	    }
	  }

	  //if not admin
	  /*if(!is_admin()){
	    $CI->db->where("store_id",get_current_store_id());
	  }*/

	  $q1=$CI->db->select("*")->where("status=1")->from("db_states")->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {  $str.='<option value="">Select State</option>'; 
	        foreach($q1->result() as $res1)
	      { 
	        $selected = ($select_id==$res1->id)? 'selected' : '';
	        $str.="<option $selected value='".$res1->id."'>".$res1->state."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>'; 
	    }
	    return $str;
 }
 function get_customers_select_list($select_id='',$store_id=''){
 	  $CI =& get_instance();

	  //if not admin
	  if(!empty($store_id)){
	    $CI->db->where("store_id",$store_id);
	  }

	  $q1=$CI->db->select("*")->where("status=1")->from("db_customers")->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {  
	    	$str='';
	        foreach($q1->result() as $res1)
	      { 
	      	//$customer_previous_due = $res1->sales_due +$res1->opening_balance;
	      	//$customer_previous_due = store_number_format($customer_previous_due,0);

	      	$customer_previous_due = $res1->sales_due +$res1->opening_balance;
	      	
	      	$customer_previous_due -=get_paid_cob($res1->id);

	      	$tot_advance = store_number_format($res1->tot_advance,0);
	      	
	        $selected = ($select_id==$res1->id)? 'selected' : '';
	        $str.="<option $selected data-delete_bit='".$res1->delete_bit."' data-tot_advance='".$tot_advance."' data-previous_due='".store_number_format($customer_previous_due,false)."' value='".$res1->id."'>".$res1->customer_code."-".$res1->customer_name."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>'; 
	    }
	    return $str;
 }

  function get_suppliers_select_list($select_id='',$store_id='',$show_select=false){
 	  $CI =& get_instance();

	  //if not admin
	  if(!empty($store_id)){
	    $CI->db->where("store_id",$store_id);
	  }
	  $supplier_profile = function_exists('mp_get_store_profile') ? mp_get_store_profile((int)$store_id) : array();
	  $supplier_industry = strtolower((string)($supplier_profile['industry_type'] ?? ''));
	  if(in_array($supplier_industry, array('pharmacy','medical_store','healthcare'), true) && $CI->db->field_exists('created_by','db_suppliers')){
	    $CI->db->group_start()
	      ->where('created_by !=','demo')
	      ->or_where_in('supplier_name', array('Global Pharma Distribution','HealthLine Medical','CarePlus Wholesale'))
	      ->group_end();
	  }

	  $q1=$CI->db->select("*")->where("status=1")->from("db_suppliers")->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {  
			if($show_select==true){
	    		$str.='<option value="">-Select-</option>'; 
	    	}
	        foreach($q1->result() as $res1)
	      { 
	        $selected = ($select_id==$res1->id)? 'selected' : '';
	        $str.="<option $selected value='".$res1->id."'>".$res1->supplier_code."-".$res1->supplier_name."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>'; 
	    }
	    return $str;
 }
 function get_expense_category_select_list($select_id='',$store_id=''){
 	  $CI =& get_instance();

	  //if not admin
	  if(!empty($store_id)){
	    $CI->db->where("store_id",$store_id);
	  }

	  $q1=$CI->db->select("*")->where("status=1")->from("db_expense_category")->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {  
	    	//$str.='<option value="">-Select-</option>'; 
	        foreach($q1->result() as $res1)
	      { 
	        $selected = ($select_id==$res1->id)? 'selected' : '';
	        $str.="<option $selected value='".$res1->id."'>".$res1->category_name."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>'; 
	    }
	    return $str;
 }
  function get_warehouse_select_list($select_id='',$store_id='',$show_select=false){

  	$defaultWarehouseId = getDefaultWarehouseId();

 	  $CI =& get_instance();

	  

	  //Only Allowed Warehouse show to loged in user
 	if(!is_admin() && !is_store_admin()){
 		//Find the previllaged wareshouses to the user
 		$q3 = $CI->db->select("warehouse_id")->where("user_id",get_current_user_id())->get("db_userswarehouses");
 		$ids = array();
 		if($q3){ foreach ($q3->result() as $res3) {
 			$ids[] = $res3->warehouse_id;
 		} }

		/*
		 * A user with NO branch assignment previously produced "id in ()",
		 * which is invalid SQL — the query returned false and the num_rows()
		 * call below then fataled with "Call to a member function num_rows() on
		 * bool", 500-ing the whole screen. A warehouse-less user is a normal
		 * state (a single-branch clinic, or a role that needs no branch), so
		 * return an empty option list instead of a broken query.
		 */
		if(empty($ids)){
			return ($show_select ? '<option value="">-Select-</option>' : '')
				. '<option value="">-</option>';
		}

		$ids = implode(',', array_map('intval', $ids));
	  }
	  else{
	  	$CI->db->where("store_id",get_current_store_id());
	  }

	  $query = $CI->db->select("*")->where("status=1")->from("db_warehouse");

	  //echo $CI->db->get_compiled_select();exit;
	  $q1=$query->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {  
	    	if($show_select==true){
	    		$str.='<option value="">-Select-</option>'; 
	    	}
	        foreach($q1->result() as $res1)
	      { 
	        $selected = ($select_id==$res1->id)? 'selected' : (($res1->id == $defaultWarehouseId) ? "selected" : '');
	        $str.="<option $selected value='".$res1->id."'>".$res1->warehouse_name."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>'; 
	    }
	    return $str;
 }

  function get_users_select_list($role_id='',$store_id=''){
 	  $CI =& get_instance();

	  $str='';

	  	if(is_cashier() && !permissions('show_all_users_sales_invoices')){
	  		$CI->db->where("id",$CI->session->userdata('inv_userid'));
	    }
	    else{
	    	$str.='<option value="">All</option>';
	    	$store_id = !empty($store_id) ? $store_id : get_current_store_id();
	    	$excluded_users = get_excluded_user_ids($store_id);
	    	$excluded_roles = get_excluded_staff_roles($store_id);
	    	$CI->db->where("store_id",$store_id);
	    	$CI->db->where_not_in('id', $excluded_users);
	    	$CI->db->where_not_in('role_id', $excluded_roles);
	    }

	    

	  $q1=$CI->db->select("*")->where("status=1")->from("db_users")->get();
	  
	   if($q1->num_rows($q1)>0)
	    {  

	    	
	        foreach($q1->result() as $res1)
	      { 
	        //$selected = ($select_id==$res1->id)? 'selected' : '';
	        $str.="<option  value='".$res1->username."'>".ucfirst($res1->username)."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>'; 
	    }
	    return $str;
 }

 function get_cheque_status_select_list($selection=''){
 	  $records = array(
 	  					'Pending',
 	  					'Cleared',
 	  					'Not-Cleared',
 	  					'Waiting',
 	  					'Cancelled',
 	  					);
 	  $str='';
 	  $str.='<option value="">-Select-</option>'; 
 	  foreach($records as $res1)
      { 
        $selected = (strtoupper($selection)==strtoupper($res1))? 'selected' : '';
        $str.="<option $selected value='".$res1."'>".$res1."</option>";
      }
	  return $str;
 }
 function get_coupon_type_select_list($selection=''){
 	  $records = array(
 	  					'Fixed',
 	  					'Percentage',
 	  					);
 	  $str='';
 	  $str.='<option value="">-Select-</option>'; 
 	  foreach($records as $res1)
      { 
        $selected = (strtoupper($selection)==strtoupper($res1))? 'selected' : '';
        $str.="<option $selected value='".$res1."'>".$res1."</option>";
      }
	  return $str;
 }

function get_packages_select_list($select_id=''){
 	  $CI =& get_instance();

	  $q1=$CI->db->select("*")->where("status=1")->from("db_package")->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {  
	    	$str.='<option value="">-Select-</option>'; 
	        foreach($q1->result() as $res1)
	      { 
	        $selected = ($select_id==$res1->id)? 'selected' : '';
	        $str.="<option $selected value='".$res1->id."'>".$res1->package_name."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>'; 
	    }
	    return $str;
 }
 function get_discount_coupon_select_list($select_id=''){
 	  $CI =& get_instance();

	  $q1=$CI->db->select("*")
		->where("status=1")
		->where("store_id",get_current_store_id())
		/*->where("expire_date>='".date("Y-m-d")."'")*/
		->from("db_coupons")->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {  
	    	$str.='<option value="">-Select-</option>'; 
	        foreach($q1->result() as $res1)
	      { 
	        $selected = ($select_id==$res1->id)? 'selected' : '';
	        $str.="<option $selected data-type='".$res1->type."' data-value='".$res1->value."' data-expire_date='".show_date($res1->expire_date)."' value='".$res1->id."'>".$res1->name."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>'; 
	    }
	    return $str;
 }

 function get_super_admin_bank_account_type_select_list($selection=''){
 	  $records = array(
 	  					'Saving',
 	  					'Current',
 	  					);
 	  $str='';
 	  //$str.='<option value="">-Select-</option>'; 
 	  foreach($records as $res1)
      { 
        $selected = (strtoupper($selection)==strtoupper($res1))? 'selected' : '';
        $str.="<option $selected value='".$res1."'>".$res1."</option>";
      }
	  return $str;
 }

 function get_super_admin_bank_status_select_list($selection=''){
 	  $records = array( 1 => 'Active', 0 => 'Disable');
 	  $str='';
 	  //$str.='<option value="">-Select-</option>'; 
 	  foreach($records as $key => $value)
      { 
         $selected = ($selection==$key)? 'selected' : '';
         $str.="<option $selected value='".$key."'>".$value."</option>";
      }
	  return $str;
 }

  function plan_types($selection=''){
 	  $records = array(
 	  					'Monthly',
 	  					'Annually',
 	  					);
 	  $str='';
 	  //$str.='<option value="">-Select-</option>'; 
 	  foreach($records as $res1)
      { 
        $selected = (strtoupper($selection)==strtoupper($res1))? 'selected' : '';
        $str.="<option $selected value='".$res1."'>".$res1."</option>";
      }
	  return $str;
 }
 function package_type($selection=''){
 	  $records = array(
 	  					'Paid',
 	  					'Free',
 	  					);
 	  $str='';
 	  //$str.='<option value="">-Select-</option>'; 
 	  foreach($records as $res1)
      { 
        $selected = (strtoupper($selection)==strtoupper($res1))? 'selected' : '';
        $str.="<option $selected value='".$res1."'>".$res1."</option>";
      }
	  return $str;
 }

 function get_roles_select_list($select_id='',$store_id=''){

 	  $CI =& get_instance();
	  $store_id = !empty($store_id) ? $store_id : get_current_store_id();
	  // Store Admin is a system role and is never assignable as ordinary staff.
	  // Business Owner is a normal tenant role and remains selectable.
	  $reserved_role_ids = store_admin_role_ids($store_id);

 	  
 	  $CI->db->where("store_id",$store_id);

 	  if(is_admin()){
 	  	$CI->db->or_where("store_id",1);
 	  }
 	  

 	  $CI->db->where("id<>",1);

	  $query = $CI->db->select("*")->where("status=1")->from("db_roles");

	  //echo $CI->db->get_compiled_select();exit;
	  $q1=$query->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {
	        foreach($q1->result() as $res1)
	      {
	        // Never let a tenant assign the install-level Store Admin role.
	        // Business Owner is a distinct permission role and can be assigned.
	        if(!is_admin() && in_array((int)$res1->id, $reserved_role_ids, true) && (int)$select_id !== (int)$res1->id){
	        	continue;
	        }
	        // Industry gate: universal roles show everywhere, industry roles
	        // (e.g. Clinical Director) only for stores of that family. The
	        // role already assigned to the user is always kept, so editing an
	        // existing user never loses its current value.
	        if($select_id != $res1->id && !mp_role_visible($res1->role_name, $store_id)){
	        	continue;
	        }
	        $selected = ($select_id==$res1->id)? 'selected' : '';
	        $str.="<option $selected value='".$res1->id."'>".$res1->role_name."</option>";
	      }
	    }
	    if($str==='')
	    {
	    	$str.='<option value="">No Records Found</option>';
	    }
	    return $str;
 }
