<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $CI =& get_instance(); ?>
<div class="mp-section print-reports">
    <div class="mp-page-head">
        <h2>Printing Reports</h2>
        <div class="mp-page-sub">Category &amp; month performance, collections, receivables, actual vs estimated cost, referral expense and per-job profitability.</div>
    </div>

    <!-- Filters -->
    <div class="rep-filters">
        <form method="get" action="" class="rep-filter-form">
            <div class="rep-filter-row">
                <div class="rep-filter-group">
                    <label>Date From</label>
                    <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from']) ?>" class="mp-input">
                </div>
                <div class="rep-filter-group">
                    <label>Date To</label>
                    <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to']) ?>" class="mp-input">
                </div>
                <div class="rep-filter-group">
                    <label>Category</label>
                    <select name="category" class="mp-input">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat->id ?>" <?= $filters['category'] == $cat->id ? 'selected' : '' ?>><?= htmlspecialchars($cat->category_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="rep-filter-group">
                    <label>Customer</label>
                    <select name="customer_id" class="mp-input">
                        <option value="">All Customers</option>
                        <?php foreach ($customers as $cust): ?>
                        <option value="<?= $cust->id ?>" <?= $filters['customer_id'] == $cust->id ? 'selected' : '' ?>><?= htmlspecialchars($cust->customer_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="rep-filter-group">
                    <label>Status</label>
                    <select name="status" class="mp-input">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="in_progress" <?= $filters['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= $filters['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="rep-filter-actions">
                    <button type="submit" class="mp-btn mp-btn-primary">Apply Filters</button>
                    <a href="<?= base_url('printing/reports') ?>" class="mp-btn mp-btn-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- ===== Production analytics: the numbers a print shop is run on ===== -->
    <?php
      $fun = $analytics['quote_funnel'] ?? [];
      $q   = $analytics['quality'] ?? [];
      $cst = $analytics['cost_accuracy'] ?? [];
      $trn = $analytics['turnaround'] ?? [];
      $bot = $analytics['stage_bottleneck'] ?? [];
      $money = function ($v) use ($CI) { return $CI->currency() . number_format((float)$v, 2); };
      $pct = function ($v) { return $v === null ? '—' : number_format((float)$v, 1) . '%'; };
      $tone = function ($v, $good, $bad) {
          if ($v === null) return '#5b6f78';
          return $v >= $good ? '#059669' : ($v <= $bad ? '#dc2626' : '#b45309');
      };
    ?>

    <h3 class="rep-subhead">Production Health</h3>
    <div class="rep-kpis">
      <div class="rep-kpi">
        <span class="rep-kpi-label">Quote Win Rate</span>
        <span class="rep-kpi-value" style="color:<?= $tone($fun['won_rate'] ?? null, 50, 25) ?>"><?= $pct($fun['won_rate'] ?? null) ?></span>
        <span class="rep-kpi-note"><?= (int)($fun['accepted'] ?? 0) ?> won of <?= (int)($fun['quoted'] ?? 0) ?> quoted</span>
      </div>
      <div class="rep-kpi">
        <span class="rep-kpi-label">On-Time Delivery</span>
        <span class="rep-kpi-value" style="color:<?= $tone($trn['on_time_pct'] ?? null, 90, 70) ?>"><?= $pct($trn['on_time_pct'] ?? null) ?></span>
        <span class="rep-kpi-note"><?= (int)($trn['on_time'] ?? 0) ?> on time · <?= (int)($trn['late'] ?? 0) ?> late<?= isset($trn['avg_days']) && $trn['avg_days'] !== null ? ' · avg ' . $trn['avg_days'] . 'd' : '' ?></span>
      </div>
      <div class="rep-kpi">
        <span class="rep-kpi-label">Spoilage</span>
        <span class="rep-kpi-value" style="color:<?= $tone($q['waste_pct'] ?? null, 3, 8) ?>"><?= $pct($q['waste_pct'] ?? null) ?></span>
        <span class="rep-kpi-note"><?= number_format((float)($q['loss_qty'] ?? 0), 0) ?> lost of <?= number_format((float)($q['qty_in'] ?? 0), 0) ?> in</span>
      </div>
      <div class="rep-kpi">
        <span class="rep-kpi-label">Cost vs Estimate</span>
        <span class="rep-kpi-value" style="color:<?= $tone($cst['variance_pct'] ?? null, 0, 10) ?>"><?= isset($cst['variance_pct']) && $cst['variance_pct'] !== null ? ($cst['variance_pct'] > 0 ? '+' : '') . number_format((float)$cst['variance_pct'], 1) . '%' : '—' ?></span>
        <span class="rep-kpi-note"><?= $money($cst['estimated'] ?? 0) ?> est · <?= $money($cst['actual'] ?? 0) ?> actual</span>
      </div>
    </div>

    <!-- Quote funnel -->
    <?php if (!empty($fun) && (int)($fun['total'] ?? 0) > 0): ?>
    <h3 class="rep-subhead">Quotation Funnel</h3>
    <div class="rep-funnel">
      <?php
        $stages = [
          ['Quoted',   (int)($fun['quoted'] ?? 0),     '#0e7490'],
          ['Won',      (int)($fun['accepted'] ?? 0),   '#059669'],
          ['Awaiting', (int)($fun['awaiting'] ?? 0),   '#d97706'],
          ['Declined', (int)($fun['declined'] ?? 0),   '#dc2626'],
          ['Expired',  (int)($fun['expired'] ?? 0),    '#64748b'],
        ];
        $max = max(1, (int)($fun['quoted'] ?? 0));
        foreach ($stages as $s):
          $w = round($s[1] / $max * 100);
      ?>
      <div class="rep-funnel-row">
        <span class="rep-funnel-label"><?= $s[0] ?></span>
        <div class="rep-funnel-track"><div class="rep-funnel-bar" style="width:<?= $w ?>%;background:<?= $s[2] ?>"></div></div>
        <span class="rep-funnel-n"><?= $s[1] ?></span>
      </div>
      <?php endforeach; ?>
      <?php if ((int)($fun['not_quoted'] ?? 0) > 0): ?>
      <div class="rep-funnel-note"><?= (int)$fun['not_quoted'] ?> job(s) in this period have no quotation raised yet.</div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Stage bottleneck -->
    <?php if (!empty($bot)): ?>
    <h3 class="rep-subhead">Stage Turnaround <span class="rep-subhead-note">slowest first — this is where work is waiting</span></h3>
    <div class="table-wrap" style="overflow-x:auto;">
      <table class="table table-striped rep-table">
        <thead>
          <tr>
            <th>Stage</th><th class="text-right">Jobs</th><th class="text-right">Still Open</th>
            <th class="text-right">Completed</th><th class="text-right">Avg Time</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bot as $s): $slow = $s->avg_hours !== null && $s->avg_hours >= 48; ?>
          <tr>
            <td><span class="rep-cat"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $s->stage_key))) ?></span></td>
            <td class="text-right"><?= (int)$s->jobs ?></td>
            <td class="text-right"><?= (int)$s->open_jobs ?></td>
            <td class="text-right"><?= (int)$s->done_jobs ?></td>
            <td class="text-right" style="color:<?= $slow ? '#dc2626' : '#102a35' ?>;font-weight:<?= $slow ? '700' : '400' ?>">
              <?= $s->avg_hours === null ? '—' : ($s->avg_hours >= 48 ? number_format($s->avg_hours / 24, 1) . ' d' : $s->avg_hours . ' h') ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <!-- Spoilage detail -->
    <?php if (!empty($q) && (float)($q['qty_in'] ?? 0) > 0): ?>
    <h3 class="rep-subhead">Production Quality</h3>
    <div class="rep-quality">
      <div class="rep-quality-item"><span>Good</span><b style="color:#059669"><?= number_format((float)$q['good_qty'], 0) ?></b></div>
      <div class="rep-quality-item"><span>Rework</span><b style="color:#b45309"><?= number_format((float)$q['rework_qty'], 0) ?></b></div>
      <div class="rep-quality-item"><span>Rejected</span><b style="color:#dc2626"><?= number_format((float)$q['reject_qty'], 0) ?></b></div>
      <div class="rep-quality-item"><span>Waste</span><b style="color:#dc2626"><?= number_format((float)$q['waste_qty'], 0) ?></b></div>
      <div class="rep-quality-item"><span>Scrap</span><b style="color:#64748b"><?= number_format((float)$q['scrap_qty'], 0) ?></b></div>
      <div class="rep-quality-item rep-quality-yield"><span>Yield</span><b style="color:#0b5d73"><?= $pct($q['yield_pct']) ?></b></div>
    </div>
    <?php endif; ?>

    <h3 class="rep-subhead">Revenue</h3>
    <!-- Money summary -->
    <div class="rep-summary">
        <div class="rep-sum-item">
            <span class="rep-sum-label">Total Jobs</span>
            <span class="rep-sum-value"><?= $summary['total_jobs'] ?></span>
        </div>
        <div class="rep-sum-item">
            <span class="rep-sum-label">Total Revenue</span>
            <span class="rep-sum-value"><?= $CI->currency() . number_format($summary['total_revenue'], 2) ?></span>
        </div>
        <div class="rep-sum-item">
            <span class="rep-sum-label">Total Cost</span>
            <span class="rep-sum-value"><?= $CI->currency() . number_format($summary['total_cost'], 2) ?></span>
        </div>
        <div class="rep-sum-item">
            <span class="rep-sum-label">Gross Profit</span>
            <span class="rep-sum-value" style="color:<?= $summary['total_profit'] < 0 ? '#dc2626' : '#059669' ?>;"><?= $CI->currency() . number_format($summary['total_profit'], 2) ?></span>
        </div>
        <div class="rep-sum-item">
            <span class="rep-sum-label">Avg Job Value</span>
            <span class="rep-sum-value"><?= $CI->currency() . number_format($summary['avg_job_value'], 2) ?></span>
        </div>
        <div class="rep-sum-item">
            <span class="rep-sum-label">Profit Margin</span>
            <span class="rep-sum-value" style="color:<?= $summary['profit_margin'] < 20 ? '#dc2626' : '#059669' ?>;"><?= number_format($summary['profit_margin'], 1) ?>%</span>
        </div>
    </div>

    <!-- Status Breakdown -->
    <?php if (!empty($summary['status_counts'])): ?>
    <h3 class="rep-subhead">Job Status Breakdown</h3>
    <div class="rep-status-grid">
        <?php foreach ($summary['status_counts'] as $status => $count): 
            $pct = $summary['total_jobs'] > 0 ? ($count / $summary['total_jobs']) * 100 : 0;
            $colors = [
                'pending' => '#f59e0b',
                'in_progress' => '#3b82f6',
                'completed' => '#10b981',
                'cancelled' => '#ef4444',
            ];
            $color = $colors[$status] ?? '#64748b';
        ?>
        <div class="rep-status-card">
            <div class="rep-status-header">
                <span class="rep-status-label"><?= ucfirst(str_replace('_', ' ', $status)) ?></span>
                <span class="rep-status-count"><?= $count ?> jobs</span>
            </div>
            <div class="rep-status-bar">
                <div class="rep-status-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div>
            </div>
            <div class="rep-status-pct"><?= number_format($pct, 1) ?>%</div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Top Customers -->
    <?php if (!empty($top_customers)): ?>
    <h3 class="rep-subhead">Top Customers by Revenue</h3>
    <div class="table-wrap" style="overflow-x:auto;">
        <table class="table table-striped rep-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th class="text-right">Jobs</th>
                    <th class="text-right">Total Revenue</th>
                    <th class="text-right">Avg per Job</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_customers as $tc): ?>
                <tr>
                    <td><?= htmlspecialchars($tc->customer_name) ?></td>
                    <td class="text-right"><?= $tc->job_count ?></td>
                    <td class="text-right"><?= $CI->currency() . number_format($tc->total_revenue, 2) ?></td>
                    <td class="text-right"><?= $CI->currency() . number_format($tc->total_revenue / $tc->job_count, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Top Categories -->
    <?php if (!empty($top_categories)): ?>
    <h3 class="rep-subhead">Top Categories by Revenue</h3>
    <div class="table-wrap" style="overflow-x:auto;">
        <table class="table table-striped rep-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-right">Jobs</th>
                    <th class="text-right">Total Revenue</th>
                    <th class="text-right">Avg per Job</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_categories as $tc): ?>
                <tr>
                    <td><span class="rep-cat"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $tc->category_key))) ?></span></td>
                    <td class="text-right"><?= $tc->job_count ?></td>
                    <td class="text-right"><?= $CI->currency() . number_format($tc->total_revenue, 2) ?></td>
                    <td class="text-right"><?= $CI->currency() . number_format($tc->total_revenue / $tc->job_count, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Monthly Trend -->
    <?php if (!empty($monthly_trend)): ?>
    <h3 class="rep-subhead">Monthly Revenue Trend (Last 6 Months)</h3>
    <div class="rep-trend-chart">
        <?php 
        $max_revenue = max(array_column($monthly_trend, 'revenue'));
        foreach ($monthly_trend as $mt): 
            $height_pct = $max_revenue > 0 ? ($mt->revenue / $max_revenue) * 100 : 0;
        ?>
        <div class="rep-trend-bar">
            <div class="rep-trend-fill" style="height:<?= $height_pct ?>%"></div>
            <div class="rep-trend-label"><?= date('M Y', strtotime($mt->month . '-01')) ?></div>
            <div class="rep-trend-value"><?= $CI->currency() . number_format($mt->revenue, 0) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Category × month -->
    <h3 class="rep-subhead">By Category &amp; Month</h3>
    <div class="table-wrap" style="overflow-x:auto;">
        <table class="table table-striped rep-table">
            <thead><tr><th>Month</th><th>Category</th><th class="text-right">Jobs</th><th class="text-right">Value</th><th class="text-right">Cost</th><th class="text-right">Gross Profit</th></tr></thead>
            <tbody>
                <?php if (empty($cat_month)): ?>
                <tr><td colspan="6" class="text-muted text-center rep-empty">No category data yet.</td></tr>
                <?php else: foreach ($cat_month as $cm): $gp = (float)$cm->value - (float)$cm->cost; ?>
                <tr>
                    <td><?= htmlspecialchars($cm->ym) ?></td>
                    <td><span class="rep-cat"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $cm->category_key))) ?></span></td>
                    <td class="text-right"><?= (int)$cm->jobs ?></td>
                    <td class="text-right"><?= $CI->currency() . number_format((float)$cm->value, 2) ?></td>
                    <td class="text-right"><?= $CI->currency() . number_format((float)$cm->cost, 2) ?></td>
                    <td class="text-right" style="color:<?= $gp < 0 ? '#dc2626' : '#059669' ?>;"><?= $CI->currency() . number_format($gp, 2) ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Per-job profitability -->
    <h3 class="rep-subhead">Per-Job Profitability</h3>
    <div class="table-wrap" style="overflow-x:auto;">
        <table class="table table-striped rep-table">
            <thead>
                <tr><th>Job</th><th>Customer</th><th class="text-right">Revenue</th><th class="text-right">Estimated</th><th class="text-right">Actual</th><th class="text-right">Gross Profit</th><th class="text-right">Margin</th><th>Cost Complete</th></tr>
            </thead>
            <tbody>
                <?php if (empty($reports)): ?>
                <tr><td colspan="8" class="text-muted text-center rep-empty">No jobs to report on.</td></tr>
                <?php else: foreach ($reports as $r): ?>
                <tr>
                    <td><span class="rep-code"><?= htmlspecialchars($r['job']->job_code) ?></span></td>
                    <td><?= htmlspecialchars($r['job']->customer_name ?: '—') ?></td>
                    <td class="text-right"><?= $CI->currency() . number_format($r['revenue'], 2) ?></td>
                    <td class="text-right"><?= $CI->currency() . number_format($r['estimated_total'], 2) ?></td>
                    <td class="text-right"><?= $CI->currency() . number_format($r['actual_total'], 2) ?></td>
                    <td class="text-right" style="color:<?= $r['gross_profit'] < 0 ? '#dc2626' : '#059669' ?>;"><?= $CI->currency() . number_format($r['gross_profit'], 2) ?></td>
                    <td class="text-right"><?= $r['margin_pct'] !== null ? $r['margin_pct'] . '%' : '—' ?></td>
                    <td><?= $r['cost_complete'] ? '<span class="rep-ok">✓</span>' : '<span class="rep-warn">partial</span>' ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.print-reports{font-family:'Inter',sans-serif}

/* Filters */
.rep-filters{background:#fff;border:1px solid #d5e3e8;border-radius:12px;padding:20px;margin-bottom:22px}
.rep-filter-form{width:100%}
.rep-filter-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;align-items:end}
.rep-filter-group{display:flex;flex-direction:column;gap:6px}
.rep-filter-group label{font-size:12px;font-weight:600;color:#5b6f78;text-transform:uppercase;letter-spacing:.04em}
.rep-filter-group .mp-input{padding:8px 12px;border:1px solid #d5e3e8;border-radius:8px;font-size:14px;background:#fff;color:#0b5d73}
.rep-filter-group .mp-input:focus{outline:none;border-color:#0e7490;box-shadow:0 0 0 3px rgba(14,116,144,.1)}
.rep-filter-actions{display:flex;gap:8px;align-items:end}
.rep-filter-actions .mp-btn{padding:8px 16px;font-size:14px;font-weight:600;border-radius:8px;cursor:pointer;transition:all .15s ease;text-decoration:none;display:inline-block}
.rep-filter-actions .mp-btn-primary{background:#0e7490;color:#fff;border:none}
.rep-filter-actions .mp-btn-primary:hover{background:#0b5d73}
.rep-filter-actions .mp-btn-secondary{background:#fff;color:#0e7490;border:1px solid #0e7490}
.rep-filter-actions .mp-btn-secondary:hover{background:#f0f9fa}

/* Summary cards */
.rep-summary{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:22px}
.rep-sum-item{background:#fff;border:1px solid #d5e3e8;border-left:3px solid #0e7490;border-radius:12px;padding:16px}
.rep-sum-label{display:block;font-size:11px;color:#5b6f78;text-transform:uppercase;letter-spacing:.05em;font-weight:700}
.rep-sum-value{display:block;font-size:20px;font-weight:800;font-family:'Bricolage Grotesque',sans-serif;color:#0b5d73;margin-top:6px}

/* Tables */
.rep-subhead{font-size:16px;font-family:'Bricolage Grotesque',sans-serif;font-weight:700;color:#102a35;margin:22px 0 10px}
.rep-table th{font-size:12px;color:#5b6f78;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid #d5e3e8;background:#fff}
.rep-table td{padding:12px;border-bottom:1px solid #eef5f7}
.rep-table tr:hover{background:#f8fbfc}
.rep-cat{background:#e0f2f8;color:#0b5d73;border-radius:999px;padding:2px 10px;font-size:12px;font-weight:600}
.rep-code{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px;background:#eef5f7;border:1px solid #d5e3e8;border-radius:6px;padding:2px 7px;color:#0b5d73}
.rep-ok{color:#059669;font-weight:700}
.rep-warn{background:#fef3c7;color:#b45309;border-radius:999px;padding:2px 8px;font-size:11px;font-weight:700}
.rep-empty{padding:26px!important;text-align:center;color:#5b6f78;font-style:italic}

/* Production analytics */
.rep-subhead-note{font-size:12px;font-weight:500;color:#5b6f78;margin-left:8px}
.rep-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(215px,1fr));gap:14px;margin-bottom:22px}
.rep-kpi{background:#fff;border:1px solid #d5e3e8;border-radius:12px;padding:16px}
.rep-kpi-label{display:block;font-size:11px;color:#5b6f78;text-transform:uppercase;letter-spacing:.05em;font-weight:700}
.rep-kpi-value{display:block;font-size:26px;font-weight:800;font-family:'Bricolage Grotesque',sans-serif;margin:6px 0 4px;line-height:1.1}
.rep-kpi-note{display:block;font-size:11.5px;color:#5b6f78;line-height:1.5}

.rep-funnel{background:#fff;border:1px solid #d5e3e8;border-radius:12px;padding:18px 20px;margin-bottom:22px}
.rep-funnel-row{display:grid;grid-template-columns:90px 1fr 44px;align-items:center;gap:12px;padding:5px 0}
.rep-funnel-label{font-size:12.5px;font-weight:600;color:#102a35}
.rep-funnel-track{height:12px;background:#eef5f7;border-radius:999px;overflow:hidden}
.rep-funnel-bar{height:100%;border-radius:999px;transition:width .3s ease}
.rep-funnel-n{font-size:13px;font-weight:800;color:#0b5d73;text-align:right}
.rep-funnel-note{font-size:12px;color:#5b6f78;margin-top:10px;padding-top:10px;border-top:1px solid #eef5f7}

.rep-quality{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:12px;margin-bottom:22px}
.rep-quality-item{background:#fff;border:1px solid #d5e3e8;border-radius:10px;padding:13px 15px;display:flex;flex-direction:column;gap:4px}
.rep-quality-item span{font-size:11px;color:#5b6f78;text-transform:uppercase;letter-spacing:.04em;font-weight:700}
.rep-quality-item b{font-size:18px;font-weight:800;font-family:'Bricolage Grotesque',sans-serif}
.rep-quality-yield{border-left:3px solid #0e7490}

/* Responsive */
@media (max-width:768px){
    .rep-filter-row{grid-template-columns:1fr}
    .rep-filter-actions{flex-direction:column;width:100%}
    .rep-filter-actions .mp-btn{width:100%}
    .rep-funnel-row{grid-template-columns:74px 1fr 36px;gap:8px}
}

/* Status breakdown */
.rep-status-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:22px}
.rep-status-card{background:#fff;border:1px solid #d5e3e8;border-radius:12px;padding:18px}
.rep-status-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
.rep-status-label{font-size:13px;font-weight:600;color:#0b5d73;text-transform:capitalize}
.rep-status-count{font-size:12px;color:#5b6f78;font-weight:600}
.rep-status-bar{height:8px;background:#eef5f7;border-radius:999px;overflow:hidden;margin-bottom:6px}
.rep-status-fill{height:100%;border-radius:999px;transition:width .3s ease}
.rep-status-pct{font-size:11px;color:#5b6f78;font-weight:700}

/* Trend chart */
.rep-trend-chart{display:flex;gap:12px;align-items:flex-end;height:200px;padding:20px;background:#fff;border:1px solid #d5e3e8;border-radius:12px;margin-bottom:22px}
.rep-trend-bar{flex:1;display:flex;flex-direction:column;align-items:center;gap:8px;position:relative}
.rep-trend-fill{width:100%;background:linear-gradient(to top,#0e7490,#06b6d4);border-radius:8px 8px 0 0;min-height:4px;transition:height .3s ease}
.rep-trend-label{font-size:11px;color:#5b6f78;font-weight:600;text-align:center}
.rep-trend-value{font-size:10px;color:#0b5d73;font-weight:700;text-align:center}
</style>
