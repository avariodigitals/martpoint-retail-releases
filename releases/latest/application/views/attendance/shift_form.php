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
    <h1><?= $shift ? 'Edit Shift' : 'New Shift' ?></h1>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        <div class="box box-info">
          <div class="box-body">
            <form id="shiftForm">
              <input type="hidden" name="id" value="<?= $shift ? $shift->id : 0 ?>">
              <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
              <div class="form-group">
                <label>Shift Name</label>
                <input type="text" name="shift_name" class="form-control" value="<?= $shift ? htmlspecialchars($shift->shift_name) : '' ?>" required>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Start Time</label>
                    <input type="time" name="start_time" class="form-control" value="<?= $shift ? $shift->start_time : '' ?>" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>End Time</label>
                    <input type="time" name="end_time" class="form-control" value="<?= $shift ? $shift->end_time : '' ?>" required>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label>Grace Period (minutes)</label>
                <input type="number" name="grace_minutes" class="form-control" value="<?= $shift ? $shift->grace_minutes : 0 ?>">
              </div>
              <hr>
              <h5><i class="fa fa-map-marker"></i> Location (for QR / geofencing)</h5>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Latitude</label>
                    <input type="text" name="location_lat" class="form-control" value="<?= $shift ? $shift->location_lat : '' ?>" placeholder="e.g. 6.5244">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Longitude</label>
                    <input type="text" name="location_lng" class="form-control" value="<?= $shift ? $shift->location_lng : '' ?>" placeholder="e.g. 3.3792">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Radius (meters)</label>
                    <input type="number" name="location_radius_meters" class="form-control" value="<?= $shift ? $shift->location_radius_meters : 100 ?>">
                  </div>
                </div>
              </div>
              <div class="form-group">
                <button type="button" id="getCurrentLocation" class="btn btn-default btn-sm"><i class="fa fa-crosshairs"></i> Use My Current Location</button>
              </div>
              <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Shift</button>
              <a href="<?= base_url('attendance/shifts'); ?>" class="btn btn-default">Cancel</a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
<?php $this->load->view('footer'); ?>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>
$('#getCurrentLocation').click(function(){
  if(!navigator.geolocation){ toastr['warning']('Geolocation not supported'); return; }
  navigator.geolocation.getCurrentPosition(function(pos){
    $('input[name="location_lat"]').val(pos.coords.latitude.toFixed(6));
    $('input[name="location_lng"]').val(pos.coords.longitude.toFixed(6));
  }, function(){ toastr['warning']('Could not get location'); });
});
$('#shiftForm').submit(function(e){
  e.preventDefault();
  var btn = $(this).find('button[type="submit"]');
  btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
  $.post('<?= base_url('attendance/save_shift'); ?>', $(this).serialize(), function(res){
    btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Shift');
    if(res.status === 'success'){
      window.location = '<?= base_url('attendance/shifts'); ?>';
    } else {
      toastr['error'](res.message);
    }
  }, 'json').fail(function(xhr){
    btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Shift');
    toastr['error']('Save failed: ' + (xhr.responseText || 'Unknown error'));
  });
});
</script>
<script>$('.attendance-shifts-active-li').addClass('active');</script>
</body>
</html>
