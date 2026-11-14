<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Paper consent — generate the patient-specific form, print it, upload the
 * signed copy to private storage, then verify the required signatories.
 * Upload never completes a consent; verification is a separate action.
 * Digital signing is out of scope pending the agreed signature method.
 */
class Consents extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled() || !mp_feature_enabled('patient_registry')){
			$this->show_feature_not_activated('patient_registry'); return;
		}
		if(!physio_can('patient_docs_view')){
			$this->show_access_denied_page(); return;
		}
		$this->load->model('consents_model', 'consents');
		$this->load->model('patient_docs_model', 'docs');
		$this->load->model('encounters_model', 'encounters');
	}

	private function _json($arr){
		$this->output->set_content_type('application/json')->set_output(json_encode($arr));
	}

	public function index(){
		$storeId = get_current_store_id();
		$data = array_merge($this->data, array(
			'page_title' => 'Consents',
			'rows'       => $this->consents->listRows($storeId, array(
				'status'     => $this->input->get('status') ?: null,
				'patient_id' => (int)$this->input->get('patient_id') ?: null,
			)),
			'patient'    => ($pid = (int)$this->input->get('patient_id'))
				? $this->db->select('p.*, c.customer_name')->from('db_patients p')
					->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
					->where('p.id', $pid)->where('p.store_id', $storeId)->get()->row()
				: null,
			'can' => array(
				'manage' => physio_can('patient_docs_upload'),
				'verify' => physio_can('patient_docs_release'),
			),
		));
		$data['content'] = $this->load->view('consents/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function generate(){
		if(!physio_can('patient_docs_upload')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$encId = (int)$this->input->post('encounter_id');
		if($encId){
			$enc = $this->encounters->getEncounter($encId);
			if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
				$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
			}
		}
		$res = $this->consents->generate(array(
			'patient_id'   => (int)$this->input->post('patient_id'),
			'episode_id'   => (int)$this->input->post('episode_id'),
			'encounter_id' => $encId ?: null,
			'consent_type' => $this->input->post('consent_type', TRUE),
			'title'        => $this->input->post('title', TRUE),
		));
		$this->_json(isset($res['error'])
			? array('status' => 'error', 'message' => $res['error'])
			: array('status' => 'success', 'message' => 'Consent generated', 'consent_id' => $res['consent_id'],
				'print_url' => base_url('consents/print_form/' . $res['consent_id'])));
	}

	/** Printable patient-specific form. */
	public function print_form($id = 0){
		$row = $this->consents->get((int)$id);
		if(!$row){ $this->show_access_denied_page(); return; }
		$patient = $this->db->select('p.*, c.customer_name, c.mobile')->from('db_patients p')
			->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
			->where('p.id', (int)$row->patient_id)->where('p.store_id', (int)$row->store_id)->get()->row();
		$this->load->view('consents/print', array('row' => $row, 'patient' => $patient, 'data' => $this->data));
	}

	/** Upload the signed scan — goes to private doc storage, then attaches. */
	public function upload_signed($id = 0){
		if(!physio_can('patient_docs_upload')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$row = $this->consents->get((int)$id);
		if(!$row){ $this->_json(array('status' => 'error', 'message' => 'Consent not found')); return; }
		if(empty($_FILES['doc_file']['name'])){
			$this->_json(array('status' => 'error', 'message' => 'Choose the signed file')); return;
		}
		$up = $this->docs->upload($_FILES['doc_file'], array(
			'patient_id' => (int)$row->patient_id,
			'episode_id' => (int)$row->episode_id ?: null,
			'category'   => 'consent',
			'title'      => $row->title . ' (signed)',
			'status'     => 'awaiting_verification',
		));
		if(isset($up['error'])){ $this->_json(array('status' => 'error', 'message' => $up['error'])); return; }
		$res = $this->consents->attachSigned((int)$id, (int)$up['document_id']);
		$this->_json($res === true
			? array('status' => 'success', 'message' => 'Signed copy stored — awaiting verification')
			: array('status' => 'error', 'message' => $res['error']));
	}

	/** Verify required signatories — the only path to completed. */
	public function verify($id = 0){
		if(!physio_can('patient_docs_release')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$signed = (array)$this->input->post('signed');
		$res = $this->consents->verify((int)$id, $signed);
		$this->_json($res === true
			? array('status' => 'success', 'message' => 'Consent verified and completed')
			: array('status' => 'error', 'message' => $res['error']));
	}

	public function decline($id = 0){
		if(!physio_can('patient_docs_upload')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$res = $this->consents->decline((int)$id, $this->input->post('reason', TRUE));
		$this->_json($res === true ? array('status' => 'success', 'message' => 'Consent marked declined') : array('status' => 'error', 'message' => $res['error']));
	}
}
