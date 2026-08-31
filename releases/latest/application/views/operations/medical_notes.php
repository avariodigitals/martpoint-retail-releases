<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?>
<style>
.mn-stat-card { background:#fff; border-radius:14px; padding:20px; border:1px solid #E2E8F0; display:flex; align-items:center; gap:14px; box-shadow:0 1px 3px rgba(0,0,0,0.06); }
.mn-stat-icon { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px; }
.mn-stat-icon.blue { background:#DBEAFE; color:#0057FF; }
.mn-stat-icon.green { background:#D1FAE5; color:#059669; }
.mn-stat-icon.orange { background:#FEF3C7; color:#D97706; }
.mn-stat-icon.red { background:#FEE2E2; color:#DC2626; }
.mn-stat-value { font-size:22px; font-weight:800; color:#1E293B; }
.mn-stat-label { font-size:12px; color:#64748B; font-weight:600; }
.mn-refill-card { background:#fff; border-radius:12px; padding:16px; border:1px solid #E2E8F0; border-left:4px solid #F59E0B; margin-bottom:10px; display:flex; align-items:center; justify-content:space-between; }
.mn-refill-info { flex:1; }
.mn-refill-name { font-size:14px; font-weight:700; color:#1E293B; }
.mn-refill-date { font-size:13px; color:#64748B; }
.mn-refill-action { background:#F59E0B; color:#fff; padding:7px 14px; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; white-space:nowrap; }
.mn-refill-action:hover { opacity:0.88; color:#fff; }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small><?= mp_label('customer'); ?> Prescription &amp; Dispensing Records</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-lg-3 col-xs-6">
        <div class="mn-stat-card">
          <div class="mn-stat-icon blue"><i class="fa fa-file-medical-o"></i></div>
          <div><div class="mn-stat-value"><?= $this_month_count; ?></div><div class="mn-stat-label">This Month</div></div>
        </div>
      </div>
      <div class="col-lg-3 col-xs-6">
        <div class="mn-stat-card">
          <div class="mn-stat-icon green"><i class="fa fa-list"></i></div>
          <div><div class="mn-stat-value"><?= count($latest_notes); ?></div><div class="mn-stat-label">Recent Entries</div></div>
        </div>
      </div>
      <div class="col-lg-3 col-xs-6">
        <div class="mn-stat-card">
          <div class="mn-stat-icon orange"><i class="fa fa-clock-o"></i></div>
          <div><div class="mn-stat-value"><?= count($refill_reminders); ?></div><div class="mn-stat-label">Refill Due (7 days)</div></div>
        </div>
      </div>
      <div class="col-lg-3 col-xs-6">
        <div class="mn-stat-card">
          <div class="mn-stat-icon red"><i class="fa fa-exclamation-triangle"></i></div>
          <div><div class="mn-stat-value" id="allergy-count">-</div><div class="mn-stat-label">Allergy Alerts</div></div>
        </div>
      </div>
    </div>

    <?php if(!empty($refill_reminders)): ?>
    <div class="box box-warning" style="margin-top:16px;border-radius:12px;">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-bell-o text-orange"></i> Refill Reminders</h3>
        <small class="text-muted" style="margin-left:8px;"><?= mp_label('customer'); ?>s with refills due in the next 7 days</small>
      </div>
      <div class="box-body" style="padding:12px;">
        <?php foreach($refill_reminders as $rm): ?>
        <div class="mn-refill-card">
          <div class="mn-refill-info">
            <div class="mn-refill-name"><?= htmlspecialchars($rm->customer_name); ?> <small class="text-muted">(<?= htmlspecialchars($rm->mobile ?? ''); ?>)</small></div>
            <div class="mn-refill-date">Refill by: <strong><?= show_date($rm->next_refill_date); ?></strong> &middot; <?= $rm->refills_remaining; ?> refills remaining</div>
          </div>
          <a href="<?= base_url('operations/medical_note/' . $rm->id); ?>" class="mn-refill-action"><i class="fa fa-pencil"></i> Process Refill</a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="box box-info" style="margin-top:16px;border-radius:12px;">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-file-medical-o"></i> Medical Notes</h3>
        <div class="box-tools pull-right">
          <a href="<?= base_url('operations/medical_note'); ?>" class="btn btn-sm btn-success"><i class="fa fa-plus"></i> New Medical Note</a>
        </div>
      </div>
      <div class="box-body">
        <div class="table-responsive">
          <table id="med-notes-table" class="table table-bordered table-striped" style="font-size:14px;">
            <thead><tr><th>#</th><th><?= mp_label('customer'); ?></th><th>Doctor</th><th>Diagnosis</th><th>Rx</th><th>Refills</th><th>Next Refill</th><th>Date</th><th>Allergy</th><th>Action</th></tr></thead>
          </table>
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
  var table = $('#med-notes-table').DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": {
      "url": "<?= base_url('operations/medical_notes_ajax'); ?>",
      "type": "POST",
      "data": { "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>" }
    },
    "columnDefs": [{ "orderable": false, "targets": [3,4,8,9] }],
    "autoWidth": false,
    "drawCallback": function(settings) {
      var allergyCount = 0;
      table.data().each(function(row){
        if(row[8] && row[8].indexOf('label-danger') !== -1) allergyCount++;
      });
      $('#allergy-count').text(allergyCount);
    }
  });
});
function delete_medical_note(id) {
  if(!confirm('Delete this medical note? This cannot be undone.')) return;
  $.post('<?= base_url('operations/medical_note_delete'); ?>', {
    id: id,
    "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>"
  }, function(res){
    if(res.success) { toastr.success(res.message); $('#med-notes-table').DataTable().ajax.reload(); }
    else { toastr.error(res.message || 'Failed'); }
  }, 'json');
}
</script>
</body>
</html>
