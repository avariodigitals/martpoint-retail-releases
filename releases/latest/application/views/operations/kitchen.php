<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.kds-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap}
.kds-toolbar .right{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.kds-fs-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border:1px solid var(--mp-border);border-radius:10px;background:var(--mp-surface);color:var(--mp-ink);font-size:13px;font-weight:600;cursor:pointer;transition:all .15s ease}
.kds-fs-btn:hover{background:var(--mp-bg)}
.kds-fs-btn.active{background:var(--mp-primary);color:#fff;border-color:var(--mp-primary)}
.kds-refresh-tag{font-size:12px;color:var(--mp-muted);font-weight:500}

.kds-board{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.kds-col{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:14px;overflow:hidden;display:flex;flex-direction:column}
.kds-col-head{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;color:#fff}
.kds-col-head.new{background:var(--mp-danger)}
.kds-col-head.preparing{background:var(--mp-warning);color:#212529}
.kds-col-head.ready{background:var(--mp-success)}
.kds-col-head .title{font-size:14px;font-weight:800;letter-spacing:.5px;display:flex;align-items:center;gap:8px;text-transform:uppercase}
.kds-col-head .count{display:inline-flex;align-items:center;justify-content:center;min-width:26px;height:26px;padding:0 9px;border-radius:13px;font-size:12px;font-weight:800;background:rgba(255,255,255,.25);color:inherit}
.kds-col-body{flex:1;overflow-y:auto;padding:12px;background:var(--mp-bg);min-height:300px;max-height:calc(100vh - 280px)}
.kds-card{background:var(--mp-surface);border-radius:10px;padding:14px;margin-bottom:12px;border-left:4px solid var(--mp-border);box-shadow:var(--mp-shadow-sm);transition:all .15s ease}
.kds-card:hover{box-shadow:var(--mp-shadow);transform:translateY(-2px)}
.kds-card.new{border-left-color:var(--mp-danger)}
.kds-card.preparing{border-left-color:var(--mp-warning)}
.kds-card.ready{border-left-color:var(--mp-success)}
.kds-order-id{font-size:14px;font-weight:800;color:var(--mp-text);margin-bottom:4px;letter-spacing:.3px}
.kds-customer{font-size:12px;color:var(--mp-muted);margin-bottom:10px}
.kds-items{margin-bottom:12px}
.kds-item{font-size:14px;padding:5px 0;border-bottom:1px dashed var(--mp-border);color:var(--mp-text)}
.kds-item:last-child{border-bottom:none}
.kds-item-qty{display:inline-block;background:var(--mp-bg);color:var(--mp-ink);font-weight:700;padding:2px 8px;border-radius:4px;font-size:12px;margin-right:6px;min-width:24px;text-align:center}
.kds-timer{font-size:20px;font-weight:700;font-family:'SF Mono','Courier New',monospace;color:var(--mp-danger);margin-bottom:12px;background:var(--mp-bg);padding:6px 10px;border-radius:6px;display:inline-block}
.kds-timer.warn{color:var(--mp-warning);background:rgba(245,158,11,.1)}
.kds-empty{text-align:center;padding:60px 20px;color:var(--mp-muted);font-size:14px}
.kds-empty i{display:block;margin-bottom:12px;font-size:32px;color:var(--mp-border)}
.kds-act-btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:14px;border:none;border-radius:8px;font-size:14px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;cursor:pointer;transition:all .15s ease}
.kds-act-btn.danger{background:var(--mp-danger);color:#fff}
.kds-act-btn.danger:hover{background:#B91C1C}
.kds-act-btn.warn{background:var(--mp-warning);color:#212529}
.kds-act-btn.warn:hover{background:#D97706;color:#fff}
.kds-act-btn.success{background:var(--mp-success);color:#fff}
.kds-act-btn.success:hover{background:#047857}
.kds-act-btn:disabled{opacity:.6}

.kds-served-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px}
.kds-served-card{background:var(--mp-bg);padding:12px;border-radius:10px;text-align:center}
.kds-served-card .code{font-size:12px;font-weight:700;color:var(--mp-text)}
.kds-served-card .cust{font-size:11px;color:var(--mp-muted);margin-top:2px}
.kds-served-card .time{font-size:11px;color:var(--mp-muted);margin-top:4px}

/* Fullscreen mode hides shell chrome */
body.kds-fullscreen .mp-header,
body.kds-fullscreen .mp-nav{display:none!important}
body.kds-fullscreen .mp-main{padding:16px 20px!important;overflow-y:auto!important}
body.kds-fullscreen .kds-col-body{max-height:calc(100vh - 180px)}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Kitchen Display System — real-time order tracking</div>
  </div>
  <div class="kds-toolbar" style="margin:0">
    <div class="right">
      <a href="<?= base_url('operations/menu_items'); ?>" class="mp-qa-btn blue"><i class="fa fa-plus-circle"></i> Menu Items</a>
      <span id="last_refresh" class="kds-refresh-tag"><i class="fa fa-refresh"></i> Auto-refresh 15s</span>
      <button type="button" id="kds-fullscreen-btn" class="kds-fs-btn" onclick="toggleKdsFullscreen()"><i class="fa fa-expand"></i> Fullscreen</button>
    </div>
  </div>
</div>

<?php $this->load->view('comman/code_flashdata.php'); ?>

<div class="kds-board">
  <!-- NEW Column -->
  <div class="kds-col">
    <div class="kds-col-head new">
      <div class="title"><i class="fa fa-bell-o"></i> New</div>
      <span class="count" id="count_new"><?= $status_counts['new']; ?></span>
    </div>
    <div class="kds-col-body" id="col_new">
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
        <button class="kds-act-btn danger" onclick="updateKdsStatus(<?= $o->kitchen_order_id; ?>, 'preparing', this)">
          <i class="fa fa-fire"></i> Start Preparing
        </button>
      </div>
      <?php endforeach; ?>
      <?php if(!$has_new): ?>
      <div class="kds-empty"><i class="fa fa-check-circle"></i>No new orders</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- PREPARING Column -->
  <div class="kds-col">
    <div class="kds-col-head preparing">
      <div class="title"><i class="fa fa-fire"></i> Preparing</div>
      <span class="count" id="count_preparing"><?= $status_counts['preparing']; ?></span>
    </div>
    <div class="kds-col-body" id="col_preparing">
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
        <button class="kds-act-btn warn" onclick="updateKdsStatus(<?= $o->kitchen_order_id; ?>, 'ready', this)">
          <i class="fa fa-check-circle"></i> Mark Ready
        </button>
      </div>
      <?php endforeach; ?>
      <?php if(!$has_prep): ?>
      <div class="kds-empty"><i class="fa fa-clock-o"></i>Nothing being prepared</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- READY Column -->
  <div class="kds-col">
    <div class="kds-col-head ready">
      <div class="title"><i class="fa fa-check-circle"></i> Ready</div>
      <span class="count" id="count_ready"><?= $status_counts['ready']; ?></span>
    </div>
    <div class="kds-col-body" id="col_ready">
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
        <button class="kds-act-btn success" onclick="updateKdsStatus(<?= $o->kitchen_order_id; ?>, 'served', this)">
          <i class="fa fa-hand-o-right"></i> Served / Collected
        </button>
      </div>
      <?php endforeach; ?>
      <?php if(!$has_ready): ?>
      <div class="kds-empty"><i class="fa fa-thumbs-up"></i>Nothing ready yet</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if(!empty($served)): ?>
<div class="mp-card-form" style="margin-top:20px">
  <div class="mp-card-head"><h3><i class="fa fa-history"></i> Recently Served</h3></div>
  <div class="mp-card-body">
    <div class="kds-served-grid">
      <?php foreach(array_slice($served, 0, 12) as $sv): ?>
      <div class="kds-served-card">
        <div class="code">#<?= htmlspecialchars($sv->sales_code); ?></div>
        <div class="cust"><?= htmlspecialchars($sv->customer_name ?: 'Walk-in'); ?></div>
        <div class="time"><i class="fa fa-clock-o"></i> <?= date('H:i', strtotime($sv->updated_at)); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
var base_url = '<?= base_url(); ?>';
var previousNewCount = <?= (int)$status_counts['new']; ?>;
var previousPrepCount = <?= (int)$status_counts['preparing']; ?>;
var previousReadyCount = <?= (int)$status_counts['ready']; ?>;

function updateTimers() {
    document.querySelectorAll('.kds-timer').forEach(function(el) {
        var elapsed = parseInt(el.getAttribute('data-elapsed')) || 0;
        elapsed++;
        el.setAttribute('data-elapsed', elapsed);
        var m = Math.floor(elapsed / 60);
        var s = elapsed % 60;
        el.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        el.classList.remove('ok','warn');
        if(elapsed > 600) el.classList.add('warn');
        if(elapsed > 1200) el.classList.remove('warn');
    });
}
setInterval(updateTimers, 1000);
updateTimers();

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
                    location.reload();
                    return;
                }
                previousNewCount = currentNew;
                previousPrepCount = currentPrep;
                previousReadyCount = currentReady;
                $('#count_new').text(currentNew);
                $('#count_preparing').text(currentPrep);
                $('#count_ready').text(currentReady);
            }
            $('#last_refresh').html('<i class="fa fa-refresh"></i> Refreshed: ' + new Date().toLocaleTimeString());
        }
    });
}
setInterval(kdsAutoRefresh, 15000);

function toggleKdsFullscreen() {
    document.body.classList.toggle('kds-fullscreen');
    var btn = document.getElementById('kds-fullscreen-btn');
    if(document.body.classList.contains('kds-fullscreen')) {
        btn.innerHTML = '<i class="fa fa-compress"></i> Exit Fullscreen';
        btn.classList.add('active');
        if(document.documentElement.requestFullscreen) document.documentElement.requestFullscreen();
    } else {
        btn.innerHTML = '<i class="fa fa-expand"></i> Fullscreen';
        btn.classList.remove('active');
        if(document.exitFullscreen) document.exitFullscreen();
    }
}
</script>
