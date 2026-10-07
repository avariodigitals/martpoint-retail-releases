<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Paper consent lifecycle.
 *
 * pending → awaiting_verification → completed
 *                    ↘ declined     ↘ superseded (by a re-issued consent)
 *
 * Uploading a signed scan only reaches awaiting_verification — completed
 * requires an explicit verification that records each required signatory.
 * Digital signing is intentionally absent pending the agreed identity and
 * signature method.
 */
class Consents_model extends CI_Model {

	const REQUIRED_SIGNATORIES = array('patient_or_guardian', 'clinician');

	public function __construct(){
		parent::__construct();
	}

	public function get($id, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', (int)$id)->where('store_id', $storeId)
			->get('db_consents')->row();
	}

	public function listRows($storeId = null, $filters = array()){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->select('c.*, p.patient_code, cu.customer_name')
			->from('db_consents c')
			->join('db_patients p', 'p.id = c.patient_id AND p.store_id = c.store_id', 'left')
			->join('db_customers cu', 'cu.id = p.customer_id AND cu.store_id = c.store_id', 'left')
			->where('c.store_id', $storeId);
		if(!empty($filters['status']))     $this->db->where('c.status', $filters['status']);
		if(!empty($filters['patient_id'])) $this->db->where('c.patient_id', (int)$filters['patient_id']);
		return $this->db->order_by('c.id', 'desc')->limit(500)->get()->result();
	}

	/**
	 * Generate a patient-specific consent form (a consent row + the wording
	 * snapshot to print). The form body is stored at generation time so a
	 * later template change never mutates what the patient signed.
	 */
	public function generate(array $d){
		$storeId = get_current_store_id();
		$patientId = (int)($d['patient_id'] ?? 0);
		if(!$patientId && !empty($d['encounter_id'])){
			$enc = $this->db->where('id', (int)$d['encounter_id'])->where('store_id', $storeId)
				->get('db_encounters')->row();
			if(!$enc) return array('error' => 'Visit not found');
			$patientId = (int)$enc->patient_id;
			$d['episode_id'] = $enc->episode_id;
		}
		$patient = $this->db->select('p.*, c.customer_name, c.mobile')
			->from('db_patients p')
			->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
			->where('p.id', $patientId)->where('p.store_id', $storeId)->get()->row();
		if(!$patient) return array('error' => 'Patient not found');

		$type  = preg_replace('/[^a-z0-9_]/i', '', (string)($d['consent_type'] ?? 'general_treatment')) ?: 'general_treatment';
		$title = trim((string)($d['title'] ?? '')) ?: $this->_titleFor($type);

		// Supersede any live consent of the same type for this patient.
		$open = $this->db->where('store_id', $storeId)->where('patient_id', $patientId)
			->where('consent_type', $type)->where_in('status', array('pending','awaiting_verification'))
			->get('db_consents')->result();

		$body = $this->_renderBody($type, $patient, $d);
		$this->db->insert('db_consents', array(
			'store_id'        => $storeId,
			'patient_id'      => $patientId,
			'episode_id'      => (int)($d['episode_id'] ?? 0) ?: null,
			'encounter_id'    => (int)($d['encounter_id'] ?? 0) ?: null,
			'consent_type'    => $type,
			'title'           => substr($title, 0, 160),
			'status'          => 'pending',
			'signatories_json'=> json_encode(self::REQUIRED_SIGNATORIES),
			'body_text'       => $body,
			'created_date'    => date('Y-m-d'),
			'created_time'    => date('H:i:s'),
			'created_by'      => $this->session->userdata('inv_username') ?: 'system',
			'system_ip'       => $this->input->ip_address(),
			'system_name'     => php_uname('n'),
		));
		$id = (int)$this->db->insert_id();
		foreach($open as $o){
			$this->db->where('id', $o->id)->update('db_consents', array(
				'status' => 'superseded', 'superseded_by_id' => $id, 'updated_at' => date('Y-m-d H:i:s'),
			));
		}
		return $id ? array('consent_id' => $id) : array('error' => 'Could not create consent');
	}

	/** Attach the signed scan (a private patient document). pending → awaiting_verification. */
	public function attachSigned($id, $documentId){
		$storeId = get_current_store_id();
		$row = $this->get($id, $storeId);
		if(!$row) return array('error' => 'Consent not found');
		if(!in_array($row->status, array('pending','awaiting_verification'))){
			return array('error' => 'Consent is ' . $row->status . ' — signed copy cannot be attached');
		}
		$doc = $this->db->where('id', (int)$documentId)->where('store_id', $storeId)
			->get('db_patient_documents')->row();
		if(!$doc || (int)$doc->patient_id !== (int)$row->patient_id){
			return array('error' => 'Document not found for this patient');
		}
		$this->db->where('id', (int)$id)->where('store_id', $storeId)->update('db_consents', array(
			'document_id' => (int)$documentId,
			'status'      => 'awaiting_verification',
			'updated_at'  => date('Y-m-d H:i:s'),
		));
		return true;
	}

	/**
	 * Verify required signatories on the scanned form — the ONLY path to
	 * 'completed'. $signed is a list of signatory roles that are visibly
	 * signed on the scan; every required role must be present.
	 * Caller holds patient_docs_release (verification of signed docs).
	 */
	public function verify($id, array $signed){
		$storeId = get_current_store_id();
		$row = $this->get($id, $storeId);
		if(!$row) return array('error' => 'Consent not found');
		if($row->status !== 'awaiting_verification' || !$row->document_id){
			return array('error' => 'Upload the signed copy before verifying');
		}
		$required = json_decode($row->signatories_json, true) ?: self::REQUIRED_SIGNATORIES;
		$missing  = array_diff($required, $signed);
		if($missing){
			return array('error' => 'Missing signatures: ' . implode(', ', array_map(function($s){ return str_replace('_', ' ', $s); }, $missing)));
		}
		$uid = (int)$this->session->userdata('inv_userid') ?: null;
		$this->db->where('id', (int)$id)->where('store_id', $storeId)->update('db_consents', array(
			'status'      => 'completed',
			'verified_by' => $uid,
			'verified_at' => date('Y-m-d H:i:s'),
			'updated_at'  => date('Y-m-d H:i:s'),
		));
		$this->db->where('id', (int)$row->document_id)->where('store_id', $storeId)
			->update('db_patient_documents', array(
				'status' => 'completed', 'verified_by' => $uid, 'verified_at' => date('Y-m-d H:i:s'),
			));
		return true;
	}

	public function decline($id, $reason){
		$reason = trim((string)$reason);
		if($reason === '') return array('error' => 'A decline reason is required');
		$storeId = get_current_store_id();
		$row = $this->get($id, $storeId);
		if(!$row) return array('error' => 'Consent not found');
		if(in_array($row->status, array('completed','superseded'))) return array('error' => 'Consent is ' . $row->status);
		$this->db->where('id', (int)$id)->where('store_id', $storeId)->update('db_consents', array(
			'status'           => 'declined',
			'declined_reason'  => substr($reason, 0, 500),
			'declined_by_name' => $this->session->userdata('inv_username') ?: 'system',
			'declined_at'      => date('Y-m-d H:i:s'),
			'updated_at'       => date('Y-m-d H:i:s'),
		));
		return true;
	}

	private function _titleFor($type){
		return array(
			'general_treatment' => 'Consent to Physiotherapy Assessment & Treatment',
			'procedure'         => 'Consent to Procedure',
			'home_visit'        => 'Consent to Home Visit',
			'data_sharing'      => 'Consent to Share Clinical Information',
		)[$type] ?? 'Patient Consent Form';
	}

	/** Consent wording — tenant wording can be customised later; this default
	 *  is intentionally generic pending the clinic's approved form. */
	private function _renderBody($type, $patient, array $d){
		$store = $this->db->select('store_name')->where('id', get_current_store_id())->get('db_store')->row();
		$lines = array(
			$this->_titleFor($type),
			'',
			'Clinic: ' . ($store->store_name ?? ''),
			'Patient: ' . ($patient->customer_name ?? '') . ' (' . ($patient->patient_code ?? '') . ')',
			'Date of birth: ' . ($patient->dob ?? '—'),
			'Date: ' . date('Y-m-d'),
			'',
			'I confirm that the nature, purpose, benefits and material risks of the proposed assessment and treatment have been explained to me, and that I have had the opportunity to ask questions.',
			'I consent to the assessment and treatment described, and I understand I may withdraw consent at any time.',
			'',
			'Patient / Guardian signature: ____________________    Date: ____________',
			'Clinician signature:         ____________________    Date: ____________',
		);
		return implode("\n", $lines);
	}
}
