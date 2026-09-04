import re

with open('/Users/ralphmore/Sites/localhost/martpoint retail/application/models/Category_model.php', 'r') as f:
    content = f.read()

# 1. Fix verify_and_save
old = """\t\telse{\n\t\t\t$info = array(\n\t\t\t\t\t\t\t\t'count_id' \t\t\t\t=> get_count_id('db_category'), \n\t\t\t    \t\t\t\t'category_code' \t\t=> get_init_code('category'), \n\t\t\t    \t\t\t\t'category_name' \t\t=> $category,\n\t\t\t    \t\t\t\t'description' \t\t\t=> $description,\n\t\t\t    \t\t\t\t'status' \t\t\t\t=> 1,\n\t\t\t    \t\t\t);"""

new = """\t\telse{\n\t\t\t$category_image='';\n\t\t\tif(!empty($_FILES['category_image']['name'])){\n\t\t\t\tif(!is_dir('./uploads/categories/')) mkdir('./uploads/categories/', 0755, true);\n\t\t\t\t$config['upload_path']          = './uploads/categories/';\n\t\t\t\t$config['allowed_types']        = 'gif|jpg|jpeg|png|webp';\n\t\t\t\t$config['max_size']             = 1024;\n\t\t\t\t$config['max_width']            = 1500;\n\t\t\t\t$config['max_height']           = 1500;\n\t\t\t\t$this->load->library('upload', $config);\n\t\t\t\tif(!$this->upload->do_upload('category_image')){\n\t\t\t\t\treturn $this->upload->display_errors();\n\t\t\t\t}\n\t\t\t\telse{\n\t\t\t\t\t$category_image='uploads/categories/'.$this->upload->data('file_name');\n\t\t\t\t}\n\t\t\t}\n\n\t\t\t$info = array(\n\t\t\t\t\t\t\t\t'count_id' \t\t\t\t=> get_count_id('db_category'), \n\t\t\t    \t\t\t\t'category_code' \t\t=> get_init_code('category'), \n\t\t\t    \t\t\t\t'category_name' \t\t=> $category,\n\t\t\t    \t\t\t\t'description' \t\t\t=> $description,\n\t\t\t    \t\t\t\t'category_image' \t\t=> $category_image,\n\t\t\t    \t\t\t\t'status' \t\t\t\t=> 1,\n\t\t\t    \t\t\t);"""

if old in content:
    content = content.replace(old, new, 1)
    print("verify_and_save updated")
else:
    print("WARNING: verify_and_save not found")

# 2. Fix update_category
old2 = """\t\t\t$info = array(\n\t\t    \t\t\t\t'category_name' \t\t=> $category,\n\t\t    \t\t\t\t'description' \t\t\t=> $description,\n\t\t    \t\t\t);"""

new2 = """\t\t\t$category_image='';\n\t\t\tif(!empty($_FILES['category_image']['name'])){\n\t\t\t\tif(!is_dir('./uploads/categories/')) mkdir('./uploads/categories/', 0755, true);\n\t\t\t\t$config['upload_path']          = './uploads/categories/';\n\t\t\t\t$config['allowed_types']        = 'gif|jpg|jpeg|png|webp';\n\t\t\t\t$config['max_size']             = 1024;\n\t\t\t\t$config['max_width']            = 1500;\n\t\t\t\t$config['max_height']           = 1500;\n\t\t\t\t$this->load->library('upload', $config);\n\t\t\t\tif(!$this->upload->do_upload('category_image')){\n\t\t\t\t\treturn $this->upload->display_errors();\n\t\t\t\t}\n\t\t\t\telse{\n\t\t\t\t\t$category_image='uploads/categories/'.$this->upload->data('file_name');\n\t\t\t\t}\n\t\t\t}\n\n\t\t\t$info = array(\n\t\t    \t\t\t\t'category_name' \t\t=> $category,\n\t\t    \t\t\t\t'description' \t\t\t=> $description,\n\t\t    \t\t\t\t'category_image' \t\t=> $category_image,\n\t\t    \t\t\t);"""

if old2 in content:
    content = content.replace(old2, new2, 1)
    print("update_category updated")
else:
    print("WARNING: update_category not found")

# 3. Fix get_details
old3 = "$data['description']=$query->description;\n\t\t\t$data['store_id']=$query->store_id;"
new3 = "$data['description']=$query->description;\n\t\t\t$data['category_image']=$query->category_image;\n\t\t\t$data['store_id']=$query->store_id;"

if old3 in content:
    content = content.replace(old3, new3, 1)
    print("get_details updated")
else:
    print("WARNING: get_details not found")

with open('/Users/ralphmore/Sites/localhost/martpoint retail/application/models/Category_model.php', 'w') as f:
    f.write(content)

print("All done")
