<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='utf-8'>
  <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate'>
  <meta http-equiv='Pragma' content='no-cache'>
  <meta http-equiv='Expires' content='0'>
  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'>
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Attributes</title>
  <link rel='preconnect' href='https://fonts.googleapis.com'>
  <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap' rel='stylesheet'>
  <link rel='stylesheet' href='<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css'>
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .topbar .add { background: var(--mp-primary); color: #fff; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px; }
    .search-bar { display: flex; align-items: center; gap: 10px; background: #fff; border-radius: 14px; padding: 10px 14px; border: 1px solid var(--mp-border); margin-bottom: 12px; }
    .search-bar i { color: var(--mp-muted); font-size: 16px; }
    .search-bar input { border: none; background: transparent; flex: 1; font-size: 16px; outline: none; min-height: 28px; color: var(--mp-text); }
    .search-bar input::placeholder { color: var(--mp-muted); }
    .attr-group { margin-bottom: 12px; }
    .attr-group-title { font-size: 13px; font-weight: 700; color: var(--mp-ink); text-transform: uppercase; letter-spacing: 0.4px; margin: 16px 0 8px; }
    .card { background: #fff; border-radius: 14px; padding: 0; margin-bottom: 12px; border: 1px solid var(--mp-border); overflow: hidden; }
    .attr-item { display: flex; justify-content: space-between; align-items: center; padding: 14px 12px; border-bottom: 1px solid var(--mp-border); }
    .attr-item:last-child { border-bottom: none; }
    .attr-item .left { flex: 1; min-width: 0; }
    .attr-item .value { font-weight: 600; font-size: 15px; }
    .attr-item .meta { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .attr-item .actions { display: flex; align-items: center; gap: 12px; }
    .attr-item .actions a { color: var(--mp-muted); font-size: 16px; text-decoration: none; }
    .attr-item .actions .edit { color: var(--mp-primary); }
    .attr-item .actions .delete { color: var(--mp-danger); }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    #toast { position: fixed; top: 16px; left: 50%; transform: translateX(-50%) translateY(-120%); max-width: 360px; width: calc(100% - 32px); padding: 14px 18px; border-radius: 14px; background: #0F172A; color: #fff; font-size: 14px; font-weight: 500; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 1000; opacity: 0; transition: all 0.3s ease; }
    #toast.active { transform: translateX(-50%) translateY(0); opacity: 1; }
    #toast.success { background: var(--mp-success); }
    #toast.error { background: var(--mp-danger); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 100px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 120px; } }
  </style>
</head>
<body>
  <div id='app'>
    <section class='screen'>
      <div class='topbar'>
        <a href='<?= base_url('mobile/more'); ?>' class='back'><i class='fa fa-chevron-left'></i></a>
        <div class='topbar-titles'>
          <div class='store-name'><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Attributes</h1>
        </div>
        <?php if(permissions('attributes_add')): ?>
          <a href='<?= base_url('mobile/attribute_form'); ?>' class='add'><i class='fa fa-plus'></i></a>
        <?php endif; ?>
      </div>

      <div class='search-bar'>
        <i class='fa fa-search'></i>
        <input type='text' id='attr-search' placeholder='Search type or value'>
      </div>

      <?php if(!empty($attributes)): ?>
        <?php $current_type = null; $group_open = false; ?>
        <?php foreach($attributes as $attr):
          if($attr->attribute_type !== $current_type):
            if($group_open) echo "</div>";
            $current_type = $attr->attribute_type;
            $group_open = true;
        ?>
          <div class='attr-group' data-type='<?= strtolower(htmlspecialchars($current_type ?? '')); ?>'>
            <div class='attr-group-title'><?= htmlspecialchars(ucfirst($current_type)); ?></div>
            <div class='card'>
        <?php endif; ?>
              <div class='attr-item' data-search='<?= strtolower(htmlspecialchars($attr->attribute_type ?? '').' '.htmlspecialchars($attr->attribute_value ?? '')); ?>'>
                <div class='left'>
                  <div class='value'><?= htmlspecialchars($attr->attribute_value); ?></div>
                  <div class='meta'>Sort: <?= (int)$attr->sort_order; ?></div>
                </div>
                <div class='actions'>
                  <?php if(permissions('attributes_edit')): ?>
                    <a href='<?= base_url('mobile/attribute_form/'.$attr->id); ?>' class='edit'><i class='fa fa-edit'></i></a>
                  <?php endif; ?>
                  <?php if(permissions('attributes_delete')): ?>
                    <a href='javascript:void(0)' class='delete' onclick='deleteAttribute(<?= (int)$attr->id; ?>)'><i class='fa fa-trash'></i></a>
                  <?php endif; ?>
                </div>
              </div>
        <?php endforeach; ?>
        <?php if($group_open) echo "</div></div>"; ?>
      <?php else: ?>
        <div class='empty-state'>
          <i class='fa fa-cogs'></i>
          <div>No attributes found.</div>
        </div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <div id='toast'></div>

  <script>
    var searchInput = document.getElementById('attr-search');
    if(searchInput){
      searchInput.addEventListener('input', function(){
        var term = this.value.toLowerCase().trim();
        document.querySelectorAll('.attr-group').forEach(function(group){
          var any = false;
          group.querySelectorAll('.attr-item').forEach(function(item){
            var match = (item.dataset.search || '').indexOf(term) !== -1;
            item.style.display = match ? 'flex' : 'none';
            if(match) any = true;
          });
          group.style.display = any ? 'block' : 'none';
        });
      });
    }

    function showToast(message, type){
      var toast = document.getElementById('toast');
      toast.textContent = message;
      toast.className = type === 'success' ? 'success' : 'error';
      toast.classList.add('active');
      setTimeout(function(){ toast.classList.remove('active'); }, 3000);
    }

    function deleteAttribute(id){
      mpConfirm('Delete this attribute?', function(){
        var formData = new FormData();
        formData.append('q_id', id);
        formData.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
        fetch('<?= base_url('mobile/delete_attribute'); ?>', {
          method: 'POST',
          body: formData
        })
        .then(function(res){ return res.text(); })
        .then(function(text){
          if(text.indexOf('success') !== -1){
            showToast('Attribute deleted.', 'success');
            setTimeout(function(){ window.location.reload(); }, 600);
          } else {
            showToast(text || 'Delete failed.', 'error');
          }
        })
        .catch(function(){
          showToast('Network error.', 'error');
        });
      }, null, {danger: true});
    }
  </script>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>