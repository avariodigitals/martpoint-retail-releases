<?php $this->load->view('finance/desktop/_styles'); ?>
<?php
if(!isset($q_id)){
   $debit_account_id = $credit_account_id = $note = $q_id = $store_id = $reference_no = '';
   $transfer_code = get_init_code('money_transfer');
   $amount = 0;
   $transfer_date = show_date(date('d-m-Y'));
}
$is_edit = isset($q_id) && $q_id !== '';
$btn_name = $is_edit ? 'Update' : 'Save';
$btn_id   = $is_edit ? 'update' : 'save';
?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= $is_edit ? 'Update transfer record' : 'Record a new money transfer'; ?></div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><?= $is_edit ? 'Update Transfer' : 'New Transfer'; ?></h3>
  </div>
  <div class="mp-card-body">
    <form class="form-horizontal" id="money_transfer-form" onkeypress="return event.keyCode != 13;">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">
      <input type="hidden" id="store_id" name="store_id" value="<?= htmlspecialchars(get_current_store_id()); ?>">
      <?php if($is_edit){ ?>
      <input type="hidden" name="q_id" id="q_id" value="<?= htmlspecialchars($q_id); ?>"/>
      <?php } ?>

      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label for="transfer_date"><?= $this->lang->line('transfer_date'); ?> <span class="text-danger">*</span></label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker" id="transfer_date" name="transfer_date" readonly value="<?= htmlspecialchars($transfer_date); ?>">
          </div>
          <span id="transfer_date_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="transfer_code"><?= $this->lang->line('transfer_code'); ?> <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control" id="transfer_code" name="transfer_code" value="<?= htmlspecialchars($transfer_code); ?>">
          <span id="transfer_code_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="debit_account_id"><?= $this->lang->line('debit_account'); ?> <span class="text-danger">*</span></label>
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

        <div class="mp-form-group">
          <label for="reference_no"><?= $this->lang->line('reference_no'); ?></label>
          <input type="text" class="mp-form-control" id="reference_no" name="reference_no" value="<?= htmlspecialchars($reference_no); ?>">
          <span id="reference_no_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group full">
          <label for="note"><?= $this->lang->line('note'); ?></label>
          <textarea class="mp-form-control" id="note" name="note" rows="3"><?= htmlspecialchars($note); ?></textarea>
          <span id="note_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>

      <div class="mp-form-actions" style="margin-top:24px;">
        <button type="button" id="<?= htmlspecialchars($btn_id); ?>" class="mp-btn-primary" title="Save Data"><?= htmlspecialchars($btn_name); ?></button>
        <a href="<?= base_url('money_transfer'); ?>" class="mp-btn-secondary close_btn" title="Go Back">Close</a>
      </div>
    </form>
  </div>
</div>

<script src="<?= htmlspecialchars($theme_link); ?>js/accounts/money_transfer.js"></script>
<script type="text/javascript">
  <?php if($is_edit){ ?> $("#store_id").attr('readonly',true); <?php } ?>
</script>
<script>
  $('.mp-nav-item').removeClass('active');
  $('.money_transfer_list-active-li').addClass('active');
  $('.money_transfer_list-active-li').closest('.mp-nav-group').addClass('open');
</script>
