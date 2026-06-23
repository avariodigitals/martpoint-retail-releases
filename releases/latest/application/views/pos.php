<!DOCTYPE html>
<html>

<head>
<!-- TABLES CSS CODE -->
<?php include"comman/code_css.php"; ?>
<!-- iCheck -->
  <link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/iCheck/square/blue.css">
  <link rel="stylesheet" href="<?= base_url('theme/css/pos.css') ?>">
  <!-- MartPoint Retail Reskin -->
  <link rel="stylesheet" href="<?php echo $theme_link; ?>dist/css/martpoint-reskin.css">
</head>

<!-- ADD THE CLASS layout-top-nav TO REMOVE THE SIDEBAR. -->
<body class="hold-transition skin-blue layout-top-nav">
  <?php $CI =& get_instance(); ?>
<div class="wrapper">
  
  
  <header class="main-header">
    <nav class="navbar navbar-static-top">
      <div class="container">
        <div class="navbar-header">
          <a href="<?php echo $base_url; ?>dashboard" class="navbar-brand" title="Go to Dashboard!"><b class="hidden-xs"><?= $this->session->userdata('store_name'); ?></b><b class="hidden-lg">MP</b></a>
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
            <i class="fa fa-bars"></i>
          </button>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
          <ul class="nav navbar-nav">
            <?php if($CI->permissions('sales_view')) { ?>
            <li class=""><a href="<?php echo $base_url; ?>sales" title="View Sales List!"><i class="fa fa-list text-yellow" ></i> <span><?= $this->lang->line('sales_list'); ?></span></a></li>
            <?php } ?>
            
            <?php if($CI->permissions('customers_view')) { ?>
            <li class=""><a href="<?php echo $base_url; ?>customers/" title="View Customers List"><i class="fa  fa-group  text-yellow " ></i> <span><?= $this->lang->line('customers_list'); ?></span></a></li>
            <?php } ?>
            <?php if($CI->permissions('items_view')) { ?>
            <li class=""><a href="<?php echo $base_url; ?>items/" title="View Items List"><i class="fa  fa-cubes text-yellow " ></i> <span><?= $this->lang->line('items_list'); ?></span></a></li>
            <?php } ?>
            <?php if($CI->permissions('sales_add')) { ?>
            <li class=""><a href="<?php echo $base_url; ?>pos" title="Create New POS Invoice"><i class="fa fa-calculator text-yellow " ></i> <span><?= $this->lang->line('new_invoice'); ?></span></a></li>
            <?php } ?>
          </ul>
        </div>
        <!-- /.navbar-collapse -->
        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav">
            
            <!-- User Account Menu -->
            <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Click To View Hold Invoices">
             
              <span class=""><?= $this->lang->line('hold_list'); ?></span>
              <span class="label label-danger hold_invoice_list_count"><?=$tot_count?></span>
            </a>

            <ul class="dropdown-menu dropdown-width-lg">
              
              <!-- Menu Body -->
              <li class="user-body">
                <div class="row">
                  <div class="col-xs-12 text-center " style="max-height:300px;overflow-y: scroll;">
                    <table class="table table-bordered" width="100%">
                      <thead>
                      <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Ref.ID</th>
                        <th>Action</th>
                      </tr>
                      </thead>
                      <tbody id="hold_invoice_list" >
                       <?=$result?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <!-- /.row -->
              <!--</li>-->
            </ul>
          </li>

            <!-- Messages: style can be found in dropdown.less-->
            <li class="hidden-xs" id="fullscreen"><a title="Fullscreen On/Off"><i class="fa fa-tv text-white" ></i> </a></li>
            <li class="text-center" id="clockInBtnWrap">
              <a id="clockInBtn" title="Clock In" href="#"><i class="fa fa-clock-o text-yellow"></i><b class="hidden-xs"> Clock In</b></a>
            </li>
            <li class="text-center" id="">
            <a title="Dashboard" href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard text-yellow" ></i><b class="hidden-xs"><?= $this->lang->line('dashboard'); ?></b></a>
          </li>

            <!-- User Account Menu -->
            <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="<?php echo get_profile_picture(); ?>" class="user-image" alt="User Image">
              <span class="hidden-xs"><?php print ucfirst($this->session->userdata('display_name')); ?></span>
            </a>

            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <img src="<?php echo get_profile_picture(); ?>" class="img-circle" alt="User Image">

                <p>
                 <?php print ($this->session->userdata('display_name')); ?>
                  <small>Year <?=date("Y");?></small>
                  <small class='text-uppercase text-bold'>Role: <?=$this->session->userdata('role_name');?></small>
                </p>
              </li>
              <!-- Menu Body -->
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                  <a href="<?php echo $base_url; ?>users/edit/<?= $this->session->userdata('inv_userid'); ?>" class="btn btn-default btn-flat">Profile</a>
                </div>
                <div class="pull-right">
                  <a href="<?php echo $base_url; ?>logout" class="btn btn-default btn-flat">Sign out</a>
                </div>
              </li>
            </ul>
          </li>
          </ul>
        </div>
        <!-- /.navbar-custom-menu -->
      </div>
      <!-- /.container-fluid -->
    </nav>
  </header>

  <?php $css = ($this->session->userdata('language')=='Arabic' || $this->session->userdata('language')=='Urdu') ? 'margin-right: 0 !important;': '';?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper" style="<?=$css;?>">
    <!-- Content Header (Page header) -->
   <!--  <section class="content-header">
      <h1>
        General Form Elements
        <small>Preview</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Forms</a></li>
        <li class="active">General Elements</li>
      </ol>
    </section> -->

    <?php if(!empty($needs_clock_in)): ?>
    <div class="callout callout-warning" id="posClockInBanner" style="margin:10px 15px 0 15px;border-radius:8px;">
      <h4><i class="fa fa-clock-o"></i> Clock In Required</h4>
      <p>You must <strong>clock in</strong> before you can process sales. <button type="button" class="btn btn-xs btn-warning" onclick="$('#clockInBtn').trigger('click');"><i class="fa fa-sign-in"></i> Clock In Now</button></p>
    </div>
    <?php endif; ?>

    <!-- **********************MODALS***************** -->
    <?php include"modals/modal_customer.php"; ?>
    <?php include"modals/modal_sales_item.php"; ?>
    <?php include"modals/modal_item.php"; ?>
    <?php include"modals/modal_item_or_service.php"; ?>
    
    <?php /*include"modals/modal_service.php";*/ ?>

    
    <!-- **********************MODALS END***************** -->
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <!-- left column -->
        <div class="col-md-7">
         
          <!-- general form elements -->
          <div class="box box-primary">
            <!-- form start -->
            <form class="form-horizontal" id="pos-form" >
            <div class="box-header with-border" style="padding-bottom: 0px;">
              <div class="row" >
                <div class="col-md-12" >
               
                  
                <?php if(isset($sales_id)): ?>
                  <?php if($CI->permissions('sales_add')) { ?>
                  <div class="col-md-4 pull-right">
                    <a href='<?= $base_url;?>pos' class="btn btn-primary pull-right">New Invoice</a>
                  </div>
                  <?php } ?>
                <?php endif; ?>
                
              </div>
              </div>
               
            
            
            
          </div>
            <!-- /.box-header -->
            
              <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
              <input type="hidden" value='0' id="hidden_rowcount" name="hidden_rowcount">
              <input type="hidden" value='' id="hidden_invoice_id" name="hidden_invoice_id">
              <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
              <input type="hidden" class="scroll_or_not" value="true">

              
              <input type="hidden" value='' id="walk_in_customer_name" value="<?=get_walk_in_customer_name();?>">
              
              <!-- **********************MODALS***************** -->
             <?php include"modals_pos_payment/modal_payments_multi.php"; ?>

             <?php include"modals/modal_terms.php"; ?>

              <!-- **********************MODALS END***************** -->
              <!-- **********************MODALS***************** -->
              <div class="modal fade" id="discount-modal" tabindex='-1'>
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                      <h4 class="modal-title">Set Discount</h4>
                    </div>
                    <div class="modal-body">
                      
                        <div class="row">
                          <div class="col-md-6">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="discount_input">Discount</label>
                                <input type="text" class="form-control" id="discount_input" name="discount_input" placeholder="" value="0">
                              </div>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="box-body">
                              <div class="form-group">
                                <label for="discount_type">Discount Type</label>
                                <select class="form-control" id='discount_type' name="discount_type">
                                  <option value='in_percentage'>Per%</option>
                                  <option value='in_fixed'>Fixed</option>
                                </select>
                              </div>
                            </div>
                          </div>
                        </div>
                     
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
                      <button type="button" class="btn btn-primary discount_update">Update</button>
                    </div>
                  </div>
                  <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
              </div>
              <!-- /.modal -->
              <!-- **********************MODALS END***************** -->
              <div class="box-body">                
              <!-- Store Code -->
              <?php $store_id=''; ?>
            <?php 
             /*if(store_module() && is_admin()) {$this->load->view('store/store_code',array('show_store_select_box_2'=>true,'store_id'=>$store_id)); }else{*/
                echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>";
              echo "<input type='hidden' name='store_name' id='store_name' value='".$this->session->userdata('store_name')."'>";
              /*}*/
              ?>
            <!-- Store Code end -->


              <div class="martpoint-pos-header-grid">
                <div class="mp-col-left">
                  <div class="input-group" data-toggle="tooltip" title="Branch">
                    <span class="input-group-addon"><i class="fa fa-building text-red"></i></span>
                     <select class="form-control select2" id="warehouse_id" name="warehouse_id"  style="width: 100%;"  >
                          <?= get_warehouse_select_list($warehouse_id,get_current_store_id()); ?>
                      </select>
                  </div>
                  <div class="input-group" data-toggle="tooltip" title="Customer">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                     <select class="form-control select2" id="customer_id" name="customer_id"  style="width: 100%;"  >
                      </select>
                    <span class="input-group-addon pointer" data-toggle="modal" data-target="#customer-modal" title="New Customer?"><i class="fa fa-user-plus text-primary fa-lg"></i></span>
                  </div>
                  <div id="walkin-warning" class="alert alert-warning" style="display:none;margin-top:6px;padding:6px 10px;font-size:12px;">
                    <i class="fa fa-exclamation-triangle"></i> <strong>Walk-in Customer:</strong> Must pay in full — any payment method allowed. Credit is not allowed.
                  </div>
                    <span class="customer_points text-success" style="display: none;"></span>
                    <lable><?= $this->lang->line('previous_due'); ?> :<label class="customer_previous_due text-red" style="font-size: 18px;"><?=store_number_format(0)?></label></lable>
                </div>
                <div class="mp-col-middle">
                  <div class="input-group" data-toggle="tooltip" title="Invoice Initial Code">
                    <span class="input-group-addon"><i class="fa fa-th-list"></i></span>
                     <input type="text" class="form-control" placeholder="Invioce Initial Code" id="init_code" name="init_code" value="<?= $init_code ?>">
                  </div>
                  <div class="input-group" data-toggle="tooltip" title="Select Items">
                    <span class="input-group-addon"><i class="fa fa-barcode"></i></span>
                     <input type="text" class="form-control" placeholder="Item name / Barcode / Itemcode" id="item_search">
                     <span class="input-group-addon pointer show_item_service" title="New Item?"><i class="fa fa-plus text-primary fa-lg"></i></span>
                  </div>
                  <div class="input-group" style="margin-top:4px;" data-toggle="tooltip" title="Price Type">
                    <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                    <select class="form-control" id="price_type" name="price_type" style="width:100%;">
                      <option value="retail" selected>Retail (MRP)</option>
                      <option value="wholesale">Wholesale</option>
                    </select>
                  </div>
                </div>
                <div class="mp-col-right">
                     <input type="text" class="form-control" data-toggle="tooltip" title="Invoice Count ID" placeholder="Invioce Number" id="count_id" name="count_id" value="<?= $count_id ?>" <?= !is_admin() ? 'readonly' : ''; ?>>
                </div>
              </div>
             
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <div class="col-sm-12" style="overflow-y:auto;height: 300px;border:1px solid #337ab7;" >
                      <table class="table table-condensed table-bordered  table-responsive items_table" style="">
                        <thead class="bg-gray">
                          <th width="30%"><?= $this->lang->line('item_name'); ?></th>
                          <th width="10%"><?= $this->lang->line('stock'); ?></th>
                          <th width="25%"><?= $this->lang->line('quantity'); ?></th>
                          <th width="15%"><?= $this->lang->line('price'); ?></th>
                          <th width="10%"><?= $this->lang->line('discount'); ?></th>
                          <th width="10%"><?= $this->lang->line('tax'); ?></th>
                          <th width="15%"><?= $this->lang->line('subtotal'); ?></th>
                          <th width="5%"><i class="fa fa-close"></i></th>
                        </thead>
                        <tbody id="pos-form-tbody" style="font-size: 16px;font-weight: bold;overflow: scroll;">
                          <!-- body code -->
                        </tbody>        
                        <tfoot>
                          <!-- footer code -->
                        </tfoot>              
                      </table>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                 <!-- SMS Sender while saving -->
                      <?php 
                         //Change Return
                          $send_sms_checkbox='disabled';
                          if($CI->is_sms_enabled()){
                            if(!isset($sales_id)){
                              $send_sms_checkbox='checked';  
                            }else{
                              $send_sms_checkbox='';
                            }
                          }

                    ?>
                   
                    <div class="col-xs-4 ">
                           <div class="checkbox icheck">
                            <label>
                              <input type="checkbox" <?=$send_sms_checkbox;?> class="form-control" id="send_sms" name="send_sms" > <label for="sales_discount" class=" control-label"><?= $this->lang->line('send_sms_to_customer'); ?>
                                <i class="hover-q " data-container="body" data-toggle="popover" data-placement="top" data-content="If checkbox is Disabled! You need to enable it from SMS -> SMS API <br><b>Note:<i>Walk-in Customer will not receive SMS!</i></b>" data-html="true" data-trigger="hover" data-original-title="" title="Do you wants to send SMS ?">
                                  <i class="fa fa-info-circle text-maroon text-black hover-q"></i>
                                </i>
                              </label>
                            </label>
                          </div>
                    </div> 
                    
                    <div class="col-md-6">
                        <label class="control-label pull-right hide div2 text-blue">
                          <?= $this->lang->line('couponApplied'); ?>: <span class="coupon_value">0.00 </span> <span class="coupon_type"></span>
                        </label>
                      </div>

                      <div class="col-md-2">
                      <label class="control-label pull-left text-blue pointer" toggle="tooltip" title="<?= $this->lang->line('edit_invoice_tc')?>" data-toggle="modal" data-target="#terms-modal">
                         <span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span> T&C
                        </label>
                    </div>
                </div>
           
              </div>
              <!-- /.box-body -->

              <div class="box-footer bg-gray">
                <div class="row">
                  <div class="col-md-3 text-right">
                          <label> <?= $this->lang->line('quantity'); ?>:</label><br>
                          <span class="text-bold tot_qty"></span>
                  </div>
                  <div class="col-md-3 text-right">
                          <label><?= $this->lang->line('total_amount'); ?>:</label><br>
                          <?= $CI->currency('<span style="font-size: 19px;" class="tot_amt text-bold"></span>');?>
                  </div>
                  <div class="col-md-3 text-right">
                          <label><?= $this->lang->line('total_discount'); ?>:<a class="fa fa-pencil-square-o cursor-pointer" data-toggle="modal" data-target="#discount-modal"></a></label><br>
                          <?= $CI->currency('<span style="font-size: 19px;" class="tot_disc text-bold"></span>');?>
                  </div>
                  <div class="col-md-3 text-right">
                          <label><?= $this->lang->line('grand_total'); ?>:</label><br>
                          <?= $CI->currency('<span style="font-size: 19px;" class="tot_grand text-bold"></span>');?>
                  </div>
                </div>
               
               <div class="row">
                
                  <?php if(isset($sales_id)){ $btn_id='update';$btn_name="Cash"; ?>
                    <input type="hidden" name="sales_id" id="sales_id" value="<?php echo $sales_id;?>"/>
                  <?php } else{ $btn_id='save';$btn_name="Cash";} ?>

                  <div class="col-md-12 text-right">

                    <div class="col-sm-3">
                      <button type="button" id="hold_invoice" name="" class="btn bg-maroon btn-block btn-flat btn-lg btnhold" title="Hold Invoice [Alt+H]" style="">
                      <i class="fa fa-hand-paper-o" aria-hidden="true"></i>
                       Hold
                    </button>
                    </div>
                    <div class="col-sm-3">
                      <button type="button" id="" name="" class="btn btn-primary btnhold btn-block btn-flat btn-lg show_payments_modal" title="Multiple Payments [Alt+M]" style="">
                            <i class="fa fa-credit-card" aria-hidden="true"></i>
                             Multiple
                          </button>
                    </div>
                    <div class="col-sm-3">
                      <button type="button" id="<?php echo "show_cash_modal";?>" name="" class="btn btnhold btn-success btn-block btn-flat btn-lg Alt_c" title="By Cash & Save [Alt+C]" style="">
                            <i class="fa fa-money" aria-hidden="true"></i>
                             <?php echo $btn_name;?>
                          </button>
                    </div>

                    <div class="col-sm-3">
                      <button type="button" id="pay_all" name="" class="btn bg-purple btnhold btn-block btn-flat btn-lg Alt_a" title="By Cash & Save [Alt+A]" style="">
                            <i class="fa fa-money" aria-hidden="true"></i>
                             Pay All
                          </button>
                    </div>
                    

                          
                  </div>
                </div>
              </div>
            </form>
          </div>
          <!-- /.box -->
        </div>
        <!--/.col (left) -->
        <!-- right column -->
        <div class="col-md-5">
          <!-- Horizontal Form -->
          <div class="box box-info">
            <!-- form start -->
            
              <div class="box-body">
                
              <div class="row">

                <div class="col-md-6">
                  <div class="input-group input-group-md">
                      <select class="form-control select2" id="category_id" name="category_id"  style="width: 100%;"  >
                        <option value="">-All Categories-</option>
                        <?= get_categories_select_list();  ?>
                      </select>
                          <span class="input-group-btn">
                            <button type="button" class="btn text-blue btn-flat reset_categories" title="Reset Categories" data-toggle="tooltip" data-placement="top">
                              <i class="fa fa-undo"></i>
                            </button>
                          </span>
                    </div>
                </div>


                <div class="col-md-6">
                  <div class="input-group input-group-md">
                      <select class="form-control select2" id="brand_id" name="brand_id"  style="width: 100%;"  >
                        <option value="">-All Brands-</option>
                        <?= get_brands_select_list();  ?>
                      </select>
                          <span class="input-group-btn">
                            <button type="button" class="btn text-blue btn-flat reset_brands" title="Reset Brand" data-toggle="tooltip" data-placement="top">
                              <i class="fa fa-undo"></i>
                            </button>
                          </span>
                    </div>
                </div>                

              </div><!-- row end -->

              <br>
              <div class="row">

                <div class="col-md-12">
                  <div class="input-group input-group-md">
                   
                      <input type="text" class="form-control" data-toggle="tooltip" title="Enter Item Name" placeholder="Item Name" id="item_name" name="item_name">

                          <span class="input-group-btn">
                            <button type="button" class="btn text-blue btn-flat reset_item_name" title="Reset Item Name" data-toggle="tooltip" data-placement="top">
                              <i class="fa fa-undo"></i>
                            </button>
                          </span>
                    </div>
                </div>               

              </div><!-- row end -->


             
              <div class="row">
                <div class="col-md-12">
                  <!-- <div class="form-group"> -->
                   <!--  <div class="col-sm-12"> -->
                      <!-- <style type="text/css">
                        
                      </style> -->
                     

                            <section class="content .sec_div" >
                              <div class="row search_div" style="overflow-y: scroll;min-height: 100px;height: 550px">
                              </div>
                              <h4 class='text-danger text-center error_div' style="display: none;">No More Records Found</h4>
                            </section>
                            <div class="ajax-load text-center" style="display:none;">
                                <button type="button" class="btn btn-default btn-lrg ajax" title="Ajax Request">
                                <i class="fa fa-spin fa-refresh"></i>&nbsp; Loading More Data
                              </button>
                              </div>
                      
                         
                    <!-- </div> -->
                  <!-- </div> -->
                </div>
              </div>
           
              </div>
              <!-- /.box-body -->

              
           
          </div>
          <!-- /.box -->
          
          <!-- /.box -->
        </div>
        <!--/.col (right) -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <?php include"footer.php";?>
</div>
<!-- ./wrapper -->

<!-- SOUND CODE -->
<?php include"comman/code_js_sound.php"; ?>
<!-- GENERAL CODE -->
<?php include"comman/code_js.php"; ?>

<!-- iCheck -->
<script src="<?php echo $theme_link; ?>plugins/iCheck/icheck.min.js"></script>

<script src="<?php echo $theme_link; ?>js/fullscreen.js"></script>
<script src="<?php echo $theme_link; ?>js/modals.js"></script>
<script src="<?php echo $theme_link; ?>js/modals/modal_item.js"></script>
<script src="<?php echo $theme_link; ?>js/ajaxselect/customer_select_ajax.js"></script>  

<!-- DROP DOWN -->
<script src="<?php echo $theme_link; ?>dist/js/bootstrap3-typeahead.min.js"></script>  
<!-- DROP DOWN END-->

<script type="text/javascript">
  
  var warehouse_module=false;
  <?php if(warehouse_module() && warehouse_count()>1){ ?> 
    warehouse_module=true;
  <?php } ?>

  var store_module=false;
  <?php if(store_module()){ ?> 
    store_module=true;
  <?php } ?>
  var walkin_customer_id = <?=json_encode($walkin_customer_id ?? null);?>;
</script>
<script src="<?php echo $theme_link; ?>js/pos.js?v=12"></script>
<script src="<?php echo $theme_link; ?>js/approval-modal.js?v=4"></script>
<script>
  window.base_url = "<?php echo $base_url; ?>";
  window.csrfName = "<?php echo $this->security->get_csrf_token_name(); ?>";
  window.csrfHash = "<?php echo $this->security->get_csrf_hash(); ?>";
  window.currentUserId = "<?php echo $this->session->userdata('inv_userid') ?? '0'; ?>";

        //Customer Selection Box Search
         function getCustomerSelectionId() {
           return '#customer_id';
         }

         function isWalkInCustomer(){
           var cid = $(getCustomerSelectionId()).val();
           return (walkin_customer_id && cid == walkin_customer_id);
         }

         $(document).ready(function () {

            // var customer_id = "<?= (!empty($customer_id)) ? $customer_id : '';  ?>";

            // autoLoadFirstCustomer(customer_id);

         });
         //Customer Selection Box Search - END


    var base_url=$("#base_url").val();
    /*$("#store_id").on("change",function(){
      var store_id=$(this).val();
      $.post(base_url+"sales/get_customers_select_list",{store_id:store_id},function(result){
          $("#customer_id").html('').append(result).select2();
          <?php if(isset($sales_id) && empty($sales_id)){ ?>
            $(".items_table > tbody").empty();
          <?php } ?>
          final_total();
      });
    });*/

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


  /*Branch*/
    $("#warehouse_id").on("change",function(){
      var warehouse_id=$(this).val();
      $(".items_table > tbody").empty();
      get_details(null,true);
      final_total();
    });
    /*Price Type*/
    $("#price_type").on("change",function(){
      $(".items_table > tbody").empty();
      get_details(null,true);
    });
    /*Branch end*/

  //RIGHT SIT DIV:-> FILTER ITEM INTO THE ITEMS LIST
  function search_it(){
  
  /*var input = $("#search_it").val();
  var item_count=$(".search_div .search_item").length;
  var error_count=item_count;
  for(i=0; i<item_count; i++){
    console.log("item_count ->"+i+" =>"+$("#item_"+i).html());
    console.log($("#item_"+i).html().toUpperCase().indexOf(input.toUpperCase()));
    if($("#item_"+i).html().toUpperCase().indexOf(input.toUpperCase())>-1){
      console.log("found");
      $("#item_"+i).show();
      $("#item_parent_"+i).show();
    }
    else{
     console.log("not-found"); 
     $("#item_"+i).hide();
     $("#item_parent_"+i).hide();
     error_count--;
    }
    if(error_count==0){
      $(".error_div").show();
    }
    else{
      $(".error_div").hide();
    }
    
  }*/
  }




//LEFT SIDE: ON CLICK ITEM ADD TO INVOICE LIST
function addrow(id='',item_obj=''){


    var item_id = (item_obj=='') ? $('#div_'+id).attr('data-item-id') : item_obj.item_id;
    var price_type = (item_obj=='') ? $('#price_type').val() || 'retail' : (item_obj.price_type || 'retail');

    //CHECK SAME ITEM ALREADY EXIST IN ITEMS TABLE
    var item_check=check_same_item(item_id, price_type);
    if(!item_check){return false;}
    var rowcount        =$("#hidden_rowcount").val();//0,1,2...
    var item_name = (item_obj=='') ? $('#div_'+id).attr('data-item-name') : item_obj.item_name; 

    var stock   =(item_obj=='') ? $('#div_'+id).attr('data-item-available-qty') : item_obj.stock;
        stock     =(parseFloat(stock)).toFixed(2);

    var tax_type   =(item_obj=='') ? $('#div_'+id).attr('data-item-tax-type') : item_obj.tax_type;  
    var tax_id   =(item_obj=='') ? $('#div_'+id).attr('data-item-tax-id') : item_obj.tax_id;  
    var tax_value   =(item_obj=='') ? $('#div_'+id).attr('data-item-tax-value') : item_obj.tax;
    
    var tax_name   =(item_obj=='') ? $('#div_'+id).attr('data-item-tax-name'):item_obj.tax_name;  
    var tax_amt   =(item_obj=='') ? $('#div_'+id).attr('data-item-tax-amt') : item_obj.item_tax_amt; 
    //var purchase_price   =(item_obj=='') ? $('#div_'+id).attr('data-purchase_price') : item_obj.purchase_price; 
    var discount_type   =(item_obj=='') ? $('#div_'+id).attr('data-discount_type') :item_obj.discount_type; 
    var discount   =(item_obj=='') ? $('#div_'+id).attr('data-discount') : item_obj.discount; 
 
    
    var service_bit   =(item_obj=='') ? $('#div_'+id).attr('data-service_bit') : item_obj.service_bit; 
    //var gst_per         =$('#div_'+id).attr('data-item-tax-per');
    //var gst_amt         =$('#div_'+id).attr('data-item-gst-amt');

    var item_cost     =(item_obj=='') ? $('#div_'+id).attr('data-item-cost') : item_obj.purchase_price;
    var batch_lot     =(item_obj=='') ? '' : (item_obj.batch_lot || '');
    var barcode       =(item_obj=='') ? '' : (item_obj.barcode || '');
    var sales_price;
    if(item_obj==''){
      sales_price = (price_type=='retail') ? $('#div_'+id).attr('data-item-mrp') : $('#div_'+id).attr('data-item-sales-price');
      if(!sales_price || sales_price=='undefined'){ sales_price = $('#div_'+id).attr('data-item-sales-price'); }
    } else {
      sales_price = item_obj.sales_price;
    }
    var sales_price_temp=sales_price;
        sales_price     =to_Fixed(sales_price);

    var quantity        ='<div class="input-group input-group-sm"><span class="input-group-btn"><button onclick="decrement_qty('+item_id+','+rowcount+')" type="button" class="btn btn-default btn-flat"><i class="fa fa-minus text-danger"></i></button></span>';
        quantity       +='<input typ="text" value="'+format_qty(1)+'" class="form-control no-padding text-center min_width" onchange="item_qty_input('+item_id+','+rowcount+')" id="item_qty_'+rowcount+'" name="item_qty_'+rowcount+'">';
        quantity       +='<span class="input-group-btn"><button onclick="increment_qty('+item_id+','+rowcount+')" type="button" class="btn btn-default btn-flat"><i class="fa fa-plus text-success"></i></button></span></div>';
    var sub_total       =(to_Fixed(1)*to_Fixed(sales_price));//Initial
    var remove_btn      ='<a class="fa fa-fw fa-trash-o text-red" style="cursor: pointer;font-size: 20px;" onclick="removerow('+rowcount+')" title="Delete Item?"></a>';

    var str=' <tr id="row_'+rowcount+'" data-row="0" data-item-id='+item_id+'>';/*item id*/
        str+='<td id="td_'+rowcount+'_0"><a data-toggle="tooltip" title="Click to Change Tax" class="pointer" id="td_data_'+rowcount+'_0" onclick="show_sales_item_modal('+rowcount+')">'+ item_name     +'</a> <i onclick="show_sales_item_modal('+rowcount+')" class="fa fa-edit pointer"></i></td>';/* td_0_0 item name*/ 
        str+='<td id="td_'+rowcount+'_1">'+ stock +'</td>';/* td_0_1 item available qty*/
        str+='<td id="td_'+rowcount+'_2">'+ quantity      +'</td>';/* td_0_2 item available qty*/
            info='<input data-toggle="tooltip" title="Click to Change Price" onclick="show_sales_item_modal('+rowcount+')" id="sales_price_'+rowcount+'" readonly name="sales_price_'+rowcount+'" type="text" class="form-control no-padding min_width pointer" value="'+sales_price+'">';
        str+='<td id="td_'+rowcount+'_3" class="text-right">'+ info   +'</td>';/* td_0_3 item sales price*/

        /*Discount*/
         info='<input data-toggle="tooltip" title="Click to Change" onclick="show_sales_item_modal('+rowcount+')" id="item_discount_'+rowcount+'" readonly name="item_discount_'+rowcount+'" type="text" class="form-control no-padding min_width pointer" value="0">';
         
        str+='<td id="td_'+rowcount+'_6" class="text-right">'+ info   +'</td>';

        /*Tax amt*/
        str+='<td id="td_'+rowcount+'_11"><input data-toggle="tooltip" title="Click to Change" id="td_data_'+rowcount+'_11" onclick="show_sales_item_modal('+rowcount+')" name="td_data_'+rowcount+'_11" type="text" class="form-control no-padding pointer min_width" readonly value="'+tax_amt+'"></td>';

        str+='<td id="td_'+rowcount+'_4" class="text-right"><input data-toggle="tooltip" title="Total" id="td_data_'+rowcount+'_4" name="td_data_'+rowcount+'_4" type="text" class="form-control no-padding pointer" readonly value="'+sub_total+'"></td>';/* td_0_4 item sub_total */
        str+='<td id="td_'+rowcount+'_5">'+ remove_btn    +'</td>';/* td_0_5 item gst_amt */

        str+='<input type="hidden" name="tr_item_id_'+rowcount+'" id="tr_item_id_'+rowcount+'" value="'+item_id+'">';
       // str+='<input type="hidden" id="tr_item_per_'+rowcount+'" name="tr_item_per_'+rowcount+'" value="'+gst_per+'">';
        str+='<input type="hidden" id="tr_sales_price_temp_'+rowcount+'" name="tr_sales_price_temp_'+rowcount+'" value="'+sales_price_temp+'">';
        str+='<input type="hidden" id="tr_tax_type_'+rowcount+'" name="tr_tax_type_'+rowcount+'" value="'+tax_type+'">';
        str+='<input type="hidden" id="tr_tax_id_'+rowcount+'" name="tr_tax_id_'+rowcount+'" value="'+tax_id+'">';
        str+='<input type="hidden" id="tr_tax_value_'+rowcount+'" name="tr_tax_value_'+rowcount+'" value="'+tax_value+'">';
        str+='<input type="hidden" id="description_'+rowcount+'" name="description_'+rowcount+'" value="">';
        str+='<input type="hidden" id="service_bit_'+rowcount+'" name="service_bit_'+rowcount+'" value="'+service_bit+'">';
        str+='<input type="hidden" id="price_type_'+rowcount+'" name="price_type_'+rowcount+'" value="'+price_type+'">';
        str+='<input type="hidden" id="batch_lot_'+rowcount+'" name="batch_lot_'+rowcount+'" value="'+batch_lot+'">';
        str+='<input type="hidden" id="barcode_'+rowcount+'" name="barcode_'+rowcount+'" value="'+barcode+'">';
        str+='<input id="item_discount_type_'+rowcount+'" name="item_discount_type_'+rowcount+'" type="hidden" value="'+discount_type+'">';
         str+='<input id="item_discount_input_'+rowcount+'" name="item_discount_input_'+rowcount+'" type="hidden" value="'+discount+'">';
        str+='</tr>';   

    //LEFT SIDE: ADD OR APPEND TO SALES INVOICE TERMINAL
    $('#pos-form-tbody').append(str);

    //LEFT SIDE: INCREMANT ROW COUNT
    $("#hidden_rowcount").val(parseFloat($("#hidden_rowcount").val())+1);
    failed.currentTime = 0;
    failed.play();
    //CALCULATE FINAL TOTAL AND OTHER OPERATIONS
    make_subtotal(item_id,rowcount);
  }

function update_price(row_id,item_cost){
  /*Input*/
  /*var sales_price=$("#sales_price_"+row_id).val();
  if(sales_price!='' || sales_price==0) {sales_price = parseFloat(sales_price); }

  Default set from item master
  var item_price=parseFloat($("#tr_sales_price_temp_"+row_id).val());

  if(sales_price<item_cost){
    //toastr["warning"]("Minimum Sales Price is "+item_cost);
    $("#sales_price_"+row_id).parent().addClass('has-error');
  }else{
    $("#sales_price_"+row_id).parent().removeClass('has-error');
  }*/

  make_subtotal($("#tr_item_id_"+row_id).val(),row_id);
}

function set_to_original(row_id,item_cost) {
  var originalPrice = parseFloat($("#tr_sales_price_temp_"+row_id).val()) || 0;
  var currentPrice = parseFloat($("#sales_price_"+row_id).val()) || 0;

  if(originalPrice !== currentPrice && typeof MPApproval !== 'undefined'){
    // IMMEDIATELY revert to original price while we wait for approval
    // (update_price already changed it on every keystroke)
    $("#sales_price_"+row_id).val(originalPrice);
    make_subtotal($("#tr_item_id_"+row_id).val(),row_id);

    MPApproval.request('price_override', {
      original_price: originalPrice,
      new_price: currentPrice
    }, function(approved, ctx){
      if(approved){
        // Re-apply the new price that the user wanted
        $("#sales_price_"+row_id).val(currentPrice);
        toastr['success']('Price change approved');
      } else {
        toastr['warning']('Price change cancelled — reverted to original');
      }
      make_subtotal($("#tr_item_id_"+row_id).val(),row_id);
    });
    return;
  }

  make_subtotal($("#tr_item_id_"+row_id).val(),row_id);
}


//INCREMENT ITEM
function increment_qty(item_id,rowcount){
  var service_bit=$("#service_bit_"+rowcount).val();
  var item_qty=$("#item_qty_"+rowcount).val();
  var stock=$("#td_"+rowcount+"_1").html();
  if(service_bit==1 || parseFloat(item_qty)<parseFloat(stock)){
    item_qty=parseFloat(item_qty)+1;
    $("#item_qty_"+rowcount).val(format_qty(item_qty));
  }
  make_subtotal(item_id,rowcount);
}
//DECREMENT ITEM
function decrement_qty(item_id,rowcount){
  var item_qty=$("#item_qty_"+rowcount).val();
  if(item_qty<=1){
    $("#item_qty_"+rowcount).val(format_qty(1));
    return;
  }
  $("#item_qty_"+rowcount).val(format_qty(parseFloat(item_qty)-1));
  make_subtotal(item_id,rowcount);
}
//LEFT SIDE: IF ITEM QTY CHANGED MANUALLY
function item_qty_input(item_id,rowcount){
  var item_qty=$("#item_qty_"+rowcount).val();
  var service_bit=$("#service_bit_"+rowcount).val();
  var stock=$("#td_"+rowcount+"_1").html();

  if(service_bit!=1){
    if(stock==0){
      toastr["warning"]("item Not Available in stock!");
    }
    if(parseFloat(item_qty)>parseFloat(stock)){
      $("#item_qty_"+rowcount).val(format_qty(stock));
      toastr["warning"]("Oops! You have only "+stock+" items in Stock");
    }
    if(item_qty==0){
      $("#item_qty_"+rowcount).val(format_qty(1));
      toastr["warning"]("You must have atlease one Quantity");
    }
  }

  make_subtotal(item_id,rowcount);
}

function zero_stock(){
  toastr["error"]("Out of Stock!");
  return;
}

function expired_item(){
  toastr["error"]("Expired Item! Cannot sell expired products.");
  failed.currentTime = 0;
  failed.play();
  return;
}
//LEFT SIDE: REMOVE ROW 
function removerow(id){//id=Rowid  
    $("#row_"+id).remove();
    failed.currentTime = 0;
    failed.play();
    final_total();
}

//MAKE SUBTOTAL


function make_subtotal(item_id,rowcount){
  set_tax_value(rowcount);

   //Find the Tax type and Tax amount
   var tax_type = $("#tr_tax_type_"+rowcount).val();
   var tax_amount = $("#td_data_"+rowcount+"_11").val();

  var sales_price     =$("#sales_price_"+rowcount).val();
  //var gst_per         =$("#tr_item_per_"+rowcount).val();
  var item_qty        =$("#item_qty_"+rowcount).val();

  var tot_sales_price =parseFloat(item_qty)*parseFloat(sales_price);
  //var gst_amt=(tot_sales_price * gst_per)/100;

  var subtotal        =parseFloat(tot_sales_price);
  /*Discounr*/
  var discount_amt    =$("#item_discount_"+rowcount).val();

  subtotal = (tax_type=='Inclusive') ? subtotal : parseFloat(subtotal) + parseFloat(tax_amount);

  subtotal -= parseFloat(discount_amt);
  
  $("#td_data_"+rowcount+"_4").val(to_Fixed(subtotal));
  final_total();
}


function calulate_discount(discount_input,discount_type,total){
  if(discount_type=='in_percentage'){
    return parseFloat((total*discount_input)/100);
  }
  else{//in_fixed
    return parseFloat(discount_input);
  }
}
//LEFT SIDE: FINAL TOTAL


function final_total(){
  var total=0;
  var item_qty=0;
  var rowcount=$("#hidden_rowcount").val();
  var discount_input=$("#discount_input").val();
  var discount_type=$("#discount_type").val();
  /*var other_charges=parseFloat($("#other_charges").val());
      other_charges = (isNaN(other_charges)) ? parseFloat(0) :other_charges;*/

  if($(".items_table tr").length>1){
    for(i=0;i<rowcount;i++){
      if(document.getElementById('tr_item_id_'+i)){
       // set_tax_value(i);
      //var tax_amt = parseFloat($("#td_data_"+i+"_11").val());
      total=parseFloat(total)+parseFloat($("#td_data_"+i+"_4").val());
      if(document.getElementById('item_qty_'+i)){
        item_qty=parseFloat(item_qty)+parseFloat($("#item_qty_"+i).val());
        item_qty = format_qty(item_qty);
      }
      }
    }//for end
  }//items_table
  
  //total+=other_charges;
  //total =round_off(total);
  
  var discount_amt=0;
  if(total>0){
    var discount_amt=calulate_discount(discount_input,discount_type,total);//return value 
  }

  var subtotal = total-discount_amt;

  var coupon_amt = discount_coupon_tot(subtotal);
      subtotal -=coupon_amt;
    
  set_total(item_qty,total,discount_amt,subtotal);
}
function set_total(tot_qty=0, tot_amt=0, tot_disc=0, tot_grand=0){
  $(".tot_qty   ").html(tot_qty);
  $(".tot_amt   ").html(to_Fixed(tot_amt));
  $(".tot_disc  ").html(to_Fixed(tot_disc));
  $(".tot_grand ").html(to_Fixed(round_off(tot_grand)));
}

//LEFT SIDE: FINAL TOTAL
function adjust_payments(){
  var total=0;
  var item_qty=parseFloat(0);
  var rowcount=$("#hidden_rowcount").val();
  var discount_input=$("#discount_input").val();
  var discount_type=$("#discount_type").val();
  //var discount_amt = parseFloat($(".tot_disc").html());

  if($(".items_table tr").length>1){
    for(i=0;i<rowcount;i++){
      if(document.getElementById('tr_item_id_'+i)){
      total=parseFloat(total)+parseFloat($("#td_data_"+i+"_4").val());
      if(document.getElementById('item_qty_'+i)){
        row_wise_item_qty = get_float_type_data("#item_qty_"+i);
        item_qty+=row_wise_item_qty;
      }

      }
    }//for end
  }//items_table


  //total =round_off(total);
  //Find customers payment

  var payments_row =get_id_value("payment_row_count");
  console.log("payments_row="+payments_row);
  var paid_amount =parseFloat(0);
  for (var i = 1; i <=payments_row; i++) {
    if(document.getElementById("amount_"+i)){
      var amount = parseFloat(get_id_value("amount_"+i));
          amount = isNaN(amount) ? 0 : amount;
          console.log("amount_"+i+"="+amount);
      paid_amount += amount;
    }
  }
  
  //RIGHT SIDE DIV
  var discount_amt=calulate_discount(discount_input,discount_type,total);//return value


  var change_return = 0;
  var subtotal = total-discount_amt;
  var coupon_amt = discount_coupon_tot(subtotal);
      subtotal -= coupon_amt;

  var balance = subtotal-paid_amount;

  if(balance < 0){
    //console.log("Negative");
    change_return = Math.abs(parseFloat(balance));
    balance = 0;
  }
  
  balance =round_off(balance);
  $(".sales_div_tot_qty").html(format_qty(item_qty));
  $(".sales_div_tot_amt").html((to_Fixed(total)));
  $(".sales_div_tot_discount").html((to_Fixed(discount_amt))); 
  $(".coupon_discount_div_amt").html((to_Fixed(coupon_amt))); 
  $("#coupon_discount_amt").val(coupon_amt); 
  $(".sales_div_tot_payble").html(round_off(subtotal)); 
  $(".sales_div_tot_paid").html(round_off(paid_amount));
  $(".sales_div_tot_balance ").html(round_off(balance)); 
  
  /**/
  $(".sales_div_change_return").html(to_Fixed(change_return)); 
  
}

$(document).ready(function(){
  get_coupon_details();
});
function check_same_item(item_id, price_type){
  price_type = price_type || 'retail';
  if($(".items_table tr").length>1){
    var rowcount=$("#hidden_rowcount").val();
    for(i=0;i<=rowcount;i++){
      if($("#tr_item_id_"+i).val()==item_id && $("#price_type_"+i).val()==price_type){
        increment_qty(item_id,i);
        failed.currentTime = 0;
        failed.play();
        return false;
      }
    }//end for
  }
  return true;
}




$(document).ready(function(){
  
  $("#store_id").trigger('change');

  var customer_id = "<?= (!empty($customer_id)) ? $customer_id : '';  ?>";

  autoLoadFirstCustomer(customer_id);

  // Toggle walk-in warning on customer change
  $("#customer_id").on('change', function(){
    if(isWalkInCustomer()){
      $("#walkin-warning").show();
    } else {
      $("#walkin-warning").hide();
    }
  });

  //FIRST TIME: LOAD
  //get_details();

  var first_div= parseFloat($(".content-wrapper").height());
  var second_div= parseFloat($("section").height());
  var items_table= parseFloat($(".items_table").height());
  $(".items_table").parent().css("height",(first_div-second_div)+items_table+230);/**/
  $(".search_div").height(((parseFloat(second_div)-parseFloat(items_table))>500) ? 500 : (second_div-items_table) );/**/
  $(".sec_div").height(((parseFloat(second_div)-parseFloat(items_table))>500) ? (parseFloat(second_div)-parseFloat(items_table)) : (second_div-items_table) );/**/

  



  //FIRST TIME: SET TOTAL ZERO
  set_total();

  <?php if(!empty($needs_clock_in)): ?>
  // Cashier not clocked in — disable checkout buttons
  $(function(){
    $('#hold_invoice, .show_payments_modal, #show_cash_modal, #pay_all').prop('disabled', true).attr('title','Clock in required');
    $('#pos-form').on('submit', function(e){ e.preventDefault(); toastr['warning']('Please clock in before processing sales.'); return false; });
    $(document).on('click', '.show_payments_modal, #show_cash_modal, #pay_all, #hold_invoice', function(e){
      if($(this).prop('disabled')){
        e.preventDefault();
        toastr['warning']('Please clock in before processing sales.');
        return false;
      }
    });
  });
  <?php endif; ?>

  //RIGHT DIV: FILTER INPUT BOX
 /* $("#search_it").on("keyup",function(){
    search_it();
  });*/

  //CATEGORY WISE ITEM FETCH FROM SERVER
  var show_only_searched=true;
  $("#category_id,#brand_id").on("change",function () {
      get_details(null,show_only_searched);
  });

  //DISCOUNT UPDATE — with approval check
  $(".discount_update").on("click",function () {
      var discountInput = parseFloat($("#discount_input").val()) || 0;
      var discountType = $("#discount_type").val();
      var total = parseFloat($(".tot_amt").text().replace(/[^0-9.]/g,'')) || 0;
      var percentage = (discountType === 'in_percentage') ? discountInput : 0;
      if(discountInput > 0 && typeof MPApproval !== 'undefined'){
        $('#discount-modal').modal('hide');
        MPApproval.request('discount', {percentage: percentage, amount: discountInput, total: total}, function(approved, ctx){
          if(approved){
            final_total();
          }
          $('#discount-modal').modal('hide');
        });
      } else {
        final_total();
        $('#discount-modal').modal('toggle');
      }
  });

  $("#item_name").on("keyup",function () {
      get_details(null,show_only_searched);
  });

  //RIGHT SIDE: CLEAR SEARCH BOX
 /* $(".show_all").on("click",function(){
    $("#search_it").val('').trigger("keyup");
    $("#category_id").val('').trigger("change");
  });*/

  //Reset Category & brand
  $(".reset_categories").on("click",function(){
      $("#category_id").val('').trigger("change");
  });
  $(".reset_brands").on("click",function(){
      $("#brand_id").val('').trigger("change");
  });
  $(".reset_item_name").on("click",function(){
      $("#item_name").val('');
      $("#brand_id").val('').trigger("change");
  });
  
  //UPDATE PROCESS START
 <?php if(isset($sales_id) && !empty($sales_id)){ ?>
    $("#store_id").attr('readonly',true);
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.get("<?php echo $base_url ?>pos/fetch_sales/<?php echo $sales_id ?>",{},function(result){
      console.log(result);

      result=result.split("<<<###>>>");
      $('#pos-form-tbody').append(result[0]);
      $('#discount_input').val(result[1]);
      $('#discount_type').val(result[2]);

      $('#invoice_terms').text(result[7]);
      /*if(store_module){
        $('#store_id').val(result[4]).select2();
      }
      else{*/
        $('#store_id').val(result[4]);
      /*}*/
      console.log("warehouse = "+result[5]);
      if(warehouse_module){
        $('#warehouse_id').val(result[5]).select2();
      }
      else{
        $('#warehouse_id').val(result[5]);
      }

      //$('#customer_id').val(result[3]).select2();
      //$("#customer_id").trigger("change");
      
      
      $("#hidden_rowcount").val(parseInt($(".items_table tr").length)-1);
      final_total();
      get_details();
      $(".overlay").remove();
      
      if(result[5]==1){
        $( "#binvoice" ).prop( "checked", true );
        $('#binvoice').parent('div').addClass('checked');
      }
    });
      //DISABLE THE HOLD BUTTON
      $("#hold_invoice,#show_cash_modal,#pay_all").attr('disabled',true).removeAttr('id');

 <?php } else{?>
  get_details();
 <?php } ?>
  //UPDATE PROCESS END


});//ready() end



//DATEPICKER INITIALIZATION
$('#order_date,#delivery_date,#cheque_date').datepicker({
      autoclose: true,
      format: 'dd-mm-yyyy',
      todayHighlight: true
    });
    $('#customer_dob,#birthday_person_dob').datepicker({
      calendarWeeks: true,
      todayHighlight: true,
      autoclose: true,
      format: 'dd-mm-yyyy',
      startView: 2
    });
    
    //Datemask dd-mm-yyyy
    //$("#customer_dob,#birthday_person_dob").inputmask("dd-mm-yyyy", {"placeholder": "dd-mm-yyyy"});

    //Timepicker
    /*$('.timepicker').timepicker({
      showInputs: false,
    });*/

    //Sale Items Modal Operations Start
    function show_sales_item_modal(row_id){
      $('#sales_item').modal('toggle');
      //$("#popup_tax_id").select2();

      //Find the item details
      var item_name = $("#td_data_"+row_id+"_0").html();
      var tax_type = $("#tr_tax_type_"+row_id).val();
      var tax_id = $("#tr_tax_id_"+row_id).val();
      var description = $("#description_"+row_id).val();
      var sales_price = $("#sales_price_"+row_id).val();

      /*Discount*/
      var item_discount_input = $("#item_discount_input_"+row_id).val();
      var item_discount_type = $("#item_discount_type_"+row_id).val();

      //Set to Popup
      $("#item_discount_input").val(item_discount_input);
      $("#item_discount_type").val(item_discount_type).select2();
      $("#popup_sales_price").val(sales_price);

      $("#popup_item_name").html(item_name);
      $("#popup_tax_type").val(tax_type).select2();
      $("#popup_tax_id").val(tax_id).select2();
      $("#popup_row_id").val(row_id);
      $("#popup_description").val(description);
    }

    function doSetInfo(row_id, tax_type, tax_id, description, item_discount_input, item_discount_type, newPrice){
      console.log('[doSetInfo] row:', row_id, 'price:', newPrice, 'discount:', item_discount_input);
      var tax = parseFloat($('option:selected', "#popup_tax_id").attr('data-tax'));
      if(typeof newPrice !== 'undefined'){
        $("#sales_price_"+row_id).val(newPrice);
      }
      $("#item_discount_input_"+row_id).val(item_discount_input);
      $("#item_discount_type_"+row_id).val(item_discount_type);
      $("#tr_tax_type_"+row_id).val(tax_type);
      $("#tr_tax_id_"+row_id).val(tax_id);
      $("#description_"+row_id).val(description);
      $("#tr_tax_value_"+row_id).val(tax);
      var item_id=$("#tr_item_id_"+row_id).val();
      make_subtotal(item_id,row_id);
      $('#sales_item').modal('hide');
    }

    function set_info(){
      var row_id = $("#popup_row_id").val();
      var tax_type = $("#popup_tax_type").val();
      var tax_id = $("#popup_tax_id").val();
      var description = $("#popup_description").val();
      var item_discount_input = $("#item_discount_input").val();
      var item_discount_type = $("#item_discount_type").val();
      var popupPrice = parseFloat($("#popup_sales_price").val()) || 0;

      var originalPrice = parseFloat($("#tr_sales_price_temp_"+row_id).val()) || 0;
      var currentPrice = parseFloat($("#sales_price_"+row_id).val()) || 0;
      var priceChanged = (popupPrice !== currentPrice && popupPrice > 0);

      var oldDiscount = parseFloat($("#item_discount_input_"+row_id).val()) || 0;
      var newDiscount = parseFloat(item_discount_input) || 0;
      var discountChanged = (oldDiscount !== newDiscount && newDiscount > 0);

      console.log('[set_info] priceChanged:', priceChanged, 'discountChanged:', discountChanged, 'popupPrice:', popupPrice, 'currentPrice:', currentPrice);

      // Price change needs approval — close modal first so SweetAlert isn't blocked
      if(priceChanged && typeof MPApproval !== 'undefined'){
        $('#sales_item').modal('hide');
        MPApproval.request('price_override', {
          original_price: originalPrice,
          new_price: popupPrice
        }, function(approved, ctx){
          console.log('[set_info] price_override approved:', approved);
          if(approved){
            // Discount also changed? Check that too
            if(discountChanged && typeof MPApproval !== 'undefined'){
              var percentage = (item_discount_type === 'Percentage') ? newDiscount : 0;
              MPApproval.request('discount', {percentage: percentage, amount: newDiscount}, function(discApproved, discCtx){
                if(discApproved){
                  doSetInfo(row_id, tax_type, tax_id, description, item_discount_input, item_discount_type, popupPrice);
                } else {
                  doSetInfo(row_id, tax_type, tax_id, description, 0, item_discount_type, popupPrice);
                }
              });
            } else {
              doSetInfo(row_id, tax_type, tax_id, description, item_discount_input, item_discount_type, popupPrice);
            }
          } else {
            toastr['warning']('Price change cancelled');
          }
        });
        return;
      }

      // Only discount changed — close modal first so SweetAlert isn't blocked
      if(discountChanged && typeof MPApproval !== 'undefined'){
        $('#sales_item').modal('hide');
        var percentage = (item_discount_type === 'Percentage') ? newDiscount : 0;
        MPApproval.request('discount', {percentage: percentage, amount: newDiscount}, function(approved, ctx){
          console.log('[set_info] discount approved:', approved);
          if(approved){
            doSetInfo(row_id, tax_type, tax_id, description, item_discount_input, item_discount_type, popupPrice);
          } else {
            toastr['warning']('Discount cancelled');
          }
        });
        return;
      }

      doSetInfo(row_id, tax_type, tax_id, description, item_discount_input, item_discount_type, popupPrice);
    }
    function set_tax_value(row_id){
      //get the sales price of the item
      var tax_type = $("#tr_tax_type_"+row_id).val();
      var tax = $("#tr_tax_value_"+row_id).val(); //%
      var qty=($("#item_qty_"+row_id).val());
          qty = (isNaN(qty)) ? 0 :qty;

      var sales_price = parseFloat($("#sales_price_"+row_id).val());
          sales_price = (isNaN(sales_price)) ? 0 :sales_price;
          sales_price = sales_price * qty;

      /*Discount*/
      var item_discount_type = $("#item_discount_type_"+row_id).val();
      var item_discount_input = parseFloat($("#item_discount_input_"+row_id).val());
          item_discount_input = (isNaN(item_discount_input)) ? 0 :item_discount_input;
      
      //Calculate discount      
      var discount_amt=(item_discount_type=='Percentage') ? ((sales_price) * item_discount_input)/100 : (item_discount_input*qty);
     
      sales_price-=parseFloat(discount_amt);

      var tax_amount = (tax_type=='Inclusive') ? calculate_inclusive(sales_price,tax) : calculate_exclusive(sales_price,tax);
      
      $("#item_discount_"+row_id).val(to_Fixed(discount_amt));
      $("#td_data_"+row_id+"_11").val(to_Fixed(tax_amount));
    }
    //Sale Items Modal Operations End

</script>
<script>
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' // optional
    });
  });
</script>
<script type="text/javascript">
  shortcut.add("Alt+m",function(e) {
        e.preventDefault();
        $(".show_payments_modal").trigger('click');
    },{
        'type':'keydown',
        'propagate':true,
        'target':document
      });

  shortcut.add("Alt+h",function(e) {
        e.preventDefault();
        $("#hold_invoice").trigger('click');
    },{
        'type':'keydown',
        'propagate':true,
        'target':document
      });
  shortcut.add("Alt+c",function(e) {
        e.preventDefault();
        $(".Alt_c").trigger('click');
    },{
        'type':'keydown',
        'propagate':true,
        'target':document
      });
  shortcut.add("Alt+a",function(e) {
        e.preventDefault();
        $(".Alt_a").trigger('click');
    },{
        'type':'keydown',
        'propagate':true,
        'target':document
      });
</script>

<script>

//Reset Tooltip
function reset_tooltip() {
  $('[data-toggle="tooltip"]').tooltip("destroy");
  $('[data-toggle="tooltip"]').tooltip(); // re-enabling 
}
$('.search_div').on('scroll', function() {
    if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {

      if($(".scroll_or_not").val()=="true"){

        //Scroll Restriction Enabled
        $(".scroll_or_not").val("false");
        //Ajax Loader
        $('.ajax-load').show();

        setTimeout(function() {
          //Ajax Request
          load_next_details();  
          //Scroll Restriction Disabled
          $(".scroll_or_not").val("true");
          //Ajax Loader End
          $('.ajax-load').hide();    
        }, 1000);
        
        
      }
      
    }
});

function load_next_details(){
  var last_id = $(".item_box:last").attr("data-item-id");
  get_details(last_id);
}



function get_details(last_id='',show_only_searched=false){

  warehouse_id = $("#warehouse_id").val();

  $.ajax({
      url: '<?php echo $base_url; ?>pos/get_details',
      type: "post",
      data:{
        last_id       : (!show_only_searched) ? last_id : '',
        customer_id   : $("#customer_id").val(),
        id            : $("#category_id").val(),
        store_id      : $("#store_id").val(),
        warehouse_id  : $("#warehouse_id").val(),
        price_type    : $("#price_type").val() || 'retail',
        item_name     : $("#item_name").val(),
        //search_it  : $("#search_it").val(),
        brand_id  : $("#brand_id").val(),

      },
      beforeSend: function(){
          $('.ajax-load').show();
      }
  }).done(function(data){
      $('.ajax-load').hide();
      
      if(data=='') {
        $(".error_div").show();
      }
      else{
        $(".error_div").hide();
      }


      if(show_only_searched){
        $(".search_div").html('');
      }
      $(".search_div").append(data);
      reset_tooltip();
  }).fail(function(jqXHR, ajaxOptions, thrownError){
      toastr.error('Server not responding. Please try again.');
  });
}


</script>

<script>
// Payment Mode UI Logic — show/hide reference and confirmation fields
function updatePaymentModeFields(container) {
  container = container || document;
  $(container).find('.payment-mode-select').each(function(){
    var $select = $(this);
    var $row = $select.closest('.box-body');
    var requiresRef = $select.find(':selected').data('requires-reference');
    var requiresConfirm = $select.find(':selected').data('requires-confirmation');
    
    var $ref = $row.find('.payment-reference').closest('.row');
    var $confirm = $row.find('.confirmation-status').closest('.row');
    
    if(requiresRef == 1) { $ref.show(); } else { $ref.hide(); $row.find('.payment-reference').val(''); }
    if(requiresConfirm == 1) { $confirm.show(); } else { $confirm.hide(); $row.find('.confirmation-status').val('1'); }
  });
}

// On page load
$(document).ready(function(){
  updatePaymentModeFields();
});

// On change of payment mode
$(document).on('change', '.payment-mode-select', function(){
  updatePaymentModeFields($(this).closest('.payments_div, .box'));
  togglePaystackButton();
});

// After adding new payment row, re-initialize
$(document).ajaxComplete(function(){
  updatePaymentModeFields();
  togglePaystackButton();
});

// Show/hide Generate Paystack Link button
function togglePaystackButton(){
  var hasPaystack = false;
  $('.payment-mode-select').each(function(){
    if($(this).val() == 'paystack'){ hasPaystack = true; }
  });
  if(hasPaystack){
    $('#btn-generate-paystack').removeClass('hide');
  } else {
    $('#btn-generate-paystack').addClass('hide');
  }
}

// Generate Paystack Payment Link
function generatePaystackLink(){
  var base_url = $('#base_url').val();
  var grand_total = $('.sales_div_tot_payble').text().replace(/,/g,'').trim();
  var amount = parseFloat(grand_total) || 0;
  var customer_id = $('#customer_id').val();

  if(amount <= 0){
    toastr['error']('Total amount must be greater than zero');
    return;
  }

  // Get customer email and phone
  $.ajax({
    type: 'POST',
    url: base_url + 'customers/get_customer_details',
    data: { customer_id: customer_id },
    dataType: 'json',
    success: function(customer){
      var email = (customer && customer.email) ? customer.email : '';
      var phone = (customer && customer.mobile) ? customer.mobile : '';
      var customer_name = (customer && customer.customer_name) ? customer.customer_name : '';

      if(!email){
        // Prompt for email if not available
        email = prompt('Customer email is required for Paystack. Enter email:');
        if(!email) { return; }
      }

      $('#paystack-link-modal').modal('show');
      $('#paystack-link-loading').removeClass('hide');
      $('#paystack-link-result').addClass('hide');
      $('#paystack-link-error').addClass('hide');

      $.ajax({
        type: 'POST',
        url: base_url + 'paystack/generate_link',
        data: {
          amount: amount,
          email: email,
          phone: phone,
          customer_id: customer_id
        },
        dataType: 'json',
        success: function(res){
          $('#paystack-link-loading').addClass('hide');
          if(res.status){
            $('#paystack-link-url').val(res.authorization_url);
            $('#paystack-whatsapp-share').attr('href', 'https://wa.me/?text=' + encodeURIComponent('Hi ' + customer_name + ', please complete your payment of ₦' + amount.toFixed(2) + ' here: ' + res.authorization_url));
            $('#paystack-email-share').attr('href', 'mailto:' + email + '?subject=Payment Link&body=Please complete your payment of ₦' + amount.toFixed(2) + ' here: ' + encodeURIComponent(res.authorization_url));

            // Generate QR Code
            var qrApi = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(res.authorization_url);
            $('#paystack-qr-code').html('<img src="' + qrApi + '" alt="QR Code" style="max-width:100%;">');

            $('#paystack-link-result').removeClass('hide');
          } else {
            $('#paystack-link-error').text(res.message || 'Failed to generate link').removeClass('hide');
          }
        },
        error: function(){
          $('#paystack-link-loading').addClass('hide');
          $('#paystack-link-error').text('Network error. Please try again.').removeClass('hide');
        }
      });
    }
  });
}

function copyPaystackLink(){
  var copyText = document.getElementById('paystack-link-url');
  copyText.select();
  document.execCommand('copy');
  toastr['success']('Link copied to clipboard!');
}
</script>

<!-- Clock In Modal -->
<div class="modal fade" id="clockInModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-clock-o"></i> <span id="clockTitle">Clock In</span></h4>
      </div>
      <div class="modal-body text-center">
        <video id="clockVideo" autoplay playsinline style="width:100%;max-width:300px;border-radius:8px;"></video>
        <canvas id="clockCanvas" style="display:none;"></canvas>
        <p id="clockStatus" class="text-muted">Click the button below to capture your face.</p>
        <button id="captureBtn" class="btn btn-primary btn-block"><i class="fa fa-camera"></i> Capture Face</button>
        <button id="confirmClockBtn" class="btn btn-success btn-block" style="display:none;"><i class="fa fa-check"></i> Confirm Clock In</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  var clockStream = null;
  var clockImage = null;
  var isClockedIn = false;

  function updateClockUi(state){
    isClockedIn = state;
    $('#clockInBtn b').text(isClockedIn ? ' Clock Out' : ' Clock In');
    $('#clockInBtn i').attr('class', isClockedIn ? 'fa fa-sign-out text-red' : 'fa fa-clock-o text-yellow');
    $('#clockTitle').text(isClockedIn ? 'Clock Out' : 'Clock In');
    $('#confirmClockBtn').html(isClockedIn ? '<i class="fa fa-check"></i> Confirm Clock Out' : '<i class="fa fa-check"></i> Confirm Clock In');
  }

  function checkStatus(){
    $.getJSON('<?= base_url('attendance/status_ajax'); ?>', function(res){
      updateClockUi(res.clocked_in);
    });
  }
  checkStatus();

  $('#clockInBtn').click(function(e){
    e.preventDefault();
    // Reset modal for current state
    updateClockUi(isClockedIn);
    $('#captureBtn').show();
    $('#confirmClockBtn').hide();
    $('#clockStatus').text('Click the button below to capture your face.');
    clockImage = null;
    $('#clockInModal').modal('show');
    startCamera();
  });

  function startCamera(){
    var video = document.getElementById('clockVideo');
    if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia){
      navigator.mediaDevices.getUserMedia({video:{facingMode:'user'}}).then(function(stream){
        clockStream = stream;
        video.srcObject = stream;
        video.play();
      }).catch(function(){
        $('#clockStatus').text('Camera access denied. Please allow camera.');
      });
    }
  }

  function stopCamera(){
    if(clockStream){ clockStream.getTracks().forEach(function(t){ t.stop(); }); clockStream = null; }
  }

  $('#captureBtn').click(function(){
    var video = document.getElementById('clockVideo');
    var canvas = document.getElementById('clockCanvas');
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    clockImage = canvas.toDataURL('image/png');
    $('#clockStatus').text('Face captured. Click Confirm to proceed.');
    $('#captureBtn').hide();
    $('#confirmClockBtn').show();
  });

  $('#confirmClockBtn').click(function(){
    if(!clockImage){ toastr.warning('Please capture your face first.'); return; }
    $('#clockStatus').text('Getting location...');
    var payload = {face_image: clockImage};
    if(navigator.geolocation){
      navigator.geolocation.getCurrentPosition(function(pos){
        payload.lat = pos.coords.latitude;
        payload.lng = pos.coords.longitude;
        sendClock(payload);
      }, function(){
        sendClock(payload);
      });
    } else {
      sendClock(payload);
    }
  });

  function enablePosCheckout(){
    $('#posClockInBanner').slideUp();
    $('#hold_invoice, .show_payments_modal, #show_cash_modal, #pay_all').prop('disabled', false).removeAttr('title');
    updateClockUi(true); // top bar now shows Clock Out
  }

  function sendClock(payload){
    var wasClockedIn = isClockedIn;
    var url = isClockedIn ? '<?= base_url('attendance/clock_out'); ?>' : '<?= base_url('attendance/clock_in'); ?>';
    $.post(url, payload, function(res){
      if(res.status === 'success'){
        toastr['success'](res.message);
        $('#clockInModal').modal('hide');
        // Immediately flip local UI — don't wait for async status check
        updateClockUi(!wasClockedIn);
        if(!wasClockedIn){
          enablePosCheckout();
        }
        // Verify with server in background
        setTimeout(checkStatus, 500);
      } else {
        toastr['error'](res.message || 'Clock action failed');
      }
    }, 'json').fail(function(xhr){
      toastr['error']('Network error. Please try again.');
      console.log('Clock action error:', xhr.responseText);
    });
  }

  $('#clockInModal').on('hidden.bs.modal', function(){ stopCamera(); });
})();
</script>

</body>
</html>
