<?php $this->load->view('customers/desktop/_styles'); ?>

<style>
@media print {
  .mp-header, .mp-nav, .mp-page-head, .no-print { display:none !important; }
  .mp-main { padding:0 !important; margin:0 !important; background:#fff !important; }
}
</style>

<div class="mp-page-head no-print">
  <div>
    <h2>Customer Statement</h2>
    <div class="mp-page-sub"><?= htmlspecialchars($customer->customer_name ?? ''); ?> — <?= htmlspecialchars($customer->customer_code ?? ''); ?></div>
  </div>
</div>

<div class="mp-quick-actions no-print">
  <button class="mp-qa-btn blue" onclick="window.print();"><i class="fa fa-print"></i> Print</button>
  <a href="<?= base_url('customers/profile/' . $customer->id); ?>" class="mp-qa-btn teal"><i class="fa fa-user"></i> Back to Profile</a>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card summary">
    <div class="mp-kpi-icon"><i class="fa fa-file-text-o"></i></div>
    <div class="mp-kpi-label">Opening Balance</div>
    <div class="mp-kpi-value"><?= store_number_format($opening); ?></div>
  </div>
  <div class="mp-kpi-card debt">
    <div class="mp-kpi-icon"><i class="fa fa-shopping-cart"></i></div>
    <div class="mp-kpi-label">Total Sales</div>
    <div class="mp-kpi-value"><?= store_number_format($summary['total_sales']); ?></div>
  </div>
  <div class="mp-kpi-card sales">
    <div class="mp-kpi-icon"><i class="fa fa-money"></i></div>
    <div class="mp-kpi-label">Total Payments</div>
    <div class="mp-kpi-value"><?= store_number_format($summary['total_payments']); ?></div>
  </div>
  <div class="mp-kpi-card debt">
    <div class="mp-kpi-icon"><i class="fa fa-balance-scale"></i></div>
    <div class="mp-kpi-label">Balance Due</div>
    <div class="mp-kpi-value"><?= store_number_format($summary['closing_balance']); ?></div>
  </div>
</div>

<div class="mp-card">
  <div class="mp-card-head">
    <h3><?= htmlspecialchars($customer->customer_name ?? ''); ?> — <?= htmlspecialchars($customer->customer_code ?? ''); ?></h3>
    <div class="no-print" style="font-size:13px;color:var(--mp-muted);">
      <?= htmlspecialchars($customer->mobile ?? ''); ?> · <?= htmlspecialchars($customer->email ?? ''); ?>
    </div>
  </div>
  <div class="mp-card-body" style="padding:0;">
    <table class="table mp-static-table">
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
<script>
  $('.mp-nav-item').removeClass('active');
  $('.customers_list-active-li').addClass('active');
  $('.customers_list-active-li').closest('.mp-nav-group').addClass('open');
</script>
