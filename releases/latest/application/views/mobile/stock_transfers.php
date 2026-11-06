<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='utf-8'>
<?php $this->load->view('comman/app_icons'); ?>
  <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate'>
  <meta http-equiv='Pragma' content='no-cache'>
  <meta http-equiv='Expires' content='0'>
  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'>
  <title><?= $store_name ?? $SITE_TITLE ?? 'MartPoint'; ?> — Stock Transfers</title>
  <link rel='preconnect' href='https://fonts.googleapis.com'>
  <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap' rel='stylesheet'>
  <link rel='stylesheet' href='<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css'>
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .topbar .add { background: var(--mp-primary); color: #fff; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px; }
    .search-bar { display: flex; align-items: center; gap: 10px; background: #fff; border-radius: 14px; padding: 10px 14px; border: 1px solid var(--mp-border); margin-bottom: 12px; }
    .search-bar i { color: var(--mp-muted); font-size: 16px; }
    .search-bar input { border: none; background: transparent; flex: 1; font-size: 16px; outline: none; min-height: 28px; color: var(--mp-text); }
    .search-bar input::placeholder { color: var(--mp-muted); }
    .card { background: #fff; border-radius: 14px; padding: 12px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .tr-item { padding: 14px 0; border-bottom: 1px solid var(--mp-border); }
    .tr-item:last-child { border-bottom: none; }
    .tr-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px; }
    .tr-header .left { flex: 1; min-width: 0; }
    .tr-header .ref { font-weight: 700; font-size: 15px; }
    .tr-header .date { font-size: 12px; color: var(--mp-muted); }
    .tr-header .qty { font-weight: 700; font-size: 18px; color: var(--mp-primary); white-space: nowrap; }
    .tr-locations { font-size: 13px; color: var(--mp-ink); margin: 6px 0; display: flex; align-items: center; gap: 6px; }
    .tr-locations .arrow { color: var(--mp-muted); }
    .tr-meta { font-size: 12px; color: var(--mp-muted); }
    .tr-note { font-size: 12px; color: var(--mp-ink); margin-top: 4px; word-break: break-word; }
    .tr-actions { display: flex; justify-content: flex-end; gap: 14px; margin-top: 10px; }
    .tr-actions .tr-delete { color: var(--mp-danger); font-size: 13px; font-weight: 600; text-decoration: none; }
    .tr-actions .tr-count { color: var(--mp-primary); font-size: 13px; font-weight: 600; text-decoration: none; }
    .badge { display: inline-block; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 999px; margin-top: 6px; }
    .badge.transit { background: #FFFBEB; color: #92400E; }
    .badge.held { background: #FEF2F2; color: #991B1B; }
    .badge.arrived { background: #ECFDF5; color: #065F46; }
    .chips { display: flex; gap: 8px; overflow-x: auto; margin-bottom: 12px; -webkit-overflow-scrolling: touch; }
    .chip { flex: 0 0 auto; padding: 8px 14px; border-radius: 999px; border: 1px solid var(--mp-border); background: #fff; font-size: 13px; font-weight: 600; color: var(--mp-ink); cursor: pointer; }
    .chip.active { background: var(--mp-primary); color: #fff; border-color: var(--mp-primary); }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 100px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 120px; } }
  </style>
</head>
<body>
  <div id='app'>
    <section class='screen'>
      <div class='topbar'>
        <a href='<?= base_url('mobile/more'); ?>' class='back'><i class='fa fa-chevron-left'></i></a>
        <div class='topbar-titles'>
          <div class='store-name'><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Stock Transfers</h1>
        </div>
        <?php if(permissions('stock_transfer_add')): ?>
          <a href='<?= base_url('mobile/stock_transfer_form'); ?>' class='add'><i class='fa fa-plus'></i></a>
        <?php endif; ?>
      </div>

      <div class='search-bar'>
        <i class='fa fa-search'></i>
        <input type='text' id='tr-search' placeholder='Search by branch or note'>
      </div>

      <?php if(!empty($arrival_mode)): ?>
      <div class='chips' id='tr-chips'>
        <button type='button' class='chip active' data-status=''>All loads</button>
        <button type='button' class='chip' data-status='In Transit'>Waiting to count</button>
        <button type='button' class='chip' data-status='Awaiting Approval'>Held for approval</button>
        <button type='button' class='chip' data-status='Received'>Counted in</button>
      </div>
      <?php endif; ?>

      <div class='card tr-list'>
        <?php if(!empty($transfers)): ?>
          <?php foreach($transfers as $t): ?>
            <?php
              $tstatus = isset($t->receipt_status) ? (string)$t->receipt_status : 'Posted';
              $badge = array('In Transit' => array('transit', 'Waiting to count in'), 'Awaiting Approval' => array('held', 'Short — held for approval'), 'Received' => array('arrived', 'Counted in'));
              $review = !empty($arrival_mode) && $tstatus === 'Awaiting Approval';
            ?>
            <div class='tr-item' data-from='<?= strtolower(htmlspecialchars($t->from_name ?? '')); ?>' data-to='<?= strtolower(htmlspecialchars($t->to_name ?? '')); ?>' data-note='<?= strtolower(htmlspecialchars($t->note ?? '')); ?>' data-status='<?= $tstatus === 'Posted' ? '' : htmlspecialchars($tstatus); ?>'>
              <div class='tr-header'>
                <div class='left'>
                  <div class='ref'><?= htmlspecialchars($t->note ?: 'Transfer #'.$t->id); ?></div>
                  <div class='date'><?= show_date($t->transfer_date); ?> · <?= htmlspecialchars($t->created_by ?: '-'); ?></div>
                </div>
                <div class='qty'><?= number_format((float)$t->total_qty, 2); ?></div>
              </div>
              <div class='tr-locations'>
                <span><?= htmlspecialchars($t->from_name ?: '-'); ?></span>
                <i class='fa fa-long-arrow-right arrow'></i>
                <span><?= htmlspecialchars($t->to_name ?: '-'); ?></span>
              </div>
              <div class='tr-meta'><?= (int)$t->item_count; ?> item<?= (int)$t->item_count == 1 ? '' : 's'; ?> transferred</div>
              <?php if(!empty($arrival_mode) && isset($badge[$tstatus])): ?>
                <span class='badge <?= $badge[$tstatus][0]; ?>'><?= $badge[$tstatus][1]; ?></span>
              <?php endif; ?>
              <div class='tr-actions'>
                <?php if(!empty($t->can_count) || $review): ?>
                  <a href='<?= base_url('stock_transfer/receive/' . (int)$t->id); ?>' class='tr-count'><i class='fa <?= $review ? 'fa-exclamation-triangle' : 'fa-check-circle'; ?>"></i> <?= $review ? 'Review' : 'Count in'; ?></a>
                <?php endif; ?>
                <?php if(permissions('stock_transfer_delete')): ?>
                  <a href='javascript:void(0)' class='tr-delete' onclick='deleteStockTransfer(<?= (int)$t->id; ?>)'><i class='fa fa-trash'></i> Delete</a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class='empty-state'>
            <i class='fa fa-exchange'></i>
            <div>No stock transfers found.</div>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <script>
    var activeStatus = '';
    function filterTransfers(){
      var term = (document.getElementById('tr-search').value || '').toLowerCase().trim();
      document.querySelectorAll('.tr-list .tr-item').forEach(function(el){
        var from = el.dataset.from || '';
        var to = el.dataset.to || '';
        var note = el.dataset.note || '';
        var matchesText = (from.indexOf(term) !== -1 || to.indexOf(term) !== -1 || note.indexOf(term) !== -1);
        var status = el.dataset.status || '';
        var matchesStatus = !activeStatus || status === activeStatus;
        el.style.display = (matchesText && matchesStatus) ? 'block' : 'none';
      });
    }

    var searchInput = document.getElementById('tr-search');
    if(searchInput){ searchInput.addEventListener('input', filterTransfers); }

    document.querySelectorAll('#tr-chips .chip').forEach(function(chip){
      chip.addEventListener('click', function(){
        document.querySelectorAll('#tr-chips .chip').forEach(function(c){ c.classList.remove('active'); });
        chip.classList.add('active');
        activeStatus = chip.getAttribute('data-status') || '';
        filterTransfers();
      });
    });
  </script>

    function deleteStockTransfer(id){
      mpConfirm('Delete this stock transfer?', function(){
        var formData = new FormData();
        formData.append('q_id', id);
        formData.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
        mpFetchJson('<?= base_url('mobile/delete_stock_transfer'); ?>', { method: 'POST', body: formData })
        .then(function(data){
          if(data.status === 'success'){
            mpSuccess(data.message);
            setTimeout(function(){ window.location.reload(); }, 600);
          } else {
            mpError(data.message || 'The transfer could not be deleted. Please try again.');
          }
        })
        .catch(function(err){ mpError(mpErrorText(err)); });
      }, null, {danger: true});
    }
  </script>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>