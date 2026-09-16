<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='utf-8'>
  <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate'>
  <meta http-equiv='Pragma' content='no-cache'>
  <meta http-equiv='Expires' content='0'>
  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'>
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Kitchen</title>
  <link rel='preconnect' href='https://fonts.googleapis.com'>
  <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap' rel='stylesheet'>
  <link rel='stylesheet' href='<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css'>
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .status-tabs { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 16px; }
    .tab-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px 4px; border-radius: 12px; border: 1px solid var(--mp-border); background: var(--mp-surface); color: var(--mp-ink); font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; }
    .tab-btn .count { font-size: 20px; font-weight: 700; margin-bottom: 2px; }
    .tab-btn.new { color: var(--mp-danger); border-color: rgba(239,68,68,0.2); }
    .tab-btn.preparing { color: #B45309; border-color: rgba(245,158,11,0.2); }
    .tab-btn.ready { color: var(--mp-success); border-color: rgba(16,185,129,0.2); }
    .tab-btn.active.new { background: rgba(239,68,68,0.1); }
    .tab-btn.active.preparing { background: rgba(245,158,11,0.1); }
    .tab-btn.active.ready { background: rgba(16,185,129,0.1); }
    .order-card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .order-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
    .order-code { font-size: 16px; font-weight: 700; }
    .order-meta { font-size: 12px; color: var(--mp-muted); }
    .order-timer { font-size: 14px; font-weight: 700; font-family: 'SF Mono', 'Courier New', monospace; color: var(--mp-danger); margin-bottom: 10px; }
    .item-list { margin: 10px 0; }
    .item-row { display: flex; align-items: center; gap: 8px; padding: 6px 0; border-bottom: 1px dashed var(--mp-border); font-size: 14px; }
    .item-row:last-child { border-bottom: none; }
    .item-qty { background: var(--mp-bg); color: var(--mp-ink); font-weight: 700; padding: 2px 8px; border-radius: 4px; font-size: 12px; min-width: 26px; text-align: center; }
    .action-btn { width: 100%; padding: 12px; border: none; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 6px; }
    .action-btn.new { background: var(--mp-danger); color: #fff; }
    .action-btn.preparing { background: var(--mp-warning); color: #212529; }
    .action-btn.ready { background: var(--mp-success); color: #fff; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    .served-section { margin-top: 24px; }
    .served-section h3 { font-size: 14px; font-weight: 700; margin: 0 0 12px; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.4px; }
    .served-chip { display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; background: var(--mp-bg); border: 1px solid var(--mp-border); border-radius: 20px; font-size: 12px; margin: 0 6px 8px 0; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 140px; } }
  </style>
</head>
<body>
  <div id='app'>
    <section class='screen'>
      <div class='topbar'>
        <a href='<?= base_url('mobile/operations'); ?>' class='back'><i class='fa fa-chevron-left'></i></a>
        <div class='topbar-titles'>
          <div class='store-name'><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= $page_title; ?></h1>
        </div>
      </div>

      <div class='status-tabs'>
        <a href='?status=new' class='tab-btn new <?= ($active_status === 'new') ? 'active' : ''; ?>'>
          <span class='count' id='count_new'><?= (int)$status_counts['new']; ?></span>
          New
        </a>
        <a href='?status=preparing' class='tab-btn preparing <?= ($active_status === 'preparing') ? 'active' : ''; ?>'>
          <span class='count' id='count_preparing'><?= (int)$status_counts['preparing']; ?></span>
          Preparing
        </a>
        <a href='?status=ready' class='tab-btn ready <?= ($active_status === 'ready') ? 'active' : ''; ?>'>
          <span class='count' id='count_ready'><?= (int)$status_counts['ready']; ?></span>
          Ready
        </a>
      </div>

      <?php if(!empty($orders)): ?>
        <?php foreach($orders as $o): ?>
        <div class='order-card' data-kid='<?= (int)$o->kitchen_order_id; ?>' data-elapsed='<?= (int)$o->elapsed_seconds; ?>' data-status='<?= htmlspecialchars($active_status); ?>'>
          <div class='order-header'>
            <div>
              <div class='order-code'>#<?= htmlspecialchars($o->sales_code); ?></div>
              <div class='order-meta'><i class='fa fa-user'></i> <?= htmlspecialchars($o->customer_name ?: 'Walk-in'); ?><?php if(!empty($o->table_name)): ?> · <i class='fa fa-table'></i> <?= htmlspecialchars($o->table_name); ?><?php endif; ?></div>
            </div>
          </div>
          <div class='order-timer' data-elapsed='<?= (int)$o->elapsed_seconds; ?>'>00:00</div>
          <div class='item-list'>
            <?php foreach($o->items as $itm): ?>
            <div class='item-row'>
              <span class='item-qty'><?= (int)$itm->sales_qty; ?></span>
              <span class='item-name'><?= htmlspecialchars($itm->item_name); ?></span>
            </div>
            <?php endforeach; ?>
          </div>
          <?php if($active_status === 'new'): ?>
          <button class='action-btn new' data-next='preparing' data-kid='<?= (int)$o->kitchen_order_id; ?>'><i class='fa fa-fire'></i> Start Preparing</button>
          <?php elseif($active_status === 'preparing'): ?>
          <button class='action-btn preparing' data-next='ready' data-kid='<?= (int)$o->kitchen_order_id; ?>'><i class='fa fa-check-circle'></i> Mark Ready</button>
          <?php elseif($active_status === 'ready'): ?>
          <button class='action-btn ready' data-next='served' data-kid='<?= (int)$o->kitchen_order_id; ?>'><i class='fa fa-hand-o-right'></i> Served</button>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class='empty-state'>
          <i class='fa fa-check-circle'></i>
          <div>No <?= $active_status; ?> orders</div>
        </div>
      <?php endif; ?>

      <?php if(!empty($served)): ?>
      <div class='served-section'>
        <h3><i class='fa fa-history'></i> Recently Served</h3>
        <?php foreach(array_slice($served, 0, 12) as $sv): ?>
        <div class='served-chip'>
          <i class='fa fa-check-circle' style='color:var(--mp-success)'></i>
          #<?= htmlspecialchars($sv->sales_code); ?>
          <span style='color:var(--mp-muted)'>· <?= date('H:i', strtotime($sv->updated_at)); ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var base_url = '<?= base_url(); ?>';
    var csrf_token = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrf_hash = '<?= $this->security->get_csrf_hash(); ?>';

    function updateTimers() {
      document.querySelectorAll('.order-timer').forEach(function(el){
        var elapsed = parseInt(el.getAttribute('data-elapsed')) || 0;
        elapsed++;
        el.setAttribute('data-elapsed', elapsed);
        var m = Math.floor(elapsed / 60);
        var s = elapsed % 60;
        el.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
      });
    }
    setInterval(updateTimers, 1000);
    updateTimers();

    document.querySelectorAll('.action-btn').forEach(function(btn){
      btn.addEventListener('click', function(){
        var kid = this.getAttribute('data-kid');
        var next = this.getAttribute('data-next');
        if(!kid || !next) return;
        this.disabled = true;
        this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Updating...';

        var fd = new FormData();
        fd.append('kitchen_order_id', kid);
        fd.append('status', next);
        fd.append(csrf_token, csrf_hash);

        fetch(base_url + 'mobile/kitchen_update_status', {
          method: 'POST',
          body: fd
        })
        .then(function(res){ return res.json(); })
        .then(function(data){
          if(data && data.success){
            window.location.reload();
          } else {
            alert(data && data.message ? data.message : 'Update failed.');
            btn.disabled = false;
            btn.innerHTML = 'Try Again';
          }
        })
        .catch(function(){
          alert('Network error. Try again.');
          btn.disabled = false;
          btn.innerHTML = 'Try Again';
        });
      });
    });

    setInterval(function(){
      fetch(base_url + 'mobile/kitchen?ajax=1')
        .then(function(res){ return res.json(); })
        .then(function(data){
          if(data && data.status_counts){
            document.getElementById('count_new').textContent = data.status_counts.new || 0;
            document.getElementById('count_preparing').textContent = data.status_counts.preparing || 0;
            document.getElementById('count_ready').textContent = data.status_counts.ready || 0;
          }
        });
    }, 15000);
  </script>
</body>
</html>
