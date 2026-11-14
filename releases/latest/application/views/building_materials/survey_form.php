<?php defined('BASEPATH') OR exit('No direct script access allowed'); $values=$form_values ?? []; $esc=function($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}; ?>
<div class="bm-ops">
  <header class="bo-head"><div><span class="bo-eyebrow">Engineer workspace</span><h1>New site survey</h1><p>Capture one opening or equipment specification per survey. Add further surveys for other openings or sites.</p></div><a class="bo-button" href="<?= base_url('building_workspace/surveys'); ?>">Survey register</a></header>
  <?php if(!empty($form_error)): ?><p class="bo-error" role="alert"><?= $esc($form_error); ?></p><?php endif; ?>
  <form class="bo-card bo-editor" method="post" action="<?= base_url('building_workspace/save_survey').(!empty($building_mobile)?'?screen=mobile':''); ?>">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>"><input type="hidden" name="request_key" value="<?= $esc($request_key); ?>">
    <h2>Visit & responsibility</h2><div class="bo-form-grid">
      <label class="full">Survey title<input name="title" maxlength="200" value="<?= $esc($values['title'] ?? ''); ?>" placeholder="e.g. Warehouse A — industrial door opening" required></label>
      <label class="full">Project (optional)<select name="project_id" data-bm-select data-bm-project><option value="">Standalone customer survey</option><?php foreach($projects as $project): if(in_array($project->stage,['closed','cancelled'],true))continue; ?><option value="<?= (int)$project->id; ?>" <?= (int)($values['project_id'] ?? $selected_project ?? 0)===(int)$project->id?'selected':''; ?>><?= $esc($project->job_code.' · '.$project->title.' · '.$project->customer_name); ?></option><?php endforeach; ?></select><small>A linked survey uses the project's customer, site and responsible team member.</small></label>
      <div class="bo-form-grid full" data-bm-standalone><?php $this->load->view('building_materials/party_fields'); ?></div>
      <label>Visit date<input name="survey_date" type="date" value="<?= $esc($values['survey_date'] ?? date('Y-m-d')); ?>" required></label>
      <label>Status<select name="status" data-bm-select><option value="scheduled" <?= ($values['status'] ?? '')==='scheduled'?'selected':''; ?>>Scheduled</option><option value="completed" <?= ($values['status'] ?? '')==='completed'?'selected':''; ?>>Completed</option></select></label>
    </div>
    <h2>Measurements & specification</h2><div class="bo-form-grid">
      <label>Width<input type="number" name="width" min="0.001" step="any" value="<?= $esc($values['width'] ?? ''); ?>"></label><label>Height<input type="number" name="height" min="0.001" step="any" value="<?= $esc($values['height'] ?? ''); ?>"></label>
      <label>Unit<select name="unit" data-bm-select><?php foreach(['mm','cm','m'] as $unit): ?><option <?= ($values['unit'] ?? 'mm')===$unit?'selected':''; ?>><?= $unit; ?></option><?php endforeach; ?></select></label><label>Quantity<input type="number" name="quantity" min="1" step="1" value="<?= $esc($values['quantity'] ?? '1'); ?>"></label>
      <label class="full">Opening / site location<input name="location" maxlength="500" placeholder="Building, level, bay or equipment position" value="<?= $esc($values['location'] ?? ''); ?>"></label>
      <label class="full">Technical specification<textarea name="specification" rows="3" maxlength="8000" placeholder="System, material, finish, motor, power requirements…"><?= $esc($values['specification'] ?? ''); ?></textarea></label>
      <label class="full">Access & safety constraints<textarea name="risks" rows="2" maxlength="8000"><?= $esc($values['risks'] ?? ''); ?></textarea></label>
      <label class="full">Findings & recommendations<textarea name="notes" rows="3" maxlength="8000"><?= $esc($values['notes'] ?? ''); ?></textarea></label>
    </div><div class="bo-form-actions"><button class="bo-button primary" type="submit">Save site survey</button><a class="bo-button" href="<?= base_url('building_workspace/surveys'); ?>">Cancel</a></div>
  </form>
</div>
