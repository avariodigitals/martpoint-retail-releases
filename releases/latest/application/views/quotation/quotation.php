<?php $this->load->view('finance/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<?php
if(!isset($quotation_id)){
  $customer_id  = $quotation_date = $quotation_status = $warehouse_id =
  $reference_no  =
  $other_charges_input          = $other_charges_tax_id = $store_id =
  $discount_type  = $quotation_note = '';
  $quotation_date=show_date(date("d-m-Y"));
  $expire_date='';
  $discount_input = 0;
  $discount_input = ($discount_input==0) ? '' : $discount_input;
}
else{
  $q2 = $this->db->query("select * from db_quotation where id=$quotation_id");
  $customer_id=$q2->row()->customer_id;
  $quotation_date=show_date($q2->row()->quotation_date);
  $expire_date=(!empty($q2->row()->expire_date)) ? show_date($q2->row()->expire_date) : '';
  $quotation_status=$q2->row()->quotation_status;
  $warehouse_id=$q2->row()->warehouse_id;
  $reference_no=$q2->row()->reference_no;
  $discount_input=$q2->row()->discount_to_all_input;
  $discount_type=$q2->row()->discount_to_all_type;
  $other_charges_input=$q2->row()->other_charges_input;
  $other_charges_tax_id=$q2->row()->other_charges_tax_id;
  $quotation_note=$q2->row()->quotation_note;
  $store_id=$q2->row()->store_id;

  $items_count = $this->db->query("select count(*) as items_count from db_quotationitems where quotation_id=$quotation_id")->row()->items_count;
}
?>

<style type="text/css">
table.table-bordered > thead > tr > th { text-align: center; }
.table > tbody > tr > td,
.table > tbody > tr > th,
.table > tfoot > tr > td,
.table > tfoot > tr > th,
.table > thead > tr > td,
.table > thead > tr > th { padding-left: 2px; padding-right: 2px; }
#quotation_table { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
#quotation_table thead.custom_thead .bg-primary { background: #F1F5F9 !important; color: #334155 !important; border-bottom: 2px solid #E2E8F0; }
#quotation_table thead th { font-weight: 600; font-size: 11px; text-transform: uppercase; color: #64748B; }
#quotation_table tbody td { color: #334155; }
.totals-table td { text-align: right; }
.totals-table th { text-align: left; }
input#item_search.mp-form-control { border-radius: 0 !important; }

/* jQuery UI Autocomplete modern skin */
.ui-autocomplete { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0,0,0,.08), 0 4px 10px -4px rgba(0,0,0,.04); padding: 8px 0; max-height: 320px; overflow-y: auto; overflow-x: hidden; z-index: 2000; font-family: inherit; font-size: 13px; width: auto !important; min-width: 320px; max-width: 90vw; }
.ui-autocomplete .ui-menu-item { padding: 0; }
.ui-autocomplete .ui-menu-item a, .ui-autocomplete .ui-menu-item div { display: block; padding: 10px 14px; color: var(--mp-ink); text-decoration: none; cursor: pointer; border: none; background: transparent; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ui-autocomplete .ui-menu-item a.ui-state-focus, .ui-autocomplete .ui-menu-item a.ui-state-active, .ui-autocomplete .ui-menu-item a.ui-state-hover, .ui-autocomplete .ui-menu-item a:hover { background: var(--mp-bg); color: var(--mp-text); margin: 0; border-radius: 0; border: none; }
.ui-autocomplete-loading { background-image: none !important; }
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= isset($quotation_id) ? 'Update quotation details' : 'Create a new quotation'; ?></div>
  </div>
  <a class="mp-qa-btn" href="<?= base_url('quotation'); ?>"><i class="fa fa-arrow-left"></i> Back to Quotations</a>
</div>


<!-- **********************MODALS***************** -->
<?php $this->load->view('modals/modal_customer');?>
<?php $this->load->view('modals/modal_item');?>
<?php $this->load->view('modals/modal_item_or_service');?>
<?php /*$this->load->view('modals/modal_service');*/ ?>
<?php $this->load->view('modals/modal_sales_item');?>
<!-- **********************MODALS END***************** -->

<?= form_open('#', array('class' => '', 'id' => 'quotation-form', 'enctype'=>'multipart/form-data', 'method'=>'POST'));?>
<input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">
<input type="hidden" value='1' id="hidden_rowcount" name="hidden_rowcount">
<input type="hidden" value='0' id="hidden_update_rowid" name="hidden_update_rowid">
<input type="hidden" id="hidden_total_amt" name="hidden_total_amt" value="0.00">
<input type="hidden" id="hidden_discount_to_all_amt" name="hidden_discount_to_all_amt" value="0.00">

<!-- Quotation Details -->
<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><i class="fa fa-file-text-o"></i> Quotation Details</h3>
  </div>
  <div class="mp-card-body">
    <!-- Store Code -->
    <?php
    /*if(store_module() && is_admin()) {$this->load->view('store/store_code',array('show_store_select_box'=>true,'store_id'=>$store_id,'div_length'=>'col-sm-3')); }else{*/
      echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>";
    /*}*/
    ?>
    <!-- Store Code end -->

    <div class="mp-form-grid">
      <!-- Branch Code -->
      <?php
       if(warehouse_module() && warehouse_count()>1) { ?>
       <div class="mp-form-group full">
         <label for="warehouse_id"><?= $this->lang->line('warehouse'); ?> <span class="text-danger">*</span></label>
         <?php $this->load->view('warehouse/warehouse_code',array('show_warehouse_select_box'=>true,'warehouse_id'=>$warehouse_id,'div_length'=>'','show_select_option'=>false,'form_group_remove'=>true,'no_label'=>true)); ?>
         <span id="warehouse_id_msg" style="display:none" class="text-danger"></span>
       </div>
       <?php }else{
        echo "<input type='hidden' name='warehouse_id' id='warehouse_id' value='".get_store_warehouse_id()."'>";
       }
      ?>
      <!-- Branch Code end -->

      <div class="mp-form-group">
        <label for="customer_id"><?= $this->lang->line('customer_name'); ?> <span class="text-danger">*</span></label>
        <div class="input-group">
          <select class="form-control select2 mp-form-control" id="customer_id" name="customer_id" style="width: 100%;"></select>
          <span class="input-group-addon pointer" data-toggle="modal" data-target="#customer-modal" title="New Customer?"><i class="fa fa-user-plus text-primary fa-lg"></i></span>
        </div>
        <span id="customer_id_msg" style="display:none" class="text-danger"></span>
        <p class="mp-form-hint"><?= $this->lang->line('previous_due'); ?>: <span class="customer_previous_due text-red" style="font-size: 16px;">0.00</span></p>
      </div>

      <div class="mp-form-group">
        <label for="quotation_date"><?= $this->lang->line('quotation_date'); ?> <span class="text-danger">*</span></label>
        <div class="input-group date">
          <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
          <input type="text" class="form-control pull-right datepicker mp-form-control" id="quotation_date" name="quotation_date" readonly onkeyup="shift_cursor(event,'quotation_status')" value="<?= $quotation_date;?>">
        </div>
        <span id="quotation_date_msg" style="display:none" class="text-danger"></span>
      </div>

      <div class="mp-form-group hide">
        <label for="quotation_status"><?= $this->lang->line('status'); ?> <span class="text-danger">*</span></label>
        <select class="form-control select2 mp-form-control" id="quotation_status" name="quotation_status" onkeyup="shift_cursor(event,'mobile')">
          <option <?= ($quotation_status=='Quotation') ? 'selected' : ''; ?> value="Quotation">Quotation</option>
        </select>
        <span id="quotation_status_msg" style="display:none" class="text-danger"></span>
      </div>

      <div class="mp-form-group">
        <label for="expire_date"><?= $this->lang->line('expire_date'); ?></label>
        <div class="input-group date">
          <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
          <input type="text" class="form-control pull-right datepicker mp-form-control" id="expire_date" name="expire_date" onkeyup="shift_cursor(event,'expire_status')" value="<?= $expire_date;?>">
        </div>
        <span id="expire_date_msg" style="display:none" class="text-danger"></span>
      </div>

      <div class="mp-form-group">
        <label for="reference_no"><?= $this->lang->line('reference_no'); ?></label>
        <input type="text" value="<?php echo  $reference_no; ?>" class="form-control mp-form-control" id="reference_no" name="reference_no" placeholder="">
        <span id="reference_no_msg" style="display:none" class="text-danger"></span>
      </div>
    </div>
  </div>
</div>

<!-- Items -->
<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><i class="fa fa-barcode"></i> Items</h3>
  </div>
  <div class="mp-card-body" style="padding:0;">
    <div style="padding:16px 20px; border-bottom:1px solid var(--mp-border);">
      <div class="mp-form-group" style="margin:0;">
        <div class="input-group">
          <span class="input-group-addon" title="Select Items"><i class="fa fa-barcode"></i></span>
          <input type="text" class="form-control mp-form-control" placeholder="Item name / Barcode / Itemcode" autofocus id="item_search" style="border-radius:0;">
          <span class="input-group-addon pointer text-green show_item_service" title="Click to Add New Item or Service"><i class="fa fa-plus"></i></span>
        </div>
      </div>
    </div>
    <div class="mp-dt-scroll">
      <table class="table table-hover table-bordered mp-dt-table" style="width:100%;margin:0;" id="quotation_table">
        <thead class="custom_thead">
          <tr class="bg-primary">
            <th style="width:15%"><?= $this->lang->line('item_name'); ?></th>
            <th style="width:10%"><?= $this->lang->line('quantity'); ?></th>
            <th style="width:10%"><?= $this->lang->line('unit_price'); ?></th>
            <th style="width:10%"><?= $this->lang->line('discount'); ?>(<?= $CI->currency() ?>)</th>
            <th style="width:10%"><?= $this->lang->line('tax_amount'); ?></th>
            <th style="width:5%"><?= $this->lang->line('tax'); ?></th>
            <th style="width:7.5%"><?= $this->lang->line('total_amount'); ?></th>
            <th style="width:7.5%"><?= $this->lang->line('action'); ?></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Summary -->
<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><i class="fa fa-calculator"></i> Summary</h3>
  </div>
  <div class="mp-card-body">
    <div class="mp-row r-equal" style="align-items:flex-start;">
      <div>
        <div class="mp-form-group" style="margin-bottom:16px;">
          <label><?= $this->lang->line('quantity'); ?></label>
          <label class="total_quantity text-success" style="font-size: 15pt;">0</label>
        </div>

        <div class="mp-form-group" style="margin-bottom:16px;">
          <label for="other_charges_input"><?= $this->lang->line('other_charges'); ?></label>
          <div class="mp-form-grid">
            <input type="text" class="form-control text-right only_currency mp-form-control" id="other_charges_input" name="other_charges_input" onkeyup="final_total();" value="<?php echo  $other_charges_input; ?>">
            <select class="form-control mp-form-control select2" id="other_charges_tax_id" name="other_charges_tax_id" onchange="final_total();" style="width:100%;">
              <?= get_tax_select_list($other_charges_tax_id,get_current_store_id());?>
            </select>
          </div>
        </div>

        <div class="mp-form-group" style="margin-bottom:16px;">
          <label for="discount_to_all_input"><?= $this->lang->line('discount_on_all'); ?></label>
          <div class="mp-form-grid">
            <input type="text" class="form-control text-right only_currency mp-form-control" id="discount_to_all_input" name="discount_to_all_input" onkeyup="enable_or_disable_item_discount();" value="<?php echo  $discount_input; ?>">
            <select class="form-control mp-form-control" onchange="final_total();" id='discount_to_all_type' name="discount_to_all_type" style="width:100%;">
              <option value='in_percentage' <?= ($discount_type == 'in_percentage') ? 'selected' : ''; ?>>Per%</option>
              <option value='in_fixed' <?= ($discount_type == 'in_fixed') ? 'selected' : ''; ?>>Fixed</option>
            </select>
          </div>
        </div>

        <div class="mp-form-group full" style="margin-bottom:16px;">
          <label for="quotation_note"><?= $this->lang->line('note'); ?></label>
          <textarea class="form-control text-left mp-form-control" id='quotation_note' name="quotation_note"><?= $quotation_note; ?></textarea>
          <span id="quotation_note_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>

      <div>
        <table class="table mp-static-table totals-table" style="margin-left:auto;max-width:420px;">
          <tr>
            <th style="font-size: 17px;"><?= $this->lang->line('subtotal'); ?></th>
            <td style="font-size: 17px;">
              <h4 style="margin:0;"><b id="subtotal_amt" name="subtotal_amt">0.00</b></h4>
            </td>
          </tr>
          <tr>
            <th style="font-size: 17px;"><?= $this->lang->line('other_charges'); ?></th>
            <td style="font-size: 17px;">
              <h4 style="margin:0;"><b id="other_charges_amt" name="other_charges_amt">0.00</b></h4>
            </td>
          </tr>
          <tr>
            <th style="font-size: 17px;"><?= $this->lang->line('discount_on_all'); ?></th>
            <td style="font-size: 17px;">
              <h4 style="margin:0;"><b id="discount_to_all_amt" name="discount_to_all_amt">0.00</b></h4>
            </td>
          </tr>
          <tr style="<?= (!is_enabled_round_off()) ? 'display: none;' : '';?>">
            <th style="font-size: 17px;">
              <?= $this->lang->line('round_off'); ?>
              <i class="hover-q" data-container="body" data-toggle="popover" data-placement="top" data-content="Go to Site Settings-> Site -> Disable the Round Off(Checkbox)." data-html="true" data-trigger="hover" title="Do you want to Disable Round Off ?">
                <i class="fa fa-info-circle text-maroon text-black hover-q"></i>
              </i>
            </th>
            <td style="font-size: 17px;">
              <h4 style="margin:0;"><b id="round_off_amt" name="tot_round_off_amt">0.00</b></h4>
            </td>
          </tr>
          <tr>
            <th style="font-size: 17px;"><?= $this->lang->line('grand_total'); ?></th>
            <td style="font-size: 17px;">
              <h4 style="margin:0;"><b id="total_amt" name="total_amt">0.00</b></h4>
            </td>
          </tr>
        </table>
      </div>
    </div>

    <?php
    $send_sms_checkbox='disabled';
    if($CI->is_sms_enabled()){
      if(!isset($quotation_id)){
        $send_sms_checkbox='checked';
      }else{
        $send_sms_checkbox='';
      }
    }
    ?>
    <div class="mp-form-group full" style="flex-direction:row;align-items:center;gap:10px;margin-top:16px;">
      <input type="checkbox" <?=$send_sms_checkbox;?> id="send_sms" name="send_sms" style="width:18px;height:18px;">
      <label for="send_sms" style="margin:0;font-weight:500;">
        <?= $this->lang->line('send_sms_to_customer'); ?>
        <i class="hover-q" data-container="body" data-toggle="popover" data-placement="top" data-content="If checkbox is Disabled! You need to enable it from SMS -> SMS API <br><b>Note:<i>Walk-in Customer will not receive SMS!</i></b>" data-html="true" data-trigger="hover" title="Do you want to send SMS ?">
          <i class="fa fa-info-circle text-maroon text-black hover-q"></i>
        </i>
      </label>
    </div>

    <div class="mp-form-actions" style="justify-content:flex-end;margin-top:12px;">
      <?php
      if(isset($quotation_id)){
        $btn_id='update';
        $btn_name="Update";
        echo '<input type="hidden" name="quotation_id" id="quotation_id" value="'.$quotation_id.'"/>';
      }
      else{
        $btn_id='save';
        $btn_name="Save";
      }
      ?>
      <button type="button" id="<?php echo $btn_id;?>" class="mp-btn-primary payments_modal" title="Save Data"><i class="fa fa-save"></i> <?php echo $btn_name;?></button>
      <a href="<?= base_url();?>dashboard" class="mp-btn-secondary"><i class="fa fa-times"></i> Close</a>
    </div>
  </div>
</div>

<?= form_close(); ?>

<script src="<?= htmlspecialchars($theme_link); ?>js/modals.js"></script>
<script src="<?= htmlspecialchars($theme_link); ?>js/modals/modal_item.js"></script>
<script type="text/javascript">
  var walk_in_customer_name ='<?= get_walk_in_customer_name();?>'
</script>
<script src="<?= htmlspecialchars($theme_link); ?>js/quotation/quotation.js"></script>
<script>$(function(){ if($("#item_search").data('ui-autocomplete')){ $("#item_search").autocomplete('option','position',{ my:'left top', at:'left bottom', collision:'flip fit' }); } });</script>
<script src="<?= htmlspecialchars($theme_link); ?>js/ajaxselect/customer_select_ajax.js"></script>
<script>
   //Customer Selection Box Search
   function getCustomerSelectionId() {
     return '#customer_id';
   }

   $(document).ready(function () {

      var customer_id = "<?= (!empty($customer_id)) ? $customer_id : '';  ?>";
      if(customer_id!=''){
         autoLoadFirstCustomer(customer_id);
      }
   });
   //Customer Selection Box Search - END

  function set_previous_due(previous_due,tot_advance){
    $(".customer_previous_due").html(previous_due);
  }

  var base_url=$("#base_url").val();
  $("#store_id").on("change",function(){
    var store_id=$("#store_id").val();
    $.post(base_url+"quotation/get_customers_select_list",{store_id:store_id},function(result){
        $("#customer_id").html('').append(result).select2();
        $("#quotation_table > tbody").empty();
        calculate_tax();
    });
    $.post(base_url+"quotation/get_tax_select_list",{store_id:store_id},function(result){
        $("#other_charges_tax_id").html('').append(result).select2();
        calculate_tax();
    });
  });

  /*Branch*/
  $("#warehouse_id").on("change",function(){
    var warehouse_id=$(this).val();
    $("#quotation_table > tbody").empty();
    final_total();
  });
  /*Branch end*/

   $(".close_btn").on("click",function(){
     if(typeof swal === 'undefined'){
       if(!confirm('Are you sure you want to navigate away from this page?')) return;
       window.location='<?= base_url('dashboard'); ?>';
     } else {
       swal({
         title: "Leave Page?",
         text: "Are you sure you want to navigate away from this page? Unsaved changes may be lost.",
         icon: "warning",
         buttons: true,
         dangerMode: true
       }).then(function(willLeave){
         if(willLeave) window.location='<?= base_url('dashboard'); ?>';
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
     var tax_amount = $("#td_data_"+i+"_11").val();

     var qty=$("#td_data_"+i+"_3").val();
     var quotation_price=parseFloat($("#td_data_"+i+"_10").val());
     $("#td_data_"+i+"_4").val(quotation_price);
     /*Discounr*/
     var discount_amt=$("#td_data_"+i+"_8").val();
         discount_amt   =(isNaN(parseFloat(discount_amt)))    ? 0 : parseFloat(discount_amt);

     var amt=parseFloat(qty) * quotation_price;//Taxable

     var total_amt=amt-discount_amt;
     total_amt = (tax_type=='Inclusive') ? total_amt : parseFloat(total_amt) + parseFloat(tax_amount);

     //Set Unit cost
     $("#td_data_"+i+"_9").val('').val(to_Fixed(total_amt));

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
        //customer_id must exist
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


   //Show total Quotation Quantitys
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
            discount=parseFloat(to_Fixed(discount));

             $("#discount_to_all_amt").html(discount);
             $("#hidden_discount_to_all_amt").val(discount);
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
      $("#round_off_amt").html('0.00');
      $("#total_amt").html('0.00');
      $("#hidden_total_amt").html('0.00');
      $("#discount_to_all_amt").html('0.00');
      $("#hidden_discount_to_all_amt").html('0.00');
      $("#subtotal_amt").html('0.00');
      $("#other_charges_amt").html('0.00');
    }

   // adjust_payments();
   //alert("final_total() end");
  }
  /* ---------- Final Description of amount end ------------*/

  function removerow(id){//id=Rowid

  $("#row_"+id).remove();
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
       calculate_tax(k);
     }//if end
   }//for end

    //final_total();
  }



 //Sale Items Modal Operations Start
 function show_quotation_item_modal(row_id){

   $('#sales_item').modal('toggle');
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
   $("#td_data_"+row_id+"_12").html(tax_name);

   calculate_tax(row_id);
   $('#sales_item').modal('toggle');
 }
 function set_tax_value(row_id){
   //get the quotation price of the item
   var tax_type = $("#tr_tax_type_"+row_id).val();
   var tax = $("#tr_tax_value_"+row_id).val(); //%
   var qty=$("#td_data_"+row_id+"_3").val();
       qty = (isNaN(qty)) ? 0 :qty;
   var quotation_price = parseFloat($("#td_data_"+row_id+"_10").val());
       quotation_price = (isNaN(quotation_price)) ? 0 :quotation_price;
       quotation_price = quotation_price * qty;

   /*Discount*/
   var item_discount_type = $("#item_discount_type_"+row_id).val();
   var item_discount_input = parseFloat($("#item_discount_input_"+row_id).val());
       item_discount_input = (isNaN(item_discount_input)) ? 0 :item_discount_input;

   //Calculate discount
   var discount_amt=(item_discount_type=='Percentage') ? ((quotation_price) * item_discount_input)/100 : (item_discount_input*qty);
   quotation_price-=parseFloat(discount_amt);

   var tax_amount = (tax_type=='Inclusive') ? calculate_inclusive(quotation_price,tax) : calculate_exclusive(quotation_price,tax);

   $("#td_data_"+row_id+"_8").val(to_Fixed(discount_amt));

   $("#td_data_"+row_id+"_11").val(to_Fixed(tax_amount));
 }
 //Sale Items Modal Operations End
</script>

<!-- UPDATE OPERATIONS -->
<script type="text/javascript">
   <?php if(isset($quotation_id)){ ?>
       $("#store_id").attr('readonly',true);
       $(document).ready(function(){
          var base_url='<?= base_url();?>';
          var quotation_id='<?= $quotation_id;?>';
          $("#quotation-form").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
          $.post(base_url+"quotation/return_quotation_list/"+quotation_id,{},function(result){
            //alert(result);
            $('#quotation_table tbody').append(result);
            $("#hidden_rowcount").val(parseInt(<?=$items_count;?>)+1);
            success.currentTime = 0;
            success.play();
            enable_or_disable_item_discount();
            $(".overlay").remove();
        });
       });
   <?php }?>
</script>
<!-- UPDATE OPERATIONS end-->

<!-- Make sidebar menu highlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
