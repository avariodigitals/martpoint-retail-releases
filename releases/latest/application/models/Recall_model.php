<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Recalls & quarantine — freeze physical lots (db_item_barcodes status = 3)
 * across every warehouse, and read back which invoices a frozen batch
 * already reached from db_salesitem_batch_allocations.
 *
 * db_item_barcodes carries no store_id column: every read joins db_items so
 * one store can never freeze (or trace) another store's stock.
 */
class Recall_model extends CI_Model {

	const QUARANTINED = 3; // 1 = sellable, 2 = warranty review (Sales_return_model)

	public function ready(){
		return $this->db->table_exists('db_recalls')
			&& $this->db->table_exists('db_recall_quarantine')
			&& $this->db->table_exists('db_item_barcodes')
			&& $this->db->table_exists('db_items');
	}

	/**
	 * Freeze every lot that matches the batch number. batch_lot is required,
	 * so a recall always names a batch instead of the whole catalogue item.
	 */
	public function initiate($store_id, $data, $user_name){
		if(!$this->ready()) return "failed: Recalls are not set up on this installation yet. Ask MartPoint support to apply the latest update.";
		$store_id = (int)$store_id;
		$batch = trim((string)($data['batch_lot'] ?? ''));
		$reason = trim((string)($data['reason'] ?? ''));
		if($batch === '') return 'failed: Enter the batch or lot number of the medicine to freeze.';
		if(mb_strlen($batch) > 100) return 'failed: That batch number is too long — copy it exactly from the pack.';
		if($reason === '' || mb_strlen($reason) > 255) return 'failed: Enter a short reason for the recall (it goes on the permanent record).';

		$item_id = (int)($data['item_id'] ?? 0);
		$barcode_id = (int)($data['barcode_id'] ?? 0);
		if($barcode_id > 0){
			$unit = $this->db->select('b.id, b.item_id, b.batch_lot')
				->from('db_item_barcodes b')
				->join('db_items i', 'i.id = b.item_id', 'inner')
				->where('b.id', $barcode_id)->where('i.store_id', $store_id)
				->get()->row();
			if(!$unit) return 'failed: That scanned code does not match any stock unit in this store.';
			$item_id = (int)$unit->item_id;
			if(trim((string)$unit->batch_lot) !== '') $batch = trim((string)$unit->batch_lot);
		}
		if($item_id > 0){
			$item = $this->db->select('id, item_name')->where('id', $item_id)->where('store_id', $store_id)
				->get('db_items')->row();
			if(!$item) return 'failed: That medicine does not belong to this store.';
		}

		$lots = $this->matching_lots($store_id, $item_id, $batch, $barcode_id);
		if(!$lots) return 'failed: No stock units with batch number "' . $batch . '" were found in this store'
			. ($item_id ? ' for that medicine' : '') . '. Check the batch number on the pack.';

		$open = $this->open_recall($store_id, $item_id ?: (int)$lots[0]->item_id, $batch);
		if($open){
			// Stock received under this batch after the recall was frozen stays
			// sellable until someone freezes it. Re-running the recall adds those
			// units to the open recall instead of opening a duplicate.
			$fresh = [];
			foreach($lots as $lot) if((int)$lot->status === 1) $fresh[] = $lot;
			if(!$fresh) return 'failed: Batch ' . $batch . ' is already frozen by recall ' . $open->recall_code
				. '. Every unit of it is held; nothing new was found to freeze.';
			return $this->extend($store_id, $open, $fresh);
		}

		$CUR_DATE = date('Y-m-d'); $CUR_TIME = date('H:i:s');
		$this->db->trans_begin();

		$code = 'RC-' . date('Ymd') . '-' . str_pad((string)($this->db->count_all('db_recalls') + 1), 4, '0', STR_PAD_LEFT);
		if($this->recall_code_taken($code)) $code .= '-' . strtoupper(substr(md5(uniqid('', true)), 0, 4));

		$this->db->insert('db_recalls', [
			'store_id' => $store_id,
			'recall_code' => $code,
			'item_id' => (int)$lots[0]->item_id,
			'batch_lot' => $batch,
			'reason' => $reason,
			'status' => 'active',
			'notes' => trim((string)($data['notes'] ?? '')) ?: null,
			'initiated_by' => mb_substr((string)$user_name, 0, 100),
			'initiated_at' => $CUR_DATE . ' ' . $CUR_TIME,
		]);
		$recall_id = (int)$this->db->insert_id();
		if(!$recall_id){ $this->db->trans_rollback(); return 'failed: The recall could not be opened. Please try again.'; }

		$frozen = 0; $units = 0;
		foreach($lots as $lot){
			$this->db->insert('db_recall_quarantine', [
				'recall_id' => $recall_id,
				'barcode_id' => (int)$lot->id,
				'previously_sellable' => ((int)$lot->status === 1 && (float)$lot->qty > 0.00001) ? 1 : 0,
				'created_date' => $CUR_DATE,
			]);
			// status=1 guard: a sold-out unit (0) is not reopened by the freeze,
			// and an already-quarantined one stays out of circulation anyway.
			$this->db->query('UPDATE db_item_barcodes SET status = ' . self::QUARANTINED . ' WHERE id = ? AND status = 1', [(int)$lot->id]);
			$units++;
			if((int)$lot->status === 1) $frozen += (float)$lot->qty;
		}

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return 'failed: The recall could not be saved. No stock was frozen.';
		}
		$this->db->trans_commit();
		$this->_auditFreeze('recall_frozen', $store_id, $recall_id, $code, $batch,
			(int)($lots[0]->item_id ?? 0), $frozen, $reason);
		return 'success<<<###>>>' . $recall_id . '<<<###>>>' . $code . '<<<###>>>' . $units . '<<<###>>>' . $frozen;
	}

	/**
	 * Add newly-received sellable units of the same batch to an open recall.
	 * Only rows not already recorded on this recall are pulled in.
	 */
	public function extend($store_id, $open, array $fresh){
		$recall_id = (int)$open->id;
		$recorded = [];
		$rows = $this->db->select('barcode_id')->where('recall_id', $recall_id)->get('db_recall_quarantine')->result();
		foreach($rows as $row) $recorded[(int)$row->barcode_id] = true;

		$this->db->trans_begin();
		$CUR_DATE = date('Y-m-d');
		$added = 0; $frozen = 0.0;
		foreach($fresh as $lot){
			$bid = (int)$lot->id;
			if(isset($recorded[$bid])) continue;
			$this->db->insert('db_recall_quarantine', [
				'recall_id' => $recall_id,
				'barcode_id' => $bid,
				'previously_sellable' => 1,
				'created_date' => $CUR_DATE,
			]);
			$this->db->query('UPDATE db_item_barcodes SET status = ' . self::QUARANTINED . ' WHERE id = ? AND status = 1', [$bid]);
			if($this->db->affected_rows() > 0){ $added++; $frozen += (float)$lot->qty; }
		}
		if($added === 0){ $this->db->trans_rollback(); return 'failed: Batch ' . $open->batch_lot . ' has no new sellable units to freeze.'; }
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return 'failed: The new units could not be added to the recall. No stock was frozen.';
		}
		$this->db->trans_commit();
		$this->_auditFreeze('recall_extended', $store_id, $recall_id, $open->recall_code, $open->batch_lot,
			(int)($open->item_id ?? 0), $frozen,
			'Units received under this batch after the recall was opened');
		return 'success<<<###>>>' . $recall_id . '<<<###>>>' . $open->recall_code . '<<<###>>>' . $added . '<<<###>>>' . $frozen;
	}

	public function matching_lots($store_id, $item_id, $batch, $barcode_id = 0){
		$this->db->select('b.id, b.item_id, b.warehouse_id, b.batch_lot, b.expire_date, b.qty, b.status, i.item_name')
			->from('db_item_barcodes b')
			->join('db_items i', 'i.id = b.item_id', 'inner')
			->where('i.store_id', (int)$store_id)
			->where("(b.serial_number IS NULL OR b.serial_number = '')", null, false)
			->where("(b.imei_number IS NULL OR b.imei_number = '')", null, false);
		if($item_id > 0) $this->db->where('b.item_id', $item_id);
		if($barcode_id > 0) $this->db->where('b.id', $barcode_id);
		$this->db->group_start()
			->where('b.batch_lot', $batch)
			->or_where('b.barcode', $batch)
		->group_end();
		$this->db->where('b.qty >=', 0);
		$this->db->order_by('b.item_id', 'asc')->order_by('b.warehouse_id', 'asc')->order_by('b.id', 'asc');
		return $this->db->limit(2000)->get()->result();
	}

	public function open_recall($store_id, $item_id, $batch){
		if($item_id <= 0 || trim((string)$batch) === '') return null;
		return $this->db->where('store_id', (int)$store_id)
			->where('item_id', (int)$item_id)
			->where('batch_lot', trim((string)$batch))
			->where('status', 'active')
			->order_by('id', 'desc')->get('db_recalls')->row();
	}

	public function recall_code_taken($code){
		return $this->db->where('recall_code', $code)->count_all_results('db_recalls') > 0;
	}

	/**
	 * Release the frozen lots. New stock received under the same batch while
	 * the recall was open must be frozen first — otherwise closing the recall
	 * would hand the pharmacy a batch that is partly unfrozen.
	 */
	public function close($store_id, $recall_id, $user_name = ''){
		if(!$this->ready()) return 'failed: Recalls are not set up on this installation yet.';
		$recall = $this->get_recall($store_id, $recall_id);
		if(!$recall) return 'failed: That recall does not belong to this store.';
		if((string)$recall->status !== 'active') return 'failed: This recall is already closed.';

		$strays = $this->sellable_strays($store_id, $recall);
		if($strays > 0){
			return 'failed: Batch ' . $recall->batch_lot . ' has ' . $strays
				. ' sellable stock unit(s) received while this recall was open. Freeze them by running this recall again, then close it.';
		}

		$rows = $this->db->where('recall_id', (int)$recall_id)->get('db_recall_quarantine')->result();
		$this->db->trans_begin();
		$restored = 0;
		$released_units = 0.0;
		foreach($rows as $row){
			if((int)$row->previously_sellable !== 1) continue;
			$unit = $this->db->select('qty')->where('id', (int)$row->barcode_id)->limit(1)->get('db_item_barcodes')->row();
			$ok = $this->db->query('UPDATE db_item_barcodes SET status = 1 WHERE id = ? AND status = ' . self::QUARANTINED . ' AND qty > 0.00001',
				[(int)$row->barcode_id]);
			if($ok && $this->db->affected_rows() > 0){ $restored++; $released_units += (float)($unit->qty ?? 0); }
		}
		$this->db->where('id', (int)$recall_id)->where('store_id', (int)$store_id)->update('db_recalls', [
			'status' => 'closed',
			'closed_at' => date('Y-m-d H:i:s'),
		]);
		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			return 'failed: The recall could not be closed. Nothing was released.';
		}
		$this->db->trans_commit();
		$this->_auditRelease($store_id, $recall, $restored, $released_units);
		return 'success<<<###>>>' . (int)$recall_id . '<<<###>>>' . $restored;
	}

	/**
	 * A freeze is a decision somebody has to be able to defend later: which
	 * batch, how much of it left the shelf, and the reason as it was typed.
	 */
	private function _auditFreeze($action, $store_id, $recall_id, $code, $batch, $item_id, $frozen_units, $reason){
		if(!function_exists('mp_audit_log')) return;
		try {
			$capture = mp_audit_capture();
			if (!$capture) return;
			$name = $capture->item_name((int)$item_id);
			$name = $name !== '' ? $name : 'Stock in this batch';
			$said = 'Recall ' . $code . ' froze this batch';
			$reason = trim((string)$reason);
			if($reason !== '') $said .= ': ' . mp_catalogue_name(mb_substr($reason, 0, 160));
			$capture->event($store_id, 'inventory', $action, (int)$recall_id,
				'Batch ' . mp_catalogue_name((string)$batch),
				array(mp_audit_change($name . ' that can be sold',
					mp_audit_format_value((float)$frozen_units, 'qty'), '0')), $said);
		} catch(Throwable $e){
			log_message('error', 'Recall audit capture failed: ' . $e->getMessage());
		}
	}

	/** Releasing a batch is the other half of the same record. */
	private function _auditRelease($store_id, $recall, $restored, $units){
		if(!function_exists('mp_audit_log')) return;
		try {
			$capture = mp_audit_capture();
			if (!$capture) return;
			$name = $capture->item_name((int)($recall->item_id ?? 0));
			$name = $name !== '' ? $name : 'Stock in this batch';
			$capture->event($store_id, 'inventory', 'recall_released', (int)$recall->id,
				'Batch ' . mp_catalogue_name((string)($recall->batch_lot ?? '')),
				array(mp_audit_change($name . ' that can be sold', '0',
					mp_audit_format_value((float)$units, 'qty'))),
				'Recall ' . $recall->recall_code . ' closed; ' . (int)$restored
					. ' stock unit(s) released back for sale');
		} catch(Throwable $e){
			log_message('error', 'Recall release audit capture failed: ' . $e->getMessage());
		}
	}

	/** Sellable units of the recalled batch that the freeze has not caught up with. */
	public function sellable_strays($store_id, $recall){
		if(!$recall) return 0;
		$row = $this->db->query(
			"SELECT COUNT(*) AS c FROM db_item_barcodes b
			   JOIN db_items i ON i.id = b.item_id
			  WHERE i.store_id = ? AND b.item_id = ? AND b.batch_lot = ? AND b.status = 1 AND b.qty > 0",
			[(int)$store_id, (int)$recall->item_id, (string)$recall->batch_lot]
		)->row();
		return $row ? (int)$row->c : 0;
	}

	public function get_recall($store_id, $recall_id){
		return $this->db->where('id', (int)$recall_id)->where('store_id', (int)$store_id)->get('db_recalls')->row();
	}

	public function lots_for_recall($recall_id){
		return $this->db->select('q.id, q.barcode_id, q.previously_sellable, q.created_date,
				b.item_id, b.warehouse_id, b.batch_lot, b.expire_date, b.qty, b.status, i.item_name, w.warehouse_name')
			->from('db_recall_quarantine q')
			->join('db_item_barcodes b', 'b.id = q.barcode_id', 'left')
			->join('db_items i', 'i.id = b.item_id', 'left')
			->join('db_warehouse w', 'w.id = b.warehouse_id', 'left')
			->where('q.recall_id', (int)$recall_id)
			->order_by('b.item_id', 'asc')->order_by('b.id', 'asc')
			->limit(500)->get()->result();
	}

	/**
	 * Invoices the frozen lots already reached. Reads the sale-time lot
	 * provenance so the list is the physical truth, not a name match.
	 */
	public function affected_sales($store_id, $recall_id, $limit = 200){
		if(!$this->db->table_exists('db_salesitem_batch_allocations')) return [];
		$alive = $this->db->field_exists('status', 'db_sales') ? ' AND s.status = 1' : '';
		$scoped = $this->db->field_exists('store_id', 'db_salesitem_batch_allocations')
			? ' AND a.store_id = ' . (int)$store_id : '';
		return $this->db->query(
			"SELECT s.id AS sales_id, s.sales_code, s.created_date, s.grand_total,
					c.customer_name, c.mobile, a.qty_allocated AS qty, a.item_id, i.item_name
			   FROM db_recall_quarantine q
			   JOIN db_salesitem_batch_allocations a ON a.barcode_id = q.barcode_id{$scoped}
			   JOIN db_sales s ON s.id = a.sales_id
			   LEFT JOIN db_items i ON i.id = a.item_id
			   LEFT JOIN db_customers c ON c.id = s.customer_id
			  WHERE s.store_id = ? AND q.recall_id = ?{$alive}
			  ORDER BY s.id DESC
			  LIMIT " . (int)$limit,
			[(int)$store_id, (int)$recall_id])->result();
	}

	public function list_recalls($store_id, $status = 'active'){
		$where = trim((string)$status);
		$this->db->select('r.*, i.item_name,
				(SELECT COUNT(*) FROM db_recall_quarantine q2 WHERE q2.recall_id = r.id) AS unit_count,
				(SELECT COALESCE(SUM(b2.qty),0) FROM db_recall_quarantine q3
					JOIN db_item_barcodes b2 ON b2.id = q3.barcode_id
				   WHERE q3.recall_id = r.id AND b2.status = ' . self::QUARANTINED . ') AS frozen_qty')
			->from('db_recalls r')
			->join('db_items i', 'i.id = r.item_id', 'left')
			->where('r.store_id', (int)$store_id);
		if($where !== 'all') $this->db->where('r.status', $where === '' ? 'active' : $where);
		$this->db->order_by('r.id', 'desc')->limit(200);
		return $this->db->get()->result();
	}
}
