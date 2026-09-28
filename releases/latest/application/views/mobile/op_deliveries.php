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
    .status-rail { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 6px; margin-bottom: 14px; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
    .status-rail::-webkit-scrollbar { display: none; }
    .status-chip { flex: 0 0 auto; padding: 8px 12px; border-radius: 20px; border: 1px solid var(--mp-border); background: var(--mp-surface); font-size: 12px; font-weight: 600; color: var(--mp-ink); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .status-chip .cnt { background: var(--mp-bg); border-radius: 10px; padding: 1px 7px; font-size: 11px; }
    .status-chip.active { background: var(--mp-primary); border-color: var(--mp-primary); color: #fff; }
    .status-chip.active .cnt { background: rgba(255,255,255,0.25); color: #fff; }
    .card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); text-decoration: none; color: inherit; display: block; }
    .card:active { transform: scale(0.99); }
    .card .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .code { font-size: 15px; font-weight: 700; }
    .route { font-size: 13px; color: var(--mp-muted); margin-top: 2px; }
    .meta { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 10px; font-size: 12px; color: var(--mp-muted); }
    .progress { height: 6px; background: var(--mp-bg); border-radius: 3px; margin-top: 10px; overflow: hidden; }
    .progress .bar { height: 100%; background: var(--mp-success); }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge.planned { background: #E2E8F0; color: #475569; }
    .badge.ready { background: #DBEAFE; color: #1D4ED8; }
    .badge.out_for_delivery { background: #FEF3C7; color: #B45309; }
    .badge.completed { background: #D1FAE5; color: #047857; }
    .badge.cancelled { background: #FEE2E2; color: #B91C1C; }
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
      </div>

      <div class="status-rail">
        <a href="<?= base_url('mobile/deliveries'); ?>" class="status-chip <?= $active_status === '' ? 'active' : ''; ?>">All</a>
        <?php foreach(['planned' => 'Planned', 'ready' => 'Ready', 'out_for_delivery' => 'Out', 'completed' => 'Delivered', 'cancelled' => 'Cancelled'] as $k => $lbl): ?>
        <a href="<?= base_url('mobile/deliveries/'.$k); ?>" class="status-chip <?= ($active_status === $k) ? 'active' : ''; ?>"><?= $lbl; ?><span class="cnt"><?= (int)($counts[$k] ?? 0); ?></span></a>
        <?php endforeach; ?>
      </div>

      <?php if(!empty($schedules)): ?>
        <?php foreach($schedules as $sc):
          $pct = $sc->items_count > 0 ? round(($sc->delivered_count / $sc->items_count) * 100) : 0;
        ?>
        <a class="card" href="<?= base_url('mobile/delivery_view/'.$sc->id); ?>">
          <div class="top">
            <div>
              <div class="code"><?= htmlspecialchars($sc->schedule_code); ?></div>
              <div class="route"><?= htmlspecialchars($sc->route_name ?: 'Unnamed route'); ?></div>
            </div>
            <span class="badge <?= htmlspecialchars($sc->status); ?>"><?= ucwords(str_replace('_', ' ', $sc->status)); ?></span>
          </div>
          <div class="meta">
            <span><i class="fa fa-calendar-o"></i> <?= show_date($sc->schedule_date); ?></span>
            <span><i class="fa fa-user"></i> <?= htmlspecialchars($sc->driver_name ?: 'Unassigned'); ?></span>
            <?php if(!empty($sc->vehicle)): ?><span><i class="fa fa-truck"></i> <?= htmlspecialchars($sc->vehicle); ?></span><?php endif; ?>
            <span><i class="fa fa-map-marker"></i> <?= (int)$sc->delivered_count; ?>/<?= (int)$sc->items_count; ?> stops</span>
          </div>
          <div class="progress"><div class="bar" style="width:<?= $pct; ?>%"></div></div>
        </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <i class="fa fa-truck"></i>
          <div>No delivery schedules<?= $active_status ? ' with this status' : ' yet'; ?>.</div>
        </div>
      <?php endif; ?>
    </section>

    <a href="<?= base_url('mobile/delivery_form'); ?>" class="fab" title="New Schedule"><i class="fa fa-plus"></i></a>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
