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
        if(isset($templates[$templateKey])){
            $body = $this->renderTemplate($read($templateKey), $context);
        } elseif($view === 'track') {
            // Tracking is a shared MartPoint flow, but uploaded themes still
            // own the page chrome and stylesheet. Render the shared tracking
            // content inside that theme's shell when no custom track template
            // was included in the package.
            $body = $this->CI->load->view('themes/shared/track', $data, true);
            $body = '<style>.mp-track-page{--mp-white:#fff;--mp-border:var(--line,#e9e7eb);--mp-radius:10px;--mp-radius-sm:8px;--mp-dark:var(--ink,#222127);--mp-gray:var(--muted,#79777f);--mp-light-gray:var(--soft,#f7f6f8);--mp-primary:var(--purple,#7356d3);--mp-primary-dark:var(--purple,#7356d3);--mp-success:#16845b}</style>' . $body;
        } else {
            throw new RuntimeException('Theme template is missing: ' . $templateKey);
        }
        $footer = $this->renderTemplate($read('footer'), $context);
        $css = [];
        foreach((array)($manifest['assets']['css'] ?? []) as $asset) $css[] = '<link rel="stylesheet" href="' . htmlspecialchars($this->assetUrl($theme, $asset), ENT_QUOTES, 'UTF-8') . '">';
        $js = [];
        foreach((array)($manifest['assets']['js'] ?? []) as $asset) $js[] = '<script src="' . htmlspecialchars($this->assetUrl($theme, $asset), ENT_QUOTES, 'UTF-8') . '" defer></script>';
        $runtime = $this->runtime($theme, $data, !empty($options['preview']));
        $html = $header . $body . $footer;
        if(($manifest['theme_key'] ?? '') === 'martpoint_gadgets'){
            // Use the merchant's own logo in both brand areas. Older uploaded
            // theme copies hardcoded the MartPoint monogram in the footer and
            // showed it as the header fallback when no logo URL resolved.
            $logo = trim((string)($context['store']['logo'] ?? ''));
            $mark = '~<span class="logomark" aria-hidden="true">M<span>•</span></span>~';
            if($logo !== ''){
                $logoImage = '<img class="mp-gadgets-logo-image" src="' . htmlspecialchars($logo, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars((string)($context['store']['name'] ?? ''), ENT_QUOTES, 'UTF-8') . '">';
                $html = preg_replace($mark, $logoImage, $html);
            } else {
                $html = preg_replace($mark, '', $html);
            }
        }
        if(($manifest['theme_key'] ?? '') === 'martpoint_daily_wellness'){
            // The first Daily Wellness release shipped with a saved static
            // Services/Contact rail. Replace that rail at render time so
            // existing installations receive the category-first menu too.
            $categoryMenu = '<a href="' . htmlspecialchars((string)($context['navigation']['catalogue_url'] ?? ''), ENT_QUOTES, 'UTF-8') . '">Shop all</a>';
            foreach((array)($context['navigation']['categories'] ?? []) as $category){
                $label = htmlspecialchars((string)($category['name'] ?? ''), ENT_QUOTES, 'UTF-8');
                $url = htmlspecialchars((string)($category['url'] ?? ''), ENT_QUOTES, 'UTF-8');
                if($label !== '' && $url !== '') $categoryMenu .= '<a href="' . $url . '">' . $label . '</a>';
            }
            $html = preg_replace_callback('~(<nav\\b[^>]*class="nav"[^>]*>).*?</nav>~is', function($match) use ($categoryMenu){ return $match[1] . $categoryMenu . '</nav>'; }, $html, 1);
            $html = preg_replace_callback('~(<nav\\b(?=[^>]*aria-label="Mobile[^"]*")[^>]*>).*?</nav>~is', function($match) use ($categoryMenu){ return $match[1] . $categoryMenu . '</nav>'; }, $html, 1);
        }
        if(($manifest['theme_key'] ?? '') === 'martpoint_gadgets'){
            // Product-led stores need category links in the primary shop rail.
            // Older uploaded copies can still contain a static Services/Contact
            // menu, so replace that section using the store's live categories.
            $categoryLinks = '';
            foreach((array)($context['navigation']['categories'] ?? []) as $category){
                $label = trim((string)($category['name'] ?? ''));
                $url = trim((string)($category['url'] ?? ''));
                if($label === '' || $url === '') continue;
                $categoryLinks .= '<a class="nav-category" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
            }
            if($categoryLinks !== ''){
                $categoryLinks = '<div class="nav-links">' . $categoryLinks . '</div>';
                $html = preg_replace('~<div\\b(?=[^>]*class="[^"]*\\bnav-links\\b[^"]*")[^>]*>.*?</div>~is', $categoryLinks, $html, 1);
            }
        }
        if(!empty($data['currency_selector_enabled'])){
            $selector=$this->CI->load->view('themes/shared/currency_selector',$data,true);
            if($selector!=='' && stripos($html,'<body')!==false) $html=preg_replace('~(<body\b[^>]*>)~i','$1'.$selector,$html,1);
        }
        $headInjection = implode("\n", $css);
        if($headInjection !== '' && stripos($html, '</head>') !== false) $html = preg_replace('~</head>~i', $headInjection . "\n</head>", $html, 1);
        if(($manifest['theme_key'] ?? '') === 'martpoint_gadgets' && stripos($html, '</head>') !== false){
            // The uploaded 1.2.2 package used a 640px breakpoint. That left
            // narrow phones and portrait tablets in a squeezed two-column
            // layout. Apply the mobile composition through 767px to existing
            // uploaded copies as well as newly uploaded packages.
            $mobileCss = '<style>@media(max-width:767px){.wrap,.header-inner,.nav-inner{width:calc(100% - 32px)}.header-inner{height:auto;padding:13px 0;display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px}.mp-gadgets-header .logo{font-size:17px;gap:6px}.mp-gadgets-header .logo small{font-size:7px;letter-spacing:2px}.mp-gadgets-logo-image{max-width:118px;max-height:42px}.logomark{font-size:42px;width:37px}.search{grid-column:1/-1;padding:11px 12px}.header-actions{gap:12px}.main-nav .nav-inner{height:46px;justify-content:flex-start;overflow-x:auto;gap:18px;white-space:nowrap}.nav-links{display:flex;gap:16px}.nav-deals{display:none}.hero{margin-top:16px;grid-template-columns:1fr;border-radius:10px;min-height:0}.hero-copy{min-width:0;padding:28px 22px 25px}.hero h1{font-size:42px;letter-spacing:-1.8px;margin:18px 0 14px;overflow-wrap:anywhere}.hero-copy p{font-size:12px}.hero-visual{min-height:280px}.hero-product{width:60%;height:78%}.hero-big-type{font-size:75px;top:34px}.orbit{width:270px;height:270px}.orbit-two{width:340px;height:340px}.hero-caption{left:18px;right:18px;bottom:16px}.vertical-caption{right:9px}.benefits{grid-template-columns:1fr 1fr;padding:18px 0;gap:14px 8px}.benefits>div{justify-content:flex-start;border:0;padding:4px}.benefits svg{width:22px;height:22px}.benefits strong{font-size:10px}.benefits small{font-size:8px}.categories{padding-top:36px}.section{padding:38px 0}.section-heading{align-items:flex-start}.section-heading h2{font-size:23px}.category-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.mp-gadgets-category{min-height:112px;padding:14px}.shop{padding-top:36px}.shop-controls{align-items:flex-start;flex-wrap:wrap}.product-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:24px 12px}.product-image{height:auto;aspect-ratio:1/1}.product-meta{padding-top:10px}.product-name{font-size:12px}.product-description{font-size:9px;min-height:42px}.price{font-size:12px}.add{height:30px;width:30px}.feature-banner{display:block;min-height:400px}.feature-copy{position:relative;z-index:1;min-height:400px;padding:30px 22px 210px;background:linear-gradient(180deg,#29282b 0%,#29282b 55%,#29282b88 100%)}.feature-copy h2{font-size:32px;letter-spacing:-1.2px}.feature-banner>img{z-index:0;right:0;bottom:0;width:100%;height:52%;object-fit:cover;opacity:.48}.banner-number{z-index:2;left:18px;right:auto;bottom:14px}.closing{padding-top:35px;padding-bottom:35px;gap:14px}.closing h2{font-size:20px}.mp-gadgets-footer .footer-top{grid-template-columns:1fr 1fr;gap:22px;padding:32px 0}.mp-gadgets-footer .footer-top>div:first-child{grid-column:1/-1}.footer-bottom{gap:10px;flex-wrap:wrap;font-size:8px}.mp-gadgets-page-intro{padding-top:32px}.mp-gadgets-page-intro h1{font-size:32px}.mp-gadgets-product-layout,.mp-gadgets-cart,.mp-gadgets-checkout{grid-template-columns:1fr;gap:18px}.mp-gadgets-product-image{min-height:280px;max-height:400px}.mp-gadgets-product-copy{padding:5px 0 28px}.mp-gadgets-product-copy h1{font-size:32px}.mp-gadgets-cart-items,.mp-gadgets-cart-summary,.mp-gadgets-checkout-aside{padding:17px}.mp-theme-cart-line{grid-template-columns:1fr auto;gap:8px}.mp-theme-cart-line label{grid-column:1/-1;grid-row:2}.mp-theme-cart-line button{grid-column:2;grid-row:1}.mp-gadgets-checkout-aside{position:static}.mp-gadgets-options{grid-template-columns:1fr!important}.mp-gadgets-footer{margin-top:15px}}@media(max-width:540px){.product-image{height:auto;aspect-ratio:1/1}.mp-gadgets-checkout-aside .mp-gadgets-summary-note{font-size:11px}}</style>';
            $html = preg_replace('~</head>~i', $mobileCss . '</head>', $html, 1);
        }
        $scripts = '';
        if($view === 'checkout' && ($manifest['theme_key'] ?? '') === 'martpoint_gadgets'){
            // Apply the delivery/pickup-first flow to already-uploaded copies
            // of the Gadgets theme as well as future uploads.
            $scripts .= <<<'HTML'
<script>
(function(){
  var form=document.querySelector('[data-mp-checkout]');if(!form)return;
  var methodFieldset=Array.prototype.slice.call(form.querySelectorAll('fieldset')).find(function(node){return /delivery or pickup/i.test(node.querySelector('legend')?.textContent||'');});
  var contact=Array.prototype.slice.call(form.querySelectorAll('fieldset')).find(function(node){return /contact details/i.test(node.querySelector('legend')?.textContent||'');});
  if(!methodFieldset||!contact)return;
  var textarea=contact.querySelector('textarea[name="customer_address"]');var addressLabel=textarea&&textarea.closest('label');
  var address=contact.querySelector('textarea[name="customer_address"]');
  var destination=form.querySelector('[data-mp-city-delivery]');
  var addressFieldset=form.querySelector('[data-mp-delivery-address]');
  if(!addressFieldset){addressFieldset=document.createElement('fieldset');addressFieldset.className='mp-gadgets-options';addressFieldset.dataset.mpDeliveryAddress='true';var legend=document.createElement('legend');legend.textContent='Delivery address';addressFieldset.appendChild(legend);}
  if(addressLabel&&addressLabel.parentNode!==addressFieldset)addressFieldset.appendChild(addressLabel);
  var mode=document.createElement('fieldset');mode.className='mp-gadgets-options';mode.dataset.mpFulfilmentChoice='true';var modeLegend=document.createElement('legend');modeLegend.textContent='How would you like your order?';mode.appendChild(modeLegend);
  var buttons=document.createElement('div');buttons.style.cssText='display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px';mode.appendChild(buttons);
  function addMode(kind,label,help){var button=document.createElement('button');button.type='button';button.dataset.mode=kind;button.className='mp-gadgets-option';button.style.cssText='display:block;width:100%;text-align:left;background:#fff;color:inherit;cursor:pointer';button.innerHTML='<strong>'+label+'</strong><small style="display:block;margin-top:5px">'+help+'</small>';buttons.appendChild(button);button.addEventListener('click',function(){choose(kind);});return button;}
  var radios=Array.prototype.slice.call(methodFieldset.querySelectorAll('input[name="shipping_method"]'));
  var pickup=radios.filter(function(input){return /pick[\s-]*up|collect/i.test(input.value||input.closest('label')?.textContent||'');});
  var delivery=radios.filter(function(input){return pickup.indexOf(input)<0;});
  if(delivery.length||destination)addMode('delivery','Deliver','Enter your state, city and street address');
  if(pickup.length)addMode('pickup','Pickup','Choose a collection option');
  if(!buttons.children.length)return;
  methodFieldset.parentNode.insertBefore(mode,methodFieldset);
  var city=destination&&destination.querySelector('select[name="shipping_city"]');
  if(city&&!city.querySelector('option[value="pickup"]')){var pickupOption=document.createElement('option');pickupOption.value='pickup';pickupOption.textContent='Pickup';city.appendChild(pickupOption);}
  if(destination)methodFieldset.parentNode.insertBefore(destination,methodFieldset.nextSibling);
  if(addressFieldset.children.length>1){var after=destination||methodFieldset;after.parentNode.insertBefore(addressFieldset,after.nextSibling);}
  var stateTrigger=destination&&destination.querySelector('[data-mp-state-select] [data-mp-select-trigger]');
  var cityTrigger=destination&&destination.querySelector('[data-mp-city-select] [data-mp-select-trigger]');
  var simpleLocation=null;
  if(!destination){simpleLocation=document.createElement('div');simpleLocation.style.cssText='display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px';[['State / region','address-level1'],['City / town','address-level2']].forEach(function(pair){var label=document.createElement('label');label.textContent=pair[0];var input=document.createElement('input');input.type='text';input.autocomplete=pair[1];input.className='mp-gadgets-select-search';input.placeholder='Enter '+pair[0].toLowerCase();label.appendChild(input);simpleLocation.appendChild(label);});if(addressFieldset)addressFieldset.insertBefore(simpleLocation,addressLabel||null);}
  var originalAddress=address?address.value:'';
  function choose(kind){
    form.dataset.mpFulfilment=kind;
    Array.prototype.forEach.call(buttons.children,function(button){button.setAttribute('aria-pressed',button.dataset.mode===kind?'true':'false');button.style.borderColor=button.dataset.mode===kind?'#0b8078':'';});
    var isDelivery=kind==='delivery';methodFieldset.hidden=false;if(destination)destination.hidden=!isDelivery;if(addressFieldset)addressFieldset.hidden=false;
    if(address){address.required=isDelivery;addressLabel.querySelector('span')?.remove();addressLabel.firstChild.textContent=isDelivery?'Street address':'Address (optional)';address.placeholder=isDelivery?'House number, street and nearby landmark':'Optional address for customer details';if(!isDelivery&&originalAddress)address.value=originalAddress;}
    radios.forEach(function(input){var active=(isDelivery?delivery:pickup).indexOf(input)>=0;var label=input.closest('label');if(label)label.hidden=!active;input.required=false;if(!active)input.checked=false;});
    var activeRadios=isDelivery?delivery:pickup;
    if(activeRadios.length){activeRadios[0].checked=true;activeRadios[0].required=true;}
    if(city){city.required=false;city.disabled=!isDelivery;city.value=isDelivery?'':'pickup';}
    if(simpleLocation)simpleLocation.hidden=!isDelivery;
    methodFieldset.querySelectorAll('input[name="shipping_method"]').forEach(function(input){input.required=false;});
    if(activeRadios.length)activeRadios[0].required=true;
  }
  methodFieldset.hidden=true;if(destination)destination.hidden=true;if(addressFieldset)addressFieldset.hidden=true;
  if(address)address.required=false;
  form.addEventListener('submit',function(event){
    if(!form.dataset.mpFulfilment){event.preventDefault();mode.scrollIntoView({behavior:'smooth',block:'center'});buttons.querySelector('button')?.focus();return;}
    if(form.dataset.mpFulfilment==='delivery'){
      var state=(stateTrigger?.textContent||simpleLocation?.querySelectorAll('input')[0]?.value||'').trim(),cityName=(cityTrigger?.textContent||simpleLocation?.querySelectorAll('input')[1]?.value||'').trim();
      if(destination&&city&&!city.value){event.preventDefault();event.stopPropagation();destination.querySelector('[data-mp-select-trigger]')?.focus();return;}
      if(address){var street=address.value.trim();address.value=[street,cityName,state].filter(function(part){return part&&!/^select/i.test(part);}).join(', ');}
    }
  },true);
})();
</script>
HTML;
        }
        $scripts = $runtime . implode("\n", $js) . $scripts;
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
        // Keep the shared theme navigation useful for product-led stores
        // without letting a long category list overwhelm the desktop or mobile menu.
        $out['navigation']['categories'] = array_slice($out['categories'], 0, 4);
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
        $feeSymbol = trim(html_entity_decode((string)($currency['symbol'] ?? $currency['currency_symbol'] ?? $currency['code'] ?? ''), ENT_QUOTES, 'UTF-8'));
        $out['checkout']['currency_symbol'] = $feeSymbol;
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
                    $city = trim((string)($row['city'] ?? ''));
                    $state = trim((string)($row['state'] ?? ''));
                    $countryName = trim((string)($row['country'] ?? ''));
                    $row['value'] = $city.'|'.$state.'|'.$countryName;
                    $row['state_key'] = $state !== '' ? 'state:'.mb_strtolower($state).'|'.mb_strtolower($countryName) : 'country:'.mb_strtolower($countryName);
                    $row['state_label'] = $state !== '' ? $state : ($countryName !== '' ? 'All states in '.$countryName : 'Other areas');
                    $row['city_label'] = $city === '*' ? 'Other areas' : ($city !== '' ? $city : ($state !== '' ? 'Any city in '.$state : 'All delivery areas'));
                    $row['label'] = implode(', ',array_filter([$city === '*' ? 'Other areas' : $city,$state,$countryName]));
                    $row['fee_display'] = !empty($row['quote']) ? 'Fee confirmed by the store' : trim($feeSymbol.' '.number_format((float)($row['fee'] ?? 0), 2));
                    $row['fee'] = (float)($row['fee'] ?? 0);
                    $row['min_order'] = (float)($row['min_order'] ?? 0);
                    $row['free_over'] = (float)($row['free_over'] ?? 0);
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
        if (array_key_exists('effective_price', $r)) {
            $price = (float)$r['effective_price'];
        } else {
            $onlinePrice = (float)($r['online_price'] ?? 0);
            $retailPrice = (float)($r['mrp'] ?? 0);
            $storeId = (int)($r['store_id'] ?? get_current_store_id());
            $separateRetailPrice = function_exists('mp_feature_enabled_for_store')
                && mp_feature_enabled_for_store('auto_retail_markup', $storeId);
            $price = $onlinePrice > 0 ? $onlinePrice : ($retailPrice > 0 ? $retailPrice
                : ($separateRetailPrice ? 0 : (float)($r['sales_price'] ?? $r['price'] ?? 0)));
        }
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
