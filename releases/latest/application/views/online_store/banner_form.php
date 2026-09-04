<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.os-form-grid{display:grid!important;grid-template-columns:1fr 1fr!important;gap:18px 24px!important}
.os-form-grid .full{grid-column:1/-1!important}
.os-form-grid .mp-form-group{display:flex!important;flex-direction:column!important;gap:6px!important}
.os-form-grid .mp-form-group>label{font-size:13px!important;font-weight:600!important;color:var(--mp-ink)!important;margin:0!important}
.os-form-narrow{max-width:760px!important}
.os-image-preview{margin-top:8px;border-radius:10px;overflow:hidden;border:1px solid var(--mp-border);max-width:240px}
.os-image-preview img{display:block;width:100%;height:auto}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><a href="<?= base_url('online_store/banners'); ?>"><i class="fa fa-arrow-left"></i> Back to Banners</a></div>
  </div>
</div>

<form id="banner-form" method="post" enctype="multipart/form-data" onsubmit="return false;" class="os-form-narrow">
  <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
  <input type="hidden" name="banner_id" value="<?= $banner ? (int)$banner->id : ''; ?>">

  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Banner Details</h3></div>
    <div class="mp-card-body">
      <div class="os-form-grid">
        <div class="mp-form-group full"><label for="banner_type">Banner Type</label>
          <select class="mp-form-control" name="banner_type" id="banner_type">
            <option value="hero" <?= ($banner->banner_type ?? 'hero') == 'hero' ? 'selected' : ''; ?>>Homepage Hero (slideshow)</option>
            <option value="promo" <?= ($banner->banner_type ?? '') == 'promo' ? 'selected' : ''; ?>>Promotional</option>
          </select>
        </div>
        <div class="mp-form-group full"><label for="banner_title">Banner Title</label><input type="text" class="mp-form-control" name="banner_title" id="banner_title" value="<?= htmlspecialchars($banner->banner_title ?? ''); ?>"></div>
        <div class="mp-form-group full"><label for="banner_subtitle">Banner Subtitle</label><input type="text" class="mp-form-control" name="banner_subtitle" id="banner_subtitle" value="<?= htmlspecialchars($banner->banner_subtitle ?? ''); ?>"></div>
        <div class="mp-form-group"><label for="button_text">Button Text</label><input type="text" class="mp-form-control" name="button_text" id="button_text" value="<?= htmlspecialchars($banner->button_text ?? ''); ?>"></div>
        <div class="mp-form-group"><label for="button_url">Button URL</label><input type="text" class="mp-form-control" name="button_url" id="button_url" value="<?= htmlspecialchars($banner->button_url ?? ''); ?>"></div>
        <div class="mp-form-group"><label for="display_order">Display Order</label><input type="number" class="mp-form-control" name="display_order" id="display_order" value="<?= (int)($banner->display_order ?? 0); ?>"></div>
        <div class="mp-form-group"><label for="status">Active</label><label class="os-check-inline" style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:var(--mp-ink);margin:0;padding:10px 14px;border:1px solid var(--mp-border);border-radius:10px;background:var(--mp-surface);"><input type="checkbox" name="status" value="1" <?= (!isset($banner) || $banner->status) ? 'checked' : ''; ?>> Yes, display this banner</label></div>
        <div class="mp-form-group"><label for="start_date">Start Date</label><input type="date" class="mp-form-control" name="start_date" id="start_date" value="<?= htmlspecialchars($banner->start_date ?? ''); ?>"></div>
        <div class="mp-form-group"><label for="end_date">End Date</label><input type="date" class="mp-form-control" name="end_date" id="end_date" value="<?= htmlspecialchars($banner->end_date ?? ''); ?>"></div>
        <div class="mp-form-group full"><label for="desktop_image">Desktop Image</label><input type="file" name="desktop_image" id="desktop_image" accept="image/*" class="mp-form-control" style="padding:8px!important;">
          <?php if($banner && !empty($banner->desktop_image) && file_exists($banner->desktop_image)): ?><div class="os-image-preview"><img src="<?= base_url($banner->desktop_image); ?>" alt="Desktop preview"></div><?php endif; ?>
        </div>
        <div class="mp-form-group full"><label for="mobile_image">Mobile Image</label><input type="file" name="mobile_image" id="mobile_image" accept="image/*" class="mp-form-control" style="padding:8px!important;">
          <?php if($banner && !empty($banner->mobile_image) && file_exists($banner->mobile_image)): ?><div class="os-image-preview"><img src="<?= base_url($banner->mobile_image); ?>" alt="Mobile preview"></div><?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="mp-form-actions" style="margin-top:20px;">
    <button type="button" class="mp-btn-primary" onclick="saveBanner()"><i class="fa fa-check"></i> Save Banner</button>
    <a href="<?= base_url('online_store/banners'); ?>" class="mp-btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
  </div>
</form>

<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };
function saveBanner(){
  const btn = document.querySelector('#banner-form .mp-btn-primary');
  btn.disabled = true; btn.textContent = 'Saving...';
  const fd = new FormData(document.getElementById('banner-form'));
  fetch('<?= base_url('online_store/save_banner'); ?>', {method:'POST', body:fd})
  .then(r=>r.json()).then(res=>{
    if(res.status==='success'){ toastr.success(res.message); location.href='<?= base_url('online_store/banners'); ?>'; }
    else { toastr.error(res.message || 'Failed to save'); btn.disabled = false; btn.innerHTML = '<i class="fa fa-check"></i> Save Banner'; }
  }).catch(()=>{ toastr.error('Error saving banner'); btn.disabled = false; btn.innerHTML = '<i class="fa fa-check"></i> Save Banner'; });
}
</script>
<script>$(".online_store-banners-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
