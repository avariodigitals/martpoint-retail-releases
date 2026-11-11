<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Safe, deliberately small Mustache-like renderer for uploaded themes. */
class Html_theme_renderer {

    private $CI;

    public function __construct(){ $this->CI =& get_instance(); }

    public function render($theme, $view, array $data = [], array $options = []){
        $manifest = json_decode((string)($theme->manifest_json ?? ''), true);
        if(!is_array($manifest)) throw new RuntimeException('Uploaded theme manifest is invalid.');
        $templates = (array)($manifest['templates'] ?? []);
        $map = ['store'=>'home','products'=>'catalogue','product_detail'=>'product','cart'=>'cart','checkout'=>'checkout'];
        $templateKey = $view === 'page' ? (string)($data['html_template'] ?? '') : ($map[$view] ?? $view);
        $root = realpath(mp_private_storage_path((string)($theme->storage_path ?? ''), true));
        if(!$root) throw new RuntimeException('Uploaded theme storage is unavailable.');
        $read = function($key) use ($templates, $root){
            $relative = (string)($templates[$key] ?? '');
            if($relative === '' || preg_match('~(^|/)\.\.(?:/|$)|\0~', $relative)) throw new RuntimeException('Theme template path is unsafe.');
            $path = realpath($root . '/' . ltrim(str_replace('\\','/',$relative), '/'));
            if(!$path || strpos($path, $root . DIRECTORY_SEPARATOR) !== 0 || !is_file($path)) throw new RuntimeException('Theme template is missing: ' . $key);
            $contents = file_get_contents($path);
            if($contents === false || preg_match('~<\?(?:php|=)|\?>~i', $contents)) throw new RuntimeException('PHP is not allowed in uploaded theme templates.');
            return $contents;
        };
        $this->currentTheme = $theme;
        $context = $this->context($theme, $manifest, $data);
        $header = $this->renderTemplate($read('header'), $context);
        $body = $this->renderTemplate($read($templateKey), $context);
        $footer = $this->renderTemplate($read('footer'), $context);
        $css = [];
        foreach((array)($manifest['assets']['css'] ?? []) as $asset) $css[] = '<link rel="stylesheet" href="' . htmlspecialchars($this->assetUrl($theme, $asset), ENT_QUOTES, 'UTF-8') . '">';
        $js = [];
        foreach((array)($manifest['assets']['js'] ?? []) as $asset) $js[] = '<script src="' . htmlspecialchars($this->assetUrl($theme, $asset), ENT_QUOTES, 'UTF-8') . '" defer></script>';
        $runtime = $this->runtime($theme, $data, !empty($options['preview']));
        $html = $header . $body . $footer;
        if(!empty($data['currency_selector_enabled'])){
            $selector=$this->CI->load->view('themes/shared/currency_selector',$data,true);
            if($selector!=='' && stripos($html,'<body')!==false) $html=preg_replace('~(<body\b[^>]*>)~i','$1'.$selector,$html,1);
        }
        $headInjection = implode("\n", $css);
        if($headInjection !== '' && stripos($html, '</head>') !== false) $html = preg_replace('~</head>~i', $headInjection . "\n</head>", $html, 1);
        $scripts = $runtime . implode("\n", $js);
        if(stripos($html, '</body>') !== false) $html = preg_replace('~</body>~i', $scripts . "\n</body>", $html, 1); else $html .= $scripts;
        return $html;
    }

    private function context($theme, array $manifest, array $data){
        $settings = (array)($data['settings'] ?? []);
        $store = (array)($data['store'] ?? []);
        $slug = (string)($settings['store_slug'] ?? $store['store_slug'] ?? '');
        $base = base_url('store/' . rawurlencode($slug));
        $currency = (array)($data['store_currency'] ?? []);
        $money = function($value) use ($currency){
            $symbol = (string)($currency['symbol'] ?? $currency['currency_symbol'] ?? $currency['code'] ?? '');
            return trim($symbol . ' ' . number_format((float)$value, 2));
        };
        $product = function($item) use ($base, $money, $settings){ return $this->itemContext($item, $base, $money, !empty($settings['allow_backorder'])); };
        $lists = ['products','all_products','featured_products','best_sellers','new_arrivals','related_products','product_variants','featured_services','services'];
        $out = [
            'theme' => [
                'name' => (string)($manifest['name'] ?? $theme->theme_name ?? 'MartPoint Theme'),
                'version' => (string)($manifest['version'] ?? $theme->theme_version ?? ''),
                'primary_color' => (string)($data['html_theme_primary'] ?? $theme->default_primary_color ?? '#173B5E'),
                'secondary_color' => (string)($data['html_theme_secondary'] ?? $theme->default_secondary_color ?? '#B85C38'),
                'font_family' => (string)($theme->default_font_family ?? 'Inter'),
            ],
            'store' => [
                'id' => (int)($store['id'] ?? $settings['store_id'] ?? 0),
                'name' => (string)($store['store_name'] ?? $settings['store_name'] ?? 'MartPoint Store'),
                'slug' => $slug,
                'description' => (string)($settings['store_description'] ?? ''),
                'logo' => $this->imageUrl($data['logo_url'] ?? $settings['store_logo'] ?? ''),
                'email' => (string)($settings['store_email'] ?? $store['email'] ?? ''),
                'phone' => (string)($settings['store_phone'] ?? $store['mobile'] ?? ''),
                'address' => (string)($settings['store_address'] ?? $store['address'] ?? ''),
                'whatsapp' => (string)($settings['whatsapp_number'] ?? ''),
            ],
            'content' => [
                'headline' => (string)($settings['store_headline'] ?? ''),
                'subheadline' => (string)($settings['store_subheadline'] ?? ''),
                'announcement' => (string)($settings['announcement_bar'] ?? ''),
                'footer_about' => (string)($settings['footer_about_us'] ?? ''),
            ],
            'settings' => $this->savedSettings($theme, $manifest),
            'connector' => ['version'=>1],
            'page' => [
                'title' => (string)($data['seo_title'] ?? $store['store_name'] ?? 'Store'),
                'description' => (string)($data['seo_description'] ?? ''),
                'canonical' => (string)($data['seo_canonical'] ?? $base),
            ],
            'navigation' => [
                'home_url' => $base,
                'products_url' => $base . '/products',
                'catalogue_url' => $base . '/catalogue',
                'cart_url' => $base . '/cart',
                'checkout_url' => $base . '/checkout',
                'contact_url' => $base . '/contact',
            ],
            'actions' => [
                'place_order_url' => base_url('storefront/place_order'),
                'cart_upsells_url' => base_url('storefront/cart_upsells'),
                'subscribe_url' => $base . '/subscribe',
                'asset_base_url' => base_url('storefront/theme_asset/' . (int)($theme->owner_store_id ?? $store['id'] ?? $settings['store_id'] ?? 0) . '/' . (int)$theme->id),
            ],
            'cart' => ['items' => [], 'count' => 0, 'subtotal' => 0, 'total' => 0, 'currency' => (string)($currency['symbol'] ?? $currency['code'] ?? '')],
            'capabilities' => array_values((array)($manifest['capabilities'] ?? [])),
        ];
        foreach(['categories'] as $key){
            $out[$key] = [];
            foreach((array)($data[$key] ?? []) as $row){
                $r = $this->plain($row);
                $out[$key][] = ['id'=>(int)($r['id'] ?? 0), 'name'=>(string)($r['category_name'] ?? $r['name'] ?? ''), 'url'=>$base . '/products?category=' . (int)($r['id'] ?? 0)];
            }
        }
        foreach($lists as $key){
            $out[$key] = [];
            foreach((array)($data[$key] ?? []) as $row) $out[$key][] = $product($row);
        }
        if(!empty($data['product'])) $out['product'] = $product($data['product']);
        else $out['product'] = [];
        $out['service'] = !empty($data['service']) ? $product($data['service']) : [];
        if(!empty($data['related_products'])) $out['related_products'] = array_map($product, (array)$data['related_products']);
        $out['search'] = ['term'=>(string)($data['search'] ?? ''), 'url'=>$base . '/products'];
        $out['catalogue'] = ['page'=>(int)($data['page'] ?? 1), 'total'=>(int)($data['total'] ?? count($out['products'])), 'total_pages'=>(int)($data['total_pages'] ?? 1)];
        return $this->extendContext($out, $data, $settings, $base);
    }

    private function savedSettings($theme, array $manifest){
        $this->CI->load->library('html_theme_content');
        $values = $this->CI->html_theme_content->values($theme, $manifest);
        foreach($this->CI->html_theme_content->fields($manifest) as $key=>$field){
            if($field['type'] === 'image' && $values[$key] !== '') $values[$key] = strpos($values[$key],'assets/') === 0 ? $this->assetUrl($theme,$values[$key]) : $this->imageUrl($values[$key]);
        }
        return $values;
    }

    /** Only public display fields cross the template boundary. */
    private function extendContext(array $out, array $data, array $settings, $base){
        foreach(['cart_enabled','sells_products','sells_services','show_prices','is_service_store'] as $key) $out['commerce'][$key] = !empty($data[$key]);
        if(empty($data['show_prices'])){
            $hide = function(&$product){if(is_array($product) && $product){$product['price']='';$product['original_price']='';$product['price_raw']=0;}};
            foreach(['products','all_products','featured_products','best_sellers','new_arrivals','related_products','product_variants','featured_services','services'] as $key){foreach($out[$key] as &$product)$hide($product);unset($product);}
            $hide($out['product']);
            $hide($out['service']);
        }
        $out['commerce']['selling_mode'] = (string)($data['selling_mode'] ?? 'catalogue');
        $out['commerce']['allow_backorder'] = !empty($settings['allow_backorder']);
        $out['security'] = ['csrf_name'=>$this->CI->security->get_csrf_token_name(), 'csrf_hash'=>$this->CI->security->get_csrf_hash()];
        $out['checkout'] = ['payment_methods'=>[], 'shipping_methods'=>[], 'city_shipping'=>[]];
        $currency = (array)($data['store_currency'] ?? []);
        foreach(['pay_on_delivery'=>'Pay on delivery','whatsapp'=>'Confirm on WhatsApp','paystack'=>'Pay online','paypal'=>'PayPal'] as $key=>$label){
            $enabled = $key === 'paypal' ? !empty($data['paypal_enabled']) : !empty($settings['allow_'.$key]);
            if($key === 'paystack') $enabled = $enabled && !empty($data['paystack_enabled']) && (($currency['code'] ?? '') === 'NGN');
            if($enabled && !empty($data['cart_enabled'])) $out['checkout']['payment_methods'][] = ['value'=>$key, 'label'=>$label];
        }
        foreach(['shipping_methods_json'=>'shipping_methods','city_shipping_json'=>'city_shipping'] as $key=>$target){
            if($target === 'city_shipping' && empty($settings['city_shipping_enabled'])) continue;
            $rows = json_decode((string)($settings[$key] ?? ''), true);
            foreach(is_array($rows) ? $rows : [] as $row){
                if(!is_array($row) || ($target === 'shipping_methods' && empty($row['enabled']))) continue;
                $row = array_intersect_key($row, array_flip(['name','fee','city','state','country','fee_type','free_over','min_order','quote','description']));
                if($target === 'city_shipping'){
                    $row['value'] = ($row['city'] ?? '').'|'.($row['state'] ?? '').'|'.($row['country'] ?? '');
                    $row['label'] = implode(', ',array_filter([$row['city'] ?? '',$row['state'] ?? '',$row['country'] ?? '']));
                    if($row['label'] === '') continue;
                }
                $out['checkout'][$target][] = $row;
            }
        }
        $publicLists = [
            'active_banners'=>['id','banner_title','banner_subtitle','desktop_image','mobile_image','button_text','button_url','banner_type'],
            'hero_banners'=>['id','banner_title','banner_subtitle','desktop_image','mobile_image','button_text','button_url'],
            'promo_banners'=>['id','banner_title','banner_subtitle','desktop_image','mobile_image','button_text','button_url'],
            'brands'=>['id','brand_name','brand_logo','brand_url'],
            'testimonials'=>['id','customer_name','testimonial_text','rating','customer_photo'],
            'faqs'=>['id','question','answer'],
            'instagram_posts'=>['id','image_url','caption','link_url'],
            'outlets'=>['id','warehouse_name','address','city','state','country','business_hours','delivery_coverage','delivery_fee','pickup_enabled','branch_type'],
            'branches'=>['id','warehouse_name','address','city','state','country','mobile','business_hours','pickup_enabled'],
        ];
        foreach($publicLists as $key=>$fields){
            $out[$key] = [];
            foreach((array)($data[$key] ?? []) as $row){
                $row = $this->plain($row);
                if(!is_array($row)) continue;
                $row = array_intersect_key($row, array_flip($fields));
                foreach(['image','mobile_image','desktop_image','image_url','brand_logo','customer_photo'] as $image){
                    if(!empty($row[$image])) $row[$image] = $this->imageUrl($row[$image]);
                }
                if($key === 'outlets') $row['name'] = (string)($row['warehouse_name'] ?? '');
                if(substr($key,-7) === 'banners'){
                    $row['title'] = (string)($row['banner_title'] ?? ''); $row['subtitle'] = (string)($row['banner_subtitle'] ?? '');
                    $row['image'] = (string)($row['desktop_image'] ?? ''); $row['url'] = (string)($row['button_url'] ?? '');
                }
                $out[$key][] = $row;
            }
        }
        $out['homepage_sections'] = [];
        foreach((array)($data['homepage_sections'] ?? []) as $row){
            $row = $this->plain($row);
            if(!is_array($row) || empty($row['is_enabled'])) continue;
            $section = array_intersect_key($row, array_flip(['section_key','section_label','title','subtitle','display_order','config_json']));
            $section['config'] = json_decode((string)($row['config_json'] ?? ''), true) ?: [];
            $out['homepage_sections'][] = $section;
        }
        $out['social_links'] = $this->plain($data['social_links'] ?? []);
        $out['business_hours'] = $this->plain($data['business_hours'] ?? []);
        $out['site_content'] = $this->plain($data['site_content'] ?? []);
        $out['about'] = ['body'=>(string)($data['about_content'] ?? '')];
        $out['contact'] = ['can_submit'=>!empty($data['contact_can_submit']), 'received'=>!empty($data['contact_received']),
            'error'=>(string)($data['contact_error'] ?? ''), 'subject'=>(string)($data['contact_subject'] ?? ''),
            'submission_ref'=>(string)($data['contact_key'] ?? ''), 'action_url'=>$base.'/contact'];
        $out['journal_posts'] = [];
        foreach((array)($data['journal_posts'] ?? []) as $post) $out['journal_posts'][] = $this->postContext($post, $base);
        $out['journal_post'] = !empty($data['journal_post']) ? $this->postContext($data['journal_post'], $base) : [];
        $out['pages'] = [];
        $manifest = json_decode((string)($this->currentTheme->manifest_json ?? ''),true);
        foreach((array)($manifest['pages'] ?? []) as $slug=>$page) $out['pages'][] = ['title'=>(string)($page['title'] ?? ''),'url'=>$base.'/page/'.rawurlencode($slug)];
        foreach(['services','about','privacy','blog','account','track','branches'] as $page) $out['navigation'][$page.'_url'] = $base.'/'.$page;
        $out['navigation']['primary'] = $this->plain($data['storefront_navigation_rows']['primary'] ?? $data['storefront_nav'] ?? []);
        $out['navigation']['secondary'] = $this->plain($data['storefront_navigation_rows']['secondary'] ?? []);
        $out['actions']['quote_url'] = base_url('storefront/cart_quote');
        $out['actions']['locations_url'] = base_url('storefront/location_availability');
        // Preserve preview across internal links, without altering canonical URLs.
        if(!empty($data['html_theme_preview'])){
            $query = 'mp_theme_preview='.(int)$this->currentTheme->id.'&mp_theme_token='.rawurlencode((string)$this->CI->session->userdata('html_theme_preview_token'));
            $walk = function(&$value, $key) use (&$walk, $base, $query){
                if(is_array($value)){ foreach($value as $k=>&$child) $walk($child, $k); unset($child); }
                elseif(is_string($value) && ($key === 'url' || substr((string)$key,-4) === '_url') && ($value === $base || strpos($value,$base.'/') === 0 || strpos($value,$base.'?') === 0)){
                    $parts = explode('#', $value, 2);
                    $value = $parts[0].(strpos($parts[0],'?') === false ? '?' : '&').$query.(isset($parts[1]) ? '#'.$parts[1] : '');
                }
            };
            foreach(['navigation','categories','products','featured_products','all_products','best_sellers','new_arrivals','related_products','product_variants','product','featured_services','services','service','pages','journal_posts','journal_post'] as $key) $walk($out[$key], $key);
        }
        return $out;
    }

    private function postContext($post, $base){
        $row = (array)$post;
        $out = array_intersect_key($row, array_flip(['title','excerpt','body','published_at','slug']));
        $out['image'] = $this->imageUrl($row['image_url'] ?? '');
        $out['url'] = $base.'/blog/'.rawurlencode((string)($row['slug'] ?? ''));
        return $out;
    }

    private function itemContext($item, $base, $money, $allowBackorder = false){
        $r = $this->plain($item);
        $id = (int)($r['id'] ?? 0);
        $name = (string)($r['item_name'] ?? $r['service_name'] ?? $r['name'] ?? 'Product');
        $price = (float)($r['effective_price'] ?? $r['online_price'] ?? $r['sales_price'] ?? $r['price'] ?? 0);
        $image = $this->imageUrl($r['item_image'] ?? $r['service_image'] ?? $r['image'] ?? '');
        $type = isset($r['service_name']) ? 'service' : (string)($r['item_type'] ?? 'product');
        $physical = $type !== 'service' && ($r['product_type'] ?? 'physical') === 'physical';
        $inStock = !$physical || (float)($r['stock'] ?? 0) > 0 || $allowBackorder;
        return [
            'id'=>$id, 'name'=>$name, 'description'=>strip_tags((string)($r['description'] ?? '')), 'category'=>(string)($r['category_name'] ?? ''),
            'price'=>$money($price), 'price_raw'=>$price, 'original_price'=>$money((float)($r['original_price'] ?? $price)), 'stock'=>(float)($r['stock'] ?? 0),
            'in_stock'=>$inStock, 'image'=>$image,
            'can_add_to_cart'=>$inStock && ($r['item_group'] ?? '') !== 'Variants' && empty($r['requires_quote']),
            'url'=>$base . ($type === 'service' ? '/service/' : '/product/') . $id, 'type'=>$type,
            'product_type'=>(string)($r['product_type'] ?? 'physical'), 'is_variant_parent'=>($r['item_group'] ?? '') === 'Variants',
            'requires_quote'=>!empty($r['requires_quote']),
        ];
    }

    private function plain($value){
        if(is_object($value)) $value = get_object_vars($value);
        if(!is_array($value)) return $value;
        $out = [];
        foreach($value as $k=>$v) $out[$k] = is_array($v) || is_object($v) ? $this->plain($v) : $v;
        return $out;
    }

    private function renderTemplate($template, array $context){
        $tokens = preg_split('/(\{\{.*?\}\})/s', $template, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
        $index = 0;
        $parse = function($closing = null) use (&$parse, &$index, $tokens){
            $nodes = [];
            while($index < count($tokens)){
                $token = $tokens[$index++];
                if(substr($token,0,2) !== '{{'){ $nodes[] = ['text',$token]; continue; }
                $tag = trim(substr($token,2,-2));
                if($tag === 'else' || substr($tag,0,1) === '/'){
                    if($closing === null || ($tag !== 'else' && $tag !== '/'.$closing) || ($tag === 'else' && $closing !== 'if')) throw new RuntimeException('Mismatched HTML theme section.');
                    return [$nodes,$tag];
                }
                if(preg_match('/^#if\s+([a-zA-Z0-9_.-]+)$/D', $tag, $m)){
                    list($yes,$stop) = $parse('if'); $no = [];
                    if($stop === 'else') list($no,$stop) = $parse('if');
                    if($stop !== '/if') throw new RuntimeException('Unclosed HTML theme condition.');
                    $nodes[] = ['if',$m[1],$yes,$no];
                } elseif(preg_match('/^#([a-zA-Z0-9_.-]+)$/D', $tag, $m)){
                    list($children,$stop) = $parse($m[1]);
                    if($stop !== '/'.$m[1]) throw new RuntimeException('Unclosed HTML theme loop.');
                    $nodes[] = ['each',$m[1],$children];
                } else { $nodes[] = ['value',$tag]; }
            }
            if($closing !== null) throw new RuntimeException('Unclosed HTML theme section.');
            return [$nodes,null];
        };
        list($nodes) = $parse();
        $render = function($nodes, $scope) use (&$render){
            $html = '';
            foreach($nodes as $node){
                if($node[0] === 'text'){ $html .= $node[1]; continue; }
                $value = $this->lookup($scope,$node[1]);
                if($node[0] === 'if'){ $html .= $render($value ? $node[2] : $node[3], $scope); }
                elseif($node[0] === 'each'){
                    foreach(is_array($value) ? $value : [] as $item) $html .= $render($node[2], array_merge($scope,is_array($item) ? $item : ['value'=>$item]));
                } else {
                    if(strpos($node[1],'asset:') === 0) $value = $this->assetUrl($this->currentTheme,trim(substr($node[1],6)));
                    $html .= htmlspecialchars(is_scalar($value) ? (string)$value : '', ENT_QUOTES, 'UTF-8');
                }
            }
            return $html;
        };
        return $render($nodes,$context);
    }

    private $currentTheme;
    private function lookup(array $scope, $path){
        $value = $scope;
        foreach(explode('.', $path) as $segment){
            if(is_array($value) && array_key_exists($segment, $value)) $value = $value[$segment]; else return '';
        }
        return $value;
    }

    private function imageUrl($path){
        $path = (string)$path;
        if($path === '') return '';
        return preg_match('~^https?://~i', $path) ? $path : base_url(ltrim($path, '/'));
    }

    private function assetUrl($theme, $relative){
        $parts = array_map('rawurlencode', explode('/', ltrim(str_replace('\\','/', (string)$relative), '/')));
        return base_url('storefront/theme_asset/' . (int)($theme->owner_store_id ?? 0) . '/' . (int)($theme->id ?? 0) . '/' . implode('/', $parts));
    }

    private function runtime($theme, array $data, $preview){
        $settings = (array)($data['settings'] ?? []);
        $storeId = (int)($settings['store_id'] ?? 0);
        $currency = (array)($data['store_currency'] ?? []);
        $config = [
            'version'=>1, 'storeId'=>$storeId, 'preview'=>(bool)$preview,
            'cartKey'=>'sf_cart_' . $storeId,
            'cartEnabled'=>!empty($data['cart_enabled']), 'allowBackorder'=>!empty($settings['allow_backorder']),
            'placeOrderUrl'=>base_url('storefront/place_order'), 'quoteUrl'=>base_url('storefront/cart_quote'),
            'verifyUrl'=>base_url('storefront/verify_payment'), 'callbackUrl'=>base_url('storefront/paystack_callback'),
            'homeUrl'=>base_url('store/' . rawurlencode((string)($settings['store_slug'] ?? ''))),
            'csrfName'=>$this->CI->security->get_csrf_token_name(), 'csrfHash'=>$this->CI->security->get_csrf_hash(),
            'currency'=>(string)($currency['code'] ?? 'NGN'), 'currencyRate'=>(float)($data['currency_conversion_rate'] ?? 1),
            'currencySymbol'=>html_entity_decode((string)($currency['symbol'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'requiresLocation'=>count((array)($data['outlets'] ?? [])) > 1,
            'locationSelectionEnabled'=>!empty($data['outlets']),
            'signInUrl'=>base_url('store/'.rawurlencode((string)($settings['store_slug'] ?? '')).'/account'),
            'pricingNoticeEnabled'=>!isset($settings['customer_pricing_notice_enabled']) || !empty($settings['customer_pricing_notice_enabled']),
            'pricingNoticeTitle'=>(string)($settings['customer_pricing_notice_title'] ?? ''),
            'pricingNoticeText'=>(string)($settings['customer_pricing_notice_text'] ?? ''),
            'pricingNoticeLinkText'=>(string)($settings['customer_pricing_notice_link_text'] ?? ''),
        ];
        $json = json_encode($config, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
        $location = ['storeId'=>$storeId, 'preview'=>(bool)$preview, 'connectorVersion'=>1,
            'selectionEnabled'=>!empty($data['outlets']), 'availabilityUrl'=>base_url('storefront/location_availability'),
            'csrfName'=>$config['csrfName'], 'csrfHash'=>$config['csrfHash']];
        $html = '<script>window.MartPointThemeConfig=' . $json . ';window.MPThemeLocationConfig=' . json_encode($location, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) . ';</script>';
        foreach(['storefront_commerce.js','html_theme_connector.js','storefront_location_picker.js'] as $asset){
            $file = dirname(rtrim(APPPATH,'/')).'/theme/js/'.$asset;
            $version = is_file($file) ? (string)filemtime($file) : '1';
            $html .= '<script src="' . htmlspecialchars(base_url('theme/js/' . $asset).'?v='.$version, ENT_QUOTES, 'UTF-8') . '"></script>';
        }
        return $html;
    }
}
