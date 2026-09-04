<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->model('migrate_model','migrate_m');
		$this->load->helper('migrate');
	}

	private function _guard(){
		if(!is_admin() && !is_store_admin() && $this->session->userdata('role_id') != 1){
			$this->show_access_denied_page();
			exit;
		}
	}

	public function index(){
		$this->_guard();
		$data = $this->data;
		$data['page_title'] = 'Data Migration Wizard';
		$data['step'] = $this->input->get('step') ?: 'upload';
		$data['sql_file'] = $this->session->userdata('migrate_sql_file');
		$data['uploads_dir'] = $this->session->userdata('migrate_uploads_dir');
		$data['analysis'] = $this->session->userdata('migrate_analysis');
		$data['import_log'] = $this->session->userdata('migrate_import_log');
		$data['stores'] = $this->db->get('db_store')->result();
		$data['content'] = $this->load->view('migrate/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function upload(){
		$this->_guard();
		set_time_limit(0);
		ini_set('max_execution_time', 0);
		ini_set('memory_limit', '1024M');

		$upload_dir = FCPATH.'backups/';
		if(!is_dir($upload_dir) || !is_writable($upload_dir)){
			$this->session->set_flashdata('failed', 'The backups/ folder is not writable.');
			redirect(base_url('migrate?step=upload'));
			return;
		}

		if(empty($_FILES['sql_file']['tmp_name']) || !is_uploaded_file($_FILES['sql_file']['tmp_name'])){
			$this->session->set_flashdata('failed', 'Please upload the old database SQL dump.');
			redirect(base_url('migrate?step=upload'));
			return;
		}

		$ts = date('Ymd_His');
		$sql_path = $upload_dir.'migrate_old_'.$ts.'.sql';
		if(!move_uploaded_file($_FILES['sql_file']['tmp_name'], $sql_path)){
			$this->session->set_flashdata('failed', 'Failed to save the uploaded SQL file.');
			redirect(base_url('migrate?step=upload'));
			return;
		}

		// Optional uploads ZIP
		$uploads_dir = null;
		if(!empty($_FILES['uploads_zip']['tmp_name']) && is_uploaded_file($_FILES['uploads_zip']['tmp_name'])){
			$zip_path = $upload_dir.'migrate_uploads_'.$ts.'.zip';
			if(move_uploaded_file($_FILES['uploads_zip']['tmp_name'], $zip_path)){
				$uploads_dir = $this->migrate_m->extract_uploads_zip($zip_path);
				if(!$uploads_dir){
					$this->session->set_flashdata('warning', 'Uploads zip could not be extracted. You can restore it later.');
				}
			}
		}

		// Stage the old SQL into a temporary database and analyze it
		$loaded = $this->migrate_m->load_sql_to_staging($sql_path);
		if(!$loaded){
			$this->session->set_flashdata('failed', 'Could not load the SQL file into the staging database. Make sure the MySQL CLI is available and the file is a valid SQL dump.');
			redirect(base_url('migrate?step=upload'));
			return;
		}

		$analysis = $this->migrate_m->analyze();
		$this->session->set_userdata('migrate_sql_file', $sql_path);
		$this->session->set_userdata('migrate_uploads_dir', $uploads_dir);
		$this->session->set_userdata('migrate_analysis', $analysis);
		$this->session->set_flashdata('success', 'SQL file uploaded and analyzed. Review the tables below before importing.');
		redirect(base_url('migrate?step=analyze'));
	}

	public function import(){
		$this->_guard();
		set_time_limit(0);
		ini_set('max_execution_time', 0);
		ini_set('memory_limit', '1024M');

		$target_store_id = (int)$this->input->post('target_store_id', TRUE);
		$password_option = $this->input->post('password_option', TRUE);
		$default_password = $this->input->post('default_password', TRUE);
		$skip_admin = (bool)$this->input->post('skip_admin', TRUE);

		if(!$target_store_id){
			$this->session->set_flashdata('failed', 'Please select a target store.');
			redirect(base_url('migrate?step=analyze'));
			return;
		}

		$log = $this->migrate_m->import_all($target_store_id, $password_option, $default_password, $skip_admin);
		$this->session->set_userdata('migrate_import_log', $log);
		$this->session->set_flashdata('success', 'Data import completed. Review the results below.');
		redirect(base_url('migrate?step=import'));
	}

	public function restore_uploads(){
		$this->_guard();
		$source = $this->input->post('uploads_source', TRUE);
		$session_dir = $this->session->userdata('migrate_uploads_dir');
		$use_source = $source ?: $session_dir;

		if(empty($use_source) || !is_dir($use_source)){
			$this->session->set_flashdata('failed', 'No uploads folder or zip available to restore.');
			redirect(base_url('migrate?step=import'));
			return;
		}

		$result = $this->migrate_m->copy_uploads($use_source);
		if($result['status'] !== 'ok'){
			$this->session->set_flashdata('failed', $result['message']);
			redirect(base_url('migrate?step=import'));
			return;
		}

		$this->session->set_flashdata('success', 'Uploads copied successfully.');
		redirect(base_url('migrate?step=finish'));
	}

	public function cleanup(){
		$this->_guard();
		$this->migrate_m->cleanup();
		$this->session->unset_userdata(['migrate_sql_file','migrate_uploads_dir','migrate_analysis','migrate_import_log']);
		$this->session->set_flashdata('success', 'Migration staging database cleaned up. Please review your settings before going live.');
		redirect(base_url('migrate?step=finish'));
	}
}
