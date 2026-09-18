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

	// Write a file reliably: create it when missing, loosen permissions only
	// where needed, and fail honestly only when nothing can write it.
	private function _write_file($output_path, $contents)
	{
		if(!file_exists($output_path)){
			@file_put_contents($output_path, '');
		}
		if(!file_exists($output_path)){
			// Directory may be too strict for the PHP user - relax it when we own it.
			@chmod(dirname($output_path), 0755);
			@file_put_contents($output_path, '');
		}
		if(file_exists($output_path) && !is_writable($output_path)){
			@chmod($output_path, 0664);
		}
		if(@file_put_contents($output_path, $contents) === false){
			return false;
		}
		@chmod($output_path, 0644);
		return true;
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

		if(!$this->_write_file($output_path, $new)){
			return false;
		}
		return $this->write_config2($data);
	}
	function write_config2($data) {

        $template_path 	= 'includes/config_file.php';
		$output_path 	= '../../application/config/config.php';

		$database_file = file_get_contents($template_path);

		$encryption_key = bin2hex(random_bytes(16));

		$new  = str_replace("%BASE_URL%",$data['url'],$database_file);
		$new  = str_replace("%ENCRYPTION_KEY%",$encryption_key,$new);

		if(!$this->_write_file($output_path, $new)){
			return false;
		}
		return $this->write_config3($data);
	}
	function write_config3($data) {

        $template_path 	= 'assets/codeigniter_index_page/index.php';
		$output_path 	= '../../index.php';

		$contents = @file_get_contents($template_path);
		if($contents === false){
			return false;
		}
		if(!$this->_write_file($output_path, $contents)){
			return false;
		}
		return $this->write_config4($data);
	}
	function write_config4($data) {

        $mid_path = '../../application/controllers/Login.php';

		$mid_path_content = file_get_contents($mid_path);

		$new  = str_replace("@@appinfo@@",appinfo(),$mid_path_content);

		return $this->_write_file($mid_path, $new);
	}
	function checkFile(){
	    $output_path = '../../application/config/database.php';
	    $template_path = 'includes/templatevthree.php';

	    if (!file_exists($output_path)) {
	        // Fresh clone may not contain database.php (it is git-ignored).
	        // Seed it from the installer placeholder so write_config() succeeds.
	        $tpl = @file_get_contents($template_path);
	        if ($tpl === false || !$this->_write_file($output_path, $tpl)) {
	            return false;
	        }
	    }

	    return true;
	}
}
