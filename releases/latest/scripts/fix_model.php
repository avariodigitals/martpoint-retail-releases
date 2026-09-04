<?php
// Temporary fix script for Category_model.php category image support
$file = __DIR__ . '/application/models/Category_model.php';
$lines = file($file);

// Insert upload code before line 90 (index 89, $info = array( in verify_and_save)
$insert_idx = 89;
$upload_code = [
    "\t\t\t\$category_image='';\n",
    "\t\t\tif(!empty(\$_FILES['category_image']['name'])){\n",
    "\t\t\t\tif(!is_dir('./uploads/categories/')) mkdir('./uploads/categories/', 0755, true);\n",
    "\t\t\t\t\$config['upload_path']          = './uploads/categories/';\n",
    "\t\t\t\t\$config['allowed_types']        = 'gif|jpg|jpeg|png|webp';\n",
    "\t\t\t\t\$config['max_size']             = 1024;\n",
    "\t\t\t\t\$config['max_width']            = 1500;\n",
    "\t\t\t\t\$config['max_height']           = 1500;\n",
    "\t\t\t\t\$this->load->library('upload', \$config);\n",
    "\t\t\t\t\tif(!\$this->upload->do_upload('category_image')){\n",
    "\t\t\t\t\t\treturn \$this->upload->display_errors();\n",
    "\t\t\t\t\t}\n",
    "\t\t\t\t\telse{\n",
    "\t\t\t\t\t\t\$category_image='uploads/categories/'.\$this->upload->data('file_name');\n",
    "\t\t\t\t\t}\n",
    "\t\t\t\t}\n",
    "\n",
];
foreach (array_reverse($upload_code) as $code_line) {
    array_splice($lines, $insert_idx, 0, $code_line);
}

// Find 'status' => 1 line in verify_and_save and add category_image before it
for ($i = 90; $i < 130; $i++) {
    if (strpos($lines[$i], "'status'") !== false && strpos($lines[$i], "=> 1,") !== false) {
        array_splice($lines, $i, 0, "\t\t\t\t\t\t\t\t'category_image' \t\t=> \$category_image,\n");
        break;
    }
}

file_put_contents($file, implode('', $lines));
echo "Category_model.php updated successfully. You can delete this file now.";
