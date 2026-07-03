<!DOCTYPE html>
<html>
<head>
  <?php include"comman/code_css.php"; ?>
  <style>
    .stat-box { border-radius: 4px; padding: 15px; color: #fff; margin-bottom: 15px; }
    .stat-box.blue { background: #3c8dbc; }
    .stat-box.green { background: #00a65a; }
    .stat-box.red { background: #dd4b39; }
    .stat-box.yellow { background: #f39c12; }
    .stat-box h3 { margin: 0; font-size: 28px; font-weight: bold; }
    .stat-box p { margin: 5px 0 0; font-size: 14px; }
  </style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php include"sidebar.php"; ?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title; ?> <small>Usage & Billing Transparency</small></h1>
      <ol class="breadcrumb">
        <li><a href="<?= $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>
    <section class="content">
      <?php include"comman/code_flashdata.php"; ?>
      <div class="row">
        <div class="col-lg-3 col-xs-6">
          <div class="stat-box blue">
            <h3><?= $total_count; ?></h3>
            <p><i class="fa fa-search"></i> Total Verifications</p>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <div class="stat-box green">
            <h3><?= $success_count; ?></h3>
            <p><i class="fa fa-check"></i> Successful</p>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <div class="stat-box yellow">
            <h3><?= number_format($total_cost, 2); ?></h3>
            <p><i class="fa fa-money"></i> Total Cost (NGN)</p>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <div class="stat-box red">
            <h3><?= $mock_count; ?></h3>
            <p><i class="fa fa-flask"></i> Demo/Mock Verifications</p>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Verification History</h3>
              <div class="box-tools pull-right">
                <span class="label label-info">Transparent Billing</span>
              </div>
            </div>
            <div class="box-body table-responsive no-padding">
              <table class="table table-bordered table-striped datatable">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Date/Time</th>
                    <th>User</th>
                    <th>NIN/BVN</th>
                    <th>Provider</th>
                    <th>Status</th>
                    <th>Mode</th>
                    <th class="text-right">Cost (NGN)</th>
                    <th>Message</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $i = 1; foreach($logs as $log){ ?>
                  <tr>
                    <td><?= $i++; ?></td>
                    <td><?= show_date($log->created_at); ?> <?= date('H:i', strtotime($log->created_at)); ?></td>
                    <td><?= htmlspecialchars(isset($log->user_name) ? $log->user_name : 'System'); ?></td>
                    <td><code><?= htmlspecialchars(isset($log->nin_bvn) ? $log->nin_bvn : '-'); ?></code></td>
                    <td><?= htmlspecialchars(isset($log->provider) ? $log->provider : '-'); ?></td>
                    <td>
                      <?php if($log->status == 'success'){ ?>
                        <span class="label label-success"><i class="fa fa-check"></i> Success</span>
                      <?php }else{ ?>
                        <span class="label label-danger"><i class="fa fa-times"></i> Failed</span>
                      <?php } ?>
                    </td>
                    <td>
                      <?php if($log->is_mock){ ?>
                        <span class="label label-warning">Demo</span>
                      <?php }else{ ?>
                        <span class="label label-primary">Live API</span>
                      <?php } ?>
                    </td>
                    <td class="text-right"><?= number_format($log->cost, 2); ?></td>
                    <td><small><?= htmlspecialchars(isset($log->response_message) ? $log->response_message : ''); ?></small></td>
                  </tr>
                  <?php } ?>
                  <?php if(empty($logs)){ ?>
                  <tr><td colspan="9" class="text-center text-muted">No verification records found.</td></tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
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
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "order": [[1, "desc"]]
    });
  });
</script>
<script>$(".ninverify_log-active-li").addClass("active");</script>
</body>
</html>
