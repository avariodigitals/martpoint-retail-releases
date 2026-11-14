<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
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
<style>
.lw-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap}
.lw-toolbar .left{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.lw-toolbar .right{display:flex;align-items:center;gap:10px}
.lw-fs-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border:1px solid var(--mp-border);border-radius:10px;background:var(--mp-surface);color:var(--mp-ink);font-size:13px;font-weight:600;cursor:pointer;transition:all .15s ease}
.lw-fs-btn:hover{background:var(--mp-bg)}
.lw-fs-btn.active{background:var(--mp-primary);color:#fff;border-color:var(--mp-primary)}
.lw-refresh-tag{font-size:12px;color:var(--mp-muted);font-weight:500}
.lw-settings-card{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:14px;margin-bottom:16px;overflow:hidden}
.lw-settings-head{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;cursor:pointer;user-select:none}
.lw-settings-head h3{font-size:14px;font-weight:700;margin:0;color:var(--mp-ink);display:flex;align-items:center;gap:8px}
.lw-settings-head h3 i{color:var(--mp-primary)}
.lw-settings-body{padding:18px;border-top:1px solid var(--mp-border);display:none}
.lw-settings-body.open{display:block}
.lw-settings-body .form-inline{display:flex;align-items:center;gap:20px;flex-wrap:wrap}
.lw-settings-body .form-inline label{font-weight:500;font-size:13px;color:var(--mp-ink);display:flex;align-items:center;gap:8px;cursor:pointer}
.lw-settings-body .form-inline input[type=checkbox]{width:18px;height:18px;cursor:pointer}
.lw-settings-body .mp-btn-primary{padding:8px 16px;font-size:13px}
.lw-settings-body .lw-flow-note{font-size:12px;color:var(--mp-muted);margin-top:10px}

.lw-board{display:grid;gap:14px}
.lw-board.cols-2{grid-template-columns:repeat(2,1fr)}
.lw-board.cols-3{grid-template-columns:repeat(3,1fr)}
.lw-board.cols-4{grid-template-columns:repeat(4,1fr)}
.lw-col{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:14px;overflow:hidden;display:flex;flex-direction:column}
.lw-col-head{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid var(--mp-border)}
.lw-col-head .title{font-size:14px;font-weight:700;color:var(--mp-ink);display:flex;align-items:center;gap:8px}
.lw-col-head .title i{font-size:16px}
.lw-col-head .count{display:inline-flex;align-items:center;justify-content:center;min-width:24px;height:24px;padding:0 8px;border-radius:12px;font-size:12px;font-weight:700;color:#fff}
.lw-col-head .count.red{background:var(--mp-danger)}
.lw-col-head .count.blue{background:var(--mp-primary)}
.lw-col-head .count.yellow{background:var(--mp-warning);color:#fff}
.lw-col-head .count.green{background:var(--mp-success)}
.lw-col-head .count.gray{background:var(--mp-muted)}
.lw-col-body{flex:1;overflow-y:auto;padding:10px;background:var(--mp-bg);min-height:300px;max-height:calc(100vh - 320px)}
.lw-card{background:var(--mp-surface);border-radius:10px;padding:12px;margin-bottom:10px;border-left:4px solid var(--mp-border);box-shadow:var(--mp-shadow-sm);transition:all .15s ease}
.lw-card:hover{transform:translateY(-1px);box-shadow:var(--mp-shadow)}
.lw-card.dropped_off{border-left-color:var(--mp-danger)}
.lw-card.washing{border-left-color:#0D9488}
.lw-card.ironing{border-left-color:var(--mp-warning)}
.lw-card.ready{border-left-color:var(--mp-success)}
.lw-card.pickedup{border-left-color:var(--mp-muted);opacity:.7}
.lw-order-id{font-size:13px;font-weight:700;color:var(--mp-text)}
.lw-customer{font-size:11px;color:var(--mp-muted);margin:4px 0 6px}
.lw-drop-time{font-size:10px;color:var(--mp-muted);margin-bottom:6px}
.lw-items{margin-bottom:6px}
.lw-item{font-size:12px;padding:4px 0;border-bottom:1px dashed var(--mp-border);color:var(--mp-text)}
.lw-item:last-child{border-bottom:none}
.lw-item-qty{display:inline-block;background:var(--mp-bg);color:var(--mp-ink);font-weight:700;padding:1px 7px;border-radius:4px;font-size:10px;margin-right:5px}
.lw-service-tag{display:inline-block;padding:2px 6px;border-radius:4px;font-size:9px;font-weight:700;text-transform:uppercase;margin-left:4px}
.lw-service-tag.wash{background:rgba(13,148,136,.12);color:#0D9488}
.lw-service-tag.iron{background:rgba(245,158,11,.12);color:var(--mp-warning)}
.lw-service-tag.dryclean{background:rgba(124,58,237,.12);color:#7C3AED}
.lw-service-tag.washiron{background:rgba(5,150,105,.12);color:var(--mp-success)}
.lw-item-status{font-size:10px;font-weight:600;margin-left:4px}
.lw-item-status.pending{color:var(--mp-danger)}
.lw-item-status.washing{color:#0D9488}
.lw-item-status.washed{color:var(--mp-success)}
.lw-item-status.ironing{color:var(--mp-warning)}
.lw-item-status.ironed{color:var(--mp-success)}
.lw-item-status.completed{color:var(--mp-success)}
.lw-empty{text-align:center;padding:40px 0;color:var(--mp-muted);font-size:13px}
.lw-empty i{display:block;margin-bottom:10px;font-size:32px;color:var(--mp-border)}
.lw-act-btn{display:flex;align-items:center;justify-content:center;gap:6px;width:100%;padding:8px 12px;border:none;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;transition:all .15s ease;margin-top:6px}
.lw-act-btn.info{background:rgba(13,148,136,.1);color:#0D9488}
.lw-act-btn.info:hover{background:#0D9488;color:#fff}
.lw-act-btn.warn{background:rgba(245,158,11,.1);color:var(--mp-warning)}
.lw-act-btn.warn:hover{background:var(--mp-warning);color:#fff}
.lw-act-btn.success{background:rgba(5,150,105,.1);color:var(--mp-success)}
.lw-act-btn.success:hover{background:var(--mp-success);color:#fff}
.lw-act-btn.neutral{background:var(--mp-bg);color:var(--mp-ink)}
.lw-act-btn.neutral:hover{background:var(--mp-border)}
.lw-act-btn:disabled{opacity:.6}

.lw-collected-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px}
.lw-collected-card{background:var(--mp-bg);padding:12px;border-radius:10px;text-align:center;border-left:3px solid var(--mp-muted)}
.lw-collected-card .code{font-size:12px;font-weight:700;color:var(--mp-text)}
.lw-collected-card .cust{font-size:11px;color:var(--mp-muted);margin-top:2px}
.lw-collected-card .time{font-size:11px;color:var(--mp-muted);margin-top:4px}
.lw-collected-card .time i{color:var(--mp-success)}

/* Fullscreen mode */
body.lw-fullscreen .mp-header,
body.lw-fullscreen .mp-nav{display:none!important}
body.lw-fullscreen .mp-main{padding:16px 20px!important;overflow-y:auto!important}
body.lw-fullscreen .lw-col-body{max-height:calc(100vh - 180px)}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Laundry workflow — track orders from drop-off to pickup</div>
  </div>
  <div style="display:flex;gap:10px;align-items:center">
    <span id="last_refresh" class="lw-refresh-tag"><i class="fa fa-refresh"></i> Auto-refresh 15s</span>
    <button type="button" id="lw-fullscreen" class="lw-fs-btn"><i class="fa fa-expand"></i> Fullscreen</button>
  </div>
</div>

<?php $this->load->view('comman/code_flashdata.php'); ?>

<?php
$stage_count = 2;
if ($lw_stages['has_washing']) $stage_count++;
if ($lw_stages['has_ironing']) $stage_count++;
$cols_class = 'cols-'.$stage_count;
?>

<div class="lw-settings-card">
  <div class="lw-settings-head" onclick="$('#lw-settings-body').toggleClass('open')">
    <h3><i class="fa fa-cog"></i> Workflow Settings</h3>
    <span class="lw-refresh-tag">Click to expand</span>
  </div>
  <div class="lw-settings-body" id="lw-settings-body">
    <?= form_open('operations/laundry', ['class'=>'form-inline']); ?>
      <input type="hidden" name="save_lw_config" value="1">
      <label><input type="checkbox" name="has_washing" value="1" <?= !empty($lw_stages['has_washing']) ? 'checked' : ''; ?>> Include Washing Stage</label>
      <label><input type="checkbox" name="has_ironing" value="1" <?= !empty($lw_stages['has_ironing']) ? 'checked' : ''; ?>> Include Ironing Stage</label>
      <button type="submit" class="mp-btn-primary"><i class="fa fa-save"></i> Save Workflow</button>
      <p class="lw-flow-note">Your current flow: <strong>Dropped Off → <?= $lw_stages['has_washing'] ? 'Washing → ' : ''; ?><?= $lw_stages['has_ironing'] ? 'Ironing → ' : ''; ?>Ready → Collected</strong></p>
    <?= form_close(); ?>
  </div>
</div>

<div class="lw-board <?= $cols_class; ?>">
  <!-- DROPPED OFF -->
  <div class="lw-col">
    <div class="lw-col-head">
      <div class="title"><i class="fa fa-inbox" style="color:var(--mp-danger)"></i> Dropped Off</div>
      <span class="count red" id="count_dropped_off"><?= $status_counts['dropped_off']; ?></span>
    </div>
    <div class="lw-col-body" id="col_dropped_off">
      <?php $has = false; foreach($orders as $o): if($o->status != 'dropped_off') continue; $has = true; ?>
      <div class="lw-card dropped_off" data-lid="<?= $o->laundry_order_id; ?>">
        <div class="lw-order-id">#<?= htmlspecialchars($o->sales_code); ?></div>
        <div class="lw-customer"><i class="fa fa-user"></i> <?= htmlspecialchars($o->customer_name ?: 'Walk-in'); ?></div>
        <div class="lw-drop-time"><i class="fa fa-clock-o"></i> <?= $o->drop_off_time; ?></div>
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
        <button class="lw-act-btn info" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'start_washing', this)"><i class="fa fa-tint"></i> Start Washing</button>
        <?php elseif($o->item_summary['can_finish_washing']): ?>
        <button class="lw-act-btn info" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'finish_washing', this)"><i class="fa fa-check"></i> Washing Done</button>
        <?php elseif($o->item_summary['can_start_ironing']): ?>
        <button class="lw-act-btn warn" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'start_ironing', this)"><i class="fa fa-refresh"></i> Start Ironing</button>
        <?php elseif($o->item_summary['can_finish_ironing']): ?>
        <button class="lw-act-btn warn" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'finish_ironing', this)"><i class="fa fa-check"></i> Ironing Done</button>
        <?php elseif($o->item_summary['all_completed']): ?>
        <button class="lw-act-btn success" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'mark_ready', this)"><i class="fa fa-check-circle"></i> Mark Ready</button>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
      <?php if(!$has): ?><div class="lw-empty"><i class="fa fa-inbox"></i>No new drop-offs</div><?php endif; ?>
    </div>
  </div>

  <?php if(!empty($lw_stages['has_washing'])): ?>
  <!-- WASHING -->
  <div class="lw-col">
    <div class="lw-col-head">
      <div class="title"><i class="fa fa-tint" style="color:#0D9488"></i> Washing</div>
      <span class="count blue" id="count_washing"><?= $status_counts['washing']; ?></span>
    </div>
    <div class="lw-col-body" id="col_washing">
      <?php $has = false; foreach($orders as $o): if($o->status != 'washing') continue; $has = true; ?>
      <div class="lw-card washing" data-lid="<?= $o->laundry_order_id; ?>">
        <div class="lw-order-id">#<?= htmlspecialchars($o->sales_code); ?></div>
        <div class="lw-customer"><i class="fa fa-user"></i> <?= htmlspecialchars($o->customer_name ?: 'Walk-in'); ?></div>
        <div class="lw-drop-time"><i class="fa fa-clock-o"></i> <?= $o->drop_off_time; ?></div>
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
        <button class="lw-act-btn info" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'finish_washing', this)"><i class="fa fa-check"></i> Washing Done</button>
        <?php elseif($o->item_summary['can_start_ironing']): ?>
        <button class="lw-act-btn warn" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'start_ironing', this)"><i class="fa fa-refresh"></i> Start Ironing</button>
        <?php elseif($o->item_summary['all_completed']): ?>
        <button class="lw-act-btn success" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'mark_ready', this)"><i class="fa fa-check-circle"></i> Mark Ready</button>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
      <?php if(!$has): ?><div class="lw-empty"><i class="fa fa-tint"></i>Nothing washing</div><?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if(!empty($lw_stages['has_ironing'])): ?>
  <!-- IRONING -->
  <div class="lw-col">
    <div class="lw-col-head">
      <div class="title"><i class="fa fa-refresh" style="color:var(--mp-warning)"></i> Ironing</div>
      <span class="count yellow" id="count_ironing"><?= $status_counts['ironing']; ?></span>
    </div>
    <div class="lw-col-body" id="col_ironing">
      <?php $has = false; foreach($orders as $o): if($o->status != 'ironing') continue; $has = true; ?>
      <div class="lw-card ironing" data-lid="<?= $o->laundry_order_id; ?>">
        <div class="lw-order-id">#<?= htmlspecialchars($o->sales_code); ?></div>
        <div class="lw-customer"><i class="fa fa-user"></i> <?= htmlspecialchars($o->customer_name ?: 'Walk-in'); ?></div>
        <div class="lw-drop-time"><i class="fa fa-clock-o"></i> <?= $o->drop_off_time; ?></div>
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
        <button class="lw-act-btn warn" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'finish_ironing', this)"><i class="fa fa-check"></i> Ironing Done</button>
        <?php elseif($o->item_summary['all_completed']): ?>
        <button class="lw-act-btn success" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'mark_ready', this)"><i class="fa fa-check-circle"></i> Mark Ready</button>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
      <?php if(!$has): ?><div class="lw-empty"><i class="fa fa-refresh"></i>Nothing ironing</div><?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- READY -->
  <div class="lw-col">
    <div class="lw-col-head">
      <div class="title"><i class="fa fa-check-circle" style="color:var(--mp-success)"></i> Ready</div>
      <span class="count green" id="count_ready"><?= $status_counts['ready']; ?></span>
    </div>
    <div class="lw-col-body" id="col_ready">
      <?php $has = false; foreach($orders as $o): if($o->status != 'ready') continue; $has = true; ?>
      <div class="lw-card ready" data-lid="<?= $o->laundry_order_id; ?>">
        <div class="lw-order-id">#<?= htmlspecialchars($o->sales_code); ?></div>
        <div class="lw-customer"><i class="fa fa-user"></i> <?= htmlspecialchars($o->customer_name ?: 'Walk-in'); ?></div>
        <div class="lw-drop-time"><i class="fa fa-clock-o"></i> <?= $o->drop_off_time; ?></div>
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
        <button class="lw-act-btn neutral" onclick="updateLwStatus(<?= $o->laundry_order_id; ?>, 'collected', this)"><i class="fa fa-hand-o-right"></i> Customer Collected</button>
      </div>
      <?php endforeach; ?>
      <?php if(!$has): ?><div class="lw-empty"><i class="fa fa-check-circle"></i>Nothing ready for pickup</div><?php endif; ?>
    </div>
  </div>
</div>

<?php if(!empty($collected)): ?>
<div class="mp-card-form" style="margin-top:20px">
  <div class="mp-card-head"><h3><i class="fa fa-check-square-o"></i> Picked Up by Customer</h3><span class="count gray"><?= count($collected); ?></span></div>
  <div class="mp-card-body">
    <div class="lw-collected-grid">
      <?php foreach(array_slice($collected, 0, 12) as $sv): ?>
      <div class="lw-collected-card">
        <div class="code">#<?= htmlspecialchars($sv->sales_code); ?></div>
        <div class="cust"><?= htmlspecialchars($sv->customer_name ?: 'Walk-in'); ?></div>
        <div class="time"><i class="fa fa-check-square-o"></i> <?= is_valid_date($sv->updated_at) ? date('H:i', strtotime($sv->updated_at)) : 'Just now'; ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

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
        $('#last_refresh').html('<i class="fa fa-refresh"></i> Refreshed: ' + new Date().toLocaleTimeString());
    });
}
setInterval(lwAutoRefresh, 15000);

$('#lw-fullscreen').on('click', function(){
    var $b = $(this);
    $('body').toggleClass('lw-fullscreen');
    $b.toggleClass('active');
    if($('body').hasClass('lw-fullscreen')){
        $b.html('<i class="fa fa-compress"></i> Exit Fullscreen');
    } else {
        $b.html('<i class="fa fa-expand"></i> Fullscreen');
    }
});
</script>
