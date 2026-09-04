<?php $this->load->view('reports/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Identify slow and dead stock by age bucket</div>
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
          <label for="category_id"><?= $this->lang->line('category'); ?></label>
          <select class="form-control select2" id="category_id" name="category_id" style="width:100%;">
            <option value="">-All-</option>
            <?= get_categories_select_list(null, get_current_store_id()); ?>
          </select>
        </div>

        <div class="mp-form-group">
          <label for="aging_bucket">Aging Bucket</label>
          <select class="form-control select2" id="aging_bucket" name="aging_bucket" style="width:100%;">
            <option value="">-All Buckets-</option>
            <option value="fast">0-30 days (Fast)</option>
            <option value="fast2">31-60 days (Fast)</option>
            <option value="med">61-90 days (Medium)</option>
            <option value="slow">91-180 days (Slow)</option>
            <option value="vslow">181-365 days (Very Slow)</option>
            <option value="dead">365+ / Never Sold (Dead Stock)</option>
          </select>
        </div>

        <div class="mp-form-group">
          <label for="as_of_date">As of Date</label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker" id="as_of_date" name="as_of_date" value="<?php echo show_date(date('d-m-Y')); ?>">
          </div>
          <span id="as_of_date_msg" style="display:none" class="text-danger"></span>
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
            <th>Item</th>
            <th>Category</th>
            <th class="text-right">Stock Qty</th>
            <th class="text-right">Stock Value (Cost)</th>
            <th>Last Sold</th>
            <th class="text-right">Days Since</th>
            <th>Bucket</th>
          </tr>
        </thead>
        <tbody id="tbodyid"></tbody>
      </table>
    </div>
  </div>
</div>

<script type="text/javascript">
  var base_url = $("#base_url").val();
  $("#store_id").on("change", function(){
    var store_id = $(this).val();
    $.post(base_url + "sales/get_categories_select_list", { store_id: store_id }, function(result){
      result = '<option value="">All</option>' + result;
      $("#category_id").html('').append(result).select2();
    });
  });

  function load_records(){
    $(".mp-report-results").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    data = new FormData($('#report-form')[0]);
    if(!xss_validation(data)){ return false; }
    $("#view").attr('disabled', true);
    $.ajax({
      type: 'POST',
      url: base_url + 'reports/show_inventory_aging_report',
      data: data,
      cache: false,
      contentType: false,
      processData: false,
      success: function(result){
        $("#tbodyid").empty().append(result);
        $("#view").attr('disabled', false);
        $(".overlay").remove();
      }
    });
  }
  $("#view").on("click", function(){
    check_field("as_of_date");
    load_records();
  });
  function check_field(id){
    if(!$("#" + id).val()){
      $('#' + id + '_msg').fadeIn(200).show().html('Required Field').addClass('required');
      flag = false;
    } else {
      $('#' + id + '_msg').fadeOut(200).hide();
    }
  }
</script>
<script>$(".report-inventory-aging-active-li").addClass("active");</script>
