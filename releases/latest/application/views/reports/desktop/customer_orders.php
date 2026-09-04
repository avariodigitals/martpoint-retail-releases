<?php $this->load->view('reports/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Find customer orders within a date window</div>
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
          <label for="within_date"><?= $this->lang->line('order_within_from_the_date'); ?></label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker" id="within_date" name="within_date">
          </div>
          <span id="within_date_msg" style="display:none" class="text-danger"></span>
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
  <div class="mp-card-head"><h3><?= $this->lang->line('records_table'); ?></h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table class="table table-bordered table-hover" id="report-data" style="width:100%;">
        <thead>
          <tr class="bg-blue">
            <th>#</th>
            <?php if(store_module() && is_admin()): ?>
            <th><?= $this->lang->line('store_name'); ?></th>
            <?php endif; ?>
            <th><?= $this->lang->line('customer_name'); ?></th>
            <th><?= $this->lang->line('last_order_date'); ?></th>
            <th><?= $this->lang->line('order_id'); ?></th>
            <th><?= $this->lang->line('no_orders'); ?>(in Days)</th>
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
  var base_url = $("#base_url").val();
  $("#store_id").on("change", function(){
    autoLoadFirstCustomer();
  });

  $("#view, #view_all").on("click", function(){
    var within_date = document.getElementById("within_date").value;
    var customer_id = document.getElementById("customer_id").value;
    var view_all = (this.id == "view_all") ? 'yes' : 'no';
    $(".mp-report-results").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.post($("#base_url").val() + "reports/show_customer_orders", { customer_id: customer_id, view_all: view_all, within_date: within_date, store_id: $("#store_id").val() }, function(result){
      setTimeout(function(){
        $("#tbodyid").empty().append(result);
        $(".overlay").remove();
      }, 0);
    });
  });
</script>
<script>$(".report-customer-orders-active-li, .reports-menu").addClass("active");</script>
