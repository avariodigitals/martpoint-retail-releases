<?php $this->load->view('marketing/desktop/_styles'); ?>
<?php
if(!isset($name)){
    $type=$value=$expire_date=$name=$description=$store_id="";
}
$is_edit = isset($q_id);
$btn_name = $is_edit ? "Update" : "Save";
$btn_id   = $is_edit ? "update" : "save";
?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= $is_edit ? 'Update discount coupon' : 'Add new discount coupon'; ?></div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><?= $is_edit ? 'Update Coupon' : 'Add Coupon'; ?></h3>
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
          <label for="coupon_name"><?= $this->lang->line('couponName'); ?> / Occasion Name <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control" id="coupon_name" name="coupon_name" value="<?= htmlspecialchars($name); ?>" placeholder="e.g. Christmas, Easter, Black Friday" autofocus>
          <p class="mp-form-hint">This name becomes the "occasion" shown when the coupon is applied at POS.</p>
          <span id="coupon_name_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="expire_date"><?= $this->lang->line('expire_date'); ?> <span class="text-danger">*</span></label>
          <div class="input-group date">
            <div class="input-group-addon">
              <i class="fa fa-calendar"></i>
            </div>
            <input type="text" class="form-control datepicker" id="expire_date" name="expire_date" value="<?= htmlspecialchars($expire_date); ?>">
          </div>
          <span id="expire_date_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="coupon_value"><?= $this->lang->line('couponValue'); ?> <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control only_currency" id="coupon_value" name="coupon_value" value="<?= htmlspecialchars($value); ?>">
          <span id="coupon_value_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="coupon_type"><?= $this->lang->line('couponType'); ?> <span class="text-danger">*</span></label>
          <select class="form-control select2" id="coupon_type" name="coupon_type" style="width: 100%;">
            <?= get_coupon_type_select_list($type); ?>
          </select>
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
        <a href="<?= base_url('discount_coupon'); ?>" class="mp-btn-secondary close_btn" title="Go Back">Close</a>
      </div>
    </form>
  </div>
</div>

<script src="<?= htmlspecialchars($theme_link); ?>js/coupons/coupon.js?v=2"></script>
<script type="text/javascript">
  <?php if($is_edit){ ?>
    $("#store_id").attr('readonly',true);
  <?php } ?>
</script>
<!-- Make sidebar menu highlighter/selector -->
<script>$(".createDiscountCoupon-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
