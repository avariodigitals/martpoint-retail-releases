<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar');?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?=base_url('online_store/banners');?>">Banners</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-8">
          <form class="form-horizontal" id="banner-form" method="post" enctype="multipart/form-data" onsubmit="return false;">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
            <input type="hidden" name="banner_id" value="<?= $banner ? $banner->id : ''; ?>">

            <div class="box box-primary">
              <div class="box-header with-border"><h3 class="box-title">Banner Details</h3></div>
              <div class="box-body">
                <div class="form-group">
                  <label class="col-sm-3 control-label">Banner Type</label>
                  <div class="col-sm-6">
                    <select class="form-control" name="banner_type">
                      <option value="hero" <?= ($banner->banner_type ?? 'hero') == 'hero' ? 'selected' : ''; ?>>Homepage Hero (slideshow)</option>
                      <option value="promo" <?= ($banner->banner_type ?? '') == 'promo' ? 'selected' : ''; ?>>Promotional</option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Banner Title</label>
                  <div class="col-sm-6"><input type="text" class="form-control" name="banner_title" value="<?= htmlspecialchars($banner->banner_title ?? ''); ?>"></div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Banner Subtitle</label>
                  <div class="col-sm-6"><input type="text" class="form-control" name="banner_subtitle" value="<?= htmlspecialchars($banner->banner_subtitle ?? ''); ?>"></div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Button Text</label>
                  <div class="col-sm-6"><input type="text" class="form-control" name="button_text" value="<?= htmlspecialchars($banner->button_text ?? ''); ?>"></div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Button URL</label>
                  <div class="col-sm-6"><input type="text" class="form-control" name="button_url" value="<?= htmlspecialchars($banner->button_url ?? ''); ?>"></div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Display Order</label>
                  <div class="col-sm-2"><input type="number" class="form-control" name="display_order" value="<?= (int)($banner->display_order ?? 0); ?>"></div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Start Date</label>
                  <div class="col-sm-3"><input type="date" class="form-control" name="start_date" value="<?= $banner->start_date ?? ''; ?>"></div>
                  <label class="col-sm-2 control-label">End Date</label>
                  <div class="col-sm-3"><input type="date" class="form-control" name="end_date" value="<?= $banner->end_date ?? ''; ?>"></div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Active</label>
                  <div class="col-sm-6">
                    <label class="checkbox-inline"><input type="checkbox" name="status" value="1" <?= (!isset($banner) || $banner->status) ? 'checked' : ''; ?>> Yes</label>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Desktop Image</label>
                  <div class="col-sm-6"><input type="file" name="desktop_image" accept="image/*"></div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Mobile Image</label>
                  <div class="col-sm-6"><input type="file" name="mobile_image" accept="image/*"></div>
                </div>
              </div>
              <div class="box-footer">
                <button type="button" class="btn btn-primary" onclick="saveBanner()">Save Banner</button>
                <a href="<?= base_url('online_store/banners'); ?>" class="btn btn-default">Back</a>
              </div>
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
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };
function saveBanner(){
  const btn = document.querySelector('#banner-form button[type=button]');
  btn.disabled = true; btn.textContent = 'Saving...';
  const fd = new FormData(document.getElementById('banner-form'));
  fetch('<?= base_url('online_store/save_banner'); ?>', {method:'POST', body:fd})
  .then(r=>r.json()).then(res=>{
    if(res.status==='success'){ toastr.success(res.message); location.href='<?= base_url('online_store/banners'); ?>'; }
    else { toastr.error(res.message || 'Failed to save'); btn.disabled = false; btn.textContent = 'Save Banner'; }
  }).catch(()=>{ toastr.error('Error saving banner'); btn.disabled = false; btn.textContent = 'Save Banner'; });
}
</script>
</body>
</html>
