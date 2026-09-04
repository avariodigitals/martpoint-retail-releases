<?php
/* Shifts list — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<div class="mp-page-head">
  <h1 class="mp-page-title">Shifts</h1>
</div>
<div class="mp-card">
  <div class="mp-card-head">
    <h3 class="mp-card-title">Shift List</h3>
    <a href="<?= base_url('attendance/shift_form'); ?>" class="mp-qa-btn green"><i class="fa fa-plus"></i> New Shift</a>
  </div>
  <div class="mp-card-body">
    <div class="mp-dt-scroll">
    <table class="mp-dt-table" id="shiftsTable" width="100%">
      <thead>
        <tr>
          <th>Name</th>
          <th>Start</th>
          <th>End</th>
          <th>Grace (min)</th>
          <th>Location</th>
          <th>Radius (m)</th>
          <th style="width:120px">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($shifts as $s): ?>
        <tr data-id="<?= $s->id ?>">
          <td class="row-name"><?= htmlspecialchars($s->shift_name) ?></td>
          <td><?= $s->start_time ?></td>
          <td><?= $s->end_time ?></td>
          <td><?= $s->grace_minutes ?></td>
          <td><?= $s->location_lat && $s->location_lng ? round($s->location_lat,4).', '.round($s->location_lng,4) : '-' ?></td>
          <td><?= $s->location_radius_meters ?></td>
          <td>
            <div class="mp-actions">
              <a href="<?= base_url('attendance/shift_form/'.$s->id); ?>" class="mp-edit" title="Edit"><i class="fa fa-pencil"></i></a>
              <button class="mp-delete delete-shift" title="Delete"><i class="fa fa-trash"></i></button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($shifts)): ?>
        <tr><td colspan="7" class="text-center text-muted" style="padding:40px">No shifts configured yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>
<script>
$('.delete-shift').click(function(){
  var row = $(this).closest('tr');
  var id = row.data('id');
  if(typeof swal === 'undefined'){
    if(!confirm('Delete this shift?')) return;
    doDeleteShift(row, id);
    return;
  }
  swal({
    title: "Delete Shift?",
    text: "This action cannot be undone.",
    icon: "warning",
    buttons: true,
    dangerMode: true
  }).then(function(willDelete){
    if(willDelete){
      doDeleteShift(row, id);
    }
  });
});
function doDeleteShift(row, id){
  $.post('<?= base_url('attendance/delete_shift/') ?>'+id, {'<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>'}, function(res){
    if(res.status === 'success'){ row.fadeOut(300, function(){ $(this).remove(); }); toastr.success(res.message); }
    else toastr.error(res.message);
  }, 'json');
}
</script>
<script>$('.attendance-shifts-active-li').addClass('active').closest(".mp-nav-group").addClass("open");</script>
