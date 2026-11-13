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
		$this->load->library('storefront_currency');
		$this->load->library('html_theme_manager');

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

	/** Change the visitor's store-scoped display/checkout currency selection. */
	public function select_currency(){
		$slug=trim((string)$this->input->post('store_slug',TRUE));
		$settings=$this->storefront_model->getStoreBySlug($slug);
		if(!$settings){$this->output->set_status_header(404);$out=['status'=>false,'message'=>'Store not found.'];}
		else{
			$storeId=(int)$settings->store_id;$country=strtoupper(trim((string)$this->input->post('country',TRUE)));
			if($country!==''&&!$this->storefront_currency->setCountry($storeId,$country)){$this->output->set_status_header(422);$out=['status'=>false,'message'=>'Choose a valid country.'];}
			else{
				$code=strtoupper(trim((string)$this->input->post('currency',TRUE)));
				if($code===''&&$country!=='')$code=$this->storefront_currency->countryDefault($storeId,$country);
				if(!$this->storefront_currency->setCurrency($storeId,$code)){$this->output->set_status_header(422);$out=['status'=>false,'message'=>'That currency is unavailable or its exchange rate is not configured.'];}
				else $out=['status'=>true,'currency'=>$this->storefront_currency->selectedCode($storeId),'country'=>$this->storefront_currency->selectedCountry($storeId),
					'rate'=>$this->storefront_currency->rate($storeId,$this->storefront_currency->selectedCode($storeId)),'csrf_hash'=>$this->security->get_csrf_hash()];
			}
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($out));
	}

	/**
	 * Main storefront page
	 * URL: /store/{store_slug}
	 */
	public function index($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$profile = function_exists('mp_get_store_profile') ? mp_get_store_profile($storeId) : [];
		if(!empty($settings->partner_marketplace_enabled) && in_array(strtolower((string)($profile['industry_type'] ?? '')), ['pharmacy', 'medical_store', 'healthcare'], true)){
			// The pharmacy experience is a separate presentation, but resolves the
			// same store slug and MartPoint data. The flag keeps every other store
			// on its existing storefront route.
			redirect(base_url('store/'.rawurlencode((string)$settings->store_slug).'/pharmacy'));
			return;
		}
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);
		$isLumiere = in_array(($this->theme_engine->currentTheme()->theme_key ?? ''), ['lumiere', 'skinora'], true);

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
			$p->original_price = $this->storefront_model->getProductOriginalPrice($p);
			$p->is_on_sale = $p->original_price > $p->effective_price;
			$p->sold_count = $soldCounts[$p->id] ?? 0;
		}
		$data = [
			'settings' => $settings,
			// Themes may opt into controlled partner offers; the setting is off by default.
			'partner_marketplace_enabled' => !empty($settings->partner_marketplace_enabled),
			'store' => $store,
			'categories' => $this->storefront_model->getCategoriesWithItems($storeId),
			'outlets' => $this->_publicOrderableOutlets($storeId),
			'featured_categories' => $this->storefront_model->getCategoriesWithItems($storeId),
			'all_products' => $allProducts,
			'featured_products' => $this->storefront_model->getFeaturedProducts($storeId, $settings->featured_products_limit, $isLumiere),
			'featured_vehicles' => $featuredVehicles,
			'featured_services' => $settings->allow_services ? $this->storefront_model->getOnlineServices($storeId, null, '', 8) : [],
			'best_sellers' => $this->storefront_model->getBestSellers($storeId, 8),
			'new_arrivals' => $this->storefront_model->getNewArrivals($storeId, 10, !$isLumiere, $isLumiere),
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
			foreach($data[$key] as &$p){
				$p->effective_price = $this->storefront_model->getProductEffectivePrice($p);
				$p->original_price = $this->storefront_model->getProductOriginalPrice($p);
				$p->is_on_sale = $p->original_price > $p->effective_price;
				$p->sold_count = $soldCounts[$p->id] ?? 0;
			}
			unset($p);
			$this->storefront_model->attachUnitNames($data[$key], $storeId);
		}
		$this->storefront_model->attachUnitNames($data['all_products'], $storeId);
		foreach($data['featured_services'] as &$service){
			$service->effective_price = $this->storefront_model->getServiceEffectivePrice($service);
		}
		unset($service);

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

	/** Serve only non-executable uploaded theme assets from private storage. */
	public function theme_asset($storeId = 0, $themeId = 0, $relative = ''){
		// CI3 passes each remaining URI segment as a separate method argument.
		// Rejoin the relative path so nested assets such as assets/theme.css
		// resolve as a single, store-confined path in Html_theme_manager.
		$segments = func_get_args();
		$relative = implode('/', array_slice($segments, 2));
		$theme = $this->html_theme_manager->getTheme((int)$themeId, (int)$storeId);
		$path = $this->html_theme_manager->assetPath($theme, rawurldecode((string)$relative));
		$ext = strtolower(pathinfo((string)$relative, PATHINFO_EXTENSION));
		$types = ['css'=>'text/css','js'=>'application/javascript','png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','webp'=>'image/webp','gif'=>'image/gif','svg'=>'image/svg+xml','ico'=>'image/x-icon','woff'=>'font/woff','woff2'=>'font/woff2','ttf'=>'font/ttf','otf'=>'font/otf'];
		if(!$path || !isset($types[$ext])){ show_404(); return; }
		$this->output->set_content_type($types[$ext])->set_header('Cache-Control: public, max-age=3600')->set_output(file_get_contents($path));
	}

	/**
	 * Product listing with optional category filter
	 * URL: /store/{store_slug}/products?category={id}&search={term}
	 */
    public function privacy($storeSlug=''){
        $settings=$this->_getSettingsOr404($storeSlug);$sid=(int)$settings->store_id;$this->theme_engine->init($sid,$this->_previewThemeForVisitor($settings));$this->load->model('storefront_content_model');$config=$this->storefront_content_model->settings($sid);
        $this->theme_engine->view('privacy',['seo_title'=>$config['privacy_heading'],'site_content'=>$config,'store_currency'=>$this->theme_engine->getStoreCurrency(),'social_links'=>$this->theme_engine->socialLinks()]);
    }
    public function contact($storeSlug = ''){
        $settings = $this->_getSettingsOr404($storeSlug);
        $sid = (int)$settings->store_id;
        $this->theme_engine->init($sid, $this->_previewThemeForVisitor($settings));
        $this->load->model('storefront_content_model');
        $config = $this->storefront_content_model->settings($sid);
        if(empty($config['contact_enabled'])){
            show_404();
            return;
        }

        $canForm = mp_feature_enabled_for_store('leads', $sid);
        $error = null;
        $received = false;

        if($this->input->method() === 'post'){
            if(!$canForm){
                show_error('Contact form unavailable', 403);
                return;
            }
            if($this->input->post('website')){
                // Honeypot: acknowledge without writing a lead or sending mail.
                $received = true;
            } else {
                try{
                    $files = (isset($_FILES['documents']) && is_array($_FILES['documents'])) ? $_FILES['documents'] : [];
                    $this->storefront_content_model->submit_contact($sid, (array)$this->input->post(), $files);
                    $received = true;
                } catch(InvalidArgumentException $e){
                    $error = $e->getMessage();
                } catch(Throwable $e){
                    log_message('error', 'Store contact: ' . $e->getMessage());
                    $error = 'Could not send your message. Please try again.';
                }
            }
        }

        $productId = (int)($this->input->post('product_id') ?: $this->input->get('product_id'));
        $product = $productId ? $this->storefront_model->getOnlineProduct($productId, $sid) : null;
        $serviceId = (int)($this->input->post('service_id') ?: $this->input->get('service_id'));
        $service = $serviceId ? $this->storefront_model->getOnlineService($serviceId, $sid) : null;

        $contactSubject = trim((string)$this->input->get('subject'));
        if($contactSubject === '' && $service){
            $contactSubject = (string)($service->service_name ?? $service->item_name ?? '');
        }

        $this->theme_engine->view('contact', [
            'seo_title' => $config['contact_heading'],
            'site_content' => $config,
            'contact_can_submit' => $canForm,
            'contact_error' => $error,
            'contact_received' => $received,
            'contact_product' => $product,
            'contact_service' => $service,
            'contact_subject' => $contactSubject,
            'contact_key' => 'contact_' . bin2hex(random_bytes(20)),
            'categories' => $this->storefront_model->getCategoriesWithItems($sid),
            'business_hours' => $this->theme_engine->businessHours(),
            'public_branches' => $this->theme_engine->branches(),
            'store_currency' => $this->theme_engine->getStoreCurrency(),
            'social_links' => $this->theme_engine->socialLinks(),
        ]);
    }

    /**
     * Editorial journal. The public listing shows insights only; project and
     * application entries have their own landing pages and still resolve at
     * their /blog/{slug} address for the "read the full entry" link.
     */
    public function blog($storeSlug = '', $slug = ''){
        $settings = $this->_getSettingsOr404($storeSlug);
        $sid = (int)$settings->store_id;
        $this->theme_engine->init($sid, $this->_previewThemeForVisitor($settings));
        $this->load->model('storefront_content_model');
        $config = $this->storefront_content_model->settings($sid);
        if(empty($config['blog_enabled'])){
            show_404();
            return;
        }
        $page = max(1, (int)$this->input->get('page'));
        $post = $slug ? $this->storefront_content_model->post($sid, $slug, true) : null;
        if($slug && !$post){
            show_404();
            return;
        }

        $this->theme_engine->view('blog', [
            'seo_title' => $post ? $post->title : $config['blog_label'],
            'seo_description' => $post ? $post->excerpt : $config['blog_intro'],
            'site_content' => $config,
            'journal_post' => $post,
            'journal_posts' => $post ? [] : $this->storefront_content_model->posts($sid, true, $page, 'insight'),
            'journal_page' => $page,
            'store_currency' => $this->theme_engine->getStoreCurrency(),
            'social_links' => $this->theme_engine->socialLinks(),
        ]);
    }

    /**
     * Applications landing page — journal entries with kind "application".
     * URL: /store/{store_slug}/applications
     */
    public function applications($storeSlug = ''){
        $settings = $this->_getSettingsOr404($storeSlug);
        $sid = (int)$settings->store_id;
        $this->theme_engine->init($sid, $this->_previewThemeForVisitor($settings));
        if(!$this->theme_engine->isBuildingMaterialsTheme()){
            show_404();
            return;
        }
        $this->load->model('storefront_content_model');
        $applications = $this->storefront_content_model->entries($sid, 'application', 24);
        $store = get_store_details($sid);

        $this->theme_engine->view('applications', [
            'seo_title' => 'Applications',
            'seo_description' => 'Applications and environments for the systems range at ' . ($store->store_name ?? 'our store') . '.',
            'seo_canonical' => base_url('store/' . $settings->store_slug . '/applications'),
            'seo_type' => 'website',
            'applications' => $applications,
            'store_currency' => $this->theme_engine->getStoreCurrency(),
            'social_links' => $this->theme_engine->socialLinks(),
        ]);
    }

    /**
     * Projects landing page — journal entries with kind "project".
     * URL: /store/{store_slug}/projects
     */
    public function projects($storeSlug = ''){
        $settings = $this->_getSettingsOr404($storeSlug);
        $sid = (int)$settings->store_id;
        $this->theme_engine->init($sid, $this->_previewThemeForVisitor($settings));
        if(!$this->theme_engine->isBuildingMaterialsTheme()){
            show_404();
            return;
        }
        $this->load->model('storefront_content_model');
        $projects = $this->storefront_content_model->entries($sid, 'project', 24);
        $store = get_store_details($sid);

        $this->theme_engine->view('projects', [
            'seo_title' => 'Projects',
            'seo_description' => 'Selected projects delivered with the systems range at ' . ($store->store_name ?? 'our store') . '.',
            'seo_canonical' => base_url('store/' . $settings->store_slug . '/projects'),
            'seo_type' => 'website',
            'projects' => $projects,
            'store_currency' => $this->theme_engine->getStoreCurrency(),
            'social_links' => $this->theme_engine->socialLinks(),
        ]);
    }

    /**
     * Public technical document download.
     * URL: /store/{store_slug}/document/{product_id}/{document_id}
     *
     * Only rows explicitly marked public are reachable. The resolved path is
     * confined to uploads/ and the download is served with a neutral filename.
     */
    public function document($storeSlug = '', $productId = 0, $documentId = 0){
        $settings = $this->_getSettingsOr404($storeSlug);
        $sid = (int)$settings->store_id;
        $this->theme_engine->init($sid, $this->_previewThemeForVisitor($settings));
        if(!$this->theme_engine->isBuildingMaterialsTheme()){
            show_404();
            return;
        }
        $this->load->model('storefront_content_model');
        // Unpublishing a product must revoke its public documents immediately.
        $product = $this->storefront_model->getOnlineProduct((int)$productId, $sid);
        if(!$product){
            show_404();
            return;
        }
        $document = $this->storefront_content_model->document($sid, (int)$productId, (int)$documentId, true);
        if(!$document || trim((string)$document->file_path) === ''){
            show_404();
            return;
        }

        $root = realpath(mp_private_storage_root(false));
        $path = realpath(mp_private_storage_path((string)$document->file_path, true));
        if(!$root || !$path || strpos($path, $root . DIRECTORY_SEPARATOR) !== 0 || !is_file($path)){
            show_404();
            return;
        }

        $this->load->helper('download');
        $name = trim((string)$document->file_name) !== '' ? basename((string)$document->file_name) : basename($path);
        force_download($name, file_get_contents($path));
    }

	public function products($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);

		$categoryId = (int)$this->input->get('category');
		$search = trim($this->input->get('search'));
		$page = max(1, (int)$this->input->get('page'));
		$sort = (string)$this->input->get('sort');
		if(!in_array($sort, ['price_asc','price_desc','name_asc','name_desc','newest'], true)) $sort = '';
		$limit = 24;
		$offset = ($page - 1) * $limit;

		$total = $this->storefront_model->countOnlineProducts($storeId, $categoryId, $search);
		$themeKey = (string)($this->theme_engine->currentTheme()->theme_key ?? '');
		if(in_array($themeKey, ['botanical_elegance','market_fresh'], true)){
			// A page number past the last one would otherwise render an empty grid
			// with a pager that still offers that page.
			$page = min($page, max(1, (int)ceil($total / $limit)));
			$offset = ($page - 1) * $limit;
		}
		$products = $this->storefront_model->getOnlineProducts($storeId, $categoryId, $search, $limit, $offset, $sort);
		$soldCounts = $this->storefront_model->getProductSoldCounts($storeId);

		foreach($products as &$p){
			$p->effective_price = $this->storefront_model->getProductEffectivePrice($p);
			$p->original_price = $this->storefront_model->getProductOriginalPrice($p);
			$p->is_on_sale = $p->original_price > $p->effective_price;
			$p->sold_count = $soldCounts[$p->id] ?? 0;
		}
		unset($p);
		$this->storefront_model->attachUnitNames($products, $storeId);

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
			'outlets' => $this->_publicOrderableOutlets($storeId),
			'category_id' => $categoryId,
			'search' => $search,
			'sort' => $sort,
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
		if (($this->theme_engine->currentTheme()->theme_key ?? '') === 'skinora' && !$this->theme_engine->sellsServices()) {
			show_404();
			return;
		}

		$search = trim($this->input->get('search'));
		$categoryId = (int)$this->input->get('category');
		$page = max(1, (int)$this->input->get('page'));
		$limit = 24;
		$offset = ($page - 1) * $limit;

		$total = $this->storefront_model->countOnlineServices($storeId, $categoryId, $search);
		if(($this->theme_engine->currentTheme()->theme_key ?? '')==='botanical_elegance'){
			$page = min($page, max(1, (int)ceil($total / $limit)));
			$offset = ($page - 1) * $limit;
		}
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
			'outlets' => $this->_publicOrderableOutlets($storeId),
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
		$product->original_price = $this->storefront_model->getProductOriginalPrice($product);
		$product->is_on_sale = $product->original_price > $product->effective_price;
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
				$v->original_price = $this->storefront_model->getProductOriginalPrice($v);
				$v->is_on_sale = $v->original_price > $v->effective_price;
				$v->is_current = ((int)$v->id === (int)$product->id);
				$product_variants[] = $v;
			}
		}

		$productOutlets = $this->_publicOrderableOutlets($storeId);
		$isFoodTheme = ($this->theme_engine->currentTheme()->theme_key ?? '') === 'food_express';
		$foodPairings = $isFoodTheme ? $this->storefront_model->getRestaurantPairings($storeId, $product, $productOutlets, !empty($settings->allow_backorder)) : [];
		$relatedProducts = $isFoodTheme ? [] : $this->storefront_model->getOnlineProducts($storeId, $product->category_id, '', 4);
		foreach($foodPairings as &$kitchenPairings){
			foreach($kitchenPairings as &$pairing){
				$pairing->effective_price = $this->storefront_model->getProductEffectivePrice($pairing);
			}
			unset($pairing);
		}
		unset($kitchenPairings);
		foreach($relatedProducts as &$rp){
			$rp->effective_price = $this->storefront_model->getProductEffectivePrice($rp);
				$rp->original_price = $this->storefront_model->getProductOriginalPrice($rp);
				$rp->is_on_sale = $rp->original_price > $rp->effective_price;
			$rp->sold_count = $soldCounts[$rp->id] ?? 0;
		}
		unset($rp);
		$this->storefront_model->attachUnitNames(array_merge([$product], $product_variants, $relatedProducts), $storeId);
		$seoCurrency=$this->storefront_currency->selectedCode($storeId);
		$seoRate=$this->storefront_currency->rate($storeId,$seoCurrency);

		$data = [
			'settings' => $settings,
			'store' => $store,
			'product' => $product,
			'product_addons' => $productAddons,
			'product_variants' => $product_variants,
			'related_products' => $relatedProducts,
			'food_pairings' => $foodPairings,
			'categories' => $this->storefront_model->getCategoriesWithItems($storeId),
			'outlets' => $productOutlets,
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
					'priceCurrency' => $seoCurrency,
					'price' => number_format($this->storefront_currency->convertBaseToTransaction($product->effective_price,$seoRate), 2, '.', ''),
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
		if (($this->theme_engine->currentTheme()->theme_key ?? '') === 'skinora' && !$this->theme_engine->sellsServices()) {
			show_404();
			return;
		}

		$service = $this->storefront_model->getOnlineService($serviceId, $storeId);
		if(!$service){
			show_404();
			return;
		}
		$service->effective_price = $this->storefront_model->getServiceEffectivePrice($service);
		$this->load->model('Extras_model', 'extras_model');
		$serviceAddons = $this->extras_model->getAddonsForService($serviceId, $storeId);

		$serviceImagePath = $service->service_image ?? $service->item_image ?? '';
		$serviceImage = $serviceImagePath && file_exists($serviceImagePath) ? base_url($serviceImagePath) : ($this->theme_engine->logoUrl() ?: base_url('uploads/site/icon.webp'));
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
			'seo_title' => $service->service_name ?? $service->item_name ?? 'Service',
			'seo_description' => substr(strip_tags($service->description ?? ''), 0, 160) ?: ('Book ' . ($service->service_name ?? $service->item_name ?? 'a service') . ' at ' . ($store->store_name ?? 'our store')),
			'seo_image' => $serviceImage,
			'seo_canonical' => base_url('store/' . $settings->store_slug . '/service/' . $serviceId),
			'seo_type' => 'product',
		];
		$this->theme_engine->view('service_detail', $data);
	}

	/** A treatment request is its own journey, separate from product checkout. */
	public function appointment($storeSlug = '', $serviceId = 0){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = (int)$settings->store_id;
		$this->theme_engine->init($storeId, $this->_previewThemeForVisitor($settings));
		$key = $this->theme_engine->currentTheme()->theme_key ?? '';
		if (!in_array($key, ['lumiere', 'skinora'], true) || empty($settings->allow_services) || !$this->theme_engine->sellsServices()) {
			show_404();
			return;
		}
		$service = $this->storefront_model->getOnlineService((int)$serviceId, $storeId);
		if (!$service) {
			show_404();
			return;
		}
		$service->effective_price = $this->storefront_model->getServiceEffectivePrice($service);
		$store = get_store_details($storeId);
		$this->theme_engine->view('appointment', [
			'settings' => $settings,
			'store' => $store,
			'service' => $service,
			'store_currency' => $this->theme_engine->getStoreCurrency(),
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
			'social_links' => $this->theme_engine->socialLinks(),
			'show_appointment' => true,
			'seo_title' => 'Request ' . $service->service_name . ' Appointment',
			'seo_description' => 'Request a time for ' . $service->service_name . ' at ' . ($store->store_name ?? 'our studio') . '.',
			'seo_canonical' => base_url('store/' . $settings->store_slug . '/appointment/' . (int)$serviceId),
			'csrf_name' => $this->security->get_csrf_token_name(),
			'csrf_hash' => $this->security->get_csrf_hash(),
		]);
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
				$p->original_price = $this->storefront_model->getProductOriginalPrice($p);
				$p->is_on_sale = $p->original_price > $p->effective_price;
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
	public function cart($storeSlug = '', $page = 'cart'){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = $settings->store_id;
		$store = get_store_details($storeId);
		$previewTheme = $this->_previewThemeForVisitor($settings);
		$this->theme_engine->init($storeId, $previewTheme);
		if (!$this->theme_engine->cartEnabled()) {
			$key = $this->theme_engine->currentTheme()->theme_key ?? '';
			$printTheme = in_array($key, ['print_works', 'print_inkpress', 'print_papercraft', 'print_neonprint'], true);
			$buildingTheme = in_array($key, ['framework_atelier', 'vector_systems'], true);
			// A catalogue-mode building store sells through the enquiry desk,
			// so send a stray cart/checkout hit back to the catalogue it came from.
			$target = $buildingTheme
				? ($this->theme_engine->sellsProducts() ? '/products' : '/services')
				: ($printTheme ? '#quote' : '/services');
			redirect(base_url('store/' . rawurlencode($settings->store_slug) . $target));
			return;
		}
		$lumiereCheckout = $page === 'checkout' && in_array(($this->theme_engine->currentTheme()->theme_key ?? ''), ['lumiere', 'skinora', 'botanical_elegance', 'food_express'], true);
		$mediqCheckout = $page === 'checkout' && ($this->theme_engine->currentTheme()->theme_key ?? '') === 'pharma_mediq';
		$htmlThemeCheckout = $page === 'checkout' && (($this->theme_engine->currentTheme()->theme_source ?? '') === 'html_zip');
		$storeCurrencyCode=$this->storefront_currency->selectedCode($storeId);
		$baseCurrencyCode=$this->storefront_currency->baseCode($storeId);
		$currencyGatewayMap=json_decode((string)($settings->currency_gateway_map_json??''),true)?:[];
		if($baseCurrencyCode!=='NGN'&&!isset($currencyGatewayMap[$baseCurrencyCode]))$currencyGatewayMap[$baseCurrencyCode]='paypal';
		$this->load->library('storefront_paypal');
		$paypalState=$this->storefront_paypal->accountSummary($storeId);
		$paypalCurrencyAllowed=($currencyGatewayMap[$storeCurrencyCode]??'')==='paypal'
			&&($storeCurrencyCode===$baseCurrencyCode ? $baseCurrencyCode!=='NGN' : !empty($settings->multicurrency_enabled));
		$paypalEnabled=$paypalCurrencyAllowed&&!empty($paypalState['enabled'])&&!empty($paypalState['webhook_configured']);

		$data = [
			'settings' => $settings,
			'store' => $store,
			'categories' => $this->storefront_model->getCategoriesWithItems($storeId),
			'paystack_enabled' => $this->paystack->is_enabled($storeId) && $storeCurrencyCode==='NGN' && $baseCurrencyCode==='NGN',
			'paypal_enabled' => $paypalEnabled,
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
			'social_links' => $this->theme_engine->socialLinks(),
			'store_currency' => $this->theme_engine->getStoreCurrency(),
			'table_number' => $this->table_number,
			'outlets' => $this->_publicOrderableOutlets($storeId),
			'cart_page' => $page,
			'seo_title' => ($lumiereCheckout || $mediqCheckout || $htmlThemeCheckout) ? 'Secure Checkout' : 'Shopping Cart',
			'seo_description' => (($lumiereCheckout || $mediqCheckout || $htmlThemeCheckout) ? 'Complete your order securely at ' : 'Your cart at ') . ($store->store_name ?? 'our store'),
			'seo_canonical' => base_url('store/' . $settings->store_slug . (($lumiereCheckout || $mediqCheckout || $htmlThemeCheckout) ? '/checkout' : '/cart')),
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
		$this->theme_engine->view(($lumiereCheckout || $mediqCheckout || $htmlThemeCheckout) ? 'checkout' : 'cart', $data);
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
			'seo_title' => 'Choose your kitchen | ' . ($store->store_name ?? 'our restaurant'),
			'seo_description' => 'Choose a restaurant location for the right menu, pickup options and delivery coverage.',
			'seo_canonical' => base_url('store/' . $settings->store_slug . '/branches'),
			'seo_type' => 'website',
		];
		$this->theme_engine->view('branches', $data);
	}

	/**
	 * Resolve locations that the current public storefront is allowed to
	 * expose. Food Express gets the restaurant boundary; pharmacy and other
	 * industries retain their own fulfilment rules.
	 */
	private function _publicOrderableOutlets($storeId){
		$profile = function_exists('mp_get_store_profile') ? mp_get_store_profile($storeId) : [];
		$industry = strtolower(trim((string)($profile['industry_type'] ?? '')));
		$foodIndustries = ['restaurant','fast_food','food_delivery','cafe','bakery','pizza_shop','shawarma','juice_bar','buka','canteen'];
		$context = in_array($industry, $foodIndustries, true) ? 'restaurant' : '';
		return $this->storefront_model->getOrderableOutlets($storeId, $context);
	}

	private function _trackStatusMap($industryType = '', $shippingMethod = '', $isCollection = null){
		$industry = strtolower(trim($industryType ?: 'general_retail'));
		$method = strtolower(trim($shippingMethod ?: ''));
		// The order itself decides whether it is being collected — a delivery fee
		// or a 'pickup' fulfilment type says more than the free-text method did.
		$isPickup = $isCollection === null
			? in_array($method, ['pickup', 'pick up', 'self pick up', 'self pickup', 'collect', 'collection'])
			: (bool)$isCollection;

		$readyLabel = $isPickup ? 'Ready for pickup' : 'Ready for delivery';
		$completedLabel = $isPickup ? 'Picked up' : 'Delivered';
		$readyDesc = $isPickup ? 'Your order is ready for collection.' : 'Your order is packed and ready to go out.';
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
	 * What the customer sees when they look their order up: the shop's own stages
	 * in this business's words, and for a delivered order the leg that carries it
	 * to their door. Both live and dead steps are returned, each marked done, so
	 * the page can show how far the order has got rather than only where it is.
	 * The times come from the order's written history — the same trail the shop
	 * works from — so nothing here is inferred from the current status.
	 *
	 * @return array of ['code','label','desc','when','done']
	 */
	private function _trackTimeline($storeId, $order, $statusMap, $isCollection){
		$stageOrder = ['pending','paid','processing','ready','completed'];
		$stageIndex = array_search((string)($order->order_status ?? 'pending'), $stageOrder);
		if($stageIndex === false) $stageIndex = 0;

		$delivery = trim((string)($order->delivery_status ?? ''));
		// A collection order has no delivery leg to show; its "collected" step is
		// the shop's own Completed stage, which _trackStatusMap already words.
		$showDelivery = $this->_trackHasDeliveryLeg($order, $isCollection);

		$times = [];
		$rider = '';
		foreach($this->storefront_model->orderTrail($storeId, (int)($order->id ?? 0)) as $row){
			$code = (string)($row['code'] ?? '');
			if($code !== '' && !isset($times[$code])) $times[$code] = (string)($row['when'] ?? '');
			if($rider === '' && $code === 'out_for_delivery'
				&& preg_match('/^With\s+(.+?)(?:\s+—|$)/u', (string)($row['reason'] ?? ''), $m)){
				$rider = trim($m[1]);
			}
		}

		$steps = [];
		$stageLimit = $showDelivery ? 4 : 5; // 'completed' becomes the delivery leg
		$rxStep = $this->_trackRxStep($storeId, (int)($order->id ?? 0));
		for($i = 0; $i < $stageLimit; $i++){
			$code = $stageOrder[$i];
			$info = $statusMap[$code] ?? ['label' => ucfirst($code), 'desc' => ''];
			$steps[] = [
				'code' => $code,
				'label' => $info['label'],
				'desc' => $info['desc'],
				'when' => $times[$code] ?? '',
				'done' => $i <= $stageIndex || ($code === 'ready' && $this->_trackHasLeftShop($delivery)),
			];
			if($code === 'paid' && $rxStep !== null) $steps[] = $rxStep;
		}

		if($showDelivery){
			$closedStage = (string)($order->order_status ?? '') === 'completed';
			$failed = $delivery === 'failed';
			$cancelledLeg = $delivery === 'cancelled';
			// A shop that closes the stage without using the delivery leg still
			// delivered the order as far as the customer is concerned.
			$neverRecorded = $delivery === '' && $closedStage;
			$left = $this->_trackHasLeftShop($delivery) || $neverRecorded;
			$arrived = $delivery === 'delivered' || $neverRecorded;
			$steps[] = [
				'code' => 'out_for_delivery',
				'label' => 'Out for delivery',
				'desc' => $cancelledLeg
					? 'This delivery was cancelled. Please contact the shop about it.'
					: ($failed
						? 'The delivery could not be completed. The shop will be in touch about it.'
						: ($left
							? ($rider !== '' ? 'It is on its way to you with ' . $rider . '.' : 'It is on its way to you.')
							: 'Not sent out yet.')),
				'when' => $times['out_for_delivery'] ?? '',
				'done' => $left && !$cancelledLeg,
			];
			$steps[] = [
				'code' => 'delivered',
				'label' => 'Delivered',
				'desc' => $arrived
					? 'It has reached you. Thank you.'
					: ($failed ? 'Awaiting another attempt.' : 'Not with you yet.'),
				'when' => $times['delivered'] ?? '',
				'done' => $arrived,
			];
		}

		// The newest completed step is where the customer actually is.
		$current = 0;
		foreach($steps as $i => $s){
			if($s['done']) $current = $i;
		}
		return ['steps' => $steps, 'current' => $current];
	}

	/** Once a parcel has gone out, the shop's "ready" stage is behind it. */
	private function _trackHasLeftShop($delivery){
		return in_array((string)$delivery, ['out_for_delivery', 'delivered', 'failed'], true);
	}

	/**
	 * Whether this order has a leg to the customer's door at all. A counter sale
	 * with no delivery charge and no recorded send-out has none, and the customer
	 * should not be shown a "not sent out yet" step for something they were always
	 * going to pick up.
	 */
	private function _trackHasDeliveryLeg($order, $isCollection){
		if($isCollection) return false;
		if(trim((string)($order->delivery_status ?? '')) !== '') return true;
		return (float)($order->delivery_fee ?? 0) > 0;
	}

	/**
	 * Whether a pharmacist has cleared the prescription behind an order. That
	 * answer lives on the review record rather than the order's stage, so the
	 * customer is told what the pharmacist actually recorded — including that
	 * nothing has been checked yet, which is not the same as "being prepared".
	 *
	 * @return array|null a step for the customer's timeline
	 */
	private function _trackRxStep($storeId, $orderId){
		if(!$orderId || !$this->db->table_exists('db_medical_notes')) return null;
		if(empty($this->storefront_model->rxItemNames($orderId, (int)$storeId)['ids'])) return null;
		$note = $this->storefront_model->findRxNote($orderId, (int)$storeId);
		$decision = $note ? (string)($note->review_status ?? '') : '';
		$approved = $decision === 'approved';
		$rejected = $decision === 'rejected';
		$who = $note ? trim((string)($note->reviewed_by_name ?? '')) : '';
		return [
			'code' => 'prescription_review',
			'label' => $approved ? 'Prescription approved' : ($rejected ? 'Prescription not approved' : 'Prescription check'),
			'desc' => $approved
				? 'A pharmacist' . ($who !== '' ? ' (' . $who . ')' : '') . ' has checked your prescription.'
				: ($rejected
					? 'The pharmacist could not approve this prescription.'
					: 'A pharmacist is checking your prescription before your medicine can be prepared.'),
			'when' => ($approved && $note) ? (string)($note->reviewed_at ?? '') : '',
			'done' => $approved,
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
		$isCollection = !empty($order) && $this->storefront_model->orderIsCollection($order);
		$hasDeliveryLeg = !empty($order) && $this->_trackHasDeliveryLeg($order, $isCollection);
		$statusMap = $this->_trackStatusMap($store->industry_type ?? '', $shippingMethod,
			!empty($order) ? $isCollection : null);
		if(!empty($order) && !$isCollection && !$hasDeliveryLeg){
			// Nothing about this order says where it goes — no pickup mark, no charge,
			// no recorded send-out. Say neither 'delivered' nor 'collect it'; say the
			// shop has finished with it.
			$statusMap['ready'] = ['label' => 'Ready', 'desc' => 'Your order is ready.'];
			$statusMap['completed'] = ['label' => 'Completed', 'desc' => 'Your order is complete.'];
		}
		$currentStatus = $statusMap[$order->order_status ?? 'pending'] ?? $statusMap['pending'];

		$timeline = ['steps' => [], 'current' => 0];
		$fulfilment = null;
		if(!empty($order) && $order->order_status !== 'cancelled'){
			$timeline = $this->_trackTimeline($storeId, $order, $statusMap, $isCollection);
			// Only say something about where the order goes when the order actually
			// says so: a pickup, or a leg to the door. A plain counter sale gets
			// nothing invented for it.
			if($isCollection || $hasDeliveryLeg){
				$fulfilment = [
					'collection' => $isCollection,
					'address' => trim((string)($order->customer_address ?? '')),
					'fee' => (float)($order->delivery_fee ?? 0),
					'quote_pending' => !empty($order->delivery_quote_pending),
				];
			}
		}

		$testimonialSubmitted = false;
		if(!empty($order->customer_name)){
			$testimonialSubmitted = $this->db->where('store_id', $storeId)->where('customer_name', $order->customer_name)->where('is_enabled', 0)->get('db_storefront_testimonials')->num_rows() > 0;
		}

		// The confirmation page hands the customer /track?code=…; nothing about the
		// order is revealed by that, because the phone number is still the key.
		$prefillCode = trim((string)$this->input->get('code'));

		$data = [
			'settings' => $settings,
			'store' => $store,
			'order' => $order,
			'items' => $items,
			'error' => $error,
			'current_status' => $currentStatus,
			'status_map' => $statusMap,
			'timeline' => $timeline,
			'fulfilment' => $fulfilment,
			'prefill_code' => $prefillCode,
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
		$this->theme_engine->init($storeId);
		if($this->theme_engine->isBuildingMaterialsTheme()){
			$sections = $this->theme_engine->homepageSections();
			$newsletterEnabled = false;
			foreach($sections as $row){
				if(preg_replace('/_\d+$/', '', $row->section_key) === 'newsletter' && !empty($row->is_enabled)) $newsletterEnabled = true;
			}
			if(!$newsletterEnabled || $this->input->post('consent') !== '1'){
				echo json_encode(['status'=>false,'message'=>$newsletterEnabled ? 'Please agree to receive email updates before subscribing.' : 'Newsletter signup is not available.'] + $csrf);
				return;
			}
		}

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
	public function garden_request($storeSlug = '') {
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = (int)$settings->store_id;
		$this->load->helper('garden');
		$profile = mp_get_store_profile($storeId);
		if (($profile['industry_type'] ?? '') !== 'garden_centre_landscaping'
			|| !mp_feature_enabled_for_store('online_store',$storeId)
			|| (!mp_garden_has_mode($profile,'landscaping') && !mp_garden_has_mode($profile,'maintenance'))) { show_404(); return; }
		$this->theme_engine->init($storeId,$this->_previewThemeForVisitor($settings));
		$error = null; $received = false;
		if ($this->input->method() === 'post') {
			if ($this->input->post('website')) $received = true;
			else {
				$this->load->model('garden_model');
				try { $this->garden_model->submit_request($storeId,(array)$this->input->post(),$profile); $received = true; }
				catch (InvalidArgumentException $e) { $error = $e->getMessage(); }
				catch (Throwable $e) { log_message('error','Garden intake: '.$e->getMessage()); $error = 'Could not send your request. Please try again.'; }
			}
		}
		$serviceId = (int)($this->input->post('service_id') ?: $this->input->get('service_id'));
		$service = $serviceId ? $this->storefront_model->getOnlineService($serviceId,$storeId) : null;
		$productId = (int)($this->input->post('product_id') ?: $this->input->get('product_id'));
		$product = $productId ? $this->storefront_model->getOnlineProduct($productId,$storeId) : null;
		$this->theme_engine->view('garden_request',[
			'garden_request_error'=>$error,'garden_request_received'=>$received,'garden_requested_service'=>$service,'garden_requested_product'=>$product,
			'garden_request_key'=>$this->input->post('submission_ref') ?: 'garden_'.bin2hex(random_bytes(20)),
			'seo_title'=>'Garden & Service Request','categories'=>$this->storefront_model->getCategoriesWithItems($storeId),
			'store_currency'=>$this->theme_engine->getStoreCurrency(),'social_links'=>$this->theme_engine->socialLinks(),
		]);
	}

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

        $this->load->model('storefront_content_model');$editorial=$this->storefront_content_model->settings($storeId);
        echo '<url><loc>'.htmlspecialchars($base.'/privacy',ENT_XML1,'UTF-8').'</loc><changefreq>monthly</changefreq></url>';
        if(!empty($editorial['contact_enabled']))echo '<url><loc>'.htmlspecialchars($base.'/contact',ENT_XML1,'UTF-8').'</loc><changefreq>monthly</changefreq></url>';
        if(!empty($editorial['blog_enabled'])){
            echo '<url><loc>'.htmlspecialchars($base.'/blog',ENT_XML1,'UTF-8').'</loc><changefreq>weekly</changefreq></url>';
            $posts=$this->db->select('slug')->where('store_id',$storeId)->where('status','published')->where('published_at <=',gmdate('Y-m-d H:i:s'))->limit(500)->get('db_storefront_posts')->result();
            foreach($posts as $post)echo '<url><loc>'.htmlspecialchars($base.'/blog/'.rawurlencode($post->slug),ENT_XML1,'UTF-8').'</loc><changefreq>monthly</changefreq></url>';
        }
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
		// Editorial landing pages exist only on the building-materials themes.
		$theme = !empty($settings->theme_id) ? $this->theme_engine->getTheme($settings->theme_id) : null;
		if(in_array(($theme->theme_key ?? ''), ['framework_atelier', 'vector_systems'], true)){
			if($this->storefront_content_model->entries($storeId, 'application', 1)){
				echo '<url><loc>' . $base . '/applications</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>';
			}
			if($this->storefront_content_model->entries($storeId, 'project', 1)){
				echo '<url><loc>' . $base . '/projects</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>';
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
	 * Checkout hint (AJAX): which cart items are prescription-only. The
	 * authoritative refusal still happens inside place_order.
	 */
	public function rx_check(){
		$storeId = (int)$this->input->post('store_id');
		$ids = json_decode((string)$this->input->post('ids', TRUE), true);
		if(!$storeId || !is_array($ids)){
			echo json_encode(['status' => false, 'rx_required' => []]);
			return;
		}
		$ids = array_slice(array_filter(array_map('intval', $ids)), 0, 200);
		$names = function_exists('mp_prescription_gate') ? mp_prescription_gate($storeId, $ids, 0, 0, true) : [];
		echo json_encode(['status' => true, 'rx_required' => $names]);
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

		// Catalogue & enquiry mode is a server-side rule, not just a hidden
		// button. A crafted POST must not create an order or reserve stock on a
		// store whose storefront has no cart.
		$this->theme_engine->init($storeId);
		if(($this->theme_engine->currentTheme()->theme_source ?? '') === 'html_zip' && !$this->theme_engine->cartEnabled()){
			echo json_encode(['status'=>false,'message'=>'This store accepts enquiries, not online orders.','csrf_hash'=>$this->security->get_csrf_hash()]);
			return;
		}
		if($this->theme_engine->isBuildingMaterialsTheme() && !$this->theme_engine->cartEnabled()){
			echo json_encode([
				'status' => false,
				'message' => 'This store accepts product enquiries and quotations, not online orders.',
				'csrf_hash' => $this->security->get_csrf_hash(),
			]);
			return;
		}

		// Housekeeping: release stock reserved by abandoned online payments
		// (paystack orders still unpaid/pending past the TTL). WhatsApp and
		// pay-on-delivery reservations are merchant-managed and never expire.
		try { $this->storefront_model->releaseExpiredReservations($storeId); } catch(Exception $e) {}

		$customerName = trim($this->input->post('customer_name'));
		$customerEmail = trim($this->input->post('customer_email'));
		$customerPhone = trim($this->input->post('customer_phone'));
		$customerAddress = trim($this->input->post('customer_address'));
		$paymentMethod = $this->input->post('payment_method'); // paystack, paypal, whatsapp, pay_on_delivery, appointment
		$baseCurrency=$this->storefront_currency->baseCode($storeId);
		$transactionCurrency=$this->storefront_currency->selectedCode($storeId);
		try{$exchangeRate=$this->storefront_currency->rate($storeId,$transactionCurrency);}catch(Throwable $e){
			echo json_encode(['status'=>false,'message'=>$e->getMessage(),'csrf_hash'=>$this->security->get_csrf_hash()]);return;
		}
		if($paymentMethod === 'paystack' && ($transactionCurrency !== 'NGN' || $baseCurrency !== 'NGN')){
			echo json_encode(['status'=>false,'message'=>'Paystack checkout is available only for NGN orders. Choose another payment method or contact the store.','csrf_hash'=>$this->security->get_csrf_hash()]);
			return;
		}
		if($paymentMethod === 'paypal'){
			$map=json_decode((string)($settings->currency_gateway_map_json??''),true)?:[];
			if($baseCurrency!=='NGN'&&!isset($map[$baseCurrency]))$map[$baseCurrency]='paypal';
			$basePaypal=$transactionCurrency===$baseCurrency&&$baseCurrency!=='NGN';
			if((!$basePaypal&&empty($settings->multicurrency_enabled))||($transactionCurrency===$baseCurrency&&!$basePaypal)||($map[$transactionCurrency]??'')!=='paypal'){
				echo json_encode(['status'=>false,'message'=>'PayPal is not configured for the selected checkout currency.','csrf_hash'=>$this->security->get_csrf_hash()]);return;
			}
			$this->load->library('storefront_paypal');$paypalState=$this->storefront_paypal->accountSummary($storeId);
			if(empty($paypalState['enabled'])||empty($paypalState['webhook_configured'])){
				echo json_encode(['status'=>false,'message'=>'The store has not completed PayPal checkout and webhook setup.','csrf_hash'=>$this->security->get_csrf_hash()]);return;
			}
		}
		$appointmentRequest = $paymentMethod === 'appointment';
		if ($appointmentRequest) {
			$cart = [['type'=>'service','id'=>(int)$this->input->post('service_id'), 'qty'=>1]];
		} else {
			$cart = json_decode($this->input->post('cart'), true);
		}
		if(empty($cart) || !is_array($cart)){
                    echo json_encode(['status' => false, 'message' => 'Cart is empty', 'csrf_hash' => $this->security->get_csrf_hash()]);
                    return;
            }
		$serviceDate = $this->input->post('service_date');
		$serviceTime = $this->input->post('service_time');
		$serviceNote = $this->input->post('service_note');
		$shippingMethod = trim($this->input->post('shipping_method'));
		$tableNumber = trim($this->input->post('table_number'));
		$idempotencyKey = substr(trim((string)$this->input->post('idempotency_key', TRUE)), 0, 96);
		$storeProfile = function_exists('mp_get_store_profile') ? mp_get_store_profile($storeId) : [];
		$isPharmacyOrder = in_array(($storeProfile['industry_type'] ?? ''), ['pharmacy', 'medical_store', 'healthcare'], true);
		$isStandardPharmacy = $isPharmacyOrder && mp_pharmacy_standard($storeId);
		if ($isStandardPharmacy && ((int)$this->input->post('warehouse_id') > 0 || mp_pharmacy_pickup_method($shippingMethod))) {
			echo json_encode(['status' => false, 'message' => 'This pharmacy fulfils orders directly from its own store. Please refresh checkout.', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}
		$prescriptionFile = null;
		if($isPharmacyOrder && !empty($_FILES['prescription_file']['name'])){
			if(!$this->db->field_exists('prescription_file', 'db_online_orders')){
				echo json_encode(['status' => false, 'message' => 'Prescription uploads are not enabled on this pharmacy yet. Please update the installation.', 'csrf_hash' => $this->security->get_csrf_hash()]);
				return;
			}
			$uploadPath = FCPATH . 'uploads/prescriptions/';
			if(!is_dir($uploadPath) && !mkdir($uploadPath, 0775, true) && !is_dir($uploadPath)){
				echo json_encode(['status' => false, 'message' => 'Prescription upload storage is unavailable. Please try again or contact the pharmacy.', 'csrf_hash' => $this->security->get_csrf_hash()]);
				return;
			}
			$this->load->library('upload', [
				'upload_path' => $uploadPath,
				'allowed_types' => 'jpg|jpeg|png|gif|pdf|webp',
				'max_size' => 5120,
				'encrypt_name' => true,
			]);
			if(!$this->upload->do_upload('prescription_file')){
				echo json_encode(['status' => false, 'message' => trim(strip_tags($this->upload->display_errors('', ''))) ?: 'Prescription upload failed. Please try another file.', 'csrf_hash' => $this->security->get_csrf_hash()]);
				return;
			}
			$uploadData = $this->upload->data();
			$prescriptionFile = 'uploads/prescriptions/' . $uploadData['file_name'];
		}

		// Rx-only medicine in the cart is a server-side rule: the customer must
		// attach the prescription, and the pharmacy gets a pending review record
		// instead of an order that quietly reaches fulfilment.
		$rxRequiredItems = [];
		if($isPharmacyOrder && function_exists('mp_prescription_gate')){
			$rxRequiredItems = mp_prescription_gate($storeId, array_column($cart, 'id'), 0, 0, true);
			if($rxRequiredItems && $prescriptionFile === null){
				echo json_encode(['status' => false, 'prescription_required' => true,
					'message' => 'These items need a valid prescription before the pharmacy can dispense them: '
						. implode(', ', $rxRequiredItems) . '. Please attach a clear photo or PDF of the prescription.',
					'csrf_hash' => $this->security->get_csrf_hash()]);
				return;
			}
		}

		try {
			$location = $this->storefront_model->checkoutLocation($storeId, $settings,
				(int)$this->input->post('warehouse_id'), $this->_publicOrderableOutlets($storeId));
		} catch(RuntimeException $e){
			echo json_encode(['status'=>false, 'message'=>$e->getMessage(), 'csrf_hash'=>$this->security->get_csrf_hash()]); return;
		}
		$orderWarehouseId = $location['warehouse_id'];
		$sourceWarehouseId = $location['source_warehouse_id'];
		$selectedOutlet = $location['outlet'];
		$verifiedCustomer = $this->storefront_model->verifiedCustomer($storeId);

		if(!$customerName || !$customerPhone){
			echo json_encode(['status' => false, 'message' => 'Name and phone are required', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}
		if($selectedOutlet && (string)($selectedOutlet->branch_type ?? '') === 'pickup_hub' && !preg_match('/pick[\s-]*up|collect/i', (string)$shippingMethod)){
			echo json_encode(['status' => false, 'message' => 'This fulfilment hub is pickup-only. Please choose pickup.', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}
		if($selectedOutlet && isset($selectedOutlet->pickup_enabled) && !(int)$selectedOutlet->pickup_enabled
			&& preg_match('/pick[\s-]*up|collect/i', (string)$shippingMethod)){
			echo json_encode(['status' => false, 'message' => 'Pickup is not available at the selected location. Choose another branch or delivery.', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}
		if (!in_array($paymentMethod, ['paystack', 'paypal', 'whatsapp', 'pay_on_delivery', 'appointment'], true)) {
			echo json_encode(['status' => false, 'message' => 'Selected payment method is not available', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}
		if ($appointmentRequest) {
			$this->theme_engine->init($storeId, $this->_previewThemeForVisitor($settings));
			$key = $this->theme_engine->currentTheme()->theme_key ?? '';
			$date = DateTime::createFromFormat('!Y-m-d', (string)$serviceDate);
			if (!in_array($key, ['lumiere', 'skinora'], true) || empty($settings->allow_services)
				|| !$this->theme_engine->sellsServices() || !$date || $date->format('Y-m-d') !== $serviceDate
				|| $serviceDate < date('Y-m-d') || !preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', (string)$serviceTime)) {
				echo json_encode(['status' => false, 'message' => 'Please choose a valid treatment and future appointment time', 'csrf_hash' => $this->security->get_csrf_hash()]);
				return;
			}
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
		$hasEvents = false;
		$eventSeatLines = [];
		$orderQty = 0;
		$itemsToInsert = [];
		$bundleComponentsByLine = [];
		$bundleServiceComponentsByLine = [];
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
			$type = (string)($item['type'] ?? 'product');
			if(in_array($type, ['physical','digital','course','membership','event','bundle'], true)) $type = 'product';
			$id = (int)$item['id'];
			$qty = max(1, (int)($item['qty'] ?? 1));

			if($type == 'product'){
				$product = $this->storefront_model->getOnlineProduct($id, $storeId, (int)($sourceWarehouseId ?: $orderWarehouseId));
				if(!$product){
					echo json_encode(['status'=>false, 'message'=>'An item is no longer available. Please refresh your cart.', 'csrf_hash'=>$this->security->get_csrf_hash()]); return;
				}
				if (!empty($product->requires_quote)) {
					echo json_encode(['status'=>false,'message'=>'Please request a quotation for this offering.','csrf_hash'=>$this->security->get_csrf_hash()]); return;
				}
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
							// The figure comes off the shelf itself, so this says
							// the same thing the counter would say if the order
							// had been walked up to it.
							$left = (float)($product->sellable_qty ?? $product->stock);
							$message = $left <= 0
								? $product->item_name . ' is out of stock'
								: $product->item_name . ' has only ' . format_qty($left) . ' left — please reduce the quantity';
							echo json_encode(['status' => false, 'message' => $message, 'csrf_hash' => $this->security->get_csrf_hash()]);
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
				// Event tickets (4.0.9.221): the customer picked a session date in
				// the shared storefront widget; the line carries its id. Never
				// trust it — resolve store-scoped through the event that owns
				// this item, then commit seats atomically in the order
				// transaction below.
				$evSession = null;
				if($product_type === 'event'){
					$evSessionId = (int)($item['session_id'] ?? ($item['options']['session_id'] ?? 0));
					if(!$evSessionId){
						echo json_encode(['status' => false, 'message' => 'Please choose an event date for ' . $product->item_name . ' — open the event page to pick a date.', 'csrf_hash' => $this->security->get_csrf_hash()]);
						return;
					}
					$this->load->model('events_model');
					$evSession = $this->events_model->get_session($evSessionId, $storeId);
					$evEvent = $evSession ? $this->events_model->get_event($evSession->event_id, $storeId) : null;
					if(!$evEvent || (int)$evEvent->item_id !== $id || (int)$evSession->status !== 1){
						echo json_encode(['status' => false, 'message' => 'The chosen date for ' . $product->item_name . ' is no longer on sale.', 'csrf_hash' => $this->security->get_csrf_hash()]);
						return;
					}
					if($evSession->session_date < date('Y-m-d')){
						echo json_encode(['status' => false, 'message' => 'That event date has already passed.', 'csrf_hash' => $this->security->get_csrf_hash()]);
						return;
					}
					$hasEvents = true;
				}
				try {
					$priceQuote = $this->storefront_model->validateProductQuote($product, $item, $exchangeRate);
				} catch(RuntimeException $e){
					echo json_encode(['status'=>false, 'price_changed'=>true, 'message'=>$e->getMessage(), 'csrf_hash'=>$this->security->get_csrf_hash()]); return;
				}
				$price = $priceQuote['price'];
				$lineIndex = count($itemsToInsert);
				$mealNote = '';
				if (($this->theme_engine->currentTheme()->theme_key ?? '') === 'food_express') {
					$this->load->helper('food_content');
					$heat = isset($item['spice_level']) && is_string($item['spice_level']) ? $item['spice_level'] : '';
					if (in_array($heat, ['mild','medium','hot','extra'], true) && mp_food_spice_allowed($product->item_name, $product->category_name ?? '', $product->description ?? '')) {
						$mealNote = 'Spice: ' . ($heat === 'extra' ? 'Extra hot' : ucfirst($heat));
					}
					$instructions = isset($item['note']) && is_string($item['note']) ? mb_substr(trim(strip_tags($item['note'])), 0, 500, 'UTF-8') : '';
					$mealNote = implode(' · ', array_filter([$mealNote, $instructions]));
				}
				$evNote = $mealNote;
				$evItemType = $isBundle ? 'bundle' : (in_array($product_type, ['digital','course','membership']) ? $product_type : 'product');
				if($evSession){
					$evItemType = 'event';
					$evWhen = date('D, j M Y', strtotime($evSession->session_date));
					if(!empty($evSession->start_time)) $evWhen .= ' · ' . substr($evSession->start_time, 0, 5);
					$evNote = trim('When: ' . $evWhen . ($mealNote !== '' ? ' · ' . $mealNote : ''));
				}
				$itemsToInsert[] = [
					'item_type' => $evItemType,
					'item_id' => $id,
					'item_name' => $product->item_name,
					'item_image' => $product->item_image,
					'qty' => $qty,
					'unit_price' => $price,
					'total_price' => $price * $qty,
					'service_note' => $evNote,
				];
				if($this->db->field_exists('pricing_snapshot', 'db_online_order_items')){
					$itemsToInsert[count($itemsToInsert) - 1]['pricing_snapshot'] = json_encode($priceQuote);
				}
				// Column arrives with 4.0.9.221 — omit on older schemas.
				if($evSession && $this->db->field_exists('event_session_id', 'db_online_order_items')){
					$itemsToInsert[count($itemsToInsert) - 1]['event_session_id'] = (int)$evSession->id;
				}
				if($evSession){
					$eventSeatLines[] = ['line_index' => count($itemsToInsert) - 1, 'session_id' => (int)$evSession->id, 'qty' => $qty];
				}
				if($isBundle){
					$bundleServiceComponentsByLine[$lineIndex] = array_values(array_filter($bundleComponents,function($component){ return (int)($component->service_bit ?? 0)===1 && ($component->product_type ?? '')==='service'; }));
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
				$this->load->helper('offering_policy');
				if (mp_service_price_model($service) !== 'fixed') {
					echo json_encode(['status'=>false,'message'=>'Please send a request so the store can confirm the price.','csrf_hash'=>$this->security->get_csrf_hash()]); return;
				}
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
		if ($appointmentRequest && (count($cart) !== 1 || count($itemsToInsert) !== 1
			|| !$hasServices || $hasProducts || $orderQty !== 1)) {
			echo json_encode(['status' => false, 'message' => 'Choose one treatment for this appointment', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}
		$orderMinQty = max(0, (int)($settings->min_order_qty ?? 0));
		if($orderMinQty > 0 && $orderQty < $orderMinQty){
			echo json_encode(['status' => false, 'message' => 'This order requires at least ' . $orderMinQty . ' items.', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}

		// Pay on delivery only makes sense for physical products
		if($paymentMethod == 'pay_on_delivery' && ($hasDigital || $hasCourse || $hasMembership || $hasServices || $hasEvents)){
			echo json_encode(['status' => false, 'message' => 'Pay on delivery is not available for digital products, courses, memberships, event tickets or services.', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}
		// Ticket baskets only ever reach pay-on-delivery by falling back from
		// paystack/whatsapp when the store has neither enabled — say so plainly.
		if($paymentMethod == 'pay_on_delivery' && $hasEvents && !$settings->allow_pay_on_delivery){
			echo json_encode(['status' => false, 'message' => 'Event tickets need online payment or WhatsApp — this store has neither enabled.', 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}

		$hasOnlyDigital = ($hasDigital || $hasCourse || $hasMembership) && !$hasPhysical && !$hasServices;
		// Ticket-only baskets need no delivery either.
		$hasOnlyDigital = $hasOnlyDigital || ($hasEvents && !$hasPhysical && !$hasServices && !$hasDigital && !$hasCourse && !$hasMembership);
		$orderType = ($hasProducts && $hasServices) ? 'mixed' : ($hasServices ? 'service' : 'product');
		if ($appointmentRequest) {
			$shippingMethod = null;
			$customerAddress = null;
		}

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
		if(preg_match('/pick[\s-]*up|collect/i', (string)$shippingMethod)){
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
		$cityShippingOn = !$appointmentRequest && !$isPickupMethod && !$hasOnlyDigital && !$tableNumber && !empty($settings->city_shipping_enabled) && is_array($cityZones) && count($cityZones) > 0;

		if($cityShippingOn){
			$selectedCity = trim((string)$this->input->post('shipping_city'));
			$selectedParts = array_map('trim', explode('|', $selectedCity));
			$selectedCityName = $selectedParts[0] ?? '';
			$selectedStateName = $selectedParts[1] ?? '';
			$selectedCountryName = $selectedParts[2] ?? '';
			$zoneMatch = null;
			$zoneMatchScore = -1;
			foreach($cityZones as $z){
				$zoneCity = trim($z['city'] ?? '');
				$zoneState = trim($z['state'] ?? '');
				$zoneCountry = trim($z['country'] ?? '');
				// Blank fields mean all locations at that level. This allows a
				// country-wide or state-wide rule to cover destinations not listed
				// individually, like WooCommerce shipping zones.
				if($zoneCountry !== '' && strcasecmp($zoneCountry, $selectedCountryName) !== 0) continue;
				if($zoneState !== '' && strcasecmp($zoneState, $selectedStateName) !== 0) continue;
				if($zoneCity !== '' && $zoneCity !== '*' && strcasecmp($zoneCity, $selectedCityName) !== 0) continue;
				$score = ($zoneCountry !== '' ? 1 : 0) + ($zoneState !== '' ? 2 : 0) + ($zoneCity !== '' && $zoneCity !== '*' ? 4 : 0);
				// A wildcard is a fallback within its country/state, so an exact
				// city match always wins even when the wildcard row appears first.
				if($zoneCity === '*') $score += ($selectedCityName === '*' ? 4 : -0.5);
				if($score > $zoneMatchScore){
					$zoneMatch = $z;
					$zoneMatchScore = $score;
				}
			}
			if(!$zoneMatch){
				echo json_encode(['status' => false, 'message' => 'Please select your delivery area', 'csrf_hash' => $this->security->get_csrf_hash()]);
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
			$destinationLabel = trim($zoneMatch['city'] ?? '') === '*'
				? 'Other cities'
				: trim(trim($zoneMatch['city'] ?? '') . (!empty($zoneMatch['state']) ? ', ' . trim($zoneMatch['state']) : ''));
			$zoneLabel = trim($zoneMatch['name'] ?? '');
			if($zoneLabel !== '' && $destinationLabel !== '') $zoneLabel .= ' · ' . $destinationLabel;
			if($zoneLabel === '') $zoneLabel = $destinationLabel ?: trim($zoneMatch['country'] ?? '') ?: 'Other areas';
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
		// Pharmacy location fees are resolved from the selected outlet, while
		// ordinary storefronts retain their existing configured method fee.
		if($isPharmacyOrder && $selectedOutlet && !$isPickupMethod && $shippingMethod !== '' && isset($selectedOutlet->delivery_fee)){
			$shippingFee = (float)$selectedOutlet->delivery_fee;
		}
		// Storefront coupon — reuses the existing promotion engine. The code is
		// resolved server-side (store scope, active flag, date window); the
		// discount mirrors the POS coupon_amt semantics (% of cart subtotal or
		// fixed amount) and the usage claim runs atomically inside the order
		// transaction below so usage limits cannot be raced.
		$couponCode = strtoupper(trim((string)$this->input->post('coupon_code', TRUE)));
		$promo = null;
		$couponDiscount = 0;
		if($couponCode !== '' && ($this->storefront_model->verifiedHasGroup($storeId)
			|| $this->storefront_model->hasGroupPrice($itemsToInsert))){
			echo json_encode(['status'=>false, 'message'=>'Coupons do not stack with member group prices. Remove the coupon to continue.', 'csrf_hash'=>$this->security->get_csrf_hash()]); return;
		}
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
			if(!empty($promo->min_spend) && $subtotal < (float)$promo->min_spend){
				echo json_encode(['status'=>false, 'message'=>'This coupon requires a minimum item total of ' . store_number_format($promo->min_spend) . '.', 'csrf_hash'=>$this->security->get_csrf_hash()]);
				return;
			}
			$couponDiscount = ($promo->discount_type == 'Percentage')
				? $subtotal * ((float)$promo->discount_value / 100)
				: (float)$promo->discount_value;
			$couponDiscount = max(0, min(round($couponDiscount, 4), $subtotal));
		}

		$taxEnabled=!empty($settings->storefront_tax_enabled)&&$this->db->field_exists('storefront_tax_rate','db_storefront_settings');
		$taxRate=$taxEnabled?(float)($settings->storefront_tax_rate??0):0.0;
		if(!is_finite($taxRate)||$taxRate<0||$taxRate>100){
			echo json_encode(['status'=>false,'message'=>'The storefront tax rate is invalid. Ask the store to correct its tax settings before checkout.','csrf_hash'=>$this->security->get_csrf_hash()]);return;
		}
		$taxMode=($settings->storefront_tax_mode??'exclusive')==='inclusive'?'inclusive':'exclusive';
		$lineTax=function($baseLineTotal) use ($taxEnabled,$taxRate,$taxMode,$subtotal,$couponDiscount){
			if(!$taxEnabled||$taxRate<=0||$subtotal<=0)return 0.0;
			$net=max(0,(float)$baseLineTotal*(1-($couponDiscount/$subtotal)));
			return round($taxMode==='inclusive'?$net*$taxRate/(100+$taxRate):$net*$taxRate/100,2,PHP_ROUND_HALF_UP);
		};
		$baseTaxAmount=0.0;
		foreach($itemsToInsert as $taxLine)$baseTaxAmount+=$lineTax($taxLine['total_price']??0);
		foreach($addonsByParentLine as $taxAddonRows)foreach($taxAddonRows as $taxLine)$baseTaxAmount+=$lineTax($taxLine['total_price']??0);
		$baseTaxAmount=round($baseTaxAmount,2,PHP_ROUND_HALF_UP);
		$grandTotal=max(0,$subtotal+$shippingFee-$couponDiscount+($taxMode==='exclusive'?$baseTaxAmount:0));
		if($paymentMethod === 'paypal' && $deliveryQuotePending){
			echo json_encode(['status'=>false,'message'=>'PayPal checkout needs a confirmed delivery amount. Choose a priced delivery option or contact the store for a quote.','csrf_hash'=>$this->security->get_csrf_hash()]);return;
		}
		// Round each transaction-currency unit price first and derive its line
		// total from that displayed price. Summing those same line totals makes
		// the cart, saved quote and provider amount agree to the cent.
		$transactionSubtotal=0.0;
		foreach($itemsToInsert as $line){$unit=$this->storefront_currency->convertBaseToTransaction($line['unit_price']??0,$exchangeRate);$transactionSubtotal+=round($unit*(float)($line['qty']??0),2,PHP_ROUND_HALF_UP);}
		foreach($addonsByParentLine as $addonRows)foreach($addonRows as $line){$unit=$this->storefront_currency->convertBaseToTransaction($line['unit_price']??0,$exchangeRate);$transactionSubtotal+=round($unit*(float)($line['qty']??0),2,PHP_ROUND_HALF_UP);}
		$transactionSubtotal=round($transactionSubtotal,2,PHP_ROUND_HALF_UP);
		$transactionShipping=$this->storefront_currency->convertBaseToTransaction($shippingFee,$exchangeRate);
		$transactionCoupon=$this->storefront_currency->convertBaseToTransaction($couponDiscount,$exchangeRate);
		$transactionTax=$this->storefront_currency->convertBaseToTransaction($baseTaxAmount,$exchangeRate);
		$transactionGrandTotal=max(0,round($transactionSubtotal+$transactionShipping-$transactionCoupon+($taxMode==='exclusive'?$transactionTax:0),2,PHP_ROUND_HALF_UP));
		if($grandTotal>0&&$transactionGrandTotal<=0){
			echo json_encode(['status'=>false,'message'=>'This order rounds below the minimum payable amount in the selected currency. Choose another currency or contact the store.','csrf_hash'=>$this->security->get_csrf_hash()]);return;
		}

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
			'tax_amount' => $baseTaxAmount,
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
		if($this->db->field_exists('base_currency','db_online_orders')){
			$orderData += [
				'base_currency'=>$baseCurrency,'transaction_currency'=>$transactionCurrency,
				'exchange_rate'=>$exchangeRate,'exchange_rate_convention'=>'1 transaction currency unit = X base currency units',
				'exchange_rate_source'=>$transactionCurrency===$baseCurrency?'base':'merchant_manual',
				'exchange_rate_snapshot_at'=>date('Y-m-d H:i:s'),'base_subtotal'=>$subtotal,
				'transaction_subtotal'=>$transactionSubtotal,'base_delivery_fee'=>$shippingFee,
				'transaction_delivery_fee'=>$transactionShipping,'base_tax_amount'=>$baseTaxAmount,'transaction_tax_amount'=>$transactionTax,
				'base_coupon_discount'=>$couponDiscount,'transaction_coupon_discount'=>$transactionCoupon,
				'base_grand_total'=>$grandTotal,'transaction_grand_total'=>$transactionGrandTotal,
				'tax_rate_snapshot'=>$taxRate,'tax_mode_snapshot'=>$taxMode,
				'currency_snapshot_json'=>json_encode(['base_currency'=>$baseCurrency,'transaction_currency'=>$transactionCurrency,
					'exchange_rate'=>$exchangeRate,'convention'=>'1 transaction currency unit = X base currency units',
					'source'=>$transactionCurrency===$baseCurrency?'base':'merchant_manual','snapshot_at'=>date('Y-m-d H:i:s')]),
			];
		}
		if($isPharmacyOrder && $prescriptionFile && $this->db->field_exists('prescription_file', 'db_online_orders')){
			$orderData['prescription_file'] = $prescriptionFile;
		}
		if($idempotencyKey !== '' && $this->db->field_exists('idempotency_key', 'db_online_orders')){
			$orderData['idempotency_key'] = $idempotencyKey;
		}
		if($this->db->field_exists('fulfilment_type', 'db_online_orders')){
			$orderData['fulfilment_type'] = $tableNumber ? 'dine_in' : (preg_match('/pick[\s-]*up|collect/i', (string)$shippingMethod) ? 'pickup' : 'delivery');
		}
		// Coupon columns arrive with migration 4.0.9.69 — omit on older schemas.
		if($this->db->field_exists('coupon_code', 'db_online_orders')){
			$orderData['coupon_code'] = $couponCode !== '' ? $couponCode : null;
			$orderData['coupon_discount'] = $couponDiscount ?: null;
			$orderData['promotion_id'] = $promo ? (int)$promo->id : null;
		}
		// Outlet choice arrives with migration 4.0.9.162 — omit on older schemas.
		if($orderWarehouseId > 0 && $this->db->field_exists('warehouse_id', 'db_online_orders')){
			$orderData['warehouse_id'] = $orderWarehouseId;
		}
		if($sourceWarehouseId > 0 && $this->db->field_exists('source_warehouse_id', 'db_online_orders')){
			$orderData['source_warehouse_id'] = $sourceWarehouseId;
		}

		// Set initial status based on payment method. WhatsApp is an order
		// CHANNEL — the payment arrangement stays unpaid until the merchant
		// confirms how the customer will pay (it is never assumed to be COD).
		if(in_array($paymentMethod,['paystack','paypal'],true)){
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
		if($this->storefront_model->last_order_duplicate){
			$this->db->trans_commit();
			$existing = $this->storefront_model->getOrder($orderId);
			if($existing && $existing->payment_method==='paypal' && $existing->payment_status!=='paid'
				&& strtoupper((string)($existing->transaction_currency??''))!==$this->storefront_currency->selectedCode($storeId)){
				echo json_encode(['status'=>false,'message'=>'This order already has a payment quote in '.strtoupper((string)$existing->transaction_currency).'. Continue or cancel that payment before starting checkout in another currency.','csrf_hash'=>$this->security->get_csrf_hash()]);return;
			}
			$pendingApproval='';
			if($existing && $existing->payment_method==='paypal' && $existing->payment_status!=='paid' && $this->db->table_exists('db_online_order_payment_attempts')){
				$attempt=$this->db->where('store_id',(int)$existing->store_id)->where('online_order_id',(int)$existing->id)->where('provider','paypal')->where('status','created')->order_by('id','DESC')->get('db_online_order_payment_attempts')->row();
				$pendingApproval=(string)($attempt->approval_url??'');
				if($pendingApproval===''){
					try{$pendingApproval=$this->_createPaypalAttemptForOrder($existing);}catch(Throwable $e){
						log_message('error','Storefront PayPal retry setup failed for '.$existing->order_code.': '.$e->getMessage());
						echo json_encode(['status'=>false,'message'=>'PayPal could not start this payment. Your order is saved; retry checkout without placing another order.','csrf_hash'=>$this->security->get_csrf_hash()]);return;
					}
				}
			}
			echo json_encode(['status'=>true, 'duplicate'=>true, 'payment_required'=>($existing && in_array($existing->payment_method,['paystack','paypal'],true) && $existing->payment_status !== 'paid'),
				'gateway'=>($existing && $existing->payment_method==='paypal')?'paypal':null,
				'order_id'=>(int)$orderId, 'order_code'=>$existing->order_code,
				'approval_url'=>$pendingApproval,'redirect_url'=>$pendingApproval?:$this->_orderReceivedUrl($existing),
				'message'=>'This order was already received.', 'csrf_hash'=>$this->security->get_csrf_hash()]);
			return;
		}

		// Contact details identify the recipient, never grant membership or access
		// to another customer's history. Only the verified session links history.
		$checkoutProfile = mp_get_store_profile($storeId);
		$customerId = $verifiedCustomer ? (int)$verifiedCustomer->id
			: $this->customers_model->getOrCreateStorefrontCustomer($storeId, $customerName, $customerPhone, $customerEmail, false);

		if($customerId){
			$this->db->where('id', $orderId)->update('db_online_orders', ['customer_id' => $customerId]);
		}

		// Rx-only cart -> a pending prescription review record the pharmacist
		// opens from the orders screen. Fulfilment stays blocked until approved.
		if($rxRequiredItems && $prescriptionFile !== null && $this->db->table_exists('db_medical_notes')){
			$rxNote = [
				'store_id' => $storeId,
				'customer_id' => (int)$customerId,
				'note_date' => date('Y-m-d'),
				'diagnosis' => 'Online prescription request — order ' . ($orderId ? '#'.$orderId : '') . ': ' . implode(', ', $rxRequiredItems),
				'prescription_ref' => 'ONLINE-'.$orderId,
				'prescription_file' => $prescriptionFile,
				'status' => 'active',
				'created_by' => 'online-store',
				'created_date' => date('Y-m-d'),
				'created_time' => date('H:i:s'),
			];
			if($this->db->field_exists('review_status', 'db_medical_notes')){
				$rxNote['review_status'] = 'pending';
				$rxNote['review_requested_at'] = date('Y-m-d H:i:s');
			}
			if($this->db->field_exists('online_order_id', 'db_medical_notes')){
				$rxNote['online_order_id'] = (int)$orderId;
			}
			$this->db->insert('db_medical_notes', $rxNote);
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
		$snapshotLineCurrency=function(array $line) use ($exchangeRate,$lineTax){
			if($this->db->field_exists('base_unit_price','db_online_order_items')){
				$line['base_unit_price']=(float)($line['unit_price']??0);
				$line['transaction_unit_price']=$this->storefront_currency->convertBaseToTransaction($line['unit_price']??0,$exchangeRate);
				$line['base_total_price']=(float)($line['total_price']??0);
				$line['transaction_total_price']=round($line['transaction_unit_price']*(float)($line['qty']??0),2,PHP_ROUND_HALF_UP);
				$line['base_tax_amount']=$lineTax($line['total_price']??0);
				$line['transaction_tax_amount']=$this->storefront_currency->convertBaseToTransaction($line['base_tax_amount'],$exchangeRate);
			}
			return $line;
		};
		foreach($itemsToInsert as $lineIndex => $item){
			$item['order_id'] = $orderId;
			$item=$snapshotLineCurrency($item);
			$this->storefront_model->addOrderItem($item);
			$parentLineId = (int)$this->db->insert_id();
			if (($checkoutProfile['industry_type'] ?? '')==='garden_centre_landscaping') {
				$this->load->model('service_fulfilment_model');
				try {
					$this->service_fulfilment_model->snapshot_online($orderId,$parentLineId,$storeId);
					foreach ($bundleServiceComponentsByLine[$lineIndex] ?? [] as $component) $this->service_fulfilment_model->snapshot_online($orderId,$parentLineId,$storeId,$component);
				} catch (Throwable $e) {
					$this->db->trans_rollback();log_message('error','Service fulfilment snapshot: '.$e->getMessage());
					echo json_encode(['status'=>false,'message'=>'Could not prepare service fulfilment. Please try again.','csrf_hash'=>$this->security->get_csrf_hash()]);return;
				}
			}
			$reservationItems[] = $item;
			foreach($addonsByParentLine[$lineIndex] ?? [] as $addonLine){
				$addonLine['order_id'] = $orderId;
				$addonLine['parent_line_id'] = $parentLineId;
				$addonLine=$snapshotLineCurrency($addonLine);
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
				$componentLine=$snapshotLineCurrency($componentLine);
				$this->storefront_model->addOrderItem($componentLine);
				$bundleDraws[] = ['item_id' => (int)$component->component_item_id, 'qty' => $componentQty];
			}
		}

		// Reserve event seats atomically — the capacity guard lives in the
		// UPDATE itself, so two checkouts cannot oversell a session, and the
		// hold rolls back with the order transaction if anything later fails.
		if(!empty($eventSeatLines)){
			$this->load->model('events_model');
			foreach($eventSeatLines as $seatLine){
				if(!$this->events_model->reserve_seats($seatLine['session_id'], $seatLine['qty'], $storeId)){
					$this->db->trans_rollback();
					echo json_encode(['status' => false, 'message' => 'Some seats for ' . $itemsToInsert[$seatLine['line_index']]['item_name'] . ' were just taken. Please review your cart and try again.', 'csrf_hash' => $this->security->get_csrf_hash()]);
					return;
				}
			}
		}

		// Reserve stock for physical items atomically so concurrent orders
		// cannot oversell the same units. The reservation is held until the
		// order is paid/confirmed (commit) or cancelled/failed (release).
		if(!$this->storefront_model->reserveOrderItems($orderId, $reservationItems, !empty($settings->allow_backorder))){
			$this->db->trans_rollback();
			$reason = trim((string)$this->storefront_model->last_reservation_error);
			echo json_encode(['status' => false, 'message' => ($reason !== ''
				? $reason . ' Nothing was ordered.'
				: 'An item in your cart just sold out. Please review your cart and try again.'), 'csrf_hash' => $this->security->get_csrf_hash()]);
			return;
		}
		if(!empty($bundleDraws)){
			if($this->extras_model->reserveBundleComponents($bundleDraws, $orderId, !empty($settings->allow_backorder), $storeId, (int)($orderData['source_warehouse_id'] ?? $orderData['warehouse_id'] ?? 0)) === false){
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
		if($this->db->trans_status() === false){
			echo json_encode(['status'=>false, 'message'=>'The order could not be saved. Please try again.', 'csrf_hash'=>$this->security->get_csrf_hash()]);
			return;
		}
		// Pay-on-delivery orders are financially real at acceptance time. Paid
		// gateway orders are posted after payment verification below.
		if(!in_array($paymentMethod,['paystack','paypal'],true)){
			$this->storefront_model->ensureSalesLedger($orderId);
		}

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
		if ($order && !empty($order->customer_phone)) {
			$currencyCode = strtoupper((string)($order->transaction_currency??$order->base_currency??$this->_storeCurrencyCode($storeId)??'NGN'));
			$paymentNotice = $order->payment_status === 'paid' ? 'Payment is confirmed.' : 'Payment is not yet confirmed.';
			$sms = trim((string)($store->store_name ?? 'MartPoint')) . ': we received order ' . $order->order_code
			. '. Total ' . $currencyCode . ' ' . number_format((float)($order->transaction_grand_total??$order->grand_total), 2) . '. ' . $paymentNotice;
			$this->load->library('transactional_sms');
			$this->transactional_sms->send($storeId, $order->customer_phone, $sms, 'online_order_confirmation', 'online-order:' . $storeId . ':' . (int)$order->id . ':placed');
		}

		// If Paystack, return payment initialization data
		if($paymentMethod == 'paystack' && $settings->allow_paystack){
			$ps = $this->paystack->get_settings($storeId);
			if($ps && $ps->public_key){
				$split = $this->paystack->get_split_config($grandTotal, $storeId);
				if(!$split['status']){
					echo json_encode(['status'=>false, 'message'=>$split['message']]);
					return;
				}
				echo json_encode([
					'status' => true,
					'payment_required' => true,
					'order_id' => $orderId,
					'order_code' => $order->order_code,
					'amount_kobo' => (int)($grandTotal * 100),
					'public_key' => $ps->public_key,
					'email' => $customerEmail ?: 'customer@' . ($settings->store_slug ?: 'store') . '.com',
					'reference' => $order->order_code,
					'paystack_split' => $split['config'],
					'currency' => $this->_storeCurrencyCode($storeId) ?: 'NGN',
					'csrf_hash' => $this->security->get_csrf_hash(),
				]);
				return;
			}
		}
		if($paymentMethod==='paypal'){
			try{
				$approval=$this->_createPaypalAttemptForOrder($order);
				echo json_encode(['status'=>true,'payment_required'=>true,'gateway'=>'paypal','order_id'=>$orderId,
					'order_code'=>$order->order_code,'currency'=>$transactionCurrency,'amount'=>$transactionGrandTotal,
					'approval_url'=>$approval,'redirect_url'=>$approval,'csrf_hash'=>$this->security->get_csrf_hash()]);return;
			}catch(Throwable $e){log_message('error','Storefront PayPal order setup failed for '.$order->order_code.': '.$e->getMessage());
				echo json_encode(['status'=>false,'message'=>$e->getMessage(),'csrf_hash'=>$this->security->get_csrf_hash()]);return;}
		}

		// For WhatsApp or Pay on Delivery
		echo json_encode([
			'status' => true,
			'payment_required' => false,
			'order_id' => $orderId,
			'order_code' => $order->order_code,
			'redirect_url' => base_url('store/' . ($settings->store_slug ?? '') . '/order_received/' . $order->order_code),
			'message' => $appointmentRequest ? 'Appointment request received.' : 'Order placed successfully!',
			'csrf_hash' => $this->security->get_csrf_hash(),
		]);
	}

	/** Create or safely resume the immutable PayPal quote saved on an order. */
	private function _createPaypalAttemptForOrder($order){
		if(!$this->db->table_exists('db_online_order_payment_attempts')||!$this->db->field_exists('transaction_grand_total','db_online_orders')) throw new RuntimeException('Run the MartPoint multi-currency migration before using PayPal checkout.');
		$currency=strtoupper((string)($order->transaction_currency??''));$base=strtoupper((string)($order->base_currency??''));
		$amount=(float)($order->transaction_grand_total??0);$baseAmount=(float)($order->base_grand_total??$order->grand_total);
		$rate=(float)($order->exchange_rate??0);
		if($currency===''||$base===''||$amount<=0||$baseAmount<=0||$rate<=0) throw new RuntimeException('The saved PayPal order quote is incomplete.');
		$this->load->library('storefront_paypal');
		$attemptNumber=1+(int)$this->db->where('store_id',(int)$order->store_id)->where('online_order_id',(int)$order->id)->where('provider','paypal')->count_all_results('db_online_order_payment_attempts');
		$created=$this->storefront_paypal->createOrder((int)$order->store_id,$order->order_code,$currency,$amount,
			base_url('storefront/paypal_return/'.rawurlencode($order->order_code)),
			base_url('storefront/paypal_cancel/'.rawurlencode($order->order_code)),$attemptNumber);
		$approval='';foreach((array)($created['links']??[]) as $link)if(($link['rel']??'')==='approve'){$approval=(string)$link['href'];break;}
		$providerOrderId=(string)($created['id']??'');
		if($providerOrderId===''||$approval==='') throw new RuntimeException('PayPal did not return an approval link.');
		$now=date('Y-m-d H:i:s');
		$ok=$this->db->insert('db_online_order_payment_attempts',['store_id'=>(int)$order->store_id,'online_order_id'=>(int)$order->id,'provider'=>'paypal',
			'provider_order_id'=>$providerOrderId,'approval_url'=>$approval,'transaction_currency'=>$currency,
			'transaction_amount'=>$amount,'base_currency'=>$base,'base_amount'=>$baseAmount,
			'exchange_rate'=>$rate,'rate_snapshot_at'=>(string)($order->exchange_rate_snapshot_at??$now),'status'=>'created','created_at'=>$now,'updated_at'=>$now]);
		if(!$ok) throw new RuntimeException('PayPal created the payment but MartPoint could not save its reference. Retry checkout to recover the same payment.');
		$this->db->where('id',(int)$order->id)->where('store_id',(int)$order->store_id)->update('db_online_orders',['paypal_order_id'=>$providerOrderId]);
		return $approval;
	}

	/** Current, verified customer prices and location stock for every storefront. */
	public function cart_quote(){
		$storeId = (int)$this->input->post('store_id');
		$out = function($data){ $data['csrf_hash'] = $this->security->get_csrf_hash(); echo json_encode($data); };
		$settings = $storeId ? $this->storefront_model->getSettings($storeId) : null;
		if(!$settings || $settings->store_status !== 'active'){ $out(['status'=>false, 'message'=>'Store unavailable.']); return; }
		$this->theme_engine->init($storeId);
		if(($this->theme_engine->currentTheme()->theme_source ?? '') === 'html_zip' && !$this->theme_engine->cartEnabled()){
			$out(['status'=>false,'message'=>'This store accepts enquiries, not online orders.']); return;
		}
		try {
			$location = $this->storefront_model->checkoutLocation($storeId, $settings,
				(int)$this->input->post('warehouse_id'), $this->_publicOrderableOutlets($storeId), false);
			$items = $this->storefront_model->quoteCart($storeId, json_decode((string)$this->input->post('cart'), true), $location['source_warehouse_id']);
			$customer = $this->storefront_model->verifiedCustomer($storeId);
			$quoteCurrency = $this->storefront_currency->selectedCode($storeId);
			$out(['status'=>true, 'items'=>$items, 'member'=>!empty($customer),
				'transaction_currency'=>$quoteCurrency, 'exchange_rate'=>$this->storefront_currency->rate($storeId,$quoteCurrency),
				'customer_name'=>$customer ? $customer->customer_name : '',
				'has_group_price'=>$this->storefront_model->hasGroupPrice($items),
				'warehouse_id'=>$location['warehouse_id'], 'needs_location'=>!empty($location['needs_location'])]);
		} catch(RuntimeException $e){ $out(['status'=>false, 'message'=>$e->getMessage()]); }
	}

	/** Public, store-scoped stock notes for the storefront location picker. */
	public function location_availability(){
		$storeId = (int)$this->input->post('store_id');
		$cart = json_decode((string)$this->input->post('cart'), true);
		$out = function($data){ $data['csrf_hash'] = $this->security->get_csrf_hash(); echo json_encode($data); };
		$settings = $storeId ? $this->storefront_model->getSettings($storeId) : null;
		if(!$settings || $settings->store_status !== 'active' || !is_array($cart) || count($cart) > 200){ $out(['status'=>false, 'message'=>'Store or cart is unavailable.']); return; }
		$locations = $this->_publicOrderableOutlets($storeId);
		$result = [];
		foreach($locations as $location){
			$id = (int)$location->id;
			$sourceId = (int)($location->stock_source_id ?? 0) ?: $id;
			try {
				$items = $this->storefront_model->quoteCart($storeId, $cart, $sourceId);
				$lines = [];
				$allAvailable = true;
				foreach($items as $item){
					$available = !array_key_exists('available', $item) || !empty($item['available']);
					$allAvailable = $allAvailable && $available;
					$lines[] = ['name'=>(string)($item['name'] ?? 'Item'), 'available'=>$available,
						'quantity'=>(int)($item['qty'] ?? 1), 'stock'=>(float)($item['stock'] ?? 0)];
				}
				$result[] = ['id'=>$id, 'name'=>(string)$location->warehouse_name,
					'address'=>trim((string)($location->address ?? '')), 'city'=>trim((string)($location->city ?? '')),
					'available'=>$allAvailable, 'items'=>$lines];
			} catch(RuntimeException $e){
				$result[] = ['id'=>$id, 'name'=>(string)$location->warehouse_name,
					'address'=>trim((string)($location->address ?? '')), 'city'=>trim((string)($location->city ?? '')),
					'available'=>false, 'items'=>[], 'note'=>'Availability could not be confirmed at this location.'];
			}
		}
		$out(['status'=>true, 'locations'=>$result]);
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
		try{$currencyRate=$this->storefront_currency->rate($storeId,$this->storefront_currency->selectedCode($storeId));}catch(Throwable $e){$currencyRate=1.0;}
		$result = [];
		foreach($items as $item){
			$image = !empty($item->item_image) ? base_url($item->item_image) : '';
			$result[] = [
				'id' => (int)$item->id,
				'name' => (string)$item->item_name,
				'price' => $this->storefront_currency->convertBaseToTransaction((float)$item->effective_price,$currencyRate),
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
		try{$rate=$this->storefront_currency->rate($storeId,$this->storefront_currency->selectedCode($storeId));}catch(Throwable $e){$out(['status'=>false,'message'=>$e->getMessage()]);return;}
		$baseSubtotal=$subtotal*$rate;

		if($this->storefront_model->verifiedHasGroup($storeId)){
			$out(['status'=>false,'message'=>'Coupons do not stack with member group prices.']); return;
		}

		$promo = $this->db->where('store_id', $storeId)
			->where('status', 1)
			->where('UPPER(promotion_code)', $code)
			->get('db_promotions')->row();
		if(!$promo){ $out(['status'=>false,'message'=>'Invalid coupon code']); return; }
		if($promo->start_date > date('Y-m-d')){ $out(['status'=>false,'message'=>'This coupon starts on '.show_date($promo->start_date)]); return; }
		if($promo->end_date < date('Y-m-d')){ $out(['status'=>false,'message'=>'This coupon expired on '.show_date($promo->end_date)]); return; }
		if(!empty($promo->min_spend) && $baseSubtotal < (float)$promo->min_spend){
			$out(['status'=>false,'message'=>'Minimum spend of '.store_number_format($promo->min_spend).' required']); return;
		}
		$baseDiscount = ($promo->discount_type == 'Percentage')
			? $baseSubtotal * ((float)$promo->discount_value / 100)
			: (float)$promo->discount_value;
		$discount=$this->storefront_currency->convertBaseToTransaction(min($baseDiscount,$baseSubtotal),$rate);
		$out(array('status'=>true,'promo_name'=>$promo->promotion_name,'discount'=>max(0,min($discount,$subtotal))));
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
		$previous = $this->storefront_model->getCartByToken($storeId, $token);
		if($previous && $previous->status === 'ordered'){
			$out(['status'=>false,'message'=>'ordered_token']);
			return;
		}

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
		if($savedCart && !empty($savedCart->coupon_code) && !$this->storefront_model->verifiedHasGroup($storeId)){
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

	/**
	 * Campaign open pixel (public, token-scoped). Returns an invisible 1x1 image
	 * and counts one open per recipient per hour; mail scanners are never counted.
	 */
	public function campaign_open($token = ''){
		$this->load->model('communication_report_model', 'comm_report');
		$this->load->library('communication_tracking');
		try {
			if ($this->comm_report->ready()) {
				$send = $this->comm_report->sendByToken($token);
				if ($send && $send->status === 'sent') $this->comm_report->recordOpen($send, (string)$this->input->server('HTTP_USER_AGENT'));
			}
		} catch (Throwable $e) {}
		$this->output->set_content_type('image/gif')
			->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
			->set_header('Pragma: no-cache')->set_header('Expires: Thu, 01 Jan 1970 00:00:00 GMT')
			->set_header('X-Content-Type-Options: nosniff')
			->set_output(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'));
	}

	/**
	 * Campaign link redirect (public). The signature is bound to the recipient
	 * token, so only links this store actually delivered can pass through.
	 */
	public function campaign_click($token = '', $signature = '', $link = ''){
		$this->load->model('communication_report_model', 'comm_report');
		$this->load->library('communication_tracking');
		$url = Communication_tracking::verifyClick($token, $link, $signature);
		if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) { show_error('This link is no longer valid.', 400); return; }
		try {
			if ($this->comm_report->ready()) {
				$send = $this->comm_report->sendByToken($token);
				if ($send && $send->status === 'sent') $this->comm_report->recordClick($send, $url, (string)$this->input->server('HTTP_USER_AGENT'));
			}
		} catch (Throwable $e) {}
		// The reader's destination must not learn the recipient token from a referrer.
		$this->output->set_header('Referrer-Policy: no-referrer')->set_header('Cache-Control: no-store')->set_status_header(302)->set_header('Location: ' . $url);
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

		$eventTickets = [];
		$hasEventLine = false;
		foreach($items as $item){
			if(($item->item_type ?? '') === 'event'){ $hasEventLine = true; break; }
		}
		if($hasEventLine){
			$this->load->model('events_model');
			$eventTickets = $this->events_model->customerTickets($order->id, $storeId);
		}

		$data = [
			'settings' => $settings,
			'store' => $store,
			'order' => $order,
			'items' => $items,
			'download_links' => $downloadLinks,
			'event_tickets' => $eventTickets,
			'has_event_line' => $hasEventLine,
			'store_currency' => $this->theme_engine->getStoreCurrency(),
			'logo_url' => $this->theme_engine->logoUrl(),
			'favicon_url' => $this->theme_engine->faviconUrl(),
			'social_links' => $this->theme_engine->socialLinks(),
			'seo_title' => 'Order Confirmed - ' . ($store->store_name ?? 'Store'),
			'seo_description' => 'Your order has been received.',
			'seo_canonical' => base_url('store/' . $settings->store_slug . '/order_received/' . $orderCode),
			'page_title' => 'Order Received',
		];
		if ($order->payment_method === 'appointment'
			&& in_array(($this->theme_engine->currentTheme()->theme_key ?? ''), ['lumiere', 'skinora'], true)) {
			$data['seo_title'] = 'Appointment Request Received';
			$data['seo_description'] = 'Your treatment appointment request has been received.';
			$data['seo_type'] = 'website';
			$this->theme_engine->view('appointment_received', $data);
			return;
		}
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
		$wrongCurrency = false;
		if($verify['status'] && $verify['payment_status'] === 'success'
			&& $verify['reference'] === $order->order_code
			&& abs((float)$verify['amount'] - (float)$order->grand_total) < 0.01){

			$verified = true;
			$storeCur = $this->_storeCurrencyCode($order->store_id);
			if($storeCur && strtoupper($verify['currency']) !== $storeCur){
				$verified = false;
				$wrongCurrency = true;
				log_message('error', "Paystack currency mismatch on order {$order->id}: gateway {$verify['currency']} vs store {$storeCur}");
			}
		} else if(!empty($verify['status'])){
			log_message('error', "Paystack verify failed checks on order {$order->id}: status=" . ($verify['payment_status'] ?? '?') . " ref=" . ($verify['reference'] ?? '?') . " amount=" . ($verify['amount'] ?? '?') . " expected={$order->grand_total}");
		}

		if(!$verified){
			// A charge the gateway confirms for this order but whose amount does not
			// match the invoice is still money. Record it, let the ledger decide what
			// the order now looks like, and hand the difference to the reconciliation
			// queue instead of dropping it in a log file. Money in the wrong currency
			// is not the invoiced money, so it stays out of the order's ledger.
			$this->load->model('online_payments_model', 'onlinepay');
			if($verify['status'] && !$wrongCurrency && (string)($verify['reference'] ?? '') === (string)$order->order_code
				&& (string)($verify['payment_status'] ?? '') === 'success'){
				$ledger = $this->onlinepay->recordGatewayMoney($order, [
					'provider'  => 'paystack',
					'reference' => $reference,
					'amount'    => $verify['amount'] ?? 0,
					'transaction_amount' => $verify['amount'] ?? 0,
					'currency'  => $verify['currency'] ?? null,
					'transaction_currency' => $verify['currency'] ?? null,
				], 'the card payment page');
				if($ledger['recorded'] || $ledger['duplicate']){
					$settle = $this->onlinepay->settleOrder((int)$order->store_id, (int)$order->id, null, 'card payment');
					if(!empty($ledger['covered']) || ($settle['state'] ?? '') === 'paid'){
						$this->storefront_model->recordPurchaseEvent($order->id, 'paystack_verify');
						$this->storefront_model->fulfilPaidOrder($order->id);
						return $verify;
					}
					log_message('error', 'Paystack amount does not match online order ' . $order->id
						. ' (settled ' . ($verify['amount'] ?? '?') . ' of ' . $order->grand_total . '); recorded and queued.');
				}
			}
			return false;
		}

		// Atomically claim the transition; only the winning request fulfils.
		// The charge itself is recorded first, so the amount the customer actually
		// sent is a fact next to the order rather than only a status word.
		$this->load->model('online_payments_model', 'onlinepay');
		$ledger = $this->onlinepay->recordGatewayMoney($order, [
			'provider'  => 'paystack',
			'reference' => $reference,
			'amount'    => $verify['amount'],
			'transaction_amount' => $verify['amount'],
			'currency'  => $verify['currency'] ?? null,
			'transaction_currency' => $verify['currency'] ?? null,
		], 'the card payment page');
		$claimed = $this->storefront_model->claimPaidOrder($order->id, [
			'paystack_reference' => $reference,
			'paystack_amount' => $verify['amount'],
			'order_status' => 'paid'
		]);
		if($claimed){
			// Commit the stock reservation (reserved -> committed; no second
			// decrement — units already moved at order time).
			$this->storefront_model->adjustStock($order->id);
			$salesId = (int)$this->storefront_model->ensureSalesLedger($order->id);
			// Post the confirmed money onto the counter's own payment lines. Also
			// covers an order whose sales record existed before this charge landed.
			$this->onlinepay->postVerifiedLines((int)$order->store_id, (int)$order->id, $salesId);
			// Paid conversion — recorded only on the verified transition;
			// deduped by event_id across callback/webhook/browser retries.
			$this->storefront_model->recordPurchaseEvent($order->id, 'paystack_verify');
			$items = $this->storefront_model->getOrderItems($order->id);
			$settings = $this->storefront_model->getSettings($order->store_id);
			$store = get_store_details($order->store_id);
			$this->_send_digital_delivery_email($order, $items, $store, $settings);
			if (!empty($order->customer_phone)) {
				$currencyCode = strtoupper((string)($order->transaction_currency??$order->base_currency??$this->_storeCurrencyCode($order->store_id)??'NGN'));
				$sms = trim((string)($store->store_name ?? 'MartPoint')) . ': payment received for order ' . $order->order_code
					. '. Amount ' . $currencyCode . ' ' . number_format((float)($order->transaction_grand_total??$order->grand_total), 2) . '.';
				$this->load->library('transactional_sms');
				$this->transactional_sms->send((int)$order->store_id, $order->customer_phone, $sms, 'online_order_payment_received', 'online-order:' . (int)$order->store_id . ':' . (int)$order->id . ':payment-paid');
			}
		} else {
			// Payment verified but the order can no longer be claimed — e.g.
			// the reservation was already released and the order cancelled.
			// A replay of a charge already credited to this order (the browser
			// popup or webhook won the claim moments ago) is not news; anything
			// else is real money that landed where it cannot be applied, and the
			// merchant has to see it rather than a line in a log file.
			if(empty($ledger['duplicate'])){
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
		}
		// Fulfilment is separate from the claim and idempotent: it runs for
		// the winner now, and self-heals here on any later verify/callback
		// if the winning request died mid-fulfilment (fulfilled_at gates it).
		$this->storefront_model->fulfilPaidOrder($order->id);
		return $verify;
	}

	/**
	 * Public "pay this order" page, reached from the invoice link a customer is
	 * sent after a pharmacist approves their prescription. There is no login, no
	 * cart and no session here: the 32-character secret in the URL is the whole
	 * authorisation, which is why it is stored hashed and carries an expiry.
	 */
	public function pay_link($token = ''){
		$this->_renderPayLink($token, '');
	}

	/**
	 * The "Pay now" click. The card session is opened server-side against the
	 * order's own total, so the amount a customer can pay is the amount the
	 * pharmacy invoiced — including the delivery charge — and never a number the
	 * browser supplied.
	 */
	public function pay_link_start($token = ''){
		$order = $this->storefront_model->payLinkOrder($token);
		$state = $this->storefront_model->payLinkState($order);
		if($state !== 'payable'){
			$this->_renderPayLink($token, 'This link can no longer be used to pay.');
			return;
		}
		$settings = $this->storefront_model->getSettings((int)$order->store_id);
		if(empty($settings->allow_paystack)){
			$this->_renderPayLink($token, 'Card payment is not switched on for this store. Please pay at collection, or ask the store to send you transfer details.');
			return;
		}
		$init = $this->paystack->initialize_online_order_checkout($order, base_url('storefront/paystack_callback'));
		if(!empty($init['status']) && !empty($init['authorization_url'])){
			redirect($init['authorization_url'], 'location');
			return;
		}
		$this->_renderPayLink($token, $init['message'] ?? 'The card payment page could not be opened just now. Please try again.');
	}

	private function _renderPayLink($token, $payError){
		$order = $this->storefront_model->payLinkOrder($token);
		$state = $this->storefront_model->payLinkState($order);
		if($state === 'missing'){
			show_404();
			return;
		}
		$storeId = (int)$order->store_id;
		$settings = $this->storefront_model->getSettings($storeId);
		$store = get_store_details($storeId);
		$rx = $this->storefront_model->rxItemNames((int)$order->id, $storeId);
		$this->load->model('online_payments_model', 'onlinepay');
		$paidIn = $this->onlinepay->available() ? $this->onlinepay->verifiedTotal($storeId, (int)$order->id) : 0.0;
		$this->load->view('storefront/pay_link', [
			'state'        => $state,
			'order'        => $order,
			'paid_in'      => $paidIn,
			'items'        => $state === 'payable' ? $this->storefront_model->getOrderItems((int)$order->id) : [],
			'store_name'   => trim((string)($store->store_name ?? '')) ?: 'the store',
			'branch_name'  => $this->_payLinkBranchName($order),
			'store_currency' => $this->theme_engine->getStoreCurrency($storeId),
			'pay_error'    => $payError,
			'pay_url'      => base_url('pay/' . $token . '/start'),
			'allow_paystack' => !empty($settings->allow_paystack),
			'csrf'         => $this->security->get_csrf_hash(),
			'csrf_name'    => $this->security->get_csrf_token_name(),
			'rx_names'     => $rx['names'],
		]);
	}

	private function _payLinkBranchName($order){
		if(empty($order->warehouse_id) || !$this->db->table_exists('db_warehouse')) return '';
		$branch = $this->db->select('warehouse_name')->where('id', (int)$order->warehouse_id)
			->where('store_id', (int)$order->store_id)->get('db_warehouse')->row();
		return $branch ? trim((string)$branch->warehouse_name) : '';
	}

	/**
	 * Paystack callback for online orders (browser redirect / manual check).
	 */
	/** PayPal redirects here after approval; the browser never marks an order paid. */
	public function paypal_return($orderCode=''){
		$order=$this->storefront_model->getOrderByCode((string)$orderCode);
		if(!$order || $order->payment_method!=='paypal' || !$this->db->table_exists('db_online_order_payment_attempts')){show_404();return;}
		$providerOrderId=trim((string)$this->input->get('token',TRUE));
		$attempt=$this->db->where('store_id',(int)$order->store_id)->where('online_order_id',(int)$order->id)
			->where('provider','paypal')->where('provider_order_id',$providerOrderId)->get('db_online_order_payment_attempts')->row();
		if(!$attempt || $providerOrderId===''){show_error('This PayPal approval does not match the order.',400);return;}
		if($order->payment_status==='paid'){redirect($this->_orderReceivedUrl($order));return;}
		try{
			$this->load->library('storefront_paypal');
			$providerOrder=$this->storefront_paypal->getOrder((int)$order->store_id,$providerOrderId);
			if(($providerOrder['status']??'')==='APPROVED'){
				$providerOrder=$this->storefront_paypal->captureOrder((int)$order->store_id,$providerOrderId,'capture-'.$attempt->id.'-'.substr(hash('sha256',$providerOrderId),0,16));
			}
			if(!$this->_completePaypalOrder($order,$attempt,$providerOrder,'buyer_return')) throw new RuntimeException('PayPal payment could not be verified yet. If you were charged, do not try again; contact the store with order '.$order->order_code.'.');
			redirect($this->_orderReceivedUrl($order));
		}catch(Throwable $e){log_message('error','PayPal return verification failed for order '.$order->id.': '.$e->getMessage());
			$this->_renderPaypalReturnError($order,$e->getMessage());}
	}

	/** Verify PayPal cancellation before keeping the unpaid order available for retry. */
	public function paypal_cancel($orderCode=''){
		$order=$this->storefront_model->getOrderByCode((string)$orderCode);
		if(!$order||$order->payment_method!=='paypal'||!$this->db->table_exists('db_online_order_payment_attempts')){show_404();return;}
		$providerOrderId=trim((string)$this->input->get('token',TRUE));
		if($providerOrderId==='')$providerOrderId=(string)($order->paypal_order_id??'');
		$attempt=$this->db->where('store_id',(int)$order->store_id)->where('online_order_id',(int)$order->id)
			->where('provider','paypal')->where('provider_order_id',$providerOrderId)->get('db_online_order_payment_attempts')->row();
		if(!$attempt){show_error('This PayPal cancellation does not match the order.',400);return;}
		try{
			$this->load->library('storefront_paypal');
			$providerOrder=$this->storefront_paypal->getOrder((int)$order->store_id,$providerOrderId);
			if(($providerOrder['status']??'')==='COMPLETED'){
				if($this->_completePaypalOrder($order,$attempt,$providerOrder,'cancel_return')){redirect($this->_orderReceivedUrl($order));return;}
				throw new RuntimeException('PayPal completed this payment, but MartPoint is still reconciling it. Do not pay again.');
			}
			if(in_array(strtoupper((string)($providerOrder['status']??'')),['CREATED','APPROVED','PAYER_ACTION_REQUIRED'],true)){
				$this->_renderPaypalReturnError($order,'No completed PayPal capture was found. The order remains unpaid and its original payment quote is saved. Return to checkout to resume that payment; MartPoint will not start a second payment for this order.');return;
			}
			throw new RuntimeException('PayPal has not confirmed the payment status. Do not pay again yet.');
		}catch(Throwable $e){log_message('error','PayPal cancel verification failed for order '.$order->id.': '.$e->getMessage());$this->_renderPaypalReturnError($order,$e->getMessage());}
	}

	/** Authenticated PayPal REST webhook. */
	public function paypal_webhook(){
		$raw=(string)file_get_contents('php://input');$event=json_decode($raw,true);
		if(!is_array($event)||($event['event_type']??'')!=='PAYMENT.CAPTURE.COMPLETED'){$this->output->set_status_header(200)->set_output('ignored');return;}
		$related=$event['resource']['supplementary_data']['related_ids']['order_id']??'';
		if(!$related || !$this->db->table_exists('db_online_order_payment_attempts')){$this->output->set_status_header(200)->set_output('unmatched');return;}
		$attempt=$this->db->where('provider','paypal')->where('provider_order_id',(string)$related)->get('db_online_order_payment_attempts')->row();
		if(!$attempt){$this->output->set_status_header(200)->set_output('unmatched');return;}
		$this->load->library('storefront_paypal');
		$headers=[];foreach(['paypal-transmission-id','paypal-transmission-time','paypal-cert-url','paypal-auth-algo','paypal-transmission-sig'] as $h)$headers[$h]=$this->input->get_request_header($h,TRUE);
		if(!$this->storefront_paypal->verifyWebhook((int)$attempt->store_id,$headers,$event)){$this->output->set_status_header(401)->set_output('invalid signature');return;}
		try{
			$providerOrder=$this->storefront_paypal->getOrder((int)$attempt->store_id,(string)$related);
			$order=$this->storefront_model->getOrder((int)$attempt->online_order_id);
			if($order && $this->_completePaypalOrder($order,$attempt,$providerOrder,'webhook')){$this->output->set_status_header(200)->set_output('ok');return;}
			throw new RuntimeException('PayPal capture does not match the saved checkout quote.');
		}catch(Throwable $e){log_message('error','PayPal webhook processing failed for attempt '.$attempt->id.': '.$e->getMessage());$this->output->set_status_header(500)->set_output('retry');}
	}

	private function _completePaypalOrder($order,$attempt,array $providerOrder,$source){
		if(($providerOrder['status']??'')!=='COMPLETED') return false;
		$purchase=$providerOrder['purchase_units'][0]??[];
		if((string)($purchase['custom_id']??$purchase['reference_id']??'')!==(string)$order->order_code) return false;
		$capture=$purchase['payments']['captures'][0]??[];
		$amount=$capture['amount']??[];$captureId=(string)($capture['id']??'');
		if(($capture['status']??'')!=='COMPLETED'||$captureId===''||strtoupper((string)($amount['currency_code']??''))!==strtoupper((string)$attempt->transaction_currency)) return false;
		if(abs((float)($amount['value']??0)-(float)$attempt->transaction_amount)>0.005) return false;
		if(strtoupper((string)$attempt->base_currency)!==strtoupper((string)($order->base_currency??$attempt->base_currency))
			|| abs((float)$attempt->exchange_rate-(float)($order->exchange_rate??$attempt->exchange_rate))>0.0000001) return false;
		if($this->db->where('provider','paypal')->where('capture_reference',$captureId)->count_all_results('db_online_order_payment_attempts')>0
			&& (string)($attempt->capture_reference??'')!==$captureId) return false;
		$attemptBase=round((float)$attempt->base_amount,2);
		$this->load->model('online_payments_model','onlinepay');
		$money=$this->onlinepay->recordGatewayMoney($order,[
			'provider'=>'paypal','reference'=>$captureId,'provider_reference'=>$captureId,
			'amount'=>$attemptBase,'base_amount'=>$attemptBase,'currency'=>$attempt->base_currency,
			'transaction_amount'=>$attempt->transaction_amount,
			'transaction_currency'=>$attempt->transaction_currency,'provider_status'=>'COMPLETED',
			'payment_status'=>'success','note'=>'PayPal capture '.$captureId,
		],'PayPal '.$source);
		if(empty($money['recorded']) && empty($money['duplicate'])) return false;
		$claimed=$this->storefront_model->claimPaidOrder((int)$order->id,[
			'paypal_order_id'=>(string)$attempt->provider_order_id,
		]);
		if(!$claimed && (string)$order->payment_status!=='paid') return false;
		// A provider capture can succeed while local posting is interrupted. Every
		// authenticated retry repairs the idempotent stock/ledger steps before it
		// acknowledges the event to PayPal.
		$stockCommitted=$this->storefront_model->adjustStock((int)$order->id);
		$stockState=$this->storefront_model->getOrder((int)$order->id);
		if(!$stockCommitted && isset($stockState->stock_state) && $stockState->stock_state!=='committed') return false;
		$salesId=(int)$this->storefront_model->ensureSalesLedger((int)$order->id);
		if(!$salesId) return false;
		$posted=$this->onlinepay->postVerifiedLines((int)$order->store_id,(int)$order->id,$salesId);
		if(!$posted && $this->db->field_exists('salespayment_id','db_online_order_payments')
			&& $this->db->where('store_id',(int)$order->store_id)->where('online_order_id',(int)$order->id)
			->where('status','verified')->where('salespayment_id IS NULL')->count_all_results('db_online_order_payments')>0) return false;
		if($claimed)$this->storefront_model->recordPurchaseEvent((int)$order->id,'paypal_capture');
		$this->db->where('id',(int)$attempt->id)->where('store_id',(int)$attempt->store_id)->update('db_online_order_payment_attempts',[
			'status'=>'captured','capture_reference'=>$captureId,'updated_at'=>date('Y-m-d H:i:s')]);
		$latest=$this->storefront_model->getOrder((int)$order->id);
		$this->storefront_model->fulfilPaidOrder((int)$order->id);
		return (string)($latest->payment_status??'')==='paid'||$claimed;
	}

	private function _renderPaypalReturnError($order,$message){
		$settings=$this->storefront_model->getSettings((int)$order->store_id);
		$store=get_store_details((int)$order->store_id);
		$this->load->view('storefront/paypal_return_error',['order'=>$order,'settings'=>$settings,'store'=>$store,'message'=>$message]);
	}

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
			'wishlist_return_item' => ((string)$this->input->get('return') === 'wishlist') ? (int)$this->input->get('item') : 0,
			'wishlist_return' => ((string)$this->input->get('return') === 'wishlist'),
			'store_industry' => $this->storefront_model->normalizeThemeIndustry($this->theme_engine->getIndustry()),
			'csrf_name' => $this->security->get_csrf_token_name(),
			'csrf_hash' => $this->security->get_csrf_hash()
		];
		if(($this->theme_engine->currentTheme()->theme_key ?? '')==='botanical_elegance'){
			$data['seo_title'] = 'Sign in';
			$data['store_currency'] = $this->theme_engine->getStoreCurrency();
			$data['categories'] = $this->storefront_model->getCategoriesWithItems($storeId);
			$data['social_links'] = $this->theme_engine->socialLinks();
			$data['favicon_url'] = $this->theme_engine->faviconUrl();
			$this->theme_engine->view('verify', $data);
		} else {
			$this->load->view('storefront/verify', $data);
		}
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
			'secure' => is_https(),
			'httponly' => TRUE
		]);

		echo json_encode([
			'status' => true,
			'message' => 'Verified',
			'csrf_hash' => $this->security->get_csrf_hash()
		]);
	}

	/** Account-owned wishlist page. The optional item is added after OTP sign-in. */
	public function wishlist($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = (int)$settings->store_id;
		$store = get_store_details($storeId);
		$this->theme_engine->init($storeId, $this->_previewThemeForVisitor($settings));
		if(!$this->theme_engine->wishlistEnabled()) show_404();

		$token = $this->input->cookie('customer_token', TRUE);
		$session = $token ? $this->storefront_model->getCustomerPortalSession($token, $storeId) : null;
		if(!$session){
			$this->load->helper('cookie');
			if($token) delete_cookie('customer_token');
			$itemId = (int)$this->input->get('item');
			$redirect = base_url('store/' . $settings->store_slug . '/verify?return=wishlist' . ($itemId > 0 ? '&item=' . $itemId : ''));
			redirect($redirect);
			return;
		}

		$itemId = (int)$this->input->get('item');
		if($itemId > 0){
			$item = $this->storefront_model->getOnlineProduct($itemId, $storeId);
			if($item && (int)($item->service_bit ?? 0) === 0 && (int)($item->status ?? 0) === 1){
				$this->storefront_model->ensureCustomerWishlistItem($storeId, (int)$session->customer_id, $itemId);
			}
		}

		$this->theme_engine->view('wishlist', [
			'settings' => $settings,
			'store' => $store,
			'wishlist_products' => $this->storefront_model->customerWishlistProducts($storeId, (int)$session->customer_id),
			'seo_title' => 'Wishlist'
		]);
	}

	/** AJAX save/remove; authentication and product ownership are checked server-side. */
	public function wishlist_toggle($storeSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$storeId = (int)$settings->store_id;
		$this->theme_engine->init($storeId, $this->_previewThemeForVisitor($settings));
		$this->output->set_content_type('application/json');
		if(!$this->theme_engine->wishlistEnabled()){
			$this->output->set_status_header(404)->set_output(json_encode(['status' => false, 'message' => 'Wishlist is unavailable']));
			return;
		}
		$itemId = (int)$this->input->post('item_id');
		$item = $itemId > 0 ? $this->storefront_model->getOnlineProduct($itemId, $storeId) : null;
		if(!$item || (int)($item->service_bit ?? 0) !== 0 || (int)($item->status ?? 0) !== 1){
			$this->output->set_status_header(422)->set_output(json_encode(['status' => false, 'message' => 'This product is not available for wishlisting', 'csrf_hash' => $this->security->get_csrf_hash()]));
			return;
		}
		$token = $this->input->cookie('customer_token', TRUE);
		$session = $token ? $this->storefront_model->getCustomerPortalSession($token, $storeId) : null;
		if(!$session){
			$redirect = base_url('store/' . $settings->store_slug . '/verify?return=wishlist&item=' . $itemId);
			$this->output->set_output(json_encode(['status' => false, 'login_required' => true, 'redirect' => $redirect, 'csrf_hash' => $this->security->get_csrf_hash()]));
			return;
		}
		$saved = $this->storefront_model->wishlistToggle($storeId, (int)$session->customer_id, $itemId);
		$this->output->set_output(json_encode([
			'status' => true, 'saved' => (bool)$saved,
			'count' => count($this->storefront_model->getCustomerWishlistIds($storeId, (int)$session->customer_id)),
			'csrf_hash' => $this->security->get_csrf_hash()
		]));
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

		if (($this->theme_engine->currentTheme()->theme_key ?? '') === 'food_express') {
			$this->theme_engine->view('account', [
				'settings' => $settings, 'store' => $store, 'customer' => $customer,
				'orders' => $orders, 'seo_title' => 'Your profile',
				'wishlist_enabled' => $this->theme_engine->wishlistEnabled(),
				'wishlist_url' => base_url('store/' . $settings->store_slug . '/wishlist'),
				'store_currency' => $this->theme_engine->getStoreCurrency(),
				'outlets' => $this->_publicOrderableOutlets($storeId),
				'social_links' => $this->theme_engine->socialLinks(),
				'favicon_url' => $this->theme_engine->faviconUrl()
			]);
			return;
		}

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
			'wishlist_enabled' => $this->theme_engine->wishlistEnabled(),
			'wishlist_url' => base_url('store/' . $settings->store_slug . '/wishlist'),
			'csrf_name' => $this->security->get_csrf_token_name(),
			'csrf_hash' => $this->security->get_csrf_hash()
		];
		if(($this->theme_engine->currentTheme()->theme_key ?? '')==='botanical_elegance'){
			$data['seo_title'] = 'Your account';
			$data['store_currency'] = $this->theme_engine->getStoreCurrency();
			$data['categories'] = $this->storefront_model->getCategoriesWithItems($storeId);
			$data['social_links'] = $this->theme_engine->socialLinks();
			$data['favicon_url'] = $this->theme_engine->faviconUrl();
			$this->theme_engine->view('account', $data);
		} else {
			$this->load->view('storefront/account', $data);
		}
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
		if(in_array(($this->theme_engine->currentTheme()->theme_key ?? ''), ['botanical_elegance', 'food_express'], true)){
			$data['seo_title'] = 'Your orders';
			$data['store_currency'] = $this->theme_engine->getStoreCurrency();
			$data['categories'] = $this->storefront_model->getCategoriesWithItems($storeId);
			$data['social_links'] = $this->theme_engine->socialLinks();
			$data['favicon_url'] = $this->theme_engine->faviconUrl();
			$this->theme_engine->view('account_orders', $data);
		} else {
			$this->load->view('storefront/account_orders', $data);
		}
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
		$requested = (int)$this->input->get('mp_theme_preview');
		$token = trim((string)$this->input->get('mp_theme_token'));
		$sessionPreviewId = (int)$this->session->userdata('html_theme_preview_theme_id');
		$sessionPreviewToken = trim((string)$this->session->userdata('html_theme_preview_token'));
		if($requested > 0 && $token !== '' && $sessionPreviewId === $requested && $sessionPreviewToken !== '' && (int)get_current_store_id() === (int)$settings->store_id){
			$expected = hash_hmac('sha256', (int)$settings->store_id . '|' . $requested . '|' . date('Y-m-d'), (string)$this->config->item('encryption_key'));
			if(hash_equals($expected, $token) && hash_equals($sessionPreviewToken, $token)){
				$theme = $this->html_theme_manager->getTheme($requested, (int)$settings->store_id);
				if($theme) return $requested;
			}
		}
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

		// A public storefront must resolve to an explicit configured slug or
		// connected domain. Never accept ?store_id= or fall back to another store.
		$settings = $this->storefront_model->getStoreBySlug($slug);
		if(!$settings){
			show_404();
			return;
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

	private function _orderEmailBrandData($store, $settings){
		$logo = '';
		if(!empty($settings->store_logo) && file_exists($settings->store_logo)){
			$logo = base_url($settings->store_logo);
		} elseif(!empty($store->store_logo) && file_exists($store->store_logo)){
			$logo = base_url($store->store_logo);
		} else {
			$logo = base_url('theme/dist/img/logo1.png');
		}
		$storeName = htmlspecialchars($store->store_name ?? ($settings->store_name ?? 'Store'));
		$storeEmail = htmlspecialchars(!empty($settings->store_email) ? $settings->store_email : ($store->email ?? ''));
		$storePhone = htmlspecialchars($settings->store_phone ?? ($store->mobile ?? ''));
		$storeAddress = htmlspecialchars($settings->store_address ?? ($store->address ?? ''));

		$contactLines = [];
		if($storeAddress !== ''){ $contactLines[] = '<p>' . $storeAddress . '</p>'; }
		$reach = [];
		if($storePhone !== ''){ $reach[] = '<a href="tel:' . preg_replace('/[^0-9+]/', '', $storePhone) . '">' . $storePhone . '</a>'; }
		if($storeEmail !== ''){ $reach[] = '<a href="mailto:' . $storeEmail . '">' . $storeEmail . '</a>'; }
		if(!empty($reach)){ $contactLines[] = '<p>' . implode(' &nbsp;·&nbsp; ', $reach) . '</p>'; }

		$contactText = trim(implode("\n", array_filter([
			$storeAddress,
			trim($storePhone . ($storePhone !== '' && $storeEmail !== '' ? ' · ' : '') . $storeEmail),
		])));

		return [
			'store_name'        => $storeName,
			'store_email'       => $storeEmail,
			'store_logo_url'    => $logo,
			'store_contact_html'=> implode('', $contactLines),
			'store_contact_text'=> $contactText,
		];
	}

	private function _orderItemsHtml($items, $currency, $withPrices = true){
		$html = '';
		foreach($items as $item){
			$nameCell = "<td class='name' style='padding:12px;font-size:14px;color:#334155;border-bottom:1px solid #F1F5F9;'>" . htmlspecialchars($item['item_name']) . "</td>";
			$qtyCell = "<td class='ctr' style='padding:12px;font-size:14px;color:#334155;border-bottom:1px solid #F1F5F9;text-align:center;'>" . (int)$item['qty'] . "</td>";
			if($withPrices){
				$lineTotal = $currency . number_format($item['total_price'], 2);
				$html .= '<tr>' . $nameCell . $qtyCell
					. "<td class='num' style='padding:12px;font-size:14px;color:#334155;border-bottom:1px solid #F1F5F9;text-align:right;'>" . $lineTotal . "</td></tr>";
			} else {
				$html .= '<tr>' . $nameCell . $qtyCell . '</tr>';
			}
		}
		return $html;
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

			// Customer-facing message values follow the immutable order currency snapshot.
			$curRow = $this->db->query("SELECT a.currency as symbol, b.currency_placement as placement FROM db_currency a, db_store b WHERE a.id = b.currency_id AND b.id = ? LIMIT 1", [$order->store_id])->row();
			$currencyCode=strtoupper((string)($order->transaction_currency??$order->base_currency??$this->_storeCurrencyCode($order->store_id)??'NGN'));
			$symbols=['NGN'=>'₦','CAD'=>'$','USD'=>'$','EUR'=>'€','GBP'=>'£'];
			$currency=$currencyCode.' '.($symbols[$currencyCode]??($curRow->symbol??''));
			$rate=(float)($order->exchange_rate??1);if($rate<=0)$rate=1;
			$emailItems=[];foreach((array)$items as $it){
				$it=(array)$it;
				if($currencyCode!==strtoupper((string)($order->base_currency??$currencyCode))){
					$it['unit_price']=$this->storefront_currency->convertBaseToTransaction($it['unit_price']??0,$rate);
					$it['total_price']=round((float)$it['unit_price']*(float)($it['qty']??0),2,PHP_ROUND_HALF_UP);
				}
				$emailItems[]=$it;
			}
			$displaySubtotal=(float)($order->transaction_subtotal??$order->subtotal);
			$displayShipping=(float)($order->transaction_delivery_fee??$order->delivery_fee);
			$displayTax=(float)($order->transaction_tax_amount??$order->tax_amount??0);
			$displayGrandTotal=(float)($order->transaction_grand_total??$order->grand_total);
			$isAppointment = ($order->payment_method === 'appointment');

			$brand = $this->_orderEmailBrandData($store, $settings);
			$storeName = $brand['store_name'];

			$appointmentData = [];
			if($isAppointment){
				$firstItem = $items[0] ?? null;
				$dateLabel = '';
				if(!empty($order->service_date)){
					$ts = strtotime($order->service_date);
					$dateLabel = $ts ? date('l, j F Y', $ts) : $order->service_date;
				}
				$note = trim((string)($order->service_note ?? ''));
				$priceLine = (float)$order->grand_total > 0
					? 'Estimated total: <b>' . $currency . number_format($displayGrandTotal, 2) . '</b> — settled at your visit.'
					: 'Pricing for this treatment is on request — we\'ll confirm the final amount when confirming your appointment.';
				$appointmentData = [
					'service_name'      => htmlspecialchars($firstItem['item_name'] ?? ''),
					'appointment_date'  => htmlspecialchars($dateLabel),
					'appointment_time'  => htmlspecialchars((string)($order->service_time ?? '')),
					'appointment_note'  => $note !== '' ? htmlspecialchars($note) : '—',
					'price_note_html'   => '<p class="price-line">' . $priceLine . '</p>',
					'price_note_text'   => (float)$order->grand_total > 0
						? 'Estimated total: ' . $currency . number_format($displayGrandTotal, 2) . ' — settled at your visit.'
						: 'Pricing for this treatment is on request — we\'ll confirm the final amount when confirming your appointment.',
				];
			}

			$tplData = array_merge($brand, $appointmentData, [
				'order_code'        => $order->order_code,
				'customer_name'     => htmlspecialchars($order->customer_name),
				'customer_phone'    => htmlspecialchars($order->customer_phone),
				'customer_email'    => $order->customer_email ?: 'N/A',
				'customer_address'  => $order->customer_address ?: 'N/A',
				'payment_method'    => ucfirst(str_replace('_', ' ', $order->payment_method)),
				'shipping_method'   => $order->shipping_method ?: 'N/A',
				'order_items'       => $this->_orderItemsHtml($emailItems, $currency, !$isAppointment),
				'order_items_text'  => implode('', array_map(function($item) use ($currency, $isAppointment){
					$price = $isAppointment ? '' : ' = ' . $currency . number_format($item['total_price'], 2);
					return "- {$item['item_name']} x{$item['qty']}{$price}\n";
				}, $emailItems)),
				'subtotal'          => $currency . number_format($displaySubtotal, 2),
				'tax_amount'       => $displayTax > 0 ? $currency . number_format($displayTax, 2) : 'None',
				'delivery_fee'      => $displayShipping > 0 ? $currency . number_format($displayShipping, 2) : 'Free',
				'grand_total'       => $currency . number_format($displayGrandTotal, 2),
				'order_link'        => base_url('store/' . ($settings->store_slug ?? '') . '/order_received/' . $order->order_code),
				'dashboard_link'    => base_url('online_store/order_detail/' . (int)$order->id),
				'fulfilment_branch' => 'Store team',
				'prescription_link' => !empty($order->prescription_file) ? base_url($order->prescription_file) : 'Not attached',
			]);

			$ownerTemplate = $isAppointment ? 'appointment_request_owner' : 'online_order_owner';
			$customerTemplate = $isAppointment ? 'appointment_request_customer' : 'online_order_customer';
			$logContext = ['related_module' => 'storefront', 'related_record_id' => $order->id];

			// ---- 1. Email to the fulfilment branch and the general store mailbox ----
			$storeEmail = !empty($settings->store_email) ? $settings->store_email : ($store->email ?? '');
			$ownerRecipients = [];
			if(!empty($storeEmail) && filter_var($storeEmail, FILTER_VALIDATE_EMAIL)) $ownerRecipients[] = $storeEmail;
			if($this->db->field_exists('warehouse_id', 'db_online_orders') && !empty($order->warehouse_id) && $this->db->table_exists('db_warehouse') && $this->db->field_exists('email', 'db_warehouse')){
				$branch = $this->db->select('warehouse_name, email')->where('id', (int)$order->warehouse_id)->where('store_id', (int)$order->store_id)->where('status', 1)->get('db_warehouse')->row();
				if($branch && filter_var($branch->email ?? '', FILTER_VALIDATE_EMAIL)) $ownerRecipients[] = $branch->email;
				if($branch && !empty($branch->warehouse_name)) $tplData['fulfilment_branch'] = htmlspecialchars($branch->warehouse_name);
			}
			$ownerRecipients = array_values(array_unique($ownerRecipients));
			if(!empty($ownerRecipients)){
				$result = $this->email_service->sendTemplate($ownerTemplate, count($ownerRecipients) === 1 ? $ownerRecipients[0] : $ownerRecipients, $tplData, $logContext);
				log_message('debug', 'Storefront: Owner email result: ' . json_encode($result));
			}

			// ---- 2. Confirmation email to customer ----
			if(!empty($order->customer_email) && filter_var($order->customer_email, FILTER_VALIDATE_EMAIL)){
				$result2 = $this->email_service->sendTemplate($customerTemplate, $order->customer_email, $tplData, $logContext);
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
	/** Additional public pages declared by an imported HTML website. */
	public function page($storeSlug = '', $pageSlug = ''){
		$settings = $this->_getSettingsOr404($storeSlug);
		$this->theme_engine->init((int)$settings->store_id, $this->_previewThemeForVisitor($settings));
		$theme = $this->theme_engine->currentTheme();
		$manifest = json_decode((string)($theme->manifest_json ?? ''), true);
		$page = is_array($manifest) ? ($manifest['pages'][$pageSlug] ?? null) : null;
		if(($theme->theme_source ?? '') !== 'html_zip' || !is_array($page) || !is_string($page['template'] ?? null)
			|| !isset($manifest['templates'][$page['template']])){ show_404(); return; }
		$this->theme_engine->view('page', [
			'html_template'=>$page['template'], 'seo_title'=>(string)($page['title'] ?? $pageSlug),
			'seo_canonical'=>base_url('store/'.rawurlencode($settings->store_slug).'/page/'.rawurlencode($pageSlug)),
			'categories'=>$this->storefront_model->getCategoriesWithItems((int)$settings->store_id),
			'store_currency'=>$this->theme_engine->getStoreCurrency(),
		]);
	}

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
