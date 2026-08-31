<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Approval Logs</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
    .form-row input, .form-row select { width: 100%; padding: 10px; border: 1px solid var(--mp-border); border-radius: 10px; font-size: 14px; }
    .btn { width: 100%; padding: 12px; border: none; border-radius: 12px; background: var(--mp-primary); color: #fff; font-size: 15px; font-weight: 700; }
    .log-card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .log-title { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
    .log-meta { font-size: 13px; color: var(--mp-muted); margin: 3px 0; }
    .badge { display: inline-block; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; }
    .badge.approved { background: #D1FAE5; color: #065F46; }
    .badge.rejected { background: #FEE2E2; color: #B91C1B; }
    .badge.pending { background: #FEF3C7; color: #B45309; }
    .empty-state { text-align: center; padding: 32px; color: var(--mp-muted); }
    .summary { text-align: center; padding: 12px; background: #E0E7FF; border-radius: 14px; margin-bottom: 14px; }
    .summary .value { font-size: 22px; font-weight: 700; color: var(--mp-primary); }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .bottom-nav { max-width: 100%; left: 0; right: 0; transform: none; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/reports'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Approval Logs</h1>
        </div>
      </div>

      <div class="summary">
        <div class="value"><?= number_format($total); ?></div>
        <div style="font-size:13px; color:var(--mp-muted);">Records</div>
      </div>

      <form method="get" class="card">
        <div class="form-row">
          <input type="date" name="date_from" value="<?= $this->input->get('date_from'); ?>" placeholder="From">
          <input type="date" name="date_to" value="<?= $this->input->get('date_to'); ?>" placeholder="To">
        </div>
        <div class="form-row">
          <input type="text" name="type" value="<?= $this->input->get('type'); ?>" placeholder="Type">
          <select name="status" class="mp-select">
            <option value="">All</option>
            <option value="Pending" <?= $this->input->get('status') == 'Pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="Approved" <?= $this->input->get('status') == 'Approved' ? 'selected' : ''; ?>>Approved</option>
            <option value="Rejected" <?= $this->input->get('status') == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
          </select>
        </div>
        <button type="submit" class="btn">Filter</button>
      </form>

      <div id="logList">
        <?php if(!empty($logs)): ?>
          <?php foreach($logs as $l):
            $status = $l->status ?? 'Pending';
            $badge = 'pending';
            if($status == 'Approved' || stripos($status, 'approve') !== false){ $badge = 'approved'; }
            elseif($status == 'Rejected' || stripos($status, 'reject') !== false){ $badge = 'rejected'; }
          ?>
            <div class="log-card">
              <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div class="log-title"><?= $l->approval_type ?? 'Approval'; ?></div>
                <span class="badge <?= $badge; ?>"><?= $status; ?></span>
              </div>
              <?php if(!empty($l->reference_no)): ?><div class="log-meta"><i class="fa fa-hashtag"></i> <?= $l->reference_no; ?></div><?php endif; ?>
              <?php if(!empty($l->amount) || isset($l->amount)): ?><div class="log-meta"><i class="fa fa-money"></i> <?= store_number_format($l->amount); ?></div><?php endif; ?>
              <div class="log-meta"><i class="fa fa-user"></i> Requested: <?= $l->requesting_user_name ?? 'Unknown'; ?></div>
              <?php if(!empty($l->approving_user_name)): ?><div class="log-meta"><i class="fa fa-check"></i> Approved by: <?= $l->approving_user_name; ?></div><?php endif; ?>
              <div class="log-meta"><i class="fa fa-calendar"></i> <?= !empty($l->created_at) ? show_date($l->created_at) : '-'; ?></div>
              <?php if(!empty($l->notes)): ?><div class="log-meta" style="margin-top:6px;"><?= $l->notes; ?></div><?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">No approval logs found.</div>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    // Convert native select to simple inline cap
    document.querySelectorAll('select.mp-select').forEach(function(sel){
      var wrap = document.createElement('div'); wrap.className = 'mp-select-wrap'; wrap.style.position='relative';
      sel.parentNode.insertBefore(wrap, sel);
      wrap.appendChild(sel);
      sel.style.display = 'none';
      var trigger = document.createElement('div');
      trigger.className = 'mp-select-trigger';
      trigger.style.cssText = 'padding:10px; border:1px solid var(--mp-border); border-radius:10px; background:#fff; font-size:14px;';
      var list = document.createElement('div');
      list.style.cssText = 'display:none; position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid var(--mp-border); border-radius:0 0 10px 10px; max-height:180px; overflow-y:auto; z-index:20;';
      wrap.appendChild(trigger); wrap.appendChild(list);
      function update(){
        var opt = sel.options[sel.selectedIndex];
        trigger.textContent = opt ? opt.text : 'Select';
        list.innerHTML = '';
        Array.from(sel.options).forEach(function(o, i){
          var d = document.createElement('div');
          d.textContent = o.text; d.style.padding = '10px'; d.style.cursor = 'pointer';
          if(i == sel.selectedIndex) d.style.background = '#F1F5F9';
          d.addEventListener('click', function(e){ e.stopPropagation(); sel.selectedIndex = i; update(); list.style.display='none'; });
          list.appendChild(d);
        });
      }
      trigger.addEventListener('click', function(e){ e.stopPropagation(); list.style.display = (list.style.display === 'block') ? 'none' : 'block'; });
      document.addEventListener('click', function(){ list.style.display = 'none'; });
      update();
      sel.addEventListener('change', update);
    });
  </script>
</body>
</html>
