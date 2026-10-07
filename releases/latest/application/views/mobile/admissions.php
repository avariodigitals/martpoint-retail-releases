<?php $CI =& get_instance(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — Admissions</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-teal: #0D9488; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); text-decoration: none; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .ad-card { background: #fff; border-radius: 14px; border: 1px solid var(--mp-border); padding: 12px 14px; margin-bottom: 10px; }
    .ad-name { font-size: 15px; font-weight: 700; }
    .ad-meta { font-size: 11px; color: var(--mp-muted); margin-top: 4px; line-height: 1.6; }
    .ad-badge { font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 10px; text-transform: uppercase; }
    .b-active { background: #DCFCE7; color: #166534; } .b-discharged { background: #DBEAFE; color: #1E40AF; }
    .b-deceased { background: #111827; color: #F9FAFB; } .b-transferred_out { background: #FEF3C7; color: #92400E; }
    .empty { text-align: center; padding: 50px 24px; color: var(--mp-muted); font-size: 14px; }
  </style>
</head>
<body>
<div id="app">
  <div class="screen">
    <div class="topbar">
      <a class="back" href="<?= base_url('mobile/more'); ?>"><i class="fa fa-arrow-left"></i></a>
      <div class="topbar-titles">
        <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
        <h1>Admissions</h1>
      </div>
    </div>

    <?php if(empty($admissions)): ?>
      <div class="empty"><i class="fa fa-bed" style="font-size:30px;display:block;margin-bottom:10px;"></i>No admissions recorded.</div>
    <?php endif; ?>

    <?php foreach($admissions as $a): ?>
    <div class="ad-card">
      <div class="ad-name"><?= htmlspecialchars($a->full_name); ?>
        <span class="ad-badge b-<?= htmlspecialchars($a->status); ?>"><?= htmlspecialchars($a->status); ?></span></div>
      <div class="ad-meta">
        <?= htmlspecialchars($a->admission_code); ?> · <?= htmlspecialchars($a->patient_code); ?><br>
        Admitted <?= htmlspecialchars($a->admitted_at); ?><?= $a->closed_at ? ' · closed ' . htmlspecialchars($a->closed_at) : ''; ?><br>
        <?= htmlspecialchars(trim(($a->ward_name ?? 'No bed') . ' / ' . ($a->bed_label ?? '—'))); ?> · packages: <?= htmlspecialchars($a->outpatient_decision); ?>
        <?php if($a->reason): ?><br><?= htmlspecialchars($a->reason); ?><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
</div>
<?php $this->load->view('mobile/chat'); ?>
</body>
</html>
