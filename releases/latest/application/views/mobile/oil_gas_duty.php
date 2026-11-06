<?php
$who = function($name){ $name = trim((string)$name); if($name === '') return '—'; $parts = preg_split('/\s+/', $name); return $parts[0]; };
$this->load->view('mobile/gas_head', [
	'gas_title' => 'Duty Readings',
	'gas_sub' => 'Read the pumps at the start and the end of a duty, then settle it against the till',
	'store_name' => $store_name ?? ($SITE_TITLE ?? 'MartPoint'),
	'theme_link' => $theme_link,
]);
$this->load->view('mobile/gas_sites', ['sites'=>$sites, 'site_id'=>$site_id, 'gas_route'=>'duty']);
?>

<?php if(!$can_work): ?>
  <div class='callout info'>You are not assigned to this <?= htmlspecialchars(strtolower($site_label)); ?>, so you cannot take its readings.</div>
<?php endif; ?>

<?php if($duty): ?>
<div class='card'>
  <div class='section-title'>Close the open duty</div>
  <p class='note' style='margin-top:0;'>Opened <?= show_date($duty->opened_at); ?> by <?= htmlspecialchars($who($duty->opened_by_name)); ?>. Enter each meter exactly as the face shows it now.</p>
  <?= form_open(base_url('oil_gas/save_duty')); ?>
  <input type='hidden' name='action' value='close'>
  <input type='hidden' name='site_id' value='<?= (int)$site_id; ?>'>
  <input type='hidden' name='shift_id' value='<?= (int)$duty->id; ?>'>
  <?php foreach($lines as $line): ?>
  <div class='meter'>
    <div>
      <div class='pump'><?= htmlspecialchars($line->pump_name); ?></div>
      <div class='sub'>opened at <?= mp_format_litres($line->opening_meter); ?> L<?= $line->item_id ? ' · ' . htmlspecialchars(get_item_name($line->item_id)) : ''; ?></div>
    </div>
    <input type='number' step='0.001' min='0' name='meter[<?= (int)$line->pump_id; ?>]' data-opening='<?= (float)$line->opening_meter; ?>'
           value='<?= $line->closing_meter !== null ? mp_format_litres($line->closing_meter) : ''; ?>' required>
  </div>
  <?php endforeach; ?>
  <div class='form-group' style='margin-top:14px;'>
    <label>Handover note</label>
    <textarea name='close_note' rows='2' placeholder='Pump 3 was still dripping after the last customer.'></textarea>
  </div>
  <div class='callout info' id='gap_note' data-till='<?= (float)$till_so_far; ?>' data-tolerance='<?= (float)$tolerance; ?>'>
    The till has rang up <strong><?= mp_format_litres($till_so_far); ?> L</strong> of these products since the duty opened.
  </div>
  <button type='submit' class='btn btn-primary'><i class='fa fa-check-circle'></i> Close and reconcile</button>
  <?= form_close(); ?>
</div>
<?php else: ?>
<div class='card'>
  <div class='section-title'>Start a duty</div>
  <?php if(empty($pumps)): ?>
    <div class='empty'><i class='fa fa-tachometer'></i>Nothing to read yet. Register the pumps at this <?= htmlspecialchars(strtolower($site_label)); ?> first.</div>
  <?php elseif(!$can_work): ?>
    <p class='note' style='margin-top:0;'>Readings cannot be taken here by you.</p>
  <?php else: ?>
    <p class='note' style='margin-top:0;'>Opening a duty captures every pump at the litre it stands at right now, so nobody can move those litres into the next duty.</p>
    <div style='margin-top:10px;'>
      <?php foreach($pumps as $pump): ?>
      <div class='meter'>
        <div>
          <div class='pump'><?= htmlspecialchars($pump->pump_name); ?></div>
          <div class='sub'><?= htmlspecialchars((string)$pump->tank_name); ?></div>
        </div>
        <div class='litre'><?= mp_format_litres(isset($meters[(int)$pump->id]) ? $meters[(int)$pump->id] : $pump->opening_meter); ?> L</div>
      </div>
      <?php endforeach; ?>
    </div>
    <?= form_open(base_url('oil_gas/save_duty')); ?>
    <input type='hidden' name='action' value='open'>
    <input type='hidden' name='site_id' value='<?= (int)$site_id; ?>'>
    <button type='submit' class='btn btn-primary' style='margin-top:12px;'><i class='fa fa-play-circle'></i> Open the duty</button>
    <?= form_close(); ?>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class='card'>
  <div class='section-title'>How the last duties settled</div>
  <?php if(empty($history)): ?>
    <div class='empty'><i class='fa fa-exchange'></i>No duty has been closed at this <?= htmlspecialchars(strtolower($site_label)); ?> yet.</div>
  <?php else: foreach($history as $past): ?>
    <div class='row'>
      <div class='name'>Duty of <?= show_date($past->opened_at); ?></div>
      <div class='meta'>
        <?= htmlspecialchars($who($past->opened_by_name)); ?> · pumps <span class='litre'><?= mp_format_litres($past->litres_metered); ?></span> L · till <span class='litre'><?= mp_format_litres($past->litres_till); ?></span> L
        · gap <span class='litre'><?= mp_format_litres($past->variance_litres); ?></span> L
      </div>
      <?php if($past->status === 'open'): ?><span class='flag open'>running</span>
      <?php elseif($past->approval_status === 'pending'): ?><span class='flag held'>waiting for a manager</span>
      <?php elseif($past->approval_status === 'approved'): ?><span class='flag transit'>signed off</span>
      <?php elseif($past->approval_status === 'rejected'): ?><span class='flag held'>sent back</span>
      <?php else: ?><span class='flag ok'>inside tolerance</span><?php endif; ?>
    </div>
  <?php endforeach; endif; ?>
</div>

<script>
(function(){
  function num(v){ v = (v || '').toString().replace(/,/g,'').trim(); return v === '' ? NaN : parseFloat(v); }
  function fmt(v){ return v.toLocaleString(undefined, {maximumFractionDigits:3}); }
  function recompute(){
    var total = 0, any = false;
    document.querySelectorAll('.meter input[data-opening]').forEach(function(input){
      var opening = parseFloat(input.getAttribute('data-opening')) || 0;
      var val = num(input.value);
      var row = input.parentNode;
      var warn = row.querySelector('.below');
      if(isNaN(val)){ if(warn) warn.remove(); return; }
      var litres = val - opening;
      any = true;
      if(litres < -0.0001){
        if(!warn){ warn = document.createElement('div'); warn.className = 'sub below'; warn.style.color = '#991B1B'; warn.textContent = 'below the opening reading'; row.insertBefore(warn, input); }
      } else if(warn){ warn.remove(); }
      var line = row.querySelector('.litre-out');
      if(!line){ line = document.createElement('div'); line.className = 'sub litre-out'; row.insertBefore(line, input); }
      line.textContent = litres < -0.0001 ? '' : fmt(litres) + ' L this duty';
      if(litres > 0) total += litres;
    });
    var note = document.getElementById('gap_note');
    if(!note || !any) return;
    var till = parseFloat(note.getAttribute('data-till')) || 0;
    var tol = parseFloat(note.getAttribute('data-tolerance')) || 0;
    var gap = total - till;
    var pct = total > 0 ? Math.abs(gap) / total * 100 : 0;
    note.innerHTML = 'The pumps will show <strong>' + fmt(total) + ' L</strong> against ' + fmt(till) + ' L rang up — a gap of '
      + fmt(gap) + ' L (' + pct.toFixed(2) + '%)'
      + (total > 0 && pct > tol ? ', so a manager will have to accept this duty.' : ', inside the ' + tol + '% you allow.');
  }
  document.addEventListener('input', recompute);
  recompute();
})();
</script>

<?php $this->load->view('mobile/gas_foot', ['gas_nav'=>'more']); ?>
