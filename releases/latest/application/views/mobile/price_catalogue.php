<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='utf-8'>
  <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate'>
  <meta http-equiv='Pragma' content='no-cache'>
  <meta http-equiv='Expires' content='0'>
  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'>
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Price Catalogue</title>
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
    .card { background: #fff; border-radius: 14px; padding: 12px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .cat-item { display: flex; justify-content: space-between; align-items: flex-start; padding: 14px 0; border-bottom: 1px solid var(--mp-border); }
    .cat-item:last-child { border-bottom: none; }
    .cat-item .left { flex: 1; min-width: 0; }
    .cat-item .name { font-weight: 700; font-size: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cat-item .meta { font-size: 12px; color: var(--mp-muted); margin-top: 3px; }
    .cat-item .right { text-align: right; flex-shrink: 0; }
    .cat-item .price { font-weight: 700; font-size: 18px; color: var(--mp-primary); }
    .cat-item .discount { font-size: 11px; color: var(--mp-muted); margin-top: 2px; }
    .cat-item .effective { font-size: 12px; font-weight: 600; color: var(--mp-success); }
    .cat-item .stock { font-size: 11px; color: var(--mp-muted); margin-top: 2px; }
    .badge { display: inline-block; font-size: 10px; font-weight: 600; padding: 3px 8px; border-radius: 20px; margin-top: 6px; }
    .badge.discount { background: #FEF3C7; color: #92400E; }
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
          <h1>Price Catalogue</h1>
        </div>
      </div>

      <div class='kpi-grid'>
        <div class='kpi-card blue'>
          <div class='label'>Products</div>
          <div class='value'><?= number_format(count($products)); ?></div>
        </div>
        <div class='kpi-card orange'>
          <div class='label'>Discounted</div>
          <?php $discounted = count(array_filter($products, function($p){ return (float)$p->discount > 0; })); ?>
          <div class='value'><?= number_format($discounted); ?></div>
        </div>
      </div>

      <div class='search-bar'>
        <i class='fa fa-search'></i>
        <input type='text' id='cat-search' placeholder='Search product, code or category'>
      </div>

      <div class='card cat-list'>
        <?php if(!empty($products)): ?>
          <?php foreach($products as $item):
            $price = (float)$item->sales_price;
            $effective = $price;
            if($item->discount_type == 'Fixed' && (float)$item->discount > 0){
              $effective = max(0, $price - (float)$item->discount);
            } elseif($item->discount_type == 'Percentage' && (float)$item->discount > 0){
              $effective = max(0, $price - ($price * (float)$item->discount / 100));
            }
            if((float)$item->online_price > 0){
              $effective = min($effective, (float)$item->online_price);
            }
            $has_discount = (float)$item->discount > 0;
          ?>
            <div class='cat-item' data-name='<?= strtolower(htmlspecialchars($item->item_name)); ?>' data-code='<?= strtolower(htmlspecialchars($item->item_code ?? '')); ?>' data-category='<?= strtolower(htmlspecialchars($item->category_name ?? '')); ?>' data-brand='<?= strtolower(htmlspecialchars($item->brand_name ?? '')); ?>'>
              <div class='left'>
                <div class='name'><?= htmlspecialchars($item->item_name); ?></div>
                <div class='meta'><?= htmlspecialchars($item->item_code ?: '-'); ?> · <?= htmlspecialchars($item->category_name ?: 'No category'); ?><?= !empty($item->brand_name) ? ' · ' . htmlspecialchars($item->brand_name) : ''; ?></div>
                <?php if($has_discount): ?>
                  <span class='badge discount'><?= htmlspecialchars($item->discount_type); ?> <?= $item->discount_type == 'Percentage' ? (float)$item->discount . '%' : store_number_format((float)$item->discount); ?></span>
                <?php endif; ?>
              </div>
              <div class='right'>
                <div class='price'><?= store_number_format($effective); ?></div>
                <?php if($has_discount): ?>
                  <div class='discount'><s><?= store_number_format($price); ?></s></div>
                <?php endif; ?>
                <div class='stock'><?= number_format((float)$item->stock, 0); ?> in stock</div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class='empty-state'>
            <i class='fa fa-tags'></i>
            <div>No products found.</div>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <script>
    var searchInput = document.getElementById('cat-search');
    if(searchInput){
      searchInput.addEventListener('input', function(){
        var term = this.value.toLowerCase().trim();
        document.querySelectorAll('.cat-list .cat-item').forEach(function(el){
          var name = el.dataset.name || '';
          var code = el.dataset.code || '';
          var category = el.dataset.category || '';
          var brand = el.dataset.brand || '';
          el.style.display = (name.indexOf(term) !== -1 || code.indexOf(term) !== -1 || category.indexOf(term) !== -1 || brand.indexOf(term) !== -1) ? 'flex' : 'none';
        });
      });
    }
  </script>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>