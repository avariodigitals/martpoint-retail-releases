<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Storefront Model
 * Manages online storefront settings, services, online orders, and QR codes.
 */
class Storefront_model extends CI_Model {

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
		return "($tableAlias.expire_date IS NULL OR $tableAlias.expire_date = '0000-00-00' OR $tableAlias.expire_date >= '".date('Y-m-d')."')";
	}

	/**
	 * Auto-create necessary tables if they don't exist
	 */
	private function ensureTables(){
		// Add publish_online to db_items if not exists
		if(!$this->db->field_exists('publish_online','db_items')){
			$this->db->query("ALTER TABLE db_items ADD COLUMN publish_online TINYINT(1) NOT NULL DEFAULT 1 AFTER status");
		}

		// Add online_price to db_items for storefront-specific pricing
		if(!$this->db->field_exists('online_price','db_items')){
			$this->db->query("ALTER TABLE db_items ADD COLUMN online_price DECIMAL(12,2) NULL AFTER sales_price");
		}

		// Storefront Settings
		$this->db->query("CREATE TABLE IF NOT EXISTS db_storefront_settings (
			id INT AUTO_INCREMENT PRIMARY KEY,
			store_id INT NOT NULL DEFAULT 0,
			store_slug VARCHAR(100) NOT NULL DEFAULT '',
			store_description TEXT,
			store_banner VARCHAR(255),
			store_logo VARCHAR(255),
			whatsapp_number VARCHAR(20) DEFAULT '',
			store_email VARCHAR(100) DEFAULT '',
			store_phone VARCHAR(20) DEFAULT '',
			store_address TEXT,
			default_branch_id INT DEFAULT 0,
			store_status ENUM('active','maintenance') DEFAULT 'active',
			allow_paystack TINYINT(1) DEFAULT 1,
			allow_whatsapp TINYINT(1) DEFAULT 1,
			allow_pay_on_delivery TINYINT(1) DEFAULT 1,
			allow_services TINYINT(1) DEFAULT 1,
			allow_backorder TINYINT(1) DEFAULT 0,
			show_search TINYINT(1) DEFAULT 1,
			show_categories TINYINT(1) DEFAULT 1,
			show_whatsapp_cta TINYINT(1) DEFAULT 1,
			featured_products_limit INT DEFAULT 8,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			UNIQUE KEY uk_storefront_store (store_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		// Add theme-related columns if they don't exist
		$cols = [
			'theme_id' => 'INT DEFAULT NULL AFTER store_id',
			'primary_color' => "VARCHAR(20) DEFAULT '#3B82F6'",
			'secondary_color' => "VARCHAR(20) DEFAULT '#10B981'",
			'font_family' => "VARCHAR(100) DEFAULT 'Inter'",
			'button_style' => "VARCHAR(50) DEFAULT 'rounded'",
			'store_headline' => 'VARCHAR(255) DEFAULT NULL',
			'store_subheadline' => 'VARCHAR(500) DEFAULT NULL',
			'favicon' => 'VARCHAR(255) DEFAULT NULL',
			'desktop_banner' => 'VARCHAR(255) DEFAULT NULL',
			'mobile_banner' => 'VARCHAR(255) DEFAULT NULL',
			'instagram_url' => 'VARCHAR(500) DEFAULT NULL',
			'facebook_url' => 'VARCHAR(500) DEFAULT NULL',
			'tiktok_url' => 'VARCHAR(500) DEFAULT NULL',
			'x_url' => 'VARCHAR(500) DEFAULT NULL',
			'youtube_url' => 'VARCHAR(500) DEFAULT NULL',
			'business_hours' => 'TEXT DEFAULT NULL',
			'announcement_bar' => 'VARCHAR(500) DEFAULT NULL',
			'announcement_bar_color' => "VARCHAR(20) DEFAULT '#0F172A'",
			'preview_mode' => 'TINYINT(1) DEFAULT 0',
			'preview_theme_id' => 'INT DEFAULT NULL',
			'meta_title' => 'VARCHAR(255) DEFAULT NULL',
			'meta_description' => 'VARCHAR(500) DEFAULT NULL',
			'footer_bg_color' => "VARCHAR(20) DEFAULT '#0F172A'",
			'header_text_color' => "VARCHAR(20) DEFAULT ''",
			'footer_style' => "VARCHAR(50) DEFAULT 'standard'",
			'footer_about_us' => 'TEXT DEFAULT NULL',
			'footer_text_color' => "VARCHAR(20) DEFAULT '#94A3B8'",
			'footer_address_url' => 'VARCHAR(500) DEFAULT NULL',
			'button_color' => "VARCHAR(20) DEFAULT '#3B82F6'",
			'meta_keywords' => 'VARCHAR(255) DEFAULT NULL',
			'google_analytics_id' => 'VARCHAR(50) DEFAULT NULL',
			'facebook_pixel_id' => 'VARCHAR(50) DEFAULT NULL',
			'robots_index' => 'TINYINT(1) DEFAULT 1',
			'custom_head_scripts' => 'TEXT DEFAULT NULL',
		];
		foreach($cols as $col => $def){
			if(!$this->db->field_exists($col, 'db_storefront_settings')){
				$this->db->query("ALTER TABLE db_storefront_settings ADD COLUMN {$col} {$def}");
			}
		}

		// Services (separate from items for richer service fields)
		$this->db->query("CREATE TABLE IF NOT EXISTS db_services (
			id INT AUTO_INCREMENT PRIMARY KEY,
			store_id INT NOT NULL DEFAULT 0,
			service_name VARCHAR(200) NOT NULL,
			service_image VARCHAR(255),
			category_id INT DEFAULT 0,
			price DECIMAL(12,2) NOT NULL DEFAULT 0,
			discount_price DECIMAL(12,2) DEFAULT NULL,
			service_duration VARCHAR(50),
			description TEXT,
			available_online TINYINT(1) DEFAULT 1,
			requires_appointment TINYINT(1) DEFAULT 0,
			requires_note TINYINT(1) DEFAULT 0,
			location_type ENUM('in-store','customer-location','online') DEFAULT 'in-store',
			sort_order INT DEFAULT 0,
			status TINYINT(1) DEFAULT 1,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			INDEX idx_store_status (store_id, status, available_online)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		// Online Orders
		$this->db->query("CREATE TABLE IF NOT EXISTS db_online_orders (
			id INT AUTO_INCREMENT PRIMARY KEY,
			store_id INT NOT NULL DEFAULT 0,
			order_code VARCHAR(50) NOT NULL,
			customer_name VARCHAR(200),
			customer_email VARCHAR(100),
			customer_phone VARCHAR(20),
			customer_address TEXT,
			order_type ENUM('product','service','mixed') DEFAULT 'product',
			order_status ENUM('pending','paid','processing','ready','completed','cancelled') DEFAULT 'pending',
			payment_status ENUM('unpaid','paid','partially_paid','failed','refunded') DEFAULT 'unpaid',
			payment_method ENUM('paystack','whatsapp','pay_on_delivery') DEFAULT 'pay_on_delivery',
			paystack_reference VARCHAR(100),
			paystack_amount DECIMAL(12,2) DEFAULT 0,
			subtotal DECIMAL(12,2) DEFAULT 0,
			delivery_fee DECIMAL(12,2) DEFAULT 0,
			tax_amount DECIMAL(12,2) DEFAULT 0,
			grand_total DECIMAL(12,2) DEFAULT 0,
			service_date DATE,
			service_time VARCHAR(20),
			service_note TEXT,
			table_number VARCHAR(20),
			qr_code_id INT DEFAULT 0,
			whatsapp_sent TINYINT(1) DEFAULT 0,
			ip_address VARCHAR(45),
			user_agent TEXT,
			status TINYINT(1) DEFAULT 1,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			INDEX idx_store_status (store_id, order_status),
			INDEX idx_created (created_at),
			INDEX idx_paystack (paystack_reference)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		// Online Order Items
		$this->db->query("CREATE TABLE IF NOT EXISTS db_online_order_items (
			id INT AUTO_INCREMENT PRIMARY KEY,
			order_id INT NOT NULL,
			item_type ENUM('product','service') DEFAULT 'product',
			item_id INT NOT NULL,
			item_name VARCHAR(200),
			item_image VARCHAR(255),
			qty INT DEFAULT 1,
			unit_price DECIMAL(12,2) DEFAULT 0,
			total_price DECIMAL(12,2) DEFAULT 0,
			service_note TEXT,
			status TINYINT(1) DEFAULT 1,
			INDEX idx_order (order_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		// Theme Engine Tables
		$this->db->query("CREATE TABLE IF NOT EXISTS db_storefront_themes (
			id INT AUTO_INCREMENT PRIMARY KEY,
			theme_key VARCHAR(50) NOT NULL UNIQUE,
			theme_name VARCHAR(100) NOT NULL,
			industry VARCHAR(50) NOT NULL,
			description TEXT,
			default_primary_color VARCHAR(20) DEFAULT '#3B82F6',
			default_secondary_color VARCHAR(20) DEFAULT '#10B981',
			default_font_family VARCHAR(100) DEFAULT 'Inter',
			preview_image VARCHAR(255),
			status TINYINT(1) DEFAULT 1,
			sort_order INT DEFAULT 0,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		$this->db->query("CREATE TABLE IF NOT EXISTS db_storefront_banners (
			id INT AUTO_INCREMENT PRIMARY KEY,
			store_id INT NOT NULL,
			banner_type ENUM('hero','promo') DEFAULT 'hero',
			banner_title VARCHAR(255),
			banner_subtitle VARCHAR(500),
			desktop_image VARCHAR(255),
			mobile_image VARCHAR(255),
			button_text VARCHAR(100),
			button_url VARCHAR(500),
			display_order INT DEFAULT 0,
			status TINYINT(1) DEFAULT 1,
			start_date DATE DEFAULT NULL,
			end_date DATE DEFAULT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			INDEX idx_store_status (store_id, status)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		// Add banner_type if it doesn't exist
		if(!$this->db->field_exists('banner_type', 'db_storefront_banners')){
			$this->db->query("ALTER TABLE db_storefront_banners ADD COLUMN banner_type ENUM('hero','promo') DEFAULT 'hero'");
		}

		$this->db->query("CREATE TABLE IF NOT EXISTS db_storefront_homepage_sections (
			id INT AUTO_INCREMENT PRIMARY KEY,
			store_id INT NOT NULL,
			section_key VARCHAR(50) NOT NULL,
			section_label VARCHAR(100),
			is_enabled TINYINT(1) DEFAULT 1,
			display_order INT DEFAULT 0,
			config_json TEXT,
			UNIQUE KEY uk_store_section (store_id, section_key)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		$this->db->query("CREATE TABLE IF NOT EXISTS db_storefront_domains (
			id INT AUTO_INCREMENT PRIMARY KEY,
			store_id INT NOT NULL,
			domain_type ENUM('subdomain','custom') DEFAULT 'subdomain',
			domain_value VARCHAR(255) NOT NULL,
			verification_status ENUM('pending','verified','failed') DEFAULT 'pending',
			ssl_status ENUM('pending','active','expired') DEFAULT 'pending',
			connection_status ENUM('pending','connected','disconnected') DEFAULT 'pending',
			dns_instructions TEXT,
			verified_at TIMESTAMP NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			UNIQUE KEY uk_domain (domain_value),
			INDEX idx_store (store_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		// QR Codes
		$this->db->query("CREATE TABLE IF NOT EXISTS db_qr_codes (
			id INT AUTO_INCREMENT PRIMARY KEY,
			store_id INT NOT NULL DEFAULT 0,
			qr_name VARCHAR(200),
			qr_type ENUM('store','product','service','category','table') DEFAULT 'store',
			related_id INT DEFAULT 0,
			table_number VARCHAR(20),
			qr_image VARCHAR(255),
			qr_data TEXT,
			download_count INT DEFAULT 0,
			status TINYINT(1) DEFAULT 1,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_store_type (store_id, qr_type)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		// Storefront Brands
		$this->db->query("CREATE TABLE IF NOT EXISTS db_storefront_brands (
			id INT AUTO_INCREMENT PRIMARY KEY,
			store_id INT NOT NULL,
			brand_name VARCHAR(100) NOT NULL,
			brand_logo VARCHAR(255) DEFAULT NULL,
			brand_url VARCHAR(500) DEFAULT NULL,
			is_enabled TINYINT(1) DEFAULT 1,
			sort_order INT DEFAULT 0,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_store_sort (store_id, sort_order)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

		// Storefront Testimonials
		$this->db->query("CREATE TABLE IF NOT EXISTS db_storefront_testimonials (
			id INT AUTO_INCREMENT PRIMARY KEY,
			store_id INT NOT NULL,
			customer_name VARCHAR(100) NOT NULL,
			customer_photo VARCHAR(255) DEFAULT NULL,
			testimonial_text TEXT NOT NULL,
			rating INT DEFAULT 5,
			is_enabled TINYINT(1) DEFAULT 1,
			sort_order INT DEFAULT 0,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_store_sort (store_id, sort_order)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

		// Storefront Instagram Gallery
		$this->db->query("CREATE TABLE IF NOT EXISTS db_storefront_instagram (
			id INT AUTO_INCREMENT PRIMARY KEY,
			store_id INT NOT NULL,
			image_url VARCHAR(255) NOT NULL,
			caption VARCHAR(255) DEFAULT NULL,
			link_url VARCHAR(500) DEFAULT NULL,
			is_enabled TINYINT(1) DEFAULT 1,
			sort_order INT DEFAULT 0,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_store_sort (store_id, sort_order)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

		// Storefront FAQs
		$this->db->query("CREATE TABLE IF NOT EXISTS db_storefront_faqs (
			id INT AUTO_INCREMENT PRIMARY KEY,
			store_id INT NOT NULL,
			question VARCHAR(255) NOT NULL,
			answer TEXT NOT NULL,
			is_enabled TINYINT(1) DEFAULT 1,
			sort_order INT DEFAULT 0,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_store_sort (store_id, sort_order)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

		// Storefront Analytics
		$this->db->query("CREATE TABLE IF NOT EXISTS db_storefront_analytics (
			id INT AUTO_INCREMENT PRIMARY KEY,
			store_id INT NOT NULL,
			page_url VARCHAR(500) NOT NULL,
			source VARCHAR(100) DEFAULT NULL,
			referrer VARCHAR(500) DEFAULT NULL,
			ip_address VARCHAR(45) DEFAULT NULL,
			user_agent VARCHAR(255) DEFAULT NULL,
			search_term VARCHAR(255) DEFAULT NULL,
			session_id VARCHAR(100) DEFAULT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_store_created (store_id, created_at)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
		// Add search_term if table already exists without it
		if(!$this->db->field_exists('search_term','db_storefront_analytics')){
			$this->db->query("ALTER TABLE db_storefront_analytics ADD COLUMN search_term VARCHAR(255) DEFAULT NULL AFTER user_agent");
		}
		// Add is_new_user if table already exists without it
		if(!$this->db->field_exists('is_new_user','db_storefront_analytics')){
			$this->db->query("ALTER TABLE db_storefront_analytics ADD COLUMN is_new_user TINYINT(1) NOT NULL DEFAULT 1 AFTER search_term");
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

	public function getOnlineProduct($productId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.*, b.category_name');
		$this->db->from('db_items a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.id', $productId);
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.publish_online', 1);
		$this->db->where('a.status', 1);
		$this->db->where($this->_expiredWhere('a', $storeId), NULL, FALSE);
		return $this->db->get()->row();
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
		$this->db->join('db_items b', 'b.category_id=a.id AND b.publish_online=1 AND b.status=1 AND b.service_bit=0', 'inner');
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.status', 1);
		$this->db->where($this->_expiredWhere('b', $storeId), NULL, FALSE);
		$this->db->group_by('a.id');
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
	 * Auto-seed themes if db_storefront_themes is empty
	 */
	public function seedThemesIfEmpty(){
		if(!$this->db->table_exists('db_storefront_themes')) return;
		$count = $this->db->count_all('db_storefront_themes');
		if($count > 0) return;

		$themes = [
			['theme_key' => 'general_retail', 'theme_name' => 'General Retail', 'industry' => 'general', 'description' => 'Clean, modern default theme for any retail store.', 'default_primary_color' => '#3B82F6', 'default_secondary_color' => '#10B981', 'default_font_family' => 'Inter', 'sort_order' => 1],
			['theme_key' => 'healthcare_pro', 'theme_name' => 'HealthCare Pro', 'industry' => 'pharmacy', 'description' => 'Professional pharmacy and healthcare theme with trust-focused design.', 'default_primary_color' => '#005EB8', 'default_secondary_color' => '#00A86B', 'default_font_family' => 'Inter', 'sort_order' => 2],
			['theme_key' => 'beauty_luxe', 'theme_name' => 'Beauty Luxe', 'industry' => 'beauty', 'description' => 'Elegant beauty and cosmetics theme with soft aesthetics.', 'default_primary_color' => '#F8A4C8', 'default_secondary_color' => '#D4AF37', 'default_font_family' => 'Playfair Display', 'sort_order' => 3],
			['theme_key' => 'urban_fashion', 'theme_name' => 'Urban Fashion', 'industry' => 'fashion', 'description' => 'Bold fashion and apparel theme with editorial layouts.', 'default_primary_color' => '#111111', 'default_secondary_color' => '#FF3B30', 'default_font_family' => 'Montserrat', 'sort_order' => 4],
			['theme_key' => 'tech_hub', 'theme_name' => 'Tech Hub', 'industry' => 'electronics', 'description' => 'Modern electronics and gadgets theme with tech-forward design.', 'default_primary_color' => '#0A2540', 'default_secondary_color' => '#635BFF', 'default_font_family' => 'Inter', 'sort_order' => 5],
			['theme_key' => 'fresh_market', 'theme_name' => 'Fresh Market', 'industry' => 'grocery', 'description' => 'Warm supermarket and grocery theme with organic feel.', 'default_primary_color' => '#2E7D32', 'default_secondary_color' => '#FF6F00', 'default_font_family' => 'Inter', 'sort_order' => 6],
			['theme_key' => 'food_express', 'theme_name' => 'Food Express', 'industry' => 'restaurant', 'description' => 'Appetizing restaurant and food ordering theme.', 'default_primary_color' => '#D32F2F', 'default_secondary_color' => '#FBC02D', 'default_font_family' => 'Inter', 'sort_order' => 7],
			['theme_key' => 'service_pro', 'theme_name' => 'Service Pro', 'industry' => 'services', 'description' => 'Professional services theme for agencies and consultancies.', 'default_primary_color' => '#1A237E', 'default_secondary_color' => '#00BCD4', 'default_font_family' => 'Inter', 'sort_order' => 8],
		];
		foreach($themes as $t){
			$this->db->insert('db_storefront_themes', $t);
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
			WHERE o.store_id=? AND oi.item_type='product' AND o.status=1 AND i.publish_online=1 AND $expiryClause
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
		$today = $this->db->where('store_id', $storeId)->where('DATE(created_at)', date('Y-m-d'))->count_all_results('db_storefront_analytics');
		$yesterday = $this->db->where('store_id', $storeId)->where('DATE(created_at)', date('Y-m-d', strtotime('-1 day')))->count_all_results('db_storefront_analytics');
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
		return $this->db->query("SELECT DATE(created_at) as date, COUNT(*) as visits, COUNT(DISTINCT session_id) as unique_visits FROM db_storefront_analytics WHERE store_id=? AND created_at >= ? AND created_at <= ? GROUP BY DATE(created_at) ORDER BY date ASC", [$storeId, $startDate, $endDate])->result();
	}

	public function getVisitsByHour($storeId, $date){
		$start = $date . ' 00:00:00';
		$end = $date . ' 23:59:59';
		return $this->db->query("SELECT HOUR(created_at) as hour, COUNT(*) as visits, COUNT(DISTINCT session_id) as unique_visits FROM db_storefront_analytics WHERE store_id=? AND created_at >= ? AND created_at <= ? GROUP BY HOUR(created_at) ORDER BY hour ASC", [$storeId, $start, $end])->result();
	}

	public function getVisitsByMonth($storeId, $startDate = null, $endDate = null){
		$endDate = $endDate ?: date('Y-m-d 23:59:59');
		$startDate = $startDate ?: date('Y-m-d 00:00:00', strtotime('-365 days'));
		return $this->db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as visits, COUNT(DISTINCT session_id) as unique_visits FROM db_storefront_analytics WHERE store_id=? AND created_at >= ? AND created_at <= ? GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month ASC", [$storeId, $startDate, $endDate])->result();
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
}
