<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Floor corrections: voids and compliments.
 *
 * Both act on a bill that already exists, and neither invents a second ledger:
 *
 *   Void      — a round that never reached the guest. Its money must leave the
 *               bill and its stock must go back on the shelf. Every revenue
 *               query in the app already filters sales_status='Final', so
 *               marking the invoice 'Voided' reverses the money everywhere at
 *               once — reports, dashboard, tab balances, customer dues — and
 *               the line recompute in Pos_model::update_items_quantity() hands
 *               the stock back. Nothing is deleted: the row stays as evidence
 *               of what was rung up and pulled off.
 *
 *   Compliment — a round the house gave away. Stock leaves the shelf and the
 *               cost lands on COGS exactly as an ordinary sale, because the
 *               guest did drink it; only the charge disappears. Recording it
 *               as a discount would bury a free pour inside the discount
 *               figures, so it gets its own flag and its own amount.
 *
 * A void is deliberately not a cancellation and not wastage: cancelling a
 * customer's order and pouring six bottles down the sink are different events
 * with different numbers, and each keeps its own record here.
 */
class Bill_adjustments_model extends CI_Model {

    const STATUS_VOID = 'Voided';

    public function __construct(){
        parent::__construct();
    }

    /** True only once migration 4.0.9.230 has run on this install. */
    public function is_available(){
        return $this->db->field_exists('voided_at', 'db_sales')
            && $this->db->field_exists('comp_bit', 'db_salesitems');
    }

    /** True once migration 4.0.9.231 has run: the waiter, tip and charge columns. */
    public function has_tip_columns(){
        return $this->db->field_exists('waiter_user_id', 'db_sales')
            && $this->db->field_exists('tip_amount', 'db_sales');
    }

    /**
     * One bill as the floor sees it: header, its lines, what was given away,
     * whether it can still be voided and why not if it cannot.
     */
    public function bill($sales_id, $store_id){
        if(!$this->is_available()) return null;
        $sale = $this->db->where('id', (int)$sales_id)->where('store_id', (int)$store_id)
            ->get('db_sales')->row();
        if(!$sale) return null;

        $this->db->where('sales_id', (int)$sales_id);
        $this->db->order_by('id', 'asc');
        $sale->lines = $this->db->get('db_salesitems')->result();

        // The floor reads item names, never ids. The line's own description is
        // kept as the fallback, because it is what was printed on the bill.
        $item_ids = [];
        foreach($sale->lines as $line){
            if((int)($line->item_id ?? 0) > 0) $item_ids[] = (int)$line->item_id;
        }
        $names = [];
        if($item_ids && $this->db->table_exists('db_items')){
            $this->db->select('id, item_name')->where_in('id', array_unique($item_ids));
            foreach($this->db->get('db_items')->result() as $item){
                $names[(int)$item->id] = (string)$item->item_name;
            }
        }
        foreach($sale->lines as $line){
            $line->item_name = $names[(int)($line->item_id ?? 0)]
                ?? mb_substr(trim((string)($line->description ?? '')), 0, 80);
        }

        $sale->is_voided = (string)$sale->sales_status === self::STATUS_VOID;
        $sale->given_away = 0.0;
        foreach($sale->lines as $line){
            if((int)($line->comp_bit ?? 0) === 1){
                $sale->given_away += (float)($line->comp_amount ?? 0);
            }
        }
        $sale->void_block = $this->_void_blocker($sale);
        $sale->comp_block = $this->_bill_blocker($sale);
        $sale->can_void   = ($sale->void_block === '');
        // A compliment is normally given on a round that WAS served — the guest
        // drank it — so it is not refused for the reason a void is.
        $sale->can_comp   = ($sale->comp_block === '');

        if(!empty($sale->tab_id) && $this->db->table_exists('db_tabs')){
            $sale->tab = $this->db->where('id', (int)$sale->tab_id)
                ->where('store_id', (int)$store_id)->get('db_tabs')->row();
        }

        // Floor people, not amounts: the bill screen names who served the round
        // and who the guest tipped, and shows the optional service charge that
        // is already inside grand_total.
        if($this->has_tip_columns()){
            $sale->waiter_id        = (int)($sale->waiter_user_id ?? 0);
            $sale->tip_amount       = round((float)($sale->tip_amount ?? 0), 2);
            $sale->tip_for_id       = (int)($sale->tip_for_user_id ?? 0);
            $sale->service_charge_pct = (float)($sale->service_charge_pct ?? 0);
            $sale->service_charge_amt = round((float)($sale->service_charge_amt ?? 0), 2);
        }
        return $sale;
    }

    /**
     * Name the waiter standing behind a round.
     *
     * The tab already names a waiter, but a tab is handed over at shift change,
     * so it cannot tell you who served a bill last Friday. The invoice carries
     * that instead, which is what makes per-waiter takings and tips provable.
     * Attribution is not a re-pricing, so it stays open on a bill the guest has
     * already paid — only a voided round is refused.
     */
    public function set_waiter($sales_id, $store_id, $user_id, $actor = ''){
        if(!$this->has_tip_columns()){
            return ['ok' => false, 'message' => 'Waiter attribution is not available on this install yet.'];
        }
        $id = (int)$sales_id;
        $user_id = (int)$user_id;
        if($user_id > 0 && !$this->_staff_in_store($user_id, $store_id)){
            return ['ok' => false, 'message' => 'That staff member does not work in this store.'];
        }
        $sale = $this->db->where('id', $id)->where('store_id', (int)$store_id)->get('db_sales')->row();
        if(!$sale) return ['ok' => false, 'message' => 'That bill was not found in this store.'];
        if((string)$sale->sales_status === self::STATUS_VOID){
            return ['ok' => false, 'message' => 'This round was voided, so nobody served it.'];
        }

        $this->db->where('id', $id)->where('store_id', (int)$store_id)
            ->update('db_sales', ['waiter_user_id' => $user_id > 0 ? $user_id : null]);
        return ['ok' => true, 'message' => $user_id > 0
            ? 'This round now belongs to ' . $this->_staff_name($user_id) . '.'
            : 'No waiter named on this round.'];
    }

    /**
     * Key in what the guest tipped, and record who it is for.
     *
     * The tip is stored beside the bill and never inside grand_total or
     * paid_amount: the house is holding that cash for a person, and pushing it
     * through the bill total would inflate revenue, tax and outlet reports by
     * the waiter's money. Because nothing is re-priced, a tip can be recorded
     * after the guest has paid, and re-keying replaces the earlier figure rather
     * than adding to it — a second press cannot tip the same guest twice.
     */
    public function record_tip($sales_id, $store_id, $amount, $for_user_id, $actor = ''){
        if(!$this->has_tip_columns()){
            return ['ok' => false, 'message' => 'Tips are not available on this install yet.'];
        }
        $id = (int)$sales_id;
        $amount = round(max(0.0, (float)$amount), 2);
        if($amount > 100000000){
            return ['ok' => false, 'message' => 'That tip amount is too large to be real.'];
        }

        $sale = $this->db->where('id', $id)->where('store_id', (int)$store_id)->get('db_sales')->row();
        if(!$sale) return ['ok' => false, 'message' => 'That bill was not found in this store.'];
        if((string)$sale->sales_status === self::STATUS_VOID){
            return ['ok' => false, 'message' => 'This round was voided. Nothing was served, so nothing was tipped.'];
        }

        // The tip follows the waiter unless the guest clearly meant someone else.
        $for_user_id = (int)$for_user_id ?: (int)($sale->waiter_user_id ?? 0);
        if($amount > 0.005){
            if($for_user_id <= 0){
                return ['ok' => false, 'message' => 'Name the waiter this tip belongs to first.'];
            }
            if(!$this->_staff_in_store($for_user_id, $store_id)){
                return ['ok' => false, 'message' => 'That staff member does not work in this store.'];
            }
        }

        $this->db->where('id', $id)->where('store_id', (int)$store_id)->update('db_sales', [
            'tip_amount'      => $amount,
            'tip_for_user_id' => $amount > 0.005 ? $for_user_id : null,
            'tip_by'          => $this->_actor($actor),
            'tip_at'          => date('Y-m-d H:i:s'),
        ]);
        if($amount <= 0.005){
            return ['ok' => true, 'message' => 'Tip removed from this round.'];
        }
        return ['ok' => true, 'message' => 'Tip of ' . $this->_amount($amount) . ' recorded for '
            . $this->_staff_name($for_user_id) . '. It stays out of the store takings on purpose.'];
    }

    /**
     * Add, change or remove the optional service charge on one round.
     *
     * Unlike a tip this is house money, so its amount rides the field the money
     * formula already adds up (other_charges_amt) and reaches grand_total
     * through the same arithmetic the till uses. The percentage and the amount
     * are both stored so the charge can be re-set or removed exactly: the new
     * figure replaces the old one by its difference, never by a fresh guess at
     * what other_charges_amt used to contain.
     *
     * It is refused on a bill that already has money on it, for the same reason
     * a compliment is: re-pricing after payment would leave the drawer, the
     * customer balance and the bill telling three different stories.
     */
    public function set_service_charge($sales_id, $store_id, $percent, $actor = ''){
        if(!$this->has_tip_columns()){
            return ['ok' => false, 'message' => 'Service charges are not available on this install yet.'];
        }
        $id = (int)$sales_id;
        $percent = round((float)$percent, 2);
        if($percent < 0 || $percent > 100){
            return ['ok' => false, 'message' => 'Give a service charge between 0 and 100 per cent. Zero takes it off.'];
        }

        $this->db->trans_begin();
        $sale = $this->db->query(
            'SELECT * FROM db_sales WHERE id = ? AND store_id = ? FOR UPDATE', [$id, (int)$store_id]
        )->row();
        if(!$sale){
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'That bill was not found in this store.'];
        }
        $blocker = $this->_bill_blocker($sale);
        if($blocker === 'This round has already been voided.' && $percent <= 0){
            $this->db->trans_rollback();
            return ['ok' => true, 'already' => true, 'message' => 'This round was voided. Nothing was charged.'];
        }
        if($blocker !== ''){
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => $blocker];
        }

        // The guest is charged on what the round actually costs them, so a
        // compliment or a bill discount cannot quietly raise the charge.
        $base = max(0.0, (float)($sale->subtotal ?? 0)
            - (float)($sale->tot_discount_to_all_amt ?? 0)
            - (float)($sale->coupon_amt ?? 0));
        $new_amt = round($base * $percent / 100, 2);
        $old_amt = round((float)($sale->service_charge_amt ?? 0), 2);
        $delta = round($new_amt - $old_amt, 2);

        if(abs($delta) < 0.005 && abs($percent - (float)($sale->service_charge_pct ?? 0)) < 0.005){
            $this->db->trans_rollback();
            return ['ok' => true, 'already' => true, 'message' => 'This round already carries that service charge. Nothing else changed.'];
        }

        $applied = $this->db->query(
            'UPDATE db_sales
                SET service_charge_pct = ?, service_charge_amt = ?,
                    other_charges_amt = COALESCE(other_charges_amt,0) + ?,
                    grand_total = GREATEST(0, COALESCE(grand_total,0) + ?)
              WHERE id = ? AND store_id = ? AND sales_status = ? LIMIT 1',
            [$percent > 0 ? $percent : null, $new_amt, $delta, $delta, $id, (int)$store_id, 'Final']
        );
        if(!$applied || $this->db->affected_rows() !== 1){
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'This round changed while the service charge was being set. Nothing was changed.'];
        }

        $this->load->model('sales_model', 'mp_sales');
        if(!$this->mp_sales->update_sales_payment_status($id, (int)($sale->customer_id ?? 0))){
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'The amount owing could not be recalculated. No service charge was recorded.'];
        }

        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'The service charge could not be saved. Nothing was changed.'];
        }
        $this->db->trans_commit();

        if($percent <= 0){
            return ['ok' => true, 'message' => 'Service charge taken off this round. The bill dropped by ' . $this->_amount($old_amt) . '.'];
        }
        return ['ok' => true, 'message' => rtrim(rtrim(number_format($percent, 2, '.', ''), '0'), '.')
            . '% service charge added: ' . $this->_amount($new_amt) . ' on the bill.'];
    }

    /**
     * What each waiter brought in and what the guests left them, for a period.
     *
     * Two separate columns on purpose: the takings belong to the house and the
     * tips belong to the person. A manager settling a wage conversation needs
     * both, and needs them not added together.
     */
    public function waiter_report($store_id, $from, $to){
        $out = ['rows' => [], 'unnamed_tips' => 0.0];
        if(!$this->has_tip_columns() || !$this->db->table_exists('db_users')) return $out;

        $this->db->select("s.waiter_user_id AS waiter_id,
                           COALESCE(SUM(CASE WHEN s.sales_status = 'Final' THEN s.grand_total ELSE 0 END),0) AS takings,
                           COALESCE(SUM(CASE WHEN s.sales_status = 'Final' THEN 1 ELSE 0 END),0) AS rounds,
                           COALESCE(SUM(CASE WHEN s.sales_status = 'Final' THEN s.tip_amount ELSE 0 END),0) AS tips,
                           COALESCE(SUM(CASE WHEN s.sales_status = 'Final' THEN s.service_charge_amt ELSE 0 END),0) AS charges", FALSE);
        $this->db->join('db_users u', 'u.id = s.waiter_user_id AND u.store_id = s.store_id', 'left');
        $this->db->where('s.store_id', (int)$store_id);
        $this->db->where('s.sales_date >=', $from)->where('s.sales_date <=', $to);
        $this->db->group_by('s.waiter_user_id');
        $this->db->order_by('takings', 'desc');
        foreach($this->db->get('db_sales s')->result() as $row){
            $row->waiter_name = (int)$row->waiter_id > 0
                ? $this->_staff_name((int)$row->waiter_id)
                : 'Not named';
            $out['rows'][] = $row;
            if((int)$row->waiter_id <= 0) $out['unnamed_tips'] += (float)$row->tips;
        }
        return $out;
    }

    /** Is this a live staff member of this store? Never trust a posted user id. */
    private function _staff_in_store($user_id, $store_id){
        if((int)$user_id <= 0 || !$this->db->table_exists('db_users')) return false;
        return $this->db->where('id', (int)$user_id)->where('store_id', (int)$store_id)
            ->where('status', 1)->count_all_results('db_users') > 0;
    }

    /** First name, the way the floor addresses people — never a login. */
    private function _staff_name($user_id){
        if((int)$user_id <= 0 || !$this->db->table_exists('db_users')) return 'a waiter';
        $row = $this->db->select('first_name, username')->where('id', (int)$user_id)
            ->get('db_users')->row();
        if(!$row) return 'a waiter';
        return trim((string)($row->first_name ?? '')) ?: 'a waiter';
    }

    /**
     * The capture side of the floor's two money decisions. A witness that
     * cannot be read never changes the outcome: an empty map, and the void or
     * compliment lands exactly as it would have without the trail.
     */
    private function _auditBillShelf($sales_id){
        try {
            $capture = mp_audit_capture();
            if (!$capture) return array();
            $shelf = array();
            foreach($this->db->select('item_id')->distinct()->where('sales_id', (int)$sales_id)
                    ->where('item_id IS NOT NULL')->get('db_salesitems')->result() as $line){
                $itemId = (int)$line->item_id;
                if($itemId > 0) $shelf[$itemId] = $capture->shelf_qty($itemId);
            }
            return $shelf;
        } catch (Throwable $e) {
            log_message('error', 'Bill shelf audit read failed: ' . $e->getMessage());
            return array();
        }
    }

    private function _auditVoided($store_id, $sales_id, array $shelf_before, $reason){
        try {
            $capture = mp_audit_capture();
            if ($capture) $capture->billVoided((int)$store_id, (int)$sales_id, $shelf_before, $reason);
        } catch (Throwable $e) {
            log_message('error', 'Bill void audit capture failed: ' . $e->getMessage());
        }
    }

    private function _auditCompelled($store_id, $sales_id, array $compelled, $reason){
        try {
            $capture = mp_audit_capture();
            if ($capture) $capture->linesCompelled((int)$store_id, (int)$sales_id, $compelled, $reason);
        } catch (Throwable $e) {
            log_message('error', 'Compliment audit capture failed: ' . $e->getMessage());
        }
    }

    /** Plain English reason nothing on this bill may be corrected, or '' when it may. */
    private function _bill_blocker($sale){
        $status = (string)$sale->sales_status;
        if($status === self::STATUS_VOID) return 'This round has already been voided.';
        if($status !== 'Final') return 'Only an issued bill can be corrected; this one is still ' . strtolower($status ?: 'unfinished') . '.';
        if((float)($sale->paid_amount ?? 0) > 0.005) {
            return 'Money has already been taken on this round. Delete the payment first, or give change back through a refund.';
        }
        if($this->db->table_exists('db_salespayments')
            && $this->db->where('sales_id', (int)$sale->id)->where('status', 1)->count_all_results('db_salespayments') > 0){
            return 'A payment is recorded against this round. Remove it before correcting the bill, so the drawer stays true.';
        }
        if($this->db->table_exists('db_salesreturn')
            && $this->db->where('sales_id', (int)$sale->id)->count_all_results('db_salesreturn') > 0){
            return 'This round already has a return against it. Returns and floor corrections cannot share one bill.';
        }
        return '';
    }

    /**
     * A void refuses one more thing: a round the bar already made up and handed
     * out. A void puts stock back on the shelf, and something that was drunk
     * never goes back — that loss belongs to wastage, which is a different
     * record with its own reason code.
     */
    private function _void_blocker($sale){
        $blocker = $this->_bill_blocker($sale);
        if($blocker !== '') return $blocker;
        if($this->db->table_exists('db_kitchen_orders')
            && $this->db->where('sales_id', (int)$sale->id)
                ->where_in('kds_status', ['ready', 'served'])->count_all_results('db_kitchen_orders') > 0){
            return 'This round was already made up and handed out. Record what was lost as wastage instead — a void puts stock back on the shelf.';
        }
        return '';
    }

    /**
     * Void one round: money off the bill, stock back on the shelf.
     *
     * Idempotent by claim, not by check-then-act: the invoice is moved out of
     * 'Final' in a single conditional UPDATE, and only the caller that actually
     * changed the row goes on to return stock. A second press — a double click,
     * a retried request after a timeout — finds zero rows to claim and returns
     * "already voided" without touching stock, payments or the kitchen board a
     * second time.
     */
    public function void_round($sales_id, $store_id, $reason = '', $actor = ''){
        if(!$this->is_available()){
            return ['ok' => false, 'message' => 'Voids are not available on this install yet.'];
        }
        $id = (int)$sales_id;
        $this->db->trans_begin();

        $sale = $this->db->query(
            'SELECT * FROM db_sales WHERE id = ? AND store_id = ? FOR UPDATE', [$id, (int)$store_id]
        )->row();
        if(!$sale){
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'That bill was not found in this store.'];
        }
        $blocker = $this->_void_blocker($sale);
        if($blocker === 'This round has already been voided.'){
            $this->db->trans_rollback();
            return ['ok' => true, 'already' => true, 'message' => 'This round was already voided. Nothing else changed.'];
        }
        if($blocker !== ''){
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => $blocker];
        }

        // Serialised units bound to the lines are only released while the lines
        // still read 'Final', so this runs before the flip.
        //
        // The shelf is read at the same moment: the void puts stock back, and
        // the trail can only say how much came back against what was there.
        $shelf_before = $this->_auditBillShelf($id);

        if(function_exists('mp_release_sale_barcodes')) mp_release_sale_barcodes($id);

        $claimed = $this->db->query(
            'UPDATE db_sales SET sales_status = ?, voided_at = NOW(), voided_by = ?, void_reason = ?
             WHERE id = ? AND store_id = ? AND sales_status = ? LIMIT 1',
            [self::STATUS_VOID, $this->_actor($actor), mb_substr(trim((string)$reason), 0, 255), $id, (int)$store_id, 'Final']
        );
        if(!$claimed || $this->db->affected_rows() !== 1){
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'This round changed while it was being voided. Nothing was taken off the bill or put back on the shelf.'];
        }

        $item_ids = $this->db->distinct()->select('item_id')->where('sales_id', $id)
            ->where('item_id IS NOT NULL')->get('db_salesitems')->result();

        $flipped = $this->db->query(
            'UPDATE db_salesitems SET sales_status = ? WHERE sales_id = ?', [self::STATUS_VOID, $id]
        );
        if(!$flipped){
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'The lines on this round could not be voided. Nothing was changed.'];
        }

        // Stock back on the shelf: batch provenance keeps its rows (the
        // allocation is marked returned, not deleted), then each item's quantity
        // is recomputed from the ledger, which no longer counts these lines.
        if($this->db->table_exists('db_salesitem_batch_allocations')){
            $this->load->model('inventory_rotation_model', 'rotation');
            if(!$this->rotation->give_back_sale_stock($id)){
                $this->db->trans_rollback();
                return ['ok' => false, 'message' => 'The batch stock for this round could not be given back. Nothing was voided.'];
            }
        }
        $this->load->model('pos_model');
        foreach($item_ids as $row){
            if((int)$row->item_id <= 0) continue;
            if(!$this->pos_model->update_items_quantity((int)$row->item_id)){
                $this->db->trans_rollback();
                return ['ok' => false, 'message' => 'Stock levels could not be recalculated for this void. Nothing was voided.'];
            }
        }

        if($this->db->table_exists('db_kitchen_orders')){
            $this->db->where('sales_id', $id)->where_in('kds_status', ['new', 'preparing'])
                ->update('db_kitchen_orders', ['kds_status' => 'cancelled']);
        }

        if((int)($sale->customer_id ?? 0) > 0){
            $this->load->model('sales_model', 'mp_sales');
            if(!$this->mp_sales->update_sales_payment_status($id, (int)$sale->customer_id)){
                $this->db->trans_rollback();
                return ['ok' => false, 'message' => 'The customer balance could not be updated for this void. Nothing was voided.'];
            }
        }

        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'The void could not be saved. Nothing was changed.'];
        }
        $this->_auditVoided((int)$store_id, $id, $shelf_before, $reason);
        $this->db->trans_commit();
        return ['ok' => true, 'message' => 'Round voided. The bill no longer carries it and the stock is back on the shelf.'];
    }

    /**
     * Compliment lines on a bill: the guest gets them, the house pays for them.
     *
     * Stock is deliberately untouched — a poured drink is gone, and its cost
     * belongs on COGS, which is exactly what the sale already booked. Only the
     * charge comes off, and it is recorded on the line as comp_amount so a
     * compliment can never be mistaken for a discount in the figures.
     */
    public function comp_lines($sales_id, $store_id, $line_ids, $reason = '', $actor = ''){
        if(!$this->is_available()){
            return ['ok' => false, 'message' => 'Compliments are not available on this install yet.'];
        }
        $id = (int)$sales_id;
        $ids = [];
        foreach((array)$line_ids as $line_id){
            if((int)$line_id > 0) $ids[] = (int)$line_id;
        }
        if(!$ids) return ['ok' => false, 'message' => 'Choose the items being given away.'];

        $this->db->trans_begin();
        $sale = $this->db->query(
            'SELECT * FROM db_sales WHERE id = ? AND store_id = ? FOR UPDATE', [$id, (int)$store_id]
        )->row();
        if(!$sale){
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'That bill was not found in this store.'];
        }
        if((string)$sale->sales_status !== 'Final'){
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'Only an issued bill can carry a compliment. This one is '
                . strtolower((string)$sale->sales_status ?: 'unfinished') . '.'];
        }
        // A bill the guest has already paid cannot be re-priced from the floor.
        $blocker = $this->_bill_blocker($sale);
        if($blocker !== ''){
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => $blocker];
        }

        $marked = 0;
        $given = 0.0;
        $compelled = array();
        $reason_text = mb_substr(trim((string)$reason), 0, 255);
        $who = $this->_actor($actor);
        foreach($ids as $line_id){
            // The invoice row is already locked by the SELECT ... FOR UPDATE
            // above, so reading the charge and clearing it cannot race another
            // floor action on the same bill.
            $line = $this->db->query(
                'SELECT item_id, total_cost FROM db_salesitems
                 WHERE id = ? AND sales_id = ? AND store_id = ? AND comp_bit = 0 AND sales_status = ? LIMIT 1',
                [$line_id, $id, (int)$store_id, 'Final']
            )->row();
            if(!$line) continue;

            // One conditional UPDATE per line is the claim: a line already
            // given away cannot be counted (or zero-rated) a second time, so a
            // retried request lands once.
            $ok = $this->db->query(
                'UPDATE db_salesitems
                    SET comp_bit = 1, comp_amount = total_cost, comp_by = ?, comp_reason = ?, comp_at = NOW(),
                        total_cost = 0, unit_total_cost = 0, tax_amt = 0
                  WHERE id = ? AND sales_id = ? AND store_id = ? AND comp_bit = 0 AND sales_status = ? LIMIT 1',
                [$who, $reason_text, $line_id, $id, (int)$store_id, 'Final']
            );
            if(!$ok || $this->db->affected_rows() !== 1) continue;
            $marked++;
            $given += max(0.0, (float)$line->total_cost);
            $compelled[] = array('item' => (int)$line->item_id, 'charge' => max(0.0, (float)$line->total_cost));
        }
        if($marked === 0){
            $this->db->trans_rollback();
            return ['ok' => true, 'already' => true, 'message' => 'Those items were already on the house. Nothing else changed.'];
        }

        // The bill's money drops by exactly what was given away. round_off is
        // left alone: it is the tweak the guest actually paid to.
        $recomputed = $this->db->query(
            'UPDATE db_sales
                SET subtotal = GREATEST(0, COALESCE(subtotal,0) - ?),
                    grand_total = GREATEST(0, COALESCE(grand_total,0) - ?)
              WHERE id = ? AND store_id = ? AND sales_status = ?',
            [$given, $given, $id, (int)$store_id, 'Final']
        );
        if(!$recomputed){
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'The bill total could not be updated. No compliment was recorded.'];
        }

        $this->load->model('sales_model', 'mp_sales');
        if(!$this->mp_sales->update_sales_payment_status($id, (int)($sale->customer_id ?? 0))){
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'The amount owing could not be recalculated. No compliment was recorded.'];
        }

        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            return ['ok' => false, 'message' => 'The compliment could not be saved. Nothing was changed.'];
        }
        $this->_auditCompelled((int)$store_id, $id, $compelled, $reason);
        $this->db->trans_commit();
        return ['ok' => true, 'marked' => $marked, 'message' => $marked . ' item(s) on the house. Stock and cost stayed booked; the bill dropped by ' . $this->_amount($given) . '.'];
    }

    /**
     * What the floor gave away and pulled off, for a period.
     *
     * Kept separate from discounts on purpose: "₦40,000 in discounts" and
     * "₦40,000 in free rounds" are different conversations with the accountant.
     */
    public function exceptions_report($store_id, $from, $to){
        $out = ['comps' => [], 'comp_total' => 0.0, 'comp_items' => 0,
                'voids' => [], 'void_total' => 0.0, 'void_rounds' => 0];
        if(!$this->is_available()) return $out;

        // "lines" is a reserved word in current MySQL, so the count is asked for
        // under a name the parser leaves alone.
        $this->db->select("si.comp_reason AS reason, COUNT(*) AS item_count,
                           COALESCE(SUM(si.comp_amount),0) AS amount,
                           COALESCE(SUM(si.base_unit_qty),0) AS qty", FALSE);
        $this->db->join('db_sales s', 's.id = si.sales_id', 'inner');
        $this->db->where('si.store_id', (int)$store_id)->where('si.comp_bit', 1);
        $this->db->where('s.sales_date >=', $from)->where('s.sales_date <=', $to);
        $this->db->group_by('si.comp_reason');
        $this->db->order_by('amount', 'desc');
        foreach($this->db->get('db_salesitems si')->result() as $row){
            $out['comps'][] = $row;
            $out['comp_total'] += (float)$row->amount;
            $out['comp_items'] += (int)$row->item_count;
        }

        $this->db->select("void_reason AS reason, COUNT(*) AS rounds,
                           COALESCE(SUM(grand_total),0) AS amount", FALSE);
        $this->db->where('store_id', (int)$store_id)->where('sales_status', self::STATUS_VOID);
        $this->db->where('sales_date >=', $from)->where('sales_date <=', $to);
        $this->db->group_by('void_reason');
        $this->db->order_by('amount', 'desc');
        foreach($this->db->get('db_sales')->result() as $row){
            $out['voids'][] = $row;
            $out['void_total'] += (float)$row->amount;
            $out['void_rounds'] += (int)$row->rounds;
        }
        return $out;
    }

    /** Who gave it away or voided the round - a username string, like created_by. */
    private function _actor($actor){
        return mb_substr(trim((string)$actor), 0, 50);
    }

    private function _amount($value){
        return function_exists('store_number_format')
            ? store_number_format((float)$value)
            : number_format((float)$value, 2);
    }
}
