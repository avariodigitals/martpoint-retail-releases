<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 130px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: var(--mp-bg); }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .topbar .add { color: #fff; font-size: 15px; text-decoration: none; min-width: 36px; height: 36px; padding: 0 12px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: var(--mp-primary); font-weight: 700; }
    .tabs { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 10px; margin-bottom: 10px; -webkit-overflow-scrolling: touch; }
    .tabs a { flex-shrink: 0; padding: 8px 14px; border-radius: 20px; background: var(--mp-bg); border: 1px solid var(--mp-border); font-size: 12px; font-weight: 700; text-decoration: none; color: var(--mp-muted); }
    .tabs a.on { background: var(--mp-primary); border-color: var(--mp-primary); color: #fff; }
    .card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); text-decoration: none; color: var(--mp-ink); display: block; }
    .card .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .nm { font-size: 14px; font-weight: 700; }
    .sub { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-default { background: #E2E8F0; color: #475569; }
    .badge-info { background: #DBEAFE; color: #1D4ED8; }
    .badge-warning { background: #FEF3C7; color: #B45309; }
    .badge-primary { background: #E0E7FF; color: #4338CA; }
    .badge-success { background: #D1FAE5; color: #047857; }
    .badge-danger { background: #FEE2E2; color: #B91C1C; }
    .meta { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 8px; font-size: 11px; color: var(--mp-muted); }
    .meta .art-ok { color: var(--mp-success); font-weight: 600; }
    .meta .art-pend { color: var(--mp-warning); font-weight: 600; }
    .meta .bal { color: var(--mp-danger); font-weight: 700; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); font-size: 14px; }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 130px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 150px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/nylon'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
        <?php if(!empty($can_add)): ?><a href="<?= base_url('mobile/nylon_order_form'); ?>" class="add"><i class="fa fa-plus"></i></a><?php endif; ?>
      </div>

      <div class="tabs">
        <?php foreach($statuses as $s): ?>
        <a href="<?= base_url('mobile/nylon_orders/'.$s); ?>" class="<?= $active_status === $s ? 'on' : ''; ?>"><?= htmlspecialchars(Custom_orders_model::status_label($s)); ?> (<?= (int)($counts[$s] ?? 0); ?>)</a>
        <?php endforeach; ?>
      </div>

      <?php if(!empty($orders)): ?>
        <?php foreach($orders as $o): ?>
        <a class="card" href="<?= base_url('mobile/nylon_order/'.$o->id); ?>">
          <div class="top">
            <div>
              <div class="nm"><?= htmlspecialchars($o->order_code); ?></div>
              <div class="sub"><?= htmlspecialchars($o->customer_name ?: 'Walk-in'); ?> · <?= htmlspecialchars($o->item_name ?: ''); ?></div>
              <div class="sub"><?= rtrim(rtrim(number_format((float)$o->order_qty, 2), '0'), '.'); ?> ordered<?= $o->due_date ? ' · due '.show_date($o->due_date) : ''; ?><?= $o->repeat_of_id ? ' · repeat' : ''; ?></div>
            </div>
            <span class="badge badge-<?= Custom_orders_model::status_badge($o->status); ?>"><?= htmlspecialchars(Custom_orders_model::status_label($o->status)); ?></span>
          </div>
          <div class="meta">
            <?php if(!empty($o->artwork_required)): ?>
              <?php if(!empty($o->artwork_approved)): ?><span class="art-ok"><i class="fa fa-check-circle"></i> Artwork approved</span><?php else: ?><span class="art-pend"><i class="fa fa-clock-o"></i> Artwork pending</span><?php endif; ?>
            <?php endif; ?>
            <?php if((float)$o->balance_due > 0): ?><span class="bal">Balance <?= store_number_format($o->balance_due); ?></span><?php endif; ?>
            <?php if((float)$o->dispatched_qty > 0): ?><span><i class="fa fa-truck"></i> <?= rtrim(rtrim(number_format((float)$o->dispatched_qty, 2), '0'), '.'); ?> dispatched</span><?php endif; ?>
          </div>
        </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state"><i class="fa fa-pencil-square-o"></i><div>No <?= strtolower(Custom_orders_model::status_label($active_status)); ?> orders.</div></div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
