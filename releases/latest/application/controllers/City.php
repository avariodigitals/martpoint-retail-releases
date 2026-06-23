<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class City extends MY_Controller {
	public function __construct(){
		parent::__construct();
		$this->load_global();
	}

	public function index(){
		if(!special_access()){
			$this->show_access_denied_page();exit;
		}
		$data=$this->data;
		$data['page_title']='Cities List';
		$this->load->view('city-list', $data);
	}
}
