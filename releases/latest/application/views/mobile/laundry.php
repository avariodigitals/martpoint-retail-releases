<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='utf-8'>
  <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate'>
  <meta http-equiv='Pragma' content='no-cache'>
  <meta http-equiv='Expires' content='0'>
  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'>
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title); ?></title>
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
    .status-tabs { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 16px; }
    .tab-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px 4px; border-radius: 12px; border: 1px solid var(--mp-border); background: var(--mp-surface); color: var(--mp-ink); font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none; }
    .tab-btn .count { font-size: 20px; font-weight: 700; margin-bottom: 2px; }
    .tab-btn.dropped_off { color: var(--mp-danger); border-color: rgba(239,68,68,0.2); }
    .tab-btn.washing { color: #1D4ED8; border-color: rgba(29,78,216,0.2); }
    .tab-btn.ironing { color: #B45309; border-color: rgba(245,158,11,0.2); }
    .tab-btn.ready { color: var(--mp-success); border-color: rgba(16,185,129,0.2); }
    .tab-btn.active.dropped_off { background: rgba(239,68,68,0.1); }
    .tab-btn.active.washing { background: rgba(29,78,216,0.1); }
    .tab-btn.active.ironing { background: rgba(245,158,11,0.1); }
    .tab-btn.active.ready { background: rgba(16,185,129,0.1); }
    .order-card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .order-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px; }
    .order-code { font-size: 16px; font-weight: 700; }
    .order-meta { font-size: 12px; color: var(--mp-muted); }
    .order-timer { font-size: 13px; font-weight: 700; font-family: 'SF Mono', 'Courier New', monospace; color: var(--mp-danger); margin-bottom: 8px; }
    .item-list { margin: 8px 0; }
    .item-row { display: flex; align-items: center; gap: 8px; padding: 6px 0; border-bottom: 1px dashed var(--mp-border); font-size: 14px; }
    .item-row:last-child { border-bottom: none; }
    .item-qty { background: var(--mp-bg); color: var(--mp-ink); font-weight: 700; padding: 2px 8px; border-radius: 4px; font-size: 12px; min-width: 26px; text-align: center; }
    .item-svc { margin-left: auto; font-size: 11px; color: var(--mp-muted); }
    .item-status { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 10px; background: var(--mp-bg); color: var(--mp-muted); }
    .item-status.completed { background: #D1FAE5; color: #047857; }
    .action-btn { width: 100%; padding: 12px; border: none; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 6px; }
    .action-btn.wash { background: #2563EB; color: #fff; }
    .action-btn.iron { background: var(--mp-warning); color: #212529; }
    .action-btn.ready { background: var(--mp-success); color: #fff; }
    .action-btn.collect { background: var(--mp-ink); color: #fff; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    .collected-section { margin-top: 24px; }
    .collected-section h3 { font-size: 14px; font-weight: 700; margin: 0 0 12px; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.4px; }
    .collected-chip { display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; background: var(--mp-bg); border: 1px solid var(--mp-border); border-radius: 20px; font-size: 12px; margin: 0 6px 8px 0; }
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
        <a href='?status=dropped_off' class='tab-btn dropped_off <?= ($active_status === 'dropped_off') ? 'active' : ''; ?>'>
          <span class='count' id='count_dropped_off'><?= (int)($status_counts['dropped_off'] ?? 0); ?></span>
          Dropped
        </a>
        <a href='?status=washing' class='tab-btn washing <?= ($active_status === 'washing') ? 'active' : ''; ?>'>
          <span class='count' id='count_washing'><?= (int)($status_counts['washing'] ?? 0); ?></span>
          Washing
        </a>
        <a href='?status=ironing' class='tab-btn ironing <?= ($active_status === 'ironing') ? 'active' : ''; ?>'>
          <span class='count' id='count_ironing'><?= (int)($status_counts['ironing'] ?? 0); ?></span>
          Ironing
        </a>
        <a href='?status=ready' class='tab-btn ready <?= ($active_status === 'ready') ? 'active' : ''; ?>'>
          <span class='count' id='count_ready'><?= (int)($status_counts['ready'] ?? 0); ?></span>
          Ready
        </a>
      </div>

      <?php if(!empty($orders)): ?>
        <?php foreach($orders as $o):
          $sum = $o->item_summary;
          $has_washing = $lw_stages['has_washing'];
          $has_ironing = $lw_stages['has_ironing'];
        ?>
        <div class='order-card'>
          <div class='order-header'>
            <div>
              <div class='order-code'>#<?= htmlspecialchars($o->sales_code); ?><?= !empty($o->tag_number) ? ' · '.htmlspecialchars($o->tag_number) : ''; ?></div>
              <div class='order-meta'><i class='fa fa-user'></i> <?= htmlspecialchars($o->customer_name ?: 'Walk-in'); ?> · <?= htmlspecialchars($o->drop_off_time); ?></div>
            </div>
          </div>
          <div class='order-timer' data-elapsed='<?= (int)$o->elapsed_seconds; ?>'>00:00</div>
          <div class='item-list'>
            <?php foreach($o->items as $itm): ?>
            <div class='item-row'>
              <span class='item-qty'><?= (int)$itm->sales_qty; ?></span>
              <span><?= htmlspecialchars($itm->item_name); ?></span>
              <span class='item-status <?= $itm->item_status === 'completed' ? 'completed' : ''; ?>'><?= ucwords(str_replace('_',' ',$itm->item_status)); ?></span>
              <?php if(!empty($itm->service_type)): ?><span class='item-svc'><?= ucwords(str_replace('_',' ',$itm->service_type)); ?></span><?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <?php
            // Determine the single next action for this order
            if($has_washing && !empty($sum['can_start_washing'])){
              echo '<button class="action-btn wash" data-action="start_washing" data-lid="'.(int)$o->laundry_order_id.'"><i class="fa fa-tint"></i> Start Washing</button>';
            } elseif($has_washing && !empty($sum['can_finish_washing'])){
              echo '<button class="action-btn wash" data-action="finish_washing" data-lid="'.(int)$o->laundry_order_id.'"><i class="fa fa-check-circle"></i> Finish Washing</button>';
            } elseif($has_ironing && !empty($sum['can_start_ironing'])){
              echo '<button class="action-btn iron" data-action="start_ironing" data-lid="'.(int)$o->laundry_order_id.'"><i class="fa fa-fire"></i> Start Ironing</button>';
            } elseif($has_ironing && !empty($sum['can_finish_ironing'])){
              echo '<button class="action-btn iron" data-action="finish_ironing" data-lid="'.(int)$o->laundry_order_id.'"><i class="fa fa-check-circle"></i> Finish Ironing</button>';
            } elseif(!empty($sum['all_completed']) || ($active_status === 'washing' && empty($sum['washing']) && empty($sum['ironing'])) || ($active_status === 'ironing' && empty($sum['ironing'])) || ($active_status === 'dropped_off' && !$has_washing && !$has_ironing)){
              if($active_status === 'ready'){
                echo '<button class="action-btn collect" data-action="collected" data-lid="'.(int)$o->laundry_order_id.'"><i class="fa fa-hand-o-right"></i> Collected</button>';
              } else {
                echo '<button class="action-btn ready" data-action="mark_ready" data-lid="'.(int)$o->laundry_order_id.'"><i class="fa fa-flag-checkered"></i> Mark Ready</button>';
              }
            } elseif($active_status === 'ready'){
              echo '<button class="action-btn collect" data-action="collected" data-lid="'.(int)$o->laundry_order_id.'"><i class="fa fa-hand-o-right"></i> Collected</button>';
            }
          ?>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class='empty-state'>
          <i class='fa fa-check-circle'></i>
          <div>No <?= str_replace('_', ' ', $active_status); ?> orders</div>
        </div>
      <?php endif; ?>

      <?php if(!empty($collected)): ?>
      <div class='collected-section'>
        <h3><i class='fa fa-history'></i> Recently Collected</h3>
        <?php foreach(array_slice($collected, 0, 12) as $sv): ?>
        <div class='collected-chip'>
          <i class='fa fa-check-circle' style='color:var(--mp-success)'></i>
          #<?= htmlspecialchars($sv->sales_code); ?>
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
        var h = Math.floor(elapsed / 3600);
        var m = Math.floor((elapsed % 3600) / 60);
        var s = elapsed % 60;
        el.textContent = (h > 0 ? h + 'h ' : '') + (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
      });
    }
    setInterval(updateTimers, 1000);
    updateTimers();

    document.querySelectorAll('.action-btn').forEach(function(btn){
      btn.addEventListener('click', function(){
        var lid = this.getAttribute('data-lid');
        var action = this.getAttribute('data-action');
        if(!lid || !action) return;
        this.disabled = true;
        this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Updating...';

        var fd = new FormData();
        fd.append('laundry_order_id', lid);
        fd.append('action', action);
        fd.append(csrf_token, csrf_hash);

        fetch(base_url + 'mobile/laundry_update_status', { method: 'POST', body: fd })
        .then(function(res){ return res.json(); })
        .then(function(data){
          if(data && data.csrf_hash){ csrf_hash = data.csrf_hash; }
          if(data && data.success){ window.location.reload(); }
          else { alert('Update failed.'); btn.disabled = false; btn.innerHTML = 'Try Again'; }
        })
        .catch(function(){ alert('Network error. Try again.'); btn.disabled = false; btn.innerHTML = 'Try Again'; });
      });
    });

    setInterval(function(){
      fetch(base_url + 'mobile/laundry?ajax=1')
        .then(function(res){ return res.json(); })
        .then(function(data){
          if(data && data.status_counts){
            ['dropped_off','washing','ironing','ready'].forEach(function(k){
              var el = document.getElementById('count_' + k);
              if(el) el.textContent = data.status_counts[k] || 0;
            });
          }
        });
    }, 15000);
  </script>
</body>
</html>
