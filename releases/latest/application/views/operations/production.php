<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small>Feature Preview — Full engine coming in Phase 4</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <div class="row"><div class="col-md-12">
      <div class="box box-info">
        <div class="box-header with-border"><h3 class="box-title">Production Workflow</h3></div>
        <div class="box-body">
          <div class="alert alert-info">
            <h4><i class="fa fa-info-circle"></i> Production Management</h4>
            <p>Plan and track production runs (e.g., bakery batches, furniture assembly, garment production).</p>
            <p><strong>Coming in Phase 4:</strong> Production orders, bill of materials, batch scheduling, yield tracking, and cost analysis.</p>
          </div>
          <div class="text-center" style="padding:40px 0;">
            <i class="fa fa-industry" style="font-size:64px;color:#E2E8F0;"></i>
            <p style="margin-top:16px;color:#94A3B8;">Production workflow will be activated in the next phase.</p>
          </div>
        </div>
      </div>
    </div></div>
  </section>
</div>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>$(".production-active-li").addClass("active");</script>
</body>
</html>
