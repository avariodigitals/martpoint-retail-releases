<!DOCTYPE html>
<html>
<head>
<?php include APPPATH . "views/comman/code_css.php"; ?>
<style>
.statement-header { margin-bottom: 20px; }
.statement-header h1 { margin: 0; font-size: 24px; }
.statement-header .sub { color: #666; }
.summary-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
.summary-card { background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 15px; text-align: center; }
.summary-card .label { font-size: 12px; color: #666; text-transform: uppercase; }
.summary-card .value { font-size: 20px; font-weight: 700; margin-top: 5px; }
.summary-card.due .value { color: #dd4b39; }
.summary-card.success .value { color: #00a65a; }
.statement-table { width: 100%; border-collapse: collapse; }
.statement-table th, .statement-table td { padding: 10px; border: 1px solid #ddd; }
.statement-table th { background: #f4f4f4; }
.statement-table .right { text-align: right; }
.statement-table .debit { color: #dd4b39; }
.statement-table .credit { color: #00a65a; }
@media print { .sidebar, .main-header, .no-print { display: none !important; } .content-wrapper { margin-left: 0 !important; } }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php include APPPATH . "views/sidebar.php"; ?>
<div class="content-wrapper">
<section class="content-header no-print">
  <h1>Customer Statement</h1>
  <ol class="breadcrumb">
    <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="<?= base_url('customers'); ?>">Customers</a></li>
    <li><a href="<?= base_url('customers/profile/' . $customer->id); ?>"><?= htmlspecialchars($customer->customer_name); ?></a></li>
    <li class="active">Statement</li>
  </ol>
</section>
<section class="content">
  <div class="box box-info">
    <div class="box-header with-border">
      <h3 class="box-title"><?= htmlspecialchars($customer->customer_name); ?> — <?= $customer->customer_code; ?></h3>
      <div class="box-tools pull-right no-print">
        <button class="btn btn-default btn-sm" onclick="window.print();"><i class="fa fa-print"></i> Print</button>
        <a href="<?= base_url('customers/profile/' . $customer->id); ?>" class="btn btn-primary btn-sm"><i class="fa fa-user"></i> Back to Profile</a>
      </div>
    </div>
    <div class="box-body">
      <div class="statement-header">
        <div class="sub"><?= $customer->mobile; ?> · <?= $customer->email; ?></div>
      </div>
      <div class="summary-cards">
        <div class="summary-card">
          <div class="label">Opening Balance</div>
          <div class="value"><?= store_number_format($opening); ?></div>
        </div>
        <div class="summary-card due">
          <div class="label">Total Sales</div>
          <div class="value"><?= store_number_format($summary['total_sales']); ?></div>
        </div>
        <div class="summary-card success">
          <div class="label">Total Payments</div>
          <div class="value"><?= store_number_format($summary['total_payments']); ?></div>
        </div>
        <div class="summary-card due">
          <div class="label">Balance Due</div>
          <div class="value"><?= store_number_format($summary['closing_balance']); ?></div>
        </div>
      </div>
      <table class="table table-bordered table-striped statement-table">
        <thead class="bg-gray">
          <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Reference</th>
            <th class="right">Debit</th>
            <th class="right">Credit</th>
            <th class="right">Balance</th>
          </tr>
        </thead>
        <tbody>
          <?php if(!empty($rows)): ?>
            <?php foreach($rows as $row): ?>
              <tr>
                <td><?= !empty($row['date']) ? show_date($row['date']) : '-'; ?></td>
                <td><?= $row['description']; ?></td>
                <td><?= $row['reference']; ?></td>
                <td class="right debit"><?= $row['debit'] > 0 ? store_number_format($row['debit']) : '-'; ?></td>
                <td class="right credit"><?= $row['credit'] > 0 ? store_number_format($row['credit']) : '-'; ?></td>
                <td class="right" style="font-weight:700;"><?= store_number_format($row['balance']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center text-muted">No records found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
</div>
<?php include APPPATH . "views/footer.php"; ?>
</div>
</body>
</html>
