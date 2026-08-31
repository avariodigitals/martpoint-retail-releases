<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='utf-8'>
  <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate'>
  <meta http-equiv='Pragma' content='no-cache'>
  <meta http-equiv='Expires' content='0'>
  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'>
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — <?= htmlspecialchars($item->item_name); ?></title>
  <link rel='preconnect' href='https://fonts.googleapis.com'>
  <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap' rel='stylesheet'>
  <link rel='stylesheet' href='<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css'>
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-info: #3B82F6; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .hero { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 12px; }
    .hero .avatar { width: 56px; height: 56px; border-radius: 14px; background: var(--mp-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; flex-shrink: 0; }
    .hero .info { flex: 1; min-width: 0; }
    .hero .name { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
    .hero .meta { font-size: 12px; color: var(--mp-muted); line-height: 1.4; }
    .kpi-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .kpi { padding: 14px; border-radius: 12px; background: var(--mp-bg); }
    .kpi .label { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 6px; }
    .kpi .value { font-size: 18px; font-weight: 700; color: var(--mp-ink); }
    .kpi .value.success { color: var(--mp-success); }
    .kpi .value.danger { color: var(--mp-danger); }
    .section-title { font-size: 15px; font-weight: 700; margin: 18px 0 10px; }
    .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--mp-border); font-size: 14px; }
    .info-row:last-child { border-bottom: none; }
    .info-row .label { color: var(--mp-muted); }
    .info-row .value { font-weight: 600; text-align: right; }
    .activity-item { display: flex; gap: 12px; padding: 14px 0; border-bottom: 1px solid var(--mp-border); }
    .activity-item:last-child { border-bottom: none; }
    .activity-icon { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; color: #fff; flex-shrink: 0; }
    .activity-icon.sale { background: var(--mp-danger); }
    .activity-icon.purchase { background: var(--mp-success); }
    .activity-icon.adjustment { background: var(--mp-warning); }
    .activity-icon.transfer { background: var(--mp-info); }
    .activity-body { flex: 1; min-width: 0; }
    .activity-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
    .activity-type { font-weight: 600; font-size: 14px; }
    .activity-qty { font-weight: 700; font-size: 15px; }
    .activity-qty.sale { color: var(--mp-danger); }
    .activity-qty.purchase { color: var(--mp-success); }
    .activity-qty.adjustment { color: var(--mp-warning); }
    .activity-qty.transfer { color: var(--mp-info); }
    .activity-date { font-size: 12px; color: var(--mp-muted); margin-bottom: 2px; }
    .activity-ref { font-size: 12px; color: var(--mp-muted); margin-bottom: 2px; }
    .activity-party { font-size: 12px; color: var(--mp-ink); margin-bottom: 2px; }
    .activity-note { font-size: 11px; color: var(--mp-muted); }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); font-size: 13px; }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 140px; } }
  </style>
</head>
<body>
  <div id='app'>
    <section class='screen'>
      <div class='topbar'>
        <a href='<?= base_url('mobile/catalogue'); ?>' class='back'><i class='fa fa-chevron-left'></i></a>
        <div class='topbar-titles'>
          <div class='store-name'><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Product Details</h1>
        </div>
      </div>

      <div class='card'>
        <div class='hero'>
          <div class='avatar'><i class='fa fa-cube'></i></div>
          <div class='info'>
            <div class='name'><?= htmlspecialchars($item->item_name); ?></div>
            <div class='meta'>
              <?= htmlspecialchars($item->item_code ?: '-'); ?> <?= !empty($item->custom_barcode) ? ' · ' . htmlspecialchars($item->custom_barcode) : ''; ?><br>
              <?= htmlspecialchars($category->category_name ?? 'No category'); ?><?= !empty($brand->brand_name) ? ' · ' . htmlspecialchars($brand->brand_name) : ''; ?>
            </div>
          </div>
        </div>

        <div class='kpi-grid'>
          <div class='kpi'>
            <div class='label'>Stock</div>
            <div class='value <?= (float)$item->stock <= 0 ? 'danger' : 'success'; ?>'><?= number_format((float)$item->stock, 0); ?></div>
          </div>
          <div class='kpi'>
            <div class='label'>Sales Price</div>
            <div class='value'><?= store_number_format($item->sales_price); ?></div>
          </div>
          <?php if((float)$item->online_price > 0): ?>
            <div class='kpi'>
              <div class='label'>Online Price</div>
              <div class='value'><?= store_number_format($item->online_price); ?></div>
            </div>
          <?php endif; ?>
          <?php if((float)$item->discount > 0): ?>
            <div class='kpi'>
              <div class='label'>Discount</div>
              <div class='value'><?= htmlspecialchars($item->discount_type); ?> <?= (float)$item->discount; ?><?= $item->discount_type == 'Percentage' ? '%' : ''; ?></div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class='card'>
        <div class='section-title' style='margin-top:0;'>Product Info</div>
        <div class='info-row'><span class='label'>Category</span><span class='value'><?= htmlspecialchars($category->category_name ?? '-'); ?></span></div>
        <div class='info-row'><span class='label'>Brand</span><span class='value'><?= htmlspecialchars($brand->brand_name ?? '-'); ?></span></div>
        <div class='info-row'><span class='label'>Code</span><span class='value'><?= htmlspecialchars($item->item_code ?: '-'); ?></span></div>
        <div class='info-row'><span class='label'>Barcode</span><span class='value'><?= htmlspecialchars($item->custom_barcode ?: '-'); ?></span></div>
        <div class='info-row'><span class='label'>Purchase Price</span><span class='value'><?= store_number_format($item->purchase_price); ?></span></div>
        <div class='info-row'><span class='label'>Sales Price</span><span class='value'><?= store_number_format($item->sales_price); ?></span></div>
        <div class='info-row'><span class='label'>Online Price</span><span class='value'><?= store_number_format($item->online_price); ?></span></div>
      </div>

      <div class='section-title'>Activity History</div>
      <?php if(!empty($activities)): ?>
        <div class='card'>
          <?php foreach($activities as $a):
            $qtyClass = $a->type == 'Sale' ? 'sale' : ($a->type == 'Purchase' ? 'purchase' : ($a->type == 'Adjustment' ? 'adjustment' : 'transfer'));
            $qtyDisplay = '';
            if($a->type == 'Sale'){ $qtyDisplay = '-' . number_format(abs((float)$a->qty), 0); }
            elseif($a->type == 'Purchase'){ $qtyDisplay = '+' . number_format(abs((float)$a->qty), 0); }
            elseif($a->type == 'Adjustment'){ $qtyDisplay = ((float)$a->qty > 0 ? '+' : '') . number_format((float)$a->qty, 0); }
            else { $qtyDisplay = number_format((float)$a->qty, 0); }
          ?>
            <div class='activity-item'>
              <div class='activity-icon <?= strtolower($a->type); ?>'><i class='fa <?= $a->icon; ?>'></i></div>
              <div class='activity-body'>
                <div class='activity-head'>
                  <div class='activity-type'><?= $a->type; ?></div>
                  <div class='activity-qty <?= $qtyClass; ?>'><?= $qtyDisplay; ?></div>
                </div>
                <div class='activity-date'><?= show_date($a->date); ?></div>
                <div class='activity-ref'><?= htmlspecialchars($a->reference); ?></div>
                <div class='activity-party'><?= htmlspecialchars($a->party); ?></div>
                <?php if(!empty($a->note)): ?>
                  <div class='activity-note'><?= htmlspecialchars($a->note); ?></div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class='empty-state'>
          <i class='fa fa-history'></i>
          <div>No activity recorded yet. Sales, purchases, adjustments and transfers will appear here.</div>
        </div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>