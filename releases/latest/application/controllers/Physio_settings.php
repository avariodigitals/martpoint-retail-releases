<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Physio_settings extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled() || !(physio_can('md_authority') || physio_can('can_approve'))){
			$this->show_access_denied_page(); return;
		}
		$this->_ensureColumns();
	}

	private function _ensureColumns(){
		if(!$this->db->field_exists('physio_document_secret', 'db_sitesettings')){
			$this->db->query("ALTER TABLE `db_sitesettings` ADD COLUMN `physio_document_secret` VARCHAR(120) DEFAULT NULL");
		}
	}

	public function documents(){
		$row = $this->db->select('physio_document_secret')->order_by('id', 'asc')->limit(1)->get('db_sitesettings')->row();
		$data = array_merge($this->data, array(
			'page_title' => 'Physio Document Settings',
			'secret_set' => $row && trim((string)$row->physio_document_secret) !== '',
		));
		$data['content'] = $this->load->view('physio_settings/documents', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function save_documents(){
		if(!physio_can('md_authority') && !physio_can('can_approve')){
			echo json_encode(array('status'=>'error','message'=>'Access denied')); return;
		}
		$this->_ensureColumns();
		$secret = trim((string)$this->input->post('physio_document_secret', TRUE));
		if($secret === ''){
			echo json_encode(array('status'=>'error','message'=>'Document secret is required')); return;
		}
		if(strlen($secret) < 6){
			echo json_encode(array('status'=>'error','message'=>'Use at least 6 characters for the document secret')); return;
		}
		$row = $this->db->select('id')->order_by('id', 'asc')->limit(1)->get('db_sitesettings')->row();
		if(!$row){
			echo json_encode(array('status'=>'error','message'=>'Site settings row was not found')); return;
		}
		$this->db->where('id', (int)$row->id)->update('db_sitesettings', array('physio_document_secret'=>$secret));
		if(function_exists('mp_audit_log')){
			mp_audit_log('settings', 'update', 'physio_documents', 'Updated Physio document secret');
		}
		echo json_encode(array('status'=>'success','message'=>'Physio document secret saved'));
	}
}
