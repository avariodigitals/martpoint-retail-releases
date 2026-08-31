<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= !empty($banner) ? 'Edit Banner' : 'Add Banner'; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.04); margin-bottom: 16px; }
    .card-title { font-size: 15px; font-weight: 700; padding: 14px 16px; border-bottom: 1px solid var(--mp-border); }
    .form-group { padding: 14px 16px; border-bottom: 1px solid var(--mp-border); }
    .form-group:last-child { border-bottom: none; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--mp-ink); margin-bottom: 6px; }
    .form-control { width: 100%; padding: 12px 14px; border-radius: 12px; border: 1px solid var(--mp-border); font-family: inherit; font-size: 14px; }
    .form-control:focus { border-color: var(--mp-primary); outline: none; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .choice-group { display: flex; flex-wrap: wrap; gap: 8px; }
    .choice { padding: 10px 14px; border-radius: 20px; border: 1px solid var(--mp-border); background: #fff; font-size: 13px; font-weight: 600; color: var(--mp-ink); cursor: pointer; }
    .choice input { position: absolute; opacity: 0; }
    .choice:has(input:checked) { background: var(--mp-primary); color: #fff; border-color: var(--mp-primary); }
    .preview { width: 100%; max-height: 160px; object-fit: cover; border-radius: 12px; margin-bottom: 10px; border: 1px solid var(--mp-border); }
    .btn { display: block; width: 100%; padding: 14px; border-radius: 12px; border: none; font-weight: 600; font-size: 15px; cursor: pointer; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 140px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= $back_url; ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= !empty($banner) ? 'Edit Banner' : 'Add Banner'; ?></h1>
        </div>
      </div>

      <form id="banner-form" onsubmit="return false;" enctype="multipart/form-data">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="banner_id" value="<?= $banner ? $banner->id : ''; ?>">

        <div class="card">
          <div class="card-title">Banner Details</div>
          <div class="form-group">
            <label class="form-label">Banner Type</label>
            <div class="choice-group">
              <label class="choice"><input type="radio" name="banner_type" value="hero" <?= ($banner->banner_type ?? 'hero') == 'hero' ? 'checked' : ''; ?>>Hero</label>
              <label class="choice"><input type="radio" name="banner_type" value="promo" <?= ($banner->banner_type ?? '') == 'promo' ? 'checked' : ''; ?>>Promo</label>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" name="banner_title" class="form-control" value="<?= htmlspecialchars($banner->banner_title ?? ''); ?>" placeholder="Banner title">
          </div>
          <div class="form-group">
            <label class="form-label">Subtitle</label>
            <input type="text" name="banner_subtitle" class="form-control" value="<?= htmlspecialchars($banner->banner_subtitle ?? ''); ?>" placeholder="Banner subtitle">
          </div>
          <div class="form-group">
            <label class="form-label">Button Text</label>
            <input type="text" name="button_text" class="form-control" value="<?= htmlspecialchars($banner->button_text ?? ''); ?>" placeholder="Shop now">
          </div>
          <div class="form-group">
            <label class="form-label">Button URL</label>
            <input type="text" name="button_url" class="form-control" value="<?= htmlspecialchars($banner->button_url ?? ''); ?>" placeholder="/store/products">
          </div>
          <div class="form-group">
            <label class="form-label">Display Order</label>
            <input type="number" name="display_order" class="form-control" value="<?= (int)($banner->display_order ?? 0); ?>">
          </div>
          <div class="form-group">
            <div class="form-row">
              <div>
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= $banner->start_date ?? ''; ?>">
              </div>
              <div>
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= $banner->end_date ?? ''; ?>">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="choice" style="padding:0;border:none;background:transparent;"><input type="checkbox" name="status" value="1" <?= (!isset($banner) || ($banner && $banner->status)) ? 'checked' : ''; ?>> Active</label>
          </div>
          <div class="form-group">
            <label class="form-label">Desktop Image</label>
            <?php if(!empty($banner->desktop_image) && file_exists($banner->desktop_image)): ?>
              <img src="<?= base_url($banner->desktop_image); ?>" class="preview" alt="">
            <?php endif; ?>
            <input type="file" name="desktop_image" class="form-control" accept="image/*">
          </div>
          <div class="form-group">
            <label class="form-label">Mobile Image</label>
            <?php if(!empty($banner->mobile_image) && file_exists($banner->mobile_image)): ?>
              <img src="<?= base_url($banner->mobile_image); ?>" class="preview" alt="">
            <?php endif; ?>
            <input type="file" name="mobile_image" class="form-control" accept="image/*">
          </div>
          <div class="form-group">
            <button type="button" class="btn btn-primary" onclick="saveBanner()">Save Banner</button>
          </div>
        </div>
      </form>
    </section>
    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
  <script>
    function showToast(message, isError){
      const t = document.createElement('div');
      t.textContent = message;
      t.style.position='fixed'; t.style.top='16px'; t.style.left='16px'; t.style.right='16px';
      t.style.background=isError?'#DC2626':'#059669'; t.style.color='#fff'; t.style.padding='14px 16px';
      t.style.borderRadius='12px'; t.style.textAlign='center'; t.style.zIndex='1000'; t.style.fontWeight='600';
      document.body.appendChild(t); setTimeout(()=>t.remove(), 3000);
    }
    function saveBanner(){
      const btn = document.querySelector('#banner-form button[type=button]');
      btn.disabled = true; btn.textContent = 'Saving...';
      const fd = new FormData(document.getElementById('banner-form'));
      fetch('<?= base_url('online_store/save_banner'); ?>', {method:'POST', body:fd})
      .then(r=>r.json()).then(res=>{
        if(res.status === 'success'){ showToast(res.message); location.href='<?= base_url('mobile/online_store/banners'); ?>'; }
        else { showToast(res.message || 'Failed to save', true); btn.disabled = false; btn.textContent = 'Save Banner'; }
      }).catch(()=>{ showToast('Error saving', true); btn.disabled = false; btn.textContent = 'Save Banner'; });
    }
  </script>
</body>
</html>
