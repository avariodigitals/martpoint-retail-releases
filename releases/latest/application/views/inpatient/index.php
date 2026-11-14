<?php $this->load->view('admin/desktop/_styles'); $this->load->view('components/dialog'); $CI =& get_instance();
/** Trim for a table cell without cutting mid-word or mid-UTF8 char. */
$ipTrim = function($s, $n = 110){
	$s = trim((string)$s);
	if($s === '') return '';
	if(function_exists('mb_strimwidth')) return mb_strimwidth($s, 0, $n, '…', 'UTF-8');
	return strlen($s) > $n ? substr($s, 0, $n) . '…' : $s;
};
?>
<style>
.ip-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:14px!important}
.ip-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 10px 0!important}
.ip-table{width:100%!important;border-collapse:collapse!important;font-size:12px!important}
.ip-table th{text-align:left!important;padding:5px 8px!important;border-bottom:1px solid var(--mp-border)!important;font-size:10px!important;text-transform:uppercase!important;color:var(--mp-muted)!important}
.ip-table td{padding:6px 8px!important;border-bottom:1px solid #F1F5F9!important;vertical-align:top!important}
.ip-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:800;text-transform:uppercase}
.ip-active{background:#D1FAE5;color:#065F46}.ip-discharged{background:#DBEAFE;color:#1E40AF}
.ip-deceased{background:#111827;color:#F9FAFB}.ip-transferred_out{background:#FEF3C7;color:#92400E}
.ip-inp{padding:7px 10px!important;border:1px solid var(--mp-border)!important;border-radius:9px!important;font-size:12px!important;width:100%!important;margin-bottom:8px!important;background:var(--mp-bg,#fff)!important;color:inherit!important}
textarea.ip-inp{resize:vertical}
.ip-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.ip-lbl{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--mp-muted);margin:0 0 4px 0}
.ip-hint{font-size:11px;color:var(--mp-muted);margin:-4px 0 8px 0;line-height:1.5}
.ip-hint b{color:inherit}
.ip-pull{background:#F0FDF4;border:1px solid #BBF7D0;border-radius:9px;padding:7px 10px;font-size:11px;color:#166534;margin:0 0 8px 0;line-height:1.5}
.ip-pull.warn{background:#FFFBEB;border-color:#FDE68A;color:#92400E}
.ip-pull.none{background:#F8FAFC;border-color:var(--mp-border);color:var(--mp-muted)}
.ip-pending{background:#EFF6FF;border:1px solid #BFDBFE;border-radius:12px;padding:12px 14px;margin-bottom:14px}
.ip-pending h4{color:#1E40AF!important;margin:0 0 4px 0!important}
.ip-pending .sub{font-size:11px;color:#3B82F6;margin:0 0 10px 0}
.ip-emerg{background:#FEF2F2;border:1px solid #FECACA;border-radius:10px;padding:9px 11px;font-size:11px;color:#991B1B;line-height:1.6}
.ip-emerg .t{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px}
</style>

<div class="mp-page-head">
  <div><h2>Admissions</h2>
  <div class="mp-page-sub">Inpatient stays — bed allocation, nursing care, daily billing and closure.</div></div>
  <div><a class="mp-qa-btn blue" href="<?= base_url('inpatient/beds'); ?>">Bed board</a>
       <a class="mp-qa-btn" href="<?= base_url('inpatient/tasks'); ?>">Ward tasks</a>
       <a class="mp-qa-btn" href="<?= base_url('inpatient/referrals'); ?>">Referrals</a>
       <?php if($can['manage']): ?><a class="mp-qa-btn green" href="#adm-form">New admission</a><?php endif; ?></div>
</div>

<?php if($can['manage'] && !empty($pending)): ?>
<div class="ip-pending" id="ip-pending">
  <h4>Waiting to be admitted</h4>
  <div class="sub">The physiotherapist has marked these patients as needing ward care, but no admission has been opened yet.</div>
  <table class="ip-table">
    <tr><th>Episode</th><th>Patient</th><th>Phone</th><th>Visits</th><th>Why they need the ward</th><th></th></tr>
    <?php foreach($pending as $p): ?>
    <tr>
      <td><strong><?= htmlspecialchars($p->episode_code); ?></strong></td>
      <td><?= htmlspecialchars(trim(($p->patient_code ?? '') . ' ' . ($p->customer_name ?? ''))); ?></td>
      <td><?= htmlspecialchars($p->patient_mobile ?: '—'); ?></td>
      <td><?= (int)$p->visits; ?></td>
      <td><?= htmlspecialchars($ipTrim($p->reason ?: '—')); ?></td>
      <td><button class="mp-qa-btn green" data-pid="<?= (int)$p->patient_id; ?>" data-eid="<?= (int)$p->episode_id; ?>"
                  onclick="ipAdmitThis(this)">Admit</button></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php endif; ?>

<div class="ip-box">
  <h4>Register</h4>
  <table class="ip-table">
    <tr><th>Admission</th><th>Patient</th><th>Episode</th><th>Admitted</th><th>Ward / Bed</th><th>Session packages</th><th>Status</th><th>Closed</th><th></th></tr>
    <?php foreach($admissions as $a): ?>
    <tr>
      <td><strong><?= htmlspecialchars($a->admission_code); ?></strong></td>
      <td><?= htmlspecialchars(trim(($a->patient_code ?? '') . ' ' . ($a->full_name ?? ''))); ?></td>
      <td><?= htmlspecialchars($a->episode_code ?: '—'); ?></td>
      <td><?= htmlspecialchars($a->admitted_at); ?></td>
      <td><?= htmlspecialchars(trim(($a->ward_name ?? '—') . ' / ' . ($a->bed_label ?? '—'))); ?></td>
      <td><?php
        // Only say anything where there was something to decide. A blank here
        // means the patient had no prepaid sessions, which is the normal case.
        $dec = (string)$a->outpatient_decision;
        echo $dec === 'continue' ? '<span style="color:var(--mp-muted)">unaffected</span>'
           : ($dec === 'pause' ? 'paused for the stay' : ($dec === 'replace' ? 'cancelled &amp; refunded' : htmlspecialchars($dec)));
      ?></td>
      <td><span class="ip-badge ip-<?= htmlspecialchars($a->status); ?>"><?= htmlspecialchars($a->status); ?></span></td>
      <td><?= htmlspecialchars($a->closed_at ?? '—'); ?></td>
      <td><a class="mp-qa-btn" href="<?= base_url('inpatient/view/' . (int)$a->id); ?>">Open</a></td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($admissions)): ?><tr><td colspan="9" style="color:var(--mp-muted)">No admissions yet.</td></tr><?php endif; ?>
  </table>
</div>

<?php if($can['manage']): ?>
<div class="ip-box" id="adm-form">
  <h4>New admission</h4>
  <form onsubmit="return ipAdmit(event)">
    <input type="hidden" name="episode_id" id="ip_episode_id" value="">

    <div class="ip-lbl">1 &middot; Patient</div>
    <select class="ip-inp" name="patient_id" id="ip_patient" required onchange="ipPickPatient(this.value)">
      <option value="">— Choose the patient —</option>
      <?php foreach($patients as $p): ?>
        <option value="<?= (int)$p->id; ?>"><?= htmlspecialchars($p->patient_code . ' — ' . ($p->customer_name ?? '')); ?></option>
      <?php endforeach; ?>
    </select>
    <div class="ip-hint">Everything below fills itself in from this patient's record. Change anything that does not fit.</div>

    <div class="ip-grid">
      <div>
        <div class="ip-lbl">2 &middot; Why are they being admitted?</div>
        <textarea class="ip-inp" name="reason" id="ip_reason" rows="3" required placeholder="What was found, and what the ward is for"></textarea>
        <div class="ip-pull none" id="ip_reason_src">Pick a patient and the physiotherapist's assessment remark is copied here.</div>

        <div class="ip-lbl">3 &middot; Which bed?</div>
        <select class="ip-inp" name="bed_id" id="ip_bed">
          <option value="">— No bed yet — assign one on the ward later —</option>
          <?php foreach($beds as $b): ?>
            <option value="<?= (int)$b->id; ?>"><?= htmlspecialchars('Bed ' . $b->bed_label . ' (ward ' . $b->ward_id . ')'); ?></option>
          <?php endforeach; ?>
        </select>
        <div class="ip-pull none" id="ip_bed_src">Pick a patient and any bed already on their bill is chosen here.</div>
      </div>

      <div>
        <div class="ip-lbl">4 &middot; Treatment plan for this stay</div>
        <select class="ip-inp" name="treatment_plan_id" id="ip_plan">
          <option value="">— No plan on file —</option>
        </select>
        <div class="ip-hint">The plan the physiotherapist drew up: the course of treatment, how many sessions, the goals. This is what the ward follows &mdash; it replaces the old free-text care plan.</div>

        <div class="ip-lbl">5 &middot; When did they come in?</div>
        <input type="datetime-local" class="ip-inp" name="admitted_at" id="ip_admitted_at" step="60">
        <div class="ip-hint">Defaults to right now &mdash; roll it back only if this patient was actually admitted earlier.</div>

        <div id="ip_pkg_wrap" style="display:none">
          <div class="ip-lbl">6 &middot; Their prepaid physio sessions</div>
          <div class="ip-pull warn" id="ip_pkg_note"></div>
          <select class="ip-inp" name="outpatient_decision" id="ip_pkg_decision">
            <option value="">— Choose what happens to them —</option>
            <option value="continue">Keep them going — they carry on alongside the ward</option>
            <option value="pause">Pause them — they pick up again when they go home</option>
            <option value="replace">Cancel them — refund what is left to their wallet</option>
          </select>
          <input class="ip-inp" name="outpatient_decision_note" placeholder="Note (optional)">
        </div>

        <div class="ip-emerg" id="ip_emerg" style="display:none">
          <div class="t">Emergency contact</div>
          <div id="ip_emerg_body"></div>
        </div>
      </div>
    </div>
    <button class="mp-qa-btn green" type="submit">Admit patient</button>
  </form>
</div>

<script>
var ipCtx = null;

function ipAdmitThis(btn){
  var pid = btn.getAttribute('data-pid');
  var eid = btn.getAttribute('data-eid');
  var sel = document.getElementById('ip_patient');
  if(sel){ sel.value = pid; }
  document.getElementById('ip_episode_id').value = eid || '';
  ipPickPatient(pid, eid);
  var f = document.getElementById('adm-form');
  if(f) f.scrollIntoView({behavior:'smooth', block:'start'});
}

function ipPickPatient(pid, eid){
  var wrapPkg = document.getElementById('ip_pkg_wrap');
  var emerg   = document.getElementById('ip_emerg');
  if(!pid){
    document.getElementById('ip_reason').value = '';
    document.getElementById('ip_plan').innerHTML = '<option value="">— No plan on file —</option>';
    document.getElementById('ip_episode_id').value = '';
    document.getElementById('ip_reason_src').className = 'ip-pull none';
    document.getElementById('ip_reason_src').textContent = 'Pick a patient and the physiotherapist’s assessment remark is copied here.';
    document.getElementById('ip_bed_src').className = 'ip-pull none';
    document.getElementById('ip_bed_src').textContent = 'Pick a patient and any bed already on their bill is chosen here.';
    wrapPkg.style.display = 'none';
    emerg.style.display = 'none';
    return;
  }
  fetch('<?= base_url('inpatient/admit_context/'); ?>' + encodeURIComponent(pid), {headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){ return r.json(); })
    .then(function(d){
      if(!d || d.status === 'error' || d.ok === false){ cqToast((d && d.message) || 'Could not read this patient’s record.', 'danger'); return; }
      ipCtx = d;

      // Reason — the physio already wrote this at assessment.
      if(d.reason){
        document.getElementById('ip_reason').value = d.reason;
        var src = document.getElementById('ip_reason_src');
        src.className = 'ip-pull';
        src.textContent = 'Copied from the ' + (d.reason_from || 'physiotherapist’s assessment') + '. Edit it if the ward needs something different.';
      }

      // Bed — the one the bill is already charging.
      if(d.bed_id){
        document.getElementById('ip_bed').value = String(d.bed_id);
        var bs = document.getElementById('ip_bed_src');
        bs.className = 'ip-pull';
        bs.textContent = 'Bed ' + (d.bed_label || '#' + d.bed_id) + ' — ' + (d.bed_from || 'from this patient’s bill') + '.';
      }

      // Episode — where the stay is filed.
      if(!eid) document.getElementById('ip_episode_id').value = d.episode ? String(d.episode.id) : '';

      // Treatment plan.
      var plans = document.getElementById('ip_plan');
      var html = '<option value="">— No plan on file —</option>';
      (d.plans || []).forEach(function(p){
        html += '<option value="' + parseInt(p.id, 10) + '">' +
          (p.plan_code ? p.plan_code + ' — ' : '') +
          (p.title || 'Untitled plan') + ' (' + (p.care_setting === 'inpatient' ? 'Inpatient' : 'Outpatient') +
          (p.status === 'draft' ? ', draft' : '') + ')</option>';
      });
      plans.innerHTML = html;
      if(d.suggested_plan_id) plans.value = String(d.suggested_plan_id);

      // Prepaid sessions — only asked about when there is something to decide.
      var left = parseInt(d.sessions_left || 0, 10);
      if(left > 0){
        wrapPkg.style.display = '';
        document.getElementById('ip_pkg_note').textContent =
          'This patient still has ' + left + ' prepaid physio session' + (left === 1 ? '' : 's') + ' coming. ' +
          'Ward care replaces that course of treatment, so say what should happen to the ones they have not used.';
      } else {
        wrapPkg.style.display = 'none';
        document.getElementById('ip_pkg_decision').value = 'continue';
      }

      // Emergency contact — shown wherever an admission is opened.
      if(d.nok && (d.nok.name || d.nok.phone)){
        emerg.style.display = '';
        document.getElementById('ip_emerg_body').innerHTML =
          '<b>' + (d.nok.name ? esc(d.nok.name) : 'Not recorded') + '</b>' +
          (d.nok.relationship ? ' &middot; ' + esc(d.nok.relationship) : '') + '<br>' +
          (d.nok.phone ? esc(d.nok.phone) : 'No phone on file');
      } else {
        emerg.style.display = '';
        document.getElementById('ip_emerg_body').innerHTML = 'No next of kin recorded for this patient yet.';
      }

      if(d.active_admission){
        cqToast('This patient already has an active admission — ' + d.active_admission.admission_code + '.', 'warning');
      }
    })
    .catch(function(){ cqToast('Could not read this patient’s record.', 'danger'); });
}

function esc(s){
  return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){
    return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
  });
}

function ipLocalDT(d){
  var p = function(n){ return (n < 10 ? '0' : '') + n; };
  return d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()) + 'T' + p(d.getHours()) + ':' + p(d.getMinutes());
}
var _ipNowEl = document.getElementById('ip_admitted_at');
if(_ipNowEl && !_ipNowEl.value){ _ipNowEl.value = ipLocalDT(new Date()); }

function ipAdmit(e){
  e.preventDefault();
  var left = ipCtx ? parseInt(ipCtx.sessions_left || 0, 10) : 0;
  if(left > 0 && !document.getElementById('ip_pkg_decision').value){
    cqToast('Say what should happen to this patient’s prepaid sessions first.', 'warning');
    document.getElementById('ip_pkg_decision').focus();
    return false;
  }
  var fd = new FormData(e.target);
  var atEl = document.getElementById('ip_admitted_at');
  var at = atEl ? (atEl.value || '') : '';
  if(at.indexOf('T') !== -1){ fd.set('admitted_at', at.replace('T', ' ') + (at.length === 16 ? ':00' : '')); }
  fetch('<?= base_url('inpatient/admit'); ?>', {method:'POST', body:fd})
    .then(function(r){ return r.json(); })
    .then(function(d){
      if(d.status === 'success'){ cqToast(d.message || 'Patient admitted', 'success'); setTimeout(function(){ location.reload(); }, 700); }
      else cqToast(d.message || 'Could not admit this patient.', 'danger');
    })
    .catch(function(){ cqToast('Could not reach the server.', 'danger'); });
  return false;
}
</script>
<?php endif; ?>
