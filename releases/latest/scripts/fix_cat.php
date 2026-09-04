<?php
$file = '/Users/ralphmore/Sites/localhost/martpoint retail/application/models/Category_model.php';
$lines = file($file);

// Insert upload logic after line 89 (index 88, after else{)
$upload_code = "\t\t\t\$category_image='';\n";
$upload_code .= "\t\t\tif(!empty(\$_FILES['category_image']['name'])){\n";
$upload_code .= "\t\t\t\tif(!is_dir('./uploads/categories/')) mkdir('./uploads/categories/', 0755, true);\n";
$upload_code .= "\t\t\t\t\$config['upload_path']          = './uploads/categories/';\n";
$upload_code .= "\t\t\t\t\$config['allowed_types']        = 'gif|jpg|jpeg|png|webp';\n";
$upload_code .= "\t\t\t\t\$config['max_size']             = 1024;\n";
$upload_code .= "\t\t\t\t\$config['max_width']            = 1500;\n";
$upload_code .= "\t\t\t\t\$config['max_height']           = 1500;\n";
$upload_code .= "\t\t\t\t\$this->load->library('upload', \$config);\n";
$upload_code .= "\t\t\t\t\tif(!\$this->upload->do_upload('category_image')){\n";
$upload_code .= "\t\t\t\t\t\treturn \$this->upload->display_errors();\n";
$upload_code .= "\t\t\t\t\t}\n";
$upload_code .= "\t\t\t\t\telse{\n";
$upload_code .= "\t\t\t\t\t\t\$category_image='uploads/categories/'.\$this->upload->data('file_name');\n";
$upload_code .= "\t\t\t\t\t}\n";
$upload_code .= "\t\t\t\t}\n\n";

// Insert before line 90 (index 89)
array_splice($lines, 90, 0, $upload_code);

// Now line 95 is the 'status' line (was 95, now shifted by upload code lines)
// We need to find the 'status' line in verify_and_save and add category_image before it
for ($i = 90; $i < count($lines); $i++) {
    if (strpos($lines[$i], "'status' \t\t\t\t=> 1,") !== false) {
        $lines[$i] = "\t\t\t\t\t\t\t\t'category_image' \t\t=> \$category_image,\n" . $lines[$i];
        break;
    }
}

file_put_contents($file, implode('', $lines));
echo "Done\n";
