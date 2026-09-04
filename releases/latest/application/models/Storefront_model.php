<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Storefront Model
 * Manages online storefront settings, services, online orders, and QR codes.
 */
class Storefront_model extends CI_Model {

	private static $themesSeeded = false;

	public function __construct(){
		parent::__construct();
		$this->ensureTables();
	}

	/**
	 * Returns a WHERE clause fragment to exclude expired items from online store.
	 * Used by all public-facing product queries.
	 */
	private function _expiredWhere($tableAlias = 'a', $storeId = null){
		// Always exclude expired items from the online storefront.
		// If you need to allow expired items online, manually unpublish them.
		return "($tableAlias.expire_date IS NULL OR $tableAlias.expire_date NOT LIKE '0000%' OR $tableAlias.expire_date >= '".date('Y-m-d')."')";
	}

	/**
	 * Verify storefront tables exist; create optional portal/Sendchamp tables when missing.
	 */
	private function ensureTables(){
		$tables = [
			'db_storefront_settings', 'db_services', 'db_online_orders', 'db_online_order_items',
			'db_storefront_themes', 'db_storefront_banners', 'db_storefront_homepage_sections',
			'db_storefront_domains', 'db_qr_codes', 'db_storefront_brands', 'db_storefront_testimonials',
			'db_storefront_instagram', 'db_storefront_faqs', 'db_storefront_analytics'
		];
		foreach($tables as $table){
			if(!$this->db->table_exists($table)){
				log_message('error', 'Missing required table: ' . $table . '. Run the 4.0.2 migration via login.');
			}
		}

		try {
			// Customer portal schema additions
			if($this->db->table_exists('db_online_orders') && !$this->db->field_exists('customer_id', 'db_online_orders')){
				$this->db->query("ALTER TABLE db_online_orders ADD customer_id INT NULL DEFAULT NULL");
			}

			if(!$this->db->table_exists('db_storefront_customer_otp')){
				$this->db->query("CREATE TABLE IF NOT EXISTS db_storefront_customer_otp (
					id INT(11) AUTO_INCREMENT PRIMARY KEY,
					store_id INT(11) NOT NULL,
					customer_id INT(11) NULL,
					phone VARCHAR(20) NULL,
					email VARCHAR(120) NULL,
					otp VARCHAR(6) NOT NULL,
					verified TINYINT(1) DEFAULT 0,
					attempts INT(11) DEFAULT 0,
					expires_at DATETIME NOT NULL,
					created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
			}

			if($this->db->table_exists('db_storefront_customer_otp') && !$this->db->field_exists('email', 'db_storefront_customer_otp')){
				$this->db->query("ALTER TABLE db_storefront_customer_otp ADD email VARCHAR(120) NULL AFTER phone");
			}

			if(!$this->db->table_exists('db_storefront_customer_sessions')){
				$this->db->query("CREATE TABLE IF NOT EXISTS db_storefront_customer_sessions (
					id INT(11) AUTO_INCREMENT PRIMARY KEY,
					store_id INT(11) NOT NULL,
					customer_id INT(11) NOT NULL,
					phone VARCHAR(20) NULL,
					email VARCHAR(120) NULL,
					session_token VARCHAR(64) NOT NULL,
					expires_at DATETIME NOT NULL,
					last_used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
			}

			if($this->db->table_exists('db_storefront_customer_sessions') && !$this->db->field_exists('email', 'db_storefront_customer_sessions')){
				$this->db->query("ALTER TABLE db_storefront_customer_sessions ADD email VARCHAR(120) NULL AFTER phone");
			}

			if(!$this->db->table_exists('db_sendchamp')){
				$this->db->query("CREATE TABLE IF NOT EXISTS db_sendchamp (
					id INT(11) AUTO_INCREMENT PRIMARY KEY,
					store_id INT(11) NOT NULL,
					api_key TEXT NOT NULL,
					sender_id VARCHAR(50) NOT NULL DEFAULT 'MartPoint',
					route VARCHAR(50) NOT NULL DEFAULT 'non_dnd_nigeria',
					created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
			}

			if($this->db->table_exists('db_storefront_settings') && !$this->db->field_exists('sendchamp_json', 'db_storefront_settings')){
				$this->db->query("ALTER TABLE db_storefront_settings ADD sendchamp_json TEXT NULL DEFAULT NULL");
			}
		} catch (Exception $e) {
			log_message('error', 'Storefront ensureTables optional migration failed: ' . $e->getMessage());
		}
	}

	// ============== STOREFRONT SETTINGS ==============

	public function getSettings($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$row = $this->db->where('store_id', $storeId)->get('db_storefront_settings')->row();
		if(!$row){
			// Return default settings
			$store = get_store_details($storeId);
			return (object)[
				'store_id' => $storeId,
				'store_slug' => $store ? strtolower(str_replace(' ','-', $store->store_name)) : 'store',
				'store_description' => '',
				'store_banner' => '',
				'store_logo' => '',
				'whatsapp_number' => '',
				'store_email' => $store ? $store->email : '',
				'store_phone' => $store ? $store->mobile : '',
				'store_address' => $store ? $store->address : '',
				'default_branch_id' => 0,
				'store_status' => 'active',
				'allow_paystack' => 1,
				'allow_whatsapp' => 1,
				'allow_pay_on_delivery' => 1,
			'shipping_notice' => '',
			'shipping_methods_json' => '',
				'allow_services' => 1,
				'allow_backorder' => 0,
				'show_search' => 1,
				'show_categories' => 1,
				'show_whatsapp_cta' => 1,
				'featured_products_limit' => 8,
			'theme_id' => null,
			'primary_color' => '#3B82F6',
			'secondary_color' => '#10B981',
			'font_family' => 'Inter',
			'button_style' => 'rounded',
			'store_headline' => '',
			'store_subheadline' => '',
			'favicon' => '',
			'desktop_banner' => '',
			'mobile_banner' => '',
			'instagram_url' => '',
			'facebook_url' => '',
			'tiktok_url' => '',
			'x_url' => '',
			'youtube_url' => '',
			'business_hours' => '',
			'announcement_bar' => '',
			'announcement_bar_color' => '#0F172A',
			'preview_mode' => 0,
			'instagram_access_token' => '',
			'instagram_username' => '',
			'google_places_api_key' => '',
			'gmb_place_id' => '',
			'testimonial_source' => 'custom',
			'trust_badges_json' => '',
			'newsletter_title' => 'Stay in the Loop',
			'newsletter_subtitle' => 'Subscribe for updates, deals and new arrivals.',
			'preview_theme_id' => null,
			'meta_title' => '',
			'meta_description' => '',
			'footer_bg_color' => '#0F172A',
			'header_text_color' => '',
			'button_color' => '#3B82F6',
			'footer_style' => 'standard',
			'footer_about_us' => '',
			'footer_text_color' => '#94A3B8',
			'footer_address_url' => '',
			'meta_keywords' => '',
			'google_analytics_id' => '',
			'facebook_pixel_id' => '',
			'robots_index' => 1,
			'custom_head_scripts' => ''
			];
		}
		// Ensure a store slug is always set so public links never generate double slashes
		if(empty($row->store_slug)){
			$store = get_store_details($storeId);
			$expectedSlug = $store ? strtolower(preg_replace('/[^a-z0-9-]/', '-', $store->store_name)) : 'store';
			$expectedSlug = trim($expectedSlug, '-');
			$row->store_slug = !empty($expectedSlug) ? $expectedSlug : 'store';
			$this->saveSettings($storeId, ['store_slug' => $row->store_slug]);
		}
		return $row;
	}

	public function saveSettings($storeId, $data){
		$storeId = $storeId ?: get_current_store_id();
		$exists = $this->db->where('store_id', $storeId)->get('db_storefront_settings')->num_rows() > 0;
		if($exists){
			return $this->db->where('store_id', $storeId)->update('db_storefront_settings', $data);
		}
		$data['store_id'] = $storeId;
		return $this->db->insert('db_storefront_settings', $data);
	}

	public function getStoreBySlug($slug){
		// 1. Exact match
		$row = $this->db->where('store_slug', $slug)->get('db_storefront_settings')->row();
		if(!$row){
			// 2. Case-insensitive match
			$row = $this->db->where('LOWER(store_slug)', strtolower($slug))->get('db_storefront_settings')->row();
		}
		if(!$row && $slug){
			// 3. Try to find store by matching store name slug and create settings
			$stores = $this->db->get('db_store')->result();
			foreach($stores as $s){
				$expectedSlug = strtolower(preg_replace('/[^a-z0-9-]/', '-', $s->store_name));
				$expectedSlug = trim($expectedSlug, '-');
				if($expectedSlug === $slug || $s->id == (int)$slug){
					$defaults = [
						'store_id' => $s->id,
						'store_slug' => $slug,
						'store_status' => 'active',
						'allow_paystack' => 1,
						'allow_whatsapp' => 1,
						'allow_pay_on_delivery' => 1,
						'allow_services' => 1,
						'allow_backorder' => 0,
						'show_search' => 1,
						'show_categories' => 1,
						'show_whatsapp_cta' => 1,
						'featured_products_limit' => 8,
						'theme_id' => null,
						'primary_color' => '#3B82F6',
						'secondary_color' => '#10B981',
						'font_family' => 'Inter',
						'button_style' => 'rounded',
						'store_headline' => '',
						'store_subheadline' => '',
						'instagram_url' => '',
						'facebook_url' => '',
						'tiktok_url' => '',
						'x_url' => '',
						'youtube_url' => '',
						'business_hours' => '',
						'announcement_bar' => '',
						'announcement_bar_color' => '#0F172A',
						'preview_mode' => 0,
						'preview_theme_id' => null,
						'meta_title' => '',
						'meta_description' => '',
						'footer_bg_color' => '#0F172A',
						'header_text_color' => '',
						'button_color' => '#3B82F6',
						'footer_style' => 'standard',
						'footer_about_us' => '',
						'footer_text_color' => '#94A3B8',
						'footer_address_url' => '',
						'meta_keywords' => '',
						'google_analytics_id' => '',
						'facebook_pixel_id' => '',
						'robots_index' => 1,
						'custom_head_scripts' => ''
					];
					$this->db->insert('db_storefront_settings', $defaults);
					return (object)$defaults;
				}
			}
		}
		if(!$row && (!$slug || $slug === '')){
			// 4. If slug is empty, return first active storefront settings
			$row = $this->db->where('store_status', 'active')->order_by('id', 'asc')->get('db_storefront_settings')->row();
		}
		if(!$row){
			// 5. Last resort: return settings for current store
			$row = $this->getSettings();
		}
		return $row;
	}

	// ============== PRODUCTS ==============

	public function getOnlineProducts($storeId = null, $categoryId = null, $search = '', $limit = 50, $offset = 0){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.id, a.item_name, a.item_image, a.item_code, a.description, a.stock, a.alert_qty, a.sales_price, a.online_price, a.discount_type, a.discount, a.status, b.category_name');
		$this->db->from('db_items a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.publish_online', 1);
		$this->db->where('a.status', 1);
		$this->db->where('a.service_bit', 0);
		$this->db->where("(a.item_group IS NULL OR a.item_group='Single')");
		$this->db->where($this->_expiredWhere('a', $storeId), NULL, FALSE);
		if($categoryId){
			$this->db->where('a.category_id', $categoryId);
		}
		if($search){
			$this->db->group_start();
			$this->db->like('a.item_name', $search);
			$this->db->or_like('a.item_code', $search);
			$this->db->or_like('b.category_name', $search);
			$this->db->group_end();
		}
		$this->db->order_by('a.id', 'desc');
		$this->db->limit($limit, $offset);
		return $this->db->get()->result();
	}

	public function getFeaturedProducts($storeId = null, $limit = 8){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.id, a.item_name, a.item_image, a.item_code, a.description, a.stock, a.alert_qty, a.sales_price, a.online_price, a.discount_type, a.discount, a.status, b.category_name');
		$this->db->from('db_items a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.is_featured', 1);
		$this->db->where('a.publish_online', 1);
		$this->db->where('a.status', 1);
		$this->db->where('a.service_bit', 0);
		$this->db->where("(a.item_group IS NULL OR a.item_group='Single')");
		$this->db->where($this->_expiredWhere('a', $storeId), NULL, FALSE);
		$this->db->order_by('a.id', 'desc');
		$this->db->limit($limit);
		return $this->db->get()->result();
	}

	public function getOnlineProduct($productId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.*, b.category_name');
		$this->db->from('db_items a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.id', $productId);
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.publish_online', 1);
		$this->db->where('a.status', 1);
		$this->db->where("(a.item_group IS NULL OR a.item_group='Single')");
		$this->db->where($this->_expiredWhere('a', $storeId), NULL, FALSE);
		return $this->db->get()->row();
	}

	public function getProductVariants($productId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.*, b.category_name');
		$this->db->from('db_items a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.parent_id', $productId);
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.publish_online', 1);
		$this->db->where('a.status', 1);
		$this->db->where('a.service_bit', 0);
		$this->db->where($this->_expiredWhere('a', $storeId), NULL, FALSE);
		$this->db->order_by('a.id', 'asc');
		return $this->db->get()->result();
	}

	public function countOnlineProducts($storeId = null, $categoryId = null, $search = ''){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->from('db_items a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.publish_online', 1);
		$this->db->where('a.status', 1);
		$this->db->where('a.service_bit', 0);
		$this->db->where("(a.item_group IS NULL OR a.item_group='Single')");
		$this->db->where($this->_expiredWhere('a', $storeId), NULL, FALSE);
		if($categoryId){
			$this->db->where('a.category_id', $categoryId);
		}
		if($search){
			$this->db->group_start();
			$this->db->like('a.item_name', $search);
			$this->db->or_like('a.item_code', $search);
			$this->db->or_like('b.category_name', $search);
			$this->db->group_end();
		}
		return $this->db->count_all_results();
	}

	// ============== SERVICES ==============

	public function getOnlineServices($storeId = null, $categoryId = null, $search = '', $limit = 50, $offset = 0){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.*, b.category_name');
		$this->db->from('db_services a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.available_online', 1);
		$this->db->where('a.status', 1);
		if($categoryId){
			$this->db->where('a.category_id', $categoryId);
		}
		if($search){
			$this->db->group_start();
			$this->db->like('a.service_name', $search);
			$this->db->or_like('b.category_name', $search);
			$this->db->group_end();
		}
		$this->db->order_by('a.sort_order', 'asc');
		$this->db->limit($limit, $offset);
		return $this->db->get()->result();
	}

	public function getOnlineService($serviceId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.*, b.category_name');
		$this->db->from('db_services a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.id', $serviceId);
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.available_online', 1);
		return $this->db->get()->row();
	}

	public function countOnlineServices($storeId = null, $categoryId = null, $search = ''){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->from('db_services a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.available_online', 1);
		$this->db->where('a.status', 1);
		if($categoryId){
			$this->db->where('a.category_id', $categoryId);
		}
		if($search){
			$this->db->group_start();
			$this->db->like('a.service_name', $search);
			$this->db->or_like('b.category_name', $search);
			$this->db->group_end();
		}
		return $this->db->count_all_results();
	}

	// ============== CATEGORIES ==============

	public function getCategoriesWithItems($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.id, a.category_name, a.category_image');
		$this->db->from('db_category a');
		$this->db->join('db_items b', "b.category_id=a.id AND b.publish_online=1 AND b.status=1 AND b.service_bit=0 AND (b.item_group IS NULL OR b.item_group='Single')", 'inner');
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.status', 1);
		$this->db->where($this->_expiredWhere('b', $storeId), NULL, FALSE);
		$this->db->group_by('a.id');
		return $this->db->get()->result();
	}

	public function getServiceCategories($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.id, a.category_name, a.category_image');
		$this->db->from('db_category a');
		$this->db->join('db_services b', 'b.category_id=a.id AND b.available_online=1 AND b.status=1', 'inner');
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.status', 1);
		$this->db->group_by('a.id');
		$this->db->order_by('a.category_name', 'asc');
		return $this->db->get()->result();
	}

	// ============== ONLINE ORDERS ==============

	public function createOrder($data){
		$data['order_code'] = $this->generateOrderCode();
		$this->db->insert('db_online_orders', $data);
		return $this->db->insert_id();
	}

	public function addOrderItem($data){
		return $this->db->insert('db_online_order_items', $data);
	}

	public function getOrder($orderId){
		return $this->db->where('id', $orderId)->get('db_online_orders')->row();
	}

	public function getOrderByReference($ref){
		return $this->db->where('paystack_reference', $ref)->get('db_online_orders')->row();
	}

	public function getOrderByCode($orderCode, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('order_code', $orderCode)->where('store_id', $storeId)->get('db_online_orders')->row();
	}

	public function getOrderItems($orderId){
		return $this->db->where('order_id', $orderId)->get('db_online_order_items')->result();
	}

	public function updateOrderStatus($orderId, $status){
		return $this->db->where('id', $orderId)->update('db_online_orders', ['order_status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
	}

	public function updatePaymentStatus($orderId, $status, $data = []){
		$data['payment_status'] = $status;
		$data['updated_at'] = date('Y-m-d H:i:s');
		return $this->db->where('id', $orderId)->update('db_online_orders', $data);
	}

	/**
	 * Decrement stock for all product items in an order.
	 * Only runs once per order (guarded by stock_adjusted flag).
	 * Services are skipped (they don't have stock).
	 */
	public function adjustStock($orderId){
		$order = $this->getOrder($orderId);
		if(!$order || $order->stock_adjusted){
			return false; // already adjusted or not found
		}
		$items = $this->getOrderItems($orderId);
		foreach($items as $item){
			if($item->item_type !== 'product') continue; // skip services
			$qty = (int)$item->qty;
			if($qty <= 0) continue;
			$this->db->set('stock', 'stock - ' . $qty, false);
			$this->db->where('id', $item->item_id);
			$this->db->update('db_items');
		}
		$this->db->where('id', $orderId)->update('db_online_orders', ['stock_adjusted' => 1]);
		return true;
	}

	/**
	 * Restore stock for all product items in an order.
	 * Only runs if stock was previously adjusted (stock_adjusted = 1).
	 */
	public function restoreStock($orderId){
		$order = $this->getOrder($orderId);
		if(!$order || !$order->stock_adjusted){
			return false; // nothing to restore
		}
		$items = $this->getOrderItems($orderId);
		foreach($items as $item){
			if($item->item_type !== 'product') continue;
			$qty = (int)$item->qty;
			if($qty <= 0) continue;
			$this->db->set('stock', 'stock + ' . $qty, false);
			$this->db->where('id', $item->item_id);
			$this->db->update('db_items');
		}
		$this->db->where('id', $orderId)->update('db_online_orders', ['stock_adjusted' => 0]);
		return true;
	}

	public function getOrders($storeId = null, $status = null, $limit = 50, $offset = 0){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->where('store_id', $storeId);
		if($status){
			$this->db->where('order_status', $status);
		}
		$this->db->order_by('id', 'desc');
		$this->db->limit($limit, $offset);
		return $this->db->get('db_online_orders')->result();
	}

	public function countOrders($storeId = null, $status = null){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->where('store_id', $storeId);
		if($status){
			$this->db->where('order_status', $status);
		}
		return $this->db->count_all_results('db_online_orders');
	}

	public function getTodaysOrderStats($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$today = date('Y-m-d');
		$stats = [
			'total_orders' => 0,
			'total_revenue' => 0,
			'pending_orders' => 0,
			'paid_orders' => 0
		];

		$q = $this->db->query("SELECT 
			COUNT(*) as total_orders,
			COALESCE(SUM(grand_total),0) as total_revenue,
			SUM(CASE WHEN order_status='pending' THEN 1 ELSE 0 END) as pending_orders,
			SUM(CASE WHEN payment_status='paid' THEN 1 ELSE 0 END) as paid_orders
			FROM db_online_orders 
			WHERE store_id=$storeId AND DATE(created_at)='$today' AND status=1");
		if($q->num_rows() > 0){
			$row = $q->row();
			$stats['total_orders'] = (int)$row->total_orders;
			$stats['total_revenue'] = (float)$row->total_revenue;
			$stats['pending_orders'] = (int)$row->pending_orders;
			$stats['paid_orders'] = (int)$row->paid_orders;
		}
		return $stats;
	}

	public function getTopOnlineProducts($storeId = null, $limit = 10){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->query("SELECT 
			i.item_name, i.item_image, SUM(oi.qty) as total_qty, SUM(oi.total_price) as total_revenue
			FROM db_online_order_items oi
			JOIN db_online_orders o ON o.id=oi.order_id
			JOIN db_items i ON i.id=oi.item_id
			WHERE o.store_id=$storeId AND oi.item_type='product' AND o.status=1
			GROUP BY oi.item_id
			ORDER BY total_qty DESC
			LIMIT $limit")->result();
	}

	// ============== QR CODES ==============

	public function createQrCode($data){
		$this->db->insert('db_qr_codes', $data);
		return $this->db->insert_id();
	}

	public function getQrCodes($storeId = null, $type = null){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->where('store_id', $storeId);
		if($type){
			$this->db->where('qr_type', $type);
		}
		return $this->db->get('db_qr_codes')->result();
	}

	public function getQrCode($id){
		return $this->db->where('id', $id)->get('db_qr_codes')->row();
	}

	public function deleteQrCode($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', $id)->where('store_id', $storeId)->delete('db_qr_codes');
	}

	// ============== HELPERS ==============

	private function generateOrderCode(){
		$prefix = 'WEB-' . date('Ymd');
		$count = $this->db->like('order_code', $prefix, 'after')->count_all_results('db_online_orders');
		return $prefix . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
	}

	public function getProductEffectivePrice($product){
		// Use online_price if set, otherwise sales_price
		$price = $product->online_price > 0 ? $product->online_price : $product->sales_price;
		// Apply discount if any
		if($product->discount > 0){
			if($product->discount_type == 'Percentage'){
				$price = $price - ($price * $product->discount / 100);
			} else {
				$price = $price - $product->discount;
			}
		}
		return max(0, round($price, 2));
	}

	public function getServiceEffectivePrice($service){
		$price = $service->price;
		if($service->discount_price > 0 && $service->discount_price < $price){
			$price = $service->discount_price;
		}
		return max(0, round($price, 2));
	}

	// ============== THEMES ==============

	public function getAllThemes(){
		$this->seedThemesIfEmpty();
		return $this->db->where('status', 1)->order_by('sort_order', 'asc')->get('db_storefront_themes')->result();
	}

	public function getTheme($themeId){
		$this->seedThemesIfEmpty();
		return $this->db->where('id', $themeId)->where('status', 1)->get('db_storefront_themes')->row();
	}

	public function getThemeByKey($key){
		$this->seedThemesIfEmpty();
		return $this->db->where('theme_key', $key)->where('status', 1)->get('db_storefront_themes')->row();
	}

	/**
	 * Get active themes for a theme-industry group (e.g. fashion, grocery, services).
	 */
	public function getThemesByIndustry($industry, $activeOnly = true){
		$this->seedThemesIfEmpty();
		$target = $this->_normalizeThemeIndustry($industry);
		if($activeOnly){
			$this->db->where('status', 1);
		}
		$rows = $this->db->order_by('sort_order', 'asc')
			->get('db_storefront_themes')->result();
		$result = [];
		foreach($rows as $r){
			if($this->_normalizeThemeIndustry($r->industry ?? '') === $target){
				$result[] = $r;
			}
		}
		return $result;
	}

	/**
	 * Map a store industry_type (business profile) to the theme industry group,
	 * then return the themes that belong to that group.
	 */
	public function getThemesByIndustryForStore($industry_type = null, $asObjects = true){
		if(empty($industry_type)){
			if(!function_exists('mp_get_store_profile')){
				$this->load->helper('business_profile');
			}
			$profile = function_exists('mp_get_store_profile') ? mp_get_store_profile() : [];
			$industry_type = $profile['industry_type'] ?? 'general_retail';
		}

		$themeIndustry = $this->_getThemeIndustryForType($industry_type);
		$themes = $this->getThemesByIndustry($themeIndustry);

		if(empty($themes)){
			// Fallback to general retail group so the store never has no theme.
			$themes = $this->getThemesByIndustry('general');
		}

		if($asObjects){
			return $themes;
		}
		return array_column($themes, 'theme_name', 'theme_key');
	}

	/**
	 * Determine the theme industry group for a business profile industry_type.
	 * Uses the business preset's base theme and reads its industry column.
	 */
	private function _getThemeIndustryForType($industry_type){
		if(!function_exists('mp_get_business_presets')){
			$this->load->helper('business_profile');
		}
		$presets = function_exists('mp_get_business_presets') ? mp_get_business_presets() : [];
		$preset = $presets[$industry_type] ?? ($presets['general_retail'] ?? []);
		$baseKey = $preset['theme_key'] ?? 'general_retail';

		$baseTheme = $this->getThemeByKey($baseKey);
		if($baseTheme && !empty($baseTheme->industry)){
			return $this->_normalizeThemeIndustry($baseTheme->industry);
		}

		// Direct theme key match fallback
		$direct = $this->getThemeByKey($industry_type);
		if($direct && !empty($direct->industry)){
			return $this->_normalizeThemeIndustry($direct->industry);
		}

		return 'general';
	}

	/**
	 * Normalize a theme industry value to a canonical lowercase key.
	 */
	private function _normalizeThemeIndustry($industry){
		$industry = strtolower(trim($industry));
		$industry = preg_replace('/[^a-z0-9]/', '', $industry);
		// Map legacy / verbose values to canonical names
		$map = [
			'generalretail' => 'general',
			'general' => 'general',
			'healthcare' => 'pharmacy',
			'pharmacy' => 'pharmacy',
			'beautyandcosmetics' => 'beauty',
			'beautycosmetics' => 'beauty',
			'beautyspa' => 'beauty',
			'salonbarbershop' => 'beauty',
			'makeupartist' => 'beauty',
			'fashion' => 'fashion',
			'apparel' => 'fashion',
			'electronics' => 'electronics',
			'tech' => 'electronics',
			'phoneaccessories' => 'electronics',
			'grocery' => 'grocery',
			'supermarket' => 'grocery',
			'restaurant' => 'restaurant',
			'food' => 'restaurant',
			'bakery' => 'restaurant',
			'services' => 'services',
			'service' => 'services',
			'servicebusiness' => 'services',
			'laundry' => 'laundry',
			'laundrydrycleaning' => 'laundry',
			'laundryanddrycleaning' => 'laundry',
		];
		return $map[$industry] ?? $industry;
	}

	/**
	 * Auto-seed themes if db_storefront_themes is empty
	 */
	public function seedThemesIfEmpty(){
		if(self::$themesSeeded) return;
		self::$themesSeeded = true;
		if(!$this->db->table_exists('db_storefront_themes')) return;

		$themes = [
			['theme_key' => 'general_retail', 'theme_name' => 'General Retail', 'industry' => 'general', 'description' => 'Clean, modern default theme for any retail store.', 'default_primary_color' => '#3B82F6', 'default_secondary_color' => '#10B981', 'default_font_family' => 'Inter', 'sort_order' => 1],
			['theme_key' => 'healthcare_pro', 'theme_name' => 'HealthCare Pro', 'industry' => 'pharmacy', 'description' => 'Professional pharmacy and healthcare theme with trust-focused design.', 'default_primary_color' => '#005EB8', 'default_secondary_color' => '#00A86B', 'default_font_family' => 'Inter', 'sort_order' => 2],
			['theme_key' => 'beauty_luxe', 'theme_name' => 'Beauty Luxe', 'industry' => 'beauty', 'description' => 'Elegant beauty and cosmetics theme with soft aesthetics.', 'default_primary_color' => '#F8A4C8', 'default_secondary_color' => '#D4AF37', 'default_font_family' => 'Playfair Display', 'sort_order' => 3],
			// Fashion presets (3-4 designs per industry)
			['theme_key' => 'urban_fashion', 'theme_name' => 'Urban Editorial', 'industry' => 'fashion', 'description' => 'Bold editorial layout with high-contrast typography and magazine-style hero sections.', 'default_primary_color' => '#111111', 'default_secondary_color' => '#FF3B30', 'default_font_family' => 'Montserrat', 'sort_order' => 4],
			['theme_key' => 'fashion_modern', 'theme_name' => 'Modern Minimal', 'industry' => 'fashion', 'description' => 'Clean, Shopify-style minimal design with generous whitespace, soft cards and refined typography.', 'default_primary_color' => '#0F172A', 'default_secondary_color' => '#6366F1', 'default_font_family' => 'Inter', 'sort_order' => 5],
			['theme_key' => 'fashion_boutique', 'theme_name' => 'Boutique Luxe', 'industry' => 'fashion', 'description' => 'Elegant serif-driven boutique experience with refined gold accents and graceful transitions.', 'default_primary_color' => '#7C2D12', 'default_secondary_color' => '#D4AF37', 'default_font_family' => 'Playfair Display', 'sort_order' => 6],
			['theme_key' => 'fashion_modest', 'theme_name' => 'Modest Studio', 'industry' => 'fashion', 'description' => 'Warm, modest-wear focused storefront with soft neutrals, calm spacing and inclusive imagery.', 'default_primary_color' => '#1F2937', 'default_secondary_color' => '#C2956A', 'default_font_family' => 'Lora', 'sort_order' => 7],
			['theme_key' => 'fashion_luxe', 'theme_name' => 'Fashion Luxe', 'industry' => 'fashion', 'description' => 'Editorial luxury fashion theme with dramatic imagery, refined serif typography, and warm gold accents.', 'default_primary_color' => '#1A1A1A', 'default_secondary_color' => '#C9A961', 'default_font_family' => 'Playfair Display', 'sort_order' => 8],
		['theme_key' => 'tech_hub', 'theme_name' => 'Tech Hub', 'industry' => 'electronics', 'description' => 'Modern electronics and gadgets theme with tech-forward design.', 'default_primary_color' => '#0A2540', 'default_secondary_color' => '#635BFF', 'default_font_family' => 'Inter', 'sort_order' => 9],
			['theme_key' => 'fresh_market', 'theme_name' => 'Fresh Market', 'industry' => 'grocery', 'description' => 'Warm supermarket and grocery theme with organic feel.', 'default_primary_color' => '#2E7D32', 'default_secondary_color' => '#FF6F00', 'default_font_family' => 'Inter', 'sort_order' => 10],
			['theme_key' => 'food_express', 'theme_name' => 'Food Express', 'industry' => 'restaurant', 'description' => 'Appetizing restaurant and food ordering theme.', 'default_primary_color' => '#D32F2F', 'default_secondary_color' => '#FBC02D', 'default_font_family' => 'Inter', 'sort_order' => 11],
			['theme_key' => 'service_pro', 'theme_name' => 'Service Pro', 'industry' => 'services', 'description' => 'Professional services theme for agencies and consultancies.', 'default_primary_color' => '#1A237E', 'default_secondary_color' => '#00BCD4', 'default_font_family' => 'Inter', 'sort_order' => 12],
			['theme_key' => 'laundry', 'theme_name' => 'Sparkle Laundry', 'industry' => 'laundry', 'description' => 'Clean, fresh laundry and dry cleaning theme for pickup, delivery and wash services.', 'default_primary_color' => '#0EA5E9', 'default_secondary_color' => '#22C55E', 'default_font_family' => 'Inter', 'sort_order' => 13],
			['theme_key' => 'laundry_fresh', 'theme_name' => 'Fresh', 'industry' => 'laundry', 'description' => 'A clean, modern storefront designed for laundries, dry cleaners and garment-care businesses.', 'default_primary_color' => '#102A43', 'default_secondary_color' => '#2F80ED', 'default_font_family' => 'Inter', 'sort_order' => 14],
		];
		foreach($themes as $t){
			$sql = $this->db->insert_string('db_storefront_themes', $t);
			$sql = preg_replace('/^INSERT INTO/i', 'INSERT IGNORE INTO', $sql);
			$this->db->query($sql);
		}

		// Normalize legacy industry values for built-in themes so the engine can group them correctly
		$canonical = [
			'general_retail' => 'general',
			'healthcare_pro' => 'pharmacy',
			'beauty_luxe' => 'beauty',
			'urban_fashion' => 'fashion',
			'fashion_modern' => 'fashion',
			'fashion_boutique' => 'fashion',
			'fashion_modest' => 'fashion',
			'fashion_luxe' => 'fashion',
			'tech_hub' => 'electronics',
			'fresh_market' => 'grocery',
			'food_express' => 'restaurant',
			'service_pro' => 'services',
			'laundry' => 'laundry',
			'laundry_fresh' => 'laundry',
		];
		foreach($canonical as $key => $industry){
			$this->db->where('theme_key', $key)->update('db_storefront_themes', ['industry' => $industry]);
		}
	}

	// ============== BANNERS ==============

	public function getBanners($storeId = null, $activeOnly = false){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->where('store_id', $storeId);
		if($activeOnly){
			$today = date('Y-m-d');
			$this->db->where('status', 1);
			$this->db->group_start()
				->where('start_date IS NULL', null, false)
				->or_where('start_date <=', $today)
			->group_end();
			$this->db->group_start()
				->where('end_date IS NULL', null, false)
				->or_where('end_date >=', $today)
			->group_end();
		}
		return $this->db->order_by('display_order', 'asc')->get('db_storefront_banners')->result();
	}

	public function getBanner($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', $id)->where('store_id', $storeId)->get('db_storefront_banners')->row();
	}

	public function saveBanner($data, $bannerId = null){
		if($bannerId){
			return $this->db->where('id', $bannerId)->update('db_storefront_banners', $data);
		}
		$data['store_id'] = $data['store_id'] ?? get_current_store_id();
		return $this->db->insert('db_storefront_banners', $data);
	}

	public function deleteBanner($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', $id)->where('store_id', $storeId)->delete('db_storefront_banners');
	}

	// ============== HOMEPAGE SECTIONS ==============

	public function getHomepageSections($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('store_id', $storeId)->order_by('display_order', 'asc')->get('db_storefront_homepage_sections')->result();
	}

	public function saveHomepageSection($storeId, $sectionKey, $isEnabled, $displayOrder = null){
		$storeId = $storeId ?: get_current_store_id();
		$exists = $this->db->where('store_id', $storeId)->where('section_key', $sectionKey)->get('db_storefront_homepage_sections')->row();
		$data = ['is_enabled' => $isEnabled ? 1 : 0];
		if($displayOrder !== null) $data['display_order'] = $displayOrder;
		if($exists){
			return $this->db->where('id', $exists->id)->update('db_storefront_homepage_sections', $data);
		}
		$data['store_id'] = $storeId;
		$data['section_key'] = $sectionKey;
		$data['section_label'] = ucwords(str_replace('_', ' ', $sectionKey));
		return $this->db->insert('db_storefront_homepage_sections', $data);
	}

	public function resetHomepageSections($storeId){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->where('store_id', $storeId)->delete('db_storefront_homepage_sections');
		$defaults = [
			['hero_banner','Hero Banner',1,1],
			['trust_badges','Trust Badges',1,2],
			['promo_banner','Promotional Banner',1,3],
			['featured_categories','Featured Categories',1,4],
			['featured_products','Featured Products',1,5],
			['featured_services','Featured Services',1,6],
			['best_sellers','Best Sellers',0,7],
			['new_arrivals','New Arrivals',0,8],
			['brands','Brands',0,9],
			['testimonials','Testimonials',0,10],
			['instagram_gallery','Instagram Gallery',0,11],
			['store_info','Store Information',1,12],
			['faqs','FAQs',0,13],
			['contact_section','Contact Section',1,14],
			['whatsapp_cta','WhatsApp CTA',1,15],
			['newsletter','Newsletter CTA',0,16],
			['store_hours','Store Hours',0,17]
		];
		foreach($defaults as $d){
			$this->db->insert('db_storefront_homepage_sections', [
				'store_id' => $storeId,
				'section_key' => $d[0],
				'section_label' => $d[1],
				'is_enabled' => $d[2],
				'display_order' => $d[3]
			]);
		}
		return true;
	}

	public function duplicateHomepageSection($storeId, $sectionKey){
		$storeId = $storeId ?: get_current_store_id();
		$original = $this->db->where('store_id', $storeId)->where('section_key', $sectionKey)->get('db_storefront_homepage_sections')->row();
		if(!$original) return false;

		$baseKey = preg_replace('/_\d+$/', '', $sectionKey);
		// Find next available copy number
		$existing = $this->db->where('store_id', $storeId)->like('section_key', $baseKey . '_', 'after')->get('db_storefront_homepage_sections')->result();
		$maxNum = 0;
		foreach($existing as $e){
			if(preg_match('/_' . preg_quote($baseKey, '/') . '_(\d+)$/', $e->section_key, $m) || preg_match('/' . preg_quote($baseKey, '/') . '_(\d+)$/', $e->section_key, $m)){
				$maxNum = max($maxNum, (int)$m[1]);
			}
		}
		// Also check if base key itself exists (it's copy 1)
		if($this->db->where('store_id', $storeId)->where('section_key', $baseKey)->count_all_results('db_storefront_homepage_sections') > 0){
			$maxNum = max($maxNum, 1);
		}
		$newNum = $maxNum + 1;
		$newKey = $baseKey . '_' . $newNum;

		// Get max display order
		$maxOrder = $this->db->where('store_id', $storeId)->select_max('display_order')->get('db_storefront_homepage_sections')->row()->display_order ?? 0;

		$label = $original->section_label;
		if(preg_match('/\s*\(\d+\)$/', $label)){
			$label = preg_replace('/\s*\(\d+\)$/', '', $label);
		}
		$label .= ' (' . $newNum . ')';

		return $this->db->insert('db_storefront_homepage_sections', [
			'store_id' => $storeId,
			'section_key' => $newKey,
			'section_label' => $label,
			'is_enabled' => $original->is_enabled,
			'display_order' => $maxOrder + 1,
			'config_json' => $original->config_json
		]);
	}

	// ============== DOMAINS ==============

	public function getDomains($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('store_id', $storeId)->get('db_storefront_domains')->result();
	}

	public function getDomain($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', $id)->where('store_id', $storeId)->get('db_storefront_domains')->row();
	}

	public function getStoreByDomain($domain){
		return $this->db->where('domain_value', $domain)->where('connection_status', 'connected')->get('db_storefront_domains')->row();
	}

	public function saveDomain($data, $domainId = null){
		if($domainId){
			return $this->db->where('id', $domainId)->update('db_storefront_domains', $data);
		}
		$data['store_id'] = $data['store_id'] ?? get_current_store_id();
		return $this->db->insert('db_storefront_domains', $data);
	}

	public function deleteDomain($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', $id)->where('store_id', $storeId)->delete('db_storefront_domains');
	}

	// ============== BEST SELLERS / NEW ARRIVALS ==============

	public function getBestSellers($storeId = null, $limit = 8){
		$storeId = (int)($storeId ?: get_current_store_id());
		$limit = (int)$limit;
		$expiryClause = $this->_expiredWhere('i', $storeId);
		return $this->db->query("SELECT i.id, i.item_name, i.item_image, i.sales_price, i.online_price, i.discount_type, i.discount, i.stock, SUM(oi.qty) as sold_count
			FROM db_online_order_items oi
			JOIN db_online_orders o ON o.id=oi.order_id
			JOIN db_items i ON i.id=oi.item_id
			WHERE o.store_id=? AND oi.item_type='product' AND o.status=1 AND i.publish_online=1 AND (i.item_group IS NULL OR i.item_group='Single') AND $expiryClause
			GROUP BY oi.item_id
			ORDER BY sold_count DESC
			LIMIT ?", [$storeId, $limit])->result();
	}

	public function getNewArrivals($storeId = null, $limit = 8){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.id, a.item_name, a.item_image, a.sales_price, a.online_price, a.discount_type, a.discount, a.stock, b.category_name');
		$this->db->from('db_items a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.publish_online', 1);
		$this->db->where('a.status', 1);
		$this->db->where('a.service_bit', 0);
		$this->db->where("(a.item_group IS NULL OR a.item_group='Single')");
		$this->db->where($this->_expiredWhere('a', $storeId), NULL, FALSE);
		$this->db->order_by('a.id', 'desc');
		$this->db->limit($limit);
		return $this->db->get()->result();
	}

	// ============== ANALYTICS ==============

	public function trackVisit($storeId, $data){
		$data['store_id'] = $storeId;
		// Check if this session_id has visited before
		if(!empty($data['session_id'])){
			$existing = $this->db->where('store_id', $storeId)->where('session_id', $data['session_id'])->count_all_results('db_storefront_analytics');
			$data['is_new_user'] = ($existing == 0) ? 1 : 0;
		}
		return $this->db->insert('db_storefront_analytics', $data);
	}

	public function getAnalyticsSummary($storeId, $startDate = null, $endDate = null){
		$endDate = $endDate ?: date('Y-m-d 23:59:59');
		$startDate = $startDate ?: date('Y-m-d 00:00:00', strtotime('-30 days'));
		$total = $this->db->where('store_id', $storeId)->where('created_at >=', $startDate)->where('created_at <=', $endDate)->count_all_results('db_storefront_analytics');
		$unique = $this->db->query("SELECT COUNT(DISTINCT session_id) as cnt FROM db_storefront_analytics WHERE store_id=? AND created_at >= ? AND created_at <= ?", [$storeId, $startDate, $endDate])->row()->cnt;
		$today = (int)$this->db->query("SELECT COUNT(*) as cnt FROM db_storefront_analytics WHERE store_id=? AND DATE(created_at)=?", [$storeId, date('Y-m-d')])->row()->cnt;
		$yesterday = (int)$this->db->query("SELECT COUNT(*) as cnt FROM db_storefront_analytics WHERE store_id=? AND DATE(created_at)=?", [$storeId, date('Y-m-d', strtotime('-1 day'))])->row()->cnt;
		$newUsers = $this->db->query("SELECT COUNT(DISTINCT session_id) as cnt FROM db_storefront_analytics WHERE store_id=? AND created_at >= ? AND created_at <= ? AND is_new_user = 1", [$storeId, $startDate, $endDate])->row()->cnt;
		$returningUsers = $unique - $newUsers;
		return ['total' => $total, 'unique' => $unique, 'today' => $today, 'yesterday' => $yesterday, 'new_users' => $newUsers, 'returning_users' => max(0, $returningUsers)];
	}

	public function getTopSources($storeId, $startDate = null, $endDate = null, $limit = 10){
		$endDate = $endDate ?: date('Y-m-d 23:59:59');
		$startDate = $startDate ?: date('Y-m-d 00:00:00', strtotime('-30 days'));
		return $this->db->query("SELECT source, COUNT(*) as visits FROM db_storefront_analytics WHERE store_id=? AND created_at >= ? AND created_at <= ? GROUP BY source ORDER BY visits DESC LIMIT ?", [$storeId, $startDate, $endDate, $limit])->result();
	}

	public function getTopPages($storeId, $startDate = null, $endDate = null, $limit = 10){
		$endDate = $endDate ?: date('Y-m-d 23:59:59');
		$startDate = $startDate ?: date('Y-m-d 00:00:00', strtotime('-30 days'));
		return $this->db->query("SELECT page_url, COUNT(*) as visits FROM db_storefront_analytics WHERE store_id=? AND created_at >= ? AND created_at <= ? GROUP BY page_url ORDER BY visits DESC LIMIT ?", [$storeId, $startDate, $endDate, $limit])->result();
	}

	public function getDailyVisits($storeId, $startDate = null, $endDate = null){
		$endDate = $endDate ?: date('Y-m-d 23:59:59');
		$startDate = $startDate ?: date('Y-m-d 00:00:00', strtotime('-30 days'));
		return $this->db->query("SELECT DATE(created_at) as `date`, COUNT(*) as visits, COUNT(DISTINCT session_id) as unique_visits FROM db_storefront_analytics WHERE store_id=? AND created_at >= ? AND created_at <= ? GROUP BY DATE(created_at) ORDER BY `date` ASC", [$storeId, $startDate, $endDate])->result();
	}

	public function getVisitsByHour($storeId, $date){
		$start = $date . ' 00:00:00';
		$end = $date . ' 23:59:59';
		return $this->db->query("SELECT HOUR(created_at) as `hour`, COUNT(*) as visits, COUNT(DISTINCT session_id) as unique_visits FROM db_storefront_analytics WHERE store_id=? AND created_at >= ? AND created_at <= ? GROUP BY HOUR(created_at) ORDER BY `hour` ASC", [$storeId, $start, $end])->result();
	}

	public function getVisitsByMonth($storeId, $startDate = null, $endDate = null){
		$endDate = $endDate ?: date('Y-m-d 23:59:59');
		$startDate = $startDate ?: date('Y-m-d 00:00:00', strtotime('-365 days'));
		return $this->db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as `month`, COUNT(*) as visits, COUNT(DISTINCT session_id) as unique_visits FROM db_storefront_analytics WHERE store_id=? AND created_at >= ? AND created_at <= ? GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY `month` ASC", [$storeId, $startDate, $endDate])->result();
	}

	public function getHeatmapData($storeId, $startDate = null, $endDate = null){
		$endDate = $endDate ?: date('Y-m-d 23:59:59');
		$startDate = $startDate ?: date('Y-m-d 00:00:00', strtotime('-30 days'));
		return $this->db->query("SELECT DAYOFWEEK(created_at) as dow, HOUR(created_at) as hour, COUNT(*) as visits FROM db_storefront_analytics WHERE store_id=? AND created_at >= ? AND created_at <= ? GROUP BY DAYOFWEEK(created_at), HOUR(created_at)", [$storeId, $startDate, $endDate])->result();
	}

	public function getDeviceBreakdown($storeId, $startDate = null, $endDate = null){
		$endDate = $endDate ?: date('Y-m-d 23:59:59');
		$startDate = $startDate ?: date('Y-m-d 00:00:00', strtotime('-30 days'));
		$rows = $this->db->query("SELECT user_agent FROM db_storefront_analytics WHERE store_id=? AND created_at >= ? AND created_at <= ? AND user_agent IS NOT NULL", [$storeId, $startDate, $endDate])->result();
		$devices = ['Desktop' => 0, 'Mobile' => 0, 'Tablet' => 0, 'Bot/Other' => 0];
		foreach($rows as $r){
			$ua = strtolower($r->user_agent);
			if(strpos($ua, 'bot') !== false || strpos($ua, 'crawl') !== false || strpos($ua, 'spider') !== false){
				$devices['Bot/Other']++;
			}elseif(strpos($ua, 'tablet') !== false || strpos($ua, 'ipad') !== false){
				$devices['Tablet']++;
			}elseif(strpos($ua, 'mobile') !== false || strpos($ua, 'android') !== false || strpos($ua, 'iphone') !== false){
				$devices['Mobile']++;
			}else{
				$devices['Desktop']++;
			}
		}
		return $devices;
	}

	public function getSearchTerms($storeId, $startDate = null, $endDate = null, $limit = 20){
		$endDate = $endDate ?: date('Y-m-d 23:59:59');
		$startDate = $startDate ?: date('Y-m-d 00:00:00', strtotime('-30 days'));
		return $this->db->query("SELECT search_term, COUNT(*) as visits FROM db_storefront_analytics WHERE store_id=? AND created_at >= ? AND created_at <= ? AND search_term IS NOT NULL AND search_term != '' GROUP BY search_term ORDER BY visits DESC LIMIT ?", [$storeId, $startDate, $endDate, $limit])->result();
	}

	public function getCustomerVisits($storeId, $startDate = null, $endDate = null, $limit = 20){
		$endDate = $endDate ?: date('Y-m-d 23:59:59');
		$startDate = $startDate ?: date('Y-m-d 00:00:00', strtotime('-30 days'));
		return $this->db->query("SELECT session_id, COUNT(*) as visits, MIN(created_at) as first_visit, MAX(created_at) as last_visit FROM db_storefront_analytics WHERE store_id=? AND created_at >= ? AND created_at <= ? GROUP BY session_id ORDER BY visits DESC LIMIT ?", [$storeId, $startDate, $endDate, $limit])->result();
	}

	public function getRecentVisits($storeId, $limit = 50){
		return $this->db->where('store_id', $storeId)->order_by('id', 'desc')->limit($limit)->get('db_storefront_analytics')->result();
	}

	// ============== STOREFRONT BRANDS ==============

	public function getStorefrontBrands($storeId = null, $enabledOnly = true){
		try{
			$storeId = $storeId ?: get_current_store_id();
			$this->db->where('store_id', $storeId);
			if($enabledOnly) $this->db->where('is_enabled', 1);
			$this->db->order_by('sort_order', 'asc');
			return $this->db->get('db_storefront_brands')->result();
		} catch(Exception $e){ return []; }
	}

	public function saveStorefrontBrand($data, $id = null){
		if($id){
			$res = $this->db->where('id', $id)->update('db_storefront_brands', $data);
			return $res ? $id : false;
		}
		$res = $this->db->insert('db_storefront_brands', $data);
		return $res ? $this->db->insert_id() : false;
	}

	public function deleteStorefrontBrand($id){
		return $this->db->where('id', $id)->delete('db_storefront_brands');
	}

	// ============== STOREFRONT TESTIMONIALS ==============

	public function getStorefrontTestimonials($storeId = null, $enabledOnly = true){
		try{
			$storeId = $storeId ?: get_current_store_id();
			$this->db->where('store_id', $storeId);
			if($enabledOnly) $this->db->where('is_enabled', 1);
			$this->db->order_by('sort_order', 'asc');
			return $this->db->get('db_storefront_testimonials')->result();
		} catch(Exception $e){ return []; }
	}

	public function saveStorefrontTestimonial($data, $id = null){
		if($id){
			$res = $this->db->where('id', $id)->update('db_storefront_testimonials', $data);
			return $res ? $id : false;
		}
		$res = $this->db->insert('db_storefront_testimonials', $data);
		return $res ? $this->db->insert_id() : false;
	}

	public function deleteStorefrontTestimonial($id){
		return $this->db->where('id', $id)->delete('db_storefront_testimonials');
	}

	// ============== STOREFRONT INSTAGRAM ==============

	public function getStorefrontInstagram($storeId = null, $enabledOnly = true){
		try{
			$storeId = $storeId ?: get_current_store_id();
			$this->db->where('store_id', $storeId);
			if($enabledOnly) $this->db->where('is_enabled', 1);
			$this->db->order_by('sort_order', 'asc');
			return $this->db->get('db_storefront_instagram')->result();
		} catch(Exception $e){ return []; }
	}

	public function saveStorefrontInstagram($data, $id = null){
		if($id){
			$res = $this->db->where('id', $id)->update('db_storefront_instagram', $data);
			return $res ? $id : false;
		}
		$res = $this->db->insert('db_storefront_instagram', $data);
		return $res ? $this->db->insert_id() : false;
	}

	public function deleteStorefrontInstagram($id){
		return $this->db->where('id', $id)->delete('db_storefront_instagram');
	}

	// ============== STOREFRONT FAQS ==============

	public function getStorefrontFaqs($storeId = null, $enabledOnly = true){
		try{
			$storeId = $storeId ?: get_current_store_id();
			$this->db->where('store_id', $storeId);
			if($enabledOnly) $this->db->where('is_enabled', 1);
			$this->db->order_by('sort_order', 'asc');
			return $this->db->get('db_storefront_faqs')->result();
		} catch(Exception $e){ return []; }
	}

	public function saveStorefrontFaq($data, $id = null){
		if($id){
			$res = $this->db->where('id', $id)->update('db_storefront_faqs', $data);
			return $res ? $id : false;
		}
		$res = $this->db->insert('db_storefront_faqs', $data);
		return $res ? $this->db->insert_id() : false;
	}

	public function deleteStorefrontFaq($id){
		return $this->db->where('id', $id)->delete('db_storefront_faqs');
	}

	// ============== CUSTOMER PORTAL HELPERS ==============

	public function getOrdersByCustomer($customerId, $storeId, $limit = 50, $offset = 0){
		return $this->db->where('store_id', $storeId)->where('customer_id', $customerId)->order_by('id','desc')->limit($limit, $offset)->get('db_online_orders')->result();
	}

	public function getCustomerPortalSession($token, $storeId){
		return $this->db->where('session_token', $token)->where('store_id', $storeId)->where('expires_at >', date('Y-m-d H:i:s'))->get('db_storefront_customer_sessions')->row();
	}

	public function createPortalSession($data){
		$this->db->insert('db_storefront_customer_sessions', $data);
		return $this->db->insert_id();
	}

	public function cleanupPortalSessions($storeId, $phone, $email = ''){
		if(!empty($phone)){
			$this->db->where('store_id', $storeId)->where('phone', $phone)->delete('db_storefront_customer_otp');
		}
		if(!empty($email)){
			$this->db->where('store_id', $storeId)->where('email', $email)->delete('db_storefront_customer_otp');
		}
	}

	public function getSendchampCredentials($storeId){
		$creds = $this->db->where('store_id', $storeId)->get('db_sendchamp')->row();
		if($creds) return $creds;
		$settings = $this->getSettings($storeId);
		if($settings && !empty($settings->sendchamp_json)){
			$json = json_decode($settings->sendchamp_json, false);
			if($json) return $json;
		}
		return null;
	}
}
