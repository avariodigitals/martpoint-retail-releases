<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.ps-card{max-width:760px!important;background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:18px!important}
.ps-card .mp-form-group{max-width:420px!important}.ps-note{font-size:12px!important;color:var(--mp-muted)!important;line-height:1.55!important;margin:8px 0 16px!important}
</style>
<div class="mp-page-head">
  <div>
    <h2>Physio Document Settings</h2>
    <div class="mp-page-sub">Clinical document download security for the Physio workspace.</div>
  </div>
</div>

<div class="ps-card">
  <h4 style="margin:0 0 8px;font-weight:800;">Assessment PDF password</h4>
  <p class="ps-note">This secret is embedded as the password on downloaded Physio assessment PDFs. Only MD/clinical-director authority can change it. Leave it known only to authorised clinical users.</p>
  <form id="physioDocSettings" onsubmit="return false;">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    <div class="mp-form-group">
      <label>Document secret <?= !empty($secret_set) ? '<small style="color:#64748b;">saved — enter a new value to replace</small>' : '<span class="text-danger">*</span>'; ?></label>
      <input type="password" class="mp-form-control" name="physio_document_secret" autocomplete="new-password" placeholder="<?= !empty($secret_set) ? 'Saved — type new secret to change' : 'Enter document password'; ?>">
    </div>
    <button type="button" class="mp-qa-btn green" onclick="savePhysioDocSettings()"><i class="fa fa-save"></i> Save settings</button>
  </form>
</div>
<script>
function savePhysioDocSettings(){
  $.post('<?= base_url('physio_settings/save_documents'); ?>', $('#physioDocSettings').serialize(), function(res){
    if(res.status === 'success'){ toastr.success(res.message); setTimeout(function(){ location.reload(); }, 650); }
    else toastr.error(res.message || 'Could not save settings');
  }, 'json').fail(function(){ toastr.error('Could not save settings'); });
}
</script>
