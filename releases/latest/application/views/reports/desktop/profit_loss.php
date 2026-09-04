<?php $this->load->view('reports/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Profit and loss statement for a selected date range</div>
  </div>
  <div class="mp-report-actions">
    <?php $this->load->view('components/export_btn', ['tableId' => 'report-data-2']); ?>
  </div>
</div>

<form class="form" id="report-form" onkeypress="return event.keyCode != 13;">
  <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
  <input type="hidden" id="base_url" value="<?php echo $base_url; ?>">
  <input type="hidden" id="warehouse_id" value="<?= get_store_warehouse_id(); ?>">

  <div class="mp-report-filter">
    <div class="mp-card-head"><h3>Report Filters</h3></div>
    <div class="mp-card-body">
      <div class="mp-form-grid">

        <div class="mp-form-group">
          <label for="pl-daterange-btn">Select Date Range</label>
          <div class="input-group">
            <button type="button" class="btn btn-default" id="pl-daterange-btn" name="pl-daterange-btn">
              <span><i class="fa fa-calendar"></i> Select Date Range</span>
              <i class="fa fa-caret-down"></i>
            </button>
          </div>
          <span id="sku_msg" style="display:none" class="text-danger"></span>
        </div>

        <?php if(store_module() && is_admin()): ?>
        <div class="mp-form-group">
          <?php $this->load->view('store/store_code', ['show_store_select_box' => true, 'store_id' => get_current_store_id(), 'div_length' => '', 'label_length' => '', 'show_all' => 'true']); ?>
        </div>
        <?php else: ?>
        <input type="hidden" name="store_id" id="store_id" value="<?= get_current_store_id(); ?>">
        <?php endif; ?>

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

<div class="mp-report-results" style="margin-bottom:24px;">
  <div class="mp-card-head">
    <h3>Profit Summary</h3>
    <?php $this->load->view('components/export_btn', ['tableId' => 'report-data-2']); ?>
  </div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table class="table table-bordered table-hover" id="report-data-2" style="width:100%;">
        <tbody>
          <tr>
            <td><?= $this->lang->line('gross_profit'); ?></td>
            <td class="text-right text-bold gross_profit"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('net_profit'); ?></td>
            <td class="text-right text-bold tot_net_profit"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
  <div class="mp-report-results" style="margin-bottom:24px;">
    <div class="mp-card-head">
      <h3>Purchase & Stock</h3>
      <?php $this->load->view('components/export_btn', ['tableId' => 'report-data']); ?>
    </div>
    <div class="box-body">
      <div class="mp-dt-scroll">
        <table class="table table-bordered table-hover" id="report-data" style="width:100%;">
          <tr>
            <td><?= $this->lang->line('opening_stock'); ?></td>
            <td class="text-right text-bold opening_stock_price"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('closing_stock'); ?><br><small class="text-primary">(By Purchase Price)</small></td>
            <td class="text-right text-bold closing_stock_price"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td colspan="2" class="text-bold font-italic text-primary"><?= $this->lang->line('purchase'); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('total_purchase'); ?></td>
            <td class="text-right text-bold pur_total"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('total_purchase_tax'); ?></td>
            <td class="text-right text-bold purchase_tax_amt"><?php echo $CI->currency(number_format((0),2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('total_other_charges_of_purchase'); ?></td>
            <td class="text-right text-bold pur_other_charges_amt"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('total_discount_on_purchase'); ?></td>
            <td class="text-right text-bold purchase_discount_amt"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('paid_amount'); ?></td>
            <td class="text-right text-bold text-success purchase_paid_amount"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('purchase_due'); ?></td>
            <td class="text-right text-bold text-danger purchase_due_total"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td colspan="2" class="text-bold font-italic text-primary"><?= $this->lang->line('purchase_return'); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('total_purchase_return'); ?></td>
            <td class="text-right text-bold pur_return_total"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('total_purchase_return_tax'); ?></td>
            <td class="text-right text-bold purchase_return_tax_amt"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('total_other_charges_of_purchase_return'); ?></td>
            <td class="text-right text-bold pur_return_other_charges_amt"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('total_discount_on_purchase_return'); ?></td>
            <td class="text-right text-bold purchase_return_discount_amt"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('paid_amount'); ?></td>
            <td class="text-right text-bold text-success purchase_return_paid_amount"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('purchase_return_due'); ?></td>
            <td class="text-right text-bold text-danger purchase_return_due_total"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <div class="mp-report-results" style="margin-bottom:24px;">
    <div class="mp-card-head">
      <h3>Sales & Returns</h3>
      <?php $this->load->view('components/export_btn', ['tableId' => 'report-data-4']); ?>
    </div>
    <div class="box-body">
      <div class="mp-dt-scroll">
        <table class="table table-bordered table-hover" id="report-data-4" style="width:100%;">
          <tr>
            <td><?= $this->lang->line('total_expense'); ?></td>
            <td class="text-right text-bold exp_total"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td colspan="2" class="text-bold font-italic text-primary"><?= $this->lang->line('sales'); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('sales'); ?> (<?= $this->lang->line('before_tax'); ?>)</td>
            <td class="text-right text-bold sal_total"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('total_sales_tax'); ?></td>
            <td class="text-right text-bold sales_tax_amt"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('total_other_charges_of_sales'); ?></td>
            <td class="text-right text-bold sal_other_charges_amt"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('total_discount_on_sales'); ?></td>
            <td class="text-right text-bold sales_discount_amt"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('couponDiscount'); ?></td>
            <td class="text-right text-bold coupon_discount_amt"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('total_sales'); ?></td>
            <td class="text-right text-bold text-danger net_sales bg-gray"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('paid_amount'); ?></td>
            <td class="text-right text-bold text-success sales_paid_amount"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('sales_due'); ?></td>
            <td class="text-right text-bold text-danger sales_due_total"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td colspan="2" class="text-bold font-italic text-primary"><?= $this->lang->line('sales_return'); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('total_sales_return'); ?></td>
            <td class="text-right text-bold sal_return_total"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('total_sales_return_tax'); ?></td>
            <td class="text-right text-bold sales_return_tax_amt"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('total_other_charges_of_sales_return'); ?></td>
            <td class="text-right text-bold sal_return_other_charges_amt"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('couponDiscount'); ?></td>
            <td class="text-right text-bold return_coupon_discount_amt"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('total_discount_on_sales_return'); ?></td>
            <td class="text-right text-bold sales_return_discount_amt"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('return_total'); ?></td>
            <td class="text-right text-bold text-success sales_return_total bg-gray"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('paid_amount'); ?></td>
            <td class="text-right text-bold text-success sales_return_paid_amount"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
          <tr>
            <td><?= $this->lang->line('sales_return_due'); ?></td>
            <td class="text-right text-bold text-danger sales_return_due_total"><?php echo $CI->currency(number_format(0,2,'.','')); ?></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="<?php echo $theme_link; ?>js/sheetjs.js" type="text/javascript"></script>
<script type="text/javascript">
  var base_url = $("#base_url").val();

  function get_pl_values(){
    var store_id = $("#store_id").val();
    var warehouse_id = $("#warehouse_id").val();
    var from_date = _get_start_date('pl-daterange-btn');
    var to_date = _get_end_date('pl-daterange-btn');

    $(".mp-report-results").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.post(base_url + "reports/get_profit_loss_report", {
      store_id: store_id,
      warehouse_id: warehouse_id,
      from_date: from_date,
      to_date: to_date
    }, function(result){
      var data = jQuery.parseJSON(result);
      $.each(data, function(index, element) {
        $("." + index).html(element);
      });
      $(".overlay").remove();
    });
  }

  $("#store_id").on("change", function(){
    get_pl_values();
  });

  $("#view").on("click", function(){
    get_pl_values();
  });

  $(function() {
    var start = moment().subtract(29, 'days');
    var end = moment();
    function cb(start, end) {
      $('#pl-daterange-btn span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }
    cb(start, end);

    $('#pl-daterange-btn').daterangepicker({
      ranges: {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
      },
      startDate: moment().subtract(29, 'days'),
      endDate: moment()
    }, function(start, end) {
      $('#pl-daterange-btn span').html(start.format('<?= isset($VIEW_DATE) ? strtoupper($VIEW_DATE) : 'DD-MM-YYYY'; ?>') + ' - ' + end.format('<?= isset($VIEW_DATE) ? strtoupper($VIEW_DATE) : 'DD-MM-YYYY'; ?>'));
    });

    $('#pl-daterange-btn').on('apply.daterangepicker', function(ev, picker) {
      get_pl_values();
    });

    function _get_start_date(input_id){
      return $('#' + input_id).data('daterangepicker').startDate.format('<?= isset($VIEW_DATE) ? strtoupper($VIEW_DATE) : 'DD-MM-YYYY'; ?>');
    }
    function _get_end_date(input_id){
      return $('#' + input_id).data('daterangepicker').endDate.format('<?= isset($VIEW_DATE) ? strtoupper($VIEW_DATE) : 'DD-MM-YYYY'; ?>');
    }

    window._get_start_date = _get_start_date;
    window._get_end_date = _get_end_date;

    get_pl_values();
  });
</script>
<script>$(".report-profit-loss-active-li").addClass("active");</script>
