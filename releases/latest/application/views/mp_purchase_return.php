<?php $this->load->view('finance/desktop/_styles'); ?>

<style type="text/css">
  /* Compact return item table */
  #purchase_table { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
  #purchase_table thead th {
    background: var(--mp-bg);
    color: var(--mp-muted);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    border-bottom: 1px solid var(--mp-border) !important;
    border-top: none !important;
    padding: 10px 8px;
    text-align: center;
    white-space: nowrap;
  }
  #purchase_table tbody td {
    border-top: 1px solid var(--mp-border) !important;
    border-left: none !important;
    border-right: none !important;
    padding: 8px;
    vertical-align: middle;
    font-size: 13px;
  }
  #purchase_table tbody tr:hover { background: var(--mp-bg); }
  #purchase_table .form-control {
    border: 1px solid var(--mp-border);
    border-radius: 8px;
    padding: 6px 8px;
    font-size: 13px;
    height: auto;
    text-align: center;
    box-shadow: none;
  }
  #purchase_table .form-control:focus {
    border-color: var(--mp-primary);
    box-shadow: 0 0 0 3px rgba(0,87,255,.1);
  }
  #purchase_table .input-group-btn .btn {
    border: 1px solid var(--mp-border);
    background: var(--mp-bg);
    color: var(--mp-ink);
    border-radius: 8px;
    width: 30px;
    height: 34px;
    padding: 0;
  }
  #purchase_table .input-group-btn:first-child .btn { border-radius: 8px 0 0 8px; border-right: none; }
  #purchase_table .input-group-btn:last-child .btn { border-radius: 0 8px 8px 0; border-left: none; }
  #purchase_table .no-padding { padding: 6px 8px; }
  #purchase_table label.form-control { background: transparent; border: none; box-shadow: none; text-align: left; font-weight: 600; }
  #purchase_table .mp-row-del {
    color: var(--mp-danger);
    font-size: 18px;
    cursor: pointer;
    padding: 6px;
    border-radius: 8px;
    transition: background .15s;
  }
  #purchase_table .mp-row-del:hover { background: rgba(220,38,38,.08); }
  .mp-status-select { appearance: none; border: 1px solid var(--mp-border); border-radius: 10px; padding: 11px 38px 11px 14px; font-size: 14px; font-weight: 600; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2378716C' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; background-color: var(--mp-surface); color: var(--mp-ink); height: auto; }
  .mp-status-select.status-return { background-color: rgba(220,38,38,.08); color: var(--mp-danger); border-color: rgba(220,38,38,.2); }
  .mp-status-select.status-cancel { background-color: rgba(120,113,108,.08); color: var(--mp-muted); border-color: var(--mp-border); }
  .totals-table td { text-align: right; }
  .totals-table th { text-align: left; }
  #payments_table { font-family: 'Inter', -apple-system, sans-serif; }
  #payments_table thead th { background: var(--mp-bg); color: var(--mp-muted); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid var(--mp-border) !important; padding: 10px 12px; }
  #payments_table tbody td { border-top: 1px solid var(--mp-border) !important; padding: 10px 12px; font-size: 13px; }
</style>

<?php
   $purchase_code=$supplier_name='';
   if($oper=='return_against_purchase'){
         $return_id='';
         $q2 = $this->db->query("select * from db_purchase where id=$purchase_id");
         $supplier_id=$q2->row()->supplier_id;
         $return_date=show_date(date("d-m-Y"));
         $purchase_code=$q2->row()->purchase_code;
         $return_status=$q2->row()->purchase_status;
         $warehouse_id=$q2->row()->warehouse_id;
         $reference_no='';
         $discount_input=store_number_format($q2->row()->discount_to_all_input,2);
         $discount_type=$q2->row()->discount_to_all_type;
         $other_charges_input=store_number_format($q2->row()->other_charges_input,0);
         $other_charges_tax_id=$q2->row()->other_charges_tax_id;
         $store_id=$q2->row()->store_id;
         $return_note='';

         $items_count = $this->db->query("select count(*) as items_count from db_purchaseitems where purchase_id=$purchase_id")->row()->items_count;
   }
   if($oper=='edit_existing_return'){
         $q2 = $this->db->query("select * from db_purchasereturn where id=$return_id");
         $purchase_id=$q2->row()->purchase_id;
         $supplier_id=$q2->row()->supplier_id;
         $return_date=show_date(date("d-m-Y"));
         $return_status=$q2->row()->return_status;
         $return_code=$q2->row()->return_code;
         $warehouse_id=$q2->row()->warehouse_id;
         $reference_no=$q2->row()->reference_no;
         $discount_input=store_number_format($q2->row()->discount_to_all_input,2);
         $discount_type=$q2->row()->discount_to_all_type;
         $other_charges_input=store_number_format($q2->row()->other_charges_input,0);
         $other_charges_tax_id=$q2->row()->other_charges_tax_id;
         $return_note=$q2->row()->return_note;
         $store_id=$q2->row()->store_id;

         $items_count = $this->db->query("select count(*) as items_count from db_purchaseitemsreturn where return_id=$return_id")->row()->items_count;
         $purchase_code = (!empty($purchase_id)) ? $this->db->query("select * from db_purchase where id=$purchase_id")->row()->purchase_code : '';
   }
   if($oper=='create_new_return'){
         $supplier_id  = $return_date = $return_status = $warehouse_id =
         $reference_no  =
         $other_charges_input          = $other_charges_tax_id =
         $discount_input = $discount_type  = $return_note= $store_id='';
         $return_date=show_date(date("d-m-Y"));
   }

   if(!empty($supplier_id)){
     $supplier_name=$this->db->select('supplier_name')->where('id',$supplier_id)->get('db_suppliers')->row()->supplier_name;
   }
?>

<div class="mp-page-head">
  <div>
    <h2><?=$page_title;?></h2>
    <div class="mp-page-sub"><?= isset($subtitle) ? $subtitle : 'Create or update a purchase return'; ?></div>
  </div>
  <a class="mp-qa-btn" href="<?= base_url('purchase_return'); ?>"><i class="fa fa-arrow-left"></i> Back to Returns</a>
</div>

<!-- **********************MODALS***************** -->
<?php include"modals/modal_supplier.php"; ?>
<?php include"modals/modal_purchase_item.php"; ?>
<?php include"modals/modal_item.php"; ?>
<?php include"modals/modal_item_or_service.php"; ?>
<?php /*include"modals/modal_service.php";*/ ?>
<!-- **********************MODALS END***************** -->

<!-- ********** ALERT MESSAGE ******* -->
<?php include"comman/code_flashdata.php"; ?>
<!-- ********** ALERT MESSAGE END******* -->

<div class="box mp-return-wrap">
  <?= form_open('#', array('class' => 'form-horizontal', 'id' => 'purchase-form', 'enctype'=>'multipart/form-data', 'method'=>'POST'));?>
  <input type="hidden" id="base_url" value="<?php echo $base_url;; ?>">
  <input type="hidden" value='1' id="hidden_rowcount" name="hidden_rowcount">
  <input type="hidden" value='0' id="hidden_update_rowid" name="hidden_update_rowid">
  <input type="hidden" id="hidden_total_amt" name="tot_total_amt" value="0.00">
  <input type="hidden" id="hidden_discount_to_all_amt" name="tot_discount_to_all_amt" value="0.00">

  <?php
    echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>";
  ?>

  <?php
   if(warehouse_module() && warehouse_count()>1) {
     $this->load->view('warehouse/warehouse_code',array('show_warehouse_select_box'=>true,'warehouse_id'=>$warehouse_id,'div_length'=>'col-sm-3','show_select_option'=>false));
   } else {
     echo "<input type='hidden' name='warehouse_id' id='warehouse_id' value='".get_store_warehouse_id()."'>";
   }
  ?>

  <!-- Return Details -->
  <div class="mp-card-form">
    <div class="mp-card-head">
      <h3><i class="fa fa-file-text-o"></i> Return Details</h3>
    </div>
    <div class="mp-card-body">
      <div class="mp-form-grid">
        <?php if(!empty($purchase_code)) { ?>
        <div class="mp-form-group">
          <label><?= $this->lang->line('purchase_code'); ?> <span class="text-danger">*</span></label>
          <div class="mp-form-control" style="background:var(--mp-bg);font-weight:600;">#<?= $purchase_code;?></div>
        </div>
        <?php } ?>

        <?php if(!empty($return_code)) { ?>
        <div class="mp-form-group">
          <label><?= $this->lang->line('invoice'); ?> <span class="text-danger">*</span></label>
          <div class="mp-form-control" style="background:var(--mp-bg);font-weight:600;">#<?= $return_code;?></div>
        </div>
        <?php } ?>

        <?php if(!empty($supplier_name)) { ?>
        <div class="mp-form-group">
          <label><?= $this->lang->line('supplier_name'); ?> <span class="text-danger">*</span></label>
          <div class="mp-form-control" style="background:var(--mp-bg);font-weight:600;"><?= $supplier_name;?></div>
          <input type="hidden" name="supplier_id" id='supplier_id' value="<?=$supplier_id;?>">
        </div>
        <?php } ?>

        <?php if(empty($supplier_id)) {?>
        <div class="mp-form-group">
          <label for="supplier_id"><?= $this->lang->line('supplier_name'); ?> <span class="text-danger">*</span></label>
          <div class="input-group">
            <select class="form-control select2 mp-form-control" id="supplier_id" name="supplier_id" style="width: 100%;"></select>
            <span class="input-group-addon pointer" data-toggle="modal" data-target="#supplier-modal" title="New Supplier?"><i class="fa fa-user-plus text-primary fa-lg"></i></span>
          </div>
          <span id="supplier_id_msg" style="display:none" class="text-danger"></span>
        </div>
        <?php } ?>

        <div class="mp-form-group">
          <label for="return_date"><?= $this->lang->line('date'); ?> <span class="text-danger">*</span></label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker mp-form-control" id="return_date" name="return_date" readonly onkeyup="shift_cursor(event,'return_status')" value="<?= $return_date;?>">
          </div>
          <span id="return_date_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="return_status"><?= $this->lang->line('status'); ?> <span class="text-danger">*</span></label>
          <select class="form-control select2 mp-status-select" id="return_status" name="return_status" style="width: 100%;" onkeyup="shift_cursor(event,'reference_no')" onchange="update_return_status_style();">
            <?php
              $return_select = ($return_status=='Return') ? 'selected' : '';
              $cancel_select = ($return_status=='Cancel') ? 'selected' : '';
            ?>
            <option <?= $return_select; ?> value="Return">Return</option>
            <option <?= $cancel_select; ?> value="Cancel">Cancel</option>
          </select>
          <span id="return_status_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="reference_no"><?= $this->lang->line('reference_no'); ?></label>
          <input type="text" value="<?php echo $reference_no; ?>" class="form-control mp-form-control" id="reference_no" name="reference_no" placeholder="Optional reference">
          <span id="reference_no_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Items -->
  <div class="mp-card-form">
    <div class="mp-card-head" style="justify-content:space-between;gap:16px;">
      <h3><i class="fa fa-barcode"></i> Items</h3>
      <div class="input-group" style="max-width:420px;min-width:220px;">
        <span class="input-group-addon" title="Select Items"><i class="fa fa-barcode"></i></span>
        <input type="text" class="form-control mp-form-control" placeholder="Item name / Barcode / SKU" autofocus id="item_search">
        <span class="input-group-addon pointer text-green show_item_service" title="Click to Add New Item or Service"><i class="fa fa-plus"></i></span>
      </div>
    </div>
    <div class="mp-card-body" style="padding:0;">
      <div class="table-responsive" style="width: 100%">
        <table class="table table-hover" style="width:100%" id="purchase_table">
          <thead>
            <tr>
              <th style="width:20%"><?= $this->lang->line('item_name'); ?></th>
              <th style="width:15%;"><?= $this->lang->line('quantity'); ?></th>
              <th style="width:10%"><?= $this->lang->line('purchase_price'); ?></th>
              <th style="width:6%"><?= $this->lang->line('discount'); ?>(<?=$CURRENCY;?>)</th>
              <th style="width:7.5%"><?= $this->lang->line('tax_amount'); ?></th>
              <th style="width:7.5%"><?= $this->lang->line('unit_cost'); ?></th>
              <th style="width:7.5%"><?= $this->lang->line('total_amount'); ?></th>
              <th style="width:3%"><?= $this->lang->line('action'); ?></th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Previous Payments (only on edit) -->
  <?php if(!empty($return_id)){ ?>
  <div class="mp-card-form">
    <div class="mp-card-head">
      <h3><i class="fa fa-history"></i> <?= $this->lang->line('previous_payments_information'); ?></h3>
    </div>
    <div class="mp-card-body" style="padding:0;">
      <div class="table-responsive" style="width:100%;">
        <table class="table" style="width:100%" id="payments_table">
          <thead>
            <tr>
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
              $q3 = $this->db->query("select * from db_purchasepaymentsreturn where return_id=$return_id");
              if($q3->num_rows()>0){
                $i=1;
                $total_paid = 0;
                foreach ($q3->result() as $res3) {
                  echo "<tr id='payment_row_".$res3->id."'>";
                  echo "<td>".$i."</td>";
                  echo "<td>".show_date($res3->payment_date)."</td>";
                  echo "<td>".$res3->payment_type."</td>";
                  echo "<td>".$res3->payment_note."</td>";
                  echo "<td class='text-right' id='paid_amt_$i'>".$CI->currency($res3->payment)."</td>";
                  echo '<td><i class="fa fa-trash mp-row-del" onclick="delete_payment('.$res3->id.')"></i></td>';
                  echo "</tr>";
                  $total_paid +=$res3->payment;
                  $i++;
                }
                echo "<tr><td colspan='4' style='text-align:right;font-weight:700;'>Total</td><td class='text-right' data-rowcount='$i' id='paid_amt_tot'>".$CI->currency(number_format($total_paid,2,'.',''))."</td><td></td></tr>";
              } else {
                echo "<tr><td colspan='6' class='text-center' style='padding:32px;color:var(--mp-muted);'>No Previous Payments Found</td></tr>";
              }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php } ?>

  <!-- Charges, Payment & Summary -->
  <div class="mp-card-form">
    <div class="mp-card-head">
      <h3><i class="fa fa-calculator"></i> Charges, Payment & Summary</h3>
    </div>
    <div class="mp-card-body">
      <div class="mp-row r-equal" style="align-items:flex-start;">
        <div>
          <div class="mp-form-group" style="margin-bottom:16px;">
            <label for="other_charges_input"><?= $this->lang->line('other_charges'); ?></label>
            <div class="mp-form-grid" style="grid-template-columns:minmax(100px,1fr) minmax(160px,2fr);gap:12px;">
              <input type="text" class="form-control text-right only_currency mp-form-control" id="other_charges_input" name="other_charges_input" onkeyup="final_total();" value="<?php echo $other_charges_input; ?>">
              <select class="form-control mp-form-control" id="other_charges_tax_id" name="other_charges_tax_id" onchange="final_total();" style="min-width:140px;">
                <?= get_tax_select_list($other_charges_tax_id,get_current_store_id());?>
              </select>
            </div>
          </div>

          <div class="mp-form-group" style="margin-bottom:16px;">
            <label for="discount_to_all_input"><?= $this->lang->line('discount_on_all'); ?></label>
            <div class="mp-form-grid" style="grid-template-columns:minmax(100px,1fr) minmax(160px,2fr);gap:12px;">
              <input type="text" class="form-control text-right only_currency mp-form-control" id="discount_to_all_input" name="discount_to_all_input" onkeyup="enable_or_disable_item_discount();" value="<?php echo $discount_input; ?>">
              <select class="form-control mp-form-control" onchange="final_total();" id='discount_to_all_type' name="discount_to_all_type" style="min-width:140px;">
                <option value='in_percentage'>Percentage (%)</option>
                <option value='in_fixed'>Fixed Amount</option>
              </select>
            </div>
            <script type="text/javascript">
              <?php if($discount_type!=''){ ?>
                document.getElementById('discount_to_all_type').value='<?php echo $discount_type; ?>';
              <?php }?>
            </script>
          </div>

          <div class="mp-form-group full" style="margin-bottom:16px;">
            <label for="return_note"><?= $this->lang->line('note'); ?></label>
            <textarea class="form-control text-left mp-form-control" id='return_note' name="return_note"><?=$return_note;?></textarea>
            <span id="return_note_msg" style="display:none" class="text-danger"></span>
          </div>

          <div class="mp-form-group full" style="margin-bottom:8px;">
            <label style="font-size:13px;font-weight:700;color:var(--mp-ink);margin-bottom:10px;display:block;"><?= $this->lang->line('make_payment'); ?></label>
            <div class="mp-form-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;">
              <div class="mp-form-group">
                <label for="amount"><?= $this->lang->line('amount'); ?></label>
                <input type="text" class="form-control text-right paid_amt only_currency mp-form-control" id="amount" name="amount" placeholder="0.00">
                <span id="amount_msg" style="display:none" class="text-danger"></span>
              </div>
              <div class="mp-form-group">
                <label for="payment_type"><?= $this->lang->line('payment_type'); ?></label>
                <select class="form-control select2 mp-form-control" id='payment_type' name="payment_type" style="width:100%;">
                  <?php
                    $q1=$this->db->query("select * from db_paymenttypes where status=1 and store_id=".get_current_store_id());
                    if($q1->num_rows()>0){
                      echo "<option value=''>-Select-</option>";
                      foreach($q1->result() as $res1){
                        echo "<option value='".$res1->payment_type."'>".$res1->payment_type ."</option>";
                      }
                    } else { echo "<option>None</option>"; }
                  ?>
                </select>
                <span id="payment_type_msg" style="display:none" class="text-danger"></span>
              </div>
              <div class="mp-form-group">
                <label for="account_id"><?= $this->lang->line('account'); ?></label>
                <select class="form-control select2 mp-form-control" id='account_id' name="account_id" style="width:100%;">
                  <?php
                    echo '<option value="">-None-</option>';
                    echo get_accounts_select_list();
                  ?>
                </select>
                <span id="account_id_msg" style="display:none" class="text-danger"></span>
              </div>
            </div>
            <div class="mp-form-group full" style="margin-top:12px;">
              <label for="payment_note"><?= $this->lang->line('payment_note'); ?></label>
              <textarea class="form-control mp-form-control" id="payment_note" name="payment_note" placeholder="Optional note"></textarea>
              <span id="payment_note_msg" style="display:none" class="text-danger"></span>
            </div>
          </div>
        </div>

        <div>
          <table class="table mp-static-table totals-table" style="margin-left:auto;max-width:420px;">
            <tr>
              <th><?= $this->lang->line('total_quantities'); ?></th>
              <td><h4 style="margin:0;"><b class="total_quantity text-success">0</b></h4></td>
            </tr>
            <tr>
              <th><?= $this->lang->line('subtotal'); ?></th>
              <td><h4 style="margin:0;"><b id="subtotal_amt" name="subtotal_amt">0.00</b></h4></td>
            </tr>
            <tr>
              <th><?= $this->lang->line('other_charges'); ?></th>
              <td><h4 style="margin:0;"><b id="other_charges_amt" name="other_charges_amt">0.00</b></h4></td>
            </tr>
            <tr>
              <th><?= $this->lang->line('discount_on_all'); ?></th>
              <td><h4 style="margin:0;"><b id="discount_to_all_amt" name="discount_to_all_amt">0.00</b></h4></td>
            </tr>
            <tr style="<?= (!is_enabled_round_off()) ? 'display: none;' : '';?>">
              <th>
                <?= $this->lang->line('round_off'); ?>
                <i class="fa fa-info-circle text-maroon" data-container="body" data-toggle="popover" data-placement="top" data-content="Go to Site Settings -> Site -> Disable the Round Off(Checkbox)." data-html="true" data-trigger="hover" title="Disable Round Off?"></i>
              </th>
              <td><h4 style="margin:0;"><b id="round_off_amt" name="tot_round_off_amt">0.00</b></h4></td>
            </tr>
            <tr>
              <th style="font-size:16px;"><?= $this->lang->line('grand_total'); ?></th>
              <td style="font-size:16px;"><h3 style="margin:0;color:var(--mp-primary);"><b id="total_amt" name="total_amt">0.00</b></h3></td>
            </tr>
          </table>
        </div>
      </div>

      <div class="mp-form-actions" style="justify-content:flex-end;margin-top:16px;flex-wrap:wrap;">
        <?php
        if($oper=='return_against_purchase'){
          $btn_id='save';
          $btn_name="Save Return";
          echo '<input type="hidden" name="purchase_id" id="purchase_id" value="'.$purchase_id.'"/>';
        }
        if($oper=='edit_existing_return'){
          $btn_id='update';
          $btn_name="Update Return";
          echo '<input type="hidden" name="return_id" id="return_id" value="'.$return_id.'"/>';
          echo '<input type="hidden" name="purchase_id" id="purchase_id" value="'.$purchase_id.'"/>';
        }
        if($oper=='create_new_return'){
          $btn_id='create';
          $btn_name="Create Return";
        }
        ?>
        <button type="button" id="<?php echo $btn_id;?>" class="mp-btn-primary payments_modal" title="Save Data"><i class="fa fa-check"></i> <?php echo $btn_name;?></button>
        <a href="<?= base_url()?>purchase_return" class="mp-btn-secondary"><i class="fa fa-times"></i> Cancel</a>
      </div>
    </div>
  </div>

  <?= form_close(); ?>
</div>

<?php include "comman/code_js_sound.php"; ?>

<script>
  //supplier Selection Box Search
  function load_supplier_select2(){
     var supplier_id = "<?= (!empty($supplier_id)) ? $supplier_id : '';  ?>";
     if(supplier_id != ""){
        return false;
     }
     return true;
  }
  function getsupplierSelectionId() {
    return '#supplier_id';
  }

  $(document).ready(function () {
     var supplier_id = "<?= (!empty($supplier_id)) ? $supplier_id : '';  ?>";
     if(supplier_id!=''){
        autoLoadFirstsupplier(supplier_id);
     }
     update_return_status_style();
  });

  var base_url=$("#base_url").val();
  $("#store_id").on("change",function(){
    var store_id=$(this).val();
    $.post(base_url+"purchase/get_suppliers_select_list",{store_id:store_id},function(result){
        $("#supplier_id").html('').append(result).select2();
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
    $("#purchase_table > tbody").empty();
    final_total();
  });
  /*Warehouse end*/

  /* Status badge style updater */
  function update_return_status_style(){
    var status = $("#return_status").val();
    var $select = $("#return_status");
    $select.removeClass('status-return status-cancel');
    if(status=='Return') $select.addClass('status-return');
    else if(status=='Cancel') $select.addClass('status-cancel');
  }

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
  function calculate_tax(i){
    set_tax_value(i);
    var tax_type = $("#tr_tax_type_"+i).val();
    var tax_amount = get_float_type_data("#td_data_"+i+"_5");
    var qty=get_float_type_data("#td_data_"+i+"_3")
    var purchase_price=get_float_type_data("#td_data_"+i+"_4");
    var discount =get_float_type_data("#td_data_"+i+"_8");
    var tax=get_float_type_data("#tr_tax_value_"+i);
    var amt=qty * purchase_price;
    var total_amt=amt-discount;
    total_amt = (tax_type=='Inclusive') ? total_amt : total_amt + tax_amount;
    var tax_each = (tax_type=='Inclusive') ? 0 : calculate_exclusive(purchase_price-discount,tax);
    $("#td_data_"+i+"_10").val('').val(to_Fixed(total_amt/qty));
    $("#td_data_"+i+"_9").val('').val(to_Fixed(total_amt));
    final_total();
  }

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
       taxable=parseFloat(other_charges_per_amt)+parseFloat(other_charges_input);
       other_charges_total_amt=parseFloat(other_charges_per_amt)+parseFloat(other_charges_input);
    }

    var tax_amt=0;
    var actual_taxable=0;
    var total_quantity=0;

    for(i=1;i<=rowcount;i++){
      if(document.getElementById("td_data_"+i+"_3")){
        if($("#td_data_"+i+"_3").val()!=null && $("#td_data_"+i+"_3").val()!=''){
             actual_taxable=actual_taxable+ + +(parseFloat($("#td_data_"+i+"_13").val()) * parseFloat($("#td_data_"+i+"_3").val()));
             subtotal=subtotal+ + +parseFloat($("#td_data_"+i+"_9").val());
             if($("#td_data_"+i+"_7").val()>=0){
               tax_amt=tax_amt+ + +$("#td_data_"+i+"_7").val();
             }
             total_quantity +=parseFloat($("#td_data_"+i+"_3").val());
         }
      }
    }

    $(".total_quantity").html(format_qty(total_quantity));

    if((subtotal!=null || subtotal!='') && (subtotal!=0)){
      $("#subtotal_amt").html(to_Fixed(subtotal));
      $("#other_charges_amt").html(to_Fixed(other_charges_total_amt));
      taxable=taxable+subtotal;

      var discount_input=parseFloat($("#discount_to_all_input").val());
      discount_input = isNaN(discount_input) ? 0 : discount_input;
      var discount=0;
      if(discount_input>0){
          var discount_type=$("#discount_to_all_type").val();
          if(discount_type=='in_fixed'){
            taxable-=discount_input;
            discount=discount_input;
          }
          else if(discount_type=='in_percentage'){
              discount=(taxable*discount_input)/100;
             taxable-=discount;
          }
      }
        discount=to_Fixed(discount);
        $("#discount_to_all_amt").html(discount);
        $("#hidden_discount_to_all_amt").val(discount);
      subtotal_round=round_off(taxable);
      subtotal_diff=subtotal_round-taxable;
      $("#round_off_amt").html(to_Fixed(subtotal_diff));
      $("#total_amt").html(to_Fixed(subtotal_round));
      $("#hidden_total_amt").val(to_Fixed(subtotal_round));
    }
    else{
      $("#subtotal_amt").html('0.00');
      $("#tax_amt").html('0.00');
    }
  }

  function removerow(id){
    $("#row_"+id).remove();
    final_total();
    failed.currentTime = 0;
    failed.play();
  }

  function enable_or_disable_item_discount(){
    var rowcount=$("#hidden_rowcount").val();
    for(k=1;k<=rowcount;k++){
     if(document.getElementById("tr_item_id_"+k)){
       calculate_tax(k);
     }
    }
  }

  //Purchase Items Modal Operations Start
  function show_purchase_item_modal(row_id){
    $('#purchase_item').modal('toggle');
    var item_name = $("#td_data_"+row_id+"_1").html();
    var tax_type = $("#tr_tax_type_"+row_id).val();
    var tax_id = $("#tr_tax_id_"+row_id).val();
    var description = $("#description_"+row_id).val();
    var item_discount_input = $("#item_discount_input_"+row_id).val();
    var item_discount_type = $("#item_discount_type_"+row_id).val();
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
    var item_discount_input = $("#item_discount_input").val();
    var item_discount_type = $("#item_discount_type").val();
    $("#item_discount_input_"+row_id).val(item_discount_input);
    $("#item_discount_type_"+row_id).val(item_discount_type);
    $("#tr_tax_type_"+row_id).val(tax_type);
    $("#tr_tax_id_"+row_id).val(tax_id);
    $("#tr_tax_value_"+row_id).val(tax);
    $("#description_"+row_id).val(description);
    $("#td_data_"+row_id+"_15").html(tax_name);
    calculate_tax(row_id);
    $('#purchase_item').modal('toggle');
  }

  function set_tax_value(row_id){
    var tax_type = $("#tr_tax_type_"+row_id).val();
    var tax = $("#tr_tax_value_"+row_id).val();
    var qty=$("#td_data_"+row_id+"_3").val();
        qty = (isNaN(qty)) ? 0 :qty;
    var purchase_price = parseFloat($("#td_data_"+row_id+"_4").val());
        purchase_price = (isNaN(purchase_price)) ? 0 :purchase_price;
        purchase_price = purchase_price * qty;
    var item_discount_type = $("#item_discount_type_"+row_id).val();
    var item_discount_input = parseFloat($("#item_discount_input_"+row_id).val());
        item_discount_input = (isNaN(item_discount_input)) ? 0 :item_discount_input;
    var discount_amt=(item_discount_type=='Percentage') ? ((purchase_price) * item_discount_input)/100 : (item_discount_input*qty);
    purchase_price-=parseFloat(discount_amt);
    var tax_amount = (tax_type=='Inclusive') ? calculate_inclusive(purchase_price,tax) : calculate_exclusive(purchase_price,tax);
    $("#td_data_"+row_id+"_8").val(to_Fixed(discount_amt));
    $("#td_data_"+row_id+"_5").val(to_Fixed(tax_amount));
  }
</script>

<!-- Return against Purchase Entry -->
<script type="text/javascript">
  <?php if($oper=='return_against_purchase') { ?>
    $(document).ready(function(){
          var base_url='<?= base_url();?>';
          var purchase_id='<?= $purchase_id;?>';
          $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
          $.post(base_url+"purchase_return/purchase_list/"+purchase_id,{},function(result){
            $('#purchase_table tbody').append(result);
            $("#hidden_rowcount").val(parseInt(<?=$items_count;?>)+1);
            success.currentTime = 0;
            success.play();
            enable_or_disable_item_discount();
            $(".overlay").remove();
        });
       });
  <?php } ?>
</script>

<!-- EDIT OPERATIONS -->
<script type="text/javascript">
   <?php if($oper=='edit_existing_return') { ?>
       $(document).ready(function(){
          var base_url='<?= base_url();?>';
          var return_id='<?= $return_id;?>';
          $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
          $.post(base_url+"purchase_return/return_purchase_list/"+return_id,{},function(result){
            $('#purchase_table tbody').append(result);
            $("#hidden_rowcount").val(parseInt(<?=$items_count;?>)+1);
            success.currentTime = 0;
            success.play();
            enable_or_disable_item_discount();
            $(".overlay").remove();
        });
       });
   <?php }?>
</script>

<script>$(".purchase-returns-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
