<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.os-test-photo{width:44px!important;height:44px!important;object-fit:cover!important;border-radius:50%!important;border:1px solid var(--mp-border)!important}
.os-test-ph{width:44px!important;height:44px!important;border-radius:50%!important;background:var(--mp-bg)!important;display:inline-block!important}
.os-stars{color:#F59E0B!important;font-size:14px!important;letter-spacing:1px!important}
.os-stars .empty{color:var(--mp-border)!important}
.os-sync-box{background:var(--mp-bg)!important;border:1px dashed var(--mp-border)!important;border-radius:14px!important;padding:18px 22px!important;margin-bottom:20px!important}
.os-sync-box h4{font-size:14px!important;font-weight:700!important;color:var(--mp-text)!important;margin:0 0 8px!important}
.os-sync-box p{font-size:13px!important;color:var(--mp-muted)!important;margin:0 0 12px!important;line-height:1.5!important}
.os-sync-box a{color:var(--mp-primary)!important;font-weight:600!important;text-decoration:none!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Customer reviews shown on your storefront</div>
  </div>
  <button class="mp-qa-btn green" onclick="openModal()"><i class="fa fa-plus"></i> Add Testimonial</button>
</div>

<div class="os-sync-box">
  <h4><i class="fa fa-google" style="color:var(--mp-danger);"></i> Import from Google My Business</h4>
  <p>Enter your Google Places API Key and GMB Place ID in <a href="<?= base_url('online_store/settings'); ?>">Settings</a>, then click Import to pull your Google reviews.<br><a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank">How to find your Place ID</a></p>
  <button class="mp-qa-btn red" onclick="fetchGmb()" id="btn-gmb"><i class="fa fa-refresh"></i> Import Google Reviews</button>
</div>

<div class="mp-table-wrap">
  <div class="mp-card-head"><h3>Testimonials</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="os-testimonials-table" class="table mp-dt-table" width="100%">
        <thead><tr><th>Photo</th><th>Customer</th><th>Text</th><th>Rating</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($testimonials as $t): ?>
          <tr data-id="<?= (int)$t->id; ?>" data-name="<?= htmlspecialchars($t->customer_name); ?>" data-text="<?= htmlspecialchars($t->testimonial_text); ?>" data-rating="<?= (int)$t->rating; ?>" data-enabled="<?= (int)$t->is_enabled; ?>" data-order="<?= (int)$t->sort_order; ?>">
            <td><?php if($t->customer_photo): ?><img src="<?= base_url($t->customer_photo); ?>" class="os-test-photo" alt=""><?php else: ?><span class="os-test-ph"></span><?php endif; ?></td>
            <td class="row-name"><?= htmlspecialchars($t->customer_name); ?></td>
            <td><?= htmlspecialchars(mb_substr($t->testimonial_text, 0, 60)) . (strlen($t->testimonial_text) > 60 ? '…' : ''); ?></td>
            <td><span class="os-stars"><?= str_repeat('&#9733;', (int)$t->rating) . '<span class="empty">'.str_repeat('&#9734;', 5 - (int)$t->rating).'</span>'; ?></span></td>
            <td><span class="label label-<?= $t->is_enabled ? 'success' : 'default'; ?>"><?= $t->is_enabled ? 'Active' : 'Inactive'; ?></span></td>
            <td>
              <div class="mp-actions">
                <button class="mp-edit" title="Edit" onclick="editTestimonial(this)"><i class="fa fa-pencil"></i></button>
                <button class="mp-delete" title="Delete" onclick="deleteTestimonial(<?= (int)$t->id; ?>)"><i class="fa fa-trash"></i></button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($testimonials)): ?><tr><td colspan="6" class="mp-empty-state">No testimonials yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

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
          <div class="mp-form-group"><label>Customer Name</label><input type="text" class="mp-form-control" name="customer_name" id="customer_name" required></div>
          <div class="mp-form-group"><label>Testimonial</label><textarea class="mp-form-control" name="testimonial_text" id="testimonial_text" rows="3" required></textarea></div>
          <div class="mp-form-group"><label>Rating (1-5)</label><select class="mp-form-control" name="rating" id="rating"><?php for($i=5;$i>=1;$i--): ?><option value="<?= $i; ?>"><?= $i; ?> Star<?= $i>1?'s':''; ?></option><?php endfor; ?></select></div>
          <div class="mp-form-group"><label>Customer Photo</label><input type="file" class="mp-form-control" name="customer_photo" id="customer_photo" accept="image/*" style="padding:8px;"></div>
          <div class="mp-form-group"><label>Sort Order</label><input type="number" class="mp-form-control" name="sort_order" id="sort_order" value="0"></div>
          <div class="mp-form-group"><label style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:var(--mp-ink);margin:0;"><input type="checkbox" name="is_enabled" id="is_enabled" value="1" checked> Active</label></div>
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
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Import failed'); }
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
  $.ajax({ url:'<?= base_url('online_store/save_testimonial'); ?>', type:'POST', data:fd, processData:false, contentType:false, success:function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Failed to save'); } } });
}
function deleteTestimonial(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Delete this testimonial?')) return;
    doDeleteTestimonial(id);
    return;
  }
  swal({ title: "Delete Testimonial?", text: "This action cannot be undone.", icon: "warning", buttons: true, dangerMode: true })
  .then(function(willDelete){ if(willDelete) doDeleteTestimonial(id); });
}
function doDeleteTestimonial(id){
  $.post('<?= base_url('online_store/delete_testimonial/'); ?>'+id, { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' }, function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Failed to delete'); } }, 'json');
}
$(document).ready(function(){
  $('#os-testimonials-table').DataTable({
    "aLengthMenu": [[10,25,50,100],[10,25,50,100]],
    dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    "order": [[1,"asc"]],
    "responsive": false,
    "columnDefs": [{ "targets": [0,3,5], "orderable": false }]
  });
});
</script>
<script>$(".online_store-testimonials-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
