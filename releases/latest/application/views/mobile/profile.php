<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?php
    /* AGENTS.md requires the STORE name in the header/title, but $SITE_TITLE is
       db_sitesettings.site_name — the installation's app name ("MartPoint
       Retail" on every tenant), not the store. The store's real name is already
       in the session. Prefer it and fall back to $SITE_TITLE. */
    $mp_store_name = $this->session->userdata('store_name');
    if (empty($mp_store_name)) { $mp_store_name = $SITE_TITLE ?? 'MartPoint'; }
    echo htmlspecialchars($mp_store_name); ?> — My Profile</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    /* Physiotherapy & rehabilitation palette — kept identical to
       mobile/clinic_home.php so this screen reads as part of the same clinic
       app rather than the retail shell. */
    :root {
      --mp-primary: #176753;
      --mp-primary-dark: #104d40;
      --mp-bg: #eef3f0;
      --mp-surface: #FFFFFF;
      --mp-text: #1e2d28;
      --mp-ink: #1e2d28;
      --mp-muted: #687a72;
      --mp-border: #d8e2dc;
      --mp-success: #176753;
      --mp-danger: #c95f49;
      --mp-warning: #a96e24;
      --safe-bottom: env(safe-area-inset-bottom, 0px);
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-bg); min-height: 100vh; position: relative; }
    .screen { padding: 16px 16px 120px; min-height: 100vh; }
    .topbar { display: flex; align-items: center; gap: 12px; padding: 16px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-family: 'DM Serif Display', Georgia, serif; font-weight: 400; font-size: 23px; margin: 0; }
    .profile-card { background: linear-gradient(135deg, var(--mp-primary) 0%, var(--mp-primary-dark) 100%); border-radius: 20px; padding: 28px 20px; text-align: center; color: #fff; margin-bottom: 20px; }
    .profile-card .avatar { width: 70px; height: 70px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 14px; overflow: hidden; }
    .profile-card .avatar img { width: 100%; height: 100%; object-fit: cover; }
    .profile-card .name { font-family: 'DM Serif Display', Georgia, serif; font-weight: 400; font-size: 22px; margin: 0; }
    .profile-card .sub { font-size: 14px; opacity: 0.85; margin-top: 4px; }
    .card { background: var(--mp-surface); border-radius: 14px; padding: 16px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .section-title { font-size: 12px; font-weight: 700; margin: 0 0 14px; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    .menu-item { display: flex; align-items: center; gap: 14px; padding: 16px 0; border-bottom: 1px solid var(--mp-border); text-decoration: none; color: var(--mp-ink); }
    .menu-item:last-child { border-bottom: none; }
    .menu-item .icon { width: 36px; height: 36px; border-radius: 10px; background: var(--mp-bg); color: var(--mp-primary); display: flex; align-items: center; justify-content: center; font-size: 16px; }
    .menu-item .text { flex: 1; }
    .menu-item .title { font-weight: 600; }
    .menu-item .desc { font-size: 13px; color: var(--mp-muted); margin-top: 2px; }
    .menu-item .arrow { color: var(--mp-muted); }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--mp-ink); }
    .form-group input { width: 100%; padding: 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 16px; background: #fff; }
    .btn { display: block; width: 100%; padding: 14px; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .btn-danger { background: #FEF2F2; color: var(--mp-danger); border: 1px solid #FECACA; }
    .btn-danger i { margin-right: 6px; }
    #toast { position: fixed; top: 16px; left: 50%; transform: translateX(-50%) translateY(-120%); max-width: 360px; width: calc(100% - 32px); padding: 14px 18px; border-radius: 14px; background: #0F172A; color: #fff; font-size: 14px; font-weight: 500; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 1000; opacity: 0; transition: all 0.3s ease; }
    #toast.active { transform: translateX(-50%) translateY(0); opacity: 1; }
    #toast.error { background: var(--mp-danger); }
    #toast.success { background: var(--mp-success); }
    .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; width: 100%; max-width: 430px; margin: 0 auto; background: rgba(255,255,255,0.96); backdrop-filter: blur(10px); border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 10px 0 calc(10px + var(--safe-bottom)); z-index: 100; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 6px 16px; border: none; background: transparent; color: var(--mp-muted); font-size: 11px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 22px; }
    .nav-item.active { color: var(--mp-primary); }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div id="app">
    <div class="topbar">
      <a href="<?= base_url('mobile'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
      <div class="topbar-titles">
        <div class="store-name"><?= htmlspecialchars($mp_store_name); ?></div>
        <h1>My Profile</h1>
      </div>
    </div>

    <section class="screen">
      <div class="profile-card">
        <div class="avatar" id="profileAvatar">
          <?php if(!empty($profile_picture)): ?>
            <img src="<?= base_url($profile_picture); ?>" alt="Profile">
          <?php else: ?>
            <?= strtoupper(substr($display_name,0,1)); ?>
          <?php endif; ?>
        </div>
        <div class="name"><?= $display_name; ?></div>
        <div class="sub"><?= $email; ?> · <?= $branch_name; ?></div>
        <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display:none;">
        <button type="button" class="btn" id="changeAvatarBtn" style="margin-top:16px; background:rgba(255,255,255,0.2); color:#fff; padding:10px 18px; border-radius:12px; font-size:13px; font-weight:600; border:none; display:inline-flex; align-items:center; gap:6px; width:auto; cursor:pointer;">
          <i class="fa fa-camera"></i> Change Photo
        </button>
      </div>

      <div class="card">
        <div class="section-title">Change Password</div>
        <form id="change_password_form">
          <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="old_password" id="old_password" placeholder="Enter current password" required>
          </div>
          <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" id="new_password" placeholder="At least 6 characters" required>
          </div>
          <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Re-enter new password" required>
          </div>
          <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
      </div>

      <div class="card">
        <a href="<?= base_url('logout'); ?>" class="btn btn-danger" onclick="return mpLogout(this, event);">
          <i class="fa fa-sign-out"></i> Log Out
        </a>
      </div>
    </section>


  </div>

  <div id="toast"></div>

  <script src="<?= $theme_link; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
  <script>
    function showToast(message, type){
      type = type || 'error';
      var $toast = document.getElementById('toast');
      $toast.className = type === 'error' ? 'error' : 'success';
      $toast.textContent = message;
      $toast.classList.add('active');
      setTimeout(function(){ $toast.classList.remove('active'); }, 3000);
    }

    $('#change_password_form').on('submit', function(e){
      e.preventDefault();
      $.ajax({
        url: '<?= base_url('mobile/update_password'); ?>',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(res){
          if(res.status === 'success'){
            showToast(res.message, 'success');
            document.getElementById('change_password_form').reset();
          } else {
            showToast(res.message, 'error');
          }
        },
        error: function(xhr){
          showToast(mpAjaxError(xhr, 'Could not update your password. Please try again.'), 'error');
        }
      });
    });

    var avatarInput = document.getElementById('avatarInput');
    var changeAvatarBtn = document.getElementById('changeAvatarBtn');
    changeAvatarBtn.addEventListener('click', function(){ avatarInput.click(); });
    avatarInput.addEventListener('change', function(){
      if(!this.files.length) return;
      var formData = new FormData();
      formData.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
      formData.append('avatar', this.files[0]);
      $.ajax({
        url: '<?= base_url('mobile/update_profile_picture'); ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res){
          if(res.status === 'success'){
            document.getElementById('profileAvatar').innerHTML = '<img src="' + res.url + '" alt="Profile">';
            showToast(res.message, 'success');
          } else {
            showToast(res.message, 'error');
          }
        },
        error: function(xhr){
          showToast(mpAjaxError(xhr, 'The photo could not be uploaded. Please try again.'), 'error');
        }
      });
    });
  </script>
  <?php if($this->session->flashdata('warning')): ?>
    <?php $flash_warning = trim(strip_tags(str_ireplace(['</p>','<br>','<br/>','<br />'], [' ',' ',' ',' '], $this->session->flashdata('warning')))); ?>
    <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($flash_warning, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: 'warning'});</script>
  <?php endif; ?>
  <?php if($this->session->flashdata('success')): ?>
    <?php $flash_success = trim(strip_tags(str_ireplace(['</p>','<br>','<br/>','<br />'], [' ',' ',' ',' '], $this->session->flashdata('success')))); ?>
    <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($flash_success, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: 'success'});</script>
  <?php endif; ?>
  <?php if($this->session->flashdata('failed')): ?>
    <?php $flash_failed = trim(strip_tags(str_ireplace(['</p>','<br>','<br/>','<br />'], [' ',' ',' ',' '], $this->session->flashdata('failed')))); ?>
    <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($flash_failed, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: 'danger'});</script>
  <?php endif; ?>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
