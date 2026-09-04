<?php
// Generate 192x192 PNG icon for PWA
$src = __DIR__ . '/uploads/site/default.png';
$dst = __DIR__ . '/uploads/site/icon-192.png';

if(extension_loaded('gd')){
    if(file_exists($src)){
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
            echo "Created uploads/site/icon-192.png from default.png\n";
            exit;
        }
    }
    // Fallback: generate colored icon with text
    $img = imagecreatetruecolor(192, 192);
    $bg = imagecolorallocate($img, 11, 17, 32);
    imagefill($img, 0, 0, $bg);
    $white = imagecolorallocate($img, 255, 255, 255);
    imagestring($img, 5, 60, 85, "MP", $white);
    imagepng($img, $dst);
    imagedestroy($img);
    echo "Created fallback uploads/site/icon-192.png\n";
} else {
    echo "GD extension not available. Please install GD or manually create a 192x192 PNG at uploads/site/icon-192.png\n";
}
