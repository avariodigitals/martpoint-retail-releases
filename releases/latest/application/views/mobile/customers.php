<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Customers</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-purple: #7C3AED; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0;}
    .topbar .add-btn { width: 36px; height: 36px; border-radius: 50%; background: var(--mp-primary); color: #fff; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px; }
    .summary-card { background: linear-gradient(135deg, var(--mp-purple) 0%, #5B21B6 100%); border-radius: 16px; padding: 20px; color: #fff; margin-bottom: 16px; }
    .summary-card .label { font-size: 13px; opacity: 0.9; margin-bottom: 6px; }
    .summary-card .value { font-size: 28px; font-weight: 700; }
    .search-bar { display: flex; align-items: center; gap: 10px; background: #fff; border-radius: 14px; padding: 10px 14px; border: 1px solid var(--mp-border); margin-bottom: 12px; }
    .search-bar i { color: var(--mp-muted); font-size: 16px; }
    .search-bar input { border: none; background: transparent; flex: 1; font-size: 16px; outline: none; min-height: 28px; color: var(--mp-text); }
    .search-bar input::placeholder { color: var(--mp-muted); }
    .customer-list { display: flex; flex-direction: column; gap: 12px; }
    .customer-card { background: #fff; border-radius: 14px; padding: 14px; border: 1px solid var(--mp-border); }
    .customer-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .customer-title { flex: 1; min-width: 0; }
    .customer-title .name { font-weight: 600; font-size: 16px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .customer-title .code { font-size: 12px; color: var(--mp-muted); margin-top: 3px; }
    .badge { display: inline-block; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; flex-shrink: 0; }
    .badge.active { background: #D1FAE5; color: #065F46; }
    .badge.inactive { background: #FEE2E2; color: #991B1B; }
    .customer-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; font-size: 12px; color: var(--mp-muted); }
    .customer-meta span, .customer-meta a { display: inline-flex; align-items: center; gap: 4px; }
    .customer-meta a { color: var(--mp-primary); text-decoration: none; }
    .customer-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--mp-border); }
    .customer-stats div { text-align: left; }
    .stat-label { font-size: 11px; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px; }
    .stat-value { font-size: 15px; font-weight: 700; }
    .stat-value.due { color: var(--mp-danger); }
    .stat-value small { display: block; font-size: 10px; color: var(--mp-muted); font-weight: 500; }
    .customer-actions { display: flex; gap: 8px; margin-top: 14px; }
    .action { flex: 1; text-align: center; padding: 9px 0; border-radius: 10px; background: var(--mp-bg); color: var(--mp-text); text-decoration: none; font-size: 12px; font-weight: 600; }
    .action i { margin-right: 4px; }
    .action.view { background: #E0E7FF; color: var(--mp-primary); }
    .action.pay { background: #FEF2F2; color: var(--mp-danger); }
    .action.call { background: #ECFDF5; color: var(--mp-success); }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .bottom-nav { max-width: 100%; left: 0; right: 0; transform: none; } .screen { padding: 16px 16px 100px; } .customer-stats { grid-template-columns: repeat(4, 1fr); } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 120px; } }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Customers</h1>
        </div>
        <a href="<?= base_url('mobile/add_customer'); ?>" class="add-btn"><i class="fa fa-plus"></i></a>
      </div>

      <div class="summary-card">
        <div class="label">Active Customers</div>
        <div class="value"><?= count($customers); ?></div>
      </div>

      <div class="search-bar">
        <i class="fa fa-search"></i>
        <input type="text" id="customer-search" placeholder="Search by name, mobile or code" autocomplete="off">
      </div>

      <div class="customer-list">
        <?php if(!empty($customers)): ?>
          <?php foreach($customers as $c): ?>
            <?php
              $previous_due = ($c->opening_balance ?? 0) + ($c->sales_due ?? 0) - ($c->sales_return_due ?? 0) - get_paid_cob($c->id);
              $store_name = get_store_name($c->store_id);
            ?>
            <div class="customer-card" data-name="<?= strtolower($c->customer_name); ?>" data-mobile="<?= strtolower($c->mobile ?? ''); ?>" data-code="<?= strtolower($c->customer_code ?? ''); ?>">
              <div class="customer-header">
                <div class="customer-title">
                  <div class="name"><?= $c->customer_name; ?></div>
                  <div class="code"><?= $c->customer_code ?: '-'; ?> · <?= $c->mobile ?: 'No phone'; ?></div>
                </div>
                <span class="badge <?= $c->status == 1 ? 'active' : 'inactive'; ?>"><?= $c->status == 1 ? 'Active' : 'Inactive'; ?></span>
              </div>

              <div class="customer-meta">
                <?php if(!empty($c->email)): ?>
                  <span><i class="fa fa-envelope"></i> <?= $c->email; ?></span>
                <?php endif; ?>
                <?php if(!empty($c->location_link)): ?>
                  <a href="<?= $c->location_link; ?>" target="_blank"><i class="fa fa-map-marker"></i> Location</a>
                <?php endif; ?>
                <?php if(!empty($store_name)): ?>
                  <span><i class="fa fa-building"></i> <?= $store_name; ?></span>
                <?php endif; ?>
              </div>

              <div class="customer-stats">
                <div>
                  <div class="stat-label">Previous Due</div>
                  <div class="stat-value <?= $previous_due > 0 ? 'due' : ''; ?>" title="<?= strip_tags(mp_format_money($previous_due)); ?>"><?= mp_format_money_compact($previous_due); ?></div>
                </div>
                <div>
                  <div class="stat-label">Advance</div>
                  <div class="stat-value" title="<?= strip_tags(mp_format_money($c->tot_advance ?? 0)); ?>"><?= mp_format_money_compact($c->tot_advance ?? 0); ?></div>
                </div>
                <div>
                  <div class="stat-label">Credit Limit</div>
                  <div class="stat-value" title="<?= ($c->credit_limit == -1) ? 'No Limit' : strip_tags(mp_format_money($c->credit_limit ?? 0)); ?>"><?= ($c->credit_limit == -1) ? 'No Limit' : mp_format_money_compact($c->credit_limit ?? 0); ?></div>
                </div>
                <div>
                  <div class="stat-label">Points</div>
                  <div class="stat-value"><?= number_format($c->loyalty_points ?? 0); ?><small><?= $c->loyalty_tier ?: 'Bronze'; ?></small></div>
                </div>
              </div>

              <div class="customer-actions">
                <a href="<?= base_url('mobile/customer_profile/' . $c->id); ?>" class="action view"><i class="fa fa-user"></i> View</a>
                <?php if(permissions('customers_edit')): ?>
                  <a href="<?= base_url('mobile/edit_customer/' . $c->id); ?>" class="action edit"><i class="fa fa-edit"></i> Edit</a>
                <?php endif; ?>
                <?php if($previous_due > 0): ?>
                  <a href="<?= base_url('customers'); ?>" class="action pay"><i class="fa fa-money"></i> Due</a>
                <?php endif; ?>
                <?php if(!empty($c->mobile)): ?>
                  <a href="tel:<?= $c->mobile; ?>" class="action call"><i class="fa fa-phone"></i> Call</a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">
            <i class="fa fa-users"></i>
            <div>No customers found.</div>
          </div>
        <?php endif; ?>
      </div>
    </section>


  </div>

  <script>
    var searchInput = document.getElementById('customer-search');
    if(searchInput){
      searchInput.addEventListener('input', function(){
        var term = this.value.toLowerCase().trim();
        document.querySelectorAll('.customer-card').forEach(function(el){
          var name = el.dataset.name || '';
          var mobile = el.dataset.mobile || '';
          var code = el.dataset.code || '';
          el.style.display = (name.indexOf(term) !== -1 || mobile.indexOf(term) !== -1 || code.indexOf(term) !== -1) ? 'block' : 'none';
        });
      });
    }
  </script>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
