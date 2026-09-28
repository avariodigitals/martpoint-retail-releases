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
    .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-default { background: #E2E8F0; color: #475569; }
    .badge-info { background: #DBEAFE; color: #1D4ED8; }
    .badge-warning { background: #FEF3C7; color: #B45309; }
    .badge-primary { background: #E0E7FF; color: #4338CA; }
    .badge-success { background: #D1FAE5; color: #047857; }
    .badge-danger { background: #FEE2E2; color: #B91C1C; }
    .badge-muted { background: #F1F5F9; color: #94A3B8; }
    .hero { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 14px; }
    .hero .nm { font-size: 16px; font-weight: 700; }
    .hero .sub { font-size: 12px; color: var(--mp-muted); margin-top: 4px; }
    .hero .kv { display: flex; gap: 16px; margin-top: 10px; flex-wrap: wrap; }
    .hero .kv div { font-size: 12px; color: var(--mp-muted); }
    .hero .kv b { display: block; font-size: 14px; color: var(--mp-ink); }
    .section-label { font-size: 12px; font-weight: 700; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.4px; margin: 18px 0 8px; }
    .stage { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 10px; }
    .stage.done { border-left: 3px solid var(--mp-success); }
    .stage.in_progress { border-left: 3px solid var(--mp-primary); }
    .stage.skipped { opacity: 0.6; }
    .stage .top { display: flex; align-items: center; gap: 12px; }
    .stage .ic { width: 36px; height: 36px; border-radius: 10px; background: var(--mp-bg); display: flex; align-items: center; justify-content: center; color: var(--mp-muted); font-size: 15px; flex-shrink: 0; }
    .stage .nm { font-size: 14px; font-weight: 700; flex: 1; }
    .stage .totals { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; font-size: 11px; color: var(--mp-muted); }
    .stage .totals b { color: var(--mp-ink); }
    .stage .acts { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
    .btn { padding: 9px 14px; border-radius: 10px; border: 1px solid var(--mp-border); background: var(--mp-bg); font-size: 12px; font-weight: 700; cursor: pointer; text-decoration: none; color: var(--mp-ink); display: inline-flex; align-items: center; gap: 6px; }
    .btn.primary { background: var(--mp-primary); border-color: var(--mp-primary); color: #fff; }
    .btn.danger { color: var(--mp-danger); }
    .btn:disabled { opacity: 0.6; }
    .log { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 12px; padding: 12px 14px; margin-bottom: 8px; }
    .log.reversed { opacity: 0.55; }
    .log .l-top { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
    .log .l-nm { font-size: 13px; font-weight: 700; }
    .log .l-meta { font-size: 11px; color: var(--mp-muted); margin-top: 3px; }
    .log .l-nums { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 8px; font-size: 11px; color: var(--mp-muted); }
    .log .l-nums b { color: var(--mp-ink); }
    .log .l-acts { display: flex; gap: 8px; margin-top: 8px; }
    .log .l-acts .btn { padding: 6px 10px; font-size: 11px; }
    .cost-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .cost-grid .c { background: var(--mp-bg); border-radius: 10px; padding: 10px; font-size: 11px; color: var(--mp-muted); }
    .cost-grid .c b { display: block; font-size: 14px; color: var(--mp-ink); margin-top: 2px; }
    .cost-grid .c.good b { color: var(--mp-success); }
    .cost-grid .c.bad b { color: var(--mp-danger); }
    .wide-btn { width: 100%; padding: 14px; border: none; border-radius: 14px; background: var(--mp-success); color: #fff; font-size: 14px; font-weight: 700; cursor: pointer; margin-top: 6px; }
    .toast { position: fixed; left: 50%; bottom: 110px; transform: translateX(-50%); background: #111827; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 13px; display: none; z-index: 300; max-width: 90%; }
    .empty-state { text-align: center; padding: 30px 20px; color: var(--mp-muted); font-size: 13px; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 130px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 150px; } }
  </style>
</head>
<body>
  <div id="toast" class="toast"></div>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/nylon_jobs'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
      </div>

      <div class="hero">
        <div class="top" style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;">
          <div>
            <div class="nm"><?= htmlspecialchars($job->item_name ?: 'Product'); ?></div>
            <div class="sub"><?= htmlspecialchars($job->job_code); ?><?= $job->order_code ? ' · '.htmlspecialchars($job->order_code) : ''; ?><?= $job->customer_name ? ' · '.htmlspecialchars($job->customer_name) : ''; ?></div>
          </div>
          <span class="badge badge-<?= Nylon_model::job_status_badge($job->status); ?>"><?= htmlspecialchars(Nylon_model::job_status_label($job->status)); ?></span>
        </div>
        <div class="kv">
          <div>Planned<b><?= rtrim(rtrim(number_format((float)$job->planned_qty, 2), '0'), '.'); ?></b></div>
          <div>Due<b><?= $job->due_date ? show_date($job->due_date) : '—'; ?></b></div>
          <div>Kind<b><?= htmlspecialchars(ucfirst($job->job_kind)); ?></b></div>
        </div>
        <?php if($order): ?>
        <div class="sub" style="margin-top:10px;"><a href="<?= base_url('mobile/nylon_order/'.$order->id); ?>" style="color:var(--mp-primary);text-decoration:none;font-weight:600;"><i class="fa fa-external-link"></i> Open linked order</a></div>
        <?php endif; ?>
      </div>

      <div class="section-label">Production Stages</div>
      <?php
        $logs_by_stage = [];
        foreach($logs as $l){ $logs_by_stage[$l->stage_id][] = $l; }
        $closed = in_array($job->status, ['completed','cancelled']);
      ?>
      <?php foreach($stages as $s):
        $def = isset($stage_defs[$s->stage_key]) ? $stage_defs[$s->stage_key] : ['label'=>ucfirst($s->stage_key),'icon'=>'fa-circle'];
        $st_logs = isset($logs_by_stage[$s->id]) ? $logs_by_stage[$s->id] : [];
        $t_in = 0; $t_good = 0; $t_rej = 0;
        foreach($st_logs as $l){ if($l->status === 'reversed') continue; $t_in += (float)$l->qty_in; $t_good += (float)$l->good_qty; $t_rej += (float)$l->reject_qty; }
        $pending_qc = ($s->stage_key === 'qc' && count(array_filter($st_logs, function($l){ return $l->status === 'submitted'; })) > 0);
      ?>
      <div class="stage <?= htmlspecialchars($s->status); ?>">
        <div class="top">
          <div class="ic"><i class="fa <?= htmlspecialchars($def['icon']); ?>"></i></div>
          <div class="nm"><?= htmlspecialchars($def['label']); ?></div>
          <span class="badge badge-<?= Nylon_model::stage_status_badge($s->status); ?>"><?= htmlspecialchars(ucfirst($s->status)); ?></span>
        </div>
        <?php if(!empty($st_logs)): ?>
        <div class="totals">
          <span>In <b><?= rtrim(rtrim(number_format($t_in, 2), '0'), '.'); ?></b></span>
          <span>Good <b><?= rtrim(rtrim(number_format($t_good, 2), '0'), '.'); ?></b></span>
          <?php if($t_rej > 0): ?><span>Rejected <b style="color:var(--mp-danger);"><?= rtrim(rtrim(number_format($t_rej, 2), '0'), '.'); ?></b></span><?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if($pending_qc): ?>
        <div class="totals"><span style="color:var(--mp-warning);"><i class="fa fa-clock-o"></i> Awaiting QC approval before stock is released</span></div>
        <?php endif; ?>
        <div class="acts">
          <?php if(!empty($can_report) && !$closed && $s->status !== 'done' && $s->status !== 'skipped'): ?>
          <a class="btn primary" href="<?= base_url('mobile/nylon_report/'.$job->id.'/'.$s->id); ?>"><i class="fa fa-pencil"></i> Report</a>
          <?php endif; ?>
          <?php if(!empty($can_approve) && !$closed && $s->status !== 'done' && $s->status !== 'skipped'): ?>
          <button class="btn" data-act="stage_done" data-id="<?= (int)$s->id; ?>"><i class="fa fa-check"></i> Done</button>
          <button class="btn" data-act="stage_skip" data-id="<?= (int)$s->id; ?>"><i class="fa fa-forward"></i> Skip</button>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>

      <?php if(!empty($can_approve) && !$closed): ?>
      <button class="wide-btn" id="completeJob"><i class="fa fa-check-circle"></i> Complete Job</button>
      <?php endif; ?>

      <div class="section-label">Shift Reports</div>
      <?php if(!empty($logs)): ?>
        <?php foreach(array_reverse($logs) as $l): ?>
        <div class="log <?= $l->status === 'reversed' ? 'reversed' : ''; ?>">
          <div class="l-top">
            <div class="l-nm"><?= htmlspecialchars(Nylon_model::stage_label($l->stage_key)); ?><?= $l->shift_label ? ' · '.htmlspecialchars($l->shift_label) : ''; ?></div>
            <span class="badge badge-<?= $l->status === 'approved' ? 'success' : ($l->status === 'reversed' ? 'muted' : 'warning'); ?>"><?= htmlspecialchars(ucfirst($l->status)); ?></span>
          </div>
          <div class="l-meta"><?= show_date($l->work_date); ?><?= $l->machine_name ? ' · '.htmlspecialchars($l->machine_name) : ''; ?><?= $l->first_name ? ' · '.htmlspecialchars(trim($l->first_name.' '.$l->last_name)) : ''; ?> · by <?= htmlspecialchars($l->submitted_by); ?></div>
          <div class="l-nums">
            <?php if((float)$l->qty_in > 0): ?><span>In <b><?= rtrim(rtrim(number_format((float)$l->qty_in, 2), '0'), '.'); ?></b></span><?php endif; ?>
            <?php if((float)$l->good_qty > 0): ?><span>Good <b><?= rtrim(rtrim(number_format((float)$l->good_qty, 2), '0'), '.'); ?></b></span><?php endif; ?>
            <?php if((float)$l->reject_qty > 0): ?><span style="color:var(--mp-danger);">Reject <b><?= rtrim(rtrim(number_format((float)$l->reject_qty, 2), '0'), '.'); ?></b></span><?php endif; ?>
            <?php if((float)$l->scrap_qty > 0): ?><span>Scrap <b><?= rtrim(rtrim(number_format((float)$l->scrap_qty, 2), '0'), '.'); ?></b></span><?php endif; ?>
            <?php if((float)$l->waste_qty > 0): ?><span>Waste <b><?= rtrim(rtrim(number_format((float)$l->waste_qty, 2), '0'), '.'); ?></b></span><?php endif; ?>
          </div>
          <?php if(!empty($can_approve) && $l->status !== 'reversed'): ?>
          <div class="l-acts">
            <?php if($l->status === 'submitted'): ?>
            <button class="btn primary" data-act="log_approve" data-id="<?= (int)$l->id; ?>"><i class="fa fa-check"></i> Approve</button>
            <?php endif; ?>
            <button class="btn danger" data-act="log_reverse" data-id="<?= (int)$l->id; ?>"><i class="fa fa-undo"></i> Reverse</button>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state"><div>No shift reports yet.</div></div>
      <?php endif; ?>

      <?php if(!empty($report)): ?>
      <div class="section-label">Costing &amp; Yield</div>
      <div class="hero">
        <div class="cost-grid">
          <div class="c">Estimated<b><?= store_number_format($report['est_total']); ?></b></div>
          <div class="c">Actual<b><?= store_number_format($report['act_total']); ?></b></div>
          <div class="c">Revenue<b><?= store_number_format($report['revenue']); ?></b></div>
          <div class="c <?= $report['margin'] >= 0 ? 'good' : 'bad'; ?>">Margin<b><?= store_number_format($report['margin']); ?><?= $report['margin_pct'] !== null ? ' ('.$report['margin_pct'].'%)' : ''; ?></b></div>
          <div class="c">Yield<b><?= $report['yield_pct'] !== null ? $report['yield_pct'].'%' : '—'; ?></b></div>
          <div class="c">Rejects / Waste<b><?= rtrim(rtrim(number_format($report['reject_qty'], 2), '0'), '.'); ?> / <?= rtrim(rtrim(number_format($report['waste_qty'], 2), '0'), '.'); ?></b></div>
        </div>
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
    var JOB_ID = <?= (int)$job->id; ?>;

    function showToast(msg){ var t = document.getElementById('toast'); t.textContent = msg; t.style.display = 'block'; setTimeout(function(){ t.style.display = 'none'; }, 3000); }

    function post(url, fields, btn){
      var fd = new FormData();
      for(var k in fields){ fd.append(k, fields[k]); }
      fd.append(csrf_token, csrf_hash);
      if(btn) btn.disabled = true;
      fetch(base_url + url, { method: 'POST', body: fd })
        .then(function(r){ return r.json(); })
        .then(function(d){
          if(d && d.csrf_hash){ csrf_hash = d.csrf_hash; }
          if(d && d.success){ window.location.reload(); }
          else { showToast(d && d.message ? d.message : 'Action failed.'); if(btn) btn.disabled = false; }
        })
        .catch(function(){ showToast('Network error.'); if(btn) btn.disabled = false; });
    }

    document.querySelectorAll('[data-act]').forEach(function(btn){
      btn.addEventListener('click', function(){
        var act = this.getAttribute('data-act');
        var id = this.getAttribute('data-id');
        if(act === 'log_reverse'){
          var reason = prompt('Reason for reversing this report?');
          if(reason === null){ return; }
          post('nylon/log_reverse', { id: id, reason: reason }, this);
        } else if(act === 'log_approve'){
          post('nylon/log_approve', { id: id }, this);
        } else if(act === 'stage_done'){
          post('nylon/stage_done', { id: id }, this);
        } else if(act === 'stage_skip'){
          post('nylon/stage_skip', { id: id }, this);
        }
      });
    });

    var cj = document.getElementById('completeJob');
    if(cj){
      cj.addEventListener('click', function(){
        if(!confirm('Complete this job? All stages must be done and QC reports approved.')){ return; }
        post('nylon/job_complete', { id: JOB_ID }, this);
      });
    }
  </script>
</body>
</html>
