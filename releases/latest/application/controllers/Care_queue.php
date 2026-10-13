<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Care Queue — the day's live patient flow.
 *
 * check-in   : one encounter per arrival; idempotent by checkin_key.
 *              Booked: checkin_key = "appt:{id}". Walk-in: caller supplies a
 *              uuid — a retry returns the same encounter.
 *              Check-in never deducts a session or posts a charge.
 * move       : queue stage transitions, permission-gated per target stage.
 * vitals     : nursing intake values on the open encounter.
 */
class Care_queue extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		if(!function_exists('physio_enabled')){ $this->load->helper('physio'); }
		if(!physio_enabled() || !mp_feature_enabled('patient_registry')){
			$this->show_feature_not_activated('patient_registry');
			return;
		}
		if(!physio_can('care_queue_view')){
			$this->show_access_denied_page();
			return;
		}
		$this->load->model('encounters_model', 'encounters');
		$this->load->model('patients_model', 'patients');
		$this->load->model('appointments_model', 'appts');
		$this->load->model('assessments_model', 'assessments');
		$this->load->model('investigations_model', 'investigations');
		$this->load->model('consents_model', 'consents');
		$this->load->model('patient_docs_model', 'docs');
	}

	private function _json($arr){
		$this->output->set_content_type('application/json')->set_output(json_encode($arr));
	}

	public function index(){
		$storeId = get_current_store_id();
		$branchIds = physio_can('clinical_cross_branch') ? null : physio_branch_ids();
		$today = date('Y-m-d');
		// Today's arrivals not yet checked in — reception's pending list
		$waiting = $this->appts->getAppointments($storeId, array(
			'date' => $today,
			'branch_ids' => physio_can('clinical_cross_branch') ? null : physio_branch_ids(),
		));
		$arrivals = array();
		foreach($waiting as $a){
			if(in_array($a->status, array('confirmed','requested','proposed')) && !$a->arrived_at) $arrivals[] = $a;
		}
		// How far back the board reaches for visits still ON the queue.
		// 365 = this year, 0 = every visit still open, however old.
		$days = in_array((int)$this->input->get('days'), array(1, 7, 30, 365, 0), TRUE) ? (int)$this->input->get('days') : 1;
		$queue = $this->encounters->getQueue($storeId, $branchIds, $days);
		// Open visits the window is hiding — the dashboard tile counts these too,
		// so the board has to admit they exist instead of looking clear.
		$queueHidden = $this->encounters->countQueueOutside($storeId, $branchIds, $days);
		// And open visits at branches this login cannot work — same reason the
		// tile and the board can disagree, so the board names them too.
		$queueElsewhere = $this->encounters->countQueueElsewhere($storeId, $branchIds);

		// Staff who can treat a patient — the "send to" picker. THERAPIST
		// profiles only: every clinical role holds encounters_add, so a
		// permission-only list would offer the nurse who cannot treat.
		$therapists = physio_therapist_select($storeId, 'first_name');
		$therapistIds = array();
		foreach($therapists as $t){ $therapistIds[(int)$t->id] = true; }
		$me = (int)$this->session->userdata('inv_userid');
		$isTherapist = isset($therapistIds[$me]);
		// "My patients" — a therapist's own view: the patients sent to them, plus
		// the ones nobody has been sent to yet, because those are still there for
		// whoever takes them.
		$mine = ($isTherapist && (int)$this->input->get('mine')) ? 1 : 0;
		if($mine){
			foreach($queue as $st => $cards){
				$queue[$st] = array_values(array_filter($cards, function($c) use ($me){
					$who = (int)($c->clinician_user_id ?? 0);
					return $who === 0 || $who === $me;
				}));
			}
		}
		// Board cards show live bill state per visit — bills raised at the
		// waiting-for-nurse desk are the reason that stage exists.
		$billSummary = array();
		if(physio_can('patient_billing_view') && $this->db->field_exists('encounter_id', 'db_sales')){
			$encIds = array();
			foreach($queue as $cards){ foreach($cards as $c){ $encIds[] = (int)$c->id; } }
			if($encIds){
				$rows = $this->db->select('encounter_id, COUNT(*) AS n, SUM(grand_total) AS total, SUM(paid_amount) AS paid')
					->where('store_id', $storeId)->where_in('encounter_id', $encIds)
					->group_by('encounter_id')->get('db_sales')->result();
				foreach($rows as $r){
					$due = round((float)$r->total - (float)$r->paid, 2);
					$billSummary[(int)$r->encounter_id] = array(
						'count' => (int)$r->n,
						'total' => round((float)$r->total, 2),
						'paid'  => round((float)$r->paid, 2),
						'due'   => $due,
						'state' => $due <= 0.009 ? 'paid' : ((float)$r->paid > 0 ? 'partial' : 'unpaid'),
					);
				}
			}
		}
		// A session ticket is only for a patient who still has paid sessions
		// owing on a package — one lookup for the whole board.
		$patientIds = array();
		foreach($queue as $cards){ foreach($cards as $c){ $patientIds[] = (int)$c->patient_id; } }
		$this->load->model('Sessions_model', 'sessions');
		$sessionEnt = $this->sessions->activeEntitlements($patientIds);
		// Sessions booked on the diary whose day has arrived (or passed) and
		// whose patient is not on the board yet — reception checks them in here,
		// which raises the ticket and sends them to vitals like any arrival.
		$booked = $this->sessions->dueBookings($storeId, $today);

		// Staff who can take a treatment patient — the tasked-nurse picker.
		// Nurse PROFILES only: vitals_add alone would list the store admin.
		$nurses = physio_nurse_select($storeId, 'first_name');

		$data = array_merge($this->data, array(
			'page_title' => 'Care Queue',
			'queue'      => $queue,
			'queue_days' => $days,
			'queue_hidden' => $queueHidden,
			'queue_elsewhere' => $queueElsewhere,
			'queue_mine' => $mine,
			'is_therapist' => $isTherapist,
			'therapists' => $therapists,
			'arrivals'   => $arrivals,
			'patients'   => $this->patients->getPatients($storeId, 'active', '', 500),
			'branches'   => physio_branch_ids()
				? $this->db->where('store_id', $storeId)->where_in('id', physio_branch_ids())->get('db_warehouse')->result()
				: array(),
			'services'   => physio_can_any(array('patient_billing_add','encounters_finalize'))
				? $this->db->select('id, item_name, sales_price')->where('store_id', $storeId)
					->where('service_bit', 1)->where('status', 1)->order_by('item_name')->get('db_items')->result()
				: array(),
			'bill_summary' => $billSummary,
			'session_ent'  => $sessionEnt,
			'booked'       => $booked,
			'nurses'       => $nurses,
			'can_checkin' => physio_can('care_checkin'),
			'can_vitals'  => physio_can('vitals_add'),
			'can_physio'  => physio_can('encounters_add'),
			'can_route'   => physio_can_any(array('care_checkin','vitals_add','encounters_add')),
			'can_finance' => physio_can('patient_funds_view'),
			'can_bill'    => physio_can('patient_billing_add'),
			'can_bill_view' => physio_can('patient_billing_view'),
		));
		$data['content'] = $this->load->view('care_queue/index', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/**
	 * POST checkin — appointment_id OR session_id OR (patient_id + checkin_key
	 * [+ branch]). Retries are safe: the unique (store_id, checkin_key) returns
	 * the same encounter; status 'already' flags the dedup. A session_id is a
	 * booked treatment session arriving on its day
	 */
	public function checkin(){
		if(!physio_can('care_checkin')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$warehouseId = (int)$this->input->post('warehouse_id');
		if($warehouseId && !physio_can_branch($warehouseId)){
			$this->_json(array('status' => 'error', 'message' => 'Branch not in your scope')); return;
		}
		$res = $this->encounters->checkin(array(
			'appointment_id'    => (int)$this->input->post('appointment_id') ?: null,
			'session_id'        => (int)$this->input->post('session_id') ?: null,
			'patient_id'        => (int)$this->input->post('patient_id') ?: null,
			'checkin_key'       => trim($this->input->post('checkin_key', TRUE) ?: ''),
			'warehouse_id'      => $warehouseId ?: null,
			'clinician_user_id' => (int)$this->input->post('clinician_user_id') ?: null,
			'note'              => trim($this->input->post('note', TRUE) ?: '') ?: null,
		));
		if(isset($res['error'])){
			$this->_json(array('status' => 'error', 'message' => $res['error']));
		} else {
			$this->_json(array('status' => 'success',
				'message' => !empty($res['already']) ? 'Already checked in — same visit returned' : 'Checked in to waiting list',
				'encounter_id' => $res['encounter_id'], 'already' => !empty($res['already'])));
		}
	}

	public function move($id = 0){
		$to = trim($this->input->post('to', TRUE) ?: '');
		$note = trim($this->input->post('note', TRUE) ?: '') ?: null;
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$res = $this->encounters->moveStage((int)$id, $to, $note);
		if($res !== true){ $this->_json($res); return; }
		$ok = array('status' => 'success', 'message' => 'Moved to ' . str_replace('_', ' ', $to));
		/* A treatment visit that closed has drawn its session — the natural
		   moment to arrange the next one of the course, offered right here so
		   nobody has to remember to go looking for it later. */
		if($to === 'closed' && ($enc->visit_type ?? null) === 'treatment'){
			$this->load->model('Sessions_model', 'sessions');
			$sess = $this->db->select('entitlement_id')->where('encounter_id', (int)$id)
				->where('store_id', get_current_store_id())->order_by('id', 'desc')->get('db_treatment_sessions')->row();
			$next = $this->sessions->nextOffer((int)$enc->patient_id, $sess ? (int)$sess->entitlement_id : null);
			if($next){
				$who = $this->db->select('c.customer_name')->from('db_patients p')
					->join('db_customers c', 'c.id = p.customer_id AND c.store_id = p.store_id', 'left')
					->where('p.id', (int)$enc->patient_id)->get()->row();
				$ok['next'] = array(
					'entitlement_id' => $next->entitlement_id,
					'fee'            => $next->fee,
					'sessions_left'  => $next->sessions_left,
					'treatment_name' => $next->treatment_name,
					'patient_name'   => $who ? $who->customer_name : '',
				);
			}
		}
		$this->_json($ok);
	}

	/**
	 * POST set_visit_type — the waiting-for-nurse desk decides what this visit
	 * IS (assessment or treatment session) and, for treatment, which nurse is
	 * tasked with the patient. That choice forks the rest of the journey.
	 */
	public function set_visit_type($id = 0){
		if(!physio_can('care_checkin') && !physio_can('vitals_add')){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$res = $this->encounters->setVisitType((int)$id,
			trim($this->input->post('visit_type', TRUE) ?: ''),
			(int)$this->input->post('nurse_user_id') ?: null);
		$this->_json($res === true
			? array('status' => 'success', 'message' => 'Visit type saved — the patient can move on')
			: $res);
	}

	/**
	 * POST book_session — book the patient's NEXT treatment session on the
	 * diary (the connect between finishing one session and starting the next).
	 * Same booking the Sessions screen makes, reachable from the Care Queue so
	 * the desk never has to leave the patient's card to arrange the return.
	 */
	public function book_session(){
		if(!physio_can('care_checkin') && !physio_can('sessions_checkin') && !physio_can('plans_add')){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$this->load->model('Sessions_model', 'sessions');
		$storeId = get_current_store_id();
		$ent = $this->sessions->getEntitlement((int)$this->input->post('entitlement_id'));
		if(!$ent){ $this->_json(array('status' => 'error', 'message' => 'That session package is not on this store')); return; }
		$when = trim($this->input->post('scheduled_at', TRUE) ?: '');
		$ts = $when ? strtotime($when) : false;
		if(!$ts){ $this->_json(array('status' => 'error', 'message' => 'Choose a valid day and time for the session')); return; }
		$patient = $this->patients->getPatient((int)$ent->patient_id);
		if(!$patient){ $this->_json(array('status' => 'error', 'message' => 'Patient not found')); return; }
		$res = $this->sessions->schedule(array(
			'entitlement_id' => (int)$ent->id,
			'customer_id'    => (int)$patient->customer_id,
			'scheduled_at'   => date('Y-m-d H:i:s', $ts),
			'fee'            => (float)$this->input->post('fee'),
			'branch_id'      => (int)$this->input->post('branch_id') ?: null,
			'clinician_id'   => (int)$this->input->post('clinician_id') ?: null,
		));
		$this->_json(!empty($res['ok'])
			? array('status' => 'success', 'message' => 'Session ' . $res['session_no'] . ' booked for ' . date('D d M H:i', $ts) . ' — it will appear on the Care Queue that day', 'session_id' => $res['session_id'])
			: array('status' => 'error', 'message' => !empty($res['error']) ? $res['error'] : 'Could not book the session'));
	}

	/** POST assign_nurse — change who is tasked with a treatment patient. */
	public function assign_nurse($id = 0){
		if(!physio_can('care_checkin') && !physio_can('vitals_add')){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$res = $this->encounters->assignNurse((int)$id, (int)$this->input->post('nurse_user_id'));
		$this->_json($res === true ? array('status' => 'success', 'message' => 'Nurse assigned') : $res);
	}

	/**
	 * POST route_therapist — send a patient to a chosen therapist, or move them
	 * to another one. The desk (reception or nursing) does this; the therapist
	 * does not have to be at the screen. Gated by the same rights that let a
	 * person move a patient into treatment, so nobody gains reach they lack.
	 */
	public function route_therapist($id = 0){
		if(!physio_can('care_checkin') && !physio_can('vitals_add') && !physio_can('encounters_add')){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$this->_json($this->encounters->routeTherapist((int)$id,
			(int)$this->input->post('clinician_user_id')));
	}

	/**
	 * Printable QUEUE TICKET — for anyone waiting to be served. Thermal-width,
	 * opens the print dialog on load. Carries no clinical data and no money
	 * beyond what the desk already shows.
	 */
	public function queue_ticket($id = 0){
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->show_access_denied_page(); return;
		}
		$patient = $this->patients->getPatient((int)$enc->patient_id);
		$storeId = get_current_store_id();

		// Queue number = position in today's arrivals, ordered by check-in time.
		$queueNo = 1;
		if($enc->checkin_at){
			$row = $this->db->query(
				"SELECT COUNT(*) AS n FROM db_encounters
				 WHERE store_id = ? AND checkin_at IS NOT NULL
				   AND checkin_at >= ? AND checkin_at < ?",
				array($storeId, date('Y-m-d 00:00:00', strtotime($enc->checkin_at)), $enc->checkin_at)
			)->row();
			$queueNo += (int)($row->n ?? 0);
		}

		if(!empty($enc->clinician_user_id)){
			$u = $this->db->select('username, role_id')->where('id', (int)$enc->clinician_user_id)->get('db_users')->row();
			$enc->clinician_name = $u->username ?? null;
			$enc->clinician_role_id = $u->role_id ?? null;
		}

		$this->load->model('patient_billing_model', 'billing');
		$bills = physio_can('patient_billing_view') ? $this->billing->billsForEncounter((int)$id) : array();
		$total = $paid = 0;
		foreach($bills as $b){ $total += (float)$b->grand_total; $paid += (float)$b->paid_amount; }

		$this->load->view('care_queue/queue_ticket', array(
			'enc'        => $enc,
			'patient'    => $patient,
			'queue_no'   => $queueNo,
			'bills'      => $bills,
			'bill_total' => round($total, 2),
			'bill_paid'  => round($paid, 2),
			'bill_due'   => round($total - $paid, 2),
		));
	}

	/**
	 * Printable SESSION TICKET — only for a patient with paid sessions still
	 * owing on a session package. It reports the one thing this slip exists
	 * for: how many sessions are left. The button that reaches here is
	 * disabled and reads "No active sessions" when the patient has none, and
	 * the endpoint refuses as well so a hand-typed URL cannot print one.
	 */
	public function session_ticket($id = 0){
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->show_access_denied_page(); return;
		}
		$this->load->model('Sessions_model', 'sessions');
		$ents = $this->sessions->activeEntitlements(array((int)$enc->patient_id));
		if(empty($ents[(int)$enc->patient_id])){ $this->show_access_denied_page(); return; }

		// The ticket must tell the patient what today's visit does to their
		// count. A session row only exists once the treatment journey reserves
		// one, and it is drawn at completion — never at check-in.
		$today = null;
		if($this->db->table_exists('db_treatment_sessions')){
			$today = $this->db->where('encounter_id', (int)$enc->id)
				->where('store_id', get_current_store_id())
				->order_by('id', 'desc')->get('db_treatment_sessions')->row();
		}

		$this->load->view('care_queue/session_ticket', array(
			'enc'     => $enc,
			'patient' => $this->patients->getPatient((int)$enc->patient_id),
			'ent'     => $ents[(int)$enc->patient_id],
			'today'   => $today,
		));
	}

	/**
	 * POST bill — raise a visit bill (assessment fee or any counter charge)
	 * for an encounter. This is the waiting-for-nurse desk's job: a checked-in
	 * patient gets registered and billed before anything clinical happens.
	 *
	 * Body: line_item_id[], line_qty[], line_price[] (or a single
	 * item_id/qty/unit_price triple) + note.
	 */
	public function bill($id = 0){
		if(!physio_can('patient_billing_add')){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		if((string)$enc->queue_stage === 'closed' && !physio_can('encounters_edit_closed')){
			$this->_json(array('status' => 'error', 'message' => 'This visit is closed — view only. Editing a closed visit needs the Edit Closed Visits right.')); return;
		}
		$this->load->model('patient_billing_model', 'billing');
		$lines = array();
		$itemIds = (array)$this->input->post('line_item_id');
		$qtys    = (array)$this->input->post('line_qty');
		$prices  = (array)$this->input->post('line_price');
		foreach($itemIds as $i => $iid){
			$lines[] = array('item_id' => (int)$iid, 'qty' => (float)($qtys[$i] ?? 1),
				'unit_price' => (float)($prices[$i] ?? 0), 'description' => '');
		}
		if(empty($lines)){
			// Single-line fallback (mobile posts one triple)
			$lines[] = array('item_id' => (int)$this->input->post('item_id'),
				'qty' => (float)$this->input->post('qty') ?: 1,
				'unit_price' => (float)$this->input->post('unit_price'),
				'description' => trim($this->input->post('description', TRUE) ?: ''));
		}
		// Fill descriptions from the catalogue where the caller left them blank
		foreach($lines as $i => $ln){
			if($ln['description'] === '' && $ln['item_id']){
				$it = $this->db->select('item_name')->where('id', $ln['item_id'])
					->where('store_id', get_current_store_id())->get('db_items')->row();
				$lines[$i]['description'] = $it ? $it->item_name : ('Item #' . $ln['item_id']);
			}
		}
		$res = $this->billing->createVisitBill((int)$id, $lines,
			trim($this->input->post('note', TRUE) ?: ''));
		$discMsg = '';
		if(!empty($res['ok'])){
			$this->encounters->logEvent((int)$id, get_current_store_id(), 'visit_bill_created', null, null,
				'Bill ' . $res['sales_code'] . ' raised at check-in');
			// A discount entered at creation is a request, not a deduction: it goes
			// to the MD for approval and only lands on the bill once approved.
			$discAmt = (float)$this->input->post('discount_amount');
			if($discAmt > 0){
				$dr = $this->billing->requestDiscount((int)$res['sales_id'], $discAmt, trim($this->input->post('discount_reason', TRUE) ?: ''));
				$discMsg = !empty($dr['ok'])
					? ' Discount sent for MD approval.'
					: ' (Discount not sent: ' . ($dr['error'] ?: 'could not raise the request') . ')';
			}
		}
		$this->_json(!empty($res['ok'])
			? array('status' => 'success', 'message' => 'Visit bill ' . $res['sales_code'] . ' created.' . $discMsg, 'sales_id' => $res['sales_id'])
			: array('status' => 'error', 'message' => $res['error']));
	}

	/**
	 * POST remark — the physiotherapist's mandatory assessment remark
	 * (how much + what sessions recommended) before pushing to Finance.
	 * With push=1 it also performs the hand-off.
	 */
	public function remark($id = 0){
		if(!physio_can('encounters_finalize')){
			$this->_json(array('status' => 'error', 'message' => 'Only the treating clinician or MD records the assessment remark')); return;
		}
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$recAmount   = $this->input->post('recommended_amount');
		$recSessions = $this->input->post('recommended_sessions');
		$recItemId   = (int)$this->input->post('recommended_item_id');
		$hasRec = (trim((string)$recAmount) !== '' && (float)$recAmount > 0)
			|| (trim((string)$recSessions) !== '' && (int)$recSessions > 0);
		if($hasRec && !$recItemId){
			$this->_json(array('status' => 'error', 'message' => 'Choose the treatment service being recommended')); return;
		}
		$res = $this->encounters->saveAssessmentRemark((int)$id,
			$this->input->post('remark', TRUE),
			$this->input->post('recommended_amount'),
			$this->input->post('recommended_sessions'));
		if($res !== true){ $this->_json($res); return; }
		// Outpatient vs Inpatient is the physiotherapist's call, made here after
		// assessment — not on the bill. It stamps the visit and its bills.
		$careType = trim($this->input->post('care_type', TRUE) ?: '');
		if($careType !== ''){
			$this->load->model('patient_billing_model', 'billing');
			$ct = $this->billing->applyCareType((int)$id, $careType);
			if(empty($ct['ok'])){
				$this->_json(array('status' => 'error', 'message' => $ct['error'] ?: 'Could not record the care type')); return;
			}
		}
		// A recommendation is a prescription: it lands as the visit's treatment
		// plan so it shows on the Treatment Plans screen, not only on the bill.
		if($hasRec){
			$this->load->model('treatment_plans_model', 'tpm');
			$planRes = $this->tpm->upsertRecommendation($enc, $recItemId,
				$recAmount ?: 0, $recSessions ?: 1, $careType, $this->input->post('remark', TRUE));
			if(empty($planRes['ok'])){
				$this->_json(array('status' => 'error', 'message' => $planRes['error'] ?: 'The treatment plan could not be saved')); return;
			}
		}
		if((int)$this->input->post('push')){
			$mv = $this->encounters->moveStage((int)$id, 'awaiting_finance',
				'Assessment complete — handed to Finance');
			$this->_json($mv === true
				? array('status' => 'success', 'message' => 'Assessment remark recorded — visit pushed to Finance')
				: array('status' => 'success', 'message' => 'Remark saved. ' . ($mv['error'] ?? '')));
			return;
		}
		$this->_json(array('status' => 'success', 'message' => 'Assessment remark saved'));
	}

	/**
	 * POST recommend_to_bill — the treating clinician reviews the visit bill
	 * and adds their recommendation to it as an explicit line. Nothing reaches
	 * the bill without this action, so a double-tap cannot quietly double-charge
	 * a patient: an identical line on the target bill replays instead.
	 */
	public function recommend_to_bill($id = 0){
		if(!physio_can('encounters_finalize')){
			$this->_json(array('status' => 'error', 'message' => 'Only the treating clinician or MD can add the recommendation to a bill')); return;
		}
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		if((string)$enc->queue_stage === 'closed' && !physio_can('encounters_edit_closed')){
			$this->_json(array('status' => 'error', 'message' => 'This visit is closed — view only. Editing a closed visit needs the Edit Closed Visits right.')); return;
		}
		$amount   = round((float)$this->input->post('amount'), 2);
		$sessions = (int)$this->input->post('sessions');
		$note     = trim($this->input->post('note', TRUE) ?: '');
		if($amount <= 0){
			$this->_json(array('status' => 'error', 'message' => 'Enter the recommended amount first')); return;
		}
		$recItemId = (int)$this->input->post('recommended_item_id');
		if(!$recItemId){
			$this->_json(array('status' => 'error', 'message' => 'Choose the treatment service being recommended')); return;
		}

		// The prescription lands as the visit's treatment plan first, so the
		// Treatment Plans screen shows the course the bill is paying for.
		$careType = '';
		if(!empty($enc->episode_id)){
			$ep = $this->db->select('episode_type')->where('id', (int)$enc->episode_id)->get('db_care_episodes')->row();
			$careType = $ep ? (string)$ep->episode_type : '';
		}
		$this->load->model('treatment_plans_model', 'tpm');
		$planRes = $this->tpm->upsertRecommendation($enc, $recItemId, $amount, $sessions ?: 1, $careType, $note);
		if(empty($planRes['ok'])){
			$this->_json(array('status' => 'error', 'message' => $planRes['error'] ?: 'The treatment plan could not be saved')); return;
		}

		$desc = ($planRes['item_name'] ?: 'Recommended plan') . ($sessions > 0 ? ' — ' . $sessions . ' session(s)' : '');
		if($note !== '') $desc .= ': ' . mb_substr($note, 0, 120);
		// One bill unit per session: paid units bank one redeemable session each,
		// so a paid 6-session plan banks exactly 6 (part payment banks pro rata).
		// A single unit at the whole course price would bank only one.
		$qty  = $sessions > 0 ? $sessions : 1;
		$unit = $sessions > 0 ? round($amount / $sessions, 2) : $amount;

		$this->load->model('patient_billing_model', 'billing');
		$bills = $this->billing->billsForEncounter((int)$id);
		// Land on the bill the patient still owes on. If every bill is settled
		// the recommendation is a fresh charge, so it gets its own bill rather
		// than silently reopening a paid one.
		$target = null;
		foreach($bills as $b){
			if((float)$b->grand_total - (float)$b->paid_amount > 0.009){ $target = $b; break; }
		}

		if($target){
			// Idempotent: the same recommendation must never land twice.
			foreach($this->billing->billItems((int)$target->id) as $it){
				if(trim((string)$it->description) === $desc){
					$this->_json(array('status' => 'success', 'message' => 'This recommendation is already on bill ' . $target->sales_code));
					return;
				}
			}
			$res = $this->billing->addLineToBill((int)$target->id, array(
				'item_id' => (int)$planRes['item_id'], 'description' => $desc, 'qty' => $qty, 'unit_price' => $unit,
			));
		} else {
			$res = $this->billing->createVisitBill((int)$id, array(
				array('item_id' => (int)$planRes['item_id'], 'description' => $desc, 'qty' => $qty, 'unit_price' => $unit),
			), 'Clinician recommendation — ' . $enc->encounter_code);
		}

		if(empty($res['ok'])){ $this->_json(array('status' => 'error', 'message' => $res['error'])); return; }

		$this->encounters->logEvent((int)$id, get_current_store_id(), 'recommendation_billed', null, null,
			'Recommendation of ' . number_format($amount, 2) . ($sessions > 0 ? ' for ' . $sessions . ' session(s)' : '') . ' added to bill ' . $res['sales_code']);
		$this->_json(array('status' => 'success',
			'message' => 'Recommendation added to bill ' . $res['sales_code'],
			'sales_id' => $res['sales_id'], 'grand_total' => $res['grand_total'] ?? null));
	}

	/**
	 * Clinical workspace for one encounter — vitals sets, assessments,
	 * investigations, consents, documents. Every panel is permission-gated;
	 * a user sees only what their role allows.
	 *
	 * Stage gating follows the Care Queue contract:
	 *   waiting_nurse    — registration + billing ONLY. No vitals, no visit
	 *                      history: this is where a checked-in patient gets
	 *                      their session ticket / bill.
	 *   nursing_intake   — vitals, bill check, history (per role).
	 *   waiting_physio   — same panels as intake, wider audience.
	 *   with_physio      — the full open record for the treating clinician.
	 *   awaiting_finance — BUSINESS ONLY for finance holders: bills, no
	 *                      vitals or medical info.
	 */
	public function encounter($id = 0){
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->show_access_denied_page(); return;
		}
		$patient = $this->patients->getPatient((int)$enc->patient_id);
		$storeId = get_current_store_id();
		$stage = (string)$enc->queue_stage;

		// A finance-only viewer (billing/funds but no clinical keys) gets the
		// business card: bills and payments, never vitals or medical notes.
		$clinical = physio_can_any(array('vitals_view', 'assessments_view',
			'investigations_view', 'encounters_view', 'patient_docs_view'));
		$business_only = !$clinical && (physio_can('patient_billing_view') || physio_can('patient_funds_view'));

		// At the waiting-for-nurse desk nothing clinical exists yet.
		$show_clinical = !$business_only && $stage !== 'waiting_nurse';

		// A closed visit is view-only: without the `encounters_edit_closed`
		// grant every write control on this screen disappears, and the same
		// actions are refused on the server behind it.
		$locked = $stage === 'closed' && !physio_can('encounters_edit_closed');

		$this->load->model('patient_billing_model', 'billing');
		$bills = physio_can('patient_billing_view')
			? $this->billing->billsForEncounter((int)$id) : array();
		$billDue = 0;
		foreach($bills as $b){ $billDue += ((float)$b->grand_total - (float)$b->paid_amount); }

		// Session ticket availability — same rule the board applies.
		$this->load->model('Sessions_model', 'sessions');
		$entMap = $this->sessions->activeEntitlements(array((int)$enc->patient_id));

		// The plan this visit's recommendation already produced, so the
		// remark form reopens with the same treatment service chosen.
		$this->load->model('treatment_plans_model', 'tpm');
		$encPlan = $this->tpm->planForEncounter((int)$id);
		$encPlanItem = 0;
		if($encPlan){ $pis = $this->tpm->currentItems($encPlan); if($pis) $encPlanItem = (int)$pis[0]->item_id; }

		$data = array_merge($this->data, array(
			'page_title'    => 'Visit ' . $enc->encounter_code,
			'enc'           => $enc,
			'patient'       => $patient,
			'stage'         => $stage,
			'business_only' => $business_only,
			'bills'         => $bills,
			'bill_due'      => round($billDue, 2),
			'session_ent'   => isset($entMap[(int)$enc->patient_id]) ? $entMap[(int)$enc->patient_id] : null,
			'vitals_sets'   => ($show_clinical && physio_can('vitals_view')) ? $this->encounters->getVitals((int)$id) : array(),
			'assessments'   => ($show_clinical && physio_can('assessments_view')) ? $this->assessments->listForEncounter((int)$id) : array(),
			'templates'     => ($show_clinical && physio_can('assessments_add')) ? $this->assessments->listTemplates() : array(),
			'investigations'=> ($show_clinical && physio_can('investigations_view')) ? $this->investigations->listRows($storeId, array('encounter_id' => (int)$id)) : array(),
			'consents'      => ($show_clinical && physio_can('patient_docs_view')) ? $this->consents->listRows($storeId, array('patient_id' => (int)$enc->patient_id)) : array(),
			'documents'     => ($show_clinical && physio_can('patient_docs_view')) ? $this->docs->listForPatient((int)$enc->patient_id) : array(),
			'events'        => ($show_clinical && !$business_only) ? $this->encounters->getEvents((int)$id) : array(),
			'can'           => array(
				'vitals_add'      => $show_clinical && !$locked && physio_can('vitals_add'),
				'assessments'     => $show_clinical && !$locked && physio_can('assessments_add'),
				'assess_fin'      => $show_clinical && !$locked && physio_can('assessments_finalize'),
				'amend'           => $show_clinical && !$locked && physio_can('encounters_amend'),
				'inv_request'     => $show_clinical && !$locked && physio_can('investigations_request'),
				'inv_result'      => $show_clinical && !$locked && physio_can('investigations_result_enter'),
				'inv_review'      => $show_clinical && !$locked && physio_can('investigations_review'),
				'docs_view'       => $show_clinical && physio_can('patient_docs_view'),
				'docs_upload'     => $show_clinical && !$locked && physio_can('patient_docs_upload'),
				'docs_release'    => $show_clinical && !$locked && physio_can('patient_docs_release'),
				'consent_manage'  => $show_clinical && !$locked && physio_can('patient_docs_upload'),
				'assign'          => $show_clinical && !$locked && physio_can('encounters_add'),
				'intake'          => $show_clinical && !$locked && physio_can('vitals_add'),
				'bill_view'       => physio_can('patient_billing_view'),
				'bill_add'        => !$locked && physio_can('patient_billing_add'),
				'remark'          => !$locked && physio_can('encounters_finalize')
					&& in_array($stage, array('nursing_intake', 'waiting_physio', 'with_physio')),
			),
			// Assign-to picker: clinical roles only, never system accounts.
			'staff'         => $show_clinical ? physio_staff_select($storeId) : array(),
			// Billable services for the raise-bill form and the physio's
			// recommendation picker on this screen.
			'services'      => physio_can_any(array('patient_billing_add','encounters_finalize'))
				? $this->db->select('id, item_name, sales_price')->where('store_id', $storeId)
					->where('service_bit', 1)->where('status', 1)->order_by('item_name')->get('db_items')->result()
				: array(),
			'enc_plan'      => $encPlan,
			'enc_plan_item' => $encPlanItem,
		));
		// Referral partners — a referral raised on this visit is tagged to the
		// encounter, so the patient history shows which visit produced it.
		$data['referral_panel'] = '';
		if($show_clinical && physio_can_any(array('referrals_view','referrals_manage'))){
			$this->load->model('referrals_model', 'referrals');
			$data['referral_panel'] = $this->load->view('referrals/patient_panel', $this->referrals->panelData(
				(int)$enc->patient_id,
				array('source' => 'visit', 'source_id' => (int)$enc->id, 'encounter_id' => (int)$enc->id)
			), TRUE);
		}
		$data['content'] = $this->load->view('care_queue/encounter', $data, TRUE);
		$this->load->view('mp_layout', $data);
	}

	/**
	 * POST vitals — a structured intake SET. Accepts arrays of per-vital
	 * fields so each entry carries unit + measured_at + explicit
	 * not_measured. finalize=1 locks the set (immutable thereafter).
	 */
	public function vitals($id = 0){
		if(!physio_can('vitals_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		// The waiting-for-nurse desk is registration + billing only — vitals
		// begin once the patient is called into Nursing Intake.
		if($enc->queue_stage === 'waiting_nurse'){
			$this->_json(array('status' => 'error', 'message' => 'Move the patient into Nursing Intake before recording vitals')); return;
		}
		// Two input styles:
		//  A) JSON body field 'entries' => [{vital_key,value,unit,not_measured,measured_at,label}]
		//  B) flat fields vit_key[]/vit_value[]/vit_unit[]/vit_nm[]/vit_at[]
		$entries = array();
		$raw = $this->input->post('entries');
		if($raw){
			$decoded = json_decode($raw, true);
			if(is_array($decoded)) $entries = $decoded;
		} else {
			$keys   = (array)$this->input->post('vit_key');
			$vals   = (array)$this->input->post('vit_value');
			$units  = (array)$this->input->post('vit_unit');
			$nms    = (array)$this->input->post('vit_nm');
			$ats    = (array)$this->input->post('vit_at');
			$labels = (array)$this->input->post('vit_label');
			foreach($keys as $i => $k){
				$entries[] = array(
					'vital_key'    => $k,
					'label'        => $labels[$i] ?? null,
					'value'        => $vals[$i] ?? null,
					'unit'         => $units[$i] ?? null,
					'not_measured' => !empty($nms[$i]),
					'measured_at'  => $ats[$i] ?? null,
				);
			}
		}
		$finalize  = (bool)$this->input->post('finalize');
		$vitalsSet = (int)$this->input->post('vitals_set_id');
		$note      = trim($this->input->post('vitals_notes', TRUE) ?: '') ?: null;
		$res = $this->encounters->saveVitalsSet((int)$id, $entries, $finalize, $vitalsSet, $note);
		if(isset($res['error'])){
			$this->_json(array('status' => 'error', 'message' => $res['error']));
		} else {
			$this->_json(array('status' => 'success',
				'message'   => $finalize ? 'Vitals finalised' : 'Draft saved',
				'vitals_id' => $res['vitals_id'], 'set_status' => $res['status']));
		}
	}

	public function vitals_list($id = 0){
		if(!physio_can('vitals_view')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$this->_json(array('status' => 'success', 'sets' => $this->encounters->getVitals((int)$id)));
	}

	/** Nursing intake completion → hands the visit to the physiotherapist. */
	public function intake_complete($id = 0){
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$res = $this->encounters->completeIntake((int)$id,
			trim($this->input->post('note', TRUE) ?: '') ?: null);
		$this->_json($res === true ? array('status' => 'success', 'message' => 'Intake complete — with physiotherapist queue') : $res);
	}

	/** Assign the visit to a staff member (permission + branch controlled). */
	public function assign($id = 0){
		if(!physio_can('encounters_add')){ $this->_json(array('status' => 'error', 'message' => 'Access denied')); return; }
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		$res = $this->encounters->assignTo((int)$id, (int)$this->input->post('user_id'));
		$this->_json($res === true ? array('status' => 'success', 'message' => 'Assigned') : $res);
	}

	public function events($id = 0){
		$enc = $this->encounters->getEncounter((int)$id);
		if(!$enc || ($enc->warehouse_id && !physio_can_branch($enc->warehouse_id))){
			$this->_json(array('status' => 'error', 'message' => 'Access denied')); return;
		}
		// Decorate with plain-English labels server-side so the feed never
		// prints a raw code such as "intake_completed" or "waiting_physio".
		$events = array();
		foreach($this->encounters->getEvents((int)$id) as $ev){
			$events[] = array(
				'label'      => mp_event_label($ev->event),
				'from_label' => $ev->from_stage ? mp_stage_label($ev->from_stage) : '',
				'to_label'   => $ev->to_stage ? mp_stage_label($ev->to_stage) : '',
				'note'       => $ev->note,
				'created_by_name' => $ev->created_by_name,
				'created_at' => $ev->created_at,
			);
		}
		$this->_json(array('status' => 'success', 'events' => $events));
	}
}
