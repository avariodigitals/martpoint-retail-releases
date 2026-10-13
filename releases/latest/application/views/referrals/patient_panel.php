<?php
/**
 * Shared patient referral panel — embedded on the patient profile, the
 * assessment form and the visit (encounter) screen.
 *
 * Expected variables (all from Referrals_model::panelData()):
 *   $refp_patient_id, $refp_referrals, $refp_partners, $refp_context, $refp_can
 *
 * Self-contained on purpose: it carries its own styles and namespaced script,
 * so a host screen only has to echo it. Not nested inside another <form>.
 */
$this->load->view('components/dialog');

$refp_partner_total = 0;
foreach((array)$refp_partners as $refp_group){ $refp_partner_total += count($refp_group); }
$refp_today = date('Y-m-d');
?>
<style>
.rpp-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:16px!important;margin-bottom:14px!important}
.rpp-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 12px 0!important}
.rpp-card{border:1px solid var(--mp-border)!important;border-radius:11px!important;padding:12px 13px!important;margin-bottom:10px!important;background:#FCFDFD!important}
.rpp-card.is-closed{opacity:.72}
.rpp-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;flex-wrap:wrap}
.rpp-partner{font-size:13.5px;font-weight:700}
.rpp-flags{display:flex;gap:6px;flex-wrap:wrap;align-items:center}
.rpp-badge{display:inline-block;padding:2px 9px;border-radius:11px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;white-space:nowrap}
.rpp-lab{background:#E0F2FE;color:#075985}.rpp-hospital{background:#EDE9FE;color:#5B21B6}.rpp-doctor{background:#DCFCE7;color:#166534}
.rpp-open{background:#DBEAFE;color:#1E40AF}.rpp-in_progress{background:#FEF3C7;color:#92400E}
.rpp-completed{background:#D1FAE5;color:#065F46}.rpp-cancelled{background:#F1F5F9;color:#64748B}
.rpp-due{background:#FEE2E2;color:#991B1B}.rpp-duetoday{background:#FEF3C7;color:#92400E}.rpp-duesoon{background:#E0F2FE;color:#075985}
.rpp-meta{margin-top:5px;font-size:11.5px;color:var(--mp-muted);line-height:1.65}
.rpp-reason{margin-top:6px;font-size:12.5px;line-height:1.55}
.rpp-acts{display:flex;gap:6px;flex-wrap:wrap;margin-top:9px}
.rpp-meetings{margin-top:10px;border-top:1px solid var(--mp-border);padding-top:9px}
.rpp-meetings h5{margin:0 0 6px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--mp-muted)}
.rpp-meeting{display:flex;gap:9px;align-items:flex-start;padding:6px 0;border-bottom:1px dashed #E2E8F0;font-size:12px}
.rpp-meeting:last-child{border-bottom:0}
.rpp-meeting-date{flex:0 0 82px;font-weight:700;color:var(--mp-primary-dark)}
.rpp-meeting-body{flex:1;line-height:1.55}
.rpp-meeting-del{background:none;border:0;color:#94A3B8;cursor:pointer;font-size:12px;padding:2px 4px}
.rpp-meeting-del:hover{color:#DC2626}
.rpp-form{border:1px dashed var(--mp-border)!important;border-radius:11px!important;padding:12px 13px!important;margin-top:11px!important;background:#FAFCFB!important}
.rpp-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(175px,1fr));gap:9px}
.rpp-field label{display:block;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:var(--mp-muted);margin-bottom:4px}
.rpp-inp{width:100%;box-sizing:border-box;padding:7px 9px!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;font-size:12px!important;background:#fff!important;color:inherit!important}
.rpp-empty{font-size:12.5px;color:var(--mp-muted);padding:4px 0 2px}
.rpp-note{font-size:11.5px;color:var(--mp-muted);margin-top:8px}
</style>

<div class="rpp-box" id="rppBox">
  <h4>Referrals to partners</h4>

  <?php if(empty($refp_referrals) && $refp_partner_total === 0 && empty($refp_can['manage'])): ?>
    <div class="rpp-empty">No referrals recorded for this patient.</div>
  <?php endif; ?>

  <?php if(empty($refp_referrals)): ?>
    <div class="rpp-empty">No referrals yet. Refer this patient to a lab, hospital or doctor below, then record each meeting at the partner so nothing is left outstanding.</div>
  <?php endif; ?>

  <?php foreach((array)$refp_referrals as $ref): ?>
    <?php
      $refClosed = in_array($ref->status, array('completed','cancelled'), true);
      $dueBadge = '';
      if(!$refClosed && !empty($ref->next_due_date)){
        $diff = (int) round((strtotime($ref->next_due_date) - strtotime($refp_today)) / 86400);
        if($diff < 0)        $dueBadge = '<span class="rpp-badge rpp-due">Overdue · '.htmlspecialchars(date('M j', strtotime($ref->next_due_date))).'</span>';
        elseif($diff === 0)  $dueBadge = '<span class="rpp-badge rpp-duetoday">Due today</span>';
        elseif($diff <= 7)   $dueBadge = '<span class="rpp-badge rpp-duesoon">Due in '.$diff.' day'.($diff === 1 ? '' : 's').'</span>';
        else                 $dueBadge = '<span class="rpp-badge rpp-duesoon">Next due '.htmlspecialchars(date('M j', strtotime($ref->next_due_date))).'</span>';
      }
    ?>
    <div class="rpp-card <?= $refClosed ? 'is-closed' : ''; ?>">
      <div class="rpp-top">
        <div>
          <div class="rpp-partner"><?= htmlspecialchars($ref->partner_name); ?></div>
          <div class="rpp-meta">
            Referred <?= htmlspecialchars(date('M j, Y', strtotime($ref->referred_at))); ?>
            <?php if(!empty($ref->referred_by_name)): ?> · by <?= htmlspecialchars($ref->referred_by_name); ?><?php endif; ?>
          </div>
        </div>
        <div class="rpp-flags">
          <span class="rpp-badge rpp-<?= htmlspecialchars($ref->status); ?>"><?= htmlspecialchars(ucfirst(str_replace('_',' ',$ref->status))); ?></span>
          <span class="rpp-badge rpp-<?= htmlspecialchars($ref->partner_type); ?>"><?= htmlspecialchars(ucfirst($ref->partner_type)); ?></span>
          <?php if(!empty($ref->urgency_label) && (string)$ref->urgency !== (string)$refp_default_urgency): ?><span class="rpp-badge rpp-due"><?= htmlspecialchars($ref->urgency_label); ?></span><?php endif; ?>
          <?= $dueBadge; ?>
        </div>
      </div>

      <?php if(!empty($ref->reason)): ?><div class="rpp-reason"><?= htmlspecialchars($ref->reason); ?></div><?php endif; ?>
      <?php if(!empty($ref->notes)): ?><div class="rpp-meta"><?= nl2br(htmlspecialchars($ref->notes)); ?></div><?php endif; ?>
      <?php if(!empty($ref->source_label)): ?><div class="rpp-meta">Recorded from: <?= htmlspecialchars($ref->source_label); ?></div><?php endif; ?>

      <?php if(!empty($refp_can['manage'])): ?>
      <div class="rpp-acts">
        <button class="mp-qa-btn blue" type="button" onclick="rppToggleMeeting(<?= (int)$ref->id; ?>)"><i class="fa fa-plus"></i> Log meeting</button>
        <?php if(!$refClosed): ?>
          <button class="mp-qa-btn green" type="button" onclick="rppStatus(<?= (int)$ref->id; ?>,'completed')"><i class="fa fa-check"></i> Completed</button>
          <button class="mp-qa-btn" type="button" onclick="rppStatus(<?= (int)$ref->id; ?>,'cancelled')">Cancel referral</button>
        <?php else: ?>
          <button class="mp-qa-btn" type="button" onclick="rppStatus(<?= (int)$ref->id; ?>,'open')"><i class="fa fa-undo"></i> Reopen</button>
        <?php endif; ?>
      </div>
      <form class="rpp-form" id="rppMeetingForm<?= (int)$ref->id; ?>" style="display:none" onsubmit="return rppSaveMeeting(event, <?= (int)$ref->id; ?>)">
        <div class="rpp-grid">
          <div class="rpp-field"><label>Meeting date *</label><input class="rpp-inp" type="date" name="meeting_date" value="<?= htmlspecialchars($refp_today); ?>" required></div>
          <div class="rpp-field"><label>Who we met</label><input class="rpp-inp" name="met_with" placeholder="e.g. Dr Bello"></div>
          <div class="rpp-field"><label>Outcome</label><input class="rpp-inp" name="outcome" placeholder="e.g. X-ray done, result pending"></div>
          <div class="rpp-field"><label>Next due</label><input class="rpp-inp" type="date" name="next_due_date"></div>
        </div>
        <div class="rpp-grid" style="margin-top:9px">
          <div class="rpp-field" style="grid-column:1/-1"><label>Notes</label><input class="rpp-inp" name="notes" placeholder="Anything the therapist should carry forward"></div>
        </div>
        <div class="rpp-acts">
          <button class="mp-qa-btn green" type="submit"><i class="fa fa-check"></i> Save meeting</button>
          <button class="mp-qa-btn" type="button" onclick="rppToggleMeeting(<?= (int)$ref->id; ?>)">Close</button>
        </div>
      </form>
      <?php endif; ?>

      <div class="rpp-meetings">
        <h5>Meetings at this partner</h5>
        <?php if(empty($ref->meetings)): ?>
          <div class="rpp-empty">No meetings recorded yet.</div>
        <?php else: ?>
          <?php foreach($ref->meetings as $m): ?>
            <div class="rpp-meeting">
              <div class="rpp-meeting-date"><?= htmlspecialchars(date('M j, Y', strtotime($m->meeting_date))); ?></div>
              <div class="rpp-meeting-body">
                <?php if(!empty($m->met_with)): ?><strong><?= htmlspecialchars($m->met_with); ?></strong><?php endif; ?>
                <?php if(!empty($m->outcome)): ?> · <?= htmlspecialchars($m->outcome); ?><?php endif; ?>
                <?php if(!empty($m->notes)): ?><div class="rpp-meta"><?= nl2br(htmlspecialchars($m->notes)); ?></div><?php endif; ?>
                <?php if(!empty($m->next_due_date)): ?><div class="rpp-meta">Next due <?= htmlspecialchars(date('M j, Y', strtotime($m->next_due_date))); ?></div><?php endif; ?>
              </div>
              <?php if(!empty($refp_can['manage'])): ?>
                <button class="rpp-meeting-del" type="button" title="Remove this meeting" onclick="rppRemoveMeeting(<?= (int)$m->id; ?>)"><i class="fa fa-times"></i></button>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if(!empty($refp_can['manage'])): ?>
    <div class="rpp-form">
      <h4 style="margin-bottom:10px;">Refer this patient to a partner</h4>
      <?php if($refp_partner_total === 0): ?>
        <div class="rpp-empty">
          No referral partners set up yet.
          <a href="<?= base_url('referrals'); ?>">Add labs, hospitals and doctors</a> first, then come back to refer this patient.
        </div>
      <?php else: ?>
        <form onsubmit="return rppSaveReferral(event)">
          <div class="rpp-grid">
            <div class="rpp-field"><label>Referral partner *</label>
              <select class="rpp-inp" name="partner_id" required>
                <option value="">— Choose a partner —</option>
                <?php foreach((array)$refp_partners as $ptype=>$plist): if(empty($plist)) continue; ?>
                  <optgroup label="<?= htmlspecialchars(ucfirst($ptype) === 'Lab' ? 'Labs' : (ucfirst($ptype) === 'Doctor' ? 'Doctors' : 'Hospitals')); ?>">
                    <?php foreach($plist as $p): ?><option value="<?= (int)$p->id; ?>"><?= htmlspecialchars($p->name . ($p->specialty ? ' — '.$p->specialty : '')); ?></option><?php endforeach; ?>
                  </optgroup>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="rpp-field"><label>Reason *</label>
              <select class="rpp-inp" name="reason" required onchange="rppReasonOther(this)">
                <option value="">— Choose a reason —</option>
                <?php foreach((array)$refp_reasons as $ro): ?>
                  <option value="<?= htmlspecialchars($ro->label); ?>"><?= htmlspecialchars($ro->label); ?></option>
                <?php endforeach; ?>
                <option value="__other">Other…</option>
              </select>
            </div>
            <div class="rpp-field" id="rppReasonOtherWrap" style="display:none">
              <label>Reason (write it in)</label>
              <input class="rpp-inp" name="reason_other" placeholder="e.g. spine clinic review">
            </div>
            <div class="rpp-field"><label>Status on arrival</label>
              <select class="rpp-inp" name="urgency">
                <?php foreach((array)$refp_urgencies as $uo): ?>
                  <option value="<?= htmlspecialchars($uo->code); ?>" <?= (string)$uo->code === (string)$refp_default_urgency ? 'selected' : ''; ?>><?= htmlspecialchars($uo->label); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="rpp-field"><label>First follow-up due</label><input class="rpp-inp" type="date" name="next_due_date"></div>
            <div class="rpp-field" style="grid-column:1/-1"><label>Notes</label><input class="rpp-inp" name="notes" placeholder="What the partner needs to know"></div>
          </div>
          <input type="hidden" name="patient_id" value="<?= (int)$refp_patient_id; ?>">
          <input type="hidden" name="source" value="<?= htmlspecialchars($refp_context['source']); ?>">
          <input type="hidden" name="source_id" value="<?= (int)$refp_context['source_id']; ?>">
          <input type="hidden" name="encounter_id" value="<?= (int)$refp_context['encounter_id']; ?>">
          <input type="hidden" name="assessment_id" value="<?= (int)$refp_context['assessment_id']; ?>">
          <div class="rpp-acts"><button class="mp-qa-btn green" type="submit"><i class="fa fa-plus"></i> Record referral</button></div>
        </form>
      <?php endif; ?>
    </div>
    <div class="rpp-note"><i class="fa fa-info-circle"></i> Referrals are kept on the patient record even after a partner is removed from the directory.</div>
  <?php endif; ?>
</div>

<script>
(function(){
  if(window.__rppBound) return;
  window.__rppBound = true;

  var CSRF_BODY = { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' };

  function post(url, data, okMsg){
    $.post(url, $.extend({}, CSRF_BODY, data || {}), function(res){
      if(res.status === 'success'){
        cqToast(res.message || okMsg || 'Saved', 'success');
        setTimeout(function(){ location.reload(); }, 650);
      } else cqToast(res.message || 'Failed', 'danger');
    }, 'json');
  }

  window.rppToggleMeeting = function(refId){
    var f = document.getElementById('rppMeetingForm' + refId);
    if(f) f.style.display = (f.style.display === 'none') ? '' : 'none';
  };
  window.rppSaveMeeting = function(e, refId){
    e.preventDefault();
    var data = {};
    $(e.target).serializeArray().forEach(function(i){ data[i.name] = i.value; });
    post('<?= base_url('referrals/meeting/'); ?>' + refId, data, 'Meeting recorded');
    return false;
  };
  window.rppReasonOther = function(sel){
    var wrap = document.getElementById('rppReasonOtherWrap');
    if(!wrap) return;
    var other = sel.value === '__other';
    wrap.style.display = other ? '' : 'none';
    var inp = wrap.querySelector('input');
    if(inp){ inp.required = other; if(!other) inp.value = ''; }
  };
  window.rppSaveReferral = function(e){
    e.preventDefault();
    var data = {};
    $(e.target).serializeArray().forEach(function(i){ data[i.name] = i.value; });
    // "Other…" is a way in, never a reason of its own.
    if(data.reason === '__other') data.reason = '';
    post('<?= base_url('referrals/save'); ?>', data, 'Referral recorded');
    return false;
  };
  window.rppStatus = function(refId, status){
    post('<?= base_url('referrals/status/'); ?>' + refId, {status: status});
  };
  window.rppRemoveMeeting = function(id){
    mpConfirmAsync('Remove this meeting entry from the referral history?', {title:'Remove meeting', okText:'Remove', danger:true}).then(function(ok){
      if(!ok) return;
      post('<?= base_url('referrals/remove_meeting/'); ?>' + id, {}, 'Meeting removed');
    });
  };
})();
</script>
