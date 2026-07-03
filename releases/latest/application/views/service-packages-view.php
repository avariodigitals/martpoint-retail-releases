<!DOCTYPE html>
<html>
<head>
<?php include"comman/code_css.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php include"sidebar.php"; ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      <?=$page_title;?>
      <small></small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?php echo $base_url; ?>service_packages">Service Packages</a></li>
      <li class="active"><?=$page_title;?></li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <!-- ********** ALERT MESSAGE START******* -->
    <?php $this->load->view('comman/code_flashdata');?>
    <!-- ********** ALERT MESSAGE END******* -->
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title"><?=$page_title;?></h3>
            <div class="box-tools pull-right">
              <a href="<?php echo base_url('service_packages'); ?>" class="btn btn-default btn-sm">Back to List</a>
              <?php if($CI->permissions('service_packages_edit')) { ?>
              <a href="<?php echo base_url('service_packages/update/'.$q_id); ?>" class="btn btn-primary btn-sm">Edit</a>
              <?php } ?>
            </div>
          </div>
        <div class="box-body">
          <div class="row">
            <div class="col-sm-4 text-center">
              <?php if(!empty($package_image) && file_exists($package_image)) { ?>
                <img src="<?php echo base_url($package_image); ?>" style="max-height:180px; border-radius:10px; border:1px solid #ddd;">
              <?php } else { ?>
                <div style="height:180px; background:#f5f5f5; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#aaa;">
                  <i class="fa fa-gift" style="font-size:64px;"></i>
                </div>
              <?php } ?>
              <h3 style="margin-top:16px;"><?php echo $package_name; ?></h3>
              <span class="label label-info"><?php echo ucfirst($pricing_model); ?></span>
              <span class="label label-<?php echo $redemption_type == 'single' ? 'success' : 'warning'; ?>"><?php echo ucfirst($redemption_type); ?></span>
              <?php if($status == 1) { ?>
                <span class="label label-success">Active</span>
              <?php } else { ?>
                <span class="label label-danger">Inactive</span>
              <?php } ?>
            </div>
            <div class="col-sm-8">
              <table class="table table-striped">
                <tr><td style="width:40%;"><strong>Package Code</strong></td><td><?php echo $package_code; ?></td></tr>
                <tr><td><strong>Description</strong></td><td><?php echo nl2br($description); ?></td></tr>
                <tr><td><strong>Package Price</strong></td><td><strong style="font-size:18px; color:#0057FF;"><?php echo $CI->currency($package_price); ?></strong></td></tr>
                <?php if($pricing_model == 'calculated') { ?>
                <tr><td><strong>Discount</strong></td><td><?php echo $discount_type ? ucfirst($discount_type) . ': ' . $discount : 'None'; ?></td></tr>
                <?php } ?>
                <tr><td><strong>Expiry</strong></td><td><?php echo $expiry_type == 'none' ? 'No Expiry' : ($expiry_type == 'days' ? $expiry_days . ' Days from purchase' : show_date($expiry_date)); ?></td></tr>
              </table>
            </div>
          </div>

          <hr>
          <h4><i class="fa fa-list"></i> Package Contents</h4>
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Type</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php $total = 0; $i = 1; foreach($package_items as $it) {
                $sub = floatval($it->sales_price ?? 0) * floatval($it->quantity);
                $total += $sub;
              ?>
              <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo ucfirst($it->item_type); ?></td>
                <td><?php echo $it->item_name; ?></td>
                <td><?php echo $it->quantity; ?></td>
                <td><?php echo $CI->currency($it->sales_price ?? 0); ?></td>
                <td><?php echo $CI->currency($sub); ?></td>
              </tr>
              <?php } ?>
            </tbody>
            <tfoot>
              <tr style="font-weight:bold;">
                <td colspan="5" class="text-right">Individual Total:</td>
                <td><?php echo $CI->currency($total); ?></td>
              </tr>
              <tr style="font-weight:bold; color:green;">
                <td colspan="5" class="text-right">Package Price:</td>
                <td><?php echo $CI->currency($package_price); ?></td>
              </tr>
              <tr style="font-weight:bold; color:#e74c3c;">
                <td colspan="5" class="text-right">Customer Saves:</td>
                <td><?php echo $CI->currency($total - $package_price); ?></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
</div>
<!-- /.content-wrapper -->
<?php include"footer.php"; ?>
<div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
<!-- SOUND CODE -->
<?php include"comman/code_js_sound.php"; ?>
<!-- TABLES CODE -->
<?php include"comman/code_js.php"; ?>
</body>
</html>
