<?php
/* Assign shifts to users — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<div class="mp-page-head">
  <h1 class="mp-page-title">Assign Shifts</h1>
</div>
<div class="mp-card">
  <div class="mp-card-head">
    <h3 class="mp-card-title">Staff Shift Assignments</h3>
  </div>
  <div class="mp-card-body">
    <div class="mp-dt-scroll">
    <table class="mp-dt-table" id="assignTable" width="100%">
      <thead>
        <tr>
          <th style="width:40px">#</th>
          <th>Staff</th>
          <th>Assigned Shifts</th>
          <th style="width:200px">Add Shift</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($users as $idx => $u): ?>
        <tr data-user="<?= $u->id ?>">
          <td><?= $idx + 1 ?></td>
          <td class="row-name">
            <img src="<?= base_url($u->profile_picture ?: 'theme/images/avatar.png') ?>" style="width:32px;height:32px;border-radius:50%;margin-right:8px;vertical-align:middle;">
            <?= htmlspecialchars(($u->first_name ?: '') . ' ' . ($u->last_name ?: '') . ' (' . $u->username . ')') ?>
          </td>
          <td class="shift-tags-<?= $u->id ?>">
            <?php foreach($u->assigned_shifts as $s): ?>
              <span class="label label-info" style="margin-right:4px;display:inline-flex;align-items:center;gap:4px;margin-bottom:4px;">
                <?= htmlspecialchars($s->shift_name) ?> (<?= $s->start_time ?> - <?= $s->end_time ?>)
                <a href="#" class="remove-shift-inline" data-user="<?= $u->id ?>" data-shift="<?= $s->id ?>" data-shift-name="<?= htmlspecialchars($s->shift_name) ?>" style="color:#fff;background:rgba(220,38,38,.3);padding:0 4px;border-radius:3px;font-size:10px;" title="Unassign <?= htmlspecialchars($s->shift_name) ?>"><i class="fa fa-times"></i></a>
              </span>
            <?php endforeach; ?>
            <?php if(empty($u->assigned_shifts)): ?>
              <span class="text-muted">No shifts assigned</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:8px;align-items:center;">
              <select class="form-control add-shift-select" data-user="<?= $u->id ?>" style="flex:1;">
                <option value="">Add shift...</option>
                <?php foreach($shifts as $s):
                  $already = false;
                  foreach($u->assigned_shifts as $us){ if($us->id == $s->id){ $already = true; break; } }
                  if(!$already):
                ?>
                <option value="<?= $s->id ?>"><?= htmlspecialchars($s->shift_name) ?> (<?= $s->start_time ?> - <?= $s->end_time ?>)</option>
                <?php endif; endforeach; ?>
              </select>
              <button class="mp-qa-btn green btn-add-shift" data-user="<?= $u->id ?>" type="button" style="flex-shrink:0;"><i class="fa fa-plus"></i></button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($users)): ?>
        <tr><td colspan="4" class="text-center text-muted" style="padding:40px">No staff found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<!-- Shift Assignments Summary -->
<div class="mp-card" style="margin-top:20px;">
  <div class="mp-card-header">
    <h3 class="mp-card-title"><i class="fa fa-list text-green"></i> Shift Assignments Summary</h3>
    <span class="badge bg-green"><?= count($shifts) ?> Shifts</span>
  </div>
  <div class="mp-card-body">
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
<script>$('.attendance-assign-active-li').addClass('active').closest(".mp-nav-group").addClass("open");</script>
