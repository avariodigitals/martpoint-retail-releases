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
        $templateKey = $map[$view] ?? $view;
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
        // Paystack is an existing backend capability. Keep its popup and
        // server-side verification available to HTML themes without allowing
        // the package itself to load arbitrary payment code.
        $runtime = str_replace('w.MartPointTheme={addToCart:add,cart:read,refresh:sync}', 'w.MartPointTheme={addToCart:add,cart:read,refresh:sync,storeId:C.storeId}', $runtime);
        $html = $header . $body . $footer;
        $headInjection = implode("\n", $css);
        if($headInjection !== '' && stripos($html, '</head>') !== false) $html = preg_replace('~</head>~i', $headInjection . "\n</head>", $html, 1);
        $paystack = '';
        if($templateKey === 'checkout' && empty($options['preview'])){
            $verifyUrl = json_encode(base_url('storefront/verify_payment'));
            $callbackUrl = json_encode(base_url('storefront/paystack_callback'));
            $paystack = '<script src="https://js.paystack.co/v1/inline.js"></script><script>(function(w,d){var verify=' . $verifyUrl . ',callback=' . $callbackUrl . ';d.addEventListener("submit",function(e){var f=e.target.closest("[data-mp-checkout]");if(!f)return;var pm=new FormData(f).get("payment_method");if(pm!=="paystack")return;e.preventDefault();e.stopImmediatePropagation();var fd=new FormData(f);fd.append("store_id",w.MartPointTheme.storeId||0);fd.append("cart",JSON.stringify(w.MartPointTheme.cart()));fetch(f.action||"' . base_url('storefront/place_order') . '",{method:"POST",body:fd,credentials:"same-origin"}).then(function(r){return r.json();}).then(function(r){if(!r.status){alert(r.message||"Could not place the order.");return;}if(!r.payment_required||!r.public_key){if(r.redirect_url)w.location.href=r.redirect_url;else alert(r.message||"Order placed successfully.");return;}if(!w.PaystackPop){alert("The payment provider could not be loaded. Please try again.");return;}var s=r.paystack_split||{},h=w.PaystackPop.setup({key:r.public_key,email:r.email,amount:r.amount_kobo,currency:r.currency||"NGN",ref:r.reference,subaccount:s.subaccount,transaction_charge:s.transaction_charge,bearer:s.bearer,callback:function(p){fetch(verify+"?reference="+encodeURIComponent(p.reference),{credentials:"same-origin"}).then(function(x){return x.json();}).then(function(v){if(v.status){try{localStorage.removeItem("sf_cart_"+(w.MartPointTheme.storeId||0));}catch(ignore){}w.location.href=v.redirect_url;}else{alert(v.message||"Payment could not be verified.");}}).catch(function(){w.location.href=callback+"?reference="+encodeURIComponent(p.reference);});},onClose:function(){}});h.openIframe();}).catch(function(){alert("Could not place the order. Please try again.");});},true);})(window,document);</script>';
        }
        $scripts = $paystack . $runtime . implode("\n", $js);
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
        $product = function($item) use ($base, $money){ return $this->itemContext($item, $base, $money); };
        $lists = ['products','all_products','featured_products','best_sellers','new_arrivals','related_products','product_variants'];
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
            'settings' => (array)($manifest['settings'] ?? []),
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
        if(!empty($data['related_products'])) $out['related_products'] = array_map($product, (array)$data['related_products']);
        $out['search'] = ['term'=>(string)($data['search'] ?? ''), 'url'=>$base . '/products'];
        $out['catalogue'] = ['page'=>(int)($data['page'] ?? 1), 'total'=>(int)($data['total'] ?? count($out['products'])), 'total_pages'=>(int)($data['total_pages'] ?? 1)];
        return $out;
    }

    private function itemContext($item, $base, $money){
        $r = $this->plain($item);
        $id = (int)($r['id'] ?? 0);
        $name = (string)($r['item_name'] ?? $r['service_name'] ?? $r['name'] ?? 'Product');
        $price = (float)($r['effective_price'] ?? $r['online_price'] ?? $r['sales_price'] ?? $r['price'] ?? 0);
        $image = $this->imageUrl($r['item_image'] ?? $r['service_image'] ?? $r['image'] ?? '');
        return [
            'id'=>$id, 'name'=>$name, 'description'=>strip_tags((string)($r['description'] ?? '')), 'category'=>(string)($r['category_name'] ?? ''),
            'price'=>$money($price), 'price_raw'=>$price, 'original_price'=>$money((float)($r['original_price'] ?? $price)), 'stock'=>(float)($r['stock'] ?? 0),
            'in_stock'=>((float)($r['stock'] ?? 0) > 0 || !empty($r['allow_backorder'])), 'image'=>$image,
            'url'=>$base . '/product/' . $id, 'type'=>(string)($r['item_type'] ?? 'product'),
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
        $self = $this;
        $render = function($source, $scope) use (&$render, $self){
            $source = preg_replace_callback('/\{\{#if\s+([a-zA-Z0-9_.-]+)\}\}(.*?)\{\{\/if\}\}/s', function($m) use (&$render,$scope){
                $parts = explode('{{else}}', $m[2], 2); $value = $thisValue = $this->lookup($scope, $m[1]);
                return $value ? $render($parts[0], $scope) : (isset($parts[1]) ? $render($parts[1], $scope) : '');
            }, $source);
            $source = preg_replace_callback('/\{\{#([a-zA-Z0-9_.-]+)\}\}(.*?)\{\{\/\1\}\}/s', function($m) use (&$render,$scope){
                $items = $this->lookup($scope, $m[1]);
                if(!is_array($items)) return '';
                $html = '';
                foreach($items as $item){
                    $child = is_array($item) ? array_merge($scope, $item) : array_merge($scope, ['value'=>$item]);
                    $html .= $render($m[2], $child);
                }
                return $html;
            }, $source);
            $source = preg_replace_callback('/\{\{\s*asset:([^}]+)\s*\}\}/', function($m) use ($self){ return htmlspecialchars($self->assetUrl($self->currentTheme, trim($m[1])), ENT_QUOTES, 'UTF-8'); }, $source);
            return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.-]+)\s*\}\}/', function($m) use ($scope){
                return htmlspecialchars((string)$this->lookup($scope, $m[1]), ENT_QUOTES, 'UTF-8');
            }, $source);
        };
        return $render($template, $context);
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
        $runtimeSettings = (array)($data['settings'] ?? []);
        $runtimeStore = (array)($data['store'] ?? []);
        $storeId = (int)($runtimeSettings['store_id'] ?? $runtimeStore['id'] ?? 0);
        $config = [
            'storeId'=>$storeId,
            'preview'=>(bool)$preview,
            'cartKey'=>'sf_cart_' . $storeId,
            'placeOrderUrl'=>base_url('storefront/place_order'),
            'homeUrl'=>base_url('store/' . rawurlencode((string)($data['settings']->store_slug ?? $data['settings']['store_slug'] ?? ''))),
        ];
        $json = json_encode($config, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
        return '<script>(function(w,d){var C=' . $json . ';function esc(v){return String(v==null?"":v).replace(/[&<>"\']/g,function(c){return {"&":"&amp;","<":"&lt;",">":"&gt;","\\"":"&quot;","\'":"&#039;"}[c];});}function read(){try{return JSON.parse(localStorage.getItem(C.cartKey)||"[]");}catch(e){return [];}}function write(items){try{localStorage.setItem(C.cartKey,JSON.stringify(items));}catch(e){}}function sync(){var items=read(),count=items.reduce(function(n,i){return n+(Number(i.qty)||0);},0);d.querySelectorAll("[data-mp-cart-count]").forEach(function(x){x.textContent=count;});var box=d.querySelector("[data-mp-cart]");if(!box)return;if(!items.length){box.innerHTML="<p>Your cart is empty.</p><a href=\""+C.homeUrl+"/products\">Browse products</a>";return;}var total=0;box.innerHTML=items.map(function(i){var line=(Number(i.price)||0)*(Number(i.qty)||0);total+=line;return "<div class=\"mp-theme-cart-line\"><span>"+esc(i.name)+" × "+i.qty+"</span><strong>"+line.toFixed(2)+"</strong></div>";}).join("");d.querySelectorAll("[data-mp-cart-total]").forEach(function(x){x.textContent=total.toFixed(2);});}function add(item){if(C.preview){alert("Cart actions are disabled in preview.");return;}var items=read(),key=String(item.type||"product")+"_"+item.id,found=items.find(function(i){return i.key===key;});if(found)found.qty=Math.min((Number(found.qty)||0)+Math.max(1,Number(item.qty)||1),Number(found.stock)||999999);else items.push({key:key,id:Number(item.id),type:item.type||"product",name:item.name||"Product",price:Number(item.price)||0,image:item.image||"",qty:Math.max(1,Number(item.qty)||1),stock:Number(item.stock)||999999});write(items);sync();}w.MartPointTheme={addToCart:add,cart:read,refresh:sync};w.mpThemeAddToCart=add;d.addEventListener("click",function(e){var b=e.target.closest("[data-mp-add-to-cart]");if(b){e.preventDefault();add({id:b.dataset.productId,type:b.dataset.productType||"product",name:b.dataset.productName,price:b.dataset.productPrice,image:b.dataset.productImage,stock:b.dataset.productStock,qty:b.dataset.productQty||1});}});d.addEventListener("submit",function(e){var f=e.target.closest("[data-mp-checkout]");if(!f)return;e.preventDefault();if(C.preview){alert("Checkout is disabled in preview.");return;}var fd=new FormData(f);fd.append("store_id",C.storeId);fd.append("cart",JSON.stringify(read()));fetch(f.action||C.placeOrderUrl,{method:"POST",body:fd,credentials:"same-origin"}).then(function(r){return r.json();}).then(function(r){if(r.status&&r.redirect_url)location.href=r.redirect_url;else if(r.status&&r.payment_required)alert("Payment is ready. Please continue in the payment provider.");else alert(r.message||"Could not place the order.");}).catch(function(){alert("Could not place the order. Please try again.");});});sync();})(window,document);</script>';
    }
}
