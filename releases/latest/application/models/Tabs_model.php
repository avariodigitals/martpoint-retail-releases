<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Running tabs.
 *
 * A tab is a floor-service grouping and nothing else. Every round is still an
 * ordinary db_sales invoice, every payment still lives in db_salespayments, and
 * the balance shown for a tab is summed from its rounds at read time — so a tab
 * can never report money the ledger does not, and this model cannot create a
 * second payment or stock engine.
 */
class Tabs_model extends CI_Model {

    const STATUS_OPEN      = 'open';
    const STATUS_SETTLED   = 'settled';
    const STATUS_CANCELLED = 'cancelled';

    public function __construct(){
        parent::__construct();
    }

    /** True only once migration 4.0.9.227 has run on this install. */
    public function is_available(){
        return $this->db->table_exists('db_tabs') && $this->db->field_exists('tab_id', 'db_sales');
    }

    public function get($tab_id, $store_id){
        if(!$this->is_available()) return null;
        return $this->db->where('id', (int)$tab_id)->where('store_id', (int)$store_id)
            ->get('db_tabs')->row();
    }

    public function get_open_by_table($table_id, $store_id){
        if(!$this->is_available() || (int)$table_id <= 0) return null;
        return $this->db->where('table_id', (int)$table_id)->where('store_id', (int)$store_id)
            ->where('status', self::STATUS_OPEN)->order_by('id', 'asc')->limit(1)
            ->get('db_tabs')->row();
    }

    /**
     * Open a tab, or return the tab this request already opened.
     *
     * Two safeties matter on a busy floor: the client key makes a retried
     * "open tab" press idempotent, and one table may only hold one open tab, so
     * a second waiter tapping the same table continues the running bill instead
     * of splitting it in two.
     */
    public function open($data){
        if(!$this->is_available()){
            return ['ok' => false, 'tab' => null, 'message' => 'Running tabs are not installed on this store yet.'];
        }
        $store_id = (int)($data['store_id'] ?? get_current_store_id());
        if($store_id <= 0){
            return ['ok' => false, 'tab' => null, 'message' => 'No store for this tab.'];
        }
        $table_id = (int)($data['table_id'] ?? 0);
        $key = substr(trim((string)($data['idempotency_key'] ?? '')), 0, 96);
        /* A table id is only meaningful on this store's floor. Without the check
         * a posted id from another tenant would open a tab that points at their
         * table and inherits their section. */
        if($table_id > 0 && $this->db->table_exists('db_tables')){
            $mine = $this->db->where('id', $table_id)->where('store_id', $store_id)
                ->count_all_results('db_tables') > 0;
            if(!$mine){
                return ['ok' => false, 'tab' => null, 'message' => 'That table is not on your floor.'];
            }
        }

        if($key !== ''){
            $replay = $this->db->where('store_id', $store_id)->where('idempotency_key', $key)
                ->get('db_tabs')->row();
            if($replay) return ['ok' => true, 'tab' => $replay, 'message' => 'Tab already opened.', 'replayed' => true];
        }
        if($table_id > 0){
            $existing = $this->get_open_by_table($table_id, $store_id);
            if($existing) return ['ok' => true, 'tab' => $existing, 'message' => 'This table already has an open tab.', 'reused' => true];
        }

        $now = date('Y-m-d H:i:s');
        // A sectioned floor already knows who is behind this table, so the tab
        // opens named instead of waiting for somebody to pick a waiter. From
        // here the chain that 4.0.9.231 built takes over: the tab's waiter is
        // copied onto every round that joins it, so each order is tied to a
        // person without a second order table.
        // Only the inheritance is switched — a name typed onto the tab by hand
        // is honoured whether or not the floor uses sections.
        $waiter = (int)($data['waiter_user_id'] ?? 0);
        if($waiter <= 0 && $table_id > 0 && function_exists('mp_feature_enabled')
            && mp_feature_enabled('waiter_assignment')){
            $waiter = $this->_table_section_waiter($table_id, $store_id);
        }
        $insert = [
            'store_id'        => $store_id,
            'warehouse_id'    => (int)($data['warehouse_id'] ?? 0),
            'tab_code'        => '',
            'tab_type'        => $this->_clean_type($data['tab_type'] ?? ($table_id > 0 ? 'table' : 'bar')),
            'table_id'        => $table_id,
            'customer_id'     => !empty($data['customer_id']) ? (int)$data['customer_id'] : null,
            'guest_name'      => $this->_clean_text($data['guest_name'] ?? '', 120),
            'waiter_user_id'  => $waiter > 0 ? $waiter : null,
            'covers'          => max(0, (int)($data['covers'] ?? 0)),
            'note'            => $this->_clean_text($data['note'] ?? '', 255),
            'status'          => self::STATUS_OPEN,
            'idempotency_key' => $key !== '' ? $key : null,
            'opened_by'       => $this->_actor(),
            'opened_at'       => $now,
            'created_at'      => $now,
            'updated_at'      => $now,
        ];
        $this->db->insert('db_tabs', $insert);
        $tab_id = (int)$this->db->insert_id();
        if($tab_id <= 0){
            // Two taps of "open tab" can race past the key lookup above; the
            // unique key then rejects the second insert, and the tab the client
            // is asking for already exists. Re-read it instead of reporting a
            // failure the cashier cannot act on.
            if($key !== ''){
                $race = $this->db->where('store_id', $store_id)->where('idempotency_key', $key)
                    ->get('db_tabs')->row();
                if($race) return ['ok' => true, 'tab' => $race, 'message' => 'Tab already opened.', 'replayed' => true];
            }
            return ['ok' => false, 'tab' => null, 'message' => 'Could not open the tab. Nothing was saved.'];
        }
        // The code is the row id so it is unique without a second insert pass
        // racing on a daily counter, and short enough to read on a receipt line.
        $this->db->where('id', $tab_id)->update('db_tabs', ['tab_code' => 'TAB-' . $tab_id]);

        $this->_set_table_status($table_id, $store_id, 'occupied');
        return ['ok' => true, 'tab' => $this->get($tab_id, $store_id), 'message' => 'Tab opened.'];
    }

    /**
     * Link a saved round invoice to its tab. Returns true or an error string.
     *
     * The link is write-once: a replayed save re-reads as already attached
     * rather than moving the invoice, and a round can never be pointed at a tab
     * that has closed — that is how a settled bill stops growing after the guest
     * has paid.
     */
    public function attach_round($tab_id, $sales_id, $store_id){
        $tab_id = (int)$tab_id;
        $store_id = (int)$store_id;
        if(!$this->is_available()) return 'Running tabs are not installed on this store yet.';
        if($tab_id <= 0) return true;

        $tab = $this->get($tab_id, $store_id);
        if(!$tab) return 'That tab does not belong to this store. The round was not charged to it.';
        if($tab->status !== self::STATUS_OPEN){
            return 'This tab was already ' . $tab->status . '. Open a new tab before taking another round.';
        }

        $current = $this->db->select('tab_id, table_id')->where('id', (int)$sales_id)->where('store_id', $store_id)
            ->get('db_sales')->row();
        if($current && (int)$current->tab_id === $tab_id) return true;
        if($current && (int)$current->tab_id > 0) return 'This invoice already belongs to another tab.';
        // A round served at table 7 cannot be charged to table 3's bill, however
        // the picker was left when the invoice was written.
        if($tab->table_id > 0 && $current && (int)$current->table_id > 0 && (int)$current->table_id !== (int)$tab->table_id){
            return 'This tab belongs to a different table. Pick the tab for this table, or pay the round separately.';
        }

        $this->db->where('id', (int)$sales_id)->where('store_id', $store_id)->where('tab_id', null)
            ->update('db_sales', ['tab_id' => $tab_id]);
        if($this->db->affected_rows() < 1){
            return 'Could not add this round to the tab. The round was not charged to it.';
        }
        // Every order belongs to a person, not just to a table. The tab's waiter
        // is copied onto the round the moment it joins the bill, and never
        // rewritten afterwards: a handover at shift change must not move last
        // Friday's takings onto whoever holds the tab now.
        if($this->db->field_exists('waiter_user_id', 'db_sales') && (int)$tab->waiter_user_id > 0){
            $this->db->where('id', (int)$sales_id)->where('store_id', $store_id)
                ->where('waiter_user_id', null)
                ->update('db_sales', ['waiter_user_id' => (int)$tab->waiter_user_id]);
        }
        $this->db->where('id', $tab_id)->update('db_tabs', ['updated_at' => date('Y-m-d H:i:s')]);
        return true;
    }

    /**
     * Live figures for one tab, summed from its round invoices. Nothing here is
     * stored, so an edit, return or payment against a round is reflected at once.
     */
    public function totals($tab_id, $store_id){
        $out = ['rounds' => 0, 'subtotal' => 0.0, 'discount' => 0.0, 'total' => 0.0, 'paid' => 0.0, 'outstanding' => 0.0, 'tips' => 0.0];
        if(!$this->is_available()) return $out;
        $this->db->select("COUNT(*) AS rounds,
                           COALESCE(SUM(subtotal),0) AS subtotal,
                           COALESCE(SUM(tot_discount_to_all_amt),0) AS discount,
                           COALESCE(SUM(grand_total),0) AS total,
                           COALESCE(SUM(paid_amount),0) AS paid", FALSE);
        // Tips are what the guests left the floor, so they ride beside the bill
        // total instead of inside it — the drawer still has to hand the cash over.
        $has_tips = $this->db->field_exists('tip_amount', 'db_sales');
        if($has_tips) $this->db->select('COALESCE(SUM(tip_amount),0) AS tips', FALSE);
        $this->db->where('store_id', (int)$store_id)->where('tab_id', (int)$tab_id)
            ->where('sales_status', 'Final');
        if($this->db->field_exists('status', 'db_sales')) $this->db->where('status', 1);
        $row = $this->db->get('db_sales')->row();
        if(!$row) return $out;
        $out['rounds']    = (int)$row->rounds;
        $out['subtotal']  = round((float)$row->subtotal, 2);
        $out['discount']  = round((float)$row->discount, 2);
        $out['total']     = round((float)$row->total, 2);
        $out['paid']      = round((float)$row->paid, 2);
        $out['outstanding'] = max(0.0, round($out['total'] - $out['paid'], 2));
        $out['tips']      = $has_tips ? round((float)($row->tips ?? 0), 2) : 0.0;
        return $out;
    }

    /** Rounds on a tab, oldest first, numbered the way the kitchen ticket reads. */
    public function rounds($tab_id, $store_id, $limit = 50){
        if(!$this->is_available()) return [];
        $this->db->select('id, sales_code, sales_date, created_time, subtotal, tot_discount_to_all_amt, grand_total, paid_amount, payment_status, sales_status, created_by');
        $this->db->where('store_id', (int)$store_id)->where('tab_id', (int)$tab_id);
        if($this->db->field_exists('status', 'db_sales')) $this->db->where('status', 1);
        $this->db->order_by('id', 'asc')->limit((int)$limit);
        $rows = $this->db->get('db_sales')->result();
        $rounds = [];
        foreach($rows as $i => $row){
            $row->round_no = $i + 1;
            $row->outstanding = round((float)$row->grand_total - (float)$row->paid_amount, 2);
            $rounds[] = $row;
        }
        return $rounds;
    }

    /** Open tabs for the floor board, with totals for all of them in one pass. */
    public function list_open($store_id, $warehouse_id = null, $limit = 60){
        if(!$this->is_available()) return [];
        $this->db->where('store_id', (int)$store_id)->where('status', self::STATUS_OPEN);
        if($warehouse_id !== null && $warehouse_id !== '') $this->db->where('warehouse_id', (int)$warehouse_id);
        $this->db->order_by('id', 'asc')->limit((int)$limit);
        $tabs = $this->db->get('db_tabs')->result();
        if(!$tabs) return [];

        $ids = array_map(function($t){ return (int)$t->id; }, $tabs);
        $this->db->select('tab_id, COUNT(*) AS rounds, COALESCE(SUM(grand_total),0) AS total, COALESCE(SUM(paid_amount),0) AS paid', FALSE);
        $this->db->where('store_id', (int)$store_id)->where_in('tab_id', $ids)->where('sales_status', 'Final');
        if($this->db->field_exists('status', 'db_sales')) $this->db->where('status', 1);
        $this->db->group_by('tab_id');
        $by_tab = [];
        foreach($this->db->get('db_sales')->result() as $agg){
            $by_tab[(int)$agg->tab_id] = $agg;
        }
        foreach($tabs as $tab){
            $agg = $by_tab[(int)$tab->id] ?? null;
            $tab->rounds      = $agg ? (int)$agg->rounds : 0;
            $tab->total       = $agg ? round((float)$agg->total, 2) : 0.0;
            $tab->paid        = $agg ? round((float)$agg->paid, 2) : 0.0;
            $tab->outstanding = $agg ? max(0.0, round((float)$agg->total - (float)$agg->paid, 2)) : 0.0;
        }
        return $tabs;
    }

    /**
     * Close a tab once every round on it is paid.
     *
     * Settlement deliberately records no money: the payment belongs on the
     * round invoice (or across invoices through the existing payment screens).
     * Refusing while a balance remains is what keeps a "settled" tab from
     * hiding an unpaid invoice.
     */
    public function settle($tab_id, $store_id){
        $tab = $this->get((int)$tab_id, (int)$store_id);
        if(!$tab) return ['ok' => false, 'message' => 'That tab does not belong to this store.'];
        if($tab->status === self::STATUS_SETTLED) return ['ok' => true, 'message' => 'This tab is already settled.'];
        if($tab->status !== self::STATUS_OPEN) return ['ok' => false, 'message' => 'This tab was already cancelled.'];

        $totals = $this->totals($tab->id, $store_id);
        if($totals['outstanding'] > 0.005){
            return ['ok' => false, 'message' => $this->_money($totals['outstanding']) . ' is still owing on ' . $totals['rounds']
                . ' round(s). Take the payment against the bill first.'];
        }
        $now = date('Y-m-d H:i:s');
        $this->db->where('id', (int)$tab_id)->where('store_id', (int)$store_id)->update('db_tabs', [
            'status'      => self::STATUS_SETTLED,
            'settled_at'  => $now,
            'updated_at'  => $now,
        ]);
        $this->_set_table_status((int)$tab->table_id, (int)$store_id, 'cleaning');
        return ['ok' => true, 'message' => $tab->tab_code . ' settled.'];
    }

    /**
     * Cancel a tab that was opened by mistake.
     *
     * Only an untouched tab can be cancelled. Once rounds exist they are real
     * invoices in the ledger, and quietly closing their tab would leave them
     * owing with nothing pointing at them — so a served table is settled or its
     * invoices are voided, both of which keep the audit trail.
     */
    public function cancel($tab_id, $store_id, $reason = ''){
        $tab = $this->get((int)$tab_id, (int)$store_id);
        if(!$tab) return ['ok' => false, 'message' => 'That tab does not belong to this store.'];
        if($tab->status === self::STATUS_CANCELLED) return ['ok' => true, 'message' => 'This tab is already cancelled.'];
        if($tab->status !== self::STATUS_OPEN) return ['ok' => false, 'message' => 'This tab was already settled.'];

        $totals = $this->totals($tab->id, $store_id);
        if($totals['rounds'] > 0){
            return ['ok' => false, 'message' => $tab->tab_code . ' already has ' . $totals['rounds']
                . ' round(s) on the ledger. Settle it, or void those invoices the usual way, instead of cancelling the tab.'];
        }
        $now = date('Y-m-d H:i:s');
        $this->db->where('id', (int)$tab_id)->where('store_id', (int)$store_id)->update('db_tabs', [
            'status'        => self::STATUS_CANCELLED,
            'cancelled_at'  => $now,
            'cancel_reason' => $this->_clean_text($reason, 255),
            'updated_at'    => $now,
        ]);
        $this->_set_table_status((int)$tab->table_id, (int)$store_id, 'available');
        return ['ok' => true, 'message' => $tab->tab_code . ' cancelled.'];
    }

    /** Name the waiter holding the tab, for the handover at shift change. */
    public function set_waiter($tab_id, $store_id, $user_id){
        $tab = $this->get((int)$tab_id, (int)$store_id);
        if(!$tab) return ['ok' => false, 'message' => 'That tab does not belong to this store.'];
        if($tab->status !== self::STATUS_OPEN) return ['ok' => false, 'message' => 'This tab is closed.'];
        $user_id = (int)$user_id;
        if($user_id > 0){
            $ok = $this->db->where('id', $user_id)->where('store_id', (int)$store_id)->count_all_results('db_users') > 0;
            if(!$ok) return ['ok' => false, 'message' => 'That staff member is not in this store.'];
        }
        $this->db->where('id', (int)$tab_id)->where('store_id', (int)$store_id)->update('db_tabs', [
            'waiter_user_id' => $user_id > 0 ? $user_id : null,
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        return ['ok' => true, 'message' => 'Waiter updated.'];
    }

    private function _set_table_status($table_id, $store_id, $status){
        if($table_id <= 0 || !$this->db->table_exists('db_tables')) return;
        $this->load->model('tables_model', 'mp_tabs_tables');
        $this->mp_tabs_tables->update_status($table_id, $store_id, $status);
    }

    /**
     * The waiter a table is sectioned to, or 0 when the table is unassigned,
     * the column has not been migrated yet, or the person no longer works here.
     *
     * The active-staff check is deliberate: a waiter who left the floor must not
     * keep collecting other people's takings every time their old table is sat.
     * Their name stays on the bills they actually served, because those rows are
     * never rewritten.
     */
    private function _table_section_waiter($table_id, $store_id){
        if(!$this->db->table_exists('db_tables') || !$this->db->field_exists('waiter_user_id', 'db_tables')) return 0;
        $row = $this->db->select('waiter_user_id')->where('id', (int)$table_id)
            ->where('store_id', (int)$store_id)->get('db_tables')->row();
        $user_id = (int)($row->waiter_user_id ?? 0);
        if($user_id <= 0 || !$this->db->table_exists('db_users')) return 0;
        $live = $this->db->where('id', $user_id)->where('store_id', (int)$store_id)
            ->where('status', 1)->count_all_results('db_users') > 0;
        return $live ? $user_id : 0;
    }

    private function _clean_type($type){
        $type = strtolower(trim((string)$type));
        return in_array($type, ['table', 'bar', 'guest'], true) ? $type : 'bar';
    }

    private function _clean_text($value, $max){
        $value = trim(strip_tags((string)$value));
        return $value === '' ? null : substr($value, 0, $max);
    }

    private function _actor(){
        $name = $this->session->userdata('display_name') ?: $this->session->userdata('username');
        return $name ? substr((string)$name, 0, 50) : null;
    }

    private function _money($amount){
        return number_format((float)$amount, 2);
    }
}
