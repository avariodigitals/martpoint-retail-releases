<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Perfume Lab</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-info: #3B82F6; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
    .kpi-card { padding: 14px 8px; border-radius: 16px; text-align: center; }
    .kpi-card.green { background: #ECFDF5; } .kpi-card.yellow { background: #FFFBEB; } .kpi-card.blue { background: #EFF6FF; } .kpi-card.red { background: #FEF2F2; }
    .kpi-card .label { font-size: 10px; color: var(--mp-muted); margin-bottom: 5px; text-transform: uppercase; font-weight: 600; }
    .kpi-card .value { font-size: 19px; font-weight: 700; color: var(--mp-ink); }
    .section-title { font-size: 14px; font-weight: 600; color: var(--mp-muted); margin: 20px 0 10px; text-transform: uppercase; }
    .mac-card { background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 12px; margin-bottom: 10px; }
    .mac-card .title { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
    .mac-card .meta { font-size: 12px; color: var(--mp-muted); margin-bottom: 6px; }
    .mac-progress { height: 7px; border-radius: 4px; background: var(--mp-bg); overflow: hidden; margin: 8px 0 4px; }
    .mac-progress > span { display: block; height: 100%; background: #B45309; }
    .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .badge-success { background: #ECFDF5; color: #065F46; }
    .badge-info { background: #EFF6FF; color: #1E40AF; }
    .badge-warning { background: #FFFBEB; color: #92400E; }
    .badge-danger { background: #FEF2F2; color: #991B1B; }
    .waste-row { display: flex; justify-content: space-between; align-items: center; background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 11px 12px; margin-bottom: 8px; }
    .waste-row .item { font-weight: 600; font-size: 13px; }
    .waste-row .sub { font-size: 11px; color: var(--mp-muted); }
    .waste-row .cost { font-size: 13px; font-weight: 700; color: var(--mp-danger); }
    .form-card { background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 10px; }
    .form-card label { display: block; font-size: 12px; font-weight: 600; color: var(--mp-ink); margin: 10px 0 4px; }
    .form-card select, .form-card input { width: 100%; padding: 11px 12px; border: 1px solid var(--mp-border); border-radius: 10px; font-size: 14px; font-family: inherit; background: #fff; }
    .mp-select { display: none; }
    .mp-select-wrap { position: relative; width: 100%; }
    .mp-select-trigger { width: 100%; padding: 12px 36px 12px 14px; border: 1px solid var(--mp-border); border-radius: 10px; font-size: 14px; background: #fff; color: var(--mp-text); cursor: pointer; display: flex; align-items: center; justify-content: space-between; min-height: 44px; }
    .mp-select-trigger::after { content: '\f0d7'; font-family: 'FontAwesome'; color: var(--mp-muted); font-size: 13px; }
    .mp-select-trigger.placeholder { color: var(--mp-muted); }
    .mp-select-options { display: none; border: 1px solid var(--mp-border); border-top: none; border-radius: 0 0 10px 10px; background: #fff; max-height: 220px; overflow-y: auto; position: absolute; left: 0; right: 0; top: 100%; z-index: 20; }
    .mp-select-wrap.open .mp-select-options { display: block; }
    .mp-select-wrap.open .mp-select-trigger { border-radius: 10px 10px 0 0; }
    .mp-select-option { padding: 12px 14px; cursor: pointer; border-bottom: 1px solid var(--mp-border); font-size: 14px; }
    .mp-select-option:last-child { border-bottom: none; }
    .mp-select-option:hover, .mp-select-option.active { background: var(--mp-bg); }
    .form-card .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .btn-block { display: block; width: 100%; padding: 13px; border: none; border-radius: 12px; background: var(--mp-primary); color: #fff; font-size: 15px; font-weight: 700; margin-top: 14px; cursor: pointer; }
    .empty-state { text-align: center; padding: 36px 20px; color: var(--mp-muted); font-size: 13px; }
    details.loss-form summary { list-style: none; display: flex; align-items: center; justify-content: space-between; background: var(--mp-primary); color: #fff; border-radius: 14px; padding: 14px 16px; font-weight: 700; font-size: 14px; cursor: pointer; }
    details.loss-form summary::-webkit-details-marker { display: none; }
    @media (min-width: 430px) { }
  </style>
</head>
<body>
  <?php $CI =& get_instance(); ?>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Perfume Lab</h1>
        </div>
        <a href="<?= base_url('mobile/more'); ?>" style="color:var(--mp-primary); font-size:13px; font-weight:600;">More</a>
      </div>

      <?php if($this->session->flashdata('success')): ?>
        <?php $flash_success = trim(strip_tags(str_ireplace(['</p>','<br>','<br/>','<br />'], [' ',' ',' ',' '], $this->session->flashdata('success')))); ?>
        <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($flash_success, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: 'success'});</script>
      <?php endif; ?>
      <?php if($this->session->flashdata('failed')): ?>
        <?php $flash_failed = trim(strip_tags(str_ireplace(['</p>','<br>','<br/>','<br />'], [' ',' ',' ',' '], $this->session->flashdata('failed')))); ?>
        <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($flash_failed, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: 'danger'});</script>
      <?php endif; ?>

      <div class="kpi-grid">
        <div class="kpi-card blue"><div class="label">Active Batches</div><div class="value"><?= (int)($stats['active_batches'] ?? 0); ?></div></div>
        <div class="kpi-card yellow"><div class="label">Macerating</div><div class="value"><?= (int)($stats['macerating'] ?? 0); ?></div></div>
        <div class="kpi-card red"><div class="label">Losses (Mo.)</div><div class="value" style="font-size:15px;"><?= $CI->currency($stats['waste_cost_month'] ?? 0); ?></div></div>
      </div>

      <div class="section-title">Maceration Watch</div>
      <?php if (!empty($macerating)): ?>
        <?php foreach ($macerating as $b):
          $pct = ($b->required_days > 0 && $b->days_in !== null) ? min(100, round($b->days_in / $b->required_days * 100)) : null;
          $ready = ($b->days_left !== null && $b->days_left <= 0); ?>
        <div class="mac-card">
          <div class="title"><?= htmlspecialchars($b->batch_name); ?></div>
          <div class="meta"><?= htmlspecialchars(implode(', ', $b->recipes) ?: 'No formula'); ?> · <?= htmlspecialchars($b->batch_code); ?></div>
          <div class="meta">
            <?php if ($b->maceration_started): ?>Started <?= show_date($b->maceration_started); ?> · <?= (int)$b->days_in; ?>d in<?php endif; ?>
            <?php if ($b->required_days > 0): ?> · needs <?= (int)$b->required_days; ?>d<?php endif; ?>
          </div>
          <?php if ($pct !== null): ?>
          <div class="mac-progress"><span style="width:<?= $pct; ?>%"></span></div>
          <span class="badge <?= $ready ? 'badge-success' : 'badge-warning'; ?>"><?= $ready ? 'Ready to bottle' : $b->days_left . 'd left'; ?></span>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">Nothing is macerating right now.</div>
      <?php endif; ?>

      <div class="section-title">Log a Loss</div>
      <details class="loss-form">
        <summary><span><i class="fa fa-trash-o"></i> Record spillage, breakage, testers…</span><i class="fa fa-chevron-down"></i></summary>
        <div class="form-card" style="border:none;border-radius:0;">
          <form method="post" action="<?= base_url('mobile/save_perfume_wastage'); ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
            <label>Material</label>
            <select class="mp-select" name="item_id" required>
              <option value="">Select item…</option>
              <?php foreach (($items ?? []) as $it): ?>
              <option value="<?= $it->id; ?>"><?= htmlspecialchars($it->item_name); ?><?= $it->not_for_sale ? ' (raw)' : ''; ?> — <?= format_qty($it->stock); ?> <?= htmlspecialchars($it->unit_name ?: ''); ?></option>
              <?php endforeach; ?>
            </select>
            <label>Stage</label>
            <select class="mp-select" name="stage">
              <?php foreach (($stages ?? []) as $sk => $sl): ?>
              <option value="<?= $sk; ?>"><?= htmlspecialchars($sl); ?></option>
              <?php endforeach; ?>
            </select>
            <div class="row2">
              <div><label>Qty Lost</label><input type="number" step="0.001" min="0.001" name="qty" required placeholder="e.g. 25"></div>
              <div><label>Unit</label><input type="text" name="unit_name" placeholder="ml"></div>
            </div>
            <label>Blend Batch (optional)</label>
            <select class="mp-select" name="batch_id">
              <option value="">None</option>
              <?php foreach (($batches ?? []) as $b): ?>
              <option value="<?= $b->id; ?>"><?= htmlspecialchars($b->batch_code); ?></option>
              <?php endforeach; ?>
            </select>
            <label>Reason</label>
            <input type="text" name="reason" placeholder="e.g. Bottle slipped while filling">
            <button type="submit" class="btn-block"><i class="fa fa-check"></i> Record Loss</button>
          </form>
        </div>
      </details>

      <div class="section-title">Recent Losses</div>
      <?php if (!empty($wastage)): ?>
        <?php foreach ($wastage as $w): ?>
        <div class="waste-row">
          <div>
            <div class="item"><?= htmlspecialchars($w->item_name); ?></div>
            <div class="sub"><?= htmlspecialchars(Perfume_model::stage_label($w->stage)); ?> · <?= format_qty($w->qty); ?> <?= htmlspecialchars($w->unit_name ?: ''); ?> · <?= show_date($w->created_date); ?></div>
          </div>
          <div class="cost"><?= $CI->currency($w->total_cost); ?></div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">No losses logged yet.</div>
      <?php endif; ?>
    </section>
  </div>

  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
  <script>
    function closeAllMpSelects(){
      document.querySelectorAll('.mp-select-wrap.open').forEach(function(w){ w.classList.remove('open'); });
    }
    function initMpSelects(){
      document.querySelectorAll('select.mp-select').forEach(function(sel){
        if(sel.dataset.mpInit) return;
        sel.dataset.mpInit = '1';
        var wrap = document.createElement('div');
        wrap.className = 'mp-select-wrap';
        sel.parentNode.insertBefore(wrap, sel);
        wrap.appendChild(sel);
        var trigger = document.createElement('div');
        trigger.className = 'mp-select-trigger';
        wrap.appendChild(trigger);
        var list = document.createElement('div');
        list.className = 'mp-select-options';
        wrap.appendChild(list);
        var options = Array.from(sel.options);
        function renderOptions(){
          list.innerHTML = '';
          options.forEach(function(opt, idx){
            var div = document.createElement('div');
            div.className = 'mp-select-option';
            div.textContent = opt.textContent;
            if(sel.selectedIndex === idx) div.classList.add('active');
            div.addEventListener('click', function(e){
              e.stopPropagation();
              sel.selectedIndex = idx;
              updateTrigger();
              sel.dispatchEvent(new Event('change', {bubbles: true}));
              closeAllMpSelects();
            });
            list.appendChild(div);
          });
        }
        function updateTrigger(){
          var s = sel.options[sel.selectedIndex];
          trigger.textContent = s ? s.textContent : 'Select';
          trigger.classList.toggle('placeholder', !s || !s.value);
          renderOptions();
        }
        trigger.addEventListener('click', function(e){
          e.stopPropagation();
          closeAllMpSelects();
          wrap.classList.toggle('open');
        });
        sel.addEventListener('change', updateTrigger);
        updateTrigger();
      });
      document.addEventListener('click', closeAllMpSelects);
    }
    document.addEventListener('DOMContentLoaded', initMpSelects);
  </script>
</body>
</html>
