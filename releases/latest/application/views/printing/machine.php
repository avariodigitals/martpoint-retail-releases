<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
  $CI =& get_instance();
  $st_colour = function ($s) {
      switch (strtolower((string) $s)) {
          case 'available': return '#059669';
          case 'running':   return '#2563eb';
          case 'maintenance': return '#d97706';
          case 'breakdown': return '#dc2626';
          case 'retired':   return '#78716c';
          default:          return '#64748b';
      }
  };
  $c = $st_colour($machine->status ?? '');
?>
<div class="mp-section">
  <div class="mp-page-head">
    <h2><?= htmlspecialchars($machine->name ?: 'Machine') ?></h2>
    <div class="mp-page-sub">
      <?= htmlspecialchars(trim(($machine->manufacturer ?? '') . ' ' . ($machine->model ?? ''))) ?: 'Print machine' ?>
      <?php if (!empty($machine->location)): ?> · <?= htmlspecialchars($machine->location) ?><?php endif; ?>
    </div>
    <a href="<?= base_url('printing_ops/machines') ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> All Machines</a>
  </div>

  <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px">
    <div class="mp-card" style="flex:1 1 160px"><div class="mp-card-body" style="padding:12px 14px">
      <div style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#78716C">Status</div>
      <span style="display:inline-block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;padding:3px 9px;border-radius:20px;background:<?= $c ?>22;color:<?= $c ?>;margin-top:5px">
        <?= htmlspecialchars(str_replace('_', ' ', $machine->status ?: 'unknown')) ?>
      </span>
    </div></div>
    <div class="mp-card" style="flex:1 1 160px"><div class="mp-card-body" style="padding:12px 14px">
      <div style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#78716C">Counter</div>
      <div style="font-size:18px;font-weight:700;margin-top:3px">
        <?php if (!empty($machine->last_reading) || !empty($machine->last_counter)): ?>
          <?= number_format((float) ($machine->last_reading ?? $machine->last_counter)) ?>
        <?php else: ?><span class="text-muted" style="font-size:14px">no reading</span><?php endif; ?>
      </div>
      <div style="font-size:11px;color:#78716C"><?= htmlspecialchars($machine->counter_unit ?: '') ?></div>
    </div></div>
    <div class="mp-card" style="flex:1 1 160px"><div class="mp-card-body" style="padding:12px 14px">
      <div style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#78716C">Next service</div>
      <div style="font-size:15px;font-weight:600;margin-top:5px">
        <?= !empty($machine->next_service_at) ? show_date($machine->next_service_at) : '<span class="text-muted" style="font-size:13px">not scheduled</span>' ?>
      </div>
    </div></div>
    <div class="mp-card" style="flex:1 1 160px"><div class="mp-card-body" style="padding:12px 14px">
      <div style="font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#78716C">Service interval</div>
      <div style="font-size:15px;font-weight:600;margin-top:5px">
        <?= (int) ($machine->service_interval_days ?? 0) > 0 ? (int) $machine->service_interval_days . ' days' : '<span class="text-muted" style="font-size:13px">none</span>' ?>
      </div>
    </div></div>
  </div>

  <?php if (!empty($can_manage)): ?>
  <div style="margin-bottom:14px;display:flex;gap:6px;flex-wrap:wrap">
    <button class="btn btn-default btn-sm" onclick="$('#statusModal').modal('show')"><i class="fa fa-exchange"></i> Change status</button>
    <button class="btn btn-default btn-sm" onclick="$('#readingModal').modal('show')"><i class="fa fa-tachometer"></i> Record reading</button>
    <?php if (empty($machine->status_active)): ?>
    <button class="btn btn-danger btn-sm" onclick="retireMachine()"><i class="fa fa-archive"></i> Retire</button>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div class="row">
    <div class="col-md-6">
      <div class="mp-card" style="margin-bottom:14px"><div class="mp-card-body" style="padding:0">
        <div style="padding:12px 14px;border-bottom:1px solid #E7E5E4;font-weight:700;font-size:13px">Recent readings</div>
        <?php if (empty($readings)): ?>
          <div class="mp-empty-state" style="padding:24px">No readings recorded.</div>
        <?php else: ?>
          <table class="table" style="margin:0">
            <thead><tr><th>Reading</th><th>When</th><th>Notes</th></tr></thead>
            <tbody>
            <?php foreach ($readings as $r): ?>
              <tr>
                <td><b><?= number_format((float) ($r->reading ?? 0)) ?></b></td>
                <td style="font-size:12px"><?= !empty($r->recorded_at) ? show_date($r->recorded_at) : '—' ?></td>
                <td style="font-size:12px;color:#78716C"><?= htmlspecialchars($r->notes ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div></div>
    </div>

    <div class="col-md-6">
      <div class="mp-card" style="margin-bottom:14px"><div class="mp-card-body" style="padding:0">
        <div style="padding:12px 14px;border-bottom:1px solid #E7E5E4;font-weight:700;font-size:13px">Maintenance history</div>
        <?php if (empty($maintenance)): ?>
          <div class="mp-empty-state" style="padding:24px">No maintenance logged.</div>
        <?php else: ?>
          <table class="table" style="margin:0">
            <thead><tr><th style="width:100px">Date</th><th>Work</th><th style="width:90px">Cost</th></tr></thead>
            <tbody>
            <?php foreach ($maintenance as $v): ?>
              <tr>
                <td style="font-size:12px"><?= !empty($v->performed_at) ? show_date($v->performed_at) : '—' ?></td>
                <td style="font-size:12px"><?= htmlspecialchars($v->description ?: ($v->engineer ?: '—')) ?></td>
                <td style="font-size:12px"><?= isset($v->cost) ? $CI->currency((float) $v->cost, true) : '—' ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div></div>

      <div class="mp-card"><div class="mp-card-body" style="padding:0">
        <div style="padding:12px 14px;border-bottom:1px solid #E7E5E4;font-weight:700;font-size:13px">Consumables</div>
        <?php if (empty($supplies)): ?>
          <div class="mp-empty-state" style="padding:24px">Nothing requested for this machine.</div>
        <?php else: ?>
          <table class="table" style="margin:0">
            <thead><tr><th>Item</th><th style="width:90px">Qty</th><th style="width:100px">Status</th></tr></thead>
            <tbody>
            <?php foreach ($supplies as $s): ?>
              <tr>
                <td style="font-size:12.5px"><?= htmlspecialchars($s->item ?? '—') ?></td>
                <td style="font-size:12.5px"><?= isset($s->qty) ? rtrim(rtrim(number_format((float) $s->qty, 2), '0'), '.') : '—' ?></td>
                <td style="font-size:12px"><?= htmlspecialchars($s->status ?? '') ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div></div>
    </div>
  </div>
</div>

<?php if (!empty($can_manage)): ?>
<div class="modal fade" id="statusModal" tabindex="-1">
  <div class="modal-dialog" style="max-width:400px"><div class="modal-content">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Change Status</h4></div>
    <div class="modal-body">
      <div class="form-group"><label>Status</label>
        <select id="stStatus" class="form-control">
          <option value="available">Available</option>
          <option value="running">Running</option>
          <option value="idle">Idle</option>
          <option value="maintenance">Under maintenance</option>
          <option value="breakdown">Breakdown</option>
        </select></div>
      <div class="form-group"><label>Reason <small class="text-muted">(shown against the machine)</small></label>
        <input type="text" id="stReason" class="form-control" placeholder="e.g. fuser ordered, 3 days"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-default" data-dismiss="modal">Cancel</button>
      <button class="btn btn-primary" id="stSaveBtn" onclick="saveStatus()">Save</button>
    </div>
  </div></div>
</div>

<div class="modal fade" id="readingModal" tabindex="-1">
  <div class="modal-dialog" style="max-width:400px"><div class="modal-content">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Record Reading</h4></div>
    <div class="modal-body">
      <div class="form-group"><label>Reading <span class="text-danger">*</span> <small class="text-muted"><?= htmlspecialchars($machine->counter_unit ?: '') ?></small></label>
        <input type="number" id="mrValue" class="form-control" step="0.01"></div>
      <div class="form-group"><label>Notes</label><input type="text" id="mrNotes" class="form-control"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-default" data-dismiss="modal">Cancel</button>
      <button class="btn btn-primary" id="mrSaveBtn" onclick="saveMachineReading()">Save Reading</button>
    </div>
  </div></div>
</div>

<script>
var MACHINE_ID = <?= (int) $machine->id ?>;
function post(url, data, btn, label) {
  $(btn).prop('disabled', true).text('Saving…');
  if (window.csrfName) data[window.csrfName] = window.csrfHash;
  $.post(url, data, function (r) {
    $(btn).prop('disabled', false).text(label);
    if (r.status === 'ok') {
      if (r.csrf_hash) window.csrfHash = r.csrf_hash;
      toastr.success(r.message);
      $('.modal').modal('hide');
      setTimeout(function () { location.reload(); }, 900);
    } else { toastr.error(r.message || 'Could not save.'); }
  }, 'json').fail(function () { $(btn).prop('disabled', false).text(label); toastr.error('Server error'); });
}
function saveStatus() {
  post('<?= base_url('printing_ops/machine_status') ?>',
       { id: MACHINE_ID, status: $('#stStatus').val(), reason: $('#stReason').val() },
       '#stSaveBtn', 'Save');
}
function saveMachineReading() {
  var v = $.trim($('#mrValue').val());
  if (v === '') { toastr.error('Enter a reading.'); return; }
  post('<?= base_url('printing_ops/reading_save') ?>',
       { machine_id: MACHINE_ID, reading: v, notes: $('#mrNotes').val() },
       '#mrSaveBtn', 'Save Reading');
}
function retireMachine() {
  if (!confirm('Retire this machine?\n\nIt stops appearing as available but its history is kept.')) return;
  post('<?= base_url('printing_ops/machine_retire') ?>', { id: MACHINE_ID }, 'button', 'Retire');
}
</script>
<?php endif; ?>
