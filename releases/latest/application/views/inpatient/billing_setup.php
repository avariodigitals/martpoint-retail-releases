<?php $this->load->view('admin/desktop/_styles'); $this->load->view('components/dialog'); $CI =& get_instance(); ?>
<style>
.bs-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:16px!important;margin-bottom:16px!important}
.bs-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 4px 0!important}
.bs-lead{color:var(--mp-muted);font-size:12px;margin:0 0 12px 0;line-height:1.6}
.bs-table{width:100%!important;border-collapse:collapse!important;font-size:12px!important}
.bs-table th{text-align:left!important;padding:6px 8px!important;border-bottom:1px solid var(--mp-border)!important;font-size:10px!important;text-transform:uppercase!important;color:var(--mp-muted)!important}
.bs-table td{padding:8px!important;border-bottom:1px solid #F1F5F9!important;vertical-align:middle!important}
.bs-table tr:last-child td{border-bottom:none!important}
.bs-inp{padding:7px 10px!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;font-size:12px!important;margin-right:6px!important;background:var(--mp-bg,#fff)!important;color:inherit!important}
.bs-current{background:#D1FAE5;color:#065F46;padding:2px 8px;border-radius:8px;font-size:10px;font-weight:800;white-space:nowrap}
.bs-muted{color:var(--mp-muted)}
.bs-rate{font-weight:700;white-space:nowrap}
.bs-form{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-top:12px;padding-top:12px;border-top:1px dashed var(--mp-border)}
.bs-form label{font-size:11px;color:var(--mp-muted);display:block;margin-bottom:3px;font-weight:700;text-transform:uppercase;letter-spacing:.4px}
.bs-form .bs-field{display:flex;flex-direction:column}
.bs-actions{display:flex;gap:8px;flex-wrap:wrap}
.bs-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px}
.bs-note{background:#FFFBEB;border:1px solid #FDE68A;color:#92400E;border-radius:10px;padding:10px 12px;font-size:12px;line-height:1.6;margin-top:12px}
</style>

<div class="mp-page-head">
  <div><h2>Inpatient Billing Setup</h2>
  <div class="mp-page-sub">Room categories carry the price. Rates apply by effective date — a new version never rewrites history.</div></div>
  <?php if($can['run']): ?><div class="bs-actions">
    <button class="mp-qa-btn" onclick="bsRun()">Run today's billing</button>
  </div><?php endif; ?>
</div>

<div class="bs-box">
  <h4>How a patient's room is priced</h4>
  <p class="bs-lead">A <strong>room category</strong> holds the daily price (Private &mdash; AC, Semi-Private, General Ward&hellip;). Every <strong>room</strong> belongs to a category, and every <strong>bed</strong> sits in a room. When billing runs, each patient-day is charged at the price of the category their room belongs to. A bed can still carry its own price for exceptions. <strong>No tax is added</strong> &mdash; medical services are treated as VAT-exempt, so there is no tax field on these bills.</p>
  <?php if(empty($room_classes)): ?>
    <div class="bs-note">No room categories yet. Start with the standard set, then edit the names and prices to suit this facility.
      <?php if($can['run']): ?> <button class="mp-qa-btn green" style="margin-left:8px" onclick="bsSeedClasses()">Add the standard categories</button><?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<div class="bs-box">
  <h4>Room categories &amp; daily rates</h4>
  <table class="bs-table">
    <tr><th>Category</th><th>Code</th><th>Current daily rate</th><th>In use by</th><th>Effective from</th><th></th></tr>
    <?php
      $classRates = array(); $facilityBed = null;
      foreach($rates as $r){
        if($r->rate_code !== 'bed') continue;
        if($r->room_class_id) $classRates[$r->room_class_id][] = $r;
        elseif($r->effective_to === null) $facilityBed = $r;
      }
      $roomCount = array();
      foreach($rooms as $rm) $roomCount[$rm->room_class_id] = ($roomCount[$rm->room_class_id] ?? 0) + 1;
      $curRateByClass = array();
      foreach($classRates as $cid => $rows){ foreach($rows as $rr){ if($rr->effective_to === null){ $curRateByClass[$cid] = $rr->amount; break; } } }
    ?>
    <?php foreach($room_classes as $c):
      $rows = $classRates[$c->id] ?? array();
      $cur = null; foreach($rows as $rr){ if($rr->effective_to === null){ $cur = $rr; break; } }
    ?>
    <tr>
      <td><strong><?= htmlspecialchars($c->class_name); ?></strong>
          <?php if($c->description): ?><div class="bs-muted" style="font-size:11px"><?= htmlspecialchars($c->description); ?></div><?php endif; ?></td>
      <td><code><?= htmlspecialchars($c->class_code); ?></code></td>
      <td class="bs-rate">
        <?php if($cur): ?>
          <?= $CI->currency($cur->amount); ?> <span class="bs-current">current</span>
        <?php else: ?>
          <span class="bs-muted">not priced — falls back to <?= $facilityBed ? $CI->currency($facilityBed->amount) : 'the bed rate' ?></span>
        <?php endif; ?>
      </td>
      <td><?= (int)($roomCount[$c->id] ?? 0); ?> room(s)</td>
      <td><?= htmlspecialchars($cur->effective_from ?? '—'); ?></td>
      <td>
        <?php if($can['run'] || $can['rooms']): ?><button class="mp-qa-btn" onclick="bsClassEdit(<?= (int)$c->id; ?>)">Edit</button><?php endif; ?>
        <?php if($can['run']): ?><button class="mp-qa-btn green" onclick="bsRateForm(<?= (int)$c->id; ?>, '<?= htmlspecialchars(addslashes($c->class_name), ENT_QUOTES); ?>')">Set rate</button><?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($room_classes)): ?><tr><td colspan="6" class="bs-muted">No room categories yet.</td></tr><?php endif; ?>
    <tr>
      <td><strong>All beds (fallback)</strong><div class="bs-muted" style="font-size:11px">Used for any bed whose room is not priced</div></td>
      <td><code>bed</code></td>
      <td class="bs-rate"><?= $facilityBed ? $CI->currency($facilityBed->amount).' <span class="bs-current">current</span>' : '<span class="bs-muted">not set</span>'; ?></td>
      <td class="bs-muted">fallback</td>
      <td><?= htmlspecialchars($facilityBed->effective_from ?? '—'); ?></td>
      <td><?php if($can['run']): ?><button class="mp-qa-btn green" onclick="bsRateForm('', 'All beds (fallback)')">Set rate</button><?php endif; ?></td>
    </tr>
  </table>

  <?php if($can['run']): ?>
  <div class="bs-form">
    <div class="bs-field">
      <label>Room category</label>
      <select class="bs-inp" name="room_class_id" form="bsRateFormEl" onchange="bsRateCodeHint(this.value)">
        <option value="">All beds (fallback)<?= $facilityBed ? ' — '.$CI->currency($facilityBed->amount).'/day' : ' — not set'; ?></option>
        <?php foreach($room_classes as $c): ?><option value="<?= (int)$c->id; ?>"><?= htmlspecialchars($c->class_name); ?><?= isset($curRateByClass[$c->id]) ? ' — '.$CI->currency($curRateByClass[$c->id]).'/day' : ' — not priced'; ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="bs-field">
      <label>Charge type</label>
      <select class="bs-inp" name="rate_code" form="bsRateFormEl">
        <option value="bed">Room / bed</option>
        <option value="nursing">Nursing care (per day)</option>
        <option value="leave">Leave</option>
        <option value="other">Other</option>
      </select>
    </div>
    <div class="bs-field"><label>Name</label><input class="bs-inp" name="name" form="bsRateFormEl" placeholder="e.g. Private AC daily"></div>
    <div class="bs-field"><label>Amount</label><input class="bs-inp" name="amount" form="bsRateFormEl" placeholder="0.00"></div>
    <div class="bs-field"><label>Effective from</label><input class="bs-inp" name="effective_from" form="bsRateFormEl" value="<?= date('Y-m-d'); ?>"></div>
    <div class="bs-field" style="justify-content:flex-end"><label>&nbsp;</label>
      <button class="mp-qa-btn green" onclick="bsRateSubmit()">Save new version</button>
    </div>
  </div>
  <p class="bs-muted" style="font-size:11px;margin:8px 0 0 0">A new rate starts on the date you choose and closes the previous version the day before. Days already billed keep the rate they were billed at.</p>
  <?php endif; ?>

  <?php if($can['run'] || $can['rooms']): ?>
  <div class="bs-form">
    <div class="bs-field"><label>Category name</label><input class="bs-inp" name="class_name" form="bsClassFormEl" placeholder="e.g. Private - AC"></div>
    <div class="bs-field"><label>Code</label><input class="bs-inp" name="class_code" form="bsClassFormEl" placeholder="e.g. PRIVATE_AC"></div>
    <div class="bs-field"><label>Description</label><input class="bs-inp" name="description" form="bsClassFormEl" placeholder="Optional note"></div>
    <input type="hidden" name="room_class_id" form="bsClassFormEl" value="">
    <div class="bs-field" style="justify-content:flex-end"><label>&nbsp;</label>
      <button class="mp-qa-btn green" onclick="bsClassSubmit()">Save category</button>
      <button class="mp-qa-btn" id="bs_class_cancel" style="display:none" onclick="bsClassReset()">Cancel edit</button>
    </div>
  </div>
  <p class="bs-muted" style="font-size:11px;margin:8px 0 0 0">Create your own room categories here, or press <b>Edit</b> on a row above to rename one. A category is where the price lives &mdash; after saving, give it a daily rate using <b>Set rate</b> above.</p>
  <?php endif; ?>
</div>

<div class="bs-grid">
  <div class="bs-box">
    <h4>Rooms</h4>
    <p class="bs-lead">Each room sits in a ward and belongs to a category. Beds are then placed in rooms.</p>
    <table class="bs-table">
      <tr><th>Ward</th><th>Room</th><th>Category</th><th>Beds</th><th>Status</th></tr>
      <?php foreach($rooms as $rm): ?>
      <tr>
        <td><?= htmlspecialchars($rm->ward_name ?? '—'); ?></td>
        <td><strong><?= htmlspecialchars($rm->room_label); ?></strong></td>
        <td><?= htmlspecialchars($rm->class_name ?? '—'); ?></td>
        <td><?= (int)$rm->bed_count; ?> / <?= (int)$rm->capacity; ?></td>
        <td><?= htmlspecialchars($rm->status); ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($rooms)): ?><tr><td colspan="5" class="bs-muted">No rooms yet — add one below, or organise beds from the Bed Board.</td></tr><?php endif; ?>
    </table>
    <?php if($can['rooms']): ?>
    <div class="bs-form">
      <div class="bs-field"><label>Ward</label>
        <select class="bs-inp" name="ward_id" form="bsRoomFormEl">
          <?php foreach($wards as $w): ?><option value="<?= (int)$w->id; ?>"><?= htmlspecialchars($w->name); ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="bs-field"><label>Category</label>
        <select class="bs-inp" name="room_class_id" form="bsRoomFormEl">
          <?php foreach($room_classes as $c): ?><option value="<?= (int)$c->id; ?>"><?= htmlspecialchars($c->class_name); ?><?= isset($curRateByClass[$c->id]) ? ' — '.$CI->currency($curRateByClass[$c->id]).'/day' : ' — not priced'; ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="bs-field"><label>Room name</label><input class="bs-inp" name="room_label" form="bsRoomFormEl" placeholder="e.g. Room 12"></div>
      <div class="bs-field"><label>Beds</label><input class="bs-inp" name="capacity" form="bsRoomFormEl" value="1" style="width:70px"></div>
      <div class="bs-field" style="justify-content:flex-end"><label>&nbsp;</label>
        <button class="mp-qa-btn green" onclick="bsRoomSubmit()">Add room</button>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="bs-box">
    <h4>Bill a patient's room by hand</h4>
    <p class="bs-lead">Billing runs on a timer every night and catches up on any days it missed, so nobody has to remember to press anything. Use this when you want to bill a chosen number of days now — say, when settling an account.</p>
    <?php if($can['run']): ?>
    <div class="bs-form" style="border-top:none;padding-top:0;margin-top:0">
      <div class="bs-field"><label>Admission</label>
        <select class="bs-inp" name="admission_id" form="bsBillFormEl" style="min-width:220px">
          <?php foreach($admissions as $a): ?>
            <option value="<?= (int)$a->id; ?>"><?= htmlspecialchars(trim(($a->full_name ?? '').' ('.($a->patient_code ?? '').')').' — '.$a->admission_code); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="bs-field"><label>Days to bill</label><input class="bs-inp" name="days" form="bsBillFormEl" value="7" style="width:80px"></div>
      <div class="bs-field"><label>Up to and including</label><input class="bs-inp" name="to_date" form="bsBillFormEl" value="<?= date('Y-m-d'); ?>"></div>
      <div class="bs-field" style="justify-content:flex-end"><label>&nbsp;</label>
        <button class="mp-qa-btn green" onclick="bsBillSubmit()">Bill those days</button>
      </div>
    </div>
    <?php if(empty($admissions)): ?><p class="bs-muted">No active admissions to bill.</p><?php endif; ?>
    <div class="bs-note">Days already billed are never billed twice. Leave days and the discharge day follow the charging policies below.</div>
    <?php else: ?>
    <p class="bs-muted">You do not have permission to run billing.</p>
    <?php endif; ?>
  </div>
</div>

<div class="bs-box">
  <h4>Bed payment accounts</h4>
  <p class="bs-lead">Record service charges and payments against the patient's bed at the time of each transaction. Outpatients use the outpatient account. Transfers affect new transactions; existing ledger history stays on its original account.</p>
  <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
    <input type="checkbox" <?= ($policies['bed_accounts_enabled'] ?? '0')==='1' ? 'checked' : ''; ?> <?= !$can['run'] ? 'disabled' : ''; ?> onchange="bsBedAccounts(this)">
    Enable bed accounts for this workspace
  </label>
  <p class="bs-muted" style="margin-top:8px">Off by default. Switching off stops new account assignments and preserves recorded history.</p>
</div>

<div class="bs-box">
  <h4>System accounts for patient billing</h4>
  <label style="display:flex;align-items:center;gap:10px">
    <input type="checkbox" <?= ($policies['bed_system_accounts_enabled'] ?? '0')==='1' ? 'checked' : ''; ?> <?= !$can['run'] ? 'disabled' : ''; ?> onchange="bsBedAccounts(this,'bed_system_accounts_enabled')">
    Use system accounts for Physio billing
  </label>
  <p class="bs-lead" style="margin-top:8px">When enabled, existing and new beds receive linked accounts in Accounts. Inpatient bills show the current bed account; outpatients choose an active account without a bed. Switching off hides the choice and stops new system-account routing, preserving existing accounts and history.</p>
  <p class="bs-lead">Bed ledgers record charges and settlements. System account books record new direct collections; wallet or store-credit settlement does not create another collection.</p>
</div>

<div class="bs-box">
  <h4>Charging policies</h4>
  <table class="bs-table">
    <tr><th>Policy</th><th>Value</th><th>Meaning</th></tr>
    <?php
    $explain = array(
      'billing_boundary'      => 'Charge unit — calendar day from admission date',
      'admission_day_charge'  => 'Whether the admission day itself is billed',
      'discharge_day_charge'  => 'none = departure day not billed; full = billed',
      'leave_billing'         => 'Bed charge while on authorised leave: full | half | none',
      'morning_due'           => 'Morning nursing task due time',
      'evening_due'           => 'Evening nursing task due time',
      'auto_settle_wallet'    => '1 = invoice auto-settles from available wallet after each run',
    );
    $defaults = array('billing_boundary'=>'calendar','admission_day_charge'=>'full','discharge_day_charge'=>'none','leave_billing'=>'half','morning_due'=>'08:00','evening_due'=>'20:00','auto_settle_wallet'=>'1');
    foreach($defaults as $k => $dv): $v = $policies[$k] ?? $dv; ?>
    <tr><td><code><?= $k; ?></code></td>
        <td><?php if($can['run']): ?><input class="bs-inp" style="width:130px" value="<?= htmlspecialchars($v); ?>" onchange="bsPolicy('<?= $k; ?>', this.value)"><?php else: ?><strong><?= htmlspecialchars($v); ?></strong><?php endif; ?></td>
        <td class="bs-muted"><?= htmlspecialchars($explain[$k] ?? ''); ?></td></tr>
    <?php endforeach; ?>
  </table>
</div>

<script>
function bsToast(m, t){ if(window.cqToast) window.cqToast(m, t || 'info'); }
function bsPost(url, data){
  var fd = new FormData();
  Object.keys(data||{}).forEach(function(k){ if(data[k]!==null && data[k]!==undefined) fd.append(k, data[k]); });
  if(window.csrfName) fd.append(window.csrfName, window.csrfHash);
  return fetch(url, {method:'POST', body:fd}).then(function(r){ return r.json(); });
}
function bsBedAccounts(input, key){
  var before = !input.checked; input.disabled = true;
  bsPost('<?= base_url('inpatient/save_policy'); ?>', {policy_key: key || 'bed_accounts_enabled', policy_value: input.checked ? '1' : '0'})
    .then(function(d){
      if(d.status !== 'success'){ input.checked = before; bsToast(d.message || 'Could not save','error'); }
      else { bsToast(d.message||'Saved','success'); setTimeout(function(){ location.reload(); }, 600); }
    })
    .catch(function(){ input.checked = before; bsToast('Could not save. Try again.','error'); })
    .finally(function(){ input.disabled = false; });
}
function bsPolicy(k, v){
  bsPost('<?= base_url('inpatient/save_policy'); ?>', {policy_key:k, policy_value:v})
    .then(function(d){ bsToast(d.message||'Saved', d.status==='success' ? 'success':'error'); });
}
function bsRateForm(classId, className){
  var sel = document.querySelector('select[name=room_class_id][form="bsRateFormEl"]');
  if(sel){ sel.value = classId || ''; }
  var name = document.querySelector('input[name=name][form="bsRateFormEl"]');
  if(name){ name.value = className ? (className + ' daily') : 'Bed daily'; name.focus(); }
}
function bsRateCodeHint(v){}
function bsRateSubmit(){
  var g = function(n){ var el = document.querySelector('[name="'+n+'"][form="bsRateFormEl"]'); return el ? el.value : ''; };
  var data = {room_class_id:g('room_class_id'), rate_code:g('rate_code'), name:g('name'), amount:g('amount'), effective_from:g('effective_from')};
  if(!data.name || !data.amount){ bsToast('Enter a name and an amount','error'); return; }
  bsPost('<?= base_url('inpatient/save_rate'); ?>', data)
    .then(function(d){ bsToast(d.message||'', d.status==='success'?'success':'error');
      if(d.status==='success') setTimeout(function(){ location.reload(); }, 600); });
}
function bsRoomSubmit(){
  var g = function(n){ var el = document.querySelector('[name="'+n+'"][form="bsRoomFormEl"]'); return el ? el.value : ''; };
  var data = {ward_id:g('ward_id'), room_class_id:g('room_class_id'), room_label:g('room_label'), capacity:g('capacity')};
  if(!data.room_label){ bsToast('Enter a room name','error'); return; }
  bsPost('<?= base_url('inpatient/save_room'); ?>', data)
    .then(function(d){ bsToast(d.message||'', d.status==='success'?'success':'error');
      if(d.status==='success') setTimeout(function(){ location.reload(); }, 600); });
}
function bsSeedClasses(){
  bsPost('<?= base_url('inpatient/seed_room_classes'); ?>', {})
    .then(function(d){ bsToast(d.message||'', d.status==='success'?'success':'error');
      if(d.status==='success') setTimeout(function(){ location.reload(); }, 600); });
}
var CLASSES = {};
<?php foreach($room_classes as $c): ?>
CLASSES[<?= (int)$c->id; ?>] = {name: <?= json_encode((string)$c->class_name); ?>, code: <?= json_encode((string)$c->class_code); ?>, desc: <?= json_encode((string)$c->description); ?>};
<?php endforeach; ?>
function bsClassSubmit(){
  var g = function(n){ var el = document.querySelector('[name="'+n+'"][form="bsClassFormEl"]'); return el ? el.value : ''; };
  var data = {class_name:g('class_name'), class_code:g('class_code'), description:g('description'), room_class_id:g('room_class_id')};
  if(!data.class_name){ bsToast('Enter a category name','error'); return; }
  bsPost('<?= base_url('inpatient/save_room_class'); ?>', data)
    .then(function(d){ bsToast(d.message||'', d.status==='success'?'success':'error');
      if(d.status==='success'){ bsClassReset(); setTimeout(function(){ location.reload(); }, 600); } });
}
function bsClassEdit(id){
  var c = CLASSES[id]; if(!c) return;
  document.querySelector('[name="class_name"][form="bsClassFormEl"]').value = c.name || '';
  document.querySelector('[name="class_code"][form="bsClassFormEl"]').value = c.code || '';
  document.querySelector('[name="description"][form="bsClassFormEl"]').value = c.desc || '';
  document.querySelector('[name="room_class_id"][form="bsClassFormEl"]').value = id;
  var cancel = document.getElementById('bs_class_cancel'); if(cancel) cancel.style.display = '';
}
function bsClassReset(){
  ['class_name','class_code','description'].forEach(function(n){ var el=document.querySelector('[name="'+n+'"][form="bsClassFormEl"]'); if(el) el.value=''; });
  var hid=document.querySelector('[name="room_class_id"][form="bsClassFormEl"]'); if(hid) hid.value='';
  var cancel=document.getElementById('bs_class_cancel'); if(cancel) cancel.style.display='none';
}
function bsBillSubmit(){
  var g = function(n){ var el = document.querySelector('[name="'+n+'"][form="bsBillFormEl"]'); return el ? el.value : ''; };
  var data = {admission_id:g('admission_id'), days:g('days'), to_date:g('to_date')};
  if(!data.admission_id){ bsToast('Choose an admission','error'); return; }
  if(!data.days || parseInt(data.days,10) < 1){ bsToast('Enter how many days to bill','error'); return; }
  var msg = 'Bill ' + data.days + ' day(s) of room charges, ending ' + (data.to_date || 'today') + '? Days already billed are skipped.';
  var run = function(){
    bsPost('<?= base_url('inpatient/bill_days'); ?>', data)
      .then(function(d){ bsToast(d.message||'', d.status==='success'?'success':'error'); });
  };
  window.mpConfirmAsync(msg, {title:'Bill room days'}).then(function(ok){ if(ok) run(); });
}
function bsRun(){
  window.mpPrompt({title:"Run today's billing", message:'Bill every active admission up to and including this date. Days already billed are skipped.', value:'<?= date('Y-m-d'); ?>', okText:'Run billing'})
    .then(function(v){
      if(!v) return;
      bsPost('<?= base_url('inpatient/run_billing'); ?>', {date:v}).then(function(d){ bsToast(d.message||'', d.status==='success'?'success':'error'); });
    });
}
</script>
