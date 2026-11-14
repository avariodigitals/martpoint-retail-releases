<?php
/* Daily attendance report — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<div class="mp-page-head">
  <h1 class="mp-page-title">Daily Attendance</h1>
</div>
<div class="mp-card">
  <div class="mp-card-head">
    <h3 class="mp-card-title">Daily Attendance — <?= show_date($date) ?></h3>
    <form method="get" style="display:flex;gap:8px;align-items:center;">
      <input type="date" name="date" class="form-control" value="<?= $date ?>" style="width:auto;">
      <button type="submit" class="mp-qa-btn green"><i class="fa fa-search"></i> View</button>
    </form>
  </div>
  <div class="mp-card-body">
    <div class="mp-dt-scroll">
    <table class="mp-dt-table" id="dailyTable" width="100%">
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
          <th style="width:120px">Actions</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach($report as $r): ?>
        <tr>
          <td class="row-name">
            <img src="<?= base_url($r['profile_picture'] ?: 'theme/images/avatar.png') ?>" style="width:32px;height:32px;border-radius:50%;margin-right:8px;vertical-align:middle;">
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
          <td><?= $r['shift_name'] ?: '-' ?></td>
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
              <button type="button" class="mp-qa-btn danger manager-clock-out" data-user="<?= $r['user_id'] ?>" data-name="<?= htmlspecialchars($r['name'] ?: $r['username']) ?>"><i class="fa fa-sign-out"></i> Clock Out</button>
            <?php endif; ?>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($report)): ?>
        <tr><td colspan="8" class="text-center text-muted" style="padding:40px">No staff assigned to shifts.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>
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
<script>$('.attendance-daily-active-li').addClass('active').closest(".mp-nav-group").addClass("open");</script>
