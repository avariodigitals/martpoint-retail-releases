<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Food Express merchant copy catalog.
 *
 * Same contract as mp_botanical_fields() / mp_building_fields(): a flat map of
 *   key => ['group','label','default','type']
 * where type is 'text', 'textarea' or 'image'. Values are stored per theme key
 * under db_storefront_settings.theme_content_json and edited on
 * Online Store → Storefront Content; images accept a URL or a file upload.
 *
 * Everything a field supplies has a working default, so an untouched install
 * renders the designed storefront; every default is presentation copy only —
 * product, category, banner and menu DATA always comes from the backend.
 */

if (!function_exists('mp_food_fields')) {
    function mp_food_fields() {
        $groups = [
            'Brand & words' => [
                'brand_line'      => 'Good food. Great mood.',
                'brand_tagline'   => 'From first craving to first bite.',
                'search_placeholder' => 'What are you craving?',
                'explore_cta'     => 'Browse the menu',
                'overline_text'   => 'Made fresh, served with a smile',
            ],
            'Homepage hero' => [
                'hero_image'      => ['Hero image', '', 'image'],
                'hero_image_alt'  => ['Hero image alt text', 'A favourite meal from the kitchen', 'text'],
            ],
            'Homepage sections' => [
                'categories_overline' => 'Craving something specific?',
                'categories_title'    => 'Find your favourites',
                'trust_1_title'  => 'Fast delivery',
                'trust_1_text'   => 'Hot from the kitchen to your door',
                'trust_2_title'  => 'Order your way',
                'trust_2_text'   => 'Delivery, pickup or QR table order',
                'trust_3_title'  => 'Secure payments',
                'trust_3_text'   => 'Pay online or on delivery',
            ],
            'Footer' => [
                'footer_line' => 'Fresh, tasty and always on time.',
            ],
        ];
        $fields = [];
        foreach ($groups as $group => $entries) {
            foreach ($entries as $key => $entry) {
                $isArray = is_array($entry);
                $value = $isArray ? $entry[1] : $entry;
                $fields[$key] = [
                    'group'   => $group,
                    'label'   => $isArray ? $entry[0] : ucfirst(str_replace('_', ' ', $key)),
                    'default' => $value,
                    'type'    => $isArray ? $entry[2] : (mb_strlen($value) > 85 ? 'textarea' : 'text'),
                ];
            }
        }
        return $fields;
    }
}

if (!function_exists('mp_food_values')) {
    /** Saved values (per theme key under theme_content_json) merged onto defaults. */
    function mp_food_values($json) {
        $data = json_decode((string)$json, true);
        $saved = is_array($data['food_express'] ?? null) ? $data['food_express'] : [];
        $values = [];
        foreach (mp_food_fields() as $key => $field) {
            $values[$key] = isset($saved[$key]) && is_string($saved[$key]) && trim($saved[$key]) !== ''
                ? $saved[$key]
                : $field['default'];
        }
        return $values;
    }
}

if (!function_exists('mp_food_product_kind')) {
    /** Classify catalogue items so customer-facing copy follows the menu. */
    function mp_food_product_kind($itemName = '', $categoryName = '', $description = '') {
        $category = strtolower(trim((string)$categoryName));
        $category = preg_replace('/[^a-z0-9]+/i', ' ', $category);
        $menuText = strtolower(trim(implode(' ', array_filter([
            (string)$itemName,
            $category,
            (string)$description,
        ]))));
        $menuText = preg_replace('/[^a-z0-9]+/i', ' ', $menuText);

        if (preg_match('/\b(drinks?|beverages?|juices?|smoothies?|waters?|sodas?|coffees?|teas?|malted?|malts?)\b/i', $category . ' ' . $menuText)) {
            return 'drink';
        }
        if (preg_match('/\b(desserts?|cakes?|ice creams?|milkshakes?|shakes?|pastries?|breads?|fruits?|yogurts?)\b/i', $category . ' ' . $menuText)) {
            return 'sweet';
        }
        return 'dish';
    }
}

if (!function_exists('mp_food_product_fulfilment_copy')) {
    /** Customer-facing availability and timing copy for the classified item. */
    function mp_food_product_fulfilment_copy($kind = 'dish', $outOfStock = false) {
        if ($outOfStock) {
            return ['availability' => 'Currently unavailable', 'timing' => 'Please choose another item'];
        }
        if ($kind === 'drink') {
            return ['availability' => 'Available to order', 'timing' => 'Ready to serve'];
        }
        if ($kind === 'sweet') {
            return ['availability' => 'Available to order', 'timing' => 'Ready to serve'];
        }
        return ['availability' => 'In stock — made to order', 'timing' => 'Ready in ~20–30 minutes'];
    }
}

if (!function_exists('mp_food_spice_allowed')) {
    /**
     * Menu-level rule for whether a dish can expose heat customisation.
     * Category is authoritative: drinks, desserts and bakery items should
     * never inherit spice controls simply because they are physical products.
     */
    function mp_food_spice_allowed($itemName = '', $categoryName = '', $description = '') {
        return mp_food_product_kind($itemName, $categoryName, $description) === 'dish';
    }
}
