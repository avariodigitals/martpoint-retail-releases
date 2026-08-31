<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='utf-8'>
  <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate'>
  <meta http-equiv='Pragma' content='no-cache'>
  <meta http-equiv='Expires' content='0'>
  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'>
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Stock Adjustments</title>
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
    .search-bar { display: flex; align-items: center; gap: 10px; background: #fff; border-radius: 14px; padding: 10px 14px; border: 1px solid var(--mp-border); margin-bottom: 12px; }
    .search-bar i { color: var(--mp-muted); font-size: 16px; }
    .search-bar input { border: none; background: transparent; flex: 1; font-size: 16px; outline: none; min-height: 28px; color: var(--mp-text); }
    .search-bar input::placeholder { color: var(--mp-muted); }
    .card { background: #fff; border-radius: 14px; padding: 12px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .adj-item { display: flex; justify-content: space-between; align-items: flex-start; padding: 14px 0; border-bottom: 1px solid var(--mp-border); }
    .adj-item:last-child { border-bottom: none; }
    .adj-item .left { flex: 1; min-width: 0; }
    .adj-item .ref { font-weight: 700; font-size: 15px; }
    .adj-item .meta { font-size: 12px; color: var(--mp-muted); margin-top: 3px; }
    .adj-item .note { font-size: 12px; color: var(--mp-ink); margin-top: 4px; word-break: break-word; }
    .adj-item .right { text-align: right; flex-shrink: 0; }
    .adj-item .qty { font-weight: 700; font-size: 18px; }
    .adj-item .qty.up { color: var(--mp-success); }
    .adj-item .qty.down { color: var(--mp-danger); }
    .adj-item .count { font-size: 11px; color: var(--mp-muted); margin-top: 2px; }
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
          <div class='store-name'><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Stock Adjustments</h1>
        </div>
      </div>

      <div class='search-bar'>
        <i class='fa fa-search'></i>
        <input type='text' id='adj-search' placeholder='Search by reference or note'>
      </div>

      <div class='card adj-list'>
        <?php if(!empty($adjustments)): ?>
          <?php foreach($adjustments as $adj): $up = (float)$adj->total_qty >= 0; ?>
            <div class='adj-item' data-ref='<?= strtolower(htmlspecialchars($adj->reference_no ?? '')); ?>' data-note='<?= strtolower(htmlspecialchars($adj->adjustment_note ?? '')); ?>'>
              <div class='left'>
                <div class='ref'><?= htmlspecialchars($adj->reference_no ?: '-'); ?></div>
                <div class='meta'><?= show_date($adj->adjustment_date); ?> · <?= htmlspecialchars($adj->created_by ?: '-'); ?></div>
                <div class='note'><?= htmlspecialchars($adj->adjustment_note ?: ''); ?></div>
              </div>
              <div class='right'>
                <div class='qty <?= $up ? 'up' : 'down'; ?>'><?= ($up ? '+' : '').number_format((float)$adj->total_qty, 2); ?></div>
                <div class='count'><?= (int)$adj->item_count; ?> item<?= (int)$adj->item_count == 1 ? '' : 's'; ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class='empty-state'>
            <i class='fa fa-sliders'></i>
            <div>No stock adjustments found.</div>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <script>
    var searchInput = document.getElementById('adj-search');
    if(searchInput){
      searchInput.addEventListener('input', function(){
        var term = this.value.toLowerCase().trim();
        document.querySelectorAll('.adj-list .adj-item').forEach(function(el){
          var ref = el.dataset.ref || '';
          var note = el.dataset.note || '';
          el.style.display = (ref.indexOf(term) !== -1 || note.indexOf(term) !== -1) ? 'flex' : 'none';
        });
      });
    }
  </script>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>