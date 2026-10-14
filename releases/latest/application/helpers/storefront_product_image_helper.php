<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Brand-only artwork: no product names, theme names, icons or stock photos. */
function sf_product_placeholder_svg($brand, $color){
    $brand = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', (string)$brand));
    $brand = mb_substr($brand, 0, 120, 'UTF-8');
    $label = htmlspecialchars($brand, ENT_QUOTES | ENT_XML1, 'UTF-8');
    $color = preg_match('/^#[0-9a-f]{3}([0-9a-f]{3})?$/i', $color) ? $color : '#3B82F6';
    $size = min(64, max(14, (int)(650 / max(1, mb_strlen($brand, 'UTF-8')))));
    return '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="800" viewBox="0 0 800 800">'
        . '<rect width="800" height="800" fill="#fff"/>'
        . '<rect width="800" height="800" fill="'.$color.'" fill-opacity=".09"/>'
        . '<text x="400" y="400" dy=".35em" text-anchor="middle" font-family="Arial,sans-serif" font-size="'.$size.'" font-weight="600" fill="'.$color.'" fill-opacity=".32">'.$label.'</text></svg>';
}

/** Local paths retain compatibility with themes that check file_exists(). */
function sf_product_placeholder_path($storeId, $brand, $color){
    $svg = sf_product_placeholder_svg($brand, $color);
    $dir = 'uploads/cache/product-placeholders/'.(int)$storeId;
    $path = $dir.'/'.hash('sha256', $svg).'.svg';
    if (is_file(FCPATH.$path)) return $path;
    if (!is_dir(FCPATH.$dir) && !@mkdir(FCPATH.$dir, 0755, true) && !is_dir(FCPATH.$dir)) {
        log_message('error', 'Cannot create storefront product placeholder cache');
        return '';
    }
    // Write once via rename so concurrent visitors never receive a partial SVG.
    $tmp = @tempnam(FCPATH.$dir, 'brand-');
    if ($tmp === false) return '';
    $written = file_put_contents($tmp, $svg);
    if ($written !== false) @chmod($tmp, 0644);
    if ($written === false || !@rename($tmp, FCPATH.$path)) {
        @unlink($tmp);
        log_message('error', 'Cannot write storefront product placeholder cache');
        return '';
    }
    return $path;
}

function sf_product_image_exists($path){
    $path = trim((string)$path);
    if ($path === '') return false;
    // Do not fetch merchant URLs from the server. Browser failures are handled separately.
    if (preg_match('~^https?://~i', $path)) return (bool)filter_var($path, FILTER_VALIDATE_URL);
    if (preg_match('~(?:^|/)(?:no_image|no-image|placeholder)\.[a-z]+$~i', $path)) return false;
    return is_file(FCPATH.ltrim($path, '/'));
}
