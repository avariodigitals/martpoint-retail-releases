<!DOCTYPE html>
<html>
<head>
<!-- FORM CSS CODE -->
<?php include"comman/code_css.php"; ?>
<style>
  .schedule-card { border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; margin-bottom: 20px; background: #fff; }
  .schedule-card h4 { margin: 0 0 12px 0; font-weight: 700; }
  .schedule-card .badge-status { font-size: 12px; padding: 4px 10px; border-radius: 20px; }
  .schedule-card .badge-enabled { background: #10B981; color: #fff; }
  .schedule-card .badge-disabled { background: #EF4444; color: #fff; }
  .schedule-card .last-run { color: #64748B; font-size: 12px; }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

 <?php include"sidebar.php"; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?=base_url('email_settings');?>">Email Settings</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">

          <div class="callout callout-info">
            <h4><i class="fa fa-clock-o"></i> Automated Reports</h4>
            <p>Configure daily reports to be sent automatically via email or WhatsApp. Set up a cron job to trigger sending (see bottom of page).</p>
          </div>

          <?php foreach($schedules as $schedule): ?>
          <div class="schedule-card" data-schedule-id="<?=$schedule->id;?>">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
              <div>
                <h4><?=htmlspecialchars($schedule->template_name ?: ucwords(str_replace('_', ' ', $schedule->report_type)));?></h4>
                <span class="badge-status <?=($schedule->status ? 'badge-enabled' : 'badge-disabled');?>"><?=($schedule->status ? 'Enabled' : 'Disabled');?></span>
                <?php if($schedule->last_run_at): ?>
                <span class="last-run">Last run: <?=show_date($schedule->last_run_at) . ' ' . date('H:i', strtotime($schedule->last_run_at));?></span>
                <?php else: ?>
                <span class="last-run">Never run</span>
                <?php endif; ?>
              </div>
              <div>
                <button type="button" class="btn btn-info btn-sm btn-run-now" style="margin-right:6px;"><i class="fa fa-play"></i> Run Now</button>
                <button type="button" class="btn btn-success btn-sm btn-save-schedule"><i class="fa fa-save"></i> Save</button>
              </div>
            </div>

            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Status</label>
                  <select class="form-control schedule-status">
                    <option value="1" <?=($schedule->status==1?'selected':'');?>>Enabled</option>
                    <option value="0" <?=($schedule->status==0?'selected':'');?>>Disabled</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Send Time (24h)</label>
                  <input type="time" class="form-control schedule-time" value="<?=htmlspecialchars($schedule->send_time);?>">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Frequency</label>
                  <select class="form-control schedule-frequency">
                    <option value="daily" <?=($schedule->frequency=='daily'?'selected':'');?>>Daily</option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Email Template</label>
                  <select class="form-control schedule-template">
                    <option value="daily_business_summary" <?=($schedule->email_template_key=='daily_business_summary'?'selected':'');?>>Daily Business Summary</option>
                    <option value="low_stock_alert" <?=($schedule->email_template_key=='low_stock_alert'?'selected':'');?>>Low Stock Alert</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label><i class="fa fa-envelope"></i> Email Recipients</label>
                  <input type="text" class="form-control schedule-emails" value="<?=htmlspecialchars($schedule->email_recipients);?>" placeholder="email1@domain.com, email2@domain.com">
                  <small class="text-muted">Comma-separated email addresses</small>
                </div>
                <div class="form-group">
                  <label>
                    <input type="checkbox" class="schedule-email-enabled" <?=($schedule->email_enabled ? 'checked' : '');?>> Enable Email Delivery
                  </label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label><i class="fa fa-whatsapp"></i> WhatsApp Numbers</label>
                  <input type="text" class="form-control schedule-phones" value="<?=htmlspecialchars($schedule->whatsapp_numbers);?>" placeholder="+254712345678, +254723456789">
                  <small class="text-muted">Comma-separated with country code</small>
                </div>
                <div class="form-group">
                  <label>
                    <input type="checkbox" class="schedule-whatsapp-enabled" <?=($schedule->whatsapp_enabled ? 'checked' : '');?>> Enable WhatsApp Delivery
                  </label>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label>WhatsApp Message Template</label>
              <textarea class="form-control schedule-whatsapp-template" rows="3"><?=htmlspecialchars($schedule->whatsapp_message_template);?></textarea>
              <small class="text-muted">Plain text message. Variables: {store_name}, {report_date}, {total_sales}, {total_profit}, {total_expenses}, {net_position}, {low_stock_items}, {report_link}</small>
            </div>
          </div>
          <?php endforeach; ?>

          <?php if(count($schedules) == 0): ?>
          <div class="alert alert-warning">No schedules found. <a href="<?=base_url('email_settings/seed_schedules');?>" class="alert-link">Seed default schedules</a>.</div>
          <?php endif; ?>

          <div class="callout callout-warning">
            <h4><i class="fa fa-terminal"></i> Cron Setup Required</h4>
            <p>To enable automatic sending, add this cron job to your server (runs every hour at minute 0):</p>
            <pre style="background:#1E293B;color:#E2E8F0;padding:12px;border-radius:8px;">0 * * * * curl -s "<?=base_url('cron/run_scheduled_reports?key=martpoint_cron_2024');?>" > /dev/null 2>&1</pre>
            <p class="text-muted">Or via CLI (if you have CLI access to your server):</p>
            <pre style="background:#1E293B;color:#E2E8F0;padding:12px;border-radius:8px;">0 * * * * cd <?=FCPATH;?> && php index.php cron run_scheduled_reports martpoint_cron_2024 > /dev/null 2>&1</pre>
          </div>

        </div>
      </div>
    </section>
  </div>

 <?php include"footer.php"; ?>
  <div class="control-sidebar-bg"></div>
</div>

<?php include"comman/code_js_sound.php"; ?>
<?php include"comman/code_js.php"; ?>

<script>
function csrfData() {
  return {
    '<?=$this->security->get_csrf_token_name();?>': '<?=$this->security->get_csrf_hash();?>'
  };
}

$('.btn-save-schedule').on('click', function(){
  var card = $(this).closest('.schedule-card');
  var id = card.data('schedule-id');
  var btn = $(this);
  btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

  $.post('<?=base_url("email_settings/schedule_save");?>', $.extend(csrfData(), {
    id: id,
    status: card.find('.schedule-status').val(),
    frequency: card.find('.schedule-frequency').val(),
    send_time: card.find('.schedule-time').val(),
    email_enabled: card.find('.schedule-email-enabled').is(':checked') ? 1 : 0,
    email_recipients: card.find('.schedule-emails').val(),
    email_template_key: card.find('.schedule-template').val(),
    whatsapp_enabled: card.find('.schedule-whatsapp-enabled').is(':checked') ? 1 : 0,
    whatsapp_numbers: card.find('.schedule-phones').val(),
    whatsapp_message_template: card.find('.schedule-whatsapp-template').val()
  }), function(res){
    btn.attr('disabled', false).html('<i class="fa fa-save"></i> Save');
    if(res.status === 'success'){
      toastr['success'](res.message);
      // Refresh to update badge
      setTimeout(function(){ location.reload(); }, 800);
    } else {
      toastr['error'](res.message || 'Failed to save.');
    }
  }, 'json').fail(function(){
    btn.attr('disabled', false).html('<i class="fa fa-save"></i> Save');
    toastr['error']('Request failed. Check connection.');
  });
});

$('.btn-run-now').on('click', function(){
  var card = $(this).closest('.schedule-card');
  var id = card.data('schedule-id');
  var btn = $(this);
  btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Running...');

  $.post('<?=base_url("email_settings/schedule_run_now");?>', $.extend(csrfData(), {
    id: id
  }), function(res){
    btn.attr('disabled', false).html('<i class="fa fa-play"></i> Run Now');
    if(res.status === 'success'){
      toastr['success'](res.message);
      setTimeout(function(){ location.reload(); }, 1200);
    } else if(res.status === 'info'){
      toastr['info'](res.message);
    } else if(res.status === 'partial'){
      toastr['warning'](res.message);
    } else {
      toastr['error'](res.message || 'Failed to run.');
    }
  }, 'json').fail(function(){
    btn.attr('disabled', false).html('<i class="fa fa-play"></i> Run Now');
    toastr['error']('Request failed. Check connection.');
  });
});
</script>
<script>$(".smtp-active-li").addClass("active");</script>
</body>
</html>
