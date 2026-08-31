<!DOCTYPE html>
<html>
<head>
<?php include"comman/code_css.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php include"sidebar.php"; ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      <?=$page_title;?>
      <small>Add/Update Package</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?php echo $base_url; ?>service_packages">Service Packages</a></li>
      <li class="active"><?=$page_title;?></li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <!-- ********** ALERT MESSAGE START******* -->
    <?php $this->load->view('comman/code_flashdata');?>
    <!-- ********** ALERT MESSAGE END******* -->
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title"><?=$page_title;?></h3>
          </div>
          <form class="form-horizontal" id="package-form" method="post" enctype="multipart/form-data">
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
          <input type="hidden" name="command" value="<?php echo $command; ?>">
          <input type="hidden" name="q_id" value="<?php echo isset($q_id) ? $q_id : ''; ?>">

          <div class="box-body">

            <!-- Package Code & Name -->
            <div class="form-group">
              <label class="col-sm-3 control-label">Package Code <span class="text-danger">*</span></label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="package_code" value="<?php echo isset($package_code) ? $package_code : ''; ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">Package Name <span class="text-danger">*</span></label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="package_name" value="<?php echo isset($package_name) ? $package_name : ''; ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">Description</label>
              <div class="col-sm-9">
                <textarea class="form-control" name="description" rows="3"><?php echo isset($description) ? $description : ''; ?></textarea>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">Package Image</label>
              <div class="col-sm-9">
                <input type="file" class="form-control" name="package_image" accept="image/*">
                <?php if(!empty($package_image) && file_exists($package_image)) { ?>
                  <br><img src="<?php echo base_url($package_image); ?>" style="max-height:80px; border-radius:6px;">
                <?php } ?>
              </div>
            </div>

            <hr>
            <h4><i class="fa fa-money"></i> Pricing</h4>

            <div class="form-group">
              <label class="col-sm-3 control-label">Pricing Model</label>
              <div class="col-sm-9">
                <select class="form-control" name="pricing_model" id="pricing_model">
                  <option value="fixed" <?php echo (isset($pricing_model) && $pricing_model == 'fixed') ? 'selected' : ''; ?>>Fixed Package Price</option>
                  <option value="calculated" <?php echo (isset($pricing_model) && $pricing_model == 'calculated') ? 'selected' : ''; ?>>Calculated (Sum of Items + Discount)</option>
                </select>
              </div>
            </div>

            <div class="form-group" id="fixed_price_group">
              <label class="col-sm-3 control-label">Package Price <span class="text-danger">*</span></label>
              <div class="col-sm-9">
                <input type="number" step="0.01" class="form-control" name="package_price" id="package_price" value="<?php echo isset($package_price) ? $package_price : '0'; ?>">
              </div>
            </div>

            <div class="form-group" id="calc_discount_group" style="display:none;">
              <label class="col-sm-3 control-label">Discount</label>
              <div class="col-sm-4">
                <select class="form-control" name="discount_type" id="discount_type">
                  <option value="" <?php echo (empty($discount_type)) ? 'selected' : ''; ?>>None</option>
                  <option value="fixed" <?php echo (isset($discount_type) && $discount_type == 'fixed') ? 'selected' : ''; ?>>Fixed Amount</option>
                  <option value="percentage" <?php echo (isset($discount_type) && $discount_type == 'percentage') ? 'selected' : ''; ?>>Percentage</option>
                </select>
              </div>
              <div class="col-sm-5">
                <input type="number" step="0.01" class="form-control" name="discount" id="discount" value="<?php echo isset($discount) ? $discount : '0'; ?>" placeholder="Discount value">
              </div>
            </div>

            <div class="form-group">
              <div class="col-sm-9 col-sm-offset-3">
                <div class="well well-sm" id="price_preview" style="margin-bottom:0;">
                  <strong>Individual Total:</strong> <span id="individual_total">0.00</span><br>
                  <strong>Package Price:</strong> <span id="final_price">0.00</span><br>
                  <strong style="color:green;">Customer Saves:</strong> <span id="savings">0.00</span>
                </div>
              </div>
            </div>

            <hr>
            <h4><i class="fa fa-refresh"></i> Redemption Settings</h4>

            <div class="form-group">
              <label class="col-sm-3 control-label">Redemption Type</label>
              <div class="col-sm-9">
                <select class="form-control" name="redemption_type">
                  <option value="single" <?php echo (isset($redemption_type) && $redemption_type == 'single') ? 'selected' : ''; ?>>Single Session (all at once)</option>
                  <option value="multi" <?php echo (isset($redemption_type) && $redemption_type == 'multi') ? 'selected' : ''; ?>>Multi-Visit (over time)</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label">Expiry</label>
              <div class="col-sm-3">
                <select class="form-control" name="expiry_type" id="expiry_type">
                  <option value="none" <?php echo (isset($expiry_type) && $expiry_type == 'none') ? 'selected' : ''; ?>>No Expiry</option>
                  <option value="days" <?php echo (isset($expiry_type) && $expiry_type == 'days') ? 'selected' : ''; ?>>After X Days</option>
                  <option value="date" <?php echo (isset($expiry_type) && $expiry_type == 'date') ? 'selected' : ''; ?>>Fixed Date</option>
                </select>
              </div>
              <div class="col-sm-3" id="expiry_days_group">
                <input type="number" class="form-control" name="expiry_days" value="<?php echo isset($expiry_days) ? $expiry_days : '30'; ?>" placeholder="Days">
              </div>
              <div class="col-sm-3" id="expiry_date_group" style="display:none;">
                <input type="text" class="form-control datepicker" name="expiry_date" value="<?php echo isset($expiry_date) ? $expiry_date : ''; ?>" placeholder="YYYY-MM-DD">
              </div>
            </div>

            <hr>
            <h4><i class="fa fa-list"></i> Package Contents</h4>
            <p class="text-muted">Add the services or products included in this package.</p>

            <div class="form-group">
              <div class="col-sm-12">
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-search"></i></span>
                  <input type="text" class="form-control" id="item_search" placeholder="Search services or products to add...">
                  <span class="input-group-btn">
                    <button type="button" class="btn btn-default" id="btn_add_service"><i class="fa fa-plus"></i> Add Service</button>
                    <button type="button" class="btn btn-default" id="btn_add_product"><i class="fa fa-plus"></i> Add Product</button>
                  </span>
                </div>
              </div>
            </div>

            <div id="package_items_container">
              <!-- Package items will be added here -->
            </div>

          </div>

          <div class="box-footer">
            <div class="col-sm-9 col-sm-offset-3">
              <button type="button" id="btn_save" class="btn btn-primary btn-lg"><i class="fa fa-save"></i> Save Package</button>
              <a href="<?php echo base_url('service_packages'); ?>" class="btn btn-default btn-lg">Cancel</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<script>
$(document).ready(function() {
  $('.datepicker').datepicker({ format: 'yyyy-mm-dd', autoclose: true });

  // Pricing model toggle
  $('#pricing_model').change(function() {
    if ($(this).val() == 'fixed') {
      $('#fixed_price_group').show();
      $('#calc_discount_group').hide();
    } else {
      $('#fixed_price_group').hide();
      $('#calc_discount_group').show();
    }
    recalcPrice();
  });
  $('#pricing_model').trigger('change');

  // Expiry type toggle
  $('#expiry_type').change(function() {
    var t = $(this).val();
    if (t == 'days') { $('#expiry_days_group').show(); $('#expiry_date_group').hide(); }
    else if (t == 'date') { $('#expiry_days_group').hide(); $('#expiry_date_group').show(); }
    else { $('#expiry_days_group').hide(); $('#expiry_date_group').hide(); }
  });
  $('#expiry_type').trigger('change');

  // Item search autocomplete
  var availableItems = [];
  function loadItems() {
    $.getJSON("<?php echo base_url('service_packages/ajax_get_services'); ?>", function(services) {
      $.getJSON("<?php echo base_url('service_packages/ajax_get_products'); ?>", function(products) {
        availableItems = [];
        $.each(services, function(i, s) { availableItems.push({ label: s.item_name + ' [Service]', value: s.id, type: 'service', price: s.sales_price }); });
        $.each(products, function(i, p) { availableItems.push({ label: p.item_name + ' [Product]', value: p.id, type: 'product', price: p.sales_price }); });
      });
    });
  }
  loadItems();

  $('#item_search').autocomplete({
    source: function(req, resp) {
      var results = $.grep(availableItems, function(item) {
        return item.label.toLowerCase().indexOf(req.term.toLowerCase()) >= 0;
      });
      resp(results.slice(0, 10));
    },
    select: function(e, ui) {
      addPackageItem(ui.item.value, ui.item.type, ui.item.label, ui.item.price);
      $('#item_search').val('');
      return false;
    }
  });

  var itemCount = 0;
  function addPackageItem(itemId, itemType, itemName, itemPrice) {
    itemCount++;
    var html = '<div class="row package-item-row" style="margin-bottom:8px; padding:8px; background:#f9f9f9; border-radius:6px;" data-price="' + itemPrice + '">' +
      '<input type="hidden" name="package_item_id[]" value="' + itemId + '">' +
      '<input type="hidden" name="package_item_type[]" value="' + itemType + '">' +
      '<div class="col-sm-1 text-center"><span class="badge">' + itemCount + '</span></div>' +
      '<div class="col-sm-5"><strong>' + itemName + '</strong><br><small class="text-muted">' + itemType.toUpperCase() + ' @ ' + itemPrice + '</small></div>' +
      '<div class="col-sm-3"><input type="number" step="0.01" name="package_item_qty[]" class="form-control input-sm item-qty" value="1" min="0.01" onchange="recalcPrice()"></div>' +
      '<div class="col-sm-2 text-right"><span class="item-subtotal text-muted">' + itemPrice + '</span></div>' +
      '<div class="col-sm-1"><button type="button" class="btn btn-xs btn-danger" onclick="removeItem(this)"><i class="fa fa-trash"></i></button></div>' +
      '</div>';
    $('#package_items_container').append(html);
    recalcPrice();
  }

  window.removeItem = function(btn) {
    $(btn).closest('.package-item-row').remove();
    recalcPrice();
  };

  window.recalcPrice = function() {
    var total = 0;
    $('.package-item-row').each(function() {
      var price = parseFloat($(this).data('price')) || 0;
      var qty = parseFloat($(this).find('.item-qty').val()) || 0;
      var sub = price * qty;
      $(this).find('.item-subtotal').text(sub.toFixed(2));
      total += sub;
    });

    $('#individual_total').text(total.toFixed(2));

    var finalPrice = total;
    if ($('#pricing_model').val() == 'fixed') {
      finalPrice = parseFloat($('#package_price').val()) || 0;
    } else {
      var discount = parseFloat($('#discount').val()) || 0;
      var dtype = $('#discount_type').val();
      if (dtype == 'fixed') finalPrice = total - discount;
      else if (dtype == 'percentage') finalPrice = total * (1 - discount / 100);
      if (finalPrice < 0) finalPrice = 0;
    }
    $('#final_price').text(finalPrice.toFixed(2));
    $('#savings').text((total - finalPrice).toFixed(2));
  };

  $('#package_price, #discount, #discount_type').on('input change', recalcPrice);

  // Submit
  $('#btn_save').click(function() {
    if ($('#package_items_container .package-item-row').length === 0) {
      toastr['warning']('Please add at least one item to the package.');
      return;
    }
    var formData = new FormData($('#package-form')[0]);
    $.ajax({
      url: "<?php echo base_url('service_packages/newpackage'); ?>",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function(res) {
        if (res == 'success') {
          toastr['success']('Package saved successfully.');
          setTimeout(function() { window.location = "<?php echo base_url('service_packages'); ?>"; }, 800);
        } else {
          toastr['error'](res);
        }
      },
      error: function() {
        toastr['error']('Something went wrong. Please try again.');
      }
    });
  });
});
</script>
</div>
<!-- /.content-wrapper -->
<?php include"footer.php"; ?>
<div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
<!-- SOUND CODE -->
<?php include"comman/code_js_sound.php"; ?>
<!-- TABLES CODE -->
<?php include"comman/code_js.php"; ?>
</body>
</html>
