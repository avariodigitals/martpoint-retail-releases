<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Items extends MY_Controller {
	private $pharmacy_purchase_category_cache = array();

	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('items_model','items');
	}

	private function _pharmacy_purchase_categories($store_id)
	{
		$store_id = (int)$store_id;
		if (array_key_exists($store_id, $this->pharmacy_purchase_category_cache)) {
			return $this->pharmacy_purchase_category_cache[$store_id];
		}

		// Read industry settings without mp_get_store_profile(). That helper uses
		// the shared query builder and this method is called while the item query
		// is already being assembled.
		$industry = '';
		foreach (array('db_store_industry_settings', 'db_store_business_profile') as $table) {
			if (!$this->db->table_exists($table)) continue;
			$query = $this->db->query(
				"SELECT industry_type FROM {$table} WHERE store_id = ? LIMIT 1",
				array($store_id)
			);
			if ($query && $query->num_rows()) {
				$row = $query->row();
				$industry = strtolower(trim((string)($row->industry_type ?? '')));
				if ($industry !== '') break;
			}
		}
		if ($industry === '' && $this->db->table_exists('db_store') && $this->db->field_exists('industry_type', 'db_store')) {
			$query = $this->db->query(
				"SELECT industry_type FROM db_store WHERE id = ? LIMIT 1",
				array($store_id)
			);
			if ($query && $query->num_rows()) {
				$row = $query->row();
				$industry = strtolower(trim((string)($row->industry_type ?? '')));
			}
		}

		if (!in_array($industry, array('pharmacy', 'medical_store', 'healthcare'), true)) {
			$this->pharmacy_purchase_category_cache[$store_id] = array();
			return array();
		}

		$this->pharmacy_purchase_category_cache[$store_id] = array(
			'Prescription Drugs', 'Over-the-Counter', 'Medical Supplies',
			'Vitamins & Supplements', 'Dermatology', 'Skincare',
			'Analgesics & Pain Relief', 'Antibiotics', 'Cold, Flu & Allergy',
			'Digestive Health', 'First Aid & Wound Care', 'Mother & Baby',
			'Personal Care & Hygiene', 'Medical Devices',
		);
		return $this->pharmacy_purchase_category_cache[$store_id];
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
		// Allow ?type=digital|course|membership to preselect the product type (Creator workspace links)
		$preType = $this->input->get('type', TRUE);
		$typeFlags = ['digital' => 'digital_products', 'course' => 'courses', 'membership' => 'memberships'];
		if(isset($typeFlags[$preType]) && mp_feature_enabled($typeFlags[$preType])){
			$data['product_type'] = $preType;
			$data['page_title'] = 'New ' . ucfirst($preType) . ($preType === 'digital' ? ' Product' : '');
		}
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
		$data = array_merge($data, $this->_auto_part_form_context());
		$data['extra_js_files'] = ['js/modals.js','js/items.js?v=16'];
		$data['content'] = $this->load->view('items',$data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function newitems(){
		$this->form_validation->set_rules('item_name', 'Item Name', 'trim|required');
		$this->form_validation->set_rules('category_id', 'Category Name', 'trim|required');
		$this->form_validation->set_rules('tax_id', 'Tax', 'trim|required');

		// Services don't require unit_id; items do
		$item_type_post = $this->input->post('item_type', TRUE);
		if($item_type_post === 'digital' && !mp_feature_enabled('digital_products')) $item_type_post = 'item';
		if($item_type_post === 'course' && !mp_feature_enabled('courses')) $item_type_post = 'item';
		if($item_type_post === 'membership' && !mp_feature_enabled('memberships')) $item_type_post = 'item';
		if(!in_array($item_type_post, ['service','digital','course','membership'])){
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
			// SKU fair-usage check: count how many new sellable units this save adds.
			// Single product = 1 SKU; variant product = N SKUs (children only,
			// the parent is just a container and is already counted by product_limit).
			if(!in_array($item_type_post, ['service','digital','course','membership'])){
				$item_group_val = $this->input->post('item_group', TRUE);
				$variant_rows = (int)$this->input->post('hidden_rowcount', TRUE);
				$new_skus = ($item_group_val === 'Variants') ? $variant_rows : 1;
				$sku_check = check_sku_limit($new_skus);
				if($sku_check !== true){
					echo $sku_check;
					return;
				}
			}
			if(!empty($_FILES['item_image']['name']) || !empty($_FILES['item_gallery']['name'][0]) || ($item_type_post === 'digital' && !empty($_FILES['digital_file']['name']))){
				$media_check = check_media_storage_limit();
				if($media_check !== true){
					echo $media_check;
					return;
				}
			}
			if($item_type_post === 'digital' && !empty($_FILES['digital_file']['name'])){
				$store_id = get_current_store_id();
				$digital_dir = 'uploads/digital/' . $store_id . '/';
				if(!is_dir('./' . $digital_dir)) mkdir('./' . $digital_dir, 0755, true);

				$upload_conf = [
					'upload_path'   => './' . $digital_dir,
					'file_name'     => 'dgtl_' . time() . '_' . $_FILES['digital_file']['name'],
					'allowed_types' => '*',
					'max_size'      => 102400,
				];
				$this->load->library('upload', $upload_conf);
				if(!$this->upload->do_upload('digital_file')){
					echo $this->upload->display_errors();
					return;
				}
				$_POST['digital_file_path'] = $digital_dir . $this->upload->data('file_name');
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
			// save_record() consumes the standard item field names from the
			// CodeIgniter input object. The modal form posts m_* names, so passing
			// only $modal_post left the model reading blank item_name/category/
			// prices and the insert failed (or produced incomplete data) on this
			// endpoint. Copy the validated mapping into POST before invoking the
			// shared save path; regular item creation remains unchanged.
			foreach ($modal_post as $field => $value) {
				$_POST[$field] = $value;
			}
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
		$data['gallery_images'] = [];
		if ($this->db->table_exists('db_item_images')) {
			$data['gallery_images'] = $this->db->where('store_id', get_current_store_id())
				->where('item_id', (int)$id)->order_by('sort_order', 'ASC')->order_by('id', 'ASC')
				->get('db_item_images')->result();
		}
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
		$data = array_merge($data, $this->_auto_part_form_context((int)$id));
		$data['extra_js_files'] = ['js/modals.js','js/items.js?v=16'];
		$data['content'] = $this->load->view('items', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/** Return this store's existing product image files for the inline media picker. */
	public function media_library(){
		if (!$this->permissions('items_add') && !$this->permissions('items_edit')) {
			show_error('You do not have permission to use the product media library.', 403);
			return;
		}
		$store_id = (int)get_current_store_id();
		$images = [];
		$add = function($path, $label) use (&$images){
			$path = str_replace('\\', '/', trim((string)$path));
			if ($path === '' || strpos($path, 'uploads/items/') !== 0 || strpos($path, '..') !== false) return;
			$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
			if (!in_array($extension, ['jpg','jpeg','png','gif','webp','avif'], true)) return;
			$root = realpath(FCPATH . 'uploads/items');
			$file = realpath(FCPATH . $path);
			if (!$root || !$file || strpos($file, $root . DIRECTORY_SEPARATOR) !== 0 || !is_file($file)) return;
			if (!isset($images[$path])) {
				$images[$path] = ['path' => $path, 'url' => base_url($path), 'label' => trim((string)$label) ?: basename($path)];
			}
		};

		if ($this->db->table_exists('db_items')) {
			$rows = $this->db->select('item_name, item_image')->where('store_id', $store_id)
				->where('item_image !=', '')->order_by('item_name', 'ASC')->get('db_items')->result();
			foreach ($rows as $row) $add($row->item_image, $row->item_name);
		}
		if ($this->db->table_exists('db_item_images')) {
			$rows = $this->db->select('g.image_path, i.item_name')->from('db_item_images g')
				->join('db_items i', 'i.id = g.item_id AND i.store_id = g.store_id', 'left')
				->where('g.store_id', $store_id)->order_by('g.id', 'DESC')->get()->result();
			foreach ($rows as $row) $add($row->image_path, $row->item_name ?? 'Product gallery');
		}
		usort($images, function($a, $b){ return strcasecmp($a['label'], $b['label']); });
		$this->output->set_content_type('application/json')->set_output(json_encode([
			'success' => true,
			'images' => array_values($images),
			'csrf_hash' => $this->security->get_csrf_hash(),
		]));
	}

	public function update_items(){
		$this->form_validation->set_rules('item_name', 'Item Name', 'trim|required');
		$this->form_validation->set_rules('category_id', 'Category Name', 'trim|required');
		$this->form_validation->set_rules('tax_id', 'Tax', 'trim|required');

		// Services don't require unit_id; items do
		$item_type_post = $this->input->post('item_type', TRUE);
		if($item_type_post === 'digital' && !mp_feature_enabled('digital_products')) $item_type_post = 'item';
		if($item_type_post === 'course' && !mp_feature_enabled('courses')) $item_type_post = 'item';
		if($item_type_post === 'membership' && !mp_feature_enabled('memberships')) $item_type_post = 'item';
		if(!in_array($item_type_post, ['service','digital','course','membership'])){
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
			if (!empty($_FILES['item_gallery']['name'][0])) {
				$media_check = check_media_storage_limit();
				if ($media_check !== true) { echo $media_check; return; }
			}
			// SKU fair-usage check on update: only count NEW variant children
			// (rows without an existing tr_item_id). The parent already exists.
			if(!in_array($item_type_post, ['service','digital','course','membership'])){
				$item_group_val = $this->input->post('item_group', TRUE);
				$variant_rows = (int)$this->input->post('hidden_rowcount', TRUE);
				$new_skus = 0;
				if($item_group_val === 'Variants'){
					for($i = 1; $i <= $variant_rows; $i++){
						$child_id = $this->input->post('tr_item_id_'.$i, TRUE);
						if(empty($child_id)){
							$new_skus++;
						}
					}
				}
				if($new_skus > 0){
					$sku_check = check_sku_limit($new_skus);
					if($sku_check !== true){
						echo $sku_check;
						return;
					}
				}
			}
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
			$requested_warehouse_ids = is_array($warehouse_id) ? $warehouse_id : explode(',', (string)$warehouse_id);
			$requested_warehouse_ids = array_values(array_unique(array_filter(array_map('intval', $requested_warehouse_ids))));

			log_message('info', 'Calling get_datatables');
			$list = $this->items->get_datatables();
			log_message('info', 'get_datatables returned '.count($list).' items');

			$data = array();
			// Pre-load available stock for all listed items in one query to avoid per-row DB calls.
			// An empty branch filter means the store-wide product total for
			// owners/admins; regular staff remain limited to assigned branches.
			$use_global_item_stock = empty($requested_warehouse_ids) && (is_admin() || is_store_admin());
			if(!empty($requested_warehouse_ids)){
				$warehouse_ids = $requested_warehouse_ids;
			}else if($use_global_item_stock){
				$warehouse_ids = array();
			}else{
				$warehouse_ids = array_values(array_unique(array_filter(array_map('intval', getArrayOfWarehouseIds()))));
			}
			if(!is_admin() && !is_store_admin()){
				$warehouse_ids = array_values(array_intersect($warehouse_ids, array_map('intval', getArrayOfWarehouseIds())));
			}
			// Validate posted branch IDs against this store so crafted requests cannot
			// read another store's stock or include inactive/unassigned branches.
			if(!empty($warehouse_ids)){
				$this->db->select('id')->from('db_warehouse')->where('store_id', $store_id)->where('status', 1)->where_in('id', $warehouse_ids);
				$warehouse_rows = $this->db->get()->result();
				$warehouse_ids = array_map(function($row){ return (int)$row->id; }, $warehouse_rows);
			}
			$stock_map = [];
			if($use_global_item_stock && !empty($list)){
				// The product detail screen uses db_items.stock for the store-wide
				// quantity. Keep the All Branches list on that same canonical total.
				foreach($list as $listed_item){
					$stock_map[$listed_item->id] = (float)$listed_item->stock;
				}
			}else if(!empty($warehouse_ids) && !empty($list)){
				$item_ids = array_map(function($it){ return $it->id; }, $list);
				$placeholders = implode(',', array_fill(0, count($item_ids), '?'));
				$warehouse_placeholders = implode(',', array_fill(0, count($warehouse_ids), '?'));
				$stock_result = $this->db->query("SELECT item_id, COALESCE(SUM(available_qty),0) as available_qty FROM db_warehouseitems WHERE store_id=? AND warehouse_id IN ($warehouse_placeholders) AND item_id IN ($placeholders) GROUP BY item_id", array_merge([$store_id], $warehouse_ids, $item_ids));
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

				 		$str2 = '<div class="btn-group mp-action-group" title="View Account">
										<a class="mp-action-toggle" data-toggle="dropdown" href="#">
											Action <span class="caret"></span>
										</a>
										<ul role="menu" class="dropdown-menu dropdown-menu-right mp-action-menu">';

											$str2.='<li>
												<a class="mp-action-item" style="cursor:pointer" title="View Product History" onclick="view_item_history('.$items->id.')">
													<i class="fa fa-fw fa-eye text-navy"></i>View
												</a>
											</li>';

											if($this->permissions('items_edit') || $this->permissions('services_edit'))
											$str2.='<li>
												<a class="mp-action-item" title="Edit Record ?" href="'.base_url(($items->service_bit)? 'services/update/'.$items->id : 'items/update/'.$items->id).'">
													<i class="fa fa-fw fa-edit text-blue"></i>Edit
												</a>
											</li>';

											if($this->permissions('items_delete')|| $this->permissions('services_delete'))
											$str2.='<li>
												<a class="mp-action-item" style="cursor:pointer" title="Delete Record ?" onclick="delete_items('.$items->id.')">
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
		$search_for=$this->input->get('search_for');
		$store_id=(int)$this->input->get('store_id');
		if(!$store_id && function_exists('get_current_store_id')) $store_id=(int)get_current_store_id();
		$warehouse_id=(int)$this->input->get('warehouse_id');
		if(!$warehouse_id && function_exists('get_store_warehouse_id')) $warehouse_id=(int)get_store_warehouse_id();

		// Transfer search is scoped to the signed-in store and selected source
		// warehouse. A missing warehouse stock row must not hide the catalogue
		// item; the picker can then explain that its available quantity is zero.
		if($search_for === 'stock_transfer'){
			$store_id = (int)get_current_store_id();
			$warehouse_ok = $warehouse_id > 0
				&& $this->db->where('id', $warehouse_id)->where('store_id', $store_id)->where('status', 1)->count_all_results('db_warehouse') > 0;
			$allowed = function_exists('mp_allowed_warehouse_ids') ? mp_allowed_warehouse_ids($store_id) : null;
			if(!$warehouse_ok || ($allowed !== null && !in_array($warehouse_id, $allowed, true))){
				$this->output->set_content_type('application/json')->set_output('[]');
				return;
			}
		}

		$show_purchase_price = $this->permissions('show_purchase_price');
		$data = array();
		$display_json = array();
		$name = isset($_GET['name']) ? strtolower(trim($_GET['name'])) : '';
		$name_sql = $this->db->escape_like_str($name); // the LIKE clauses below interpolate raw SQL
		$warehouse_stock_sql = 'COALESCE(SUM(b.available_qty),0)';
		if ((!isset($search_for) || $search_for === 'stock_transfer') && $this->db->table_exists('db_item_barcodes') && $this->db->table_exists('db_stocktransfer_batch_allocations')) {
			$batch_stock_sql = "(SELECT COALESCE(SUM(bc.qty),0) FROM db_item_barcodes bc WHERE bc.item_id=a.id AND bc.warehouse_id=".(int)$warehouse_id." AND bc.status=1 AND bc.qty>0 AND ((bc.batch_lot IS NOT NULL AND bc.batch_lot<>'') OR bc.expire_date IS NOT NULL) AND (bc.serial_number IS NULL OR bc.serial_number='') AND (bc.imei_number IS NULL OR bc.imei_number=''))";
			$warehouse_stock_sql = "CASE WHEN COALESCE(SUM(b.available_qty),0)>0 THEN COALESCE(SUM(b.available_qty),0) ELSE {$batch_stock_sql} END";
		}
		$pharmacy_categories = ($search_for === 'purchase')
			? $this->_pharmacy_purchase_categories($store_id)
			: array();

		if(isset($search_for) && $search_for=='purchase'){
			$this->db->select("a.service_bit,a.purchase_price,a.id,a.item_name,a.item_code,COALESCE(a.stock,0) as stock,item_group");
			$this->db->from("db_items as a");
			if ($pharmacy_categories) {
				$this->db->join('db_category as pharmacy_category', 'pharmacy_category.id=a.category_id AND pharmacy_category.store_id=a.store_id', 'inner')
					->where('pharmacy_category.status', 1)
					->where_in('pharmacy_category.category_name', $pharmacy_categories);
			}
		}
		else if(isset($search_for) && ($search_for=='labels' || $search_for=='sales')){
			$this->db->select("*");
			$this->db->from("db_items as a");
		}
		else if($search_for === 'stock_transfer'){
			$this->db->where('a.service_bit', 0);
			$this->db->select('a.service_bit,a.purchase_price,a.id,a.item_name,a.item_code,'.$warehouse_stock_sql.' as stock,a.item_group', false);
			$this->db->from('db_items as a');
			$this->db->join('db_warehouseitems as b', 'b.item_id=a.id AND b.store_id=a.store_id AND b.warehouse_id='.(int)$warehouse_id, 'left', false);
		}
		else{
			$this->db->where('a.service_bit=0');
			$this->db->select("a.service_bit,a.purchase_price,a.id,a.item_name,a.item_code,".$warehouse_stock_sql." as stock,a.item_group", false);
			$this->db->from("db_items as a");
			$this->db->join("db_warehouseitems as b", "b.item_id=a.id AND b.store_id=a.store_id AND b.warehouse_id=".(int)$warehouse_id, 'left', false);
		}

		$this->db->where("a.status",1);
		$this->db->where("a.store_id",$store_id);
		// Exclude consumables / raw materials from POS sales
		if(isset($search_for) && $search_for=='sales'){
			$this->db->where("(a.not_for_sale IS NULL OR a.not_for_sale = 0)");
		}
		if(!empty($name)){
			// Parts trade on references: a workshop asks for the part number, not
			// the catalogue name. Columns arrive with 4.0.9.243.
			$part_ref_clause = '';
			if ($this->db->field_exists('manufacturer_part_no', 'db_items')) {
				$part_ref_clause = " or LOWER(a.manufacturer_part_no) LIKE '%$name_sql%' or LOWER(a.oem_reference) LIKE '%$name_sql%' or LOWER(a.cross_reference) LIKE '%$name_sql%'";
			}
			$this->db->where("(LOWER(a.custom_barcode) LIKE '%$name_sql%' or LOWER(a.item_name) LIKE '%$name_sql%' or LOWER(a.item_code) LIKE '%$name_sql%'$part_ref_clause)");
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
				  $json_arr["label"] = $res->item_name . $this->_matched_part_ref($res, $name);
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

			// Also search per-product selling units by barcode for pack/piece/box sales
			if(isset($search_for) && $search_for=='sales' && !empty($name) && $this->db->table_exists('db_item_selling_units')){
				$this->db->select('su.*, a.item_name, a.item_code, a.service_bit, a.custom_barcode, a.stock as item_stock, a.tax_id, a.tax_type, a.discount_type, a.discount');
				$this->db->from('db_item_selling_units su');
				$this->db->join('db_items a', 'a.id = su.item_id', 'left');
				$this->db->where('a.status', 1);
				$this->db->where('a.store_id', $store_id);
				$this->db->where('su.status', 1);
				$this->db->where('su.is_template', 0);
				$this->db->where("(a.not_for_sale IS NULL OR a.not_for_sale = 0)");
				$this->db->where("(LOWER(su.barcode) LIKE '%$name_sql%' OR LOWER(su.sku) LIKE '%$name_sql%')", null, false);
				$this->db->limit(20);
				$su_sql = $this->db->get();
				foreach ($su_sql->result() as $sres) {
					$json_arr = array();
					$json_arr["id"] = $sres->item_id;
					$json_arr["value"] = $sres->item_name;
					$label_extra = trim($sres->unit_shortcode ?: ($sres->unit_name ?: 'Unit'));
					$json_arr["label"] = $sres->item_name . ($label_extra ? ' [' . $label_extra . ']' : '');
					$json_arr["item_code"] = $sres->item_code;
					$json_arr["stock"] = total_available_qty_items_of_warehouse($warehouse_id, $store_id, $sres->item_id);
					$json_arr["purchase_price"] = ($show_purchase_price) ? store_number_format($sres->purchase_price) : '';
					$json_arr["service_bit"] = $sres->service_bit;
					$json_arr["package_bit"] = 0;
					$json_arr["accept_custom_order"] = 0;
					$json_arr["custom_order_fields_json"] = null;
					$json_arr["barcode"] = $sres->barcode;
					$json_arr["barcode_id"] = 0;
					$json_arr["barcode_price"] = store_number_format($sres->selling_price);
					$json_arr["unit_id"] = $sres->unit_id;
					$json_arr["unit_name"] = $sres->unit_name;
					$json_arr["unit_shortcode"] = $sres->unit_shortcode;
					$json_arr["conversion_factor"] = $sres->conversion_factor;
					$json_arr["expired"] = false;
					array_push($display_json, $json_arr);
				}
			}

			// Also search db_item_barcodes for barcode, serial, or imei matches
			if(!empty($name)){
				$this->db->select('b.id as barcode_id, b.item_id, b.barcode, b.batch_lot, b.serial_number, b.imei_number, b.imei_number_2, b.warranty_months, b.purchase_price as bc_purchase_price, b.sales_price as bc_sales_price, b.mrp as bc_mrp, b.qty as bc_qty, a.item_name, a.item_code, a.service_bit, a.tax_id, a.tax_type, a.discount_type, a.discount, a.stock as item_stock');
				$this->db->from('db_item_barcodes b');
				$this->db->join('db_items a', 'a.id = b.item_id', 'left');
				$this->db->where('a.status', 1);
				$this->db->where('a.store_id', $store_id);
				if(isset($search_for) && $search_for=='sales'){
					$this->db->where("(a.not_for_sale IS NULL OR a.not_for_sale = 0)");
				}
				$this->db->where('b.status', 1);
				if(isset($search_for) && $search_for==='sales' && $this->db->field_exists('warehouse_id','db_item_barcodes')) $this->db->where('b.warehouse_id',(int)$warehouse_id);
				$this->db->where("(LOWER(b.barcode) LIKE '%$name_sql%' OR LOWER(b.serial_number) LIKE '%$name_sql%' OR LOWER(b.imei_number) LIKE '%$name_sql%' OR LOWER(b.imei_number_2) LIKE '%$name_sql%')", null, false);
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
					if($bres->imei_number_2) $label_extra .= ($label_extra ? ' / ' : '') . 'IMEI2:' . $bres->imei_number_2;
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
					$json_arr["imei_number_2"] = $bres->imei_number_2;
					$json_arr["warranty_months"] = $bres->warranty_months;
					if(isset($search_for) && $search_for=='sales'){
						$json_arr["expired"] = false;
					}
					array_push($display_json, $json_arr);
				}
			}

		echo json_encode($display_json);exit;
	}

	/**
	 * When staff search by part reference instead of by name, show the number
	 * that matched so they can see they have the right part before adding it.
	 */
	private function _matched_part_ref($res, $term){
		$term = trim((string) $term);
		if ($term === '' || strpos(strtolower((string) $res->item_name), $term) !== false) return '';
		foreach (['manufacturer_part_no', 'oem_reference', 'cross_reference'] as $col) {
			foreach (preg_split('/[,\s\/]+/', trim((string) ($res->{$col} ?? ''))) as $token) {
				if ($token !== '' && strpos(strtolower($token), $term) !== false) return ' [' . $token . ']';
			}
		}
		return '';
	}

	/** Fitment pickers for the auto-parts catalogue form; other business
	 * types never see the section, so nothing is posted and nothing is stored.
	 */
	private function _auto_part_form_context($item_id = null){
		if (!mp_feature_enabled('auto_parts')) return [];
		$store_id = (int) get_current_store_id();
		$this->load->model('item_vehicle_compat_model');
		$makes = $this->db->where_in('store_id', [0, $store_id])->where('status', 1)
			->order_by('name', 'ASC')->get('db_vehicle_makes')->result();
		$models = $this->db->where_in('store_id', [0, $store_id])->where('status', 1)
			->order_by('name', 'ASC')->get('db_vehicle_models')->result();
		return [
			'vehicle_compat_rows' => $item_id ? $this->item_vehicle_compat_model->rows_for_item($store_id, $item_id) : [],
			'vehicle_makes' => $makes,
			'vehicle_models' => $models,
		];
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
		$showSerial = mp_feature_enabled('serial_number_tracking') && !empty($item->track_serial);
		$showImei = mp_feature_enabled('imei_tracking') && !empty($item->track_imei);
		$showWarranty = mp_feature_enabled('warranty_tracking') && (int)($item->warranty_months ?? 0) > 0;

		$html = '<div class="row">';
		
		// Item Basic Info
		$html .= '<div class="col-md-12">';
		$html .= '<div class="box box-default">';
		$html .= '<div class="box-header with-border"><h3 class="box-title"><i class="fa fa-tag"></i> '.$item->item_name.'</h3></div>';
		$html .= '<div class="box-body">';
		$html .= '<div class="row">';
		$html .= '<div class="col-md-3"><b>Code:</b> '.$item->item_code.'</div>';
		$html .= '<div class="col-md-3"><b>Barcode:</b> '.($item->custom_barcode ?: '-').'</div>';
		if(mp_feature_enabled('auto_parts') && !empty($item->manufacturer_part_no)){
			$html .= '<div class="col-md-3"><b>Part No:</b> '.htmlspecialchars($item->manufacturer_part_no).'</div>';
		}
		$html .= '<div class="col-md-3"><b>Stock:</b> '.$item->stock.'</div>';
		$html .= '<div class="col-md-3"><b>Price:</b> '.store_number_format($item->sales_price).'</div>';
		$html .= '</div>';
		$html .= '</div></div></div>';

		// Product Activity Timeline — sales, purchases, adjustments & transfers
		$activities = [];

		// Sales
		$this->db->select('s.id as ref_id, s.sales_code as reference, s.sales_date as activity_date, c.customer_name as party, w.warehouse_name as location, si.sales_qty as qty, si.unit_total_cost as amount, si.sold_serial_number as serial, si.sold_imei_number as imei');
		$this->db->from('db_salesitems si');
		$this->db->join('db_sales s', 's.id = si.sales_id AND s.store_id = si.store_id', 'left');
		$this->db->join('db_customers c', 'c.id = s.customer_id AND c.store_id = s.store_id', 'left');
		$this->db->join('db_warehouse w', 'w.id = s.warehouse_id AND w.store_id = s.store_id', 'left');
		$this->db->where('si.item_id', $item_id)->where('si.store_id', (int)$item->store_id);
		$this->db->where('s.sales_status', 'Final');
		$this->db->where('s.sales_date IS NOT NULL');
		$this->db->order_by('s.sales_date', 'DESC');
		$timeline_sales = $this->db->get()->result();
		foreach($timeline_sales as $ts){
			$note = [];
			if($showSerial && !empty($ts->serial)) $note[] = 'S/N: '.$ts->serial;
			if($showImei && !empty($ts->imei)) $note[] = 'IMEI: '.$ts->imei;
			$activities[] = (object)[
				'type' => 'Sale',
				'icon' => 'fa-shopping-cart',
				'label_class' => 'label-danger',
				'date' => $ts->activity_date,
				'reference' => $ts->reference,
				'party' => trim(($ts->party ?: 'Walk-in').(!empty($ts->location) ? ' · '.$ts->location : '')),
				'qty' => (float) $ts->qty * -1,
				'amount' => $ts->amount,
				'note' => implode(' ', $note)
			];
		}

		// Purchases
		$purchaseQtyExpr = $this->db->field_exists('received_qty', 'db_purchaseitems') ? 'COALESCE(pi.received_qty, pi.purchase_qty)' : 'pi.purchase_qty';
		$this->db->select('p.id as ref_id, p.purchase_code as reference, p.purchase_date as activity_date, s.supplier_name as party, w.warehouse_name as location, '.$purchaseQtyExpr.' as qty, pi.unit_total_cost as amount, pi.batch_lot as batch, pi.barcode as barcode', false);
		$this->db->from('db_purchaseitems pi');
		$this->db->join('db_purchase p', 'p.id = pi.purchase_id AND p.store_id = pi.store_id', 'left');
		$this->db->join('db_suppliers s', 's.id = p.supplier_id AND s.store_id = p.store_id', 'left');
		$this->db->join('db_warehouse w', 'w.id = p.warehouse_id AND w.store_id = p.store_id', 'left');
		$this->db->where('pi.item_id', $item_id)->where('pi.store_id', (int)$item->store_id);
		$this->db->where_in('pi.purchase_status', ['Received','Partially Received']);
		$this->db->where('p.purchase_date IS NOT NULL');
		$this->db->order_by('p.purchase_date', 'DESC');
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
				'party' => trim(($tp->party ?: 'Supplier').(!empty($tp->location) ? ' · '.$tp->location : '')),
				'qty' => (float) $tp->qty,
				'amount' => $tp->amount,
				'note' => implode(' ', $note)
			];
		}

		// Stock Adjustments
		$this->db->select('a.id as ref_id, a.reference_no as reference, a.adjustment_date as activity_date, a.adjustment_note as party, ai.adjustment_qty as qty, ai.description as note');
		$this->db->from('db_stockadjustmentitems ai');
		$this->db->join('db_stockadjustment a', 'a.id = ai.adjustment_id AND a.store_id = ai.store_id', 'left');
		$this->db->where('ai.item_id', $item_id)->where('ai.store_id', (int)$item->store_id);
		$this->db->where('a.status', 1);
		$this->db->where('ai.status', 1);
		$this->db->where('a.adjustment_date IS NOT NULL');
		if($this->db->table_exists('db_online_orders') && $this->db->field_exists('sales_id', 'db_online_orders')){
			$this->db->where("(a.reference_no NOT LIKE 'ONLINE-RESERVE-%' OR NOT EXISTS (
				SELECT 1 FROM db_online_orders oo WHERE oo.store_id=a.store_id
				AND a.reference_no=CONCAT('ONLINE-RESERVE-',oo.id) AND COALESCE(oo.sales_id,0)>0))", null, false);
		}
		$this->db->order_by('a.adjustment_date', 'DESC');
		$timeline_adjustments = $this->db->get()->result();
		foreach($timeline_adjustments as $ta){
			$isReservation = strpos((string)$ta->reference, 'ONLINE-RESERVE-') === 0;
			$activities[] = (object)[
				'type' => $isReservation ? 'Online reservation' : 'Adjustment',
				'icon' => $isReservation ? 'fa-clock-o' : 'fa-sliders',
				'label_class' => $isReservation ? 'label-warning' : 'label-warning',
				'date' => $ta->activity_date,
				'reference' => ($ta->reference ?: 'ADJ-'.$ta->ref_id),
				'party' => ($isReservation ? 'Stock held for an unpaid online order' : ($ta->party ?: 'Stock adjustment')),
				'qty' => (float) $ta->qty,
				'amount' => null,
				'note' => $isReservation ? 'Temporary hold; this is released on cancellation or replaced by the sale when the order is posted.' : $ta->note
			];
		}

		// Stock Transfers
		$this->db->select('t.id as ref_id, t.note as reference, t.transfer_date as activity_date, ti.warehouse_from as from_id, ti.warehouse_to as to_id, wf.warehouse_name as wh_from, wt.warehouse_name as wh_to, ti.transfer_qty as qty');
		$this->db->from('db_stocktransferitems ti');
		$this->db->join('db_stocktransfer t', 't.id = ti.stocktransfer_id AND t.store_id = ti.store_id', 'left');
		$this->db->join('db_warehouse wf', 'wf.id = ti.warehouse_from AND wf.store_id = ti.store_id', 'left');
		$this->db->join('db_warehouse wt', 'wt.id = ti.warehouse_to AND wt.store_id = ti.store_id', 'left');
		$this->db->where('ti.item_id', $item_id)->where('ti.store_id', (int)$item->store_id);
		$this->db->where('t.status', 1);
		$this->db->where('ti.status', 1);
		$this->db->where('t.transfer_date IS NOT NULL');
		$this->db->order_by('t.transfer_date', 'DESC');
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

		$html .= '<div class="col-md-12">';
		$html .= '<div class="box box-primary">';
		$html .= '<div class="box-header with-border"><h3 class="box-title"><i class="fa fa-history"></i> Product Activity</h3></div>';
		$html .= '<div class="box-body table-responsive no-padding">';
		if(!empty($activities)){
			$html .= '<div class="item-history-filters" data-table="product_activity_table" style="display:flex;gap:8px;flex-wrap:wrap;padding:12px 15px;align-items:end;">';
			$html .= '<label style="margin:0;">Type <select class="form-control ih-type" style="min-width:130px;"><option value="">All types</option><option>Sale</option><option>Purchase</option><option>Adjustment</option><option>Online reservation</option><option>Transfer</option></select></label>';
			$html .= '<label style="margin:0;">From <input class="form-control ih-from" type="date"></label><label style="margin:0;">To <input class="form-control ih-to" type="date"></label>';
			$html .= '<label style="margin:0;min-width:190px;">Location <input class="form-control ih-location" type="search" placeholder="Search location / party"></label>';
			$html .= '<label style="margin:0;min-width:190px;">Reference / notes <input class="form-control ih-global" type="search" placeholder="Invoice, batch, note..."></label>';
			$html .= '<button type="button" class="btn btn-default ih-clear">Clear filters</button></div>';
			$html .= '<table class="table table-bordered table-striped table-condensed js-item-history-table" id="product_activity_table">';
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
		$this->db->select('bc.*, w.warehouse_name');
		$this->db->from('db_item_barcodes bc');
		$this->db->join('db_items i', 'i.id = bc.item_id AND i.store_id = '.(int)$item->store_id, 'inner', false);
		$this->db->join('db_warehouse w', 'w.id = bc.warehouse_id AND w.store_id = i.store_id', 'left');
		$originalFallback = 'NULL';
		if($this->db->table_exists('db_purchaseitems') && $this->db->table_exists('db_purchase')
			&& $this->db->field_exists('batch_lot', 'db_purchaseitems')){
			$receivedExpr = $this->db->field_exists('received_qty', 'db_purchaseitems')
				? 'COALESCE(px.received_qty, px.purchase_qty)'
				: 'px.purchase_qty';
			$hasBarcodePurchaseId = $this->db->field_exists('purchase_id', 'db_item_barcodes');
			$purchaseMatch = $hasBarcodePurchaseId
				? '(bc.purchase_id IS NULL OR bc.purchase_id = 0 OR px.purchase_id = bc.purchase_id)'
				: '1=1';
			$purchaseWarehouseMatch = 'pp.warehouse_id = bc.warehouse_id';
			$batchAttributionGuard = $hasBarcodePurchaseId
				? "(NULLIF(bc.batch_lot,'') IS NOT NULL OR COALESCE(bc.purchase_id,0) > 0)"
				: "NULLIF(bc.batch_lot,'') IS NOT NULL";
			$originalFallback = "(SELECT SUM({$receivedExpr}) FROM db_purchaseitems px
				JOIN db_purchase pp ON pp.id = px.purchase_id AND pp.store_id = px.store_id
				WHERE px.item_id = bc.item_id AND {$purchaseWarehouseMatch}
				AND px.purchase_status IN ('Received','Partially Received')
				AND {$purchaseMatch}
				AND CONVERT(IFNULL(px.batch_lot,'') USING utf8mb4) COLLATE utf8mb4_unicode_ci
					= CONVERT(IFNULL(bc.batch_lot,'') USING utf8mb4) COLLATE utf8mb4_unicode_ci
				AND {$batchAttributionGuard})";
		}
		if($this->db->field_exists('purchaseitems_id', 'db_item_barcodes') && $this->db->table_exists('db_purchaseitems')){
			$linkedReceived = $this->db->field_exists('received_qty', 'db_purchaseitems')
				? 'CASE WHEN purchase_header.warehouse_id = bc.warehouse_id THEN COALESCE(pi.received_qty, pi.purchase_qty) END' : 'NULL';
			$originalExpr = "COALESCE({$linkedReceived}, {$originalFallback})";
			$this->db->select("CASE WHEN COALESCE(bc.serial_number,'') <> '' OR COALESCE(bc.imei_number,'') <> '' THEN IF(purchase_header.warehouse_id = bc.warehouse_id, 1, 0) ELSE {$originalExpr} END AS original_qty", false);
			$this->db->join('db_purchaseitems pi', 'pi.id = bc.purchaseitems_id AND pi.item_id = bc.item_id AND pi.store_id = i.store_id', 'left');
			$purchaseHeaderId = $this->db->field_exists('purchase_id', 'db_item_barcodes')
				? 'COALESCE(pi.purchase_id, bc.purchase_id)' : 'pi.purchase_id';
			$this->db->join('db_purchase purchase_header', 'purchase_header.id = '.$purchaseHeaderId.' AND purchase_header.store_id = i.store_id', 'left', false);
		}else{
			if($this->db->table_exists('db_purchase') && $this->db->field_exists('purchase_id', 'db_item_barcodes')){
				$this->db->join('db_purchase purchase_header', 'purchase_header.id = bc.purchase_id AND purchase_header.store_id = i.store_id', 'left', false);
				$this->db->select("CASE WHEN COALESCE(bc.serial_number,'') <> '' OR COALESCE(bc.imei_number,'') <> '' THEN IF(purchase_header.warehouse_id = bc.warehouse_id, 1, 0) ELSE {$originalFallback} END AS original_qty", false);
			}else{
				$this->db->select("CASE WHEN COALESCE(bc.serial_number,'') <> '' OR COALESCE(bc.imei_number,'') <> '' THEN 1 ELSE {$originalFallback} END AS original_qty", false);
			}
		}
		$soldParts = [];
		if($this->db->table_exists('db_salesitem_batch_allocations')){
			$soldParts[] = "SELECT a.barcode_id, SUM(GREATEST(a.qty_allocated - a.qty_returned, 0)) AS sold_qty
				FROM db_salesitem_batch_allocations a
				JOIN db_sales s ON s.id = a.sales_id AND s.store_id = a.store_id
				WHERE a.store_id = ".(int)$item->store_id." AND s.sales_status = 'Final'
				GROUP BY a.barcode_id";
		}
		if($this->db->field_exists('barcode_id', 'db_salesitems')){
			$unallocatedSaleFilter = $this->db->table_exists('db_salesitem_batch_allocations')
				? ' AND NOT EXISTS (SELECT 1 FROM db_salesitem_batch_allocations ax WHERE ax.salesitems_id = si.id)'
				: '';
			$soldParts[] = "SELECT si.barcode_id, SUM(si.sales_qty) AS sold_qty
				FROM db_salesitems si
				JOIN db_sales s ON s.id = si.sales_id AND s.store_id = si.store_id
				WHERE si.store_id = ".(int)$item->store_id." AND si.item_id = ".(int)$item_id."
				AND si.barcode_id > 0 AND s.sales_status = 'Final'{$unallocatedSaleFilter}
				GROUP BY si.barcode_id";
		}
		if(!empty($soldParts)){
			$soldSubquery = 'SELECT movements.barcode_id, SUM(movements.sold_qty) AS sold_qty FROM ('.implode(' UNION ALL ', $soldParts).') movements GROUP BY movements.barcode_id';
			$this->db->select('COALESCE(batch_sold.sold_qty, 0) AS sold_qty', false);
			$this->db->join('('.$soldSubquery.') batch_sold', 'batch_sold.barcode_id = bc.id', 'left', false);
		}else{
			$this->db->select('0 AS sold_qty', false);
		}
		if($this->db->table_exists('db_stocktransfer_batch_allocations') && $this->db->table_exists('db_stocktransfer')){
			$transferMoves = "SELECT movement.barcode_id, SUM(movement.transfer_in) AS transfer_in, SUM(movement.transfer_out) AS transfer_out FROM (
				SELECT x.source_barcode_id AS barcode_id, 0 AS transfer_in, SUM(x.qty) AS transfer_out
				FROM db_stocktransfer_batch_allocations x JOIN db_stocktransfer t ON t.id = x.stocktransfer_id
				WHERE t.store_id = ".(int)$item->store_id." AND t.status = 1 GROUP BY x.source_barcode_id
				UNION ALL
				SELECT x.dest_barcode_id AS barcode_id, SUM(x.qty) AS transfer_in, 0 AS transfer_out
				FROM db_stocktransfer_batch_allocations x JOIN db_stocktransfer t ON t.id = x.stocktransfer_id
				WHERE t.store_id = ".(int)$item->store_id." AND t.status = 1 GROUP BY x.dest_barcode_id
			) movement GROUP BY movement.barcode_id";
			$this->db->select('COALESCE(batch_moves.transfer_in,0) AS transfer_in, COALESCE(batch_moves.transfer_out,0) AS transfer_out', false);
			$this->db->join('('.$transferMoves.') batch_moves', 'batch_moves.barcode_id = bc.id', 'left', false);
		}else{
			$this->db->select('0 AS transfer_in, 0 AS transfer_out', false);
		}
		$barcodeQuery = $this->db->where('bc.item_id', $item_id)->order_by('bc.warehouse_id')->order_by('bc.expire_date')->get();
		if(!$barcodeQuery){
			$dbError = $this->db->error();
			log_message('error', 'Product history batch lookup failed for item '.$item_id.': '.json_encode($dbError));
			echo json_encode(['status'=>'error','message'=>'Could not load batch stock details. Please try again.']);
			exit;
		}
		$barcodes = $barcodeQuery->result();
		if(mp_feature_enabled('serial_number_tracking')){
			foreach($barcodes as $bc){ if(!empty($bc->serial_number)){$showSerial=true;break;} }
		}
		if(mp_feature_enabled('imei_tracking')){
			foreach($barcodes as $bc){ if(!empty($bc->imei_number)){$showImei=true;break;} }
		}
		if(mp_feature_enabled('warranty_tracking')){
			foreach($barcodes as $bc){ if((int)($bc->warranty_months ?? 0)>0){$showWarranty=true;break;} }
		}
		$html .= '<div class="col-md-12">';
		$html .= '<div class="box box-info">';
		$html .= '<div class="box-header with-border"><h3 class="box-title"><i class="fa fa-cubes"></i> Unit / Batch Stock</h3></div>';
		$html .= '<div class="box-body table-responsive no-padding">';
		if(!empty($barcodes)){
			$unitLocations = [];
			foreach($barcodes as $bc){ $unitLocations[$bc->warehouse_name ?: 'Unassigned'] = true; }
			$html .= '<div class="item-history-filters" data-table="unit_batch_stock_table" style="display:flex;gap:8px;flex-wrap:wrap;padding:12px 15px;align-items:end;">';
			$html .= '<label style="margin:0;">Location <select class="form-control ih-location-select" style="min-width:160px;"><option value="">All locations</option>';
			foreach(array_keys($unitLocations) as $locationName){ $html .= '<option>'.htmlspecialchars($locationName, ENT_QUOTES, 'UTF-8').'</option>'; }
			$html .= '</select></label>';
			$html .= '<label style="margin:0;">Batch / Lot <input class="form-control ih-batch" type="search" placeholder="Search batch"></label>';
			$html .= '<label style="margin:0;">Barcode <input class="form-control ih-barcode" type="search" placeholder="Search barcode"></label>';
			if($showSerial || $showImei) $html .= '<label style="margin:0;">'.($showSerial && $showImei ? 'Serial / IMEI' : ($showSerial ? 'Serial number' : 'IMEI')).' <input class="form-control ih-identity" type="search" placeholder="Search tracked identifier"></label>';
			$html .= '<button type="button" class="btn btn-default ih-clear">Clear filters</button></div>';
			$html .= '<table class="table table-bordered table-striped table-condensed js-item-history-table" id="unit_batch_stock_table">';
			$html .= '<thead><tr><th>Location</th><th>Barcode</th><th>Batch / Lot</th>';
			if($showSerial) $html .= '<th>Serial number</th>';
			if($showImei) $html .= '<th>IMEI</th>';
			$html .= '<th>Purchase cost</th><th>Wholesale price</th><th>Retail price</th><th>Received from supplier</th><th>Transfer in</th><th>Transfer out</th><th>Sold (net returns)</th><th>Current stock</th><th>Stock status</th>';
			if($showWarranty) $html .= '<th>Warranty</th>';
			$html .= '</tr></thead>';
			$html .= '<tbody>';
			foreach($barcodes as $bc){
				$html .= '<tr>';
				$html .= '<td>'.htmlspecialchars($bc->warehouse_name ?: 'Unassigned').'</td>';
				$html .= '<td>'.htmlspecialchars($bc->barcode ?: '-').'</td>';
				$html .= '<td>'.htmlspecialchars($bc->batch_lot ?: '-').'</td>';
				if($showSerial) $html .= '<td>'.htmlspecialchars($bc->serial_number ?: '-').'</td>';
				if($showImei) $html .= '<td>'.htmlspecialchars($bc->imei_number ?: '-').'</td>';
				$html .= '<td>'.store_number_format($bc->purchase_price).'</td>';
				$html .= '<td>'.store_number_format($bc->sales_price).'</td>';
				$html .= '<td>'.store_number_format($bc->mrp).'</td>';
				$html .= '<td>'.($bc->original_qty !== null ? format_qty($bc->original_qty) : '—').'</td>';
				$html .= '<td>'.format_qty($bc->transfer_in).'</td>';
				$html .= '<td>'.format_qty($bc->transfer_out).'</td>';
				$html .= '<td>'.format_qty($bc->sold_qty).'</td>';
				$html .= '<td><strong>'.format_qty($bc->qty).'</strong></td>';
				$statusLabel = (int)$bc->status === 3 ? 'Quarantined' : ((int)$bc->status === 1 ? 'In stock' : 'Depleted / inactive');
				$html .= '<td>'.htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8').'</td>';
				if($showWarranty) $html .= '<td>'.($bc->warranty_months ? $bc->warranty_months.' mo' : '-').'</td>';
				$html .= '</tr>';
			}
			$html .= '</tbody></table>';
			$html .= '<div class="text-muted" style="padding:10px 15px;font-size:12px;">Received from supplier comes from the linked purchase receipt or a match on item, location and batch. Transfers in and out come from the batch transfer ledger. Sold is recorded batch sales minus returns. Current stock is the live batch ledger quantity. Unbatched legacy stock without a purchase link is left blank rather than guessed.</div>';
		} else {
			$html .= '<div class="text-center" style="padding:20px 10px; color:#888;"><i class="fa fa-cubes fa-2x" style="color:#d2d6de; margin-bottom:8px; display:block;"></i><p style="margin:0; font-size:13px;">No batch / unit records found.</p></div>';
		}
		$html .= '</div></div></div>';

		// Sales History
		$this->db->select('s.sales_code, s.sales_date, c.customer_name, si.sales_qty, si.unit_total_cost, si.sold_serial_number, si.sold_imei_number');
		$this->db->from('db_salesitems si');
		$this->db->join('db_sales s', 's.id = si.sales_id AND s.store_id = si.store_id', 'left');
		$this->db->join('db_customers c', 'c.id = s.customer_id AND c.store_id = s.store_id', 'left');
		$this->db->where('si.item_id', $item_id)->where('si.store_id', (int)$item->store_id);
		$this->db->where('s.sales_status', 'Final');
		$this->db->order_by('s.sales_date', 'DESC');
		$sales = $this->db->get()->result();

		$html .= '<div class="col-md-12">';
		$html .= '<div class="box box-success">';
		$html .= '<div class="box-header with-border"><h3 class="box-title"><i class="fa fa-shopping-cart"></i> Sales History</h3></div>';
		$html .= '<div class="box-body table-responsive no-padding">';
		if(!empty($sales)){
			$html .= '<div class="item-history-filters" data-table="product_sales_history_table" style="display:flex;gap:8px;flex-wrap:wrap;padding:12px 15px;align-items:end;">';
			$html .= '<label style="margin:0;">From <input class="form-control ih-from" type="date"></label><label style="margin:0;">To <input class="form-control ih-to" type="date"></label>';
			$html .= '<label style="margin:0;min-width:220px;">Invoice / customer / serial <input class="form-control ih-global" type="search" placeholder="Search sales history"></label>';
			$html .= '<button type="button" class="btn btn-default ih-clear">Clear filters</button></div>';
			$html .= '<table class="table table-bordered table-striped table-condensed js-item-history-table" id="product_sales_history_table">';
			$html .= '<thead><tr><th>Invoice</th><th>Date</th><th>Customer</th><th>Qty sold</th><th>Unit price</th>';
			if($showSerial) $html .= '<th>Serial number</th>';
			if($showImei) $html .= '<th>IMEI</th>';
			$html .= '</tr></thead>';
			$html .= '<tbody>';
			foreach($sales as $sale){
				$html .= '<tr>';
				$html .= '<td>'.$sale->sales_code.'</td>';
				$html .= '<td>'.$sale->sales_date.'</td>';
				$html .= '<td>'.($sale->customer_name ?: 'Walk-in').'</td>';
				$html .= '<td>'.$sale->sales_qty.'</td>';
				$html .= '<td>'.store_number_format($sale->unit_total_cost).'</td>';
				if($showSerial) $html .= '<td>'.htmlspecialchars($sale->sold_serial_number ?: '-').'</td>';
				if($showImei) $html .= '<td>'.htmlspecialchars($sale->sold_imei_number ?: '-').'</td>';
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

