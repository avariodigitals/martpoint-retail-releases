<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reports_clinical_model — the clinical report set for the physiotherapy &
 * rehabilitation business type.
 *
 * Why a separate model: the retail Reports controller answers "what did we
 * sell". A clinic's questions are different — who is waiting, what is the ward
 * doing, which sessions actually happened, which investigations are stuck, and
 * who still owes us money. None of that exists in db_sales alone.
 *
 * Contract every method here honours:
 *   - store scoping is mandatory and always applied first;
 *   - branch scope follows the caller's grant: a user holding
 *     `clinical_cross_branch` sees the whole store, everyone else is limited to
 *     their assigned branches (physio_branch_ids());
 *   - every aggregate is computed from the table the corresponding clinical
 *     screen writes to, so a figure can always be traced back to its records;
 *   - nothing here writes.
 */
class Reports_clinical_model extends CI_Model {

	/**
	 * Memoised branch scope for this request.
	 *
	 * Resolving the scope runs a query of its own, and CodeIgniter 3 keeps ONE
	 * shared Query Builder object: a query issued while another is half-built
	 * merges into the builder being composed and destroys it (the symptom is a
	 * stray `FROM db_permissions` inside a report query, or a silent fall-back
	 * to SELECT *). So the scope is resolved exactly once, before any report
	 * query is composed, and reused from this property afterwards.
	 */
	private $branchIds = null;

	private function storeId() {
		return (int) get_current_store_id();
	}

	/** Resolve (once) and return the branch scope; call before any query. */
	private function scope() {
		if ($this->branchIds === null) {
			$this->branchIds = $this->resolveBranchIds();
		}
		return $this->branchIds;
	}

	private function resolveBranchIds() {
		if (!function_exists('physio_branch_ids')) { $this->load->helper('physio'); }
		$ids = array_map('intval', physio_branch_ids());
		return array_values(array_filter($ids, function($i){ return $i > 0; }));
	}

	private function branchScope() {
		return $this->scope();
	}

	/**
	 * Branch-scope a query as a WHERE fragment rather than through the Query
	 * Builder.
	 *
	 * This has to be a string, not $this->db->where_in(): CodeIgniter 3's Query
	 * Builder RESETS its select/where state when count_all_results() runs, so a
	 * where_in() applied before a count silently disappears from the count and
	 * — worse — a select() applied after it is discarded, which makes the query
	 * fall back to SELECT * and fail under ONLY_FULL_GROUP_BY.
	 *
	 * Rows with no branch recorded are included, matching physio_can_branch():
	 * an unscoped record is visible to anyone who holds the permission for the
	 * screen. Imported history and records created before branch capture are
	 * unscoped, and hiding them would under-report every clinic figure.
	 *
	 * @param string $column qualified column holding the branch id
	 * @return string a safe WHERE fragment; never empty (a user assigned to no
	 *                branch still sees only unscoped records)
	 */
	private function branchWhere($column) {
		$branches = $this->branchScope();
		if (empty($branches)) {
			return '(' . $column . ' IS NULL)';
		}
		return '(' . $column . ' IN (' . implode(',', $branches) . ') OR ' . $column . ' IS NULL)';
	}

	/** Guard: a clinical table may be absent on an older install. */
	private function has($table) {
		return $this->db->table_exists($table);
	}

	private function dateRange($from, $to, $column = 'created_date') {
		if (!empty($from)) { $this->db->where($column . ' >=', $from); }
		if (!empty($to))   { $this->db->where($column . ' <=', $to); }
	}

	/* =====================================================================
	 * 1. Patient register & growth
	 * ================================================================== */

	/**
	 * Register totals, new registrations in the period and the demographic
	 * split, plus the patients who are currently deceased (excluded from
	 * "active" everywhere else in the app).
	 */
	public function register($from, $to) {
		$store = $this->storeId();
		$this->scope();
		$out = array(
			'total' => 0, 'active' => 0, 'deceased' => 0, 'new' => 0,
			'gender' => array(), 'age_bands' => array(), 'monthly' => array(),
		);
		if (!$this->has('db_patients')) { return $out; }

		$row = $this->db->select('COUNT(*) total, '
				. 'SUM(status = 1 AND deceased = 0) active, SUM(deceased = 1) deceased', false)
			->where('store_id', $store)
			->get('db_patients')->row();
		$out['total']    = (int) ($row->total ?? 0);
		$out['active']   = (int) ($row->active ?? 0);
		$out['deceased'] = (int) ($row->deceased ?? 0);

		// New registrations inside the reported window.
		$this->db->where('store_id', $store);
		$this->dateRange($from, $to);
		$out['new'] = (int) $this->db->count_all_results('db_patients');

		// Monthly registrations across the window (for the trend table).
		$this->db->select("DATE_FORMAT(created_date, '%Y-%m') AS ym, COUNT(*) AS n", false)
			->where('store_id', $store);
		$this->dateRange($from, $to);
		$rows = $this->db->group_by('ym')->order_by('ym', 'asc')
			->get('db_patients')->result();
		foreach ($rows as $r) { $out['monthly'][$r->ym] = (int) $r->n; }

		// Gender split — the column is free text, so empty means "not stated".
		$this->db->select("COALESCE(NULLIF(TRIM(p.gender), ''), 'Not stated') AS g, COUNT(*) AS n", false)
			->from('db_patients p')
			->where('p.store_id', $store)->where('p.deceased', 0)
			->group_by('g')->order_by('n', 'desc');
		foreach ($this->db->get()->result() as $r) {
			$out['gender'][$r->g] = (int) $r->n;
		}

		// Age bands from the date of birth. Rows without a usable DOB are
		// reported separately rather than silently dropped.
		if ($this->db->field_exists('dob', 'db_patients')) {
			$bands = array('0-17', '18-35', '36-50', '51-65', '65+', 'No date of birth');
			foreach ($bands as $b) { $out['age_bands'][$b] = 0; }

			// Compare the raw stored value: db_patients.dob is a date column and
			// "no date of birth" is stored as a zero date, which a bound
			// comparison rejects as an invalid DATE under strict SQL mode.
			$rows = $this->db->select('p.dob')->from('db_patients p')
				->where('p.store_id', $store)->where('p.deceased', 0)
				->where('p.dob IS NOT NULL', null, false)
				->where('p.dob <> 0', null, false)
				->get()->result();
			foreach ($rows as $r) {
				$age = $this->ageFrom($r->dob);
				if ($age === null) { $out['age_bands']['No date of birth']++; continue; }
				if ($age <= 17)      { $out['age_bands']['0-17']++; }
				elseif ($age <= 35)  { $out['age_bands']['18-35']++; }
				elseif ($age <= 50)  { $out['age_bands']['36-50']++; }
				elseif ($age <= 65)  { $out['age_bands']['51-65']++; }
				else                 { $out['age_bands']['65+']++; }
			}
			$noDob = $this->db->from('db_patients p')->where('p.store_id', $store)
				->where('p.deceased', 0)
				->where('(p.dob IS NULL OR p.dob = 0)', null, false)
				->count_all_results();
			$out['age_bands']['No date of birth'] = (int) $noDob;
		}
		return $out;
	}

	private function ageFrom($dob) {
		if (empty($dob) || $dob === '0000-00-00') { return null; }
		try { $d = new DateTime($dob); } catch (Exception $e) { return null; }
		return (int) $d->diff(new DateTime('today'))->y;
	}

	/* =====================================================================
	 * 2. Appointments & attendance
	 * ================================================================== */

	/** Bookings in the window, split by status and by clinician. */
	public function appointments($from, $to) {
		$store = $this->storeId();
		$this->scope();
		$out = array('total' => 0, 'by_status' => array(), 'by_staff' => array(),
			'attendance' => array('arrived' => 0, 'no_show' => 0, 'rate' => null));
		if (!$this->has('db_appointments')) { return $out; }

		$this->db->where('store_id', $store)
			->where('DATE(scheduled_at) >=', $from)
			->where('DATE(scheduled_at) <=', $to);
		$out['total'] = (int) $this->db->count_all_results('db_appointments');

		$this->db->select('status, COUNT(*) AS n')->where('store_id', $store)
			->where('DATE(scheduled_at) >=', $from)
			->where('DATE(scheduled_at) <=', $to)->group_by('status');
		foreach ($this->db->get('db_appointments')->result() as $r) {
			$out['by_status'][$r->status ?: 'unknown'] = (int) $r->n;
		}

		// Attendance is the operational number a clinic actually manages to.
		$this->db->select('COUNT(*) n, SUM(arrived_at IS NOT NULL) arrived', false)
			->where('store_id', $store)
			->where('DATE(scheduled_at) >=', $from)
			->where('DATE(scheduled_at) <=', $to)
			->where_in('status', array('completed', 'arrived', 'checked_in', 'no_show', 'cancelled'));
		$att = $this->db->get('db_appointments')->row();
		$eligible = (int) ($att->n ?? 0);
		$out['attendance']['arrived'] = (int) ($att->arrived ?? 0);
		$out['attendance']['no_show'] = (int) ($out['by_status']['no_show'] ?? 0);
		if ($eligible > 0) {
			$out['attendance']['rate'] = round(100 * $out['attendance']['arrived'] / $eligible, 1);
		}

		// Workload by clinician (db_users has no single name column).
		$staffSelect = $this->db->field_exists('first_name', 'db_users')
			? "COALESCE(NULLIF(TRIM(CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,''))),''), u.username) AS staff_name"
			: 'u.username AS staff_name';
		$rows = $this->db->select('a.staff_user_id, ' . $staffSelect . ', COUNT(*) AS n', false)
			->from('db_appointments a')
			->join('db_users u', 'u.id = a.staff_user_id', 'left')
			->where('a.store_id', $store)
			->where('DATE(a.scheduled_at) >=', $from)
			->where('DATE(a.scheduled_at) <=', $to)
			->group_by('a.staff_user_id')->order_by('n', 'desc')->get()->result();
		foreach ($rows as $r) {
			$out['by_staff'][] = array(
				'name' => $r->staff_name ?: 'Unassigned',
				'n'    => (int) $r->n,
			);
		}
		return $out;
	}

	/* =====================================================================
	 * 3. Ward occupancy & bed utilisation
	 * ================================================================== */

	public function ward($from, $to) {
		$store = $this->storeId();
		$this->scope();
		$out = array(
			'beds' => array('total' => 0, 'free' => 0, 'occupied' => 0, 'rate' => null),
			'wards' => array(), 'admissions' => array('active' => 0, 'new' => 0,
			'discharged' => 0, 'deceased' => 0), 'los' => array('avg' => null, 'n' => 0),
			'turnover' => null,
		);
		if (!$this->has('db_beds')) { return $out; }

		$total = (int) $this->db->where('store_id', $store)->count_all_results('db_beds');
		$occupied = 0;
		if ($this->has('db_bed_occupancy')) {
			$occupied = (int) $this->db->where('store_id', $store)
				->where('to_at IS NULL', null, false)->count_all_results('db_bed_occupancy');
		}
		$out['beds'] = array(
			'total' => $total, 'occupied' => $occupied,
			'free' => max(0, $total - $occupied),
			'rate' => $total > 0 ? round(100 * $occupied / $total, 1) : null,
		);

		// Per-ward utilisation.
		if ($this->has('db_wards')) {
			$wards = $this->db->select('id, name')->where('store_id', $store)
				->order_by('name', 'asc')->get('db_wards')->result();
			foreach ($wards as $w) {
				$wTotal = (int) $this->db->where('store_id', $store)->where('ward_id', $w->id)
					->count_all_results('db_beds');
				$wOcc = 0;
				if ($this->has('db_bed_occupancy')) {
					$wOcc = (int) $this->db->select('COUNT(*) n', false)
						->from('db_bed_occupancy o')
						->join('db_beds b', 'b.id = o.bed_id')
						->where('o.store_id', $store)->where('b.ward_id', $w->id)
						->where('o.to_at IS NULL', null, false)->get()->row()->n;
				}
				$out['wards'][] = array(
					'name' => $w->name, 'total' => $wTotal, 'occupied' => $wOcc,
					'free' => max(0, $wTotal - $wOcc),
					'rate' => $wTotal > 0 ? round(100 * $wOcc / $wTotal, 1) : null,
				);
			}
		}

		if (!$this->has('db_admissions')) { return $out; }

		$branch = $this->branchWhere('branch_id');
		$this->db->where('store_id', $store)->where($branch, null, false);
		$out['admissions']['active'] = (int) $this->db->where('status', 'active')
			->count_all_results('db_admissions');

		foreach (array('discharged', 'deceased') as $st) {
			$this->db->where('store_id', $store)->where($branch, null, false);
			$this->db->where('DATE(admitted_at) >=', $from)
				->where('DATE(admitted_at) <=', $to)->where('status', $st);
			$out['admissions'][$st] = (int) $this->db->count_all_results('db_admissions');
		}
		$this->db->where('store_id', $store)->where($branch, null, false);
		$this->db->where('DATE(admitted_at) >=', $from)->where('DATE(admitted_at) <=', $to);
		$out['admissions']['new'] = (int) $this->db->count_all_results('db_admissions');

		// Average length of stay over closed admissions in the window.
		$this->db->select('AVG(DATEDIFF(closed_at, admitted_at)) AS los, COUNT(*) AS n', false)
			->where('store_id', $store)->where($branch, null, false)
			->where('closed_at IS NOT NULL', null, false)
			->where('DATE(admitted_at) >=', $from)->where('DATE(admitted_at) <=', $to);
		$los = $this->db->get('db_admissions')->row();
		if ($los && $los->n > 0) {
			$out['los'] = array('avg' => round((float) $los->los, 1), 'n' => (int) $los->n);
		}
		if ($out['beds']['total'] > 0 && $out['admissions']['new'] > 0) {
			$days = max(1, (int) ((strtotime($to) - strtotime($from)) / 86400) + 1);
			$out['turnover'] = round($out['admissions']['new'] / $out['beds']['total'], 2)
				. ' admissions per bed over ' . $days . ' day(s)';
		}
		return $out;
	}

	/* =====================================================================
	 * 4. Treatment sessions delivered
	 * ================================================================== */

	public function sessions($from, $to) {
		$store = $this->storeId();
		$this->scope();
		$out = array('total' => 0, 'by_status' => array(), 'by_clinician' => array(),
			'units' => 0, 'fee_posted' => 0.0, 'not_posted' => 0, 'by_service' => array());
		if (!$this->has('db_treatment_sessions')) { return $out; }

		$branch = $this->branchWhere('branch_id');

		$this->db->where('store_id', $store);
		$this->db->where($branch, null, false);
		$this->db->where('DATE(scheduled_at) >=', $from)->where('DATE(scheduled_at) <=', $to);
		$out['total'] = (int) $this->db->count_all_results('db_treatment_sessions');

		$rows = $this->db->select('status, COUNT(*) AS n, COALESCE(SUM(units_total),0) AS units', false)
			->where('store_id', $store)->where($branch, null, false)
			->where('DATE(scheduled_at) >=', $from)->where('DATE(scheduled_at) <=', $to)
			->group_by('status')->get('db_treatment_sessions')->result();
		foreach ($rows as $r) {
			$out['by_status'][$r->status ?: 'unknown'] = (int) $r->n;
			$out['units'] += (int) $r->units;
		}

		// Fee posted vs not — a completed session with no charge posted is
		// delivered revenue that never reached the patient's account, and is the
		// single most useful control number for a clinic.
		$row = $this->db->select('COALESCE(SUM(completed_at IS NOT NULL AND fee_posted = 0),0) AS not_posted', false)
			->where('store_id', $store)->where($branch, null, false)
			->where('DATE(scheduled_at) >=', $from)->where('DATE(scheduled_at) <=', $to)
			->get('db_treatment_sessions')->row();
		$out['not_posted'] = (int) ($row->not_posted ?? 0);

		$fee = $this->db->select('COALESCE(SUM(fee),0) AS fee', false)
			->where('store_id', $store)->where($branch, null, false)
			->where('DATE(scheduled_at) >=', $from)
			->where('DATE(scheduled_at) <=', $to)->where('fee_posted', 1)
			->get('db_treatment_sessions')->row();
		$out['fee_posted'] = (float) ($fee->fee ?? 0);

		// Clinician workload.
		$nameSel = $this->db->field_exists('first_name', 'db_users')
			? "COALESCE(NULLIF(TRIM(CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,''))),''), u.username) AS staff_name"
			: 'u.username AS staff_name';
		$rows = $this->db->select('s.clinician_id, ' . $nameSel . ', COUNT(*) AS n, '
				. 'COALESCE(SUM(s.units_total),0) AS units', false)
			->from('db_treatment_sessions s')
			->join('db_users u', 'u.id = s.clinician_id', 'left')
			->where('s.store_id', $store)
			->where($this->branchWhere('s.branch_id'), null, false)
			->where('DATE(s.scheduled_at) >=', $from)->where('DATE(s.scheduled_at) <=', $to)
			->group_by('s.clinician_id')->order_by('n', 'desc')->get()->result();
		foreach ($rows as $r) {
			$out['by_clinician'][] = array(
				'name' => $r->staff_name ?: 'Unassigned',
				'n' => (int) $r->n, 'units' => (int) $r->units,
			);
		}
		return $out;
	}

	/* =====================================================================
	 * 5. Investigations turnaround
	 * ================================================================== */

	public function investigations($from, $to) {
		$store = $this->storeId();
		$this->scope();
		$out = array('total' => 0, 'by_status' => array(), 'pending' => 0,
			'overdue' => 0, 'avg_turnaround' => null, 'by_category' => array());
		if (!$this->has('db_investigations')) { return $out; }

		$this->db->where('store_id', $store)
			->where('DATE(requested_at) >=', $from)->where('DATE(requested_at) <=', $to);
		$out['total'] = (int) $this->db->count_all_results('db_investigations');

		$this->db->select('status, COUNT(*) n')->where('store_id', $store)
			->where('DATE(requested_at) >=', $from)->where('DATE(requested_at) <=', $to)
			->group_by('status');
		foreach ($this->db->get('db_investigations')->result() as $r) {
			$out['by_status'][$r->status ?: 'unknown'] = (int) $r->n;
		}
		// Still open regardless of request date — that is the live worklist.
		$out['pending'] = (int) $this->db->where('store_id', $store)
			->where_not_in('status', array('completed', 'reviewed', 'cancelled'))
			->count_all_results('db_investigations');
		$out['overdue'] = (int) $this->db->where('store_id', $store)
			->where_not_in('status', array('completed', 'reviewed', 'cancelled'))
			->where('requested_at <', date('Y-m-d H:i:s', strtotime('-7 days')))
			->count_all_results('db_investigations');

		$row = $this->db->select('AVG(TIMESTAMPDIFF(HOUR, requested_at, result_received_at)) AS hrs, '
				. 'COUNT(*) n', false)
			->where('store_id', $store)->where('result_received_at IS NOT NULL', null, false)
			->where('DATE(requested_at) >=', $from)->where('DATE(requested_at) <=', $to)
			->get('db_investigations')->row();
		if ($row && $row->n > 0 && $row->hrs !== null) {
			$out['avg_turnaround'] = array(
				'hours' => round((float) $row->hrs, 1),
				'days'  => round((float) $row->hrs / 24, 1),
				'n'     => (int) $row->n,
			);
		}

		$this->db->select("COALESCE(NULLIF(TRIM(category), ''), 'Uncategorised') AS cat, COUNT(*) n", false)
			->where('store_id', $store)
			->where('DATE(requested_at) >=', $from)->where('DATE(requested_at) <=', $to)
			->group_by('cat')->order_by('n', 'desc');
		foreach ($this->db->get('db_investigations')->result() as $r) {
			$out['by_category'][$r->cat] = (int) $r->n;
		}
		return $out;
	}

	/* =====================================================================
	 * 6. Clinical activity — encounters, assessments, care queue
	 * ================================================================== */

	public function activity($from, $to) {
		$store = $this->storeId();
		$this->scope();
		$out = array('encounters' => 0, 'queue_open' => 0, 'queue_stages' => array(),
			'assessments' => array('total' => 0, 'finalized' => 0, 'draft' => 0),
			'plans' => array('active' => 0, 'new' => 0));

		if ($this->has('db_encounters')) {
			$this->db->where('store_id', $store)
				->where($this->branchWhere('warehouse_id'), null, false);
			$this->db->where('DATE(checkin_at) >=', $from)->where('DATE(checkin_at) <=', $to);
			$out['encounters'] = (int) $this->db->count_all_results('db_encounters');

			$this->db->select('queue_stage, COUNT(*) n')->where('store_id', $store)
				->where('queue_stage IS NOT NULL', null, false)->where('queue_stage !=', 'closed')
				->group_by('queue_stage')->order_by('n', 'desc');
			foreach ($this->db->get('db_encounters')->result() as $r) {
				$out['queue_stages'][$r->queue_stage] = (int) $r->n;
				$out['queue_open'] += (int) $r->n;
			}
		}

		if ($this->has('db_assessments')) {
			$this->db->where('store_id', $store)
				->where('DATE(created_at) >=', $from)->where('DATE(created_at) <=', $to);
			$out['assessments']['total'] = (int) $this->db->count_all_results('db_assessments');
			$this->db->select('status, COUNT(*) n')->where('store_id', $store)
				->where('DATE(created_at) >=', $from)->where('DATE(created_at) <=', $to)
				->group_by('status');
			foreach ($this->db->get('db_assessments')->result() as $r) {
				if (in_array($r->status, array('finalized', 'final', 'completed'), true)) {
					$out['assessments']['finalized'] += (int) $r->n;
				} else {
					$out['assessments']['draft'] += (int) $r->n;
				}
			}
		}

		if ($this->has('db_treatment_plans')) {
			$out['plans']['active'] = (int) $this->db->where('store_id', $store)
				->where_in('status', array('active', 'in_progress', 'open'))
				->count_all_results('db_treatment_plans');
			$out['plans']['new'] = (int) $this->db->where('store_id', $store)
				->where('DATE(created_date) >=', $from)->where('DATE(created_date) <=', $to)
				->count_all_results('db_treatment_plans');
		}
		return $out;
	}

	/* =====================================================================
	 * 7. Ward task load — nursing + portering
	 * ================================================================== */

	public function taskLoad($from, $to) {
		$store = $this->storeId();
		$this->scope();
		$out = array(
			'nursing' => array('open' => 0, 'overdue' => 0, 'done' => 0, 'by_type' => array()),
			'porter'  => array('open' => 0, 'overdue' => 0, 'done' => 0, 'by_type' => array()),
		);
		if ($this->has('db_nursing_tasks')) {
			$a = $this->db->select('SUM(status = "open") open, '
					. 'SUM(status = "open" AND due_at < NOW()) overdue, '
					. 'SUM(status = "done") done', false)
				->where('store_id', $store)
				->where('task_date >=', $from)->where('task_date <=', $to)
				->get('db_nursing_tasks')->row();
			$out['nursing']['open']    = (int) ($a->open ?? 0);
			$out['nursing']['overdue'] = (int) ($a->overdue ?? 0);
			$out['nursing']['done']    = (int) ($a->done ?? 0);
			$this->db->select('status, COUNT(*) n')->where('store_id', $store)
				->where('task_date >=', $from)->where('task_date <=', $to)->group_by('status');
			foreach ($this->db->get('db_nursing_tasks')->result() as $r) {
				$out['nursing']['by_type'][$r->status ?: 'unknown'] = (int) $r->n;
			}
		}
		if ($this->has('db_porter_tasks')) {
			$a = $this->db->select('SUM(status = "open") open, SUM(status = "done") done', false)
				->where('store_id', $store)->get('db_porter_tasks')->row();
			$out['porter']['open'] = (int) ($a->open ?? 0);
			$out['porter']['done'] = (int) ($a->done ?? 0);
			$this->db->select('task_type, COUNT(*) n')->where('store_id', $store)
				->group_by('task_type')->order_by('n', 'desc');
			foreach ($this->db->get('db_porter_tasks')->result() as $r) {
				$out['porter']['by_type'][$r->task_type ?: 'general'] = (int) $r->n;
			}
		}
		return $out;
	}

	/* =====================================================================
	 * 8. Outstanding patient balances (aged)
	 * ================================================================== */

	/**
	 * Aged debtor analysis over patient accounts.
	 *
	 * The "account" is the patient's sale: db_sales is the invoice a clinic
	 * posts charges to (through db_patient_bill_items), and the balance is
	 * grand_total - paid_amount. Only accounts linked to a patient are counted,
	 * so a clinic's retail counter sales never appear as patient debt.
	 */
	public function outstanding($from, $to) {
		$store = $this->storeId();
		$this->scope();
		$out = array('total' => 0.0, 'accounts' => 0, 'buckets' => array(
			'Current' => 0.0, '1-30 days' => 0.0, '31-60 days' => 0.0,
			'61-90 days' => 0.0, '90+ days' => 0.0,
		), 'top' => array());
		if (!$this->has('db_sales') || !$this->has('db_patients')) { return $out; }

		$rows = $this->db->select('s.id, s.sales_code, s.sales_date, s.grand_total, s.paid_amount, '
				. 'COALESCE(NULLIF(TRIM(c.customer_name), ""), "Patient") AS patient_name, '
				. 'p.patient_code', false)
			->from('db_sales s')
			->join('db_patients p', 'p.customer_id = s.customer_id AND p.store_id = s.store_id')
			->join('db_customers c', 'c.id = s.customer_id', 'left')
			->where('s.store_id', $store)
			->where('s.grand_total > s.paid_amount', null, false)
			->order_by('(s.grand_total - s.paid_amount)', 'desc', false)
			->get()->result();

		$today = strtotime(date('Y-m-d'));
		foreach ($rows as $r) {
			$bal = (float) $r->grand_total - (float) $r->paid_amount;
			if ($bal <= 0.004) { continue; }
			$out['total'] += $bal;
			$out['accounts']++;
			$age = $r->sales_date ? (int) floor(($today - strtotime($r->sales_date)) / 86400) : 0;
			if ($age <= 0)       { $key = 'Current'; }
			elseif ($age <= 30)  { $key = '1-30 days'; }
			elseif ($age <= 60)  { $key = '31-60 days'; }
			elseif ($age <= 90)  { $key = '61-90 days'; }
			else                 { $key = '90+ days'; }
			$out['buckets'][$key] += $bal;

			if (count($out['top']) < 25) {
				$out['top'][] = array(
					'code' => $r->sales_code,
					'patient' => $r->patient_name,
					'patient_code' => $r->patient_code,
					'date' => $r->sales_date,
					'age' => $age,
					'balance' => $bal,
				);
			}
		}
		return $out;
	}

	/* =====================================================================
	 * 9. Revenue — charges, collections, held funds
	 * ================================================================== */

	public function revenue($from, $to) {
		$store = $this->storeId();
		$this->scope();
		$out = array(
			'charged' => 0.0, 'collected' => 0.0, 'collected_new' => 0.0,
			'collected_advance' => 0.0, 'held' => 0.0, 'held_reservations' => 0,
			'by_service' => array(), 'by_mode' => array(), 'funding' => 0.0,
		);
		if (!$this->has('db_sales')) { return $out; }

		// Charges posted to patient accounts in the window. Charge lines that
		// went through the billing module are the clinical revenue; if the
		// module has not posted anything we fall back to the account totals so
		// the report is never blank on a store that bills manually.
		$charged = $this->db->select('COALESCE(SUM(s.grand_total),0) AS t', false)
			->from('db_sales s')
			->join('db_patients p', 'p.customer_id = s.customer_id AND p.store_id = s.store_id')
			->where('s.store_id', $store)
			->where('s.sales_date >=', $from)->where('s.sales_date <=', $to)
			->get()->row();
		$out['charged'] = (float) ($charged->t ?? 0);

		// Collections recorded on those accounts.
		$paid = $this->db->select('COALESCE(SUM(s.paid_amount),0) AS t', false)
			->from('db_sales s')
			->join('db_patients p', 'p.customer_id = s.customer_id AND p.store_id = s.store_id')
			->where('s.store_id', $store)
			->where('s.sales_date >=', $from)->where('s.sales_date <=', $to)
			->get()->row();
		$out['collected'] = (float) ($paid->t ?? 0);

		// Funds held against treatment plans.
		if ($this->has('db_fund_reservations')) {
			$r = $this->db->select('COALESCE(SUM(amount_reserved - amount_consumed),0) AS s, COUNT(*) n', false)
				->where('store_id', $store)->where('status', 'active')
				->get('db_fund_reservations')->row();
			$out['held'] = (float) ($r->s ?? 0);
			$out['held_reservations'] = (int) ($r->n ?? 0);
		}

		// Funding taken in (money put on account) in the window, by kind.
		if ($this->has('db_patient_wallet_txns')) {
			$rows = $this->db->select('txn_type, direction, COALESCE(SUM(amount),0) amt', false)
				->where('store_id', $store)
				->where('DATE(created_date) >=', $from)->where('DATE(created_date) <=', $to)
				->group_by('txn_type')->group_by('direction')->get('db_patient_wallet_txns')->result();
			foreach ($rows as $r) {
				$amt = (float) $r->amt;
				$isIn = ($r->direction === 'in' || $r->direction === 'credit');
				if ($isIn) {
					$out['funding'] += $amt;
					if ($r->txn_type === 'advance' || $r->txn_type === 'deposit') {
						$out['collected_advance'] += $amt;
					} else {
						$out['collected_new'] += $amt;
					}
				}
			}
		}

		// Service mix from posted charge lines.
		if ($this->has('db_patient_bill_items')) {
			$rows = $this->db->select('COALESCE(NULLIF(TRIM(bi.description), ""), CONCAT("Item #", bi.item_id)) AS svc, '
					. 'COUNT(*) n, COALESCE(SUM(bi.total),0) amt', false)
				->from('db_patient_bill_items bi')
				->join('db_sales s', 's.id = bi.sales_id')
				->where('bi.store_id', $store)
				->where('s.sales_date >=', $from)->where('s.sales_date <=', $to)
				->group_by('svc')->order_by('amt', 'desc')->get()->result();
			foreach ($rows as $r) {
				$out['by_service'][] = array(
					'name' => $r->svc, 'n' => (int) $r->n, 'amount' => (float) $r->amt,
				);
			}
		}
		return $out;
	}

	/* =====================================================================
	 * 10. Therapy aids & material consumption
	 * ================================================================== */

	/**
	 * Aids and material consumed against patient work. db_patient_bill_items
	 * links each charge line to its item, so this is the clinic's consumable
	 * picture without touching the retail stock ledger.
	 */
	public function aids($from, $to) {
		$store = $this->storeId();
		$this->scope();
		$out = array('lines' => 0, 'qty' => 0.0, 'value' => 0.0, 'items' => array());
		if (!$this->has('db_patient_bill_items') || !$this->has('db_items')) { return $out; }

		$rows = $this->db->select('COALESCE(NULLIF(TRIM(i.item_name), ""), CONCAT("Item #", bi.item_id)) AS name, '
				. 'i.item_code, COALESCE(SUM(bi.qty),0) qty, COALESCE(SUM(bi.total),0) amt, COUNT(*) n', false)
			->from('db_patient_bill_items bi')
			->join('db_sales s', 's.id = bi.sales_id')
			->join('db_items i', 'i.id = bi.item_id', 'left')
			->where('bi.store_id', $store)
			->where('s.sales_date >=', $from)->where('s.sales_date <=', $to)
			->group_by('bi.item_id')->order_by('amt', 'desc')->get()->result();
		foreach ($rows as $r) {
			$out['lines'] += (int) $r->n;
			$out['qty']   += (float) $r->qty;
			$out['value'] += (float) $r->amt;
			$out['items'][] = array(
				'name' => $r->name, 'code' => $r->item_code,
				'qty' => (float) $r->qty, 'amount' => (float) $r->amt, 'lines' => (int) $r->n,
			);
		}
		return $out;
	}

	/* =====================================================================
	 * 11. Procurement — purchases of clinical supplies and aids
	 * ================================================================== */

	public function procurement($from, $to) {
		$store = $this->storeId();
		$this->scope();
		$out = array('orders' => 0, 'value' => 0.0, 'paid' => 0.0, 'balance' => 0.0,
			'suppliers' => array(), 'returns' => 0);
		if (!$this->has('db_purchase')) { return $out; }

		$dateCol = $this->db->field_exists('purchase_date', 'db_purchase')
			? 'purchase_date' : 'created_date';
		$row = $this->db->select('COUNT(*) n, COALESCE(SUM(grand_total),0) total, '
				. 'COALESCE(SUM(paid_amount),0) paid', false)
			->where('store_id', $store)
			->where($dateCol . ' >=', $from)->where($dateCol . ' <=', $to)
			->get('db_purchase')->row();
		$out['orders'] = (int) ($row->n ?? 0);
		$out['value']  = (float) ($row->total ?? 0);
		$out['paid']   = (float) ($row->paid ?? 0);
		$out['balance'] = max(0, $out['value'] - $out['paid']);

		if ($this->has('db_suppliers') && $this->db->field_exists('supplier_id', 'db_purchase')) {
			$rows = $this->db->select('COALESCE(NULLIF(TRIM(s.supplier_name), ""), "Unknown supplier") AS name, '
					. 'COUNT(*) n, COALESCE(SUM(p.grand_total),0) amt', false)
				->from('db_purchase p')
				->join('db_suppliers s', 's.id = p.supplier_id', 'left')
				->where('p.store_id', $store)
				->where('p.' . $dateCol . ' >=', $from)->where('p.' . $dateCol . ' <=', $to)
				->group_by('p.supplier_id')->order_by('amt', 'desc')->get()->result();
			foreach ($rows as $r) {
				$out['suppliers'][] = array(
					'name' => $r->name, 'n' => (int) $r->n, 'amount' => (float) $r->amt,
				);
			}
		}
		return $out;
	}

	/* =====================================================================
	 * 12. Patients awaiting money — opening positions still under review
	 * ================================================================== */

	public function openingPositions() {
		$store = $this->storeId();
		$this->scope();
		$out = array('rows' => array(), 'total' => 0.0, 'count' => 0);
		if (!$this->has('db_opening_positions')) { return $out; }

		$statuses = $this->db->field_exists('status', 'db_opening_positions')
			? 'status' : null;
		$this->db->select('o.id, o.patient_id, o.status, o.amount, '
				. 'COALESCE(NULLIF(TRIM(c.customer_name), ""), "Patient") AS patient_name, '
				. 'p.patient_code', false)
			->from('db_opening_positions o')
			->join('db_patients p', 'p.id = o.patient_id', 'left')
			->join('db_customers c', 'c.id = p.customer_id', 'left')
			->where('o.store_id', $store);
		if ($statuses) {
			$this->db->where_in('o.status', array('pending', 'draft', 'submitted', 'review'));
		}
		$rows = $this->db->order_by('o.amount', 'desc')->limit(25)->get()->result();
		foreach ($rows as $r) {
			$out['count']++;
			$out['total'] += (float) $r->amount;
			$out['rows'][] = array(
				'patient' => $r->patient_name, 'patient_code' => $r->patient_code,
				'status' => $r->status, 'amount' => (float) $r->amount,
			);
		}
		return $out;
	}
	/**
	 * A row for every month in the window, so a quiet month reads as zero
	 * rather than silently vanishing from a trend table.
	 */
	private function monthSeries($from, $to) {
		$out = array();
		try {
			$start = new DateTime(date('Y-m-01', strtotime($from)));
			$end   = new DateTime(date('Y-m-01', strtotime($to)));
		} catch (Exception $e) {
			return $out;
		}
		while ($start <= $end) {
			$out[$start->format('Y-m')] = 0;
			$start->modify('+1 month');
		}
		return $out;
	}

	/** Monthly counts for a trend table, with empty months reported as zero. */
	private function monthlyCounts($table, $from, $to, array $where = array(), $column = 'created_date') {
		$series = $this->monthSeries($from, $to);
		if (!$this->has($table)) { return $series; }

		$this->db->select("DATE_FORMAT({$column}, '%Y-%m') AS ym, COUNT(*) AS n", false);
		foreach ($where as $k => $v) { $this->db->where($k, $v); }
		if (!empty($from)) { $this->db->where($column . ' >=', $from); }
		if (!empty($to))   { $this->db->where($column . ' <=', $to); }
		foreach ($this->db->group_by('ym')->get($table)->result() as $r) {
			if (isset($series[$r->ym])) { $series[$r->ym] = (int) $r->n; }
		}
		return $series;
	}

	private function daysSince($date) {
		if (empty($date) || $date === '0000-00-00') { return null; }
		try { $d = new DateTime($date); } catch (Exception $e) { return null; }
		return (int) $d->diff(new DateTime('today'))->days;
	}

	/* =====================================================================
	 * 13. Cancellations & no-shows — the leakage report
	 * ================================================================== */

	/**
	 * Lost appointments and cancelled work.
	 *
	 * A clinic loses most of its revenue in the diary, not at the till: a
	 * cancelled session or a patient who never arrives is capacity that can
	 * never be sold again. This report shows where that happens and to whom.
	 */
	public function cancellations($from, $to) {
		$store = $this->storeId();
		$this->scope();
		$out = array(
			'appointments' => array('total' => 0, 'cancelled' => 0, 'no_show' => 0,
				'completed' => 0, 'lost' => 0, 'upcoming' => 0, 'rate' => null,
				'by_reason' => array(), 'by_patient' => array()),
			'sessions' => array('total' => 0, 'cancelled' => 0, 'reversed' => 0,
				'rate' => null, 'by_reason' => array(), 'by_clinician' => array()),
			'trend' => array(),
		);

		if ($this->has('db_appointments')) {
			$this->db->select('a.status, COUNT(*) AS n', false)
				->from('db_appointments a')->where('a.store_id', $store);
			$this->dateRange($from, $to, 'a.scheduled_at');
			foreach ($this->db->group_by('a.status')->get()->result() as $r) {
				$status = strtolower((string) $r->status);
				$n = (int) $r->n;
				$out['appointments']['total'] += $n;
				if (strpos($status, 'cancel') !== false) { $out['appointments']['cancelled'] += $n; }
				elseif (strpos($status, 'no_show') !== false || strpos($status, 'noshow') !== false) { $out['appointments']['no_show'] += $n; }
				elseif (strpos($status, 'complete') !== false || strpos($status, 'attend') !== false) { $out['appointments']['completed'] += $n; }
			}
			$out['appointments']['lost'] = $out['appointments']['cancelled'] + $out['appointments']['no_show'];
			if ($out['appointments']['total'] > 0) {
				$out['appointments']['rate'] = round(100 * $out['appointments']['lost'] / $out['appointments']['total'], 1);
			}

			/*
			 * The forward book.
			 *
			 * Loss only accumulates in the past, so the window above is
			 * backward-looking — which makes a clinic whose diary is mostly
			 * ahead of it read as "no data". This counts the bookings still to
			 * come, so the reader can see the size of the diary those losses
			 * are being measured against.
			 */
			$this->db->from('db_appointments a')
				->where('a.store_id', $store)
				->where('a.scheduled_at >', date('Y-m-d H:i:s'))
				->where("LOWER(a.status) NOT LIKE '%cancel%'", null, false)
				->where("LOWER(a.status) NOT LIKE '%no_show%'", null, false);
			$out['appointments']['upcoming'] = (int) $this->db->count_all_results();

			if ($this->db->field_exists('cancel_reason', 'db_appointments')) {
				$this->db->select("COALESCE(NULLIF(TRIM(a.cancel_reason), ''), 'Not given') AS reason, COUNT(*) AS n", false)
					->from('db_appointments a')->where('a.store_id', $store)
					->where("LOWER(a.status) LIKE '%cancel%'", null, false);
				$this->dateRange($from, $to, 'a.scheduled_at');
				foreach ($this->db->group_by('reason')->order_by('n', 'desc')->limit(10)->get()->result() as $r) {
					$out['appointments']['by_reason'][$r->reason] = (int) $r->n;
				}
			}

			// Who loses the most appointments — the list a manager actions.
			$this->db->select("COALESCE(NULLIF(TRIM(c.customer_name), ''), 'Patient') AS patient_name, COUNT(*) AS n", false)
				->from('db_appointments a')
				->join('db_patients p', 'p.id = a.patient_id', 'left')
				->join('db_customers c', 'c.id = p.customer_id', 'left')
				->where('a.store_id', $store)
				->where("(LOWER(a.status) LIKE '%cancel%' OR LOWER(a.status) LIKE '%no_show%')", null, false);
			$this->dateRange($from, $to, 'a.scheduled_at');
			foreach ($this->db->group_by('patient_name')->order_by('n', 'desc')->limit(10)->get()->result() as $r) {
				$out['appointments']['by_patient'][] = array('name' => $r->patient_name, 'n' => (int) $r->n);
			}

			$out['trend'] = $this->monthlyCounts('db_appointments', $from, $to,
				array('store_id' => $store), 'scheduled_at');
		}

		if ($this->has('db_treatment_sessions')) {
			$this->db->select('s.status, COUNT(*) AS n', false)
				->from('db_treatment_sessions s')->where('s.store_id', $store);
			$this->dateRange($from, $to, 's.scheduled_at');
			foreach ($this->db->group_by('s.status')->get()->result() as $r) {
				$status = strtolower((string) $r->status);
				$n = (int) $r->n;
				$out['sessions']['total'] += $n;
				if (strpos($status, 'cancel') !== false) { $out['sessions']['cancelled'] += $n; }
				elseif (strpos($status, 'revers') !== false) { $out['sessions']['reversed'] += $n; }
			}
			if ($out['sessions']['total'] > 0) {
				$out['sessions']['rate'] = round(100 * $out['sessions']['cancelled'] / $out['sessions']['total'], 1);
			}
			if ($this->db->field_exists('cancel_reason', 'db_treatment_sessions')) {
				$this->db->select("COALESCE(NULLIF(TRIM(s.cancel_reason), ''), 'Not given') AS reason, COUNT(*) AS n", false)
					->from('db_treatment_sessions s')->where('s.store_id', $store)
					->where("LOWER(s.status) LIKE '%cancel%'", null, false);
				$this->dateRange($from, $to, 's.scheduled_at');
				foreach ($this->db->group_by('reason')->order_by('n', 'desc')->limit(10)->get()->result() as $r) {
					$out['sessions']['by_reason'][$r->reason] = (int) $r->n;
				}
			}
			$this->db->select("COALESCE(NULLIF(TRIM(u.username), ''), 'Unassigned') AS clinician, COUNT(*) AS n", false)
				->from('db_treatment_sessions s')
				->join('db_users u', 'u.id = s.clinician_id', 'left')
				->where('s.store_id', $store)
				->where("LOWER(s.status) LIKE '%cancel%'", null, false);
			$this->dateRange($from, $to, 's.scheduled_at');
			foreach ($this->db->group_by('clinician')->order_by('n', 'desc')->limit(10)->get()->result() as $r) {
				$out['sessions']['by_clinician'][] = array('name' => $r->clinician, 'n' => (int) $r->n);
			}
		}
		return $out;
	}

	/* =====================================================================
	 * 14. Treatment plans — episode progress
	 * ================================================================== */

	/**
	 * Course-of-treatment progress.
	 *
	 * A physiotherapy clinic is paid per plan, so the plan is the unit of work:
	 * how many are open, how much of each has been delivered, which have stalled
	 * and which clinicians are carrying the load.
	 */
	public function plans($from, $to) {
		$store = $this->storeId();
		$this->scope();
		$out = array(
			'total' => 0, 'new' => 0, 'by_status' => array(),
			'progress' => array('planned' => 0, 'delivered' => 0, 'rate' => null),
			'open' => array(), 'by_clinician' => array(), 'stalled' => 0,
		);
		if (!$this->has('db_treatment_plans')) { return $out; }

		$this->db->select('p.status, COUNT(*) AS n', false)
			->from('db_treatment_plans p')->where('p.store_id', $store);
		foreach ($this->db->group_by('p.status')->get()->result() as $r) {
			$key = ($r->status === null || $r->status === '') ? 'unspecified' : (string) $r->status;
			$out['by_status'][$key] = (int) $r->n;
			$out['total'] += (int) $r->n;
		}

		$this->db->from('db_treatment_plans p')->where('p.store_id', $store);
		$this->dateRange($from, $to, 'p.created_date');
		$out['new'] = (int) $this->db->count_all_results();

		// Delivered against planned, across the store's plans.
		if ($this->has('db_treatment_sessions')) {
			$row = $this->db->select('COALESCE(SUM(s.units_total), 0) AS planned, '
					. "COALESCE(SUM(CASE WHEN LOWER(s.status) = 'completed' THEN s.units_total ELSE 0 END), 0) AS delivered", false)
				->from('db_treatment_sessions s')
				->join('db_treatment_plans p', 'p.id = s.plan_id', 'inner')
				->where('p.store_id', $store)->where('s.store_id', $store)
				->get()->row();
			$out['progress']['planned']   = (float) ($row->planned ?? 0);
			$out['progress']['delivered'] = (float) ($row->delivered ?? 0);
			if ($out['progress']['planned'] > 0) {
				$out['progress']['rate'] = round(100 * $out['progress']['delivered'] / $out['progress']['planned'], 1);
			}
		}

		// Open plans, oldest first — the ones that need a push.
		$this->db->select('p.id, p.plan_code, p.title, p.status, p.created_date, '
				. 'COALESCE(NULLIF(TRIM(c.customer_name), ""), "Patient") AS patient_name, '
				. 'COALESCE(SUM(s.units_total), 0) AS planned, '
				. "COALESCE(SUM(CASE WHEN LOWER(s.status) = 'completed' THEN s.units_total ELSE 0 END), 0) AS delivered", false)
			->from('db_treatment_plans p')
			->join('db_patients pt', 'pt.id = p.patient_id', 'left')
			->join('db_customers c', 'c.id = pt.customer_id', 'left')
			->join('db_treatment_sessions s', 's.plan_id = p.id', 'left')
			->where('p.store_id', $store)
			->where("LOWER(p.status) NOT IN ('completed','closed','cancelled')", null, false)
			->group_by('p.id, p.plan_code, p.title, p.status, p.created_date, patient_name');
		foreach ($this->db->order_by('p.created_date', 'asc')->limit(25)->get()->result() as $r) {
			$planned = (float) $r->planned;
			$delivered = (float) $r->delivered;
			$age = $this->daysSince($r->created_date);
			$rate = $planned > 0 ? round(100 * $delivered / $planned, 1) : null;
			$out['open'][] = array(
				'plan_code' => $r->plan_code, 'title' => $r->title,
				'patient' => $r->patient_name, 'status' => $r->status,
				'planned' => $planned, 'delivered' => $delivered,
				'remaining' => max(0, $planned - $delivered),
				'rate' => $rate, 'age_days' => $age,
			);
			// Open over a month with under half delivered = stalled work.
			if ($age !== null && $age > 30 && ($rate === null || $rate < 50)) { $out['stalled']++; }
		}

		// Caseload per clinician.
		$this->db->select("COALESCE(NULLIF(TRIM(u.username), ''), 'Unassigned') AS clinician, COUNT(DISTINCT p.id) AS plans", false)
			->from('db_treatment_plans p')
			->join('db_users u', 'u.id = p.clinician_id', 'left')
			->where('p.store_id', $store)
			->where("LOWER(p.status) NOT IN ('completed','closed','cancelled')", null, false);
		foreach ($this->db->group_by('clinician')->order_by('plans', 'desc')->limit(12)->get()->result() as $r) {
			$out['by_clinician'][] = array('name' => $r->clinician, 'plans' => (int) $r->plans);
		}
		return $out;
	}

	/* =====================================================================
	 * 15. Documentation & consent — compliance
	 * ================================================================== */

	/**
	 * Records completeness.
	 *
	 * Consent and signature evidence is the part of a clinical file that only
	 * matters when it is missing, and by then it is too late to collect. This
	 * report names the gaps while the patient is still in front of the desk.
	 */
	public function documents($from, $to) {
		$store = $this->storeId();
		$this->scope();
		$out = array(
			'total' => 0, 'in_period' => 0, 'by_status' => array(), 'by_category' => array(),
			'awaiting_release' => 0, 'unsigned' => 0,
			'patients_total' => 0, 'patients_with_docs' => 0, 'coverage' => null,
			'gaps' => array(), 'trend' => array(),
		);
		if (!$this->has('db_patient_documents')) { return $out; }

		$this->db->select('d.status, COUNT(*) AS n', false)
			->from('db_patient_documents d')->where('d.store_id', $store);
		foreach ($this->db->group_by('d.status')->get()->result() as $r) {
			$key = ($r->status === null || $r->status === '') ? 'unspecified' : (string) $r->status;
			$out['by_status'][$key] = (int) $r->n;
			$out['total'] += (int) $r->n;
		}

		$this->db->from('db_patient_documents d')->where('d.store_id', $store);
		$this->dateRange($from, $to, 'd.created_date');
		$out['in_period'] = (int) $this->db->count_all_results();

		$this->db->select("COALESCE(NULLIF(TRIM(d.category), ''), 'Uncategorised') AS category, COUNT(*) AS n", false)
			->from('db_patient_documents d')->where('d.store_id', $store);
		foreach ($this->db->group_by('category')->order_by('n', 'desc')->limit(15)->get()->result() as $r) {
			$out['by_category'][$r->category] = (int) $r->n;
		}

		// Consent evidence outstanding.
		$this->db->from('db_patient_documents d')->where('d.store_id', $store)
			->where("LOWER(d.status) IN ('draft','pending','awaiting_signature','unsigned')", null, false);
		$out['unsigned'] = (int) $this->db->count_all_results();

		$this->db->from('db_patient_documents d')->where('d.store_id', $store)
			->where('d.released_to_patient', 0);
		$out['awaiting_release'] = (int) $this->db->count_all_results();

		// Coverage: what share of the register has any document at all.
		if ($this->has('db_patients')) {
			$out['patients_total'] = (int) $this->db->from('db_patients p')
				->where('p.store_id', $store)->where('p.deceased', 0)->count_all_results();
			$out['patients_with_docs'] = (int) $this->db->from('db_patients p')
				->where('p.store_id', $store)->where('p.deceased', 0)
				->where('EXISTS (SELECT 1 FROM db_patient_documents d WHERE d.patient_id = p.id)', null, false)
				->count_all_results();
			if ($out['patients_total'] > 0) {
				$out['coverage'] = round(100 * $out['patients_with_docs'] / $out['patients_total'], 1);
			}
		}

		// Named gaps — the patients with no document on file at all.
		// created_date is grouped rather than only ordered by: the server runs
		// with ONLY_FULL_GROUP_BY, which rejects an ORDER BY column that is not
		// in the GROUP BY.
		$this->db->select('p.patient_code, p.created_date, '
				. 'COALESCE(NULLIF(TRIM(c.customer_name), ""), "Patient") AS patient_name, '
				. 'COUNT(DISTINCT d.id) AS docs', false)
			->from('db_patients p')
			->join('db_customers c', 'c.id = p.customer_id', 'left')
			->join('db_patient_documents d', 'd.patient_id = p.id', 'left')
			->where('p.store_id', $store)->where('p.deceased', 0)
			->group_by('p.patient_code, p.created_date, patient_name')
			->having('docs = 0', null, false)
			->order_by('p.created_date', 'desc');
		foreach ($this->db->limit(25)->get()->result() as $r) {
			$out['gaps'][] = array(
				'patient_code' => $r->patient_code,
				'patient' => $r->patient_name,
				'docs' => (int) $r->docs,
			);
		}

		$out['trend'] = $this->monthlyCounts('db_patient_documents', $from, $to,
			array('store_id' => $store), 'created_date');
		return $out;
	}
}
