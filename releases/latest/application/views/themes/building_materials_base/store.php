<?php
// The purpose-built homepage stays owned by Theme Content. The builder only
// supplies explicitly opted-in Building Materials additions before the enquiry CTA.
$bmHomeValues = $bm_values;
$bmHomeFeatured = $featured_products ?? [];
$bmHomeAll = $all_products ?? [];
$bmHomeSections = $homepage_sections ?? [];
$bmHeroTag = 'h1';
foreach(['hero_banner','perspective','featured_products','store_info'] as $bmSectionBase){
    if($bmSectionBase === 'featured_products' && empty($sells_products)) continue;
    include __DIR__.'/_home_editorial.php';
}
foreach($bmHomeSections as $section){
    if(empty($section->is_enabled)) continue;
    $bmSectionKey = (string)$section->section_key;
    $bmSectionBase = preg_replace('/_\d+$/', '', $bmSectionKey);
    if(in_array($bmSectionBase, ['hero_banner','featured_products','store_info','contact_section'], true)) continue;
    $bmSectionId = 'bm-home-' . preg_replace('/[^a-z0-9_-]/i', '', $bmSectionKey);
    $bmCfg = json_decode($section->config_json ?? '', true);
    $bmCfg = is_array($bmCfg) ? ($bmCfg['industry_content']['building_materials'] ?? []) : [];
    if(!is_array($bmCfg)) $bmCfg = [];
    $bmText = function($field, $fallback = '') use ($bmCfg){
        return isset($bmCfg[$field]) && is_scalar($bmCfg[$field]) && trim((string)$bmCfg[$field]) !== '' ? (string)$bmCfg[$field] : $fallback;
    };
    $bm_values = $bmHomeValues;
    $featured_products = $bmHomeFeatured;
    $all_products = $bmHomeAll;
    if(in_array($bmSectionBase, ['best_sellers','new_arrivals'], true)){
        $featured_products = $bmSectionBase === 'best_sellers' ? ($best_sellers ?? []) : ($new_arrivals ?? []);
        $all_products = [];
        $bm_values['home_systems_title'] = $bmText('title', $bmSectionBase === 'best_sellers' ? 'Proven systems.' : 'New systems.');
        $bm_values['home_systems_support'] = $bmText('subtitle', $bm_values['home_systems_support'] ?? '');
        if(!$featured_products) continue;
    }
    ob_start();
    if(in_array($bmSectionBase, ['best_sellers','new_arrivals'], true)){
        if(!empty($sells_products)) include __DIR__.'/_home_editorial.php';
    } else {
        include __DIR__.'/_home_components.php';
    }
    $bmSectionHtml = trim(ob_get_clean());
    if($bmSectionHtml !== '') echo '<div class="bm-home-section" id="'.htmlspecialchars($bmSectionId, ENT_QUOTES, 'UTF-8').'" data-section="'.htmlspecialchars($bmSectionKey, ENT_QUOTES, 'UTF-8').'">'.$bmSectionHtml.'</div>';
}
$bm_values = $bmHomeValues;
$featured_products = $bmHomeFeatured;
$all_products = $bmHomeAll;
$bmSectionBase = 'contact_section';
include __DIR__.'/_home_editorial.php';
?>
