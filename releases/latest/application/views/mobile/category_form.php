<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='utf-8'>
  <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate'>
  <meta http-equiv='Pragma' content='no-cache'>
  <meta http-equiv='Expires' content='0'>
  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'>
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Add Category</title>
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
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .form-group { margin-bottom: 16px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--mp-ink); }
    .form-group .req { color: var(--mp-danger); }
    .form-group input, .form-group textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; color: var(--mp-text); outline: none; }
    .form-group textarea { resize: vertical; min-height: 80px; }
    .btn { display: block; width: 100%; padding: 14px; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .btn-secondary { background: var(--mp-bg); color: var(--mp-primary); border: 1px solid var(--mp-border); margin-top: 12px; }
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
          <h1>Add Category</h1>
        </div>
      </div>

      <form id='category-form' action='<?= base_url('mobile/save_category'); ?>' method='post' enctype='multipart/form-data'>
        <input type='hidden' name='<?= $this->security->get_csrf_token_name(); ?>' value='<?= $this->security->get_csrf_hash(); ?>'>

        <div class='card'>
          <div class='form-group'>
            <label>Category Name <span class='req'>*</span></label>
            <input type='text' name='category' id='category' value='<?= htmlspecialchars($category_name); ?>' placeholder='e.g. Beverages, Electronics' required>
          </div>
          <div class='form-group'>
            <label>Description</label>
            <textarea name='description' id='description' placeholder='Optional notes'><?= htmlspecialchars($description); ?></textarea>
          </div>
        </div>

        <button type='submit' class='btn btn-primary' id='btn-save'>Save Category</button>
        <a href='<?= base_url('mobile/more'); ?>' class='btn btn-secondary'>Cancel</a>
      </form>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <div id='toast'></div>

  <script>
    var form = document.getElementById('category-form');
    form.addEventListener('submit', function(e){
      e.preventDefault();
      var btn = document.getElementById('btn-save');
      btn.disabled = true;
      btn.textContent = 'Saving...';

      var formData = new FormData(form);
      fetch('<?= base_url('mobile/save_category'); ?>', {
        method: 'POST',
        body: formData
      })
      .then(function(res){ return res.json(); })
      .then(function(data){
        btn.disabled = false;
        btn.textContent = 'Save Category';
        if(data.status === 'success'){
          showToast(data.message, 'success');
          setTimeout(function(){ window.location.href = data.redirect || '<?= base_url('mobile/more'); ?>'; }, 800);
        } else {
          showToast(data.message || 'Save failed.', 'error');
        }
      })
      .catch(function(){
        btn.disabled = false;
        btn.textContent = 'Save Category';
        showToast('Network or server error. Try again.', 'error');
      });
    });

    function showToast(message, type){
      var toast = document.getElementById('toast');
      toast.textContent = message;
      toast.className = type === 'success' ? 'success' : 'error';
      toast.classList.add('active');
      setTimeout(function(){ toast.classList.remove('active'); }, 3000);
    }
  </script>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
