<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?>
<style>
.pw-dashboard { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px; }
.pw-card { background:#fff; border-radius:14px; padding:20px; border:1px solid #E2E8F0; box-shadow:0 1px 3px rgba(0,0,0,0.06); transition:transform .2s, box-shadow .2s; text-decoration:none; color:inherit; display:block; }
.pw-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,0.1); }
.pw-card-icon { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px; margin-bottom:12px; }
.pw-card-icon.blue { background:#DBEAFE; color:#0057FF; }
.pw-card-icon.green { background:#D1FAE5; color:#059669; }
.pw-card-icon.orange { background:#FEF3C7; color:#D97706; }
.pw-card-icon.purple { background:#EDE9FE; color:#7C3AED; }
.pw-card-icon.red { background:#FEE2E2; color:#DC2626; }
.pw-card-title { font-size:15px; font-weight:700; color:#1E293B; margin-bottom:4px; }
.pw-card-desc { font-size:13px; color:#64748B; line-height:1.4; }
.pw-quick-row { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:24px; }
.pw-quick-btn { display:inline-flex; align-items:center; gap:8px; padding:10px 18px; border-radius:10px; font-size:14px; font-weight:600; color:#fff; text-decoration:none; box-shadow:0 2px 6px rgba(0,0,0,0.08); transition:transform .15s; }
.pw-quick-btn:hover { transform:translateY(-2px); color:#fff; }
.pw-quick-btn.green { background:#10B981; }
.pw-quick-btn.blue { background:#3B82F6; }
.pw-quick-btn.orange { background:#F59E0B; }
.pw-quick-btn.purple { background:#8B5CF6; }
.pw-section-title { font-size:15px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:16px; }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">

    <div class="pw-quick-row">
      <a href="<?= base_url('operations/production_batch'); ?>" class="pw-quick-btn green"><i class="fa fa-plus"></i> New Batch</a>
      <a href="<?= base_url('operations/production_schedule'); ?>" class="pw-quick-btn blue"><i class="fa fa-calendar"></i> Production Schedule</a>
      <a href="<?= base_url('reports/production_summary'); ?>" class="pw-quick-btn orange"><i class="fa fa-chart-bar"></i> Production Report</a>
      <a href="<?= base_url('reports/ingredient_usage'); ?>" class="pw-quick-btn purple"><i class="fa fa-flask"></i> Ingredient Usage</a>
    </div>

    <div class="pw-section-title">Production Overview</div>
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

    <div class="box box-info" style="border-radius:12px;">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-info-circle"></i> How Production Workflow Works</h3>
      </div>
      <div class="box-body">
        <div class="row">
          <div class="col-md-3 col-sm-6">
            <div style="text-align:center;padding:20px;">
              <div style="width:48px;height:48px;border-radius:50%;background:#DBEAFE;color:#0057FF;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;margin:0 auto 10px;">1</div>
              <h4 style="font-size:14px;font-weight:700;">Create Recipe</h4>
              <p style="font-size:13px;color:#64748B;">Define ingredients, quantities, and expected yield for each product you make.</p>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div style="text-align:center;padding:20px;">
              <div style="width:48px;height:48px;border-radius:50%;background:#D1FAE5;color:#059669;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;margin:0 auto 10px;">2</div>
              <h4 style="font-size:14px;font-weight:700;">Start Batch</h4>
              <p style="font-size:13px;color:#64748B;">Create a production batch from a recipe. Assign staff, equipment, and schedule.</p>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div style="text-align:center;padding:20px;">
              <div style="width:48px;height:48px;border-radius:50%;background:#FEF3C7;color:#D97706;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;margin:0 auto 10px;">3</div>
              <h4 style="font-size:14px;font-weight:700;">Track Progress</h4>
              <p style="font-size:13px;color:#64748B;">Update batch status as work progresses: Planned &rarr; In Progress &rarr; Completed.</p>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div style="text-align:center;padding:20px;">
              <div style="width:48px;height:48px;border-radius:50%;background:#EDE9FE;color:#7C3AED;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;margin:0 auto 10px;">4</div>
              <h4 style="font-size:14px;font-weight:700;">Review Reports</h4>
              <p style="font-size:13px;color:#64748B;">Analyze production output, costs, ingredient usage, and yield performance.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

  </section>
</div>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>$(".production-active-li").addClass("active");</script>
</body>
</html>
