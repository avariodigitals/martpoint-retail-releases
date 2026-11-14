<?php
/* Subscription plan selection — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<link rel="stylesheet" href="<?php echo $theme_link; ?>css/subscription.css">

<div class="mp-page-head"><h1 class="mp-page-title"><?= $page_title; ?></h1></div>

<div class="pay_now_modal">
</div>
<div class="pay_return_due_modal">
</div>
<?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
<input type="hidden" id='base_url' value="<?=$base_url;?>">

<?php include"comman/code_flashdata.php"; ?>

<div class="col-xs-12">
  <div class="">
    <div class="planContainer">
      <!--  -->
              

                <div class="plan">
                  <div class="titleContainer">
                    <div class="title"><?= $package_list[1]['package_name']; ?></div>
                  </div>
                  <div class="infoContainer">

                    <?php if($package_list[1]['monthly_price']>0){ ?>
                    <div class="price">
                      <p><?= $CI->currency($package_list[1]['monthly_price']) ?> </p><span>/<?= $this->lang->line('month'); ?></span>
                    </div>
                  <?php } else{ ?>
                    <div class="price">
                      <p><?= $CI->currency($package_list[1]['annual_price']) ?> </p><span>/<?= $this->lang->line('annual'); ?></span>
                    </div>
                  <?php } ?>

                    <div class="p desc"><em><?= $CI->currency($package_list[1]['description']) ?></em></div>
                    <ul class="features">
                      <li><strong><?= ($package_list[1]['max_warehouses']) ?></strong> Branches <small class="text-muted">(via Warehouses)</small></li>
                      <li><strong><?= ($package_list[1]['max_users']) ?></strong> <?= $this->lang->line('users'); ?></li>
                      <li><strong><?= ($package_list[1]['max_items']) ?></strong> <?= $this->lang->line('items'); ?></li>
                      <li><strong><?= ($package_list[1]['max_invoices']) ?></strong> <?= $this->lang->line('invoices'); ?></li>
                    </ul>
                    <?php $current_branches = warehouse_count(); $max_branches = intval($package_list[1]['max_warehouses']); ?>
                    <?php if($current_branches > $max_branches && $max_branches > 0): ?>
                    <div class="alert alert-warning" style="margin-top:10px;padding:8px;font-size:12px;">
                      <i class="fa fa-exclamation-triangle"></i> You have <?= $current_branches; ?> branches but your plan allows <?= $max_branches; ?>. Please upgrade.
                    </div>
                    <?php elseif($max_branches > 0): ?>
                    <div class="alert alert-info" style="margin-top:10px;padding:8px;font-size:12px;">
                      <i class="fa fa-info-circle"></i> Current usage: <?= $current_branches; ?> / <?= $max_branches; ?> branches
                    </div>
                    <?php endif; ?>
                
                    <?php if($package_list[1]['monthly_price']==0 && $package_list[1]['annual_price']==0) {?>
                        <a class="selectPlan pay_btn"><?= $this->lang->line('subscribe'); ?></a>
                    <?php } else{ ?>

                      <hr>
                    <span class="text-uppercase "><?= $this->lang->line('select_payment_gateway'); ?></span>

                    <div class="price">
                      <label class="pointer text-blue"><input type="radio" value="instamojo" name="gateway" checked> <?= $this->lang->line('instamojo'); ?></label>
                    </div>
                    
                    <div class="price">
                      <label class="pointer text-blue"><input type="radio" name="gateway" value="paypal" > <?= $this->lang->line('paypal'); ?></label>
                    </div> 
                    <?php 
                    $bankDetails = get_super_admin_bank_details();
                    if($bankDetails->status==1){ ?>
                        <div class="price">
                        <label class="pointer text-blue"><input type="radio" name="gateway" value="bank_transfer" > <?= $this->lang->line('bankTransfer'); ?></label>
                      </div>
                    <?php } ?>

                    <a class="selectPlan pay_btn"><?= $this->lang->line('pay'); ?></a>


                    <?php } ?>
                    <a href="<?=base_url('subscription_license')?>">Back</a>
                  </div>
                </div>

     

      
      <!--  -->
    </div>
  </div>
</div>

<?= form_close();?>

<!-- bootstrap datepicker -->
<script src="<?php echo $theme_link; ?>plugins/datepicker/bootstrap-datepicker.js"></script>
<script type="text/javascript">
  //Date picker
    $('.datepicker').datepicker({
      autoclose: true,
    format: 'dd-mm-yyyy',
     todayHighlight: true
    });
</script>
<script>
  $(".pay_btn").on("click",function(){
    if(typeof swal === 'undefined'){
      if(!confirm("Are you sure ?")) return;
      processPayment();
    } else {
      swal({
        title: "Are you sure?",
        text: "You are about to proceed with payment.",
        icon: "warning",
        buttons: true,
        dangerMode: true
      }).then(function(willPay){
        if(willPay) processPayment();
      });
    }
  });
  function processPayment(){
    var base_url = $("#base_url").val();
    if($("input[name='gateway']:checked").val()=='paypal'){
      location.href = base_url+"online_payments/buy_package/paypal/<?=$package_list[1]['id']?>";
    }
    else if($("input[name='gateway']:checked").val()=='instamojo'){
      location.href = base_url+"online_payments/buy_package/instamojo/<?=$package_list[1]['id']?>";
    }
    else if($("input[name='gateway']:checked").val()=='bank_transfer'){
      location.href = base_url+"online_payments/bank_transfer/<?=$package_list[1]['id']?>";
    }
    else{
      location.href = base_url+"online_payments/free_package/<?=$package_list[1]['id']?>";
    }
  } 
</script>


<!-- Make sidebar menu hughlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
