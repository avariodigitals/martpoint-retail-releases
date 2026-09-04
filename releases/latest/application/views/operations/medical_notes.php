<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= mp_label('customer'); ?> Prescription &amp; Dispensing Records</div>
  </div>
  <a href="<?= base_url('operations/medical_note'); ?>" class="mp-qa-btn green"><i class="fa fa-plus"></i> New Medical Note</a>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card primary"><div class="mp-kpi-icon"><i class="fa fa-file-medical-o"></i></div><div class="mp-kpi-label">This Month</div><div class="mp-kpi-value"><?= $this_month_count; ?></div></div>
  <div class="mp-kpi-card success"><div class="mp-kpi-icon"><i class="fa fa-list"></i></div><div class="mp-kpi-label">Recent Entries</div><div class="mp-kpi-value"><?= count($latest_notes); ?></div></div>
  <div class="mp-kpi-card warn"><div class="mp-kpi-icon"><i class="fa fa-clock-o"></i></div><div class="mp-kpi-label">Refill Due (7 days)</div><div class="mp-kpi-value"><?= count($refill_reminders); ?></div></div>
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(220,38,38,.1);color:var(--mp-danger)"><i class="fa fa-exclamation-triangle"></i></div><div class="mp-kpi-label">Allergy Alerts</div><div class="mp-kpi-value" id="allergy-count">-</div></div>
</div>

<?php if(!empty($refill_reminders)): ?>
<div class="mp-card-form" style="margin-top:16px;">
  <div class="mp-card-head">
    <h3><i class="fa fa-bell-o"></i> Refill Reminders</h3>
    <small class="mp-form-hint"><?= mp_label('customer'); ?>s with refills due in the next 7 days</small>
  </div>
  <div class="mp-card-body" style="padding:12px;">
    <?php foreach($refill_reminders as $rm): ?>
    <div style="background:var(--mp-surface);border-radius:12px;padding:16px;border:1px solid var(--mp-border);border-left:4px solid var(--mp-warning);margin-bottom:10px;display:flex;align-items:center;justify-content:space-between;">
      <div style="flex:1;">
        <div style="font-size:14px;font-weight:700;color:var(--mp-text);"><?= htmlspecialchars($rm->customer_name); ?> <small style="color:var(--mp-muted);">(<?= htmlspecialchars($rm->mobile ?? ''); ?>)</small></div>
        <div style="font-size:13px;color:var(--mp-muted);">Refill by: <strong><?= show_date($rm->next_refill_date); ?></strong> &middot; <?= $rm->refills_remaining; ?> refills remaining</div>
      </div>
      <a href="<?= base_url('operations/medical_note/' . $rm->id); ?>" class="mp-qa-btn blue" style="white-space:nowrap;"><i class="fa fa-pencil"></i> Process Refill</a>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<div class="mp-table-wrap" style="margin-top:16px;">
  <div class="mp-card-head"><h3><i class="fa fa-file-medical-o"></i> Medical Notes</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="med-notes-table" class="table mp-dt-table" width="100%">
        <thead><tr><th>#</th><th><?= mp_label('customer'); ?></th><th>Doctor</th><th>Diagnosis</th><th>Rx</th><th>Refills</th><th>Next Refill</th><th>Date</th><th>Allergy</th><th>Action</th></tr></thead>
      </table>
    </div>
  </div>
</div>

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
