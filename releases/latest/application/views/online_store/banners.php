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
        <li><a href="<?=base_url('online_store');?>">Online Store</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Banners</h3>
              <div class="box-tools">
                <a href="<?= base_url('online_store/banner_form'); ?>" class="btn btn-sm btn-success"><i class="fa fa-plus"></i> Add Banner</a>
              </div>
            </div>
            <div class="box-body table-responsive">
              <table class="table table-bordered table-striped">
                <thead><tr><th>Order</th><th>Type</th><th>Title</th><th>Subtitle</th><th>Status</th><th>Dates</th><th>Actions</th></tr></thead>
                <tbody>
                  <?php foreach($banners as $b): ?>
                  <tr>
                    <td><?= (int)$b->display_order; ?></td>
                    <td><span class="label label-<?= $b->banner_type == 'hero' ? 'info' : 'warning'; ?>"><?= $b->banner_type == 'hero' ? 'Hero' : 'Promo'; ?></span></td>
                    <td><?= htmlspecialchars($b->banner_title); ?></td>
                    <td><?= htmlspecialchars($b->banner_subtitle); ?></td>
                    <td><span class="label label-<?= $b->status ? 'success' : 'default'; ?>"><?= $b->status ? 'Active' : 'Inactive'; ?></span></td>
                    <td><?= $b->start_date ?: 'Always'; ?> to <?= $b->end_date ?: 'Always'; ?></td>
                    <td>
                      <a href="<?= base_url('online_store/banner_form/' . $b->id); ?>" class="btn btn-xs btn-primary">Edit</a>
                      <button class="btn btn-xs btn-danger" onclick="deleteBanner(<?= $b->id; ?>)">Delete</button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if(empty($banners)): ?>
                  <tr><td colspan="6" class="text-center text-muted">No banners yet.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <?php $this->load->view('footer'); ?>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };
function deleteBanner(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Delete this banner?')) return;
    doDeleteBanner(id);
    return;
  }
  swal({
    title: "Delete Banner?",
    text: "This action cannot be undone.",
    icon: "warning",
    buttons: true,
    dangerMode: true
  }).then(function(willDelete){
    if(willDelete) doDeleteBanner(id);
  });
}
function doDeleteBanner(id){
  fetch('<?= base_url('online_store/delete_banner'); ?>/'+id, {method:'POST'})
  .then(r=>r.json()).then(res=>{ if(res.status==='success'){ toastr.success(res.message); location.reload(); } else { toastr.error(res.message || 'Failed to delete'); } });
}
</script>
</body>
</html>
