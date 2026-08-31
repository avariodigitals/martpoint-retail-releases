<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Users</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0;}
    .topbar .add-btn { width: 36px; height: 36px; border-radius: 50%; background: var(--mp-primary); color: #fff; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px; }
    .summary-card { background: linear-gradient(135deg, var(--mp-primary) 0%, var(--mp-primary-dark) 100%); border-radius: 16px; padding: 18px; color: #fff; margin-bottom: 16px; }
    .summary-card .label { font-size: 13px; opacity: 0.9; margin-bottom: 6px; }
    .summary-card .value { font-size: 26px; font-weight: 700; }
    .search-bar { display: flex; align-items: center; gap: 10px; background: #fff; border-radius: 14px; padding: 10px 14px; border: 1px solid var(--mp-border); margin-bottom: 12px; }
    .search-bar i { color: var(--mp-muted); font-size: 16px; }
    .search-bar input { border: none; background: transparent; flex: 1; font-size: 16px; outline: none; min-height: 28px; color: var(--mp-text); }
    .user-list { display: flex; flex-direction: column; gap: 12px; }
    .user-card { background: #fff; border-radius: 14px; padding: 14px; border: 1px solid var(--mp-border); }
    .user-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .user-title { flex: 1; min-width: 0; }
    .user-title .name { font-weight: 600; font-size: 16px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .user-title .role { font-size: 12px; color: var(--mp-muted); margin-top: 3px; }
    .badge { display: inline-block; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; flex-shrink: 0; }
    .badge.active { background: #D1FAE5; color: #065F46; }
    .badge.inactive { background: #FEE2E2; color: #991B1B; }
    .user-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; font-size: 12px; color: var(--mp-muted); }
    .user-meta span { display: inline-flex; align-items: center; gap: 4px; }
    .branch-list { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 8px; }
    .branch-pill { font-size: 10px; background: var(--mp-bg); color: var(--mp-ink); padding: 3px 8px; border-radius: 20px; }
    .user-actions { display: flex; gap: 8px; margin-top: 14px; }
    .action { flex: 1; text-align: center; padding: 9px 0; border-radius: 10px; background: var(--mp-bg); color: var(--mp-text); text-decoration: none; font-size: 12px; font-weight: 600; border: none; cursor: pointer; }
    .action i { margin-right: 4px; }
    .action.primary { background: #E0E7FF; color: var(--mp-primary); }
    .action.danger { background: #FEF2F2; color: var(--mp-danger); }
    .action.success { background: #ECFDF5; color: var(--mp-success); }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    .limit-notice { background: #FFFBEB; color: #92400E; padding: 10px 14px; border-radius: 12px; font-size: 13px; margin-bottom: 12px; }
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
        <a href="<?= base_url('mobile/more'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Users</h1>
        </div>
        <?php if($can_add): ?>
          <a href="<?= base_url('mobile/user_form'); ?>" class="add-btn"><i class="fa fa-plus"></i></a>
        <?php endif; ?>
      </div>

      <div class="summary-card">
        <div class="label">Active Users</div>
        <div class="value"><?= count($users); ?></div>
      </div>

      <?php if(!$can_add && $at_limit): ?>
        <div class="limit-notice"><i class="fa fa-exclamation-triangle"></i> User limit reached (<?= $user_used; ?>/<?= $user_limit; ?>)</div>
      <?php endif; ?>

      <div class="search-bar">
        <i class="fa fa-search"></i>
        <input type="text" id="user-search" placeholder="Search by name, role or mobile" autocomplete="off">
      </div>

      <div class="user-list">
        <?php if(!empty($users)): ?>
          <?php foreach($users as $u): ?>
            <div class="user-card" data-name="<?= strtolower(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')); ?>" data-role="<?= strtolower($u->role_name ?? ''); ?>" data-mobile="<?= strtolower($u->mobile ?? ''); ?>">
              <div class="user-header">
                <div class="user-title">
                  <div class="name"><?= htmlspecialchars(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')); ?></div>
                  <div class="role"><?= $u->role_name ?? '-'; ?></div>
                </div>
                <span class="badge <?= $u->status == 1 ? 'active' : 'inactive'; ?>" id="badge-<?= $u->id; ?>"><?= $u->status == 1 ? 'Active' : 'Inactive'; ?></span>
              </div>

              <div class="user-meta">
                <span><i class="fa fa-user"></i> <?= $u->username; ?></span>
                <?php if(!empty($u->mobile)): ?>
                  <span><i class="fa fa-phone"></i> <?= $u->mobile; ?></span>
                <?php endif; ?>
                <?php if(!empty($u->email)): ?>
                  <span><i class="fa fa-envelope"></i> <?= $u->email; ?></span>
                <?php endif; ?>
              </div>

              <?php if(!empty($u->warehouse_names)): ?>
                <div class="branch-list">
                  <?php foreach($u->warehouse_names as $w): ?>
                    <span class="branch-pill"><i class="fa fa-building"></i> <?= $w; ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <div class="user-actions">
                <?php if($can_edit && $u->id != 1): ?>
                  <a href="<?= base_url('mobile/user_form/' . $u->id); ?>" class="action primary"><i class="fa fa-edit"></i> Edit</a>
                <?php endif; ?>
                <?php if($can_edit && $u->id != 1 && $u->id != $current_user_id): ?>
                  <button type="button" class="action success" onclick="toggleStatus(<?= $u->id; ?>, <?= $u->status == 1 ? 0 : 1; ?>)"><i class="fa fa-power-off"></i> <span id="btn-text-<?= $u->id; ?>"><?= $u->status == 1 ? 'Deactivate' : 'Activate'; ?></span></button>
                <?php endif; ?>
                <?php if($can_delete && $u->id != 1 && $u->id != $current_user_id): ?>
                  <button type="button" class="action danger" onclick="deleteUser(<?= $u->id; ?>)"><i class="fa fa-trash"></i> Delete</button>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">
            <i class="fa fa-users" style="font-size:48px;display:block;margin-bottom:12px;color:var(--mp-border)"></i>
            <div>No users found.</div>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <script>
    var searchInput = document.getElementById('user-search');
    if(searchInput){
      searchInput.addEventListener('input', function(){
        var term = this.value.toLowerCase().trim();
        document.querySelectorAll('.user-card').forEach(function(el){
          var name = el.dataset.name || '';
          var role = el.dataset.role || '';
          var mobile = el.dataset.mobile || '';
          el.style.display = (name.indexOf(term) !== -1 || role.indexOf(term) !== -1 || mobile.indexOf(term) !== -1) ? 'block' : 'none';
        });
      });
    }

    function toggleStatus(id, status){
      mpConfirm(status == 1 ? 'Activate this user?' : 'Deactivate this user?', function(){
        var fd = new FormData();
        fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
        fd.append('id', id);
        fd.append('status', status);
        fetch('<?= base_url('users/status_update'); ?>', {method: 'POST', body: fd})
          .then(r => r.text())
          .then(function(res){
            if(res.trim() === 'success'){
              var badge = document.getElementById('badge-' + id);
              var btnText = document.getElementById('btn-text-' + id);
              if(status == 1){
                badge.textContent = 'Active';
                badge.className = 'badge active';
                if(btnText) { btnText.textContent = 'Deactivate'; btnText.parentElement.setAttribute('onclick', 'toggleStatus(' + id + ', 0)'); }
              } else {
                badge.textContent = 'Inactive';
                badge.className = 'badge inactive';
                if(btnText) { btnText.textContent = 'Activate'; btnText.parentElement.setAttribute('onclick', 'toggleStatus(' + id + ', 1)'); }
              }
            } else {
              mpAlert(res, 'danger');
            }
          });
      });
    }

    function deleteUser(id){
      mpConfirm('Delete this user permanently?', function(){
        var fd = new FormData();
        fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
        fd.append('q_id', id);
        fetch('<?= base_url('users/delete_user'); ?>', {method: 'POST', body: fd})
          .then(r => r.text())
          .then(function(res){
            if(res.trim() === 'success'){
              location.reload();
            } else {
              mpAlert(res, 'danger');
            }
          });
      }, null, {danger: true});
    }
  </script>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
