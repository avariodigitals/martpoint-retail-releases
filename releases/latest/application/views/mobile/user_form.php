<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — <?= $page_title; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0;}
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .form-group { margin-bottom: 14px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--mp-ink); }
    .form-group input[type="text"], .form-group input[type="email"], .form-group input[type="password"], .form-group input[type="file"], .form-group input[type="number"] { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; color: var(--mp-text); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .req { color: var(--mp-danger); }
    .hint { font-size: 12px; color: var(--mp-muted); margin-top: 4px; display: block; }
    .btn { display: block; width: 100%; padding: 14px; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .checkbox-group { display: flex; flex-direction: column; gap: 8px; }
    .checkbox-group label { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: var(--mp-bg); border-radius: 10px; font-size: 14px; font-weight: 500; cursor: pointer; }
    .checkbox-group input { width: 18px; height: 18px; accent-color: var(--mp-primary); }
    .mp-select { display: none; }
    .mp-select-wrap { position: relative; width: 100%; }
    .mp-select-trigger { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; color: var(--mp-text); cursor: pointer; display: flex; align-items: center; justify-content: space-between; min-height: 46px; }
    .mp-select-trigger::after { content: '\f0d7'; font-family: 'FontAwesome'; color: var(--mp-muted); font-size: 14px; }
    .mp-select-trigger.placeholder { color: var(--mp-muted); }
    .mp-select-options { display: none; border: 1px solid var(--mp-border); border-top: none; border-radius: 0 0 12px 12px; background: #fff; max-height: 220px; overflow-y: auto; position: relative; z-index: 10; }
    .mp-select-wrap.open .mp-select-options { display: block; }
    .mp-select-wrap.open .mp-select-trigger { border-radius: 12px 12px 0 0; }
    .mp-select-option { padding: 12px 14px; cursor: pointer; border-bottom: 1px solid var(--mp-border); font-size: 15px; }
    .mp-select-option:last-child { border-bottom: none; }
    .mp-select-option:hover, .mp-select-option.active { background: var(--mp-bg); }
    #toast { position: fixed; top: 16px; left: 50%; transform: translateX(-50%) translateY(-120%); max-width: 360px; width: calc(100% - 32px); padding: 14px 18px; border-radius: 14px; background: var(--mp-danger); color: #fff; font-size: 14px; font-weight: 500; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 1000; opacity: 0; transition: all 0.3s ease; }
    #toast.active { transform: translateX(-50%) translateY(0); opacity: 1; }
    #toast.success { background: var(--mp-success); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 120px; } }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/users'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= $page_title; ?></h1>
        </div>
      </div>

      <form id="user-form" class="card" enctype="multipart/form-data">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="q_id" value="<?= $q_id ?? ''; ?>">
        <input type="hidden" name="command" value="<?= !empty($q_id) ? 'update' : 'save'; ?>">
        <input type="hidden" name="store_id" value="<?= $store_id ?? get_current_store_id(); ?>">

        <div class="form-group">
          <label>Username <span class="req">*</span></label>
          <input type="text" name="username" value="<?= $username ?? ''; ?>" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>First Name <span class="req">*</span></label>
            <input type="text" name="new_user" value="<?= $first_name ?? ''; ?>" required>
          </div>
          <div class="form-group">
            <label>Last Name <span class="req">*</span></label>
            <input type="text" name="last_name" value="<?= $last_name ?? ''; ?>" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Mobile</label>
            <input type="text" name="mobile" value="<?= $mobile ?? ''; ?>">
          </div>
          <div class="form-group">
            <label>Email <span class="req">*</span></label>
            <input type="email" name="email" value="<?= $email ?? ''; ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label>Role <span class="req">*</span></label>
          <select class="mp-select" name="role_id" id="role_id" required>
            <option value="">Select</option>
            <?= get_roles_select_list($role_id ?? '', $store_id ?? get_current_store_id()); ?>
          </select>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Password <?php if(empty($q_id)): ?><span class="req">*</span><?php endif; ?></label>
            <input type="password" name="pass" id="pass" <?php if(empty($q_id)): ?>required<?php endif; ?>>
          </div>
          <div class="form-group">
            <label>Confirm Password <?php if(empty($q_id)): ?><span class="req">*</span><?php endif; ?></label>
            <input type="password" name="confirm" id="confirm" <?php if(empty($q_id)): ?>required<?php endif; ?>>
          </div>
        </div>

        <?php if(warehouse_module() && warehouse_count() > 0): ?>
        <div class="form-group" id="default-warehouse-group">
          <label>Default Branch <span class="req">*</span></label>
          <select class="mp-select" name="default_warehouse_id" id="default_warehouse_id" required>
            <?= get_warehouse_select_list($default_warehouse_id ?? '', $store_id ?? get_current_store_id(), true); ?>
          </select>
        </div>

        <div class="form-group" id="warehouses-group">
          <label>Assigned Branches <span class="req">*</span></label>
          <div class="checkbox-group">
            <?php foreach($warehouses as $wh): ?>
              <label>
                <input type="checkbox" name="warehouses[]" value="<?= $wh->id; ?>" <?= in_array($wh->id, $user_warehouse_ids ?? []) ? 'checked' : ''; ?>>
                <?= $wh->warehouse_name; ?>
              </label>
            <?php endforeach; ?>
          </div>
          <small class="hint">Default branch must be selected here.</small>
        </div>
        <?php else: ?>
          <input type="hidden" name="default_warehouse_id" value="<?= get_store_warehouse_id(); ?>">
        <?php endif; ?>

        <div class="form-group">
          <label>Profile Picture</label>
          <input type="file" name="profile_picture" accept="image/*">
          <small class="hint">Max 500x500, 500KB, jpg/png/gif</small>
          <?php if(!empty($profile_picture) && file_exists(FCPATH . $profile_picture)): ?>
            <img src="<?= base_url($profile_picture); ?>" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:10px;margin-top:10px;border:1px solid var(--mp-border);">
          <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary" id="save-btn"><i class="fa fa-save"></i> Save</button>
      </form>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <div id="toast"></div>

  <script>
    function initMpSelects(){
      document.querySelectorAll('select.mp-select').forEach(function(sel){
        if(sel.dataset.mpInit) return;
        sel.dataset.mpInit = '1';
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
        var options = Array.from(sel.options);
        function renderOptions(){
          list.innerHTML = '';
          options.forEach(function(opt, idx){
            var div = document.createElement('div');
            div.className = 'mp-select-option';
            div.textContent = opt.textContent;
            if(sel.selectedIndex === idx) div.classList.add('active');
            div.addEventListener('click', function(e){
              e.stopPropagation();
              sel.selectedIndex = idx;
              updateTrigger();
              sel.dispatchEvent(new Event('change', {bubbles: true}));
              closeAllMpSelects();
            });
            list.appendChild(div);
          });
        }
        function updateTrigger(){
          var s = sel.options[sel.selectedIndex];
          trigger.textContent = s ? s.textContent : 'Select';
          trigger.classList.toggle('placeholder', !s || !s.value);
          renderOptions();
        }
        trigger.addEventListener('click', function(e){
          e.stopPropagation();
          closeAllMpSelects();
          wrap.classList.toggle('open');
        });
        sel.addEventListener('change', updateTrigger);
        updateTrigger();
      });
      document.addEventListener('click', closeAllMpSelects);
    }
    function closeAllMpSelects(){
      document.querySelectorAll('.mp-select-wrap.open').forEach(function(w){ w.classList.remove('open'); });
    }
    initMpSelects();

    var form = document.getElementById('user-form');
    var toast = document.getElementById('toast');
    function showToast(msg, type){
      toast.textContent = msg;
      toast.className = 'active ' + (type || '');
      setTimeout(function(){ toast.classList.remove('active'); }, 4000);
    }

    function hideIfAdmin(){
      var role = document.getElementById('role_id');
      var defaultGroup = document.getElementById('default-warehouse-group');
      var whGroup = document.getElementById('warehouses-group');
      if(!role || !defaultGroup) return;
      var adminIds = ['1', '<?= store_admin_id(); ?>'];
      if(adminIds.indexOf(role.value) !== -1){
        defaultGroup.style.display = 'none';
        if(whGroup) whGroup.style.display = 'none';
      } else {
        defaultGroup.style.display = 'block';
        if(whGroup) whGroup.style.display = 'block';
      }
    }
    document.getElementById('role_id').addEventListener('change', hideIfAdmin);
    hideIfAdmin();

    form.addEventListener('submit', function(e){
      e.preventDefault();
      var btn = document.getElementById('save-btn');
      btn.disabled = true;
      btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
      var fd = new FormData(form);
      fetch('<?= base_url('users/save_or_update'); ?>', {method: 'POST', body: fd})
        .then(function(r){ return r.text(); })
        .then(function(res){
          if(res.trim() === 'success' || res.indexOf('success') !== -1){
            showToast('Saved successfully', 'success');
            setTimeout(function(){ window.location.href = '<?= base_url('mobile/users'); ?>'; }, 800);
          } else {
            showToast(res, '');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-save"></i> Save';
          }
        })
        .catch(function(err){
          showToast('Network error: ' + err, '');
          btn.disabled = false;
          btn.innerHTML = '<i class="fa fa-save"></i> Save';
        });
    });
  </script>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
