<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(!function_exists('mp_plant_banner_enabled')){
    function mp_plant_banner_enabled($kind, $store_id = null){
        $store_id = (int)($store_id ?: get_current_store_id());
        if(!in_array($kind, array('quotation','invoice'), true)){ return false; }
        if(!function_exists('mp_get_store_profile')){ return false; }
        $profile = mp_get_store_profile($store_id);
        return ($profile['industry_type'] ?? '') === 'oil_processing_plant'
            && (int)mp_get_store_setting($store_id, 'plant_documents', $kind.'_banner_enabled', 0) === 1;
    }
}

if(!function_exists('mp_plant_banner_image')){
    function mp_plant_banner_image($store_id = null){
        $store_id = (int)($store_id ?: get_current_store_id());
        $path = (string)mp_get_store_setting($store_id, 'plant_documents', 'banner_path', '');
        $prefix = 'uploads/plant_documents/'.$store_id.'/';
        if(strpos($path, $prefix) !== 0 || basename($path) !== substr($path, strlen($prefix))){ return ''; }
        $full = FCPATH.$path;
        if(!is_file($full) || filesize($full) > 3145728){ return ''; }
        $info = @getimagesize($full);
        if(!$info || !in_array($info['mime'], array('image/png','image/jpeg'), true)){ return ''; }
        return 'data:'.$info['mime'].';base64,'.base64_encode(file_get_contents($full));
    }
}
