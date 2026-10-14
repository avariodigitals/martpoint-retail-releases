<?php $this->load->view('customers/desktop/_styles'); ?>

<style>
.cs-wrap{display:flex;flex-direction:column;gap:14px}
.cs-title-row{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap}
.cs-title-row h2{margin:0;font-size:24px;font-weight:800;letter-spacing:0;color:var(--mp-ink)}
.cs-title-row .cs-sub{margin-top:4px;font-size:13px;color:var(--mp-muted);font-weight:600}
.cs-actions{display:flex;gap:8px;flex-wrap:wrap}
.cs-toolbar{display:flex;align-items:flex-end;justify-content:space-between;gap:14px;flex-wrap:wrap;background:var(--mp-surface,#fff);border:1px solid var(--mp-border);border-radius:12px;padding:12px 14px;margin:0}
.cs-filter-fields{display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap}
.cs-field{margin:0;min-width:154px}
.cs-field label{display:block;margin:0 0 5px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:var(--mp-muted)}
.cs-field .mp-form-control{height:38px;border-radius:9px;min-width:154px}
.cs-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
.cs-stat{background:var(--mp-surface,#fff);border:1px solid var(--mp-border);border-radius:12px;padding:14px;display:grid;grid-template-columns:34px minmax(0,1fr);gap:10px;align-items:center;min-width:0}
.cs-stat-icon{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:#e8f4ef;color:var(--mp-primary,#176753);font-size:15px}
.cs-stat-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:var(--mp-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cs-stat-value{margin-top:3px;font-size:18px;font-weight:900;color:var(--mp-ink);line-height:1.15;word-break:break-word}
.cs-stat.due .cs-stat-icon{background:#fff1f2;color:#dc2626}
.cs-stat.paid .cs-stat-icon{background:#ecfdf5;color:#059669}
.cs-statement-card .mp-card-head{align-items:flex-start}
.cs-customer-line{font-size:13px;color:var(--mp-muted);font-weight:600}
.cs-table-wrap{overflow:auto}
.cs-table{min-width:820px;margin-bottom:0}
.cs-table th{font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:var(--mp-muted);background:#f8fbfa;white-space:nowrap}
.cs-table td{vertical-align:middle}
@media print {
  .mp-header, .mp-nav, .mp-page-head, .no-print { display:none !important; }
  .mp-main { padding:0 !important; margin:0 !important; background:#fff !important; }
  .cs-wrap{gap:10px}
  .cs-summary{grid-template-columns:repeat(4,1fr)}
  .cs-stat{break-inside:avoid}
}
@media(max-width:1200px){.cs-summary{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:720px){.cs-title-row{display:block}.cs-actions{margin-top:10px}.cs-toolbar{align-items:stretch}.cs-filter-fields,.cs-actions{width:100%}.cs-field{flex:1 1 140px}.cs-filter-fields .mp-qa-btn{flex:1 1 110px}.cs-summary{grid-template-columns:1fr}.cs-stat-value{font-size:16px}}
</style>

<div class="cs-wrap">
  <div class="cs-title-row">
    <div>
      <h2>Customer Statement</h2>
      <div class="cs-sub"><?= htmlspecialchars($customer->customer_name ?? ''); ?><?= !empty($customer->customer_code) ? ' · '.htmlspecialchars($customer->customer_code) : ''; ?></div>
    </div>
    <div class="cs-actions no-print">
      <button class="mp-qa-btn blue" onclick="window.print();"><i class="fa fa-print"></i> Print</button>
      <a href="<?= htmlspecialchars($profile_url ?? base_url('customers/profile/' . $customer->id)); ?>" class="mp-qa-btn teal"><i class="fa fa-user"></i> Back to Profile</a>
    </div>
  </div>

  <form method="get" action="<?= base_url('customers/statement/' . $customer->id); ?>" class="cs-toolbar no-print">
    <div class="cs-filter-fields">
      <div class="cs-field">
        <label>From Date</label>
        <input type="date" name="from" class="form-control mp-form-control" value="<?= htmlspecialchars($from ?? ''); ?>">
      </div>
      <div class="cs-field">
        <label>To Date</label>
        <input type="date" name="to" class="form-control mp-form-control" value="<?= htmlspecialchars($to ?? ''); ?>">
      </div>
      <button type="submit" class="mp-qa-btn blue"><i class="fa fa-filter"></i> Apply</button>
      <?php if (!empty($from) || !empty($to)): ?>
        <a href="<?= base_url('customers/statement/' . $customer->id); ?>" class="mp-qa-btn teal"><i class="fa fa-times"></i> Clear</a>
      <?php endif; ?>
    </div>
  </form>

  <div class="cs-summary">
    <div class="cs-stat">
      <div class="cs-stat-icon"><i class="fa fa-file-text-o"></i></div>
      <div>
        <div class="cs-stat-label">Opening Balance</div>
        <div class="cs-stat-value"><?= store_number_format($opening); ?></div>
      </div>
    </div>
    <div class="cs-stat due">
      <div class="cs-stat-icon"><i class="fa fa-shopping-cart"></i></div>
      <div>
        <div class="cs-stat-label">Total Sales</div>
        <div class="cs-stat-value"><?= store_number_format($summary['total_sales']); ?></div>
      </div>
    </div>
    <div class="cs-stat paid">
      <div class="cs-stat-icon"><i class="fa fa-money"></i></div>
      <div>
        <div class="cs-stat-label">Total Payments</div>
        <div class="cs-stat-value"><?= store_number_format($summary['total_payments']); ?></div>
      </div>
    </div>
    <div class="cs-stat due">
      <div class="cs-stat-icon"><i class="fa fa-balance-scale"></i></div>
      <div>
        <div class="cs-stat-label">Balance Due</div>
        <div class="cs-stat-value"><?= store_number_format($summary['closing_balance']); ?></div>
      </div>
    </div>
  </div>

  <div class="mp-card cs-statement-card">
    <div class="mp-card-head">
      <div>
        <h3><?= htmlspecialchars($customer->customer_name ?? ''); ?><?= !empty($customer->customer_code) ? ' — '.htmlspecialchars($customer->customer_code) : ''; ?></h3>
        <div class="cs-customer-line">
          <?= htmlspecialchars($customer->mobile ?? ''); ?><?= (!empty($customer->mobile) && !empty($customer->email)) ? ' · ' : ''; ?><?= htmlspecialchars($customer->email ?? ''); ?>
        </div>
      </div>
      <?php if (!empty($from) || !empty($to)): ?>
        <div class="no-print" style="font-size:12px;color:var(--mp-muted);font-weight:700;">Period: <?= !empty($from) ? show_date($from) : 'Start'; ?> → <?= !empty($to) ? show_date($to) : 'Today'; ?></div>
      <?php endif; ?>
    </div>
    <div class="mp-card-body cs-table-wrap" style="padding:0;">
      <table class="table mp-static-table cs-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Reference</th>
            <th class="text-right">Debit</th>
            <th class="text-right">Credit</th>
            <th class="text-right">Balance</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($rows)): ?>
            <?php foreach ($rows as $row): ?>
            <tr>
              <td><?= !empty($row['date']) ? show_date($row['date']) : '-'; ?></td>
              <td><?= htmlspecialchars($row['description']); ?></td>
              <td><?= htmlspecialchars($row['reference']); ?></td>
              <td class="text-right" style="color:var(--mp-danger);"><?= $row['debit'] > 0 ? store_number_format($row['debit']) : '-'; ?></td>
              <td class="text-right" style="color:var(--mp-success);"><?= $row['credit'] > 0 ? store_number_format($row['credit']) : '-'; ?></td>
              <td class="text-right" style="font-weight:700;"><?= store_number_format($row['balance']); ?></td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center text-muted">No records found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
  $('.mp-nav-item').removeClass('active');
  $('.customers_list-active-li').addClass('active');
  $('.customers_list-active-li').closest('.mp-nav-group').addClass('open');
</script>
