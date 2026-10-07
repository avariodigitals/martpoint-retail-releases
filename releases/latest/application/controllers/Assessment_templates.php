<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Assessment templates — draft / preview / publish / new version.
 * Existing assessments keep the template_id + template_version captured at
 * save time, so publishing a new version never rewrites history.
 */
class Assessment_templates extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled()){ $this->show_feature_not_activated('physiotherapy'); return; }
		if(!physio_can('assessment_templates_manage')){ $this->show_access_denied_page(); return; }
		$this->load->model('assessments_model', 'asm');
	}

	private function _json($arr){
		$this->output->set_content_type('application/json')->set_output(json_encode($arr));
	}

	public function index(){
		$data = array_merge($this->data, array(
			'page_title' => 'Assessment Templates',
			'groups'     => $this->asm->templateGroups(),
		));
		$data['content'] = $this->load->view('assessment_templates/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function edit($id = 0){
		$storeId = get_current_store_id();
		$t = $this->asm->getTemplate((int)$id);
		if(!$t || (int)$t->store_id !== (int)$storeId){ show_404(); return; }
		$data = array_merge($this->data, array(
			'page_title' => ($t->status === 'draft' ? 'Edit draft' : 'Template').' — '.$t->name,
			't'          => $t,
			'versions'   => $this->asm->templateVersions($t->template_key, $storeId),
		));
		$data['content'] = $this->load->view('assessment_templates/edit', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function preview($id = 0){
		$storeId = get_current_store_id();
		$t = $this->asm->getTemplate((int)$id);
		if(!$t || !in_array((int)$t->store_id, array(0, (int)$storeId), true)){ show_404(); return; }
		$data = array_merge($this->data, array(
			'page_title' => 'Preview — '.$t->name.' v'.(int)$t->version,
			't'          => $t,
			'sections'   => $this->asm->templateSections($t),
		));
		$data['content'] = $this->load->view('assessment_templates/preview', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function create(){
		$key  = trim((string)$this->input->post('template_key', TRUE));
		$name = trim((string)$this->input->post('name', TRUE));
		$sec  = (string)$this->input->post('sections_json', FALSE);
		$res  = $this->asm->createDraft($key, $name, $sec);
		if(isset($res['error'])){ $this->_json(array('status'=>'error','message'=>$res['error'])); return; }
		$this->_json(array('status'=>'success','message'=>'Draft created','id'=>$res['id']));
	}

	public function new_version(){
		$key = trim((string)$this->input->post('template_key', TRUE));
		$res = $this->asm->draftNewVersion($key);
		if(isset($res['error'])){ $this->_json(array('status'=>'error','message'=>$res['error'])); return; }
		$this->_json(array('status'=>'success','message'=>'Draft v'.$res['version'].' created','id'=>$res['id']));
	}

	public function save($id = 0){
		$res = $this->asm->updateDraft((int)$id,
			trim((string)$this->input->post('name', TRUE)),
			(string)$this->input->post('sections_json', FALSE),
			(int)$this->input->post('provisional'));
		if($res !== true){ $this->_json(array('status'=>'error','message'=>$res['error'])); return; }
		$this->_json(array('status'=>'success','message'=>'Draft saved'));
	}

	public function publish($id = 0){
		$res = $this->asm->publishTemplate((int)$id);
		if($res !== true){ $this->_json(array('status'=>'error','message'=>$res['error'])); return; }
		$this->_json(array('status'=>'success','message'=>'Published — new assessments now use this version; existing assessments keep theirs'));
	}
}
