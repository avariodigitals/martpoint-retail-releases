<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($patient_term ?? 'Patient'); ?>s</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar .back:active { background: #E2E8F0; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar .add { padding: 9px 14px; border-radius: 10px; background: var(--mp-primary); color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; white-space: nowrap; border: none; cursor: pointer; }
    .pills { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 10px; margin-bottom: 6px; -webkit-overflow-scrolling: touch; }
    .pills::-webkit-scrollbar { display: none; }
    .pill { flex-shrink: 0; padding: 7px 14px; border-radius: 20px; background: var(--mp-bg); border: 1px solid var(--mp-border); font-size: 12px; font-weight: 600; color: var(--mp-muted); text-decoration: none; }
    .pill.active { background: var(--mp-primary); border-color: var(--mp-primary); color: #fff; }
    .pt-card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); padding: 14px 16px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(15,23,42,0.04); display: block; text-decoration: none; color: inherit; }
    .pt-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
    .pt-name { font-size: 15px; font-weight: 700; color: var(--mp-ink); }
    .pt-code { font-size: 11px; color: var(--mp-muted); font-family: monospace; margin-top: 2px; }
    .pt-contact { font-size: 12px; color: var(--mp-muted); margin-top: 5px; line-height: 1.5; word-break: break-all; }
    .pt-contact a { color: var(--mp-muted); text-decoration: none; }
    .pt-meta { font-size: 11px; color: var(--mp-muted); margin-top: 8px; }
    .badge { font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 20px; white-space: nowrap; text-transform: capitalize; }
    .badge.active { background: #D1FAE5; color: #065F46; }
    .badge.inactive { background: #FEF3C7; color: #B45309; }
    .badge.deceased { background: #E2E8F0; color: #475569; }
    .badge.dup { background: #FEE2E2; color: #B91C1C; }
    .empty { text-align: center; padding: 60px 24px; color: var(--mp-muted); font-size: 14px; }
    .searchbar { display: flex; gap: 8px; margin-bottom: 12px; }
    .searchbar input { flex: 1; padding: 11px 14px; border: 1px solid var(--mp-border); border-radius: 10px; font-size: 14px; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/more'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($patient_term); ?>s</h1>
        </div>
        <?php if($can_add): ?>
          <a class="add" href="<?= base_url('mobile/patient_form'); ?>">+ Register</a>
        <?php endif; ?>
      </div>

      <div class="pills">
        <a class="pill <?= $status_filter === '' ? 'active' : ''; ?>" href="<?= base_url('mobile/patients'); ?>">All (<?= (int)($stats['total'] ?? 0); ?>)</a>
        <a class="pill <?= $status_filter === 'active' ? 'active' : ''; ?>" href="<?= base_url('mobile/patients?status=active'); ?>">Active (<?= (int)($stats['active'] ?? 0); ?>)</a>
        <a class="pill <?= $status_filter === 'inactive' ? 'active' : ''; ?>" href="<?= base_url('mobile/patients?status=inactive'); ?>">Inactive (<?= (int)($stats['inactive'] ?? 0); ?>)</a>
        <a class="pill <?= $status_filter === 'deceased' ? 'active' : ''; ?>" href="<?= base_url('mobile/patients?status=deceased'); ?>">Deceased (<?= (int)($stats['deceased'] ?? 0); ?>)</a>
      </div>

      <form method="get" action="<?= base_url('mobile/patients'); ?>" class="searchbar">
        <?php if($status_filter !== ''): ?><input type="hidden" name="status" value="<?= htmlspecialchars($status_filter); ?>"><?php endif; ?>
        <input type="text" name="search" placeholder="Search name, phone, code…" value="<?= htmlspecialchars($search ?? ''); ?>">
      </form>

      <?php if(!empty($patients)): ?>
        <?php foreach($patients as $p): ?>
          <a class="pt-card" href="<?= base_url('mobile/patient_profile/'.(int)$p->id); ?>">
            <div class="pt-top">
              <div>
                <div class="pt-name"><?= htmlspecialchars($p->customer_name); ?></div>
                <div class="pt-code"><?= htmlspecialchars($p->patient_code ?: '—'); ?></div>
              </div>
              <?php if($p->deceased): ?><span class="badge deceased">Deceased</span>
              <?php elseif($p->duplicate_of_id): ?><span class="badge dup">Duplicate</span>
              <?php else: ?><span class="badge <?= $p->status ? 'active' : 'inactive'; ?>"><?= $p->status ? 'Active' : 'Inactive'; ?></span><?php endif; ?>
            </div>
            <div class="pt-contact">
              <?php if($p->mobile): ?><span><i class="fa fa-phone"></i> <?= htmlspecialchars($p->mobile); ?></span><?php endif; ?>
              <?php if($p->email): ?> · <i class="fa fa-envelope-o"></i> <?= htmlspecialchars($p->email); ?><?php endif; ?>
            </div>
            <div class="pt-meta">
              <?= $p->gender ? ucfirst($p->gender) : ''; ?><?= $p->dob ? ' · DOB ' . date('M j, Y', strtotime($p->dob)) : ''; ?>
              <?= !empty($p->created_date) ? ' · Reg ' . date('M j, Y', strtotime($p->created_date)) : ''; ?>
            </div>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty">No <?= strtolower(htmlspecialchars($patient_term)); ?>s registered yet.<br>Tap + Register to add the first one.</div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
