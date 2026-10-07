<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.ld-stats{display:grid!important;grid-template-columns:repeat(auto-fit,minmax(140px,1fr))!important;gap:12px!important;margin-bottom:20px!important}
.ld-stat{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:16px!important;cursor:pointer!important;transition:border-color .15s,box-shadow .15s!important;text-decoration:none!important;display:block!important}
.ld-stat:hover{border-color:var(--mp-primary)!important;box-shadow:0 4px 14px rgba(15,23,42,.06)!important}
.ld-stat.active{border-color:var(--mp-primary)!important;box-shadow:0 0 0 1px var(--mp-primary) inset!important}
.ld-stat .num{font-size:26px!important;font-weight:800!important;color:var(--mp-text)!important;line-height:1!important}
.ld-stat .lbl{font-size:11px!important;font-weight:700!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin-top:6px!important}
.ld-stat.s-new .num{color:#2563EB!important}
.ld-stat.s-contacted .num{color:#D97706!important}
.ld-stat.s-qualified .num{color:#7C3AED!important}
.ld-stat.s-converted .num{color:#059669!important}
.ld-stat.s-lost .num{color:#DC2626!important}
.ld-filters{display:flex!important;gap:10px!important;margin-bottom:16px!important;flex-wrap:wrap!important;align-items:center!important}
.ld-filters input[type=text]{flex:1!important;min-width:220px!important;padding:10px 14px!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;font-size:14px!important}
.ld-badge{font-size:11px!important;font-weight:700!important;padding:4px 10px!important;border-radius:20px!important;white-space:nowrap!important}
.ld-badge.new{background:#DBEAFE!important;color:#1D4ED8!important}
.ld-badge.contacted{background:#FEF3C7!important;color:#B45309!important}
.ld-badge.qualified{background:#EDE9FE!important;color:#6D28D9!important}
.ld-badge.converted{background:#D1FAE5!important;color:#065F46!important}
.ld-badge.lost{background:#FEE2E2!important;color:#B91C1C!important}
.ld-src{font-size:11px!important;color:var(--mp-muted)!important;text-transform:capitalize!important}
.ld-convert{color:var(--mp-primary)!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Capture enquiries, qualify them and convert them into customers</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <a href="<?= base_url('leads/export'); ?>" class="mp-qa-btn blue"><i class="fa fa-download"></i> Export CSV</a>
    <?php if($can_edit): ?><button class="mp-qa-btn green" onclick="openLeadModal()"><i class="fa fa-plus"></i> Add Lead</button><?php endif; ?>
  </div>
</div>

<div class="ld-stats">
  <a href="<?= base_url('leads'); ?>" class="ld-stat <?= $status_filter === '' ? 'active' : ''; ?>">
    <div class="num"><?= (int)$stats['total']; ?></div><div class="lbl">All Leads</div>
  </a>
  <?php foreach(['new','contacted','qualified','converted','lost'] as $st): ?>
  <a href="<?= base_url('leads?status=' . $st); ?>" class="ld-stat s-<?= $st; ?> <?= $status_filter === $st ? 'active' : ''; ?>">
    <div class="num"><?= (int)($stats[$st] ?? 0); ?></div><div class="lbl"><?= ucfirst($st); ?></div>
  </a>
  <?php endforeach; ?>
</div>

<form method="get" action="<?= base_url('leads'); ?>" class="ld-filters">
  <?php if($status_filter !== ''): ?><input type="hidden" name="status" value="<?= htmlspecialchars($status_filter); ?>"><?php endif; ?>
  <input type="text" name="search" placeholder="Search name, phone, email or interest…" value="<?= htmlspecialchars($search ?? ''); ?>">
  <button type="submit" class="mp-qa-btn blue"><i class="fa fa-search"></i> Search</button>
  <?php if(!empty($search) || $status_filter !== ''): ?><a href="<?= base_url('leads'); ?>" class="mp-qa-btn"><i class="fa fa-times"></i> Clear</a><?php endif; ?>
</form>

<div class="mp-table-wrap">
  <div class="mp-card-head"><h3>Lead Pipeline</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="leads-table" class="table mp-dt-table" width="100%">
        <thead><tr><th>Lead</th><th>Contact</th><th>Source</th><th>Interest</th><?php if(!empty($physio_active)): ?><th>Owner</th><?php endif; ?><th>Status</th><th>Added</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($leads as $l): ?>
          <tr data-id="<?= (int)$l->id; ?>"
              data-name="<?= htmlspecialchars($l->name); ?>"
              data-phone="<?= htmlspecialchars($l->phone ?? ''); ?>"
              data-email="<?= htmlspecialchars($l->email ?? ''); ?>"
              data-source="<?= htmlspecialchars($l->source); ?>"
              data-status="<?= htmlspecialchars($l->status); ?>"
              data-interest="<?= htmlspecialchars($l->interest ?? ''); ?>"
              data-notes="<?= htmlspecialchars($l->notes ?? ''); ?>">
            <td class="row-name"><?= htmlspecialchars($l->name); ?></td>
            <td>
              <?php if($l->phone): ?><div><i class="fa fa-phone" style="color:var(--mp-muted);font-size:11px;"></i> <?= htmlspecialchars($l->phone); ?></div><?php endif; ?>
              <?php if($l->email): ?><div style="font-size:12px;color:var(--mp-muted);"><?= htmlspecialchars($l->email); ?></div><?php endif; ?>
              <?php if(!$l->phone && !$l->email): ?>—<?php endif; ?>
            </td>
            <td><span class="ld-src"><i class="fa fa-tag"></i> <?= htmlspecialchars(str_replace('_',' ',$l->source)); ?></span></td>
            <td style="max-width:220px;"><?= htmlspecialchars(mb_substr($l->interest ?? $l->enquiry ?? '', 0, 60)) . (strlen($l->interest ?? $l->enquiry ?? '') > 60 ? '…' : ''); ?></td>
            <?php if(!empty($physio_active)): ?>
            <td style="font-size:12px;">
              <?= !empty($l->assigned_name) ? htmlspecialchars($l->assigned_name) : '<span style="color:var(--mp-muted);">Unassigned</span>'; ?>
              <?php if(!empty($l->next_followup_at)): ?><div style="color:#B45309;"><i class="fa fa-clock-o"></i> <?= date('M j', strtotime($l->next_followup_at)); ?></div><?php endif; ?>
            </td>
            <?php endif; ?>
            <td>
              <?php if($can_edit && $l->status !== 'converted'): ?>
              <select class="ld-badge <?= $l->status; ?>" style="border:none;cursor:pointer;" onchange="updateLeadStatus(<?= (int)$l->id; ?>, this.value)">
                <?php foreach(['new','contacted','qualified','lost'] as $st): ?>
                  <option value="<?= $st; ?>" <?= $l->status === $st ? 'selected' : ''; ?>><?= ucfirst($st); ?></option>
                <?php endforeach; ?>
              </select>
              <?php else: ?>
              <span class="ld-badge <?= $l->status; ?>"><?= ucfirst($l->status); ?></span>
              <?php endif; ?>
              <?php if($l->status === 'converted' && $l->converted_customer_id): ?>
                <div style="font-size:11px;color:var(--mp-muted);margin-top:4px;"><i class="fa fa-check-circle"></i>
                  <?php if(!empty($physio_active) && !empty($l->patient_id)): ?>
                    <?= htmlspecialchars(mp_label('customer')); ?> <?= htmlspecialchars($l->patient_code ?? ('#' . (int)$l->patient_id)); ?>
                  <?php else: ?>
                    Customer #<?= (int)$l->converted_customer_id; ?>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </td>
            <td><?= !empty($l->created_at) ? date('M j, Y', strtotime($l->created_at)) : '—'; ?></td>
            <td>
              <div class="mp-actions">
                <?php if($can_edit && $l->status !== 'converted'): ?>
                  <?php if(!empty($can_convert_patient)): ?>
                    <button class="mp-edit ld-convert" title="Convert to <?= htmlspecialchars(mp_label('customer')); ?>" onclick="convertLeadToPatient(<?= (int)$l->id; ?>)"><i class="fa fa-heartbeat"></i></button>
                  <?php else: ?>
                    <button class="mp-edit ld-convert" title="Convert to Customer" onclick="convertLead(<?= (int)$l->id; ?>)"><i class="fa fa-user-plus"></i></button>
                  <?php endif; ?>
                  <button class="mp-edit" title="Edit" onclick="editLead(this)"><i class="fa fa-pencil"></i></button>
                  <?php if(!empty($physio_active)): ?>
                    <button class="mp-edit" title="Assign / Follow-up" onclick="assignLead(<?= (int)$l->id; ?>, <?= (int)($l->assigned_to ?? 0); ?>, '<?= htmlspecialchars($l->next_followup_at ?? ''); ?>')"><i class="fa fa-user-md"></i></button>
                  <?php endif; ?>
                <?php endif; ?>
                <?php
                /*
                 * Repair path.
                 *
                 * "Convert to Customer" was originally offered on clinical stores
                 * too, and it marks the lead `converted` WITHOUT creating a
                 * db_patients row — so the lead left the registry and never
                 * reappeared in Patients. Existing leads converted that way are
                 * stranded: the normal convert buttons are hidden once
                 * status='converted', so there was no way back.
                 *
                 * Show the patient-conversion action whenever a converted lead
                 * still has no patient to land in.
                 */
                ?>
                <?php if($l->status === 'converted' && empty($l->patient_id) && !empty($can_convert_patient)): ?>
                  <button class="mp-edit ld-convert" title="Create <?= htmlspecialchars(strtolower(mp_label('customer'))); ?> record (this lead was converted without one)" onclick="convertLeadToPatient(<?= (int)$l->id; ?>)"><i class="fa fa-heartbeat"></i></button>
                <?php endif; ?>
                <?php if(!empty($physio_active)): ?>
                  <button class="mp-edit" title="Activity History" onclick="viewLeadActivity(<?= (int)$l->id; ?>)"><i class="fa fa-history"></i></button>
                  <?php if(!empty($can_book_appt) && !empty($l->patient_id)): ?>
                    <a class="mp-edit" title="Book Appointment" href="<?= base_url('appointments'); ?>"><i class="fa fa-calendar"></i></a>
                  <?php endif; ?>
                <?php endif; ?>
                <?php if($can_edit): ?>
                  <button class="mp-delete" title="Delete" onclick="deleteLead(<?= (int)$l->id; ?>)"><i class="fa fa-trash"></i></button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($leads)): ?><tr><td colspan="<?= !empty($physio_active) ? 8 : 7; ?>" class="mp-empty-state">No leads yet. Add one manually or enable the lead form on your storefront contact section.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="leadModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="leadForm" method="post" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="lead_id" id="lead_id" value="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="leadModalTitle">Add Lead</h4>
        </div>
        <div class="modal-body">
          <div class="mp-form-group"><label>Name *</label><input type="text" class="mp-form-control" name="name" id="ld_name" required></div>
          <div class="row">
            <div class="col-sm-6"><div class="mp-form-group"><label>Phone / WhatsApp</label><input type="text" class="mp-form-control" name="phone" id="ld_phone"></div></div>
            <div class="col-sm-6"><div class="mp-form-group"><label>Email</label><input type="email" class="mp-form-control" name="email" id="ld_email"></div></div>
          </div>
          <div class="row">
            <div class="col-sm-6"><div class="mp-form-group"><label>Source</label>
              <select class="mp-form-control" name="source" id="ld_source">
                <option value="manual">Manual Entry</option>
                <option value="walk_in">Walk-in</option>
                <option value="phone">Phone Call</option>
                <option value="whatsapp">WhatsApp</option>
                <option value="referral">Referral</option>
                <option value="storefront">Website</option>
                <option value="other">Other</option>
              </select></div></div>
            <div class="col-sm-6"><div class="mp-form-group"><label>Status</label>
              <select class="mp-form-control" name="status" id="ld_status">
                <option value="new">New</option>
                <option value="contacted">Contacted</option>
                <option value="qualified">Qualified</option>
                <option value="lost">Lost</option>
              </select></div></div>
          </div>
          <div class="mp-form-group"><label>Interested In</label><input type="text" class="mp-form-control" name="interest" id="ld_interest" placeholder="e.g. Braids package, Toyota Camry 2015, printing quote"></div>
          <div class="mp-form-group"><label>Notes</label><textarea class="mp-form-control" name="notes" id="ld_notes" rows="3"></textarea></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="saveLead()">Save Lead</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php if(!empty($physio_active)): ?>
<div class="modal fade" id="assignModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="assignForm" method="post" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" id="as_lead_id" value="">
        <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Assign Lead</h4></div>
        <div class="modal-body">
          <div class="mp-form-group"><label>Owner</label>
            <select class="mp-form-control" id="as_owner">
              <option value="">Unassigned</option>
              <?php foreach($staff as $u): ?><option value="<?= (int)$u->id; ?>"><?= htmlspecialchars($u->username); ?></option><?php endforeach; ?>
            </select></div>
          <div class="mp-form-group"><label>Next follow-up</label><input type="date" class="mp-form-control" id="as_follow"></div>
          <div class="mp-form-group"><label>Add activity note</label>
            <div style="display:flex;gap:8px;">
              <select class="mp-form-control" id="as_atype" style="max-width:140px;">
                <option value="note">Note</option><option value="call">Call</option><option value="whatsapp">WhatsApp</option><option value="email">Email</option><option value="visit">Visit</option><option value="followup">Follow-up</option>
              </select>
              <input type="text" class="mp-form-control" id="as_note" placeholder="e.g. Called — prefers mornings">
            </div></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="saveAssign()">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="activityModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Lead Activity</h4></div>
      <div class="modal-body"><ul id="ldActList" class="list-unstyled" style="font-size:13px;"></ul></div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };
var CSRF_BODY = { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' };

function openLeadModal(){
  $('#lead_id').val(''); $('#ld_name').val(''); $('#ld_phone').val(''); $('#ld_email').val('');
  $('#ld_source').val('manual'); $('#ld_status').val('new'); $('#ld_interest').val(''); $('#ld_notes').val('');
  $('#leadModalTitle').text('Add Lead');
  $('#leadModal').modal('show');
}
function editLead(btn){
  const tr = $(btn).closest('tr');
  $('#lead_id').val(tr.data('id'));
  $('#ld_name').val(tr.data('name'));
  $('#ld_phone').val(tr.data('phone'));
  $('#ld_email').val(tr.data('email'));
  $('#ld_source').val(tr.data('source'));
  $('#ld_status').val(tr.data('status') === 'converted' ? 'qualified' : tr.data('status'));
  $('#ld_interest').val(tr.data('interest'));
  $('#ld_notes').val(tr.data('notes'));
  $('#leadModalTitle').text('Edit Lead');
  $('#leadModal').modal('show');
}
function saveLead(){
  if(!$('#ld_name').val().trim()){ toastr.error('Lead name is required'); return; }
  const fd = new FormData(document.getElementById('leadForm'));
  $.ajax({ url:'<?= base_url('leads/save'); ?>', type:'POST', data:fd, processData:false, contentType:false, success:function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Failed to save'); } } });
}
function updateLeadStatus(id, status){
  $.post('<?= base_url('leads/update_status/'); ?>'+id, $.extend({}, CSRF_BODY, {status:status}), function(res){ if(res.status==='success'){ toastr.success(res.message); } else { toastr.error(res.message || 'Failed'); setTimeout(()=>location.reload(), 900); } }, 'json');
}
function convertLead(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Convert this lead to a customer?')) return;
    doConvert(id);
    return;
  }
  swal({ title: "Convert to Customer?", text: "A customer record will be created and the lead marked as converted.", icon: "info", buttons: true })
  .then(function(ok){ if(ok) doConvert(id); });
}
function doConvert(id){
  $.post('<?= base_url('leads/convert/'); ?>'+id, CSRF_BODY, function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 900); } else { toastr.error(res.message || 'Conversion failed'); } }, 'json');
}
function deleteLead(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Delete this lead?')) return;
    doDelete(id);
    return;
  }
  swal({ title: "Delete Lead?", text: "This action cannot be undone.", icon: "warning", buttons: true, dangerMode: true })
  .then(function(willDelete){ if(willDelete) doDelete(id); });
}
function doDelete(id){
  $.post('<?= base_url('leads/delete/'); ?>'+id, CSRF_BODY, function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Failed to delete'); } }, 'json');
}
<?php if(!empty($physio_active)): ?>
function convertLeadToPatient(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Convert this lead to a <?= addslashes(mp_label('customer')); ?>?')) return;
    doConvertPatient(id); return;
  }
  swal({ title: "Convert to <?= addslashes(mp_label('customer')); ?>?", text: "A <?= strtolower(addslashes(mp_label('customer'))); ?> record will be created or linked; the lead is marked converted.", icon: "info", buttons: true })
  .then(function(ok){ if(ok) doConvertPatient(id); });
}
function doConvertPatient(id){
  $.post('<?= base_url('leads/convert_to_patient/'); ?>'+id, CSRF_BODY, function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 900); } else { toastr.error(res.message || 'Conversion failed'); } }, 'json');
}
function assignLead(id, owner, follow){
  $('#as_lead_id').val(id); $('#as_owner').val(owner || ''); $('#as_follow').val(follow || ''); $('#as_note').val('');
  $('#assignModal').modal('show');
}
function saveAssign(){
  const id = $('#as_lead_id').val();
  $.post('<?= base_url('leads/assign/'); ?>'+id, $.extend({}, CSRF_BODY, {assigned_to:$('#as_owner').val(), next_followup_at:$('#as_follow').val()}), function(res){
    if(res.status !== 'success'){ toastr.error(res.message || 'Failed'); return; }
    const note = $('#as_note').val().trim();
    if(note){
      $.post('<?= base_url('leads/log_activity/'); ?>'+id, $.extend({}, CSRF_BODY, {activity_type:$('#as_atype').val(), note:note}), function(){
        toastr.success('Saved'); setTimeout(()=>location.reload(), 700);
      }, 'json');
    } else { toastr.success('Saved'); setTimeout(()=>location.reload(), 700); }
  }, 'json');
}
function viewLeadActivity(id){
  $.get('<?= base_url('leads/activities/'); ?>'+id, function(res){
    let html = '';
    (res.activities||[]).forEach(function(a){ html += '<li><b>'+a.activity_type+'</b>'+(a.note?' — '+a.note:'')+' <span style="color:#94a3b8">by '+(a.created_by_name||'system')+' · '+a.created_at+'</span></li>'; });
    $('#ldActList').html(html || '<li>No activity yet</li>');
    $('#activityModal').modal('show');
  }, 'json');
}
<?php endif; ?>
$(document).ready(function(){
  $('#leads-table').DataTable({
    "aLengthMenu": [[10,25,50,100],[10,25,50,100]],
    dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    "order": [[5,"desc"]],
    "responsive": false,
    "columnDefs": [{ "targets": [<?= !empty($physio_active) ? 7 : 6; ?>], "orderable": false }]
  });
});
</script>
<script>$(".leads-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
