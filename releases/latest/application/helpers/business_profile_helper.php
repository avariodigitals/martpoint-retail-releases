<?php
/**
 * MartPoint Industry Adaptation Engine
 * Business Profile, Feature Flags, Label Resolver, and Preset Configurations
 * Phase 2 Foundation — Configuration-driven industry adaptation.
 */

/* ================================================================
   BUSINESS TYPE & MODEL CONSTANTS
   ================================================================ */
if (!function_exists('mp_get_business_types')) {
    function mp_get_business_types() {
        return [
            'general_retail'       => 'General Retail',
            'supermarket'           => 'Supermarket',
            'mini_mart'             => 'Mini Mart',
            'pharmacy'              => 'Pharmacy',
            'restaurant'            => 'Restaurant',
            'electronics'           => 'Electronics',
            'fashion'               => 'Fashion',
            'beauty_cosmetics'      => 'Beauty & Cosmetics',
            'beauty_spa'            => 'Beauty Spa',
            'salon_barbershop'      => 'Salon & Barbershop',
            'makeup_artist'         => 'Makeup Artist',
            'laundry'               => 'Laundry',
            'bakery_cake_studio'    => 'Bakery & Cake Studio',
            'bookshop'              => 'Bookshop',
            'building_materials'    => 'Building Materials',
            'furniture'             => 'Furniture',
            'phone_accessories'     => 'Phone Accessories',
            'service_business'      => 'Service Business',
        ];
    }
}

if (!function_exists('mp_get_business_models')) {
    function mp_get_business_models() {
        return [
            'product_based'        => 'Product Based',
            'service_based'        => 'Service Based',
            'product_and_service'  => 'Product & Service',
        ];
    }
}

if (!function_exists('mp_get_feature_flags')) {
    function mp_get_feature_flags() {
        return [
            'accounts'                  => 'Accounts Module',
            'warehouse'                 => 'Warehouse / Stock Transfer Module',
            'multi_store'               => 'Multi-Store / SaaS Module',
            'online_store'              => 'Online Store',
            'qr_ordering'               => 'QR Ordering',
            'payplan'                   => 'PayPlan / Installments',
            'loyalty'                   => 'Loyalty & Rewards',
            'gift_cards'                => 'Gift Cards',
            'store_credit'              => 'Store Credit',
            'appointments'              => 'Appointments',
            'service_workflow'          => 'Service Workflow',
            'custom_orders'             => 'Custom Orders',
            'packages'                  => 'Packages',
            'bundles'                   => 'Bundles',
            'memberships'               => 'Memberships',
            'multi_unit_inventory'      => 'Multi-Unit Inventory',
            'batch_tracking'            => 'Batch Tracking',
            'expiry_tracking'           => 'Expiry Tracking',
            'serial_number_tracking'    => 'Serial Number Tracking',
            'imei_tracking'             => 'IMEI Tracking',
            'warranty_tracking'         => 'Warranty Tracking',
            'kitchen_workflow'          => 'Kitchen Workflow',
            'table_management'          => 'Table Management',
            'laundry_workflow'          => 'Laundry Workflow',
            'treatment_notes'           => 'Treatment Notes',
            'staff_assignment'          => 'Staff Assignment',
            'staff_commission'          => 'Staff Commission',
            'delivery_scheduling'       => 'Delivery Scheduling',
            'production_workflow'       => 'Production Workflow',
            'recipe_tracking'           => 'Recipe Tracking',
            'customer_notes'            => 'Customer Notes',
            'price_catalogue'           => 'Price Catalogue',
            'public_catalogue'          => 'Public Catalogue',
            'manager_approvals'         => 'Manager Approvals',
        ];
    }
}

if (!function_exists('mp_get_label_defaults')) {
    function mp_get_label_defaults() {
        return [
            'warehouse'         => 'Warehouse',
            'branch'            => 'Branch',
            'item'              => 'Item',
            'product'           => 'Product',
            'service'           => 'Service',
            'service_order'     => 'Service Order',
            'customer'          => 'Customer',
            'client'            => 'Client',
            'supplier'          => 'Supplier',
            'vendor'            => 'Vendor',
            'sales'             => 'Sales',
            'purchase'          => 'Purchase',
            'expense'           => 'Expense',
            'pos'               => 'POS',
            'category'          => 'Category',
            'brand'             => 'Brand',
            'stock'             => 'Stock',
            'stock_transfer'    => 'Stock Transfer',
            'batch'             => 'Batch',
            'imei'              => 'IMEI',
            'serial'            => 'Serial Number',
            'expiry'            => 'Expiry Date',
            'mfg'               => 'Manufacturing Date',
            'table'             => 'Table',
            'room'              => 'Room',
            'appointment'       => 'Appointment',
            'booking'           => 'Booking',
            'treatment'         => 'Treatment',
            'recipe'            => 'Recipe',
            'production'        => 'Production',
            'delivery'          => 'Delivery',
            'order'             => 'Order',
            'custom_order'      => 'Custom Order',
            'catalogue'         => 'Catalogue',
            'membership'        => 'Membership',
            'package'           => 'Package',
            'bundle'            => 'Bundle',
            'staff'             => 'Staff',
            'employee'          => 'Employee',
            'commission'        => 'Commission',
            'deposit'           => 'Deposit',
            'quote'             => 'Quote',
            'prescription'      => 'Prescription',
            'note'              => 'Note',
            'task'              => 'Task',
        ];
    }
}

if (!function_exists('mp_get_business_presets')) {
    function mp_get_business_presets() {
        return [
            'general_retail' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','gift_cards','store_credit','multi_unit_inventory','batch_tracking','public_catalogue'],
                'theme_key'=>'general_retail','dashboard_template'=>'general_retail','workflow_template'=>'retail_standard','labels'=>[],
            ],
            'supermarket' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','gift_cards','store_credit','multi_unit_inventory','batch_tracking','expiry_tracking','delivery_scheduling','public_catalogue'],
                'theme_key'=>'fresh_market','dashboard_template'=>'supermarket','workflow_template'=>'retail_standard',
                'labels'=>['warehouse'=>'Branch','item'=>'Product'],
            ],
            'mini_mart' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','multi_unit_inventory','batch_tracking','expiry_tracking','public_catalogue'],
                'theme_key'=>'general_retail','dashboard_template'=>'mini_mart','workflow_template'=>'retail_standard',
                'labels'=>['warehouse'=>'Branch','item'=>'Product'],
            ],
            'pharmacy' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','loyalty','multi_unit_inventory','batch_tracking','expiry_tracking','serial_number_tracking','manager_approvals','price_catalogue','public_catalogue'],
                'theme_key'=>'healthcare_pro','dashboard_template'=>'pharmacy','workflow_template'=>'pharmacy_standard',
                'labels'=>['item'=>'Medicine','batch'=>'Batch','expiry'=>'Expiry Date','customer'=>'Patient'],
            ],
            'restaurant' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','table_management','kitchen_workflow','loyalty','delivery_scheduling','manager_approvals','recipe_tracking','staff_commission'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                'labels'=>['item'=>'Menu Item','service'=>'Dining Service','table'=>'Table','order'=>'Order','pos'=>'Counter'],
            ],
            'electronics' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','gift_cards','store_credit','serial_number_tracking','imei_tracking','warranty_tracking','manager_approvals','price_catalogue','public_catalogue'],
                'theme_key'=>'tech_hub','dashboard_template'=>'electronics','workflow_template'=>'electronics_standard',
                'labels'=>['item'=>'Product','serial'=>'Serial Number','imei'=>'IMEI'],
            ],
            'fashion' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','gift_cards','store_credit','multi_unit_inventory','public_catalogue'],
                'theme_key'=>'urban_fashion','dashboard_template'=>'fashion','workflow_template'=>'retail_standard',
                'labels'=>['item'=>'Item','category'=>'Collection'],
            ],
            'beauty_cosmetics' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','appointments','service_workflow','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','treatment_notes','price_catalogue','public_catalogue'],
                'theme_key'=>'beauty_luxe','dashboard_template'=>'beauty','workflow_template'=>'beauty_standard',
                'labels'=>['service'=>'Treatment','service_order'=>'Treatment Booking','customer'=>'Client','staff'=>'Therapist'],
            ],
            'beauty_spa' => [
                'business_model'=>'service_based',
                'features'=>['accounts','online_store','appointments','service_workflow','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','treatment_notes','packages','memberships','public_catalogue'],
                'theme_key'=>'beauty_luxe','dashboard_template'=>'beauty_spa','workflow_template'=>'beauty_standard',
                'labels'=>['service'=>'Treatment','service_order'=>'Spa Booking','customer'=>'Guest','staff'=>'Therapist','package'=>'Spa Package'],
            ],
            'salon_barbershop' => [
                'business_model'=>'service_based',
                'features'=>['accounts','online_store','appointments','service_workflow','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','packages','public_catalogue'],
                'theme_key'=>'beauty_luxe','dashboard_template'=>'salon','workflow_template'=>'salon_standard',
                'labels'=>['service'=>'Service','service_order'=>'Booking','customer'=>'Client','staff'=>'Stylist','package'=>'Service Package'],
            ],
            'makeup_artist' => [
                'business_model'=>'service_based',
                'features'=>['accounts','online_store','appointments','service_workflow','custom_orders','loyalty','gift_cards','staff_assignment','staff_commission','treatment_notes','public_catalogue'],
                'theme_key'=>'beauty_luxe','dashboard_template'=>'makeup_artist','workflow_template'=>'makeup_standard',
                'labels'=>['service'=>'Service','service_order'=>'Booking','customer'=>'Client','staff'=>'Artist','custom_order'=>'Custom Booking'],
            ],
            'laundry' => [
                'business_model'=>'service_based',
                'features'=>['accounts','online_store','qr_ordering','service_workflow','laundry_workflow','loyalty','delivery_scheduling','staff_assignment','staff_commission','public_catalogue'],
                'theme_key'=>'service_pro','dashboard_template'=>'laundry','workflow_template'=>'laundry_standard',
                'labels'=>['service'=>'Service','service_order'=>'Laundry Order','customer'=>'Customer','delivery'=>'Pickup/Delivery'],
            ],
            'bakery_cake_studio' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','warehouse','online_store','qr_ordering','custom_orders','packages','production_workflow','recipe_tracking','delivery_scheduling','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','public_catalogue'],
                'theme_key'=>'food_express','dashboard_template'=>'bakery','workflow_template'=>'bakery_standard',
                'labels'=>['service'=>'Custom Cake','service_order'=>'Cake Order','customer'=>'Client','product'=>'Baked Item','recipe'=>'Recipe','production'=>'Production'],
            ],
            'bookshop' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','loyalty','gift_cards','store_credit','multi_unit_inventory','public_catalogue'],
                'theme_key'=>'general_retail','dashboard_template'=>'bookshop','workflow_template'=>'retail_standard',
                'labels'=>['item'=>'Book','category'=>'Genre'],
            ],
            'building_materials' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','multi_unit_inventory','batch_tracking','delivery_scheduling','manager_approvals','price_catalogue','public_catalogue'],
                'theme_key'=>'general_retail','dashboard_template'=>'building_materials','workflow_template'=>'wholesale_standard',
                'labels'=>['item'=>'Material','warehouse'=>'Depot','customer'=>'Client'],
            ],
            'furniture' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','custom_orders','delivery_scheduling','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','public_catalogue'],
                'theme_key'=>'general_retail','dashboard_template'=>'furniture','workflow_template'=>'furniture_standard',
                'labels'=>['item'=>'Furniture','service'=>'Custom Design','service_order'=>'Design Order','customer'=>'Client','delivery'=>'Delivery'],
            ],
            'phone_accessories' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','gift_cards','store_credit','imei_tracking','warranty_tracking','public_catalogue'],
                'theme_key'=>'tech_hub','dashboard_template'=>'phone_accessories','workflow_template'=>'electronics_standard',
                'labels'=>['item'=>'Accessory','imei'=>'IMEI','serial'=>'Serial'],
            ],
            'service_business' => [
                'business_model'=>'service_based',
                'features'=>['accounts','online_store','appointments','service_workflow','custom_orders','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','packages','memberships','public_catalogue'],
                'theme_key'=>'service_pro','dashboard_template'=>'service_business','workflow_template'=>'service_standard',
                'labels'=>['service'=>'Service','service_order'=>'Job','customer'=>'Client','staff'=>'Team Member'],
            ],
        ];
    }
}

if (!function_exists('mp_feature_flag_raw')) {
    function mp_feature_flag_raw($flag_key) {
        $CI =& get_instance();
        $store_id = get_current_store_id();
        if (!$CI->db->field_exists('feature_flags_json', 'db_store')) {
            return null;
        }
        $q = $CI->db->select('feature_flags_json')
            ->where('id',$store_id)->get('db_store');
        if (!$q || !method_exists($q, 'row')) {
            return null;
        }
        $store = $q->row();
        if ($store && !empty($store->feature_flags_json)) {
            $flags = json_decode($store->feature_flags_json,true);
            if (is_array($flags) && array_key_exists($flag_key,$flags)) {
                return filter_var($flags[$flag_key],FILTER_VALIDATE_BOOLEAN);
            }
        }
        return null;
    }
}

if (!function_exists('mp_feature_enabled')) {
    function mp_feature_enabled($flag_key) {
        $raw = mp_feature_flag_raw($flag_key);
        if ($raw !== null) {
            return $raw;
        }
        // Backward compat: payplan used to be called flexpay
        if ($flag_key === 'payplan') {
            $legacy = mp_feature_flag_raw('flexpay');
            if ($legacy !== null) { return $legacy; }
        }
        switch ($flag_key) {
            case 'service_workflow': case 'appointments': return service_module();
            case 'accounts': return accounts_module();
            case 'warehouse': case 'multi_unit_inventory': return warehouse_module();
            case 'online_store': case 'qr_ordering': case 'loyalty': case 'gift_cards': case 'store_credit': return true;
            // Inventory tracking flags - default to module availability
            case 'serial_number_tracking': return warehouse_module();
            case 'imei_tracking': return warehouse_module();
            case 'warranty_tracking': return warehouse_module();
            case 'batch_tracking': return warehouse_module();
            case 'expiry_tracking': return warehouse_module();
            // Service flags - default to service module
            case 'treatment_notes': return service_module();
            case 'staff_assignment': return service_module();
            case 'staff_commission': return service_module();
            case 'custom_orders': return service_module();
            case 'packages': return true;
            case 'memberships': return true;
            // Workflow flags - currently no modules, so false
            case 'kitchen_workflow': return false;
            case 'laundry_workflow': return false;
            case 'production_workflow': return false;
            case 'recipe_tracking': return false;
            // Sales & storefront
            case 'price_catalogue': return true;
            case 'public_catalogue': return true;
            case 'table_management': return service_module();
            case 'delivery_scheduling': return true;
            case 'customer_notes': return true;
            case 'manager_approvals': return true;
            case 'payplan': return true;
            case 'bundles': return true;
            default: return false;
        }
    }
}

if (!function_exists('mp_get_store_profile')) {
    function mp_get_store_profile($store_id=null) {
        $CI =& get_instance();
        if (empty($store_id)) $store_id = get_current_store_id();
        $columns = ['industry_type','business_model','feature_flags_json','workflow_template_key','dashboard_template_key','storefront_theme_key','label_overrides_json','industry_settings_json'];
        $available = [];
        foreach ($columns as $col) {
            if ($CI->db->field_exists($col, 'db_store')) {
                $available[] = $col;
            }
        }
        if (empty($available)) {
            return mp_get_default_profile();
        }
        $q = $CI->db
            ->select(implode(',', $available))
            ->where('id',$store_id)->get('db_store');
        if (!$q || !method_exists($q, 'row')) {
            return mp_get_default_profile();
        }
        $store = $q->row();
        if (!$store) return mp_get_default_profile();
        $industry = (!empty($store->industry_type)) ? $store->industry_type : 'general_retail';
        $presets = mp_get_business_presets();
        $preset = isset($presets[$industry]) ? $presets[$industry] : $presets['general_retail'];
        $profile = [
            'industry_type'=>$industry,
            'business_model'=>(!empty($store->business_model))?$store->business_model:$preset['business_model'],
            'features'=>$preset['features'],
            'theme_key'=>(!empty($store->storefront_theme_key))?$store->storefront_theme_key:$preset['theme_key'],
            'dashboard_template'=>(!empty($store->dashboard_template_key))?$store->dashboard_template_key:$preset['dashboard_template'],
            'workflow_template'=>(!empty($store->workflow_template_key))?$store->workflow_template_key:$preset['workflow_template'],
            'labels'=>$preset['labels'],
        ];
        if (!empty($store->feature_flags_json)) {
            $store_flags = json_decode($store->feature_flags_json,true);
            if (is_array($store_flags)) {
                foreach ($store_flags as $k=>$v) {
                    $enabled = filter_var($v,FILTER_VALIDATE_BOOLEAN);
                    if ($enabled && !in_array($k,$profile['features'])) { $profile['features'][]=$k; }
                    elseif (!$enabled && in_array($k,$profile['features'])) {
                        $profile['features']=array_values(array_diff($profile['features'],[$k]));
                    }
                }
            }
        }
        if (!empty($store->label_overrides_json)) {
            $store_labels = json_decode($store->label_overrides_json,true);
            if (is_array($store_labels)) $profile['labels']=array_merge($profile['labels'],$store_labels);
        }
        if (!empty($store->industry_settings_json)) {
            $settings = json_decode($store->industry_settings_json,true);
            if (is_array($settings)) $profile['settings']=$settings;
        }
        return $profile;
    }
}

if (!function_exists('mp_get_default_profile')) {
    function mp_get_default_profile() {
        return [
            'industry_type'=>'general_retail','business_model'=>'product_based',
            'features'=>['online_store','qr_ordering','loyalty','gift_cards','store_credit','multi_unit_inventory','batch_tracking','public_catalogue'],
            'theme_key'=>'general_retail','dashboard_template'=>'general_retail',
            'workflow_template'=>'retail_standard','labels'=>[],'settings'=>[],
        ];
    }
}

if (!function_exists('mp_label')) {
    function mp_label($key,$fallback=null) {
        $CI =& get_instance();
        $store_id = get_current_store_id();
        if ($CI->db->field_exists('label_overrides_json', 'db_store')) {
            $q = $CI->db->select('label_overrides_json')->where('id',$store_id)->get('db_store');
            if ($q && method_exists($q, 'row')) {
                $store = $q->row();
                if ($store && !empty($store->label_overrides_json)) {
                    $overrides = json_decode($store->label_overrides_json,true);
                    if (is_array($overrides) && isset($overrides[$key]) && $overrides[$key]!=='') return $overrides[$key];
                }
            }
        }
        $profile = mp_get_store_profile($store_id);
        if (isset($profile['labels'][$key]) && $profile['labels'][$key]!=='') return $profile['labels'][$key];
        $defaults = mp_get_label_defaults();
        if (isset($defaults[$key])) return $defaults[$key];
        return (!empty($fallback))?$fallback:ucwords(str_replace('_',' ',$key));
    }
}

if (!function_exists('mp_get_dashboard_widgets')) {
    function mp_get_dashboard_widgets() {
        return [
            'sales_summary'=>['title'=>'Sales Summary','icon'=>'fa-shopping-cart','industries'=>'*','features'=>[]],
            'purchase_summary'=>['title'=>'Purchase Summary','icon'=>'fa-cart-arrow-down','industries'=>'*','features'=>[]],
            'expense_summary'=>['title'=>'Expense Summary','icon'=>'fa-money','industries'=>'*','features'=>[]],
            'stock_alert'=>['title'=>'Low Stock Alert','icon'=>'fa-bell','industries'=>'*','features'=>[]],
            'top_selling_items'=>['title'=>'Top Selling Items','icon'=>'fa-trophy','industries'=>'*','features'=>[]],
            'recent_transactions'=>['title'=>'Recent Transactions','icon'=>'fa-list','industries'=>'*','features'=>[]],
            'near_expiry'=>['title'=>'Near Expiry','icon'=>'fa-calendar-times-o','industries'=>['pharmacy','supermarket','mini_mart','bakery_cake_studio'],'features'=>['expiry_tracking']],
            'expired_items'=>['title'=>'Expired Items','icon'=>'fa-ban','industries'=>['pharmacy','supermarket','mini_mart','bakery_cake_studio'],'features'=>['expiry_tracking']],
            'low_stock_medicines'=>['title'=>'Low Stock Medicines','icon'=>'fa-medkit','industries'=>['pharmacy'],'features'=>[]],
            'pending_laundry'=>['title'=>'Pending Laundry','icon'=>'fa-refresh','industries'=>['laundry'],'features'=>['laundry_workflow']],
            'ready_for_pickup'=>['title'=>'Ready for Pickup','icon'=>'fa-check-circle','industries'=>['laundry'],'features'=>['laundry_workflow']],
            'overdue_pickups'=>['title'=>'Overdue Pickups','icon'=>'fa-clock-o','industries'=>['laundry'],'features'=>['laundry_workflow']],
            'upcoming_events'=>['title'=>'Upcoming Events','icon'=>'fa-calendar','industries'=>['bakery_cake_studio','makeup_artist','furniture'],'features'=>['custom_orders']],
            'production_queue'=>['title'=>'Production Queue','icon'=>'fa-industry','industries'=>['bakery_cake_studio','restaurant'],'features'=>['production_workflow']],
            'deposit_balance'=>['title'=>'Deposit Balance','icon'=>'fa-money','industries'=>['bakery_cake_studio','furniture','makeup_artist'],'features'=>['custom_orders']],
            'pending_appointments'=>['title'=>'Pending Appointments','icon'=>'fa-calendar-check-o','industries'=>['beauty_cosmetics','beauty_spa','salon_barbershop','makeup_artist','service_business'],'features'=>['appointments']],
            'today_bookings'=>['title'=>"Today's Bookings",'icon'=>'fa-calendar','industries'=>['restaurant','beauty_cosmetics','beauty_spa','salon_barbershop','makeup_artist'],'features'=>['appointments','table_management']],
            'kitchen_status'=>['title'=>'Kitchen Status','icon'=>'fa-fire','industries'=>['restaurant'],'features'=>['kitchen_workflow']],
            'open_tables'=>['title'=>'Open Tables','icon'=>'fa-table','industries'=>['restaurant'],'features'=>['table_management']],
            'staff_schedule'=>['title'=>'Staff Schedule','icon'=>'fa-users','industries'=>'*','features'=>['staff_assignment']],
            'commission_due'=>['title'=>'Commission Due','icon'=>'fa-percent','industries'=>'*','features'=>['staff_commission']],
            'pending_approvals'=>['title'=>'Pending Approvals','icon'=>'fa-check-square-o','industries'=>'*','features'=>['manager_approvals']],
            'treatment_history'=>['title'=>'Treatment History','icon'=>'fa-heartbeat','industries'=>['beauty_cosmetics','beauty_spa'],'features'=>['treatment_notes']],
            'recipe_costing'=>['title'=>'Recipe Costing','icon'=>'fa-cutlery','industries'=>['restaurant','bakery_cake_studio'],'features'=>['recipe_tracking']],
            'online_orders'=>['title'=>'Online Orders','icon'=>'fa-globe','industries'=>'*','features'=>['online_store']],
            'qr_orders'=>['title'=>'QR Orders','icon'=>'fa-qrcode','industries'=>'*','features'=>['qr_ordering']],
            'subscription_status'=>['title'=>'Subscription Status','icon'=>'fa-key','industries'=>'*','features'=>[]],
        ];
    }
}

if (!function_exists('mp_get_active_dashboard_widgets')) {
    function mp_get_active_dashboard_widgets($store_id=null) {
        $profile = mp_get_store_profile($store_id);
        $industry = $profile['industry_type']; $features = $profile['features'];
        $all = mp_get_dashboard_widgets(); $active = [];
        foreach ($all as $key=>$widget) {
            $eligible = ($widget['industries']==='*' || in_array($industry,$widget['industries']));
            if ($eligible && !empty($widget['features'])) {
                $has = false;
                foreach ($widget['features'] as $req) { if (in_array($req,$features)) { $has=true; break; } }
                if (!$has) $eligible = false;
            }
            if ($eligible) $active[$key] = $widget;
        }
        return $active;
    }
}

if (!function_exists('mp_get_workflow_templates')) {
    function mp_get_workflow_templates() {
        return [
            'retail_standard'       => 'Retail Standard',
            'pharmacy_standard'   => 'Pharmacy Standard',
            'restaurant_standard' => 'Restaurant Standard',
            'electronics_standard'=> 'Electronics Standard',
            'beauty_standard'     => 'Beauty / Spa Standard',
            'salon_standard'      => 'Salon / Barbershop Standard',
            'makeup_standard'     => 'Makeup Artist Standard',
            'laundry_standard'    => 'Laundry Standard',
            'bakery_standard'     => 'Bakery / Cake Studio Standard',
            'wholesale_standard'  => 'Wholesale Standard',
            'furniture_standard'  => 'Furniture Standard',
            'service_standard'    => 'Service Business Standard',
        ];
    }
}

if (!function_exists('mp_get_dashboard_templates')) {
    function mp_get_dashboard_templates() {
        return [
            'general_retail'      => 'General Retail',
            'supermarket'         => 'Supermarket',
            'mini_mart'           => 'Mini Mart',
            'pharmacy'            => 'Pharmacy',
            'restaurant'          => 'Restaurant',
            'electronics'         => 'Electronics',
            'fashion'             => 'Fashion / Clothing',
            'beauty'              => 'Beauty / Cosmetics',
            'beauty_spa'          => 'Beauty Spa',
            'salon'               => 'Salon / Barbershop',
            'makeup_artist'       => 'Makeup Artist',
            'laundry'             => 'Laundry',
            'bakery'              => 'Bakery / Cake Studio',
            'bookshop'            => 'Bookshop',
            'building_materials'  => 'Building Materials',
            'furniture'           => 'Furniture',
            'phone_accessories'   => 'Phone Accessories',
            'service_business'    => 'Service Business',
        ];
    }
}

if (!function_exists('mp_get_storefront_themes')) {
    function mp_get_storefront_themes() {
        return [
            'general_retail'  => 'General Retail',
            'fresh_market'    => 'Fresh Market',
            'healthcare_pro'  => 'Healthcare Pro',
            'food_express'    => 'Food Express',
            'tech_hub'        => 'Tech Hub',
            'urban_fashion'   => 'Urban Fashion',
            'beauty_luxe'     => 'Beauty Luxe',
            'service_pro'     => 'Service Pro',
        ];
    }
}

if (!function_exists('mp_suggest_storefront_theme')) {
    function mp_suggest_storefront_theme($business_type=null,$store_id=null) {
        $CI =& get_instance();
        if (empty($store_id)) $store_id = get_current_store_id();
        if (empty($business_type)) {
            $q = $CI->db->select('industry_type')->where('id',$store_id)->get('db_store');
            $business_type = 'general_retail';
            if ($q && method_exists($q, 'row')) {
                $row = $q->row();
                if ($row && !empty($row->industry_type)) {
                    $business_type = $row->industry_type;
                }
            }
        }
        $presets = mp_get_business_presets();
        return isset($presets[$business_type]) ? $presets[$business_type]['theme_key'] : 'general_retail';
    }
}

if (!function_exists('mp_get_custom_field_types')) {
    function mp_get_custom_field_types() {
        return [
            'text'=>['label'=>'Text','has_options'=>false],'textarea'=>['label'=>'Textarea','has_options'=>false],
            'number'=>['label'=>'Number','has_options'=>false],'dropdown'=>['label'=>'Dropdown','has_options'=>true],
            'radio'=>['label'=>'Radio','has_options'=>true],'checkbox'=>['label'=>'Checkbox','has_options'=>true],
            'date'=>['label'=>'Date','has_options'=>false],'time'=>['label'=>'Time','has_options'=>false],
            'color'=>['label'=>'Color','has_options'=>false],'image_upload'=>['label'=>'Image Upload','has_options'=>false],
            'file_upload'=>['label'=>'File Upload','has_options'=>false],'measurement'=>['label'=>'Measurement','has_options'=>false],
            'budget'=>['label'=>'Budget','has_options'=>false],'notes'=>['label'=>'Notes','has_options'=>false],
        ];
    }
}

if (!function_exists('mp_get_service_custom_fields')) {
    function mp_get_service_custom_fields($service_id) {
        $CI =& get_instance();
        $q = $CI->db->select('industry_fields_json')->where('id',$service_id)->get('db_services');
        if (!$q || !method_exists($q, 'row')) {
            return [];
        }
        $row = $q->row();
        if ($row && !empty($row->industry_fields_json)) {
            $fields = json_decode($row->industry_fields_json,true);
            return is_array($fields) ? $fields : [];
        }
        return [];
    }
}

if (!function_exists('mp_set_service_custom_fields')) {
    function mp_set_service_custom_fields($service_id,$fields_array) {
        $CI =& get_instance();
        $json = json_encode($fields_array);
        return @$CI->db->where('id',$service_id)->update('db_services',['industry_fields_json'=>$json]);
    }
}
