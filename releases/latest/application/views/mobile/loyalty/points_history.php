<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Points History</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 100%; margin: 0; background: var(--mp-bg); min-height: 100vh; position: relative; }
    .screen { padding: 12px 16px 110px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: #fff; border: 1px solid var(--mp-border); }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .search-bar { display: flex; align-items: center; gap: 10px; background: #fff; border-radius: 14px; padding: 10px 14px; border: 1px solid var(--mp-border); margin-bottom: 12px; }
    .search-bar i { color: var(--mp-muted); font-size: 16px; }
    .search-bar input { border: none; background: transparent; flex: 1; font-size: 16px; outline: none; min-height: 28px; color: var(--mp-text); }
    .search-bar input::placeholder { color: var(--mp-muted); }
    .section { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 18px; margin-bottom: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .section-body { padding: 16px; display: flex; flex-direction: column; gap: 12px; }
    .txn-card { background: #fff; border: 1px solid var(--mp-border); border-radius: 16px; padding: 14px; }
    .txn-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .txn-header .customer { font-weight: 700; font-size: 15px; }
    .txn-header .points { font-weight: 700; font-size: 16px; }
    .txn-header .points.earn { color: var(--mp-success); }
    .txn-header .points.redeem { color: var(--mp-danger); }
    .txn-meta { font-size: 12px; color: var(--mp-muted); margin-top: 6px; }
    .txn-desc { font-size: 13px; color: var(--mp-ink); margin-top: 8px; }
    .txn-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; font-size: 12px; color: var(--mp-muted); }
    .badge { display: inline-block; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; background: #F1F5F9; color: var(--mp-muted); text-transform: uppercase; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    @media (min-width: 430px) { .screen { padding: 16px 20px 120px; } }
    @media (min-width: 600px) { .screen { padding: 16px 24px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= $back_url; ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Points History</h1>
        </div>
      </div>

      <div class="search-bar">
        <i class="fa fa-search"></i>
        <input type="text" id="historySearch" placeholder="Search by customer or description">
      </div>

      <div class="section">
        <div class="section-body" id="historyList">
          <?php if(!empty($history)): foreach($history as $h): ?>
            <div class="txn-card" data-customer="<?= htmlspecialchars(strtolower($h->customer_name ?? '')); ?>" data-desc="<?= htmlspecialchars(strtolower($h->description ?? '')); ?>">
              <div class="txn-header">
                <div class="customer"><?= htmlspecialchars($h->customer_name ?? 'Unknown'); ?></div>
                <div class="points <?= in_array($h->transaction_type, ['earn','bonus','birthday','referral','tier_upgrade','adjust']) ? 'earn' : 'redeem'; ?>">
                  <?= in_array($h->transaction_type, ['redeem','adjust_sub']) ? '-' : '+'; ?><?= number_format($h->points ?? 0, 2); ?>
                </div>
              </div>
              <div class="txn-meta">Balance: <?= number_format($h->points_balance ?? 0, 2); ?></div>
              <?php if(!empty($h->description)): ?>
                <div class="txn-desc"><?= htmlspecialchars($h->description); ?></div>
              <?php endif; ?>
              <div class="txn-footer">
                <span class="badge"><?= htmlspecialchars(str_replace('_', ' ', $h->transaction_type ?? 'Unknown')); ?></span>
                <span><?= show_date($h->created_date ?? ''); ?> <?= !empty($h->created_time) ? $h->created_time : ''; ?></span>
              </div>
            </div>
          <?php endforeach; else: ?>
            <div class="empty-state"><i class="fa fa-history"></i><div>No points history.</div></div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/chat'); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>

  <script>
    const searchInput = document.getElementById('historySearch');
    if(searchInput){
      searchInput.addEventListener('input', function(){
        const term = this.value.toLowerCase().trim();
        document.querySelectorAll('#historyList .txn-card').forEach(function(el){
          const customer = el.dataset.customer || '';
          const desc = el.dataset.desc || '';
          el.style.display = (customer.indexOf(term) !== -1 || desc.indexOf(term) !== -1) ? 'block' : 'none';
        });
      });
    }
  </script>
</body>
</html>
