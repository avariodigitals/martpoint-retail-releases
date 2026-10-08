<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $CI =& get_instance(); ?>
<?php
  // Machine status → colour. One vocabulary shared by the badge and the row
  // accent so a colour never disagrees with its word.
  $st_colour = function ($s) {
      switch (strtolower((string) $s)) {
          case 'available': return '#059669';
          case 'running':   return '#2563eb';
          case 'idle':      return '#64748b';
          case 'maintenance': return '#d97706';
          case 'breakdown': return '#dc2626';
          case 'retired':   return '#78716c';
          default:          return '#64748b';
      }
  };
?>
<div class="mp-section print-machines">
  <div class="mp-page-head">
    <h2>Machines</h2>
    <div class="mp-page-sub">
      Presses, counters and service schedules for this print shop.
      <?= count($machines) ?> machine<?= count($machines) === 1 ? '' : 's' ?><?php if (!empty($due)): ?>,
      <b><?= count($due) ?> due for service</b><?php endif; ?>.
    </div>
    <?php if (!empty($can_manage)): ?>
    <button class="btn btn-primary" onclick="$('#machineModal').modal('show')"><i class="fa fa-plus"></i> Add Machine</button>
    <?php endif; ?>
  </div>

  <?php if (!empty($due)): ?>
  <div class="mp-card" style="border-color:#FDE68A;background:#FFFBEB;margin-bottom:14px">
    <div class="mp-card-body" style="padding:12px 14px">
      <b style="color:#92400E"><i class="fa fa-wrench"></i> Service due</b>
      <div style="font-size:12.5px;color:#92400E;margin-top:6px">
        <?php foreach ($due as $d): ?>
          <a href="<?= base_url('printing_ops/machine/' . (int) $d->id) ?>" style="color:#92400E;margin-right:12px;white-space:nowrap">
            <?= htmlspecialchars($d->name) ?>
            <?php if (!empty($d->next_service_at)): ?>
              · due <?= show_date($d->next_service_at) ?>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (empty($machines)): ?>
  <div class="mp-card"><div class="mp-card-body">
    <div class="mp-empty-state">
      No machines yet.
      <?php if (!empty($can_manage)): ?>Add your first press to start tracking counter readings, consumables and service intervals.<?php endif; ?>
    </div>
  </div></div>
  <?php else: ?>
  <div class="mp-card"><div class="mp-card-body" style="padding:0">
    <table class="table" style="margin:0">
      <thead>
        <tr>
          <th>Machine</th>
          <th style="width:120px">Code</th>
          <th style="width:130px">Status</th>
          <th style="width:150px">Next service</th>
          <th style="width:150px">Location</th>
          <th style="width:90px"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($machines as $m):
          $c = $st_colour($m->status ?? '');
        ?>
        <tr>
          <td>
            <a href="<?= base_url('printing_ops/machine/' . (int) $m->id) ?>" style="font-weight:600">
              <?= htmlspecialchars($m->name ?: 'Machine') ?>
            </a>
            <div style="font-size:11.5px;color:#78716C">
              <?= htmlspecialchars(trim(($m->manufacturer ?? '') . ' ' . ($m->model ?? ''))) ?: '—' ?>
              <?php if (!empty($m->serial_no)): ?> · sn <?= htmlspecialchars($m->serial_no) ?><?php endif; ?>
            </div>
          </td>
          <td><code><?= htmlspecialchars($m->machine_code ?: '—') ?></code></td>
          <td>
            <span style="display:inline-block;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;padding:3px 8px;border-radius:20px;background:<?= $c ?>22;color:<?= $c ?>">
              <?= htmlspecialchars(str_replace('_', ' ', $m->status ?: 'unknown')) ?>
            </span>
            <?php if (!empty($m->status_reason)): ?>
              <div style="font-size:11px;color:#78716C;margin-top:3px"><?= htmlspecialchars($m->status_reason) ?></div>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($m->next_service_at)): ?>
              <?= show_date($m->next_service_at) ?>
            <?php else: ?>
              <span class="text-muted">not scheduled</span>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($m->location ?: '—') ?></td>
          <td class="text-right">
            <?php if (!empty($can_manage)): ?>
            <button class="btn btn-xs btn-default" title="Edit"
                    onclick='editMachine(<?= json_encode([
                      "id" => (int) $m->id,
                      "name" => (string) $m->name,
                      "machine_code" => (string) $m->machine_code,
                      "model" => (string) $m->model,
                      "manufacturer" => (string) $m->manufacturer,
                      "serial_no" => (string) $m->serial_no,
                      "location" => (string) $m->location,
                      "reading_mode" => (string) ($m->reading_mode ?? "counter"),
                      "counter_unit" => (string) ($m->counter_unit ?? "impressions"),
                      "service_interval_days" => (int) ($m->service_interval_days ?? 0),
                      "notes" => (string) ($m->notes ?? ""),
                    ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
              <i class="fa fa-pencil"></i>
            </button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
  <?php endif; ?>
</div>

<?php if (!empty($can_manage)): ?>
<div class="modal fade" id="machineModal" tabindex="-1">
  <div class="modal-dialog" style="max-width:520px">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="machineModalTitle">Add Machine</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="mId">
        <div class="row">
          <div class="col-sm-8"><div class="form-group"><label>Name <span class="text-danger">*</span></label><input type="text" id="mName" class="form-control" placeholder="e.g. Roland VersaCAMM"></div></div>
          <div class="col-sm-4"><div class="form-group"><label>Code</label><input type="text" id="mCode" class="form-control" placeholder="M-01"></div></div>
        </div>
        <div class="row">
          <div class="col-sm-6"><div class="form-group"><label>Manufacturer</label><input type="text" id="mManufacturer" class="form-control"></div></div>
          <div class="col-sm-6"><div class="form-group"><label>Model</label><input type="text" id="mModel" class="form-control"></div></div>
        </div>
        <div class="row">
          <div class="col-sm-6"><div class="form-group"><label>Serial number</label><input type="text" id="mSerial" class="form-control"></div></div>
          <div class="col-sm-6"><div class="form-group"><label>Location</label><input type="text" id="mLocation" class="form-control" placeholder="e.g. Press room"></div></div>
        </div>
        <div class="row">
          <div class="col-sm-4"><div class="form-group"><label>Reading mode</label>
            <select id="mReadingMode" class="form-control">
              <option value="counter">Meter counter</option>
              <option value="manual">Manual</option>
              <option value="none">No counter</option>
            </select></div></div>
          <div class="col-sm-4"><div class="form-group"><label>Counter unit</label><input type="text" id="mCounterUnit" class="form-control" value="impressions"></div></div>
          <div class="col-sm-4"><div class="form-group"><label>Service every (days)</label><input type="number" id="mInterval" class="form-control" min="0" value="0"></div></div>
        </div>
        <div class="form-group"><label>Notes</label><textarea id="mNotes" class="form-control" rows="2"></textarea></div>
        <p class="help-block" style="font-size:11px">Every field except the name is optional — you can fill in the rest later.</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" onclick="saveMachine()" id="mSaveBtn">Save Machine</button>
      </div>
    </div>
  </div>
</div>

<script>
function editMachine(m) {
  $('#machineModalTitle').text('Edit Machine');
  $('#mId').val(m.id);
  $('#mName').val(m.name);
  $('#mCode').val(m.machine_code);
  $('#mManufacturer').val(m.manufacturer);
  $('#mModel').val(m.model);
  $('#mSerial').val(m.serial_no);
  $('#mLocation').val(m.location);
  $('#mReadingMode').val(m.reading_mode || 'counter');
  $('#mCounterUnit').val(m.counter_unit || 'impressions');
  $('#mInterval').val(m.service_interval_days || 0);
  $('#mNotes').val(m.notes);
  $('#machineModal').modal('show');
}
$('#machineModal').on('hidden.bs.modal', function () {
  $('#machineModalTitle').text('Add Machine');
  $('#machineModal input, #machineModal textarea').val('');
  $('#mId').val('');
  $('#mCounterUnit').val('impressions');
  $('#mInterval').val(0);
});
function saveMachine() {
  var name = $.trim($('#mName').val());
  if (!name) { toastr.error('Give the machine a name.'); return; }
  $('#mSaveBtn').prop('disabled', true).text('Saving…');
  var data = {
    id: $('#mId').val(),
    name: name,
    machine_code: $('#mCode').val(),
    manufacturer: $('#mManufacturer').val(),
    model: $('#mModel').val(),
    serial_no: $('#mSerial').val(),
    location: $('#mLocation').val(),
    reading_mode: $('#mReadingMode').val(),
    counter_unit: $('#mCounterUnit').val(),
    service_interval_days: $('#mInterval').val(),
    notes: $('#mNotes').val()
  };
  if (window.csrfName) data[window.csrfName] = window.csrfHash;
  $.post('<?= base_url('printing_ops/machine_save') ?>', data, function (r) {
    $('#mSaveBtn').prop('disabled', false).text('Save Machine');
    if (r.status === 'ok') {
      if (r.csrf_hash) window.csrfHash = r.csrf_hash;
      toastr.success(r.message);
      $('#machineModal').modal('hide');
      setTimeout(function () { location.reload(); }, 900);
    } else { toastr.error(r.message || 'Could not save.'); }
  }, 'json').fail(function () {
    $('#mSaveBtn').prop('disabled', false).text('Save Machine');
    toastr.error('Server error');
  });
}
</script>
<?php endif; ?>
