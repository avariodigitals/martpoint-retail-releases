<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sh4_import — Smart Hospital 4.0 → MartPoint migration runner (CLI only).
 *
 *   php index.php sh4_import run        [patients → positions → documents]
 *   php index.php sh4_import reconcile  [source-vs-destination report]
 *   php index.php sh4_import positions  [re-stage opening positions only]
 *
 * Environment:
 *   MP_DB    destination database (default: app DB). Rehearsals run
 *            against an isolated copy:  MP_DB=martpoint_rehearsal php index.php sh4_import run
 *   SH4_DB   source database containing the restored SH4 dump
 *            (default: martpoint_sh_src)
 *   MP_STORE destination store id (default: 3)
 *   SH4_ASOF opening-position "as of" date (default: max source created_at)
 *
 * Every source row lands in db_migration_rows keyed by source id, so runs
 * are repeatable (dedupe across batches), resumable (checkpoint cursor),
 * and rollbackable. Opening positions are staged 'entered' — review +
 * approval post them; nothing here touches sales/cash/revenue.
 */
class Sh4_import extends CI_Controller {

	/** Ledger source key for attachment batches (kept separate from staging). */
	const ATTACH_SOURCE = 'patient_timeline_attachment';

	private $storeId;
	private $srcDb;
	private $asOf;

	public function __construct(){
		parent::__construct();
		if(!is_cli()){ show_404(); return; }
		$this->storeId = (int)(getenv('MP_STORE') ?: 3);
		$this->srcDb   = getenv('SH4_DB') ?: 'martpoint_sh_src';
		foreach(array('imports_model','sh4_source_model','patient_funds_model') as $m){
			$this->load->model($m);
		}
		// CLI has no login — attribute writes to a migration actor on the store.
		$this->session->set_userdata(array(
			'inv_userid' => 0, 'inv_username' => 'migration:sh4',
			'role_id' => 0, 'store_id' => $this->storeId,
		));
		if(!$this->sh4_source_model->connect($this->srcDb)){
			fwrite(STDERR, "Cannot connect to source DB {$this->srcDb}\n"); exit(2);
		}
		$this->asOf = getenv('SH4_ASOF')
			?: substr($this->db->query("SELECT MAX(created_at) mx FROM {$this->srcDb}.patients")->row()->mx, 0, 10);
	}

	public function run(){
		$this->importPatients();
		$this->stagePositions();
		$this->stageDocuments();
		echo "\nDone. Reconcile with: MP_DB=" . $this->db->database . " php index.php sh4_import reconcile\n";
	}

	/** Roll back a migration batch (deletes its targets in reverse order). */
	/** Patient-only cutover: never stage balances, documents or visits. */
	public function patients_only(){
		if(!getenv('MP_DB') || !getenv('MP_STORE') || getenv('SH4_ASOF')!=='2026-10-02'){
			fwrite(STDERR,"Set explicit MP_DB, MP_STORE and SH4_ASOF=2026-10-02 for the approved snapshot.\n"); exit(2);
		}
		if(!function_exists('physio_enabled')) $this->load->helper('physio');
		if(!physio_enabled($this->storeId)){ fwrite(STDERR,"Target must be a Physio workspace.\n"); exit(2); }
		$this->importPatients();
	}
	public function export_patients($path = ''){
		$path=getenv('SH4_EXPORT_PATH') ?: $path;
		if(getenv('SH4_ASOF')!=='2026-10-02' || !$path){ fwrite(STDERR,"Supply SH4_ASOF=2026-10-02 and an output path.\n"); exit(2); }
		$directory=realpath(dirname($path));$webRoot=realpath(FCPATH);
		if(!$directory || $directory===$webRoot || strpos($directory,$webRoot.DIRECTORY_SEPARATOR)===0){ fwrite(STDERR,"Patient extract must be stored outside the web root.\n"); exit(2); }
		$rows=array();$after=0;
		do {
			$page=$this->sh4_source_model->patients($after,200,'2026-10-02');
			foreach($page as $r){$rows[]=$r;$after=(int)$r['legacy_id'];}
		} while($page);
		$payload=array('source_system'=>'smarthospital4','snapshot_date'=>'2026-10-02','scope'=>'patients_only','patients'=>$rows);
		$json=json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		umask(0077);
		if($json===false || file_put_contents($path,$json)===false){ fwrite(STDERR,"Export failed.\n"); exit(2); }
		chmod($path,0600);
		echo count($rows)." patients exported; no financial or admission records.\n";
	}

	/** Roll back a migration batch (deletes its targets in reverse order). */
	public function rollback($batchId = 0){
		$batchId = (int)$batchId;
		$res = $this->imports_model->rollback($batchId);
		echo "rollback batch #{$batchId}: " . json_encode($res) . "\n";
	}

	// ------------------------------------------------------------------
	public function importPatients(){
		echo "== PATIENTS ({$this->srcDb} -> {$this->db->database}, store {$this->storeId}) ==\n";
		$batchId = $this->imports_model->startBatch('smarthospital4', 'SH4 patients ' . date('Y-m-d'));
		echo "batch #{$batchId}\n";
		$after = 0; $ok = 0; $skip = 0; $conflict = 0; $fail = 0;
		while(true){
			$rows = $this->sh4_source_model->patients($after, 200, $this->asOf);
			if(!$rows) break;
			foreach($rows as $r){
				$after = (int)$r['legacy_id'];
				$res = $this->imports_model->importPatient($batchId, $r);
				if(!empty($res['skipped'])) $skip++;
				elseif(!empty($res['conflict'])) $conflict++;
				elseif(!empty($res['ok'])) $ok++;
				else { $fail++; echo "  FAIL sh#{$r['legacy_id']}: {$res['error']}\n"; }
			}
			$this->imports_model->checkpoint($batchId, array('last_patient_id' => $after));
			echo "  ...through sh#{$after} (ok=$ok skip=$skip conflict=$conflict fail=$fail)\n";
		}
		$this->imports_model->finishBatch($batchId);
		echo "patients: inserted=$ok skipped=$skip conflicts=$conflict failed=$fail\n\n";
	}

	// ------------------------------------------------------------------
	public function stagePositions(){
		echo "== OPENING POSITIONS (as of {$this->asOf}) ==\n";
		$src = $this->srcDb;
		$batchId = $this->imports_model->startBatch('smarthospital4', 'SH4 opening positions ' . $this->asOf);
		$net = $this->sh4_source_model->openingPositions();
		$orphans = $this->sh4_source_model->orphanChargeCount();
		if($orphans) echo "  exception: $orphans patient_charges rows have no opd/ipd owner\n";
		$staged = 0; $skip = 0; $debt = 0; $credit = 0;
		foreach($net as $srcPid => $v){
			$billed = (float)($v['billed'] ?? 0); $paid = (float)($v['paid'] ?? 0);
			$balance = round($billed - $paid, 2);
			if(abs($balance) < 0.005){ continue; }
			// Map source patient → imported patient.
			$pid = $this->patientIdForSource($srcPid);
			if(!$pid){
				$skip++;
				$this->imports_model->record($batchId, 'opening_position', $srcPid, 'failed',
					array(), null, 'source patient not imported');
				continue;
			}
			$sid = 'pos:' . $srcPid;
			if($this->imports_model->seen('opening_position', $sid)){
				$skip++; continue;
			}
			$type = $balance > 0 ? 'outstanding_debt' : 'unused_money';
			$res = $this->patient_funds_model->enterOpeningItem($pid, $type, array(
				'cutoff_date' => $this->asOf,
				'source'      => 'smarthospital4',
				'source_patient_id' => $srcPid,
				'sh4_billed'  => $billed,
				'sh4_paid'    => $paid,
				'evidence'    => "SH4 reconciliation: billed {$billed} - paid {$paid} = {$balance} as of {$this->asOf}",
			), abs($balance), 'Migrated from Smart Hospital 4.0 — pending review');
			if(!empty($res['ok'])){
				$staged++;
				$balance > 0 ? $debt += $balance : $credit += abs($balance);
				$pos = $this->db->where('patient_id', $pid)->where('store_id', $this->storeId)
					->get('db_opening_positions')->row();
				$this->imports_model->record($batchId, 'opening_position', $sid, 'inserted',
					array(
						array('table' => 'db_opening_positions', 'id' => (int)$pos->id),
						array('table' => 'db_opening_position_items', 'id' => (int)$res['item_id']),
					));
			} else {
				$this->imports_model->record($batchId, 'opening_position', $sid, 'failed', array(), null, $res['error'] ?? '?');
			}
		}
		$this->imports_model->finishBatch($batchId);
		echo "positions staged (entered, awaiting review): $staged  debt=₦" . number_format($debt,2)
			. "  funds=₦" . number_format($credit,2) . "  skipped=$skip\n\n";
	}

	// ------------------------------------------------------------------
	public function stageDocuments(){
		echo "== TIMELINE DOCUMENT STUBS ==\n";
		$batchId = $this->imports_model->startBatch('smarthospital4', 'SH4 timeline documents');
		$after = 0; $staged = 0; $skip = 0;
		while(true){
			$rows = $this->sh4_source_model->timelineDocuments($after, 200);
			if(!$rows) break;
			foreach($rows as $r){
				$after = (int)$r['id'];
				$sid = 'tl:' . $r['id'];
				if($this->imports_model->seen('patient_timeline', $sid)){ $skip++; continue; }
				$pid = $this->patientIdForSource($r['patient_id']);
				if(!$pid){
					$this->imports_model->record($batchId, 'patient_timeline', $sid, 'failed',
						array(), null, 'source patient not imported');
					continue;
				}
				// Document stub — the uploads folder was not supplied, so
				// the file itself is missing; metadata + provenance kept.
				// Empty file_path + 'missing_attachment' hint marks the gap.
				$title = '[SH4 #' . $r['id'] . '] ' . trim($r['title']);
				if(trim((string)$r['description']) !== '')
					$title .= ' — ' . trim($r['description']);
				$this->db->insert('db_patient_documents', array(
					'store_id'   => $this->storeId,
					'patient_id' => $pid,
					'category'   => 'clinical',
					'title'      => substr($title, 0, 255),
					'status'     => 'missing_attachment',
					'created_date' => substr($r['date'], 0, 10) ?: date('Y-m-d'),
					'created_time' => date('H:i:s'),
					'created_by' => 'import:sh4',
					'system_ip'  => 'import', 'system_name' => 'migration',
				));
				$docId = (int)$this->db->insert_id();
				if($docId){
					$this->db->insert('db_document_versions', array(
						'store_id' => $this->storeId, 'document_id' => $docId,
						'version_no' => 1, 'file_path' => '',
						'file_hash' => null, 'file_size' => null, 'mime' => null,
						'uploaded_by' => 'import:sh4', 'created_at' => date('Y-m-d H:i:s'),
					));
					$verId = (int)$this->db->insert_id();
					$this->db->where('id', $docId)->update('db_patient_documents',
						array('current_version_id' => $verId));
					$staged++;
					$this->imports_model->record($batchId, 'patient_timeline', $sid, 'inserted',
						array(
							array('table' => 'db_patient_documents', 'id' => $docId),
							array('table' => 'db_document_versions', 'id' => $verId),
						),
						'missing attachment: ' . $r['document']);
				} else {
					$this->imports_model->record($batchId, 'patient_timeline', $sid, 'failed', array(), null, 'insert failed');
				}
			}
			$this->imports_model->checkpoint($batchId, array('last_timeline_id' => $after));
		}
		$this->imports_model->finishBatch($batchId);
		echo "document stubs staged=$staged skipped=$skip (all missing-attachment until uploads folder supplied)\n\n";
	}

	// ------------------------------------------------------------------
	public function reconcile(){
		$s = $this->sh4_source_model->stats();
		$d = $this->db->database;
		echo "== RECONCILIATION  {$this->srcDb} -> {$d} (store {$this->storeId}) ==\n";

		// Numeric source ids = real SH4 rows (synthetic fixtures used non-numeric ids).
		$imported = $this->db->query(
			"SELECT COUNT(DISTINCT r.source_id) n FROM db_migration_rows r
			  JOIN db_migration_batches b ON b.id = r.batch_id
			  WHERE r.store_id = ? AND b.mode='import' AND r.source_table='patients'
			    AND r.source_id REGEXP '^[0-9]+$'
			    AND r.action IN ('inserted','conflict')", [$this->storeId])->row()->n;
		// Patients carrying a numeric smarthospital4 legacy id.
		$sh4Patient = "p.store_id=? AND p.legacy_ids_json REGEXP '\"smarthospital4\":\"[0-9]+\"'";
		$importedAlive = $this->db->query(
			"SELECT COUNT(*) n FROM db_patients p WHERE $sh4Patient", [$this->storeId])->row()->n;
		printf("patients: source=%d imported(distinct sid)=%d db_patients rows=%d\n",
			$s['patients'], $imported, $importedAlive);
		printf("  deceased: source=%d imported=%d\n", $s['patients_dead'],
			$this->db->query("SELECT COUNT(*) n FROM db_patients p WHERE $sh4Patient AND p.deceased=1",
				[$this->storeId])->row()->n);
		printf("  inactive: source=%d imported=%d\n", $s['patients_inactive'],
			$this->db->query("SELECT COUNT(*) n FROM db_patients p WHERE $sh4Patient AND p.status=0",
				[$this->storeId])->row()->n);

		// Opening positions
		$pos = $this->db->query(
			"SELECT o.status, i.item_type, COUNT(*) n, SUM(i.amount) amt
			  FROM db_opening_positions o JOIN db_opening_position_items i ON i.position_id = o.id
			  WHERE o.store_id=? AND i.payload_json LIKE '%\"source\":\"smarthospital4\"%'
			  GROUP BY o.status, i.item_type",
			[$this->storeId])->result();
		$net = $this->sh4_source_model->openingPositions();
		$srcDebt = 0; $srcCredit = 0; $srcN = 0;
		foreach($net as $v){
			$b = ($v['billed'] ?? 0) - ($v['paid'] ?? 0);
			if(abs($b) >= 0.005){ $srcN++; $b > 0 ? $srcDebt += $b : $srcCredit += abs($b); }
		}
		printf("opening positions: source non-zero=%d (debt ₦%s / funds ₦%s)\n",
			$srcN, number_format($srcDebt,2), number_format($srcCredit,2));
		foreach($pos as $p)
			printf("  staged: %s / %s  n=%d  ₦%s\n", $p->status, $p->item_type, $p->n, number_format($p->amt,2));

		// Documents — count ledger-tracked stubs (numeric sid = real SH4).
		$docs = $this->db->query(
			"SELECT COUNT(*) n FROM db_migration_rows r JOIN db_migration_batches b ON b.id=r.batch_id
			  WHERE r.store_id=? AND b.mode='import' AND r.source_table='patient_timeline'
			    AND r.source_id REGEXP '^tl:[0-9]+$' AND r.action='inserted'", [$this->storeId])->row()->n;
		printf("documents: source timeline docs=%d staged stubs=%d (all missing-attachment)\n",
			$s['timeline_docs'], $docs);

		// Ledger actions
		foreach($this->db->query(
			"SELECT r.source_table, r.action, COUNT(*) n FROM db_migration_rows r
			  JOIN db_migration_batches b ON b.id=r.batch_id
			  WHERE r.store_id=? AND b.source_system='smarthospital4'
			    AND r.source_id REGEXP '^[0-9]+$|^tl:[0-9]+$|^pos:[0-9]+$'
			  GROUP BY r.source_table, r.action", [$this->storeId])->result() as $r)
			printf("ledger: %-18s %-12s %d\n", $r->source_table, $r->action, $r->n);
	}

	private function patientIdForSource($srcPid){
		// legacy_ids_json holds {"smarthospital4":"<src id>"}
		$row = $this->db->select('id')->where('store_id', $this->storeId)
			->like('legacy_ids_json', '"smarthospital4":"' . (int)$srcPid . '"')
			->get('db_patients')->row();
		return $row ? (int)$row->id : 0;
	}

	// ------------------------------------------------------------------
	// Clinical history (narrative) — inert history entries per patient
	//
	// The old system's visit/timeline/bed/discharge records are loaded as
	// entries on the patient's history feed (db_patient_events). Nothing here
	// creates an encounter, assessment, invoice, wallet txn or entitlement, so
	// the clinical and billing tables keep exactly the records that were
	// deliberately staged. Each entry keeps its source table + id, the original
	// date and the relevant narrative in meta_json, so provenance is intact
	// when the clinic later reconciles against their old system.
	// ------------------------------------------------------------------

	public function history(){
		echo "== CLINICAL HISTORY ({$this->srcDb} -> {$this->db->database}, store {$this->storeId}) ==\n";
		if(!$this->db->table_exists('db_patient_events')){
			fwrite(STDERR, "db_patient_events is missing — run migrations first.\n"); exit(2);
		}
		$counts = $this->sh4_source_model->historyCounts();
		foreach($counts as $t => $n){ printf("source %-20s %6d record(s)\n", $t, $n); }
		echo "\n";

		$batchId = $this->imports_model->startBatch('smarthospital4',
			'SH4 clinical history (' . count($counts) . ' sources) ' . date('Y-m-d'));
		echo "batch #{$batchId}\n\n";
		$totals = array('inserted' => 0, 'skipped' => 0, 'failed' => 0);

		$totals = $this->mergeCounts($totals, $this->importHistoryTable(
			$batchId, 'opd_details', 'sh4_opd', function($r){
				return array(
					'date'  => $this->pickDate($r, array('created_at')),
					'label' => 'Outpatient visit',
					'body'  => 'Legacy outpatient visit recorded in Smart Hospital 4.0'
						. ($r['case_reference_id'] ? ' — case ' . $r['case_reference_id'] : ''),
					'meta'  => array('case_reference_id' => $r['case_reference_id'] ?? null,
						'discharged' => $r['discharged'] ?? null),
				);
			}));

		$totals = $this->mergeCounts($totals, $this->importHistoryTable(
			$batchId, 'ipd_details', 'sh4_ipd', function($r){
				$bits = array();
				if(!empty($r['bed']))             $bits[] = 'bed ' . $r['bed'];
				if(!empty($r['case_type']))       $bits[] = 'case type ' . $r['case_type'];
				if(!empty($r['known_allergies'])) $bits[] = 'allergies: ' . $r['known_allergies'];
				if(!empty($r['symptoms']))        $bits[] = 'symptoms: ' . $r['symptoms'];
				if(!empty($r['note']))            $bits[] = $r['note'];
				return array(
					'date'  => $this->pickDate($r, array('date', 'created_at')),
					'label' => 'Inpatient stay',
					'body'  => $bits ? implode(' · ', $bits) : 'Legacy inpatient stay recorded in Smart Hospital 4.0',
					// Money columns are deliberately NOT carried across.
					'meta'  => array('case_reference_id' => $r['case_reference_id'] ?? null,
						'bed' => $r['bed'] ?? null, 'bed_group_id' => $r['bed_group_id'] ?? null,
						'case_type' => $r['case_type'] ?? null, 'consultant' => $r['cons_doctor'] ?? null),
				);
			}));

		$totals = $this->mergeCounts($totals, $this->importHistoryTable(
			$batchId, 'patient_timeline', 'sh4_timeline_note', function($r){
				return array(
					'date'  => $this->pickDate($r, array('timeline_date', 'date', 'created_at')),
					'label' => trim((string)($r['title'] ?? '')) ?: 'Clinical note',
					'body'  => trim((string)($r['description'] ?? '')) ?: 'Legacy timeline note',
					'meta'  => array('source_status' => $r['status'] ?? null,
						'recorded_by_type' => $r['generated_users_type'] ?? null),
				);
			},
			// Only the narrative rows — file-bearing rows are staged as document
			// stubs by stageDocuments() and must not be duplicated here.
			// (The column is NOT NULL in SH4, so '' is the only empty form.)
			function($r){ return trim((string)($r['document'] ?? '')) === ''; }));

		$totals = $this->mergeCounts($totals, $this->importHistoryTable(
			$batchId, 'patient_bed_history', 'sh4_bed_history', function($r){
				$span = trim(($r['from_date'] ?? '') . ' → ' . ($r['to_date'] ?? ($r['is_active'] ? 'current' : '')));
				return array(
					'date'  => $this->pickDate($r, array('from_date', 'created_at')),
					'label' => 'Bed allocation',
					'body'  => 'Bed ' . ($r['bed_id'] ?? '—') . ' in group ' . ($r['bed_group_id'] ?? '—')
						. ($span ? ' · ' . $span : '')
						. (!empty($r['revert_reason']) ? ' · ' . $r['revert_reason'] : ''),
					'meta'  => array('bed_id' => $r['bed_id'] ?? null, 'bed_group_id' => $r['bed_group_id'] ?? null,
						'from_date' => $r['from_date'] ?? null, 'to_date' => $r['to_date'] ?? null,
						'is_active' => $r['is_active'] ?? null),
				);
			},
			// patient_bed_history has no patient_id — the owning patient is
			// reached through the case reference on the linked visit.
			null, function($r){
				$src = $this->sh4_source_model->patientForCase((int)($r['case_reference_id'] ?? 0));
				return $src ? (int)$src['patient_id'] : 0;
			}));

		$totals = $this->mergeCounts($totals, $this->importHistoryTable(
			$batchId, 'discharge_card', 'sh4_discharge', function($r){
				return array(
					'date'  => $this->pickDate($r, array('discharge_date', 'created_at')),
					'label' => 'Discharge record',
					// Original referral/discharge narrative preserved verbatim.
					'body'  => $this->firstNonEmpty(array($r['operation'] ?? '', $r['diagnosis'] ?? '',
						$r['investigations'] ?? '', $r['treatment_home'] ?? '', $r['note'] ?? '',
						$r['reason_for_referral'] ?? '')),
					'meta'  => array('discharge_status' => $r['discharge_status'] ?? null,
						'discharge_by' => $r['discharge_by'] ?? null,
						'death_date' => $r['death_date'] ?? null,
						'refer_to_hospital' => $r['refer_to_hospital'] ?? null),
				);
			},
			// Referral-only cards carry no patient_id — they are resolved through
			// ipd_details below rather than skipped silently.
			null, function($r){
				if((int)($r['ipd_details_id'] ?? 0) > 0){
					$ipd = $this->sh4_source_model->ipdStaysById((int)$r['ipd_details_id']);
					if($ipd) return (int)$ipd['patient_id'];
				}
				if((int)($r['opd_details_id'] ?? 0) > 0){
					$opd = $this->sh4_source_model->opdVisitById((int)$r['opd_details_id']);
					if($opd) return (int)$opd['patient_id'];
				}
				return 0;
			}));

		$this->imports_model->finishBatch($batchId);
		echo "\ninserted={$totals['inserted']} skipped={$totals['skipped']} failed={$totals['failed']}\n";
		echo "History entries are inert: no encounters, invoices, wallet txns or entitlements were created.\n";
	}

	/**
	 * Page one source table into db_patient_events.
	 *
	 * @param callable      $shape     row -> ['date','label','body','meta']
	 * @param callable|null $filter    row -> bool (skip rows that are not history)
	 * @param callable|null $resolvePid row -> int (when the table lacks patient_id)
	 */
	private function importHistoryTable($batchId, $table, $eventKey, callable $shape,
			?callable $filter = null, ?callable $resolvePid = null){
		$this->load->model('patients_model', 'patients_m');
		$counts = array('inserted' => 0, 'skipped' => 0, 'failed' => 0, 'unmapped' => 0);
		$after = 0;
		$hasPatientId = $this->sh4_source_model->hasColumn($table, 'patient_id');
		while(true){
			$rows = $this->sh4_source_model->historyPage($table, $after, 500);
			if(empty($rows)) break;
			foreach($rows as $r){
				$after = (int)$r['id'];
				if($filter && !$filter($r)) continue;

				$srcPid = $hasPatientId ? (int)($r['patient_id'] ?? 0) : 0;
				if(!$srcPid && $resolvePid) $srcPid = (int)$resolvePid($r);

				$sourceId = $eventKey . ':' . (int)$r['id'];

				// Resolve the destination patient BEFORE the dedupe check.
				// If no imported identity exists for this row, record the gap as
				// 'failed' — never as 'skipped', because skipped counts as "seen"
				// and would permanently block the row if the identity is imported
				// later. The ledger row does not block a retry either (seen()
				// only matches inserted/skipped/conflict), so the history can be
				// re-run once the missing patients are loaded.
				$pid = $srcPid ? $this->patientIdForSource($srcPid) : 0;
				if(!$pid){
					if(!$this->hasLedgerRow($eventKey, $sourceId)){
						$this->imports_model->record($batchId, $eventKey, $sourceId, 'failed',
							array(), null, 'no imported patient for source id ' . $srcPid);
					}
					$counts['unmapped']++;
					continue;
				}

				if($this->imports_model->seen($eventKey, $sourceId)){ $counts['skipped']++; continue; }

				$shaped = $shape($r);
				$meta = array_merge(array(
					'source'    => 'smarthospital4',
					'source_table' => $table,
					'source_id' => (int)$r['id'],
					'imported_at' => date('c'),
				), is_array($shaped['meta'] ?? null) ? $shaped['meta'] : array());

				$this->db->trans_begin();
				$this->db->insert('db_patient_events', array(
					'store_id'        => $this->storeId,
					'patient_id'      => $pid,
					'event'           => $eventKey,
					'reason'          => mb_substr((string)$shaped['label'], 0, 500),
					'meta_json'       => json_encode(array_merge($meta, array(
						'body' => (string)$shaped['body'],
						'date' => (string)$shaped['date'],
					))),
					'created_by'      => (int)$this->session->userdata('inv_userid'),
					'created_by_name' => (string)$this->session->userdata('inv_username'),
					// Original date preserved for the timeline ordering.
					'created_at'      => $this->pickDate($r, array('created_at')) . ' 00:00:00',
				));
				$eventId = (int)$this->db->insert_id();
				if($eventId > 0){
					$this->db->trans_commit();
					$this->imports_model->record($batchId, $eventKey, $sourceId, 'inserted',
						array(array('table' => 'db_patient_events', 'id' => $eventId)),
						'patient #' . $pid);
					$counts['inserted']++;
				} else {
					$this->db->trans_rollback();
					$this->imports_model->record($batchId, $eventKey, $sourceId, 'failed',
						array(), null, 'event insert failed');
					$counts['failed']++;
				}
			}
			echo "  {$table}: processed to source id {$after} "
				. "(inserted={$counts['inserted']} skipped={$counts['skipped']} unmapped={$counts['unmapped']})\n";
			if(count($rows) < 500) break;
		}
		if($counts['unmapped']){
			echo "  ! {$table}: {$counts['unmapped']} row(s) had no matching imported patient (see ledger)\n";
		}
		return $counts;
	}

	private function mergeCounts($a, $b){
		foreach($b as $k => $v){ $a[$k] = ($a[$k] ?? 0) + $v; }
		return $a;
	}

	/** Has this exact source record already got a ledger row (any action)? */
	private function hasLedgerRow($sourceTable, $sourceId){
		return $this->db->where('store_id', $this->storeId)
			->where('source_table', (string)$sourceTable)->where('source_id', (string)$sourceId)
			->count_all_results('db_migration_rows') > 0;
	}

	// ------------------------------------------------------------------
	// Legacy attachments — attach the real files to the document stubs
	//
	// stageDocuments() created one 'missing_attachment' stub per file-bearing
	// timeline row, with an empty file_path. When the clinic supplies their
	// uploads folder, this command walks it, matches each file to its stub by
	// the source record's document name, copies it into private storage and
	// fills the version row — after which the document becomes downloadable
	// and releasable through the normal (permission-gated) paths.
	//
	// Files are validated with the same rule the interactive uploader uses, so
	// the import cannot introduce a type or size the UI would reject. Matching
	// is by exact filename first, then by basename, and never by guesswork: an
	// unmatched stub stays 'missing_attachment' and is reported, it is not
	// silently linked to the wrong patient's report.
	// ------------------------------------------------------------------

	public function attach(){
		$dir = getenv('SH4_UPLOADS') ?: '';
		if($dir === '' || !is_dir($dir)){
			fwrite(STDERR, "Set SH4_UPLOADS to the folder containing the clinic's uploaded files.\n");
			fwrite(STDERR, "  e.g. SH4_UPLOADS=/path/to/uploads php index.php sh4_import attach\n");
			exit(2);
		}
		if(!function_exists('physio_docs_validate_file')){ $this->load->helper('physio'); }
		$private = physio_docs_dir();
		if(!$private){
			fwrite(STDERR, "Private document storage is unavailable — cannot attach files.\n"); exit(2);
		}

		echo "== LEGACY ATTACHMENTS\n";
		echo "source folder: {$dir}\n";
		echo "private store: {$private}\n\n";

		// Index the supplied folder: exact filename + lowercase basename, so a
		// file can be found regardless of any path prefix stored in SH4.
		$index = array();
		$skippedDirs = 0;
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::LEAVES_ONLY);
		foreach($it as $f){
			if(!$f->isFile()) continue;
			$base = $f->getFilename();
			$key  = strtolower($base);
			if(!isset($index[$key])) $index[$key] = $f->getPathname();
			if(!isset($index[strtolower($f->getPathname())])) $index[strtolower($f->getPathname())] = $f->getPathname();
		}
		echo "files found in folder: " . count($index) . "\n\n";
		if(empty($index)){
			echo "Nothing to attach.\n"; return;
		}

		// The stubs: every imported document still awaiting its file. The
		// original SH4 filename is recovered from the ledger hint.
		$stubs = $this->db->query(
			"SELECT r.id AS row_id, r.source_id, r.targets_json, r.dupe_hint
			   FROM db_migration_rows r
			   JOIN db_migration_batches b ON b.id = r.batch_id
			  WHERE r.store_id = ? AND r.source_table = 'patient_timeline'
			    AND r.action = 'inserted' AND r.dupe_hint LIKE 'missing attachment:%'",
			array($this->storeId))->result();
		echo "document stubs awaiting attachments: " . count($stubs) . "\n\n";

		$attached = 0; $missing = 0; $invalid = 0; $already = 0;
		$batchId = $this->imports_model->startBatch('smarthospital4',
			'SH4 attachments ' . date('Y-m-d'));
		echo "batch #{$batchId}\n";

		foreach($stubs as $row){
			$want = trim(substr((string)$row->dupe_hint, strlen('missing attachment:')));
			if($want === ''){ $missing++; continue; }
			$key = strtolower(basename($want));
			$src = $index[$key] ?? ($index[strtolower($want)] ?? null);
			if(!$src){ $missing++; continue; }

			$targets = json_decode((string)$row->targets_json, true) ?: array();
			$docId = 0; $verId = 0;
			foreach($targets as $t){
				if(($t['table'] ?? '') === 'db_patient_documents') $docId = (int)$t['id'];
				if(($t['table'] ?? '') === 'db_document_versions') $verId = (int)$t['id'];
			}
			if(!$docId){ $missing++; continue; }

			$doc = $this->db->where('id', $docId)->where('store_id', $this->storeId)
				->get('db_patient_documents')->row();
			if(!$doc){ $missing++; continue; }

			// Resolve the version to fill. A rollback can have removed it, so
			// fall back to the document's current/newest version and recreate one
			// when none survives — otherwise a rolled-back attachment could never
			// be re-attached.
			$ver = $verId ? $this->db->where('id', $verId)->where('store_id', $this->storeId)
				->where('document_id', $docId)->get('db_document_versions')->row() : null;
			if(!$ver && !empty($doc->current_version_id)){
				$ver = $this->db->where('id', (int)$doc->current_version_id)->where('store_id', $this->storeId)
					->where('document_id', $docId)->get('db_document_versions')->row();
			}
			if(!$ver){
				$ver = $this->db->where('document_id', $docId)->where('store_id', $this->storeId)
					->order_by('version_no', 'desc')->limit(1)->get('db_document_versions')->row();
			}
			if($ver && trim((string)$ver->file_path) !== ''){ $already++; continue; }
			if(!$ver){
				// No version row survived — create the placeholder this attachment fills.
				$nextVer = ((int)$this->db->where('document_id', $docId)->count_all_results('db_document_versions')) + 1;
				$this->db->insert('db_document_versions', array(
					'store_id'    => $this->storeId,
					'document_id' => $docId,
					'version_no'  => $nextVer,
					'file_path'   => '',
					'mime'        => 'application/octet-stream',
					'uploaded_by' => 'migration:sh4',
					'created_at'  => date('Y-m-d H:i:s'),
				));
				$verId = (int)$this->db->insert_id();
				$ver = $this->db->where('id', $verId)->get('db_document_versions')->row();
				if(!$ver){ $missing++; continue; }
			} else {
				$verId = (int)$ver->id;
			}

			// Same rule as the interactive uploader — never accept what the UI would reject.
			$check = physio_docs_validate_file($src, basename($src));
			if(!$check['ok']){
				$invalid++;
				$this->imports_model->record($batchId, self::ATTACH_SOURCE, 'doc:' . $docId, 'failed',
					array(), null, 'invalid file ' . basename($src) . ': ' . $check['error']);
				continue;
			}

			// Opaque server-side name, matching the uploader's convention.
			$fname = 'd' . $docId . '_v' . (int)$ver->version_no . '_' . bin2hex(random_bytes(8)) . '.' . $check['ext'];
			$dest  = $private . '/' . $fname;
			if(!@copy($src, $dest)){
				$this->imports_model->record($batchId, self::ATTACH_SOURCE, 'doc:' . $docId, 'failed',
					array(), null, 'could not copy ' . basename($src));
				continue;
			}
			@chmod($dest, 0640);

			$this->db->trans_begin();
			$this->db->where('id', $verId)->where('store_id', $this->storeId)->update('db_document_versions', array(
				'file_path'   => $fname,
				'file_hash'   => hash_file('sha256', $dest),
				'file_size'   => filesize($dest),
				'mime'        => $ver->mime ?: 'application/octet-stream',
				'uploaded_by' => 'migration:sh4',
			));
			// The stub becomes a normal draft — no longer a missing attachment,
			// so the existing release/portal gates treat it as a real document.
			$this->db->where('id', $docId)->where('store_id', $this->storeId)
				->update('db_patient_documents', array('status' => 'draft'));
			if($this->db->trans_status() === FALSE){
				$this->db->trans_rollback();
				@unlink($dest);
				$this->imports_model->record($batchId, self::ATTACH_SOURCE, 'doc:' . $docId, 'failed',
					array(), null, 'attach save failed');
				continue;
			}
			$this->db->trans_commit();
				// Recorded under its own source key: rolling an attachment batch back
				// must not mark the document-staging row as rolled back, which would
				// drop the stub from the attachment pipeline entirely.
				$this->imports_model->record($batchId, self::ATTACH_SOURCE, 'doc:' . $docId, 'inserted',
				array(array('table' => 'db_document_versions', 'id' => $verId)),
				'attached ' . basename($src));
			$attached++;
		}

		$this->imports_model->finishBatch($batchId);
		echo "\nattached={$attached} already_attached={$already} unmatched={$missing} invalid={$invalid}\n";
		if($missing){
			echo "! {$missing} stub(s) had no matching file — they stay marked 'missing attachment'\n";
			echo "  and are excluded from download, release and the patient portal.\n";
		}
		if($invalid){
			echo "! {$invalid} file(s) were rejected by the document rules (see ledger).\n";
		}
	}

	// ------------------------------------------------------------------
	// Attachment status report (works before the uploads are supplied)
	// ------------------------------------------------------------------

	public function attachments_report(){
		// Anchor on the exact target object — a bare "id":<n> LIKE would also
		// match a longer id (41 matching 412).
		$docTarget = "{\"table\":\"db_patient_documents\",\"id\":";
		$rows = $this->db->query(
			"SELECT
			   SUM(p.status = 'missing_attachment') AS missing,
			   SUM(p.status <> 'missing_attachment') AS attached,
			   SUM(p.released_to_patient = 1)        AS released
			 FROM db_patient_documents p
			 JOIN db_migration_rows r ON r.store_id = p.store_id
			  AND r.source_table = 'patient_timeline' AND r.action = 'inserted'
			  AND r.targets_json LIKE CONCAT('%', ?, p.id, '}%')
			 WHERE p.store_id = ?", array($docTarget, $this->storeId))->row();
		printf("imported documents: %d attached, %d still missing an attachment, %d released\n",
			(int)($rows->attached ?? 0), (int)($rows->missing ?? 0), (int)($rows->released ?? 0));
		if((int)($rows->missing ?? 0) > 0){
			echo "Missing files are excluded from download, release and the patient portal.\n";
			echo "Supply the clinic's uploads folder with SH4_UPLOADS=... and run 'sh4_import attach'.\n";
		}
	}

	/** First non-empty value from a list (narrative field fallbacks). */
	private function firstNonEmpty(array $vals){
		foreach($vals as $v){ if(trim((string)$v) !== '') return trim((string)$v); }
		return '';
	}

	/** Best available date for a source row, normalised to Y-m-d. */
	private function pickDate(array $r, array $keys){
		foreach($keys as $k){
			$v = trim((string)($r[$k] ?? ''));
			if($v !== '' && $v !== '0000-00-00' && $v !== '0000-00-00 00:00:00'){
				return substr($v, 0, 10);
			}
		}
		return date('Y-m-d');
	}
}
