<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.ap-filters{display:flex!important;gap:10px!important;margin-bottom:16px!important;flex-wrap:wrap!important;align-items:center!important}
.ap-badge{font-size:11px!important;font-weight:700!important;padding:4px 10px!important;border-radius:20px!important;white-space:nowrap!important;text-transform:capitalize!important}
.ap-badge.requested{background:#DBEAFE!important;color:#1D4ED8!important}
.ap-badge.proposed{background:#E0E7FF!important;color:#4338CA!important}
.ap-badge.confirmed{background:#D1FAE5!important;color:#065F46!important}
.ap-badge.checked_in{background:#CCFBF1!important;color:#0F766E!important}
.ap-badge.completed{background:#E2E8F0!important;color:#334155!important}
.ap-badge.cancelled{background:#FEE2E2!important;color:#B91C1C!important}
.ap-badge.no_show{background:#FEF3C7!important;color:#B45309!important}
.ap-ref{font-family:monospace!important;font-size:12px!important;color:var(--mp-muted)!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Bookings by branch, clinician and service — check-in happens on the Care Queue</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <a href="<?= base_url('care_queue'); ?>" class="mp-qa-btn blue"><i class="fa fa-list-ol"></i> Care Queue</a>
    <?php if($can_book): ?><button class="mp-qa-btn green" onclick="openApptModal()"><i class="fa fa-plus"></i> Book Appointment</button><?php endif; ?>
  </div>
</div>

<form method="get" action="<?= base_url('appointments'); ?>" class="ap-filters">
  <input type="date" name="date" value="<?= htmlspecialchars($filter_date); ?>" class="mp-form-control" style="max-width:170px;">
  <select name="status" class="mp-form-control" style="max-width:170px;">
    <option value="">All statuses</option>
    <?php foreach(array('requested','proposed','confirmed','checked_in','completed','cancelled','no_show') as $st): ?>
      <option value="<?= $st; ?>" <?= $status_filter === $st ? 'selected' : ''; ?>><?= ucfirst(str_replace('_',' ',$st)); ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="mp-qa-btn blue"><i class="fa fa-search"></i> Filter</button>
  <a href="<?= base_url('appointments?date=' . $filter_date); ?>" class="mp-qa-btn"><i class="fa fa-times"></i> Clear status</a>
</form>

<div class="mp-table-wrap">
  <div class="mp-card-head"><h3><?= date('l, M j Y', strtotime($filter_date)); ?></h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="appt-table" class="table mp-dt-table" width="100%">
        <thead><tr><th>Ref</th><th>Time</th><th>Patient</th><th>Service</th><th>Clinician</th><th>Branch</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($appointments as $a): ?>
          <tr data-id="<?= (int)$a->id; ?>" data-status="<?= htmlspecialchars($a->status); ?>">
            <td><span class="ap-ref"><?= htmlspecialchars($a->booking_ref ?: '—'); ?></span></td>
            <td><?= $a->scheduled_at ? date('H:i', strtotime($a->scheduled_at)) : '—'; ?>
              <div style="font-size:11px;color:var(--mp-muted);"><?= (int)($a->duration_min ?: 0); ?> min</div></td>
            <td class="row-name"><?= htmlspecialchars($a->customer_name ?: '—'); ?>
              <div style="font-size:11px;color:var(--mp-muted);"><?= htmlspecialchars($a->mobile ?: ''); ?></div></td>
            <td><?= htmlspecialchars($a->service_name ?: '—'); ?></td>
            <td><?= htmlspecialchars($a->staff_name ?: 'Unassigned'); ?></td>
            <td><?= htmlspecialchars($a->branch_name ?: '—'); ?></td>
            <td><span class="ap-badge <?= $a->status; ?>"><?= str_replace('_',' ',$a->status); ?></span></td>
            <td>
              <div class="mp-actions">
                <?php if($can_checkin && in_array($a->status, array('requested','proposed','confirmed')) && !$a->arrived_at): ?>
                  <button class="mp-edit" title="Check In" onclick="checkInAppt(<?= (int)$a->id; ?>)"><i class="fa fa-sign-in"></i></button>
                <?php endif; ?>
                <?php if($can_edit && in_array($a->status, array('requested','proposed'))): ?>
                  <button class="mp-edit" title="Confirm" onclick="apptDo(<?= (int)$a->id; ?>,'confirmed')"><i class="fa fa-check"></i></button>
                <?php endif; ?>
                <?php if($can_edit && in_array($a->status, array('requested','proposed','confirmed'))): ?>
                  <button class="mp-edit" title="Reschedule" onclick="rescheduleAppt(<?= (int)$a->id; ?>)"><i class="fa fa-calendar"></i></button>
                <?php endif; ?>
                <?php if($can_edit && $a->status === 'confirmed'): ?>
                  <button class="mp-edit" title="No-Show" onclick="apptDo(<?= (int)$a->id; ?>,'no_show')"><i class="fa fa-user-times"></i></button>
                <?php endif; ?>
                <?php if($can_cancel && in_array($a->status, array('requested','proposed','confirmed'))): ?>
                  <button class="mp-delete" title="Cancel" onclick="cancelAppt(<?= (int)$a->id; ?>)"><i class="fa fa-ban"></i></button>
                <?php endif; ?>
                <button class="mp-edit" title="History" onclick="viewEvents(<?= (int)$a->id; ?>)"><i class="fa fa-history"></i></button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($appointments)): ?><tr><td colspan="8" class="mp-empty-state">No appointments for this filter.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="apptModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="apptForm" method="post" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Book Appointment</h4>
        </div>
        <div class="modal-body">
          <div class="mp-form-group"><label>Patient *</label>
            <select class="mp-form-control" name="patient_id" id="ap_patient" required>
              <option value="">— choose patient —</option>
              <?php foreach($patients as $p): ?>
                <option value="<?= (int)$p->id; ?>"><?= htmlspecialchars(($p->patient_code ? $p->patient_code . ' — ' : '') . $p->customer_name); ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="row">
            <div class="col-sm-6"><div class="mp-form-group"><label>Branch</label>
              <select class="mp-form-control" name="warehouse_id" id="ap_branch">
                <option value="">—</option>
                <?php foreach($branches as $b): ?><option value="<?= (int)$b->id; ?>"><?= htmlspecialchars($b->warehouse_name); ?></option><?php endforeach; ?>
              </select></div></div>
            <div class="col-sm-6"><div class="mp-form-group"><label>Service</label>
              <select class="mp-form-control" name="service_id" id="ap_service">
                <option value="">—</option>
                <?php foreach($services as $s): ?><option value="<?= (int)$s->id; ?>"><?= htmlspecialchars($s->service_name); ?></option><?php endforeach; ?>
              </select></div></div>
          </div>
          <div class="row">
            <div class="col-sm-6"><div class="mp-form-group"><label>Clinician</label>
              <select class="mp-form-control" name="staff_user_id" id="ap_staff">
                <option value="">Unassigned</option>
                <?php foreach($clinicians as $u): ?><option value="<?= (int)$u->id; ?>"><?= htmlspecialchars($u->username); ?></option><?php endforeach; ?>
              </select></div></div>
            <div class="col-sm-6"><div class="mp-form-group"><label>Date &amp; Time *</label><input type="datetime-local" class="mp-form-control" name="scheduled_at" id="ap_when" required></div></div>
          </div>
          <div class="row">
            <div class="col-sm-6"><div class="mp-form-group"><label>Duration (min)</label><input type="number" class="mp-form-control" name="duration_min" id="ap_dur" value="30" min="5" step="5"></div></div>
          </div>
          <div class="mp-form-group"><label>Notes</label><textarea class="mp-form-control" name="notes" id="ap_notes" rows="2"></textarea></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="saveAppt()">Book</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="reschedModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="reschedForm" method="post" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" id="rs_id" value="">
        <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Reschedule</h4></div>
        <div class="modal-body">
          <div class="mp-form-group"><label>New date &amp; time *</label><input type="datetime-local" class="mp-form-control" id="rs_when" required></div>
          <div class="mp-form-group"><label>Reason</label><input type="text" class="mp-form-control" id="rs_reason" placeholder="e.g. Patient requested"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="doReschedule()">Reschedule</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="eventsModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Appointment History</h4></div>
      <div class="modal-body"><ul id="evList" class="list-unstyled" style="font-size:13px;"></ul></div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>

<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };
var CSRF_BODY = { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' };

function openApptModal(){ $('#apptForm')[0].reset(); $('#apptModal').modal('show'); }
function saveAppt(){
  if(!$('#ap_patient').val()){ toastr.error('Choose a patient'); return; }
  if(!$('#ap_when').val()){ toastr.error('Choose date & time'); return; }
  const fd = new FormData(document.getElementById('apptForm'));
  $.ajax({ url:'<?= base_url('appointments/save'); ?>', type:'POST', data:fd, processData:false, contentType:false, dataType:'json',
    success:function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Failed'); } },
    error:function(){ toastr.error('Request failed'); }
  });
}
function apptDo(id, to, note){
  $.post('<?= base_url('appointments/transition/'); ?>'+id, $.extend({}, CSRF_BODY, {to:to, note:note||''}), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 700); } else { toastr.error(res.message || 'Failed'); }
  }, 'json');
}
function cancelAppt(id){
  const reason = prompt('Cancel appointment — reason:', '');
  if(reason === null) return;
  apptDo(id, 'cancelled', reason);
}
function checkInAppt(id){
  $.post('<?= base_url('care_queue/checkin'); ?>', $.extend({}, CSRF_BODY, {appointment_id:id}), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.href='<?= base_url('care_queue'); ?>', 700); } else { toastr.error(res.message || 'Failed'); }
  }, 'json');
}
function rescheduleAppt(id){ $('#rs_id').val(id); $('#reschedModal').modal('show'); }
function doReschedule(){
  if(!$('#rs_when').val()){ toastr.error('New date & time required'); return; }
  $.post('<?= base_url('appointments/reschedule/'); ?>'+$('#rs_id').val(), $.extend({}, CSRF_BODY, {scheduled_at:$('#rs_when').val(), reason:$('#rs_reason').val()}), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Failed'); }
  }, 'json');
}
function viewEvents(id){
  $.get('<?= base_url('appointments/events/'); ?>'+id, function(res){
    let html = '';
    (res.events||[]).forEach(function(e){ html += '<li><b>'+e.event+'</b>'+(e.from_value?' '+e.from_value+' → '+e.to_value:'')+(e.note?' — '+e.note:'')+' <span style="color:#94a3b8">by '+(e.created_by_name||'system')+' · '+e.created_at+'</span></li>'; });
    $('#evList').html(html || '<li>No history</li>');
    $('#eventsModal').modal('show');
  }, 'json');
}
$(document).ready(function(){
  $('#appt-table').DataTable({
    "aLengthMenu": [[10,25,50,100],[10,25,50,100]],
    dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    "order": [[1,"asc"]],
    "responsive": false,
    "columnDefs": [{ "targets": [7], "orderable": false }]
  });
});
</script>
<script>$(".appointments-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
