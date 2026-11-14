<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Batch ledger reports — what the shop holds by batch, when each batch runs
 * out, which batch each sale came from, and how long the unsold ones have
 * been sitting.
 *
 * Every number here is read from db_item_barcodes (or the allocation rows the
 * till and the online store write into it), never from an item's stock figure,
 * and every "can this be sold today" answer comes from
 * Inventory_rotation_model::lot_rules() — the same rules the counter spends.
 * A report that disagreed with the till would be worse than no report, so
 * there is deliberately no second definition of a sellable lot in this file.
 */
class Pharmacy_batch_report_model extends CI_Model {

	const HOLD = 'hold';
	const EXPIRING = 'expiring';
	const SOLD = 'sold';
	const AGEING = 'ageing';

	/** Lot age buckets, in days since the batch arrived on the shelf. */
	const BUCKETS = [
		'ninety'    => ['label' => 'Up to 3 months',   'max' => 90],
		'onetwo'    => ['label' => '3 to 6 months',    'max' => 180],
		'twelve'    => ['label' => '6 to 12 months',   'max' => 365],
		'oversix'   => ['label' => 'Over a year',      'max' => null],
		'unrecorded' => ['label' => 'Arrival date not recorded', 'max' => null],
	];

	public function tab($tab) {
		$tab = strtolower(trim((string)$tab));
		return in_array($tab, [self::HOLD, self::EXPIRING, self::SOLD, self::AGEING], true) ? $tab : self::HOLD;
	}

	/**
	 * Can this report run at all? A server that has not applied the batch
	 * migration has no ledger to read, and the screen must say so rather than
	 * show an empty table that looks like an empty shelf.
	 */
	public function ready($tab = self::HOLD) {
		if (!$this->db->table_exists('db_item_barcodes')) return false;
		if ($this->tab($tab) === self::SOLD) return $this->db->table_exists('db_salesitem_batch_allocations');
		return true;
	}

	/** The locations this shop keeps stock in — the same list the reconciliation screen uses. */
	public function locations($storeId) {
		$this->load->model('batch_reconciliation_model');
		return $this->batch_reconciliation_model->warehouses((int)$storeId);
	}

	public function in_scope_warehouse($storeId, $warehouseId) {
		foreach ($this->locations($storeId) as $row) {
			if ((int)$row->id === (int)$warehouseId) return true;
		}
		return false;
	}

	private function _warehouseIds($storeId) {
		return array_map(function ($row) { return (int)$row->id; }, $this->locations($storeId));
	}

	/**
	 * How far ahead the pharmacist wants to see, taken from their own expiry
	 * settings so the report answers the question the shop already configured.
	 */
	public function horizonDays($storeId) {
		$days = 30;
		if ($this->db->table_exists('db_expiry_settings')) {
			$this->load->model('expiry_settings_model');
			$settings = $this->expiry_settings_model->get_settings((int)$storeId);
			$days = (int)($settings->alert_before_days ?? 30);
		}
		return min(3650, max(1, $days));
	}

	public function rotationMethod($storeId) {
		$this->load->model('inventory_rotation_model');
		return $this->inventory_rotation_model->method((int)$storeId);
	}

	private function _lotRules($storeId) {
		$this->load->model('inventory_rotation_model');
		// every report here reads the ledger as `b`, and db_items shares column
		// names with it, so the rules are always taken in their qualified form.
		return $this->inventory_rotation_model->lot_rules((int)$storeId, 'b');
	}

	/**
	 * The filters every tab understands, cleaned once. Nothing here trusts the
	 * query string: an unknown location falls back to the whole shop, a
	 * nonsense horizon falls back to the store's own setting, a page of "-3" is
	 * page 1.
	 */
	public function filters($storeId, array $opts) {
		$warehouseId = (int)($opts['warehouse'] ?? 0);
		if ($warehouseId > 0 && !$this->in_scope_warehouse($storeId, $warehouseId)) $warehouseId = 0;
		$page = max(1, (int)($opts['page'] ?? 1));
		$perPage = 25;
		$horizon = (int)($opts['horizon'] ?? 0);
		if ($horizon < 1 || $horizon > 3650) $horizon = $this->horizonDays($storeId);
		$bucket = strtolower(trim((string)($opts['bucket'] ?? '')));
		if (!array_key_exists($bucket, self::BUCKETS)) $bucket = '';
		$from = $this->_date($opts['from'] ?? '', date('Y-m-d', strtotime('-30 days')));
		$to = $this->_date($opts['to'] ?? '', date('Y-m-d'));
		if ($from > $to) { $swap = $to; $to = $from; $from = $swap; }
		return [
			'warehouse' => $warehouseId,
			'page' => $page,
			'per_page' => $perPage,
			'offset' => ($page - 1) * $perPage,
			'search' => trim(strip_tags((string)($opts['q'] ?? ''))),
			'horizon' => $horizon,
			'bucket' => $bucket,
			'from' => $from,
			'to' => $to,
			'show' => (string)($opts['show'] ?? '') === 'all' ? 'all' : 'active',
		];
	}

	private function _date($value, $fallback) {
		$value = trim((string)$value);
		if ($value === '') return $fallback;
		if (function_exists('is_valid_date') && !is_valid_date($value)) return $fallback;
		$stamp = strtotime(function_exists('system_fromatted_date') ? system_fromatted_date($value) : $value);
		return $stamp === false ? $fallback : date('Y-m-d', $stamp);
	}

	/**
	 * The one batch rule the whole screen is built on, as a WHERE fragment.
	 * $warehouse 0 means every location this shop owns.
	 */
	private function _lotScope($storeId, $warehouseId) {
		$ids = $this->_warehouseIds($storeId);
		if (!$ids) return '';
		if ($warehouseId > 0) return 'b.warehouse_id = ' . (int)$warehouseId;
		return 'b.warehouse_id IN (' . implode(',', array_map('intval', $ids)) . ')';
	}

	private function _searchSql($search, $columns) {
		$escaped = $this->db->escape('%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search) . '%');
		$parts = [];
		foreach ($columns as $column) $parts[] = $column . ' LIKE ' . $escaped;
		return '(' . implode(' OR ', $parts) . ')';
	}

	/**
	 * Why a batch cannot be sold right now, in the words a pharmacist would
	 * use. The yes/no answer comes from the counter's own rule; this only
	 * explains it, and it explains it from the same fields that rule reads.
	 */
	public function held_reason($row, $blocksExpired, $today) {
		if ((int)($row->sellable ?? 0) === 1) return '';
		if ((int)($row->status ?? 0) === 3) return 'Held under a recall';
		if ((float)($row->qty ?? 0) <= 0) return 'Nothing left on this batch';
		if ((int)($row->status ?? 1) === 0) return 'Closed by the shop';
		if ((int)($row->status ?? 1) === 2) return 'Awaiting a return check';
		$expire = trim((string)($row->expire_date ?? ''));
		if ($expire !== '' && $expire !== '0000-00-00' && $blocksExpired && $expire < $today) {
			return 'Past its expiry date';
		}
		return 'Not sellable right now';
	}

	/**
	 * Every batch on the shelf, with what can still be sold from it.
	 * $filters['show'] 'all' also lists batches that are used up.
	 */
	public function lot_stock($storeId, array $filters) {
		$storeId = (int)$storeId;
		$scope = $this->_lotScope($storeId, (int)$filters['warehouse']);
		if (!$scope) return ['rows' => [], 'total' => 0, 'totals' => $this->_emptyTotals()];
		$rules = $this->_lotRules($storeId);

		$where = $scope . " AND {$rules['real']}";
		if ((string)$filters['show'] !== 'all') $where .= ' AND b.qty > 0';
		if ((string)$filters['search'] !== '') $where .= ' AND ' . $this->_searchSql($filters['search'], ['i.item_name', 'i.item_code', 'b.batch_lot']);

		$purchaseJoin = '';
		$originalQty = 'b.qty';
		if ($this->db->field_exists('purchaseitems_id', 'db_item_barcodes') && $this->db->field_exists('received_qty', 'db_purchaseitems')) {
			$purchaseJoin = " LEFT JOIN db_purchaseitems pi ON pi.id = b.purchaseitems_id AND pi.item_id = b.item_id AND pi.store_id = " . $storeId;
			$originalQty = 'COALESCE(pi.received_qty, b.qty)';
		}
		$sql = "SELECT b.id, b.item_id, b.warehouse_id, b.batch_lot, b.expire_date, b.qty, b.status, " . $originalQty . " AS original_qty,"
			. " b.purchase_price, b.created_date, i.item_name, i.item_code, w.warehouse_name,"
			. " CASE WHEN {$rules['eligible']} THEN 1 ELSE 0 END AS sellable,"
			. " (b.qty * COALESCE(b.purchase_price, 0)) AS cost_value"
			. " FROM db_item_barcodes b"
			. " JOIN db_items i ON i.id = b.item_id AND i.store_id = " . $storeId
			. " LEFT JOIN db_warehouse w ON w.id = b.warehouse_id"
			. $purchaseJoin
			. " WHERE " . $where
			. " ORDER BY (b.qty <= 0) ASC, b.expire_date IS NULL, b.expire_date ASC, b.id DESC";
		$sql .= " LIMIT " . (int)$filters['per_page'] . " OFFSET " . (int)$filters['offset'];

		$query = $this->db->query($sql);
		return [
			'rows' => $query ? $query->result() : [],
			'total' => $this->_count("FROM db_item_barcodes b JOIN db_items i ON i.id = b.item_id AND i.store_id = " . $storeId . " WHERE " . $where),
			'totals' => $this->_lotTotals($storeId, $where),
		];
	}

	private function _emptyTotals() {
		return ['sellable_qty' => 0.0, 'held_qty' => 0.0, 'cost_value' => 0.0, 'batch_rows' => 0,
			'expiring_qty' => 0.0, 'expired_qty' => 0.0, 'no_expiry_rows' => 0,
			'serial_rows' => 0, 'unbatched_rows' => 0, 'unlocated_rows' => 0];
	}

	/**
	 * The figures above the table: what the counter could sell, what is being
	 * held back, and what the ledger holds but cannot sell — measured with the
	 * same rule, over the whole filter, not just the page on screen.
	 */
	private function _lotTotals($storeId, $where) {
		$rules = $this->_lotRules($storeId);
		$today = date('Y-m-d');
		$sql = "SELECT"
			. " COALESCE(SUM(CASE WHEN {$rules['eligible']} THEN b.qty ELSE 0 END), 0) AS sellable_qty,"
			. " COALESCE(SUM(CASE WHEN b.qty > 0 AND NOT ({$rules['eligible']}) THEN b.qty ELSE 0 END), 0) AS held_qty,"
			. " COALESCE(SUM(b.qty * COALESCE(b.purchase_price, 0)), 0) AS cost_value,"
			. " COUNT(*) AS batch_rows,"
			. " COALESCE(SUM(CASE WHEN b.expire_date IS NOT NULL AND b.expire_date < '" . $today . "' THEN b.qty ELSE 0 END), 0) AS expired_qty"
			. " FROM db_item_barcodes b"
			. " JOIN db_items i ON i.id = b.item_id AND i.store_id = " . (int)$storeId
			. " WHERE " . $where;
		$sum = $this->db->query($sql);
		$row = $sum ? $sum->row() : null;
		$totals = [
			'sellable_qty' => round((float)($row->sellable_qty ?? 0), 4),
			'held_qty' => round((float)($row->held_qty ?? 0), 4),
			'cost_value' => round((float)($row->cost_value ?? 0), 2),
			'batch_rows' => (int)($row->batch_rows ?? 0),
			'expired_qty' => round((float)($row->expired_qty ?? 0), 4),
		];
		// What the batch view deliberately leaves out, counted rather than hidden,
		// so nobody reads an empty report as an empty shelf. These are measured
		// over the whole shelf and not over the tab's filter: the Running-out-of-
		// date tab only lists batches that HAVE a date, so asking it how many
		// batches have no date would always answer none — a warning that can
		// never fire is worse than no warning.
		$left = $this->db->query(
			"SELECT COUNT(CASE WHEN (b.serial_number IS NOT NULL AND b.serial_number <> '')"
			. " OR (b.imei_number IS NOT NULL AND b.imei_number <> '') THEN 1 END) AS serial_rows,"
			. " COUNT(CASE WHEN ((b.batch_lot IS NULL OR b.batch_lot = '') AND b.expire_date IS NULL)"
			. " AND (b.serial_number IS NULL OR b.serial_number = '') THEN 1 END) AS unbatched_rows,"
			. " COUNT(CASE WHEN b.expire_date IS NULL AND b.qty > 0"
			. " AND ({$rules['real']}) THEN 1 END) AS no_expiry_rows"
			. " FROM db_item_barcodes b JOIN db_items i ON i.id = b.item_id AND i.store_id = " . (int)$storeId
			. " WHERE " . $this->_lotScope((int)$storeId, 0))->row();
		$totals['serial_rows'] = (int)($left->serial_rows ?? 0);
		$totals['unbatched_rows'] = (int)($left->unbatched_rows ?? 0);
		$totals['no_expiry_rows'] = (int)($left->no_expiry_rows ?? 0);
		$totals['unlocated_rows'] = (int)$this->db->query(
			"SELECT COUNT(*) AS n FROM db_item_barcodes b JOIN db_items i ON i.id = b.item_id AND i.store_id = " . (int)$storeId
			. " WHERE b.warehouse_id IS NULL OR b.warehouse_id = 0")->row()->n;
		$totals['expiring_qty'] = 0.0;
		return $totals;
	}

	/**
	 * Batches that are already past their date, or run out inside the shop's own
	 * warning window — batch by batch, which is the only way a shelf can be
	 * cleared. An item's single expiry date cannot say this.
	 */
	public function expiring($storeId, array $filters) {
		$storeId = (int)$storeId;
		$scope = $this->_lotScope($storeId, (int)$filters['warehouse']);
		if (!$scope) return ['rows' => [], 'total' => 0, 'totals' => $this->_emptyTotals(), 'buckets' => []];
		$rules = $this->_lotRules($storeId);
		$today = date('Y-m-d');
		$until = date('Y-m-d', strtotime($today . ' +' . (int)$filters['horizon'] . ' days'));

		$where = $scope . " AND {$rules['real']} AND b.qty > 0"
			. " AND b.expire_date IS NOT NULL AND b.expire_date <= '" . $until . "'";
		if ((string)$filters['search'] !== '') $where .= ' AND ' . $this->_searchSql($filters['search'], ['i.item_name', 'i.item_code', 'b.batch_lot']);

		$sql = "SELECT b.id, b.item_id, b.warehouse_id, b.batch_lot, b.expire_date, b.qty, b.status,"
			. " b.purchase_price, i.item_name, i.item_code, w.warehouse_name,"
			. " CASE WHEN {$rules['eligible']} THEN 1 ELSE 0 END AS sellable,"
			. " DATEDIFF('" . $today . "', b.expire_date) AS days_past,"
			. " (b.qty * COALESCE(b.purchase_price, 0)) AS cost_value"
			. " FROM db_item_barcodes b"
			. " JOIN db_items i ON i.id = b.item_id AND i.store_id = " . $storeId
			. " LEFT JOIN db_warehouse w ON w.id = b.warehouse_id"
			. " WHERE " . $where
			. " ORDER BY b.expire_date ASC, b.id DESC"
			. " LIMIT " . (int)$filters['per_page'] . " OFFSET " . (int)$filters['offset'];
		$query = $this->db->query($sql);

		$from = "FROM db_item_barcodes b JOIN db_items i ON i.id = b.item_id AND i.store_id = " . $storeId . " WHERE " . $where;
		$band = $this->db->query("SELECT"
			. " COALESCE(SUM(CASE WHEN b.expire_date < '" . $today . "' THEN b.qty ELSE 0 END), 0) AS expired_qty,"
			. " COALESCE(SUM(CASE WHEN b.expire_date >= '" . $today . "' THEN b.qty ELSE 0 END), 0) AS soon_qty,"
			. " COALESCE(SUM(CASE WHEN b.expire_date < '" . $today . "' THEN b.qty * COALESCE(b.purchase_price,0) ELSE 0 END), 0) AS expired_value"
			. " " . $from)->row();

		return [
			'rows' => $query ? $query->result() : [],
			'total' => $this->_count($from),
			'bands' => ['expired_qty' => round((float)$band->expired_qty, 4), 'soon_qty' => round((float)$band->soon_qty, 4),
				'soon_until' => $until, 'expired_value' => round((float)$band->expired_value, 2)],
			'totals' => $this->_lotTotals($storeId, $where),
		];
	}

	/**
	 * Which batch each sale came from. This is the record a recall is answered
	 * with, and the only way to see whether oldest-expiry-first is really
	 * happening on the shelf.
	 */
	public function batch_sales($storeId, array $filters) {
		$storeId = (int)$storeId;
		$ids = $this->_warehouseIds($storeId);
		if (!$ids || !$this->db->table_exists('db_salesitem_batch_allocations')) {
			return ['rows' => [], 'total' => 0, 'totals' => ['units' => 0.0, 'returned' => 0.0, 'cost_value' => 0.0, 'invoices' => 0]];
		}
		$where = 'a.store_id = ' . $storeId . " AND s.sales_status = 'Final'"
			. " AND s.sales_date >= '" . $filters['from'] . "' AND s.sales_date <= '" . $filters['to'] . "'";
		$where .= (int)$filters['warehouse'] > 0 ? ' AND a.warehouse_id = ' . (int)$filters['warehouse']
			: ' AND a.warehouse_id IN (' . implode(',', array_map('intval', $ids)) . ')';
		if ((string)$filters['search'] !== '') $where .= ' AND ' . $this->_searchSql($filters['search'], ['i.item_name', 'i.item_code', 'a.batch_lot', 's.sales_code']);

		$sql = "SELECT a.id, a.sales_id, a.item_id, a.warehouse_id, a.barcode_id, a.batch_lot, a.expire_date,"
			. " a.qty_allocated, a.qty_returned, a.rotation_method, s.sales_code, s.sales_date,"
			. " CASE WHEN s.online_order_id > 0 THEN 'Online order' ELSE COALESCE(NULLIF(s.source_channel, ''), 'Counter') END AS channel,"
			. " i.item_name, i.item_code, w.warehouse_name, b.purchase_price,"
			. " (a.qty_allocated * COALESCE(b.purchase_price, 0)) AS cost_value"
			. " FROM db_salesitem_batch_allocations a"
			. " JOIN db_sales s ON s.id = a.sales_id"
			. " JOIN db_items i ON i.id = a.item_id AND i.store_id = " . $storeId
			. " LEFT JOIN db_item_barcodes b ON b.id = a.barcode_id"
			. " LEFT JOIN db_warehouse w ON w.id = a.warehouse_id"
			. " WHERE " . $where
			. " ORDER BY s.sales_date DESC, a.id DESC"
			. " LIMIT " . (int)$filters['per_page'] . " OFFSET " . (int)$filters['offset'];
		$query = $this->db->query($sql);

		$join = " JOIN db_sales s ON s.id = a.sales_id"
			. " JOIN db_items i ON i.id = a.item_id AND i.store_id = " . $storeId
			. " LEFT JOIN db_item_barcodes b ON b.id = a.barcode_id";
		$sum = $this->db->query("SELECT COALESCE(SUM(a.qty_allocated), 0) AS units,"
			. " COALESCE(SUM(a.qty_returned), 0) AS returned,"
			. " COALESCE(SUM(a.qty_allocated * COALESCE(b.purchase_price, 0)), 0) AS cost_value,"
			. " COUNT(DISTINCT a.sales_id) AS invoices"
			. " FROM db_salesitem_batch_allocations a" . $join . " WHERE " . $where)->row();
		return [
			'rows' => $query ? $query->result() : [],
			'total' => $this->_count("FROM db_salesitem_batch_allocations a"
				. " JOIN db_sales s ON s.id = a.sales_id"
				. " JOIN db_items i ON i.id = a.item_id AND i.store_id = " . $storeId
				. " WHERE " . $where),
			'totals' => ['units' => round((float)$sum->units, 4), 'returned' => round((float)$sum->returned, 4),
				'cost_value' => round((float)$sum->cost_value, 2), 'invoices' => (int)$sum->invoices],
		];
	}

	/**
	 * How long the batches nobody has sold have been sitting, counted from the
	 * day each one arrived on the shelf.
	 */
	public function ageing($storeId, array $filters) {
		$storeId = (int)$storeId;
		$scope = $this->_lotScope($storeId, (int)$filters['warehouse']);
		if (!$scope) return ['rows' => [], 'total' => 0, 'buckets' => [], 'totals' => $this->_emptyTotals()];
		$rules = $this->_lotRules($storeId);

		$age = "CASE WHEN b.created_date IS NULL THEN -1 ELSE DATEDIFF(CURDATE(), b.created_date) END";
		$bucketCase = "CASE WHEN b.created_date IS NULL THEN 'unrecorded'"
			. " WHEN " . $age . " <= 90 THEN 'ninety'"
			. " WHEN " . $age . " <= 180 THEN 'onetwo'"
			. " WHEN " . $age . " <= 365 THEN 'twelve' ELSE 'oversix' END";

		$where = $scope . " AND {$rules['real']} AND b.qty > 0";
		if ((string)$filters['search'] !== '') $where .= ' AND ' . $this->_searchSql($filters['search'], ['i.item_name', 'i.item_code', 'b.batch_lot']);
		if ((string)$filters['bucket'] !== '') $where .= ' AND (' . $bucketCase . ") = '" . $filters['bucket'] . "'";

		$sql = "SELECT b.id, b.item_id, b.warehouse_id, b.batch_lot, b.expire_date, b.qty, b.status,"
			. " b.created_date, b.purchase_price, i.item_name, i.item_code, w.warehouse_name,"
			. " CASE WHEN {$rules['eligible']} THEN 1 ELSE 0 END AS sellable,"
			. " " . $age . " AS age_days, " . $bucketCase . " AS bucket,"
			. " (b.qty * COALESCE(b.purchase_price, 0)) AS cost_value"
			. " FROM db_item_barcodes b"
			. " JOIN db_items i ON i.id = b.item_id AND i.store_id = " . $storeId
			. " LEFT JOIN db_warehouse w ON w.id = b.warehouse_id"
			. " WHERE " . $where
			. " ORDER BY (b.created_date IS NULL) DESC, b.created_date ASC, b.id DESC"
			. " LIMIT " . (int)$filters['per_page'] . " OFFSET " . (int)$filters['offset'];
		$query = $this->db->query($sql);

		$from = "FROM db_item_barcodes b JOIN db_items i ON i.id = b.item_id AND i.store_id = " . $storeId . " WHERE " . $where;
		$bucketRows = $this->db->query("SELECT " . $bucketCase . " AS bucket, COUNT(*) AS rows_n,"
			. " COALESCE(SUM(b.qty), 0) AS qty, COALESCE(SUM(b.qty * COALESCE(b.purchase_price,0)), 0) AS cost_value"
			. " " . $from . " GROUP BY bucket")->result();
		$buckets = [];
		foreach (self::BUCKETS as $key => $meta) $buckets[$key] = ['label' => $meta['label'], 'rows' => 0, 'qty' => 0.0, 'cost_value' => 0.0];
		foreach ($bucketRows as $row) {
			$key = (string)$row->bucket;
			if (!isset($buckets[$key])) continue;
			$buckets[$key] = ['label' => self::BUCKETS[$key]['label'], 'rows' => (int)$row->rows_n,
				'qty' => round((float)$row->qty, 4), 'cost_value' => round((float)$row->cost_value, 2)];
		}

		return [
			'rows' => $query ? $query->result() : [],
			'total' => $this->_count($from),
			'buckets' => $buckets,
			'totals' => $this->_lotTotals($storeId, $where),
		];
	}

	private function _count($from) {
		$query = $this->db->query("SELECT COUNT(*) AS n " . $from);
		$row = $query ? $query->row() : null;
		return (int)($row->n ?? 0);
	}
}
