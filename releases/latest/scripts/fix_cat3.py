with open('/Users/ralphmore/Sites/localhost/martpoint retail/application/models/Category_model.php', 'r') as f:
    lines = f.readlines()

# Insert upload code before line 90 (index 89, $info = array( in verify_and_save)
insert_idx = 89
upload_code = [
    "\t\t\t$category_image='';\n",
    "\t\t\tif(!empty($_FILES['category_image']['name'])){\n",
    "\t\t\t\tif(!is_dir('./uploads/categories/')) mkdir('./uploads/categories/', 0755, true);\n",
    "\t\t\t\t$config['upload_path']          = './uploads/categories/';\n",
    "\t\t\t\t$config['allowed_types']        = 'gif|jpg|jpeg|png|webp';\n",
    "\t\t\t\t$config['max_size']             = 1024;\n",
    "\t\t\t\t$config['max_width']            = 1500;\n",
    "\t\t\t\t$config['max_height']           = 1500;\n",
    "\t\t\t\t$this->load->library('upload', $config);\n",
    "\t\t\t\tif(!$this->upload->do_upload('category_image')){\n",
    "\t\t\t\t\treturn $this->upload->display_errors();\n",
    "\t\t\t\t}\n",
    "\t\t\t\telse{\n",
    "\t\t\t\t\t$category_image='uploads/categories/'.$this->upload->data('file_name');\n",
    "\t\t\t\t}\n",
    "\t\t\t}\n",
    "\n",
]
for code_line in reversed(upload_code):
    lines.insert(insert_idx, code_line)

# After insertion, 'status' => 1 line shifted by 17 lines
# Original line 95 is now at index 95 + 17 = 112
# Find it by looking for 'status' and '=> 1' in the first 150 lines
for i in range(90, 130):
    if "'status'" in lines[i] and "=> 1," in lines[i]:
        lines.insert(i, "\t\t\t\t\t\t\t\t'category_image' \t\t=> $category_image,\n")
        break

with open('/Users/ralphmore/Sites/localhost/martpoint retail/application/models/Category_model.php', 'w') as f:
    f.writelines(lines)

print("Done")
