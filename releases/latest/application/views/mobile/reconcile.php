<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — Reconciliation</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .rc-summary { display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; margin-bottom: 12px; }
    .rc-sum { background: #fff; border: 1px solid var(--mp-border); border-radius: 12px; padding: 10px; text-align: center; }
    .rc-sum .n { font-size: 18px; font-weight: 700; }
    .rc-sum .l { font-size: 10px; text-transform: uppercase; color: var(--mp-muted); letter-spacing: .3px; }
    .rc-filters { display: flex; gap: 6px; margin-bottom: 12px; overflow-x: auto; padding-bottom: 4px; }
    .rc-pill { padding: 7px 14px; border-radius: 20px; border: 1px solid var(--mp-border); background: #fff; font-size: 12px; font-weight: 600; color: var(--mp-muted); text-decoration: none; white-space: nowrap; }
    .rc-pill.active { background: var(--mp-primary); color: #fff; border-color: var(--mp-primary); }
    .rc-scan { width: 100%; padding: 12px; border-radius: 12px; background: var(--mp-primary); color: #fff; border: none; font-size: 14px; font-weight: 600; margin-bottom: 14px; cursor: pointer; }
    .exc-card { background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 12px 14px; margin-bottom: 10px; }
    .exc-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
    .exc-type { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 12px; text-transform: uppercase; }
    .t-unmatched { background: #FEF2F2; color: #991B1B; }
    .t-late_payment { background: #FEF3C7; color: #92400E; font-weight: 600; }
    .t-discrepancy { background: #FFFBEB; color: #92400E; }
    .t-partial { background: #EFF6FF; color: #1D4ED8; }
    .t-refund { background: #F5F3FF; color: #6D28D9; }
    .t-dispute { background: #FFF1F2; color: #BE123C; }
    .exc-status { font-size: 11px; font-weight: 600; color: var(--mp-muted); }
    .exc-status.open { color: #991B1B; }
    .exc-detail { font-size: 13px; margin: 4px 0; }
    .exc-meta { font-size: 11px; color: var(--mp-muted); margin-top: 6px; }
    .exc-amounts { font-size: 13px; font-weight: 600; margin-top: 6px; }
    .exc-actions { display: flex; gap: 6px; margin-top: 10px; flex-wrap: wrap; }
    .exc-act { padding: 7px 12px; border-radius: 9px; border: 1px solid var(--mp-border); background: #fff; font-size: 12px; font-weight: 600; cursor: pointer; }
    .exc-act.resolve { background: var(--mp-success); color: #fff; border-color: var(--mp-success); }
    .empty { text-align: center; padding: 50px 24px; color: var(--mp-muted); font-size: 14px; }
    .toast { position: fixed; top: 16px; left: 16px; right: 16px; padding: 14px; border-radius: 12px; text-align: center; color: #fff; font-weight: 600; z-index: 1000; display: none; }
  </style>
</head>
<body>
  <div id="toast" class="toast"></div>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= $back_url; ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Reconciliation</h1>
        </div>
      </div>

      <div class="rc-summary">
        <div class="rc-sum"><div class="n" style="color:#991B1B;"><?= (int)($counts['open'] ?? 0); ?></div><div class="l">Open</div></div>
        <div class="rc-sum"><div class="n"><?= (int)($counts['unmatched_open'] ?? 0); ?></div><div class="l">Unmatched</div></div>
        <div class="rc-sum"><div class="n" style="color:#92400E;"><?= (int)($counts['late_payment_open'] ?? 0); ?></div><div class="l">Late pay</div></div>
        <div class="rc-sum"><div class="n"><?= (int)($counts['discrepancy_open'] ?? 0); ?></div><div class="l">Mismatch</div></div>
      </div>

      <div class="rc-filters">
        <?php foreach(array('open'=>'Open','acknowledged'=>'Acknowledged','resolved'=>'Resolved','dismissed'=>'Dismissed','all'=>'All') as $k=>$v): ?>
        <a class="rc-pill <?= $f_status===$k?'active':''; ?>" href="<?= base_url('mobile/reconcile?status='.$k); ?>"><?= $v; ?></a>
        <?php endforeach; ?>
      </div>

      <button class="rc-scan" id="rcScanBtn" onclick="rcScan()"><i class="fa fa-refresh"></i> Run Scan</button>

      <?php if(empty($exceptions)): ?>
        <div class="empty">No <?= htmlspecialchars($f_status); ?> exceptions.</div>
      <?php else: foreach($exceptions as $e): ?>
        <div class="exc-card">
          <div class="exc-head">
            <span class="exc-type t-<?= htmlspecialchars($e->exception_type); ?>"><?= htmlspecialchars($e->exception_type); ?></span>
            <span class="exc-status <?= htmlspecialchars($e->status); ?>"><?= htmlspecialchars($e->status); ?></span>
          </div>
          <div class="exc-detail"><?= htmlspecialchars($e->detail); ?></div>
          <div class="exc-amounts">
            <?php if($e->amount !== null): ?>Amount: <?= store_number_format($e->amount); ?><?php endif; ?>
            <?php if($e->expected_amount !== null): ?> &nbsp;·&nbsp; Expected: <?= store_number_format($e->expected_amount); ?><?php endif; ?>
          </div>
          <div class="exc-meta">
            <?= htmlspecialchars($e->provider); ?><?= $e->provider==='manual' ? ' (manually recorded)' : ''; ?>
            <?= $e->reference ? ' · '.htmlspecialchars($e->reference) : ''; ?>
            <?= $e->sales_id ? ' · Sale #'.(int)$e->sales_id : ''; ?>
            <?= $e->order_id ? ' · Order #'.(int)$e->order_id : ''; ?>
            <br><?= htmlspecialchars($e->created_date); ?> via <?= htmlspecialchars($e->detected_by); ?>
          </div>
          <?php if($e->status === 'open' || $e->status === 'acknowledged'): ?>
          <div class="exc-actions">
            <button class="exc-act resolve" onclick="rcResolve(<?= (int)$e->id; ?>,'resolved')">Resolve</button>
            <button class="exc-act" onclick="rcResolve(<?= (int)$e->id; ?>,'acknowledged')">Ack</button>
            <?php if($e->exception_type !== 'dispute'): ?>
            <button class="exc-act" onclick="rcResolve(<?= (int)$e->id; ?>,'dispute')">Dispute</button>
            <?php endif; ?>
            <button class="exc-act" onclick="rcResolve(<?= (int)$e->id; ?>,'dismissed')">Dismiss</button>
          </div>
          <?php endif; ?>
        </div>
      <?php endforeach; endif; ?>
    </section>
    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    function showToast(msg, isError){
      const t = document.getElementById('toast');
      t.textContent = msg;
      t.style.background = isError ? '#EF4444' : '#10B981';
      t.style.display = 'block';
      setTimeout(() => t.style.display = 'none', 2500);
    }
    function csrf(){
      <?php if($this->security): ?>
      return {'<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'};
      <?php else: ?>
      return {};
      <?php endif; ?>
    }
    var rcRowType = { <?php $rt=array(); foreach($exceptions as $e){ $rt[] = (int)$e->id . ':' . json_encode($e->exception_type); } echo implode(',', $rt); ?> };
    function rcScan(){
      var btn = document.getElementById('rcScanBtn');
      btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Scanning…';
      var fd = new FormData();
      for(const [k,v] of Object.entries(csrf())) fd.append(k, v);
      mpFetchJson('<?= base_url('reconcile/scan'); ?>', {method:'POST', body:fd})
        .then(d => {
          btn.disabled = false; btn.innerHTML = '<i class="fa fa-refresh"></i> Run Scan';
          if(d.status === 'success'){
            var f = d.found || {};
            showToast('Scan done: '+(f.unmatched||0)+' unmatched, '+(f.discrepancy||0)+' discrepancies, '+(f.partial||0)+' partial, '+(f.refund||0)+' refunds');
            setTimeout(() => window.location.reload(), 1800);
          } else { showToast(d.message || 'Scan failed', true); }
        })
        .catch(err => { btn.disabled=false; btn.innerHTML='<i class="fa fa-refresh"></i> Run Scan'; showToast((typeof mpErrorText==='function'?mpErrorText(err):'Request failed. Please try again.'), true); });
    }
    function rcResolve(id, action){
      var note = '';
      var moneyTypes = ['unmatched','late_payment'];
      var type = (window.rcRowType && window.rcRowType[id]) || '';
      if(action === 'acknowledged' && moneyTypes.indexOf(type) !== -1){
        // Money-received exceptions: a bare acknowledgement hides real cash.
        var raw = prompt('This exception reports money actually received. Acknowledging it does NOT settle the money.\n\nRecord what happened (refund sent, order reinstated, etc.) — required:');
        if(raw === null) return;
        note = (raw || '').trim();
        if(!note){ showToast('A note is required for money-received exceptions.', true); return; }
      } else if(action === 'resolved' || action === 'dismissed' || action === 'dispute'){
        var raw2 = prompt(action.charAt(0).toUpperCase()+action.slice(1) + ' — add a note (optional):');
        if(raw2 === null) return;
        note = raw2;
      }
      var fd = new FormData();
      fd.append('id', id); fd.append('action', action); fd.append('note', note);
      for(const [k,v] of Object.entries(csrf())) fd.append(k, v);
      mpFetchJson('<?= base_url('reconcile/resolve'); ?>', {method:'POST', body:fd})
        .then(d => {
          if(d.status === 'success'){ showToast(d.message || 'Updated'); setTimeout(() => window.location.reload(), 800); }
          else showToast(d.message || 'Update failed', true);
        })
        .catch(err => showToast((typeof mpErrorText==='function'?mpErrorText(err):'Request failed. Please try again.'), true));
    }
  </script>
</body>
</html>
