<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Supplier Profile</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-purple: #7C3AED; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0;}
    .profile-card { background: #fff; border-radius: 16px; padding: 16px; border: 1px solid var(--mp-border); margin-bottom: 12px; }
    .profile-header { display: flex; align-items: center; gap: 14px; margin-bottom: 14px; }
    .avatar { width: 56px; height: 56px; border-radius: 50%; background: var(--mp-warning); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 700; }
    .profile-title { flex: 1; }
    .profile-title .name { font-size: 18px; font-weight: 700; }
    .profile-title .code { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .details-list { display: grid; grid-template-columns: 1fr; gap: 10px; }
    .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--mp-border); }
    .detail-row:last-child { border-bottom: none; }
    .detail-row .label { font-size: 13px; color: var(--mp-muted); }
    .detail-row .value { font-size: 14px; font-weight: 600; text-align: right; }
    .detail-row .value a { color: var(--mp-primary); text-decoration: none; }
    .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 12px; }
    .stat-card { background: #fff; border-radius: 14px; padding: 14px; border: 1px solid var(--mp-border); }
    .stat-card .label { font-size: 11px; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 4px; }
    .stat-card .value { font-size: 18px; font-weight: 700; }
    .stat-card .value.due { color: var(--mp-danger); }
    .section-title { font-size: 15px; font-weight: 700; margin: 16px 0 10px; }
    .card { background: #fff; border-radius: 14px; padding: 12px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .list-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--mp-border); text-decoration: none; color: inherit; }
    .list-item:last-child { border-bottom: none; }
    .list-item .left { flex: 1; min-width: 0; }
    .list-item .name { font-weight: 600; font-size: 15px; }
    .list-item .desc { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .list-item .right { text-align: right; }
    .list-item .amount { font-weight: 700; font-size: 15px; }
    .list-item .status { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 12px; }
    .status.Paid { background: #D1FAE5; color: #065F46; }
    .status.Partial { background: #FEF3C7; color: #92400E; }
    .status.Unpaid { background: #FEE2E2; color: #991B1B; }
    .badge { display: inline-block; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; }
    .badge.active { background: #D1FAE5; color: #065F46; }
    .badge.inactive { background: #FEE2E2; color: #991B1B; }
    .empty-state { text-align: center; padding: 24px; color: var(--mp-muted); font-size: 13px; }
    .btn-block { display: block; width: 100%; padding: 12px; border: none; border-radius: 12px; background: var(--mp-primary); color: #fff; font-size: 14px; font-weight: 700; text-align: center; text-decoration: none; margin-top: 12px; }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .bottom-nav { max-width: 100%; left: 0; right: 0; transform: none; } .screen { padding: 16px 16px 100px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 120px; } }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/suppliers'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Supplier Profile</h1>
        </div>
      </div>

      <div class="profile-card">
        <div class="profile-header">
          <div class="avatar"><?= strtoupper(substr($supplier->supplier_name, 0, 1)); ?></div>
          <div class="profile-title">
            <div class="name"><?= $supplier->supplier_name; ?></div>
            <div class="code"><?= $supplier->supplier_code; ?></div>
          </div>
          <span class="badge <?= $supplier->status == 1 ? 'active' : 'inactive'; ?>"><?= $supplier->status == 1 ? 'Active' : 'Inactive'; ?></span>
        </div>

        <div class="details-list">
          <div class="detail-row">
            <span class="label">Phone</span>
            <span class="value"><?= !empty($supplier->mobile) ? '<a href="tel:' . $supplier->mobile . '">' . $supplier->mobile . '</a>' : '-'; ?></span>
          </div>
          <div class="detail-row">
            <span class="label">Email</span>
            <span class="value"><?= !empty($supplier->email) ? '<a href="mailto:' . $supplier->email . '">' . $supplier->email . '</a>' : '-'; ?></span>
          </div>
          <div class="detail-row">
            <span class="label">Opening Balance</span>
            <span class="value" title="<?= strip_tags(mp_format_money(($supplier->opening_balance ?? 0) - get_paid_sob($supplier->id))); ?>"><?= mp_format_money_compact(($supplier->opening_balance ?? 0) - get_paid_sob($supplier->id)); ?></span>
          </div>
          <div class="detail-row">
            <span class="label">Purchase Due</span>
            <span class="value" style="color:var(--mp-danger);" title="<?= strip_tags(mp_format_money($supplier->purchase_due ?? 0)); ?>"><?= mp_format_money_compact($supplier->purchase_due ?? 0); ?></span>
          </div>
          <div class="detail-row">
            <span class="label">Return Due</span>
            <span class="value" title="<?= strip_tags(mp_format_money($supplier->purchase_return_due ?? 0)); ?>"><?= mp_format_money_compact($supplier->purchase_return_due ?? 0); ?></span>
          </div>
          <div class="detail-row">
            <span class="label">Total Due</span>
            <span class="value due" style="color:var(--mp-danger);" title="<?= strip_tags(mp_format_money($total_due)); ?>"><?= mp_format_money_compact($total_due); ?></span>
          </div>
        </div>

        <?php if(permissions('suppliers_edit')): ?>
          <a href="<?= base_url('mobile/edit_supplier/' . $supplier->id); ?>" class="btn-block"><i class="fa fa-edit"></i> Edit Supplier</a>
        <?php endif; ?>
        <?php if(permissions('suppliers_delete')): ?>
          <a href="<?= base_url('mobile/delete_supplier/' . $supplier->id); ?>" class="btn-block" style="background:var(--mp-danger);" onclick="return mpConfirmAction(this, 'Remove this supplier? This will deactivate the record.', event, {danger: true});"><i class="fa fa-trash"></i> Remove Supplier</a>
        <?php endif; ?>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="label">Total Due</div>
          <div class="value due" title="<?= strip_tags(mp_format_money($total_due)); ?>"><?= mp_format_money_compact($total_due); ?></div>
        </div>
        <div class="stat-card">
          <div class="label">Opening Balance</div>
          <div class="value" title="<?= strip_tags(mp_format_money(($supplier->opening_balance ?? 0) - get_paid_sob($supplier->id))); ?>"><?= mp_format_money_compact(($supplier->opening_balance ?? 0) - get_paid_sob($supplier->id)); ?></div>
        </div>
        <div class="stat-card">
          <div class="label">Purchase Due</div>
          <div class="value due" title="<?= strip_tags(mp_format_money($supplier->purchase_due ?? 0)); ?>"><?= mp_format_money_compact($supplier->purchase_due ?? 0); ?></div>
        </div>
        <div class="stat-card">
          <div class="label">Return Due</div>
          <div class="value" title="<?= strip_tags(mp_format_money($supplier->purchase_return_due ?? 0)); ?>"><?= mp_format_money_compact($supplier->purchase_return_due ?? 0); ?></div>
        </div>
      </div>

      <div class="section-title"><i class="fa fa-shopping-cart"></i> Purchase History</div>
      <div class="card">
        <?php if(!empty($purchases)): ?>
          <?php foreach($purchases as $p): ?>
            <a href="<?= base_url('purchase/invoice/' . $p->id); ?>" target="_blank" class="list-item">
              <div class="left">
                <div class="name"><?= $p->purchase_code; ?></div>
                <div class="desc"><?= !empty($p->purchase_date) ? show_date($p->purchase_date) : 'N/A'; ?></div>
              </div>
              <div class="right">
                <div class="amount"><?= mp_format_money($p->grand_total); ?></div>
                <div class="desc"><?= mp_format_money($p->grand_total - $p->paid_amount); ?> due</div>
                <span class="status <?= $p->payment_status; ?>"><?= $p->payment_status; ?></span>
              </div>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">No purchase records found</div>
        <?php endif; ?>
      </div>

      <div class="section-title"><i class="fa fa-money"></i> Payments</div>
      <div class="card">
        <?php if(!empty($payments)): ?>
          <?php foreach($payments as $p): ?>
            <div class="list-item">
              <div class="left">
                <div class="name"><?= ucfirst(str_replace('_', ' ', $p->payment_type)); ?></div>
                <div class="desc"><?= !empty($p->payment_date) ? show_date($p->payment_date) : 'N/A'; ?></div>
              </div>
              <div class="right">
                <div class="amount"><?= mp_format_money($p->amount); ?></div>
                <div class="desc"><?= $p->description ?? '-'; ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">No payment records</div>
        <?php endif; ?>
      </div>
    </section>


  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
