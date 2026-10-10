<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sh4_source_model — Smart Hospital 4.0 (QDOCS) source extractor.
 *
 * READ-ONLY adapter over the source database (e.g. `martpoint_sh_src`,
 * restored from isla0987_pashentsoft.sql). Emits the normalized row
 * shapes Imports_model consumes, plus opening-position and document
 * extracts. Every record carries its source id so reruns dedupe and
 * reconciliation can trace targets back to source rows.
 *
 * Connection is a second CI database handle — the write path stays on
 * the default (destination) connection, so this model never writes.
 *
 * Verified against isla0987_pashentsoft.sql (MariaDB 10.6 dump,
 * 167 tables, ~1,472 patients / 15,160 visit_details / 15,793
 * patient_charges / 16,159 transactions / 412 timeline documents).
 */
class Sh4_source_model extends CI_Model {

	/** @var CI_DB second connection to the source database */
	private $src = null;

	/** Connect to the named source database (read-only usage). */
	public function connect($database){
		$this->src = $this->load->database(array(
			'hostname' => $this->db->hostname,
			'username' => $this->db->username,
			'password' => $this->db->password,
			'database' => $database,
			'dbdriver' => 'mysqli',
			'char_set' => 'utf8',
			'dbcollat' => 'utf8_general_ci',
			'db_debug' => FALSE,
		), TRUE);
		return (bool)$this->src;
	}

	private function src(){
		if(!$this->src) throw new Exception('Sh4_source_model: connect() first');
		return $this->src;
	}

	// ------------------------------------------------------------------
	// Source statistics — reconciliation ground truth
	// ------------------------------------------------------------------

	public function stats(){
		$s = $this->src();
		$one = function($q) use ($s){ return (int)$s->query($q)->row()->n; };
		return array(
			'patients'         => $one("SELECT COUNT(*) n FROM patients"),
			'patients_dead'    => $one("SELECT COUNT(*) n FROM patients WHERE is_dead='yes'"),
			'patients_inactive'=> $one("SELECT COUNT(*) n FROM patients WHERE is_active='no'"),
			'opd'              => $one("SELECT COUNT(*) n FROM opd_details"),
			'ipd'              => $one("SELECT COUNT(*) n FROM ipd_details"),
			'visit_details'    => $one("SELECT COUNT(*) n FROM visit_details"),
			'patient_charges'  => $one("SELECT COUNT(*) n FROM patient_charges"),
			'transactions'     => $one("SELECT COUNT(*) n FROM transactions"),
			'timeline'         => $one("SELECT COUNT(*) n FROM patient_timeline"),
			'timeline_docs'    => $one("SELECT COUNT(*) n FROM patient_timeline WHERE document != ''"),
			'discharge_cards'  => $one("SELECT COUNT(*) n FROM discharge_card"),
			'bed_history'      => $one("SELECT COUNT(*) n FROM patient_bed_history"),
			'custom_values'    => $one("SELECT COUNT(*) n FROM custom_field_values"),
			'appointments'     => $one("SELECT COUNT(*) n FROM appointment"),
		);
	}

	// ------------------------------------------------------------------
	// Patients — identities first
	// ------------------------------------------------------------------

	/**
	 * Stream normalized patient rows. `patients.id` is the legacy id.
	 * Patient-level custom fields (belong_to='patient'): WhatsApp
	 * availability/number, Card No, Referred By — carried into
	 * legacy_ids_json + note so nothing is silently dropped.
	 */
	public function patients($afterId = 0, $limit = 200, $asOf = null){
		$s = $this->src();
		if($asOf) $s->where('created_at <', date('Y-m-d',strtotime($asOf.' +1 day')));
		$rows = $s->where('id >', (int)$afterId)->order_by('id')->limit((int)$limit)
			->get('patients')->result_array();
		if(!$rows) return array();

		// Custom field values for this page of patients.
		$ids = array_map(function($r){ return (int)$r['id']; }, $rows);
		$cfv = $s->select('cfv.belong_table_id pid, cf.name, cfv.field_value')
			->from('custom_field_values cfv')
			->join('custom_fields cf', 'cf.id = cfv.custom_field_id')
			->where('cf.belong_to', 'patient')->where_in('cfv.belong_table_id', $ids)
			->get()->result();
		$cfByPid = array();
		foreach($cfv as $c) $cfByPid[(int)$c->pid][trim($c->name)] = trim((string)$c->field_value);

		$out = array();
		foreach($rows as $r){
			$cf = $cfByPid[(int)$r['id']] ?? array();
			$legacy = array('smarthospital4' => (string)$r['id']);
			if(!empty($cf['Card No']))          $legacy['sh4_card_no']    = $cf['Card No'];
			if(!empty($cf['WhatsApp Number']))  $legacy['sh4_whatsapp']   = $cf['WhatsApp Number'];
			if(!empty($cf['Referred By']))      $legacy['sh4_referred_by']= $cf['Referred By'];
			if(($r['identification_number'] ?? '') !== '')
				$legacy['sh4_identification_number'] = $r['identification_number'];

			$notes = array();
			if(($r['note'] ?? '') !== '')            $notes[] = $r['note'];
			if(($r['known_allergies'] ?? '') !== '') $notes[] = 'Allergies: ' . $r['known_allergies'];
			if(!empty($cf['Available on WhatsApp'])) $notes[] = 'Available on WhatsApp';

			$out[] = array(
				'legacy_id'       => (string)$r['id'],
				'name'            => trim((string)$r['patient_name']),
				'phone'           => trim((string)$r['mobileno']),
				'email'           => trim((string)$r['email']),
				'gender'          => trim((string)$r['gender']),
				'dob'             => ($r['dob'] && $r['dob'] !== '0000-00-00') ? $r['dob'] : null,
				'age_hint'        => (int)$r['age'] ?: null, // for dob-less patients
				'address'         => trim((string)$r['address']),
				'marital_status'  => trim((string)$r['marital_status']) ?: null,
				'blood_group'     => trim((string)$r['blood_group']) ?: null,
				'guardian_name'   => trim((string)$r['guardian_name']) ?: null,
				'deceased'        => ($r['is_dead'] ?? 'no') === 'yes' ? 1 : 0,
				'inactive'        => ($r['is_active'] ?? 'yes') === 'no' ? 1 : 0,
				'source_notes'    => implode(' | ', $notes) ?: null,
				'legacy_extra'    => $legacy,
				'source_created'  => $r['created_at'] ?? null,
			);
		}
		return $out;
	}

	// ------------------------------------------------------------------
	// Documents — patient_timeline rows carrying an uploaded file
	// ------------------------------------------------------------------

	/**
	 * Timeline entries: title/date/description/status + `document`
	 * filename. The uploads folder was NOT supplied, so each is a stub
	 * with missing_attachment=1 — never silently dropped.
	 */
	public function timelineDocuments($afterId = 0, $limit = 200){
		$s = $this->src();
		return $s->where('id >', (int)$afterId)->where('document !=', '')
			->order_by('id')->limit((int)$limit)
			->get('patient_timeline')->result_array();
	}

	/** Timeline entries WITHOUT a document — narrative history notes. */
	public function timelineNotes($afterId = 0, $limit = 200){
		$s = $this->src();
		return $s->where('id >', (int)$afterId)
			->group_start()->where('document', '')->or_where('document IS NULL')->group_end()
			->order_by('id')->limit((int)$limit)
			->get('patient_timeline')->result_array();
	}

	// ------------------------------------------------------------------
	// Opening positions — billed minus paid, per patient
	// ------------------------------------------------------------------

	/**
	 * Per-patient financial position at the dump date:
	 *   billed = SUM(patient_charges.amount) via opd_details/ipd_details links
	 *   paid   = SUM(transactions.amount) where type='payment'
	 * Positive net = opening debt; negative net = unused funds.
	 * Zero-net patients are skipped (no position needed).
	 */
	public function openingPositions(){
		$s = $this->src();
		$billed = $s->query(
			"SELECT pid, SUM(amount) billed FROM (
				SELECT od.patient_id pid, pc.amount
				  FROM patient_charges pc JOIN opd_details od ON od.id = pc.opd_id
				UNION ALL
				SELECT id2.patient_id pid, pc.amount
				  FROM patient_charges pc JOIN ipd_details id2 ON id2.id = pc.ipd_id
			) x GROUP BY pid")->result();
		$paid = $s->query(
			"SELECT patient_id pid, SUM(amount) paid FROM transactions
			  WHERE type='payment' GROUP BY patient_id")->result();
		$net = array();
		foreach($billed as $b) $net[(int)$b->pid]['billed'] = (float)$b->billed;
		foreach($paid as $p)   $net[(int)$p->pid]['paid']   = (float)$p->paid;
		return $net; // [src_patient_id => ['billed'=>x,'paid'=>y]]
	}

	/** Unlinked/orphan charges (no opd/ipd owner) — exception reporting. */
	public function orphanChargeCount(){
		$s = $this->src();
		return (int)$s->query(
			"SELECT COUNT(*) n FROM patient_charges pc
			  LEFT JOIN opd_details od ON od.id = pc.opd_id
			  LEFT JOIN ipd_details id2 ON id2.id = pc.ipd_id
			  WHERE od.id IS NULL AND id2.id IS NULL")->row()->n;
	}

	// ------------------------------------------------------------------
	// Clinical history summary (dates + provenance preserved as notes)
	// ------------------------------------------------------------------

	/** Per-patient counts of each clinical record type. */
	public function clinicalSummary($patientIds = null){
		$s = $this->src();
		$q = function($table, $col = 'patient_id') use ($s, $patientIds){
			$s->select("$col pid, COUNT(*) n, MIN(created_at) first, MAX(created_at) last")
				->from($table)->group_by($col);
			if($patientIds) $s->where_in($col, $patientIds);
			return $s->get()->result();
		};
		$out = array();
		foreach($q('opd_details') as $r)        $out[(int)$r->pid]['opd'] = (int)$r->n;
		foreach($q('ipd_details') as $r)        $out[(int)$r->pid]['ipd'] = (int)$r->n;
		foreach($q('patient_timeline') as $r)   $out[(int)$r->pid]['timeline'] = (int)$r->n;
		foreach($q('patient_bed_history') as $r)$out[(int)$r->pid]['beds'] = (int)$r->n;
		return $out;
	}

	/** Discharge cards (discharge/referral/deceased evidence). */
	public function dischargeCards($afterId = 0, $limit = 200){
		$s = $this->src();
		return $s->where('id >', (int)$afterId)->order_by('id')->limit((int)$limit)
			->get('discharge_card')->result_array();
	}

	// ------------------------------------------------------------------
	// Clinical history — narrative/timeline records, read-only.
	//
	// These land as inert history entries on the patient record (no charges,
	// no invoices, no wallet movement). Each reader is paginated by source id
	// so the importer can checkpoint and resume.
	// ------------------------------------------------------------------

	/** Outpatient visits. */
	public function opdVisits($afterId = 0, $limit = 500){
		$s = $this->src();
		return $s->where('id >', (int)$afterId)->order_by('id')->limit((int)$limit)
			->get('opd_details')->result_array();
	}

	/** Inpatient stays — clinical fields only, never the money columns. */
	public function ipdStays($afterId = 0, $limit = 500){
		$s = $this->src();
		return $s->select('id, patient_id, case_reference_id, bed, bed_group_id, case_type, '
				. 'symptoms, known_allergies, note, cons_doctor, date, discharged, created_at')
			->where('id >', (int)$afterId)->order_by('id')->limit((int)$limit)
			->get('ipd_details')->result_array();
	}

	/** Bed history intervals. */
	public function bedHistory($afterId = 0, $limit = 500){
		$s = $this->src();
		return $s->where('id >', (int)$afterId)->order_by('id')->limit((int)$limit)
			->get('patient_bed_history')->result_array();
	}

	/** Counts per history source — used for the reconciliation report. */
	public function historyCounts(){
		$s = $this->src();
		$out = array();
		foreach(array('opd_details', 'ipd_details', 'patient_timeline',
				'patient_bed_history', 'discharge_card') as $t){
			$out[$t] = (int)$s->query("SELECT COUNT(*) n FROM {$t}")->row()->n;
			$s->reset_query();
		}
		return $out;
	}

	/** Whitelisted history tables — guards the table name used in historyPage(). */
	const HISTORY_TABLES = array('opd_details', 'ipd_details', 'patient_timeline',
		'patient_bed_history', 'discharge_card');

	/** True when the source table carries a patient_id column. */
	public function hasColumn($table, $column){
		if(!in_array($table, self::HISTORY_TABLES, true)) return false;
		$q = $this->src()->query("SHOW COLUMNS FROM `{$table}` LIKE " . $this->src()->escape($column));
		$n = $q->num_rows();
		$this->src()->reset_query();
		return $n > 0;
	}

	/** One page of a history table, ordered by source id (checkpoint friendly). */
	public function historyPage($table, $afterId = 0, $limit = 500){
		if(!in_array($table, self::HISTORY_TABLES, true)) return array();
		$s = $this->src();
		$rows = $s->where('id >', (int)$afterId)->order_by('id')->limit((int)$limit)
			->get($table)->result_array();
		$s->reset_query();
		return $rows;
	}

	/** Single lookup — discharge cards resolve their patient through these. */
	public function ipdStaysById($id){
		if(empty($id)) return null;
		$s = $this->src();
		$row = $s->select('id, patient_id')->where('id', (int)$id)->get('ipd_details')->row_array();
		$s->reset_query();
		return $row;
	}

	public function opdVisitById($id){
		if(empty($id)) return null;
		$s = $this->src();
		$row = $s->select('id, patient_id')->where('id', (int)$id)->get('opd_details')->row_array();
		$s->reset_query();
		return $row;
	}

	/**
	 * Resolve the owning patient for a case reference. Bed history and
	 * discharge cards link by case, not by patient.
	 */
	public function patientForCase($caseRef){
		$caseRef = (int)$caseRef;
		if(!$caseRef) return null;
		$s = $this->src();
		$row = $s->select('patient_id')->where('case_reference_id', $caseRef)
			->order_by('id')->limit(1)->get('ipd_details')->row_array();
		if(!$row){
			$row = $s->select('patient_id')->where('case_reference_id', $caseRef)
				->order_by('id')->limit(1)->get('opd_details')->row_array();
		}
		$s->reset_query();
		return $row;
	}
}
