<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

/**
 * Generate a square, center-cropped store logo as a PNG data URI.
 * DOMPDF renders this as a round avatar when the <img> is 1:1.
 *
 * @param string $logo_path Relative path from project root (e.g. uploads/store/xyz.png)
 * @param int    $size      Output width/height in pixels
 * @return string           data:image/png;base64,... or empty string on failure
 */
function mp_store_logo_round_base64($logo_path='', $size=120){
    if(empty($logo_path)) return '';
    $full_path = FCPATH . $logo_path;
    if(!file_exists($full_path)){
        $demo = FCPATH . store_demo_logo();
        if(!file_exists($demo)) return '';
        $full_path = $demo;
    }

    $ext = strtolower(pathinfo($full_path, PATHINFO_EXTENSION));
    if($ext === 'png') $src = @imagecreatefrompng($full_path);
    elseif(in_array($ext, ['jpg', 'jpeg'])) $src = @imagecreatefromjpeg($full_path);
    elseif($ext === 'webp') $src = @imagecreatefromwebp($full_path);
    else $src = @imagecreatefromstring(file_get_contents($full_path));
    if(!$src) return '';

    $w = imagesx($src);
    $h = imagesy($src);
    $crop = min($w, $h);
    $x = ($w - $crop) / 2;
    $y = ($h - $crop) / 2;

    $square = imagecreatetruecolor($crop, $crop);
    $white = imagecolorallocate($square, 255, 255, 255);
    imagefill($square, 0, 0, $white);
    imagecopy($square, $src, 0, 0, $x, $y, $crop, $crop);
    imagedestroy($src);

    $out = imagecreatetruecolor($size, $size);
    imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
    imagecopyresampled($out, $square, 0, 0, 0, 0, $size, $size, $crop, $crop);
    imagedestroy($square);

    ob_start();
    imagepng($out);
    $data = ob_get_clean();
    imagedestroy($out);
    return 'data:image/png;base64,' . base64_encode($data);
}
