<?php $this->load->view('admin/desktop/_styles'); $this->load->view('components/dialog'); $CI =& get_instance(); ?>
<style>
.rp-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:16px!important;margin-bottom:14px!important}
.rp-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 12px 0!important}
.rp-table{width:100%!important;border-collapse:collapse!important;font-size:12.5px!important}
.rp-table th{text-align:left!important;padding:6px 9px!important;border-bottom:1px solid var(--mp-border)!important;font-size:10px!important;text-transform:uppercase!important;letter-spacing:.4px!important;color:var(--mp-muted)!important}
.rp-table td{padding:8px 9px!important;border-bottom:1px solid #F1F5F9!important;vertical-align:top!important}
.rp-table tr:last-child td{border-bottom:0!important}
.rp-name{font-weight:700!important;font-size:13px!important}
.rp-sub{color:var(--mp-muted);font-size:11px;margin-top:2px}
.rp-badge{display:inline-block;padding:2px 9px;border-radius:11px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.3px}
.rp-lab{background:#E0F2FE;color:#075985}.rp-hospital{background:#EDE9FE;color:#5B21B6}.rp-doctor{background:#DCFCE7;color:#166534}
.rp-on{background:#D1FAE5;color:#065F46}.rp-off{background:#F1F5F9;color:#64748B}
.rp-tabs{display:flex;gap:7px;flex-wrap:wrap;margin:0 0 14px}
.rp-tabs a{padding:7px 14px;border:1px solid transparent;border-radius:8px;font-size:12px;font-weight:700;color:var(--mp-muted);text-decoration:none}
.rp-tabs a:hover{background:#e3efe9;color:var(--mp-primary-dark)}
.rp-tabs a.active{background:#dff0e8;color:#104d40;border-color:#8dbba6}
.rp-inp{padding:7px 10px!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;font-size:12px!important;background:var(--mp-bg,#fff)!important;color:inherit!important}
.rp-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:10px}
.rp-field label{display:block;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--mp-muted);margin-bottom:4px}
.rp-field .rp-inp{width:100%;box-sizing:border-box}
.rp-actions{display:flex;gap:8px;align-items:center;margin-top:12px}
.rp-empty{padding:22px 4px;color:var(--mp-muted);font-size:13px}
</style>

<div class="mp-page-head">
  <div>
    <h2>Referral partners</h2>
    <div class="mp-page-sub">Labs, hospitals and doctors you send patients to. Therapists pick from this list when a patient is referred, and record what happened at the partner afterwards.</div>
  </div>
</div>

<div class="rp-tabs">
  <?php $tabs = array(null=>'All', 'lab'=>'Labs', 'hospital'=>'Hospitals', 'doctor'=>'Doctors'); ?>
  <?php foreach($tabs as $key=>$label): ?>
    <?php $n = $key === null ? array_sum($counts) : (int)$counts[$key]; ?>
    <a class="<?= $active_type === $key ? 'active' : ''; ?>" href="<?= base_url('referrals' . ($key ? '?type='.$key : '')); ?>">
      <?= htmlspecialchars($label); ?> <span style="opacity:.65">(<?= $n; ?>)</span>
    </a>
  <?php endforeach; ?>
</div>

<div class="rp-box">
  <table class="rp-table">
    <thead>
      <tr><th>Partner</th><th>Type</th><th>Specialty / focus</th><th>Contact</th><th>Status</th><?php if($can['manage']): ?><th style="width:170px;"></th><?php endif; ?></tr>
    </thead>
    <tbody>
    <?php foreach($partners as $p): ?>
      <tr>
        <td>
          <div class="rp-name"><?= htmlspecialchars($p->name); ?></div>
          <?php if($p->address): ?><div class="rp-sub"><?= htmlspecialchars($p->address); ?></div><?php endif; ?>
          <?php if($p->notes): ?><div class="rp-sub"><?= htmlspecialchars(mb_substr($p->notes, 0, 90)); ?></div><?php endif; ?>
        </td>
        <td><span class="rp-badge rp-<?= htmlspecialchars($p->partner_type); ?>"><?= htmlspecialchars(ucfirst($p->partner_type)); ?></span></td>
        <td><?= htmlspecialchars($p->specialty ?: '—'); ?></td>
        <td>
          <?php if($p->contact_person): ?><div><?= htmlspecialchars($p->contact_person); ?></div><?php endif; ?>
          <?php if($p->phone): ?><div class="rp-sub"><?= htmlspecialchars($p->phone); ?></div><?php endif; ?>
          <?php if($p->email): ?><div class="rp-sub"><?= htmlspecialchars($p->email); ?></div><?php endif; ?>
          <?php if(!$p->contact_person && !$p->phone && !$p->email): ?>—<?php endif; ?>
        </td>
        <td><span class="rp-badge <?= $p->is_active ? 'rp-on' : 'rp-off'; ?>"><?= $p->is_active ? 'Active' : 'Hidden'; ?></span></td>
        <?php if($can['manage']): ?>
        <td>
          <button class="mp-qa-btn" type="button" onclick='rpEdit(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG); ?>)'><i class="fa fa-edit"></i> Edit</button>
          <button class="mp-qa-btn" type="button" onclick="rpRemove(<?= (int)$p->id; ?>,'<?= htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?>')"><i class="fa fa-trash"></i> Remove</button>
        </td>
        <?php endif; ?>
      </tr>
    <?php endforeach; ?>
    <?php if(empty($partners)): ?>
      <tr><td colspan="6"><div class="rp-empty">No referral partners here yet. Add the first one below.</div></td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  <?php if($can['manage']): ?>
  <div class="rp-actions" style="border-top:1px solid var(--mp-border);margin-top:0;padding-top:12px">
    <button class="mp-qa-btn" type="button" onclick="rpSeed()"><i class="fa fa-hospital-o"></i> Add the standard hospitals</button>
    <span class="rp-sub">Restores the well-known hospitals that have been removed. Nothing already here is touched.</span>
  </div>
  <?php endif; ?>
</div>

<div class="rp-box">
  <h4>Referral choices</h4>
  <div class="mp-page-sub" style="margin:-4px 0 14px">What a therapist picks from when they refer a patient. Edit the wording to suit your facility — existing referrals keep the reason they were made with.</div>
  <div class="rp-grid" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr));align-items:start">

    <div>
      <div class="rp-field" style="margin-bottom:8px"><label>Reason for referral</label></div>
      <table class="rp-table">
        <?php foreach($reasons as $o): ?>
        <tr>
          <td><?= htmlspecialchars($o->label); ?></td>
          <td style="width:78px;text-align:right">
            <?php if($can['manage']): ?><button class="mp-qa-btn" type="button" onclick="rpOptionRemove(<?= (int)$o->id; ?>,'<?= htmlspecialchars($o->label, ENT_QUOTES, 'UTF-8'); ?>')"><i class="fa fa-times"></i></button><?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($reasons)): ?><tr><td class="rp-empty">No reasons configured.</td></tr><?php endif; ?>
      </table>
      <?php if($can['manage']): ?>
      <form class="rp-actions" onsubmit="return rpOptionAdd(event,'reason')">
        <input class="rp-inp" name="label" placeholder="Add a reason…" required style="flex:1">
        <button class="mp-qa-btn green">Add</button>
      </form>
      <?php endif; ?>
    </div>

    <div>
      <div class="rp-field" style="margin-bottom:8px"><label>How urgent — and the one it starts on</label></div>
      <table class="rp-table">
        <?php foreach($urgencies as $o): ?>
        <tr>
          <td>
            <?= htmlspecialchars($o->label); ?>
            <?php if(!empty($o->is_default)): ?><span class="rp-badge rp-on">starts here</span><?php endif; ?>
          </td>
          <td style="width:120px;text-align:right">
            <?php if($can['manage']): ?>
              <?php if(empty($o->is_default)): ?><button class="mp-qa-btn" type="button" title="New referrals start on this one" onclick="rpOptionDefault('urgency','<?= htmlspecialchars($o->code, ENT_QUOTES, 'UTF-8'); ?>')">Make default</button><?php endif; ?>
              <button class="mp-qa-btn" type="button" onclick="rpOptionRemove(<?= (int)$o->id; ?>,'<?= htmlspecialchars($o->label, ENT_QUOTES, 'UTF-8'); ?>')"><i class="fa fa-times"></i></button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($urgencies)): ?><tr><td class="rp-empty">No urgency levels configured.</td></tr><?php endif; ?>
      </table>
      <?php if($can['manage']): ?>
      <form class="rp-actions" onsubmit="return rpOptionAdd(event,'urgency')">
        <input class="rp-inp" name="label" placeholder="Add an urgency level…" required style="flex:1">
        <button class="mp-qa-btn green">Add</button>
      </form>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php if($can['manage']): ?>
<div class="rp-box">
  <h4 id="rpFormTitle">Add a referral partner</h4>
  <form id="rpForm" onsubmit="return rpSave(event)">
    <input type="hidden" name="id" id="rpId" value="0">
    <div class="rp-grid">
      <div class="rp-field"><label>Type *</label>
        <select class="rp-inp" name="partner_type" id="rpType" required onchange="rpFillSpecialty(this.value)">
          <option value="hospital">Hospital</option>
          <option value="lab">Lab</option>
          <option value="doctor">Doctor</option>
        </select>
      </div>
      <div class="rp-field"><label>Name *</label><input class="rp-inp" name="name" id="rpName" required placeholder="e.g. Meridian Diagnostics"></div>
      <div class="rp-field"><label id="rpSpecialtyLabel">Focus</label>
        <select class="rp-inp" name="specialty" id="rpSpecialty" onchange="rpSpecialtyChange(this)">
          <option value="">— Not specified —</option>
          <option value="__other__">Other…</option>
        </select>
        <input class="rp-inp" id="rpSpecialtyOther" style="margin-top:6px;display:none" placeholder="Type your own — it stays in the list">
      </div>
      <div class="rp-field"><label>Contact person</label><input class="rp-inp" name="contact_person" id="rpContact" placeholder="Who you speak to"></div>
      <div class="rp-field"><label>Phone</label><input class="rp-inp" name="phone" id="rpPhone" placeholder="Phone number"></div>
      <div class="rp-field"><label>Email</label><input class="rp-inp" name="email" id="rpEmail" placeholder="Email address"></div>
      <div class="rp-field" style="grid-column:1/-1"><label>Address</label><input class="rp-inp" name="address" id="rpAddress" placeholder="Street, city"></div>
      <div class="rp-field" style="grid-column:1/-1"><label>Notes</label><input class="rp-inp" name="notes" id="rpNotes" placeholder="Anything the therapist should know before sending a patient here"></div>
    </div>
    <div class="rp-actions">
      <label style="font-size:12px;font-weight:600;display:flex;align-items:center;gap:6px;">
        <input type="checkbox" name="is_active" id="rpActive" value="1" checked> Available for referral
      </label>
      <button class="mp-qa-btn green" type="submit"><i class="fa fa-check"></i> <span id="rpSubmitLabel">Add partner</span></button>
      <button class="mp-qa-btn" type="button" id="rpCancel" style="display:none" onclick="rpReset()">Cancel</button>
    </div>
  </form>
</div>
<?php endif; ?>

<script>
var CSRF_BODY = { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' };

var RP_SPECIALTIES = <?= json_encode($specialty_options, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var RP_SPECIALTY_LABEL = {hospital:'Focus', doctor:'Specialty', lab:'Type of lab'};

/* `keep` re-selects a saved value when editing — "Other" if it is not on the list. */
function rpFillSpecialty(type, keep){
  var sel = $('#rpSpecialty');
  var list = RP_SPECIALTIES[type] || [];
  sel.empty().append($('<option>').val('').text('— Not specified —'));
  list.forEach(function(v){ sel.append($('<option>').val(v).text(v)); });
  sel.append($('<option>').val('__other__').text('Other…'));
  $('#rpSpecialtyLabel').text(RP_SPECIALTY_LABEL[type] || 'Specialty / focus');
  if(keep && list.indexOf(keep) >= 0){
    sel.val(keep); $('#rpSpecialtyOther').val('').hide();
  } else if(keep){
    sel.val('__other__'); $('#rpSpecialtyOther').val(keep).show();
  } else {
    sel.val(''); $('#rpSpecialtyOther').val('').hide();
  }
}
function rpSpecialtyChange(el){
  if(el.value === '__other__'){ $('#rpSpecialtyOther').show().focus(); }
  else { $('#rpSpecialtyOther').val('').hide(); }
}

function rpEdit(p){
  $('#rpId').val(p.id); $('#rpType').val(p.partner_type); $('#rpName').val(p.name);
  rpFillSpecialty(p.partner_type, p.specialty || '');
  $('#rpContact').val(p.contact_person || '');
  $('#rpPhone').val(p.phone || ''); $('#rpEmail').val(p.email || '');
  $('#rpAddress').val(p.address || ''); $('#rpNotes').val(p.notes || '');
  $('#rpActive').prop('checked', String(p.is_active) === '1');
  $('#rpFormTitle').text('Edit referral partner');
  $('#rpSubmitLabel').text('Save changes');
  $('#rpCancel').show();
  $('html,body').animate({scrollTop: $('#rpForm').offset().top - 40}, 200);
}
function rpReset(){
  $('#rpForm')[0].reset(); $('#rpId').val(0); $('#rpActive').prop('checked', true);
  rpFillSpecialty($('#rpType').val() || 'hospital');
  $('#rpFormTitle').text('Add a referral partner'); $('#rpSubmitLabel').text('Add partner'); $('#rpCancel').hide();
}
function rpSave(e){
  e.preventDefault();
  var fd = new FormData(e.target); var id = $('#rpId').val() || 0;
  var data = $.extend({}, CSRF_BODY);
  fd.forEach(function(v,k){ if(k!=='is_active') data[k]=v; });
  data.is_active = $('#rpActive').is(':checked') ? 1 : 0;
  if(data.specialty === '__other__') data.specialty = $.trim($('#rpSpecialtyOther').val());
  $.post('<?= base_url('referrals/save_partner/'); ?>' + id, data, function(res){
    if(res.status === 'success'){ cqToast(res.message, 'success'); setTimeout(function(){ location.reload(); }, 600); }
    else cqToast(res.message || 'Failed', 'danger');
  }, 'json');
  return false;
}
function rpRemove(id, name){
  mpConfirmAsync('Remove "' + name + '" from the referral partners? Referrals already recorded for patients will keep the name in their history.', {
    title: 'Remove referral partner', okText: 'Remove', danger: true
  }).then(function(ok){
    if(!ok) return;
    $.post('<?= base_url('referrals/remove_partner/'); ?>' + id, CSRF_BODY, function(res){
      if(res.status === 'success'){ cqToast(res.message, 'success'); setTimeout(function(){ location.reload(); }, 600); }
      else cqToast(res.message || 'Failed', 'danger');
    }, 'json');
  });
}
function rpSeed(){
  $.post('<?= base_url('referrals/seed_hospitals'); ?>', CSRF_BODY, function(res){
    cqToast(res.message || (res.status === 'success' ? 'Done' : 'Failed'), res.status === 'success' ? 'success' : 'danger');
    if(res.status === 'success') setTimeout(function(){ location.reload(); }, 600);
  }, 'json');
}
function rpSlug(s){
  return String(s).toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '').slice(0, 60);
}
function rpOptionAdd(e, group){
  e.preventDefault();
  var label = $.trim(e.target.label.value);
  if(!label) return false;
  $.post('<?= base_url('referrals/save_option'); ?>', $.extend({}, CSRF_BODY, {
    group_code: group, code: rpSlug(label), label: label, sort_order: 999
  }), function(res){
    if(res.status === 'success'){ cqToast(res.message, 'success'); setTimeout(function(){ location.reload(); }, 500); }
    else cqToast(res.message || 'Failed', 'danger');
  }, 'json');
  return false;
}
function rpOptionDefault(group, code){
  $.post('<?= base_url('referrals/default_option'); ?>', $.extend({}, CSRF_BODY, {
    group_code: group, code: code
  }), function(res){
    if(res.status === 'success'){ cqToast(res.message, 'success'); setTimeout(function(){ location.reload(); }, 500); }
    else cqToast(res.message || 'Failed', 'danger');
  }, 'json');
}
function rpOptionRemove(id, label){
  mpConfirmAsync('Remove "' + label + '" from the list? Referrals already recorded keep the reason they were made with.', {
    title: 'Remove from list', okText: 'Remove', danger: true
  }).then(function(ok){
    if(!ok) return;
    $.post('<?= base_url('referrals/remove_option/'); ?>' + id, CSRF_BODY, function(res){
      if(res.status === 'success'){ cqToast(res.message, 'success'); setTimeout(function(){ location.reload(); }, 500); }
      else cqToast(res.message || 'Failed', 'danger');
    }, 'json');
  });
}
if(document.getElementById('rpSpecialty')){
  rpFillSpecialty(document.getElementById('rpType').value || 'hospital');
}
</script>
