<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Atomic, store/warehouse-scoped batch allocation for products sold by lot. */
class Inventory_rotation_model extends CI_Model {
    const MANUAL = 'MANUAL';
    const FEFO = 'FEFO';
    const FIFO = 'FIFO';
    const HOLDS_TABLE = 'db_onlineorder_batch_allocations';

    public function method($storeId = null) {
        $storeId = (int)($storeId ?: get_current_store_id());
        if (!$this->db->table_exists('db_inventory_rotation_settings')) return self::MANUAL;
        $row = $this->db->where('store_id', $storeId)->get('db_inventory_rotation_settings')->row();
        $method = strtoupper((string)($row->rotation_method ?? self::MANUAL));
        return in_array($method, [self::FEFO, self::FIFO], true) ? $method : self::MANUAL;
    }

    /**
     * Turning batch selling on is the one switch that can stop a counter by
     * itself: under FEFO/FIFO a product only sells from its lots, so every
     * item whose lots disagree with its stock figure blocks on the first sale.
     * The reconciliation screen is the tool that fixes the numbers, so this
     * guard points at it and requires a deliberate second look before the
     * switch lands. When the reconciliation tool is not installed yet there
     * are no numbers to quote, and the switch stays available.
     *
     * @param bool $acknowledged the merchant saw the gaps and chose to proceed
     * @return array ['ok' => bool, 'gaps' => int, 'message' => string]
     */
    public function switch_guard($storeId, $method, $acknowledged = false) {
        $storeId = (int)$storeId;
        $method = strtoupper(trim((string)$method));
        if (!in_array($method, [self::MANUAL, self::FEFO, self::FIFO], true)) {
            return ['ok' => false, 'gaps' => 0, 'message' => 'That batch rotation method is not one of the choices.'];
        }
        if ($method === self::MANUAL || $this->method($storeId) === $method) {
            return ['ok' => true, 'gaps' => 0, 'message' => ''];
        }
        if (!$this->db->table_exists('db_batch_reconciliations')
            || !$this->db->table_exists('db_item_barcodes')
            || !$this->db->table_exists('db_warehouseitems')) {
            return ['ok' => true, 'gaps' => 0, 'message' => ''];
        }
        $this->load->model('batch_reconciliation_model', 'switch_recon');
        $gaps = (int)$this->switch_recon->readiness($storeId)['gaps'];
        if ($gaps === 0 || $acknowledged) return ['ok' => true, 'gaps' => $gaps, 'message' => ''];
        return ['ok' => false, 'gaps' => $gaps, 'message' => sprintf(
            'Automatic batch selling would stop %s %s at the counter, because their batch quantities do not match their stock figures. Open Batch reconciliation to settle them first, or tick "Switch anyway" to take that risk now.',
            $gaps, $gaps === 1 ? 'product' : 'products'
        )];
    }

    public function save_method($storeId, $method, $userId = null) {
        $storeId = (int)$storeId;
        $method = strtoupper(trim((string)$method));
        if ($storeId <= 0 || !in_array($method, [self::MANUAL, self::FEFO, self::FIFO], true)) return false;
        if (!$this->db->table_exists('db_inventory_rotation_settings')) return false;
        $existing = $this->db->where('store_id', $storeId)->get('db_inventory_rotation_settings')->row();
        $data = ['rotation_method' => $method, 'updated_by' => $userId ? (int)$userId : null];
        $saved = $existing
            ? $this->db->where('store_id', $storeId)->update('db_inventory_rotation_settings', $data)
            : $this->db->insert('db_inventory_rotation_settings', ['store_id' => $storeId] + $data);
        if ($saved && (!$existing || strtoupper(trim((string)$existing->rotation_method)) !== $method)) {
            $this->_audit_method($storeId, $existing ? $existing->rotation_method : null, $method, $userId);
        }
        return $saved;
    }

    /**
     * Which batch leaves the shelf is a selling policy, and switching it can
     * block the counter on its own — so the change belongs in the trail with
     * both rule names in it, whoever made it.
     */
    private function _audit_method($storeId, $from, $to, $userId = null){
        if (!function_exists('mp_audit_log')) return;
        try {
            $words = array(
                self::MANUAL => 'Chosen at the counter',
                self::FEFO   => 'The batch that runs out first leaves first',
                self::FIFO   => 'The oldest stock leaves first',
            );
            $said = function ($value) use ($words) {
                $key = strtoupper(trim((string)$value));
                if ($key === '') return 'no rule set yet';
                return isset($words[$key]) ? $words[$key] : mp_code_label($key);
            };
            $capture = mp_audit_capture();
			if (!$capture) return;
            $capture->event($storeId, 'settings', 'rotation_method', 'rotation',
                'How stock leaves the shelf',
                array(mp_audit_change('Rotation rule', $said($from), $said($to))), null, $userId);
        } catch (Throwable $e) {
            log_message('error', 'Rotation rule audit capture failed: ' . $e->getMessage());
        }
    }

    /**
     * One spelling of "a lot this shop may sell right now". The counter, the
     * reconciliation screen and the online storefront all read these rules, so
     * the quantity a shopper is offered is the quantity the till can actually
     * draw. Any drift here would let the website promise stock the counter then
     * refuses to hand over.
     *
     * $alias qualifies the columns for a query that joins the lot ledger to
     * another table — db_items carries its own batch_lot, expire_date and
     * status, so an unqualified rule reads as ambiguous there and the report
     * would silently come back empty.
     *
     * @return array ['real' => lot rows that hold batch stock,
     *               'eligible' => ... and are sellable today]
     */
    public function lot_rules($storeId, $alias = '') {
        $alias = preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', (string)$alias) ? (string)$alias : '';
        $p = $alias === '' ? '' : $alias . '.';
        $real = "(({$p}batch_lot IS NOT NULL AND {$p}batch_lot <> '') OR {$p}expire_date IS NOT NULL)"
            . " AND ({$p}serial_number IS NULL OR {$p}serial_number = '')"
            . " AND ({$p}imei_number IS NULL OR {$p}imei_number = '')";
        $eligible = "{$p}status = 1 AND {$p}qty > 0 AND " . $real;
        if ($this->blocks_expired_sales((int)$storeId)) {
            $eligible .= " AND ({$p}expire_date IS NULL OR {$p}expire_date >= '" . date('Y-m-d') . "')";
        }
        return ['real' => $real, 'eligible' => $eligible];
    }

    public function blocks_expired_sales($storeId) {
        if (!$this->db->table_exists('db_expiry_settings')) return false;
        $this->load->model('expiry_settings_model', 'rotation_expiry_settings');
        $settings = $this->rotation_expiry_settings->get_settings((int)$storeId);
        return !empty($settings->stop_selling_expired);
    }

    /**
     * Is this store selling from the batch ledger at all? Manual rotation, or
     * an install without the batch tables, keeps the older aggregate-only
     * behaviour everywhere — which is what every non-batch workflow expects.
     */
    public function sells_from_lot_ledger($storeId = null) {
        $storeId = (int)($storeId ?: get_current_store_id());
        if ($this->method($storeId) === self::MANUAL) return false;
        return $this->db->table_exists('db_item_barcodes');
    }

    /**
     * Sellable quantity per item, read straight off the lot ledger for the
     * named locations. An item with no eligible lot answers zero: that is the
     * wall the counter would hit, not a display choice, and the batch
     * reconciliation screen is the tool that settles it.
     *
     * @return array [item_id => float]
     */
    public function sellable_map($storeId, array $warehouseIds, array $itemIds) {
        $out = [];
        $warehouseIds = array_values(array_unique(array_map('intval', $warehouseIds)));
        $itemIds = array_values(array_unique(array_filter(array_map('intval', $itemIds))));
        if (!$warehouseIds || !$itemIds || !$this->db->table_exists('db_item_barcodes')) return $out;
        $rules = $this->lot_rules((int)$storeId);
        $sql = "SELECT item_id, SUM(qty) AS sellable FROM db_item_barcodes"
            . " WHERE " . $rules['eligible']
            . " AND warehouse_id IN (" . implode(',', array_fill(0, count($warehouseIds), '?')) . ")"
            . " AND item_id IN (" . implode(',', array_fill(0, count($itemIds), '?')) . ")"
            . " GROUP BY item_id";
        $query = $this->db->query($sql, array_merge($warehouseIds, $itemIds));
        foreach (($query ? $query->result() : []) as $row) $out[(int)$row->item_id] = round((float)$row->sellable, 4);
        return $out;
    }

    public function sellable_qty($storeId, array $warehouseIds, $itemId) {
        $map = $this->sellable_map($storeId, $warehouseIds, [(int)$itemId]);
        return (float)($map[(int)$itemId] ?? 0);
    }

    /**
     * Decrement one or more batch rows and persist the exact sale allocation.
     * Caller must already be inside the sale transaction.
     * Returns empty string on success, otherwise a user-readable blocking error.
     */
    public function allocate_sale_line($saleId, $saleItemId, $storeId, $warehouseId, $itemId, $baseQty, $serviceBit = 0) {
        $method = $this->method($storeId);
        if ($method === self::MANUAL || (int)$serviceBit === 1) return '';
        if ((int)$warehouseId <= 0) return 'Select a stock location before using automatic batch rotation.';
        if (!$this->db->table_exists('db_item_barcodes') || !$this->db->table_exists('db_salesitem_batch_allocations')) {
            return 'Batch rotation is not installed yet. Keep rotation on Manual until the update is applied.';
        }
        $need = round((float)$baseQty, 4);
        if ($need <= 0) return '';

        // Lock candidates in deterministic rotation order. The explicit
        // expired-stock preference remains authoritative for both methods.
        $batches = $this->_candidate_lots($storeId, $warehouseId, $itemId, $method);
        if ($batches === null) return 'Could not check batch availability. No stock was changed.';
        $available = 0.0;
        foreach ($batches as $batch) $available += (float)$batch->qty;
        if ($available + 0.00001 < $need) {
            return 'Batch stock is short for this item (need ' . format_qty($need) . ', eligible batches ' . format_qty($available) . '). Reconcile the batch quantities or switch rotation to Manual before selling.';
        }

        $remaining = $need;
        foreach ($batches as $batch) {
            if ($remaining <= 0.00001) break;
            $take = min($remaining, (float)$batch->qty);
            // Conditional update is a second guard if the storage engine or
            // transaction isolation did not honor the row lock as expected.
            // status is assigned before qty so MySQL evaluates its condition
            // against the pre-update quantity; otherwise left-to-right
            // assignment can subtract twice when deciding whether to disable.
            $updated = $this->db->query(
                'UPDATE db_item_barcodes SET status = IF(qty - ? <= 0.00001, 0, 1), qty = qty - ? WHERE id = ? AND status = 1 AND qty >= ?',
                [$take, $take, (int)$batch->id, $take]
            );
            if (!$updated || $this->db->affected_rows() !== 1) {
                return 'A batch changed during checkout. Please retry the sale; no partial sale will be committed.';
            }
            $inserted = $this->db->insert('db_salesitem_batch_allocations', [
                'sales_id' => (int)$saleId,
                'salesitems_id' => (int)$saleItemId,
                'store_id' => (int)$storeId,
                'warehouse_id' => (int)$warehouseId,
                'item_id' => (int)$itemId,
                'barcode_id' => (int)$batch->id,
                'batch_lot' => $batch->batch_lot,
                'expire_date' => $batch->expire_date ?: null,
                'qty_allocated' => $take,
                'rotation_method' => $method,
            ]);
            if (!$inserted) return 'Could not record the batch allocation. Please retry the sale.';
            $remaining -= $take;
        }
        return $remaining > 0.00001 ? 'Not enough batch stock was allocated. Please retry the sale.' : '';
    }

    /**
     * Consume the exact batch row a clerk scanned instead of letting rotation
     * pick a lot. Without this, a scanned lot would be consumed from the pool
     * by FEFO/FIFO and then retired wholesale by the till, losing its stock.
     * Caller must already be inside the sale transaction.
     */
    public function consume_chosen_batch($saleId, $saleItemId, $storeId, $warehouseId, $itemId, $barcodeId, $baseQty, $itemName = 'item') {
        if (!$this->db->table_exists('db_item_barcodes') || !$this->db->table_exists('db_salesitem_batch_allocations')) {
            return 'Batch rotation is not installed yet. Keep rotation on Manual until the update is applied.';
        }
        $need = round((float)$baseQty, 4);
        if ($need <= 0) return '';
        $row = $this->db->where('id', (int)$barcodeId)->get('db_item_barcodes')->row();
        if (!$row || (int)$row->item_id !== (int)$itemId || (int)$row->warehouse_id !== (int)$warehouseId) {
            return "The scanned unit of {$itemName} does not belong to this stock location. Please rescan it.";
        }
        if (!$this->location_belongs_to_store($storeId, $warehouseId)) {
            return "This stock location does not belong to the store taking the sale, so {$itemName} was not sold. Please pick the right counter.";
        }
        $this->load->model('expiry_settings_model', 'rotation_expiry_settings');
        $expirySettings = $this->rotation_expiry_settings->get_settings($storeId);
        if (!empty($expirySettings->stop_selling_expired) && is_valid_date($row->expire_date ?? null) && $row->expire_date < date('Y-m-d')) {
            return 'The scanned batch of ' . $itemName . ' expired on ' . $row->expire_date . ' and cannot be sold.';
        }
        if ((float)$row->qty + 0.00001 < $need) {
            return 'The scanned batch of ' . $itemName . ' holds only ' . format_qty((float)$row->qty) . ' but this line needs ' . format_qty($need) . '. Reconcile the batch quantity or sell the remaining amount on its own line.';
        }
        $updated = $this->db->query(
            'UPDATE db_item_barcodes SET status = IF(qty - ? <= 0.00001, 0, 1), qty = qty - ? WHERE id = ? AND status = 1 AND qty >= ?',
            [$need, $need, (int)$barcodeId, $need]
        );
        if (!$updated || $this->db->affected_rows() !== 1) {
            return 'A batch changed during checkout. Please retry the sale; no partial sale will be committed.';
        }
        $inserted = $this->db->insert('db_salesitem_batch_allocations', [
            'sales_id' => (int)$saleId,
            'salesitems_id' => (int)$saleItemId,
            'store_id' => (int)$storeId,
            'warehouse_id' => (int)$warehouseId,
            'item_id' => (int)$itemId,
            'barcode_id' => (int)$barcodeId,
            'batch_lot' => $row->batch_lot,
            'expire_date' => $row->expire_date ?: null,
            'qty_allocated' => $need,
            'rotation_method' => 'SCANNED',
        ]);
        return $inserted ? '' : 'Could not record the batch allocation. Please retry the sale.';
    }

    /** Restore allocations when a sale is edited/deleted; caller owns transaction. */
    public function restore_sale($saleId) {
        if (!$this->db->table_exists('db_salesitem_batch_allocations')) return true;
        $allocations = $this->db->where('sales_id', (int)$saleId)->get('db_salesitem_batch_allocations')->result();
        foreach ($allocations as $allocation) {
            $qty = max(0, (float)$allocation->qty_allocated - (float)$allocation->qty_returned);
            if ($qty > 0) {
                $restored = $this->db->query($this->_put_back_sql(), [$qty, (int)$allocation->barcode_id]);
                if (!$restored || $this->db->affected_rows() !== 1) return false;
            }
        }
        return $this->db->where('sales_id', (int)$saleId)->delete('db_salesitem_batch_allocations');
    }

    /**
     * The one way a quantity goes back on the shelf.
     *
     * A lot the shop sealed (status 3: quarantined by a recall) stays sealed when
     * a refund, a void, a deleted invoice or an undone return moves its units back.
     * Recalled medicine that physically came off a customer must not reappear as
     * sellable stock because some other document touched its quantity; only the
     * recall screen's release, or a recorded reconciliation decision, may reopen it.
     */
    private function _put_back_sql() {
        return 'UPDATE db_item_barcodes SET qty = qty + ?, status = IF(status = 3, 3, 1) WHERE id = ?';
    }

    /**
     * Give a voided sale's stock back without erasing its history.
     *
     * restore_sale() is for deleting an invoice, so it drops the allocation
     * rows. A void keeps them: the round was genuinely rung up and genuinely
     * came back, and the bar needs both halves of that story. Each allocation
     * is marked fully returned, which also makes a retry safe — the second pass
     * finds nothing unreturned and moves no quantity twice.
     */
    public function give_back_sale_stock($saleId) {
        if (!$this->db->table_exists('db_salesitem_batch_allocations')) return true;
        if (!$this->db->field_exists('qty_returned', 'db_salesitem_batch_allocations')) return true;
        $allocations = $this->db->where('sales_id', (int)$saleId)->get('db_salesitem_batch_allocations')->result();
        foreach ($allocations as $allocation) {
            $qty = max(0, (float)$allocation->qty_allocated - (float)$allocation->qty_returned);
            if ($qty <= 0) continue;
            $restored = $this->db->query($this->_put_back_sql(), [$qty, (int)$allocation->barcode_id]);
            if (!$restored || $this->db->affected_rows() !== 1) return false;
            $marked = $this->db->where('id', (int)$allocation->id)
                ->update('db_salesitem_batch_allocations', ['qty_returned' => $allocation->qty_allocated]);
            if (!$marked) return false;
        }
        return true;
    }

    /** Return sale stock to its original batches, not an arbitrary new batch. */
    public function return_sale_qty($saleId, $returnItemId, $itemId, $baseQty, $returnStatus = 'Return') {
        if (!$this->db->table_exists('db_salesitem_batch_allocations')) return '';
        $hasSourceAllocation = $this->db->where('sales_id', (int)$saleId)
            ->where('item_id', (int)$itemId)
            ->count_all_results('db_salesitem_batch_allocations');
        // Historical invoices created before rotation was enabled have no
        // batch provenance; keep their legacy return path unchanged.
        if (!$hasSourceAllocation) return '';
        $sale = $this->db->select('store_id')->where('id', (int)$saleId)->get('db_sales')->row();
        $method = $this->method($sale->store_id ?? get_current_store_id());
        if (strtolower((string)$returnStatus) !== 'return') {
            return 'This pharmacy return must be inspected and recorded as a regular Return before it can re-enter sellable batch stock.';
        }
        $remaining = round((float)$baseQty, 4);
        $allocations = $this->db->query(
            'SELECT * FROM db_salesitem_batch_allocations WHERE sales_id = ? AND item_id = ? AND qty_allocated > qty_returned ORDER BY id ASC FOR UPDATE',
            [(int)$saleId, (int)$itemId]
        )->result();
        foreach ($allocations as $allocation) {
            if ($remaining <= 0.00001) break;
            $available = (float)$allocation->qty_allocated - (float)$allocation->qty_returned;
            $restore = min($remaining, $available);
            $batchUpdated = $this->db->query($this->_put_back_sql(), [$restore, (int)$allocation->barcode_id]);
            $allocationUpdated = $this->db->where('id', (int)$allocation->id)->set('qty_returned', 'qty_returned + '.(float)$restore, false)->update('db_salesitem_batch_allocations');
            if (!$batchUpdated || !$allocationUpdated) return 'Could not update the returned batch quantity. Please retry.';
            if (!$this->db->insert('db_salesreturn_batch_allocations', [
                'salesreturnitems_id' => (int)$returnItemId,
                'allocation_id' => (int)$allocation->id,
                'barcode_id' => (int)$allocation->barcode_id,
                'qty_returned' => $restore,
                'return_status' => 'Return',
            ])) return 'Could not record the returned batch. Please retry.';
            $remaining -= $restore;
        }
        return $remaining > 0.00001
            ? 'The source sale does not have enough unreturned batch allocations. No return was committed.'
            : '';
    }

    /** Undo batch quantities when an existing return is edited or deleted. */
    public function undo_return($returnId) {
        if (!$this->db->table_exists('db_salesreturn_batch_allocations')) return true;
        $rows = $this->db->where('salesreturnitems_id', (int)$returnId)->get('db_salesreturn_batch_allocations')->result();
        foreach ($rows as $row) {
            $batchUpdated = $this->db->query('UPDATE db_item_barcodes SET status = IF(status = 3, 3, IF(qty - ? <= 0.00001, 0, 1)), qty = qty - ? WHERE id = ? AND qty >= ?', [(float)$row->qty_returned, (float)$row->qty_returned, (int)$row->barcode_id, (float)$row->qty_returned]);
            if (!$batchUpdated || $this->db->affected_rows() !== 1) return false;
            $allocationUpdated = $this->db->query('UPDATE db_salesitem_batch_allocations SET qty_returned = GREATEST(0, qty_returned - ?) WHERE id = ? AND qty_returned >= ?', [(float)$row->qty_returned, (int)$row->allocation_id, (float)$row->qty_returned]);
            if (!$allocationUpdated) return false;
        }
        return $this->db->where('salesreturnitems_id', (int)$returnId)->delete('db_salesreturn_batch_allocations');
    }

    /**
     * The lots this item could be sold from at one location, in rotation order,
     * locked for the caller's transaction. Returns null when the read failed so
     * the caller can refuse without touching a single quantity.
     */
    private function _candidate_lots($storeId, $warehouseId, $itemId, $method = null) {
        if (!$this->location_belongs_to_store($storeId, $warehouseId)) return [];
        $method = $method ?: $this->method($storeId);
        $order = $method === self::FEFO
            ? ' (expire_date IS NULL) ASC, expire_date ASC, created_date ASC, id ASC '
            : ' created_date ASC, id ASC ';
        $sql = "SELECT id, item_id, warehouse_id, batch_lot, expire_date, qty"
            . " FROM db_item_barcodes"
            . " WHERE item_id = ? AND warehouse_id = ? AND " . $this->lot_rules((int)$storeId)['eligible']
            . " ORDER BY {$order} FOR UPDATE";
        $query = $this->db->query($sql, [(int)$itemId, (int)$warehouseId]);
        return $query ? $query->result() : null;
    }

    /**
     * Does this stock location belong to this shop?
     *
     * The lot ledger has no store column: a batch is tied to a shop only through
     * its warehouse. Every caller already picks the location out of the store's
     * own list, so this is the second gate — a mis-wired or tampered location id
     * must never let one pharmacy hold or sell another pharmacy's medicine.
     */
    public function location_belongs_to_store($storeId, $warehouseId) {
        $storeId = (int)$storeId; $warehouseId = (int)$warehouseId;
        if ($warehouseId <= 0) return false;
        if (!$this->db->table_exists('db_warehouse')) return true;
        $query = $this->db->select('id')->where('id', $warehouseId)->where('store_id', $storeId)->get('db_warehouse');
        return (bool)($query && $query->num_rows() > 0);
    }

    /**
     * Hold batches for an online order that has not been rung up yet.
     *
     * An online reservation already moves the shop's stock figure and the
     * branch's own count; without this it left the batch ledger untouched, so
     * the counter could sell units a shopper had already reserved — and the
     * paid order then had no lot to draw when it reached the ledger. Holding
     * here means both channels draw from one pile, whichever arrives first.
     * Caller must already be inside the order transaction.
     *
     * @return string '' when the hold is placed, otherwise a plain-English refusal
     */
    public function reserve_lots($orderId, $orderItemId, $storeId, $warehouseId, $itemId, $need, $itemName = 'this product') {
        if (!$this->holds_installed()) return 'Online batch holding is not installed on this store yet. Please try again after the update has been applied.';
        $method = $this->method($storeId);
        if ($method === self::MANUAL) return '';
        if ((int)$warehouseId <= 0) return 'This item cannot be ordered online yet because its stock location is not set. Please contact the store.';
        $need = round((float)$need, 4);
        if ($need <= 0) return '';

        $batches = $this->_candidate_lots($storeId, $warehouseId, $itemId, $method);
        if ($batches === null) return 'Could not check the shelf for ' . $itemName . '. Nothing was ordered.';
        $available = 0.0;
        foreach ($batches as $batch) $available += (float)$batch->qty;
        if ($available + 0.00001 < $need) {
            return $available <= 0.00001
                ? $itemName . ' is out of stock. Nothing was added to your order.'
                : $itemName . ' has only ' . format_qty($available) . ' left on the shelf, but this order asked for ' . format_qty($need) . '. Please reduce the quantity.';
        }

        $remaining = $need;
        foreach ($batches as $batch) {
            if ($remaining <= 0.00001) break;
            $take = min($remaining, (float)$batch->qty);
            $updated = $this->db->query(
                'UPDATE db_item_barcodes SET status = IF(qty - ? <= 0.00001, 0, 1), qty = qty - ? WHERE id = ? AND status = 1 AND qty >= ?',
                [$take, $take, (int)$batch->id, $take]
            );
            if (!$updated || $this->db->affected_rows() !== 1) {
                return 'The shelf changed while ' . $itemName . ' was being added to the order. Please try again.';
            }
            $inserted = $this->db->insert(self::HOLDS_TABLE, [
                'online_order_id' => (int)$orderId,
                'online_order_item_id' => (int)$orderItemId,
                'store_id' => (int)$storeId,
                'warehouse_id' => (int)$warehouseId,
                'item_id' => (int)$itemId,
                'barcode_id' => (int)$batch->id,
                'batch_lot' => $batch->batch_lot,
                'expire_date' => $batch->expire_date ?: null,
                'qty_allocated' => $take,
                'rotation_method' => $method,
            ]);
            if (!$inserted) return 'Could not record the reserved batch for ' . $itemName . '. Please try again.';
            $remaining -= $take;
        }
        return $remaining > 0.00001 ? 'Not enough stock could be reserved for ' . $itemName . '. Please try again.' : '';
    }

    public function holds_installed() {
        return $this->db->table_exists(self::HOLDS_TABLE) && $this->db->table_exists('db_item_barcodes');
    }

    public function holds_for_order($orderId, $orderItemId = null) {
        if (!$this->holds_installed()) return [];
        $this->db->where('online_order_id', (int)$orderId);
        if ((int)$orderItemId > 0) $this->db->where('online_order_item_id', (int)$orderItemId);
        $query = $this->db->get(self::HOLDS_TABLE);
        return $query ? $query->result() : [];
    }

    public function has_holds($orderId, $orderItemId = null) {
        return (bool)$this->holds_for_order($orderId, $orderItemId);
    }

    public function held_qty($orderId, $itemId = null) {
        $total = 0.0;
        foreach ($this->holds_for_order($orderId) as $hold) {
            if ((int)$itemId > 0 && (int)$hold->item_id !== (int)$itemId) continue;
            $total += (float)$hold->qty_allocated;
        }
        return round($total, 4);
    }

    /**
     * Turn one order line's held batches into the sale's batch provenance with
     * no quantity movement — the hold took the units off the lot when the order
     * was placed. Afterwards the sale is indistinguishable from a till sale for
     * returns, voids, recalls and reports.
     */
    public function convert_line_holds($orderId, $saleId, $orderItemId, $salesItemId) {
        $holds = $this->holds_for_order($orderId, $orderItemId);
        foreach ($holds as $hold) {
            $inserted = $this->db->insert('db_salesitem_batch_allocations', [
                'sales_id' => (int)$saleId,
                'salesitems_id' => (int)$salesItemId,
                'store_id' => (int)$hold->store_id,
                'warehouse_id' => (int)$hold->warehouse_id,
                'item_id' => (int)$hold->item_id,
                'barcode_id' => (int)$hold->barcode_id,
                'batch_lot' => $hold->batch_lot,
                'expire_date' => $hold->expire_date ?: null,
                'qty_allocated' => (float)$hold->qty_allocated,
                'rotation_method' => $hold->rotation_method,
            ]);
            if (!$inserted) return 'Could not record the batch allocation for this order. Please retry.';
            $deleted = $this->db->where('id', (int)$hold->id)->delete(self::HOLDS_TABLE);
            if (!$deleted) return 'Could not close the batch reservation for this order. Please retry.';
        }
        return '';
    }

    /**
     * Put an order's held batches back on the shelf (cancelled, expired or
     * refunded before the ledger was posted). Idempotent: the rows go with the
     * quantities, so a retry moves nothing twice. A lot under recall quarantine
     * stays quarantined — returning units to a shelf the recall sealed off would
     * put recalled medicine back in circulation.
     */
    public function release_holds($orderId) {
        $holds = $this->holds_for_order($orderId);
        foreach ($holds as $hold) {
            $qty = max(0, (float)$hold->qty_allocated);
            if ($qty <= 0) continue;
            $restored = $this->db->query($this->_put_back_sql(), [$qty, (int)$hold->barcode_id]);
            if (!$restored || $this->db->affected_rows() !== 1) return false;
        }
        if (!$holds) return true;
        return (bool)$this->db->where('online_order_id', (int)$orderId)->delete(self::HOLDS_TABLE);
    }
}
