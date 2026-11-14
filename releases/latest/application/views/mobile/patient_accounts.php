<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
<?php $this->load->view('comman/app_icons'); ?>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — Patient Accounts</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root{--mp-primary:#0057FF;--mp-bg:#F1F5F9;--mp-surface:#fff;--mp-text:#0F172A;--mp-muted:#64748B;--mp-border:#E2E8F0;--mp-success:#059669;--mp-warning:#D97706;--safe-bottom:env(safe-area-inset-bottom,0px)}
    *{box-sizing:border-box}html,body{margin:0;padding:0;font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;background:var(--mp-bg);color:var(--mp-text);height:100%;overscroll-behavior:none}
    #app{max-width:430px;margin:0 auto;background:var(--mp-surface);min-height:100vh;position:relative}.screen{padding:12px 12px 108px}
    .topbar{display:flex;align-items:center;gap:12px;margin-bottom:14px;padding-top:8px}.back{color:var(--mp-primary);font-size:20px;text-decoration:none;width:36px;height:36px;display:grid;place-items:center;border-radius:12px;background:var(--mp-bg)}
    .topbar-titles{flex:1;min-width:0}.store-name{font-size:11px;color:var(--mp-muted);font-weight:700;text-transform:uppercase;letter-spacing:.4px;margin-bottom:2px}.topbar h1{font-size:22px;font-weight:800;margin:0}
    .searchbar{display:flex;gap:8px;margin-bottom:12px}.searchbar input{flex:1;padding:11px 14px;border:1px solid var(--mp-border);border-radius:12px;font-size:14px}.searchbar button{width:42px;border:0;border-radius:12px;background:var(--mp-primary);color:#fff;font-size:15px}
    .summary{background:#EEF6FF;border:1px solid #BFDBFE;border-radius:14px;padding:12px 14px;margin-bottom:12px;color:#1E3A8A;font-size:12px;line-height:1.45}
    .acct-card{display:block;text-decoration:none;color:inherit;background:#fff;border:1px solid var(--mp-border);border-radius:16px;padding:14px;margin-bottom:10px;box-shadow:0 1px 3px rgba(15,23,42,.04)}
    .acct-head{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}.acct-name{font-size:15px;font-weight:800;color:#1E293B}.acct-code{font-size:11px;color:var(--mp-muted);font-family:monospace;margin-top:2px}.acct-phone{font-size:12px;color:var(--mp-muted);margin-top:4px}
    .acct-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px}.metric{background:var(--mp-bg);border-radius:12px;padding:10px}.metric .label{display:block;font-size:10px;color:var(--mp-muted);font-weight:700;text-transform:uppercase;letter-spacing:.04em}.metric .value{display:block;margin-top:4px;font-size:13px;font-weight:800;color:#0F172A}.metric.ok .value{color:var(--mp-success)}.metric.warn .value{color:var(--mp-warning)}
    .empty{text-align:center;padding:56px 24px;color:var(--mp-muted);font-size:14px}.actions{display:flex;gap:8px;margin-top:12px}.action{flex:1;text-align:center;border-radius:10px;padding:9px 8px;text-decoration:none;font-size:12px;font-weight:700;background:#E0E7FF;color:var(--mp-primary)}
    @media(min-width:600px){#app{max-width:100%;margin:0}.screen{padding:16px 16px 120px}.acct-grid{grid-template-columns:repeat(4,1fr)}}
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Patient Accounts</h1>
        </div>
      </div>

      <form method="get" action="<?= base_url('mobile/patient_accounts'); ?>" class="searchbar">
        <input type="text" name="search" placeholder="Search patient, code, phone..." value="<?= htmlspecialchars($search ?? ''); ?>">
        <button type="submit"><i class="fa fa-search"></i></button>
      </form>

      <div class="summary">Wallet balance is spendable patient money. Package held is value left inside paid treatment sessions, so it can show even when the wallet ledger has no rows.</div>

      <?php if(!empty($accounts)): ?>
        <?php foreach($accounts as $row): $p = $row['patient']; $b = $row['balances']; ?>
          <div class="acct-card">
            <div class="acct-head">
              <div>
                <div class="acct-name"><?= htmlspecialchars($p->customer_name ?: 'Unnamed patient'); ?></div>
                <div class="acct-code"><?= htmlspecialchars($p->patient_code ?: 'No code'); ?></div>
                <?php if(!empty($p->mobile)): ?><div class="acct-phone"><i class="fa fa-phone"></i> <?= htmlspecialchars($p->mobile); ?></div><?php endif; ?>
              </div>
            </div>
            <div class="acct-grid">
              <div class="metric ok"><span class="label">Wallet</span><span class="value"><?= htmlspecialchars(mp_format_money($b['available'] ?? 0)); ?></span></div>
              <div class="metric warn"><span class="label">Reserved</span><span class="value"><?= htmlspecialchars(mp_format_money($b['reserved'] ?? 0)); ?></span></div>
              <div class="metric"><span class="label">Pending</span><span class="value"><?= htmlspecialchars(mp_format_money($b['pending'] ?? 0)); ?></span></div>
              <div class="metric"><span class="label">Package held</span><span class="value"><?= htmlspecialchars(mp_format_money($row['package_held'] ?? 0)); ?></span></div>
            </div>
            <div class="actions"><a class="action" href="<?= base_url('mobile/patient_profile/'.(int)$p->id); ?>">Open profile</a></div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty">No patient account records found.</div>
      <?php endif; ?>
    </section>
    <?php $this->load->view('mobile/bottom_nav', ['active' => 'accounts']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
