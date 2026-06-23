<?php
// Helper script to fix Category_model.php for category image support
$file = APPPATH . 'models/Category_model.php';
$content = file_get_contents($file);

// 1. Fix verify_and_save - add image upload before $info array
$old = <<<'EOT'
		else{
			$info = array(
							'count_id' 				=> get_count_id('db_category'), 
			    				'category_code' 		=> get_init_code('category'), 
			    				'category_name' 		=> $category,
			    				'description' 			=> $description,
			    				'status' 				=> 1,
			    			);
EOT;

$new = <<<'EOT'
		else{
			$category_image='';
			if(!empty($_FILES['category_image']['name'])){
				if(!is_dir('./uploads/categories/')) mkdir('./uploads/categories/', 0755, true);
				$config['upload_path']          = './uploads/categories/';
				$config['allowed_types']        = 'gif|jpg|jpeg|png|webp';
				$config['max_size']             = 1024;
				$config['max_width']            = 1500;
				$config['max_height']           = 1500;
				$this->load->library('upload', $config);
				if(!$this->upload->do_upload('category_image')){
					return $this->upload->display_errors();
				}
				else{
					$category_image='uploads/categories/'.$this->upload->data('file_name');
				}
			}

			$info = array(
							'count_id' 				=> get_count_id('db_category'), 
			    				'category_code' 		=> get_init_code('category'), 
			    				'category_name' 		=> $category,
			    				'description' 			=> $description,
			    				'category_image' 		=> $category_image,
			    				'status' 				=> 1,
			    			);
EOT;

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    echo "verify_and_save updated\n";
} else {
    echo "WARNING: verify_and_save pattern not found\n";
}

file_put_contents($file, $content);
echo "Done\n";
