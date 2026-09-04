<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MartPoint Retail — Marketing menu helper.
 *
 * Single source of truth for the Marketing landing-page item list.
 * Both the mobile (Mobile::marketing) and desktop (Marketing::index)
 * controllers call marketing_menu_items() so the list, permission
 * gating and ordering are defined in exactly one place.
 *
 * Each item carries url_mobile and url_desktop so each shell can branch
 * by device without duplicating logic.
 */
if (!function_exists('marketing_menu_items')) {
    function marketing_menu_items() {
        $CI =& get_instance();

        $items = [
            [
                'title' => 'Create Customer Coupon',
                'desc'  => 'Generate a customer coupon',
                'icon'  => 'fa-plus-square',
                'perm'  => 'customerCouponAdd',
                'color' => 'primary',
                'url_mobile'  => 'mobile/customer_coupon/generate',
                'url_desktop' => 'customer_coupon/generate',
            ],
            [
                'title' => 'Customer Coupons List',
                'desc'  => 'All customer coupons',
                'icon'  => 'fa-list',
                'perm'  => 'customerCouponView',
                'color' => 'blue',
                'url_mobile'  => 'mobile/customer_coupon',
                'url_desktop' => 'customer_coupon',
            ],
            [
                'title' => 'Create Coupon',
                'desc'  => 'Create a discount coupon',
                'icon'  => 'fa-plus-square',
                'perm'  => 'discountCouponAdd',
                'color' => 'primary',
                'url_mobile'  => 'mobile/discount_coupon/add',
                'url_desktop' => 'discount_coupon/add',
            ],
            [
                'title' => 'Coupons Master',
                'desc'  => 'Manage all coupons',
                'icon'  => 'fa-list',
                'perm'  => 'discountCouponView',
                'color' => 'blue',
                'url_mobile'  => 'mobile/discount_coupon/view',
                'url_desktop' => 'discount_coupon/view',
            ],
            [
                'title' => 'Loyalty Dashboard',
                'desc'  => 'Loyalty overview',
                'icon'  => 'fa-dashboard',
                'perm'  => 'loyalty_view',
                'feature' => 'loyalty',
                'color' => 'purple',
                'url_mobile'  => 'mobile/loyalty',
                'url_desktop' => 'loyalty',
            ],
            [
                'title' => 'Loyalty Settings',
                'desc'  => 'Configure loyalty rules',
                'icon'  => 'fa-cog',
                'perm'  => 'loyalty_edit',
                'feature' => 'loyalty',
                'color' => 'purple',
                'url_mobile'  => 'mobile/loyalty/settings',
                'url_desktop' => 'loyalty/settings',
            ],
            [
                'title' => 'Customer Tiers',
                'desc'  => 'Loyalty customer tiers',
                'icon'  => 'fa-sitemap',
                'perm'  => 'loyalty_view',
                'feature' => 'loyalty',
                'color' => 'purple',
                'url_mobile'  => 'mobile/loyalty/tiers',
                'url_desktop' => 'loyalty/tiers',
            ],
            [
                'title' => 'Points History',
                'desc'  => 'Loyalty points history',
                'icon'  => 'fa-history',
                'perm'  => 'loyalty_view',
                'feature' => 'loyalty',
                'color' => 'orange',
                'url_mobile'  => 'mobile/loyalty/points_history',
                'url_desktop' => 'loyalty/points_history',
            ],
            [
                'title' => 'Bonus Rules',
                'desc'  => 'Loyalty bonus & multiplier rules',
                'icon'  => 'fa-bolt',
                'perm'  => 'loyalty_view',
                'feature' => 'loyalty',
                'color' => 'orange',
                'url_mobile'  => 'mobile/loyalty/bonus_rules',
                'url_desktop' => 'loyalty/bonus_rules',
            ],
            [
                'title' => 'Product Points',
                'desc'  => 'Bonus points per product',
                'icon'  => 'fa-cubes',
                'perm'  => 'loyalty_view',
                'feature' => 'loyalty',
                'color' => 'blue',
                'url_mobile'  => 'mobile/loyalty/product_points',
                'url_desktop' => 'loyalty/product_points',
            ],
            [
                'title' => 'Referral Program',
                'desc'  => 'Customer referrals',
                'icon'  => 'fa-share-alt',
                'perm'  => 'loyalty_view',
                'feature' => 'loyalty',
                'color' => 'orange',
                'url_mobile'  => 'mobile/loyalty/referral_program',
                'url_desktop' => 'loyalty/referral_program',
            ],
            [
                'title' => 'Gift Cards',
                'desc'  => 'Issue & manage gift cards',
                'icon'  => 'fa-ticket',
                'perm'  => 'gift_cards_view',
                'feature' => 'gift_cards',
                'color' => 'purple',
                'url_mobile'  => 'gift_cards',
                'url_desktop' => 'gift_cards',
            ],
            [
                'title' => 'Store Credit',
                'desc'  => 'Customer store credit',
                'icon'  => 'fa-credit-card',
                'perm'  => 'store_credit_view',
                'feature' => 'store_credit',
                'color' => 'red',
                'url_mobile'  => 'mobile/store_credit',
                'url_desktop' => 'store_credit',
            ],
        ];

        $result = [];
        foreach ($items as $item) {
            // Permission gate
            if (!empty($item['perm']) && !$CI->permissions($item['perm'])) {
                continue;
            }
            // Feature flag gate (for items that map to a feature)
            if (!empty($item['feature']) && function_exists('mp_feature_enabled') && !mp_feature_enabled($item['feature'])) {
                continue;
            }
            $result[] = $item;
        }
        return $result;
    }
}
