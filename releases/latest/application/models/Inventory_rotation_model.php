<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Atomic, store/warehouse-scoped batch allocation for pharmacy sales. */
class Inventory_rotation_model extends CI_Model {
    const MANUAL = 'MANUAL';
    const FEFO = 'FEFO';
    const FIFO = 'FIFO';

    public function method($storeId = null) {
        $storeId = (int)($storeId ?: get_current_store_id());
        if (!$this->db->table_exists('db_inventory_rotation_settings')) return self::MANUAL;
        $row = $this->db->where('store_id', $storeId)->get('db_inventory_rotation_settings')->row();
        $method = strtoupper((string)($row->rotation_method ?? self::MANUAL));
        return in_array($method, [self::FEFO, self::FIFO], true) ? $method : self::MANUAL;
    }

    public function save_method($storeId, $method, $userId = null) {
        $storeId = (int)$storeId;
        $method = strtoupper(trim((string)$method));
        if ($storeId <= 0 || !in_array($method, [self::MANUAL, self::FEFO, self::FIFO], true)) return false;
        if (!$this->db->table_exists('db_inventory_rotation_settings')) return false;
        $existing = $this->db->where('store_id', $storeId)->get('db_inventory_rotation_settings')->row();
        $data = ['rotation_method' => $method, 'updated_by' => $userId ? (int)$userId : null];
        if ($existing) return $this->db->where('store_id', $storeId)->update('db_inventory_rotation_settings', $data);
        return $this->db->insert('db_inventory_rotation_settings', ['store_id' => $storeId] + $data);
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
        $order = $method === self::FEFO
            ? ' (expire_date IS NULL) ASC, expire_date ASC, created_date ASC, id ASC '
            : ' created_date ASC, id ASC ';
        $sql = "SELECT id, item_id, warehouse_id, batch_lot, expire_date, qty
                FROM db_item_barcodes
                WHERE item_id = ? AND warehouse_id = ? AND status = 1 AND qty > 0
                  AND ((batch_lot IS NOT NULL AND batch_lot <> '') OR expire_date IS NOT NULL)
                  AND (serial_number IS NULL OR serial_number = '')
                  AND (imei_number IS NULL OR imei_number = '')";
        $this->load->model('expiry_settings_model', 'rotation_expiry_settings');
        $expirySettings = $this->rotation_expiry_settings->get_settings($storeId);
        $params = [(int)$itemId, (int)$warehouseId];
        if (!empty($expirySettings->stop_selling_expired)) {
            $sql .= " AND (expire_date IS NULL OR expire_date >= ?)";
            $params[] = date('Y-m-d');
        }
        $sql .= " ORDER BY {$order} FOR UPDATE";
        $query = $this->db->query($sql, $params);
        if (!$query) return 'Could not check pharmacy batch availability. No stock was changed.';
        $batches = $query->result();
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

    /** Restore allocations when a sale is edited/deleted; caller owns transaction. */
    public function restore_sale($saleId) {
        if (!$this->db->table_exists('db_salesitem_batch_allocations')) return true;
        $allocations = $this->db->where('sales_id', (int)$saleId)->get('db_salesitem_batch_allocations')->result();
        foreach ($allocations as $allocation) {
            $qty = max(0, (float)$allocation->qty_allocated - (float)$allocation->qty_returned);
            if ($qty > 0) {
                $restored = $this->db->query('UPDATE db_item_barcodes SET qty = qty + ?, status = 1 WHERE id = ?', [$qty, (int)$allocation->barcode_id]);
                if (!$restored || $this->db->affected_rows() !== 1) return false;
            }
        }
        return $this->db->where('sales_id', (int)$saleId)->delete('db_salesitem_batch_allocations');
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
            $batchUpdated = $this->db->query('UPDATE db_item_barcodes SET qty = qty + ?, status = 1 WHERE id = ?', [$restore, (int)$allocation->barcode_id]);
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
            $batchUpdated = $this->db->query('UPDATE db_item_barcodes SET status = IF(qty - ? <= 0.00001, 0, 1), qty = qty - ? WHERE id = ? AND qty >= ?', [(float)$row->qty_returned, (float)$row->qty_returned, (int)$row->barcode_id, (float)$row->qty_returned]);
            if (!$batchUpdated || $this->db->affected_rows() !== 1) return false;
            $allocationUpdated = $this->db->query('UPDATE db_salesitem_batch_allocations SET qty_returned = GREATEST(0, qty_returned - ?) WHERE id = ? AND qty_returned >= ?', [(float)$row->qty_returned, (int)$row->allocation_id, (float)$row->qty_returned]);
            if (!$allocationUpdated) return false;
        }
        return $this->db->where('salesreturnitems_id', (int)$returnId)->delete('db_salesreturn_batch_allocations');
    }
}
