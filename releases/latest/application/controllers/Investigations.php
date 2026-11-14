<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Investigations — request / pending / result_received / reviewed / cancelled.
 * Result upload uses private document storage and stops at result_received;
 * review is a separate clinician-permissioned action.
 */
class Investigations extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled() || !mp_feature_enabled('patient_registry')){
			$this->show_feature_not_activated('patient_registry'); return;
		}
		if(!physio_can('investigations_view')){
			$this->show_access_denied_page(); return;
		}
		$this->load->model('investigations_model', 'investigations');
		$this->load->model('encounters_model', 'encounters');
		$this->load->model('patient_docs_model', 'docs');
	}

	private function _json($arr){
		$this->output->set_content_type('application/json')->set_output(json_encode($arr));
	}

	private function _scopedRow($id){
		$row = $this->investigations->get((int)$id);
		if(!$row) return array(null, 'Investigation not found');
		if($row->encounter_id){
			$enc = $this->encounters->getEncounter((int)$row->encounter_id);
			if($enc && $enc->warehouse_id && !physio_can_branch($enc->warehouse_id)){
				return array(null, 'Access denied');
			}
		}
		return array($row, null);
	}

	public function index(){
		$storeId = get_current_store_id();
		$branchIds = physio_can('clinical_cross_branch') ? null : physio_branch_ids();
		$rows = $this->investigations->listRows($storeId, array(
			'status' => $this->input->get('status') ?: null,
			'branch_ids' => $branchIds,
		));
		$verCounts = array();
		if($rows && $this->db->table_exists('db_investigation_results')){
			$counts = $this->db->select('investigation_id, MAX(version_no) AS v')
				->where('store_id', $storeId)->group_by('investigation_id')
				->get('db_investigation_results')->result();
			foreach($counts as $c){ $verCounts[$c->investigation_id] = (int)$c->v; }
		}
		$data = array_merge($this->data, array(
			'page_title' => 'Investigations',
			'rows'       => $rows,
			'result_versions' => $verCounts,
			'statuses' => Investigations_model::STATUSES,
			'can' => array(
				'request' => physio_can('investigations_request'),
				'result'  => physio_can('investigations_result_enter'),
				'review'  => physio_can('investigations_review'),
			),
		));
		$data['content'] = $this->load->view('investigations/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	public function request(){
		if(!physio_can('investigations_request')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$encId = (int)$this->input->post('encounter_id');
		if($encId){
			$enc = $this->encounters->getEncounter($encId);
			if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
				$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
			}
		}
		$res = $this->investigations->request(array(
			'patient_id'    => (int)$this->input->post('patient_id'),
			'episode_id'    => (int)$this->input->post('episode_id'),
			'encounter_id'  => $encId ?: null,
			'test_name'     => $this->input->post('test_name', TRUE),
			'category'      => $this->input->post('category', TRUE),
			'priority'      => $this->input->post('priority', TRUE),
			'facility_name' => $this->input->post('facility_name', TRUE),
			'external_ref'  => $this->input->post('external_ref', TRUE),
			'request_notes' => $this->input->post('request_notes', TRUE),
		));
		$this->_json(isset($res['error'])
			? array('status' => 'error', 'message' => $res['error'])
			: array('status' => 'success', 'message' => 'Requested — ' . $res['request_ref'], 'investigation_id' => $res['investigation_id']));
	}

	public function pending($id = 0){
		if(!physio_can('investigations_request')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		list($row, $err) = $this->_scopedRow($id);
		if($err){ $this->_json(array('status' => 'error', 'message' => $err)); return; }
		$res = $this->investigations->markPending((int)$id);
		$this->_json($res === true ? array('status' => 'success', 'message' => 'Sent / pending') : array('status' => 'error', 'message' => $res['error']));
	}

	/**
	 * Result intake — optional file attachment goes through private document
	 * storage, then the row moves to result_received. NOT reviewed.
	 */
	public function result($id = 0){
		if(!physio_can('investigations_result_enter')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		list($row, $err) = $this->_scopedRow($id);
		if($err){ $this->_json(array('status' => 'error', 'message' => $err)); return; }
		$docId = (int)$this->input->post('document_id');
		if(!$docId && !empty($_FILES['result_file']['name'])){
			$up = $this->docs->upload($_FILES['result_file'], array(
				'patient_id' => (int)$row->patient_id,
				'episode_id' => (int)$row->episode_id ?: null,
				'category'   => 'investigation',
				'title'      => $row->request_ref . ' result — ' . $row->test_name,
				'status'     => 'completed',
			));
			if(isset($up['error'])){ $this->_json(array('status' => 'error', 'message' => $up['error'])); return; }
			$docId = (int)$up['document_id'];
		}
		$summary = trim($this->input->post('result_summary', TRUE) ?: '');
		if(!$docId && $summary === ''){
			$this->_json(array('status' => 'error', 'message' => 'Attach a result file or enter a result summary')); return;
		}
		$res = $this->investigations->recordResult((int)$id, $docId, $summary);
		$this->_json($res === true
			? array('status' => 'success', 'message' => 'Result received — awaiting clinician review')
			: array('status' => 'error', 'message' => $res['error']));
	}

	/**
	 * Amend a REVIEWED result — the original is preserved as a superseded
	 * version and the replacement goes through fresh review. Reason required.
	 */
	public function amend($id = 0){
		if(!physio_can('investigations_result_enter')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		list($row, $err) = $this->_scopedRow($id);
		if($err){ $this->_json(array('status' => 'error', 'message' => $err)); return; }
		$docId = (int)$this->input->post('document_id');
		if(!$docId && !empty($_FILES['result_file']['name'])){
			$up = $this->docs->upload($_FILES['result_file'], array(
				'patient_id' => (int)$row->patient_id,
				'episode_id' => (int)$row->episode_id ?: null,
				'category'   => 'investigation',
				'title'      => $row->request_ref . ' corrected result — ' . $row->test_name,
				'status'     => 'completed',
			));
			if(isset($up['error'])){ $this->_json(array('status' => 'error', 'message' => $up['error'])); return; }
			$docId = (int)$up['document_id'];
		}
		$res = $this->investigations->amendResult((int)$id, $docId,
			trim($this->input->post('result_summary', TRUE) ?: ''),
			trim($this->input->post('reason', TRUE) ?: ''));
		$this->_json($res === true
			? array('status' => 'success', 'message' => 'Amended result received — awaiting fresh clinician review')
			: array('status' => 'error', 'message' => $res['error']));
	}

	public function review($id = 0){
		if(!physio_can('investigations_review')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		list($row, $err) = $this->_scopedRow($id);
		if($err){ $this->_json(array('status' => 'error', 'message' => $err)); return; }
		$res = $this->investigations->review((int)$id,
			trim($this->input->post('note', TRUE) ?: '') ?: null);
		$this->_json($res === true ? array('status' => 'success', 'message' => 'Result reviewed') : array('status' => 'error', 'message' => $res['error']));
	}

	public function cancel($id = 0){
		if(!physio_can('investigations_request')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		list($row, $err) = $this->_scopedRow($id);
		if($err){ $this->_json(array('status' => 'error', 'message' => $err)); return; }
		$res = $this->investigations->cancel((int)$id, $this->input->post('reason', TRUE));
		$this->_json($res === true ? array('status' => 'success', 'message' => 'Cancelled') : array('status' => 'error', 'message' => $res['error']));
	}
}
