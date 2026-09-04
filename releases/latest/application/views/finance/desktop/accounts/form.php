<?php $this->load->view('finance/desktop/_styles'); ?>
<?php
if(!isset($q_id)){
   $parent_id = $account_name = $note = $q_id = $store_id = '';
   $account_code = get_init_code('accounts');
   $opening_balance = 0;
}
$is_edit = isset($q_id) && $q_id !== '';
$btn_name = $is_edit ? 'Update' : 'Save';
$btn_id   = $is_edit ? 'update' : 'save';
?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= $is_edit ? 'Update account details' : 'Create a new ledger account'; ?></div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><?= $is_edit ? 'Update Account' : 'Add Account'; ?></h3>
  </div>
  <div class="mp-card-body">
    <form class="form-horizontal" id="accounts-form" onkeypress="return event.keyCode != 13;">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">
      <input type="hidden" name="store_id" id="store_id" value="<?= htmlspecialchars(get_current_store_id()); ?>">
      <?php if($is_edit){ ?>
      <input type="hidden" name="q_id" id="q_id" value="<?= htmlspecialchars($q_id); ?>"/>
      <?php } ?>

      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label for="parent_id"><?= $this->lang->line('parent_account'); ?> <span class="text-danger">*</span></label>
          <select class="form-control select2 mp-form-control" id="parent_id" name="parent_id">
            <option value="">— Create Account Head —</option>
            <?= get_accounts_select_list($parent_id); ?>
          </select>
          <span id="parent_id_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="account_code"><?= $this->lang->line('account_code'); ?> <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control" id="account_code" name="account_code" value="<?= htmlspecialchars($account_code); ?>">
          <span id="account_code_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="account_name"><?= $this->lang->line('account_name'); ?> <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control" id="account_name" name="account_name" value="<?= htmlspecialchars($account_name); ?>">
          <span id="account_name_msg" style="display:none" class="text-danger"></span>
        </div>

        <?php if(!$is_edit){ ?>
        <div class="mp-form-group">
          <label for="opening_balance"><?= $this->lang->line('opening_balance'); ?> <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control only_currency" id="opening_balance" name="opening_balance" value="<?= htmlspecialchars(store_number_format($opening_balance,0)); ?>">
          <span id="opening_balance_msg" style="display:none" class="text-danger"></span>
        </div>
        <?php } ?>

        <div class="mp-form-group full">
          <label for="note"><?= $this->lang->line('note'); ?></label>
          <textarea class="mp-form-control" id="note" name="note" rows="3"><?= htmlspecialchars($note); ?></textarea>
          <span id="note_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>

      <div class="mp-form-actions" style="margin-top:24px;">
        <button type="button" id="<?= htmlspecialchars($btn_id); ?>" class="mp-btn-primary" title="Save Data"><?= htmlspecialchars($btn_name); ?></button>
        <a href="<?= base_url('accounts'); ?>" class="mp-btn-secondary close_btn" title="Go Back">Close</a>
      </div>
    </form>
  </div>
</div>

<script src="<?= htmlspecialchars($theme_link); ?>js/accounts/accounts.js"></script>
<script type="text/javascript">
  <?php if($is_edit){ ?> $("#store_id").attr('readonly',true); <?php } ?>
</script>
<script>
  $('.mp-nav-item').removeClass('active');
  <?php if($is_edit): ?>
  $('.accounts_list-active-li').addClass('active');
  <?php else: ?>
  $('.accounts-active-li').addClass('active');
  <?php endif; ?>
  $('.accounts_list-active-li, .accounts-active-li').closest('.mp-nav-group').addClass('open');
</script>
