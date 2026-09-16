<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Cutting Worksheet</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-info: #3B82F6; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .topbar .back { color: var(--mp-ink); font-size: 18px; text-decoration: none; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .summary { background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 16px; }
    .summary .row { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 6px; }
    .cut-row { background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 12px; }
    .cut-row .title { font-size: 15px; font-weight: 700; margin-bottom: 10px; }
    .form-group { margin-bottom: 12px; }
    .form-group label { display: block; font-size: 13px; font-weight: 500; margin-bottom: 5px; }
    .form-control { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 16px; background: #fff; outline: none; min-height: 48px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .btn-primary { width: 100%; padding: 18px; border: none; border-radius: 14px; background: var(--mp-primary); color: white; font-size: 17px; font-weight: 600; cursor: pointer; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/butchery'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Cutting Worksheet</h1>
        </div>
      </div>

      <div class="summary">
        <div class="row"><span>Carcass</span><strong><?= htmlspecialchars($carcass->carcass_name); ?></strong></div>
        <div class="row"><span>Lot</span><strong><?= htmlspecialchars($carcass->lot_number ?: '---'); ?></strong></div>
        <div class="row"><span>Total Weight</span><strong><?= number_format($carcass->receiving_weight, 2); ?> kg</strong></div>
        <div class="row"><span>Expected Yield</span><strong><?= number_format($carcass->expected_yield_pct, 2); ?>%</strong></div>
      </div>

      <form method="post" action="<?= base_url('mobile/save_butchery_cut/' . $carcass->id); ?>">
        <?php
        $rows = !empty($records) ? $records : $template_cuts;
        if (empty($rows)) $rows = [null, null, null];
        foreach ($rows as $i => $row):
          $name = $row->cut_name ?? '';
          $expected = isset($row->expected_weight_kg) ? $row->expected_weight_kg : ($row->expected_weight ?? 0);
          $actual = $row->actual_weight ?? 0;
          $packs = $row->packs ?? 1;
          $waste = $row->waste ?? 0;
          $purchase_price = $row->purchase_price ?? 0;
          $sales_price = $row->sales_price ?? 0;
          $notes = $row->notes ?? '';
        ?>
        <div class="cut-row">
          <div class="title">Cut <?= $i + 1; ?></div>
          <div class="form-group">
            <label>Cut Name</label>
            <input type="text" name="cuts[<?= $i; ?>][cut_name]" class="form-control" value="<?= htmlspecialchars($name); ?>" placeholder="e.g. Braising">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Expected (kg)</label>
              <input type="number" step="0.01" name="cuts[<?= $i; ?>][expected_weight]" class="form-control" value="<?= $expected; ?>">
            </div>
            <div class="form-group">
              <label>Actual (kg)</label>
              <input type="number" step="0.01" name="cuts[<?= $i; ?>][actual_weight]" class="form-control" value="<?= $actual; ?>">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Packs</label>
              <input type="number" step="1" name="cuts[<?= $i; ?>][packs]" class="form-control" value="<?= $packs; ?>">
            </div>
            <div class="form-group">
              <label>Waste (kg)</label>
              <input type="number" step="0.01" name="cuts[<?= $i; ?>][waste]" class="form-control" value="<?= $waste; ?>">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Cost</label>
              <input type="number" step="0.01" name="cuts[<?= $i; ?>][purchase_price]" class="form-control" value="<?= $purchase_price; ?>" placeholder="0.00">
            </div>
            <div class="form-group">
              <label>Price</label>
              <input type="number" step="0.01" name="cuts[<?= $i; ?>][sales_price]" class="form-control" value="<?= $sales_price; ?>" placeholder="0.00">
            </div>
          </div>
          <div class="form-group">
            <label>Notes</label>
            <input type="text" name="cuts[<?= $i; ?>][notes]" class="form-control" value="<?= htmlspecialchars($notes); ?>" placeholder="Notes">
          </div>
        </div>
        <?php endforeach; ?>

        <button type="submit" class="btn-primary"><i class="fa fa-check"></i> Complete Cutting</button>
      </form>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
    <?php $this->load->view('mobile/mp_alert'); ?>
    <?php $this->load->view('mobile/chat'); ?>
  </div>
</body>
</html>
