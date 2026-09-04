<?php
$this->load->view('admin/desktop/_styles');

$CI =& get_instance();
$store_name = $this->session->userdata('store_name') ?: 'MartPoint';
$is_update = ($command ?? '') === 'update';
?>

<style>
.mp-card-form { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 16px; box-shadow: var(--mp-shadow-sm); overflow: hidden; margin-bottom: 24px; }
.mp-card-form .mp-card-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px 14px; border-bottom: 1px solid var(--mp-border); }
.mp-card-form .mp-card-head h3 { font-size: 15px; font-weight: 700; margin: 0; color: var(--mp-text); }
.mp-card-form .mp-card-body { padding: 20px; }
.mp-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px 24px; }
.mp-form-grid .mp-form-group.full { grid-column: 1 / -1; }
.mp-form-group { display: flex; flex-direction: column; gap: 6px; }
.mp-form-group > label { font-size: 13px; font-weight: 600; color: var(--mp-ink); }
.mp-form-group > label .text-danger { color: var(--mp-danger); }
.mp-form-hint { font-size: 12px; color: var(--mp-muted); margin: 0; }
.mp-form-control { width: 100%; padding: 11px 14px; border: 1px solid var(--mp-border); border-radius: 10px; background: var(--mp-surface); color: var(--mp-ink); font-size: 14px; font-weight: 500; font-family: inherit; transition: all .15s ease; }
.mp-form-control:focus { outline: none; border-color: var(--mp-primary); box-shadow: 0 0 0 3px rgba(0,87,255,.1); }
.mp-form-control[readonly] { background: var(--mp-bg); color: var(--mp-muted); }
select.mp-form-control { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2378716C' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 38px; }
textarea.mp-form-control { min-height: 90px; resize: vertical; }
.mp-form-actions { display: flex; gap: 10px; flex-wrap: wrap; padding: 16px 20px; border-top: 1px solid var(--mp-border); background: var(--mp-bg); }
.mp-section-title { font-size: 13px; font-weight: 700; color: var(--mp-muted); text-transform: uppercase; letter-spacing: .06em; margin: 24px 0 12px; padding-bottom: 8px; border-bottom: 1px solid var(--mp-border); }
.mp-price-preview { background: var(--mp-bg); border: 1px solid var(--mp-border); border-radius: 10px; padding: 14px 16px; font-size: 13px; color: var(--mp-ink); }
.mp-price-preview div { display: flex; justify-content: space-between; margin-bottom: 4px; }
.mp-price-preview div:last-child { margin-bottom: 0; }
.mp-price-preview .saves { color: var(--mp-success); font-weight: 700; }
.mp-search-row { display: flex; align-items: center; gap: 10px; }
.mp-search-row .mp-form-control { flex: 1; }
.mp-search-row .mp-search-icon { width: 42px; height: 42px; border-radius: 10px; background: var(--mp-bg); border: 1px solid var(--mp-border); display: flex; align-items: center; justify-content: center; color: var(--mp-muted); flex-shrink: 0; }
.pkg-item-row { display: flex; align-items: center; gap: 12px; padding: 10px 12px; background: var(--mp-bg); border: 1px solid var(--mp-border); border-radius: 10px; margin-bottom: 8px; }
.pkg-item-row .pkg-item-rank { width: 28px; height: 28px; border-radius: 8px; background: var(--mp-surface); border: 1px solid var(--mp-border); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: var(--mp-muted); flex-shrink: 0; }
.pkg-item-row .pkg-item-info { flex: 1; min-width: 0; }
.pkg-item-row .pkg-item-name { font-size: 13px; font-weight: 600; color: var(--mp-ink); }
.pkg-item-row .pkg-item-meta { font-size: 11px; color: var(--mp-muted); }
.pkg-item-row .pkg-item-qty { width: 90px; }
.pkg-item-row .pkg-item-sub { font-size: 13px; font-weight: 700; color: var(--mp-ink); width: 90px; text-align: right; font-variant-numeric: tabular-nums; }
.pkg-item-row .pkg-item-remove { width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--mp-border); background: var(--mp-surface); color: var(--mp-danger); cursor: pointer; flex-shrink: 0; }
.pkg-item-row .pkg-item-remove:hover { background: var(--mp-danger); color: #fff; }
.mp-empty-state { text-align: center; padding: 24px; color: var(--mp-muted); font-size: 13px; border: 1px dashed var(--mp-border); border-radius: 10px; }
.mp-file-drop { border: 1px dashed var(--mp-border); border-radius: 10px; padding: 18px; text-align: center; color: var(--mp-muted); font-size: 13px; cursor: pointer; transition: all .15s ease; background: var(--mp-bg); }
.mp-file-drop:hover { border-color: var(--mp-primary); color: var(--mp-primary); }
.mp-file-drop input[type=file] { display: none; }
.mp-file-preview { margin-top: 12px; }
.mp-file-preview img { max-height: 80px; border-radius: 8px; border: 1px solid var(--mp-border); object-fit: cover; }
@media (max-width:767px){ .mp-form-grid { grid-template-columns: 1fr; } .pkg-item-row { flex-wrap: wrap; } }
</style>

<div class="mp-section">
  <?php $this->load->view('comman/code_flashdata'); ?>
</div>

<!-- Page Header -->
<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2><?= htmlspecialchars($page_title); ?></h2>
      <div class="mp-page-sub"><?= htmlspecialchars($store_name); ?> &mdash; <?= $is_update ? 'Update service package' : 'Create a service package'; ?></div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a href="<?php echo base_url('service_packages'); ?>" class="mp-qa-btn" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back to Packages
      </a>
    </div>
  </div>
</div>

<!-- Form Card -->
<div class="mp-section">
  <div class="mp-card-form box">
    <div class="mp-card-head">
      <h3>Package Details</h3>
    </div>
    <form id="package-form" method="post" enctype="multipart/form-data">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
      <input type="hidden" name="command" value="<?php echo $command; ?>">
      <input type="hidden" name="q_id" value="<?php echo isset($q_id) ? $q_id : ''; ?>">

      <div class="mp-card-body">
        <div class="mp-form-grid">
          <div class="mp-form-group">
            <label>Package Code <span class="text-danger">*</span></label>
            <input type="text" class="mp-form-control" name="package_code" value="<?php echo isset($package_code) ? htmlspecialchars($package_code) : ''; ?>" required>
          </div>
          <div class="mp-form-group">
            <label>Package Name <span class="text-danger">*</span></label>
            <input type="text" class="mp-form-control" name="package_name" value="<?php echo isset($package_name) ? htmlspecialchars($package_name) : ''; ?>" required>
          </div>
          <div class="mp-form-group full">
            <label>Description</label>
            <textarea class="mp-form-control" name="description" rows="3"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
          </div>
          <div class="mp-form-group full">
            <label>Package Image</label>
            <label class="mp-file-drop" for="package_image">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              Click to upload an image
            </label>
            <input type="file" id="package_image" class="mp-form-control" name="package_image" accept="image/*" style="display:none;" onchange="document.getElementById('pkg_img_preview').src = window.URL.createObjectURL(this.files[0]); document.getElementById('pkg_img_preview').style.display='block';">
            <?php if(!empty($package_image) && file_exists(FCPATH . $package_image)): ?>
              <div class="mp-file-preview"><img id="pkg_img_preview" src="<?php echo htmlspecialchars(base_url($package_image)); ?>"></div>
            <?php else: ?>
              <div class="mp-file-preview"><img id="pkg_img_preview" src="" style="display:none;"></div>
            <?php endif; ?>
          </div>
        </div>

        <div class="mp-section-title">Pricing</div>
        <div class="mp-form-grid">
          <div class="mp-form-group">
            <label>Pricing Model</label>
            <select class="mp-form-control" name="pricing_model" id="pricing_model">
              <option value="fixed" <?php echo (isset($pricing_model) && $pricing_model == 'fixed') ? 'selected' : ''; ?>>Fixed Package Price</option>
              <option value="calculated" <?php echo (isset($pricing_model) && $pricing_model == 'calculated') ? 'selected' : ''; ?>>Calculated (Sum + Discount)</option>
            </select>
          </div>
          <div class="mp-form-group" id="fixed_price_group">
            <label>Package Price <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="mp-form-control" name="package_price" id="package_price" value="<?php echo isset($package_price) ? $package_price : '0'; ?>">
          </div>
          <div class="mp-form-group" id="calc_discount_group" style="display:none;">
            <label>Discount Type</label>
            <select class="mp-form-control" name="discount_type" id="discount_type">
              <option value="" <?php echo (empty($discount_type)) ? 'selected' : ''; ?>>None</option>
              <option value="fixed" <?php echo (isset($discount_type) && $discount_type == 'fixed') ? 'selected' : ''; ?>>Fixed Amount</option>
              <option value="percentage" <?php echo (isset($discount_type) && $discount_type == 'percentage') ? 'selected' : ''; ?>>Percentage</option>
            </select>
          </div>
          <div class="mp-form-group" id="calc_discount_value_group" style="display:none;">
            <label>Discount Value</label>
            <input type="number" step="0.01" class="mp-form-control" name="discount" id="discount" value="<?php echo isset($discount) ? $discount : '0'; ?>" placeholder="Discount value">
          </div>
          <div class="mp-form-group full">
            <div class="mp-price-preview">
              <div><span>Individual Total:</span><span id="individual_total">0.00</span></div>
              <div><span>Package Price:</span><span id="final_price">0.00</span></div>
              <div class="saves"><span>Customer Saves:</span><span id="savings">0.00</span></div>
            </div>
          </div>
        </div>

        <div class="mp-section-title">Redemption Settings</div>
        <div class="mp-form-grid">
          <div class="mp-form-group">
            <label>Redemption Type</label>
            <select class="mp-form-control" name="redemption_type">
              <option value="single" <?php echo (isset($redemption_type) && $redemption_type == 'single') ? 'selected' : ''; ?>>Single Session (all at once)</option>
              <option value="multi" <?php echo (isset($redemption_type) && $redemption_type == 'multi') ? 'selected' : ''; ?>>Multi-Visit (over time)</option>
            </select>
          </div>
          <div class="mp-form-group">
            <label>Expiry Type</label>
            <select class="mp-form-control" name="expiry_type" id="expiry_type">
              <option value="none" <?php echo (isset($expiry_type) && $expiry_type == 'none') ? 'selected' : ''; ?>>No Expiry</option>
              <option value="days" <?php echo (isset($expiry_type) && $expiry_type == 'days') ? 'selected' : ''; ?>>After X Days</option>
              <option value="date" <?php echo (isset($expiry_type) && $expiry_type == 'date') ? 'selected' : ''; ?>>Fixed Date</option>
            </select>
          </div>
          <div class="mp-form-group" id="expiry_days_group">
            <label>Days</label>
            <input type="number" class="mp-form-control" name="expiry_days" value="<?php echo isset($expiry_days) ? $expiry_days : '30'; ?>" placeholder="Days">
          </div>
          <div class="mp-form-group" id="expiry_date_group" style="display:none;">
            <label>Expiry Date</label>
            <input type="text" class="mp-form-control datepicker" name="expiry_date" value="<?php echo isset($expiry_date) ? htmlspecialchars($expiry_date) : ''; ?>" placeholder="YYYY-MM-DD">
          </div>
        </div>

        <div class="mp-section-title">Package Contents</div>
        <p class="mp-form-hint" style="margin-bottom:12px;">Search for services or products to add to this package.</p>
        <div class="mp-search-row" style="margin-bottom:12px;">
          <div class="mp-search-icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </div>
          <input type="text" class="mp-form-control" id="item_search" placeholder="Search services or products to add...">
        </div>
        <div id="package_items_container">
          <div class="mp-empty-state" id="pkg_empty">No items added yet. Search above to add services or products.</div>
        </div>
      </div>

      <div class="mp-form-actions">
        <button type="button" id="btn_save" class="mp-qa-btn green">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          <?= $is_update ? 'Update Package' : 'Save Package'; ?>
        </button>
        <a href="<?php echo base_url('service_packages'); ?>" class="mp-qa-btn" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);">Cancel</a>
      </div>
    </form>
  </div>
</div>


<script>
$(document).ready(function() {
  $('.datepicker').datepicker({ format: 'yyyy-mm-dd', autoclose: true });

  // Pricing model toggle
  $('#pricing_model').change(function() {
    if ($(this).val() == 'fixed') {
      $('#fixed_price_group').show();
      $('#calc_discount_group, #calc_discount_value_group').hide();
    } else {
      $('#fixed_price_group').hide();
      $('#calc_discount_group, #calc_discount_value_group').show();
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
    var s1 = $.getJSON("<?php echo base_url('service_packages/ajax_get_services'); ?>");
    var s2 = $.getJSON("<?php echo base_url('service_packages/ajax_get_products'); ?>");
    $.when(s1, s2).done(function(services, products) {
      availableItems = [];
      $.each(services[0], function(i, s) { availableItems.push({ label: s.item_name + ' [Service]', value: s.id, type: 'service', price: s.sales_price }); });
      $.each(products[0], function(i, p) { availableItems.push({ label: p.item_name + ' [Product]', value: p.id, type: 'product', price: p.sales_price }); });
    }).fail(function(){ toastr['error']('Failed to load services/products for search.'); });
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
    $('#pkg_empty').hide();
    var safeName = $('<div>').text(itemName).html();
    var safeType = $('<div>').text(itemType.toUpperCase()).html();
    var safePrice = $('<div>').text(itemPrice).html();
    var html = '<div class="pkg-item-row package-item-row" data-price="' + parseFloat(itemPrice) + '">' +
      '<input type="hidden" name="package_item_id[]" value="' + parseInt(itemId) + '">' +
      '<input type="hidden" name="package_item_type[]" value="' + safeType.toLowerCase() + '">' +
      '<div class="pkg-item-rank">' + itemCount + '</div>' +
      '<div class="pkg-item-info"><div class="pkg-item-name">' + safeName + '</div><div class="pkg-item-meta">' + safeType + ' @ ' + safePrice + '</div></div>' +
      '<input type="number" step="0.01" name="package_item_qty[]" class="mp-form-control pkg-item-qty item-qty" value="1" min="0.01" onchange="recalcPrice()">' +
      '<span class="pkg-item-sub item-subtotal">' + parseFloat(itemPrice).toFixed(2) + '</span>' +
      '<button type="button" class="pkg-item-remove" onclick="removeItem(this)"><i class="fa fa-trash"></i></button>' +
      '</div>';
    $('#package_items_container').append(html);
    recalcPrice();
  }

  window.removeItem = function(btn) {
    $(btn).closest('.package-item-row').remove();
    // Re-number rows
    $('.package-item-row .pkg-item-rank').each(function(i){ $(this).text(i+1); });
    if ($('.package-item-row').length === 0) $('#pkg_empty').show();
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
    $('#savings').text(Math.max(0, total - finalPrice).toFixed(2));
  };

  $('#package_price, #discount, #discount_type').on('input change', recalcPrice);

  // Submit
  $('#btn_save').click(function() {
    if ($('#package_items_container .package-item-row').length === 0) {
      toastr['warning']('Please add at least one item to the package.');
      return;
    }
    var $btn = $(this);
    $btn.attr('disabled', true);
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
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
          $btn.attr('disabled', false);
        }
        $(".overlay").remove();
      },
      error: function() {
        toastr['error']('Something went wrong. Please try again.');
        $btn.attr('disabled', false);
        $(".overlay").remove();
      }
    });
  });
});
</script>
<script>$('.service-packages-active-li,.service_packages-active-li').addClass('active');</script>
