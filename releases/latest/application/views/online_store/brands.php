<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.os-brand-logo{width:48px!important;height:48px!important;object-fit:contain!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;background:var(--mp-bg)!important}
.os-brand-ph{width:48px!important;height:48px!important;border-radius:8px!important;background:var(--mp-bg)!important;display:inline-block!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Brand logos shown on your storefront</div>
  </div>
  <button class="mp-qa-btn green" onclick="openModal()"><i class="fa fa-plus"></i> Add Brand</button>
</div>

<div class="mp-table-wrap">
  <div class="mp-card-head"><h3>Brands</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="os-brands-table" class="table mp-dt-table" width="100%">
        <thead><tr><th>Logo</th><th>Name</th><th>URL</th><th>Status</th><th>Order</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($brands as $b): ?>
          <tr data-id="<?= (int)$b->id; ?>" data-name="<?= htmlspecialchars($b->brand_name); ?>" data-url="<?= htmlspecialchars($b->brand_url); ?>" data-enabled="<?= (int)$b->is_enabled; ?>" data-order="<?= (int)$b->sort_order; ?>">
            <td><?php if($b->brand_logo): ?><img src="<?= base_url($b->brand_logo); ?>" class="os-brand-logo" alt=""><?php else: ?><span class="os-brand-ph"></span><?php endif; ?></td>
            <td class="row-name"><?= htmlspecialchars($b->brand_name); ?></td>
            <td><?= $b->brand_url ? '<a href="'.htmlspecialchars($b->brand_url).'" target="_blank">Link</a>' : '-'; ?></td>
            <td><span class="label label-<?= $b->is_enabled ? 'success' : 'default'; ?>"><?= $b->is_enabled ? 'Active' : 'Inactive'; ?></span></td>
            <td><?= (int)$b->sort_order; ?></td>
            <td>
              <div class="mp-actions">
                <button class="mp-edit" title="Edit" onclick="editBrand(this)"><i class="fa fa-pencil"></i></button>
                <button class="mp-delete" title="Delete" onclick="deleteBrand(<?= (int)$b->id; ?>)"><i class="fa fa-trash"></i></button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($brands)): ?><tr><td colspan="6" class="mp-empty-state">No brands yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="brandModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="brandForm" method="post" enctype="multipart/form-data" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="brand_id" id="brand_id" value="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="modalTitle">Add Brand</h4>
        </div>
        <div class="modal-body">
          <div class="mp-form-group"><label>Brand Name</label><input type="text" class="mp-form-control" name="brand_name" id="brand_name" required></div>
          <div class="mp-form-group"><label>Brand URL (optional)</label><input type="url" class="mp-form-control" name="brand_url" id="brand_url" placeholder="https://..."></div>
          <div class="mp-form-group"><label>Logo</label><input type="file" class="mp-form-control" name="brand_logo" id="brand_logo" accept="image/*" style="padding:8px;"></div>
          <div class="mp-form-group"><label>Sort Order</label><input type="number" class="mp-form-control" name="sort_order" id="sort_order" value="0"></div>
          <div class="mp-form-group"><label style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:var(--mp-ink);margin:0;"><input type="checkbox" name="is_enabled" id="is_enabled" value="1" checked> Active</label></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="saveBrand()">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };
function openModal(){ $('#brand_id').val(''); $('#brand_name').val(''); $('#brand_url').val(''); $('#sort_order').val(0); $('#is_enabled').prop('checked',true); $('#modalTitle').text('Add Brand'); $('#brandModal').modal('show'); }
function editBrand(btn){
  const tr = $(btn).closest('tr');
  $('#brand_id').val(tr.data('id'));
  $('#brand_name').val(tr.data('name'));
  $('#brand_url').val(tr.data('url'));
  $('#sort_order').val(tr.data('order'));
  $('#is_enabled').prop('checked', tr.data('enabled') == 1);
  $('#modalTitle').text('Edit Brand');
  $('#brandModal').modal('show');
}
function saveBrand(){
  const fd = new FormData(document.getElementById('brandForm'));
  $.ajax({ url:'<?= base_url('online_store/save_brand'); ?>', type:'POST', data:fd, processData:false, contentType:false, success:function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Failed to save'); } } });
}
function deleteBrand(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Delete this brand?')) return;
    doDeleteBrand(id);
    return;
  }
  swal({ title: "Delete Brand?", text: "This action cannot be undone.", icon: "warning", buttons: true, dangerMode: true })
  .then(function(willDelete){ if(willDelete) doDeleteBrand(id); });
}
function doDeleteBrand(id){
  $.post('<?= base_url('online_store/delete_brand/'); ?>'+id, { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' }, function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Failed to delete'); } }, 'json');
}
$(document).ready(function(){
  $('#os-brands-table').DataTable({
    "aLengthMenu": [[10,25,50,100],[10,25,50,100]],
    dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    "order": [[4,"asc"]],
    "responsive": false,
    "columnDefs": [{ "targets": [0,5], "orderable": false }]
  });
});
</script>
<script>$(".online_store-brands-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
