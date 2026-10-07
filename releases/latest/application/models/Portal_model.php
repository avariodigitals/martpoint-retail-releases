<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Patient-portal model — Stage 6.
 *
 * Separate authentication surface for patients and scoped caregivers
 * (proxies). Portal sessions carry portal_* keys only — never inv_userid —
 * and every request re-validates status + auth_version so revocation and
 * password changes take effect immediately.
 *
 * Data rules enforced here (single choke point):
 *  - every query is bound to the session's patient_id + store_id;
 *  - proxies only see the scopes they were granted;
 *  - documents require released_to_patient=1 (or consent linkage) at read
 *    time — withdrawing a release denies the very next request;
 *  - unapproved opening positions render as "Under review";
 *  - private feedback never appears on the testimonial side.
 */
class Portal_model extends CI_Model {

	const SCOPES = array('appointments','progress','bills','documents','funds','feedback');
	const LOCK_MINUTES = 15;
	const MAX_FAILURES = 5;

	public function __construct(){ parent::__construct(); }

	// ------------------------------------------------------------------
	// Policies
	// ------------------------------------------------------------------
	public function policy($key, $default = null, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$row = $this->db->where('store_id',$storeId)->where('policy_key',$key)
			->get('db_portal_policies')->row();
		return $row ? $row->policy_value : $default;
	}
	public function setPolicy($key, $value, $storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$exists = $this->db->where('store_id',$storeId)->where('policy_key',$key)->count_all_results('db_portal_policies');
		if($exists){
			$this->db->where('store_id',$storeId)->where('policy_key',$key)
				->update('db_portal_policies', array('policy_value'=>(string)$value,'updated_by'=>$this->session->userdata('inv_username') ?: 'system','updated_at'=>date('Y-m-d H:i:s')));
		} else {
			$this->db->insert('db_portal_policies', array('store_id'=>$storeId,'policy_key'=>$key,'policy_value'=>(string)$value,'updated_by'=>$this->session->userdata('inv_username') ?: 'system','updated_at'=>date('Y-m-d H:i:s')));
		}
		return true;
	}
	public function policies($storeId = null){
		$storeId = $storeId ?: get_current_store_id();
		$rows = $this->db->where('store_id',$storeId)->get('db_portal_policies')->result();
		$out = array(); foreach($rows as $r){ $out[$r->policy_key] = $r; }
		return $out;
	}

	// ------------------------------------------------------------------
	// Staff side — invitations
	// ------------------------------------------------------------------

	/**
	 * Invite (or re-invite) a patient to the portal. Creates the account row,
	 * mints a signed token (hashed at rest) and queues the invite message.
	 * Imported patients are NEVER auto-invited — this is a staff-triggered,
	 * per-patient action only; callers must not batch it over migrations.
	 */
	public function invitePatient($patientId, $expiryHours = null){
		$storeId = get_current_store_id();
		$p = $this->db->where('id',(int)$patientId)->where('store_id',$storeId)->get('db_patients')->row();
		if(!$p) return array('ok'=>false,'error'=>'Patient not found');
		if((int)($p->deceased ?? 0) === 1) return array('ok'=>false,'error'=>'Cannot invite a deceased patient');
		$customer = $this->db->where('id',(int)$p->customer_id)->get('db_customers')->row();
		$identity = trim((string)($customer->email ?? ''));
		if($identity === '') $identity = trim((string)($customer->mobile ?? ''));
		if($identity === '') return array('ok'=>false,'error'=>'Patient has no email or phone on file — add a contact first');

		$hours = $expiryHours ?: (int)$this->policy('invite_expiry_hours', 72);
		$token = bin2hex(random_bytes(24));
		$row = $this->db->where('store_id',$storeId)->where('patient_id',$p->id)->get('db_patient_portal_users')->row();
		$data = array(
			'invite_token_hash' => hash('sha256', $token),
			'invite_expires_at' => date('Y-m-d H:i:s', strtotime('+'.$hours.' hours')),
			'invited_by'        => (int)$this->session->userdata('inv_userid'),
		);
		if($row){
			if($row->status === 'revoked') return array('ok'=>false,'error'=>'Portal access was revoked — reinstate it instead of re-inviting');
			$this->db->where('id',$row->id)->update('db_patient_portal_users', $data);
			$uid = (int)$row->id;
		} else {
			$this->db->insert('db_patient_portal_users', array_merge($data, array(
				'store_id'=>$storeId,'patient_id'=>$p->id,'identity'=>$identity,'status'=>'invited',
				'created_date'=>date('Y-m-d'),'created_time'=>date('H:i:s'),'created_by'=>$this->session->userdata('inv_username') ?: 'system',
			)));
			$uid = (int)$this->db->insert_id();
			if(!$uid) return array('ok'=>false,'error'=>'Invite insert failed');
		}
		$this->db->where('id',$p->id)->update('db_patients', array('portal_status'=>'invited'));
		if(function_exists('physio_notify')){
			physio_notify('portal.invite.'.$uid.'.'.substr($token,0,8), array(
				'store_id'=>$storeId,'channel'=>strpos($identity,'@') !== false ? 'email' : 'sms',
				'template_key'=>'portal_invite','recipient'=>$identity,
				'payload'=>array('patient_id'=>$p->id,'portal_user_id'=>$uid,
					'invite_url'=>base_url('portal/invite/'.$token),'expires_hours'=>$hours),
			));
		}
		return array('ok'=>true,'portal_user_id'=>$uid,'identity'=>$identity,'token'=>$token,
			'invite_url'=>base_url('portal/invite/'.$token));
	}

	/** Revoke portal access — bumps auth_version so live sessions die on next request. */
	public function revokePatient($patientId){
		$row = $this->db->where('store_id',get_current_store_id())->where('patient_id',(int)$patientId)
			->get('db_patient_portal_users')->row();
		if(!$row) return array('ok'=>false,'error'=>'No portal account');
		$this->db->where('id',$row->id)->update('db_patient_portal_users', array(
			'status'=>'revoked','auth_version'=>(int)$row->auth_version + 1));
		$this->db->where('id',(int)$patientId)->update('db_patients', array('portal_status'=>'revoked'));
		return array('ok'=>true);
	}
	public function reinstatePatient($patientId){
		$row = $this->db->where('store_id',get_current_store_id())->where('patient_id',(int)$patientId)
			->get('db_patient_portal_users')->row();
		if(!$row || $row->status !== 'revoked') return array('ok'=>false,'error'=>'Nothing to reinstate');
		$this->db->where('id',$row->id)->update('db_patient_portal_users', array(
			'status'=> $row->verified_at ? 'active' : 'invited',
			'auth_version'=>(int)$row->auth_version + 1));
		$this->db->where('id',(int)$patientId)->update('db_patients', array('portal_status'=>'invited'));
		return array('ok'=>true);
	}

	// ------------------------------------------------------------------
	// Proxies (caregivers)
	// ------------------------------------------------------------------
	public function inviteProxy($patientId, array $d){
		$storeId = get_current_store_id();
		$p = $this->db->where('id',(int)$patientId)->where('store_id',$storeId)->get('db_patients')->row();
		if(!$p) return array('ok'=>false,'error'=>'Patient not found');
		if((int)($p->deceased ?? 0) === 1) return array('ok'=>false,'error'=>'Cannot grant proxy access for a deceased patient');
		$identity = trim((string)($d['identity'] ?? ''));
		if($identity === '') return array('ok'=>false,'error'=>'Proxy email or phone is required');
		$scopes = array_values(array_intersect((array)($d['scopes'] ?? array()), self::SCOPES));
		if(!$scopes) $scopes = array('appointments','progress');

		$hours = (int)$this->policy('invite_expiry_hours', 72);
		$token = bin2hex(random_bytes(24));
		$existing = $this->db->where('store_id',$storeId)->where('patient_id',$p->id)->where('identity',$identity)
			->get('db_patient_portal_proxies')->row();
		$data = array(
			'name'=>substr(trim((string)($d['name'] ?? '')),0,150),
			'relationship'=>substr(trim((string)($d['relationship'] ?? '')),0,80),
			'scope_csv'=>implode(',',$scopes),
			'invite_token_hash'=>hash('sha256',$token),
			'invite_expires_at'=>date('Y-m-d H:i:s', strtotime('+'.$hours.' hours')),
			'invited_by'=>(int)$this->session->userdata('inv_userid'),
		);
		if($existing){
			if($existing->status === 'revoked') return array('ok'=>false,'error'=>'This proxy was revoked — create a new invitation only after review');
			$this->db->where('id',$existing->id)->update('db_patient_portal_proxies', $data);
			$pid = (int)$existing->id;
		} else {
			$this->db->insert('db_patient_portal_proxies', array_merge($data, array(
				'store_id'=>$storeId,'patient_id'=>$p->id,'identity'=>$identity,'status'=>'invited',
				'created_date'=>date('Y-m-d'),'created_time'=>date('H:i:s'),
				'created_by'=>$this->session->userdata('inv_username') ?: 'system',
			)));
			$pid = (int)$this->db->insert_id();
		}
		if(function_exists('physio_notify')){
			physio_notify('portal.proxy_invite.'.$pid.'.'.substr($token,0,8), array(
				'store_id'=>$storeId,'channel'=>strpos($identity,'@') !== false ? 'email' : 'sms',
				'template_key'=>'portal_proxy_invite','recipient'=>$identity,
				'payload'=>array('patient_id'=>$p->id,'proxy_id'=>$pid,
					'scopes'=>$scopes,'invite_url'=>base_url('portal/invite/'.$token)),
			));
		}
		return array('ok'=>true,'proxy_id'=>$pid,'token'=>$token,'invite_url'=>base_url('portal/invite/'.$token));
	}
	public function revokeProxy($proxyId){
		$row = $this->db->where('id',(int)$proxyId)->where('store_id',get_current_store_id())
			->get('db_patient_portal_proxies')->row();
		if(!$row) return array('ok'=>false,'error'=>'Proxy not found');
		$this->db->where('id',$row->id)->update('db_patient_portal_proxies', array(
			'status'=>'revoked','auth_version'=>(int)$row->auth_version + 1,
			'revoked_at'=>date('Y-m-d H:i:s'),'revoked_by'=>(int)$this->session->userdata('inv_userid')));
		return array('ok'=>true);
	}
	public function portalAccount($patientId){
		return $this->db->where('store_id',get_current_store_id())->where('patient_id',(int)$patientId)
			->get('db_patient_portal_users')->row();
	}
	public function proxies($patientId){
		return $this->db->where('store_id',get_current_store_id())->where('patient_id',(int)$patientId)
			->order_by('id','desc')->get('db_patient_portal_proxies')->result();
	}

	// ------------------------------------------------------------------
	// Public side — invitation acceptance + authentication
	// ------------------------------------------------------------------

	/** Resolve an invite token to its account row (patient or proxy). */
	public function findInvite($token){
		$hash = hash('sha256', (string)$token);
		$u = $this->db->where('invite_token_hash',$hash)->where('status','invited')
			->get('db_patient_portal_users')->row();
		if($u) return array('kind'=>'patient','row'=>$u);
		$x = $this->db->where('invite_token_hash',$hash)->where('status','invited')
			->get('db_patient_portal_proxies')->row();
		if($x) return array('kind'=>'proxy','row'=>$x);
		return null;
	}

	/** Accept an invitation: sets the password and verifies the account. */
	public function acceptInvite($token, $password){
		$inv = $this->findInvite($token);
		if(!$inv) return array('ok'=>false,'error'=>'Invitation not found or already used');
		$row = $inv['row'];
		if($row->invite_expires_at && strtotime($row->invite_expires_at) < time()){
			return array('ok'=>false,'error'=>'expired');
		}
		if(strlen((string)$password) < 8) return array('ok'=>false,'error'=>'Choose a password of at least 8 characters');
		$tbl = $inv['kind'] === 'patient' ? 'db_patient_portal_users' : 'db_patient_portal_proxies';
		$this->db->where('id',(int)$row->id)->update($tbl, array(
			'auth_secret'=>password_hash($password, PASSWORD_DEFAULT),
			'verified_at'=>date('Y-m-d H:i:s'),'status'=>'active',
			'invite_token_hash'=>null,'invite_expires_at'=>null,
			'auth_version'=>(int)$row->auth_version + 1,
		));
		if($inv['kind'] === 'patient'){
			$this->db->where('id',(int)$row->patient_id)->update('db_patients', array('portal_status'=>'active'));
		}
		return array('ok'=>true,'kind'=>$inv['kind']);
	}

	/** Password login. Locks for LOCK_MINUTES after MAX_FAILURES. */
	public function login($identity, $password){
		$identity = trim((string)$identity);
		foreach(array('db_patient_portal_users'=>'patient','db_patient_portal_proxies'=>'proxy') as $tbl=>$kind){
			$row = $this->db->where('identity',$identity)->where('status','active')->get($tbl)->row();
			if(!$row) continue;
			if($row->locked_until && strtotime($row->locked_until) > time()){
				return array('ok'=>false,'error'=>'Account temporarily locked — try again later');
			}
			if(!$row->auth_secret || !password_verify((string)$password, $row->auth_secret)){
				$fails = (int)$row->failed_attempts + 1;
				$upd = array('failed_attempts'=>$fails);
				if($fails >= self::MAX_FAILURES){
					$upd['locked_until'] = date('Y-m-d H:i:s', strtotime('+'.self::LOCK_MINUTES.' minutes'));
					$upd['failed_attempts'] = 0;
				}
				$this->db->where('id',(int)$row->id)->update($tbl, $upd);
				return array('ok'=>false,'error'=>'Invalid credentials');
			}
			$this->db->where('id',(int)$row->id)->update($tbl, array(
				'failed_attempts'=>0,'locked_until'=>null,'last_login_at'=>date('Y-m-d H:i:s')));
			return array('ok'=>true,'kind'=>$kind,'row'=>$row);
		}
		return array('ok'=>false,'error'=>'Invalid credentials');
	}

	/**
	 * Resolve the live portal session, re-validating status + auth_version
	 * against the DB — revocation and password resets take effect on the
	 * very next request. Returns null when the session must be destroyed.
	 */
	public function currentPrincipal(){
		$kind = $this->session->userdata('portal_kind');
		$id   = (int)$this->session->userdata('portal_account_id');
		if(!$kind || !$id) return null;
		if($kind === 'patient'){
			$row = $this->db->where('id',$id)->where('status','active')->get('db_patient_portal_users')->row();
			if(!$row || (int)$row->auth_version !== (int)$this->session->userdata('portal_auth_version')) return null;
			$patient = $this->db->where('id',(int)$row->patient_id)->get('db_patients')->row();
			if(!$patient || (int)($patient->deceased ?? 0) === 1) return null;
			return array('kind'=>'patient','account'=>$row,'patient_id'=>(int)$row->patient_id,
				'store_id'=>(int)$row->store_id,'scopes'=>self::SCOPES,'patient'=>$patient);
		}
		if($kind === 'proxy'){
			$row = $this->db->where('id',$id)->where('status','active')->get('db_patient_portal_proxies')->row();
			if(!$row || (int)$row->auth_version !== (int)$this->session->userdata('portal_auth_version')) return null;
			$patient = $this->db->where('id',(int)$row->patient_id)->get('db_patients')->row();
			if(!$patient || (int)($patient->deceased ?? 0) === 1) return null;
			return array('kind'=>'proxy','account'=>$row,'patient_id'=>(int)$row->patient_id,
				'store_id'=>(int)$row->store_id,'scopes'=>explode(',',(string)$row->scope_csv),'patient'=>$patient);
		}
		return null;
	}

	public function beginSession($kind, $row){
		$this->session->set_userdata(array(
			'portal_kind'=>$kind,'portal_account_id'=>(int)$row->id,
			'portal_patient_id'=>(int)$row->patient_id,'portal_store_id'=>(int)$row->store_id,
			'portal_auth_version'=>(int)$row->auth_version,
			// Store context lets the shared models (funds, billing) scope
			// correctly. inv_userid is never set — staff guards still deny.
			'store_id'=>(int)$row->store_id,
		));
	}
	public function endSession(){
		$this->session->unset_userdata(array('portal_kind','portal_account_id','portal_patient_id','portal_store_id','portal_auth_version','store_id'));
	}
	public function proxyCan($scope, array $principal){
		return $principal['kind'] === 'patient' || in_array($scope, $principal['scopes'], true);
	}

	// ------------------------------------------------------------------
	// Portal data — all patient-scoped
	// ------------------------------------------------------------------
	public function appointments($patientId, $storeId){
		return $this->db->where('store_id',$storeId)->where('patient_id',(int)$patientId)
			->where_in('status', array('requested','proposed','confirmed','checked_in','completed'))
			->order_by('scheduled_at','desc')->limit(50)->get('db_appointments')->result();
	}

	/** Session/package progress for the patient. */
	public function progress($patientId, $storeId){
		$ents = $this->db->where('store_id',$storeId)->where('patient_id',(int)$patientId)
			->order_by('id','desc')->get('db_plan_entitlements')->result();
		$sessions = $this->db->select('id,scheduled_at,status,session_no,units_total')
			->where('store_id',$storeId)->where('patient_id',(int)$patientId)
			->order_by('scheduled_at','desc')->limit(30)->get('db_treatment_sessions')->result();
		return array('entitlements'=>$ents,'sessions'=>$sessions);
	}

	/** Bills + payments for the patient's customer id. */
	public function bills($patientId, $storeId){
		$p = $this->db->where('id',(int)$patientId)->get('db_patients')->row();
		if(!$p || !$p->customer_id) return array();
		return $this->db->select('id,sales_code,sales_date,grand_total,paid_amount,payment_status,sales_status')
			->where('store_id',$storeId)->where('customer_id',(int)$p->customer_id)
			->where_in("sales_status", array("Final","Opening"))->order_by('id','desc')->limit(50)
			->get('db_sales')->result();
	}
	public function bill($salesId, $patientId, $storeId){
		$p = $this->db->where('id',(int)$patientId)->get('db_patients')->row();
		if(!$p) return null;
		$sale = $this->db->where('id',(int)$salesId)->where('store_id',$storeId)
			->where('customer_id',(int)$p->customer_id)->get('db_sales')->row();
		if(!$sale) return null;
		$items = $this->db->where('sales_id',$sale->id)->get('db_salesitems')->result();
		$payments = $this->db->where('sales_id',$sale->id)->order_by('id')->get('db_salespayments')->result();
		return array('sale'=>$sale,'items'=>$items,'payments'=>$payments);
	}

	/** Wallet picture — pending-opening positions display as "Under review". */
	public function funds($patientId, $storeId){
		$this->load->model('patient_funds_model','pf');
		$balances = $this->pf->getBalances($patientId, $storeId)
			?: array('available'=>0,'reserved'=>0,'pending'=>0,'consumed'=>0,'retail_advance'=>0);
		// Debt = unpaid balance on Final invoices for this patient's customer.
		$p = $this->db->where('id',(int)$patientId)->get('db_patients')->row();
		$debt = 0;
		if($p && $p->customer_id){
			$debt = (float)$this->db->select('COALESCE(SUM(grand_total - paid_amount),0) AS d', false)
				->where('store_id',$storeId)->where('customer_id',(int)$p->customer_id)
				->where_in('sales_status', array('Final','Opening'))->where('payment_status !=','Paid')
				->get('db_sales')->row()->d;
		}
		$balances['debt'] = round($debt, 2);
		$openings = $this->db->where('patient_id',(int)$patientId)->get('db_opening_positions')->result();
		return array('balances'=>$balances,'openings'=>$openings);
	}

	/**
	 * Portal statement — patient-owned. Reuses Patient_statement_model so the
	 * patient sees the same admission-scoped and patient-wide figures as the
	 * clinic, with the wallet kept separate from debt. Patient ownership is
	 * enforced because every query keys on the principal's own patient_id.
	 */
	public function statement($patientId, $storeId){
		$this->load->model('patient_statement_model','stmt');
		$patient = $this->stmt->patientStatement($patientId, $storeId);

		// Admission statements (one per admission-linked invoice), admission-scoped.
		$admissions = array();
		foreach($patient['invoices'] as $inv){
			if($inv['admission_id']){
				$ast = $this->stmt->admissionStatement($inv['admission_id'], $storeId);
				if($ast){ $admissions[] = $ast; }
			}
		}
		return array(
			'patient'      => $patient,
			'admissions'   => $admissions,
		);
	}

	/**
	 * Portal-visible documents: explicitly released reports, and consent
	 * documents the patient signed. Release is re-checked at read time —
	 * withdrawal denies immediately.
	 */
	public function documents($patientId, $storeId){
		$consentDocIds = array();
		foreach($this->db->select('document_id')->where('store_id',$storeId)
			->where('patient_id',(int)$patientId)->where('document_id IS NOT NULL',null,false)
			->get('db_consents')->result() as $c){ $consentDocIds[] = (int)$c->document_id; }
		$q = $this->db->select('d.id,d.category,d.title,d.status,d.released_at,v.mime,v.file_size,d.current_version_id')
			->from('db_patient_documents d')
			->join('db_document_versions v','v.id = d.current_version_id','left')
			->where('d.store_id',$storeId)->where('d.patient_id',(int)$patientId)
			->where('d.status !=','missing_attachment')
			->group_start()
				->where('d.released_to_patient',1)
				->or_where_in('d.id', $consentDocIds ?: array(0))
			->group_end()
			->order_by('d.id','desc')->get();
		return $q->result();
	}
	public function portalDoc($docId, $patientId, $storeId){
		$doc = $this->db->where('id',(int)$docId)->where('store_id',$storeId)
			->where('patient_id',(int)$patientId)->get('db_patient_documents')->row();
		if(!$doc || $doc->status === 'missing_attachment') return null;
		if((int)$doc->released_to_patient === 1) return $doc;
		$linked = $this->db->where('document_id',(int)$docId)->where('patient_id',(int)$patientId)
			->where('store_id',$storeId)->count_all_results('db_consents');
		return $linked ? $doc : null;
	}

	/** Resubmit/raise payment evidence from the portal — same dedup rules as staff path. */
	public function submitEvidence($salesId, $patientId, $storeId, $amount, $ref, $channel){
		$bill = $this->bill($salesId, $patientId, $storeId);
		if(!$bill) return array('ok'=>false,'error'=>'Bill not found');
		$this->load->model('patient_funds_model','pf');
		return $this->pf->submitEvidence($patientId, array(
			'sale_id'=>$salesId,'amount'=>$amount,'payment_ref'=>$ref,'channel'=>$channel,
		));
	}

	// ------------------------------------------------------------------
	// Feedback + testimonials
	// ------------------------------------------------------------------

	/**
	 * Queue an optional feedback invitation after checkout/discharge —
	 * frequency-limited by policy; never blocks the clinical action.
	 */
	public function maybeRequestFeedback($patientId, $refType, $refId, $storeId){
		if((int)$this->policy('feedback_enabled', 1, $storeId) !== 1) return false;
		$already = $this->db->where('store_id',$storeId)->where('patient_id',(int)$patientId)
			->where('ref_type',$refType)->where('ref_id',(int)$refId)
			->count_all_results('db_patient_feedback');
		if($already) return false;
		$cool = (int)$this->policy('feedback_cooldown_days', 7, $storeId);
		if($cool > 0){
			$recent = $this->db->where('store_id',$storeId)->where('patient_id',(int)$patientId)
				->where('created_at >=', date('Y-m-d H:i:s', strtotime('-'.$cool.' days')))
				->count_all_results('db_patient_feedback');
			if($recent) return false;
		}
		$p = $this->db->where('id',(int)$patientId)->where('store_id',$storeId)->get('db_patients')->row();
		if(!$p || (int)($p->deceased ?? 0) === 1) return false;
		$customer = $this->db->where('id',(int)$p->customer_id)->get('db_customers')->row();
		$to = trim((string)($customer->email ?? '')) ?: trim((string)($customer->mobile ?? ''));
		if($to === '') return false;
		if(function_exists('physio_notify')){
			return physio_notify('feedback.request.'.$refType.'.'.$refId, array(
				'store_id'=>$storeId,'channel'=>strpos($to,'@') !== false ? 'email' : 'sms',
				'template_key'=>'feedback_request','recipient'=>$to,
				'payload'=>array('patient_id'=>(int)$patientId,'ref_type'=>$refType,'ref_id'=>(int)$refId),
			));
		}
		return false;
	}

	public function submitFeedback($patientId, $storeId, array $d){
		$rating = (int)($d['rating'] ?? 0);
		if($rating < 1 || $rating > 5) return array('ok'=>false,'error'=>'Choose a rating between 1 and 5');
		$refType = in_array(($d['ref_type'] ?? ''), array('encounter','admission','session','sale','appointment'), true) ? $d['ref_type'] : 'encounter';
		$refId = (int)($d['ref_id'] ?? 0);
		// Feedback can only tag records owned by this patient.
		if($refId > 0 && !$this->_ownsRef($patientId, $storeId, $refType, $refId)){
			return array('ok'=>false,'error'=>'Unknown reference');
		}
		// one feedback row per (patient, ref) — resubmission updates, not duplicates
		$row = array(
			'rating'=>$rating,'comment'=>substr(trim((string)($d['comment'] ?? '')),0,4000),
			'is_private'=>1,'source'=>'portal',
		);
		$existing = $this->db->where('store_id',$storeId)->where('patient_id',(int)$patientId)
			->where('ref_type',$refType)->where('ref_id',$refId)->get('db_patient_feedback')->row();
		if($existing){
			$this->db->where('id',$existing->id)->update('db_patient_feedback', $row);
			return array('ok'=>true,'feedback_id'=>(int)$existing->id,'updated'=>true);
		}
		$this->db->insert('db_patient_feedback', array_merge($row, array(
			'store_id'=>$storeId,'patient_id'=>(int)$patientId,
			'ref_type'=>$refType,'ref_id'=>$refId,'created_at'=>date('Y-m-d H:i:s'),
		)));
		return array('ok'=>true,'feedback_id'=>(int)$this->db->insert_id());
	}
	/** Verify a feedback reference belongs to the authenticated patient. */
	private function _ownsRef($patientId, $storeId, $refType, $refId){
		switch($refType){
			case 'appointment':
				return $this->db->where('id',$refId)->where('store_id',$storeId)
					->where('patient_id',$patientId)->count_all_results('db_appointments') > 0;
			case 'session':
				return $this->db->where('id',$refId)->where('store_id',$storeId)
					->where('patient_id',$patientId)->count_all_results('db_treatment_sessions') > 0;
			case 'admission':
				return $this->db->where('id',$refId)->where('store_id',$storeId)
					->where('patient_id',$patientId)->count_all_results('db_admissions') > 0;
			case 'sale':
				$p = $this->db->where('id',$patientId)->get('db_patients')->row();
				return $p && $this->db->where('id',$refId)->where('store_id',$storeId)
					->where('customer_id',(int)$p->customer_id)->count_all_results('db_sales') > 0;
			case 'encounter':
			default:
				return $this->db->where('id',$refId)->where('store_id',$storeId)
					->where('patient_id',$patientId)->count_all_results('db_encounters') > 0;
		}
	}

	public function feedbackList($storeId, $status = null){
		$q = $this->db->select('f.*, c.customer_name AS patient_name')
			->from('db_patient_feedback f')
			->join('db_patients p','p.id = f.patient_id')
			->join('db_customers c','c.id = p.customer_id','left')
			->where('f.store_id',$storeId)->order_by('f.id','desc');
		if($status) $q->where('f.follow_up_status',$status);
		return $q->get()->result();
	}
	public function feedbackFollowUp($feedbackId, $status, $note = ''){
		if(!in_array($status, array('acknowledged','in_progress','resolved'), true)) return array('ok'=>false,'error'=>'Bad status');
		$this->db->where('id',(int)$feedbackId)->where('store_id',get_current_store_id())
			->update('db_patient_feedback', array(
				'follow_up_status'=>$status,'follow_up_by'=>(int)$this->session->userdata('inv_userid'),
				'follow_up_at'=>date('Y-m-d H:i:s'),'follow_up_note'=>substr(trim($note),0,490)));
		return array('ok'=>$this->db->affected_rows() > 0);
	}

	/** Testimonial submission — requires the separate publish consent. */
	public function submitTestimonial($patientId, $storeId, array $d){
		if((int)$this->policy('testimonials_enabled', 1, $storeId) !== 1)
			return array('ok'=>false,'error'=>'Testimonials are not enabled');
		$body = trim((string)($d['body'] ?? ''));
		if(strlen($body) < 10) return array('ok'=>false,'error'=>'Tell us a little more (10+ characters)');
		if(empty($d['publish_consent']))
			return array('ok'=>false,'error'=>'Publication consent is required — private feedback stays private');
		$mode = in_array(($d['display_mode'] ?? ''), array('anonymous','first_name','custom'), true)
			? $d['display_mode'] : 'anonymous';
		$this->db->insert('db_patient_testimonials', array(
			'store_id'=>$storeId,'patient_id'=>(int)$patientId,
			'feedback_id'=>!empty($d['feedback_id']) ? (int)$d['feedback_id'] : null,
			'body'=>substr($body,0,4000),
			'rating'=>!empty($d['rating']) ? (int)$d['rating'] : null,
			'display_mode'=>$mode,
			'display_name'=>$mode==='custom' ? substr(trim((string)($d['display_name'] ?? '')),0,100) : null,
			'publish_consent'=>1,'consent_at'=>date('Y-m-d H:i:s'),
			'status'=>'pending','created_at'=>date('Y-m-d H:i:s'),
		));
		return array('ok'=>true,'testimonial_id'=>(int)$this->db->insert_id());
	}
	public function myTestimonials($patientId, $storeId){
		return $this->db->where('store_id',$storeId)->where('patient_id',(int)$patientId)
			->order_by('id','desc')->get('db_patient_testimonials')->result();
	}
	public function withdrawTestimonial($id, $patientId, $storeId){
		// Ownership-bound: a patient can only withdraw their own.
		$t = $this->db->where('id',(int)$id)->where('store_id',$storeId)
			->where('patient_id',(int)$patientId)->get('db_patient_testimonials')->row();
		if(!$t) return array('ok'=>false,'error'=>'Testimonial not found');
		if($t->status === 'withdrawn') return array('ok'=>true,'replayed'=>true);
		$this->db->where('id',(int)$t->id)->update('db_patient_testimonials', array(
			'status'=>'withdrawn','withdrawn_at'=>date('Y-m-d H:i:s')));
		return array('ok'=>true);
	}
	public function testimonials($storeId, $status = null){
		$q = $this->db->select('t.*, c.customer_name AS patient_name')
			->from('db_patient_testimonials t')
			->join('db_patients p','p.id = t.patient_id')
			->join('db_customers c','c.id = p.customer_id','left')
			->where('t.store_id',$storeId)->order_by('t.id','desc');
		if($status) $q->where('t.status',$status);
		return $q->get()->result();
	}
	public function moderateTestimonial($id, $decision, $note = ''){
		if(!in_array($decision, array('approved','rejected'), true)) return array('ok'=>false,'error'=>'Bad decision');
		$t = $this->db->where('id',(int)$id)->where('store_id',get_current_store_id())
			->get('db_patient_testimonials')->row();
		if(!$t || $t->status !== 'pending') return array('ok'=>false,'error'=>'Not awaiting moderation');
		$this->db->where('id',$t->id)->update('db_patient_testimonials', array(
			'status'=>$decision,'moderated_by'=>(int)$this->session->userdata('inv_userid'),
			'moderated_at'=>date('Y-m-d H:i:s'),'moderation_note'=>substr(trim($note),0,255)));
		return array('ok'=>true);
	}
	/** Public display set — approved only, never withdrawn. */
	public function publicTestimonials($storeId, $limit = 12){
		return $this->db->where('store_id',$storeId)->where('status','approved')
			->where('publish_consent',1)->order_by('moderated_at','desc')->limit((int)$limit)
			->get('db_patient_testimonials')->result();
	}
}
