<?php $this->load->view('reports/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Purchase recommendations based on sell-through velocity, not guesswork</div>
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
          <label for="days_window">Analysis Window (days)</label>
          <input type="number" min="7" max="365" step="1" class="form-control" id="days_window" name="days_window" value="30">
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
            <th>Item</th>
            <th>Category</th>
            <th class="text-right">Current Stock</th>
            <th class="text-right">Sold (window)</th>
            <th class="text-right">Avg Daily</th>
            <th class="text-right">Reorder Point</th>
            <th class="text-right">Suggested Qty</th>
            <th>Urgency</th>
            <th class="text-right">Est. Cost</th>
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
      url: base_url + 'reports/show_reorder_suggestion_report',
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
  $("#view").on("click", function(){ load_records(); });
</script>
<script>$(".report-reorder-suggestion-active-li").addClass("active");</script>
