<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<?php
    if(isset($sales_id)){

      //Edit
      $q2 = $this->db->query("select * from db_sales where id=$sales_id");
      $customer_id=$q2->row()->customer_id;
      $sales_date=show_date($q2->row()->sales_date);
      $due_date=(!empty($q2->row()->due_date)) ? show_date($q2->row()->due_date) : '';
      $sales_status=$q2->row()->sales_status;
      $warehouse_id=$q2->row()->warehouse_id;
      $reference_no=$q2->row()->reference_no;
      $discount_input=store_number_format($q2->row()->discount_to_all_input,0);
      $discount_type=$q2->row()->discount_to_all_type;
      $other_charges_input=store_number_format($q2->row()->other_charges_input,0);
      $other_charges_tax_id=$q2->row()->other_charges_tax_id;
      $sales_note=$q2->row()->sales_note;
      $store_id=$q2->row()->store_id;
      
      $init_code=$q2->row()->init_code;
      $count_id=$q2->row()->count_id;

      $coupon_id=$q2->row()->coupon_id;
      $coupon_code = (!empty($coupon_id)) ? get_customer_coupon_details($coupon_id)->code : '';
      $invoice_terms=$q2->row()->invoice_terms;


      $items_count = $this->db->query("select count(*) as items_count from db_salesitems where sales_id=$sales_id")->row()->items_count;
      $save_operation = false;
    }
    else if(isset($quotation_id) && !empty($quotation_id)){
      //NEW
      $q2 = $this->db->query("select * from db_quotation where id=$quotation_id");
      $customer_id=$q2->row()->customer_id;
      $sales_date=show_date($q2->row()->quotation_date);
      $due_date='';
      $sales_status='';
      $warehouse_id=$q2->row()->warehouse_id;
      $reference_no=$q2->row()->reference_no;
      $discount_input=store_number_format($q2->row()->discount_to_all_input,0);
      $discount_type=$q2->row()->discount_to_all_type;
      $other_charges_input=store_number_format($q2->row()->other_charges_input,0);
      $other_charges_tax_id=$q2->row()->other_charges_tax_id;
      $sales_note=$q2->row()->quotation_note;
      $store_id=$q2->row()->store_id;

      //$sales_code = get_init_code('sales');
      $init_code=get_only_init_code('sales');
      $count_id=get_last_count_id('db_sales');

      $items_count = $this->db->query("select count(*) as items_count from db_quotationitems where quotation_id=$quotation_id")->row()->items_count;
      $coupon_code='';

      $store_details = get_store_details($store_id);
      $invoice_terms =$store_details->invoice_terms;
      $save_operation = true;
    }
    else{
      //NEW
      $customer_id  = $sales_date = $sales_status = $warehouse_id =$due_date=
      $reference_no  =$coupon_code=
      $other_charges_input          = $other_charges_tax_id = $store_id =
      $discount_type  = $sales_note = '';
      $sales_date=show_date(date("d-m-Y"));
      $discount_input = $this->db->select("sales_discount")->get('db_store')->row()->sales_discount;
      $discount_input = ($discount_input==0) ? 0 : $discount_input;
      
      $init_code=get_only_init_code('sales');
      $count_id=get_last_count_id('db_sales');

      $store_details = get_store_details();
      $invoice_terms =$store_details->invoice_terms;
      $save_operation = true;
    }
   
   
    ?>

<?php 
                              echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>";
                              ?>

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

<?php
                                if(isset($sales_id)){
                                  $btn_id='update';
                                  $btn_name="Update";
                                  echo '<input type="hidden" name="sales_id" id="sales_id" value="'.$sales_id.'"/>';
                                }
                                else{
                                  $btn_id='save';
                                  $btn_name="Save";
                                }

                                ?>

<style type="text/css">
* { box-sizing: border-box; }
.nav-item.active { background: #EDF4FF; color: var(--mp-primary); font-weight: 600; }
.btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  padding: 10px 18px; border-radius: 10px; border: 1px solid transparent;
  font-size: 14px; font-weight: 600; cursor: pointer; transition: all .15s;
}
.btn-primary { background: var(--mp-primary); color: #fff; }
.btn-primary:hover { background: var(--mp-primary-dark); }
.btn-secondary { background: var(--mp-surface); color: var(--mp-ink); border-color: var(--mp-border); }
.btn-ghost { background: transparent; color: var(--mp-muted); border-color: var(--mp-border); }

.content { padding: 0; overflow-x: hidden; }
.invoice-grid {
  display: grid;
  grid-template-columns: 1fr minmax(300px, 360px);
  gap: 24px;
  align-items: start;
  max-width: 1400px;
  margin: 0 auto;
}
.left-column, .right-column { min-width: 0; }
@media (max-width: 1200px) {
  .invoice-grid { grid-template-columns: 1fr; }
  .content { padding: 16px; }
}

.card {
  background: var(--mp-surface);
  border: 1px solid var(--mp-border);
  border-radius: 14px;
  padding: 20px;
  box-shadow: var(--mp-shadow-sm);
  margin-bottom: 20px;
}
.card-title {
  font-size: 14px; font-weight: 700; color: var(--mp-text);
  margin-bottom: 16px; letter-spacing: -0.01em;
}
.form-row { display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 16px; }
.form-group { flex: 1 1 220px; min-width: 180px; }
.form-group.wide { flex: 2 1 300px; }
.form-group.half { flex: 1 1 45%; }
.content label {
  display: block; font-size: 12px; font-weight: 600; color: var(--mp-muted);
  margin-bottom: 6px; text-transform: uppercase; letter-spacing: .03em;
}
/* Scope form styles to sales content only — prevent leaking into MartPoint Assist */
.content input, .content select, .content textarea {
  width: 100%; border: 1px solid var(--mp-border); border-radius: 10px; padding: 10px 12px;
  font-size: 14px; font-family: inherit; color: var(--mp-text); background-color: var(--mp-surface);
  outline: none; transition: border .15s, box-shadow .15s;
}
.content input:focus, .content select:focus, .content textarea:focus { border-color: var(--mp-primary); box-shadow: 0 0 0 3px rgba(0,87,255,.08); }
.content input:disabled, .content select:disabled, .content textarea:disabled { background: var(--mp-bg); color: var(--mp-muted); cursor: not-allowed; }
.content .input-group { display: flex; }
.content .input-group .addon {
  display: inline-flex; align-items: center; justify-content: center;
  padding: 0 14px; background: var(--mp-bg); border: 1px solid var(--mp-border);
  border-right: none; border-radius: 10px 0 0 10px; color: var(--mp-muted); font-size: 14px;
}
.content .input-group input, .content .input-group select { border-radius: 0 10px 10px 0; }
.select-custom, .payment-mode-select { -webkit-appearance: none !important; -moz-appearance: none !important; appearance: none !important; background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%2378716C' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") !important; background-repeat: no-repeat !important; background-position: right 12px center !important; padding-right: 34px !important; width: 100%; min-width: 0; max-width: 100%; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; }

/* Item search */
.search-card { padding: 18px 20px; }
.search-row { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.price-toggle { display: flex; border: 1px solid var(--mp-border); border-radius: 10px; overflow: hidden; }
.price-toggle .pt-btn {
  border: none; background: var(--mp-surface); color: var(--mp-muted); padding: 10px 16px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .15s;
}
.price-toggle .pt-btn.active { background: var(--mp-primary); color: #fff; }
.search-input { flex: 1; min-width: 220px; position: relative; }
.search-input input { padding-left: 40px; }
.search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--mp-muted); }
.btn-add { width: 40px; height: 40px; border: 1px solid var(--mp-border); border-radius: 10px; background: var(--mp-surface); color: var(--mp-success); font-size: 18px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; }

/* Items table */
.items-table {
  width: 100%; border-collapse: separate; border-spacing: 0;
  border: 1px solid var(--mp-border); border-radius: 12px; overflow: hidden;
  font-size: 13px;
}
.items-table th, .items-table td { padding: 12px 14px; text-align: left; vertical-align: middle; }
.items-table thead th {
  background: var(--mp-bg); color: var(--mp-muted); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
}
.items-table td { border-bottom: 1px solid var(--mp-border); }
.items-table tbody tr:last-child td { border-bottom: none; }
.items-table .num { text-align: right; }
.items-table .total { font-weight: 700; color: var(--mp-text); }
.remove-btn { color: var(--mp-danger); cursor: pointer; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; transition: background .15s; }
.remove-btn:hover { background: rgba(220,38,38,.1); }
.empty-row td { padding: 36px; text-align: center; color: var(--mp-muted); font-size: 13px; }
.empty-row .empty-icon { font-size: 28px; color: var(--mp-muted); opacity: .5; display: block; margin-bottom: 8px; }

/* Item row cells */
.si-name { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.si-name-link { font-size: 13px; font-weight: 600; color: var(--mp-ink); text-decoration: none; cursor: pointer; }
.si-name-link:hover { color: var(--mp-primary); }
.si-promo { display: inline-block; font-size: 10px; font-weight: 700; color: var(--mp-pay); background: rgba(217,119,6,.1); padding: 1px 6px; border-radius: 4px; width: fit-content; }
.si-meta { font-size: 11px; color: var(--mp-muted); }
.si-tax-name { display: block; font-size: 10px; color: var(--mp-muted); margin-top: 2px; text-decoration: none; cursor: pointer; }
.si-tax-name:hover { color: var(--mp-primary); }
.action-col { text-align: center; }

/* Qty stepper */
.qty-stepper { display: inline-flex; align-items: stretch; border: 1px solid var(--mp-border); border-radius: 8px; overflow: hidden; }
.qty-btn { border: none; background: var(--mp-surface); color: var(--mp-muted); width: 30px; cursor: pointer; font-size: 11px; transition: background .12s; }
.qty-btn:hover { background: var(--mp-bg); color: var(--mp-ink); }
.qty-input { border: none; border-left: 1px solid var(--mp-border); border-right: 1px solid var(--mp-border); text-align: center; width: 52px; padding: 6px 4px; font-size: 13px; font-weight: 600; color: var(--mp-text); outline: none; }

/* Cell inputs (inside table) */
.cell-input { width: 100%; min-width: 85px; border: 1px solid var(--mp-border); border-radius: 8px; padding: 7px 10px; font-size: 13px; font-variant-numeric: tabular-nums; color: var(--mp-text); background: var(--mp-surface); outline: none; transition: border .15s, box-shadow .15s; }
.cell-input:focus { border-color: var(--mp-primary); box-shadow: 0 0 0 3px rgba(0,87,255,.08); }
.cell-input[readonly] { background: var(--mp-bg); color: var(--mp-ink); cursor: default; }
.cell-input.num { text-align: right; }
.cell-input.total { font-weight: 700; color: var(--mp-text); border-color: transparent; background: rgba(0,87,255,.04); }
.items-table td.num { white-space: nowrap; }
.qty-input { width: 64px; min-width: 58px; padding: 6px 6px; }
.si-name { min-width: 0; word-break: break-word; }
.si-name-link { display: inline-block; max-width: 100%; }

/* Totals side */
.sidebar-sticky { position: sticky; top: 24px; }
.total-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--mp-border); font-size: 14px; }
.total-row:last-child { border-bottom: none; }
.total-row .label { color: var(--mp-muted); font-weight: 500; }
.total-row .value { font-weight: 700; color: var(--mp-text); }
.total-row.grand { padding: 18px 0; margin-top: 4px; border-top: 2px solid var(--mp-border); }
.total-row.grand .label { font-size: 16px; font-weight: 700; color: var(--mp-text); }
.total-row.grand .value { font-size: 22px; font-weight: 800; color: var(--mp-primary); }

/* Payment */
.payment-fields { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 14px; }
@media (max-width: 600px) { .payment-fields { grid-template-columns: 1fr; } }
.payment-note textarea { min-height: 80px; resize: vertical; }
.cheque-fields { display: flex; gap: 14px; margin-top: 14px; }

/* Toggle boxes */
.toggle-section { border: 1px solid var(--mp-border); border-radius: 10px; padding: 14px; margin-bottom: 14px; cursor: pointer; }
.toggle-section:hover { border-color: var(--mp-primary); }
.sms-row { display: flex; align-items: center; gap: 10px; font-size: 14px; color: var(--mp-ink); }

/* Footer actions */
.actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 8px; }

/* Status chip */
.status-chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
.status-chip.open { background: #FEF3C7; color: #92400E; }

/* Customer trends */
.trends-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 8px; }
.trend-cell { display: flex; flex-direction: column; gap: 2px; padding: 10px; background: var(--mp-bg); border-radius: 8px; }
.trend-label { font-size: 11px; font-weight: 600; color: var(--mp-muted); text-transform: uppercase; letter-spacing: .03em; }
.trend-value { font-size: 15px; font-weight: 700; color: var(--mp-text); }
.trend-cell.full { grid-column: 1 / -1; }

/* Other charges & tax card */
.charges-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width: 600px) { .charges-row { grid-template-columns: 1fr; } }

/* Existing helpers */
.only_currency { text-align: right; }
.paid_amt { text-align: right; }
.hide { display: none; }
.select2 { width: 100%; }
.datepicker { cursor: pointer; }

/* Previous payments table */
#payments_table .pm-num { color: var(--mp-muted); font-weight: 600; width: 36px; }
#payments_table .pm-note { color: var(--mp-muted); font-size: 12px; }
#payments_table .pm-total td { font-weight: 700; color: var(--mp-text); border-top: 2px solid var(--mp-border); }
#payments_table .amt { font-variant-numeric: tabular-nums; }

/* Payment mode select consistency */

/* jQuery UI Autocomplete modern skin */
.ui-autocomplete { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(0,0,0,.08), 0 4px 10px -4px rgba(0,0,0,.04); padding: 8px 0; max-height: 320px; overflow-y: auto; overflow-x: hidden; z-index: 2000; font-family: inherit; font-size: 13px; width: auto !important; min-width: 320px; max-width: 90vw; }
.ui-autocomplete .ui-menu-item { padding: 0; }
.ui-autocomplete .ui-menu-item a, .ui-autocomplete .ui-menu-item div { display: block; padding: 10px 14px; color: var(--mp-ink); text-decoration: none; cursor: pointer; border: none; background: transparent; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ui-autocomplete .ui-menu-item a.ui-state-focus, .ui-autocomplete .ui-menu-item a.ui-state-active, .ui-autocomplete .ui-menu-item a.ui-state-hover, .ui-autocomplete .ui-menu-item a:hover { background: var(--mp-bg); color: var(--mp-text); margin: 0; border-radius: 0; border: none; }
.ui-autocomplete-loading { background-image: none !important; }

</style>

<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2><?= $page_title; ?></h2>
      <div class="mp-page-sub"><?= htmlspecialchars($this->session->userdata('store_name') ?: 'MartPoint'); ?> &mdash; <?= isset($sales_id) ? 'Edit Sales Invoice' : 'New Sales Invoice'; ?></div>
    </div>
    <a href="<?= $base_url; ?>sales" class="mp-qa-btn blue"><i class="fa fa-list"></i> Sales List</a>
  </div>
</div>
<?php include "comman/code_flashdata.php"; ?>
<?php include "modals/modal_customer.php"; ?>
<?php include "modals/modal_item.php"; ?>
<?php include "modals/modal_item_or_service.php"; ?>
<?php include "modals/modal_sales_item.php"; ?>
<section class="content">
<?= form_open('#', array('class' => 'mp-sales-form', 'id' => 'sales-form', 'enctype'=>'multipart/form-data', 'method'=>'POST'));?>

                           <input type="hidden" id="base_url" value="<?php echo $base_url; ?>">
                           <input type="hidden" value='1' id="hidden_rowcount" name="hidden_rowcount">
                           <input type="hidden" value='0' id="hidden_update_rowid" name="hidden_update_rowid">
                           <input type="hidden" value='Final' id="sales_status" name="sales_status">

                           <input type="hidden" value="" id="hidden_total_amt" name="hidden_total_amt">
                           <input type="hidden" value="" id="hidden_discount_to_all_amt" name="hidden_discount_to_all_amt">
                           <input type="hidden" value="" id="hidden_other_charges_amt" name="hidden_other_charges_amt">
                           <input type="hidden" value="" id="hidden_coupon_discount_amt" name="hidden_coupon_discount_amt">
                           <input type="hidden" value="" id="hidden_round_off_amt" name="hidden_round_off_amt">
                           <input type="hidden" value="" id="hidden_subtotal_amt" name="hidden_subtotal_amt">


                          <?php if(isset($quotation_id)) {?>
                           <input type="hidden" id="quotation_id" name="quotation_id" value="<?php echo $quotation_id;; ?>">
                           <?php } ?>

                           

      <div class="invoice-grid">
        <!-- LEFT COLUMN -->
        <div class="left-column">

          <!-- Invoice details -->
          <div class="card">
            <div class="card-title">Invoice Details</div>
            <div class="form-row">
              <div class="form-group wide">
                <label for="customer_id">Customer</label>
                <select class="form-control select2" id="customer_id" name="customer_id" style="width:100%;">
                  <option value="">Select Customer</option>
                </select>
                <span id="customer_id_msg" style="display:none" class="text-danger"></span>
                <div id="walkin-warning" class="alert alert-warning" style="display:none;margin-top:6px;padding:6px 10px;font-size:12px;border-radius:8px;">
                  <i class="fa fa-exclamation-triangle"></i> <strong>Walk-in Customer:</strong> Must pay in full — credit not allowed.
                </div>
                <div style="margin-top:6px;font-size:13px;color:var(--mp-muted);">
                  Previous Due: <span class="customer_previous_due text-danger" style="font-weight:700;">0.00</span>
                </div>
              </div>
              <div class="form-group">
                <label for="warehouse_id">Branch</label>
                <select class="form-control select2" id="warehouse_id" name="warehouse_id" style="width:100%;">
                  <?= get_warehouse_select_list($warehouse_id, get_current_store_id()); ?>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="init_code">Invoice Code</label>
                <input type="text" class="form-control" id="init_code" name="init_code" value="<?= $init_code; ?>" readonly>
                <span id="init_code_msg" style="display:none" class="text-danger"></span>
              </div>
              <div class="form-group">
                <label for="count_id">Count #</label>
                <input type="text" class="form-control" id="count_id" name="count_id" value="<?= $count_id; ?>" readonly>
                <span id="count_id_msg" style="display:none" class="text-danger"></span>
              </div>
              <div class="form-group">
                <label for="sales_date">Sales Date</label>
                <div class="input-group">
                  <span class="addon"><i class="fa fa-calendar"></i></span>
                  <input type="text" class="form-control datepicker" id="sales_date" name="sales_date" readonly value="<?= $sales_date; ?>">
                </div>
              </div>
              <div class="form-group">
                <label for="due_date">Due Date</label>
                <div class="input-group">
                  <span class="addon"><i class="fa fa-calendar"></i></span>
                  <input type="text" class="form-control datepicker" id="due_date" name="due_date" readonly value="<?= $due_date; ?>">
                </div>
              </div>
              <div class="form-group">
                <label for="reference_no">Reference No.</label>
                <input type="text" class="form-control" id="reference_no" name="reference_no" value="<?= $reference_no; ?>" placeholder="Optional reference...">
                <span id="reference_no_msg" style="display:none" class="text-danger"></span>
              </div>
            </div>
          </div>

          <!-- Item search -->
          <div class="card search-card">
            <div class="search-row">
              <input type="hidden" id="price_type" name="price_type" value="<?= (!isset($price_type) || $price_type == "wholesale") ? "wholesale" : "retail"; ?>">
              <div class="price-toggle">
                <button type="button" class="pt-btn <?= (!isset($price_type) || $price_type == 'wholesale') ? 'active' : ''; ?>" data-val="wholesale" onclick="setPriceType(this)">Wholesale</button>
                <button type="button" class="pt-btn <?= (isset($price_type) && $price_type == 'retail') ? 'active' : ''; ?>" data-val="retail" onclick="setPriceType(this)">Retail</button>
              </div>
              <div class="search-input">
                <span class="search-icon"><i class="fa fa-search"></i></span>
                <input type="text" id="item_search" placeholder="Scan barcode, type item name or code..." autocomplete="off">
              </div>
              <button type="button" class="btn-add show_item_service" title="Add Item/Service"><i class="fa fa-plus"></i></button>
            </div>
          </div>

          <!-- Items table -->
          <div class="card" style="padding: 0; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table class="items-table" id="sales_table">
              <thead>
                <tr>
                  <th>Item Name</th>
                  <th class="num" style="width:90px;">Qty</th>
                  <th class="num" style="width:100px;">Unit Price</th>
                  <th class="num" style="width:90px;">Discount</th>
                  <th class="num" style="width:100px;">Tax</th>
                  <th class="num" style="width:110px;">Total</th>
                  <th style="width:40px;"></th>
                </tr>
              </thead>
              <tbody>
                <tr class="empty-row" id="items_empty_state">
                  <td colspan="7">
                    <span class="empty-icon"><i class="fa fa-shopping-basket"></i></span>
                    No items added yet. Search or scan a barcode above to start the sale.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Other Charges & Tax -->
          <div class="card">
            <div class="card-title">Other Charges &amp; Tax</div>
            <div class="charges-row">
              <div class="form-group">
                <label for="other_charges_input">Other Charges</label>
                <input type="text" class="form-control text-right only_currency" id="other_charges_input" name="other_charges_input" onkeyup="final_total();" value="<?= $other_charges_input; ?>">
                <span id="other_charges_input_msg" style="display:none" class="text-danger"></span>
              </div>
              <div class="form-group">
                <label for="other_charges_tax_id">Tax</label>
                <select class="form-control select2" id="other_charges_tax_id" name="other_charges_tax_id" onchange="final_total();" style="width:100%;">
                  <?= get_tax_select_list($other_charges_tax_id, get_current_store_id()); ?>
                </select>
              </div>
            </div>
          </div>

          
          <!-- Discount and Coupon -->
          <div class="card">
            <div class="card-title">Discount & Coupon</div>
            <div class="form-group">
              <label for="coupon_code">Coupon Code</label>
              <input type="text" class="form-control" id="coupon_code" name="coupon_code" onkeyup="get_coupon_details();" value="<?= $coupon_code; ?>">
              <span id="coupon_code_msg" style="display:none" class="text-danger"></span>
              <div style="display:flex; justify-content:space-between; margin-top:6px; font-size:12px; color:var(--mp-muted);">
                <span>Coupon Type: <span class="coupon_type">---</span></span>
                <span>Coupon Value: <span class="coupon_value">0.00</span></span>
              </div>
            </div>
            <div class="form-group">
              <label for="discount_to_all_input">Discount on All</label>
              <div style="display:flex; gap:12px;">
                <input type="text" class="form-control text-right only_currency" id="discount_to_all_input" name="discount_to_all_input" onkeyup="enable_or_disable_item_discount();" value="<?= store_number_format($discount_input,0); ?>" style="flex:1;">
                <span id="discount_to_all_input_msg" style="display:none" class="text-danger"></span>
                <select class="form-control" id="discount_to_all_type" name="discount_to_all_type" onchange="final_total();" style="width:120px;">
                  <option value="in_percentage" <?= ($discount_type == 'in_percentage') ? 'selected' : '' ?>>%</option>
                  <option value="in_fixed" <?= ($discount_type == 'in_fixed') ? 'selected' : '' ?>>Fixed</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div class="card">
            <div class="card-title">Invoice Note</div>
            <textarea id="sales_note" name="sales_note" rows="3" placeholder="Internal note for this invoice..."><?= $sales_note; ?></textarea>
          </div>

          <!-- Customer Buying Trends -->
          <div class="card trends-card" id="customer-trends-card">
            <div class="card-title">Customer Buying Trends</div>
            <div class="trends-grid">
              <div class="trend-cell">
                <span class="trend-label">Total Invoices</span>
                <span class="trend-value" id="trend_invoices">-</span>
              </div>
              <div class="trend-cell">
                <span class="trend-label">Total Bought</span>
                <span class="trend-value" id="trend_bought">-</span>
              </div>
              <div class="trend-cell">
                <span class="trend-label">Total Paid</span>
                <span class="trend-value" id="trend_paid">-</span>
              </div>
              <div class="trend-cell">
                <span class="trend-label">Total Due</span>
                <span class="trend-value" id="trend_due">-</span>
              </div>
              <div class="trend-cell">
                <span class="trend-label">Paid / Partial / Unpaid</span>
                <span class="trend-value" id="trend_status">-</span>
              </div>
              <div class="trend-cell">
                <span class="trend-label">Avg Payment Days</span>
                <span class="trend-value" id="trend_avg_days">-</span>
              </div>
              <div class="trend-cell full">
                <span class="trend-label">Last Invoice</span>
                <span class="trend-value" id="trend_last">-</span>
              </div>
              <div class="trend-cell full">
                <span class="trend-label">Top Items</span>
                <span class="trend-value" id="trend_top">-</span>
              </div>
            </div>
          </div>

        </div>

        <!-- RIGHT COLUMN -->
        <div class="right-column sidebar-sticky">

          <!-- Totals -->
          <div class="card">
            <div class="card-title">Summary</div>
            <div class="total-row">
              <span class="label">Total Qty</span>
              <span class="value" id="total_quantity">0</span>
            </div>
            <div class="total-row">
              <span class="label">Subtotal</span>
              <span class="value" id="subtotal_amt">0.00</span>
            </div>
            <div class="total-row">
              <span class="label">Other Charges</span>
              <span class="value" id="other_charges_amt">0.00</span>
            </div>
            <div class="total-row">
              <span class="label">Coupon</span>
              <span class="value" id="coupon_discount_amt">0.00</span>
            </div>
            <div class="total-row">
              <span class="label">Discount</span>
              <span class="value" id="discount_to_all_amt">0.00</span>
            </div>
            <div class="total-row">
              <span class="label">Round Off</span>
              <span class="value" id="round_off_amt">0.00</span>
            </div>
            <div class="total-row grand">
              <span class="label">Grand Total</span>
              <span class="value" id="total_amt">0.00</span>
            </div>
          </div>


          <!-- Previous Payments -->
          <div class="card" id="previous-payments-card"<?= !isset($sales_id) ? ' style="display:none;"' : '' ?>>
            <div class="card-title">Previous Payments</div>
            <table class="items-table" id="payments_table" style="font-size:12px;">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Date</th>
                  <th>Mode</th>
                  <th>Note</th>
                  <th class="num">Amount</th>
                  <th style="width:40px;"></th>
                </tr>
              </thead>
              <tbody id="payments_tbody"><?php
if(isset($sales_id)){
  $q3 = $this->db->query("select * from db_salespayments where sales_id=$sales_id");
  if($q3->num_rows()>0){
    $i=1; $total_paid = 0;
    foreach ($q3->result() as $res3) {
      echo "<tr id='payment_row_".$res3->id."'>";
      echo "<td class='pm-num'>".$i."</td>";
      echo "<td>".show_date($res3->payment_date)."</td>";
      echo "<td><span class='mp-pill paid'>".$res3->payment_type."</span></td>";
      echo "<td class='pm-note'>".$res3->payment_note."</td>";
      echo "<td class='num amt' id='paid_amt_$i'>".store_number_format($res3->payment)."</td>";
      echo "<td class='action-col'><a class='remove-btn' onclick='delete_payment(".$res3->id.")' title='Delete ?'><i class='fa fa-trash'></i></a></td>";
      echo "</tr>";
      $total_paid +=$res3->payment;
      $i++;
    }
    echo "<tr class='pm-total'><td colspan='4'>Total Paid</td><td class='num amt' data-rowcount='$i' id='paid_amt_tot'>".store_number_format($total_paid)."</td><td></td></tr>";
  } else {
    echo "<tr class='empty-row'><td colspan='6'>No previous payments recorded for this invoice.</td></tr>";
  }
}
?></tbody>
            </table>
          </div>

          <!-- Payment -->
          <div class="card">
            <div class="card-title">Payment</div>
            <div class="payment-fields" style="grid-template-columns: 1fr;">
              <div class="form-group" style="margin:0;">
                <label for="amount">Amount</label>
                <input type="text" class="form-control text-right paid_amt only_currency" id="amount" name="amount" placeholder="">
                <span id="amount_msg" style="display:none" class="text-danger"></span>
              </div>
              <div class="form-group" style="margin:0;">
                <label for="payment_type">Mode</label>
                <select class="form-control select2" id="payment_type" name="payment_type" style="width:100%;" data-placeholder="-Select-">
                  <option value="">-Select-</option>
                  <?= get_payment_modes_select_list(get_current_store_id()); ?>
                </select>
              </div>
            </div>
            <div class="form-group payment-account" style="margin: 14px 0 0 0;">
              <label for="account_id">Account</label>
              <select class="form-control select2" id="account_id" name="account_id" style="width:100%;" data-placeholder="-None-">
                  <option value="">-None-</option>
                  <?= get_accounts_select_list(get_store_details()->default_account_id); ?>
                </select>
                <span id="account_id_msg" style="display:none" class="text-danger"></span>
            </div>

              <div class="form-group payment-reference-row" style="display:none;">
                <label for="payment_reference">Reference</label>
                <input type="text" class="form-control" id="payment_reference" name="payment_reference" placeholder="Enter reference number...">
                <span id="payment_reference_msg" style="display:none" class="text-danger"></span>
              </div>
              <div class="form-group confirmation-status-row" style="display:none;">
                <label for="confirmation_status">Confirmation Status</label>
                <select class="form-control select-custom" id="confirmation_status" name="confirmation_status" style="width:100%;">
                  <option value="1">Confirmed</option>
                  <option value="0">Not Confirmed</option>
                </select>
                <span id="confirmation_status_msg" style="display:none" class="text-danger"></span>
              </div>
              <div class="form-group cheque_div" style="display:none;">
                <div style="display:flex; gap:12px;">
                  <div style="flex:1;">
                    <label for="cheque_number"><?= $this->lang->line('cheque_number'); ?></label>
                    <input type="text" class="form-control" id="cheque_number" name="cheque_number" placeholder="">
                    <span id="cheque_number_msg" style="display:none" class="text-danger"></span>
                  </div>
                  <div style="flex:1;">
                    <label for="cheque_period"><?= $this->lang->line('cheque_period_days'); ?></label>
                    <input type="text" class="form-control only_currency" id="cheque_period" name="cheque_period" placeholder="">
                    <span id="cheque_period_msg" style="display:none" class="text-danger"></span>
                  </div>
                </div>
              </div>
              <div style="margin-bottom:10px;font-size:13px;color:var(--mp-muted);">Advance Available: <span class="customer_tot_advance" style="font-weight:700;color:var(--mp-success);">0.00</span></div>
              <div class="form-group advance-row" style="display:flex; align-items:center; gap:10px;">
                <input type="checkbox" id="allow_tot_advance" name="allow_tot_advance" style="width:auto;"> 
                <label for="allow_tot_advance" style="margin:0;"><?= $this->lang->line('adjust_advance_payment'); ?></label>
              </div>
            <div class="form-group payment-note" style="margin: 14px 0 0 0;">
              <label for="payment_note">Payment Note</label>
              <textarea id="payment_note" name="payment_note" rows="2"></textarea>
                <span id="payment_note_msg" style="display:none" class="text-danger"></span>
            </div>
          </div>

          <!-- Terms -->
          <div class="card">
            <div class="card-title">Terms & Conditions</div>
            <textarea id="invoice_terms" name="invoice_terms" rows="3" placeholder="Enter invoice terms..."><?= $invoice_terms; ?></textarea>
                <span id="invoice_terms_msg" style="display:none" class="text-danger"></span>
          </div>

          <!-- SMS -->
          <div class="card" style="padding: 14px 20px;">
            <label class="sms-row" for="send_sms" style="display:flex; align-items:center; gap:10px; cursor:pointer;">
              <input type="checkbox" id="send_sms" name="send_sms" <?=$send_sms_checkbox;?> style="width:auto; margin:0;">
              Send SMS to customer
            </label>
          </div>

          <!-- Actions -->
          <div class="actions">
            <a href="<?= base_url() ?>dashboard" class="btn btn-ghost close_btn" style="flex:1;text-decoration:none;">Close</a>
            <button type="button" id="<?= $btn_id; ?>" name="<?= $btn_name; ?>" class="btn btn-primary" style="flex:2;"><?= $btn_name; ?></button>
          </div>

        </div>
      </div>
    
<?= form_close(); ?>
</section>

<?php include "comman/code_js_sound.php"; ?>

<script type="text/javascript">
  var walk_in_customer_name ='<?= get_walk_in_customer_name();?>';
  var walkin_customer_id = <?=json_encode($walkin_customer_id ?? null);?>;
  var mp_currency = <?= json_encode($this->session->userdata('currency') ?: ''); ?>;
  var mp_currency_placement = <?= json_encode($this->session->userdata('currency_placement') ?: 'Left'); ?>;
  function money(res){
    var raw = format_money(res);
    if(!mp_currency){ return raw; }
    return (mp_currency_placement === 'Left') ? (mp_currency + ' ' + raw) : (raw + ' ' + mp_currency);
  }
  
function setPriceType(btn){
  $('#price_type').val($(btn).data('val'));
  $(btn).siblings().removeClass('active');
  $(btn).addClass('active');
}

function loadCustomerTrends(customer_id){
  if(!customer_id){ resetCustomerTrends(); return; }
  $.ajax({
    url: base_url + "sales/get_customer_trends",
    type: "POST",
    dataType: "json",
    data: { customer_id: customer_id, store_id: $("#store_id").val() },
    success: function(res){
      if(res.error){ resetCustomerTrends(); return; }
      $("#trend_invoices").text(res.invoice_count || 0);
      $("#trend_bought").text(money(res.total_amount));
      $("#trend_paid").text(money(res.paid_amount));
      $("#trend_due").text(money(res.due_amount));
      $("#trend_status").text((res.paid_count || 0) + ' / ' + (res.partial_count || 0) + ' / ' + (res.unpaid_count || 0));
      $("#trend_avg_days").text((res.avg_payment_days || 0) + ' days');
      $("#trend_last").text((res.last_sale_date || '-') + ' (' + money(res.last_sale_amount) + ')');
      if(res.top_items && res.top_items.length > 0){ $("#trend_top").html(res.top_items.map(function(i){ return i.name + ' (' + i.qty + ')'; }).join(', ')); }
      else { $("#trend_top").text('-'); }
    },
    error: function(){ resetCustomerTrends(); }
  });
}
function resetCustomerTrends(){ ['trend_invoices','trend_bought','trend_paid','trend_due','trend_status','trend_avg_days','trend_last','trend_top'].forEach(function(i){ $('#'+i).text('-'); }); }

  function isWalkInCustomer(){
    var cid = $(getCustomerSelectionId()).val();
    return (walkin_customer_id && cid == walkin_customer_id);
  }
</script>
<script src="<?= htmlspecialchars($theme_link); ?>js/sales.js?v=2"></script>
<script src="<?= htmlspecialchars($theme_link); ?>js/ajaxselect/customer_select_ajax.js?v=2"></script>
<script>$(function(){ if($("#item_search").data('ui-autocomplete')){ $("#item_search").autocomplete('option','position',{ my:'left top', at:'left bottom', collision:'flip fit' }); } });</script>
      <script>

         //Customer Selection Box Search
         function getCustomerSelectionId() {
           return '#customer_id';
         }

         $(document).ready(function () {

            var customer_id = "<?= (!empty($customer_id)) ? $customer_id : ($walkin_customer_id ?? '');  ?>";

            autoLoadFirstCustomer(customer_id);

            // Toggle walk-in warning on customer change
            function refreshCustomerData(){
              var cid = $('#customer_id').val();
              loadCustomerTrends(cid);
              if(isWalkInCustomer()){
                $("#walkin-warning").show();
              } else {
                $("#walkin-warning").hide();
              }
            }
            $("#customer_id").on('change', refreshCustomerData);
            $("#customer_id").on('select2:select', refreshCustomerData);
            refreshCustomerData();

            // Show walk-in warning on initial load if walk-in customer is preselected/loaded
            if(isWalkInCustomer()){
              $("#walkin-warning").show();
            }

         });
         //Customer Selection Box Search - END
         


         function save_operation() {
            <?php if($save_operation){ ?>
               return true;
            <?php }else{ ?>
               return false;
            <?php } ?>
         }

         $("#payment_type").on("change",function(){
          show_cheque_details();
        });
        function show_cheque_details(){
            var payment_type = $("#payment_type").val();
            var $selected = $("#payment_type option:selected");
            var requiresRef = $selected.data('requires-reference');
            var requiresConfirm = $selected.data('requires-confirmation');

            if(payment_type.toUpperCase()=='<?=strtoupper(cheque_name())?>'){
               $(".cheque_div").show();
            }
            else{
               $(".cheque_div").hide();
               $("#cheque_period,#cheque_number").val('');
            }

            if(requiresRef == 1 || requiresConfirm == 1){
               $(".payment-reference-row").show();
            } else {
               $(".payment-reference-row").hide();
               $("#payment_reference").val('');
               $("#confirmation_status").val('1');
            }
        }
        

       
        function set_previous_due(previous_due,tot_advance){
          if(typeof previous_due !== 'undefined' && previous_due !== null){ $(".customer_previous_due").html(previous_due); }
          if(typeof tot_advance !== 'undefined' && tot_advance !== null){ $(".customer_tot_advance").html(tot_advance); }
        }

        var base_url=$("#base_url").val();
        $("#store_id").on("change",function(){
          var store_id=$("#store_id").val();
          $.post(base_url+"sales/get_customers_select_list",{store_id:store_id},function(result){
              $("#customer_id").html('').append(result).select2();
              $("#sales_table > tbody").empty();
              calculate_tax();
          });
          $.post(base_url+"sales/get_tax_select_list",{store_id:store_id},function(result){
              $("#other_charges_tax_id").html('').append(result).select2();
              calculate_tax();
          });
        });

        /*Branch*/
        $("#warehouse_id").on("change",function(){
          var warehouse_id=$(this).val();
          $("#sales_table > tbody").empty();
          final_total();
        });
        /*Branch end*/

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
          
        /*if($("#warehouse_id").val()==''){
          $("#item_search").attr({
            disabled: true,
          });
          toastr["warning"]("Please Select Branch!!");
          failed.currentTime = 0; 
          failed.play();
         
        }*/
         
         /* ---------- CALCULATE TAX -------------*/
        

         function calculate_tax(i){ //i=Row
            set_tax_value(i);

           //Find the Tax type and Tax amount
           var tax_type = $("#tr_tax_type_"+i).val();
           var tax_amount = parseFloat(($("#td_data_"+i+"_11").val() || '').replace(/,/g,''));

           var qty=$("#td_data_"+i+"_3").val();
           var sales_price=parseFloat(($("#td_data_"+i+"_10").val() || '').replace(/,/g,''));
           $("#td_data_"+i+"_4").val(sales_price);
           /*Discounr*/
           var discount_amt=$("#td_data_"+i+"_8").val().replace(/,/g,'');
               discount_amt   =(isNaN(parseFloat(discount_amt)))    ? 0 : parseFloat(discount_amt);

           var amt=parseFloat(qty) * sales_price;//Taxable

           var total_amt=amt-discount_amt;
           total_amt = (tax_type=='Inclusive') ? total_amt : parseFloat(total_amt) + tax_amount;
           
           //Set Unit cost
           $("#td_data_"+i+"_9").val('').val(format_money(total_amt));
        
           final_total();
         }

        
         /* ---------- CALCULATE GST END -------------*/

         /*Calculate Coupon Discount Amount*/
         const discount_coupon_tot = function(subtotal) {
             var coupon_value=parseFloat($(".coupon_value").text());
                 coupon_value = isNaN(coupon_value) ? 0 : coupon_value;

             var coupon_type=$(".coupon_type").text();

             var discount_amt =0;
             if(coupon_type!='' && coupon_value>0){
                 if(coupon_type=='Percentage'){
                     discount_amt=(subtotal*coupon_value)/100;
                 }
                 else{//Fixed
                     discount_amt=coupon_value;
                 }
             }
             return discount_amt;
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
                    subtotal=subtotal+ + +parseFloat(($("#td_data_"+i+"_9").val() || '').replace(/,/g,''));
                    if($("#td_data_"+i+"_7").val()>=0){
                      tax_amt=tax_amt+ + +$("#td_data_"+i+"_7").val();
                    }   
                    total_quantity +=parseFloat($("#td_data_"+i+"_3").val());
                }
                   
             }//if end
           }//for end
           
          
          //Show total Sales Quantitys
           $("#total_quantity").html(format_qty(total_quantity));

           //Apply Output on screen
           //subtotal
           if((subtotal!=null || subtotal!='') && (subtotal!=0)){
             
             //subtotal
             $("#subtotal_amt").html(money(subtotal));
             
             //other charges total amount
             $("#other_charges_amt").html(money(other_charges_total_amt));
             
             //other charges total amount
            

             taxable=taxable+subtotal;

             //Calculate Coupon Discount
             var coupon_amt = discount_coupon_tot(taxable);
               taxable-=coupon_amt;

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
                   
                    $("#coupon_discount_amt").html(money(coupon_amt));  
                    $("#discount_to_all_amt").html(money(discount));  
                    $("#hidden_discount_to_all_amt").val(to_Fixed(discount));  
             //}
             //subtotal_round=Math.round(taxable);
             subtotal_round=round_off(taxable);//round_off() method custom defined
             subtotal_diff=subtotal_round-taxable;
         
             $("#round_off_amt").html(money(subtotal_diff)); 
             $("#total_amt").html(money(subtotal_round)); 
             if(save_operation()){
               $("#amount").val(to_Fixed(subtotal_round));
             }
             $("#hidden_total_amt").val(to_Fixed(subtotal_round)); 
           }
           else{
             $("#subtotal_amt").html(money(0)); 
             $("#tax_amt").html(money(0)); 
             $("#round_off_amt").html(money(0)); 
             $("#total_amt").html(money(0)); 
             $("#hidden_total_amt").val(to_Fixed(0)); 
             $("#discount_to_all_amt").html(money(0)); 
             $("#hidden_discount_to_all_amt").val(to_Fixed(0)); 
             $("#other_charges_amt").html(money(0));
             $("#amount").val(to_Fixed(0));
           }
           
          // adjust_payments();
          //alert("final_total() end");
         }
         /* ---------- Final Description of amount end ------------*/
          
         function removerow(id){//id=Rowid
           
         $("#row_"+id).remove();
         if($("#sales_table tbody tr:not(.empty-row)").length === 0){
           $("#sales_table tbody").append('<tr class="empty-row" id="items_empty_state"><td colspan="7"><span class="empty-icon"><i class="fa fa-shopping-basket"></i></span>No items added yet. Search or scan a barcode above to start the sale.</td></tr>');
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
         calculate_tax(k);
       }//if end
     }//for end

      //final_total();
    }

    

    //Sale Items Modal Operations Start
    function show_sales_item_modal(row_id){
      $('#sales_item').modal('show');
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
      //get the sales price of the item
      var tax_type = $("#tr_tax_type_"+row_id).val();
      var tax = $("#tr_tax_value_"+row_id).val(); //%
      var qty=$("#td_data_"+row_id+"_3").val();
          qty = (isNaN(qty)) ? 0 :qty;
      var sales_price = parseFloat(($("#td_data_"+row_id+"_10").val() || '').replace(/,/g,''));
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
      
      $("#td_data_"+row_id+"_8").val(format_money(discount_amt));

      $("#td_data_"+row_id+"_11").val(format_money(tax_amount));
    }
    //Sale Items Modal Operations End
      </script>


      <!-- UPDATE OPERATIONS -->
      <script type="text/javascript">
         <?php if(isset($sales_id) || isset($quotation_id)|| isset($order_id)){ ?> 
             $(document).ready(function(){
                var base_url='<?= base_url();?>';
                var path='';
                var id='';
                <?php if(isset($sales_id) && !empty($sales_id)) {?>
                  var id='<?=$sales_id;?>';  
                  var path = 'return_sales_list';
                <?php }?>

                <?php if(isset($quotation_id) && !empty($quotation_id)) {?>
                  var id='<?=$quotation_id;?>';  
                  var path = 'return_quotation_list';
                <?php }?>
   
                $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
                $.post(base_url+"sales/"+path+"/"+id,{},function(result){
                //  alert(result);
                  $('#sales_table tbody').append(result);
                  $("#items_empty_state").remove();
                  $("#hidden_rowcount").val(parseInt(<?=$items_count;?>)+1);
                  success.currentTime = 0;
                  success.play();
                  get_coupon_details();  
                  enable_or_disable_item_discount();
                  $(".overlay").remove();
              }); 
             });
         <?php }?>
      </script>
      <script>
        $(function () {
          //bootstrap WYSIHTML5 - text editor
          //$("#invoice_terms").wysihtml5()
        })
      </script>
      <!-- UPDATE OPERATIONS end-->

      <!-- Make sidebar menu hughlighter/selector -->
      <script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>