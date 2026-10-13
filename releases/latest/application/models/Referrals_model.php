<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Referrals_model — external referral partners and the referrals made to them.
 *
 * Two separate things live here. The partner directory (labs, hospitals,
 * doctors) is clinic setup data. A patient referral points at one of those
 * partners and carries a dated meeting log, so a therapist can see what is
 * due at the partner and what has already happened there.
 *
 * Removing a partner never erases patient history: db_patient_referrals keeps
 * a snapshot of the name and type, and only the partner_id is cleared.
 */
class Referrals_model extends CI_Model {

	private function storeId(){ return (int) get_current_store_id(); }
	private function userId(){ return (int) $this->session->userdata('inv_userid'); }
	private function now(){ return date('Y-m-d H:i:s'); }

	private static $partnerTypes = array('lab', 'hospital', 'doctor');
	private static $refStatuses = array('open', 'in_progress', 'completed', 'cancelled');

	/** Seed choices for the specialty / focus dropdown, per partner type. */
	private static $seedSpecialties = array(
		'doctor' => array(
			'General Practice', 'Orthopaedics', 'Neurology', 'Cardiology',
			'Sports Medicine', 'Rheumatology', 'Pain Management', 'General Surgery',
			'Paediatrics', 'Geriatrics', 'Gynaecology & Obstetrics', 'Psychiatry',
			'Dermatology', 'Ophthalmology', 'ENT (Ear, Nose & Throat)', 'Urology',
			'Endocrinology', 'Respiratory Medicine', 'Physiotherapy',
		),
		'hospital' => array(
			'General Hospital', 'Teaching Hospital', 'Specialist Hospital',
			'Orthopaedic Hospital', 'Trauma & Emergency Centre', 'Rehabilitation Centre',
			'Physiotherapy & Rehabilitation Centre', 'Cardiac Centre',
			'Neurological Centre', "Children's Hospital", 'Maternity Hospital',
			'Diagnostic Centre',
		),
		'lab' => array(
			'Diagnostic Laboratory', 'Radiology & Imaging', 'MRI / CT Scanning',
			'Ultrasound', 'X-Ray', 'Pathology & Histology', 'Haematology / Blood Works',
			'Microbiology', 'Cardiac Investigations (ECG / Echo)',
			'Neurophysiology (EMG / NCS)', 'Genetic Testing', 'Drug Screening',
		),
	);

	/**
	 * Reason and urgency choices, seeded the first time a store reads them.
	 * The clinic then edits its own wording on the Referral partners screen —
	 * these are a starting point, not a fixed vocabulary.
	 */
	private static $seedReasons = array(
		array('orthopaedic_review', 'Orthopaedic review', 10),
		array('imaging',             'X-ray / scan review', 20),
		array('mri_ct',              'MRI or CT scan', 30),
		array('specialist_opinion',  'Specialist consultation', 40),
		array('surgical_opinion',    'Surgical opinion', 50),
		array('neurology',           'Neurology review', 60),
		array('cardiology',          'Cardiology review', 70),
		array('pain_management',     'Pain management', 80),
		array('rehabilitation',      'Rehabilitation assessment', 90),
		array('laboratory',          'Laboratory investigation', 100),
		array('second_opinion',      'Second opinion', 110),
		array('post_procedure',      'Follow-up after a procedure', 120),
		array('acute_care',          'Emergency / acute care', 130),
	);
	private static $seedUrgencies = array(
		array('routine',   'Routine', 10),
		array('urgent',    'Urgent', 20),
		array('emergency', 'Emergency', 30),
	);

	/**
	 * Starting directory of well-known hospitals.
	 *
	 * Public teaching and specialist hospitals, and nothing else: phone, email
	 * and address are left empty because we never guess contact details. Staff
	 * fill those in. Each row carries an external_key so a future shared
	 * directory can update it in place rather than adding a second copy.
	 */
	private static $seedHospitals = array(
		array('National Orthopaedic Hospital, Igbobi',                        'Orthopaedic Hospital', 'noh-igbobi'),
		array('National Orthopaedic Hospital, Enugu',                         'Orthopaedic Hospital', 'noh-enugu'),
		array('National Orthopaedic Hospital, Dala (Kano)',                   'Orthopaedic Hospital', 'noh-dala'),
		array('Lagos University Teaching Hospital (LUTH)',                    'Teaching Hospital',    'luth'),
		array('University College Hospital, Ibadan (UCH)',                    'Teaching Hospital',    'uch-ibadan'),
		array('Lagos State University Teaching Hospital (LASUTH)',            'Teaching Hospital',    'lasuth'),
		array('National Hospital, Abuja',                                     'General Hospital',     'national-hospital-abuja'),
		array('University of Nigeria Teaching Hospital, Ituku-Ozalla',        'Teaching Hospital',    'unth-enugu'),
		array('Ahmadu Bello University Teaching Hospital, Zaria',             'Teaching Hospital',    'abuth-zaria'),
		array('Obafemi Awolowo University Teaching Hospitals Complex, Ile-Ife','Teaching Hospital',   'oauthc-ile-ife'),
		array('University of Ilorin Teaching Hospital',                       'Teaching Hospital',    'uith-ilorin'),
		array('Federal Medical Centre, Ebute Metta',                          'General Hospital',     'fmc-ebute-metta'),
		array('Federal Medical Centre, Abeokuta',                             'General Hospital',     'fmc-abeokuta'),
		array('Gbagada General Hospital, Lagos',                              'Trauma & Emergency Centre', 'gbagada-general'),
		array('Lagos State Trauma & Emergency Centre',                        'Trauma & Emergency Centre', 'lagos-trauma-centre'),
		array('Lagoon Hospitals, Lagos',                                      'Specialist Hospital',  'lagoon-hospitals'),
		array('Reddington Hospital, Lagos',                                   'Specialist Hospital',  'reddington'),
		array('St. Nicholas Hospital, Lagos',                                 'Specialist Hospital',  'st-nicholas'),
		array('Eko Hospital, Ikeja',                                          'Specialist Hospital',  'eko-hospital'),
		array('Duchess International Hospital, Ikeja',                       'Specialist Hospital',  'duchess-international'),
		array('Cedarcrest Hospitals, Abuja',                                  'Specialist Hospital',  'cedarcrest'),
		array('Alliance Hospital, Abuja',                                     'Specialist Hospital',  'alliance-abuja'),
		array('Nizamiye Hospital, Abuja',                                     'Specialist Hospital',  'nizamiye'),
		array('Neuropsychiatric Hospital, Aro, Abeokuta',                     'Specialist Hospital',  'aro-abeokuta'),
		array('Federal Neuropsychiatric Hospital, Yaba',                      'Specialist Hospital',  'neuro-yaba'),
		array('Eye Foundation Hospital, Lagos',                               'Specialist Hospital',  'eye-foundation'),
		array('Tristate Cardiovascular Centre, Lagos',                        'Cardiac Centre',       'tristate-cardio'),
	);

	// ------------------------------------------------------------------
	// Referral option lists — reasons and urgency statuses
	// ------------------------------------------------------------------

	/**
	 * One configured list. Read-only: nothing is written on a page render, so
	 * a viewer who may not edit the directory never changes it. A store with
	 * nothing configured (or no migration yet) sees the built-in list, so the
	 * referral form is never a blank dropdown.
	 */
	public function optionList($group, $activeOnly = true){
		if(!in_array($group, array('reason', 'urgency'), true)) return array();
		$seeds = $group === 'reason' ? self::$seedReasons : self::$seedUrgencies;
		if(!$this->db->table_exists('db_referral_options')) return self::seedRowsAsObjects($group, $seeds);

		$q = $this->db->where('store_id', $this->storeId())->where('group_code', $group);
		if($activeOnly) $q->where('is_active', 1);
		$rows = $q->order_by('sort_order')->order_by('label')->get('db_referral_options')->result();
		return $rows ? $rows : self::seedRowsAsObjects($group, $seeds);
	}

	/** Write the built-in lists in once per store, so staff can edit them. */
	public function ensureOptionSeeds(){
		if(!$this->db->table_exists('db_referral_options')) return;
		$this->ensureOptionSeedGroup('reason');
		$this->ensureOptionSeedGroup('urgency');
	}

	/** The code a new referral starts on — the flagged default, else the first. */
	public function defaultOptionCode($group){
		$list = $this->optionList($group);
		foreach($list as $o){ if(!empty($o->is_default)) return (string)$o->code; }
		return $list ? (string)$list[0]->code : '';
	}

	/** Is this code one of the store's configured values? */
	public function optionExists($group, $code){
		foreach($this->optionList($group) as $o){
			if((string)$o->code === (string)$code) return true;
		}
		return false;
	}

	/** Write the built-in defaults for one list in, once per store. */
	private function ensureOptionSeedGroup($group){
		$seeds = $group === 'reason' ? self::$seedReasons : self::$seedUrgencies;
		$have = $this->db->where('store_id', $this->storeId())->where('group_code', $group)
			->count_all_results('db_referral_options');
		if((int)$have > 0) return;
		$now = $this->now();
		foreach($seeds as $i => $s){
			$this->db->insert('db_referral_options', array(
				'store_id'    => $this->storeId(),
				'group_code'  => $group,
				'code'        => $s[0],
				'label'       => $s[1],
				'sort_order'  => $s[2],
				'is_default'  => $group === 'urgency' && $i === 0 ? 1 : 0,
				'is_active'   => 1,
				'created_at'  => $now,
			));
		}
	}

	private static function seedRowsAsObjects($group, array $seeds){
		$out = array();
		foreach($seeds as $i => $s){
			$o = new stdClass();
			$o->id = 0;
			$o->group_code = $group;
			$o->code = $s[0];
			$o->label = $s[1];
			$o->sort_order = $s[2];
			$o->is_default = $group === 'urgency' && $i === 0 ? 1 : 0;
			$o->is_active = 1;
			$out[] = $o;
		}
		return $out;
	}

	/** Add or rename an entry in one of the store's lists. */
	public function saveOption($group, $code, $label, $sortOrder = 0, $isDefault = false, $id = 0){
		if(!in_array($group, array('reason', 'urgency'), true)) return array('ok' => false, 'error' => 'Unknown list');
		if(!$this->db->table_exists('db_referral_options')) return array('ok' => false, 'error' => 'Run the latest migration to configure referral lists');
		// Adding to an unconfigured store brings the standard list in first,
		// so the custom entry joins it instead of replacing it.
		$this->ensureOptionSeedGroup($group);
		$label = mb_substr(trim((string)$label), 0, 120);
		$code = strtolower(trim((string)$code));
		if($code === '') $code = self::slug($label);
		$code = mb_substr($code, 0, 60);
		if($code === '' || $label === '') return array('ok' => false, 'error' => 'Both a short code and a name are required');

		$row = array(
			'group_code' => $group,
			'code'       => $code,
			'label'      => $label,
			'sort_order' => (int)$sortOrder,
			'updated_at' => $this->now(),
		);
		if((int)$id > 0){
			$exists = $this->db->where('id', (int)$id)->where('store_id', $this->storeId())
				->get('db_referral_options')->row();
			if(!$exists) return array('ok' => false, 'error' => 'Entry not found');
			$this->db->where('id', (int)$id)->update('db_referral_options', $row);
		} else {
			$dup = $this->db->where('store_id', $this->storeId())->where('group_code', $group)
				->where('code', $code)->get('db_referral_options')->row();
			if($dup) return array('ok' => false, 'error' => 'That short code is already in use on this list');
			$row['store_id'] = $this->storeId();
			$row['is_active'] = 1;
			$row['created_at'] = $this->now();
			$this->db->insert('db_referral_options', $row);
		}
		if($isDefault) $this->setDefaultOption($group, $code);
		return array('ok' => true, 'message' => 'Referral option saved');
	}

	/** Exactly one default per list, so a referral always starts somewhere. */
	public function setDefaultOption($group, $code){
		if(!$this->db->table_exists('db_referral_options')) return array('ok' => false, 'error' => 'Run the latest migration to configure referral lists');
		$this->db->where('store_id', $this->storeId())->where('group_code', $group)
			->update('db_referral_options', array('is_default' => 0, 'updated_at' => $this->now()));
		$this->db->where('store_id', $this->storeId())->where('group_code', $group)
			->where('code', (string)$code)->update('db_referral_options', array('is_default' => 1));
		return array('ok' => true, 'message' => 'Default updated');
	}

	public function removeOption($id){
		if(!$this->db->table_exists('db_referral_options')) return array('ok' => false, 'error' => 'Not available yet');
		$o = $this->db->where('id', (int)$id)->where('store_id', $this->storeId())
			->get('db_referral_options')->row();
		if(!$o) return array('ok' => false, 'error' => 'Entry not found');
		// Referrals already recorded keep the reason text they were made with.
		$this->db->where('id', (int)$id)->delete('db_referral_options');
		return array('ok' => true, 'message' => 'Removed from the list');
	}

	/**
	 * Add the standard hospitals to the directory. Idempotent — anything this
	 * store already has, by name or by external key, is left alone.
	 */
	public function seedHospitalDirectory(){
		if(!$this->db->table_exists('db_referral_partners')) return array('ok' => false, 'error' => 'Run the latest migration first');
		$added = 0;
		$now = $this->now();
		foreach(self::$seedHospitals as $h){
			$dup = $this->db->where('store_id', $this->storeId())
				->group_start()->where('external_key', $h[2])->or_where('name', $h[0])->group_end()
				->get('db_referral_partners')->row();
			if($dup) continue;
			$this->db->insert('db_referral_partners', array(
				'store_id'     => $this->storeId(),
				'partner_type' => 'hospital',
				'name'         => $h[0],
				'specialty'    => $h[1],
				'is_active'    => 1,
				'is_seeded'    => 1,
				'source_system'=> 'seed',
				'external_key' => $h[2],
				'created_at'   => $now,
			));
			$added++;
		}
		return array('ok' => true, 'added' => $added,
			'message' => $added ? $added . ' hospital(s) added to the directory' : 'The standard hospitals are already in the directory');
	}

	// ------------------------------------------------------------------
	// Partner directory
	// ------------------------------------------------------------------

	public function partnerTypes(){ return self::$partnerTypes; }

	/**
	 * Specialty / focus choices for one partner type.
	 *
	 * Starts from the seeded list below, then appends whatever this store has
	 * already used on its own partners — so a value typed under "Other" turns
	 * into a pickable choice next time without anyone maintaining a list.
	 */
	public function specialtyOptions($type){
		if(!in_array($type, self::$partnerTypes, true)) return array();
		$list = isset(self::$seedSpecialties[$type]) ? self::$seedSpecialties[$type] : array();
		$used = $this->db->select('specialty')->distinct()
			->where('store_id', $this->storeId())
			->where('partner_type', $type)
			->where('specialty !=', '')
			->order_by('specialty')->get('db_referral_partners')->result();
		foreach($used as $u){
			$v = trim((string)$u->specialty);
			if($v !== '' && !in_array($v, $list, true)) $list[] = $v;
		}
		return $list;
	}

	/** Active partners, optionally narrowed to one type, grouped for pickers. */
	public function partners($type = null, $activeOnly = true){
		$q = $this->db->where('store_id', $this->storeId());
		if($activeOnly) $q->where('is_active', 1);
		if($type && in_array($type, self::$partnerTypes, true)) $q->where('partner_type', $type);
		return $q->order_by('partner_type')->order_by('name')->get('db_referral_partners')->result();
	}

	/** Partners grouped by type for a single dropdown that keeps the sections readable. */
	public function partnersGrouped($activeOnly = true){
		$out = array('lab' => array(), 'hospital' => array(), 'doctor' => array());
		foreach($this->partners(null, $activeOnly) as $p){
			if(isset($out[$p->partner_type])) $out[$p->partner_type][] = $p;
		}
		return $out;
	}

	public function getPartner($id){
		return $this->db->where('id', (int)$id)->where('store_id', $this->storeId())
			->get('db_referral_partners')->row();
	}

	public function savePartner($id, array $d){
		$type = (string)($d['partner_type'] ?? 'hospital');
		if(!in_array($type, self::$partnerTypes, true)) return array('ok' => false, 'error' => 'Partner type must be a lab, hospital or doctor');
		$name = trim((string)($d['name'] ?? ''));
		if($name === '') return array('ok' => false, 'error' => 'Partner name is required');

		$row = array(
			'partner_type'   => $type,
			'name'           => mb_substr($name, 0, 160),
			'specialty'      => mb_substr(trim((string)($d['specialty'] ?? '')), 0, 160) ?: null,
			'contact_person' => mb_substr(trim((string)($d['contact_person'] ?? '')), 0, 120) ?: null,
			'phone'          => mb_substr(trim((string)($d['phone'] ?? '')), 0, 40) ?: null,
			'email'          => mb_substr(trim((string)($d['email'] ?? '')), 0, 120) ?: null,
			'address'        => mb_substr(trim((string)($d['address'] ?? '')), 0, 255) ?: null,
			'notes'          => trim((string)($d['notes'] ?? '')) ?: null,
			'is_active'      => empty($d['is_active']) ? 0 : 1,
			'updated_by'     => $this->userId(),
			'updated_at'     => $this->now(),
		);

		if((int)$id > 0){
			$existing = $this->getPartner((int)$id);
			if(!$existing) return array('ok' => false, 'error' => 'Partner not found');
			$this->db->where('id', (int)$id)->update('db_referral_partners', $row);
			return array('ok' => true, 'partner_id' => (int)$id, 'message' => 'Referral partner updated');
		}

		$row['store_id'] = $this->storeId();
		$row['created_by'] = $this->userId();
		$row['created_at'] = $this->now();
		$this->db->insert('db_referral_partners', $row);
		$id = (int)$this->db->insert_id();
		return $id ? array('ok' => true, 'partner_id' => $id, 'message' => 'Referral partner added')
		           : array('ok' => false, 'error' => 'Could not save the referral partner');
	}

	/**
	 * Remove a partner from the directory. Referrals already made to it keep
	 * their partner name snapshot so the patient record still reads correctly.
	 */
	public function removePartner($id){
		$p = $this->getPartner((int)$id);
		if(!$p) return array('ok' => false, 'error' => 'Partner not found');
		$this->db->trans_begin();
		$this->db->where('partner_id', (int)$id)->where('store_id', $this->storeId())
			->update('db_patient_referrals', array('partner_id' => null, 'updated_at' => $this->now()));
		$this->db->where('partner_id', (int)$id)->where('store_id', $this->storeId())
			->update('db_referral_meetings', array('partner_id' => null));
		$this->db->where('id', (int)$id)->delete('db_referral_partners');
		$this->db->trans_commit();
		return array('ok' => true, 'message' => 'Referral partner removed');
	}

	// ------------------------------------------------------------------
	// Patient referrals
	// ------------------------------------------------------------------

	/** Open and in-progress first, most recent first within each group. */
	public function forPatient($patientId){
		$rows = $this->db->where('store_id', $this->storeId())
			->where('patient_id', (int)$patientId)
			->order_by('referred_at', 'desc')->get('db_patient_referrals')->result();
		$weight = array('open' => 0, 'in_progress' => 1, 'completed' => 2, 'cancelled' => 3);
		usort($rows, function($a, $b) use ($weight){
			$wa = $weight[$a->status] ?? 9; $wb = $weight[$b->status] ?? 9;
			return $wa !== $wb ? $wa <=> $wb : strcmp((string)$b->referred_at, (string)$a->referred_at);
		});
		$sourceLabel = array('assessment' => 'the assessment', 'visit' => 'the visit', 'manual' => 'added by hand');
		$urgencyLabel = array();
		foreach($this->optionList('urgency') as $o){ $urgencyLabel[(string)$o->code] = (string)$o->label; }
		foreach($rows as $r){
			// First name only — history feeds never show a login username.
			$r->referred_by_name = function_exists('mp_first_name')
				? mp_first_name((int)$r->referred_by, '') : '';
			$r->source_label = $sourceLabel[$r->source] ?? 'added by hand';
			$r->urgency_label = $urgencyLabel[(string)$r->urgency]
				?? ucfirst(str_replace('_', ' ', (string)$r->urgency));
			$r->meetings = $this->db->where('store_id', $this->storeId())
				->where('referral_id', (int)$r->id)
				->order_by('meeting_date', 'desc')->order_by('id', 'desc')
				->get('db_referral_meetings')->result();
		}
		return $rows;
	}

	public function getReferral($id){
		return $this->db->where('id', (int)$id)->where('store_id', $this->storeId())
			->get('db_patient_referrals')->row();
	}

	public function saveReferral(array $d, $referralId = 0){
		$patientId = (int)($d['patient_id'] ?? 0);
		$partnerId = (int)($d['partner_id'] ?? 0);
		if($patientId <= 0) return array('ok' => false, 'error' => 'A patient is required');
		$partner = $this->getPartner($partnerId);
		if(!$partner) return array('ok' => false, 'error' => 'Choose a referral partner');

		$urgency = (string)($d['urgency'] ?? '');
		if(!$this->optionExists('urgency', $urgency)) $urgency = $this->defaultOptionCode('urgency') ?: 'routine';
		$source = (string)($d['source'] ?? 'manual');
		if(!in_array($source, array('assessment', 'visit', 'manual'), true)) $source = 'manual';

		$row = array(
			'partner_id'    => (int)$partner->id,
			'partner_name'  => $partner->name,
			'partner_type'  => $partner->partner_type,
			'source'        => $source,
			'source_id'     => !empty($d['source_id']) ? (int)$d['source_id'] : null,
			'encounter_id'  => !empty($d['encounter_id']) ? (int)$d['encounter_id'] : null,
			'assessment_id' => !empty($d['assessment_id']) ? (int)$d['assessment_id'] : null,
			'reason'        => mb_substr(trim((string)($d['reason'] ?? '')), 0, 255) ?: null,
			'notes'         => trim((string)($d['notes'] ?? '')) ?: null,
			'urgency'       => $urgency,
			'next_due_date' => self::dateOrNull($d['next_due_date'] ?? null),
			'updated_at'    => $this->now(),
		);

		if((int)$referralId > 0){
			$existing = $this->getReferral((int)$referralId);
			if(!$existing) return array('ok' => false, 'error' => 'Referral not found');
			// An open referral may be re-pointed; a closed one keeps its history.
			if(in_array($existing->status, array('completed', 'cancelled'), true)){
				$row = array_intersect_key($row, array_flip(array('notes', 'reason', 'next_due_date', 'updated_at')));
			}
			$this->db->where('id', (int)$referralId)->update('db_patient_referrals', $row);
			return array('ok' => true, 'referral_id' => (int)$referralId, 'message' => 'Referral updated');
		}

		$row['store_id'] = $this->storeId();
		$row['patient_id'] = $patientId;
		$row['status'] = 'open';
		$row['referred_by'] = $this->userId();
		$row['referred_at'] = $this->now();
		$row['created_at'] = $this->now();
		$this->db->insert('db_patient_referrals', $row);
		$id = (int)$this->db->insert_id();
		return $id ? array('ok' => true, 'referral_id' => $id, 'message' => 'Patient referred to '.$partner->name)
		           : array('ok' => false, 'error' => 'Could not record the referral');
	}

	public function setReferralStatus($id, $status){
		if(!in_array($status, self::$refStatuses, true)) return array('ok' => false, 'error' => 'Unknown status');
		$r = $this->getReferral((int)$id);
		if(!$r) return array('ok' => false, 'error' => 'Referral not found');
		$upd = array('status' => $status, 'updated_at' => $this->now());
		if(in_array($status, array('completed', 'cancelled'), true)){
			$upd['closed_at'] = $this->now();
			$upd['closed_by'] = $this->userId();
		} else {
			$upd['closed_at'] = null;
			$upd['closed_by'] = null;
		}
		$this->db->where('id', (int)$id)->update('db_patient_referrals', $upd);
		return array('ok' => true, 'message' => 'Referral marked '.str_replace('_', ' ', $status));
	}

	// ------------------------------------------------------------------
	// Meeting log — what happened at the partner, and what is due next
	// ------------------------------------------------------------------

	public function addMeeting($referralId, array $d){
		$r = $this->getReferral((int)$referralId);
		if(!$r) return array('ok' => false, 'error' => 'Referral not found');
		$meetingDate = self::dateOrNull($d['meeting_date'] ?? null);
		if(!$meetingDate) return array('ok' => false, 'error' => 'Meeting date is required');

		$this->db->trans_begin();
		$this->db->insert('db_referral_meetings', array(
			'store_id'     => $this->storeId(),
			'referral_id'  => (int)$r->id,
			'patient_id'   => (int)$r->patient_id,
			'partner_id'   => !empty($r->partner_id) ? (int)$r->partner_id : null,
			'meeting_date' => $meetingDate,
			'met_with'     => mb_substr(trim((string)($d['met_with'] ?? '')), 0, 160) ?: null,
			'outcome'      => mb_substr(trim((string)($d['outcome'] ?? '')), 0, 255) ?: null,
			'notes'        => trim((string)($d['notes'] ?? '')) ?: null,
			'next_due_date'=> self::dateOrNull($d['next_due_date'] ?? null),
			'created_by'   => $this->userId(),
			'created_at'   => $this->now(),
		));
		// The referral carries the date of whatever is due next, so the whole
		// patient list can be scanned for overdue work in one pass.
		$nextDue = self::dateOrNull($d['next_due_date'] ?? null);
		$this->db->where('id', (int)$r->id)->update('db_patient_referrals', array(
			'next_due_date' => $nextDue,
			'updated_at'    => $this->now(),
		));
		$this->db->trans_commit();
		return array('ok' => true, 'message' => 'Meeting recorded');
	}

	public function removeMeeting($id){
		$m = $this->db->where('id', (int)$id)->where('store_id', $this->storeId())
			->get('db_referral_meetings')->row();
		if(!$m) return array('ok' => false, 'error' => 'Meeting not found');
		$this->db->where('id', (int)$id)->delete('db_referral_meetings');
		return array('ok' => true, 'message' => 'Meeting removed');
	}

	// ------------------------------------------------------------------
	// Panel payload — everything the shared patient panel view needs
	// ------------------------------------------------------------------

	/**
	 * @param int   $patientId
	 * @param array $context source hint: source, source_id, encounter_id, assessment_id
	 */
	public function panelData($patientId, array $context = array()){
		return array(
			'refp_patient_id' => (int)$patientId,
			'refp_referrals'  => $this->forPatient((int)$patientId),
			'refp_partners'   => $this->partnersGrouped(),
			'refp_reasons'    => $this->optionList('reason'),
			'refp_urgencies'  => $this->optionList('urgency'),
			'refp_default_urgency' => $this->defaultOptionCode('urgency'),
			'refp_context'    => array(
				'source'        => in_array(($context['source'] ?? 'manual'), array('assessment', 'visit', 'manual'), true) ? $context['source'] : 'manual',
				'source_id'     => (int)($context['source_id'] ?? 0),
				'encounter_id'  => (int)($context['encounter_id'] ?? 0),
				'assessment_id' => (int)($context['assessment_id'] ?? 0),
			),
			'refp_can' => array(
				'manage' => function_exists('physio_can') ? physio_can('referrals_manage') : false,
			),
		);
	}

	private static function slug($value){
		$value = strtolower(trim((string)$value));
		$value = preg_replace('/[^a-z0-9]+/', '_', $value);
		return trim((string)$value, '_');
	}

	private static function dateOrNull($value){
		$value = trim((string)$value);
		if($value === '') return null;
		$ts = strtotime($value);
		return $ts ? date('Y-m-d', $ts) : null;
	}
}
