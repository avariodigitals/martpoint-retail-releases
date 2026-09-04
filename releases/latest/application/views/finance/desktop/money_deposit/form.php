<?php $this->load->view('finance/desktop/_styles'); ?>
<?php
if(!isset($q_id)){
   $debit_account_id = $credit_account_id = $note = $q_id = $store_id = $reference_no = '';
   $amount = 0;
   $deposit_date = show_date(date('d-m-Y'));
}
$is_edit = isset($q_id) && $q_id !== '';
$btn_name = $is_edit ? 'Update' : 'Save';
$btn_id   = $is_edit ? 'update' : 'save';
?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= $is_edit ? 'Update deposit record' : 'Record a new deposit'; ?></div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><?= $is_edit ? 'Update Deposit' : 'New Deposit'; ?></h3>
  </div>
  <div class="mp-card-body">
    <form class="form-horizontal" id="money_deposit-form" onkeypress="return event.keyCode != 13;">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">
      <input type="hidden" id="store_id" name="store_id" value="<?= htmlspecialchars(get_current_store_id()); ?>">
      <?php if($is_edit){ ?>
      <input type="hidden" name="q_id" id="q_id" value="<?= htmlspecialchars($q_id); ?>"/>
      <?php } ?>

      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label for="deposit_date"><?= $this->lang->line('deposit_date'); ?> <span class="text-danger">*</span></label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker" id="deposit_date" name="deposit_date" readonly value="<?= htmlspecialchars($deposit_date); ?>">
          </div>
          <span id="deposit_date_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="reference_no"><?= $this->lang->line('reference_no'); ?></label>
          <input type="text" class="mp-form-control" id="reference_no" name="reference_no" value="<?= htmlspecialchars($reference_no); ?>">
          <span id="reference_no_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="debit_account_id"><?= $this->lang->line('debit_account'); ?></label>
          <select class="form-control select2 mp-form-control" id="debit_account_id" name="debit_account_id" style="width:100%;">
            <option value="">Select</option>
            <?= get_accounts_select_list($debit_account_id); ?>
          </select>
          <span id="debit_account_id_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="credit_account_id"><?= $this->lang->line('credit_account'); ?> <span class="text-danger">*</span></label>
          <select class="form-control select2 mp-form-control" id="credit_account_id" name="credit_account_id" style="width:100%;">
            <option value="">Select</option>
            <?= get_accounts_select_list($credit_account_id); ?>
          </select>
          <span id="credit_account_id_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="amount"><?= $this->lang->line('amount'); ?> <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control only_currency" id="amount" name="amount" value="<?= htmlspecialchars(store_number_format($amount,0)); ?>">
          <span id="amount_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group full">
          <label for="note"><?= $this->lang->line('note'); ?></label>
          <textarea class="mp-form-control" id="note" name="note" rows="3"><?= htmlspecialchars($note); ?></textarea>
          <span id="note_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>

      <div class="mp-form-actions" style="margin-top:24px;">
        <button type="button" id="<?= htmlspecialchars($btn_id); ?>" class="mp-btn-primary" title="Save Data"><?= htmlspecialchars($btn_name); ?></button>
        <a href="<?= base_url('money_deposit'); ?>" class="mp-btn-secondary close_btn" title="Go Back">Close</a>
      </div>
    </form>
  </div>
</div>

<script src="<?= htmlspecialchars($theme_link); ?>js/accounts/money_deposit.js"></script>
<script type="text/javascript">
  <?php if($is_edit){ ?> $("#store_id").attr('readonly',true); <?php } ?>
</script>
<script>
  $('.mp-nav-item').removeClass('active');
  $('.money_deposit_list-active-li').addClass('active');
  $('.money_deposit_list-active-li').closest('.mp-nav-group').addClass('open');
</script>
