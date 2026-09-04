<?php $this->load->view('finance/desktop/_styles'); ?>
<?php $balance = floatval($plan->total_amount) - floatval($plan->total_paid); ?>
<?php
$payment_id = $this->input->get('payment_id');
$selected_payment = null;
foreach($plan->payments as $p){
  if(!$payment_id || $p->id == $payment_id){
    if(!$selected_payment && $p->status != 'paid'){
      $selected_payment = $p;
    }
  }
}
if(!$selected_payment) $selected_payment = isset($plan->payments[0]) ? $plan->payments[0] : null;
?>

<div class="mp-page-head">
  <div>
    <h2>Record Installment Payment</h2>
    <div class="mp-page-sub"><?= htmlspecialchars($plan->customer_name); ?> — Plan <?= htmlspecialchars($plan->plan_code); ?></div>
  </div>
  <a class="mp-qa-btn" href="<?= base_url('installments/view/'.$plan->id); ?>"><i class="fa fa-arrow-left"></i> Back to Plan</a>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card sales">
    <div class="mp-kpi-icon"><i class="fa fa-money"></i></div>
    <div class="mp-kpi-label">Total Amount</div>
    <div class="mp-kpi-value"><?= store_number_format($plan->total_amount); ?></div>
  </div>
  <div class="mp-kpi-card profit">
    <div class="mp-kpi-icon"><i class="fa fa-check"></i></div>
    <div class="mp-kpi-label">Total Paid</div>
    <div class="mp-kpi-value"><?= store_number_format($plan->total_paid); ?></div>
  </div>
  <div class="mp-kpi-card <?= $balance > 0 ? 'debt' : 'profit'; ?>">
    <div class="mp-kpi-icon"><i class="fa fa-balance-scale"></i></div>
    <div class="mp-kpi-label">Balance</div>
    <div class="mp-kpi-value"><?= store_number_format($balance); ?></div>
  </div>
</div>

<div style="max-width:960px;margin:0 auto 24px;">
<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><i class="fa fa-money"></i> Payment Details</h3>
  </div>
  <div class="mp-card-body">
    <form id="installment-payment-form">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label>Customer</label>
          <input type="text" class="form-control mp-form-control" value="<?= htmlspecialchars($plan->customer_name); ?>" readonly>
        </div>

        <div class="mp-form-group">
          <label>Total Plan Amount</label>
          <input type="text" class="form-control mp-form-control text-right" value="<?= store_number_format($plan->total_amount); ?>" readonly>
        </div>

        <div class="mp-form-group">
          <label>Total Paid So Far</label>
          <input type="text" class="form-control mp-form-control text-right" value="<?= store_number_format($plan->total_paid); ?>" readonly>
        </div>

        <div class="mp-form-group">
          <label>Balance</label>
          <input type="text" class="form-control mp-form-control text-right text-danger" style="font-weight:700;" value="<?= store_number_format($balance); ?>" readonly>
        </div>

        <div class="mp-form-group full">
          <label for="payment_id_select">Installment #</label>
          <select class="form-control select2 mp-form-control" style="width:100%;" id="payment_id_select" onchange="window.location='<?= base_url('installments/pay/'.$plan->id); ?>?payment_id='+this.value">
            <?php foreach($plan->payments as $p){ ?>
              <option value="<?= $p->id; ?>" <?= ($selected_payment && $selected_payment->id == $p->id) ? 'selected' : ''; ?>>
                #<?= $p->installment_number; ?> — Due <?= show_date($p->due_date); ?> — <?= store_number_format($p->amount_due); ?> (<?= ucfirst($p->status); ?>)
              </option>
            <?php } ?>
          </select>
        </div>

        <input type="hidden" name="payment_id" id="payment_id" value="<?= $selected_payment ? $selected_payment->id : ''; ?>">

        <div class="mp-form-group">
          <label>Amount Due</label>
          <input type="text" class="form-control mp-form-control text-right" value="<?= $selected_payment ? store_number_format($selected_payment->amount_due) : '0.00'; ?>" readonly>
        </div>

        <div class="mp-form-group">
          <label for="amount_paid">Amount to Pay <span class="text-danger">*</span></label>
          <input type="number" step="0.01" class="form-control mp-form-control text-right" name="amount_paid" id="amount_paid" value="<?= $selected_payment ? number_format($selected_payment->amount_due, 2, '.', '') : '0.00'; ?>" required>
        </div>

        <div class="mp-form-group">
          <label for="payment_type">Payment Type</label>
          <select class="form-control select2 mp-form-control" style="width:100%;" name="payment_type" id="payment_type">
            <?= get_payment_modes_select_list(get_current_store_id()); ?>
          </select>
        </div>

        <div class="mp-form-group">
          <label for="account_id">Account</label>
          <select class="form-control select2 mp-form-control" style="width:100%;" name="account_id" id="account_id">
            <option value="">-Select-</option>
            <?= get_accounts_select_list(); ?>
          </select>
        </div>

        <div class="mp-form-group full">
          <label for="payment_note">Note</label>
          <textarea class="form-control mp-form-control" name="payment_note" id="payment_note" rows="2"></textarea>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="mp-form-actions" style="justify-content:flex-end;">
  <a href="<?= base_url('installments/view/'.$plan->id); ?>" class="mp-btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
  <button type="button" class="mp-btn-primary" onclick="savePayment()"><i class="fa fa-save"></i> Save Payment</button>
</div>
</div>

<script>
function savePayment(){
  var form = $('#installment-payment-form').serialize();
  $.post("<?= base_url('installments/save_payment'); ?>", form, function(res){
    if(res.indexOf('success') !== -1 || res.indexOf('Payment') !== -1){
      toastr.success(res);
      setTimeout(function(){ window.location = "<?= base_url('installments/view/'.$plan->id); ?>"; }, 800);
    } else {
      toastr.error(res);
    }
  }).fail(function(){
    toastr.error('Payment failed. Please try again.', 'Error');
  });
}
</script>
<script>
$(document).ready(function(){
  $(".select2").select2();
});
</script>
<script>$(".installments-active-li").addClass("active");</script>
