<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Per-Customer Service History</div>
  </div>
  <a href="<?= base_url('operations/treatment_note'); ?>" class="mp-qa-btn green"><i class="fa fa-plus"></i> Add Note</a>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card profit">
    <div class="mp-kpi-icon"><i class="fa fa-calendar"></i></div>
    <div class="mp-kpi-label">This Month</div>
    <div class="mp-kpi-value"><?= $this_month_count; ?></div>
    <div class="mp-form-hint" style="margin-top:6px;">Treatments recorded</div>
  </div>
  <div class="mp-kpi-card sales">
    <div class="mp-kpi-icon"><i class="fa fa-file-text-o"></i></div>
    <div class="mp-kpi-label">Recent Entries</div>
    <div class="mp-kpi-value"><?= count($latest_notes); ?></div>
    <div class="mp-form-hint" style="margin-top:6px;">Last 5 notes</div>
  </div>
</div>

<div class="mp-table-wrap">
  <div class="mp-card-head">
    <h3>Treatment Notes</h3>
  </div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="notes-table" class="table mp-dt-table">
        <thead><tr><th>#</th><th>Customer</th><th>Service</th><th>Notes</th><th>Staff</th><th>Date</th><th>Action</th></tr></thead>
      </table>
    </div>
  </div>
</div>

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
