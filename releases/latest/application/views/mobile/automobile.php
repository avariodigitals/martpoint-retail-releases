<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Vehicles</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root {
      --mp-primary: #0057FF;
      --mp-primary-dark: #0044CC;
      --mp-bg: #F1F5F9;
      --mp-surface: #FFFFFF;
      --mp-text: #0F172A;
      --mp-muted: #64748B;
      --mp-border: #E2E8F0;
      --mp-success: #10B981;
      --mp-danger: #EF4444;
      --mp-warning: #F59E0B;
      --mp-info: #3B82F6;
      --mp-ink: #1E293B;
      --safe-bottom: env(safe-area-inset-bottom, 0px);
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
    .kpi-card { padding: 16px 10px; border-radius: 16px; text-align: center; }
    .kpi-card.green { background: #ECFDF5; }
    .kpi-card.yellow { background: #FFFBEB; }
    .kpi-card.blue { background: #EFF6FF; }
    .kpi-card .label { font-size: 11px; color: var(--mp-muted); margin-bottom: 6px; text-transform: uppercase; font-weight: 600; }
    .kpi-card .value { font-size: 22px; font-weight: 700; color: var(--mp-ink); }
    .status-chips { display: flex; gap: 8px; margin-bottom: 16px; overflow-x: auto; }
    .chip { flex: 0 0 auto; padding: 10px 16px; border-radius: 20px; border: 1px solid var(--mp-border); background: #fff; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; color: var(--mp-ink); }
    .chip.active { background: var(--mp-primary); color: #fff; border-color: var(--mp-primary); }
    .vehicle-card { display: flex; gap: 12px; background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 12px; margin-bottom: 10px; text-decoration: none; color: var(--mp-ink); }
    .vehicle-card img { width: 70px; height: 56px; object-fit: cover; border-radius: 10px; background: var(--mp-bg); }
    .vehicle-card .details { flex: 1; }
    .vehicle-card .title { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
    .vehicle-card .meta { font-size: 12px; color: var(--mp-muted); }
    .vehicle-card .price { font-size: 15px; font-weight: 700; color: var(--mp-primary); margin-top: 4px; }
    .vehicle-card .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; margin-top: 6px; }
    .badge-success { background: #ECFDF5; color: #065F46; }
    .badge-warning { background: #FFFBEB; color: #92400E; }
    .badge-primary { background: #EFF6FF; color: #1E40AF; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    .fab { position: fixed; bottom: calc(72px + var(--safe-bottom)); right: 16px; width: 56px; height: 56px; border-radius: 50%; background: var(--mp-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; text-decoration: none; box-shadow: 0 6px 16px rgba(0,87,255,0.3); z-index: 50; }
    @media (min-width: 430px) { .fab { right: calc(50% - 199px); } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Vehicles</h1>
        </div>
      </div>

      <div class="kpi-grid">
        <a href="<?= base_url('mobile/automobile?status=available'); ?>" class="kpi-card green">
          <div class="label">Available</div>
          <div class="value"><?= number_format($counts['available'] ?? 0); ?></div>
        </a>
        <a href="<?= base_url('mobile/automobile?status=reserved'); ?>" class="kpi-card yellow">
          <div class="label">Reserved</div>
          <div class="value"><?= number_format($counts['reserved'] ?? 0); ?></div>
        </a>
        <a href="<?= base_url('mobile/automobile?status=sold'); ?>" class="kpi-card blue">
          <div class="label">Sold</div>
          <div class="value"><?= number_format($counts['sold'] ?? 0); ?></div>
        </a>
      </div>

      <div class="status-chips">
        <a href="<?= base_url('mobile/automobile'); ?>" class="chip <?= empty($status) ? 'active' : ''; ?>">All</a>
        <a href="<?= base_url('mobile/automobile?status=available'); ?>" class="chip <?= $status === 'available' ? 'active' : ''; ?>">Available</a>
        <a href="<?= base_url('mobile/automobile?status=reserved'); ?>" class="chip <?= $status === 'reserved' ? 'active' : ''; ?>">Reserved</a>
        <a href="<?= base_url('mobile/automobile?status=sold'); ?>" class="chip <?= $status === 'sold' ? 'active' : ''; ?>">Sold</a>
      </div>

      <?php if (!empty($vehicles)): ?>
        <?php foreach ($vehicles as $v): ?>
        <a href="<?= base_url('mobile/automobile_form/' . $v->id); ?>" class="vehicle-card">
          <?php if (!empty($v->image_path) && file_exists(FCPATH . $v->image_path)): ?>
            <img src="<?= base_url($v->image_path); ?>" alt="">
          <?php else: ?>
            <div style="width:70px; height:56px; border-radius:10px; background:var(--mp-bg); display:flex; align-items:center; justify-content:center; color:var(--mp-muted);"><i class="fa fa-car"></i></div>
          <?php endif; ?>
          <div class="details">
            <div class="title"><?= htmlspecialchars(($v->year ? $v->year . ' ' : '') . $v->make . ' ' . $v->model); ?></div>
            <div class="meta"><?= htmlspecialchars(ucfirst($v->vehicle_condition)); ?> <?= $v->mileage ? '· ' . number_format($v->mileage) . ' km' : ''; ?></div>
            <div class="price"><?= number_format($v->price, 2); ?></div>
            <span class="badge badge-<?= $v->status === 'available' ? 'success' : ($v->status === 'reserved' ? 'warning' : 'primary'); ?>"><?= ucfirst($v->status); ?></span>
          </div>
        </a>
        <div style="display:flex; gap:8px; justify-content:flex-end; margin: -4px 0 12px;">
          <?php if($v->status !== 'sold'): ?>
            <a href="<?= base_url('mobile/sell_vehicle/'.$v->id); ?>" style="padding:8px 14px; border-radius:20px; background:var(--mp-success); color:#fff; font-size:13px; font-weight:600; text-decoration:none;">Sell</a>
          <?php else: ?>
            <a href="<?= base_url('mobile/vehicle_receipt/'.$v->id); ?>" style="padding:8px 14px; border-radius:20px; background:var(--mp-info); color:#fff; font-size:13px; font-weight:600; text-decoration:none;">Receipt</a>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">No vehicles found. Tap + to add one.</div>
      <?php endif; ?>
    </section>

    <a href="<?= base_url('mobile/automobile_form'); ?>" class="fab">+</a>
  </div>

  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
