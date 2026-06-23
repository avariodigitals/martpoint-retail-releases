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
    <h1>Daily Attendance</h1>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="box box-info">
          <div class="box-header with-border">
            <form method="get" class="form-inline">
              <div class="form-group">
                <input type="date" name="date" class="form-control" value="<?= $date ?>">
              </div>
              <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> View</button>
            </form>
          </div>
          <div class="box-body">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Staff</th>
                  <th>Status</th>
                  <th>Shift</th>
                  <th>Clock In</th>
                  <th>Clock Out</th>
                  <th>Location OK</th>
                  <th>Face</th>
                  <?php $CI =& get_instance(); if($CI->permissions('attendance_edit') || is_admin() || is_store_admin()): ?>
                  <th style="width:100px">Actions</th>
                  <?php endif; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach($report as $r): ?>
                <tr>
                  <td>
                    <img src="<?= base_url($r['profile_picture'] ?: 'theme/images/avatar.png') ?>" style="width:32px;height:32px;border-radius:50%;margin-right:8px;">
                    <?= htmlspecialchars($r['name'] ?: $r['username']) ?>
                  </td>
                  <td>
                    <?php if($r['present']): ?>
                      <span class="label label-success">Present</span>
                    <?php else:
                      $shift = $this->attendance_model->isOnDuty($r['user_id'], get_current_store_id());
                      $reason = $shift ? 'unknown' : 'not on duty';
                    ?>
                      <span class="label label-danger">Absent</span>
                      <small class="text-muted">(reason - <?= $reason ?>)</small>
                    <?php endif; ?>
                  </td>
                  <td><?= $r['shift_name'] ?></td>
                  <td><?= $r['clock_in'] ?: '-' ?></td>
                  <td><?= $r['clock_out'] ?: '-' ?></td>
                  <td>
                    <?php if($r['present']): ?>
                      <?= $r['location_ok'] ? '<span class="label label-success"><i class="fa fa-check"></i> Yes</span>' : '<span class="label label-warning"><i class="fa fa-times"></i> No</span>' ?>
                    <?php else: ?>-<?php endif; ?>
                  </td>
                  <td>
                    <?php if($r['face_image']): ?>
                      <a href="<?= base_url($r['face_image']) ?>" target="_blank"><img src="<?= base_url($r['face_image']) ?>" style="width:40px;height:40px;border-radius:4px;object-fit:cover;"></a>
                    <?php else: ?>-<?php endif; ?>
                  </td>
                  <?php $CI =& get_instance(); if($CI->permissions('attendance_edit') || is_admin() || is_store_admin()): ?>
                  <td>
                    <?php if($r['present'] && !$r['clock_out']): ?>
                      <button type="button" class="btn btn-xs btn-danger manager-clock-out" data-user="<?= $r['user_id'] ?>" data-name="<?= htmlspecialchars($r['name'] ?: $r['username']) ?>"><i class="fa fa-sign-out"></i> Clock Out</button>
                    <?php endif; ?>
                  </td>
                  <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($report)): ?>
                <tr><td colspan="8" class="text-center text-muted">No staff assigned to shifts.</td></tr>
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
<script>
// Manager clock-out staff from daily attendance
$(document).on('click', '.manager-clock-out', function(e){
  e.preventDefault();
  var userId = $(this).data('user');
  var name = $(this).data('name');
  var $btn = $(this);

  if(typeof swal === 'undefined'){
    if(!confirm('Clock out '+name+'?')) return;
    doManagerClockOut(userId, $btn);
    return;
  }

  swal({
    title: "Clock Out "+name+"?",
    text: "This will record the clock-out time for this staff member.",
    icon: "warning",
    buttons: ["Cancel", "Clock Out"],
    dangerMode: true
  }).then(function(confirmed){
    if(confirmed) doManagerClockOut(userId, $btn);
  });
});

function doManagerClockOut(userId, $btn){
  $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
  $.post('<?= base_url('attendance/clock_out_ajax'); ?>', {
    user_id: userId,
    '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>'
  }, function(res){
    if(res.status === 'success'){
      toastr['success'](res.message || 'Clocked out');
      $btn.closest('tr').find('td:eq(4)').text(new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}));
      $btn.fadeOut();
    } else {
      toastr['error'](res.message || 'Failed to clock out');
      $btn.prop('disabled', false).html('<i class="fa fa-sign-out"></i> Clock Out');
    }
  }, 'json').fail(function(){
    toastr['error']('Network error. Please try again.');
    $btn.prop('disabled', false).html('<i class="fa fa-sign-out"></i> Clock Out');
  });
}
</script>
<script>$('.attendance-daily-active-li').addClass('active');</script>
</body>
</html>
