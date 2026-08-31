<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — <?= $page_title; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
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
    .fin-card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .fin-title { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
    .fin-meta { font-size: 13px; color: var(--mp-muted); margin: 3px 0; }
    .fin-meta i { margin-right: 4px; color: var(--mp-primary); width: 16px; }
    .fin-actions { display: flex; gap: 8px; margin-top: 12px; }
    .action { flex: 1; text-align: center; padding: 9px 0; border-radius: 10px; font-size: 12px; font-weight: 600; text-decoration: none; color: #fff; }
    .action.edit { background: #FEF3C7; color: #B45309; }
    .action.delete { background: #FEE2E2; color: #B91C1C; }
    .action.view { background: #E0E7FF; color: var(--mp-primary); }
    .empty-state { text-align: center; padding: 32px; color: var(--mp-muted); }
    .totals { background: #E0E7FF; border-radius: 12px; padding: 12px; margin-bottom: 12px; }
    .totals .label { font-size: 12px; color: var(--mp-muted); }
    .totals .value { font-size: 18px; font-weight: 700; color: var(--mp-primary); }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    .badge { padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }
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
          <h1><?= $page_title; ?></h1>
        </div>
        <?php
          $can_add = false;
          switch($type){
            case 'accounts': $can_add = permissions('accounts_add'); break;
            case 'money_transfers': $can_add = permissions('money_transfer_add'); break;
            case 'money_deposits': $can_add = permissions('money_deposit_add'); break;
            case 'tills': $can_add = permissions('tills_add'); break;
            case 'expenses': $can_add = permissions('expense_add'); break;
          }
        ?>
        <?php if($can_add): ?>
          <a href="<?= base_url('mobile/finance/'.$type.'/form'); ?>" class="add"><i class="fa fa-plus"></i></a>
        <?php endif; ?>
      </div>

      <div class="search-bar">
        <input type="text" id="listSearch" placeholder="Search..." oninput="filterList()">
      </div>

      <?php if($type == 'cash_transactions' && !empty($records)):
        $total = 0;
        foreach($records as $r){ $total += (float)$r->payment; }
      ?>
        <div class="totals">
          <div class="label">Total Payments</div>
          <div class="value"><?= store_number_format($total); ?></div>
        </div>
      <?php endif; ?>

      <div id="recordsList">
        <?php if(!empty($records)): ?>
          <?php foreach($records as $r): ?>
            <div class="fin-card" data-search="<?= strtolower(
              ($type == 'accounts') ? ($r->account_name.' '.$r->account_code) :
              (($type == 'money_transfers') ? ($r->transfer_code.' '.$r->reference_no) :
              (($type == 'money_deposits') ? ($r->reference_no.' '.get_account_name($r->credit_account_id)) :
              (($type == 'tills') ? ($r->till_name) :
              (($type == 'expenses') ? ($r->expense_for.' '.$r->reference_no) :
              (($type == 'cash_transactions') ? ($r->payment_code.' '.$r->payment_note) : '')))))
            ); ?>">

              <?php if($type == 'accounts'): ?>
                <div class="fin-title"><?= $r->account_name; ?></div>
                <div class="fin-meta"><i class="fa fa-barcode"></i> <?= $r->account_code; ?></div>
                <div class="fin-meta"><i class="fa fa-money"></i> Balance: <?= store_number_format($r->balance); ?></div>
                <?php if($r->parent_id): ?><div class="fin-meta"><i class="fa fa-sitemap"></i> Parent: <?= get_account_name($r->parent_id); ?></div><?php endif; ?>
                <div class="fin-actions">
                  <?php if(permissions('accounts_edit')): ?>
                    <a href="<?= base_url('mobile/finance/accounts/form/'.$r->id); ?>" class="action edit"><i class="fa fa-edit"></i> Edit</a>
                  <?php endif; ?>
                  <a href="<?= base_url('accounts/book/'.$r->id); ?>" class="action view"><i class="fa fa-book"></i> Book</a>
                </div>

              <?php elseif($type == 'money_transfers'): ?>
                <div class="fin-title"><?= $r->transfer_code; ?></div>
                <div class="fin-meta"><i class="fa fa-calendar"></i> <?= show_date($r->transfer_date); ?></div>
                <div class="fin-meta"><i class="fa fa-arrow-up"></i> From: <?= get_account_name($r->debit_account_id); ?></div>
                <div class="fin-meta"><i class="fa fa-arrow-down"></i> To: <?= get_account_name($r->credit_account_id); ?></div>
                <div class="fin-meta"><i class="fa fa-money"></i> <?= store_number_format($r->amount); ?></div>
                <div class="fin-actions">
                  <?php if(permissions('money_transfer_edit')): ?>
                    <a href="<?= base_url('mobile/finance/money_transfers/form/'.$r->id); ?>" class="action edit"><i class="fa fa-edit"></i> Edit</a>
                  <?php endif; ?>
                </div>

              <?php elseif($type == 'money_deposits'): ?>
                <div class="fin-title"><?= get_account_name($r->credit_account_id); ?></div>
                <div class="fin-meta"><i class="fa fa-calendar"></i> <?= show_date($r->deposit_date); ?></div>
                <?php if($r->debit_account_id): ?><div class="fin-meta"><i class="fa fa-arrow-up"></i> From: <?= get_account_name($r->debit_account_id); ?></div><?php endif; ?>
                <div class="fin-meta"><i class="fa fa-money"></i> <?= store_number_format($r->amount); ?></div>
                <?php if($r->reference_no): ?><div class="fin-meta"><i class="fa fa-hashtag"></i> <?= $r->reference_no; ?></div><?php endif; ?>
                <div class="fin-actions">
                  <?php if(permissions('money_deposit_edit')): ?>
                    <a href="<?= base_url('mobile/finance/money_deposits/form/'.$r->id); ?>" class="action edit"><i class="fa fa-edit"></i> Edit</a>
                  <?php endif; ?>
                </div>

              <?php elseif($type == 'cash_transactions'): ?>
                <div class="fin-title"><?= $r->payment_code; ?></div>
                <div class="fin-meta"><i class="fa fa-calendar"></i> <?= show_date($r->payment_date); ?></div>
                <div class="fin-meta"><i class="fa fa-tag"></i> <?= $r->SALES_PAYMENT; ?></div>
                <div class="fin-meta"><i class="fa fa-money"></i> <?= store_number_format($r->payment); ?></div>
                <div class="fin-meta"><i class="fa fa-user"></i> <?= get_account_name($r->account_id); ?></div>
                <div class="fin-meta"><i class="fa fa-comment"></i> <?= $r->payment_note; ?></div>

              <?php elseif($type == 'tills'): ?>
                <div class="fin-title"><?= $r->till_name; ?> <?= $r->is_default ? '<span class="badge" style="background:#D1FAE5;color:#065F46;">Default</span>' : ''; ?></div>
                <div class="fin-meta"><i class="fa fa-user"></i> Cashier: <?= $r->first_name.' '.$r->last_name; ?></div>
                <div class="fin-meta"><i class="fa fa-university"></i> Account: <?= $r->account_name; ?></div>
                <div class="fin-meta"><i class="fa fa-money"></i> Balance: <?= store_number_format($r->balance); ?></div>
                <div class="fin-actions">
                  <?php if(permissions('tills_edit')): ?>
                    <a href="<?= base_url('mobile/finance/tills/form/'.$r->id); ?>" class="action edit"><i class="fa fa-edit"></i> Edit</a>
                  <?php endif; ?>
                </div>

              <?php elseif($type == 'expenses'): ?>
                <div class="fin-title"><?= $r->expense_for; ?></div>
                <div class="fin-meta"><i class="fa fa-calendar"></i> <?= show_date($r->expense_date); ?></div>
                <div class="fin-meta"><i class="fa fa-folder"></i> <?= $r->category_name; ?></div>
                <div class="fin-meta"><i class="fa fa-money"></i> <?= store_number_format($r->expense_amt); ?></div>
                <div class="fin-meta"><i class="fa fa-credit-card"></i> <?= $r->payment_type; ?></div>
                <div class="fin-actions">
                  <?php if(permissions('expense_edit')): ?>
                    <a href="<?= base_url('mobile/finance/expenses/form/'.$r->id); ?>" class="action edit"><i class="fa fa-edit"></i> Edit</a>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">
            <i class="fa fa-folder-open" style="font-size:48px; margin-bottom:12px;"></i>
            <div>No records found.</div>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    function filterList(){
      var q = document.getElementById('listSearch').value.toLowerCase();
      document.querySelectorAll('.fin-card').forEach(function(card){
        card.style.display = (card.getAttribute('data-search') || '').indexOf(q) >= 0 ? '' : 'none';
      });
    }
  </script>
</body>
</html>
