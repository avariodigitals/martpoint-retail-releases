<?php
$invoice_payment_accounts = [];
$saved_invoice_account_id = 0;
if ($this->db->field_exists('bank_account_number', 'ac_accounts')) {
    $invoice_payment_accounts = $this->db->select('id, account_name, bank_name, bank_account_name, bank_account_number')
        ->where('store_id', get_current_store_id())->where('status', 1)
        ->where('bank_account_number !=', '')->order_by('account_name', 'ASC')->get('ac_accounts')->result();
}
if (isset($sales_id) && $this->db->field_exists('invoice_account_id', 'db_sales')) {
    $saved_invoice_account_id = (int)$this->db->select('invoice_account_id')->where('id', (int)$sales_id)
        ->where('store_id', get_current_store_id())->get('db_sales')->row()->invoice_account_id;
}
$invoice_account_mobile = !empty($invoice_account_mobile);
?>
<div class="form-group invoice-payment-account" style="margin:12px 0 18px;">
  <label for="invoice_account_id">Account to receive payment <small>(optional)</small></label>
  <select id="invoice_account_id" name="invoice_account_id" class="form-control<?= $invoice_account_mobile ? ' mp-select' : ''; ?>" style="width:100%;">
    <option value="">Do not show bank details</option>
    <?php foreach ($invoice_payment_accounts as $account):
      $parts = array_filter([$account->account_name, $account->bank_name, ($account->bank_account_name && $account->bank_account_name !== $account->account_name) ? $account->bank_account_name : null, $account->bank_account_number]);
      $label = implode(' · ', $parts);
    ?>
    <option value="<?= (int)$account->id; ?>" <?= $saved_invoice_account_id === (int)$account->id ? 'selected' : ''; ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
    <?php endforeach; ?>
  </select>
  <?php if (!$invoice_payment_accounts): ?><small>Add a bank account number to an account in Account List to offer it here.</small><?php endif; ?>
</div>
<?php if ($invoice_account_mobile): ?>
<style>
.mp-select { display:none; }
.mp-select-wrap { position:relative; width:100%; }
.mp-select-trigger { width:100%; min-height:44px; padding:10px 12px; border:1px solid #d5dce5; border-radius:8px; background:#fff; text-align:left; display:flex; justify-content:space-between; align-items:center; }
.mp-select-options { display:none; position:static; max-height:180px; overflow-y:auto; border:1px solid #d5dce5; border-radius:0 0 8px 8px; background:#fff; }
.mp-select-wrap.open .mp-select-options { display:block; }
.mp-select-option { padding:11px 12px; border-bottom:1px solid #edf0f4; }
</style>
<script>
(function(){
  var select=document.getElementById('invoice_account_id'); if(!select)return;
  var wrap=document.createElement('div'); wrap.className='mp-select-wrap'; select.parentNode.insertBefore(wrap,select); wrap.appendChild(select);
  var trigger=document.createElement('button'); trigger.type='button'; trigger.className='mp-select-trigger'; wrap.appendChild(trigger);
  var options=document.createElement('div'); options.className='mp-select-options'; wrap.appendChild(options);
  function sync(){ trigger.textContent=select.options[select.selectedIndex].text+'  ▾'; }
  Array.prototype.forEach.call(select.options,function(option){ var row=document.createElement('div'); row.className='mp-select-option'; row.textContent=option.text; row.addEventListener('click',function(){select.value=option.value;sync();wrap.classList.remove('open');select.dispatchEvent(new Event('change',{bubbles:true}));}); options.appendChild(row); });
  trigger.addEventListener('click',function(){wrap.classList.toggle('open');}); sync();
})();
</script>
<?php endif; ?>
<script>
(function(){
  document.addEventListener('change',function(event){
    if(!event.target || event.target.id!=='invoice_account_id')return;
    var paymentAccount=document.getElementById('account_id');
    if(!paymentAccount)return;
    var chosen=event.target.value;
    if(chosen && paymentAccount.querySelector('option[value="'+chosen+'"]')) paymentAccount.value=chosen;
    else paymentAccount.value=paymentAccount.getAttribute('data-default-account') || '';
    if(window.jQuery)window.jQuery(paymentAccount).trigger('change');
  });
})();
</script>
