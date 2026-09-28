<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 130px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: var(--mp-bg); }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .search-card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 14px; }
    .search-card .hint { font-size: 12px; color: var(--mp-muted); margin-bottom: 10px; }
    .search-row { display: flex; gap: 8px; }
    .search-row input { flex: 1; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-family: inherit; font-size: 15px; }
    .search-row button { padding: 12px 18px; border: none; border-radius: 12px; background: var(--mp-primary); color: #fff; font-weight: 700; cursor: pointer; }
    .card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 10px; }
    .card .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .nm { font-size: 15px; font-weight: 700; }
    .serials { margin-top: 8px; display: flex; flex-direction: column; gap: 4px; }
    .serial { font-size: 13px; font-family: 'SF Mono', 'Courier New', monospace; background: var(--mp-bg); padding: 6px 10px; border-radius: 8px; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge.valid { background: #D1FAE5; color: #047857; }
    .badge.expired { background: #FEE2E2; color: #B91C1C; }
    .badge.stock { background: #DBEAFE; color: #1D4ED8; }
    .meta { font-size: 12px; color: var(--mp-muted); margin-top: 8px; display: flex; flex-wrap: wrap; gap: 10px; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); font-size: 14px; }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 130px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 150px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/operations'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
      </div>

      <div class="search-card">
        <div class="hint">Search by serial number, IMEI, invoice number, customer name, phone or product.</div>
        <form class="search-row" method="get" action="<?= base_url('mobile/warranty_lookup'); ?>">
          <input type="search" name="search" placeholder="Serial / IMEI / invoice / customer…" value="<?= htmlspecialchars($search ?? ''); ?>" autofocus>
          <button type="submit"><i class="fa fa-search"></i></button>
        </form>
      </div>

      <?php if(!empty($search)): ?>
        <?php if(!empty($results)): ?>
          <?php foreach($results as $r):
            $is_stock = ($r->sales_code === 'In Stock');
            $warranty_ok = null;
            if(!$is_stock && !empty($r->warranty_months) && (int)$r->warranty_months > 0 && !empty($r->sales_date)){
              $expiry = strtotime($r->sales_date.' +'.(int)$r->warranty_months.' months');
              $warranty_ok = time() <= $expiry;
            }
          ?>
          <div class="card">
            <div class="top">
              <div>
                <div class="nm"><?= htmlspecialchars($r->item_name ?: 'Unknown item'); ?></div>
                <div class="meta"><span>#<?= htmlspecialchars($r->sales_code); ?></span></div>
              </div>
              <?php if($is_stock): ?>
                <span class="badge stock">In Stock</span>
              <?php elseif($warranty_ok === null): ?>
                <span class="badge stock">No warranty data</span>
              <?php elseif($warranty_ok): ?>
                <span class="badge valid">Under Warranty</span>
              <?php else: ?>
                <span class="badge expired">Warranty Expired</span>
              <?php endif; ?>
            </div>
            <div class="serials">
              <?php if(!empty($r->sold_serial_number)): ?><span class="serial"><i class="fa fa-barcode"></i> SN: <?= htmlspecialchars($r->sold_serial_number); ?></span><?php endif; ?>
              <?php if(!empty($r->sold_imei_number)): ?><span class="serial"><i class="fa fa-mobile"></i> IMEI: <?= htmlspecialchars($r->sold_imei_number); ?></span><?php endif; ?>
            </div>
            <div class="meta">
              <?php if(!empty($r->customer_name)): ?><span><i class="fa fa-user"></i> <?= htmlspecialchars($r->customer_name); ?><?= !empty($r->mobile) ? ' · '.htmlspecialchars($r->mobile) : ''; ?></span><?php endif; ?>
              <?php if(!empty($r->sales_date)): ?><span><i class="fa fa-calendar-o"></i> <?= $is_stock ? 'Stocked' : 'Sold'; ?> <?= show_date($r->sales_date); ?></span><?php endif; ?>
              <?php if(!empty($r->warranty_months) && (int)$r->warranty_months > 0): ?><span><i class="fa fa-shield"></i> <?= (int)$r->warranty_months; ?>-month warranty</span><?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">
            <i class="fa fa-shield"></i>
            <div>No matches for “<?= htmlspecialchars($search); ?>”.</div>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="empty-state">
          <i class="fa fa-shield"></i>
          <div>Enter a search term to check warranty status.</div>
        </div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
