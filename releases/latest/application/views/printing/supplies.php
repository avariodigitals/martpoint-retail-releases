<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $CI =& get_instance(); ?>
<div class="mp-section">
  <div class="mp-page-head">
    <h2>Consumables</h2>
    <div class="mp-page-sub">Ink, toner, paper, plates and other consumables requested per machine.</div>
    <?php if (!empty($can_manage) && !empty($machines)): ?>
    <button class="btn btn-primary" onclick="$('#supModal').modal('show')"><i class="fa fa-plus"></i> Request Consumable</button>
    <?php endif; ?>
  </div>

  <div class="mp-card"><div class="mp-card-body" style="padding:0">
    <?php if (empty($supplies)): ?>
      <div class="mp-empty-state" style="padding:30px">Nothing recorded yet.</div>
    <?php else: ?>
      <table class="table" style="margin:0">
        <thead>
          <tr><th>Item</th><th style="width:150px">Machine</th><th style="width:120px">Qty</th><th style="width:130px">Status</th><th style="width:120px">Requested</th></tr>
        </thead>
        <tbody>
        <?php foreach ($supplies as $s):
          $status = strtolower((string) ($s->status ?? ''));
          $col = ['approved' => '#059669', 'pending' => '#d97706', 'requested' => '#d97706',
                  'rejected' => '#dc2626', 'installed' => '#2563eb', 'returned' => '#78716c'][$status] ?? '#64748b';
          // The supplies table names these description/requested_qty — not
          // item/qty. `item_name` comes from the linked stock item when one is
          // set, so a free-text request falls back to its own description.
          $label = trim((string) ($s->item_name ?? '')) ?: trim((string) ($s->description ?? ''));
          $qty   = $s->requested_qty ?? null;
        ?>
          <tr>
            <td>
              <b><?= htmlspecialchars($label !== '' ? $label : '—') ?></b>
              <?php if (!empty($s->item_name) && !empty($s->description)): ?>
                <div style="font-size:11.5px;color:#78716C"><?= htmlspecialchars($s->description) ?></div>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($s->machine_id)): ?>
                <a href="<?= base_url('printing_ops/machine/' . (int) $s->machine_id) ?>"><?= htmlspecialchars($s->machine_name ?? ('#' . (int) $s->machine_id)) ?></a>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td>
              <?= $qty !== null ? rtrim(rtrim(number_format((float) $qty, 2), '0'), '.') : '—' ?>
              <?php if (!empty($s->unit_name)): ?> <small class="text-muted"><?= htmlspecialchars($s->unit_name) ?></small><?php endif; ?>
            </td>
            <td>
              <span style="display:inline-block;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;padding:3px 8px;border-radius:20px;background:<?= $col ?>22;color:<?= $col ?>">
                <?= htmlspecialchars($s->status ?: 'unknown') ?>
              </span>
            </td>
            <td><?= !empty($s->requested_at) ? show_date($s->requested_at) : (!empty($s->created_at) ? show_date($s->created_at) : '—') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div></div>
</div>

<?php if (!empty($can_manage) && !empty($machines)): ?>
<div class="modal fade" id="supModal" tabindex="-1">
  <div class="modal-dialog" style="max-width:460px">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Request Consumable</h4>
      </div>
      <div class="modal-body">
        <div class="form-group"><label>Machine <span class="text-danger">*</span></label>
          <select id="spMachine" class="form-control">
            <option value="">— pick a machine —</option>
            <?php foreach ($machines as $m): ?>
              <option value="<?= (int) $m->id ?>"><?= htmlspecialchars($m->name ?: 'Machine') ?><?= $m->machine_code ? ' (' . htmlspecialchars($m->machine_code) . ')' : '' ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="form-group"><label>Item <span class="text-danger">*</span></label><input type="text" id="spItem" class="form-control" placeholder="e.g. Cyan ink cartridge"></div>
        <div class="row">
          <div class="col-xs-6"><div class="form-group"><label>Quantity <span class="text-danger">*</span></label><input type="number" id="spQty" class="form-control" step="0.01" min="0.01"></div></div>
          <div class="col-xs-6"><div class="form-group"><label>Unit</label>
            <select id="spUnitId" class="form-control">
              <option value="">— optional —</option>
              <?php
                // db_units drives the conversion; leaving it blank records a
                // free-text quantity, which is fine but cannot be costed.
                $units = $CI->db->select('id, unit_name')->where('status', 1)
                    ->order_by('unit_name')->get('db_units')->result();
                foreach ($units as $u): ?>
                <option value="<?= (int) $u->id ?>"><?= htmlspecialchars($u->unit_name) ?></option>
              <?php endforeach; ?>
            </select></div></div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="spSaveBtn" onclick="saveSupply()">Request</button>
      </div>
    </div>
  </div>
</div>
<script>
function saveSupply() {
  if (!$('#spMachine').val()) { toastr.error('Pick a machine.'); return; }
  if (!$.trim($('#spItem').val())) { toastr.error('Name the consumable.'); return; }
  $('#spSaveBtn').prop('disabled', true).text('Saving…');
  var data = {
    machine_id: $('#spMachine').val(), description: $('#spItem').val(),
    requested_qty: $('#spQty').val(), unit_id: $('#spUnitId').val()
  };
  if (window.csrfName) data[window.csrfName] = window.csrfHash;
  $.post('<?= base_url('printing_ops/supply_save') ?>', data, function (r) {
    $('#spSaveBtn').prop('disabled', false).text('Request');
    if (r.status === 'ok') {
      if (r.csrf_hash) window.csrfHash = r.csrf_hash;
      toastr.success(r.message);
      $('#supModal').modal('hide');
      setTimeout(function () { location.reload(); }, 900);
    } else { toastr.error(r.message || 'Could not save.'); }
  }, 'json').fail(function () {
    $('#spSaveBtn').prop('disabled', false).text('Request');
    toastr.error('Server error');
  });
}
</script>
<?php endif; ?>
