<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='utf-8'>
  <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate'>
  <meta http-equiv='Pragma' content='no-cache'>
  <meta http-equiv='Expires' content='0'>
  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'>
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — Locations</title>
  <link rel='preconnect' href='https://fonts.googleapis.com'>
  <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap' rel='stylesheet'>
  <link rel='stylesheet' href='<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css'>
  <style>
    :root { --mp-primary:#0057FF; --mp-bg:#F1F5F9; --mp-surface:#FFFFFF; --mp-text:#0F172A; --mp-muted:#64748B; --mp-border:#E2E8F0; --mp-danger:#EF4444; --mp-warning:#F59E0B; --mp-ink:#1E293B; }
    * { box-sizing:border-box; }
    html, body { margin:0; padding:0; font-family:Inter,-apple-system,BlinkMacSystemFont,sans-serif; background:var(--mp-bg); color:var(--mp-text); height:100%; overscroll-behavior:none; -webkit-tap-highlight-color:transparent; }
    #app { max-width:430px; margin:0 auto; background:var(--mp-surface); min-height:100vh; }
    .screen { padding:12px 12px 110px; }
    .topbar { display:flex; align-items:center; gap:12px; margin-bottom:12px; padding-top:8px; }
    .topbar .back { color:var(--mp-primary); font-size:20px; text-decoration:none; }
    .topbar .topbar-titles { flex:1; min-width:0; }
    .topbar .store-name { font-size:11px; color:var(--mp-muted); font-weight:600; text-transform:uppercase; letter-spacing:.4px; margin-bottom:2px; }
    .topbar h1 { font-size:20px; font-weight:700; margin:0; }
    .note { background:#EFF6FF; border:1px solid #BFDBFE; color:#1E40AF; border-radius:14px; padding:12px 14px; font-size:12px; line-height:1.5; margin-bottom:12px; }
    .card { background:#fff; border-radius:14px; padding:14px; margin-bottom:12px; border:1px solid var(--mp-border); }
    .card .name { font-weight:700; font-size:15px; }
    .card .chip { display:inline-block; padding:2px 8px; border-radius:999px; background:#EEF2F7; color:#475467; font-size:11px; font-weight:700; margin-left:6px; vertical-align:middle; }
    .card .addr { font-size:12px; color:var(--mp-muted); margin-top:4px; line-height:1.45; }
    .figs { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:12px; }
    .fig { background:var(--mp-bg); border-radius:10px; padding:10px; }
    .fig b { display:block; font-size:15px; font-variant-numeric:tabular-nums; }
    .fig span { display:block; font-size:11px; color:var(--mp-muted); margin-top:3px; }
    .switches { border-top:1px solid var(--mp-border); margin-top:12px; padding-top:12px; }
    .switch { display:flex; align-items:flex-start; gap:10px; padding:9px 0; }
    .switch input { width:20px; height:20px; margin-top:1px; accent-color:var(--mp-primary); }
    .switch label { flex:1; font-size:13px; line-height:1.4; color:var(--mp-ink); }
    .state { font-size:12px; color:var(--mp-muted); margin-top:8px; }
    .save-bar { position:sticky; bottom:0; background:#fff; border-top:1px solid var(--mp-border); padding:12px 0 calc(12px + env(safe-area-inset-bottom,0px)); }
    .btn { display:block; width:100%; padding:14px; border:none; border-radius:12px; font-size:16px; font-weight:600; cursor:pointer; text-align:center; text-decoration:none; background:var(--mp-primary); color:#fff; }
    .empty { text-align:center; padding:30px 16px; color:var(--mp-muted); font-size:13px; }
    .empty i { display:block; font-size:36px; color:var(--mp-border); margin-bottom:10px; }
    @media (min-width:600px){ #app{max-width:100%;margin:0} .screen{padding:16px 16px 120px} .card{max-width:640px} }
    @media (min-width:1024px){ .screen{padding:24px 48px 140px} }
  </style>
</head>
<body>
  <div id='app'>
    <section class='screen'>
      <div class='topbar'>
        <a href='<?= base_url('oil_gas'); ?>' class='back'><i class='fa fa-chevron-left'></i></a>
        <div class='topbar-titles'>
          <div class='store-name'><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Locations</h1>
        </div>
      </div>

      <?php if(!empty($switches) && !empty($locations) && $can_edit): ?>
      <div class='note'>
        A site can only narrow what head office runs, never grant itself more. Untick a capability to switch it off for that one <?= htmlspecialchars(strtolower($site_label)); ?>.
      </div>
      <?php endif; ?>

      <?php if(empty($locations)): ?>
        <div class='card'><div class='empty'><i class='fa fa-map-marker'></i>No locations are visible to you. Locations are created under Inventory → Warehouses, and staff only see the sites they are assigned to.</div></div>
      <?php elseif(!empty($can_edit) && !empty($switches)): ?>
        <?= form_open(base_url('oil_gas/save_locations')); ?>
        <?php foreach($locations as $location):
          $figures = isset($rollup[$location->id]) ? $rollup[$location->id] : null;
          $flags = mp_get_location_flags($location->id);
        ?>
        <div class='card'>
          <div class='name'><?= htmlspecialchars($location->warehouse_name); ?><?php if(!empty($location->is_distribution_center)): ?><span class='chip'>Depot</span><?php endif; ?></div>
          <?php if(!empty($location->address)): ?><div class='addr'><?= htmlspecialchars($location->address); ?></div><?php endif; ?>
          <div class='figs'>
            <div class='fig'><b><?= $figures ? (int)$figures['stock_lines'] : '—' ?></b><span>stock lines · <?= $figures ? htmlspecialchars(mp_format_money($figures['stock_value'])) : '—' ?></span></div>
            <div class='fig'><b><?= $figures ? (int)$figures['arrivals_waiting'] : '—' ?></b><span>waiting to count in</span></div>
            <div class='fig'><b><?= $figures ? (int)$figures['arrivals_held'] : '—' ?></b><span>held for approval</span></div>
            <div class='fig'><b><?= $figures ? (int)$figures['dispatches_month'] : '—' ?></b><span>sent this month</span></div>
          </div>
          <div class='switches'>
            <?php $any = false; foreach($switches as $key => $label): if(!mp_feature_enabled($key)) continue; $any = true; $on = !($flags && array_key_exists($key, $flags) && !$flags[$key]); ?>
              <div class='switch'>
                <input type='checkbox' id='sw_<?= (int)$location->id; ?>_<?= htmlspecialchars($key); ?>'
                       name='location[<?= (int)$location->id; ?>][<?= htmlspecialchars($key); ?>]' value='1' <?= $on ? 'checked' : ''; ?>>
                <label for='sw_<?= (int)$location->id; ?>_<?= htmlspecialchars($key); ?>'><?= htmlspecialchars($label); ?></label>
              </div>
            <?php endforeach; ?>
            <?php if(!$any): ?><div class='state'>Nothing here can be narrowed per site. Turn capabilities on under Business Profile first.</div><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
        <div class='save-bar'><button type='submit' class='btn'><i class='fa fa-check'></i> Save location settings</button></div>
        <?= form_close(); ?>
      <?php else: ?>
        <?php foreach($locations as $location):
          $figures = isset($rollup[$location->id]) ? $rollup[$location->id] : null;
          $flags = mp_get_location_flags($location->id);
        ?>
        <div class='card'>
          <div class='name'><?= htmlspecialchars($location->warehouse_name); ?></div>
          <?php if(!empty($location->address)): ?><div class='addr'><?= htmlspecialchars($location->address); ?></div><?php endif; ?>
          <div class='figs'>
            <div class='fig'><b><?= $figures ? (int)$figures['stock_lines'] : '—' ?></b><span>stock lines</span></div>
            <div class='fig'><b><?= $figures ? (int)$figures['arrivals_waiting'] : '—' ?></b><span>waiting to count in</span></div>
          </div>
          <?php foreach($switches as $key => $label): if(!mp_feature_enabled($key)) continue; ?>
            <div class='state'><?= ($flags && array_key_exists($key, $flags) && !$flags[$key]) ? 'Off' : 'On' ?> — <?= htmlspecialchars($label); ?></div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
