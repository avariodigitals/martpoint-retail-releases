import sys

file = '/Users/ralphmore/Sites/localhost/martpoint retail/application/models/Category_model.php'
with open(file, 'r') as f:
    lines = f.readlines()

# Find the first $info = array( in verify_and_save
# We'll insert upload code right before it
insert_idx = None
for i, line in enumerate(lines):
    if '$info = array(' in line:
        insert_idx = i
        break

if insert_idx is None:
    print("ERROR: Could not find $info = array(")
    sys.exit(1)

# Insert the upload code before $info = array(
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

# Now find the 'status' => 1 line (which should be the first one, in verify_and_save)
# and add category_image before it
for i, line in enumerate(lines):
    if "'status' \t\t\t\t=> 1," in line:
        lines.insert(i, "\t\t\t\t\t\t\t\t'category_image' \t\t=> $category_image,\n")
        break

with open(file, 'w') as f:
    f.writelines(lines)

print("Done")
