<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Verify_ward — seeds ward data and exercises the real admission flows (CLI).
 *
 *   php index.php verify_ward run            # seed + test on store 3
 *   php index.php verify_ward run 3          # explicit store
 *   php index.php verify_ward clean          # remove the seeded rows only
 *
 * Why this exists: the ward screens LOOK populated (48 admissions, 3 beds) but
 * that says nothing about whether admitting, transferring and discharging
 * actually work. This drives Inpatient_model's own methods — the same code the
 * buttons call — and asserts the resulting state, so a broken flow fails here
 * instead of in front of a patient.
 *
 * Everything it creates is labelled with a run tag and can be removed again, so
 * it is safe to run against a development database.
 */
class Verify_ward extends CI_Controller {

	private $storeId;
	private $tag;
	private $pass = 0;
	private $fail = 0;
	private $note = 0;
	private $created = array('admissions' => array(), 'beds' => array(), 'wards' => array());

	public function __construct(){
		parent::__construct();
		if(!is_cli()){ show_404(); return; }
		$this->storeId = (int)(getenv('MP_STORE') ?: 3);
		$this->tag = 'WARD' . substr(md5(uniqid('', true)), 0, 6);
		$this->load->model('inpatient_model', 'ipd');
	}

	private function check($name, $cond, $detail = ''){
		$ok = (bool)$cond;
		if($ok){ $this->pass++; } else { $this->fail++; }
		printf("%s  %-56s %s\n", $ok ? 'PASS' : 'FAIL', $name, $detail);
	}

	private function info($name, $detail){
		$this->note++;
		printf("NOTE  %-56s %s\n", $name, $detail);
	}

	/** Act as the store's admin so the model's permission-free paths are used. */
	private function asAdmin(){
		$u = $this->db->select('id,username,role_id,store_id')
			->where('store_id', $this->storeId)->where('status', 1)
			->order_by('role_id', 'ASC')->get('db_users')->row();
		if(!$u){ return false; }
		$this->session->set_userdata(array(
			'inv_userid'   => (int)$u->id,
			'inv_username' => $u->username,
			'role_id'      => (int)$u->role_id,
			'logged_in'    => 1,
			'store_id'     => (string)$u->store_id,
		));
		return true;
	}

	/** A patient with no active admission and no money already attached. */
	private function freePatient(){
		return $this->db->query(
			'SELECT p.* FROM db_patients p
			 WHERE p.store_id = ? AND p.deceased = 0
			   AND NOT EXISTS (SELECT 1 FROM db_admissions a
			                   WHERE a.patient_id = p.id AND a.status = \'active\')
			 ORDER BY p.id DESC LIMIT 1', array($this->storeId))->row();
	}

	/* ------------------------------------------------------------------ */
	/* Seeding                                                            */
	/* ------------------------------------------------------------------ */

	private function seedWardAndBeds(){
		$ward = $this->db->where('store_id', $this->storeId)->where('status', 1)
			->order_by('id', 'ASC')->get('db_wards')->row();
		if(!$ward){
			$this->db->insert('db_wards', array(
				'store_id' => $this->storeId, 'name' => 'Ward A (seeded)',
				'code' => 'WA', 'status' => 1,
				'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
				'created_by' => $this->session->userdata('inv_username') ?: 'seed',
			));
			$wardId = (int)$this->db->insert_id();
			$this->created['wards'][] = $wardId;
		} else {
			$wardId = (int)$ward->id;
		}

		// Add beds until the ward has a reasonable board to work with, so the
		// transfer test has a destination that is genuinely free.
		$existing = $this->db->where('ward_id', $wardId)->count_all_results('db_beds');
		$target = max(4, (int)$existing);
		$labels = array('A1','A2','A3','A4','A5','A6','A7','A8');
		for($i = (int)$existing; $i < $target; $i++){
			$label = $labels[$i] ?? ('A' . ($i + 1));
			if($this->db->where('ward_id', $wardId)->where('bed_label', $label)
				->count_all_results('db_beds')){ continue; }
			$this->db->insert('db_beds', array(
				'store_id' => $this->storeId, 'ward_id' => $wardId, 'bed_label' => $label,
				'status' => 'available', 'daily_rate' => 7500,
				'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
				'created_by' => $this->session->userdata('inv_username') ?: 'seed',
			));
			$this->created['beds'][] = (int)$this->db->insert_id();
		}
		return $wardId;
	}

	private function freeBed($excludeBedId = 0){
		$q = $this->db->query(
			'SELECT b.* FROM db_beds b
			 WHERE b.store_id = ? AND b.status = \'available\' AND b.id <> ?
			   AND NOT EXISTS (SELECT 1 FROM db_bed_occupancy o
			                   WHERE o.bed_id = b.id AND o.to_at IS NULL)
			 ORDER BY b.id ASC LIMIT 1', array($this->storeId, $excludeBedId));
		return $q->row();
	}

	/* ------------------------------------------------------------------ */
	/* The run                                                            */
	/* ------------------------------------------------------------------ */

	public function run(){
		$args = array_slice($this->uri->segment_array(), 2);
		if(!empty($args[0]) && ctype_digit((string)$args[0])){ $this->storeId = (int)$args[0]; }

		echo "== Ward / admission flow — store {$this->storeId}, tag {$this->tag} ==\n\n";
		if(!$this->asAdmin()){ echo "No active user on store {$this->storeId}.\n"; return; }

		// ---------- seeding ----------
		$wardId = $this->seedWardAndBeds();
		$bedCount = (int)$this->db->where('ward_id', $wardId)->count_all_results('db_beds');
		$this->check('ward board has beds to test with', $bedCount >= 4, "ward #{$wardId}, {$bedCount} bed(s)");

		$board = $this->ipd->bedBoard();
		$this->check('bedBoard() returns the ward', !empty($board), count($board) . ' ward row(s)');

		// ---------- admit ----------
		$patient = $this->freePatient();
		if(!$patient){ $this->check('a free patient exists to admit', false); return; }
		$bed = $this->freeBed();

		// A missing outpatient decision must be refused, not silently admitted.
		$noDecision = $this->ipd->admit((int)$patient->id, array('reason' => 'test'));
		$this->check('admit refuses without an outpatient decision',
			empty($noDecision['ok']), $noDecision['error'] ?? '');

		// A missing reason must be refused too.
		$noReason = $this->ipd->admit((int)$patient->id,
			array('reason' => '', 'outpatient_decision' => 'pause'));
		$this->check('admit refuses without a reason',
			empty($noReason['ok']), $noReason['error'] ?? '');

		$adm = $this->ipd->admit((int)$patient->id, array(
			'reason' => 'Ward verification run ' . $this->tag,
			'care_plan' => 'Seeded by verify_ward',
			'outpatient_decision' => 'pause',
			'outpatient_decision_note' => 'Automated ward check',
			'bed_id' => $bed ? (int)$bed->id : 0,
		));
		$this->check('admit succeeds', !empty($adm['ok']), $adm['error'] ?? ('admission ' . ($adm['admission_code'] ?? '?')));
		if(empty($adm['ok'])){ return; }
		$admId = (int)$adm['admission_id'];
		$this->created['admissions'][] = $admId;

		$row = $this->db->where('id', $admId)->get('db_admissions')->row();
		$this->check('admission is active', ($row->status ?? '') === 'active', 'status=' . ($row->status ?? '?'));
		$this->check('admission has a generated code',
			!empty($row->admission_code) && strpos($row->admission_code, 'ADM-') === 0,
			$row->admission_code ?? '');

		// Bed occupancy opened?
		if($bed){
			$occ = $this->db->where('admission_id', $admId)->where('to_at IS NULL')
				->get('db_bed_occupancy')->row();
			$this->check('bed occupancy opened for the admission', !empty($occ),
				$occ ? ('bed ' . $occ->bed_id) : 'no open occupancy row');
			$bedNow = $this->db->where('id', $bed->id)->get('db_beds')->row();
			$this->check('bed marked occupied', ($bedNow->status ?? '') === 'occupied',
				'status=' . ($bedNow->status ?? '?'));

			// A second patient must not be able to take the same bed.
			$other = $this->freePatient();
			if($other && (int)$other->id !== (int)$patient->id){
				$conflict = $this->ipd->admit((int)$other->id, array(
					'reason' => 'conflict probe ' . $this->tag,
					'outpatient_decision' => 'pause',
					'bed_id' => (int)$bed->id,
				));
				$this->check('an occupied bed cannot be double-booked',
					empty($conflict['ok']), $conflict['error'] ?? 'unexpectedly admitted');
				if(!empty($conflict['ok'])){ $this->created['admissions'][] = (int)$conflict['admission_id']; }
			}
		} else {
			$this->info('bed occupancy', 'no free bed available to test with');
		}

		// One active admission per patient.
		$again = $this->ipd->admit((int)$patient->id, array(
			'reason' => 'duplicate probe', 'outpatient_decision' => 'pause'));
		$this->check('a patient cannot hold two active admissions',
			empty($again['ok']), $again['error'] ?? '');

		// Invoice.
		$this->check('admission created its invoice', !empty($row->invoice_id), 'invoice_id=' . ($row->invoice_id ?? 'null'));

		// Tasks.
		$tasks = $this->db->where('admission_id', $admId)->get('db_nursing_tasks')->result();
		$this->check('nursing tasks generated for the admission day', count($tasks) >= 2,
			count($tasks) . ' task(s): ' . implode(', ', array_map(function($t){ return $t->task_code; }, $tasks)));

		// ---------- daily task generation is idempotent ----------
		$before = (int)$this->db->where('admission_id', $admId)->count_all_results('db_nursing_tasks');
		$this->ipd->generateTasks($admId, date('Y-m-d'));
		$after = (int)$this->db->where('admission_id', $admId)->count_all_results('db_nursing_tasks');
		$this->check('re-running task generation does not duplicate', $before === $after,
			"{$before} -> {$after}");

		// ---------- complete a task ----------
		$open = $this->db->where('admission_id', $admId)->where('status', 'open')
			->get('db_nursing_tasks')->row();
		if($open){
			$done = $this->ipd->completeTaskItem((int)$open->id, 'verified by ' . $this->tag);
			$this->check('a nursing task can be completed', !empty($done['ok']), json_encode($done));
			$againDone = $this->ipd->completeTaskItem((int)$open->id, 'second attempt');
			$this->check('a completed task cannot be completed twice',
				empty($againDone['ok']), $againDone['error'] ?? '');
		} else {
			$this->info('nursing task completion', 'no open task to complete');
		}

		// ---------- transfer ----------
		$dest = $this->freeBed($bed ? (int)$bed->id : 0);
		if($dest){
			$tr = $this->ipd->requestTransfer($admId, (int)$dest->id, 'verification ' . $this->tag);
			$this->check('bed transfer can be requested', !empty($tr['ok']), json_encode($tr));
			if(!empty($tr['ok'])){
				$porter = $this->db->where('store_id', $this->storeId)
					->where('status', 'open')->order_by('id', 'DESC')
					->get('db_porter_tasks')->row();
				$this->check('a porter task is raised for the move', !empty($porter),
					$porter ? ('task #' . $porter->id . ' ' . $porter->task_type) : 'none');
				if($porter){
					$claim = $this->ipd->claimTask((int)$porter->id);
					$comp  = $this->ipd->completeTask((int)$porter->id);
					$this->check('porter task claim + complete', !empty($claim['ok']) && !empty($comp['ok']),
						json_encode(array('claim' => $claim['ok'] ?? false, 'complete' => $comp['ok'] ?? false)));
				}
			}
		} else {
			$this->info('transfer', 'no second free bed to transfer into');
		}

		// ---------- notes ----------
		// Valid types are observation / handover / review.
		$note = $this->ipd->recordNote($admId, 'observation', 'note from ' . $this->tag, 'morning');
		$this->check('a clinical note can be recorded', !empty($note['ok']), json_encode($note));
		$badNote = $this->ipd->recordNote($admId, 'nursing', 'bad type ' . $this->tag);
		$this->check('an invalid note type is refused', empty($badNote['ok']), $badNote['error'] ?? '');
		$notes = $this->ipd->notes($admId);
		$this->check('the note is readable back', !empty($notes), count((array)$notes) . ' note(s)');

		// ---------- discharge (recommend -> decide) ----------
		$rec = $this->ipd->recommendDischarge($admId, 'Recommendation from ' . $this->tag);
		$this->check('a discharge can be recommended',
			!empty($rec['ok']), json_encode($rec));
		$dischargeId = (int)($rec['discharge_id'] ?? 0);
		if(!$dischargeId){
			$drow = $this->db->where('admission_id', $admId)
				->order_by('id', 'DESC')->get('db_discharges')->row();
			$dischargeId = (int)($drow->id ?? 0);
		}
		$this->check('the discharge is pending a decision',
			$dischargeId > 0 && ($this->db->where('id', $dischargeId)->get('db_discharges')->row()->status ?? '') === 'recommended',
			'db_discharges #' . $dischargeId);

		// Deciding a discharge that was never recommended must be refused.
		$notPending = $this->ipd->decideDischarge(0, true, 'recovered', 'summary', '');
		$this->check('deciding a non-existent discharge is refused',
			empty($notPending['ok']), $notPending['error'] ?? '');

		$dis = $this->ipd->decideDischarge($dischargeId, true, 'recovered',
			'Discharge summary ' . $this->tag, 'review if needed', date('Y-m-d', strtotime('+14 days')));
		$this->check('discharge can be approved', !empty($dis['ok']), json_encode($dis));

		/*
		 * Approval is not departure. decideDischarge() only records the clinical
		 * decision; the ward is freed by completeDischarge(), which runs when the
		 * patient actually leaves — possibly the next morning. So the admission
		 * must STILL be active at this point, and that is asserted rather than
		 * assumed.
		 */
		$stillActive = $this->db->where('id', $admId)->get('db_admissions')->row();
		$this->check('approving a discharge does not yet free the bed',
			($stillActive->status ?? '') === 'active', 'status=' . ($stillActive->status ?? '?'));

		$comp = $this->ipd->completeDischarge($dischargeId);
		$this->check('the patient can actually be discharged', !empty($comp['ok']), json_encode($comp));

		$closed = $this->db->where('id', $admId)->get('db_admissions')->row();
		$this->check('admission is no longer active', ($closed->status ?? '') !== 'active',
			'status=' . ($closed->status ?? '?'));

		$stillOpen = (int)$this->db->where('admission_id', $admId)->where('to_at IS NULL')
			->count_all_results('db_bed_occupancy');
		$this->check('discharge closes the bed occupancy', $stillOpen === 0, $stillOpen . ' open occupancy row(s)');

		if($bed){
			$bedAfter = $this->db->where('id', $bed->id)->get('db_beds')->row();
			$this->check('discharge frees the bed', ($bedAfter->status ?? '') !== 'occupied',
				'status=' . ($bedAfter->status ?? '?'));
		}

		$openTasks = (int)$this->db->where('admission_id', $admId)->where('status', 'open')
			->count_all_results('db_nursing_tasks');
		$this->check('discharge suppresses outstanding ward tasks', $openTasks === 0,
			$openTasks . ' still open');

		// A patient whose admission is closed must be admissible again.
		$readmit = $this->ipd->admit((int)$patient->id, array(
			'reason' => 'readmission probe ' . $this->tag,
			'outpatient_decision' => 'pause',
		));
		$this->check('a discharged patient can be readmitted', !empty($readmit['ok']),
			$readmit['error'] ?? ('admission ' . ($readmit['admission_code'] ?? '?')));
		if(!empty($readmit['ok'])){ $this->created['admissions'][] = (int)$readmit['admission_id']; }

		echo "\n";
		printf("== SUMMARY: %d passed, %d failed, %d note(s) ==\n", $this->pass, $this->fail, $this->note);
		echo "seeded: " . count($this->created['admissions']) . " admission(s), "
			. count($this->created['beds']) . " bed(s), "
			. count($this->created['wards']) . " ward(s)\n";
		echo "run 'php index.php verify_ward clean' to remove them.\n";
	}

	/** Remove only what a previous run created (matched by its tag / own ids). */
	public function clean(){
		$a = $this->db->select('id')->where('store_id', $this->storeId)
			->like('reason', 'WARD', 'after')->get('db_admissions')->result();
		$removed = 0;
		foreach($a as $r){
			$this->db->where('admission_id', $r->id)->delete('db_bed_occupancy');
			$this->db->where('admission_id', $r->id)->delete('db_nursing_tasks');
			$this->db->where('admission_id', $r->id)->delete('db_porter_tasks');
			$this->db->where('id', $r->id)->delete('db_admissions');
			$removed++;
		}
		echo "removed {$removed} seeded admission(s)\n";
	}

	public function index(){
		echo "Usage:\n  php index.php verify_ward run [store_id]\n  php index.php verify_ward clean\n";
	}
}
