<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?></h1>
    <ol class="breadcrumb">
      <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active"><?= $page_title; ?></li>
    </ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-truck"></i> Delivery Schedules</h3>
            <div class="box-tools pull-right">
              <a href="<?= base_url('operations/delivery_schedule_form'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> New Delivery Route</a>
              <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#driverModal"><i class="fa fa-user"></i> Drivers</button>
            </div>
          </div>
          <div class="box-body">
            <div class="table-responsive">
              <table id="delivery_table" class="table table-bordered table-striped" width="100%">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Route</th>
                    <th>Date</th>
                    <th>Driver</th>
                    <th>Vehicle</th>
                    <th>Status</th>
                    <th>Progress</th>
                    <th>Action</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
</div>

<!-- Driver Modal -->
<div class="modal fade" id="driverModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-navy">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title" style="color:#fff;"><i class="fa fa-users"></i> Delivery Drivers</h4>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-striped" id="driver_table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Phone</th>
              <th>Vehicle</th>
              <th>Plate</th>
              <th>License</th>
              <th>Status</th>
              <th style="width:100px;"></th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
        <hr>
        <h5 id="driver_form_title"><i class="fa fa-plus"></i> Add New Driver</h5>
        <form id="driver_form">
          <input type="hidden" name="id" id="driver_id_field" value="">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
          <div class="row">
            <div class="col-sm-6"><label>Full Name *</label><input type="text" class="form-control input-sm" name="name" required></div>
            <div class="col-sm-3"><label>Phone *</label><input type="text" class="form-control input-sm" name="phone" required></div>
            <div class="col-sm-3"><label>Email</label><input type="email" class="form-control input-sm" name="email"></div>
          </div>
          <div class="row" style="margin-top:8px;">
            <div class="col-sm-6"><label>Address</label><input type="text" class="form-control input-sm" name="address" placeholder="Residential address"></div>
            <div class="col-sm-3"><label>Emergency Contact Name</label><input type="text" class="form-control input-sm" name="emergency_contact_name"></div>
            <div class="col-sm-3"><label>Emergency Contact Phone</label><input type="text" class="form-control input-sm" name="emergency_contact_phone"></div>
          </div>
          <div class="row" style="margin-top:8px;">
            <div class="col-sm-3"><label>NIN</label><input type="text" class="form-control input-sm" name="nin" placeholder="National ID Number"></div>
            <div class="col-sm-3"><label>Driver's License</label><input type="text" class="form-control input-sm" name="driver_license" placeholder="FRSC License No"></div>
            <div class="col-sm-3"><label>License Expiry</label><input type="date" class="form-control input-sm" name="license_expiry"></div>
            <div class="col-sm-3"><label>Hire Date</label><input type="date" class="form-control input-sm" name="hire_date"></div>
          </div>
          <div class="row" style="margin-top:8px;">
            <div class="col-sm-3"><label>Vehicle Make/Model</label><input type="text" class="form-control input-sm" name="vehicle" placeholder="e.g. Toyota Hilux"></div>
            <div class="col-sm-2"><label>Type</label>
              <select class="form-control input-sm" name="vehicle_type">
                <option value="motorcycle">Motorcycle</option>
                <option value="car">Car</option>
                <option value="van">Van</option>
                <option value="truck">Truck</option>
                <option value="keke">Keke</option>
                <option value="bicycle">Bicycle</option>
              </select>
            </div>
            <div class="col-sm-2"><label>Color</label><input type="text" class="form-control input-sm" name="vehicle_color" placeholder="e.g. White"></div>
            <div class="col-sm-2"><label>Plate No</label><input type="text" class="form-control input-sm" name="license_plate"></div>
            <div class="col-sm-3"><label>Employment</label>
              <select class="form-control input-sm" name="employment_type">
                <option value="full_time">Full Time</option>
                <option value="contract">Contract</option>
                <option value="part_time">Part Time</option>
                <option value="intern">Intern</option>
              </select>
            </div>
          </div>
          <div class="row" style="margin-top:8px;">
            <div class="col-sm-3"><label>Status</label>
              <select class="form-control input-sm" name="status">
                <option value="active">Active</option>
                <option value="on_leave">On Leave</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>
            <div class="col-sm-9"><label>Notes</label><input type="text" class="form-control input-sm" name="notes" placeholder="Any additional notes..."></div>
          </div>
          <div class="row" style="margin-top:12px;">
            <div class="col-sm-12">
              <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-save"></i> <span id="driver_submit_text">Save Driver</span></button>
              <button type="button" class="btn btn-default btn-sm" onclick="resetDriverForm()">Cancel</button>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php $this->load->view('comman/code_js.php'); ?>
<script src="<?= base_url(); ?>theme/plugins/DataTables-1.10.18/js/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>theme/plugins/DataTables-1.10.18/js/dataTables.bootstrap.min.js"></script>
<script>
$(function() {
  var table = $('#delivery_table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: "<?= base_url('operations/delivery_schedules_ajax'); ?>", type: "POST",
      data: { "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>" }
    },
    columnDefs: [{ orderable: false, targets: [0, 7, 8] }],
    autoWidth: false
  });

  function statusBadge(st) {
    var map = {active:'success', on_leave:'warning', inactive:'default', suspended:'danger'};
    return '<span class="label label-' + (map[st]||'default') + '">' + (st||'active').replace('_',' ') + '</span>';
  }
  function loadDrivers() {
    $.getJSON("<?= base_url('operations/ajax_drivers'); ?>", function(drivers) {
      var html = '';
      $.each(drivers, function(i, d) {
        html += '<tr>';
        html += '<td><strong>' + d.name + '</strong></td>';
        html += '<td>' + (d.phone || '-') + '</td>';
        html += '<td>' + (d.vehicle || '-') + ' <small class="text-muted">(' + (d.vehicle_type || '-') + ')</small></td>';
        html += '<td>' + (d.license_plate || '-') + '</td>';
        html += '<td>' + (d.driver_license || '-') + '</td>';
        html += '<td>' + statusBadge(d.status) + '</td>';
        html += '<td>';
        html += '<a href="<?= base_url('operations/driver_profile/'); ?>' + d.id + '" class="btn btn-xs btn-info" title="Profile" target="_blank"><i class="fa fa-eye"></i></a> ';
        html += '<button class="btn btn-xs btn-primary" onclick="editDriver(' + d.id + ')" title="Edit"><i class="fa fa-pencil"></i></button> ';
        html += '<button class="btn btn-xs btn-danger" onclick="deleteDriver(' + d.id + ')" title="Remove"><i class="fa fa-trash"></i></button>';
        html += '</td></tr>';
      });
      $('#driver_table tbody').html(html || '<tr><td colspan="7" class="text-center text-muted">No drivers yet</td></tr>');
    });
  }
  $('#driverModal').on('show.bs.modal', loadDrivers);

  window.editDriver = function(id) {
    $.post("<?= base_url('operations/ajax_driver_detail'); ?>", { id: id, '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>' }, function(res) {
      if(res.status && res.driver) {
        var d = res.driver;
        $('#driver_id_field').val(d.id);
        $('#driver_form_title').html('<i class="fa fa-pencil"></i> Edit Driver');
        $('#driver_submit_text').text('Update Driver');
        $.each(d, function(k,v) {
          if(v !== null) $('#driver_form [name="'+k+'"]').val(v);
        });
      }
    }, 'json');
  };

  window.resetDriverForm = function() {
    $('#driver_form')[0].reset();
    $('#driver_id_field').val('');
    $('#driver_form_title').html('<i class="fa fa-plus"></i> Add New Driver');
    $('#driver_submit_text').text('Save Driver');
  };

  $('#driver_form').submit(function(e) {
    e.preventDefault();
    $.post("<?= base_url('operations/ajax_save_driver'); ?>", $(this).serialize(), function(res) {
      if (res.status) {
        toastr['success'](res.message);
        resetDriverForm();
        loadDrivers();
      } else { toastr['error'](res.message || 'Failed.'); }
    }, 'json');
  });

  window.deleteDriver = function(id) {
    if(!confirm('Remove this driver?')) return;
    $.post("<?= base_url('operations/ajax_delete_driver'); ?>", { id: id, '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>' }, function(res) {
      if (res.status) { toastr['success'](res.message); loadDrivers(); }
      else { toastr['error'](res.message || 'Failed.'); }
    }, 'json');
  };

  window.delete_schedule = function(id) {
    if(!confirm('Are you sure?')) return;
    $.post("<?= base_url('operations/delivery_schedule_delete'); ?>", { q_id: id, '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>' }, function(res) {
      if (res == 'success') { toastr['success']('Deleted.'); table.ajax.reload(); }
      else { toastr['error']('Failed.'); }
    });
  };
});
</script>
</body>
</html>
