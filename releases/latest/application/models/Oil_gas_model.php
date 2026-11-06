<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Oil & Gas workspace: the multi-location view.
 *
 * A company here can be one filling station, a depot plus three stations, or an
 * LPG roundabout with a service workshop. Everything in this model is composed
 * from records that already exist (locations, stock, transfers), so the
 * workspace is useful on day one and grows as each capability ships.
 */
class Oil_gas_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	/**
	 * Locations this user may see. null = the whole company.
	 */
	public function scoped_location_ids($store_id){
		if(!function_exists('mp_location_assigned_ids')) return null;
		return mp_location_assigned_ids((int)$store_id);
	}

	/** Location rows for the company, already narrowed to what the user may see. */
	public function locations($store_id){
		$ids = $this->scoped_location_ids($store_id);
		if(is_array($ids) && empty($ids)) return array();

		$where_ids = '';
		if(is_array($ids)){
			$clean = array_values(array_filter(array_map('intval', $ids)));
			if(empty($clean)) return array();
			$where_ids = ' AND id IN ('.implode(',', $clean).')';
		}
		return $this->db->query(
			'SELECT id, warehouse_name, warehouse_type, branch_type, is_distribution_center, address, city, status, feature_flags_json'
			.' FROM db_warehouse WHERE store_id = '.(int)$store_id.' AND status = 1'.$where_ids
			.' ORDER BY is_distribution_center DESC, id ASC'
		)->result();
	}

	/**
	 * Per-location figures in three grouped queries rather than one per site.
	 */
	public function location_rollup($store_id, $location_ids){
		$rollup = array();
		foreach((array)$location_ids as $id){
			$rollup[(int)$id] = array(
				'stock_lines'=>0, 'stock_value'=>0,
				'arrivals_waiting'=>0, 'arrivals_held'=>0,
				'dispatches_open'=>0, 'dispatches_month'=>0,
			);
		}
		if(empty($location_ids)) return $rollup;

		$ids = implode(',', array_map('intval', (array)$location_ids));
		$store = (int)$store_id;
		$month_start = date('Y-m-01');

		$stock = $this->db->query(
			'SELECT wi.warehouse_id, COUNT(*) AS line_count,'
			.' COALESCE(SUM(wi.available_qty * i.purchase_price),0) AS line_value'
			.' FROM db_warehouseitems wi'
			.' INNER JOIN db_items i ON i.id = wi.item_id'
			." WHERE wi.store_id = $store AND wi.warehouse_id IN ($ids) AND wi.available_qty > 0"
			.' GROUP BY wi.warehouse_id'
		)->result();
		foreach($stock as $row){
			$key = (int)$row->warehouse_id;
			if(isset($rollup[$key])){
				$rollup[$key]['stock_lines'] = (int)$row->line_count;
				$rollup[$key]['stock_value'] = (float)$row->line_value;
			}
		}

		$arrivals = $this->db->query(
			"SELECT warehouse_to AS location_id, receipt_status, COUNT(*) AS total FROM db_stocktransfer"
			." WHERE store_id = $store AND receipt_status IN ('In Transit','Awaiting Approval') AND warehouse_to IN ($ids)"
			.' GROUP BY warehouse_to, receipt_status'
		)->result();
		foreach($arrivals as $row){
			$key = (int)$row->location_id;
			if(!isset($rollup[$key])) continue;
			if($row->receipt_status === 'In Transit') $rollup[$key]['arrivals_waiting'] = (int)$row->total;
			else $rollup[$key]['arrivals_held'] = (int)$row->total;
		}

		$dispatches = $this->db->query(
			"SELECT warehouse_from AS location_id,"
			." COUNT(*) AS total_month,"
			." SUM(CASE WHEN receipt_status = 'In Transit' THEN 1 ELSE 0 END) AS open_loads"
			." FROM db_stocktransfer"
			." WHERE store_id = $store AND warehouse_from IN ($ids) AND transfer_date >= '$month_start'"
			.' GROUP BY warehouse_from'
		)->result();
		foreach($dispatches as $row){
			$key = (int)$row->location_id;
			if(!isset($rollup[$key])) continue;
			$rollup[$key]['dispatches_month'] = (int)$row->total_month;
			$rollup[$key]['dispatches_open'] = (int)$row->open_loads;
		}

		return $rollup;
	}

	/** Company totals for the head-office strip. */
	public function company_summary($store_id){
		$store = (int)$store_id;
		$summary = array(
			'locations'=>0, 'stock_value'=>0, 'arrivals_waiting'=>0, 'arrivals_held'=>0,
			'tolerance'=>function_exists('mp_transfer_loss_tolerance_percent') ? mp_transfer_loss_tolerance_percent($store) : 0,
		);

		$ids = $this->scoped_location_ids($store);
		$locations = $this->locations($store);
		$summary['locations'] = count($locations);
		if(empty($locations)) return $summary;

		$rollup = $this->location_rollup($store, array_map(function($l){ return (int)$l->id; }, $locations));
		foreach($rollup as $figures){
			$summary['stock_value']      += $figures['stock_value'];
			$summary['arrivals_waiting'] += $figures['arrivals_waiting'];
			$summary['arrivals_held']    += $figures['arrivals_held'];
		}
		return $summary;
	}

	/** The loads still on the road, newest first, capped for a readable screen. */
	public function open_loads($store_id, $limit = 8){
		if(!$this->db->field_exists('receipt_status', 'db_stocktransfer')) return array();
		$ids = $this->scoped_location_ids($store_id);
		$scope = '';
		if(is_array($ids)){
			$clean = array_values(array_filter(array_map('intval', $ids)));
			if(empty($clean)) return array();
			$list = implode(',', $clean);
			$scope = " AND (warehouse_to IN ($list) OR warehouse_from IN ($list))";
		}
		$limit = max(1, min(25, (int)$limit));
		return $this->db->query(
			"SELECT id, transfer_date, warehouse_from, warehouse_to, receipt_status, note, variance_reason"
			." FROM db_stocktransfer WHERE store_id = ".(int)$store_id
			." AND receipt_status IN ('In Transit','Awaiting Approval')".$scope
			.' ORDER BY CASE receipt_status WHEN \'Awaiting Approval\' THEN 0 ELSE 1 END, id DESC LIMIT '.$limit
		)->result();
	}

	/**
	 * Save what a single location switches off for itself. A location can never
	 * grant itself a capability the company has not turned on, so anything not
	 * in the company's feature set is simply not stored.
	 */
	public function save_location_flags($store_id, $warehouse_id, $posted_flags){
		$warehouse_id = (int)$warehouse_id;
		$store = (int)$store_id;
		$exists = $this->db->query('SELECT id FROM db_warehouse WHERE id = '.$warehouse_id.' AND store_id = '.$store)->num_rows();
		if(!$exists) return 'That location does not belong to this company.';
		if(!$this->db->field_exists('feature_flags_json', 'db_warehouse')) return 'Location settings are not available on this install yet.';

		$editable = function_exists('mp_get_location_scoped_flags') ? mp_get_location_scoped_flags() : array();

		$flags = array();
		foreach($editable as $key => $label){
			// Only a capability the company actually runs can be narrowed here.
			if(!function_exists('mp_feature_enabled') || !mp_feature_enabled($key)) continue;
			// Presence is not permission: a caller that sends the key with an off
			// value means switched off, and saving that as on would hand a site a
			// capability head office deliberately withdrew.
			$flags[$key] = isset($posted_flags[$key]) && filter_var($posted_flags[$key], FILTER_VALIDATE_BOOLEAN);
		}
		$this->db->query('UPDATE db_warehouse SET feature_flags_json = '.
			$this->db->escape(json_encode($flags)).' WHERE id = '.$warehouse_id.' AND store_id = '.$store);
		return 'success';
	}

	/* ------------------------------------------------------------ group report */

	/**
	 * One row per location, one total for the company.
	 *
	 * The company line is the PHP sum of the site rows printed above it rather
	 * than a second query, so head office and the sites can never disagree. Work
	 * that has no site chosen yet is given its own row instead of quietly
	 * disappearing from the total. Sections only appear when the company actually
	 * runs that operation, and a staff member only sees the sites they are
	 * assigned to.
	 */
	public function group_report($store_id, $from = null, $to = null){
		$store = (int)$store_id;
		$from = $this->_report_date($from, date('Y-m-01'));
		$to   = $this->_report_date($to, date('Y-m-d'));
		if($from > $to){ $swap = $from; $from = $to; $to = $swap; }

		$locations = $this->locations($store);
		$ids = array(0);
		$rows = array(0 => $this->_blank_site('Not tied to a location'));
		foreach($locations as $location){
			$id = (int)$location->id;
			$ids[] = $id;
			$rows[$id] = $this->_blank_site($location->warehouse_name !== '' ? $location->warehouse_name : 'Unnamed location');
			$rows[$id]['town'] = $location->city;
		}
		$list = implode(',', array_map('intval', $ids));
		$range = " BETWEEN '$from' AND '$to'";

		$sections = array('trading'=>'Trading', 'stock'=>'Stock', 'movements'=>'Movements between sites');
		$forecourt = $this->_runs('pump_metering') || $this->_runs('fuel_shift_reconciliation');
		$lpg = $this->_runs('lpg_refills') || $this->_runs('cylinder_tracking');
		if($forecourt) $sections['forecourt'] = 'Forecourt';
		if($lpg) $sections['lpg'] = 'Gas cylinders';
		if($this->_runs('cylinder_deposits')) $sections['deposits'] = 'Deposits held';
		$jobs_run = function_exists('mp_oil_gas_jobs_enabled') ? mp_oil_gas_jobs_enabled() : $this->_runs('service_jobs');
		if($jobs_run) $sections['jobs'] = 'Service work';

		// Trading: a cancelled or opening balance-invoice is not a day's work.
		$sales = $this->db->query(
			"SELECT warehouse_id AS id, COUNT(*) AS sales_count, COALESCE(SUM(grand_total),0) AS sales_value"
			." FROM db_sales WHERE store_id = $store AND warehouse_id IN ($list)"
			." AND sales_date$range AND (sales_status IS NULL OR sales_status NOT IN ('Cancelled','Opening'))"
			.' GROUP BY warehouse_id'
		)->result();
		foreach($sales as $row){ if(isset($rows[(int)$row->id])){
			$rows[(int)$row->id]['sales_count'] = (int)$row->sales_count;
			$rows[(int)$row->id]['sales_value'] = (float)$row->sales_value;
		}}

		// Stock is what the shelf holds today, not what it held during the range.
		$stock = $this->db->query(
			'SELECT wi.warehouse_id AS id, COUNT(*) AS stock_lines,'
			.' COALESCE(SUM(wi.available_qty * i.purchase_price),0) AS stock_value'
			." FROM db_warehouseitems wi INNER JOIN db_items i ON i.id = wi.item_id"
			." WHERE wi.store_id = $store AND wi.warehouse_id IN ($list) AND wi.available_qty > 0"
			.' GROUP BY wi.warehouse_id'
		)->result();
		foreach($stock as $row){ if(isset($rows[(int)$row->id])){
			$rows[(int)$row->id]['stock_lines'] = (int)$row->stock_lines;
			$rows[(int)$row->id]['stock_value'] = (float)$row->stock_value;
		}}

		// Loads out and in, plus whatever is still on the road.
		$sent = $this->db->query(
			"SELECT warehouse_from AS id, COUNT(*) AS loads_sent,"
			." SUM(CASE WHEN receipt_status = 'In Transit' THEN 1 ELSE 0 END) AS loads_open"
			." FROM db_stocktransfer WHERE store_id = $store AND warehouse_from IN ($list)"
			." AND transfer_date$range GROUP BY warehouse_from"
		)->result();
		foreach($sent as $row){ if(isset($rows[(int)$row->id])){
			$rows[(int)$row->id]['loads_sent'] = (int)$row->loads_sent;
			$rows[(int)$row->id]['loads_open'] += (int)$row->loads_open;
		}}
		$received = $this->db->query(
			'SELECT warehouse_to AS id, COUNT(*) AS loads_received'
			." FROM db_stocktransfer WHERE store_id = $store AND warehouse_to IN ($list)"
			." AND transfer_date$range GROUP BY warehouse_to"
		)->result();
		foreach($received as $row){ if(isset($rows[(int)$row->id])){
			$rows[(int)$row->id]['loads_received'] = (int)$row->loads_received;
		}}

		if($forecourt){
			// A duty only counts once it has been closed and signed.
			$shifts = $this->db->query(
				"SELECT warehouse_id AS id, COALESCE(SUM(litres_metered),0) AS litres_metered,"
				." COALESCE(SUM(ABS(variance_litres)),0) AS variance_litres,"
				." SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) AS duties_awaiting"
				." FROM db_fuel_shift WHERE store_id = $store AND warehouse_id IN ($list)"
				." AND status = 'closed' AND DATE(closed_at)$range GROUP BY warehouse_id"
			)->result();
			foreach($shifts as $row){ if(isset($rows[(int)$row->id])){
				$rows[(int)$row->id]['litres_metered'] = (float)$row->litres_metered;
				$rows[(int)$row->id]['variance_litres'] = (float)$row->variance_litres;
				$rows[(int)$row->id]['duties_awaiting'] = (int)$row->duties_awaiting;
			}}
		}

		if($lpg){
			$fills = $this->db->query(
				"SELECT warehouse_id AS id, COUNT(*) AS fills, COALESCE(SUM(net_kg),0) AS kg_filled,"
				." SUM(CASE WHEN linked_sale_id IS NULL OR linked_sale_id = 0 THEN 1 ELSE 0 END) AS fills_unmatched"
				." FROM db_fuel_lpg_refill WHERE store_id = $store AND warehouse_id IN ($list)"
				." AND refill_date$range GROUP BY warehouse_id"
			)->result();
			foreach($fills as $row){ if(isset($rows[(int)$row->id])){
				$rows[(int)$row->id]['fills'] = (int)$row->fills;
				$rows[(int)$row->id]['kg_filled'] = (float)$row->kg_filled;
				$rows[(int)$row->id]['fills_unmatched'] = (int)$row->fills_unmatched;
			}}
			$shells = $this->db->query(
				"SELECT warehouse_id AS id, SUM(CASE WHEN status = 'out' THEN 1 ELSE 0 END) AS shells_out,"
				.' COUNT(*) AS shells_registered'
				." FROM db_fuel_cylinder WHERE store_id = $store AND warehouse_id IN ($list)"
				." AND status IN ('out','in_shop') GROUP BY warehouse_id"
			)->result();
			foreach($shells as $row){ if(isset($rows[(int)$row->id])){
				$rows[(int)$row->id]['shells_out'] = (int)$row->shells_out;
				$rows[(int)$row->id]['shells_registered'] = (int)$row->shells_registered;
			}}
		}

		if($this->_runs('cylinder_deposits')){
			// Money owed back to customers, never income.
			$deposits = $this->db->query(
				"SELECT warehouse_id AS id,"
				." SUM(CASE WHEN event_type = 'paid' THEN amount ELSE -amount END) AS deposits_held"
				." FROM db_fuel_cylinder_deposit WHERE store_id = $store AND warehouse_id IN ($list)"
				." AND deposit_date$range GROUP BY warehouse_id"
			)->result();
			foreach($deposits as $row){ if(isset($rows[(int)$row->id])){
				$rows[(int)$row->id]['deposits_held'] = (float)$row->deposits_held;
			}}
		}

		if($jobs_run && $this->db->field_exists('warehouse_id', 'db_service_jobs')){
			// Work still on the board belongs to the head office table whatever date
			// it carries, because it has not been dealt with yet. Only finished work
			// is filtered to the period, since that is the part that already happened.
			$jobs = $this->db->query(
				"SELECT warehouse_id AS id,"
				." SUM(CASE WHEN status NOT IN ('completed','cancelled') THEN 1 ELSE 0 END) AS jobs_open,"
				." SUM(CASE WHEN status = 'completed' AND completed_date$range THEN 1 ELSE 0 END) AS jobs_completed,"
				." SUM(CASE WHEN status = 'completed' AND completed_date$range"
				." THEN labour_charge + parts_total ELSE 0 END) AS job_charge"
				." FROM db_service_jobs WHERE store_id = $store AND warehouse_id IN ($list)"
				.' GROUP BY warehouse_id'
			)->result();
			foreach($jobs as $row){ if(isset($rows[(int)$row->id])){
				$rows[(int)$row->id]['jobs_completed'] = (int)$row->jobs_completed;
				$rows[(int)$row->id]['jobs_open'] = (int)$row->jobs_open;
				$rows[(int)$row->id]['job_charge'] = (float)$row->job_charge;
			}}
		}

		// A site with nothing at all to report is noise on a head-office table.
		foreach($rows as $id => $row){ if($this->_row_is_blank($row)) unset($rows[$id]); }
		ksort($rows);
		// The company's own sites read first; unassigned work sits at the bottom.
		if(isset($rows[0])){ $unassigned = $rows[0]; unset($rows[0]); $rows[0] = $unassigned; }

		$total = $this->_blank_site('Whole company');
		foreach($this->_report_figures() as $key){
			foreach($rows as $row) $total[$key] += $row[$key];
		}

		return array(
			'from'=>$from, 'to'=>$to, 'sections'=>$sections,
			'sites'=>$rows, 'total'=>$total, 'site_count'=>count($rows),
		);
	}

	/** The figure columns a site row carries, in the order the screen prints them. */
	private function _report_figures(){
		$blank = $this->_blank_site('');
		unset($blank['name'], $blank['town']);
		return array_keys($blank);
	}

	/** Does this company run that operation anywhere at all? */
	private function _runs($flag){
		return function_exists('mp_feature_enabled') && mp_feature_enabled($flag);
	}

	private function _blank_site($name){
		return array(
			'name'=>$name, 'town'=>'',
			'sales_count'=>0, 'sales_value'=>0,
			'stock_lines'=>0, 'stock_value'=>0,
			'loads_sent'=>0, 'loads_received'=>0, 'loads_open'=>0,
			'litres_metered'=>0, 'variance_litres'=>0, 'duties_awaiting'=>0,
			'fills'=>0, 'kg_filled'=>0, 'fills_unmatched'=>0,
			'shells_out'=>0, 'shells_registered'=>0, 'deposits_held'=>0,
			'jobs_completed'=>0, 'jobs_open'=>0, 'job_charge'=>0,
		);
	}

	/** Ignore the two label keys when deciding whether a row carries any figures. */
	private function _row_is_blank($row){
		foreach($row as $key => $value){
			if($key === 'name' || $key === 'town') continue;
			if((float)$value !== 0.0) return false;
		}
		return true;
	}

	private function _report_date($value, $fallback){
		$value = trim((string)$value);
		if($value === '') return $fallback;
		$parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
		if($parsed && $parsed->format('Y-m-d') === $value) return $value;
		$slashed = DateTimeImmutable::createFromFormat('!d-m-Y', $value);
		if($slashed && $slashed->format('d-m-Y') === $value) return $slashed->format('Y-m-d');
		return $fallback;
	}
}
