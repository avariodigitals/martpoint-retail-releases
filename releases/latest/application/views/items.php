<!DOCTYPE html>
<html>
   <head>
  <!-- TABLES CSS CODE -->
  <?php include"comman/code_css.php"; ?>
  
  <!-- </copy> -->  
  </head>
   <body class="hold-transition skin-blue  sidebar-mini">
      <!-- **********************MODALS***************** -->
       <?php include"modals/modal_brand.php"; ?>
       <?php include"modals/modal_category.php"; ?>
       <?php include"modals/modal_unit.php"; ?>
       <?php include"modals/modal_tax.php"; ?>
       <!-- **********************MODALS END***************** -->
       
      <div class="wrapper">
      <?php include"sidebar.php"; ?>
      <?php
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
         
         //$variants_selected='';
         $item_group='Single';

         $discount='';
          $discount_type='Percentage';

          
          $opening_stock_readonly='';


         $item_code = get_init_code('item');

         }
         else{
            $opening_stock_readonly = 'readonly';
         }
         //For new or update
         $opening_stock ='0';

         
         
         
         ?>
      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- **********************MODALS***************** -->
      <?php include"modals/modal_variant.php"; ?>
      <!-- **********************MODALS END***************** -->

         <!-- Content Header (Page header) -->
         <section class="content-header">
            <h1>
               <?= $page_title;?>
               <small>Add/Update Items</small>
            </h1>
            <ol class="breadcrumb">
               <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i>Home</a></li>
               <li><a href="<?php echo $base_url; ?>items"><?= $this->lang->line('items_list'); ?></a></li>
               <li class="active"><?= $page_title;?></li>
            </ol>
         </section>
         <!-- Main content -->
         <section class="content">
            <div class="row">
               <!-- ********** ALERT MESSAGE START******* -->
               <?php include"comman/code_flashdata.php"; ?>
               <!-- ********** ALERT MESSAGE END******* -->
               <!-- right column -->
               <div class="col-md-12">
                  <!-- Horizontal Form -->
                  <div class="box box-primary ">
                     
                      <?= form_open('#', array('class' => 'form', 'id' => 'items-form', 'enctype'=>'multipart/form-data', 'method'=>'POST'));?>
                        <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
                        <div class="box-body">

                          <div class="row">
                             <!-- Store Code -->
                              <?php /*if(store_module() && is_admin()) {$this->load->view('store/store_code',array('show_store_select_box_1'=>true,'store_id'=>$store_id)); }else{*/
                                echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>";
                              /*}*/ ?>
                              <!-- Store Code end -->
                          </div>

                           <div class="row">
                              <div class="form-group col-md-4">
                                 <label for="item_code"><?= $this->lang->line('item_code'); ?><span class="text-danger">*</span></label>
                                 <input type="text" class="form-control" id="item_code" name="item_code" placeholder="" value="<?php print $item_code; ?>" >
                                 <span id="item_code_msg" style="display:none" class="text-danger"></span>
                              </div>
                           </div>
                           <div class="row">
                              <div class="form-group col-md-4">
                                 <label for="item_name"><?= $this->lang->line('item_name'); ?><span class="text-danger">*</span></label>
                                 <input type="text" autofocus="" class="form-control" id="item_name" name="item_name" placeholder="" value="<?php print $item_name; ?>" >
                                 <span id="item_name_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="brand_id"><?= $this->lang->line('brand'); ?></label>
                                 <div class="input-group">
                                 <select class="form-control select2" id="brand_id" name="brand_id"  style="width: 100%;"  >
                                    <option value="">-Select-</option>
                                    <?= get_brands_select_list($brand_id);  ?>
                                 </select>
                                 <span class="input-group-addon pointer" data-toggle="modal" data-target="#brand_modal" title="Add Customer"><i class="fa fa-plus-square-o text-primary fa-lg"></i></span>
                                    </div>
                                 <span id="brand_id_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="category_id"><?= $this->lang->line('category'); ?><span class="text-danger">*</span></label>
                                 <div class="input-group">
                                 <select class="form-control select2" id="category_id" name="category_id"  style="width: 100%;"  >
                                    <option value="">-Select-</option>
                                    <?= get_categories_select_list($category_id);  ?>
                                 </select>
                                 <span class="input-group-addon pointer" data-toggle="modal" data-target="#category_modal" title="Add Customer"><i class="fa fa-plus-square-o text-primary fa-lg"></i></span>
                                    </div>
                                 <span id="category_id_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="item_group"><?= $this->lang->line('item_group'); ?><span class="text-danger">*</span></label>
                                 <select class="form-control select2" id="item_group" name="item_group"  style="width: 100%;" >
                                  
                                    <option  value="Single">Single</option>
                                    <?php if(mp_feature_enabled('bundles')) { ?><option  value="Variants">Variants</option><?php } ?>
                                 </select>
                                 <span id="item_group_msg" style="display:none" class="text-danger"></span>
                                 
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="unit_id"><?= $this->lang->line('unit'); ?><span class="text-danger">*</span></label>
                                 <div class="input-group">
                                 <select class="form-control select2" id="unit_id" name="unit_id"  style="width: 100%;"  >
                                    <?= get_units_select_list($unit_id);  ?>
                                 </select>
                                 <?php if(mp_feature_enabled('multi_unit_inventory')) { ?>
                                 <span class="input-group-addon pointer" data-toggle="modal" data-target="#unit_modal" title="Add Customer"><i class="fa fa-plus-square-o text-primary fa-lg"></i></span>
                                 <?php } ?>
                                    </div>
                                 <span id="unit_id_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="sku"><?= $this->lang->line('sku'); ?></label>
                                 <input type="text" class="form-control" id="sku" name="sku" placeholder="" value="<?php print $sku; ?>" >
                                 <span id="sku_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="hsn"><?= $this->lang->line('hsn'); ?></label>
                                 <input type="text" class="form-control" id="hsn" name="hsn" placeholder="" value="<?php print $hsn; ?>" >
                                 <span id="hsn_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="alert_qty" ><?= $this->lang->line('alert_qty'); ?></label>
                                 <input type="number" class="form-control no_special_char" id="alert_qty" name="alert_qty" placeholder="" min="0"  value="<?php print $alert_qty; ?>" >
                                 <span id="alert_qty_msg" style="display:none" class="text-danger"></span>
                              </div>
                              
                              <div class="form-group col-md-4">
                                 <label for="seller_points" ><?= $this->lang->line('seller_points'); ?></label>
                                 <input type="text" class="form-control only_currency" id="seller_points" name="seller_points" placeholder=""  value="<?php print $seller_points; ?>" >
                                 <span id="seller_points_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="custom_barcode" ><?= $this->lang->line('barcode'); ?></label>
                                 <input type="text" class="form-control " id="custom_barcode" name="custom_barcode" placeholder=""  value="<?php print $custom_barcode; ?>" >
                                 <span id="custom_barcode_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="description" ><?= $this->lang->line('description'); ?></label>
                                 <textarea type="text" class="form-control" id="description" name="description" placeholder=""><?php print $description; ?></textarea>
                                 <span id="description_msg" style="display:none" class="text-danger"></span>
                              </div>

                              <div class="form-group col-md-4">
                                 <label for="item_image"><?= $this->lang->line('select_image'); ?></label>
                                 <input type="file" name="item_image" id="item_image">
                                 <span id="item_image_msg" style="display:block;" class="text-danger">Max Width/Height: 1000px * 1000px & Size: 1MB </span>
                              </div>

                              <?php
                              // Show barcode table when batch tracking OR any unit tracking is enabled
                              $show_unit_table = mp_feature_enabled('batch_tracking') || mp_feature_enabled('serial_number_tracking') || mp_feature_enabled('imei_tracking') || mp_feature_enabled('warranty_tracking');
                              ?>

                              <?php if(!$show_unit_table && mp_feature_enabled('serial_number_tracking')) { ?>
                              <div class="form-group col-md-4">
                                 <label for="serial_number">Serial Number</label>
                                 <input type="text" class="form-control" id="serial_number" name="serial_number" placeholder="Enter serial number" value="<?= isset($serial_number) ? $serial_number : ''; ?>" >
                                 <span id="serial_number_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <?php } ?>

                              <?php if(!$show_unit_table && mp_feature_enabled('imei_tracking')) { ?>
                              <div class="form-group col-md-4">
                                 <label for="imei_number">IMEI Number</label>
                                 <input type="text" class="form-control" id="imei_number" name="imei_number" placeholder="Enter IMEI" value="<?= isset($imei_number) ? $imei_number : ''; ?>" >
                                 <span id="imei_number_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <?php } ?>

                              <?php if(!$show_unit_table && mp_feature_enabled('warranty_tracking')) { ?>
                              <div class="form-group col-md-4">
                                 <label for="warranty_months">Warranty (Months)</label>
                                 <input type="number" class="form-control" id="warranty_months" name="warranty_months" placeholder="e.g. 12" min="0" value="<?= isset($warranty_months) ? $warranty_months : ''; ?>" >
                                 <span id="warranty_months_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <?php } ?>

                              <div class="form-group col-md-4">
                                 <label for="not_for_sale">
                                    <input type="checkbox" id="not_for_sale" name="not_for_sale" value="1" <?= (isset($not_for_sale) && $not_for_sale==1) ? 'checked' : ''; ?>>
                                    Not for Sale <small class="text-muted">(Consumable / Raw Material)</small>
                                 </label>
                                 <p class="text-muted" style="font-size:11px;margin-top:4px;">Hide from POS. Use in treatments, production, etc.</p>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="consumable_unit">Unit Label <small class="text-muted">(for consumables)</small></label>
                                 <input type="text" class="form-control" id="consumable_unit" name="consumable_unit" placeholder="e.g. ml, bottle, pump, sachet" value="<?= isset($consumable_unit) ? htmlspecialchars($consumable_unit) : ''; ?>">
                                 <span id="consumable_unit_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="accept_custom_order">
                                    <input type="checkbox" id="accept_custom_order" name="accept_custom_order" value="1" <?= (isset($accept_custom_order) && $accept_custom_order==1) ? 'checked' : ''; ?>>
                                    Accept Custom Orders <small class="text-muted">(Made to Order)</small>
                                 </label>
                                 <p class="text-muted" style="font-size:11px;margin-top:4px;">Furniture, cakes, tailored items. Capture specs at POS.</p>
                              </div>
                              <div id="custom-order-options" class="col-md-12" style="<?= (isset($accept_custom_order) && $accept_custom_order==1) ? '' : 'display:none;'; ?>">
                                <div class="box box-default" style="border-top:3px solid #F39C12;">
                                  <div class="box-header"><h3 class="box-title" style="font-size:14px;"><i class="fa fa-pencil-square-o"></i> Custom Order Settings</h3></div>
                                  <div class="box-body">
                                    <div class="row">
                                      <div class="form-group col-md-3">
                                        <label><input type="checkbox" id="requires_quote" name="requires_quote" value="1" <?= (isset($requires_quote) && $requires_quote==1) ? 'checked' : ''; ?>> Requires Quote</label>
                                        <p class="text-muted" style="font-size:10px;">Price set by staff after taking order</p>
                                      </div>
                                      <div class="form-group col-md-3">
                                        <label><input type="checkbox" id="requires_deposit" name="requires_deposit" value="1" <?= (isset($requires_deposit) && $requires_deposit==1) ? 'checked' : ''; ?>> Requires Deposit</label>
                                        <p class="text-muted" style="font-size:10px;">Customer pays deposit before production starts</p>
                                      </div>
                                      <div class="form-group col-md-3">
                                        <label for="workflow_template_key">Workflow</label>
                                        <select class="form-control" id="workflow_template_key" name="workflow_template_key">
                                          <option value="standard" <?= (isset($workflow_template_key) && $workflow_template_key=='standard') ? 'selected' : ''; ?>>Standard (New → Production → Ready → Delivered)</option>
                                          <option value="food" <?= (isset($workflow_template_key) && $workflow_template_key=='food') ? 'selected' : ''; ?>>Food / Bakery (New → Confirmed → Baking → Ready → Picked Up)</option>
                                          <option value="furniture" <?= (isset($workflow_template_key) && $workflow_template_key=='furniture') ? 'selected' : ''; ?>>Furniture (Quote → Deposit → Build → QC → Delivery)</option>
                                        </select>
                                      </div>
                                    </div>
                                    <hr style="margin:10px 0;">
                                    <label>Custom Fields to Capture <small class="text-muted">(what the customer must specify)</small></label>
                                    <table class="table table-bordered table-condensed" id="custom-fields-table">
                                      <thead class="bg-gray"><tr><th>Field Label</th><th>Type</th><th>Options</th><th>Required?</th><th style="width:40px;"></th></tr></thead>
                                      <tbody>
                                        <?php
                                        $custom_fields = [];
                                        if(isset($custom_order_fields_json) && !empty($custom_order_fields_json)){
                                            $custom_fields = json_decode($custom_order_fields_json, true) ?: [];
                                        }
                                        foreach($custom_fields as $fi => $f):
                                        ?>
                                        <tr class="cf-row">
                                          <td><input type="text" class="form-control input-sm" name="cf_label[]" value="<?= htmlspecialchars($f['label'] ?? ''); ?>" placeholder="e.g. Size, Flavor"></td>
                                          <td>
                                            <select class="form-control input-sm" name="cf_type[]">
                                              <option value="text" <?= ($f['type']??'')=='text'?'selected':''; ?>>Text</option>
                                              <option value="textarea" <?= ($f['type']??'')=='textarea'?'selected':''; ?>>Long Text</option>
                                              <option value="number" <?= ($f['type']??'')=='number'?'selected':''; ?>>Number</option>
                                              <option value="select" <?= ($f['type']??'')=='select'?'selected':''; ?>>Dropdown</option>
                                              <option value="date" <?= ($f['type']??'')=='date'?'selected':''; ?>>Date</option>
                                              <option value="color" <?= ($f['type']??'')=='color'?'selected':''; ?>>Color</option>
                                            </select>
                                          </td>
                                          <td><input type="text" class="form-control input-sm" name="cf_options[]" value="<?= htmlspecialchars($f['options'] ?? ''); ?>" placeholder="For dropdown: Red, Blue, Green"></td>
                                          <td class="text-center" style="padding-top:8px;"><input type="checkbox" name="cf_required[]" value="1" <?= ($f['required']??0)==1?'checked':''; ?>></td>
                                          <td><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest('tr').remove()"><i class="fa fa-trash"></i></button></td>
                                        </tr>
                                        <?php endforeach; ?>
                                      </tbody>
                                    </table>
                                    <button type="button" class="btn btn-xs btn-success" id="btn-add-cf"><i class="fa fa-plus"></i> Add Field</button>
                                  </div>
                                </div>
                              </div>

                              <?php if(recipe_module()): ?>
                              <!-- RECIPE LINKING -->
                              <div class="col-md-12">
                                <div class="box box-default" style="border-top:3px solid #00C851;">
                                  <div class="box-header"><h3 class="box-title" style="font-size:14px;"><i class="fa fa-book"></i> Recipe & Costing</h3></div>
                                  <div class="box-body">
                                    <div class="row">
                                      <div class="form-group col-md-4">
                                        <label for="recipe_id">Recipe / BOM</label>
                                        <select class="form-control select2" id="recipe_id" name="recipe_id">
                                          <option value="">-- No Recipe (Manual Costing) --</option>
                                          <?php foreach(($recipes_list ?? []) as $r): ?>
                                          <option value="<?= $r->id; ?>" data-cost="<?= $r->cost_per_unit ?? 0; ?>" data-yield="<?= $r->yield_qty; ?>" data-unit="<?= htmlspecialchars($r->yield_unit ?? ''); ?>" <?= (isset($recipe_id) && $recipe_id==$r->id)?'selected':''; ?>><?= htmlspecialchars($r->name); ?> (<?= number_format($r->yield_qty ?? 1,0); ?> <?= htmlspecialchars($r->yield_unit ?? 'piece'); ?>)</option>
                                          <?php endforeach; ?>
                                        </select>
                                        <p class="text-muted" style="font-size:10px;margin-top:4px;">Select a recipe to auto-calculate cost price from ingredients</p>
                                      </div>
                                      <div class="form-group col-md-3">
                                        <label for="recipe_margin_pct">Recipe Margin (%)</label>
                                        <input type="number" step="0.01" class="form-control" id="recipe_margin_pct" name="recipe_margin_pct" value="<?= isset($recipe_margin_pct) ? $recipe_margin_pct : '30'; ?>">
                                        <p class="text-muted" style="font-size:10px;margin-top:4px;">Markup on top of recipe ingredient cost</p>
                                      </div>
                                      <div class="form-group col-md-5">
                                        <div class="row">
                                          <div class="col-xs-6 text-center" style="padding-top:8px;">
                                            <small class="text-muted">Recipe Cost</small>
                                            <div style="font-size:18px;font-weight:600;" id="recipe-cost-display">0.00</div>
                                          </div>
                                          <div class="col-xs-6 text-center" style="padding-top:8px;">
                                            <small class="text-muted">Suggested Sale Price</small>
                                            <div style="font-size:18px;font-weight:600;color:#00C851;" id="recipe-sale-display">0.00</div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <?php endif; ?>

                              <?php if($show_unit_table) { ?>
                              <!-- Unit / Variant Details Table -->
                              <div class="col-md-12" id="barcode_section">
                                <div class="box box-info">
                                  <div class="box-header with-border">
                                    <h3 class="box-title">Unit / Variant Details <small class="text-muted">(Each row = one physical unit or variant. Prices & stock are set here.)</small></h3>
                                    <button type="button" class="btn btn-xs btn-primary pull-right" onclick="addBarcodeRow()"><i class="fa fa-plus"></i> Add Unit</button>
                                  </div>
                                  <div class="box-body">
                                    <table class="table table-bordered table-condensed" id="barcode_table">
                                      <thead>
                                        <tr>
                                          <th>Barcode</th>
                                          <?php if(mp_feature_enabled('batch_tracking')) { ?><th>Batch / Lot</th><?php } ?>
                                          <?php if(mp_feature_enabled('serial_number_tracking')) { ?><th>Serial Number</th><?php } ?>
                                          <?php if(mp_feature_enabled('imei_tracking')) { ?><th>IMEI Number</th><?php } ?>
                                          <th>Purchase Price</th>
                                          <th>Wholesale Price</th>
                                          <th>Retail Price (MRP)</th>
                                          <th>Qty in Stock</th>
                                          <?php if(mp_feature_enabled('expiry_tracking')) { ?><th>Expiry</th>
                                          <th>MFG Date</th><?php } ?>
                                          <?php if(mp_feature_enabled('warranty_tracking')) { ?><th>Warranty (Mo)</th><?php } ?>
                                          <th style="width:40px;"></th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        <?php if(!empty($item_barcodes) && is_array($item_barcodes)){ 
                                          $bi = 0;
                                          foreach($item_barcodes as $brow){ $bi++; ?>
                                        <tr id="barcode_row_<?=$bi;?>">
                                          <td><input type="text" class="form-control" name="barcode_barcode[]" value="<?=htmlspecialchars($brow->barcode);?>" placeholder="Scan or enter barcode"></td>
                                          <?php if(mp_feature_enabled('batch_tracking')) { ?><td><input type="text" class="form-control" name="barcode_batch[]" value="<?=htmlspecialchars($brow->batch_lot);?>" placeholder="Batch / Lot"></td><?php } ?>
                                          <?php if(mp_feature_enabled('serial_number_tracking')) { ?><td><input type="text" class="form-control" name="barcode_serial[]" value="<?=htmlspecialchars($brow->serial_number ?? '');?>" placeholder="Serial"></td><?php } ?>
                                          <?php if(mp_feature_enabled('imei_tracking')) { ?><td><input type="text" class="form-control" name="barcode_imei[]" value="<?=htmlspecialchars($brow->imei_number ?? '');?>" placeholder="IMEI"></td><?php } ?>
                                          <td><input type="text" class="form-control only_currency" name="barcode_pprice[]" value="<?=store_number_format($brow->purchase_price,0);?>" placeholder="0.00"></td>
                                          <td><input type="text" class="form-control only_currency" name="barcode_sprice[]" value="<?=store_number_format($brow->sales_price,0);?>" placeholder="0.00"><div class="profit-indicator wholesale-profit text-success small"></div></td>
                                          <td><input type="text" class="form-control only_currency" name="barcode_mrp[]" value="<?=store_number_format($brow->mrp,0);?>" placeholder="0.00"><div class="profit-indicator retail-profit text-success small"></div></td>
                                          <td><input type="text" class="form-control only_currency" name="barcode_qty[]" value="<?=store_number_format($brow->qty,0);?>" placeholder="0"></td>
                                          <?php if(mp_feature_enabled('expiry_tracking')) { ?><td><input type="date" class="form-control" name="barcode_expire_date[]" value="<?=htmlspecialchars($brow->expire_date ?? '');?>"></td>
                                          <td><input type="date" class="form-control" name="barcode_mfg_date[]" value="<?=htmlspecialchars($brow->mfg_date ?? '');?>"></td><?php } ?>
                                          <?php if(mp_feature_enabled('warranty_tracking')) { ?><td><input type="text" class="form-control" name="barcode_warranty[]" value="<?=htmlspecialchars($brow->warranty_months ?? '');?>" placeholder="Months" style="min-width:60px;"></td><?php } ?>
                                          <td><button type="button" class="btn btn-xs btn-danger" onclick="removeBarcodeRow('<?=$bi;?>')"><i class="fa fa-trash"></i></button></td>
                                        </tr>
                                        <?php } }else{ ?>
                                        <tr id="barcode_row_1">
                                          <td><input type="text" class="form-control" name="barcode_barcode[]" value="<?=htmlspecialchars($custom_barcode);?>" placeholder="Scan or enter barcode"></td>
                                          <?php if(mp_feature_enabled('batch_tracking')) { ?><td><input type="text" class="form-control" name="barcode_batch[]" value="<?=htmlspecialchars($batch_lot);?>" placeholder="Batch / Lot"></td><?php } ?>
                                          <?php if(mp_feature_enabled('serial_number_tracking')) { ?><td><input type="text" class="form-control" name="barcode_serial[]" value="<?=htmlspecialchars($serial_number);?>" placeholder="Serial"></td><?php } ?>
                                          <?php if(mp_feature_enabled('imei_tracking')) { ?><td><input type="text" class="form-control" name="barcode_imei[]" value="<?=htmlspecialchars($imei_number);?>" placeholder="IMEI"></td><?php } ?>
                                          <td><input type="text" class="form-control only_currency" name="barcode_pprice[]" value="<?=$purchase_price;?>" placeholder="0.00"></td>
                                          <td><input type="text" class="form-control only_currency" name="barcode_sprice[]" value="<?=$sales_price;?>" placeholder="0.00"><div class="profit-indicator wholesale-profit text-success small"></div></td>
                                          <td><input type="text" class="form-control only_currency" name="barcode_mrp[]" value="<?=$mrp;?>" placeholder="0.00"><div class="profit-indicator retail-profit text-success small"></div></td>
                                          <td><input type="text" class="form-control only_currency" name="barcode_qty[]" value="<?=$opening_stock;?>" placeholder="0"></td>
                                          <?php if(mp_feature_enabled('expiry_tracking')) { ?><td><input type="date" class="form-control" name="barcode_expire_date[]" value="<?=htmlspecialchars($expire_date);?>"></td>
                                          <td><input type="date" class="form-control" name="barcode_mfg_date[]" value="<?=htmlspecialchars($mfg_date);?>"></td><?php } ?>
                                          <?php if(mp_feature_enabled('warranty_tracking')) { ?><td><input type="text" class="form-control" name="barcode_warranty[]" value="<?=htmlspecialchars($warranty_months);?>" placeholder="Months" style="min-width:60px;"></td><?php } ?>
                                          <td><button type="button" class="btn btn-xs btn-danger" onclick="removeBarcodeRow('1')"><i class="fa fa-trash"></i></button></td>
                                        </tr>
                                        <?php } ?>
                                      </tbody>
                                    </table>
                                    <div id="barcode_table_msg" style="display:none; margin-top:10px;" class="alert alert-warning">
                                      <i class="fa fa-exclamation-circle"></i> <span id="barcode_table_msg_text">Please fill Purchase Price, Wholesale Price, and Retail Price for at least the first unit row.</span>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <?php } ?>
                              
                              
                           </div>
                           <hr>
                           <div class="row">
                              <div class="form-group col-md-4">
                                 <label for="discount_type"><?= $this->lang->line('discount_type'); ?></label>
                                 <select class="form-control" id="discount_type" name="discount_type"  style="width: 100%;" >
                                 <option value='Percentage'>Percentage(%)</option>
                                 <option value='Fixed'>Fixed(<?= $CI->currency() ?>)</option>
                                 </select>
                                 <span id="discount_type_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="discount"><?= $this->lang->line('discount'); ?></label>
                                 <input type="text" class="form-control only_currency" id="discount" name="discount" value="<?php print $discount; ?>" >
                                 <span id="discount_msg" style="display:none" class="text-danger"></span>
                              </div>
                              
                           </div>
                           <hr>
                           <?php if(!$show_unit_table) { ?>
                           <!-- Pricing fields for simple items (no batch/serial tracking) -->
                           <div class="row" id="simple-pricing-section">
                              <div class="form-group col-md-3">
                                 <label for="price">Base Price <small class="text-muted">(before tax)</small></label>
                                 <input type="text" class="form-control only_currency" id="price" name="price" value="<?php print $price; ?>" placeholder="0.00">
                                 <span id="price_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-3">
                                 <label for="purchase_price">Purchase Price <small class="text-muted">(Cost with tax)</small></label>
                                 <input type="text" class="form-control only_currency" id="purchase_price" name="purchase_price" value="<?php print $purchase_price; ?>" placeholder="0.00" readonly>
                                 <span id="purchase_price_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-3">
                                 <label for="sales_price">Sale Price <small class="text-muted">(if you sell it later)</small></label>
                                 <input type="text" class="form-control only_currency" id="sales_price" name="sales_price" value="<?php print $sales_price; ?>" placeholder="0.00">
                                 <span id="sales_price_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-2">
                                 <label for="profit_margin">Margin (%)</label>
                                 <input type="text" class="form-control" id="profit_margin" name="profit_margin" value="<?php print $profit_margin; ?>" placeholder="e.g. 20">
                                 <span id="profit_margin_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-1">
                                 <label for="mrp">MRP</label>
                                 <input type="text" class="form-control only_currency" id="mrp" name="mrp" value="<?php print $mrp; ?>" placeholder="0.00">
                                 <span id="mrp_msg" style="display:none" class="text-danger"></span>
                              </div>
                           </div>
                           <hr>
                           <?php } ?>
                           <!-- Hidden fields synced from first Barcode / Batch row -->
                           <div style="display:none;">
                              <?php if($show_unit_table) { ?>
                              <input type="hidden" id="price" name="price" value="<?php print $price; ?>">
                              <input type="hidden" id="purchase_price" name="purchase_price" value="<?php print $purchase_price; ?>">
                              <input type="hidden" id="sales_price" name="sales_price" value="<?php print $sales_price; ?>">
                              <input type="hidden" id="profit_margin" name="profit_margin" value="<?php print $profit_margin; ?>">
                              <input type="hidden" id="mrp" name="mrp" value="<?php print $mrp; ?>">
                              <?php } ?>
                              <input type="hidden" id="batch_lot" name="batch_lot" value="<?php print $batch_lot; ?>">
                              <input type="hidden" id="adjustment_qty" name="adjustment_qty" value="<?php print $opening_stock; ?>">
                              <input type="hidden" id="expire_date" name="expire_date" value="<?php print $expire_date; ?>">
                              <input type="hidden" id="mfg_date" name="mfg_date" value="<?php print $mfg_date; ?>">
                           </div>
                           <div class="row">
                              <div class="form-group col-md-4">
                                 <label for="tax_id"><?= $this->lang->line('tax'); ?><span class="text-danger">*</span></label>
                                 <div class="input-group">
                                 <select class="form-control select2" id="tax_id" name="tax_id"  style="width: 100%;"  >
                                    <?= get_tax_select_list($tax_id);  ?>
                                 </select>
                                 <span class="input-group-addon pointer" data-toggle="modal" data-target="#tax_modal" title="Add Customer"><i class="fa fa-plus-square-o text-primary fa-lg"></i></span>
                                    </div>
                                 <span id="tax_id_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="tax_type"><?= $this->lang->line('tax_type'); ?><span class="text-danger">*</span></label>
                                 <select class="form-control select2" id="tax_type" name="tax_type"  style="width: 100%;" >
                                  <?php 
                                    $inclusive_selected=$exclusive_selected='';
                                    if($tax_type =='Inclusive') { $inclusive_selected='selected'; }
                                    if($tax_type =='Exclusive') { $exclusive_selected='selected'; }

                                  ?>
                                    <option <?= $inclusive_selected ?> value="Inclusive">Inclusive</option>
                                    <option <?= $exclusive_selected ?> value="Exclusive">Exclusive</option>
                                 </select>
                                 <span id="tax_type_msg" style="display:none" class="text-danger"></span>
                                 
                              </div>
                              <!-- Profit margin now auto-calculated from barcode prices -->
                              <div class="form-group col-md-4">
                                 <label for="warehouse_id">Branch</label>
                                 <select class="form-control" id="warehouse_id" name="warehouse_id"  style="width: 100%;" >
                                 <?= get_warehouse_select_list();?>
                                 </select>
                                 <span id="warehouse_id_msg" style="display:none" class="text-danger"></span>
                              </div>
                           </div>

                           <?php if(mp_feature_enabled('bundles')) { ?>
                           <div class="row variant_div">
                             <div class="col-md-12">
                                  <div class="box box-info ">
                                    <div class="">
                                      <div class="box-header">
                                        <div class="col-md-6 col-md-offset-3 d-flex justify-content" >
                                          <div class="input-group">
                                                <span class="input-group-addon" title="Select Items"><i class="fa fa-search"></i></span>
                                                 <input type="text" class="form-control " placeholder="Search Variant" id="variant_search">
                                                 <span class="input-group-addon pointer text-green" data-toggle="modal" data-target="#variant-modal" title="Click to Add New Variant"><i class="fa fa-plus"></i></span>
                                              </div>
                                        </div>
                                      </div>
                                      <div class="box-body">
                                        <div class="table-responsive" style="width: 100%">
                                        <input type="hidden" value='1' id="hidden_rowcount" name="hidden_rowcount">
                                        <table class="table table-hover table-bordered" style="width:100%" id="variant_table">
                                             <thead class="custom_thead">
                                                <tr class="bg-primary" >
                                                   <th rowspan='2' style="width:15%"><?= $this->lang->line('variant_name'); ?></th>
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('sku'); ?></th> 
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('hsn'); ?></th> 
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('barcode'); ?></th> 
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('price'); ?>(<?= $CI->currency() ?>)</th>
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('purchase_price'); ?>(<?= $CI->currency() ?>)</th>
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('profit_margin'); ?></th>
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('sales_price'); ?>(<?= $CI->currency() ?>)</th>
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('mrp'); ?>(<?= $CI->currency() ?>)</th>
                                                   <th rowspan='2' style="width:10%"><?= $this->lang->line('opening_stock'); ?></th>
                                                   <th rowspan='2' style="width:5%"><?= $this->lang->line('action'); ?></th>
                                                </tr>
                                             </thead>
                                             <tbody>
                                               <?php if($item_group!='Single'){ 
                                                  echo $this->items_model->get_variants_list_in_row($q_id);
                                                } ?>
                                             </tbody>
                                          </table>
                                      </div>
                                      </div>
                                    </div>
                                  </div>
                              </div>
                              
                           </div>
                           <!-- /row -->
                           <?php } ?>
                           <!-- /.box-body -->
                           <div class="box-footer">
                              <div class="col-sm-8 col-sm-offset-2 text-center">
                                 <!-- <div class="col-sm-4"></div> -->
                                 <?php
                                    if($item_name!=""){
                                         $btn_name="Update";
                                         $btn_id="update";
                                         ?>
                                 <input type="hidden" name="q_id" id="q_id" value="<?php echo $q_id;?>"/>
                                 <?php
                                    }
                                              else{
                                                  $btn_name="Save";
                                                  $btn_id="save";
                                              }
                                    
                                              ?>
                                 <div class="col-md-3 col-md-offset-3">
                                    <button type="button" id="<?php echo $btn_id;?>" class=" btn btn-block btn-success" title="Save Data"><?php echo $btn_name;?></button>
                                 </div>
                                 <div class="col-sm-3">
                                    <a href="<?=base_url('dashboard');?>">
                                    <button type="button" class="col-sm-3 btn btn-block btn-warning close_btn" title="Go Dashboard">Close</button>
                                    </a>
                                 </div>
                              </div>
                           </div>
                           <!-- /.box-footer -->
                     <?= form_close(); ?>
                     </div>
                     <!-- /.box -->
                  </div>
                  <!--/.col (right) -->
               </div>
              
               <!-- /.row -->
         </section>
         <!-- /.content -->
         </div>
         <!-- /.content-wrapper -->
         <?php include"footer.php"; ?>
         <!-- Add the sidebar's background. This div must be placed
            immediately after the control sidebar -->
         <div class="control-sidebar-bg"></div>
      </div>
      <!-- ./wrapper -->
      <!-- SOUND CODE -->
      <?php include"comman/code_js_sound.php"; ?>
      <!-- TABLES CODE -->
      <?php include"comman/code_js.php"; ?>
      <script src="<?php echo $theme_link; ?>js/items.js?v=11"></script>
      <script src="<?php echo $theme_link; ?>js/modals.js"></script>
      <script type="text/javascript">
         $("#discount_type").val('<?=$discount_type; ?>');
        <?php if(isset($q_id)){ ?>
          $("#store_id").attr('readonly',true);
        <?php }?>
        $("#item_group").val("<?=$item_group;?>").select2().trigger("change");

        <?php if(!empty($item_name)){ ?>
          $("#hidden_rowcount").val($("#variant_table  tr").length)+1;
            calculate_purchase_price_of_all_row();
            calculate_sales_price_of_all_row();
        <?php } ?>

        <?php if($child_bit==1 || !empty($item_name)){ ?>
          $("#item_group").parent().addClass('hide');
        <?php } ?>

      <script>
        var barcodeRowIndex = <?= (!empty($item_barcodes) && is_array($item_barcodes)) ? count($item_barcodes) : 1; ?>;
        function addBarcodeRow(){
          barcodeRowIndex++;
          var html = '<tr id="barcode_row_'+barcodeRowIndex+'">'+
            '<td><input type="text" class="form-control" name="barcode_barcode[]" placeholder="Scan or enter barcode"></td>'+
            <?php if(mp_feature_enabled('batch_tracking')) { ?>'<td><input type="text" class="form-control" name="barcode_batch[]" placeholder="Batch / Lot"></td>'+
            <?php } ?><?php if(mp_feature_enabled('serial_number_tracking')) { ?>'<td><input type="text" class="form-control" name="barcode_serial[]" placeholder="Serial"></td>'+
            <?php } ?><?php if(mp_feature_enabled('imei_tracking')) { ?>'<td><input type="text" class="form-control" name="barcode_imei[]" placeholder="IMEI"></td>'+
            <?php } ?>'<td><input type="text" class="form-control only_currency" name="barcode_pprice[]" placeholder="0.00"></td>'+
            '<td><input type="text" class="form-control only_currency" name="barcode_sprice[]" placeholder="0.00"><div class="profit-indicator wholesale-profit text-success small"></div></td>'+
            '<td><input type="text" class="form-control only_currency" name="barcode_mrp[]" placeholder="0.00"><div class="profit-indicator retail-profit text-success small"></div></td>'+
            '<td><input type="text" class="form-control only_currency" name="barcode_qty[]" placeholder="0"></td>'+
            <?php if(mp_feature_enabled('expiry_tracking')) { ?>'<td><input type="date" class="form-control" name="barcode_expire_date[]"></td>'+
            '<td><input type="date" class="form-control" name="barcode_mfg_date[]"></td>'+
            <?php } ?><?php if(mp_feature_enabled('warranty_tracking')) { ?>'<td><input type="text" class="form-control" name="barcode_warranty[]" placeholder="Months" style="min-width:60px;"></td>'+
            <?php } ?>'<td><button type="button" class="btn btn-xs btn-danger" onclick="removeBarcodeRow('+barcodeRowIndex+')\"><i class="fa fa-trash\"><\/i><\/button><\/td>'+
            '<\/tr>';
          $('#barcode_table tbody').append(html);
        }
        function removeBarcodeRow(id){
          $('#barcode_row_'+id).remove();
        }

        // Custom Order field builder
        $('#accept_custom_order').on('change', function(){
          if($(this).is(':checked')){
            $('#custom-order-options').slideDown(200);
          } else {
            $('#custom-order-options').slideUp(200);
          }
        });
        $('#btn-add-cf').on('click', function(){
          var html = '<tr class="cf-row">'+
            '<td><input type="text" class="form-control input-sm" name="cf_label[]" placeholder="e.g. Size, Flavor"></td>'+
            '<td><select class="form-control input-sm" name="cf_type[]"><option value="text">Text</option><option value="textarea">Long Text</option><option value="number">Number</option><option value="select">Dropdown</option><option value="date">Date</option><option value="color">Color</option></select></td>'+
            '<td><input type="text" class="form-control input-sm" name="cf_options[]" placeholder="For dropdown: Red, Blue, Green"></td>'+
            '<td class="text-center" style="padding-top:8px;"><input type="checkbox" name="cf_required[]" value="1"></td>'+
            '<td><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest(\'tr\').remove()"><i class="fa fa-trash"></i></button></td>'+
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

          // If simple pricing section is visible, auto-fill purchase_price and price
          if($('#simple-pricing-section').is(':visible') && cost > 0){
            $('#purchase_price').val(cost.toFixed(2));
            $('#price').val(cost.toFixed(2));
            if(sale > 0){
              $('#sales_price').val(sale.toFixed(2));
            }
            // Trigger price calculations in items.js
            $('#price').trigger('change');
            $('#sales_price').trigger('change');
          }
        }
        $('#recipe_id').on('change', updateRecipeCost);
        $('#recipe_margin_pct').on('input', updateRecipeCost);
        updateRecipeCost();
        <?php endif; ?>
      </script>
      <!-- Make sidebar menu hughlighter/selector -->
      <script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
     
   </body>
</html>
