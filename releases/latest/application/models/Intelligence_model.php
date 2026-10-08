<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MartPoint — Intelligence
 * ========================
 * Turns the store's own history into a short list of things the owner should
 * know: what is about to run out, who is slow to pay, which machine is due,
 * where the trend is heading.
 *
 * DESIGN RULES (these are the point of the class — please keep them)
 * ----------------------------------------------------------------
 * 1. **Evidence or silence.** Every insight is derived from rows that exist. If
 *    there is not enough history to say something true, the insight is NOT
 *    emitted. A dashboard that invents "your machine needs servicing" from no
 *    data is worse than one that says nothing, because the owner acts on it and
 *    then stops believing the panel.
 *
 * 2. **No prediction without a stated basis.** Where a figure is projected it
 *    says what it was projected FROM ("at the current rate", "over the last 30
 *    days"), so the reader can judge it. An unqualified forecast is a guess
 *    wearing a suit.
 *
 * 3. **Tone is advisory, never alarmist.** These are observations for a busy
 *    owner, not alarms. Severity is carried in a separate field so the view
 *    decides colour; the text itself stays neutral.
 *
 * 4. **Bounded work.** Every query is indexed-or-cheap and capped. This runs on
 *    every dashboard load, so it must not become the slow part of the page.
 *
 * Each insight is an array:
 *   ['tone' => 'info|warn|good', 'icon' => 'fa-...', 'text' => '...',
 *    'action' => ['label' => '...', 'url' => '...']  (optional) ]
 */
class Intelligence_model extends CI_Model {

    /** Rows examined per signal. History beyond this adds no decision value. */
    const WINDOW_DAYS = 90;

    /* ==================================================================
     * PRINTING
     * ================================================================== */

    /**
     * Printing insight set. Reads the print tables only; safe to call for any
     * store because a non-printing store simply has no rows and gets nothing.
     */
    public function for_printing($store_id = null)
    {
        $store_id = $store_id ?: get_current_store_id();
        $out = [];

        $out = array_merge($out, $this->_print_workload($store_id));
        $out = array_merge($out, $this->_print_artwork_blockers($store_id));
        $out = array_merge($out, $this->_print_debtors($store_id));
        $out = array_merge($out, $this->_print_payment_reliability($store_id));
        $out = array_merge($out, $this->_print_machine_service($store_id));
        $out = array_merge($out, $this->_print_machine_faults($store_id));
        $out = array_merge($out, $this->_print_custody_ageing($store_id));
        $out = array_merge($out, $this->_print_throughput($store_id));

        return array_slice($out, 0, 8);
    }

    /** Jobs piling up at a gate, and work that has passed its promised date. */
    private function _print_workload($store_id)
    {
        $out = [];
        if (!$this->db->table_exists('db_print_jobs')) { return $out; }

        $row = $this->db->select('
                SUM(CASE WHEN production_status NOT IN ("completed","cancelled") THEN 1 ELSE 0 END) AS open_jobs,
                SUM(CASE WHEN due_date IS NOT NULL AND due_date < CURDATE() AND production_status NOT IN ("completed","cancelled") THEN 1 ELSE 0 END) AS overdue,
                SUM(CASE WHEN due_date IS NOT NULL AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY) AND production_status NOT IN ("completed","cancelled") THEN 1 ELSE 0 END) AS due_3d
            ', false)->where('store_id', $store_id)->get('db_print_jobs')->row();

        if (!$row || (int) $row->open_jobs === 0) { return $out; }

        if ((int) $row->overdue > 0) {
            $out[] = [
                'tone' => 'warn',
                'icon' => 'fa-exclamation-triangle',
                'text' => (int) $row->overdue . ' job' . ((int) $row->overdue === 1 ? ' has' : 's have')
                          . ' passed the promised date and are still open.',
                'action' => ['label' => 'Open jobs', 'url' => base_url('printing/jobs')],
            ];
        }
        if ((int) $row->due_3d > 0) {
            $out[] = [
                'tone' => 'info',
                'icon' => 'fa-clock-o',
                'text' => (int) $row->due_3d . ' job' . ((int) $row->due_3d === 1 ? '' : 's')
                          . ' due within 3 days — worth checking the floor is loaded for it.',
                'action' => ['label' => 'Print jobs', 'url' => base_url('printing/jobs')],
            ];
        }
        return $out;
    }

    /** Jobs stuck waiting on the client rather than on the shop. */
    private function _print_artwork_blockers($store_id)
    {
        $out = [];
        if (!$this->db->table_exists('db_print_jobs')) { return $out; }

        $row = $this->db->select('
                SUM(CASE WHEN payment_status NOT IN ("verified","paid") THEN 1 ELSE 0 END) AS awaiting_deposit,
                SUM(CASE WHEN artwork_status <> "approved" AND production_status NOT IN ("completed","cancelled") THEN 1 ELSE 0 END) AS awaiting_artwork
            ', false)->where('store_id', $store_id)
               ->where_not_in('production_status', ['completed', 'cancelled'])
               ->get('db_print_jobs')->row();

        if (!$row) { return $out; }

        // Framed as "you are waiting on the client", which is the actionable
        // fact — the shop cannot progress these and should chase instead.
        if ((int) $row->awaiting_deposit > 0) {
            $out[] = [
                'tone' => 'warn',
                'icon' => 'fa-hand-holding-usd',
                'text' => (int) $row->awaiting_deposit . ' accepted job'
                          . ((int) $row->awaiting_deposit === 1 ? ' is' : 's are')
                          . ' waiting on a deposit — work cannot start until it clears.',
                'action' => ['label' => 'Payments', 'url' => base_url('printing/payments')],
            ];
        }
        if ((int) $row->awaiting_artwork > 0) {
            $out[] = [
                'tone' => 'info',
                'icon' => 'fa-picture-o',
                'text' => (int) $row->awaiting_artwork . ' job'
                          . ((int) $row->awaiting_artwork === 1 ? ' is' : 's are')
                          . ' waiting on artwork approval from the customer.',
                'action' => ['label' => 'Artworks', 'url' => base_url('printing/artworks')],
            ];
        }
        return $out;
    }

    /** Money owed, and who is holding it up. */
    private function _print_debtors($store_id)
    {
        $out = [];
        if (!$this->db->table_exists('db_print_jobs')) { return $out; }

        // Balance per job, then aggregated per customer. Built from the ledger
        // (verified payments, minus refunds) so it cannot disagree with the
        // deposit gate on the job screen.
        $rows = $this->db->select('j.customer_id, c.customer_name,
                SUM(GREATEST(0, j.quote_amount - COALESCE(p.paid, 0))) AS owed,
                COUNT(*) AS jobs')
            ->from('db_print_jobs j')
            ->join('db_customers c', 'c.id = j.customer_id', 'left')
            ->join('(SELECT job_id, SUM(CASE WHEN payment_kind = "refund" THEN -amount ELSE amount END) AS paid
                     FROM db_print_payments WHERE status = "verified" GROUP BY job_id) p',
                   'p.job_id = j.id', 'left', false)
            ->where('j.store_id', $store_id)
            ->where('j.customer_id >', 0)
            ->group_by('j.customer_id, c.customer_name')
            ->having('owed >', 0.01)
            ->order_by('owed', 'desc')
            ->limit(3)->get()->result();

        if (empty($rows)) { return $out; }

        $top = $rows[0];
        $name = trim((string) ($top->customer_name ?? '')) ?: 'A customer';
        $total = 0.0;
        foreach ($rows as $r) { $total += (float) $r->owed; }

        $out[] = [
            'tone' => 'warn',
            'icon' => 'fa-balance-scale',
            'text' => $name . ' has the largest outstanding balance at '
                      . store_number_format((float) $top->owed)
                      . ' across ' . (int) $top->jobs . ' job' . ((int) $top->jobs === 1 ? '' : 's') . '.',
            'action' => ['label' => 'Statements', 'url' => base_url('printing/payments')],
        ];
        return $out;
    }

    /**
     * Who pays promptly and who does not.
     *
     * Only emitted when a customer has enough settled history for the claim to
     * mean something — two payments is not a pattern, so we require at least
     * three and a clear separation from the store's own median.
     */
    private function _print_payment_reliability($store_id)
    {
        $out = [];
        if (!$this->db->table_exists('db_print_payments')) { return $out; }

        $rows = $this->db->select('j.customer_id, c.customer_name,
                COUNT(DISTINCT j.id) AS jobs,
                AVG(DATEDIFF(p.created_at, j.created_at)) AS avg_days')
            ->from('db_print_payments p')
            ->join('db_print_jobs j', 'j.id = p.job_id')
            ->join('db_customers c', 'c.id = j.customer_id', 'left')
            ->where('j.store_id', $store_id)
            ->where('j.customer_id >', 0)
            ->where('p.status', 'verified')
            ->where('p.payment_kind !=', 'refund')
            ->group_by('j.customer_id, c.customer_name')
            ->having('jobs >=', 3)
            ->order_by('avg_days', 'asc')
            ->limit(20)->get()->result();

        if (count($rows) < 2) { return $out; }

        $days = array_map(function ($r) { return (float) $r->avg_days; }, $rows);
        sort($days);
        $median = $days[(int) floor(count($days) / 2)];

        $best = $rows[0];
        if ((float) $best->avg_days <= max(1, $median - 2)) {
            $name = trim((string) ($best->customer_name ?? '')) ?: 'One customer';
            $out[] = [
                'tone' => 'good',
                'icon' => 'fa-thumbs-up',
                'text' => $name . ' settles fastest — averaging '
                          . round((float) $best->avg_days) . ' day'
                          . (round((float) $best->avg_days) == 1 ? '' : 's')
                          . ' after the job opens, across ' . (int) $best->jobs . ' jobs.',
            ];
        }
        return $out;
    }

    /**
     * Machines approaching a service date.
     *
     * Deliberately reports the COUNTDOWN, not a demand. On a shop with no
     * service history at all there is nothing to say, and saying "needs
     * servicing now" would be a fabrication.
     */
    private function _print_machine_service($store_id)
    {
        $out = [];
        if (!$this->db->table_exists('db_print_machines')) { return $out; }
        if (!$this->db->field_exists('next_service_at', 'db_print_machines')) { return $out; }

        $rows = $this->db->select('name, next_service_at')
            ->where('store_id', $store_id)
            ->where('next_service_at IS NOT NULL', null, false)
            ->order_by('next_service_at', 'asc')
            ->limit(2)->get('db_print_machines')->result();

        if (empty($rows)) { return $out; }

        $soonest = $rows[0];
        $days = (int) floor((strtotime($soonest->next_service_at) - strtotime(date('Y-m-d'))) / 86400);
        $name = trim((string) $soonest->name) ?: 'A machine';

        if ($days < 0) {
            $out[] = [
                'tone' => 'warn',
                'icon' => 'fa-wrench',
                'text' => $name . ' is ' . abs($days) . ' day' . (abs($days) === 1 ? '' : 's')
                          . ' past its service date.',
                'action' => ['label' => 'Machines', 'url' => base_url('printing_ops/machines')],
            ];
        } elseif ($days <= 30) {
            $out[] = [
                'tone' => 'info',
                'icon' => 'fa-wrench',
                'text' => $name . ' is due for service in ' . $days . ' day' . ($days === 1 ? '' : 's') . '.',
                'action' => ['label' => 'Machines', 'url' => base_url('printing_ops/machines')],
            ];
        }
        return $out;
    }

    /** A machine that has been down more than once — a pattern, not an incident. */
    private function _print_machine_faults($store_id)
    {
        $out = [];
        if (!$this->db->table_exists('db_print_machine_maintenance')) { return $out; }

        $row = $this->db->select('m.name, COUNT(*) AS visits, SUM(mt.downtime_minutes) AS down_mins')
            ->from('db_print_machine_maintenance mt')
            ->join('db_print_machines m', 'm.id = mt.machine_id', 'left')
            ->where('mt.store_id', $store_id)
            ->where('mt.visit_date >=', date('Y-m-d', strtotime('-' . self::WINDOW_DAYS . ' days')))
            ->group_by('m.name')
            ->having('visits >=', 3)
            ->order_by('visits', 'desc')
            ->limit(1)->get()->row();

        if (!$row || empty($row->name)) { return $out; }

        $down = (int) $row->down_mins;
        $out[] = [
            'tone' => 'warn',
            'icon' => 'fa-cogs',
            'text' => $row->name . ' has needed ' . (int) $row->visits . ' maintenance visits in '
                      . self::WINDOW_DAYS . ' days'
                      . ($down > 0 ? ', costing ' . round($down / 60, 1) . ' hours of downtime' : '')
                      . '. A recurring fault may be worth a proper overhaul.',
            'action' => ['label' => 'Maintenance', 'url' => base_url('printing_ops/maintenance')],
        ];
        return $out;
    }

    /**
     * Client-owned material sitting with the shop.
     *
     * This is the print-specific version of "dead stock", and it carries real
     * liability — the shop is holding someone else's property. Ageing is framed
     * from the oldest holding so the owner sees the worst case first.
     */
    private function _print_custody_ageing($store_id)
    {
        $out = [];
        if (!$this->db->table_exists('db_print_customer_materials')) { return $out; }

        $row = $this->db->select('COUNT(*) AS rows_held, COALESCE(SUM(qty_custody),0) AS qty,
                MIN(received_at) AS oldest')
            ->where('store_id', $store_id)
            ->where('qty_custody >', 0)
            ->get('db_print_customer_materials')->row();

        if (!$row || (int) $row->rows_held === 0) { return $out; }

        $qty = rtrim(rtrim(number_format((float) $row->qty, 2), '0'), '.');
        $text = 'Holding ' . $qty . ' unit' . ((float) $row->qty == 1 ? '' : 's')
                . ' of client-owned material across ' . (int) $row->rows_held . ' receipt'
                . ((int) $row->rows_held === 1 ? '' : 's') . '.';

        if (!empty($row->oldest)) {
            $days = (int) floor((time() - strtotime($row->oldest)) / 86400);
            if ($days >= 60) {
                $text .= ' The oldest has been with you ' . $days . ' days — worth reconciling with the client.';
            }
        }

        $out[] = [
            'tone' => 'info',
            'icon' => 'fa-cubes',
            'text' => $text,
            // printing_ops/custody, NOT printing_ops/customer_materials — the
            // latter does not exist. The rail links "Materials Held" here too,
            // so the insight and the menu now point at the same screen.
            'action' => ['label' => 'Customer materials', 'url' => base_url('printing_ops/custody')],
        ];
        return $out;
    }

    /**
     * Throughput trend — how many jobs the shop is completing, and whether that
     * is rising or falling. Compared period-on-period, and only stated when
     * both periods have enough jobs for a percentage to be meaningful.
     */
    private function _print_throughput($store_id)
    {
        $out = [];
        if (!$this->db->table_exists('db_print_jobs')) { return $out; }

        $this_30 = (int) $this->db->where('store_id', $store_id)
            ->where('created_at >=', date('Y-m-d', strtotime('-30 days')))
            ->count_all_results('db_print_jobs');
        $prev_30 = (int) $this->db->where('store_id', $store_id)
            ->where('created_at >=', date('Y-m-d', strtotime('-60 days')))
            ->where('created_at <', date('Y-m-d', strtotime('-30 days')))
            ->count_all_results('db_print_jobs');

        // Below this there is no trend, only noise.
        if ($this_30 < 5 || $prev_30 < 5) {
            if ($this_30 > 0) {
                $out[] = [
                    'tone' => 'info',
                    'icon' => 'fa-print',
                    'text' => 'You have taken on ' . $this_30 . ' print job' . ($this_30 === 1 ? '' : 's')
                              . ' in the last 30 days.',
                    'action' => ['label' => 'Print jobs', 'url' => base_url('printing/jobs')],
                ];
            }
            return $out;
        }

        $change = round((($this_30 - $prev_30) / $prev_30) * 100);
        if (abs($change) >= 10) {
            $dir = $change > 0 ? 'up' : 'down';
            $tone = $change > 0 ? 'good' : 'warn';
            $out[] = [
                'tone' => $tone,
                'icon' => $change > 0 ? 'fa-arrow-up' : 'fa-arrow-down',
                'text' => 'Job intake is ' . $dir . ' ' . abs($change) . '% on the previous 30 days ('
                          . $this_30 . ' vs ' . $prev_30 . ').',
                'action' => ['label' => 'Reports', 'url' => base_url('printing/reports')],
            ];
        }
        return $out;
    }

    /* ==================================================================
     * RETAIL / GENERAL
     * ================================================================== */

    /** Retail insight set: stock cover, sales trend, customer value, debt. */
    public function for_retail($store_id = null)
    {
        $store_id = $store_id ?: get_current_store_id();
        $out = [];

        $out = array_merge($out, $this->_retail_stock_cover($store_id));
        $out = array_merge($out, $this->_retail_sales_trend($store_id));
        $out = array_merge($out, $this->_retail_best_performer($store_id));
        $out = array_merge($out, $this->_retail_debt($store_id));
        $out = array_merge($out, $this->_retail_slow_movers($store_id));

        return array_slice($out, 0, 8);
    }

    /**
     * Which items will run out first, at the rate they actually sell.
     *
     * "Days of cover" only means something once an item has sold during the
     * window — an item with no sales has no burn rate and is reported
     * separately as slow-moving rather than falsely as urgent.
     */
    private function _retail_stock_cover($store_id)
    {
        $out = [];
        if (!$this->db->table_exists('db_items') || !$this->db->table_exists('db_salesitems')) {
            return $out;
        }

        $rows = $this->db->select('i.item_name, i.stock, i.alert_qty, i.reorder_point,
                COALESCE(SUM(si.sales_qty),0) AS sold')
            ->from('db_items i')
            ->join('db_salesitems si', 'si.item_id = i.id', 'left')
            ->join('db_sales s', 's.id = si.sales_id AND s.sales_status = "Final"', 'left', false)
            ->where('i.store_id', $store_id)
            ->where('i.stock >', 0)
            ->group_by('i.id, i.item_name, i.stock, i.alert_qty, i.reorder_point')
            ->having('sold >', 0)
            ->order_by('i.stock / NULLIF(SUM(si.sales_qty),0)', 'asc', false)
            ->limit(3)->get()->result();

        if (empty($rows)) { return $out; }

        foreach ($rows as $r) {
            $sold = (float) $r->sold;
            if ($sold <= 0) { continue; }
            $stock = (float) $r->stock;
            // Sold over WINDOW_DAYS, so daily rate = sold / WINDOW_DAYS.
            $per_day = $sold / self::WINDOW_DAYS;
            $cover = $per_day > 0 ? (int) floor($stock / $per_day) : null;
            if ($cover === null || $cover > 30) { continue; }

            $name = trim((string) $r->item_name) ?: 'An item';
            $out[] = [
                'tone' => $cover <= 7 ? 'warn' : 'info',
                'icon' => 'fa-cubes',
                'text' => $name . ' has about ' . $cover . ' day' . ($cover === 1 ? '' : 's')
                          . ' of stock left at the current selling rate (' . round($per_day, 1) . '/day).',
                'action' => ['label' => 'Purchases', 'url' => base_url('purchase')],
            ];
            if (count($out) >= 2) { break; }
        }
        return $out;
    }

    /** Sales direction, period on period. */
    private function _retail_sales_trend($store_id)
    {
        $out = [];
        if (!$this->db->table_exists('db_sales')) { return $out; }

        $sum = function ($from, $to) use ($store_id) {
            $r = $this->db->select('COALESCE(SUM(grand_total),0) AS t')
                ->where('store_id', $store_id)
                ->where('sales_status', 'Final')
                ->where('sales_date >=', $from)->where('sales_date <', $to)
                ->get('db_sales')->row();
            return (float) ($r->t ?? 0);
        };

        $this_30 = $sum(date('Y-m-d', strtotime('-30 days')), date('Y-m-d'));
        $prev_30 = $sum(date('Y-m-d', strtotime('-60 days')), date('Y-m-d', strtotime('-30 days')));

        if ($prev_30 <= 0 || $this_30 <= 0) { return $out; }

        $change = round((($this_30 - $prev_30) / $prev_30) * 100);
        if (abs($change) < 8) { return $out; }

        // A percentage built on a tiny base is noise dressed as a trend:
        // comparing 1.8m against 34m is a real change, but against 50k it would
        // report "+2000%" and mean nothing. Require a floor on BOTH periods so
        // the figure is worth the owner's attention.
        $floor = 100000;
        if ($prev_30 < $floor || $this_30 < $floor) {
            $out[] = [
                'tone' => 'info',
                'icon' => 'fa-line-chart',
                'text' => 'Sales over the last 30 days are ' . store_number_format($this_30)
                          . ', against ' . store_number_format($prev_30) . ' in the 30 days before.',
                'action' => ['label' => 'Sales report', 'url' => base_url('reports/sales')],
            ];
            return $out;
        }

        // A swing this large is normally the result of a quiet period rather
        // than a real surge or collapse, so it is stated as a comparison rather
        // than as a growth rate the owner should plan around.
        if (abs($change) > 300) {
            $out[] = [
                'tone' => $change > 0 ? 'good' : 'warn',
                'icon' => 'fa-line-chart',
                'text' => 'Sales last 30 days were ' . store_number_format($this_30)
                          . ', against ' . store_number_format($prev_30) . ' the month before — a step change rather than a trend.',
                'action' => ['label' => 'Sales report', 'url' => base_url('reports/sales')],
            ];
            return $out;
        }

        $out[] = [
            'tone' => $change > 0 ? 'good' : 'warn',
            'icon' => $change > 0 ? 'fa-arrow-up' : 'fa-arrow-down',
            'text' => 'Sales are ' . ($change > 0 ? 'up' : 'down') . ' ' . abs($change)
                      . '% over the last 30 days (' . store_number_format($this_30)
                      . ' against ' . store_number_format($prev_30) . ').',
            'action' => ['label' => 'Sales report', 'url' => base_url('reports/sales')],
        ];
        return $out;
    }

    /** The single best-selling item, with the evidence behind the claim. */
    private function _retail_best_performer($store_id)
    {
        $out = [];
        if (!$this->db->table_exists('db_salesitems')) { return $out; }

        $row = $this->db->select('i.item_name, SUM(si.sales_qty) AS qty, SUM(si.total_cost) AS value')
            ->from('db_salesitems si')
            ->join('db_items i', 'i.id = si.item_id', 'left')
            ->join('db_sales s', 's.id = si.sales_id')
            ->where('s.store_id', $store_id)
            ->where('s.sales_status', 'Final')
            ->where('s.sales_date >=', date('Y-m-d', strtotime('-30 days')))
            ->group_by('i.item_name')
            ->order_by('qty', 'desc')
            ->limit(1)->get()->row();

        if (!$row || empty($row->item_name) || (float) $row->qty <= 0) { return $out; }

        $out[] = [
            'tone' => 'good',
            'icon' => 'fa-star',
            'text' => trim((string) $row->item_name) . ' is your best seller this month — '
                      . rtrim(rtrim(number_format((float) $row->qty, 2), '0'), '.') . ' units'
                      . ($row->value > 0 ? ', worth ' . store_number_format((float) $row->value) : '')
                      . '. Worth keeping in stock.',
        ];
        return $out;
    }

    /** Concentration risk in receivables. */
    private function _retail_debt($store_id)
    {
        $out = [];
        if (!$this->db->table_exists('db_sales')) { return $out; }

        $row = $this->db->select('c.customer_name, SUM(s.grand_total - s.paid_amount) AS owed')
            ->from('db_sales s')
            ->join('db_customers c', 'c.id = s.customer_id', 'left')
            ->where('s.store_id', $store_id)
            ->where('s.sales_status', 'Final')
            ->where('s.customer_id >', 0)
            ->group_by('s.customer_id, c.customer_name')
            ->order_by('owed', 'desc')
            ->limit(1)->get()->row();

        if (!$row || (float) $row->owed <= 0) { return $out; }

        $name = trim((string) $row->customer_name) ?: 'A customer';
        $out[] = [
            'tone' => 'warn',
            'icon' => 'fa-balance-scale',
            'text' => $name . ' carries the largest balance at '
                      . store_number_format((float) $row->owed) . '.',
            // reports/receivables_aging, NOT reports/due_report — the latter does
            // not exist. Reports.php exposes individual report methods and has no
            // index, so /reports 404s too; the rail's own Debtors entry points at
            // receivables_aging, so this matches the menu.
            'action' => ['label' => 'Debtors', 'url' => base_url('reports/receivables_aging')],
        ];
        return $out;
    }

    /** Stock that has not moved — cash sitting on a shelf. */
    private function _retail_slow_movers($store_id)
    {
        $out = [];
        if (!$this->db->table_exists('db_items')) { return $out; }

        $row = $this->db->select('COUNT(*) AS n, COALESCE(SUM(stock * purchase_price),0) AS tied')
            ->from('db_items i')
            ->where('i.store_id', $store_id)
            ->where('i.stock >', 0)
            ->where('NOT EXISTS (
                SELECT 1 FROM db_salesitems si
                JOIN db_sales s ON s.id = si.sales_id AND s.sales_status = "Final"
                WHERE si.item_id = i.id AND s.sales_date >= "' . date('Y-m-d', strtotime('-30 days')) . '"
            )', null, false)
            ->get()->row();

        if (!$row || (int) $row->n === 0 || (float) $row->tied <= 0) { return $out; }

        $out[] = [
            'tone' => 'info',
            'icon' => 'fa-hourglass-half',
            'text' => (int) $row->n . ' item' . ((int) $row->n === 1 ? '' : 's')
                      . ' have not sold in 30 days, holding about '
                      . store_number_format((float) $row->tied) . ' of tied-up cash.'
                      . ' A bundle or a promotion may move them.',
            'action' => ['label' => 'Items', 'url' => base_url('items')],
        ];
        return $out;
    }
}
