<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $CI =& get_instance(); ?>
<div class="mp-section">
  <div class="mp-page-head">
    <h2>Maintenance</h2>
    <div class="mp-page-sub">Service visits and machines due for attention
      <?php if (!empty($summary)): ?>
        <?php if (isset($summary->visits)): ?> · <?= (int) $summary->visits ?> visit<?= (int) $summary->visits === 1 ? '' : 's' ?> logged<?php endif; ?>
        <?php if (!empty($summary->cost)): ?> · <?= $CI->currency((float) $summary->cost, true) ?> total<?php endif; ?>
      <?php endif; ?>.
    </div>
    <?php if (!empty($can_manage) && !empty($machines)): ?>
    <button class="btn btn-primary" onclick="$('#maintModal').modal('show')"><i class="fa fa-wrench"></i> Log Visit</button>
    <?php endif; ?>
  </div>

  <?php if (!empty($due)): ?>
  <div class="mp-card" style="border-color:#FDE68A;background:#FFFBEB;margin-bottom:14px">
    <div class="mp-card-body" style="padding:12px 14px">
      <b style="color:#92400E"><i class="fa fa-clock-o"></i> Due for service (<?= count($due) ?>)</b>
      <table class="table" style="margin:8px 0 0;background:#fff">
        <thead><tr><th>Machine</th><th style="width:150px">Next service</th><th style="width:170px">Impressions due</th></tr></thead>
        <tbody>
        <?php foreach ($due as $d): ?>
          <tr>
            <td><a href="<?= base_url('printing_ops/machine/' . (int) $d->id) ?>"><?= htmlspecialchars($d->name ?: 'Machine') ?></a></td>
            <td><?= !empty($d->next_service_at) ? show_date($d->next_service_at) : '<span class="text-muted">—</span>' ?></td>
            <td><?= !empty($d->next_service_impressions) ? number_format((float) $d->next_service_impressions) : '<span class="text-muted">—</span>' ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <div class="mp-card"><div class="mp-card-body" style="padding:0">
    <?php if (empty($visits)): ?>
      <div class="mp-empty-state" style="padding:30px">No maintenance logged yet.</div>
    <?php else: ?>
      <table class="table" style="margin:0">
        <thead>
          <tr><th style="width:110px">Date</th><th>Machine</th><th>Engineer</th><th>Work done</th><th style="width:110px">Cost</th></tr>
        </thead>
        <tbody>
        <?php foreach ($visits as $v): ?>
          <tr>
            <td><?= !empty($v->performed_at) ? show_date($v->performed_at) : '—' ?></td>
            <td>
              <?php if (!empty($v->machine_id)): ?>
                <a href="<?= base_url('printing_ops/machine/' . (int) $v->machine_id) ?>"><?= htmlspecialchars($v->machine_name ?? ('#' . (int) $v->machine_id)) ?></a>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td><?= htmlspecialchars($v->engineer ?: '—') ?></td>
            <td style="font-size:12.5px"><?= htmlspecialchars($v->description ?: '—') ?></td>
            <td><?= isset($v->cost) ? $CI->currency((float) $v->cost, true) : '—' ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div></div>
</div>

<?php if (!empty($can_manage) && !empty($machines)): ?>
<div class="modal fade" id="maintModal" tabindex="-1">
  <div class="modal-dialog" style="max-width:500px">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Log Maintenance Visit</h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-sm-7"><div class="form-group"><label>Machine <span class="text-danger">*</span></label>
            <select id="mtMachine" class="form-control">
              <option value="">— pick a machine —</option>
              <?php foreach ($machines as $m): ?>
                <option value="<?= (int) $m->id ?>"><?= htmlspecialchars($m->name ?: 'Machine') ?><?= $m->machine_code ? ' (' . htmlspecialchars($m->machine_code) . ')' : '' ?></option>
              <?php endforeach; ?>
            </select></div></div>
          <div class="col-sm-5"><div class="form-group"><label>Date</label>
            <input type="date" id="mtDate" class="form-control" value="<?= date('Y-m-d') ?>"></div></div>
        </div>
        <div class="row">
          <div class="col-sm-6"><div class="form-group"><label>Engineer</label><input type="text" id="mtEngineer" class="form-control"></div></div>
          <div class="col-sm-6"><div class="form-group"><label>Cost</label><input type="number" id="mtCost" class="form-control" step="0.01" min="0" placeholder="0.00"></div></div>
        </div>
        <div class="form-group"><label>Work done</label><textarea id="mtDesc" class="form-control" rows="2" placeholder="e.g. replaced fuser unit, cleaned heads"></textarea></div>
        <div class="form-group"><label>Meter reading at service <small class="text-muted">(optional)</small></label><input type="number" id="mtReading" class="form-control" step="0.01"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="mtSaveBtn" onclick="saveMaint()">Log Visit</button>
      </div>
    </div>
  </div>
</div>
<script>
function saveMaint() {
  if (!$('#mtMachine').val()) { toastr.error('Pick a machine.'); return; }
  $('#mtSaveBtn').prop('disabled', true).text('Saving…');
  var data = {
    machine_id: $('#mtMachine').val(),
    performed_at: $('#mtDate').val(),
    engineer: $('#mtEngineer').val(),
    cost: $('#mtCost').val(),
    description: $('#mtDesc').val(),
    reading: $('#mtReading').val()
  };
  if (window.csrfName) data[window.csrfName] = window.csrfHash;
  $.post('<?= base_url('printing_ops/maintenance_save') ?>', data, function (r) {
    $('#mtSaveBtn').prop('disabled', false).text('Log Visit');
    if (r.status === 'ok') {
      if (r.csrf_hash) window.csrfHash = r.csrf_hash;
      toastr.success(r.message);
      $('#maintModal').modal('hide');
      setTimeout(function () { location.reload(); }, 900);
    } else { toastr.error(r.message || 'Could not save.'); }
  }, 'json').fail(function () {
    $('#mtSaveBtn').prop('disabled', false).text('Log Visit');
    toastr.error('Server error');
  });
}
</script>
<?php endif; ?>
