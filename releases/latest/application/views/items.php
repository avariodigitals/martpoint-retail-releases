<?php
$CI =& get_instance();
// Business model: product_based, service_based, product_and_service
$store_profile = mp_get_store_profile();
$business_model = $store_profile['business_model'] ?? 'product_based';
$services_enabled = service_module();
$show_item_toggle = ($business_model === 'product_based' || $business_model === 'product_and_service');
$show_service_toggle = $services_enabled && ($business_model === 'service_based' || $business_model === 'product_and_service');

// Default values for new items
if(!isset($item_name)){
  $item_name=$sku=$hsn=$opening_stock=$brand_id=$category_id=$gst_percentage=$tax_type=
  $sales_price=$purchase_price=$profit_margin=$unit_id=$price=$alert_qty=$store_id="";
  $stock = 0;
  $seller_points =0;
  $custom_barcode ='';
  $description ='';
  $mrp ='';
  $batch_lot ='';
  $child_bit ='';
  $tax_id ='';
  $serial_number ='';
  $imei_number ='';
  $expire_date ='';
  $mfg_date ='';
  $warranty_months ='';
  $track_serial = 0;
  $track_imei = 0;
  $not_for_sale = 0;
  $consumable_unit ='';
  $accept_custom_order = 0;
  $requires_quote = 0;
  $requires_deposit = 0;
  $workflow_template_key = 'standard';
  $item_group='Single';
  $discount='';
  $discount_type='Percentage';
  $warehouse_id='';
  $opening_stock_readonly='';
  $item_code = get_init_code('item');
  $service_bit = 0;
  $sac = '';
  $laundry_service_type = '';
  $commission_type = 'none';
  $commission_value = 0;
  $deposit_required = 0;
  $deposit_percent = 0;
  // If business is service-only, default to Service
  if($business_model === 'service_based' && $show_service_toggle){
    $service_bit = 1;
  }
}
else{
  $opening_stock_readonly = 'readonly';
  // Ensure these have defaults for edit mode (get_details may not set all)
  if(!isset($warehouse_id)) $warehouse_id='';
  if(!isset($accept_custom_order)) $accept_custom_order=0;
  if(!isset($requires_quote)) $requires_quote=0;
  if(!isset($requires_deposit)) $requires_deposit=0;
  if(!isset($workflow_template_key)) $workflow_template_key='standard';
  if(!isset($custom_order_fields_json)) $custom_order_fields_json='';
  if(!isset($not_for_sale)) $not_for_sale=0;
  if(!isset($consumable_unit)) $consumable_unit='';
  if(!isset($track_serial)) $track_serial=0;
  if(!isset($track_imei)) $track_imei=0;
  if(!isset($item_barcodes)) $item_barcodes=[];
  if(!isset($service_bit)) $service_bit=0;
  if(!isset($sac)) $sac='';
  if(!isset($laundry_service_type)) $laundry_service_type='';
  if(!isset($commission_type)) $commission_type='none';
  if(!isset($commission_value)) $commission_value=0;
  if(!isset($deposit_required)) $deposit_required=0;
  if(!isset($deposit_percent)) $deposit_percent=0;
}
// Only default opening_stock to 0 when not provided by the controller.
// The model's get_details() does not pass opening_stock (stock adjustments
// are handled separately), so edit mode defaults to 0 (no change).
// For new items, the if(!isset($item_name)) block above already sets it to "".
if(!isset($opening_stock) || $opening_stock === ''){
  $opening_stock = '0';
}

// Is this a service record?
$is_service = ($service_bit == 1);

// Show barcode/unit table when batch OR any unit tracking is enabled (and not a service)
$show_unit_table = !$is_service && (mp_feature_enabled('batch_tracking') || mp_feature_enabled('serial_number_tracking') || mp_feature_enabled('imei_tracking') || mp_feature_enabled('warranty_tracking'));

// Laundry detection
$profile = mp_get_store_profile();
$is_laundry = ($profile['industry_type'] ?? '') === 'laundry' || mp_feature_enabled('laundry_workflow');

// Save vs Update button
if(!empty($item_name)){
  $btn_name = 'Update';
  $btn_id = 'update';
} else {
  $btn_name = 'Save';
  $btn_id = 'save';
}

$this->load->view('admin/desktop/_styles');
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
textarea.mp-form-control { min-height: 80px; }

/* Compact pricing row — all prices on one line */
.mp-price-row { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; }
.mp-price-row .mp-form-group { gap: 4px; }
.mp-price-row .mp-form-control { padding: 9px 12px; font-size: 13px; }
.mp-price-row label { font-size: 12px; font-weight: 600; color: var(--mp-muted); text-transform: uppercase; letter-spacing: .02em; }
@media (max-width: 1280px) { .mp-price-row { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 768px) { .mp-price-row { grid-template-columns: 1fr 1fr; } }

.mp-input-row { display: flex; gap: 12px; }
.mp-input-row .mp-form-control { flex: 1; }
.mp-input-row .mp-form-control.short { max-width: 160px; }

.mp-select-with-add { display: flex; gap: 8px; align-items: stretch; }
.mp-select-with-add .mp-form-control { flex: 1; }
.mp-add-btn { flex-shrink: 0; width: 42px; border: 1px solid var(--mp-border); border-radius: 10px; background: var(--mp-bg); color: var(--mp-primary); cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; transition: all .15s ease; }
.mp-add-btn:hover { background: var(--mp-primary); color: #fff; border-color: var(--mp-primary); }

.mp-tax-row { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.mp-tax-row .mp-form-control { width: 200px; flex: 0 0 auto; }
.mp-tax-row .mp-toggle-row { margin-left: auto; }

.mp-toggle-row { display: flex; align-items: center; gap: 12px; }
.mp-toggle { appearance: none; width: 42px; height: 24px; border-radius: 12px; background: var(--mp-border); position: relative; cursor: pointer; transition: all .2s ease; flex-shrink: 0; }
.mp-toggle::after { content: ''; position: absolute; width: 20px; height: 20px; border-radius: 50%; background: #fff; top: 2px; left: 2px; transition: all .2s ease; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
.mp-toggle:checked { background: var(--mp-primary); }
.mp-toggle:checked::after { left: 20px; }
.mp-toggle-label { font-size: 14px; font-weight: 500; color: var(--mp-ink); }

.mp-toggle-options { display: flex; align-items: center; gap: 32px; }
.mp-toggle-wrap { display: flex; flex-direction: column; gap: 4px; }
.mp-toggle-desc { font-size: 12px; color: var(--mp-muted); margin: 0; padding-left: 54px; }

.mp-check-label { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 500; color: var(--mp-ink); cursor: pointer; }
.mp-check-label input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--mp-primary); cursor: pointer; }

.mp-upload-box { border: 2px dashed var(--mp-border); border-radius: 12px; padding: 28px; text-align: center; color: var(--mp-muted); cursor: pointer; transition: all .15s ease; }
.mp-upload-box:hover { border-color: var(--mp-primary); background: rgba(0,87,255,.03); }
.mp-upload-box svg { margin-bottom: 10px; color: var(--mp-muted); }
.mp-upload-box strong { display: block; font-size: 14px; color: var(--mp-ink); margin-bottom: 4px; }
.mp-upload-box span { font-size: 13px; }

.mp-hidden { display: none !important; }

/* Tables inside cards */
.mp-inner-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.mp-inner-table th { background: var(--mp-bg); padding: 8px 10px; font-weight: 600; color: var(--mp-muted); text-transform: uppercase; font-size: 11px; letter-spacing: .03em; border-bottom: 1px solid var(--mp-border); text-align: left; }
.mp-inner-table td { padding: 8px 10px; border-bottom: 1px solid var(--mp-border); }
.mp-inner-table input, .mp-inner-table select { width: 100%; padding: 7px 10px; border: 1px solid var(--mp-border); border-radius: 8px; font-size: 13px; background: var(--mp-surface); }
.mp-inner-table .btn-xs-danger { background: rgba(220,38,38,.1); color: var(--mp-danger); border: none; padding: 6px 10px; border-radius: 8px; cursor: pointer; font-size: 12px; }
.mp-inner-table .btn-xs-danger:hover { background: var(--mp-danger); color: #fff; }
.mp-table-add-btn { margin-top: 10px; padding: 7px 14px; background: rgba(5,150,105,.1); color: var(--mp-success); border: 1px solid transparent; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
.mp-table-add-btn:hover { background: var(--mp-success); color: #fff; }

.mp-recipe-stats { display: flex; gap: 24px; }
.mp-recipe-stat { text-align: center; }
.mp-recipe-stat .lbl { font-size: 11px; color: var(--mp-muted); text-transform: uppercase; }
.mp-recipe-stat .val { font-size: 18px; font-weight: 700; color: var(--mp-text); }
.mp-recipe-stat .val.green { color: var(--mp-success); }

@media (max-width: 1024px) {
  .mp-form-grid { grid-template-columns: 1fr; }
  .mp-tax-row .mp-form-control { width: 100%; flex: 1 1 auto; }
  .mp-tax-row .mp-toggle-row { margin-left: 0; }
}

/* Style select2 to match mp-form-control */
.select2-container--default .select2-selection--single {
  border: 1px solid var(--mp-border) !important;
  border-radius: 10px !important;
  height: 42px !important;
  background: var(--mp-surface) !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
  line-height: 40px !important;
  color: var(--mp-ink) !important;
  font-size: 14px !important;
  font-weight: 500 !important;
  padding-left: 14px !important;
  padding-right: 8px !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
  height: 40px !important;
}
.select2-container--default.select2-container--focus .select2-selection--single,
.select2-container--default.select2-container--open .select2-selection--single {
  border-color: var(--mp-primary) !important;
  box-shadow: 0 0 0 3px rgba(0,87,255,.1) !important;
}
.select2-container--default .select2-selection--multiple {
  border: 1px solid var(--mp-border) !important;
  border-radius: 10px !important;
  min-height: 42px !important;
  background: var(--mp-surface) !important;
}
.select2-dropdown {
  border: 1px solid var(--mp-border) !important;
  border-radius: 10px !important;
  box-shadow: var(--mp-shadow) !important;
}
.select2-results__option--highlighted[aria-selected] {
  background: var(--mp-primary) !important;
}

/* Loading overlay container — items.js appends overlay to .box */
.mp-items-box { position: relative; }
.mp-items-box > .overlay {
  position: absolute; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(255,255,255,.7); z-index: 9999;
  display: flex; align-items: center; justify-content: center;
}

/* Item/Service visibility toggling */
.mp-item-only { display: block; }
.mp-service-only { display: none; }
body.mp-mode-service .mp-item-only { display: none !important; }
body.mp-mode-service .mp-service-only { display: block !important; }
</style>

<div class="box mp-items-box">
<?= form_open('#', array('class' => 'form', 'id' => 'items-form', 'enctype' => 'multipart/form-data', 'method' => 'POST')); ?>
<input type="hidden" id="base_url" value="<?php echo $base_url; ?>">
<input type="hidden" name="store_id" id="store_id" value="<?php echo get_current_store_id(); ?>">
<?php if(!empty($q_id)): ?>
<input type="hidden" name="q_id" id="q_id" value="<?php echo $q_id; ?>">
<?php endif; ?>
<input type="hidden" name="hidden_rowcount" id="hidden_rowcount" value="1">

<!-- Page head -->
<div class="mp-page-head">
  <div>
    <h2><?= $page_title; ?></h2>
    <div class="mp-page-sub">Add/Update <?= mp_label('item'); ?></div>
  </div>
  <div style="display:flex;gap:10px;">
    <a href="<?php echo $base_url; ?>items" class="mp-qa-btn" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);">Cancel</a>
    <button type="button" id="<?php echo $btn_id; ?>" class="mp-qa-btn green" title="Save Data">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:inline-block;vertical-align:middle;margin-right:6px;"><polyline points="20 6 9 17 4 12"/></svg>
      <?php echo $btn_name; ?>
    </button>
  </div>
</div>

<!-- Item Type (only show toggle if both products and services are enabled) -->
<?php if($show_item_toggle && $show_service_toggle): ?>
<div class="mp-card-form" id="item_type_card">
  <div class="mp-card-head"><h3>Item Type</h3></div>
  <div class="mp-card-body">
    <div class="mp-toggle-options">
      <div class="mp-toggle-row">
        <input class="mp-toggle" id="type_item" name="item_type" type="radio" value="item" <?= ($is_service) ? '' : 'checked'; ?>>
        <label class="mp-toggle-label" for="type_item">Item</label>
      </div>
      <div class="mp-toggle-row">
        <input class="mp-toggle" id="type_service" name="item_type" type="radio" value="service" <?= ($is_service) ? 'checked' : ''; ?>>
        <label class="mp-toggle-label" for="type_service">Service</label>
      </div>
    </div>
  </div>
</div>
<?php elseif($show_service_toggle && !$show_item_toggle): ?>
<input type="hidden" name="item_type" id="item_type_hidden" value="service">
<?php else: ?>
<input type="hidden" name="item_type" id="item_type_hidden" value="item">
<?php endif; ?>

<!-- Basic Information -->
<div class="mp-card-form">
  <div class="mp-card-head"><h3>Basic Information</h3></div>
  <div class="mp-card-body">
    <div class="mp-form-grid">
      <div class="mp-form-group full">
        <label for="item_name"><?= mp_label('item'); ?> Name <span class="text-danger">*</span></label>
        <input class="mp-form-control" id="item_name" name="item_name" placeholder="e.g. Coca-Cola 50cl" type="text" value="<?php print htmlspecialchars($item_name); ?>" autofocus>
        <span id="item_name_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group">
        <label for="item_code"><?= mp_label('item'); ?> Code <span class="text-danger">*</span></label>
        <input class="mp-form-control" id="item_code" name="item_code" type="text" value="<?php print htmlspecialchars($item_code); ?>">
        <span id="item_code_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group">
        <label for="custom_barcode"><?= $this->lang->line('barcode'); ?></label>
        <input class="mp-form-control" id="custom_barcode" name="custom_barcode" placeholder="Scan or type barcode" type="text" value="<?php print htmlspecialchars($custom_barcode); ?>">
        <span id="custom_barcode_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group">
        <label for="sku"><?= $this->lang->line('sku'); ?></label>
        <input class="mp-form-control" id="sku" name="sku" placeholder="Stock keeping unit" type="text" value="<?php print htmlspecialchars($sku); ?>">
        <span id="sku_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group mp-item-only">
        <label for="hsn"><?= $this->lang->line('hsn'); ?></label>
        <input class="mp-form-control" id="hsn" name="hsn" type="text" value="<?php print htmlspecialchars($hsn); ?>">
      </div>
      <!-- SAC (Service Accounting Code — service-only) -->
      <div class="mp-form-group mp-service-only">
        <label for="sac"><?= $this->lang->line('sac'); ?></label>
        <input class="mp-form-control" id="sac" name="sac" type="text" value="<?php print htmlspecialchars($sac); ?>">
        <span id="sac_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group mp-item-only">
        <label for="brand_id"><?= $this->lang->line('brand'); ?></label>
        <div class="mp-select-with-add">
          <select class="mp-form-control select2" id="brand_id" name="brand_id" style="width:100%;">
            <option value="">-Select-</option>
            <?= get_brands_select_list($brand_id); ?>
          </select>
          <button type="button" class="mp-add-btn" data-toggle="modal" data-target="#brand_modal" title="Add Brand"><i class="fa fa-plus"></i></button>
        </div>
        <span id="brand_id_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group">
        <label for="category_id"><?= $this->lang->line('category'); ?> <span class="text-danger">*</span></label>
        <div class="mp-select-with-add">
          <select class="mp-form-control select2" id="category_id" name="category_id" required style="width:100%;">
            <option value="">-Select-</option>
            <?= get_categories_select_list($category_id); ?>
          </select>
          <button type="button" class="mp-add-btn" data-toggle="modal" data-target="#category_modal" title="Add Category"><i class="fa fa-plus"></i></button>
        </div>
        <span id="category_id_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group mp-item-only">
        <label for="unit_id"><?= $this->lang->line('unit'); ?> <span class="text-danger">*</span></label>
        <div class="mp-select-with-add">
          <select class="mp-form-control select2" id="unit_id" name="unit_id" required style="width:100%;">
            <?= get_units_select_list($unit_id); ?>
          </select>
          <?php if(mp_feature_enabled('multi_unit_inventory')): ?>
          <button type="button" class="mp-add-btn" data-toggle="modal" data-target="#unit_modal" title="Add Unit"><i class="fa fa-plus"></i></button>
          <?php endif; ?>
        </div>
        <span id="unit_id_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group mp-item-only" id="item_group_group">
        <label for="item_group"><?= $this->lang->line('item_group'); ?> <span class="text-danger">*</span></label>
        <select class="mp-form-control select2" id="item_group" name="item_group" style="width:100%;">
          <option value="Single" <?php if($item_group=='Single') echo 'selected'; ?>>Single</option>
          <?php if(mp_feature_enabled('bundles')): ?>
          <option value="Variants" <?php if($item_group=='Variants') echo 'selected'; ?>>Variants</option>
          <?php endif; ?>
        </select>
        <span id="item_group_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group full mp-hidden" id="attribute_types_box">
        <label for="attribute_types">Attribute Types</label>
        <select class="mp-form-control select2" id="attribute_types" name="attribute_types[]" multiple style="width:100%;">
          <?php
          $attribute_map = $this->items->get_variant_attribute_map();
          $selected_attribute_types = isset($attribute_types) ? (array)$attribute_types : array();
          foreach($attribute_map as $type => $vals):
            $selected = in_array($type, $selected_attribute_types) ? 'selected' : '';
            $label = ucfirst($type) . ' (' . implode(', ', array_slice($vals, 0, 3)) . (count($vals) > 3 ? '...' : '') . ')';
          ?>
          <option value="<?= $type; ?>" <?= $selected; ?>><?= $label; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group mp-item-only">
        <label for="alert_qty"><?= $this->lang->line('alert_qty'); ?></label>
        <input class="mp-form-control" id="alert_qty" name="alert_qty" type="number" min="0" value="<?php print $alert_qty; ?>">
        <span id="alert_qty_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group">
        <label for="seller_points"><?= $this->lang->line('seller_points'); ?></label>
        <input class="mp-form-control" id="seller_points" name="seller_points" type="text" value="<?php print store_number_format($seller_points,0); ?>">
        <span id="seller_points_msg" style="display:none" class="text-danger"></span>
      </div>
      <?php if($is_laundry): ?>
      <div class="mp-form-group mp-service-only">
        <label for="laundry_service_type">Laundry Service Type <span class="text-danger">*</span></label>
        <select class="mp-form-control select2" id="laundry_service_type" name="laundry_service_type" style="width:100%;">
          <option value="">-Select-</option>
          <option value="wash_iron" <?= ($laundry_service_type == 'wash_iron') ? 'selected' : ''; ?>>Wash + Iron</option>
          <option value="wash_only" <?= ($laundry_service_type == 'wash_only') ? 'selected' : ''; ?>>Wash Only</option>
          <option value="iron_only" <?= ($laundry_service_type == 'iron_only') ? 'selected' : ''; ?>>Iron Only</option>
          <option value="dry_clean" <?= ($laundry_service_type == 'dry_clean') ? 'selected' : ''; ?>>Dry Clean</option>
        </select>
        <span id="laundry_service_type_msg" style="display:none" class="text-danger"></span>
      </div>
      <?php endif; ?>
      <?php if(mp_feature_enabled('staff_commission')): ?>
      <div class="mp-form-group mp-service-only">
        <label for="commission_type">Commission Type</label>
        <select class="mp-form-control select2" id="commission_type" name="commission_type" style="width:100%;" onchange="toggleCommissionValue()">
          <option value="none" <?= ($commission_type == 'none') ? 'selected' : ''; ?>>No Commission</option>
          <option value="flat" <?= ($commission_type == 'flat') ? 'selected' : ''; ?>>Flat Amount</option>
          <option value="percent" <?= ($commission_type == 'percent') ? 'selected' : ''; ?>>Percentage (%)</option>
        </select>
      </div>
      <div class="mp-form-group mp-service-only" id="commission_value_wrap" style="<?= ($commission_type != 'none') ? '' : 'display:none;'; ?>">
        <label for="commission_value">Commission Value</label>
        <input type="number" step="0.01" min="0" class="mp-form-control" id="commission_value" name="commission_value" value="<?= $commission_value; ?>">
        <span id="commission_value_msg" style="display:none" class="text-danger"></span>
      </div>
      <?php endif; ?>
      <div class="mp-form-group full">
        <label for="description"><?= $this->lang->line('description'); ?></label>
        <textarea class="mp-form-control" id="description" name="description" rows="3" placeholder="Optional description"><?php print htmlspecialchars($description); ?></textarea>
        <span id="description_msg" style="display:none" class="text-danger"></span>
      </div>
    </div>
  </div>
</div>

<!-- Optionals (item-only) -->
<div class="mp-card-form mp-item-only">
  <div class="mp-card-head"><h3>Optionals</h3></div>
  <div class="mp-card-body">
    <div class="mp-form-grid">
      <div class="mp-form-group full">
        <label class="mp-check-label">
          <input type="checkbox" id="not_for_sale" name="not_for_sale" value="1" <?= ($not_for_sale==1) ? 'checked' : ''; ?>>
          Not for Sale <span style="font-weight:400;color:var(--mp-muted);font-size:12px;">(Consumable / Raw Material)</span>
        </label>
        <p class="mp-form-hint">Hide from POS. Use in treatments, production, etc.</p>
      </div>
      <div class="mp-form-group">
        <label for="consumable_unit">Unit Label <span style="font-weight:400;color:var(--mp-muted);font-size:12px;">(for consumables)</span></label>
        <input class="mp-form-control" id="consumable_unit" name="consumable_unit" placeholder="e.g. ml, bottle, pump, sachet" type="text" value="<?php print htmlspecialchars($consumable_unit); ?>">
        <span id="consumable_unit_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group full">
        <label class="mp-check-label">
          <input type="checkbox" id="accept_custom_order" name="accept_custom_order" value="1" <?= ($accept_custom_order==1) ? 'checked' : ''; ?>>
          Accept Custom Orders <span style="font-weight:400;color:var(--mp-muted);font-size:12px;">(Made to Order)</span>
        </label>
        <p class="mp-form-hint">Furniture, cakes, tailored items. Capture specs at POS.</p>
      </div>
    </div>

    <!-- Custom Order Settings -->
    <div id="custom-order-options" class="mp-hidden" style="margin-top:16px;padding-top:16px;border-top:1px solid var(--mp-border);">
      <h4 style="font-size:14px;font-weight:700;margin:0 0 14px;color:var(--mp-text);">Custom Order Settings</h4>
      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label class="mp-check-label">
            <input type="checkbox" id="requires_quote" name="requires_quote" value="1" <?= ($requires_quote==1) ? 'checked' : ''; ?>>
            Requires Quote
          </label>
          <p class="mp-form-hint">Price set by staff after taking order</p>
        </div>
        <div class="mp-form-group">
          <label class="mp-check-label">
            <input type="checkbox" id="requires_deposit" name="requires_deposit" value="1" <?= ($requires_deposit==1) ? 'checked' : ''; ?>>
            Requires Deposit
          </label>
          <p class="mp-form-hint">Customer pays deposit before production starts</p>
        </div>
        <div class="mp-form-group full">
          <label for="workflow_template_key">Workflow</label>
          <select class="mp-form-control select2" id="workflow_template_key" name="workflow_template_key" style="width:100%;">
            <option value="standard" <?= ($workflow_template_key=='standard') ? 'selected' : ''; ?>>Standard (New → Production → Ready → Delivered)</option>
            <option value="food" <?= ($workflow_template_key=='food') ? 'selected' : ''; ?>>Food / Bakery (New → Confirmed → Baking → Ready → Picked Up)</option>
            <option value="furniture" <?= ($workflow_template_key=='furniture') ? 'selected' : ''; ?>>Furniture (Quote → Deposit → Build → QC → Delivery)</option>
          </select>
        </div>
      </div>
      <div style="margin-top:14px;">
        <label style="font-size:13px;font-weight:600;color:var(--mp-ink);margin-bottom:8px;">Custom Fields to Capture <span style="font-weight:400;color:var(--mp-muted);font-size:12px;">(what the customer must specify)</span></label>
        <table class="mp-inner-table" id="custom-fields-table">
          <thead>
            <tr><th>Field Label</th><th>Type</th><th>Options</th><th>Required?</th><th style="width:40px;"></th></tr>
          </thead>
          <tbody>
            <?php
            $custom_fields = [];
            if(isset($custom_order_fields_json) && !empty($custom_order_fields_json)){
              $custom_fields = json_decode($custom_order_fields_json, true) ?: [];
            }
            foreach($custom_fields as $fi => $f):
            ?>
            <tr class="cf-row">
              <td><input type="text" name="cf_label[]" value="<?= htmlspecialchars($f['label'] ?? ''); ?>" placeholder="e.g. Size, Flavor"></td>
              <td>
                <select name="cf_type[]">
                  <option value="text" <?= ($f['type']??'')=='text'?'selected':''; ?>>Text</option>
                  <option value="textarea" <?= ($f['type']??'')=='textarea'?'selected':''; ?>>Long Text</option>
                  <option value="number" <?= ($f['type']??'')=='number'?'selected':''; ?>>Number</option>
                  <option value="select" <?= ($f['type']??'')=='select'?'selected':''; ?>>Dropdown</option>
                  <option value="date" <?= ($f['type']??'')=='date'?'selected':''; ?>>Date</option>
                  <option value="color" <?= ($f['type']??'')=='color'?'selected':''; ?>>Color</option>
                </select>
              </td>
              <td><input type="text" name="cf_options[]" value="<?= htmlspecialchars($f['options'] ?? ''); ?>" placeholder="For dropdown: Red, Blue, Green"></td>
              <td style="text-align:center;"><input type="hidden" name="cf_required[]" value="0"><input type="checkbox" name="cf_required[]" value="1" <?= ($f['required']??0)==1?'checked':''; ?> style="width:auto;"></td>
              <td><button type="button" class="btn-xs-danger" onclick="$(this).closest('tr').remove()"><i class="fa fa-trash"></i></button></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <button type="button" class="mp-table-add-btn" id="btn-add-cf"><i class="fa fa-plus"></i> Add Field</button>
      </div>
    </div>

    <!-- Serial / IMEI / Warranty tracking (only when no barcode table) -->
    <?php if(!$show_unit_table && mp_feature_enabled('serial_number_tracking')): ?>
    <div class="mp-form-group" style="margin-top:16px;">
      <label for="serial_number">Serial Number</label>
      <input class="mp-form-control" id="serial_number" name="serial_number" placeholder="Enter serial number" type="text" value="<?= isset($serial_number) ? htmlspecialchars($serial_number) : ''; ?>">
    </div>
    <?php endif; ?>
    <?php if(!$show_unit_table && mp_feature_enabled('imei_tracking')): ?>
    <div class="mp-form-group" style="margin-top:16px;">
      <label for="imei_number">IMEI Number</label>
      <input class="mp-form-control" id="imei_number" name="imei_number" placeholder="Enter IMEI" type="text" value="<?= isset($imei_number) ? htmlspecialchars($imei_number) : ''; ?>">
    </div>
    <?php endif; ?>
    <?php if(!$show_unit_table && mp_feature_enabled('warranty_tracking')): ?>
    <div class="mp-form-group" style="margin-top:16px;">
      <label for="warranty_months">Warranty (Months)</label>
      <input class="mp-form-control" id="warranty_months" name="warranty_months" placeholder="e.g. 12" type="number" min="0" value="<?= isset($warranty_months) ? htmlspecialchars($warranty_months) : ''; ?>">
    </div>
    <?php endif; ?>
    <?php if(!$show_unit_table && mp_feature_enabled('serial_number_tracking')): ?>
    <div class="mp-form-group full" style="margin-top:16px;">
      <label class="mp-check-label">
        <input type="checkbox" id="track_serial" name="track_serial" value="1" <?= ($track_serial==1) ? 'checked' : ''; ?>>
        Require Serial Number <span style="font-weight:400;color:var(--mp-muted);font-size:12px;">(POS will block checkout without it)</span>
      </label>
      <p class="mp-form-hint">Used for phones, electronics, appliances.</p>
    </div>
    <?php endif; ?>
    <?php if(!$show_unit_table && mp_feature_enabled('imei_tracking')): ?>
    <div class="mp-form-group full" style="margin-top:16px;">
      <label class="mp-check-label">
        <input type="checkbox" id="track_imei" name="track_imei" value="1" <?= ($track_imei==1) ? 'checked' : ''; ?>>
        Require IMEI <span style="font-weight:400;color:var(--mp-muted);font-size:12px;">(POS will block checkout without it)</span>
      </label>
      <p class="mp-form-hint">Required for mobile phones and cellular devices.</p>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if(recipe_module()): ?>
<!-- Recipe & Costing (item-only) -->
<div class="mp-card-form mp-item-only">
  <div class="mp-card-head"><h3>Recipe & Costing</h3></div>
  <div class="mp-card-body">
    <div class="mp-form-grid">
      <div class="mp-form-group">
        <label for="recipe_id">Recipe / BOM</label>
        <select class="mp-form-control select2" id="recipe_id" name="recipe_id" style="width:100%;">
          <option value="">-- No Recipe (Manual Costing) --</option>
          <?php foreach(($recipes_list ?? []) as $r): ?>
          <option value="<?= $r->id; ?>" data-cost="<?= $r->cost_per_unit ?? 0; ?>" data-yield="<?= $r->yield_qty; ?>" data-unit="<?= htmlspecialchars($r->yield_unit ?? ''); ?>" <?= (isset($recipe_id) && $recipe_id==$r->id)?'selected':''; ?>><?= htmlspecialchars($r->name); ?> (<?= number_format($r->yield_qty ?? 1,0); ?> <?= htmlspecialchars($r->yield_unit ?? 'piece'); ?>)</option>
          <?php endforeach; ?>
        </select>
        <p class="mp-form-hint">Select a recipe to auto-calculate cost price from ingredients</p>
      </div>
      <div class="mp-form-group">
        <label for="recipe_margin_pct">Recipe Margin (%)</label>
        <input class="mp-form-control" id="recipe_margin_pct" name="recipe_margin_pct" type="number" step="0.01" value="<?= isset($recipe_margin_pct) ? $recipe_margin_pct : '30'; ?>">
        <p class="mp-form-hint">Markup on top of recipe ingredient cost</p>
      </div>
      <div class="mp-form-group full">
        <div class="mp-recipe-stats">
          <div class="mp-recipe-stat">
            <div class="lbl">Recipe Cost</div>
            <div class="val" id="recipe-cost-display">0.00</div>
          </div>
          <div class="mp-recipe-stat">
            <div class="lbl">Suggested Sale Price</div>
            <div class="val green" id="recipe-sale-display">0.00</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Pricing & Stock -->
<div class="mp-card-form">
  <div class="mp-card-head"><h3>Pricing & Stock</h3></div>
  <div class="mp-card-body">
    <?php if(!$show_unit_table): ?>
    <!-- Simple pricing: all prices on one line -->
    <div class="mp-price-row" id="simple-pricing-section">
      <div class="mp-form-group">
        <label for="price">Base Price <span style="font-weight:400;color:var(--mp-muted);text-transform:none;">(before tax)</span></label>
        <input class="mp-form-control only_currency" id="price" name="price" type="text" value="<?php print store_number_format($price,0); ?>" placeholder="0.00">
        <span id="price_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group">
        <label for="purchase_price">Purchase Price <span style="font-weight:400;color:var(--mp-muted);text-transform:none;">(cost w/ tax)</span></label>
        <input class="mp-form-control only_currency" id="purchase_price" name="purchase_price" type="text" value="<?php print store_number_format($purchase_price,0); ?>" placeholder="0.00" readonly>
        <span id="purchase_price_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group">
        <label for="sales_price">Sale Price</label>
        <input class="mp-form-control only_currency" id="sales_price" name="sales_price" type="text" value="<?php print store_number_format($sales_price,0); ?>" placeholder="0.00">
        <span id="sales_price_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group">
        <label for="profit_margin">Margin (%)</label>
        <input class="mp-form-control" id="profit_margin" name="profit_margin" type="text" value="<?php print store_number_format($profit_margin,0); ?>" placeholder="e.g. 20">
        <span id="profit_margin_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group">
        <label for="mrp">MRP</label>
        <input class="mp-form-control only_currency" id="mrp" name="mrp" type="text" value="<?php print store_number_format($mrp,0); ?>" placeholder="0.00">
        <span id="mrp_msg" style="display:none" class="text-danger"></span>
      </div>
    </div>
    <!-- Opening Stock for simple items (no barcode/unit table) -->
    <div class="mp-form-group mp-item-only" style="margin-top:16px;max-width:240px;">
      <label for="simple_opening_stock">Opening Stock</label>
      <input class="mp-form-control only_currency" id="simple_opening_stock" name="simple_opening_stock" type="text" value="<?php print store_number_format($opening_stock,0); ?>" placeholder="0" onchange="$('#adjustment_qty').val(this.value);">
      <p class="mp-form-hint">Current stock: <?= store_number_format($stock ?? 0, 0); ?></p>
    </div>
    <?php else: ?>
    <!-- Hidden price fields when barcode table is visible -->
    <input type="hidden" id="price" name="price" value="<?php print store_number_format($price,0); ?>">
    <input type="hidden" id="purchase_price" name="purchase_price" value="<?php print store_number_format($purchase_price,0); ?>">
    <input type="hidden" id="sales_price" name="sales_price" value="<?php print store_number_format($sales_price,0); ?>">
    <input type="hidden" id="profit_margin" name="profit_margin" value="<?php print store_number_format($profit_margin,0); ?>">
    <input type="hidden" id="mrp" name="mrp" value="<?php print store_number_format($mrp,0); ?>">
    <?php endif; ?>

    <!-- Hidden fields synced from barcode row -->
    <input type="hidden" id="batch_lot" name="batch_lot" value="<?php print $batch_lot; ?>">
    <input type="hidden" id="adjustment_qty" name="adjustment_qty" value="<?php print $opening_stock; ?>">
    <input type="hidden" id="expire_date" name="expire_date" value="<?php print $expire_date; ?>">
    <input type="hidden" id="mfg_date" name="mfg_date" value="<?php print $mfg_date; ?>">

    <hr style="border:none;border-top:1px solid var(--mp-border);margin:20px 0;">

    <div class="mp-form-grid">
      <div class="mp-form-group">
        <label for="discount_type"><?= $this->lang->line('discount_type'); ?></label>
        <select class="mp-form-control select2" id="discount_type" name="discount_type" style="width:100%;">
          <option value="Percentage" <?php if($discount_type=='Percentage') echo 'selected'; ?>>Percentage (%)</option>
          <option value="Fixed" <?php if($discount_type=='Fixed') echo 'selected'; ?>>Fixed (<?= $CI->currency(); ?>)</option>
        </select>
        <span id="discount_type_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group">
        <label for="discount"><?= $this->lang->line('discount'); ?></label>
        <input class="mp-form-control only_currency" id="discount" name="discount" type="text" value="<?php print store_number_format($discount,0); ?>">
        <span id="discount_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group">
        <label for="tax_id"><?= $this->lang->line('tax'); ?> <span class="text-danger">*</span></label>
        <div class="mp-select-with-add">
          <select class="mp-form-control select2" id="tax_id" name="tax_id" required style="width:100%;">
            <?= get_tax_select_list($tax_id); ?>
          </select>
          <button type="button" class="mp-add-btn" data-toggle="modal" data-target="#tax_modal" title="Add Tax"><i class="fa fa-plus"></i></button>
        </div>
        <span id="tax_id_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group">
        <label for="tax_type"><?= $this->lang->line('tax_type'); ?> <span class="text-danger">*</span></label>
        <select class="mp-form-control select2" id="tax_type" name="tax_type" required style="width:100%;">
          <option value="Inclusive" <?php if($tax_type=='Inclusive') echo 'selected'; ?>>Inclusive</option>
          <option value="Exclusive" <?php if($tax_type=='Exclusive') echo 'selected'; ?>>Exclusive</option>
        </select>
        <span id="tax_type_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group mp-item-only">
        <label for="warehouse_id">Branch</label>
        <select class="mp-form-control select2" id="warehouse_id" name="warehouse_id" style="width:100%;">
          <?= get_warehouse_select_list($warehouse_id); ?>
        </select>
        <span id="warehouse_id_msg" style="display:none" class="text-danger"></span>
      </div>
      <!-- Service deposit fields -->
      <div class="mp-form-group mp-service-only">
        <label for="deposit_required">Deposit Required</label>
        <select class="mp-form-control select2" id="deposit_required" name="deposit_required" style="width:100%;">
          <option value="0" <?= ($deposit_required == '0') ? 'selected' : ''; ?>>No</option>
          <option value="1" <?= ($deposit_required == '1') ? 'selected' : ''; ?>>Yes</option>
        </select>
        <span id="deposit_required_msg" style="display:none" class="text-danger"></span>
      </div>
      <div class="mp-form-group mp-service-only">
        <label for="deposit_percent">Deposit %</label>
        <input class="mp-form-control only_currency" id="deposit_percent" name="deposit_percent" type="text" value="<?php print store_number_format($deposit_percent,0); ?>" placeholder="e.g. 50">
        <span id="deposit_percent_msg" style="display:none" class="text-danger"></span>
      </div>
    </div>
  </div>
</div>

<?php if($show_unit_table): ?>
<!-- Unit / Variant Details Table -->
<div class="mp-card-form mp-item-only" id="barcode_section">
  <div class="mp-card-head">
    <h3>Unit / Variant Details <span style="font-weight:400;color:var(--mp-muted);font-size:12px;">(Each row = one physical unit or variant. Prices & stock are set here.)</span></h3>
    <button type="button" class="mp-table-add-btn" onclick="addBarcodeRow()"><i class="fa fa-plus"></i> Add Unit</button>
  </div>
  <div class="mp-card-body">
    <?php if(mp_feature_enabled('serial_number_tracking') || mp_feature_enabled('imei_tracking')): ?>
    <div style="display:flex;gap:24px;margin-bottom:14px;">
      <?php if(mp_feature_enabled('serial_number_tracking')): ?>
      <label class="mp-check-label">
        <input type="checkbox" id="track_serial" name="track_serial" value="1" <?= ($track_serial??0)==1?'checked':''; ?>> Require Serial Number
      </label>
      <?php endif; ?>
      <?php if(mp_feature_enabled('imei_tracking')): ?>
      <label class="mp-check-label">
        <input type="checkbox" id="track_imei" name="track_imei" value="1" <?= ($track_imei??0)==1?'checked':''; ?>> Require IMEI
      </label>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <div style="overflow-x:auto;">
    <table class="mp-inner-table" id="barcode_table">
      <thead>
        <tr>
          <th>Barcode</th>
          <?php if(mp_feature_enabled('batch_tracking')): ?><th>Batch / Lot</th><?php endif; ?>
          <?php if(mp_feature_enabled('serial_number_tracking')): ?><th>Serial Number</th><?php endif; ?>
          <?php if(mp_feature_enabled('imei_tracking')): ?><th>IMEI Number</th><?php endif; ?>
          <th>Purchase Price</th>
          <th>Wholesale Price</th>
          <th>Retail Price (MRP)</th>
          <th>Qty in Stock</th>
          <?php if(mp_feature_enabled('expiry_tracking')): ?><th>Expiry</th><?php endif; ?>
          <?php if(mp_feature_enabled('mfg_tracking')): ?><th>MFG Date</th><?php endif; ?>
          <?php if(mp_feature_enabled('warranty_tracking')): ?><th>Warranty (Mo)</th><?php endif; ?>
          <th style="width:40px;"></th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($item_barcodes) && is_array($item_barcodes)):
          $bi = 0;
          foreach($item_barcodes as $brow): $bi++; ?>
        <tr id="barcode_row_<?=$bi;?>">
          <td><input type="text" name="barcode_barcode[]" value="<?=htmlspecialchars($brow->barcode);?>" placeholder="Scan or enter barcode"></td>
          <?php if(mp_feature_enabled('batch_tracking')): ?><td><input type="text" name="barcode_batch[]" value="<?=htmlspecialchars($brow->batch_lot);?>" placeholder="Batch / Lot"></td><?php endif; ?>
          <?php if(mp_feature_enabled('serial_number_tracking')): ?><td><input type="text" name="barcode_serial[]" value="<?=htmlspecialchars($brow->serial_number ?? '');?>" placeholder="Serial"></td><?php endif; ?>
          <?php if(mp_feature_enabled('imei_tracking')): ?><td><input type="text" name="barcode_imei[]" value="<?=htmlspecialchars($brow->imei_number ?? '');?>" placeholder="IMEI"></td><?php endif; ?>
          <td><input type="text" class="only_currency" name="barcode_pprice[]" value="<?=store_number_format($brow->purchase_price,0);?>" placeholder="0.00"></td>
          <td><input type="text" class="only_currency" name="barcode_sprice[]" value="<?=store_number_format($brow->sales_price,0);?>" placeholder="0.00"><div class="profit-indicator wholesale-profit text-success small"></div></td>
          <td><input type="text" class="only_currency" name="barcode_mrp[]" value="<?=store_number_format($brow->mrp,0);?>" placeholder="0.00"><div class="profit-indicator retail-profit text-success small"></div></td>
          <td><input type="text" class="only_currency" name="barcode_qty[]" value="<?=store_number_format($brow->qty,0);?>" placeholder="0"></td>
          <?php if(mp_feature_enabled('expiry_tracking')): ?><td><input type="date" name="barcode_expire_date[]" value="<?=htmlspecialchars($brow->expire_date ?? '');?>"></td><?php endif; ?>
          <?php if(mp_feature_enabled('mfg_tracking')): ?><td><input type="date" name="barcode_mfg_date[]" value="<?=htmlspecialchars($brow->mfg_date ?? '');?>"></td><?php endif; ?>
          <?php if(mp_feature_enabled('warranty_tracking')): ?><td><input type="text" name="barcode_warranty[]" value="<?=htmlspecialchars($brow->warranty_months ?? '');?>" placeholder="Months" style="min-width:60px;"></td><?php endif; ?>
          <td><button type="button" class="btn-xs-danger" onclick="removeBarcodeRow('<?=$bi;?>')"><i class="fa fa-trash"></i></button></td>
        </tr>
        <?php endforeach; else: ?>
        <tr id="barcode_row_1">
          <td><input type="text" name="barcode_barcode[]" value="<?=htmlspecialchars($custom_barcode);?>" placeholder="Scan or enter barcode"></td>
          <?php if(mp_feature_enabled('batch_tracking')): ?><td><input type="text" name="barcode_batch[]" value="<?=htmlspecialchars($batch_lot);?>" placeholder="Batch / Lot"></td><?php endif; ?>
          <?php if(mp_feature_enabled('serial_number_tracking')): ?><td><input type="text" name="barcode_serial[]" value="<?=htmlspecialchars($serial_number);?>" placeholder="Serial"></td><?php endif; ?>
          <?php if(mp_feature_enabled('imei_tracking')): ?><td><input type="text" name="barcode_imei[]" value="<?=htmlspecialchars($imei_number);?>" placeholder="IMEI"></td><?php endif; ?>
          <td><input type="text" class="only_currency" name="barcode_pprice[]" value="<?=store_number_format($purchase_price,0);?>" placeholder="0.00"></td>
          <td><input type="text" class="only_currency" name="barcode_sprice[]" value="<?=store_number_format($sales_price,0);?>" placeholder="0.00"><div class="profit-indicator wholesale-profit text-success small"></div></td>
          <td><input type="text" class="only_currency" name="barcode_mrp[]" value="<?=store_number_format($mrp,0);?>" placeholder="0.00"><div class="profit-indicator retail-profit text-success small"></div></td>
          <td><input type="text" class="only_currency" name="barcode_qty[]" value="<?=store_number_format($opening_stock,0);?>" placeholder="0"></td>
          <?php if(mp_feature_enabled('expiry_tracking')): ?><td><input type="date" name="barcode_expire_date[]" value="<?=htmlspecialchars($expire_date);?>"></td><?php endif; ?>
          <?php if(mp_feature_enabled('mfg_tracking')): ?><td><input type="date" name="barcode_mfg_date[]" value="<?=htmlspecialchars($mfg_date);?>"></td><?php endif; ?>
          <?php if(mp_feature_enabled('warranty_tracking')): ?><td><input type="text" name="barcode_warranty[]" value="<?=htmlspecialchars($warranty_months);?>" placeholder="Months" style="min-width:60px;"></td><?php endif; ?>
          <td><button type="button" class="btn-xs-danger" onclick="removeBarcodeRow('1')"><i class="fa fa-trash"></i></button></td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>
    <div id="barcode_table_msg" style="display:none; margin-top:10px; padding:10px 14px; background:rgba(245,158,11,.1); border-radius:8px; color:#92400E; font-size:13px;">
      <i class="fa fa-exclamation-circle"></i> <span id="barcode_table_msg_text">Please fill Purchase Price, Wholesale Price, and Retail Price for at least the first unit row.</span>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if(mp_feature_enabled('bundles')): ?>
<!-- Variant Table -->
<div class="mp-card-form mp-item-only variant_div" style="display:none;">
  <div class="mp-card-head">
    <h3>Variants</h3>
    <div style="display:flex;gap:10px;align-items:center;">
      <div class="mp-select-with-add">
        <input type="text" class="mp-form-control" placeholder="Search Variant" id="variant_search" style="width:220px;">
        <button type="button" class="mp-add-btn" data-toggle="modal" data-target="#variant-modal" title="Add New Variant"><i class="fa fa-plus"></i></button>
      </div>
      <button type="button" class="mp-qa-btn purple" id="btn_matrix_generator" data-toggle="modal" data-target="#matrix_generator_modal">
        <i class="fa fa-th"></i> <?= $this->lang->line('variant_matrix_generator'); ?>
      </button>
    </div>
  </div>
  <div class="mp-card-body">
    <div style="overflow-x:auto;">
    <table class="mp-inner-table" id="variant_table" style="min-width:900px;">
      <thead>
        <tr>
          <th style="width:15%"><?= $this->lang->line('variant_name'); ?></th>
          <th style="width:8%"><?= $this->lang->line('image'); ?></th>
          <th style="width:10%"><?= $this->lang->line('sku'); ?></th>
          <th style="width:10%"><?= $this->lang->line('barcode'); ?></th>
          <th style="width:10%"><?= $this->lang->line('price'); ?>(<?= $CI->currency(); ?>)</th>
          <th style="width:10%"><?= $this->lang->line('purchase_price'); ?>(<?= $CI->currency(); ?>)</th>
          <th style="width:10%"><?= $this->lang->line('profit_margin'); ?></th>
          <th style="width:10%"><?= $this->lang->line('sales_price'); ?>(<?= $CI->currency(); ?>)</th>
          <th style="width:10%"><?= $this->lang->line('mrp'); ?>(<?= $CI->currency(); ?>)</th>
          <th style="width:10%"><?= $this->lang->line('opening_stock'); ?></th>
          <th style="width:5%"><?= $this->lang->line('action'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php if($item_group!='Single' && !empty($q_id)):
          echo $this->items->get_variants_list_in_row($q_id);
        endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Product Image -->
<div class="mp-card-form">
  <div class="mp-card-head"><h3>Product Image</h3></div>
  <div class="mp-card-body">
    <div class="mp-upload-box" id="mp_upload_box" onclick="document.getElementById('item_image').click()">
      <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
      <strong>Click to upload image</strong>
      <span>PNG, JPG or WEBP up to 1MB</span>
    </div>
    <input type="file" name="item_image" id="item_image" style="position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;" accept="image/png,image/jpeg,image/webp">
    <div id="image_preview_wrap" style="margin-top:12px;display:none;">
      <img id="image_preview" src="" style="max-width:120px;max-height:120px;border-radius:8px;border:1px solid var(--mp-border);">
      <button type="button" class="btn-xs-danger" onclick="removeImagePreview()" style="margin-left:8px;"><i class="fa fa-trash"></i></button>
    </div>
    <?php if(!empty($q_id) && isset($item_image) && !empty($item_image)): ?>
    <div style="margin-top:12px;">
      <img src="<?= base_url($item_image); ?>" style="max-width:120px;max-height:120px;border-radius:8px;border:1px solid var(--mp-border);">
    </div>
    <?php endif; ?>
    <p class="mp-form-hint" style="color:var(--mp-danger);margin-top:8px;">Max Width/Height: 1500px * 1500px &amp; Size: 1MB</p>
    <span id="item_image_msg" style="display:none;" class="text-danger"></span>
  </div>
</div>

<?= form_close(); ?>
</div><!-- /.box.mp-items-box -->

<!-- Modals -->
<?php include "modals/modal_brand.php"; ?>
<?php include "modals/modal_category.php"; ?>
<?php include "modals/modal_unit.php"; ?>
<?php include "modals/modal_tax.php"; ?>
<?php include "modals/modal_variant.php"; ?>

<!-- Variant Matrix Generator Modal -->
<div class="modal fade" id="matrix_generator_modal" tabindex="-1" role="dialog" aria-labelledby="matrixModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background:#7C3AED;color:#fff;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="matrixModalLabel"><i class="fa fa-th"></i> <?= $this->lang->line('variant_matrix_generator'); ?></h4>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
          <i class="fa fa-info-circle"></i> Pick the values for each attribute flagged on this product. MartPoint will create every combination as a child SKU.
        </div>
        <div class="row" id="matrix_attribute_container"></div>
        <div id="matrix_preview" class="text-muted small" style="margin-top:10px;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-purple" id="btn_generate_matrix" style="background:#7C3AED;color:#fff;"><i class="fa fa-magic"></i> Generate Rows</button>
      </div>
    </div>
  </div>
</div>

<!-- Item form initialization & helpers -->
<script type="text/javascript">
// Item/Service mode switching
function setItemTypeMode(isService){
  if(isService){
    $('body').addClass('mp-mode-service');
    // When service is selected, force item_group to Single (services don't have variants)
    $("#item_group").val("Single").trigger("change");
    $("#item_group_group").addClass('mp-hidden');
  } else {
    $('body').removeClass('mp-mode-service');
    <?php if($child_bit==1 || !empty($item_name)): ?>
    $("#item_group_group").addClass('mp-hidden');
    <?php else: ?>
    $("#item_group_group").removeClass('mp-hidden');
    <?php endif; ?>
  }
  // Refresh select2 on any newly visible/hidden selects
  setTimeout(function(){ $('.select2').each(function(){ var $s=$(this); try{ if($s.data('select2')){ $s.select2('destroy'); } $s.select2(); }catch(e){} }); }, 50);
}

function toggleCommissionValue() {
  var type = $('#commission_type').val();
  if (type === 'none' || type === '') {
    $('#commission_value_wrap').hide();
    $('#commission_value').val('0');
  } else {
    $('#commission_value_wrap').show();
  }
}

$(function(){
  $("#discount_type").val('<?=$discount_type; ?>');
  <?php if(isset($q_id)): ?>
  $("#store_id").attr('readonly',true);
  <?php endif; ?>
  $("#item_group").val("<?=$item_group;?>").trigger("change");

  <?php if(!empty($item_name)): ?>
  $("#hidden_rowcount").val($("#variant_table tbody tr").length);
  if(typeof calculate_purchase_price_of_all_row === 'function'){
    calculate_purchase_price_of_all_row();
    calculate_sales_price_of_all_row();
  }
  <?php endif; ?>

  <?php if($child_bit==1 || !empty($item_name)): ?>
  $("#item_group_group").addClass('mp-hidden');
  <?php endif; ?>

  // Initialize item/service mode
  var initiallyService = <?= $is_service ? 'true' : 'false'; ?>;
  setItemTypeMode(initiallyService);

  // Toggle handler
  $('input[name="item_type"]').on('change', function(){
    var isService = ($(this).val() === 'service');
    setItemTypeMode(isService);
  });

  toggleCommissionValue();

  // Image upload preview
  $('#item_image').on('change', function(){
    if(this.files && this.files[0]){
      var file = this.files[0];
      if(file.size > 1048576){
        toastr["warning"]("Image size must be less than 1MB.");
        $(this).val('');
        return;
      }
      var reader = new FileReader();
      reader.onload = function(e){
        $('#image_preview').attr('src', e.target.result);
        $('#image_preview_wrap').show();
      };
      reader.readAsDataURL(file);
    }
  });
});

function removeImagePreview(){
  $('#item_image').val('');
  $('#image_preview_wrap').hide();
}
</script>

<script>
var barcodeRowIndex = <?= (!empty($item_barcodes) && is_array($item_barcodes)) ? count($item_barcodes) : 1; ?>;
function addBarcodeRow(){
  barcodeRowIndex++;
  var html = '<tr id="barcode_row_'+barcodeRowIndex+'">'+
    '<td><input type="text" name="barcode_barcode[]" placeholder="Scan or enter barcode"></td>'+
    <?php if(mp_feature_enabled('batch_tracking')): ?>'<td><input type="text" name="barcode_batch[]" placeholder="Batch / Lot"></td>'+
    <?php endif; ?><?php if(mp_feature_enabled('serial_number_tracking')): ?>'<td><input type="text" name="barcode_serial[]" placeholder="Serial"></td>'+
    <?php endif; ?><?php if(mp_feature_enabled('imei_tracking')): ?>'<td><input type="text" name="barcode_imei[]" placeholder="IMEI"></td>'+
    <?php endif; ?>'<td><input type="text" class="only_currency" name="barcode_pprice[]" placeholder="0.00"></td>'+
    '<td><input type="text" class="only_currency" name="barcode_sprice[]" placeholder="0.00"><div class="profit-indicator wholesale-profit text-success small"></div></td>'+
    '<td><input type="text" class="only_currency" name="barcode_mrp[]" placeholder="0.00"><div class="profit-indicator retail-profit text-success small"></div></td>'+
    '<td><input type="text" class="only_currency" name="barcode_qty[]" placeholder="0"></td>'+
    <?php if(mp_feature_enabled('expiry_tracking')): ?>'<td><input type="date" name="barcode_expire_date[]"></td>'+
    <?php endif; ?><?php if(mp_feature_enabled('mfg_tracking')): ?>'<td><input type="date" name="barcode_mfg_date[]"></td>'+
    <?php endif; ?><?php if(mp_feature_enabled('warranty_tracking')): ?>'<td><input type="text" name="barcode_warranty[]" placeholder="Months" style="min-width:60px;"></td>'+
    <?php endif; ?>'<td><button type="button" class="btn-xs-danger" onclick="removeBarcodeRow('+barcodeRowIndex+')"><i class="fa fa-trash"><\/i><\/button><\/td>'+
    '<\/tr>';
  $('#barcode_table tbody').append(html);
}
function removeBarcodeRow(id){
  $('#barcode_row_'+id).remove();
}

$(function(){
  // Custom Order field builder
  $('#accept_custom_order').on('change', function(){
    if($(this).is(':checked')){
      $('#custom-order-options').slideDown(200);
    } else {
      $('#custom-order-options').slideUp(200);
    }
  });
  // Trigger on load so the section is visible if the item already has accept_custom_order=1
  $('#accept_custom_order').trigger('change');
  $('#btn-add-cf').on('click', function(){
    var html = '<tr class="cf-row">'+
      '<td><input type="text" name="cf_label[]" placeholder="e.g. Size, Flavor"></td>'+
      '<td><select name="cf_type[]"><option value="text">Text</option><option value="textarea">Long Text</option><option value="number">Number</option><option value="select">Dropdown</option><option value="date">Date</option><option value="color">Color</option></select></td>'+
      '<td><input type="text" name="cf_options[]" placeholder="For dropdown: Red, Blue, Green"></td>'+
      '<td style="text-align:center;"><input type="hidden" name="cf_required[]" value="0"><input type="checkbox" name="cf_required[]" value="1" style="width:auto;"></td>'+
      '<td><button type="button" class="btn-xs-danger" onclick="$(this).closest(\'tr\').remove()"><i class="fa fa-trash"></i></button></td>'+
      '</tr>';
    $('#custom-fields-table tbody').append(html);
  });

  <?php if(recipe_module()): ?>
  // Recipe cost calculator
  function updateRecipeCost(){
    var $opt = $('#recipe_id option:selected');
    var cost = parseFloat($opt.data('cost')) || 0;
    var margin = parseFloat($('#recipe_margin_pct').val()) || 0;
    var sale = cost > 0 ? (cost * (1 + margin / 100)) : 0;
    $('#recipe-cost-display').text(cost.toFixed(2));
    $('#recipe-sale-display').text(sale.toFixed(2));
    if($('#simple-pricing-section').is(':visible') && cost > 0){
      $('#purchase_price').val(cost.toFixed(2));
      $('#price').val(cost.toFixed(2));
      if(sale > 0){
        $('#sales_price').val(sale.toFixed(2));
      }
      $('#price').trigger('change');
      $('#sales_price').trigger('change');
    }
  }
  $('#recipe_id').on('change', updateRecipeCost);
  $('#recipe_margin_pct').on('input', updateRecipeCost);
  updateRecipeCost();
  <?php endif; ?>
});
</script>

<!-- Variant Matrix Generator JS -->
<script type="text/javascript">
var base_url_js = "<?= base_url(); ?>";
var attributeMap = {};

function toggleAttributeTypesBox(){
  var ig = $("#item_group").val();
  if(ig == 'Variants'){
    $("#attribute_types_box").removeClass('mp-hidden');
  } else {
    $("#attribute_types_box").addClass('mp-hidden');
  }
}

function previewVariantImage(input, rowcount){
  if(input.files && input.files[0]){
    var reader = new FileReader();
    reader.onload = function(e){
      var img = $('#variant_preview_' + rowcount);
      if(img.length === 0){
        $(input).closest('td').prepend('<img id="variant_preview_' + rowcount + '" src="' + e.target.result + '" style="max-width:48px; max-height:48px; border-radius:4px; margin-bottom:6px; cursor:pointer;" onclick="$(\'#variant_image_' + rowcount + '\').click()">');
      } else {
        img.attr('src', e.target.result);
      }
      $(input).closest('td').find('.variant-choose-link').text('Change image');
    };
    reader.readAsDataURL(input.files[0]);
  }
}

$(function(){
  $("#item_group").on('change', function(){
    toggleAttributeTypesBox();
  });
  toggleAttributeTypesBox();
  $("#item_group").trigger('change');
});

function loadMatrixAttributeMap(){
  $.get(base_url_js+'items/get_variant_attribute_map', function(res){
    if(res.status == 'success'){
      attributeMap = res.map || {};
      renderMatrixAttributeRows();
    }
  }, 'json');
}

function renderMatrixAttributeRows(){
  var container = $("#matrix_attribute_container");
  container.html('');
  var selectedTypes = $("#attribute_types").val() || [];
  if(selectedTypes.length == 0){
    container.html('<div class="col-md-12"><div class="alert alert-warning">Please flag attribute types for this product first (Size, Colour, Length).</div></div>');
    return;
  }
  selectedTypes.forEach(function(type){
    var vals = attributeMap[type] || [];
    if(vals.length == 0) return;
    var html = '<div class="col-md-4">';
    html += '<div class="form-group">';
    html += '<label class="text-capitalize">'+type+'</label>';
    html += '<select class="form-control matrix-attribute-values" data-type="'+type+'" multiple style="min-height:120px;">';
    vals.forEach(function(v){ html += '<option value="'+v+'">'+v+'</option>'; });
    html += '</select></div></div>';
    container.append(html);
  });
  updateMatrixPreview();
}

function updateMatrixPreview(){
  var combos = 1;
  var active = false;
  $(".matrix-attribute-values").each(function(){
    var vals = $(this).val() || [];
    if(vals.length > 0){
      combos *= vals.length;
      active = true;
    }
  });
  if(active){
    $("#matrix_preview").html('<b>'+combos+'</b> combinations will be generated.');
  } else {
    $("#matrix_preview").html('');
  }
}

$(document).on('change', '.matrix-attribute-values', updateMatrixPreview);

$(function(){
  $("#matrix_generator_modal").on('shown.bs.modal', function(){
    loadMatrixAttributeMap();
  });

  $("#btn_generate_matrix").on('click', function(){
    var attribute_map = {};
    var anySelected = false;
    $(".matrix-attribute-values").each(function(){
      var type = $(this).data('type');
      var vals = $(this).val() || [];
      if(vals.length > 0){
        attribute_map[type] = vals;
        anySelected = true;
      }
    });
    if(!anySelected){
      alert('Please select at least one value for each attribute.');
      return;
    }
    var starting_rowcount = $("#variant_table tbody tr").length + 1;
    $("#btn_generate_matrix").attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating...');
    $.post(base_url_js+'items/generate_matrix_rows', {
      attribute_map: JSON.stringify(attribute_map),
      base_item_name: $("#item_name").val() || '',
      base_sku: $("#sku").val() || '',
      base_price: $("#price").val() || '0',
      base_sales_price: $("#sales_price").val() || '0',
      base_mrp: $("#mrp").val() || '0',
      starting_rowcount: starting_rowcount,
      '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
    }, function(res){
      $("#btn_generate_matrix").attr('disabled', false).html('<i class="fa fa-magic"></i> Generate Rows');
      if(res.status == 'success' && res.html){
        $("#variant_table tbody").append(res.html);
        $("#hidden_rowcount").val($("#variant_table tbody tr").length);
        if(typeof calculate_purchase_price_of_all_row === 'function') calculate_purchase_price_of_all_row();
        if(typeof calculate_sales_price_of_all_row === 'function') calculate_sales_price_of_all_row();
        $("#matrix_generator_modal").modal('hide');
      } else {
        alert('Could not generate matrix rows.');
      }
    }, 'json');
  });
});
</script>

<!-- Make sidebar menu highlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
