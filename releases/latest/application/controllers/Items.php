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
		$data['page_title']=mp_label('item').' List';
		$data['extra_js_files'] = ['plugins/lightbox/ekko-lightbox.js','js/items.js'];
		$data['content'] = $this->load->view('items-list',$data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function add()
	{
		$this->permission_check('items_add');
		$data=$this->data;
		$data['page_title']=mp_label('item');
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
		$data['extra_js_files'] = ['js/modals.js','js/items.js?v=15'];
		$data['content'] = $this->load->view('items',$data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function newitems(){
		$this->form_validation->set_rules('item_name', 'Item Name', 'trim|required');
		$this->form_validation->set_rules('category_id', 'Category Name', 'trim|required');
		$this->form_validation->set_rules('tax_id', 'Tax', 'trim|required');

		// Services don't require unit_id; items do
		$item_type_post = $this->input->post('item_type', TRUE);
		if($item_type_post !== 'service'){
			$this->form_validation->set_rules('unit_id', 'Unit', 'trim|required');
		}

		if($this->input->post('item_group')=='Single'){
		$this->form_validation->set_rules('price', 'Item Price', 'trim|required');
		$this->form_validation->set_rules('purchase_price', 'Purchase Price', 'trim|required');
		$this->form_validation->set_rules('sales_price', 'Sales Price', 'trim|required');
		}
		else{
			if($this->input->get_post('hidden_rowcount')==1){
				$_POST['item_group']='Single';
			}
		}
		if ($this->form_validation->run() == TRUE) {
			// Use the correct subscription limit based on item type
			$limit_key = ($item_type_post === 'service') ? 'service_limit' : 'product_limit';
			$product_check = check_subscription_limit($limit_key);
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
			try{
				session_write_close();
				$result=$this->items->save_record(array('command' =>'save'));
				echo $result;
			} catch(Throwable $e){
				log_message('error', 'newitems save error: '.$e->getMessage());
				echo 'Save failed: '.$e->getMessage();
			}
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
			if($this->input->get_post('hidden_rowcount')==1){
				$_POST['item_group']='Single';
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
		$data['page_title']=mp_label('item');
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
		$data['extra_js_files'] = ['js/modals.js','js/items.js?v=15'];
		$data['content'] = $this->load->view('items', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}
	public function update_items(){
		$this->form_validation->set_rules('item_name', 'Item Name', 'trim|required');
		$this->form_validation->set_rules('category_id', 'Category Name', 'trim|required');
		$this->form_validation->set_rules('tax_id', 'Tax', 'trim|required');

		// Services don't require unit_id; items do
		$item_type_post = $this->input->post('item_type', TRUE);
		if($item_type_post !== 'service'){
			$this->form_validation->set_rules('unit_id', 'Unit', 'trim|required');
		}

		if($this->input->post('item_group')=='Single'){
		$this->form_validation->set_rules('price', 'Item Price', 'trim|required');
		$this->form_validation->set_rules('purchase_price', 'Purchase Price', 'trim|required');
		$this->form_validation->set_rules('sales_price', 'Sales Price', 'trim|required');
		}
		else{
			if($this->input->post('hidden_rowcount')==1){
				$_POST['item_group']='Single';
			}
		}


		if ($this->form_validation->run() == TRUE) {
			try{
				// Release session lock before the potentially long save (image uploads, DB writes)
				// so the next page load's AJAX requests don't block on the session file.
				session_write_close();
				$result=$this->items->save_record(array('command'=>'update'));
				echo $result;
			} catch(Throwable $e){
				log_message('error', 'update_items save error: '.$e->getMessage());
				echo 'Update failed: '.$e->getMessage();
			}
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
		try {
			// Release the session lock early so concurrent AJAX requests don't block
			// on the session file while this query runs (fixes "Processing..." / Ajax error).
			$store_id = get_current_store_id();
			session_write_close();

			log_message('info', 'ajax_list started');
			$warehouse_id = $_REQUEST['warehouse_id'] ?? '';

			log_message('info', 'Calling get_datatables');
			$list = $this->items->get_datatables();
			log_message('info', 'get_datatables returned '.count($list).' items');

			$data = array();
			// Pre-load available stock for all listed items in one query to avoid per-row DB calls
			$warehouse_ids = (!empty($warehouse_id)) ? $warehouse_id : get_privileged_warehouses_ids();
			$stock_map = [];
			if(!empty($warehouse_ids) && !empty($list)){
				$item_ids = array_map(function($it){ return $it->id; }, $list);
				$placeholders = implode(',', array_fill(0, count($item_ids), '?'));
				$stock_result = $this->db->query("SELECT item_id, COALESCE(SUM(available_qty),0) as available_qty FROM db_warehouseitems WHERE store_id=? AND warehouse_id IN ($warehouse_ids) AND item_id IN ($placeholders) GROUP BY item_id", array_merge([$store_id], $item_ids));
				foreach($stock_result->result() as $r){
					$stock_map[$r->item_id] = $r->available_qty;
				}
			}

			$no = $_POST['start'] ?? 0;
			foreach ($list as $items) {

				$no++;
				try {
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
					}
					else{
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
			 			$str= "<i class='fa fa-building-o pointer bg-blue text-dark' title='Click to view Branch Wise Stock' data-toggle='tooltip' onclick='view_warehouse_wise_stock_item(".$items->id.")'> </i>";
			 			}
				$qty = isset($stock_map[$items->id]) ? $stock_map[$items->id] : 0;
				$row[] = format_qty($qty)." $str";

				$row[] = $items->alert_qty;
				$row[] = store_number_format($items->sales_price);
				$row[] = $items->tax_name."<br>(".store_number_format($items->tax)."%)";

					// Expiry status badge
					$expiry_badge = '';
					if(is_valid_date($items->expire_date)){
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
					$mfg_display = is_valid_date($items->mfg_date) ? date('d-m-Y', strtotime($items->mfg_date)) : '';
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
				} catch (Throwable $e) {
					log_message('error', 'ajax_list row error for item '.$items->id.' ('.$items->item_name.'): '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine());
				}
			}

			$output = array(
							"draw" => (int)($_POST['draw'] ?? 1),
							"recordsTotal" => (int)$this->items->count_all(),
							"recordsFiltered" => (int)$this->items->count_filtered(),
							"data" => $data,
					);
			//output to json format
			$json = json_encode($output, JSON_PARTIAL_OUTPUT_ON_ERROR);
			if($json === false){
				log_message('error', 'Items ajax_list json_encode failed: '.json_last_error_msg().' output='.substr(print_r($output, true), 0, 2000));
				echo json_encode(['error' => 'JSON encode failed: '.json_last_error_msg()]);
			} else {
				echo $json;
			}
		} catch (Throwable $e) {
			log_message('error', 'Items ajax_list Throwable: '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine().' trace: '.$e->getTraceAsString());
			$output = array(
				'draw' => $_POST['draw'] ?? 1,
				'recordsTotal' => 0,
				'recordsFiltered' => 0,
				'data' => [],
			);
			echo json_encode($output);
		}
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
		$name = isset($_GET['name']) ? strtolower(trim($_GET['name'])) : '';

		if(isset($search_for) && $search_for=='purchase'){
			$this->db->select("a.service_bit,a.purchase_price,a.id,a.item_name,a.item_code,COALESCE(a.stock,0) as stock,item_group");
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
		if(!empty($name)){
			$this->db->where("(LOWER(a.custom_barcode) LIKE '%$name%' or LOWER(a.item_name) LIKE '%$name%' or LOWER(a.item_code) LIKE '%$name%')");
		}

		$this->db->group_by("a.id");
		$this->db->limit("20");
		$sql =$this->db->get();

		if(!$sql){
			echo json_encode(['error' => 'Database query failed']);
			exit;
		}

		// Load expiry settings for sales search
		$expiry_settings = null;
		$today = date('Y-m-d');
		if(isset($search_for) && $search_for=='sales'){
			$this->load->model('expiry_settings_model');
			$expiry_settings = $this->expiry_settings_model->get_settings($store_id);
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
				  	if(is_valid_date($res->expire_date) && $expiry_settings->stop_selling_expired == 1 && $res->expire_date < $today){
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
				$this->db->select('b.id as barcode_id, b.item_id, b.barcode, b.batch_lot, b.serial_number, b.imei_number, b.warranty_months, b.purchase_price as bc_purchase_price, b.sales_price as bc_sales_price, b.mrp as bc_mrp, b.qty as bc_qty, a.item_name, a.item_code, a.service_bit, a.tax_id, a.tax_type, a.discount_type, a.discount, a.stock as item_stock');
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
		$data['extra_js_files'] = ['js/labels.js?v=2'];
		$data['content'] = $this->load->view('labels',$data, TRUE);
		$this->load->view('mp_layout', $data);
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

		// Product Activity Timeline — sales, purchases, adjustments & transfers
		$activities = [];

		// Sales
		$this->db->select('s.id as ref_id, s.sales_code as reference, s.sales_date as activity_date, c.customer_name as party, si.sales_qty as qty, si.unit_total_cost as amount, si.sold_serial_number as serial, si.sold_imei_number as imei');
		$this->db->from('db_salesitems si');
		$this->db->join('db_sales s', 's.id = si.sales_id', 'left');
		$this->db->join('db_customers c', 'c.id = s.customer_id', 'left');
		$this->db->where('si.item_id', $item_id);
		$this->db->where('s.sales_status', 'Final');
		$this->db->where('s.sales_date IS NOT NULL');
		$this->db->order_by('s.sales_date', 'DESC');
		$this->db->limit(25);
		$timeline_sales = $this->db->get()->result();
		foreach($timeline_sales as $ts){
			$note = [];
			if(!empty($ts->serial)) $note[] = 'S/N: '.$ts->serial;
			if(!empty($ts->imei)) $note[] = 'IMEI: '.$ts->imei;
			$activities[] = (object)[
				'type' => 'Sale',
				'icon' => 'fa-shopping-cart',
				'label_class' => 'label-danger',
				'date' => $ts->activity_date,
				'reference' => $ts->reference,
				'party' => ($ts->party ?: 'Walk-in'),
				'qty' => (float) $ts->qty * -1,
				'amount' => $ts->amount,
				'note' => implode(' ', $note)
			];
		}

		// Purchases
		$this->db->select('p.id as ref_id, p.purchase_code as reference, p.purchase_date as activity_date, s.supplier_name as party, pi.purchase_qty as qty, pi.unit_total_cost as amount, pi.batch_lot as batch, pi.barcode as barcode');
		$this->db->from('db_purchaseitems pi');
		$this->db->join('db_purchase p', 'p.id = pi.purchase_id', 'left');
		$this->db->join('db_suppliers s', 's.id = p.supplier_id', 'left');
		$this->db->where('pi.item_id', $item_id);
		$this->db->where_in('pi.purchase_status', ['Received','Partially Received']);
		$this->db->where('p.purchase_date IS NOT NULL');
		$this->db->order_by('p.purchase_date', 'DESC');
		$this->db->limit(25);
		$timeline_purchases = $this->db->get()->result();
		foreach($timeline_purchases as $tp){
			$note = [];
			if(!empty($tp->batch)) $note[] = 'Batch: '.$tp->batch;
			if(!empty($tp->barcode)) $note[] = 'Barcode: '.$tp->barcode;
			$activities[] = (object)[
				'type' => 'Purchase',
				'icon' => 'fa-truck',
				'label_class' => 'label-success',
				'date' => $tp->activity_date,
				'reference' => $tp->reference,
				'party' => ($tp->party ?: 'Supplier'),
				'qty' => (float) $tp->qty,
				'amount' => $tp->amount,
				'note' => implode(' ', $note)
			];
		}

		// Stock Adjustments
		$this->db->select('a.id as ref_id, a.reference_no as reference, a.adjustment_date as activity_date, a.adjustment_note as party, ai.adjustment_qty as qty, ai.description as note');
		$this->db->from('db_stockadjustmentitems ai');
		$this->db->join('db_stockadjustment a', 'a.id = ai.adjustment_id', 'left');
		$this->db->where('ai.item_id', $item_id);
		$this->db->where('a.status', 1);
		$this->db->where('ai.status', 1);
		$this->db->where('a.adjustment_date IS NOT NULL');
		$this->db->order_by('a.adjustment_date', 'DESC');
		$this->db->limit(25);
		$timeline_adjustments = $this->db->get()->result();
		foreach($timeline_adjustments as $ta){
			$activities[] = (object)[
				'type' => 'Adjustment',
				'icon' => 'fa-sliders',
				'label_class' => 'label-warning',
				'date' => $ta->activity_date,
				'reference' => ($ta->reference ?: 'ADJ-'.$ta->ref_id),
				'party' => ($ta->party ?: 'Stock adjustment'),
				'qty' => (float) $ta->qty,
				'amount' => null,
				'note' => $ta->note
			];
		}

		// Stock Transfers
		$this->db->select('t.id as ref_id, t.note as reference, t.transfer_date as activity_date, ti.warehouse_from as from_id, ti.warehouse_to as to_id, wf.warehouse_name as wh_from, wt.warehouse_name as wh_to, ti.transfer_qty as qty');
		$this->db->from('db_stocktransferitems ti');
		$this->db->join('db_stocktransfer t', 't.id = ti.stocktransfer_id', 'left');
		$this->db->join('db_warehouse wf', 'wf.id = ti.warehouse_from', 'left');
		$this->db->join('db_warehouse wt', 'wt.id = ti.warehouse_to', 'left');
		$this->db->where('ti.item_id', $item_id);
		$this->db->where('t.status', 1);
		$this->db->where('ti.status', 1);
		$this->db->where('t.transfer_date IS NOT NULL');
		$this->db->order_by('t.transfer_date', 'DESC');
		$this->db->limit(25);
		$timeline_transfers = $this->db->get()->result();
		foreach($timeline_transfers as $tt){
			$from = $tt->wh_from ?: 'Warehouse #'.$tt->from_id;
			$to = $tt->wh_to ?: 'Warehouse #'.$tt->to_id;
			$activities[] = (object)[
				'type' => 'Transfer',
				'icon' => 'fa-exchange',
				'label_class' => 'label-info',
				'date' => $tt->activity_date,
				'reference' => ($tt->reference ?: 'Transfer'),
				'party' => $from.' <i class="fa fa-long-arrow-right" style="margin:0 4px;"></i> '.$to,
				'qty' => (float) $tt->qty,
				'amount' => null,
				'note' => ''
			];
		}

		usort($activities, function($a, $b){
			return strtotime($b->date) - strtotime($a->date);
		});
		$activities = array_slice($activities, 0, 25);

		$html .= '<div class="col-md-12">';
		$html .= '<div class="box box-primary">';
		$html .= '<div class="box-header with-border"><h3 class="box-title"><i class="fa fa-history"></i> Product Activity</h3></div>';
		$html .= '<div class="box-body table-responsive no-padding">';
		if(!empty($activities)){
			$html .= '<table class="table table-bordered table-striped table-condensed">';
			$html .= '<thead><tr><th>Type</th><th>Date</th><th>Reference</th><th>Party / Location</th><th>Qty</th><th>Unit Cost</th><th>Notes</th></tr></thead>';
			$html .= '<tbody>';
			foreach($activities as $act){
				if($act->type == 'Sale'){
					$qty_display = '-'.abs($act->qty);
					$qty_class = 'text-danger';
				} elseif($act->type == 'Purchase'){
					$qty_display = '+'.abs($act->qty);
					$qty_class = 'text-success';
				} elseif($act->type == 'Adjustment'){
					$qty_display = ($act->qty > 0 ? '+' : '').$act->qty;
					$qty_class = ($act->qty >= 0 ? 'text-success' : 'text-danger');
				} else {
					$qty_display = $act->qty;
					$qty_class = 'text-muted';
				}
				$html .= '<tr>';
				$html .= '<td><span class="label '.$act->label_class.'"><i class="fa '.$act->icon.'"></i> '.$act->type.'</span></td>';
				$html .= '<td>'.$act->date.'</td>';
				$html .= '<td>'.$act->reference.'</td>';
				$html .= '<td>'.$act->party.'</td>';
				$html .= '<td class="'.$qty_class.'"><b>'.$qty_display.'</b></td>';
				$html .= '<td>'.($act->amount !== null ? store_number_format($act->amount) : '-').'</td>';
				$html .= '<td>'.$act->note.'</td>';
				$html .= '</tr>';
			}
			$html .= '</tbody></table>';
		} else {
			$html .= '<div class="text-center" style="padding:35px 15px; color:#888;">';
			$html .= '<i class="fa fa-history fa-3x" style="color:#d2d6de; margin-bottom:15px;"></i>';
			$html .= '<h4 style="margin:0 0 8px; font-size:16px; color:#555;">No activity recorded yet</h4>';
			$html .= '<p style="margin:0; font-size:13px;">Sales, purchases, stock adjustments and transfers will appear here once recorded.</p>';
			$html .= '</div>';
		}
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
			$html .= '<div class="text-center" style="padding:20px 10px; color:#888;"><i class="fa fa-cubes fa-2x" style="color:#d2d6de; margin-bottom:8px; display:block;"></i><p style="margin:0; font-size:13px;">No batch / unit records found.</p></div>';
		}
		$html .= '</div></div></div>';

		// Sales History
		$this->db->select('s.sales_code, s.sales_date, c.customer_name, si.sales_qty, si.unit_total_cost, si.sold_serial_number, si.sold_imei_number');
		$this->db->from('db_salesitems si');
		$this->db->join('db_sales s', 's.id = si.sales_id', 'left');
		$this->db->join('db_customers c', 'c.id = s.customer_id', 'left');
		$this->db->where('si.item_id', $item_id);
		$this->db->where('s.sales_status', 'Final');
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
			$html .= '<div class="text-center" style="padding:20px 10px; color:#888;"><i class="fa fa-shopping-cart fa-2x" style="color:#d2d6de; margin-bottom:8px; display:block;"></i><p style="margin:0; font-size:13px;">No sales records found.</p></div>';
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
				$html .= '<div class="text-center" style="padding:20px 10px; color:#888;"><i class="fa fa-industry fa-2x" style="color:#d2d6de; margin-bottom:8px; display:block;"></i><p style="margin:0; font-size:13px;">No production records found.</p></div>';
			}

			$html .= '</div></div></div>';
		}

		$html .= '</div>';

		echo json_encode(['status'=>'success','html'=>$html]);exit;
	}

	/**
	 * AJAX: Return distinct variant attribute types/values for the item form matrix generator.
	 */
	public function get_variant_attribute_map(){
		$map = $this->items->get_variant_attribute_map();
		echo json_encode(['status'=>'success','map'=>$map]);exit;
	}

	/**
	 * AJAX: Generate variant matrix rows for the item form.
	 * Cross-product of any 2 attribute types (size × colour, storage × colour, etc.)
	 */
	public function generate_matrix_rows(){
		$this->permission_check_with_msg('items_add');
		$html = $this->items->build_matrix_rows_html();
		echo json_encode(['status'=>'success','html'=>$html]);
	}

	/**
	 * Export all products (single + variants) as a round-trip CSV.
	 */
	public function export_items_csv(){
		$this->permission_check('items_view');
		$store_id = get_current_store_id();

		$headers = [
			'item_name','category_name','sku','hsn','unit_name','alert_qty','brand_name','lot_number',
			'price_before_tax','tax_name','tax_value','tax_type','sales_price','opening_stock',
			'custom_barcode','seller_points','description','discount_type','discount','mrp',
			'item_group','parent_sku','variant_name'
		];

		$this->db->select('i.*, b.brand_name, c.category_name, u.unit_name, t.tax_name, t.tax as tax_value, p.sku as parent_sku, v.variant_name', FALSE);
		$this->db->from('db_items i');
		$this->db->join('db_category c','c.id=i.category_id','left');
		$this->db->join('db_units u','u.id=i.unit_id','left');
		$this->db->join('db_brands b','b.id=i.brand_id','left');
		$this->db->join('db_tax t','t.id=i.tax_id','left');
		$this->db->join('db_items p','p.id=i.parent_id','left');
		$this->db->join('db_variants v','v.id=i.variant_id','left');
		$this->db->where('i.store_id', $store_id);
		$this->db->where('i.status', 1);
		$this->db->order_by('i.parent_id', 'asc');
		$this->db->order_by('i.id', 'asc');
		$query = $this->db->get();

		$filename = 'products_export_'.date('Ymd_His').'.csv';
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="'.$filename.'"');

		$out = fopen('php://output','w');
		fputcsv($out, $headers);

		foreach($query->result() as $row){
			if((int)$row->child_bit === 1){
				$item_group = 'Variant';
			} elseif($row->item_group === 'Variants'){
				$item_group = 'Variants';
			} else {
				$item_group = 'Single';
			}

			$line = [
				$row->item_name,
				$row->category_name,
				$row->sku,
				$row->hsn,
				$row->unit_name,
				number_format($row->alert_qty,0,'.',''),
				$row->brand_name,
				$row->lot_number,
				number_format($row->price,2,'.',''),
				$row->tax_name,
				number_format($row->tax_value,2,'.',''),
				$row->tax_type,
				number_format($row->sales_price,2,'.',''),
				number_format($row->stock,0,'.',''),
				$row->custom_barcode,
				number_format($row->seller_points,0,'.',''),
				$row->description,
				$row->discount_type,
				number_format($row->discount,2,'.',''),
				number_format($row->mrp,2,'.',''),
				$item_group,
				$row->parent_sku,
				$row->variant_name
			];
			fputcsv($out, $line);
		}
		fclose($out);
		exit;
	}

}

