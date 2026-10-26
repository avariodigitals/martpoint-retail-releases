<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Demo-only content seeder.
 *
 * Additive and idempotent. It uses MartPoint's real tables and is called by
 * Install_seed plus the guarded local/demo industry-switch path. It does not
 * touch centralisation files.
 */
class Demo_seeder extends CI_Model {

    private $blueprints = array(
        'pharmacy' => array(
            'store_name' => 'Zonat Pharmacy & Healthcare', 'theme' => array('#0F766E', '#14B8A6'),
            'units' => array('Pack' => 'PK', 'Bottle' => 'BTL', 'Tablet' => 'TAB', 'Piece' => 'PCS'),
            'categories' => array('Prescription Drugs', 'Over-the-Counter', 'Medical Supplies', 'Vitamins & Supplements', 'Dermatology', 'Skincare'),
            'products' => array(
                array('Amoxicillin 500mg', 'Prescription Drugs', 1200, 800, 100), array('Paracetamol 500mg', 'Over-the-Counter', 400, 200, 500),
                array('Digital Thermometer', 'Medical Supplies', 4500, 3000, 20), array('Vitamin C 1000mg', 'Vitamins & Supplements', 2500, 1500, 60),
                array('Hydrocortisone Cream', 'Dermatology', 3200, 1800, 40), array('Omeprazole 20mg', 'Prescription Drugs', 1500, 900, 120),
                array('Cetirizine Tablets', 'Over-the-Counter', 800, 400, 300), array('Blood Pressure Monitor', 'Medical Supplies', 12000, 8000, 15),
                array('Gentle Facial Cleanser', 'Skincare', 6500, 3500, 80), array('Moisturising Body Lotion', 'Skincare', 5500, 2800, 100),
                array('SPF 50 Sunscreen', 'Skincare', 9000, 5000, 60), array('Aloe Vera Soothing Gel', 'Skincare', 4800, 2400, 75),
            ),
            'services' => array('Medication Review', 'Blood Pressure Check', 'Prescription Refill Support'),
            'suppliers' => array('Global Pharma Distribution', 'HealthLine Medical', 'CarePlus Wholesale'),
            'posts' => array('Understanding Your Prescriptions', 'Pharmacy Care Tips', 'Boosting Immunity Safely'),
        ),
        'building_materials' => array(
            'store_name' => 'Zonat Building Materials & Hardware', 'theme' => array('#92400E', '#F59E0B'),
            'store_subheadline' => 'Doors, perimeter protection and loading systems for residential, commercial and industrial projects.',
            'store_description' => 'Building systems catalogue for doors, secure access, perimeter protection and loading technology.',
            'units' => array('Piece' => 'PCS', 'Set' => 'SET', 'Square Metre' => 'SQM', 'Metre' => 'M'),
            'categories' => array('Doors & Access Systems', 'Perimeter Protection', 'Loading Technology', 'Frames & Hardware', 'Safety & Security'),
            'products' => array(
                array('Industrial Sectional Door', 'Doors & Access Systems', 1850000, 1450000, 8),
                array('High-Speed Fabric Door', 'Doors & Access Systems', 2650000, 2100000, 5),
                array('Fire-Rated Steel Door', 'Doors & Access Systems', 485000, 350000, 18),
                array('Secure Entrance Door Set', 'Doors & Access Systems', 720000, 520000, 12),
                array('Roller Shutter Door', 'Doors & Access Systems', 980000, 740000, 10),
                array('Automatic Sliding Door Operator', 'Doors & Access Systems', 1250000, 930000, 6),
                array('Fixed Security Bollard', 'Perimeter Protection', 165000, 105000, 40),
                array('Automatic Sliding Gate Operator', 'Perimeter Protection', 890000, 650000, 7),
                array('Loading Dock Leveler', 'Loading Technology', 3250000, 2500000, 3),
                array('Dock Shelter System', 'Loading Technology', 1450000, 1050000, 4),
                array('Door Frame System', 'Frames & Hardware', 275000, 180000, 24),
                array('Emergency Exit Hardware Kit', 'Safety & Security', 195000, 125000, 30),
            ),
            'services' => array('Site Survey & Measurement', 'Door Installation & Commissioning', 'Preventive Maintenance Visit'),
            'suppliers' => array('Access Systems West Africa', 'SecureBuild Wholesale', 'Loading Bay Technologies'),
            'posts' => array(
                array('kind' => 'insight', 'title' => 'Choosing the Right Industrial Door for Your Opening', 'excerpt' => 'A practical guide to selecting door speed, security, insulation and operating cycles for a working building.', 'body' => "The right industrial door starts with the opening and the way the space is used. Consider traffic frequency, clear opening size, security, ventilation and the temperature of the environment before choosing a system.\n\nSectional, roller and high-speed doors each solve a different operational problem. A site survey helps confirm the dimensions, access conditions and safety clearances before a quotation is prepared.", 'meta' => array('systems' => 'Industrial doors, High-speed doors', 'sector' => 'Industrial buildings')),
                array('kind' => 'insight', 'title' => 'What to Measure Before Specifying a Door', 'excerpt' => 'The site details that make an entrance or loading-door quotation accurate from the first visit.', 'body' => "Record the clear width and height, lintel and side-room space, floor level, traffic pattern and available power. Photograph the approach and note any drainage, fire-safety or access-control constraints.\n\nA complete survey reduces revisions and gives the installation team a clear starting point.", 'meta' => array('systems' => 'Door frames, Access control', 'sector' => 'Commercial and residential')),
                array('kind' => 'insight', 'title' => 'Loading Bay Safety: Levelers, Shelters and Seals', 'excerpt' => 'How coordinated loading technology keeps people, goods and vehicles moving safely between dock and warehouse.', 'body' => "A loading bay works as a system. Dock levelers bridge the height difference, dock shelters reduce exposure to weather and dock seals help maintain a cleaner, safer transfer point.\n\nThe right combination depends on vehicle types, dock height, operating frequency and the goods being handled.", 'meta' => array('systems' => 'Dock levelers, Dock shelters', 'sector' => 'Warehousing and logistics')),
                array('kind' => 'application', 'title' => 'Industrial & Warehouse Doors', 'excerpt' => 'Keep people, vehicles and materials moving with door systems specified for demanding daily cycles.', 'body' => "From receiving bays to production floors, industrial openings need reliable movement, protection and safe separation. We help teams compare sectional, roller and high-speed door options around the actual traffic pattern and operating conditions.\n\nThe process begins with a site survey and ends with installation, commissioning and a clear maintenance plan.", 'meta' => array('systems' => 'Sectional doors, High-speed doors, Roller shutters', 'sector' => 'Industrial and logistics', 'location' => 'West Africa')),
                array('kind' => 'application', 'title' => 'Commercial Entrances & Secure Access', 'excerpt' => 'Create a considered first arrival for offices, retail spaces, estates and public-facing buildings.', 'body' => "Entrances balance appearance, accessibility, security and daily wear. A coordinated door, frame and access-control package helps a building feel welcoming while protecting the people and assets inside.\n\nWe can support the specification from concept and opening dimensions through installation and after-sales care.", 'meta' => array('systems' => 'Entrance doors, Frames, Access control', 'sector' => 'Commercial and residential', 'location' => 'Nigeria')),
                array('kind' => 'application', 'title' => 'Loading Bays & Distribution Centres', 'excerpt' => 'Build a safer, more efficient handover between delivery vehicles and the warehouse floor.', 'body' => "Busy distribution centres need loading points that are safe, durable and suited to the vehicles using them. Dock levelers, shelters and seals work together to support faster loading while reducing exposure to rain, dust and temperature changes.\n\nWe review the dock layout, vehicle profile and operating rhythm before recommending a system.", 'meta' => array('systems' => 'Dock levelers, Dock shelters, Dock seals', 'sector' => 'Logistics and distribution', 'location' => 'Lagos')),
                array('kind' => 'project', 'title' => 'Warehouse Loading Bay Retrofit', 'excerpt' => 'A coordinated loading upgrade for a high-throughput warehouse receiving area.', 'body' => "The receiving area needed safer vehicle-to-floor movement without slowing daily deliveries. The scope combined a dock leveler, dock shelter and revised safety clearances around the loading position.\n\nThe result is a clearer handover point for drivers, warehouse teams and incoming goods, with maintenance considerations documented for the facility team.", 'meta' => array('systems' => 'Dock leveler, Dock shelter', 'sector' => 'Warehousing', 'location' => 'Lagos', 'year' => '2026')),
                array('kind' => 'project', 'title' => 'Corporate Office Entrance Upgrade', 'excerpt' => 'A more secure and accessible entrance sequence for a growing commercial office.', 'body' => "The project brought the entrance door, frame and access-control requirements into one coordinated package. The team reviewed visitor flow, accessibility and the relationship between the public entrance and the secure office areas.\n\nThe completed entrance is easier to navigate and gives the facilities team a clearer maintenance path.", 'meta' => array('systems' => 'Secure entrance doors, Frames', 'sector' => 'Commercial office', 'location' => 'Ikeja', 'year' => '2026')),
                array('kind' => 'project', 'title' => 'Residential Perimeter & Gate System', 'excerpt' => 'A durable access point that balances daily convenience with perimeter security.', 'body' => "The brief called for a robust gate approach with controlled vehicle access and a clean residential arrival. The solution paired an automatic sliding gate operator with fixed security bollards and a straightforward service plan.\n\nThe site review focused on vehicle turning space, drainage, power availability and safe pedestrian movement.", 'meta' => array('systems' => 'Sliding gate operator, Security bollards', 'sector' => 'Residential estate', 'location' => 'Abuja', 'year' => '2026')),
            ),
        ),
        'frozen' => array(
            'store_name' => 'Zonat Frozen Foods & Cold Store', 'theme' => array('#1D4ED8', '#38BDF8'),
            'units' => array('Kilogram' => 'KG', 'Piece' => 'PCS', 'Carton' => 'CTN', 'Gram' => 'G'),
            'categories' => array('Frozen Poultry', 'Seafood', 'Frozen Vegetables', 'Ready-to-Eat', 'Dairy & Ice Cream'),
            'products' => array(
                array('Frozen Chicken Breast 1kg', 'Frozen Poultry', 6000, 4500, 80), array('Atlantic Salmon Fillet', 'Seafood', 15000, 11000, 30),
                array('Mixed Vegetables 500g', 'Frozen Vegetables', 2000, 1200, 120), array('Frozen French Fries 2kg', 'Ready-to-Eat', 4000, 2500, 100),
                array('Vanilla Ice Cream 1L', 'Dairy & Ice Cream', 7000, 4000, 50), array('Frozen Prawns 500g', 'Seafood', 8000, 5000, 60),
                array('Chicken Wings 2kg', 'Frozen Poultry', 7500, 5000, 100), array('Frozen Peas 1kg', 'Frozen Vegetables', 1800, 1000, 150),
            ),
            'services' => array('Cold-Chain Delivery', 'Bulk Order Preparation', 'Freezer Audit'),
            'suppliers' => array('Arctic Cold Chain', 'OceanFresh Imports', 'Frosty Logistics'),
            'posts' => array('Cold Chain Logistics', 'Fast Frozen Meals', 'Storing Frozen Foods Safely'),
        ),
        'garden_centre' => array(
            'store_name' => 'Zonat Garden Centre & Landscaping', 'theme' => array('#166534', '#84CC16'),
            'units' => array('Piece' => 'PCS', 'Pot' => 'POT', 'Litre' => 'L', 'Bag' => 'BAG', 'Square Metre' => 'SQM'),
            'categories' => array('Ornamental Plants', 'Seeds & Seedlings', 'Garden Tools', 'Soil & Fertilizers', 'Outdoor Decor'),
            'products' => array(
                array('Hybrid Rose Bush', 'Ornamental Plants', 5000, 3000, 30), array('Organic Potting Soil 20L', 'Soil & Fertilizers', 3500, 2000, 100),
                array('Ergonomic Hand Trowel', 'Garden Tools', 2500, 1200, 50), array('Tomato Hybrid Seeds', 'Seeds & Seedlings', 1500, 800, 200),
                array('Ceramic Garden Pot Large', 'Outdoor Decor', 12000, 7000, 15), array('Automatic Drip System', 'Garden Tools', 25000, 18000, 10),
                array('Palm Tree Seedling', 'Ornamental Plants', 8000, 5000, 20), array('Liquid Fertilizer 1L', 'Soil & Fertilizers', 4000, 2000, 60),
            ),
            'services' => array('Garden Design Consultation', 'Planting & Maintenance', 'Landscape Site Visit'),
            'suppliers' => array('GreenWorld Flora', 'TerraLand Supplies', 'BioGrow Imports'),
            'posts' => array('Urban Gardening 101', 'The Secret to Healthy Soil', 'Seasonal Planting Guide'),
        ),
        'fashion' => array(
            'store_name' => 'Zonat Fashion & Apparel', 'theme' => array('#9D174D', '#F472B6'),
            'units' => array('Piece' => 'PCS', 'Pair' => 'PR', 'Set' => 'SET', 'Yard' => 'YD'),
            'categories' => array('Menswear', 'Womenswear', 'Accessories', 'Footwear', 'Kids Fashion'),
            'products' => array(
                array('Slim Fit Cotton Shirt', 'Menswear', 12000, 7000, 60), array('Summer Floral Dress', 'Womenswear', 18000, 10000, 40),
                array('Leather Crossbody Bag', 'Accessories', 25000, 15000, 20), array('Classic Leather Loafers', 'Footwear', 30000, 18000, 30),
                array('Kids Denim Jacket', 'Kids Fashion', 8000, 4000, 50), array('Silk Necktie Blue', 'Accessories', 5000, 2000, 100),
                array('High-Waist Trousers', 'Womenswear', 14000, 8000, 60), array('Cashmere Sweater', 'Menswear', 22000, 12000, 30),
            ),
            'services' => array('Personal Styling', 'Alterations & Fittings', 'Bridal Fitting'),
            'suppliers' => array('StyleHub Manufacturing', 'Elite Fabrics Co.', 'TrendSetters Wholesale'),
            'posts' => array('2026 Fashion Trends', 'Capsule Wardrobe Guide', 'Fabric Care 101'),
        ),
        'restaurant' => array(
            'store_name' => 'Zonat Gourmet Kitchen', 'theme' => array('#9A3412', '#FB923C'),
            'units' => array('Portion' => 'PORT', 'Plate' => 'PLT', 'Piece' => 'PCS', 'Litre' => 'L'),
            'categories' => array('Main Course', 'Appetizers', 'Drinks', 'Desserts', 'Brunch'),
            'products' => array(
                array('Grilled Salmon Steak', 'Main Course', 15000, 8000, 999, 'kitchen'), array('Garlic Butter Shrimp', 'Appetizers', 7000, 3500, 999, 'kitchen'),
                array('Fresh Orange Juice', 'Drinks', 3000, 1000, 999, 'bar'), array('Chocolate Lava Cake', 'Desserts', 5000, 2000, 999, 'kitchen'),
                array('Avocado Toast', 'Brunch', 6500, 3000, 999, 'kitchen'), array('Truffle Pasta', 'Main Course', 18000, 9000, 999, 'kitchen'),
                array('Spring Rolls', 'Appetizers', 4000, 1500, 999, 'kitchen'), array('Iced Caramel Macchiato', 'Drinks', 4500, 1200, 999, 'bar'),
            ),
            'services' => array('Private Dining Setup', 'Catering Delivery', 'Event Menu Planning'),
            'suppliers' => array('FreshFarm Produce', 'DairyDirect', 'Oceanic Seafoods'),
            'posts' => array('The Art of Plating', 'Farm to Table Journey', 'The Perfect Brunch'),
        ),
        'skincare' => array(
            'store_name' => 'Zonat Glow Skincare', 'theme' => array('#86198F', '#E879F9'),
            'units' => array('Piece' => 'PCS', 'Millilitre' => 'ML', 'Gram' => 'G', 'Bottle' => 'BTL'),
            'categories' => array('Serums', 'Moisturizers', 'Cleansers', 'Sun Protection', 'Anti-Aging'),
            'products' => array(
                array('Hyaluronic Acid Serum', 'Serums', 8000, 4000, 100), array('Ceramide Night Cream', 'Moisturizers', 12000, 6000, 60),
                array('Gentle Foaming Cleanser', 'Cleansers', 5000, 2500, 120), array('SPF 50 Sunscreen Gel', 'Sun Protection', 7000, 3500, 80),
                array('Retinol Treatment', 'Anti-Aging', 15000, 7000, 40), array('Vitamin C Glow Serum', 'Serums', 9000, 4500, 70),
                array('Deep Hydrating Mask', 'Moisturizers', 6000, 3000, 90), array('Mineral Sunscreen Stick', 'Sun Protection', 8500, 4000, 50),
            ),
            'services' => array('Skin Consultation', 'Facial Treatment', 'Product Routine Review'),
            'suppliers' => array('PureSkin Labs', 'DermaCare Wholesale', 'BioGlow Inc.'),
            'posts' => array('Morning vs Evening Routine', 'The Truth about SPF', 'Understanding Skin Types'),
        ),
        'physiotherapy' => array(
            'store_name' => 'Zonat Physiotherapy & Rehabilitation', 'theme' => array('#0E7490', '#22D3EE'),
            'units' => array('Session' => 'SES', 'Piece' => 'PCS', 'Pack' => 'PK'),
            'categories' => array('Rehabilitation', 'Pain Relief', 'Mobility Aids', 'Exercise Equipment'),
            'products' => array(
                array('Resistance Band Set', 'Exercise Equipment', 8500, 5000, 30), array('Foam Roller', 'Exercise Equipment', 12000, 7000, 20),
                array('Knee Support Brace', 'Mobility Aids', 9500, 5500, 25), array('Hot & Cold Pack', 'Pain Relief', 5000, 2500, 40),
                array('Ankle Support', 'Mobility Aids', 7000, 4000, 30), array('Balance Cushion', 'Rehabilitation', 15000, 9000, 15),
            ),
            'services' => array('Initial Physiotherapy Assessment', 'Sports Rehabilitation Session', 'Follow-up Therapy'),
            'suppliers' => array('RehabCare Supplies', 'Motion Health Wholesale', 'ActiveLife Medical'),
            'posts' => array('When to See a Physiotherapist', 'Simple Mobility Exercises', 'Returning to Sport Safely'),
        ),
        'general_retail' => array(
            'store_name' => 'Zonat General Retail', 'theme' => array('#1E3A8A', '#60A5FA'),
            'units' => array('Piece' => 'PCS', 'Pack' => 'PK', 'Box' => 'BOX'),
            'categories' => array('Featured Products', 'Household', 'Personal Care', 'Food & Drinks'),
            'products' => array(
                array('Everyday Essentials Pack', 'Featured Products', 5000, 3000, 50), array('Household Storage Box', 'Household', 7000, 4000, 30),
                array('Personal Care Kit', 'Personal Care', 6500, 3500, 40), array('Breakfast Pantry Pack', 'Food & Drinks', 9000, 6000, 35),
            ),
            'services' => array('Gift Wrapping', 'Product Sourcing', 'Local Delivery'),
            'suppliers' => array('Zonat Wholesale Partners', 'Everyday Distribution', 'MarketLink Supplies'),
            'posts' => array('Welcome to Zonat Retail', 'How to Shop Smarter', 'New Arrivals This Month'),
        ),
    );

    public function seed_full_demo($store_id, $industry_type) {
        $store_id = (int)$store_id;
        if ($store_id < 1) return array('ok' => false, 'error' => 'Invalid store.');
        $key = $this->_canonical_industry($industry_type);
        $data = $this->blueprints[$key];
        $result = array('ok' => true, 'industry' => $key, 'products' => 0, 'services' => 0, 'customers' => 0, 'leads' => 0, 'suppliers' => 0, 'purchase_invoices' => 0, 'posts' => 0, 'accounts' => 0, 'locations' => 0, 'prescriptions' => 0);

        $this->db->trans_begin();
        try {
            // The demo is a switchable showcase tenant. On the first seed or
            // an industry switch, archive the previous tenant data before
            // rebuilding the selected industry. Nothing is hard-deleted, so
            // old demo actions remain available for audit/reporting while
            // active screens show only the selected industry's dataset.
            if ($this->_demo_industry_switch_requires_reset($store_id, $data['store_name'])) {
                $this->_reset_demo_store_content($store_id);
            }
            $this->load->model('default_data_model', 'default_data');
            $this->default_data->seed_industry_defaults($store_id, $this->_default_industry($industry_type, $key), true);
            if ($this->db->table_exists('db_store')) $this->db->where('id', $store_id)->update('db_store', array('store_name' => $data['store_name']));

            // A switch must change the active demo data set without hard-deleting
            // history. Old demo master data and related transactional rows are
            // retired from active screens while the source rows remain available
            // for audit and reporting.
            $this->_retire_previous_demo_content($store_id, $key, $data['suppliers']);
            if ($key === 'building_materials') $this->_retire_building_non_catalogue_content($store_id);

            $units = $this->_seed_units($store_id, $data['units']);
            $categories = $this->_seed_categories($store_id, $data['categories']);
            if ($key === 'restaurant') {
                $this->_retire_restaurant_non_catalogue_content($store_id);
                $this->_seed_restaurant_locations($store_id, $result);
            }
            if ($key === 'pharmacy') $this->_retire_pharmacy_non_catalogue_content($store_id);
            $suppliers = $this->_seed_suppliers($store_id, $data['suppliers'], $result);
            $items = $this->_seed_products($store_id, $data['products'], $units, $categories, $result, $key);
            if ($key === 'pharmacy') $this->_seed_pharmacy_locations($store_id, $items, $result);
            $this->_seed_services($store_id, $data['services'], $categories, $result);
            if ($key === 'pharmacy') $this->_retire_pharmacy_non_catalogue_orders($store_id, $this->_pharmacy_category_ids($store_id));
            $this->_retire_inactive_item_transactions($store_id);
            $this->_seed_customers($store_id, $key, $result);
            if ($key === 'pharmacy') $this->_seed_pharmacy_prescriptions($store_id, $items, $result);
            $this->_seed_leads($store_id, $key, $data['store_name'], $result);
            $this->_seed_accounts($store_id, $data['store_name'], $result);
            $this->_seed_purchase($store_id, $suppliers, $items, $result, $key);
            $this->_seed_storefront($store_id, $data, $key);
            $this->_seed_storefront_posts($store_id, $data['posts'], $key, $result);

            if (!$this->db->trans_status()) throw new RuntimeException('Demo seed transaction failed.');
            $this->db->trans_commit();
        } catch (Throwable $e) {
            $this->db->trans_rollback();
            log_message('error', 'Demo seed failed for store '.$store_id.': '.$e->getMessage());
            return array('ok' => false, 'industry' => $key, 'error' => $e->getMessage());
        }
        return $result;
    }

    private function _demo_industry_switch_requires_reset($store_id, $targetStoreName) {
        if (!$this->db->table_exists('db_store')) return true;
        $store = $this->db->select('store_name')->where('id', (int)$store_id)->get('db_store')->row();
        if (!$store) return true;
        return strcasecmp(trim((string)$store->store_name), trim((string)$targetStoreName)) !== 0;
    }

    private function _reset_demo_store_content($store_id) {
        $updates = array(
            array('db_items', array('status' => 0, 'publish_online' => 0, 'online_excluded' => 1)),
            array('db_services', array('status' => 0, 'available_online' => 0)),
            array('db_category', array('status' => 0)),
            array('db_units', array('status' => 0)),
            array('db_brands', array('status' => 0)),
            array('db_recipe_categories', array('status' => 0)),
            array('db_suppliers', array('status' => 0)),
            array('db_customers', array('status' => 0)),
            array('db_leads', array('status' => 'archived')),
            array('ac_accounts', array('status' => 0, 'delete_bit' => 1)),
            array('db_storefront_posts', array('status' => 'archived')),
            array('db_purchase', array('status' => 0)),
            array('db_online_orders', array('status' => 0)),
            array('db_medical_notes', array('status' => 'closed')),
        );
        foreach ($updates as $entry) {
            list($table, $values) = $entry;
            if (!$this->db->table_exists($table)) continue;
            $this->db->where('store_id', (int)$store_id)->update($table, $this->_filter($table, $values));
        }
    }

    private function _canonical_industry($industry) {
        $industry = strtolower(trim((string)$industry));
        $map = array(
            'building' => 'building_materials', 'building_materials' => 'building_materials',
            'frozen_foods_retailer' => 'frozen', 'frozen_foods' => 'frozen', 'frozen' => 'frozen',
            'garden' => 'garden_centre', 'garden_centre_landscaping' => 'garden_centre', 'garden_centre' => 'garden_centre',
            'physio' => 'physiotherapy', 'physiotherapy_rehabilitation' => 'physiotherapy',
            'pharmacy' => 'pharmacy', 'fashion' => 'fashion', 'skincare' => 'skincare',
            // Food presets share the RestaurantOS catalogue and fulfilment flow.
            'restaurant' => 'restaurant', 'fast_food' => 'restaurant', 'cafe' => 'restaurant',
            'pizza_shop' => 'restaurant', 'shawarma' => 'restaurant', 'juice_bar' => 'restaurant',
            'buka' => 'restaurant', 'canteen' => 'restaurant', 'food_delivery' => 'restaurant',
        );
        return isset($map[$industry]) ? $map[$industry] : 'general_retail';
    }

    private function _retire_previous_demo_content($store_id, $industry, $currentSuppliers = array()) {
        if ($this->db->table_exists('db_items') && $this->db->field_exists('sku', 'db_items')) {
            $this->db->where('store_id', $store_id)->like('sku', 'ZONAT-'.$store_id.'-', 'after')
                ->update('db_items', $this->_filter('db_items', array('status' => 0, 'publish_online' => 0, 'online_excluded' => 1)));
        }
        if ($this->db->table_exists('db_services')) {
            $this->db->where('store_id', $store_id)->like('description', 'Zonat ', 'after')->like('description', ' service.')
                ->update('db_services', $this->_filter('db_services', array('status' => 0, 'available_online' => 0)));
        }
        if ($this->db->table_exists('db_category')) {
            $this->db->where('store_id', $store_id)->like('description', 'Demo ', 'after')->like('description', ' catalogue')
                ->update('db_category', $this->_filter('db_category', array('status' => 0)));
        }
        if ($this->db->table_exists('db_storefront_posts')) {
            $this->db->where('store_id', $store_id)->like('body', 'practical guidance and useful ideas from Zonat')
                ->update('db_storefront_posts', $this->_filter('db_storefront_posts', array('status' => 'archived')));
            foreach (array_keys($this->blueprints) as $prefix) {
                if ($prefix === $industry) continue;
                $this->db->where('store_id', $store_id)->like('slug', $prefix.'-', 'after')
                    ->update('db_storefront_posts', $this->_filter('db_storefront_posts', array('status' => 'archived')));
            }
        }
        if ($this->db->table_exists('db_medical_notes')) {
            $this->db->where('store_id', $store_id)->like('prescription_ref', 'RX-DEMO-', 'after')
                ->update('db_medical_notes', $this->_filter('db_medical_notes', array('status' => 'closed')));
        }
        if ($this->db->table_exists('db_suppliers') && $this->db->field_exists('created_by', 'db_suppliers')) {
            $this->db->where('store_id', $store_id)->where('created_by', 'demo');
            if ($currentSuppliers) $this->db->where_not_in('supplier_name', $currentSuppliers);
            $this->db->update('db_suppliers', $this->_filter('db_suppliers', array('status' => 0)));
        }
        if ($this->db->table_exists('db_customers') && $this->db->field_exists('email', 'db_customers')) {
            $this->db->where('store_id', $store_id)->like('email', 'demo.', 'after')
                ->not_like('email', 'demo.'.$industry.'.', 'after')
                ->update('db_customers', $this->_filter('db_customers', array('status' => 0)));
        }
        if ($this->db->table_exists('db_leads') && $this->db->field_exists('email', 'db_leads')) {
            $this->db->where('store_id', $store_id)->like('email', 'demo.', 'after')
                ->not_like('email', 'demo.'.$industry.'.', 'after')
                ->update('db_leads', $this->_filter('db_leads', array('status' => 'archived')));
        }
    }

    /** Keep the Building Materials demo from exposing old cross-industry test data. */
    private function _retire_building_non_catalogue_content($store_id) {
        if ($this->db->table_exists('db_items')) {
            $this->db->where('store_id', $store_id)->group_start()
                ->where_in('product_type', array('digital', 'course', 'membership'))
                ->or_like('sku', 'DIGITAL_TEST_', 'after')
                ->or_like('sku', 'COURSE_TEST_', 'after')
                ->or_like('sku', 'MEMBERSHIP_TEST_', 'after')
                ->or_like('item_name', 'Test Creator', 'after')
                ->group_end()
                ->update('db_items', $this->_filter('db_items', array('status' => 0, 'publish_online' => 0, 'online_excluded' => 1)));

            if ($this->db->table_exists('db_category')) {
                $legacy = $this->db->select('id')->where('store_id', $store_id)
                    ->where_in('category_name', array('Dress', 'Electronics', 'Shapoodjhoif', 'Slipper/Shoe'))
                    ->get('db_category')->result();
                $legacyIds = array_map(function ($row) { return (int)$row->id; }, $legacy);
                if ($legacyIds) {
                    $this->db->where('store_id', $store_id)->where_in('category_id', $legacyIds)
                        ->update('db_items', $this->_filter('db_items', array('status' => 0, 'publish_online' => 0, 'online_excluded' => 1)));
                }
            }
        }
    }

    /**
     * Keep the RestaurantOS demo catalogue and supporting master data food-only.
     *
     * This runs only through the guarded demo seeder, never during an ordinary
     * merchant profile save. Rows are archived instead of deleted so old demo
     * transactions retain their foreign-key history while active screens show
     * only the restaurant dataset.
     */
    private function _retire_restaurant_non_catalogue_content($store_id) {
        $allowedCategories = array(
            'Main Course', 'Mains', 'Appetizers', 'Starters & Small Chops',
            'Sides & Extras', 'Drinks', 'Desserts', 'Brunch', 'Combos & Platters',
        );
        $allowedUnits = array('Portion', 'Plate', 'Cup', 'Bottle', 'Piece', 'Kilogram', 'Litre');
        $allowedServices = array('Private Dining Setup', 'Catering Delivery', 'Event Menu Planning');
        $allowedSuppliers = array('FreshFarm Produce', 'DairyDirect', 'Oceanic Seafoods');

        $categoryIds = array();
        if ($this->db->table_exists('db_category')) {
            $categoryRows = $this->db->select('id')->where('store_id', $store_id)
                ->where_in('category_name', $allowedCategories)->get('db_category')->result();
            $categoryIds = array_map(function ($row) { return (int) $row->id; }, $categoryRows);

            $categoryUpdate = $this->db->where('store_id', $store_id);
            if ($allowedCategories) $categoryUpdate->where_not_in('category_name', $allowedCategories);
            $categoryUpdate->update('db_category', $this->_filter('db_category', array('status' => 0)));
        }

        if ($this->db->table_exists('db_items')) {
            $itemUpdate = $this->db->where('store_id', $store_id)->group_start();
            if ($categoryIds) {
                $itemUpdate->where_not_in('category_id', $categoryIds)
                    ->or_where('category_id', 0)
                    ->or_where('category_id IS NULL', null, false);
            } else {
                $itemUpdate->where('id >', 0);
            }
            $itemUpdate->group_end()->update('db_items', $this->_filter('db_items', array(
                'status' => 0, 'publish_online' => 0, 'online_excluded' => 1,
            )));
        }

        if ($this->db->table_exists('db_units')) {
            $this->db->where('store_id', $store_id)->where_not_in('unit_name', $allowedUnits)
                ->update('db_units', $this->_filter('db_units', array('status' => 0)));
        }

        if ($this->db->table_exists('db_recipe_categories') && $this->db->field_exists('name', 'db_recipe_categories')) {
            $this->db->where('store_id', $store_id)->where_not_in('name', array('Mains', 'Starters', 'Drinks', 'Desserts', 'Combos'))
                ->update('db_recipe_categories', $this->_filter('db_recipe_categories', array('status' => 0)));
        }

        if ($this->db->table_exists('db_services')) {
            $this->db->where('store_id', $store_id)->where_not_in('service_name', $allowedServices)
                ->update('db_services', $this->_filter('db_services', array('status' => 0, 'available_online' => 0)));
        }

        if ($this->db->table_exists('db_suppliers')) {
            $this->db->where('store_id', $store_id)->where_not_in('supplier_name', $allowedSuppliers)
                ->update('db_suppliers', $this->_filter('db_suppliers', array('status' => 0)));
        }

        // A previous pharmacy demo could leave its fulfilment locations active
        // because warehouses are operational master data, not catalogue rows.
        // Archive only unmistakable pharmacy/demo locations; never touch a
        // merchant's legitimate restaurant branches.
        if ($this->db->table_exists('db_warehouse')) {
            $rows = $this->db->where('store_id', $store_id)->get('db_warehouse')->result();
            foreach ($rows as $row) {
                $signals = implode(' ', array(
                    (string)($row->warehouse_name ?? ''),
                    (string)($row->address ?? ''),
                    (string)($row->delivery_coverage ?? ''),
                    (string)($row->email ?? ''),
                ));
                $type = strtolower(trim((string)($row->branch_type ?? '')));
                if (in_array($type, array('partner', 'pickup_hub'), true)
                    || preg_match('/pharmacy|medical|prescription|fulfilment|fulfillment|collection hub/i', $signals)) {
                    $this->db->where('id', (int)$row->id)->where('store_id', $store_id)
                        ->update('db_warehouse', $this->_filter('db_warehouse', array('status' => 0, 'public_enabled' => 0)));
                }
            }
        }
    }

    /** Seed clearly restaurant-owned locations for the switchable demo tenant. */
    private function _seed_restaurant_locations($store_id, &$result) {
        if (!$this->db->table_exists('db_warehouse')) return;
        $rowsQuery = $this->db->where('store_id', $store_id)->where('status', 1);
        if ($this->db->field_exists('warehouse_type', 'db_warehouse')) $rowsQuery->where('warehouse_type', 'Custom');
        if ($this->db->field_exists('public_enabled', 'db_warehouse')) $rowsQuery->where('public_enabled', 1);
        $rows = $rowsQuery->get('db_warehouse')->result();
        foreach ($rows as $row) {
            $type = strtolower(trim((string)($row->branch_type ?? '')));
            $signals = implode(' ', array((string)($row->warehouse_name ?? ''), (string)($row->email ?? '')));
            if (!in_array($type, array('partner', 'pickup_hub'), true)
                && !preg_match('/pharmacy|medical|prescription|fulfilment|fulfillment|collection hub/i', $signals)) return;
        }

        $locations = array(
            array('name' => 'Ikeja GRA Kitchen', 'address' => '14 Isaac John Street, Ikeja GRA', 'city' => 'Ikeja', 'state' => 'Lagos'),
            array('name' => 'Lekki Phase 1 Kitchen', 'address' => '12 Fola Osibo Street, Lekki Phase 1', 'city' => 'Lekki', 'state' => 'Lagos'),
            array('name' => 'Wuse 2 Kitchen', 'address' => '8 Aminu Kano Crescent, Wuse 2', 'city' => 'Abuja', 'state' => 'FCT'),
        );
        foreach ($locations as $location) {
            $id = $this->_save('db_warehouse', array('store_id' => $store_id, 'warehouse_name' => $location['name']), array(
                'store_id' => $store_id, 'warehouse_type' => 'Custom', 'warehouse_name' => $location['name'],
                'address' => $location['address'], 'city' => $location['city'], 'state' => $location['state'], 'country' => 'Nigeria',
                'branch_type' => 'branch', 'is_distribution_center' => 0, 'mobile' => '08000002026',
                'email' => 'demo.restaurant.'.$store_id.'@example.com', 'status' => 1, 'business_hours' => 'Mon-Sun · 10:00am-10:00pm',
                'delivery_coverage' => 'Delivery coverage confirmed at checkout', 'delivery_fee' => 0,
                'pickup_enabled' => 1, 'public_enabled' => 1, 'stock_source_id' => null,
            ));
            if ($id) $result['locations']++;
        }
    }

    /**
     * Keep the pharmacy demo catalogue pharmacy-only when the demo store was
     * previously seeded with the install fixture or another industry.
     *
     * Rows are retired rather than deleted so old demo transactions keep
     * their foreign-key references and reporting history remains readable.
     */
    private function _retire_pharmacy_non_catalogue_content($store_id) {
        $categoryIds = $this->_pharmacy_category_ids($store_id);

        if ($this->db->table_exists('db_category')) {
            if ($categoryIds) {
                $this->db->where('store_id', $store_id)->where_not_in('id', $categoryIds)
                    ->update('db_category', $this->_filter('db_category', array('status' => 0)));

                if ($this->db->table_exists('db_items')) {
                    $this->db->where('store_id', $store_id)->group_start()
                        ->where_not_in('category_id', $categoryIds)
                        ->or_where('category_id', 0)
                        ->or_where('category_id IS NULL', null, false)
                        ->group_end()
                        ->update('db_items', $this->_filter('db_items', array('status' => 0, 'publish_online' => 0, 'online_excluded' => 1)));
                }
            } elseif ($this->db->table_exists('db_items')) {
                $this->db->where('store_id', $store_id)
                    ->update('db_items', $this->_filter('db_items', array('status' => 0, 'publish_online' => 0, 'online_excluded' => 1)));
            }
        }

        // Retire old demo-only units left by another industry's seed pack.
        if ($this->db->table_exists('db_units')) {
            $this->db->where('store_id', $store_id)
                ->where_not_in('unit_name', array('Tablet', 'Capsule', 'Card', 'Pack', 'Bottle', 'Tube', 'Vial', 'Piece'))
                ->update('db_units', $this->_filter('db_units', array('status' => 0)));
        }

        // Keep demo services aligned with the selected pharmacy workflow.
        if ($this->db->table_exists('db_services')) {
            $allowedServices = array('Medication Review', 'Blood Pressure Check', 'Prescription Refill Support');
            $serviceQuery = $this->db->where('store_id', $store_id)->where_not_in('service_name', $allowedServices);
            if ($this->db->field_exists('created_by', 'db_services')) {
                $serviceQuery->group_start()->where('created_by', 'demo')->or_like('description', 'Zonat ', 'after')->group_end();
            } else {
                $serviceQuery->like('description', 'Zonat ', 'after');
            }
            $serviceQuery->update('db_services', $this->_filter('db_services', array('status' => 0, 'available_online' => 0)));
        }

    }

    private function _pharmacy_category_ids($store_id) {
        if (!$this->db->table_exists('db_category')) return array();
        $allowed = array(
            'Prescription Drugs', 'Over-the-Counter', 'Medical Supplies',
            'Vitamins & Supplements', 'Dermatology', 'Skincare',
            'Analgesics & Pain Relief', 'Antibiotics', 'Cold, Flu & Allergy',
            'Digestive Health', 'First Aid & Wound Care', 'Mother & Baby',
            'Personal Care & Hygiene', 'Medical Devices',
        );
        $rows = $this->db->select('id')->where('store_id', $store_id)->where_in('category_name', $allowed)->get('db_category')->result();
        return array_map(function ($row) { return (int)$row->id; }, $rows);
    }

    /** Retire transactions that reference products retired by the industry switch. */
    private function _retire_inactive_item_transactions($store_id) {
        if (!$this->db->table_exists('db_items')) return;
        $rows = $this->db->select('id')->where('store_id', $store_id)->where('status !=', 1)->get('db_items')->result();
        $itemIds = array_map(function ($row) { return (int)$row->id; }, $rows);
        if (!$itemIds) return;

        $links = array(
            array('db_salesitems', 'sales_id', 'db_sales'),
            array('db_purchaseitems', 'purchase_id', 'db_purchase'),
            array('db_quotationitems', 'quotation_id', 'db_quotation'),
            array('db_holditems', 'hold_id', 'db_hold'),
            array('db_stockadjustmentitems', 'adjustment_id', 'db_stockadjustment'),
            array('db_stocktransferitems', 'stocktransfer_id', 'db_stocktransfer'),
            array('db_online_order_items', 'order_id', 'db_online_orders'),
        );
        foreach ($links as $link) {
            list($lineTable, $parentColumn, $parentTable) = $link;
            if (!$this->db->table_exists($lineTable) || !$this->db->table_exists($parentTable)) continue;
            $query = $this->db->select($parentColumn)->distinct()->where_in('item_id', $itemIds)->get($lineTable);
            $parentIds = array();
            foreach ($query->result() as $row) {
                $parentId = (int)($row->{$parentColumn} ?? 0);
                if ($parentId > 0) $parentIds[] = $parentId;
            }
            if (!$parentIds) continue;
            if ($this->db->field_exists('status', $lineTable)) {
                $this->db->where_in('item_id', $itemIds)->update($lineTable, $this->_filter($lineTable, array('status' => 0)));
            }
            if ($this->db->field_exists('status', $parentTable)) {
                $parentQuery = $this->db->where_in('id', $parentIds);
                if ($this->db->field_exists('store_id', $parentTable)) {
                    $parentQuery->where('store_id', $store_id);
                }
                $parentData = array('status' => 0);
                if ($parentTable === 'db_sales' && $this->db->field_exists('sales_status', $parentTable)) {
                    $parentData['sales_status'] = 'Cancelled';
                } elseif ($parentTable === 'db_purchase' && $this->db->field_exists('purchase_status', $parentTable)) {
                    $parentData['purchase_status'] = 'Cancelled';
                } elseif ($parentTable === 'db_online_orders' && $this->db->field_exists('order_status', $parentTable)) {
                    $parentData['order_status'] = 'cancelled';
                } elseif ($parentTable === 'db_quotation' && $this->db->field_exists('quotation_status', $parentTable)) {
                    $parentData['quotation_status'] = 'Cancelled';
                }
                $parentQuery->update($parentTable, $this->_filter($parentTable, $parentData));
            }
        }

        if ($this->db->table_exists('db_warehouseitems') && $this->db->field_exists('available_qty', 'db_warehouseitems')) {
            $this->db->where('store_id', $store_id)->where_in('item_id', $itemIds)
                ->update('db_warehouseitems', array('available_qty' => 0));
        }
    }

    /** Hide active online orders whose lines no longer belong to the pharmacy catalogue. */
    private function _retire_pharmacy_non_catalogue_orders($store_id, $categoryIds) {
        if (!$categoryIds || !$this->db->table_exists('db_online_orders') || !$this->db->table_exists('db_online_order_items')) return;

        $categorySql = implode(',', array_map('intval', $categoryIds));
        $serviceNames = array('Medication Review', 'Blood Pressure Check', 'Prescription Refill Support');
        $serviceSql = implode(',', array_map(function ($name) { return $this->db->escape($name); }, $serviceNames));
        $serviceJoin = $this->db->table_exists('db_services')
            ? "LEFT JOIN db_services svc ON svc.id = oi.item_id AND svc.store_id = o.store_id"
            : "LEFT JOIN (SELECT 0 AS id, 0 AS store_id, '' AS service_name, 0 AS status) svc ON 1=1";

        $sql = "SELECT DISTINCT o.id
            FROM db_online_orders o
            INNER JOIN db_online_order_items oi ON oi.order_id = o.id
            LEFT JOIN db_items i ON i.id = oi.item_id AND i.store_id = o.store_id
            {$serviceJoin}
            WHERE o.store_id = ? AND o.status = 1
              AND (
                (oi.item_type = 'product' AND (i.id IS NULL OR i.status <> 1 OR i.category_id IS NULL OR i.category_id NOT IN ({$categorySql})))
                OR (oi.item_type = 'service' AND (svc.id IS NULL OR svc.status <> 1 OR svc.service_name NOT IN ({$serviceSql})))
                OR oi.item_type NOT IN ('product', 'service')
              )";
        $badOrders = $this->db->query($sql, array((int)$store_id))->result();
        $badIds = array_map(function ($row) { return (int)$row->id; }, $badOrders);
        if ($badIds) {
            $this->db->where('store_id', $store_id)->where_in('id', $badIds)
                ->update('db_online_orders', $this->_filter('db_online_orders', array('status' => 0)));
        }
    }

    private function _default_industry($input, $key) {
        $input = strtolower(trim((string)$input));
        if ($input === 'building' || $key === 'building_materials') return 'building_materials';
        if ($input === 'garden' || $key === 'garden_centre') return 'garden_centre_landscaping';
        if ($input === 'frozen' || $key === 'frozen') return 'frozen_foods_retailer';
        if ($input === 'physio' || $key === 'physiotherapy') return 'physiotherapy_rehabilitation';
        return $input ?: $key;
    }

    private function _filter($table, $data) {
        if (!$this->db->table_exists($table)) return array();
        return array_intersect_key($data, array_flip($this->db->list_fields($table)));
    }

    private function _row($table, $where) {
        if (!$this->db->table_exists($table)) return null;
        foreach ($where as $column => $value) $this->db->where($column, $value);
        return $this->db->get($table)->row();
    }

    private function _save($table, $where, $data) {
        if (!$this->db->table_exists($table)) return 0;
        $row = $this->_row($table, $where); $data = $this->_filter($table, $data);
        if ($row) { if (!empty($data)) $this->db->where('id', (int)$row->id)->update($table, $data); return (int)$row->id; }
        if (empty($data)) return 0;
        $this->db->insert($table, $data); return (int)$this->db->insert_id();
    }

    private function _next_count($table, $store_id) {
        if (!$this->db->field_exists('count_id', $table)) return 1;
        $row = $this->db->select('COALESCE(MAX(count_id),0)+1 AS next_id')->where('store_id', $store_id)->get($table)->row();
        return $row ? (int)$row->next_id : 1;
    }

    private function _seed_units($store_id, $units) {
        $ids = array();
        foreach ($units as $name => $shortcode) {
            $id = $this->_save('db_units', array('store_id' => $store_id, 'unit_name' => $name), array('store_id' => $store_id, 'unit_name' => $name, 'shortcode' => $shortcode, 'description' => 'Demo '.$name.' unit', 'status' => 1));
            if ($id) $ids[$name] = $id;
        }
        return $ids;
    }

    private function _seed_categories($store_id, $categories) {
        $ids = array(); $count = $this->_next_count('db_category', $store_id);
        foreach ($categories as $name) {
            $old = $this->_row('db_category', array('store_id' => $store_id, 'category_name' => $name));
            if ($old) {
                $this->db->where('id', (int)$old->id)->update('db_category', $this->_filter('db_category', array('description' => 'Demo '.$name.' catalogue', 'status' => 1)));
                $ids[$name] = (int)$old->id;
                continue;
            }
            $id = $this->_save('db_category', array('store_id' => $store_id, 'category_name' => $name), array('store_id' => $store_id, 'count_id' => $count, 'category_code' => 'CT/'.str_pad($store_id, 2, '0', STR_PAD_LEFT).'/'.str_pad($count, 4, '0', STR_PAD_LEFT), 'category_name' => $name, 'description' => 'Demo '.$name.' catalogue', 'status' => 1));
            if ($id) { $ids[$name] = $id; $count++; }
        }
        return $ids;
    }

    private function _seed_suppliers($store_id, $names, &$result) {
        $ids = array(); $count = $this->_next_count('db_suppliers', $store_id);
        foreach ($names as $name) {
            $id = $this->_save('db_suppliers', array('store_id' => $store_id, 'supplier_name' => $name), array('store_id' => $store_id, 'count_id' => $count, 'supplier_code' => 'SUP/'.str_pad($store_id, 2, '0', STR_PAD_LEFT).'/'.str_pad($count, 4, '0', STR_PAD_LEFT), 'supplier_name' => $name, 'phone' => '080'.str_pad($count, 8, '0', STR_PAD_LEFT), 'email' => strtolower(str_replace(' ', '.', $name)).'@example.com', 'city' => 'Lagos', 'created_date' => date('Y-m-d'), 'created_time' => date('h:i:s a'), 'created_by' => 'demo', 'status' => 1));
            if ($id) { $ids[] = $id; $result['suppliers']++; $count++; }
        }
        return $ids;
    }

    private function _seed_products($store_id, $products, $units, $categories, &$result, $industry = '') {
        $ids = array(); $count = $this->_next_count('db_items', $store_id); $default_unit = reset($units);
        $restaurantMenu = $industry === 'restaurant' ? require APPPATH . 'config/restaurant_demo_menu.php' : array();
        foreach ($products as $product) {
            list($name, $category, $sales, $cost, $stock) = $product;
            $oldName = $name;
            $menuEntry = $restaurantMenu[$oldName] ?? null;
            if ($menuEntry) $name = $menuEntry['name'];
            $fulfilmentRoute = isset($product[5]) && in_array($product[5], array('counter', 'kitchen', 'bar'), true)
                ? $product[5] : 'counter';
            $unit_id = isset($units['Piece']) ? $units['Piece'] : $default_unit;
            $packLabel = null;
            if (in_array($category, array('Prescription Drugs', 'Over-the-Counter', 'Medical Supplies', 'Vitamins & Supplements', 'Dermatology', 'Skincare'), true)) {
                if (preg_match('/capsule|amoxicillin/i', $name)) { $packLabel = 'Pack of 21 capsules'; if (isset($units['Pack'])) $unit_id = $units['Pack']; }
                elseif (preg_match('/tablet|paracetamol/i', $name)) { $packLabel = 'Pack of 12 tablets'; if (isset($units['Pack'])) $unit_id = $units['Pack']; }
                elseif (preg_match('/cream|cleanser|gel|sunscreen/i', $name)) { $packLabel = 'Tube / container'; if (isset($units['Bottle'])) $unit_id = $units['Bottle']; }
                elseif (preg_match('/monitor/i', $name)) $packLabel = '1 monitor';
                elseif (preg_match('/thermometer/i', $name)) $packLabel = '1 device';
                elseif (isset($units['Bottle'])) { $packLabel = 'Bottle'; $unit_id = $units['Bottle']; }
            }
            $data = array('store_id' => $store_id, 'count_id' => $count, 'item_code' => 'IT/'.str_pad($store_id, 2, '0', STR_PAD_LEFT).'/'.str_pad($count, 4, '0', STR_PAD_LEFT), 'item_name' => $name, 'category_id' => isset($categories[$category]) ? $categories[$category] : 0, 'unit_id' => $unit_id, 'consumable_unit' => $packLabel, 'sku' => 'ZONAT-'.$store_id.'-'.$count, 'alert_qty' => 5, 'purchase_price' => $cost, 'price' => $cost, 'sales_price' => $sales, 'stock' => $stock, 'description' => 'Demo '.$category.' product from Zonat.', 'created_date' => date('Y-m-d'), 'created_time' => date('h:i:s a'), 'created_by' => 'demo', 'status' => 1, 'publish_online' => 1, 'is_featured' => ($count % 3 === 0) ? 1 : 0, 'is_new_arrival' => 1, 'item_group' => 'Single', 'service_bit' => 0, 'product_type' => 'physical', 'discount_type' => 'Percentage', 'discount' => 0);
            if ($this->db->field_exists('fulfilment_route', 'db_items')) $data['fulfilment_route'] = $fulfilmentRoute;
            $where = array('store_id' => $store_id, 'item_name' => $name);
            if ($menuEntry) {
                $data['description'] = $menuEntry['description'];
                $existing = $this->_row('db_items', $where);
                if (!$existing) $existing = $this->_row('db_items', array('store_id' => $store_id, 'item_name' => $oldName, 'created_by' => 'demo'));
                if ($existing) $where = array('store_id' => $store_id, 'id' => (int)$existing->id);
                // Assets are scoped to the fictional store. Preserve uploaded photography.
                $image = 'uploads/storefront/' . (int)$store_id . '/menu-demo/' . $menuEntry['slug'] . '.webp';
                if (is_file(FCPATH . $image) && empty($existing->item_image)) $data['item_image'] = $image;
            }
            $id = $this->_save('db_items', $where, $data);
            if ($id) { $ids[] = (object)array('id' => $id, 'name' => $name, 'cost' => $cost, 'sales' => $sales, 'stock' => $stock); $result['products']++; $count++; }
        }
        $warehouse = $this->_row('db_warehouse', array('store_id' => $store_id));
        if ($warehouse && $this->db->table_exists('db_warehouseitems')) foreach ($ids as $item) $this->_save('db_warehouseitems', array('store_id' => $store_id, 'warehouse_id' => $warehouse->id, 'item_id' => $item->id), array('store_id' => $store_id, 'warehouse_id' => $warehouse->id, 'item_id' => $item->id, 'available_qty' => $item->stock));
        return $ids;
    }

    /**
     * Seed only fictional, public pharmacy fulfilment locations. The customer
     * sees the first two as stock-holding locations; the collection hub has no
     * stock and points to Ikeja through stock_source_id. All stock remains in
     * db_warehouseitems so the existing reservation/FEFO services stay the
     * source of truth.
     */
    private function _seed_pharmacy_locations($store_id, $items, &$result) {
        if (!$this->db->table_exists('db_warehouse') || !$this->db->table_exists('db_warehouseitems')) return;
        $locations = array(
            array('name' => 'Ikeja GRA Pharmacy', 'type' => 'branch', 'address' => '14 Isaac John Street, Ikeja GRA', 'city' => 'Ikeja', 'state' => 'Lagos', 'hours' => 'Mon-Sat · 8:00am-8:00pm', 'coverage' => 'Ikeja, Alausa and Maryland', 'fee' => 1800),
            array('name' => 'Wuse Partner Fulfilment Hub', 'type' => 'partner', 'address' => '8 Aminu Kano Crescent, Wuse 2', 'city' => 'Abuja', 'state' => 'FCT', 'hours' => 'Mon-Fri · 9:00am-6:00pm', 'coverage' => 'Wuse, Maitama and Central Area', 'fee' => 2200),
            array('name' => 'Surulere Collection Hub', 'type' => 'pickup_hub', 'address' => '5 Adeniran Ogunsanya Street, Surulere', 'city' => 'Surulere', 'state' => 'Lagos', 'hours' => 'Mon-Sat · 9:00am-6:00pm', 'coverage' => 'Pickup only · prepared by Ikeja Pharmacy', 'fee' => 0),
        );
        $ids = array();
        foreach ($locations as $location) {
            $id = $this->_save('db_warehouse', array('store_id' => $store_id, 'warehouse_name' => $location['name']), array(
                'store_id' => $store_id, 'warehouse_type' => 'Custom', 'warehouse_name' => $location['name'],
                'address' => $location['address'], 'city' => $location['city'], 'state' => $location['state'], 'country' => 'Nigeria',
                'branch_type' => $location['type'], 'is_distribution_center' => $location['type'] === 'partner' ? 1 : 0,
                'mobile' => '08000002026', 'email' => 'demo.'.$location['type'].'.pharmacy'.$store_id.'@example.com', 'status' => 1,
                'business_hours' => $location['hours'], 'delivery_coverage' => $location['coverage'], 'delivery_fee' => $location['fee'],
                'pickup_enabled' => 1, 'public_enabled' => 1, 'stock_source_id' => null,
            ));
            if ($id) { $ids[$location['type']] = $id; $result['locations']++; }
        }
        if (empty($ids['branch']) || empty($ids['partner']) || empty($ids['pickup_hub'])) return;
        if ($this->db->field_exists('stock_source_id', 'db_warehouse')) {
            $this->db->where('id', (int)$ids['pickup_hub'])->where('store_id', $store_id)->update('db_warehouse', array('stock_source_id' => (int)$ids['branch']));
        }
        foreach ($items as $item) {
            // Rebuild only the seeded demo item's location ledger. This removes
            // the generic first-warehouse attachment created by _seed_products
            // and prevents a hidden System warehouse from appearing as a third
            // pharmacy source, without touching unrelated stock.
            $this->db->where('store_id', $store_id)->where('item_id', (int)$item->id)->delete('db_warehouseitems');
            $branchQty = (int)ceil(((int)$item->stock) * 0.6);
            $partnerQty = max(0, (int)$item->stock - $branchQty);
            $this->_save('db_warehouseitems', array('store_id' => $store_id, 'warehouse_id' => (int)$ids['branch'], 'item_id' => (int)$item->id), array('store_id' => $store_id, 'warehouse_id' => (int)$ids['branch'], 'item_id' => (int)$item->id, 'available_qty' => $branchQty));
            $this->_save('db_warehouseitems', array('store_id' => $store_id, 'warehouse_id' => (int)$ids['partner'], 'item_id' => (int)$item->id), array('store_id' => $store_id, 'warehouse_id' => (int)$ids['partner'], 'item_id' => (int)$item->id, 'available_qty' => $partnerQty));
            $this->db->where('id', (int)$item->id)->where('store_id', $store_id)->update('db_items', array('stock' => $branchQty + $partnerQty));
        }
    }

    private function _seed_services($store_id, $names, $categories, &$result) {
        if (!$this->db->table_exists('db_services')) return;
        $category_id = reset($categories);
        foreach ($names as $index => $name) {
            $id = $this->_save('db_services', array('store_id' => $store_id, 'service_name' => $name), array('store_id' => $store_id, 'service_name' => $name, 'category_id' => $category_id, 'price' => 5000 + ($index * 2500), 'service_duration' => ($index + 1).' hour', 'description' => 'Zonat '.$name.' service.', 'available_online' => 1, 'requires_appointment' => 1, 'location_type' => 'in-store', 'sort_order' => $index, 'status' => 1));
            if ($id) $result['services']++;
        }
    }

    private function _seed_customers($store_id, $industry, &$result) {
        if (!$this->db->table_exists('db_customers')) return;
        foreach (array('Amina Bello', 'Chinedu Okafor', 'Fatima Yusuf', 'David Mensah', 'Zonat Trade Customer', 'Grace Eze') as $index => $name) {
            $email = 'demo.'.$industry.'.customer'.($index + 1).'@example.com';
            $id = $this->_save('db_customers', array('store_id' => $store_id, 'email' => $email), array('store_id' => $store_id, 'count_id' => $this->_next_count('db_customers', $store_id), 'customer_code' => 'CUS/'.str_pad($store_id, 2, '0', STR_PAD_LEFT).'/'.str_pad($index + 1, 4, '0', STR_PAD_LEFT), 'customer_name' => $name, 'customer_type' => 'Retail', 'mobile' => '080'.str_pad($index + 1, 8, '0', STR_PAD_LEFT), 'email' => $email, 'city' => 'Lagos', 'address' => 'Demo customer address, Lagos', 'created_date' => date('Y-m-d'), 'created_time' => date('h:i:s a'), 'created_by' => 'demo', 'status' => 1));
            if ($id) $result['customers']++;
        }
    }

    /** Seed fictional prescription records into the real pharmacy queue. */
    private function _seed_pharmacy_prescriptions($store_id, $items, &$result) {
        // Medical_notes_model owns the legacy table bootstrap for older demo
        // installs, so the pharmacy seed can work immediately after an
        // industry switch as well as after the migration has run.
        $this->load->model('medical_notes_model', 'medical_notes_seed');
        if (!$this->db->table_exists('db_medical_notes') || !$this->db->table_exists('db_medical_note_items')) return;
        $itemIds = array();
        foreach ($items as $item) $itemIds[(string)$item->name] = (int)$item->id;
        $customers = array();
        foreach (array(1, 2, 3, 4) as $number) {
            $customer = $this->_row('db_customers', array('store_id' => $store_id, 'email' => 'demo.pharmacy.customer'.$number.'@example.com'));
            if ($customer) $customers[$number] = (int)$customer->id;
        }
        $today = date('Y-m-d');
        $records = array(
            array(1, 'RX-DEMO-001', 'Dr. Ada Nwosu', 'Demo respiratory review', 'Demo allergy flag: none recorded', 'Take one capsule every 8 hours after food for 7 days.', 'Continue the course as reviewed by the pharmacist.', 7, 1, 'active', array(array('name' => 'Amoxicillin 500mg', 'qty' => 21, 'dosage' => '1 capsule every 8 hours', 'duration' => '7 days', 'instructions' => 'After food'))),
            array(2, 'RX-DEMO-002', 'Dr. Tunde Balogun', 'Demo blood-pressure follow-up', '', 'Use as directed on the approved prescription and review at the next visit.', 'Demo counselling note: confirm the patient understands the label.', 14, 2, 'active', array(array('name' => 'Omeprazole 20mg', 'qty' => 14, 'dosage' => '1 capsule each morning', 'duration' => '14 days', 'instructions' => 'Before breakfast'))),
            array(3, 'RX-DEMO-003', 'Dr. Mariam Yusuf', 'Demo seasonal allergy review', 'Demo allergy flag: none recorded', 'Take one tablet once daily when required.', 'Demo refill record for the pharmacy follow-up workflow.', 21, 1, 'active', array(array('name' => 'Cetirizine Tablets', 'qty' => 10, 'dosage' => '1 tablet once daily', 'duration' => '10 days', 'instructions' => 'Prefer evening use if drowsy'))),
            array(4, 'RX-DEMO-004', 'Dr. Chuka Eze', 'Demo completed dispensing record', 'Demo allergy flag: penicillin noted', 'Use only as reviewed by the pharmacist.', 'Demo historical record; no live clinical decision is implied.', null, 0, 'closed', array(array('name' => 'Paracetamol 500mg', 'qty' => 12, 'dosage' => 'As directed on label', 'duration' => '3 days', 'instructions' => 'Do not exceed the label dose'))),
        );
        foreach ($records as $record) {
            $customerId = $customers[$record[0]] ?? 0;
            if (!$customerId) continue;
            $noteId = $this->_save('db_medical_notes', array('store_id' => $store_id, 'prescription_ref' => $record[1]), array(
                'store_id' => $store_id, 'customer_id' => $customerId, 'note_date' => $today,
                'prescribing_doctor' => $record[2], 'doctor_contact' => 'demo@example.com', 'diagnosis' => $record[3],
                'prescription_ref' => $record[1], 'allergies_flagged' => $record[4], 'dosage_instructions' => $record[5],
                'counselling_notes' => $record[6], 'next_refill_date' => $record[7] ? date('Y-m-d', strtotime('+'.$record[7].' days')) : null,
                'refills_remaining' => $record[8], 'staff_name' => 'Demo Pharmacist', 'status' => $record[9],
                'review_status' => 'approved', 'review_requested_at' => $today.' '.date('H:i:s'),
                'reviewed_at' => $today.' '.date('H:i:s'), 'reviewed_by' => null,
                'created_date' => $today, 'created_time' => date('H:i:s'), 'created_by' => 'demo',
            ));
            if (!$noteId) continue;
            $this->db->where('medical_note_id', $noteId)->delete('db_medical_note_items');
            foreach ($record[10] as $line) {
                $itemId = $itemIds[$line['name']] ?? 0;
                if (!$itemId) continue;
                $this->db->insert('db_medical_note_items', array('medical_note_id' => $noteId, 'item_id' => $itemId, 'item_name' => $line['name'], 'qty' => $line['qty'], 'dosage' => $line['dosage'], 'duration' => $line['duration'], 'instructions' => $line['instructions']));
            }
            $result['prescriptions']++;
        }
    }

    private function _seed_leads($store_id, $industry, $store_name, &$result) {
        if (!$this->db->table_exists('db_leads')) return;
        $rows = array(array('Zonat '.$industry.' wholesale enquiry', 'wholesale'), array('Zonat '.$industry.' service enquiry', 'website'), array('Local business partnership enquiry', 'referral'));
        foreach ($rows as $index => $lead) {
            $email = 'demo.'.$industry.'.lead'.($index + 1).'@example.com';
            $id = $this->_save('db_leads', array('store_id' => $store_id, 'email' => $email), array('store_id' => $store_id, 'name' => $lead[0], 'phone' => '081'.str_pad($index + 1, 8, '0', STR_PAD_LEFT), 'email' => $email, 'source' => $lead[1], 'status' => 'new', 'interest' => $store_name, 'notes' => 'Seeded demo lead for '.$industry.'.', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')));
            if ($id) $result['leads']++;
        }
    }

    private function _seed_accounts($store_id, $store_name, &$result) {
        if (!$this->db->table_exists('ac_accounts')) return;
        foreach (array('Zonat Cash Till', 'Zonat Bank Account', 'Zonat Sales Revenue', 'Zonat Purchase Expense', 'Zonat Operating Expenses') as $name) {
            $id = $this->_save('ac_accounts', array('store_id' => $store_id, 'account_name' => $name), array('store_id' => $store_id, 'count_id' => $this->_next_count('ac_accounts', $store_id), 'account_name' => $name, 'account_code' => 'AC/'.str_pad($store_id, 2, '0', STR_PAD_LEFT).'/'.substr(md5($name), 0, 6), 'sort_code' => '100', 'parent_id' => 0, 'balance' => 0, 'note' => 'Seeded for '.$store_name.'.', 'created_by' => 'demo', 'created_date' => date('Y-m-d'), 'created_time' => date('h:i:s a'), 'status' => 1, 'delete_bit' => 0));
            if ($id) $result['accounts']++;
        }
    }

    private function _seed_purchase($store_id, $suppliers, $items, &$result, $industry) {
        if (!$this->db->table_exists('db_purchase') || !$this->db->table_exists('db_purchaseitems') || empty($suppliers) || empty($items)) return;
        // Keep one active purchase invoice per industry dataset. The previous
        // generic note made an industry switch retire the old invoice and then
        // incorrectly prevented the new industry from receiving its own
        // purchase history.
        $reference = 'ZONAT-DEMO-'.$store_id.'-'.$industry;
        if ($this->db->where('store_id', $store_id)->where('reference_no', $reference)->where('status', 1)->get('db_purchase')->row()) return;
        $total = 0; foreach ($items as $item) $total += ((float)$item->cost * 10);
        $count = $this->_next_count('db_purchase', $store_id);
        $purchase = $this->_filter('db_purchase', array('store_id' => $store_id, 'count_id' => $count, 'purchase_code' => 'PUR/'.str_pad($store_id, 2, '0', STR_PAD_LEFT).'/'.str_pad($count, 4, '0', STR_PAD_LEFT), 'reference_no' => $reference, 'purchase_date' => date('Y-m-d'), 'purchase_status' => 'Received', 'supplier_id' => $suppliers[0], 'subtotal' => $total, 'grand_total' => $total, 'payment_status' => 'Paid', 'paid_amount' => $total, 'purchase_note' => 'Zonat '.$industry.' demo purchase', 'created_date' => date('Y-m-d'), 'created_time' => date('h:i:s a'), 'created_by' => 'demo', 'status' => 1));
        $this->db->insert('db_purchase', $purchase); $purchase_id = (int)$this->db->insert_id();
        if (!$purchase_id) return;
        foreach ($items as $item) $this->db->insert('db_purchaseitems', $this->_filter('db_purchaseitems', array('store_id' => $store_id, 'purchase_id' => $purchase_id, 'purchase_status' => 'Received', 'item_id' => $item->id, 'purchase_qty' => 10, 'received_qty' => 10, 'price_per_unit' => $item->cost, 'unit_total_cost' => $item->cost, 'total_cost' => $item->cost * 10, 'unit_sales_price' => $item->sales, 'status' => 1)));
        $result['purchase_invoices']++;
    }

    private function _seed_storefront($store_id, $data, $industry_key = '') {
        if (!$this->db->table_exists('db_storefront_settings')) return;
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $data['store_name']), '-'));
        $this->load->model('storefront_model');
        $storefrontData = array('store_slug' => $slug, 'store_description' => $data['store_description'] ?? ($data['store_name'].' online catalogue, services and helpful updates.'), 'store_headline' => $data['store_name'], 'store_subheadline' => $data['store_subheadline'] ?? 'Products, services and trusted support from Zonat.', 'primary_color' => $data['theme'][0], 'secondary_color' => $data['theme'][1], 'store_status' => 'active', 'show_search' => 1, 'show_categories' => 1, 'allow_services' => 1, 'allow_pay_on_delivery' => 1, 'partner_marketplace_enabled' => $industry_key === 'pharmacy' ? 1 : 0, 'featured_products_limit' => 8, 'meta_title' => $data['store_name'], 'meta_description' => $data['store_description'] ?? ('Shop '.$data['store_name'].' products and services online.'));
        if ($industry_key === 'pharmacy') $storefrontData['shipping_methods_json'] = json_encode(array(
            array('name' => 'Delivery', 'fee' => 1800, 'description' => 'Delivery from the selected eligible location', 'enabled' => 1, 'quote' => 0),
            array('name' => 'Pickup', 'fee' => 0, 'description' => 'Collect from the selected pharmacy or hub', 'enabled' => 1, 'quote' => 0)
        ));
        $this->storefront_model->saveSettings($store_id, $this->_filter('db_storefront_settings', $storefrontData));
    }

    private function _seed_storefront_posts($store_id, $titles, $industry, &$result) {
        $this->load->model('storefront_content_model');
        if (!$this->db->table_exists('db_storefront_posts')) return;
        foreach ($titles as $entry) {
            $entry = is_array($entry) ? $entry : array('title' => $entry, 'kind' => 'insight');
            $title = trim((string)($entry['title'] ?? ''));
            if ($title === '') continue;
            $kind = in_array(($entry['kind'] ?? 'insight'), array('insight', 'project', 'application'), true) ? $entry['kind'] : 'insight';
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $industry.'-'.$title), '-'));
            $row = array('store_id' => $store_id, 'slug' => $slug, 'title' => $title, 'excerpt' => $entry['excerpt'] ?? 'A useful note from Zonat.', 'body' => $entry['body'] ?? ($title.' — practical guidance and useful ideas from Zonat '.ucwords(str_replace('_', ' ', $industry)).'.'), 'status' => 'published', 'published_at' => gmdate('Y-m-d H:i:s'), 'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'));
            if ($this->db->field_exists('kind', 'db_storefront_posts')) $row['kind'] = $kind;
            if ($this->db->field_exists('meta_json', 'db_storefront_posts')) $row['meta_json'] = json_encode($entry['meta'] ?? array('industry' => $industry));
            $id = $this->_save('db_storefront_posts', array('store_id' => $store_id, 'slug' => $slug), $row);
            if ($id) $result['posts']++;
        }
    }
}
