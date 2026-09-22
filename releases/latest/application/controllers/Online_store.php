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

	/**
	 * Can view online store (dashboard, products, services, settings page).
	 * Requires online_store_view permission or admin/store_admin.
	 */
	private function _can_view(){
		return $this->permissions('online_store_view') || is_admin() || is_store_admin() || $this->session->userdata('role_id') == 1;
	}

	/**
	 * Can view/manage online orders.
	 * Requires online_store_orders OR online_store_view OR online_store_edit OR admin/store_admin.
	 * This lets Managers see orders without giving them full store editing.
	 */
	private function _can_view_orders(){
		return $this->permissions('online_store_orders') || $this->permissions('online_store_view') || $this->permissions('online_store_edit') || is_admin() || is_store_admin() || $this->session->userdata('role_id') == 1;
	}

	/**
	 * Can update order status / payment status.
	 * Requires online_store_orders OR online_store_edit OR admin/store_admin.
	 */
	private function _can_edit_orders(){
		return $this->permissions('online_store_orders') || $this->permissions('online_store_edit') || is_admin() || is_store_admin() || $this->session->userdata('role_id') == 1;
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
		if(!mp_feature_enabled('online_store')){
			$this->show_feature_not_activated('online_store');
			return;
		}
		if(!$this->permissions('online_store_view') && !$this->permissions('online_store_orders') && !$this->permissions('online_store_edit') && !is_admin() && !is_store_admin() && $this->session->userdata('role_id') != 1){
			$this->show_access_denied_page();
			return;
		}
		$this->load->model('storefront_model');
		$this->load->model('paystack_model','paystack');
	}

	// ============== DASHBOARD ==============

	public function index(){
		if(!$this->_can_view()){ $this->show_access_denied_page(); return; }
		$data = array_merge($this->data, [
			'page_title' => 'Online Store Dashboard',
			'stats' => $this->storefront_model->getTodaysOrderStats(),
			'recent_orders' => $this->storefront_model->getOrders(null, null, 10, 0),
			'top_products' => $this->storefront_model->getTopOnlineProducts(null, 5)
		]);
		$data['content'] = $this->load->view('online_store/dashboard', $data, TRUE);
		$this->load->view('mp_layout', $data);
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
		$data['content'] = $this->load->view('online_store/settings', $data, TRUE);
		$this->load->view('mp_layout', $data);
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
				'shipping_notice' => trim($this->input->post('shipping_notice')),
				'shipping_methods_json' => $this->_build_shipping_methods_json(),
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


	private function _build_shipping_methods_json(){
		$names = $this->input->post('sm_name');
		$fees = $this->input->post('sm_fee');
		$descs = $this->input->post('sm_desc');
		$enabled = $this->input->post('sm_enabled');
		$rowids = $this->input->post('sm_rowid');
		if(!is_array($names)) return json_encode([]);
		$methods = [];
		foreach($names as $i => $name){
			$name = trim($name);
			if($name === '') continue;
			$rowKey = (is_array($rowids) && isset($rowids[$i])) ? $rowids[$i] : $i;
			$methods[] = [
				'name' => $name,
				'fee' => (float)($fees[$i] ?? 0),
				'description' => trim($descs[$i] ?? ''),
				'enabled' => (is_array($enabled) && isset($enabled[$rowKey])) ? 1 : 0
			];
		}
		return json_encode($methods);
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
		if(!$this->_can_view_orders()){ $this->show_access_denied_page(); return; }
		$status = $this->input->get('status');
		$data = array_merge($this->data, [
			'page_title' => 'Online Orders',
			'orders' => $this->storefront_model->getOrders(null, $status ?: null, 50, 0),
			'total' => $this->storefront_model->countOrders(null, $status ?: null),
			'current_status' => $status ?: 'all'
		]);
		$data['content'] = $this->load->view('online_store/orders', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function order_detail($orderId = 0){
		if(!$this->_can_view_orders()){ $this->show_access_denied_page(); return; }
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
		$data['content'] = $this->load->view('online_store/order_detail', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function update_order_status(){
		if(!$this->_can_edit_orders()){
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
		// Get current order to check previous status
		$order = $this->storefront_model->getOrder($orderId);
		$previousStatus = $order ? $order->order_status : '';
		$this->storefront_model->updateOrderStatus($orderId, $status);
		// Stock logic: decrement when marked paid, restore when cancelled
		if($status === 'paid' && $previousStatus !== 'paid'){
			$this->storefront_model->adjustStock($orderId);
		} elseif($status === 'cancelled' && $previousStatus !== 'cancelled'){
			$this->storefront_model->restoreStock($orderId);
		}
		echo json_encode(['status' => 'success', 'message' => 'Order status updated']);
	}

	public function update_payment_status(){
		if(!$this->_can_edit_orders()){
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
		// Get current order to check previous payment status
		$order = $this->storefront_model->getOrder($orderId);
		$previousStatus = $order ? $order->payment_status : '';
		$this->storefront_model->updatePaymentStatus($orderId, $status);
		// Stock logic: decrement when marked paid, restore when leaving paid status
		if($status === 'paid' && $previousStatus !== 'paid'){
			$this->storefront_model->adjustStock($orderId);
			// Deliver digital products, courses and memberships when manually marked paid
			$this->storefront_model->deliverDigitalOrder($orderId);
			$this->storefront_model->deliverCourseAndMembership($orderId);
			$this->storefront_model->completeIfNoPhysicalProducts($orderId);
		} elseif($status === 'refunded' && $previousStatus === 'paid'){
			$this->storefront_model->restoreStock($orderId);
		}
		echo json_encode(['status' => 'success', 'message' => 'Payment status updated']);
	}

	// ============== QUICK WHATSAPP ORDER ==============

	public function quick_wa(){
		if(!$this->_can_edit_orders()){ $this->show_access_denied_page(); return; }
		$storeId = get_current_store_id();
		$settings = $this->storefront_model->getSettings($storeId);
		if(empty($settings->allow_whatsapp) || empty($settings->whatsapp_number)){
			$this->session->set_flashdata('error', 'WhatsApp orders are not enabled. Turn on "Allow WhatsApp Orders" and set a WhatsApp number in Online Store > Settings.');
			redirect('online_store/settings');
			return;
		}
		$data = array_merge($this->data, [
			'page_title' => 'Quick WhatsApp Order',
			'products' => $this->storefront_model->getOnlineProducts($storeId, null, '', 500, 0),
			'settings' => $settings
		]);
		$data['content'] = $this->load->view('online_store/quick_wa', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function quick_wa_save(){
		if(!$this->_can_edit_orders()){
			$this->session->set_flashdata('error', 'Access denied');
			redirect('online_store/quick_wa');
			return;
		}

		$storeId = get_current_store_id();
		$settings = $this->storefront_model->getSettings($storeId);
		if(empty($settings->allow_whatsapp) || empty($settings->whatsapp_number)){
			$this->session->set_flashdata('error', 'WhatsApp orders are not enabled. Turn on "Allow WhatsApp Orders" and set a WhatsApp number in Online Store > Settings.');
			redirect('online_store/settings');
			return;
		}

		$productId = (int)$this->input->post('product_id');
		$qty = max(1, (int)$this->input->post('qty'));
		$customerName = trim($this->input->post('customer_name', TRUE));
		$customerPhone = trim($this->input->post('customer_phone', TRUE));
		$customerAddress = trim($this->input->post('customer_address', TRUE));

		if(!$productId){
			$this->session->set_flashdata('error', 'Please select a product.');
			redirect('online_store/quick_wa');
			return;
		}

		$product = $this->storefront_model->getOnlineProduct($productId, $storeId);
		if(!$product){
			$this->session->set_flashdata('error', 'Product not found.');
			redirect('online_store/quick_wa');
			return;
		}

		$settings = $this->storefront_model->getSettings($storeId);
		if((int)$product->stock < $qty && empty($settings->allow_backorder)){
			$this->session->set_flashdata('error', 'Not enough stock.');
			redirect('online_store/quick_wa');
			return;
		}

		$price = (float)$this->storefront_model->getProductEffectivePrice($product);
		$subtotal = round($price * $qty, 2);
		$token = bin2hex(random_bytes(16));

		$orderData = [
			'store_id'           => $storeId,
			'customer_name'      => $customerName ?: 'WhatsApp Customer',
			'customer_email'     => '',
			'customer_phone'     => $customerPhone,
			'customer_address'   => $customerAddress,
			'order_type'         => 'product',
			'payment_method'     => 'whatsapp',
			'shipping_method'    => null,
			'delivery_fee'       => 0,
			'subtotal'           => $subtotal,
			'grand_total'        => $subtotal,
			'order_status'       => 'pending',
			'payment_status'     => 'unpaid',
			'source_channel'     => 'whatsapp',
			'channel_user_id'    => $customerPhone,
			'confirmation_token' => $token,
			'token_expires_at'   => date('Y-m-d H:i:s', strtotime('+15 minutes')),
			'stock_adjusted'     => 0,
		];

		$orderId = $this->storefront_model->createOrder($orderData);
		if(!$orderId){
			$this->session->set_flashdata('error', 'Could not create order.');
			redirect('online_store/quick_wa');
			return;
		}

		$this->storefront_model->addOrderItem([
			'order_id'     => $orderId,
			'item_type'    => 'product',
			'item_id'      => $productId,
			'item_name'    => $product->item_name,
			'item_image'   => $product->item_image,
			'qty'          => $qty,
			'unit_price'   => $price,
			'total_price'  => $subtotal,
			'service_note' => ''
		]);

		$this->session->set_flashdata('success', 'WhatsApp order created. Mark it paid to deduct stock.');
		redirect('online_store/order/' . $orderId);
	}

	// ============== SERVICES ==============

	public function services(){
		if(!$this->_can_view()){ $this->show_access_denied_page(); return; }
		$data = array_merge($this->data, [
			'page_title' => 'Services',
			'services' => $this->storefront_model->getOnlineServices(null, null, '', 100, 0),
			'categories' => $this->db->where('store_id', get_current_store_id())->where('status', 1)->get('db_category')->result()
		]);
		$data['content'] = $this->load->view('online_store/services', $data, TRUE);
		$this->load->view('mp_layout', $data);
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
			'deposit_required' => $this->input->post('deposit_required') ? 1 : 0,
			'deposit_percent' => (float)$this->input->post('deposit_percent'),
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
		if(!mp_feature_enabled('qr_ordering')){
			$this->show_access_denied_page();
			return;
		}
		if(!$this->_can_view()){ $this->show_access_denied_page(); return; }
		$data = array_merge($this->data, [
			'page_title' => 'QR Codes',
			'qr_codes' => $this->storefront_model->getQrCodes(),
			'products' => $this->storefront_model->getOnlineProducts(null, null, '', 100),
			'services' => $this->storefront_model->getOnlineServices(null, null, '', 100),
			'categories' => $this->storefront_model->getCategoriesWithItems()
		]);
		$data['content'] = $this->load->view('online_store/qr_codes', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function generate_qr(){
		if(!mp_feature_enabled('qr_ordering')){
			echo json_encode(['status' => 'error', 'message' => 'Feature disabled']);
			return;
		}
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
		if(!$this->_can_view()){ $this->show_access_denied_page(); return; }
		$search = trim($this->input->get('search'));
		$category_id = (int)$this->input->get('category');

		// Build query with optional category + search filters
		$this->db->select('a.id, a.item_name, a.item_image, a.stock, a.sales_price, a.online_price, a.publish_online, a.is_featured, a.is_new_arrival, a.status, b.category_name');
		$this->db->from('db_items a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.store_id', get_current_store_id());
		$this->db->where('a.service_bit', 0);
		$this->db->where("(a.not_for_sale IS NULL OR a.not_for_sale = 0)", null, false);
		$this->db->where("(a.item_group IS NULL OR a.item_group='Single')");
		if($category_id){
			$this->db->where('a.category_id', $category_id);
		}
		if($search){
			$this->db->group_start();
			$this->db->like('a.item_name', $search);
			$this->db->or_like('a.item_code', $search);
			$this->db->group_end();
		}
		$this->db->order_by('a.id', 'desc');
		$products = $this->db->get()->result();

		// Categories for the filter dropdown (only categories that have products)
		$categories = $this->db->select('id, category_name')
		                        ->where('store_id', get_current_store_id())
		                        ->where('status', 1)
		                        ->order_by('category_name', 'asc')
		                        ->get('db_category')->result();

		$data = array_merge($this->data, [
			'page_title' => 'Online Products',
			'products' => $products,
			'search' => $search,
			'category_id' => $category_id,
			'categories' => $categories,
		]);
		$data['content'] = $this->load->view('online_store/products_online', $data, TRUE);
		$this->load->view('mp_layout', $data);
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
		// Items flagged "not for sale" (raw materials/consumables) can never go online
		if($newVal == 1 && !empty($product->not_for_sale)){
			echo json_encode(['status' => 'error', 'message' => 'This item is flagged Not for Sale and cannot be published online']);
			return;
		}
		// Only enforce the online product limit when turning ON (publishing)
		if($newVal == 1){
			$online_check = check_online_product_limit(1);
			if($online_check !== true){
				echo json_encode(['status' => 'error', 'message' => $online_check]);
				return;
			}
		}
		// When turning OFF manually, mark online_excluded=1 so "Sync All" won't re-publish it.
		// When turning ON manually, clear the exclusion flag.
		$updateData = ['publish_online' => $newVal];
		if($newVal == 0){
			$updateData['online_excluded'] = 1;
		} else {
			$updateData['online_excluded'] = 0;
		}
		$this->db->where('id', $productId)->update('db_items', $updateData);
		echo json_encode(['status' => 'success', 'publish_online' => $newVal]);
	}

	public function save_featured(){
		if(!$this->_can_edit()){
			$this->session->set_flashdata('error', 'Access denied');
			redirect('online_store/products_online');
			return;
		}
		$storeId = get_current_store_id();
		// Save featured flags
		$featured = $this->input->post('featured') ?: [];
		foreach($featured as $productId => $value){
			$productId = (int)$productId;
			$value = (int)$value;
			$this->db->where('id', $productId)->where('store_id', $storeId)->update('db_items', ['is_featured' => $value]);
		}
		// Save new arrival flags
		$newArrivals = $this->input->post('new_arrival') ?: [];
		foreach($newArrivals as $productId => $value){
			$productId = (int)$productId;
			$value = (int)$value;
			$this->db->where('id', $productId)->where('store_id', $storeId)->update('db_items', ['is_new_arrival' => $value]);
		}
		$this->session->set_flashdata('success', 'Featured & New Arrival flags saved');
		redirect('online_store/products_online');
	}

	public function save_new_arrivals(){
		if(!$this->_can_edit()){
			$this->session->set_flashdata('error', 'Access denied');
			redirect('online_store/products_online');
			return;
		}
		$storeId = get_current_store_id();
		$newArrivals = $this->input->post('new_arrival') ?: [];
		foreach($newArrivals as $productId => $value){
			$productId = (int)$productId;
			$value = (int)$value;
			$this->db->where('id', $productId)->where('store_id', $storeId)->update('db_items', ['is_new_arrival' => $value]);
		}
		$this->session->set_flashdata('success', 'New Arrival flags saved');
		redirect('online_store/products_online');
	}

	public function toggle_new_arrival(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$productId = (int)$this->input->post('product_id');
		$storeId = get_current_store_id();
		$product = $this->db->select('is_new_arrival')->where('id', $productId)->where('store_id', $storeId)->get('db_items')->row();
		if(!$product){
			echo json_encode(['status' => 'error', 'message' => 'Product not found']);
			return;
		}
		$newVal = $product->is_new_arrival ? 0 : 1;
		$this->db->where('id', $productId)->where('store_id', $storeId)->update('db_items', ['is_new_arrival' => $newVal]);
		echo json_encode(['status' => 'success', 'is_new_arrival' => (int)$newVal]);
	}

	public function toggle_featured(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$productId = (int)$this->input->post('product_id');
		$storeId = get_current_store_id();
		$product = $this->db->select('is_featured')->where('id', $productId)->where('store_id', $storeId)->get('db_items')->row();
		if(!$product){
			echo json_encode(['status' => 'error', 'message' => 'Product not found']);
			return;
		}
		$newVal = $product->is_featured ? 0 : 1;
		$this->db->where('id', $productId)->where('store_id', $storeId)->update('db_items', ['is_featured' => $newVal]);
		echo json_encode(['status' => 'success', 'is_featured' => (int)$newVal]);
	}

	/**
	 * Sync (batch publish) all eligible products to the online store.
	 * Only publishes Single items that are currently offline (publish_online=0).
	 * Respects the online_product_limit quota — stops before exceeding it.
	 */
	public function sync_all_online(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$storeId = get_current_store_id();
		$categoryId = (int)$this->input->post('category_id');

		// Count currently online products
		$currentlyOnline = get_online_product_usage($storeId);
		// Get the plan limit
		$limit = get_subscription_limit('online_product_limit', $storeId);

		// Find eligible offline products (Single, non-service, active).
		// IMPORTANT: Skip products with online_excluded=1 — those were deliberately
		// turned off by the user and sync must respect that decision.
		$this->db->where('store_id', $storeId);
		$this->db->where('service_bit', 0);
		$this->db->where('status', 1);
		$this->db->where('publish_online', 0);
		$this->db->where('online_excluded', 0);
		$this->db->where('(not_for_sale IS NULL OR not_for_sale = 0)', null, false);
		$this->db->where("(item_group IS NULL OR item_group='Single')", null, false);
		if($categoryId){
			$this->db->where('category_id', $categoryId);
		}
		$this->db->order_by('id', 'asc');
		$offline = $this->db->get('db_items')->result();

		// Also count how many were excluded (for user feedback)
		$this->db->where('store_id', $storeId);
		$this->db->where('service_bit', 0);
		$this->db->where('status', 1);
		$this->db->where('publish_online', 0);
		$this->db->where('online_excluded', 1);
		$this->db->where("(item_group IS NULL OR item_group='Single')", null, false);
		if($categoryId){
			$this->db->where('category_id', $categoryId);
		}
		$excludedCount = $this->db->count_all_results('db_items');

		$published = 0;
		$skipped = 0;
		$ids = [];
		foreach($offline as $item){
			// Enforce quota: stop if publishing this item would exceed the limit
			if($limit > 0 && ($currentlyOnline + $published) >= $limit){
				$skipped = count($offline) - $published;
				break;
			}
			$ids[] = (int)$item->id;
			$published++;
		}

		if(!empty($ids)){
			$this->db->where('store_id', $storeId)->where_in('id', $ids)->update('db_items', ['publish_online' => 1]);
		}

		$msg = $published . ' product' . ($published != 1 ? 's' : '') . ' published to online store';
		if($skipped > 0){
			$msg .= ', ' . $skipped . ' skipped (quota limit: ' . $limit . ')';
		}
		if($excludedCount > 0){
			$msg .= ', ' . $excludedCount . ' excluded (manually turned off)';
		}
		echo json_encode([
			'status' => 'success',
			'message' => $msg,
			'published' => $published,
			'skipped' => $skipped,
			'excluded' => $excludedCount,
			'limit' => $limit,
		]);
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

	/**
	 * Batch update multiple products at once.
	 * Accepts: product_ids[] (array of IDs) + action (string)
	 * Actions: publish, unpublish, mark_new, unmark_new, mark_featured, unmark_featured
	 */
	public function batch_update(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$storeId = get_current_store_id();
		$productIds = $this->input->post('product_ids') ?: [];
		$action = trim($this->input->post('action', TRUE) ?: '');

		if(empty($productIds) || empty($action)){
			echo json_encode(['status' => 'error', 'message' => 'No products or action selected']);
			return;
		}

		// Sanitize IDs
		$ids = [];
		foreach($productIds as $pid){
			$pid = (int)$pid;
			if($pid > 0){ $ids[] = $pid; }
		}
		if(empty($ids)){
			echo json_encode(['status' => 'error', 'message' => 'No valid product IDs']);
			return;
		}

		// For publish: enforce quota — count how many are currently offline in the selection
		if($action === 'publish'){
			$currentlyOnline = get_online_product_usage($storeId);
			$limit = get_subscription_limit('online_product_limit', $storeId);
			if($limit > 0){
				// Count how many of the selected IDs are currently offline
				$offlineInSelection = $this->db->where('store_id', $storeId)
				                               ->where_in('id', $ids)
				                               ->where('publish_online', 0)
				                               ->count_all_results('db_items');
				$slotsNeeded = $offlineInSelection;
				if($currentlyOnline + $slotsNeeded > $limit){
					$available = max(0, $limit - $currentlyOnline);
					echo json_encode([
						'status' => 'error',
						'message' => "Cannot publish all — your plan allows {$limit} online products, you have {$currentlyOnline} online, and {$slotsNeeded} selected are offline. Only {$available} slots available."
					]);
					return;
				}
			}
		}

		// Build update based on action
		$updateData = [];
		$label = '';
		switch($action){
			case 'publish':
				$updateData = ['publish_online' => 1, 'online_excluded' => 0];
				$label = 'published online';
				break;
			case 'unpublish':
				$updateData = ['publish_online' => 0, 'online_excluded' => 1];
				$label = 'unpublished (excluded from sync)';
				break;
			case 'mark_new':
				$updateData = ['is_new_arrival' => 1];
				$label = 'marked as New Arrival';
				break;
			case 'unmark_new':
				$updateData = ['is_new_arrival' => 0];
				$label = 'removed from New Arrivals';
				break;
			case 'mark_featured':
				$updateData = ['is_featured' => 1];
				$label = 'marked as Featured';
				break;
			case 'unmark_featured':
				$updateData = ['is_featured' => 0];
				$label = 'removed from Featured';
				break;
			default:
				echo json_encode(['status' => 'error', 'message' => 'Unknown action: ' . $action]);
				return;
		}

		$this->db->where('store_id', $storeId)->where_in('id', $ids);
		if($action === 'publish'){
			$this->db->where('(not_for_sale IS NULL OR not_for_sale = 0)', null, false);
		}
		$this->db->update('db_items', $updateData);
		$count = $this->db->affected_rows();
		echo json_encode([
			'status' => 'success',
			'message' => $count . ' product' . ($count != 1 ? 's' : '') . ' ' . $label,
			'affected' => $count,
		]);
	}

	// ============== APPEARANCE ==============

	public function appearance(){
		if(!$this->_can_edit()){ echo "You Don't Have Enough Permission for this Operation!"; exit; }
		$storeId = get_current_store_id();
		$settings = $this->storefront_model->getSettings($storeId);
		$profile = mp_get_store_profile($storeId);
		$industryType = $profile['industry_type'] ?? 'general_retail';

		$data = array_merge($this->data, [
			'page_title' => 'Appearance',
			'settings' => $settings,
			'themes' => $this->storefront_model->getThemesByIndustryForStore($industryType, true),
			'current_theme' => $this->storefront_model->getTheme($settings->theme_id),
			'store' => get_store_details($storeId),
			'industry_type' => $industryType
		]);
		$data['content'] = $this->load->view('online_store/appearance', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_appearance(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$storeId = get_current_store_id();

		// Validate theme_id belongs to the store's industry
		$themeId = (int)$this->input->post('theme_id') ?: null;
		if($themeId){
			$profile = mp_get_store_profile($storeId);
			$allowed = $this->storefront_model->getThemesByIndustryForStore($profile['industry_type'] ?? null, true);
			$allowedIds = array_column($allowed, 'id');
			if(!in_array($themeId, $allowedIds)){
				$themeId = $allowedIds[0] ?? null;
			}
		}

		$data = [
			'theme_id' => $themeId,
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
			'marquee_items' => trim($this->input->post('marquee_items') ?: ''),
			'meta_title' => trim($this->input->post('meta_title')),
			'meta_description' => trim($this->input->post('meta_description')),
			'meta_keywords' => trim($this->input->post('meta_keywords')),
			'google_analytics_id' => trim($this->input->post('google_analytics_id')),
			'facebook_pixel_id' => trim($this->input->post('facebook_pixel_id')),
			'robots_index' => (int)$this->input->post('robots_index'),
			'custom_head_scripts' => trim($this->input->post('custom_head_scripts'))
		];

		// Store logo upload → db_storefront_settings.store_logo
		if(!empty($_FILES['store_logo']['name'])){
			$media_check = check_media_storage_limit();
			if($media_check !== true){
				echo json_encode(['status' => 'error', 'message' => $media_check]);
				return;
			}
			$uploadDir = './uploads/storefront/' . $storeId . '/';
			if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
			$config = ['upload_path' => $uploadDir, 'allowed_types' => 'jpg|jpeg|png|gif|webp', 'max_size' => 1024, 'file_name' => 'logo_' . time()];
			$this->load->library('upload');
			$this->upload->initialize($config);
			if($this->upload->do_upload('store_logo')){
				$up = $this->upload->data();
				$data['store_logo'] = 'uploads/storefront/' . $storeId . '/' . $up['file_name'];
			}
		}

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
		$data['content'] = $this->load->view('online_store/banners', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function banner_form($id = 0){
		if(!$this->_can_edit()){ echo "You Don't Have Enough Permission for this Operation!"; exit; }
		$storeId = get_current_store_id();
		$banner = $id ? $this->storefront_model->getBanner($id, $storeId) : null;
		$data = array_merge($this->data, [
			'page_title' => $banner ? 'Edit Banner' : 'Add Banner',
			'banner' => $banner
		]);
		$data['content'] = $this->load->view('online_store/banner_form', $data, TRUE);
		$this->load->view('mp_layout', $data);
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
		$data['content'] = $this->load->view('online_store/homepage_builder', $data, TRUE);
		$this->load->view('mp_layout', $data);
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

	public function save_homepage_section_meta(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$storeId = get_current_store_id();
		$sectionKey = preg_replace('/[^a-z0-9_]/i', '', (string)$this->input->post('section_key'));
		$title    = trim((string)$this->input->post('title'));
		$subtitle = trim((string)$this->input->post('subtitle'));
		if(!$sectionKey){
			echo json_encode(['status' => 'error', 'message' => 'No section key provided']);
			return;
		}
		if($this->storefront_model->saveHomepageSectionMeta($storeId, $sectionKey, $title, $subtitle)){
			echo json_encode(['status' => 'success', 'message' => 'Section updated', 'title' => $title]);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Section not found']);
		}
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

	public function delete_homepage_section(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$storeId = get_current_store_id();
		$sectionKey = preg_replace('/[^a-z0-9_]/i', '', (string)$this->input->post('section_key'));
		if(!$sectionKey){
			echo json_encode(['status' => 'error', 'message' => 'No section key provided']);
			return;
		}
		if($this->storefront_model->deleteHomepageSection($storeId, $sectionKey)){
			echo json_encode(['status' => 'success', 'message' => 'Section removed']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Only duplicated sections can be removed']);
		}
	}

	// ============== ANALYTICS ==============

	public function analytics(){
		if(!$this->_can_view()){ $this->show_access_denied_page(); return; }
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
		$data['content'] = $this->load->view('online_store/analytics', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function export_analytics(){
		if(!$this->_can_view()){ $this->show_access_denied_page(); return; }
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
		$data['content'] = $this->load->view('online_store/domains', $data, TRUE);
		$this->load->view('mp_layout', $data);
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
		if(!in_array($type, ['subdomain', 'custom'])) $type = 'custom';
		$value = $this->_normalize_domain($this->input->post('domain_value'));
		if(!$value){
			echo json_encode(['status' => 'error', 'message' => 'Enter a valid domain (e.g. shop.yourstore.com)']);
			return;
		}
		$platformHost = $this->_normalize_domain($this->input->server('HTTP_HOST'));
		if($value === $platformHost){
			echo json_encode(['status' => 'error', 'message' => 'You cannot use the platform\'s own domain.']);
			return;
		}
		// Prevent another store from claiming a domain that is already registered
		$existing = $this->db->where('domain_value', $value)->get('db_storefront_domains')->row();
		if($existing && (int)$existing->id !== $domainId){
			echo json_encode(['status' => 'error', 'message' => 'This domain is already registered to another store.']);
			return;
		}
		$instructions = trim((string)$this->input->post('dns_instructions'));
		if($instructions === ''){
			$instructions = $this->_domain_dns_instructions($value);
		}
		$data = [
			'domain_type' => $type,
			'domain_value' => $value,
			'dns_instructions' => $instructions
		];
		if($domainId){
			$this->storefront_model->saveDomain($data, $domainId);
		} else {
			$data['store_id'] = $storeId;
			$data['verification_status'] = 'pending';
			$data['connection_status'] = 'pending';
			$this->storefront_model->saveDomain($data);
		}
		echo json_encode(['status' => 'success', 'message' => 'Domain saved. Follow the DNS instructions, then click "Verify & Connect".']);
	}

	/**
	 * Normalize a domain value: lowercase, no scheme, path, port or trailing dot.
	 */
	private function _normalize_domain($value){
		$value = strtolower(trim((string)$value));
		$value = preg_replace('#^https?://#', '', $value);
		$value = preg_replace('#/.*$#', '', $value);
		$value = rtrim(preg_replace('/:\d+$/', '', $value), '.');
		if($value === '' || strpos($value, '.') === false) return '';
		if(!preg_match('/^[a-z0-9]([a-z0-9\-\.]*[a-z0-9])?$/', $value)) return '';
		return $value;
	}

	/**
	 * Auto-generated DNS instructions for a domain.
	 */
	private function _domain_dns_instructions($domain){
		$platformHost = $this->_normalize_domain($this->input->server('HTTP_HOST'));
		$serverIp = @gethostbyname($platformHost);
		$lines = [
			"Option A (recommended): create a CNAME record for {$domain} pointing to {$platformHost}.",
		];
		if($serverIp && $serverIp !== $platformHost){
			$lines[] = "Option B: create an A record for {$domain} pointing to {$serverIp}.";
		}
		$lines[] = "Then return to Online Store > Domains and click \"Verify & Connect\". DNS changes can take up to 24-48 hours (usually a few minutes).";
		return implode("\n", $lines);
	}

	/**
	 * Verify that a domain's DNS actually points to this server, then connect it.
	 */
	public function verify_domain(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$storeId = get_current_store_id();
		$domainId = (int)$this->input->post('domain_id');
		$domain = $this->storefront_model->getDomain($domainId, $storeId);
		if(!$domain){
			echo json_encode(['status' => 'error', 'message' => 'Domain not found']);
			return;
		}
		$target = $domain->domain_value;
		$platformHost = $this->_normalize_domain($this->input->server('HTTP_HOST'));
		$serverIps = array_unique(array_filter([
			@gethostbyname($platformHost),
			isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : ''
		]));

		$ok = false;
		$found = [];

		$cnames = @dns_get_record($target, DNS_CNAME);
		if(is_array($cnames)) foreach($cnames as $r){
			if(empty($r['target'])) continue;
			$t = rtrim(strtolower($r['target']), '.');
			$found[] = 'CNAME → ' . $t;
			if($t === $platformHost || substr($t, -strlen('.'.$platformHost)) === '.'.$platformHost) $ok = true;
		}

		$ares = @dns_get_record($target, DNS_A);
		if(is_array($ares)) foreach($ares as $r){
			if(empty($r['ip'])) continue;
			$found[] = 'A → ' . $r['ip'];
			if(in_array($r['ip'], $serverIps)) $ok = true;
		}
		if(empty($found)){
			$ip = @gethostbyname($target);
			if($ip && $ip !== $target){
				$found[] = 'A → ' . $ip;
				if(in_array($ip, $serverIps)) $ok = true;
			}
		}

		// Proxy/CDN fallback (e.g. Cloudflare): DNS points at the proxy, not us.
		// If the domain already serves this app's robots.txt, it is routed here.
		if(!$ok && !empty($found)){
			$ctx = stream_context_create(['http' => ['timeout' => 6, 'ignore_errors' => true, 'follow_location' => 0]]);
			$body = @file_get_contents('http://' . $target . '/robots.txt', false, $ctx);
			if(is_string($body) && strpos($body, 'Disallow: /online_store/') !== false){
				$ok = true;
				$found[] = 'routed via proxy';
			}
		}

		// Probe HTTPS (port 443) — SSL becomes usable once the host issues a cert
		$ssl = 'pending';
		$fp = @fsockopen('ssl://' . $target, 443, $errno, $errstr, 5);
		if($fp){ $ssl = 'active'; fclose($fp); }

		$update = ['ssl_status' => $ssl];
		if($ok){
			$update['verification_status'] = 'verified';
			$update['connection_status'] = 'connected';
			$update['verified_at'] = date('Y-m-d H:i:s');
			$this->db->where('id', $domainId)->update('db_storefront_domains', $update);
			$msg = 'Domain verified and connected — https://' . $target . ' now serves your store.';
			if($ssl !== 'active'){
				$msg .= ' Note: HTTPS is not active yet — ask your hosting provider to add this domain (parked/alias) and issue an SSL certificate (e.g. cPanel AutoSSL).';
			}
			echo json_encode(['status' => 'success', 'message' => $msg]);
		} else {
			$update['verification_status'] = 'failed';
			$this->db->where('id', $domainId)->update('db_storefront_domains', $update);
			$msg = 'DNS is not pointing to this server yet.';
			$msg .= $found ? ' Found: ' . implode(', ', $found) . '.' : ' No DNS records found for ' . $target . '.';
			$msg .= ' Expected: CNAME → ' . $platformHost . ($serverIps ? ' or A → ' . implode(' / ', $serverIps) : '') . '.';
			echo json_encode(['status' => 'error', 'message' => $msg]);
		}
	}

	/**
	 * Email the DNS setup instructions for a domain to a team member.
	 */
	public function send_domain_instructions(){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']);
			return;
		}
		$storeId = get_current_store_id();
		$domainId = (int)$this->input->post('domain_id');
		$domain = $this->storefront_model->getDomain($domainId, $storeId);
		if(!$domain){
			echo json_encode(['status' => 'error', 'message' => 'Domain not found']);
			return;
		}
		$email = trim((string)$this->input->post('email'));
		if(!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)){
			echo json_encode(['status' => 'error', 'message' => 'Enter a valid recipient email']);
			return;
		}
		$name = trim((string)$this->input->post('name'));

		$store = get_store_details($storeId);
		$settings = $this->storefront_model->getSettings($storeId);
		$storeName = $store->store_name ?? 'your store';
		$platformHost = $this->_normalize_domain($this->input->server('HTTP_HOST'));
		$serverIp = @gethostbyname($platformHost);
		$freeUrl = base_url('store/' . ($settings->store_slug ?? ''));

		$steps = [
			"Log in to the DNS / domain management panel for <strong>" . htmlspecialchars($domain->domain_value) . "</strong> (e.g. Cloudflare, GoDaddy, Namecheap, cPanel DNS Zone Editor).",
			"Create a <strong>CNAME</strong> record: <code>" . htmlspecialchars($domain->domain_value) . " → " . htmlspecialchars($platformHost) . "</code>"
				. ($serverIp && $serverIp !== $platformHost ? " &nbsp;<em>(or an <strong>A</strong> record pointing to " . htmlspecialchars($serverIp) . ")</em>" : ""),
			"If you also want <code>www." . htmlspecialchars($domain->domain_value) . "</code> to work, add the same record for the <code>www</code> host.",
			"Wait for DNS propagation — usually a few minutes, up to 24-48 hours.",
			"Tell the store admin to open <strong>Online Store → Domains</strong> in MartPoint and click <strong>Verify &amp; Connect</strong>.",
			"For HTTPS: the domain must also be added on the hosting account that runs MartPoint (e.g. cPanel → Parked/Alias domain) so an SSL certificate can be issued (AutoSSL). Until then the store may load on http:// only."
		];
		$stepsText = [
			"1. Log in to the DNS / domain management panel for {$domain->domain_value} (e.g. Cloudflare, GoDaddy, Namecheap, cPanel DNS Zone Editor).",
			"2. Create a CNAME record: {$domain->domain_value} -> {$platformHost}" . ($serverIp && $serverIp !== $platformHost ? " (or an A record pointing to {$serverIp})" : ""),
			"3. If you also want www.{$domain->domain_value} to work, add the same record for the www host.",
			"4. Wait for DNS propagation — usually a few minutes, up to 24-48 hours.",
			"5. Tell the store admin to open Online Store > Domains in MartPoint and click \"Verify & Connect\".",
			"6. For HTTPS: the domain must also be added on the hosting account that runs MartPoint (e.g. cPanel > Parked/Alias domain) so an SSL certificate can be issued (AutoSSL). Until then the store may load on http:// only."
		];

		$subject = 'DNS setup instructions for ' . $domain->domain_value . ' — ' . $storeName;
		$html = '<div style="font-family:Inter,system-ui,sans-serif;max-width:640px;margin:0 auto;padding:32px;color:#0F172A;">';
		$html .= '<h2 style="margin-top:0;">Connect ' . htmlspecialchars($domain->domain_value) . ' to ' . htmlspecialchars($storeName) . '</h2>';
		if($name) $html .= '<p>Hi ' . htmlspecialchars($name) . ',</p>';
		$html .= '<p>Please point the domain below to our online store. Here are the steps:</p>';
		$html .= '<ol style="line-height:1.9;padding-left:20px;">';
		foreach($steps as $s){ $html .= '<li>' . $s . '</li>'; }
		$html .= '</ol>';
		$html .= '<div style="background:#F1F5F9;border:1px solid #E2E8F0;border-radius:10px;padding:14px 18px;font-size:14px;">';
		$html .= '<strong>Platform host:</strong> ' . htmlspecialchars($platformHost) . '<br>';
		if($serverIp && $serverIp !== $platformHost) $html .= '<strong>Server IP:</strong> ' . htmlspecialchars($serverIp) . '<br>';
		$html .= '<strong>Current store URL:</strong> <a href="' . $freeUrl . '">' . $freeUrl . '</a>';
		$html .= '</div>';
		$html .= '<p style="margin-top:24px;font-size:13px;color:#64748B;">Sent from ' . htmlspecialchars($storeName) . ' via MartPoint.</p>';
		$html .= '</div>';
		$text = "Connect {$domain->domain_value} to {$storeName}\n\n" . implode("\n", $stepsText)
			. "\n\nPlatform host: {$platformHost}"
			. ($serverIp && $serverIp !== $platformHost ? "\nServer IP: {$serverIp}" : '')
			. "\nCurrent store URL: {$freeUrl}\n";

		try {
			$this->load->model('email_service');
			$this->email_service->setStoreId($storeId);
			$res = $this->email_service->sendRaw($email, $subject, $html, $text, [
				'template_key' => 'domain_instructions',
				'related_module' => 'storefront',
				'related_record_id' => $domainId
			]);
			if($res['success']){
				echo json_encode(['status' => 'success', 'message' => 'Instructions sent to ' . $email]);
			} else {
				echo json_encode(['status' => 'error', 'message' => 'Could not send email: ' . $res['message'] . ' Configure one under Email Settings.']);
			}
		} catch (Throwable $e) {
			echo json_encode(['status' => 'error', 'message' => 'Email failed: ' . $e->getMessage()]);
		}
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
		if(!$this->_can_edit()){ $this->show_access_denied_page(); exit; }
		$storeId = get_current_store_id();
		$data = array_merge($this->data, [
			'page_title' => 'Storefront Brands',
			'brands' => $this->storefront_model->getStorefrontBrands($storeId, false)
		]);
		$data['content'] = $this->load->view('online_store/brands', $data, TRUE);
		$this->load->view('mp_layout', $data);
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
		if(!$this->_can_edit()){ $this->show_access_denied_page(); exit; }
		$storeId = get_current_store_id();
		$data = array_merge($this->data, [
			'page_title' => 'Storefront Testimonials',
			'testimonials' => $this->storefront_model->getStorefrontTestimonials($storeId, false)
		]);
		$data['content'] = $this->load->view('online_store/testimonials', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_testimonial(){
		header('Content-Type: application/json');
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
		if(!$this->_can_edit()){ $this->show_access_denied_page(); exit; }
		$storeId = get_current_store_id();
		$data = array_merge($this->data, [
			'page_title' => 'Instagram Gallery',
			'posts' => $this->storefront_model->getStorefrontInstagram($storeId, false)
		]);
		$data['content'] = $this->load->view('online_store/instagram', $data, TRUE);
		$this->load->view('mp_layout', $data);
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
		if(!$this->_can_edit()){ $this->show_access_denied_page(); exit; }
		$storeId = get_current_store_id();
		$data = array_merge($this->data, [
			'page_title' => 'Storefront FAQs',
			'faqs' => $this->storefront_model->getStorefrontFaqs($storeId, false)
		]);
		$data['content'] = $this->load->view('online_store/faqs', $data, TRUE);
		$this->load->view('mp_layout', $data);
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

	// ============== NEWSLETTER SUBSCRIBERS ==============

	public function subscribers(){
		if(!$this->_can_view()){ $this->show_access_denied_page(); exit; }
		$storeId = get_current_store_id();
		$search = trim($this->input->get('search', TRUE) ?: '');
		$data = array_merge($this->data, [
			'page_title' => 'Newsletter Subscribers',
			'subscribers' => $this->storefront_model->getNewsletterSubscribers($storeId, $search),
			'total_subscribers' => $this->storefront_model->countNewsletterSubscribers($storeId),
			'search' => $search
		]);
		$data['content'] = $this->load->view('online_store/subscribers', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function export_subscribers(){
		if(!$this->_can_view()){ $this->show_access_denied_page(); return; }
		$storeId = get_current_store_id();
		$rows = $this->storefront_model->getNewsletterSubscribers($storeId);
		$store = get_store_details($storeId);
		$filename = 'newsletter-subscribers-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower($store->store_name ?? 'store')) . '-' . date('Ymd') . '.csv';

		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		$out = fopen('php://output', 'w');
		fputcsv($out, ['Email', 'Source', 'Subscribed At', 'IP Address']);
		foreach($rows as $r){
			fputcsv($out, [$r->email, $r->source, $r->created_at, $r->ip_address]);
		}
		fclose($out);
		exit;
	}

	public function delete_subscriber($id = 0){
		if(!$this->_can_edit()){
			echo json_encode(['status' => 'error', 'message' => 'Access denied']); return;
		}
		try{
			$this->storefront_model->deleteNewsletterSubscriber($id, get_current_store_id());
			echo json_encode(['status' => 'success', 'message' => 'Subscriber removed']);
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
