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
        <div class="box-header with-border"><h3 class="box-title">Price Catalogue</h3></div>
        <div class="box-body">
          <div class="alert alert-info">
            <h4><i class="fa fa-info-circle"></i> Price Catalogue</h4>
            <p>Maintain a structured price list for products and services (e.g., medicine price list, electronics catalog, service menu).</p>
            <p><strong>Coming in Phase 4:</strong> Category-wise price lists, customer-tier pricing, export to PDF/Excel, and quick update tools.</p>
          </div>
          <div class="text-center" style="padding:40px 0;">
            <i class="fa fa-tags" style="font-size:64px;color:#E2E8F0;"></i>
            <p style="margin-top:16px;color:#94A3B8;">Price catalogue will be activated in the next phase.</p>
          </div>
        </div>
      </div>
    </div></div>
  </section>
</div>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>$(".price-catalogue-active-li").addClass("active");</script>
</body>
</html>
