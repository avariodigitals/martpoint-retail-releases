<?php $this->load->view('admin/desktop/_styles'); $CI =& get_instance(); ?>
<style>
.tp-card{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:12px!important}
.tp-badge{font-size:10px!important;font-weight:700!important;padding:2px 8px!important;border-radius:12px!important}
.tp-draft{background:#F1F5F9!important;color:#475569!important}
.tp-active{background:#DCFCE7!important;color:#166534!important}
.tp-completed{background:#E0E7FF!important;color:#4338CA!important}
.tp-cancelled{background:#FEE2E2!important;color:#991B1B!important}
.tp-meta{font-size:12px!important;color:var(--mp-muted)!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= htmlspecialchars($patient->customer_name ?? ''); ?> — plans link to assessments and care episodes</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <?php if($can_add): ?><a href="<?= base_url('treatment_plans/add/' . $patient->id); ?>" class="mp-qa-btn green"><i class="fa fa-plus"></i> New Plan</a><?php endif; ?>
    <a href="<?= base_url('patient_funds/index/' . $patient->id); ?>" class="mp-qa-btn blue"><i class="fa fa-wallet"></i> Funds</a>
  </div>
</div>

<?php if(empty($plans)): ?>
<div class="tp-card tp-meta">No treatment plans yet.</div>
<?php endif; ?>

<?php foreach($plans as $p):
  $clin = $this->db->select('username,first_name,last_name')->where('id', $p->clinician_id)->get('db_users')->row(); ?>
<div class="tp-card">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
    <div>
      <a href="<?= base_url('treatment_plans/view/' . $p->id); ?>" style="font-weight:800;font-size:15px;"><?= htmlspecialchars($p->title ?: $p->plan_code); ?></a>
      <span class="tp-badge tp-<?= $p->status; ?>"><?= strtoupper($p->status); ?></span>
      <span class="tp-badge tp-draft">v<?= (int)$p->version; ?></span>
      <span class="tp-badge <?= $p->care_setting === 'inpatient' ? 'tp-cancelled' : 'tp-active'; ?>"><?= strtoupper($p->care_setting); ?></span>
    </div>
    <div class="tp-meta">
      <?= htmlspecialchars($p->plan_code); ?> ·
      Clinician: <?= htmlspecialchars($clin ? trim($clin->first_name . ' ' . $clin->last_name) ?: $clin->username : '—'); ?> ·
      <?= htmlspecialchars($p->created_date); ?>
    </div>
  </div>
  <?php if($p->goals): ?><div class="tp-meta" style="margin-top:6px;"><?= nl2br(htmlspecialchars($p->goals)); ?></div><?php endif; ?>
</div>
<?php endforeach; ?>
