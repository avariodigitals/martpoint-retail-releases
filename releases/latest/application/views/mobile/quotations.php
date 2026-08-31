<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Quotations</title>
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
    .topbar .add { background: var(--mp-primary); color: #fff; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px; }
    .search-bar { display: flex; gap: 8px; margin-bottom: 12px; }
    .search-bar input { flex: 1; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; }
    .quote-card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .quote-title { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
    .quote-meta { font-size: 13px; color: var(--mp-muted); margin: 3px 0; }
    .quote-meta i { margin-right: 4px; color: var(--mp-primary); width: 16px; }
    .quote-actions { display: flex; gap: 8px; margin-top: 12px; }
    .action { flex: 1; text-align: center; padding: 9px 0; border-radius: 10px; font-size: 12px; font-weight: 600; text-decoration: none; color: #fff; }
    .action.view { background: #E0E7FF; color: var(--mp-primary); }
    .action.convert { background: #D1FAE5; color: #065F46; }
    .action.print { background: #FEF3C7; color: #B45309; }
    .action.delete { background: #FEF2F2; color: #B91C1B; }
    .empty-state { text-align: center; padding: 32px; color: var(--mp-muted); }
    .badge { display: inline-block; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; }
    .badge.converted { background: #D1FAE5; color: #065F46; }
    .badge.active { background: #E0E7FF; color: var(--mp-primary); }
    .badge.expired { background: #FEE2E2; color: #B91C1B; }
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
          <h1>Quotations</h1>
        </div>
        <a href="<?= base_url('mobile/quotation_form'); ?>" class="add"><i class="fa fa-plus"></i></a>
      </div>

      <div class="search-bar">
        <input type="text" id="quoteSearch" placeholder="Search..." oninput="filterQuotes()">
      </div>

      <div id="quoteList">
        <?php if(!empty($records)): ?>
          <?php
            $now = date('Y-m-d');
            foreach($records as $r):
              $status = 'Active';
              $badge = 'active';
              if(!empty($r->sales_status)){ $status = 'Converted'; $badge = 'converted'; }
              elseif(!empty($r->expire_date) && $r->expire_date < $now){ $status = 'Expired'; $badge = 'expired'; }
          ?>
            <div class="quote-card" data-search="<?= strtolower(($r->customer_name ?? '').' '.($r->quotation_code ?? '').' '.($r->reference_no ?? '')); ?>">
              <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div class="quote-title"><?= $r->quotation_code; ?></div>
                <span class="badge <?= $badge; ?>"><?= $status; ?></span>
              </div>
              <div class="quote-meta"><i class="fa fa-user"></i> <?= $r->customer_name ?: 'Walk-in'; ?></div>
              <div class="quote-meta"><i class="fa fa-calendar"></i> <?= show_date($r->quotation_date); ?></div>
              <?php if(!empty($r->expire_date)): ?>
                <div class="quote-meta"><i class="fa fa-hourglass"></i> Expires <?= show_date($r->expire_date); ?></div>
              <?php endif; ?>
              <div class="quote-meta"><i class="fa fa-money"></i> <?= store_number_format($r->grand_total); ?></div>
              <?php if(!empty($r->reference_no)): ?>
                <div class="quote-meta"><i class="fa fa-hashtag"></i> <?= $r->reference_no; ?></div>
              <?php endif; ?>
              <div class="quote-actions">
                <a href="<?= base_url('mobile/quotation_view/'.$r->id); ?>" class="action view"><i class="fa fa-eye"></i> View</a>
                <?php if(empty($r->sales_status) && permissions('quotation_edit')): ?>
                  <a href="<?= base_url('mobile/quotation_form/'.$r->id); ?>" class="action edit"><i class="fa fa-edit"></i> Edit</a>
                <?php endif; ?>
                <?php if(empty($r->sales_status) && permissions('sales_add')): ?>
                  <a href="<?= base_url('sales/quotation/'.$r->id); ?>" class="action convert"><i class="fa fa-exchange"></i> Invoice</a>
                <?php endif; ?>
                <?php if(empty($r->sales_status) && permissions('quotation_delete')): ?>
                  <a href="javascript:void(0)" class="action delete" onclick="deleteQuotation(<?= (int)$r->id; ?>)"><i class="fa fa-trash"></i></a>
                <?php endif; ?>
                <a href="<?= base_url('quotation/print_invoice/'.$r->id); ?>" target="_blank" class="action print"><i class="fa fa-print"></i> Print</a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">
            <i class="fa fa-list-alt" style="font-size:48px; margin-bottom:12px;"></i>
            <div>No quotations found.</div>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    function filterQuotes(){
      var q = document.getElementById('quoteSearch').value.toLowerCase();
      document.querySelectorAll('.quote-card').forEach(function(card){
        card.style.display = (card.getAttribute('data-search') || '').indexOf(q) >= 0 ? '' : 'none';
      });
    }

    function deleteQuotation(id){
      mpConfirm('Delete this quotation?', function(){
        var formData = new FormData();
        formData.append('q_id', id);
        formData.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
        fetch('<?= base_url('mobile/delete_quotation'); ?>', { method: 'POST', body: formData })
        .then(function(res){ return res.json(); })
        .then(function(data){
          if(data.status === 'success'){
            mpSuccess(data.message);
            setTimeout(function(){ window.location.reload(); }, 600);
          } else {
            mpError(data.message || 'Delete failed.');
          }
        })
        .catch(function(){ mpError('Network error.'); });
      }, null, {danger: true});
    }
  </script>
</body>
</html>
