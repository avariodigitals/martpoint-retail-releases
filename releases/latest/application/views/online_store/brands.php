<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
<style>
.brand-logo-preview { width:60px; height:60px; object-fit:contain; border:1px solid #ddd; border-radius:4px; background:#f8f9fa; }
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
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Brands</h3>
              <div class="box-tools">
                <button class="btn btn-sm btn-success" onclick="openModal()"><i class="fa fa-plus"></i> Add Brand</button>
              </div>
            </div>
            <div class="box-body table-responsive">
              <table class="table table-bordered table-striped">
                <thead><tr><th>Logo</th><th>Name</th><th>URL</th><th>Status</th><th>Order</th><th>Actions</th></tr></thead>
                <tbody>
                  <?php foreach($brands as $b): ?>
                  <tr data-id="<?= $b->id; ?>" data-name="<?= htmlspecialchars($b->brand_name); ?>" data-url="<?= htmlspecialchars($b->brand_url); ?>" data-enabled="<?= $b->is_enabled; ?>" data-order="<?= $b->sort_order; ?>">
                    <td><?php if($b->brand_logo): ?><img src="<?= base_url($b->brand_logo); ?>" class="brand-logo-preview"><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
                    <td><?= htmlspecialchars($b->brand_name); ?></td>
                    <td><?= $b->brand_url ? '<a href="'.htmlspecialchars($b->brand_url).'" target="_blank">Link</a>' : '-'; ?></td>
                    <td><span class="label label-<?= $b->is_enabled ? 'success' : 'default'; ?>"><?= $b->is_enabled ? 'Active' : 'Inactive'; ?></span></td>
                    <td><?= (int)$b->sort_order; ?></td>
                    <td>
                      <button class="btn btn-xs btn-primary" onclick="editBrand(this)">Edit</button>
                      <button class="btn btn-xs btn-danger" onclick="deleteBrand(<?= $b->id; ?>)">Delete</button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if(empty($brands)): ?>
                  <tr><td colspan="6" class="text-center text-muted">No brands yet.</td></tr>
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
          <div class="form-group"><label>Brand Name</label><input type="text" class="form-control" name="brand_name" id="brand_name" required></div>
          <div class="form-group"><label>Brand URL (optional)</label><input type="url" class="form-control" name="brand_url" id="brand_url" placeholder="https://..."></div>
          <div class="form-group"><label>Logo</label><input type="file" class="form-control" name="brand_logo" id="brand_logo" accept="image/*"></div>
          <div class="form-group"><label>Sort Order</label><input type="number" class="form-control" name="sort_order" id="sort_order" value="0"></div>
          <div class="form-group"><label><input type="checkbox" name="is_enabled" id="is_enabled" value="1" checked> Active</label></div>
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
  $.ajax({ url:'<?= base_url('online_store/save_brand'); ?>', type:'POST', data:fd, processData:false, contentType:false, success:function(res){ if(res.status==='success'){ toastr.success(res.message); location.reload(); } else { toastr.error(res.message || 'Failed to save'); } } });
}
function deleteBrand(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Delete this brand?')) return;
    doDeleteBrand(id);
    return;
  }
  swal({
    title: "Delete Brand?",
    text: "This action cannot be undone.",
    icon: "warning",
    buttons: true,
    dangerMode: true
  }).then(function(willDelete){
    if(willDelete) doDeleteBrand(id);
  });
}
function doDeleteBrand(id){
  $.post('<?= base_url('online_store/delete_brand/'); ?>'+id, { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' }, function(res){ if(res.status==='success'){ toastr.success(res.message); location.reload(); } else { toastr.error(res.message || 'Failed to delete'); } }, 'json');
}
</script>
</body>
</html>
