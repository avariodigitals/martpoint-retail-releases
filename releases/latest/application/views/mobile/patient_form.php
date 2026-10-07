<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title ?? 'Register Patient'); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-danger: #EF4444; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .field { margin-bottom: 12px; }
    .field label { display: block; font-size: 12px; font-weight: 600; color: var(--mp-muted); margin-bottom: 5px; }
    .field input, .field select, .field textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; font-family: inherit; }
    .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .sec { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--mp-muted); margin: 18px 0 8px; }
    .save { width: 100%; padding: 14px; border-radius: 12px; border: none; background: var(--mp-primary); color: #fff; font-size: 15px; font-weight: 700; cursor: pointer; margin-top: 8px; }
    .save:active { opacity: .9; }
    .dup-panel { background: #FEF3C7; border: 1px solid #F59E0B; border-radius: 12px; padding: 12px 14px; margin-bottom: 14px; font-size: 13px; display: none; }
    .dup-panel ul { margin: 6px 0 0 18px; }
    /* Custom inline select — no native dropdown shoot-outs */
    .mp-select-wrap { position: relative; width: 100%; }
    select.mp-select { display: none !important; }
    .mp-select-trigger { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; color: var(--mp-text); cursor: pointer; display: flex; align-items: center; justify-content: space-between; min-height: 46px; }
    .mp-select-trigger::after { content: '\f0d7'; font-family: 'FontAwesome'; color: var(--mp-muted); font-size: 14px; }
    .mp-select-trigger.placeholder { color: var(--mp-muted); }
    .mp-select-options { display: none; border: 1px solid var(--mp-border); border-top: none; border-radius: 0 0 12px 12px; background: #fff; max-height: 220px; overflow-y: auto; position: absolute; left: 0; right: 0; top: 100%; z-index: 100; }
    .mp-select-wrap.open .mp-select-options { display: block; }
    .mp-select-wrap.open .mp-select-trigger { border-radius: 12px 12px 0 0; }
    .mp-select-option { padding: 12px 14px; cursor: pointer; border-bottom: 1px solid var(--mp-border); font-size: 15px; }
    .mp-select-option:last-child { border-bottom: none; }
    .mp-select-option.active { background: #EFF6FF; color: var(--mp-primary); font-weight: 600; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/patients'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
      </div>

      <div class="dup-panel" id="dupPanel"></div>

      <form id="patientForm" onsubmit="return false;">
        <input type="hidden" name="patient_id" id="patient_id" value="<?= (int)($patient->id ?? 0); ?>">
        <input type="hidden" id="confirm_duplicate" value="0">

        <div class="field"><label>Full Name *</label><input type="text" id="pt_name" value="<?= htmlspecialchars($patient->customer_name ?? ''); ?>"></div>
        <div class="row2">
          <div class="field"><label>Mobile / WhatsApp</label><input type="tel" id="pt_mobile" value="<?= htmlspecialchars($patient->mobile ?? ''); ?>"></div>
          <div class="field"><label>Alt. Phone</label><input type="tel" id="pt_phone" value="<?= htmlspecialchars($patient->phone ?? ''); ?>"></div>
        </div>
        <div class="field"><label>Email</label><input type="email" id="pt_email" value="<?= htmlspecialchars($patient->email ?? ''); ?>"></div>

        <div class="sec">Demographics</div>
        <div class="row2">
          <div class="field"><label>Date of Birth</label><input type="date" id="pt_dob" value="<?= htmlspecialchars($patient->dob ?? ''); ?>"></div>
          <div class="field"><label>Gender</label>
            <select id="pt_gender">
              <option value="">—</option>
              <?php foreach(['male'=>'Male','female'=>'Female','other'=>'Other'] as $v=>$t): ?>
              <option value="<?= $v; ?>" <?= (($patient->gender ?? '') === $v) ? 'selected' : ''; ?>><?= $t; ?></option>
              <?php endforeach; ?>
            </select></div>
        </div>
        <div class="row2">
          <div class="field"><label>Marital Status</label><input type="text" id="pt_marital" value="<?= htmlspecialchars($patient->marital_status ?? ''); ?>"></div>
          <div class="field"><label>Blood Group</label>
            <select id="pt_blood">
              <option value="">—</option>
              <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-','unknown'] as $v): ?>
              <option value="<?= $v; ?>" <?= (($patient->blood_group ?? '') === $v) ? 'selected' : ''; ?>><?= $v === 'unknown' ? 'Unknown' : $v; ?></option>
              <?php endforeach; ?>
            </select></div>
        </div>
        <div class="field"><label>Occupation</label><input type="text" id="pt_occupation" value="<?= htmlspecialchars($patient->occupation ?? ''); ?>"></div>
        <div class="field"><label>Address</label><input type="text" id="pt_address" value="<?= htmlspecialchars($patient->address ?? ''); ?>"></div>
        <div class="field"><label>City</label><input type="text" id="pt_city" value="<?= htmlspecialchars($patient->city ?? ''); ?>"></div>

        <div class="sec">Next of Kin</div>
        <div class="field"><label>Name</label><input type="text" id="pt_nok_name" value="<?= htmlspecialchars($patient->nok_name ?? ''); ?>"></div>
        <div class="row2">
          <div class="field"><label>Phone</label><input type="tel" id="pt_nok_phone" value="<?= htmlspecialchars($patient->nok_phone ?? ''); ?>"></div>
          <div class="field"><label>Relationship</label><input type="text" id="pt_nok_rel" value="<?= htmlspecialchars($patient->nok_relationship ?? ''); ?>" placeholder="e.g. Spouse"></div>
        </div>

        <button type="button" class="save" onclick="savePatient()">Save <?= htmlspecialchars($patient_term); ?></button>
      </form>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var CSRF_NAME = '<?= $this->security->get_csrf_token_name(); ?>';
    var CSRF_HASH = '<?= $this->security->get_csrf_hash(); ?>';

    // Custom inline select — hidden native select retains name/value
    (function(){
      function buildMpSelect(sel){
        if(sel.dataset.mpBuilt) return;
        sel.dataset.mpBuilt = '1';
        sel.classList.add('mp-select');
        var wrap = document.createElement('div');
        wrap.className = 'mp-select-wrap';
        sel.parentNode.insertBefore(wrap, sel);
        wrap.appendChild(sel);
        var trigger = document.createElement('div');
        trigger.className = 'mp-select-trigger';
        wrap.appendChild(trigger);
        var list = document.createElement('div');
        list.className = 'mp-select-options';
        wrap.appendChild(list);
        function render(){
          list.innerHTML = '';
          Array.from(sel.options).forEach(function(opt, idx){
            var div = document.createElement('div');
            div.className = 'mp-select-option';
            div.textContent = opt.textContent;
            if(sel.selectedIndex === idx) div.classList.add('active');
            div.addEventListener('click', function(e){
              e.stopPropagation();
              sel.selectedIndex = idx;
              sel.dispatchEvent(new Event('change', {bubbles:true}));
              closeAll();
            });
            list.appendChild(div);
          });
          var s = sel.options[sel.selectedIndex];
          trigger.textContent = s ? s.textContent : 'Select';
          trigger.classList.toggle('placeholder', !s || !s.value);
        }
        trigger.addEventListener('click', function(e){
          e.stopPropagation();
          closeAll();
          wrap.classList.toggle('open');
        });
        render();
      }
      function closeAll(){ document.querySelectorAll('.mp-select-wrap.open').forEach(function(w){ w.classList.remove('open'); }); }
      document.addEventListener('click', closeAll);
      document.querySelectorAll('select').forEach(buildMpSelect);
    })();

    function savePatient(){
      var name = document.getElementById('pt_name').value.trim();
      if(!name){ mpError('Patient name is required'); return; }
      var fd = new FormData();
      fd.append(CSRF_NAME, CSRF_HASH);
      fd.append('patient_id', document.getElementById('patient_id').value);
      fd.append('confirm_duplicate', document.getElementById('confirm_duplicate').value);
      fd.append('name', name);
      fd.append('mobile', document.getElementById('pt_mobile').value);
      fd.append('phone', document.getElementById('pt_phone').value);
      fd.append('email', document.getElementById('pt_email').value);
      fd.append('dob', document.getElementById('pt_dob').value);
      fd.append('gender', document.getElementById('pt_gender').value);
      fd.append('marital_status', document.getElementById('pt_marital').value);
      fd.append('blood_group', document.getElementById('pt_blood').value);
      fd.append('occupation', document.getElementById('pt_occupation').value);
      fd.append('address', document.getElementById('pt_address').value);
      fd.append('city', document.getElementById('pt_city').value);
      fd.append('nok_name', document.getElementById('pt_nok_name').value);
      fd.append('nok_phone', document.getElementById('pt_nok_phone').value);
      fd.append('nok_relationship', document.getElementById('pt_nok_rel').value);
      mpFetchJson('<?= base_url('mobile/save_patient'); ?>', { method: 'POST', body: fd })
        .then(function(d){
          if(d && d.status === 'success'){ location.href = '<?= base_url('mobile/patient_profile/'); ?>' + d.patient_id; }
          else if(d && d.status === 'duplicates'){
            var panel = document.getElementById('dupPanel');
            var html = '<b>Possible duplicate record(s):</b><ul>';
            (d.duplicates || []).forEach(function(x){ html += '<li>' + (x.customer_name || '') + ' — ' + (x.patient_code || '') + ' ' + (x.mobile || '') + '</li>'; });
            html += '</ul>Review before registering. Tap Save again to confirm this is a different person.';
            panel.innerHTML = html;
            panel.style.display = 'block';
            document.getElementById('confirm_duplicate').value = '1';
            panel.scrollIntoView({ behavior: 'smooth' });
          }
          else { mpError(d && d.message ? d.message : 'The record could not be saved. Please check the details and try again.'); }
        })
        .catch(function(err){ mpError(mpErrorText(err)); });
    }
  </script>
</body>
</html>
