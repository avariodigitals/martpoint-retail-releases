<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Suppliers</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-purple: #7C3AED; --mp-yellow: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0;}
    .topbar .add { background: var(--mp-primary); color: #fff; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px; }
    .search-bar { display: flex; gap: 8px; margin-bottom: 12px; }
    .search-bar input { flex: 1; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; }
    .supplier-card { background: #fff; border-radius: 14px; padding: 16px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .supplier-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
    .supplier-name { font-size: 16px; font-weight: 700; color: var(--mp-text); }
    .supplier-code { font-size: 12px; color: var(--mp-muted); }
    .supplier-meta { font-size: 13px; color: var(--mp-muted); margin: 4px 0; }
    .supplier-meta i { margin-right: 4px; color: var(--mp-primary); }
    .supplier-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin: 12px 0; }
    .supplier-stat { background: var(--mp-bg); border-radius: 10px; padding: 10px; }
    .supplier-stat .label { font-size: 11px; color: var(--mp-muted); margin-bottom: 2px; }
    .supplier-stat .value { font-size: 15px; font-weight: 700; }
    .supplier-stat .value.due { color: var(--mp-danger); }
    .supplier-actions { display: flex; gap: 8px; margin-top: 12px; }
    .action { flex: 1; text-align: center; padding: 10px 0; border-radius: 10px; font-size: 12px; font-weight: 600; text-decoration: none; color: #fff; }
    .action.view { background: #E0E7FF; color: var(--mp-primary); }
    .action.edit { background: #FEF3C7; color: #B45309; }
    .action.pay { background: #D1FAE5; color: #065F46; }
    .action.call { background: var(--mp-primary); color: #fff; }
    .empty-state { text-align: center; padding: 32px; color: var(--mp-muted); }
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
        <a href="<?= base_url('mobile'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Suppliers</h1>
        </div>
        <a href="<?= base_url('mobile/add_supplier'); ?>" class="add"><i class="fa fa-plus"></i></a>
      </div>

      <div class="search-bar">
        <input type="text" id="supplierSearch" placeholder="Search suppliers..." oninput="filterSuppliers()">
      </div>

      <div id="supplierList">
        <?php if(!empty($suppliers)): ?>
          <?php foreach($suppliers as $s):
            $opening = ($s->opening_balance ?? 0) - get_paid_sob($s->id);
            $total_due = $opening + ($s->purchase_due ?? 0) - ($s->purchase_return_due ?? 0);
          ?>
            <div class="supplier-card" data-name="<?= strtolower($s->supplier_name); ?>">
              <div class="supplier-header">
                <div>
                  <div class="supplier-name"><?= $s->supplier_name; ?></div>
                  <div class="supplier-code"><?= $s->supplier_code; ?></div>
                </div>
                <span class="badge" style="background:<?= $s->status==1 ? '#D1FAE5' : '#FEE2E2'; ?>; color:<?= $s->status==1 ? '#065F46' : '#991B1B'; ?>; padding:3px 8px; border-radius:20px; font-size:11px; font-weight:600;"><?= $s->status==1 ? 'Active' : 'Inactive'; ?></span>
              </div>
              <?php if(!empty($s->mobile)): ?>
                <div class="supplier-meta"><i class="fa fa-phone"></i> <?= $s->mobile; ?></div>
              <?php endif; ?>
              <?php if(!empty($s->email)): ?>
                <div class="supplier-meta"><i class="fa fa-envelope"></i> <?= $s->email; ?></div>
              <?php endif; ?>

              <div class="supplier-stats">
                <div class="supplier-stat">
                  <div class="label">Opening Balance</div>
                  <div class="value" title="<?= strip_tags(mp_format_money($opening)); ?>"><?= mp_format_money_compact($opening); ?></div>
                </div>
                <div class="supplier-stat">
                  <div class="label">Purchase Due</div>
                  <div class="value due" title="<?= strip_tags(mp_format_money($s->purchase_due ?? 0)); ?>"><?= mp_format_money_compact($s->purchase_due ?? 0); ?></div>
                </div>
                <div class="supplier-stat">
                  <div class="label">Return Due</div>
                  <div class="value" title="<?= strip_tags(mp_format_money($s->purchase_return_due ?? 0)); ?>"><?= mp_format_money_compact($s->purchase_return_due ?? 0); ?></div>
                </div>
                <div class="supplier-stat">
                  <div class="label">Total Due</div>
                  <div class="value due" title="<?= strip_tags(mp_format_money($total_due)); ?>"><?= mp_format_money_compact($total_due); ?></div>
                </div>
              </div>

              <div class="supplier-actions">
                <a href="<?= base_url('mobile/supplier_profile/' . $s->id); ?>" class="action view"><i class="fa fa-user"></i> View</a>
                <?php if(permissions('suppliers_edit')): ?>
                  <a href="<?= base_url('mobile/edit_supplier/' . $s->id); ?>" class="action edit"><i class="fa fa-edit"></i> Edit</a>
                <?php endif; ?>
                <?php if($total_due > 0): ?>
                  <a href="<?= base_url('mobile/supplier_payment/' . $s->id); ?>" class="action pay"><i class="fa fa-money"></i> Pay</a>
                <?php endif; ?>
                <?php if(!empty($s->mobile)): ?>
                  <a href="tel:<?= $s->mobile; ?>" class="action call"><i class="fa fa-phone"></i> Call</a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">
            <i class="fa fa-truck"></i>
            <div>No suppliers found.</div>
          </div>
        <?php endif; ?>
      </div>
    </section>


  </div>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    function filterSuppliers(){
      var q = document.getElementById('supplierSearch').value.toLowerCase();
      var cards = document.querySelectorAll('.supplier-card');
      cards.forEach(function(card){
        var name = card.getAttribute('data-name');
        card.style.display = (name.indexOf(q) !== -1) ? '' : 'none';
      });
    }
  </script>
</body>
</html>
