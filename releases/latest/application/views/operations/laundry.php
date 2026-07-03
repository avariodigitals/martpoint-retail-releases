<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php'); ?>
<style>
.lw-container { padding: 0 10px; }
.lw-column { padding: 0 6px; }
.lw-column .box {
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: none;
}
.lw-column .box-header {
    border-radius: 6px 6px 0 0;
    padding: 12px 14px;
}
.lw-column .box-header .box-title { font-size: 14px; font-weight: 700; }
.lw-column .box-body {
    height: calc(100vh - 240px);
    min-height: 300px;
    max-height: calc(100vh - 240px);
    overflow-y: auto !important;
    overflow-x: hidden;
    background: #f5f5f5;
    padding: 10px;
    border-radius: 0 0 6px 6px;
    position: relative;
}
.lw-card {
    background: #fff;
    border-radius: 5px;
    padding: 10px;
    margin-bottom: 8px;
    border-left: 3px solid #ccc;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: all 0.2s ease;
}
.lw-card:hover { transform: translateY(-1px); box-shadow: 0 3px 8px rgba(0,0,0,0.1); }
.lw-card.dropped_off { border-left-color: #dc3545; }
.lw-card.washing { border-left-color: #17a2b8; }
.lw-card.ironing { border-left-color: #ffc107; }
.lw-card.ready { border-left-color: #28a745; }
.lw-order-id { font-size: 13px; font-weight: 700; color: #2c3e50; }
.lw-customer { font-size: 11px; color: #666; margin-bottom: 6px; }
.lw-items { margin-bottom: 6px; }
.lw-item { font-size: 12px; padding: 2px 0; border-bottom: 1px dashed #eee; }
.lw-item:last-child { border-bottom: none; }
.lw-item-qty { display: inline-block; background: #e9ecef; color: #495057; font-weight: 700; padding: 1px 6px; border-radius: 3px; font-size: 10px; margin-right: 4px; }
.lw-service-tag { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 9px; font-weight: 600; text-transform: uppercase; margin-left: 4px; }
.lw-service-tag.wash { background: #d1ecf1; color: #0c5460; }
.lw-service-tag.iron { background: #fff3cd; color: #856404; }
.lw-service-tag.dryclean { background: #e2e3e5; color: #383d41; }
.lw-service-tag.washiron { background: #d4edda; color: #155724; }
.lw-empty { text-align: center; padding: 30px 0; color: #bbb; font-size: 13px; }
.lw-empty i { margin-bottom: 8px; display: block; }
.lw-collected-row { margin-top: 15px; }
.lw-badge { font-size: 11px; padding: 2px 8px; border-radius: 10px; }
.lw-status-line { font-size: 11px; color: #777; margin-top: 4px; padding-top: 6px; border-top: 1px dashed #eee; }
.lw-pickedup { border-left-color: #6c757d !important; opacity: 0.7; }
.lw-item-status { font-size: 10px; font-weight: 600; margin-left: 4px; }
.lw-item-status.pending { color: #dc3545; }
.lw-item-status.washing { color: #17a2b8; }
.lw-item-status.washed { color: #28a745; }
.lw-item-status.ironing { color: #ffc107; }
.lw-item-status.ironed { color: #28a745; }
.lw-item-status.completed { color: #28a745; }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>

<div class="content-wrapper">

  <section class="content-header">
    <h1><?= $page_title; ?></h1>
    <ol class="breadcrumb">
      <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Laundry Workflow</li>
    </ol>
  </section>

  <section class="content lw-container">
    <?php $this->load->view('comman/code_flashdata.php'); ?>

    <?php
    // Dynamic grid sizing based on active stages
    $stage_count = 2; // dropped_off + ready always shown
    if ($lw_stages['has_washing']) $stage_count++;
    if ($lw_stages['has_ironing']) $stage_count++;
    $col_class = 'col-md-6';
    if ($stage_count == 3) $col_class = 'col-md-4';
    if ($stage_count == 4) $col_class = 'col-md-3';
    ?>

    <!-- Workflow Settings -->
    <div class="row" style="margin-bottom:10px;">
      <div class="col-md-12">
        <div class="box box-default">
          <div class="box-header with-border" style="cursor:pointer;" onclick="$('#lw-settings').toggle();">
            <h3 class="box-title"><i class="fa fa-cog"></i> Workflow Settings</h3>
            <div class="box-tools pull-right">
              <span class="text-muted" style="font-size:12px;">Click to expand</span>
            </div>
          </div>
          <div class="box-body" id="lw-settings" style="display:none;">
            <?= form_open('operations/laundry', ['class'=>'form-inline']); ?>
              <input type="hidden" name="save_lw_config" value="1">
              <div class="form-group" style="margin-right:20px;">
                <label style="font-weight:400;">
                  <input type="checkbox" name="has_washing" value="1" <?= !empty($lw_stages['has_washing']) ? 'checked' : ''; ?>> Include Washing Stage
                </label>
              </div>
              <div class="form-group" style="margin-right:20px;">
                <label style="font-weight:400;">
                  <input type="checkbox" name="has_ironing" value="1" <?= !empty($lw_stages['has_ironing']) ? 'checked' : ''; ?>> Include Ironing Stage
                </label>
              </div>
              <button type="submit" class="btn btn-primary btn-xs"><i class="fa fa-save"></i> Save Workflow</button>
              <p class="text-muted" style="margin-top:8px;font-size:12px;">
                Your current flow: <strong>Dropped Off → <?= $lw_stages['has_washing'] ? 'Washing → ' : ''; ?><?= $lw_stages['has_ironing'] ? 'Ironing → ' : ''; ?>Ready → Collected</strong>
              </p>
            <?= form_close(); ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="row" style="margin-bottom:8px;">
      <div class="col-md-12">
        <div class="pull-right">
          <span id="last_refresh" class="text-muted" style="margin-right:12px;font-size:12px;"><i class="fa fa-refresh"></i> Auto-refresh every 15s</span>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- DROPPED OFF -->
      <div class="<?= $col_class; ?> lw-column">
        <div class="box box-danger">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-inbox"></i> Dropped Off</h3>
            <span class="badge bg-red lw-badge" id="count_dropped_off"><?= $status_counts['dropped_off']; ?></span>
          </div>
          <div class="box-body" id="col_dropped_off">
            <?php $has = false; foreach($orders as $o): if($o->status != 'dropped_off') continue; $has = true; ?>
            <div class="lw-card dropped_off" data-lid="<?= $o->laundry_order_id; ?>">
              <div class="lw-order-id">#<?= htmlspecialchars($o->sales_code); ?></div>
              <div class="lw-customer"><i class="fa fa-user"></i> <?= htmlspecialchars($o->customer_name ?: 'Walk-in'); ?></div>
              <div class="lw-drop-time" style="font-size:10px;color:#888;margin-bottom:4px;"><i class="fa fa-clock-o"></i> <?= $o->drop_off_time; ?></div>
              <div class="lw-items">
                <?php foreach($o->items as $itm): ?>
                <div class="lw-item">
                  <span class="lw-item-qty"><?= (int)$itm->sales_qty; ?></span>
                  <?= htmlspecialchars($itm->item_name); ?>
                  <?= lw_item_service_tag($itm->service_type); ?>
                  <span class="lw-item-status <?= $itm->item_status; ?>">(<?= ucfirst(str_replace('_', ' ', $itm->item_status)); ?>)</span>
                </div>
                <?php endforeach; ?>
              </div>
              <?php if($o->item_summary['can_start_washing']): ?>
              <button class="btn btn-info btn-xs btn-block" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'start_washing', this)"><i class="fa fa-tint"></i> Start Washing</button>
              <?php elseif($o->item_summary['can_finish_washing']): ?>
              <button class="btn btn-info btn-xs btn-block" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'finish_washing', this)"><i class="fa fa-check"></i> Washing Done</button>
              <?php elseif($o->item_summary['can_start_ironing']): ?>
              <button class="btn btn-warning btn-xs btn-block" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'start_ironing', this)"><i class="fa fa-refresh"></i> Start Ironing</button>
              <?php elseif($o->item_summary['can_finish_ironing']): ?>
              <button class="btn btn-warning btn-xs btn-block" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'finish_ironing', this)"><i class="fa fa-check"></i> Ironing Done</button>
              <?php elseif($o->item_summary['all_completed']): ?>
              <button class="btn btn-success btn-xs btn-block" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'mark_ready', this)"><i class="fa fa-check-circle"></i> Mark Ready</button>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if(!$has): ?><div class="lw-empty"><i class="fa fa-inbox" style="font-size:28px;color:#ccc;"></i><br>No new drop-offs</div><?php endif; ?>
          </div>
        </div>
      </div>

      <!-- WASHING -->
      <div class="<?= $col_class; ?> lw-column">
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-tint"></i> Washing</h3>
            <span class="badge bg-aqua lw-badge" id="count_washing"><?= $status_counts['washing']; ?></span>
          </div>
          <div class="box-body" id="col_washing">
            <?php $has = false; foreach($orders as $o): if($o->status != 'washing') continue; $has = true; ?>
            <div class="lw-card washing" data-lid="<?= $o->laundry_order_id; ?>">
              <div class="lw-order-id">#<?= htmlspecialchars($o->sales_code); ?></div>
              <div class="lw-customer"><i class="fa fa-user"></i> <?= htmlspecialchars($o->customer_name ?: 'Walk-in'); ?></div>
              <div class="lw-drop-time" style="font-size:10px;color:#888;margin-bottom:4px;"><i class="fa fa-clock-o"></i> <?= $o->drop_off_time; ?></div>
              <div class="lw-items">
                <?php foreach($o->items as $itm): ?>
                <div class="lw-item">
                  <span class="lw-item-qty"><?= (int)$itm->sales_qty; ?></span>
                  <?= htmlspecialchars($itm->item_name); ?>
                  <?= lw_item_service_tag($itm->service_type); ?>
                  <span class="lw-item-status <?= $itm->item_status; ?>">(<?= ucfirst(str_replace('_', ' ', $itm->item_status)); ?>)</span>
                </div>
                <?php endforeach; ?>
              </div>
              <?php if($o->item_summary['can_finish_washing']): ?>
              <button class="btn btn-info btn-xs btn-block" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'finish_washing', this)"><i class="fa fa-check"></i> Washing Done</button>
              <?php elseif($o->item_summary['can_start_ironing']): ?>
              <button class="btn btn-warning btn-xs btn-block" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'start_ironing', this)"><i class="fa fa-refresh"></i> Start Ironing</button>
              <?php elseif($o->item_summary['all_completed']): ?>
              <button class="btn btn-success btn-xs btn-block" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'mark_ready', this)"><i class="fa fa-check-circle"></i> Mark Ready</button>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if(!$has): ?><div class="lw-empty"><i class="fa fa-tint" style="font-size:28px;color:#ccc;"></i><br>Nothing washing</div><?php endif; ?>
          </div>
        </div>
      </div>

      <!-- IRONING -->
      <div class="<?= $col_class; ?> lw-column">
        <div class="box box-warning">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-refresh"></i> Ironing</h3>
            <span class="badge bg-yellow lw-badge" id="count_ironing"><?= $status_counts['ironing']; ?></span>
          </div>
          <div class="box-body" id="col_ironing">
            <?php $has = false; foreach($orders as $o): if($o->status != 'ironing') continue; $has = true; ?>
            <div class="lw-card ironing" data-lid="<?= $o->laundry_order_id; ?>">
              <div class="lw-order-id">#<?= htmlspecialchars($o->sales_code); ?></div>
              <div class="lw-customer"><i class="fa fa-user"></i> <?= htmlspecialchars($o->customer_name ?: 'Walk-in'); ?></div>
              <div class="lw-drop-time" style="font-size:10px;color:#888;margin-bottom:4px;"><i class="fa fa-clock-o"></i> <?= $o->drop_off_time; ?></div>
              <div class="lw-items">
                <?php foreach($o->items as $itm): ?>
                <div class="lw-item">
                  <span class="lw-item-qty"><?= (int)$itm->sales_qty; ?></span>
                  <?= htmlspecialchars($itm->item_name); ?>
                  <?= lw_item_service_tag($itm->service_type); ?>
                  <span class="lw-item-status <?= $itm->item_status; ?>">(<?= ucfirst(str_replace('_', ' ', $itm->item_status)); ?>)</span>
                </div>
                <?php endforeach; ?>
              </div>
              <?php if($o->item_summary['can_finish_ironing']): ?>
              <button class="btn btn-warning btn-xs btn-block" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'finish_ironing', this)"><i class="fa fa-check"></i> Ironing Done</button>
              <?php elseif($o->item_summary['all_completed']): ?>
              <button class="btn btn-success btn-xs btn-block" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'mark_ready', this)"><i class="fa fa-check-circle"></i> Mark Ready</button>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if(!$has): ?><div class="lw-empty"><i class="fa fa-refresh" style="font-size:28px;color:#ccc;"></i><br>Nothing ironing</div><?php endif; ?>
          </div>
        </div>
      </div>

      <!-- READY -->
      <div class="<?= $col_class; ?> lw-column">
        <div class="box box-success">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-check-circle"></i> Ready</h3>
            <span class="badge bg-green lw-badge" id="count_ready"><?= $status_counts['ready']; ?></span>
          </div>
          <div class="box-body" id="col_ready">
            <?php $has = false; foreach($orders as $o): if($o->status != 'ready') continue; $has = true; ?>
            <div class="lw-card ready" data-lid="<?= $o->laundry_order_id; ?>">
              <div class="lw-order-id">#<?= htmlspecialchars($o->sales_code); ?></div>
              <div class="lw-customer"><i class="fa fa-user"></i> <?= htmlspecialchars($o->customer_name ?: 'Walk-in'); ?></div>
              <div class="lw-drop-time" style="font-size:10px;color:#888;margin-bottom:4px;"><i class="fa fa-clock-o"></i> <?= $o->drop_off_time; ?></div>
              <div class="lw-items">
                <?php foreach($o->items as $itm): ?>
                <div class="lw-item">
                  <span class="lw-item-qty"><?= (int)$itm->sales_qty; ?></span>
                  <?= htmlspecialchars($itm->item_name); ?>
                  <?= lw_item_service_tag($itm->service_type); ?>
                  <span class="lw-item-status completed">(Completed)</span>
                </div>
                <?php endforeach; ?>
              </div>
              <button class="btn btn-default btn-xs btn-block" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'collected', this)"><i class="fa fa-hand-o-right"></i> Customer Collected</button>
            </div>
            <?php endforeach; ?>
            <?php if(!$has): ?><div class="lw-empty"><i class="fa fa-check-circle" style="font-size:28px;color:#ccc;"></i><br>Nothing ready for pickup</div><?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Recently Collected / Picked Up -->
    <?php if(!empty($collected)): ?>
    <div class="row lw-collected-row">
      <div class="col-md-12">
        <div class="box box-default">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-check-square-o"></i> Picked Up by Customer</h3>
            <span class="badge bg-gray"><?= count($collected); ?></span>
          </div>
          <div class="box-body">
            <div class="row">
              <?php foreach(array_slice($collected, 0, 6) as $sv): ?>
              <div class="col-md-2 col-sm-4 col-xs-6" style="margin-bottom:8px;">
                <div style="background:#f4f4f4;padding:8px;border-radius:3px;text-align:center; border-left: 3px solid #6c757d;">
                  <div style="font-size:12px;font-weight:700;">#<?= htmlspecialchars($sv->sales_code); ?></div>
                  <div style="font-size:11px;color:#777;"><?= htmlspecialchars($sv->customer_name ?: 'Walk-in'); ?></div>
                  <div style="font-size:11px;color:#999;margin-top:3px;"><i class="fa fa-check-square-o" style="color:#28a745;"></i> <?= (!empty($sv->updated_at) && $sv->updated_at !== '0000-00-00 00:00:00') ? date('H:i', strtotime($sv->updated_at)) : 'Just now'; ?></div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </section>

</div>
</div>

<?php
// Helper: render laundry service type tag from configured service_type
function lw_item_service_tag($service_type) {
    switch ($service_type) {
        case 'dry_clean':
            return '<span class="lw-service-tag dryclean">Dry Clean</span>';
        case 'wash_iron':
            return '<span class="lw-service-tag washiron">Wash + Iron</span>';
        case 'iron_only':
            return '<span class="lw-service-tag iron">Iron Only</span>';
        case 'wash_only':
            return '<span class="lw-service-tag wash">Wash Only</span>';
        default:
            return '<span class="lw-service-tag wash">Wash + Iron</span>';
    }
}
?>
<?php $this->load->view('comman/code_js.php'); ?>
<script>
function updateLwStatus(laundryOrderId, newStatus, btn) {
    var $btn = $(btn);
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');
    $.post('<?= base_url("operations/laundry_update_status"); ?>', {
        laundry_order_id: laundryOrderId,
        status: newStatus,
        <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
    }, function(res) {
        if(res.success) {
            location.reload();
        } else {
            $btn.prop('disabled', false).html('<i class="fa fa-exclamation-triangle"></i> Failed');
        }
    }, 'json');
}

var lwPrevDropped = <?= (int)$status_counts['dropped_off']; ?>;
<?php if(!empty($lw_stages['has_washing'])): ?>var lwPrevWashing = <?= (int)$status_counts['washing']; ?>;<?php endif; ?>
<?php if(!empty($lw_stages['has_ironing'])): ?>var lwPrevIroning = <?= (int)$status_counts['ironing']; ?>;<?php endif; ?>
var lwPrevReady = <?= (int)$status_counts['ready']; ?>;

// Auto-refresh counts; reload page if counts changed so new cards appear
function lwAutoRefresh() {
    $.get('<?= base_url("operations/laundry"); ?>', {ajax: 1}, function(data) {
        if(data.status_counts) {
            var curDrop = parseInt(data.status_counts.dropped_off) || 0;
            var curReady = parseInt(data.status_counts.ready) || 0;
            var totalCur = curDrop + curReady;
            var totalPrev = lwPrevDropped + lwPrevReady;
            <?php if(!empty($lw_stages['has_washing'])): ?>
            var curWash = parseInt(data.status_counts.washing) || 0;
            totalCur += curWash; totalPrev += lwPrevWashing;
            <?php endif; ?>
            <?php if(!empty($lw_stages['has_ironing'])): ?>
            var curIron = parseInt(data.status_counts.ironing) || 0;
            totalCur += curIron; totalPrev += lwPrevIroning;
            <?php endif; ?>
            if(totalCur !== totalPrev) {
                location.reload();
                return;
            }
            lwPrevDropped = curDrop; lwPrevReady = curReady;
            <?php if(!empty($lw_stages['has_washing'])): ?>lwPrevWashing = curWash; $('#count_washing').text(curWash);<?php endif; ?>
            <?php if(!empty($lw_stages['has_ironing'])): ?>lwPrevIroning = curIron; $('#count_ironing').text(curIron);<?php endif; ?>
            $('#count_dropped_off').text(curDrop);
            $('#count_ready').text(curReady);
        }
        $('#last_refresh').text('Refreshed: ' + new Date().toLocaleTimeString());
    });
}
setInterval(lwAutoRefresh, 15000);
$(".laundry-active-li").addClass("active");
</script>
</body>
</html>
