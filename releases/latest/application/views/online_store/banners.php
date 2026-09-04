<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Homepage and promotional banners shown on your storefront</div>
  </div>
  <a href="<?= base_url('online_store/banner_form'); ?>" class="mp-qa-btn green"><i class="fa fa-plus"></i> Add Banner</a>
</div>

<div class="mp-table-wrap">
  <div class="mp-card-head"><h3>Banners</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="os-banners-table" class="table mp-dt-table" width="100%">
        <thead><tr><th>Order</th><th>Type</th><th>Title</th><th>Subtitle</th><th>Status</th><th>Dates</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($banners as $b): ?>
          <tr>
            <td><?= (int)$b->display_order; ?></td>
            <td><span class="label label-<?= $b->banner_type == 'hero' ? 'info' : 'warning'; ?>"><?= $b->banner_type == 'hero' ? 'Hero' : 'Promo'; ?></span></td>
            <td class="row-name"><?= htmlspecialchars($b->banner_title); ?></td>
            <td><?= htmlspecialchars($b->banner_subtitle); ?></td>
            <td><span class="label label-<?= $b->status ? 'success' : 'default'; ?>"><?= $b->status ? 'Active' : 'Inactive'; ?></span></td>
            <td><?= htmlspecialchars($b->start_date ?: 'Always'); ?> to <?= htmlspecialchars($b->end_date ?: 'Always'); ?></td>
            <td>
              <div class="mp-actions">
                <a href="<?= base_url('online_store/banner_form/' . $b->id); ?>" class="mp-edit" title="Edit"><i class="fa fa-pencil"></i></a>
                <button class="mp-delete" title="Delete" onclick="deleteBanner(<?= (int)$b->id; ?>)"><i class="fa fa-trash"></i></button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($banners)): ?><tr><td colspan="7" class="mp-empty-state">No banners yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };
function deleteBanner(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Delete this banner?')) return;
    doDeleteBanner(id);
    return;
  }
  swal({ title: "Delete Banner?", text: "This action cannot be undone.", icon: "warning", buttons: true, dangerMode: true })
  .then(function(willDelete){ if(willDelete) doDeleteBanner(id); });
}
function doDeleteBanner(id){
  var fd = new FormData();
  fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
  fetch('<?= base_url('online_store/delete_banner'); ?>/'+id, {method:'POST', body:fd})
  .then(r=>r.json()).then(res=>{ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Failed to delete'); } });
}
$(document).ready(function(){
  $('#os-banners-table').DataTable({
    "aLengthMenu": [[10,25,50,100],[10,25,50,100]],
    dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    "order": [[0,"asc"]],
    "responsive": false,
    "columnDefs": [{ "targets": [6], "orderable": false }]
  });
});
</script>
<script>$(".online_store-banners-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
