<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — Service Status</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/assist.css?v=15">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 16px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .card { background: #fff; border: 1px solid var(--mp-border); border-radius: 16px; padding: 16px; margin-bottom: 14px; }
    .card h3 { margin: 0 0 4px; font-size: 15px; font-weight: 700; }
    .card .hint { font-size: 12px; color: var(--mp-muted); margin: 0 0 14px; line-height: 1.5; }
    .state-pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; margin-bottom: 14px; }
    .state-pill.on { background: #FEF3C7; color: #92400E; }
    .state-pill.off { background: #ECFDF5; color: #065F46; }
    .state-pill .fa { font-size: 11px; }
    .field { margin-bottom: 14px; }
    .field label { display: block; font-size: 13px; font-weight: 600; color: var(--mp-ink); margin-bottom: 6px; }
    .field input[type="text"], .field input[type="url"] { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; font-family: inherit; background: #fff; color: var(--mp-text); }
    .field .sub { font-size: 11px; color: var(--mp-muted); margin-top: 4px; }
    .switch-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .switch-row .label-txt { font-size: 14px; font-weight: 600; color: var(--mp-ink); }
    .switch-row .sub { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .mp-switch { position: relative; width: 48px; height: 28px; flex-shrink: 0; }
    .mp-switch input { opacity: 0; width: 0; height: 0; position: absolute; }
    .mp-switch .track { position: absolute; inset: 0; border-radius: 999px; background: #CBD5E1; transition: background .2s; }
    .mp-switch .track::after { content: ''; position: absolute; top: 3px; left: 3px; width: 22px; height: 22px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25); transition: transform .2s; }
    .mp-switch input:checked + .track { background: var(--mp-warning); }
    .mp-switch input:checked + .track::after { transform: translateX(20px); }
    .mp-select { display: none; }
    .mp-select-wrap { position: relative; width: 100%; }
    .mp-select-trigger { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; color: var(--mp-text); cursor: pointer; display: flex; align-items: center; justify-content: space-between; min-height: 46px; }
    .mp-select-trigger::after { content: '\f0d7'; font-family: 'FontAwesome'; color: var(--mp-muted); font-size: 14px; }
    .mp-select-options { display: none; border: 1px solid var(--mp-border); border-top: none; border-radius: 0 0 12px 12px; background: #fff; max-height: 220px; overflow-y: auto; position: relative; z-index: 10; }
    .mp-select-wrap.open .mp-select-options { display: block; }
    .mp-select-wrap.open .mp-select-trigger { border-radius: 12px 12px 0 0; }
    .mp-select-option { padding: 12px 14px; cursor: pointer; border-bottom: 1px solid var(--mp-border); font-size: 15px; }
    .mp-select-option:last-child { border-bottom: none; }
    .mp-select-option.selected { color: var(--mp-primary); font-weight: 600; }
    .save-btn { width: 100%; padding: 14px; border: none; border-radius: 14px; background: var(--mp-primary); color: #fff; font-size: 15px; font-weight: 700; font-family: inherit; }
    .save-btn:active { opacity: .9; }
    .save-btn:disabled { opacity: .6; }
    .preview-note { font-size: 12px; color: var(--mp-muted); text-align: center; margin-top: 10px; }
    .preview-note a { color: var(--mp-primary); font-weight: 600; text-decoration: none; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 24px 120px; max-width: 640px; margin: 0 auto; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/more'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Service Status</h1>
        </div>
      </div>

      <div class="card">
        <h3>Incident banner</h3>
        <p class="hint">While active, a notice strip shows at the top of every screen — desktop and mobile — linking to your status page. Use it for outages or scheduled maintenance.</p>
        <?php $inc_active = !empty($incident['active']); ?>
        <span class="state-pill <?= $inc_active ? 'on' : 'off'; ?>" id="statePill">
          <i class="fa <?= $inc_active ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
          <?= $inc_active ? 'Banner is showing' : 'All clear — banner hidden'; ?>
        </span>

        <div class="field switch-row">
          <div>
            <div class="label-txt">Show incident banner</div>
            <div class="sub"><?= $inc_active && !empty($incident['started_at']) ? 'Showing since ' . htmlspecialchars($incident['started_at']) : 'Turn on to publish the notice'; ?></div>
          </div>
          <label class="mp-switch">
            <input type="checkbox" id="incident_active" <?= $inc_active ? 'checked' : ''; ?>>
            <span class="track"></span>
          </label>
        </div>

        <div class="field">
          <label for="incident_severity">Severity</label>
          <select id="incident_severity" class="mp-select">
            <option value="investigating" <?= ($incident['severity'] ?? '') === 'investigating' ? 'selected' : ''; ?>>Investigating</option>
            <option value="identified" <?= ($incident['severity'] ?? '') === 'identified' ? 'selected' : ''; ?>>Identified</option>
            <option value="monitoring" <?= ($incident['severity'] ?? '') === 'monitoring' ? 'selected' : ''; ?>>Monitoring</option>
            <option value="maintenance" <?= ($incident['severity'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>Scheduled maintenance</option>
          </select>
        </div>

        <div class="field">
          <label for="incident_message">Message</label>
          <input type="text" id="incident_message" maxlength="255" value="<?= htmlspecialchars($incident['message'] ?? ''); ?>" placeholder="We are investigating a technical issue.">
        </div>

        <div class="field">
          <label for="incident_url">Status page URL</label>
          <input type="url" id="incident_url" maxlength="255" value="<?= htmlspecialchars($incident['url'] ?? 'https://www.martpoint.com.ng/status'); ?>" placeholder="https://www.martpoint.com.ng/status">
          <div class="sub">Opens in a new tab when staff tap "Status page" on the banner.</div>
        </div>

        <button type="button" class="save-btn" id="saveBtn">Save</button>
        <div class="preview-note">Live updates: <a href="<?= htmlspecialchars($incident['url'] ?? 'https://www.martpoint.com.ng/status'); ?>" target="_blank" rel="noopener noreferrer">open status page</a></div>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
  <script>
  (function(){
    // Custom select (native <select> popovers are banned on mobile views)
    document.querySelectorAll('select.mp-select').forEach(function(sel){
      var wrap = document.createElement('div'); wrap.className = 'mp-select-wrap';
      var trigger = document.createElement('div'); trigger.className = 'mp-select-trigger';
      var opts = document.createElement('div'); opts.className = 'mp-select-options';
      function label(){ var o = sel.options[sel.selectedIndex]; trigger.textContent = o ? o.textContent : ''; }
      Array.prototype.forEach.call(sel.options, function(o){
        var d = document.createElement('div'); d.className = 'mp-select-option' + (o.selected ? ' selected' : '');
        d.textContent = o.textContent;
        d.addEventListener('click', function(){
          sel.value = o.value; label();
          opts.querySelectorAll('.mp-select-option').forEach(function(x){ x.classList.remove('selected'); });
          d.classList.add('selected');
          wrap.classList.remove('open');
        });
        opts.appendChild(d);
      });
      label();
      trigger.addEventListener('click', function(){ wrap.classList.toggle('open'); });
      document.addEventListener('click', function(e){ if(!wrap.contains(e.target)) wrap.classList.remove('open'); });
      sel.parentNode.insertBefore(wrap, sel);
      wrap.appendChild(sel); wrap.appendChild(trigger); wrap.appendChild(opts);
    });

    var csrfName = <?= json_encode($this->security->get_csrf_token_name()); ?>;
    var csrfHash = <?= json_encode($this->security->get_csrf_hash()); ?>;
    var saveBtn = document.getElementById('saveBtn');
    saveBtn.addEventListener('click', function(){
      var body = new URLSearchParams();
      body.set(csrfName, csrfHash);
      body.set('incident_active', document.getElementById('incident_active').checked ? '1' : '0');
      body.set('incident_severity', document.getElementById('incident_severity').value);
      body.set('incident_message', document.getElementById('incident_message').value);
      body.set('incident_url', document.getElementById('incident_url').value);
      saveBtn.disabled = true;
      mpFetchJson('<?= base_url('site/save_incident'); ?>', { method: 'POST', body: body })
        .then(function(res){
          saveBtn.disabled = false;
          if(res && res.status === 'ok'){
            mpSuccess(res.message || 'Saved.');
            var on = document.getElementById('incident_active').checked;
            var pill = document.getElementById('statePill');
            pill.className = 'state-pill ' + (on ? 'on' : 'off');
            pill.innerHTML = '<i class="fa ' + (on ? 'fa-exclamation-circle' : 'fa-check-circle') + '"></i> ' + (on ? 'Banner is showing' : 'All clear — banner hidden');
          } else {
            mpError((res && res.message) || 'Could not save.');
          }
        })
        .catch(function(err){ saveBtn.disabled = false; mpError(mpErrorText(err)); });
    });
  })();
  </script>
</body>
</html>
