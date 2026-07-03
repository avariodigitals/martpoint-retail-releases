<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Items extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('items_model','items');
	}
	
	public function index()
	{
		if(!$this->permissions('items_view') && !$this->permissions('services_view')){
			$this->show_access_denied_page();exit;
		}
		$data=$this->data;
		$data['page_title']=$this->lang->line('items_list');
		$this->load->view('items-list',$data);
	}
	public function add()
	{
		$this->permission_check('items_add');
		$data=$this->data;
		$data['page_title']=$this->lang->line('items');
		$data['recipes_list'] = [];
		if (recipe_module() && $this->db->table_exists('db_recipes')) {
			$this->load->model('recipe_model');
			$store_id = get_current_store_id();
			$recipes = $this->db->where('store_id', $store_id)->where('status', 1)->order_by('name')->get('db_recipes')->result();
			foreach ($recipes as $r) {
				$r->cost_per_unit = $this->recipe_model->calculate_cost_per_unit($r->id);
			}
			$data['recipes_list'] = $recipes;
		}
		$this->load->view('items',$data);
	}

	public function newitems(){
		$this->form_validation->set_rules('item_name', 'Item Name', 'trim|required');
		$this->form_validation->set_rules('category_id', 'Category Name', 'trim|required');
		$this->form_validation->set_rules('unit_id', 'Unit', 'trim|required');
		$this->form_validation->set_rules('tax_id', 'Tax', 'trim|required');

		if($this->input->post('item_group')=='Single'){
		$this->form_validation->set_rules('price', 'Item Price', 'trim|required');
		$this->form_validation->set_rules('purchase_price', 'Purchase Price', 'trim|required');
		$this->form_validation->set_rules('sales_price', 'Sales Price', 'trim|required');
		}
		else{
			if($this->input->post('existing_row_count')==1){
				echo "Variants List Not Added, Please Select Variants!!";exit();
			}
		}		
		if ($this->form_validation->run() == TRUE) {
			$product_check = check_subscription_limit('product_limit');
			if($product_check !== true){
				echo $product_check;
				return;
			}
			if(!empty($_FILES['item_image']['name'])){
				$media_check = check_media_storage_limit();
				if($media_check !== true){
					echo $media_check;
					return;
				}
			}
			$result=$this->items->save_record(array('command' =>'save'));
			echo $result;
		} else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}
	}

	//PopUP Modal
	public function addItemFromModal(){

		$this->form_validation->set_rules('m_item_name', 'Item Name', 'trim|required');
		$this->form_validation->set_rules('m_category_id', 'Category Name', 'trim|required');
		$this->form_validation->set_rules('m_unit_id', 'Unit', 'trim|required');
		$this->form_validation->set_rules('m_tax_id', 'Tax', 'trim|required');

		if($this->input->post('item_group')=='Single'){
		$this->form_validation->set_rules('m_price', 'Item Price', 'trim|required');
		$this->form_validation->set_rules('m_purchase_price', 'Purchase Price', 'trim|required');
		$this->form_validation->set_rules('m_sales_price', 'Sales Price', 'trim|required');
		}
		else{
			if($this->input->post('existing_row_count')==1){
				echo "Variants List Not Added, Please Select Variants!!";exit();
			}
		}		
		if ($this->form_validation->run() == TRUE) {
			if(!empty($_FILES['item_image']['name'])){
				$media_check = check_media_storage_limit();
				if($media_check !== true){
					echo $media_check;
					return;
				}
			}
			$modal_post=array(
								'item_name' => $this->input->post('m_item_name'),
								'brand_id' => $this->input->post('m_brand_id'),
								'category_id' => $this->input->post('m_category_id'),
								'unit_id' => $this->input->post('m_unit_id'),
								'tax_id' => $this->input->post('m_tax_id'),
								'price' => $this->input->post('m_price'),
								'purchase_price' => $this->input->post('m_purchase_price'),
								'sales_price' => $this->input->post('m_sales_price'),
								'hsn' => $this->input->post('m_hsn'),
								'sku' => $this->input->post('m_sku'),
								'alert_qty' => $this->input->post('m_alert_qty'),
								'seller_points' => $this->input->post('m_seller_points'),
								'custom_barcode' => $this->input->post('m_custom_barcode'),
								'item_group' => $this->input->post('m_item_group'),
								'description' => $this->input->post('m_description'),
								'discount_type' => $this->input->post('m_discount_type'),
								'discount' => $this->input->post('m_discount'),
								'price' => $this->input->post('m_price'),
								'tax_id' => $this->input->post('m_tax_id'),
								'purchase_price' => $this->input->post('m_purchase_price'),
								'tax_type' => $this->input->post('m_tax_type'),
								'profit_margin' => $this->input->post('m_profit_margin'),
								'sales_price' => $this->input->post('m_sales_price'),
								'mrp' => $this->input->post('m_mrp'),
								'expire_date' => $this->input->post('m_expire_date'),
								'warehouse_id' => $this->input->post('m_warehouse_id'),
								'command' => 'save',
							);
			$result=$this->items->save_record($modal_post);
			echo $result;
		} else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}
	}

	public function update($id){
		$this->belong_to('db_items',$id);
		$this->permission_check('items_edit');
		//Check is direct Access of the variant by id in item ?
		/*$parent_id = $this->db->select("parent_id")->where("store_id",get_current_store_id())->where("id",$id)->get("db_items")->row()->parent_id;
		if(!empty($parent_id)){
			show_error("You can't access variant Item!!", 403, $heading = "Invalid Access!!");
		}*/

		$data=$this->data;
		$this->load->model('items_model');
		$result=$this->items_model->get_details($id,$data);
		$data=array_merge($data,$result);
		$data['page_title']=$this->lang->line('items');
		$data['recipes_list'] = [];
		if (recipe_module() && $this->db->table_exists('db_recipes')) {
			$this->load->model('recipe_model');
			$store_id = get_current_store_id();
			$recipes = $this->db->where('store_id', $store_id)->where('status', 1)->order_by('name')->get('db_recipes')->result();
			foreach ($recipes as $r) {
				$r->cost_per_unit = $this->recipe_model->calculate_cost_per_unit($r->id);
			}
			$data['recipes_list'] = $recipes;
			// If this item is the final product of a recipe but recipe_id is not set, auto-link it
			if (empty($data['recipe_id'])) {
				$linked_recipe = $this->db->where('product_item_id', $id)->get('db_recipes')->row();
				if ($linked_recipe) {
					$data['recipe_id'] = $linked_recipe->id;
					if (empty($data['recipe_margin_pct'])) {
						$data['recipe_margin_pct'] = 30;
					}
				}
			}
		}
		//$data['variant_tbody']=$this->items_model->get_variants_list_in_row($id);
		$this->load->view('items', $data);
	}
	public function update_items(){
		$this->form_validation->set_rules('item_name', 'Item Name', 'trim|required');
		$this->form_validation->set_rules('category_id', 'Category Name', 'trim|required');
		$this->form_validation->set_rules('unit_id', 'Unit', 'trim|required');
		$this->form_validation->set_rules('tax_id', 'Tax', 'trim|required');

		if($this->input->post('item_group')=='Single'){
		$this->form_validation->set_rules('price', 'Item Price', 'trim|required');
		$this->form_validation->set_rules('purchase_price', 'Purchase Price', 'trim|required');
		$this->form_validation->set_rules('sales_price', 'Sales Price', 'trim|required');
		}
		else{
			if($this->input->post('existing_row_count')==1){
				echo "Variants List Not Added, Please Select Variants!!";exit();
			}
		}

		
		if ($this->form_validation->run() == TRUE) {
			$result=$this->items->save_record(array('command'=>'update'));
			echo $result;
		} else {
			echo "Please Fill Compulsory(* marked) Fields.";
		}

	}

	public function get_brand_name($brand_id=''){
		if($brand_id==NULL || $brand_id=='' || $brand_id ==0){
			return;
		}
		return $this->db->query('select brand_name from db_brands where id="'.$brand_id.'"')->row()->brand_name;
	}
	public function ajax_list()
	{
		$warehouse_id = $_REQUEST['warehouse_id'];

		$list = $this->items->get_datatables();
		
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $items) {
			
			$no++;
			$row = array();
			$row[] = '<input type="checkbox" name="checkbox[]" value='.$items->id.' class="checkbox column_checkbox" >';
						

			$row[] = (!empty($items->item_image)) ? "
						<a title='Click for Bigger!' href='".base_url($items->item_image)."' data-toggle='lightbox'>
						<image style='border:1px #72afd2 solid;' src='".base_url(return_item_image_thumb($items->item_image))."' width='75%' height='50%'> </a>" : "
						<image style='border:1px #72afd2 solid;' src='".base_url()."theme/images/no_image.png' title='No Image!' width='75%' height='50%' >";
			
			$row[] = $items->item_code;

			$str = "";

			$str = "<label class='text-blue'>".$items->item_name."</label>";
				if($items->service_bit){
					$str .="<br><b>SAC</b>:".$items->sac;
					$str .="<br><b>HSN</b>:".$items->hsn;
				}
				else{
					$str .="<br><b>HSN</b>:".$items->hsn;
					$str .="<br><b>SKU</b>:".$items->sku;
				}
				

			$row[] = $str;

			$row[] = $items->brand_name;

			$service_or_item_name = ($items->service_bit) ? 'SERVICE' : "ITEM";
			$not_for_sale_badge = ($items->not_for_sale ?? 0) ? " <span class='label label-default'>CONSUMABLE</span>" : "";

			$row[] = $items->category_name."<br>[<label class='text-orange'>".$service_or_item_name."</label>]".$not_for_sale_badge;

			$item_group = '';// (!empty($items->item_group)) ? "<br>[<label class='text-green'>".$items->item_group."</label>]" : '';
			$row[] = $items->unit_name.$item_group;

					 $str='';
					 if(warehouse_module() && warehouse_count()>0 && $items->stock>0){ 
			 			$str= "<i class='fa fa-building-o pointer bg-blue text-dark' title='Click to view Warehouse Wise Stock' data-toggle='tooltip' onclick='view_warehouse_wise_stock_item(".$items->id.")'> </i>";
			 		 }
			$warehouse_ids  = (!empty($warehouse_id)) ? $warehouse_id : get_privileged_warehouses_ids();

			
			$row[] = format_qty(total_available_qty_items_of_warehouse($warehouse_ids,null,$items->id))." $str";

			$row[] = $items->alert_qty;
			$row[] = store_number_format($items->sales_price);
			$row[] = $items->tax_name."<br>(".store_number_format($items->tax)."%)";

				// Expiry status badge
				$expiry_badge = '';
				if(!empty($items->expire_date) && $items->expire_date != '0000-00-00'){
					$today = date('Y-m-d');
					$expiry = $items->expire_date;
					$diff = strtotime($expiry) - strtotime($today);
					$days = round($diff / 86400);
					if($days < 0){
						$expiry_badge = "<span class='label label-danger'>Expired (".abs($days)."d)</span>";
					} else if($days <= 30){
						$expiry_badge = "<span class='label label-warning'>Expiring (".$days."d)</span>";
					} else {
						$expiry_badge = "<span class='label label-success'>Good (".$days."d)</span>";
					}
				}
				$row[] = $expiry_badge;
				$mfg_display = (!empty($items->mfg_date) && $items->mfg_date != '0000-00-00') ? date('d-m-Y', strtotime($items->mfg_date)) : '';
				$row[] = $mfg_display;

			 		if($items->status==1){ 
			 			$str= "<span onclick='update_status(".$items->id.",0)' id='span_".$items->id."'  class='label label-success' style='cursor:pointer'>Active </span>";}
					else{ 
						$str = "<span onclick='update_status(".$items->id.",1)' id='span_".$items->id."'  class='label label-danger' style='cursor:pointer'> Inactive </span>";
					}
			$row[] = $str;		

			 		$str2 = '<div class="btn-group" title="View Account">
										<a class="btn btn-xs btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
											Action <span class="caret"></span>
										</a>
										<ul role="menu" class="dropdown-menu dropdown-light pull-right">';

											$str2.='<li>
												<a style="cursor:pointer" title="View Product History" onclick="view_item_history('.$items->id.')">
													<i class="fa fa-fw fa-eye text-navy"></i>View
												</a>
											</li>';

											if($this->permissions('items_edit') || $this->permissions('services_edit'))
											$str2.='<li>
												<a title="Edit Record ?" href="'.base_url(($items->service_bit)? 'services/update/'.$items->id : 'items/update/'.$items->id).'">
													<i class="fa fa-fw fa-edit text-blue"></i>Edit
												</a>
											</li>';

											if($this->permissions('items_delete')|| $this->permissions('services_delete'))
											$str2.='<li>
												<a style="cursor:pointer" title="Delete Record ?" onclick="delete_items('.$items->id.')">
													<i class="fa fa-fw fa-trash text-red"></i>Delete
												</a>
											</li>
											
										</ul>
									</div>';			
			$row[] = $str2;

			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->items->count_all(),
						"recordsFiltered" => $this->items->count_filtered(),
						"data" => $data,
				);
		//output to json format
		echo json_encode($output);
	}
	public function update_status(){
		$this->permission_check_with_msg('items_edit');
		$id=$this->input->post('id');
		$status=$this->input->post('status');

		$this->load->model('items_model');
		$result=$this->items_model->update_status($id,$status);
		return $result;
	}

	public function delete_items(){
		$this->permission_check_with_msg('items_delete');
		$id=$this->input->post('q_id');
		return $this->items->delete_items_from_table($id);
	}
	public function multi_delete(){
		$this->permission_check_with_msg('items_delete');
		$ids=implode (",",$_POST['checkbox']);
		return $this->items->delete_items_from_table($ids);
	}

	
	public function get_json_items_details(){
		$store_id=$this->input->get('store_id');
		$warehouse_id=$this->input->get('warehouse_id');
		$search_for=$this->input->get('search_for');

		$show_purchase_price = $this->permissions('show_purchase_price');
		$data = array();
		$display_json = array();
			$name = strtolower(trim($_GET['name']));

			if(isset($search_for) && $search_for=='purchase'){
				//$this->db->where('a.service_bit=1');
				$this->db->select("a.service_bit,a.purchase_price,a.id,a.item_name,a.item_code,COALESCE(SUM(a.stock),0) as stock,item_group");
				$this->db->from("db_items as a");

			}
			else if(isset($search_for) && ($search_for=='labels' || $search_for=='sales')){
				$this->db->select("*");
				$this->db->from("db_items as a");
			}
			else{
				$this->db->where('a.service_bit=0');
				$this->db->select("a.service_bit,a.purchase_price,a.id,a.item_name,a.item_code,COALESCE(b.available_qty,0) as stock,item_group");
				$this->db->from("db_items as a");
				$this->db->join("db_warehouseitems as b","b.item_id=a.id",'left');
				$this->db->where("b.warehouse_id=$warehouse_id");
			}
			
			$this->db->where("a.status",1);
			$this->db->where("a.store_id",$store_id);
			// Exclude consumables / raw materials from POS sales
			if(isset($search_for) && $search_for=='sales'){
				$this->db->where("(a.not_for_sale IS NULL OR a.not_for_sale = 0)");
			}
			$this->db->where("(LOWER(a.custom_barcode) LIKE '%$name%' or LOWER(a.item_name) LIKE '%$name%' or LOWER(a.item_code) LIKE '%$name%')");

			$this->db->group_by("a.id");
			$this->db->limit("20");
			//echo $this->db->get_compiled_select();exit();
			$sql =$this->db->get();
			
			// Load expiry settings for sales search
			if(isset($search_for) && $search_for=='sales'){
				$this->load->model('expiry_settings_model');
				$expiry_settings = $this->expiry_settings_model->get_settings($store_id);
				$today = date('Y-m-d');
			}

			foreach ($sql->result() as $res) {
				if($res->item_group!='Variants'){
			      $json_arr["id"] = $res->id;
				  $json_arr["value"] = $res->item_name;
				  $json_arr["label"] = $res->item_name;
				  $json_arr["item_code"] = $res->item_code;
				  $json_arr["stock"] = (isset($search_for) && $search_for=='sales') ? total_available_qty_items_of_warehouse($warehouse_id,$store_id,$res->id) : $res->stock;
				  $json_arr["purchase_price"] = ($show_purchase_price) ? store_number_format($res->purchase_price) : '';
				  $json_arr["service_bit"] = $res->service_bit;
				  $json_arr["package_bit"] = $res->package_bit ?? 0;
				  $json_arr["accept_custom_order"] = $res->accept_custom_order ?? 0;
				  $json_arr["custom_order_fields_json"] = $res->custom_order_fields_json ?? null;
				  $json_arr["commission_type"] = $res->commission_type ?? 'none';
				  $json_arr["commission_value"] = $res->commission_value ?? 0;

				  // Check expiry for sales search
				  if(isset($search_for) && $search_for=='sales'){
				  	$is_expired = false;
				  	if(!empty($res->expire_date) && $res->expire_date != '0000-00-00' && $expiry_settings->stop_selling_expired == 1 && $res->expire_date < $today){
				  		$is_expired = true;
				  		$json_arr["label"] = $res->item_name.' (EXPIRED)';
				  	}
				  	$json_arr["expired"] = $is_expired;
				  }

				  array_push($display_json, $json_arr);
				}
			}

			// Also search db_item_barcodes for barcode, serial, or imei matches
			if(!empty($name)){
				$this->db->select('b.id as barcode_id, b.item_id, b.barcode, b.batch_lot, b.serial_number, b.imei_number, b.warranty_months, b.purchase_price as bc_purchase_price, b.sales_price as bc_sales_price, b.mrp as bc_mrp, b.qty as bc_qty, a.item_name, a.item_code, a.service_bit, a.tax_id, a.tax_type, a.discount_type, a.discount, a.stock as item_stock, a.commission_type, a.commission_value');
				$this->db->from('db_item_barcodes b');
				$this->db->join('db_items a', 'a.id = b.item_id', 'left');
				$this->db->where('a.status', 1);
				$this->db->where('a.store_id', $store_id);
				if(isset($search_for) && $search_for=='sales'){
					$this->db->where("(a.not_for_sale IS NULL OR a.not_for_sale = 0)");
				}
				$this->db->where('b.status', 1);
				$this->db->where("(LOWER(b.barcode) LIKE '%$name%' OR LOWER(b.serial_number) LIKE '%$name%' OR LOWER(b.imei_number) LIKE '%$name%')", null, false);
				$this->db->limit(20);
				$bc_sql = $this->db->get();
				foreach ($bc_sql->result() as $bres) {
					$json_arr = array();
					$json_arr["id"] = $bres->item_id;
					$json_arr["value"] = $bres->item_name;
					$label_extra = '';
					if($bres->barcode) $label_extra .= $bres->barcode;
					if($bres->batch_lot) $label_extra .= ($label_extra ? ' / ' : '') . $bres->batch_lot;
					if($bres->serial_number) $label_extra .= ($label_extra ? ' / ' : '') . 'S/N:' . $bres->serial_number;
					if($bres->imei_number) $label_extra .= ($label_extra ? ' / ' : '') . 'IMEI:' . $bres->imei_number;
					$json_arr["label"] = $bres->item_name . ($label_extra ? ' [' . $label_extra . ']' : '');
					$json_arr["item_code"] = $bres->item_code;
					$json_arr["stock"] = ($bres->bc_qty > 0) ? $bres->bc_qty : $bres->item_stock;
					$json_arr["purchase_price"] = ($show_purchase_price) ? store_number_format($bres->bc_purchase_price) : '';
					$json_arr["service_bit"] = $bres->service_bit;
					$json_arr["package_bit"] = $bres->package_bit ?? 0;
					$json_arr["accept_custom_order"] = $bres->accept_custom_order ?? 0;
					$json_arr["custom_order_fields_json"] = $bres->custom_order_fields_json ?? null;
					$json_arr["barcode"] = $bres->barcode;
					$json_arr["batch_lot"] = $bres->batch_lot;
					$json_arr["barcode_price"] = store_number_format($bres->bc_sales_price);
					$json_arr["barcode_mrp"] = store_number_format($bres->bc_mrp);
					$json_arr["barcode_pprice"] = store_number_format($bres->bc_purchase_price);
					$json_arr["barcode_id"] = $bres->barcode_id;
					$json_arr["serial_number"] = $bres->serial_number;
					$json_arr["imei_number"] = $bres->imei_number;
					$json_arr["warranty_months"] = $bres->warranty_months;
					$json_arr["commission_type"] = $bres->commission_type ?? 'none';
					$json_arr["commission_value"] = $bres->commission_value ?? 0;
					if(isset($search_for) && $search_for=='sales'){
						$json_arr["expired"] = false;
					}
					array_push($display_json, $json_arr);
				}
			}

		echo json_encode($display_json);exit;
	}

	/* Returns all items for offline sync (IndexedDB caching) */
	public function sync_items_for_offline(){
		$store_id=$this->input->get('store_id');
		$warehouse_id=$this->input->get('warehouse_id');
		$show_purchase_price = $this->permissions('show_purchase_price');
		$display_json = array();
		
		$this->db->select("a.service_bit,a.purchase_price,a.id,a.item_name,a.item_code,COALESCE(SUM(a.stock),0) as stock,item_group");
		$this->db->from("db_items as a");
		$this->db->where("a.status",1);
		$this->db->where("a.store_id",$store_id);
		$this->db->where("(a.not_for_sale IS NULL OR a.not_for_sale = 0)");
		$this->db->group_by("a.id");
		$this->db->limit(5000);
		$sql = $this->db->get();
		
		foreach ($sql->result() as $res) {
			if($res->item_group!='Variants'){
			  $json_arr = array();
			  $json_arr["id"] = $res->id;
			  $json_arr["value"] = $res->item_name;
			  $json_arr["label"] = $res->item_name;
			  $json_arr["item_code"] = $res->item_code;
			  $json_arr["stock"] = total_available_qty_items_of_warehouse($warehouse_id,$store_id,$res->id);
			  $json_arr["purchase_price"] = ($show_purchase_price) ? store_number_format($res->purchase_price) : '';
			  $json_arr["service_bit"] = $res->service_bit;
			  $json_arr["barcode"] = '';
			  $json_arr["batch_lot"] = '';
			  $json_arr["barcode_price"] = '';
			  $json_arr["barcode_mrp"] = '';
			  $json_arr["barcode_pprice"] = '';
			  array_push($display_json, $json_arr);
			}
		}
		echo json_encode($display_json);exit;
	}

	public function labels($purchase_id=''){
		$this->permission_check('print_labels');
		$data=$this->data;
		$data['page_title']=$this->lang->line('print_labels');
		$data['purchase_id']=$purchase_id;
		$this->load->view('labels',$data);
	}

	/*Labels Print request*/
	public function return_row_with_data($rowcount,$item_id){
		echo $this->items->get_items_info($rowcount,$item_id);
	}

	public function preview_labels(){
		echo $this->items->preview_labels();
	}

	//GET Labels from Purchase Invoice
	public function show_labels($purchase_id=''){
		$i=1;
		$result='';
		$q2=$this->db->query("select item_id,purchase_qty from db_purchaseitems where purchase_id='$purchase_id'");
		if($q2->num_rows()>0){
			
			foreach ($q2 -> result() as $res2) {
				$result.= $this->items->get_purchase_items_info($i++,$res2->item_id,$res2->purchase_qty);
			}
		}
		echo $result;
	}

	public function get_json_variant_details(){
		
		$data = array();
		$display_json = array();
			$name = strtolower(trim($_GET['name']));

				$this->db->select("id,variant_name,description");
				$this->db->from("db_variants");
				$this->db->where("(UPPER(variant_name) LIKE UPPER('%$name%') OR (UPPER(description) LIKE UPPER('%$name%')))");
				$this->db->where("status=1");
				$this->db->where("store_id",get_current_store_id());
			$this->db->limit("10");
			//$this->db->get_compiled_select();exit;
			$sql =$this->db->get();
			
			foreach ($sql->result() as $res) {
			      $json_arr["id"] = $res->id;
				  $json_arr["variant_name"] = $res->variant_name;
				  $json_arr["description"] = $res->description;
				  array_push($display_json, $json_arr);
			}
		echo json_encode($display_json);exit;
	}
	public function return_variant_data_in_row($rowcount,$item_id){
		echo $this->items->return_variant_data_in_row($rowcount,$item_id);
	}

	public function getItems($id=''){
		echo $this->items->getItemsJson($id);
	}

	public function get_item_history($item_id){
		$this->belong_to('db_items',$item_id);
		$item = $this->db->where('id',$item_id)->get('db_items')->row();
		if(!$item){
			echo json_encode(['status'=>'error','message'=>'Item not found']);exit;
		}

		$html = '<div class="row">';
		
		// Item Basic Info
		$html .= '<div class="col-md-12">';
		$html .= '<div class="box box-default">';
		$html .= '<div class="box-header with-border"><h3 class="box-title"><i class="fa fa-tag"></i> '.$item->item_name.'</h3></div>';
		$html .= '<div class="box-body">';
		$html .= '<div class="row">';
		$html .= '<div class="col-md-3"><b>Code:</b> '.$item->item_code.'</div>';
		$html .= '<div class="col-md-3"><b>Barcode:</b> '.($item->custom_barcode ?: '-').'</div>';
		$html .= '<div class="col-md-3"><b>Stock:</b> '.$item->stock.'</div>';
		$html .= '<div class="col-md-3"><b>Price:</b> '.store_number_format($item->sales_price).'</div>';
		$html .= '</div>';
		$html .= '</div></div></div>';

		// Barcode / Batch / Unit Stock
		$barcodes = $this->db->where('item_id',$item_id)->where('status',1)->get('db_item_barcodes')->result();
		$html .= '<div class="col-md-12">';
		$html .= '<div class="box box-info">';
		$html .= '<div class="box-header with-border"><h3 class="box-title"><i class="fa fa-cubes"></i> Unit / Batch Stock</h3></div>';
		$html .= '<div class="box-body table-responsive no-padding">';
		if(!empty($barcodes)){
			$html .= '<table class="table table-bordered table-striped table-condensed">';
			$html .= '<thead><tr><th>Barcode</th><th>Batch</th><th>Serial</th><th>IMEI</th><th>Purchase</th><th>Wholesale</th><th>Retail</th><th>Qty</th><th>Warranty</th></tr></thead>';
			$html .= '<tbody>';
			foreach($barcodes as $bc){
				$html .= '<tr>';
				$html .= '<td>'.($bc->barcode ?: '-').'</td>';
				$html .= '<td>'.($bc->batch_lot ?: '-').'</td>';
				$html .= '<td>'.($bc->serial_number ?: '-').'</td>';
				$html .= '<td>'.($bc->imei_number ?: '-').'</td>';
				$html .= '<td>'.store_number_format($bc->purchase_price).'</td>';
				$html .= '<td>'.store_number_format($bc->sales_price).'</td>';
				$html .= '<td>'.store_number_format($bc->mrp).'</td>';
				$html .= '<td>'.$bc->qty.'</td>';
				$html .= '<td>'.($bc->warranty_months ? $bc->warranty_months.' mo' : '-').'</td>';
				$html .= '</tr>';
			}
			$html .= '</tbody></table>';
		} else {
			$html .= '<div class="alert alert-info">No batch / unit records found.</div>';
		}
		$html .= '</div></div></div>';

		// Sales History
		$this->db->select('s.sales_code, s.sales_date, c.customer_name, si.sales_qty, si.unit_total_cost, si.sold_serial_number, si.sold_imei_number');
		$this->db->from('db_salesitems si');
		$this->db->join('db_sales s', 's.id = si.sales_id', 'left');
		$this->db->join('db_customers c', 'c.id = s.customer_id', 'left');
		$this->db->where('si.item_id', $item_id);
		$this->db->order_by('s.sales_date', 'DESC');
		$this->db->limit(20);
		$sales = $this->db->get()->result();

		$html .= '<div class="col-md-12">';
		$html .= '<div class="box box-success">';
		$html .= '<div class="box-header with-border"><h3 class="box-title"><i class="fa fa-shopping-cart"></i> Recent Sales (last 20)</h3></div>';
		$html .= '<div class="box-body table-responsive no-padding">';
		if(!empty($sales)){
			$html .= '<table class="table table-bordered table-striped table-condensed">';
			$html .= '<thead><tr><th>Invoice</th><th>Date</th><th>Customer</th><th>Qty</th><th>Unit Price</th><th>Serial</th><th>IMEI</th></tr></thead>';
			$html .= '<tbody>';
			foreach($sales as $sale){
				$html .= '<tr>';
				$html .= '<td>'.$sale->sales_code.'</td>';
				$html .= '<td>'.$sale->sales_date.'</td>';
				$html .= '<td>'.($sale->customer_name ?: 'Walk-in').'</td>';
				$html .= '<td>'.$sale->sales_qty.'</td>';
				$html .= '<td>'.store_number_format($sale->unit_total_cost).'</td>';
				$html .= '<td>'.($sale->sold_serial_number ?: '-').'</td>';
				$html .= '<td>'.($sale->sold_imei_number ?: '-').'</td>';
				$html .= '</tr>';
			}
			$html .= '</tbody></table>';
		} else {
			$html .= '<div class="alert alert-info">No sales records found.</div>';
		}
		$html .= '</div></div></div>';

		// Production History
		if (mp_feature_enabled('production_workflow')) {
			$this->db->select('pr.*, r.name as recipe_name, b.batch_code, b.scheduled_date, u.first_name, u.last_name');
			$this->db->from('db_recipe_production_runs pr');
			$this->db->join('db_recipes r', 'r.id = pr.recipe_id', 'left');
			$this->db->join('db_production_batches b', 'b.id = pr.batch_id', 'left');
			$this->db->join('db_users u', 'u.id = pr.staff_id', 'left');
			$this->db->where('b.status', 'completed');
			$this->db->where('r.product_item_id', $item_id);
			$this->db->order_by('pr.run_date', 'DESC');
			$this->db->limit(20);
			$production_runs = $this->db->get()->result();

			// Also show where this item was used as an ingredient
			$this->db->select('ri.*, r.name as recipe_name, pr.run_date, pr.actual_yield, pr.actual_cost, b.batch_code, b.scheduled_date');
			$this->db->from('db_recipe_ingredients ri');
			$this->db->join('db_recipes r', 'r.id = ri.recipe_id', 'left');
			$this->db->join('db_recipe_production_runs pr', 'pr.recipe_id = r.id', 'left');
			$this->db->join('db_production_batches b', 'b.id = pr.batch_id', 'left');
			$this->db->where('b.status', 'completed');
			$this->db->where('ri.item_id', $item_id);
			$this->db->order_by('pr.run_date', 'DESC');
			$this->db->limit(20);
			$ingredient_runs = $this->db->get()->result();

			$html .= '<div class="col-md-12">';
			$html .= '<div class="box box-warning">';
			$html .= '<div class="box-header with-border"><h3 class="box-title"><i class="fa fa-industry"></i> Production History</h3></div>';
			$html .= '<div class="box-body">';

			// Produced as final product
			if (!empty($production_runs)) {
				$html .= '<h5><b>Produced as Final Product</b></h5>';
				$html .= '<table class="table table-bordered table-striped table-condensed">';
				$html .= '<thead><tr><th>Date</th><th>Batch #</th><th>Recipe</th><th>Planned Qty</th><th>Actual Yield</th><th>Actual Cost</th><th>Staff</th></tr></thead>';
				$html .= '<tbody>';
				foreach ($production_runs as $pr) {
					$html .= '<tr>';
					$html .= '<td>'.$pr->run_date.'</td>';
					$html .= '<td>'.($pr->batch_code ?: '-').'</td>';
					$html .= '<td>'.($pr->recipe_name ?: '-').'</td>';
					$html .= '<td>'.$pr->planned_qty.'</td>';
					$html .= '<td>'.$pr->actual_yield.'</td>';
					$html .= '<td>'.store_number_format($pr->actual_cost).'</td>';
					$html .= '<td>'.($pr->first_name ? $pr->first_name.' '.$pr->last_name : '-').'</td>';
					$html .= '</tr>';
				}
				$html .= '</tbody></table>';
			}

			// Used as ingredient
			if (!empty($ingredient_runs)) {
				$html .= '<h5><b>Used as Ingredient</b></h5>';
				$html .= '<table class="table table-bordered table-striped table-condensed">';
				$html .= '<thead><tr><th>Date</th><th>Batch #</th><th>Recipe</th><th>Qty Used</th><th>Cost/Unit</th><th>Recipe Yield</th></tr></thead>';
				$html .= '<tbody>';
				foreach ($ingredient_runs as $ir) {
					$scale = ($ir->actual_yield > 0 && $ir->yield_qty > 0) ? ($ir->actual_yield / $ir->yield_qty) : 0;
					$qty_used = $scale * $ir->qty;
					$html .= '<tr>';
					$html .= '<td>'.($ir->run_date ?: ($ir->scheduled_date ?: '-')).'</td>';
					$html .= '<td>'.($ir->batch_code ?: '-').'</td>';
					$html .= '<td>'.($ir->recipe_name ?: '-').'</td>';
					$html .= '<td>'.round($qty_used, 3).'</td>';
					$html .= '<td>'.store_number_format($ir->cost_per_unit).'</td>';
					$html .= '<td>'.($ir->actual_yield ?: '-').'</td>';
					$html .= '</tr>';
				}
				$html .= '</tbody></table>';
			}

			if (empty($production_runs) && empty($ingredient_runs)) {
				$html .= '<div class="alert alert-info">No production records found.</div>';
			}

			$html .= '</div></div></div>';
		}

		$html .= '</div>';

		echo json_encode(['status'=>'success','html'=>$html]);exit;
	}
}
