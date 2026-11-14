<?php $this->load->view('reports/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">View sales payments by customer, payment type and user</div>
  </div>
  <div class="mp-report-actions">
    <?php $this->load->view('components/export_btn', ['tableId' => 'report-data']); ?>
  </div>
</div>

<form class="form-horizontal" id="report-form" onkeypress="return event.keyCode != 13;">
  <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
  <input type="hidden" id="base_url" value="<?php echo $base_url; ?>">

  <div class="mp-report-filter">
    <div class="mp-card-head"><h3>Report Filters</h3></div>
    <div class="mp-card-body">
      <div class="mp-form-grid">

        <?php if(store_module() && is_admin()): ?>
        <div class="mp-form-group full">
          <?php $this->load->view('store/store_code', ['show_store_select_box' => true, 'store_id' => get_current_store_id(), 'div_length' => '', 'show_all' => 'true', 'form_group_remove' => 'true']); ?>
        </div>
        <?php else: ?>
        <input type="hidden" name="store_id" id="store_id" value="<?= get_current_store_id(); ?>">
        <?php endif; ?>

        <div class="mp-form-group">
          <label for="customer_id"><?= $this->lang->line('customer_name'); ?></label>
          <select class="form-control select2" id="customer_id" name="customer_id" style="width:100%;"></select>
          <span id="customer_id_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="payment_type"><?= $this->lang->line('payment_type'); ?></label>
          <select class="form-control select2" id="payment_type" name="payment_type" style="width:100%;">
            <option value="">Select</option>
            <?= get_payment_types_select_list(null, get_current_store_id()); ?>
          </select>
          <span id="payment_type_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="created_by"><?= $this->lang->line('user'); ?></label>
          <select class="form-control select2" id="created_by" name="created_by" style="width:100%;">
            <?= get_users_select_list($this->session->userdata("role_id"), get_current_store_id()); ?>
          </select>
          <span id="created_by_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="from_date"><?= $this->lang->line('from_date'); ?></label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker" id="from_date" name="from_date" value="<?php echo show_date(date('d-m-Y')); ?>">
          </div>
          <span id="Sales_date_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="to_date"><?= $this->lang->line('to_date'); ?></label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker" id="to_date" name="to_date" value="<?php echo show_date(date('d-m-Y')); ?>">
          </div>
          <span id="Sales_date_msg" style="display:none" class="text-danger"></span>
        </div>

      </div>

      <div class="mp-report-filter-actions" style="margin-top:20px;">
        <button type="button" id="view" class="mp-btn-primary" title="Show Report"><i class="fa fa-eye"></i> Show</button>
        <a href="<?= base_url('dashboard'); ?>">
          <button type="button" class="mp-btn-secondary close_btn" title="Go Dashboard"><i class="fa fa-times"></i> Close</button>
        </a>
      </div>
    </div>
  </div>
</form>

<div class="mp-report-results">
  <div class="mp-card-head"><h3>Records Table</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table class="table table-bordered table-hover" id="report-data" style="width:100%;">
        <thead>
          <tr class="bg-blue">
            <th>#</th>
            <?php if(store_module() && is_admin()): ?>
            <th><?= $this->lang->line('store_name'); ?></th>
            <?php endif; ?>
            <th><?= $this->lang->line('sales'); ?><br><?= $this->lang->line('invoice_no'); ?></th>
            <th><?= $this->lang->line('payment_date'); ?></th>
            <th><?= $this->lang->line('customer_id'); ?></th>
            <th><?= $this->lang->line('customer_name'); ?></th>
            <th><?= $this->lang->line('payment_type'); ?></th>
            <th><?= $this->lang->line('payment_note'); ?></th>
            <th><?= $this->lang->line('paid_amount'); ?> (<?= $CI->currency(); ?>)</th>
            <th><?= $this->lang->line('user'); ?></th>
          </tr>
        </thead>
        <tbody id="tbodyid">
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="<?php echo $theme_link; ?>js/ajaxselect/customer_select_ajax.js"></script>
<script>function getCustomerSelectionId() { return '#customer_id'; }</script>
<script type="text/javascript">
  $("#view").on("click", function(){
    var from_date = document.getElementById("from_date").value;
    var to_date = document.getElementById("to_date").value;
    var customer_id = document.getElementById("customer_id").value;
    var payment_type = document.getElementById("payment_type").value;
    var store_id = document.getElementById("store_id").value;
    var created_by = document.getElementById("created_by").value;
    if(from_date == ""){ toastr["warning"]("Select From Date!"); document.getElementById("from_date").focus(); return; }
    if(to_date == ""){ toastr["warning"]("Select To Date!"); document.getElementById("to_date").focus(); return; }
    $(".mp-report-results").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.post("show_sales_payments_report", { created_by: created_by, store_id: store_id, payment_type: payment_type, customer_id: customer_id, from_date: from_date, to_date: to_date }, function(result){
      setTimeout(function(){
        $("#tbodyid").empty().append(result);
        $(".overlay").remove();
      }, 0);
    });
  });

  var base_url = $("#base_url").val();
  $("#store_id").on("change", function(){
    load_payment_types_list();
    load_users();
    autoLoadFirstCustomer();
  });

  function load_payment_types_list(){
    var store_id = $("#store_id").val();
    $.post(base_url + "sales/get_payment_types_select_list", { store_id: store_id }, function(result){
      result = '<option value="">All</option>' + result;
      $("#payment_type").html('').append(result).select2();
    });
  }

  function load_users(){
    var store_id = $("#store_id").val();
    $.post(base_url + "sales/get_users_select_list", { store_id: store_id }, function(result){
      $("#created_by").html('').append(result).select2();
    });
  }
</script>
<script>$(".report-sales-payments-active-li").addClass("active");</script>
