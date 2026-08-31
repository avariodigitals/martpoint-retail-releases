<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — <?= !empty($q_id) ? 'Edit Customer' : 'Add Customer'; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-purple: #7C3AED; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0;}
    .section-title { font-size: 15px; font-weight: 700; margin: 20px 0 10px; color: var(--mp-text); display: flex; align-items: center; gap: 8px; }
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .form-group { margin-bottom: 14px; }
    .form-group:last-child { margin-bottom: 0; }
    label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--mp-text); }
    label .req { color: var(--mp-danger); }
    input[type="text"], input[type="email"], input[type="tel"], input[type="number"], input[type="date"], input[type="file"], textarea, select {
      width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; font-family: inherit; background: #fff; color: var(--mp-text); outline: none;
    }
    input:focus, textarea:focus, select:focus { border-color: var(--mp-primary); }
    textarea { min-height: 80px; resize: vertical; }
    .help-text { font-size: 11px; color: var(--mp-muted); margin-top: 4px; }
    .btn { width: 100%; padding: 14px; border: none; border-radius: 12px; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; }
    .btn:active { background: var(--mp-primary-dark); }
    .btn-danger { background: var(--mp-danger); }
    .alert { padding: 12px 14px; border-radius: 12px; margin-bottom: 14px; font-size: 13px; font-weight: 500; }
    .alert-success { background: #ECFDF5; color: #065F46; }
    .alert-danger { background: #FEF2F2; color: #B91C1C; }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    .checkbox-row { display: flex; align-items: center; gap: 8px; margin-bottom: 14px; }
    .checkbox-row input { width: auto; }
    .checkbox-row label { margin-bottom: 0; }
    @media (max-width: 360px) { .form-row { grid-template-columns: 1fr; } }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .bottom-nav { max-width: 100%; left: 0; right: 0; transform: none; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 140px; } }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
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
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/customers'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= !empty($q_id) ? 'Edit Customer' : 'Add Customer'; ?></h1>
        </div>
      </div>

      <?php if($this->session->flashdata('success')): ?>
        <?php $flash_success = trim(strip_tags(str_ireplace(['</p>','<br>','<br/>','<br />'], [' ',' ',' ',' '], $this->session->flashdata('success')))); ?>
        <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($flash_success, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: 'success'});</script>
      <?php endif; ?>
      <?php if($this->session->flashdata('failed')): ?>
        <?php $flash_failed = trim(strip_tags(str_ireplace(['</p>','<br>','<br/>','<br />'], [' ',' ',' ',' '], $this->session->flashdata('failed')))); ?>
        <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($flash_failed, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: 'danger'});</script>
      <?php endif; ?>

      <?php
        $q_id = $q_id ?? '';
        $customer_name = $customer_name ?? '';
        $mobile = $mobile ?? '';
        $phone = $phone ?? '';
        $email = $email ?? '';
        $gstin = $gstin ?? '';
        $tax_number = $tax_number ?? '';
        $country_id = $country_id ?? '';
        $state_id = $state_id ?? '';
        $city = $city ?? '';
        $postcode = $postcode ?? '';
        $address = $address ?? '';
        $location_link = $location_link ?? '';
        $opening_balance = $opening_balance ?? 0;
        $credit_limit = $credit_limit ?? '0';
        $price_level_type = $price_level_type ?? 'Increase';
        $price_level = $price_level ?? '0';
        $birthday = $birthday ?? '';
        $notes = $notes ?? '';
        $shipping_country = $shipping_country ?? '';
        $shipping_state = $shipping_state ?? '';
        $shipping_city = $shipping_city ?? '';
        $shipping_postcode = $shipping_postcode ?? '';
        $shipping_address = $shipping_address ?? '';
        $shipping_location_link = $shipping_location_link ?? '';
        $nin_bvn = $nin_bvn ?? '';
        $nin_verified = $nin_verified ?? 0;

        $country_name = '';
        if(!empty($country_id)){
          $cr = get_country_details($country_id);
          if($cr) $country_name = $cr->country;
        }
        $shipping_country_name = '';
        if(!empty($shipping_country)){
          $cr = get_country_details($shipping_country);
          if($cr) $shipping_country_name = $cr->country;
        }
      ?>

      <form action="<?= base_url('mobile/save_customer'); ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="store_id" id="store_id" value="<?= get_current_store_id(); ?>">
        <input type="hidden" name="q_id" value="<?= $q_id; ?>">

        <div class="card">
          <div class="form-group">
            <label>Customer Name <span class="req">*</span></label>
            <input type="text" name="customer_name" value="<?= $customer_name; ?>" placeholder="Full name" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Mobile <span class="req">*</span></label>
              <input type="tel" name="mobile" value="<?= $mobile; ?>" placeholder="+234..." required>
            </div>
            <div class="form-group">
              <label>Phone</label>
              <input type="tel" name="phone" value="<?= $phone; ?>" placeholder="Alternate phone">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" value="<?= $email; ?>" placeholder="email@example.com">
            </div>
            <div class="form-group">
              <label>Birthday</label>
              <input type="date" name="birthday" value="<?= $birthday; ?>">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Tax</label>
              <input type="text" name="gstin" value="<?= $gstin; ?>" placeholder="Tax Number">
            </div>
            <div class="form-group">
              <label>Tax Number</label>
              <input type="text" name="tax_number" value="<?= $tax_number; ?>" placeholder="Tax #">
            </div>
          </div>
        </div>

        <div class="section-title"><i class="fa fa-map-marker"></i> Billing Address</div>
        <div class="card">
          <div class="form-row">
            <div class="form-group">
              <label>Country</label>
              <select name="country" id="country">
                <option value="">Select Country</option>
                <?= get_country_select_list($country_id, false); ?>
              </select>
            </div>
            <div class="form-group">
              <label>State</label>
              <select name="state" id="state">
                <option value="">Select State</option>
                <?= get_state_select_list_by_country($country_name, $state_id); ?>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>City</label>
              <select name="city" id="city">
                <option value="">Select City</option>
                <?= get_city_select_list($state_id, $city); ?>
              </select>
            </div>
            <div class="form-group">
              <label>Postcode</label>
              <input type="text" name="postcode" value="<?= $postcode; ?>" placeholder="Postcode">
            </div>
          </div>
          <div class="form-group">
            <label>Address</label>
            <textarea name="address" placeholder="Customer address"><?= $address; ?></textarea>
          </div>
          <div class="form-group">
            <label>Location Link</label>
            <input type="text" name="location_link" value="<?= $location_link; ?>" placeholder="Google Maps link">
          </div>
        </div>

        <div class="checkbox-row">
          <input type="checkbox" id="copy_address" name="copy_address">
          <label for="copy_address" style="font-weight:400;">Copy billing address to shipping address</label>
        </div>

        <div class="section-title"><i class="fa fa-truck"></i> Shipping Address</div>
        <div class="card">
          <div class="form-row">
            <div class="form-group">
              <label>Country</label>
              <select name="shipping_country" id="shipping_country">
                <option value="">Select Country</option>
                <?= get_country_select_list($shipping_country, false); ?>
              </select>
            </div>
            <div class="form-group">
              <label>State</label>
              <select name="shipping_state" id="shipping_state">
                <option value="">Select State</option>
                <?= get_state_select_list_by_country($shipping_country_name, $shipping_state); ?>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>City</label>
              <select name="shipping_city" id="shipping_city">
                <option value="">Select City</option>
                <?= get_city_select_list($shipping_state, $shipping_city); ?>
              </select>
            </div>
            <div class="form-group">
              <label>Postcode</label>
              <input type="text" name="shipping_postcode" value="<?= $shipping_postcode; ?>" placeholder="Postcode">
            </div>
          </div>
          <div class="form-group">
            <label>Address</label>
            <textarea name="shipping_address" id="shipping_address" placeholder="Shipping address"><?= $shipping_address; ?></textarea>
          </div>
          <div class="form-group">
            <label>Location Link</label>
            <input type="text" name="shipping_location_link" id="shipping_location_link" value="<?= $shipping_location_link; ?>" placeholder="Google Maps link">
          </div>
        </div>

        <div class="section-title"><i class="fa fa-gears"></i> Advanced Settings</div>
        <div class="card">
          <div class="form-row">
            <div class="form-group">
              <label>Opening Balance</label>
              <input type="number" step="0.01" name="opening_balance" value="<?= $opening_balance; ?>" placeholder="0">
            </div>
            <div class="form-group">
              <label>Credit Limit</label>
              <input type="number" step="0.01" name="credit_limit" value="<?= $credit_limit; ?>" placeholder="0">
              <div class="help-text">0 = no credit, -1 = no limit</div>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Price Level Type</label>
              <select name="price_level_type" id="price_level_type">
                <option value="Increase" <?= $price_level_type == 'Increase' ? 'selected' : ''; ?>>Increase</option>
                <option value="Decrease" <?= $price_level_type == 'Decrease' ? 'selected' : ''; ?>>Decrease</option>
              </select>
            </div>
            <div class="form-group">
              <label>Price Level (%)</label>
              <input type="number" step="0.01" name="price_level" value="<?= $price_level; ?>" placeholder="0">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>NIN / BVN</label>
              <input type="text" name="nin_bvn" value="<?= $nin_bvn; ?>" maxlength="11" placeholder="Enter NIN or BVN">
            </div>
            <div class="form-group">
              <label>NIN / BVN Verified</label>
              <select name="nin_verified">
                <option value="0" <?= $nin_verified == 0 ? 'selected' : ''; ?>>No</option>
                <option value="1" <?= $nin_verified == 1 ? 'selected' : ''; ?>>Yes</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Attachment</label>
            <?php if(!empty($attachment_1)): ?>
              <div class="help-text"><a href="<?= base_url($attachment_1); ?>" target="_blank">View current attachment</a></div>
            <?php endif; ?>
            <input type="file" name="attachment_1" accept=".gif,.jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx,.txt">
          </div>
        </div>

        <div class="section-title"><i class="fa fa-sticky-note"></i> Notes</div>
        <div class="card">
          <div class="form-group">
            <textarea name="notes" placeholder="Customer notes"><?= $notes; ?></textarea>
          </div>
        </div>

        <button type="submit" class="btn"><i class="fa fa-save"></i> <?= !empty($q_id) ? 'Update Customer' : 'Save Customer'; ?></button>
        <?php if(!empty($q_id)): ?>
          <a href="<?= base_url('mobile/customer_profile/' . $q_id); ?>" class="btn" style="display:block; text-align:center; margin-top:10px; background:var(--mp-muted); color:#fff; text-decoration:none;"><i class="fa fa-user"></i> View Profile</a>
        <?php endif; ?>
      </form>
    </section>


  </div>

  <script>
  (function(){
    var tokenName = '<?= $this->security->get_csrf_token_name(); ?>';
    var token = '<?= $this->security->get_csrf_hash(); ?>';
    function buildMpSelect(sel){
      if(sel.dataset.mpBuilt) return;
      sel.dataset.mpBuilt = '1';
      sel.classList.add('mp-select');
      sel.style.display = 'none';
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
            sel.dispatchEvent(new Event('mpupdate', {bubbles:true}));
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
      sel.addEventListener('mpupdate', render);
      render();
    }
    function setSelectOptions(sel, html, val){
      sel.innerHTML = html;
      if(val && sel.querySelector('option[value="'+val+'"]')){
        sel.value = val;
      } else if(sel.options.length){
        sel.selectedIndex = 0;
      }
      buildMpSelect(sel);
      sel.dispatchEvent(new Event('change', {bubbles:true}));
      sel.dispatchEvent(new Event('mpupdate', {bubbles:true}));
    }
    function closeAll(){ document.querySelectorAll('.mp-select-wrap.open').forEach(function(w){ w.classList.remove('open'); }); }
    document.addEventListener('click', closeAll);
    document.querySelectorAll('select').forEach(buildMpSelect);
    function loadStates(countrySel, stateSel, citySel){
      var selectedText = countrySel.options[countrySel.selectedIndex] ? countrySel.options[countrySel.selectedIndex].text : '';
      if(!countrySel.value || selectedText === 'Select Country' || selectedText === ''){
        setSelectOptions(stateSel, '<option value="">Select State</option>', '');
        if(citySel) setSelectOptions(citySel, '<option value="">Select City</option>', '');
        return;
      }
      var fd = new FormData();
      fd.append(tokenName, token);
      fd.append('country', selectedText);
      fetch('<?= base_url('site/get_states_by_country'); ?>', {method:'POST', body:fd})
      .then(function(r){ return r.json(); })
      .then(function(data){
        var html = '<option value="">Select State</option>';
        data.forEach(function(s){ html += '<option value="'+s.id+'">'+s.state+'</option>'; });
        setSelectOptions(stateSel, html, '');
        if(citySel) setSelectOptions(citySel, '<option value="">Select City</option>', '');
      });
    }
    function loadCities(stateSel, citySel){
      var stateId = stateSel.value;
      if(!stateId){ setSelectOptions(citySel, '<option value="">Select City</option>', ''); return; }
      var fd = new FormData();
      fd.append(tokenName, token);
      fd.append('state_id', stateId);
      fetch('<?= base_url('site/get_cities_by_state'); ?>', {method:'POST', body:fd})
      .then(function(r){ return r.json(); })
      .then(function(data){
        var html = '<option value="">Select City</option>';
        data.forEach(function(c){ html += '<option value="'+c.city+'">'+c.city+'</option>'; });
        setSelectOptions(citySel, html, '');
      });
    }
    function wireCascade(countryId, stateId, cityId){
      var c = document.getElementById(countryId);
      var s = document.getElementById(stateId);
      var t = document.getElementById(cityId);
      if(!c || !s || !t) return;
      c.addEventListener('change', function(){ loadStates(c, s, t); });
      s.addEventListener('change', function(){ loadCities(s, t); });
    }
    wireCascade('country','state','city');
    wireCascade('shipping_country','shipping_state','shipping_city');
    var copyChk = document.getElementById('copy_address');
    if(copyChk){
      copyChk.addEventListener('change', function(){
        if(this.checked){
          document.getElementById('shipping_address').value = document.querySelector('textarea[name="address"]').value;
          document.getElementById('shipping_location_link').value = document.querySelector('input[name="location_link"]').value;
          document.getElementById('shipping_postcode').value = document.querySelector('input[name="postcode"]').value;
          var c = document.getElementById('country'); var sc = document.getElementById('shipping_country');
          sc.innerHTML = c.innerHTML; sc.value = c.value; sc.dispatchEvent(new Event('mpupdate', {bubbles:true}));
          var s = document.getElementById('state'); var ss = document.getElementById('shipping_state');
          ss.innerHTML = s.innerHTML; ss.value = s.value; ss.dispatchEvent(new Event('mpupdate', {bubbles:true}));
          var t = document.getElementById('city'); var st = document.getElementById('shipping_city');
          st.innerHTML = t.innerHTML; st.value = t.value; st.dispatchEvent(new Event('mpupdate', {bubbles:true}));
        }
      });
    }
  })();
  </script>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
