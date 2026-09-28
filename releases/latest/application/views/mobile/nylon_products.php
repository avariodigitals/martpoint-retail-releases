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
    :root { --mp-primary: #0057FF; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 130px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: var(--mp-bg); }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .section-label { font-size: 12px; font-weight: 700; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.4px; margin: 16px 0 8px; }
    .card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .card .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .nm { font-size: 14px; font-weight: 700; }
    .sub { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .stock { font-size: 13px; font-weight: 700; white-space: nowrap; }
    .stock.low { color: var(--mp-danger); }
    .chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
    .chip { background: var(--mp-bg); border-radius: 8px; padding: 4px 10px; font-size: 11px; font-weight: 600; color: var(--mp-ink); }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); font-size: 14px; }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    .hint { font-size: 12px; color: var(--mp-muted); text-align: center; margin-top: 14px; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 130px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 150px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/nylon'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
      </div>

      <?php
        $by_class = [];
        foreach($spec_items as $p){ $by_class[$p->item_class][] = $p; }
      ?>
      <?php if(!empty($by_class)): ?>
        <?php foreach($by_class as $cls => $items): ?>
        <div class="section-label"><?= htmlspecialchars(isset($classes[$cls]) ? $classes[$cls] : ucfirst(str_replace('_',' ',$cls))); ?></div>
        <?php foreach($items as $p):
          $chips = [];
          if(!empty($p->material)) $chips[] = $p->material;
          if(!empty($p->width) || !empty($p->length)) $chips[] = trim(($p->width ?: '').'×'.($p->length ?: ''), '×');
          if(!empty($p->thickness)) $chips[] = $p->thickness;
          if(!empty($p->colour)) $chips[] = $p->colour;
          if(!empty($p->print_type) && $p->print_type !== 'none') $chips[] = 'Print: '.$p->print_type;
          if(!empty($p->kg_per_roll)) $chips[] = $p->kg_per_roll.' kg/roll';
          if(!empty($p->design_ref)) $chips[] = 'Ref '.$p->design_ref;
        ?>
        <div class="card">
          <div class="top">
            <div>
              <div class="nm"><?= htmlspecialchars($p->item_name); ?></div>
              <div class="sub"><?= htmlspecialchars($p->item_code ?: ''); ?></div>
            </div>
            <span class="stock <?= (float)$p->stock <= 0 ? 'low' : ''; ?>"><?= rtrim(rtrim(number_format((float)$p->stock, 2), '0'), '.'); ?> <?= htmlspecialchars($p->unit_name ?: ''); ?></span>
          </div>
          <?php if(!empty($chips)): ?>
          <div class="chips"><?php foreach($chips as $c): ?><span class="chip"><?= htmlspecialchars($c); ?></span><?php endforeach; ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endforeach; ?>
        <div class="hint">Specs and selling units are edited on the desktop — Nylon → Products.</div>
      <?php else: ?>
        <div class="empty-state"><i class="fa fa-cubes"></i><div>No materials or products configured yet.<br>Set them up under Nylon → Products on desktop.</div></div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
