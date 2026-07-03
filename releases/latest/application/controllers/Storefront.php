<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Storefront Controller - Public-facing online store
 * No login required. Customers browse and order.
 */
class Storefront extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->helper(['url','custom','currency']);
		$this->load->model('storefront_model');
		$this->load->model('paystack_model','paystack');
		$this->load->library('theme_engine');
	}

	/**
	 * Main storefront page
	 * URL: /store/{store_slug}
	 */
	public function index($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = ($settings->preview_mode && $settings->preview_theme_id) ? $settings->preview_theme_id : null;
		$this->theme_engine->init($storeId, $previewTheme);

		$canonical = base_url('store/' . $settings->store_slug);
		$data = [
			'settings' => $settings,
			'store' => $store,
			'categories' => $this->storefront_model->getCategoriesWithItems($storeId),
			'featured_products' => $this->storefront_model->getOnlineProducts($storeId, null, '', $settings->featured_products_limit),
			'featured_services' => $settings->allow_services ? $this->storefront_model->getOnlineServices($storeId, null, '', 8) : [],
			'best_sellers' => $this->storefront_model->getBestSellers($storeId, 8),
			'new_arrivals' => $this->storefront_model->getNewArrivals($storeId, 8),
			'paystack_enabled' => $this->paystack->is_enabled(),
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

		// Get Paystack public key if enabled
		if($data['paystack_enabled']){
			$ps = $this->paystack->get_settings();
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
		$previewTheme = ($settings->preview_mode && $settings->preview_theme_id) ? $settings->preview_theme_id : null;
		$this->theme_engine->init($storeId, $previewTheme);

		$categoryId = (int)$this->input->get('category');
		$search = trim($this->input->get('search'));
		$page = max(1, (int)$this->input->get('page'));
		$limit = 24;
		$offset = ($page - 1) * $limit;

		$total = $this->storefront_model->countOnlineProducts($storeId, $categoryId, $search);
		$products = $this->storefront_model->getOnlineProducts($storeId, $categoryId, $search, $limit, $offset);

		foreach($products as &$p){
			$p->effective_price = $this->storefront_model->getProductEffectivePrice($p);
			$p->original_price = $p->sales_price;
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
			'store' => get_store_details($storeId),
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
		$previewTheme = ($settings->preview_mode && $settings->preview_theme_id) ? $settings->preview_theme_id : null;
		$this->theme_engine->init($storeId, $previewTheme);

		$search = trim($this->input->get('search'));
		$page = max(1, (int)$this->input->get('page'));
		$limit = 24;
		$offset = ($page - 1) * $limit;

		$total = $this->storefront_model->countOnlineServices($storeId, null, $search);
		$services = $this->storefront_model->getOnlineServices($storeId, null, $search, $limit, $offset);

		foreach($services as &$s){
			$s->effective_price = $this->storefront_model->getServiceEffectivePrice($s);
		}

		$canonical = base_url('store/' . $settings->store_slug . '/services');
		if($search) $canonical .= '?search=' . urlencode($search);
		$data = [
			'settings' => $settings,
			'store' => get_store_details($storeId),
			'services' => $services,
			'categories' => $this->storefront_model->getCategoriesWithItems($storeId),
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
		$previewTheme = ($settings->preview_mode && $settings->preview_theme_id) ? $settings->preview_theme_id : null;
		$this->theme_engine->init($storeId, $previewTheme);

		$product = $this->storefront_model->getOnlineProduct($productId, $storeId);
		if(!$product){
			show_404();
			return;
		}
		$product->effective_price = $this->storefront_model->getProductEffectivePrice($product);
		$product->original_price = $product->sales_price;

		$productImage = $product->item_image && file_exists($product->item_image) ? base_url($product->item_image) : ($this->theme_engine->logoUrl() ?: base_url('uploads/site/icon.webp'));
		$data = [
			'settings' => $settings,
			'store' => get_store_details($storeId),
			'product' => $product,
			'related_products' => $this->storefront_model->getOnlineProducts($storeId, $product->category_id, '', 4),
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
		$this->theme_engine->view('product_detail', $data);
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
		$previewTheme = ($settings->preview_mode && $settings->preview_theme_id) ? $settings->preview_theme_id : null;
		$this->theme_engine->init($storeId, $previewTheme);

		$service = $this->storefront_model->getOnlineService($serviceId, $storeId);
		if(!$service){
			show_404();
			return;
		}
		$service->effective_price = $this->storefront_model->getServiceEffectivePrice($service);

		$serviceImage = $service->item_image && file_exists($service->item_image) ? base_url($service->item_image) : ($this->theme_engine->logoUrl() ?: base_url('uploads/site/icon.webp'));
		$data = [
			'settings' => $settings,
			'store' => get_store_details($storeId),
			'service' => $service,
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
	 * Cart page
	 * URL: /store/{store_slug}/cart
	 */
	public function cart($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$previewTheme = ($settings->preview_mode && $settings->preview_theme_id) ? $settings->preview_theme_id : null;
		$this->theme_engine->init($storeId, $previewTheme);

		$data = [
			'settings' => $settings,
			'store' => get_store_details($storeId),
			'categories' => $this->storefront_model->getCategoriesWithItems($storeId),
			'paystack_enabled' => $this->paystack->is_enabled(),
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
			'social_links' => $this->theme_engine->socialLinks(),
			'store_currency' => $this->theme_engine->getStoreCurrency(),
			'seo_title' => 'Shopping Cart',
			'seo_description' => 'Your cart at ' . ($store->store_name ?? 'our store'),
			'seo_canonical' => base_url('store/' . $settings->store_slug . '/cart'),
			'seo_type' => 'website',
			'csrf_name' => $this->security->get_csrf_token_name(),
			'csrf_hash' => $this->security->get_csrf_hash(),
		];
		if($data['paystack_enabled']){
			$ps = $this->paystack->get_settings();
			if($ps){
				$data['paystack_public_key'] = $ps->public_key ?? '';
				$data['paystack_test_mode'] = $ps->test_mode ?? 1;
			}
		}
		$this->theme_engine->view('cart', $data);
	}

	/**
	 * Dynamic XML Sitemap for storefront
	 * URL: /sitemap.xml
	 */
	public function sitemap(){
		$storeSlug = $this->input->get('store');
		if(!$storeSlug){ show_404(); return; }
		$settings = $this->storefront_model->getStoreBySlug($storeSlug);
		if(!$settings || $settings->store_status != 'active'){ show_404(); return; }
		$storeId = $settings->store_id;
		$base = base_url('store/' . $storeSlug);

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
		$hasServices = false;
		$itemsToInsert = [];

		foreach($cart as $item){
			$type = $item['type'];
			$id = (int)$item['id'];
			$qty = max(1, (int)($item['qty'] ?? 1));

			if($type == 'product'){
				$product = $this->storefront_model->getOnlineProduct($id, $storeId);
				if(!$product) continue;
				if($product->stock < $qty && !$settings->allow_backorder){
					echo json_encode(['status' => false, 'message' => $product->item_name . ' is out of stock', 'csrf_hash' => $this->security->get_csrf_hash()]);
					return;
				}
				$price = $this->storefront_model->getProductEffectivePrice($product);
				$hasProducts = true;
				$itemsToInsert[] = [
					'item_type' => 'product',
					'item_id' => $id,
					'item_name' => $product->item_name,
					'item_image' => $product->item_image,
					'qty' => $qty,
					'unit_price' => $price,
					'total_price' => $price * $qty,
					'service_note' => ''
				];
				$subtotal += $price * $qty;
			} else if($type == 'service'){
				$service = $this->storefront_model->getOnlineService($id, $storeId);
				if(!$service) continue;
				$price = $this->storefront_model->getServiceEffectivePrice($service);
				$hasServices = true;
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
				$subtotal += $price * $qty;
			}
		}

		if(empty($itemsToInsert)){
			echo json_encode(['status' => false, 'message' => 'No valid items in cart', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}

		$orderType = ($hasProducts && $hasServices) ? 'mixed' : ($hasServices ? 'service' : 'product');
		$grandTotal = $subtotal;

		$orderData = [
			'store_id' => $storeId,
			'customer_name' => $customerName,
			'customer_email' => $customerEmail,
			'customer_phone' => $customerPhone,
			'customer_address' => $customerAddress,
			'order_type' => $orderType,
			'payment_method' => $paymentMethod,
			'subtotal' => $subtotal,
			'grand_total' => $grandTotal,
			'service_date' => $serviceDate ?: null,
			'service_time' => $serviceTime ?: null,
			'service_note' => $serviceNote ?: null,
			'ip_address' => $this->input->ip_address(),
			'user_agent' => $this->input->user_agent()
		];

		// Set initial status based on payment method
		if($paymentMethod == 'paystack'){
			$orderData['order_status'] = 'pending';
			$orderData['payment_status'] = 'unpaid';
		} else if($paymentMethod == 'whatsapp'){
			$orderData['order_status'] = 'pending';
			$orderData['payment_status'] = 'unpaid';
			$orderData['whatsapp_sent'] = 1;
		} else {
			$orderData['order_status'] = 'pending';
			$orderData['payment_status'] = 'unpaid';
		}

		$orderId = $this->storefront_model->createOrder($orderData);

		foreach($itemsToInsert as $item){
			$item['order_id'] = $orderId;
			$this->storefront_model->addOrderItem($item);
		}

		$order = $this->storefront_model->getOrder($orderId);

		// If Paystack, return payment initialization data
		if($paymentMethod == 'paystack' && $settings->allow_paystack){
			$ps = $this->paystack->get_settings();
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
			'message' => 'Order placed successfully!',
			'csrf_hash' => $this->security->get_csrf_hash(),
		]);
	}

	/**
	 * Paystack callback for online orders
	 */
	public function paystack_callback(){
		$reference = $this->input->get('reference') ?: $this->input->post('reference');
		if(!$reference){
			show_error('No reference provided', 400);
			return;
		}

		$this->load->model('paystack_model', 'paystack');
		$verify = $this->paystack->verify_transaction($reference);

		if($verify['status'] && $verify['payment_status'] == 'success'){
			$order = $this->storefront_model->getOrderByReference($reference);
			if($order){
				$this->storefront_model->updatePaymentStatus($order->id, 'paid', [
					'paystack_reference' => $reference,
					'paystack_amount' => $verify['amount'] / 100,
					'order_status' => 'paid'
				]);
			}
			$data = ['success' => true, 'message' => 'Payment successful!', 'reference' => $reference];
		} else {
			$order = $this->storefront_model->getOrderByReference($reference);
			if($order){
				$this->storefront_model->updatePaymentStatus($order->id, 'failed');
			}
			$data = ['success' => false, 'message' => 'Payment was not successful. Please try again.', 'reference' => $reference];
		}
		$this->load->view('storefront/paystack_callback', $data);
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

	// ============== HELPERS ==============

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
		if(!$settings){
			$storeId = (int)$this->input->get('store_id');
			if($storeId){
				$settings = $this->storefront_model->getSettings($storeId);
			}
		}
		if(!$settings){
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
}
