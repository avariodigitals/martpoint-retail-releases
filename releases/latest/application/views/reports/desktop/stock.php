<?php $this->load->view('reports/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">View stock levels by item or brand, as of a selected date</div>
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

        <div class="mp-form-group full">
          <?php $this->load->view('warehouse/warehouse_code', ['show_warehouse_select_box' => true, 'div_length' => '', 'show_all' => 'true', 'form_group_remove' => 'true', 'show_all_option' => true]); ?>
        </div>

        <div class="mp-form-group">
          <label for="brand_id"><?= $this->lang->line('brand'); ?></label>
          <select class="form-control select2" id="brand_id" name="brand_id" style="width:100%;">
            <option value="">-Select-</option>
            <?= get_brands_select_list(); ?>
          </select>
          <span id="brand_id_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="category_id"><?= $this->lang->line('category'); ?></label>
          <select class="form-control select2" id="category_id" name="category_id" style="width:100%;">
            <option value="">-Select-</option>
            <?= get_categories_select_list(); ?>
          </select>
          <span id="category_id_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group full">
          <label>Date Range</label>
          <div class="mp-report-actions">
            <button type="button" class="btn btn-default btn-range mp-btn-secondary" data-from="<?= show_date(date('Y-m-d')); ?>" data-to="<?= show_date(date('Y-m-d')); ?>" style="padding:8px 14px;">Today</button>
            <button type="button" class="btn btn-default btn-range mp-btn-secondary" data-from="<?= show_date(date('Y-m-d', strtotime('-1 day'))); ?>" data-to="<?= show_date(date('Y-m-d', strtotime('-1 day'))); ?>" style="padding:8px 14px;">Yesterday</button>
            <button type="button" class="btn btn-default btn-range mp-btn-secondary" data-from="<?= show_date(date('Y-m-d', strtotime('-7 days'))); ?>" data-to="<?= show_date(date('Y-m-d')); ?>" style="padding:8px 14px;">Last 7 Days</button>
            <button type="button" class="btn btn-default btn-range mp-btn-secondary" data-from="<?= show_date(date('Y-m-d', strtotime('-30 days'))); ?>" data-to="<?= show_date(date('Y-m-d')); ?>" style="padding:8px 14px;">Last 30 Days</button>
          </div>
        </div>

        <div class="mp-form-group">
          <label for="from_date">From Date</label>
          <input type="text" class="form-control datepicker" id="from_date" name="from_date" value="<?= show_date(date('Y-m-d', strtotime('-30 days'))); ?>" readonly>
        </div>

        <div class="mp-form-group">
          <label for="to_date">To Date</label>
          <input type="text" class="form-control datepicker" id="to_date" name="to_date" value="<?= show_date(date('Y-m-d')); ?>" readonly>
          <span class="text-muted" style="font-size:12px;margin-top:4px;"><small>Stock level is as of the To Date</small></span>
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
  <ul class="nav nav-tabs" style="background:var(--mp-bg);border-bottom:1px solid var(--mp-border);padding:12px 20px 0;display:flex;gap:4px;list-style:none;margin:0;">
    <li class="active" style="margin:0;"><a href="#tab_1" data-toggle="tab" style="padding:10px 18px;border-radius:10px 10px 0 0;display:block;text-decoration:none;background:var(--mp-surface);color:var(--mp-ink);font-weight:600;"><?= $this->lang->line('item_wise'); ?></a></li>
    <li style="margin:0;"><a href="#tab_2" data-toggle="tab" style="padding:10px 18px;border-radius:10px 10px 0 0;display:block;text-decoration:none;color:var(--mp-muted);font-weight:600;"><?= $this->lang->line('brand_wise'); ?></a></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane active" id="tab_1" style="padding:20px;">
      <div class="mp-dt-scroll">
        <table class="table table-bordered table-hover" id="report-data" style="width:100%;">
          <thead>
            <tr class="bg-blue">
              <th>#</th>
              <?php if(store_module() && is_admin()): ?>
              <th><?= $this->lang->line('store_name'); ?></th>
              <?php endif; ?>
              <th><?= $this->lang->line('item_code'); ?></th>
              <th><?= $this->lang->line('item_name'); ?></th>
              <th><?= $this->lang->line('brand'); ?></th>
              <th><?= $this->lang->line('category'); ?></th>
              <th><?= $this->lang->line('unit_price'); ?></th>
              <th><?= $this->lang->line('tax'); ?></th>
              <th><?= $this->lang->line('purchase_cost'); ?></th>
              <th><?= $this->lang->line('sales_price'); ?></th>
              <th><?= $this->lang->line('current_stock'); ?></th>
              <th><?= $this->lang->line('stock_value'); ?><br><small>(<?= $this->lang->line('by_sale_price'); ?>)</small></th>
              <th><?= $this->lang->line('stock_value'); ?><br><small>(<?= $this->lang->line('by_purchase_price'); ?>)</small></th>
            </tr>
          </thead>
          <tbody id="tbodyid">
          </tbody>
        </table>
      </div>
    </div>
    <div class="tab-pane" id="tab_2" style="padding:20px;">
      <div class="mp-dt-scroll">
        <table class="table table-bordered table-hover" id="brand_wise_stock" style="width:100%;">
          <thead>
            <tr class="bg-blue">
              <th>#</th>
              <?php if(store_module() && is_admin()): ?>
              <th><?= $this->lang->line('store_name'); ?></th>
              <?php endif; ?>
              <th><?= $this->lang->line('brand_name'); ?></th>
              <th><?= $this->lang->line('current_stock'); ?></th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="<?php echo $theme_link; ?>js/sheetjs.js" type="text/javascript"></script>
<script type="text/javascript">
  var base_url = $("#base_url").val();

  function load_reports(){
    var store_id = $("#store_id").val();
    var brand_id = $("#brand_id").val();
    var category_id = $("#category_id").val();
    var warehouse_id = $("#warehouse_id").val();
    var from_date = $("#from_date").val();
    var to_date = $("#to_date").val();
    $(".mp-report-results").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.post(base_url + "reports/get_stock_report", { warehouse_id: warehouse_id, store_id: store_id, brand_id: brand_id, category_id: category_id, from_date: from_date, to_date: to_date }, function(result){
      result = $.parseJSON(result);
      $.each(result, function(key, val){
        if(key == 'item_wise_report'){ $("#tbodyid").empty().append(val); }
        if(key == 'brand_wise_stock'){ $("#brand_wise_stock tbody").empty().append(val); }
      });
      $(".overlay").remove();
    });
  }

  $("#view").on("click", function(){ load_reports(); });
  $("#store_id,#warehouse_id").on("change", function(){ load_reports(); });

  $(".btn-range").on("click", function(){
    $("#from_date").val($(this).data('from'));
    $("#to_date").val($(this).data('to'));
    load_reports();
  });

  $("#store_id").on("change", function(){
    var store_id = $(this).val();
    $.post(base_url + "sales/get_customers_select_list", { store_id: store_id }, function(result){
      result = '<option value="">All</option>' + result;
      $("#customer_id").html('').append(result).select2();
    });
    $.post(base_url + "sales/get_warehouse_select_list", { store_id: store_id }, function(result){
      result = '<option value="">All</option>' + result;
      $("#warehouse_id").html('').append(result).select2();
      load_brands_list();
      load_category_list();
    });
  });

  function load_brands_list(){
    var store_id = $("#store_id").val();
    $.post(base_url + "sales/get_brands_select_list", { store_id: store_id }, function(result){
      result = '<option value="">All</option>' + result;
      $("#brand_id").html('').append(result).select2();
    });
  }

  function load_category_list(){
    var store_id = $("#store_id").val();
    $.post(base_url + "sales/get_categories_select_list", { store_id: store_id }, function(result){
      result = '<option value="">All</option>' + result;
      $("#category_id").html('').append(result).select2();
    });
  }
</script>
<script>$(".report-stock-active-li").addClass("active");</script>
