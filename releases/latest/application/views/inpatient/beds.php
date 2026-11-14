<?php $this->load->view('admin/desktop/_styles'); $this->load->view('components/dialog'); $CI =& get_instance(); ?>
<style>
.ib-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:14px!important}
.ib-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 10px 0!important}
.ib-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px}
.ib-bed{border:1px solid var(--mp-border);border-radius:12px;padding:12px;text-align:center;font-size:12px}
.ib-bed .lbl{font-weight:800;font-size:14px}
.ib-bed .st{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;margin-top:4px}
.ib-available{border-color:#059669;background:#ECFDF5;color:#065F46}
.ib-occupied{border-color:#2563EB;background:#EFF6FF;color:#1E40AF}
.ib-held{border-color:#D97706;background:#FFFBEB;color:#92400E}
.ib-maintenance{border-color:#6B7280;background:#F9FAFB;color:#6B7280}
.ib-inp{padding:6px 9px!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;font-size:12px!important;margin-right:6px!important;background:var(--mp-bg,#fff)!important;color:inherit!important}
.ib-addbed{display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end}
.ib-field label{display:block;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--mp-muted);margin:0 0 5px}
.ib-field label span{font-weight:400;text-transform:none;letter-spacing:0}
.ib-rate-preview{margin-top:12px;font-size:12px;font-weight:700;color:#065F46;background:#ECFDF5;border:1px solid #A7F3D0;border-radius:10px;padding:8px 12px}
.ib-rate-preview.warn{color:#92400E;background:#FFFBEB;border-color:#FDE68A}
.ib-editwrap{position:fixed;inset:0;z-index:2000;display:flex;align-items:center;justify-content:center}
.ib-edit-back{position:absolute;inset:0;background:rgba(15,23,42,.45)}
.ib-edit-card{position:relative;background:var(--mp-surface,#fff);border:1px solid var(--mp-border);border-radius:16px;padding:20px;width:min(440px,92vw);box-shadow:0 20px 50px rgba(15,23,42,.25)}
.ib-edit-card h4{font-size:13px;font-weight:800;margin:0 0 14px}
.ib-edit-card .ib-field{margin-bottom:12px}
.ib-edit-card .ib-field label{display:block;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--mp-muted);margin:0 0 5px}
.ib-edit-card .ib-field label span{font-weight:400;text-transform:none;letter-spacing:0}
</style>

<div class="mp-page-head">
  <div><h2>Bed Board</h2>
  <div class="mp-page-sub">Ward occupancy, patient admissions and bed accounts.</div></div>
  <div><a class="mp-qa-btn" href="<?= base_url('inpatient'); ?>">Admissions</a></div>
</div>

<div class="ib-box">
  <h4>How a bed is organised and priced</h4>
  <div style="font-size:12px;line-height:1.7;color:inherit">
    <strong>Ward</strong> = a physical area or wing (e.g. “Maternity”). <strong>Room</strong> = a room inside a ward (e.g. “Room 12”), and it belongs to a <strong>category</strong> (e.g. “Private — AC”). <strong>Bed</strong> = the actual bed a patient sleeps in, inside a room (or loose in a ward).<br>
    The price lives on the <strong>category</strong>, not the bed. A patient is billed each day at their room’s category rate. One room can hold several beds (set its capacity) — they all share that rate. A bed can carry its own rate only as an exception.
  </div>
</div>

<?php
$byWard = array();
foreach($beds as $b){ $byWard[$b->ward_id][] = $b; }
$roomsByWard = array();
foreach($rooms as $rm){ $roomsByWard[$rm->ward_id][] = $rm; }
foreach($wards as $w): ?>
<div class="ib-box">
  <h4><?= htmlspecialchars($w->name); ?></h4>
  <div class="ib-grid">
    <?php foreach(($byWard[$w->id] ?? array()) as $b): ?>
    <div class="ib-bed ib-<?= htmlspecialchars($b->status); ?>">
      <div class="lbl"><?= htmlspecialchars($b->bed_label); ?></div>
      <div class="st"><?= htmlspecialchars($b->status); ?></div>
      <?php if($b->room_label): ?>
        <div style="margin-top:6px;font-size:10px;font-weight:700"><?= htmlspecialchars($b->room_label); ?><?php if($b->room_class_name): ?> · <?= htmlspecialchars($b->room_class_name); ?><?php endif; ?></div>
      <?php else: ?>
        <div style="margin-top:6px;font-size:10px;font-weight:700;color:#B45309">Open-ward bed (not in a room)</div>
      <?php endif; ?>
      <?php if($b->patient_name): ?><div style="margin-top:4px"><?= htmlspecialchars($b->patient_name); ?><br><small>ADM #<?= (int)$b->admission_id; ?></small></div><?php endif; ?>
      <?php if($b->eff_rate !== null): ?>
        <div style="margin-top:6px;font-size:11px;font-weight:800;color:var(--mp-muted)"><?= $CI->currency($b->eff_rate); ?>/day</div>
        <div style="font-size:10px;color:var(--mp-muted)"><?= $b->rate_source === 'own' ? 'own rate (overrides category)' : htmlspecialchars($b->rate_label); ?></div>
      <?php else: ?>
        <div style="margin-top:6px;font-size:10px;color:#B45309">Not priced yet — set a category rate in Billing Setup</div>
      <?php endif; ?>
      <?php if($can['manage']): ?>
        <button class="mp-qa-btn" style="margin-top:8px;width:100%" onclick="ibEditBed(<?= (int)$b->id; ?>)">Edit bed</button>
      <?php endif; ?>
      <?php if($b->admission_id && $can['open']): ?><a style="display:block;margin-top:8px" href="<?= base_url('inpatient/view/'.(int)$b->admission_id); ?>">Open admission</a><?php endif; ?>
      <?php if($can['finance'] && $b->payment_account_id): ?><a style="display:block;margin-top:8px" href="<?= base_url('inpatient/bed_ledger/'.(int)$b->payment_account_id); ?>">Bed account</a><?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php if(empty($byWard[$w->id])): ?><div style="color:var(--mp-muted);font-size:12px">No beds in this ward.</div><?php endif; ?>
  </div>
</div>
<?php endforeach; ?>
<?php if(empty($wards)): ?><div class="ib-box" style="color:var(--mp-muted)">No wards configured yet.</div><?php endif; ?>

<?php if($can['manage']): ?>
<div class="ib-box">
  <h4>Add ward</h4>
  <form style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap" onsubmit="return ibPost(event,'inpatient/save_ward','Ward saved')">
    <input type="hidden" name="<?= htmlspecialchars($CI->security->get_csrf_token_name(), ENT_QUOTES, 'UTF-8'); ?>" value="<?= htmlspecialchars($CI->security->get_csrf_hash(), ENT_QUOTES, 'UTF-8'); ?>">
    <div class="ib-field"><label>Ward (physical area)</label><input class="ib-inp" name="name" placeholder="e.g. Maternity Wing" required></div>
    <button class="mp-qa-btn">Add ward</button>
  </form>
</div>

<div class="ib-box">
  <h4>Add bed</h4>
  <div style="font-size:11px;color:var(--mp-muted);margin:0 0 12px">Pick the room the bed belongs to &mdash; the ward and the daily rate come from that room&rsquo;s category. Choose &ldquo;Open-ward bed&rdquo; only for a bed that isn&rsquo;t in a room.</div>
  <form onsubmit="return ibPost(event,'inpatient/save_bed','Bed saved')">
    <input type="hidden" name="<?= htmlspecialchars($CI->security->get_csrf_token_name(), ENT_QUOTES, 'UTF-8'); ?>" value="<?= htmlspecialchars($CI->security->get_csrf_hash(), ENT_QUOTES, 'UTF-8'); ?>">
    <div class="ib-addbed">
      <div class="ib-field" style="flex:2 1 260px">
        <label>Room (sets the category &amp; rate)</label>
        <select class="ib-inp" name="room_id" id="ib_room_select" onchange="ibRoomChange(this)" style="width:100%">
          <option value="" data-ward="" data-class="" data-ratelabel="">&mdash; Open-ward bed (not in a room) &mdash;</option>
          <?php foreach($wards as $w): ?>
            <?php if(empty($roomsByWard[$w->id])) continue; ?>
            <optgroup label="<?= htmlspecialchars($w->name); ?>">
              <?php foreach($roomsByWard[$w->id] as $rm): ?>
                <option value="<?= (int)$rm->id; ?>" data-ward="<?= (int)$rm->ward_id; ?>" data-class="<?= htmlspecialchars($rm->class_name); ?>" data-ratelabel="<?= $rm->eff_rate !== null ? $CI->currency($rm->eff_rate) : ''; ?>"><?= htmlspecialchars($rm->room_label.' ('.$rm->class_name.')'); ?> &mdash; <?= $rm->eff_rate !== null ? $CI->currency($rm->eff_rate).'/day' : 'not priced'; ?></option>
              <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="ib-field" id="ib_ward_wrap" style="flex:1 1 160px">
        <label>Ward <span>(only for an open-ward bed)</span></label>
        <select class="ib-inp" name="ward_id" id="ib_ward_select" style="width:100%">
          <?php foreach($wards as $w): ?><option value="<?= (int)$w->id; ?>"><?= htmlspecialchars($w->name); ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="ib-field" style="flex:1 1 140px">
        <label>Bed label</label>
        <input class="ib-inp" name="label" placeholder="e.g. Bed 12-A" required style="width:100%">
      </div>
      <div class="ib-field" style="flex:1 1 170px">
        <label>Own rate <span>(optional)</span></label>
        <input class="ib-inp" name="daily_rate" placeholder="Leave blank to use the category rate" style="width:100%">
      </div>
      <button class="mp-qa-btn green">Add bed</button>
    </div>
    <div id="ib_rate_preview" class="ib-rate-preview<?= $fallback_rate !== null ? '' : ' warn'; ?>">Open-ward bed &mdash; bills at the fallback bed rate<?= $fallback_rate !== null ? ' ('.$CI->currency($fallback_rate).'/day)' : ' (not set yet in Billing Setup)'; ?>.</div>
  </form>
</div>

<div class="ib-editwrap" id="ib_edit_wrap" style="display:none">
  <div class="ib-edit-back" onclick="ibEditClose()"></div>
  <div class="ib-edit-card">
    <h4>Edit bed</h4>
    <input type="hidden" id="ib_edit_id">
    <div class="ib-field"><label>Bed label</label><input class="ib-inp" id="ib_edit_label" placeholder="e.g. Bed 12-A" style="width:100%"></div>
    <div class="ib-field"><label>Room (sets the category &amp; rate)</label>
      <select class="ib-inp" id="ib_edit_room" onchange="ibEditRoomChange(this)" style="width:100%">
        <option value="">&mdash; Open-ward bed (not in a room) &mdash;</option>
        <?php foreach($wards as $w): ?>
          <?php if(empty($roomsByWard[$w->id])) continue; ?>
          <optgroup label="<?= htmlspecialchars($w->name); ?>">
            <?php foreach($roomsByWard[$w->id] as $rm): ?>
              <option value="<?= (int)$rm->id; ?>" data-class="<?= htmlspecialchars($rm->class_name); ?>" data-ratelabel="<?= $rm->eff_rate !== null ? $CI->currency($rm->eff_rate) : ''; ?>"><?= htmlspecialchars($rm->room_label.' ('.$rm->class_name.')'); ?> &mdash; <?= $rm->eff_rate !== null ? $CI->currency($rm->eff_rate).'/day' : 'not priced'; ?></option>
            <?php endforeach; ?>
          </optgroup>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="ib-field"><label>Own rate <span>(leave blank to use the category rate)</span></label>
      <input class="ib-inp" id="ib_edit_rate" placeholder="Blank = category rate" style="width:100%"></div>
    <div id="ib_edit_preview" class="ib-rate-preview"></div>
    <div style="display:flex;gap:8px;margin-top:14px">
      <button class="mp-qa-btn" onclick="ibEditClose()">Cancel</button>
      <button class="mp-qa-btn green" onclick="ibEditSave()">Save changes</button>
    </div>
  </div>
</div>
<script>
var BEDS = {};
<?php foreach($beds as $b): ?>
BEDS[<?= (int)$b->id; ?>] = {label: <?= json_encode((string)$b->bed_label); ?>, room_id: <?= (int)($b->room_id ?: 0); ?>, daily_rate: <?= $b->daily_rate !== null ? json_encode((string)$b->daily_rate) : 'null'; ?>};
<?php endforeach; ?>
function ibEditBed(id){
  var b = BEDS[id]; if(!b) return;
  document.getElementById('ib_edit_id').value = id;
  document.getElementById('ib_edit_label').value = b.label;
  document.getElementById('ib_edit_room').value = b.room_id ? String(b.room_id) : '';
  document.getElementById('ib_edit_rate').value = (b.daily_rate === null || b.daily_rate === '') ? '' : b.daily_rate;
  ibEditRoomChange(document.getElementById('ib_edit_room'));
  document.getElementById('ib_edit_wrap').style.display = 'flex';
}
function ibEditClose(){ document.getElementById('ib_edit_wrap').style.display = 'none'; }
function ibEditRoomChange(sel){
  var opt = sel.options[sel.selectedIndex];
  var rate = (opt.getAttribute('data-ratelabel') || '').trim();
  var cls = opt.getAttribute('data-class') || '';
  var prev = document.getElementById('ib_edit_preview');
  var own = document.getElementById('ib_edit_rate').value.trim();
  if(own){
    prev.className = 'ib-rate-preview warn';
    prev.textContent = 'This bed uses its own rate (' + own + '/day) and ignores the category. Clear the rate box to go back to the category price.';
    return;
  }
  if(sel.value){
    prev.className = 'ib-rate-preview' + (rate ? '' : ' warn');
    prev.textContent = rate ? 'Bills at the category rate: ' + rate + '/day (' + cls + ').' : 'This room’s category is not priced yet — set it in Billing Setup.';
  } else {
    prev.className = 'ib-rate-preview' + (<?= $fallback_rate !== null ? 'true' : 'false'; ?> ? '' : ' warn');
    prev.textContent = <?= $fallback_rate !== null ? json_encode('Open-ward bed — bills at the fallback rate ('.$CI->currency($fallback_rate).'/day).') : json_encode('Open-ward bed — no fallback bed rate set yet in Billing Setup.'); ?>;
  }
}
function ibEditSave(){
  var fd = new FormData();
  fd.append('bed_id', document.getElementById('ib_edit_id').value);
  fd.append('label', document.getElementById('ib_edit_label').value);
  fd.append('room_id', document.getElementById('ib_edit_room').value);
  fd.append('daily_rate', document.getElementById('ib_edit_rate').value);
  fd.append('<?= htmlspecialchars($CI->security->get_csrf_token_name(), ENT_QUOTES, 'UTF-8'); ?>', '<?= htmlspecialchars($CI->security->get_csrf_hash(), ENT_QUOTES, 'UTF-8'); ?>');
  fetch('<?= base_url('inpatient/update_bed'); ?>', {method:'POST', body:fd})
    .then(function(r){ return r.json(); })
    .then(function(d){ ibToast(d.message || 'Saved', d.status === 'success' ? 'success' : 'error'); if(d.status === 'success') setTimeout(function(){ location.reload(); }, 500); })
    .catch(function(){ ibToast('Could not save. Try again.', 'error'); });
}
function ibToast(m, t){ if(window.cqToast) window.cqToast(m, t || 'info'); else if(window.console) console.log(m); }
function ibRoomChange(sel){
  var opt = sel.options[sel.selectedIndex];
  var wardSel = document.getElementById('ib_ward_select');
  var wardWrap = document.getElementById('ib_ward_wrap');
  var prev = document.getElementById('ib_rate_preview');
  var rate = (opt.getAttribute('data-ratelabel') || '').trim();
  var cls = opt.getAttribute('data-class') || '';
  if(sel.value){
    if(wardSel && opt.getAttribute('data-ward')) wardSel.value = opt.getAttribute('data-ward');
    if(wardWrap) wardWrap.style.display = 'none';
    prev.className = 'ib-rate-preview' + (rate ? '' : ' warn');
    prev.textContent = rate
      ? 'This bed bills at ' + rate + '/day — from category “' + cls + '”.'
      : 'This room’s category is not priced yet — set its rate in Inpatient Billing Setup.';
  } else {
    if(wardWrap) wardWrap.style.display = '';
    var fb = <?= $fallback_rate !== null ? json_encode($CI->currency($fallback_rate)) : 'null'; ?>;
    prev.className = 'ib-rate-preview' + (fb ? '' : ' warn');
    prev.textContent = fb
      ? 'Open-ward bed — bills at the fallback bed rate (' + fb + '/day).'
      : 'Open-ward bed — no fallback bed rate set yet in Billing Setup.';
  }
}
function ibPost(e, url, ok){
  e.preventDefault();
  var form=e.target, fd=new FormData(form), button=form.querySelector('button');
  if(button && button.disabled) return false;
  if(button) button.disabled=true;
  fetch('<?= base_url(); ?>'+url, {method:'POST', body:fd})
    .then(function(r){
      if(!r.ok) throw new Error(r.status===403 ? 'Your session could not be verified. Refresh this page and try again.' : 'Could not save. Please try again.');
      return r.json();
    })
    .then(function(d){
      if(d.status==='success'){ ibToast(d.message||ok, 'success'); location.reload(); }
      else { ibToast(d.message||'Could not save. Please check the details and try again.', 'error'); }
    })
    .catch(function(err){ ibToast(err.message||'Could not save. Please try again.', 'error'); })
    .finally(function(){ if(button) button.disabled=false; });
  return false;
}
</script>
<?php endif; ?>
