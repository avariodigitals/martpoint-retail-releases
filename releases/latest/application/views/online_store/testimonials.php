<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
<style>
.testimonial-photo-preview { width:50px; height:50px; object-fit:cover; border-radius:50%; border:1px solid #ddd; }
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
          <!-- Sync from Google -->
          <div class="sync-box">
            <h4 style="margin-top:0; margin-bottom:10px;"><i class="fa fa-google text-danger"></i> Import from Google My Business</h4>
            <p style="font-size:13px; color:#666; margin-bottom:12px;">
              Enter your Google Places API Key and GMB Place ID in <a href="<?= base_url('online_store/settings'); ?>">Settings</a>, then click Import to pull your Google reviews.<br>
              <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank">How to find your Place ID</a>
            </p>
            <button class="btn btn-danger" onclick="fetchGmb()" id="btn-gmb"><i class="fa fa-refresh"></i> Import Google Reviews</button>
          </div>

          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Testimonials</h3>
              <div class="box-tools">
                <button class="btn btn-sm btn-success" onclick="openModal()"><i class="fa fa-plus"></i> Add Testimonial</button>
              </div>
            </div>
            <div class="box-body table-responsive">
              <table class="table table-bordered table-striped">
                <thead><tr><th>Photo</th><th>Customer</th><th>Text</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                  <?php foreach($testimonials as $t): ?>
                  <tr data-id="<?= $t->id; ?>" data-name="<?= htmlspecialchars($t->customer_name); ?>" data-text="<?= htmlspecialchars($t->testimonial_text); ?>" data-rating="<?= $t->rating; ?>" data-enabled="<?= $t->is_enabled; ?>" data-order="<?= $t->sort_order; ?>">
                    <td><?php if($t->customer_photo): ?><img src="<?= base_url($t->customer_photo); ?>" class="testimonial-photo-preview"><?php else: ?><span class="text-muted">-</span><?php endif; ?></td>
                    <td><?= htmlspecialchars($t->customer_name); ?></td>
                    <td><?= htmlspecialchars(substr($t->testimonial_text, 0, 60)) . (strlen($t->testimonial_text) > 60 ? '...' : ''); ?></td>
                    <td><?= str_repeat('&#9733;', $t->rating) . str_repeat('&#9734;', 5 - $t->rating); ?></td>
                    <td><span class="label label-<?= $t->is_enabled ? 'success' : 'default'; ?>"><?= $t->is_enabled ? 'Active' : 'Inactive'; ?></span></td>
                    <td>
                      <button class="btn btn-xs btn-primary" onclick="editTestimonial(this)">Edit</button>
                      <button class="btn btn-xs btn-danger" onclick="deleteTestimonial(<?= $t->id; ?>)">Delete</button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if(empty($testimonials)): ?>
                  <tr><td colspan="6" class="text-center text-muted">No testimonials yet.</td></tr>
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

<div class="modal fade" id="testimonialModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="testimonialForm" method="post" enctype="multipart/form-data" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="testimonial_id" id="testimonial_id" value="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="modalTitle">Add Testimonial</h4>
        </div>
        <div class="modal-body">
          <div class="form-group"><label>Customer Name</label><input type="text" class="form-control" name="customer_name" id="customer_name" required></div>
          <div class="form-group"><label>Testimonial</label><textarea class="form-control" name="testimonial_text" id="testimonial_text" rows="3" required></textarea></div>
          <div class="form-group"><label>Rating (1-5)</label><select class="form-control" name="rating" id="rating"><?php for($i=5;$i>=1;$i--): ?><option value="<?= $i; ?>"><?= $i; ?> Star<?= $i>1?'s':''; ?></option><?php endfor; ?></select></div>
          <div class="form-group"><label>Customer Photo</label><input type="file" class="form-control" name="customer_photo" id="customer_photo" accept="image/*"></div>
          <div class="form-group"><label>Sort Order</label><input type="number" class="form-control" name="sort_order" id="sort_order" value="0"></div>
          <div class="form-group"><label><input type="checkbox" name="is_enabled" id="is_enabled" value="1" checked> Active</label></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="saveTestimonial()">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };
function fetchGmb(){
  $('#btn-gmb').attr('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Importing...');
  $.post('<?= base_url('online_store/fetch_gmb_reviews'); ?>', {
    '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>'
  }, function(res){
    $('#btn-gmb').attr('disabled',false).html('<i class="fa fa-refresh"></i> Import Google Reviews');
    if(res.status==='success'){ toastr.success(res.message); location.reload(); } else { toastr.error(res.message || 'Import failed'); }
  }, 'json');
}
function openModal(){ $('#testimonial_id').val(''); $('#customer_name').val(''); $('#testimonial_text').val(''); $('#rating').val(5); $('#sort_order').val(0); $('#is_enabled').prop('checked',true); $('#modalTitle').text('Add Testimonial'); $('#testimonialModal').modal('show'); }
function editTestimonial(btn){
  const tr = $(btn).closest('tr');
  $('#testimonial_id').val(tr.data('id'));
  $('#customer_name').val(tr.data('name'));
  $('#testimonial_text').val(tr.data('text'));
  $('#rating').val(tr.data('rating'));
  $('#sort_order').val(tr.data('order'));
  $('#is_enabled').prop('checked', tr.data('enabled') == 1);
  $('#modalTitle').text('Edit Testimonial');
  $('#testimonialModal').modal('show');
}
function saveTestimonial(){
  const fd = new FormData(document.getElementById('testimonialForm'));
  $.ajax({ url:'<?= base_url('online_store/save_testimonial'); ?>', type:'POST', data:fd, processData:false, contentType:false, success:function(res){ if(res.status==='success'){ toastr.success(res.message); location.reload(); } else { toastr.error(res.message || 'Failed to save'); } } });
}
function deleteTestimonial(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Delete this testimonial?')) return;
    doDeleteTestimonial(id);
    return;
  }
  swal({
    title: "Delete Testimonial?",
    text: "This action cannot be undone.",
    icon: "warning",
    buttons: true,
    dangerMode: true
  }).then(function(willDelete){
    if(willDelete) doDeleteTestimonial(id);
  });
}
function doDeleteTestimonial(id){
  $.post('<?= base_url('online_store/delete_testimonial/'); ?>'+id, { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' }, function(res){ if(res.status==='success'){ toastr.success(res.message); location.reload(); } else { toastr.error(res.message || 'Failed to delete'); } }, 'json');
}
</script>
</body>
</html>
