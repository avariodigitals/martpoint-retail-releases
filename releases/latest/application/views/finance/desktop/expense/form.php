<?php $this->load->view('finance/desktop/_styles'); ?>
<?php
if(!isset($expense_amt)){
    $category_id = $expense_for = $note = $expense_amt = $q_id = $reference_no = $store_id = '';
    $expense_date = show_date(date('d-m-Y'));
    $payment_type = '';
    $account_id = '';
}
$is_edit = isset($q_id) && $q_id !== '';
$btn_name = $is_edit ? 'Update' : 'Save';
$btn_id   = $is_edit ? 'update' : 'save';
?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= $is_edit ? 'Update expense record' : 'Record a new expense'; ?></div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><?= $is_edit ? 'Update Expense' : 'New Expense'; ?></h3>
  </div>
  <div class="mp-card-body">
    <form class="form-horizontal" id="expense-form" onkeypress="return event.keyCode != 13;">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">
      <input type="hidden" name="store_id" id="store_id" value="<?= htmlspecialchars(get_current_store_id()); ?>">
      <?php if($is_edit){ ?>
      <input type="hidden" name="q_id" id="q_id" value="<?= htmlspecialchars($q_id); ?>"/>
      <?php } ?>

      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label for="expense_date"><?= $this->lang->line('expense_date'); ?> <span class="text-danger">*</span></label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker" id="expense_date" name="expense_date" readonly value="<?= htmlspecialchars($expense_date); ?>">
          </div>
          <span id="expense_date_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="category_id"><?= $this->lang->line('category'); ?> <span class="text-danger">*</span></label>
          <select class="form-control select2 mp-form-control" id="category_id" name="category_id" style="width:100%;">
            <?php
              $query1 = "select * from db_expense_category where status=1 and store_id=".get_current_store_id();
              $q1 = $this->db->query($query1);
              if($q1->num_rows() > 0){
                echo '<option value="">— Select —</option>';
                foreach($q1->result() as $res1){
                  $selected = ($category_id == $res1->id) ? 'selected' : '';
                  echo "<option $selected value='".$res1->id."'>".$res1->category_name."</option>";
                }
              } else {
                echo '<option value="">No Records Found</option>';
              }
            ?>
          </select>
          <span id="category_id_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="expense_for"><?= $this->lang->line('expense_for'); ?> <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control" id="expense_for" name="expense_for" value="<?= htmlspecialchars($expense_for); ?>" placeholder="e.g. Office stationery">
          <span id="expense_for_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="payment_type"><?= $this->lang->line('payment_mode'); ?> <span class="text-danger">*</span></label>
          <select class="form-control select2 mp-form-control" id="payment_type" name="payment_type" style="width:100%;">
            <option value="">— Select —</option>
            <?= get_payment_modes_select_list(get_current_store_id(), $payment_type); ?>
          </select>
          <span id="payment_type_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="account_id"><?= $this->lang->line('account'); ?></label>
          <select class="form-control select2 mp-form-control" id="account_id" name="account_id" style="width:100%;">
            <option value="">— None —</option>
            <?= get_accounts_select_list($account_id); ?>
          </select>
          <span id="account_id_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="expense_amt"><?= $this->lang->line('amount'); ?> <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control only_currency" id="expense_amt" name="expense_amt" value="<?= htmlspecialchars($expense_amt); ?>">
          <span id="expense_amt_msg" style="display:none" class="text-danger"></span>
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
        <a href="<?= base_url('expense'); ?>" class="mp-btn-secondary close_btn" title="Go Back">Close</a>
      </div>
    </form>
  </div>
</div>

<script src="<?= htmlspecialchars($theme_link); ?>js/expense.js"></script>
<script type="text/javascript">
  <?php if($is_edit){ ?> $("#store_id").attr('readonly',true); <?php } ?>
</script>
<script>
  $('.mp-nav-item').removeClass('active');
  $('.expense-list-active-li').addClass('active');
  $('.expense-list-active-li').closest('.mp-nav-group').addClass('open');
</script>
