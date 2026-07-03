<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <div class="row"><div class="col-md-8 col-md-offset-2">
      <div class="box box-info">
        <div class="box-header with-border"><h3 class="box-title">Public Catalogue Settings</h3></div>
        <div class="box-body">
          <div class="alert alert-info">
            <p>Configure your public browsing catalogue. These settings are managed from <strong>Settings → Business Profile → Templates &amp; Labels</strong>.</p>
          </div>
          <table class="table table-bordered">
            <tr><th>Setting</th><th>Value</th></tr>
            <tr><td>Enabled</td><td><?= (!empty($settings['enabled']) && $settings['enabled'] == '1') ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>'; ?></td></tr>
            <tr><td>Slug</td><td><?= !empty($settings['slug']) ? htmlspecialchars($settings['slug']) : '<em>catalogue</em>'; ?></td></tr>
            <tr><td>Show Products</td><td><?= (!empty($settings['show_products']) && $settings['show_products'] == '1') ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>'; ?></td></tr>
            <tr><td>Show Services</td><td><?= (!empty($settings['show_services']) && $settings['show_services'] == '1') ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>'; ?></td></tr>
          </table>
          <div class="text-center" style="margin-top:20px;">
            <a href="<?= base_url('business_profile'); ?>" class="btn btn-primary"><i class="fa fa-cog"></i> Edit in Business Profile</a>
            <a href="<?= base_url('store/' . ($this->session->userdata('store_slug') ?? '') . '/catalogue'); ?>" class="btn btn-success" target="_blank"><i class="fa fa-eye"></i> Preview Public Catalogue</a>
          </div>
          <hr>
          <div class="alert alert-warning">
            <h4><i class="fa fa-clock-o"></i> Full Public Catalogue Coming in Phase 4</h4>
            <p>The public browsing page will support category filters, search, and direct WhatsApp/online ordering.</p>
          </div>
        </div>
      </div>
    </div></div>
  </section>
</div>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>$(".public-catalogue-active-li").addClass("active");</script>
</body>
</html>
