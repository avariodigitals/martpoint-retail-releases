<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Stock</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .kpi-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
    .kpi-card { padding: 18px 14px; border-radius: 16px; text-align: left; }
    .kpi-card.blue { background: #EFF6FF; }
    .kpi-card.orange { background: #FFFBEB; }
    .kpi-card .label { font-size: 12px; color: var(--mp-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px; }
    .kpi-card .value { font-size: 24px; font-weight: 700; }
    .search-bar { display: flex; align-items: center; gap: 10px; background: #fff; border-radius: 14px; padding: 10px 14px; border: 1px solid var(--mp-border); margin-bottom: 12px; }
    .search-bar i { color: var(--mp-muted); font-size: 16px; }
    .search-bar input { border: none; background: transparent; flex: 1; font-size: 16px; outline: none; min-height: 28px; color: var(--mp-text); }
    .search-bar input::placeholder { color: var(--mp-muted); }
    .section-title { font-size: 15px; font-weight: 700; margin: 14px 0 8px; }
    .card { background: #fff; border-radius: 14px; padding: 12px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .stock-item { display: flex; justify-content: space-between; align-items: center; padding: 14px 0; border-bottom: 1px solid var(--mp-border); }
    .stock-item:last-child { border-bottom: none; }
    .stock-item .left { flex: 1; min-width: 0; }
    .stock-item .name { font-weight: 600; font-size: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .stock-item .meta { font-size: 12px; color: var(--mp-muted); margin-top: 3px; }
    .stock-item .right { text-align: right; flex-shrink: 0; }
    .stock-item .qty { font-weight: 700; font-size: 18px; }
    .stock-item .qty.low { color: var(--mp-danger); }
    .stock-item .qty.out { color: var(--mp-danger); }
    .stock-item .qty.good { color: var(--mp-success); }
    .stock-item .alert { font-size: 11px; color: var(--mp-muted); margin-top: 2px; }
    .badge { display: inline-block; font-size: 10px; font-weight: 600; padding: 3px 8px; border-radius: 20px; margin-top: 6px; }
    .badge.low { background: #FEF2F2; color: #991B1B; }
    .badge.out { background: #FEF2F2; color: #991B1B; }
    .badge.good { background: #D1FAE5; color: #065F46; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .bottom-nav { max-width: 100%; left: 0; right: 0; transform: none; } .screen { padding: 16px 16px 100px; } .kpi-grid { grid-template-columns: 1fr 1fr; } }
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
          <h1>Stock</h1>
        </div>
      </div>

      <div class="kpi-grid">
        <div class="kpi-card blue">
          <div class="label">Total Items</div>
          <div class="value"><?= number_format(count($stock_items)); ?></div>
        </div>
        <div class="kpi-card orange">
          <div class="label">Low Stock</div>
          <div class="value <?= $low_stock_count > 0 ? 'low' : ''; ?>" style="color:<?= $low_stock_count > 0 ? 'var(--mp-danger)' : 'var(--mp-text)'; ?>"><?= number_format($low_stock_count); ?></div>
        </div>
      </div>

      <?php if(!empty($low_stock_items)): ?>
        <div class="section-title">Needs Attention</div>
        <div class="card">
          <?php foreach($low_stock_items as $ls): ?>
            <div class="stock-item">
              <div class="left">
                <div class="name"><?= $ls['name']; ?></div>
                <div class="meta">Alert at <?= number_format($ls['min'], 0); ?></div>
              </div>
              <div class="right">
                <div class="qty low"><?= number_format($ls['qty'], 0); ?></div>
                <span class="badge out">Low</span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="section-title">All Stock</div>

      <div class="search-bar">
        <i class="fa fa-search"></i>
        <input type="text" id="stock-search" placeholder="Search product, code or category">
      </div>

      <div class="card stock-list">
        <?php if(!empty($stock_items)): ?>
          <?php foreach($stock_items as $item):
            $is_low = ($item->alert_qty > 0 && (float)$item->stock <= (float)$item->alert_qty);
            $is_out = ((float)$item->stock <= 0);
          ?>
            <div class="stock-item" data-name="<?= strtolower($item->item_name); ?>" data-code="<?= strtolower($item->item_code ?? ''); ?>" data-category="<?= strtolower($item->category_name ?? ''); ?>">
              <div class="left">
                <div class="name"><?= $item->item_name; ?></div>
                <div class="meta"><?= $item->item_code ?: '-'; ?> · <?= $item->category_name ?: 'No category'; ?><?= !empty($item->brand_name) ? ' · ' . $item->brand_name : ''; ?></div>
              </div>
              <div class="right">
                <div class="qty <?= $is_out ? 'out' : ($is_low ? 'low' : 'good'); ?>"><?= number_format($item->stock, 0); ?></div>
                <?php if($is_out): ?>
                  <span class="badge out">Out</span>
                <?php elseif($is_low): ?>
                  <span class="badge low">Low</span>
                <?php else: ?>
                  <span class="badge good">Good</span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">
            <i class="fa fa-cubes"></i>
            <div>No stock records found.</div>
          </div>
        <?php endif; ?>
      </div>
    </section>


  </div>

  <script>
    var searchInput = document.getElementById('stock-search');
    if(searchInput){
      searchInput.addEventListener('input', function(){
        var term = this.value.toLowerCase().trim();
        document.querySelectorAll('.stock-list .stock-item').forEach(function(el){
          var name = el.dataset.name || '';
          var code = el.dataset.code || '';
          var category = el.dataset.category || '';
          el.style.display = (name.indexOf(term) !== -1 || code.indexOf(term) !== -1 || category.indexOf(term) !== -1) ? 'flex' : 'none';
        });
      });
    }
  </script>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
