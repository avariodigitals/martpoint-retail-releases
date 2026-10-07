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

			if($this->db->table_exists('db_storefront_settings')){
				$have = $this->db->list_fields('db_storefront_settings');
				$need = [
					'store_logo' => "VARCHAR(255) NULL DEFAULT NULL",
					'shipping_notice' => "TEXT NULL DEFAULT NULL",
					'shipping_methods_json' => "TEXT NULL DEFAULT NULL",
					'theme_id' => "INT NULL DEFAULT NULL",
					'primary_color' => "VARCHAR(20) NULL DEFAULT '#3B82F6'",
					'secondary_color' => "VARCHAR(20) NULL DEFAULT '#10B981'",
					'background_color' => "VARCHAR(20) NULL DEFAULT ''",
					'font_family' => "VARCHAR(100) NULL DEFAULT 'Inter'",
					'button_style' => "VARCHAR(50) NULL DEFAULT 'rounded'",
					'store_headline' => "VARCHAR(255) NULL DEFAULT NULL",
					'store_subheadline' => "VARCHAR(500) NULL DEFAULT NULL",
					'favicon' => "VARCHAR(255) NULL DEFAULT NULL",
					'desktop_banner' => "VARCHAR(255) NULL DEFAULT NULL",
					'mobile_banner' => "VARCHAR(255) NULL DEFAULT NULL",
					'instagram_url' => "VARCHAR(500) NULL DEFAULT NULL",
					'facebook_url' => "VARCHAR(500) NULL DEFAULT NULL",
					'tiktok_url' => "VARCHAR(500) NULL DEFAULT NULL",
					'x_url' => "VARCHAR(500) NULL DEFAULT NULL",
					'youtube_url' => "VARCHAR(500) NULL DEFAULT NULL",
					'business_hours' => "TEXT NULL DEFAULT NULL",
					'announcement_bar' => "VARCHAR(500) NULL DEFAULT NULL",
					'announcement_bar_color' => "VARCHAR(20) NULL DEFAULT '#0F172A'",
					'marquee_items' => "TEXT NULL DEFAULT NULL",
					'preview_mode' => "TINYINT(1) NULL DEFAULT 0",
					'preview_theme_id' => "INT NULL DEFAULT NULL",
					'meta_title' => "VARCHAR(255) NULL DEFAULT NULL",
					'meta_description' => "VARCHAR(500) NULL DEFAULT NULL",
					'footer_bg_color' => "VARCHAR(20) NULL DEFAULT '#0F172A'",
					'header_text_color' => "VARCHAR(20) NULL DEFAULT ''",
					'footer_style' => "VARCHAR(50) NULL DEFAULT 'standard'",
					'footer_about_us' => "TEXT NULL DEFAULT NULL",
					'footer_text_color' => "VARCHAR(20) NULL DEFAULT '#94A3B8'",
					'footer_address_url' => "VARCHAR(500) NULL DEFAULT NULL",
					'button_color' => "VARCHAR(20) NULL DEFAULT '#3B82F6'",
					'meta_keywords' => "VARCHAR(255) NULL DEFAULT NULL",
					'google_analytics_id' => "VARCHAR(50) NULL DEFAULT NULL",
					'facebook_pixel_id' => "VARCHAR(50) NULL DEFAULT NULL",
					'require_tracking_consent' => "TINYINT(1) NULL DEFAULT 1",
					'robots_index' => "TINYINT(1) NULL DEFAULT 1",
					'custom_head_scripts' => "TEXT NULL DEFAULT NULL",
					'testimonial_source' => "VARCHAR(20) NULL DEFAULT 'custom'",
					'trust_badges_json' => "TEXT NULL DEFAULT NULL",
					'newsletter_title' => "VARCHAR(255) NULL DEFAULT 'Stay in the Loop'",
					'newsletter_subtitle' => "VARCHAR(500) NULL DEFAULT 'Subscribe for updates, deals and new arrivals.'",
					'instagram_access_token' => "VARCHAR(500) NULL DEFAULT NULL",
					'instagram_username' => "VARCHAR(100) NULL DEFAULT NULL",
					'google_places_api_key' => "VARCHAR(255) NULL DEFAULT NULL",
					'gmb_place_id' => "VARCHAR(100) NULL DEFAULT NULL",
					'sendchamp_json' => "TEXT NULL DEFAULT NULL",
					'city_shipping_enabled' => "TINYINT(1) NULL DEFAULT 0",
					'city_shipping_json' => "TEXT NULL DEFAULT NULL"
				];
				foreach($need as $col => $def){
					if(!in_array($col, $have)){
						$this->db->query("ALTER TABLE db_storefront_settings ADD $col $def");
					}
				}
				$statusCol = $this->db->query("SHOW COLUMNS FROM db_storefront_settings LIKE 'store_status'")->row();
				if($statusCol && strpos($statusCol->Type, 'deactivated') === false){
					$this->db->query("ALTER TABLE db_storefront_settings MODIFY store_status ENUM('active','maintenance','deactivated') DEFAULT 'active'");
				}
			}

			// Omni-channel fields for db_online_orders
			if($this->db->table_exists('db_online_orders')){
				if(!$this->db->field_exists('source_channel', 'db_online_orders')){
					$this->db->query("ALTER TABLE db_online_orders ADD source_channel VARCHAR(20) NULL DEFAULT 'web'");
				}
				if(!$this->db->field_exists('channel_user_id', 'db_online_orders')){
					$this->db->query("ALTER TABLE db_online_orders ADD channel_user_id VARCHAR(50) NULL DEFAULT NULL");
				}
				if(!$this->db->field_exists('confirmation_token', 'db_online_orders')){
					$this->db->query("ALTER TABLE db_online_orders ADD confirmation_token VARCHAR(128) NULL DEFAULT NULL");
				}
				if(!$this->db->field_exists('token_expires_at', 'db_online_orders')){
					$this->db->query("ALTER TABLE db_online_orders ADD token_expires_at DATETIME NULL DEFAULT NULL");
				}
				if(!$this->db->field_exists('stock_adjusted', 'db_online_orders')){
					$this->db->query("ALTER TABLE db_online_orders ADD stock_adjusted TINYINT(1) NOT NULL DEFAULT 0");
				}
				if(!$this->db->field_exists('stock_state', 'db_online_orders')){
					$this->db->query("ALTER TABLE db_online_orders ADD stock_state ENUM('none','reserved','committed','released') NOT NULL DEFAULT 'none' AFTER stock_adjusted");
				}
				if(!$this->db->field_exists('stock_reserved_at', 'db_online_orders')){
					$this->db->query("ALTER TABLE db_online_orders ADD stock_reserved_at DATETIME NULL DEFAULT NULL AFTER stock_state");
				}
				if(!$this->db->field_exists('delivery_quote_pending', 'db_online_orders')){
					$this->db->query("ALTER TABLE db_online_orders ADD delivery_quote_pending TINYINT(1) NOT NULL DEFAULT 0 AFTER delivery_fee");
				}
				if(!$this->db->field_exists('shipping_method', 'db_online_orders')){
					$this->db->query("ALTER TABLE db_online_orders ADD shipping_method VARCHAR(100) NULL DEFAULT NULL");
				}
				if(!$this->db->field_exists('fulfilled_at', 'db_online_orders')){
					$this->db->query("ALTER TABLE db_online_orders ADD fulfilled_at DATETIME NULL DEFAULT NULL");
				}
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
				'city_shipping_enabled' => 0,
				'city_shipping_json' => '',
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
			'background_color' => '',
			'button_color' => '#3B82F6',
			'footer_style' => 'standard',
			'footer_about_us' => '',
			'footer_text_color' => '#94A3B8',
			'footer_address_url' => '',
			'meta_keywords' => '',
			'google_analytics_id' => '',
			'facebook_pixel_id' => '',
			'require_tracking_consent' => 1,
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
		// Drop keys for columns that may not exist yet on un-migrated installs
		foreach(['marquee_items', 'city_shipping_enabled', 'city_shipping_json', 'background_color'] as $col){
			if(isset($data[$col]) && !$this->db->field_exists($col, 'db_storefront_settings')) unset($data[$col]);
		}
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
						'background_color' => '',
						'button_color' => '#3B82F6',
						'footer_style' => 'standard',
						'footer_about_us' => '',
						'footer_text_color' => '#94A3B8',
						'footer_address_url' => '',
						'meta_keywords' => '',
						'google_analytics_id' => '',
						'facebook_pixel_id' => '',
						'require_tracking_consent' => 1,
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

	/**
	 * Storefront listings show sellable top-level items only: standalone
	 * products AND variant parents. Variant children (parent_id set /
	 * child_bit=1) are reachable through their parent's variant selector,
	 * never as separate cards.
	 */
	private function _listedItemsWhere($alias = 'a'){
		return "({$alias}.item_group IS NULL OR {$alias}.item_group IN ('Single','Variants'))"
			. " AND ({$alias}.parent_id IS NULL OR {$alias}.parent_id = 0)"
			. " AND ({$alias}.child_bit IS NULL OR {$alias}.child_bit = 0)";
	}

	/**
	 * Children written by older flows can carry status=NULL; storefront
	 * reads treat NULL as active ONLY for child rows (never for top-level
	 * items, where NULL is ambiguous legacy data).
	 */
	private function _childStatusWhere($alias = 'a'){
		return "({$alias}.status = 1 OR ({$alias}.status IS NULL AND {$alias}.parent_id IS NOT NULL AND {$alias}.parent_id > 0))";
	}

	/**
	 * Give variant parents displayable values derived from their children:
	 * price = lowest child effective price, image = first child image when
	 * the parent has none, stock = sum of child stock. Mutates the rows.
	 */
	private function _decorateVariantParents($rows){
		$parentIds = [];
		foreach($rows as $r){
			if(($r->item_group ?? '') === 'Variants'){ $parentIds[] = (int)$r->id; }
		}
		if(!empty($parentIds)){
			$children = $this->db->select('id, parent_id, item_name, item_image, sales_price, online_price, discount_type, discount, stock')
				->from('db_items')
				->where_in('parent_id', $parentIds)
				->where('publish_online', 1)
				->where('(status = 1 OR status IS NULL)', null, false)
				->where("(not_for_sale IS NULL OR not_for_sale = 0)", null, false)
				->order_by('id', 'asc')
				->get()->result();
			$byParent = [];
			foreach($children as $c){ $byParent[$c->parent_id][] = $c; }
			foreach($rows as $r){
				if(($r->item_group ?? '') !== 'Variants') continue;
				$kids = $byParent[$r->id] ?? [];
				$minPrice = null; $stockSum = 0; $img = null;
				foreach($kids as $c){
					$eff = $this->getProductEffectivePrice($c);
					if($minPrice === null || $eff < $minPrice){ $minPrice = $eff; }
					$stockSum += (int)$c->stock;
					if(!$img && !empty($c->item_image) && file_exists($c->item_image)){ $img = $c->item_image; }
				}
				$r->variant_count = count($kids);
				$r->stock = $stockSum;
				if($minPrice !== null){
					$r->sales_price = $minPrice;
					$r->online_price = null;
					$r->discount_type = null;
					$r->discount = null;
				}
				if(empty($r->item_image) && $img){ $r->item_image = $img; }
			}
		}
		$bundleRows = array_values(array_filter($rows, function($r){ return !empty($r->is_bundle); }));
		if(!empty($bundleRows)){
			$this->load->model('Extras_model', 'extras_model');
			foreach($bundleRows as $bundle){
				$available = $this->extras_model->bundleAvailability($bundle->id, (int)($bundle->store_id ?? get_current_store_id()));
				$bundle->bundle_available = $available;
				$bundle->stock = (int)($available ?? 0);
			}
		}
		return $rows;
	}

	public function getOnlineProducts($storeId = null, $categoryId = null, $search = '', $limit = 50, $offset = 0){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.id, a.store_id, a.item_name, a.item_image, a.item_code, a.description, a.stock, a.alert_qty, a.sales_price, a.online_price, a.discount_type, a.discount, a.status, a.product_type, a.is_new_arrival, a.is_featured, a.is_bundle, a.bundle_pricing, a.item_group, a.parent_id, a.child_bit, b.category_name');
		$this->db->from('db_items a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.publish_online', 1);
		$this->db->where('a.status', 1);
		$this->db->where('a.service_bit', 0);
		$this->db->where("(a.not_for_sale IS NULL OR a.not_for_sale = 0)", null, false);
		$this->db->where($this->_listedItemsWhere('a'), NULL, FALSE);
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
		return $this->_decorateVariantParents($this->db->get()->result());
	}

	public function getFeaturedProducts($storeId = null, $limit = 8){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.id, a.store_id, a.item_name, a.item_image, a.item_code, a.description, a.stock, a.alert_qty, a.sales_price, a.online_price, a.discount_type, a.discount, a.status, a.product_type, a.is_new_arrival, a.is_featured, a.is_bundle, a.bundle_pricing, a.item_group, a.parent_id, a.child_bit, b.category_name');
		$this->db->from('db_items a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.is_featured', 1);
		$this->db->where('a.publish_online', 1);
		$this->db->where('a.status', 1);
		$this->db->where('a.service_bit', 0);
		$this->db->where("(a.not_for_sale IS NULL OR a.not_for_sale = 0)", null, false);
		$this->db->where($this->_listedItemsWhere('a'), NULL, FALSE);
		$this->db->where($this->_expiredWhere('a', $storeId), NULL, FALSE);
		$this->db->order_by('a.id', 'desc');
		$this->db->limit($limit);
		return $this->_decorateVariantParents($this->db->get()->result());
	}

	public function getOnlineProduct($productId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.*, b.category_name');
		$this->db->from('db_items a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.id', $productId);
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.publish_online', 1);
		$this->db->where($this->_childStatusWhere('a'), NULL, FALSE);
		$this->db->where("(a.not_for_sale IS NULL OR a.not_for_sale = 0)", null, false);
		// No listing predicate here: variant parents must be viewable and
		// variant children must stay purchasable (order flow resolves by id).
		$this->db->where($this->_expiredWhere('a', $storeId), NULL, FALSE);
		$product = $this->db->get()->row();
		if($product){ $this->_decorateVariantParents([$product]); }
		return $product;
	}

	public function getProductVariants($productId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.*, b.category_name');
		$this->db->from('db_items a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.parent_id', $productId);
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.publish_online', 1);
		$this->db->where('(a.status = 1 OR a.status IS NULL)', null, false);
		$this->db->where('a.service_bit', 0);
		$this->db->where("(a.not_for_sale IS NULL OR a.not_for_sale = 0)", null, false);
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
		$this->db->where("(a.not_for_sale IS NULL OR a.not_for_sale = 0)", null, false);
		$this->db->where($this->_listedItemsWhere('a'), NULL, FALSE);
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
		$this->db->join('db_items b', "b.category_id=a.id AND b.publish_online=1 AND b.status=1 AND b.service_bit=0 AND (b.not_for_sale IS NULL OR b.not_for_sale = 0) AND " . $this->_listedItemsWhere('b'), 'inner');
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

	/**
	 * Find an order by either its Paystack reference or its order code.
	 * Paystack references for storefront orders are the order_code, set on the
	 * payment row at init time, so the callback must match both columns.
	 */
	public function getOrderByPaymentReference($ref){
		return $this->db->group_start()
			->where('paystack_reference', $ref)
			->or_where('order_code', $ref)
			->group_end()
			->get('db_online_orders')->row();
	}

	/**
	 * Atomically claim the unpaid -> paid transition for an order.
	 * Returns true only for the request that actually performed the
	 * transition, so duplicate/concurrent callbacks cannot re-fulfil.
	 */
	public function claimPaidOrder($orderId, $data = []){
		$data['payment_status'] = 'paid';
		$data['updated_at'] = date('Y-m-d H:i:s');
		$this->db->where('id', $orderId)
			->where('payment_status', 'unpaid')
			->where('order_status', 'pending');
		if($this->db->field_exists('stock_state', 'db_online_orders')){
			$this->db->where_in('stock_state', ['none', 'reserved']);
		}
		$this->db
			->update('db_online_orders', $data);
		return $this->db->affected_rows() === 1;
	}

	public function claimFailedOrder($orderId){
		$data = [
			'payment_status' => 'failed',
			'order_status' => 'cancelled',
			'updated_at' => date('Y-m-d H:i:s'),
		];
		$this->db->where('id', (int)$orderId)
			->where('payment_status', 'unpaid')
			->where('order_status', 'pending');
		if($this->db->field_exists('stock_state', 'db_online_orders')){
			$this->db->where_in('stock_state', ['none', 'reserved']);
		}
		$this->db->update('db_online_orders', $data);
		return $this->db->affected_rows() === 1;
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
		if(!$order){
			return false;
		}
		$this->db->trans_begin();
		// Explicit lifecycle: commit moves 'reserved' (already decremented at
		// order time) or 'none' (legacy order without a reservation) to
		// 'committed'. Only the 'none' path needs to decrement stock.
		$wasReserved = isset($order->stock_state) && $order->stock_state === 'reserved';
		if(isset($order->stock_state)){
			$this->db->where('id', $orderId)
				->where_in('stock_state', ['reserved', 'none'])
				->update('db_online_orders', ['stock_state' => 'committed', 'stock_adjusted' => 1]);
		} else {
			$this->db->where('id', $orderId)
				->where('stock_adjusted', 0)
				->update('db_online_orders', ['stock_adjusted' => 1]);
		}
		if($this->db->affected_rows() !== 1){
			$this->db->trans_rollback();
			return false; // already committed/released — no double mutation
		}
		if($wasReserved){
			$this->db->trans_commit();
			return true; // stock already moved at reservation time
		}
		$draws = [];
		foreach($this->getOrderItems($orderId) as $item){
			if(!in_array($item->item_type, ['product', 'bundle_component'], true)) continue;
			$draws[] = ['item_id' => (int)$item->item_id, 'qty' => (float)$item->qty];
		}
		if(!$this->_writeOrderStockLedger($orderId, $order->store_id, $draws, -1, 'commit') || $this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return false;
		}
		$this->db->trans_commit();
		return true;
	}

	private function _writeOrderStockLedger($orderId, $storeId, array $lines, $direction, $action){
		if(empty($lines)) return true;
		$warehouseId = function_exists('get_store_warehouse_id') ? get_store_warehouse_id() : null;
		if(!$this->db->insert('db_stockadjustment', [
			'store_id' => (int)$storeId,
			'warehouse_id' => $warehouseId,
			'reference_no' => 'ONLINE-' . strtoupper($action) . '-' . (int)$orderId,
			'adjustment_date' => date('Y-m-d'),
			'adjustment_note' => 'Online order ' . $action . ' #' . (int)$orderId,
			'created_date' => date('Y-m-d'),
			'created_time' => date('H:i:s'),
			'created_by' => 'Storefront',
			'system_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
			'system_name' => 'Storefront',
			'status' => 1,
		])) return false;
		$adjustmentId = (int)$this->db->insert_id();
		$itemIds = [];
		foreach($lines as $line){
			$itemId = (int)($line['item_id'] ?? 0);
			$qty = abs((float)($line['qty'] ?? 0));
			if($itemId <= 0 || $qty <= 0) continue;
			if(!$this->db->insert('db_stockadjustmentitems', [
				'store_id' => (int)$storeId,
				'warehouse_id' => $warehouseId,
				'adjustment_id' => $adjustmentId,
				'item_id' => $itemId,
				'adjustment_qty' => $qty * (int)$direction,
				'description' => 'Online order #' . (int)$orderId . ' ' . $action,
				'status' => 1,
			])) return false;
			$itemIds[$itemId] = true;
		}
		$this->load->model('pos_model');
		foreach(array_keys($itemIds) as $itemId){
			if(!$this->pos_model->update_items_quantity($itemId)) return false;
		}
		return true;
	}

	/**
	 * Reserve stock for the items being placed on an order.
	 * Runs inside the caller's transaction. Each physical item is decremented
	 * atomically with a stock>=qty guard so two simultaneous orders cannot
	 * oversell the same unit. The reservation is recorded as
	 * stock_state='reserved' + stock_reserved_at so abandoned online
	 * payments can be expired later. Returns false when any item is short.
	 */
	public function reserveOrderItems($orderId, $items, $allowBackorder = false){
		$reserved = [];
		foreach($items as $item){
			if(($item['item_type'] ?? '') !== 'product') continue;
			$qty = (int)$item['qty'];
			if($qty <= 0) continue;
			$reserved[] = ['item_id' => (int)$item['item_id'], 'qty' => $qty];
		}
		if(empty($reserved)) return true;

		$this->db->trans_begin();
		$order = $this->getOrder($orderId);
		if(!$order){
			$this->db->trans_rollback();
			return false;
		}
		if(isset($order->stock_state)){
			$this->db->where('id', (int)$orderId)->where('stock_state', 'none')
				->update('db_online_orders', [
					'stock_adjusted' => 1,
					'stock_state' => 'reserved',
					'stock_reserved_at' => date('Y-m-d H:i:s'),
				]);
		} else {
			$this->db->where('id', (int)$orderId)->where('stock_adjusted', 0)
				->update('db_online_orders', ['stock_adjusted' => 1]);
		}
		if($this->db->affected_rows() !== 1){
			$this->db->trans_rollback();
			return false;
		}

		foreach($reserved as $r){
			$qty = $r['qty'];
			$this->db->set('stock', 'stock - ' . $qty, false);
			$this->db->where('id', $r['item_id']);
			if(!$allowBackorder){
				$this->db->where('stock >=', $qty);
			}
			$this->db->update('db_items');
			if($this->db->affected_rows() !== 1){
				$this->db->trans_rollback();
				return false;
			}
		}
		if(!$this->_writeOrderStockLedger($orderId, $order->store_id, $reserved, -1, 'reserve') || $this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return false;
		}
		$this->db->trans_commit();
		return true;
	}

	/**
	 * Release stock held by an order. Legal from 'reserved' (cancel/expiry of
	 * an unpaid order) and 'committed' (cancel/refund of a paid order — the
	 * units go back on the shelf). The state transition is claimed atomically
	 * so duplicate callbacks or status updates release exactly once.
	 */
	public function restoreStock($orderId){
		$order = $this->getOrder($orderId);
		if(!$order){
			return false;
		}
		if(isset($order->stock_state) && $order->stock_state === 'none') return true;
		if(!isset($order->stock_state) && empty($order->stock_adjusted)) return true;
		$this->db->trans_begin();
		if(isset($order->stock_state)){
			$this->db->where('id', $orderId)
				->where_in('stock_state', ['reserved', 'committed'])
				->update('db_online_orders', ['stock_state' => 'released', 'stock_adjusted' => 0]);
			if($this->db->affected_rows() !== 1){
				$this->db->trans_rollback();
				return false;
			}
			if(!in_array($order->stock_state, ['reserved', 'committed'], true)){
				$this->db->trans_commit();
				return true; // nothing held — e.g. 'none' orders
			}
		} else {
			$this->db->where('id', $orderId)
				->where('stock_adjusted', 1)
				->update('db_online_orders', ['stock_adjusted' => 0]);
			if($this->db->affected_rows() !== 1){
				$this->db->trans_rollback();
				return false;
			}
		}
		$releaseLines = [];
		foreach($this->getOrderItems($orderId) as $item){
			if(!in_array($item->item_type, ['product', 'bundle_component'], true)) continue;
			$releaseLines[] = ['item_id' => (int)$item->item_id, 'qty' => (float)$item->qty];
		}
		if(!$this->_writeOrderStockLedger($orderId, $order->store_id, $releaseLines, 1, 'release')){
			$this->db->trans_rollback();
			return false;
		}
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return false;
		}
		$this->db->trans_commit();
		return true;
	}

	/**
	 * Expire abandoned reservations so stock cannot be held indefinitely.
	 * Two tiers:
	 *  - paystack (online payment): released after $paystackTtlHours — an
	 *    abandoned checkout is almost certainly dead within a day.
	 *  - whatsapp / pay_on_delivery: released after $offlineTtlHours, but
	 *    only while the order is still 'pending'. Once a merchant confirms
	 *    (order_status moves off pending) the reservation is merchant-
	 *    managed and only releases via explicit cancel/refund.
	 * Returns the number of orders released.
	 */
	public function releaseExpiredReservations($storeId, $paystackTtlHours = 24, $offlineTtlHours = 72){
		$released = 0;
		foreach([['paystack', $paystackTtlHours], ['offline', $offlineTtlHours]] as $tier){
			list($mode, $ttlHours) = $tier;
			$cutoff = date('Y-m-d H:i:s', time() - ($ttlHours * 3600));
			$q = $this->db->where('store_id', $storeId)
				->where('stock_state', 'reserved')
				->where('payment_status', 'unpaid')
				->where('order_status', 'pending')
				->where('stock_reserved_at <', $cutoff);
			if($mode === 'paystack'){
				$q->where('payment_method', 'paystack');
			} else {
				$q->where_in('payment_method', ['whatsapp', 'pay_on_delivery']);
			}
			$stale = $q->get('db_online_orders')->result();
			foreach($stale as $o){
				if(!$this->restoreStock($o->id)){
					continue; // already released/committed elsewhere
				}
				$released++;
				$this->db->where('id', $o->id)->update('db_online_orders', [
					'payment_status' => 'failed',
					'order_status' => 'cancelled',
				]);
				// Dead order — release its coupon claim so usage limits
				// recover (column arrives with migration 4.0.9.69).
				if($this->db->field_exists('order_id', 'db_promotion_usage')){
					$this->db->where('order_id', $o->id)->delete('db_promotion_usage');
				}
			}
		}
		return $released;
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
		if(empty($storeId)) return $stats;

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
		if(empty($storeId)) return [];
		return $this->db->query("SELECT 
			i.item_name, i.item_image, SUM(oi.qty) as total_qty, SUM(oi.total_price) as total_revenue
			FROM db_online_order_items oi
			JOIN db_online_orders o ON o.id=oi.order_id
			JOIN db_items i ON i.id=oi.item_id
			WHERE o.store_id=$storeId AND oi.item_type='product' AND o.status=1 AND (i.not_for_sale IS NULL OR i.not_for_sale = 0)
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

	public function getProductSoldCounts($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		if(empty($storeId)) return [];
		$rows = $this->db->query("SELECT oi.item_id, oi.item_type, SUM(oi.qty) AS sold
			FROM db_online_order_items oi
			INNER JOIN db_online_orders o ON o.id = oi.order_id
			WHERE o.store_id = $storeId AND o.payment_status = 'paid' AND o.status = 1
			GROUP BY oi.item_id, oi.item_type")->result();
		$counts = [];
		foreach($rows as $r){
			$counts[$r->item_id] = ($counts[$r->item_id] ?? 0) + (int)$r->sold;
		}
		return $counts;
	}

	public function getProductEffectivePrice($product){
		if(!is_object($product) && !is_array($product)) return 0.0;
		$prod = (object)$product;
		if(!empty($prod->is_bundle)){
			$this->load->model('Extras_model', 'extras_model');
			return $this->extras_model->bundleUnitPrice($prod, (int)($prod->store_id ?? get_current_store_id()));
		}
		$onlinePrice = isset($prod->online_price) ? (float)$prod->online_price : 0.0;
		$salesPrice = isset($prod->sales_price) ? (float)$prod->sales_price : 0.0;
		$price = $onlinePrice > 0 ? $onlinePrice : $salesPrice;
		$discount = isset($prod->discount) ? (float)$prod->discount : 0.0;
		if($discount > 0){
			$discountType = strtolower((string)($prod->discount_type ?? ''));
			if($discountType === 'percentage'){
				$price = $price - ($price * ($discount / 100));
			} else {
				$price = $price - $discount;
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
		$normalized = $this->_normalizeThemeIndustry($industry_type);
		if(!function_exists('mp_get_business_presets')){
			$this->load->helper('business_profile');
		}
		$presets = function_exists('mp_get_business_presets') ? mp_get_business_presets() : [];
		// Prefer raw business profile industry, then canonical normalization (e.g. healthcare -> pharmacy)
		$lookupKey = isset($presets[$industry_type]) ? $industry_type : (isset($presets[$normalized]) ? $normalized : 'general_retail');
		$preset = $presets[$lookupKey] ?? ($presets['general_retail'] ?? []);
		$baseKey = $preset['theme_key'] ?? 'general_retail';

		$baseTheme = $this->getThemeByKey($baseKey);
		if($baseTheme && !empty($baseTheme->industry)){
			return $this->_normalizeThemeIndustry($baseTheme->industry);
		}

		// Direct theme key match fallback (raw, supports theme_key values like fashion_luxe)
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
			'makeupstudio' => 'beauty',
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
			'perfumery' => 'perfumery',
			'perfume' => 'perfumery',
			'perfumeshop' => 'perfumery',
			'fragrance' => 'perfumery',
			'skincare' => 'skincare',
			'skincarecosmetics' => 'skincare',
			'skincareorganics' => 'skincare',
			'organicskincare' => 'skincare',
			'organiccosmetics' => 'skincare',
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
			// Pharmacy presets (3 world-class designs)
			['theme_key' => 'pharma_clinical', 'theme_name' => 'Pharma Clinical', 'industry' => 'pharmacy', 'description' => 'Medical-grade clinical pharmacy theme with deep trust blues, crisp typography and a professional healthcare-chain aesthetic.', 'default_primary_color' => '#005EB8', 'default_secondary_color' => '#00A86B', 'default_font_family' => 'Inter', 'sort_order' => 3],
			['theme_key' => 'pharma_wellness', 'theme_name' => 'Pharma Wellness', 'industry' => 'pharmacy', 'description' => 'Modern wellness pharmacy theme with soft sage-teal tones, coral accents and a friendly holistic-health aesthetic.', 'default_primary_color' => '#0D9488', 'default_secondary_color' => '#F97066', 'default_font_family' => 'Poppins', 'sort_order' => 4],
			['theme_key' => 'pharma_care', 'theme_name' => 'Pharma Care', 'industry' => 'pharmacy', 'description' => 'Warm community pharmacy theme with deep teal, amber accents and serif typography for a trusted neighbourhood care feel.', 'default_primary_color' => '#0F766E', 'default_secondary_color' => '#D97706', 'default_font_family' => 'Lora', 'sort_order' => 5],
			['theme_key' => 'beauty_luxe', 'theme_name' => 'Beauty Luxe', 'industry' => 'beauty', 'description' => 'Elegant beauty and cosmetics theme with soft aesthetics.', 'default_primary_color' => '#F8A4C8', 'default_secondary_color' => '#D4AF37', 'default_font_family' => 'Playfair Display', 'sort_order' => 6],
			// Fashion presets (3-4 designs per industry)
			['theme_key' => 'urban_fashion', 'theme_name' => 'Urban Editorial', 'industry' => 'fashion', 'description' => 'Bold editorial layout with high-contrast typography and magazine-style hero sections.', 'default_primary_color' => '#111111', 'default_secondary_color' => '#FF3B30', 'default_font_family' => 'Montserrat', 'sort_order' => 7],
			['theme_key' => 'fashion_modern', 'theme_name' => 'Modern Minimal', 'industry' => 'fashion', 'description' => 'Clean, Shopify-style minimal design with generous whitespace, soft cards and refined typography.', 'default_primary_color' => '#0F172A', 'default_secondary_color' => '#6366F1', 'default_font_family' => 'Inter', 'sort_order' => 8],
			['theme_key' => 'fashion_boutique', 'theme_name' => 'Boutique Luxe', 'industry' => 'fashion', 'description' => 'Elegant serif-driven boutique experience with refined gold accents and graceful transitions.', 'default_primary_color' => '#7C2D12', 'default_secondary_color' => '#D4AF37', 'default_font_family' => 'Playfair Display', 'sort_order' => 9],
			['theme_key' => 'fashion_modest', 'theme_name' => 'Modest Studio', 'industry' => 'fashion', 'description' => 'Warm, modest-wear focused storefront with soft neutrals, calm spacing and inclusive imagery.', 'default_primary_color' => '#1F2937', 'default_secondary_color' => '#C2956A', 'default_font_family' => 'Lora', 'sort_order' => 10],
			['theme_key' => 'fashion_luxe', 'theme_name' => 'Fashion Luxe', 'industry' => 'fashion', 'description' => 'Editorial luxury fashion theme with dramatic imagery, refined serif typography, and warm gold accents.', 'default_primary_color' => '#1A1A1A', 'default_secondary_color' => '#C9A961', 'default_font_family' => 'Playfair Display', 'sort_order' => 11],
		['theme_key' => 'tech_hub', 'theme_name' => 'Tech Hub', 'industry' => 'electronics', 'description' => 'Modern electronics and gadgets theme with tech-forward design.', 'default_primary_color' => '#0A2540', 'default_secondary_color' => '#635BFF', 'default_font_family' => 'Inter', 'sort_order' => 12],
			['theme_key' => 'fresh_market', 'theme_name' => 'Fresh Market', 'industry' => 'grocery', 'description' => 'Warm supermarket and grocery theme with organic feel.', 'default_primary_color' => '#2E7D32', 'default_secondary_color' => '#FF6F00', 'default_font_family' => 'Inter', 'sort_order' => 13],
			// Grocery / supermarket presets (3 world-class designs)
			['theme_key' => 'market_fresh', 'theme_name' => 'Market Fresh', 'industry' => 'grocery', 'description' => 'Vibrant produce-forward supermarket theme with fresh green and warm orange accents, Inter typography and quick-add grids.', 'default_primary_color' => '#16A34A', 'default_secondary_color' => '#F97316', 'default_font_family' => 'Inter', 'sort_order' => 14],
			['theme_key' => 'daily_cart', 'theme_name' => 'Daily Cart', 'industry' => 'grocery', 'description' => 'Friendly neighbourhood mini mart and convenience store with deep blue and amber accents, rounded Poppins cards and quick essentials.', 'default_primary_color' => '#2563EB', 'default_secondary_color' => '#F59E0B', 'default_font_family' => 'Poppins', 'sort_order' => 15],
			['theme_key' => 'grocery_plus', 'theme_name' => 'Grocery Plus', 'industry' => 'grocery', 'description' => 'Premium large-format supermarket chain with deep emerald and gold accents, Inter + Lora typography and a refined grocery experience.', 'default_primary_color' => '#047857', 'default_secondary_color' => '#B45309', 'default_font_family' => 'Lora', 'sort_order' => 16],
			['theme_key' => 'food_express', 'theme_name' => 'Food Express', 'industry' => 'restaurant', 'description' => 'Appetizing restaurant and food ordering theme.', 'default_primary_color' => '#D32F2F', 'default_secondary_color' => '#FBC02D', 'default_font_family' => 'Inter', 'sort_order' => 17],
			['theme_key' => 'service_pro', 'theme_name' => 'Service Pro', 'industry' => 'services', 'description' => 'Professional services theme for agencies and consultancies.', 'default_primary_color' => '#1A237E', 'default_secondary_color' => '#00BCD4', 'default_font_family' => 'Inter', 'sort_order' => 18],
			['theme_key' => 'laundry', 'theme_name' => 'Sparkle Laundry', 'industry' => 'laundry', 'description' => 'Clean, fresh laundry and dry cleaning theme for pickup, delivery and wash services.', 'default_primary_color' => '#0EA5E9', 'default_secondary_color' => '#22C55E', 'default_font_family' => 'Inter', 'sort_order' => 19],
			['theme_key' => 'laundry_fresh', 'theme_name' => 'Fresh', 'industry' => 'laundry', 'description' => 'A clean, modern storefront designed for laundries, dry cleaners and garment-care businesses.', 'default_primary_color' => '#102A43', 'default_secondary_color' => '#2F80ED', 'default_font_family' => 'Inter', 'sort_order' => 20],
			['theme_key' => 'online_store', 'theme_name' => 'Online Store', 'industry' => 'general', 'description' => 'A storefront-first theme optimized for e-commerce and digital product catalogues.', 'default_primary_color' => '#7C3AED', 'default_secondary_color' => '#F97316', 'default_font_family' => 'Inter', 'sort_order' => 21],
			['theme_key' => 'wholesale', 'theme_name' => 'Wholesale / B2B', 'industry' => 'wholesale', 'description' => 'Clean B2B wholesale theme designed for distributors, manufacturers and multi-branch operations.', 'default_primary_color' => '#1E3A8A', 'default_secondary_color' => '#10B981', 'default_font_family' => 'Inter', 'sort_order' => 22],
			['theme_key' => 'hardware', 'theme_name' => 'Hardware & Materials', 'industry' => 'hardware', 'description' => 'Industrial hardware and building materials theme with strong, reliable typography.', 'default_primary_color' => '#374151', 'default_secondary_color' => '#F59E0B', 'default_font_family' => 'Inter', 'sort_order' => 23],
			['theme_key' => 'agro', 'theme_name' => 'Agro Inputs', 'industry' => 'agro', 'description' => 'Agricultural inputs theme for agro dealers and feed stores with earthy, natural tones.', 'default_primary_color' => '#166534', 'default_secondary_color' => '#A16207', 'default_font_family' => 'Inter', 'sort_order' => 24],
			['theme_key' => 'automotive', 'theme_name' => 'Automotive Parts', 'industry' => 'automotive', 'description' => 'Automotive parts and tyre shop theme with bold, mechanical styling.', 'default_primary_color' => '#111827', 'default_secondary_color' => '#EF4444', 'default_font_family' => 'Inter', 'sort_order' => 25],
			['theme_key' => 'auto_modern', 'theme_name' => 'Auto Modern', 'industry' => 'automotive', 'description' => 'Clean, world-class car dealership theme with a blue and white hero, fast minified images, mobile-first grids and WhatsApp leads.', 'default_primary_color' => '#2563EB', 'default_secondary_color' => '#0B1220', 'default_font_family' => 'Inter', 'sort_order' => 26],
			['theme_key' => 'auto_luxe', 'theme_name' => 'Auto Luxe', 'industry' => 'automotive', 'description' => 'Dark, premium luxury vehicle theme with gold accents, dramatic hero, and a premium buying experience.', 'default_primary_color' => '#C9A961', 'default_secondary_color' => '#0B0F1A', 'default_font_family' => 'Inter', 'sort_order' => 27],
			['theme_key' => 'auto_garage', 'theme_name' => 'Auto Garage', 'industry' => 'automotive', 'description' => 'Rugged, high-energy auto theme for trucks, SUVs and performance vehicles with bold red and charcoal styling.', 'default_primary_color' => '#DC2626', 'default_secondary_color' => '#1F2937', 'default_font_family' => 'Inter', 'sort_order' => 28],
			// Creator / digital store themes (3 modern templates)
			['theme_key' => 'creator_focus', 'theme_name' => 'Creator Focus', 'industry' => 'creator', 'description' => 'Dark, modern digital storefront with purple-pink gradients and neon accents. Ideal for courses, ebooks and downloads.', 'default_primary_color' => '#7C3AED', 'default_secondary_color' => '#EC4899', 'default_font_family' => 'Inter', 'sort_order' => 29],
			['theme_key' => 'creator_bold', 'theme_name' => 'Creator Bold', 'industry' => 'creator', 'description' => 'High-contrast black and orange creative theme. Bold, editorial and built for selling digital products and memberships.', 'default_primary_color' => '#FF4D00', 'default_secondary_color' => '#FFD700', 'default_font_family' => 'Inter', 'sort_order' => 30],
			['theme_key' => 'creator_studio', 'theme_name' => 'Creator Studio', 'industry' => 'creator', 'description' => 'Warm, light and elegant studio theme with terracotta and cream. Perfect for coaches, creators and course creators.', 'default_primary_color' => '#C75D3A', 'default_secondary_color' => '#E4A15A', 'default_font_family' => 'Inter', 'sort_order' => 31],
			// Perfumery presets (4 world-class luxury designs, WhatsApp-first)
			['theme_key' => 'noir_parfum', 'theme_name' => 'Noir Parfum', 'industry' => 'perfumery', 'description' => 'Midnight luxury flagship theme — deep black, champagne gold and italic serif typography for a dramatic haute-parfumerie storefront.', 'default_primary_color' => '#C9A961', 'default_secondary_color' => '#0B0A08', 'default_font_family' => 'Cormorant Garamond', 'sort_order' => 32],
			['theme_key' => 'maison_blanche', 'theme_name' => 'Maison Blanche', 'industry' => 'perfumery', 'description' => 'Ivory Parisian maison theme — cream canvas, black ink and old-gold hairlines for a refined French fragrance boutique.', 'default_primary_color' => '#A98954', 'default_secondary_color' => '#1C1917', 'default_font_family' => 'Playfair Display', 'sort_order' => 33],
			['theme_key' => 'oud_royale', 'theme_name' => 'Oud Royale', 'industry' => 'perfumery', 'description' => 'Arabian opulence theme — espresso darkness, royal gold and arched gallery for oud, attar and musk houses.', 'default_primary_color' => '#D4A24E', 'default_secondary_color' => '#150E07', 'default_font_family' => 'Marcellus', 'sort_order' => 34],
			['theme_key' => 'atelier_essence', 'theme_name' => 'Atelier Essence', 'industry' => 'perfumery', 'description' => 'Niche-lab minimalism — bone white, mono ink and stark grid for artisan perfumeries and custom formulation labs.', 'default_primary_color' => '#161513', 'default_secondary_color' => '#9C4A2F', 'default_font_family' => 'Inter', 'sort_order' => 35],
			// Beauty / makeup studio presets (3 world-class designs)
			['theme_key' => 'glam_atelier', 'theme_name' => 'Glam Atelier', 'industry' => 'beauty', 'description' => 'Editorial makeup-studio flagship — porcelain canvas, espresso ink, rose-gold hairlines and Cormorant serif for a premium artist brand.', 'default_primary_color' => '#B76E79', 'default_secondary_color' => '#241B18', 'default_font_family' => 'Cormorant Garamond', 'sort_order' => 36],
			['theme_key' => 'velvet_glow', 'theme_name' => 'Velvet Glow', 'industry' => 'beauty', 'description' => 'Warm velvet beauty theme — deep berry, blush silk and plush glowing cards for salons and cosmetics boutiques.', 'default_primary_color' => '#8E3B5E', 'default_secondary_color' => '#E9B8C4', 'default_font_family' => 'Playfair Display', 'sort_order' => 37],
			['theme_key' => 'studio_blanc', 'theme_name' => 'Studio Blanc', 'industry' => 'beauty', 'description' => 'Clean ivory minimalism — crisp black ink, terracotta accents and airy product grids for modern beauty retail.', 'default_primary_color' => '#111111', 'default_secondary_color' => '#C98A6B', 'default_font_family' => 'Jost', 'sort_order' => 38],
			// Skincare / organic cosmetics presets (3 world-class designs)
			['theme_key' => 'botanica', 'theme_name' => 'Botanica', 'industry' => 'skincare', 'description' => 'Editorial botanical flagship — warm cream canvas, forest ink, sage accents and arched product imagery for organic, made-from-scratch skincare brands.', 'default_primary_color' => '#4A7C59', 'default_secondary_color' => '#22302A', 'default_font_family' => 'Fraunces', 'sort_order' => 39],
			['theme_key' => 'derma_pure', 'theme_name' => 'Derma Pure', 'industry' => 'skincare', 'description' => 'Clinical minimal lab theme — crisp white, ink and derma-teal with mono labels for science-led skincare and formulation brands.', 'default_primary_color' => '#2F6B5E', 'default_secondary_color' => '#0F172A', 'default_font_family' => 'Inter', 'sort_order' => 40],
			['theme_key' => 'terra_glow', 'theme_name' => 'Terra Glow', 'industry' => 'skincare', 'description' => 'Warm earth-luxe theme — sand canvas, clay ink and terracotta accents with plush rounded cards for shea, butter and glow-focused brands.', 'default_primary_color' => '#B5643C', 'default_secondary_color' => '#31221A', 'default_font_family' => 'Cormorant Garamond', 'sort_order' => 41],
			['theme_key' => 'verdant', 'theme_name' => 'Verdant', 'industry' => 'skincare', 'description' => 'Calm editorial flagship — cream canvas, deep forest bands, sage accents and serif typography with concern-led shopping, journal and routine sets, all driven by the Online Store backend.', 'default_primary_color' => '#4F7A5C', 'default_secondary_color' => '#1F3A2E', 'default_font_family' => 'Instrument Serif', 'sort_order' => 42],
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
			'pharma_clinical' => 'pharmacy',
			'pharma_wellness' => 'pharmacy',
			'pharma_care' => 'pharmacy',
			'beauty_luxe' => 'beauty',
			'urban_fashion' => 'fashion',
			'fashion_modern' => 'fashion',
			'fashion_boutique' => 'fashion',
			'fashion_modest' => 'fashion',
			'fashion_luxe' => 'fashion',
			'tech_hub' => 'electronics',
			'fresh_market' => 'grocery',
			'market_fresh' => 'grocery',
			'daily_cart' => 'grocery',
			'grocery_plus' => 'grocery',
			'food_express' => 'restaurant',
			'service_pro' => 'services',
			'laundry' => 'laundry',
			'laundry_fresh' => 'laundry',
			'online_store' => 'general',
			'noir_parfum' => 'perfumery',
			'maison_blanche' => 'perfumery',
			'oud_royale' => 'perfumery',
			'atelier_essence' => 'perfumery',
			'glam_atelier' => 'beauty',
			'velvet_glow' => 'beauty',
			'studio_blanc' => 'beauty',
			'botanica' => 'skincare',
			'derma_pure' => 'skincare',
			'terra_glow' => 'skincare',
			'verdant' => 'skincare',
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

	/**
	 * Save a section's custom title/subtitle (themes read these via config_json).
	 */
	public function saveHomepageSectionMeta($storeId, $sectionKey, $title, $subtitle){
		$storeId = $storeId ?: get_current_store_id();
		$row = $this->db->where('store_id', $storeId)->where('section_key', $sectionKey)->get('db_storefront_homepage_sections')->row();
		if(!$row) return false;

		$cfg = [];
		if(!empty($row->config_json)){
			$cfg = json_decode($row->config_json, true);
			if(!is_array($cfg)) $cfg = [];
		}
		if($title !== '') $cfg['title'] = $title; else unset($cfg['title']);
		if($subtitle !== '') $cfg['subtitle'] = $subtitle; else unset($cfg['subtitle']);

		$data = ['config_json' => json_encode($cfg)];
		if($title !== ''){
			$data['section_label'] = $title;
		} else {
			// Cleared title: restore the default label for this key (incl. duplicated keys)
			$baseKey = preg_replace('/_\d+$/', '', $sectionKey);
			foreach($this->homepageSectionDefaults() as $d){
				if($d[0] === $baseKey){
					$label = $d[1];
					if(preg_match('/_(\d+)$/', $sectionKey, $m)) $label .= ' (' . $m[1] . ')';
					$data['section_label'] = $label;
					break;
				}
			}
		}
		return $this->db->where('id', $row->id)->update('db_storefront_homepage_sections', $data);
	}

	private function homepageSectionDefaults(){
		return [
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
	}

	public function resetHomepageSections($storeId){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->where('store_id', $storeId)->delete('db_storefront_homepage_sections');
		$defaults = $this->homepageSectionDefaults();
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

	public function deleteHomepageSection($storeId, $sectionKey){
		$storeId = $storeId ?: get_current_store_id();
		// Only duplicated copies (_N suffix) may be deleted; base sections are toggle-only
		if(!preg_match('/_\d+$/', (string)$sectionKey)) return false;
		return $this->db->where('store_id', $storeId)->where('section_key', $sectionKey)->delete('db_storefront_homepage_sections');
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
		$domain = strtolower(trim((string)$domain));
		$domain = rtrim(preg_replace('/:\d+$/', '', $domain), '.');
		if($domain === '') return null;
		$candidates = [$domain];
		$candidates[] = (strpos($domain, 'www.') === 0) ? substr($domain, 4) : 'www.'.$domain;
		return $this->db->where_in('domain_value', $candidates)->where('connection_status', 'connected')->get('db_storefront_domains')->row();
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
		$expiryClause = $this->_expiredWhere('i2', $storeId);
		// Orders store the purchased child item; roll children up to their
		// variant parent so best sellers rank real products, not SKU rows.
		$rows = $this->db->query("SELECT i2.id, i2.store_id, i2.item_name, i2.item_image, i2.sales_price, i2.online_price, i2.discount_type, i2.discount, i2.stock, i2.description, i2.product_type, i2.is_new_arrival, i2.is_featured, i2.is_bundle, i2.bundle_pricing, i2.item_group, i2.parent_id, i2.child_bit, SUM(oi.qty) as sold_count
			FROM db_online_order_items oi
			JOIN db_online_orders o ON o.id=oi.order_id
			JOIN db_items i ON i.id=oi.item_id
			JOIN db_items i2 ON i2.id = COALESCE(NULLIF(i.parent_id,0), i.id)
			WHERE o.store_id=? AND oi.item_type IN ('product','digital','course','membership') AND o.status=1 AND i2.publish_online=1 AND (i2.not_for_sale IS NULL OR i2.not_for_sale = 0) AND (i2.item_group IS NULL OR i2.item_group IN ('Single','Variants')) AND (i2.parent_id IS NULL OR i2.parent_id = 0) AND (i2.child_bit IS NULL OR i2.child_bit = 0) AND $expiryClause
			GROUP BY i2.id
			ORDER BY sold_count DESC
			LIMIT ?", [$storeId, $limit])->result();
		return $this->_decorateVariantParents($rows);
	}

	public function getNewArrivals($storeId = null, $limit = 8){
		$storeId = $storeId ?: get_current_store_id();
		// Prefer manually flagged "New Arrival" products (is_new_arrival=1).
		// Fall back to most recently added published products if none are flagged.
		$buildQuery = function($storeId, $limit, $flaggedOnly) {
			$this->db->select('a.id, a.store_id, a.item_name, a.item_image, a.sales_price, a.online_price, a.discount_type, a.discount, a.stock, a.description, a.product_type, a.is_new_arrival, a.is_featured, a.is_bundle, a.bundle_pricing, a.item_group, a.parent_id, a.child_bit, b.category_name');
			$this->db->from('db_items a');
			$this->db->join('db_category b', 'b.id=a.category_id', 'left');
			$this->db->where('a.store_id', $storeId);
			$this->db->where('a.publish_online', 1);
			$this->db->where('a.status', 1);
			$this->db->where('a.service_bit', 0);
			$this->db->where("(a.not_for_sale IS NULL OR a.not_for_sale = 0)", null, false);
			$this->db->where($this->_listedItemsWhere('a'), NULL, FALSE);
			if($flaggedOnly){
				$this->db->where('a.is_new_arrival', 1);
			}
			$this->db->where($this->_expiredWhere('a', $storeId), NULL, FALSE);
			$this->db->order_by('a.id', 'desc');
			$this->db->limit($limit);
			return $this->_decorateVariantParents($this->db->get()->result());
		};
		// Try flagged items first
		$results = $buildQuery($storeId, $limit, true);
		if(!empty($results)){
			return $results;
		}
		// Fallback: most recent published products
		return $buildQuery($storeId, $limit, false);
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
			$rows = $this->db->get('db_storefront_testimonials')->result();
			// Clinical (physio) testimonials — separate consent + moderation
			// pipeline. Only approved, consented, non-withdrawn rows surface,
			// and withdrawal removes them on the very next query.
			if($this->db->table_exists('db_patient_testimonials')){
				$pt = $this->db->select('t.id,t.body,t.rating,t.display_mode,t.display_name,c.customer_name')
					->from('db_patient_testimonials t')
					->join('db_patients p','p.id = t.patient_id','left')
					->join('db_customers c','c.id = p.customer_id','left')
					->where('t.store_id',$storeId)->where('t.status','approved')
					->where('t.publish_consent',1)->get()->result();
				foreach($pt as $t){
					$name = 'Verified patient';
					if($t->display_mode === 'first_name' && $t->customer_name) $name = strtok($t->customer_name, ' ');
					elseif($t->display_mode === 'custom' && $t->display_name) $name = $t->display_name;
					elseif($t->display_mode === 'anonymous') $name = 'Anonymous patient';
					$rows[] = (object)array(
						'id' => 'pt'.$t->id, 'customer_name' => $name,
						'testimonial_text' => $t->body, 'rating' => $t->rating ?: 5,
						'customer_photo' => null,
					);
				}
			}
			return $rows;
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

	// ============== NEWSLETTER SUBSCRIBERS ==============

	public function saveNewsletterSubscriber($data){
		try{
			$exists = $this->db->where('store_id', $data['store_id'])->where('email', $data['email'])->get('db_newsletter_subscribers')->row();
			if($exists){
				return $exists->id;
			}
			$res = $this->db->insert('db_newsletter_subscribers', $data);
			return $res ? $this->db->insert_id() : false;
		} catch(Exception $e){ return false; }
	}

	public function getNewsletterSubscribers($storeId = null, $search = ''){
		try{
			$storeId = $storeId ?: get_current_store_id();
			$this->db->where('store_id', $storeId);
			if($search !== '') $this->db->like('email', $search);
			return $this->db->order_by('id', 'desc')->get('db_newsletter_subscribers')->result();
		} catch(Exception $e){ return []; }
	}

	public function countNewsletterSubscribers($storeId = null){
		try{
			$storeId = $storeId ?: get_current_store_id();
			return (int)$this->db->where('store_id', $storeId)->where('status', 1)->count_all_results('db_newsletter_subscribers');
		} catch(Exception $e){ return 0; }
	}

	public function deleteNewsletterSubscriber($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', $id)->where('store_id', $storeId)->delete('db_newsletter_subscribers');
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

	public function deliverDigitalOrder($orderId){
		$order = $this->getOrder($orderId);
		if(!$order || $order->payment_status !== 'paid') return false;
		$items = $this->getOrderItems($orderId);
		$hasDigital = false;
		$allDigital = true;
		foreach($items as $item){
			if($item->item_type !== 'digital') $allDigital = false;
			if($item->item_type === 'digital') $hasDigital = true;
		}
		if(!$hasDigital) return false;
		$now = date('Y-m-d H:i:s');
		foreach($items as $item){
			if($item->item_type !== 'digital') continue;
			$product = $this->db->where('id', $item->item_id)->get('db_items')->row();
			if(!$product || empty($product->digital_file)) continue;
			$hours = (int)($product->download_expiry_hours ?? 72);
			$token = md5($item->id . '_' . $item->item_id . '_' . time() . '_' . rand(1000,9999));
			$this->db->where('id', $item->id)->update('db_online_order_items', [
				'download_token' => $token,
				'download_count' => 0,
				'download_expires_at' => date('Y-m-d H:i:s', strtotime("+$hours hours", strtotime($now)))
			]);
		}
		if($allDigital){
			$this->db->where('id', $orderId)->update('db_online_orders', ['order_status' => 'completed']);
		}
		return true;
	}

	public function deliverCourseAndMembership($orderId){
		$order = $this->getOrder($orderId);
		if(!$order || $order->payment_status !== 'paid' || empty($order->customer_id)) return false;

		$this->load->model('course_model');
		$this->load->model('creator_membership_model', 'membership_model');

		$items = $this->getOrderItems($orderId);
		foreach($items as $item){
			if($item->item_type === 'course'){
				$course = $this->course_model->getByItemId($item->item_id, $order->store_id);
				if($course){
					$this->course_model->enrollCustomer($order->customer_id, $course->id, $item->item_id, $order->id, $order->store_id);
				}
			} elseif($item->item_type === 'membership'){
				$membership = $this->membership_model->getByItemId($item->item_id, $order->store_id);
				if($membership){
					$this->membership_model->createSubscription($order->customer_id, $membership->id, $item->item_id, $order->id, $order->store_id);
				}
			}
		}
		return true;
	}

	public function completeIfNoPhysicalProducts($orderId){
		$items = $this->getOrderItems($orderId);
		foreach($items as $item){
			if($item->item_type === 'product') return false;
		}
		$this->db->where('id', $orderId)->update('db_online_orders', ['order_status' => 'completed']);
		return true;
	}

	/**
	 * Run paid-order fulfilment (digital delivery, course/membership
	 * enrolment, auto-complete) and stamp fulfilled_at on success.
	 * Each step is idempotent, so this is safe to re-run: if the request
	 * that claimed the unpaid->paid transition died mid-fulfilment, any
	 * later callback/verify/admin action calls this again until it sticks.
	 * Returns true once fulfilled, false while any step still fails.
	 */
	public function fulfilPaidOrder($orderId){
		$order = $this->getOrder($orderId);
		if(!$order || $order->payment_status !== 'paid' || (isset($order->stock_state) && $order->stock_state === 'released')){
			return false;
		}
		if(!empty($order->fulfilled_at)){
			return true;
		}
		try {
			$this->deliverDigitalOrder($orderId);
			$this->deliverCourseAndMembership($orderId);
			$this->completeIfNoPhysicalProducts($orderId);
		} catch (Exception $e) {
			log_message('error', "fulfilPaidOrder failed for order {$orderId}: " . $e->getMessage());
			return false;
		}
		$this->db->where('id', $orderId)->update('db_online_orders', ['fulfilled_at' => date('Y-m-d H:i:s')]);
		return true;
	}

	// ============== SHOPPING EVENTS (first-party, consent-gated) ==============

	/**
	 * Record a storefront shopping event. The caller asserts consent; the
	 * consented flag is stored so diagnostics can distinguish gated traffic.
	 */
	public function recordEvent($storeId, array $data){
		if(!$this->db->table_exists('db_storefront_events')) return false;
		$allowed = array('view_item','add_to_cart','begin_checkout','order_placed','purchase');
		if(!in_array($data['event_type'] ?? '', $allowed, true)) return false;
		$eventId = substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)($data['event_id'] ?? '')), 0, 64);
		// Deduplicate: the same logical event may arrive from the browser and
		// the server (or twice via retries). event_id makes it idempotent.
		if($eventId !== '' && $this->db->field_exists('event_id', 'db_storefront_events')){
			$dupe = $this->db->where('store_id', (int)$storeId)
				->where('event_type', $data['event_type'])
				->where('event_id', $eventId)
				->count_all_results('db_storefront_events');
			if($dupe) return 'duplicate';
		}
		return $this->db->insert('db_storefront_events', array(
			'store_id'   => (int)$storeId,
			'session_id' => substr((string)($data['session_id'] ?? ''), 0, 100) ?: null,
			'event_type' => $data['event_type'],
			'event_id'   => $eventId !== '' ? $eventId : null,
			'item_id'    => !empty($data['item_id']) ? (int)$data['item_id'] : null,
			'order_id'   => !empty($data['order_id']) ? (int)$data['order_id'] : null,
			'value'      => isset($data['value']) ? (float)$data['value'] : null,
			'meta'       => isset($data['meta']) ? substr(is_string($data['meta']) ? $data['meta'] : json_encode($data['meta']), 0, 5000) : null,
			'consented'  => !empty($data['consented']) ? 1 : 0,
		));
	}

	/**
	 * Record the paid conversion for an order. Called only from verified
	 * payment transitions (provider verification or merchant-confirmed
	 * payment) — order creation alone never emits this. The event_id is
	 * stable per order so retries, duplicate webhooks and a browser-side
	 * confirmation all collapse to one record.
	 */
	public function recordPurchaseEvent($order, $source = 'provider'){
		if(!$this->db->table_exists('db_storefront_events')) return false;
		if(is_numeric($order)) $order = $this->getOrder((int)$order);
		if(!$order) return false;
		return $this->recordEvent($order->store_id, array(
			'event_type' => 'purchase',
			'event_id'   => 'purchase_' . $order->order_code,
			'order_id'   => (int)$order->id,
			'value'      => (float)$order->grand_total,
			'session_id' => '',
			'consented'  => 1,
			'meta'       => json_encode(array('source' => $source, 'payment_method' => $order->payment_method ?? null)),
		));
	}

	/**
	 * Funnel counts per event type over a window — powers the diagnostics
	 * panel on the analytics page.
	 */
	public function getEventFunnel($storeId, $days = 30){
		if(!$this->db->table_exists('db_storefront_events')) return array();
		$start = date('Y-m-d 00:00:00', strtotime('-'.(int)$days.' days'));
		$rows = $this->db->select('event_type, COUNT(*) AS hits, SUM(consented) AS consented_hits')
			->where('store_id', (int)$storeId)->where('created_at >=', $start)
			->group_by('event_type')->get('db_storefront_events')->result();
		$out = array();
		foreach($rows as $r){ $out[$r->event_type] = $r; }
		return $out;
	}

	public function getRecentEvents($storeId, $limit = 25){
		if(!$this->db->table_exists('db_storefront_events')) return array();
		return $this->db->where('store_id', (int)$storeId)
			->order_by('id', 'desc')->limit((int)$limit)
			->get('db_storefront_events')->result();
	}

	// ============== PERSISTED CARTS ==============

	/**
	 * Upsert a storefront cart snapshot keyed by (store_id, cart_token).
	 * Returns the cart row id or false.
	 */
	public function saveCart($storeId, $token, array $data){
		if(!$this->db->table_exists('db_storefront_carts')) return false;
		// Only accept client tokens in the crypto-random format the storefront
		// issues (32+ lowercase hex chars from crypto.getRandomValues). Weak
		// legacy/fallback tokens are rejected so the client regenerates.
		if(!preg_match('/^[0-9a-f]{32,64}$/', (string)$token)) return false;
		$token = (string)$token;
		$row = array(
			'customer_name'  => substr((string)($data['customer_name'] ?? ''), 0, 191) ?: null,
			'customer_phone' => substr((string)($data['customer_phone'] ?? ''), 0, 50) ?: null,
			'customer_email' => substr((string)($data['customer_email'] ?? ''), 0, 191) ?: null,
			'items_json'     => isset($data['items_json']) ? (string)$data['items_json'] : null,
			'subtotal'       => isset($data['subtotal']) ? (float)$data['subtotal'] : null,
			'coupon_code'    => substr((string)($data['coupon_code'] ?? ''), 0, 64) ?: null,
			'session_id'     => substr((string)($data['session_id'] ?? ''), 0, 100) ?: null,
			'last_activity'  => date('Y-m-d H:i:s'),
		);
		// Rolling expiry — recovery links die 7 days after last activity.
		if($this->db->field_exists('expires_at', 'db_storefront_carts')){
			$row['expires_at'] = date('Y-m-d H:i:s', time() + 7 * 86400);
		}
		// Keep merchant decision flags (status/reminders) on update.
		$existing = $this->db->where('store_id', (int)$storeId)->where('cart_token', $token)->get('db_storefront_carts')->row();
		if($existing){
			if(in_array($existing->status, array('ordered'), true)) return (int)$existing->id;
			$this->db->where('id', $existing->id)->update('db_storefront_carts', $row);
			return (int)$existing->id;
		}
		$row['store_id'] = (int)$storeId;
		$row['cart_token'] = $token;
		$row['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert('db_storefront_carts', $row);
		return (int)$this->db->insert_id();
	}

	public function getCartByToken($storeId, $token){
		if(!$this->db->table_exists('db_storefront_carts')) return null;
		if(!preg_match('/^[0-9a-f]{32,64}$/', (string)$token)) return null;
		return $this->db->where('store_id', (int)$storeId)->where('cart_token', (string)$token)
			->get('db_storefront_carts')->row();
	}

	/** Merchant-side cart fetch by id (contact details allowed here). */
	public function getCartById($cartId, $storeId){
		if(!$this->db->table_exists('db_storefront_carts')) return null;
		return $this->db->where('id', (int)$cartId)->where('store_id', (int)$storeId)
			->get('db_storefront_carts')->row();
	}

	/**
	 * Public restoration payload — deliberately excludes saved contact fields
	 * (name/phone/email stay merchant-side) and revalidates every item
	 * against the live catalogue so stale prices, removed products and
	 * out-of-stock lines never reach the order form.
	 */
	public function getRestorableCart($storeId, $token){
		$cart = $this->getCartByToken($storeId, $token);
		if(!$cart || $cart->status === 'ordered') return null;
		$now = date('Y-m-d H:i:s');
		$expiry = !empty($cart->expires_at) ? $cart->expires_at : date('Y-m-d H:i:s', strtotime($cart->created_at . ' +7 days'));
		if($expiry <= $now) return null;
		$items = json_decode((string)$cart->items_json, true);
		if(!is_array($items)) $items = array();
		return $this->revalidateCartItems($storeId, $items);
	}

	/**
	 * Re-resolve every saved cart line against current catalogue data:
	 * live effective price, current stock, availability. Returns
	 * ['items' => restored lines, 'dropped' => names that can't be restored].
	 */
	public function revalidateCartItems($storeId, array $items){
		$settings = $this->getSettings($storeId);
		$out = array('items' => array(), 'dropped' => array());
		foreach(array_slice($items, 0, 100) as $item){
			$id  = (int)($item['id'] ?? 0);
			$qty = max(1, (int)($item['qty'] ?? 1));
			$type = ($item['type'] ?? 'product') === 'service' ? 'service' : 'product';
			if(!$id) continue;
			if($type === 'service'){
				$service = $this->getOnlineService($id, $storeId);
				if(!$service){ $out['dropped'][] = $item['name'] ?? ('#'.$id); continue; }
				$out['items'][] = array('key' => 'service_'.$id, 'id' => $id, 'type' => 'service',
					'name' => $service->service_name, 'price' => (float)$this->getServiceEffectivePrice($service),
					'image' => $service->service_image, 'qty' => $qty, 'stock' => 999);
			} else {
				$product = $this->getOnlineProduct($id, $storeId);
				// Variant parents are shells — the saved child id is what restores.
				if(!$product || ($product->item_group ?? '') === 'Variants'){ $out['dropped'][] = $item['name'] ?? ('#'.$id); continue; }
				$price = (float)$this->getProductEffectivePrice($product);
				$stock = (int)($product->stock ?? 0);
				if(($product->product_type ?? 'physical') === 'physical' && $stock <= 0 && empty($settings->allow_backorder)){
					$out['dropped'][] = $product->item_name; continue;
				}
				$out['items'][] = array('key' => 'product_'.$id, 'id' => $id, 'type' => 'product',
					'name' => $product->item_name, 'price' => $price,
					'image' => $product->item_image, 'qty' => $qty, 'stock' => $stock);
			}
		}
		return $out;
	}

	/** Customer opt-out — suppresses the cart from all recovery surfaces. */
	public function optOutCart($storeId, $token){
		if(!$this->db->field_exists('opt_out', 'db_storefront_carts')) return false;
		$cart = $this->getCartByToken($storeId, $token);
		if(!$cart) return false;
		$this->db->where('id', $cart->id)->update('db_storefront_carts', array('opt_out' => 1));
		return true;
	}

	/**
	 * Mark a persisted cart converted once its order is placed. Called from
	 * place_order after the order transaction commits.
	 */
	public function markCartOrdered($storeId, $token, $orderId){
		$cart = $this->getCartByToken($storeId, $token);
		if(!$cart || $cart->status === 'ordered') return;
		$this->db->where('id', $cart->id)->update('db_storefront_carts', array(
			'status' => 'ordered', 'order_id' => (int)$orderId,
		));
	}

	/**
	 * Abandoned carts: still active, stale beyond the merchant's threshold,
	 * and carrying some way to reach the customer. Reminder suppression keeps
	 * carts off the list once nudged 3+ times.
	 */
	public function getAbandonedCarts($storeId, $hours = 24, $limit = 100){
		if(!$this->db->table_exists('db_storefront_carts')) return array();
		$cutoff = date('Y-m-d H:i:s', time() - max(1, (int)$hours) * 3600);
		$this->db->where('store_id', (int)$storeId)
			->where('status', 'active')
			->where('last_activity <', $cutoff)
			->where('items_json IS NOT NULL', null, false)
			->where('(customer_phone IS NOT NULL OR customer_email IS NOT NULL)', null, false)
			->where('reminder_count <', 3);
		if($this->db->field_exists('opt_out', 'db_storefront_carts')){
			$this->db->where('opt_out', 0);
		}
		return $this->db->order_by('last_activity', 'desc')->limit((int)$limit)
			->get('db_storefront_carts')->result();
	}

	/**
	 * Cross-store abandoned carts for the automated-recovery cron. Same
	 * rules as the merchant list; caller still re-checks eligibility per
	 * cart at send time.
	 */
	public function getAbandonedCartsForRecovery($hours = 24, $limit = 500){
		if(!$this->db->table_exists('db_storefront_carts')) return array();
		$cutoff = date('Y-m-d H:i:s', time() - max(1, (int)$hours) * 3600);
		$this->db->where('status', 'active')
			->where('last_activity <', $cutoff)
			->where('items_json IS NOT NULL', null, false)
			->where('(customer_phone IS NOT NULL OR customer_email IS NOT NULL)', null, false)
			->where('reminder_count <', 3);
		if($this->db->field_exists('opt_out', 'db_storefront_carts')){
			$this->db->where('opt_out', 0);
		}
		return $this->db->order_by('last_activity', 'desc')->limit((int)$limit)
			->get('db_storefront_carts')->result();
	}

	/**
	 * Re-check a cart's recovery eligibility at nudge time — not just list
	 * time. Returns true only when the cart is still active, not opted out,
	 * under the reminder cap, and outside the 24h spacing window.
	 */
	public function cartReminderEligible($cart, $hours = 24){
		if(!$cart || $cart->status !== 'active') return false;
		if(!empty($cart->opt_out)) return false; // column absent pre-.73 → field never set → falsy, fine

		if((int)$cart->reminder_count >= 3) return false;
		if(empty($cart->customer_phone) && empty($cart->customer_email)) return false;
		$now = time();
		if(strtotime((string)$cart->last_activity) > $now - max(1, (int)$hours) * 3600) return false;
		if(!empty($cart->reminder_sent_at) && strtotime((string)$cart->reminder_sent_at) > $now - 86400) return false;
		return true;
	}

	/**
	 * Atomically claim the next reminder slot and write the audit row.
	 * Returns false when another request already claimed the slot or the
	 * cart no longer qualifies, so a nudge can never be double-counted.
	 */
	public function claimCartReminder($cartId, $storeId, $channel, $mode, $recipient, $detail, $actor){
		if(!$this->db->table_exists('db_storefront_carts')) return false;
		$now = date('Y-m-d H:i:s');
		$this->db->where('id', (int)$cartId)->where('store_id', (int)$storeId)
			->where('status', 'active')
			->where('reminder_count <', 3)
			->where('(reminder_sent_at IS NULL OR reminder_sent_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR))', null, false)
			->set('reminder_count', 'reminder_count + 1', false)
			->set('reminder_sent_at', $now);
		if($this->db->field_exists('opt_out', 'db_storefront_carts')){
			$this->db->where('opt_out', 0);
		}
		$this->db->update('db_storefront_carts');
		if($this->db->affected_rows() !== 1) return false;
		if($this->db->table_exists('db_storefront_cart_reminders')){
			$this->db->insert('db_storefront_cart_reminders', array(
				'store_id' => (int)$storeId,
				'cart_id'  => (int)$cartId,
				'channel'  => substr((string)$channel, 0, 20) ?: 'whatsapp',
				'mode'     => $mode === 'auto' ? 'auto' : 'manual',
				'recipient'=> substr((string)$recipient, 0, 191) ?: null,
				'detail'   => substr((string)$detail, 0, 255) ?: null,
				'actor'    => substr((string)$actor, 0, 60) ?: null,
				'created_at' => $now,
			));
		}
		return true;
	}

	/** Record a send attempt (auto-recovery retries, capped at 3). */
	public function recordCartSendAttempt($cartId, $storeId){
		if(!$this->db->table_exists('db_storefront_carts')) return false;
		if(!$this->db->field_exists('send_attempts', 'db_storefront_carts')) return false;
		$this->db->where('id', (int)$cartId)->where('store_id', (int)$storeId)
			->where('send_attempts <', 3)
			->set('send_attempts', 'send_attempts + 1', false)
			->set('last_send_attempt_at', date('Y-m-d H:i:s'))
			->update('db_storefront_carts');
		return $this->db->affected_rows() === 1;
	}
}
