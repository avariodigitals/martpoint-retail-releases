<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.ass-sec{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:16px!important;margin-bottom:14px!important;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 14px}
.ass-sec h4{font-size:12px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 12px 0!important}
.ass-sec h4{grid-column:1/-1}.ass-field-full{grid-column:1/-1}.ass-signature{display:flex;align-items:center;min-height:54px;padding:8px 12px;border:1px solid var(--mp-border);border-radius:10px;background:#fff}.ass-signature img{max-width:220px;max-height:58px;object-fit:contain}
.ass-role{font-weight:800!important;text-transform:uppercase;letter-spacing:.2px}
.ass-req{color:#DC2626!important}
.ass-lock{background:#F8FAFC!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;padding:10px 12px!important;font-size:12px!important;margin-bottom:8px!important}
.ass-amend{border-left:3px solid #8B5CF6!important;padding:8px 12px!important;margin:8px 0!important;font-size:12px!important;background:#FAF5FF!important}
.final-banner{background:#D1FAE5!important;border:1px solid #10B981!important;color:#065F46!important;font-size:12px!important;padding:8px 12px!important;border-radius:8px!important;margin-bottom:14px!important}
.ass-voice-wrap{position:relative}.ass-voice-wrap textarea,.ass-voice-wrap input{padding-right:104px}.ass-voice{position:absolute;right:8px;top:8px;border:1px solid #b8d5c7;background:#eaf3ee;color:#104d40;border-radius:7px;padding:5px 8px;font-size:11px;cursor:pointer}.ass-voice.listening{background:#fee2e2;border-color:#fca5a5;color:#991b1b}.ass-voice-note{font-size:11px;color:#64748b;margin:5px 0 12px}
.ass-check-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;padding:10px;border:1px solid var(--mp-border);border-radius:9px;background:#fbfdfc}.ass-check{display:flex;align-items:flex-start;gap:7px;font-size:13px;font-weight:500}.ass-check input{margin-top:3px}.amend-check-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px;margin-top:5px}.amend-check{display:flex;gap:6px;font-size:12px;font-weight:500}@media(max-width:760px){.ass-sec{grid-template-columns:1fr}.ass-check-grid,.amend-check-grid{grid-template-columns:1fr}}
</style>

<?php $assessmentTitle = ($template->template_key ?? '') === 'physio_initial' ? 'Physiotherapy Initial Assessment' : $template->name; $headerAssessor = $assessment->assessor_name ?? ''; if($headerAssessor === '' && !empty($physio_user)){ $headerAssessor = trim(($physio_user->first_name ?? '').' '.($physio_user->last_name ?? '')) ?: ($physio_user->username ?? ''); } if($headerAssessor === '') $headerAssessor = $this->session->userdata('inv_username'); ?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($assessmentTitle); ?></h2>
    <div class="mp-page-sub">
      <?= htmlspecialchars($patient->customer_name ?? ''); ?> (<?= htmlspecialchars($patient->patient_code ?? ''); ?>)
      · <?= htmlspecialchars($enc->encounter_code); ?>
      · Assessor: <?= htmlspecialchars($headerAssessor); ?>
    </div>
  </div>
  <a href="<?= base_url('care_queue/encounter/' . (int)$enc->id); ?>" class="mp-qa-btn"><i class="fa fa-arrow-left"></i> Back to visit</a>
</div>

<?php if($assessment && $assessment->status === 'final'): ?>
<div class="final-banner"><i class="fa fa-lock"></i>
  Finalised <?= $assessment->finalized_at; ?> — this record is locked. Corrections are made only as attributed amendments below.</div>
<?php endif; ?>

<form id="assForm" onsubmit="return false;">
  <input type="hidden" name="encounter_id" value="<?= (int)$enc->id; ?>">
  <input type="hidden" name="template_id" value="<?= (int)$template->id; ?>">
  <input type="hidden" name="assessment_id" value="<?= (int)($assessment->id ?? 0); ?>">
  <?php $isFinal = $assessment && $assessment->status === 'final'; ?>
  <?php foreach($sections as $sec): ?>
    <div class="ass-sec">
      <h4><?= htmlspecialchars($sec['title'] ?? $sec['key']); ?></h4>
      <?php foreach(($sec['fields'] ?? array()) as $f):
        // PHP rewrites '.' to '_' in incoming POST variable names, so a key
        // sent as "ans__history.presenting_complaint" arrives as
        // "ans__history_presenting_complaint". Using a dot in the answer key
        // therefore produced keys the controller could never reproduce, and a
        // browser-saved assessment came back with every field blank (finalised
        // records rendered as a page full of em-dashes). Underscore is the only
        // separator that survives the round trip.
        $k = $sec['key'] . '_' . $f['key'];
        $v = $answers[$k] ?? '';
      ?>
        <?php $compactField = in_array(($f['type'] ?? 'text'), array('text','readonly','signature','select'), true); ?>
        <?php $fieldLabel = $f['label'] ?? $f['key']; $isRoleLabel = preg_match('/^[A-D]\./', $fieldLabel); ?>
        <div class="mp-form-group <?= $compactField ? '' : 'ass-field-full'; ?>">
          <label class="<?= $isRoleLabel ? 'ass-role' : ''; ?>"><?= htmlspecialchars($fieldLabel); ?><?= !empty($f['required']) ? ' <span class="ass-req">*</span>' : ''; ?></label>
          <?php if($isFinal): ?>
            <?php $displayValue = is_array($v) ? implode(', ', $v) : (string)$v; ?>
            <div class="ass-lock"><?= $displayValue !== '' ? nl2br(htmlspecialchars($displayValue)) : '<span style="color:#94a3b8;">—</span>'; ?></div>
          <?php elseif(($f['type'] ?? '') === 'readonly'): ?>
            <div class="ass-lock"><?= $v !== '' ? htmlspecialchars((string)$v) : '<span style="color:#94a3b8;">Filled from the visit</span>'; ?></div>
            <input type="hidden" name="ans__<?= $k; ?>" value="<?= htmlspecialchars((string)$v); ?>">
          <?php elseif(($f['type'] ?? '') === 'signature'): ?>
            <?php if($v): ?><div class="ass-signature"><img src="<?= base_url($v); ?>" alt="Clinician signature"></div><?php else: ?><div class="ass-lock"><span style="color:#94a3b8;">No signature saved in this user profile</span></div><?php endif; ?>
            <input type="hidden" name="ans__<?= $k; ?>" value="<?= htmlspecialchars((string)$v); ?>">
          <?php elseif(($f['type'] ?? '') === 'checkboxes'): ?>
            <?php $selected = is_array($v) ? $v : (($v !== '' && $v !== null) ? array($v) : array()); ?>
            <div class="ass-check-grid">
              <?php foreach(($f['options'] ?? array()) as $opt): ?>
                <label class="ass-check"><input type="checkbox" name="ans__<?= $k; ?>[]" value="<?= htmlspecialchars($opt); ?>" <?= in_array($opt, $selected, true) ? 'checked' : ''; ?>> <span><?= htmlspecialchars($opt); ?></span></label>
              <?php endforeach; ?>
            </div>
          <?php elseif(($f['type'] ?? 'text') === 'textarea'): ?>
            <div class="ass-voice-wrap"><textarea class="mp-form-control" name="ans__<?= $k; ?>" rows="3"><?= htmlspecialchars((string)$v); ?></textarea><button type="button" class="ass-voice" data-target="ans__<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-microphone"></i> Voice</button></div>
          <?php elseif(($f['type'] ?? '') === 'select'): ?>
            <select class="mp-form-control" name="ans__<?= $k; ?>">
              <option value="">—</option>
              <?php foreach(($f['options'] ?? array()) as $opt): ?>
                <option value="<?= htmlspecialchars($opt); ?>" <?= $v === $opt ? 'selected' : ''; ?>><?= htmlspecialchars($opt); ?></option>
              <?php endforeach; ?>
            </select>
          <?php else: ?>
            <div class="ass-voice-wrap"><input type="text" class="mp-form-control" name="ans__<?= $k; ?>" value="<?= htmlspecialchars((string)$v); ?>"><button type="button" class="ass-voice" data-target="ans__<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-microphone"></i> Voice</button></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>

  <?php if(!$isFinal && $can['save']): ?>
    <button class="mp-qa-btn" onclick="saveDraft()"><i class="fa fa-save"></i> Save draft</button>
  <?php endif; ?>
  <?php if(!$isFinal && $can['finalize']): ?>
    <button class="mp-qa-btn green" onclick="finalizeAss()"><i class="fa fa-check"></i> Save &amp; Finalise</button>
  <?php endif; ?>
  <?php if($assessment): ?><a id="downloadLink" class="mp-qa-btn blue" href="<?= base_url('assessments/download/'.(int)$assessment->id); ?>"><i class="fa fa-download"></i> Download copy</a><?php else: ?><a id="downloadLink" class="mp-qa-btn blue" href="#" style="display:none"><i class="fa fa-download"></i> Download copy</a><?php endif; ?>
</form>
<?php if(!empty($referral_panel)): ?><?= $referral_panel; ?><?php endif; ?>
<div class="ass-voice-note"><i class="fa fa-info-circle"></i> Voice fills the focused narrative field using your browser microphone. Review the transcript before saving.</div>

<?php if($isFinal && $can['amend']): ?>
<div class="ass-sec">
  <h4>Record an Amendment (originals are preserved)</h4>
  <?php foreach($sections as $sec): foreach(($sec['fields'] ?? array()) as $f):
    $k = $sec['key'] . '_' . $f['key'];
  ?>
    <div class="mp-form-group">
      <?php $currentValue = $answers[$k] ?? ''; $currentDisplay = is_array($currentValue) ? implode(', ', $currentValue) : (string)$currentValue; ?>
      <label><?= htmlspecialchars($f['label'] ?? $f['key']); ?> <small style="color:#94a3b8;">current: <?= htmlspecialchars($currentDisplay !== '' ? $currentDisplay : '—'); ?></small></label>
      <?php if(($f['type'] ?? '') === 'checkboxes'): ?>
        <?php $selected = is_array($currentValue) ? $currentValue : (($currentValue !== '' && $currentValue !== null) ? array($currentValue) : array()); ?>
        <div class="amend-check-grid amend-check-group" data-key="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>">
          <?php foreach(($f['options'] ?? array()) as $opt): ?><label class="amend-check"><input type="checkbox" value="<?= htmlspecialchars($opt); ?>" <?= in_array($opt, $selected, true) ? 'checked' : ''; ?>> <?= htmlspecialchars($opt); ?></label><?php endforeach; ?>
        </div>
      <?php else: ?>
        <input type="text" class="mp-form-control amend-field" data-key="<?= $k; ?>" placeholder="Amended value (leave blank to keep current)">
      <?php endif; ?>
    </div>
  <?php endforeach; endforeach; ?>
  <div class="mp-form-group"><label>Amendment reason *</label><input type="text" class="mp-form-control" id="amendReason"></div>
  <button class="mp-qa-btn green" onclick="doAmend()"><i class="fa fa-edit"></i> Submit amendment</button>
</div>
<?php endif; ?>

<?php if(!empty($amendments)): ?>
<div class="ass-sec">
  <h4>Amendment History</h4>
  <?php foreach($amendments as $am): ?>
    <div class="ass-amend">
      <b><?= htmlspecialchars($am->amended_by_name ?: 'system'); ?></b> · <?= $am->created_at; ?><br>
      <i><?= htmlspecialchars($am->reason); ?></i>
      <ul style="margin:6px 0 0 16px;">
        <?php foreach(json_decode($am->changes_json, true) ?: array() as $ch): ?>
          <?php $fromValue = is_array($ch['from'] ?? null) ? implode(', ', $ch['from']) : (string)($ch['from'] ?? ''); $toValue = is_array($ch['to'] ?? null) ? implode(', ', $ch['to']) : (string)($ch['to'] ?? ''); ?>
          <li><?= htmlspecialchars($ch['label']); ?>: <s><?= htmlspecialchars($fromValue); ?></s> → <b><?= htmlspecialchars($toValue); ?></b></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
var CSRF_BODY = { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' };
var ASS_ID = <?= (int)($assessment->id ?? 0); ?>;
var activeRecognition = null;

function collectAnswers(){
  var data = {encounter_id: <?= (int)$enc->id; ?>, template_id: <?= (int)$template->id; ?>, assessment_id: ASS_ID};
  $('#assForm').find('[name^="ans__"]').each(function(){
    var key=$(this).attr('name');
    if(this.type === 'checkbox'){
      if(!this.checked) return;
      if(!Array.isArray(data[key])) data[key]=[];
      data[key].push($(this).val());
    } else data[key]=$(this).val();
  });
  return $.extend({}, CSRF_BODY, data);
}
function saveDraft(){
  $.post('<?= base_url('assessments/save'); ?>', collectAnswers(), function(res){
    if(res.status==='success'){ toastr.success(res.message); if(!ASS_ID){ ASS_ID = res.assessment_id; history.replaceState({},'', '<?= base_url('assessments/form/'); ?>'+ASS_ID); $('#downloadLink').attr('href','<?= base_url('assessments/download/'); ?>'+ASS_ID).show(); } }
    else toastr.error(res.message||'Failed');
  }, 'json');
}
$('.ass-voice').on('click', function(){
  var button=this, target=document.querySelector('[name="'+button.getAttribute('data-target')+'"]');
  var Speech=window.SpeechRecognition||window.webkitSpeechRecognition;
  if(!Speech){ toastr.error('Voice input is not supported by this browser. Try Chrome or Safari.'); return; }
  if(activeRecognition){ activeRecognition.stop(); activeRecognition=null; return; }
  var recognition=new Speech(); activeRecognition=recognition; recognition.lang='en-NG'; recognition.interimResults=false; recognition.continuous=true;
  button.classList.add('listening'); button.innerHTML='<i class="fa fa-stop"></i> Stop';
  recognition.onresult=function(event){ var text=''; for(var i=event.resultIndex;i<event.results.length;i++){ if(event.results[i].isFinal) text+=event.results[i][0].transcript+' '; } if(text){ target.value=(target.value ? target.value.trim()+' ' : '')+text.trim(); target.dispatchEvent(new Event('input',{bubbles:true})); } };
  recognition.onerror=function(){ toastr.error('Voice input could not start. Check microphone permission.'); };
  recognition.onend=function(){ button.classList.remove('listening'); button.innerHTML='<i class="fa fa-microphone"></i> Voice'; if(activeRecognition===recognition) activeRecognition=null; };
  recognition.start();
});
function finalizeAss(){
  $.post('<?= base_url('assessments/save'); ?>', collectAnswers(), function(res){
    if(res.status!=='success'){ toastr.error(res.message||'Failed'); return; }
    ASS_ID = res.assessment_id;
    $.post('<?= base_url('assessments/finalize/'); ?>'+ASS_ID, CSRF_BODY, function(r2){
      if(r2.status==='success'){ toastr.success(r2.message); setTimeout(()=>location.reload(),700); }
      else toastr.error(r2.message||'Failed');
    }, 'json');
  }, 'json');
}
function doAmend(){
  var reason = $('#amendReason').val();
  if(!reason){ toastr.error('An amendment reason is required'); return; }
  var data = $.extend({}, CSRF_BODY, {reason:reason});
  var n = 0;
  $('.amend-field').each(function(){ if($(this).val() !== ''){ data['amend['+$(this).data('key')+']'] = $(this).val(); n++; } });
  $('.amend-check-group').each(function(){
    var values=[]; $(this).find('input:checked').each(function(){ values.push($(this).val()); });
    if(values.length){ data['amend['+$(this).data('key')+']'] = values; n++; }
  });
  if(!n){ toastr.error('No amended values entered'); return; }
  $.post('<?= base_url('assessments/amend/'); ?>'+ASS_ID, data, function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed');
  }, 'json');
}
</script>
