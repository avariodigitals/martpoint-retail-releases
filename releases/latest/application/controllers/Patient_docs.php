<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Patient documents — private clinical file storage.
 *
 * Files live outside the served docroot and are reachable ONLY through
 * download(), which requires an authenticated session, patient_docs_view,
 * store scope and (where the doc is episode-linked) branch scope. Every
 * access — granted or denied — is written to db_document_access_log.
 */
class Patient_docs extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled() || !mp_feature_enabled('patient_registry')){
			$this->show_feature_not_activated('patient_registry'); return;
		}
		$this->load->model('patient_docs_model', 'docs');
		$this->load->model('patients_model', 'patients');
		$this->load->model('encounters_model', 'encounters');
	}

	private function _json($arr){
		$this->output->set_content_type('application/json')->set_output(json_encode($arr));
	}

	/** Branch scope: a document linked to an episode follows that episode's
	 *  encounter branch; unattached docs are visible to any in-scope staff. */
	private function _branchOk($doc){
		if(!$doc || !$doc->episode_id) return true;
		$enc = $this->db->where('episode_id', (int)$doc->episode_id)
			->where('store_id', get_current_store_id())
			->order_by('id', 'desc')->get('db_encounters')->row();
		return !$enc || !$enc->warehouse_id || physio_can_branch($enc->warehouse_id);
	}

	public function index(){
		if(!physio_can('patient_docs_view')){ $this->show_access_denied_page(); return; }
		$patientId = (int)$this->input->get('patient_id');
		$patient   = $patientId ? $this->patients->getPatient($patientId) : null;
		$data = array_merge($this->data, array(
			'page_title'    => 'Patient Documents',
			'patient'       => $patient,
			'documents'     => $patient ? $this->docs->listForPatient($patientId) : array(),
			'storage_ready' => physio_docs_dir() !== null,
			'can'           => array(
				'upload'   => physio_can('patient_docs_upload'),
				'release'  => physio_can('patient_docs_release'),
				'clinical' => physio_can('patient_docs_clinical_view'),
			),
		));
		$data['content'] = $this->load->view('patient_docs/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function upload(){
		if(!physio_can('patient_docs_upload')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		if(empty($_FILES['doc_file']['name'])){
			$this->_json(array('status' => 'error', 'message' => 'Choose a file')); return;
		}
		$docId = (int)$this->input->post('document_id');
		if($docId){
			$doc = $this->docs->get($docId);
			if(!$doc || !$this->_branchOk($doc)){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		} else {
			$patient = $this->patients->getPatient((int)$this->input->post('patient_id'));
			if(!$patient){ $this->_json(array('status' => 'error', 'message' => 'Patient not found')); return; }
		}
		$res = $this->docs->upload($_FILES['doc_file'], array(
			'patient_id' => (int)$this->input->post('patient_id'),
			'episode_id' => (int)$this->input->post('episode_id'),
			'category'   => $this->input->post('category', TRUE) ?: 'other',
			'title'      => $this->input->post('title', TRUE),
		), $docId);
		$this->_json(isset($res['error'])
			? array('status' => 'error', 'message' => $res['error'])
			: array('status' => 'success', 'message' => 'Stored (v' . $res['version_no'] . ')', 'document_id' => $res['document_id']));
	}

	/** The ONLY file egress — authenticated, permissioned, logged. */
	public function download($id = 0, $versionId = 0){
		$deny = function($docId = 0){
			if($docId) $this->docs->logAccess((int)$docId, null, 'denied');
			$this->output->set_status_header(403);
			echo 'Access denied';
			return;
		};
		if(!physio_can('patient_docs_view')){
			$deny((int)$id); return;
		}
		$doc = $this->docs->get((int)$id);
		if(!$doc || !$this->_branchOk($doc)){ $deny((int)$id); return; }
		// Category enforcement at the file endpoint: clinical content
		// (assessments, investigation results, imaging) requires the
		// clinical-document permission — front-desk view access is not enough.
		if(!$this->docs->canViewDoc($doc)){ $deny((int)$id); return; }
		$res = $this->docs->resolveForDownload((int)$id, (int)$versionId);
		if(isset($res['error'])){ $deny((int)$id); return; }
		$this->output
			->set_content_type($res['mime'])
			->set_header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9._ -]/', '_', $res['name']) . '"')
			->set_header('Content-Length: ' . $res['size'])
			->set_header('X-Content-Type-Options: nosniff')
			->set_header('Cache-Control: private, no-store')
			->set_output(file_get_contents($res['path']));
	}

	public function release($id = 0){
		if(!physio_can('patient_docs_release')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$doc = $this->docs->get((int)$id);
		if(!$doc || !$this->_branchOk($doc)){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		if(!$this->docs->canViewDoc($doc)){
			$this->docs->logAccess((int)$id, null, 'denied');
			$this->_json(array('status' => 'error', 'message' => 'Clinical documents require clinical access')); return;
		}
		$res = $this->docs->releaseToPatient((int)$id);
		if($res === true){
			// "Report released" notice — suppressed at delivery if the release
			// is withdrawn before the queue runs.
			physio_notify('docs.report_released.' . (int)$id, array(
				'channel' => 'email', 'template_key' => 'report_released',
				'payload' => array('patient_id' => (int)$doc->patient_id, 'document_id' => (int)$id),
			));
		}
		$this->_json($res === true ? array('status' => 'success', 'message' => 'Released to patient') : array('status' => 'error', 'message' => $res['error']));
	}

	/** Withdraw a release — portal access is denied on the very next request. */
	public function unrelease($id = 0){
		if(!physio_can('patient_docs_release')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$doc = $this->docs->get((int)$id);
		if(!$doc || !$this->_branchOk($doc)){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		if(!$this->docs->canViewDoc($doc)){
			$this->docs->logAccess((int)$id, null, 'denied');
			$this->_json(array('status' => 'error', 'message' => 'Clinical documents require clinical access')); return;
		}
		$res = $this->docs->withdrawRelease((int)$id, trim((string)$this->input->post('reason', TRUE)));
		$this->_json($res === true ? array('status' => 'success', 'message' => 'Release withdrawn — the patient can no longer access this document') : array('status' => 'error', 'message' => $res['error']));
	}

	public function log($id = 0){
		if(!physio_can('patient_docs_view')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$doc = $this->docs->get((int)$id);
		if(!$doc || !$this->_branchOk($doc)){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		if(!$this->docs->canViewDoc($doc)){
			$this->docs->logAccess((int)$id, null, 'denied');
			$this->_json(array('status' => 'error', 'message' => 'Clinical documents require clinical access')); return;
		}
		$this->_json(array('status' => 'success', 'log' => $this->docs->accessLog((int)$id),
			'versions' => $this->docs->versions((int)$id)));
	}
}
