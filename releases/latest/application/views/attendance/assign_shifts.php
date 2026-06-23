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
    <h1>Assign Shifts</h1>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="box box-info">
          <div class="box-body table-responsive">
            <table class="table table-bordered table-striped datatable">
              <thead>
                <tr>
                  <th style="width:40px">#</th>
                  <th>Staff</th>
                  <th>Assigned Shifts</th>
                  <th style="width:180px">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($users as $idx => $u): ?>
                <tr data-user="<?= $u->id ?>">
                  <td><?= $idx + 1 ?></td>
                  <td>
                    <img src="<?= base_url($u->profile_picture ?: 'theme/images/avatar.png') ?>" style="width:32px;height:32px;border-radius:50%;margin-right:8px;">
                    <?= htmlspecialchars(($u->first_name ?: '') . ' ' . ($u->last_name ?: '') . ' (' . $u->username . ')') ?>
                  </td>
                  <td class="shift-tags-<?= $u->id ?>">
                    <?php foreach($u->assigned_shifts as $s): ?>
                      <span class="label label-info" style="margin-right:4px;display:inline-block;margin-bottom:4px;">
                        <?= htmlspecialchars($s->shift_name) ?> (<?= $s->start_time ?> - <?= $s->end_time ?>)
                        <a href="#" class="remove-shift-inline" data-user="<?= $u->id ?>" data-shift="<?= $s->id ?>" data-shift-name="<?= htmlspecialchars($s->shift_name) ?>" style="margin-left:4px;color:#fff;background:#dd4b39;padding:0 4px;border-radius:3px;font-size:10px;" title="Unassign <?= htmlspecialchars($s->shift_name) ?>"><i class="fa fa-times"></i></a>
                      </span>
                    <?php endforeach; ?>
                    <?php if(empty($u->assigned_shifts)): ?>
                      <span class="text-muted">No shifts assigned</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="input-group input-group-sm">
                      <select class="form-control add-shift-select" data-user="<?= $u->id ?>">
                        <option value="">Add shift...</option>
                        <?php foreach($shifts as $s):
                          $already = false;
                          foreach($u->assigned_shifts as $us){ if($us->id == $s->id){ $already = true; break; } }
                          if(!$already):
                        ?>
                        <option value="<?= $s->id ?>"><?= htmlspecialchars($s->shift_name) ?> (<?= $s->start_time ?> - <?= $s->end_time ?>)</option>
                        <?php endif; endforeach; ?>
                      </select>
                      <span class="input-group-btn">
                        <button class="btn btn-success btn-add-shift" data-user="<?= $u->id ?>" type="button"><i class="fa fa-plus"></i></button>
                      </span>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($users)): ?>
                <tr><td colspan="4" class="text-center text-muted">No staff found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Shift Assignments Summary -->
    <div class="row" style="margin-top:20px;">
      <div class="col-md-12">
        <div class="box box-success">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list text-green"></i> Shift Assignments Summary</h3>
            <div class="box-tools pull-right">
              <span class="badge bg-green"><?= count($shifts) ?> Shifts</span>
            </div>
          </div>
          <div class="box-body">
            <?php
              // Build shift-centric view
              $shiftStaff = [];
              foreach($shifts as $s){
                $shiftStaff[$s->id] = ['shift' => $s, 'users' => []];
              }
              foreach($users as $u){
                foreach($u->assigned_shifts as $s){
                  if(isset($shiftStaff[$s->id])){
                    $shiftStaff[$s->id]['users'][] = $u;
                  }
                }
              }
            ?>
            <div class="row">
              <?php foreach($shiftStaff as $ss): ?>
              <div class="col-md-4 col-sm-6">
                <div class="info-box bg-aqua">
                  <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text"><?= htmlspecialchars($ss['shift']->shift_name) ?></span>
                    <span class="info-box-number"><?= count($ss['users']) ?> Staff</span>
                    <span class="progress-description"><?= $ss['shift']->start_time ?> - <?= $ss['shift']->end_time ?></span>
                    <div style="margin-top:6px;">
                      <?php if(empty($ss['users'])): ?>
                        <small class="text-muted">No staff assigned</small>
                      <?php else: ?>
                        <?php foreach($ss['users'] as $u): ?>
                          <span class="badge bg-green" style="margin-right:2px;margin-bottom:2px;">
                            <?= htmlspecialchars(($u->first_name ?: '') . ' ' . ($u->last_name ?: '')) ?: $u->username ?>
                          </span>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
              <?php if(empty($shifts)): ?>
              <div class="col-md-12 text-center text-muted">No shifts configured.</div>
              <?php endif; ?>
            </div>
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
function reloadPage(){ location.reload(); }

$('.btn-add-shift').click(function(){
  var userId = $(this).data('user');
  var select = $('select.add-shift-select[data-user="'+userId+'"]');
  var shiftId = select.val();
  if(!shiftId){ toastr['warning']('Select a shift first'); return; }
  $.post('<?= base_url('attendance/save_user_shift'); ?>', {
    user_id: userId,
    shift_id: shiftId,
    action: 'add',
    '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>'
  }, function(res){
    if(res.status === 'success') reloadPage();
    else toastr['error'](res.message || 'Failed to assign shift');
  }, 'json');
});

$(document).on('click', '.remove-shift-inline', function(e){
  e.preventDefault();
  var userId = $(this).data('user');
  var shiftId = $(this).data('shift');
  var shiftName = $(this).data('shift-name') || 'this shift';
  if(typeof swal === 'undefined'){
    if(!confirm('Unassign '+shiftName+' from this staff member?')) return;
    doRemoveShift(userId, shiftId);
    return;
  }
  swal({
    title: "Unassign Shift?",
    text: "This will remove "+shiftName+" from this staff member.",
    icon: "warning",
    buttons: true,
    dangerMode: true
  }).then(function(willDelete){
    if(willDelete){
      doRemoveShift(userId, shiftId);
    }
  });
});
function doRemoveShift(userId, shiftId){
  $.post('<?= base_url('attendance/save_user_shift'); ?>', {
    user_id: userId,
    shift_id: shiftId,
    action: 'remove',
    '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>'
  }, function(res){
    if(res.status === 'success') reloadPage();
    else toastr['error'](res.message || 'Failed to remove shift');
  }, 'json');
}
</script>
<script>$('.attendance-assign-active-li').addClass('active');</script>
</body>
</html>
