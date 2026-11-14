<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$stages = $building_project_stages ?? [];
$projects = (array)($building_projects ?? []);
$counts = (array)($building_project_counts ?? []);
$lanes = [
    ['key' => 'enquiry', 'title' => 'Planning & survey', 'stages' => ['enquiry', 'inspection'], 'tone' => 'sand'],
    ['key' => 'quoted', 'title' => 'Quoted & approved', 'stages' => ['quoted', 'approved'], 'tone' => 'teal'],
    ['key' => 'delivery', 'title' => 'Ready for delivery', 'stages' => ['deposit'], 'tone' => 'amber'],
    ['key' => 'execution', 'title' => 'Installation in progress', 'stages' => ['execution'], 'tone' => 'copper'],
    ['key' => 'complete', 'title' => 'Completed & closed', 'stages' => ['completed', 'paid', 'closed'], 'tone' => 'green'],
];
$projectUrl = function($id){ return base_url('building_projects/'.(int)$id); };
?>
<div class="bm-projects" id="bm-projects">
  <header class="bm-projects-head">
    <div>
      <div class="bm-projects-eyebrow"><span class="bm-projects-live"></span> Project operations</div>
      <h1>Project control</h1>
      <p>Coordinate customer scope, site surveys, quotations and execution. Start directly for a customer or link a source enquiry.</p>
    </div>
    <div class="bm-projects-actions">
      <?php if(get_instance()->permissions('service_jobs_add')): ?><a class="bm-projects-button primary" href="<?= base_url('building_projects/create'); ?>"><i class="fa fa-plus"></i> New project</a><?php endif; ?>
      <a class="bm-projects-button primary" href="<?= base_url('quotation'); ?>"><i class="fa fa-file-text-o"></i> Open quotations</a>
    </div>
  </header>

  <?php if (empty($building_projects_ready)): ?>
    <section class="bm-project-empty bm-project-empty-wide">
      <span class="bm-project-empty-icon"><i class="fa fa-sitemap"></i></span>
      <h2>Project workbench is waiting for its foundation</h2>
      <p>The shared service-job project tables are not available in this environment yet. Once the approved project migrations are applied, enquiries, surveys, quotations, delivery and installation records will appear here without creating a second inventory or finance system.</p>
      <a class="bm-projects-button primary" href="<?= base_url('leads'); ?>">Open CRM enquiries</a>
    </section>
  <?php else: ?>
    <div class="bm-project-summary">
      <div class="bm-project-summary-card"><span>Open workstreams</span><strong><?= number_format(count(array_filter($projects, function($p){ return !in_array((string)($p->stage ?? ''), ['closed','cancelled'], true); }))); ?></strong><small>Across the operating flow</small></div>
      <div class="bm-project-summary-card teal"><span>Awaiting a move</span><strong><?= number_format((int)($counts['enquiry'] ?? 0) + (int)($counts['inspection'] ?? 0) + (int)($counts['quoted'] ?? 0) + (int)($counts['approved'] ?? 0)); ?></strong><small>Enquiry through approval</small></div>
      <div class="bm-project-summary-card amber"><span>In delivery / install</span><strong><?= number_format((int)($counts['deposit'] ?? 0) + (int)($counts['execution'] ?? 0)); ?></strong><small>Material and field activity</small></div>
      <div class="bm-project-summary-card green"><span>Closed work</span><strong><?= number_format((int)($counts['completed'] ?? 0) + (int)($counts['paid'] ?? 0) + (int)($counts['closed'] ?? 0)); ?></strong><small>Ready for after-sales</small></div>
    </div>

    <div class="bm-project-board" aria-label="Building Materials project board">
      <?php foreach ($lanes as $lane): $laneProjects = array_values(array_filter($projects, function($project) use ($lane){ return in_array((string)($project->stage ?? ''), $lane['stages'], true); })); ?>
        <section class="bm-project-lane tone-<?= htmlspecialchars($lane['tone'], ENT_QUOTES, 'UTF-8'); ?>">
          <div class="bm-project-lane-head"><div><span class="bm-project-lane-dot"></span><h2><?= htmlspecialchars($lane['title'], ENT_QUOTES, 'UTF-8'); ?></h2></div><strong><?= number_format(count($laneProjects)); ?></strong></div>
          <div class="bm-project-lane-body">
            <?php if ($laneProjects): foreach ($laneProjects as $project): $stage = (string)($project->stage ?? 'enquiry'); ?>
              <a class="bm-project-card" href="<?= $projectUrl($project->job_id ?? $project->id); ?>">
                <div class="bm-project-card-top"><span><?= htmlspecialchars((string)($project->job_code ?: '#'.$project->job_id), ENT_QUOTES, 'UTF-8'); ?></span><i class="fa fa-arrow-up-right"></i></div>
                <h3><?= htmlspecialchars((string)($project->title ?: 'Untitled project'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="bm-project-customer"><i class="fa fa-building-o"></i> <?= htmlspecialchars((string)($project->customer_name ?: 'Customer'), ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="bm-project-card-meta"><span class="bm-project-stage"><?= htmlspecialchars($stages[$stage] ?? ucfirst($stage), ENT_QUOTES, 'UTF-8'); ?></span><span><?= htmlspecialchars((string)($project->scheduled_date ?: 'No date'), ENT_QUOTES, 'UTF-8'); ?></span></div>
                <div class="bm-project-card-footer"><span><?= htmlspecialchars((string)($project->assigned_name ?: 'Unassigned'), ENT_QUOTES, 'UTF-8'); ?></span><span>Open record <i class="fa fa-angle-right"></i></span></div>
              </a>
            <?php endforeach; else: ?><div class="bm-project-lane-empty">No workstreams at this stage.</div><?php endif; ?>
          </div>
        </section>
      <?php endforeach; ?>
      <?php $cancelled = array_values(array_filter($projects, function($project){ return (string)($project->stage ?? '') === 'cancelled'; })); if ($cancelled): ?>
        <section class="bm-project-lane tone-red"><div class="bm-project-lane-head"><div><span class="bm-project-lane-dot"></span><h2>Cancelled</h2></div><strong><?= number_format(count($cancelled)); ?></strong></div><div class="bm-project-lane-body"><?php foreach($cancelled as $project): ?><a class="bm-project-card" href="<?= $projectUrl($project->job_id ?? $project->id); ?>"><div class="bm-project-card-top"><span><?= htmlspecialchars((string)($project->job_code ?: '#'.$project->job_id), ENT_QUOTES, 'UTF-8'); ?></span><i class="fa fa-arrow-up-right"></i></div><h3><?= htmlspecialchars((string)($project->title ?: 'Untitled project'), ENT_QUOTES, 'UTF-8'); ?></h3><p class="bm-project-customer"><i class="fa fa-building-o"></i> <?= htmlspecialchars((string)($project->customer_name ?: 'Customer'), ENT_QUOTES, 'UTF-8'); ?></p></a><?php endforeach; ?></div></section>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
