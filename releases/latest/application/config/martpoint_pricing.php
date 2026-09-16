<?php
/**
 * MartPoint Retail Pricing Catalogue — approved commercial source.
 * This file is the single source of truth for the customer-facing catalogue.
 * The generated HTML/PDF, pricing pages and quotation selectors should read from here.
 */
return [
    'pricing_version' => '1.0',
    'effective_date' => date('Y-m-d'),
    'currency' => 'NGN',
    'plans' => [
        'basic' => [
            'name' => 'Basic',
            'annual_price' => 99999,
            'branch_limit' => 1,
            'user_limit' => 5,
            'product_limit' => 500,
            'variation_limit' => 10000,
            'online_product_limit' => 500,
            'service_limit' => 100,
            'media_storage_limit_mb' => 2048,
            'accent' => '#3B82F6',
        ],
        'standard' => [
            'name' => 'Standard',
            'annual_price' => 249999,
            'branch_limit' => 3,
            'user_limit' => 10,
            'product_limit' => 2000,
            'variation_limit' => 50000,
            'online_product_limit' => 2000,
            'service_limit' => 300,
            'media_storage_limit_mb' => 5120,
            'accent' => '#0057FF',
        ],
        'premium' => [
            'name' => 'Premium',
            'annual_price' => 499999,
            'branch_limit' => 5,
            'user_limit' => 25,
            'product_limit' => 5000,
            'variation_limit' => 150000,
            'online_product_limit' => 5000,
            'service_limit' => 500,
            'media_storage_limit_mb' => 10240,
            'accent' => '#7C3AED',
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'annual_price' => 999999,
            'branch_limit' => 10,
            'user_limit' => 50,
            'product_limit' => 10000,
            'variation_limit' => 500000,
            'online_product_limit' => 10000,
            'service_limit' => 1000,
            'media_storage_limit_mb' => 20480,
            'accent' => '#0F172A',
        ],
    ],
    'capacity_addons' => [
        ['label' => 'Additional branch', 'price' => 50000, 'unit' => 'per branch/year'],
        ['label' => 'Additional 5 users', 'price' => 25000, 'unit' => 'per year'],
        ['label' => 'Additional 500 main products', 'price' => 15000, 'unit' => 'per year'],
        ['label' => 'Additional 10,000 product variations', 'price' => 25000, 'unit' => 'per year'],
        ['label' => 'Additional 100 services', 'price' => 10000, 'unit' => 'per year'],
        ['label' => 'Additional 5 GB media storage', 'price' => 10000, 'unit' => 'per year'],
    ],
    'implementation' => [
        ['name' => 'Essential Remote Implementation', 'price' => 'From ₦50,000', 'scope' => 'One business, remote configuration, one scheduled remote training session and launch checks'],
        ['name' => 'Multi-branch Implementation', 'price' => 'From ₦150,000', 'scope' => 'Multi-branch configuration, role setup, structured training and coordinated launch'],
        ['name' => 'Premium Rollout', 'price' => 'From ₦300,000', 'scope' => 'More complex rollout, structured migration support, multiple training activities and launch coordination'],
        ['name' => 'Enterprise Implementation', 'price' => 'From ₦500,000', 'scope' => 'Requirements discovery, rollout planning, complex configuration, migration and deployment coordination'],
    ],
    'optional_services' => [
        ['name' => 'Additional remote training session', 'price' => '₦40,000 per session'],
        ['name' => 'Onsite implementation or training', 'price' => '₦100,000 per day'],
        ['name' => 'Requirements/discovery session', 'price' => '₦75,000'],
        ['name' => 'Custom storefront design', 'price' => 'From ₦150,000'],
        ['name' => 'Custom domain registration and configuration', 'price' => 'Quoted separately'],
        ['name' => 'Custom software development', 'price' => 'From ₦250,000'],
        ['name' => 'Data migration, catalogue cleanup or bulk product preparation', 'price' => 'Quoted after assessment'],
        ['name' => 'Travel, accommodation and logistics', 'price' => 'Quoted separately'],
    ],
    'offline' => [
        ['name' => 'Offline/Local MartPoint licence', 'price' => 'Starting from ₦250,000 per year'],
        ['name' => 'Additional offline branch', 'price' => 'Starting from ₦100,000 per year'],
        ['name' => 'Optional Annual Care plan', 'price' => 'Starting from ₦150,000/year'],
        ['name' => 'Installation and implementation', 'price' => 'Quoted after assessment'],
    ],
    'erp' => [
        'license_from' => 250000,
    ],
];
