<?php $this->load->view('admin/desktop/_styles'); ?>

<?php
if(!isset($promotion_name)){
  $promotion_id=$promotion_name=$promotion_code=$description='';
  $discount_type='Percentage'; $discount_value=''; $min_price_rule=''; $min_margin_pct='';
  $applies_to='all'; $category_id=''; $brand_id=''; $start_date=''; $end_date='';
  $linked_item_ids=array();
  $mode='simple'; $min_spend=''; $usage_limit_per_customer=''; $usage_limit_total='';
}
$store_name = $this->session->userdata('store_name') ?: 'MartPoint';
$command = (!empty($promotion_id)) ? 'update' : 'save';
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
select.mp-form-control { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2378716C' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 38px; }
textarea.mp-form-control { min-height: 80px; resize: vertical; }

.select2-container--default .select2-selection--single { border: 1px solid var(--mp-border)!important; border-radius: 10px!important; height: 42px!important; }
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px!important; color: var(--mp-ink)!important; font-size: 14px!important; }
.select2-container--default .select2-selection--multiple { border: 1px solid var(--mp-border)!important; border-radius: 10px!important; min-height: 42px!important; }
.select2-container--default .select2-selection--multiple .select2-selection__choice { background: var(--mp-bg)!important; border: 1px solid var(--mp-border)!important; border-radius: 6px!important; font-size: 13px!important; }

.input-group.date { width: 100%; }
.input-group.date .input-group-addon { cursor: pointer; }

/* Mode segmented control (Simple / Advanced) */
.mp-mode-segmented { display: inline-flex; padding: 3px; background: var(--mp-bg); border: 1px solid var(--mp-border); border-radius: 10px; gap: 2px; }
.mp-seg-btn { border: none; background: transparent; padding: 7px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; color: var(--mp-muted); cursor: pointer; transition: all .15s ease; display: inline-flex; align-items: center; gap: 6px; line-height: 1; white-space: nowrap; }
.mp-seg-btn:hover { color: var(--mp-ink); }
.mp-seg-btn.active { background: var(--mp-surface); color: var(--mp-primary); box-shadow: 0 1px 2px rgba(0,0,0,.06); }
.mp-seg-btn svg { width: 14px; height: 14px; }
.mp-mode-wrap { display: flex; align-items: center; gap: 10px; }
.mp-mode-label { font-size: 12px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .03em; }

@media (max-width: 768px) {
  .mp-form-grid { grid-template-columns: 1fr; }
  .mp-card-form .mp-card-head { flex-direction: column; align-items: flex-start; gap: 12px; }
}
</style>

<form class="form-horizontal" id="promotion-form" onkeypress="return event.keyCode != 13;">
<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
<input type="hidden" id="base_url" value="<?= $base_url; ?>">
<input type="hidden" name="command" value="<?= $command; ?>">
<input type="hidden" name="promotion_id" id="promotion_id" value="<?= $promotion_id ?? ''; ?>">

<!-- Page Header + Save/Close -->
<div class="mp-page-head">
  <div>
    <h2><?= $page_title; ?></h2>
    <div class="mp-page-sub"><?= htmlspecialchars($store_name); ?> &mdash; <?= !empty($promotion_id) ? 'Update Promotion' : 'Add Promotion'; ?></div>
  </div>
  <div style="display:flex;gap:10px;">
    <a href="<?= base_url('promotions'); ?>" class="mp-qa-btn" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);">Close</a>
    <button type="button" id="save" name="save" class="mp-qa-btn green" value="Save">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
      <?= !empty($promotion_id) ? 'Update' : 'Save'; ?>
    </button>
  </div>
</div>

<!-- Promotion Details -->
<div class="mp-card-form">
  <div class="mp-card-head">
    <h3>Promotion Details</h3>
    <div class="mp-mode-wrap">
      <span class="mp-mode-label">Mode</span>
      <div class="mp-mode-segmented" role="tablist" aria-label="Promotion mode">
        <button type="button" class="mp-seg-btn <?= ($mode == 'simple') ? 'active' : ''; ?>" data-mode="simple" role="tab" aria-selected="<?= ($mode == 'simple') ? 'true' : 'false'; ?>">
          <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
          Simple
        </button>
        <button type="button" class="mp-seg-btn <?= ($mode == 'advanced') ? 'active' : ''; ?>" data-mode="advanced" role="tab" aria-selected="<?= ($mode == 'advanced') ? 'true' : 'false'; ?>">
          <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 20v-6M12 4v2M6 20v-2M6 4v6M18 20v-4M18 4v10M4 14h4M10 8h4M16 12h4"/></svg>
          Advanced
        </button>
      </div>
      <input type="hidden" name="mode" id="mode_input" value="<?= htmlspecialchars($mode); ?>">
    </div>
  </div>
  <div class="mp-card-body">
    <div class="mp-form-grid">

      <div class="mp-form-group">
        <label for="promotion_name">Promotion Name <span class="text-danger">*</span></label>
        <input type="text" class="mp-form-control" id="promotion_name" name="promotion_name" value="<?= htmlspecialchars($promotion_name); ?>" autofocus>
      </div>

      <div class="mp-form-group">
        <label for="promotion_code">Promotion Code</label>
        <input type="text" class="mp-form-control" id="promotion_code" name="promotion_code" value="<?= htmlspecialchars($promotion_code); ?>" placeholder="Optional e.g. SUMMER25">
      </div>

      <div class="mp-form-group full">
        <label for="description">Description</label>
        <textarea class="mp-form-control" id="description" name="description" rows="2"><?= htmlspecialchars($description); ?></textarea>
      </div>

      <div class="mp-form-group">
        <label for="discount_type">Discount Type <span class="text-danger">*</span></label>
        <select class="form-control select2" id="discount_type" name="discount_type" style="width:100%;">
          <option value="Percentage" <?= ($discount_type == 'Percentage') ? 'selected' : ''; ?>>Percentage (%)</option>
          <option value="Fixed" <?= ($discount_type == 'Fixed') ? 'selected' : ''; ?>>Fixed Amount</option>
        </select>
      </div>

      <div class="mp-form-group">
        <label for="discount_value">Discount Value <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" class="mp-form-control only_currency" id="discount_value" name="discount_value" value="<?= htmlspecialchars($discount_value); ?>">
      </div>

      <div class="mp-form-group full">
        <label for="applies_to"><?= $this->lang->line('applies_to'); ?></label>
        <select class="form-control select2" id="applies_to" name="applies_to" style="width:100%;">
          <option value="all" <?= ($applies_to == 'all') ? 'selected' : ''; ?>>All Items</option>
          <option value="category" <?= ($applies_to == 'category') ? 'selected' : ''; ?>>Specific Category (Collection)</option>
          <option value="brand" <?= ($applies_to == 'brand') ? 'selected' : ''; ?>>Specific Brand</option>
          <option value="items" <?= ($applies_to == 'items') ? 'selected' : ''; ?>>Specific Items</option>
        </select>
      </div>

      <div class="mp-form-group" id="category_row" style="display:none;">
        <label for="category_id">Category</label>
        <select class="form-control select2" id="category_id" name="category_id" style="width:100%;">
          <option value="">-Select-</option>
          <?= get_categories_select_list($category_id ?? '', get_current_store_id()); ?>
        </select>
      </div>

      <div class="mp-form-group" id="brand_row" style="display:none;">
        <label for="brand_id">Brand</label>
        <select class="form-control select2" id="brand_id" name="brand_id" style="width:100%;">
          <option value="">-Select-</option>
          <?= get_brands_select_list($brand_id ?? '', get_current_store_id()); ?>
        </select>
      </div>

      <div class="mp-form-group full" id="items_row" style="display:none;">
        <label for="item_ids">Select Items</label>
        <select class="form-control select2" id="item_ids" name="item_ids[]" multiple="multiple" style="width:100%;">
        <?php
        $all_items = $this->db->select('id, item_name')->where('store_id', get_current_store_id())->where('status', 1)->where('service_bit', 0)->order_by('item_name', 'asc')->get('db_items')->result();
        $linked = $linked_item_ids ?? array();
        foreach($all_items as $it):
          $sel = in_array($it->id, $linked) ? 'selected' : '';
        ?>
          <option value="<?= $it->id; ?>" <?= $sel; ?>><?= htmlspecialchars($it->item_name); ?></option>
        <?php endforeach; ?>
        </select>
        <p class="mp-form-hint">Select one or more items this promotion applies to.</p>
      </div>

      <div class="mp-form-group">
        <label for="start_date"><?= $this->lang->line('start_date'); ?> <span class="text-danger">*</span></label>
        <div class="input-group date">
          <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
          <input type="text" class="form-control datepicker" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date); ?>">
        </div>
      </div>

      <div class="mp-form-group">
        <label for="end_date"><?= $this->lang->line('end_date'); ?> <span class="text-danger">*</span></label>
        <div class="input-group date">
          <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
          <input type="text" class="form-control datepicker" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date); ?>">
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Advanced Settings (hidden in simple mode) -->
<div class="mp-card-form" id="advanced_section" style="display:none;">
  <div class="mp-card-head"><h3>Advanced Rules</h3></div>
  <div class="mp-card-body">
    <div class="mp-form-grid">

      <div class="mp-form-group">
        <label for="min_spend">Minimum Spend</label>
        <input type="number" step="0.01" min="0" class="mp-form-control only_currency" id="min_spend" name="min_spend" value="<?= htmlspecialchars($min_spend); ?>" placeholder="e.g. 10000">
        <p class="mp-form-hint">Cart total must be at least this amount for the code to work. Leave empty for no minimum.</p>
      </div>

      <div class="mp-form-group">
        <label for="usage_limit_per_customer">Usage Limit Per Customer</label>
        <input type="number" min="0" class="mp-form-control" id="usage_limit_per_customer" name="usage_limit_per_customer" value="<?= htmlspecialchars($usage_limit_per_customer); ?>" placeholder="e.g. 1 (blank = unlimited)">
        <p class="mp-form-hint">How many times a single customer can use this code. Blank = unlimited.</p>
      </div>

      <div class="mp-form-group">
        <label for="usage_limit_total">Total Usage Limit</label>
        <input type="number" min="0" class="mp-form-control" id="usage_limit_total" name="usage_limit_total" value="<?= htmlspecialchars($usage_limit_total); ?>" placeholder="e.g. 100 (blank = unlimited)">
        <p class="mp-form-hint">Maximum total uses across all customers. Blank = unlimited. Good for flash sales.</p>
      </div>

      <div class="mp-form-group">
        <label for="min_price_rule"><?= $this->lang->line('min_price_rule'); ?></label>
        <input type="number" step="0.01" min="0" class="mp-form-control only_currency" id="min_price_rule" name="min_price_rule" value="<?= htmlspecialchars($min_price_rule); ?>" placeholder="Never sell below this price">
        <p class="mp-form-hint">Protects margin — discount will never drop the price below this amount.</p>
      </div>

      <div class="mp-form-group">
        <label for="min_margin_pct"><?= $this->lang->line('min_margin_pct'); ?></label>
        <input type="number" step="0.01" min="0" max="100" class="mp-form-control" id="min_margin_pct" name="min_margin_pct" value="<?= htmlspecialchars($min_margin_pct); ?>" placeholder="e.g. 20">
        <p class="mp-form-hint">Discount will never drop below this % margin over cost price.</p>
      </div>

    </div>
  </div>
</div>

</form>


<script type="text/javascript">
var base_url = $("#base_url").val();

// Mode segmented control (Simple / Advanced)
function toggle_mode(mode){
  if(!mode){ mode = $("#mode_input").val(); }
  if(mode == 'advanced'){
    $("#advanced_section").slideDown(180);
  } else {
    $("#advanced_section").slideUp(180);
    // Clear advanced fields when switching to simple
    $("#min_spend, #usage_limit_per_customer, #usage_limit_total, #min_price_rule, #min_margin_pct").val('');
  }
}

$(".mp-seg-btn").on("click", function(){
  var mode = $(this).data("mode");
  $(".mp-seg-btn").removeClass("active").attr("aria-selected","false");
  $(this).addClass("active").attr("aria-selected","true");
  $("#mode_input").val(mode);
  toggle_mode(mode);
});

// Initialise on load
toggle_mode();

function toggle_applies_rows(){
  var v = $("#applies_to").val();
  $("#category_row").hide(); $("#brand_row").hide(); $("#items_row").hide();
  if(v == "category"){ $("#category_row").show(); }
  if(v == "brand"){ $("#brand_row").show(); }
  if(v == "items"){ $("#items_row").show(); }
}
$("#applies_to").on("change", function(){ toggle_applies_rows(); });
toggle_applies_rows();

$("#save").on("click", function(){
  if(!$("#promotion_name").val()){ toastr.error("Promotion Name is required."); return; }
  if(!$("#discount_value").val() || parseFloat($("#discount_value").val()) <= 0){ toastr.error("Discount Value is required and must be greater than 0."); return; }
  if($("#discount_type").val() == "Percentage" && parseFloat($("#discount_value").val()) > 100){ toastr.error("Percentage discount cannot exceed 100%."); return; }
  if(!$("#start_date").val() || !$("#end_date").val()){ toastr.error("Start and End dates are required."); return; }
  var sDate = $("#start_date").val().split("-").reverse().join("-"); // dd-mm-yyyy → yyyy-mm-dd
  var eDate = $("#end_date").val().split("-").reverse().join("-");
  if(eDate < sDate){ toastr.error("End Date cannot be before Start Date."); return; }
  var $btn = $(this);
  var originalHtml = $btn.html();
  $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
  var data = new FormData($("#promotion-form")[0]);
  $.ajax({
    type: 'POST', url: base_url + 'promotions/save', data: data,
    cache: false, contentType: false, processData: false,
    success: function(res){
      if(res.indexOf("success") !== -1){
        toastr.success("Promotion saved successfully!");
        setTimeout(function(){ window.location.href = base_url + "promotions"; }, 800);
      } else {
        toastr.error(res);
        $btn.prop('disabled', false).html(originalHtml);
      }
    },
    error: function(){
      toastr.error("Network error. Please try again.");
      $btn.prop('disabled', false).html(originalHtml);
    }
  });
});
</script>
<script>$(".<?php echo basename(__FILE__, '.php'); ?>-active-li").addClass("active");</script>
