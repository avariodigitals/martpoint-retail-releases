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
    .hero { background: linear-gradient(135deg, var(--mp-primary), var(--mp-primary-dark)); border-radius: 16px; padding: 18px; color: #fff; margin-bottom: 12px; }
    .hero .lbl { font-size: 12px; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.4px; }
    .hero .amt { font-size: 28px; font-weight: 700; margin-top: 4px; }
    .hero .range { font-size: 12px; opacity: 0.85; margin-top: 4px; }
    .filters { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 12px; margin-bottom: 14px; }
    .filters .grid3 { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .filters input[type=date] { width: 100%; padding: 10px 12px; border: 1px solid var(--mp-border); border-radius: 10px; font-family: inherit; font-size: 13px; }
    .filters .apply { grid-column: 1 / -1; padding: 11px; border: none; border-radius: 10px; background: var(--mp-ink); color: #fff; font-weight: 700; cursor: pointer; }
    .mp-select { display: none; }
    .mp-select-wrap { position: relative; grid-column: 1 / -1; }
    .mp-select-trigger { display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border: 1px solid var(--mp-border); border-radius: 10px; background: #fff; font-size: 13px; cursor: pointer; }
    .mp-select-options { display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 200; background: #fff; border: 1px solid var(--mp-border); border-radius: 10px; max-height: 200px; overflow-y: auto; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .mp-select-options.open { display: block; }
    .mp-option { padding: 11px 12px; border-bottom: 1px solid var(--mp-border); cursor: pointer; font-size: 13px; }
    .mp-option.active { background: #E0E7FF; color: var(--mp-primary); font-weight: 600; }
    .section-label { font-size: 12px; font-weight: 700; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.4px; margin: 14px 0 8px; }
    .sum-card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center; }
    .sum-card .who { font-size: 14px; font-weight: 700; }
    .sum-card .sub { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .sum-card .amt { font-size: 16px; font-weight: 700; color: var(--mp-primary); }
    .tx-card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 12px; padding: 12px 14px; margin-bottom: 8px; }
    .tx-card .top { display: flex; justify-content: space-between; align-items: flex-start; }
    .tx-card .inv { font-size: 13px; font-weight: 700; }
    .tx-card .date { font-size: 11px; color: var(--mp-muted); }
    .tx-card .item { font-size: 13px; margin-top: 6px; color: var(--mp-ink); }
    .tx-card .foot { display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; }
    .tx-card .who { color: var(--mp-muted); }
    .tx-card .comm { font-weight: 700; color: var(--mp-success); }
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

      <div class="hero">
        <div class="lbl">Total Commission</div>
        <div class="amt"><?= $this->currency($grand_total); ?></div>
        <div class="range"><?= show_date($from_date); ?> — <?= show_date($to_date); ?><?= $selected_staff_id ? ' · filtered' : ''; ?></div>
      </div>

      <form class="filters" method="get" action="<?= base_url('mobile/staff_commission'); ?>">
        <div class="grid3">
          <select name="staff_id" class="mp-select">
            <option value="">All <?= htmlspecialchars(strtolower($staff_label)); ?></option>
            <?php foreach($staff_list as $u): ?>
            <option value="<?= (int)$u->id; ?>" <?= ($selected_staff_id == $u->id) ? 'selected' : ''; ?>><?= htmlspecialchars(trim($u->first_name.' '.$u->last_name) ?: $u->username); ?></option>
            <?php endforeach; ?>
          </select>
          <input type="date" name="from_date" value="<?= htmlspecialchars($from_date); ?>">
          <input type="date" name="to_date" value="<?= htmlspecialchars($to_date); ?>">
          <button type="submit" class="apply"><i class="fa fa-filter"></i> Apply</button>
        </div>
      </form>

      <?php if(!empty($summary)): ?>
      <div class="section-label">By <?= htmlspecialchars($staff_label); ?></div>
      <?php foreach($summary as $s): ?>
      <div class="sum-card">
        <div>
          <div class="who"><?= htmlspecialchars($s->staff_name ?: 'Unknown'); ?></div>
          <div class="sub"><?= (int)$s->invoice_count; ?> invoice<?= $s->invoice_count == 1 ? '' : 's'; ?> · <?= store_number_format($s->total_qty); ?> units</div>
        </div>
        <div class="amt"><?= $this->currency($s->total_commission); ?></div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>

      <div class="section-label">Transactions</div>
      <?php if(!empty($commissions)): ?>
        <?php foreach($commissions as $c): ?>
        <div class="tx-card">
          <div class="top">
            <span class="inv">#<?= htmlspecialchars($c->sales_code ?: $c->sales_id); ?></span>
            <span class="date"><?= show_date($c->sales_date); ?></span>
          </div>
          <div class="item"><?= htmlspecialchars($c->item_name ?: 'Item'); ?> × <?= store_number_format($c->sales_qty); ?></div>
          <div class="foot">
            <span class="who"><i class="fa fa-user"></i> <?= htmlspecialchars($c->staff_name ?: '-'); ?></span>
            <span class="comm">+<?= $this->currency($c->commission_amount); ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <i class="fa fa-percent"></i>
          <div>No commission entries for this period.</div>
        </div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    document.querySelectorAll('select.mp-select').forEach(function(sel){
      var wrap = document.createElement('div'); wrap.className = 'mp-select-wrap';
      sel.parentNode.insertBefore(wrap, sel); wrap.appendChild(sel);
      var trigger = document.createElement('div'); trigger.className = 'mp-select-trigger';
      var opts = document.createElement('div'); opts.className = 'mp-select-options';
      var label = document.createElement('span');
      var icon = document.createElement('i'); icon.className = 'fa fa-chevron-down'; icon.style.fontSize = '12px';
      trigger.appendChild(label); trigger.appendChild(icon);
      wrap.appendChild(trigger); wrap.appendChild(opts);
      function setLabel(){ label.textContent = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : 'Select'; }
      Array.from(sel.options).forEach(function(opt, idx){
        var d = document.createElement('div'); d.className = 'mp-option'; d.textContent = opt.text;
        if(idx === sel.selectedIndex) d.classList.add('active');
        d.addEventListener('click', function(e){
          e.stopPropagation(); sel.selectedIndex = idx; setLabel();
          opts.querySelectorAll('.mp-option').forEach(function(o){ o.classList.remove('active'); });
          d.classList.add('active'); opts.classList.remove('open');
        });
        opts.appendChild(d);
      });
      setLabel();
      trigger.addEventListener('click', function(e){
        e.stopPropagation();
        document.querySelectorAll('.mp-select-options.open').forEach(function(o){ if(o !== opts) o.classList.remove('open'); });
        opts.classList.toggle('open');
      });
    });
    document.addEventListener('click', function(){ document.querySelectorAll('.mp-select-options.open').forEach(function(o){ o.classList.remove('open'); }); });
  </script>
</body>
</html>
