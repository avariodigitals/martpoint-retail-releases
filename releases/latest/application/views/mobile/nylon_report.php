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
    .ctx { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 12px; font-size: 13px; }
    .ctx .flow { display: flex; align-items: center; gap: 8px; margin-top: 8px; font-size: 12px; color: var(--mp-muted); flex-wrap: wrap; }
    .ctx .flow .chip { background: var(--mp-bg); border-radius: 8px; padding: 5px 10px; font-weight: 600; color: var(--mp-ink); }
    .ctx .hint { font-size: 11px; color: var(--mp-muted); margin-top: 8px; line-height: 1.4; }
    .section { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 12px; }
    .section h3 { font-size: 13px; font-weight: 700; margin: 0 0 12px; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.4px; }
    .field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
    .field:last-child { margin-bottom: 0; }
    .field label { font-size: 13px; color: var(--mp-muted); font-weight: 600; }
    .field input[type=text], .field input[type=number], .field input[type=date], .field textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-family: inherit; font-size: 15px; background: #fff; color: var(--mp-ink); }
    .field textarea { min-height: 70px; resize: vertical; }
    .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .mp-select { display: none; }
    .mp-select-wrap { position: relative; }
    .mp-select-trigger { display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; font-size: 15px; cursor: pointer; }
    .mp-select-options { display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 200; background: #fff; border: 1px solid var(--mp-border); border-radius: 12px; max-height: 220px; overflow-y: auto; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .mp-select-options.open { display: block; }
    .mp-option { padding: 12px 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; font-size: 14px; }
    .mp-option:last-child { border-bottom: none; }
    .mp-option.active { background: #E0E7FF; color: var(--mp-primary); font-weight: 600; }
    .save-btn { width: 100%; padding: 16px; border: none; border-radius: 14px; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 4px; }
    .save-btn:disabled { opacity: 0.6; }
    .toast { position: fixed; left: 50%; bottom: 110px; transform: translateX(-50%); background: #111827; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 13px; display: none; z-index: 300; max-width: 90%; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 130px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 150px; } }
  </style>
</head>
<body>
  <div id="toast" class="toast"></div>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/nylon_job/'.$job->id); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
      </div>

      <div class="ctx">
        <b><?= htmlspecialchars($job->job_code); ?></b> — <?= htmlspecialchars($job->item_name ?: 'Product'); ?>
        <div class="flow">
          <?php if($input_name): ?><span class="chip"><?= htmlspecialchars($input_name); ?></span><i class="fa fa-arrow-right"></i><?php endif; ?>
          <span class="chip"><?= htmlspecialchars($output_name ?: ($job->item_name ?: 'Output')); ?></span>
        </div>
        <?php if($stage->stage_key === 'material_allocation'): ?>
        <div class="hint">Allocation reserves material for this job — it does not deduct stock. Consumption is posted at the stage that actually uses it.</div>
        <?php elseif($stage->stage_key === 'qc'): ?>
        <div class="hint">QC output is held until a supervisor approves this report — only then does it become saleable stock. Rejects are never added to stock.</div>
        <?php endif; ?>
      </div>

      <form id="logForm" method="post" action="<?= base_url('nylon/stage_log'); ?>" onsubmit="return saveLog(event);" autocomplete="off">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="job_id" value="<?= (int)$job->id; ?>">
        <input type="hidden" name="stage_id" value="<?= (int)$stage->id; ?>">

        <div class="section">
          <h3>Shift</h3>
          <div class="grid2">
            <div class="field"><label>Work Date</label><input type="date" name="work_date" value="<?= date('Y-m-d'); ?>"></div>
            <div class="field">
              <label>Shift</label>
              <select name="shift_label" class="mp-select">
                <?php foreach($shifts as $sh): ?><option value="<?= $sh; ?>"><?= $sh; ?></option><?php endforeach; ?>
              </select>
            </div>
          </div>
          <?php if(!empty($machines)): ?>
          <div class="field">
            <label>Machine</label>
            <select name="machine_id" class="mp-select">
              <option value="">No machine</option>
              <?php foreach($machines as $m): ?><option value="<?= (int)$m->id; ?>"><?= htmlspecialchars($m->machine_name); ?><?= $m->machine_code ? ' ('.htmlspecialchars($m->machine_code).')' : ''; ?></option><?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
        </div>

        <div class="section">
          <h3>Quantities</h3>
          <div class="grid2">
            <div class="field"><label>Qty In</label><input type="number" step="any" min="0" name="qty_in" placeholder="0"></div>
            <div class="field"><label>Good Output</label><input type="number" step="any" min="0" name="good_qty" placeholder="0"></div>
            <div class="field"><label>Rejected</label><input type="number" step="any" min="0" name="reject_qty" placeholder="0"></div>
            <div class="field"><label>Reusable Scrap</label><input type="number" step="any" min="0" name="scrap_qty" id="scrap_qty" placeholder="0"></div>
            <div class="field"><label>Lost Waste</label><input type="number" step="any" min="0" name="waste_qty" placeholder="0"></div>
          </div>
          <div class="field" id="scrapItemField" style="display:none;">
            <label>Scrap goes to (regrind item)</label>
            <select name="scrap_item_id" class="mp-select">
              <option value="">Not reused</option>
              <?php foreach($scrap_items as $si): ?><option value="<?= (int)$si->id; ?>"><?= htmlspecialchars($si->item_name); ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="section">
          <h3>Notes</h3>
          <div class="field"><textarea name="notes" placeholder="Shift notes, downtime, observations…"></textarea></div>
        </div>

        <button type="submit" class="save-btn" id="saveBtn"><i class="fa fa-check"></i> Submit Report</button>
      </form>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var base_url = '<?= base_url(); ?>';
    var JOB_ID = <?= (int)$job->id; ?>;

    function showToast(msg){ var t = document.getElementById('toast'); t.textContent = msg; t.style.display = 'block'; setTimeout(function(){ t.style.display = 'none'; }, 3500); }

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

    /* Show the scrap-item picker only when scrap qty is entered */
    document.getElementById('scrap_qty').addEventListener('input', function(){
      document.getElementById('scrapItemField').style.display = parseFloat(this.value) > 0 ? '' : 'none';
    });

    function saveLog(e){
      e.preventDefault();
      var btn = document.getElementById('saveBtn');
      btn.disabled = true;
      var form = document.getElementById('logForm');
      mpFetchJson(form.action, { method: 'POST', body: new FormData(form) })
        .then(function(d){
          if(d && d.csrf_hash){
            var ci = form.querySelector('input[name="<?= $this->security->get_csrf_token_name(); ?>"]');
            if(ci) ci.value = d.csrf_hash;
          }
          if(d && d.success){
            showToast(d.message || 'Report saved.');
            setTimeout(function(){ window.location.href = base_url + 'mobile/nylon_job/' + JOB_ID; }, 700);
          } else {
            showToast(d && d.message ? d.message : 'Could not save this report. Please check the details and try again.');
            btn.disabled = false;
          }
        })
        .catch(function(err){ showToast(mpErrorText(err)); btn.disabled = false; });
      return false;
    }
  </script>
</body>
</html>
