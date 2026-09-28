<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 130px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: var(--mp-bg); }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .topbar .plans-link { color: var(--mp-primary); font-size: 13px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 5px; }
    .stats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px; }
    .stat { border-radius: 14px; padding: 14px; }
    .stat .num { font-size: 24px; font-weight: 700; }
    .stat .lbl { font-size: 12px; margin-top: 2px; }
    .stat.active { background: #D1FAE5; color: #047857; }
    .stat.expiring { background: #FEF3C7; color: #B45309; }
    .card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .card .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .who { font-size: 15px; font-weight: 700; }
    .plan { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge.active { background: #D1FAE5; color: #047857; }
    .badge.expiring { background: #FEF3C7; color: #B45309; }
    .badge.expired { background: #FEE2E2; color: #B91C1C; }
    .badge.cancelled, .badge.default { background: #E2E8F0; color: #475569; }
    .dates { font-size: 12px; color: var(--mp-muted); margin-top: 8px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .foot { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; font-size: 12px; }
    .auto { color: #1D4ED8; }
    .row-actions { display: flex; gap: 8px; }
    .mini-btn { padding: 6px 12px; border-radius: 8px; border: 1px solid var(--mp-border); background: var(--mp-bg); font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none; color: var(--mp-ink); display: inline-flex; align-items: center; gap: 5px; }
    .mini-btn.danger { color: var(--mp-danger); border-color: #FECACA; }
    .mini-btn.success { color: var(--mp-success); border-color: #A7F3D0; }
    .fab { position: fixed; right: 18px; bottom: calc(92px + var(--safe-bottom)); width: 54px; height: 54px; border-radius: 50%; background: var(--mp-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; text-decoration: none; box-shadow: 0 6px 16px rgba(0,87,255,0.35); z-index: 50; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); font-size: 14px; }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 130px; } .fab { right: 32px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 150px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/operations'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
        <a href="<?= base_url('mobile/membership_plans'); ?>" class="plans-link"><i class="fa fa-id-card"></i> Plans</a>
      </div>

      <div class="stats">
        <div class="stat active"><div class="num"><?= (int)$active_count; ?></div><div class="lbl">Active</div></div>
        <div class="stat expiring"><div class="num"><?= (int)$expiring_count; ?></div><div class="lbl">Expiring ≤ 7 days</div></div>
      </div>

      <?php if(!empty($memberships)): ?>
        <?php foreach($memberships as $m):
          $today = strtotime(date('Y-m-d'));
          $end = strtotime($m->end_date);
          $days_left = round(($end - $today) / 86400);
          if($m->status === 'active' && $days_left <= 7 && $days_left >= 0){ $bcls = 'expiring'; $btxt = $days_left.'d left'; }
          elseif($m->status === 'active'){ $bcls = 'active'; $btxt = 'Active'; }
          elseif($m->status === 'expired'){ $bcls = 'expired'; $btxt = 'Expired'; }
          else { $bcls = 'default'; $btxt = ucfirst($m->status); }
        ?>
        <div class="card">
          <div class="top">
            <div>
              <div class="who"><?= htmlspecialchars($m->customer_name ?: 'Unknown'); ?></div>
              <div class="plan"><?= htmlspecialchars($m->plan_name ?: 'Plan'); ?> · <?= $this->currency($m->price); ?> / <?= htmlspecialchars($m->billing_cycle); ?></div>
            </div>
            <span class="badge <?= $bcls; ?>"><?= $btxt; ?></span>
          </div>
          <div class="dates"><i class="fa fa-calendar-o"></i> <?= show_date($m->start_date); ?> <i class="fa fa-arrow-right"></i> <?= show_date($m->end_date); ?></div>
          <div class="foot">
            <span><?php if($m->auto_renew): ?><span class="auto"><i class="fa fa-refresh"></i> Auto-renew</span><?php else: ?><span style="color:var(--mp-muted)">Manual renew</span><?php endif; ?></span>
            <span class="row-actions">
              <?php if($can_add): ?><a class="mini-btn success" href="<?= base_url('mobile/membership_assign/'.$m->customer_id.'?renew='.$m->id); ?>"><i class="fa fa-refresh"></i> Renew</a><?php endif; ?>
              <?php if($can_delete && $m->status === 'active'): ?><button class="mini-btn danger" onclick="cancelMembership(<?= (int)$m->id; ?>)"><i class="fa fa-times"></i></button><?php endif; ?>
            </span>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <i class="fa fa-id-card"></i>
          <div>No customer <?= htmlspecialchars(strtolower($m_label)); ?>s yet.</div>
        </div>
      <?php endif; ?>
    </section>

    <?php if($can_add): ?>
    <a href="<?= base_url('mobile/membership_assign'); ?>" class="fab" title="Assign <?= htmlspecialchars($m_label); ?>"><i class="fa fa-plus"></i></a>
    <?php endif; ?>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var base_url = '<?= base_url(); ?>';
    var csrf_token = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrf_hash = '<?= $this->security->get_csrf_hash(); ?>';

    function cancelMembership(id){
      if(!confirm('Cancel this membership?')){ return; }
      var fd = new FormData();
      fd.append('id', id); fd.append(csrf_token, csrf_hash);
      fetch(base_url + 'mobile/membership_cancel', { method: 'POST', body: fd })
        .then(function(r){ return r.json(); })
        .then(function(d){
          if(d && d.csrf_hash){ csrf_hash = d.csrf_hash; }
          if(d && d.success){ window.location.reload(); }
          else { alert(d && d.message ? d.message : 'Cancel failed.'); }
        });
    }
  </script>
</body>
</html>
