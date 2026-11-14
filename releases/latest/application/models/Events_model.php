<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Event Ticketing Model
 * Events, dated sessions with per-seat capacity, and issued tickets.
 *
 * A ticket product is a db_items row with product_type='event' linked here
 * via db_events.item_id, so it rides the existing storefront pipeline and is
 * purchasable in every theme. Seats are counted on db_event_sessions
 * (capacity vs sold); db_items.stock is deliberately untouched for tickets.
 */
class Events_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    private function _ready() {
        return $this->db->table_exists('db_events')
            && $this->db->table_exists('db_event_sessions')
            && $this->db->table_exists('db_event_tickets');
    }

    /* ===================== EVENTS ===================== */

    public function get_events($store_id, $active_only = true) {
        if (!$this->_ready()) return [];
        $this->db->where('store_id', (int)$store_id);
        if ($active_only) $this->db->where('status', 1);
        $this->db->order_by('starts_on', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get('db_events')->result();
    }

    public function get_event($id, $store_id) {
        if (!$this->_ready()) return null;
        $this->db->where('id', (int)$id);
        $this->db->where('store_id', (int)$store_id);
        return $this->db->get('db_events')->row();
    }

    public function get_event_by_item($item_id, $store_id) {
        if (!$this->_ready()) return null;
        $this->db->where('item_id', (int)$item_id);
        $this->db->where('store_id', (int)$store_id);
        return $this->db->get('db_events')->row();
    }

    public function save_event($data, $id = null) {
        if (!$this->_ready()) return false;
        if (empty($id)) {
            $this->db->insert('db_events', $data);
            return $this->db->insert_id();
        }
        $this->db->where('id', (int)$id);
        $this->db->where('store_id', (int)$data['store_id']);
        $this->db->update('db_events', $data);
        return (int)$id;
    }

    /* ===================== SESSIONS ===================== */

    /**
     * Sessions for one event. The ticket product itself must not appear as a
     * ticketed event in the catalogue until its first open session is dated,
     * so this is what the storefront availability check reads.
     */
    public function get_sessions($event_id, $store_id, $future_only = false) {
        if (!$this->_ready()) return [];
        $this->db->where('event_id', (int)$event_id);
        $this->db->where('store_id', (int)$store_id);
        if ($future_only) {
            $this->db->where('status', 1);
            $this->db->where('session_date >=', date('Y-m-d'));
        }
        $this->db->order_by('session_date', 'ASC');
        $this->db->order_by('start_time', 'ASC');
        $sessions = $this->db->get('db_event_sessions')->result();
        foreach ($sessions as $s) {
            $s->remaining = max(0, (int)$s->capacity - (int)$s->sold);
        }
        return $sessions;
    }

    /**
     * Open sessions across a set of events — used to decide whether a ticket
     * product still has anything to sell (catalogue visibility).
     */
    public function has_open_sessions($event_ids, $store_id) {
        $event_ids = array_values(array_filter(array_map('intval', (array)$event_ids)));
        if (!$this->_ready() || !$event_ids) return false;
        $this->db->where_in('event_id', $event_ids);
        $this->db->where('store_id', (int)$store_id);
        $this->db->where('status', 1);
        $this->db->where('session_date >=', date('Y-m-d'));
        $this->db->where('sold < capacity', null, false);
        return $this->db->count_all_results('db_event_sessions') > 0;
    }

    /**
     * Mirror remaining seats into db_items.stock for a ticket item. Themes
     * and the cart key off stock for their sold-out presentation; the
     * authoritative oversell guard stays in reserve_seats(). Callers invoke
     * this OUTSIDE the order transaction (after commit or rollback), never
     * inside — a rolled-back order must not leave the mirror low.
     */
    public function recalc_item_stock($item_id, $store_id) {
        if (!$this->_ready() || (int)$item_id <= 0) return false;
        $event = $this->get_event_by_item($item_id, $store_id);
        if (!$event) return false;
        $row = $this->db->select('COALESCE(SUM(GREATEST(0, capacity - sold)), 0) seats')
            ->where('event_id', (int)$event->id)
            ->where('store_id', (int)$store_id)
            ->where('status', 1)
            ->where('session_date >=', date('Y-m-d'))
            ->get('db_event_sessions')->row();
        $seats = (int)($row->seats ?? 0);
        $this->db->where('id', (int)$item_id);
        $this->db->where('store_id', (int)$store_id);
        $this->db->update('db_items', ['stock' => $seats]);
        return true;
    }

    public function get_session($id, $store_id) {
        if (!$this->_ready()) return null;
        $this->db->where('id', (int)$id);
        $this->db->where('store_id', (int)$store_id);
        $session = $this->db->get('db_event_sessions')->row();
        if ($session) {
            $session->remaining = max(0, (int)$session->capacity - (int)$session->sold);
        }
        return $session;
    }

    public function get_session_for_item($session_id, $item_id, $store_id) {
        $session = $this->get_session($session_id, $store_id);
        if (!$session) return null;
        $event = $this->get_event($session->event_id, $store_id);
        if (!$event || (int)$event->item_id !== (int)$item_id) return null;
        return $session;
    }

    public function save_session($data, $id = null) {
        if (!$this->_ready()) return false;
        if (empty($id)) {
            $this->db->insert('db_event_sessions', $data);
            return $this->db->insert_id();
        }
        // sold is never written through this path — only capacity/status edits.
        unset($data['sold']);
        $this->db->where('id', (int)$id);
        $this->db->where('store_id', (int)$data['store_id']);
        $this->db->update('db_event_sessions', $data);
        return (int)$id;
    }

    public function delete_session($id, $store_id) {
        if (!$this->_ready()) return false;
        $session = $this->get_session($id, $store_id);
        if (!$session || (int)$session->sold > 0) return false; // never strand issued seats
        $this->db->where('id', (int)$id);
        $this->db->where('store_id', (int)$store_id);
        return $this->db->delete('db_event_sessions');
    }

    /**
     * Atomically commit seats on the order transaction. The WHERE doubles as
     * the capacity + open-session guard, so two checkouts cannot oversell and
     * a past/closed session is rejected without a second query.
     */
    public function reserve_seats($session_id, $qty, $store_id) {
        if (!$this->_ready() || $qty < 1) return false;
        $qty = (int)$qty;
        $this->db->set('sold', 'sold + ' . $qty, false);
        $this->db->where('id', (int)$session_id);
        $this->db->where('store_id', (int)$store_id);
        $this->db->where('status', 1);
        $this->db->where('session_date >=', date('Y-m-d'));
        // $escape=FALSE is required: CI3's identifier protection rewrites
        // "sold + 2 <= capacity" as `sold +` 2, which MySQL rejects.
        $this->db->where("sold + $qty <= capacity", NULL, FALSE);
        $this->db->update('db_event_sessions');
        return $this->db->affected_rows() === 1;
    }

    /**
     * Return seats when an order is cancelled/expired. LEAST/GREATEST keep a
     * double release from driving sold negative.
     */
    public function release_seats($session_id, $qty, $store_id) {
        if (!$this->_ready() || $qty < 1) return false;
        $qty = (int)$qty;
        $this->db->set('sold', "GREATEST(0, sold - $qty)", false);
        $this->db->where('id', (int)$session_id);
        $this->db->where('store_id', (int)$store_id);
        $this->db->update('db_event_sessions');
        return $this->db->affected_rows() === 1;
    }

    /**
     * Release the seats held by every event line of a cancelled/unpaid order.
     * The claim on seats_released is atomic, so duplicate callbacks or a
     * cancel racing an expiry payment release the seats exactly once.
     * (db_online_order_items.seats_released arrives with 4.0.9.221.)
     */
    public function release_line_seats($order_id, $store_id) {
        if (!$this->_ready() || !$this->db->field_exists('seats_released', 'db_online_order_items')) return 0;
        $lines = $this->db->where('order_id', (int)$order_id)
            ->where('item_type', 'event')
            ->where('seats_released', 0)
            ->where('event_session_id >', 0)
            ->get('db_online_order_items')->result();
        $released = 0;
        foreach ($lines as $line) {
            $claim = $this->db->where('id', (int)$line->id)
                ->where('seats_released', 0)
                ->update('db_online_order_items', ['seats_released' => 1]);
            if ($claim && $this->db->affected_rows() === 1) {
                $this->release_seats((int)$line->event_session_id, (int)$line->qty, $store_id);
                $released += (int)$line->qty;
            }
        }
        return $released;
    }

    /**
     * Void a cancelled order's tickets and give their seats back. Seats at the
     * door (status='redeemed') are kept and never re-sold.
     */
    public function void_tickets_for_order($order_id, $store_id) {
        if (!$this->_ready()) return 0;
        $tickets = $this->db->where('online_order_id', (int)$order_id)
            ->where('store_id', (int)$store_id)
            ->where('status', 'sold')
            ->get('db_event_tickets')->result();
        $by_session = [];
        foreach ($tickets as $t) {
            $this->db->where('id', (int)$t->id);
            $this->db->where('status', 'sold'); // claim the row; a door scan mid-call wins
            $this->db->update('db_event_tickets', ['status' => 'void']);
            if ($this->db->affected_rows() === 1) {
                $sid = (int)$t->session_id;
                $by_session[$sid] = ($by_session[$sid] ?? 0) + max(1, (int)$t->qty_seats);
            }
        }
        foreach ($by_session as $sid => $n) {
            $this->release_seats($sid, $n, $store_id);
        }
        return array_sum($by_session);
    }

    /* ===================== TICKETS ===================== */

    /**
     * Issue one ticket row per seat for an event order line. Idempotent by
     * order line so re-running fulfilment never double-issues.
     */
    public function issue_tickets_for_order($order, $line, $store_id) {
        if (!$this->_ready()) return [];
        $session_id = (int)($line->event_session_id ?? 0);
        if ($session_id <= 0) return [];
        $session = $this->get_session($session_id, $store_id);
        if (!$session) return [];

        $existing = $this->db->where('order_line_id', (int)$line->id)
            ->where('store_id', (int)$store_id)
            ->count_all_results('db_event_tickets');
        if ($existing) {
            return $this->db->where('order_line_id', (int)$line->id)
                ->where('store_id', (int)$store_id)
                ->get('db_event_tickets')->result();
        }

        $seats = max(1, (int)$line->qty);
        $rows = [];
        for ($i = 0; $i < $seats; $i++) {
            $rows[] = [
                'store_id'        => (int)$store_id,
                'event_id'        => (int)$session->event_id,
                'session_id'      => $session_id,
                'ticket_code'     => $this->_new_code(),
                'holder_name'     => $order->customer_name ?? null,
                'holder_phone'    => $order->customer_phone ?? null,
                'holder_email'    => $order->customer_email ?? null,
                'qty_seats'       => 1,
                'source'          => 'online',
                'online_order_id' => (int)$order->id,
                'order_line_id'   => (int)$line->id,
                'status'          => 'sold',
            ];
        }
        $this->db->insert_batch('db_event_tickets', $rows);
        // The line's seat hold now lives in the ticket rows; mark the line so a
        // later cancel goes through void_tickets_for_order, not release_line_seats.
        if ($this->db->field_exists('seats_released', 'db_online_order_items')) {
            $this->db->where('id', (int)$line->id);
            $this->db->update('db_online_order_items', ['seats_released' => 1]);
        }
        return $rows;
    }

    private function _new_code() {
        // 8 chars of base-36 from random bytes, uppercase, MP prefix kept short
        // so a door scan/typed code stays quick. Uniqueness is enforced by the
        // DB index; generation retries on the rare collision.
        for ($try = 0; $try < 5; $try++) {
            $code = 'MP' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
            $clash = $this->db->where('ticket_code', $code)->count_all_results('db_event_tickets');
            if (!$clash) return $code;
        }
        return 'MP' . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 8));
    }

    public function get_tickets_for_order($order_id, $store_id) {
        if (!$this->_ready()) return [];
        $this->db->where('online_order_id', (int)$order_id);
        $this->db->where('store_id', (int)$store_id);
        $this->db->order_by('id', 'ASC');
        return $this->db->get('db_event_tickets')->result();
    }

    /**
     * Flat, buyer-facing ticket list for one order: code plus when/where.
     * Session and event lookups are cached because a 10-seat order is 10 rows
     * for the same date.
     */
    public function customerTickets($order_id, $store_id) {
        $out = [];
        $sessions = [];
        $events = [];
        foreach ($this->get_tickets_for_order($order_id, $store_id) as $t) {
            if ($t->status === 'void') continue;
            $sid = (int)$t->session_id;
            $eid = (int)$t->event_id;
            if (!isset($sessions[$sid])) $sessions[$sid] = $this->get_session($sid, $store_id);
            if (!isset($events[$eid])) $events[$eid] = $this->get_event($eid, $store_id);
            $s = $sessions[$sid];
            $e = $events[$eid];
            $out[] = [
                'code'   => (string)$t->ticket_code,
                'event'  => (string)($e->event_name ?? ''),
                'venue'  => (string)($e->venue ?? ''),
                'date'   => $s ? (string)$s->session_date : '',
                'time'   => $s ? (string)($s->start_time ?? '') : '',
                'status' => (string)$t->status,
            ];
        }
        return $out;
    }

    public function get_ticket_by_code($code, $store_id) {
        if (!$this->_ready()) return null;
        $code = strtoupper(trim((string)$code));
        if ($code === '') return null;
        $this->db->where('ticket_code', $code);
        $this->db->where('store_id', (int)$store_id);
        $ticket = $this->db->get('db_event_tickets')->row();
        if ($ticket) {
            $ticket->session = $this->get_session($ticket->session_id, $store_id);
            $ticket->event = $this->get_event($ticket->event_id, $store_id);
        }
        return $ticket;
    }

    public function redeem_ticket($code, $store_id) {
        $ticket = $this->get_ticket_by_code($code, $store_id);
        if (!$ticket || $ticket->status !== 'sold') return false;
        $this->db->where('id', (int)$ticket->id);
        $this->db->where('store_id', (int)$store_id);
        $this->db->update('db_event_tickets', [
            'status'      => 'redeemed',
            'redeemed_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->db->affected_rows() === 1;
    }

    public function tickets_for_session($session_id, $store_id, $status = null) {
        if (!$this->_ready()) return [];
        $this->db->where('session_id', (int)$session_id);
        $this->db->where('store_id', (int)$store_id);
        if ($status !== null) $this->db->where('status', $status);
        $this->db->order_by('id', 'ASC');
        return $this->db->get('db_event_tickets')->result();
    }

    public function session_counts($session_id, $store_id) {
        $out = ['sold' => 0, 'redeemed' => 0];
        if (!$this->_ready()) return $out;
        $rows = $this->db->select('status, COUNT(*) cnt')
            ->where('session_id', (int)$session_id)
            ->where('store_id', (int)$store_id)
            ->group_by('status')
            ->get('db_event_tickets')->result();
        foreach ($rows as $r) {
            if ($r->status === 'void') continue;
            $out['sold'] += (int)$r->cnt;
            if ($r->status === 'redeemed') $out['redeemed'] = (int)$r->cnt;
        }
        return $out;
    }
}
