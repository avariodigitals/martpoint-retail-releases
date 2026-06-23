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
    <h1>Shifts</h1>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">Shift List</h3>
            <div class="box-tools pull-right">
              <a href="<?= base_url('attendance/shift_form'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> New Shift</a>
            </div>
          </div>
          <div class="box-body">
            <table class="table table-bordered table-striped datatable">
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
                  <td><?= htmlspecialchars($s->shift_name) ?></td>
                  <td><?= $s->start_time ?></td>
                  <td><?= $s->end_time ?></td>
                  <td><?= $s->grace_minutes ?></td>
                  <td><?= $s->location_lat && $s->location_lng ? round($s->location_lat,4).', '.round($s->location_lng,4) : '-' ?></td>
                  <td><?= $s->location_radius_meters ?></td>
                  <td>
                    <a href="<?= base_url('attendance/shift_form/'.$s->id); ?>" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
                    <button class="btn btn-xs btn-danger delete-shift"><i class="fa fa-trash"></i></button>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($shifts)): ?>
                <tr><td colspan="7" class="text-center text-muted">No shifts configured yet.</td></tr>
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
<script>$('.attendance-shifts-active-li').addClass('active');</script>
</body>
</html>
