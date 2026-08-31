<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Customer Statement</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0;}
    .customer-title { font-size: 16px; font-weight: 700; margin-bottom: 2px; }
    .customer-meta { font-size: 13px; color: var(--mp-muted); margin-bottom: 14px; }
    .summary-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 14px; }
    .stat-card { background: #fff; border-radius: 14px; padding: 14px; border: 1px solid var(--mp-border); }
    .stat-card .label { font-size: 11px; color: var(--mp-muted); margin-bottom: 4px; }
    .stat-card .value { font-size: 18px; font-weight: 700; }
    .stat-card .value.due { color: var(--mp-danger); }
    .table-wrap { background: #fff; border-radius: 14px; border: 1px solid var(--mp-border); overflow: hidden; margin-bottom: 14px; }
    .statement-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .statement-table th { background: var(--mp-bg); padding: 10px 8px; text-align: left; font-weight: 700; color: var(--mp-muted); border-bottom: 1px solid var(--mp-border); }
    .statement-table td { padding: 10px 8px; border-bottom: 1px solid var(--mp-border); vertical-align: top; }
    .statement-table tr:last-child td { border-bottom: none; }
    .statement-table .right { text-align: right; }
    .statement-table .debit { color: var(--mp-danger); }
    .statement-table .credit { color: var(--mp-success); }
    .statement-table .type { font-size: 10px; text-transform: uppercase; color: var(--mp-muted); }
    .empty-state { text-align: center; padding: 24px; color: var(--mp-muted); }
    .btn-block { display: block; width: 100%; padding: 12px; border: none; border-radius: 12px; background: var(--mp-primary); color: #fff; font-size: 14px; font-weight: 700; text-align: center; text-decoration: none; }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    @media print { .topbar, .bottom-nav, .btn-block { display: none; } .screen { padding-bottom: 20px; } }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .bottom-nav { max-width: 100%; left: 0; right: 0; transform: none; } .screen { padding: 16px 16px 100px; } }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/customer_profile/' . $customer->id . '?tab=statements'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Statement</h1>
        </div>
      </div>

      <div class="customer-title"><?= $customer->customer_name; ?></div>
      <div class="customer-meta"><?= $customer->customer_code; ?> · <?= $customer->mobile; ?></div>

      <div class="summary-grid">
        <div class="stat-card">
          <div class="label">Opening Balance</div>
          <div class="value due"><?= mp_format_money($opening); ?></div>
        </div>
        <div class="stat-card">
          <div class="label">Total Sales</div>
          <div class="value due"><?= mp_format_money($summary['total_sales']); ?></div>
        </div>
        <div class="stat-card">
          <div class="label">Total Payments</div>
          <div class="value" style="color:var(--mp-success);"><?= mp_format_money($summary['total_payments']); ?></div>
        </div>
        <div class="stat-card">
          <div class="label">Balance Due</div>
          <div class="value due"><?= mp_format_money($summary['closing_balance']); ?></div>
        </div>
      </div>

      <div class="table-wrap">
        <table class="statement-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Description</th>
              <th class="right">Debit</th>
              <th class="right">Credit</th>
              <th class="right">Balance</th>
            </tr>
          </thead>
          <tbody>
            <?php if(!empty($rows)): ?>
              <?php foreach($rows as $row): ?>
                <tr>
                  <td>
                    <?= !empty($row['date']) ? show_date($row['date']) : '-'; ?>
                    <div class="type"><?= $row['type']; ?></div>
                  </td>
                  <td>
                    <?= $row['description']; ?>
                    <?php if(!empty($row['reference']) && $row['reference'] != '-'): ?>
                      <div class="type"><?= $row['reference']; ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="right debit"><?= $row['debit'] > 0 ? mp_format_money($row['debit']) : '-'; ?></td>
                  <td class="right credit"><?= $row['credit'] > 0 ? mp_format_money($row['credit']) : '-'; ?></td>
                  <td class="right" style="font-weight:700;"><?= mp_format_money($row['balance']); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="5" class="empty-state">No records found</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <button type="button" class="btn-block" onclick="window.print();"><i class="fa fa-print"></i> Print Statement</button>
    </section>


  </div>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
