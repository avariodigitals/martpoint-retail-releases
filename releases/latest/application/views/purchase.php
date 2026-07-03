<!DOCTYPE html>
<html>

<head>
<!-- TABLES CSS CODE -->
<?php include"comman/code_css.php"; ?>
<!-- </copy> -->  
<style type="text/css">
   table.table-bordered > thead > tr > th {
   text-align: center;
   }
   .table > tbody > tr > td,
   .table > tbody > tr > th,
   .table > tfoot > tr > td,
   .table > tfoot > tr > th,
   .table > thead > tr > td,
   .table > thead > tr > th
   {
   padding-left: 2px;
   padding-right: 2px;
   }
</style>
<link rel="stylesheet" href="<?php echo $theme_link; ?>css/newcustom.css">
</head>



<body class="hold-transition skin-blue sidebar-mini">
  
<div class="wrapper">
 
 
 <?php include"sidebar.php"; ?>
 
 <?php
    if(!isset($purchase_id)){
      $supplier_id  = $pur_date = $purchase_status = $warehouse_id =
      $reference_no  =
      $other_charges_input          = $other_charges_tax_id =
      $discount_input = $discount_type  = $purchase_note=$store_id='';
      $pur_date=show_date(date("d-m-Y"));
    }
    else{
      $q2 = $this->db->query("select * from db_purchase where id=$purchase_id");
      $supplier_id=$q2->row()->supplier_id;
      $warehouse_id=$q2->row()->warehouse_id;
      $pur_date=show_date($q2->row()->purchase_date);
      $purchase_status=$q2->row()->purchase_status;
      $reference_no=$q2->row()->reference_no;
      $discount_input=store_number_format($q2->row()->discount_to_all_input,2);
      $discount_type=$q2->row()->discount_to_all_type;
      $other_charges_input=store_number_format($q2->row()->other_charges_input,0);
      $other_charges_tax_id=$q2->row()->other_charges_tax_id;
      $purchase_note=$q2->row()->purchase_note;
      $store_id=$q2->row()->store_id;

      $items_count = $this->db->query("select count(*) as items_count from db_purchaseitems where purchase_id=$purchase_id")->row()->items_count;
    }
    
    ?>

 

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- **********************MODALS***************** -->
    <?php include"modals/modal_supplier.php"; ?>
    <?php include"modals/modal_purchase_item.php"; ?>
    <?php include"modals/modal_item.php"; ?>
    <?php include"modals/modal_item_or_service.php"; ?>
   <?php /*include"modals/modal_service.php";*/ ?>
    <!-- **********************MODALS END***************** -->
    <!-- Content Header (Page header) -->
    <section class="content-header">
         <h1>
            <?=$page_title;?>
            <small>Add/Update Purchase</small>
         </h1>
         <ol class="breadcrumb">
            <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="<?php echo $base_url; ?>purchase"><?= $this->lang->line('purchase_list'); ?></a></li>
            <li><a href="<?php echo $base_url; ?>purchase/add"><?= $this->lang->line('new_purchase'); ?></a></li>
            <li class="active"><?=$page_title;?></li>
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
                     <div class="box box-primary " >
                        <!-- style="background: #68deac;" -->
                        
                        <!-- form start -->
                         <!-- OK START -->
                        <?= form_open('#', array('class' => 'form-horizontal', 'id' => 'purchase-form', 'enctype'=>'multipart/form-data', 'method'=>'POST'));?>
                           <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
                           <input type="hidden" value='1' id="hidden_rowcount" name="hidden_rowcount">
                           <input type="hidden" value='0' id="hidden_update_rowid" name="hidden_update_rowid">

                          
                           <div class="box-body purchase-modern">
                              <!-- Store Code -->
                              <?php
                              echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>";
                              ?>
                              <!-- SECTION 1: PURCHASE HEADER -->
                              <div class="purchase-section-card">
                                 <div class="section-title">Purchase Details</div>
                                 <div class="smart-header">
                                    <!-- Box 1: Reference No. -->
                                    <div class="smart-box reference">
                                       <div class="box-accent"></div>
                                       <i class="fa fa-hashtag box-icon"></i>
                                       <label for="reference_no"><?= $this->lang->line('reference_no'); ?></label>
                                       <input type="text" value="<?php echo $reference_no; ?>" class="form-control" id="reference_no" name="reference_no" placeholder="Enter reference" <?= !is_admin() ? 'readonly' : ''; ?>>
                                       <span id="reference_no_msg" style="display:none" class="text-danger"></span>
                                    </div>

                                    <!-- Box 2: Purchase Date -->
                                    <div class="smart-box date">
                                       <div class="box-accent"></div>
                                       <i class="fa fa-calendar box-icon"></i>
                                       <label for="pur_date"><?= $this->lang->line('purchase_date'); ?> <span class="text-danger">*</span></label>
                                       <div class="input-group date">
                                          <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                          <input type="text" class="form-control pull-right datepicker" id="pur_date" name="pur_date" readonly onkeyup="shift_cursor(event,'purchase_status')" value="<?= $pur_date;?>">
                                       </div>
                                       <span id="pur_date_msg" style="display:none" class="text-danger"></span>
                                    </div>

                                    <!-- Box 3: Supplier Name -->
                                    <div class="smart-box supplier">
                                       <div class="box-accent"></div>
                                       <i class="fa fa-user box-icon"></i>
                                       <label for="supplier_id"><?= $this->lang->line('supplier_name'); ?> <span class="text-danger">*</span></label>
                                       <div class="supplier-row">
                                          <div class="supplier-select-wrap">
                                             <select class="form-control select2" id="supplier_id" name="supplier_id" style="width: 100%;"></select>
                                          </div>
                                          <div class="supplier-quick-actions">
                                             <button type="button" class="quick-btn" data-toggle="modal" data-target="#supplier-modal" title="Add"><i class="fa fa-plus"></i></button>
                                             <button type="button" class="quick-btn" onclick="view_supplier_details()" title="View"><i class="fa fa-eye"></i></button>
                                          </div>
                                       </div>
                                       <span id="supplier_id_msg" style="display:none" class="text-danger"></span>
                                    </div>

                                    <!-- Box 4: Purchase Status -->
                                    <div class="smart-box status">
                                       <div class="box-accent"></div>
                                       <i class="fa fa-tag box-icon"></i>
                                       <label for="purchase_status"><?= $this->lang->line('purchase_status'); ?> <span class="text-danger">*</span></label>
                                       <select class="form-control status-badge-select status-draft" id="purchase_status" name="purchase_status" onchange="toggle_batch_fields(); update_status_badge_style();">
                                          <option value="Draft" <?= ($purchase_status=='Draft' || $purchase_status=='') ? 'selected' : ''; ?>>Draft</option>
                                          <option value="Ordered" <?= ($purchase_status=='Ordered') ? 'selected' : ''; ?>>Ordered</option>
                                          <option value="Partially Received" <?= ($purchase_status=='Partially Received') ? 'selected' : ''; ?>>Partially Received</option>
                                          <option value="Received" <?= ($purchase_status=='Received') ? 'selected' : ''; ?>>Received</option>
                                       </select>
                                       <span id="purchase_status_msg" style="display:none" class="text-danger"></span>
                                    </div>

                                    <!-- Box 5: Warehouse -->
                                    <div class="smart-box warehouse">
                                       <div class="box-accent"></div>
                                       <i class="fa fa-building box-icon"></i>
                                       <label for="warehouse_id"><?= $this->lang->line('warehouse'); ?> <span class="text-danger">*</span></label>
                                       <?php if(warehouse_module() && warehouse_count()>1){ ?>
                                       <?php $defaultWarehouseId = getDefaultWarehouseId(); $store_id = get_current_store_id(); ?>
                                       <select class="form-control select2" id="warehouse_id" name="warehouse_id" style="width: 100%;">
                                          <?php
                                          if(!is_admin() && !is_store_admin()){
                                             $privileged_warehouses = get_privileged_warehouses_ids();
                                             if(!empty($privileged_warehouses)){
                                                $this->db->where("id in ($privileged_warehouses)");
                                             } else {
                                                $this->db->where("id",0);
                                             }
                                          }
                                          $this->db->select("*")->where("status",1)->where("store_id",$store_id)->from("db_warehouse");
                                          $q2=$this->db->get();
                                          if($q2->num_rows()>0){
                                             foreach($q2->result() as $res1){
                                                $selected = ((isset($warehouse_id) && !empty($warehouse_id) && $warehouse_id==$res1->id) || $res1->id == $defaultWarehouseId) ? 'selected' : '';
                                                echo "<option $selected value='".$res1->id."'>".$res1->warehouse_name."</option>";
                                             }
                                          } else {
                                             echo "<option value=''>No Records Found</option>";
                                          }
                                          ?>
                                       </select>
                                       <span id="warehouse_id_msg" style="display:none" class="text-danger"></span>
                                       <?php } else {
                                          $wh_id = get_store_warehouse_id();
                                          $wh_name = get_warehouse_name($wh_id);
                                          echo "<input type='hidden' name='warehouse_id' id='warehouse_id' value='".$wh_id."'>";
                                          echo "<p class='form-control-static' style='margin:0;padding:8px 0;font-size:13px;color:#2d3748;'>".$wh_name."</p>";
                                       } ?>
                                    </div>
                                 </div>
                              </div>

                              <!-- WORKFLOW STEPS -->
                              <div class="purchase-workflow">
                                 <div class="step active"><span class="step-num">1</span> Select Supplier</div>
                                 <div class="step"><span class="step-num">2</span> Add Products</div>
                                 <div class="step"><span class="step-num">3</span> Review Totals</div>
                                 <div class="step"><span class="step-num">4</span> Record Payment</div>
                                 <div class="step"><span class="step-num">5</span> Save Purchase</div>
                              </div>

                              <!-- SECTION 2: HERO SEARCH -->
                              <div class="purchase-hero-search">
                                 <div class="hero-label">Search Products</div>
                                 <div class="hero-input-wrap">
                                    <i class="fa fa-barcode hero-input-icon"></i>
                                    <input type="text" class="hero-input" placeholder="Search product name, barcode or SKU" autofocus id="item_search">
                                    <button type="button" class="hero-add-btn show_item_service" title="Add New Item"><i class="fa fa-plus"></i></button>
                                 </div>
                              </div>

                              <!-- SECTION 3: PURCHASE ITEMS -->
                              <div class="purchase-section-card">
                                 <div class="section-title">Purchase Items</div>
                                 <div id="purchase_items_container" class="purchase-items-container">
                                    <div class="purchase-items-empty" id="purchase_items_empty">
                                       <i class="fa fa-shopping-cart"></i>
                                       <p>No items added yet</p>
                                       <small>Search for a product above to get started</small>
                                    </div>
                                 </div>
                              </div>

                              <!-- Hidden legacy table for JS compatibility -->
                              <table class="table table-hover table-bordered" style="display:none;" id="purchase_table">
                                 <thead class="custom_thead">
                                    <tr class="bg-primary">
                                       <th rowspan='2' style="width:15%">Item Name</th>
                                       <th rowspan='2' style="width:15%;min-width:180px;">Quantity</th>
                                       <th rowspan='2' style="width:10%">Price(<?=$CURRENCY;?>)</th>
                                       <th rowspan='2' style="width:10%">Discount(<?=$CURRENCY;?>)</th>
                                       <th rowspan='2' style="width:7.5%">Tax</th>
                                       <th rowspan='2' style="width:7.5%">Unit Cost</th>
                                       <th rowspan='2' style="width:7.5%">Total</th>
                                       <th colspan='5' class="batch-group-header" style="width:37%">Receipt / Batch Details</th>
                                       <th rowspan='2' style="width:7.5%">Action</th>
                                    </tr>
                                    <tr class="bg-primary batch-header-row">
                                       <th style="width:8%">Batch/Lot</th>
                                       <th style="width:8%">Barcode</th>
                                       <th style="width:7%">Rcv Qty</th>
                                       <th style="width:7%">Expiry</th>
                                       <th style="width:7%">MFG Date</th>
                                    </tr>
                                 </thead>
                                 <tbody></tbody>
                              </table>

                           <!-- ====== SECTION 4: CHARGES & STICKY SUMMARY ====== -->
                           <div class="row">
                              
                              
                              <div class="col-md-8">
                                 <div class="purchase-section-card">
                                    <div class="section-title">Charges & Notes</div>
                                    <div class="row">
                                       <div class="col-md-12">
                                          <div class="form-group">
                                             <label for="other_charges_input" class="col-sm-4 control-label"><?= $this->lang->line('other_charges'); ?></label>
                                             <div class="col-sm-4">
                                                <input type="text" class="form-control text-right only_currency" id="other_charges_input" name="other_charges_input" onkeyup="final_total();" value="<?php echo $other_charges_input; ?>">
                                             </div>
                                             <div class="col-sm-4">
                                                <select class="form-control" id="other_charges_tax_id" name="other_charges_tax_id" onchange="final_total();" style="width: 100%;">
                                                   <?= get_tax_select_list($other_charges_tax_id,get_current_store_id());?>
                                                </select>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="row">
                                       <div class="col-md-12">
                                          <div class="form-group">
                                             <label for="discount_to_all_input" class="col-sm-4 control-label"><?= $this->lang->line('discount_on_all'); ?></label>
                                             <div class="col-sm-4">
                                                <input type="text" class="form-control text-right only_currency" id="discount_to_all_input" name="discount_to_all_input" onkeyup="enable_or_disable_item_discount();" value="<?php echo $discount_input; ?>">
                                             </div>
                                             <div class="col-sm-4">
                                                <select class="form-control" onchange="final_total();" id='discount_to_all_type' name="discount_to_all_type">
                                                   <option value='in_percentage'>Per%</option>
                                                   <option value='in_fixed'>Fixed</option>
                                                </select>
                                             </div>
                                             <script type="text/javascript">
                                                <?php if($discount_type!=''){ ?>
                                                   document.getElementById('discount_to_all_type').value='<?php echo $discount_type; ?>';
                                                <?php }?>
                                             </script>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="row">
                                       <div class="col-md-12">
                                          <div class="form-group">
                                             <label for="purchase_note" class="col-sm-4 control-label"><?= $this->lang->line('note'); ?></label>
                                             <div class="col-sm-8">
                                                <textarea class="form-control text-left" id='purchase_note' name="purchase_note"><?=$purchase_note;?></textarea>
                                                <span id="purchase_note_msg" style="display:none" class="text-danger"></span>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              

                              <div class="col-md-4">
                                 <div class="purchase-summary-card">
                                    <div class="summary-title">Live Summary</div>
                                    <div class="summary-row">
                                       <span class="label"><?= $this->lang->line('total_quantities'); ?></span>
                                       <span class="value total_quantity text-success">0</span>
                                    </div>
                                    <div class="summary-row">
                                       <span class="label"><?= $this->lang->line('subtotal'); ?></span>
                                       <span class="value"><?= $CI->currency('<b id="subtotal_amt" name="subtotal_amt">0.00</b>'); ?></span>
                                    </div>
                                    <div class="summary-row">
                                       <span class="label"><?= $this->lang->line('other_charges'); ?></span>
                                       <span class="value"><?= $CI->currency('<b id="other_charges_amt" name="other_charges_amt">0.00</b>'); ?></span>
                                    </div>
                                    <div class="summary-row">
                                       <span class="label"><?= $this->lang->line('discount_on_all'); ?></span>
                                       <span class="value"><?= $CI->currency('<b id="discount_to_all_amt" name="discount_to_all_amt">0.00</b>'); ?></span>
                                    </div>
                                    <div class="summary-row" style="<?= (!is_enabled_round_off()) ? 'display: none;' : '';?>">
                                       <span class="label"><?= $this->lang->line('round_off'); ?> <i class="hover-q fa fa-info-circle text-maroon" data-container="body" data-toggle="popover" data-placement="top" data-content="Go to Site Settings -> Site -> Disable the Round Off(Checkbox)." data-html="true" data-trigger="hover" title="Disable Round Off?"></i></span>
                                       <span class="value"><?= $CI->currency('<b id="round_off_amt" name="tot_round_off_amt">0.00</b>'); ?></span>
                                    </div>
                                    <div class="summary-row grand-total">
                                       <span class="label"><?= $this->lang->line('grand_total'); ?></span>
                                       <span class="value"><?= $CI->currency('<b id="total_amt" name="total_amt">0.00</b>'); ?></span>
                                    </div>
                                 </div>
                              </div>
                           </div>

                           <!-- PREVIOUS PAYMENTS -->
                           <div class="col-xs-12" style="margin-top:20px;">
                              <div class="col-sm-12">
                                 <div class="box-body">
                                    <div class="col-md-12">
                                       <table class="table table-hover table-bordered" style="width:100%" id="payments_table"><h4 class="box-title text-info"><?= $this->lang->line('previous_payments_information'); ?> : </h4>
                                          <thead>
                                             <tr class="bg-gray" >
                                                <th>#</th>
                                                <th><?= $this->lang->line('date'); ?></th>
                                                <th><?= $this->lang->line('payment_type'); ?></th>
                                                <th><?= $this->lang->line('payment_note'); ?></th>
                                                <th><?= $this->lang->line('payment'); ?></th>
                                                <th><?= $this->lang->line('action'); ?></th>
                                             </tr>
                                          </thead>
                                          <tbody>
                                             <?php
                                             if(isset($purchase_id)){
                                               $q3 = $this->db->query("select * from db_purchasepayments where purchase_id=$purchase_id");
                                               if($q3->num_rows()>0){
                                                 $i=1;
                                                 $total_paid = 0;
                                                 foreach ($q3->result() as $res3) {
                                                   echo "<tr class='text-center text-bold' id='payment_row_".$res3->id."'>";
                                                   echo "<td>".$i."</td>";
                                                   echo "<td>".show_date($res3->payment_date)."</td>";
                                                   echo "<td>".$res3->payment_type."</td>";
                                                   echo "<td>".$res3->payment_note."</td>";
                                                   echo "<td class='text-right' id='paid_amt_$i'>".$CI->currency($res3->payment)."</td>";
                                                   echo '<td><i class="fa fa-trash text-red pointer" onclick="delete_payment('.$res3->id.')"> Delete</i></td>';
                                                   echo "</tr>";
                                                   $total_paid +=$res3->payment;
                                                   $i++;
                                                 }
                                                 echo "<tr class='text-right text-bold'><td colspan='4' >Total</td><td data-rowcount='$i' id='paid_amt_tot'>".$CI->currency(number_format($total_paid,2,'.',''))."</td><td></td></tr>";
                                               }
                                               else{
                                                 echo "<tr><td colspan='6' class='text-center text-bold'>No Previous Payments Found!!</td></tr>";
                                               }
                                             }
                                             else{
                                               echo "<tr><td colspan='6' class='text-center text-bold'>Payments Pending!!</td></tr>";
                                             }
                                             ?>
                                          </tbody>
                                       </table>
                                    </div>
                                 </div>
                              </div>
                           </div>

                           <!-- SECTION 5: PAYMENT CARD -->
                           <div class="purchase-payment-card" style="margin-top:20px;">
                              <div class="section-title"><?= $this->lang->line('make_payment'); ?></div>
                              <div class="payment-grid">
                                 <div>
                                    <label for="amount"><?= $this->lang->line('amount'); ?></label>
                                    <input type="text" class="form-control text-right paid_amt only_currency" id="amount" name="amount" placeholder="" >
                                    <span id="amount_msg" style="display:none" class="text-danger"></span>
                                 </div>
                                 <div>
                                    <label for="payment_type"><?= $this->lang->line('payment_type'); ?></label>
                                    <select class="form-control select2" id='payment_type' name="payment_type">
                                       <?php
                                       $q1=$this->db->query("select * from db_paymenttypes where status=1 and store_id=".get_current_store_id());
                                       if($q1->num_rows()>0){
                                          echo "<option value=''>-Select-</option>";
                                          foreach($q1->result() as $res1){
                                             echo "<option value='".$res1->payment_type."'>".$res1->payment_type ."</option>";
                                          }
                                       }
                                       else{
                                          echo "<option>None</option>";
                                       }
                                       ?>
                                    </select>
                                    <span id="payment_type_msg" style="display:none" class="text-danger"></span>
                                 </div>
                                 <div>
                                    <label for="account_id"><?= $this->lang->line('account'); ?></label>
                                    <select class="form-control select2" id='account_id' name="account_id">
                                       <?php
                                       echo '<option value="">-None-</option>';
                                       echo get_accounts_select_list();
                                       ?>
                                    </select>
                                    <span id="account_id_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>
                              <div class="row" style="margin-top:16px;">
                                 <div class="col-md-12">
                                    <label for="payment_note"><?= $this->lang->line('payment_note'); ?></label>
                                    <textarea class="form-control" id="payment_note" name="payment_note" placeholder="" ></textarea>
                                    <span id="payment_note_msg" style="display:none" class="text-danger"></span>
                                 </div>
                              </div>
                           </div>

                           </div>
                           <!-- /.box-body -->

                           <!-- SAVE ACTIONS -->
                           <div class="box-footer col-sm-12" style="border:none; background:transparent;">
                              <center>
                                <?php
                                if(isset($purchase_id)){
                                  $btn_id='update';
                                  $btn_name="Update Purchase";
                                  echo '<input type="hidden" name="purchase_id" id="purchase_id" value="'.$purchase_id.'"/>';
                                }
                                else{
                                  $btn_id='save';
                                  $btn_name="Save Purchase";
                                }
                                ?>
                                 <div class="purchase-actions">
                                    <button type="button" id="<?php echo $btn_id;?>" class="btn-action btn-save payments_modal" title="Save Data"><i class="fa fa-check"></i> <?php echo $btn_name;?></button>
                                    <button type="button" class="btn-action btn-save-draft" onclick="save_as_draft()"><i class="fa fa-file-text-o"></i> Save Draft</button>
                                    <button type="button" class="btn-action btn-save-receive" onclick="save_and_receive()"><i class="fa fa-download"></i> Save & Receive Stock</button>
                                    <a href="<?= base_url()?>dashboard" class="btn-action btn-cancel"><i class="fa fa-times"></i> Cancel</a>
                                 </div>
                              </center>
                           </div>
                           

                           <?= form_close(); ?>
                           <!-- OK END -->
                     </div>
                  </div>
                  <!-- /.box-footer -->
                 
               </div>
               <!-- /.box -->
             </section>
            <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  
 <?php include"footer.php"; ?>
<!-- SOUND CODE -->
<?php include"comman/code_js_sound.php"; ?>
<!-- GENERAL CODE -->
<?php include"comman/code_js.php"; ?>

 <script src="<?php echo $theme_link; ?>js/modals.js"></script>
 <script src="<?php echo $theme_link; ?>js/modals/modal_item.js"></script>
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<script src="<?php echo $theme_link; ?>js/mp-offline-db.js?v=3"></script>
<script src="<?php echo $theme_link; ?>js/purchase.js?v=2"></script>
<script src="<?php echo $theme_link; ?>js/ajaxselect/supplier_select_ajax.js"></script>  

<script>

         //supplier Selection Box Search
         function getsupplierSelectionId() {
           return '#supplier_id';
         }

         $(document).ready(function () {

            var supplier_id = "<?= (!empty($supplier_id)) ? $supplier_id : '';  ?>";

            if(supplier_id!=''){
               autoLoadFirstsupplier(supplier_id);
            }

         });
         //supplier Selection Box Search - END


        var base_url=$("#base_url").val();
        $("#store_id").on("change",function(){
          var store_id=$(this).val();
          $.post(base_url+"purchase/get_suppliers_select_list",{store_id:store_id},function(result){
              $("#supplier_id").html('').append(result).select2();
              $("#purchase_items_container").empty();
              $("#purchase_items_empty").show();
              $("#purchase_table > tbody").empty();
              final_total();
          });
          $.post(base_url+"sales/get_tax_select_list",{store_id:store_id},function(result){
              $("#other_charges_tax_id").html('').append(result).select2();
              final_total();
          });
          
        });

        /*Warehouse*/
        $("#warehouse_id").on("change",function(){
          var warehouse_id=$(this).val();
          $("#purchase_items_container").empty();
          $("#purchase_items_empty").show();
          $("#purchase_table > tbody").empty();
          final_total();
        });
        /*Warehouse end*/

        /* Status badge style updater */
        function update_status_badge_style(){
          var status = $("#purchase_status").val();
          var $select = $("#purchase_status");
          $select.removeClass('status-draft status-ordered status-partial status-received');
          if(status=='Draft') $select.addClass('status-draft');
          else if(status=='Ordered') $select.addClass('status-ordered');
          else if(status=='Partially Received') $select.addClass('status-partial');
          else if(status=='Received') $select.addClass('status-received');
        }
        $(document).ready(function(){ update_status_badge_style(); });

        /* Supplier quick actions */
        function view_supplier_details(){
          var sup_id = $("#supplier_id").val();
          if(sup_id) window.open(base_url+'suppliers/view/'+sup_id,'_blank');
          else toastr['warning']('Please select a supplier first!');
        }

        /* Save helpers */
        function save_as_draft(){
          $("#purchase_status").val('Draft');
          update_status_badge_style();
          $("#save, #update").trigger('click');
        }
        function save_and_receive(){
          $("#purchase_status").val('Received');
          update_status_badge_style();
          $("#save, #update").trigger('click');
        }

        /* Card expand/collapse */
        $(document).on('click', '.btn-expand', function(){
          var $btn = $(this);
          var $adv = $btn.closest('.purchase-item-card').find('.card-advanced');
          $adv.toggleClass('expanded');
          $btn.toggleClass('expanded');
          var text = $adv.hasClass('expanded') ? 'Hide Details' : 'Additional Details';
          $btn.html('<i class="fa fa-chevron-down"></i> ' + text);
        });


         $(".close_btn").on("click",function(){
           if(typeof swal === 'undefined'){
             if(!confirm('Are you sure you want to navigate away from this page?')) return;
             window.location='<?php echo $base_url; ?>dashboard';
           } else {
             swal({
               title: "Leave Page?",
               text: "Are you sure you want to navigate away from this page? Unsaved changes may be lost.",
               icon: "warning",
               buttons: true,
               dangerMode: true
             }).then(function(willLeave){
               if(willLeave) window.location='<?php echo $base_url; ?>dashboard';
             });
           }
         });
         //Initialize Select2 Elements
             $(".select2").select2();
         //Date picker
             $('.datepicker').datepicker({
               autoclose: true,
            format: 'dd-mm-yyyy',
              todayHighlight: true
             });
          
       


         
         /* ---------- CALCULATE TAX -------------*/
         function calculate_tax(i){ //i=Row
           set_tax_value(i);

           //Find the Tax type and Tax amount
           var tax_type = $("#tr_tax_type_"+i).val();
           var tax_amount = get_float_type_data("#td_data_"+i+"_5");
           var qty=get_float_type_data("#td_data_"+i+"_3")
           var purchase_price=get_float_type_data("#td_data_"+i+"_4");
           var discount =get_float_type_data("#td_data_"+i+"_8");
           var tax=get_float_type_data("#tr_tax_value_"+i);
           
           var amt=qty * purchase_price;//Taxable
         
           var total_amt=amt-discount;

          

           total_amt = (tax_type=='Inclusive') ? total_amt : total_amt + tax_amount;
           
           //CAlculate Item wise price and tax and discount
           var tax_each = (tax_type=='Inclusive') ? 0 : calculate_exclusive(purchase_price-discount,tax);
           
           $("#td_data_"+i+"_10").val('').val(to_Fixed(total_amt/qty));
           $("#td_data_"+i+"_9").val('').val(to_Fixed(total_amt));
           $("#td_data_"+i+"_9_display").html(to_Fixed(total_amt));
           final_total();
         }
         
         /* ---------- CALCULATE GST END -------------*/

        
         /* ---------- Final Description of amount ------------*/
         function final_total(){
           

           var rowcount=$("#hidden_rowcount").val();
           var subtotal=parseFloat(0);
           
           var other_charges_per_amt=parseFloat(0);
           var other_charges_total_amt=0;
           var taxable=0;
          if($("#other_charges_input").val()!=null && $("#other_charges_input").val()!=''){
             
              other_charges_tax_id =$('option:selected', '#other_charges_tax_id').attr('data-tax');
             other_charges_input=$("#other_charges_input").val();
             if(other_charges_tax_id>0){

               other_charges_per_amt=(other_charges_tax_id * other_charges_input)/100;
             }
             
             taxable=parseFloat(other_charges_per_amt)+parseFloat(other_charges_input);//Other charges input
             other_charges_total_amt=parseFloat(other_charges_per_amt)+parseFloat(other_charges_input);
           }
           else{
             //$("#other_charges_amt").html('0.00');
           }
           
         
           var tax_amt=0;
           var actual_taxable=0;
           var total_quantity=0;
         
           for(i=1;i<=rowcount;i++){
         
             if(document.getElementById("td_data_"+i+"_3")){
               //supplier_id must exist
               if($("#td_data_"+i+"_3").val()!=null && $("#td_data_"+i+"_3").val()!=''){
                    actual_taxable=actual_taxable+ + +(parseFloat($("#td_data_"+i+"_13").val()) * parseFloat($("#td_data_"+i+"_3").val()));
                    subtotal=subtotal+ + +parseFloat($("#td_data_"+i+"_9").val());
                    if($("#td_data_"+i+"_7").val()>=0){
                      tax_amt=tax_amt+ + +$("#td_data_"+i+"_7").val();
                    }   
                    total_quantity +=parseFloat($("#td_data_"+i+"_3").val());
                }
                   
             }//if end
           }//for end
           
          
          //Show total Purchase Quantitys
           $(".total_quantity").html(format_qty(total_quantity));

           //Apply Output on screen
           //subtotal
           if((subtotal!=null || subtotal!='') && (subtotal!=0)){
             
             //subtotal
             $("#subtotal_amt").html(to_Fixed(subtotal));
             
             //other charges total amount
             $("#other_charges_amt").html(to_Fixed(other_charges_total_amt));
             
             //other charges total amount
            

             taxable=taxable+subtotal;
             
             //discount_to_all_amt
            // if($("#discount_to_all_input").val()!=null && $("#discount_to_all_input").val()!=''){
                 var discount_input=parseFloat($("#discount_to_all_input").val());
                 discount_input = isNaN(discount_input) ? 0 : discount_input;
                 var discount=0;
                 if(discount_input>0){
                     var discount_type=$("#discount_to_all_type").val();
                     if(discount_type=='in_fixed'){
                       taxable-=discount_input;
                       discount=discount_input;
                       //Minus
                     }
                     else if(discount_type=='in_percentage'){
                         discount=(taxable*discount_input)/100;
                        taxable-=discount;
             
                     }
                 }
                 else{
                    //discount += $("#")
                 }
                   discount=parseFloat(discount);
                   
                    $("#discount_to_all_amt").html(to_Fixed(discount));  
                    $("#hidden_discount_to_all_amt").val(to_Fixed(discount));  
             //}
             //subtotal_round=Math.round(taxable);
             subtotal_round=round_off(taxable);//round_off() method custom defined
             subtotal_diff=subtotal_round-taxable;
         
             $("#round_off_amt").html(to_Fixed(subtotal_diff)); 
             $("#total_amt").html(to_Fixed(subtotal_round)); 
             $("#hidden_total_amt").val(to_Fixed(subtotal_round)); 
           }
           else{
             $("#subtotal_amt").html('0.00'); 
             
             $("#tax_amt").html('0.00'); 
           }
           
          // adjust_payments();
          //alert("final_total() end");
         }
         /* ---------- Final Description of amount end ------------*/
          
         function removerow(id){//id=Rowid
         $("#row_"+id).remove();
         $("#row_"+id+"_batch").remove();
         if($("#purchase_items_container .purchase-item-card").length==0){
           $("#purchase_items_empty").show();
         }
         final_total();
         failed.currentTime = 0;
         failed.play();
         }
               
     

    function enable_or_disable_item_discount(){
      /*var discount_input=parseFloat($("#discount_to_all_input").val());
      discount_input = isNaN(discount_input) ? 0 : discount_input;
      if(discount_input>0){
        $(".item_discount").attr({
          'readonly': true,
          'style': 'border-color:red;cursor:no-drop',
        });
      }
      else{
        $(".item_discount").attr({
          'readonly': false,
          'style': '',
        });
      }*/

      var rowcount=$("#hidden_rowcount").val();
      for(k=1;k<=rowcount;k++){
       if(document.getElementById("tr_item_id_"+k)){
         console.log("Hello="+k);
         calculate_tax(k);
       }//if end
     }//for end

      //final_total();
    }

    //Purchase Items Modal Operations Start


    function show_purchase_item_modal(row_id){

      $('#purchase_item').modal('toggle');
      $("#popup_tax_id").select2();

      //Find the item details
      var item_name = $("#td_data_"+row_id+"_1").html();
      var tax_type = $("#tr_tax_type_"+row_id).val();
      var tax_id = $("#tr_tax_id_"+row_id).val();
      var description = $("#description_"+row_id).val();

      /*Discount*/
      var item_discount_input = $("#item_discount_input_"+row_id).val();
      var item_discount_type = $("#item_discount_type_"+row_id).val();

      //Set to Popup
      $("#item_discount_input").val(item_discount_input);
      $("#item_discount_type").val(item_discount_type).select2();

      $("#popup_item_name").html(item_name);
      $("#popup_tax_type").val(tax_type).select2();
      $("#popup_tax_id").val(tax_id).select2();
      $("#popup_description").val(description);
      $("#popup_row_id").val(row_id);

    }


     function set_info(){
      var row_id = $("#popup_row_id").val();
      var tax_type = $("#popup_tax_type").val();
      var tax_id = $("#popup_tax_id").val();
      var description = $("#popup_description").val();
      var tax_name = ($('option:selected', "#popup_tax_id").attr('data-tax-value'));
      var tax = parseFloat($('option:selected', "#popup_tax_id").attr('data-tax'));

      /*Discounr*/
      var item_discount_input = $("#item_discount_input").val();
      var item_discount_type = $("#item_discount_type").val();

      //Set it into row 
      $("#item_discount_input_"+row_id).val(item_discount_input);
      $("#item_discount_type_"+row_id).val(item_discount_type);

      $("#tr_tax_type_"+row_id).val(tax_type);
      $("#tr_tax_id_"+row_id).val(tax_id);
      $("#tr_tax_value_"+row_id).val(tax);//%
      $("#description_"+row_id).val(description);
      $("#td_data_"+row_id+"_15").html(tax_name);
      
      calculate_tax(row_id);
      $('#purchase_item').modal('toggle');
    }

    
    function set_tax_value(row_id){
      //get the purchase price of the item
      var tax_type = $("#tr_tax_type_"+row_id).val();
      var tax = $("#tr_tax_value_"+row_id).val(); //%
      var qty=$("#td_data_"+row_id+"_3").val();
          qty = (isNaN(qty)) ? 0 :qty;
      var purchase_price = parseFloat($("#td_data_"+row_id+"_4").val());
          purchase_price = (isNaN(purchase_price)) ? 0 :purchase_price;
          purchase_price = purchase_price * qty;

      /*Discount*/
      var item_discount_type = $("#item_discount_type_"+row_id).val();
      var item_discount_input = parseFloat($("#item_discount_input_"+row_id).val());
          item_discount_input = (isNaN(item_discount_input)) ? 0 :item_discount_input;

      //Calculate discount      
      var discount_amt=(item_discount_type=='Percentage') ? ((purchase_price) * item_discount_input)/100 : (item_discount_input*qty);
      purchase_price-=parseFloat(discount_amt);

      var tax_amount = (tax_type=='Inclusive') ? calculate_inclusive(purchase_price,tax) : calculate_exclusive(purchase_price,tax);
      
      $("#td_data_"+row_id+"_8").val(to_Fixed(discount_amt));

      $("#td_data_"+row_id+"_5").val(to_Fixed(tax_amount));
    }
    //Purchase Items Modal Operations End
    
</script>
      <!-- UPDATE OPERATIONS -->
      <script type="text/javascript">
         <?php if(isset($purchase_id)){ ?> 
             $(document).ready(function(){
                /*$("#warehouse_id").attr('readonly',true);*/
                $("#store_id").attr('readonly',true);
                var base_url='<?= base_url();?>';
                var purchase_id='<?= $purchase_id;?>';
                $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
                $.post(base_url+"purchase/return_purchase_list/"+purchase_id,{},function(result){
                  $('#purchase_items_container').append(result);
                  $("#purchase_items_empty").hide();
                  $("#hidden_rowcount").val(parseInt(<?=$items_count;?>)+1);
                  // Initialize datepickers on loaded batch date fields
                  $('#purchase_items_container .datepicker').datepicker({
                      autoclose: true,
                      format: 'dd-mm-yyyy',
                      todayHighlight: true
                  });
                  toggle_batch_fields();
                  success.currentTime = 0;
                  success.play();
                  enable_or_disable_item_discount();
                  $(".overlay").remove();
              }); 
             });
         <?php }?>
      </script>
      <!-- UPDATE OPERATIONS end-->

      <!-- Make sidebar menu hughlighter/selector -->
      <script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
</body>
</html>
