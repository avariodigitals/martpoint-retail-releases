<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
$CI =& get_instance();
$canAddJob = $CI->permissions('print_jobs_add');
$canPay = $CI->permissions('print_payments');
$store_name = $CI->session->userdata('store_name') ?: 'MartPoint';
$range_labels = ['Today'=>'Today','7Days'=>'7 Days','30Days'=>'30 Days','ThisMonth'=>'This Month','ThisYear'=>'This Year'];
$range_label = $range_labels[$range] ?? 'Today';
?>

<!-- SECTION 1: BUSINESS OVERVIEW (KPIs) -->
<div class="mp-section">
  <div class="mp-page-head" style="flex-wrap:wrap;">
    <div style="flex:1 1 100%;">
      <h2>Printing Overview</h2>
      <div class="mp-page-sub"><?= htmlspecialchars($range_label); ?> · <?= htmlspecialchars($store_name); ?></div>
    </div>
    <div class="print-head-row" style="flex:1 1 100%;display:flex;flex-wrap:wrap;align-items:center;gap:8px 10px;margin:16px 0 4px;">
      <div class="mp-quick-actions print-qa" style="margin:0;">
        <?php if ($canAddJob): ?><a href="<?= base_url('printing/job') ?>" class="mp-qa-btn green"><i class="fa fa-plus"></i> New Job</a><?php endif; ?>
        <a href="<?= base_url('printing/jobs') ?>" class="mp-qa-btn blue"><i class="fa fa-list-ul"></i> Jobs</a>
        <?php if ($canPay): ?><a href="<?= base_url('printing/payments') ?>" class="mp-qa-btn orange"><i class="fa fa-money"></i> Payment</a><?php endif; ?>
        <a href="<?= base_url('customers/add') ?>" class="mp-qa-btn purple"><i class="fa fa-user-plus"></i> Client</a>
      </div>
      <div class="mp-range-tabs print-tabs" style="flex:0 0 auto;margin-left:auto;">
        <?php foreach ($ranges as $rk): ?>
        <a href="<?= base_url('printing?range=' . $rk) ?>" class="tab <?= ($range === $rk) ? 'active' : '' ?>"><?= $range_labels[$rk] ?? $rk ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <style>
      .print-head-row .print-qa{width:auto!important;margin-bottom:0!important;gap:6px!important;}
      .print-head-row .print-qa .mp-qa-btn{padding:7px 10px!important;font-size:12px!important;height:auto!important;}
      .print-head-row .print-tabs{margin-left:auto!important;}
      .print-head-row .print-tabs .tab{padding:6px 9px!important;font-size:12px!important;}
    </style>
  </div>

  <div class="mp-kpi-grid">
    <!-- Operational KPIs (top) -->
    <div class="mp-kpi-card stock">
      <div class="mp-kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div>
      <div class="mp-kpi-label">Open Jobs</div>
      <div class="mp-kpi-value"><?= (int)$kpis['open']; ?></div>
      <div class="mp-kpi-sub neutral"><?= (int)$kpis['awaiting_quote'] ?> awaiting quote</div>
    </div>
    <div class="mp-kpi-card expense">
      <div class="mp-kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 20h20"/><path d="M5 20V9l7-5 7 5v11"/><path d="M9 20v-6h6v6"/></svg></div>
      <div class="mp-kpi-label">In Production</div>
      <div class="mp-kpi-value"><?= (int)$kpis['in_production']; ?></div>
      <div class="mp-kpi-sub neutral">On the floor now</div>
    </div>
    <div class="mp-kpi-card debt">
      <div class="mp-kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="9 12 11 14 15 9"/></svg></div>
      <div class="mp-kpi-label">Collection</div>
      <div class="mp-kpi-value"><?= (int)$kpis['awaiting_fulfilment']; ?></div>
      <div class="mp-kpi-sub <?= $kpis['awaiting_fulfilment'] > 0 ? 'down' : 'up' ?>">Awaiting pick-up</div>
    </div>
    <div class="mp-kpi-card expense">
      <div class="mp-kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
      <div class="mp-kpi-label">Overdue Jobs</div>
      <div class="mp-kpi-value"><?= (int)$kpis['overdue']; ?></div>
      <div class="mp-kpi-sub <?= $kpis['overdue'] > 0 ? 'down' : 'up' ?>"><?= (int)$kpis['due_soon'] ?> due ≤ 7 days</div>
    </div>

    <!-- Money cards (bottom) -->
    <div class="mp-kpi-card sales">
      <div class="mp-kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
      <div class="mp-kpi-label"><?= $range_label; ?> Collected</div>
      <div class="mp-kpi-value"><?= $CI->currency($daily['collected']); ?></div>
      <div class="mp-kpi-sub neutral"><?= (int)$daily['jobs_created'] ?> jobs created</div>
    </div>
    <div class="mp-kpi-card cash">
      <div class="mp-kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="12" cy="12" r="3"/><line x1="2" y1="10" x2="22" y2="10"/></svg></div>
      <div class="mp-kpi-label">Cash Payments</div>
      <div class="mp-kpi-value"><?= $CI->currency($daily['cash']); ?></div>
      <div class="mp-kpi-sub neutral"><?= $range_label; ?></div>
    </div>
    <div class="mp-kpi-card profit">
      <div class="mp-kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></div>
      <div class="mp-kpi-label">Bank Payments</div>
      <div class="mp-kpi-value"><?= $CI->currency($daily['bank']); ?></div>
      <div class="mp-kpi-sub neutral"><?= $range_label; ?></div>
    </div>
    <div class="mp-kpi-card debt">
      <div class="mp-kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg></div>
      <div class="mp-kpi-label">Outstanding Balance</div>
      <div class="mp-kpi-value"><?= $CI->currency($kpis['outstanding_balance']); ?></div>
      <div class="mp-kpi-sub <?= $kpis['outstanding_balance'] > 0 ? 'down' : 'up' ?>"><?= $kpis['outstanding_balance'] > 0 ? 'To collect' : 'All settled' ?></div>
    </div>
  </div>
</div>

<!-- LICENSE & USAGE -->
<?php $lic_summary = function_exists('mp_get_license_usage_summary') ? mp_get_license_usage_summary() : null;
if ($lic_summary):
  $lic_status = $lic_summary['status'];
  $lic_status_color = ['ACTIVE'=>'var(--mp-success)','EXPIRING_SOON'=>'var(--mp-warning)','EXPIRED'=>'var(--mp-danger)','SUSPENDED'=>'var(--mp-danger)','NOT_ACTIVATED'=>'var(--mp-muted)'][$lic_status] ?? 'var(--mp-muted)';
  $lic_status_label = ['ACTIVE'=>'Active','EXPIRING_SOON'=>'Expiring Soon','EXPIRED'=>'Expired','SUSPENDED'=>'Suspended','NOT_ACTIVATED'=>'Not Activated'][$lic_status] ?? $lic_status;
?>
<div class="mp-section">
  <div class="mp-card mp-license-card">
    <div class="mp-card-head">
      <h3><i class="fa fa-shield" style="color:var(--mp-primary);"></i> License &amp; Usage</h3>
      <a href="<?= base_url('subscription_license/usage'); ?>" class="mp-card-link">Details</a>
    </div>
    <div class="mp-card-body" style="padding:20px;">
      <div class="mp-license-top">
        <div class="mp-license-plan"><span class="mp-license-plan-label">Plan</span><span class="mp-license-plan-name"><?= htmlspecialchars($lic_summary['plan_name'] ?: '—'); ?></span></div>
        <div class="mp-license-status">
          <span class="mp-license-badge" style="background:<?= htmlspecialchars($lic_status_color); ?>22;color:<?= htmlspecialchars($lic_status_color); ?>;"><i class="fa fa-<?= ($lic_status === 'ACTIVE') ? 'check-circle' : 'exclamation-circle'; ?>"></i> <?= htmlspecialchars($lic_status_label); ?></span>
          <?php if($lic_status === 'ACTIVE' && $lic_summary['days_left'] <= 30 && $lic_summary['days_left'] > 0): ?><span class="mp-license-days"><?= $lic_summary['days_left']; ?> days left</span>
          <?php elseif($lic_summary['has_license'] && $lic_summary['end_date']): ?><span class="mp-license-days">Until <?= show_date($lic_summary['end_date']); ?></span><?php endif; ?>
        </div>
      </div>
      <div class="mp-license-quotas">
        <?php foreach($lic_summary['quotas'] as $q): $q_color = ($q['pct'] >= 100) ? 'var(--mp-danger)' : (($q['pct'] >= 80) ? 'var(--mp-warning)' : 'var(--mp-primary)'); $q_used_fmt = $q['unit'] === 'MB' ? number_format($q['used'],1).' MB' : number_format($q['used']); $q_limit_fmt = $q['unit'] === 'MB' ? number_format($q['limit']).' MB' : number_format($q['limit']); ?>
        <div class="mp-license-quota">
          <div class="mp-license-quota-head"><span class="mp-license-quota-label"><?= htmlspecialchars($q['label']); ?></span><span class="mp-license-quota-val"><?= $q_used_fmt; ?> <span class="mp-of">of</span> <?= $q_limit_fmt; ?></span></div>
          <div class="mp-license-bar"><div class="mp-license-bar-fill" style="width:<?= min($q['pct'],100); ?>%;background:<?= htmlspecialchars($q_color); ?>;"></div></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<style>
.mp-license-card .mp-license-top{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--mp-border)}
.mp-license-plan{display:flex;flex-direction:column;gap:2px}.mp-license-plan-label{font-size:12px;color:var(--mp-muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em}.mp-license-plan-name{font-size:20px;font-weight:700;color:var(--mp-ink)}
.mp-license-status{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.mp-license-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:999px;font-size:13px;font-weight:600}.mp-license-days{font-size:12px;color:var(--mp-muted)}
.mp-license-quotas{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}.mp-license-quota-head{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:6px}.mp-license-quota-label{font-size:13px;font-weight:600;color:var(--mp-ink)}.mp-license-quota-val{font-size:12px;color:var(--mp-muted)}.mp-license-quota-val .mp-of{opacity:.6}.mp-license-bar{height:6px;background:var(--mp-bg);border-radius:3px;overflow:hidden}.mp-license-bar-fill{height:100%;border-radius:3px;transition:width .4s ease}
</style>
<?php endif; ?>

<!-- SECTION 3: COLLECTIONS TREND + TARGET (2fr 1fr) -->
<div class="mp-section">
  <div class="mp-row r-2-1">
    <div class="mp-card">
      <div class="mp-card-head"><h3>Collections — Last 7 Days</h3><a href="<?= base_url('printing/reports'); ?>" class="mp-card-link">View Report</a></div>
      <div class="mp-card-body" style="padding:20px;">
        <?php
        $max = 1; foreach ($trend as $t) { if ($t['amount'] > $max) $max = $t['amount']; }
        ?>
        <div style="display:flex;align-items:flex-end;gap:10px;height:180px;">
          <?php foreach ($trend as $t): $h = round(($t['amount'] / $max) * 100); ?>
          <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;gap:6px;">
            <div style="font-size:10px;color:var(--mp-muted);"><?= $t['amount'] > 0 ? $CI->currency($t['amount']) : ''; ?></div>
            <div style="width:100%;height:<?= max($h,2); ?>%;background:linear-gradient(180deg,var(--mp-primary),var(--mp-primary-dark));border-radius:6px 6px 0 0;min-height:4px;"></div>
            <div style="font-size:11px;color:var(--mp-muted);"><?= $t['label']; ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="mp-card">
      <div class="mp-card-head"><h3>Collections vs Target</h3></div>
      <div class="mp-card-body" style="padding:20px;">
        <?php if ($range_target > 0): ?>
        <div style="font-size:26px;font-weight:700;color:var(--mp-ink);"><?= $CI->currency($daily['collected']); ?></div>
        <div style="font-size:12px;color:var(--mp-muted);margin-bottom:12px;">of <?= $CI->currency($range_target); ?> target · <?= $range_label; ?></div>
        <div style="height:8px;background:var(--mp-bg);border-radius:4px;overflow:hidden;margin-bottom:8px;"><div style="width:<?= (float)$range_progress; ?>%;height:100%;background:linear-gradient(90deg,var(--mp-primary),var(--mp-success));"></div></div>
        <div style="font-size:13px;color:var(--mp-muted);"><?= (float)$range_progress; ?>% achieved</div>
        <?php else: ?>
        <div class="mp-empty-state">Set a Daily Sales Target in <a href="<?= base_url('site') ?>">Site Settings</a></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- SECTION 4: RECENT JOBS + RECENT ACTIVITY -->
<div class="mp-section">
  <div class="mp-row r-2-1">
    <div class="mp-card">
      <div class="mp-card-head"><h3>Recent Jobs <span style="font-size:12px;color:var(--mp-muted);font-weight:400;">· <?= $range_label; ?></span></h3><a href="<?= base_url('printing/jobs') ?>" class="mp-card-link">View All</a></div>
      <table class="mp-tbl">
        <thead><tr><th>Job</th><th>Client</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody>
          <?php if (!empty($recent_jobs)): foreach ($recent_jobs as $j): ?>
          <tr>
            <td><strong><?= htmlspecialchars($j->job_code); ?></strong></td>
            <td><?= htmlspecialchars($j->customer_name ?: 'Walk-in'); ?></td>
            <td class="amt"><?= $CI->currency($j->quote_amount, true); ?></td>
            <td><span class="mp-pill <?= $j->production_status === 'completed' ? 'paid' : ($j->production_status === 'in_progress' ? 'partial' : 'unpaid'); ?>"><?= htmlspecialchars(ucwords(str_replace('_',' ',$j->production_status))); ?></span></td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="4" style="text-align:center;color:var(--mp-muted);padding:24px;">No jobs in <?= $range_label; ?></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="mp-card">
      <div class="mp-card-head"><h3>Recent Activity</h3></div>
      <div class="mp-card-body">
        <?php if (!empty($recent_activity)): foreach (array_slice($recent_activity, 0, 5) as $act): ?>
        <div class="mp-activity-item">
          <div class="mp-activity-dot <?= $act['type']; ?>"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
          <div class="mp-activity-body"><div class="mp-activity-title"><?= htmlspecialchars($act['title']); ?></div><div class="mp-activity-meta"><?= show_date($act['date']); ?></div></div>
        </div>
        <?php endforeach; else: ?><div class="mp-empty-state">No recent activity</div><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- SECTION 5: TOP CATEGORIES + LOW STOCK -->
<div class="mp-section">
  <div class="mp-row r-equal">
    <div class="mp-card">
      <div class="mp-card-head"><h3>Top Printing Categories</h3><a href="<?= base_url('printing/reports'); ?>" class="mp-card-link"><?= $range_label; ?></a></div>
      <div class="mp-card-body">
        <?php if (!empty($top_categories)): $i = 1; foreach ($top_categories as $c): ?>
        <div class="mp-top-prod">
          <div class="mp-top-prod-rank <?= $i <= 3 ? 'r'.$i : '' ?>"><?= $i; ?></div>
          <div class="mp-top-prod-info"><div class="mp-top-prod-name"><?= htmlspecialchars($c->name); ?></div><div class="mp-top-prod-meta"><?= number_format((float)$c->qty); ?> qty</div></div>
          <div class="mp-top-prod-amt"><?= $CI->currency($c->revenue); ?></div>
        </div>
        <?php $i++; endforeach; else: ?><div class="mp-empty-state">Not Enough Data Yet</div><?php endif; ?>
      </div>
    </div>
    <div class="mp-card">
      <div class="mp-card-head"><h3>Low Stock — Materials</h3><a href="<?= base_url('items'); ?>" class="mp-card-link">Restock</a></div>
      <table class="mp-tbl">
        <thead><tr><th>Material</th><th>Stock</th><th>Status</th></tr></thead>
        <tbody>
          <?php if (!empty($low_stock)): foreach ($low_stock as $it): ?>
          <tr>
            <td><?= htmlspecialchars($it->name); ?></td>
            <td class="amt"><?= number_format((float)$it->stock, 0) . ' ' . htmlspecialchars($it->unit_name ?: ''); ?></td>
            <td><span class="mp-pill <?= $it->stock <= 0 ? 'out' : 'low'; ?>"><?= $it->stock <= 0 ? 'Out' : 'Low'; ?></span></td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="3" style="text-align:center;color:var(--mp-muted);padding:24px;">All material levels are healthy</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- SECTION 6: TOP DEBTORS -->
<div class="mp-section">
  <div class="mp-row r-equal">
    <div class="mp-card">
      <div class="mp-card-head"><h3>Top Debtors</h3><a href="<?= base_url('customers'); ?>" class="mp-card-link">View All</a></div>
      <table class="mp-tbl">
        <thead><tr><th>Client</th><th>Owing</th></tr></thead>
        <tbody>
          <?php if (!empty($top_debtors)): foreach ($top_debtors as $d): ?>
          <tr><td><?= htmlspecialchars($d['name']); ?></td><td class="amt" style="color:var(--mp-danger);"><?= $CI->currency($d['amount']); ?></td></tr>
          <?php endforeach; else: ?>
          <tr><td colspan="2" style="text-align:center;color:var(--mp-muted);padding:24px;">No outstanding payments</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="mp-card">
      <div class="mp-card-head"><h3>Workflow Pipeline</h3></div>
      <div class="mp-card-body">
        <?php
        $pipeline = [
          ['Awaiting Quote', $kpis['awaiting_quote'], 'fa-file-text-o'],
          ['Awaiting Deposit', $kpis['awaiting_deposit'], 'fa-money'],
          ['Awaiting Artwork', $kpis['awaiting_artwork'], 'fa-picture-o'],
          ['Awaiting Approval', $kpis['awaiting_authorization'], 'fa-check-circle-o'],
          ['In Production', $kpis['in_production'], 'fa-industry'],
          ['Awaiting Collection', $kpis['awaiting_fulfilment'], 'fa-truck'],
        ];
        foreach ($pipeline as $p): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid var(--mp-border);">
          <span style="font-size:13.5px;color:var(--mp-ink);"><i class="fa <?= $p[2]; ?>" style="color:var(--mp-primary);width:20px;"></i> <?= $p[0]; ?></span>
          <strong style="font-size:15px;"><?= (int)$p[1]; ?></strong>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
