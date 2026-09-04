<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.os-theme-grid{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(180px,1fr))!important;gap:16px!important}
.os-theme-card{border:2px solid var(--mp-border)!important;border-radius:14px!important;padding:18px!important;text-align:center!important;cursor:pointer!important;transition:all .15s ease!important;background:var(--mp-surface)!important}
.os-theme-card:hover{border-color:var(--mp-primary)!important}
.os-theme-card.selected{border-color:var(--mp-primary)!important;box-shadow:0 0 0 3px rgba(0,87,255,.15)!important}
.os-theme-preview{width:44px!important;height:44px!important;border-radius:50%!important;margin:0 auto 10px!important}
.os-theme-name{font-weight:700!important;font-size:14px!important;color:var(--mp-text)!important}
.os-theme-ind{font-size:12px!important;color:var(--mp-muted)!important;margin-top:2px!important}
.os-color-input{width:54px!important;height:42px!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;cursor:pointer!important;padding:2px!important;background:var(--mp-surface)!important}
.os-form-grid{display:grid!important;grid-template-columns:1fr 1fr!important;gap:18px 24px!important}
.os-form-grid .full{grid-column:1/-1!important}
.os-form-grid .mp-form-group{display:flex!important;flex-direction:column!important;gap:6px!important}
.os-form-grid .mp-form-group>label{font-size:13px!important;font-weight:600!important;color:var(--mp-ink)!important;margin:0!important}
.os-color-row{display:flex!important;align-items:center!important;gap:10px!important}
.os-color-row label{font-size:13px!important;font-weight:600!important;color:var(--mp-ink)!important;margin:0!important;min-width:120px!important}
.os-section-title{font-size:13px!important;font-weight:700!important;color:var(--mp-muted)!important;text-transform:uppercase!important;letter-spacing:.05em!important;margin:0 0 14px!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Customise your storefront theme, colours, branding and SEO</div>
  </div>
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" target="_blank" class="mp-qa-btn blue"><i class="fa fa-external-link"></i> View Store</a>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3><i class="fa fa-paint-brush"></i> Theme</h3></div>
  <div class="mp-card-body">
    <div class="os-theme-grid" id="theme-selector">
      <?php foreach($themes as $t): ?>
      <div class="os-theme-card <?= ($current_theme && $current_theme->id == $t->id) ? 'selected' : ''; ?>" data-theme-id="<?= (int)$t->id; ?>" onclick="selectTheme(<?= (int)$t->id; ?>)">
        <div class="os-theme-preview" style="background:<?= htmlspecialchars($t->default_primary_color); ?>"></div>
        <div class="os-theme-name"><?= htmlspecialchars($t->theme_name); ?></div>
        <div class="os-theme-ind"><?= htmlspecialchars($t->industry); ?></div>
        <a href="<?= base_url('online_store/preview_store?theme_id=' . $t->id); ?>" target="_blank" class="mp-qa-btn blue" style="margin-top:10px;padding:6px 12px;font-size:12px;" onclick="event.stopPropagation();">Preview</a>
      </div>
      <?php endforeach; ?>
    </div>
    <input type="hidden" id="theme_id" value="<?= $current_theme ? (int)$current_theme->id : ''; ?>">
  </div>
</div>

<form id="appearance-form" onsubmit="return false;">
  <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

  <div class="mp-card-form">
    <div class="mp-card-head"><h3><i class="fa fa-palette"></i> Colours & Typography</h3></div>
    <div class="mp-card-body">
      <div class="os-form-grid">
        <div class="mp-form-group">
          <label>Primary Colour</label>
          <div class="os-color-row"><input type="color" class="os-color-input" id="primary_color" name="primary_color" value="<?= htmlspecialchars($settings->primary_color ?? '#3B82F6'); ?>"></div>
        </div>
        <div class="mp-form-group">
          <label>Secondary Colour</label>
          <div class="os-color-row"><input type="color" class="os-color-input" id="secondary_color" name="secondary_color" value="<?= htmlspecialchars($settings->secondary_color ?? '#10B981'); ?>"></div>
        </div>
        <div class="mp-form-group">
          <label>Footer Background</label>
          <div class="os-color-row"><input type="color" class="os-color-input" id="footer_bg_color" name="footer_bg_color" value="<?= htmlspecialchars($settings->footer_bg_color ?? '#0F172A'); ?>"></div>
        </div>
        <div class="mp-form-group">
          <label>Footer Text Colour</label>
          <div class="os-color-row"><input type="color" class="os-color-input" id="footer_text_color" name="footer_text_color" value="<?= htmlspecialchars($settings->footer_text_color ?? '#94A3B8'); ?>"></div>
        </div>
        <div class="mp-form-group">
          <label>Button Colour</label>
          <div class="os-color-row"><input type="color" class="os-color-input" id="button_color" name="button_color" value="<?= htmlspecialchars($settings->button_color ?? ($settings->primary_color ?? '#3B82F6')); ?>"></div>
        </div>
        <div class="mp-form-group">
          <label>Header Text Colour</label>
          <div class="os-color-row"><input type="color" class="os-color-input" id="header_text_color" name="header_text_color" value="<?= htmlspecialchars($settings->header_text_color ?? '#000000'); ?>" title="Leave empty for default"></div>
        </div>
        <div class="mp-form-group">
          <label for="font_family">Font Family</label>
          <select class="mp-form-control" id="font_family" name="font_family">
            <option value="Inter" <?= ($settings->font_family ?? '') == 'Inter' ? 'selected' : ''; ?>>Inter (Modern)</option>
            <option value="Playfair Display" <?= ($settings->font_family ?? '') == 'Playfair Display' ? 'selected' : ''; ?>>Playfair Display (Elegant)</option>
            <option value="Montserrat" <?= ($settings->font_family ?? '') == 'Montserrat' ? 'selected' : ''; ?>>Montserrat (Bold)</option>
            <option value="Roboto" <?= ($settings->font_family ?? '') == 'Roboto' ? 'selected' : ''; ?>>Roboto (Clean)</option>
            <option value="Poppins" <?= ($settings->font_family ?? '') == 'Poppins' ? 'selected' : ''; ?>>Poppins (Friendly)</option>
            <option value="Open Sans" <?= ($settings->font_family ?? '') == 'Open Sans' ? 'selected' : ''; ?>>Open Sans (Readable)</option>
          </select>
        </div>
        <div class="mp-form-group">
          <label for="button_style">Button Style</label>
          <select class="mp-form-control" id="button_style" name="button_style">
            <option value="rounded" <?= ($settings->button_style ?? '') == 'rounded' ? 'selected' : ''; ?>>Rounded</option>
            <option value="pill" <?= ($settings->button_style ?? '') == 'pill' ? 'selected' : ''; ?>>Pill</option>
            <option value="square" <?= ($settings->button_style ?? '') == 'square' ? 'selected' : ''; ?>>Square</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="mp-card-form">
    <div class="mp-card-head"><h3><i class="fa fa-store"></i> Store Branding</h3></div>
    <div class="mp-card-body">
      <div class="os-form-grid">
        <div class="mp-form-group"><label for="store_headline">Store Headline</label><input type="text" class="mp-form-control" id="store_headline" name="store_headline" value="<?= htmlspecialchars($settings->store_headline ?? ''); ?>" placeholder="Welcome to our store"></div>
        <div class="mp-form-group"><label for="store_subheadline">Store Subheadline</label><input type="text" class="mp-form-control" id="store_subheadline" name="store_subheadline" value="<?= htmlspecialchars($settings->store_subheadline ?? ''); ?>" placeholder="Discover amazing products"></div>
        <div class="mp-form-group"><label for="footer_style">Footer Style</label>
          <select class="mp-form-control" id="footer_style" name="footer_style">
            <option value="standard" <?= ($settings->footer_style ?? '') == 'standard' ? 'selected' : ''; ?>>Standard (4 columns)</option>
            <option value="compact" <?= ($settings->footer_style ?? '') == 'compact' ? 'selected' : ''; ?>>Compact (centered)</option>
            <option value="about_focused" <?= ($settings->footer_style ?? '') == 'about_focused' ? 'selected' : ''; ?>>About-Focused</option>
          </select>
        </div>
        <div class="mp-form-group"><label for="footer_address_url">Address Link</label><input type="url" class="mp-form-control" id="footer_address_url" name="footer_address_url" value="<?= htmlspecialchars($settings->footer_address_url ?? ''); ?>" placeholder="https://maps.google.com/..."></div>
        <div class="mp-form-group full"><label for="footer_about_us">About Us (Footer)</label><textarea class="mp-form-control" id="footer_about_us" name="footer_about_us" rows="3" placeholder="Short about us text shown under the brand in footer"><?= htmlspecialchars($settings->footer_about_us ?? ''); ?></textarea></div>
        <div class="mp-form-group"><label for="announcement_bar">Announcement Bar</label><input type="text" class="mp-form-control" id="announcement_bar" name="announcement_bar" value="<?= htmlspecialchars($settings->announcement_bar ?? ''); ?>" placeholder="Free delivery on orders over N10,000"></div>
        <div class="mp-form-group"><label>Bar Colour</label><div class="os-color-row"><input type="color" class="os-color-input" id="announcement_bar_color" name="announcement_bar_color" value="<?= htmlspecialchars($settings->announcement_bar_color ?? '#0F172A'); ?>"></div></div>
      </div>
    </div>
  </div>

  <div class="mp-card-form">
    <div class="mp-card-head"><h3><i class="fa fa-search"></i> SEO & Analytics</h3></div>
    <div class="mp-card-body">
      <div class="os-form-grid">
        <div class="mp-form-group"><label for="meta_title">Meta Title</label><input type="text" class="mp-form-control" id="meta_title" name="meta_title" value="<?= htmlspecialchars($settings->meta_title ?? ''); ?>" placeholder="Homepage title for search engines"></div>
        <div class="mp-form-group"><label for="meta_description">Meta Description</label><textarea class="mp-form-control" id="meta_description" name="meta_description" rows="2" placeholder="Short description shown in Google results"><?= htmlspecialchars($settings->meta_description ?? ''); ?></textarea></div>
        <div class="mp-form-group"><label for="meta_keywords">Meta Keywords</label><input type="text" class="mp-form-control" id="meta_keywords" name="meta_keywords" value="<?= htmlspecialchars($settings->meta_keywords ?? ''); ?>" placeholder="products, shop, online store"></div>
        <div class="mp-form-group"><label for="google_analytics_id">Google Analytics ID</label><input type="text" class="mp-form-control" id="google_analytics_id" name="google_analytics_id" value="<?= htmlspecialchars($settings->google_analytics_id ?? ''); ?>" placeholder="G-XXXXXXXXXX or UA-XXXXX-X"></div>
        <div class="mp-form-group"><label for="facebook_pixel_id">Facebook Pixel ID</label><input type="text" class="mp-form-control" id="facebook_pixel_id" name="facebook_pixel_id" value="<?= htmlspecialchars($settings->facebook_pixel_id ?? ''); ?>" placeholder="1234567890"></div>
        <div class="mp-form-group"><label for="robots_index">Allow Search Indexing</label>
          <select class="mp-form-control" id="robots_index" name="robots_index">
            <option value="1" <?= ($settings->robots_index ?? '1') == '1' ? 'selected' : ''; ?>>Yes — index, follow</option>
            <option value="0" <?= ($settings->robots_index ?? '1') == '0' ? 'selected' : ''; ?>>No — noindex, nofollow</option>
          </select>
        </div>
        <div class="mp-form-group full"><label for="custom_head_scripts">Custom Head Scripts</label><textarea class="mp-form-control" id="custom_head_scripts" name="custom_head_scripts" rows="4" placeholder="Paste Google Search Console verification tag, Tag Manager, or any custom script here."><?= htmlspecialchars($settings->custom_head_scripts ?? ''); ?></textarea><div class="mp-form-hint">These scripts are output exactly as entered inside the &lt;head&gt; tag. Use with care.</div></div>
      </div>
    </div>
  </div>

  <div class="mp-card-form">
    <div class="mp-card-head"><h3><i class="fa fa-share-alt"></i> Social Links</h3></div>
    <div class="mp-card-body">
      <div class="os-form-grid">
        <div class="mp-form-group"><label for="instagram_url">Instagram</label><input type="url" class="mp-form-control" id="instagram_url" name="instagram_url" value="<?= htmlspecialchars($settings->instagram_url ?? ''); ?>"></div>
        <div class="mp-form-group"><label for="facebook_url">Facebook</label><input type="url" class="mp-form-control" id="facebook_url" name="facebook_url" value="<?= htmlspecialchars($settings->facebook_url ?? ''); ?>"></div>
        <div class="mp-form-group"><label for="tiktok_url">TikTok</label><input type="url" class="mp-form-control" id="tiktok_url" name="tiktok_url" value="<?= htmlspecialchars($settings->tiktok_url ?? ''); ?>"></div>
        <div class="mp-form-group"><label for="x_url">X (Twitter)</label><input type="url" class="mp-form-control" id="x_url" name="x_url" value="<?= htmlspecialchars($settings->x_url ?? ''); ?>"></div>
        <div class="mp-form-group"><label for="youtube_url">YouTube</label><input type="url" class="mp-form-control" id="youtube_url" name="youtube_url" value="<?= htmlspecialchars($settings->youtube_url ?? ''); ?>"></div>
      </div>
    </div>
  </div>

  <div class="mp-card-form">
    <div class="mp-card-head"><h3><i class="fa fa-clock-o"></i> Business Hours</h3></div>
    <div class="mp-card-body">
      <div class="os-form-grid">
        <div class="mp-form-group full"><label for="business_hours">Hours (one per line)</label><textarea class="mp-form-control" id="business_hours" name="business_hours" rows="4" placeholder="Mon - Fri: 9:00 AM - 6:00 PM"><?= htmlspecialchars($settings->business_hours ?? ''); ?></textarea></div>
      </div>
    </div>
  </div>

  <div class="mp-form-actions" style="margin-top:20px;">
    <button type="button" class="mp-btn-primary" onclick="saveAppearance()"><i class="fa fa-check"></i> Save Appearance</button>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" target="_blank" class="mp-btn-secondary"><i class="fa fa-external-link"></i> View Store</a>
  </div>
</form>

<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000, showMethod: 'slideDown', hideMethod: 'slideUp' };
function selectTheme(id){
  document.getElementById('theme_id').value = id;
  document.querySelectorAll('.os-theme-card').forEach(c => c.classList.remove('selected'));
  document.querySelector('.os-theme-card[data-theme-id="'+id+'"]').classList.add('selected');
}
function saveAppearance(){
  const btn = document.querySelector('#appearance-form .mp-btn-primary');
  btn.disabled = true; btn.textContent = 'Saving...';
  const fd = new FormData(document.getElementById('appearance-form'));
  fd.append('theme_id', document.getElementById('theme_id').value);
  fetch('<?= base_url('online_store/save_appearance'); ?>', {method:'POST', body:fd})
  .then(r=>r.json()).then(res=>{
    if(res.status === 'success'){ toastr.success(res.message); }
    else { toastr.error(res.message || 'Failed to save'); }
    btn.disabled = false; btn.innerHTML = '<i class="fa fa-check"></i> Save Appearance';
  }).catch(()=>{ toastr.error('Error saving appearance'); btn.disabled = false; btn.innerHTML = '<i class="fa fa-check"></i> Save Appearance'; });
}
</script>
<script>$(".online_store-appearance-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
