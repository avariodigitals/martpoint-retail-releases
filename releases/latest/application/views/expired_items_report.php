<!DOCTYPE html>
<html>
<head>
<?php include"comman/code_css.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php include"sidebar.php"; ?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Expired / Expiring Items <small>Report</small></h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Expired Items Report</li>
    </ol>
  </section>
  <section class="content">
    <div class="row">
      <!-- Summary Cards -->
      <div class="col-md-12">
        <div class="row" style="margin-bottom:15px;">
          <div class="col-md-3 col-sm-6">
            <div class="small-box bg-red" style="border-radius:8px;">
              <div class="inner"><h3><?= count($expired); ?></h3><p>Expired Items</p></div>
              <div class="icon"><i class="fa fa-calendar-times-o"></i></div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="small-box bg-yellow" style="border-radius:8px;color:#000;">
              <div class="inner"><h3><?= count($expiring); ?></h3><p>Expiring Soon</p></div>
              <div class="icon"><i class="fa fa-clock-o"></i></div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="small-box bg-green" style="border-radius:8px;">
              <div class="inner"><h3><?= $settings->alert_before_days; ?></h3><p>Alert Days</p></div>
              <div class="icon"><i class="fa fa-bell-o"></i></div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="small-box bg-aqua" style="border-radius:8px;">
              <div class="inner"><h3><?= count($expired) + count($expiring); ?></h3><p>Total Alerted</p></div>
              <div class="icon"><i class="fa fa-exclamation-triangle"></i></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Combined Table -->
      <?php if(!empty($expired) || !empty($expiring)): ?>
      <div class="col-md-12">
        <div class="box">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list-alt"></i> Item Details</h3>
            <div class="box-tools pull-right">
              <a href="<?= base_url('expiry_settings'); ?>" class="btn btn-sm btn-info"><i class="fa fa-cog"></i> Settings</a>
              <a href="<?= base_url('expiry_settings/send_email_alert'); ?>" class="btn btn-sm btn-warning" onclick="toastr.success('Email alert triggered!');"><i class="fa fa-envelope"></i> Send Alert</a>
            </div>
          </div>
          <div class="box-body table-responsive no-padding">
            <table class="table table-hover table-striped">
              <thead>
                <tr class="bg-gray">
                  <th style="width:40px;">#</th>
                  <th>Status</th>
                  <th>Item Code</th>
                  <th>Item Name</th>
                  <th>Expiry Date</th>
                  <th>MFG Date</th>
                  <th>Stock</th>
                  <th>Time Left</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php $i=1;
                foreach($expired as $item):
                  $days = round((strtotime(date('Y-m-d')) - strtotime($item->expire_date)) / 86400);
                ?>
                <tr style="background:#fff0f0;">
                  <td><?= $i++; ?></td>
                  <td><span class="label label-danger"><i class="fa fa-ban"></i> EXPIRED</span></td>
                  <td><code><?= $item->item_code; ?></code></td>
                  <td><strong><?= $item->item_name; ?></strong></td>
                  <td><span class="text-danger"><strong><?= show_date($item->expire_date); ?></strong></span></td>
                  <td><?= (!empty($item->mfg_date) && $item->mfg_date != '0000-00-00') ? show_date($item->mfg_date) : '-'; ?></td>
                  <td><?= format_qty($item->stock); ?></td>
                  <td><span class="badge bg-red"><?= $days; ?> days past</span></td>
                  <td><a href="<?= base_url('items/update/'.$item->id); ?>" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Edit</a></td>
                </tr>
                <?php endforeach; ?>

                <?php foreach($expiring as $item):
                  $days = round((strtotime($item->expire_date) - strtotime(date('Y-m-d'))) / 86400);
                ?>
                <tr style="background:#fffbe6;">
                  <td><?= $i++; ?></td>
                  <td><span class="label label-warning"><i class="fa fa-clock-o"></i> EXPIRING</span></td>
                  <td><code><?= $item->item_code; ?></code></td>
                  <td><strong><?= $item->item_name; ?></strong></td>
                  <td><span class="text-warning"><strong><?= show_date($item->expire_date); ?></strong></span></td>
                  <td><?= (!empty($item->mfg_date) && $item->mfg_date != '0000-00-00') ? show_date($item->mfg_date) : '-'; ?></td>
                  <td><?= format_qty($item->stock); ?></td>
                  <td><span class="badge bg-yellow" style="color:#000;"><?= $days; ?> days left</span></td>
                  <td><a href="<?= base_url('items/update/'.$item->id); ?>" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Edit</a></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php else: ?>
      <div class="col-md-12">
        <div class="alert alert-success" style="border-left:4px solid #00a65a;">
          <h4><i class="icon fa fa-check"></i> All Clear!</h4>
          No expired or expiring items found. <a href="<?= base_url('expiry_settings'); ?>" class="btn btn-sm btn-info" style="margin-left:10px;"><i class="fa fa-cog"></i> Manage Settings</a>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </section>
</div>

<?php include"footer.php"; ?>
<div class="control-sidebar-bg"></div>
</div>
<?php include"comman/code_js_sound.php"; ?>
<?php include"comman/code_js.php"; ?>
<script>$(".expired_items_report-active-li").addClass("active");</script>
</body>
</html>
