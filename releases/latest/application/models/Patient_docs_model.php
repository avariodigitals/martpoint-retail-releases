<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Patient documents — clinical files in private storage.
 *
 * Files NEVER live under public uploads/: physio_docs_dir() resolves a path
 * outside the docroot (or a shielded fallback). Version rows hold an opaque
 * server-side filename; the raw path is never returned to the client.
 * Every view/download/upload is written to db_document_access_log.
 */
class Patient_docs_model extends CI_Model {

	/** Categories that hold clinical content — downloading them needs
	 *  patient_docs_clinical_view (clinical staff), not plain
	 *  patient_docs_view (front desk). A document linked as an
	 *  investigation result is ALWAYS clinical, whatever its label. */
	const CLINICAL_CATEGORIES = array(
		'clinical','assessment','investigation','investigation_result',
		'result','imaging','lab_result','clinical_note','discharge_summary',
	);

	public function __construct(){
		parent::__construct();
		if(!function_exists('physio_docs_dir')) $this->load->helper('physio');
	}

	/** Effective sensitivity of a document — declared category OR usage
	 *  as an investigation-result attachment upgrades it to clinical. */
	public function isClinical($doc){
		if(!$doc) return false;
		if(in_array($doc->category, self::CLINICAL_CATEGORIES, true)) return true;
		if($this->db->table_exists('db_investigations')){
			$linked = $this->db->where('store_id', (int)$doc->store_id)
				->where('result_document_id', (int)$doc->id)
				->count_all_results('db_investigations');
			if($linked > 0) return true;
		}
		return false;
	}

	/** May the current user view/download this document? */
	public function canViewDoc($doc){
		if(!$doc) return false;
		if($this->isClinical($doc)) return physio_can('patient_docs_clinical_view');
		return physio_can('patient_docs_view');
	}

	public function get($docId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('id', (int)$docId)->where('store_id', $storeId)
			->get('db_patient_documents')->row();
	}

	public function versions($docId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('document_id', (int)$docId)->where('store_id', $storeId)
			->order_by('version_no', 'desc')->get('db_document_versions')->result();
	}

	/**
	 * Documents visible to the current user for a patient. Without
	 * patient_docs_clinical_view the clinical rows are filtered out
	 * entirely — front desk sees administrative documents only.
	 */
	public function listForPatient($patientId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$rows = $this->db->select('d.*, v.version_no, v.file_size, v.mime, v.created_at AS file_uploaded_at')
			->from('db_patient_documents d')
			->join('db_document_versions v', 'v.id = d.current_version_id', 'left')
			->where('d.store_id', $storeId)->where('d.patient_id', (int)$patientId)
			->order_by('d.id', 'desc')->get()->result();
		if(physio_can('patient_docs_clinical_view')) return $rows;
		return array_values(array_filter($rows, function($d){ return !$this->isClinical($d); }));
	}

	/**
	 * Upload a file as a NEW version of an existing document, or create the
	 * document (version 1). $file is the $_FILES entry.
	 * Returns array('document_id'=>,'version_no'=>) or array('error'=>msg).
	 */
	public function upload(array $file, array $meta, $docId = 0){
		$storeId = get_current_store_id();
		$check = physio_docs_validate_file($file['tmp_name'], $file['name']);
		if(!$check['ok']) return array('error' => $check['error']);
		$dir = physio_docs_dir();
		if(!$dir) return array('error' => 'Private document storage is unavailable');

		$this->db->trans_start();
		if($docId){
			$doc = $this->get($docId, $storeId);
			if(!$doc) return array('error' => 'Document not found');
		} else {
			$patientId = (int)($meta['patient_id'] ?? 0);
			$patient = $this->db->where('id', $patientId)->where('store_id', $storeId)->get('db_patients')->row();
			if(!$patient) return array('error' => 'Patient not found');
			$this->db->insert('db_patient_documents', array(
				'store_id'   => $storeId,
				'patient_id' => $patientId,
				'episode_id' => (int)($meta['episode_id'] ?? 0) ?: null,
				'category'   => substr($meta['category'] ?? 'other', 0, 40),
				'title'      => substr(trim($meta['title'] ?? ($file['name'] ?? 'Document')), 0, 255),
				'status'     => $meta['status'] ?? 'draft',
				'created_date' => date('Y-m-d'),
				'created_time' => date('H:i:s'),
				'created_by'   => $this->session->userdata('inv_username') ?: 'system',
				'system_ip'    => $this->input->ip_address(),
				'system_name'  => php_uname('n'),
			));
			$docId = (int)$this->db->insert_id();
			$doc = $this->get($docId, $storeId);
		}

		$nextVer = ((int)$this->db->where('document_id', $docId)->count_all_results('db_document_versions')) + 1;
		// Opaque server-side name — original name is never used on disk.
		$fname = 'd' . $docId . '_v' . $nextVer . '_' . bin2hex(random_bytes(8)) . '.' . $check['ext'];
		$dest  = $dir . '/' . $fname;
		if(!@move_uploaded_file($file['tmp_name'], $dest) && !@rename($file['tmp_name'], $dest)){
			$this->db->trans_rollback();
			return array('error' => 'Could not store the file');
		}
		@chmod($dest, 0640);

		$this->db->insert('db_document_versions', array(
			'store_id'    => $storeId,
			'document_id' => $docId,
			'version_no'  => $nextVer,
			'file_path'   => $fname,
			'file_hash'   => hash_file('sha256', $dest),
			'file_size'   => filesize($dest),
			'mime'        => $this->_mimeFor($check['ext']),
			'uploaded_by' => $this->session->userdata('inv_username') ?: 'system',
			'created_at'  => date('Y-m-d H:i:s'),
		));
		$verId = (int)$this->db->insert_id();
		$this->db->where('id', $docId)->where('store_id', $storeId)
			->update('db_patient_documents', array('current_version_id' => $verId, 'status' => 'draft'));
		$this->logAccess($docId, $verId, 'upload', $storeId);
		$this->db->trans_complete();
		if($this->db->trans_status() === FALSE){
			@unlink($dest);
			return array('error' => 'Save failed');
		}
		return array('document_id' => $docId, 'version_no' => $nextVer, 'version_id' => $verId);
	}

	/**
	 * Resolve a downloadable file for an authorised caller.
	 * Returns array('path'=>,'mime'=>,'name'=>) or array('error'=>msg).
	 * Caller must already have checked physio_can('patient_docs_view') +
	 * branch scope — this method logs every access, granted or not.
	 */
	public function resolveForDownload($docId, $versionId = 0){
		$storeId = get_current_store_id();
		$doc = $this->get($docId, $storeId);
		if(!$doc) return array('error' => 'Document not found');
		$ver = $versionId
			? $this->db->where('id', (int)$versionId)->where('document_id', $docId)->where('store_id', $storeId)->get('db_document_versions')->row()
			: $this->db->where('id', (int)$doc->current_version_id)->get('db_document_versions')->row();
		if(!$ver){ $this->logAccess($docId, $versionId ?: null, 'denied', $storeId); return array('error' => 'File not found'); }
		if(trim((string)$ver->file_path) === ''){ $this->logAccess($docId, $ver->id, 'denied', $storeId); return array('error' => 'Attachment is missing'); }
		$path = rtrim(physio_docs_dir(), '/') . '/' . $ver->file_path;
		if(!is_file($path)){ $this->logAccess($docId, $ver->id, 'denied', $storeId); return array('error' => 'File missing from storage'); }
		$this->logAccess($docId, (int)$ver->id, 'download', $storeId);
		$name = ($doc->title ?: 'document') . ' - v' . $ver->version_no . '.' . pathinfo($ver->file_path, PATHINFO_EXTENSION);
		return array('path' => $path, 'mime' => $ver->mime ?: 'application/octet-stream', 'name' => $name, 'size' => (int)$ver->file_size);
	}

	public function setStatus($docId, $status, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->where('id', (int)$docId)->where('store_id', $storeId)
			->update('db_patient_documents', array('status' => substr($status, 0, 30)));
	}

	public function releaseToPatient($docId){
		$storeId = get_current_store_id();
		$doc = $this->get($docId, $storeId);
		if(!$doc) return array('error' => 'Document not found');
		if($doc->status === 'missing_attachment') return array('error' => 'Cannot release a document with a missing attachment');
		$ver = $this->db->where('id', (int)$doc->current_version_id)->where('document_id', (int)$docId)
			->where('store_id', $storeId)->get('db_document_versions')->row();
		if(!$ver || trim((string)$ver->file_path) === '') return array('error' => 'A stored attachment is required before release');
		$path = rtrim(physio_docs_dir(), '/') . '/' . $ver->file_path;
		if(!is_file($path)) return array('error' => 'Attachment is missing from private storage');
		$this->db->where('id', $docId)->where('store_id', $storeId)->update('db_patient_documents', array(
			'released_to_patient' => 1,
			'released_by'         => (int)$this->session->userdata('inv_userid') ?: null,
			'released_at'         => date('Y-m-d H:i:s'),
		));
		$this->logAccess($docId, $doc->current_version_id, 'release', $storeId);
		return true;
	}

	/** Withdraw a patient release — portal access denies on the very next request. */
	public function withdrawRelease($docId, $reason = null){
		$storeId = get_current_store_id();
		$doc = $this->get($docId, $storeId);
		if(!$doc) return array('error' => 'Document not found');
		$this->db->where('id', $docId)->where('store_id', $storeId)->update('db_patient_documents', array(
			'released_to_patient' => 0,
			'withdrawn_by'        => (int)$this->session->userdata('inv_userid') ?: null,
			'withdrawn_at'        => date('Y-m-d H:i:s'),
			'withdraw_reason'     => $reason ?: null,
		));
		$this->logAccess($docId, $doc->current_version_id, 'unrelease', $storeId);
		return true;
	}

	public function logAccess($docId, $versionId, $action, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$this->db->insert('db_document_access_log', array(
			'store_id'    => $storeId,
			'document_id' => (int)$docId,
			'version_id'  => $versionId ? (int)$versionId : null,
			'action'      => substr($action, 0, 20),
			'user_id'     => (int)$this->session->userdata('inv_userid') ?: null,
			'username'    => $this->session->userdata('inv_username') ?: null,
			'ip'          => $this->input->ip_address(),
			'created_at'  => date('Y-m-d H:i:s'),
		));
	}

	public function accessLog($docId, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		return $this->db->where('document_id', (int)$docId)->where('store_id', $storeId)
			->order_by('id', 'desc')->limit(200)->get('db_document_access_log')->result();
	}

	private function _mimeFor($ext){
		return array(
			'pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
			'png' => 'image/png', 'webp' => 'image/webp', 'dcm' => 'application/dicom',
		)[$ext] ?? 'application/octet-stream';
	}
}
