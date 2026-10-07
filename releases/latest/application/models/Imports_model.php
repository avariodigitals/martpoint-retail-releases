<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Imports_model — Stage 7 legacy import framework.
 *
 * SOURCE STATUS: the Smart Hospital 4.0 SQL dump is not yet available, so
 * concrete source-table extraction is pending. This model provides the
 * repeatable, resumable, rollbackable chassis; source-specific extractors
 * feed it normalized row arrays (see importPatient() for the contract).
 *
 * Mechanics:
 *  - db_migration_batches: batch lifecycle + counters + checkpoint cursor.
 *  - db_migration_rows: one row per source record, dedupe-keyed on
 *    (store, source_table, source_id) ACROSS batches — a rerun detects
 *    what a previous batch already imported and skips it.
 *  - targets_json records every target row a source row created, so
 *    rollback() deletes them in reverse insert order.
 *  - Shared phone/identity collisions are recorded as 'conflict' rows
 *    with dupe_hint — NEVER auto-merged; they surface for manual review.
 */
class Imports_model extends CI_Model {

	/** Target tables a batch may create rows in (rollback whitelist). */
	const ROLLBACKABLE = array('db_patients', 'db_customers', 'db_patient_documents',
		'db_customer_notes', 'db_opening_positions', 'db_opening_position_items',
		'db_document_versions', 'db_patient_events');

	public function __construct(){ parent::__construct(); }

	private function _store(){ return (int)get_current_store_id(); }

	// ------------------------------------------------------------------
	// Batch lifecycle
	// ------------------------------------------------------------------

	public function startBatch($sourceSystem, $label = '', $mode = 'import'){
		$this->db->insert('db_migration_batches', array(
			'store_id'       => $this->_store(),
			'source_system'  => substr((string)$sourceSystem, 0, 40),
			'label'          => substr((string)$label, 0, 160) ?: null,
			'mode'           => $mode === 'dry_run' ? 'dry_run' : 'import',
			'status'         => 'running',
			'started_by'     => (int)$this->session->userdata('inv_userid') ?: null,
			'started_by_name'=> $this->session->userdata('inv_username') ?: 'system',
			'started_at'     => date('Y-m-d H:i:s'),
			'created_date'   => date('Y-m-d'), 'created_time' => date('H:i:s'),
			'created_by'     => $this->session->userdata('inv_username') ?: 'system',
		));
		return (int)$this->db->insert_id();
	}

	public function batch($id){
		return $this->db->where('id', (int)$id)->where('store_id', $this->_store())
			->get('db_migration_batches')->row();
	}

	public function batches($limit = 100){
		return $this->db->where('store_id', $this->_store())
			->order_by('id', 'desc')->limit((int)$limit)->get('db_migration_batches')->result();
	}

	public function batchRows($batchId, $limit = 500){
		return $this->db->where('batch_id', (int)$batchId)->where('store_id', $this->_store())
			->order_by('id')->limit((int)$limit)->get('db_migration_rows')->result();
	}

	/** Persist a resumable cursor (e.g. last processed source id). */
	public function checkpoint($batchId, array $cursor){
		$this->db->where('id', (int)$batchId)->where('store_id', $this->_store())
			->update('db_migration_batches', array('checkpoint_json' => json_encode($cursor)));
	}

	public function getCheckpoint($batchId){
		$b = $this->batch($batchId);
		return $b && $b->checkpoint_json ? (json_decode($b->checkpoint_json, true) ?: array()) : array();
	}

	/** A paused batch resumes exactly where its checkpoint left off. */
	public function resumeBatch($batchId){
		$this->db->where('id', (int)$batchId)->where('store_id', $this->_store())
			->where('status', 'paused')
			->update('db_migration_batches', array('status' => 'running', 'finished_at' => null));
		return $this->getCheckpoint($batchId);
	}

	public function pauseBatch($batchId){
		$this->db->where('id', (int)$batchId)->where('store_id', $this->_store())
			->where('status', 'running')
			->update('db_migration_batches', array('status' => 'paused'));
	}

	public function finishBatch($batchId){
		$b = $this->batch($batchId);
		if(!$b) return;
		$status = ((int)$b->rows_failed > 0 || (int)$b->rows_conflicts > 0)
			? 'completed_with_exceptions' : 'completed';
		$this->db->where('id', $b->id)->update('db_migration_batches', array(
			'status' => $status, 'finished_at' => date('Y-m-d H:i:s'),
		));
	}

	// ------------------------------------------------------------------
	// Row ledger — dedupe across batches is the rerun-safety mechanism
	// ------------------------------------------------------------------

	/**
	 * Has this source record already been imported (by ANY batch)?
	 * Returns the ledger row or null. 'rolled_back' rows do not count —
	 * a rolled-back record may legitimately be re-imported.
	 */
	public function seen($sourceTable, $sourceId){
		return $this->db->select('r.*')->from('db_migration_rows r')
			->join('db_migration_batches b', 'b.id = r.batch_id')
			->where('r.store_id', $this->_store())
			->where('b.mode', 'import')
			->where('r.source_table', (string)$sourceTable)
			->where('r.source_id', (string)$sourceId)
			->where_in('r.action', array('inserted','skipped','conflict'))
			->get()->row();
	}

	/** Record a ledger row and bump the batch counter atomically. */
	public function record($batchId, $sourceTable, $sourceId, $action, $targets = array(), $hint = null, $error = null){
		$this->db->trans_begin();
		$this->db->insert('db_migration_rows', array(
			'batch_id'     => (int)$batchId,
			'store_id'     => $this->_store(),
			'source_table' => (string)$sourceTable,
			'source_id'    => (string)$sourceId,
			'action'       => $action,
			'targets_json' => $targets ? json_encode($targets) : null,
			'dupe_hint'    => $hint ? substr((string)$hint, 0, 160) : null,
			'error'        => $error ? substr((string)$error, 0, 255) : null,
			'created_at'   => date('Y-m-d H:i:s'),
		));
		$rowId = (int)$this->db->insert_id();
		if(!$rowId){ $this->db->trans_rollback(); return 0; }
		$counter = array(
			'inserted' => 'rows_imported', 'skipped' => 'rows_skipped',
			'conflict' => 'rows_conflicts', 'failed' => 'rows_failed',
		);
		$col = $counter[$action] ?? null;
		$this->db->set('rows_seen', 'rows_seen+1', false);
		if($col) $this->db->set($col, $col . '+1', false);
		$this->db->where('id', (int)$batchId)->update('db_migration_batches');
		$this->db->trans_commit();
		return $rowId;
	}

	// ------------------------------------------------------------------
	// Rollback — delete only what this batch created, newest first
	// ------------------------------------------------------------------

	public function rollback($batchId){
		$storeId = $this->_store();
		$batch = $this->batch($batchId);
		if(!$batch) return array('ok' => false, 'error' => 'Batch not found');
		if($batch->status === 'rolled_back') return array('ok' => true, 'replayed' => true);
		if($batch->mode === 'dry_run') return array('ok' => false, 'error' => 'Dry-run batches write nothing to roll back');

		// 'conflict' rows also created targets — flagging a dupe never skips
		// the insert, so rollback must remove them too.
		$rows = $this->db->where('batch_id', (int)$batchId)->where('store_id', $storeId)
			->where_in('action', array('inserted','conflict'))->order_by('id', 'desc')
			->get('db_migration_rows')->result();

		$deleted = 0; $protected = 0;
		// Capture which document each version belonged to BEFORE deleting, so
		// document state can be repaired afterwards. An attachment batch
		// targets only version rows, so the owning document is not otherwise
		// discoverable from its targets.
		$docParents = array();
		foreach($rows as $r){
			$targets = json_decode($r->targets_json, true) ?: array();
			foreach($targets as $t){
				if(($t['table'] ?? '') !== 'db_document_versions' || empty($t['id'])) continue;
				$owner = $this->db->select('document_id')->where('id', (int)$t['id'])
					->where('store_id', $storeId)->get('db_document_versions')->row();
				if($owner && (int)$owner->document_id){
					$docParents[(int)$owner->document_id] = true;
				}
			}
		}
		$this->db->trans_begin();
		foreach($rows as $r){
			$targets = json_decode($r->targets_json, true) ?: array();
			// Protection: an imported identity that has since accrued real
			// clinical/financial records is NOT deleted — that would orphan
			// bills, wallet history and clinical data. The row is marked
			// protected and reported for manual cleanup instead.
			if($this->rowProtected($targets, $storeId)){
				$protected++;
				$this->db->where('id', $r->id)->update('db_migration_rows', array(
					'dupe_hint' => 'protected: downstream records exist — manual cleanup',
				));
				continue;
			}
			foreach(array_reverse($targets) as $t){
				$table = $t['table'] ?? '';
				$tid   = (int)($t['id'] ?? 0);
				if(!in_array($table, self::ROLLBACKABLE) || !$tid) continue;
				// Only delete what still exists and belongs to this store.
				$this->db->where('id', $tid)->where('store_id', $storeId)->delete($table);
				if($this->db->affected_rows() > 0) $deleted++;
			}
			$this->db->where('id', $r->id)->update('db_migration_rows', array(
				'action' => 'rolled_back', 'rolled_back_at' => date('Y-m-d H:i:s'),
			));
		}
		$this->db->where('id', $batch->id)->update('db_migration_batches', array(
			'status' => $protected ? 'completed_with_exceptions' : 'rolled_back',
			'rolled_back_at' => date('Y-m-d H:i:s'),
			'rolled_back_by' => (int)$this->session->userdata('inv_userid') ?: null,
		));
		$this->db->trans_commit();
		$this->repairDocumentState($storeId, $rows, array_keys($docParents));
		return array('ok' => true, 'deleted' => $deleted, 'protected' => $protected, 'batch_id' => (int)$batchId);
	}

	/**
	 * Restore document consistency after a rollback removed version rows.
	 *
	 * Two states are invalid and both are caused by deleting a version the
	 * document still points at:
	 *   - current_version_id pointing at a deleted version → the document
	 *     claims an attachment that no longer exists on disk;
	 *   - no versions left at all → an imported stub that never had a file.
	 * Both are returned to 'missing_attachment' (empty current_version_id), so
	 * the document cannot be downloaded, released or shown in the portal. A
	 * document that still has versions keeps whatever status it had.
	 */
	private function repairDocumentState($storeId, array $rows, array $extraDocIds = array()){
		$docIds = $extraDocIds;
		foreach($rows as $r){
			$targets = json_decode($r->targets_json, true) ?: array();
			foreach($targets as $t){
				if(($t['table'] ?? '') === 'db_patient_documents' && !empty($t['id'])){
					$docIds[] = (int)$t['id'];
				}
			}
		}
		$docIds = array_unique(array_filter($docIds));
		if(empty($docIds) || !$this->db->table_exists('db_document_versions')) return;

		foreach($docIds as $docId){
			if(!$this->db->where('id', $docId)->where('store_id', $storeId)->count_all_results('db_patient_documents')){
				continue; // the document itself was deleted
			}
			$live = $this->db->select('id')->where('document_id', $docId)
				->where('store_id', $storeId)->order_by('version_no', 'desc')->limit(1)
				->get('db_document_versions')->row();
			if($live){
				// Keep the pointer on a version that still exists.
				$this->db->where('id', $docId)->where('store_id', $storeId)
					->update('db_patient_documents', array('current_version_id' => (int)$live->id));
			} else {
				$this->db->where('id', $docId)->where('store_id', $storeId)
					->update('db_patient_documents', array(
						'current_version_id' => null,
						'status'             => 'missing_attachment',
						'released_to_patient' => 0,
					));
			}
		}
	}

	/**
	 * Rollback protection: a target may not be deleted once downstream
	 * business records reference it — sales, wallet txns, sessions,
	 * appointments, admissions, opening positions, portal accounts.
	 */
	/** Source tables whose ledger rows a history import owns. */
	const HISTORY_SOURCES = array('opd_details', 'ipd_details', 'patient_timeline',
		'patient_bed_history', 'discharge_card');

	private function rowProtected(array $targets, $storeId){
		foreach($targets as $t){
			if(($t['table'] ?? '') !== 'db_patients') continue;
			$pid = (int)($t['id'] ?? 0);
			if(!$pid) continue;
			// Financial AND clinical records protect the identity — imported
			// patients are not deleted once real activity references them.
			foreach(array('db_sales'=>'patient via customer','db_appointments'=>'patient_id',
					'db_treatment_sessions'=>'patient_id','db_admissions'=>'patient_id',
					'db_opening_positions'=>'patient_id','db_patient_portal_users'=>'patient_id',
					'db_encounters'=>'patient_id','db_assessments'=>'patient_id',
					'db_investigations'=>'patient_id','db_patient_documents'=>'patient_id',
					'db_consents'=>'patient_id','db_patient_events'=>'patient_id') as $tbl => $col){
				if(!$this->db->table_exists($tbl)) continue;
				if($tbl === 'db_sales'){
					$cust = $this->db->select('customer_id')->where('id',$pid)->where('store_id',$storeId)
						->get('db_patients')->row();
					$n = $cust ? $this->db->where('customer_id',(int)$cust->customer_id)
						->where('store_id',$storeId)->count_all_results('db_sales') : 0;
				} else {
					$n = $this->db->where($col,$pid)->where('store_id',$storeId)->count_all_results($tbl);
				}
				if($n > 0) return true;
			}
			// Wallet txns
			if($this->db->table_exists('db_patient_wallet_txns')
				&& $this->db->where('patient_id',$pid)->where('store_id',$storeId)
					->count_all_results('db_patient_wallet_txns') > 0) return true;
		}
		return false;
	}

	// ------------------------------------------------------------------
	// Reconciliation — compare what the batch produced vs what was entered
	// ------------------------------------------------------------------

	public function reconciliation($batchId){
		$storeId = $this->_store();
		$rows = $this->db->where('batch_id', (int)$batchId)->where('store_id', $storeId)
			->get('db_migration_rows')->result();
		$byAction = array('inserted'=>0,'skipped'=>0,'conflict'=>0,'failed'=>0,'rolled_back'=>0);
		$patients = 0; $conflicts = array();
		foreach($rows as $r){
			$byAction[$r->action] = ($byAction[$r->action] ?? 0) + 1;
			if($r->action === 'inserted'){
				$targets = json_decode($r->targets_json, true) ?: array();
				foreach($targets as $t) if(($t['table'] ?? '') === 'db_patients') $patients++;
			}
			if($r->action === 'conflict') $conflicts[] = $r;
		}
		// Opening positions for patients this batch created.
		$patientIds = array();
		foreach($rows as $r){
			foreach((json_decode($r->targets_json, true) ?: array()) as $t){
				if(($t['table'] ?? '') === 'db_patients') $patientIds[] = (int)$t['id'];
			}
		}
		$positions = array('debt'=>0.0,'funds'=>0.0,'units'=>0.0,'pending'=>0);
		if($patientIds){
			$ops = $this->db->where_in('patient_id', $patientIds)->where('store_id', $storeId)
				->get('db_opening_positions')->result();
			foreach($ops as $o){
				if(!in_array($o->status, array('approved'))){
					$positions['pending']++;
				} else {
					foreach($this->db->where('position_id',$o->id)->where('status','approved')
						->get('db_opening_position_items')->result() as $it){
						if($it->item_type === 'outstanding_debt') $positions['debt'] += (float)$it->amount;
						if(in_array($it->item_type, array('unused_money','wallet_balance'))) $positions['funds'] += (float)$it->amount;
						$p = json_decode($it->payload_json, true) ?: array();
						if($it->item_type === 'unused_sessions' && isset($p['units_total'])) $positions['units'] += (float)$p['units_total'];
					}
				}
			}
		}
		return array(
			'by_action' => $byAction, 'patients_created' => $patients,
			'conflicts' => $conflicts, 'opening' => $positions,
		);
	}

	/** Store-wide reconciliation across all batches. */
	public function storeReconciliation(){
		$storeId = $this->_store();
		// Row scan — JSON_TABLE is not portable across supported MariaDB versions.
		$importedN = 0;
		foreach($this->db->where('store_id',$storeId)->where('action','inserted')
			->get('db_migration_rows')->result() as $r){
			foreach((json_decode($r->targets_json,true) ?: array()) as $t){
				if(($t['table'] ?? '')==='db_patients'){ $importedN++; break; }
			}
		}
		$pending = $this->db->where('store_id', $storeId)
			->where_in('status', array('entered','submitted','reviewed'))
			->count_all_results('db_opening_positions');
		return array(
			'patients_imported'  => $importedN,
			'positions_pending'  => (int)$pending,
			'positions_approved' => (int)$this->db->where('store_id',$storeId)->where('status','approved')->count_all_results('db_opening_positions'),
		);
	}

	// ------------------------------------------------------------------
	// Identity writer — normalized patient contract (source extractor pending)
	// ------------------------------------------------------------------

	/**
	 * Import one legacy patient identity. Accepts a NORMALIZED row — the
	 * Smart Hospital extractor (pending source SQL) produces this shape:
	 *   legacy_id (required), name, phone, email, gender, dob (Y-m-d),
	 *   address, deceased (0/1), deceased_date, deceased_notes, inactive (0/1)
	 * Rules: preserve legacy id in legacy_ids_json, preserve deceased/
	 * inactive status, NEVER auto-merge on shared phone (record conflict
	 * and still insert — review decides), NEVER send a portal invitation.
	 */
	public function importPatient($batchId, array $row){
		$storeId = $this->_store();
		$src = 'patients'; // source table label; extractor sets real name
		$sid = trim((string)($row['legacy_id'] ?? ''));
		if($sid === '') return array('ok' => false, 'error' => 'Row has no legacy_id');
		if(empty($row['name'])) return array('ok' => false, 'error' => 'Row has no name');

		if($this->seen($src, $sid)){
			$this->record($batchId, $src, $sid, 'skipped', array(), 'already imported');
			return array('ok' => true, 'skipped' => true);
		}

		$phone = trim((string)($row['phone'] ?? ''));
		$dupeHint = null;
		if($phone !== ''){
			$dupe = $this->db->select('p.id')->from('db_patients p')
				->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id')
				->where('p.store_id', $storeId)->where('c.mobile', $phone)->limit(1)
				->get()->row();
			if($dupe) $dupeHint = 'shared phone ' . $phone . ' with patient #' . $dupe->id;
		}

		$this->db->trans_begin();
		// Customer shell (the retail identity the patient links to).
		$this->db->insert('db_customers', array(
			'store_id'      => $storeId,
			'customer_name' => substr(trim($row['name']), 0, 150),
			'mobile'        => $phone ?: null,
			'email'         => trim((string)($row['email'] ?? '')) ?: null,
			'address'       => trim((string)($row['address'] ?? '')) ?: null,
			'created_date'  => date('Y-m-d'), 'created_time' => date('H:i:s'),
			'created_by'    => 'import:' . $batchId, 'status' => 1,
			'system_ip'     => 'import', 'system_name' => 'migration',
		));
		$customerId = (int)$this->db->insert_id();
		if(!$customerId){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Customer insert failed'); }

		$legacy = array('smarthospital4' => $sid);
		foreach((array)($row['legacy_extra'] ?? array()) as $k => $v){
			if($v !== '' && $v !== null) $legacy[$k] = $v;
		}
		$dob = !empty($row['dob']) ? substr($row['dob'], 0, 10) : null;
		$notes = trim((string)($row['source_notes'] ?? ''));
		if(!$dob && !empty($row['age_hint']))
			$notes = trim(($notes ? $notes . ' | ' : '') . 'Source age at registration: ' . (int)$row['age_hint']);

		$this->db->insert('db_patients', array(
			'store_id'       => $storeId,
			'customer_id'    => $customerId,
			'gender'         => trim((string)($row['gender'] ?? '')) ?: null,
			'dob'            => $dob,
			'marital_status' => trim((string)($row['marital_status'] ?? '')) ?: null,
			'blood_group'    => trim((string)($row['blood_group'] ?? '')) ?: null,
			'nok_name'       => trim((string)($row['guardian_name'] ?? '')) ?: null,
			'legacy_ids_json'=> json_encode($legacy),
			'portal_status'  => 'none',  // no auto-invite — ever
			'deceased'       => !empty($row['deceased']) ? 1 : 0,
			'deceased_date'  => !empty($row['deceased_date']) ? substr($row['deceased_date'], 0, 10) : null,
			'deceased_notes' => trim((string)($row['deceased_notes'] ?? '')) ?: null,
			'status'         => !empty($row['inactive']) ? 0 : 1,
			'created_date'   => date('Y-m-d'), 'created_time' => date('H:i:s'),
			'created_by'     => 'import:' . $batchId,
			'system_ip'      => 'import', 'system_name' => 'migration',
		));
		$patientId = (int)$this->db->insert_id();
		if(!$patientId){ $this->db->trans_rollback(); return array('ok' => false, 'error' => 'Patient insert failed'); }
		// Patient code from the auto-increment id — same convention as
		// Patients_model::savePatient.
		$this->db->where('id', $patientId)->update('db_patients', array(
			'patient_code' => 'PT-' . str_pad($patientId, 5, '0', STR_PAD_LEFT),
		));
		$targets = array(
			array('table' => 'db_customers', 'id' => $customerId),
			array('table' => 'db_patients', 'id' => $patientId),
		);
		// General source notes (allergies, WhatsApp, age-at-registration)
		// live on the customer notes trail — never in deceased_notes.
		if($notes !== ''){
			$this->db->insert('db_customer_notes', array(
				'store_id' => $storeId, 'customer_id' => $customerId,
				'note' => '[SH4 #' . $sid . '] ' . substr($notes, 0, 4000),
				'created_by' => 'import:' . $batchId,
				'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
				'created_at' => date('Y-m-d H:i:s'),
			));
			$noteId = (int)$this->db->insert_id();
			if($noteId) $targets[] = array('table' => 'db_customer_notes', 'id' => $noteId);
		}

		$action = $dupeHint ? 'conflict' : 'inserted';
		$this->record($batchId, $src, $sid, $action, $targets, $dupeHint);
		$this->db->trans_commit();
		return array('ok' => true, 'patient_id' => $patientId, 'customer_id' => $customerId,
			'conflict' => (bool)$dupeHint);
	}
}
