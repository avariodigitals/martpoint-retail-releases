<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
<style>
.theme-card { border:2px solid #ddd; border-radius:8px; padding:16px; text-align:center; cursor:pointer; transition:all .2s; background:#fff; }
.theme-card:hover { border-color:#3B82F6; }
.theme-card.selected { border-color:#3B82F6; box-shadow:0 0 0 3px rgba(59,130,246,0.2); }
.theme-color-preview { width:40px; height:40px; border-radius:50%; margin:0 auto 8px; }
.color-input { width:64px; height:44px; border:none; cursor:pointer; padding:0; border-radius:6px; overflow:hidden; }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar');?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?=base_url('online_store');?>">Online Store</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">

          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-paint-brush text-purple"></i> Theme</h3>
            </div>
            <div class="box-body">
              <div class="row" id="theme-selector">
                <?php foreach($themes as $t): ?>
                <div class="col-md-3 col-sm-6 mp-mb-1">
                  <div class="theme-card <?= ($current_theme && $current_theme->id == $t->id) ? 'selected' : ''; ?>" data-theme-id="<?= $t->id; ?>" onclick="selectTheme(<?= $t->id; ?>)">
                    <div class="theme-color-preview" style="background:<?= $t->default_primary_color; ?>"></div>
                    <div style="font-weight:700; font-size:14px;"><?= htmlspecialchars($t->theme_name); ?></div>
                    <div style="font-size:12px; color:#888;"><?= htmlspecialchars($t->industry); ?></div>
                    <a href="<?= base_url('online_store/preview_store?theme_id=' . $t->id); ?>" target="_blank" class="btn btn-xs btn-default" style="margin-top:8px;" onclick="event.stopPropagation();">Preview</a>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <input type="hidden" id="theme_id" value="<?= $current_theme ? $current_theme->id : ''; ?>">
            </div>
          </div>

          <form class="form-horizontal" id="appearance-form" onsubmit="return false;">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

            <div class="box box-info">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-palette text-orange"></i> Colors & Typography</h3>
              </div>
              <div class="box-body">
                <div class="form-group">
                  <label class="col-sm-2 control-label">Primary Color</label>
                  <div class="col-sm-2">
                    <input type="color" class="color-input" id="primary_color" name="primary_color" value="<?= htmlspecialchars($settings->primary_color ?? '#3B82F6'); ?>">
                  </div>
                  <label class="col-sm-2 control-label">Secondary Color</label>
                  <div class="col-sm-2">
                    <input type="color" class="color-input" id="secondary_color" name="secondary_color" value="<?= htmlspecialchars($settings->secondary_color ?? '#10B981'); ?>">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">Footer Background</label>
                  <div class="col-sm-2">
                    <input type="color" class="color-input" id="footer_bg_color" name="footer_bg_color" value="<?= htmlspecialchars($settings->footer_bg_color ?? '#0F172A'); ?>">
                  </div>
                  <label class="col-sm-2 control-label">Footer Text Color</label>
                  <div class="col-sm-2">
                    <input type="color" class="color-input" id="footer_text_color" name="footer_text_color" value="<?= htmlspecialchars($settings->footer_text_color ?? '#94A3B8'); ?>">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">Button Color</label>
                  <div class="col-sm-2">
                    <input type="color" class="color-input" id="button_color" name="button_color" value="<?= htmlspecialchars($settings->button_color ?? ($settings->primary_color ?? '#3B82F6')); ?>">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">Header Text Color</label>
                  <div class="col-sm-2">
                    <input type="color" class="color-input" id="header_text_color" name="header_text_color" value="<?= htmlspecialchars($settings->header_text_color ?? ''); ?>" title="Leave empty for default">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">Font Family</label>
                  <div class="col-sm-4">
                    <select class="form-control" id="font_family" name="font_family">
                      <option value="Inter" <?= ($settings->font_family ?? '') == 'Inter' ? 'selected' : ''; ?>>Inter (Modern)</option>
                      <option value="Playfair Display" <?= ($settings->font_family ?? '') == 'Playfair Display' ? 'selected' : ''; ?>>Playfair Display (Elegant)</option>
                      <option value="Montserrat" <?= ($settings->font_family ?? '') == 'Montserrat' ? 'selected' : ''; ?>>Montserrat (Bold)</option>
                      <option value="Roboto" <?= ($settings->font_family ?? '') == 'Roboto' ? 'selected' : ''; ?>>Roboto (Clean)</option>
                      <option value="Poppins" <?= ($settings->font_family ?? '') == 'Poppins' ? 'selected' : ''; ?>>Poppins (Friendly)</option>
                      <option value="Open Sans" <?= ($settings->font_family ?? '') == 'Open Sans' ? 'selected' : ''; ?>>Open Sans (Readable)</option>
                    </select>
                  </div>
                  <label class="col-sm-2 control-label">Button Style</label>
                  <div class="col-sm-4">
                    <select class="form-control" id="button_style" name="button_style">
                      <option value="rounded" <?= ($settings->button_style ?? '') == 'rounded' ? 'selected' : ''; ?>>Rounded</option>
                      <option value="pill" <?= ($settings->button_style ?? '') == 'pill' ? 'selected' : ''; ?>>Pill</option>
                      <option value="square" <?= ($settings->button_style ?? '') == 'square' ? 'selected' : ''; ?>>Square</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="box box-success">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-store text-green"></i> Store Branding</h3>
              </div>
              <div class="box-body">
                <div class="form-group">
                  <label class="col-sm-2 control-label">Store Headline</label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control" id="store_headline" name="store_headline" value="<?= htmlspecialchars($settings->store_headline ?? ''); ?>" placeholder="Welcome to our store">
                  </div>
                  <label class="col-sm-2 control-label">Store Subheadline</label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control" id="store_subheadline" name="store_subheadline" value="<?= htmlspecialchars($settings->store_subheadline ?? ''); ?>" placeholder="Discover amazing products">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">Footer Style</label>
                  <div class="col-sm-4">
                    <select class="form-control" id="footer_style" name="footer_style">
                      <option value="standard" <?= ($settings->footer_style ?? '') == 'standard' ? 'selected' : ''; ?>>Standard (4 columns)</option>
                      <option value="compact" <?= ($settings->footer_style ?? '') == 'compact' ? 'selected' : ''; ?>>Compact (centered)</option>
                      <option value="about_focused" <?= ($settings->footer_style ?? '') == 'about_focused' ? 'selected' : ''; ?>>About-Focused</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">About Us (Footer)</label>
                  <div class="col-sm-6">
                    <textarea class="form-control" id="footer_about_us" name="footer_about_us" rows="3" placeholder="Short about us text shown under the brand in footer"><?= htmlspecialchars($settings->footer_about_us ?? ''); ?></textarea>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">Address Link</label>
                  <div class="col-sm-6">
                    <input type="url" class="form-control" id="footer_address_url" name="footer_address_url" value="<?= htmlspecialchars($settings->footer_address_url ?? ''); ?>" placeholder="https://maps.google.com/...">
                    <small class="text-muted">Optional link for the address in footer (Google Maps, etc.)</small>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">Announcement Bar</label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control" id="announcement_bar" name="announcement_bar" value="<?= htmlspecialchars($settings->announcement_bar ?? ''); ?>" placeholder="Free delivery on orders over N10,000">
                  </div>
                  <label class="col-sm-2 control-label">Bar Color</label>
                  <div class="col-sm-2">
                    <input type="color" class="color-input" id="announcement_bar_color" name="announcement_bar_color" value="<?= htmlspecialchars($settings->announcement_bar_color ?? '#0F172A'); ?>">
                  </div>
                </div>
              </div>
            </div>

            <div class="box box-danger">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-search text-red"></i> SEO &amp; Analytics</h3>
              </div>
              <div class="box-body">
                <div class="form-group">
                  <label class="col-sm-2 control-label">Meta Title</label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control" id="meta_title" name="meta_title" value="<?= htmlspecialchars($settings->meta_title ?? ''); ?>" placeholder="Homepage title for search engines">
                  </div>
                  <label class="col-sm-2 control-label">Meta Description</label>
                  <div class="col-sm-4">
                    <textarea class="form-control" id="meta_description" name="meta_description" rows="2" placeholder="Short description shown in Google results"><?= htmlspecialchars($settings->meta_description ?? ''); ?></textarea>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">Meta Keywords</label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="<?= htmlspecialchars($settings->meta_keywords ?? ''); ?>" placeholder="products, shop, online store">
                  </div>
                  <label class="col-sm-2 control-label">Google Analytics ID</label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control" id="google_analytics_id" name="google_analytics_id" value="<?= htmlspecialchars($settings->google_analytics_id ?? ''); ?>" placeholder="G-XXXXXXXXXX or UA-XXXXX-X">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">Facebook Pixel ID</label>
                  <div class="col-sm-4">
                    <input type="text" class="form-control" id="facebook_pixel_id" name="facebook_pixel_id" value="<?= htmlspecialchars($settings->facebook_pixel_id ?? ''); ?>" placeholder="1234567890">
                  </div>
                  <label class="col-sm-2 control-label">Allow Search Indexing</label>
                  <div class="col-sm-4">
                    <select class="form-control" id="robots_index" name="robots_index">
                      <option value="1" <?= ($settings->robots_index ?? '1') == '1' ? 'selected' : ''; ?>>Yes — index, follow</option>
                      <option value="0" <?= ($settings->robots_index ?? '1') == '0' ? 'selected' : ''; ?>>No — noindex, nofollow</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">Custom Head Scripts</label>
                  <div class="col-sm-10">
                    <textarea class="form-control" id="custom_head_scripts" name="custom_head_scripts" rows="4" placeholder="Paste Google Search Console verification tag, Tag Manager, or any custom script here. It will be inserted in the <head> of every storefront page."><?= htmlspecialchars($settings->custom_head_scripts ?? ''); ?></textarea>
                    <small class="text-muted">These scripts are output exactly as entered inside the &lt;head&gt; tag. Use with care.</small>
                  </div>
                </div>
              </div>
            </div>

            <div class="box box-warning">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-share-alt text-red"></i> Social Links</h3>
              </div>
              <div class="box-body">
                <div class="form-group">
                  <label class="col-sm-2 control-label">Instagram</label>
                  <div class="col-sm-4">
                    <input type="url" class="form-control" id="instagram_url" name="instagram_url" value="<?= htmlspecialchars($settings->instagram_url ?? ''); ?>">
                  </div>
                  <label class="col-sm-2 control-label">Facebook</label>
                  <div class="col-sm-4">
                    <input type="url" class="form-control" id="facebook_url" name="facebook_url" value="<?= htmlspecialchars($settings->facebook_url ?? ''); ?>">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">TikTok</label>
                  <div class="col-sm-4">
                    <input type="url" class="form-control" id="tiktok_url" name="tiktok_url" value="<?= htmlspecialchars($settings->tiktok_url ?? ''); ?>">
                  </div>
                  <label class="col-sm-2 control-label">X (Twitter)</label>
                  <div class="col-sm-4">
                    <input type="url" class="form-control" id="x_url" name="x_url" value="<?= htmlspecialchars($settings->x_url ?? ''); ?>">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">YouTube</label>
                  <div class="col-sm-4">
                    <input type="url" class="form-control" id="youtube_url" name="youtube_url" value="<?= htmlspecialchars($settings->youtube_url ?? ''); ?>">
                  </div>
                </div>
              </div>
            </div>

            <div class="box box-default">
              <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-clock-o text-gray"></i> Business Hours</h3>
              </div>
              <div class="box-body">
                <div class="form-group">
                  <label class="col-sm-2 control-label">Hours (one per line)</label>
                  <div class="col-sm-6">
                    <textarea class="form-control" id="business_hours" name="business_hours" rows="4" placeholder="Mon - Fri: 9:00 AM - 6:00 PM"><?= htmlspecialchars($settings->business_hours ?? ''); ?></textarea>
                  </div>
                </div>
              </div>
            </div>

            <div class="box-footer">
              <button type="button" class="btn btn-primary" onclick="saveAppearance()">Save Appearance</button>
              <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" target="_blank" class="btn btn-default">View Store</a>
            </div>
          </form>

        </div>
      </div>
    </section>
  </div>
  <?php $this->load->view('footer'); ?>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000, showMethod: 'slideDown', hideMethod: 'slideUp' };
function selectTheme(id){
  document.getElementById('theme_id').value = id;
  document.querySelectorAll('.theme-card').forEach(c => c.classList.remove('selected'));
  document.querySelector('.theme-card[data-theme-id="'+id+'"]').classList.add('selected');
}
function saveAppearance(){
  const btn = document.querySelector('#appearance-form button[type=button]');
  btn.disabled = true; btn.textContent = 'Saving...';
  const fd = new FormData(document.getElementById('appearance-form'));
  fd.append('theme_id', document.getElementById('theme_id').value);
  fetch('<?= base_url('online_store/save_appearance'); ?>', {method:'POST', body:fd})
  .then(r=>r.json()).then(res=>{
    if(res.status === 'success'){
      toastr.success(res.message);
    } else {
      toastr.error(res.message || 'Failed to save');
    }
    btn.disabled = false; btn.textContent = 'Save Appearance';
  }).catch(()=>{ toastr.error('Error saving appearance'); btn.disabled = false; btn.textContent = 'Save Appearance'; });
}
</script>
</body>
</html>
