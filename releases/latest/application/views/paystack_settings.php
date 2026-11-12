<?php
/* MartPoint Pay settings — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<?php
  $s = $settings ?? null;
  $secret_key = $s ? $s->secret_key : '';
  $public_key = $s ? $s->public_key : '';
  $enabled = $s ? $s->enabled : 0;
  $test_mode = $s ? ($s->test_mode ?? 1) : 1;
  $webhook_secret = $s ? ($s->webhook_secret ?? '') : '';
  $platform_fee_percent = $s ? ($s->platform_fee_percent ?? (($s->platform_fee_type ?? 'none') === 'percentage' ? $s->platform_fee_value : 0)) : 0;
  $platform_fee_fixed = $s ? ($s->platform_fee_fixed ?? (($s->platform_fee_type ?? 'none') === 'flat' ? $s->platform_fee_value : 0)) : 0;
  $fee_bearer = $s ? ($s->fee_bearer ?? 'platform') : 'platform';
  $payout = $payout_account ?? null;
  $central_paystack_configured = $s && !empty($s->secret_key);
  $percent_label = rtrim(rtrim(number_format((float)$platform_fee_percent, 2, '.', ''), '0'), '.');
  $platform_fee_label = ((float)$platform_fee_percent > 0 || (float)$platform_fee_fixed > 0)
    ? ($percent_label !== '' ? $percent_label . '% + ' : '') . '₦' . number_format((float)$platform_fee_fixed, 2) . ' per payment'
    : 'No MartPoint platform fee is configured';
  $example_platform_fee = round(10000 * (float)$platform_fee_percent / 100 + (float)$platform_fee_fixed, 2);
  $has_current_fee_acceptance = !empty($paystack_terms_accepted);
?>
<?php include"comman/code_flashdata.php"; ?>

<div class="mp-page-head">
  <h1 class="mp-page-title">MartPoint Pay</h1>
</div>

<div class="mp-card">
  <div class="mp-card-body">
    <form class="form-horizontal mp-settings-form mp-paystack-settings" id="paystack-settings-form">
      <input type="hidden" id="base_url" value="<?=htmlspecialchars(base_url(), ENT_QUOTES, 'UTF-8');?>">
      <input type="hidden" name="<?=htmlspecialchars($this->security->get_csrf_token_name(), ENT_QUOTES, 'UTF-8');?>" value="<?=htmlspecialchars($this->security->get_csrf_hash(), ENT_QUOTES, 'UTF-8');?>">
<?php if(empty($central_managed)): ?>
      <div class="alert alert-info mp-settings-note">
        <i class="fa fa-info-circle"></i> <strong>Webhook URL:</strong> <code><?=base_url('paystack/webhook');?></code><br>
        <small>Add this URL to the payment provider’s webhook settings.</small>
      </div>
<?php endif; ?>

<?php if(!empty($central_managed)): ?>
      <div class="alert <?= !empty($enabled) ? 'alert-success' : 'alert-warning'; ?> mp-settings-status">
        <i class="fa <?= !empty($enabled) ? 'fa-check-circle' : 'fa-clock-o'; ?>" aria-hidden="true"></i>
        <div><strong><?= !empty($enabled) ? 'MartPoint Pay is active' : 'MartPoint Pay activation is pending'; ?></strong><br><?= !empty($enabled) ? 'Manage this store’s settlement account below.' : 'Ask your Central administrator to activate MartPoint Pay before saving this payout account.'; ?></div>
      </div>
<?php else: ?>
      <div class="form-group">
        <label class="col-sm-3 control-label">Enable MartPoint Pay</label>
        <div class="col-sm-6">
          <select class="form-control" id="enabled" name="enabled">
            <option value="0" <?=($enabled==0)?'selected':'';?>>Disabled</option>
            <option value="1" <?=($enabled==1)?'selected':'';?>>Enabled</option>
          </select>
        </div>
      </div>
<?php endif; ?>

<?php if(empty($central_managed)): ?>
      <section class="mp-settings-section">
      <h4 class="mp-settings-section-title"><i class="fa fa-random"></i> Payment fee policy</h4>
      <div class="alert alert-info mp-settings-note">This platform charge is deducted from the fleet settlement before the remaining balance reaches its payout account. It is separate from the payment processing fee.</div>
      <div class="form-group">
        <label class="col-sm-3 control-label">Platform charge</label>
        <div class="mp-settings-inline-fields">
          <div><span class="mp-fee-component-label">Percentage of payment (%)</span><input type="number" min="0" max="100" step="0.01" class="form-control" name="platform_fee_percent" value="<?=htmlspecialchars($platform_fee_percent);?>" placeholder="e.g. 2.5"></div>
          <div><span class="mp-fee-component-label">Fixed amount (₦)</span><input type="number" min="0" step="0.01" class="form-control" name="platform_fee_fixed" value="<?=htmlspecialchars($platform_fee_fixed);?>" placeholder="e.g. 100"></div>
        </div>
        <div class="col-sm-6 col-sm-offset-3"><small class="help-block" id="paystack-fee-example"></small></div>
      </div>
      <div class="form-group"><label class="col-sm-3 control-label">Gateway fee bearer</label><div class="col-sm-6"><select class="form-control" name="fee_bearer"><option value="platform" <?=($fee_bearer==='platform')?'selected':'';?>>MartPoint</option><option value="merchant" <?=($fee_bearer==='merchant')?'selected':'';?>>Merchant</option></select></div></div>
      </section>
<?php else: ?>
      <div class="alert alert-info mp-settings-note mp-managed-note">
        <strong>Fleet payment fees are centrally controlled.</strong><br>
<?php if($central_paystack_configured): ?>
        Platform charge: <strong><?=htmlspecialchars($platform_fee_label, ENT_QUOTES, 'UTF-8');?></strong>. Payment processing fee paid by <strong><?=($fee_bearer === 'merchant') ? 'the fleet' : 'MartPoint';?></strong>.
<?php else: ?>
        Fee details will appear here after MartPoint Pay is activated centrally.
<?php endif; ?>
      </div>
<?php endif; ?>

<?php if(empty($central_managed) || !empty($allow_payout_account_edit)): ?>
      <section class="mp-settings-section">
      <h4 class="mp-settings-section-title"><i class="fa fa-bank"></i> Merchant payout account</h4>
      <p class="mp-form-hint" style="margin:0 0 20px;">Choose the settlement bank and enter the account details for this store.</p>
      <div class="mp-payout-grid">
        <div class="mp-payout-field full">
          <label for="payout_bank_code">Bank</label>
          <select class="form-control" id="payout_bank_code" name="payout_bank_code"><option value="">Loading banks...</option></select>
          <input type="hidden" id="payout_bank_name" name="payout_bank_name" value="<?=htmlspecialchars($payout->bank_name ?? '');?>">
          <small class="mp-payout-help">Type a bank name to search.</small>
        </div>
        <div class="mp-payout-field">
          <label for="payout_account_number">Account number</label>
          <input type="text" inputmode="numeric" maxlength="10" class="form-control" id="payout_account_number" name="payout_account_number" value="<?=htmlspecialchars($payout->account_number ?? '');?>" placeholder="10-digit account number">
        </div>
        <div class="mp-payout-field">
          <label for="payout_account_name">Account name</label>
          <input type="text" class="form-control" id="payout_account_name" value="<?=htmlspecialchars($payout->account_name ?? '', ENT_QUOTES, 'UTF-8');?>" placeholder="Verified account name" readonly>
          <small id="payout_account_lookup_status" class="mp-payout-help">Enter an account number to verify the name.</small>
        </div>
        <div class="mp-payout-field full">
          <label for="payout_email">Payout email</label>
          <input type="email" class="form-control" id="payout_email" name="payout_email" value="<?=htmlspecialchars($payout->contact_email ?? '');?>" placeholder="name@example.com">
          <small class="mp-payout-help">Used for settlement and payout notifications.</small>
        </div>
      </div>
      <?php if(!empty($payout->status)): ?><p class="mp-payout-help" style="margin-top:16px!important;">Account status: <strong><?=htmlspecialchars($payout->status, ENT_QUOTES, 'UTF-8');?></strong></p><?php endif; ?>
      <div class="mp-payout-actions"><button type="button" id="save_payout_account" class="mp-btn-primary" <?=(!empty($central_managed) && empty($enabled)) ? 'disabled title="Available after MartPoint Pay is activated"' : '';?>><?php if(!empty($central_managed) && empty($enabled)): ?>Available after activation<?php elseif(!$has_current_fee_acceptance): ?>Review fees &amp; activate MartPoint Pay<?php elseif(empty($payout->enabled)): ?>Verify account &amp; activate<?php else: ?>Verify account and save<?php endif; ?></button></div>
      </section>
<?php elseif(empty($payout_edit_policy_available)): ?>
      <div class="alert alert-warning mp-settings-note"><strong>Payout account details are hidden.</strong> Central’s edit policy could not be confirmed, so account viewing and changes are paused. Reload after the Central connection is available.</div>
<?php else: ?>
      <div class="alert alert-info mp-settings-note"><strong>Payout account details are managed by Central.</strong> The bank account and account number are hidden on this fleet. Ask your Central administrator to enable editing if these details need correction.</div>
<?php endif; ?>

<?php if(empty($central_managed)): ?>
      <section class="mp-settings-section">
      <h4 class="mp-settings-section-title"><i class="fa fa-key"></i> Payment gateway connection</h4>
      <div class="alert alert-info mp-settings-note">
        <strong>Shared Paystack webhook:</strong> <code><?=htmlspecialchars($paystack_shared_webhook_url ?? 'https://martpoint.com.ng/api/payments/webhooks/paystack', ENT_QUOTES, 'UTF-8');?></code><br>
        <small>Use this one URL in the Paystack dashboard. It routes successful subaccount payments to the correct fleet. Checkout callbacks are supplied by each payment flow, so you do not need a separate global callback URL.</small>
      </div>
      <div class="alert alert-warning mp-settings-note">
        Save and confirm the API keys here first. Bank lookup runs afterward with the saved key; a bank-list failure does not prevent saving these credentials.
      </div>
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
      <div class="mp-settings-actions" style="margin-top:18px!important;">
        <button type="button" id="save_settings" class="mp-btn-primary">Save Paystack credentials</button>
        <span class="mp-form-hint" style="align-self:center;">Credentials are checked against the selected Test or Live mode.</span>
      </div>
      </section>
<?php endif; ?>

      <div class="mp-settings-actions">
<?php if(empty($central_managed)): ?>
<?php endif; ?>
        <a href="<?=base_url('dashboard');?>" class="mp-btn-secondary close_btn">Close</a>
      </div>
    </form>
  </div>
</div>

<?php if(!empty($central_managed)): ?>
<div class="modal fade" id="martpoint-pay-terms-modal" tabindex="-1" role="dialog" aria-labelledby="martpoint-pay-terms-title" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border:0;border-radius:16px;overflow:hidden;">
      <div class="modal-header" style="background:#f3f7ff;border-bottom:1px solid #e5eaf3;padding:20px 24px;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="martpoint-pay-terms-title"><i class="fa fa-credit-card"></i> MartPoint Pay fees</h4>
      </div>
      <div class="modal-body" style="padding:24px;">
        <p>When you accept an online payment, a platform charge of <strong><?=htmlspecialchars($platform_fee_label, ENT_QUOTES, 'UTF-8');?></strong> is deducted from your settlement.</p>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin:16px 0;">
          <div style="font-weight:600;margin-bottom:8px;">Example on a ₦10,000 payment</div>
          <div>Platform charge: <strong>₦<?=number_format($example_platform_fee, 2);?></strong></div>
          <div class="text-muted" style="font-size:13px;margin-top:6px;">The payment processing fee is paid by <?=($fee_bearer === 'merchant') ? 'your fleet' : 'MartPoint';?> under the current Central settings.</div>
          <div class="text-muted" style="font-size:13px;margin-top:4px;">Your customer pays the listed checkout amount; no separate payment surcharge is added.</div>
        </div>
        <label style="display:flex;align-items:flex-start;gap:10px;font-weight:400;line-height:1.5;">
          <input type="checkbox" id="martpoint-pay-terms-accept" value="1" style="margin-top:4px;">
          <span>I have read and accept the MartPoint Pay platform charge and the payment processing fee arrangement shown above.</span>
        </label>
      </div>
      <div class="modal-footer" style="padding:16px 24px;">
        <button type="button" class="btn btn-default" data-dismiss="modal">Review later</button>
        <button type="button" class="mp-btn-primary" id="accept-martpoint-pay-terms" disabled>Accept and activate</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="<?php echo $theme_link; ?>js/paystack_settings.js?v=4.0.9.268"></script>
<script>
$(function(){
<?php if(empty($central_managed) || !empty($allow_payout_account_edit)): ?>
  var currentBank = '<?=htmlspecialchars($payout->bank_code ?? '', ENT_QUOTES);?>';
  var lookupTimer = null, lookupRequest = null;
  function updatePlatformFeeSummary(){
    var percent = parseFloat($('[name="platform_fee_percent"]').val()) || 0;
    var fixed = parseFloat($('[name="platform_fee_fixed"]').val()) || 0;
    var parts = [];
    if(percent > 0) parts.push(percent.toLocaleString(undefined, {maximumFractionDigits: 2}) + '%');
    if(fixed > 0) parts.push('₦' + fixed.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#paystack-fee-example').text(parts.length
      ? 'Current fields calculate: ' + parts.join(' + ') + ' per successful online payment.'
      : 'No platform charge is currently configured.');
  }
  $('[name="platform_fee_percent"], [name="platform_fee_fixed"]').on('input change', updatePlatformFeeSummary);
  updatePlatformFeeSummary();
  function resolvePayoutAccount(){
    var bank=$('#payout_bank_code').val(), number=($('#payout_account_number').val()||'').replace(/\D/g,'');
    $('#payout_account_name').val('');
    if(number.length!==10 || !bank){ $('#payout_account_lookup_status').text('Choose a bank and enter a 10-digit account number.').removeClass('text-danger text-success').addClass('text-muted'); return; }
    if(lookupRequest && lookupRequest.readyState!==4) lookupRequest.abort();
    $('#payout_account_lookup_status').text('Verifying account…').removeClass('text-danger text-success').addClass('text-muted');
    lookupRequest=$.post('<?=base_url('paystack/resolve_payout_account');?>',{
      account_number:number, bank_code:bank,
      '<?= $this->security->get_csrf_token_name(); ?>':$('input[name="<?= $this->security->get_csrf_token_name(); ?>"]').val()
    },function(res){
      if(res.csrf_hash) $('input[name="<?= $this->security->get_csrf_token_name(); ?>"]').val(res.csrf_hash);
      if(res.status && res.account_name){
        $('#payout_account_number').val(res.account_number||number);
        $('#payout_account_name').val(res.account_name);
        $('#payout_account_lookup_status').text('Account name verified.').removeClass('text-danger text-muted').addClass('text-success');
      } else {
        $('#payout_account_lookup_status').text(res.message||'The account could not be verified. Check the bank and account number.').removeClass('text-success text-muted').addClass('text-danger');
      }
    },'json').fail(function(xhr,status){if(status!=='abort')$('#payout_account_lookup_status').text('Could not verify this account. Check the details and try again.').removeClass('text-success text-muted').addClass('text-danger');});
  }
  $.getJSON('<?=base_url('paystack/banks');?>', function(res){
    var sel=$('#payout_bank_code'); sel.empty().append('<option value="">Select bank</option>');
    (res.banks||[]).forEach(function(b){ var code=b.code||b.bank_code; var name=b.name||b.bank_name; if(code){ sel.append($('<option>',{value:code,text:name}).attr('data-name',name)); }});
    sel.val(currentBank).select2({width:'100%', placeholder:'Search or select a bank', allowClear:true});
    if(currentBank && $('#payout_account_number').val()) resolvePayoutAccount();
  }).fail(function(){ $('#payout_bank_code').html('<option value="">Banks unavailable</option>'); $('#payout_account_lookup_status').text('Could not load the Paystack bank list. Save or check the API keys first; if already saved, confirm Live mode and account access.').addClass('text-danger'); });
  $('#payout_bank_code').on('change',function(){ $('#payout_bank_name').val($(this).find('option:selected').data('name')||''); clearTimeout(lookupTimer); lookupTimer=setTimeout(resolvePayoutAccount,250); });
  $('#payout_account_number').on('input',function(){ $('#payout_account_name').val(''); clearTimeout(lookupTimer); lookupTimer=setTimeout(resolvePayoutAccount,450); });
  function savePayoutAccount(acceptedTerms){
    var f=$('#paystack-settings-form'), btn=$('#save_payout_account'); btn.prop('disabled',true);
    var payload=f.serializeArray();
    if(acceptedTerms) payload.push({name:'terms_accepted',value:'1'});
    $.post('<?=base_url('paystack/save_payout_account');?>', payload, function(res){
      if(res.csrf_hash) $('input[name="<?= $this->security->get_csrf_token_name(); ?>"]').val(res.csrf_hash);
      if(res.status){ toastr.success(res.message||'Payout account saved'); } else { toastr.error(res.message||'Could not save payout account'); }
      if(res.status) window.location.reload();
    },'json').always(function(){btn.prop('disabled',false);});
  }
  $('#save_payout_account').on('click',function(){
    <?php if(!empty($central_managed) && !$has_current_fee_acceptance): ?>
    $('#martpoint-pay-terms-accept').prop('checked',false);
    $('#accept-martpoint-pay-terms').prop('disabled',true);
    $('#martpoint-pay-terms-modal').modal('show');
    <?php else: ?>
    savePayoutAccount(false);
    <?php endif; ?>
  });
  $('#martpoint-pay-terms-accept').on('change',function(){ $('#accept-martpoint-pay-terms').prop('disabled',!this.checked); });
  $('#accept-martpoint-pay-terms').on('click',function(){
    if(!$('#martpoint-pay-terms-accept').is(':checked')) return;
    $(this).prop('disabled',true).text('Saving…');
    $('#martpoint-pay-terms-modal').modal('hide');
    savePayoutAccount(true);
  });
<?php endif; ?>
});
</script>
<script>$(".paystack_settings-active-li").addClass("active");$(".paystack_settings-active-li").closest(".mp-nav-group").addClass("open");</script>
