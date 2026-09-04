<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.os-ig-preview{width:64px!important;height:64px!important;object-fit:cover!important;border-radius:10px!important;border:1px solid var(--mp-border)!important}
.os-sync-box{background:var(--mp-bg)!important;border:1px dashed var(--mp-border)!important;border-radius:14px!important;padding:18px 22px!important;margin-bottom:20px!important}
.os-sync-box h4{font-size:14px!important;font-weight:700!important;color:var(--mp-text)!important;margin:0 0 8px!important}
.os-sync-box p{font-size:13px!important;color:var(--mp-muted)!important;margin:0 0 12px!important;line-height:1.5!important}
.os-sync-box a{color:var(--mp-primary)!important;font-weight:600!important;text-decoration:none!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Instagram-style photo gallery shown on your storefront</div>
  </div>
  <button class="mp-qa-btn green" onclick="openModal()"><i class="fa fa-plus"></i> Add Manually</button>
</div>

<div class="os-sync-box">
  <h4><i class="fa fa-refresh" style="color:var(--mp-primary);"></i> Auto-Fetch from Instagram</h4>
  <p>Enter your Instagram Access Token in <a href="<?= base_url('online_store/settings'); ?>">Settings</a>, then click Fetch to automatically pull your latest 10 posts.<br><a href="https://developers.facebook.com/docs/instagram-basic-display-api" target="_blank">How to get an access token</a></p>
  <button class="mp-qa-btn blue" onclick="fetchInstagram()" id="btn-fetch"><i class="fa fa-refresh"></i> Fetch Latest 10 Posts</button>
</div>

<div class="mp-table-wrap">
  <div class="mp-card-head"><h3>Gallery Posts</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="os-instagram-table" class="table mp-dt-table" width="100%">
        <thead><tr><th>Image</th><th>Caption</th><th>Link</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($posts as $p): ?>
          <tr data-id="<?= (int)$p->id; ?>" data-caption="<?= htmlspecialchars($p->caption); ?>" data-link="<?= htmlspecialchars($p->link_url); ?>" data-enabled="<?= (int)$p->is_enabled; ?>" data-order="<?= (int)$p->sort_order; ?>">
            <td><img src="<?= base_url($p->image_url); ?>" class="os-ig-preview" alt=""></td>
            <td class="row-name"><?= htmlspecialchars($p->caption ?: '-'); ?></td>
            <td><?= $p->link_url ? '<a href="'.htmlspecialchars($p->link_url).'" target="_blank">Link</a>' : '-'; ?></td>
            <td><span class="label label-<?= $p->is_enabled ? 'success' : 'default'; ?>"><?= $p->is_enabled ? 'Active' : 'Inactive'; ?></span></td>
            <td>
              <div class="mp-actions">
                <button class="mp-edit" title="Edit" onclick="editPost(this)"><i class="fa fa-pencil"></i></button>
                <button class="mp-delete" title="Delete" onclick="deletePost(<?= (int)$p->id; ?>)"><i class="fa fa-trash"></i></button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($posts)): ?><tr><td colspan="5" class="mp-empty-state">No posts yet. Fetch from Instagram or add manually.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

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
          <div class="mp-form-group"><label>Image</label><input type="file" class="mp-form-control" name="post_image" id="post_image" accept="image/*" style="padding:8px;"></div>
          <div class="mp-form-group"><label>Caption</label><input type="text" class="mp-form-control" name="caption" id="caption" placeholder="Short caption..."></div>
          <div class="mp-form-group"><label>Link URL</label><input type="url" class="mp-form-control" name="link_url" id="link_url" placeholder="https://instagram.com/..."></div>
          <div class="mp-form-group"><label>Sort Order</label><input type="number" class="mp-form-control" name="sort_order" id="sort_order" value="0"></div>
          <div class="mp-form-group"><label style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:var(--mp-ink);margin:0;"><input type="checkbox" name="is_enabled" id="is_enabled" value="1" checked> Active</label></div>
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
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Fetch failed'); }
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
  $.ajax({ url:'<?= base_url('online_store/save_instagram'); ?>', type:'POST', data:fd, processData:false, contentType:false, success:function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Failed to save'); } } });
}
function deletePost(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Delete this post?')) return;
    doDeletePost(id);
    return;
  }
  swal({ title: "Delete Post?", text: "This action cannot be undone.", icon: "warning", buttons: true, dangerMode: true })
  .then(function(willDelete){ if(willDelete) doDeletePost(id); });
}
function doDeletePost(id){
  $.post('<?= base_url('online_store/delete_instagram/'); ?>'+id, { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' }, function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Failed to delete'); } }, 'json');
}
$(document).ready(function(){
  $('#os-instagram-table').DataTable({
    "aLengthMenu": [[10,25,50,100],[10,25,50,100]],
    dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    "order": [[1,"asc"]],
    "responsive": false,
    "columnDefs": [{ "targets": [0,4], "orderable": false }]
  });
});
</script>
<script>$(".online_store-instagram-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
