<?php
// Generate a 192x192 PNG icon for PWA from existing logo
$src = __DIR__ . '/uploads/site/default.png';
$dst = __DIR__ . '/uploads/site/icon-192.png';

if(file_exists($src) && extension_loaded('gd')){
    $info = getimagesize($src);
    if($info['mime'] == 'image/png'){
        $orig = imagecreatefrompng($src);
    } elseif($info['mime'] == 'image/jpeg'){
        $orig = imagecreatefromjpeg($src);
    } else {
        $orig = false;
    }

    if($orig){
        $new = imagecreatetruecolor(192, 192);
        imagealphablending($new, false);
        imagesavealpha($new, true);
        $transparent = imagecolorallocatealpha($new, 0, 0, 0, 127);
        imagefill($new, 0, 0, $transparent);
        imagecopyresampled($new, $orig, 0, 0, 0, 0, 192, 192, imagesx($orig), imagesy($orig));
        imagepng($new, $dst);
        imagedestroy($orig);
        imagedestroy($new);
        echo "Created 192x192 icon: uploads/site/icon-192.png\n";
    } else {
        echo "Could not process source image.\n";
    }
} else {
    echo "GD not available or source image missing. Using fallback.\n";
    // Create a simple colored icon
    if(extension_loaded('gd')){
        $img = imagecreatetruecolor(192, 192);
        $bg = imagecolorallocate($img, 11, 17, 32); // #0B1120
        imagefill($img, 0, 0, $bg);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagestring($img, 5, 60, 85, "MP", $white);
        imagepng($img, $dst);
        imagedestroy($img);
        echo "Created fallback 192x192 icon: uploads/site/icon-192.png\n";
    }
}
