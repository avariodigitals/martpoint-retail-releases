<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Batch reconciliation — finds where the batch ledger and the stock figures
 * disagree, and posts the one entry that closes the gap.
 *
 * A store on FEFO/FIFO sells from db_item_barcodes; its stock figures are
 * derived from documents (purchases, sales, returns, adjustments, transfers).
 * When the two sides disagree the till refuses the sale, so the shop needs a
 * way to say which side is the truth — per item, per location, by a human.
 */
class Batch_reconciliation_model extends CI_Model {

	const TOLERANCE = 0.0001;
	/** The documents are wrong: post an adjustment so the figures match the shelf. */
	const FIX_COUNTS = 'counts';
	/** The ledger is short: record the batch that is physically there. */
	const FIX_BATCHES = 'batches';

	public function ready() {
		return $this->db->table_exists('db_batch_reconciliations')
			&& $this->db->table_exists('db_item_barcodes')
			&& $this->db->table_exists('db_warehouseitems');
	}

	/** Active stock locations of this store only. */
	public function warehouses($storeId) {
		return $this->db->select('id, warehouse_name')
			->where('store_id', (int)$storeId)->where('status', 1)
			->order_by('id', 'ASC')->get('db_warehouse')->result();
	}

	private function _warehouseIds($storeId) {
		$ids = [];
		foreach ($this->warehouses($storeId) as $w) $ids[] = (int)$w->id;
		return $ids;
	}

	/**
	 * Is this store selling from the ledger at all? Manual means the till
	 * ignores lots, so a disagreement costs nothing until rotation is switched on.
	 */
	public function rotationMethod($storeId) {
		$this->load->model('inventory_rotation_model', 'rotation');
		return $this->rotation->method((int)$storeId);
	}

	private function _blocksExpired($storeId) {
		$this->load->model('inventory_rotation_model', 'rotation');
		return $this->rotation->blocks_expired_sales((int)$storeId);
	}

	/**
	 * The lot rules, asked from Inventory_rotation_model — the model that
	 * actually spends them at the till. The screen and the counter cannot drift
	 * apart, because there is only one place that says what a sellable lot is.
	 */
	private function _lotRules($storeId) {
		$this->load->model('inventory_rotation_model', 'rotation');
		return $this->rotation->lot_rules((int)$storeId);
	}

	/**
	 * Both sides of one item in one location, plus what the ledger holds but
	 * cannot sell (quarantined, retired or expired).
	 */
	public function figures($storeId, $itemId, $warehouseId) {
		$itemId = (int)$itemId; $warehouseId = (int)$warehouseId;
		if ($storeId <= 0 || $itemId <= 0 || $warehouseId <= 0) return null;
		$item = $this->db->select('id, store_id, item_name, stock, service_bit, purchase_price')
			->where('id', $itemId)->get('db_items')->row();
		if (!$item || (int)$item->store_id !== (int)$storeId) return null;
		if (!$this->in_scope_warehouse($storeId, $warehouseId)) return null;
		$rules = $this->_lotRules($storeId);

		$totals = $this->db->query(
			"SELECT COALESCE(SUM(CASE WHEN {$rules['eligible']} THEN qty ELSE 0 END),0) AS eligible_qty,"
			. " COALESCE(SUM(CASE WHEN {$rules['real']} THEN qty ELSE 0 END),0) AS ledger_qty,"
			. " COUNT(CASE WHEN {$rules['real']} THEN 1 END) AS lot_rows"
			. " FROM db_item_barcodes WHERE item_id = ? AND warehouse_id = ?",
			[$itemId, $warehouseId])->row();

		$count = $this->db->select('available_qty')->where('store_id', (int)$storeId)
			->where('warehouse_id', $warehouseId)->where('item_id', $itemId)->get('db_warehouseitems');
		$row = $count ? $count->row() : null;

		return [
			'item_id' => $itemId,
			'item_name' => (string)($item->item_name ?? ''),
			'warehouse_id' => $warehouseId,
			'store_count' => (float)($item->stock ?? 0),
			'count_qty' => (float)($row->available_qty ?? 0),
			'lot_qty' => (float)($totals->eligible_qty ?? 0),
			'ledger_qty' => (float)($totals->ledger_qty ?? 0),
			'held_back_qty' => max(0, (float)($totals->ledger_qty ?? 0) - (float)($totals->eligible_qty ?? 0)),
			'lot_rows' => (int)($totals->lot_rows ?? 0),
			'difference' => round((float)($totals->eligible_qty ?? 0) - (float)($row->available_qty ?? 0), 4),
		];
	}

	public function in_scope_warehouse($storeId, $warehouseId) {
		return in_array((int)$warehouseId, $this->_warehouseIds($storeId), true);
	}

	/**
	 * Items whose ledger and figures disagree in one location, biggest gap first.
	 * $warehouseId 0 means every location of the store, where the item's own
	 * stock figure is compared against its lots across all of them.
	 */
	public function mismatches($storeId, $warehouseId = 0, $limit = 25, $offset = 0, $search = '') {
		$rows = $this->_gap_rows($storeId, $warehouseId, $limit, $offset, $search, false);
		return $rows;
	}

	public function count_mismatches($storeId, $warehouseId = 0, $search = '') {
		$rows = $this->_gap_rows($storeId, $warehouseId, 0, 0, $search, true);
		return (int)($rows->num_rows() ? $rows->row()->gap_rows : 0);
	}

	private function _gap_rows($storeId, $warehouseId, $limit, $offset, $search, $countOnly) {
		$storeId = (int)$storeId;
		$ids = $this->_warehouseIds($storeId);
		if (!$ids) return $this->db->query('SELECT ' . ($countOnly ? '0 AS gap_rows' : '0 AS id') . ' FROM DUAL');
		$scope = '(' . implode(',', array_map('intval', $ids)) . ')';
		$rules = $this->_lotRules($storeId);

		$onSite = (int)$warehouseId > 0 && in_array((int)$warehouseId, $ids, true);
		if ($onSite) {
			// One location: that branch's own figure against that branch's lots.
			$side = 'COALESCE(w.available_qty, 0)';
			$branchJoin = " LEFT JOIN db_warehouseitems w ON w.item_id = i.id AND w.store_id = i.store_id"
				. " AND w.warehouse_id = " . (int)$warehouseId;
			$lotFilter = " AND warehouse_id = " . (int)$warehouseId;
		} else {
			// Every location: the item's own stock figure against its lots
			// across the store. No branch join here — joining db_warehouseitems
			// would repeat one medicine once per branch it sits in.
			$side = 'i.stock';
			$branchJoin = '';
			$lotFilter = '';
		}
		$gap = "ABS(" . $side . " - COALESCE(b.eligible_qty, 0))";

		$select = $countOnly
			? 'COUNT(*) AS gap_rows'
			: "i.id AS item_id, i.item_name, i.stock AS store_count,"
				. " " . $side . " AS count_qty,"
				. " COALESCE(b.eligible_qty, 0) AS lot_qty,"
				. " COALESCE(b.ledger_qty, 0) AS ledger_qty,"
				. " COALESCE(b.lot_rows, 0) AS lot_rows,"
				. " " . $gap . " AS gap_qty, (" . $side . " - COALESCE(b.eligible_qty,0)) AS signed_gap";

		$sql = "SELECT " . $select . " FROM db_items i"
			. $branchJoin
			. " LEFT JOIN (SELECT item_id,"
				. " SUM(CASE WHEN {$rules['eligible']} THEN qty ELSE 0 END) AS eligible_qty,"
				. " SUM(CASE WHEN {$rules['real']} THEN qty ELSE 0 END) AS ledger_qty,"
				. " COUNT(CASE WHEN {$rules['real']} THEN 1 END) AS lot_rows"
				. " FROM db_item_barcodes WHERE warehouse_id IN $scope" . $lotFilter . " GROUP BY item_id) b ON b.item_id = i.id"
			. " WHERE i.store_id = " . $storeId . " AND i.service_bit = 0 AND " . $gap . " > " . self::TOLERANCE;
		$search = trim((string)$search);
		if ($search !== '') {
			$like = $this->db->escape('%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search) . '%');
			$sql .= " AND i.item_name LIKE " . $like;
		}
		if (!$countOnly) $sql .= " ORDER BY " . $gap . " DESC, i.item_name ASC LIMIT " . max(1, (int)$limit) . " OFFSET " . max(0, (int)$offset);
		return $this->db->query($sql);
	}

	/** The batch rows of one item in one location, for the decision screen. */
	public function lot_rows($storeId, $itemId, $warehouseId) {
		$figures = $this->figures($storeId, $itemId, $warehouseId);
		if (!$figures) return [];
		$rules = $this->_lotRules($storeId);
		$query = $this->db->query(
			"SELECT id, batch_lot, expire_date, qty, status,"
			. " CASE WHEN {$rules['eligible']} THEN 1 ELSE 0 END AS sellable"
			. " FROM db_item_barcodes WHERE item_id = ? AND warehouse_id = ?"
			. " AND ((batch_lot IS NOT NULL AND batch_lot <> '') OR expire_date IS NOT NULL)"
			. " ORDER BY (qty <= 0) ASC, expire_date ASC, id ASC",
			[(int)$itemId, (int)$warehouseId]);
		return $query ? $query->result() : [];
	}

	/**
	 * Recalculate both derived figures for one item: the store's own stock
	 * number and every location's copy of it. The batch ledger is never
	 * touched here — it is the record, these are the summaries.
	 */
	public function refresh($itemId) {
		$this->load->model('pos_model');
		if (!$this->pos_model->update_items_quantity((int)$itemId)) return false;
		return update_warehouse_items([[(int)$itemId]]);
	}

	/**
	 * Close one gap. Which side is the truth is always the operator's call, so
	 * nothing here guesses: a balance either posts a signed stock adjustment
	 * (the documents were wrong) or records the batch that is physically on the
	 * shelf (the ledger was short). Both write one reconciliation row and one
	 * audit entry.
	 *
	 * @return string 'success<<<###>>>' or 'failed: plain English'
	 */
	public function balance($storeId, array $opts, $userId = null) {
		$storeId = (int)$storeId;
		$itemId = (int)($opts['item_id'] ?? 0);
		$warehouseId = (int)($opts['warehouse_id'] ?? 0);
		$fix = trim((string)($opts['fix'] ?? ''));
		if (!in_array($fix, [self::FIX_COUNTS, self::FIX_BATCHES], true)) {
			return 'failed: Choose which figure is correct before balancing.';
		}
		$note = trim(substr(strip_tags((string)($opts['note'] ?? '')), 0, 255));
		if ($note === '') return 'failed: Say why the two sides disagree — that note is the record of this decision.';
		$figures = $this->figures($storeId, $itemId, $warehouseId);
		if (!$figures) return 'failed: That medicine is not in this location, or it belongs to another business.';
		// Both numbers on the screen are recalculated from documents, and a
		// recalculation can be waiting on a queue. Measure against what the
		// till will actually use, so a balance closes the real gap and not a
		// stale cache of it.
		if (!$this->refresh($itemId)) {
			return 'failed: The stock figures could not be recalculated. Nothing was changed.';
		}
		$figures = $this->figures($storeId, $itemId, $warehouseId);
		if (!$figures) return 'failed: That medicine is not in this location, or it belongs to another business.';

		$gap = $figures['lot_qty'] - $figures['count_qty'];
		if (abs($gap) <= self::TOLERANCE) {
			return 'failed: The ledger and the figures already agree for ' . $figures['item_name'] . ' — nothing to balance. Reload the list.';
		}

		$this->db->trans_begin();
		$barcodeId = null; $adjustmentId = null; $delta = 0.0; $batchLot = null; $expireDate = null;

		if ($fix === self::FIX_COUNTS) {
			$delta = round($gap, 4);
			$reasonCode = null;
			if ($delta < 0) {
				$reasonCode = trim((string)($opts['reason_code'] ?? ''));
				if (!array_key_exists($reasonCode, mp_get_wastage_reasons())) {
					$this->db->trans_rollback();
					return 'failed: Stock the shelf cannot show has to be written out against a reason. Pick one of the listed reasons.';
				}
			}
			$document = $this->db->insert('db_stockadjustment', [
				'store_id' => $storeId,
				'warehouse_id' => $warehouseId,
				'reference_no' => 'RECON-' . date('Ymd-His') . '-' . $itemId,
				'adjustment_date' => date('Y-m-d'),
				'adjustment_note' => 'Batch reconciliation — ' . $note,
				'created_date' => date('Y-m-d'),
				'created_time' => date('H:i:s'),
				'created_by' => (string)($userId ?: 'system'),
				'system_ip' => (string)($this->input->ip_address()),
				'system_name' => 'MartPoint',
				'status' => 1,
			]);
			$adjustmentId = (int)$this->db->insert_id();
			if (!$document || $adjustmentId <= 0) {
				$this->db->trans_rollback();
				return 'failed: The correcting stock entry could not be opened. Nothing was changed.';
			}
			$line = [
				'store_id' => $storeId,
				'warehouse_id' => $warehouseId,
				'adjustment_id' => $adjustmentId,
				'item_id' => $itemId,
				'adjustment_qty' => $delta,
				'description' => 'Batch reconciliation: ' . $figures['item_name'],
				'status' => 1,
			];
			if ($reasonCode !== null) $line['reason_code'] = $reasonCode;
			if (!$this->db->insert('db_stockadjustmentitems', $line)) {
				$this->db->trans_rollback();
				return 'failed: The correcting stock entry could not be written. Nothing was changed.';
			}
			if (!$this->refresh($itemId)) {
				$this->db->trans_rollback();
				return 'failed: The stock figures could not be recalculated. Nothing was changed.';
			}
		} else {
			// The books hold more than the ledger: record the batch that is there.
			$delta = round(-$gap, 4);
			if ($delta <= self::TOLERANCE) {
				$this->db->trans_rollback();
				return 'failed: The batch ledger already holds at least what the figures say for ' . $figures['item_name'] . '. If the ledger is the truth, balance it the other way instead.';
			}
			$batchLot = trim(substr(strip_tags((string)($opts['batch_lot'] ?? '')), 0, 100));
			if ($batchLot === '') {
				$this->db->trans_rollback();
				return 'failed: Type the batch or lot number printed on the packs. Without it the shelf cannot be sold from under batch rotation.';
			}
			$expireDate = trim((string)($opts['expire_date'] ?? ''));
			if ($expireDate === '') {
				$expireDate = null;
			} elseif (!is_valid_date($expireDate)) {
				$this->db->trans_rollback();
				return 'failed: That expiry date is not a real date. Type it as YYYY-MM-DD or leave it blank if the pack shows none.';
			} else {
				$stamp = strtotime($expireDate);
				if ($stamp === false) {
					$this->db->trans_rollback();
					return 'failed: That expiry date is not a real date. Type it as YYYY-MM-DD or leave it blank if the pack shows none.';
				}
				$expireDate = date('Y-m-d', $stamp);
				if ($this->_blocksExpired($storeId) && $expireDate < date('Y-m-d')) {
					$this->db->trans_rollback();
					return 'failed: That batch expired on ' . $expireDate . ', and this store does not sell expired medicine. Record it as expired stock instead of a balance.';
				}
			}
			// A batch already recorded in this location is topped up, not duplicated.
			$existing = $this->db->select('id, status')->where('item_id', $itemId)
				->where('warehouse_id', $warehouseId)->where('batch_lot', $batchLot)
				->order_by('id', 'ASC')->limit(1)->get('db_item_barcodes')->row();
			if ($existing && (int)$existing->status === 3) {
				$this->db->trans_rollback();
				return 'failed: Batch ' . $batchLot . ' is held under a recall. Close the recall before putting stock back against it.';
			}
			if ($existing) {
				$topped = $this->db->query(
					'UPDATE db_item_barcodes SET qty = qty + ?, status = 1'
					. ', expire_date = COALESCE(?, expire_date) WHERE id = ? AND status <> 3',
					[$delta, $expireDate, (int)$existing->id]);
				if (!$topped || $this->db->affected_rows() !== 1) {
					$this->db->trans_rollback();
					return 'failed: That batch changed while this balance was being saved. Nothing was changed.';
				}
				$barcodeId = (int)$existing->id;
			} else {
				$cost = $this->db->select('purchase_price')->where('id', $itemId)->get('db_items')->row();
				$row = [
					'item_id' => $itemId,
					'warehouse_id' => $warehouseId,
					'barcode' => '',
					'batch_lot' => $batchLot,
					'purchase_price' => round((float)($cost->purchase_price ?? 0), 2),
					'qty' => $delta,
					'status' => 1,
					'created_date' => date('Y-m-d'),
					'created_time' => date('H:i:s'),
				];
				if ($expireDate !== null) $row['expire_date'] = $expireDate;
				if (!$this->db->insert('db_item_barcodes', $row)) {
					$this->db->trans_rollback();
					return 'failed: The batch could not be recorded. Nothing was changed.';
				}
				$barcodeId = (int)$this->db->insert_id();
			}
		}

		if (!$this->db->insert('db_batch_reconciliations', [
			'store_id' => $storeId,
			'warehouse_id' => $warehouseId,
			'item_id' => $itemId,
			'count_qty' => $figures['count_qty'],
			'lot_qty' => $figures['lot_qty'],
			'delta_qty' => $delta,
			'fix' => $fix,
			'barcode_id' => $barcodeId,
			'adjustment_id' => $adjustmentId,
			'batch_lot' => $batchLot,
			'expire_date' => $expireDate,
			'reason' => $note,
			'created_by' => $userId ? (int)$userId : null,
			'created_at' => date('Y-m-d H:i:s'),
		])) {
			$this->db->trans_rollback();
			return 'failed: The balance happened but could not be written down. Nothing was changed.';
		}

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return 'failed: The balance could not be saved. Nothing was changed.';
		}
		$this->db->trans_commit();

		// Both sides are re-read from the ledger after the write, so the entry
		// reports the figures that actually landed rather than the ones the
		// screen was showing when the decision was made.
		$balanced = $this->figures($storeId, $itemId, $warehouseId) ?: $figures;
		$moves = array(
			array('Stock figure', $figures['count_qty'], $balanced['count_qty']),
			array('Units the batches hold', $figures['lot_qty'], $balanced['lot_qty']),
		);
		$changes = array();
		foreach ($moves as $move) {
			if (abs((float)$move[1] - (float)$move[2]) <= self::TOLERANCE) continue;
			$changes[] = mp_audit_change($move[0], format_qty($move[1]), format_qty($move[2]));
		}
		if ($batchLot !== null && trim((string)$batchLot) !== '') {
			$changes[] = mp_audit_change('Batch named on the ledger', null, trim((string)$batchLot)
				. ($expireDate ? ', expires ' . mp_audit_format_value($expireDate, 'date') : ', no expiry date on the pack'));
		}
		$capture = mp_audit_capture();
		if ($capture) $capture->event($storeId, 'inventory', 'batch_reconciliation', $itemId,
			$figures['item_name'] . ' at ' . $this->_warehouse_name($warehouseId), $changes, sprintf(
			'%s balanced at %s: %s counted %s, batches held %s. %s.',
			$figures['item_name'],
			$this->_warehouse_name($warehouseId),
			$fix === self::FIX_COUNTS ? 'the batch ledger was taken as correct, so the stock figures were' : 'the count was taken as correct, so the batch ledger was',
			format_qty($figures['count_qty']),
			format_qty($figures['lot_qty']),
			($fix === self::FIX_COUNTS ? 'Stock figures' : 'Batch ledger') . ' moved by ' . format_qty(abs($delta))
		));

		return 'success<<<###>>>' . $itemId;
	}

	private function _warehouse_name($warehouseId) {
		$row = $this->db->select('warehouse_name')->where('id', (int)$warehouseId)->get('db_warehouse')->row();
		return trim((string)($row->warehouse_name ?? '')) ?: 'the shop';
	}

	/** What has already been balanced, newest first, with who did it. */
	public function history($storeId, $limit = 20, $offset = 0) {
		if (!$this->ready()) return [];
		$sql = "SELECT r.id, r.item_id, r.delta_qty, r.fix, r.count_qty, r.lot_qty, r.batch_lot,"
			. " r.reason, r.created_at, i.item_name, w.warehouse_name, u.first_name, u.last_name"
			. " FROM db_batch_reconciliations r"
			. " LEFT JOIN db_items i ON i.id = r.item_id"
			. " LEFT JOIN db_warehouse w ON w.id = r.warehouse_id"
			. " LEFT JOIN db_users u ON u.id = r.created_by"
			. " WHERE r.store_id = ?"
			. " ORDER BY r.created_at DESC, r.id DESC LIMIT " . max(1, (int)$limit)
			. " OFFSET " . max(0, (int)$offset);
		$query = $this->db->query($sql, [(int)$storeId]);
		return $query ? $query->result() : [];
	}

	/**
	 * Is this store about to block its own counter? Used by the rotation switch
	 * so a shop is warned, in numbers, before it turns batch selling on.
	 */
	public function readiness($storeId) {
		$byWarehouse = [];
		foreach ($this->warehouses($storeId) as $w) {
			$byWarehouse[(int)$w->id] = ['name' => (string)$w->warehouse_name, 'gaps' => $this->count_mismatches($storeId, (int)$w->id)];
		}
		$gaps = 0;
		foreach ($byWarehouse as $entry) $gaps += (int)$entry['gaps'];
		return ['rotation' => $this->rotationMethod($storeId), 'gaps' => $gaps, 'by_warehouse' => $byWarehouse];
	}
}
