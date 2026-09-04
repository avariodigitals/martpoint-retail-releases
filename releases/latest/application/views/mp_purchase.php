<?php $this->load->view('finance/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
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

<style type="text/css">
  #purchase_table, #payments_table { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
  /* Compact purchase item cards */
  .mp-purchase-items{display:flex;flex-direction:column;gap:10px;}
  .mp-purchase-item{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:12px;overflow:hidden;transition:box-shadow .15s ease,transform .15s ease;}
  .mp-purchase-item:hover{box-shadow:0 4px 14px rgba(0,0,0,.06);}
  .mp-pi-head{display:grid;grid-template-columns:auto 1fr auto auto;gap:14px;align-items:center;padding:12px 16px;border-bottom:1px solid var(--mp-border);}
  .mp-pi-icon{width:38px;height:38px;border-radius:9px;background:var(--mp-bg);display:flex;align-items:center;justify-content:center;color:var(--mp-muted);font-size:16px;flex-shrink:0;}
  .mp-pi-name{font-size:14px;font-weight:600;color:var(--mp-ink);min-width:0;}
  .mp-pi-name a{color:var(--mp-ink);text-decoration:none;}
  .mp-pi-name a:hover{color:var(--mp-primary);}
  .mp-pi-meta{font-size:12px;color:var(--mp-muted);margin-top:2px;}
  .mp-pi-total{font-size:15px;font-weight:700;color:var(--mp-ink);text-align:right;font-variant-numeric:tabular-nums;}
  .mp-pi-total small{display:block;font-size:10px;font-weight:500;color:var(--mp-muted);text-transform:uppercase;letter-spacing:.04em;}
  .mp-pi-del{color:var(--mp-danger);font-size:16px;cursor:pointer;padding:6px;border-radius:8px;transition:background .15s;}
  .mp-pi-del:hover{background:rgba(220,38,38,.08);}
  .mp-pi-body{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;padding:12px 16px;}
  @media(max-width:900px){.mp-pi-body{grid-template-columns:repeat(2,1fr);}}
  .mp-pi-field{display:flex;flex-direction:column;gap:4px;}
  .mp-pi-field label{font-size:10px;font-weight:700;color:var(--mp-muted);text-transform:uppercase;letter-spacing:.05em;margin:0;}
  .mp-pi-field .form-control{border:1px solid var(--mp-border);border-radius:8px;padding:7px 10px;font-size:13px;height:auto;text-align:center;box-shadow:none;}
  .mp-pi-field .form-control:focus{border-color:var(--mp-primary);box-shadow:0 0 0 3px rgba(0,87,255,.1);outline:none;}
  .mp-qty{display:flex;align-items:center;}
  .mp-qty .btn-qty{width:30px;height:34px;border:1px solid var(--mp-border);background:var(--mp-bg);color:var(--mp-ink);font-size:13px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s;}
  .mp-qty .btn-qty:first-child{border-radius:8px 0 0 8px;border-right:none;}
  .mp-qty .btn-qty:last-child{border-radius:0 8px 8px 0;border-left:none;}
  .mp-qty .btn-qty:hover{background:var(--mp-border);}
  .mp-qty .qty-input{width:54px;text-align:center;border:1px solid var(--mp-border);border-left:none;border-right:none;padding:7px 4px;font-size:13px;font-weight:600;height:34px;}
  .mp-pi-expand{display:flex;align-items:center;justify-content:center;gap:6px;width:100%;padding:8px;background:transparent;border:none;border-top:1px solid var(--mp-border);color:var(--mp-muted);font-size:12px;font-weight:500;cursor:pointer;transition:color .15s;}
  .mp-pi-expand:hover{color:var(--mp-primary);}
  .mp-pi-expand i{transition:transform .25s;}
  .mp-pi-expand.expanded i{transform:rotate(180deg);}
  .mp-pi-advanced{background:var(--mp-bg);border-top:1px solid var(--mp-border);max-height:0;overflow:hidden;transition:max-height .3s ease,padding .3s ease;padding:0 16px;}
  .mp-pi-advanced.expanded{max-height:500px;padding:14px 16px;}
  .mp-pi-advanced-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;}
  @media(max-width:900px){.mp-pi-advanced-grid{grid-template-columns:repeat(2,1fr);}}
  .mp-pi-empty{text-align:center;padding:48px 20px;color:var(--mp-muted);}
  .mp-pi-empty i{font-size:36px;margin-bottom:10px;display:block;color:var(--mp-border);}
  .mp-pi-empty p{font-size:14px;font-weight:600;margin:0 0 4px;color:var(--mp-muted);}
  .mp-pi-empty small{font-size:12px;color:var(--mp-muted);}
  .totals-table td{text-align:right;}
  .totals-table th{text-align:left;}
  .mp-status-select{appearance:none;border:1px solid var(--mp-border);border-radius:10px;padding:11px 38px 11px 14px;font-size:14px;font-weight:600;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2378716C' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;background-color:var(--mp-surface);color:var(--mp-ink);height:auto;}
  .mp-status-select.status-draft{background-color:rgba(120,113,108,.08);color:var(--mp-muted);border-color:var(--mp-border);}
  .mp-status-select.status-ordered{background-color:rgba(0,87,255,.08);color:var(--mp-primary);border-color:rgba(0,87,255,.2);}
  .mp-status-select.status-partial{background-color:rgba(245,158,11,.08);color:var(--mp-warning);border-color:rgba(245,158,11,.2);}
  .mp-status-select.status-received{background-color:rgba(5,150,105,.08);color:var(--mp-success);border-color:rgba(5,150,105,.2);}
</style>

<div class="mp-page-head">
  <div>
    <h2><?=$page_title;?></h2>
    <div class="mp-page-sub"><?= isset($purchase_id) ? 'Update purchase order details' : 'Create a new purchase order'; ?></div>
  </div>
  <a class="mp-qa-btn" href="<?= base_url('purchase'); ?>"><i class="fa fa-arrow-left"></i> Back to Purchases</a>
</div>

<!-- **********************MODALS***************** -->
<?php include"modals/modal_supplier.php"; ?>
<?php include"modals/modal_purchase_item.php"; ?>
<?php include"modals/modal_item.php"; ?>
<?php include"modals/modal_item_or_service.php"; ?>
<?php /*include"modals/modal_service.php";*/ ?>
<!-- **********************MODALS END***************** -->

<?= form_open('#', array('class' => '', 'id' => 'purchase-form', 'enctype'=>'multipart/form-data', 'method'=>'POST'));?>
<input type="hidden" id="base_url" value="<?php echo $base_url; ?>">
<input type="hidden" value='1' id="hidden_rowcount" name="hidden_rowcount">
<input type="hidden" value='0' id="hidden_update_rowid" name="hidden_update_rowid">
<input type="hidden" id="hidden_subtotal_amt" name="tot_subtotal_amt" value="0.00">
<input type="hidden" id="hidden_other_charges_amt" name="other_charges_amt" value="0.00">
<input type="hidden" id="hidden_discount_to_all_amt" name="tot_discount_to_all_amt" value="0.00">
<input type="hidden" id="hidden_round_off_amt" name="tot_round_off_amt" value="0.00">
<input type="hidden" id="hidden_total_amt" name="tot_total_amt" value="0.00">
<?php echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>"; ?>

<!-- Purchase Details -->
<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><i class="fa fa-file-text-o"></i> Purchase Details</h3>
  </div>
  <div class="mp-card-body">
    <div class="mp-form-grid">
      <div class="mp-form-group">
        <label for="reference_no"><?= $this->lang->line('reference_no'); ?></label>
        <input type="text" value="<?php echo $reference_no; ?>" class="form-control mp-form-control" id="reference_no" name="reference_no" placeholder="Optional reference">
        <span id="reference_no_msg" style="display:none" class="text-danger"></span>
      </div>

      <div class="mp-form-group">
        <label for="pur_date"><?= $this->lang->line('purchase_date'); ?> <span class="text-danger">*</span></label>
        <div class="input-group date">
          <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
          <input type="text" class="form-control pull-right datepicker mp-form-control" id="pur_date" name="pur_date" readonly onkeyup="shift_cursor(event,'purchase_status')" value="<?= $pur_date;?>">
        </div>
        <span id="pur_date_msg" style="display:none" class="text-danger"></span>
      </div>

      <div class="mp-form-group">
        <label for="supplier_id"><?= $this->lang->line('supplier_name'); ?> <span class="text-danger">*</span></label>
        <div class="input-group">
          <select class="form-control select2 mp-form-control" id="supplier_id" name="supplier_id" style="width: 100%;"></select>
          <span class="input-group-addon pointer" data-toggle="modal" data-target="#supplier-modal" title="New Supplier?"><i class="fa fa-user-plus text-primary fa-lg"></i></span>
        </div>
        <span id="supplier_id_msg" style="display:none" class="text-danger"></span>
      </div>

      <div class="mp-form-group">
        <label for="purchase_status"><?= $this->lang->line('purchase_status'); ?> <span class="text-danger">*</span></label>
        <select class="form-control mp-status-select" id="purchase_status" name="purchase_status" onchange="toggle_batch_fields(); update_status_badge_style();">
          <option value="Draft" <?= ($purchase_status=='Draft' || $purchase_status=='') ? 'selected' : ''; ?>>Draft</option>
          <option value="Ordered" <?= ($purchase_status=='Ordered') ? 'selected' : ''; ?>>Ordered</option>
          <option value="Partially Received" <?= ($purchase_status=='Partially Received') ? 'selected' : ''; ?>>Partially Received</option>
          <option value="Received" <?= ($purchase_status=='Received') ? 'selected' : ''; ?>>Received</option>
        </select>
        <span id="purchase_status_msg" style="display:none" class="text-danger"></span>
      </div>

      <?php if(warehouse_module() && warehouse_count()>1){ ?>
      <div class="mp-form-group full">
        <label for="warehouse_id"><?= $this->lang->line('warehouse'); ?> <span class="text-danger">*</span></label>
        <select class="form-control select2 mp-form-control" id="warehouse_id" name="warehouse_id" style="width: 100%;">
          <?php
          $defaultWarehouseId = getDefaultWarehouseId(); $store_id = get_current_store_id();
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
      </div>
      <?php } else {
        $wh_id = get_store_warehouse_id();
        echo "<input type='hidden' name='warehouse_id' id='warehouse_id' value='".$wh_id."'>";
      } ?>
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
  <div class="mp-card-body" style="padding:16px;">
    <div id="purchase_items_container" class="mp-purchase-items">
      <div class="mp-pi-empty" id="purchase_items_empty">
        <i class="fa fa-shopping-cart"></i>
        <p>No items added yet</p>
        <small>Search for a product above to get started</small>
      </div>
    </div>

    <!-- Hidden legacy table for JS compatibility -->
    <table class="table table-hover table-bordered" style="display:none;" id="purchase_table">
      <thead class="custom_thead">
        <tr class="bg-primary">
          <th rowspan='2' style="width:15%">Item Name</th>
          <th rowspan='2' style="width:15%;">Quantity</th>
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
  </div>
</div>

<!-- Previous Payments (only on edit) -->
<?php if(isset($purchase_id)){ ?>
<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><i class="fa fa-history"></i> <?= $this->lang->line('previous_payments_information'); ?></h3>
  </div>
  <div class="mp-card-body" style="padding:0;">
    <div class="mp-dt-scroll">
      <table class="table mp-dt-table" style="width:100%;margin:0;" id="payments_table">
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
          $q3 = $this->db->query("select * from db_purchasepayments where purchase_id=$purchase_id");
          if($q3->num_rows()>0){
            $i=1;
            $total_paid = 0;
            foreach ($q3->result() as $res3) {
              echo "<tr id='payment_row_".$res3->id."'>";
              echo "<td>".$i."</td>";
              echo "<td>".show_date($res3->payment_date)."</td>";
              echo "<td>".$res3->payment_type."</td>";
              echo "<td>".$res3->payment_note."</td>";
              echo "<td class='amt text-right' id='paid_amt_$i'>".$CI->currency($res3->payment)."</td>";
              echo '<td class="mp-actions"><button class="mp-delete" onclick="delete_payment('.$res3->id.')"><i class="fa fa-trash"></i></button></td>';
              echo "</tr>";
              $total_paid +=$res3->payment;
              $i++;
            }
            echo "<tr><td colspan='4' style='text-align:right;font-weight:700;'>Total</td><td class='amt text-right' data-rowcount='$i' id='paid_amt_tot'>".$CI->currency(number_format($total_paid,2,'.',''))."</td><td></td></tr>";
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

<!-- Payment & Summary -->
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
          <label for="purchase_note"><?= $this->lang->line('note'); ?></label>
          <textarea class="form-control text-left mp-form-control" id='purchase_note' name="purchase_note"><?= $purchase_note;?></textarea>
          <span id="purchase_note_msg" style="display:none" class="text-danger"></span>
        </div>

        <?php if($CI->permissions('purchase_payment_add')){ ?>
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
        <?php } ?>
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
              <i class="hover-q fa fa-info-circle text-maroon" data-container="body" data-toggle="popover" data-placement="top" data-content="Go to Site Settings -> Site -> Disable the Round Off(Checkbox)." data-html="true" data-trigger="hover" title="Disable Round Off?"></i>
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
      if(isset($purchase_id)){
        $btn_id='update';
        $btn_name="Update Purchase";
        echo '<input type="hidden" name="purchase_id" id="purchase_id" value="'.$purchase_id.'"/>';
      } else {
        $btn_id='save';
        $btn_name="Save Purchase";
      }
      ?>
      <button type="button" id="<?php echo $btn_id;?>" class="mp-btn-primary" title="Save Data" onclick="handleSaveClick('<?php echo $btn_id;?>')"><i class="fa fa-check"></i> <?php echo $btn_name;?></button>
      <button type="button" class="mp-btn-secondary" onclick="handleSaveClick('<?php echo $btn_id;?>', 'Draft')"><i class="fa fa-file-text-o"></i> Save Draft</button>
      <button type="button" class="mp-btn-secondary" onclick="handleSaveClick('<?php echo $btn_id;?>', 'Received')"><i class="fa fa-download"></i> Save &amp; Receive</button>
      <a href="<?= base_url()?>purchase" class="mp-btn-secondary"><i class="fa fa-times"></i> Cancel</a>
    </div>
  </div>
</div>

<?= form_close(); ?>

<?php include "comman/code_js_sound.php"; ?>

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
        function handleSaveClick(btnId, statusOverride){
          if(statusOverride){
            $("#purchase_status").val(statusOverride);
            update_status_badge_style();
          }

          var base_url=$("#base_url").val();
          var rowcount=document.getElementById("hidden_rowcount").value;
          var flag1=false;
          for(var n=1;n<=rowcount;n++){
            if($("#td_data_"+n+"_3").val()!=null && $("#td_data_"+n+"_3").val()!=''){
              flag1=true;
            }
          }

          if(flag1==false){
            toastr["warning"]("Please Select Item!!");
            $("#item_search").focus();
            return;
          }

          var data = new FormData($('#purchase-form')[0]);
          data.append('command', btnId);
          data.append('rowcount', rowcount);

          $(".mp-card-form").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
          $("#"+btnId).attr('disabled',true);

          $.ajax({
            type: 'POST',
            url: base_url+'purchase/purchase_save_and_update',
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: function(result){
              result=result.split("<<<###>>>");
              if(result[0]=="success"){
                location.href=base_url+"purchase/invoice/"+result[1];
              }
              else if(result[0]=="failed"){
                toastr['error']("Sorry! Failed to save Record.Try again");
              }
              else{
                toastr.error(result);
              }
              $("#"+btnId).attr('disabled',false);
              $(".overlay").remove();
            },
            error: function(xhr, status, error){
              console.error("AJAX Error:", error);
              console.error("Response:", xhr.responseText);
              toastr.error("Error: " + error);
              $("#"+btnId).attr('disabled',false);
              $(".overlay").remove();
            }
          });
        }

        /* Card expand/collapse */
        $(document).on('click', '.mp-pi-expand', function(){
          var $btn = $(this);
          var $adv = $btn.closest('.mp-purchase-item').find('.mp-pi-advanced');
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
           $("#td_data_"+i+"_9_display").html(format_money(total_amt));
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
           $(".total_quantity").html(format_qty_display(total_quantity));

           //Apply Output on screen
           //subtotal
           if((subtotal!=null || subtotal!='') && (subtotal!=0)){

             //subtotal
             $("#subtotal_amt").html(format_money(subtotal));
             $("#hidden_subtotal_amt").val(to_Fixed(subtotal));

             //other charges total amount
             $("#other_charges_amt").html(format_money(other_charges_total_amt));
             $("#hidden_other_charges_amt").val(to_Fixed(other_charges_total_amt));

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

                    $("#discount_to_all_amt").html(format_money(discount));
                    $("#hidden_discount_to_all_amt").val(to_Fixed(discount));
             //}
             //subtotal_round=Math.round(taxable);
             subtotal_round=round_off(taxable);//round_off() method custom defined
             subtotal_diff=subtotal_round-taxable;

             $("#round_off_amt").html(format_money(subtotal_diff));
             $("#hidden_round_off_amt").val(to_Fixed(subtotal_diff));
             $("#total_amt").html(format_money(subtotal_round));
             $("#hidden_total_amt").val(to_Fixed(subtotal_round));
           }
           else{
             $("#subtotal_amt").html(format_money(0));
             $("#hidden_subtotal_amt").val(to_Fixed(0));
             $("#other_charges_amt").html(format_money(0));
             $("#hidden_other_charges_amt").val(to_Fixed(0));
             $("#discount_to_all_amt").html(format_money(0));
             $("#hidden_discount_to_all_amt").val(to_Fixed(0));
             $("#round_off_amt").html(format_money(0));
             $("#hidden_round_off_amt").val(to_Fixed(0));
             $("#total_amt").html(format_money(0));
             $("#hidden_total_amt").val(to_Fixed(0));

             $("#tax_amt").html('0.00');
           }

          // adjust_payments();
          //alert("final_total() end");
         }
         /* ---------- Final Description of amount end ------------*/

         function removerow(id){//id=Rowid
         $("#row_"+id).remove();
         $("#row_"+id+"_batch").remove();
         if($("#purchase_items_container .mp-purchase-item").length==0){
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
                $(".mp-card-form").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
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
      <script>
        $(".<?php echo 'purchase';?>-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
      </script>
