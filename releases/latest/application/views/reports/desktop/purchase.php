<?php $this->load->view('reports/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">View purchases by supplier and date range</div>
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

        <div class="mp-form-group full">
          <?php $this->load->view('warehouse/warehouse_code', ['show_warehouse_select_box' => true, 'div_length' => '', 'show_all' => 'true', 'form_group_remove' => 'true', 'show_all_option' => true]); ?>
        </div>

        <div class="mp-form-group">
          <label for="supplier_id"><?= $this->lang->line('supplier_name'); ?></label>
          <select class="form-control select2" id="supplier_id" name="supplier_id" style="width:100%;"></select>
          <span id="supplier_id_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="from_date"><?= $this->lang->line('from_date'); ?></label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker" id="from_date" name="from_date" onkeyup="shift_cursor(event,'to_date')" value="<?php echo show_date(date('d-m-Y')); ?>">
          </div>
          <span id="sales_date_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="to_date"><?= $this->lang->line('to_date'); ?></label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker" id="to_date" name="to_date" onkeyup="shift_cursor(event,'category_name')" value="<?php echo show_date(date('d-m-Y')); ?>">
          </div>
          <span id="sales_date_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="show_account_payble" class="checkbox-inline" style="display:flex;align-items:center;gap:8px;padding-top:28px;cursor:pointer;">
            <input type="checkbox" id="show_account_payble" name="show_account_payble">
            <?= $this->lang->line('view_account_payble'); ?>
          </label>
          <span id="show_account_payble_msg" style="display:none" class="text-danger"></span>
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
            <?php if(warehouse_module() && warehouse_count() > 0): ?>
            <th><?= $this->lang->line('warehouse_name'); ?></th>
            <?php endif; ?>
            <th><?= $this->lang->line('invoice_no'); ?></th>
            <th><?= $this->lang->line('purchase_date'); ?></th>
            <th><?= $this->lang->line('supplier_id'); ?></th>
            <th><?= $this->lang->line('supplier_name'); ?></th>
            <th><?= $this->lang->line('invoice_total'); ?> (<?= $CI->currency(); ?>)</th>
            <th><?= $this->lang->line('paid_amount'); ?> (<?= $CI->currency(); ?>)</th>
            <th><?= $this->lang->line('due_amount'); ?> (<?= $CI->currency(); ?>)</th>
          </tr>
        </thead>
        <tbody id="tbodyid">
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="<?php echo $theme_link; ?>js/ajaxselect/supplier_select_ajax.js"></script>
<script>function getsupplierSelectionId() { return '#supplier_id'; }</script>
<script src="<?php echo $theme_link; ?>js/report-purchase.js"></script>
<script type="text/javascript">
  var base_url = $("#base_url").val();
  $("#store_id").on("change", function(){
    var store_id = $(this).val();
    autoLoadFirstsupplier();
    $.post(base_url + "sales/get_warehouse_select_list", { store_id: store_id }, function(result){
      result = '<option value="">All</option>' + result;
      $("#warehouse_id").html('').append(result).select2();
    });
  });
</script>
<script>$(".report-purchase-active-li").addClass("active");</script>
