<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Oil & gas: LPG — cylinders, the weight that leaves the shop in them, and the
 * money held against them.
 *
 * The rule this model shares with the forecourt: the till already booked the
 * sale and the stock ledger already counts it, so nothing here deducts gas
 * again. A refill records what the scale said; the kilograms went out through
 * the till. The two are compared on the LPG board rather than double-counted
 * here.
 *
 * The second rule is about shells: a cylinder is the shop's property, so the
 * register is a custody book. Somebody is always answerable for a shell that is
 * not on the premises, and the deposit is the money that keeps them honest. A
 * deposit is money and never stock — no item row, no ledger row, no sale.
 */
class Fuel_lpg_model extends CI_Model {

	public function __construct(){
		parent::__construct();
		$this->load->helper('business_profile');
		$this->load->model('oil_gas_model', 'oil_gas');
	}

	// -------------------------------------------------------------- capability

	/** Sites the current user may work on. null = the whole company. */
	public function scoped_location_ids($store_id){
		return $this->oil_gas->scoped_location_ids($store_id);
	}

	public function lpg_enabled($warehouse_id, $flag = 'lpg_refills'){
		return function_exists('mp_location_feature_enabled') && mp_location_feature_enabled($flag, (int)$warehouse_id);
	}

	/** The user's sites that run LPG work — the LPG picker. */
	public function lpg_sites($store_id, $flag = 'lpg_refills'){
		$sites = array();
		foreach($this->oil_gas->locations($store_id) as $location){
			if($this->lpg_enabled((int)$location->id, $flag)) $sites[] = $location;
		}
		return $sites;
	}

	/** Sites that either fill shells or lend them — the board's picker. */
	public function worksites($store_id){
		$sites = array();
		foreach($this->oil_gas->locations($store_id) as $location){
			$id = (int)$location->id;
			if($this->lpg_enabled($id) || $this->lpg_enabled($id, 'cylinder_tracking')) $sites[] = $location;
		}
		return $sites;
	}

	public function has_lpg($store_id){
		return count($this->lpg_sites($store_id)) > 0
			|| count($this->lpg_sites($store_id, 'cylinder_tracking')) > 0;
	}

	/** Products this site can hold, so a cylinder is only ever given a real one. */
	public function items_for_site($store_id, $warehouse_id){
		return $this->db->query(
			'SELECT i.id, i.item_name AS name FROM db_items i'
			.' INNER JOIN db_warehouseitems w ON w.item_id = i.id AND w.warehouse_id = '.(int)$warehouse_id
			.' WHERE i.store_id = '.(int)$store_id
			.' GROUP BY i.id, i.item_name ORDER BY i.item_name ASC'
		)->result();
	}

	/** The customers a shell may be handed to. Bounded, because it is a picker. */
	public function customers($store_id, $limit = 200){
		return $this->db->query(
			'SELECT id, customer_name AS name FROM db_customers WHERE store_id = '.(int)$store_id
			.' AND status = 1 ORDER BY customer_name ASC LIMIT '.(int)$limit
		)->result();
	}

	// ---------------------------------------------------------------- cylinders

	public function cylinders($store_id, $statuses = null, $warehouse_ids = null){
		$ids = $warehouse_ids === null ? $this->scoped_location_ids($store_id) : $warehouse_ids;
		if(is_array($ids) && empty($ids)) return array();
		$store = (int)$store_id;
		$where = 'c.store_id = '.$store;
		if(is_array($ids)){
			$clean = array_values(array_filter(array_map('intval', $ids)));
			if(empty($clean)) return array();
			$where .= ' AND c.warehouse_id IN ('.implode(',', $clean).')';
		}
		if($statuses !== null){
			$allowed = array('in_shop','out','lost','scrapped');
			$pick = array_values(array_intersect(array_map('strval', (array)$statuses), $allowed));
			if(empty($pick)) return array();
			$where .= " AND c.status IN ('".implode("','", $pick)."')";
		}
		return $this->db->query(
			'SELECT c.*, w.warehouse_name, i.item_name AS product, cu.customer_name'
			.' FROM db_fuel_cylinder c'
			.' LEFT JOIN db_warehouse w ON w.id = c.warehouse_id'
			.' LEFT JOIN db_items i ON i.id = c.item_id'
			.' LEFT JOIN db_customers cu ON cu.id = c.customer_id'
			.' WHERE '.$where
			.' ORDER BY c.status ASC, c.cylinder_code ASC'
		)->result();
	}

	public function cylinder($store_id, $cylinder_id){
		return $this->db->query('SELECT * FROM db_fuel_cylinder WHERE id = '.(int)$cylinder_id
			.' AND store_id = '.(int)$store_id)->row();
	}

	/** A shell counted by stamp, not by row id — the number is what is on the collar. */
	public function cylinder_by_code($store_id, $code){
		$code = trim((string)$code);
		if($code === '') return null;
		return $this->db->query('SELECT * FROM db_fuel_cylinder WHERE store_id = '.(int)$store_id
			.' AND cylinder_code = '.$this->db->escape($code).' LIMIT 1')->row();
	}

	public function cylinder_counts($store_id){
		$counts = array('in_shop'=>0, 'full'=>0, 'out'=>0, 'lost'=>0, 'scrapped'=>0, 'total'=>0);
		$rows = $this->cylinders($store_id);
		foreach($rows as $cylinder){
			$counts['total']++;
			$status = (string)$cylinder->status;
			if(isset($counts[$status])) $counts[$status]++;
			if($status === 'in_shop' && (int)$cylinder->is_full === 1) $counts['full']++;
		}
		return $counts;
	}

	/**
	 * Register a shell, or correct one. The stamp is unique per company because
	 * two shells with the same number cannot be told apart once one walks out.
	 */
	public function save_cylinder($store_id, $post){
		$store = (int)$store_id;
		$id = (int)($post['cylinder_id'] ?? 0);
		$code = trim((string)($post['cylinder_code'] ?? ''));
		$existing = $id > 0 ? $this->cylinder($store, $id) : null;
		// The screen you are standing at is the site the shell belongs to. A
		// correction fixes the stamp, the size or the tare — it never re-homes a
		// shell, because the register is kept per location and a shell that walks
		// between sites does so as a hand-back at one and an issue at the other.
		$warehouse_id = $existing ? (int)$existing->warehouse_id
			: ((int)($post['warehouse_id'] ?? 0) ?: (int)($post['site_id'] ?? 0));
		$item_id = (int)($post['item_id'] ?? 0);
		$size = (float)($post['size_kg'] ?? 0);
		$tare = (float)($post['tare_kg'] ?? 0);

		if($code === '') return 'Enter the number stamped on the shell.';
		if(strlen($code) > 60) return 'That shell number is too long — enter it as it is stamped.';
		if($size <= 0) return 'Enter the size this cylinder is filled to, in kilograms.';
		if($tare <= 0) return 'Enter the empty weight stamped on the collar.';
		if(!$this->lpg_enabled($warehouse_id, 'cylinder_tracking')) return 'Cylinder tracking is not switched on for this location.';
		if(!in_array($warehouse_id, $this->site_ids($store), true)) return 'That location is not one of yours.';
		if(!$this->works_at($store, $warehouse_id)) return 'That location is not one you are assigned to.';
		if($item_id > 0 && !$this->item_is_yours($store, $item_id)) return 'That product is not one of yours.';

		$clash = $this->db->query('SELECT id FROM db_fuel_cylinder WHERE store_id = '.$store
			.' AND cylinder_code = '.$this->db->escape($code)
			.($id > 0 ? ' AND id <> '.$id : ''))->row();
		if($clash) return 'A cylinder numbered '.$this->clean($code).' is already on the register.';

		$data = array(
			'store_id'=>$store, 'warehouse_id'=>$warehouse_id, 'cylinder_code'=>$this->clean($code),
			'item_id'=>$item_id, 'size_kg'=>$size, 'tare_kg'=>$tare,
			'note'=>isset($post['note']) ? $this->clean($post['note']) : null,
		);
		// A shell can arrive already filled — a relay depot lends shells the
		// bottling plant weighed, so being able to say "this one is full" without
		// a weighing of its own is the only way it can ever go out. Saying nothing
		// means empty, which is the safe way to be wrong.
		$data['is_full'] = (isset($post['is_full']) && (int)$post['is_full'] === 1) ? 1 : 0;
		if($id > 0){
			if(!$existing) return 'That cylinder does not belong to this company.';
			$this->db->where('id', $id)->where('store_id', $store)->update('db_fuel_cylinder', $data);
			$this->log_movement($store, array('cylinder_id'=>$id, 'warehouse_id'=>$warehouse_id,
				'movement_type'=>'registered', 'reason'=>'The shell was corrected'));
			return 'success';
		}
		$data['status'] = 'in_shop';
		$data['customer_id'] = 0;
		$data['created_by'] = $this->actor();
		$data['created_date'] = date('Y-m-d');
		$data['last_movement_at'] = date('Y-m-d H:i:s');
		$this->db->insert('db_fuel_cylinder', $data);
		$new_id = (int)$this->db->insert_id();
		$this->log_movement($store, array('cylinder_id'=>$new_id, 'warehouse_id'=>$warehouse_id,
			'movement_type'=>'registered', 'to_status'=>'in_shop', 'reason'=>'The shell was put on the register'));
		return $new_id;
	}

	// ----------------------------------------------------------------- refills

	/**
	 * Weigh a fill. Gross comes from the scale, the tare comes from the register —
	 * never from the person at the scale, because that is the figure that decides
	 * how much gas the customer is charged for.
	 */
	public function refill($store_id, $post){
		$store = (int)$store_id;
		$cylinder_id = (int)($post['cylinder_id'] ?? 0);
		$gross = (float)($post['gross_kg'] ?? 0);
		$reference = trim((string)($post['sale_reference'] ?? ''));

		$cylinder = $this->cylinder($store, $cylinder_id);
		if(!$cylinder) return 'That cylinder does not belong to this company.';
		// The scale is at the counter you are standing at, not necessarily where the
		// shell sleeps. Weighing a plant's shell at the highway counter is normal
		// work, so the site that gets credited — and checked against your
		// assignments — is the screen you used, falling back to the shell's home.
		$warehouse_id = (int)($post['warehouse_id'] ?? 0) ?: (int)($post['site_id'] ?? 0)
			?: (int)$cylinder->warehouse_id;
		if(!$this->lpg_enabled($warehouse_id)) return 'Refilling is not switched on for this location.';
		if(!$this->works_at($store, $warehouse_id)) return 'That work is at a location you are not assigned to.';
		if($cylinder->status === 'out') return 'That cylinder is out with a customer. Take it back before refilling it.';
		if($cylinder->status === 'lost') return 'That cylinder is written off as lost, so it cannot be refilled.';
		if($cylinder->status === 'scrapped') return 'That cylinder is off the road, so it cannot be refilled.';
		if($gross <= 0) return 'Enter what the filled cylinder weighed.';
		$tare = (float)$cylinder->tare_kg;
		if($tare <= 0) return 'This cylinder has no empty weight on the register, so its fill cannot be worked out.';
		$net = round($gross - $tare, 4);
		if($net <= 0) return 'The weight on the scale is below the cylinder\'s own weight. Check the reading.';
		if((float)$cylinder->size_kg > 0 && $net > (float)$cylinder->size_kg + 0.0001){
			return 'That is '.rtrim(rtrim(number_format($net - (float)$cylinder->size_kg, 3),'0'),'.')
				.' kg more than this cylinder is filled to. Do not overfill it.';
		}

		$sale_id = $reference !== '' ? $this->find_sale_by_reference($store, $warehouse_id, $reference) : 0;
		$data = array(
			'store_id'=>$store, 'warehouse_id'=>$warehouse_id, 'cylinder_id'=>$cylinder_id,
			'item_id'=>(int)$cylinder->item_id, 'gross_kg'=>$gross, 'tare_kg'=>$tare, 'net_kg'=>$net,
			'refill_date'=>date('Y-m-d'), 'refill_time'=>date('H:i'),
			'sale_reference'=>$reference !== '' ? $this->clean($reference) : null,
			'linked_sale_id'=>$sale_id > 0 ? $sale_id : null,
			'note'=>isset($post['note']) ? $this->clean($post['note']) : null,
			'created_by'=>$this->actor(), 'created_at'=>date('Y-m-d H:i:s'),
		);
		$this->db->insert('db_fuel_lpg_refill', $data);
		$refill_id = (int)$this->db->insert_id();

		$this->db->where('id', $cylinder_id)->where('store_id', $store)->update('db_fuel_cylinder', array(
			'is_full'=>1, 'last_movement_at'=>date('Y-m-d H:i:s')));
		$this->log_movement($store, array('cylinder_id'=>$cylinder_id, 'warehouse_id'=>$warehouse_id,
			'movement_type'=>'refilled', 'net_kg'=>$net, 'refill_id'=>$refill_id,
			'reason'=>'Refilled with '.$this->kg($net).' kg'));
		return $refill_id;
	}

	public function refills($store_id, $from = null, $to = null, $limit = 50, $warehouse_id = 0){
		$store = (int)$store_id;
		$ids = $this->scoped_location_ids($store);
		$where = 'r.store_id = '.$store;
		if(is_array($ids)){
			if(empty($ids)) return array();
			$where .= ' AND r.warehouse_id IN ('.implode(',', array_map('intval', $ids)).')';
		}
		if((int)$warehouse_id > 0) $where .= ' AND r.warehouse_id = '.(int)$warehouse_id;
		if($from) $where .= ' AND r.refill_date >= '.$this->db->escape((string)$from);
		if($to) $where .= ' AND r.refill_date <= '.$this->db->escape((string)$to);
		return $this->db->query(
			'SELECT r.*, c.cylinder_code, c.size_kg, w.warehouse_name'
			.' FROM db_fuel_lpg_refill r'
			.' LEFT JOIN db_fuel_cylinder c ON c.id = r.cylinder_id'
			.' LEFT JOIN db_warehouse w ON w.id = r.warehouse_id'
			.' WHERE '.$where.' ORDER BY r.id DESC LIMIT '.(int)$limit
		)->result();
	}

	/** Kilograms of gas put into shells over a window, on the sites asked for. */
	public function refilled_kg($store_id, $from, $to, $warehouse_ids = null){
		$ids = $warehouse_ids === null ? $this->scoped_location_ids($store_id) : $warehouse_ids;
		if(is_array($ids) && empty($ids)) return 0.0;
		$scope = '';
		if(is_array($ids)){
			$clean = array_values(array_filter(array_map('intval', $ids)));
			if(empty($clean)) return 0.0;
			$scope = ' AND warehouse_id IN ('.implode(',', $clean).')';
		}
		$row = $this->db->query('SELECT COALESCE(SUM(net_kg),0) AS kg, COUNT(*) AS fills FROM db_fuel_lpg_refill'
			.' WHERE store_id = '.(int)$store_id.$scope
			.' AND refill_date BETWEEN '.$this->db->escape((string)$from).' AND '.$this->db->escape((string)$to))->row();
		return (float)$row->kg;
	}

	/**
	 * Kilograms the till rang up over the same window. Compared with the scale,
	 * not added to it: the till is what actually took the gas out of stock.
	 */
	public function sold_kg($store_id, $warehouse_ids, $item_ids, $from, $to){
		$clean = array_values(array_filter(array_map('intval', (array)$item_ids)));
		if(empty($clean)) return 0.0;
		$ids = $warehouse_ids === null ? $this->scoped_location_ids($store_id) : $warehouse_ids;
		if(is_array($ids) && empty($ids)) return 0.0;
		$site = '';
		if(is_array($ids)){
			$sites = array_values(array_filter(array_map('intval', $ids)));
			if(empty($sites)) return 0.0;
			$site = ' AND b.warehouse_id IN ('.implode(',', $sites).')';
		}
		$list = implode(',', $clean);
		$from_ts = $this->db->escape((string)$from);
		$to_ts = $this->db->escape((string)$to);

		$sold = $this->db->query(
			'SELECT COALESCE(SUM(COALESCE(a.base_unit_qty, a.sales_qty)),0) AS kg'
			.' FROM db_salesitems a INNER JOIN db_sales b ON b.id = a.sales_id'
			.' WHERE b.store_id = '.(int)$store_id.' AND a.item_id IN ('.$list.')'
			." AND (b.sales_status IS NULL OR b.sales_status NOT IN ('Cancelled','Opening'))"
			.' AND (b.voided_at IS NULL)'
			.' AND TIMESTAMP(b.sales_date, b.created_time) BETWEEN '.$from_ts.' AND '.$to_ts.$site
		)->row();

		$back = $this->db->query(
			'SELECT COALESCE(SUM(COALESCE(a.base_unit_qty, a.return_qty)),0) AS kg'
			.' FROM db_salesitemsreturn a INNER JOIN db_salesreturn b ON b.id = a.return_id'
			.' WHERE b.store_id = '.(int)$store_id.' AND a.item_id IN ('.$list.')'
			." AND (b.return_status IS NULL OR b.return_status NOT IN ('Cancelled','Opening'))"
			.' AND TIMESTAMP(b.return_date, b.created_time) BETWEEN '.$from_ts.' AND '.$to_ts.$site
		)->row();

		return (float)$sold->kg - (float)$back->kg;
	}

	/** The products that go into shells — the ones the till must have rung up. */
	public function cylinder_item_ids($store_id){
		$rows = $this->db->query('SELECT DISTINCT item_id FROM db_fuel_cylinder WHERE store_id = '.(int)$store_id
			.' AND item_id > 0')->result();
		return array_map(function($r){ return (int)$r->item_id; }, $rows);
	}

	/**
	 * Fills the till has never seen. A refill with no receipt number, or with a
	 * number that matches nothing, is gas that left the bulk without being paid for.
	 */
	public function unreconciled_refills($store_id, $days = 7, $limit = 25){
		$ids = $this->scoped_location_ids($store_id);
		if(is_array($ids) && empty($ids)) return array();
		$scope = '';
		if(is_array($ids)){
			$clean = array_values(array_filter(array_map('intval', $ids)));
			if(empty($clean)) return array();
			$scope = ' AND r.warehouse_id IN ('.implode(',', $clean).')';
		}
		$since = date('Y-m-d', strtotime('-'.(int)$days.' days'));
		return $this->db->query(
			'SELECT r.id, r.cylinder_id, r.net_kg, r.refill_date, r.sale_reference, r.linked_sale_id,'
			.' c.cylinder_code, w.warehouse_name'
			.' FROM db_fuel_lpg_refill r'
			.' LEFT JOIN db_fuel_cylinder c ON c.id = r.cylinder_id'
			.' LEFT JOIN db_warehouse w ON w.id = r.warehouse_id'
			.' WHERE r.store_id = '.(int)$store_id.$scope
			.' AND r.refill_date >= '.$this->db->escape($since)
			.' AND (r.linked_sale_id IS NULL)'
			.' ORDER BY r.id DESC LIMIT '.(int)$limit
		)->result();
	}

	// ----------------------------------------------------------------- custody

	/** Hand a full shell to a customer. The shop still owns it; they now hold it. */
	public function issue($store_id, $cylinder_id, $customer_id, $note = '', $warehouse_id = 0){
		$store = (int)$store_id;
		$cylinder = $this->cylinder($store, (int)$cylinder_id);
		if(!$cylinder) return 'That cylinder does not belong to this company.';
		if(!$this->works_at($store, (int)$cylinder->warehouse_id)) return 'That cylinder stands at a location you are not assigned to.';
		if($cylinder->status !== 'in_shop') return 'That cylinder is already out with a customer.';
		if((int)$cylinder->is_full !== 1) return 'That cylinder is empty. Refill it before handing it out.';
		$customer_id = (int)$customer_id;
		if($customer_id <= 0) return 'Say who is taking the cylinder.';
		if(!$this->customer_is_yours($store, $customer_id)) return 'That customer is not one of yours.';

		$this->db->where('id', (int)$cylinder_id)->where('store_id', $store)->update('db_fuel_cylinder', array(
			'status'=>'out', 'customer_id'=>$customer_id, 'last_movement_at'=>date('Y-m-d H:i:s')));
		$this->log_movement($store, array('cylinder_id'=>(int)$cylinder_id,
			'warehouse_id'=>(int)($warehouse_id ?: $cylinder->warehouse_id), 'movement_type'=>'issued',
			'customer_id'=>$customer_id, 'from_status'=>'in_shop', 'to_status'=>'out',
			'reason'=>$this->clean($note) ?: 'Handed to the customer'));
		return 'success';
	}

	/** A shell comes home. It is assumed empty — refilling it is a separate act. */
	public function take_back($store_id, $cylinder_id, $note = ''){
		$store = (int)$store_id;
		$cylinder = $this->cylinder($store, (int)$cylinder_id);
		if(!$cylinder) return 'That cylinder does not belong to this company.';
		if(!$this->works_at($store, (int)$cylinder->warehouse_id)) return 'That cylinder stands at a location you are not assigned to.';
		if($cylinder->status !== 'out') return 'That cylinder is already in the shop.';

		$this->db->where('id', (int)$cylinder_id)->where('store_id', $store)->update('db_fuel_cylinder', array(
			'status'=>'in_shop', 'customer_id'=>0, 'is_full'=>0, 'last_movement_at'=>date('Y-m-d H:i:s')));
		$this->log_movement($store, array('cylinder_id'=>(int)$cylinder_id,
			'warehouse_id'=>(int)$cylinder->warehouse_id, 'movement_type'=>'returned',
			'customer_id'=>(int)$cylinder->customer_id, 'from_status'=>'out', 'to_status'=>'in_shop',
			'reason'=>$this->clean($note) ?: 'Brought back'));
		return 'success';
	}

	/**
	 * Stop being able to hand a shell back: it is lost or scrapped. Both need a
	 * reason, because the deposit against it stays owed to somebody either way.
	 */
	public function write_off_cylinder($store_id, $cylinder_id, $kind, $note){
		$store = (int)$store_id;
		if(!in_array($kind, array('lost','scrapped'), true)) return 'Unknown cylinder action.';
		$note = trim((string)$note);
		if($note === '') return 'Say what happened before writing this cylinder off.';
		$cylinder = $this->cylinder($store, (int)$cylinder_id);
		if(!$cylinder) return 'That cylinder does not belong to this company.';
		if(!$this->works_at($store, (int)$cylinder->warehouse_id)) return 'That cylinder stands at a location you are not assigned to.';
		if($cylinder->status === 'scrapped') return 'This cylinder is already off the road.';
		if($cylinder->status === 'lost') return 'This cylinder is already written off as lost.';

		$this->db->where('id', (int)$cylinder_id)->where('store_id', $store)->update('db_fuel_cylinder', array(
			'status'=>$kind, 'is_full'=>0, 'last_movement_at'=>date('Y-m-d H:i:s')));
		$this->log_movement($store, array('cylinder_id'=>(int)$cylinder_id,
			'warehouse_id'=>(int)$cylinder->warehouse_id, 'movement_type'=>$kind,
			'customer_id'=>(int)$cylinder->customer_id ? (int)$cylinder->customer_id : null,
			'from_status'=>$cylinder->status, 'to_status'=>$kind, 'reason'=>$this->clean($note)));
		return 'success';
	}

	/**
	 * One swap, two lines: a full shell goes out and an empty one comes in, to the
	 * same customer. Doing it as one action is what keeps the register counting —
	 * two separate steps are how a shell goes missing.
	 */
	public function exchange($store_id, $given_id, $taken_id, $customer_id, $note = ''){
		$store = (int)$store_id;
		$given = $this->cylinder($store, (int)$given_id);
		$taken = $this->cylinder($store, (int)$taken_id);
		if(!$given || !$taken) return 'One of those cylinders does not belong to this company.';
		if((int)$given->id === (int)$taken->id) return 'The cylinder going out and the one coming back cannot be the same shell.';
		if(!$this->works_at($store, (int)$given->warehouse_id)) return 'That cylinder stands at a location you are not assigned to.';
		if($given->status !== 'in_shop') return 'The cylinder going out is already out with a customer.';
		if((int)$given->is_full !== 1) return 'Give out a cylinder that has been refilled.';
		if($taken->status !== 'out') return 'The cylinder coming back is already in the shop.';
		$customer_id = (int)$customer_id;
		if($customer_id <= 0) return 'Say who is swapping cylinders.';
		if(!$this->customer_is_yours($store, $customer_id)) return 'That customer is not one of yours.';
		if((int)$taken->customer_id !== $customer_id) {
			return 'The cylinder coming back is out at someone else, so it is not this customer\'s to return.';
		}

		$this->db->where('id', (int)$given->id)->where('store_id', $store)->update('db_fuel_cylinder', array(
			'status'=>'out', 'customer_id'=>$customer_id, 'last_movement_at'=>date('Y-m-d H:i:s')));
		$this->db->where('id', (int)$taken->id)->where('store_id', $store)->update('db_fuel_cylinder', array(
			'status'=>'in_shop', 'customer_id'=>0, 'is_full'=>0, 'last_movement_at'=>date('Y-m-d H:i:s')));

		$reason = $this->clean($note) ?: 'Exchanged with the same customer';
		$this->log_movement($store, array('cylinder_id'=>(int)$given->id, 'warehouse_id'=>(int)$given->warehouse_id,
			'movement_type'=>'given', 'customer_id'=>$customer_id, 'from_status'=>'in_shop', 'to_status'=>'out',
			'reason'=>$reason.' — '.$this->clean($given->cylinder_code)));
		$this->log_movement($store, array('cylinder_id'=>(int)$taken->id, 'warehouse_id'=>(int)$taken->warehouse_id,
			'movement_type'=>'received', 'customer_id'=>$customer_id, 'from_status'=>'out', 'to_status'=>'in_shop',
			'reason'=>$reason.' — '.$this->clean($taken->cylinder_code)));
		return 'success';
	}

	// ---------------------------------------------------------------- deposits

	/**
	 * Money held against a shell. A deposit follows the cylinder, not the sale:
	 * taken when a full shell goes out, paid back when the shell comes home.
	 */
	public function record_deposit($store_id, $post){
		$store = (int)$store_id;
		$cylinder_id = (int)($post['cylinder_id'] ?? 0);
		$customer_id = (int)($post['customer_id'] ?? 0);
		$amount = round((float)($post['amount'] ?? 0), 2);
		$event = ($post['event_type'] ?? 'paid') === 'refunded' ? 'refunded' : 'paid';

		$cylinder = $this->cylinder($store, $cylinder_id);
		if(!$cylinder) return 'That cylinder does not belong to this company.';
		if(!$this->works_at($store, (int)$cylinder->warehouse_id)) return 'That cylinder stands at a location you are not assigned to.';
		if($amount <= 0) return 'Enter the amount of money held.';
		if($customer_id <= 0) return 'Say whose money this is.';
		if(!$this->customer_is_yours($store, $customer_id)) return 'That customer is not one of yours.';

		$held = $this->deposit_balance($store, $cylinder_id);
		if($event === 'paid'){
			if($cylinder->status !== 'out') return 'Take the deposit when the cylinder goes out — it is in the shop now.';
			if((int)$cylinder->customer_id !== $customer_id) return 'That cylinder is not out with this customer.';
			if($held > 0) return 'This cylinder already has a deposit held against it. Refund that one first if you mean to replace it.';
		}else{
			if($cylinder->status !== 'in_shop') return 'Take the cylinder back before refunding its deposit.';
			if($held <= 0) return 'There is nothing held against this cylinder to refund.';
			if($amount > $held) return 'You are refunding more than the deposit still held against this cylinder.';
			if($this->deposit_payer($store, $cylinder_id) !== $customer_id) return 'This deposit was taken from a different customer.';
		}

		$this->db->insert('db_fuel_cylinder_deposit', array(
			'store_id'=>$store, 'warehouse_id'=>(int)$cylinder->warehouse_id, 'cylinder_id'=>$cylinder_id,
			'customer_id'=>$customer_id, 'amount'=>$amount, 'event_type'=>$event,
			'payment_mode'=>isset($post['payment_mode']) ? $this->clean($post['payment_mode']) : null,
			'note'=>isset($post['note']) ? $this->clean($post['note']) : null,
			'deposit_date'=>date('Y-m-d'), 'created_by'=>$this->actor(), 'created_at'=>date('Y-m-d H:i:s'),
		));
		$deposit_id = (int)$this->db->insert_id();
		$this->log_movement($store, array('cylinder_id'=>$cylinder_id,
			'warehouse_id'=>(int)$cylinder->warehouse_id,
			'movement_type'=>$event === 'paid' ? 'deposit_paid' : 'deposit_refunded',
			'customer_id'=>$customer_id, 'deposit_id'=>$deposit_id,
			'reason'=>($event === 'paid' ? 'Deposit taken against this shell' : 'Deposit refunded to the customer')));
		return 'success';
	}

	/** What is still held against one shell. */
	public function deposit_balance($store_id, $cylinder_id){
		$row = $this->db->query(
			'SELECT COALESCE(SUM(CASE WHEN event_type = \'paid\' THEN amount ELSE -amount END),0) AS held'
			.' FROM db_fuel_cylinder_deposit WHERE store_id = '.(int)$store_id
			.' AND cylinder_id = '.(int)$cylinder_id)->row();
		return round((float)$row->held, 2);
	}

	/** Who paid it, so a refund can only go back to that person. */
	public function deposit_payer($store_id, $cylinder_id){
		$rows = $this->db->query('SELECT customer_id,'
			.' SUM(CASE WHEN event_type = \'paid\' THEN amount ELSE -amount END) AS held'
			.' FROM db_fuel_cylinder_deposit WHERE store_id = '.(int)$store_id
			.' AND cylinder_id = '.(int)$cylinder_id.' GROUP BY customer_id HAVING held > 0 ORDER BY held DESC LIMIT 1')->row();
		return $rows ? (int)$rows->customer_id : 0;
	}

	/** The money the shop owes its customers right now, per cylinder. */
	public function open_deposits($store_id, $limit = 50){
		$ids = $this->scoped_location_ids($store_id);
		$where = 'd.store_id = '.(int)$store_id;
		if(is_array($ids)){
			if(empty($ids)) return array();
			$where .= ' AND d.warehouse_id IN ('.implode(',', array_map('intval', $ids)).')';
		}
		return $this->db->query(
			'SELECT d.cylinder_id, d.customer_id, cu.customer_name, c.cylinder_code, w.warehouse_name, c.status,'
			.' SUM(CASE WHEN d.event_type = \'paid\' THEN d.amount ELSE -d.amount END) AS held'
			.' FROM db_fuel_cylinder_deposit d'
			.' LEFT JOIN db_customers cu ON cu.id = d.customer_id'
			.' LEFT JOIN db_fuel_cylinder c ON c.id = d.cylinder_id'
			.' LEFT JOIN db_warehouse w ON w.id = d.warehouse_id'
			.' WHERE '.$where.' GROUP BY d.cylinder_id, d.customer_id, cu.customer_name, c.cylinder_code,'
			.' w.warehouse_name, c.status HAVING held > 0.004 ORDER BY held DESC LIMIT '.(int)$limit
		)->result();
	}

	public function deposits_owed($store_id){
		$total = 0.0;
		foreach($this->open_deposits($store_id, 500) as $row) $total += (float)$row->held;
		return round($total, 2);
	}

	public function deposits($store_id, $limit = 50){
		$ids = $this->scoped_location_ids($store_id);
		$where = 'dp.store_id = '.(int)$store_id;
		if(is_array($ids)){
			if(empty($ids)) return array();
			$where .= ' AND dp.warehouse_id IN ('.implode(',', array_map('intval', $ids)).')';
		}
		return $this->db->query(
			'SELECT dp.*, c.cylinder_code, cu.customer_name, w.warehouse_name'
			.' FROM db_fuel_cylinder_deposit dp'
			.' LEFT JOIN db_fuel_cylinder c ON c.id = dp.cylinder_id'
			.' LEFT JOIN db_customers cu ON cu.id = dp.customer_id'
			.' LEFT JOIN db_warehouse w ON w.id = dp.warehouse_id'
			.' WHERE '.$where.' ORDER BY dp.id DESC LIMIT '.(int)$limit
		)->result();
	}

	// ------------------------------------------------------------------ boards

	public function movement_history($store_id, $cylinder_id, $limit = 30){
		return $this->db->query(
			'SELECT m.*, cu.customer_name, w.warehouse_name FROM db_fuel_cylinder_movement m'
			.' LEFT JOIN db_customers cu ON cu.id = m.customer_id'
			.' LEFT JOIN db_warehouse w ON w.id = m.warehouse_id'
			.' WHERE m.store_id = '.(int)$store_id.' AND m.cylinder_id = '.(int)$cylinder_id
			.' ORDER BY m.id DESC LIMIT '.(int)$limit
		)->result();
	}

	/** Numbers the workspace and the LPG board show. */
	public function lpg_counts($store_id){
		if(!$this->has_lpg($store_id)) return array('shells'=>0, 'full'=>0, 'out'=>0, 'waiting'=>0, 'deposits'=>0.0);
		$counts = $this->cylinder_counts($store_id);
		return array(
			'shells'=>(int)$counts['total'],
			'full'=>(int)$counts['full'],
			'out'=>(int)$counts['out'],
			'waiting'=>count($this->unreconciled_refills($store_id, 7, 100)),
			'deposits'=>$this->deposits_owed($store_id),
		);
	}

	/** One row per site that runs LPG, for the workspace cards. */
	public function site_lpg_summary($store_id){
		$sites = array();
		foreach($this->oil_gas->locations($store_id) as $location){
			$id = (int)$location->id;
			$refills = $this->lpg_enabled($id);
			$register = $this->lpg_enabled($id, 'cylinder_tracking');
			if(!$refills && !$register) continue;
			$shells = $this->cylinders($store_id, null, array($id));
			$full = 0; $out = 0;
			foreach($shells as $cylinder){
				if($cylinder->status === 'out') $out++;
				elseif($cylinder->status === 'in_shop' && (int)$cylinder->is_full === 1) $full++;
			}
			$today = $this->refilled_kg($store_id, date('Y-m-d'), date('Y-m-d'), array($id));
			$sites[$id] = array(
				'name'=>$location->warehouse_name,
				'shells'=>count($shells), 'full'=>$full, 'out'=>$out,
				'today_kg'=>$today,
				'refills'=>$refills, 'tracking'=>$register,
			);
		}
		return $sites;
	}

	// ------------------------------------------------------------------ shared

	private function find_sale_by_reference($store_id, $warehouse_id, $reference){
		$row = $this->db->query('SELECT id FROM db_sales WHERE store_id = '.(int)$store_id
			.' AND warehouse_id = '.(int)$warehouse_id
			.' AND reference_no = '.$this->db->escape($reference)
			.' ORDER BY id DESC LIMIT 1')->row();
		return $row ? (int)$row->id : 0;
	}

	private function log_movement($store_id, $data){
		if(!isset($data['store_id'])) $data['store_id'] = (int)$store_id;
		$data['created_by'] = $this->actor();
		$data['created_at'] = date('Y-m-d H:i:s');
		$this->db->insert('db_fuel_cylinder_movement', $data);
		return (int)$this->db->insert_id();
	}

	/** Every site id the company owns, ignoring assignment — for ownership tests. */
	private function site_ids($store_id){
		$rows = $this->db->query('SELECT id FROM db_warehouse WHERE store_id = '.(int)$store_id.' AND status = 1')->result();
		return array_map(function($r){ return (int)$r->id; }, $rows);
	}

	/** Sites the user may work on: null means all of them. */
	private function accessible_site_ids($store_id){
		$ids = $this->scoped_location_ids($store_id);
		if($ids === null) return null;
		return array_values(array_filter(array_map('intval', (array)$ids)));
	}

	/**
	 * May this user touch a shell at this site? Head office is scoped to nothing,
	 * so an unscoped user (null) is allowed everywhere — testing a null against
	 * in_array() would refuse the owner at their own depot.
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

	private function customer_is_yours($store_id, $customer_id){
		return (int)$this->db->query('SELECT COUNT(*) AS total FROM db_customers WHERE id = '.(int)$customer_id
			.' AND store_id = '.(int)$store_id)->row()->total === 1;
	}

	private function actor(){
		$name = $this->session->userdata('display_name');
		return $name ? $name : (string)$this->session->userdata('inv_username');
	}

	private function clean($value){
		return $this->security ? $this->security->xss_clean(strip_tags((string)$value)) : strip_tags((string)$value);
	}

	private function kg($weight){
		return rtrim(rtrim(number_format((float)$weight, 3), '0'), '.');
	}
}
