<?php $this->load->view('customers/desktop/_styles'); ?>
<?php
if(!isset($payment_type)){
   $payment_type=$amount=$note="";
   $customer_id='';
   $payment_date=show_date(date("d-m-Y"));
}
$is_edit = isset($q_id) && $q_id !== '';
$btn_name = $is_edit ? 'Update' : 'Save';
$btn_id   = $is_edit ? 'update' : 'save';
?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= $is_edit ? 'Edit customer advance payment' : 'Record a new customer advance payment'; ?></div>
  </div>
</div>

<?php $this->load->view('modals/modal_customer'); ?>

<div class="mp-card-form box">
  <div class="mp-card-head">
    <h3><?= $is_edit ? 'Edit Advance Payment' : 'Add Advance Payment'; ?></h3>
  </div>
  <div class="mp-card-body">
    <form class="form-horizontal" id="advance-form" onkeypress="return event.keyCode != 13;">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">
      <input type="hidden" name="store_id" id="store_id" value="<?= htmlspecialchars(get_current_store_id()); ?>">
      <?php if($is_edit){ ?>
      <input type="hidden" name="q_id" id="q_id" value="<?= htmlspecialchars($q_id); ?>"/>
      <?php } ?>

      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label for="payment_date"><?= $this->lang->line('date'); ?> <span class="text-danger">*</span></label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker mp-form-control" id="payment_date" name="payment_date" readonly onkeyup="shift_cursor(event,'sales_status')" value="<?= $payment_date; ?>">
          </div>
          <span id="payment_date_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="customer_id"><?= $this->lang->line('customer_name'); ?> <span class="text-danger">*</span></label>
          <div class="input-group" style="width:100%;">
            <select class="form-control mp-form-control" id="customer_id" name="customer_id" style="width:100%;"></select>
            <span class="input-group-addon pointer" data-toggle="modal" data-target="#customer-modal" title="New Customer?"><i class="fa fa-user-plus text-primary fa-lg"></i></span>
          </div>
          <span id="customer_id_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="amount"><?= $this->lang->line('amount'); ?> <span class="text-danger">*</span></label>
          <input type="text" class="form-control mp-form-control" id="amount" name="amount" placeholder="" value="<?php print $amount; ?>" autofocus>
          <span id="amount_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="payment_type"><?= $this->lang->line('payment_type'); ?> <span class="text-danger">*</span></label>
          <select class="form-control select2 mp-form-control" id="payment_type" name="payment_type">
            <?php
              $q1=$this->db->query("select * from db_paymenttypes where status=1 and store_id=".get_current_store_id());
              if($q1->num_rows()>0){
                  echo "<option value=''>-Select-</option>";
                  foreach($q1->result() as $res1){
                      $selected = (!empty($payment_type) && ($res1->payment_type==$payment_type)) ? 'selected' : '';
                      echo "<option $selected value='".$res1->payment_type."'>".$res1->payment_type ."</option>";
                  }
              } else {
                  echo "<option>None</option>";
              }
            ?>
          </select>
          <span id="payment_type_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group full">
          <label for="note"><?= $this->lang->line('note'); ?></label>
          <textarea class="form-control mp-form-control" id="note" name="note" rows="3"><?php print $note; ?></textarea>
          <span id="note_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>

      <div class="mp-form-actions" style="margin-top:24px;">
        <button type="button" id="<?= htmlspecialchars($btn_id); ?>" class="mp-btn-primary" title="Save Data"><?= htmlspecialchars($btn_name); ?></button>
        <a href="<?= base_url('customers_advance'); ?>" class="mp-btn-secondary close_btn" title="Go Back">Close</a>
      </div>
    </form>
  </div>
</div>

<script src="<?= htmlspecialchars($theme_link); ?>js/customers_advance/advance.js"></script>
<script src="<?= htmlspecialchars($theme_link); ?>js/modals.js"></script>
<script type="text/javascript">
  <?php if($is_edit){ ?> $("#store_id").attr('readonly',true); <?php } ?>
</script>
<script src="<?= htmlspecialchars($theme_link); ?>js/ajaxselect/customer_select_ajax.js"></script>
<script>
  function getCustomerSelectionId() {
    return '#customer_id';
  }
  $(document).ready(function () {
     var customer_id = "<?= (!empty($customer_id)) ? $customer_id : '';  ?>";
     autoLoadFirstCustomer(customer_id);
     $('.datepicker').datepicker({ autoclose: true, format: '<?= $VIEW_DATE; ?>', todayHighlight: true });
  });
</script>
<script>
  $('.mp-nav-item').removeClass('active');
  <?php if ($is_edit): ?>
  $('.customers_advance_list-active-li').addClass('active');
  <?php else: ?>
  $('.customers_advance_add-active-li').addClass('active');
  <?php endif; ?>
  $('.customers_advance_list-active-li').closest('.mp-nav-group').addClass('open');
</script>
