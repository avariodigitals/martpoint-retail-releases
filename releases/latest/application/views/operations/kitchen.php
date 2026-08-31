<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php'); ?>
<style>
/* KDS Kanban Board */
.kds-column { padding: 0 8px; }
.kds-column .box {
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: none;
}
.kds-column .box-header {
    border-radius: 6px 6px 0 0;
    padding: 14px 16px;
}
.kds-column .box-header .box-title {
    font-size: 16px;
    font-weight: 700;
}
.kds-column .box-body {
    height: calc(100vh - 260px);
    min-height: 300px;
    max-height: calc(100vh - 260px);
    overflow-y: auto !important;
    overflow-x: hidden;
    background: #f5f5f5;
    padding: 12px;
    border-radius: 0 0 6px 6px;
    position: relative;
}
.kds-card {
    background: #fff;
    border-radius: 6px;
    padding: 14px;
    margin-bottom: 12px;
    border-left: 4px solid #ccc;
    box-shadow: 0 2px 4px rgba(0,0,0,0.06);
    transition: all 0.2s ease;
}
.kds-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
.kds-card.new { border-left-color: #dc3545; }
.kds-card.preparing { border-left-color: #ffc107; }
.kds-card.ready { border-left-color: #28a745; }
.kds-order-id {
    font-size: 14px;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 4px;
    letter-spacing: 0.3px;
}
.kds-customer {
    font-size: 12px;
    color: #666;
    margin-bottom: 10px;
}
.kds-items { margin-bottom: 12px; }
.kds-item {
    font-size: 14px;
    padding: 5px 0;
    border-bottom: 1px dashed #e0e0e0;
    color: #333;
}
.kds-item:last-child { border-bottom: none; }
.kds-item-qty {
    display: inline-block;
    background: #e9ecef;
    color: #495057;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    margin-right: 6px;
    min-width: 24px;
    text-align: center;
}
.kds-timer {
    font-size: 20px;
    font-weight: 700;
    font-family: 'SF Mono', 'Courier New', monospace;
    color: #dc3545;
    margin-bottom: 12px;
    background: #f8f9fa;
    padding: 6px 10px;
    border-radius: 4px;
    display: inline-block;
}
.kds-timer.warn { color: #ffc107; background: #fff3cd; }
.kds-empty {
    text-align: center;
    padding: 60px 20px;
    color: #adb5bd;
    font-size: 14px;
}
.kds-empty i {
    margin-bottom: 12px;
    display: block;
}
/* Button styling - big for tablet/kitchen use */
.kds-column .btn-block {
    padding: 14px;
    font-size: 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-radius: 5px;
}
/* Box color overrides for stronger headers */
.box-danger .box-header {
    background: #dc3545 !important;
    color: #fff;
}
.box-danger .box-header .box-title { color: #fff; }
.box-warning .box-header {
    background: #ffc107 !important;
    color: #212529;
}
.box-warning .box-header .box-title { color: #212529; }
.box-success .box-header {
    background: #28a745 !important;
    color: #fff;
}
.box-success .box-header .box-title { color: #fff; }
/* Fullscreen mode hides chrome */
body.kds-fullscreen .main-sidebar,
body.kds-fullscreen .main-header,
body.kds-fullscreen .content-header,
body.kds-fullscreen .breadcrumb {
    display: none !important;
}
body.kds-fullscreen .content-wrapper {
    margin-left: 0 !important;
    padding-top: 0 !important;
}
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1><?= $page_title; ?></h1>
    <ol class="breadcrumb">
        <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Kitchen Display</li>
    </ol>
</section>

<!-- Main content -->
<section class="content kds-container">
    <?php $this->load->view('comman/code_flashdata.php'); ?>
    <!-- Toolbar -->
    <div class="row" style="margin-bottom:10px;">
        <div class="col-md-12">
            <div class="pull-right">
                <a href="<?= base_url('operations/menu_items'); ?>" class="btn btn-primary btn-sm" style="margin-right:10px;">
                    <i class="fa fa-plus-circle"></i> Menu Items
                </a>
                <span id="last_refresh" class="text-muted" style="margin-right:15px;font-size:13px;"><i class="fa fa-refresh"></i> Auto-refresh every 15s</span>
                <button class="btn btn-default btn-sm" onclick="toggleKdsFullscreen()" title="Toggle Full Screen">
                    <i class="fa fa-expand"></i> Full Screen
                </button>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- NEW Column -->
        <div class="col-md-4 kds-column">
          <div class="box box-danger">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-bell-o"></i> NEW</h3>
              <span class="badge bg-red" id="count_new"><?= $status_counts['new']; ?></span>
            </div>
            <div class="box-body" id="col_new">
                <?php $has_new = false; foreach($orders as $o): if($o->kds_status != 'new') continue; $has_new = true; ?>
                <div class="kds-card new" data-kid="<?= $o->kitchen_order_id; ?>" data-elapsed="<?= $o->elapsed_seconds; ?>">
                    <div class="kds-order-id">#<?= htmlspecialchars($o->sales_code); ?></div>
                    <div class="kds-customer"><i class="fa fa-user"></i> <?= htmlspecialchars($o->customer_name ?: 'Walk-in'); ?></div>
                    <div class="kds-timer" data-elapsed="<?= $o->elapsed_seconds; ?>">00:00</div>
                    <div class="kds-items">
                        <?php foreach($o->items as $itm): ?>
                        <div class="kds-item"><span class="kds-item-qty"><?= (int)$itm->sales_qty; ?></span> <?= htmlspecialchars($itm->item_name); ?></div>
                        <?php endforeach; ?>
                    </div>
                    <button class="btn btn-danger btn-block" onclick="updateKdsStatus(<?= $o->kitchen_order_id; ?>, 'preparing', this)">
                        <i class="fa fa-fire"></i> Start Preparing
                    </button>
                </div>
                <?php endforeach; ?>
                <?php if(!$has_new): ?>
                <div class="kds-empty"><i class="fa fa-check-circle" style="font-size:32px;color:#ccc;"></i><br>No new orders</div>
                <?php endif; ?>
              </div>
            </div>
          </div>

        <!-- PREPARING Column -->
        <div class="col-md-4 kds-column">
          <div class="box box-warning">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-fire"></i> PREPARING</h3>
              <span class="badge bg-yellow" id="count_preparing"><?= $status_counts['preparing']; ?></span>
            </div>
            <div class="box-body" id="col_preparing">
                <?php $has_prep = false; foreach($orders as $o): if($o->kds_status != 'preparing') continue; $has_prep = true; ?>
                <div class="kds-card preparing" data-kid="<?= $o->kitchen_order_id; ?>" data-elapsed="<?= $o->elapsed_seconds; ?>">
                    <div class="kds-order-id">#<?= htmlspecialchars($o->sales_code); ?></div>
                    <div class="kds-customer"><i class="fa fa-user"></i> <?= htmlspecialchars($o->customer_name ?: 'Walk-in'); ?></div>
                    <div class="kds-timer" data-elapsed="<?= $o->elapsed_seconds; ?>">00:00</div>
                    <div class="kds-items">
                        <?php foreach($o->items as $itm): ?>
                        <div class="kds-item"><span class="kds-item-qty"><?= (int)$itm->sales_qty; ?></span> <?= htmlspecialchars($itm->item_name); ?></div>
                        <?php endforeach; ?>
                    </div>
                    <button class="btn btn-warning btn-block" onclick="updateKdsStatus(<?= $o->kitchen_order_id; ?>, 'ready', this)">
                        <i class="fa fa-check-circle"></i> Mark Ready
                    </button>
                </div>
                <?php endforeach; ?>
                <?php if(!$has_prep): ?>
                <div class="kds-empty"><i class="fa fa-clock-o" style="font-size:32px;color:#ccc;"></i><br>Nothing being prepared</div>
                <?php endif; ?>
              </div>
            </div>
          </div>

        <!-- READY Column -->
        <div class="col-md-4 kds-column">
          <div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-check-circle"></i> READY</h3>
              <span class="badge bg-green" id="count_ready"><?= $status_counts['ready']; ?></span>
            </div>
            <div class="box-body" id="col_ready">
                <?php $has_ready = false; foreach($orders as $o): if($o->kds_status != 'ready') continue; $has_ready = true; ?>
                <div class="kds-card ready" data-kid="<?= $o->kitchen_order_id; ?>" data-elapsed="<?= $o->elapsed_seconds; ?>">
                    <div class="kds-order-id">#<?= htmlspecialchars($o->sales_code); ?></div>
                    <div class="kds-customer"><i class="fa fa-user"></i> <?= htmlspecialchars($o->customer_name ?: 'Walk-in'); ?></div>
                    <div class="kds-timer" data-elapsed="<?= $o->elapsed_seconds; ?>">00:00</div>
                    <div class="kds-items">
                        <?php foreach($o->items as $itm): ?>
                        <div class="kds-item"><span class="kds-item-qty"><?= (int)$itm->sales_qty; ?></span> <?= htmlspecialchars($itm->item_name); ?></div>
                        <?php endforeach; ?>
                    </div>
                    <button class="btn btn-success btn-block" onclick="updateKdsStatus(<?= $o->kitchen_order_id; ?>, 'served', this)">
                        <i class="fa fa-hand-o-right"></i> Served / Collected
                    </button>
                </div>
                <?php endforeach; ?>
                <?php if(!$has_ready): ?>
                <div class="kds-empty"><i class="fa fa-thumbs-up" style="font-size:32px;color:#ccc;"></i><br>Nothing ready yet</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
    </div>

    <!-- Recently Served -->
    <?php if(!empty($served)): ?>
    <div class="row" style="margin-top:20px;">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-history"></i> Recently Served</h3></div>
                <div class="box-body">
                    <div class="row">
                        <?php foreach(array_slice($served, 0, 5) as $sv): ?>
                        <div class="col-md-2 col-sm-4 col-xs-6" style="margin-bottom:10px;">
                            <div style="background:#f4f4f4;padding:10px;border-radius:3px;text-align:center;">
                                <div style="font-size:12px;font-weight:700;">#<?= htmlspecialchars($sv->sales_code); ?></div>
                                <div style="font-size:11px;color:#777;"><?= htmlspecialchars($sv->customer_name ?: 'Walk-in'); ?></div>
                                <div style="font-size:11px;color:#999;margin-top:4px;"><i class="fa fa-clock-o"></i> <?= date('H:i', strtotime($sv->updated_at)); ?></div>
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

<?php $this->load->view('comman/code_js.php'); ?>
<script>
var base_url = '<?= base_url(); ?>';
var previousNewCount = <?= (int)$status_counts['new']; ?>;
var previousPrepCount = <?= (int)$status_counts['preparing']; ?>;
var previousReadyCount = <?= (int)$status_counts['ready']; ?>;

// Update all timers every second
function updateTimers() {
    document.querySelectorAll('.kds-timer').forEach(function(el) {
        var elapsed = parseInt(el.getAttribute('data-elapsed')) || 0;
        elapsed++;
        el.setAttribute('data-elapsed', elapsed);
        var m = Math.floor(elapsed / 60);
        var s = elapsed % 60;
        el.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        // Color code based on wait time
        el.classList.remove('ok','warn');
        if(elapsed > 600) el.classList.add('warn');   // > 10 min orange
        if(elapsed > 1200) el.classList.remove('warn'); // > 20 min stays red (default)
    });
}
setInterval(updateTimers, 1000);
updateTimers();

// Advance order status via AJAX
function updateKdsStatus(kitchenOrderId, newStatus, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Updating...';
    $.ajax({
        url: base_url + 'operations/kitchen_update_status',
        type: 'POST',
        data: { kitchen_order_id: kitchenOrderId, status: newStatus },
        dataType: 'json',
        success: function(res) {
            if(res.success) {
                // Reload page to show re-sorted cards
                location.reload();
            } else {
                toastr.error('Update failed. Please try again.');
                btn.disabled = false;
                btn.innerHTML = 'Try Again';
            }
        },
        error: function() {
            toastr.error('Network error. Please try again.');
            btn.disabled = false;
            btn.innerHTML = 'Try Again';
        }
    });
}

// Sound alert for new orders (simple beep using Web Audio API)
function playNewOrderSound() {
    try {
        var AudioContext = window.AudioContext || window.webkitAudioContext;
        if(!AudioContext) return;
        var ctx = new AudioContext();
        var osc = ctx.createOscillator();
        var gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(440, ctx.currentTime + 0.3);
        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.5);
    } catch(e) {}
}

// Auto-refresh every 15 seconds; reload page if counts changed so new cards appear
function kdsAutoRefresh() {
    $.ajax({
        url: base_url + 'operations/kitchen?ajax=1',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if(data.status_counts) {
                var currentNew = parseInt(data.status_counts.new) || 0;
                var currentPrep = parseInt(data.status_counts.preparing) || 0;
                var currentReady = parseInt(data.status_counts.ready) || 0;
                var totalCurrent = currentNew + currentPrep + currentReady;
                var totalPrevious = previousNewCount + previousPrepCount + previousReadyCount;

                if(totalCurrent !== totalPrevious) {
                    if(currentNew > previousNewCount) {
                        playNewOrderSound();
                    }
                    // Reload to render new order cards
                    location.reload();
                    return;
                }
                previousNewCount = currentNew;
                previousPrepCount = currentPrep;
                previousReadyCount = currentReady;
                // Update badges
                $('#count_new').text(currentNew);
                $('#count_preparing').text(currentPrep);
                $('#count_ready').text(currentReady);
            }
            $('#last_refresh').text('Refreshed: ' + new Date().toLocaleTimeString());
        }
    });
}
setInterval(kdsAutoRefresh, 15000);

// Fullscreen toggle
function toggleKdsFullscreen() {
    document.body.classList.toggle('kds-fullscreen');
    var btn = document.querySelector('.kds-fullscreen-btn');
    if(document.body.classList.contains('kds-fullscreen')) {
        btn.innerHTML = '<i class="fa fa-compress"></i> Exit Full';
        if(document.documentElement.requestFullscreen) document.documentElement.requestFullscreen();
    } else {
        btn.innerHTML = '<i class="fa fa-expand"></i> Full Screen';
        if(document.exitFullscreen) document.exitFullscreen();
    }
}

$(".kitchen-active-li").addClass("active");
</script>
</body>
</html>
