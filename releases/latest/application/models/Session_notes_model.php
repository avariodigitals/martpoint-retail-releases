<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Session_notes_model — the clinic's paper TREATMENT CHARTS sheet.
 *
 * One signed row per booked session: SESSION DATE | TREATMENT OFFERED |
 * SIGN | REMARKS. It is keyed to db_treatment_sessions.id, not to the visit,
 * so a chart line always knows which session of the course it records
 * ("session 3 of 5") and the assessment remark — the physio's hand-over note
 * to Finance — stays a separate record with a separate job.
 *
 * UNIQUE (store_id, session_id): re-saving amends that session's chart
 * instead of stacking duplicates.
 */
class Session_notes_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	private function _table(){
		return $this->db->table_exists('db_session_treatment_notes');
	}

	/** The chart row for one session, or null when nothing is signed yet. */
	public function forSession($sessionId){
		if(!$this->_table() || (int)$sessionId <= 0) return null;
		return $this->db->where('store_id', get_current_store_id())
			->where('session_id', (int)$sessionId)
			->get('db_session_treatment_notes')->row();
	}

	/**
	 * The patient's chart sheet — most recent treatments first, capped so the
	 * card stays a summary rather than the whole case note.
	 */
	public function forPatient($patientId, $limit = 10){
		if(!$this->_table() || (int)$patientId <= 0) return array();
		return $this->db->where('store_id', get_current_store_id())
			->where('patient_id', (int)$patientId)
			->order_by('treatment_date', 'desc')->order_by('id', 'desc')
			->limit((int)$limit)->get('db_session_treatment_notes')->result();
	}

	/**
	 * Chart rows for a list of sessions in one query, keyed by session id —
	 * the diary screen needs to know which booked sessions are already signed
	 * without a query per card.
	 */
	public function mapForSessions(array $sessionIds){
		$ids = array();
		foreach($sessionIds as $id){ if((int)$id > 0) $ids[] = (int)$id; }
		if(!$this->_table() || empty($ids)) return array();
		$rows = $this->db->where('store_id', get_current_store_id())
			->where_in('session_id', array_unique($ids))
			->get('db_session_treatment_notes')->result();
		$map = array();
		foreach($rows as $r){ $map[(int)$r->session_id] = $r; }
		return $map;
	}

	/**
	 * Who gave the treatment. Taken from the booking's clinician, then from the
	 * clinician the visit is assigned to — never assumed to be the person
	 * typing, because whoever is on duty may be covering the course.
	 *
	 * Returns array(user_id, name) or array(null, null) when nobody is named.
	 */
	public function treatedByFor($session, $encounter = null){
		if(!function_exists('mp_first_name')){ $this->load->helper('physio'); }
		$userId = (int)($session->clinician_id ?? 0);
		if(!$userId && !empty($session->encounter_id)){
			$enc = $this->db->select('clinician_user_id')->where('store_id', get_current_store_id())
				->where('id', (int)$session->encounter_id)->get('db_encounters')->row();
			if($enc) $userId = (int)$enc->clinician_user_id;
		}
		if(!$userId && $encounter){ $userId = (int)($encounter->clinician_user_id ?? 0); }
		if(!$userId) return array(null, null);
		return array($userId, mp_first_name($userId, ''));
	}

	/**
	 * POST save — create or amend the chart for a session.
	 *
	 * D: session_id (required), treatment_date (Y-m-d), treatment_offered
	 *    (required), remarks, treated_by (physio who gave it), encounter_id
	 *    (only used when the session itself carries none).
	 * The session number and course size are copied from the session, never
	 * trusted from the browser, so "N of M" cannot drift from the real booking.
	 */
	public function save(array $d){
		$storeId = get_current_store_id();
		if(!$this->_table()){
			return array('ok' => false, 'error' => 'The treatment chart table is missing — run the pending update first.');
		}
		$sessionId = (int)($d['session_id'] ?? 0);
		if($sessionId <= 0) return array('ok' => false, 'error' => 'No session to chart');

		$this->load->model('sessions_model', 'sessions');
		$s = $this->sessions->getSession($sessionId);
		if(!$s) return array('ok' => false, 'error' => 'Session not found');
		if(in_array((string)$s->status, array('cancelled', 'no_show'), true)){
			return array('ok' => false, 'error' => 'That session was cancelled or marked as a no-show — there is no treatment to chart for it.');
		}

		$offered = trim((string)($d['treatment_offered'] ?? ''));
		if($offered === '') return array('ok' => false, 'error' => 'Say what treatment was given');

		$date = trim((string)($d['treatment_date'] ?? '')) ?: date('Y-m-d');
		if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
			|| !checkdate((int)substr($date, 5, 2), (int)substr($date, 8, 2), (int)substr($date, 0, 4))){
			return array('ok' => false, 'error' => 'The treatment date is not a real date');
		}

		$encounterId = $s->encounter_id ? (int)$s->encounter_id : null;
		if(!$encounterId && !empty($d['encounter_id'])){
			$cand = $this->db->select('id')->where('store_id', $storeId)
				->where('patient_id', (int)$s->patient_id)->where('id', (int)$d['encounter_id'])
				->get('db_encounters')->row();
			$encounterId = $cand ? (int)$cand->id : null;
		}

		if(!function_exists('mp_first_name')){ $this->load->helper('physio'); }
		$signerId = (int)$this->session->userdata('inv_userid');
		// The treating physio is the one named on the booking unless the person
		// charting says otherwise — a covering physio corrects it, they do not
		// sign someone else's treatment as their own.
		$treatedId = (int)($d['treated_by'] ?? 0);
		if($treatedId){
			$therapist = $this->db->select('id, first_name')->where('store_id', $storeId)->where('id', $treatedId)
				->get('db_users')->row();
			if(!$therapist) return array('ok' => false, 'error' => 'The named physiotherapist does not belong to this branch');
			$treatedName = mp_first_name($treatedId, (string)($therapist->first_name ?? ''));
		} else {
			list($treatedId, $treatedName) = $this->treatedByFor($s);
			$treatedId = $treatedId ?: null;
		}

		$now = date('Y-m-d H:i:s');
		$row = array(
			'encounter_id'     => $encounterId,
			'patient_id'       => (int)$s->patient_id,
			'session_no'       => (int)$s->session_no,
			'units_total'      => (int)$s->units_total,
			'treatment_date'   => $date,
			'treatment_offered'=> $offered,
			'remarks'          => trim((string)($d['remarks'] ?? '')) ?: null,
			'treated_by'       => $treatedId,
			'treated_by_name'  => $treatedName,
			'signed_by'        => $signerId ?: null,
			'signed_by_name'   => mp_first_name($signerId, (string)$this->session->userdata('first_name')),
			'updated_at'       => $now,
		);

		$existing = $this->forSession($sessionId);
		if($existing){
			// The physio who first signed keeps the signature line; everyone else
			// who changes the sheet is named and dated as an amendment, so a
			// hand-over never silently rewrites the record.
			unset($row['signed_by'], $row['signed_by_name']);
			if((int)$existing->signed_by !== $signerId){
				$row['amended_by'] = $signerId ?: null;
				$row['amended_by_name'] = mp_first_name($signerId, (string)$this->session->userdata('first_name'));
				$row['amended_at'] = $now;
			}
			$this->db->where('store_id', $storeId)->where('session_id', $sessionId)
				->update('db_session_treatment_notes', $row);
			return array('ok' => true, 'id' => (int)$existing->id, 'amended' => true);
		}
		$row['store_id'] = $storeId;
		$row['session_id'] = $sessionId;
		$row['created_at'] = $now;
		$this->db->insert('db_session_treatment_notes', $row);
		return array('ok' => true, 'id' => (int)$this->db->insert_id(), 'amended' => false);
	}
}
