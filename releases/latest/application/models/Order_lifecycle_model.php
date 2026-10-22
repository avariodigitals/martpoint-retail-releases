<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * The order lifecycle, once, for every channel that takes an order.
 *
 * Counter, Online Store and table-QR orders all pass through the same stages,
 * so the rules about what may follow what live here instead of being repeated
 * — and partly omitted — at each screen. Two columns already hold this
 * history (an order's own status and its kitchen ticket); this does not
 * replace them with a third one, it keeps them honest and writes down each
 * move, because a dispute about whether food was handed over needs evidence.
 */
class Order_lifecycle_model extends CI_Model {

    /**
     * Canonical stages, in the order work actually happens.
     *
     * 'accepted' and 'preparing' share one stored order status on purpose: an
     * order column with a value per stage would mean changing the vocabulary
     * every business and shopper already sees, for a distinction the kitchen
     * ticket already carries (new vs preparing). The same is true of
     * 'fulfilled' and 'completed' — the handover and the settled receipt share
     * 'completed', and the difference is recorded in the transition log, then
     * settled by the sales record itself.
     */
    private $_stages = [
        'placed'    => ['rank' => 10, 'online' => 'pending',   'kds' => 'new',       'label' => 'Order placed'],
        'accepted'  => ['rank' => 20, 'online' => 'processing','kds' => 'new',       'label' => 'Accepted'],
        'preparing' => ['rank' => 30, 'online' => 'processing','kds' => 'preparing', 'label' => 'Preparing'],
        'ready'     => ['rank' => 40, 'online' => 'ready',     'kds' => 'ready',     'label' => 'Ready'],
        'fulfilled' => ['rank' => 50, 'online' => 'completed', 'kds' => 'served',    'label' => 'Handed over'],
        'completed' => ['rank' => 60, 'online' => 'completed', 'kds' => 'served',    'label' => 'Completed'],
        'cancelled' => ['rank' =>  0, 'online' => 'cancelled', 'kds' => 'cancelled', 'label' => 'Cancelled'],
    ];

    /** Every value the order's own status column may hold. */
    const ONLINE_VALUES = ['pending', 'paid', 'processing', 'ready', 'completed', 'cancelled'];

    public function stages() {
        return $this->_stages;
    }

    /**
     * Return the existing operational order for a client-generated retry key.
     * The key is always store-scoped; a key from another tenant is never
     * allowed to resolve an order here.
     */
    public function find_by_idempotency($store_id, $key) {
        $key = trim((string) $key);
        if ($key === '' || !$this->db->field_exists('idempotency_key', 'db_online_orders')) return null;
        return $this->db->where('store_id', (int) $store_id)
            ->where('idempotency_key', substr($key, 0, 96))
            ->get('db_online_orders')->row();
    }

    /**
     * Create the canonical operational row for a completed walk-in sale.
     * The sale remains the financial source of truth; this row only gives a
     * walk-in/QR/kitchen flow one traceable order identity.
     */
    public function ensure_counter_order($store_id, $sales_id, array $meta = []) {
        $store_id = (int) $store_id; $sales_id = (int) $sales_id;
        if (!$store_id || !$sales_id || !$this->db->table_exists('db_online_orders')) return null;
        $key = substr(trim((string) ($meta['idempotency_key'] ?? ('sale:' . $store_id . ':' . $sales_id))), 0, 96);
        $existing = $this->find_by_idempotency($store_id, $key);
        if ($existing) return $existing;

        $sale = $this->db->where('id', $sales_id)->where('store_id', $store_id)->get('db_sales')->row();
        if (!$sale) return null;
        $customer = $this->db->where('id', (int) $sale->customer_id)->where('store_id', $store_id)->get('db_customers')->row();
        $table_id = (int) ($sale->table_id ?? 0);
        $table = $table_id && $this->db->table_exists('db_tables')
            ? $this->db->where('id', $table_id)->where('store_id', $store_id)->get('db_tables')->row() : null;
        $paid = strtolower((string) ($sale->payment_status ?? '')) === 'paid';
        $order = [
            'store_id' => $store_id,
            'warehouse_id' => (int) ($sale->warehouse_id ?? 0),
            'order_code' => substr('WALK-' . (string) $sale->sales_code, 0, 50),
            'customer_id' => (int) ($sale->customer_id ?? 0),
            'customer_name' => $customer ? $customer->customer_name : 'Walk-in',
            'order_type' => 'product',
            'order_status' => mp_feature_enabled('kitchen_workflow') ? 'processing' : 'completed',
            'payment_status' => $paid ? 'paid' : 'unpaid',
            'payment_method' => 'pay_on_delivery',
            'subtotal' => (float) $sale->subtotal,
            'grand_total' => (float) $sale->grand_total,
            'table_number' => $table ? $table->table_name : null,
            'source_channel' => 'walk_in',
            'fulfilment_type' => $table ? 'dine_in' : 'takeaway',
            'idempotency_key' => $key,
            'sales_id' => $sales_id,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->trans_begin();
        $this->db->insert('db_online_orders', $order);
        $order_id = (int) $this->db->insert_id();
        if (!$order_id) {
            $this->db->trans_rollback();
            return $this->find_by_idempotency($store_id, $key);
        }
        foreach ($this->db->where('sales_id', $sales_id)->get('db_salesitems')->result() as $line) {
            $item = $this->db->where('id', (int) $line->item_id)->where('store_id', $store_id)->get('db_items')->row();
            $this->db->insert('db_online_order_items', [
                'order_id' => $order_id, 'item_type' => 'product', 'item_id' => (int) $line->item_id,
                'item_name' => $item ? $item->item_name : ('Item #' . (int) $line->item_id),
                'qty' => (int) $line->sales_qty, 'unit_price' => (float) $line->price_per_unit,
                'total_price' => (float) $line->total_cost, 'status' => 1,
            ]);
        }
        // Sales_model is the shared walk-in financial path.  Complete the
        // same made-to-order recipe movement here for mobile/alternate POS
        // paths; legacy desktop POS movements are recognised as SALE-<id>.
        $this->load->model('storefront_model');
        if (!$this->storefront_model->ensureRecipeConsumptionForOrder($order_id)) {
            $this->db->trans_rollback();
            return null;
        }
        if ($this->db->trans_status() === false) { $this->db->trans_rollback(); return null; }
        $this->db->trans_commit();
        return $this->get_order_by_id($store_id, $order_id);
    }

    public function get_order_by_id($store_id, $order_id) {
        return $this->db->where('store_id', (int) $store_id)->where('id', (int) $order_id)->get('db_online_orders')->row();
    }

    public function is_stage($state) {
        return isset($this->_stages[$state]);
    }

    public function rank($state) {
        return isset($this->_stages[$state]) ? (int) $this->_stages[$state]['rank'] : -1;
    }

    public function label($state) {
        return isset($this->_stages[$state]) ? $this->_stages[$state]['label'] : ucfirst((string) $state);
    }

    /** The order-status value a stage is stored as. */
    public function online_for($state) {
        return isset($this->_stages[$state]) ? $this->_stages[$state]['online'] : 'pending';
    }

    /** The kitchen ticket state a stage shows as, when the store runs a kitchen. */
    public function kds_for($state) {
        return isset($this->_stages[$state]) ? $this->_stages[$state]['kds'] : 'new';
    }

    /**
     * Which stage an order's stored status says it is in.
     *
     * 'pending' and 'paid' are the same stage: paid is a payment fact, kept in
     * payment_status as well, and money arriving has not made anybody cook,
     * pack or hand over anything.
     */
    public function from_online($status) {
        $status = (string) $status;
        if ($status === 'paid') return 'placed';
        foreach ($this->_stages as $state => $spec) {
            if ($spec['online'] === $status) {
                // 'processing' is shared; without a ticket the work has at
                // least been taken on, which is the later of the two stages.
                return $state === 'accepted' ? 'preparing' : $state;
            }
        }
        return 'placed';
    }

    /** Which stage a kitchen ticket shows. A ticket is where the handover is witnessed. */
    public function from_kds($kds) {
        return [
            'new'       => 'accepted',
            'preparing' => 'preparing',
            'ready'     => 'ready',
            'served'    => 'fulfilled',
            'cancelled' => 'cancelled',
        ][(string) $kds] ?? '';
    }

    /**
     * The order's real stage, read from both of its columns.
     *
     * $order may be null for a counter order that has no online row. $settled
     * is whether the receipt exists; it is what separates a meal that has left
     * the pass from one that has also been accounted for.
     */
    public function resolve($order = null, $ticket = null, $settled = false) {
        $online = $order ? $this->from_online($order->order_status ?? 'pending') : 'placed';
        if ($online === 'cancelled') return 'cancelled';

        // A completed order that has no receipt behind it has only been handed
        // over. The two share one stored value, so the sales record is what
        // tells them apart — and only a kitchen ticket makes the handover a
        // real event worth holding the books open for. A delivered parcel in a
        // shop with no kitchen stays simply 'completed', as it reads today.
        if ($online === 'completed' && !$settled && $ticket !== null) $online = 'fulfilled';

        $kds = $ticket ? (string) $ticket->kds_status : '';
        $from_ticket = $this->from_kds($kds);

        if ($from_ticket === '') return $online;
        if ($from_ticket === 'cancelled') return 'cancelled';

        // A ticket that is further along wins; one that has fallen behind only
        // refines, so a kitchen record left at 'new' cannot undo a ready meal.
        if ($this->rank($from_ticket) > $this->rank($online)) {
            if ($from_ticket === 'fulfilled' && $settled) return 'completed';
            return $from_ticket;
        }
        return $online;
    }

    /**
     * May an order move from one stage to another?
     *
     * Work only ever moves forward, and cancellation stops being possible once
     * the order has left the pass — at that point it is a return or a refund,
     * not a cancelled order. Cancelled and completed are ends.
     */
    public function can_move($from, $to) {
        if (!$this->is_stage($from) || !$this->is_stage($to)) return false;
        if ($from === $to) return true;
        if ($from === 'cancelled') return false;
        if ($to === 'cancelled') return $this->rank($from) < $this->rank('fulfilled');
        if (in_array($from, ['fulfilled', 'completed'], true)) return $from === 'fulfilled' && $to === 'completed';
        return $this->rank($to) > $this->rank($from);
    }

    /** Plain English for why a move was refused, for staff rather than developers. */
    public function why_blocked($from, $to) {
        $fl = $this->label($from); $tl = $this->label($to);
        if (!in_array($to, array_keys($this->_stages), true)) {
            return 'That is not a stage this order can be in.';
        }
        if ($from === 'cancelled') {
            return 'A cancelled order cannot be moved again. Take a new order instead.';
        }
        if ($to === 'cancelled' && in_array($from, ['fulfilled', 'completed'], true)) {
            return "This order was already handed over, so it cannot be cancelled. "
                 . "Refund it, or return the items, so the money and the stock both follow what actually happened.";
        }
        if ($this->rank($to) < $this->rank($from)) {
            return "This order is already '{$fl}', which is further on than '{$tl}'. "
                 . "An order does not go backwards through the kitchen.";
        }
        if ($from === 'fulfilled' && $to !== 'completed') {
            return "This order has been handed over, so the only thing left is to settle it.";
        }
        return "An order cannot go from '{$fl}' to '{$tl}'.";
    }

    /**
     * Move an online or table-QR order to another stage — the only place that
     * writes an order's status, so no screen can slip past the rules.
     *
     * Accepts either vocabulary: a stage ('ready') or the order's own status
     * value ('processing'). Stock, payment and service-work side effects stay
     * with the caller on purpose; this owns the lifecycle and its record, not
     * the consequences of the business type.
     *
     * @return array ok, from, state, storage, changed, message
     */
    public function move($store_id, $order_id, $target, array $opts = []) {
        $store_id = (int) $store_id;
        $order_id = (int) $order_id;
        if (!$order_id) return $this->_refused(0, '', '', 'No order was named.');

        $order = $this->db->where('id', $order_id)->where('store_id', $store_id)->get('db_online_orders')->row();
        if (!$order) return $this->_refused($order_id, '', '', 'That order belongs to another store, or no longer exists.');

        $ticket = null;
        if ($this->db->table_exists('db_kitchen_orders') && $this->db->field_exists('online_order_id', 'db_kitchen_orders')) {
            $ticket = $this->db->where('online_order_id', $order_id)->where('store_id', $store_id)
                ->order_by('id', 'desc')->limit(1)->get('db_kitchen_orders')->row();
        }
        $settled = $this->_is_settled($order);

        $from  = $this->resolve($order, $ticket, $settled);
        $state = $this->_normalise($target, $opts);
        if ($state === '') return $this->_refused($order_id, $from, '', $this->why_blocked($from, $target));
        if (!$this->can_move($from, $state)) {
            return $this->_refused($order_id, $from, $state, $this->why_blocked($from, $state));
        }

        // The stored value: honour the exact one a caller using the old
        // vocabulary asked for, so a merchant marking an order paid still gets
        // 'paid' rather than the stage's default 'pending'.
        $storage = $this->online_for($state);
        $requested = $opts['storage'] ?? (in_array($target, self::ONLINE_VALUES, true) ? $target : '');
        if (in_array($requested, self::ONLINE_VALUES, true)) $storage = $requested;

        $now = date('Y-m-d H:i:s');
        $changed = $storage !== (string) $order->order_status;

        $this->db->trans_start();
        if ($changed) {
            $this->db->where('id', $order_id)->where('store_id', $store_id)
                ->update('db_online_orders', ['order_status' => $storage, 'updated_at' => $now]);
        }
        if ($ticket) {
            $wanted = $this->kds_for($state);
            if ($wanted !== (string) $ticket->kds_status && $this->_kds_accepts($wanted)
                && $this->_ticket_can_follow($ticket, $wanted)) {
                $this->db->where('id', (int) $ticket->id)->where('store_id', $store_id)
                    ->update('db_kitchen_orders', ['kds_status' => $wanted, 'updated_at' => $now]);
                // Reflect the move in this same write, so a caller that reads
                // the stage afterwards is not told the old one.
                $ticket->kds_status = $wanted;
            }
        }
        // Only a real stage change is written down. Marking an order paid
        // records a payment, not progress, and payment history lives with the
        // payment status — logging it here would bury the moves that matter.
        if ($from !== $state) {
            $this->_record($store_id, $order_id, $from, $state, $ticket, $opts);
        }
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return $this->_refused($order_id, $from, $state, 'The order could not be updated; nothing was saved.');
        }
        return [
            'ok'      => true,
            'from'    => $from,
            'state'   => $state,
            'storage' => $storage,
            'changed' => ($from !== $state) || $changed,
            'message' => $from === $state ? 'Already ' . strtolower($this->label($state)) : $this->label($state),
        ];
    }

    /**
     * The written record of each move, oldest first, for whoever has to answer
     * "when did you take this order?".
     */
    public function history($store_id, $order_id, $limit = 40) {
        $store_id = (int) $store_id;
        $order_id = (int) $order_id;
        if (!$this->db->table_exists('db_order_status_log')) return [];
        $this->db->where('store_id', $store_id)->where('online_order_id', $order_id)
            ->order_by('created_at', 'asc')->order_by('id', 'asc')->limit((int) $limit);
        $rows = [];
        foreach ($this->db->get('db_order_status_log')->result() as $r) {
            $rows[] = [
                'state'      => $r->to_status,
                'from'       => (string) $r->from_status,
                'label'      => $this->label($r->to_status),
                'actor'      => (string) $r->actor,
                'channel'    => (string) $r->channel,
                'reason'     => (string) $r->reason,
                'created_at' => $r->created_at,
            ];
        }
        return $rows;
    }

    /**
     * Move a kitchen ticket.
     *
     * A ticket for a table-QR or online order is the same order seen from the
     * pass, so it is handed to move() and both columns move together. A ticket
     * for a counter sale has no order row of its own — the sale is already a
     * receipt — so the ticket carries its stage, and is still recorded here.
     */
    public function move_ticket($store_id, $kitchen_order_id, $to, array $opts = []) {
        $store_id = (int) $store_id;
        $kitchen_order_id = (int) $kitchen_order_id;
        if (!$this->db->table_exists('db_kitchen_orders')) {
            return $this->_refused(0, '', '', 'This store has no kitchen board.');
        }
        $ticket = $this->db->where('id', $kitchen_order_id)->where('store_id', $store_id)
            ->get('db_kitchen_orders')->row();
        if (!$ticket) return $this->_refused(0, '', '', 'That ticket belongs to another store, or is gone.');

        $wanted = $this->is_stage($to) ? $this->kds_for($to) : (string) $to;
        $state  = $this->from_kds($wanted);
        if ($state === '') return $this->_refused(0, $this->from_kds($ticket->kds_status), '', 'That is not a stage the kitchen board uses.');

        $online_id = (int) ($ticket->online_order_id ?? 0);
        if ($online_id > 0) {
            $opts['state']   = $state;
            $opts['channel'] = $opts['channel'] ?? 'kds';
            return $this->move($store_id, $online_id, $state, $opts);
        }

        $from = $this->from_kds($ticket->kds_status) ?: 'accepted';
        if (!$this->can_move($from, $state)) {
            return $this->_refused(0, $from, $state, $this->why_blocked($from, $state));
        }
        if (!$this->_kds_accepts($wanted)) {
            return $this->_refused(0, $from, $state, 'This install\'s kitchen board cannot hold that stage yet.');
        }
        if ($wanted === (string) $ticket->kds_status) {
            return ['ok' => true, 'from' => $from, 'state' => $state, 'storage' => $wanted,
                    'changed' => false, 'message' => 'Already ' . strtolower($this->label($state))];
        }

        $this->db->trans_start();
        $this->db->where('id', $kitchen_order_id)->where('store_id', $store_id)
            ->update('db_kitchen_orders', ['kds_status' => $wanted, 'updated_at' => date('Y-m-d H:i:s')]);
        $this->_record($store_id, 0, $from, $state, $ticket, $opts);
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return $this->_refused(0, $from, $state, 'The ticket could not be updated; nothing was saved.');
        }
        return ['ok' => true, 'from' => $from, 'state' => $state, 'storage' => $wanted,
                'changed' => true, 'message' => $this->label($state)];
    }

    // ------------------------------------------------------------------ internals

    /** Settled means the order has become a receipt, whatever its status column says. */
    private function _is_settled($order) {
        return $this->db->field_exists('sales_id', 'db_online_orders') && (int) ($order->sales_id ?? 0) > 0;
    }

    /** Accepts a stage name or an order-status value; '' when neither. */
    private function _normalise($target, array $opts) {
        $state = $opts['state'] ?? '';
        if ($this->is_stage($state)) return $state;
        $target = (string) $target;
        if ($this->is_stage($target)) return $target;
        if (in_array($target, self::ONLINE_VALUES, true)) return $this->from_online($target);
        return '';
    }

    /**
     * Whether this install's kitchen table can hold a given ticket state.
     *
     * 'cancelled' arrives with migration 4.0.9.166. Writing it before that has
     * run would empty the column, so the stage is skipped rather than trusted
     * to a schema that may not have been upgraded yet.
     */
    private function _kds_accepts($value) {
        static $allowed = null;
        if ($allowed === null) {
            $allowed = [];
            if (!$this->db->table_exists('db_kitchen_orders')) return false;
            $q = $this->db->query("SELECT COLUMN_TYPE FROM information_schema.COLUMNS
                                   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_kitchen_orders'
                                   AND COLUMN_NAME = 'kds_status' LIMIT 1");
            $row = $q ? $q->row() : null;
            if ($row && preg_match_all("/'([^']+)'/", (string) $row->COLUMN_TYPE, $m)) {
                $allowed = $m[1];
            }
        }
        return in_array($value, $allowed, true);
    }

    /**
     * A ticket only ever moves forward too. An order that is cancelled or
     * reopened is the exception the kitchen needs to see, so a dead ticket
     * stops showing as cooking food.
     */
    private function _ticket_can_follow($ticket, $wanted) {
        $order = ['new' => 1, 'preparing' => 2, 'ready' => 3, 'served' => 4];
        if ($wanted === 'cancelled') return true;
        if ((string) $ticket->kds_status === 'cancelled') return false;
        return ($order[$wanted] ?? 0) >= ($order[(string) $ticket->kds_status] ?? 0);
    }

    private function _record($store_id, $order_id, $from, $state, $ticket, array $opts) {
        if (!$this->db->table_exists('db_order_status_log')) return;
        $actor = (string) ($opts['actor'] ?? '');
        if ($actor === '') {
            $user_id = function_exists('get_current_user_id') ? (int) get_current_user_id() : 0;
            $actor = $user_id > 0 && function_exists('mp_first_name')
                ? mp_first_name($user_id)
                : ($opts['system'] ?? 'System');
        }
        $this->db->insert('db_order_status_log', [
            'store_id'         => $store_id,
            'online_order_id'  => $order_id,
            'sales_id'         => 0,
            'kitchen_order_id' => $ticket ? (int) $ticket->id : 0,
            'from_status'      => $from,
            'to_status'        => $state,
            'reason'           => isset($opts['reason']) ? mb_substr(trim((string) $opts['reason']), 0, 255) : null,
            'actor'            => mb_substr($actor, 0, 60),
            'channel'          => mb_substr((string) ($opts['channel'] ?? 'system'), 0, 20),
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        // The store-wide Audit Trail screen already exists, so a move is
        // visible to whoever reads it without waiting for a page of this
        // order's own. Its own table stays the detailed record.
        try {
            if (function_exists('mp_audit_log')) {
                mp_audit_log('Orders', $this->label($state), $order_id ? $order_id : ($ticket ? (int) $ticket->id : null),
                    ($from !== '' ? $this->label($from) . ' → ' : '') . $this->label($state)
                    . (isset($opts['reason']) && trim((string) $opts['reason']) !== '' ? ' — ' . trim((string) $opts['reason']) : ''));
            }
        } catch (Throwable $e) {
            // History is a record, not a gate: a store with auditing switched
            // off still moves its orders.
        }
    }

    private function _refused($order_id, $from, $state, $message) {
        return ['ok' => false, 'order_id' => $order_id, 'from' => $from, 'state' => $state,
                'storage' => '', 'changed' => false, 'error' => $message, 'message' => $message];
    }
}
