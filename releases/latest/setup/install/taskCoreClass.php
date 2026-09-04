<?php
class Core {
	function checkEmpty($data)
	{
	    if(!empty($data['hostname']) && !empty($data['username']) && !empty($data['database']) && !empty($data['url'])){
	        return true;
	    }else{
	        return false;
	    }
	}

	function show_message($type,$message) {
		return $message;
	}
	
	function getAllData($data) {
		return $data;
	}

	function write_config($data) {

       
        $template_path 	= 'includes/templatevthree.php';

		$output_path 	= '../../application/config/database.php';

		$database_file = file_get_contents($template_path);

		// Escape values so that single quotes or backslashes in credentials
		// do not break the generated PHP single-quoted strings.
		$new  = str_replace("%HOSTNAME%",addcslashes($data['hostname'],"'\\"),$database_file);
		$new  = str_replace("%USERNAME%",addcslashes($data['username'],"'\\"),$new);
		$new  = str_replace("%PASSWORD%",addcslashes($data['password'],"'\\"),$new);
		$new  = str_replace("%DATABASE%",addcslashes($data['database'],"'\\"),$new);

		$handle = fopen($output_path,'w+');
		@chmod($output_path,0777);
		
		if(is_writable(dirname($output_path))) {

			if(fwrite($handle,$new)) {
				//return true;
				if($this->write_config2($data)){
					@chmod($output_path,0644);
					return true;
				}
				return false;

			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	function write_config2($data) {

        $template_path 	= 'includes/config_file.php';
		$output_path 	= '../../application/config/config.php';

		$database_file = file_get_contents($template_path);

		$encryption_key = bin2hex(random_bytes(16));

		$new  = str_replace("%BASE_URL%",$data['url'],$database_file);
		$new  = str_replace("%ENCRYPTION_KEY%",$encryption_key,$new);
		
		$handle = fopen($output_path,'w+');
		@chmod($output_path,0777);
		
		if(is_writable(dirname($output_path))) {

			if(fwrite($handle,$new)) {
				//return true;
				if($this->write_config3($data)){
					@chmod($output_path,0644);
					return true;
				}
				return false;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	function write_config3($data) {

        $template_path 	= 'assets/codeigniter_index_page/index.php';
		$output_path 	= '../../index.php';


		@chmod($template_path,0777);
		@chmod($output_path,0777);
		
		if(copy($template_path, $output_path)){
			if($this->write_config4($data)){
				@chmod($output_path,0644);
				return true;
			}
			return false;
		}
		return false;
	}
	function write_config4($data) {

        $mid_path = '../../application/controllers/Login.php';
		

		$mid_path_content = file_get_contents($mid_path);

		$new  = str_replace("@@appinfo@@",appinfo(),$mid_path_content);
		
		$handle = fopen($mid_path,'w+');
		@chmod($mid_path,0777);
		
		if(is_writable(dirname($mid_path))) {

			if(fwrite($handle,$new)) {
				@chmod($mid_path,0644);
				return true;	
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	function checkFile(){
	    $output_path = '../../application/config/database.php';
	    $template_path = 'includes/templatevthree.php';

	    if (!file_exists($output_path)) {
	        // Fresh clone may not contain database.php (it is git-ignored).
	        // Seed it from the installer placeholder so write_config() succeeds.
	        if (!file_exists($template_path) || !copy($template_path, $output_path)) {
	            return false;
	        }
	        @chmod($output_path, 0777);
	    }

	    return true;
	}
}