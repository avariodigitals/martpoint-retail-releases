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
            'makeup_studio'         => 'Makeup Studio',
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
            'bar_lounge'            => 'Bar / Lounge',
            'butcher'               => 'Butcher / Meat Shop',
            'frozen_foods_retailer' => 'Frozen Foods Retailer',
            'medical_store'         => 'Medical Store',
            'supplements_wellness'  => 'Supplements & Wellness',
            'clinic'                => 'Clinic',
            'hospital'              => 'Hospital',
            'diagnostic_centre'     => 'Diagnostic Centre',
            'physiotherapy_rehabilitation' => 'Physiotherapy & Rehabilitation',
            'boutique'              => 'Boutique',
            'shoe_store'            => 'Shoe Store',
            'perfume_shop'          => 'Perfume Shop',
            'skincare'              => 'Skincare & Organic Cosmetics',
            'jewellery_store'       => 'Jewellery Store',
            'computer_store'        => 'Computer Store',
            'gadget_store'          => 'Gadget Store',
            'appliance_store'       => 'Appliance Store',
            'paint_store'           => 'Paint Store',
            'plumbing_store'        => 'Plumbing Store',
            'agro_dealer'           => 'Agro Dealer',
            'garden_centre_landscaping' => 'Garden Centre & Landscaping',
            'feed_store'            => 'Feed Store',
            'auto_parts'            => 'Automotive Parts & Accessories',
            'tyre_shop'             => 'Tyre Shop',
            'car_dealership'        => 'Car Dealership',
            'printing'              => 'Printing',
            'tailoring'             => 'Tailoring',
            'manufacturer'          => 'Manufacturer',
            'oil_processing_plant'  => 'Processing Plant (Palm / Vegetable Oil)',
            'lpg_gas_business'      => 'LPG / Cooking Gas Business',
            'fuel_station'          => 'Filling Station',
            'bulk_fuel_depot'       => 'Bulk Fuel Depot',
            'lubricants_distributor'=> 'Lubricants & Petroleum Products',
            'oil_gas_supply_services' => 'Oil & Gas Supplies / Services',
            'nylon_polythene'       => 'Nylon & Polythene Manufacturing',
            'scientific_equipment'  => 'Scientific Equipment & Laboratory Supplies',
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
            'memberships'               => 'Memberships',
            'item_variants'             => 'Item Variants (Size, Colour, Style)',
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
            'restaurant_workspace'      => 'RestaurantOS Workspace',
            'event_ticketing'           => 'Event Ticketing (Dated Sessions & Door Codes)',
            'table_management'          => 'Table Management',
            'running_tabs'              => 'Running Tabs (Rounds on One Bill)',
            'wastage_reasons'           => 'Wastage & Breakage Reasons',
            'sale_voids'                => 'Voids (Take a Round off the Bill, Stock Back)',
            'complimentary_items'       => 'Complimentary Items (Rounds on the House)',
            'waiter_assignment'         => 'Waiter Attribution (Who Served Each Round)',
            'tips_gratuity'             => 'Tips (Keyed In, Recorded for the Waiter)',
            'service_charge'            => 'Service Charge (Optional, Added Per Bill)',
            'laundry_workflow'          => 'Laundry Workflow',
            'meat_butchery_workflow'    => 'Meat / Butchery Workflow',
            'frozen_food_cold_chain'    => 'Frozen Food Cold Chain',
            'perfumery_workflow'        => 'Perfumery / Blending Workflow',
            'oil_processing_workflow'   => 'Palm / Vegetable Oil Processing Workspace',
            'oil_gas_workspace'         => 'Oil & Gas Workspace (stations, LPG, depots)',
            'transfer_receipt'          => 'Stock Transfer Arrival Receipt',
            'pump_metering'             => 'Fuel Pumps, Nozzles & Tank Dip Readings',
            'fuel_shift_reconciliation' => 'Forecourt Duty Reconciliation (pump meters vs till vs tank)',
            'bulk_dispatch'             => 'Bulk Fuel Dispatch & Tank Filling',
            'lpg_refills'               => 'LPG Cylinder Refills (sold by net weight)',
            'cylinder_tracking'         => 'Cylinder Register, Custody & Exchange',
            'cylinder_deposits'         => 'Refundable Cylinder Deposits',
            'oil_gas_service_jobs'      => 'Equipment Service Work (install / maintain / repair)',
            'nylon_workflow'            => 'Nylon / Polythene Production Module',
            'nylon_extrusion'           => 'Nylon: In-house Film Extrusion',
            'nylon_conversion'          => 'Nylon: Film-to-Bag Conversion',
            'nylon_roll_trading'        => 'Nylon: Film Roll Trading',
            'automobile_workflow'       => 'Automobile / Vehicle Management',
            'treatment_notes'           => 'Treatment Notes',
            'staff_assignment'          => 'Staff Assignment',
            'staff_commission'          => 'Staff Commission',
            'delivery_scheduling'       => 'Delivery Scheduling',
            'production_workflow'       => 'Production Workflow',
            'printing_workflow'         => 'Print Shop Module',
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
            'fashion_measurements'      => 'Fashion: Customer Measurement Notebook',
            'fashion_size_requests'     => 'Fashion: Shareable Size Requests',
            'auto_retail_markup'        => 'Automatic Retail Pricing (Wholesale + Markup)',
            'pos_retail_button'         => 'POS Retail Price Button',
            'pos_wholesale_button'      => 'POS Wholesale Price Button',
            'pos_receipt_reprint'       => 'Reprint Receipts from POS Sales List',
            'customer_contacts'         => 'Customer Contacts (Multiple Contacts per Organisation)',
            'customer_sites'            => 'Customer Sites (Multiple Delivery / Service Sites)',
            'equipment_register'        => 'Customer Equipment Register',
            'service_jobs'              => 'Service Jobs (Install / Calibrate / Maintain)',
            'patient_registry'          => 'Patient Registry & Episodes',
            'clinical_encounters'       => 'Clinical Encounters & Vitals',
            'clinical_assessments'      => 'Assessments & Investigations',
            'treatment_plans'           => 'Treatment Plans & Sessions',
            'patient_wallet'            => 'Patient Funds & Reservations',
            'patient_documents'         => 'Patient Documents & Consent',
            'inpatient_care'            => 'Inpatient Care (Admissions & Beds)',
            'patient_portal'            => 'Patient Portal',
            'patient_experience'        => 'Patient Feedback & Testimonials',
            /*
             * Operating-model switches. These are the shapes of business that
             * used to collide, because there was no per-store switch for them:
             * a pharmacy that dispenses, a pharmacy that only shelves, a
             * multi-branch chain, a shop selling a vendor's stock, and a
             * business that sells to approved trade accounts. Each is OFF by
             * default except where a preset below declares it, and every gate
             * reads it store-scoped.
             */
            'prescription_required'     => 'Prescription check before dispensing',
            'recall_management'         => 'Recalls & batch quarantine',
            'b2b_trade_accounts'        => 'Trade accounts (approved businesses ordering)',
            'vendor_owned_stock'        => 'Vendor-owned stock (sell stock we do not own)',
            'id_card'                   => 'Customer ID cards',
        ];
    }
}

/* ================================================================
   FEATURE FLAG ARCHITECTURE
   ----------------------------------------------------------------
   One registry describes every capability switch: which clean-labelled
   section it belongs to, which kinds of business it is relevant to, and
   whether every store should see it. Business Profile draws its tabs
   from this, so a section can no longer be a hand-maintained list that
   drifts away from the flags the code actually reads.

   Why relevance-based display: a single flat wall of 80 switches means
   a pharmacy operator hunting for the dispensing check, and a print
   shop seeing "Inpatient Care". Worse, the switches that were NOT
   listed here turned out to be the ones controllers enforced — so the
   "enable it in Business Profile" message pointed at a switch that did
   not exist. Both failure modes are guarded now: this registry covers
   every flag, and anything the store actually uses is always shown.
   ================================================================ */

if (!function_exists('mp_get_business_families')) {
    /**
     * Which kinds of business each family covers. A business type can belong
     * to several (a supermarket sells food and retail stock; a physio clinic
     * runs a services desk and keeps patient records).
     */
    function mp_get_business_families() {
        return [
            'retail'   => ['general_retail','supermarket','mini_mart','grocery_store','provision_store','convenience_store','fashion','boutique','shoe_store','jewellery_store','bookshop','electronics','computer_store','gadget_store','appliance_store','phone_accessories','furniture','building_materials','paint_store','plumbing_store','perfume_shop','skincare','beauty_cosmetics','makeup_studio','multi_branch_retail','online_store','agro_dealer','feed_store','medical_store','pharmacy','supplements_wellness'],
            'food'     => ['restaurant','fast_food','cafe','pizza_shop','shawarma','juice_bar','buka','canteen','bar_lounge','bakery_cake_studio','butcher','frozen_foods_retailer','supermarket','mini_mart','grocery_store','provision_store','convenience_store'],
            'pharmacy' => ['pharmacy','medical_store'],
            'clinical' => ['clinic','hospital','diagnostic_centre','physiotherapy_rehabilitation'],
            'beauty'   => ['beauty_cosmetics','makeup_studio','makeup_artist','skincare','perfume_shop','salon_barbershop','beauty_spa'],
            'services' => ['laundry','service_business','salon_barbershop','beauty_spa','makeup_artist','garden_centre_landscaping','scientific_equipment','car_dealership','physiotherapy_rehabilitation','clinic','hospital','diagnostic_centre'],
            'production'=>['manufacturer','nylon_polythene','oil_processing_plant','printing','bakery_cake_studio','butcher','tailoring','agro_dealer','feed_store'],
            'trade'    => ['distributor','wholesaler','manufacturer','multi_branch_retail','building_materials','agro_dealer','feed_store','scientific_equipment','oil_processing_plant','nylon_polythene','printing'],
            'auto'     => ['auto_parts','tyre_shop','car_dealership'],
            'digital'  => ['creator','online_store'],
            'fuel'     => ['lpg_gas_business','fuel_station','bulk_fuel_depot','lubricants_distributor','oil_gas_supply_services'],
        ];
    }
}

if (!function_exists('mp_business_families')) {
    /** The families a business type belongs to (empty for an unknown type). */
    function mp_business_families($industry_type) {
        $industry_type = strtolower(trim((string)$industry_type));
        if ($industry_type === '') return [];
        $families = [];
        foreach (mp_get_business_families() as $family => $industries) {
            if (in_array($industry_type, $industries, true)) $families[] = $family;
        }
        return $families;
    }
}

if (!function_exists('mp_vehicle_register_mode')) {
    /**
     * True for auto trades that run db_vehicles as a CUSTOMER-vehicle register
     * (fitments, service history, POS "fit to vehicle") rather than as
     * cars-for-sale. Dealership furniture (price, sell, status) is hidden for
     * these industries; car_dealership keeps it.
     */
    function mp_vehicle_register_mode($industry_type = null) {
        if ($industry_type === null) {
            $industry_type = mp_get_store_profile()['industry_type'] ?? '';
        }
        return in_array(strtolower(trim((string)$industry_type)), ['tyre_shop', 'auto_parts'], true);
    }
}

if (!function_exists('mp_get_feature_sections')) {
    /**
     * The labelled sections the Capabilities screen renders, in order.
     * 'families' is the whole section's relevance; a flag can narrow it further.
     * 'controls' names non-checkbox controls that live in this section (they
     * save somewhere other than feature_flags_json, so they are not flags).
     */
    function mp_get_feature_sections() {
        return [
            'core'       => ['label'=>'Core modules', 'blurb'=>'The parts of MartPoint this business runs on.', 'always'=>true],
            'selling'    => ['label'=>'Selling & online store', 'blurb'=>'How customers buy from you, in person or online.'],
            'counter'    => ['label'=>'Counter (POS)', 'blurb'=>'Switches that change what the till offers.'],
            'inventory'  => ['label'=>'Products & inventory', 'blurb'=>'How stock is recorded, packed and tracked.'],
            'pharmacy'   => ['label'=>'Pharmacy & dispensing', 'blurb'=>'Prescription checks and medicine safety.', 'families'=>['pharmacy']],
            'rotation'   => ['label'=>'Stock rotation', 'blurb'=>'Choose which recorded batch is used when stock sells.', 'families'=>['pharmacy'], 'industries'=>['frozen_foods_retailer','supplements_wellness'], 'controls'=>['rotation_method']],
            'clinical'   => ['label'=>'Clinic & patient care', 'blurb'=>'Patient records, episodes, plans and beds.', 'families'=>['clinical']],
            'services'   => ['label'=>'Services & appointments', 'blurb'=>'Work you book, quote and carry out for customers.', 'families'=>['services','clinical','beauty','auto']],
            'food'       => ['label'=>'Food & drink operations', 'blurb'=>'Kitchen, tables, tabs and service rounds.', 'families'=>['food']],
            'fuel'       => ['label'=>'Oil & gas operations', 'blurb'=>'Gas refills, cylinders, tanks, pumps, shifts and bulk dispatch.', 'families'=>['fuel']],
            'production' => ['label'=>'Production & manufacturing', 'blurb'=>'Making goods from components, with workspaces for each process.', 'families'=>['production']],
            'trade'      => ['label'=>'Wholesale & trade accounts', 'blurb'=>'Selling to approved businesses, and stock you hold for others.', 'families'=>['trade','production']],
            'customers'  => ['label'=>'Customers & records', 'blurb'=>'Contact details, notes and identity kept per customer.', 'always'=>true],
            'people'     => ['label'=>'Staff & approvals', 'blurb'=>'Who did the work, who signs it off.'],
        ];
    }
}

if (!function_exists('mp_get_feature_registry')) {
    /**
     * flag_key => ['section'=>, 'industries'=>, 'families'=>, 'always'=>, 'label'=>, 'hint'=>]
     * 'industries' names exact business types (a workflow only that trade runs);
     * 'families' covers a whole kind of business. Every key in
     * mp_get_feature_flags() must appear here exactly once.
     */
    function mp_get_feature_registry() {
        return [
        // Core modules — every business decides these.
        'accounts'                 => ['section'=>'core', 'always'=>true],
        'warehouse'                => ['section'=>'core', 'always'=>true],
        'multi_store'              => ['section'=>'core', 'always'=>true],
        'manager_approvals'        => ['section'=>'core', 'always'=>true],
        'cashier_shifts'           => ['section'=>'core', 'always'=>true],

        // Selling & storefront.
        'online_store'             => ['section'=>'selling', 'always'=>true],
        'public_catalogue'         => ['section'=>'selling', 'always'=>true],
        'price_catalogue'          => ['section'=>'selling', 'always'=>true],
        'leads'                    => ['section'=>'selling', 'always'=>true],
        'loyalty'                  => ['section'=>'selling', 'families'=>['retail','food','beauty','services','digital']],
        'gift_cards'               => ['section'=>'selling', 'families'=>['retail','food','digital']],
        'store_credit'             => ['section'=>'selling', 'families'=>['retail','food','services','digital']],
        'payplan'                  => ['section'=>'selling', 'families'=>['retail','trade','services','clinical']],
        'qr_ordering'              => ['section'=>'selling', 'families'=>['food']],
        'digital_products'         => ['section'=>'selling', 'families'=>['digital','retail']],
        'courses'                  => ['section'=>'selling', 'families'=>['digital','services']],
        'manual_shipping'          => ['section'=>'selling', 'families'=>['retail','digital','trade','food','services']],

        'auto_retail_markup'       => ['section'=>'inventory', 'families'=>['retail','trade','pharmacy','production'], 'hint'=>'Calculate retail price from wholesale plus your store markup. Off by default; purchase cost stays independent.'],

        // Counter.
        'pos_retail_button'        => ['section'=>'counter', 'always'=>true],
        'pos_wholesale_button'     => ['section'=>'counter', 'families'=>['trade','retail']],
        'pos_receipt_reprint'      => ['section'=>'counter', 'always'=>true],

        // Products & inventory.
        'item_variants'            => ['section'=>'inventory', 'families'=>['retail','beauty','food','auto']],
        'fashion_variants_default' => ['section'=>'inventory', 'industries'=>['fashion','boutique','shoe_store','tailoring']],
        'fashion_measurements'     => ['section'=>'services', 'industries'=>['fashion','boutique','tailoring'], 'label'=>'Fashion Measurement Notebook', 'hint'=>'Save dated measurement snapshots against each customer, including dress type and size standard.'],
        'fashion_size_requests'    => ['section'=>'selling', 'industries'=>['fashion','boutique','tailoring'], 'label'=>'Shareable Size Request Links', 'hint'=>'Let a customer submit measurements remotely. Size standards can be set to UK or US.'],
        'multi_unit_inventory'     => ['section'=>'inventory', 'families'=>['retail','food','trade','production','pharmacy','clinical']],
        'multi_unit_selling'       => ['section'=>'inventory', 'families'=>['pharmacy','food','trade','retail','production'], 'hint'=>'Sell the pack, the box and the loose piece, and stock counts itself in base units.'],
        'batch_tracking'           => ['section'=>'inventory', 'families'=>['pharmacy','food','production','clinical'], 'industries'=>['supplements_wellness'], 'hint'=>'Record the batch number on every unit that arrives, so one shelf date can be traced and pulled.'],
        'expiry_tracking'          => ['section'=>'inventory', 'families'=>['pharmacy','food','production','beauty'], 'industries'=>['supplements_wellness']],
        'mfg_tracking'             => ['section'=>'inventory', 'families'=>['pharmacy','food','production'], 'industries'=>['supplements_wellness']],
        'serial_number_tracking'   => ['section'=>'inventory', 'families'=>['retail','auto']],
        'imei_tracking'            => ['section'=>'inventory', 'industries'=>['electronics','computer_store','gadget_store','phone_accessories','appliance_store']],
        'warranty_tracking'        => ['section'=>'inventory', 'industries'=>['electronics','computer_store','gadget_store','appliance_store','phone_accessories','furniture','car_dealership','auto_parts','tyre_shop']],
        'wastage_reasons'          => ['section'=>'inventory', 'families'=>['food','production','pharmacy','clinical','trade'], 'hint'=>'Record why stock left the shelf without being sold.'],
        'delivery_scheduling'      => ['section'=>'inventory', 'families'=>['retail','food','services','trade','digital','clinical']],

        // Pharmacy & dispensing.
        'prescription_required'    => ['section'=>'pharmacy', 'hint'=>'A sale of a prescription-only medicine is refused until an approved prescription for that patient is linked to it.'],
        'medical_notes'            => ['section'=>'pharmacy', 'label'=>'Prescription records', 'hint'=>'The pharmacist reviews and approves each prescription here.'],
        'recall_management'        => ['section'=>'pharmacy', 'hint'=>'Freeze a batch across every location and see the invoices it already reached.'],

        // Clinic & patient care.
        'patient_registry'         => ['section'=>'clinical'],
        'clinical_encounters'      => ['section'=>'clinical'],
        'clinical_assessments'     => ['section'=>'clinical'],
        'treatment_plans'          => ['section'=>'clinical'],
        'patient_wallet'           => ['section'=>'clinical'],
        'patient_documents'        => ['section'=>'clinical'],
        'inpatient_care'           => ['section'=>'clinical'],
        'patient_portal'           => ['section'=>'clinical'],
        'patient_experience'       => ['section'=>'clinical'],
        'treatment_notes'          => ['section'=>'clinical'],

        // Services & appointments.
        'appointments'             => ['section'=>'services'],
        'service_workflow'         => ['section'=>'services'],
        'custom_orders'            => ['section'=>'services'],
        'packages'                 => ['section'=>'services'],
        'memberships'              => ['section'=>'services'],
        'service_jobs'             => ['section'=>'services'],
        'equipment_register'       => ['section'=>'services'],
        'laundry_workflow'         => ['section'=>'services', 'industries'=>['laundry','tailoring']],
        'automobile_workflow'      => ['section'=>'services', 'families'=>['auto']],
        'auto_parts'               => ['section'=>'services', 'families'=>['auto']],

        // Food & drink.
        'restaurant_workspace'     => ['section'=>'food'],
        'kitchen_workflow'         => ['section'=>'food'],
        'table_management'         => ['section'=>'food'],
        'running_tabs'             => ['section'=>'food'],
        'sale_voids'               => ['section'=>'food'],
        'complimentary_items'      => ['section'=>'food'],
        'waiter_assignment'        => ['section'=>'food'],
        'tips_gratuity'            => ['section'=>'food'],
        'service_charge'           => ['section'=>'food'],
        'event_ticketing'          => ['section'=>'food', 'families'=>['food','services','digital']],
        'meat_butchery_workflow'   => ['section'=>'food', 'industries'=>['butcher','supermarket','frozen_foods_retailer','restaurant']],
        'frozen_food_cold_chain'   => ['section'=>'food', 'industries'=>['frozen_foods_retailer','supermarket','butcher','restaurant','cafe']],

        // Production & manufacturing.
        'production_workflow'      => ['section'=>'production', 'families'=>['production']],
        'recipe_tracking'          => ['section'=>'production', 'families'=>['production','food']],
        'printing_workflow'        => ['section'=>'production', 'industries'=>['printing','tailoring','manufacturer']],
        'perfumery_workflow'       => ['section'=>'production', 'industries'=>['perfume_shop','beauty_cosmetics','skincare','makeup_studio']],
        'nylon_workflow'           => ['section'=>'production', 'industries'=>['nylon_polythene']],
        'nylon_extrusion'          => ['section'=>'production', 'industries'=>['nylon_polythene']],
        'nylon_conversion'         => ['section'=>'production', 'industries'=>['nylon_polythene']],
        'nylon_roll_trading'       => ['section'=>'production', 'industries'=>['nylon_polythene']],
        'oil_processing_workflow'  => ['section'=>'production', 'industries'=>['oil_processing_plant']],

        // Oil & gas. One workspace; each operating model switches on its own so
        // a gas retail outlet is not shown pump meters it does not have.
        'oil_gas_workspace'        => ['section'=>'fuel', 'hint'=>'The base workspace: gas and fuel stock, sales and purchases for this business.'],
        'lpg_refills'              => ['section'=>'fuel', 'hint'=>'Fill cylinders by net weight in kg and charge for the gas.'],
        'cylinder_tracking'        => ['section'=>'fuel', 'hint'=>'Keep a register of every cylinder, who owns it and whether it is full, empty or out for exchange.'],
        'cylinder_deposits'        => ['section'=>'fuel', 'hint'=>'Record the refundable deposit held on a customer\'s cylinders, kept apart from gas stock.'],
        'fuel_tanks'               => ['section'=>'fuel', 'hint'=>'Storage tanks per location, with capacity and physical dip readings.'],
        'pump_metering'            => ['section'=>'fuel', 'hint'=>'Cumulative litre readings for each pump and nozzle.'],
        'fuel_shift_reconciliation' => ['section'=>'fuel', 'hint'=>'Open and close the station shift on meter readings, and approve any difference before the till closes.'],
        'bulk_dispatch'            => ['section'=>'fuel', 'hint'=>'Bulk receipts, stored volume, loading and dispatch with delivered-quantity reconciliation.'],
        'oil_gas_service_jobs'     => ['section'=>'fuel', 'hint'=>'Equipment and spare-part supply, installation and maintenance jobs quoted and invoiced.'],

        // Wholesale & trade accounts.
        'b2b_trade_accounts'       => ['section'=>'trade', 'hint'=>'Approved businesses order on their own account, with their own prices and payment terms.'],
        'vendor_owned_stock'       => ['section'=>'trade', 'hint'=>'Shelf stock that belongs to a vendor until it sells; settle them from what sold.'],

        // Customers & records.
        'customer_notes'           => ['section'=>'customers', 'always'=>true],
        'id_card'                  => ['section'=>'customers', 'always'=>true],
        'customer_contacts'        => ['section'=>'customers', 'families'=>['trade','services','production','clinical']],
        'customer_sites'           => ['section'=>'customers', 'families'=>['trade','services','production']],

        // Staff & approvals.
        'staff_assignment'         => ['section'=>'people', 'families'=>['services','food','clinical','production']],
        'staff_commission'         => ['section'=>'people', 'families'=>['services','food','retail','clinical']],
        ];
    }
}

if (!function_exists('mp_get_rotation_methods')) {
    /**
     * Batch rotation choices offered to businesses that track stock by batch. This is a
     * per-store setting in db_inventory_rotation_settings, not a feature flag,
     * so it is declared here and rendered as its own control.
     */
    function mp_get_rotation_methods() {
        return [
            'MANUAL' => ['label'=>'Manual — the clerk picks the batch', 'blurb'=>'Nothing is drawn from the batch ledger automatically. This is the safe default.'],
            'FEFO'   => ['label'=>'Earliest expiry first', 'blurb'=>'Sell the batch that expires soonest. Recommended once batch quantities match total stock.'],
            'FIFO'   => ['label'=>'Oldest received first', 'blurb'=>'Sell the batch received earliest, whatever its expiry date.'],
        ];
    }
}

if (!function_exists('mp_rotation_gap_count')) {
    /**
     * How many medicines this store holds in batches that do not match their
     * stock figures. A shop still on Manual is about to discover this at the
     * till, medicine by medicine, the moment it switches rotation on — so the
     * rotation control shows the number and asks for a deliberate confirmation.
     * Reads as 0 when the reconciliation tool is not installed on this fleet
     * yet, or when rotation is already on and the switch is not a new risk.
     */
    function mp_rotation_gap_count($storeId = null) {
        $ci =& get_instance();
        $storeId = (int)($storeId ?: get_current_store_id());
        if ($storeId <= 0) return 0;
        $ci->load->model('Inventory_rotation_model', 'rotation_gap');
        if ($ci->rotation_gap->method($storeId) !== 'MANUAL') return 0;
        if (!$ci->db->table_exists('db_batch_reconciliations')) return 0;
        $ci->load->model('batch_reconciliation_model', 'recon_gap');
        return (int)$ci->recon_gap->readiness($storeId)['gaps'];
    }
}

if (!function_exists('mp_store_feature_layout')) {
    /**
     * Which switches this business type should be shown, in which sections.
     *
     * A flag is shown when any of these is true:
     *   1. it is marked 'always' — every business decides it;
     *   2. the store's business type recommends it (its preset lists it);
     *   3. it belongs to a family this business type is part of;
     *   4. it is already ON for this store — a switch that is working must
     *      never become unreachable, or the store cannot turn it back off.
     * Everything else is still rendered, but folded away under "Other
     * capabilities" so a business running an unusual combination can find it.
     * Nothing is dropped: every rendered switch posts a seen-marker, so
     * saving one section can never zero another store's setting.
     */
    function mp_store_feature_layout($industry_type = null, $preset_features = null, $saved_flags = array(), $context = array()) {
        $presets = mp_get_business_presets();
        if ($industry_type === null || $industry_type === '') {
            $profile = mp_get_store_profile();
            $industry_type = $profile['industry_type'] ?? 'general_retail';
        }
        if ($preset_features === null) {
            $preset_features = $presets[$industry_type]['features'] ?? [];
        }
        $saved_flags = is_array($saved_flags) ? $saved_flags : array();
        $registry = mp_get_feature_registry();
        $sections = mp_get_feature_sections();
        $labels = mp_get_feature_flags();
        $families = mp_business_families($industry_type);

        $in_section = array();
        $other = array();
        foreach ($registry as $key => $meta) {
            if (!isset($labels[$key])) continue;
            $relevant = !empty($meta['always']) || in_array($key, (array)$preset_features, true);
            $names_trades = !empty($meta['industries']);
            if (!$relevant && $names_trades) {
                $relevant = in_array((string)$industry_type, $meta['industries'], true);
            }
            if (!$relevant && !$names_trades) {
                // A flag that only names a family inherits its section's; a flag
                // that names exact trades has already had its say above.
                $flag_families = !empty($meta['families'])
                    ? $meta['families']
                    : ($sections[$meta['section']]['families'] ?? []);
                if ($flag_families && array_intersect($flag_families, $families)) $relevant = true;
            }
            if (!$relevant && isset($saved_flags[$key]) && filter_var($saved_flags[$key], FILTER_VALIDATE_BOOLEAN)) {
                $relevant = true;
            }
            if (!$relevant) { $other[$key] = $labels[$key]; continue; }
            if (!isset($in_section[$meta['section']])) $in_section[$meta['section']] = array();
            $in_section[$meta['section']][] = array(
                'key'   => $key,
                'label' => !empty($meta['label']) ? $meta['label'] : $labels[$key],
                'hint'  => $meta['hint'] ?? '',
            );
        }

        $layout = array();
        foreach ($sections as $skey => $def) {
            $flags = $in_section[$skey] ?? array();
            $controls = array();
            if (!empty($def['controls'])) {
                $section_relevant = !empty($def['always']) || !empty($flags)
                    || (!empty($def['families']) && array_intersect($def['families'], $families))
                    || (!empty($def['industries']) && in_array((string)$industry_type, $def['industries'], true));
                if (!$section_relevant && !empty($def['controls']) && !empty($context['rotation_method'])
                    && strtoupper((string)$context['rotation_method']) !== 'MANUAL') {
                    $section_relevant = true; // rotation is in use, so its switch must be reachable
                }
                if ($section_relevant) {
                    foreach ($def['controls'] as $control) { $controls[] = $control; }
                }
            }
            if (!$flags && !$controls) continue;
            $layout[$skey] = array('key'=>$skey, 'label'=>$def['label'], 'blurb'=>$def['blurb'], 'flags'=>$flags, 'controls'=>$controls);
        }
        return array('sections'=>$layout, 'other'=>$other);
    }
}

if (!function_exists('mp_merge_feature_flags')) {
    /**
     * Build the feature_flags_json payload from what the form posted.
     *
     * Three cases, because a checkbox that is OFF posts nothing and is
     * therefore indistinguishable from a flag the form never rendered:
     *   1. the form rendered it (marker present) -> submitted value wins,
     *      so an admin can deliberately switch a preset capability off;
     *   2. not rendered but already stored        -> preserve, so saving
     *      one tab can't silently drop flags (a store with a 24-key profile
     *      would otherwise be rewritten down to the rendered subset);
     *   3. never stored                           -> default to the
     *      business-type preset, which is what makes selecting
     *      Physiotherapy turn patient_registry & the clinical set on.
     */
    function mp_merge_feature_flags($posted_flags, $posted_seen, $stored, $preset_features) {
        $flags = is_array($posted_flags) ? $posted_flags : array();
        $seen = is_array($posted_seen) ? $posted_seen : array();
        $stored = is_array($stored) ? $stored : array();
        $preset_features = is_array($preset_features) ? $preset_features : array();

        $clean = array();
        foreach (mp_get_feature_flags() as $key => $label) {
            if (isset($seen[$key])) {
                $clean[$key] = isset($flags[$key]) ? '1' : '0';
            } elseif (isset($stored[$key])) {
                $clean[$key] = $stored[$key];
            } else {
                $clean[$key] = in_array($key, $preset_features, true) ? '1' : '0';
            }
        }
        // A store that predates a capability still gets it when its business
        // type requires it — "select Physiotherapy" must mean the clinical
        // modules are on, not merely available.
        foreach ($preset_features as $key) {
            if (!isset($seen[$key]) && isset($clean[$key]) && $clean[$key] !== '1') {
                $clean[$key] = empty($stored[$key]) ? '1' : $clean[$key];
            }
        }
        return $clean;
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

if (!function_exists('mp_feature_display_label')) {
    /**
     * The wording this store should read for a capability: the registry's clean
     * section label when it gives one ('Prescription records'), otherwise the
     * master label. Used by screens that name a locked capability, so the
     * message always points at a switch the operator can actually find.
     */
    function mp_feature_display_label($flag_key) {
        $registry = mp_get_feature_registry();
        if (isset($registry[$flag_key]['label'])) return $registry[$flag_key]['label'];
        return mp_feature_label($flag_key);
    }
}

if (!function_exists('mp_get_wastage_reasons')) {
    /**
     * Why stock left the shelf without being sold — the wastage twin of the
     * garden module's loss reasons, and shaped the same way (stable code, the
     * words the merchant reads).
     *
     * These are losses only. A drink a guest was served is not wastage — it is a
     * sale, complimentary or not — and a cancelled line never left the shelf at
     * all, so neither belongs in this list.
     */
    function mp_get_wastage_reasons() {
        return [
            'breakage'  => 'Broken',
            'spillage'  => 'Spilled',
            'expiry'    => 'Expired',
            'quality'   => 'Failed quality',
            'shrinkage' => 'Missing / uncounted',
            'other'     => 'Other reason',
        ];
    }
}

if (!function_exists('mp_wastage_reason_label')) {
    /**
     * A code in the words the merchant used to record it. An unknown or empty
     * code returns the not-recorded wording rather than printing a raw code, so
     * movements saved before reasons existed still read as plain English.
     */
    function mp_wastage_reason_label($code) {
        $code = is_string($code) ? trim($code) : '';
        if ($code === '' || !isset(mp_get_wastage_reasons()[$code])) return 'Reason not recorded';
        return mp_get_wastage_reasons()[$code];
    }
}

if (!function_exists('mp_get_label_defaults')) {
    function mp_get_label_defaults() {
        return [
            'warehouse'         => 'Branch',
            'branch'            => 'Branch',
            'item'              => 'Item',
            'sku'               => 'SKU',
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
            'film_roll'         => 'Film Roll',
            'artwork'           => 'Artwork',
            'machine'           => 'Machine',
            'job'               => 'Job',
            'scrap'             => 'Scrap',
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
                'features'=>['accounts','warehouse','online_store','loyalty','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','serial_number_tracking','manager_approvals','price_catalogue','public_catalogue','medical_notes','prescription_required'],
                'theme_key'=>'healthcare_pro','dashboard_template'=>'pharmacy','workflow_template'=>'pharmacy_standard',
                'labels'=>['item'=>'Medicine','batch'=>'Batch','expiry'=>'Expiry Date','customer'=>'Patient'],
            ],
            'restaurant' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','restaurant_workspace','table_management','kitchen_workflow','loyalty','delivery_scheduling','manager_approvals','recipe_tracking','production_workflow','staff_commission','event_ticketing'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                'labels'=>['item'=>'Menu Item','service'=>'Dining Service','table'=>'Table','order'=>'Order','pos'=>'Counter'],
            ],
            'electronics' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','gift_cards','store_credit','serial_number_tracking','imei_tracking','warranty_tracking','manager_approvals','price_catalogue','public_catalogue','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'tech_hub','dashboard_template'=>'electronics','workflow_template'=>'electronics_standard',
                'labels'=>['item'=>'Product','serial'=>'Serial Number','imei'=>'IMEI'],
            ],
            'scientific_equipment' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','warehouse','online_store','customer_notes','serial_number_tracking','warranty_tracking','batch_tracking','expiry_tracking','mfg_tracking','multi_unit_inventory','multi_unit_selling','delivery_scheduling','manager_approvals','price_catalogue','payplan','store_credit','custom_orders','manual_shipping','public_catalogue','pos_retail_button','pos_wholesale_button','leads','customer_contacts','customer_sites','equipment_register','service_jobs'],
                'theme_key'=>'tech_hub','dashboard_template'=>'electronics','workflow_template'=>'electronics_standard',
                'labels'=>['item'=>'Product','customer'=>'Client','purchase'=>'Purchase Order','quote'=>'Quotation','batch'=>'Batch/Lot','serial'=>'Serial Number','warehouse'=>'Warehouse','service_order'=>'Job'],
            ],
            'fashion' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','item_variants','online_store','qr_ordering','loyalty','gift_cards','store_credit','multi_unit_inventory','public_catalogue','fashion_variants_default','fashion_measurements','fashion_size_requests'],
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
            'makeup_studio' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','warehouse','online_store','qr_ordering','appointments','service_workflow','custom_orders','packages','memberships','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','treatment_notes','multi_unit_inventory','batch_tracking','expiry_tracking','price_catalogue','public_catalogue','pos_retail_button','pos_wholesale_button','leads'],
                'theme_key'=>'glam_atelier','dashboard_template'=>'makeup_studio','workflow_template'=>'makeup_studio_standard',
                'labels'=>['service'=>'Makeup Service','service_order'=>'Booking','customer'=>'Client','staff'=>'Artist','custom_order'=>'Custom Booking','item'=>'Product','package'=>'Beauty Package','membership'=>'Membership'],
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
                'features'=>['accounts','warehouse','online_store','qr_ordering','loyalty','multi_unit_inventory','batch_tracking','delivery_scheduling','manager_approvals','price_catalogue','public_catalogue','pos_retail_button','pos_wholesale_button','leads','customer_contacts','customer_sites','staff_assignment','equipment_register','service_jobs','service_workflow','serial_number_tracking','warranty_tracking'],
                'theme_key'=>'framework_atelier','dashboard_template'=>'building_materials','workflow_template'=>'wholesale_standard',
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
                'features'=>['accounts','online_store','qr_ordering','restaurant_workspace','table_management','kitchen_workflow','loyalty','delivery_scheduling','manager_approvals','recipe_tracking','production_workflow','staff_commission'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                'labels'=>['item'=>'Menu Item','service'=>'Fast Food Service','table'=>'Counter','order'=>'Order','pos'=>'Counter'],
            ],
            'cafe' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','restaurant_workspace','table_management','kitchen_workflow','loyalty','delivery_scheduling','manager_approvals','recipe_tracking','production_workflow','staff_commission'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                'labels'=>['item'=>'Menu Item','service'=>'Cafe Service','table'=>'Table','order'=>'Order','pos'=>'Counter'],
            ],
            'pizza_shop' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','restaurant_workspace','table_management','kitchen_workflow','loyalty','delivery_scheduling','manager_approvals','recipe_tracking','production_workflow','staff_commission'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                'labels'=>['item'=>'Pizza','service'=>'Pizza Service','table'=>'Table','order'=>'Order','pos'=>'Counter'],
            ],
            'shawarma' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','restaurant_workspace','table_management','kitchen_workflow','loyalty','delivery_scheduling','manager_approvals','recipe_tracking','production_workflow','staff_commission'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                'labels'=>['item'=>'Shawarma','service'=>'Grill Service','table'=>'Counter','order'=>'Order','pos'=>'Counter'],
            ],
            'juice_bar' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','custom_orders','restaurant_workspace','table_management','kitchen_workflow','loyalty','delivery_scheduling','recipe_tracking','staff_commission'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                'labels'=>['item'=>'Beverage','service'=>'Juice Service','table'=>'Counter','order'=>'Order','pos'=>'Counter'],
            ],
            'buka' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','restaurant_workspace','table_management','kitchen_workflow','loyalty','delivery_scheduling','manager_approvals','recipe_tracking','production_workflow','staff_commission'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                'labels'=>['item'=>'Menu Item','service'=>'Buka Service','table'=>'Table','order'=>'Order','pos'=>'Counter'],
            ],
            'canteen' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','qr_ordering','restaurant_workspace','table_management','kitchen_workflow','loyalty','delivery_scheduling','manager_approvals','recipe_tracking','production_workflow','staff_commission'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                'labels'=>['item'=>'Menu Item','service'=>'Canteen Service','table'=>'Table','order'=>'Meal','pos'=>'Counter'],
            ],
            // Bar/Lounge starts drinks-first: no online store, QR ordering or
            // delivery forced on; food stays available by switching kitchen
            // flags on under Feature Flags. Every flag here gates a workflow
            // that ships today: running tabs (4.0.9.227), reason-coded
            // wastage on the phone as well as the desk (4.0.9.228), voids and
            // compliments (4.0.9.230), waiter attribution with tips and an
            // optional service charge (4.0.9.231), sections that say which
            // tables a waiter is behind (4.0.9.235) and event nights
            // (4.0.9.221).
            'bar_lounge' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','warehouse','multi_unit_inventory','multi_unit_selling','restaurant_workspace','table_management','running_tabs','wastage_reasons','sale_voids','complimentary_items','waiter_assignment','tips_gratuity','service_charge','kitchen_workflow','recipe_tracking','manager_approvals','cashier_shifts','loyalty','store_credit','event_ticketing'],
                'theme_key'=>'food_express','dashboard_template'=>'restaurant','workflow_template'=>'restaurant_standard',
                // 'workspace' is the word this business uses on its own signs. The
                // lounge rides the restaurant rails, so every heading that once
                // said "Restaurant …" verbatim would call a bar a restaurant.
                'labels'=>['item'=>'Menu Item','service'=>'Bar Service','table'=>'Table','order'=>'Order','pos'=>'Bar','workspace'=>'Lounge'],
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
                'features'=>['accounts','warehouse','online_store','loyalty','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','serial_number_tracking','manager_approvals','price_catalogue','public_catalogue','medical_notes','prescription_required'],
                'theme_key'=>'healthcare_pro','dashboard_template'=>'pharmacy','workflow_template'=>'pharmacy_standard',
                'labels'=>['item'=>'Medical Item','batch'=>'Batch','expiry'=>'Expiry Date','customer'=>'Patient'],
            ],
            'supplements_wellness' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','loyalty','memberships','gift_cards','store_credit','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','mfg_tracking','manager_approvals','price_catalogue','public_catalogue','delivery_scheduling'],
                'theme_key'=>'general_retail','dashboard_template'=>'supplements_wellness','workflow_template'=>'retail_standard',
                'labels'=>['item'=>'Product','warehouse'=>'Branch','batch'=>'Batch','expiry'=>'Best Before'],
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
            'physiotherapy_rehabilitation' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','warehouse','leads','appointments','service_workflow','packages','store_credit','staff_assignment','manager_approvals','price_catalogue','customer_notes','patient_registry','clinical_encounters','clinical_assessments','treatment_plans','patient_wallet','patient_documents','inpatient_care','patient_portal','patient_experience'],
                'theme_key'=>'healthcare_pro','dashboard_template'=>'service_business','workflow_template'=>'service_standard',
                'labels'=>['service'=>'Treatment','service_order'=>'Appointment','customer'=>'Patient','staff'=>'Therapist','package'=>'Session Package','warehouse'=>'Branch'],
            ],
            'boutique' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','item_variants','online_store','qr_ordering','loyalty','gift_cards','store_credit','multi_unit_inventory','public_catalogue','fashion_variants_default','fashion_measurements','fashion_size_requests'],
                'theme_key'=>'urban_fashion','dashboard_template'=>'fashion','workflow_template'=>'retail_standard',
                'labels'=>['item'=>'Item','category'=>'Collection','customer'=>'Customer','warehouse'=>'Branch'],
            ],
            'shoe_store' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','item_variants','online_store','qr_ordering','loyalty','gift_cards','store_credit','multi_unit_inventory','public_catalogue','fashion_variants_default'],
                'theme_key'=>'urban_fashion','dashboard_template'=>'fashion','workflow_template'=>'retail_standard',
                'labels'=>['item'=>'Shoe','category'=>'Category','customer'=>'Customer','warehouse'=>'Branch'],
            ],
            'perfume_shop' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','warehouse','online_store','qr_ordering','custom_orders','loyalty','gift_cards','store_credit','item_variants','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','mfg_tracking','production_workflow','recipe_tracking','perfumery_workflow','manager_approvals','price_catalogue','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'noir_parfum','dashboard_template'=>'perfume_shop','workflow_template'=>'perfumery_standard',
                'labels'=>['item'=>'Fragrance','product'=>'Fragrance','service'=>'Blending Service','service_order'=>'Bespoke Order','customer'=>'Client','staff'=>'Perfumer','category'=>'Fragrance Family','brand'=>'House','batch'=>'Batch Code','recipe'=>'Formula','production'=>'Blend Batch','custom_order'=>'Bespoke Blend','expiry'=>'Best Before','mfg'=>'Blended On','warehouse'=>'Branch'],
            ],
            'skincare' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','warehouse','online_store','qr_ordering','appointments','service_workflow','custom_orders','packages','item_variants','loyalty','gift_cards','store_credit','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','mfg_tracking','production_workflow','recipe_tracking','staff_assignment','staff_commission','treatment_notes','delivery_scheduling','manager_approvals','price_catalogue','public_catalogue','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'verdant','dashboard_template'=>'skincare','workflow_template'=>'skincare_standard',
                'labels'=>['item'=>'Product','product'=>'Product','service'=>'Treatment','service_order'=>'Booking','customer'=>'Client','staff'=>'Therapist','category'=>'Range','brand'=>'Brand','batch'=>'Batch','expiry'=>'Best Before','mfg'=>'Made On','recipe'=>'Formula','production'=>'Batch Run','custom_order'=>'Custom Formulation','appointment'=>'Consultation','package'=>'Skincare Set','warehouse'=>'Branch'],
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
            'garden_centre_landscaping' => [
                'canonical_key'=>'GARDEN_CENTRE_LANDSCAPING',
                // [implementation status, verification evidence, route, entitlement]
                // Public claims remain disabled until full journey acceptance.
                'capabilities'=>[
                    'general_product_sales'=>['IMPLEMENTED BUT UNVERIFIED','shared_engine_audit','items','product_limit'],
                    'living_plant_inventory'=>['PARTIAL','isolated_stock_reconciliation','garden/nursery','product_limit'],
                    'nursery_production'=>['PARTIAL','isolated_production_stock_cost_and_http','garden/production','active_subscription'],
                    'materials_costing'=>['PARTIAL','isolated_production_stock_cost_and_http','garden/materials','active_subscription'],
                    'product_variants'=>['IMPLEMENTED BUT UNVERIFIED','shared_engine_audit','items','sku_limit'],
                    'accessories'=>['IMPLEMENTED BUT UNVERIFIED','three_theme_http','garden/catalogue','product_limit'],
                    'pos'=>['IMPLEMENTED BUT UNVERIFIED','shared_engine_audit','pos','active_subscription'],
                    'online_store'=>['PARTIAL','three_theme_http','online_store','online_product_limit'],
                    'mixed_product_service_sales'=>['PARTIAL','shared_engine_audit','pos','service_limit_existing_pos_only'],
                    'service_bookings'=>['PARTIAL','crm_request_http','garden/maintenance','service_limit_existing_pos_only'],
                    'landscaping_enquiries'=>['PARTIAL','crm_request_http','leads','active_subscription'],
                    'landscaping_quotations'=>['PARTIAL','source_link_and_stage_checks','garden/projects','active_subscription'],
                    'deposits'=>['IMPLEMENTED BUT UNVERIFIED','source_payment_stage_checks','sales','active_subscription'],
                    'project_costing'=>['PARTIAL','issued_cost_and_expense_checks','garden/projects','active_subscription'],
                    'recurring_maintenance'=>['PARTIAL','recurrence_replay_checks','garden/maintenance','service_limit_existing_pos_only'],
                    'shared_inventory'=>['PARTIAL','isolated_stock_reconciliation','inventory','active_subscription'],
                    'delivery'=>['IMPLEMENTED BUT UNVERIFIED','shared_engine_audit','online_store/orders','online_product_limit'],
                    'customer_balances'=>['IMPLEMENTED BUT UNVERIFIED','source_payment_stage_checks','customers','active_subscription'],
                    'business_reporting'=>['PARTIAL','source_cost_checks','reports','active_subscription'],
                ],
                'operating_modes'=>[
                    'retail'=>['label'=>'Retail & Accessories','features'=>['pos_retail_button','pos_wholesale_button']],
                    'nursery'=>['label'=>'Plant Nursery','features'=>['batch_tracking']],
                    'landscaping'=>['label'=>'Landscaping Projects','features'=>['custom_orders','service_jobs','leads']],
                    'maintenance'=>['label'=>'Garden Maintenance & Services','features'=>['service_workflow','service_jobs','staff_assignment']],
                    'combined'=>['label'=>'Combined Operations','features'=>[]],
                ],
                'business_model'=>'product_and_service',
                'features'=>['production_workflow','recipe_tracking','accounts','warehouse','online_store','multi_unit_inventory','multi_unit_selling','item_variants','batch_tracking','service_workflow','service_jobs','custom_orders','staff_assignment','delivery_scheduling','customer_notes','customer_contacts','customer_sites','manager_approvals','price_catalogue','public_catalogue','pos_retail_button','pos_wholesale_button','leads'],
                // Use the existing commerce/service screens until the dedicated workspace ships.
                'theme_key'=>'botanical_elegance','dashboard_template'=>'general_retail','workflow_template'=>'service_standard',
                'labels'=>['recipe'=>'Materials template','production'=>'Nursery production','item'=>'Product','product'=>'Product','service'=>'Service','service_order'=>'Service Job','appointment'=>'Site Visit','custom_order'=>'Landscaping Order','customer'=>'Client','warehouse'=>'Branch','staff'=>'Team Member'],
            ],
            'feed_store' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','payplan','store_credit','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','manager_approvals','price_catalogue','public_catalogue','delivery_scheduling','customer_notes','cashier_shifts','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'distributor','workflow_template'=>'distributor_standard',
                'labels'=>['item'=>'Feed','batch'=>'Batch','expiry'=>'Expiry Date','customer'=>'Client','warehouse'=>'Depot','order'=>'Order'],
            ],
            // Shares the auto-family engine with Tyre Shop (customer vehicles,
            // fitment, variants, warranty/serial tracking, fitting services);
            // keeps the distributor dashboard — the tyre seasonal block is
            // tyre-size specific.
            'auto_parts' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','warehouse','online_store','item_variants','payplan','store_credit','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','serial_number_tracking','warranty_tracking','service_workflow','appointments','staff_assignment','staff_commission','customer_contacts','customer_sites','automobile_workflow','auto_parts','manager_approvals','price_catalogue','public_catalogue','delivery_scheduling','customer_notes','cashier_shifts','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'distributor','workflow_template'=>'distributor_standard',
                'labels'=>['item'=>'Auto Part','batch'=>'Batch/DOT Code','expiry'=>'Stock Age Check','service'=>'Fitting Service','service_order'=>'Fitting Job','customer'=>'Customer','warehouse'=>'Depot','order'=>'Order'],
            ],
            'tyre_shop' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','warehouse','online_store','item_variants','payplan','store_credit','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','serial_number_tracking','warranty_tracking','service_workflow','appointments','staff_assignment','staff_commission','customer_contacts','customer_sites','automobile_workflow','auto_parts','manager_approvals','price_catalogue','public_catalogue','delivery_scheduling','customer_notes','cashier_shifts','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'tyre_shop','workflow_template'=>'tyre_shop_standard',
                'labels'=>['item'=>'Tyre','batch'=>'Batch/DOT Code','expiry'=>'Stock Age Check','service'=>'Fitting Service','service_order'=>'Fitting Job','customer'=>'Customer','warehouse'=>'Depot','order'=>'Order'],
            ],
            'car_dealership' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','payplan','store_credit','automobile_workflow','auto_parts','service_workflow','price_catalogue','public_catalogue','delivery_scheduling','customer_notes','cashier_shifts','pos_retail_button','pos_wholesale_button','leads'],
                'theme_key'=>'general_retail','dashboard_template'=>'distributor','workflow_template'=>'distributor_standard',
                'labels'=>['item'=>'Auto Part','customer'=>'Buyer','warehouse'=>'Branch','order'=>'Order'],
            ],
            'printing' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','warehouse','online_store','custom_orders','production_workflow','printing_workflow','recipe_tracking','delivery_scheduling','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','public_catalogue','pos_retail_button','pos_wholesale_button','leads'],
                'theme_key'=>'general_retail','dashboard_template'=>'service_business','workflow_template'=>'service_standard',
                'labels'=>['service'=>'Print Job','service_order'=>'Print Order','item'=>'Material','customer'=>'Client','recipe'=>'Recipe','production'=>'Production'],
            ],
            'tailoring' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','online_store','custom_orders','appointments','service_workflow','loyalty','gift_cards','store_credit','staff_assignment','staff_commission','public_catalogue','pos_retail_button','pos_wholesale_button','leads','fashion_measurements','fashion_size_requests'],
                'theme_key'=>'general_retail','dashboard_template'=>'service_business','workflow_template'=>'service_standard',
                'labels'=>['service'=>'Tailoring Job','service_order'=>'Order','item'=>'Fabric','customer'=>'Client','staff'=>'Tailor'],
            ],
            'manufacturer' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','online_store','payplan','store_credit','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','production_workflow','recipe_tracking','manager_approvals','price_catalogue','public_catalogue','delivery_scheduling','customer_notes','cashier_shifts','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'distributor','workflow_template'=>'distributor_standard',
                'labels'=>['item'=>'Finished Good','batch'=>'Batch','expiry'=>'Expiry Date','customer'=>'Client','warehouse'=>'Factory','order'=>'Order','recipe'=>'Recipe','production'=>'Production'],
            ],
            'oil_processing_plant' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','mfg_tracking','production_workflow','recipe_tracking','manager_approvals','price_catalogue','delivery_scheduling','customer_notes','oil_processing_workflow'],
                'theme_key'=>'general_retail','dashboard_template'=>'oil_processing_plant','workflow_template'=>'oil_processing_standard',
                'labels'=>['item'=>'Material / Product','product'=>'Finished Oil','batch'=>'Processing Batch','customer'=>'Buyer','supplier'=>'Supplier','warehouse'=>'Plant / Depot','order'=>'Sales Order','recipe'=>'Process Formula','production'=>'Processing Run','quote'=>'Quotation'],
            ],
            /*
             * Oil & gas. Five operating profiles trade from one commerce base, so a
             * mixed company can run a station, an LPG outlet, a depot and a supply
             * division from the same account, and a owner of a single filling station
             * can run just that. Each profile turns on only the screens its trade
             * needs; anything the store does not use stays off.
             */
            'lpg_gas_business' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','warehouse','oil_gas_workspace','transfer_receipt','bulk_dispatch','lpg_refills','cylinder_tracking','cylinder_deposits','multi_unit_inventory','multi_unit_selling','online_store','delivery_scheduling','price_catalogue','manager_approvals','customer_notes','customer_sites','cashier_shifts','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'lpg_gas_business','workflow_template'=>'lpg_gas_business_standard',
                'labels'=>['item'=>'Gas / Product','product'=>'Gas Product','service'=>'Refill','warehouse'=>'Outlet','stock_transfer'=>'Inter-Outlet Transfer','customer'=>'Customer','deposit'=>'Cylinder Deposit'],
            ],
            'fuel_station' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','oil_gas_workspace','transfer_receipt','pump_metering','fuel_shift_reconciliation','bulk_dispatch','multi_unit_inventory','multi_unit_selling','price_catalogue','manager_approvals','customer_notes','staff_assignment','staff_commission','cashier_shifts','pos_retail_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'fuel_station','workflow_template'=>'fuel_station_standard',
                'labels'=>['item'=>'Fuel / Product','product'=>'Fuel Product','warehouse'=>'Station','stock_transfer'=>'Inter-Station Transfer','customer'=>'Customer'],
            ],
            'bulk_fuel_depot' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','oil_gas_workspace','transfer_receipt','bulk_dispatch','multi_unit_inventory','multi_unit_selling','price_catalogue','manager_approvals','delivery_scheduling','customer_notes','customer_sites','cashier_shifts','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'bulk_fuel_depot','workflow_template'=>'bulk_fuel_depot_standard',
                'labels'=>['item'=>'Product','product'=>'Bulk Product','warehouse'=>'Depot','stock_transfer'=>'Inter-Depot Transfer','customer'=>'Trade Customer'],
            ],
            'lubricants_distributor' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','oil_gas_workspace','transfer_receipt','multi_unit_inventory','multi_unit_selling','batch_tracking','expiry_tracking','mfg_tracking','online_store','price_catalogue','public_catalogue','manager_approvals','delivery_scheduling','customer_notes','customer_sites','cashier_shifts','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'lubricants_distributor','workflow_template'=>'lubricants_distributor_standard',
                'labels'=>['item'=>'Product','product'=>'Lubricant / Pack','warehouse'=>'Depot','stock_transfer'=>'Inter-Depot Transfer','customer'=>'Dealer','batch'=>'Batch','expiry'=>'Best Before'],
            ],
            'oil_gas_supply_services' => [
                'business_model'=>'product_and_service',
                'features'=>['accounts','warehouse','oil_gas_workspace','transfer_receipt','pump_metering','fuel_shift_reconciliation','bulk_dispatch','multi_unit_inventory','service_workflow','service_jobs','oil_gas_service_jobs','equipment_register','custom_orders','serial_number_tracking','warranty_tracking','online_store','price_catalogue','manager_approvals','delivery_scheduling','customer_notes','customer_sites','staff_assignment','cashier_shifts','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'oil_gas_supply_services','workflow_template'=>'oil_gas_supply_services_standard',
                'labels'=>['item'=>'Part / Equipment','service'=>'Service','service_order'=>'Service Job','customer'=>'Client','warehouse'=>'Store','stock_transfer'=>'Inter-Store Transfer'],
            ],
            'nylon_polythene' => [
                'business_model'=>'product_based',
                'features'=>['accounts','warehouse','custom_orders','payplan','store_credit','multi_unit_inventory','multi_unit_selling','batch_tracking','manager_approvals','price_catalogue','delivery_scheduling','customer_notes','cashier_shifts','nylon_workflow','nylon_extrusion','nylon_conversion','nylon_roll_trading','pos_retail_button','pos_wholesale_button'],
                'theme_key'=>'general_retail','dashboard_template'=>'nylon_polythene','workflow_template'=>'nylon_polythene_standard',
                'labels'=>['item'=>'Material / Product','batch'=>'Roll Batch','customer'=>'Client','warehouse'=>'Factory','order'=>'Order','custom_order'=>'Job Order','production'=>'Production Job','recipe'=>'Spec','stock_transfer'=>'Factory Transfer'],
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
            'nylon_polythene'       => 'wholesale',
            'multi_branch_retail'   => 'wholesale',
            'building_materials'    => 'framework_atelier',
            'paint_store'           => 'hardware',
            'plumbing_store'        => 'hardware',
            'agro_dealer'           => 'agro',
            'feed_store'            => 'agro',
            'auto_parts'            => 'automotive',
            'tyre_shop'             => 'automotive',
            'car_dealership'        => 'auto_modern',
            'bookshop'              => 'general_retail',
            'furniture'             => 'general_retail',
            // Printing has its own four-theme set (print_works, print_inkpress,
            // print_papercraft, print_neonprint), all with industry = 'printing'.
            //
            // This used to point at 'service_pro'. Because the storefront theme
            // picker resolves a store's theme GROUP by looking up the base
            // theme's industry, printing stores were handed the SERVICES group —
            // so a print shop was offered service_pro and never saw a single one
            // of its own printing themes. Pointing at a theme whose industry is
            // 'printing' is what makes the group resolve correctly.
            'printing'              => 'print_inkpress',
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
            // General commerce capabilities are available to every business
            // type; a store can still disable them via its own feature flags.
            foreach (['loyalty', 'gift_cards', 'store_credit'] as $universal) {
                if (!in_array($universal, $preset['features'], true)) {
                    $preset['features'][] = $universal;
                }
            }
            // Storefront QR codes apply wherever the online store is enabled.
            // Table QR ordering stays gated by table_management.
            if (in_array('online_store', $preset['features'], true) && !in_array('qr_ordering', $preset['features'], true)) {
                $preset['features'][] = 'qr_ordering';
            }
            // Item variants and manual POS delivery fees apply to any business
            // that can sell physical products.
            if (($preset['business_model'] ?? '') !== 'service_based') {
                foreach (['item_variants', 'manual_shipping'] as $product_flag) {
                    if (!in_array($product_flag, $preset['features'], true)) {
                        $preset['features'][] = $product_flag;
                    }
                }
            }
        }
        unset($preset);
        return $presets;
    }
}

if (!function_exists('mp_feature_flag_raw')) {
    function mp_feature_flag_raw($flag_key) {
        $CI =& get_instance();
        $reader = mp_read_db();
        $store_id = get_current_store_id();

        // 1. Prefer modular industry settings table
        if ($reader->table_exists('db_store_industry_settings')) {
            $q = $reader->select('feature_flags_json')
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
        if (!$reader->field_exists('feature_flags_json', 'db_store')) {
            return null;
        }
        $q = $reader->select('feature_flags_json')
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
        // Cached per store: one PHP process can serve several companies (a cron
        // digest, a background loop, a CLI probe), and the first company's
        // profile must not answer for the rest.
        static $profile_features_by_store = null;
        if ($profile_features_by_store === null) { $profile_features_by_store = []; }
        $profile_store_id = (int) get_current_store_id();
        if (!array_key_exists($profile_store_id, $profile_features_by_store)) {
            $profile = mp_get_store_profile($profile_store_id);
            $profile_features_by_store[$profile_store_id] = $profile['features'] ?? [];
        }
        $profile_features = $profile_features_by_store[$profile_store_id];
        switch ($flag_key) {
            case 'service_workflow': case 'appointments': return service_module() && in_array($flag_key, $profile_features);
            case 'accounts': return accounts_module();
            case 'warehouse': case 'multi_unit_inventory': return warehouse_module();
            case 'online_store': case 'qr_ordering': case 'loyalty': case 'gift_cards': case 'store_credit': return in_array($flag_key, $profile_features);
            // Inventory tracking flags - require both the warehouse module and the industry profile
            case 'serial_number_tracking': case 'imei_tracking': case 'warranty_tracking': case 'batch_tracking': case 'expiry_tracking': case 'meat_butchery_workflow': case 'frozen_food_cold_chain': case 'perfumery_workflow': return warehouse_module() && in_array($flag_key, $profile_features);
            // Recording why stock was written off is meaningless without a
            // shelf to write it off from, so it follows the warehouse module.
            case 'wastage_reasons': return warehouse_module() && in_array($flag_key, $profile_features);
            case 'mfg_tracking': return mp_feature_enabled('expiry_tracking');
            // Service flags are independently configurable in Feature Flags.
            // Requiring service_workflow here meant switching treatment_notes
            // on had no effect when a store had service_workflow off, because
            // the saved feature toggle was shadowed by this extra gate.
            case 'treatment_notes': case 'staff_assignment': case 'staff_commission': case 'custom_orders': return in_array($flag_key, $profile_features);
            // Table management is a restaurant/food feature, not a service-workflow feature
            case 'table_management': return in_array($flag_key, $profile_features);
            // Business-driven features
            case 'packages': case 'memberships': case 'kitchen_workflow': case 'laundry_workflow': case 'production_workflow': case 'recipe_tracking': case 'delivery_scheduling': return in_array($flag_key, $profile_features);
            // Sales & storefront
            case 'price_catalogue': case 'public_catalogue': return in_array($flag_key, $profile_features);
            case 'customer_notes': case 'manager_approvals': case 'cashier_shifts': return true;
            // Cashier receipt reprints are opt-in per store, never enabled by an industry preset.
            case 'pos_receipt_reprint': return false;
            case 'medical_notes': return in_array('medical_notes', $profile_features);
            case 'payplan': return true;
            // 'bundles' is the legacy flag key for item variants — kept as a
            // readable alias so existing saved feature flags keep working.
            // True composite bundles are a separate future capability.
            case 'bundles': return mp_feature_enabled('item_variants');
            case 'item_variants':
                $legacy = mp_feature_flag_raw('bundles');
                if ($legacy !== null) { return $legacy; }
                return in_array('item_variants', $profile_features);
            default: return in_array($flag_key, $profile_features);
        }
    }
}

if (!function_exists('mp_get_location_scoped_flags')) {
    /**
     * Capabilities the head office can narrow site by site. A location can only
     * switch a capability OFF for itself — it can never grant itself one the
     * company has not turned on — and a flag that is absent simply inherits.
     */
    function mp_get_location_scoped_flags() {
        return [
            'transfer_receipt'          => 'Record what actually arrives at this location',
            'pump_metering'             => 'This location has pumps and tanks to read',
            'fuel_shift_reconciliation' => 'Duties here are closed and reconciled',
            'bulk_dispatch'             => 'This location sends or receives bulk loads',
            'lpg_refills'               => 'Cylinders are refilled and weighed at this location',
            'cylinder_tracking'         => 'This location keeps its own cylinder register',
            'delivery_scheduling' => 'Send deliveries from this location',
            'online_store'        => 'This location sells on the online store',
            'cashier_shifts'      => 'Tills here are opened and closed as shifts',
            'staff_assignment'    => 'Assign staff to this location',
        ];
    }
}

if (!function_exists('mp_get_location_flags')) {
    /** Saved location overrides for one warehouse (empty when nothing is set). */
    function mp_get_location_flags($warehouse_id) {
        static $cache = [];
        $warehouse_id = (int)$warehouse_id;
        if ($warehouse_id <= 0) return [];
        if (array_key_exists($warehouse_id, $cache)) return $cache[$warehouse_id];
        $cache[$warehouse_id] = [];
        $CI =& get_instance();
        if (!$CI->db->table_exists('db_warehouse') || !$CI->db->field_exists('feature_flags_json', 'db_warehouse')) {
            return $cache[$warehouse_id];
        }
        $row = $CI->db->select('feature_flags_json')
            ->where('id', $warehouse_id)
            ->where('store_id', get_current_store_id())
            ->get('db_warehouse')->row();
        if ($row && !empty($row->feature_flags_json)) {
            $flags = json_decode($row->feature_flags_json, true);
            if (is_array($flags)) $cache[$warehouse_id] = $flags;
        }
        return $cache[$warehouse_id];
    }
}

if (!function_exists('mp_location_feature_enabled')) {
    /**
     * Company capability AND, when a location is known, not narrowed away there.
     */
    function mp_location_feature_enabled($flag_key, $warehouse_id = null) {
        if (!mp_feature_enabled($flag_key)) return false;
        $warehouse_id = ($warehouse_id === null) ? 0 : (int)$warehouse_id;
        if ($warehouse_id <= 0) return true;
        $flags = mp_get_location_flags($warehouse_id);
        if (!array_key_exists($flag_key, $flags)) return true;
        return filter_var($flags[$flag_key], FILTER_VALIDATE_BOOLEAN);
    }
}

if (!function_exists('mp_is_oil_gas_workspace')) {
    function mp_is_oil_gas_workspace() {
        return mp_feature_enabled('oil_gas_workspace');
    }
}

if (!function_exists('mp_oil_gas_jobs_enabled')) {
    /**
     * Does this fuel company run service work at its locations? `service_jobs`
     * is the equipment trade's switch and brings its own screens with it; a
     * station or depot that only ever fixes pumps and generates can turn on the
     * fuel one and keep the workspace it actually uses.
     */
    function mp_oil_gas_jobs_enabled() {
        return mp_feature_enabled('oil_gas_service_jobs') || mp_feature_enabled('service_jobs');
    }
}

if (!function_exists('mp_oil_gas_report_cards')) {
    /**
     * The cards the Oil & Gas company report prints, in the order head office
     * reads them. Both the desktop and the phone screen render this same table so
     * the two can never quote different figures for the same question.
     */
    function mp_oil_gas_report_cards() {
        return array(
            'trading' => array(
                'title' => 'What each location rang up',
                'blurb' => "Invoices raised in the period. A cancelled bill or an opening balance is not a day's work, so neither is counted.",
                'columns' => array(
                    array('key' => 'sales_count', 'label' => 'Invoices', 'kind' => 'int'),
                    array('key' => 'sales_value', 'label' => 'Value rung up', 'kind' => 'money'),
                ),
            ),
            'stock' => array(
                'title' => 'What each location is holding',
                'blurb' => 'This is the shelf as it stands today, so it carries no dates.',
                'columns' => array(
                    array('key' => 'stock_lines', 'label' => 'Lines holding quantity', 'kind' => 'int'),
                    array('key' => 'stock_value', 'label' => 'Value at cost', 'kind' => 'money'),
                ),
            ),
            'movements' => array(
                'title' => 'Movement between locations',
                'blurb' => 'A load counts at the location that sent it and again at the one that received it, because both of them handled it.',
                'columns' => array(
                    array('key' => 'loads_sent', 'label' => 'Loads sent', 'kind' => 'int'),
                    array('key' => 'loads_received', 'label' => 'Loads received', 'kind' => 'int'),
                    array('key' => 'loads_open', 'label' => 'Still on the road', 'kind' => 'int'),
                ),
            ),
            'forecourt' => array(
                'title' => 'Forecourt',
                'blurb' => "Litres the pump meters recorded on duties that have been closed. The difference column adds up every duty's gap, however small, so a slow leak cannot hide inside a big month.",
                'columns' => array(
                    array('key' => 'litres_metered', 'label' => 'Litres metered', 'kind' => 'litres'),
                    array('key' => 'variance_litres', 'label' => 'Difference to explain', 'kind' => 'litres'),
                    array('key' => 'duties_awaiting', 'label' => 'Duties awaiting a manager', 'kind' => 'int'),
                ),
            ),
            'lpg' => array(
                'title' => 'Gas cylinders',
                'blurb' => 'What the scale booked, against the shells that are still out with customers. A fill with no till receipt is one we have weighed but not yet charged for.',
                'columns' => array(
                    array('key' => 'kg_filled', 'label' => 'Kilograms filled', 'kind' => 'kg'),
                    array('key' => 'fills', 'label' => 'Fills weighed', 'kind' => 'int'),
                    array('key' => 'fills_unmatched', 'label' => 'Fills not yet charged', 'kind' => 'int'),
                    array('key' => 'shells_out', 'label' => 'Shells out', 'kind' => 'int'),
                ),
            ),
            'deposits' => array(
                'title' => 'Deposit money held',
                'blurb' => 'Refundable money taken for shells on loan, less what has been paid back. It is never income and never stock.',
                'columns' => array(
                    array('key' => 'deposits_held', 'label' => 'Held at the end of the period', 'kind' => 'money'),
                ),
            ),
            'jobs' => array(
                'title' => 'Service work',
                'blurb' => 'Visits still open at each location, whatever date they carry, because an unfinished job is a job somebody still owes. What they are worth counts only once a job is finished, in the period it was finished in, because that is when the number is real.',
                'columns' => array(
                    array('key' => 'jobs_open', 'label' => 'Still open', 'kind' => 'int'),
                    array('key' => 'jobs_completed', 'label' => 'Finished', 'kind' => 'int'),
                    array('key' => 'job_charge', 'label' => 'Charged for finished work', 'kind' => 'money'),
                ),
            ),
        );
    }
}

if (!function_exists('mp_oil_gas_report_figure')) {
    /** Reads one report figure the way a merchant would say it. */
    function mp_oil_gas_report_figure($kind, $value) {
        if ($kind === 'money') return mp_format_money($value);
        if ($kind === 'litres') return mp_format_litres($value) . ' L';
        if ($kind === 'kg') return rtrim(rtrim(number_format((float)$value, 2), '0'), '.') . ' kg';
        return number_format((int)$value);
    }
}

if (!function_exists('mp_location_assigned_ids')) {
    /**
     * Locations (db_warehouse) the signed-in user may act on inside a company.
     * null means the whole company: the store owner and Super Admin are never
     * scoped down. Everyone else is limited to their branch assignment, which
     * is exactly what the multi-location Oil & Gas workspace needs — a forecourt
     * attendant sees their own station, head office sees every one.
     *
     * One raw query, memoised: this runs while a DataTables query builder is
     * half-built, and a builder-based nested query would corrupt it.
     */
    function mp_location_assigned_ids($store_id = null) {
        if ($store_id === null) $store_id = get_current_store_id();
        $store_id = (int)$store_id;
        if (is_store_admin() || is_admin()) return null;
        $user_id = (int)get_current_user_id();
        if ($user_id <= 0) return [];
        // Keyed by user too: two staff of the same shop must never inherit each
        // other's site list.
        static $cache = [];
        $cache_key = $store_id . ':' . $user_id;
        if (array_key_exists($cache_key, $cache)) return $cache[$cache_key];
        $CI =& get_instance();
        $rows = $CI->db->query(
            'SELECT uw.warehouse_id FROM db_userswarehouses uw'
            . ' INNER JOIN db_warehouse w ON w.id = uw.warehouse_id'
            . ' WHERE uw.user_id = ' . $user_id . ' AND w.store_id = ' . $store_id . ' AND w.status = 1'
        )->result_array();
        $ids = array_values(array_unique(array_filter(array_map('intval', array_column($rows, 'warehouse_id')))));
        $cache[$cache_key] = $ids;
        return $ids;
    }
}

if (!function_exists('mp_transfer_loss_tolerance_percent')) {
    /**
     * How much of a transferred quantity may go missing in transit before the
     * receiving site has to send the load up for approval. Stored per store so
     * head office sets the rule once.
     */
    function mp_transfer_loss_tolerance_percent($store_id = null) {
        if ($store_id === null) $store_id = get_current_store_id();
        if (!function_exists('mp_get_store_setting')) return 0.5;
        $value = mp_get_store_setting((int)$store_id, 'oil_gas', 'transfer_loss_tolerance_percent', null);
        if ($value === null || !is_numeric($value)) return 0.5;
        $value = (float)$value;
        if ($value < 0) return 0;
        if ($value > 100) return 100;
        return $value;
    }
}

if (!function_exists('mp_forecourt_shift_variance_percent')) {
    /**
     * How far a duty's pump meters may disagree with its till, as a percentage of
     * what was pumped, before a manager has to sign for the difference. A
     * forecourt always measures a little differently to a cash register; the rule
     * only decides when that difference stops being normal.
     */
    function mp_forecourt_shift_variance_percent($store_id = null) {
        if ($store_id === null) $store_id = get_current_store_id();
        if (!function_exists('mp_get_store_setting')) return 1.0;
        $value = mp_get_store_setting((int)$store_id, 'oil_gas', 'shift_variance_tolerance_percent', null);
        if ($value === null || !is_numeric($value)) return 1.0;
        $value = (float)$value;
        if ($value < 0) return 0;
        if ($value > 100) return 100;
        return $value;
    }
}

if (!function_exists('mp_forecourt_tank_variance_percent')) {
    /**
     * How far a tank's dip may disagree with what it is expected to hold before
     * the correction needs a manager. Temperature and a wet dip tape account for
     * a little; a hole in a line account for a lot.
     */
    function mp_forecourt_tank_variance_percent($store_id = null) {
        if ($store_id === null) $store_id = get_current_store_id();
        if (!function_exists('mp_get_store_setting')) return 0.5;
        $value = mp_get_store_setting((int)$store_id, 'oil_gas', 'tank_variance_tolerance_percent', null);
        if ($value === null || !is_numeric($value)) return 0.5;
        $value = (float)$value;
        if ($value < 0) return 0;
        if ($value > 100) return 100;
        return $value;
    }
}

if (!function_exists('mp_format_litres')) {
    /**
     * Litres the way a forecourt writes them: whole numbers stay whole, a dip
     * reading keeps its decimals. Screens use this so 1500 L and 1500.25 L do
     * not both come out as "1,500.000".
     */
    function mp_format_litres($value = 0) {
        return rtrim(rtrim(number_format((float)$value, 3), '0'), '.');
    }
}

if (!function_exists('mp_format_percent')) {
    function mp_format_percent($value = 0) {
        return rtrim(rtrim(number_format((float)$value, 2), '0'), '.') . '%';
    }
}

if (!function_exists('mp_forecourt_sites')) {
    /**
     * Locations that run a forecourt, without loading a model. Used by menus and
     * the workspace cards, which must not pull the fuel engine just to decide
     * whether to show a link.
     */
    function mp_forecourt_sites($flag = 'pump_metering') {
        if (!function_exists('mp_feature_enabled') || !mp_feature_enabled($flag)) return array();
        $ids = function_exists('mp_location_assigned_ids') ? mp_location_assigned_ids((int)get_current_store_id()) : null;
        if (is_array($ids) && empty($ids)) return array();
        $where = 'store_id = ' . (int)get_current_store_id() . ' AND status = 1';
        if (is_array($ids)) {
            $clean = array_values(array_filter(array_map('intval', $ids)));
            if (empty($clean)) return array();
            $where .= ' AND id IN (' . implode(',', $clean) . ')';
        }
        $CI =& get_instance();
        if (!isset($CI->db)) return array();
        $rows = $CI->db->query('SELECT id, warehouse_name FROM db_warehouse WHERE ' . $where . ' ORDER BY id ASC')->result();
        $sites = array();
        foreach ($rows as $row) {
            if (mp_location_feature_enabled($flag, (int)$row->id)) $sites[(int)$row->id] = $row->warehouse_name;
        }
        return $sites;
    }
}

if (!function_exists('mp_lpg_movement_words')) {
    /**
     * A cylinder's history is read by a person at a counter, so the movement
     * codes never show up on screen as codes.
     */
    function mp_lpg_movement_words($type) {
        $words = [
            'registered'        => 'Put on the register',
            'refilled'          => 'Weighed a fill',
            'issued'            => 'Sent out with a customer',
            'returned'          => 'Brought back',
            'given'             => 'Given on exchange',
            'received'          => 'Taken back on exchange',
            'lost'              => 'Written off as lost',
            'scrapped'          => 'Taken off the road',
            'deposit_paid'      => 'Deposit taken',
            'deposit_refunded'  => 'Deposit paid back',
        ];
        return isset($words[$type]) ? $words[$type] : 'Moved';
    }
}

if (!function_exists('mp_lpg_sites')) {
    /**
     * Locations that run LPG work — refilling shells or holding a cylinder
     * register. Same shape as mp_forecourt_sites(): menus and the workspace ask
     * this instead of loading the fuel engine just to decide whether to show a
     * link. A company that switched the capability on but has no site marked for
     * it is offered nothing, exactly as with pumps.
     */
    function mp_lpg_sites($flag = 'lpg_refills') {
        return mp_forecourt_sites($flag);
    }
}

if (!function_exists('mp_is_print_shop')) {
    /**
     * Is this store running a print business?
     *
     * Gates on `printing_workflow`, NOT `production_workflow`: the latter is
     * declared by the bakery/restaurant/manufacturer/butchery/oil presets too,
     * so it leaks the print workspace into ordinary retail. A store whose
     * industry label is wrong can still switch this on under Feature Flags.
     */
    function mp_is_print_shop() {
        return mp_feature_enabled('printing_workflow');
    }
}

if (!function_exists('mp_is_restaurant_workspace')) {
    /**
     * Is this store running the RestaurantOS workspace?
     *
     * Gated on `restaurant_workspace`, declared by the restaurant/food presets
     * (restaurant, fast_food, cafe, pizza_shop, shawarma, juice_bar, buka,
     * canteen) and the bar_lounge preset. A store whose industry label is
     * wrong can still switch it on under Feature Flags; retail presets never
     * declare it, so ordinary retail stays untouched.
     */
    function mp_is_restaurant_workspace() {
        return mp_feature_enabled('restaurant_workspace');
    }
}

if (!function_exists('mp_is_oil_gas_workspace')) {
    /**
     * Is this store running the Oil & Gas workspace?
     *
     * Gated on `oil_gas_workspace`, declared by the five fuel presets. Ordinary
     * retail presets never declare it, so a provision store that happens to
     * sell a bottle of kerosene is not handed tank and pump screens.
     */
    function mp_is_oil_gas_workspace() {
        return mp_feature_enabled('oil_gas_workspace');
    }
}

if (!function_exists('mp_get_restaurant_nav_presets')) {
    /**
     * RestaurantOS navigation presets.
     *
     * Each preset decides WHICH workspace destinations a restaurant sees —
     * the screens themselves are shared, the navigation is configurable per
     * operating model. `tables` = show Tables & Reservations, `outlets` =
     * always show the Outlets group (a store that actually runs more than
     * one outlet sees it regardless of preset).
     */
    function mp_get_restaurant_nav_presets() {
        return [
            'quick_service' => [
                'label'       => 'Quick Service',
                'description' => 'Counter-first service: walk-in POS, kitchen queue and pickup/delivery. No table service.',
                'tables'      => false,
                'outlets'     => false,
            ],
            'casual_dining' => [
                'label'       => 'Casual Dining',
                'description' => 'Table service with reservations, alongside takeaway and delivery.',
                'tables'      => true,
                'outlets'     => false,
            ],
            'delivery_only' => [
                'label'       => 'Delivery Only',
                'description' => 'Online and phone orders fulfilled by pickup or delivery; no dine-in tables.',
                'tables'      => false,
                'outlets'     => false,
            ],
            'multi_location' => [
                'label'       => 'Multi-Location Chain',
                'description' => 'Every service screen plus outlet management for a chain of outlets.',
                'tables'      => true,
                'outlets'     => true,
            ],
            'bar_lounge' => [
                'label'         => 'Bar / Lounge',
                'description'   => 'Table service with the bar board and drinks stock; food, ordering and delivery stay optional.',
                'tables'        => true,
                'outlets'       => false,
                'kitchen_label' => 'Bar & Kitchen Board',
                // Drinks go out across the counter as well as to tables, so the
                // walk-in screen is named for both halves of the floor.
                'pos_label'     => 'Counter & Bar POS',
            ],
        ];
    }
}

if (!function_exists('mp_restaurant_nav_preset')) {
    /**
     * The store's current RestaurantOS navigation preset key.
     * Stored under restaurant_workspace.nav_preset in industry_settings_json
     * (the same namespaced-blob pattern the laundry workflow config uses).
     */
    function mp_restaurant_nav_preset() {
        $profile = mp_get_store_profile();
        // A lounge lands on the lounge rail: a drinks business should not have to
        // find a settings screen before its tables, tabs and board are named right.
        $default = (($profile['industry_type'] ?? '') === 'bar_lounge') ? 'bar_lounge' : 'quick_service';
        // mp_get_store_profile() exposes industry_settings_json as 'settings'.
        $json = (!empty($profile['settings']) && is_array($profile['settings'])) ? $profile['settings'] : [];
        if (!empty($json['restaurant_workspace']['nav_preset'])) {
            $default = $json['restaurant_workspace']['nav_preset'];
        }
        $presets = mp_get_restaurant_nav_presets();
        return isset($presets[$default]) ? $default : 'quick_service';
    }
}

if (!function_exists('mp_kitchen_board_label')) {
    /**
     * What this store calls its fulfilment board.
     *
     * A bar/lounge works drinks as well as food on the same tickets, so its
     * board is named for what the merchant actually does there. Every other
     * restaurant keeps the plain Kitchen Display name. One function because the
     * rail, the screen heading and the setup preview must never disagree.
     */
    function mp_kitchen_board_label() {
        $presets = mp_get_restaurant_nav_presets();
        $key = mp_restaurant_nav_preset();
        return $presets[$key]['kitchen_label'] ?? 'Kitchen Display';
    }
}

if (!function_exists('mp_workspace_word')) {
    /**
     * The word a service workspace uses for itself on its own signs.
     *
     * A bar/lounge rides the RestaurantOS rails, so the headings that were
     * written as literal "Restaurant …" called a bar a restaurant. The word now
     * comes from the store's own industry — a lounge says Lounge, a restaurant
     * still says Restaurant — and a merchant's label override outranks it. One
     * function because the rail, the dashboard heading, the overview page and
     * the setup screen must never disagree about what the place is called.
     */
    function mp_workspace_word($default = 'Restaurant') {
        // Cached per store: a label lookup walks three tables, and one screen
        // asks this question from the rail, the page heading and the shortcut
        // grid. They must read the same answer anyway.
        static $cache = [];
        $store_id = (int) get_current_store_id();
        if (!isset($cache[$store_id])) {
            $cache[$store_id] = mp_label('workspace', $default);
        }
        return $cache[$store_id];
    }
}

if (!function_exists('mp_restaurant_nav_items')) {
    /**
     * Visible RestaurantOS workspace destinations for this store.
     *
     * The sidebar rail and the Overview shortcut grid read this one list so a
     * destination and its gate can never disagree — every item carries the
     * permission its own controller checks (POS → pos, Menu → items_add,
     * Kitchen → store_view, Recipes → recipes_view …), and preset-only
     * destinations (Tables, Outlets) drop out when the preset excludes them.
     *
     * Pseudo-perm 'admin' matches is_admin() || is_store_admin().
     *
     * Pass $preset_key to ask what a *different* layout would show — that is
     * how Restaurant Setup previews a preset before it is saved, so the
     * preview and the real rail are produced by the same gating code.
     */
    function mp_restaurant_nav_items($preset_key = null) {
        $CI =& get_instance();
        $presets = mp_get_restaurant_nav_presets();
        $key = ($preset_key !== null && isset($presets[$preset_key])) ? $preset_key : mp_restaurant_nav_preset();
        $preset = $presets[$key];

        $items = [];
        $add = function ($item_key, $label, $url, $perms = [], $flag = null) use (&$items, $CI) {
            if ($flag !== null && !mp_feature_enabled($flag)) return;
            if (!empty($perms)) {
                $ok = false;
                foreach ((array) $perms as $p) {
                    if ($p === 'admin') {
                        if (is_admin() || is_store_admin()) { $ok = true; break; }
                    } elseif ($CI->permissions($p)) {
                        $ok = true; break;
                    }
                }
                if (!$ok) return;
            }
            $items[$item_key] = [
                'key'   => $item_key,
                'label' => $label,
                'url'   => base_url($url),
            ];
        };

        // A lounge serves drinks across the counter as well as to tables, so its
        // walk-in screen is named for both halves of the floor.
        $add('pos',       !empty($preset['pos_label']) ? $preset['pos_label'] : 'Walk-in POS', 'pos', ['pos']);
        $add('orders',    'Orders',                'restaurantos/orders',            ['sales_view', 'online_store_orders', 'admin']);
        // The bar/lounge board carries drinks as well as food, so it is named for
        // what the merchant actually works on there.
        $add('kitchen',   mp_kitchen_board_label(), 'operations/kitchen', ['store_view'], 'kitchen_workflow');
        $add('fulfilment','Pickup & Delivery',     'operations/delivery_scheduling', ['store_view'], 'delivery_scheduling');
        if (!empty($preset['tables'])) {
            $add('tables', 'Tables & Reservations', 'operations/table_management',   ['store_view', 'admin'], 'table_management');
        }
        // Tabs work with or without tables — a bar tab has no table on it.
        $add('tabs',      'Running Tabs',          'operations/tabs',                ['store_view', 'admin'], 'running_tabs');
        // A lounge settles wages off the floor, so who served each round and what
        // the guests left that person is a report, not a notebook by the till.
        $add('waiters',   'Waiter Takings',        'operations/waiter_takings',      ['store_view', 'admin'], 'waiter_assignment');
        $add('menu',      'Menu & Combos',         'operations/menu_items',          ['items_add'], 'kitchen_workflow');
        $add('recipes',   'Recipes & Ingredients', 'operations/recipes',             ['recipes_view'], 'recipe_tracking');
        // Event nights are how a lounge fills the room, so the workspace rail
        // carries them; the shared Operations menu keeps its entry too.
        $add('events',    'Events & Tickets',      'events',                         ['store_view'], 'event_ticketing');

        // Outlets: chains always, plus any store that actually runs more than
        // one outlet — hiding outlet tools from a two-outlet shop because it
        // never touched the preset would strand it.
        $outlets_ok = !empty($preset['outlets']);
        if (!$outlets_ok && function_exists('get_current_store_id')) {
            $outlets_ok = $CI->db->where('store_id', get_current_store_id())->count_all_results('db_warehouse') > 1;
        }
        if ($outlets_ok && function_exists('warehouse_module') && warehouse_module()) {
            $add('outlets', 'Outlets', 'warehouse', ['warehouse_view', 'warehouse_add']);
        }

        return $items;
    }
}

if (!function_exists('mp_feature_enabled_for_store')) {
    /**
     * Same as mp_feature_enabled() but for an explicit store — needed on public
     * pages (storefront) where there is no session store_id.
     */
    function mp_feature_enabled_for_store($flag_key, $store_id) {
        $CI =& get_instance();
        $reader = mp_read_db();
        $raw = null;
        if ($reader->table_exists('db_store_industry_settings')) {
            $store = $reader->select('feature_flags_json')->where('store_id', $store_id)->get('db_store_industry_settings')->row();
            if ($store && !empty($store->feature_flags_json)) {
                $flags = json_decode($store->feature_flags_json, true);
                if (is_array($flags) && array_key_exists($flag_key, $flags)) {
                    $raw = filter_var($flags[$flag_key], FILTER_VALIDATE_BOOLEAN);
                }
            }
        }
        if ($raw === null && $reader->field_exists('feature_flags_json', 'db_store')) {
            $store = $reader->select('feature_flags_json')->where('id', $store_id)->get('db_store')->row();
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
        // 'bundles' is the legacy flag key for item variants (same alias as
        // mp_feature_enabled); true composite bundles are a future capability.
        if ($flag_key === 'bundles') {
            return mp_feature_enabled_for_store('item_variants', $store_id);
        }
        if ($flag_key === 'item_variants') {
            $CI2 =& get_instance();
            if ($reader->table_exists('db_store_industry_settings')) {
                $store = $reader->select('feature_flags_json')->where('store_id', $store_id)->get('db_store_industry_settings')->row();
                if ($store && !empty($store->feature_flags_json)) {
                    $flags = json_decode($store->feature_flags_json, true);
                    if (is_array($flags) && array_key_exists('bundles', $flags)) {
                        return filter_var($flags['bundles'], FILTER_VALIDATE_BOOLEAN);
                    }
                }
            }
            if ($reader->field_exists('feature_flags_json', 'db_store')) {
                $store = $reader->select('feature_flags_json')->where('id', $store_id)->get('db_store')->row();
                if ($store && !empty($store->feature_flags_json)) {
                    $flags = json_decode($store->feature_flags_json, true);
                    if (is_array($flags) && array_key_exists('bundles', $flags)) {
                        return filter_var($flags['bundles'], FILTER_VALIDATE_BOOLEAN);
                    }
                }
            }
        }
        $profile = mp_get_store_profile($store_id);
        return in_array($flag_key, $profile['features'] ?? []);
    }
}

if (!function_exists('mp_read_store_profile')) {
    /** Isolated, schema-aware profile read for older and current fleets. */
    function mp_read_store_profile($store_id) {
        $reader = mp_read_db();
        $columns = ['industry_type','business_model','feature_flags_json','workflow_template_key','dashboard_template_key','storefront_theme_key','label_overrides_json','industry_settings_json'];
        foreach (['db_store_industry_settings'=>'store_id','db_store_business_profile'=>'store_id','db_store'=>'id'] as $table=>$key) {
            if (!$reader->table_exists($table)) continue;
            $fields = $reader->list_fields($table);
            $available = array_values(array_intersect($columns, $fields));
            if (!$available || !in_array($key, $fields, true)) continue;
            $query = $reader->select(implode(',', $available))->where($key, (int)$store_id)->limit(1)->get($table);
            $row = $query ? $query->row_array() : [];
            if ($row) return $row;
        }
        return [];
    }
}

if (!function_exists('mp_get_store_profile')) {
    function mp_get_store_profile($store_id=null) {
        if (empty($store_id)) $store_id = get_current_store_id();
        $row = mp_read_store_profile($store_id);
        if (!$row) return mp_get_default_profile();
        $store = (object)$row;

        $industry = (!empty($store->industry_type)) ? $store->industry_type : 'general_retail';
        // Public canonical identifier aliases the established persisted key.
        if (strtoupper(trim($industry)) === 'GARDEN_CENTRE_LANDSCAPING') {
            $industry = 'garden_centre_landscaping';
        }
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
        // Older Supplements presets saved "Member" as the customer label.
        // Keep customers as the shared person record and surface membership
        // separately, even when an existing store still has that old value.
        if ($industry === 'supplements_wellness'
            && in_array(strtolower(trim((string)($profile['labels']['customer'] ?? ''))), ['member','members'], true)) {
            unset($profile['labels']['customer']);
        }
        if (!empty($store->industry_settings_json)) {
            $settings = json_decode($store->industry_settings_json,true);
            if (is_array($settings)) $profile['settings']=$settings;
        }
        if ($industry === 'garden_centre_landscaping') {
            require_once APPPATH.'helpers/garden_helper.php';
            try {
                $profile['operating_modes'] = mp_garden_modes($profile['settings']['garden_operating_modes'] ?? ['combined']);
            } catch (InvalidArgumentException $e) {
                // Malformed saved settings fail closed; leave Settings usable
                // so an owner can repair the modes without a fatal page.
                $profile['operating_modes'] = [];
                log_message('error','Invalid garden operating modes for store '.(int)$store_id);
            }
            $profile['features'] = mp_garden_mode_features($profile['features'], $profile['operating_modes']);
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
        $reader = mp_read_db();
        $store_id = get_current_store_id();
        $profile = mp_get_store_profile($store_id);
        $legacy_supplements_customer_label = $key === 'customer'
            && ($profile['industry_type'] ?? '') === 'supplements_wellness';

        // 1. Prefer modular industry settings table
        if ($reader->table_exists('db_store_industry_settings')) {
            $q = $reader->select('label_overrides_json')->where('store_id',$store_id)->get('db_store_industry_settings');
            if ($q && method_exists($q, 'row')) {
                $store = $q->row();
                if ($store && !empty($store->label_overrides_json)) {
                    $overrides = json_decode($store->label_overrides_json,true);
                    if (is_array($overrides) && isset($overrides[$key]) && $overrides[$key]!=='') {
                        $value = trim((string)$overrides[$key]);
                        if (!$legacy_supplements_customer_label || !in_array(strtolower($value), ['member','members'], true)) return $value;
                    }
                }
            }
        }

        // 2. Fallback to db_store columns
        if ($reader->field_exists('label_overrides_json', 'db_store')) {
            $q = $reader->select('label_overrides_json')->where('id',$store_id)->get('db_store');
            if ($q && method_exists($q, 'row')) {
                $store = $q->row();
                if ($store && !empty($store->label_overrides_json)) {
                    $overrides = json_decode($store->label_overrides_json,true);
                    if (is_array($overrides) && isset($overrides[$key]) && $overrides[$key]!=='') {
                        $value = trim((string)$overrides[$key]);
                        if (!$legacy_supplements_customer_label || !in_array(strtolower($value), ['member','members'], true)) return $value;
                    }
                }
            }
        }
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
            'near_expiry'=>['title'=>'Near Expiry','icon'=>'fa-calendar-times-o','industries'=>['pharmacy','supermarket','mini_mart','distributor','wholesaler','bakery_cake_studio','perfume_shop','beauty_cosmetics','makeup_studio','skincare'],'features'=>['expiry_tracking']],
            'expired_items'=>['title'=>'Expired Items','icon'=>'fa-ban','industries'=>['pharmacy','supermarket','mini_mart','distributor','wholesaler','bakery_cake_studio','perfume_shop','beauty_cosmetics','makeup_studio','skincare'],'features'=>['expiry_tracking']],
            'low_stock_medicines'=>['title'=>'Low Stock Medicines','icon'=>'fa-medkit','industries'=>['pharmacy'],'features'=>[]],
            'pending_laundry'=>['title'=>'Pending Laundry','icon'=>'fa-refresh','industries'=>['laundry'],'features'=>['laundry_workflow']],
            'ready_for_pickup'=>['title'=>'Ready for Pickup','icon'=>'fa-check-circle','industries'=>['laundry'],'features'=>['laundry_workflow']],
            'overdue_pickups'=>['title'=>'Overdue Pickups','icon'=>'fa-clock-o','industries'=>['laundry'],'features'=>['laundry_workflow']],
            'upcoming_events'=>['title'=>'Upcoming Events','icon'=>'fa-calendar','industries'=>['bakery_cake_studio','makeup_artist','makeup_studio','furniture','perfume_shop','skincare'],'features'=>['custom_orders']],
            'production_queue'=>['title'=>'Production Queue','icon'=>'fa-industry','industries'=>['bakery_cake_studio','restaurant','perfume_shop','skincare'],'features'=>['production_workflow']],
            'deposit_balance'=>['title'=>'Deposit Balance','icon'=>'fa-money','industries'=>['bakery_cake_studio','furniture','makeup_artist','makeup_studio','perfume_shop','skincare'],'features'=>['custom_orders']],
            'pending_appointments'=>['title'=>'Pending Appointments','icon'=>'fa-calendar-check-o','industries'=>['beauty_cosmetics','beauty_spa','salon_barbershop','makeup_artist','makeup_studio','service_business','skincare'],'features'=>['appointments']],
            'today_bookings'=>['title'=>"Today's Bookings & Tables",'icon'=>'fa-calendar','industries'=>['restaurant','beauty_cosmetics','beauty_spa','salon_barbershop','makeup_artist','makeup_studio','skincare'],'features'=>['appointments','table_management']],
            'kitchen_status'=>['title'=>'Kitchen Status','icon'=>'fa-fire','industries'=>['restaurant','bar_lounge'],'features'=>['kitchen_workflow']],
            'open_tables'=>['title'=>'Open Tables','icon'=>'fa-table','industries'=>['restaurant','bar_lounge'],'features'=>['table_management']],
            'staff_schedule'=>['title'=>'Staff Schedule','icon'=>'fa-users','industries'=>'*','features'=>['staff_assignment']],
            'commission_due'=>['title'=>'Commission Due','icon'=>'fa-percent','industries'=>'*','features'=>['staff_commission']],
            'pending_approvals'=>['title'=>'Pending Approvals','icon'=>'fa-check-square-o','industries'=>'*','features'=>['manager_approvals']],
            'treatment_history'=>['title'=>'Treatment History','icon'=>'fa-heartbeat','industries'=>['beauty_cosmetics','beauty_spa','salon_barbershop','makeup_artist','makeup_studio','skincare'],'features'=>['treatment_notes']],
            'recipe_costing'=>['title'=>'Recipe Costing','icon'=>'fa-cutlery','industries'=>['restaurant','bakery_cake_studio','perfume_shop','skincare','bar_lounge'],'features'=>['recipe_tracking']],
            'online_orders'=>['title'=>'Online Orders','icon'=>'fa-globe','industries'=>'*','features'=>['online_store']],
            'qr_orders'=>['title'=>'QR Orders','icon'=>'fa-qrcode','industries'=>'*','features'=>['qr_ordering']],
            'nylon_open_jobs'=>['title'=>'Open Production Jobs','icon'=>'fa-industry','industries'=>['nylon_polythene'],'features'=>['nylon_workflow']],
            'nylon_film_stock'=>['title'=>'Raw Material & Film Stock','icon'=>'fa-cubes','industries'=>['nylon_polythene'],'features'=>['nylon_workflow']],
            'nylon_output_rejects'=>['title'=>'Output vs Rejects','icon'=>'fa-check-circle','industries'=>['nylon_polythene'],'features'=>['nylon_workflow']],
            'nylon_job_margin'=>['title'=>'Profit by Job','icon'=>'fa-line-chart','industries'=>['nylon_polythene'],'features'=>['nylon_workflow']],
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
            'makeup_studio_standard' => 'Makeup Studio Standard',
            'laundry_standard'    => 'Laundry Standard',
            'bakery_standard'     => 'Bakery / Cake Studio Standard',
            'wholesale_standard'  => 'Wholesale Standard',
            'distributor_standard' => 'Distributor Standard',
            'wholesaler_standard'  => 'Wholesaler Standard',
            'furniture_standard'  => 'Furniture Standard',
            'service_standard'    => 'Service Business Standard',
            'perfumery_standard'  => 'Perfumery Standard',
            'skincare_standard'   => 'Skincare / Formulation Standard',
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
            'makeup_studio'       => 'Makeup Studio',
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
            'perfume_shop'        => 'Perfume Shop',
            'skincare'            => 'Skincare & Organic Cosmetics',
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
        $reader = mp_read_db();
        if (empty($store_id)) $store_id = get_current_store_id();
        if (empty($business_type)) {
            $business_type = 'general_retail';
            if ($reader->table_exists('db_store_business_profile')) {
                $q = $reader->select('industry_type')->where('store_id',$store_id)->get('db_store_business_profile');
            } else {
                $q = $reader->select('industry_type')->where('id',$store_id)->get('db_store');
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
        $reader = mp_read_db();
        $q = $reader->select('industry_fields_json')->where('id',$service_id)->get('db_services');
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

// ============================================================
// ROLE / INDUSTRY FAMILIES
// Role lists are gated by the store's industry: universal roles show
// everywhere, industry roles (e.g. Clinical Director) only for stores
// of that family — a plant must never be offered clinical roles.
// ============================================================

if (!function_exists('mp_role_family')) {
    /**
     * Which industry family a role name belongs to.
     * 'universal' roles are offered to every business; the rest only to
     * stores in that family. Unknown/custom roles count as universal so
     * a store's own roles always persist.
     */
    function mp_role_family($role_name) {
        $n = strtolower(trim((string)$role_name));
        if ($n === '') {
            return 'universal';
        }
        $clinical = ['clinical director','physiotherapist','nurse','receptionist','finance officer','auditor','porter'];
        foreach ($clinical as $c) {
            if (strpos($n, $c) !== false) {
                return 'clinical';
            }
        }
        $pharmacy = ['pharmacist','dispensing assistant','pharmacy buyer'];
        foreach ($pharmacy as $p) {
            if (strpos($n, $p) !== false) {
                return 'pharmacy';
            }
        }
        if (strpos($n, 'production supervisor') !== false || strpos($n, 'production operator') !== false) {
            return 'production';
        }
        if (strpos($n, 'service engineer') !== false) {
            return 'equipment';
        }
        return 'universal';
    }
}

if (!function_exists('mp_store_role_families')) {
    /**
     * The role families this store's industry belongs to.
     * A store can belong to more than one (e.g. a physio clinic that also
     * runs a small production unit).
     */
    function mp_store_role_families($store_id = null) {
        $profile = mp_get_store_profile($store_id);
        $ind  = $profile['industry_type'] ?? '';
        $feat = $profile['features'] ?? [];
        $families = [];

        if (in_array($ind, ['physiotherapy_rehabilitation','clinic','hospital','diagnostic_centre'], true)
            || in_array('patient_registry', $feat, true)
            || in_array('clinical_encounters', $feat, true)) {
            $families[] = 'clinical';
        }

        // A pharmacy may dispense: the till, the prescription queue and recalls
        // all need a named clinician role.
        if ((function_exists('mp_pharmacy_industry') && mp_pharmacy_industry($ind))
            || in_array('prescription_required', $feat, true)
            || in_array('medical_notes', $feat, true)) {
            $families[] = 'pharmacy';
        }

        if (in_array($ind, ['manufacturer','nylon_polythene','oil_processing_plant','bakery_cake_studio','printing'], true)
            || in_array('production_workflow', $feat, true)
            || in_array('recipe_tracking', $feat, true)
            || in_array('oil_processing_workflow', $feat, true)) {
            $families[] = 'production';
        }

        if ($ind === 'scientific_equipment') {
            $families[] = 'equipment';
        }

        return $families;
    }
}

if (!function_exists('mp_role_visible')) {
    /**
     * May a store of this industry be given this role?
     */
    function mp_role_visible($role_name, $store_id = null) {
        $family = mp_role_family($role_name);
        if ($family === 'universal') {
            return true;
        }
        return in_array($family, mp_store_role_families($store_id), true);
    }
}

if (!function_exists('mp_industry_screen_labels')) {
    /**
     * Friendly screen names for the routes an industry exposes. MartPoint
     * Assist uses these to name and open the right workspace screens.
     */
    function mp_industry_screen_labels() {
        return array(
            'pos'                            => 'POS',
            'items'                          => 'Products',
            'inventory'                      => 'Inventory',
            'sales'                          => 'Sales',
            'customers'                      => 'Customers',
            'suppliers'                      => 'Suppliers',
            'purchases'                      => 'Purchases',
            'expenses'                       => 'Expenses',
            'reports'                        => 'Reports',
            'online_store'                   => 'Online Store',
            'online_store/orders'            => 'Online Orders',
            'garden/catalogue'               => 'Catalogue',
            'garden/nursery'                 => 'Nursery',
            'garden/production'              => 'Nursery Production',
            'garden/materials'               => 'Materials',
            'garden/projects'                => 'Landscaping Projects',
            'garden/maintenance'             => 'Garden Maintenance',
            'leads'                          => 'Enquiries & Leads',
            'appointments'                   => 'Bookings & Appointments',
            'quotation/add'                  => 'New Quotation',
            'production'                     => 'Production',
            'operations/production_schedule' => 'Production Schedule',
            'operations/kitchen'             => 'Kitchen Display',
            'oil_processing'                 => 'Plant Workspace',
            'oil_gas'                        => 'Oil & Gas Workspace',
            'oil_gas/locations'              => 'Location Operations',
            'stock_transfer/view'            => 'Stock Transfers',
            'nylon'                          => 'Production',
            'printing/production'            => 'Production Floor',
        );
    }
}

if (!function_exists('mp_industry_synonym_map')) {
    /**
     * Informal words users type or say, mapped to the canonical concept key.
     * Seeded generically, then extended for specific industries.
     */
    function mp_industry_synonym_map($industry = null) {
        $common = array(
            'product' => 'item', 'products' => 'item', 'goods' => 'item', 'merchandise' => 'item',
            'buyer' => 'customer', 'buyers' => 'customer', 'client' => 'customer', 'clients' => 'customer',
            'shopper' => 'customer', 'patient' => 'customer', 'patients' => 'customer',
            'vendor' => 'supplier', 'vendors' => 'supplier',
            'bill' => 'sale', 'bills' => 'sale', 'invoice' => 'sale', 'invoices' => 'sale', 'receipt' => 'sale',
            'estimate' => 'quotation', 'estimates' => 'quotation', 'quote' => 'quotation', 'quotes' => 'quotation',
            'debt' => 'customer_balance', 'debts' => 'customer_balance', 'owing' => 'customer_balance', 'owes' => 'customer_balance',
            'depot' => 'warehouse', 'shop' => 'warehouse', 'outlet' => 'warehouse',
            'revenue' => 'sales', 'turnover' => 'sales', 'takings' => 'sales',
        );
        $by_industry = array(
            'garden_centre_landscaping' => array(
                'plant' => 'item', 'plants' => 'item', 'tree' => 'item', 'trees' => 'item',
                'shrub' => 'item', 'shrubs' => 'item', 'seedling' => 'item', 'seedlings' => 'item',
                'flower' => 'item', 'flowers' => 'item', 'pot' => 'item', 'pots' => 'item', 'soil' => 'item',
                'nursery' => 'production', 'propagation' => 'production', 'potting' => 'production',
                'project' => 'service_order', 'projects' => 'service_order', 'job' => 'service_order',
                'jobs' => 'service_order', 'installation' => 'service_order', 'installations' => 'service_order',
                'landscaping' => 'service_order', 'maintenance' => 'service_order',
                'visit' => 'appointment', 'visits' => 'appointment', 'site' => 'appointment', 'sites' => 'appointment',
                'material' => 'recipe', 'materials' => 'recipe', 'template' => 'recipe', 'formula' => 'recipe',
                'crew' => 'staff', 'team' => 'staff', 'gardener' => 'staff', 'landscaper' => 'staff',
            ),
            'oil_processing_plant' => array(
                'material' => 'recipe', 'materials' => 'recipe', 'formula' => 'recipe',
                'process' => 'production', 'run' => 'production', 'runs' => 'production', 'batch' => 'production',
            ),
            'fuel_station' => array(
                'fuel' => 'item', 'petrol' => 'item', 'diesel' => 'item', 'pms' => 'item', 'ago' => 'item',
                'kerosene' => 'item', 'gas' => 'item', 'litre' => 'item',
                'station' => 'warehouse', 'forecourt' => 'warehouse', 'filling station' => 'warehouse',
                'attendant' => 'staff', 'till' => 'pos',
            ),
            'lpg_gas_business' => array(
                'gas' => 'item', 'lpg' => 'item', 'cooking gas' => 'item', 'cylinder' => 'item',
                'refill' => 'service', 'refills' => 'service', 'filling' => 'service',
                'exchange' => 'service', 'swap' => 'service',
            ),
            'bulk_fuel_depot' => array(
                'fuel' => 'item', 'petrol' => 'item', 'diesel' => 'item', 'pms' => 'item', 'ago' => 'item',
                'depot' => 'warehouse', 'tank farm' => 'warehouse', 'load' => 'purchase',
            ),
        );
        if ($industry === null) {
            $profile  = function_exists('mp_get_store_profile') ? mp_get_store_profile() : array();
            $industry = isset($profile['industry_type']) ? $profile['industry_type'] : '';
        }
        return isset($by_industry[$industry]) ? array_merge($common, $by_industry[$industry]) : $common;
    }
}

if (!function_exists('mp_industry_vocabulary')) {
    /**
     * The vocabulary the active industry actually uses: canonical terms, the
     * screens/slugs available to it, and the informal words that map onto
     * those concepts. Consumed by MartPoint Assist so it understands the
     * store's own language instead of a generic retail vocabulary.
     *
     * @param array|null $profile store profile (defaults to the active store)
     * @return array{industry:string,industry_label:string,terms:array,screens:array,synonyms:array}
     */
    function mp_industry_vocabulary($profile = null) {
        if ($profile === null) {
            $profile = function_exists('mp_get_store_profile') ? mp_get_store_profile() : array();
        }
        $industry = $profile['industry_type'] ?? 'general_retail';
        $types    = function_exists('mp_get_business_types') ? mp_get_business_types() : array();
        $presets  = function_exists('mp_get_business_presets') ? mp_get_business_presets() : array();
        $preset   = isset($presets[$industry]) ? $presets[$industry] : (isset($presets['general_retail']) ? $presets['general_retail'] : array());

        // Canonical terms: the store's own label for each concept. Labels are
        // already resolved on the profile (preset + store overrides), so no
        // per-key database lookups are needed here.
        $term_keys = array('item','product','customer','supplier','service','service_order','appointment',
            'order','quote','warehouse','branch','staff','production','recipe','sale','purchase','expense','batch');
        $defaults = function_exists('mp_get_label_defaults') ? mp_get_label_defaults() : array();
        $labels   = isset($profile['labels']) && is_array($profile['labels']) ? $profile['labels'] : array();
        $merged   = array_merge($defaults, $labels);
        $terms    = array();
        foreach ($term_keys as $key) {
            if (!empty($merged[$key])) $terms[$key] = $merged[$key];
        }

        // Screens the industry exposes — the 4th capability column is the route.
        $screen_labels = function_exists('mp_industry_screen_labels') ? mp_industry_screen_labels() : array();
        // Core screens every store has; industry capabilities add/override.
        $screens = array(
            'pos'       => isset($screen_labels['pos']) ? $screen_labels['pos'] : 'POS',
            'items'     => isset($screen_labels['items']) ? $screen_labels['items'] : 'Products',
            'inventory' => isset($screen_labels['inventory']) ? $screen_labels['inventory'] : 'Inventory',
            'sales'     => isset($screen_labels['sales']) ? $screen_labels['sales'] : 'Sales',
            'customers' => isset($screen_labels['customers']) ? $screen_labels['customers'] : 'Customers',
            'purchases' => isset($screen_labels['purchases']) ? $screen_labels['purchases'] : 'Purchases',
            'expenses'  => isset($screen_labels['expenses']) ? $screen_labels['expenses'] : 'Expenses',
            'reports'   => isset($screen_labels['reports']) ? $screen_labels['reports'] : 'Reports',
        );
        if (isset($preset['capabilities']) && is_array($preset['capabilities'])) {
            foreach ($preset['capabilities'] as $capability => $meta) {
                $route = isset($meta[2]) ? trim((string)$meta[2]) : '';
                if ($route === '' || isset($screens[$route])) continue;
                $screens[$route] = isset($screen_labels[$route]) ? $screen_labels[$route] : ucwords(str_replace('_', ' ', (string)$capability));
            }
        }

        // Synonyms: informal words -> canonical concept key, seeded from the
        // store's labels then extended per industry.
        $synonyms = array();
        foreach ($terms as $key => $label) {
            $lower = strtolower($label);
            if ($lower !== '' && $lower !== strtolower($key)) $synonyms[$lower] = $key;
        }
        $extra = function_exists('mp_industry_synonym_map') ? mp_industry_synonym_map($industry) : array();
        foreach ($extra as $word => $key) { $synonyms[$word] = $key; }

        return array(
            'industry'       => $industry,
            'industry_label' => isset($types[$industry]) ? $types[$industry] : 'General Retail',
            'terms'          => $terms,
            'screens'        => $screens,
            'synonyms'       => $synonyms,
        );
    }
}
