<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.pw-dashboard{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-bottom:24px}
.pw-card{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:16px;padding:20px;box-shadow:var(--mp-shadow-sm);transition:all .15s ease;text-decoration:none;color:var(--mp-ink);display:block}
.pw-card:hover{transform:translateY(-3px);box-shadow:var(--mp-shadow);text-decoration:none;color:var(--mp-ink);border-color:var(--mp-primary)}
.pw-card-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:14px}
.pw-card-icon.blue{background:rgba(0,87,255,.1);color:var(--mp-primary)}
.pw-card-icon.green{background:rgba(5,150,105,.1);color:var(--mp-success)}
.pw-card-icon.orange{background:rgba(217,119,6,.1);color:var(--mp-pay)}
.pw-card-icon.purple{background:rgba(124,58,237,.1);color:#7C3AED}
.pw-card-icon.red{background:rgba(220,38,38,.1);color:var(--mp-danger)}
.pw-card-title{font-size:15px;font-weight:700;color:var(--mp-text);margin-bottom:4px}
.pw-card-desc{font-size:13px;color:var(--mp-muted);line-height:1.4}
.pw-step{display:flex;flex-direction:column;align-items:center;text-align:center;padding:20px}
.pw-step-num{width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;margin:0 auto 12px}
.pw-step h4{font-size:14px;font-weight:700;margin:0 0 6px;color:var(--mp-text)}
.pw-step p{font-size:13px;color:var(--mp-muted);margin:0;line-height:1.5}
.pw-steps-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:0}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Plan, track and complete production runs</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <a href="<?= base_url('operations/production_batch'); ?>" class="mp-qa-btn green"><i class="fa fa-plus"></i> New Batch</a>
    <a href="<?= base_url('operations/production_schedule'); ?>" class="mp-qa-btn blue"><i class="fa fa-calendar"></i> Schedule</a>
  </div>
</div>

<div class="mp-quick-actions">
  <a href="<?= base_url('reports/production_summary'); ?>" class="mp-qa-btn orange"><i class="fa fa-chart-bar"></i> Production Report</a>
  <a href="<?= base_url('reports/ingredient_usage'); ?>" class="mp-qa-btn purple"><i class="fa fa-flask"></i> Ingredient Usage</a>
</div>

<h3 style="font-size:13px;font-weight:700;color:var(--mp-muted);text-transform:uppercase;letter-spacing:.05em;margin:0 0 16px">Production Overview</h3>
<div class="pw-dashboard">
  <a href="<?= base_url('operations/production_schedule'); ?>" class="pw-card">
    <div class="pw-card-icon blue"><i class="fa fa-industry"></i></div>
    <div class="pw-card-title">Production Schedule</div>
    <div class="pw-card-desc">View and manage all production batches. Track status from planned to completed.</div>
  </a>
  <a href="<?= base_url('operations/production_batch'); ?>" class="pw-card">
    <div class="pw-card-icon green"><i class="fa fa-plus-circle"></i></div>
    <div class="pw-card-title">Create Batch</div>
    <div class="pw-card-desc">Start a new production run. Assign recipe, staff, equipment, and target quantity.</div>
  </a>
  <a href="<?= base_url('operations/recipes'); ?>" class="pw-card">
    <div class="pw-card-icon orange"><i class="fa fa-book"></i></div>
    <div class="pw-card-title">Recipe Book</div>
    <div class="pw-card-desc">Manage recipes with ingredients, quantities, and cost calculations.</div>
  </a>
  <a href="<?= base_url('reports/production_summary'); ?>" class="pw-card">
    <div class="pw-card-icon purple"><i class="fa fa-chart-line"></i></div>
    <div class="pw-card-title">Production Reports</div>
    <div class="pw-card-desc">Summary of production output, yields, costs, and performance over time.</div>
  </a>
  <a href="<?= base_url('reports/ingredient_usage'); ?>" class="pw-card">
    <div class="pw-card-icon red"><i class="fa fa-flask"></i></div>
    <div class="pw-card-title">Ingredient Usage</div>
    <div class="pw-card-desc">Track raw material consumption across production runs. Identify waste.</div>
  </a>
  <a href="<?= base_url('reports/production_runs'); ?>" class="pw-card">
    <div class="pw-card-icon blue"><i class="fa fa-list-alt"></i></div>
    <div class="pw-card-title">Production Runs</div>
    <div class="pw-card-desc">Detailed log of all production runs with timelines and outcomes.</div>
  </a>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3><i class="fa fa-info-circle"></i> How Production Workflow Works</h3></div>
  <div class="mp-card-body">
    <div class="pw-steps-grid">
      <div class="pw-step">
        <div class="pw-step-num" style="background:rgba(0,87,255,.1);color:var(--mp-primary)">1</div>
        <h4>Create Recipe</h4>
        <p>Define ingredients, quantities, and expected yield for each product you make.</p>
      </div>
      <div class="pw-step">
        <div class="pw-step-num" style="background:rgba(5,150,105,.1);color:var(--mp-success)">2</div>
        <h4>Start Batch</h4>
        <p>Create a production batch from a recipe. Assign staff, equipment, and schedule.</p>
      </div>
      <div class="pw-step">
        <div class="pw-step-num" style="background:rgba(217,119,6,.1);color:var(--mp-pay)">3</div>
        <h4>Track Progress</h4>
        <p>Update batch status as work progresses: Planned &rarr; In Progress &rarr; Completed.</p>
      </div>
      <div class="pw-step">
        <div class="pw-step-num" style="background:rgba(124,58,237,.1);color:#7C3AED">4</div>
        <h4>Review Reports</h4>
        <p>Analyze production output, costs, ingredient usage, and yield performance.</p>
      </div>
    </div>
  </div>
</div>
