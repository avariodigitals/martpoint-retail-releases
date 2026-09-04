<?php $this->load->view('marketing/desktop/_styles'); ?>
<?php
if(!isset($coupon_id)){
    $coupon_id=$code=$description='';
}
$customer_id = $customer_id ?? '';
$is_edit = isset($q_id);
$btn_name = $is_edit ? "Update" : "Save";
$btn_id   = $is_edit ? "update" : "save";
?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Generate a customer-specific coupon code</div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3>Customer Coupon</h3>
  </div>
  <div class="mp-card-body">
    <form class="form-horizontal" id="coupon-form" onkeypress="return event.keyCode != 13;">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">

      <!-- Store Code -->
      <input type="hidden" name="store_id" id="store_id" value="<?= htmlspecialchars(get_current_store_id()); ?>">
      <!-- Store Code end -->

      <input type="hidden" name="command" id="command" value="<?= $is_edit ? 'update' : 'save'; ?>">

      <?php if($is_edit){ ?>
      <input type="hidden" name="q_id" id="q_id" value="<?= htmlspecialchars($q_id); ?>"/>
      <?php } ?>

      <div class="mp-form-grid">

        <div class="mp-form-group">
          <label for="customer_id"><?= $this->lang->line('customer_name'); ?> <span class="text-danger">*</span></label>
          <select class="form-control select2" id="customer_id" name="customer_id" style="width: 100%;">
          </select>
          <span id="customer_id_msg" style="display:none" class="text-danger"></span>
          <?php if(!empty($customer_id)){ ?>
            <a class="pull-right" href="<?= base_url('customer_coupon/generate'); ?>">Change</a>
          <?php } ?>
        </div>

        <div class="mp-form-group">
          <label for="coupon_id">Occasion / <?= $this->lang->line('couponName'); ?> <span class="text-danger">*</span></label>
          <select class="form-control select2" id="coupon_id" name="coupon_id" style="width: 100%;">
            <?= get_discount_coupon_select_list($coupon_id); ?>
          </select>
          <p class="mp-form-hint" id="occasion_name_display" style="display:none;"></p>
          <span id="coupon_id_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="code"><?= $this->lang->line('couponCode'); ?> <span class="text-danger">*</span></label>
          <div class="input-group input-group-sm" style="width:100%;">
            <input type="text" class="form-control" id="code" name="code" placeholder="" value="<?= htmlspecialchars($code); ?>">
            <span class="input-group-btn">
              <button data-toggle="tooltip" title="Generate Code" type="button" class="btn btn-info btn-flat generate"><i class="fa fa-fw fa-refresh"></i></button>
            </span>
          </div>
          <span id="code_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="expire_date"><?= $this->lang->line('expire_date'); ?></label>
          <input type="text" class="mp-form-control" id="expire_date" name="expire_date" value="" readonly>
          <span id="expire_date_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="coupon_value"><?= $this->lang->line('couponValue'); ?></label>
          <input type="text" class="mp-form-control only_currency" id="coupon_value" name="coupon_value" value="" readonly>
          <span id="coupon_value_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="coupon_type"><?= $this->lang->line('couponType'); ?></label>
          <input type="text" class="mp-form-control" id="coupon_type" name="coupon_type" value="" readonly>
          <span id="coupon_type_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group full">
          <label for="description"><?= $this->lang->line('description'); ?></label>
          <textarea class="mp-form-control" id="description" name="description" rows="3"><?= htmlspecialchars($description); ?></textarea>
          <span id="description_msg" style="display:none" class="text-danger"></span>
        </div>

      </div>

      <div class="mp-form-actions" style="margin-top:24px;">
        <button type="button" id="<?= htmlspecialchars($btn_id); ?>" class="mp-btn-primary" title="Save Data"><?= htmlspecialchars($btn_name); ?></button>
        <a href="<?= base_url('customer_coupon'); ?>" class="mp-btn-secondary close_btn" title="Go Back">Close</a>
      </div>
    </form>
  </div>
</div>

<script src="<?= htmlspecialchars($theme_link); ?>js/coupons/generate.js?v=3"></script>
<script src="<?= htmlspecialchars($theme_link); ?>js/ajaxselect/customer_select_ajax.js"></script>
<script>
  //Customer Selection Box Search
  function getCustomerSelectionId() {
    return '#customer_id';
  }

  $(document).ready(function () {
    var customer_id = "<?= !empty($customer_id) ? htmlspecialchars($customer_id) : ''; ?>";
    autoLoadFirstCustomer(customer_id);
  });
  //Customer Selection Box Search - END
</script>
<script type="text/javascript">
  <?php if($is_edit){ ?>
    $("#store_id").attr('readonly',true);
  <?php } ?>
  <?php if(!empty($customer_id)){ ?>
    $("#customer_id").attr('readonly',true);
  <?php } ?>
</script>
<!-- Make sidebar menu highlighter/selector -->
<script>$(".createCoupon-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
