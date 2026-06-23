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
              <h3 class="box-title"><i class="fa fa-wrench text-blue"></i> Services</h3>
              <div class="box-tools">
                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modal-service"><i class="fa fa-plus"></i> Add Service</button>
              </div>
            </div>
            <div class="box-body table-responsive">
              <table class="table table-bordered table-striped">
                <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Duration</th><th>Online</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                  <?php foreach($services as $s): ?>
                  <tr>
                    <td><?= htmlspecialchars($s->service_name); ?></td>
                    <td><?= htmlspecialchars($s->category_name ?: 'Uncategorized'); ?></td>
                    <td><?= store_number_format($s->price); ?></td>
                    <td><?= htmlspecialchars($s->service_duration ?: '-'); ?></td>
                    <td><?= $s->available_online ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>'; ?></td>
                    <td><?= $s->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>'; ?></td>
                    <td>
                      <button class="btn btn-xs btn-primary" onclick="editService(<?= $s->id; ?>)"><i class="fa fa-pencil"></i></button>
                      <button class="btn btn-xs btn-danger" onclick="deleteService(<?= $s->id; ?>)"><i class="fa fa-trash"></i></button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if(empty($services)): ?><tr><td colspan="7" class="text-center text-muted">No services found.</td></tr><?php endif; ?>
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

<div class="modal fade" id="modal-service">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Add/Edit Service</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="service_id">
        <div class="form-group"><label>Service Name</label><input type="text" class="form-control" id="service_name" placeholder="Service name"></div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group"><label>Category</label>
              <select class="form-control" id="category_id"><option value="0">- None -</option><?php foreach($categories as $c): ?><option value="<?= $c->id; ?>"><?= htmlspecialchars($c->category_name); ?></option><?php endforeach; ?></select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group"><label>Duration</label><input type="text" class="form-control" id="service_duration" placeholder="e.g. 1 hour"></div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group"><label>Price</label><input type="number" class="form-control" id="price" step="0.01"></div>
          </div>
          <div class="col-md-6">
            <div class="form-group"><label>Discount Price</label><input type="number" class="form-control" id="discount_price" step="0.01"></div>
          </div>
        </div>
        <div class="form-group"><label>Description</label><textarea class="form-control" id="description" rows="3"></textarea></div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group"><label>Location Type</label>
              <select class="form-control" id="location_type"><option value="in-store">In-store</option><option value="customer-location">Customer Location</option><option value="online">Online/Remote</option></select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group"><label>Sort Order</label><input type="number" class="form-control" id="sort_order" value="0"></div>
          </div>
        </div>
        <div class="form-group">
          <div class="checkbox icheck"><label><input type="checkbox" id="available_online" checked> Available Online</label></div>
          <div class="checkbox icheck"><label><input type="checkbox" id="requires_appointment"> Requires Appointment</label></div>
          <div class="checkbox icheck"><label><input type="checkbox" id="requires_note"> Requires Customer Note</label></div>
          <div class="checkbox icheck"><label><input type="checkbox" id="status" checked> Active</label></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="btn-save-service">Save</button>
      </div>
    </div>
  </div>
</div>

<?php $this->load->view('comman/code_js_sound.php');?>
<?php $this->load->view('comman/code_js.php');?>
<script>
var categories = <?= json_encode($categories); ?>;
var services = <?= json_encode($services); ?>;

function editService(id){
  var s = services.find(x => x.id == id);
  if(!s) return;
  $('#service_id').val(s.id);
  $('#service_name').val(s.service_name);
  $('#category_id').val(s.category_id);
  $('#price').val(s.price);
  $('#discount_price').val(s.discount_price);
  $('#service_duration').val(s.service_duration);
  $('#description').val(s.description);
  $('#location_type').val(s.location_type);
  $('#sort_order').val(s.sort_order);
  $('#available_online').prop('checked', s.available_online == 1);
  $('#requires_appointment').prop('checked', s.requires_appointment == 1);
  $('#requires_note').prop('checked', s.requires_note == 1);
  $('#status').prop('checked', s.status == 1);
  $('#modal-service').modal('show');
}

function deleteService(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Delete this service?')) return;
    doDeleteService(id);
    return;
  }
  swal({
    title: "Delete Service?",
    text: "This action cannot be undone.",
    icon: "warning",
    buttons: true,
    dangerMode: true
  }).then(function(willDelete){
    if(willDelete) doDeleteService(id);
  });
}
function doDeleteService(id){
  $.post('<?=base_url("online_store/delete_service/");?>' + id, {
    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
  }, function(res){
    if(res.status === 'success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); }
    else { toastr.error(res.message); }
  }, 'json');
}

$('#btn-save-service').click(function(){
  var data = {
    service_id: $('#service_id').val(),
    service_name: $('#service_name').val(),
    category_id: $('#category_id').val(),
    price: $('#price').val(),
    discount_price: $('#discount_price').val(),
    service_duration: $('#service_duration').val(),
    description: $('#description').val(),
    location_type: $('#location_type').val(),
    sort_order: $('#sort_order').val(),
    available_online: $('#available_online').is(':checked') ? 1 : 0,
    requires_appointment: $('#requires_appointment').is(':checked') ? 1 : 0,
    requires_note: $('#requires_note').is(':checked') ? 1 : 0,
    status: $('#status').is(':checked') ? 1 : 0,
    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
  };
  $.post('<?=base_url("online_store/save_service");?>', data, function(res){
    if(res.status === 'success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); }
    else { toastr.error(res.message || 'Failed'); }
  }, 'json');
});
</script>
<script>$(".online-store-services-active-li").addClass("active");</script>
</body>
</html>
