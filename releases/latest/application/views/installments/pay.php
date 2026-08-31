<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css');?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

<?php $this->load->view('sidebar');?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>Record Installment Payment <small><?= $plan->plan_code; ?></small></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo $base_url; ?>installments">Installments</a></li>
        <li><a href="<?php echo $base_url; ?>installments/view/<?= $plan->id; ?>">Plan #<?= $plan->plan_code; ?></a></li>
        <li class="active">Record Payment</li>
      </ol>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-6 col-md-offset-3">
          <div class="box box-success">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-money"></i> Payment Details</h3></div>
            <form id="installment-payment-form">
              <div class="box-body">
                <div class="form-group">
                  <label>Customer</label>
                  <input type="text" class="form-control" value="<?= $plan->customer_name; ?>" readonly>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Total Plan Amount</label>
                      <input type="text" class="form-control text-right" value="<?= number_format($plan->total_amount, 2); ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Total Paid So Far</label>
                      <input type="text" class="form-control text-right" value="<?= number_format($plan->total_paid, 2); ?>" readonly>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label>Balance</label>
                  <input type="text" class="form-control text-right text-bold text-red" value="<?= number_format($plan->total_amount - $plan->total_paid, 2); ?>" readonly>
                </div>
                <hr>
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
                <input type="hidden" name="payment_id" value="<?= $selected_payment ? $selected_payment->id : ''; ?>">
                <div class="form-group">
                  <label>Installment #</label>
                  <select class="form-control" name="payment_id_select" onchange="window.location='<?= base_url('installments/pay/'.$plan->id); ?>?payment_id='+this.value">
                    <?php foreach($plan->payments as $p){ ?>
                      <option value="<?= $p->id; ?>" <?= ($selected_payment && $selected_payment->id == $p->id) ? 'selected' : ''; ?>>
                        #<?= $p->installment_number; ?> — Due <?= show_date($p->due_date); ?> — <?= number_format($p->amount_due, 2); ?> (<?= ucfirst($p->status); ?>)
                      </option>
                    <?php } ?>
                  </select>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Amount Due</label>
                      <input type="text" class="form-control text-right" value="<?= $selected_payment ? number_format($selected_payment->amount_due, 2) : '0.00'; ?>" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Amount to Pay <span class="text-danger">*</span></label>
                      <input type="number" step="0.01" class="form-control text-right" name="amount_paid" value="<?= $selected_payment ? number_format($selected_payment->amount_due, 2, '.', '') : '0.00'; ?>" required>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Payment Type</label>
                      <select class="form-control" name="payment_type">
                        <?= get_payment_modes_select_list(get_current_store_id()); ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Account</label>
                      <select class="form-control" name="account_id">
                        <option value="">-Select-</option>
                        <?= get_accounts_select_list(); ?>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label>Note</label>
                  <textarea class="form-control" name="payment_note" rows="2"></textarea>
                </div>
              </div>
              <div class="box-footer">
                <a href="<?= base_url('installments/view/'.$plan->id); ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back</a>
                <button type="button" class="btn btn-success pull-right" onclick="savePayment()">
                  <i class="fa fa-save"></i> Save Payment
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
<?php $this->load->view('footer'); ?>
</div>
<?php $this->load->view('comman/code_js');?>
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
  });
}
</script>
<!-- Make sidebar menu highlighter/selector -->
<script>$(".installments-active-li").addClass("active");</script>
</body>
</html>
