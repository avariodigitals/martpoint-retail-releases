<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.mp-filter-bar{display:grid!important;grid-template-columns:repeat(auto-fit,minmax(180px,1fr))!important;gap:16px!important;align-items:end!important}
.mp-driver-row{display:grid!important;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))!important;gap:14px!important;margin-bottom:12px!important}
.mp-driver-row label{font-size:12px!important;font-weight:600!important;color:var(--mp-muted)!important;display:block!important;margin-bottom:4px!important}
.mp-order-row{display:grid!important;grid-template-columns:40px 1fr 1fr 80px 110px 44px!important;gap:10px!important;align-items:center!important;padding:10px 12px!important;background:var(--mp-bg)!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;margin-bottom:8px!important}
.mp-order-row .seq{width:28px;height:28px;border-radius:50%;background:var(--mp-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700}
.mp-order-row .seq-input{width:60px}
.mp-timeline{position:relative!important;padding-left:24px!important;margin:8px 0!important}
.mp-timeline:before{content:'';position:absolute;left:8px;top:4px;bottom:4px;width:2px;background:var(--mp-border)}
.mp-timeline .tl-item{position:relative!important;padding:0 0 16px 8px!important}
.mp-timeline .tl-item:before{content:'';position:absolute;left:-16px;top:4px;width:10px;height:10px;border-radius:50%;background:var(--mp-primary);border:2px solid var(--mp-surface)}
.mp-timeline .tl-item .tl-time{font-size:11px;color:var(--mp-muted)}
.mp-timeline .tl-item .tl-label{font-size:13px;font-weight:600;color:var(--mp-text)}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Plan delivery routes, assign drivers and track stops</div>
  </div>
  <div style="display:flex;gap:10px;">
    <a href="<?= base_url('operations/delivery_schedule_form'); ?>" class="mp-qa-btn green"><i class="fa fa-plus"></i> New Delivery Route</a>
    <button type="button" class="mp-qa-btn blue" data-toggle="modal" data-target="#driverModal"><i class="fa fa-users"></i> Drivers</button>
  </div>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(0,87,255,.1);color:var(--mp-primary)"><i class="fa fa-calendar-check-o"></i></div><div class="mp-kpi-label">Scheduled</div><div class="mp-kpi-value"><?= $kpi['scheduled'] ?? 0; ?></div></div>
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(245,158,11,.1);color:var(--mp-warning)"><i class="fa fa-truck"></i></div><div class="mp-kpi-label">In Transit</div><div class="mp-kpi-value"><?= $kpi['in_transit'] ?? 0; ?></div></div>
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(5,150,105,.1);color:var(--mp-success)"><i class="fa fa-check-circle"></i></div><div class="mp-kpi-label">Delivered</div><div class="mp-kpi-value"><?= $kpi['delivered'] ?? 0; ?></div></div>
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(220,38,38,.1);color:var(--mp-danger)"><i class="fa fa-exclamation-circle"></i></div><div class="mp-kpi-label">Failed</div><div class="mp-kpi-value"><?= $kpi['failed'] ?? 0; ?></div></div>
</div>

<div class="mp-card-form" style="margin-bottom:20px">
  <div class="mp-card-head"><h3><i class="fa fa-filter"></i> Filters</h3><button type="button" class="mp-qa-btn blue" onclick="clearFilters()"><i class="fa fa-eraser"></i> Clear</button></div>
  <div class="mp-card-body">
    <div class="mp-filter-bar">
      <div class="mp-form-group" style="margin:0">
        <label>From Date</label>
        <input type="text" class="mp-form-control datepicker" id="filter_from" placeholder="yyyy-mm-dd">
      </div>
      <div class="mp-form-group" style="margin:0">
        <label>To Date</label>
        <input type="text" class="mp-form-control datepicker" id="filter_to" placeholder="yyyy-mm-dd">
      </div>
      <div class="mp-form-group" style="margin:0">
        <label>Driver</label>
        <select class="mp-form-control" id="filter_driver">
          <option value="">-- All Drivers --</option>
        </select>
      </div>
      <div class="mp-form-group" style="margin:0">
        <label>Status</label>
        <select class="mp-form-control" id="filter_status">
          <option value="">-- All Status --</option>
          <option value="planned">Planned</option>
          <option value="ready">Ready</option>
          <option value="out_for_delivery">Out for Delivery</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="mp-form-group" style="margin:0">
        <button type="button" class="mp-btn-primary" onclick="applyFilters()" style="width:100%"><i class="fa fa-search"></i> Apply</button>
      </div>
    </div>
  </div>
</div>

<div class="mp-table-wrap">
  <div class="mp-card-head"><h3><i class="fa fa-truck"></i> Delivery Schedules</h3></div>
  <div class="mp-dt-scroll">
    <table id="delivery_table" class="table mp-dt-table" width="100%">
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

<!-- Driver Modal -->
<div class="modal fade" id="driverModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-navy">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title" style="color:#fff;"><i class="fa fa-users"></i> Delivery Drivers</h4>
      </div>
      <div class="modal-body">
        <div class="mp-dt-scroll" style="margin-bottom:16px">
          <table class="table mp-dt-table" id="driver_table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>Vehicle</th>
                <th>Plate</th>
                <th>License</th>
                <th>Status</th>
                <th style="width:120px;"></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
        <h5 id="driver_form_title"><i class="fa fa-plus"></i> Add New Driver</h5>
        <form id="driver_form">
          <input type="hidden" name="id" id="driver_id_field" value="">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
          <div class="mp-driver-row">
            <div><label>Full Name *</label><input type="text" class="mp-form-control" name="name" required></div>
            <div><label>Phone *</label><input type="text" class="mp-form-control" name="phone" required></div>
            <div><label>Email</label><input type="email" class="mp-form-control" name="email"></div>
          </div>
          <div class="mp-driver-row">
            <div><label>Address</label><input type="text" class="mp-form-control" name="address" placeholder="Residential address"></div>
            <div><label>Emergency Contact Name</label><input type="text" class="mp-form-control" name="emergency_contact_name"></div>
            <div><label>Emergency Contact Phone</label><input type="text" class="mp-form-control" name="emergency_contact_phone"></div>
          </div>
          <div class="mp-driver-row">
            <div><label>NIN</label><input type="text" class="mp-form-control" name="nin" placeholder="National ID Number"></div>
            <div><label>Driver's License</label><input type="text" class="mp-form-control" name="driver_license" placeholder="FRSC License No"></div>
            <div><label>License Expiry</label><input type="date" class="mp-form-control" name="license_expiry"></div>
            <div><label>Hire Date</label><input type="date" class="mp-form-control" name="hire_date"></div>
          </div>
          <div class="mp-driver-row">
            <div><label>Vehicle Make/Model</label><input type="text" class="mp-form-control" name="vehicle" placeholder="e.g. Toyota Hilux"></div>
            <div><label>Type</label>
              <select class="mp-form-control" name="vehicle_type">
                <option value="motorcycle">Motorcycle</option>
                <option value="car">Car</option>
                <option value="van">Van</option>
                <option value="truck">Truck</option>
                <option value="keke">Keke</option>
                <option value="bicycle">Bicycle</option>
              </select>
            </div>
            <div><label>Color</label><input type="text" class="mp-form-control" name="vehicle_color" placeholder="e.g. White"></div>
            <div><label>Plate No</label><input type="text" class="mp-form-control" name="license_plate"></div>
            <div><label>Employment</label>
              <select class="mp-form-control" name="employment_type">
                <option value="full_time">Full Time</option>
                <option value="contract">Contract</option>
                <option value="part_time">Part Time</option>
                <option value="intern">Intern</option>
              </select>
            </div>
          </div>
          <div class="mp-driver-row">
            <div><label>Status</label>
              <select class="mp-form-control" name="status">
                <option value="active">Active</option>
                <option value="on_leave">On Leave</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>
            <div style="grid-column:span 3"><label>Notes</label><input type="text" class="mp-form-control" name="notes" placeholder="Any additional notes..."></div>
          </div>
          <div class="mp-form-actions" style="margin-top:16px">
            <button type="submit" class="mp-btn-primary"><i class="fa fa-save"></i> <span id="driver_submit_text">Save Driver</span></button>
            <button type="button" class="mp-btn-secondary" onclick="resetDriverForm()">Cancel</button>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="mp-btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
  $('.datepicker').datepicker({ format: 'yyyy-mm-dd', autoclose: true });

  var table = $('#delivery_table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: "<?= base_url('operations/delivery_schedules_ajax'); ?>", type: "POST",
      data: function(d) {
        d.<?= $this->security->get_csrf_token_name(); ?> = "<?= $this->security->get_csrf_hash(); ?>";
        d.filter_from = $('#filter_from').val();
        d.filter_to = $('#filter_to').val();
        d.filter_driver = $('#filter_driver').val();
        d.filter_status = $('#filter_status').val();
      }
    },
    columnDefs: [{ orderable: false, targets: [0, 7, 8] }],
    autoWidth: false
  });

  window.applyFilters = function() { table.ajax.reload(); };
  window.clearFilters = function() {
    $('#filter_from,#filter_to,#filter_driver').val('');
    $('#filter_status').val('');
    table.ajax.reload();
  };

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
        html += '<td><div class="mp-actions">';
        html += '<a href="<?= base_url('operations/driver_profile/'); ?>' + d.id + '" class="mp-edit" title="Profile" target="_blank"><i class="fa fa-eye"></i></a>';
        html += '<button class="mp-edit" onclick="editDriver(' + d.id + ')" title="Edit"><i class="fa fa-pencil"></i></button>';
        html += '<button class="mp-delete" onclick="deleteDriver(' + d.id + ')" title="Remove"><i class="fa fa-trash"></i></button>';
        html += '</div></td></tr>';
      });
      $('#driver_table tbody').html(html || '<tr><td colspan="7" class="text-center text-muted">No drivers yet</td></tr>');
      var drvHtml = '<option value="">-- All Drivers --</option>';
      $.each(drivers, function(i, d) { drvHtml += '<option value="'+d.id+'">'+d.name+'</option>'; });
      $('#filter_driver').html(drvHtml);
    });
  }
  $('#driverModal').on('show.bs.modal', loadDrivers);
  loadDrivers();

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
