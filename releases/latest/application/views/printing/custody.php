<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php $CI =& get_instance(); ?>
<div class="mp-section">
  <div class="mp-page-head">
    <h2>Customer Materials</h2>
    <div class="mp-page-sub">Stock a client has placed with you. You hold it, you draw it down on their jobs, and the balance must always be explainable.</div>
    <?php if (!empty($can_manage)): ?>
    <button class="btn btn-primary" onclick="$('#custModal').modal('show')"><i class="fa fa-plus"></i> Receive Material</button>
    <?php endif; ?>
  </div>

  <?php if (!empty($position)): ?>
  <div class="mp-card" style="margin-bottom:14px"><div class="mp-card-body" style="padding:12px 14px">
    <b>Position</b>
    <div style="font-size:12.5px;color:#44403C;margin-top:6px">
      <?php if (is_object($position) && isset($position->holders)): ?>
        <?= (int) $position->holders ?> client<?= (int) $position->holders === 1 ? '' : 's' ?> holding material
        <?php if (isset($position->lines)): ?> · <?= (int) $position->lines ?> line<?= (int) $position->lines === 1 ? '' : 's' ?><?php endif; ?>
      <?php endif; ?>
      <?php if (is_array($position)): ?>
        <?php foreach ($position as $k => $v): if (is_scalar($v)): ?>
          <span style="margin-right:14px"><b><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $k))) ?>:</b> <?= htmlspecialchars((string) $v) ?></span>
        <?php endif; endforeach; ?>
      <?php endif; ?>
    </div>
  </div></div>
  <?php endif; ?>

  <?php if (!empty($mismatch)): ?>
  <div class="mp-card" style="border-color:#FCA5A5;background:#FEF2F2;margin-bottom:14px">
    <div class="mp-card-body" style="padding:12px 14px">
      <b style="color:#B91C1C"><i class="fa fa-exclamation-triangle"></i> <?= count($mismatch) ?> unexplained difference<?= count($mismatch) === 1 ? '' : 's' ?></b>
      <div style="font-size:12px;color:#B91C1C;margin-top:4px">Received minus issued does not match the recorded balance. This is the figure that matters when a client asks where their stock went.</div>
    </div>
  </div>
  <?php endif; ?>

  <div class="mp-card"><div class="mp-card-body" style="padding:0">
    <?php if (empty($materials)): ?>
      <div class="mp-empty-state" style="padding:30px">No client-owned material held right now.</div>
    <?php else: ?>
      <table class="table" style="margin:0">
        <thead>
          <tr>
            <th>Material</th>
            <th style="width:150px">Client</th>
            <th style="width:100px">Brought in</th>
            <th style="width:210px">Where it is now</th>
            <th style="width:110px">Remaining</th>
            <th style="width:90px"></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($materials as $m):
          // The custody table splits a received quantity across discrete
          // physical states. Showing one "balance" column answers "how much is
          // left" but not the question that actually gets asked — "where is my
          // stock?". So the states are listed individually and the total is
          // what remains with us.
          $received = (float) ($m->qty_received ?? 0);
          $states = array_filter([
              'In our custody'   => (float) ($m->qty_custody ?? 0),
              'In production'    => (float) ($m->qty_in_production ?? 0),
              'Finished'         => (float) ($m->qty_finished ?? 0),
              'Damaged'          => (float) ($m->qty_damaged ?? 0),
          ], function ($v) { return $v > 0; });
          $remaining = (float) ($m->qty_custody ?? 0) + (float) ($m->qty_in_production ?? 0)
                     + (float) ($m->qty_finished ?? 0) + (float) ($m->qty_damaged ?? 0);
          $done = (float) ($m->qty_returned ?? 0) + (float) ($m->qty_collected_finished ?? 0)
                + (float) ($m->qty_consumed ?? 0);
          $fmt = function ($v) { return rtrim(rtrim(number_format((float) $v, 2), '0'), '.'); };
        ?>
          <tr>
            <td>
              <b><?= htmlspecialchars($m->material_name ?? '—') ?></b>
              <?php if (!empty($m->material_type)): ?> <small class="text-muted"><?= htmlspecialchars($m->material_type) ?></small><?php endif; ?>
              <?php if (!empty($m->job_code)): ?>
                <div style="font-size:11.5px;color:#78716C">for job <?= htmlspecialchars($m->job_code) ?></div>
              <?php elseif (empty($m->job_id)): ?>
                <div style="font-size:11.5px;color:#B45309">not yet allocated to a job</div>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($m->customer_name ?? '—') ?></td>
            <td><b><?= $fmt($received) ?></b> <?php if (!empty($m->unit_label)): ?><small class="text-muted"><?= htmlspecialchars($m->unit_label) ?></small><?php endif; ?></td>
            <td>
              <?php if ($states): ?>
                <?php foreach ($states as $label => $qty): ?>
                  <span style="display:inline-block;font-size:11px;background:#E0F2FE;color:#0E7490;border-radius:20px;padding:2px 8px;margin:1px 3px 1px 0;white-space:nowrap">
                    <?= $fmt($qty) ?> <?= htmlspecialchars($label) ?>
                  </span>
                <?php endforeach; ?>
              <?php else: ?>
                <span class="text-muted">nothing left with us</span>
              <?php endif; ?>
              <?php if ($done > 0): ?>
                <div style="font-size:11px;color:#78716C;margin-top:2px"><?= $fmt($done) ?> returned / collected / used</div>
              <?php endif; ?>
            </td>
            <td>
              <b style="color:<?= $remaining > 0 ? '#0E7490' : '#78716C' ?>;font-size:15px"><?= $fmt($remaining) ?></b>
              <?php if ($remaining > 0): ?><div style="font-size:10.5px;color:#78716C">still with us</div><?php endif; ?>
            </td>
            <td class="text-right">
              <?php if (!empty($m->customer_id)): ?>
              <a class="btn btn-xs btn-default" href="<?= base_url('printing_ops/custody_statement/' . (int) $m->customer_id) ?>" title="Statement">Statement</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div></div>
</div>

<?php if (!empty($can_manage)): ?>
<div class="modal fade" id="custModal" tabindex="-1">
  <div class="modal-dialog" style="max-width:480px">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Receive Client Material</h4>
      </div>
      <div class="modal-body">
        <div class="form-group"><label>Client <span class="text-danger">*</span></label>
          <select id="cuCustomer" class="form-control" style="width:100%">
            <option value="">— search for a client —</option>
          </select></div>
        <div class="form-group"><label>Material <span class="text-danger">*</span></label><input type="text" id="cuItem" class="form-control" placeholder="e.g. 300gsm art card, client's fabric"></div>
        <div class="row">
          <div class="col-xs-6"><div class="form-group"><label>Quantity brought in <span class="text-danger">*</span></label><input type="number" id="cuQty" class="form-control" step="0.01" min="0.01"></div></div>
          <div class="col-xs-6"><div class="form-group"><label>Unit</label><input type="text" id="cuUnit" class="form-control" placeholder="sheets / kg / rolls"></div></div>
        </div>
        <div class="row">
          <div class="col-xs-6"><div class="form-group"><label>Received on</label><input type="date" id="cuDate" class="form-control" value="<?= date('Y-m-d') ?>"></div></div>
          <div class="col-xs-6"><div class="form-group"><label>Condition when received</label><input type="text" id="cuCondition" class="form-control" placeholder="e.g. sealed, slight foxing"></div></div>
        </div>
        <div class="form-group"><label>Notes</label><input type="text" id="cuNotes" class="form-control"></div>
        <p class="help-block" style="font-size:11px">Recording what arrived is what makes the balance auditable later. Material that arrives without a record cannot be reconciled when the client asks where their stock went.</p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="cuSaveBtn" onclick="saveCustody()">Receive</button>
      </div>
    </div>
  </div>
</div>
<script>
var cuSearchUrl = '<?= base_url('customers/get_customers_select_list') ?>';
$('#custModal').on('shown.bs.modal', function () {
  // Reuse the app's existing customer lookup so the client list behaves the
  // same here as everywhere else, instead of a second hand-rolled select.
  if ($.fn.select2 && !$('#cuCustomer').data('select2')) {
    $('#cuCustomer').select2({
      dropdownParent: $('#custModal'),
      placeholder: 'Search by name or phone',
      minimumInputLength: 1,
      ajax: {
        url: cuSearchUrl, dataType: 'json', delay: 250,
        data: function (p) { return { term: p.term }; },
        processResults: function (d) { return { results: (d && d.results) ? d.results : (d || []) }; }
      }
    });
  }
});
function saveCustody() {
  if (!$('#cuCustomer').val()) { toastr.error('Choose a client.'); return; }
  if (!$.trim($('#cuItem').val())) { toastr.error('Describe the material.'); return; }
  $('#cuSaveBtn').prop('disabled', true).text('Saving…');
  var data = {
    customer_id: $('#cuCustomer').val(), material_name: $('#cuItem').val(),
    qty_received: $('#cuQty').val(), unit_label: $('#cuUnit').val(),
    received_at: $('#cuDate').val(), condition_in: $('#cuCondition').val(),
    notes: $('#cuNotes').val()
  };
  if (window.csrfName) data[window.csrfName] = window.csrfHash;
  $.post('<?= base_url('printing_ops/custody_receive') ?>', data, function (r) {
    $('#cuSaveBtn').prop('disabled', false).text('Receive');
    if (r.status === 'ok') {
      if (r.csrf_hash) window.csrfHash = r.csrf_hash;
      toastr.success(r.message);
      $('#custModal').modal('hide');
      setTimeout(function () { location.reload(); }, 900);
    } else { toastr.error(r.message || 'Could not save.'); }
  }, 'json').fail(function () {
    $('#cuSaveBtn').prop('disabled', false).text('Receive');
    toastr.error('Server error');
  });
}
</script>
<?php endif; ?>
