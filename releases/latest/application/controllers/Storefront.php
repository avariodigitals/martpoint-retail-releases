<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Storefront Controller - Public-facing online store
 * No login required. Customers browse and order.
 */
class Storefront extends CI_Controller {

	public function __construct(){
		parent::__construct();
		// Don't let browsers or intermediate proxies cache the storefront pages
		// so admin changes (featured products, prices, etc.) are visible immediately.
		header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
		$this->load->helper(['url','custom','currency','image']);
		$this->load->model('storefront_model');
		$this->load->model('customers_model');
		$this->load->model('paystack_model','paystack');
		$this->load->library('theme_engine');

		// Table QR: keep the scanned table number in session so it follows the customer
		// through browsing, the cart page and checkout.
		$table = trim($this->input->get('table'));
		if($table !== ''){
			$this->session->set_userdata('sf_table_number', $table);
			$this->table_number = $table;
		} else {
			$this->table_number = $this->session->userdata('sf_table_number') ?: '';
		}
	}

	/**
	 * Main storefront page
	 * URL: /store/{store_slug}
	 */
	public function index($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$canonical = base_url('store/' . $settings->store_slug);
		$businessProfile = mp_get_store_profile($storeId);
		$featuredVehicles = [];
		if(in_array('automobile_workflow', $businessProfile['features'] ?? [])){
			$this->load->model('Automobile_model', 'automobile_m');
			$featuredVehicles = $this->automobile_m->get_all($storeId, 'available', 6);
		}
		$allProducts = $this->storefront_model->getOnlineProducts($storeId, null, '', 500);
		$soldCounts = $this->storefront_model->getProductSoldCounts($storeId);
		foreach($allProducts as &$p){
			$p->effective_price = $this->storefront_model->getProductEffectivePrice($p);
			$p->original_price = !empty($p->is_bundle) ? $p->effective_price : $p->sales_price;
			$p->sold_count = $soldCounts[$p->id] ?? 0;
		}
		$data = [
			'settings' => $settings,
			'store' => $store,
			'categories' => $this->storefront_model->getCategoriesWithItems($storeId),
			'featured_categories' => $this->storefront_model->getCategoriesWithItems($storeId),
			'all_products' => $allProducts,
			'featured_products' => $this->storefront_model->getFeaturedProducts($storeId, $settings->featured_products_limit),
			'featured_vehicles' => $featuredVehicles,
			'featured_services' => $settings->allow_services ? $this->storefront_model->getOnlineServices($storeId, null, '', 8) : [],
			'best_sellers' => $this->storefront_model->getBestSellers($storeId, 8),
			'new_arrivals' => $this->storefront_model->getNewArrivals($storeId, 10),
			'paystack_enabled' => $this->paystack->is_enabled($storeId),
			'paystack_public_key' => '',
			'active_banners' => $this->theme_engine->activeBanners(),
			'hero_banners' => $this->theme_engine->activeBanners(5, 'hero'),
			'promo_banners' => $this->theme_engine->activeBanners(5, 'promo'),
			'homepage_sections' => $this->theme_engine->homepageSections(),
			'social_links' => $this->theme_engine->socialLinks(),
			'business_hours' => $this->theme_engine->businessHours(),
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
			'store_currency' => $this->theme_engine->getStoreCurrency(),
			'brands' => $this->theme_engine->storefrontBrands(),
			'testimonials' => $this->theme_engine->storefrontTestimonials(),
			'instagram_posts' => $this->theme_engine->storefrontInstagram(),
			'faqs' => $this->theme_engine->storefrontFaqs(),
			'seo_title' => $settings->meta_title ?: $store->store_name,
			'seo_description' => $settings->meta_description ?: $settings->store_description,
			'seo_image' => $this->theme_engine->logoUrl() ?: base_url('uploads/site/icon.webp'),
			'seo_canonical' => $canonical,
			'seo_type' => 'website',
		];

		foreach(['featured_products','best_sellers','new_arrivals'] as $key){
			foreach($data[$key] as &$p){ $p->sold_count = $soldCounts[$p->id] ?? 0; }
		}

		// Get Paystack public key if enabled
		if($data['paystack_enabled']){
			$ps = $this->paystack->get_settings($storeId);
			if($ps){
				$data['paystack_public_key'] = $ps->public_key ?? '';
				$data['paystack_test_mode'] = $ps->test_mode ?? 1;
			}
		}

		$this->theme_engine->view('store', $data);
	}

	/**
	 * Product listing with optional category filter
	 * URL: /store/{store_slug}/products?category={id}&search={term}
	 */
	public function products($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$categoryId = (int)$this->input->get('category');
		$search = trim($this->input->get('search'));
		$page = max(1, (int)$this->input->get('page'));
		$limit = 24;
		$offset = ($page - 1) * $limit;

		$total = $this->storefront_model->countOnlineProducts($storeId, $categoryId, $search);
		$products = $this->storefront_model->getOnlineProducts($storeId, $categoryId, $search, $limit, $offset);
		$soldCounts = $this->storefront_model->getProductSoldCounts($storeId);

		foreach($products as &$p){
			$p->effective_price = $this->storefront_model->getProductEffectivePrice($p);
			$p->original_price = !empty($p->is_bundle) ? $p->effective_price : $p->sales_price;
			$p->sold_count = $soldCounts[$p->id] ?? 0;
		}

		$categories = $this->storefront_model->getCategoriesWithItems($storeId);
		$catName = '';
		foreach($categories as $cat){ if($cat->id == $categoryId){ $catName = $cat->category_name; break; } }
		$seoTitle = $search ? ('Search: ' . $search) : ($catName ?: 'All Products');
		$canonical = base_url('store/' . $settings->store_slug . '/products');
		if($categoryId) $canonical .= '?category=' . $categoryId;
		elseif($search) $canonical .= '?search=' . urlencode($search);
		$data = [
			'settings' => $settings,
			'store' => $store,
			'products' => $products,
			'categories' => $categories,
			'category_id' => $categoryId,
			'search' => $search,
			'page' => $page,
			'limit' => $limit,
			'total' => $total,
			'total_pages' => ceil($total / $limit),
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
			'store_currency' => $this->theme_engine->getStoreCurrency(),
			'seo_title' => $seoTitle,
			'seo_description' => 'Browse ' . ($catName ?: 'our products') . ' at ' . ($store->store_name ?? 'our store'),
			'seo_image' => $this->theme_engine->logoUrl() ?: base_url('uploads/site/icon.webp'),
			'seo_canonical' => $canonical,
			'seo_type' => 'website',
		];
		$this->theme_engine->view('products', $data);
	}

	/**
	 * Service listing
	 * URL: /store/{store_slug}/services
	 */
	public function services($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		if(!$settings->allow_services){
			show_404();
			return;
		}
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$search = trim($this->input->get('search'));
		$categoryId = (int)$this->input->get('category');
		$page = max(1, (int)$this->input->get('page'));
		$limit = 24;
		$offset = ($page - 1) * $limit;

		$total = $this->storefront_model->countOnlineServices($storeId, $categoryId, $search);
		$services = $this->storefront_model->getOnlineServices($storeId, $categoryId, $search, $limit, $offset);

		foreach($services as &$s){
			$s->effective_price = $this->storefront_model->getServiceEffectivePrice($s);
		}

		$canonical = base_url('store/' . $settings->store_slug . '/services');
		if($categoryId) $canonical .= '?category=' . $categoryId;
		elseif($search) $canonical .= '?search=' . urlencode($search);
		$data = [
			'settings' => $settings,
			'store' => $store,
			'services' => $services,
			'service_categories' => $this->storefront_model->getServiceCategories($storeId),
			'category_id' => $categoryId,
			'search' => $search,
			'page' => $page,
			'limit' => $limit,
			'total' => $total,
			'total_pages' => ceil($total / $limit),
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
			'social_links' => $this->theme_engine->socialLinks(),
			'store_currency' => $this->theme_engine->getStoreCurrency(),
			'seo_title' => $search ? ('Search Services: ' . $search) : 'Our Services',
			'seo_description' => 'Book services at ' . ($store->store_name ?? 'our store'),
			'seo_image' => $this->theme_engine->logoUrl() ?: base_url('uploads/site/icon.webp'),
			'seo_canonical' => $canonical,
			'seo_type' => 'website',
		];
		$this->theme_engine->view('services', $data);
	}

	/**
	 * Single product page
	 * URL: /store/{store_slug}/product/{id}
	 */
	public function product($storeSlug = '', $productId = 0){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);
		$this->load->model('Extras_model', 'extras_model');

		$product = $this->storefront_model->getOnlineProduct($productId, $storeId);
		if(!$product){
			show_404();
			return;
		}
		$soldCounts = $this->storefront_model->getProductSoldCounts($storeId);
		$product->effective_price = $this->storefront_model->getProductEffectivePrice($product);
		$product->original_price = !empty($product->is_bundle) ? $product->effective_price : $product->sales_price;
		$product->sold_count = $soldCounts[$product->id] ?? 0;
		$productAddons = $this->extras_model->getAddonsForItem($productId, $storeId);

		$productImage = $product->item_image && file_exists($product->item_image) ? base_url($product->item_image) : ($this->theme_engine->logoUrl() ?: base_url('uploads/site/icon.webp'));

		$product_variants = [];
		// Variant parents show their children; a child page shows its siblings
		// (with itself marked) so every variant page has a working selector.
		$variantParentId = null;
		if ($product->item_group == 'Variants') {
			$variantParentId = $product->id;
		} elseif (!empty($product->parent_id)) {
			$variantParentId = (int)$product->parent_id;
		}
		if ($variantParentId) {
			$variants = $this->storefront_model->getProductVariants($variantParentId, $storeId);
			foreach ($variants as $v) {
				$v->effective_price = $this->storefront_model->getProductEffectivePrice($v);
				$v->original_price = $v->sales_price;
				$v->is_current = ((int)$v->id === (int)$product->id);
				$product_variants[] = $v;
			}
		}

		$relatedProducts = $this->storefront_model->getOnlineProducts($storeId, $product->category_id, '', 4);
		foreach($relatedProducts as &$rp){
			$rp->effective_price = $this->storefront_model->getProductEffectivePrice($rp);
				$rp->original_price = !empty($rp->is_bundle) ? $rp->effective_price : $rp->sales_price;
			$rp->sold_count = $soldCounts[$rp->id] ?? 0;
		}

		$data = [
			'settings' => $settings,
			'store' => $store,
			'product' => $product,
			'product_addons' => $productAddons,
			'product_variants' => $product_variants,
			'related_products' => $relatedProducts,
			'categories' => $this->storefront_model->getCategoriesWithItems($storeId),
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
			'social_links' => $this->theme_engine->socialLinks(),
			'store_currency' => $this->theme_engine->getStoreCurrency(),
			'seo_title' => $product->item_name,
			'seo_description' => substr(strip_tags($product->description ?? ''), 0, 160) ?: ('Buy ' . $product->item_name . ' at ' . ($store->store_name ?? 'our store')),
			'seo_image' => $productImage,
			'seo_canonical' => base_url('store/' . $settings->store_slug . '/product/' . $productId),
			'seo_type' => 'product',
			'seo_jsonld' => [
				'@context' => 'https://schema.org',
				'@type' => 'Product',
				'name' => $product->item_name,
				'image' => $productImage,
				'description' => strip_tags($product->description ?? ''),
				'offers' => [
					'@type' => 'Offer',
					'priceCurrency' => $this->theme_engine->getStoreCurrency()['code'] ?? 'NGN',
					'price' => number_format($product->effective_price, 2),
					'availability' => ((int)$product->stock > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
					'url' => base_url('store/' . $settings->store_slug . '/product/' . $productId)
				]
			]
		];
		if(!empty($settings->reviews_enabled)){
			$this->load->model('Reviews_model', 'reviews_model');
			$data['product_reviews_enabled'] = true;
			$data['product_reviews'] = $this->reviews_model->getPublicReviews($storeId, $productId);
			$data['product_review_aggregate'] = $this->reviews_model->getAggregate($storeId, $productId);
			$data['product_review_post_url'] = base_url('store/' . $settings->store_slug . '/product/' . (int)$productId . '/review');
			$data['review_csrf_name'] = $this->security->get_csrf_token_name();
			$data['review_csrf_hash'] = $this->security->get_csrf_hash();
		}
		$this->theme_engine->view('product_detail', $data);
	}

	public function submit_product_review($storeSlug = '', $productId = 0){
		$settings = $this->_getSettingsOr404($storeSlug);
		if(empty($settings->reviews_enabled)){
			show_404();
			return;
		}
		$this->load->model('Reviews_model', 'reviews_model');
		$data = $this->input->post(NULL, TRUE);
		$data['settings'] = $settings;
		$result = $this->reviews_model->submitReview(
			(int)$settings->store_id,
			(int)$productId,
			$data,
			$this->input->ip_address()
		);
		$this->output->set_content_type('application/json')->set_output(json_encode([
			'status' => $result[1] === null,
			'message' => $result[1] ?: (($result[2] ?? '') === 'pending' ? 'Thanks. Your review is awaiting approval.' : 'Thanks for your review.'),
			'review_status' => $result[2] ?? null,
			'verified' => $result[3] ?? false,
			'csrf_hash' => $this->security->get_csrf_hash(),
		]));
	}

	/**
	 * Single service page
	 * URL: /store/{store_slug}/service/{id}
	 */
	public function service($storeSlug = '', $serviceId = 0){
		$settings = $this->_getSettingsOr404($storeSlug);
		if(!$settings->allow_services){
			show_404();
			return;
		}
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$service = $this->storefront_model->getOnlineService($serviceId, $storeId);
		if(!$service){
			show_404();
			return;
		}
		$service->effective_price = $this->storefront_model->getServiceEffectivePrice($service);
		$this->load->model('Extras_model', 'extras_model');
		$serviceAddons = $this->extras_model->getAddonsForService($serviceId, $storeId);

		$serviceImage = $service->item_image && file_exists($service->item_image) ? base_url($service->item_image) : ($this->theme_engine->logoUrl() ?: base_url('uploads/site/icon.webp'));
		$data = [
			'settings' => $settings,
			'store' => $store,
			'service' => $service,
			'service_addons' => $serviceAddons,
			'related_services' => $this->storefront_model->getOnlineServices($storeId, $service->category_id, '', 4),
			'categories' => $this->storefront_model->getCategoriesWithItems($storeId),
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
			'social_links' => $this->theme_engine->socialLinks(),
			'store_currency' => $this->theme_engine->getStoreCurrency(),
			'seo_title' => $service->item_name,
			'seo_description' => substr(strip_tags($service->description ?? ''), 0, 160) ?: ('Book ' . $service->item_name . ' at ' . ($store->store_name ?? 'our store')),
			'seo_image' => $serviceImage,
			'seo_canonical' => base_url('store/' . $settings->store_slug . '/service/' . $serviceId),
			'seo_type' => 'product',
		];
		$this->theme_engine->view('service_detail', $data);
	}

	/**
	 * Public Catalogue - lightweight browse-only page
	 * URL: /store/{store_slug}/catalogue
	 * No cart, no checkout — just browse + WhatsApp enquiry
	 */
	public function catalogue($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);

		// Check if public catalogue is enabled in business profile settings
		$this->load->model('Business_profile_model','bp_model');
		$profile = $this->bp_model->get_profile($storeId);
		$cat_settings = [];
		if(!empty($profile['industry_settings_json'])){
			$decoded = json_decode($profile['industry_settings_json'], true);
			if(is_array($decoded) && isset($decoded['catalogue'])){
				$cat_settings = $decoded['catalogue'];
			}
		}
		// If not enabled, fall back to the online store products page
		if(empty($cat_settings['enabled'])){
			redirect(base_url('store/' . ($settings->store_slug ?? '') . '/products'));
			return;
		}

		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$categoryId = (int)$this->input->get('category');
		$search = trim($this->input->get('search'));
		$page = max(1, (int)$this->input->get('page'));
		$limit = 30;
		$offset = ($page - 1) * $limit;

		$showProducts = !isset($cat_settings['show_products']) || $cat_settings['show_products'] == '1';
		$showServices = !isset($cat_settings['show_services']) || $cat_settings['show_services'] == '1';

		$products = [];
		$services = [];
		$total = 0;

		if($showProducts){
			$total += $this->storefront_model->countOnlineProducts($storeId, $categoryId, $search);
			$products = $this->storefront_model->getOnlineProducts($storeId, $categoryId, $search, $limit, $offset);
			foreach($products as &$p){
				$p->effective_price = $this->storefront_model->getProductEffectivePrice($p);
				$p->original_price = !empty($p->is_bundle) ? $p->effective_price : $p->sales_price;
			}
		}
		if($showServices){
			$services = $this->storefront_model->getOnlineServices($storeId, $categoryId, $search, 30, 0);
			foreach($services as &$s){
				$s->effective_price = $this->storefront_model->getServiceEffectivePrice($s);
			}
		}

		$categories = $this->storefront_model->getCategoriesWithItems($storeId);
		$catName = '';
		foreach($categories as $cat){ if($cat->id == $categoryId){ $catName = $cat->category_name; break; } }

		$whatsappNumber = $settings->whatsapp_number ?? '';
		$storeName = $store->store_name ?? 'Our Store';

		$data = [
			'settings' => $settings,
			'store' => $store,
			'products' => $products,
			'services' => $services,
			'categories' => $categories,
			'category_id' => $categoryId,
			'category_name' => $catName,
			'search' => $search,
			'page' => $page,
			'limit' => $limit,
			'total' => $total,
			'total_pages' => $total > 0 ? ceil($total / $limit) : 1,
			'show_products' => $showProducts,
			'show_services' => $showServices,
			'whatsapp_number' => $whatsappNumber,
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
			'store_currency' => $this->theme_engine->getStoreCurrency(),
			'seo_title' => $catName ?: ($search ? 'Search: ' . $search : 'Catalogue'),
			'seo_description' => 'Browse our catalogue at ' . $storeName,
			'seo_canonical' => base_url('store/' . ($settings->store_slug ?? '') . '/catalogue'),
			'seo_type' => 'website',
		];
		$this->load->view('storefront/catalogue', $data);
	}

	/**
	 * Cart page
	 * URL: /store/{store_slug}/cart
	 */
	public function cart($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$data = [
			'settings' => $settings,
			'store' => $store,
			'categories' => $this->storefront_model->getCategoriesWithItems($storeId),
			'paystack_enabled' => $this->paystack->is_enabled($storeId),
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
			'social_links' => $this->theme_engine->socialLinks(),
			'store_currency' => $this->theme_engine->getStoreCurrency(),
			'table_number' => $this->table_number,
			'seo_title' => 'Shopping Cart',
			'seo_description' => 'Your cart at ' . ($store->store_name ?? 'our store'),
			'seo_canonical' => base_url('store/' . $settings->store_slug . '/cart'),
			'seo_type' => 'website',
			'csrf_name' => $this->security->get_csrf_token_name(),
			'csrf_hash' => $this->security->get_csrf_hash(),
		];
		if($data['paystack_enabled']){
			$ps = $this->paystack->get_settings($storeId);
			if($ps){
				$data['paystack_public_key'] = $ps->public_key ?? '';
				$data['paystack_test_mode'] = $ps->test_mode ?? 1;
			}
		}
		$this->theme_engine->view('cart', $data);
	}

	/**
	 * Branches
	 * URL: /store/{store_slug}/branches
	 */
	public function branches($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$data = [
			'settings' => $settings,
			'store' => $store,
			'branches' => $this->theme_engine->branches(),
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
			'social_links' => $this->theme_engine->socialLinks(),
			'store_currency' => $this->theme_engine->getStoreCurrency(),
			'seo_title' => 'Our Branches',
			'seo_description' => 'Find a branch near you at ' . ($store->store_name ?? 'our store'),
			'seo_canonical' => base_url('store/' . $settings->store_slug . '/branches'),
			'seo_type' => 'website',
		];
		$this->theme_engine->view('branches', $data);
	}

	private function _trackStatusMap($industryType = '', $shippingMethod = ''){
		$industry = strtolower(trim($industryType ?: 'general_retail'));
		$method = strtolower(trim($shippingMethod ?: ''));
		$isPickup = in_array($method, ['pickup', 'pick up', 'self pick up', 'self pickup', 'collect', 'collection']);

		$readyLabel = $isPickup ? 'Ready for pickup' : 'Ready for delivery';
		$completedLabel = $isPickup ? 'Picked up' : 'Delivered';
		$readyDesc = $isPickup ? 'Your order is ready for collection.' : 'Your order is on its way.';
		$completedDesc = $isPickup ? 'You have collected your order.' : 'Your order has been delivered.';
		// Laundry / dry cleaning
		if(in_array($industry, ['laundry', 'dry_cleaning', 'cleaning'])){
			return [
				'pending' => ['label' => 'Order received', 'desc' => 'We have received your order.'],
				'paid' => ['label' => 'Payment confirmed', 'desc' => 'Payment has been received.'],
				'processing' => ['label' => 'Cleaning in progress', 'desc' => 'Your items are being cleaned.'],
				'ready' => ['label' => $readyLabel, 'desc' => $readyDesc],
				'completed' => ['label' => $completedLabel, 'desc' => $completedDesc],
				'cancelled' => ['label' => 'Cancelled', 'desc' => 'This order was cancelled.'],
			];
		}

		// Food / restaurant
		if(in_array($industry, ['restaurant', 'fast_food', 'food_delivery', 'cafe', 'bakery'])){
			return [
				'pending' => ['label' => 'Order received', 'desc' => 'We have received your order.'],
				'paid' => ['label' => 'Payment confirmed', 'desc' => 'Payment has been received.'],
				'processing' => ['label' => 'Preparing your meal', 'desc' => 'Your food is being prepared.'],
				'ready' => ['label' => $isPickup ? 'Ready for pickup' : 'Ready to serve', 'desc' => $isPickup ? 'Your meal is ready.' : 'Your meal is being served.'],
				'completed' => ['label' => $completedLabel, 'desc' => $completedDesc],
				'cancelled' => ['label' => 'Cancelled', 'desc' => 'This order was cancelled.'],
			];
		}

		// Healthcare / pharmacy
		if(in_array($industry, ['pharmacy', 'healthcare', 'clinic', 'medical'])){
			return [
				'pending' => ['label' => 'Order received', 'desc' => 'We have received your request.'],
				'paid' => ['label' => 'Payment confirmed', 'desc' => 'Payment has been received.'],
				'processing' => ['label' => 'Dispensing order', 'desc' => 'Your items are being prepared.'],
				'ready' => ['label' => $readyLabel, 'desc' => $readyDesc],
				'completed' => ['label' => $completedLabel, 'desc' => $completedDesc],
				'cancelled' => ['label' => 'Cancelled', 'desc' => 'This order was cancelled.'],
			];
		}

		// Fashion / boutique
		if(in_array($industry, ['fashion', 'clothing', 'boutique', 'apparel', 'tailoring'])){
			return [
				'pending' => ['label' => 'Order received', 'desc' => 'We have received your order.'],
				'paid' => ['label' => 'Payment confirmed', 'desc' => 'Payment has been received.'],
				'processing' => ['label' => 'Processing order', 'desc' => 'Your items are being prepared.'],
				'ready' => ['label' => $readyLabel, 'desc' => $readyDesc],
				'completed' => ['label' => $completedLabel, 'desc' => $completedDesc],
				'cancelled' => ['label' => 'Cancelled', 'desc' => 'This order was cancelled.'],
			];
		}

		// Beauty / salon / spa / makeup
		if(in_array($industry, ['beauty', 'beauty_cosmetics', 'beauty_spa', 'salon', 'salon_barbershop', 'spa', 'wellness', 'makeup_artist', 'makeup_studio'])){
			return [
				'pending' => ['label' => 'Booking received', 'desc' => 'We have received your booking.'],
				'paid' => ['label' => 'Payment confirmed', 'desc' => 'Payment has been received.'],
				'processing' => ['label' => 'Preparing appointment', 'desc' => 'Your appointment is being prepared.'],
				'ready' => ['label' => 'Ready for service', 'desc' => 'Everything is set for you.'],
				'completed' => ['label' => 'Completed', 'desc' => 'Your appointment is complete.'],
				'cancelled' => ['label' => 'Cancelled', 'desc' => 'This booking was cancelled.'],
			];
		}

		// Services / bookings
		if(in_array($industry, ['services', 'consulting', 'repair', 'automotive'])){
			return [
				'pending' => ['label' => 'Request received', 'desc' => 'We have received your request.'],
				'paid' => ['label' => 'Payment confirmed', 'desc' => 'Payment has been received.'],
				'processing' => ['label' => 'Work in progress', 'desc' => 'We are working on your request.'],
				'ready' => ['label' => $readyLabel, 'desc' => $readyDesc],
				'completed' => ['label' => $completedLabel, 'desc' => $completedDesc],
				'cancelled' => ['label' => 'Cancelled', 'desc' => 'This request was cancelled.'],
			];
		}

		// Default / retail
		return [
			'pending' => ['label' => 'Order received', 'desc' => 'We have received your order.'],
			'paid' => ['label' => 'Payment confirmed', 'desc' => 'Payment has been received.'],
			'processing' => ['label' => 'Processing order', 'desc' => 'Your order is being prepared.'],
			'ready' => ['label' => $readyLabel, 'desc' => $readyDesc],
			'completed' => ['label' => $completedLabel, 'desc' => $completedDesc],
			'cancelled' => ['label' => 'Cancelled', 'desc' => 'This order was cancelled.'],
		];
	}

	/**
	 * Track Order
	 * URL: /store/{store_slug}/track
	 */
	public function track($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$order = null;
		$items = [];
		$error = '';

		if($this->input->post('order_code')){
			$orderCode = trim($this->input->post('order_code'));
			$customerPhone = trim($this->input->post('customer_phone'));
			$order = $this->storefront_model->getOrderByCode($orderCode, $storeId);
			if(!$order || $order->customer_phone !== $customerPhone){
				$order = null;
				$error = 'We could not find a matching order. Please check the order number and phone number.';
			} else {
				$items = $this->storefront_model->getOrderItems($order->id);
			}
		}

		$shippingMethod = $order->shipping_method ?? '';
		$statusMap = $this->_trackStatusMap($store->industry_type ?? '', $shippingMethod);
		$currentStatus = $statusMap[$order->order_status ?? 'pending'] ?? $statusMap['pending'];
		$allStatuses = ['pending','paid','processing','ready','completed'];
		$currentIndex = array_search($order->order_status ?? 'pending', $allStatuses);
		if($currentIndex === false) $currentIndex = 0;
		$visibleStatuses = ($order->order_status ?? 'pending') === 'completed' ? $allStatuses : array_slice($allStatuses, 0, $currentIndex + 1);

		$testimonialSubmitted = false;
		if(!empty($order->customer_name)){
			$testimonialSubmitted = $this->db->where('store_id', $storeId)->where('customer_name', $order->customer_name)->where('is_enabled', 0)->get('db_storefront_testimonials')->num_rows() > 0;
		}

		$data = [
			'settings' => $settings,
			'store' => $store,
			'order' => $order,
			'items' => $items,
			'error' => $error,
			'current_status' => $currentStatus,
			'status_map' => $statusMap,
			'visible_statuses' => $visibleStatuses,
			'all_statuses' => $allStatuses,
			'can_testimonial' => !empty($order) && ($order->order_status ?? '') === 'completed',
			'testimonial_submitted' => $testimonialSubmitted,
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
			'social_links' => $this->theme_engine->socialLinks(),
			'store_currency' => $this->theme_engine->getStoreCurrency(),
			'seo_title' => 'Track Order',
			'seo_description' => 'Track your order at ' . ($store->store_name ?? 'our store'),
			'seo_canonical' => base_url('store/' . $settings->store_slug . '/track'),
			'seo_type' => 'website',
			'csrf_name' => $this->security->get_csrf_token_name(),
			'csrf_hash' => $this->security->get_csrf_hash(),
		];
		$this->theme_engine->view('track', $data);
	}

	/**
	 * Submit testimonial from tracking page
	 * URL: /store/{store_slug}/track/testimonial
	 */
	public function submit_testimonial($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;

		$orderCode = trim($this->input->post('order_code', TRUE) ?: '');
		$customerPhone = trim($this->input->post('customer_phone', TRUE) ?: '');
		$customerName = trim($this->input->post('customer_name', TRUE) ?: '');
		$testimonialText = trim($this->input->post('testimonial_text', TRUE) ?: '');
		$rating = (int)($this->input->post('rating', TRUE) ?: 5);

		if(empty($orderCode) || empty($customerName) || empty($testimonialText)){
			echo json_encode(['status' => false, 'message' => 'All fields are required', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}

		$order = $this->storefront_model->getOrderByCode($orderCode, $storeId);
		if(!$order || $order->customer_phone !== $customerPhone || $order->order_status !== 'completed'){
			echo json_encode(['status' => false, 'message' => 'Order not found or not completed', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}

		$this->storefront_model->saveStorefrontTestimonial([
			'store_id' => $storeId,
			'customer_name' => $customerName,
			'testimonial_text' => $testimonialText,
			'rating' => max(1, min(5, $rating)),
			'sort_order' => 0,
			'is_enabled' => 0
		]);

		echo json_encode(['status' => true, 'message' => 'Testimonial submitted for approval', 'csrf_hash' => $this->security->get_csrf_hash()]);
	}

	/**
	 * Newsletter signup — captures email into db_newsletter_subscribers
	 * URL: POST /store/{store_slug}/subscribe
	 */
	public function subscribe($storeSlug = ''){
		$csrf = ['csrf_hash' => $this->security->get_csrf_hash()];
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;

		$email = strtolower(trim($this->input->post('email', TRUE) ?: ''));
		if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
			echo json_encode(['status' => false, 'message' => 'Please enter a valid email address'] + $csrf);
			return;
		}

		$source = substr(trim($this->input->post('source', TRUE) ?: 'newsletter'), 0, 50);
		$result = $this->storefront_model->saveNewsletterSubscriber([
			'store_id' => $storeId,
			'email' => $email,
			'source' => $source ?: 'newsletter',
			'ip_address' => $this->input->ip_address(),
			'status' => 1,
			'created_at' => date('Y-m-d H:i:s')
		]);

		if($result === false){
			echo json_encode(['status' => false, 'message' => 'Could not subscribe right now. Please try again.'] + $csrf);
			return;
		}
		echo json_encode(['status' => true, 'message' => 'Thank you for subscribing!'] + $csrf);
	}

	/**
	 * Lead capture — enquiry/quote-request form on the storefront contact section.
	 * Only active when the 'leads' feature flag is on for the store.
	 * URL: POST /store/{store_slug}/lead
	 */
	public function submit_lead($storeSlug = ''){
		$csrf = ['csrf_hash' => $this->security->get_csrf_hash()];
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;

		if(!mp_feature_enabled_for_store('leads', $storeId)){
			echo json_encode(['status' => false, 'message' => 'This form is not available right now'] + $csrf);
			return;
		}

		// Honeypot — bots fill hidden fields, humans never see it
		if($this->input->post('website')){
			echo json_encode(['status' => true, 'message' => 'Thank you! We will be in touch shortly.'] + $csrf);
			return;
		}

		$name = trim($this->input->post('name', TRUE) ?: '');
		$phone = trim($this->input->post('phone', TRUE) ?: '');
		$email = strtolower(trim($this->input->post('email', TRUE) ?: ''));
		$interest = trim($this->input->post('interest', TRUE) ?: '');

		if($name === '' || ($phone === '' && $email === '')){
			echo json_encode(['status' => false, 'message' => 'Please leave your name and a phone number or email'] + $csrf);
			return;
		}
		if($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)){
			echo json_encode(['status' => false, 'message' => 'Please enter a valid email address'] + $csrf);
			return;
		}

		$this->load->model('leads_model', 'leads_m');
		$result = $this->leads_m->saveLead([
			'store_id' => $storeId,
			'name' => $name,
			'phone' => $phone ?: null,
			'email' => $email ?: null,
			'source' => 'storefront',
			'status' => 'new',
			'interest' => $interest ?: null,
			'created_at' => date('Y-m-d H:i:s')
		]);

		if($result === false){
			echo json_encode(['status' => false, 'message' => 'Could not send your enquiry. Please try again.'] + $csrf);
			return;
		}
		echo json_encode(['status' => true, 'message' => 'Thank you! We will be in touch shortly.'] + $csrf);
	}

	/**
	 * Dynamic XML Sitemap for storefront
	 * URL: /sitemap.xml
	 */
	public function sitemap(){
		$storeSlug = $this->input->get('store');
		$settings = null;
		$viaDomain = false;
		if($storeSlug){
			$settings = $this->storefront_model->getStoreBySlug($storeSlug);
		} else {
			// On a connected custom domain, resolve the store from the request host
			$dom = $this->storefront_model->getStoreByDomain($this->input->server('HTTP_HOST'));
			if($dom){ $settings = $this->storefront_model->getSettings($dom->store_id); $viaDomain = true; }
		}
		if(!$settings || $settings->store_status != 'active'){ show_404(); return; }
		$storeSlug = $settings->store_slug;
		$storeId = $settings->store_id;
		$base = $viaDomain ? rtrim(base_url(), '/') : base_url('store/' . $storeSlug);

		$products = $this->storefront_model->getOnlineProducts($storeId, null, '', 500);
		$services = $settings->allow_services ? $this->storefront_model->getOnlineServices($storeId, null, '', 500) : [];
		$categories = $this->storefront_model->getCategoriesWithItems($storeId);

		header('Content-Type: application/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

		// Home
		echo '<url><loc>' . $base . '</loc><changefreq>daily</changefreq><priority>1.0</priority></url>';
		// Products page
		echo '<url><loc>' . $base . '/products</loc><changefreq>daily</changefreq><priority>0.9</priority></url>';
		// Categories
		foreach($categories as $cat){
			echo '<url><loc>' . $base . '/products?category=' . $cat->id . '</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>';
		}
		// Individual products
		foreach($products as $p){
			echo '<url><loc>' . $base . '/product/' . $p->id . '</loc><changefreq>weekly</changefreq><priority>0.7</priority></url>';
		}
		// Services page
		if($settings->allow_services){
			echo '<url><loc>' . $base . '/services</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>';
			foreach($services as $s){
				echo '<url><loc>' . $base . '/service/' . $s->id . '</loc><changefreq>weekly</changefreq><priority>0.7</priority></url>';
			}
		}
		// Cart
		echo '<url><loc>' . $base . '/cart</loc><changefreq>monthly</changefreq><priority>0.3</priority></url>';
		echo '</urlset>';
	}

	/**
	 * Robots.txt for storefront
	 * URL: /robots.txt
	 */
	public function robots(){
		header('Content-Type: text/plain');
		// On a connected custom domain the storefront is served from the root
		$dom = $this->storefront_model->getStoreByDomain($this->input->server('HTTP_HOST'));
		if($dom){
			echo "User-agent: *\n";
			echo "Allow: /\n";
			echo "Disallow: /store/\n";
			echo "Disallow: /storefront/\n";
			echo "Sitemap: " . base_url('sitemap.xml') . "\n";
			return;
		}
		echo "User-agent: *\n";
		echo "Allow: /store/\n";
		echo "Disallow: /online_store/\n";
		echo "Disallow: /dashboard\n";
		echo "Disallow: /items/\n";
		echo "Disallow: /sales/\n";
		echo "Disallow: /purchase/\n";
		echo "Disallow: /qsr/\n";
		echo "Disallow: /reports/\n";
		echo "Sitemap: " . base_url('sitemap.xml') . "\n";
	}

	/**
	 * Place order (AJAX)
	 */
	public function place_order(){
		$storeId = (int)$this->input->post('store_id');
		if(!$storeId){
			echo json_encode(['status' => false, 'message' => 'Invalid store', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}

		$settings = $this->storefront_model->getSettings($storeId);
		if(!$settings || $settings->store_status != 'active'){
			$msg = ($settings && $settings->store_status == 'maintenance') ? 'Store under maintenance' : 'Store not active';
			echo json_encode(['status' => false, 'message' => $msg, 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}

		// Housekeeping: release stock reserved by abandoned online payments
		// (paystack orders still unpaid/pending past the TTL). WhatsApp and
		// pay-on-delivery reservations are merchant-managed and never expire.
		try { $this->storefront_model->releaseExpiredReservations($storeId); } catch(Exception $e) {}

		$cart = json_decode($this->input->post('cart'), true);
		if(empty($cart) || !is_array($cart)){
			echo json_encode(['status' => false, 'message' => 'Cart is empty', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}

		$customerName = trim($this->input->post('customer_name'));
		$customerEmail = trim($this->input->post('customer_email'));
		$customerPhone = trim($this->input->post('customer_phone'));
		$customerAddress = trim($this->input->post('customer_address'));
		$paymentMethod = $this->input->post('payment_method'); // paystack, whatsapp, pay_on_delivery
		$serviceDate = $this->input->post('service_date');
		$serviceTime = $this->input->post('service_time');
		$serviceNote = $this->input->post('service_note');
		$shippingMethod = trim($this->input->post('shipping_method'));
		$tableNumber = trim($this->input->post('table_number'));

		if(!$customerName || !$customerPhone){
			echo json_encode(['status' => false, 'message' => 'Name and phone are required', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}

		// Validate payment method against settings
		if($paymentMethod == 'paystack' && !$settings->allow_paystack){
			$paymentMethod = 'pay_on_delivery';
		}
		if($paymentMethod == 'whatsapp' && !$settings->allow_whatsapp){
			$paymentMethod = 'pay_on_delivery';
		}
		if($paymentMethod == 'pay_on_delivery' && !$settings->allow_pay_on_delivery){
			echo json_encode(['status' => false, 'message' => 'Selected payment method is not available', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}

		$subtotal = 0;
		$hasProducts = false;
		$hasPhysical = false;
		$hasDigital = false;
		$hasCourse = false;
		$hasMembership = false;
		$hasServices = false;
		$orderQty = 0;
		$itemsToInsert = [];
		$bundleComponentsByLine = [];
		$addonsByParentLine = [];
		$this->load->model('Extras_model', 'extras_model');
		$appendAddons = function($parentType, $parentId, $parentQty, $parentLineIndex, $selections) use (&$addonsByParentLine, &$subtotal, &$hasPhysical, $storeId){
			if(!is_array($selections) || empty($selections)) return null;
			list($addons, $error) = $this->extras_model->resolveAddonSelections($parentType, $parentId, $selections, $storeId);
			if($error) return $error;
			foreach($addons as $addon){
				$lineQty = (int)$addon->resolved_qty * (int)$parentQty;
				$linked = $addon->linked_item ?? null;
				$itemType = $linked ? 'product' : 'addon';
				$addonsByParentLine[$parentLineIndex][] = [
					'item_type' => $itemType,
					'item_id' => $linked ? (int)$linked->id : (int)$addon->id,
					'item_name' => (string)$addon->name,
					'item_image' => $linked->item_image ?? '',
					'qty' => $lineQty,
					'unit_price' => (float)$addon->price,
					'total_price' => (float)$addon->price * $lineQty,
					'service_note' => 'Add-on',
				];
				$subtotal += (float)$addon->price * $lineQty;
				if($linked) $hasPhysical = true;
			}
			return null;
		};

		foreach($cart as $item){
			$type = (string)($item['type'] ?? '');
			$id = (int)$item['id'];
			$qty = max(1, (int)($item['qty'] ?? 1));

			if($type == 'product'){
				$product = $this->storefront_model->getOnlineProduct($id, $storeId);
				if(!$product) continue;
				// Variant parents are catalogue shells — only a specific
				// child variant can be ordered.
				if(($product->item_group ?? '') === 'Variants'){
					echo json_encode(['status' => false, 'message' => 'Please choose an option for ' . $product->item_name, 'csrf_hash' => $this->security->get_csrf_hash()]);
					return;
				}
				$qtyError = $this->extras_model->qtyRuleError($product, $qty);
				if($qtyError){
					echo json_encode(['status' => false, 'message' => $qtyError, 'csrf_hash' => $this->security->get_csrf_hash()]);
					return;
				}
				$product_type = $product->product_type ?? 'physical';
				$isBundle = !empty($product->is_bundle);
				$bundleComponents = [];
				$hasProducts = true;
				if($product_type === 'physical'){
					if($isBundle){
						$bundleComponents = $this->extras_model->getBundleComponents($id, $storeId);
						$bundleAvailable = $this->extras_model->bundleAvailability($id, $storeId);
						if(empty($bundleComponents) || $bundleAvailable === null || ($bundleAvailable < $qty && !$settings->allow_backorder)){
							echo json_encode(['status' => false, 'message' => $product->item_name . ' is out of stock or has no configured components', 'csrf_hash' => $this->security->get_csrf_hash()]);
							return;
						}
						$hasPhysical = true;
					} else {
						$hasPhysical = true;
						if($product->stock < $qty && !$settings->allow_backorder){
							echo json_encode(['status' => false, 'message' => $product->item_name . ' is out of stock', 'csrf_hash' => $this->security->get_csrf_hash()]);
							return;
						}
					}
				} else if($product_type === 'digital'){
					$hasDigital = true;
				} else if($product_type === 'course'){
					$hasCourse = true;
				} else if($product_type === 'membership'){
					$hasMembership = true;
				}
				$price = $isBundle
					? $this->extras_model->bundleUnitPrice($product, $storeId)
					: $this->storefront_model->getProductEffectivePrice($product);
				$lineIndex = count($itemsToInsert);
				$itemsToInsert[] = [
					'item_type' => $isBundle ? 'bundle' : (in_array($product_type, ['digital','course','membership']) ? $product_type : 'product'),
					'item_id' => $id,
					'item_name' => $product->item_name,
					'item_image' => $product->item_image,
					'qty' => $qty,
					'unit_price' => $price,
					'total_price' => $price * $qty,
					'service_note' => ''
				];
				if($isBundle){
					$bundleComponentsByLine[$lineIndex] = array_values(array_filter($bundleComponents, function($component){
						return (int)($component->service_bit ?? 0) !== 1;
					}));
				}
				$addonError = $appendAddons('product', $id, $qty, $lineIndex, $item['addons'] ?? []);
				if($addonError){
					echo json_encode(['status' => false, 'message' => $addonError, 'csrf_hash' => $this->security->get_csrf_hash()]);
					return;
				}
				$subtotal += $price * $qty;
				$orderQty += $qty;
			} else if($type == 'service'){
				$service = $this->storefront_model->getOnlineService($id, $storeId);
				if(!$service) continue;
				$price = $this->storefront_model->getServiceEffectivePrice($service);
				$hasServices = true;
				$lineIndex = count($itemsToInsert);
				$itemsToInsert[] = [
					'item_type' => 'service',
					'item_id' => $id,
					'item_name' => $service->service_name,
					'item_image' => $service->service_image,
					'qty' => $qty,
					'unit_price' => $price,
					'total_price' => $price * $qty,
					'service_note' => $item['note'] ?? ''
				];
				$addonError = $appendAddons('service', $id, $qty, $lineIndex, $item['addons'] ?? []);
				if($addonError){
					echo json_encode(['status' => false, 'message' => $addonError, 'csrf_hash' => $this->security->get_csrf_hash()]);
					return;
				}
				$subtotal += $price * $qty;
				$orderQty += $qty;
			}
		}

		if(empty($itemsToInsert)){
			echo json_encode(['status' => false, 'message' => 'No valid items in cart', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}
		$orderMinQty = max(0, (int)($settings->min_order_qty ?? 0));
		if($orderMinQty > 0 && $orderQty < $orderMinQty){
			echo json_encode(['status' => false, 'message' => 'This order requires at least ' . $orderMinQty . ' items.', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}

		// Pay on delivery only makes sense for physical products
		if($paymentMethod == 'pay_on_delivery' && ($hasDigital || $hasCourse || $hasMembership || $hasServices)){
			echo json_encode(['status' => false, 'message' => 'Pay on delivery is not available for digital products, courses, memberships or services.', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}

		$hasOnlyDigital = ($hasDigital || $hasCourse || $hasMembership) && !$hasPhysical && !$hasServices;
		$orderType = ($hasProducts && $hasServices) ? 'mixed' : ($hasServices ? 'service' : 'product');

		// Digital-only orders never need shipping
		if($hasOnlyDigital){
			$shippingMethod = null;
			$customerAddress = null;
		}

		// City-based delivery fees (per-store flag). When enabled with a
		// configured city list it replaces the flat shipping methods: the
		// customer picks their city at checkout and the fee is re-resolved
		// server-side so it can't be tampered with.
		$shippingFee = 0;
		$deliveryQuotePending = 0;
		$cityZones = json_decode($settings->city_shipping_json ?? '', true);
		// Pickup is exempt from city-zone resolution: when the posted method
		// is an enabled pickup-type method, no delivery city is required and
		// the fee is whatever that method charges (usually free).
		$isPickupMethod = false;
		if(preg_match('/pick\s*up|collect/i', (string)$shippingMethod)){
			$smList = json_decode($settings->shipping_methods_json ?? '', true);
			if(is_array($smList)){
				foreach($smList as $sm){
					if(($sm['name'] ?? '') === $shippingMethod && !empty($sm['enabled'])){
						$isPickupMethod = true;
						$shippingFee = !empty($sm['quote']) ? 0 : (float)($sm['fee'] ?? 0);
						if(!empty($sm['quote'])) $deliveryQuotePending = 1;
						break;
					}
				}
			}
			// Unrecognised pickup name → not a real method; fall through to
			// normal zone/method resolution so it can't skip delivery fees.
			if(!$isPickupMethod) $shippingMethod = '';
		}
		$cityShippingOn = !$isPickupMethod && !$hasOnlyDigital && !$tableNumber && !empty($settings->city_shipping_enabled) && is_array($cityZones) && count($cityZones) > 0;

		if($cityShippingOn){
			$selectedCity = trim((string)$this->input->post('shipping_city'));
			$zoneMatch = null;
			$wildcard = null;
			foreach($cityZones as $z){
				$zoneKey = trim($z['city'] ?? '') . '|' . trim($z['state'] ?? '');
				// A '*' city is the catch-all zone for unlisted destinations.
				if(trim($z['city'] ?? '') === '*'){ $wildcard = $z; continue; }
				if($zoneKey !== '|' && strcasecmp($zoneKey, $selectedCity) === 0){
					$zoneMatch = $z;
					break;
				}
			}
			// The customer may pick the '*' zone directly ("Other areas"), and
			// an unlisted city also resolves to the wildcard when configured.
			if(!$zoneMatch){
				$zoneMatch = $wildcard;
			}
			if(!$zoneMatch){
				echo json_encode(['status' => false, 'message' => 'Please select your delivery city', 'csrf_hash' => $this->security->get_csrf_hash()]);
				return;
			}
			// Per-zone order minimum — zones can be gated to viable baskets.
			$zoneMinOrder = (float)($zoneMatch['min_order'] ?? 0);
			if($zoneMinOrder > 0 && $subtotal < $zoneMinOrder){
				echo json_encode(['status' => false, 'message' => 'Delivery to this area requires a minimum order of ' . store_number_format($zoneMinOrder) . '.', 'csrf_hash' => $this->security->get_csrf_hash()]);
				return;
			}
			$shippingFee = (float)($zoneMatch['fee'] ?? 0);
			// Per-zone free-delivery threshold.
			$zoneFreeOver = (float)($zoneMatch['free_over'] ?? 0);
			if($zoneFreeOver > 0 && $subtotal >= $zoneFreeOver){
				$shippingFee = 0;
			}
			$zoneLabel = (trim($zoneMatch['city'] ?? '') === '*')
				? 'Other areas'
				: trim(trim($zoneMatch['city'] ?? '') . (!empty($zoneMatch['state']) ? ', ' . trim($zoneMatch['state']) : ''));
			$shippingMethod = mb_substr('Delivery - ' . $zoneLabel, 0, 100);
		} else {
			// Resolve shipping method fee from store settings
			$shippingMethods = json_decode($settings->shipping_methods_json ?? '', true);
			if(is_array($shippingMethods) && $shippingMethod){
				foreach($shippingMethods as $sm){
					if(($sm['name'] ?? '') === $shippingMethod && ($sm['enabled'] ?? 0)){
						// "Fee on quote" methods carry no charged amount — the
						// merchant sets the delivery fee when confirming.
						$shippingFee = !empty($sm['quote']) ? 0 : (float)($sm['fee'] ?? 0);
						if(!empty($sm['quote'])) $deliveryQuotePending = 1;
						break;
					}
				}
			}
		}
		// Storefront coupon — reuses the existing promotion engine. The code is
		// resolved server-side (store scope, active flag, date window); the
		// discount mirrors the POS coupon_amt semantics (% of cart subtotal or
		// fixed amount) and the usage claim runs atomically inside the order
		// transaction below so usage limits cannot be raced.
		$couponCode = strtoupper(trim((string)$this->input->post('coupon_code', TRUE)));
		$promo = null;
		$couponDiscount = 0;
		if($couponCode !== ''){
			$promo = $this->db->where('store_id', $storeId)
				->where('status', 1)
				->where('UPPER(promotion_code)', $couponCode)
				->where('start_date <=', date('Y-m-d'))
				->where('end_date >=', date('Y-m-d'))
				->get('db_promotions')->row();
			if(!$promo){
				echo json_encode(['status' => false, 'message' => 'Coupon code is invalid or expired', 'csrf_hash' => $this->security->get_csrf_hash()]);
				return;
			}
			$couponDiscount = ($promo->discount_type == 'Percentage')
				? $subtotal * ((float)$promo->discount_value / 100)
				: (float)$promo->discount_value;
			$couponDiscount = max(0, min(round($couponDiscount, 4), $subtotal));
		}

		$grandTotal = max(0, $subtotal + $shippingFee - $couponDiscount);

		$orderData = [
			'store_id' => $storeId,
			'customer_name' => $customerName,
			'customer_email' => $customerEmail,
			'customer_phone' => $customerPhone,
			'customer_address' => $customerAddress,
			'order_type' => $orderType,
			'payment_method' => $paymentMethod,
			'shipping_method' => $shippingMethod ?: null,
			'delivery_fee' => $shippingFee,
			'delivery_quote_pending' => $deliveryQuotePending,
			'subtotal' => $subtotal,
			'grand_total' => $grandTotal,
			'table_number' => $tableNumber ?: null,
			'service_date' => $serviceDate ?: null,
			'service_time' => $serviceTime ?: null,
			'service_note' => $serviceNote ?: null,
			'ip_address' => $this->input->ip_address(),
			'user_agent' => $this->input->user_agent()
		];
		// Coupon columns arrive with migration 4.0.9.69 — omit on older schemas.
		if($this->db->field_exists('coupon_code', 'db_online_orders')){
			$orderData['coupon_code'] = $couponCode !== '' ? $couponCode : null;
			$orderData['coupon_discount'] = $couponDiscount ?: null;
			$orderData['promotion_id'] = $promo ? (int)$promo->id : null;
		}

		// Set initial status based on payment method. WhatsApp is an order
		// CHANNEL — the payment arrangement stays unpaid until the merchant
		// confirms how the customer will pay (it is never assumed to be COD).
		if($paymentMethod == 'paystack'){
			$orderData['order_status'] = 'pending';
			$orderData['payment_status'] = 'unpaid';
			$orderData['source_channel'] = 'web';
		} else if($paymentMethod == 'whatsapp'){
			$orderData['order_status'] = 'pending';
			$orderData['payment_status'] = 'unpaid';
			$orderData['whatsapp_sent'] = 1;
			$orderData['source_channel'] = 'whatsapp';
		} else {
			$orderData['source_channel'] = 'web';
			$orderData['order_status'] = 'pending';
			$orderData['payment_status'] = 'unpaid';
		}

		$this->db->trans_start();

		$orderId = $this->storefront_model->createOrder($orderData);

		// Link or create customer record
		$customerId = $this->customers_model->getOrCreateStorefrontCustomer($storeId, $customerName, $customerPhone, $customerEmail);
		if($customerId){
			$this->db->where('id', $orderId)->update('db_online_orders', ['customer_id' => $customerId]);
		}

		// Atomic coupon claim — promotion row is locked FOR UPDATE inside this
		// transaction, so usage limits hold under concurrent checkouts. A
		// rejected claim rolls the order back entirely (no orphan reservation).
		if($promo){
			$this->load->model('Promotions_model','promotions_m');
			$claim = $this->promotions_m->claim_redemption($promo->id, $customerId, $subtotal, $storeId);
			if(!$claim['ok']){
				$this->db->trans_rollback();
				echo json_encode(['status' => false, 'message' => 'Coupon cannot be applied: ' . $claim['message'], 'csrf_hash' => $this->security->get_csrf_hash()]);
				return;
			}
			$this->promotions_m->record_usage($promo->id, $customerId, null, $storeId, $orderId);
		}

		$bundleDraws = [];
		$reservationItems = [];
		foreach($itemsToInsert as $lineIndex => $item){
			$item['order_id'] = $orderId;
			$this->storefront_model->addOrderItem($item);
			$parentLineId = (int)$this->db->insert_id();
			$reservationItems[] = $item;
			foreach($addonsByParentLine[$lineIndex] ?? [] as $addonLine){
				$addonLine['order_id'] = $orderId;
				$addonLine['parent_line_id'] = $parentLineId;
				$this->storefront_model->addOrderItem($addonLine);
				if($addonLine['item_type'] === 'product') $reservationItems[] = $addonLine;
			}
			foreach($bundleComponentsByLine[$lineIndex] ?? [] as $component){
				$componentQty = (float)$component->qty * (int)$item['qty'];
				if($componentQty <= 0) continue;
				$componentLine = [
					'order_id' => $orderId,
					'parent_line_id' => $parentLineId,
					'item_type' => 'bundle_component',
					'item_id' => (int)$component->component_item_id,
					'item_name' => $component->item_name,
					'item_image' => $component->item_image,
					'qty' => $componentQty,
					'unit_price' => 0,
					'total_price' => 0,
					'service_note' => 'Bundle component of order line ' . $parentLineId,
				];
				$this->storefront_model->addOrderItem($componentLine);
				$bundleDraws[] = ['item_id' => (int)$component->component_item_id, 'qty' => $componentQty];
			}
		}

		// Reserve stock for physical items atomically so concurrent orders
		// cannot oversell the same units. The reservation is held until the
		// order is paid/confirmed (commit) or cancelled/failed (release).
		if(!$this->storefront_model->reserveOrderItems($orderId, $reservationItems, !empty($settings->allow_backorder))){
			$this->db->trans_rollback();
			echo json_encode(['status' => false, 'message' => 'An item in your cart just sold out. Please review your cart and try again.', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}
		if(!empty($bundleDraws)){
			if($this->extras_model->reserveBundleComponents($bundleDraws, $orderId, !empty($settings->allow_backorder), $storeId) === false){
				$this->db->trans_rollback();
				echo json_encode(['status' => false, 'message' => 'A bundle component just sold out. Please review your cart and try again.', 'csrf_hash' => $this->security->get_csrf_hash()]);
				return;
			}
			$this->db->where('id', $orderId)->update('db_online_orders', [
				'stock_adjusted' => 1,
				'stock_state' => 'reserved',
				'stock_reserved_at' => date('Y-m-d H:i:s'),
			]);
		}

		$this->db->trans_complete();

		// Convert the persisted cart + record the funnel's terminal event.
		// order_placed is transactional truth, recorded server-side so the
		// funnel never depends on a browser callback firing.
		$cartToken = (string)$this->input->post('cart_token');
		if($cartToken !== ''){
			$this->storefront_model->markCartOrdered($storeId, $cartToken, $orderId);
		}
		$this->storefront_model->recordEvent($storeId, array(
			'event_type' => 'order_placed',
			'event_id'   => 'order_' . $orderId,
			'order_id'   => $orderId,
			'value'      => $grandTotal,
			'consented'  => $this->input->post('consent') === '1',
		));

		$order = $this->storefront_model->getOrder($orderId);

		// Send email notifications (store owner + customer if email provided)
		$store = get_store_details($storeId);
		$this->_send_order_emails($order, $itemsToInsert, $store, $settings);

		// If Paystack, return payment initialization data
		if($paymentMethod == 'paystack' && $settings->allow_paystack){
			$ps = $this->paystack->get_settings($storeId);
			if($ps && $ps->public_key){
				echo json_encode([
					'status' => true,
					'payment_required' => true,
					'order_id' => $orderId,
					'order_code' => $order->order_code,
					'amount_kobo' => (int)($grandTotal * 100),
					'public_key' => $ps->public_key,
					'email' => $customerEmail ?: 'customer@' . ($settings->store_slug ?: 'store') . '.com',
					'reference' => $order->order_code,
					'currency' => $this->_storeCurrencyCode($storeId) ?: 'NGN',
					'csrf_hash' => $this->security->get_csrf_hash(),
				]);
				return;
			}
		}

		// For WhatsApp or Pay on Delivery
		echo json_encode([
			'status' => true,
			'payment_required' => false,
			'order_id' => $orderId,
			'order_code' => $order->order_code,
			'redirect_url' => base_url('store/' . ($settings->store_slug ?? '') . '/order_received/' . $order->order_code),
			'message' => 'Order placed successfully!',
			'csrf_hash' => $this->security->get_csrf_hash(),
		]);
	}

	public function cart_upsells(){
		$storeId = (int)$this->input->post('store_id');
		$settings = $storeId ? $this->storefront_model->getSettings($storeId) : null;
		$cartIds = json_decode((string)$this->input->post('cart_ids'), true);
		if(!$settings || $settings->store_status !== 'active' || (isset($settings->upsells_enabled) && !(int)$settings->upsells_enabled) || !is_array($cartIds)){
			$this->output->set_content_type('application/json')->set_output(json_encode(['items' => []]));
			return;
		}
		$this->load->model('Extras_model', 'extras_model');
		$items = $this->extras_model->getUpsellsForCart($cartIds, $storeId, 4);
		$result = [];
		foreach($items as $item){
			$image = !empty($item->item_image) ? base_url($item->item_image) : '';
			$result[] = [
				'id' => (int)$item->id,
				'name' => (string)$item->item_name,
				'price' => (float)$item->effective_price,
				'image' => $image,
				'stock' => (float)$item->stock,
			];
		}
		$this->output->set_content_type('application/json')->set_output(json_encode(['items' => $result]));
	}

	/**
	 * Coupon preview (AJAX). Validates the code against db_promotions
	 * (store scope, active, date window, min spend) and returns the discount
	 * the customer would get. Usage limits are enforced at order time via
	 * claim_redemption() — this preview never consumes a redemption.
	 */
	public function validate_coupon(){
		$storeId = (int)$this->input->post('store_id');
		$code = strtoupper(trim((string)$this->input->post('coupon_code', TRUE)));
		$subtotal = (float)$this->input->post('subtotal');
		$out = function($arr){ $arr['csrf_hash'] = $this->security->get_csrf_hash(); echo json_encode($arr); };

		if(!$storeId || $code === ''){ $out(['status'=>false,'message'=>'Enter a coupon code']); return; }
		$settings = $this->storefront_model->getSettings($storeId);
		if(!$settings || $settings->store_status != 'active'){ $out(['status'=>false,'message'=>'Store not active']); return; }

		$promo = $this->db->where('store_id', $storeId)
			->where('status', 1)
			->where('UPPER(promotion_code)', $code)
			->get('db_promotions')->row();
		if(!$promo){ $out(['status'=>false,'message'=>'Invalid coupon code']); return; }
		if($promo->start_date > date('Y-m-d')){ $out(['status'=>false,'message'=>'This coupon starts on '.show_date($promo->start_date)]); return; }
		if($promo->end_date < date('Y-m-d')){ $out(['status'=>false,'message'=>'This coupon expired on '.show_date($promo->end_date)]); return; }
		if(!empty($promo->min_spend) && $subtotal < (float)$promo->min_spend){
			$out(['status'=>false,'message'=>'Minimum spend of '.store_number_format($promo->min_spend).' required']); return;
		}
		$discount = ($promo->discount_type == 'Percentage')
			? $subtotal * ((float)$promo->discount_value / 100)
			: (float)$promo->discount_value;
		$out(array('status'=>true,'promo_name'=>$promo->promotion_name,'discount'=>max(0,min(round($discount,4),$subtotal))));
	}

	/**
	 * Shopping-event intake (AJAX). First-party funnel events — the client
	 * only sends when the merchant's consent policy allows it; the asserted
	 * consent flag is stored so diagnostics stay honest. When the store
	 * requires tracking consent and the request doesn't assert it, the event
	 * is dropped rather than recorded.
	 */
	public function track_event(){
		$storeId = (int)$this->input->post('store_id');
		$event   = trim((string)$this->input->post('event', TRUE));
		$out = function($arr){ $arr['csrf_hash'] = $this->security->get_csrf_hash(); echo json_encode($arr); };
		if(!$storeId || $event === ''){ $out(['status'=>false]); return; }
		$settings = $this->storefront_model->getSettings($storeId);
		if(!$settings || $settings->store_status != 'active'){ $out(['status'=>false]); return; }

		$needConsent = ((int)($settings->require_tracking_consent ?? 1) === 1);
		$consented   = $this->input->post('consent') === '1';
		if($needConsent && !$consented){ $out(['status'=>false,'message'=>'consent_required']); return; }

		// A client-reported 'purchase' only lands if the referenced order is
		// already paid — otherwise forged events would occupy the dedup slot
		// before the real verified-payment record arrives.
		if($event === 'purchase'){
			$orderId = (int)$this->input->post('order_id');
			$code = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$this->input->post('event_id'));
			if(!$orderId && strpos($code, 'purchase_') === 0){
				$o = $this->storefront_model->getOrderByCode(substr($code, 9), $storeId);
				$orderId = $o ? (int)$o->id : 0;
			}
			$order = $orderId ? $this->storefront_model->getOrder($orderId) : null;
			if(!$order || (int)$order->store_id !== $storeId || $order->payment_status !== 'paid'){
				$out(['status'=>false,'message'=>'unverified']); return;
			}
		}

		$ok = $this->storefront_model->recordEvent($storeId, array(
			'event_type' => $event,
			'event_id'   => substr(preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$this->input->post('event_id')), 0, 64) ?: null,
			'item_id'    => (int)$this->input->post('item_id'),
			'order_id'   => (int)$this->input->post('order_id') ?: null,
			'value'      => $this->input->post('value'),
			'session_id' => session_id() ?: '',
			'consented'  => $needConsent ? $consented : 1,
			'meta'       => json_encode(array(
				'page'       => substr((string)$this->input->post('page'), 0, 500) ?: null,
				'cart_token' => substr(preg_replace('/[^a-zA-Z0-9]/', '', (string)$this->input->post('cart_token')), 0, 64) ?: null,
			)),
		));
		$out(['status' => $ok ? true : false, 'deduped' => $ok === 'duplicate']);
	}

	/**
	 * Persist a cart snapshot (AJAX). Lets the merchant see and recover
	 * abandoned checkouts. Token is client-generated and localStorage-held;
	 * it identifies the browser session, never an account.
	 */
	public function save_cart(){
		$storeId = (int)$this->input->post('store_id');
		$token   = (string)$this->input->post('cart_token');
		$out = function($arr){ $arr['csrf_hash'] = $this->security->get_csrf_hash(); echo json_encode($arr); };
		if(!$storeId || $token === ''){ $out(['status'=>false]); return; }
		$settings = $this->storefront_model->getSettings($storeId);
		if(!$settings || $settings->store_status != 'active'){ $out(['status'=>false]); return; }
		if(empty($settings->cart_recovery_enabled)){ $out(['status'=>false,'message'=>'disabled']); return; }

		$items = json_decode((string)$this->input->post('items_json'), true);
		if(!is_array($items)){ $items = array(); }
		if(!preg_match('/^[0-9a-f]{32,64}$/', $token)){ $out(['status'=>false,'message'=>'bad_token']); return; }

		$id = $this->storefront_model->saveCart($storeId, $token, array(
			'customer_name'  => $this->input->post('customer_name', TRUE),
			'customer_phone' => $this->input->post('customer_phone', TRUE),
			'customer_email' => $this->input->post('customer_email', TRUE),
			'items_json'     => json_encode(array_slice($items, 0, 100)),
			'subtotal'       => $this->input->post('subtotal'),
			'coupon_code'    => $this->input->post('coupon_code', TRUE),
			'session_id'     => session_id() ?: '',
		));
		$out(array('status' => $id !== false));
	}

	/**
	 * Fetch a persisted cart for recovery (AJAX). Token is a 128-bit
	 * crypto-random store-scoped value that expires 7 days after last
	 * activity. The response contains only revalidated catalogue items —
	 * saved contact details are never returned to the public caller.
	 */
	public function get_cart(){
		$storeId = (int)$this->input->post('store_id');
		$token   = (string)$this->input->post('cart_token');
		$out = function($arr){ $arr['csrf_hash'] = $this->security->get_csrf_hash(); echo json_encode($arr); };
		if(!$storeId || !preg_match('/^[0-9a-f]{32,64}$/', (string)$token)){ $out(['status'=>false]); return; }
		$settings = $this->storefront_model->getSettings($storeId);
		if(!$settings || $settings->store_status != 'active' || empty($settings->cart_recovery_enabled)){ $out(['status'=>false]); return; }
		$cart = $this->storefront_model->getRestorableCart($storeId, $token);
		if(!$cart){ $out(['status'=>false]); return; }
		// Saved coupon code is revalidated live before the response.
		$coupon = null;
		$savedCart = $this->storefront_model->getCartByToken($storeId, $token);
		if($savedCart && !empty($savedCart->coupon_code)){
			$subtotal = 0; foreach($cart['items'] as $i){ $subtotal += $i['price'] * $i['qty']; }
			$promo = $this->db->where('store_id', $storeId)
				->where('status', 1)
				->where('UPPER(promotion_code)', strtoupper($savedCart->coupon_code))
				->where('start_date <=', date('Y-m-d'))
				->where('end_date >=', date('Y-m-d'))
				->get('db_promotions')->row();
			if($promo && (empty($promo->min_spend) || $subtotal >= (float)$promo->min_spend)){
				$coupon = $savedCart->coupon_code;
			}
		}
		$out(array('status' => true, 'items' => $cart['items'], 'dropped' => $cart['dropped'], 'coupon' => $coupon));
	}

	/**
	 * Back-in-stock subscription (public). One row per (store,item,email);
	 * notified by the cron when the item's stock returns above zero.
	 */
	public function notify_restock(){
		$storeId = (int)$this->input->post('store_id');
		$itemId  = (int)$this->input->post('item_id');
		$email   = trim((string)$this->input->post('email', TRUE));
		$out = function($arr){ $arr['csrf_hash'] = $this->security->get_csrf_hash(); echo json_encode($arr); };
		$settings = $storeId ? $this->storefront_model->getSettings($storeId) : null;
		if(!$settings || $settings->store_status != 'active' || !$itemId || !filter_var($email, FILTER_VALIDATE_EMAIL)){
			$out(['status'=>false,'message'=>'Enter a valid email']); return;
		}
		$item = $this->storefront_model->getOnlineProduct($itemId, $storeId);
		if(!$item){ $out(['status'=>false,'message'=>'Item not found']); return; }
		$this->load->model('Marketing_model','marketing_m');
		$ok = $this->marketing_m->subscribeStockAlert($storeId, $itemId, $item->item_name, $email);
		$out(['status' => (bool)$ok, 'message' => $ok ? 'We will email you when it is back in stock.' : 'Could not subscribe']);
	}

	/**
	 * One-click campaign unsubscribe (token in link). Writes an
	 * authoritative suppression row so pending AND future campaign sends
	 * to this recipient/channel are skipped at dispatch time.
	 */
	public function campaign_unsubscribe($token = ''){
		$this->load->model('marketing_model', 'marketing_m');
		$send = $this->marketing_m->unsubscribeByToken($token);
		$ok = !empty($send);
		echo '<!DOCTYPE html><html><body style="font-family:sans-serif;text-align:center;padding:60px 20px;"><h2>' .
			($ok ? 'Unsubscribed' : 'Link expired') . '</h2><p>' .
			($ok ? 'You will not receive further ' . htmlspecialchars($send->channel === 'sms' ? 'SMS ' : 'email ') . 'messages from this store.' : 'This unsubscribe link is invalid or already used.') .
			'</p></body></html>';
	}

	/** One-click unsubscribe for stock alerts (token in link). */
	public function stock_unsubscribe($token = ''){
		$this->load->model('Marketing_model','marketing_m');
		$ok = $this->marketing_m->unsubscribeStockAlert($token);
		echo '<!DOCTYPE html><html><body style="font-family:sans-serif;text-align:center;padding:60px 20px;"><h2>' .
			($ok ? 'Unsubscribed' : 'Link expired') . '</h2><p>' .
			($ok ? 'You will no longer receive back-in-stock alerts for this item.' : 'This unsubscribe link is invalid or already used.') .
			'</p></body></html>';
	}

	/**
	 * Customer opt-out from cart recovery (public, token-scoped). Sets
	 * opt_out so the cart disappears from the merchant list and the
	 * automated sender skips it.
	 */
	public function cart_optout(){
		$storeId = (int)$this->input->post('store_id');
		$token   = (string)$this->input->post('cart_token');
		$out = function($arr){ $arr['csrf_hash'] = $this->security->get_csrf_hash(); echo json_encode($arr); };
		if(!$storeId || !preg_match('/^[0-9a-f]{32,64}$/', (string)$token)){ $out(['status'=>false]); return; }
		$out(['status' => (bool)$this->storefront_model->optOutCart($storeId, $token)]);
	}

	/**
	 * Order Received (Thank You) page
	 * URL: /store/{store_slug}/order_received/{order_code}
	 */
	public function order_received($storeSlug = '', $orderCode = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$order = $this->storefront_model->getOrderByCode($orderCode, $storeId);
		if(!$order){
			show_404();
			return;
		}
		$items = $this->storefront_model->getOrderItems($order->id);

		$downloadLinks = [];
		if($order->payment_status === 'paid' && $order->order_status === 'completed'){
			foreach($items as $item){
				if($item->item_type === 'digital' && !empty($item->download_token)){
					$downloadLinks[] = [
						'name' => $item->item_name,
						'url' => base_url('store/' . $settings->store_slug . '/download/' . $item->download_token)
					];
				}
			}
		}

		$data = [
			'settings' => $settings,
			'store' => $store,
			'order' => $order,
			'items' => $items,
			'download_links' => $downloadLinks,
			'store_currency' => $this->theme_engine->getStoreCurrency(),
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
			'social_links' => $this->theme_engine->socialLinks(),
			'seo_title' => 'Order Confirmed - ' . ($store->store_name ?? 'Store'),
			'seo_description' => 'Your order has been received.',
			'seo_canonical' => base_url('store/' . $settings->store_slug . '/order_received/' . $orderCode),
			'page_title' => 'Order Received',
		];
		$this->load->view('storefront/order_received', $data);
	}

	/**
	 * Secure digital product download by token
	 * URL: /store/{store_slug}/download/{token}
	 */
	public function download($storeSlug = '', $token = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;

		if(empty($token)){
			show_404();
			return;
		}

		$orderItem = $this->db->where('download_token', $token)->where('item_type', 'digital')->get('db_online_order_items')->row();
		if(!$orderItem){
			show_error('Download link not found.', 404);
			return;
		}

		$order = $this->storefront_model->getOrder($orderItem->order_id);
		if(!$order || $order->store_id != $storeId || $order->payment_status !== 'paid' || $order->order_status !== 'completed'){
			show_error('Order is not ready for download.', 403);
			return;
		}

		if($orderItem->download_expires_at && strtotime($orderItem->download_expires_at) < time()){
			show_error('This download link has expired.', 403);
			return;
		}

		$product = $this->db->where('id', $orderItem->item_id)->where('store_id', $storeId)->get('db_items')->row();
		if(!$product || empty($product->digital_file)){
			show_error('File not found.', 404);
			return;
		}

		$limit = (int)($product->download_limit ?? 3);
		if($limit > 0 && (int)($orderItem->download_count) >= $limit){
			show_error('Download limit reached.', 403);
			return;
		}

		$filePath = FCPATH . $product->digital_file;
		if(!file_exists($filePath) || !is_file($filePath)){
			show_error('File not found on server.', 404);
			return;
		}

		// Increment download count
		$this->db->where('id', $orderItem->id)->update('db_online_order_items', ['download_count' => ($orderItem->download_count + 1)]);

		// Serve file
		$filename = basename($filePath);
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Content-Length: ' . filesize($filePath));
		readfile($filePath);
		exit;
	}

	/**
	 * Verify a Paystack reference against a specific online order and, on
	 * success, atomically claim the unpaid->paid transition and fulfil it.
	 * Returns the gateway verify payload on success, false otherwise.
	 * Side effects (digital delivery, emails) only run for the request that
	 * wins the atomic claim — replays are safe.
	 */
	private function _verifyAndFulfilPaystackOrder($order, $reference){
		$this->load->model('paystack_model', 'paystack');
		$verify = $this->paystack->verify_transaction($reference, $order->store_id);

		// Full verification contract: gateway success + reference belongs to
		// this order + amount matches the order total + currency matches the
		// store currency (when a code can be derived).
		$verified = false;
		if($verify['status'] && $verify['payment_status'] === 'success'
			&& $verify['reference'] === $order->order_code
			&& abs((float)$verify['amount'] - (float)$order->grand_total) < 0.01){

			$verified = true;
			$storeCur = $this->_storeCurrencyCode($order->store_id);
			if($storeCur && strtoupper($verify['currency']) !== $storeCur){
				$verified = false;
				log_message('error', "Paystack currency mismatch on order {$order->id}: gateway {$verify['currency']} vs store {$storeCur}");
			}
		} else if(!empty($verify['status'])){
			log_message('error', "Paystack verify failed checks on order {$order->id}: status=" . ($verify['payment_status'] ?? '?') . " ref=" . ($verify['reference'] ?? '?') . " amount=" . ($verify['amount'] ?? '?') . " expected={$order->grand_total}");
		}

		if(!$verified){
			return false;
		}

		// Atomically claim the transition; only the winning request fulfils.
		$claimed = $this->storefront_model->claimPaidOrder($order->id, [
			'paystack_reference' => $reference,
			'paystack_amount' => $verify['amount'],
			'order_status' => 'paid'
		]);
		if($claimed){
			// Commit the stock reservation (reserved -> committed; no second
			// decrement — units already moved at order time).
			$this->storefront_model->adjustStock($order->id);
			// Paid conversion — recorded only on the verified transition;
			// deduped by event_id across callback/webhook/browser retries.
			$this->storefront_model->recordPurchaseEvent($order->id, 'paystack_verify');
			$items = $this->storefront_model->getOrderItems($order->id);
			$settings = $this->storefront_model->getSettings($order->store_id);
			$store = get_store_details($order->store_id);
			$this->_send_digital_delivery_email($order, $items, $store, $settings);
		} else {
			// Payment verified but the order can no longer be claimed — e.g.
			// the reservation was already released and the order cancelled.
			// The money is real, so record a visible reconciliation exception
			// rather than letting a late payment disappear.
			$this->load->model('payment_reconcile_model','recon');
			$this->recon->queue_late_payment($order->store_id, array(
				'provider'        => 'paystack',
				'reference'       => $reference,
				'order_id'        => $order->id,
				'amount'          => $verify['amount'],
				'expected_amount' => $order->grand_total,
				'currency'        => $verify['currency'] ?? null,
				'detail'          => 'Verified payment received for order ' . $order->order_code
					. ' which is already ' . $order->order_status . '/' . $order->payment_status
					. ' (stock ' . ($order->stock_state ?? 'n/a') . '). Refund the customer or reinstate the order.',
				'detected_by'     => 'storefront_verify',
			));
		}
		// Fulfilment is separate from the claim and idempotent: it runs for
		// the winner now, and self-heals here on any later verify/callback
		// if the winning request died mid-fulfilment (fulfilled_at gates it).
		$this->storefront_model->fulfilPaidOrder($order->id);
		return $verify;
	}

	/**
	 * Paystack callback for online orders (browser redirect / manual check).
	 */
	public function paystack_callback(){
		$reference = $this->input->get('reference') ?: $this->input->post('reference');
		if(!$reference){
			show_error('No reference provided', 400);
			return;
		}

		// Resolve the order first — the order owns the store, never the session.
		$order = $this->storefront_model->getOrderByPaymentReference($reference);
		if(!$order){
			show_error('Order not found for this payment reference', 404);
			return;
		}

		if($this->_verifyAndFulfilPaystackOrder($order, $reference)){
			$data = ['success' => true, 'message' => 'Payment successful!', 'reference' => $reference];
		} else {
			// Failed verification releases the stock reservation (once).
			if($order->payment_status === 'unpaid'){
				$this->db->trans_begin();
				if($this->storefront_model->claimFailedOrder($order->id)){
					if(!$this->storefront_model->restoreStock($order->id) || $this->db->trans_status() === FALSE){
						$this->db->trans_rollback();
						log_message('error', 'Paystack failed callback could not atomically release order ' . (int)$order->id);
					} else {
						$this->db->trans_commit();
					}
				} else {
					$this->db->trans_rollback();
				}
			}
			$data = ['success' => false, 'message' => 'Payment was not successful. Please try again.', 'reference' => $reference];
		}
		$this->load->view('storefront/paystack_callback', $data);
	}

	/**
	 * JSON payment verification for the storefront Paystack popup.
	 * The inline popup callback runs in the browser, so the client asks the
	 * server to verify the reference against the order before showing success.
	 */
	public function verify_payment(){
		$reference = $this->input->get('reference') ?: $this->input->post('reference');
		if(!$reference){
			echo json_encode(['status' => false, 'message' => 'No reference provided']);
			return;
		}
		$order = $this->storefront_model->getOrderByPaymentReference($reference);
		if(!$order){
			echo json_encode(['status' => false, 'message' => 'Order not found']);
			return;
		}

		// Already paid (webhook or earlier verify won the claim). If the
		// winning request died mid-fulfilment, re-run it — it is idempotent.
		if($order->payment_status === 'paid'){
			if(empty($order->fulfilled_at)){
				$this->storefront_model->fulfilPaidOrder($order->id);
			}
			echo json_encode([
				'status' => true,
				'order_code' => $order->order_code,
				'redirect_url' => $this->_orderReceivedUrl($order),
			]);
			return;
		}

		if($this->_verifyAndFulfilPaystackOrder($order, $reference)){
			echo json_encode([
				'status' => true,
				'order_code' => $order->order_code,
				'redirect_url' => $this->_orderReceivedUrl($order),
			]);
			return;
		}

		echo json_encode(['status' => false, 'message' => 'Payment could not be verified. If you were charged, contact the store.']);
	}

	private function _orderReceivedUrl($order){
		$settings = $this->storefront_model->getSettings($order->store_id);
		return base_url('store/' . ($settings->store_slug ?? '') . '/order_received/' . $order->order_code);
	}

	/**
	 * Derive the ISO currency code for a store (e.g. "Nigerian Naira (NGN)").
	 * Returns null when it cannot be determined — callers fall back to NGN.
	 */
	private function _storeCurrencyCode($storeId){
		$row = $this->db->query(
			"SELECT c.currency_code, c.currency_name, c.currency FROM db_currency c JOIN db_store s ON s.currency_id = c.id WHERE s.id = ? LIMIT 1",
			[$storeId]
		)->row();
		if(!$row){
			return null;
		}
		// Prefer the ISO code column; then a "(XXX)" suffix in the name;
		// then the symbol field when it already holds an ISO code.
		if(!empty($row->currency_code) && preg_match('/^[A-Za-z]{3}$/', trim($row->currency_code))){
			return strtoupper(trim($row->currency_code));
		}
		if(preg_match('/\(([A-Za-z]{3})\)/', $row->currency_name ?? '', $m)){
			return strtoupper($m[1]);
		}
		if(preg_match('/^[A-Za-z]{3}$/', trim($row->currency ?? ''))){
			return strtoupper(trim($row->currency));
		}
		return null;
	}

	/**
	 * QR Store redirect
	 * URL: /qr/{qr_id}
	 */
	public function qr($qrId = 0){
		$qr = $this->db->where('id', $qrId)->where('status', 1)->get('db_qr_codes')->row();
		if(!$qr){
			show_404();
			return;
		}

		$settings = $this->storefront_model->getSettings($qr->store_id);
		if(!$settings){
			show_404();
			return;
		}

		switch($qr->qr_type){
			case 'product':
				redirect(base_url('store/' . $settings->store_slug . '/product/' . $qr->related_id));
				break;
			case 'service':
				redirect(base_url('store/' . $settings->store_slug . '/service/' . $qr->related_id));
				break;
			case 'category':
				redirect(base_url('store/' . $settings->store_slug . '/products?category=' . $qr->related_id));
				break;
			case 'table':
				redirect(base_url('store/' . $settings->store_slug . '?table=' . urlencode($qr->table_number)));
				break;
			case 'attendance':
				redirect(base_url('attendance/clockin'));
				break;
			default:
				redirect(base_url('store/' . $settings->store_slug));
				break;
		}
	}

	private function _buildOtpEmail($otp, $settings, $store, $name = ''){
		$storeName = htmlspecialchars($settings->store_name ?? ($store->store_name ?? 'Store'));
		$firstName = htmlspecialchars($name ?: 'there');
		$storeEmail = htmlspecialchars($settings->store_email ?? ($store->email ?? ''));
		$storePhone = htmlspecialchars($settings->store_phone ?? ($store->mobile ?? ''));
		$storeAddress = htmlspecialchars($settings->store_address ?? ($store->address ?? ''));
		$supportLink = !empty($storeEmail) ? 'mailto:' . $storeEmail : '#';
		$logo = '';
		if(!empty($settings->store_logo) && file_exists($settings->store_logo)){
			$logo = base_url($settings->store_logo);
		} elseif(!empty($store->store_logo) && file_exists($store->store_logo)){
			$logo = base_url($store->store_logo);
		} else {
			$logo = base_url('theme/dist/img/logo1.png');
		}
		$otpDigits = str_split($otp);
		$otpBoxes = '';
		foreach($otpDigits as $d){
			$otpBoxes .= '<td style="width:48px;height:60px;border:1px solid #E2E8F0;border-radius:10px;background:#fff;text-align:center;vertical-align:middle;font-size:28px;font-weight:800;color:#10B981;">' . $d . '</td>';
		}
		return '<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>' . $storeName . ' - Verification Code</title>
  <style type="text/css">
    body { margin:0; padding:0; background-color:#F1F5F9; font-family:-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    .wrapper { width:100%; padding:40px 0; background-color:#F1F5F9; }
    .container { width:100%; max-width:520px; margin:0 auto; background:#ffffff; border-radius:16px; box-shadow:0 4px 24px rgba(0,0,0,0.06); overflow:hidden; }
    .header { padding:32px 40px 24px; text-align:center; background:#ffffff; border-bottom:1px solid #F1F5F9; }
    .header img { max-height:48px; display:inline-block; }
    .brand-name { margin-top:12px; font-size:18px; font-weight:800; color:#0F172A; letter-spacing:-0.2px; }
    .body { padding:40px; }
    .tag { display:inline-block; padding:6px 14px; border-radius:999px; background:#E0E7FF; color:#3B82F6; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:20px; }
    .greeting { font-size:18px; font-weight:700; color:#0F172A; margin-bottom:10px; }
    .message { font-size:15px; line-height:1.6; color:#475569; margin-bottom:28px; }
    .code-section { text-align:center; margin-bottom:28px; }
    .code-table { margin:0 auto; border-spacing:8px; }
    .expires { font-size:13px; color:#64748B; text-align:center; }
    .footer { padding:24px 40px; text-align:center; background:#F8FAFC; border-top:1px solid #F1F5F9; }
    .footer p { margin:4px 0; font-size:13px; color:#64748B; line-height:1.5; }
    .footer a { color:#3B82F6; text-decoration:none; }
    .help { margin-top:16px; font-size:13px; color:#64748B; }
    .copyright { margin-top:20px; padding-top:16px; border-top:1px solid #E2E8F0; font-size:12px; color:#94A3B8; }
  </style>
</head>
<body>
  <table class="wrapper" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td align="center">
    <table class="container" role="presentation" width="520" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td class="header">
          <img src="' . $logo . '" alt="' . $storeName . '" onerror="this.style.display=\'none\'">
          <div class="brand-name">' . $storeName . '</div>
        </td>
      </tr>
      <tr>
        <td class="body">
          <span class="tag">Security &amp; System</span>
          <div class="greeting">Hi ' . $firstName . ',</div>
          <div class="message">Thanks for signing in to your account. Your verification code is <strong>' . $otp . '</strong>. This code expires in <strong>10 minutes</strong>.</div>
          <div class="code-section">
            <table class="code-table" role="presentation" cellspacing="0" cellpadding="0" border="0">
              <tr>' . $otpBoxes . '</tr>
            </table>
          </div>
          <div class="expires">If you did not request this code, you can safely ignore this email.</div>
        </td>
      </tr>
      <tr>
        <td class="footer">
          ' . (!empty($storeAddress) ? '<p>' . $storeAddress . '</p>' : '') . '
          ' . (!empty($storePhone) ? '<p><a href="tel:' . preg_replace("/[^0-9]/", "", $storePhone) . '">' . $storePhone . '</a></p>' : '') . '
          ' . (!empty($storeEmail) ? '<p><a href="' . $supportLink . '">' . $storeEmail . '</a></p>' : '') . '
          <div class="copyright">&copy; ' . date('Y') . ' ' . $storeName . '. All rights reserved.<br>Powered by MartPoint.</div>
        </td>
      </tr>
    </table>
  </td></tr></table>
</body>
</html>';
	}

	// ============== CUSTOMER PORTAL ==============

	public function verify($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$prefillPhone = $this->input->get('phone');
		$data = [
			'settings' => $settings,
			'store' => $store,
			'seo_title' => 'Verify Phone - ' . ($store->store_name ?? 'Store'),
			'prefill_phone' => $prefillPhone,
			'csrf_name' => $this->security->get_csrf_token_name(),
			'csrf_hash' => $this->security->get_csrf_hash()
		];
		$this->load->view('storefront/verify', $data);
	}

	public function send_otp($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$method = trim($this->input->post('method', TRUE) ?: 'phone');
		$phone = trim($this->input->post('phone', TRUE) ?: '');
		$email = trim($this->input->post('email', TRUE) ?: '');
		$name = trim($this->input->post('name', TRUE) ?: '');

		$contactField = ($method === 'email') ? 'email' : 'phone';
		$contactValue = ($method === 'email') ? $email : $phone;

		if($method === 'email'){
			if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
				echo json_encode(['status' => false, 'message' => 'Enter a valid email address', 'csrf_hash' => $this->security->get_csrf_hash()]);
				return;
			}
		} else {
			if(empty($phone) || strlen(preg_replace('/[^0-9]/', '', $phone)) < 7){
				echo json_encode(['status' => false, 'message' => 'Enter a valid phone number', 'csrf_hash' => $this->security->get_csrf_hash()]);
				return;
			}
			$phone = preg_replace('/[^0-9]/', '', $phone);
		}

		// Generate, store and deliver through the shared OTP service so the
		// store's ACTIVE provider is used (previously this was hardcoded to
		// Sendchamp, so OTP silently failed for every other provider).
		$this->load->library('otp_service');
		$this->otp_service->expiry = 600;          // 10 minutes
		$this->otp_service->max_attempts = 5;      // unchanged lockout
		$this->otp_service->resend_cooldown = 0;   // storefront allows resend

		$opts = [
			'store_name' => $settings->store_name ?? 'MartPoint',
			'return_code'=> false,
		];
		if($method === 'email'){
			$opts['subject'] = 'Your ' . ($settings->store_name ?? 'Store') . ' verification code';
			// Keep the storefront's branded email; the service injects the
			// real generated code so the email can never disagree with the
			// stored OTP.
			$opts['html_builder'] = function($code, $minutes) use ($settings, $store, $name) {
				return $this->_buildOtpEmail($code, $settings, $store, $name);
			};
			$opts['text'] = 'Hi ' . ($name ?: 'there') . ",\n\nYour " . ($settings->store_name ?? 'MartPoint')
				. " verification code is {{OTP}}. It expires in {{MINUTES}} minutes.\n\nIf you did not request this, please ignore it.";
		}

		$sent = $this->otp_service->send('storefront', ($method === 'email' ? 'email' : 'phone'), $contactValue, $storeId, $opts);

		if(!empty($sent['ok'])){
			echo json_encode(['status' => true, 'message' => 'OTP sent', 'csrf_hash' => $this->security->get_csrf_hash()]);
		} else {
			$msg = $sent['message'] ?? (($method === 'email') ? 'Could not send email. Please try again or use phone.' : 'Could not send OTP. Please try again or use email.');
			echo json_encode(['status' => false, 'message' => $msg, 'csrf_hash' => $this->security->get_csrf_hash()]);
		}
	}

	public function verify_otp($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$method = trim($this->input->post('method', TRUE) ?: 'phone');
		$phone = preg_replace('/[^0-9]/', '', trim($this->input->post('phone', TRUE) ?: ''));
		$email = trim($this->input->post('email', TRUE) ?: '');
		$otp = trim($this->input->post('otp', TRUE) ?: '');
		$name = trim($this->input->post('name', TRUE) ?: 'Customer');

		$contactField = ($method === 'email') ? 'email' : 'phone';
		$contactValue = ($method === 'email') ? $email : $phone;

		if(empty($contactValue) || empty($otp)){
			echo json_encode(['status' => false, 'message' => 'Contact and OTP are required', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}

		// Verification (lookup, expiry, attempt limit, single-use) is owned by
		// the shared service so storefront/admin/POS cannot drift apart.
		$this->load->library('otp_service');
		$this->otp_service->max_attempts = 5;
		$check = $this->otp_service->verify(
			'storefront',
			($method === 'email' ? 'email' : 'phone'),
			$contactValue,
			$otp,
			$storeId
		);
		if(empty($check['ok'])){
			echo json_encode([
				'status' => false,
				// Preserve the original user-facing wording for the common case.
				'message' => ($check['message'] === 'Invalid or expired code') ? 'Invalid or expired OTP' : $check['message'],
				'csrf_hash' => $this->security->get_csrf_hash()
			]);
			return;
		}

		if($method === 'email'){
			$customer = $this->db->where('store_id', $storeId)->where('email', $email)->get('db_customers')->row();
			$customerId = $customer ? $customer->id : $this->customers_model->getOrCreateStorefrontCustomer($storeId, $name, '', $email);
		} else {
			$customer = $this->db->where('store_id', $storeId)->where('mobile', $phone)->get('db_customers')->row();
			$customerId = $customer ? $customer->id : $this->customers_model->getOrCreateStorefrontCustomer($storeId, $name, $phone, '');
		}

		$token = bin2hex(random_bytes(32));
		$expires = date('Y-m-d H:i:s', strtotime('+7 days'));
		$this->storefront_model->createPortalSession([
			'store_id' => $storeId,
			'customer_id' => $customerId,
			'phone' => $phone,
			'email' => $email,
			'session_token' => $token,
			'expires_at' => $expires
		]);

		$this->load->helper('cookie');
		set_cookie([
			'name' => 'customer_token',
			'value' => $token,
			'expire' => 7 * 24 * 60 * 60,
			'path' => '/',
			'prefix' => '',
			'secure' => FALSE,
			'httponly' => TRUE
		]);

		echo json_encode([
			'status' => true,
			'message' => 'Verified',
			'csrf_hash' => $this->security->get_csrf_hash()
		]);
	}

	public function account($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$token = $this->input->cookie('customer_token', TRUE);
		if(!$token){
			redirect(base_url('store/' . $settings->store_slug . '/verify'));
			return;
		}

		$session = $this->storefront_model->getCustomerPortalSession($token, $storeId);
		if(!$session){
			$this->load->helper('cookie');
			delete_cookie('customer_token');
			redirect(base_url('store/' . $settings->store_slug . '/verify'));
			return;
		}

		$customer = $this->db->where('id', $session->customer_id)->get('db_customers')->row();
		$orders = $this->storefront_model->getOrdersByCustomer($session->customer_id, $storeId, 5);

		// Build customer's active digital downloads
		$downloads = [];
		$customerOrders = $this->storefront_model->getOrdersByCustomer($session->customer_id, $storeId, 100);
		$orderIds = array_column($customerOrders, 'id');
		if(!empty($orderIds)){
			$orderMap = [];
			foreach($customerOrders as $o) $orderMap[$o->id] = $o;
			$items = $this->db->where_in('order_id', $orderIds)
							  ->where('item_type', 'digital')
							  ->where('download_token IS NOT NULL', NULL, FALSE)
							  ->get('db_online_order_items')->result();
			foreach($items as $item){
				$order = $orderMap[$item->order_id] ?? null;
				if($order && $order->payment_status === 'paid' && $order->order_status === 'completed'){
					$downloads[] = [
						'name' => $item->item_name,
						'url' => base_url('store/' . $settings->store_slug . '/download/' . $item->download_token),
						'expires' => $item->download_expires_at,
						'order_code' => $order->order_code
					];
				}
			}
		}

		// Build customer's enrolled courses
		$this->load->model('course_model');
		$this->load->model('creator_membership_model', 'membership_model');
		$customerId = $customer->id;

		$courses = [];
		$enrollments = $this->course_model->getEnrollmentsByCustomer($customerId, $storeId);
		foreach($enrollments as $e){
			$course = $this->course_model->getByItemId($e->item_id, $storeId);
			if(!$course) continue;
			$courses[] = [
				'name' => $course->title,
				'progress' => $this->course_model->getCourseProgressPercent($course->id, $e->id),
				'url' => base_url('store/' . $settings->store_slug . '/course/' . $course->id)
			];
		}

		$memberships = [];
		$subscriptions = $this->membership_model->getSubscriptionsByCustomer($customerId, $storeId);
		foreach($subscriptions as $s){
			$membership = $this->membership_model->getByItemId($s->item_id, $storeId);
			if(!$membership) continue;
			$memberships[] = [
				'name' => $membership->membership_name,
				'status' => $s->status,
				'end_date' => $s->end_date,
				'is_active' => $this->membership_model->isActive($s)
			];
		}

		$data = [
			'settings' => $settings,
			'store' => $store,
			'customer' => $customer,
			'orders' => $orders,
			'downloads' => $downloads,
			'courses' => $courses,
			'memberships' => $memberships,
			'csrf_name' => $this->security->get_csrf_token_name(),
			'csrf_hash' => $this->security->get_csrf_hash()
		];
		$this->load->view('storefront/account', $data);
	}

	public function account_orders($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$token = $this->input->cookie('customer_token', TRUE);
		if(!$token){
			redirect(base_url('store/' . $settings->store_slug . '/verify'));
			return;
		}

		$session = $this->storefront_model->getCustomerPortalSession($token, $storeId);
		if(!$session){
			$this->load->helper('cookie');
			delete_cookie('customer_token');
			redirect(base_url('store/' . $settings->store_slug . '/verify'));
			return;
		}

		$orders = $this->storefront_model->getOrdersByCustomer($session->customer_id, $storeId, 50);
		$customer = $this->db->where('id', $session->customer_id)->get('db_customers')->row();

		$data = [
			'settings' => $settings,
			'store' => $store,
			'customer' => $customer,
			'orders' => $orders,
			'csrf_name' => $this->security->get_csrf_token_name(),
			'csrf_hash' => $this->security->get_csrf_hash()
		];
		$this->load->view('storefront/account_orders', $data);
	}

	public function account_logout($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$token = $this->input->cookie('customer_token', TRUE);
		if($token){
			$this->db->where('session_token', $token)->where('store_id', $storeId)->delete('db_storefront_customer_sessions');
		}
		$this->load->helper('cookie');
		delete_cookie('customer_token');
		redirect(base_url('store/' . $settings->store_slug));
	}

	/**
	 * Customer course list (My Courses)
	 */
	public function my_courses($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$token = $this->input->cookie('customer_token', TRUE);
		if(!$token){
			redirect(base_url('store/' . $settings->store_slug . '/verify'));
			return;
		}
		$session = $this->storefront_model->getCustomerPortalSession($token, $storeId);
		if(!$session){
			$this->load->helper('cookie');
			delete_cookie('customer_token');
			redirect(base_url('store/' . $settings->store_slug . '/verify'));
			return;
		}

		$this->load->model('course_model');
		$customerId = $session->customer_id;
		$enrollments = $this->course_model->getEnrollmentsByCustomer($customerId, $storeId);
		$courses = [];
		foreach($enrollments as $e){
			$course = $this->course_model->getByItemId($e->item_id, $storeId);
			if(!$course) continue;
			$courses[] = [
				'course' => $course,
				'progress' => $this->course_model->getCourseProgressPercent($course->id, $e->id),
				'url' => base_url('store/' . $settings->store_slug . '/course/' . $course->id)
			];
		}

		$customer = $this->db->where('id', $session->customer_id)->get('db_customers')->row();
		$data = [
			'settings' => $settings,
			'store' => $store,
			'customer' => $customer,
			'courses' => $courses,
			'page_title' => 'My Courses',
			'seo_title' => 'My Courses - ' . ($store->store_name ?? 'Store'),
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
		];
		$this->load->view('storefront/my_courses', $data);
	}

	/**
	 * Single course view for a customer
	 */
	public function course($storeSlug = '', $courseId = 0){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$token = $this->input->cookie('customer_token', TRUE);
		if(!$token){
			redirect(base_url('store/' . $settings->store_slug . '/verify'));
			return;
		}
		$session = $this->storefront_model->getCustomerPortalSession($token, $storeId);
		if(!$session){
			$this->load->helper('cookie');
			delete_cookie('customer_token');
			redirect(base_url('store/' . $settings->store_slug . '/verify'));
			return;
		}

		$this->load->model('course_model');
		$customerId = $session->customer_id;
		$course = $this->course_model->get($courseId, $storeId);
		if(!$course || $course->store_id != $storeId){
			show_404();
			return;
		}

		$enrollment = $this->course_model->getEnrollment($customerId, $course->id, $storeId);
		if(!$enrollment){
			show_404();
			return;
		}

		$modules = $this->course_model->getModules($course->id);
		$lessons = $this->course_model->getLessons($course->id);
		$progressLessons = $this->course_model->getProgress($enrollment->id);
		$completedIds = array_column($progressLessons, 'lesson_id');
		$customer = $this->db->where('id', $customerId)->get('db_customers')->row();

		$data = [
			'settings' => $settings,
			'store' => $store,
			'customer' => $customer,
			'course' => $course,
			'modules' => $modules,
			'lessons' => $lessons,
			'completed_ids' => $completedIds,
			'enrollment' => $enrollment,
			'progress' => $this->course_model->getCourseProgressPercent($course->id, $enrollment->id),
			'page_title' => $course->title,
			'seo_title' => $course->title . ' - ' . ($store->store_name ?? 'Store'),
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
		];
		$this->load->view('storefront/course', $data);
	}

	// ============== HELPERS ==============

	/**
	 * Theme preview is private: it only renders for the merchant who owns
	 * this store (dashboard session with the same store_id), never for
	 * anonymous visitors. Anonymous traffic always sees the published theme.
	 */
	private function _previewThemeForVisitor($settings){
		if(empty($settings->preview_mode) || empty($settings->preview_theme_id)){
			return null;
		}
		return ((int)get_current_store_id() === (int)$settings->store_id)
			? $settings->preview_theme_id
			: null;
	}

	private function _getSettingsOr404($slug){
		// 1. Try custom domain lookup first
		$host = strtolower($this->input->server('HTTP_HOST'));
		if($host){
			$domain = $this->storefront_model->getStoreByDomain($host);
			if($domain){
				$settings = $this->storefront_model->getSettings($domain->store_id);
				if($settings) return $settings;
			}
		}

		$settings = $this->storefront_model->getStoreBySlug($slug);
		$viaStoreId = false;
		if(!$settings){
			$storeId = (int)$this->input->get('store_id');
			if($storeId){
				$settings = $this->storefront_model->getSettings($storeId);
				$viaStoreId = !empty($settings);
			}
		}
		// A slug was supplied but didn't resolve to that store —
		// getStoreBySlug() falls back to generic/current-store defaults which
		// would render the wrong shop (or fatal on an empty store_id).
		if($slug !== '' && !$viaStoreId && (!$settings || strcasecmp((string)($settings->store_slug ?? ''), $slug) !== 0)){
			show_404();
			return;
		}
		if(!$settings && ($slug === '' || $slug === null)){
			// Last resort: try first active storefront
			$settings = $this->db->where('store_status', 'active')->order_by('id', 'asc')->get('db_storefront_settings')->row();
		}
		if(!$settings || $settings->store_status != 'active'){
			$status = ($settings && isset($settings->store_status)) ? $settings->store_status : 'missing';
			$output = $this->load->view('storefront/maintenance', [
				'store_status' => $status,
				'page_title' => ($status === 'maintenance') ? 'Under Maintenance' : 'Unavailable'
			], TRUE);
			echo $output;
			exit;
		}
		return $settings;
	}

	/**
	 * Send order notification emails to store owner and customer.
	 * Uses editable email templates (online_order_owner, online_order_customer).
	 * Silently fails if email is not configured — never blocks checkout.
	 */
	private function _send_digital_delivery_email($order, $items, $store, $settings){
		$customerEmail = $order->customer_email ?? '';
		if(empty($customerEmail) || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)){
			return;
		}

		$downloadLinks = [];
		foreach($items as $item){
			if($item->item_type === 'digital' && !empty($item->download_token)){
				$downloadLinks[] = [
					'name' => $item->item_name,
					'url' => base_url('store/' . ($settings->store_slug ?? '') . '/download/' . $item->download_token)
				];
			}
		}
		if(empty($downloadLinks)){
			return;
		}

		$this->load->model('email_service');
		$this->email_service->setStoreId($order->store_id);

		$storeName = htmlspecialchars($store->store_name ?? ($settings->store_name ?? 'Store'));
		$subject = 'Your digital download is ready — ' . $storeName;

		$html = '<div style="font-family:Inter,system-ui,sans-serif;max-width:600px;margin:0 auto;padding:32px;color:#0F172A;">';
		$html .= '<h2 style="margin-top:0;">Your purchase is ready for download</h2>';
		$html .= '<p>Thank you for your order <strong>' . htmlspecialchars($order->order_code) . '</strong> from ' . $storeName . '.</p>';
		$html .= '<p>Click the links below to download your digital products. Each link is unique and will expire based on the product settings.</p>';
		$html .= '<ul style="padding-left:20px;line-height:1.8;">';
		foreach($downloadLinks as $link){
			$html .= '<li><a href="' . $link['url'] . '" style="color:#2563EB;font-weight:600;">' . htmlspecialchars($link['name']) . '</a></li>';
		}
		$html .= '</ul>';
		$html .= '<p style="margin-top:24px;font-size:13px;color:#64748B;">If you have trouble downloading, contact ' . $storeName . '.</p>';
		$html .= '</div>';

		$this->email_service->sendRaw($customerEmail, $subject, $html);
	}

	private function _send_order_emails($order, $items, $store, $settings){
		try {
			$this->load->model('email_service');
			$this->load->model('email_settings_model');
			$this->load->model('email_template_model');

			// Email service must run in the order's store context (public storefront has no session store)
			$this->email_service->setStoreId($order->store_id);

			// Skip if email provider is not configured
			if(!$this->email_settings_model->isReady($order->store_id)){
				log_message('debug', 'Storefront: Email provider not ready, skipping order emails for order ' . $order->order_code);
				return;
			}

			// Seed default templates if they don't exist yet for this store
			$this->email_template_model->seedDefaults($order->store_id);

			// Get currency symbol for this store
			$curRow = $this->db->query("SELECT a.currency as symbol, b.currency_placement as placement FROM db_currency a, db_store b WHERE a.id = b.currency_id AND b.id = ? LIMIT 1", [$order->store_id])->row();
			$currency = $curRow ? $curRow->symbol : '';
			$storeName = $store->store_name ?? 'MartPoint Retail';
			$storeEmail = !empty($settings->store_email) ? $settings->store_email : ($store->email ?? '');
			$paymentMethodLabel = ucfirst(str_replace('_', ' ', $order->payment_method));

			// Build item list HTML and text
			$itemsHtml = '';
			$itemsText = '';
			foreach($items as $item){
				$lineTotal = $currency . number_format($item['total_price'], 2);
				$itemsHtml .= "<tr><td style='padding:8px;border-bottom:1px solid #eee;'>" . htmlspecialchars($item['item_name']) . "</td>"
					. "<td style='padding:8px;border-bottom:1px solid #eee;text-align:center;'>" . $item['qty'] . "</td>"
					. "<td style='padding:8px;border-bottom:1px solid #eee;text-align:right;'>" . $lineTotal . "</td></tr>";
				$itemsText .= "- {$item['item_name']} x{$item['qty']} = {$lineTotal}\n";
			}

			// Build placeholder data for templates
			$tplData = [
				'order_code'        => $order->order_code,
				'customer_name'     => $order->customer_name,
				'customer_phone'    => $order->customer_phone,
				'customer_email'    => $order->customer_email ?: 'N/A',
				'customer_address'  => $order->customer_address ?: 'N/A',
				'payment_method'    => $paymentMethodLabel,
				'shipping_method'   => $order->shipping_method ?: 'N/A',
				'order_items'       => $itemsHtml,
				'order_items_text'  => $itemsText,
				'subtotal'          => $currency . number_format($order->subtotal, 2),
				'delivery_fee'      => $order->delivery_fee > 0 ? $currency . number_format($order->delivery_fee, 2) : 'Free',
				'grand_total'       => $currency . number_format($order->grand_total, 2),
				'store_name'        => $storeName,
				'store_email'       => $storeEmail ?: $storeName,
			];

			// ---- 1. Email to store owner ----
			if(!empty($storeEmail) && filter_var($storeEmail, FILTER_VALIDATE_EMAIL)){
				$result = $this->email_service->sendTemplate('online_order_owner', $storeEmail, $tplData, [
					'related_module' => 'storefront',
					'related_record_id' => $order->id,
				]);
				log_message('debug', 'Storefront: Owner email result: ' . json_encode($result));
			}

			// ---- 2. Confirmation email to customer ----
			if(!empty($order->customer_email) && filter_var($order->customer_email, FILTER_VALIDATE_EMAIL)){
				$result2 = $this->email_service->sendTemplate('online_order_customer', $order->customer_email, $tplData, [
					'related_module' => 'storefront',
					'related_record_id' => $order->id,
				]);
				log_message('debug', 'Storefront: Customer email result: ' . json_encode($result2));
			}
		} catch (Exception $e) {
			log_message('error', 'Storefront: Order email exception: ' . $e->getMessage());
		}
	}

	/**
	 * Dedicated About Us page
	 * URL: /store/{store_slug}/about
	 */
	public function about($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$aboutContent = !empty($settings->footer_about_us) ? $settings->footer_about_us : $settings->store_description;
		$canonical = base_url('store/' . $settings->store_slug . '/about');

		$data = [
			'settings' => $settings,
			'store' => $store,
			'about_content' => $aboutContent,
			'categories' => $this->storefront_model->getCategoriesWithItems($storeId),
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
			'social_links' => $this->theme_engine->socialLinks(),
			'store_currency' => $this->theme_engine->getStoreCurrency(),
			'seo_title' => 'About Us',
			'seo_description' => 'Learn more about ' . ($store->store_name ?? 'our store'),
			'seo_image' => $this->theme_engine->logoUrl() ?: base_url('uploads/site/icon.webp'),
			'seo_canonical' => $canonical,
			'seo_type' => 'website',
		];
		$this->theme_engine->view('about', $data);
	}

	/**
	 * On-demand image resize + cache endpoint
	 * URL: /image/{width}/{path}
	 */
	public function image($width = 0, ...$pathSegments){
		$width = (int)$width;
		if($width < 10 || $width > 1600) show_404();

		$path = implode('/', $pathSegments);
		$path = urldecode($path);
		$path = ltrim(str_replace('\\', '/', $path), '/');
		$fullPath = FCPATH . $path;

		if(!file_exists($fullPath) || is_dir($fullPath)) show_404();

		$real = realpath($fullPath);
		if($real === false || strpos($real, FCPATH) !== 0) show_404();

		$ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
		$allowed = ['jpg','jpeg','png','gif','webp','bmp'];
		if(!in_array($ext, $allowed)) show_404();

		$mimes = [
			'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
			'gif' => 'image/gif', 'webp' => 'image/webp', 'bmp' => 'image/bmp'
		];
		$mime = $mimes[$ext] ?? 'application/octet-stream';

		$quality = 85;
		$cacheRel = 'uploads/cache/img/' . $width . '/' . md5($path . '|q' . $quality) . '.' . $ext;
		$cacheFull = FCPATH . $cacheRel;

		if(file_exists($cacheFull) && filemtime($cacheFull) >= filemtime($real)){
			$this->_serveImage($cacheFull, $mime);
			return;
		}

		$dir = dirname($cacheFull);
		if(!is_dir($dir)) mkdir($dir, 0777, true);

		// Unsupported or resize-fail: copy original to cache so the static URL works next time
		if($ext === 'bmp' || $ext === 'gif'){
			copy($real, $cacheFull);
			$this->_serveImage($cacheFull, $mime);
			return;
		}

		$this->load->library('image_lib');
		$config = [
			'source_image' => $real,
			'new_image' => $cacheFull,
			'maintain_ratio' => TRUE,
			'width' => $width,
			'master_dim' => 'width'
		];

		if(in_array($ext, ['jpg','jpeg'])){
			$config['quality'] = $quality . '%';
		} elseif($ext === 'png'){
			$config['quality'] = '9';
		} elseif($ext === 'webp'){
			$config['quality'] = $quality;
		}

		$this->image_lib->initialize($config);
		if(!$this->image_lib->resize()){
			log_message('error', 'Image resize failed for ' . $path . ': ' . $this->image_lib->display_errors());
			$this->image_lib->clear();
			copy($real, $cacheFull);
			$this->_serveImage($cacheFull, $mime);
			return;
		}
		$this->image_lib->clear();
		$this->_serveImage($cacheFull, $mime);
	}

	private function _serveImage($file, $mime){
		$ts = filemtime($file);
		$etag = md5($file . $ts);
		http_response_code(200);
		if(!headers_sent()){
			header('Content-Type: ' . $mime, true);
			header('Content-Length: ' . filesize($file), true);
			header('Cache-Control: public, max-age=31536000, immutable', true);
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT', true);
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $ts) . ' GMT', true);
			header('ETag: "' . $etag . '"', true);
		}
		readfile($file);
		exit;
	}

	/**
	 * Vehicle listing
	 * URL: /store/{store_slug}/vehicles
	 */
	public function vehicles($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$this->load->model('Automobile_model', 'automobile_m');
		$vehicles = $this->automobile_m->get_all($storeId, 'available', 100);

		$canonical = base_url('store/' . $settings->store_slug . '/vehicles');
		$data = [
			'settings' => $settings,
			'store' => $store,
			'vehicles' => $vehicles,
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
			'store_currency' => $this->theme_engine->getStoreCurrency(),
			'seo_title' => 'Vehicles for Sale',
			'seo_description' => 'Browse available vehicles at ' . ($store->store_name ?? 'our store'),
			'seo_image' => $this->theme_engine->logoUrl() ?: base_url('uploads/site/icon.webp'),
			'seo_canonical' => $canonical,
			'seo_type' => 'website',
		];
		$this->theme_engine->view('vehicles', $data);
	}

	/**
	 * Single vehicle page
	 * URL: /store/{store_slug}/vehicle/{id}
	 */
	public function vehicle($storeSlug = '', $vehicleId = 0){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$this->load->model('Automobile_model', 'automobile_m');
		$vehicle = $this->automobile_m->get_by_id($vehicleId);
		if(!$vehicle || $vehicle->store_id != $storeId || $vehicle->status !== 'available'){
			show_404();
			return;
		}

		$canonical = base_url('store/' . $settings->store_slug . '/vehicle/' . $vehicle->id);
		$data = [
			'settings' => $settings,
			'store' => $store,
			'vehicle' => $vehicle,
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
			'store_currency' => $this->theme_engine->getStoreCurrency(),
			'whatsapp_number' => preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''),
			'seo_title' => ($vehicle->year ? $vehicle->year . ' ' : '') . $vehicle->make . ' ' . $vehicle->model,
			'seo_description' => strip_tags($vehicle->description ?? ''),
			'seo_image' => !empty($vehicle->image_path) && file_exists($vehicle->image_path) ? base_url($vehicle->image_path) : ($this->theme_engine->logoUrl() ?: base_url('uploads/site/icon.webp')),
			'seo_canonical' => $canonical,
			'seo_type' => 'product',
		];
		$this->theme_engine->view('vehicle', $data);
	}
}
