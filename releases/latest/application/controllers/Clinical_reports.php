<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Clinical_reports — the reporting surface for the physiotherapy &
 * rehabilitation business type.
 *
 * Design notes
 * ------------
 * - One screen ("Clinical reports") with a report selector, rather than a dozen
 *   rail entries: a clinic manager wants the answer to a question, not a maze.
 * - Each report is gated on the clinical grant that its underlying screen
 *   requires, so a report can never disclose more than the viewer's day-to-day
 *   screen already would. Money reports additionally require
 *   `clinical_reports_view` (the Clinical Reports grant).
 * - Export re-runs the same model call and streams CSV, so the export always
 *   equals the screen (no second, drifting query).
 * - Read-only. Nothing here writes or posts.
 *
 *   /clinical_reports                → overview index
 *   /clinical_reports/<key>          → a single report
 *   /clinical_reports/<key>?export=1 → the same report as CSV
 */
class Clinical_reports extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load_global();
		$this->load->helper('clinical_report');
		if (!function_exists('physio_can')) { $this->load->helper('physio'); }

		// This controller only exists for the physiotherapy business type, and
		// only for staff who are allowed to read clinical reports. The gate is
		// shared with the rail (cr_can_see_reports / cr_can_see) so the link and
		// the route can never disagree.
		if (!physio_enabled()) { show_404(); return; }
		if (!cr_can_see_reports()) {
			$this->show_access_denied_page();
			return;
		}
		$this->load->model('reports_clinical_model', 'creports');
	}

	private function reports(){
		return cr_reports();
	}

	/**
	 * Reports this viewer may open.
	 *
	 * Delegates to the shared registry gate (see cr_can_see in the
	 * clinical_report helper) so a rail entry built from the same registry
	 * can never lead to a denial.
	 */
	private function allowed(){
		$out = array();
		foreach ($this->reports() as $key => $r) {
			if (cr_can_see($r)) { $out[$key] = $r; }
		}
		return $out;
	}

	/** Any-of a list, against the retail permission set. */
	private function permissions_any(array $perms){
		foreach ($perms as $p) { if ($this->permissions($p)) { return true; } }
		return false;
	}

	private function range(){
		$from = $this->input->get('from', true);
		$to   = $this->input->get('to', true);
		if (empty($from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
			$from = date('Y-m-01', strtotime('-2 months'));
		}
		if (empty($to) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
			$to = date('Y-m-d');
		}
		if (strtotime($from) > strtotime($to)) { $tmp = $from; $from = $to; $to = $tmp; }
		return array($from, $to);
	}

	public function index(){
		list($from, $to) = $this->range();
		$data = $this->data;
		$data['page_title'] = 'Clinical reports';
		$data['reports'] = $this->allowed();
		$data['from'] = $from;
		$data['to'] = $to;
		$data['header_actions'] = '';
		$data['content'] = $this->load->view('reports/clinical/index', $data, true);
		$this->load->view('mp_layout', $data);
	}

	/** A single report — also the CSV export endpoint. */
	public function view($key = ''){
		$key = trim((string)$key);
		$all = $this->allowed();
		if (!isset($all[$key])) {
			// Either the report does not exist or this viewer lacks its grant.
			// Both are "not available to you" as far as the user is concerned.
			show_404();
			return;
		}
		list($from, $to) = $this->range();
		$meta = $all[$key];
		$report = $this->build($key, $from, $to);

		if ($this->input->get('export')) {
			$this->exportCsv($key, $meta, $report, $from, $to);
			return;
		}

		$data = $this->data;
		$data['page_title'] = $meta['title'];
		$data['meta'] = $meta;
		$data['key'] = $key;
		$data['report'] = $report;
		$data['from'] = $from;
		$data['to'] = $to;
		$data['reports'] = $all;
		$data['content'] = $this->load->view('reports/clinical/' . $key, $data, true);
		$this->load->view('mp_layout', $data);
	}

	/** Run one report's model call. */
	private function build($key, $from, $to){
		switch ($key) {
			case 'register':      return $this->creports->register($from, $to);
			case 'appointments':  return $this->creports->appointments($from, $to);
			case 'sessions':      return $this->creports->sessions($from, $to);
			case 'activity':      return $this->creports->activity($from, $to);
			case 'investigations':return $this->creports->investigations($from, $to);
			case 'ward':          return $this->creports->ward($from, $to);
			case 'tasks':         return $this->creports->taskLoad($from, $to);
			case 'outstanding':   return $this->creports->outstanding($from, $to);
			case 'cancellations': return $this->creports->cancellations($from, $to);
			case 'plans':         return $this->creports->plans($from, $to);
			case 'documents':     return $this->creports->documents($from, $to);
			case 'revenue':       return $this->creports->revenue($from, $to);
			case 'aids':          return $this->creports->aids($from, $to);
			case 'procurement':   return $this->creports->procurement($from, $to);
			case 'opening':       return $this->creports->openingPositions();
		}
		return array();
	}

	/**
	 * Flatten a report into rows for CSV. Each report declares a column list so
	 * the file is readable in Excel without a second sheet of keys.
	 */
	private function exportCsv($key, array $meta, array $report, $from, $to){
		$rows = $this->csvRows($key, $report);
		$name = preg_replace('/[^a-z0-9]+/i', '-', strtolower($meta['title']));
		$file = 'clinical-' . $name . '-' . $from . '-to-' . $to . '.csv';
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $file . '"');
		$out = fopen('php://output', 'w');
		// BOM so Excel reads UTF-8 (currency symbol, accented names) correctly.
		fwrite($out, "\xEF\xBB\xBF");
		fputcsv($out, array($meta['title']));
		fputcsv($out, array('Period', $from . ' to ' . $to, 'Generated', date('Y-m-d H:i')));
		fputcsv($out, array());
		foreach ($rows as $r) { fputcsv($out, $r); }
		fclose($out);
		exit;
	}

	/** Uniform [header, ...rows] shape per report, for CSV only. */
	private function csvRows($key, array $r){
		$rows = array();
		switch ($key) {
			case 'register':
				$rows[] = array('Measure', 'Value');
				$rows[] = array('Patients on register', $r['total']);
				$rows[] = array('Active', $r['active']);
				$rows[] = array('New in period', $r['new']);
				$rows[] = array('Deceased', $r['deceased']);
				$rows[] = array();
				$rows[] = array('Gender', 'Patients');
				foreach ($r['gender'] as $k => $v) { $rows[] = array($k, $v); }
				$rows[] = array();
				$rows[] = array('Age band', 'Patients');
				foreach ($r['age_bands'] as $k => $v) { $rows[] = array($k, $v); }
				$rows[] = array();
				$rows[] = array('Month', 'New registrations');
				foreach ($r['monthly'] as $k => $v) { $rows[] = array($k, $v); }
				break;

			case 'appointments':
				$rows[] = array('Status', 'Appointments');
				foreach ($r['by_status'] as $k => $v) { $rows[] = array($k, $v); }
				$rows[] = array();
				$rows[] = array('Clinician', 'Appointments');
				foreach ($r['by_staff'] as $s) { $rows[] = array($s['name'], $s['n']); }
				$rows[] = array();
				$rows[] = array('Arrived', $r['attendance']['arrived']);
				$rows[] = array('No-show', $r['attendance']['no_show']);
				$rows[] = array('Attendance rate %', $r['attendance']['rate'] === null ? '' : $r['attendance']['rate']);
				break;

			case 'sessions':
				$rows[] = array('Status', 'Sessions');
				foreach ($r['by_status'] as $k => $v) { $rows[] = array($k, $v); }
				$rows[] = array();
				$rows[] = array('Total sessions', $r['total']);
				$rows[] = array('Units delivered', $r['units']);
				$rows[] = array('Fees posted', $r['fee_posted']);
				$rows[] = array('Completed but NOT posted', $r['not_posted']);
				$rows[] = array();
				$rows[] = array('Clinician', 'Sessions', 'Units');
				foreach ($r['by_clinician'] as $s) { $rows[] = array($s['name'], $s['n'], $s['units']); }
				break;

			case 'outstanding':
				$rows[] = array('Age band', 'Balance');
				foreach ($r['buckets'] as $k => $v) { $rows[] = array($k, $v); }
				$rows[] = array();
				$rows[] = array('Invoice', 'Patient', 'Patient code', 'Date', 'Age (days)', 'Balance');
				foreach ($r['top'] as $t) {
					$rows[] = array($t['code'], $t['patient'], $t['patient_code'], $t['date'], $t['age'], $t['balance']);
				}
				break;

			case 'revenue':
				$rows[] = array('Measure', 'Amount');
				$rows[] = array('Charged to patient accounts', $r['charged']);
				$rows[] = array('Collected on those accounts', $r['collected']);
				$rows[] = array('Funding taken in period', $r['funding']);
				$rows[] = array('Funds currently held', $r['held']);
				$rows[] = array('Active reservations', $r['held_reservations']);
				$rows[] = array();
				$rows[] = array('Service', 'Lines', 'Amount');
				foreach ($r['by_service'] as $s) { $rows[] = array($s['name'], $s['n'], $s['amount']); }
				break;

			case 'aids':
				$rows[] = array('Item', 'Code', 'Quantity', 'Lines', 'Value');
				foreach ($r['items'] as $i) {
					$rows[] = array($i['name'], $i['code'], $i['qty'], $i['lines'], $i['amount']);
				}
				break;

			case 'procurement':
				$rows[] = array('Measure', 'Value');
				$rows[] = array('Purchase orders', $r['orders']);
				$rows[] = array('Value', $r['value']);
				$rows[] = array('Paid', $r['paid']);
				$rows[] = array('Outstanding', $r['balance']);
				$rows[] = array();
				$rows[] = array('Supplier', 'Orders', 'Value');
				foreach ($r['suppliers'] as $s) { $rows[] = array($s['name'], $s['n'], $s['amount']); }
				break;

			case 'ward':
				$rows[] = array('Measure', 'Value');
				$rows[] = array('Beds', $r['beds']['total']);
				$rows[] = array('Occupied', $r['beds']['occupied']);
				$rows[] = array('Free', $r['beds']['free']);
				$rows[] = array('Occupancy %', $r['beds']['rate'] === null ? '' : $r['beds']['rate']);
				$rows[] = array('Admissions in period', $r['admissions']['new']);
				$rows[] = array('Discharged', $r['admissions']['discharged']);
				$rows[] = array('Deceased', $r['admissions']['deceased']);
				$rows[] = array('Average length of stay (days)', $r['los']['avg'] === null ? '' : $r['los']['avg']);
				$rows[] = array();
				$rows[] = array('Ward', 'Beds', 'Occupied', 'Free', 'Occupancy %');
				foreach ($r['wards'] as $w) {
					$rows[] = array($w['name'], $w['total'], $w['occupied'], $w['free'], $w['rate'] === null ? '' : $w['rate']);
				}
				break;

			case 'investigations':
				$rows[] = array('Status', 'Requests');
				foreach ($r['by_status'] as $k => $v) { $rows[] = array($k, $v); }
				$rows[] = array();
				$rows[] = array('Category', 'Requests');
				foreach ($r['by_category'] as $k => $v) { $rows[] = array($k, $v); }
				$rows[] = array();
				$rows[] = array('Still open', $r['pending']);
				$rows[] = array('Open over 7 days', $r['overdue']);
				$rows[] = array('Average turnaround (days)', $r['avg_turnaround'] ? $r['avg_turnaround']['days'] : '');
				break;

			case 'tasks':
				$rows[] = array('Queue', 'Open', 'Done', 'Overdue');
				$rows[] = array('Nursing', $r['nursing']['open'], $r['nursing']['done'], $r['nursing']['overdue']);
				$rows[] = array('Porter', $r['porter']['open'], $r['porter']['done'], $r['porter']['overdue']);
				break;

			case 'opening':
				$rows[] = array('Patient', 'Patient code', 'Status', 'Amount');
				foreach ($r['rows'] as $o) {
					$rows[] = array($o['patient'], $o['patient_code'], $o['status'], $o['amount']);
				}
				break;
			case 'cancellations':
				$a = $r['appointments']; $s = $r['sessions'];
				$rows[] = array('Appointments in period', $a['total']);
				$rows[] = array('Still to come (booked ahead of today)', $a['upcoming']);
				$rows[] = array('Cancelled', $a['cancelled']);
				$rows[] = array('No-show', $a['no_show']);
				$rows[] = array('Attended / completed', $a['completed']);
				$rows[] = array('Lost', $a['lost']);
				$rows[] = array('Loss rate %', $a['rate'] === null ? '' : $a['rate']);
				$rows[] = array();
				$rows[] = array('Cancellation reason', 'Appointments');
				foreach ($a['by_reason'] as $k => $v) { $rows[] = array($k, $v); }
				$rows[] = array();
				$rows[] = array('Patient', 'Lost appointments');
				foreach ($a['by_patient'] as $x) { $rows[] = array($x['name'], $x['n']); }
				$rows[] = array();
				$rows[] = array('Sessions in period', $s['total']);
				$rows[] = array('Cancelled sessions', $s['cancelled']);
				$rows[] = array('Reversed sessions', $s['reversed']);
				$rows[] = array('Cancellation rate %', $s['rate'] === null ? '' : $s['rate']);
				$rows[] = array();
				$rows[] = array('Clinician', 'Cancelled sessions');
				foreach ($s['by_clinician'] as $x) { $rows[] = array($x['name'], $x['n']); }
				$rows[] = array();
				$rows[] = array('Month', 'Appointments');
				foreach ($r['trend'] as $ym => $n) { $rows[] = array($ym, $n); }
				break;

			case 'plans':
				$p = $r['progress'];
				$rows[] = array('Plans on file', $r['total']);
				$rows[] = array('Created in period', $r['new']);
				$rows[] = array('Units planned', $p['planned']);
				$rows[] = array('Units delivered', $p['delivered']);
				$rows[] = array('Delivered %', $p['rate'] === null ? '' : $p['rate']);
				$rows[] = array('Stalled plans', $r['stalled']);
				$rows[] = array();
				$rows[] = array('Status', 'Plans');
				foreach ($r['by_status'] as $k => $v) { $rows[] = array($k, $v); }
				$rows[] = array();
				$rows[] = array('Plan', 'Patient', 'Status', 'Planned', 'Delivered', 'Remaining', 'Progress %', 'Age (days)');
				foreach ($r['open'] as $x) {
					$rows[] = array($x['plan_code'] . ' ' . $x['title'], $x['patient'], $x['status'],
						$x['planned'], $x['delivered'], $x['remaining'],
						$x['rate'] === null ? '' : $x['rate'], $x['age_days']);
				}
				$rows[] = array();
				$rows[] = array('Clinician', 'Open plans');
				foreach ($r['by_clinician'] as $x) { $rows[] = array($x['name'], $x['plans']); }
				break;

			case 'documents':
				$rows[] = array('Documents on file', $r['total']);
				$rows[] = array('Created in period', $r['in_period']);
				$rows[] = array('Awaiting signature', $r['unsigned']);
				$rows[] = array('Not yet released to patient', $r['awaiting_release']);
				$rows[] = array('Patients on register', $r['patients_total']);
				$rows[] = array('Patients with a document', $r['patients_with_docs']);
				$rows[] = array('Coverage %', $r['coverage'] === null ? '' : $r['coverage']);
				$rows[] = array();
				$rows[] = array('Status', 'Documents');
				foreach ($r['by_status'] as $k => $v) { $rows[] = array($k, $v); }
				$rows[] = array();
				$rows[] = array('Category', 'Documents');
				foreach ($r['by_category'] as $k => $v) { $rows[] = array($k, $v); }
				$rows[] = array();
				$rows[] = array('Patient', 'Code', 'Documents');
				foreach ($r['gaps'] as $x) { $rows[] = array($x['patient'], $x['patient_code'], $x['docs']); }
				break;
			case 'activity':
			default:
				$rows[] = array('Measure', 'Value');
				foreach ($this->summaryPairs($key, $r) as $k => $v) { $rows[] = array($k, $v); }
				break;
		}
		return $rows;
	}

	/** Flat label=>value pairs for the simple reports, used by view + CSV. */
	private function summaryPairs($key, array $r){
		switch ($key) {
			case 'activity':
				return array(
					'Encounters in period' => $r['encounters'],
					'Open care queue'      => $r['queue_open'],
					'Assessments in period'=> $r['assessments']['total'],
					'Assessments finalised'=> $r['assessments']['finalized'],
					'Assessments in draft' => $r['assessments']['draft'],
					'Active treatment plans' => $r['plans']['active'],
					'New plans in period'  => $r['plans']['new'],
				);
			case 'opening':
				return array(
					'Awaiting review' => $r['count'],
					'Total amount'    => $r['total'],
				);
		}
		return array();
	}
}
