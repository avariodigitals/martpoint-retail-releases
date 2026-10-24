<?php
/* Paystack Settings — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<?php
  $s = $settings ?? null;
  $secret_key = $s ? $s->secret_key : '';
  $public_key = $s ? $s->public_key : '';
  $enabled = $s ? $s->enabled : 0;
  $test_mode = $s ? ($s->test_mode ?? 1) : 1;
  $webhook_secret = $s ? ($s->webhook_secret ?? '') : '';
  $platform_fee_type = $s ? ($s->platform_fee_type ?? 'none') : 'none';
  $platform_fee_value = $s ? ($s->platform_fee_value ?? 0) : 0;
  $fee_bearer = $s ? ($s->fee_bearer ?? 'platform') : 'platform';
  $payout = $payout_account ?? null;
?>
<?php include"comman/code_flashdata.php"; ?>

<div class="mp-page-head">
  <h1 class="mp-page-title">Paystack Settings</h1>
</div>

<div class="mp-card">
  <div class="mp-card-body">
    <form class="form-horizontal" id="paystack-settings-form">
      <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> <strong>Webhook URL:</strong> <code><?=base_url('paystack/webhook');?></code><br>
        <small>Copy this URL into your Paystack Dashboard > Settings > Webhooks</small>
      </div>

      <div class="form-group">
        <label class="col-sm-3 control-label">Enable Paystack</label>
        <div class="col-sm-6">
<?php if(!empty($central_managed)): ?>
          <div class="alert alert-info" style="margin-bottom:0">Paystack is centrally managed by MartPoint. This install only maintains its payout account.</div>
<?php else: ?>
          <select class="form-control" id="enabled" name="enabled">
            <option value="0" <?=($enabled==0)?'selected':'';?>>Disabled</option>
            <option value="1" <?=($enabled==1)?'selected':'';?>>Enabled</option>
          </select>
<?php endif; ?>
        </div>
      </div>

<?php if(empty($central_managed)): ?>
      <hr>
      <h4 class="text-center"><i class="fa fa-random"></i> Central split policy</h4>
      <div class="alert alert-info">MartPoint uses one central Paystack account. This policy controls what MartPoint retains before the merchant payout subaccount receives the balance.</div>
      <div class="form-group">
        <label class="col-sm-3 control-label">MartPoint fee</label>
        <div class="col-sm-3"><select class="form-control" name="platform_fee_type"><option value="none" <?=($platform_fee_type==='none')?'selected':'';?>>No platform fee</option><option value="percentage" <?=($platform_fee_type==='percentage')?'selected':'';?>>Percentage</option><option value="flat" <?=($platform_fee_type==='flat')?'selected':'';?>>Fixed NGN amount</option></select></div>
        <div class="col-sm-3"><input type="number" min="0" step="0.01" class="form-control" name="platform_fee_value" value="<?=htmlspecialchars($platform_fee_value);?>" placeholder="Fee value"></div>
      </div>
      <div class="form-group"><label class="col-sm-3 control-label">Gateway fee bearer</label><div class="col-sm-6"><select class="form-control" name="fee_bearer"><option value="platform" <?=($fee_bearer==='platform')?'selected':'';?>>MartPoint</option><option value="merchant" <?=($fee_bearer==='merchant')?'selected':'';?>>Merchant</option></select></div></div>
<?php else: ?>
      <div class="alert alert-info">The central split policy and provider fees are managed in Fleet. You do not need to configure them here.</div>
<?php endif; ?>

      <hr>
      <h4 class="text-center"><i class="fa fa-bank"></i> Merchant payout account</h4>
      <div class="alert alert-success">The merchant only needs to choose a bank, enter the account number, verify the name, and turn payouts on. No Paystack API settings are required here.</div>
      <div class="form-group"><label class="col-sm-3 control-label">Bank</label><div class="col-sm-6"><select class="form-control" id="payout_bank_code" name="payout_bank_code"><option value="">Loading banks...</option></select><input type="hidden" id="payout_bank_name" name="payout_bank_name" value="<?=htmlspecialchars($payout->bank_name ?? '');?>"></div></div>
      <div class="form-group"><label class="col-sm-3 control-label">Account number</label><div class="col-sm-6"><input type="text" inputmode="numeric" maxlength="10" class="form-control" id="payout_account_number" name="payout_account_number" value="<?=htmlspecialchars($payout->account_number ?? '');?>"></div></div>
      <div class="form-group"><label class="col-sm-3 control-label">Payout email</label><div class="col-sm-6"><input type="email" class="form-control" id="payout_email" name="payout_email" value="<?=htmlspecialchars($payout->contact_email ?? '');?>"></div></div>
      <div class="form-group"><label class="col-sm-3 control-label">Payout status</label><div class="col-sm-6"><select class="form-control" id="payout_enabled" name="payout_enabled"><option value="0" <?=empty($payout->enabled)?'selected':'';?>>Off</option><option value="1" <?=!empty($payout->enabled)?'selected':'';?>>On</option></select><small class="text-muted">Current: <?=htmlspecialchars($payout->status ?? 'Not connected');?><?=!empty($payout->account_name) ? ' — '.htmlspecialchars($payout->account_name) : '';?></small></div></div>
      <div class="text-center"><button type="button" id="save_payout_account" class="mp-btn-primary">Verify and save payout account</button></div>

<?php if(empty($central_managed)): ?>
      <div class="form-group">
        <label class="col-sm-3 control-label">Mode</label>
        <div class="col-sm-6">
          <select class="form-control" id="test_mode" name="test_mode">
            <option value="1" <?=($test_mode==1)?'selected':'';?>>Test Mode</option>
            <option value="0" <?=($test_mode==0)?'selected':'';?>>Live Mode</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="col-sm-3 control-label">Secret Key <span class="text-danger">*</span></label>
        <div class="col-sm-6">
          <input type="text" class="form-control" id="secret_key" name="secret_key" value="<?=htmlspecialchars($secret_key);?>" placeholder="sk_test_... or sk_live_...">
          <span id="secret_key_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>

      <div class="form-group">
        <label class="col-sm-3 control-label">Public Key <span class="text-danger">*</span></label>
        <div class="col-sm-6">
          <input type="text" class="form-control" id="public_key" name="public_key" value="<?=htmlspecialchars($public_key);?>" placeholder="pk_test_... or pk_live_...">
          <span id="public_key_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>

      <div class="form-group">
        <label class="col-sm-3 control-label">Webhook Secret (Optional)</label>
        <div class="col-sm-6">
          <input type="text" class="form-control" id="webhook_secret" name="webhook_secret" value="<?=htmlspecialchars($webhook_secret);?>" placeholder="For webhook signature verification">
        </div>
      </div>
<?php endif; ?>

      <div class="col-sm-8 col-sm-offset-2 text-center">
<?php if(empty($central_managed)): ?>
        <div class="col-md-3 col-md-offset-3">
          <button type="button" id="save_settings" class="mp-btn-primary">Save Settings</button>
        </div>
<?php endif; ?>
        <div class="col-sm-3">
          <a href="<?=base_url('dashboard');?>"><button type="button" class="col-sm-3 mp-btn-secondary close_btn">Close</button></a>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="<?php echo $theme_link; ?>js/paystack_settings.js"></script>
<script>
$(function(){
  var currentBank = '<?=htmlspecialchars($payout->bank_code ?? '', ENT_QUOTES);?>';
  $.getJSON('<?=base_url('paystack/banks');?>', function(res){
    var sel=$('#payout_bank_code'); sel.empty().append('<option value="">Select bank</option>');
    (res.banks||[]).forEach(function(b){ var code=b.code||b.bank_code; var name=b.name||b.bank_name; if(code){ sel.append($('<option>',{value:code,text:name}).attr('data-name',name)); }});
    sel.val(currentBank);
  });
  $('#payout_bank_code').on('change',function(){ $('#payout_bank_name').val($(this).find('option:selected').data('name')||''); });
  $('#save_payout_account').on('click',function(){
    var f=$('#paystack-settings-form'), btn=$(this); btn.prop('disabled',true);
    $.post('<?=base_url('paystack/save_payout_account');?>', f.serialize(), function(res){
      if(res.status){ toastr.success(res.message||'Payout account saved'); } else { toastr.error(res.message||'Could not save payout account'); }
    },'json').always(function(){btn.prop('disabled',false);});
  });
});
</script>
<script>$(".paystack_settings-active-li").addClass("active");$(".paystack_settings-active-li").closest(".mp-nav-group").addClass("open");</script>
