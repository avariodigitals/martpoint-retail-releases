<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); $patient_term = mp_label('customer'); ?>
<style>
.pt-stats{display:grid!important;grid-template-columns:repeat(auto-fit,minmax(140px,1fr))!important;gap:12px!important;margin-bottom:20px!important}
.pt-stat{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:16px!important;cursor:pointer!important;transition:border-color .15s,box-shadow .15s!important;text-decoration:none!important;display:block!important}
.pt-stat:hover{border-color:var(--mp-primary)!important;box-shadow:0 4px 14px rgba(15,23,42,.06)!important}
.pt-stat.active{border-color:var(--mp-primary)!important;box-shadow:0 0 0 1px var(--mp-primary) inset!important}
.pt-stat .num{font-size:26px!important;font-weight:800!important;color:var(--mp-text)!important;line-height:1!important}
.pt-stat .lbl{font-size:11px!important;font-weight:700!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin-top:6px!important}
.pt-stat.s-active .num{color:#059669!important}
.pt-stat.s-deceased .num{color:#475569!important}
.pt-stat.s-inactive .num{color:#D97706!important}
.pt-stat.s-new .num{color:#2563EB!important}
.pt-filters{display:flex!important;gap:10px!important;margin-bottom:16px!important;flex-wrap:wrap!important;align-items:center!important}
.pt-filters input[type=text]{flex:1!important;min-width:220px!important;padding:10px 14px!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;font-size:14px!important}
.pt-badge{font-size:11px!important;font-weight:700!important;padding:4px 10px!important;border-radius:20px!important;white-space:nowrap!important}
.pt-badge.active{background:#D1FAE5!important;color:#065F46!important}
.pt-badge.inactive{background:#FEF3C7!important;color:#B45309!important}
.pt-badge.deceased{background:#E2E8F0!important;color:#475569!important}
.pt-badge.dup{background:#FEE2E2!important;color:#B91C1C!important}
.pt-code{font-family:monospace!important;font-size:12px!important;color:var(--mp-muted)!important}
.pt-dup-panel{background:#FEF3C7!important;border:1px solid #F59E0B!important;border-radius:10px!important;padding:12px!important;margin:10px 0!important;font-size:13px!important}
.pt-dup-panel ul{margin:6px 0 0 18px!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Register and manage <?= strtolower($patient_term); ?> records — clinical identity linked to billing</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <?php if($can_export): ?><a href="<?= base_url('patients/export'); ?>" class="mp-qa-btn blue"><i class="fa fa-download"></i> Export CSV</a><?php endif; ?>
    <?php if($can_add): ?><button class="mp-qa-btn green" onclick="openPatientModal()"><i class="fa fa-plus"></i> Register <?= htmlspecialchars($patient_term); ?></button><?php endif; ?>
  </div>
</div>

<div class="pt-stats">
  <a href="<?= base_url('patients'); ?>" class="pt-stat <?= $status_filter === '' ? 'active' : ''; ?>">
    <div class="num"><?= (int)$stats['total']; ?></div><div class="lbl">All <?= htmlspecialchars($patient_term); ?>s</div>
  </a>
  <a href="<?= base_url('patients?status=active'); ?>" class="pt-stat s-active <?= $status_filter === 'active' ? 'active' : ''; ?>">
    <div class="num"><?= (int)$stats['active']; ?></div><div class="lbl">Active</div>
  </a>
  <a href="<?= base_url('patients?status=inactive'); ?>" class="pt-stat s-inactive <?= $status_filter === 'inactive' ? 'active' : ''; ?>">
    <div class="num"><?= (int)$stats['inactive']; ?></div><div class="lbl">Inactive</div>
  </a>
  <a href="<?= base_url('patients?status=deceased'); ?>" class="pt-stat s-deceased <?= $status_filter === 'deceased' ? 'active' : ''; ?>">
    <div class="num"><?= (int)$stats['deceased']; ?></div><div class="lbl">Deceased</div>
  </a>
  <a href="<?= base_url('patients'); ?>" class="pt-stat s-new">
    <div class="num"><?= (int)$stats['new_this_month']; ?></div><div class="lbl">New This Month</div>
  </a>
</div>

<form method="get" action="<?= base_url('patients'); ?>" class="pt-filters">
  <?php if($status_filter !== ''): ?><input type="hidden" name="status" value="<?= htmlspecialchars($status_filter); ?>"><?php endif; ?>
  <input type="text" name="search" placeholder="Search name, phone, email or <?= strtolower($patient_term); ?> code…" value="<?= htmlspecialchars($search ?? ''); ?>">
  <button type="submit" class="mp-qa-btn blue"><i class="fa fa-search"></i> Search</button>
  <?php if(!empty($search) || $status_filter !== ''): ?><a href="<?= base_url('patients'); ?>" class="mp-qa-btn"><i class="fa fa-times"></i> Clear</a><?php endif; ?>
</form>

<div class="mp-table-wrap">
  <div class="mp-card-head"><h3><?= htmlspecialchars($patient_term); ?> Register</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="patients-table" class="table mp-dt-table" width="100%">
        <thead><tr><th>Code</th><th><?= htmlspecialchars($patient_term); ?></th><th>Contact</th><th>Gender / DOB</th><th>Next of Kin</th><th>Status</th><th>Registered</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($patients as $p): ?>
          <tr data-id="<?= (int)$p->id; ?>"
              data-code="<?= htmlspecialchars($p->patient_code ?? ''); ?>"
              data-name="<?= htmlspecialchars($p->customer_name ?? ''); ?>"
              data-mobile="<?= htmlspecialchars($p->mobile ?? ''); ?>"
              data-email="<?= htmlspecialchars($p->email ?? ''); ?>"
              data-phone="<?= htmlspecialchars($p->phone ?? ''); ?>"
              data-gender="<?= htmlspecialchars($p->gender ?? ''); ?>"
              data-dob="<?= htmlspecialchars($p->dob ?? ''); ?>"
              data-marital="<?= htmlspecialchars($p->marital_status ?? ''); ?>"
              data-occupation="<?= htmlspecialchars($p->occupation ?? ''); ?>"
              data-blood="<?= htmlspecialchars($p->blood_group ?? ''); ?>"
              data-nok-name="<?= htmlspecialchars($p->nok_name ?? ''); ?>"
              data-nok-phone="<?= htmlspecialchars($p->nok_phone ?? ''); ?>"
              data-nok-rel="<?= htmlspecialchars($p->nok_relationship ?? ''); ?>"
              data-address="<?= htmlspecialchars($p->address ?? ''); ?>"
              data-city="<?= htmlspecialchars($p->city ?? ''); ?>">
            <td><span class="pt-code"><?= htmlspecialchars($p->patient_code ?: '—'); ?></span></td>
            <td class="row-name"><a href="<?= base_url('patients/profile/'.(int)$p->id); ?>"><?= htmlspecialchars($p->customer_name); ?></a>
              <?php if($p->duplicate_of_id): ?><div><span class="pt-badge dup">Duplicate of #<?= (int)$p->duplicate_of_id; ?></span></div><?php endif; ?>
            </td>
            <td>
              <?php if($p->mobile): ?><div><i class="fa fa-phone" style="color:var(--mp-muted);font-size:11px;"></i> <?= htmlspecialchars($p->mobile); ?></div><?php endif; ?>
              <?php if($p->email): ?><div style="font-size:12px;color:var(--mp-muted);"><?= htmlspecialchars($p->email); ?></div><?php endif; ?>
              <?php if(!$p->mobile && !$p->email): ?>—<?php endif; ?>
            </td>
            <td style="font-size:12px;">
              <?= $p->gender ? ucfirst($p->gender) : '—'; ?><?= $p->dob ? ' · ' . date('M j, Y', strtotime($p->dob)) : ''; ?>
              <?php if($p->blood_group && $p->blood_group !== 'unknown'): ?><div style="color:var(--mp-muted);"><?= htmlspecialchars($p->blood_group); ?></div><?php endif; ?>
            </td>
            <td style="font-size:12px;">
              <?= $p->nok_name ? htmlspecialchars($p->nok_name) . ($p->nok_relationship ? ' (' . htmlspecialchars($p->nok_relationship) . ')' : '') : '—'; ?>
              <?= $p->nok_phone ? '<div style="color:var(--mp-muted);">' . htmlspecialchars($p->nok_phone) . '</div>' : ''; ?>
            </td>
            <td>
              <?php if($p->deceased): ?><span class="pt-badge deceased">Deceased<?= $p->deceased_date ? ' · ' . date('M j, Y', strtotime($p->deceased_date)) : ''; ?></span>
              <?php else: ?><span class="pt-badge <?= $p->status ? 'active' : 'inactive'; ?>"><?= $p->status ? 'Active' : 'Inactive'; ?></span><?php endif; ?>
            </td>
            <td><?= !empty($p->created_date) ? date('M j, Y', strtotime($p->created_date)) : '—'; ?></td>
            <td>
              <div class="mp-actions">
                <a class="mp-edit" title="Profile" href="<?= base_url('patients/profile/'.(int)$p->id); ?>"><i class="fa fa-user"></i></a>
                <?php if($can_edit && !$p->deceased): ?>
                  <button class="mp-edit" title="Edit" onclick="editPatient(this)"><i class="fa fa-pencil"></i></button>
                  <button class="mp-delete" title="Mark Deceased" onclick="markDeceased(<?= (int)$p->id; ?>)"><i class="fa fa-bed"></i></button>
                <?php endif; ?>
                <?php if($can_edit && $p->deceased): ?>
                  <button class="mp-edit" title="Correct Deceased Entry" onclick="unmarkDeceased(<?= (int)$p->id; ?>)"><i class="fa fa-undo"></i></button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($patients)): ?><tr><td colspan="8" class="mp-empty-state">No <?= strtolower($patient_term); ?>s registered yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="patientModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="patientForm" method="post" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="patient_id" id="patient_id" value="">
        <input type="hidden" name="confirm_duplicate" id="confirm_duplicate" value="0">
        <input type="hidden" name="link_customer_id" id="link_customer_id" value="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="patientModalTitle">Register <?= htmlspecialchars($patient_term); ?></h4>
        </div>
        <div class="modal-body">
          <div id="dupWarning" class="pt-dup-panel" style="display:none;"></div>
          <div class="mp-form-group"><label>Full Name *</label><input type="text" class="mp-form-control" name="name" id="pt_name" required></div>
          <div class="row">
            <div class="col-sm-6"><div class="mp-form-group"><label>Mobile / WhatsApp</label><input type="text" class="mp-form-control" name="mobile" id="pt_mobile"></div></div>
            <div class="col-sm-6"><div class="mp-form-group"><label>Alt. Phone</label><input type="text" class="mp-form-control" name="phone" id="pt_phone"></div></div>
          </div>
          <div class="row">
            <div class="col-sm-6"><div class="mp-form-group"><label>Email</label><input type="email" class="mp-form-control" name="email" id="pt_email"></div></div>
            <div class="col-sm-6"><div class="mp-form-group"><label>Date of Birth</label><input type="date" class="mp-form-control" name="dob" id="pt_dob"></div></div>
          </div>
          <div class="row">
            <div class="col-sm-4"><div class="mp-form-group"><label>Gender</label>
              <select class="mp-form-control" name="gender" id="pt_gender">
                <option value="">—</option><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option>
              </select></div></div>
            <div class="col-sm-4"><div class="mp-form-group"><label>Marital Status</label><input type="text" class="mp-form-control" name="marital_status" id="pt_marital"></div></div>
            <div class="col-sm-4"><div class="mp-form-group"><label>Blood Group</label>
              <select class="mp-form-control" name="blood_group" id="pt_blood">
                <option value="">—</option><option>A+</option><option>A-</option><option>B+</option><option>B-</option><option>AB+</option><option>AB-</option><option>O+</option><option>O-</option><option value="unknown">Unknown</option>
              </select></div></div>
          </div>
          <div class="mp-form-group"><label>Occupation</label><input type="text" class="mp-form-control" name="occupation" id="pt_occupation"></div>
          <div class="mp-form-group"><label>Address</label><input type="text" class="mp-form-control" name="address" id="pt_address"></div>
          <div class="row">
            <div class="col-sm-6"><div class="mp-form-group"><label>City</label><input type="text" class="mp-form-control" name="city" id="pt_city"></div></div>
          </div>
          <div class="mp-form-group"><label>Next of Kin — Name</label><input type="text" class="mp-form-control" name="nok_name" id="pt_nok_name"></div>
          <div class="row">
            <div class="col-sm-6"><div class="mp-form-group"><label>NOK Phone</label><input type="text" class="mp-form-control" name="nok_phone" id="pt_nok_phone"></div></div>
            <div class="col-sm-6"><div class="mp-form-group"><label>Relationship</label><input type="text" class="mp-form-control" name="nok_relationship" id="pt_nok_rel" placeholder="e.g. Spouse, Parent"></div></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="savePatient()">Save <?= htmlspecialchars($patient_term); ?></button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="deceasedModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="deceasedForm" method="post" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="patient_id" id="dec_patient_id" value="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Record Death</h4>
        </div>
        <div class="modal-body">
          <p style="font-size:13px;color:var(--mp-muted);">This suspends the patient portal, suppresses reminders and prevents new bookings. The record is never deleted. The entry is audited — your name and the time are recorded.</p>
          <div class="mp-form-group"><label>Date of Death</label><input type="date" class="mp-form-control" name="deceased_date" id="dec_date" value="<?= date('Y-m-d'); ?>"></div>
          <div class="mp-form-group"><label>Reason *</label><textarea class="mp-form-control" name="reason" id="dec_reason" rows="2" placeholder="e.g. Family informed clinic of death on …" required></textarea></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" onclick="confirmDeceased()">Record Death</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };
var CSRF_BODY = { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' };

function openPatientModal(){
  $('#patientForm')[0].reset();
  $('#patient_id, #link_customer_id').val(''); $('#confirm_duplicate').val(0);
  $('#dupWarning').hide().html('');
  $('#patientModalTitle').text('Register <?= addslashes($patient_term); ?>');
  $('#patientModal').modal('show');
}
function editPatient(btn){
  const tr = $(btn).closest('tr');
  openPatientModal();
  $('#patient_id').val(tr.data('id'));
  $('#pt_name').val(tr.data('name')); $('#pt_mobile').val(tr.data('mobile'));
  $('#pt_phone').val(tr.data('phone')); $('#pt_email').val(tr.data('email'));
  $('#pt_dob').val(tr.data('dob')); $('#pt_gender').val(tr.data('gender'));
  $('#pt_marital').val(tr.data('marital')); $('#pt_occupation').val(tr.data('occupation'));
  $('#pt_blood').val(tr.data('blood')); $('#pt_address').val(tr.data('address'));
  $('#pt_city').val(tr.data('city'));
  $('#pt_nok_name').val(tr.data('nokName')); $('#pt_nok_phone').val(tr.data('nokPhone'));
  $('#pt_nok_rel').val(tr.data('nokRel'));
  $('#patientModalTitle').text('Edit ' + tr.data('name'));
}
function savePatient(){
  if(!$('#pt_name').val().trim()){ toastr.error('Patient name is required'); return; }
  const fd = new FormData(document.getElementById('patientForm'));
  $.ajax({ url:'<?= base_url('patients/save'); ?>', type:'POST', data:fd, processData:false, contentType:false, dataType:'json',
    success:function(res){
      if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); }
      else if(res.status==='duplicates'){
        let html = '<b>Possible duplicate record(s):</b><ul>';
        (res.duplicates||[]).forEach(function(d){ html += '<li>'+ (d.customer_name||'') +' — '+ (d.patient_code||'') +' '+ (d.mobile||'') +'</li>'; });
        html += '</ul>Review the list before registering. Press Save again to confirm this is a different person.';
        $('#dupWarning').html(html).show();
        $('#confirm_duplicate').val(1);
      }
      else { toastr.error(res.message || 'Failed to save'); }
    },
    error:function(){ toastr.error('Request failed'); }
  });
}
function markDeceased(id){
  $('#dec_patient_id').val(id);
  $('#deceasedModal').modal('show');
}
function confirmDeceased(){
  if(!$('#dec_reason').val().trim()){ toastr.error('A reason is required to record a death'); return; }
  const fd = new FormData(document.getElementById('deceasedForm'));
  $.ajax({ url:'<?= base_url('patients/mark_deceased/'); ?>'+$('#dec_patient_id').val(), type:'POST', data:fd, processData:false, contentType:false, dataType:'json',
    success:function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 900); } else { toastr.error(res.message || 'Failed'); } },
    error:function(){ toastr.error('Request failed'); }
  });
}
function unmarkDeceased(id){
  const reason = prompt('Correct deceased flag — state the reason (audited):', 'Recorded in error');
  if(reason === null) return;
  if(!reason.trim()){ toastr.error('A correction reason is required'); return; }
  $.post('<?= base_url('patients/unmark_deceased/'); ?>'+id, $.extend({}, CSRF_BODY, {reason:reason}), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 900); } else { toastr.error(res.message || 'Failed'); }
  }, 'json');
}
$(document).ready(function(){
  $('#patients-table').DataTable({
    "aLengthMenu": [[10,25,50,100],[10,25,50,100]],
    dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    "order": [[6,"desc"]],
    "responsive": false,
    "columnDefs": [{ "targets": [7], "orderable": false }]
  });
});
</script>
<script>$(".patients-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
