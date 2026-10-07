<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Render helpers for the clinical report screens.
 *
 * These live in a helper rather than in the shared view partial because a view
 * is included inside CodeIgniter's loader function: closures defined in one
 * view are local to that include and are NOT visible to the view that loads it.
 */

if (!function_exists('cr_reports')) {
    /**
     * The clinical report registry — the single source of truth for both the
     * reports controller and the rail.
     *
     * `perm` is the grant the matching working screen requires; `money` marks
     * figures that need the reports grant (checked once by the controller) and
     * formats totals as currency; `retail` marks a report over the catalogue or
     * purchase ledger rather than over clinical data.
     */
    function cr_reports() {
        return array(
            'register' => array(
                'title' => 'Patient register',
                'sub'   => 'Register size, new registrations, gender and age profile.',
                'icon'  => 'fa-address-book-o',
                'perm'  => 'patients_view',
                'url'   => 'patients',
                'url_label' => 'Open register',
            ),
            'appointments' => array(
                'title' => 'Appointments & attendance',
                'sub'   => 'Bookings by status, attendance rate and clinician workload.',
                'icon'  => 'fa-calendar',
                'perm'  => 'appointments_view',
                'url'   => 'appointments',
                'url_label' => 'Open diary',
            ),
            'sessions' => array(
                'title' => 'Treatment sessions',
                'sub'   => 'Sessions delivered, units, clinician load and unposted fees.',
                'icon'  => 'fa-stethoscope',
                'perm'  => 'sessions_view',
                'url'   => 'sessions',
                'url_label' => 'Open sessions',
            ),
            'activity' => array(
                'title' => 'Clinical activity',
                'sub'   => 'Encounters, open queue, assessments and active plans.',
                'icon'  => 'fa-heartbeat',
                'perm'  => 'care_queue_view',
                'url'   => 'care_queue',
                'url_label' => 'Open care queue',
            ),
            'investigations' => array(
                'title' => 'Investigations',
                'sub'   => 'Requests by category, pending worklist and turnaround time.',
                'icon'  => 'fa-flask',
                'perm'  => 'investigations_view',
                'url'   => 'investigations',
                'url_label' => 'Open investigations',
            ),
            'ward' => array(
                'title' => 'Ward occupancy',
                'sub'   => 'Bed utilisation by ward, admissions, length of stay.',
                'icon'  => 'fa-bed',
                'perm'  => 'admissions_view',
                'url'   => 'inpatient/beds',
                'url_label' => 'Open bed board',
            ),
            'tasks' => array(
                'title' => 'Ward task load',
                'sub'   => 'Nursing and porter tasks raised, completed and overdue.',
                'icon'  => 'fa-exchange',
                'perm'  => 'nursing_tasks_view',
                'perm_any' => array('nursing_tasks_view','porter_tasks_view'),
                'url'   => 'inpatient/tasks',
                'url_label' => 'Open ward tasks',
            ),
            'outstanding' => array(
                'title' => 'Outstanding balances',
                'sub'   => 'Aged patient debt with the largest balances.',
                'icon'  => 'fa-exclamation-circle',
                'perm'  => 'patient_billing_view',
                'url'   => 'patient_billing',
                'url_label' => 'Open accounts',
                'money' => true,
            ),
            /* The leakage report: a clinic loses most of its revenue in the
               diary, so this sits with the clinical reports rather than with
               billing. */
            'cancellations' => array(
                'title' => 'Cancellations & no-shows',
                'sub'   => 'Lost appointments and cancelled sessions, by reason and by clinician.',
                'icon'  => 'fa-calendar-times-o',
                'perm'  => 'appointments_view',
                'perm_any' => array('appointments_view', 'sessions_view', 'care_queue_view'),
                'url'   => 'appointments',
                'url_label' => 'Open diary',
            ),
            'plans' => array(
                'title' => 'Treatment plans',
                'sub'   => 'Course progress, delivered against planned, and stalled episodes.',
                'icon'  => 'fa-list-alt',
                'perm'  => 'plans_view',
                'perm_any' => array('plans_view', 'sessions_view', 'patients_view'),
                'url'   => 'sessions',
                'url_label' => 'Open sessions',
            ),
            'documents' => array(
                'title' => 'Documents & consent',
                'sub'   => 'Record completeness, signatures outstanding and patients with no file.',
                'icon'  => 'fa-folder-open-o',
                'perm'  => 'patient_docs_view',
                'url'   => 'patient_docs',
                'url_label' => 'Open documents',
            ),
            'revenue' => array(
                'title' => 'Revenue & funds',
                'sub'   => 'Charges, collections, funds held and service mix.',
                'icon'  => 'fa-money',
                'perm'  => 'patient_billing_view',
                'url'   => 'patient_billing',
                'money' => true,
            ),
            'aids' => array(
                'title' => 'Therapy aids & material',
                'sub'   => 'Consumables used against patient work, by item and value.',
                'icon'  => 'fa-cubes',
                'perm'  => 'items_view',
                // The rail opens Services & pricing on any of these, so the
                // report that reads the same catalogue uses the same gate.
                'perm_any' => array('services_view','services_add','items_view'),
                'url'   => 'items',
                'url_label' => 'Open catalogue',
                'money' => true,
                'retail' => true,
            ),
            'procurement' => array(
                'title' => 'Procurement',
                'sub'   => 'Purchases of clinical supplies, by supplier and value.',
                'icon'  => 'fa-truck',
                'perm'  => 'purchase_view',
                'url'   => 'purchase',
                'url_label' => 'Open purchases',
                'money' => true,
                'retail' => true,
            ),
            'opening' => array(
                'title' => 'Opening positions',
                'sub'   => 'Imported balances still awaiting review or approval.',
                'icon'  => 'fa-sign-in',
                'perm'  => 'opening_positions_view',
                'url'   => 'patient_funds/openings',
                'url_label' => 'Open opening positions',
                'money' => true,
            ),
        );
    }
}

if (!function_exists('cr_can_see_reports')) {
    /**
     * Whether the viewer may open the Clinical reports screen at all.
     *
     * MUST stay identical to the check Clinical_reports::__construct() runs.
     * The rail gates its "Clinical reports" entry on this same function, so the
     * link and the route can never disagree.
     */
    function cr_can_see_reports() {
        if (!function_exists('physio_can')) { return false; }
        return physio_can('clinical_reports_view');
    }
}

if (!function_exists('cr_can_see')) {
    /**
     * Whether the viewer may open one report from the registry.
     *
     * Mirrors the gate Clinical_reports::allowed() applies, so a rail entry
     * built from this can never lead to a denial:
     *  - clinical reports use the explicit clinical grant (physio_can, no
     *    administrator bypass by design);
     *  - `retail` reports read the catalogue / purchase ledger, so they use the
     *    retail permission the matching working screen already requires,
     *    checked with permissions() so a store administrator is not locked out
     *    of their own inventory reports.
     *
     * @param array $r a report registry entry
     */
    function cr_can_see(array $r) {
        // The controller's constructor requires the reports grant for the whole
        // screen before it inspects a single report, so no registry entry can be
        // reachable without it. Check it here too, or a role that holds the
        // working screen's permission but not the reports grant (e.g. a
        // therapist with services_view) is handed a link the controller denies.
        if (!cr_can_see_reports()) { return false; }

        $CI =& get_instance();
        $any = !empty($r['perm_any']) ? $r['perm_any'] : array();
        if (!empty($r['retail'])) {
            if ($any) {
                foreach ($any as $p) { if ($CI->permissions($p)) { return true; } }
                return false;
            }
            return (bool) $CI->permissions($r['perm']);
        }
        if ($any) {
            foreach ($any as $p) { if (physio_can($p)) { return true; } }
            return false;
        }
        return (bool) physio_can($r['perm']);
    }
}

if (!function_exists('cr_kpi')) {
    /** A KPI card. $value is pre-formatted HTML-safe output. */
    function cr_kpi($label, $value, $sub = '', array $opts = array()) {
        $cls = 'cr-kpi';
        if (!empty($opts['money'])) { $cls .= ' money'; }
        if (!empty($opts['alert'])) { $cls .= ' alert'; }
        echo '<div class="' . $cls . '"><div class="lbl">' . htmlspecialchars($label) . '</div>'
            . '<div class="val">' . $value . '</div>';
        if ($sub !== '') { echo '<div class="sub">' . $sub . '</div>'; }
        echo '</div>';
    }
}

if (!function_exists('cr_bar')) {
    /** A horizontal share bar. $pct is 0-100, or null for "no data". */
    function cr_bar($pct) {
        if ($pct === null) { return '<span style="color:var(--mp-muted)">—</span>'; }
        $pct = max(0, min(100, (float)$pct));
        return '<div style="display:flex;align-items:center;gap:9px">'
            . '<div class="cr-bar"><span style="width:' . $pct . '%"></span></div>'
            . '<span style="font-variant-numeric:tabular-nums;font-size:12.5px;color:var(--mp-muted)">'
            . rtrim(rtrim(number_format($pct, 1), '0'), '.') . '%</span></div>';
    }
}

if (!function_exists('cr_money')) {
    /** Money through the app formatter, so placement and decimals stay consistent. */
    function cr_money($v) {
        $CI =& get_instance();
        return $CI->currency((float)$v);
    }
}

if (!function_exists('cr_number')) {
    /** Quantities may be fractional (sessions, materials) — keep it readable. */
    function cr_number($v, $decimals = 0) {
        $n = (float)$v;
        if ($decimals === 0 && $n == (int)$n) { return number_format($n); }
        return rtrim(rtrim(number_format($n, $decimals ?: 2), '0'), '.');
    }
}

if (!function_exists('cr_period_filter')) {
    /**
     * The date-range filter bar, shared by every report so the controls and the
     * quick ranges are identical everywhere.
     */
    function cr_period_filter($action, $from, $to) {
        $q = strpos($action, '?') === false ? '?' : '&';
        ?>
        <form class="cr-filter" method="get" action="<?= $action; ?>">
            <div>
                <label for="from">From</label>
                <input type="date" id="from" name="from" value="<?= htmlspecialchars($from); ?>">
            </div>
            <div>
                <label for="to">To</label>
                <input type="date" id="to" name="to" value="<?= htmlspecialchars($to); ?>">
            </div>
            <button type="submit" class="cr-btn primary"><i class="fa fa-filter"></i> Apply</button>
            <div class="cr-quick">
                <a class="cr-chip" href="<?= $action . $q; ?>from=<?= date('Y-m-01'); ?>&to=<?= date('Y-m-d'); ?>">This month</a>
                <a class="cr-chip" href="<?= $action . $q; ?>from=<?= date('Y-m-01', strtotime('-1 month')); ?>&to=<?= date('Y-m-t', strtotime('-1 month')); ?>">Last month</a>
                <a class="cr-chip" href="<?= $action . $q; ?>from=<?= date('Y-01-01'); ?>&to=<?= date('Y-m-d'); ?>">This year</a>
            </div>
        </form>
        <?php
    }
}

if (!function_exists('cr_toolbar')) {
    /**
     * The report header: breadcrumb, title, description and the two actions
     * every report has — jump to the working screen, or export what you see.
     */
    function cr_toolbar($meta, $key, $from, $to) {
        $export = base_url('clinical_reports/' . $key . '?from=' . $from . '&to=' . $to . '&export=1');
        ?>
        <div class="cr-head">
          <div>
            <div class="cr-eyebrow"><a href="<?= base_url('clinical_reports'); ?>" style="color:inherit">Clinical reports</a></div>
            <h1><?= htmlspecialchars($meta['title']); ?></h1>
            <p><?= htmlspecialchars($meta['sub']); ?></p>
          </div>
          <div class="cr-actions">
            <?php if(!empty($meta['url'])): ?>
              <a class="cr-btn" href="<?= base_url($meta['url']); ?>"><i class="fa fa-external-link"></i> <?= htmlspecialchars($meta['url_label'] ?? 'Open screen'); ?></a>
            <?php endif; ?>
            <a class="cr-btn primary" href="<?= $export; ?>"><i class="fa fa-download"></i> Export CSV</a>
          </div>
        </div>
        <?php
    }
}
