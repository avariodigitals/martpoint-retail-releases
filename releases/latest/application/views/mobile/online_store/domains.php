<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Domains</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar .back:active { background: #E2E8F0; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.04); margin-bottom: 16px; }
    .card-title { font-size: 15px; font-weight: 700; padding: 14px 16px; border-bottom: 1px solid var(--mp-border); }
    .form-group { padding: 14px 16px; border-bottom: 1px solid var(--mp-border); }
    .form-group:last-child { border-bottom: none; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--mp-ink); margin-bottom: 6px; }
    .form-control { width: 100%; padding: 12px 14px; border-radius: 12px; border: 1px solid var(--mp-border); font-family: inherit; font-size: 14px; }
    .form-control:focus { border-color: var(--mp-primary); outline: none; }
    .btn { display: block; width: 100%; padding: 14px; border-radius: 12px; border: none; font-weight: 600; font-size: 15px; cursor: pointer; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .url-box { background: #F8FAFC; padding: 14px; border-radius: 12px; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 13px; word-break: break-all; margin-bottom: 8px; }
    .domain-card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); padding: 16px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .domain-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .domain-value { font-weight: 700; font-size: 15px; word-break: break-all; }
    .domain-type { font-size: 11px; font-weight: 600; color: var(--mp-muted); }
    .domain-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
    .status { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; }
    .status-connected { background: #D1FAE5; color: #065F46; }
    .status-disconnected { background: #FEF3C7; color: #92400E; }
    .domain-actions { display: flex; gap: 10px; }
    .domain-actions button { flex: 1; padding: 10px; border-radius: 10px; border: none; font-weight: 600; font-size: 13px; cursor: pointer; }
    .btn-connect { background: #D1FAE5; color: #065F46; }
    .btn-disconnect { background: #FEF3C7; color: #92400E; }
    .btn-delete { background: #FEF2F2; color: #DC2626; }
    .empty { text-align: center; padding: 40px 24px; color: var(--mp-muted); font-size: 14px; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= $back_url; ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Domains</h1>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Free MartPoint URL</div>
        <div class="form-group">
          <div class="url-box"><?= base_url('store/' . ($settings->store_slug ?? '')); ?></div>
          <p style="font-size:13px;color:var(--mp-muted);margin:0;">Your store is always accessible at this URL.</p>
        </div>
      </div>

      <?php if($can_edit): ?>
      <div class="card">
        <div class="card-title">Add Domain</div>
        <form id="domain-form">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
          <div class="form-group">
            <label class="form-label">Domain Type</label>
            <select name="domain_type" class="form-control" id="domain_type">
              <option value="subdomain">MartPoint Subdomain</option>
              <option value="custom">Custom Domain</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Domain Value</label>
            <input type="text" name="domain_value" class="form-control" placeholder="yourstore.martpoint.store or shop.yourstore.com">
          </div>
          <div class="form-group">
            <label class="form-label">DNS Instructions</label>
            <textarea name="dns_instructions" class="form-control" rows="3" placeholder="CNAME yourstore.martpoint.store -> martpoint.store"></textarea>
          </div>
          <div class="form-group">
            <button type="button" class="btn btn-primary" onclick="saveDomain()">Add Domain</button>
          </div>
        </form>
      </div>
      <?php endif; ?>

      <?php if(!empty($domains)): ?>
        <?php foreach($domains as $d): ?>
          <div class="domain-card">
            <div class="domain-header">
              <span class="domain-value"><?= htmlspecialchars($d->domain_value); ?></span>
              <span class="domain-type"><?= ucfirst($d->domain_type); ?></span>
            </div>
            <div class="domain-row">
              <span class="domain-type">Status</span>
              <span class="status <?= $d->connection_status === 'connected' ? 'status-connected' : 'status-disconnected'; ?>"><?= ucfirst($d->connection_status); ?></span>
            </div>
            <?php if($can_edit): ?>
            <div class="domain-actions">
              <?php if($d->connection_status !== 'connected'): ?>
                <button class="btn-connect" onclick="updateStatus(<?= $d->id; ?>, 'connected')">Connect</button>
              <?php else: ?>
                <button class="btn-disconnect" onclick="updateStatus(<?= $d->id; ?>, 'disconnected')">Disconnect</button>
              <?php endif; ?>
              <button class="btn-delete" onclick="deleteDomain(<?= $d->id; ?>, this)">Delete</button>
            </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty">No custom domains configured.</div>
      <?php endif; ?>
    </section>
    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
  <script>
    function showToast(message, isError){
      const toast = document.createElement('div');
      toast.textContent = message;
      toast.style.position = 'fixed'; toast.style.top = '16px'; toast.style.left = '16px'; toast.style.right = '16px';
      toast.style.background = isError ? '#DC2626' : '#059669'; toast.style.color = '#fff'; toast.style.padding = '14px 16px';
      toast.style.borderRadius = '12px'; toast.style.textAlign = 'center'; toast.style.zIndex = '1000'; toast.style.fontWeight = '600';
      document.body.appendChild(toast);
      setTimeout(() => toast.remove(), 3000);
    }
    function saveDomain(){
      const fd = new FormData(document.getElementById('domain-form'));
      fetch('<?= base_url('online_store/save_domain'); ?>', {method:'POST', body:fd})
      .then(r=>r.json()).then(res=>{
        if(res.status === 'success'){ showToast(res.message); location.reload(); } else { showToast(res.message || 'Failed to save', true); }
      }).catch(()=>showToast('Network error', true));
    }
    function updateStatus(id, status){
      const fd = new FormData();
      fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
      fd.append('domain_id', id);
      fd.append('connection_status', status);
      fetch('<?= base_url('online_store/update_domain_status'); ?>', {method:'POST', body:fd})
      .then(r=>r.json()).then(res=>{
        if(res.status === 'success'){ showToast(res.message); location.reload(); } else { showToast(res.message || 'Failed', true); }
      }).catch(()=>showToast('Network error', true));
    }
    function deleteDomain(id, btn){
      if(!confirm('Delete this domain?')) return;
      btn.disabled = true;
      fetch('<?= base_url('online_store/delete_domain'); ?>/'+id, {method:'POST'})
      .then(r=>r.json()).then(res=>{
        if(res.status === 'success'){ showToast(res.message); location.reload(); } else { showToast(res.message || 'Failed', true); btn.disabled = false; }
      }).catch(()=>{ showToast('Network error', true); btn.disabled = false; });
    }
  </script>
</body>
</html>
