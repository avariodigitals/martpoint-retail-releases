<?php
/**
 * Services form — CONTENT ONLY.
 *
 * Rendered by Services::add()/update() as:
 *   $data['content'] = $this->load->view('services/services', $data, TRUE);
 *   $this->load->view('mp_layout', $data);
 *
 * The legacy document chrome (doctype, <head>, code_css, the old 'sidebar'
 * shell, footer and code_js) was removed so the page renders inside the
 * shared MartPoint shell. mp_layout already provides Bootstrap 3, select2,
 * DataTables, toastr and jQuery via mp_header/code_js, and dispatches to
 * physio_layout automatically on clinical stores — so this one view now
 * serves retail AND the clinic.
 *
 * Controls intentionally keep their historic ids (#items-form, #save/#update,
 * .box) and the Bootstrap-3 grid classes, because theme/js/services/services.js
 * binds to them by name.
 */
?>
<?php
         if(!isset($item_name)){
         $item_name=$sku=$opening_stock=$brand_id=$category_id=$gst_percentage=$tax_type=
         $sales_price=$purchase_price=$profit_margin=$unit_id=$price=$alert_qty=$lot_number=$store_id="";
         $stock = 0;
         $sac ='';
         $expire_date ='';
         $seller_points =0;
         $custom_barcode ='';
         $description ='';
         $laundry_service_type ='';
         $commission_type ='none';
         $commission_value ='0';
         $hsn ='';
         $discount='';
         $deposit_required='0';
         $deposit_percent='0';

         $tax_id ="";
         $discount_type='Percentage';

         $item_code = get_init_code('item');


         }
         $new_opening_stock ='';
         $CI =& get_instance();
         ?>
<!-- Page head -->
<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2><?= $page_title;?></h2>
      <div class="mp-page-sub">Add/Update Services</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a href="<?= base_url('items'); ?>" class="mp-qa-btn" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);">
        Back to <?= $this->lang->line('items_list'); ?>
      </a>
    </div>
  </div>
</div>

<section class="mp-section">
  <div class="row">
    <!-- right column -->
    <div class="col-md-12">
      <!-- Horizontal Form -->
      <!-- .box is REQUIRED: services.js appends its loading .overlay here.
           Its frame is stripped so the card isn't flush against a second border. -->
      <div class="box box-primary mp-items-box">
                     
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
                                 <input type="text" class="form-control" id="item_name" name="item_name" placeholder="" value="<?php print $item_name; ?>" >
                                 <span id="item_name_msg" style="display:none" class="text-danger"></span>
                              </div>
                              
                              <div class="form-group col-md-4">
                                 <label for="category_id">Category <span class="text-danger">*</span></label>
                                 <select class="form-control select2" id="category_id" name="category_id"  style="width: 100%;"  value="<?php print $category_id; ?>">
                                    <option value="">-Select-</option>
                                  <?= get_categories_select_list($category_id);  ?>
                                 </select>
                                 <span id="category_id_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="custom_barcode" ><?= $this->lang->line('barcode'); ?></label>
                                 <input type="text" class="form-control" id="custom_barcode" name="custom_barcode" placeholder=""  value="<?php print $custom_barcode; ?>" >
                                 <span id="custom_barcode_msg" style="display:none" class="text-danger"></span>
                              </div>

                              <div class="form-group col-md-4">
                                 <label for="sac" ><?= $this->lang->line('sac'); ?></label>
                                 <input type="text" class="form-control" id="sac" name="sac" placeholder=""  value="<?php print $sac; ?>" >
                                 <span id="sac_msg" style="display:none" class="text-danger"></span>
                              </div>

                              <div class="form-group col-md-4" style="display:none;">
                                 <label for="hsn" ><?= $this->lang->line('hsn'); ?></label>
                                 <input type="text" class="form-control" id="hsn" name="hsn" placeholder=""  value="<?php print $hsn; ?>" >
                                 <span id="hsn_msg" style="display:none" class="text-danger"></span>
                              </div>

                              <div class="form-group col-md-4">
                                 <label for="seller_points" ><?= $this->lang->line('seller_points'); ?></label>
                                 <input type="text" class="form-control only_currency" id="seller_points" name="seller_points" placeholder=""  value="<?php print $seller_points; ?>" >
                                 <span id="seller_points_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="custom_barcode" ><?= $this->lang->line('description'); ?></label>
                                 <textarea type="text" class="form-control" id="description" name="description" placeholder=""><?php print $description; ?></textarea>
                                 <span id="description_msg" style="display:none" class="text-danger"></span>
                              </div>

                              <?php
                              $profile = mp_get_store_profile();
                              $is_laundry = ($profile['industry_type'] ?? '') === 'laundry' || mp_feature_enabled('laundry_workflow');
                              ?>
                              <?php if ($is_laundry): ?>
                              <div class="form-group col-md-4">
                                 <label for="laundry_service_type">Laundry Service Type <span class="text-danger">*</span></label>
                                 <select class="form-control select2" id="laundry_service_type" name="laundry_service_type" style="width: 100%;">
                                    <option value="">-Select-</option>
                                    <option value="wash_iron" <?= (isset($laundry_service_type) && $laundry_service_type == 'wash_iron') ? 'selected' : ''; ?>>Wash + Iron</option>
                                    <option value="wash_only" <?= (isset($laundry_service_type) && $laundry_service_type == 'wash_only') ? 'selected' : ''; ?>>Wash Only</option>
                                    <option value="iron_only" <?= (isset($laundry_service_type) && $laundry_service_type == 'iron_only') ? 'selected' : ''; ?>>Iron Only</option>
                                    <option value="dry_clean" <?= (isset($laundry_service_type) && $laundry_service_type == 'dry_clean') ? 'selected' : ''; ?>>Dry Clean</option>
                                 </select>
                                 <span id="laundry_service_type_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <?php endif; ?>

                              <?php if (mp_feature_enabled('staff_commission')): ?>
                              <div class="form-group col-md-4">
                                 <label for="commission_type">Commission Type</label>
                                 <select class="form-control select2" id="commission_type" name="commission_type" style="width: 100%;" onchange="toggleCommissionValue()">
                                    <option value="none" <?= (isset($commission_type) && $commission_type == 'none') ? 'selected' : ''; ?>>No Commission</option>
                                    <option value="flat" <?= (isset($commission_type) && $commission_type == 'flat') ? 'selected' : ''; ?>>Flat Amount</option>
                                    <option value="percent" <?= (isset($commission_type) && $commission_type == 'percent') ? 'selected' : ''; ?>>Percentage (%)</option>
                                 </select>
                              </div>
                              <div class="form-group col-md-4" id="commission_value_wrap" style="<?= (isset($commission_type) && $commission_type != 'none') ? '' : 'display:none;' ?>">
                                 <label for="commission_value">Commission Value</label>
                                 <input type="number" step="0.01" min="0" class="form-control" id="commission_value" name="commission_value" value="<?= isset($commission_value) ? $commission_value : '0'; ?>">
                                 <span id="commission_value_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <?php endif; ?>

                              <div class="form-group col-md-4">
                                 <label for="item_image"><?= $this->lang->line('select_image'); ?></label>
                                 <input type="file" name="item_image" id="item_image">
                                 <span id="item_image_msg" style="display:block;" class="text-danger">Max Width/Height: 1000px * 1000px & Size: 1MB </span>
                              </div>
                              
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
                           <div class="row">
                              <div class="form-group col-md-4">
                                 <label for="price"><?= $this->lang->line('price'); ?> (Expenses)<span class="text-danger">*</span></label>
                                 <input type="text" class="form-control only_currency" id="price" name="price" placeholder="Price of Item without Tax"  value="<?php print $price; ?>" >
                                 <span id="price_msg" style="display:none" class="text-danger"></span>
                                 <span id="" style="display:show" class="text-primary">Enter "0", If there is no expenses</span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="tax_id" ><?= $this->lang->line('tax'); ?><span class="text-danger">*</span></label>
                                 <select class="form-control select2" id="tax_id" name="tax_id"  style="width: 100%;" >
                                  <?= get_tax_select_list($tax_id);  ?>
                                 </select>
                                 <span id="tax_id_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4 hide">
                                 <label for="purchase_price"><?= $this->lang->line('purchase_price'); ?><span class="text-danger">*</span></label>
                                 <input type="text" class="form-control only_currency" id="purchase_price" name="purchase_price" placeholder="Total Price with Tax Amount"  value="<?php print $purchase_price; ?>" readonly='' >
                                 <span id="purchase_price_msg" style="display:none" class="text-danger"></span>
                              </div>
                           </div>
                           <!-- /row -->
                           <div class="row">
                              <div class="form-group col-md-4">
                                 <label for="tax_type"><?= $this->lang->line('sales_tax_type'); ?><span class="text-danger">*</span></label>
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
                              <div class="form-group col-md-4">
                                 <label for="sales_price" class="control-label"><?= $this->lang->line('sales_price'); ?><span class="text-danger">*</span></label>
                                 <input type="text" class="form-control only_currency " id="sales_price" name="sales_price" placeholder="Sales Price"  value="<?php print $sales_price; ?>" >
                                 <span id="sales_price_msg" style="display:none" class="text-danger"></span>
                              </div>
                           </div>
                           <!-- /row -->
                           <div class="row">
                              <div class="form-group col-md-4">
                                 <label for="deposit_required">Deposit Required</label>
                                 <select class="form-control" id="deposit_required" name="deposit_required" style="width: 100%;">
                                    <option value="0" <?= $deposit_required == '0' ? 'selected' : ''; ?>>No</option>
                                    <option value="1" <?= $deposit_required == '1' ? 'selected' : ''; ?>>Yes</option>
                                 </select>
                                 <span id="deposit_required_msg" style="display:none" class="text-danger"></span>
                              </div>
                              <div class="form-group col-md-4">
                                 <label for="deposit_percent">Deposit %</label>
                                 <input type="text" class="form-control only_currency" id="deposit_percent" name="deposit_percent" placeholder="e.g. 50" value="<?php print $deposit_percent; ?>" >
                                 <span id="deposit_percent_msg" style="display:none" class="text-danger"></span>
                              </div>
                           </div>
                           <!-- /row -->
                           
                           
                           

                           <!-- /row -->
                           <!-- /.box-body -->
                           <div class="box-footer mp-form-actions">
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
</div>
</section>

<!-- services.js is enqueued by the controller via $data['extra_js_files']
     so it loads from mp_layout before this content. -->
<style>
/* .box is required by services.js (it appends its loading .overlay), but the
   card sections inside carry their own chrome — strip the outer frame. */
.box.mp-items-box { border: none !important; background: transparent !important; box-shadow: none !important; border-radius: 0 !important; }

/* Form actions bar — same pattern as the item/category forms */
.mp-form-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; padding: 16px 20px; border-top: 1px solid var(--mp-border); background: var(--mp-bg); border-radius: 0 0 16px 16px; }
</style>
<script type="text/javascript">
         $("#discount_type").val('<?=$discount_type; ?>');
        <?php if(isset($q_id)){ ?>
          $("#store_id").attr('readonly',true);
        <?php }?>
         function toggleCommissionValue() {
            var type = $('#commission_type').val();
            if (type === 'none' || type === '') {
               $('#commission_value_wrap').hide();
               $('#commission_value').val('0');
            } else {
               $('#commission_value_wrap').show();
            }
         }
         $(document).ready(function(){
            toggleCommissionValue();
         });
      </script>
      <!-- Make sidebar menu highlighter/selector -->
      <script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
