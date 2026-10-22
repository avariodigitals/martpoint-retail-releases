<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $this->load->view('components/dialog'); ?>
<?php $this->load->view('components/card_lanes'); ?>
<?php
$CI =& get_instance();
$patient_term = mp_label('customer');
$storeId = get_current_store_id();
$patientCardStoreName = trim((string)($SITE_TITLE ?? 'MartPoint'));
$patientCardLogo = function_exists('mp_get_store_theme_setting') ? trim((string)mp_get_store_theme_setting($storeId, 'store_logo', '')) : '';
if($patientCardLogo === '' || !file_exists(FCPATH . ltrim($patientCardLogo, '/'))){
  $patientCardLogo = function_exists('get_site_logo') ? get_site_logo() : '';
}
$patientCardLogoUrl = $patientCardLogo !== '' ? base_url(ltrim($patientCardLogo, '/')) : '';
$patientCardPhoto = '';
foreach(array('customer_photo','patient_photo','photo','image_path','profile_picture') as $photoField){
  if(empty($patient->{$photoField})) continue;
  $candidate = trim((string)$patient->{$photoField});
  if(preg_match('/^https?:\/\//i', $candidate)){
    $patientCardPhoto = $candidate;
    break;
  }
  $candidate = ltrim($candidate, '/');
  if($candidate !== '' && file_exists(FCPATH . $candidate)){
    $patientCardPhoto = base_url($candidate);
    break;
  }
}
?>
<style>
.pp-head{display:flex;gap:18px;align-items:flex-start;flex-wrap:wrap;margin-bottom:20px}
.pp-avatar{width:64px;height:64px;border-radius:16px;background:var(--mp-primary,#2563EB);color:#fff;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:800;overflow:hidden}
.pp-avatar.has-photo{background:transparent}
.pp-avatar img{width:100%;height:100%;object-fit:cover;display:block}
.pp-meta{font-size:13px;color:var(--mp-muted);line-height:1.7}
.pp-badge{font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;display:inline-block}
.pp-badge.active{background:#D1FAE5;color:#065F46}
.pp-badge.inactive{background:#FEF3C7;color:#B45309}
.pp-badge.deceased{background:#E2E8F0;color:#475569}
/* min-width:0 lets the card shrink below its content width — without it a
   wide table stretches the lane past the card and off the screen. */
.pp-card{background:var(--mp-surface,#fff);border:1px solid var(--mp-border);border-radius:14px;padding:18px;min-width:0;overflow-x:auto}
.pp-card h4{margin:0 0 12px;font-size:13px;text-transform:uppercase;letter-spacing:.5px;color:var(--mp-muted)}
.pp-card .table{width:100%;min-width:440px;margin-bottom:0}
.pp-kv{display:grid;grid-template-columns:130px 1fr;gap:6px 12px;font-size:13px}
.pp-kv dt{color:var(--mp-muted)}
.pp-kv dd{margin:0}
.pp-money{font-size:20px;font-weight:800}
.pp-ok{color:#059669}.pp-due{color:#DC2626}
.pp-empty{font-size:13px;color:var(--mp-muted);padding:10px 0}
/* Episode mini-cards — four across on wide screens, fewer as it narrows. */
.pp-ep-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:10px}
@media(max-width:1280px){.pp-ep-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:980px){.pp-ep-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:640px){.pp-ep-grid{grid-template-columns:1fr}}
.pp-ep{border:1px solid var(--mp-border);border-radius:12px;padding:12px;display:flex;flex-direction:column;gap:8px;min-width:0}
.pp-ep-visits{display:flex;flex-direction:column;gap:6px;border-top:1px dashed var(--mp-border);padding-top:8px}
.pp-ep-link{display:flex;align-items:center;gap:8px;text-decoration:none;color:var(--mp-ink);font-size:12px;border:1px solid var(--mp-border);border-radius:8px;padding:5px 8px;min-width:0}
.pp-ep-link:hover{border-color:var(--mp-primary);background:#EFF6FF}
.pp-appts{display:flex;flex-direction:column;gap:8px}
.pp-appt{display:grid;grid-template-columns:minmax(96px,130px) minmax(0,1fr) auto;gap:12px;align-items:center;border:1px solid var(--mp-border);border-radius:10px;padding:10px 12px;background:#fbfdfc}
.pp-appt-date{font-size:12px;font-weight:800;color:var(--mp-ink);line-height:1.35}
.pp-appt-date small,.pp-appt-sub{display:block;font-size:11px;font-weight:700;color:var(--mp-muted);margin-top:2px}
.pp-appt-title{font-size:13px;font-weight:800;color:var(--mp-ink);line-height:1.3;word-break:break-word}
.pp-appt-status{justify-self:end;white-space:nowrap}
@media(max-width:640px){.pp-appt{grid-template-columns:1fr}.pp-appt-status{justify-self:start}}
.pp-card-compact{display:grid;grid-template-columns:56px minmax(0,1fr) auto;gap:12px;align-items:center;border:1px solid var(--mp-border);border-radius:12px;background:#fbfdfc;padding:12px;min-width:0}
.pp-card-compact-logo{width:56px;height:44px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:#eef8f5;overflow:hidden}
.pp-card-compact-logo img{max-width:52px;max-height:40px;object-fit:contain;display:block}
.pp-card-compact-logo span{font-size:18px;font-weight:900;color:var(--mp-primary,#176753)}
.pp-card-compact-title{font-size:13px;font-weight:900;color:var(--mp-ink);line-height:1.25;word-break:break-word}
.pp-card-compact-meta{font-size:11px;color:var(--mp-muted);font-weight:700;margin-top:3px}
.pp-card-modal{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:24px;background:rgba(15,23,42,.58)}
.pp-card-modal.open{display:flex}
.pp-card-modal-panel{width:min(100%,720px);max-height:calc(100vh - 48px);overflow:auto;background:#fff;border-radius:16px;border:1px solid rgba(15,61,53,.16);box-shadow:0 28px 80px rgba(15,23,42,.28);padding:16px}
.pp-card-modal-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}
.pp-card-modal-head strong{font-size:14px;color:var(--mp-ink)}
.pp-card-modal-close{width:32px;height:32px;border:1px solid var(--mp-border);border-radius:8px;background:#fff;color:var(--mp-ink);display:flex;align-items:center;justify-content:center}
.pp-card-preview-frame{max-width:100%;overflow:auto;padding:4px 2px 14px}
.pp-patient-card{position:relative;overflow:hidden;width:620px;height:391px;border-radius:20px;padding:28px 28px 0;box-sizing:border-box;color:#07131a;background:linear-gradient(110deg,#fff 0%,#f8fffd 58%,#dff5f1 100%);border:1px solid rgba(15,61,53,.12);box-shadow:0 18px 45px rgba(15,61,53,.14);-webkit-print-color-adjust:exact;print-color-adjust:exact}
.pp-patient-card:before{content:"";position:absolute;right:-90px;top:36px;width:260px;height:260px;border-radius:48%;background:linear-gradient(135deg,rgba(23,103,83,.08),rgba(23,103,83,.28));opacity:.72}
.pp-patient-card:after{content:"";position:absolute;left:210px;right:-80px;bottom:92px;height:116px;border-radius:999px;background:linear-gradient(135deg,rgba(23,103,83,0),rgba(23,103,83,.16));transform:rotate(-14deg)}
.pp-card-logo{position:relative;z-index:2;display:flex;align-items:center;height:72px;max-width:46%}
.pp-card-logo img{width:auto;max-width:210px;height:72px;object-fit:contain;border-radius:0;background:transparent}
.pp-card-logo-text{font-size:19px;font-weight:950;letter-spacing:.04em;line-height:1.08;text-transform:uppercase;color:#07131a}
.pp-card-logo-text small{display:block;margin-top:6px;font-size:11px;font-weight:800;letter-spacing:.18em;color:var(--mp-primary,#176753)}
.pp-card-type{position:absolute;right:30px;top:32px;z-index:2;font-size:15px;font-weight:950;text-transform:uppercase;letter-spacing:.06em;padding:12px 30px;border-radius:999px;background:linear-gradient(135deg,var(--mp-primary,#176753),#075546);color:#fff;box-shadow:0 10px 22px rgba(7,85,70,.2)}
.pp-card-body{position:relative;z-index:2;display:grid;grid-template-columns:112px minmax(0,1fr);gap:22px;align-items:center;margin:25px 188px 0 28px;min-height:112px;text-align:left}
.pp-card-photo{width:112px;height:112px;border-radius:24px;overflow:hidden;background:linear-gradient(135deg,#eef8f5,#d8eee8);display:flex;align-items:center;justify-content:center;color:var(--mp-primary,#176753);font-size:42px;font-weight:950;box-shadow:inset 0 0 0 1px rgba(15,61,53,.08)}
.pp-card-photo img{width:100%;height:100%;object-fit:cover;display:block}
.pp-card-info{display:flex;flex-direction:column;justify-content:center;gap:12px;min-width:0}
.pp-card-line{display:grid;grid-template-columns:48px minmax(0,1fr);gap:10px;align-items:baseline;min-width:0}
.pp-card-line span{font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.06em;color:rgba(7,19,26,.58)}
.pp-card-line strong{font-weight:900;color:#06121a;line-height:1.08;min-width:0;word-break:break-word}
.pp-card-line.name strong{font-size:24px}
.pp-card-line.code strong{font-size:18px;letter-spacing:.02em}
.pp-card-return{position:absolute;left:32px;right:32px;bottom:104px;z-index:3;font-size:11px;font-weight:800;letter-spacing:.02em;color:rgba(7,19,26,.66);text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pp-card-strip{position:absolute;left:0;right:0;bottom:0;z-index:3;display:grid;grid-template-columns:220px 1fr;gap:26px;align-items:center;padding:21px 32px;background:linear-gradient(135deg,var(--mp-primary,#176753),#075546);color:#fff}
.pp-card-id-label{font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;opacity:.88}
.pp-card-id-value{font-size:27px;font-weight:950;letter-spacing:.04em;line-height:1.1;margin-top:7px}
.pp-card-barcode{background:rgba(255,255,255,.74);border:1px solid rgba(255,255,255,.68);border-radius:18px;padding:11px 18px;display:flex;justify-content:center;min-width:0}
.pp-card-barcode img{height:56px;max-width:100%;display:block}
.pp-card-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
.pp-card-actions .mp-qa-btn{font-size:12px!important;padding:7px 10px!important}
.pp-ledger-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
.pp-ledger-actions .mp-qa-btn{font-size:12px!important;padding:7px 10px!important}
@media(max-width:640px){.pp-card-compact{grid-template-columns:48px minmax(0,1fr)}.pp-card-compact-logo{width:48px;height:40px}.pp-card-compact .mp-qa-btn{grid-column:1 / -1;width:100%}}
@media print{.no-print{display:none!important}.pp-patient-card{box-shadow:none!important}}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($patient->customer_name); ?></h2>
    <div class="mp-page-sub">
      <a href="<?= base_url('patients'); ?>">&larr; <?= htmlspecialchars($patient_term); ?> Register</a>
      &nbsp;·&nbsp; <span style="font-family:monospace;"><?= htmlspecialchars($patient->patient_code ?: '—'); ?></span>
      <?php if(function_exists('physio_can') && physio_can('portal_manage')): ?>
        &nbsp;·&nbsp; <a href="<?= base_url('patients/portal_access/'.$patient->id); ?>">Portal Access</a>
      <?php endif; ?>
      <?php if(function_exists('physio_can') && physio_can('plans_view')): ?>
        &nbsp;·&nbsp; <a href="<?= base_url('treatment_plans/index/'.$patient->id); ?>">Treatment Plans</a>
      <?php endif; ?>
    </div>
  </div>
  <div>
    <?php if($patient->deceased): ?><span class="pp-badge deceased">Deceased<?= $patient->deceased_date ? ' · ' . date('M j, Y', strtotime($patient->deceased_date)) : ''; ?></span>
    <?php else: ?><span class="pp-badge <?= $patient->status ? 'active' : 'inactive'; ?>"><?= $patient->status ? 'Active' : 'Inactive'; ?></span><?php endif; ?>
    <?php if(!empty($can_edit) && !$patient->deceased): ?>
      <div style="margin-top:10px;"><a href="<?= base_url('patients?edit=' . (int)$patient->id); ?>" class="mp-qa-btn green"><i class="fa fa-pencil"></i> Edit patient</a></div>
    <?php endif; ?>
    <?php if($patient->deceased): ?>
      <div class="pp-meta" style="margin-top:8px;">
        Recorded by <b><?= htmlspecialchars($deceased_by ?: '—'); ?></b><?= $patient->updated_at ? ' · ' . date('M j, Y g:ia', strtotime($patient->updated_at)) : ''; ?>
        <?php if($patient->deceased_notes): ?><br>Reason: <?= htmlspecialchars($patient->deceased_notes); ?><?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="pp-head">
  <?php $avatar_url = $patientCardPhoto; $avatar_name = $patient->customer_name; $avatar_size = 64; $avatar_class = 'pp-avatar'; $this->load->view('components/person_avatar'); ?>
  <div class="pp-meta">
    <?php if($patient->mobile): ?><div><i class="fa fa-phone"></i> <?= htmlspecialchars($patient->mobile); ?></div><?php endif; ?>
    <?php if($patient->email): ?><div><i class="fa fa-envelope"></i> <?= htmlspecialchars($patient->email); ?></div><?php endif; ?>
    <?php if($patient->address || $patient->city): ?><div><i class="fa fa-map-marker"></i> <?= htmlspecialchars(trim(($patient->address ?: '') . ' ' . ($patient->city ?: ''))); ?></div><?php endif; ?>
  </div>
</div>

<div class="mp-lanes">
  <div class="pp-card">
    <h4>Demographics</h4>
    <dl class="pp-kv">
      <dt>Gender</dt><dd><?= $patient->gender ? ucfirst($patient->gender) : '—'; ?></dd>
      <dt>Date of Birth</dt><dd><?= $patient->dob ? date('M j, Y', strtotime($patient->dob)) : '—'; ?></dd>
      <dt>Marital Status</dt><dd><?= htmlspecialchars($patient->marital_status ?: '—'); ?></dd>
      <dt>Occupation</dt><dd><?= htmlspecialchars($patient->occupation ?: '—'); ?></dd>
      <dt>Blood Group</dt><dd><?= ($patient->blood_group && $patient->blood_group !== 'unknown') ? htmlspecialchars($patient->blood_group) : '—'; ?></dd>
    </dl>
  </div>

  <div class="pp-card">
    <h4>Next of Kin</h4>
    <dl class="pp-kv">
      <dt>Name</dt><dd><?= htmlspecialchars($patient->nok_name ?: '—'); ?></dd>
      <dt>Phone</dt><dd><?= htmlspecialchars($patient->nok_phone ?: '—'); ?></dd>
      <dt>Relationship</dt><dd><?= htmlspecialchars($patient->nok_relationship ?: '—'); ?></dd>
    </dl>
  </div>

  <div class="pp-card">
    <h4>Account (billing identity)</h4>
    <dl class="pp-kv">
      <dt>Billing Code</dt><dd><span style="font-family:monospace;"><?= htmlspecialchars($patient->customer_code ?: '—'); ?></span></dd>
      <dt>Advance / Prepaid</dt><dd class="pp-money pp-ok"><?= $CI->currency($patient->tot_advance ?? 0); ?></dd>
      <dt>Balance Due</dt><dd class="pp-money <?= ($patient->sales_due ?? 0) > 0 ? 'pp-due' : 'pp-ok'; ?>"><?= $CI->currency($patient->sales_due ?? 0); ?></dd>
      <?php if(mp_feature_enabled('store_credit')): ?><dt>Store Credit</dt><dd><?= $CI->currency($patient->store_credit_balance ?? 0); ?></dd><?php endif; ?>
      <?php if(mp_feature_enabled('gift_cards')): ?><dt>Gift Card Bal.</dt><dd><?= $CI->currency($patient->gift_card_balance ?? 0); ?></dd><?php endif; ?>
      <dt>Portal</dt><dd><?= htmlspecialchars(ucfirst($patient->portal_status ?: 'none')); ?></dd>
    </dl>
    <div class="pp-ledger-actions">
      <?php if($CI->permissions('customers_view') && !empty($patient->customer_id)): ?>
        <a href="<?= base_url('customers/statement/' . (int)$patient->customer_id); ?>" class="mp-qa-btn green"><i class="fa fa-file-text-o"></i> Customer ledger</a>
      <?php endif; ?>
      <?php if(function_exists('physio_can') && physio_can('patient_funds_view')): ?>
        <a href="<?= base_url('patient_funds/index/' . (int)$patient->id); ?>" class="mp-qa-btn blue"><i class="fa fa-credit-card"></i> Wallet / funds</a>
      <?php endif; ?>
      <?php if(function_exists('physio_can') && physio_can('patient_billing_view')): ?>
        <a href="<?= base_url('patient_billing?patient_id=' . (int)$patient->id); ?>" class="mp-qa-btn"><i class="fa fa-list"></i> Patient bills</a>
      <?php endif; ?>
    </div>
    <div class="pp-empty" style="margin-top:8px;">Ledger is available from the linked billing account; clinical wallet funds are managed in Patient Funds.</div>

    <?php if(isset($reminder_pause)): ?>
    <div style="margin-top:10px;border-top:1px solid var(--mp-border);padding-top:10px;font-size:12px;">
      <strong>Debt reminders</strong>
      <?php if($reminder_pause): ?>
        <div class="pp-badge inactive" style="margin:6px 0">Paused</div>
        <?php if($reminder_pause->reason): ?><div style="color:var(--mp-muted)"><?= htmlspecialchars($reminder_pause->reason); ?></div><?php endif; ?>
        <?php if($reminder_pause->resume_at): ?><div style="color:var(--mp-muted)">Resumes <?= htmlspecialchars($reminder_pause->resume_at); ?></div><?php endif; ?>
        <?php if($can_reminder): ?><button class="mp-qa-btn" onclick="ppResumeReminder(<?= (int)$reminder_pause->id; ?>)">Resume reminders</button><?php endif; ?>
      <?php else: ?>
        <div class="pp-badge active" style="margin:6px 0">Active</div>
        <?php if($can_reminder): ?><button class="mp-qa-btn" onclick="ppPauseReminder(<?= (int)$patient->id; ?>)">Pause reminders</button><?php endif; ?>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="pp-card">
    <h4>Patient Card</h4>
    <div class="pp-card-compact">
      <div class="pp-card-compact-logo">
        <?php if($patientCardLogoUrl): ?>
          <img crossorigin="anonymous" src="<?= htmlspecialchars($patientCardLogoUrl); ?>" alt="Store logo">
        <?php else: ?>
          <span><?= htmlspecialchars(strtoupper(substr((string)$patientCardStoreName, 0, 1))); ?></span>
        <?php endif; ?>
      </div>
      <div style="min-width:0;">
        <div class="pp-card-compact-title"><?= htmlspecialchars($patient->customer_name ?: 'Patient'); ?></div>
        <div class="pp-card-compact-meta">
          <?= htmlspecialchars($patient->patient_code ?: ($patient->customer_code ?: 'PAT-'.(int)$patient->id)); ?>
          · <?= htmlspecialchars($patient->customer_code ?: ($patient->patient_code ?: 'P'.(int)$patient->id)); ?>
        </div>
      </div>
      <button type="button" class="mp-qa-btn blue" onclick="ppShowPatientCardPreview()"><i class="fa fa-eye"></i> Preview</button>
    </div>
  </div>

  <div class="pp-card-modal no-print" id="patientCardModal" aria-hidden="true">
    <div class="pp-card-modal-panel" role="dialog" aria-modal="true" aria-label="Patient card preview">
      <div class="pp-card-modal-head">
        <strong>Patient card preview</strong>
        <button type="button" class="pp-card-modal-close" onclick="ppHidePatientCardPreview()" aria-label="Close"><i class="fa fa-times"></i></button>
      </div>
      <div class="pp-card-preview-frame">
        <div class="pp-patient-card" id="patientCardPreview">
          <div class="pp-card-logo">
            <?php if($patientCardLogoUrl): ?><img crossorigin="anonymous" src="<?= htmlspecialchars($patientCardLogoUrl); ?>" alt="Store logo"><?php endif; ?>
          </div>
          <div class="pp-card-type">Patient</div>
          <div class="pp-card-body">
            <div class="pp-card-photo">
              <?php if($patientCardPhoto): ?>
                <img crossorigin="anonymous" src="<?= htmlspecialchars($patientCardPhoto); ?>" alt="Patient photo">
              <?php else: ?>
                <?= htmlspecialchars(strtoupper(substr((string)($patient->customer_name ?: 'P'), 0, 1))); ?>
              <?php endif; ?>
            </div>
          <div class="pp-card-info">
              <div class="pp-card-line name">
                <span>Name:</span>
                <strong><?= htmlspecialchars($patient->customer_name ?: 'Patient'); ?></strong>
              </div>
            </div>
          </div>
          <div class="pp-card-return">If found, please return to any <?= htmlspecialchars($patientCardStoreName ?: 'MartPoint'); ?> location.</div>
          <div class="pp-card-strip">
            <div>
              <div class="pp-card-id-label">Patient ID</div>
              <div class="pp-card-id-value"><?= htmlspecialchars($patient->customer_code ?: ($patient->patient_code ?: 'P'.(int)$patient->id)); ?></div>
            </div>
            <div class="pp-card-barcode">
              <img crossorigin="anonymous" src="https://bwipjs-api.metafloor.com/?bcid=code128&amp;text=<?= urlencode($patient->patient_code ?: ($patient->customer_code ?: ('P'.$patient->id))); ?>&amp;scale=3&amp;height=12" alt="Patient barcode">
            </div>
          </div>
        </div>
      </div>
      <div class="pp-card-actions">
        <button type="button" class="mp-qa-btn blue" onclick="ppPrintPatientCard('patientCardPreview')"><i class="fa fa-print"></i> Print card</button>
        <button type="button" class="mp-qa-btn" onclick="ppDownloadPatientCard('patientCardPreview','patient-card-<?= htmlspecialchars($patient->patient_code ?: $patient->id, ENT_QUOTES, 'UTF-8'); ?>.png')"><i class="fa fa-download"></i> PNG</button>
      </div>
    </div>
  </div>

  <div class="pp-card">
    <h4>Recent Appointments</h4>
    <?php if(empty($appointments)): ?>
      <div class="pp-empty">No appointments on record.</div>
    <?php else: ?>
      <div class="pp-appts">
        <?php foreach($appointments as $a): ?>
          <div class="pp-appt">
            <div class="pp-appt-date">
              <?= $a->scheduled_at ? date('M j, Y', strtotime($a->scheduled_at)) : '—'; ?>
              <small><?= $a->scheduled_at ? date('g:ia', strtotime($a->scheduled_at)) : ''; ?></small>
            </div>
            <div>
              <div class="pp-appt-title"><?= htmlspecialchars($a->service_name ?: 'Appointment'); ?></div>
              <span class="pp-appt-sub"><?= htmlspecialchars($a->staff_name ?: 'No clinician assigned'); ?></span>
            </div>
            <div class="pp-appt-status">
              <span class="pp-badge <?= in_array($a->status, ['completed','confirmed','checked_in']) ? 'active' : ($a->status === 'cancelled' || $a->status === 'no_show' ? 'deceased' : 'inactive'); ?>"><?= ucfirst(str_replace('_',' ',$a->status)); ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="pp-card">
    <h4>Registration</h4>
    <dl class="pp-kv">
      <dt>Registered</dt><dd><?= $patient->created_date ? date('M j, Y', strtotime($patient->created_date)) : '—'; ?></dd>
      <dt>Registered By</dt><dd><?= htmlspecialchars($patient->created_by ?: '—'); ?></dd>
      <dt>Billing ID</dt><dd><?= (int)$patient->customer_id; ?></dd>
    </dl>
  </div>

</div>

<?php
/**
 * Care episodes — the spine of the record, so they get a full-width band like
 * referrals and the audit trail. Each episode lists its visits with a
 * quick-launch into the visit screen, where the clinical detail lives.
 */
$visitsByEp = array();
foreach(($episode_visits ?? array()) as $v){
  $visitsByEp[(int)$v->episode_id][] = $v;
}
$byType = array('inpatient' => array(), 'outpatient' => array(), 'other' => array());
foreach($episodes as $e){
  $t = in_array($e->episode_type, array('inpatient','outpatient'), true) ? $e->episode_type : 'other';
  $byType[$t][] = $e;
}
$typeHead = array(
  'inpatient'  => array('Inpatient spells', '#EEF2FF', '#4338CA', 'Admitted and staying — the ward spell.'),
  'outpatient' => array('Outpatient visits', '#ECFDF5', '#065F46', 'Seen and home the same day.'),
  'other'      => array('Not yet marked', '#F1F5F9', '#64748B', 'Raised before care type was recorded.'),
);
$episodeLimit = 12;
$shownEpisodes = 0;
?>
<div class="pp-card" style="margin-top:14px;">
  <h4>Care Episodes</h4>
  <?php if(empty($episodes)): ?>
    <div class="pp-empty">No care episodes yet. Episodes open automatically at first check-in.</div>
  <?php else: ?>
    <?php foreach($typeHead as $t => $head): if(empty($byType[$t])) continue; ?>
      <div style="margin:14px 0 0;padding:8px 12px;border-radius:10px;background:<?= $head[1]; ?>;color:<?= $head[2]; ?>;">
        <b style="font-size:12px;text-transform:uppercase;letter-spacing:.4px;"><?= $head[0]; ?></b>
        <span style="font-size:11px;opacity:.85;"> — <?= $head[3]; ?> (<?= count($byType[$t]); ?>)</span>
      </div>
      <div class="pp-ep-grid">
      <?php foreach($byType[$t] as $e): if($shownEpisodes++ >= $episodeLimit) break; ?>
        <?php $evisits = $visitsByEp[(int)$e->id] ?? array(); ?>
        <div class="pp-ep">
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <span style="font-family:monospace;font-weight:700;font-size:13px;"><?= htmlspecialchars($e->episode_code ?: 'EP-'.$e->id); ?></span>
            <span class="pp-badge <?= $e->status === 'open' ? 'active' : 'deceased'; ?>"><?= ucfirst($e->status); ?></span>
          </div>
          <div style="font-size:11px;color:var(--mp-muted);">
            <?= $e->started_at ? date('M j, Y', strtotime($e->started_at)) : '—'; ?> · <?= count($evisits); ?> visit(s)
          </div>
          <?php if(!empty($e->admission_id)): ?>
            <a class="pp-ep-link" href="<?= base_url('inpatient/view/' . (int)$e->admission_id); ?>" style="margin-top:4px;">
              <i class="fa fa-bed" style="color:#4338CA;"></i>
              <span style="font-family:monospace;font-weight:600;"><?= htmlspecialchars($e->admission_code); ?></span>
              <span class="pp-badge <?= ($e->admission_status ?? '') === 'active' ? 'active' : 'deceased'; ?>" style="margin-left:auto;">
                <?= ($e->admission_status ?? '') === 'active' ? 'In the ward' : 'Ward stay finished'; ?>
              </span>
            </a>
          <?php endif; ?>
          <?php if(empty($evisits)): ?>
            <div class="pp-ep-visits"><div style="font-size:11px;color:var(--mp-muted);">No visits yet.</div></div>
          <?php else: ?>
            <div class="pp-ep-visits">
              <?php foreach(array_slice($evisits, 0, 4) as $v): ?>
                <a class="pp-ep-link" href="<?= base_url('care_queue/encounter/' . (int)$v->id); ?>">
                  <i class="fa fa-external-link" style="color:var(--mp-primary);"></i>
                  <span style="font-family:monospace;font-weight:600;"><?= htmlspecialchars($v->encounter_code ?: 'Visit #'.$v->id); ?></span>
                  <span class="pp-badge <?= $v->queue_stage === 'closed' ? 'deceased' : 'active'; ?>" style="margin-left:auto;"><?= htmlspecialchars(mp_code_label($v->queue_stage)); ?></span>
                </a>
              <?php endforeach; ?>
              <?php if(count($evisits) > 4): ?>
                <div style="font-size:11px;color:var(--mp-muted);">+<?= count($evisits) - 4; ?> more visit(s)</div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
    <?php if(count($episodes) > $episodeLimit): ?>
      <div class="pp-empty">Showing the <?= $episodeLimit; ?> most recent episodes — open the register for the full history.</div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php if(!empty($referral_panel)): ?>
<div style="margin-top:14px;"><?= $referral_panel; ?></div>
<?php endif; ?>

<?php
/**
 * Split the event feed into history carried over from the clinic's previous
 * system (Smart Hospital 4.0) and actions taken inside MartPoint.
 *
 * Imported entries are inert narrative: they record what happened, with the
 * original date and source record, but carry no charges, invoices, wallet
 * movement or entitlements. Showing them separately means staff can tell
 * legacy history apart from their own audit trail.
 */
$imported = array();
$auditOnly = array();
foreach($events as $ev){
    if(strpos((string)$ev->event, 'sh4_') === 0){ $imported[] = $ev; }
    else { $auditOnly[] = $ev; }
}
$typeLabels = array(
    'sh4_opd'           => array('Outpatient visit', 'fa-stethoscope'),
    'sh4_ipd'           => array('Inpatient stay',  'fa-bed'),
    'sh4_timeline_note' => array('Clinical note',    'fa-file-text-o'),
    'sh4_bed_history'   => array('Bed allocation',   'fa-th'),
    'sh4_discharge'     => array('Discharge record', 'fa-sign-out'),
);
if(!empty($imported)){
    usort($imported, function($a, $b){ return strcmp((string)$b->created_at, (string)$a->created_at); });
}
?>
<div class="pp-card" style="margin-top:14px;">
    <h4>Audit Log</h4>
    <?php if(empty($auditOnly)): ?>
      <div class="pp-empty">No audited events yet.</div>
    <?php else: ?>
      <table class="table" style="font-size:12px;">
        <thead><tr><th>Event</th><th>Reason</th><th>By</th><th>When</th></tr></thead>
        <tbody>
        <?php foreach($auditOnly as $ev): ?>
          <tr>
            <td><?= htmlspecialchars(mp_code_label($ev->event)); ?></td>
            <td><?= htmlspecialchars(mb_substr($ev->reason ?? '', 0, 80)); ?></td>
            <td><?= htmlspecialchars($ev->created_by_name ?: '—'); ?></td>
            <td><?= $ev->created_at ? date('M j, Y g:ia', strtotime($ev->created_at)) : '—'; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<?php if(!empty($imported)): ?>
  <div class="pp-card" style="margin-top:14px;">
    <h4>Clinical history — imported from previous system</h4>
    <div class="pp-empty" style="padding:0 0 12px;">
      <?= count($imported); ?> record(s) carried over. These are historical notes only —
      no charges, invoices or balances were created from them.
    </div>
    <table class="table" style="font-size:12px;">
      <thead><tr><th style="width:150px;">Date</th><th style="width:170px;">Record</th><th>Detail</th><th style="width:130px;">Source</th></tr></thead>
      <tbody>
      <?php foreach($imported as $ev):
        $meta = json_decode((string)$ev->meta_json, true) ?: array();
        $label = $typeLabels[$ev->event][0] ?? ucfirst(str_replace('_', ' ', (string)$ev->event));
        $icon  = $typeLabels[$ev->event][1] ?? 'fa-history';
        $date  = !empty($meta['date']) ? $meta['date'] : substr((string)$ev->created_at, 0, 10);
        $body  = trim((string)($meta['body'] ?? ''));
      ?>
        <tr>
          <td><?= htmlspecialchars(date('M j, Y', strtotime($date))); ?></td>
          <td><i class="fa <?= htmlspecialchars($icon); ?>"></i> <?= htmlspecialchars($label); ?></td>
          <td>
            <?= htmlspecialchars(mb_substr($body, 0, 220)); ?>
            <?php if(!empty($ev->reason) && $ev->reason !== $label): ?>
              <div style="color:var(--mp-muted);font-size:11px;margin-top:2px;"><?= htmlspecialchars(mb_substr($ev->reason, 0, 90)); ?></div>
            <?php endif; ?>
          </td>
          <td style="color:var(--mp-muted);font-size:11px;">
            <?= htmlspecialchars((string)($meta['source_table'] ?? 'imported')); ?>
            <?php if(!empty($meta['source_id'])): ?>#<?= (int)$meta['source_id']; ?><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<script src="<?= $theme_link; ?>plugins/tableExporter/libs/html2canvas/html2canvas.min.js"></script>
<script>$(".patients-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
function ppShowPatientCardPreview(){
  var modal = document.getElementById('patientCardModal');
  if(!modal) return;
  modal.classList.add('open');
  modal.setAttribute('aria-hidden', 'false');
}
function ppHidePatientCardPreview(){
  var modal = document.getElementById('patientCardModal');
  if(!modal) return;
  modal.classList.remove('open');
  modal.setAttribute('aria-hidden', 'true');
}
document.addEventListener('click', function(e){
  var modal = document.getElementById('patientCardModal');
  if(modal && e.target === modal) ppHidePatientCardPreview();
});
document.addEventListener('keydown', function(e){
  if(e.key === 'Escape') ppHidePatientCardPreview();
});
function ppPrintPatientCard(elId){
  var el = document.getElementById(elId);
  if(!el) return;
  var clone = el.cloneNode(true);
  clone.removeAttribute('id');
  var style = window.getComputedStyle(el);
  var primary = style.getPropertyValue('background-image') || 'linear-gradient(135deg,#176753 0%,#0f3d35 68%,#d9b36c 100%)';
  var win = window.open('', '_blank');
  win.document.write('<html><head><title>Patient Card</title><style>');
  win.document.write('body{margin:0;padding:24px;background:#f8fafc;text-align:center;font-family:Arial,sans-serif;}');
  win.document.write('.pp-patient-card{position:relative;overflow:hidden;width:620px;height:391px;border-radius:20px;padding:28px 28px 0;box-sizing:border-box;color:#07131a;background:'+primary+';border:1px solid rgba(15,61,53,.12);margin:0 auto;-webkit-print-color-adjust:exact;print-color-adjust:exact;}');
  win.document.write('.pp-patient-card:before{content:"";position:absolute;right:-90px;top:36px;width:260px;height:260px;border-radius:48%;background:linear-gradient(135deg,rgba(23,103,83,.08),rgba(23,103,83,.28));opacity:.72}.pp-patient-card:after{content:"";position:absolute;left:210px;right:-80px;bottom:92px;height:116px;border-radius:999px;background:linear-gradient(135deg,rgba(23,103,83,0),rgba(23,103,83,.16));transform:rotate(-14deg)}');
  win.document.write('.pp-card-logo{position:relative;z-index:2;display:flex;align-items:center;height:72px;max-width:46%;text-align:left}.pp-card-logo img{width:auto;max-width:210px;height:72px;object-fit:contain;border-radius:0;background:transparent}.pp-card-logo-text{font-size:19px;font-weight:950;letter-spacing:.04em;line-height:1.08;text-transform:uppercase;color:#07131a}.pp-card-logo-text small{display:block;margin-top:6px;font-size:11px;font-weight:800;letter-spacing:.18em;color:#176753}.pp-card-type{position:absolute;right:30px;top:32px;z-index:2;font-size:15px;font-weight:950;text-transform:uppercase;letter-spacing:.06em;padding:12px 30px;border-radius:999px;background:linear-gradient(135deg,#176753,#075546);color:#fff}');
  win.document.write('.pp-card-body{position:relative;z-index:2;display:grid;grid-template-columns:112px minmax(0,1fr);gap:22px;align-items:center;margin:25px 188px 0 28px;min-height:112px;text-align:left}.pp-card-photo{width:112px;height:112px;border-radius:24px;overflow:hidden;background:linear-gradient(135deg,#eef8f5,#d8eee8);display:flex;align-items:center;justify-content:center;color:#176753;font-size:42px;font-weight:950;box-shadow:inset 0 0 0 1px rgba(15,61,53,.08)}.pp-card-photo img{width:100%;height:100%;object-fit:cover;display:block}.pp-card-info{display:flex;flex-direction:column;justify-content:center;gap:12px;min-width:0}.pp-card-line{display:grid;grid-template-columns:48px minmax(0,1fr);gap:10px;align-items:baseline;min-width:0}.pp-card-line span{font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.06em;color:rgba(7,19,26,.58)}.pp-card-line strong{font-weight:900;color:#06121a;line-height:1.08;min-width:0;word-break:break-word}.pp-card-line.name strong{font-size:24px}.pp-card-return{position:absolute;left:32px;right:32px;bottom:104px;z-index:3;font-size:11px;font-weight:800;letter-spacing:.02em;color:rgba(7,19,26,.66);text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}');
  win.document.write('.pp-card-strip{position:absolute;left:0;right:0;bottom:0;z-index:3;display:grid;grid-template-columns:220px 1fr;gap:26px;align-items:center;padding:21px 32px;background:linear-gradient(135deg,#176753,#075546);color:#fff;text-align:left}.pp-card-id-label{font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;opacity:.88}.pp-card-id-value{font-size:27px;font-weight:950;letter-spacing:.04em;line-height:1.1;margin-top:7px}.pp-card-barcode{background:rgba(255,255,255,.74);border:1px solid rgba(255,255,255,.68);border-radius:18px;padding:11px 18px;display:flex;justify-content:center;min-width:0}.pp-card-barcode img{height:56px;max-width:100%;display:block}');
  win.document.write('</style></head><body>');
  win.document.write(clone.outerHTML);
  win.document.write('</body></html>');
  win.document.close();
  setTimeout(function(){ win.print(); win.close(); }, 350);
}
function ppDownloadPatientCard(elementId, filename){
  var el = document.getElementById(elementId);
  if(!el || typeof html2canvas === 'undefined') return;
  html2canvas(el, {scale:2, useCORS:true, backgroundColor:null}).then(function(canvas){
    var link = document.createElement('a');
    link.download = filename;
    link.href = canvas.toDataURL('image/png');
    link.click();
  });
}
function ppPauseReminder(pid){
  mpPrompt({
    title: 'Pause debt reminders',
    message: 'Why are reminders being paused? The reason is required and stays on the record.',
    placeholder: 'Reason for pausing',
    multiline: true,
    required: true,
    okText: 'Continue'
  }).then(function(reason){
    if(reason === null) return;
    mpPrompt({
      title: 'Resume time (optional)',
      message: 'When should reminders resume? Leave blank to resume them manually later.',
      placeholder: 'Y-m-d H:i',
      okText: 'Pause Reminders'
    }).then(function(resume){
      if(resume === null) return;
      var fd = new FormData();
      fd.append('scope','patient'); fd.append('target', pid);
      fd.append('reason', reason); fd.append('resume_at', resume || '');
      fetch('<?= base_url('debt_reminders/pause'); ?>', {method:'POST', body:fd})
        .then(function(r){ return r.json(); })
        .then(function(d){
          if(d.status === 'success'){ cqSuccess(d.message || 'Reminders paused'); setTimeout(function(){ location.reload(); }, 700); }
          else cqError(d.message || 'Could not pause reminders');
        })
        .catch(function(){ cqError('Could not pause reminders'); });
    });
  });
}
function ppResumeReminder(id){
  mpConfirmAsync('Resume debt reminders for this patient now?').then(function(ok){
    if(!ok) return;
    fetch('<?= base_url('debt_reminders/resume/'); ?>'+id, {method:'POST'})
      .then(function(r){ return r.json(); })
      .then(function(d){
        if(d.status === 'success'){ cqSuccess(d.message || 'Reminders resumed'); setTimeout(function(){ location.reload(); }, 700); }
        else cqError(d.message || 'Could not resume reminders');
      })
      .catch(function(){ cqError('Could not resume reminders'); });
  });
}
</script>
