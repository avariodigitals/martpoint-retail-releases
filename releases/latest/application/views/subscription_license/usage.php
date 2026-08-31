<!DOCTYPE html>
<html>
   <head>
      <?php $this->load->view('comman/code_css.php');?>
      <style>
        .progress { height: 24px; margin-bottom: 5px; }
        .progress-bar { line-height: 24px; font-size: 12px; }
        .usage-card { margin-bottom: 20px; }
        .usage-label { font-size: 14px; margin-bottom: 5px; }
        .usage-value { font-weight: bold; font-size: 16px; }
      </style>
   </head>
   <body class="hold-transition skin-blue sidebar-mini">
      <div class="wrapper">
         <?php $this->load->view('sidebar');?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>License Usage</h1>
    <ol class="breadcrumb">
      <li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Usage</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">

      <!-- Plan Summary -->
      <div class="col-md-4">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-info-circle"></i> Current Plan</h3>
          </div>
          <div class="box-body">
            <?php if($license && !empty($license->license_code)): 
              $days = $this->license->days_left($license->subscription_end_date);
            ?>
            <div class="text-center" style="padding:20px 0;">
              <h2 style="margin-top:0;"><?= htmlspecialchars($license->plan_name ?: 'Custom'); ?></h2>
              <span class="label label-<?= ($days > 30) ? 'success' : (($days > 0) ? 'warning' : 'danger'); ?>">
                <?= ($days > 0) ? $days . ' Days Left' : 'Expired'; ?>
              </span>
            </div>
            <table class="table table-bordered table-condensed">
              <tr><td><strong>Start</strong></td><td><?= show_date($license->subscription_start_date); ?></td></tr>
              <tr><td><strong>End</strong></td><td><?= show_date($license->subscription_end_date); ?></td></tr>
              <tr><td><strong>Client</strong></td><td><?= htmlspecialchars($license->client_name ?: '—'); ?></td></tr>
              <tr><td><strong>Status</strong></td><td><?= $license->subscription_status; ?></td></tr>
            </table>
            <?php else: ?>
            <div class="alert alert-warning text-center">
              <strong>Not Activated</strong><br>
              This store has no active license.
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Usage Details -->
      <div class="col-md-8">
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-chart-pie"></i> Usage Details</h3>
          </div>
          <div class="box-body">
            <?php
              $limits = [
                ['label' => 'Branches', 'used' => $branch_used, 'limit' => $license->branch_limit ?? 1, 'icon' => 'fa-building', 'color' => 'aqua'],
                ['label' => 'Users', 'used' => $user_used, 'limit' => $license->user_limit ?? 3, 'icon' => 'fa-users', 'color' => 'green'],
                ['label' => 'Products', 'used' => $product_used, 'limit' => $license->product_limit ?? 500, 'icon' => 'fa-cubes', 'color' => 'yellow'],
                ['label' => 'Services', 'used' => $service_used, 'limit' => $license->service_limit ?? 100, 'icon' => 'fa-wrench', 'color' => 'purple'],
                ['label' => 'Media Storage', 'used' => $media_used, 'limit' => $license->media_storage_limit_mb ?? 2048, 'icon' => 'fa-hdd-o', 'color' => 'red'],
              ];
              foreach($limits as $l):
                $pct = ($l['limit'] > 0) ? round(($l['used'] / $l['limit']) * 100, 1) : 0;
                $bar_color = ($pct >= 100) ? 'progress-bar-danger' : (($pct >= 80) ? 'progress-bar-warning' : 'progress-bar-success');
                $alert_class = ($pct >= 100) ? 'label-danger' : (($pct >= 80) ? 'label-warning' : '');
            ?>
            <div class="usage-card">
              <div class="usage-label">
                <i class="fa <?= $l['icon']; ?> text-<?= $l['color']; ?>"></i>
                <strong><?= $l['label']; ?></strong>
                <span class="pull-right">
                  <span class="usage-value"><?= $l['used']; ?> / <?= $l['limit']; ?></span>
                  <?php if($alert_class): ?>
                  <span class="label <?= $alert_class; ?>"><?= ($pct >= 100) ? 'Limit Reached' : 'Near Limit'; ?></span>
                  <?php endif; ?>
                </span>
              </div>
              <div class="progress">
                <div class="progress-bar <?= $bar_color; ?>" role="progressbar" style="width: <?= min($pct, 100); ?>%;">
                  <?= $pct; ?>%
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

      </div>
      <?php $this->load->view('footer.php');?>
      <div class="control-sidebar-bg"></div>
      </div>
      <?php $this->load->view('comman/code_js_sound.php');?>
      <?php $this->load->view('comman/code_js.php');?>
      <script>$(".subscription-usage-active-li").addClass("active");</script>
   </body>
</html>
