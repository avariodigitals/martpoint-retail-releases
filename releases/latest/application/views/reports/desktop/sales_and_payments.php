<?php $this->load->view('reports/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Sales and payments across the selected business and date range</div>
  </div>
  <div class="mp-report-actions">
    <?php $this->load->view('components/export_btn', ['tableId' => 'report-data']); ?>
  </div>
</div>

<form method="post" class="form-horizontal" id="report-form" onkeypress="return event.keyCode != 13;" action="<?= base_url('reports/sales_and_payments_report'); ?>">
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
          <label for="from_date"><?= $this->lang->line('from_date'); ?></label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker" id="from_date" name="from_date" value="">
          </div>
          <span id="Sales_date_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="to_date"><?= $this->lang->line('to_date'); ?></label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker" id="to_date" name="to_date" value="">
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
  <div class="mp-card-head"><h3><?= $this->lang->line('records_table'); ?></h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table class="table table-bordered table-hover" id="report-data" style="width:100%;">
        <thead>
          <tr>
            <th>#</th>
            <th><?= $this->lang->line('date'); ?></th>
            <th><?= $this->lang->line('customer_name'); ?></th>
            <th><?= $this->lang->line('invoice_no'); ?></th>
            <th><?= $this->lang->line('referenced_bill_no'); ?></th>
            <th><?= $this->lang->line('description'); ?></th>
            <th><?= $this->lang->line('qty'); ?></th>
            <th><?= $this->lang->line('bill_amount'); ?> (<?= $CI->currency(); ?>)</th>
            <th><?= $this->lang->line('receive'); ?> (<?= $CI->currency(); ?>)</th>
            <th><?= $this->lang->line('total'); ?> (<?= $CI->currency(); ?>)</th>
          </tr>
        </thead>
        <tbody id="tbodyid">
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="<?php echo $theme_link; ?>js/sheetjs.js" type="text/javascript"></script>
<script type="text/javascript">
  var base_url = $("#base_url").val();
  $("#view").click(function(){
    var from_date = document.getElementById("from_date").value;
    var to_date = document.getElementById("to_date").value;
    var button=$(this);button.prop('disabled',true);
    var results=$(".mp-report-results");results.append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.ajax({url:$("#report-form").attr('action'),method:'POST',data:$("#report-form").serialize(),dataType:'html'})
      .done(function(result){$("#tbodyid").html(result);})
      .fail(function(xhr){var message=xhr.status===403?'Your session or permissions changed. Refresh this page and try again.':'The sales report could not be loaded. Please try again.';$("#tbodyid").html('<tr><td colspan="10" role="alert"></td></tr>').find('[role=alert]').text(message);})
      .always(function(){results.find('.overlay').remove();button.prop('disabled',false);});
  });
</script>
<script>$(".report-sales-and-payments-active-li, .reports-menu").addClass("active");</script>
