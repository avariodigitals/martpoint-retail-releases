<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small>Per-Customer Service History</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
          <div class="inner"><h3><?= $this_month_count; ?></h3><p>This Month</p></div>
          <div class="icon"><i class="fa fa-calendar"></i></div>
          <span class="small-box-footer">Treatments recorded</span>
        </div>
      </div>
      <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-blue">
          <div class="inner"><h3><?= count($latest_notes); ?></h3><p>Recent Entries</p></div>
          <div class="icon"><i class="fa fa-file-text-o"></i></div>
          <span class="small-box-footer">Last 5 notes</span>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">Treatment Notes</h3>
            <div class="box-tools pull-right">
              <a href="<?= base_url('operations/treatment_note'); ?>" class="btn btn-sm btn-success"><i class="fa fa-plus"></i> Add Note</a>
            </div>
          </div>
          <div class="box-body">
            <div class="table-responsive">
              <table id="notes-table" class="table table-bordered table-striped">
                <thead><tr><th>#</th><th>Customer</th><th>Service</th><th>Notes</th><th>Staff</th><th>Date</th><th>Action</th></tr></thead>
              </table>
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
<script src="<?= base_url(); ?>theme/plugins/DataTables-1.10.18/js/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>theme/plugins/DataTables-1.10.18/js/dataTables.bootstrap.min.js"></script>
<script>
$(function(){
  var table = $('#notes-table').DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": {
      "url": "<?= base_url('operations/treatment_notes_ajax'); ?>",
      "type": "POST",
      "data": { "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>" }
    },
    "columnDefs": [{ "orderable": false, "targets": [3,6] }],
    "autoWidth": false
  });
});
function delete_note(id) {
  if(!confirm('Delete this treatment note?')) return;
  $.post('<?= base_url('operations/treatment_note_delete'); ?>', {
    id: id,
    "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>"
  }, function(res){
    if(res.success) { toastr.success(res.message); $('#notes-table').DataTable().ajax.reload(); }
    else { toastr.error(res.message || 'Failed'); }
  }, 'json');
}
</script>
</body>
</html>
