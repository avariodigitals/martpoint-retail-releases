<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
<style>
.ig-preview { width:80px; height:80px; object-fit:cover; border-radius:6px; border:1px solid #ddd; }
.sync-box { background:#f8f9fa; border:1px dashed #ccc; border-radius:6px; padding:20px; margin-bottom:20px; }
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
        <div class="col-md-8">
          <!-- Sync from Instagram -->
          <div class="sync-box">
            <h4 style="margin-top:0; margin-bottom:10px;"><i class="fa fa-refresh text-primary"></i> Auto-Fetch from Instagram</h4>
            <p style="font-size:13px; color:#666; margin-bottom:12px;">
              Enter your Instagram Access Token in <a href="<?= base_url('online_store/settings'); ?>">Settings</a>, then click Fetch to automatically pull your latest 10 posts.<br>
              <a href="https://developers.facebook.com/docs/instagram-basic-display-api" target="_blank">How to get an access token</a>
            </p>
            <button class="btn btn-primary" onclick="fetchInstagram()" id="btn-fetch"><i class="fa fa-refresh"></i> Fetch Latest 10 Posts</button>
          </div>

          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Gallery Posts</h3>
              <div class="box-tools">
                <button class="btn btn-sm btn-success" onclick="openModal()"><i class="fa fa-plus"></i> Add Manually</button>
              </div>
            </div>
            <div class="box-body table-responsive">
              <table class="table table-bordered table-striped">
                <thead><tr><th>Image</th><th>Caption</th><th>Link</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                  <?php foreach($posts as $p): ?>
                  <tr data-id="<?= $p->id; ?>" data-caption="<?= htmlspecialchars($p->caption); ?>" data-link="<?= htmlspecialchars($p->link_url); ?>" data-enabled="<?= $p->is_enabled; ?>" data-order="<?= $p->sort_order; ?>">
                    <td><img src="<?= base_url($p->image_url); ?>" class="ig-preview"></td>
                    <td><?= htmlspecialchars($p->caption ?: '-'); ?></td>
                    <td><?= $p->link_url ? '<a href="'.htmlspecialchars($p->link_url).'" target="_blank">Link</a>' : '-'; ?></td>
                    <td><span class="label label-<?= $p->is_enabled ? 'success' : 'default'; ?>"><?= $p->is_enabled ? 'Active' : 'Inactive'; ?></span></td>
                    <td>
                      <button class="btn btn-xs btn-primary" onclick="editPost(this)">Edit</button>
                      <button class="btn btn-xs btn-danger" onclick="deletePost(<?= $p->id; ?>)">Delete</button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if(empty($posts)): ?>
                  <tr><td colspan="5" class="text-center text-muted">No posts yet. Fetch from Instagram or add manually.</td></tr>
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

<div class="modal fade" id="postModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="postForm" method="post" enctype="multipart/form-data" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="post_id" id="post_id" value="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="modalTitle">Add Post</h4>
        </div>
        <div class="modal-body">
          <div class="form-group"><label>Image</label><input type="file" class="form-control" name="post_image" id="post_image" accept="image/*"></div>
          <div class="form-group"><label>Caption</label><input type="text" class="form-control" name="caption" id="caption" placeholder="Short caption..."></div>
          <div class="form-group"><label>Link URL</label><input type="url" class="form-control" name="link_url" id="link_url" placeholder="https://instagram.com/..."></div>
          <div class="form-group"><label>Sort Order</label><input type="number" class="form-control" name="sort_order" id="sort_order" value="0"></div>
          <div class="form-group"><label><input type="checkbox" name="is_enabled" id="is_enabled" value="1" checked> Active</label></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="savePost()">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };
function fetchInstagram(){
  $('#btn-fetch').attr('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Fetching...');
  $.post('<?= base_url('online_store/fetch_instagram'); ?>', {
    '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>'
  }, function(res){
    $('#btn-fetch').attr('disabled',false).html('<i class="fa fa-refresh"></i> Fetch Latest 10 Posts');
    if(res.status==='success'){ toastr.success(res.message); location.reload(); } else { toastr.error(res.message || 'Fetch failed'); }
  }, 'json');
}
function openModal(){ $('#post_id').val(''); $('#caption').val(''); $('#link_url').val(''); $('#sort_order').val(0); $('#is_enabled').prop('checked',true); $('#modalTitle').text('Add Post'); $('#postModal').modal('show'); }
function editPost(btn){
  const tr = $(btn).closest('tr');
  $('#post_id').val(tr.data('id'));
  $('#caption').val(tr.data('caption'));
  $('#link_url').val(tr.data('link'));
  $('#sort_order').val(tr.data('order'));
  $('#is_enabled').prop('checked', tr.data('enabled') == 1);
  $('#modalTitle').text('Edit Post');
  $('#postModal').modal('show');
}
function savePost(){
  const fd = new FormData(document.getElementById('postForm'));
  $.ajax({ url:'<?= base_url('online_store/save_instagram'); ?>', type:'POST', data:fd, processData:false, contentType:false, success:function(res){ if(res.status==='success'){ toastr.success(res.message); location.reload(); } else { toastr.error(res.message || 'Failed to save'); } } });
}
function deletePost(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Delete this post?')) return;
    doDeletePost(id);
    return;
  }
  swal({
    title: "Delete Post?",
    text: "This action cannot be undone.",
    icon: "warning",
    buttons: true,
    dangerMode: true
  }).then(function(willDelete){
    if(willDelete) doDeletePost(id);
  });
}
function doDeletePost(id){
  $.post('<?= base_url('online_store/delete_instagram/'); ?>'+id, { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' }, function(res){ if(res.status==='success'){ toastr.success(res.message); location.reload(); } else { toastr.error(res.message || 'Failed to delete'); } }, 'json');
}
</script>
</body>
</html>
