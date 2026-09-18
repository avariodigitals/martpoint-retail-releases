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
            'distributor'           => 'Distributor',
            'wholesaler'            => 'Wholesaler',
            'bookshop'              => 'Bookshop',
            'building_materials'    => 'Building Materials',
            'furniture'             => 'Furniture',
            'phone_accessories'     => 'Phone Accessories',
            'service_business'      => 'Service Business',
            'grocery_store'         => 'Grocery Store',
            'provision_store'       => 'Provision Store',
            'convenience_store'     => 'Convenience Store',
            'fast_food'             => 'Fast Food',
            'cafe'                  => 'Cafe',
            'pizza_shop'            => 'Pizza Shop',
            'shawarma'              => 'Shawarma',
            'juice_bar'             => 'Juice Bar',
            'buka'                  => 'Buka / Mama Put',
            'canteen'               => 'Canteen / Food Court',
            'butcher'               => 'Butcher / Meat Shop',
            'frozen_foods_retailer' => 'Frozen Foods Retailer',
            'medical_store'         => 'Medical Store',
            'clinic'                => 'Clinic',
            'hospital'              => 'Hospital',
            'diagnostic_centre'     => 'Diagnostic Centre',
            'boutique'              => 'Boutique',
            'shoe_store'            => 'Shoe Store',
            'perfume_shop'          => 'Perfume Shop',
            'jewellery_store'       => 'Jewellery Store',
            'computer_store'        => 'Computer Store',
            'gadget_store'          => 'Gadget Store',
            'appliance_store'       => 'Appliance Store',
            'paint_store'           => 'Paint Store',
            'plumbing_store'        => 'Plumbing Store',
            'agro_dealer'           => 'Agro Dealer',
            'feed_store'            => 'Feed Store',
            'auto_parts'            => 'Auto Parts',
            'tyre_shop'             => 'Tyre Shop',
            'car_dealership'        => 'Car Dealership',
            'printing'              => 'Printing',
            'tailoring'             => 'Tailoring',
            'manufacturer'          => 'Manufacturer',
            'multi_branch_retail'   => 'Multi-Branch Retail',
            'online_store'          => 'Online Store',
            'creator'               => 'Creator / Digital Store',
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
            'warehouse'                 => 'Branch / Stock Transfer Module',
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
            'multi_unit_selling'        => 'Multi-Unit Selling (Pack/Piece/Box)',
            'batch_tracking'            => 'Batch Tracking',
            'expiry_tracking'           => 'Expiry Tracking',
            'mfg_tracking'              => 'Manufacturing / MFG Date',
            'serial_number_tracking'    => 'Serial Number Tracking',
            'imei_tracking'             => 'IMEI Tracking',
            'warranty_tracking'         => 'Warranty Tracking',
            'auto_parts'                => 'Auto Parts / Spares Catalog',
            'kitchen_workflow'          => 'Kitchen Workflow',
            'table_management'          => 'Table Management',
            'laundry_workflow'          => 'Laundry Workflow',
            'meat_butchery_workflow'    => 'Meat / Butchery Workflow',
            'frozen_food_cold_chain'    => 'Frozen Food Cold Chain',
            'automobile_workflow'       => 'Automobile / Vehicle Management',
            'treatment_notes'           => 'Treatment Notes',
            'staff_assignment'          => 'Staff Assignment',
            'staff_commission'          => 'Staff Commission',
            'delivery_scheduling'       => 'Delivery Scheduling',
            'production_workflow'       => 'Production Workflow',
            'recipe_tracking'           => 'Recipe Tracking',
            'customer_notes'            => 'Customer Notes',
            'price_catalogue'           => 'Price Catalogue',
            'public_catalogue'          => 'Public Catalogue',
            'digital_products'          => 'Digital Products',
            'courses'                   => 'Online Courses',
            'manager_approvals'         => 'Manager Approvals',
            'cashier_shifts'            => 'Cashier Shifts / Tills',
            'medical_notes'             => 'Medical Notes (Pharmacy)',
            'leads'                     => 'Leads / CRM',
            'manual_shipping'           => 'Manual Shipping (POS Delivery Fee)',
            'fashion_variants_default'  => 'Fashion: Default New Items to Variants',
            'pos_retail_button'         => 'POS Retail Price Button',
            'pos_wholesale_button'      => 'POS Wholesale Price Button',
        ];
    }
}

if (!function_exists('mp_feature_label')) {
    function mp_feature_label($flag_key) {
        $flags = mp_get_feature_flags();
        if (is_array($flags) && isset($flags[$flag_key])) {
            return $flags[$flag_key];
        }
        return ucwords(str_replace(['_','-'], ' ', $flag_key));
    }
}

if (!function_exists('mp_get_label_defaults')) {
    function mp_get_label_defaults() {
        return [
            'warehouse'         => 'Branch',
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
        $presets = [
            'general_retail' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','gift_cards','store_credit','multi_unit_inventory','batch_tracking','public_catalogue','pos_retail_button','pos_wholesale_button','digital_products','courses'],
                'theme_key'=>'general_retail','dashboard_template'=>'general_retail','workflow_template'=>'retail_standard','labels'=>[],
            ],
            'supermarket' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','gift_cards','store_credit','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','delivery_scheduling','public_catalogue','pos_retail_button'],
                'theme_key'=>'market_fresh','dashboard_template'=>'supermarket','workflow_template'=>'retail_standard',
                'labels'=>['warehouse'=>'Branch','item'=>'Product'],
            ],
            'mini_mart' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','public_catalogue','pos_retail_button'],
                'theme_key'=>'daily_cart','dashboard_template'=>'mini_mart','workflow_template'=>'retail_standard',
                'labels'=>['warehouse'=>'Branch','item'=>'Product'],
            ],
            'pharmacy' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','loyalty','multi_unit_inventory','batch_tracking','expiry_tracking','serial_number_tracking','manager_approvals','price_catalogue','public_catalogue','medical_notes'],
                'theme_key'=>'healthcare_pro','dashboard_template'=>'pharmacy','workflow_template'=>'pharmacy_standard',
                'labels'=>['item'=>'Medicine','batch'=>'Batch','expiry'=>'Expiry Date','customer'=>'Patient'],
            ],
            'restaurant' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','table_management','kitchen_workflow','loyalty','delivery_scheduling','manager_approvals','recipe_tracking','production_workflow','staff_commission'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                'labels'=>['item'=>'Menu Item','service'=>'Dining Service','table'=>'Table','order'=>'Order','pos'=>'Counter'],
            ],
            'electronics' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','gift_cards','store_credit','serial_number_tracking','imei_tracking','warranty_tracking','manager_approvals','price_catalogue','public_catalogue','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'tech_hub','dashboard_template'=>'electronics','workflow_template'=>'electronics_standard',
                'labels'=>['item'=>'Product','serial'=>'Serial Number','imei'=>'IMEI'],
            ],
            'fashion' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','bundles','online_store','qr_ordering','loyalty','gift_cards','store_credit','multi_unit_inventory','public_catalogue','fashion_variants_default'],
                'theme_key'=>'urban_fashion','dashboard_template'=>'fashion','workflow_template'=>'retail_standard',
                'labels'=>['item'=>'Item','category'=>'Collection','customer'=>'Customer','warehouse'=>'Branch'],
            ],
            'beauty_cosmetics' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','appointments','service_workflow','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','treatment_notes','price_catalogue','public_catalogue','pos_retail_button','pos_wholesale_button','leads'],
                'theme_key'=>'beauty_luxe','dashboard_template'=>'beauty','workflow_template'=>'beauty_standard',
                'labels'=>['service'=>'Treatment','service_order'=>'Treatment Booking','customer'=>'Client','staff'=>'Therapist'],
            ],
            'beauty_spa' => [
                'business_model'=>'service_based',
                'features'=>['accounts','online_store','appointments','service_workflow','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','treatment_notes','packages','memberships','public_catalogue','pos_retail_button','pos_wholesale_button','leads'],
                'theme_key'=>'beauty_luxe','dashboard_template'=>'beauty_spa','workflow_template'=>'beauty_standard',
                'labels'=>['service'=>'Treatment','service_order'=>'Spa Booking','customer'=>'Guest','staff'=>'Therapist','package'=>'Spa Package'],
            ],
            'salon_barbershop' => [
                'business_model'=>'service_based',
                'features'=>['accounts','online_store','appointments','service_workflow','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','packages','public_catalogue','pos_retail_button','pos_wholesale_button','leads'],
                'theme_key'=>'beauty_luxe','dashboard_template'=>'salon','workflow_template'=>'salon_standard',
                'labels'=>['service'=>'Service','service_order'=>'Booking','customer'=>'Client','staff'=>'Stylist','package'=>'Service Package'],
            ],
            'makeup_artist' => [
                'business_model'=>'service_based',
                'features'=>['accounts','online_store','appointments','service_workflow','custom_orders','loyalty','gift_cards','staff_assignment','staff_commission','treatment_notes','public_catalogue','pos_retail_button','pos_wholesale_button','leads'],
                'theme_key'=>'beauty_luxe','dashboard_template'=>'makeup_artist','workflow_template'=>'makeup_standard',
                'labels'=>['service'=>'Service','service_order'=>'Booking','customer'=>'Client','staff'=>'Artist','custom_order'=>'Custom Booking'],
            ],
            'laundry' => [
                'business_model'=>'service_based',
                'features'=>['accounts','online_store','qr_ordering','service_workflow','laundry_workflow','loyalty','delivery_scheduling','staff_assignment','staff_commission','public_catalogue','pos_retail_button','pos_wholesale_button','leads'],
                'theme_key'=>'laundry','dashboard_template'=>'laundry','workflow_template'=>'laundry_standard',
                'labels'=>['service'=>'Service','service_order'=>'Laundry Order','customer'=>'Customer','delivery'=>'Pickup/Delivery'],
            ],
            'bakery_cake_studio' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','warehouse','online_store','qr_ordering','custom_orders','packages','production_workflow','recipe_tracking','delivery_scheduling','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','public_catalogue','pos_retail_button','pos_wholesale_button','leads'],
                'theme_key'=>'food_express','dashboard_template'=>'bakery','workflow_template'=>'bakery_standard',
                'labels'=>['service'=>'Custom Cake','service_order'=>'Cake Order','customer'=>'Client','product'=>'Baked Item','recipe'=>'Recipe','production'=>'Production'],
            ],
            'bookshop' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','loyalty','gift_cards','store_credit','multi_unit_inventory','public_catalogue','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'bookshop','workflow_template'=>'retail_standard',
                'labels'=>['item'=>'Book','category'=>'Genre'],
            ],
            'building_materials' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','multi_unit_inventory','batch_tracking','delivery_scheduling','manager_approvals','price_catalogue','public_catalogue','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'building_materials','workflow_template'=>'wholesale_standard',
                'labels'=>['item'=>'Material','warehouse'=>'Depot','customer'=>'Client'],
            ],
            'furniture' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','custom_orders','delivery_scheduling','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','public_catalogue','pos_retail_button','pos_wholesale_button','leads'],
                'theme_key'=>'general_retail','dashboard_template'=>'furniture','workflow_template'=>'furniture_standard',
                'labels'=>['item'=>'Furniture','service'=>'Custom Design','service_order'=>'Design Order','customer'=>'Client','delivery'=>'Delivery'],
            ],
            'phone_accessories' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','gift_cards','store_credit','imei_tracking','warranty_tracking','public_catalogue','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'tech_hub','dashboard_template'=>'phone_accessories','workflow_template'=>'electronics_standard',
                'labels'=>['item'=>'Accessory','imei'=>'IMEI','serial'=>'Serial'],
            ],
            'distributor' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','payplan','store_credit','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','manager_approvals','price_catalogue','public_catalogue','delivery_scheduling','customer_notes','cashier_shifts','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'distributor','workflow_template'=>'distributor_standard',
                'labels'=>['item'=>'Product','batch'=>'Batch','expiry'=>'Expiry Date','customer'=>'Client','warehouse'=>'Depot','order'=>'Order'],
            ],
            'wholesaler' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','payplan','store_credit','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','manager_approvals','price_catalogue','public_catalogue','delivery_scheduling','customer_notes','cashier_shifts','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'wholesaler','workflow_template'=>'wholesaler_standard',
                'labels'=>['item'=>'Product','batch'=>'Batch','expiry'=>'Expiry Date','customer'=>'Client','warehouse'=>'Branch','order'=>'Order'],
            ],
            'service_business' => [
                'business_model'=>'service_based',
                'features'=>['accounts','online_store','appointments','service_workflow','custom_orders','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','packages','memberships','public_catalogue','pos_retail_button','pos_wholesale_button','leads'],
                'theme_key'=>'service_pro','dashboard_template'=>'service_business','workflow_template'=>'service_standard',
                'labels'=>['service'=>'Service','service_order'=>'Job','customer'=>'Client','staff'=>'Team Member'],
            ],
            'grocery_store' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','public_catalogue','pos_retail_button'],
                'theme_key'=>'daily_cart','dashboard_template'=>'mini_mart','workflow_template'=>'retail_standard',
                'labels'=>['warehouse'=>'Branch','item'=>'Product'],
            ],
            'provision_store' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','multi_unit_inventory','batch_tracking','expiry_tracking','public_catalogue','pos_retail_button'],
                'theme_key'=>'daily_cart','dashboard_template'=>'mini_mart','workflow_template'=>'retail_standard',
                'labels'=>['warehouse'=>'Branch','item'=>'Product'],
            ],
            'convenience_store' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','multi_unit_inventory','batch_tracking','expiry_tracking','public_catalogue','pos_retail_button'],
                'theme_key'=>'daily_cart','dashboard_template'=>'mini_mart','workflow_template'=>'retail_standard',
                'labels'=>['warehouse'=>'Branch','item'=>'Product'],
            ],
            'fast_food' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','table_management','kitchen_workflow','loyalty','delivery_scheduling','manager_approvals','recipe_tracking','production_workflow','staff_commission'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                'labels'=>['item'=>'Menu Item','service'=>'Fast Food Service','table'=>'Counter','order'=>'Order','pos'=>'Counter'],
            ],
            'cafe' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','table_management','kitchen_workflow','loyalty','delivery_scheduling','manager_approvals','recipe_tracking','production_workflow','staff_commission'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                'labels'=>['item'=>'Menu Item','service'=>'Cafe Service','table'=>'Table','order'=>'Order','pos'=>'Counter'],
            ],
            'pizza_shop' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','table_management','kitchen_workflow','loyalty','delivery_scheduling','manager_approvals','recipe_tracking','production_workflow','staff_commission'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                'labels'=>['item'=>'Pizza','service'=>'Pizza Service','table'=>'Table','order'=>'Order','pos'=>'Counter'],
            ],
            'shawarma' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','table_management','kitchen_workflow','loyalty','delivery_scheduling','manager_approvals','recipe_tracking','production_workflow','staff_commission'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                'labels'=>['item'=>'Shawarma','service'=>'Grill Service','table'=>'Counter','order'=>'Order','pos'=>'Counter'],
            ],
            'juice_bar' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','custom_orders','table_management','kitchen_workflow','loyalty','delivery_scheduling','recipe_tracking','staff_commission'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                'labels'=>['item'=>'Beverage','service'=>'Juice Service','table'=>'Counter','order'=>'Order','pos'=>'Counter'],
            ],
            'buka' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','table_management','kitchen_workflow','loyalty','delivery_scheduling','manager_approvals','recipe_tracking','production_workflow','staff_commission'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                'labels'=>['item'=>'Menu Item','service'=>'Buka Service','table'=>'Table','order'=>'Order','pos'=>'Counter'],
            ],
            'canteen' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','table_management','kitchen_workflow','loyalty','delivery_scheduling','manager_approvals','recipe_tracking','production_workflow','staff_commission'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                'labels'=>['item'=>'Menu Item','service'=>'Canteen Service','table'=>'Table','order'=>'Meal','pos'=>'Counter'],
            ],
            'butcher' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','mfg_tracking','production_workflow','recipe_tracking','meat_butchery_workflow','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'market_fresh','dashboard_template'=>'butcher','workflow_template'=>'retail_standard',
                'labels'=>['item'=>'Cut / Product','batch'=>'Batch','expiry'=>'Expiry Date','warehouse'=>'Branch','customer'=>'Customer'],
            ],
            'frozen_foods_retailer' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','mfg_tracking','frozen_food_cold_chain','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'market_fresh','dashboard_template'=>'frozen_foods','workflow_template'=>'retail_standard',
                'labels'=>['item'=>'Product / Pack','batch'=>'Batch','expiry'=>'Expiry Date','warehouse'=>'Branch','customer'=>'Customer'],
            ],
            'medical_store' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','loyalty','multi_unit_inventory','batch_tracking','expiry_tracking','serial_number_tracking','manager_approvals','price_catalogue','public_catalogue','medical_notes'],
                'theme_key'=>'healthcare_pro','dashboard_template'=>'pharmacy','workflow_template'=>'pharmacy_standard',
                'labels'=>['item'=>'Medical Item','batch'=>'Batch','expiry'=>'Expiry Date','customer'=>'Patient'],
            ],
            'clinic' => [
                'business_model'=>'service_based',
                'features'=>['accounts','online_store','appointments','service_workflow','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','treatment_notes','medical_notes','public_catalogue','leads'],
                'theme_key'=>'healthcare_pro','dashboard_template'=>'service_business','workflow_template'=>'service_standard',
                'labels'=>['service'=>'Consultation','service_order'=>'Appointment','customer'=>'Patient','staff'=>'Doctor'],
            ],
            'hospital' => [
                'business_model'=>'service_based',
                'features'=>['accounts','warehouse','online_store','appointments','service_workflow','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','treatment_notes','medical_notes','public_catalogue','leads'],
                'theme_key'=>'healthcare_pro','dashboard_template'=>'service_business','workflow_template'=>'service_standard',
                'labels'=>['service'=>'Hospital Service','service_order'=>'Admission','customer'=>'Patient','staff'=>'Doctor','warehouse'=>'Department'],
            ],
            'diagnostic_centre' => [
                'business_model'=>'service_based',
                'features'=>['accounts','online_store','appointments','service_workflow','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','treatment_notes','medical_notes','public_catalogue','leads'],
                'theme_key'=>'healthcare_pro','dashboard_template'=>'service_business','workflow_template'=>'service_standard',
                'labels'=>['service'=>'Test','service_order'=>'Booking','customer'=>'Patient','staff'=>'Technician'],
            ],
            'boutique' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','bundles','online_store','qr_ordering','loyalty','gift_cards','store_credit','multi_unit_inventory','public_catalogue','fashion_variants_default'],
                'theme_key'=>'urban_fashion','dashboard_template'=>'fashion','workflow_template'=>'retail_standard',
                'labels'=>['item'=>'Item','category'=>'Collection','customer'=>'Customer','warehouse'=>'Branch'],
            ],
            'shoe_store' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','bundles','online_store','qr_ordering','loyalty','gift_cards','store_credit','multi_unit_inventory','public_catalogue','fashion_variants_default'],
                'theme_key'=>'urban_fashion','dashboard_template'=>'fashion','workflow_template'=>'retail_standard',
                'labels'=>['item'=>'Shoe','category'=>'Category','customer'=>'Customer','warehouse'=>'Branch'],
            ],
            'perfume_shop' => [
                'business_model'=>'product_based',
                'features'=>['accounts','online_store','qr_ordering','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','treatment_notes','price_catalogue','public_catalogue','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'beauty_luxe','dashboard_template'=>'beauty','workflow_template'=>'beauty_standard',
                'labels'=>['item'=>'Perfume','service'=>'Service','customer'=>'Client','staff'=>'Therapist'],
            ],
            'jewellery_store' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','gift_cards','store_credit','serial_number_tracking','warranty_tracking','manager_approvals','price_catalogue','public_catalogue','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'tech_hub','dashboard_template'=>'electronics','workflow_template'=>'electronics_standard',
                'labels'=>['item'=>'Jewellery','serial'=>'Serial Number','imei'=>'Model No','customer'=>'Client'],
            ],
            'computer_store' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','gift_cards','store_credit','serial_number_tracking','warranty_tracking','manager_approvals','price_catalogue','public_catalogue','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'tech_hub','dashboard_template'=>'electronics','workflow_template'=>'electronics_standard',
                'labels'=>['item'=>'Computer','serial'=>'Serial Number','imei'=>'IMEI','customer'=>'Customer'],
            ],
            'gadget_store' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','gift_cards','store_credit','imei_tracking','warranty_tracking','public_catalogue','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'tech_hub','dashboard_template'=>'electronics','workflow_template'=>'electronics_standard',
                'labels'=>['item'=>'Gadget','imei'=>'IMEI','serial'=>'Serial','customer'=>'Customer'],
            ],
            'appliance_store' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','gift_cards','store_credit','serial_number_tracking','warranty_tracking','delivery_scheduling','manager_approvals','price_catalogue','public_catalogue','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'tech_hub','dashboard_template'=>'electronics','workflow_template'=>'electronics_standard',
                'labels'=>['item'=>'Appliance','serial'=>'Serial Number','warranty'=>'Warranty','customer'=>'Client'],
            ],
            'paint_store' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','multi_unit_inventory','batch_tracking','delivery_scheduling','manager_approvals','price_catalogue','public_catalogue','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'building_materials','workflow_template'=>'wholesale_standard',
                'labels'=>['item'=>'Paint','warehouse'=>'Depot','customer'=>'Client'],
            ],
            'plumbing_store' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','multi_unit_inventory','batch_tracking','delivery_scheduling','manager_approvals','price_catalogue','public_catalogue','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'building_materials','workflow_template'=>'wholesale_standard',
                'labels'=>['item'=>'Plumbing Item','warehouse'=>'Depot','customer'=>'Client'],
            ],
            'agro_dealer' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','payplan','store_credit','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','manager_approvals','price_catalogue','public_catalogue','delivery_scheduling','customer_notes','cashier_shifts','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'distributor','workflow_template'=>'distributor_standard',
                'labels'=>['item'=>'Agro Input','batch'=>'Batch','expiry'=>'Expiry Date','customer'=>'Client','warehouse'=>'Depot','order'=>'Order'],
            ],
            'feed_store' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','payplan','store_credit','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','manager_approvals','price_catalogue','public_catalogue','delivery_scheduling','customer_notes','cashier_shifts','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'distributor','workflow_template'=>'distributor_standard',
                'labels'=>['item'=>'Feed','batch'=>'Batch','expiry'=>'Expiry Date','customer'=>'Client','warehouse'=>'Depot','order'=>'Order'],
            ],
            'auto_parts' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','payplan','store_credit','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','manager_approvals','price_catalogue','public_catalogue','delivery_scheduling','customer_notes','cashier_shifts','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'distributor','workflow_template'=>'distributor_standard',
                'labels'=>['item'=>'Auto Part','batch'=>'Batch','expiry'=>'Expiry Date','customer'=>'Client','warehouse'=>'Depot','order'=>'Order'],
            ],
            'tyre_shop' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','payplan','store_credit','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','manager_approvals','price_catalogue','public_catalogue','delivery_scheduling','customer_notes','cashier_shifts','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'distributor','workflow_template'=>'distributor_standard',
                'labels'=>['item'=>'Tyre','batch'=>'Batch','expiry'=>'Expiry Date','customer'=>'Client','warehouse'=>'Depot','order'=>'Order'],
            ],
            'car_dealership' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','payplan','store_credit','automobile_workflow','auto_parts','service_workflow','price_catalogue','public_catalogue','delivery_scheduling','customer_notes','cashier_shifts','pos_retail_button','pos_wholesale_button','leads'],
                'theme_key'=>'general_retail','dashboard_template'=>'distributor','workflow_template'=>'distributor_standard',
                'labels'=>['item'=>'Auto Part','customer'=>'Buyer','warehouse'=>'Branch','order'=>'Order'],
            ],
            'printing' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','warehouse','online_store','custom_orders','production_workflow','recipe_tracking','delivery_scheduling','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','public_catalogue','pos_retail_button','pos_wholesale_button','leads'],
                'theme_key'=>'general_retail','dashboard_template'=>'service_business','workflow_template'=>'service_standard',
                'labels'=>['service'=>'Print Job','service_order'=>'Print Order','item'=>'Material','customer'=>'Client','recipe'=>'Recipe','production'=>'Production'],
            ],
            'tailoring' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','custom_orders','appointments','service_workflow','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','public_catalogue','pos_retail_button','pos_wholesale_button','leads'],
                'theme_key'=>'general_retail','dashboard_template'=>'service_business','workflow_template'=>'service_standard',
                'labels'=>['service'=>'Tailoring Job','service_order'=>'Order','item'=>'Fabric','customer'=>'Client','staff'=>'Tailor'],
            ],
            'manufacturer' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','payplan','store_credit','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','production_workflow','recipe_tracking','manager_approvals','price_catalogue','public_catalogue','delivery_scheduling','customer_notes','cashier_shifts','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'distributor','workflow_template'=>'distributor_standard',
                'labels'=>['item'=>'Finished Good','batch'=>'Batch','expiry'=>'Expiry Date','customer'=>'Client','warehouse'=>'Factory','order'=>'Order','recipe'=>'Recipe','production'=>'Production'],
            ],
            'multi_branch_retail' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','multi_store','online_store','qr_ordering','payplan','store_credit','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','manager_approvals','price_catalogue','public_catalogue','delivery_scheduling','customer_notes','cashier_shifts','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'distributor','workflow_template'=>'distributor_standard',
                'labels'=>['item'=>'Product','batch'=>'Batch','expiry'=>'Expiry Date','customer'=>'Client','warehouse'=>'Branch','order'=>'Order'],
            ],
            'online_store' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','gift_cards','store_credit','multi_unit_inventory','batch_tracking','public_catalogue','pos_retail_button','pos_wholesale_button','digital_products','courses'],
                'theme_key'=>'online_store','dashboard_template'=>'online_store','workflow_template'=>'online_store',
                'labels'=>['item'=>'Product','customer'=>'Customer','warehouse'=>'Branch'],
            ],
            'creator' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','gift_cards','store_credit','public_catalogue','pos_retail_button','pos_wholesale_button','packages','memberships','digital_products','courses','leads'],
                'theme_key'=>'creator_focus','dashboard_template'=>'creator','workflow_template'=>'standard',
                'labels'=>['item'=>'Product','service'=>'Service','customer'=>'Customer','warehouse'=>'Branch'],
            ],
        ];
        $theme_map = [
            'distributor'           => 'wholesale',
            'wholesaler'            => 'wholesale',
            'manufacturer'          => 'wholesale',
            'multi_branch_retail'   => 'wholesale',
            'building_materials'    => 'hardware',
            'paint_store'           => 'hardware',
            'plumbing_store'        => 'hardware',
            'agro_dealer'           => 'agro',
            'feed_store'            => 'agro',
            'auto_parts'            => 'automotive',
            'tyre_shop'             => 'automotive',
            'car_dealership'        => 'auto_modern',
            'bookshop'              => 'general_retail',
            'furniture'             => 'general_retail',
            'printing'              => 'service_pro',
            'tailoring'             => 'service_pro',
        ];
        foreach ($presets as $key => &$preset) {
            if (empty($preset['dashboard_template'])) {
                $preset['dashboard_template'] = $key;
            }
            if (empty($preset['workflow_template'])) {
                $preset['workflow_template'] = $key . '_standard';
            }
            if (isset($theme_map[$key])) {
                $preset['theme_key'] = $theme_map[$key];
            }
            // Leads/CRM is available to every industry
            if (!in_array('leads', $preset['features'], true)) {
                $preset['features'][] = 'leads';
            }
        }
        unset($preset);
        return $presets;
    }
}

if (!function_exists('mp_feature_flag_raw')) {
    function mp_feature_flag_raw($flag_key) {
        $CI =& get_instance();
        $store_id = get_current_store_id();

        // 1. Prefer modular industry settings table
        if ($CI->db->table_exists('db_store_industry_settings')) {
            $q = $CI->db->select('feature_flags_json')
                ->where('store_id',$store_id)->get('db_store_industry_settings');
            if ($q && method_exists($q, 'row')) {
                $store = $q->row();
                if ($store && !empty($store->feature_flags_json)) {
                    $flags = json_decode($store->feature_flags_json,true);
                    if (is_array($flags) && array_key_exists($flag_key,$flags)) {
                        return filter_var($flags[$flag_key],FILTER_VALIDATE_BOOLEAN);
                    }
                }
            }
        }

        // 2. Fallback to db_store columns
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
        static $profile_features = null;
        if ($profile_features === null) {
            $profile = mp_get_store_profile();
            $profile_features = $profile['features'] ?? [];
        }
        switch ($flag_key) {
            case 'service_workflow': case 'appointments': return service_module() && in_array($flag_key, $profile_features);
            case 'accounts': return accounts_module();
            case 'warehouse': case 'multi_unit_inventory': return warehouse_module();
            case 'online_store': case 'qr_ordering': case 'loyalty': case 'gift_cards': case 'store_credit': return in_array($flag_key, $profile_features);
            // Inventory tracking flags - require both the warehouse module and the industry profile
            case 'serial_number_tracking': case 'imei_tracking': case 'warranty_tracking': case 'batch_tracking': case 'expiry_tracking': case 'meat_butchery_workflow': case 'frozen_food_cold_chain': return warehouse_module() && in_array($flag_key, $profile_features);
            case 'mfg_tracking': return mp_feature_enabled('expiry_tracking');
            // Service flags - require service module and the industry profile
            case 'treatment_notes': case 'staff_assignment': case 'staff_commission': case 'custom_orders': return service_module() && in_array($flag_key, $profile_features);
            // Table management is a restaurant/food feature, not a service-workflow feature
            case 'table_management': return in_array($flag_key, $profile_features);
            // Business-driven features
            case 'packages': case 'memberships': case 'kitchen_workflow': case 'laundry_workflow': case 'production_workflow': case 'recipe_tracking': case 'delivery_scheduling': return in_array($flag_key, $profile_features);
            // Sales & storefront
            case 'price_catalogue': case 'public_catalogue': return in_array($flag_key, $profile_features);
            case 'customer_notes': case 'manager_approvals': case 'cashier_shifts': return true;
            case 'medical_notes': return in_array('medical_notes', $profile_features);
            case 'payplan': return true;
            case 'bundles': return true; // variants available unless explicitly disabled in feature flags
            default: return in_array($flag_key, $profile_features);
        }
    }
}

if (!function_exists('mp_feature_enabled_for_store')) {
    /**
     * Same as mp_feature_enabled() but for an explicit store — needed on public
     * pages (storefront) where there is no session store_id.
     */
    function mp_feature_enabled_for_store($flag_key, $store_id) {
        $CI =& get_instance();
        $raw = null;
        if ($CI->db->table_exists('db_store_industry_settings')) {
            $store = $CI->db->select('feature_flags_json')->where('store_id', $store_id)->get('db_store_industry_settings')->row();
            if ($store && !empty($store->feature_flags_json)) {
                $flags = json_decode($store->feature_flags_json, true);
                if (is_array($flags) && array_key_exists($flag_key, $flags)) {
                    $raw = filter_var($flags[$flag_key], FILTER_VALIDATE_BOOLEAN);
                }
            }
        }
        if ($raw === null && $CI->db->field_exists('feature_flags_json', 'db_store')) {
            $store = $CI->db->select('feature_flags_json')->where('id', $store_id)->get('db_store')->row();
            if ($store && !empty($store->feature_flags_json)) {
                $flags = json_decode($store->feature_flags_json, true);
                if (is_array($flags) && array_key_exists($flag_key, $flags)) {
                    $raw = filter_var($flags[$flag_key], FILTER_VALIDATE_BOOLEAN);
                }
            }
        }
        if ($raw !== null) {
            return $raw;
        }
        $profile = mp_get_store_profile($store_id);
        return in_array($flag_key, $profile['features'] ?? []);
    }
}

if (!function_exists('mp_get_store_profile')) {
    function mp_get_store_profile($store_id=null) {
        $CI =& get_instance();
        if (empty($store_id)) $store_id = get_current_store_id();
        $columns = ['industry_type','business_model','feature_flags_json','workflow_template_key','dashboard_template_key','storefront_theme_key','label_overrides_json','industry_settings_json'];

        $store = null;

        // 1. Prefer new modular industry settings table
        if ($CI->db->table_exists('db_store_industry_settings')) {
            $q = $CI->db->select(implode(',', $columns))->where('store_id',$store_id)->get('db_store_industry_settings');
            if ($q && method_exists($q, 'row')) {
                $store = $q->row();
            }
        }

        // 2. Fallback: older dedicated business-profile table
        if (!$store && $CI->db->table_exists('db_store_business_profile')) {
            $q = $CI->db->select(implode(',', $columns))->where('store_id',$store_id)->get('db_store_business_profile');
            if ($q && method_exists($q, 'row')) {
                $store = $q->row();
            }
        }

        // 3. Fallback: old db_store columns
        if (!$store) {
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
        }

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
            'features'=>['online_store','qr_ordering','loyalty','gift_cards','store_credit','multi_unit_inventory','batch_tracking','public_catalogue','pos_retail_button','pos_wholesale_button'],
            'theme_key'=>'general_retail','dashboard_template'=>'general_retail',
            'workflow_template'=>'retail_standard','labels'=>[],'settings'=>[],
        ];
    }
}

if (!function_exists('mp_label')) {
    function mp_label($key,$fallback=null) {
        $CI =& get_instance();
        $store_id = get_current_store_id();

        // 1. Prefer modular industry settings table
        if ($CI->db->table_exists('db_store_industry_settings')) {
            $q = $CI->db->select('label_overrides_json')->where('store_id',$store_id)->get('db_store_industry_settings');
            if ($q && method_exists($q, 'row')) {
                $store = $q->row();
                if ($store && !empty($store->label_overrides_json)) {
                    $overrides = json_decode($store->label_overrides_json,true);
                    if (is_array($overrides) && isset($overrides[$key]) && $overrides[$key]!=='') return $overrides[$key];
                }
            }
        }

        // 2. Fallback to db_store columns
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
            'near_expiry'=>['title'=>'Near Expiry','icon'=>'fa-calendar-times-o','industries'=>['pharmacy','supermarket','mini_mart','distributor','wholesaler','bakery_cake_studio'],'features'=>['expiry_tracking']],
            'expired_items'=>['title'=>'Expired Items','icon'=>'fa-ban','industries'=>['pharmacy','supermarket','mini_mart','distributor','wholesaler','bakery_cake_studio'],'features'=>['expiry_tracking']],
            'low_stock_medicines'=>['title'=>'Low Stock Medicines','icon'=>'fa-medkit','industries'=>['pharmacy'],'features'=>[]],
            'pending_laundry'=>['title'=>'Pending Laundry','icon'=>'fa-refresh','industries'=>['laundry'],'features'=>['laundry_workflow']],
            'ready_for_pickup'=>['title'=>'Ready for Pickup','icon'=>'fa-check-circle','industries'=>['laundry'],'features'=>['laundry_workflow']],
            'overdue_pickups'=>['title'=>'Overdue Pickups','icon'=>'fa-clock-o','industries'=>['laundry'],'features'=>['laundry_workflow']],
            'upcoming_events'=>['title'=>'Upcoming Events','icon'=>'fa-calendar','industries'=>['bakery_cake_studio','makeup_artist','furniture'],'features'=>['custom_orders']],
            'production_queue'=>['title'=>'Production Queue','icon'=>'fa-industry','industries'=>['bakery_cake_studio','restaurant'],'features'=>['production_workflow']],
            'deposit_balance'=>['title'=>'Deposit Balance','icon'=>'fa-money','industries'=>['bakery_cake_studio','furniture','makeup_artist'],'features'=>['custom_orders']],
            'pending_appointments'=>['title'=>'Pending Appointments','icon'=>'fa-calendar-check-o','industries'=>['beauty_cosmetics','beauty_spa','salon_barbershop','makeup_artist','service_business'],'features'=>['appointments']],
            'today_bookings'=>['title'=>"Today's Bookings & Tables",'icon'=>'fa-calendar','industries'=>['restaurant','beauty_cosmetics','beauty_spa','salon_barbershop','makeup_artist'],'features'=>['appointments','table_management']],
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
        $templates = [
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
            'distributor_standard' => 'Distributor Standard',
            'wholesaler_standard'  => 'Wholesaler Standard',
            'furniture_standard'  => 'Furniture Standard',
            'service_standard'    => 'Service Business Standard',
        ];
        foreach (mp_get_business_types() as $key => $label) {
            $standard = $key . '_standard';
            if (!isset($templates[$standard])) {
                $templates[$standard] = $label . ' Standard';
            }
        }
        return $templates;
    }
}

if (!function_exists('mp_get_dashboard_templates')) {
    function mp_get_dashboard_templates() {
        $templates = [
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
            'distributor'         => 'Distributor',
            'wholesaler'          => 'Wholesaler',
            'service_business'    => 'Service Business',
            'frozen_foods'        => 'Frozen Foods',
        ];
        foreach (mp_get_business_types() as $key => $label) {
            if (!isset($templates[$key])) {
                $templates[$key] = $label;
            }
        }
        return $templates;
    }
}

if (!function_exists('mp_get_storefront_themes')) {
    function mp_get_storefront_themes($industry_type = null) {
        $CI =& get_instance();
        if (!isset($CI->storefront_model)) {
            $CI->load->model('storefront_model');
        }
        return $CI->storefront_model->getThemesByIndustryForStore($industry_type, false);
    }
}

if (!function_exists('mp_suggest_storefront_theme')) {
    function mp_suggest_storefront_theme($business_type=null,$store_id=null) {
        $CI =& get_instance();
        if (empty($store_id)) $store_id = get_current_store_id();
        if (empty($business_type)) {
            $business_type = 'general_retail';
            if ($CI->db->table_exists('db_store_business_profile')) {
                $q = $CI->db->select('industry_type')->where('store_id',$store_id)->get('db_store_business_profile');
            } else {
                $q = $CI->db->select('industry_type')->where('id',$store_id)->get('db_store');
            }
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
