<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Online Store Admin Controller
 * Manage storefront settings, online orders, services, and QR codes.
 */
class Online_store extends MY_Controller {

	private function _can_edit(){
		return $this->permissions('online_store_edit') || is_admin() || is_store_admin() || $this->session->userdata('role_id') == 1;
	}

	public function seed_permissions(){
		if(!is_admin() && !is_store_admin() && $this->session->userdata('role_id') != 1){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$storeId = get_current_store_id();
		$roleId = store_admin_id();
		$perms = ['online_store_view', 'online_store_edit', 'attendance_view', 'attendance_edit'];
		foreach($perms as $p){
			$exists = $this->db->where('role_id', $roleId)->where('store_id', $storeId)->where('permissions', $p)->get('db_permissions')->num_rows();
			if(!$exists){
				$this->db->insert('db_permissions', ['role_id' => $roleId, 'store_id' => $storeId, 'permissions' => $p]);
			}
		}
		echo json_encode(['status' => 'success', 'message' => 'Permissions seeded for Store Admin']);
	}

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!$this->permissions('online_store_view') && !is_admin() && !is_store_admin() && $this->session->userdata('role_id') != 1){
			$this->show_access_denied_page();
			return;
		}
		$this->load->model('storefront_model');
		$this->load->model('paystack_model','paystack');
	}

	// ============== DASHBOARD ==============

	public function index(){
		$data = array_merge($this->data, [
			'page_title' => 'Online Store Dashboard',
			'stats' => $this->storefront_model->getTodaysOrderStats(),
			'recent_orders' => $this->storefront_model->getOrders(null, null, 10, 0),
			'top_products' => $this->storefront_model->getTopOnlineProducts(null, 5)
		]);
		$this->load->view('online_store/dashboard', $data);
	}

	// ============== SETTINGS ==============

	public function settings(){
		if(!$this->_can_edit()){ echo "You Don't Have Enough Permission for this Operation!"; exit; }
		$storeId = get_current_store_id();
		$data = array_merge($this->data, [
			'page_title' => 'Online Store Settings',
			'settings' => $this->storefront_model->getSettings($storeId),
			'store' => get_store_details($storeId),
			'is_saved' => $this->db->where('store_id', $storeId)->get('db_storefront_settings')->num_rows() > 0,
			'categories' => $this->db->where('store_id', $storeId)->where('status', 1)->get('db_category')->result(),
			'warehouses' => $this->db->where('store_id', $storeId)->where('status', 1)->get('db_warehouse')->result(),
			'paystack_enabled' => $this->paystack->is_enabled()
		]);
		$this->load->view('online_store/settings', $data);
	}

	public function save_settings(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$storeId = get_current_store_id();
		if(!$storeId){
			echo json_encode(['status' => 'error', 'message' => 'Store ID not found in session']);
			return;
		}

		// Check storefront limit if creating new
		$existing = $this->storefront_model->getSettings($storeId);
		if(!$existing || empty($existing->store_slug)){
			$storefront_used = get_storefront_usage();
			$storefront_limit = get_subscription_limit('storefront_limit');
			if($storefront_limit > 0 && $storefront_used >= $storefront_limit){
				echo json_encode(['status' => 'error', 'message' => 'Storefront limit reached ('.$storefront_used.'/'.$storefront_limit.'). Contact admin to upgrade subscription.']);
				return;
			}
		}

		try {
			$slug = trim($this->input->post('store_slug'));
			$slug = strtolower(preg_replace('/[^a-z0-9-]/', '-', $slug));
			$slug = trim($slug, '-');
			if(!$slug){
				$store = get_store_details($storeId);
				$slug = strtolower(preg_replace('/[^a-z0-9-]/', '-', $store ? $store->store_name : 'store'));
				$slug = trim($slug, '-');
			}

			$data = [
				'store_slug' => $slug,
				'store_description' => $this->input->post('store_description'),
				'whatsapp_number' => $this->input->post('whatsapp_number'),
				'store_email' => $this->input->post('store_email'),
				'store_phone' => $this->input->post('store_phone'),
				'store_address' => $this->input->post('store_address'),
				'default_branch_id' => (int)$this->input->post('default_branch_id'),
				'store_status' => $this->input->post('store_status'),
				'allow_paystack' => $this->input->post('allow_paystack') ? 1 : 0,
				'allow_whatsapp' => $this->input->post('allow_whatsapp') ? 1 : 0,
				'allow_pay_on_delivery' => $this->input->post('allow_pay_on_delivery') ? 1 : 0,
				'allow_services' => $this->input->post('allow_services') ? 1 : 0,
				'allow_backorder' => $this->input->post('allow_backorder') ? 1 : 0,
				'show_search' => $this->input->post('show_search') ? 1 : 0,
				'show_categories' => $this->input->post('show_categories') ? 1 : 0,
				'show_whatsapp_cta' => $this->input->post('show_whatsapp_cta') ? 1 : 0,
				'featured_products_limit' => (int)($this->input->post('featured_products_limit') ?: 8),
				'instagram_access_token' => trim($this->input->post('instagram_access_token')),
				'instagram_username' => trim($this->input->post('instagram_username')),
				'google_places_api_key' => trim($this->input->post('google_places_api_key')),
				'gmb_place_id' => trim($this->input->post('gmb_place_id')),
				'trust_badges_json' => json_encode([
					['title' => trim($this->input->post('tb_1_title')), 'desc' => trim($this->input->post('tb_1_desc'))],
					['title' => trim($this->input->post('tb_2_title')), 'desc' => trim($this->input->post('tb_2_desc'))],
					['title' => trim($this->input->post('tb_3_title')), 'desc' => trim($this->input->post('tb_3_desc'))],
					['title' => trim($this->input->post('tb_4_title')), 'desc' => trim($this->input->post('tb_4_desc'))]
				]),
				'newsletter_title' => trim($this->input->post('newsletter_title')),
				'newsletter_subtitle' => trim($this->input->post('newsletter_subtitle'))
			];

			$result = $this->storefront_model->saveSettings($storeId, $data);
			if($result){
				echo json_encode(['status' => 'success', 'message' => 'Settings saved successfully', 'store_url' => base_url('store/' . $slug)]);
			} else {
				$err = $this->db->error();
				echo json_encode(['status' => 'error', 'message' => 'Failed to save settings. DB error: ' . ($err['message'] ?? 'Unknown')]);
			}
		} catch (Exception $e) {
			echo json_encode(['status' => 'error', 'message' => 'Exception: ' . $e->getMessage()]);
		}
	}

	public function debug_storefront(){
		if(!$this->_can_edit()){ echo json_encode(['status' => 'error', 'message' => 'Access denied']); return; }
		$storeId = get_current_store_id();
		$settings = $this->storefront_model->getSettings($storeId);
		$bySlug = $this->storefront_model->getStoreBySlug($settings->store_slug ?? '');
		$tables = [];
		foreach(['db_storefront_settings','db_online_orders','db_online_order_items','db_services','db_qr_codes'] as $t){
			$tables[$t] = $this->db->query("SHOW TABLES LIKE '$t'")->num_rows() > 0;
		}
		echo json_encode([
			'store_id' => $storeId,
			'settings' => $settings,
			'slug_lookup' => $bySlug ? 'found' : 'not found',
			'tables' => $tables
		]);
	}

	// ============== ONLINE ORDERS ==============

	public function orders(){
		$status = $this->input->get('status');
		$data = array_merge($this->data, [
			'page_title' => 'Online Orders',
			'orders' => $this->storefront_model->getOrders(null, $status ?: null, 50, 0),
			'total' => $this->storefront_model->countOrders(null, $status ?: null),
			'current_status' => $status ?: 'all'
		]);
		$this->load->view('online_store/orders', $data);
	}

	public function order_detail($orderId = 0){
		$order = $this->storefront_model->getOrder($orderId);
		if(!$order){
			show_404();
			return;
		}
		$data = array_merge($this->data, [
			'page_title' => 'Order #' . $order->order_code,
			'order' => $order,
			'items' => $this->storefront_model->getOrderItems($orderId)
		]);
		$this->load->view('online_store/order_detail', $data);
	}

	public function update_order_status(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$orderId = (int)$this->input->post('order_id');
		$status = $this->input->post('status');
		$validStatuses = ['pending','paid','processing','ready','completed','cancelled'];
		if(!in_array($status, $validStatuses)){
			echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
			return;
		}
		$this->storefront_model->updateOrderStatus($orderId, $status);
		echo json_encode(['status' => 'success', 'message' => 'Order status updated']);
	}

	public function update_payment_status(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$orderId = (int)$this->input->post('order_id');
		$status = $this->input->post('status');
		$validStatuses = ['unpaid','paid','partially_paid','failed','refunded'];
		if(!in_array($status, $validStatuses)){
			echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
			return;
		}
		$this->storefront_model->updatePaymentStatus($orderId, $status);
		echo json_encode(['status' => 'success', 'message' => 'Payment status updated']);
	}

	// ============== SERVICES ==============

	public function services(){
		$data = array_merge($this->data, [
			'page_title' => 'Services',
			'services' => $this->storefront_model->getOnlineServices(null, null, '', 100, 0),
			'categories' => $this->db->where('store_id', get_current_store_id())->where('status', 1)->get('db_category')->result()
		]);
		$this->load->view('online_store/services', $data);
	}

	public function save_service(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$serviceId = (int)$this->input->post('service_id');
		$storeId = get_current_store_id();
		$data = [
			'service_name' => trim($this->input->post('service_name')),
			'category_id' => (int)$this->input->post('category_id'),
			'price' => (float)$this->input->post('price'),
			'discount_price' => (float)$this->input->post('discount_price'),
			'service_duration' => trim($this->input->post('service_duration')),
			'description' => trim($this->input->post('description')),
			'available_online' => $this->input->post('available_online') ? 1 : 0,
			'requires_appointment' => $this->input->post('requires_appointment') ? 1 : 0,
			'requires_note' => $this->input->post('requires_note') ? 1 : 0,
			'location_type' => $this->input->post('location_type') ?: 'in-store',
			'sort_order' => (int)$this->input->post('sort_order'),
			'status' => $this->input->post('status') ? 1 : 0
		];
		if($serviceId){
			$this->db->where('id', $serviceId)->where('store_id', $storeId)->update('db_services', $data);
		} else {
			$data['store_id'] = $storeId;
			$this->db->insert('db_services', $data);
		}
		echo json_encode(['status' => 'success', 'message' => 'Service saved successfully']);
	}

	public function delete_service($id = 0){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$storeId = get_current_store_id();
		$this->db->where('id', $id)->where('store_id', $storeId)->update('db_services', ['status' => 0]);
		echo json_encode(['status' => 'success', 'message' => 'Service deleted']);
	}

	// ============== QR CODES ==============

	public function qr_codes(){
		$data = array_merge($this->data, [
			'page_title' => 'QR Codes',
			'qr_codes' => $this->storefront_model->getQrCodes(),
			'products' => $this->storefront_model->getOnlineProducts(null, null, '', 100),
			'services' => $this->storefront_model->getOnlineServices(null, null, '', 100),
			'categories' => $this->storefront_model->getCategoriesWithItems()
		]);
		$this->load->view('online_store/qr_codes', $data);
	}

	public function generate_qr(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$storeId = get_current_store_id();
		$settings = $this->storefront_model->getSettings($storeId);
		$type = $this->input->post('qr_type');
		$relatedId = (int)$this->input->post('related_id');
		$tableNumber = trim($this->input->post('table_number'));
		$name = trim($this->input->post('qr_name'));

		$url = base_url('store/' . ($settings->store_slug ?: 'store'));
		switch($type){
			case 'product':
				$url .= '/product/' . $relatedId;
				break;
			case 'service':
				$url .= '/service/' . $relatedId;
				break;
			case 'category':
				$url .= '/products?category=' . $relatedId;
				break;
			case 'table':
				$url .= '?table=' . urlencode($tableNumber);
				break;
			case 'attendance':
				$url = base_url('attendance/clockin');
				break;
		}

		if(!is_dir('./uploads/qr/')){
			mkdir('./uploads/qr/', 0777, true);
		}
		$filename = 'qr-' . $type . '-' . time() . '.png';
		$filepath = './uploads/qr/' . $filename;

		// Use chillerlan/php-qrcode via Composer (v5 API)
		$options = new \chillerlan\QRCode\QROptions([
			'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
			'eccLevel'   => \chillerlan\QRCode\QRCode::ECC_H,
			'scale'      => 10,
			'imageBase64'=> false,
		]);
		$qrcode = new \chillerlan\QRCode\QRCode($options);
		$imageData = $qrcode->render($url);
		file_put_contents($filepath, $imageData);

		$qrData = [
			'store_id' => $storeId,
			'qr_name' => $name ?: ucfirst($type) . ' QR',
			'qr_type' => $type,
			'related_id' => $relatedId,
			'table_number' => $tableNumber,
			'qr_image' => 'uploads/qr/' . $filename,
			'qr_data' => $url
		];
		$qrId = $this->storefront_model->createQrCode($qrData);

		echo json_encode(['status' => 'success', 'message' => 'QR Code generated', 'qr_id' => $qrId]);
	}

	public function delete_qr($id = 0){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$qr = $this->storefront_model->getQrCode($id);
		if($qr && file_exists($qr->qr_image)){
			unlink($qr->qr_image);
		}
		$this->storefront_model->deleteQrCode($id);
		echo json_encode(['status' => 'success', 'message' => 'QR Code deleted']);
	}

	// ============== PRODUCTS ONLINE STATUS ==============

	public function products_online(){
		$search = trim($this->input->get('search'));
		$this->db->select('a.id, a.item_name, a.item_image, a.stock, a.sales_price, a.online_price, a.publish_online, a.status, b.category_name');
		$this->db->from('db_items a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.store_id', get_current_store_id());
		$this->db->where('a.service_bit', 0);
		$this->db->where("(a.item_group IS NULL OR a.item_group='Single')");
		if($search){
			$this->db->group_start();
			$this->db->like('a.item_name', $search);
			$this->db->or_like('a.item_code', $search);
			$this->db->group_end();
		}
		$this->db->order_by('a.id', 'desc');
		$this->db->limit(100);
		$products = $this->db->get()->result();

		$data = array_merge($this->data, [
			'page_title' => 'Online Products',
			'products' => $products,
			'search' => $search
		]);
		$this->load->view('online_store/products_online', $data);
	}

	public function toggle_product_online(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$productId = (int)$this->input->post('product_id');
		$storeId = get_current_store_id();
		$product = $this->db->where('id', $productId)->where('store_id', $storeId)->get('db_items')->row();
		if(!$product){
			echo json_encode(['status' => 'error', 'message' => 'Product not found']);
			return;
		}
		$newVal = $product->publish_online ? 0 : 1;
		$this->db->where('id', $productId)->update('db_items', ['publish_online' => $newVal]);
		echo json_encode(['status' => 'success', 'publish_online' => $newVal]);
	}

	public function update_online_price(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$productId = (int)$this->input->post('product_id');
		$price = (float)$this->input->post('online_price');
		$storeId = get_current_store_id();
		$this->db->where('id', $productId)->where('store_id', $storeId)->update('db_items', ['online_price' => $price]);
		echo json_encode(['status' => 'success', 'message' => 'Price updated']);
	}

	// ============== APPEARANCE ==============

	public function appearance(){
		if(!$this->_can_edit()){ echo "You Don't Have Enough Permission for this Operation!"; exit; }
		$storeId = get_current_store_id();
		$settings = $this->storefront_model->getSettings($storeId);
		$data = array_merge($this->data, [
			'page_title' => 'Appearance',
			'settings' => $settings,
			'themes' => $this->storefront_model->getAllThemes(),
			'current_theme' => $this->storefront_model->getTheme($settings->theme_id),
			'store' => get_store_details($storeId)
		]);
		$this->load->view('online_store/appearance', $data);
	}

	public function save_appearance(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$storeId = get_current_store_id();
		$data = [
			'theme_id' => (int)$this->input->post('theme_id') ?: null,
			'primary_color' => $this->input->post('primary_color') ?: '#3B82F6',
			'secondary_color' => $this->input->post('secondary_color') ?: '#10B981',
			'footer_bg_color' => $this->input->post('footer_bg_color') ?: '#0F172A',
			'footer_text_color' => $this->input->post('footer_text_color') ?: '#94A3B8',
			'header_text_color' => trim($this->input->post('header_text_color')),
			'button_color' => $this->input->post('button_color') ?: '#3B82F6',
			'font_family' => $this->input->post('font_family') ?: 'Inter',
			'button_style' => $this->input->post('button_style') ?: 'rounded',
			'store_headline' => trim($this->input->post('store_headline')),
			'store_subheadline' => trim($this->input->post('store_subheadline')),
			'footer_style' => $this->input->post('footer_style') ?: 'standard',
			'footer_about_us' => trim($this->input->post('footer_about_us')),
			'footer_address_url' => trim($this->input->post('footer_address_url')),
			'instagram_url' => trim($this->input->post('instagram_url')),
			'facebook_url' => trim($this->input->post('facebook_url')),
			'tiktok_url' => trim($this->input->post('tiktok_url')),
			'x_url' => trim($this->input->post('x_url')),
			'youtube_url' => trim($this->input->post('youtube_url')),
			'business_hours' => trim($this->input->post('business_hours')),
			'announcement_bar' => trim($this->input->post('announcement_bar')),
			'announcement_bar_color' => $this->input->post('announcement_bar_color') ?: '#0F172A',
			'meta_title' => trim($this->input->post('meta_title')),
			'meta_description' => trim($this->input->post('meta_description')),
			'meta_keywords' => trim($this->input->post('meta_keywords')),
			'google_analytics_id' => trim($this->input->post('google_analytics_id')),
			'facebook_pixel_id' => trim($this->input->post('facebook_pixel_id')),
			'robots_index' => (int)$this->input->post('robots_index'),
			'custom_head_scripts' => trim($this->input->post('custom_head_scripts'))
		];
		$ok = $this->storefront_model->saveSettings($storeId, $data);
		if($ok){
			echo json_encode(['status' => 'success', 'message' => 'Appearance saved']);
		} else {
			$dbError = $this->db->error();
			echo json_encode(['status' => 'error', 'message' => 'Save failed: ' . ($dbError['message'] ?? 'Database error')]);
		}
	}

	// ============== BANNERS ==============

	public function banners(){
		if(!$this->_can_edit()){ echo "You Don't Have Enough Permission for this Operation!"; exit; }
		$storeId = get_current_store_id();
		$data = array_merge($this->data, [
			'page_title' => 'Banners',
			'banners' => $this->storefront_model->getBanners($storeId)
		]);
		$this->load->view('online_store/banners', $data);
	}

	public function banner_form($id = 0){
		if(!$this->_can_edit()){ echo "You Don't Have Enough Permission for this Operation!"; exit; }
		$storeId = get_current_store_id();
		$banner = $id ? $this->storefront_model->getBanner($id, $storeId) : null;
		$data = array_merge($this->data, [
			'page_title' => $banner ? 'Edit Banner' : 'Add Banner',
			'banner' => $banner
		]);
		$this->load->view('online_store/banner_form', $data);
	}

	public function save_banner(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$storeId = get_current_store_id();
		$bannerId = (int)$this->input->post('banner_id');
		$data = [
			'banner_type' => $this->input->post('banner_type') ?: 'hero',
			'banner_title' => trim($this->input->post('banner_title')),
			'banner_subtitle' => trim($this->input->post('banner_subtitle')),
			'button_text' => trim($this->input->post('button_text')),
			'button_url' => trim($this->input->post('button_url')),
			'display_order' => (int)$this->input->post('display_order'),
			'status' => $this->input->post('status') ? 1 : 0,
			'start_date' => $this->input->post('start_date') ?: null,
			'end_date' => $this->input->post('end_date') ?: null
		];

		$uploadDir = './uploads/storefront/' . $storeId . '/';
		if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

		if(!empty($_FILES['desktop_image']['name']) || !empty($_FILES['mobile_image']['name'])){
			$media_check = check_media_storage_limit();
			if($media_check !== true){
				echo json_encode(['status' => 'error', 'message' => $media_check]);
				return;
			}
		}

		if(!empty($_FILES['desktop_image']['name'])){
			$config = ['upload_path' => $uploadDir, 'allowed_types' => 'jpg|jpeg|png|gif|webp', 'max_size' => 2048, 'file_name' => 'desktop_' . time()];
			$this->load->library('upload');
			$this->upload->initialize($config);
			if($this->upload->do_upload('desktop_image')){
				$up = $this->upload->data();
				$data['desktop_image'] = 'uploads/storefront/' . $storeId . '/' . $up['file_name'];
			}
		}
		if(!empty($_FILES['mobile_image']['name'])){
			$config = ['upload_path' => $uploadDir, 'allowed_types' => 'jpg|jpeg|png|gif|webp', 'max_size' => 2048, 'file_name' => 'mobile_' . time()];
			$this->upload->initialize($config);
			if($this->upload->do_upload('mobile_image')){
				$up = $this->upload->data();
				$data['mobile_image'] = 'uploads/storefront/' . $storeId . '/' . $up['file_name'];
			}
		}

		if(!$bannerId){
			$data['store_id'] = $storeId;
		}
		$this->storefront_model->saveBanner($data, $bannerId ?: null);
		echo json_encode(['status' => 'success', 'message' => 'Banner saved']);
	}

	public function delete_banner($id = 0){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$this->storefront_model->deleteBanner($id);
		echo json_encode(['status' => 'success', 'message' => 'Banner deleted']);
	}

	// ============== HOMEPAGE BUILDER ==============

	public function homepage_builder(){
		if(!$this->_can_edit()){ echo "You Don't Have Enough Permission for this Operation!"; exit; }
		$storeId = get_current_store_id();
		$sections = $this->storefront_model->getHomepageSections($storeId);
		if(empty($sections)){
			$this->storefront_model->resetHomepageSections($storeId);
			$sections = $this->storefront_model->getHomepageSections($storeId);
		}
		$data = array_merge($this->data, [
			'page_title' => 'Homepage Builder',
			'homepage_sections' => $sections,
			'settings' => $this->storefront_model->getSettings($storeId),
			'store' => get_store_details($storeId)
		]);
		$this->load->view('online_store/homepage_builder', $data);
	}

	public function save_homepage_sections(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$storeId = get_current_store_id();
		$sections = $this->input->post('sections');
		if(is_array($sections)){
			foreach($sections as $key => $val){
				$this->storefront_model->saveHomepageSection($storeId, $key, $val['enabled'] ?? 0, $val['order'] ?? 0);
			}
		}
		echo json_encode(['status' => 'success', 'message' => 'Homepage layout saved']);
	}

	public function duplicate_homepage_section(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$storeId = get_current_store_id();
		$sectionKey = $this->input->post('section_key');
		if(!$sectionKey){
			echo json_encode(['status' => 'error', 'message' => 'No section key provided']);
			return;
		}
		$duplicable = ['hero_banner','promo_banner','featured_products','featured_services','featured_categories','testimonials','brands','instagram_gallery'];
		$baseKey = preg_replace('/_\d+$/', '', $sectionKey);
		if(!in_array($baseKey, $duplicable)){
			echo json_encode(['status' => 'error', 'message' => 'This section cannot be duplicated']);
			return;
		}
		$result = $this->storefront_model->duplicateHomepageSection($storeId, $sectionKey);
		if($result){
			echo json_encode(['status' => 'success', 'message' => 'Section duplicated']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Failed to duplicate section']);
		}
	}

	// ============== ANALYTICS ==============

	public function analytics(){
		if(!$this->_can_edit()){ echo "You Don't Have Enough Permission for this Operation!"; exit; }
		$storeId = get_current_store_id();
		$filter = $this->input->get('filter') ?: 'month';
		$customStart = $this->input->get('start');
		$customEnd = $this->input->get('end');

		$startDate = null; $endDate = null;
		$rangeLabel = '';
		switch($filter){
			case 'today':
				$startDate = date('Y-m-d 00:00:00');
				$endDate = date('Y-m-d 23:59:59');
				$rangeLabel = 'Today';
				break;
			case 'week':
				$startDate = date('Y-m-d 00:00:00', strtotime('monday this week'));
				$endDate = date('Y-m-d 23:59:59', strtotime('sunday this week'));
				$rangeLabel = 'This Week';
				break;
			case 'month':
				$startDate = date('Y-m-01 00:00:00');
				$endDate = date('Y-m-t 23:59:59');
				$rangeLabel = date('F Y');
				break;
			case 'year':
				$startDate = date('Y-01-01 00:00:00');
				$endDate = date('Y-12-31 23:59:59');
				$rangeLabel = date('Y');
				break;
			case 'custom':
				$startDate = $customStart ? date('Y-m-d 00:00:00', strtotime($customStart)) : date('Y-m-d 00:00:00', strtotime('-30 days'));
				$endDate = $customEnd ? date('Y-m-d 23:59:59', strtotime($customEnd)) : date('Y-m-d 23:59:59');
				$rangeLabel = date('M j, Y', strtotime($startDate)) . ' - ' . date('M j, Y', strtotime($endDate));
				break;
			default:
				$startDate = date('Y-m-01 00:00:00');
				$endDate = date('Y-m-t 23:59:59');
				$rangeLabel = date('F Y');
				$filter = 'month';
		}

		$chartData = [];
		$chartLabels = [];
		$chartType = 'day';
		if($filter == 'today'){
			$chartType = 'hour';
			$hourly = $this->storefront_model->getVisitsByHour($storeId, date('Y-m-d'));
			for($h=0; $h<24; $h++){
				$found = null;
				foreach($hourly as $row){ if((int)$row->hour === $h){ $found = $row; break; } }
				$chartLabels[] = sprintf('%02d:00', $h);
				$chartData[] = (int)($found->visits ?? 0);
			}
		}elseif($filter == 'year'){
			$chartType = 'month';
			$monthly = $this->storefront_model->getVisitsByMonth($storeId, $startDate, $endDate);
			for($m=1; $m<=12; $m++){
				$monthKey = date('Y') . '-' . sprintf('%02d', $m);
				$found = null;
				foreach($monthly as $row){ if($row->month == $monthKey){ $found = $row; break; } }
				$chartLabels[] = date('M', mktime(0,0,0,$m,1));
				$chartData[] = (int)($found->visits ?? 0);
			}
		}else{
			// week, month, custom => show daily bars
			$chartType = 'day';
			$daily = $this->storefront_model->getDailyVisits($storeId, $startDate, $endDate);
			$periodStart = new DateTime($startDate);
			$periodEnd = new DateTime($endDate);
			$interval = new DateInterval('P1D');
			$period = new DatePeriod($periodStart, $interval, $periodEnd->modify('+1 day'));
			foreach($period as $dt){
				$d = $dt->format('Y-m-d');
				$found = null;
				foreach($daily as $row){ if($row->date == $d){ $found = $row; break; } }
				$chartLabels[] = $dt->format('j');
				$chartData[] = (int)($found->visits ?? 0);
			}
		}

		$data = array_merge($this->data, [
			'page_title' => 'Store Analytics',
			'summary' => $this->storefront_model->getAnalyticsSummary($storeId, $startDate, $endDate),
			'top_sources' => $this->storefront_model->getTopSources($storeId, $startDate, $endDate),
			'top_pages' => $this->storefront_model->getTopPages($storeId, $startDate, $endDate),
			'daily_visits' => $this->storefront_model->getDailyVisits($storeId, $startDate, $endDate),
			'chart_labels' => $chartLabels,
			'chart_data' => $chartData,
			'chart_type' => $chartType,
			'heatmap' => $this->storefront_model->getHeatmapData($storeId, $startDate, $endDate),
			'devices' => $this->storefront_model->getDeviceBreakdown($storeId, $startDate, $endDate),
			'search_terms' => $this->storefront_model->getSearchTerms($storeId, $startDate, $endDate),
			'customers' => $this->storefront_model->getCustomerVisits($storeId, $startDate, $endDate),
			'recent_visits' => $this->storefront_model->getRecentVisits($storeId, 50),
			'filter' => $filter,
			'range_label' => $rangeLabel,
			'start_date' => date('Y-m-d', strtotime($startDate)),
			'end_date' => date('Y-m-d', strtotime($endDate))
		]);
		$this->load->view('online_store/analytics', $data);
	}

	public function export_analytics(){
		if(!$this->_can_edit()){ echo "You Don't Have Enough Permission for this Operation!"; exit; }
		$storeId = get_current_store_id();
		$filter = $this->input->get('filter') ?: 'month';
		$customStart = $this->input->get('start');
		$customEnd = $this->input->get('end');
		$startDate = null; $endDate = null;
		switch($filter){
			case 'today':
				$startDate = date('Y-m-d 00:00:00'); $endDate = date('Y-m-d 23:59:59'); break;
			case 'week':
				$startDate = date('Y-m-d 00:00:00', strtotime('monday this week'));
				$endDate = date('Y-m-d 23:59:59', strtotime('sunday this week'));
				break;
			case 'month':
				$startDate = date('Y-m-01 00:00:00'); $endDate = date('Y-m-t 23:59:59'); break;
			case 'year':
				$startDate = date('Y-01-01 00:00:00'); $endDate = date('Y-12-31 23:59:59'); break;
			case 'custom':
				$startDate = $customStart ? date('Y-m-d 00:00:00', strtotime($customStart)) : date('Y-m-d 00:00:00', strtotime('-30 days'));
				$endDate = $customEnd ? date('Y-m-d 23:59:59', strtotime($customEnd)) : date('Y-m-d 23:59:59');
				break;
			default:
				$startDate = date('Y-m-01 00:00:00'); $endDate = date('Y-m-t 23:59:59');
		}
		$data = [
			'page_title' => 'Store Analytics Report',
			'summary' => $this->storefront_model->getAnalyticsSummary($storeId, $startDate, $endDate),
			'top_sources' => $this->storefront_model->getTopSources($storeId, $startDate, $endDate),
			'top_pages' => $this->storefront_model->getTopPages($storeId, $startDate, $endDate),
			'devices' => $this->storefront_model->getDeviceBreakdown($storeId, $startDate, $endDate),
			'search_terms' => $this->storefront_model->getSearchTerms($storeId, $startDate, $endDate),
			'customers' => $this->storefront_model->getCustomerVisits($storeId, $startDate, $endDate),
			'filter' => $filter,
			'range_label' => date('M j, Y', strtotime($startDate)) . ' - ' . date('M j, Y', strtotime($endDate)),
			'generated_at' => date('Y-m-d H:i:s')
		];
		$html = $this->load->view('online_store/analytics_pdf', $data, true);
		require_once FCPATH . 'vendor/autoload.php';
		$dompdf = new \Dompdf\Dompdf();
		$dompdf->loadHtml($html);
		$dompdf->setPaper('A4', 'landscape');
		$dompdf->render();
		$dompdf->stream('analytics-' . date('Ymd') . '.pdf', ['Attachment' => true]);
	}

	public function track_visit(){
		$storeId = (int)$this->input->post('store_id');
		if(!$storeId){
			echo json_encode(['status' => 'error']);
			return;
		}
		$pageUrl = trim($this->input->post('page_url'));
		$referrer = trim($this->input->post('referrer'));
		$source = '';
		if(stripos($referrer, 'facebook.com') !== false || stripos($referrer, 'fb.com') !== false) $source = 'Facebook';
		elseif(stripos($referrer, 'instagram.com') !== false) $source = 'Instagram';
		elseif(stripos($referrer, 'twitter.com') !== false || stripos($referrer, 'x.com') !== false) $source = 'X/Twitter';
		elseif(stripos($referrer, 'google.') !== false) $source = 'Google';
		elseif(stripos($referrer, 'tiktok.com') !== false) $source = 'TikTok';
		elseif(stripos($referrer, 'youtube.com') !== false) $source = 'YouTube';
		elseif(stripos($referrer, 'whatsapp.com') !== false) $source = 'WhatsApp';
		elseif(!empty($referrer)) $source = 'Other';
		else $source = 'Direct';

		// Extract search term from page URL or direct POST
		$searchTerm = trim($this->input->post('search_term'));
		if(empty($searchTerm) && $pageUrl && strpos($pageUrl, 'search=') !== false){
			preg_match('/search=([^&]+)/', $pageUrl, $m);
			if(!empty($m[1])) $searchTerm = urldecode($m[1]);
		}

		$this->storefront_model->trackVisit($storeId, [
			'page_url' => substr($pageUrl, 0, 500),
			'referrer' => substr($referrer, 0, 500),
			'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
			'ip_address' => $this->input->ip_address(),
			'session_id' => session_id() ?: uniqid('sess_', true),
			'source' => $source,
			'search_term' => substr($searchTerm, 0, 255)
		]);
		echo json_encode(['status' => 'success']);
	}

	public function preview_store(){
		if(!$this->_can_edit()){ echo "You Don't Have Enough Permission for this Operation!"; exit; }
		$storeId = get_current_store_id();
		$themeId = (int)$this->input->get('theme_id');
		$settings = $this->storefront_model->getSettings($storeId);
		if($themeId){
			$this->storefront_model->saveSettings($storeId, ['preview_mode' => 1, 'preview_theme_id' => $themeId]);
		} else {
			$this->storefront_model->saveSettings($storeId, ['preview_mode' => 0, 'preview_theme_id' => null]);
		}
		redirect(base_url('store/' . ($settings->store_slug ?? '')));
	}

	// ============== DOMAINS ==============

	public function domains(){
		if(!$this->_can_edit()){ echo "You Don't Have Enough Permission for this Operation!"; exit; }
		$storeId = get_current_store_id();
		$data = array_merge($this->data, [
			'page_title' => 'Domain Settings',
			'domains' => $this->storefront_model->getDomains($storeId),
			'settings' => $this->storefront_model->getSettings($storeId)
		]);
		$this->load->view('online_store/domains', $data);
	}

	public function save_domain(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$storeId = get_current_store_id();
		$domainId = (int)$this->input->post('domain_id');
		// Check custom domain limit on new domain
		if(!$domainId){
			$domain_used = get_custom_domain_usage();
			$domain_limit = get_subscription_limit('custom_domain_limit');
			if($domain_limit > 0 && $domain_used >= $domain_limit){
				echo json_encode(['status' => 'error', 'message' => 'Custom domain limit reached ('.$domain_used.'/'.$domain_limit.'). Contact admin to upgrade subscription.']);
				return;
			}
		}
		$type = $this->input->post('domain_type');
		$value = strtolower(trim($this->input->post('domain_value')));
		if(!$value){
			echo json_encode(['status' => 'error', 'message' => 'Domain is required']);
			return;
		}
		$data = [
			'domain_type' => $type,
			'domain_value' => $value,
			'dns_instructions' => $this->input->post('dns_instructions')
		];
		if($domainId){
			$this->storefront_model->saveDomain($data, $domainId);
		} else {
			$data['store_id'] = $storeId;
			$data['verification_status'] = 'pending';
			$data['connection_status'] = 'pending';
			$this->storefront_model->saveDomain($data);
		}
		echo json_encode(['status' => 'success', 'message' => 'Domain saved']);
	}

	public function update_domain_status(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$domainId = (int)$this->input->post('domain_id');
		$status = $this->input->post('connection_status');
		$this->db->where('id', $domainId)->update('db_storefront_domains', [
			'connection_status' => $status,
			'verification_status' => $status === 'connected' ? 'verified' : 'pending',
			'verified_at' => $status === 'connected' ? date('Y-m-d H:i:s') : null
		]);
		echo json_encode(['status' => 'success', 'message' => 'Status updated']);
	}

	public function delete_domain($id = 0){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$this->storefront_model->deleteDomain($id);
		echo json_encode(['status' => 'success', 'message' => 'Domain deleted']);
	}

	// ============== STOREFRONT BRANDS ==============

	public function brands(){
		if(!$this->_can_edit()){ show_404(); exit; }
		$storeId = get_current_store_id();
		$data = array_merge($this->data, [
			'page_title' => 'Storefront Brands',
			'brands' => $this->storefront_model->getStorefrontBrands($storeId, false)
		]);
		$this->load->view('online_store/brands', $data);
	}

	public function save_brand(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		try{
			$storeId = get_current_store_id();
			$id = (int)$this->input->post('brand_id');
			$brandData = [
				'store_id' => $storeId,
				'brand_name' => trim($this->input->post('brand_name')),
				'brand_url' => trim($this->input->post('brand_url')),
				'is_enabled' => $this->input->post('is_enabled') ? 1 : 0,
				'sort_order' => (int)$this->input->post('sort_order')
			];
			if(!empty($_FILES['brand_logo']['name'])){
				$media_check = check_media_storage_limit();
				if($media_check !== true){
					echo json_encode(['status' => 'error', 'message' => $media_check]);
					return;
				}
				$config['upload_path'] = './uploads/storefront/';
				$config['allowed_types'] = 'jpg|jpeg|png|webp|svg';
				$config['max_size'] = 2048;
				if(!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0777, true);
				$this->load->library('upload', $config);
				if($this->upload->do_upload('brand_logo')){
					$uploadData = $this->upload->data();
					$brandData['brand_logo'] = 'uploads/storefront/' . $uploadData['file_name'];
				}
			}
			$result = $this->storefront_model->saveStorefrontBrand($brandData, $id ?: null);
			if($result === false){
				$err = $this->db->error();
				echo json_encode(['status' => 'error', 'message' => 'Failed to save brand. ' . ($err['message'] ?? '')]);
			} else {
				echo json_encode(['status' => 'success', 'message' => 'Brand saved']);
			}
		} catch(Exception $e){
			echo json_encode(['status' => 'error', 'message' => 'Error: '.$e->getMessage()]);
		}
	}

	public function delete_brand($id = 0){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		try{
			$this->storefront_model->deleteStorefrontBrand($id);
			echo json_encode(['status' => 'success', 'message' => 'Brand deleted']);
		} catch(Exception $e){
			echo json_encode(['status' => 'error', 'message' => 'Error: '.$e->getMessage()]);
		}
	}

	// ============== STOREFRONT TESTIMONIALS ==============

	public function testimonials(){
		if(!$this->_can_edit()){ show_404(); exit; }
		$storeId = get_current_store_id();
		$data = array_merge($this->data, [
			'page_title' => 'Storefront Testimonials',
			'testimonials' => $this->storefront_model->getStorefrontTestimonials($storeId, false)
		]);
		$this->load->view('online_store/testimonials', $data);
	}

	public function save_testimonial(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		try{
			$storeId = get_current_store_id();
			$id = (int)$this->input->post('testimonial_id');
			$tData = [
				'store_id' => $storeId,
				'customer_name' => trim($this->input->post('customer_name')),
				'testimonial_text' => trim($this->input->post('testimonial_text')),
				'rating' => min(5, max(1, (int)$this->input->post('rating'))),
				'is_enabled' => $this->input->post('is_enabled') ? 1 : 0,
				'sort_order' => (int)$this->input->post('sort_order')
			];
			if(!empty($_FILES['customer_photo']['name'])){
				$media_check = check_media_storage_limit();
				if($media_check !== true){
					echo json_encode(['status' => 'error', 'message' => $media_check]);
					return;
				}
				$config['upload_path'] = './uploads/storefront/';
				$config['allowed_types'] = 'jpg|jpeg|png|webp';
				$config['max_size'] = 2048;
				if(!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0777, true);
				$this->load->library('upload', $config);
				if($this->upload->do_upload('customer_photo')){
					$uploadData = $this->upload->data();
					$tData['customer_photo'] = 'uploads/storefront/' . $uploadData['file_name'];
				}
			}
			$result = $this->storefront_model->saveStorefrontTestimonial($tData, $id ?: null);
			if($result === false){
				$err = $this->db->error();
				echo json_encode(['status' => 'error', 'message' => 'Failed to save testimonial. ' . ($err['message'] ?? '')]);
			} else {
				echo json_encode(['status' => 'success', 'message' => 'Testimonial saved']);
			}
		} catch(Exception $e){
			echo json_encode(['status' => 'error', 'message' => 'Error: '.$e->getMessage()]);
		}
	}

	public function delete_testimonial($id = 0){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		try{
			$this->storefront_model->deleteStorefrontTestimonial($id);
			echo json_encode(['status' => 'success', 'message' => 'Testimonial deleted']);
		} catch(Exception $e){
			echo json_encode(['status' => 'error', 'message' => 'Error: '.$e->getMessage()]);
		}
	}

	// ============== STOREFRONT INSTAGRAM ==============

	public function instagram(){
		if(!$this->_can_edit()){ show_404(); exit; }
		$storeId = get_current_store_id();
		$data = array_merge($this->data, [
			'page_title' => 'Instagram Gallery',
			'posts' => $this->storefront_model->getStorefrontInstagram($storeId, false)
		]);
		$this->load->view('online_store/instagram', $data);
	}

	public function save_instagram(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		try{
			$storeId = get_current_store_id();
			$id = (int)$this->input->post('post_id');
			$igData = [
				'store_id' => $storeId,
				'caption' => trim($this->input->post('caption')),
				'link_url' => trim($this->input->post('link_url')),
				'is_enabled' => $this->input->post('is_enabled') ? 1 : 0,
				'sort_order' => (int)$this->input->post('sort_order')
			];
			if(!empty($_FILES['post_image']['name'])){
				$media_check = check_media_storage_limit();
				if($media_check !== true){
					echo json_encode(['status' => 'error', 'message' => $media_check]);
					return;
				}
				$config['upload_path'] = './uploads/storefront/';
				$config['allowed_types'] = 'jpg|jpeg|png|webp';
				$config['max_size'] = 2048;
				if(!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0777, true);
				$this->load->library('upload', $config);
				if($this->upload->do_upload('post_image')){
					$uploadData = $this->upload->data();
					$igData['image_url'] = 'uploads/storefront/' . $uploadData['file_name'];
				}
			} else if(!$id){
				echo json_encode(['status' => 'error', 'message' => 'Image is required']); return;
			}
			$result = $this->storefront_model->saveStorefrontInstagram($igData, $id ?: null);
			if($result === false){
				$err = $this->db->error();
				echo json_encode(['status' => 'error', 'message' => 'Failed to save post. ' . ($err['message'] ?? '')]);
			} else {
				echo json_encode(['status' => 'success', 'message' => 'Post saved']);
			}
		} catch(Exception $e){
			echo json_encode(['status' => 'error', 'message' => 'Error: '.$e->getMessage()]);
		}
	}

	public function delete_instagram($id = 0){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		try{
			$this->storefront_model->deleteStorefrontInstagram($id);
			echo json_encode(['status' => 'success', 'message' => 'Post deleted']);
		} catch(Exception $e){
			echo json_encode(['status' => 'error', 'message' => 'Error: '.$e->getMessage()]);
		}
	}

	// ============== STOREFRONT FAQS ==============

	public function faqs(){
		if(!$this->_can_edit()){ show_404(); exit; }
		$storeId = get_current_store_id();
		$data = array_merge($this->data, [
			'page_title' => 'Storefront FAQs',
			'faqs' => $this->storefront_model->getStorefrontFaqs($storeId, false)
		]);
		$this->load->view('online_store/faqs', $data);
	}

	public function save_faq(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		try{
			$storeId = get_current_store_id();
			$id = (int)$this->input->post('faq_id');
			$faqData = [
				'store_id' => $storeId,
				'question' => trim($this->input->post('question')),
				'answer' => trim($this->input->post('answer')),
				'is_enabled' => $this->input->post('is_enabled') ? 1 : 0,
				'sort_order' => (int)$this->input->post('sort_order')
			];
			$result = $this->storefront_model->saveStorefrontFaq($faqData, $id ?: null);
			if($result === false){
				$err = $this->db->error();
				echo json_encode(['status' => 'error', 'message' => 'Failed to save FAQ. ' . ($err['message'] ?? '')]);
			} else {
				echo json_encode(['status' => 'success', 'message' => 'FAQ saved']);
			}
		} catch(Exception $e){
			echo json_encode(['status' => 'error', 'message' => 'Error: '.$e->getMessage()]);
		}
	}

	public function delete_faq($id = 0){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		try{
			$this->storefront_model->deleteStorefrontFaq($id);
			echo json_encode(['status' => 'success', 'message' => 'FAQ deleted']);
		} catch(Exception $e){
			echo json_encode(['status' => 'error', 'message' => 'Error: '.$e->getMessage()]);
		}
	}

	// ============== INSTAGRAM SYNC ==============

	public function fetch_instagram(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		try{
			$storeId = get_current_store_id();
			$settings = $this->storefront_model->getSettings($storeId);
			$token = trim($settings->instagram_access_token ?? '');
			if(!$token){
				echo json_encode(['status' => 'error', 'message' => 'Instagram access token not configured. Add it in Settings.']); return;
			}
			$url = 'https://graph.instagram.com/me/media?fields=id,caption,media_url,permalink,thumbnail_url&access_token='.urlencode($token).'&limit=10';
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			$response = curl_exec($ch);
			$err = curl_error($ch);
			curl_close($ch);
			if($err){
				echo json_encode(['status' => 'error', 'message' => 'Connection error: '.$err]); return;
			}
			$data = json_decode($response, true);
			if(!empty($data['error'])){
				echo json_encode(['status' => 'error', 'message' => 'Instagram API error: '.($data['error']['message'] ?? 'Unknown')]); return;
			}
			$posts = $data['data'] ?? [];
			if(empty($posts)){
				echo json_encode(['status' => 'error', 'message' => 'No posts found. Check your access token.']); return;
			}
			// Clear existing auto-fetched posts (optional: keep manual ones)
			$this->db->where('store_id', $storeId)->delete('db_storefront_instagram');
			$count = 0;
			foreach($posts as $p){
				$imageUrl = $p['thumbnail_url'] ?? $p['media_url'] ?? '';
				if(!$imageUrl) continue;
				$this->storefront_model->saveStorefrontInstagram([
					'store_id' => $storeId,
					'image_url' => $imageUrl,
					'caption' => $p['caption'] ?? '',
					'link_url' => $p['permalink'] ?? '',
					'is_enabled' => 1,
					'sort_order' => $count
				]);
				$count++;
			}
			echo json_encode(['status' => 'success', 'message' => $count.' Instagram posts fetched and saved.']);
		} catch(Exception $e){
			echo json_encode(['status' => 'error', 'message' => 'Error: '.$e->getMessage()]);
		}
	}

	// ============== GOOGLE MY BUSINESS SYNC ==============

	public function fetch_gmb_reviews(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		try{
			$storeId = get_current_store_id();
			$settings = $this->storefront_model->getSettings($storeId);
			$apiKey = trim($settings->google_places_api_key ?? '');
			$placeId = trim($settings->gmb_place_id ?? '');
			if(!$apiKey || !$placeId){
				echo json_encode(['status' => 'error', 'message' => 'Google Places API Key and GMB Place ID are required. Add them in Settings.']); return;
			}
			$url = 'https://maps.googleapis.com/maps/api/place/details/json?place_id='.urlencode($placeId).'&fields=reviews&key='.urlencode($apiKey);
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			$response = curl_exec($ch);
			$err = curl_error($ch);
			curl_close($ch);
			if($err){
				echo json_encode(['status' => 'error', 'message' => 'Connection error: '.$err]); return;
			}
			$data = json_decode($response, true);
			if(!empty($data['error_message'])){
				echo json_encode(['status' => 'error', 'message' => 'Google API error: '.$data['error_message']]); return;
			}
			$reviews = $data['result']['reviews'] ?? [];
			if(empty($reviews)){
				echo json_encode(['status' => 'error', 'message' => 'No reviews found for this Place ID.']); return;
			}
			$count = 0;
			foreach($reviews as $r){
				$this->storefront_model->saveStorefrontTestimonial([
					'store_id' => $storeId,
					'customer_name' => $r['author_name'] ?? 'Google User',
					'testimonial_text' => $r['text'] ?? '',
					'rating' => min(5, max(1, (int)($r['rating'] ?? 5))),
					'is_enabled' => 1,
					'sort_order' => $count
				]);
				$count++;
			}
			// Mark testimonial source as GMB
			$this->storefront_model->saveSettings($storeId, ['testimonial_source' => 'gmb']);
			echo json_encode(['status' => 'success', 'message' => $count.' Google reviews imported.']);
		} catch(Exception $e){
			echo json_encode(['status' => 'error', 'message' => 'Error: '.$e->getMessage()]);
		}
	}
}
