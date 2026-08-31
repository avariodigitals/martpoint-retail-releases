<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Appearance</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
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
    textarea.form-control { min-height: 90px; resize: vertical; }
    .color-row { display: flex; align-items: center; gap: 12px; }
    .color-row input[type="color"] { -webkit-appearance: none; border: none; width: 44px; height: 44px; border-radius: 12px; padding: 0; background: none; cursor: pointer; }
    .color-row input[type="color"]::-webkit-color-swatch-wrapper { padding: 0; }
    .color-row input[type="color"]::-webkit-color-swatch { border: none; border-radius: 12px; }
    .btn { display: block; width: 100%; padding: 14px; border-radius: 12px; border: none; font-weight: 600; font-size: 15px; cursor: pointer; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .theme-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 16px; }
    .theme-card { border: 2px solid var(--mp-border); border-radius: 14px; padding: 12px; text-align: center; background: #fff; }
    .theme-card.selected { border-color: var(--mp-primary); box-shadow: 0 0 0 3px rgba(0,87,255,0.15); }
    .theme-dot { width: 40px; height: 40px; border-radius: 50%; margin: 0 auto 8px; }
    .theme-name { font-weight: 700; font-size: 13px; }
    .theme-industry { font-size: 11px; color: var(--mp-muted); }
    .choice-group { display: flex; flex-wrap: wrap; gap: 8px; }
    .choice { padding: 10px 14px; border-radius: 20px; border: 1px solid var(--mp-border); background: #fff; font-size: 13px; font-weight: 600; color: var(--mp-ink); cursor: pointer; }
    .choice input { position: absolute; opacity: 0; }
    .choice:has(input:checked) { background: var(--mp-primary); color: #fff; border-color: var(--mp-primary); }
    .link { color: var(--mp-primary); text-decoration: none; font-size: 13px; font-weight: 600; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= $back_url; ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Appearance</h1>
        </div>
      </div>

      <form id="appearance-form" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" id="theme_id" name="theme_id" value="<?= $current_theme ? $current_theme->id : ''; ?>">

        <div class="card">
          <div class="card-title">Theme</div>
          <div class="form-group">
            <div class="theme-grid">
              <?php foreach($themes as $t): ?>
                <div class="theme-card <?= ($current_theme && $current_theme->id == $t->id) ? 'selected' : ''; ?>" data-id="<?= $t->id; ?>" onclick="selectTheme(<?= $t->id; ?>)">
                  <div class="theme-dot" style="background:<?= $t->default_primary_color; ?>"></div>
                  <div class="theme-name"><?= htmlspecialchars($t->theme_name); ?></div>
                  <div class="theme-industry"><?= htmlspecialchars($t->industry); ?></div>
                </div>
              <?php endforeach; ?>
            </div>
            <a href="<?= base_url('online_store/preview_store?theme_id=' . ($current_theme ? $current_theme->id : '')); ?>" target="_blank" class="link" style="display:inline-flex;align-items:center;gap:6px;"><i class="fa fa-eye"></i> Preview Store</a>
          </div>
        </div>

        <div class="card">
          <div class="card-title">Colors</div>
          <div class="form-group color-row">
            <div style="flex:1;"><label class="form-label">Primary</label><input type="color" name="primary_color" value="<?= htmlspecialchars($settings->primary_color ?? '#0057FF'); ?>"></div>
            <div style="flex:1;"><label class="form-label">Secondary</label><input type="color" name="secondary_color" value="<?= htmlspecialchars($settings->secondary_color ?? '#10B981'); ?>"></div>
            <div style="flex:1;"><label class="form-label">Button</label><input type="color" name="button_color" value="<?= htmlspecialchars($settings->button_color ?? ($settings->primary_color ?? '#0057FF')); ?>"></div>
          </div>
          <div class="form-group color-row">
            <div style="flex:1;"><label class="form-label">Footer BG</label><input type="color" name="footer_bg_color" value="<?= htmlspecialchars($settings->footer_bg_color ?? '#0F172A'); ?>"></div>
            <div style="flex:1;"><label class="form-label">Footer Text</label><input type="color" name="footer_text_color" value="<?= htmlspecialchars($settings->footer_text_color ?? '#94A3B8'); ?>"></div>
            <div style="flex:1;"><label class="form-label">Header Text</label><input type="color" name="header_text_color" value="<?= htmlspecialchars($settings->header_text_color ?? '#FFFFFF'); ?>"></div>
          </div>
        </div>

        <div class="card">
          <div class="card-title">Typography & Buttons</div>
          <div class="form-group">
            <label class="form-label">Font Family</label>
            <div class="choice-group">
              <?php $fonts = ['Inter', 'Playfair Display', 'Montserrat', 'Roboto', 'Poppins', 'Open Sans']; ?>
              <?php foreach($fonts as $f): ?>
                <label class="choice"><input type="radio" name="font_family" value="<?= $f; ?>" <?= ($settings->font_family ?? 'Inter') == $f ? 'checked' : ''; ?>><?= $f; ?></label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Button Style</label>
            <div class="choice-group">
              <?php $bStyles = ['rounded' => 'Rounded', 'pill' => 'Pill', 'square' => 'Square']; ?>
              <?php foreach($bStyles as $k => $v): ?>
                <label class="choice"><input type="radio" name="button_style" value="<?= $k; ?>" <?= ($settings->button_style ?? 'rounded') == $k ? 'checked' : ''; ?>><?= $v; ?></label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Footer Style</label>
            <div class="choice-group">
              <?php $fStyles = ['standard' => 'Standard', 'compact' => 'Compact', 'about_focused' => 'About']; ?>
              <?php foreach($fStyles as $k => $v): ?>
                <label class="choice"><input type="radio" name="footer_style" value="<?= $k; ?>" <?= ($settings->footer_style ?? 'standard') == $k ? 'checked' : ''; ?>><?= $v; ?></label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-title">Branding</div>
          <div class="form-group">
            <label class="form-label">Store Headline</label>
            <input type="text" name="store_headline" class="form-control" value="<?= htmlspecialchars($settings->store_headline ?? ''); ?>" placeholder="Welcome to our store">
          </div>
          <div class="form-group">
            <label class="form-label">Store Subheadline</label>
            <input type="text" name="store_subheadline" class="form-control" value="<?= htmlspecialchars($settings->store_subheadline ?? ''); ?>" placeholder="Discover amazing products">
          </div>
          <div class="form-group">
            <label class="form-label">About Us (footer)</label>
            <textarea name="footer_about_us" class="form-control" placeholder="Short about us text"><?= htmlspecialchars($settings->footer_about_us ?? ''); ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Address Link</label>
            <input type="url" name="footer_address_url" class="form-control" value="<?= htmlspecialchars($settings->footer_address_url ?? ''); ?>" placeholder="https://maps.google.com/...">
          </div>
          <div class="form-group">
            <label class="form-label">Announcement Bar</label>
            <input type="text" name="announcement_bar" class="form-control" value="<?= htmlspecialchars($settings->announcement_bar ?? ''); ?>" placeholder="Free delivery on orders over N10,000">
          </div>
          <div class="form-group color-row">
            <div><label class="form-label">Bar Color</label><input type="color" name="announcement_bar_color" value="<?= htmlspecialchars($settings->announcement_bar_color ?? '#0F172A'); ?>"></div>
          </div>
        </div>

        <div class="card">
          <div class="card-title">SEO &amp; Analytics</div>
          <div class="form-group">
            <label class="form-label">Meta Title</label>
            <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($settings->meta_title ?? ''); ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Meta Description</label>
            <textarea name="meta_description" class="form-control" rows="2"><?= htmlspecialchars($settings->meta_description ?? ''); ?></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Meta Keywords</label>
            <input type="text" name="meta_keywords" class="form-control" value="<?= htmlspecialchars($settings->meta_keywords ?? ''); ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Google Analytics ID</label>
            <input type="text" name="google_analytics_id" class="form-control" value="<?= htmlspecialchars($settings->google_analytics_id ?? ''); ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Facebook Pixel ID</label>
            <input type="text" name="facebook_pixel_id" class="form-control" value="<?= htmlspecialchars($settings->facebook_pixel_id ?? ''); ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Allow Search Indexing</label>
            <div class="choice-group">
              <label class="choice"><input type="radio" name="robots_index" value="1" <?= ($settings->robots_index ?? '1') == '1' ? 'checked' : ''; ?>>Yes</label>
              <label class="choice"><input type="radio" name="robots_index" value="0" <?= ($settings->robots_index ?? '1') == '0' ? 'checked' : ''; ?>>No</label>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Custom Head Scripts</label>
            <textarea name="custom_head_scripts" class="form-control" rows="4"><?= htmlspecialchars($settings->custom_head_scripts ?? ''); ?></textarea>
          </div>
        </div>

        <div class="card">
          <div class="card-title">Social Links</div>
          <?php foreach(['instagram_url' => 'Instagram', 'facebook_url' => 'Facebook', 'tiktok_url' => 'TikTok', 'x_url' => 'X (Twitter)', 'youtube_url' => 'YouTube'] as $k => $l): ?>
          <div class="form-group">
            <label class="form-label"><?= $l; ?></label>
            <input type="url" name="<?= $k; ?>" class="form-control" value="<?= htmlspecialchars($settings->{$k} ?? ''); ?>" placeholder="https://...">
          </div>
          <?php endforeach; ?>
        </div>

        <div class="card">
          <div class="card-title">Business Hours</div>
          <div class="form-group">
            <label class="form-label">Hours (one per line)</label>
            <textarea name="business_hours" class="form-control" rows="4"><?= htmlspecialchars($settings->business_hours ?? ''); ?></textarea>
          </div>
        </div>

        <div class="form-group" style="padding:0 0 20px;">
          <button type="button" class="btn btn-primary" onclick="saveAppearance()" style="margin-bottom:10px;">Save Appearance</button>
          <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" target="_blank" class="btn btn-primary" style="background:#0F172A;text-align:center;text-decoration:none;">View Store</a>
        </div>
      </form>
    </section>
    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
  <script>
    function selectTheme(id){
      document.getElementById('theme_id').value = id;
      document.querySelectorAll('.theme-card').forEach(c => c.classList.remove('selected'));
      document.querySelector('.theme-card[data-id="'+id+'"]').classList.add('selected');
    }
    function showToast(message, isError){
      const t = document.createElement('div');
      t.textContent = message;
      t.style.position='fixed'; t.style.top='16px'; t.style.left='16px'; t.style.right='16px';
      t.style.background=isError?'#DC2626':'#059669'; t.style.color='#fff'; t.style.padding='14px 16px';
      t.style.borderRadius='12px'; t.style.textAlign='center'; t.style.zIndex='1000'; t.style.fontWeight='600';
      document.body.appendChild(t); setTimeout(()=>t.remove(), 3000);
    }
    function saveAppearance(){
      const btn = document.querySelector('#appearance-form button[type=button]');
      btn.disabled = true; btn.textContent = 'Saving...';
      const fd = new FormData(document.getElementById('appearance-form'));
      fetch('<?= base_url('online_store/save_appearance'); ?>', {method:'POST', body:fd})
      .then(r=>r.json()).then(res=>{
        showToast(res.message || 'Saved', res.status !== 'success');
        btn.disabled = false; btn.textContent = 'Save Appearance';
      }).catch(()=>{ showToast('Error saving', true); btn.disabled = false; btn.textContent = 'Save Appearance'; });
    }
  </script>
</body>
</html>
