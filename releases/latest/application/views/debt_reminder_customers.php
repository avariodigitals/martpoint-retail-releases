<!DOCTYPE html>
<html>
<head>
<?php include"comman/code_css.php"; ?>
<style>
.mp-card { background:#fff; border-radius:8px; box-shadow:0 1px 4px rgba(0,0,0,0.06); margin-bottom:20px; }
.mp-card-header { padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; }
.mp-card-title { font-size:16px; font-weight:700; color:#0F172A; }
.mp-card-body { padding:20px; }
.mp-table { width:100%; border-collapse:collapse; }
.mp-table th { background:#F8FAFC; color:#64748B; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.4px; padding:12px 16px; text-align:left; border-bottom:1px solid #E2E8F0; }
.mp-table td { padding:12px 16px; border-bottom:1px solid #E2E8F0; font-size:14px; color:#334155; }
.mp-table tr:hover td { background:#F8FAFC; }
.mp-toggle-small input[type="checkbox"] { width:36px; height:20px; -webkit-appearance:none; appearance:none; background:#CBD5E1; border-radius:10px; position:relative; cursor:pointer; outline:none; transition:background .2s; }
.mp-toggle-small input[type="checkbox"]:checked { background:#10B981; }
.mp-toggle-small input[type="checkbox"]::after { content:''; position:absolute; width:16px; height:16px; background:#fff; border-radius:50%; top:2px; left:2px; transition:left .2s; box-shadow:0 1px 3px rgba(0,0,0,0.2); }
.mp-toggle-small input[type="checkbox"]:checked::after { left:18px; }
.mp-select { padding:6px 10px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; color:#0F172A; background:#fff; }
.mp-select:focus { border-color:#3B82F6; outline:none; }
.mp-input-sm { padding:6px 10px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; width:70px; }
.mp-btn-save { padding:6px 14px; border-radius:6px; font-size:13px; font-weight:600; border:none; cursor:pointer; background:#10B981; color:#fff; }
.mp-btn-save:hover { background:#059669; }
.mp-badge { display:inline-block; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:600; }
.mp-badge-green { background:#D1FAE5; color:#065F46; }
.mp-badge-red { background:#FEE2E2; color:#991B1B; }
.mp-empty { text-align:center; padding:40px; color:#94A3B8; }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php include"sidebar.php"; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?=base_url('debt_reminder');?>">Debt Reminder</a></li>
        <li class="active">Customers</li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">

          <div class="mp-card">
            <div class="mp-card-header">
              <div class="mp-card-title"><i class="fa fa-users text-blue"></i> Customers with Outstanding Debt</div>
              <div>
                <small class="text-muted">Store default: <?= ucfirst($store_defaults->frequency); ?> &middot; 
                <?= $store_defaults->enabled ? '<span style="color:#10B981;">Enabled</span>' : '<span style="color:#EF4444;">Disabled</span>'; ?></small>
              </div>
            </div>
            <div class="mp-card-body">
              <?php if(!empty($customers)): ?>
              <div class="table-responsive">
                <table class="mp-table" id="tbl_customers">
                  <thead>
                    <tr>
                      <th>Customer</th>
                      <th>Mobile</th>
                      <th>Amount Due</th>
                      <th>Reminders Sent</th>
                      <th>Last Sent</th>
                      <th>Override</th>
                      <th>Freq</th>
                      <th>Max</th>
                      <th>Email</th>
                      <th>SMS</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($customers as $c): ?>
                    <tr data-cid="<?= $c->customer_id; ?>">
                      <td><strong><?= htmlspecialchars($c->customer_name); ?></strong></td>
                      <td><?= htmlspecialchars($c->mobile); ?></td>
                      <td><?= store_number_format($c->amount_due); ?></td>
                      <td><?= (int)$c->reminder_count; ?></td>
                      <td><?= !empty($c->last_reminder_sent) ? date('M d, Y H:i', strtotime($c->last_reminder_sent)) : '<span class="text-muted">Never</span>'; ?></td>
                      <td>
                        <select class="mp-select override-enabled" data-cid="<?= $c->customer_id; ?>">
                          <option value="" <?= $c->enabled === NULL ? 'selected' : ''; ?>>Use Store Default (<?= $store_defaults->enabled ? 'On' : 'Off'; ?>)</option>
                          <option value="1" <?= $c->enabled === '1' ? 'selected' : ''; ?>>Enabled</option>
                          <option value="0" <?= $c->enabled === '0' ? 'selected' : ''; ?>>Disabled</option>
                        </select>
                      </td>
                      <td>
                        <select class="mp-select override-freq" data-cid="<?= $c->customer_id; ?>">
                          <option value="">Default</option>
                          <option value="daily" <?= $c->frequency == 'daily' ? 'selected' : ''; ?>>Daily</option>
                          <option value="3days" <?= $c->frequency == '3days' ? 'selected' : ''; ?>>3 Days</option>
                          <option value="weekly" <?= $c->frequency == 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                          <option value="biweekly" <?= $c->frequency == 'biweekly' ? 'selected' : ''; ?>>Bi-Weekly</option>
                          <option value="monthly" <?= $c->frequency == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                        </select>
                      </td>
                      <td><input type="number" class="mp-input-sm override-max" value="<?= $c->max_reminders !== NULL ? $c->max_reminders : ''; ?>" placeholder="Default" data-cid="<?= $c->customer_id; ?>"></td>
                      <td><label class="mp-toggle-small"><input type="checkbox" class="override-email" <?= $c->send_email == 1 ? 'checked' : ''; ?> data-cid="<?= $c->customer_id; ?>"></label></td>
                      <td><label class="mp-toggle-small"><input type="checkbox" class="override-sms" <?= $c->send_sms == 1 ? 'checked' : ''; ?> data-cid="<?= $c->customer_id; ?>"></label></td>
                      <td><button class="mp-btn-save btn-save-customer" data-cid="<?= $c->customer_id; ?>"><i class="fa fa-save"></i></button></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <?php else: ?>
                <div class="mp-empty">
                  <i class="fa fa-check-circle" style="font-size:32px; color:#10B981; margin-bottom:10px;"></i>
                  <p>No customers currently have outstanding debt. Great job!</p>
                </div>
              <?php endif; ?>
            </div>
          </div>

        </div>
      </div>
    </section>
  </div>

  <?php $this->load->view('footer'); ?>
  <div class="control-sidebar-bg"></div>
</div>

<?php include"comman/code_js_sound.php"; ?>
<?php include"comman/code_js.php"; ?>
<script src="<?= $theme_link; ?>toastr/toastr.min.js"></script>
<script>
$(function(){
  toastr.options = { positionClass: 'toast-top-right', closeButton: true, progressBar: true };

  $('.btn-save-customer').click(function(){
    var btn = $(this);
    var cid = btn.data('cid');
    var row = $('tr[data-cid="'+cid+'"]');
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

    $.post('<?=base_url("debt_reminder/update_customer");?>', {
      customer_id: cid,
      enabled: row.find('.override-enabled').val(),
      frequency: row.find('.override-freq').val(),
      max_reminders: row.find('.override-max').val(),
      send_email: row.find('.override-email').is(':checked') ? 1 : 0,
      send_sms: row.find('.override-sms').is(':checked') ? 1 : 0,
      <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
    }, function(res){
      btn.prop('disabled', false).html('<i class="fa fa-save"></i>');
      if(res.status === 'success'){
        toastr.success(res.message);
      } else {
        toastr.error(res.message);
      }
    }, 'json');
  });
});
</script>
</body>
</html>
