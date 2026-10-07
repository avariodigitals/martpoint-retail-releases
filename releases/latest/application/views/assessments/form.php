<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.ass-sec{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:16px!important;margin-bottom:14px!important}
.ass-sec h4{font-size:12px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 12px 0!important}
.ass-req{color:#DC2626!important}
.ass-lock{background:#F8FAFC!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;padding:10px 12px!important;font-size:12px!important;margin-bottom:8px!important}
.ass-amend{border-left:3px solid #8B5CF6!important;padding:8px 12px!important;margin:8px 0!important;font-size:12px!important;background:#FAF5FF!important}
.prov-banner{background:#FEF3C7!important;border:1px solid #F59E0B!important;color:#92400E!important;font-size:12px!important;padding:8px 12px!important;border-radius:8px!important;margin-bottom:14px!important}
.final-banner{background:#D1FAE5!important;border:1px solid #10B981!important;color:#065F46!important;font-size:12px!important;padding:8px 12px!important;border-radius:8px!important;margin-bottom:14px!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($template->name); ?></h2>
    <div class="mp-page-sub">
      <?= htmlspecialchars($patient->customer_name ?? ''); ?> (<?= htmlspecialchars($patient->patient_code ?? ''); ?>)
      · <?= htmlspecialchars($enc->encounter_code); ?>
      · Assessor: <?= htmlspecialchars($assessment->assessor_name ?? $this->session->userdata('inv_username')); ?>
    </div>
  </div>
  <a href="<?= base_url('care_queue/encounter/' . (int)$enc->id); ?>" class="mp-qa-btn"><i class="fa fa-arrow-left"></i> Back to visit</a>
</div>

<?php if($template->provisional): ?>
<div class="prov-banner"><i class="fa fa-exclamation-triangle"></i>
  <b>Provisional template.</b> This is a stand-in assessment pending the agreed five-page clinical form — do not treat it as the final approved document.</div>
<?php endif; ?>

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
        <div class="mp-form-group">
          <label><?= htmlspecialchars($f['label'] ?? $f['key']); ?><?= !empty($f['required']) ? ' <span class="ass-req">*</span>' : ''; ?></label>
          <?php if($isFinal): ?>
            <div class="ass-lock"><?= $v !== '' ? nl2br(htmlspecialchars((string)$v)) : '<span style="color:#94a3b8;">—</span>'; ?></div>
          <?php elseif(($f['type'] ?? 'text') === 'textarea'): ?>
            <textarea class="mp-form-control" name="ans__<?= $k; ?>" rows="3"><?= htmlspecialchars((string)$v); ?></textarea>
          <?php elseif(($f['type'] ?? '') === 'select'): ?>
            <select class="mp-form-control" name="ans__<?= $k; ?>">
              <option value="">—</option>
              <?php foreach(($f['options'] ?? array()) as $opt): ?>
                <option value="<?= htmlspecialchars($opt); ?>" <?= $v === $opt ? 'selected' : ''; ?>><?= htmlspecialchars($opt); ?></option>
              <?php endforeach; ?>
            </select>
          <?php else: ?>
            <input type="text" class="mp-form-control" name="ans__<?= $k; ?>" value="<?= htmlspecialchars((string)$v); ?>">
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
</form>

<?php if($isFinal && $can['amend']): ?>
<div class="ass-sec">
  <h4>Record an Amendment (originals are preserved)</h4>
  <?php foreach($sections as $sec): foreach(($sec['fields'] ?? array()) as $f):
    $k = $sec['key'] . '_' . $f['key'];
  ?>
    <div class="mp-form-group">
      <label><?= htmlspecialchars($f['label'] ?? $f['key']); ?> <small style="color:#94a3b8;">current: <?= htmlspecialchars((string)($answers[$k] ?? '—')); ?></small></label>
      <input type="text" class="mp-form-control amend-field" data-key="<?= $k; ?>" placeholder="Amended value (leave blank to keep current)">
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
          <li><?= htmlspecialchars($ch['label']); ?>: <s><?= htmlspecialchars((string)$ch['from']); ?></s> → <b><?= htmlspecialchars((string)$ch['to']); ?></b></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
var CSRF_BODY = { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' };
var ASS_ID = <?= (int)($assessment->id ?? 0); ?>;

function collectAnswers(){
  var data = {encounter_id: <?= (int)$enc->id; ?>, template_id: <?= (int)$template->id; ?>, assessment_id: ASS_ID};
  $('#assForm').find('[name^="ans__"]').each(function(){ data[$(this).attr('name')] = $(this).val(); });
  return $.extend({}, CSRF_BODY, data);
}
function saveDraft(){
  $.post('<?= base_url('assessments/save'); ?>', collectAnswers(), function(res){
    if(res.status==='success'){ toastr.success(res.message); if(!ASS_ID){ ASS_ID = res.assessment_id; history.replaceState({},'', '<?= base_url('assessments/form/'); ?>'+ASS_ID); } }
    else toastr.error(res.message||'Failed');
  }, 'json');
}
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
  if(!n){ toastr.error('No amended values entered'); return; }
  $.post('<?= base_url('assessments/amend/'); ?>'+ASS_ID, data, function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed');
  }, 'json');
}
</script>
