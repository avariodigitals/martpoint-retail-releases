<!DOCTYPE html>
<html>
<head>
  <?php include"comman/code_css.php"; ?>
  <style>
    .stat-box { border-radius: 4px; padding: 20px; color: #fff; margin-bottom: 20px; text-align: center; }
    .stat-box.blue { background: #3c8dbc; }
    .stat-box.green { background: #00a65a; }
    .stat-box.red { background: #dd4b39; }
    .stat-box.yellow { background: #f39c12; }
    .stat-box.purple { background: #605ca8; }
    .stat-box.teal { background: #39cccc; }
    .stat-box h2 { margin: 0; font-size: 36px; font-weight: bold; }
    .stat-box p { margin: 8px 0 0; font-size: 14px; }
    .stat-box small { font-size: 12px; opacity: 0.8; }
  </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php include"sidebar.php"; ?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title; ?> <small>Billing & Usage Dashboard</small></h1>
      <ol class="breadcrumb">
        <li><a href="<?= $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>
    <section class="content">
      <?php include"comman/code_flashdata.php"; ?>

      <!-- Summary Cards -->
      <div class="row">
        <div class="col-lg-2 col-md-4 col-sm-6">
          <div class="stat-box blue">
            <h2><?= $total_count; ?></h2>
            <p><i class="fa fa-search"></i> Total Checks</p>
            <small>All verifications</small>
          </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <div class="stat-box green">
            <h2><?= $success_count; ?></h2>
            <p><i class="fa fa-check"></i> Successful</p>
            <small>Verified customers</small>
          </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <div class="stat-box red">
            <h2><?= $failed_count; ?></h2>
            <p><i class="fa fa-times"></i> Failed</p>
            <small>Invalid or errors</small>
          </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <div class="stat-box yellow">
            <h2><?= number_format($total_cost, 2); ?></h2>
            <p><i class="fa fa-money"></i> Total Cost</p>
            <small>NGN spent</small>
          </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <div class="stat-box purple">
            <h2><?= $live_count; ?></h2>
            <p><i class="fa fa-globe"></i> Live API</p>
            <small>Real verifications</small>
          </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <div class="stat-box teal">
            <h2><?= $mock_count; ?></h2>
            <p><i class="fa fa-flask"></i> Demo Mode</p>
            <small>Mock verifications</small>
          </div>
        </div>
      </div>

      <!-- Daily Breakdown -->
      <div class="row">
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-calendar"></i> Daily Usage Breakdown — <?= date('F Y'); ?></h3>
              <div class="box-tools pull-right">
                <span class="label label-success">Transparent Billing</span>
              </div>
            </div>
            <div class="box-body table-responsive no-padding">
              <table class="table table-bordered table-striped datatable">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th class="text-right">Total Checks</th>
                    <th class="text-right">Successful</th>
                    <th class="text-right">Failed</th>
                    <th class="text-right">Cost (NGN)</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($daily_breakdown as $day){ 
                    $day_failed = $day->total - $day->successful;
                  ?>
                  <tr>
                    <td><?= show_date($day->date); ?></td>
                    <td class="text-right"><?= $day->total; ?></td>
                    <td class="text-right"><span class="text-success"><?= $day->successful; ?></span></td>
                    <td class="text-right"><span class="text-danger"><?= $day_failed; ?></span></td>
                    <td class="text-right"><strong><?= number_format($day->daily_cost, 2); ?></strong></td>
                  </tr>
                  <?php } ?>
                  <?php if(empty($daily_breakdown)){ ?>
                  <tr><td colspan="5" class="text-center text-muted">No verification activity this month.</td></tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Info Box -->
      <div class="row">
        <div class="col-md-12">
          <div class="alert alert-info">
            <h4><i class="fa fa-info-circle"></i> How Billing Works</h4>
            <ul>
              <li><strong>Live API verifications</strong> are billed per successful check at the rate set by the Super Admin.</li>
              <li><strong>Demo/Mock verifications</strong> cost <strong>NGN 0.00</strong> and are used when no real API is configured.</li>
              <li>Only Super Admin can change the API provider, cost per verification, or enable/disable the feature.</li>
              <li>This dashboard shows your store's total usage for transparent billing.</li>
            </ul>
          </div>
        </div>
      </div>
    </section>
  </div>
  <?php include"footer.php"; ?>
</div>
<?php include"comman/code_js.php"; ?>
<script>
  $(function () {
    $('.datatable').DataTable({
      "paging": true,
      "lengthChange": true,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "order": [[0, "desc"]]
    });
  });
</script>
<script>$('.ninverify_usage-active-li').addClass('active');</script>
</body>
</html>
