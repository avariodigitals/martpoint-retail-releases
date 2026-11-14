<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $CI =& get_instance(); ?>
<div class="mp-section">
  <div class="mp-page-head">
    <h2>Counter Readings</h2>
    <div class="mp-page-sub">Meter readings per machine. A reading below the previous one is flagged — it usually means a typo or a rolled-over counter.</div>
  </div>

  <?php if (!empty($invalid)): ?>
  <div class="mp-card" style="border-color:#FCA5A5;background:#FEF2F2;margin-bottom:14px">
    <div class="mp-card-body" style="padding:12px 14px">
      <b style="color:#B91C1C"><i class="fa fa-exclamation-triangle"></i> <?= count($invalid) ?> reading<?= count($invalid) === 1 ? '' : 's' ?> need a look</b>
      <table class="table" style="margin:8px 0 0;background:#fff">
        <thead><tr><th>Machine</th><th>Reading</th><th>Recorded</th><th>Why</th></tr></thead>
        <tbody>
        <?php foreach ($invalid as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r->machine_name ?? ('#' . (int) ($r->machine_id ?? 0))) ?></td>
            <td><b><?= number_format((float) ($r->reading ?? 0)) ?></b></td>
            <td><?= !empty($r->recorded_at) ? show_date($r->recorded_at) : '—' ?></td>
            <td style="color:#B91C1C;font-size:12px"><?= htmlspecialchars($r->issue ?? $r->reason ?? 'Below the previous reading') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <div class="mp-card"><div class="mp-card-body">
    <?php if (empty($machines)): ?>
      <div class="mp-empty-state">No machines yet. <a href="<?= base_url('printing_ops/machines') ?>">Add one first</a>.</div>
    <?php else: ?>
      <table class="table" style="margin:0">
        <thead>
          <tr><th>Machine</th><th style="width:140px">Status</th><th style="width:200px">Last reading</th><th style="width:110px"></th></tr>
        </thead>
        <tbody>
        <?php foreach ($machines as $m): ?>
          <tr>
            <td>
              <a href="<?= base_url('printing_ops/machine/' . (int) $m->id) ?>" style="font-weight:600"><?= htmlspecialchars($m->name ?: 'Machine') ?></a>
              <div style="font-size:11.5px;color:#78716C"><?= htmlspecialchars($m->machine_code ?: '') ?></div>
            </td>
            <td><?= htmlspecialchars(str_replace('_', ' ', $m->status ?: 'unknown')) ?></td>
            <td>
              <?php if (!empty($m->last_reading)): ?>
                <b><?= number_format((float) $m->last_reading) ?></b>
                <span style="color:#78716C;font-size:11.5px"><?= htmlspecialchars($m->counter_unit ?: '') ?></span>
              <?php elseif (!empty($m->last_counter)): ?>
                <b><?= number_format((float) $m->last_counter) ?></b>
              <?php else: ?>
                <span class="text-muted">no reading yet</span>
              <?php endif; ?>
            </td>
            <td class="text-right">
              <?php if (!empty($can_record)): ?>
              <button class="btn btn-xs btn-primary record-btn"
                      data-id="<?= (int) $m->id ?>"
                      data-name="<?= htmlspecialchars($m->name ?: 'Machine', ENT_QUOTES) ?>"
                      data-unit="<?= htmlspecialchars($m->counter_unit ?: 'impressions', ENT_QUOTES) ?>">Record</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div></div>
</div>

<?php if (!empty($can_record)): ?>
<div class="modal fade" id="readingModal" tabindex="-1">
  <div class="modal-dialog" style="max-width:420px">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Record Reading</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="rMachineId">
        <p style="font-size:13px">Machine: <b id="rMachineName">—</b></p>
        <div class="form-group">
          <label>Reading <span class="text-danger">*</span> <small class="text-muted" id="rUnit">impressions</small></label>
          <input type="number" id="rValue" class="form-control" step="0.01" placeholder="e.g. 148250">
        </div>
        <div class="form-group"><label>Notes</label><input type="text" id="rNotes" class="form-control"></div>
        <p class="help-block" style="font-size:11px">Enter the number as shown on the machine's meter.</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="rSaveBtn" onclick="saveReading()">Save Reading</button>
      </div>
    </div>
  </div>
</div>
<script>
$(document).on('click', '.record-btn', function () {
  $('#rMachineId').val($(this).data('id'));
  $('#rMachineName').text($(this).data('name'));
  $('#rUnit').text($(this).data('unit'));
  $('#rValue').val('');
  $('#rNotes').val('');
  $('#readingModal').modal('show');
});
function saveReading() {
  var v = $.trim($('#rValue').val());
  if (v === '') { toastr.error('Enter a reading.'); return; }
  $('#rSaveBtn').prop('disabled', true).text('Saving…');
  var data = { machine_id: $('#rMachineId').val(), reading: v, notes: $('#rNotes').val() };
  if (window.csrfName) data[window.csrfName] = window.csrfHash;
  $.post('<?= base_url('printing_ops/reading_save') ?>', data, function (r) {
    $('#rSaveBtn').prop('disabled', false).text('Save Reading');
    if (r.status === 'ok') {
      if (r.csrf_hash) window.csrfHash = r.csrf_hash;
      toastr.success(r.message);
      $('#readingModal').modal('hide');
      setTimeout(function () { location.reload(); }, 900);
    } else { toastr.error(r.message || 'Could not save.'); }
  }, 'json').fail(function () {
    $('#rSaveBtn').prop('disabled', false).text('Save Reading');
    toastr.error('Server error');
  });
}
</script>
<?php endif; ?>
