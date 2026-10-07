<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title); ?></title>
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
    .card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 12px; }
    .card .ic { width: 40px; height: 40px; border-radius: 12px; background: #CCFBF1; color: #0F766E; display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0; }
    .card .mi { flex: 1; min-width: 0; }
    .nm { font-size: 14px; font-weight: 700; }
    .sub { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .del { width: 34px; height: 34px; border-radius: 10px; border: 1px solid var(--mp-border); background: #fff; color: var(--mp-danger); font-size: 13px; cursor: pointer; flex-shrink: 0; }
    .section { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin: 16px 0 12px; }
    .section h3 { font-size: 13px; font-weight: 700; margin: 0 0 12px; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.4px; }
    .field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 12px; }
    .field:last-child { margin-bottom: 0; }
    .field label { font-size: 13px; color: var(--mp-muted); font-weight: 600; }
    .field input[type=text], .field textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-family: inherit; font-size: 15px; background: #fff; color: var(--mp-ink); }
    .field textarea { min-height: 60px; resize: vertical; }
    .mp-select { display: none; }
    .mp-select-wrap { position: relative; }
    .mp-select-trigger { display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; font-size: 15px; cursor: pointer; }
    .mp-select-options { display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 200; background: #fff; border: 1px solid var(--mp-border); border-radius: 12px; max-height: 220px; overflow-y: auto; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .mp-select-options.open { display: block; }
    .mp-option { padding: 12px 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; font-size: 14px; }
    .mp-option:last-child { border-bottom: none; }
    .mp-option.active { background: #E0E7FF; color: var(--mp-primary); font-weight: 600; }
    .save-btn { width: 100%; padding: 14px; border: none; border-radius: 12px; background: var(--mp-primary); color: #fff; font-size: 14px; font-weight: 700; cursor: pointer; }
    .save-btn:disabled { opacity: 0.6; }
    .toast { position: fixed; left: 50%; bottom: 110px; transform: translateX(-50%); background: #111827; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 13px; display: none; z-index: 300; max-width: 90%; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); font-size: 14px; }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 130px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 150px; } }
  </style>
</head>
<body>
  <div id="toast" class="toast"></div>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/nylon'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
      </div>

      <?php if(!empty($machines)): ?>
        <?php foreach($machines as $m): ?>
        <div class="card">
          <div class="ic"><i class="fa fa-cog"></i></div>
          <div class="mi">
            <div class="nm"><?= htmlspecialchars($m->machine_name); ?></div>
            <div class="sub"><?= $m->machine_code ? htmlspecialchars($m->machine_code).' · ' : ''; ?><?= htmlspecialchars(isset($types[$m->machine_type]) ? $types[$m->machine_type] : ucfirst($m->machine_type)); ?><?= $m->notes ? ' · '.htmlspecialchars($m->notes) : ''; ?></div>
          </div>
          <?php if(!empty($can_edit)): ?>
          <button class="del" data-del="<?= (int)$m->id; ?>" title="Remove"><i class="fa fa-trash-o"></i></button>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state"><i class="fa fa-cogs"></i><div>No machines registered yet.</div></div>
      <?php endif; ?>

      <?php if(!empty($can_edit)): ?>
      <div class="section">
        <h3>Add Machine</h3>
        <form id="mForm" method="post" action="<?= base_url('nylon/machine_save'); ?>" onsubmit="return saveMachine(event);" autocomplete="off">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
          <div class="field"><label>Machine Name *</label><input type="text" name="machine_name" placeholder="e.g. Extruder 1" required></div>
          <div class="field"><label>Code</label><input type="text" name="machine_code" placeholder="e.g. EX-01"></div>
          <div class="field">
            <label>Type</label>
            <select name="machine_type" class="mp-select">
              <?php foreach($types as $k => $lbl): ?><option value="<?= $k; ?>"><?= htmlspecialchars($lbl); ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="field"><label>Notes</label><textarea name="notes" placeholder="Capacity, die size, location…"></textarea></div>
          <button type="submit" class="save-btn" id="saveBtn"><i class="fa fa-plus"></i> Add Machine</button>
        </form>
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

    function showToast(msg){ var t = document.getElementById('toast'); t.textContent = msg; t.style.display = 'block'; setTimeout(function(){ t.style.display = 'none'; }, 3000); }

    /* ---------- mp-select ---------- */
    function buildSelect(sel){
      var wrap = document.createElement('div'); wrap.className = 'mp-select-wrap';
      sel.parentNode.insertBefore(wrap, sel);
      wrap.appendChild(sel);
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
          e.stopPropagation();
          sel.selectedIndex = idx; setLabel();
          opts.querySelectorAll('.mp-option').forEach(function(o){ o.classList.remove('active'); });
          d.classList.add('active'); opts.classList.remove('open');
          sel.dispatchEvent(new Event('change'));
        });
        opts.appendChild(d);
      });
      setLabel();
      trigger.addEventListener('click', function(e){
        e.stopPropagation();
        document.querySelectorAll('.mp-select-options.open').forEach(function(o){ if(o !== opts) o.classList.remove('open'); });
        opts.classList.toggle('open');
      });
    }
    document.querySelectorAll('select.mp-select').forEach(buildSelect);
    document.addEventListener('click', function(){ document.querySelectorAll('.mp-select-options.open').forEach(function(o){ o.classList.remove('open'); }); });

    function saveMachine(e){
      e.preventDefault();
      var btn = document.getElementById('saveBtn');
      btn.disabled = true;
      var form = document.getElementById('mForm');
      mpFetchJson(form.action, { method: 'POST', body: new FormData(form) })
        .then(function(d){
          if(d && d.success){ window.location.reload(); }
          else { showToast(d && d.message ? d.message : 'Could not save this machine. Please check the details and try again.'); btn.disabled = false; }
        })
        .catch(function(err){ showToast(mpErrorText(err)); btn.disabled = false; });
      return false;
    }

    document.querySelectorAll('[data-del]').forEach(function(btn){
      btn.addEventListener('click', function(){
        if(!confirm('Remove this machine?')){ return; }
        var b = this; b.disabled = true;
        var fd = new FormData();
        fd.append('id', this.getAttribute('data-del'));
        fd.append(csrf_token, csrf_hash);
        mpFetchJson(base_url + 'nylon/machine_delete', { method: 'POST', body: fd })
          .then(function(d){
            if(d && d.csrf_hash){ csrf_hash = d.csrf_hash; }
            if(d && d.success){ window.location.reload(); }
            else { showToast(d && d.message ? d.message : 'Could not delete this machine. Please try again.'); b.disabled = false; }
          })
          .catch(function(err){ showToast(mpErrorText(err)); b.disabled = false; });
      });
    });
  </script>
</body>
</html>
