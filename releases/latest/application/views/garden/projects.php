<?php if (get_instance()->permissions('service_jobs_add')): ?><details class="garden-card" id="new-project"><summary class="garden-create-summary"><span><i class="fa fa-plus" aria-hidden="true"></i> New landscaping project</span><small>Create a project for a customer &amp; site</small></summary><form class="garden-card" method="post" action="<?= base_url('garden/create_project'); ?>">
<?php $this->load->view('garden/form_token'); ?><input type="hidden" name="request_key" value="<?= $request_key; ?>"><h2>New Landscaping Project</h2>
<?php $this->load->view('garden/service_customer_fields'); ?>
<label>Project Title<input name="title" maxlength="200" required></label><label>Site and Project Description<textarea name="description" rows="3"></textarea></label>
<label>Existing CRM Enquiry ID (optional)<input type="number" name="lead_id" min="1"></label><button class="garden-button">Create Project</button></form></details><?php endif; ?>
<?php
$lanes=[
'enquiry'=>['Enquiry & inspection',['enquiry','inspection']],
'planning'=>['Quote & approval',['quoted','approved']],
'ready'=>['Ready to start',['deposit']],
'execution'=>['In progress',['execution']],
'completed'=>['Completed',['completed','paid','closed']],
'cancelled'=>['Cancelled',['cancelled']],
]; $labels=mp_garden_project_stages(); ?>
<div class="garden-board-head"><div><span class="garden-kicker">FROM ENQUIRY TO DELIVERY</span><h2>Project board</h2><p class="garden-muted">Open a stage to list its projects, then open a card to inspect the site, plan work or record the next stage. Showing the latest 250 projects.</p></div><label><span class="sr-only">Search projects</span><input type="search" id="garden-project-search" placeholder="Search project or customer…"></label></div>
<div class="garden-project-board" aria-label="Landscaping project board">
<?php $opened=false; foreach ($lanes as $laneKey=>$lane): $cards=array_filter($garden_projects,function($project) use($lane){return in_array($project->stage,$lane[1],true);}); $open=(!$opened && count($cards)>0); if($open)$opened=true; ?>
<details class="garden-stage-fold" data-project-lane <?= $open ? 'open' : ''; ?>>
<summary><span class="garden-stage-name"><i class="garden-stage-dot garden-dot-<?= $laneKey; ?>"></i><?= $lane[0]; ?></span><span class="garden-stage-meta"><b data-lane-count><?= count($cards); ?></b><i class="fa fa-chevron-down" aria-hidden="true"></i></span></summary>
<div class="garden-lane-body"><?php foreach ($cards as $project): ?><a class="garden-project-tile" data-project-card href="<?= base_url('garden/project/'.(int)$project->id); ?>"><small><?= htmlspecialchars($project->job_code); ?></small><h3><?= htmlspecialchars($project->title); ?></h3><p><i class="fa fa-user-o" aria-hidden="true"></i> <?= htmlspecialchars($project->customer_name); ?></p><span class="garden-pill"><?= htmlspecialchars($labels[$project->stage] ?? $project->stage); ?></span><footer><span>Open workspace</span><span aria-hidden="true">↗</span></footer></a><?php endforeach; ?><p class="garden-lane-empty" data-lane-empty <?= $cards ? 'hidden' : ''; ?>>No projects at this stage.</p></div>
</details><?php endforeach; ?></div>
