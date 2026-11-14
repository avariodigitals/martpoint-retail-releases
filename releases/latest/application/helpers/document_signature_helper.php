<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('mp_document_signature_image')) {
    /** A PDF-safe image for the store's enabled, uploaded signature. */
    function mp_document_signature_image($store_id = null) {
        $store_id = (int)($store_id ?: get_current_store_id());
        if ($store_id <= 0 || (int)mp_get_store_pos_setting($store_id, 'show_signature', 0) !== 1) {
            return '';
        }
        // The signature path lives on db_store; the modular theme table does
        // not have a signature column. The settings helper falls back to it.
        $path = trim((string)mp_get_store_theme_setting($store_id, 'signature', ''));
        $prefix = 'uploads/signature/';
        if (strpos($path, $prefix) !== 0 || basename($path) !== substr($path, strlen($prefix))) {
            return '';
        }
        $file = FCPATH.$path;
        if (!is_file($file) || filesize($file) > 1048576) {
            return '';
        }
        $info = @getimagesize($file);
        if (!$info) { return ''; }
        $mime = $info['mime'];
        if ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
            $image = @imagecreatefromwebp($file);
            if (!$image) { return ''; }
            ob_start();
            imagepng($image);
            $bytes = ob_get_clean();
            imagedestroy($image);
            return 'data:image/png;base64,'.base64_encode($bytes);
        }
        if (!in_array($mime, array('image/png', 'image/jpeg', 'image/gif'), true)) {
            return '';
        }
        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($file));
    }
}

if (!function_exists('mp_document_signature_tag')) {
    function mp_document_signature_tag($store_id = null) {
        $image = mp_document_signature_image($store_id);
        return $image === '' ? '' : '<img src="'.htmlspecialchars($image, ENT_QUOTES, 'UTF-8')
            .'" alt="Authorized signature" style="display:inline-block;max-width:180px;max-height:70px;width:auto;height:auto;">';
    }
}
