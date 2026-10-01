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
    .week-nav { display: flex; align-items: center; justify-content: space-between; background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 10px 12px; margin-bottom: 14px; }
    .week-nav a { color: var(--mp-primary); text-decoration: none; font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; gap: 5px; padding: 6px 10px; }
    .week-nav .range { font-size: 13px; font-weight: 700; }
    .day-group { margin-bottom: 16px; }
    .day-label { font-size: 12px; font-weight: 700; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 8px; }
    .card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .card .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .nm { font-size: 15px; font-weight: 700; }
    .sub { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-default { background: #E2E8F0; color: #475569; }
    .badge-info { background: #DBEAFE; color: #1D4ED8; }
    .badge-warning { background: #FEF3C7; color: #B45309; }
    .badge-primary { background: #E0E7FF; color: #4338CA; }
    .badge-success { background: #D1FAE5; color: #047857; }
    .badge-danger { background: #FEE2E2; color: #B91C1C; }
    .st-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
    .st-btn { padding: 9px 14px; border-radius: 10px; border: 1px solid var(--mp-border); background: var(--mp-bg); font-size: 12px; font-weight: 700; cursor: pointer; }
    .st-btn.primary { background: var(--mp-primary); border-color: var(--mp-primary); color: #fff; }
    .st-btn:disabled { opacity: 0.6; }
    .pending-card { border-left: 3px solid var(--mp-warning); }
    .pending-card .nm { font-size: 14px; }
    .pending-meta { display: flex; gap: 12px; margin-top: 6px; font-size: 12px; color: var(--mp-muted); }
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

      <div class="week-nav">
        <a href="?from=<?= $prev_from; ?>&to=<?= $prev_to; ?>"><i class="fa fa-chevron-left"></i> Prev</a>
        <span class="range"><?= show_date($date_from); ?> — <?= show_date($date_to); ?></span>
        <a href="?from=<?= $next_from; ?>&to=<?= $next_to; ?>">Next <i class="fa fa-chevron-right"></i></a>
      </div>

      <?php
        $by_day = [];
        foreach($batches as $b){ $by_day[$b->scheduled_date][] = $b; }
      ?>
      <?php if(!empty($by_day)): ?>
        <?php foreach($by_day as $day => $day_batches): ?>
        <div class="day-group">
          <div class="day-label"><?= date('l', strtotime($day)); ?> · <?= show_date($day); ?></div>
          <?php foreach($day_batches as $b):
            $badge = Production_batches_model::status_badge($b->status);
            $batch_statuses = Production_batches_model::get_statuses(null, $b->batch_type ?? null);
          ?>
          <div class="card">
            <div class="top">
              <div>
                <div class="nm"><?= htmlspecialchars($b->batch_code ?? 'Batch #'.$b->id); ?></div>
                <div class="sub"><?= !empty($b->scheduled_time) ? date('g:i A', strtotime($b->scheduled_time)) : 'Anytime'; ?><?= !empty($b->first_name) ? ' · '.htmlspecialchars(trim($b->first_name.' '.$b->last_name)) : ''; ?></div>
              </div>
              <span class="badge badge-<?= $badge; ?>"><?= htmlspecialchars(Production_batches_model::status_label($b->status)); ?></span>
            </div>
            <?php if($b->status !== 'completed' && $b->status !== 'cancelled'): ?>
            <div class="st-actions">
              <?php foreach($batch_statuses as $bs):
                if($bs === $b->status || $bs === 'cancelled') continue;
                // offer forward steps + (for editors) backward steps
                $forward = array_search($bs, $batch_statuses) > array_search($b->status, $batch_statuses);
                if(!$forward && !$can_edit) continue;
              ?>
              <button class="st-btn <?= $forward ? 'primary' : ''; ?>" data-id="<?= (int)$b->id; ?>" data-status="<?= $bs; ?>" data-current="<?= htmlspecialchars($b->status); ?>"><?= htmlspecialchars(Production_batches_model::status_label($bs)); ?></button>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <i class="fa fa-industry"></i>
          <div>No production batches scheduled for this week.</div>
        </div>
      <?php endif; ?>

      <?php if(!empty($pending_items)): ?>
      <div class="day-group" style="margin-top:20px;">
        <div class="day-label"><i class="fa fa-clock-o"></i> Pending Custom Orders (unscheduled)</div>
        <?php foreach(array_slice($pending_items, 0, 20) as $p): ?>
        <div class="card pending-card">
          <div class="nm"><?= htmlspecialchars($p->item_name ?: 'Item'); ?></div>
          <div class="pending-meta">
            <span><i class="fa fa-user"></i> <?= htmlspecialchars($p->customer_name ?: 'Walk-in'); ?></span>
            <?php if(!empty($p->due_date)): ?><span><i class="fa fa-calendar"></i> Due <?= show_date($p->due_date); ?></span><?php endif; ?>
            <span class="badge badge-<?= Custom_orders_model::status_badge($p->status); ?>"><?= htmlspecialchars(Custom_orders_model::status_label($p->status)); ?></span>
          </div>
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

    document.querySelectorAll('.st-btn').forEach(function(btn){
      btn.addEventListener('click', function(){
        var id = this.getAttribute('data-id');
        var st = this.getAttribute('data-status');
        var cur = this.getAttribute('data-current');
        if(st === 'completed' && !confirm('Mark this batch complete? Stock will be posted to inventory.')){ return; }
        var b = this; b.disabled = true;

        var fd = new FormData();
        fd.append('id', id); fd.append('status', st); fd.append('current_status', cur);
        fd.append(csrf_token, csrf_hash);
        mpFetchJson(base_url + 'mobile/production_status', { method: 'POST', body: fd })
          .then(function(d){
            if(d && d.csrf_hash){ csrf_hash = d.csrf_hash; }
            if(d && d.success){ window.location.reload(); }
            else { mpError(d && d.message ? d.message : 'The status could not be updated. Please try again.'); b.disabled = false; }
          })
          .catch(function(err){ mpError(mpErrorText(err)); b.disabled = false; });
      });
    });
  </script>
</body>
</html>
