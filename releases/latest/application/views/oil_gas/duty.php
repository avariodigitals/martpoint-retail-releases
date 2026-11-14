<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $this->load->view('oil_gas/_styles'); ?>
<?php
$who = function($name){ $name = trim((string)$name); if($name === '') return '—'; $parts = preg_split('/\s+/', $name); return $parts[0]; };
$open_meters = 0.0;
foreach($lines as $line) $open_meters += (float)$line->opening_meter;
?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Read the pumps when a duty starts and again when it ends. The litres that came out are compared with what the till rang up.</div>
  </div>
  <div class="mp-form-actions" style="margin:0;">
    <a href="<?= base_url('oil_gas/forecourt?site='.(int)$site_id); ?>" class="mp-btn-secondary"><i class="fa fa-arrow-left"></i> Forecourt</a>
    <a href="<?= base_url('oil_gas/pumps?site='.(int)$site_id); ?>" class="mp-btn-secondary"><i class="fa fa-tachometer"></i> Pumps</a>
  </div>
</div>

<div class="gas-sites">
  <?php foreach($sites as $site): ?>
    <a href="<?= base_url('oil_gas/duty?site='.(int)$site->id); ?>" class="gas-site <?= (int)$site->id === (int)$site_id ? 'active' : ''; ?>"><?= htmlspecialchars($site->warehouse_name); ?></a>
  <?php endforeach; ?>
</div>

<?php if(empty($pumps) && empty($lines)): ?>
<div class="alert alert-warning">
  <strong>Nothing to read yet.</strong>
  This <?= htmlspecialchars(strtolower($site_label)); ?> has no pump registered, so a duty cannot be opened. Add the pumps at this site first.
</div>
<?php endif; ?>

<div class="gas-two">
  <div class="mp-card-form gas-card">
    <div class="mp-card-head">
      <h3><?= $duty ? 'Close this duty' : 'Start a duty' ?></h3>
      <?php if($duty): ?><span class="mp-card-link">opened <?= show_date($duty->opened_at); ?></span><?php endif; ?>
    </div>
    <div class="mp-card-body">
      <?php if(!$can_work): ?>
        <p class="gas-empty">You are not assigned to this <?= htmlspecialchars(strtolower($site_label)); ?>, so you cannot take its readings.</p>
      <?php elseif(!$duty): ?>
        <p class="gas-note" style="margin-top:0;">Opening a duty captures every pump at the litre it stands at right now. Nobody can move those litres into the next duty.</p>
        <table class="loc-table" style="margin-top:12px;">
          <thead><tr><th>Pump</th><th>Holds</th><th>Meter now</th></tr></thead>
          <tbody>
            <?php foreach($pumps as $pump): ?>
            <tr>
              <td><div class="loc-name"><?= htmlspecialchars($pump->pump_name); ?></div><div class="loc-sub"><?= htmlspecialchars((string)$pump->tank_name); ?></div></td>
              <td><?= $pump->item_id ? htmlspecialchars(get_item_name($pump->item_id)) : '—' ?></td>
              <td class="gas-qty"><?= mp_format_litres(isset($meters[(int)$pump->id]) ? $meters[(int)$pump->id] : $pump->opening_meter); ?> L</td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?= form_open(base_url('oil_gas/save_duty')); ?>
        <input type="hidden" name="action" value="open">
        <input type="hidden" name="site_id" value="<?= (int)$site_id; ?>">
        <div class="mp-form-actions" style="margin-top:16px;">
          <button type="submit" class="mp-btn-primary"><i class="fa fa-play-circle"></i> Open the duty</button>
        </div>
        <?= form_close(); ?>
      <?php else: ?>
        <?= form_open(base_url('oil_gas/save_duty')); ?>
        <input type="hidden" name="action" value="close">
        <input type="hidden" name="site_id" value="<?= (int)$site_id; ?>">
        <input type="hidden" name="shift_id" value="<?= (int)$duty->id; ?>">
        <div class="mp-dt-scroll">
          <table class="loc-table">
            <thead><tr><th>Pump</th><th>Opened at</th><th>Closing reading <span class="text-danger">*</span></th><th>Litres this duty</th></tr></thead>
            <tbody>
              <?php foreach($lines as $line): ?>
              <tr class="gas-meter-row" data-opening="<?= (float)$line->opening_meter; ?>">
                <td><div class="loc-name"><?= htmlspecialchars($line->pump_name); ?></div><div class="loc-sub"><?= $line->item_id ? htmlspecialchars(get_item_name($line->item_id)) : '' ?></div></td>
                <td class="gas-qty"><?= mp_format_litres($line->opening_meter); ?> L</td>
                <td>
                  <input class="mp-form-control gas-reading meter-input" type="number" step="0.001" min="<?= (float)$line->opening_meter; ?>"
                         name="meter[<?= (int)$line->pump_id; ?>]" value="<?= $line->closing_meter !== null ? mp_format_litres($line->closing_meter) : ''; ?>"
                         placeholder="Read the face" data-opening="<?= (float)$line->opening_meter; ?>">
                </td>
                <td class="gas-qty"><span class="pump-litres">&mdash;</span> L</td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="mp-form-group full" style="margin-top:16px;">
          <label for="close_note">Handover note</label>
          <textarea class="mp-form-control" id="close_note" name="close_note" rows="2" placeholder="Pump 3 was still dripping after the last customer."></textarea>
        </div>
        <div class="mp-form-actions" style="margin-top:16px;">
          <button type="submit" class="mp-btn-primary"><i class="fa fa-check-circle"></i> Close and reconcile</button>
        </div>
        <p class="gas-note" id="gap_note" data-till="<?= (float)$till_so_far; ?>" data-tolerance="<?= (float)$tolerance; ?>">
          The till has rang up <strong><?= mp_format_litres($till_so_far); ?> L</strong> of these products since the duty opened.
          A gap of more than <?= mp_format_percent($tolerance); ?> of what the pumps measured goes to a manager before the duty is final.
        </p>
        <?= form_close(); ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="mp-card-form gas-card">
    <div class="mp-card-head"><h3>How the last duties settled</h3></div>
    <div class="mp-card-body" style="padding:0!important">
      <?php if(empty($history)): ?>
        <p class="gas-empty">No duty has been closed at this <?= htmlspecialchars(strtolower($site_label)); ?> yet.</p>
      <?php else: ?>
      <table class="loc-table">
        <thead><tr><th>Duty</th><th>Measured</th><th>Till</th><th>Gap</th></tr></thead>
        <tbody>
          <?php foreach($history as $past): ?>
          <tr>
            <td>
              <div class="loc-name"><?= htmlspecialchars($past->shift_code ?: 'Duty'); ?></div>
              <div class="loc-sub"><?= show_date($past->opened_at); ?> · <?= htmlspecialchars($who($past->opened_by_name)); ?></div>
            </td>
            <td class="gas-qty"><?= mp_format_litres($past->litres_metered); ?> L</td>
            <td class="gas-qty"><?= mp_format_litres($past->litres_till); ?> L</td>
            <td class="gas-qty">
              <?= mp_format_litres($past->variance_litres); ?> L
              <div class="loc-sub">
                <?php if($past->status === 'open'): ?>running
                <?php elseif($past->approval_status === 'pending'): ?><span class="text-danger">waiting for a manager</span>
                <?php elseif($past->approval_status === 'approved'): ?>accepted by <?= htmlspecialchars($who($past->closed_by_name)); ?>
                <?php elseif($past->approval_status === 'rejected'): ?><span class="text-danger">sent back</span>
                <?php else: ?>inside tolerance<?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
(function(){
  function num(v){ v = (v || '').toString().replace(/,/g, '').trim(); return v === '' ? NaN : parseFloat(v); }
  function fmt(v){ return v.toLocaleString(undefined, {maximumFractionDigits: 3}); }
  function recompute(){
    var total = 0;
    document.querySelectorAll('.gas-meter-row').forEach(function(tr){
      var input = tr.querySelector('.meter-input');
      var cell = tr.querySelector('.pump-litres');
      var opening = parseFloat(tr.getAttribute('data-opening')) || 0;
      var val = num(input.value);
      if(isNaN(val)){ cell.textContent = '—'; input.classList.remove('mp-invalid'); return; }
      var litres = val - opening;
      if(litres < -0.0001){
        cell.innerHTML = '<span class="text-danger">below the opening reading</span>';
        total += 0;
      } else {
        cell.textContent = fmt(litres);
        total += litres;
      }
    });
    var note = document.getElementById('gap_note');
    if(note){
      var till = parseFloat(note.getAttribute('data-till')) || 0;
      var gap = total - till;
      var tol = parseFloat(note.getAttribute('data-tolerance')) || 0;
      var pct = total > 0 ? Math.abs(gap) / total * 100 : 0;
      note.textContent = 'The pumps will show ' + fmt(total) + ' L against ' + fmt(till) + ' L rang up — a gap of '
        + fmt(gap) + ' L (' + pct.toFixed(2) + '%)'
        + (total > 0 && pct > tol ? ', so a manager will have to accept this duty.' : ', inside the ' + tol + '% you allow.');
      note.className = (total > 0 && pct > tol) ? 'gas-note text-danger' : 'gas-note';
    }
  }
  document.addEventListener('input', recompute);
  recompute();
})();
</script>
<script>
  $(function(){ $('.oil_gas-duty-active-li').addClass('active'); });
</script>
