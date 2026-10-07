<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.cq-board{display:grid!important;grid-template-columns:repeat(auto-fit,minmax(230px,1fr))!important;gap:12px!important;margin-bottom:20px!important}
.cq-col{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:12px!important;min-height:120px!important}
.cq-col h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 10px 0!important}
.cq-card{border:1px solid var(--mp-border)!important;border-radius:10px!important;padding:10px!important;margin-bottom:8px!important;background:#fff!important;font-size:13px!important}
.cq-card .nm{font-weight:700!important}
.cq-card .meta{font-size:11px!important;color:var(--mp-muted)!important;margin-top:2px!important}
.cq-card .acts{margin-top:8px!important;display:flex!important;gap:6px!important;flex-wrap:wrap!important}
.cq-card .acts .btn-xs{font-size:11px!important;padding:4px 8px!important;border-radius:6px!important;border:1px solid var(--mp-border)!important;background:var(--mp-surface,#fff)!important;cursor:pointer!important}
.cq-card .acts .btn-xs.primary{background:var(--mp-primary)!important;color:#fff!important;border-color:var(--mp-primary)!important}
.cq-arrive{background:#FFFBEB!important;border:1px solid #F59E0B!important;border-radius:14px!important;padding:14px!important;margin-bottom:18px!important}
.cq-badge{font-size:10px!important;font-weight:700!important;padding:2px 8px!important;border-radius:12px!important;background:#E0E7FF!important;color:#4338CA!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Today's patient flow — queue position is separate from appointment and billing status</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <a href="<?= base_url('appointments'); ?>" class="mp-qa-btn blue"><i class="fa fa-calendar"></i> Appointments</a>
    <?php if($can_checkin): ?><button class="mp-qa-btn green" onclick="openWalkin()"><i class="fa fa-user-plus"></i> Walk-in Check-in</button><?php endif; ?>
  </div>
</div>

<?php if(!empty($arrivals) && $can_checkin): ?>
<div class="cq-arrive">
  <b><i class="fa fa-bell"></i> Expected arrivals — <?= count($arrivals); ?> booked patient(s) not yet checked in:</b>
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
    <?php foreach($arrivals as $a): ?>
    <button class="mp-qa-btn" onclick="checkinAppt(<?= (int)$a->id; ?>)">
      <i class="fa fa-sign-in"></i> <?= date('H:i', strtotime($a->scheduled_at)); ?> — <?= htmlspecialchars($a->customer_name ?: 'Patient'); ?>
    </button>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<div class="cq-board">
<?php
$stageLabels = array(
  'waiting_nurse' => 'Waiting for Nurse',
  'nursing_intake' => 'Nursing Intake',
  'waiting_physio' => 'Waiting for ' . mp_label('staff'),
  'with_physio' => 'With ' . mp_label('staff'),
  'awaiting_finance' => 'Awaiting Finance / Next',
  'closed' => 'Visit Closed',
);
$stagePerms = array(
  'nursing_intake' => $can_vitals,
  'waiting_physio' => $can_vitals,
  'with_physio' => $can_physio,
  'awaiting_finance' => $can_physio,
  'closed' => $can_physio || $can_finance,
  'waiting_nurse' => $can_checkin,
);
foreach($stageLabels as $stage => $label):
  $cards = isset($queue[$stage]) ? $queue[$stage] : array();
?>
  <div class="cq-col">
    <h4><?= htmlspecialchars($label); ?> <span class="cq-badge"><?= count($cards); ?></span></h4>
    <?php foreach($cards as $e): ?>
    <div class="cq-card">
      <div class="nm"><?= htmlspecialchars($e->customer_name ?: '—'); ?></div>
      <div class="meta">
        <?= htmlspecialchars($e->patient_code ?: ''); ?> · <?= htmlspecialchars($e->encounter_code ?: ''); ?>
        <br>In <?= $e->checkin_at ? date('H:i', strtotime($e->checkin_at)) : '—'; ?>
        <?= $e->clinician_name ? ' · Dr/Clin: ' . htmlspecialchars($e->clinician_name) : ''; ?>
        <?= $e->handler_name ? ' · With: ' . htmlspecialchars($e->handler_name) : ''; ?>
      </div>
      <div class="acts">
        <?php if($stage === 'waiting_nurse' && $can_vitals): ?>
          <button class="btn-xs primary" onclick="vitalsPrompt(<?= (int)$e->id; ?>)">Start Intake</button>
        <?php endif; ?>
        <?php if($stage === 'nursing_intake' && $can_vitals): ?>
          <button class="btn-xs primary" onclick="completeIntake(<?= (int)$e->id; ?>)">Complete Intake → <?= htmlspecialchars(mp_label('staff')); ?></button>
          <button class="btn-xs" onclick="vitalsPrompt(<?= (int)$e->id; ?>)">Vitals</button>
        <?php endif; ?>
        <?php if($stage === 'waiting_physio' && $can_physio): ?>
          <button class="btn-xs primary" onclick="moveEnc(<?= (int)$e->id; ?>,'with_physio')">Start Consult</button>
        <?php endif; ?>
        <?php if($stage === 'waiting_physio' && $can_checkin): ?>
          <button class="btn-xs" onclick="moveEnc(<?= (int)$e->id; ?>,'waiting_nurse')">Back to Nurse</button>
        <?php endif; ?>
        <?php if($stage === 'with_physio' && $can_physio): ?>
          <button class="btn-xs primary" onclick="moveEnc(<?= (int)$e->id; ?>,'awaiting_finance')">To Finance/Next</button>
          <button class="btn-xs" onclick="closeVisit(<?= (int)$e->id; ?>)">Close Visit</button>
        <?php endif; ?>
        <?php if($stage === 'awaiting_finance' && ($can_finance || $can_physio)): ?>
          <button class="btn-xs primary" onclick="closeVisit(<?= (int)$e->id; ?>)">Close Visit</button>
        <?php endif; ?>
        <a class="btn-xs" href="<?= base_url('care_queue/encounter/' . (int)$e->id); ?>"><i class="fa fa-folder-open"></i> Open</a>
        <?php if($stage !== 'closed'): ?>
          <button class="btn-xs" onclick="viewEncEvents(<?= (int)$e->id; ?>)"><i class="fa fa-history"></i></button>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if(empty($cards)): ?><div style="font-size:12px;color:var(--mp-muted);">Empty</div><?php endif; ?>
  </div>
<?php endforeach; ?>
</div>

<div class="modal fade" id="walkinModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="walkinForm" method="post" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="checkin_key" id="wk_key">
        <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Walk-in Check-in</h4></div>
        <div class="modal-body">
          <div class="mp-form-group"><label>Patient *</label>
            <select class="mp-form-control" name="patient_id" id="wk_patient" required>
              <option value="">— choose patient —</option>
              <?php foreach($patients as $p): ?>
                <option value="<?= (int)$p->id; ?>"><?= htmlspecialchars(($p->patient_code ? $p->patient_code . ' — ' : '') . $p->customer_name); ?></option>
              <?php endforeach; ?>
            </select></div>
          <?php if(!empty($branches)): ?>
          <div class="mp-form-group"><label>Branch</label>
            <select class="mp-form-control" name="warehouse_id" id="wk_branch">
              <option value="">—</option>
              <?php foreach($branches as $b): ?><option value="<?= (int)$b->id; ?>"><?= htmlspecialchars($b->warehouse_name); ?></option><?php endforeach; ?>
            </select></div>
          <?php endif; ?>
          <div class="mp-form-group"><label>Note</label><input type="text" class="mp-form-control" name="note" id="wk_note" placeholder="e.g. Walk-in, knee pain"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="doWalkin()">Check In</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="vitalsModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="vitalsForm" method="post" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" id="vt_id" value="">
        <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Nursing Intake — Vitals</h4></div>
        <div class="modal-body">
          <p style="font-size:11px;color:var(--mp-muted);margin:0 0 10px;">Each reading keeps its own unit and time. Tick <b>Not measured</b> when a vital was not taken — that is recorded explicitly and is never the same as zero.</p>
          <table class="table" style="font-size:12px;">
            <thead><tr><th>Vital</th><th>Value</th><th>Unit</th><th>Not measured</th></tr></thead>
            <tbody id="vt_rows">
              <tr data-key="bp_systolic"><td>Systolic BP</td><td><input type="text" class="mp-form-control vt-val" placeholder="120"></td><td class="vt-unit">mmHg</td><td><input type="checkbox" class="vt-nm"></td></tr>
              <tr data-key="bp_diastolic"><td>Diastolic BP</td><td><input type="text" class="mp-form-control vt-val" placeholder="80"></td><td class="vt-unit">mmHg</td><td><input type="checkbox" class="vt-nm"></td></tr>
              <tr data-key="pulse"><td>Pulse</td><td><input type="text" class="mp-form-control vt-val" placeholder="72"></td><td class="vt-unit">bpm</td><td><input type="checkbox" class="vt-nm"></td></tr>
              <tr data-key="temperature"><td>Temperature</td><td><input type="text" class="mp-form-control vt-val" placeholder="36.8"></td><td class="vt-unit">°C</td><td><input type="checkbox" class="vt-nm"></td></tr>
              <tr data-key="spo2"><td>SpO2</td><td><input type="text" class="mp-form-control vt-val" placeholder="98"></td><td class="vt-unit">%</td><td><input type="checkbox" class="vt-nm"></td></tr>
              <tr data-key="resp_rate"><td>Resp rate</td><td><input type="text" class="mp-form-control vt-val" placeholder="16"></td><td class="vt-unit">/min</td><td><input type="checkbox" class="vt-nm"></td></tr>
              <tr data-key="weight"><td>Weight</td><td><input type="text" class="mp-form-control vt-val" placeholder="70"></td><td class="vt-unit">kg</td><td><input type="checkbox" class="vt-nm"></td></tr>
              <tr data-key="pain_score"><td>Pain score</td><td><input type="number" class="mp-form-control vt-val" min="0" max="10"></td><td class="vt-unit">/10</td><td><input type="checkbox" class="vt-nm"></td></tr>
            </tbody>
          </table>
          <div class="mp-form-group"><label>Measured at (optional — defaults to now)</label><input type="datetime-local" class="mp-form-control" id="vt_at"></div>
          <div class="mp-form-group"><label>Intake notes</label><textarea class="mp-form-control" id="vt_notes" rows="2"></textarea></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-default" onclick="saveVitals(0)">Save Draft</button>
          <button type="button" class="btn btn-primary" onclick="saveVitals(1)">Finalise Vitals</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="encEventsModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Visit History</h4></div>
      <div class="modal-body"><ul id="encEvList" class="list-unstyled" style="font-size:13px;"></ul></div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>

<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };
var CSRF_BODY = { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' };

function checkinAppt(apptId){
  $.post('<?= base_url('care_queue/checkin'); ?>', $.extend({}, CSRF_BODY, {appointment_id:apptId}), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 700); } else { toastr.error(res.message || 'Failed'); }
  }, 'json');
}
function openWalkin(){
  $('#wk_key').val('walkin:' + Date.now() + ':' + Math.random().toString(36).slice(2,10));
  $('#walkinModal').modal('show');
}
function doWalkin(){
  if(!$('#wk_patient').val()){ toastr.error('Choose a patient'); return; }
  const fd = new FormData(document.getElementById('walkinForm'));
  $.ajax({ url:'<?= base_url('care_queue/checkin'); ?>', type:'POST', data:fd, processData:false, contentType:false, dataType:'json',
    success:function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 700); } else { toastr.error(res.message || 'Failed'); } },
    error:function(){ toastr.error('Request failed'); }
  });
}
function moveEnc(id, to){
  $.post('<?= base_url('care_queue/move/'); ?>'+id, $.extend({}, CSRF_BODY, {to:to}), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 700); } else { toastr.error(res.message || 'Failed'); }
  }, 'json');
}
function completeIntake(id){
  $.post('<?= base_url('care_queue/intake_complete/'); ?>'+id, CSRF_BODY, function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 700); } else { toastr.error(res.message || 'Failed'); }
  }, 'json');
}
function closeVisit(id){
  const note = prompt('Close this visit — outcome note:', '');
  if(note === null) return;
  $.post('<?= base_url('care_queue/move/'); ?>'+id, $.extend({}, CSRF_BODY, {to:'closed', note:note}), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 700); } else { toastr.error(res.message || 'Failed'); }
  }, 'json');
}
function vitalsPrompt(id){ $('#vt_id').val(id); $('#vitalsForm')[0].reset(); $('#vt_id').val(id); $('#vitalsModal').modal('show'); }
function saveVitals(finalize){
  const id = $('#vt_id').val();
  const data = $.extend({}, CSRF_BODY, {finalize: finalize ? 1 : 0, vitals_notes: $('#vt_notes').val()});
  const at = $('#vt_at').val() ? $('#vt_at').val().replace('T',' ') + ':00' : '';
  let i = 0;
  $('#vt_rows tr').each(function(){
    const $tr = $(this), nm = $tr.find('.vt-nm').is(':checked'), val = $tr.find('.vt-val').val();
    if(!nm && val === '') return; // untouched row — skip entirely
    data['vit_key['+i+']']   = $tr.data('key');
    data['vit_label['+i+']'] = $tr.find('td:first').text();
    data['vit_value['+i+']'] = val;
    data['vit_unit['+i+']']  = $tr.find('.vt-unit').text();
    data['vit_nm['+i+']']    = nm ? 1 : 0;
    data['vit_at['+i+']']    = at;
    i++;
  });
  if(i === 0){ toastr.error('Enter at least one vital or mark it not measured'); return; }
  $.post('<?= base_url('care_queue/vitals/'); ?>'+id, data, function(res){
    if(res.status==='success'){
      // Move into intake stage for those still waiting for nurse, then reload
      $.post('<?= base_url('care_queue/move/'); ?>'+id, $.extend({}, CSRF_BODY, {to:'nursing_intake'}), function(){
        toastr.success(res.message); setTimeout(()=>location.reload(), 700);
      }, 'json');
    } else { toastr.error(res.message || 'Failed'); }
  }, 'json');
}
function viewEncEvents(id){
  $.get('<?= base_url('care_queue/events/'); ?>'+id, function(res){
    let html = '';
    (res.events||[]).forEach(function(e){ html += '<li><b>'+e.event+'</b>'+(e.from_stage?' '+e.from_stage+' → '+e.to_stage:'')+(e.note?' — '+e.note:'')+' <span style="color:#94a3b8">by '+(e.created_by_name||'system')+' · '+e.created_at+'</span></li>'; });
    $('#encEvList').html(html || '<li>No history</li>');
    $('#encEventsModal').modal('show');
  }, 'json');
}
</script>
<script>$(".care_queue-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
