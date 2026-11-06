<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Storefront Model
 * Manages online storefront settings, services, online orders, and QR codes.
 */
class Storefront_model extends CI_Model {

	private static $themesSeeded = false;
	private $verifiedCustomers = [];

	/** Why the last status change was refused, in staff's words. */
	public $last_status_error = '';

	/** Why the last sales-ledger posting was refused (batch stock short, etc). */
	public $last_ledger_error = '';

	/** Why the last online reservation was refused, in the shopper's words. */
	public $last_reservation_error = '';

	public function __construct(){
		parent::__construct();
		$this->ensureTables();
        $this->load->helper('category_tree');
	}

	/**
	 * Returns a WHERE clause fragment to exclude expired items from online store.
	 * Used by all public-facing product queries.
	 */
	private function _expiredWhere($tableAlias = 'a', $storeId = null){
		// Always exclude expired items from the online storefront.
		// If you need to allow expired items online, manually unpublish them.
		return "($tableAlias.expire_date IS NULL OR $tableAlias.expire_date LIKE '0000%' OR $tableAlias.expire_date >= '".date('Y-m-d')."')";
	}

	/**
	 * Verify storefront tables exist; create optional portal/Sendchamp tables when missing.
	 */
	private function ensureTables(){
		$tables = [
			'db_storefront_settings', 'db_services', 'db_online_orders', 'db_online_order_items',
			'db_storefront_themes', 'db_storefront_banners', 'db_storefront_homepage_sections',
			'db_storefront_domains', 'db_qr_codes', 'db_storefront_brands', 'db_storefront_testimonials',
			'db_storefront_instagram', 'db_storefront_faqs', 'db_storefront_analytics',
			'db_storefront_uploaded_themes', 'db_storefront_uploaded_theme_settings'
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

			// BulkSMSNigeria SMS provider. base_url is stored (not hardcoded) so
			// the endpoint can be corrected from the UI if the vendor moves it.
			if(!$this->db->table_exists('db_bulksmsng')){
				$this->db->query("CREATE TABLE IF NOT EXISTS db_bulksmsng (
					id INT(11) AUTO_INCREMENT PRIMARY KEY,
					store_id INT(11) NOT NULL,
					api_token TEXT NOT NULL,
					sender_id VARCHAR(50) NOT NULL DEFAULT 'BulkSMS',
					base_url VARCHAR(255) NOT NULL DEFAULT 'https://www.bulksmsnigeria.com/api/v2',
					gateway VARCHAR(20) NULL DEFAULT NULL,
					created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					UNIQUE KEY uk_store (store_id)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
			} else {
				// One credential row per store: without this, get_credentials()
				// uses ->row() and would silently pick the first match, sending
				// from the wrong sender ID. Added only when no duplicate exists,
				// so the guard can never abort on a legacy install.
				$hasUk = $this->db->query("SHOW INDEX FROM db_bulksmsng WHERE Key_name='uk_store'")->num_rows();
				if(!$hasUk){
					$dups = $this->db->query("SELECT COUNT(*) c FROM (SELECT store_id FROM db_bulksmsng GROUP BY store_id HAVING COUNT(*) > 1) d")->row();
					if(!$dups || (int)$dups->c === 0){
						$this->db->query("ALTER TABLE db_bulksmsng ADD UNIQUE KEY uk_store (store_id)");
					}
				}
			}

			if($this->db->table_exists('db_storefront_settings')){
				$have = $this->db->list_fields('db_storefront_settings');
				$need = [
					'store_logo' => "VARCHAR(255) NULL DEFAULT NULL",
					'footer_logo' => "VARCHAR(255) NULL DEFAULT NULL",
					'show_sale_stock_labels' => "TINYINT(1) NOT NULL DEFAULT 1",
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
					'theme_content_json' => "MEDIUMTEXT NULL",
					'newsletter_title' => "VARCHAR(255) NULL DEFAULT 'Stay in the Loop'",
					'newsletter_subtitle' => "VARCHAR(500) NULL DEFAULT 'Subscribe for updates, deals and new arrivals.'",
					'instagram_access_token' => "VARCHAR(500) NULL DEFAULT NULL",
					'instagram_username' => "VARCHAR(100) NULL DEFAULT NULL",
					'google_places_api_key' => "VARCHAR(255) NULL DEFAULT NULL",
					'gmb_place_id' => "VARCHAR(100) NULL DEFAULT NULL",
					'sendchamp_json' => "TEXT NULL DEFAULT NULL",
					'city_shipping_enabled' => "TINYINT(1) NULL DEFAULT 0",
					'city_shipping_json' => "TEXT NULL DEFAULT NULL",
					// Opt-in only: existing storefronts remain first-party/catalogue-only.
					'partner_marketplace_enabled' => "TINYINT(1) NOT NULL DEFAULT 0"
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
				// Approved-Rx invoice link (migration 4.0.9.233). Self-healed so a
				// server that has not run the migration yet still sends customers a
				// link instead of failing the pharmacist's approval.
				if(!$this->db->field_exists('pay_link_token_hash', 'db_online_orders')){
					$this->db->query("ALTER TABLE db_online_orders ADD pay_link_token_hash CHAR(64) NULL DEFAULT NULL");
				}
				if(!$this->db->field_exists('pay_link_expires_at', 'db_online_orders')){
					$this->db->query("ALTER TABLE db_online_orders ADD pay_link_expires_at DATETIME NULL DEFAULT NULL");
				}
				if(!$this->db->field_exists('pay_link_sent_at', 'db_online_orders')){
					$this->db->query("ALTER TABLE db_online_orders ADD pay_link_sent_at DATETIME NULL DEFAULT NULL");
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
				if(!$this->db->field_exists('warehouse_id', 'db_online_orders')){
					$this->db->query("ALTER TABLE db_online_orders ADD warehouse_id INT(11) NOT NULL DEFAULT 0 AFTER store_id");
				}
				if(!$this->db->field_exists('source_warehouse_id', 'db_online_orders')){
					$this->db->query("ALTER TABLE db_online_orders ADD source_warehouse_id INT(11) NULL DEFAULT NULL AFTER warehouse_id");
				}
			}
			if($this->db->table_exists('db_warehouse')){
				$warehouseFields = [
					'stock_source_id' => "INT(11) NULL DEFAULT NULL",
					'business_hours' => "VARCHAR(255) NULL DEFAULT NULL",
					'delivery_coverage' => "VARCHAR(500) NULL DEFAULT NULL",
					'delivery_fee' => "DECIMAL(20,2) NOT NULL DEFAULT 0",
					'pickup_enabled' => "TINYINT(1) NOT NULL DEFAULT 1",
					'public_enabled' => "TINYINT(1) NOT NULL DEFAULT 1",
				];
				foreach($warehouseFields as $field => $definition){
					if(!$this->db->field_exists($field, 'db_warehouse')) $this->db->query("ALTER TABLE db_warehouse ADD {$field} {$definition}");
				}
			}
		} catch (Exception $e) {
			log_message('error', 'Storefront ensureTables optional migration failed: ' . $e->getMessage());
		}
	}

	// ============== STOREFRONT SETTINGS ==============

	/**
	 * Outlets a shopper can order from. A store with one till gets no choice
	 * anywhere in the storefront — the picker only exists when there is
	 * genuinely somewhere else to order from.
	 *
	 * Deliberately the same definition the public "Our Branches" page uses
	 * (Theme_engine::branches()): only locations the merchant has published as
	 * customer-facing branches count. The store's own System warehouse is a
	 * stock back office, so including it here would have popped an outlet
	 * picker onto every retail and clinic storefront that has never advertised
	 * a second location. Pass `restaurant` as the context for Food Express so a
	 * food storefront cannot expose pharmacy fulfilment fixtures that happen to
	 * share a demo tenant's warehouse table.
	 */
	public function getOrderableOutlets($storeId = null, $context = ''){
		$storeId = $storeId ?: get_current_store_id();
		if (mp_pharmacy_standard($storeId)) return [];
		if(!$this->db->table_exists('db_warehouse')) return [];
		// address/city arrive with migration 4.0.9.161 — omit them on older schemas.
		$cols = ['id', 'warehouse_name'];
		foreach(['address', 'city', 'state', 'country', 'business_hours', 'delivery_coverage', 'delivery_fee', 'pickup_enabled', 'branch_type', 'stock_source_id', 'public_enabled', 'email'] as $col){
			if($this->db->field_exists($col, 'db_warehouse')) $cols[] = $col;
		}
		$this->db->select(implode(', ', $cols))
			->where('store_id', (int)$storeId)
			->where('status', 1)
			->order_by('warehouse_name', 'asc')
			->limit(24);
		if($this->db->field_exists('warehouse_type', 'db_warehouse')){
			$this->db->where('warehouse_type', 'Custom');
		}
		if($this->db->field_exists('public_enabled', 'db_warehouse')){
			$this->db->where('public_enabled', 1);
		}
		$rows = $this->db->get('db_warehouse')->result();
		if(!$rows) return [];

		// Pharmacy demo locations are deliberately marked as partner/pickup
		// hubs, but the primary fixture is a normal `branch`. The public food
		// catalogue must still be safe when an old mixed-demo tenant contains
		// those rows. Keep this boundary here so branches, product pickers and
		// checkout all receive the same restaurant-only collection.
		if(strtolower(trim((string)$context)) === 'restaurant'){
			$rows = array_values(array_filter($rows, function($row){
				$type = strtolower(trim((string)($row->branch_type ?? '')));
				if(in_array($type, ['partner', 'pickup_hub'], true)) return false;
				$signals = implode(' ', [
					(string)($row->warehouse_name ?? ''),
					(string)($row->address ?? ''),
					(string)($row->delivery_coverage ?? ''),
					(string)($row->email ?? ''),
				]);
				return !preg_match('/pharmacy|medical|prescription|fulfilment|fulfillment|collection hub/i', $signals);
			}));
		}
		return $rows;
	}

	public function getSettings($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$row = $this->db->where('store_id', $storeId)->get('db_storefront_settings')->row();
		if(!$row){
			// Return default settings
			$store = get_store_details($storeId);
			return mp_pharmacy_storefront_settings((object)[
				'store_id' => $storeId,
				'store_slug' => $store ? strtolower(str_replace(' ','-', $store->store_name)) : 'store',
				'store_description' => '',
				'store_banner' => '',
				'store_logo' => '',
				'footer_logo' => '',
				'show_sale_stock_labels' => 1,
				'whatsapp_number' => '',
				'store_email' => $store ? $store->email : '',
				'store_phone' => $store ? $store->mobile : '',
				'store_address' => $store ? $store->address : '',
				'default_branch_id' => 0,
				'store_status' => 'active',
				'allow_paystack' => 1,
				'allow_whatsapp' => 1,
				'allow_pay_on_delivery' => 1,
				'partner_marketplace_enabled' => 0,
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
			'instagram_business_account_id' => '',
			'whatsapp_api_token' => '',
			'whatsapp_phone_number_id' => '',
			'whatsapp_webhook_verify_token' => '',
			'whatsapp_api_enabled' => 0,
			'facebook_page_access_token' => '',
			'facebook_page_id' => '',
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
			]);
		}
		// Ensure a store slug is always set so public links never generate double slashes
		if(empty($row->store_slug)){
			$store = get_store_details($storeId);
			$expectedSlug = $store ? strtolower(preg_replace('/[^a-z0-9-]/', '-', $store->store_name)) : 'store';
			$expectedSlug = trim($expectedSlug, '-');
			$row->store_slug = !empty($expectedSlug) ? $expectedSlug : 'store';
			$this->saveSettings($storeId, ['store_slug' => $row->store_slug]);
		}
		return mp_pharmacy_storefront_settings($row);
	}

	public function saveSettings($storeId, $data){
		$storeId = $storeId ?: get_current_store_id();
		if (mp_pharmacy_industry(mp_get_store_profile($storeId)['industry_type'] ?? '')) {
			// Pharmacy mode is administered only through Critical settings.
			unset($data['partner_marketplace_enabled']);
			if (mp_pharmacy_standard($storeId)) {
				unset($data['default_branch_id']);
				if (isset($data['shipping_methods_json'])) {
					$methods = json_decode($data['shipping_methods_json'], true) ?: [];
					$methods = array_values(array_filter($methods, function($m) { return !mp_pharmacy_pickup_method(($m['type'] ?? '').' '.($m['name'] ?? '')); }));
					// Hidden collection methods survive edits to ordinary delivery settings.
					$raw = $this->db->where('store_id', $storeId)->get('db_storefront_settings')->row();
					foreach (json_decode($raw->shipping_methods_json ?? '', true) ?: [] as $method) {
						if (mp_pharmacy_pickup_method(($method['type'] ?? '').' '.($method['name'] ?? ''))) $methods[] = $method;
					}
					$data['shipping_methods_json'] = json_encode($methods);
				}
			}
		}
		// Drop keys for columns that may not exist yet on un-migrated installs
		foreach(['marquee_items', 'city_shipping_enabled', 'city_shipping_json', 'background_color', 'nav_json', 'partner_marketplace_enabled'] as $col){
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
		// Public storefront resolution must be explicit. Never infer a store from
		// a numeric id/name, the current session, or the first active storefront:
		// an omitted or mistyped slug must not display another merchant's shop.
		$slug = trim((string)$slug);
		if($slug === '') return null;

		// 1. Exact match
		$row = $this->db->where('store_slug', $slug)->get('db_storefront_settings')->row();
		if(!$row){
			// 2. Case-insensitive match
			$row = $this->db->where('LOWER(store_slug)', strtolower($slug))->get('db_storefront_settings')->row();
		}
		return mp_pharmacy_storefront_settings($row);
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

	/** A marked variant option belongs on its parent product card. */
	private function _flaggedCardWhere($flag, $storeId, $alias = 'a'){
		if(!in_array($flag, ['is_featured', 'is_new_arrival'], true)) return '0=1';
		$storeId = (int)$storeId;
		return "({$alias}.{$flag} = 1 OR EXISTS (SELECT 1 FROM db_items variant_flag"
			. " WHERE variant_flag.store_id = {$storeId} AND variant_flag.parent_id = {$alias}.id"
			. " AND variant_flag.{$flag} = 1 AND variant_flag.publish_online = 1"
			. " AND (variant_flag.status = 1 OR variant_flag.status IS NULL)"
			. " AND (variant_flag.not_for_sale IS NULL OR variant_flag.not_for_sale = 0)))";
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
	private function _decorateVariantParents($rows, $inheritFlags = false, $warehouseId = 0){
		$parentIds = [];
		foreach($rows as $r){
			if(($r->item_group ?? '') === 'Variants'){ $parentIds[] = (int)$r->id; }
		}
		if(!empty($parentIds)){
			$children = $this->db->select('id, parent_id, item_name, item_image, sales_price, mrp, online_price, discount_type, discount, stock, is_featured, is_new_arrival')
				->from('db_items')
				->where('store_id', (int)$rows[0]->store_id)
				->where_in('parent_id', $parentIds)
				->where('publish_online', 1)
				->where('(status = 1 OR status IS NULL)', null, false)
				->where("(not_for_sale IS NULL OR not_for_sale = 0)", null, false)
				->order_by('id', 'asc')
				->get()->result();
			// Options are corrected against the shelf before the parent's figure
			// is summed, so a parent never advertises units no option can sell.
			$children = $this->_applyLedgerAvailability($children, (int)$rows[0]->store_id, (int)$warehouseId);
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
				if($inheritFlags){
					foreach($kids as $kid){
						if(!empty($kid->is_featured)) $r->is_featured = 1;
						if(!empty($kid->is_new_arrival)) $r->is_new_arrival = 1;
					}
				}
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

	/**
	 * Which stock locations the online channel may sell from for this store.
	 *
	 * A Standard pharmacy sells from its own branch — the same one the till
	 * opens on — and never from a partner or collection hub. Every other route
	 * keeps its existing behaviour and may sell from any active location of the
	 * store. A named outlet always wins, so a multi-outlet shop can quote the
	 * outlet the shopper is actually ordering from.
	 */
	public function online_stock_locations($storeId = null, $warehouseId = 0){
		$storeId = (int)($storeId ?: get_current_store_id());
		$warehouseId = (int)$warehouseId;
		if($warehouseId > 0){
			try { return [$this->orderStockSource($storeId, $warehouseId)]; } catch(RuntimeException $e){ return []; }
		}
		if(mp_pharmacy_standard($storeId)){
			$own = mp_pharmacy_own_warehouse_ids($storeId);
			$preferred = (int)get_pos_default_warehouse_id($storeId);
			return in_array($preferred, $own, true) ? [$preferred] : $own;
		}
		if(!$this->db->table_exists('db_warehouse')) return [];
		$rows = $this->db->select('id')->where('store_id', $storeId)->where('status', 1)->get('db_warehouse')->result();
		return array_map(function($row){ return (int)$row->id; }, $rows);
	}

	/**
	 * How much of each item this shop can hand over right now.
	 *
	 * Under automatic batch selling the shelf IS the batch ledger, so the
	 * online number is what the sellable lots hold — capped by the stock figure
	 * and by the branch's own count, because the counter stops at whichever
	 * runs out first. A store on Manual rotation gets no answers back here and
	 * keeps the plain stock figure, which is what every other industry's route
	 * has always used.
	 *
	 * @param array $stockByItem [item_id => aggregate stock figure]
	 * @return array [item_id => float]
	 */
	private function _ledger_availability($storeId, array $ids, array $stockByItem, $warehouseId = 0){
		$storeId = (int)$storeId;
		$ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
		if(!$ids || !$storeId) return [];
		$this->load->model('inventory_rotation_model');
		$usesLots = $this->inventory_rotation_model->sells_from_lot_ledger($storeId);
		if(!$usesLots && !(int)$warehouseId) return [];
		$locations = $this->online_stock_locations($storeId, $warehouseId);
		if(!$locations) return array_fill_keys($ids, 0);
		$lots = $usesLots ? $this->inventory_rotation_model->sellable_map($storeId, $locations, $ids) : [];
		$single = count($locations) === 1;
		$branch = [];
		if($single && $this->db->table_exists('db_warehouseitems')){
			$query = $this->db->select('item_id, available_qty')->where('store_id', $storeId)
				->where('warehouse_id', (int)$locations[0])->where_in('item_id', $ids)->get('db_warehouseitems');
			foreach(($query ? $query->result() : []) as $row) $branch[(int)$row->item_id] = (float)$row->available_qty;
		}
		$branchLedgerExists = false;
		if($single && $this->db->table_exists('db_warehouseitems')){
			// Once the store uses branch allocations, an item with no row at this
			// source is not available there. Looking only for rows on the selected
			// branch would incorrectly fall back to pooled stock for a new/empty
			// branch and could promise stock held at a different outlet.
			$branchLedgerExists = $this->db->where('store_id', $storeId)
				->count_all_results('db_warehouseitems') > 0;
		}
		$profile = function_exists('mp_get_store_profile') ? mp_get_store_profile($storeId) : [];
		$isPharmacy = function_exists('mp_pharmacy_industry')
			? mp_pharmacy_industry($profile['industry_type'] ?? '') : mp_pharmacy_standard($storeId);
		$requireBranchLedger = $single && ($isPharmacy || $branchLedgerExists);
		$out = [];
		foreach($ids as $id){
			$figures = [(float)($stockByItem[$id] ?? 0)];
			if($usesLots) $figures[] = (float)($lots[$id] ?? 0);
			// An outlet with no row holds nothing there — the branch figure is
			// deleted when it drains. Only apply it for a single-location read.
			if($requireBranchLedger) $figures[] = (float)($branch[$id] ?? 0);
			$out[$id] = round(max(0, min($figures)), 4);
		}
		return $out;
	}

	/**
	 * How much of one item this shop can hand over right now, online. Used by
	 * the cart and checkout guards, which must refuse for the same reason the
	 * till would.
	 */
	public function sellable_online($storeId, $itemId, $warehouseId = 0){
		$storeId = (int)$storeId; $itemId = (int)$itemId;
		$row = $this->db->select('stock')->where('id', $itemId)->where('store_id', $storeId)->get('db_items')->row();
		if(!$row) return 0.0;
		$map = $this->_ledger_availability($storeId, [$itemId], [$itemId => (float)$row->stock], $warehouseId);
		if($map) return $map[$itemId];
		return round((float)$row->stock, 4);
	}

	/**
	 * Rewrite the stock figure on storefront rows to the number the counter
	 * could actually give the shopper, so "in stock" online means the same
	 * thing as "in stock" at the till. Variant parents are skipped — their
	 * figure is the sum of options, which are corrected here first.
	 */
	private function _applyLedgerAvailability($rows, $storeId, $warehouseId = 0){
		if(empty($rows)) return $rows;
		$ids = [];
		$stockByItem = [];
		foreach($rows as $row){
			if(($row->item_group ?? '') === 'Variants') continue;
			// A bundle's figure is what its components can be assembled into,
			// already resolved; the bundle itself has no lot on the shelf.
			if(!empty($row->is_bundle)) continue;
			if(!empty($row->service_bit)) continue;
			if(isset($row->product_type) && !in_array((string)$row->product_type, ['physical', ''], true)) continue;
			$ids[] = (int)$row->id;
			$stockByItem[(int)$row->id] = (float)($row->stock ?? 0);
		}
		$map = $this->_ledger_availability((int)$storeId, $ids, $stockByItem, $warehouseId);
		if(!$map) return $rows;
		foreach($rows as $row){
			$id = (int)$row->id;
			if(!isset($map[$id])) continue;
			$row->sellable_qty = $map[$id];
			$row->stock = $map[$id];
		}
		return $rows;
	}

	public function getOnlineProducts($storeId = null, $categoryId = null, $search = '', $limit = 50, $offset = 0, $sort = ''){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.expire_date');
		$this->db->select('a.id, a.store_id, a.category_id, a.item_name, a.item_image, a.item_code, a.description, a.stock, a.alert_qty, a.sales_price, a.mrp, a.online_price, a.discount_type, a.discount, a.status, a.product_type, a.is_new_arrival, a.is_featured, a.is_bundle, a.bundle_pricing, a.item_group, a.parent_id, a.child_bit, b.category_name');
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
			mp_category_filter('a.category_id',$categoryId,$storeId,true);
		}
		if($search){
			$this->db->group_start();
			$this->db->like('a.item_name', $search);
			$this->db->or_like('a.item_code', $search);
			$this->db->or_like('b.category_name', $search);
			$this->db->group_end();
		}
		foreach($this->_onlineSortColumns($sort) as $column => $direction){
			$this->db->order_by($column, $direction, strpos($column, '(') !== false ? FALSE : TRUE);
		}
		if($limit !== null) $this->db->limit($limit, $offset);
		return $this->_applyLedgerAvailability($this->_decorateVariantParents($this->db->get()->result()), $storeId);
	}

	/**
	 * Whitelisted catalogue sort orders. An empty sort keeps the historical
	 * "newest first" ordering, so callers that never pass one are unaffected.
	 *
	 * Price sorts on the same base the pricing engine starts from —
	 * online_price, else mrp, else sales_price — because effective_price is
	 * resolved in PHP per customer and cannot be ordered in SQL.
	 */
	private function _onlineSortColumns($sort){
		$price = 'COALESCE(NULLIF(a.online_price,0), NULLIF(a.mrp,0), a.sales_price)';
		switch((string)$sort){
			case 'price_asc':  return [$price => 'asc', 'a.id' => 'desc'];
			case 'price_desc': return [$price => 'desc', 'a.id' => 'desc'];
			case 'name_asc':   return ['a.item_name' => 'asc', 'a.id' => 'desc'];
			case 'name_desc':  return ['a.item_name' => 'desc', 'a.id' => 'desc'];
			case 'newest':     return ['a.id' => 'desc'];
			default:           return ['a.id' => 'desc'];
		}
	}

	/**
	 * Attach each item's selling unit ("kg", "carton") for storefront display.
	 * Additive: rows keep every field they had, and an item with no unit simply
	 * gets none. One store-scoped query for the whole page.
	 */
	public function attachUnitNames($rows, $storeId = null){
		if(empty($rows) || !is_array($rows)) return $rows;
		$storeId = (int)($storeId ?: get_current_store_id());
		$ids = [];
		foreach($rows as $row) if(is_object($row) && !empty($row->id)) $ids[(int)$row->id] = (int)$row->id;
		if(!$ids || !$this->db->table_exists('db_units')) return $rows;
		$names = $this->db->select('i.id, u.unit_name')
			->from('db_items i')
			->join('db_units u', 'u.id=i.unit_id', 'inner')
			->where('i.store_id', $storeId)
			->where_in('i.id', array_values($ids))
			->get()->result();
		$map = [];
		foreach($names as $row) $map[(int)$row->id] = trim((string)$row->unit_name);
		foreach($rows as $row){
			if(is_object($row) && isset($map[(int)$row->id])) $row->unit_name = $map[(int)$row->id];
		}
		return $rows;
	}

	/** Restaurant-only complements, separately resolved against each published kitchen. */
	public function getRestaurantPairings($storeId, $product, $outlets = [], $allowBackorder = false){
		$this->load->helper('food_pairing');
		$candidates = $this->getOnlineProducts($storeId, null, '', null);
		$candidates = array_values(array_filter($candidates, function($item) use ($storeId){
			$expiry = substr((string)($item->expire_date ?? ''), 0, 10);
			return (int)$item->store_id === (int)$storeId
				&& ($item->product_type ?? 'physical') === 'physical'
				&& ($item->item_group ?? 'Single') !== 'Variants'
				&& (!$expiry || $expiry === '0000-00-00' || $expiry >= date('Y-m-d'));
		}));
		$ledgers = [];
		$sources = array_unique(array_map(function($outlet){ return (int)(!empty($outlet->stock_source_id) ? $outlet->stock_source_id : $outlet->id); }, $outlets));
		if($sources && $this->db->table_exists('db_warehouseitems')){
			$rows = $this->db->select('warehouse_id, item_id, available_qty')->where('store_id', (int)$storeId)
				->where_in('warehouse_id', $sources)->get('db_warehouseitems')->result();
			foreach($rows as $row) $ledgers[(int)$row->warehouse_id][(int)$row->item_id] = (float)$row->available_qty;
		}
		$result = [];
		$contexts = $outlets ?: [(object)['id' => 0, 'stock_source_id' => 0]];
		foreach($contexts as $outlet){
			$source = (int)(!empty($outlet->stock_source_id) ? $outlet->stock_source_id : $outlet->id);
			$eligible = array_filter($candidates, function($item) use ($source, $ledgers, $allowBackorder){
				if($allowBackorder) return true;
				$qty = isset($ledgers[$source]) ? ($ledgers[$source][(int)$item->id] ?? 0) : ($item->stock ?? 0);
				return (float)$qty >= 1 && (float)($item->stock ?? 0) >= 1;
			});
			$result[(int)$outlet->id] = mp_food_rank_pairings($product, $eligible, 4);
		}
		return $result;
	}

	public function getFeaturedProducts($storeId = null, $limit = 8, $includeVariantFlags = false){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.id, a.store_id, a.category_id, a.item_name, a.item_image, a.item_code, a.description, a.stock, a.alert_qty, a.sales_price, a.mrp, a.online_price, a.discount_type, a.discount, a.status, a.product_type, a.is_new_arrival, a.is_featured, a.is_bundle, a.bundle_pricing, a.item_group, a.parent_id, a.child_bit, b.category_name');
		$this->db->from('db_items a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.store_id', $storeId);
		if($includeVariantFlags){
			$this->db->where($this->_flaggedCardWhere('is_featured', $storeId), NULL, FALSE);
		} else {
			$this->db->where('a.is_featured', 1);
		}
		$this->db->where('a.publish_online', 1);
		$this->db->where('a.status', 1);
		$this->db->where('a.service_bit', 0);
		$this->db->where("(a.not_for_sale IS NULL OR a.not_for_sale = 0)", null, false);
		$this->db->where($this->_listedItemsWhere('a'), NULL, FALSE);
		$this->db->where($this->_expiredWhere('a', $storeId), NULL, FALSE);
		$this->db->order_by('a.id', 'desc');
		$this->db->limit($limit);
		return $this->_applyLedgerAvailability($this->_decorateVariantParents($this->db->get()->result(), $includeVariantFlags), $storeId);
	}

	public function getOnlineProduct($productId, $storeId = null, $warehouseId = 0){
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
		if($product){
			$decorated = $this->_decorateVariantParents([$product], false, $warehouseId);
			$this->_applyLedgerAvailability($decorated, $storeId, $warehouseId);
		}
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
		return $this->_applyLedgerAvailability($this->db->get()->result(), $storeId);
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
			mp_category_filter('a.category_id',$categoryId,$storeId,true);
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

	/** All of this merchant's services for the admin editor, including drafts and offline entries. */
	public function getServicesForAdmin($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->select('a.*, b.category_name')
			->from('db_services a')
			->join('db_category b', 'b.id=a.category_id AND b.store_id=a.store_id', 'left')
			->where('a.store_id', $storeId)
			->order_by('a.sort_order', 'asc')
			->order_by('a.id', 'desc')
			->get()->result();
	}

	public function getOnlineServices($storeId = null, $categoryId = null, $search = '', $limit = 50, $offset = 0){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.*, b.category_name');
		$this->db->from('db_services a');
		$this->db->join('db_category b', 'b.id=a.category_id', 'left');
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.available_online', 1);
		$this->db->where('a.status', 1);
		if($categoryId){
			mp_category_filter('a.category_id',$categoryId,$storeId,true);
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
		$this->db->where('a.status', 1);
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
			mp_category_filter('a.category_id',$categoryId,$storeId,true);
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
		$this->db->join('db_items b', "b.category_id=a.id AND b.store_id=a.store_id AND b.publish_online=1 AND b.status=1 AND b.service_bit=0 AND (b.not_for_sale IS NULL OR b.not_for_sale = 0) AND " . $this->_listedItemsWhere('b'), 'inner');
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.status', 1);
		$this->db->where($this->_expiredWhere('b', $storeId), NULL, FALSE);
		$this->db->group_by('a.id');
		$published=$this->db->get()->result();$tree=mp_category_tree_model()->public_tree($storeId,$published);$flat=[];$walk=function($nodes)use(&$walk,&$flat){foreach($nodes as $node){$flat[]=$node;$walk($node->children);}};$walk($tree);
		// Tree nodes are rebuilt from the full category table, so the real
		// published-item count is attached here rather than in the select above.
		foreach($flat as $node) $node->item_count = 0;
		if($flat){
			$rows = $this->db->select('b.category_id, COUNT(DISTINCT b.id) AS c')
				->from('db_items b')
				->where('b.store_id', $storeId)
				->where('b.publish_online', 1)
				->where('b.status', 1)
				->where('b.service_bit', 0)
				->where('(b.not_for_sale IS NULL OR b.not_for_sale = 0)', null, false)
				->where($this->_listedItemsWhere('b'), NULL, FALSE)
				->where($this->_expiredWhere('b', $storeId), NULL, FALSE)
				->where_in('b.category_id', array_map(function($n){ return (int)$n->id; }, $flat))
				->group_by('b.category_id')
				->get()->result();
			$counts = [];
			foreach($rows as $row) $counts[(int)$row->category_id] = (int)$row->c;
			foreach($flat as $node) $node->item_count = $counts[(int)$node->id] ?? 0;
		}
		return $flat;
	}

	public function getServiceCategories($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('a.id, a.category_name, a.category_image');
		$this->db->from('db_category a');
		$this->db->join('db_services b', 'b.category_id=a.id AND b.store_id=a.store_id AND b.available_online=1 AND b.status=1', 'inner');
		$this->db->where('a.store_id', $storeId);
		$this->db->where('a.status', 1);
		$this->db->group_by('a.id');
		$this->db->order_by('a.category_name', 'asc');
		$published=$this->db->get()->result();$tree=mp_category_tree_model()->public_tree($storeId,$published);$flat=[];$walk=function($nodes)use(&$walk,&$flat){foreach($nodes as $node){$flat[]=$node;$walk($node->children);}};$walk($tree);return $flat;
	}

	// ============== ONLINE ORDERS ==============
	public $last_order_duplicate = false;

	public function createOrder($data){
		$this->last_order_duplicate = false;
		if(!empty($data['idempotency_key']) && $this->db->field_exists('idempotency_key', 'db_online_orders')){
			$existing = $this->db->where('store_id', (int)$data['store_id'])
				->where('idempotency_key', substr((string)$data['idempotency_key'], 0, 96))
				->get('db_online_orders')->row();
			if($existing){
				$this->last_order_duplicate = true;
				return (int)$existing->id;
			}
			$data['idempotency_key'] = substr((string)$data['idempotency_key'], 0, 96);
		}
		$data['order_code'] = $this->generateOrderCode();
		$this->db->insert('db_online_orders', $data);
		return $this->db->insert_id();
	}

	/**
	 * Post an online/QR order into the existing sales ledger exactly once.
	 * This deliberately does not touch stock: online checkout already owns the
	 * reservation/commit state, while db_sales is only the financial/reporting
	 * representation of the same order.
	 */
	/**
	 * What the delivery line on the invoice should say. A collection order has no
	 * charge to name, so it stays blank rather than printing "Pickup: ₦0.00".
	 */
	private function _deliveryLabel($order){
		if((float)($order->delivery_fee ?? 0) <= 0) return null;
		$method = trim((string)($order->shipping_method ?? ''));
		if($method === '' || stripos($method, 'pickup') === 0) return 'Delivery';
		return substr($method, 0, 160);
	}

	public function ensureSalesLedger($orderId){
		$order = $this->getOrder((int)$orderId);
		if(!$order || !$this->db->field_exists('sales_id', 'db_online_orders')
			|| !$this->db->field_exists('online_order_id', 'db_sales')) return false;
		if((int)$order->sales_id > 0) return (int)$order->sales_id;
		$this->db->trans_begin();
		$locked = $this->db->query('SELECT * FROM db_online_orders WHERE id = ? AND store_id = ? FOR UPDATE', [(int)$orderId, (int)$order->store_id])->row();
		if(!$locked){ $this->db->trans_rollback(); return false; }
		$linked = $this->db->where('store_id', (int)$locked->store_id)->where('online_order_id', (int)$orderId)->get('db_sales')->row();
		if($linked){
			$this->db->where('id', (int)$orderId)->update('db_online_orders', ['sales_id' => (int)$linked->id]);
			$this->db->trans_commit(); return (int)$linked->id;
		}
		$saleCode = substr((string)$locked->order_code, 0, 50);
		$customerId = (int)($locked->customer_id ?? 0);
		$sale = [
			'store_id' => (int)$locked->store_id, 'warehouse_id' => (int)($locked->warehouse_id ?? 0),
			'online_order_id' => (int)$orderId, 'restaurant_order_key' => $locked->idempotency_key ?? null,
			'source_channel' => $locked->source_channel ?? 'web', 'sales_code' => $saleCode,
			'reference_no' => $saleCode, 'sales_date' => date('Y-m-d'), 'sales_status' => 'Final',
			'customer_id' => $customerId > 0 ? $customerId : null, 'subtotal' => (float)$locked->subtotal,
			'grand_total' => (float)$locked->grand_total, 'payment_status' => ($locked->payment_status === 'paid' ? 'Paid' : 'Unpaid'),
			// The shopper was quoted delivery at checkout, so the invoice they are
			// asked to pay must carry it too — otherwise the store has taken money
			// the ledger cannot see.
			'shipping_fee' => (float)($locked->delivery_fee ?? 0),
			'shipping_label' => $this->_deliveryLabel($locked),
			'paid_amount' => 0, 'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
			'created_by' => 'RestaurantOS', 'status' => 1, 'table_id' => 0,
		];
		$this->db->insert('db_sales', $sale);
		$salesId = (int)$this->db->insert_id();
		if(!$salesId){ $this->db->trans_rollback(); return false; }
		// Products hold FEFO-ordered lots at the till; online orders must draw
		// the same physical stock or the batch screen overstates what is on the
		// shelf and recalls miss the sale. The rotation model no-ops while the
		// store keeps rotation on Manual.
		$stockWarehouseId = (int)($locked->source_warehouse_id ?? 0);
		if($stockWarehouseId <= 0) $stockWarehouseId = (int)($locked->warehouse_id ?? 0);
		if($stockWarehouseId <= 0 && function_exists('get_store_warehouse_id')) $stockWarehouseId = (int)get_store_warehouse_id();
		$this->load->model('inventory_rotation_model');
		foreach($this->getOrderItems($orderId) as $line){
			if(!in_array((string)$line->item_type, ['product','bundle','event'], true)) continue;
			$saleLine = [
				'store_id' => (int)$locked->store_id, 'sales_id' => $salesId, 'sales_status' => 'Final',
				'item_id' => (int)$line->item_id, 'sales_qty' => (float)$line->qty,
				'base_unit_qty' => (float)$line->qty, 'price_per_unit' => (float)$line->unit_price,
				'total_cost' => (float)$line->total_price, 'unit_total_cost' => (float)$line->unit_price,
				'status' => 1,
			];
			if($this->db->field_exists('pricing_snapshot', 'db_salesitems')) $saleLine['pricing_snapshot'] = $line->pricing_snapshot ?? null;
			if($this->db->field_exists('price_type', 'db_salesitems')){
				$pricing = json_decode((string)($line->pricing_snapshot ?? ''), true);
				$saleLine['price_type'] = ($pricing['effective_tier'] ?? '') === 'wholesale' ? 'wholesale' : 'retail';
			}
			$this->db->insert('db_salesitems', $saleLine);
			$salesItemId = (int)$this->db->insert_id();
			if(!$salesItemId){ $this->db->trans_rollback(); return false; }
			if((string)$line->item_type === 'product'){
				// An order that reserved its batches at checkout already has the
				// units taken off those lots; posting the sale converts the hold
				// into provenance instead of drawing the shelf a second time.
				if($this->inventory_rotation_model->has_holds((int)$orderId, (int)$line->id)){
					$batchErr = $this->inventory_rotation_model->convert_line_holds(
						(int)$orderId, $salesId, (int)$line->id, $salesItemId);
				} else {
					$batchErr = $this->inventory_rotation_model->allocate_sale_line(
						$salesId, $salesItemId, (int)$locked->store_id, $stockWarehouseId, (int)$line->item_id, (float)$line->qty, 0);
				}
				// Only a real message from the rotation model may refuse the
				// sale. A null answer (an unloaded model, a stubbed caller) used
				// to read as a refusal here, which silently kept a paid online
				// order from ever reaching the ledger.
				if(!is_string($batchErr)){
					log_message('error', 'Online order '.$orderId.' got a non-string batch result ('.gettype($batchErr).'); posting the ledger without batch allocation.');
				} elseif($batchErr !== ''){
					$this->db->trans_rollback();
					$this->last_ledger_error = $batchErr;
					log_message('error', 'Online order batch allocation refused (order '.$orderId.'): '.$batchErr);
					return false;
				}
			}
		}
		if($locked->payment_status === 'paid'){
			// Money that a gateway or a named person has confirmed for this order
			// already exists as its own record, so post those amounts rather than
			// inventing one line for the whole invoice. Only when there is nothing
			// to post (an install without the ledger, or a legacy paid order) does
			// the old single full-total row get written.
			$posted = 0;
			$this->load->model('online_payments_model', 'onlinepay');
			if($this->onlinepay->available()){
				$posted = $this->onlinepay->postVerifiedLines((int)$locked->store_id, (int)$orderId, $salesId);
			}
			if(!$posted){
				$this->db->insert('db_salespayments', [
					'store_id' => (int)$locked->store_id, 'sales_id' => $salesId, 'payment_date' => date('Y-m-d'),
					'payment_type' => $locked->payment_method ?: 'Online', 'payment' => (float)$locked->grand_total,
					'payment_reference' => $locked->paystack_reference ?: $locked->order_code,
					'confirmation_status' => 1, 'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
					'created_by' => 'RestaurantOS', 'status' => 1, 'customer_id' => $customerId > 0 ? $customerId : null,
				]);
			}
		}
		// Recipe ingredients are a stock movement of the same order, not a
		// second financial or finished-product ledger.  Keep this inside the
		// locked ledger transaction so a retry cannot consume them twice.
		if(!$this->ensureRecipeConsumption($locked, $orderId)){
			$this->db->trans_rollback();
			return false;
		}
		$this->db->where('id', (int)$orderId)->where('store_id', (int)$locked->store_id)
			->update('db_online_orders', ['sales_id' => $salesId]);
		if($this->db->trans_status() === false){ $this->db->trans_rollback(); return false; }
		// A storefront order that carries prescription-only medicines is a
		// dispensing too, not just a sale. Recorded with the same batch
		// provenance the till uses, and only when such a line exists.
		$this->_auditDispensed((int)$locked->store_id, $salesId);
		$this->db->trans_commit();
		return $salesId;
	}

	/**
	 * The ledger write is the money; the record of what it dispensed is the
	 * witness. A fault here must never leave a paid order unposted, so the
	 * capture is tried and dropped rather than allowed to abort the commit.
	 */
	private function _auditDispensed($storeId, $salesId){
		try {
			$capture = mp_audit_capture();
			if ($capture) $capture->dispensed((int)$storeId, (int)$salesId, false, array(), 'online');
		} catch (Throwable $e) {
			log_message('error', 'Online dispensing audit capture failed: ' . $e->getMessage());
		}
	}

	/**
	 * Consume made-to-order recipe ingredients once for an online/QR order.
	 * Finished-product reservation remains owned by the normal order stock
	 * state machine.  This only handles items explicitly configured as
	 * component/sale_deplete, and uses the shared stock-adjustment ledger.
	 */
	private function ensureRecipeConsumption($order, $orderId){
		if(!$this->db->field_exists('source_order_id', 'db_stockadjustment')
			|| !$this->db->field_exists('source_action', 'db_stockadjustment')) return true;
		// Legacy desktop POS records recipe depletion as SALE-<sales_id>.
		// Recognise that movement so the operational bridge cannot double it.
		if(!empty($order->sales_id) && $this->db->where('store_id', (int)$order->store_id)
			->where('reference_no', 'SALE-' . (int)$order->sales_id)
			->count_all_results('db_stockadjustment') > 0) return true;
		$existing = $this->db->where('store_id', (int)$order->store_id)
			->where('source_order_id', (int)$orderId)
			->where('source_action', 'recipe')
			->get('db_stockadjustment')->row();
		if($existing) return true;

		$ingredientTotals = [];
		$this->load->model('recipe_model');
		foreach($this->getOrderItems((int)$orderId) as $line){
			if((string)$line->item_type !== 'product' || (int)$line->item_id <= 0) continue;
			$item = $this->db->where('id', (int)$line->item_id)
				->where('store_id', (int)$order->store_id)->get('db_items')->row();
			if(!$item || !in_array((string)$item->item_production_mode, ['component', 'sale_deplete'], true)
				|| empty($item->recipe_id)) continue;
			$recipe = $this->recipe_model->get((int)$item->recipe_id);
			if(!$recipe) continue;
			$yield = (float)$recipe->yield_qty > 0 ? (float)$recipe->yield_qty : 1;
			$scale = (float)$line->qty / $yield;
			foreach($this->recipe_model->get_ingredients((int)$item->recipe_id) as $ingredient){
				$ingredientId = (int)$ingredient->item_id;
				$qty = (float)$ingredient->qty * $scale;
				if($ingredientId <= 0 || $qty <= 0) continue;
				$ingredientTotals[$ingredientId] = ($ingredientTotals[$ingredientId] ?? 0) + $qty;
			}
		}
		if(empty($ingredientTotals)) return true;

		// A pickup-only hub is the customer-facing handover point, not the
		// warehouse that owns the stock. Reserve and ledger against the source
		// warehouse while keeping order.warehouse_id as the fulfilment location.
		$warehouseId = (int)($order->source_warehouse_id ?? 0);
		if($warehouseId <= 0) $warehouseId = (int)($order->warehouse_id ?? 0);
		if($warehouseId <= 0 && function_exists('get_store_warehouse_id')) $warehouseId = (int)get_store_warehouse_id();
		$ledger = [
			'store_id' => (int)$order->store_id,
			'warehouse_id' => $warehouseId,
			'source_order_id' => (int)$orderId,
			'source_action' => 'recipe',
			'reference_no' => 'RECIPE-ORDER-' . (int)$orderId,
			'adjustment_date' => date('Y-m-d'),
			'adjustment_note' => 'Made-to-order recipe consumption #' . (int)$orderId,
			'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
			'created_by' => 'RestaurantOS', 'system_ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
			'system_name' => 'RestaurantOS', 'status' => 1,
		];
		if(!$this->db->insert('db_stockadjustment', $ledger)) return false;
		$adjustmentId = (int)$this->db->insert_id();
		foreach($ingredientTotals as $ingredientId => $qty){
			if(!$this->db->insert('db_stockadjustmentitems', [
				'store_id' => (int)$order->store_id, 'warehouse_id' => $warehouseId,
				'adjustment_id' => $adjustmentId, 'item_id' => (int)$ingredientId,
				'adjustment_qty' => -$qty,
				'description' => 'Recipe for online order #' . (int)$orderId, 'status' => 1,
			])) return false;
		}
		$this->load->model('pos_model');
		foreach(array_keys($ingredientTotals) as $ingredientId){
			if(!$this->pos_model->update_items_quantity((int)$ingredientId)) return false;
			if($warehouseId > 0 && function_exists('update_warehousewise_items_qty')
				&& !update_warehousewise_items_qty((int)$ingredientId, $warehouseId, (int)$order->store_id)) return false;
		}
		return true;
	}

	public function addOrderItem($data){
		return $this->db->insert('db_online_order_items', $data);
	}

	/** Used by the walk-in order bridge after Sales_model has committed. */
	public function ensureRecipeConsumptionForOrder($orderId){
		$order = $this->getOrder((int)$orderId);
		return $order ? $this->ensureRecipeConsumption($order, (int)$orderId) : false;
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
	 * Atomically claim the paid transition for an order.
	 * Returns true only for the request that actually performed the
	 * transition, so duplicate/concurrent callbacks cannot re-fulfil.
	 *
	 * A part-paid order is claimable too: money arriving in pieces is the
	 * normal way an invoice for a prescription gets settled.
	 */
	public function claimPaidOrder($orderId, $data = []){
		$data['payment_status'] = 'paid';
		// Payment is a separate fact from preparation. Do not force an order
		// back to "paid" when the kitchen has already moved it to processing or
		// ready; the lifecycle model owns that state.
		unset($data['order_status']);
		$data['updated_at'] = date('Y-m-d H:i:s');
		$this->db->where('id', $orderId)
			->where_in('payment_status', ['unpaid', 'partially_paid']);
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
		$this->last_status_error = '';
		$order=$this->getOrder($orderId);
		if (!$order) return false;
		$profile=mp_get_store_profile($order->store_id);
		if (($profile['industry_type'] ?? '')==='garden_centre_landscaping') {
			$allowed=['pending'=>['paid','processing','cancelled'],'paid'=>['processing','cancelled'],'processing'=>['ready','cancelled'],'ready'=>['completed','cancelled']];
			if ($status!==$order->order_status && !in_array($status,$allowed[$order->order_status] ?? [],true)) {
				$this->last_status_error = 'A garden or landscaping job moves one stage at a time, so it cannot skip to ' . $status . '.';
				return false;
			}
			if ($status==='paid' && $order->payment_status!=='paid') {
				$this->last_status_error = 'This order has not been paid for, so it cannot be marked paid.';
				return false;
			}
			if ($this->db->table_exists('db_service_fulfilment_sources')) {
				$this->load->model('service_fulfilment_model');
				if ($status==='completed' && !$this->service_fulfilment_model->online_complete($orderId,$order->store_id)) {
					$this->last_status_error = 'The service work on this order is not recorded as done yet, so it cannot be completed.';
					return false;
				}
				if ($status==='cancelled' && !$this->service_fulfilment_model->cancel_online_work($orderId,$order->store_id)) {
					$this->last_status_error = 'The booked work on this order could not be released, so it was not cancelled.';
					return false;
				}
			}
		}
		// The lifecycle itself — what may follow what, and the written record of
		// the move — belongs to one machine for every channel, not to this
		// business type. What stays here are the obligations a garden job has
		// that a shop does not: a service cannot be completed until the work
		// behind it has been recorded, and it only moves one stage at a time.
		$this->load->model('order_lifecycle_model');
		$moved = $this->order_lifecycle_model->move($order->store_id, $orderId, $status, [
			'channel' => !empty($order->table_number) ? 'qr' : 'online',
		]);
		if (empty($moved['ok'])) {
			$this->last_status_error = $moved['error'];
			return false;
		}
		return true;
	}

	/**
	 * The delivery leg of an order, kept apart from the shop's own stage:
	 * "packed" and "on the rider's bike" are different facts. The same words the
	 * rider board writes (Delivery_model::mark_item_status) land in the same
	 * column, so a shop that schedules riders and a shop that simply calls a
	 * bike are not writing two vocabularies onto one order.
	 */
	const DELIVERY_STATES = ['pending', 'out_for_delivery', 'delivered', 'failed', 'cancelled'];

	public function deliveryWord($state){
		$words = [
			'pending'          => 'Not sent out yet',
			'out_for_delivery' => 'On its way',
			'delivered'        => 'Delivered',
			'failed'           => 'Could not be delivered',
			'cancelled'        => 'Delivery cancelled',
		];
		return isset($words[(string)$state]) ? $words[(string)$state] : 'Not sent out yet';
	}

	/** A collection order has no delivery leg to move, whatever its status says. */
	public function orderIsCollection($order){
		if((string)($order->fulfilment_type ?? '') === 'pickup') return true;
		if((float)($order->delivery_fee ?? 0) > 0) return false;
		$method = trim((string)($order->shipping_method ?? ''));
		return $method !== '' && (bool)preg_match('/pick\s*[-\s]?up|collect/i', $method);
	}

	/**
	 * Move an order's delivery leg and write down who moved it. Delivered goods
	 * also close the shop's own stage, so the board is not left showing an order
	 * as waiting to go out after the rider is back at the depot.
	 *
	 * @return array ['ok'=>bool,'changed'=>bool,'word'=>string,'error'=>string]
	 */
	public function setOrderDelivery($storeId, $orderId, $status, array $opts = []){
		$this->last_status_error = '';
		$refuse = function($message){
			$this->last_status_error = $message;
			return ['ok' => false, 'changed' => false, 'word' => '', 'error' => $message];
		};
		$storeId = (int)$storeId; $orderId = (int)$orderId;
		$status = (string)$status;
		if(!in_array($status, self::DELIVERY_STATES, true)){
			return $refuse('That is not a delivery step.');
		}
		if(!$this->db->field_exists('delivery_status', 'db_online_orders')){
			return $refuse('This store cannot track deliveries yet — the delivery step has not been installed.');
		}
		$order = $this->db->where('id', $orderId)->where('store_id', $storeId)->get('db_online_orders')->row();
		if(!$order) return $refuse('That order is not one of yours.');
		$going = in_array($status, ['out_for_delivery', 'delivered'], true);
		if((string)$order->order_status === 'cancelled'){
			return $refuse('This order has been cancelled, so there is nothing to send.');
		}
		if($going && $this->orderIsCollection($order)){
			return $refuse('This order is for collection at the shop, so there is nothing to send out. Mark it Ready when it is packed, then Completed once the customer has picked it up.');
		}
		if($going && (string)$order->payment_status !== 'paid' && (string)$order->payment_method !== 'pay_on_delivery'){
			return $refuse('The money for this order has not arrived and it is not cash on delivery, so do not send it out yet.');
		}
		$from = trim((string)($order->delivery_status ?? ''));
		if($from === '') $from = 'pending';
		if(in_array($status, ['delivered', 'failed'], true) && $from !== 'out_for_delivery'){
			return $refuse($status === 'delivered'
				? 'Say the order is on its way before you say it has arrived.'
				: 'Only an order that is on its way can fail to arrive.');
		}
		if($from === 'delivered' && $status !== 'delivered'){
			return $refuse('This order has already been delivered. If something is wrong with it, refund it or raise a return rather than changing the delivery.');
		}
		if($from === $status){
			return ['ok' => true, 'changed' => false, 'word' => $this->deliveryWord($status), 'error' => ''];
		}

		$this->db->where('id', $orderId)->where('store_id', $storeId)->update('db_online_orders', [
			'delivery_status' => $status,
			'updated_at'      => date('Y-m-d H:i:s'),
		]);
		$rider = mb_substr(trim((string)($opts['rider'] ?? '')), 0, 80);
		$note  = mb_substr(trim((string)($opts['note'] ?? '')), 0, 160);
		$reason = $rider !== '' ? 'With ' . $rider : '';
		if($note !== '') $reason = trim($reason . ($reason !== '' ? ' — ' : '') . $note);
		$this->_recordDeliveryMove($storeId, $orderId, $from, $status, $reason, (string)($opts['channel'] ?? 'online'));

		if($status === 'delivered'){
			$this->load->model('order_lifecycle_model');
			$this->order_lifecycle_model->move($storeId, $orderId, 'completed', [
				'channel' => 'delivery',
				'reason'  => $reason !== '' ? $reason : null,
			]);
		}
		return ['ok' => true, 'changed' => true, 'word' => $this->deliveryWord($status), 'error' => ''];
	}

	/**
	 * The order's own journey, newest first, in the words a shop would say:
	 * who moved it, when, and anything they wrote when they did. A delivery step
	 * reads as "On its way — with Chidi", not as a status code.
	 *
	 * @return array of ['when','text','actor','kind']
	 */
	public function orderTrail($storeId, $orderId, $limit = 8){
		$rows = [];
		if(!$this->db->table_exists('db_order_status_log')) return $rows;
		$found = $this->db->where('store_id', (int)$storeId)->where('online_order_id', (int)$orderId)
			->order_by('created_at', 'desc')->order_by('id', 'desc')
			->limit((int)$limit)->get('db_order_status_log')->result();
		if(!$found) return $rows;
		$this->load->model('order_lifecycle_model');
		foreach($found as $r){
			// The handover that closes the shop's own stage is written on the
			// delivery channel, so the vocabulary is read from the step itself.
			$isDelivery = (string)$r->channel === 'delivery'
				&& in_array((string)$r->to_status, self::DELIVERY_STATES, true);
			$label = $isDelivery ? $this->deliveryWord($r->to_status) : $this->order_lifecycle_model->label($r->to_status);
			$reason = trim((string)($r->reason ?? ''));
			$rows[] = [
				'when'   => (string)$r->created_at,
				'text'   => $label . ($reason !== '' ? ' — ' . $reason : ''),
				'actor'  => trim((string)($r->actor ?? '')),
				'kind'   => $isDelivery ? 'delivery' : 'stage',
				// The step itself, so a caller that needs to line a time up with
				// a known stage (the customer's track page) can do so without
				// reading the shop's wording back into a status.
				'code'   => (string)$r->to_status,
				'reason' => $reason,
			];
		}
		return $rows;
	}

	/** The delivery leg's own line in the order's journey table. */
	private function _recordDeliveryMove($storeId, $orderId, $from, $to, $reason, $channel){
		if(!$this->db->table_exists('db_order_status_log')) return;
		$actor = 'System';
		$user_id = function_exists('get_current_user_id') ? (int)get_current_user_id() : 0;
		if($user_id > 0 && function_exists('mp_first_name')) $actor = (string)mp_first_name($user_id);
		$this->db->insert('db_order_status_log', [
			'store_id'        => (int)$storeId,
			'online_order_id' => (int)$orderId,
			'sales_id'        => 0,
			'kitchen_order_id'=> 0,
			'from_status'     => $from,
			'to_status'       => $to,
			'reason'          => $reason !== '' ? $reason : null,
			'actor'           => mb_substr($actor, 0, 60),
			'channel'         => 'delivery',
			'created_at'      => date('Y-m-d H:i:s'),
		]);
		if(function_exists('mp_audit_log')){
			mp_audit_log('Orders', $this->deliveryWord($to), (int)$orderId,
				$this->deliveryWord($from) . ' → ' . $this->deliveryWord($to) . ($reason !== '' ? ' — ' . $reason : ''));
		}
	}

	/**
	 * Names of the Rx-only medicines in an order's cart (variant children
	 * inherit their parent's flag). Empty when none, or when the store has
	 * the rule switched off.
	 */
	public function rxItemNames($orderId, $storeId){
		$empty = ['names' => [], 'ids' => []];
		if(!$orderId || !$storeId) return $empty;
		if(!mp_feature_enabled_for_store('prescription_required', (int)$storeId)) return $empty;
		if(!$this->db->field_exists('prescription_required', 'db_items')) return $empty;
		$ids = [];
		foreach($this->getOrderItems($orderId) as $line){
			if((string)$line->item_type === 'product' && (int)$line->item_id > 0){
				$ids[] = (int)$line->item_id;
			}
		}
		if(!$ids) return $empty;
		$placeholders = implode(',', array_fill(0, count($ids), '?'));
		$rows = $this->db->query(
			"SELECT i.id, i.item_name, IFNULL(pi.prescription_required,0) AS parent_required, IFNULL(i.prescription_required,0) AS self_required
			   FROM db_items i
			   LEFT JOIN db_items pi ON pi.id = i.parent_id
			  WHERE i.store_id = ? AND i.id IN ($placeholders)",
			array_merge([(int)$storeId], $ids))->result();
		$names = [];
		$ids_by_name = [];
		foreach($rows as $row){
			if((int)$row->self_required === 1 || (int)$row->parent_required === 1){
				$names[] = trim((string)$row->item_name) !== '' ? $row->item_name : ('Item #'.$row->id);
				$ids_by_name[(int)$row->id] = true;
			}
		}
		if(!$names) return $empty;
		return ['names' => $names, 'ids' => array_keys($ids_by_name)];
	}

	/**
	 * The prescription review record raised for an online order, whatever the
	 * customer attached through checkout (linked note, or an uploaded file
	 * from before the review flow existed).
	 */
	public function findRxNote($orderId, $storeId){
		if(!$orderId || !$storeId || !$this->db->table_exists('db_medical_notes')) return null;
		$note = null;
		if($this->db->field_exists('online_order_id', 'db_medical_notes')){
			$note = $this->db->where('online_order_id', (int)$orderId)->where('store_id', (int)$storeId)
				->order_by('id', 'desc')->get('db_medical_notes')->row();
		}
		if(!$note && $this->db->field_exists('prescription_ref', 'db_medical_notes')){
			$note = $this->db->where('store_id', (int)$storeId)
				->where("prescription_ref IN ('ONLINE-".$orderId."', 'ONLINE#".$orderId."')")
				->order_by('id', 'desc')->get('db_medical_notes')->row();
		}
		return $note ? $this->attachRxReviewer($note) : null;
	}

	/**
	 * reviewed_by holds the staff row, not the person's name — so say who it was
	 * by first name, and fall back to the login name for rows written before the
	 * column carried an id.
	 */
	private function attachRxReviewer($note){
		$note->reviewed_by_name = null;
		if(empty($note->reviewed_by) || !$this->db->table_exists('db_users')) return $note;
		$user = $this->db->select('username, first_name')->where('id', (int)$note->reviewed_by)
			->get('db_users')->row();
		if($user){
			$note->reviewed_by_name = trim((string)($user->first_name ?? '')) !== ''
				? $user->first_name : $user->username;
		}
		return $note;
	}

	/** May this order's Rx medicines be dispensed? Unflagged orders: yes. */
	public function rxApprovedForOrder($orderId, $storeId){
		$rx = $this->rxItemNames($orderId, $storeId);
		if(empty($rx['ids'])) return true;
		$note = $this->findRxNote($orderId, $storeId);
		return $note && (string)($note->review_status ?? '') === 'approved';
	}

	/**
	 * Mint the customer's pay-later link for an existing order. Only the SHA-256
	 * of the secret is stored (same convention as db_quotation.response_token_hash),
	 * so a leaked dump cannot mint live URLs. Re-issuing replaces the secret,
	 * which is what keeps one order to one live amount.
	 */
	public function issuePayLink($orderId, $storeId, $ttlHours = 168){
		$order = $this->getOrder($orderId);
		if(!$order || (int)$order->store_id !== (int)$storeId) return null;
		$token = bin2hex(random_bytes(16));
		$hash = hash('sha256', $token);
		$expires = date('Y-m-d H:i:s', time() + max(1, (int)$ttlHours) * 3600);
		$this->db->where('id', (int)$orderId)->where('store_id', (int)$storeId)->update('db_online_orders', [
			'pay_link_token_hash' => $hash,
			'pay_link_expires_at' => $expires,
			'pay_link_sent_at'    => date('Y-m-d H:i:s'),
		]);
		$saved = $this->db->select('pay_link_token_hash')->where('id', (int)$orderId)
			->where('store_id', (int)$storeId)->get('db_online_orders')->row();
		if(!$saved || (string)$saved->pay_link_token_hash !== $hash){
			log_message('error', 'Could not store the payment link for online order '.$orderId.'.');
			return null;
		}
		return ['token' => $token, 'expires_at' => $expires, 'url' => base_url('pay/'.$token)];
	}

	/** Find the order a presented link secret belongs to, store-scoped by the row. */
	public function payLinkOrder($token){
		if(!preg_match('/^[a-f0-9]{32}$/', (string)$token)) return null;
		$row = $this->db->where('pay_link_token_hash', hash('sha256', (string)$token))
			->get('db_online_orders')->row();
		return $row ?: null;
	}

	/** Is this link still a way to pay, or does the page have to explain itself? */
	public function payLinkState($order){
		if(!$order) return 'missing';
		if((string)$order->payment_status === 'paid') return 'paid';
		if((string)$order->payment_status === 'refunded') return 'refunded';
		if(in_array((string)$order->order_status, ['cancelled', 'completed'], true)) return 'closed';
		if(empty($order->pay_link_expires_at) || strtotime((string)$order->pay_link_expires_at) < time()) return 'expired';
		if((float)$order->grand_total <= 0) return 'closed';
		// Money that has already reached the till is not the invoiced total any
		// more. Asking the customer to pay the full amount again over this link
		// would charge them twice, so the link stops offering a card button.
		if($this->db->table_exists('db_online_order_payments')){
			$in = $this->db->select('COALESCE(SUM(amount),0) AS t', false)
				->where('store_id', (int)$order->store_id)->where('online_order_id', (int)$order->id)
				->where('status', 'verified')->get('db_online_order_payments')->row();
			if((float)($in->t ?? 0) > 0.005) return 'partial';
		}
		return 'payable';
	}

	/**
	 * The words a customer is sent when their order becomes payable. One amount,
	 * which already contains the delivery charge they were quoted at checkout,
	 * and the link. Public because "the invoice includes delivery" has to be
	 * checkable without sending a message to a real person.
	 */
	public function buildInvoiceMessage($order, $storeName, $url){
		$cur = $this->db->query("SELECT a.currency AS symbol FROM db_currency a INNER JOIN db_store b ON b.currency_id = a.id WHERE b.id = ? LIMIT 1",
			[(int)$order->store_id])->row();
		$currency = trim((string)($cur->symbol ?? ''));
		$amount = trim($currency . ' ' . store_number_format((float)$order->grand_total));
		$delivery = (float)$order->delivery_fee > 0
			? ' (includes ' . trim($currency . ' ' . store_number_format((float)$order->delivery_fee)) . ' delivery)'
			: '';
		return [
			'amount' => $amount,
			'email' => [
				'customer_name'  => htmlspecialchars((string)$order->customer_name),
				'invoice_number' => $order->order_code,
				'amount_due'     => htmlspecialchars($amount . $delivery),
				'payment_link'   => $url,
				'store_name'     => htmlspecialchars($storeName),
			],
			'sms' => $storeName . ': your prescription is approved for order ' . $order->order_code
				. '. Pay ' . $amount . $delivery . ' here: ' . $url
				. ' (valid until ' . date('d M Y', strtotime((string)$order->pay_link_expires_at)) . ')',
		];
	}

	/**
	 * Invoice an approved order on the channels the customer already gave us.
	 * Deliberately best-effort per channel: an SMS provider that is not
	 * configured must not fail the pharmacist's approval, and the reply has to
	 * say in plain English what actually went out.
	 */
	public function sendOrderInvoiceLink($orderId, $storeId){
		$order = $this->getOrder($orderId);
		if(!$order || (int)$order->store_id !== (int)$storeId){
			return ['ok' => false, 'channels' => [], 'message' => 'That order is not one of yours.'];
		}
		$link = $this->issuePayLink($orderId, $storeId);
		if(!$link){
			return ['ok' => false, 'channels' => [], 'message' => 'The payment link could not be created. Try again or take payment at the counter.'];
		}
		$store = function_exists('get_store_details') ? get_store_details($storeId) : null;
		$order->pay_link_expires_at = $link['expires_at'];
		$parts = $this->buildInvoiceMessage($order, trim((string)(($store->store_name ?? 'the store'))) ?: 'the store', $link['url']);
		$channels = [];
		$silent = [];

		$email = trim((string)($order->customer_email ?? ''));
		if(filter_var($email, FILTER_VALIDATE_EMAIL)){
			try {
				$this->load->model('email_service');
				$this->load->model('email_settings_model');
				$this->load->model('email_template_model');
				$this->email_service->setStoreId($storeId);
				if(!$this->email_settings_model->isReady($storeId)){
					$silent[] = 'email is not set up for this store';
				} else {
					$this->email_template_model->seedDefaults($storeId);
					$res = $this->email_service->sendTemplate('payment_link_sent', $email, $parts['email'],
						['related_module' => 'storefront', 'related_record_id' => (int)$orderId]);
					if(!empty($res['success'])) $channels[] = 'email'; else $silent[] = 'the email was refused';
				}
			} catch (Throwable $e) {
				log_message('error', 'Invoice email failed for online order '.$orderId.': '.$e->getMessage());
				$silent[] = 'the email could not be sent';
			}
		} else {
			$silent[] = $email === '' ? 'no email address on the order' : 'the email address on the order is not valid';
		}

		$phone = preg_replace('/[^0-9]/', '', (string)($order->customer_phone ?? ''));
		if($phone !== ''){
			try {
				$this->load->library('transactional_sms');
				if($this->transactional_sms->send($storeId, $phone, $parts['sms'], 'online_rx_invoice',
					'online-order:' . (int)$storeId . ':' . (int)$orderId . ':invoice-' . substr($link['token'], 0, 8))){
					$channels[] = 'sms';
				} else {
					$silent[] = 'text messages are not set up for this store';
				}
			} catch (Throwable $e) {
				log_message('error', 'Invoice SMS failed for online order '.$orderId.': '.$e->getMessage());
				$silent[] = 'the text could not be sent';
			}
		} else {
			$silent[] = 'no phone number on the order';
		}

		$message = $channels
			? 'Payment link sent by ' . implode(' and ', $channels) . ', valid until ' . date('d M Y H:i', strtotime($link['expires_at'])) . '.'
			: 'Payment link created, but nothing reached the customer: ' . implode(', ', $silent) . '. Give them the link or take payment at the counter.';
		return ['ok' => (bool)$channels, 'channels' => $channels, 'message' => $message,
			'url' => $link['url'], 'expires_at' => $link['expires_at']];
	}

	public function updatePaymentStatus($orderId, $status, $data = []){
		$this->db->trans_begin();
		try {
			if ($status==='refunded' && $this->db->table_exists('db_service_fulfilment_sources')) {
				$order=$this->getOrder($orderId);if (!$order) throw new RuntimeException('Order not found.');
				// Product-only restaurant orders can share the service table with
				// other industries. Only invoke service cancellation when this
				// order actually owns service-fulfilment source rows.
				$hasServiceSources = $this->db->where('store_id',(int)$order->store_id)
					->where('source_type','online')->where('source_parent_id',(int)$orderId)
					->count_all_results('db_service_fulfilment_sources') > 0;
				if($hasServiceSources){
					$this->load->model('service_fulfilment_model');
					if (!$this->service_fulfilment_model->cancel_online_work($orderId,$order->store_id)) throw new RuntimeException('Could not cancel service work.');
				}
			}
			$data['payment_status'] = $status;
			$data['updated_at'] = date('Y-m-d H:i:s');
			if (!$this->db->where('id', $orderId)->update('db_online_orders', $data) || $this->db->trans_status()===false) throw new RuntimeException('Could not update payment status.');
			$this->db->trans_commit();return true;
		} catch (Throwable $e) { $this->db->trans_rollback();log_message('error','Payment status update: '.$e->getMessage());return false; }
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
		$sourceWarehouseId = (int)($order->source_warehouse_id ?? 0);
		if($sourceWarehouseId <= 0) $sourceWarehouseId = (int)($order->warehouse_id ?? 0);
		if(!$this->_writeOrderStockLedger($orderId, $order->store_id, $draws, -1, 'commit', $sourceWarehouseId) || $this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return false;
		}
		$this->db->trans_commit();
		return true;
	}

	private function _writeOrderStockLedger($orderId, $storeId, array $lines, $direction, $action, $warehouseId = null){
		if(empty($lines)) return true;
		// Stock moves at the outlet the customer ordered from. Orders placed
		// before locations existed (or by a store running a single till) keep
		// using the store's own warehouse, so nothing regresses.
		if((int)$warehouseId <= 0){
			$warehouseId = function_exists('get_store_warehouse_id') ? get_store_warehouse_id() : null;
		}
		$ledger = [
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
		];
		if($this->db->field_exists('source_order_id', 'db_stockadjustment')) $ledger['source_order_id'] = (int)$orderId;
		if($this->db->field_exists('source_action', 'db_stockadjustment')) $ledger['source_action'] = (string)$action;
		if(!$this->db->insert('db_stockadjustment', $ledger)) return false;
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
		// The outlet's own on-hand figure is derived from the same ledger rows,
		// so it has to be recomputed here or the till looks right while the
		// kitchen's outlet still thinks it has the stock.
		if((int)$warehouseId > 0 && function_exists('update_warehousewise_items_qty')){
			foreach(array_keys($itemIds) as $itemId){
				if(!update_warehousewise_items_qty($itemId, (int)$warehouseId, (int)$storeId)) return false;
			}
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
	 * Releases and commits go through the same state row, so an abandoned
	 * payment gives the lots back and a paid order turns them into the sale's
	 * batch provenance without moving any quantity twice.
	 */
	public function reserveOrderItems($orderId, $items, $allowBackorder = false){
		$this->last_reservation_error = '';
		$reserved = [];
		foreach($items as $item){
			if(($item['item_type'] ?? '') !== 'product') continue;
			$qty = (int)$item['qty'];
			if($qty <= 0) continue;
			$reserved[] = ['item_id' => (int)$item['item_id'], 'qty' => $qty];
		}
		if(empty($reserved)) return true;

		$this->db->trans_begin();
		$order = $this->db->query('SELECT * FROM db_online_orders WHERE id = ? FOR UPDATE', [(int)$orderId])->row();
		if(!$order){
			$this->db->trans_rollback();
			return false;
		}
		try {
			$warehouseId = $this->orderStockSource((int)$order->store_id, (int)($order->warehouse_id ?? 0), (int)($order->source_warehouse_id ?? 0));
		} catch(RuntimeException $e){
			$this->last_reservation_error = $e->getMessage(); $this->db->trans_rollback(); return false;
		}
		$needed = [];
		foreach($reserved as $r) $needed[$r['item_id']] = ($needed[$r['item_id']] ?? 0) + $r['qty'];
		ksort($needed);
		$branchAllocated = false;
		if($warehouseId > 0 && $this->db->table_exists('db_warehouseitems')){
			$branchAllocated = $this->db->where('store_id', (int)$order->store_id)
				->count_all_results('db_warehouseitems') > 0;
		}
		$profile = function_exists('mp_get_store_profile') ? mp_get_store_profile((int)$order->store_id) : [];
		$isPharmacy = function_exists('mp_pharmacy_industry')
			? mp_pharmacy_industry($profile['industry_type'] ?? '') : mp_pharmacy_standard((int)$order->store_id);
		$checkBranchStock = $warehouseId > 0 && $this->db->table_exists('db_warehouseitems')
			&& ($branchAllocated || $isPharmacy);
		if($checkBranchStock){
			foreach($needed as $itemId => $qty){
				$row = $this->db->query('SELECT available_qty FROM db_warehouseitems WHERE store_id = ? AND warehouse_id = ? AND item_id = ? FOR UPDATE', [(int)$order->store_id, $warehouseId, $itemId])->row();
				if(!$allowBackorder && (!$row || (float)$row->available_qty < $qty)){
					$this->last_reservation_error = 'There is not enough stock at the selected location. Please review your cart.';
					$this->db->trans_rollback(); return false;
				}
			}
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

		// Hold the exact lots this order will be served from. The counter draws
		// from the same ledger, so without this an online basket and a till sale
		// could both be promised the same three packets — and the paid order
		// would then reach the ledger with no batch left to allocate.
		$this->load->model('inventory_rotation_model');
		if($this->inventory_rotation_model->holds_installed()
			&& $this->inventory_rotation_model->sells_from_lot_ledger((int)$order->store_id)){
			$holdWarehouse = $warehouseId;
			if($holdWarehouse <= 0){
				$locations = $this->online_stock_locations((int)$order->store_id);
				$holdWarehouse = (int)($locations[0] ?? 0);
			}
			$holdLines = [];
			foreach($this->getOrderItems($orderId) as $line){
				if((string)$line->item_type === 'product' && (float)$line->qty > 0){
					$holdLines[] = [(int)$line->id, (int)$line->item_id, (float)$line->qty, (string)($line->item_name ?? 'this item')];
				}
			}
			foreach($holdLines as $hold){
				$err = $this->inventory_rotation_model->reserve_lots(
					(int)$orderId, $hold[0], (int)$order->store_id, $holdWarehouse, $hold[1], $hold[2], $hold[3]);
				if($err !== ''){
					$this->last_reservation_error = $err;
					$this->db->trans_rollback();
					return false;
				}
			}
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
		if(!$this->_writeOrderStockLedger($orderId, $order->store_id, $reserved, -1, 'reserve', $warehouseId) || $this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return false;
		}
		$this->db->trans_commit();
		return true;
	}

	/**
	 * Take the batches an order will be served from, for a channel that has
	 * already consumed the stock figure but has not rung up a sale yet — a
	 * staff-taken WhatsApp order, for instance. Without this the counter can
	 * sell units this order is holding, and the order then reaches the ledger
	 * with no lot left to give it.
	 */
	public function hold_order_batches($orderId){
		$this->last_reservation_error = '';
		$order = $this->getOrder((int)$orderId);
		if(!$order) return false;
		$this->load->model('inventory_rotation_model');
		if(!$this->inventory_rotation_model->holds_installed()) return true;
		if(!$this->inventory_rotation_model->sells_from_lot_ledger((int)$order->store_id)) return true;
		$warehouse = (int)($order->source_warehouse_id ?? 0) ?: (int)($order->warehouse_id ?? 0);
		if($warehouse <= 0){
			$locations = $this->online_stock_locations((int)$order->store_id);
			$warehouse = (int)($locations[0] ?? 0);
		}
		$this->db->trans_begin();
		foreach($this->getOrderItems($orderId) as $line){
			if((string)$line->item_type !== 'product' || (float)$line->qty <= 0) continue;
			$err = $this->inventory_rotation_model->reserve_lots((int)$orderId, (int)$line->id,
				(int)$order->store_id, $warehouse, (int)$line->item_id, (float)$line->qty,
				(string)($line->item_name ?? 'this item'));
			if($err !== ''){
				$this->last_reservation_error = $err;
				$this->db->trans_rollback();
				return false;
			}
		}
		if($this->db->trans_status() === false){
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
		// Event seats are not warehouse stock: release them here whatever the
		// physical stock_state says. Paid orders give their seats back through
		// ticket voids; never-issued lines through the per-line claim. Both are
		// atomic, so duplicate cancels/expiries release exactly once.
		if($this->db->table_exists('db_event_tickets')){
			$this->load->model('events_model');
			$this->events_model->void_tickets_for_order((int)$orderId, (int)$order->store_id);
			$this->events_model->release_line_seats((int)$orderId, (int)$order->store_id);
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
		// When the order already reached the sales ledger its lot allocation
		// must be undone with the release, or the batch screen would show the
		// returned units as sold while the shelf has them back.
		if((int)($order->sales_id ?? 0) > 0 && $this->db->table_exists('db_salesitem_batch_allocations')){
			$this->load->model('inventory_rotation_model');
			if(!$this->inventory_rotation_model->restore_sale((int)$order->sales_id)){
				$this->db->trans_rollback();
				return false;
			}
		}
		// Lots still held by an order that never reached the ledger go straight
		// back on the shelf. Once an order has posted, its batches are sale
		// allocations and restore_sale() above has already returned them.
		$this->load->model('inventory_rotation_model');
		if(!$this->inventory_rotation_model->release_holds((int)$orderId)){
			$this->db->trans_rollback();
			return false;
		}
		$releaseLines = [];
		foreach($this->getOrderItems($orderId) as $item){
			if(!in_array($item->item_type, ['product', 'bundle_component'], true)) continue;
			$releaseLines[] = ['item_id' => (int)$item->item_id, 'qty' => (float)$item->qty];
		}
		$sourceWarehouseId = (int)($order->source_warehouse_id ?? 0);
		if($sourceWarehouseId <= 0) $sourceWarehouseId = (int)($order->warehouse_id ?? 0);
		if(!$this->_writeOrderStockLedger($orderId, $order->store_id, $releaseLines, 1, 'release', $sourceWarehouseId)){
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
	 * Mark reserved/committed food as wasted without returning it to sellable
	 * stock. The reservation/commit movement already reduced stock; this only
	 * claims the order state and records the physical disposition once.
	 */
	public function disposeStock($orderId, $reason = 'Prepared food disposed'){
		$order = $this->getOrder($orderId);
		if(!$order || !$this->db->field_exists('stock_state', 'db_online_orders')) return false;
		$tableProbe = $this->db->query("SHOW TABLES LIKE 'db_order_stock_dispositions'");
		if(!$tableProbe || $tableProbe->num_rows() < 1) return false;
		if((string)$order->stock_state === 'wasted') return true;
		if(!in_array((string)$order->stock_state, ['reserved','committed'], true)) return false;
		$wasteState = ['stock_state' => 'wasted', 'stock_adjusted' => 0];
		if($this->db->field_exists('updated_at', 'db_online_orders')) $wasteState['updated_at'] = date('Y-m-d H:i:s');
		$set = "stock_state = 'wasted', stock_adjusted = 0";
		if(isset($wasteState['updated_at'])) $set .= ", updated_at = " . $this->db->escape($wasteState['updated_at']);
		$updated = $this->db->query(
			"UPDATE db_online_orders SET {$set} WHERE id = ? AND stock_state IN ('reserved','committed')",
			[(int)$orderId]
		);
		if(!$updated){
			return false;
		}
		$claimed = $this->getOrder($orderId);
		if(!$claimed || (string)$claimed->stock_state !== 'wasted'){
			return false;
		}
		foreach($this->getOrderItems($orderId) as $item){
			if(!in_array($item->item_type, ['product','bundle_component'], true)) continue;
			$this->db->insert('db_order_stock_dispositions', [
				'store_id' => (int)$order->store_id,
				'online_order_id' => (int)$orderId,
				'item_id' => (int)$item->item_id,
				'quantity' => (float)$item->qty,
				'disposition' => 'wasted',
				'reason' => mb_substr(trim((string)$reason), 0, 255),
				'created_by' => (string)($this->session->userdata('inv_username') ?? 'System'),
				'created_at' => date('Y-m-d H:i:s'),
			]);
		}
		if($this->db->error()['code'] ?? 0) return false;
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

	/** Only a live, store-scoped portal session grants customer pricing/history. */
	public function verifiedCustomer($storeId){
		if(!method_exists($this->input, 'cookie')) return null;
		$token = (string)$this->input->cookie('customer_token', TRUE);
		if(!preg_match('/^[a-f0-9]{64}$/', $token)) return null;
		$key = (int)$storeId . ':' . $token;
		if(array_key_exists($key, $this->verifiedCustomers)) return $this->verifiedCustomers[$key];
		$session = $this->getCustomerPortalSession($token, (int)$storeId);
		if(!$session) return $this->verifiedCustomers[$key] = null;
		return $this->verifiedCustomers[$key] = $this->db->where('id', (int)$session->customer_id)->where('store_id', (int)$storeId)
			->where('status', 1)->get('db_customers')->row();
	}

	/** Shared with the POS customer policy; catalogue prices are never rewritten. */
	public function productPriceQuote($product, $customerId = null){
		$prod = (object)$product;
		$storeId = (int)($prod->store_id ?? get_current_store_id());
		if($customerId === null){
			$customer = $this->verifiedCustomer($storeId);
			$customerId = $customer ? (int)$customer->id : 0;
		}
		$this->load->model('customer_pricing_model', 'customer_pricing');
		$policy = $this->customer_pricing->policy($storeId, (int)$customerId);
		$online = (float)($prod->online_price ?? 0);
		$retail = (float)($prod->mrp ?? 0);
		$wholesale = (float)($prod->sales_price ?? 0);
		$base = $online > 0 ? $online : ($retail > 0 ? $retail : $wholesale);
		$tier = $online > 0 ? 'online' : ($retail > 0 ? 'retail' : 'wholesale');
		if(!empty($prod->is_bundle)){
			$this->load->model('Extras_model', 'extras_model');
			$base = $this->extras_model->bundleUnitPrice($prod, $storeId);
			$policy = array_merge($policy, ['source'=>'normal', 'group_id'=>null, 'name'=>'Bundle price']);
			$tier = 'bundle';
		}
		$price = $base;
		$originalPrice = $base;
		if($policy['source'] === 'group'){
			if($wholesale <= 0) throw new RuntimeException('A Wholesale price is required for the member price of ' . ($prod->item_name ?? 'this item') . '. Please contact the store.');
			$base = $wholesale; $originalPrice = $wholesale; $tier = 'wholesale';
			$price = Customer_pricing_model::adjust($base, $policy['direction'], $policy['percent'], $policy['decimals']);
		} elseif(empty($prod->is_bundle)){
			if($policy['source'] === 'customer') $price = Customer_pricing_model::adjust($base, $policy['direction'], $policy['percent'], $policy['decimals']);
			$discount = (float)($prod->discount ?? 0);
			if($discount > 0) $price -= strtolower((string)($prod->discount_type ?? '')) === 'percentage' ? $price * $discount / 100 : $discount;
			$price = max(0, $price);
			if(!empty($prod->id) && $this->db->table_exists('db_promotions')){
				$this->load->model('promotions_model');
				try {
					$promo = $this->promotions_model->compute_effective_price((int)$prod->id, $price, $storeId);
					if(!empty($promo['has_promo'])) $price = (float)$promo['price'];
				} catch(Throwable $e) { /* Keep the verified catalogue rule if a legacy promotion schema is incomplete. */ }
			}
		}
		$quote = array_merge($policy, ['store_id'=>$storeId, 'customer_id'=>(int)$customerId,
			'item_id'=>(int)($prod->id ?? 0), 'base'=>(float)$base, 'original_price'=>(float)$originalPrice,
			'price'=>max(0, round($price, $policy['decimals'])), 'effective_tier'=>$tier]);
		$quote['version'] = hash('sha256', json_encode($quote));
		return $quote;
	}

	public function getProductEffectivePrice($product){
		if(!is_object($product) && !is_array($product)) return 0.0;
		return $this->productPriceQuote($product)['price'];
	}

	public function getProductOriginalPrice($product){
		if(!is_object($product) && !is_array($product)) return 0.0;
		return $this->productPriceQuote($product)['original_price'];
	}

	/** Reject stale or tampered cart prices before creating an order or payment. */
	public function validateProductQuote($product, array $line){
		$q = $this->productPriceQuote($product);
		if(!isset($line['price']) || !is_scalar($line['price']) || !is_numeric($line['price'])
			|| !is_finite((float)$line['price']) || abs((float)$line['price'] - $q['price']) > 0.00001
			|| (!empty($line['pricing_version']) && !hash_equals($q['version'], (string)$line['pricing_version']))){
			throw new RuntimeException('Prices or membership have changed. Refresh your cart and review the total before placing the order.');
		}
		return $q;
	}

	public function verifiedHasGroup($storeId){
		$c = $this->verifiedCustomer($storeId);
		if(!$c) return false;
		$this->load->model('customer_pricing_model', 'customer_pricing');
		return $this->customer_pricing->policy($storeId, (int)$c->id)['source'] === 'group';
	}

	public function hasGroupPrice(array $lines){
		foreach($lines as $line){
			$line = (array)$line;
			if(($line['pricing_source'] ?? '') === 'group') return true;
			$snapshot = json_decode((string)($line['pricing_snapshot'] ?? ''), true);
			if(($snapshot['source'] ?? '') === 'group') return true;
		}
		return false;
	}

	/** Recipient outlet and physical stock source are distinct, tenant-owned records. */
	public function checkoutLocation($storeId, $settings, $requested, array $outlets, $requireChoice = true){
		$requested = (int)$requested;
		$outlet = null;
		if(mp_pharmacy_standard($storeId)){
			if($requested > 0) throw new RuntimeException('This pharmacy fulfils orders from its own store. Please refresh checkout.');
			$own = mp_pharmacy_own_warehouse_ids($storeId);
			$preferred = (int)get_pos_default_warehouse_id($storeId);
			$requested = in_array($preferred, $own, true) ? $preferred : (int)($own[0] ?? 0);
		} else {
			if($requested > 0){
				foreach($outlets as $o) if((int)$o->id === $requested) $outlet = $o;
				if(!$outlet) throw new RuntimeException('Please choose an available fulfilment location.');
			} elseif(count($outlets) > 1){
				if($requireChoice) throw new RuntimeException('Please choose the location that will fulfil this order.');
				return ['warehouse_id'=>0, 'source_warehouse_id'=>0, 'outlet'=>null, 'needs_location'=>true];
			} else {
				$outlet = $outlets[0] ?? null;
				$requested = $outlet ? (int)$outlet->id : (int)($settings->default_branch_id ?? 0);
				if(!$requested) $requested = (int)get_pos_default_warehouse_id($storeId);
				// Older single-location storefronts use the store-wide stock pool and
				// have no warehouse record. Preserve that valid setup.
				if(!$requested && !$outlets && !mp_pharmacy_standard($storeId)){
					return ['warehouse_id'=>0, 'source_warehouse_id'=>0, 'outlet'=>null];
				}
			}
		}
		if(!$requested) throw new RuntimeException('This store has no fulfilment stock location. Please contact the store.');
		$source = $this->orderStockSource($storeId, $requested);
		return ['warehouse_id'=>$requested, 'source_warehouse_id'=>$source, 'outlet'=>$outlet];
	}

	/** Reprice browser/recovered carts from raw catalogue data, never saved client prices. */
	public function quoteCart($storeId, $cart, $sourceId = 0){
		if(!is_array($cart) || count($cart) > 200) throw new RuntimeException('Please refresh your cart.');
		$this->load->model('Extras_model', 'extras_model');
		$out = []; $demand = [];
		foreach($cart as $line){
			if(!is_array($line)) throw new RuntimeException('Invalid cart item.');
			$id = (int)($line['id'] ?? 0); $qty = $line['qty'] ?? 1;
			if(!is_scalar($qty) || !is_numeric($qty) || !is_finite((float)$qty) || (float)$qty < 1 || (float)$qty != (int)$qty || (float)$qty > 1000000) throw new RuntimeException('Enter a valid whole quantity for each item.');
			$type = ($line['type'] ?? 'product') === 'service' ? 'service' : 'product';
			if($type === 'product'){
				$item = $this->getOnlineProduct($id, $storeId, $sourceId);
				if(!$item || ($item->item_group ?? '') === 'Variants') throw new RuntimeException('An item or selected variant is no longer available. Please update your cart.');
				if(!empty($item->requires_quote)) throw new RuntimeException('Please request a quotation for this item.');
				$error = $this->extras_model->qtyRuleError($item, (int)$qty);
				if($error) throw new RuntimeException($error);
				$q = $this->productPriceQuote($item);
				$line['pricing_source'] = $q['source']; $line['pricing_name'] = $q['name'];
				$line['pricing_version'] = $q['version'];
				$line['price'] = $q['price']; $line['stock'] = (float)$item->stock;
				$line['name'] = $item->item_name; $line['image'] = $item->item_image;
				if(empty($item->is_bundle) && ($item->product_type ?? 'physical') === 'physical'){
					$demand[$id] = ($demand[$id] ?? 0) + (int)$qty;
					$line['available'] = $demand[$id] <= (float)$item->stock;
				}
			} else {
				$item = $this->getOnlineService($id, $storeId);
				if(!$item) throw new RuntimeException('A service is no longer available.');
				$line['price'] = $this->getServiceEffectivePrice($item);
				$line['name'] = $item->service_name; $line['image'] = $item->service_image ?? '';
				$line['pricing_source'] = 'normal'; $line['pricing_name'] = '';
			}
			$selections = $line['addons'] ?? [];
			list($addons, $error) = $this->extras_model->resolveAddonSelections($type, $id, $selections, $storeId);
			if($error) throw new RuntimeException($error);
			$line['addons'] = array_map(function($a){ return ['id'=>(int)$a->id, 'name'=>(string)$a->name, 'price'=>(float)$a->price, 'qty'=>(int)$a->resolved_qty]; }, $addons);
			$line['qty'] = (int)$qty; $out[] = $line;
		}
		return $out;
	}

	/** One physical source for availability, holds, movements, posting and release. */
	public function orderStockSource($storeId, $warehouseId, $sourceId = 0){
		$storeId = (int)$storeId; $warehouseId = (int)$warehouseId; $sourceId = (int)$sourceId;
		if($warehouseId <= 0 && $sourceId <= 0) return 0; // historical pooled orders
		$outlet = $this->db->where('id', $warehouseId)->where('store_id', $storeId)->where('status', 1)->get('db_warehouse')->row();
		if(!$outlet) throw new RuntimeException('This fulfilment location is unavailable. Please choose another location.');
		$resolved = $sourceId ?: (int)($outlet->stock_source_id ?? 0);
		$resolved = $resolved ?: $warehouseId;
		$source = $this->db->where('id', $resolved)->where('store_id', $storeId)->where('status', 1)->get('db_warehouse')->row();
		if(!$source || (string)($source->branch_type ?? '') === 'pickup_hub') throw new RuntimeException('This location has no available stock source. Please contact the store.');
		if(mp_pharmacy_standard($storeId) && (!in_array($warehouseId, mp_pharmacy_own_warehouse_ids($storeId), true)
			|| !in_array($resolved, mp_pharmacy_own_warehouse_ids($storeId), true))) throw new RuntimeException('This pharmacy fulfils orders from its own stock.');
		return $resolved;
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
		if($this->db->field_exists('theme_source', 'db_storefront_themes')){
			$this->db->group_start()->where('theme_source', 'builtin')->or_where('theme_source IS NULL', null, false)->group_end();
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
	public function getThemesByIndustryForStore($industry_type = null, $asObjects = true, $storeId = null){
		if(empty($industry_type)){
			if(!function_exists('mp_get_store_profile')){
				$this->load->helper('business_profile');
			}
			$profile = function_exists('mp_get_store_profile') ? mp_get_store_profile() : [];
			$industry_type = $profile['industry_type'] ?? 'general_retail';
		}

		$themeIndustry = $this->_getThemeIndustryForType($industry_type);
		$themes = $this->getThemesByIndustry($themeIndustry);

		// Building Materials has two deliberate, quote-led storefronts.  Keep
		// the legacy Hardware & Materials skin out of this store's selectable
		// and runtime theme set even before the retirement migration is run.
		if (strtolower(trim((string)$industry_type)) === 'building_materials') {
			$themes = array_values(array_filter($themes, function($theme){
				return (string)($theme->theme_key ?? '') !== 'hardware';
			}));
		}

		// Uploaded HTML themes are private to their tenant. They participate in
		// the same Theme_engine admission rules as built-ins, but are never added
		// to another merchant's selector or runtime theme set.
		$storeId = $storeId !== null ? (int)$storeId : (function_exists('get_current_store_id') ? (int)get_current_store_id() : 0);
		if($this->db->table_exists('db_storefront_themes') && $this->db->field_exists('theme_source', 'db_storefront_themes') && $storeId > 0){
			$this->db->where('status', 1)->where('theme_source', 'html_zip')->where('owner_store_id', $storeId);
			$uploaded = $this->db->order_by('created_at', 'desc')->get('db_storefront_themes')->result();
			$targetIndustry = $this->_normalizeThemeIndustry($themeIndustry);
			foreach($uploaded as $theme){
				if($this->_normalizeThemeIndustry($theme->industry ?? '') === $targetIndustry) $themes[] = $theme;
			}
		}

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
		if ($normalized === 'garden') return 'garden';
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
	/**
	 * Canonical theme-industry name for a raw industry/business-profile value.
	 *
	 * Public because Theme_engine has to make the same judgement when deciding
	 * whether a stored theme is legitimate for a store's industry, and two
	 * copies of this mapping would drift.
	 */
	public function normalizeThemeIndustry($industry){
		return $this->_normalizeThemeIndustry($industry);
	}

	private function _normalizeThemeIndustry($industry){
		$industry = strtolower(trim($industry));
		$industry = preg_replace('/[^a-z0-9]/', '', $industry);
		// Map legacy / verbose values to canonical names
		$map = [
			'generalretail' => 'general',
			'gardencentrelandscaping' => 'garden',
			'garden' => 'garden',
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
			['theme_key' => 'botanical_elegance', 'theme_name' => 'Botanical Elegance', 'industry' => 'garden', 'description' => 'Editorial garden retail with care details and service discovery.', 'default_primary_color' => '#24563E', 'default_secondary_color' => '#ACC6A4', 'default_font_family' => 'Inter', 'sort_order' => 46],
			['theme_key' => 'garden_marketplace', 'theme_name' => 'Garden Marketplace', 'industry' => 'garden', 'description' => 'Search-led garden commerce for plants, supplies and services.', 'default_primary_color' => '#367349', 'default_secondary_color' => '#E6B64B', 'default_font_family' => 'Inter', 'sort_order' => 47],
			['theme_key' => 'landscape_studio', 'theme_name' => 'Landscape Studio', 'industry' => 'garden', 'description' => 'A service-led garden studio with project and maintenance enquiries.', 'default_primary_color' => '#173F35', 'default_secondary_color' => '#C1B18B', 'default_font_family' => 'Inter', 'sort_order' => 48],
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
			['theme_key' => 'lumiere', 'theme_name' => 'Lumière', 'industry' => 'skincare', 'description' => 'Cream editorial skincare boutique with dark forest accents, serif headlines and clean product-led sections.', 'default_primary_color' => '#173A2B', 'default_secondary_color' => '#151713', 'default_font_family' => 'Cormorant Garamond', 'sort_order' => 43],
			['theme_key' => 'skinora', 'theme_name' => 'Skinora', 'industry' => 'skincare', 'description' => 'Blush premium skincare storefront with editorial serif type, soft rose surfaces and conversion-focused collections.', 'default_primary_color' => '#6A2730', 'default_secondary_color' => '#311D1E', 'default_font_family' => 'Playfair Display', 'sort_order' => 44],
			['theme_key' => 'bella_reve', 'theme_name' => 'Bella Rêve', 'industry' => 'skincare', 'description' => 'Dark luxury spa storefront with warm gold accents, treatment booking and premium product merchandising.', 'default_primary_color' => '#D3A74C', 'default_secondary_color' => '#10130D', 'default_font_family' => 'Cormorant Garamond', 'sort_order' => 45],
			// Building materials / architectural systems (hardware group)
			['theme_key' => 'framework_atelier', 'theme_name' => 'Framework Atelier', 'industry' => 'hardware', 'description' => 'Editorial architectural-systems catalogue with ivory surfaces, deep ink, serif display type and project-led enquiry journeys.', 'default_primary_color' => '#245C94', 'default_secondary_color' => '#142438', 'default_font_family' => 'Playfair Display', 'sort_order' => 49],
			['theme_key' => 'vector_systems', 'theme_name' => 'Vector Systems', 'industry' => 'hardware', 'description' => 'Precision industrial catalogue with cool silver surfaces, copper accents, Swiss-grid typography and technical specification tables.', 'default_primary_color' => '#E16D3B', 'default_secondary_color' => '#111820', 'default_font_family' => 'Manrope', 'sort_order' => 50],
		];
		foreach($themes as $t){
			$sql = $this->db->insert_string('db_storefront_themes', $t);
			$sql = preg_replace('/^INSERT INTO/i', 'INSERT IGNORE INTO', $sql);
			$this->db->query($sql);
		}

		// Retire the previous pharmacy storefront family. Keep the rows (rather
		// than deleting them) so existing appearance history and store settings
		// remain recoverable, but make every old pharmacy design unavailable to
		// selectors and runtime theme resolution.
		$this->db->where_in('theme_key', [
			'healthcare_pro', 'pharma_clinical', 'pharma_wellness',
			'pharma_care', 'pharma_mediq'
		])->update('db_storefront_themes', ['status' => 0]);

		// Normalize legacy industry values for built-in themes so the engine can group them correctly
		$canonical = [
			'general_retail' => 'general',
			'healthcare_pro' => 'pharmacy',
			'pharma_clinical' => 'pharmacy',
			'pharma_wellness' => 'pharmacy',
			'pharma_care' => 'pharmacy',
			'pharma_mediq' => 'pharmacy',
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
			'lumiere' => 'skincare',
			'skinora' => 'skincare',
			'bella_reve' => 'skincare',
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
			return $this->db->where('id', $bannerId)->where('store_id', get_current_store_id())->update('db_storefront_banners', $data);
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
	public function saveHomepageSectionMeta($storeId, $sectionKey, $title, $subtitle, $imageUrl = null, $eyebrow = null, $contentScope = null){
		$storeId = $storeId ?: get_current_store_id();
		$row = $this->db->where('store_id', $storeId)->where('section_key', $sectionKey)->get('db_storefront_homepage_sections')->row();
		if(!$row) return false;

		$cfg = [];
		if(!empty($row->config_json)){
			$cfg = json_decode($row->config_json, true);
			if(!is_array($cfg)) $cfg = [];
		}
		$rootCfg = $cfg;
		if($contentScope === 'building_materials'){
			$cfg = $rootCfg['industry_content']['building_materials'] ?? [];
			if(!is_array($cfg)) $cfg = [];
		}
		if($title !== '') $cfg['title'] = $title; else unset($cfg['title']);
		if($subtitle !== '') $cfg['subtitle'] = $subtitle; else unset($cfg['subtitle']);
		if($imageUrl !== null && in_array(preg_replace('/_\d+$/', '', $sectionKey), ['hero_banner','promo_banner','store_info','contact_section'], true)){
			if($imageUrl !== '') $cfg['image_url'] = $imageUrl; else unset($cfg['image_url']);
		}
		if($eyebrow !== null && in_array(preg_replace('/_\d+$/', '', $sectionKey), ['hero_banner','promo_banner'], true)){
			if($eyebrow !== '') $cfg['eyebrow'] = $eyebrow; else unset($cfg['eyebrow']);
		}

		if($contentScope === 'building_materials'){
			if(!isset($rootCfg['industry_content']) || !is_array($rootCfg['industry_content'])) $rootCfg['industry_content'] = [];
			$rootCfg['industry_content']['building_materials'] = $cfg;
			// Do not rename the shared row or alter another industry's copy.
			return $this->db->where('id', $row->id)->update('db_storefront_homepage_sections', ['config_json'=>json_encode($rootCfg)]);
		}
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

	/** Building-only opt-ins; never change another preset's layout or content. */
	public function saveBuildingHomepageLayout($storeId, $sectionKey, $enabled, $order){
		$baseKey = preg_replace('/_\d+$/', '', (string)$sectionKey);
		if(!in_array($baseKey,['trust_badges','promo_banner','featured_categories','featured_services','best_sellers','new_arrivals','brands','testimonials','instagram_gallery','faqs','whatsapp_cta','newsletter','store_hours'],true)) return false;
		$row = $this->db->where('store_id', $storeId)->where('section_key', $sectionKey)->get('db_storefront_homepage_sections')->row();
		if(!$row) return false;
		$cfg = json_decode($row->config_json ?? '', true);
		if(!is_array($cfg)) $cfg = [];
		if(!isset($cfg['industry_content']) || !is_array($cfg['industry_content'])) $cfg['industry_content'] = [];
		if(!isset($cfg['industry_content']['building_materials']) || !is_array($cfg['industry_content']['building_materials'])) $cfg['industry_content']['building_materials'] = [];
		$cfg['industry_content']['building_materials']['is_enabled'] = (int)!empty($enabled);
		$cfg['industry_content']['building_materials']['display_order'] = max(0,(int)$order);
		return $this->db->where('id',$row->id)->where('store_id',$storeId)->update('db_storefront_homepage_sections',['config_json'=>json_encode($cfg)]);
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
		$rows = $this->db->query("SELECT i2.id, i2.store_id, i2.item_name, i2.item_image, i2.sales_price, i2.mrp, i2.online_price, i2.discount_type, i2.discount, i2.stock, i2.description, i2.product_type, i2.is_new_arrival, i2.is_featured, i2.is_bundle, i2.bundle_pricing, i2.item_group, i2.parent_id, i2.child_bit, SUM(oi.qty) as sold_count
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

	public function getNewArrivals($storeId = null, $limit = 8, $fallbackToRecent = true, $includeVariantFlags = false){
		$storeId = $storeId ?: get_current_store_id();
		// Prefer manually flagged "New Arrival" cards (including variant parents).
		// Themes may opt out of the recent-product fallback when flags are explicit.
		$buildQuery = function($storeId, $limit, $flaggedOnly) use ($includeVariantFlags) {
			$this->db->select('a.id, a.store_id, a.category_id, a.item_name, a.item_image, a.sales_price, a.mrp, a.online_price, a.discount_type, a.discount, a.stock, a.description, a.product_type, a.is_new_arrival, a.is_featured, a.is_bundle, a.bundle_pricing, a.item_group, a.parent_id, a.child_bit, b.category_name');
			$this->db->from('db_items a');
			$this->db->join('db_category b', 'b.id=a.category_id', 'left');
			$this->db->where('a.store_id', $storeId);
			$this->db->where('a.publish_online', 1);
			$this->db->where('a.status', 1);
			$this->db->where('a.service_bit', 0);
			$this->db->where("(a.not_for_sale IS NULL OR a.not_for_sale = 0)", null, false);
			$this->db->where($this->_listedItemsWhere('a'), NULL, FALSE);
			if($flaggedOnly){
				if($includeVariantFlags){
					$this->db->where($this->_flaggedCardWhere('is_new_arrival', $storeId), NULL, FALSE);
				} else {
					$this->db->where('a.is_new_arrival', 1);
				}
			}
			$this->db->where($this->_expiredWhere('a', $storeId), NULL, FALSE);
			$this->db->order_by('a.id', 'desc');
			$this->db->limit($limit);
			return $this->_decorateVariantParents($this->db->get()->result(), $includeVariantFlags);
		};
		// Try flagged items first
		$results = $buildQuery($storeId, $limit, true);
		if(!empty($results)){
			return $results;
		}
		// Fallback: most recent published products
		return $fallbackToRecent ? $buildQuery($storeId, $limit, false) : [];
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
			$rows = $this->db->get('db_storefront_faqs')->result();
			if(!$enabledOnly) return $rows;
			return array_values(array_filter($rows, function($faq){
				return trim((string)$faq->question) !== '' && trim(strip_tags((string)$faq->answer)) !== '';
			}));
		} catch(Exception $e){ return []; }
	}

	public function saveStorefrontFaq($data, $id = null){
		if($id){
			$res = $this->db->where('id', $id)->where('store_id', $data['store_id'])->update('db_storefront_faqs', $data);
			return $res ? $id : false;
		}
		$res = $this->db->insert('db_storefront_faqs', $data);
		return $res ? $this->db->insert_id() : false;
	}

	public function deleteStorefrontFaq($id){
		return $this->db->where('id', $id)->where('store_id', get_current_store_id())->delete('db_storefront_faqs');
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

	/**
	 * Issue door codes for paid event tickets (migration 4.0.9.221).
	 * Idempotent per order line, so a replayed payment callback never
	 * double-issues. Seat capacity itself was already committed at order
	 * placement; this turns the held seats into scannable tickets.
	 */
	public function deliverEventTickets($orderId){
		$order = $this->getOrder($orderId);
		if(!$order || $order->payment_status !== 'paid') return false;
		if(!$this->db->table_exists('db_event_tickets')) return true;
		$this->load->model('events_model');
		foreach($this->getOrderItems($orderId) as $line){
			if(($line->item_type ?? '') !== 'event') continue;
			if(empty($line->event_session_id ?? null)){
				log_message('error', 'Event order line '.$line->id.' on order '.$orderId.' has no session — cannot issue tickets');
				continue;
			}
			$this->events_model->issue_tickets_for_order($order, $line, (int)$order->store_id);
		}
		return true;
	}

	public function completeIfNoPhysicalProducts($orderId){
		$items = $this->getOrderItems($orderId);
		if (!$items) return false;
		foreach($items as $item){
			// Only automatically delivered digital lines can auto-complete.
			// A paid service or bundle still needs physical/service fulfilment.
			if(!in_array($item->item_type, ['digital','course','membership'], true)) return false;
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
			if ($this->db->table_exists('db_service_fulfilment_sources')) {
				$this->load->model('service_fulfilment_model');
				$this->service_fulfilment_model->fulfil_online($orderId);
			}
			$this->deliverDigitalOrder($orderId);
			$this->deliverCourseAndMembership($orderId);
			$this->deliverEventTickets($orderId);
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
		$items = json_decode((string)($data['items_json'] ?? '[]'), true);
		$hasItems = is_array($items) && count($items) > 0;
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
			// A non-checkout snapshot must not erase contact details already
			// captured on checkout. An explicit empty string still clears a field.
			foreach(array('customer_name','customer_phone','customer_email','subtotal','coupon_code') as $field){
				if(!isset($data[$field])) unset($row[$field]);
			}
			$row['status'] = $hasItems ? 'active' : 'cleared';
			if($hasItems && $existing->status === 'cleared'){
				foreach(array('customer_name','customer_phone','customer_email','coupon_code') as $field){
					if(!isset($data[$field])) $row[$field] = null;
				}
				$row['reminder_count'] = 0;
				$row['reminder_sent_at'] = null;
			}
			$ok = $this->db->where('id', $existing->id)->update('db_storefront_carts', $row);
			return $ok ? (int)$existing->id : false;
		}
		if(!$hasItems) return 0;
		$row['store_id'] = (int)$storeId;
		$row['cart_token'] = $token;
		$row['created_at'] = date('Y-m-d H:i:s');
		$ok = $this->db->insert('db_storefront_carts', $row);
		return $ok ? (int)$this->db->insert_id() : false;
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
		if(!$cart || $cart->status !== 'active') return null;
		$now = date('Y-m-d H:i:s');
		$expiry = !empty($cart->expires_at) ? $cart->expires_at : date('Y-m-d H:i:s', strtotime($cart->created_at . ' +7 days'));
		if($expiry <= $now) return null;
		$items = json_decode((string)$cart->items_json, true);
		if(!is_array($items) || !$items) return null;
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
			->where("items_json <> '[]'", null, false)
			->where('(customer_phone IS NOT NULL OR customer_email IS NOT NULL)', null, false)
			->where('reminder_count <', 3);
		if($this->db->field_exists('expires_at', 'db_storefront_carts')){
			$this->db->where('(expires_at > NOW() OR (expires_at IS NULL AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)))', null, false);
		}
		if($this->db->field_exists('opt_out', 'db_storefront_carts')){
			$this->db->where('opt_out', 0);
		}
		return $this->db->order_by('last_activity', 'desc')->limit((int)$limit)
			->get('db_storefront_carts')->result();
	}

	/**
	 * Abandoned carts for automated recovery. The cron passes a store id so
	 * each merchant gets its own limit and a busy store cannot starve another.
	 * The caller still re-checks eligibility before sending.
	 */
	public function getAbandonedCartsForRecovery($hours = 24, $limit = 500, $storeId = null){
		if(!$this->db->table_exists('db_storefront_carts')) return array();
		$cutoff = date('Y-m-d H:i:s', time() - max(1, (int)$hours) * 3600);
		$this->db->where('status', 'active')
			->where('last_activity <', $cutoff)
			->where('items_json IS NOT NULL', null, false)
			->where("items_json <> '[]'", null, false)
			->where('(customer_phone IS NOT NULL OR customer_email IS NOT NULL)', null, false)
			->where('reminder_count <', 3);
		if($storeId !== null) $this->db->where('store_id', (int)$storeId);
		if($this->db->field_exists('expires_at', 'db_storefront_carts')){
			$this->db->where('(expires_at > NOW() OR (expires_at IS NULL AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)))', null, false);
		}
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
		if(trim((string)$cart->items_json) === '' || trim((string)$cart->items_json) === '[]') return false;
		$expiry = !empty($cart->expires_at)
			? strtotime((string)$cart->expires_at)
			: strtotime((string)$cart->created_at . ' +7 days');
		if(!$expiry || $expiry <= time()) return false;

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
