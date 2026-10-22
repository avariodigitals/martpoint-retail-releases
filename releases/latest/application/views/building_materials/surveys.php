<?php defined('BASEPATH') OR exit('No direct script access allowed'); $ci =& get_instance(); ?>
<div class="bm-ops">
  <header class="bo-head"><div><span class="bo-eyebrow">Technical & field operations</span><h1>Site surveys</h1><p>Schedule assessments and capture dimensions, specifications, access constraints and site findings.</p></div><?php if($ci->permissions('service_jobs_add')): ?><a class="bo-button primary" href="<?= base_url('building_workspace/survey_form'); ?>"><i class="fa fa-plus"></i> New site survey</a><?php endif; ?></header>
  <section class="bo-card"><div class="bo-section-head"><h2>Survey register</h2><span class="bo-pill"><?= count($surveys); ?> surveys</span></div>
    <?php if(!$surveys): ?><div class="bo-empty"><i class="fa fa-map-marker"></i><h3>No site surveys recorded yet</h3><p>Create a survey directly for a customer or attach one to an existing project. CRM enquiries remain in Opportunities.</p></div><?php else: ?>
    <div class="bo-table-wrap"><table class="bo-table"><thead><tr><th>Survey</th><th>Customer</th><th>Engineer</th><th>Visit date</th><th>Status</th><th></th></tr></thead><tbody>
      <?php foreach($surveys as $survey): $fields=json_decode($survey->fields_json,true) ?: []; ?><tr><td><strong><?= htmlspecialchars($fields['title'] ?? $survey->title); ?></strong><small>SUR-<?= (int)$survey->id; ?> · <?= htmlspecialchars($survey->job_code); ?></small></td><td><?= htmlspecialchars($survey->customer_name ?: 'Customer'); ?></td><td><?= htmlspecialchars($survey->engineer ?: 'Unassigned'); ?></td><td><?= htmlspecialchars($fields['survey_date'] ?? 'Not scheduled'); ?></td><td><span class="bo-pill"><?= htmlspecialchars(ucfirst($fields['status'] ?? 'Recorded')); ?></span></td><td><a class="bo-button compact" href="<?= base_url('building_workspace/survey/'.(int)$survey->id); ?>">Open survey</a></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
  </section>
</div>
