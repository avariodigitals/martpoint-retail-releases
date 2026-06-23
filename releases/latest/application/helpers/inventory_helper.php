<?php
 function get_brands_select_list($select_id='',$store_id=''){
 	  $CI =& get_instance();

	   $store_id = (!empty($store_id)) ? $store_id : get_current_store_id();

	   $CI->db->where("store_id",$store_id);

	  $q1=$CI->db->select("*")->where("status=1")->from("db_brands")->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {  //$str.='<option value="">-Select-</option>'; 
	        foreach($q1->result() as $res1)
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
	        	if(!empty($res1->expire_date) && $res1->expire_date != '0000-00-00' && $expiry_settings->stop_selling_expired == 1 && $res1->expire_date < date('Y-m-d')){
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
	  
	  $q1=$CI->db->select("*")->where("status=1")->from("db_units")->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {  $str.='<option value="">-Select-</option>'; 
	        foreach($q1->result() as $res1)
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
	  
	  $q1=$CI->db->select("*")->where("status=1")->from("db_tax")->get();
	  $str='';
	   if($q1->num_rows($q1)>0)
	    {  $str.='<option value="">-Select-</option>'; 
	        foreach($q1->result() as $res1)
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

	  $q1=$CI->db->select("*")
	             ->where("status=1")
	             ->where("state_id",$state_id)
	             ->from("db_cities")
	             ->get();
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
 		foreach ($q3->result() as $res3) {
 			$ids[] = $res3->warehouse_id;
 		}
 		$ids = implode(',', $ids);
 		$CI->db->where("id in ($ids)");
 		
 	}


    //if not admin
	  if(!empty($store_id)){
	    $CI->db->where("store_id",$store_id);
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

	  	if($role_id==1 || $role_id==2){
	  		$str.='<option value="">All</option>';
	    	$CI->db->where("store_id",$store_id);
	    }
	    else{//other user
	    		if(permissions('show_all_users_sales_invoices')){
	    			$str.='<option value="">All</option>';
      		}
	    		else{
	    			$CI->db->where("id",$CI->session->userdata('inv_userid'));
	    		}
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
	        $selected = ($select_id==$res1->id)? 'selected' : '';
	        $str.="<option $selected value='".$res1->id."'>".$res1->role_name."</option>";
	      }
	    }
	    else
	    {
	    	$str.='<option value="">No Records Found</option>'; 
	    }
	    return $str;
 }