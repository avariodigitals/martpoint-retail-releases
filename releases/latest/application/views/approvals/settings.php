<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
<style>
.approval-card { border: 1px solid #ddd; border-radius: 6px; margin-bottom: 15px; background: #fff; }
.approval-card .card-header { background: #f4f4f4; padding: 10px 15px; border-bottom: 1px solid #ddd; border-radius: 6px 6px 0 0; font-weight: 600; }
.approval-card .card-body { padding: 15px; }
.approval-row { margin-bottom: 12px; padding: 6px 0; }
.approval-row select.form-control { height: 36px; padding: 6px 10px; font-size: 14px; }
.approval-row label { margin-bottom: 0; line-height: 36px; }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar');?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?=base_url('settings');?>">Settings</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <?php $this->load->view('comman/code_flashdata'); ?>
        </div>
      </div>
      <form id="approvalSettingsForm">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

        <!-- Global Settings -->
        <div class="box box-primary">
          <div class="box-header with-border"><h3 class="box-title">Global Approval Settings</h3></div>
          <div class="box-body">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Approval System</label>
                  <select name="approval_system_enabled" class="form-control">
                    <option value="1" <?= $settings->approval_system_enabled ? 'selected' : '' ?>>Enabled</option>
                    <option value="0" <?= !$settings->approval_system_enabled ? 'selected' : '' ?>>Disabled</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Business Control Mode</label>
                  <select name="business_control_mode" class="form-control">
                    <option value="simple" <?= $settings->business_control_mode=='simple' ? 'selected' : '' ?>>Simple Mode (Approvals Off by Default)</option>
                    <option value="controlled" <?= $settings->business_control_mode=='controlled' ? 'selected' : '' ?>>Controlled Mode (Approvals Configurable)</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Allow Self Approval</label>
                  <select name="allow_self_approval" class="form-control">
                    <option value="0" <?= !$settings->allow_self_approval ? 'selected' : '' ?>>No (Default)</option>
                    <option value="1" <?= $settings->allow_self_approval ? 'selected' : '' ?>>Yes</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Thresholds -->
        <div class="box box-info">
          <div class="box-header with-border"><h3 class="box-title">Thresholds</h3></div>
          <div class="box-body">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Discount Limit (%)</label>
                  <input type="number" step="0.01" name="discount_limit" class="form-control" value="<?= $settings->discount_limit ?? 0 ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Expense Threshold</label>
                  <input type="number" step="0.01" name="expense_threshold" class="form-control" value="<?= $settings->expense_threshold ?? 0 ?>">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Approval Types -->
        <?php
        $groups = [
          'Sales Approvals' => ['discount','price_override','void_sale','sale_return','edit_completed_sale','hold_delete'],
          'Customer & Credit Approvals' => ['credit_sale','credit_limit_override','customer_balance_adjustment'],
          'Inventory Approvals' => ['negative_stock_sale','stock_adjustment','inventory_transfer','cost_price_change','selling_price_change'],
          'Finance Approvals' => ['expense','cash_variance','reopen_shift'],
          'Online Store Approvals' => ['online_refund','cancel_online_order','manual_payment_confirmation'],
          'Purchase Approvals' => ['purchase','purchase_price_override'],
        ];
        $methodOptions = $approval_methods;
        ?>

        <?php foreach($groups as $groupName => $keys): ?>
        <div class="approval-card">
          <div class="card-header"><?= $groupName ?></div>
          <div class="card-body">
            <?php foreach($keys as $key):
              $enabledField = $key.'_approval_enabled';
              $methodField = $key.'_approval_method';
              $enabled = $settings->$enabledField ?? 0;
              $method = $settings->$methodField ?? 'none';
              // Support both single ID and comma-separated IDs
              $selectedIds = [];
              if($method !== 'none' && $method !== ''){
                $selectedIds = array_map('trim', explode(',', $method));
              }
              // Also handle legacy single ID that might match a user
              if(!in_array('none', $selectedIds) && count($selectedIds) === 1 && ctype_digit((string)$selectedIds[0])){
                $selectedIds = [(string)$selectedIds[0]];
              }
            ?>
            <div class="row approval-row">
              <div class="col-md-4"><label><?= $approval_types[$key] ?></label></div>
              <div class="col-md-2">
                <select name="<?= $key ?>_enabled" class="form-control">
                  <option value="0" <?= !$enabled ? 'selected' : '' ?>>Disabled</option>
                  <option value="1" <?= $enabled ? 'selected' : '' ?>>Enabled</option>
                </select>
              </div>
              <div class="col-md-6">
                <div style="margin-bottom:4px;">
                  <label class="checkbox-inline" style="font-weight:600;">
                    <input type="checkbox" class="select-all-approvers" data-target="<?= $key ?>_method"> Select All
                  </label>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:10px;">
                  <?php foreach($approver_users as $u):
                    if($u['id'] === 'none') continue;
                    $isChecked = in_array((string)$u['id'], $selectedIds) || in_array($u['id'], $selectedIds);
                  ?>
                  <label class="checkbox-inline" style="margin-right:10px;white-space:nowrap;">
                    <input type="checkbox" name="<?= $key ?>_method[]" value="<?= $u['id'] ?>" <?= $isChecked ? 'checked' : '' ?>> <?= $u['name'] ?><?= $u['role'] ? ' ('.$u['role'].')' : '' ?>
                  </label>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
        <script>
        $(document).on('change', '.select-all-approvers', function(){
          var target = $(this).data('target');
          var checked = $(this).is(':checked');
          $('input[name="'+target+'[]"]').prop('checked', checked);
        });
        </script>

        <div class="text-center" style="margin-bottom:30px;">
          <button type="button" id="saveBtn" class="btn btn-lg btn-success"><i class="fa fa-save"></i> Save Settings</button>
          <a href="<?= base_url('dashboard') ?>" class="btn btn-lg btn-warning">Close</a>
        </div>
      </form>
    </section>
  </div>
  <?php $this->load->view('footer'); ?>
</div>
<?php $this->load->view('comman/code_js'); ?>
<script>
$('#saveBtn').click(function(){
  var fd = new FormData(document.getElementById('approvalSettingsForm'));
  $.ajax({
    url: '<?= base_url('approvals/save_settings'); ?>',
    type: 'POST',
    data: fd,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function(res){
      if(res.status === 'success'){
        toastr.success('Settings saved successfully');
      } else {
        toastr.error(res.message || 'Failed to save');
      }
    },
    error: function(){ toastr.error('Request failed'); }
  });
});
</script>
</body>
</html>
