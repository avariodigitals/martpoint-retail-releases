<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title ?? 'Held Sales'); ?></title>
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
    .summary-card { background: linear-gradient(135deg, var(--mp-primary) 0%, var(--mp-primary-dark) 100%); border-radius: 16px; padding: 20px; color: #fff; margin-bottom: 16px; }
    .summary-card .label { font-size: 13px; opacity: 0.9; margin-bottom: 6px; }
    .summary-card .value { font-size: 28px; font-weight: 700; }
    .alert { padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 14px; }
    .alert-success { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
    .alert-danger { background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; }
    .holds-list { display: flex; flex-direction: column; gap: 12px; }
    .hold-card { background: #fff; border-radius: 14px; padding: 14px; border: 1px solid var(--mp-border); }
    .hold-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .hold-title { flex: 1; min-width: 0; }
    .hold-title .ref { font-weight: 600; font-size: 16px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .hold-title .customer { font-size: 12px; color: var(--mp-muted); margin-top: 3px; }
    .hold-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; font-size: 12px; color: var(--mp-muted); }
    .hold-meta span { display: inline-flex; align-items: center; gap: 4px; }
    .hold-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--mp-border); }
    .hold-stats div { text-align: left; }
    .stat-label { font-size: 11px; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px; }
    .stat-value { font-size: 15px; font-weight: 700; }
    .hold-actions { display: flex; gap: 8px; margin-top: 14px; }
    .action { flex: 1; text-align: center; padding: 9px 0; border-radius: 10px; background: var(--mp-bg); color: var(--mp-text); text-decoration: none; font-size: 12px; font-weight: 600; }
    .action i { margin-right: 4px; }
    .action.open { background: #E0E7FF; color: var(--mp-primary); }
    .action.delete { background: #FEF2F2; color: var(--mp-danger); }
    .empty-state { text-align: center; padding: 60px 20px; color: var(--mp-muted); }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 10px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
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
        <a href="<?= base_url('mobile/pos'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= $page_title ?? 'Held Sales'; ?></h1>
        </div>
      </div>

      <?php if($this->session->flashdata('success')): ?>
        <?php $flash_success = trim(strip_tags(str_ireplace(['</p>','<br>','<br/>','<br />'], [' ',' ',' ',' '], $this->session->flashdata('success')))); ?>
        <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($flash_success, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: 'success'});</script>
      <?php endif; ?>
      <?php if($this->session->flashdata('failed')): ?>
        <?php $flash_failed = trim(strip_tags(str_ireplace(['</p>','<br>','<br/>','<br />'], [' ',' ',' ',' '], $this->session->flashdata('failed')))); ?>
        <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($flash_failed, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: 'danger'});</script>
      <?php endif; ?>

      <div class="summary-card">
        <div class="label">Active Holds</div>
        <div class="value"><?= count($holds); ?></div>
      </div>

      <div class="holds-list">
        <?php if(!empty($holds)): ?>
          <?php foreach($holds as $h): ?>
            <div class="hold-card">
              <div class="hold-header">
                <div class="hold-title">
                  <div class="ref"><?= htmlspecialchars($h->reference_id); ?></div>
                  <div class="customer"><?= !empty($h->customer_name) ? $h->customer_name : 'Walk-in'; ?> · <?= show_date($h->sales_date); ?></div>
                </div>
              </div>

              <div class="hold-meta">
                <span><i class="fa fa-cube"></i> <?= number_format($h->items_count); ?> items</span>
              </div>

              <div class="hold-stats">
                <div>
                  <div class="stat-label">Total</div>
                  <div class="stat-value" title="<?= strip_tags(mp_format_money($h->grand_total)); ?>"><?= mp_format_money_compact($h->grand_total); ?></div>
                </div>
                <div>
                  <div class="stat-label">Discount</div>
                  <div class="stat-value" title="<?= strip_tags(mp_format_money($h->discount_to_all_input)); ?>"><?= mp_format_money_compact($h->discount_to_all_input); ?></div>
                </div>
              </div>

              <div class="hold-actions">
                <a href="<?= base_url('mobile/sale?hold_id='.$h->id); ?>" class="action open"><i class="fa fa-refresh"></i> Recall</a>
                <a href="<?= base_url('mobile/delete_hold/'.$h->id); ?>" class="action delete" onclick="return mpConfirmAction(this, 'Delete this held sale?', event, {danger: true});"><i class="fa fa-trash"></i> Delete</a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">
            <i class="fa fa-hand-paper-o"></i>
            <div>No held sales yet.</div>
          </div>
        <?php endif; ?>
      </div>
    </section>


  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'holds']); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
