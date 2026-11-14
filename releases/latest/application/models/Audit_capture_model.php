<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Audit capture
 *
 * The trail already answered "who signed in and when". This is the other half:
 * the movements a shop is accountable for — a price that changed, stock that
 * left the shelf, a batch that was sealed, a course that was dispensed — each
 * written with the value it had before and the value it has now.
 *
 * Every method here records and never decides: no method blocks a save, no
 * method can break one. A store that changes nothing writes nothing, because
 * each entry is built from a real before/after comparison rather than from
 * what a form claimed it was about to do.
 */
class Audit_capture_model extends CI_Model {

	/** Fields that carry money, a count or a switch, and how to say them. */
	private $price_fields = array(
		'sales_price'           => array('label' => 'Selling price', 'type' => 'money'),
		'price'                 => array('label' => 'List price', 'type' => 'money'),
		'purchase_price'        => array('label' => 'Cost price', 'type' => 'money'),
		'mrp'                   => array('label' => 'Recommended price', 'type' => 'money'),
		'online_price'          => array('label' => 'Online price', 'type' => 'money'),
		'discount'              => array('label' => 'Discount', 'type' => 'qty'),
		'profit_margin'         => array('label' => 'Profit margin', 'type' => 'qty'),
	);

	public function __construct(){
		parent::__construct();
	}

	/**
	 * One entry. $label is what a reviewer reads as the thing that changed
	 * (the medicine, the bill, the batch) — never a row id.
	 */
	public function event($storeId, $module, $action, $ref = null, $label = null, $changes = array(), $description = null, $userId = null){
		if (!function_exists('mp_audit_log')) return false;
		$opts = array('store_id' => (int)$storeId);
		if ($label !== null && trim((string)$label) !== '') $opts['label'] = $label;
		if ($changes) $opts['changes'] = $changes;
		if ($userId !== null && (int)$userId > 0) $opts['user_id'] = (int)$userId;
		return mp_audit_log($module, $action, $ref, $description, $opts);
	}

	/**
	 * A product's money moved. Pass the row as it was and the row as it is;
	 * only the figures that genuinely changed are recorded.
	 */
	public function priceFields(){
		return $this->price_fields;
	}

	public function priceChanged($storeId, $itemId, $before, $after, $module = 'catalogue', $action = 'price_change', $description = null){
		if (!$before || !$after) return false;
		$changes = mp_audit_diff($before, $after, $this->price_fields);
		if (!$changes) return false;      // a save that re-typed the same price is not an event
		$label = mp_catalogue_name(is_array($after) ? ($after['item_name'] ?? '') : ($after->item_name ?? ''));
		return $this->event($storeId, $module, $action, (int)$itemId, $label, $changes, $description);
	}

	/**
	 * Stock that actually moved on the shelf, one line per product.
	 * $lines: ['item' => name, 'from' => quantity before, 'to' => quantity now]
	 */
	public function stockMoved($storeId, $module, $action, $ref, $document, array $lines, $description = null){
		$changes = array();
		foreach ($lines as $line) {
			if (!is_array($line)) continue;
			$name = trim(mp_catalogue_name($line['item'] ?? ''));
			if ($name === '') continue;
			$from = isset($line['from']) ? mp_audit_format_value($line['from'], 'qty') : null;
			$to   = isset($line['to']) ? mp_audit_format_value($line['to'], 'qty') : null;
			if ($from !== null && $to !== null && $from === $to) continue;   // nothing left or arrived
			$changes[] = mp_audit_change($name, $from, $to);
		}
		if (!$changes) return false;
		return $this->event($storeId, $module, $action, $ref, $document, $changes, $description);
	}

	/** Shelf quantity for one product, or null when the product is gone. */
	public function shelf_qty($itemId){
		$itemId = (int)$itemId;
		if ($itemId <= 0) return null;
		$row = $this->db->select('stock')->where('id', $itemId)->limit(1)->get('db_items')->row();
		return $row ? (float)$row->stock : null;
	}

	/** The name a reviewer should see for a product, or an empty string. */
	public function item_name($itemId){
		return mp_audit_record_name('db_items', $itemId, 'item_name');
	}

	/**
	 * What left the shelf under a prescription, and which batch it came from.
	 *
	 * This entry cannot appear in a shop that does not dispense: it is built
	 * from the bill's own lines, so a store with nothing marked "needs a
	 * prescription" writes nothing at all. Editing an existing bill records
	 * only the lines whose quantity actually moved, including medicines that
	 * were taken off the bill entirely.
	 */
	public function dispensed($storeId, $salesId, $isUpdate = false, array $before = array(), $source = 'till'){
		$storeId = (int)$storeId;
		$salesId = (int)$salesId;
		if ($storeId <= 0 || $salesId <= 0) return false;
		if (!$this->db->table_exists('db_salesitems')
			|| !$this->db->field_exists('prescription_required', 'db_items')) return false;

		$sale = $this->db->select('sales_code, customer_id')->where('id', $salesId)
			->where('store_id', $storeId)->limit(1)->get('db_sales')->row();
		if (!$sale) return false;

		$lines = $this->db->select('si.id AS line_id, si.item_id, si.sales_qty, i.item_name, i.consumable_unit')
			->from('db_salesitems si')
			->join('db_items i', 'i.id = si.item_id', 'inner')
			->where('si.sales_id', $salesId)
			->where('i.prescription_required', 1)
			->get()->result();
		if (!$lines) return false;

		$lots = $this->lotProvenance($salesId);
		$changes = array();
		foreach ($lines as $line) {
			$itemId = (int)$line->item_id;
			$qty = (float)$line->sales_qty;
			$was = isset($before[$itemId]) ? (float)$before[$itemId] : null;
			// A line that is still on the bill counts as accounted for whether or
			// not its quantity moved — otherwise an untouched medicine would be
			// reported below as having been taken off the bill.
			if ($was !== null) unset($before[$itemId]);
			if ($isUpdate && $was !== null && abs($was - $qty) < 0.0000001) continue;   // untouched line
			$changes[] = mp_audit_change(
				mp_catalogue_name($line->item_name),
				$was === null ? 'Nothing dispensed' : $this->saidQty($was, $line->consumable_unit),
				$this->saidQty($qty, $line->consumable_unit) . $this->saidLots($lots[$itemId] ?? array())
			);
		}
		foreach ($before as $itemId => $was) {
			if ((float)$was <= 0) continue;
			$changes[] = mp_audit_change($this->item_name($itemId),
				$this->saidQty((float)$was, null), 'Nothing dispensed');
		}
		if (!$changes) return false;

		$customer = $this->db->select('customer_name')->where('id', (int)$sale->customer_id)
			->limit(1)->get('db_customers')->row();
		$who = $this->givenName($customer ? $customer->customer_name : '');
		$document = trim((string)$sale->sales_code) !== '' ? 'Invoice ' . trim((string)$sale->sales_code) : 'A bill';
		$description = ($isUpdate ? 'Corrected on ' : 'Dispensed on ') . $document
			. ($who !== '' ? ' for ' . $who : '')
			. ($source === 'online' ? ' — order received from the online storefront.' : '');
		return $this->event($storeId, 'dispensing', $isUpdate ? 'dispensed_corrected' : 'dispensed',
			$salesId, $document, $changes, $description);
	}

	/** The bill's prescription lines as they stand now: item id => quantity. */
	public function dispensedLines($salesId){
		$salesId = (int)$salesId;
		if ($salesId <= 0 || !$this->db->table_exists('db_salesitems')
			|| !$this->db->field_exists('prescription_required', 'db_items')) return array();
		$rows = $this->db->select('si.item_id, SUM(si.sales_qty) AS qty')
			->from('db_salesitems si')
			->join('db_items i', 'i.id = si.item_id', 'inner')
			->where('si.sales_id', $salesId)
			->where('i.prescription_required', 1)
			->group_by('si.item_id')
			->get()->result();
		$out = array();
		foreach ($rows as $r) { $out[(int)$r->item_id] = (float)$r->qty; }
		return $out;
	}

	/** Which batches each prescription line was drawn from. */
	private function lotProvenance($salesId){
		$lots = array();
		if (!$this->db->table_exists('db_salesitem_batch_allocations')) return $lots;
		$rows = $this->db->select('si.item_id AS item_id, a.batch_lot, a.expire_date, a.qty_allocated')
			->from('db_salesitem_batch_allocations a')
			->join('db_salesitems si', 'si.id = a.salesitems_id', 'inner')
			->where('a.sales_id', (int)$salesId)
			->get()->result();
		foreach ($rows as $r) {
			$itemId = (int)$r->item_id;
			$lot = trim(mp_catalogue_name((string)$r->batch_lot));
			if ($lot === '') continue;
			$units = isset($lots[$itemId]['lots'][$lot]) ? $lots[$itemId]['lots'][$lot] : 0;
			$lots[$itemId]['lots'][$lot] = $units + (float)$r->qty_allocated;
			if (!empty($r->expire_date) && empty($lots[$itemId]['expiry'])) $lots[$itemId]['expiry'] = $r->expire_date;
		}
		return $lots;
	}

	private function saidQty($qty, $unit){
		$text = mp_audit_format_value($qty, 'qty');
		$unit = trim(mp_catalogue_name((string)$unit));
		if ($unit !== '' && strlen($unit) <= 24) $text .= ' ' . $unit;
		return $text;
	}

	private function saidLots(array $lot){
		$out = '';
		$lots = array_keys($lot['lots'] ?? array());
		if ($lots) {
			$shown = array_slice($lots, 0, 2);
			if (count($lots) > 2) $shown[] = count($lots) - 2 . ' others';
			$out .= ' from batch ' . implode(', ', $shown);
		}
		if (!empty($lot['expiry'])) $out .= ', expires ' . mp_audit_format_value($lot['expiry'], 'date');
		return $out;
	}

	/** Reviewers see a first name, never a whole identity. */
	private function givenName($fullName){
		$name = trim(preg_replace('/\s+/', ' ', mp_catalogue_name((string)$fullName)));
		if ($name === '' || stripos($name, 'walk-in') !== false) return '';
		$parts = explode(' ', $name);
		return trim(array_shift($parts));
	}

	/**
	 * A prescription chart — what it says now and what it said before. Clinical
	 * records are edited after the fact all the time, so the trail keeps the
	 * wording of the change rather than a bare "record updated".
	 */
	public function chartSaved($storeId, $noteId, $before, $medicinesBefore = array()){
		$note = $this->db->select('mn.*, c.customer_name')
			->from('db_medical_notes mn')
			->join('db_customers c', 'c.id = mn.customer_id', 'left')
			->where('mn.id', (int)$noteId)->where('mn.store_id', (int)$storeId)
			->limit(1)->get()->row();
		if (!$note) return false;
		$changes = mp_audit_diff($before ? $before : array(), $note, $this->chartFields());
		$after = $this->chartMedicines($noteId);
		$saidBefore = implode(', ', $this->shortList($medicinesBefore));
		$saidAfter = implode(', ', $this->shortList($after));
		if ($saidBefore !== $saidAfter) {
			$changes[] = mp_audit_change('Medicines on the chart',
				$saidBefore === '' ? 'No medicines' : $saidBefore,
				$saidAfter === '' ? 'No medicines' : $saidAfter);
		}
		if (!$changes) return false;
		$who = $this->givenName($note->customer_name ?? '');
		return $this->event((int)$storeId, 'dispensing', $before ? 'chart_corrected' : 'chart_written',
			(int)$noteId, $who !== '' ? 'Prescription chart for ' . $who : 'Prescription chart',
			$changes, 'Chart dated ' . mp_audit_format_value($note->note_date, 'date') . '.');
	}

	/**
	 * A prescription chart destroyed on purpose. Called before the row goes, so
	 * the trail keeps what the chart said — a struck-out clinical record has to
	 * be reconstructable from the log.
	 */
	public function chartRemoved($storeId, $noteId){
		$storeId = (int)$storeId;
		$note = $this->db->select('mn.*, c.customer_name')
			->from('db_medical_notes mn')
			->join('db_customers c', 'c.id = mn.customer_id', 'left')
			->where('mn.id', (int)$noteId)->where('mn.store_id', $storeId)
			->limit(1)->get()->row();
		if (!$note) return false;
		$changes = array();
		foreach ($this->chartFields() as $column => $spec) {
			$value = $note->{$column} ?? null;
			if (trim((string)($value ?? '')) === '') continue;
			if ($spec['type'] === 'qty' && (float)$value === 0.0) continue;
			$changes[] = mp_audit_change($spec['label'],
				mp_audit_format_value($value, $spec['type']), 'Struck out');
		}
		$meds = $this->shortList($this->chartMedicines($noteId));
		if ($meds) $changes[] = mp_audit_change('Medicines on the chart', implode(', ', $meds), 'Struck out');
		if (!$changes) $changes[] = mp_audit_change('Prescription chart', 'Written', 'Struck out');
		$who = $this->givenName($note->customer_name ?? '');
		return $this->event($storeId, 'dispensing', 'chart_removed', (int)$noteId,
			$who !== '' ? 'Prescription chart for ' . $who : 'Prescription chart',
			$changes, 'Chart dated ' . mp_audit_format_value($note->note_date, 'date')
				. ' was deleted; what it said is recorded here so it can be reconstructed.');
	}

	private function chartFields(){
		return array(
			'diagnosis'          => array('label' => 'Diagnosis', 'type' => 'text'),
			'prescribing_doctor' => array('label' => 'Prescribing doctor', 'type' => 'text'),
			'allergies_flagged'  => array('label' => 'Allergies flagged', 'type' => 'text'),
			'next_refill_date'   => array('label' => 'Next refill due', 'type' => 'date'),
			'refills_remaining'  => array('label' => 'Refills left', 'type' => 'qty'),
			'review_status'      => array('label' => 'Prescription review', 'type' => 'word'),
		);
	}

	/** The medicines a chart prescribes, named. */
	public function chartMedicines($noteId){
		if (!$this->db->table_exists('db_medical_note_items')) return array();
		$rows = $this->db->select('item_name')->where('medical_note_id', (int)$noteId)
			->order_by('id')->get('db_medical_note_items')->result();
		$out = array();
		foreach ($rows as $r) {
			$name = trim(mp_catalogue_name((string)$r->item_name));
			if ($name !== '') $out[] = $name;
		}
		return $out;
	}

	/** A bounded, readable list of names. */
	private function shortList(array $names){
		$names = array_values(array_filter(array_map('trim', $names), function ($n) { return $n !== ''; }));
		if (count($names) <= 3) return $names;
		return array_merge(array_slice($names, 0, 3), array((count($names) - 3) . ' others'));
	}

	/**
	 * A whole bill struck out, and the stock that went back on the shelf with
	 * it. $shelfBefore is item id => quantity on hand as the void began, which
	 * only the caller can know, so it is taken before the lines are flipped.
	 */
	public function billVoided($storeId, $salesId, array $shelfBefore, $reason = '', $module = 'billing'){
		$storeId = (int)$storeId;
		$sale = $this->db->select('sales_code, sales_status, grand_total')
			->where('id', (int)$salesId)->where('store_id', $storeId)->limit(1)->get('db_sales')->row();
		if (!$sale) return false;
		$changes = array(mp_audit_change('Bill status',
			mp_audit_format_value($sale->sales_status, 'word'), 'Voided'));
		if ($this->db->table_exists('db_salesitems')) {
			$lines = $this->db->select('item_id')->distinct()->where('sales_id', (int)$salesId)
				->where('item_id IS NOT NULL')->get('db_salesitems')->result();
			foreach ($lines as $line) {
				$itemId = (int)$line->item_id;
				if ($itemId <= 0 || !isset($shelfBefore[$itemId])) continue;
				$after = $this->shelf_qty($itemId);
				$before = $shelfBefore[$itemId];
				if ($after === null || $before === null || abs((float)$before - (float)$after) < 0.0000001) continue;
				$changes[] = mp_audit_change($this->item_name($itemId),
					mp_audit_format_value($before, 'qty'), mp_audit_format_value($after, 'qty'));
			}
		}
		$reason = trim(mp_catalogue_name((string)$reason));
		$document = trim((string)$sale->sales_code) !== '' ? 'Invoice ' . trim((string)$sale->sales_code) : 'A bill';
		return $this->event($storeId, $module, 'void', (int)$salesId, $document, $changes,
			$reason === '' ? 'Voided with no reason recorded. Charge removed, stock returned.'
				: 'Voided — reason given: ' . $reason . '. Charge removed, stock returned.');
	}

	/**
	 * One line given to the customer free of charge. The stock has gone already
	 * (a poured drink is poured), so only the money moves — and the house needs
	 * to know whose judgement cost it that.
	 */
	public function linesCompelled($storeId, $salesId, array $compelled, $reason = '', $module = 'billing'){
		$sale = $this->db->select('sales_code')->where('id', (int)$salesId)
			->where('store_id', (int)$storeId)->limit(1)->get('db_sales')->row();
		if (!$sale || !$compelled) return false;
		$changes = array();
		foreach ($compelled as $detail) {
			$itemId = (int)($detail['item'] ?? 0);
			$name = $itemId > 0 ? $this->item_name($itemId) : '';
			if ($name === '') $name = 'An item on the bill';
			$changes[] = mp_audit_change($name,
				mp_audit_format_value($detail['charge'] ?? null, 'money'), 'Nothing charged');
		}
		$reason = trim(mp_catalogue_name((string)$reason));
		return $this->event((int)$storeId, $module, 'comp', (int)$salesId,
			trim((string)$sale->sales_code) !== '' ? 'Invoice ' . trim((string)$sale->sales_code) : 'A bill',
			$changes, $reason === '' ? 'Given free of charge with no reason recorded.'
				: 'Given free of charge — reason given: ' . $reason . '.');
	}

	/**
	 * Goods a customer brought back, and the money that went with them.
	 * $shelfBefore is item id => quantity on hand before the return was posted,
	 * which only the caller can know at the right moment.
	 */
	public function goodsReturned($storeId, $returnId, array $shelfBefore, $isUpdate = false){
		$storeId = (int)$storeId;
		$return = $this->db->select('sr.return_code, sr.grand_total, sr.return_note, c.customer_name')
			->from('db_salesreturn sr')
			->join('db_customers c', 'c.id = sr.customer_id', 'left')
			->where('sr.id', (int)$returnId)->where('sr.store_id', $storeId)->limit(1)->get()->row();
		if (!$return) return false;
		$lines = array();
		if ($this->db->table_exists('db_salesitemsreturn')) {
			$rows = $this->db->select('item_id')->distinct()->where('return_id', (int)$returnId)
				->where('item_id IS NOT NULL')->get('db_salesitemsreturn')->result();
			foreach ($rows as $row) {
				$itemId = (int)$row->item_id;
				if ($itemId <= 0 || !isset($shelfBefore[$itemId])) continue;
				$after = $this->shelf_qty($itemId);
				if ($after === null) continue;
				$lines[] = array('item' => $this->item_name($itemId),
					'from' => $shelfBefore[$itemId], 'to' => $after);
			}
		}
		$who = $this->givenName($return->customer_name ?? '');
		$document = trim((string)$return->return_code) !== '' ? 'Return ' . trim((string)$return->return_code) : 'A customer return';
		$note = trim(preg_replace('/\s+/', ' ', mp_catalogue_name((string)($return->return_note ?? ''))));
		$description = ($isUpdate ? 'Corrected on ' : 'Taken back from ') . ($who !== '' ? $who : ' the customer')
			. '; ' . mp_audit_format_value($return->grand_total, 'money') . ' credited.'
			. ($note !== '' ? ' Reason given: ' . mb_substr($note, 0, 160) . '.' : '')
			. $this->saidReturnLots('db_salesreturn_batch_allocations', 'salesreturnitems_id',
				'db_salesitemsreturn', (int)$returnId);
		if (!$lines) {
			return $this->event($storeId, 'returns', $isUpdate ? 'return_corrected' : 'customer_return',
				(int)$returnId, $document,
				array(mp_audit_change('Amount credited', 'Nothing credited',
					mp_audit_format_value($return->grand_total, 'money'))),
			$description . ' No stock came back on this one — the credit is bookkeeping only.');
		}
		return $this->stockMoved($storeId, 'returns', $isUpdate ? 'return_corrected' : 'customer_return',
			(int)$returnId, $document, $lines, $description);
	}

	/**
	 * Goods the shop sent back to its supplier. The units leave the shelf, so
	 * the trail names the supplier, the credit and the batch that gave them up.
	 */
	public function goodsReturnedToSupplier($storeId, $returnId, array $shelfBefore, $isUpdate = false){
		$storeId = (int)$storeId;
		$return = $this->db->select('pr.return_code, pr.grand_total, pr.return_note, s.supplier_name')
			->from('db_purchasereturn pr')
			->join('db_suppliers s', 's.id = pr.supplier_id', 'left')
			->where('pr.id', (int)$returnId)->where('pr.store_id', $storeId)->limit(1)->get()->row();
		if (!$return) return false;
		$lines = array();
		if ($this->db->table_exists('db_purchaseitemsreturn')) {
			$rows = $this->db->select('item_id')->distinct()->where('return_id', (int)$returnId)
				->where('item_id IS NOT NULL')->get('db_purchaseitemsreturn')->result();
			foreach ($rows as $row) {
				$itemId = (int)$row->item_id;
				if ($itemId <= 0 || !isset($shelfBefore[$itemId])) continue;
				$after = $this->shelf_qty($itemId);
				if ($after === null) continue;
				$lines[] = array('item' => $this->item_name($itemId),
					'from' => $shelfBefore[$itemId], 'to' => $after);
			}
		}
		$supplier = trim(mp_catalogue_name((string)($return->supplier_name ?? '')));
		$code = trim((string)$return->return_code);
		$document = $code !== '' ? 'Supplier return ' . $code : 'A supplier return';
		$note = trim(preg_replace('/\s+/', ' ', mp_catalogue_name((string)($return->return_note ?? ''))));
		$description = 'Sent back' . ($supplier !== '' ? ' to ' . $supplier : '')
			. '; ' . mp_audit_format_value($return->grand_total, 'money') . ' taken off what the shop owes.'
			. ($note !== '' ? ' Reason given: ' . mb_substr($note, 0, 160) . '.' : '')
			. $this->saidReturnLots('db_purchaseitemsreturn_batch_allocations', 'purchaseitemsreturn_id',
				'db_purchaseitemsreturn', (int)$returnId);
		if (!$lines) return false;
		return $this->stockMoved($storeId, 'procurement', $isUpdate ? 'supplier_return_corrected' : 'supplier_return',
			(int)$returnId, $document, $lines, $description);
	}

	/**
	 * Return documents deleted. The credit they carried is cancelled and the
	 * units that had come back leave the shelf again, so the trail has to say
	 * both — a deleted return is otherwise just a missing refund.
	 * $shelfBefore is item id => quantity on hand while the returns still stood.
	 */
	public function returnsDeleted($storeId, array $returnIds, array $shelfBefore, $kind = 'customer'){
		$isSupplier = $kind === 'supplier';
		$table = $isSupplier ? 'db_purchasereturn' : 'db_salesreturn';
		$returnIds = array_values(array_unique(array_filter(array_map('intval', $returnIds))));
		if (!$returnIds) return false;
		// A head-office operator may delete another outlet's paperwork; the entry
		// still has to belong to the outlet the goods are in.
		$storeId = (int)$storeId;
		if ($storeId <= 0) {
			$home = $this->db->select('store_id')->where_in('id', $returnIds)->limit(1)->get($table)->row();
			$storeId = $home ? (int)$home->store_id : 0;
		}
		if ($storeId <= 0) return false;
		$rows = $this->db->select('id, return_code, grand_total')
			->where_in('id', $returnIds)->where('store_id', $storeId)
			->get($table)->result();
		if (!$rows) return false;
		$codes = array(); $credited = 0.0;
		foreach ($rows as $r) {
			$codes[] = trim((string)$r->return_code) !== '' ? trim((string)$r->return_code) : ('#' . (int)$r->id);
			$credited += (float)$r->grand_total;
		}
		$lines = array();
		foreach ($shelfBefore as $itemId => $before) {
			$after = $this->shelf_qty($itemId);
			if ($after === null || $before === null) continue;
			if (abs((float)$before - (float)$after) < 0.0000001) continue;
			$lines[] = array('item' => $this->item_name($itemId), 'from' => $before, 'to' => $after);
		}
		$saidCodes = $this->shortList($codes);
		$document = count($codes) === 1
			? ($isSupplier ? 'Supplier return ' : 'Return ') . $saidCodes[0]
			: count($codes) . ($isSupplier ? ' supplier returns' : ' customer returns');
		$module = $isSupplier ? 'procurement' : 'returns';
		$description = 'Deleted — the ' . mp_audit_format_value($credited, 'money')
			. ' credit carried by ' . $document . ' was reversed'
			. ($lines ? ', and the units moved on the shelf again.' : ', with no stock movement recorded.');
		if (!$lines) {
			return $this->event($storeId, $module, 'return_deleted', $returnIds[0], $document,
				array(mp_audit_change('Amount credited', mp_audit_format_value($credited, 'money'), 'Nothing credited')),
				$description);
		}
		return $this->stockMoved($storeId, $module, 'return_deleted', $returnIds[0], $document, $lines, $description);
	}

	/**
	 * Which batches the returned units actually went back into. Said as a
	 * sentence, never as row ids — a reviewer must be able to find the lot on
	 * the batch screen from the words alone.
	 */
	private function saidReturnLots($allocTable, $lineColumn, $itemsTable, $returnId){
		if (!$this->db->table_exists($allocTable) || !$this->db->table_exists($itemsTable)
			|| !$this->db->table_exists('db_item_barcodes')) return '';
		$rows = $this->db->select('b.batch_lot, b.expire_date')
			->from($allocTable . ' a')
			->join('db_item_barcodes b', 'b.id = a.barcode_id', 'inner')
			->join($itemsTable . ' li', 'li.id = a.' . $lineColumn, 'inner')
			->where('li.return_id', (int)$returnId)
			->get()->result();
		$names = array();
		foreach ($rows as $r) {
			$lot = trim(mp_catalogue_name((string)$r->batch_lot));
			if ($lot === '') continue;
			$names[] = $lot . (!empty($r->expire_date) ? ' (expires ' . mp_audit_format_value($r->expire_date, 'date') . ')' : '');
		}
		$names = $this->shortList(array_unique($names));
		if (!$names) return '';
		return ' Went back into batch ' . implode(', ', $names) . '. Where those units sit now is on the batch screen.';
	}

	/** One line of the medicine ledger: a batch, what it holds, when it ends. */
	public function lotLine($batch, $unitsFrom = null, $unitsTo = null, $expiryFrom = null, $expiryTo = null){
		$batch = trim(mp_catalogue_name((string)$batch));
		if ($batch === '') return null;
		$out = array();
		if ($unitsFrom !== null && $unitsTo !== null && $unitsFrom !== $unitsTo) {
			$out[] = mp_audit_change('Units in batch ' . $batch,
				mp_audit_format_value($unitsFrom, 'qty'), mp_audit_format_value($unitsTo, 'qty'));
		}
		if ($expiryFrom !== null && $expiryTo !== null
			&& trim((string)$expiryFrom) !== trim((string)$expiryTo)) {
			$out[] = mp_audit_change('Expiry date on batch ' . $batch,
				mp_audit_format_value($expiryFrom, 'date'), mp_audit_format_value($expiryTo, 'date'));
		}
		return $out;
	}
}
