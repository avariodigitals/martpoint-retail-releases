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
    .topbar h1 { font-size: 18px; font-weight: 700; margin: 0; }
    .hero { background: linear-gradient(135deg, var(--mp-primary), var(--mp-primary-dark)); border-radius: 16px; padding: 16px; color: #fff; margin-bottom: 12px; }
    .hero .code { font-size: 17px; font-weight: 700; }
    .hero .meta { display: flex; flex-wrap: wrap; gap: 12px; font-size: 12px; opacity: 0.9; margin-top: 8px; }
    .progress { height: 6px; background: rgba(255,255,255,0.3); border-radius: 3px; margin-top: 12px; overflow: hidden; }
    .progress .bar { height: 100%; background: #fff; }
    .status-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
    .st-btn { padding: 10px 14px; border-radius: 12px; border: 1px solid var(--mp-border); background: var(--mp-surface); font-size: 13px; font-weight: 600; cursor: pointer; flex: 1; min-width: 45%; }
    .st-btn.primary { background: var(--mp-primary); border-color: var(--mp-primary); color: #fff; }
    .st-btn.danger { color: var(--mp-danger); border-color: #FECACA; }
    .st-btn:disabled { opacity: 0.6; }
    .section-label { font-size: 12px; font-weight: 700; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.4px; margin: 14px 0 8px; }
    .stop { display: flex; gap: 12px; background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 10px; }
    .stop.done { opacity: 0.75; border-color: #A7F3D0; background: #F0FDF9; }
    .stop .seq { width: 30px; height: 30px; border-radius: 50%; background: var(--mp-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; flex-shrink: 0; }
    .stop.done .seq { background: var(--mp-success); }
    .stop .body { flex: 1; min-width: 0; }
    .stop .cust { font-size: 15px; font-weight: 700; }
    .stop .inv { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .stop .addr { font-size: 12px; color: var(--mp-muted); margin-top: 6px; display: flex; gap: 5px; }
    .stop .tel { font-size: 13px; color: var(--mp-primary); text-decoration: none; font-weight: 600; margin-top: 6px; display: inline-flex; align-items: center; gap: 5px; }
    .stop .act { display: flex; gap: 8px; margin-top: 10px; }
    .stop .act button { flex: 1; padding: 9px; border-radius: 10px; font-size: 12px; font-weight: 700; cursor: pointer; border: 1px solid var(--mp-border); background: var(--mp-bg); }
    .stop .act button.deliver { background: var(--mp-success); border-color: var(--mp-success); color: #fff; }
    .stop .act button.fail { color: var(--mp-danger); border-color: #FECACA; }
    .stop .delivered-tag { font-size: 12px; color: var(--mp-success); font-weight: 700; margin-top: 8px; }
    .actions { display: flex; gap: 10px; margin-top: 4px; }
    .btn { flex: 1; padding: 13px; border-radius: 14px; border: 1px solid var(--mp-border); background: var(--mp-bg); color: var(--mp-ink); font-size: 13px; font-weight: 700; text-align: center; text-decoration: none; cursor: pointer; }
    .btn.danger { color: var(--mp-danger); border-color: #FECACA; }
    .notes-block { background: var(--mp-bg); border-radius: 12px; padding: 12px; font-size: 13px; color: var(--mp-ink); margin-bottom: 12px; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); font-size: 14px; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 130px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 150px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/deliveries'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
      </div>

      <?php
        $total = count($schedule_items);
        $done = 0;
        foreach($schedule_items as $si){ if($si->delivery_status === 'delivered'){ $done++; } }
        $pct = $total > 0 ? round(($done / $total) * 100) : 0;
      ?>
      <div class="hero">
        <div class="code"><?= htmlspecialchars($schedule['schedule_code']); ?></div>
        <div class="meta">
          <span><i class="fa fa-calendar-o"></i> <?= htmlspecialchars($schedule['schedule_date']); ?></span>
          <span><i class="fa fa-user"></i> <?= htmlspecialchars($schedule['driver_name'] ?: 'Unassigned'); ?></span>
          <?php if(!empty($schedule['vehicle'])): ?><span><i class="fa fa-truck"></i> <?= htmlspecialchars($schedule['vehicle']); ?></span><?php endif; ?>
          <span><i class="fa fa-flag"></i> <?= ucwords(str_replace('_', ' ', $schedule['status'])); ?></span>
        </div>
        <div class="progress"><div class="bar" style="width:<?= $pct; ?>%"></div></div>
        <div class="meta" style="margin-top:6px;"><span><?= $done; ?>/<?= $total; ?> stops delivered</span></div>
      </div>

      <div class="status-actions">
        <?php if($schedule['status'] === 'planned'): ?>
        <button class="st-btn primary" data-st="ready"><i class="fa fa-check-circle"></i> Mark Ready</button>
        <?php endif; ?>
        <?php if(in_array($schedule['status'], ['planned','ready'])): ?>
        <button class="st-btn primary" data-st="out_for_delivery"><i class="fa fa-truck"></i> Out for Delivery</button>
        <?php endif; ?>
        <?php if($schedule['status'] === 'out_for_delivery'): ?>
        <button class="st-btn primary" data-st="completed"><i class="fa fa-flag-checkered"></i> Complete Route</button>
        <?php endif; ?>
        <?php if($schedule['status'] !== 'completed' && $schedule['status'] !== 'cancelled'): ?>
        <button class="st-btn danger" data-st="cancelled"><i class="fa fa-times"></i> Cancel Route</button>
        <?php endif; ?>
      </div>

      <?php if(!empty($schedule['notes'])): ?><div class="notes-block"><i class="fa fa-sticky-note-o"></i> <?= nl2br(htmlspecialchars($schedule['notes'])); ?></div><?php endif; ?>

      <div class="section-label">Stops</div>
      <?php if(!empty($schedule_items)): ?>
        <?php foreach($schedule_items as $si): $isDone = ($si->delivery_status === 'delivered'); ?>
        <div class="stop <?= $isDone ? 'done' : ''; ?>">
          <div class="seq"><?= $isDone ? '<i class="fa fa-check"></i>' : (int)$si->delivery_sequence; ?></div>
          <div class="body">
            <div class="cust"><?= htmlspecialchars($si->customer_name ?: 'Walk-in'); ?></div>
            <div class="inv">Invoice #<?= htmlspecialchars($si->sales_code); ?> · <?= ucwords(str_replace('_', ' ', $si->delivery_status)); ?></div>
            <?php if(!empty($si->address)): ?><div class="addr"><i class="fa fa-map-marker"></i> <?= htmlspecialchars($si->address); ?></div><?php endif; ?>
            <?php if(!empty($si->phone)): ?><a class="tel" href="tel:<?= htmlspecialchars($si->phone); ?>"><i class="fa fa-phone"></i> <?= htmlspecialchars($si->phone); ?></a><?php endif; ?>
            <?php if($isDone): ?>
              <div class="delivered-tag"><i class="fa fa-check-circle"></i> Delivered<?= !empty($si->delivery_notes) ? ' — '.htmlspecialchars($si->delivery_notes) : ''; ?></div>
            <?php else: ?>
              <div class="act">
                <button class="deliver" data-item="<?= (int)$si->id; ?>" data-status="delivered"><i class="fa fa-check"></i> Delivered</button>
                <button class="fail" data-item="<?= (int)$si->id; ?>" data-status="failed"><i class="fa fa-times"></i> Failed</button>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state"><div>No stops on this route yet — edit the schedule to add sales.</div></div>
      <?php endif; ?>

      <div class="actions">
        <a href="<?= base_url('mobile/delivery_form/'.$schedule['q_id']); ?>" class="btn"><i class="fa fa-pencil"></i> Edit</a>
        <button class="btn danger" id="deleteBtn"><i class="fa fa-trash"></i> Delete</button>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var base_url = '<?= base_url(); ?>';
    var csrf_token = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrf_hash = '<?= $this->security->get_csrf_hash(); ?>';
    var schedule_id = <?= (int)$schedule['q_id']; ?>;

    function post(url, fields, cb){
      var fd = new FormData();
      for(var k in fields){ fd.append(k, fields[k]); }
      fd.append(csrf_token, csrf_hash);
      mpFetchJson(base_url + url, { method: 'POST', body: fd })
        .then(function(d){ if(d && d.csrf_hash){ csrf_hash = d.csrf_hash; } cb(d); })
        .catch(function(err){ cb({success:false, message: mpErrorText(err)}); });
    }

    document.querySelectorAll('.st-btn').forEach(function(btn){
      btn.addEventListener('click', function(){
        var st = this.getAttribute('data-st');
        if(st === 'cancelled' && !confirm('Cancel this route?')){ return; }
        var b = this; b.disabled = true;
        post('mobile/delivery_status', { id: schedule_id, status: st }, function(d){
          if(d && d.success){ window.location.reload(); }
          else { mpError(d && d.message ? d.message : 'The status could not be updated. Please try again.'); b.disabled = false; }
        });
      });
    });

    document.querySelectorAll('.stop .act button').forEach(function(btn){
      btn.addEventListener('click', function(){
        var itemId = this.getAttribute('data-item');
        var st = this.getAttribute('data-status');
        var notes = '';
        if(st === 'failed'){ notes = prompt('Reason for failed delivery? (optional)') || ''; }
        var b = this; b.disabled = true;
        post('mobile/delivery_item_status', { item_id: itemId, status: st, notes: notes }, function(d){
          if(d && d.success){ window.location.reload(); }
          else { mpError(d && d.message ? d.message : 'The status could not be updated. Please try again.'); b.disabled = false; }
        });
      });
    });

    document.getElementById('deleteBtn').addEventListener('click', function(){
      if(!confirm('Delete this delivery schedule?')){ return; }
      post('mobile/delivery_delete', { id: schedule_id }, function(d){
        if(d && d.success){ window.location.href = base_url + 'mobile/deliveries'; }
        else { mpError(d && d.message ? d.message : 'The delivery schedule could not be deleted. Please try again.'); }
      });
    });
  </script>
</body>
</html>
