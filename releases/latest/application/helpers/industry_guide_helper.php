<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** The single walkthrough relevant to the current store's business type. */
if (!function_exists('mp_industry_guide')) {
    function mp_industry_guide($industry_type = null) {
        if ($industry_type === null) {
            $profile = function_exists('mp_get_store_profile') ? mp_get_store_profile() : array();
            $industry_type = $profile['industry_type'] ?? '';
        }
        $guides = array(
            'physiotherapy_rehabilitation' => array('Zonat PhysioSpace — Walkthrough', 'Patient journeys, therapy, bed accounts and procurement.', 'physio-workspace-zonat-physiospace.html', 'fa-heartbeat', 'physio-workspace-zonat-physiospace.pdf'),
            'oil_processing_plant' => array('Processing Plant Use Case', 'Zonat Processing LTD: materials, runs, quotations and invoices.', 'oil-processing-plant-zonat.html', 'fa-industry'),
            'fashion' => array('Fashion Use Case', 'A boutique day from opening to customer support.', 'fashion-scenario.html', 'fa-female'),
            'boutique' => array('Fashion Use Case', 'A boutique day from opening to customer support.', 'fashion-scenario.html', 'fa-female'),
            'printing' => array('Printing Shop Guide', 'Quotes, jobs, production and delivery.', 'printing-shop-guide.html', 'fa-print'),
            'nylon_polythene' => array('Nylon & Polythene Use Case', 'Materials, film and bag production through dispatch.', 'nylon-polythene-customer-guide.html', 'fa-industry'),
            'skincare' => array('Skincare Use Case', 'Formulas, batches and customer orders.', 'skincare-customer-guide.html', 'fa-leaf'),
            'electronics' => array('Electronics Use Case', 'Devices, serial numbers and warranty support.', 'electronics-customer-guide.html', 'fa-laptop'),
            'gadget_store' => array('Gadget Store Use Case', 'Zonat Gadgets: stock, sales and warranty support.', 'gadget-store-zonat-gadgets.html', 'fa-bolt'),
            'frozen_foods_retailer' => array('Frozen Foods Use Case', 'Cold stock, orders and daily checks.', 'frozen-food-customer-guide.html', 'fa-snowflake-o'),
            'butcher' => array('Butchery Use Case', 'Meat stock, processing and daily sales.', 'butchery-frozen-customer-guide.html', 'fa-cutlery'),
            'perfume_shop' => array('Perfume Shop Use Case', 'Blending, decants and bespoke orders.', 'perfume-shop-customer-guide.html', 'fa-flask'),
            'car_dealership' => array('Automobile Use Case', 'Vehicle stock, sales and customer records.', 'automobile-customer-guide.html', 'fa-car'),
        );
        if (!isset($guides[$industry_type])) return null;
        $guide = $guides[$industry_type];
        return array('title'=>$guide[0], 'description'=>$guide[1], 'file'=>$guide[2],
            'icon'=>$guide[3], 'pdf'=>$guide[4] ?? null);
    }
}
