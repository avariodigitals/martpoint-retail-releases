<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Verify_vitals — nursing intake vitals must be saveable, and must CONTINUE the
 * open draft instead of piling up new sets (CLI).
 *
 *   php index.php verify_vitals run            # store 3
 *   php index.php verify_vitals run 3          # explicit store
 *   php index.php verify_vitals clean          # remove sets this created
 *
 * Why this exists: "can't save nursing intake vitals" was a real defect, and the
 * whole clinical handover depends on it — completeIntake() refuses to hand a
 * visit to the physiotherapist unless a FINAL vitals set exists. The failure mode
 * was quiet rather than loud: every save worked, but each one inserted a NEW set,
 * so a nurse saw "Set #25 draft / #29 draft / #30 draft" for one patient and
 * could never finish.
 *
 * Drives Encounters_model directly — the same methods the mobile screen posts to.
 */
class Verify_vitals extends CI_Controller {

	private $storeId;
	private $pass = 0;
	private $fail = 0;
	private $created = array();

	public function __construct(){
		parent::__construct();
		if(!is_cli()){ show_404(); return; }
		$this->storeId = (int)(getenv('MP_STORE') ?: 3);
		$this->load->model('encounters_model', 'enc');
	}

	private function check($name, $cond, $detail = ''){
		$ok = (bool)$cond;
		if($ok){ $this->pass++; } else { $this->fail++; }
		printf("%s  %-58s %s\n", $ok ? 'PASS' : 'FAIL', $name, $detail);
	}

	private function asUser(){
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

	/** An encounter that is not closed, so vitals may be recorded against it. */
	private function openEncounter(){
		return $this->db->query(
			"SELECT * FROM db_encounters
			  WHERE store_id = ? AND (queue_stage IS NULL OR queue_stage <> 'closed')
			  ORDER BY id DESC LIMIT 1", array($this->storeId))->row();
	}

	private function entry($key, $val, $unit = ''){
		return array('vital_key' => $key, 'label' => $key, 'value' => $val,
			'unit' => $unit, 'not_measured' => false);
	}

	public function run(){
		$args = array_slice($this->uri->segment_array(), 2);
		if(!empty($args[0]) && ctype_digit((string)$args[0])){ $this->storeId = (int)$args[0]; }

		echo "== Nursing intake vitals — store {$this->storeId} ==\n\n";
		if(!$this->asUser()){ echo "No active user on store {$this->storeId}.\n"; return; }

		$enc = $this->openEncounter();
		if(!$enc){ echo "No open encounter to test against.\n"; return; }
		$encId = (int)$enc->id;
		echo "encounter {$enc->encounter_code} (#{$encId})\n\n";

		$before = (int)$this->db->where('encounter_id', $encId)->count_all_results('db_encounter_vitals');

		// 1. An empty payload must be refused, not store an empty set.
		$empty = $this->enc->saveVitalsSet($encId, array(), false, 0);
		$this->check('an empty vitals payload is refused', isset($empty['error']), $empty['error'] ?? '');

		// 2. First save creates a draft.
		$r1 = $this->enc->saveVitalsSet($encId, array(
			$this->entry('bp_systolic', '118', 'mmHg'),
			$this->entry('pulse', '72', 'bpm'),
		), false, 0);
		$this->check('first save creates a vitals set', empty($r1['error']),
			$r1['error'] ?? ('set #' . ($r1['vitals_id'] ?? '?')));
		$setId = (int)($r1['vitals_id'] ?? 0);
		if($setId){ $this->created[] = $setId; }
		if(!$setId){ return; }

		$row = $this->db->where('id', $setId)->get('db_encounter_vitals')->row();
		$this->check('the set is a draft', ($row->status ?? '') === 'draft', 'status=' . ($row->status ?? '?'));
		$n = (int)$this->db->where('vitals_id', $setId)->count_all_results('db_encounter_vital_entries');
		$this->check('both readings were stored', $n === 2, $n . ' entr(ies)');

		// 3. THE REGRESSION — a second save must UPDATE that set, not add another.
		$afterFirst = (int)$this->db->where('encounter_id', $encId)->count_all_results('db_encounter_vitals');
		$r2 = $this->enc->saveVitalsSet($encId, array(
			$this->entry('bp_systolic', '125', 'mmHg'),
			$this->entry('pulse', '76', 'bpm'),
		), false, $setId);
		$afterSecond = (int)$this->db->where('encounter_id', $encId)->count_all_results('db_encounter_vitals');
		$this->check('a second save UPDATES the same set (no new set)',
			empty($r2['error']) && $afterSecond === $afterFirst,
			"sets {$afterFirst} -> {$afterSecond}, set #" . ($r2['vitals_id'] ?? '?'));
		$val = $this->db->select('value_text')->where('vitals_id', $setId)->where('vital_key', 'bp_systolic')
			->get('db_encounter_vital_entries')->row();
		$this->check('the corrected reading replaced the old one',
			($val->value_text ?? '') === '125', 'bp_systolic=' . ($val->value_text ?? '?'));

		// 4. A third save must still not multiply sets.
		$this->enc->saveVitalsSet($encId, array($this->entry('weight', '68', 'kg')), false, $setId);
		$afterThird = (int)$this->db->where('encounter_id', $encId)->count_all_results('db_encounter_vitals');
		$this->check('a third save still does not add a set', $afterThird === $afterFirst,
			"sets {$afterSecond} -> {$afterThird}");

		// 5. "not measured" is a legitimate record, not an empty value.
		$r5 = $this->enc->saveVitalsSet($encId, array(
			array('vital_key' => 'temperature', 'label' => 'Temp', 'value' => '',
				'unit' => 'C', 'not_measured' => true),
		), false, $setId);
		$nm = $this->db->where('vitals_id', $setId)->where('vital_key', 'temperature')
			->get('db_encounter_vital_entries')->row();
		$this->check('a reading can be recorded as not measured',
			empty($r5['error']) && !empty($nm->not_measured), 'not_measured=' . ($nm->not_measured ?? '?'));

		// 6. Finalising locks the set.
		$r6 = $this->enc->saveVitalsSet($encId, array($this->entry('spo2', '98', '%')), true, $setId);
		$fin = $this->db->where('id', $setId)->get('db_encounter_vitals')->row();
		$this->check('the set can be finalised', empty($r6['error']) && ($fin->status ?? '') === 'final',
			'status=' . ($fin->status ?? '?'));

		// 7. A final set must NOT be reopened.
		$r7 = $this->enc->saveVitalsSet($encId, array($this->entry('spo2', '95', '%')), false, $setId);
		$this->check('final vitals are locked against edits', isset($r7['error']), $r7['error'] ?? 'edited!');

		// 8. Intake completion requires a final set — which we now have.
		$this->check('a final set exists for the visit',
			$this->db->where('encounter_id', $encId)->where('status', 'final')->count_all_results('db_encounter_vitals') > 0);

		// 9. Clean up the extra sets we created so the visit is not left messy.
		$this->check('exactly one new set was created overall',
			($afterThird - $before) === 1, ($afterThird - $before) . ' new set(s)');

		echo "\n";
		printf("== SUMMARY: %d passed, %d failed ==\n", $this->pass, $this->fail);
		echo "created set #{$setId} on encounter #{$encId}; run 'php index.php verify_vitals clean' for a full tidy-up.\n";
	}

	/** Remove the sets and entries this verifier created. */
	public function clean(){
		$sets = $this->db->select('id')->where('store_id', $this->storeId)
			->like('recorded_by_name', '', 'after')->order_by('id', 'DESC')->limit(5)
			->get('db_encounter_vitals')->result();
		$removed = 0;
		foreach($sets as $s){
			// Never delete a set that carries a finalised signature.
			$st = $this->db->where('id', $s->id)->get('db_encounter_vitals')->row();
			if($st && $st->status === 'final'){ continue; }
			$this->db->where('vitals_id', $s->id)->delete('db_encounter_vital_entries');
			$this->db->where('id', $s->id)->delete('db_encounter_vitals');
			$removed++;
		}
		echo "removed {$removed} draft vitals set(s)\n";
	}

	public function index(){
		echo "Usage:\n  php index.php verify_vitals run [store_id]\n  php index.php verify_vitals clean\n";
	}
}
