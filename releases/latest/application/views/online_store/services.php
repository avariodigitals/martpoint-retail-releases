<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.os-modal-grid{display:grid!important;grid-template-columns:1fr 1fr!important;gap:16px!important}
.os-modal-grid .full{grid-column:1/-1!important}
.os-check-row{display:flex!important;flex-wrap:wrap!important;gap:18px!important;padding:8px 0!important}
.os-check-row label{display:inline-flex!important;align-items:center!important;gap:8px!important;font-size:13px!important;font-weight:600!important;color:var(--mp-ink)!important;cursor:pointer!important;margin:0!important}
.os-check-row input[type=checkbox]{width:18px!important;height:18px!important;cursor:pointer!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Services available for online booking and ordering</div>
  </div>
  <button class="mp-qa-btn green" data-toggle="modal" data-target="#modal-service"><i class="fa fa-plus"></i> Add Service</button>
</div>

<div class="mp-table-wrap">
  <div class="mp-card-head"><h3>Services</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="os-services-table" class="table mp-dt-table" width="100%">
        <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Duration</th><th>Online</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($services as $s): ?>
          <tr>
            <td class="row-name"><?= htmlspecialchars($s->service_name); ?></td>
            <td><?= htmlspecialchars($s->category_name ?: 'Uncategorized'); ?></td>
            <td class="amt"><?= store_number_format($s->price); ?></td>
            <td><?= htmlspecialchars($s->service_duration ?: '-'); ?></td>
            <td><?= $s->available_online ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>'; ?></td>
            <td><?= $s->status ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>'; ?></td>
            <td>
              <div class="mp-actions">
                <button class="mp-edit" title="Edit" onclick="editService(<?= (int)$s->id; ?>)"><i class="fa fa-pencil"></i></button>
                <button class="mp-delete" title="Delete" onclick="deleteService(<?= (int)$s->id; ?>)"><i class="fa fa-trash"></i></button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($services)): ?><tr><td colspan="7" class="mp-empty-state">No services found.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
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
        <div class="mp-form-group"><label>Service Name</label><input type="text" class="mp-form-control" id="service_name" placeholder="Service name"></div>
        <div class="os-modal-grid">
          <div class="mp-form-group"><label>Category</label>
            <select class="form-control select2" id="category_id" style="width:100%;"><option value="0">- None -</option><?php foreach($categories as $c): ?><option value="<?= (int)$c->id; ?>"><?= htmlspecialchars($c->category_name); ?></option><?php endforeach; ?></select>
          </div>
          <div class="mp-form-group"><label>Duration</label><input type="text" class="mp-form-control" id="service_duration" placeholder="e.g. 1 hour"></div>
          <div class="mp-form-group"><label>Price</label><input type="number" class="mp-form-control" id="price" step="0.01"></div>
          <div class="mp-form-group"><label>Discount Price</label><input type="number" class="mp-form-control" id="discount_price" step="0.01"></div>
          <div class="mp-form-group"><label>Location Type</label>
            <select class="mp-form-control" id="location_type"><option value="in-store">In-store</option><option value="customer-location">Customer Location</option><option value="online">Online/Remote</option></select>
          </div>
          <div class="mp-form-group"><label>Sort Order</label><input type="number" class="mp-form-control" id="sort_order" value="0"></div>
          <div class="mp-form-group"><label>Deposit Required</label>
            <select class="mp-form-control" id="deposit_required"><option value="0">No</option><option value="1">Yes</option></select>
          </div>
          <div class="mp-form-group"><label>Deposit %</label><input type="number" class="mp-form-control" id="deposit_percent" step="0.01" value="0"></div>
        </div>
        <?php if(mp_feature_enabled('staff_assignment')) { ?>
        <div class="mp-form-group"><label>Assigned Staff</label><select class="mp-form-control" id="assigned_staff_id"><option value="">-- Select Staff --</option></select></div>
        <?php } ?>
        <?php if(mp_feature_enabled('staff_commission')) { ?>
        <div class="mp-form-group"><label>Staff Commission (%)</label><input type="number" class="mp-form-control" id="staff_commission_percent" step="0.01" value="0" placeholder="e.g. 10"></div>
        <?php } ?>
        <?php if(mp_feature_enabled('treatment_notes')) { ?>
        <div class="mp-form-group"><label>Treatment Notes Template</label><textarea class="mp-form-control" id="treatment_notes_template" rows="2" placeholder="Default notes for this service"></textarea></div>
        <?php } ?>
        <div class="mp-form-group"><label>Description</label><textarea class="mp-form-control" id="description" rows="3"></textarea></div>
        <div class="os-check-row">
          <label><input type="checkbox" id="available_online" checked> Available Online</label>
          <label><input type="checkbox" id="requires_appointment"> Requires Appointment</label>
          <label><input type="checkbox" id="requires_note"> Requires Customer Note</label>
          <label><input type="checkbox" id="status" checked> Active</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="btn-save-service">Save</button>
      </div>
    </div>
  </div>
</div>

<script>
var services = <?= json_encode($services); ?>;

function editService(id){
  var s = services.find(x => x.id == id);
  if(!s) return;
  $('#service_id').val(s.id);
  $('#service_name').val(s.service_name);
  $('#category_id').val(s.category_id).trigger('change');
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
  $('#deposit_required').val(s.deposit_required || 0);
  $('#deposit_percent').val(s.deposit_percent || 0);
  <?php if(mp_feature_enabled('staff_assignment')) { ?>$('#assigned_staff_id').val(s.assigned_staff_id || '');<?php } ?>
  <?php if(mp_feature_enabled('staff_commission')) { ?>$('#staff_commission_percent').val(s.staff_commission_percent || 0);<?php } ?>
  <?php if(mp_feature_enabled('treatment_notes')) { ?>$('#treatment_notes_template').val(s.treatment_notes_template || '');<?php } ?>
  $('#modal-service').modal('show');
}

function deleteService(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Delete this service?')) return;
    doDeleteService(id);
    return;
  }
  swal({ title: "Delete Service?", text: "This action cannot be undone.", icon: "warning", buttons: true, dangerMode: true })
  .then(function(willDelete){ if(willDelete) doDeleteService(id); });
}
function doDeleteService(id){
  $.post('<?=base_url("online_store/delete_service/");?>' + id, {
    '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
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
    deposit_required: $('#deposit_required').val(),
    deposit_percent: $('#deposit_percent').val(),
    available_online: $('#available_online').is(':checked') ? 1 : 0,
    requires_appointment: $('#requires_appointment').is(':checked') ? 1 : 0,
    requires_note: $('#requires_note').is(':checked') ? 1 : 0,
    status: $('#status').is(':checked') ? 1 : 0,
    <?php if(mp_feature_enabled('staff_assignment')) { ?>assigned_staff_id: $('#assigned_staff_id').val(),<?php } ?>
    <?php if(mp_feature_enabled('staff_commission')) { ?>staff_commission_percent: $('#staff_commission_percent').val() || 0,<?php } ?>
    <?php if(mp_feature_enabled('treatment_notes')) { ?>treatment_notes_template: $('#treatment_notes_template').val(),<?php } ?>
    '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
  };
  $.post('<?=base_url("online_store/save_service");?>', data, function(res){
    if(res.status === 'success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); }
    else { toastr.error(res.message || 'Failed'); }
  }, 'json');
});

$(document).ready(function(){
  $('#category_id').select2({ dropdownParent: $('#modal-service') });
  $('#os-services-table').DataTable({
    "aLengthMenu": [[10,25,50,100],[10,25,50,100]],
    dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    "order": [[0,"asc"]],
    "responsive": false,
    "columnDefs": [{ "targets": [6], "orderable": false }]
  });
});
</script>
<script>$(".online_store-services-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
