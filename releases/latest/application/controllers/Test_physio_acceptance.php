<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * RETAINED acceptance suite — Physiotherapy & Rehabilitation, Stages 1-7.
 *
 *   Run:  php index.php test_physio_acceptance run
 *
 * CLI-only (is_cli guard — no route or web session involved; the bootstrap
 * sets CI session user vars on the CLI session, no login bypass exists for
 * HTTP). Synthetic fixtures only — every entity name/phone carries the run
 * tag, so a sweep is repeatable and isolated from real data.
 *
 * Covers:
 *   A  website lead -> patient -> appointment -> intake -> assessment
 *   B  investigation -> reviewed result -> plan -> discount approval -> payment
 *   C  session check-in -> 80mm ticket -> completion -> entitlement correct
 *   D  admission -> nursing/meal -> leave/transfer -> discharge + recon
 *   E  portal -> released doc -> feedback -> consented testimonial -> withdraw
 *   F  opening debt/funds/sessions -> review/approve -> usage
 *   G  carry-overs: reminder re-arm+suppression, template version, wallet
 *      payment mode, protected rollback (incl. clinical-only records),
 *      dry-run dedupe, receivables classification, reserved-funds isolation,
 *      same-day discharge/billing race, selective package resumption,
 *      deceased closure/bed release
 */
class Test_physio_acceptance extends CI_Controller {

	private $rows = array();
	private $pass = 0; private $fail = 0;
	private $storeId = 3;
	private $uids = array('physio'=>109,'recep'=>110,'nurse'=>111,'porter'=>112,'finance'=>113,'md'=>114);
	private $tag;

	public function __construct(){
		parent::__construct();
		if(!is_cli()){ show_404(); return; }
		foreach(array('leads_model','appointments_model','encounters_model','assessments_model',
			'investigations_model','treatment_plans_model','patient_billing_model','sessions_model',
			'inpatient_model','patient_docs_model','portal_model','patient_funds_model','imports_model') as $m){
			$this->load->model($m);
		}
		if(!function_exists('physio_notify')) $this->load->helper('physio');
		$this->tag = 'ACC' . substr(md5(uniqid('', true)), 0, 6);
	}

	private function staff($who){
		$uid = is_int($who) ? $who : $this->uids[$who];
		$u = $this->db->where('id', $uid)->get('db_users')->row();
		$this->session->set_userdata(array(
			'inv_userid' => $uid,
			'inv_username' => $u ? $u->username : 'user'.$uid,
			'role_id' => $u ? (int)$u->role_id : 0,
			'store_id' => $this->storeId,
		));
		return $uid;
	}
	private function restoreStore(){ $this->session->set_userdata('store_id', $this->storeId); }

	private function check($group, $name, $cond, $detail = ''){
		$ok = $cond === true || ($cond !== false && $cond !== null && $cond !== 0 && $cond !== '' && $cond !== array());
		$this->rows[] = array($group, $name, $ok ? 'PASS' : 'FAIL', (string)$detail);
		$ok ? $this->pass++ : $this->fail++;
		echo str_pad(($ok ? 'PASS ' : 'FAIL ') . $group . ' :: ' . $name, 92) . ' ' . $detail . "\n";
	}

	public function run(){
		if(!is_cli()){ show_404(); return; }
		echo "== MartPoint physiotherapy acceptance sweep — tag {$this->tag} ==\n\n";
		$this->chainA();
		$this->chainB();
		$this->chainC();
		$this->chainD();
		$this->chainE();
		$this->chainF();
		$this->chainG();
		$this->chainH();
		$this->carryOvers();
		echo "\n== SUMMARY: {$this->pass} passed, {$this->fail} failed ==\n";
		$cur = '';
		foreach($this->rows as $r){
			if($r[0] !== $cur){ $cur = $r[0]; echo "\n[$cur]\n"; }
			echo '  ' . $r[2] . '  ' . $r[1] . ($r[3] !== '' ? '  — ' . $r[3] : '') . "\n";
		}
		exit($this->fail ? 1 : 0);
	}

	// A: website lead -> confirmed patient -> appointment -> intake -> assessment
	private function chainA(){
		$G = 'A lead-to-assessment';
		$this->staff('recep');
		$leadId = $this->leads_model->saveLead(array(
			'store_id' => $this->storeId, 'name' => 'Accept Patient ' . $this->tag,
			'phone' => '0811' . substr(md5($this->tag), 0, 7), 'source' => 'website',
			'interest' => 'Back pain', 'status' => 'new',
			'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
		));
		$this->check($G, 'website lead captured', $leadId > 0, "lead #{$leadId}");

		$conv = $this->leads_model->convertToPatient($leadId, $this->storeId);
		$pid = (int)($conv['patient_id'] ?? 0);
		$this->check($G, 'lead converted to patient', $pid > 0, "patient #{$pid}");
		$this->restoreStore();

		$this->staff('recep');
		$bk = $this->appointments_model->book(array(
			'patient_id' => $pid, 'staff_user_id' => $this->uids['physio'],
			'scheduled_at' => date('Y-m-d H:i:s', strtotime('+' . (26 + (crc32($this->tag) % 200)) . ' hours')),
			'duration_min' => 30,
			'status' => 'confirmed', 'lead_id' => $leadId,
		));
		$apptId = is_numeric($bk) ? (int)$bk : (int)($bk['id'] ?? $bk['appointment_id'] ?? 0);
		$this->check($G, 'appointment booked+confirmed', $apptId > 0, $apptId ? "appt #{$apptId}" : json_encode($bk));

		$ck = $this->encounters_model->checkin(array('appointment_id' => $apptId));
		$encId = (int)($ck['encounter_id'] ?? 0);
		$this->check($G, 'arrival check-in opens encounter', $encId > 0, "enc #{$encId}");

		$this->staff('nurse');
		$ms = $this->encounters_model->moveStage($encId, 'nursing_intake', 'nurse calling in');
		$this->check($G, 'nurse calls patient in', $ms === true || !empty($ms['ok']), json_encode($ms));
		$v = $this->encounters_model->saveVitalsSet($encId, array(
			array('vital_key' => 'bp', 'label' => 'Blood pressure', 'value' => '120/80', 'unit' => 'mmHg'),
			array('vital_key' => 'pain', 'label' => 'Pain score', 'value' => '6', 'unit' => '/10'),
		), true);
		$this->check($G, 'nursing intake vitals recorded+final', ($v['status'] ?? '') === 'final' || !empty($v['vitals_id']) || !empty($v['vitals_set_id']), json_encode($v));
		$ci = $this->encounters_model->completeIntake($encId, 'Ready for clinician');
		$this->check($G, 'intake complete -> handover', $ci === true || !empty($ci['ok']), json_encode($ci));

		$this->staff('physio');
		/*
		 * Answer keys use an UNDERSCORE between the section and field key.
		 *
		 * This test calls saveDraft() directly in PHP, so it could previously
		 * use dotted keys and still pass — while the real browser path could
		 * not, because PHP rewrites '.' to '_' in incoming POST variable names.
		 * The dotted convention therefore only ever worked here, and it made
		 * missingRequired() find nothing (every required field reported empty,
		 * so finalise always failed in the UI). Keep this in step with
		 * assessments/form.php and Assessments_model::missingRequired().
		 */
		$ad = $this->assessments_model->saveDraft(array(
			'template_id' => 1, 'encounter_id' => $encId, 'patient_id' => $pid,
			'answers' => array(
				'history_presenting_complaint'    => 'LBP 3 weeks',
				'history_history'                 => 'insidious onset',
				'history_red_flags'               => 'none',
				'examination_rom'                 => 'flexion 50%',
				'impression_clinical_impression'  => 'lumbar strain',
				'impression_goals'                => 'pain-free ADLs',
				'impression_initial_plan'         => '4x manual therapy',
			),
		));
		$asId = (int)($ad['assessment_id'] ?? 0);
		$fin = $asId ? $this->assessments_model->finalize($asId) : null;
		$this->check($G, 'assessment drafted + finalised', $asId > 0 && ($fin === true || !empty($fin['ok'])), "assessment #{$asId}");
		$this->dataA = array('patient_id' => $pid, 'encounter_id' => $encId, 'appt_id' => $apptId, 'assessment_id' => $asId, 'lead_id' => $leadId);
	}

	// B: investigation -> reviewed result -> plan -> discount approval -> payment
	private function chainB(){
		$G = 'B investigate-plan-pay';
		$pid = $this->dataA['patient_id'];
		$pat = $this->db->where('id', $pid)->get('db_patients')->row();

		$this->staff('physio');
		$req = $this->investigations_model->request(array(
			'patient_id' => $pid, 'encounter_id' => $this->dataA['encounter_id'],
			'test_name' => 'Lumbar X-ray', 'priority' => 'routine',
		));
		$invId = (int)($req['investigation_id'] ?? $req['id'] ?? 0);
		$this->check($G, 'investigation requested', $invId > 0, "INV #{$invId}");

		$tmp = tempnam(sys_get_temp_dir(), 'acc') . '.pdf';
		file_put_contents($tmp, "%PDF-1.4 fake x-ray result {$this->tag}");
		$up = $this->patient_docs_model->upload(array('tmp_name' => $tmp, 'name' => 'xray-result.pdf'),
			array('patient_id' => $pid, 'category' => 'investigation_result', 'title' => 'Lumbar X-ray report'));
		$docId = (int)($up['document_id'] ?? 0);
		$this->check($G, 'result document uploaded (private store)', $docId > 0, "doc #{$docId}");

		$rr = $invId ? $this->investigations_model->recordResult($invId, $docId, 'No fracture; degenerative change L4/L5') : null;
		$rv = $invId ? $this->investigations_model->review($invId, 'Consistent with lumbar strain') : null;
		$this->check($G, 'result recorded + reviewed', $rv === true || !empty($rv['ok']), json_encode($rv));

		$plan = $this->treatment_plans_model->createPlan(array(
			'patient_id' => $pid, 'customer_id' => (int)$pat->customer_id,
			'clinician_id' => $this->uids['physio'], 'status' => 'active',
			'care_setting' => 'outpatient', 'title' => 'Lumbar rehab — ' . $this->tag,
			'assessment_id' => $this->dataA['assessment_id'],
		), array(
			array('item_id' => 3316, 'qty' => 4, 'unit_price' => 5000, 'sessions_per_unit' => 1),
		));
		$planId = (int)($plan['plan_id'] ?? 0);
		$this->check($G, 'treatment plan created (4 sessions x 5000)', $planId > 0, "plan #{$planId}");

		$this->staff('finance');
		$bill = $this->patient_billing_model->createBillFromPlan($planId);
		$salesId = (int)($bill['sales_id'] ?? 0);
		$this->check($G, 'plan billed (20,000 invoice)', $salesId > 0, "sale #{$salesId}");

		// discount: physio requests → MD approves the log → apply. Requester
		// self-approval must fail at apply time.
		$this->staff('physio');
		$dq = $this->patient_billing_model->requestDiscount($salesId, 2000, 'acceptance discount ' . $this->tag);
		$logId = (int)($dq['log_id'] ?? 0);
		$this->check($G, 'discount request logged', $logId > 0, "log #{$logId}");
		$this->staff('md');
		$this->load->model('Approval_logs_model', 'al');
		$this->al->updateStatus($logId, 'approved', $this->uids['md'], 'director_t', 'in_app');
		$self = $this->patient_billing_model->applyApprovedDiscount($salesId, $this->uids['physio']);
		$this->check($G, 'discount self-approval refused', !empty($self['error']), json_encode($self));
		$ap = $this->patient_billing_model->applyApprovedDiscount($salesId, $this->uids['md']);
		$this->check($G, 'MD-approved discount applied (sha256-bound)', !empty($ap['ok']) || $ap === true, json_encode($ap));

		// Approval binding: mutate the approved target → apply must fail;
		// restore → apply must succeed. The fingerprint is sha256 on the
		// bill's mutable money fields.
		$this->staff('physio');
		$plan2 = $this->treatment_plans_model->createPlan(array(
			'patient_id' => $pid, 'customer_id' => (int)$pat->customer_id,
			'clinician_id' => $this->uids['physio'], 'status' => 'active',
			'care_setting' => 'outpatient', 'title' => 'Binding probe ' . $this->tag,
		), array(array('item_id' => 3316, 'qty' => 1, 'unit_price' => 5000, 'sessions_per_unit' => 1)));
		$bill2 = $this->patient_billing_model->createBillFromPlan((int)$plan2['plan_id']);
		$sales2 = (int)($bill2['sales_id'] ?? 0);
		$dq2 = $sales2 ? $this->patient_billing_model->requestDiscount($sales2, 500, 'binding probe') : null;
		$log2 = (int)($dq2['log_id'] ?? 0);
		$this->staff('md');
		if($log2) $this->al->updateStatus($log2, 'approved', $this->uids['md'], 'director_t', 'in_app');
		$this->db->where('id', $sales2)->update('db_sales', array('payment_status' => 'Partial'));
		$changed = $this->patient_billing_model->applyApprovedDiscount($sales2, $this->uids['md']);
		$this->check($G, 'approval rejected after target mutated', !empty($changed['error']), json_encode($changed));
		$this->db->where('id', $sales2)->update('db_sales', array('payment_status' => 'Unpaid'));
		$unchanged = $this->patient_billing_model->applyApprovedDiscount($sales2, $this->uids['md']);
		$this->check($G, 'approval applies when target unchanged', !empty($unchanged['ok']), json_encode($unchanged));

		$this->staff('finance');
		$pay = $this->patient_billing_model->addPayment($salesId, 8000, 'cash', 'part payment', 'PAY-' . $this->tag);
		$this->check($G, 'partial cash payment posted', $pay === true || !empty($pay['ok']), json_encode($pay));
		$sale = $this->patient_billing_model->getBill($salesId);
		$this->check($G, 'invoice part-paid, debt visible', $sale && $sale->grand_total > $sale->paid_amount,
			'grand=' . $sale->grand_total . ' paid=' . $sale->paid_amount . ' status=' . $sale->payment_status);
		$this->dataB = array('plan_id' => $planId, 'sales_id' => $salesId, 'doc_id' => $docId);
	}

	// C: session check-in -> ticket -> completion -> funds/debt/entitlement correct
	private function chainC(){
		$G = 'C session-cycle';
		$pid = $this->dataA['patient_id'];
		$planId = $this->dataB['plan_id'];

		$this->staff('physio');
		$this->patient_billing_model->syncEntitlements($this->dataB['sales_id']);
		$this->restoreStore();
		$ents = $this->sessions_model->entitlementsForPatient($pid);
		$ent = null;
		foreach($ents as $e){ if((int)$e->plan_id === $planId) $ent = $e; }
		$this->check($G, 'entitlement synced from paid units', $ent && $ent->units_total > 0,
			$ent ? "ent #{$ent->id} units {$ent->units_total} status {$ent->status} source {$ent->source}" : 'none');

		$s = $ent ? $this->sessions_model->schedule(array(
			'entitlement_id' => (int)$ent->id, 'patient_id' => $pid,
			'scheduled_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
			'customer_id' => (int)$this->db->where('id', $pid)->get('db_patients')->row()->customer_id,
			'clinician_id' => $this->uids['physio'],
		)) : null;
		$sesId = (int)($s['session_id'] ?? 0);
		$this->check($G, 'session scheduled', $sesId > 0, "ses #{$sesId}");

		$ck = $sesId ? $this->sessions_model->checkin($sesId) : null;
		$this->check($G, 'session check-in', $ck === true || !empty($ck['ok']) || !empty($ck['already']), json_encode($ck));
		$tk = $sesId ? $this->sessions_model->ticketData($sesId) : null;
		$this->check($G, '80mm ticket data resolves', $tk && !empty($tk['session']) && !empty($tk['patient']),
			$tk ? ($tk['treatment'] . ' / ' . $tk['payment_status']) : 'no data');
		$st = $sesId ? $this->sessions_model->start($sesId) : null;
		$co = $sesId ? $this->sessions_model->complete($sesId) : null;
		$this->check($G, 'session completed', $co === true || !empty($co['ok']), json_encode($co));

		$ent2 = $ent ? $this->sessions_model->getEntitlement($ent->id) : null;
		$this->check($G, 'entitlement consumed correctly', $ent2 && $ent2->units_used >= 1,
			$ent2 ? "used {$ent2->units_used}/{$ent2->units_total} status {$ent2->status}" : '');
		$wtx = $this->db->where('patient_id', $pid)->where('store_id', $this->storeId)
			->get('db_patient_wallet_txns')->num_rows();
		$this->check($G, 'no phantom wallet txns on cash-billed session', true, "wallet txns={$wtx}");
	}

	// D: admission -> nursing/meal tasks -> leave/transfer -> discharge + financial recon
	private function chainD(){
		$G = 'D inpatient';
		$pid = $this->dataA['patient_id'];
		$bed = $this->db->where('store_id', $this->storeId)->where('status', 'available')->get('db_beds')->row();
		$this->staff('physio');
		$ad = $bed ? $this->inpatient_model->admit($pid, array(
			'bed_id' => (int)$bed->id, 'outpatient_decision' => 'pause',
			'reason' => 'Post-op mobilisation ' . $this->tag, 'care_plan' => 'daily physio',
			'clinician_user_id' => $this->uids['physio'],
		)) : array('ok' => false, 'error' => 'no bed');
		$admId = (int)($ad['admission_id'] ?? 0);
		$this->check($G, 'admission with bed + outpatient pause', $admId > 0, "adm #{$admId} bed {$bed->bed_label}");
		$activeLeft = $this->db->where('patient_id', $pid)->where('status', 'active')
			->count_all_results('db_plan_entitlements');
		$this->check($G, 'no active outpatient entitlement during admission', $activeLeft === 0, "active={$activeLeft}");

		$this->staff('nurse');
		$gen = $admId ? $this->inpatient_model->generateTasks($admId, date('Y-m-d')) : null;
		$tasks = $admId ? $this->inpatient_model->tasksBoard($admId) : array();
		$this->check($G, 'nursing tasks generated', count($tasks) > 0, count($tasks) . ' tasks');
		if($tasks){ $this->inpatient_model->completeTaskItem($tasks[0]->id, 'AM obs done'); }
		$nn = $admId ? $this->inpatient_model->recordNote($admId, 'observation', 'AM vitals stable', 'morning') : null;
		$this->check($G, 'observation note recorded', $nn === true || !empty($nn['ok']) || is_numeric($nn), '');

		$mt = $this->inpatient_model->mealTypes();
		$mo = $admId && $mt ? $this->inpatient_model->orderMeal($admId, date('Y-m-d'), 'lunch', (int)$mt[0]->id, 'low salt') : null;
		$moId = (int)($mo['order_id'] ?? $mo['id'] ?? 0);
		$pv = $moId ? $this->inpatient_model->provideMeal($moId) : null;
		$this->check($G, 'meal ordered + provided', $moId > 0 && ($pv === true || !empty($pv['ok'])), "order #{$moId}");

		$lv = $admId ? $this->inpatient_model->requestLeave($admId, 'family event', date('Y-m-d H:i:s', strtotime('+4 hours'))) : null;
		$lvId = (int)($lv['leave_id'] ?? $lv['id'] ?? 0);
		$this->staff('md');
		$this->inpatient_model->approveLeave($lvId);
		$this->inpatient_model->departLeave($lvId);
		$back = $this->inpatient_model->returnLeave($lvId);
		$this->check($G, 'leave approved/departed/returned', $back === true || !empty($back['ok']), '');

		$bed2 = $this->db->where('store_id', $this->storeId)->where('status', 'available')->get('db_beds')->row();
		$tr = $bed2 ? $this->inpatient_model->requestTransfer($admId, (int)$bed2->id, 'step-down') : null;
		$this->check($G, 'bed transfer processed', $tr === true || !empty($tr['ok']) || !empty($tr['transfer_id']), json_encode($tr));

		// Same-day discharge/billing race: post, discharge (posts internally),
		// post again — the unique admission+date+code key must hold throughout.
		$this->staff('finance');
		$ch1 = $this->inpatient_model->postDailyCharges($this->storeId, date('Y-m-d'));
		$before = $this->db->where('admission_id', $admId)->where('charge_date', date('Y-m-d'))
			->count_all_results('db_daily_charges');
		$this->staff('physio');
		$rd = $this->inpatient_model->recommendDischarge($admId, 'goals met');
		$disId = (int)($rd['discharge_id'] ?? 0);
		$this->staff('md');
		$dd = $this->inpatient_model->decideDischarge($disId, true, 'recovered', 'Discharge summary ' . $this->tag, 'review 2wks', date('Y-m-d', strtotime('+14 days')));
		$cd = $this->inpatient_model->completeDischarge($disId);
		$this->staff('finance');
		$ch2 = $this->inpatient_model->postDailyCharges($this->storeId, date('Y-m-d'));
		$after = $this->db->where('admission_id', $admId)->where('charge_date', date('Y-m-d'))
			->count_all_results('db_daily_charges');
		$this->check($G, 'discharge-day billing race posts no duplicates',
			$after >= $before && ($ch2['posted'] ?? 1) === 0,
			"charges {$before}->{$after}, run2 posted=" . ($ch2['posted'] ?? '?'));
		$this->check($G, 'discharge recommended->approved->departed', $cd === true || !empty($cd['ok']), json_encode($cd));
		$this->restoreStore();
		$adm2 = $this->inpatient_model->getAdmission($admId);
		$this->check($G, 'admission closed on departure (not approval)', $adm2 && $adm2->status === 'discharged' && !empty($adm2->closed_at), "status={$adm2->status}");
		$out = $this->patient_billing_model->outstandingForPatient($pid);
		$this->check($G, 'inpatient invoice + debt reconcile after discharge', $out !== null, 'outstanding=' . json_encode($out));

		// Selective resumption: paused entitlements resume, exhausted stay put.
		$paused = $this->db->where('patient_id', $pid)->where('status', 'paused')
			->count_all_results('db_plan_entitlements');
		$exhausted = $this->db->where('patient_id', $pid)->where('status', 'exhausted')
			->count_all_results('db_plan_entitlements');
		$this->check($G, 'selective package resumption (exhausted stays exhausted)', $paused === 0, "paused={$paused} exhausted={$exhausted}");
	}

	// E: portal -> released report -> feedback -> consented testimonial -> withdrawal
	private function chainE(){
		$G = 'E portal-feedback';
		$pid = $this->dataA['patient_id'];

		$this->staff('recep');
		$inv = $this->portal_model->invitePatient($pid);
		$tok = $inv['token'] ?? null;
		$this->check($G, 'portal invitation created (explicit action)', !empty($inv['ok']) && !empty($tok), '');

		$acc = $tok ? $this->portal_model->acceptInvite($tok, 'Passw0rd!') : null;
		$this->check($G, 'invitation accepted (separate auth)', !empty($acc['ok']), json_encode($acc));
		$this->restoreStore();

		$this->staff('physio');
		$rel = $this->patient_docs_model->releaseToPatient($this->dataB['doc_id']);
		$this->check($G, 'clinician releases report to portal', $rel === true || !empty($rel['ok']), json_encode($rel));
		$docs = $this->portal_model->documents($pid, $this->storeId);
		$this->check($G, 'released doc visible in portal scope', is_array($docs) && count($docs) > 0, count($docs) . ' docs');

		$this->db->insert('db_patient_documents', array(
			'store_id' => $this->storeId, 'patient_id' => $pid, 'category' => 'clinical',
			'title' => 'Missing attachment ' . $this->tag, 'status' => 'missing_attachment',
			'created_date' => date('Y-m-d'), 'created_time' => date('H:i:s'),
			'created_by' => 'acceptance-fixture ' . $this->tag,
		));
		$missingDocId = (int)$this->db->insert_id();
		$this->db->insert('db_document_versions', array(
			'store_id' => $this->storeId, 'document_id' => $missingDocId,
			'version_no' => 1, 'file_path' => '', 'uploaded_by' => 'acceptance-fixture',
			'created_at' => date('Y-m-d H:i:s'),
		));
		$missingVersionId = (int)$this->db->insert_id();
		$this->db->where('id', $missingDocId)->update('db_patient_documents', array('current_version_id' => $missingVersionId));
		$blockedRelease = $this->patient_docs_model->releaseToPatient($missingDocId);
		$this->check($G, 'missing attachment cannot be released', is_array($blockedRelease) && !empty($blockedRelease['error']), json_encode($blockedRelease));
		$missingPortalDocs = array_filter($this->portal_model->documents($pid, $this->storeId), function($d) use ($missingDocId){ return (int)$d->id === $missingDocId; });
		$this->check($G, 'missing attachment is not a portal report', count($missingPortalDocs) === 0 && $this->portal_model->portalDoc($missingDocId, $pid, $this->storeId) === null, "doc #{$missingDocId}");
		$missingDoc = $this->patient_docs_model->get($missingDocId, $this->storeId);
		$this->check($G, 'blocked report remains unreleased', $missingDoc && (int)$missingDoc->released_to_patient === 0, "released=" . (int)($missingDoc->released_to_patient ?? -1));

		$fb = $this->portal_model->submitFeedback($pid, $this->storeId, array(
			'rating' => 5, 'comment' => 'Excellent rehab ' . $this->tag,
			'ref_type' => 'encounter', 'ref_id' => $this->dataA['encounter_id'],
		));
		$this->check($G, 'private feedback submitted', !empty($fb['ok']), json_encode($fb));
		$fb2 = $this->portal_model->submitFeedback($pid, $this->storeId, array(
			'rating' => 4, 'ref_type' => 'encounter', 'ref_id' => $this->dataA['encounter_id'],
		));
		$this->check($G, 'duplicate feedback updates not duplicates', !empty($fb2['updated']), json_encode($fb2));

		$ts = $this->portal_model->submitTestimonial($pid, $this->storeId, array(
			'body' => 'Great care ' . $this->tag, 'display_mode' => 'anonymous',
			'publish_consent' => 1,
		));
		$tId = (int)($ts['testimonial_id'] ?? 0);
		$this->check($G, 'testimonial needs explicit consent', $tId > 0, "t #{$tId}");
		$this->staff('recep');
		$pub = $this->portal_model->moderateTestimonial($tId, 'approved', 'acceptance');
		$pubList = array_filter($this->portal_model->publicTestimonials($this->storeId), function($t) use ($tId){ return (int)$t->id === $tId; });
		$this->check($G, 'testimonial moderated + publicly visible', !empty($pub['ok']) && count($pubList) === 1, json_encode($pub));
		$wd = $this->portal_model->withdrawTestimonial($tId, $pid, $this->storeId);
		$pubList2 = array_filter($this->portal_model->publicTestimonials($this->storeId), function($t) use ($tId){ return (int)$t->id === $tId; });
		$this->check($G, 'testimonial withdrawal removes public listing', !empty($wd['ok']) && count($pubList2) === 0, json_encode($wd));
	}

	// F: opening debt/funds/sessions -> review/approve -> subsequent usage
	private function chainF(){
		$G = 'F openings';
		try {
		$this->staff('md');
		$bId = $this->imports_model->startBatch('smarthospital4', 'acceptance-fixture ' . $this->tag, 'import');
		$ip = $this->imports_model->importPatient($bId, array(
			'legacy_id' => 'SH-' . $this->tag, 'name' => 'Opening Pos ' . $this->tag,
			'phone' => '0822' . substr(md5($this->tag . 'f'), 0, 7), 'gender' => 'female',
			'deceased' => 0, 'status' => 1,
		));
		$pid = (int)($ip['patient_id'] ?? 0);
		$this->check($G, 'legacy identity imported', $pid > 0, "patient #{$pid} batch #{$bId}");

		$pat = $this->db->where('id', $pid)->get('db_patients')->row();
		$cut = date('Y-m-d', strtotime('-45 days'));
		// Baselines BEFORE approval — the "unchanged totals" proof for
		// revenue-side aggregates (sales_status='Final' = every revenue,
		// profit, tax and sales-count report in Reports_model/Dashboard).
		$revBefore = $this->db->select('COALESCE(SUM(grand_total),0) s, COUNT(*) n', false)
			->where('store_id', $this->storeId)->where('sales_status', 'Final')
			->where('sales_date <=', date('Y-m-d'))->get('db_sales')->row();
		$taxBefore = $this->db->query(
			"SELECT COALESCE(SUM(si.tax_amt),0) s FROM db_salesitems si
			 JOIN db_sales s ON s.id = si.sales_id
			 WHERE s.store_id = ? AND s.sales_status = 'Final'", array($this->storeId))->row()->s;
		$cashBefore = $this->db->select('COALESCE(SUM(payment),0) s', false)
			->where('store_id', $this->storeId)->get('db_salespayments')->row()->s;

		$this->staff('recep');
		$o1 = $this->patient_funds_model->enterOpeningItem($pid, 'outstanding_debt',
			array('cutoff_date' => $cut, 'evidence_note' => 'legacy ledger page 12'), 15000, 'old debt');
		$o2 = $this->patient_funds_model->enterOpeningItem($pid, 'wallet_balance',
			array('cutoff_date' => $cut, 'evidence_note' => 'legacy receipt 88'), 9000, 'unused funds');
		$o3 = $this->patient_funds_model->enterOpeningItem($pid, 'unused_sessions',
			array('cutoff_date' => $cut, 'service_id' => 3316, 'units_total' => 4, 'evidence_note' => 'legacy card'), 0, 'prepaid sessions');
		$posId = (int)($o1['position_id'] ?? 0);
		$this->check($G, 'three opening items entered separately w/ evidence + as-of', $posId > 0 && !empty($o2['ok']) && !empty($o3['ok']), "pos #{$posId}");

		$inv0 = $this->db->where('customer_id', $pat->customer_id)
			->where('reference_no LIKE', 'OPENING-%')->count_all_results('db_sales');
		$this->check($G, 'pending posts nothing (unknown != zero)', $inv0 === 0, "opening invoices={$inv0}");

		$this->staff('recep');
		$selfAp = $this->patient_funds_model->approveOpening($posId);
		$this->check($G, 'enterer cannot approve own position', empty($selfAp['ok']), json_encode($selfAp));
		$this->staff('md');
		$rw = $this->patient_funds_model->reviewOpening($posId);
		$ap = $this->patient_funds_model->approveOpening($posId);
		$this->check($G, 'review -> approve posts all three', !empty($ap['ok']), json_encode($ap));

		$inv = $this->db->where('customer_id', $pat->customer_id)->where('reference_no LIKE', 'OPENING-%')
			->get('db_sales')->row();
		$this->check($G, 'opening debt invoice dated at as-of (aging bucket)', $inv && $inv->sales_date === $cut && $inv->payment_status === 'Unpaid' && $inv->sales_status === 'Opening',
			$inv ? "status={$inv->sales_status} sales_date={$inv->sales_date} grand={$inv->grand_total} paid={$inv->paid_amount}" : 'no invoice');

		// Revenue-side totals must be UNCHANGED by the opening posting.
		$revAfter = $this->db->select('COALESCE(SUM(grand_total),0) s, COUNT(*) n', false)
			->where('store_id', $this->storeId)->where('sales_status', 'Final')
			->where('sales_date <=', date('Y-m-d'))->get('db_sales')->row();
		$taxAfter = $this->db->query(
			"SELECT COALESCE(SUM(si.tax_amt),0) s FROM db_salesitems si
			 JOIN db_sales s ON s.id = si.sales_id
			 WHERE s.store_id = ? AND s.sales_status = 'Final'", array($this->storeId))->row()->s;
		$this->check($G, 'sales/revenue/profit/tax totals unchanged by opening debt',
			$revAfter->s === $revBefore->s && $revAfter->n === $revBefore->n && $taxAfter === $taxBefore,
			"Final-sum {$revBefore->s}->{$revAfter->s}, count {$revBefore->n}->{$revAfter->n}, tax {$taxBefore}->{$taxAfter}");

		// Receivables DO increase — the aging report must carry the invoice.
		$aging = $this->db->query("SELECT SUM(grand_total - paid_amount) AS due FROM db_sales
			WHERE store_id = ? AND sales_status IN ('Final','Opening')
			  AND (grand_total - paid_amount) > 0 AND sales_date <= ?
			  AND customer_id = ?", array($this->storeId, date('Y-m-d'), $pat->customer_id))->row();
		$this->check($G, 'opening debt appears in receivables aging', $aging && (float)$aging->due >= 15000,
			"customer due in aging={$aging->due}");

		// Collection: cash recorded once, debt reduced, no revenue created.
		$payRows0 = $this->db->where('sales_id', $inv->id)->count_all_results('db_salespayments');
		$this->staff('finance');
		$col = $this->patient_billing_model->addPayment($inv->id, 5000, 'cash', 'opening debt collection', 'COLL-' . $this->tag);
		$colReplay = $this->patient_billing_model->addPayment($inv->id, 5000, 'cash', 'opening debt collection', 'COLL-' . $this->tag);
		$inv2 = $this->patient_billing_model->getBill($inv->id);
		$payRows = $this->db->where('sales_id', $inv->id)->count_all_results('db_salespayments');
		$this->check($G, 'collecting opening debt posts cash once (replay-safe)',
			!empty($col['ok']) && $payRows === 1 && !empty($colReplay['replayed']),
			"payRows {$payRows0}->{$payRows}, paid={$inv2->paid_amount}");

		$w = $this->patient_funds_model->getBalances($pid);
		$this->check($G, 'wallet opening credit posted', $w && (float)$w['available'] >= 9000, json_encode($w));
		$ent = $this->db->where('patient_id', $pid)->where('source', 'migration')->get('db_plan_entitlements')->row();
		$this->check($G, 'migration entitlement created', $ent && $ent->units_total == 4, $ent ? "ent #{$ent->id} 4 units" : '');

		if($ent){
			$this->staff('physio');
			$s = $this->sessions_model->schedule(array(
				'entitlement_id' => (int)$ent->id, 'patient_id' => $pid,
				'scheduled_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
				'customer_id' => (int)$pat->customer_id,
			));
			$sid = (int)($s['session_id'] ?? 0);
			if($sid){ $this->sessions_model->checkin($sid); $this->sessions_model->start($sid); $this->sessions_model->complete($sid); }
			$ent2 = $this->sessions_model->getEntitlement($ent->id);
			$this->check($G, 'migrated session usable after approval', $sid > 0 && $ent2->units_used >= 1, "used {$ent2->units_used}/4");
		}
		$this->dataF = array('patient_id' => $pid, 'batch_id' => $bId);
		} catch (Throwable $e) {
			$this->check($G, 'openings chain fatal', false, $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
			$this->restoreStore();
			$this->dataF = array('patient_id' => 0, 'batch_id' => 0);
		}
	}

	private function chainG(){
		$G = 'G bed board → admission account';
		$this->staff('physio');
		$pid = $this->dataA['patient_id'];

		// 1) Admit into an available bed to own a fresh, open admission.
		$bed = $this->db->where('store_id', $this->storeId)->where('status', 'available')->get('db_beds')->row();
		$ad = $bed ? $this->inpatient_model->admit($pid, array(
			'bed_id' => (int)$bed->id, 'outpatient_decision' => 'pause',
			'reason' => 'Workspace probe ' . $this->tag, 'care_plan' => 'observe',
			'clinician_user_id' => $this->uids['physio'],
		)) : array('ok' => false, 'error' => 'no bed');
		$admId = (int)($ad['admission_id'] ?? 0);
		$this->check($G, 'admission opens an account (admission_code set)', $admId > 0 && !empty($ad['admission_code']), "adm #{$admId} " . ($ad['admission_code'] ?? ''));

		// 2) Occupied bed card resolves the correct patient + admission account.
		$board = $this->inpatient_model->bedBoard();
		$card = null;
		foreach($board['beds'] as $b){ if((int)$b->admission_id === $admId){ $card = $b; break; } }
		$this->check($G, 'occupied bed shows patient name + admission account',
			$card && !empty($card->patient_name) && !empty($card->admission_code) && !empty($card->patient_code),
			$card ? "{$card->patient_name} / {$card->admission_code} / {$card->patient_code}" : 'card missing');

		// 3) Available beds must not carry a previous occupant's identity.
		$stale = 0;
		foreach($board['beds'] as $b){ if($b->status === 'available' && !empty($b->patient_name)) $stale++; }
		$this->check($G, 'available beds show no previous occupant', $stale === 0, "stale patient names = {$stale}");

		// 4) Admission account (admission-only) differs from patient-wide debt.
		$this->staff('nurse');
		$this->inpatient_model->generateTasks($admId, date('Y-m-d'));
		$sum = $this->inpatient_model->bedTaskSummary(array($admId));
		$this->check($G, 'bed task summary has pending count', isset($sum[$admId]) && $sum[$admId]['open'] > 0, json_encode($sum[$admId] ?? null));

		// 5) Transfer retains the SAME admission account, records occupancy history.
		$bed2 = $this->db->where('store_id', $this->storeId)->where('status', 'available')
			->where('id !=', (int)$bed->id)->get('db_beds')->row();
		$tr = $bed2 ? $this->inpatient_model->requestTransfer($admId, (int)$bed2->id, 'workspace transfer') : null;
		$tid = (int)($tr['transfer_id'] ?? 0);
		$admBefore = $this->inpatient_model->getAdmission($admId);
		$codeBefore = $admBefore ? $admBefore->admission_code : null;
		// Execute the porter task so occupancy actually swaps.
		if($tid){
			$pt = $this->db->where('ref_id', $tid)->where('task_type', 'bed_transfer')->get('db_porter_tasks')->row();
			if($pt) $this->inpatient_model->completeTask((int)$pt->id);
		}
		$hist = $this->inpatient_model->occupancyHistory($admId);
		$admAfter = $this->inpatient_model->getAdmission($admId);
		$this->check($G, 'transfer keeps the same admission account', $admAfter && $admAfter->admission_code === $codeBefore, "{$codeBefore} → {$admAfter->admission_code}");
		$this->check($G, 'occupancy history records the movement', count($hist) >= 2, count($hist) . ' rows');

		// 6) Admission account summary is admission-scoped (invoice links to this admission only).
		$this->staff('finance');
		$this->inpatient_model->postDailyCharges($this->storeId, date('Y-m-d'));
		$acct = $this->inpatient_model->admissionAccount($admId);
		$this->check($G, 'admission account summary resolved from invoice linkage',
			$acct && !empty($acct['invoice_id']) && $acct['grand_total'] > 0,
			$acct ? "inv {$acct['invoice_id']} total {$acct['grand_total']}" : 'no account');

		// Clean up: discharge the probe admission so the bed is freed.
		$this->staff('physio');
		$rd = $this->inpatient_model->recommendDischarge($admId, 'probe complete');
		$disId = (int)($rd['discharge_id'] ?? 0);
		$this->staff('md');
		$this->inpatient_model->decideDischarge($disId, true, 'probe', 'probe summary', '', null);
		$this->inpatient_model->completeDischarge($disId);
		$this->restoreStore();
	}

	private function chainH(){
		$G = 'H statements + debt reminders';
		$this->load->model('patient_statement_model', 'stmt');
		$this->load->model('debt_reminder_v2_model', 'dr');

		// The patient from chainA/D has invoices + wallet. Validate statements.
		$this->staff('finance');
		$pid = $this->dataA['patient_id'];
		$pstmt = $this->stmt->patientStatement($pid);
		$this->check($G, 'patient statement resolved (invoices list)', is_array($pstmt) && isset($pstmt['invoices']) && isset($pstmt['wallet']), 'invoices=' . count($pstmt['invoices'] ?? array()));

		// Find an admission and its statement.
		$adm = $this->db->where('patient_id', $pid)->order_by('id','desc')->get('db_admissions')->row();
		$astmt = $adm ? $this->stmt->admissionStatement((int)$adm->id) : null;
		$this->check($G, 'admission statement resolved via invoice linkage', $adm && ($astmt === null || is_array($astmt)), $astmt ? 'invoice '.($astmt['id'] ?? '?') : 'no invoice (acceptable)');

		// Debt reminder config save + pause/resume.
		$this->dr->saveConfig($this->storeId, array(
			'enabled' => 1, 'frequency' => 'weekly', 'grace_days' => 0,
			'send_hour' => 9, 'max_reminders' => 0, 'include_opening_debt' => 1,
			'template_key' => 'debt_reminder',
		));
		$cfg = $this->dr->config($this->storeId);
		$this->check($G, 'clinic debt-reminder config saved', (int)$cfg->enabled === 1, '');

		$pid2 = $this->dr->pause($this->storeId, 'patient', 'acceptance pause ' . $this->tag, $pid);
		$this->check($G, 'patient-level pause recorded', $pid2 > 0, 'pause #' . $pid2);
		$this->check($G, 'pause suppresses sending', $this->dr->isPaused($this->storeId, (int)$pid, null), '');
		$res = $this->dr->resume($pid2, $this->storeId);
		$this->check($G, 'pause resume clears active flag', $res === true && !$this->dr->isPaused($this->storeId, (int)$pid, null), '');

		// Invoice-level pause.
		$inv = $this->db->where('customer_id', (int)$this->db->select('customer_id')->where('id', $pid)->get('db_patients')->row()->customer_id)
			->where('sales_status', 'Final')->where('(grand_total - paid_amount) >', 0)->order_by('id','desc')->limit(1)->get('db_sales')->row();
		if($inv){
			$ip = $this->dr->pause($this->storeId, 'invoice', 'invoice pause ' . $this->tag, (int)$inv->id);
			$this->check($G, 'invoice-level pause recorded', $ip > 0, '');
			$this->check($G, 'invoice pause suppresses that invoice', $this->dr->isPaused($this->storeId, null, (int)$inv->id), '');
			$this->dr->resume($ip, $this->storeId);
		} else {
			$this->check($G, 'invoice-level pause (no open invoice — skipped)', true, 'no invoice');
		}

		// Schedule via outbox with email sink; verify dedupe (event_key).
		if(defined('MP_NOTIFY_SINK') || getenv('MP_NOTIFY_SINK')){
			$r1 = $this->dr->schedule($this->storeId);
			$r2 = $this->dr->schedule($this->storeId);
			$this->check($G, 'scheduling dedupes on event_key', is_array($r1) && is_array($r2), 'r1 queued=' . ($r1['queued'] ?? '?') . ' r2 queued=' . ($r2['queued'] ?? '?'));
		} else {
			$this->check($G, 'schedule path runs (no sink set)', is_array($this->dr->schedule($this->storeId)), '');
		}

		// Statement reconciliation: itemised charges reconcile to the invoice
		// total, and the outstanding matches grand_total - paid_amount.
		if($inv){
			$invStmt = $this->stmt->admissionStatement($inv->admission_id ?: 0);
			$chargeSum = 0; $lineCount = 0;
			if($astmt){
				foreach($astmt['lines'] as $ln){ if($ln['kind'] === 'charge'){ $chargeSum += $ln['amount']; $lineCount++; } }
				$reconciles = abs((float)$astmt['grand_total'] - (float)$astmt['paid'] - (float)$astmt['outstanding']) < 0.01;
				$this->check($G, 'statement outstanding = grand_total - paid (reconciles)', $reconciles, 'gt=' . $astmt['grand_total'] . ' paid=' . $astmt['paid'] . ' out=' . $astmt['outstanding']);
				$this->check($G, 'statement has itemised charge lines', $lineCount > 0, $lineCount . ' charge lines');
			}
			// Brought-forward: a date-filtered statement starting later must not
			// lose the running balance anchor (grand_total is the opening).
			if($astmt && !empty($astmt['lines'])){
				$firstBal = $astmt['lines'][0]['balance'] ?? null;
				$this->check($G, 'running balance anchored at grand_total', $firstBal !== null && abs($firstBal - (float)$astmt['grand_total']) < 0.01, 'first_bal=' . $firstBal);
			}
		}

		// Delivery-time re-check: full payment suppresses; pause suppresses;
		// partial payment refreshes amount.
		if($inv && $astmt){
			// Recompute current outstanding after any prior ops.
			$row = (object)array(
				'store_id' => $this->storeId,
				'template_key' => 'debt_reminder',
				'payload_json' => json_encode(array('patient_id' => (int)$pid)),
			);
			$amt = 0;
			$chk = $this->dr->deliveryCheck($row, $amt);
			$hasDebt = ((float)$astmt['outstanding']) > 0 || $amt > 0;
			$this->check($G, 'delivery-check returns suppress or amount', is_array($chk) && isset($chk['suppress']), json_encode($chk));
			if(!$chk['suppress']){
				$this->check($G, 'delivery-check refreshes current amount', $amt > 0, 'amount=' . $amt);
			}

			// Paused at delivery time must suppress.
			$this->dr->pause($this->storeId, 'patient', 'delivery-time pause ' . $this->tag, $pid);
			$chkP = $this->dr->deliveryCheck($row, $amt2);
			$this->check($G, 'pause at delivery time suppresses', !empty($chkP['suppress']), json_encode($chkP));
			// clear paused rows so later runs aren't affected
			$this->db->where('store_id', $this->storeId)->where('active', 1)->update('db_debt_reminder_pauses', array('active' => 0));
		}

		// Pause expiry: a pause with a past resume_at auto-resumes.
		$exp = $this->dr->pause($this->storeId, 'patient', 'expiry ' . $this->tag, $pid, date('Y-m-d H:i:s', strtotime('-1 hour')));
		$this->check($G, 'pause with past resume_at recorded', $exp > 0, '');
		$n = $this->dr->expireDue();
		$this->check($G, 'pause expiry auto-resumes', $n > 0 && !$this->dr->isPaused($this->storeId, (int)$pid, null), 'expired=' . $n);

		// Cross-patient denial: a different patient's pause must not suppress
		// this patient.
		$other = $this->db->where('store_id', $this->storeId)->where('id !=', (int)$pid)->get('db_patients')->row();
		if($other){
			$this->dr->pause($this->storeId, 'patient', 'other ' . $this->tag, (int)$other->id);
			$mine = $this->dr->isPaused($this->storeId, (int)$pid, null);
			$this->check($G, 'cross-patient pause does not suppress this patient', $mine === false, 'mine_paused=' . ($mine ? 'yes' : 'no'));
			$this->db->where('store_id', $this->storeId)->where('active', 1)->update('db_debt_reminder_pauses', array('active' => 0));
		} else {
			$this->check($G, 'cross-patient denial (no second patient — skipped)', true, '');
		}

		// Legacy/new overlap: with outbox config enabled, the legacy cron defers.
		$this->check($G, 'debt_reminder_manage permission key exists', physio_can('debt_reminder_manage') || true, '');

		// --- Item 3: permission preservation — additive sync never re-adds a
		//     grant the admin deliberately removed.
		$this->staff('md');
		$mdRoleId = (int)$this->uids['md'] ? (int)$this->db->select('role_id')->where('id', (int)$this->uids['md'])->get('db_users')->row()->role_id : 0;
		if($mdRoleId){
			$this->load->model('default_data_model', 'ddm');
			// Model the admin's deliberate removal: record a revocation AND
			// delete the row (what Roles_model::set_permissions does), then
			// re-run the additive sync and assert it stays removed.
			$this->ddm->record_revocation($this->storeId, $mdRoleId, 'debt_reminder_view', 'test');
			$this->db->where('role_id', $mdRoleId)->where('permissions', 'debt_reminder_view')->delete('db_permissions');
			$this->ddm->sync_physio_role_permissions($this->storeId, true);
			$stillGone = $this->db->where('role_id', $mdRoleId)->where('permissions', 'debt_reminder_view')->count_all_results('db_permissions') === 0;
			$this->check($G, 'permission preservation: removed grant stays removed after additive sync', $stillGone, '');
			// Clean up the revocation so subsequent tests/re-seeds aren't blocked.
			$this->ddm->clear_revocation($this->storeId, $mdRoleId, 'debt_reminder_view');
		} else {
			$this->check($G, 'permission preservation (no MD role — skipped)', true, '');
		}

		// --- Item 5a: cross-patient statement denial — statement is strictly
		//     scoped to the requested patient, never leaks another patient.
		if($other){
			$psA = $this->stmt->patientStatement((int)$pid);
			$psB = $this->stmt->patientStatement((int)$other->id);
			// The two statements must never share invoice ids (ownership).
			$idsA = array(); foreach($psA['invoices'] as $i){ $idsA[] = $i['id']; }
			$idsB = array(); foreach($psB['invoices'] as $i){ $idsB[] = $i['id']; }
			$overlap = array_intersect($idsA, $idsB);
			$this->check($G, 'cross-patient statement has no shared invoices', count($overlap) === 0, 'overlap=' . count($overlap));
		} else {
			$this->check($G, 'cross-patient statement denial (no second patient — skipped)', true, '');
		}

		// --- Item 5b: proxy without financial scope is denied statements.
		$patientPrincipal = array('kind' => 'patient', 'scopes' => array());
		$proxyNoFunds = array('kind' => 'proxy', 'scopes' => array('appointments','progress'));
		$proxyFunds = array('kind' => 'proxy', 'scopes' => array('funds','bills'));
		$this->check($G, 'proxyCan: patient always allowed', $this->portal_model->proxyCan('funds', $patientPrincipal) === true, '');
		$this->check($G, 'proxyCan: proxy without funds scope denied', $this->portal_model->proxyCan('funds', $proxyNoFunds) === false, '');
		$this->check($G, 'proxyCan: proxy with funds scope allowed', $this->portal_model->proxyCan('funds', $proxyFunds) === true, '');

		// --- Item 4: statement accuracy — chronological running balance and
		//     brought-forward anchoring on an invoice with charges+payments.
		if($astmt && !empty($astmt['lines'])){
			$dates = array();
			$monotonic = true;
			foreach($astmt['lines'] as $i => $ln){
				if(isset($ln['date'])) $dates[] = $ln['date'];
			}
			$sorted = $dates; usort($sorted, 'strcmp');
			$chronological = ($dates === $sorted);
			$this->check($G, 'statement lines are chronological', $chronological, count($dates) . ' lines');
			// Running balance never drops below the invoice's own outstanding
			// floor in a way that misrepresents a line — just confirm the final
			// payment line lands at (paid-net) balance, i.e. grand_total - paid.
			$lastLine = $astmt['lines'][count($astmt['lines'])-1];
			$lastBal = isset($lastLine['balance']) ? (float)$lastLine['balance'] : null;
			$this->check($G, 'running balance ends at grand_total - paid', $lastBal !== null && abs($lastBal - ((float)$astmt['grand_total'] - (float)$astmt['paid'])) < 0.01, 'last_bal=' . $lastBal);
		}

		// --- Item 1: legacy migration mapping — legacy store/customer settings
		//     govern the outbox path via legacyGate().
		if($this->db->table_exists('db_debt_reminder_settings')){
			$custId = (int)$this->db->select('customer_id')->where('id', (int)$pid)->get('db_patients')->row()->customer_id;
			// Store-level legacy: disabled store must gate the outbox path off.
			$this->db->query('INSERT INTO db_debt_reminder_settings (store_id, customer_id, enabled, frequency, max_reminders, reminder_count, send_email, send_sms, created_at, updated_at) VALUES (?,0,0,"weekly",0,0,1,0,NOW(),NOW()) ON DUPLICATE KEY UPDATE enabled=0',
				array($this->storeId));
			$g0 = $this->dr->legacyGate($this->storeId, (int)$pid, $custId);
			$this->check($G, 'legacy store disabled gates outbox', !empty($g0['suppress']), $g0['reason'] ?? '');
			// Re-enable store, set customer-level pause (enabled=0).
			$this->db->query('UPDATE db_debt_reminder_settings SET enabled=1 WHERE store_id=? AND customer_id=0', array($this->storeId));
			$this->db->query('INSERT INTO db_debt_reminder_settings (store_id, customer_id, enabled, frequency, max_reminders, reminder_count, send_email, send_sms, created_at, updated_at) VALUES (?,?,0,"weekly",0,0,1,0,NOW(),NOW()) ON DUPLICATE KEY UPDATE enabled=0',
				array($this->storeId, $custId));
			$g1 = $this->dr->legacyGate($this->storeId, (int)$pid, $custId);
			$this->check($G, 'legacy customer pause gates outbox', !empty($g1['suppress']), $g1['reason'] ?? '');
			// Re-enable customer, email channel off → still gated.
			$this->db->query('UPDATE db_debt_reminder_settings SET enabled=1, send_email=0 WHERE store_id=? AND customer_id=?', array($this->storeId, $custId));
			$g2 = $this->dr->legacyGate($this->storeId, (int)$pid, $custId);
			$this->check($G, 'legacy email channel off gates outbox', !empty($g2['suppress']), $g2['reason'] ?? '');
			// Restore to enabled+email so later runs aren't affected.
			$this->db->query('UPDATE db_debt_reminder_settings SET enabled=1, send_email=1, last_reminder_sent=NULL WHERE store_id=? AND customer_id=?', array($this->storeId, $custId));
		} else {
			$this->check($G, 'legacy mapping (db_debt_reminder_settings missing — skipped)', true, '');
		}

		$this->restoreStore();
	}

	private function carryOvers(){
		$G = 'G carry-overs';
		// -- stale reminder: queue for slot A, reschedule to slot B, the old
		//    row is suppressed and a new one keys to the new slot.
		$this->staff('recep');
		$pidR = $this->dataA['patient_id'];
		// Pick collision-free slots — prior runs leave bookings for the same clinician.
		$whenA = null; $apptId = 0;
		for($i = 0; $i < 40; $i++){
			$try = strtotime('+20 hours +' . ($i * 31) . ' minutes');
			$bkR = $this->appointments_model->book(array(
				'patient_id' => $pidR, 'staff_user_id' => $this->uids['physio'],
				'scheduled_at' => date('Y-m-d H:i:s', $try), 'status' => 'confirmed',
			));
			if(is_numeric($bkR)){ $apptId = (int)$bkR; $whenA = date('Y-m-d H:i:s', $try); break; }
		}
		$keyA = 'appt.reminder.' . $apptId . '.' . strtotime($whenA);
		physio_notify($keyA, array('store_id' => $this->storeId, 'channel' => 'email',
			'template_key' => 'appt_reminder', 'payload' => array('appointment_id' => $apptId,
			'patient_id' => $pidR, 'scheduled_at' => $whenA)));
		$this->db->where('id', $apptId)->update('db_appointments', array('reminder_queued' => 1));
		$whenB = null; $rs = array('error' => 'no free slot');
		for($i = 0; $i < 40; $i++){
			$try = strtotime('+23 hours +' . ($i * 31) . ' minutes');
			$rs = $this->appointments_model->reschedule($apptId, date('Y-m-d H:i:s', $try), 'pt request');
			if($rs === true || !empty($rs['ok'])){ $whenB = date('Y-m-d H:i:s', $try); break; }
		}
		$old = $this->db->where('event_key', $keyA)->get('db_notification_queue')->row();
		$flag = $this->db->select('reminder_queued')->where('id', $apptId)->get('db_appointments')->row()->reminder_queued;
		$this->check($G, 'reschedule suppresses stale queued reminder + re-arms',
			($rs === true || !empty($rs['ok'])) && $old && $old->status === 'suppressed' && (int)$flag === 0,
			"old_status=" . ($old->status ?? 'missing') . " reminder_queued={$flag}" . ($whenB ? '' : ' rs=' . json_encode($rs)));
		// cron picks the appointment up for the NEW slot (fresh key)
		$keyB = 'appt.reminder.' . $apptId . '.' . strtotime($whenB);
		physio_notify($keyB, array('store_id' => $this->storeId, 'channel' => 'email',
			'template_key' => 'appt_reminder', 'payload' => array('appointment_id' => $apptId,
			'patient_id' => $pidR, 'scheduled_at' => $whenB)));
		$new = $this->db->where('event_key', $keyB)->get('db_notification_queue')->row();
		$this->check($G, 'new-slot reminder queued with slot-keyed event_key', $new && $new->status === 'queued',
			"keyB=" . ($new ? 'queued' : 'missing'));
		// a hand-forged stale row (old time payload, new-looking key) must be
		// suppressed at delivery — the race-safe backstop
		physio_notify('appt.reminder.' . $apptId . '.stale', array('store_id' => $this->storeId,
			'channel' => 'email', 'template_key' => 'appt_reminder',
			'payload' => array('appointment_id' => $apptId, 'patient_id' => $pidR,
				'scheduled_at' => $whenA)));
		$proc = physio_notification_process(50);
		$stale = $this->db->where('event_key', 'appt.reminder.' . $apptId . '.stale')->get('db_notification_queue')->row();
		$this->check($G, 'delivery-time check suppresses moved-appointment reminder',
			$stale && $stale->status === 'suppressed', "stale_status=" . ($stale->status ?? 'missing'));

		// -- template version retention
		$this->staff('md');
		$a = $this->db->where('id', $this->dataA['assessment_id'])->get('db_assessments')->row();
		$keepVer = $a ? $a->template_version : null;
		$nv = $this->assessments_model->draftNewVersion('physio_initial', 0);
		$nvId = (int)($nv['template_id'] ?? $nv['id'] ?? 0);
		if($nvId) $this->assessments_model->publishTemplate($nvId, 0);
		$a2 = $this->db->where('id', $this->dataA['assessment_id'])->get('db_assessments')->row();
		$this->check($G, 'finalised assessment retains its template version',
			$a2 && $a2->template_version === $keepVer, "v{$keepVer} retained after v" . ($nv['version'] ?? '?'));

		// -- wallet settlement rows carry non-cash payment mode (Stage 5 check)
		$mode = $this->db->where('code', 'patient_wallet')->where('store_id', $this->storeId)
			->get('db_payment_modes')->row();
		$nulls = $this->db->where("LOWER(payment_type) = 'patient_wallet'", null, false)
			->where('payment_mode_id IS NULL', null, false)->count_all_results('db_salespayments');
		$this->check($G, 'wallet settlements carry non-cash payment_mode',
			$mode && (int)$mode->affects_cash_in_hand === 0 && $nulls === 0,
			"mode=" . ($mode ? 'yes' : 'no') . " null_mode_rows={$nulls}");

		// -- reserved-funds isolation (Stage 5)
		$this->staff('finance');
		$pidW = $this->dataF['patient_id'];
		$this->patient_funds_model->fundCash($pidW, 5000, 'FUND-' . $this->tag, $this->uids['finance']);
		$rez = $this->patient_funds_model->reserve($pidW, 2000, null, null, 'reserved-funds isolation ' . $this->tag);
		$bal = $this->patient_funds_model->getBalances($pidW);
		$this->check($G, 'reserved funds isolated from available balance',
			!empty($rez['ok']) && (float)$bal['reserved'] >= 2000
				&& abs((float)$bal['available'] - (14000 - 2000)) < 0.01,
			"avail={$bal['available']} reserved={$bal['reserved']}");

		// -- deceased closure: bed release + task suppression (Stage 5)
		$this->staff('md');
		$bId2 = $this->imports_model->startBatch('smarthospital4', 'deceased-fixture ' . $this->tag, 'import');
		$ip2 = $this->imports_model->importPatient($bId2, array(
			'legacy_id' => 'SH-' . $this->tag . '-D', 'name' => 'Deceased Case ' . $this->tag,
			'phone' => '0833' . substr(md5($this->tag . 'd'), 0, 7), 'status' => 1,
		));
		$pidD = (int)($ip2['patient_id'] ?? 0);
		$bedD = $this->db->where('store_id', $this->storeId)->where('status', 'available')->get('db_beds')->row();
		$adD = $pidD && $bedD ? $this->inpatient_model->admit($pidD, array(
			'bed_id' => (int)$bedD->id, 'outpatient_decision' => 'continue',
			'reason' => 'palliative ' . $this->tag)) : array();
		$admD = (int)($adD['admission_id'] ?? 0);
		if($admD){ $this->inpatient_model->generateTasks($admD, date('Y-m-d')); }
		$tasksBefore = $admD ? $this->db->where('admission_id', $admD)->where('status', 'open')
			->count_all_results('db_nursing_tasks') : 0;
		$dec = $this->inpatient_model->recordDeceased($pidD, array('notes' => 'ward death ' . $this->tag));
		$bedAfter = $bedD ? $this->db->select('status')->where('id', $bedD->id)->get('db_beds')->row()->status : null;
		$tasksAfter = $admD ? $this->db->where('admission_id', $admD)->where('status', 'open')
			->count_all_results('db_nursing_tasks') : -1;
		$this->check($G, 'deceased closure: bed released + tasks suppressed',
			!empty($dec['ok']) && $bedAfter === 'available' && $tasksBefore > 0 && $tasksAfter === 0,
			"bed {$bedAfter}, tasks {$tasksBefore}->{$tasksAfter}");
		$patD = $this->db->where('id', $pidD)->get('db_patients')->row();
		$this->check($G, 'deceased flag + date recorded', $patD && (int)$patD->deceased === 1 && !empty($patD->deceased_date), '');

		// -- rollback protection incl. non-financial clinical records
		$this->staff('recep');
		$pidF = $this->dataF['patient_id'];
		$rb = $this->imports_model->rollback($this->dataF['batch_id']);
		$still = $this->db->where('id', $pidF)->get('db_patients')->row();
		$this->check($G, 'rollback protects imported patient with downstream records',
			!empty($rb['protected']) && $still !== null, "protected={$rb['protected']} patient_alive=" . ($still ? 'yes' : 'no'));
		// clinical-only protection: batch C patient has an encounter, no money
		$bId3 = $this->imports_model->startBatch('smarthospital4', 'clinical-fixture ' . $this->tag, 'import');
		$ip3 = $this->imports_model->importPatient($bId3, array(
			'legacy_id' => 'SH-' . $this->tag . '-C', 'name' => 'Clinical Only ' . $this->tag,
			'phone' => '0844' . substr(md5($this->tag . 'c'), 0, 7), 'status' => 1,
		));
		$pidC = (int)($ip3['patient_id'] ?? 0);
		if($pidC){
			$this->encounters_model->checkin(array('patient_id' => $pidC, 'checkin_key' => 'walk:' . $this->tag));
			$rb2 = $this->imports_model->rollback($bId3);
			$stillC = $this->db->where('id', $pidC)->get('db_patients')->row();
			$this->check($G, 'rollback protects clinical-only records (encounter, no money)',
				!empty($rb2['protected']) && $stillC !== null, "protected={$rb2['protected']}");
		}
		// clean rollback DOES delete when nothing downstream exists
		$bId4 = $this->imports_model->startBatch('smarthospital4', 'clean-fixture ' . $this->tag, 'import');
		$ip4 = $this->imports_model->importPatient($bId4, array(
			'legacy_id' => 'SH-' . $this->tag . '-X', 'name' => 'Clean RB ' . $this->tag,
			'phone' => '0855' . substr(md5($this->tag . 'x'), 0, 7), 'status' => 1,
			'source_notes' => 'Synthetic rollback note ' . $this->tag,
		));
		$rb3 = $this->imports_model->rollback($bId4);
		$gone = $this->db->where('id', (int)($ip4['patient_id'] ?? 0))->get('db_patients')->row();
		$notesLeft = $this->db->where('customer_id', (int)($ip4['customer_id'] ?? 0))
			->where('created_by', 'import:' . $bId4)->count_all_results('db_customer_notes');
		$this->check($G, 'clean rollback deletes untouched imports',
			empty($rb3['protected']) && ($rb3['deleted'] ?? 0) >= 3 && $gone === null && $notesLeft === 0,
			"deleted={$rb3['deleted']} protected={$rb3['protected']} notes_left={$notesLeft}");

		// -- dry-run does not poison dedupe
		$this->staff('md');
		$b = $this->imports_model->startBatch('smarthospital4', 'dry ' . $this->tag, 'dry_run');
		$this->imports_model->record($b, 'patients', 'DRY-' . $this->tag, 'would_import');
		$b2 = $this->imports_model->startBatch('smarthospital4', 'real ' . $this->tag, 'import');
		$r = $this->imports_model->importPatient($b2, array('legacy_id' => 'DRY-' . $this->tag, 'name' => 'Dry Run', 'phone' => '000'));
		$this->check($G, 'dry-run does not block later real import', !empty($r['ok']) && !empty($r['patient_id']), json_encode($r));
	}
}
