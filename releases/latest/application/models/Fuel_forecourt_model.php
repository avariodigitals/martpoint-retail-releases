<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Oil & Gas forecourt: tanks, pumps, duties and the two reconciliations that
 * prove a day at a filling station.
 *
 * The rule this whole model is built around: the till already books the sale and
 * the stock ledger already counts it, so nothing here deducts litres again.
 * A tank is its own physical book — fills in, measured litres out, and whatever
 * a manager signs for — and a duty compares three accounts that cannot each
 * fake the other two: what the pump meters measured, what the till rang up, and
 * what a dip says is really in the tank.
 */
class Fuel_forecourt_model extends CI_Model {

	public function __construct(){
		parent::__construct();
		$this->load->helper('business_profile');
		$this->load->model('oil_gas_model', 'oil_gas');
	}

	/** Sites the current user may work on. null = the whole company. */
	public function scoped_location_ids($store_id){
		return $this->oil_gas->scoped_location_ids($store_id);
	}

	/** Does this site run a forecourt at all? Company gate first, then the site's own switch. */
	public function forecourt_enabled($warehouse_id, $flag = 'pump_metering'){
		return function_exists('mp_location_feature_enabled') && mp_location_feature_enabled($flag, (int)$warehouse_id);
	}

	/** The user's sites that actually run pumps and tanks — the forecourt picker. */
	public function forecourt_sites($store_id, $flag = 'pump_metering'){
		$sites = array();
		foreach($this->oil_gas->locations($store_id) as $location){
			if($this->forecourt_enabled((int)$location->id, $flag)) $sites[] = $location;
		}
		return $sites;
	}

	public function has_forecourt($store_id, $flag = 'pump_metering'){
		return count($this->forecourt_sites($store_id, $flag)) > 0;
	}

	/** Products this site can hold, so a tank or pump can only be given a real one. */
	public function items_for_site($store_id, $warehouse_id){
		return $this->db->query(
			'SELECT i.id, i.item_name AS name FROM db_items i'
			.' INNER JOIN db_warehouseitems w ON w.item_id = i.id AND w.warehouse_id = '.(int)$warehouse_id
			.' WHERE i.store_id = '.(int)$store_id
			.' GROUP BY i.id, i.item_name ORDER BY i.item_name ASC'
		)->result();
	}

	// ------------------------------------------------------------------ tanks

	public function tanks($store_id, $warehouse_ids = null){
		$ids = $warehouse_ids === null ? $this->scoped_location_ids($store_id) : $warehouse_ids;
		if(is_array($ids) && empty($ids)) return array();
		$scope = '';
		if(is_array($ids)){
			$clean = array_values(array_filter(array_map('intval', $ids)));
			if(empty($clean)) return array();
			$scope = ' AND warehouse_id IN ('.implode(',', $clean).')';
		}
		return $this->db->query(
			'SELECT id, warehouse_id, tank_name, item_id, capacity_litres, safe_fill_litres, note'
			.' FROM db_fuel_tank WHERE store_id = '.(int)$store_id.' AND status = 1'.$scope
			.' ORDER BY warehouse_id ASC, id ASC'
		)->result();
	}

	public function tank($store_id, $tank_id){
		$tank = $this->db->query('SELECT * FROM db_fuel_tank WHERE id = '.(int)$tank_id.' AND store_id = '.(int)$store_id)->row();
		return $tank;
	}

	/** Litres the tank should be holding: every signed movement, summed. */
	public function tank_book_litres($tank_id){
		$row = $this->db->query('SELECT COALESCE(SUM(litres),0) AS litres FROM db_fuel_tank_movement WHERE tank_id = '.(int)$tank_id)->row();
		return (float)$row->litres;
	}

	/**
	 * Book, last dip and the open gap for many tanks in two queries.
	 */
	public function tank_positions($store_id, $tank_ids){
		$positions = array();
		foreach((array)$tank_ids as $id) $positions[(int)$id] = array('book'=>0.0, 'dip'=>null, 'dip_date'=>null, 'gap'=>null);
		if(empty($tank_ids)) return $positions;

		$ids = implode(',', array_map('intval', (array)$tank_ids));
		$movements = $this->db->query(
			'SELECT tank_id, COALESCE(SUM(litres),0) AS litres FROM db_fuel_tank_movement'
			.' WHERE store_id = '.(int)$store_id.' AND tank_id IN ('.$ids.') GROUP BY tank_id'
		)->result();
		foreach($movements as $row) $positions[(int)$row->tank_id]['book'] = (float)$row->litres;

		// Newest dip per tank, taken by date then id so a same-day re-dip wins.
		$dips = $this->db->query(
			'SELECT d.tank_id, d.level_litres, d.dip_date FROM db_fuel_dip d'
			.' INNER JOIN (SELECT tank_id, MAX(id) AS id FROM db_fuel_dip WHERE store_id = '.(int)$store_id
			.' AND tank_id IN ('.$ids.') GROUP BY tank_id) latest ON latest.id = d.id'
		)->result();
		foreach($dips as $row){
			$key = (int)$row->tank_id;
			$positions[$key]['dip'] = (float)$row->level_litres;
			$positions[$key]['dip_date'] = $row->dip_date;
			$positions[$key]['gap'] = (float)$row->level_litres - $positions[$key]['book'];
		}
		return $positions;
	}

	/**
	 * Add a tank, or move one. Quantities are checked against the site so a
	 * station cannot be given a tank that belongs to another company.
	 */
	public function save_tank($store_id, $post){
		$store = (int)$store_id;
		$warehouse_id = (int)($post['warehouse_id'] ?? 0);
		$tank_id = (int)($post['tank_id'] ?? 0);
		$name = trim((string)($post['tank_name'] ?? ''));
		$item_id = (int)($post['item_id'] ?? 0);
		$capacity = (float)($post['capacity_litres'] ?? 0);
		$safe = (float)($post['safe_fill_litres'] ?? 0);

		if($name === '') return 'Give the tank a name the staff will recognise, such as "Tank 1 — Petrol".';
		if($capacity <= 0) return 'Enter the tank\'s capacity in litres.';
		if(!$this->forecourt_enabled($warehouse_id)) return 'Pump metering is not switched on for this location.';
		if(!in_array($warehouse_id, $this->site_ids($store), true)) return 'That location is not one of yours.';
		if($item_id > 0 && !$this->item_is_yours($store, $item_id)) return 'That product is not one of yours.';
		if($safe > $capacity) return 'The safe fill level cannot be above the capacity.';

		$data = array(
			'store_id'=>$store, 'warehouse_id'=>$warehouse_id, 'tank_name'=>$this->clean($name),
			'item_id'=>$item_id, 'capacity_litres'=>$capacity,
			'safe_fill_litres'=>($safe > 0 ? $safe : round($capacity * 0.95, 3)),
			'note'=>isset($post['note']) ? $this->clean($post['note']) : null,
		);
		if($tank_id > 0){
			if(!$this->tank($store, $tank_id)) return 'That tank does not belong to this company.';
			$this->db->where('id', $tank_id)->where('store_id', $store)->update('db_fuel_tank', $data);
			return 'success';
		}
		$data['created_by'] = $this->actor();
		$data['created_date'] = date('Y-m-d');
		$this->db->insert('db_fuel_tank', $data);
		return 'success';
	}

	// ------------------------------------------------------------------ pumps

	public function pumps($store_id, $warehouse_id = 0){
		$scope = '';
		$ids = $this->scoped_location_ids($store_id);
		if(is_array($ids)){
			if(empty($ids)) return array();
			$scope = ' AND p.warehouse_id IN ('.implode(',', array_map('intval', $ids)).')';
		}
		if((int)$warehouse_id > 0) $scope .= ' AND p.warehouse_id = '.(int)$warehouse_id;
		return $this->db->query(
			'SELECT p.id, p.warehouse_id, p.tank_id, p.item_id, p.pump_name, p.opening_meter, t.tank_name'
			.' FROM db_fuel_pump p LEFT JOIN db_fuel_tank t ON t.id = p.tank_id'
			.' WHERE p.store_id = '.(int)$store_id.' AND p.status = 1'.$scope
			.' ORDER BY p.warehouse_id ASC, p.id ASC'
		)->result();
	}

	public function save_pump($store_id, $post){
		$store = (int)$store_id;
		$pump_id = (int)($post['pump_id'] ?? 0);
		$warehouse_id = (int)($post['warehouse_id'] ?? 0);
		$tank_id = (int)($post['tank_id'] ?? 0);
		$name = trim((string)($post['pump_name'] ?? ''));
		$opening = (float)($post['opening_meter'] ?? 0);

		if($name === '') return 'Give the pump a name the staff will read out, such as "Pump 2".';
		if(!in_array($warehouse_id, $this->site_ids($store), true)) return 'That location is not one of yours.';
		if(!$this->forecourt_enabled($warehouse_id)) return 'Pump metering is not switched on for this location.';
		if($opening < 0) return 'A pump meter cannot start below zero.';

		$tank = $this->tank($store, $tank_id);
		if(!$tank) return 'Choose which tank this pump draws from.';
		if((int)$tank->warehouse_id !== $warehouse_id) return 'That tank stands at a different location than the pump.';

		$item_id = (int)($post['item_id'] ?? $tank->item_id);
		if($item_id <= 0) return 'Say which product this pump gives out.';
		if((int)$tank->item_id > 0 && (int)$tank->item_id !== $item_id){
			return 'That pump is set to a different product than its tank. Change the tank or the pump, not both.';
		}
		if($opening > 0 && $this->last_closing_meter($store, $pump_id) > $opening){
			return 'The starting meter is below a reading this pump has already reported.';
		}

		$data = array(
			'store_id'=>$store, 'warehouse_id'=>$warehouse_id, 'tank_id'=>$tank_id, 'item_id'=>$item_id,
			'pump_name'=>$this->clean($name), 'opening_meter'=>$opening,
		);
		if($pump_id > 0){
			if(!$this->pump($store, $pump_id)) return 'That pump does not belong to this company.';
			$this->db->where('id', $pump_id)->where('store_id', $store)->update('db_fuel_pump', $data);
			return 'success';
		}
		$data['status'] = 1;
		$data['created_by'] = $this->actor();
		$data['created_date'] = date('Y-m-d');
		$this->db->insert('db_fuel_pump', $data);
		return 'success';
	}

	public function pump($store_id, $pump_id){
		return $this->db->query('SELECT * FROM db_fuel_pump WHERE id = '.(int)$pump_id.' AND store_id = '.(int)$store_id)->row();
	}

	/** Where the meter stands now: last duty's closing face, else the pump's own start. */
	public function last_closing_meter($store_id, $pump_id, $before_shift_id = 0){
		$row = $this->db->query(
			'SELECT COALESCE(m.closing_meter, m.opening_meter) AS meter FROM db_fuel_meter m'
			.' INNER JOIN db_fuel_shift s ON s.id = m.shift_id'
			.' WHERE m.pump_id = '.(int)$pump_id.' AND m.store_id = '.(int)$store_id
			.' AND s.status = \'closed\''.($before_shift_id ? ' AND m.shift_id <> '.(int)$before_shift_id : '')
			.' ORDER BY m.shift_id DESC LIMIT 1'
		)->row();
		if($row) return (float)$row->meter;
		$pump = $this->pump($store_id, $pump_id);
		return $pump ? (float)$pump->opening_meter : 0.0;
	}

	/**
	 * Where every pump's meter stands now, in one query: the newest closed duty's
	 * closing face, falling back to the reading taken when the pump was registered.
	 */
	public function pump_current_meters($store_id, $warehouse_id){
		$meters = array();
		$rows = $this->db->query(
			'SELECT p.id AS pump_id, COALESCE(s.litres_read, p.opening_meter) AS metres FROM db_fuel_pump p'
			.' LEFT JOIN (SELECT m.pump_id, COALESCE(m.closing_meter, m.opening_meter) AS litres_read, m.shift_id'
			.' FROM db_fuel_meter m INNER JOIN db_fuel_shift s2 ON s2.id = m.shift_id'
			." WHERE s2.status = 'closed') s ON s.pump_id = p.id"
			.' WHERE p.store_id = '.(int)$store_id.' AND p.warehouse_id = '.(int)$warehouse_id
			.' ORDER BY s.shift_id ASC'
		)->result();
		foreach($rows as $row) $meters[(int)$row->pump_id] = (float)$row->metres;
		return $meters;
	}

	// ------------------------------------------------------------------ duties

	public function current_shift($store_id, $warehouse_id){
		return $this->db->query(
			'SELECT * FROM db_fuel_shift WHERE store_id = '.(int)$store_id.' AND warehouse_id = '.(int)$warehouse_id
			.' AND status = \'open\' ORDER BY id DESC LIMIT 1'
		)->row();
	}

	public function shift($store_id, $shift_id){
		return $this->db->query('SELECT * FROM db_fuel_shift WHERE id = '.(int)$shift_id.' AND store_id = '.(int)$store_id)->row();
	}

	/**
	 * Open a duty. Every pump on the site is captured at the meter it currently
	 * stands at, so a duty can never quietly leave litres in the previous one.
	 */
	public function open_shift($store_id, $warehouse_id){
		$store = (int)$store_id;
		if(!$this->forecourt_enabled($warehouse_id)) return 'This location does not run pump metering.';
		if(!$this->works_at($store, $warehouse_id)) return 'You are not assigned to this location.';
		if($this->current_shift($store, $warehouse_id)) return 'A duty is already open here. Close it before opening another.';

		$pumps = $this->pumps($store, $warehouse_id);
		if(empty($pumps)) return 'Register at least one pump at this location before opening a duty.';

		// A second open duty would silently leave litres in the first one, so the
		// guard is re-read immediately before the insert rather than trusted once.
		if($this->current_shift($store, $warehouse_id)) return 'A duty is already open here. Close it before opening another.';

		$data = array(
			'store_id'=>$store, 'warehouse_id'=>(int)$warehouse_id,
			'shift_code'=>'FC/'.date('Y/m').'/'.str_pad((string)(1 + (int)$this->shift_count($store, $warehouse_id)), 4, '0', STR_PAD_LEFT),
			'status'=>'open', 'opened_at'=>date('Y-m-d H:i:s'),
			'opened_by'=>(int)get_current_user_id(), 'opened_by_name'=>$this->actor(),
		);
		$this->db->insert('db_fuel_shift', $data);
		$shift_id = (int)$this->db->insert_id();

		foreach($pumps as $pump){
			$this->db->insert('db_fuel_meter', array(
				'store_id'=>$store, 'shift_id'=>$shift_id, 'pump_id'=>(int)$pump->id, 'item_id'=>(int)$pump->item_id,
				'opening_meter'=>$this->last_closing_meter($store, $pump->id),
			));
		}
		return $shift_id;
	}

	public function shift_count($store_id, $warehouse_id){
		$row = $this->db->query('SELECT COUNT(*) AS total FROM db_fuel_shift WHERE store_id = '.(int)$store_id
			.' AND warehouse_id = '.(int)$warehouse_id)->row();
		return (int)$row->total;
	}

	/** The duty's own meter lines, with the site's product names attached. */
	public function shift_meters($store_id, $shift_id){
		return $this->db->query(
			'SELECT m.id, m.pump_id, m.item_id, m.opening_meter, m.closing_meter, m.litres_pumped, p.pump_name'
			.' FROM db_fuel_meter m INNER JOIN db_fuel_pump p ON p.id = m.pump_id'
			.' WHERE m.shift_id = '.(int)$shift_id.' AND m.store_id = '.(int)$store_id.' ORDER BY p.id ASC'
		)->result();
	}

	/**
	 * What the till rang up for these products while the duty was running.
	 * Returns and cancelled or voided invoices are taken out, so the two sides
	 * of the comparison really are the same litres.
	 */
	public function till_litres($store_id, $warehouse_id, $item_ids, $from, $to){
		$clean = array_values(array_filter(array_map('intval', (array)$item_ids)));
		if(empty($clean)) return 0.0;
		$list = implode(',', $clean);
		$store = (int)$store_id;
		$from_ts = $this->db->escape((string)$from);
		$to_ts = $this->db->escape((string)$to);

		$sold = $this->db->query(
			'SELECT COALESCE(SUM(COALESCE(a.base_unit_qty, a.sales_qty)),0) AS litres'
			.' FROM db_salesitems a INNER JOIN db_sales b ON b.id = a.sales_id'
			.' WHERE b.store_id = '.$store.' AND b.warehouse_id = '.(int)$warehouse_id
			.' AND a.item_id IN ('.$list.')'
			." AND (b.sales_status IS NULL OR b.sales_status NOT IN ('Cancelled','Opening'))"
			.' AND (b.voided_at IS NULL)'
			.' AND TIMESTAMP(b.sales_date, b.created_time) BETWEEN '.$from_ts.' AND '.$to_ts
		)->row();

		$back = $this->db->query(
			'SELECT COALESCE(SUM(COALESCE(a.base_unit_qty, a.return_qty)),0) AS litres'
			.' FROM db_salesitemsreturn a INNER JOIN db_salesreturn b ON b.id = a.return_id'
			.' WHERE b.store_id = '.$store.' AND b.warehouse_id = '.(int)$warehouse_id
			.' AND a.item_id IN ('.$list.')'
			." AND (b.return_status IS NULL OR b.return_status NOT IN ('Cancelled','Opening'))"
			.' AND TIMESTAMP(b.return_date, b.created_time) BETWEEN '.$from_ts.' AND '.$to_ts
		)->row();

		return (float)$sold->litres - (float)$back->litres;
	}

	/**
	 * Close a duty. Meters must only ever climb, every pump must be read, and the
	 * measured litres leave the tank book there and then — they were physically
	 * dispensed, whether or not anybody rang them up.
	 */
	public function close_shift($store_id, $shift_id, $readings, $note = ''){
		$store = (int)$store_id;
		$shift = $this->shift($store, $shift_id);
		if(!$shift) return 'That duty does not belong to this company.';
		if($shift->status !== 'open') return 'This duty has already been closed.';

		$lines = $this->shift_meters($store, $shift_id);
		if(empty($lines)) return 'This duty has no pumps on it, so there is nothing to read.';

		$parsed = array();
		foreach($lines as $line){
			$raw = $readings[$line->pump_id] ?? null;
			if($raw === null || $raw === '') return $line->pump_name.' was left blank. Every pump needs its closing reading.';
			if(!is_numeric($raw)) return $line->pump_name.' needs a plain number of litres on the meter.';
			$closing = (float)$raw;
			if($closing < (float)$line->opening_meter){
				return $line->pump_name.'\'s closing reading is below where it opened ('
					.$this->l((float)$line->opening_meter).' L). A pump meter only goes up.';
			}
			$parsed[(int)$line->pump_id] = array('closing'=>$closing, 'litres'=>$closing - (float)$line->opening_meter, 'item_id'=>(int)$line->item_id, 'name'=>$line->pump_name);
		}

		$metered = 0.0;
		foreach($parsed as $figure) $metered += $figure['litres'];

		$items = array();
		foreach($parsed as $figure) if($figure['item_id'] > 0) $items[$figure['item_id']] = true;
		$till = $this->till_litres($store, $shift->warehouse_id, array_keys($items), $shift->opened_at, date('Y-m-d H:i:s'));

		$variance = $metered - $till;
		$percent = $metered > 0 ? abs($variance) / $metered * 100 : 0;
		$tolerance = function_exists('mp_forecourt_shift_variance_percent') ? mp_forecourt_shift_variance_percent($store) : 1.0;
		$needs_approval = $metered > 0 && $percent > $tolerance;

		$now = date('Y-m-d H:i:s');
		$this->db->where('id', $shift_id)->update('db_fuel_shift', array(
			'status'=>'closed', 'closed_at'=>$now,
			'closed_by'=>(int)get_current_user_id(), 'closed_by_name'=>$this->actor(),
			'litres_metered'=>$metered, 'litres_till'=>$till,
			'variance_litres'=>$variance,
			'variance_value'=>round($variance * $this->purchase_price_store($store, $items), 4),
			'variance_percent'=>round($percent, 3),
			'approval_status'=>$needs_approval ? 'pending' : 'not_required',
			'close_note'=>$note === '' ? null : $this->clean($note),
		));

		foreach($parsed as $pump_id => $figure){
			$this->db->where('id', $this->meter_line_id($store, $shift_id, $pump_id))->update('db_fuel_meter', array(
				'closing_meter'=>$figure['closing'], 'litres_pumped'=>$figure['litres'],
			));
			if($figure['litres'] <= 0) continue;
			$tank_id = (int)($this->pump($store, $pump_id)->tank_id ?? 0);
			if($tank_id <= 0) continue;
			$this->db->insert('db_fuel_tank_movement', array(
				'store_id'=>$store, 'tank_id'=>$tank_id, 'item_id'=>$figure['item_id'],
				'litres'=>-$figure['litres'], 'movement_type'=>'metered', 'shift_id'=>(int)$shift_id,
				'reason'=>$figure['name'].' measured '.$this->l($figure['litres']).' L on this duty',
				'created_by'=>$this->actor(), 'movement_date'=>date('Y-m-d'),
			));
		}

		if($needs_approval){
			$this->log_shift_approval($store, $shift_id, $metered, $till, $variance, $percent, $tolerance);
		}
		return 'success';
	}

	public function meter_line_id($store_id, $shift_id, $pump_id){
		$row = $this->db->query('SELECT id FROM db_fuel_meter WHERE store_id = '.(int)$store_id
			.' AND shift_id = '.(int)$shift_id.' AND pump_id = '.(int)$pump_id)->row();
		return $row ? (int)$row->id : 0;
	}

	/** Cost value of the unexplained litres, so the manager sees money not just volume. */
	public function purchase_price_store($store_id, $items){
		$ids = implode(',', array_map('intval', array_keys((array)$items)));
		if($ids === '') return 0.0;
		$row = $this->db->query('SELECT COALESCE(AVG(purchase_price),0) AS price FROM db_items WHERE store_id = '.(int)$store_id.' AND id IN ('.$ids.')')->row();
		return (float)$row->price;
	}

	private function log_shift_approval($store_id, $shift_id, $metered, $till, $variance, $percent, $tolerance){
		$shift = $this->shift($store_id, $shift_id);
		$CI =& get_instance();
		$CI->load->model('approval_logs_model');
		$fingerprint = md5($shift_id.'|'.$metered.'|'.$till.'|'.$tolerance);
		$CI->approval_logs_model->supersedeTarget('fuel_shift', (int)$shift_id);
		$log_id = $CI->approval_logs_model->log(array(
			'store_id'             => (int)$store_id,
			'branch_id'            => (int)$shift->warehouse_id,
			'action_type'          => 'fuel_shift_variance',
			'approval_type'        => 'forecourt_variance',
			'requesting_user_id'   => (int)get_current_user_id(),
			'requesting_user_name' => $this->actor(),
			'reason'               => $variance > 0
				? 'Pumps measured more than the till rang up'
				: 'The till rang up more than the pumps measured',
			'previous_value'       => json_encode(array('metered'=>$metered)),
			'new_value'            => json_encode(array('till'=>$till, 'variance_litres'=>$variance,
				'variance_percent'=>round($percent, 3), 'tolerance_percent'=>$tolerance)),
			'status'               => 'pending',
			'amount'               => abs((float)$shift->variance_value),
			'threshold'            => $tolerance,
			'target_module'        => 'fuel_shift',
			'target_id'            => (int)$shift_id,
			'target_version'       => $fingerprint,
		));
		if($log_id){
			$this->db->where('id', (int)$shift_id)->update('db_fuel_shift', array('approval_log_id'=>(int)$log_id));
		}
	}

	/**
	 * A manager's decision. Accepting settles the duty and leaves the measured
	 * litres out of the tank — they really were dispensed. Rejecting reopens the
	 * duty and takes the tank book back, so the readings can be retaken honestly.
	 */
	public function decide_shift($store_id, $shift_id, $decision, $note){
		$store = (int)$store_id;
		$shift = $this->shift($store, $shift_id);
		if(!$shift) return 'That duty does not belong to this company.';
		if($shift->approval_status !== 'pending') return 'This duty is not waiting for a decision.';
		if($note === '') return 'Say why before deciding on this duty.';

		$CI =& get_instance();
		$CI->load->model('approval_logs_model');

		if($decision === 'approve'){
			if((int)$shift->approval_log_id > 0){
				$CI->approval_logs_model->updateStatus((int)$shift->approval_log_id, 'approved',
					(int)get_current_user_id(), $this->actor(), 'manual');
			}
			$this->db->where('id', $shift_id)->update('db_fuel_shift', array(
				'approval_status'=>'approved',
				'variance_reason'=>$this->clean($note),
			));
			return 'success';
		}
		if($decision === 'reject'){
			if((int)$shift->approval_log_id > 0){
				$CI->approval_logs_model->updateStatus((int)$shift->approval_log_id, 'rejected',
					(int)get_current_user_id(), $this->actor(), 'manual');
			}
			$this->db->where('shift_id', (int)$shift_id)->delete('db_fuel_tank_movement');
			foreach($this->shift_meters($store, $shift_id) as $line){
				$this->db->where('id', (int)$line->id)->update('db_fuel_meter', array('closing_meter'=>null, 'litres_pumped'=>0));
			}
			$this->db->where('id', $shift_id)->update('db_fuel_shift', array(
				'status'=>'open', 'closed_at'=>null, 'closed_by'=>null, 'closed_by_name'=>null,
				'litres_metered'=>0, 'litres_till'=>0, 'variance_litres'=>0, 'variance_value'=>0,
				'variance_percent'=>0, 'approval_status'=>'not_required', 'approval_log_id'=>null,
				'variance_reason'=>$this->clean($note),
			));
			return 'success';
		}
		return 'Unknown decision.';
	}

	// -------------------------------------------------------------- dip checks

	/**
	 * A dip never changes the book on its own — it is evidence. Within the
	 * company's allowance the difference is booked as normal; beyond it nothing
	 * moves until a manager signs, so an unexplained hole in a line cannot be
	 * quietly written off.
	 */
	public function record_dip($store_id, $tank_id, $level, $note = ''){
		$store = (int)$store_id;
		$tank = $this->tank($store, $tank_id);
		if(!$tank) return 'That tank does not belong to this company.';
		if(!is_numeric($level) || (float)$level < 0) return 'Enter the dipped quantity in litres.';
		if((float)$level > (float)$tank->capacity_litres){
			return 'That is more than the tank can hold ('.$this->l((float)$tank->capacity_litres).' L).';
		}
		if(!$this->works_at($store, $tank->warehouse_id)) return 'That tank stands at a location you are not assigned to.';

		$book = $this->tank_book_litres($tank->id);
		$delta = (float)$level - $book;
		$tolerance = function_exists('mp_forecourt_tank_variance_percent') ? mp_forecourt_tank_variance_percent($store) : 0.5;
		$base = (float)$tank->capacity_litres > 0 ? (float)$tank->capacity_litres : 1;
		$percent = abs($delta) / $base * 100;
		$awaiting = ($delta >= 0.0001 || $delta <= -0.0001) && $percent > $tolerance;

		$this->db->insert('db_fuel_dip', array(
			'store_id'=>$store, 'tank_id'=>(int)$tank->id, 'item_id'=>(int)$tank->item_id,
			'level_litres'=>(float)$level, 'book_litres'=>$book,
			'dip_date'=>date('Y-m-d'), 'dip_time'=>date('H:i'),
			'decision_status'=>$awaiting ? 'awaiting' : 'none',
			'note'=>$note === '' ? null : $this->clean($note),
			'created_by'=>$this->actor(),
		));
		$dip_id = (int)$this->db->insert_id();

		if(abs($delta) < 0.0001) return 'Recorded. The tank matches its book exactly.';
		if(!$awaiting){
			$this->book_tank_correction($store, $tank, $delta, $dip_id,
				'Dip inside your '.$this->p($tolerance).' allowance ('.$this->p($percent).' of capacity)', null);
			return 'Recorded. '.$this->l(abs($delta)).' L '.($delta > 0 ? 'found' : 'lost').' — inside your '.$this->p($tolerance).' allowance, so the tank book now matches the dip.';
		}
		$log_id = $this->log_dip_approval($store, $dip_id, $tank, $book, (float)$level, $delta, $percent, $tolerance);
		if($log_id){
			$this->db->where('id', $dip_id)->update('db_fuel_dip', array('approval_log_id'=>$log_id));
		}
		return 'Recorded. The dip is '.$this->l(abs($delta)).' L '.($delta > 0 ? 'above' : 'below').' the book — over your '.$this->p($tolerance).' allowance, so it waits for a manager before anything is written off.';
	}

	/**
	 * A manager settling an open dip gap. Only a dip still marked as waiting can be
	 * signed for, so the same shortfall can never be written off twice.
	 */
	public function decide_dip($store_id, $dip_id, $decision, $note){
		$store = (int)$store_id;
		$dip = $this->db->query('SELECT d.*, t.warehouse_id, t.tank_name FROM db_fuel_dip d'
			.' INNER JOIN db_fuel_tank t ON t.id = d.tank_id'
			.' WHERE d.id = '.(int)$dip_id.' AND d.store_id = '.$store)->row();
		if(!$dip) return 'That dip does not belong to this company.';
		if($dip->decision_status !== 'awaiting') return 'This dip is not waiting for a decision.';
		if($note === '') return 'Say why before deciding on this dip.';
		if(!$this->works_at($store, $dip->warehouse_id)) return 'That tank stands at a location you are not assigned to.';

		$CI =& get_instance();
		$CI->load->model('approval_logs_model');

		if($decision === 'accept'){
			$book = $this->tank_book_litres($dip->tank_id);
			$gap = (float)$dip->level_litres - $book;
			if(abs($gap) < 0.0001){
				$this->db->where('id', (int)$dip_id)->update('db_fuel_dip', array(
					'decision_status'=>'accepted', 'note'=>$this->clean($note),
				));
				return 'success';
			}
			$tank = $this->tank($store, $dip->tank_id);
			$this->book_tank_correction($store, $tank, $gap, (int)$dip_id, $this->clean($note), (int)$dip->approval_log_id ?: null);
			if((int)$dip->approval_log_id > 0){
				$CI->approval_logs_model->updateStatus((int)$dip->approval_log_id, 'approved',
					(int)get_current_user_id(), $this->actor(), 'manual');
			}
			$this->db->where('id', (int)$dip_id)->update('db_fuel_dip', array('decision_status'=>'accepted'));
			return 'success';
		}
		if($decision === 'reject'){
			if((int)$dip->approval_log_id > 0){
				$CI->approval_logs_model->updateStatus((int)$dip->approval_log_id, 'rejected',
					(int)get_current_user_id(), $this->actor(), 'manual');
			}
			$this->db->where('id', (int)$dip_id)->update('db_fuel_dip', array(
				'decision_status'=>'declined', 'note'=>$this->clean($note),
			));
			return 'success';
		}
		return 'Unknown decision.';
	}

	private function log_dip_approval($store_id, $dip_id, $tank, $book, $level, $delta, $percent, $tolerance){
		$CI =& get_instance();
		$CI->load->model('approval_logs_model');
		$CI->approval_logs_model->supersedeTarget('fuel_dip', (int)$dip_id);
		$log_id = $CI->approval_logs_model->log(array(
			'store_id'             => (int)$store_id,
			'branch_id'            => (int)$tank->warehouse_id,
			'action_type'          => 'fuel_dip_variance',
			'approval_type'        => 'forecourt_dip',
			'requesting_user_id'   => (int)get_current_user_id(),
			'requesting_user_name' => $this->actor(),
			'reason'               => $tank->tank_name.': the dip is '.$this->l(abs($delta)).' L '
				.($delta > 0 ? 'above' : 'below').' what the book holds',
			'previous_value'       => json_encode(array('book_litres'=>$book)),
			'new_value'            => json_encode(array('dip_litres'=>$level, 'gap_litres'=>$delta,
				'gap_percent'=>round($percent, 3), 'tolerance_percent'=>$tolerance)),
			'status'               => 'pending',
			'amount'               => 0,
			'threshold'            => $tolerance,
			'target_module'        => 'fuel_dip',
			'target_id'            => (int)$dip_id,
			'target_version'       => md5($dip_id.'|'.$level.'|'.$tolerance),
		));
		return $log_id ? (int)$log_id : 0;
	}

	public function open_dip_gaps($store_id, $limit = 10){
		$store = (int)$store_id;
		$ids = $this->accessible_site_ids($store);
		if(is_array($ids) && empty($ids)) return array();
		$scope = is_array($ids) ? ' AND t.warehouse_id IN ('.implode(',', $ids).')' : '';
		$limit = max(1, min(50, (int)$limit));
		$rows = $this->db->query(
			'SELECT d.id AS dip_id, d.tank_id, d.item_id, d.level_litres, d.book_litres, d.dip_date, d.note, d.created_by,'
			.' t.tank_name, t.warehouse_id, t.capacity_litres'
			.' FROM db_fuel_dip d INNER JOIN db_fuel_tank t ON t.id = d.tank_id'
			.' WHERE d.store_id = '.$store.$scope
			." AND d.decision_status = 'awaiting'"
			.' ORDER BY d.id DESC LIMIT '.$limit
		)->result();
		$gaps = array();
		if(empty($rows)) return $gaps;
		$tank_ids = array();
		foreach($rows as $row) $tank_ids[] = (int)$row->tank_id;
		$books = $this->tank_positions($store, $tank_ids);
		foreach($rows as $row){
			$book = isset($books[(int)$row->tank_id]) ? (float)$books[(int)$row->tank_id]['book'] : 0.0;
			$gap = (float)$row->level_litres - $book;
			$base = (float)$row->capacity_litres > 0 ? (float)$row->capacity_litres : 1;
			if(abs($gap) < 0.0001) continue;
			$row->gap = $gap;
			$row->gap_percent = abs($gap) / $base * 100;
			$gaps[] = $row;
		}
		return $gaps;
	}

	private function book_tank_correction($store_id, $tank, $delta, $dip_id, $reason, $approval_log_id){
		$this->db->insert('db_fuel_tank_movement', array(
			'store_id'=>(int)$store_id, 'tank_id'=>(int)$tank->id, 'item_id'=>(int)$tank->item_id,
			'litres'=>$delta, 'movement_type'=>'correction', 'dip_id'=>(int)$dip_id,
			'reason'=>$reason, 'approval_log_id'=>$approval_log_id,
			'created_by'=>$this->actor(), 'movement_date'=>date('Y-m-d'),
		));
	}

	// ------------------------------------------------------------ bulk fills

	/** Tanks at this site that can take this product. */
	public function fill_options($store_id, $warehouse_id, $item_id){
		$tanks = $this->db->query(
			'SELECT id, tank_name, item_id, capacity_litres, safe_fill_litres FROM db_fuel_tank'
			.' WHERE store_id = '.(int)$store_id.' AND status = 1 AND warehouse_id = '.(int)$warehouse_id
			.' AND (item_id = '.(int)$item_id.' OR item_id = 0)'
			.' ORDER BY id ASC'
		)->result();
		$positions = $this->tank_positions($store_id, array_map(function($t){ return (int)$t->id; }, $tanks));
		foreach($tanks as $tank){
			$tank->book_litres = isset($positions[(int)$tank->id]) ? $positions[(int)$tank->id]['book'] : 0.0;
			$tank->headroom = max(0, (float)$tank->safe_fill_litres - $tank->book_litres);
		}
		return $tanks;
	}

	/**
	 * Book what actually arrived into a tank. Overfill protection is a refusal,
	 * not a warning: a tank that takes more than it can hold is an incident.
	 */
	public function fill_tank($store_id, $tank_id, $litres, $transfer_id = 0, $reason = '', $item_id = 0){
		$store = (int)$store_id;
		$tank = $this->tank($store, $tank_id);
		if(!$tank) return 'That tank does not belong to this company.';
		if(!$this->works_at($store, $tank->warehouse_id)) return 'That tank stands at a location you are not assigned to.';
		$litres = (float)$litres;
		if($litres <= 0) return 'Enter how many litres arrived.';

		$book = $this->tank_book_litres($tank->id);
		$limit = (float)$tank->safe_fill_litres > 0 ? (float)$tank->safe_fill_litres : (float)$tank->capacity_litres;
		if($book + $litres > $limit + 0.0001){
			return $tank->tank_name.' holds '.round($book, 1).' L and can safely take '.round(max(0, $limit - $book), 1).' L more.'
				.' Split the load or use another tank.';
		}
		$this->db->insert('db_fuel_tank_movement', array(
			'store_id'=>$store, 'tank_id'=>(int)$tank->id, 'item_id'=>(int)($item_id ?: $tank->item_id),
			'litres'=>$litres, 'movement_type'=>'fill',
			'transfer_id'=>((int)$transfer_id > 0 ? (int)$transfer_id : null),
			'reason'=>$reason === '' ? 'Load arrived' : $this->clean($reason),
			'created_by'=>$this->actor(), 'movement_date'=>date('Y-m-d'),
		));
		return 'success';
	}

	/** The fill that came from a counted-in load, if one was booked. */
	public function fill_for_transfer($store_id, $transfer_id){
		return $this->db->query('SELECT * FROM db_fuel_tank_movement WHERE store_id = '.(int)$store_id
			.' AND transfer_id = '.(int)$transfer_id.' AND movement_type = \'fill\' ORDER BY id DESC LIMIT 1')->row();
	}

	// ----------------------------------------------------------------- duties list

	/** Closed duties whose difference is bigger than the company allows. */
	public function pending_shifts($store_id, $limit = 10){
		$store = (int)$store_id;
		$ids = $this->accessible_site_ids($store);
		if(is_array($ids) && empty($ids)) return array();
		$scope = is_array($ids) ? ' AND warehouse_id IN ('.implode(',', $ids).')' : '';
		$limit = max(1, min(50, (int)$limit));
		return $this->db->query('SELECT * FROM db_fuel_shift WHERE store_id = '.$store.$scope
			." AND approval_status = 'pending' ORDER BY closed_at DESC LIMIT ".$limit)->result();
	}

	public function shifts($store_id, $from = null, $to = null, $limit = 50, $warehouse_id = 0){
		$store = (int)$store_id;
		$ids = $this->accessible_site_ids($store);
		if(is_array($ids) && empty($ids)) return array();
		$scope = is_array($ids) ? ' AND warehouse_id IN ('.implode(',', $ids).')' : '';
		if((int)$warehouse_id > 0) $scope .= ' AND warehouse_id = '.(int)$warehouse_id;
		$where = 'store_id = '.$store.$scope;
		if($from) $where .= " AND DATE(opened_at) >= '" . $this->db->escape_str($from) . "'";
		if($to) $where .= " AND DATE(opened_at) <= '" . $this->db->escape_str($to) . "'";
		$limit = max(1, min(200, (int)$limit));
		return $this->db->query('SELECT * FROM db_fuel_shift WHERE '.$where.' ORDER BY id DESC LIMIT '.$limit)->result();
	}

	/**
	 * One row per metered site for the head-office board: is a duty running, how
	 * much is waiting for a signature, how many tanks are being watched.
	 */
	public function site_forecourt_summary($store_id){
		$sites = $this->forecourt_sites($store_id);
		if(empty($sites)) return array();
		$store = (int)$store_id;
		$ids = array();
		foreach($sites as $site) $ids[] = (int)$site->id;
		$list = implode(',', $ids);

		$tanks = array();
		$rows = $this->db->query('SELECT warehouse_id, COUNT(*) AS total FROM db_fuel_tank'
			.' WHERE store_id = '.$store.' AND status = 1 AND warehouse_id IN ('.$list.') GROUP BY warehouse_id')->result();
		foreach($rows as $row) $tanks[(int)$row->warehouse_id] = (int)$row->total;

		$duties = array();
		$rows = $this->db->query('SELECT warehouse_id,'
			." SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) AS running,"
			." SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) AS waiting"
			.' FROM db_fuel_shift WHERE store_id = '.$store.' AND warehouse_id IN ('.$list.') GROUP BY warehouse_id')->result();
		foreach($rows as $row) $duties[(int)$row->warehouse_id] = $row;

		$gaps = array();
		foreach($this->open_dip_gaps($store, 50) as $gap){
			$key = (int)$gap->warehouse_id;
			$gaps[$key] = isset($gaps[$key]) ? $gaps[$key] + 1 : 1;
		}

		$summary = array();
		foreach($sites as $site){
			$key = (int)$site->id;
			$summary[$key] = array(
				'name' => $site->warehouse_name,
				'tanks' => isset($tanks[$key]) ? $tanks[$key] : 0,
				'running' => isset($duties[$key]) ? (int)$duties[$key]->running : 0,
				'waiting' => isset($duties[$key]) ? (int)$duties[$key]->waiting : 0,
				'gaps' => isset($gaps[$key]) ? $gaps[$key] : 0,
			);
		}
		return $summary;
	}

	/** Open decisions and open gaps, counted the way the screen lists them. */
	public function forecourt_counts($store_id){
		// A company with no metered site never pays for these queries.
		if(!$this->has_forecourt($store_id) && !$this->has_forecourt($store_id, 'fuel_shift_reconciliation')){
			return array('awaiting'=>0, 'gaps'=>0, 'open_duties'=>0);
		}
		$store = (int)$store_id;
		$ids = $this->accessible_site_ids($store);
		if(is_array($ids) && empty($ids)) return array('awaiting'=>0, 'gaps'=>0, 'open_duties'=>0);
		$scope = is_array($ids) ? ' AND warehouse_id IN ('.implode(',', $ids).')' : '';
		$awaiting = (int)$this->db->query('SELECT COUNT(*) AS total FROM db_fuel_shift WHERE store_id = '.$store
			.$scope.' AND approval_status = \'pending\'')->row()->total;
		$open = (int)$this->db->query('SELECT COUNT(*) AS total FROM db_fuel_shift WHERE store_id = '.$store
			.$scope.' AND status = \'open\'')->row()->total;
		return array('awaiting'=>$awaiting, 'gaps'=>count($this->open_dip_gaps($store, 50)), 'open_duties'=>$open);
	}

	// ------------------------------------------------------------------ shared

	/** Every site id the company owns, ignoring assignment — for ownership tests. */
	private function site_ids($store_id){
		$rows = $this->db->query('SELECT id FROM db_warehouse WHERE store_id = '.(int)$store_id.' AND status = 1')->result();
		return array_map(function($r){ return (int)$r->id; }, $rows);
	}

	/**
	 * Sites the user may work on: null means all of them.
	 */
	private function accessible_site_ids($store_id){
		$ids = $this->scoped_location_ids($store_id);
		if($ids === null) return null;
		return array_values(array_filter(array_map('intval', (array)$ids)));
	}

	/**
	 * May this user touch a tank at this site? Head office is scoped to nothing,
	 * so an unscoped user (null) is allowed everywhere — testing a null against
	 * in_array() would refuse the owner at their own station.
	 */
	private function works_at($store_id, $warehouse_id){
		$ids = $this->accessible_site_ids($store_id);
		if($ids === null) return true;
		return in_array((int)$warehouse_id, $ids, true);
	}

	private function item_is_yours($store_id, $item_id){
		return (int)$this->db->query('SELECT COUNT(*) AS total FROM db_items WHERE id = '.(int)$item_id
			.' AND store_id = '.(int)$store_id)->row()->total === 1;
	}

	private function actor(){
		$name = $this->session->userdata('display_name');
		return $name ? $name : (string)$this->session->userdata('inv_username');
	}

	private function clean($value){
		return $this->security ? $this->security->xss_clean(strip_tags((string)$value)) : strip_tags((string)$value);
	}

	private function l($litres){
		return rtrim(rtrim(number_format((float)$litres, 3), '0'), '.');
	}

	private function p($percent){
		return rtrim(rtrim(number_format((float)$percent, 2), '0'), '.').'%';
	}
}
