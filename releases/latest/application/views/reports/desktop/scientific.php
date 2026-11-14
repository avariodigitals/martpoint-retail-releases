<?php $this->load->view('reports/desktop/_styles'); ?>
<?php
  $filters = $report_meta['filters'];
  $job_statuses = array('open','scheduled','in_progress','awaiting_parts','completed','cancelled');
?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= htmlspecialchars($report_meta['sub']); ?></div>
  </div>
  <div class="mp-report-actions">
    <?php $this->load->view('components/export_btn', ['tableId' => 'report-data']); ?>
  </div>
</div>

<form class="form-horizontal" id="report-form" onkeypress="return event.keyCode != 13;">
  <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
  <input type="hidden" id="base_url" value="<?php echo $base_url; ?>">
  <input type="hidden" name="report" id="report" value="<?= htmlspecialchars($report); ?>">

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

        <?php if(in_array('customer', $filters)): ?>
        <div class="mp-form-group">
          <label for="customer_id"><?= mp_label('customer'); ?></label>
          <select class="form-control select2" id="customer_id" name="customer_id" style="width:100%;"></select>
        </div>
        <?php endif; ?>

        <?php if(in_array('date_range', $filters)): ?>
        <div class="mp-form-group">
          <label for="from_date"><?= $this->lang->line('from_date'); ?></label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker" id="from_date" name="from_date" readonly>
          </div>
        </div>
        <div class="mp-form-group">
          <label for="to_date"><?= $this->lang->line('to_date'); ?></label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker" id="to_date" name="to_date" readonly>
          </div>
        </div>
        <?php endif; ?>

        <?php if(in_array('days', $filters)): ?>
        <div class="mp-form-group">
          <label for="days">Within (days)</label>
          <input type="number" class="form-control mp-form-control" id="days" name="days" value="30" min="1" placeholder="e.g. 30">
          <span class="mp-form-hint">Show items due/expiring within this many days</span>
        </div>
        <?php endif; ?>

        <?php if($report === 'service_jobs'): ?>
        <div class="mp-form-group">
          <label for="status">Job status</label>
          <select class="form-control" id="status" name="status">
            <option value="">All statuses</option>
            <?php foreach($job_statuses as $st): ?>
            <option value="<?= $st; ?>"><?= ucwords(str_replace('_',' ',$st)); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mp-form-group">
          <label for="engineer_id">Engineer</label>
          <select class="form-control" id="engineer_id" name="engineer_id">
            <option value="">All engineers</option>
            <?php foreach($engineers as $e): ?>
            <option value="<?= $e->id; ?>"><?= htmlspecialchars($e->username); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>

        <?php if(in_array('search', $filters)): ?>
        <div class="mp-form-group">
          <label for="search">Search</label>
          <input type="text" class="form-control mp-form-control" id="search" name="search" placeholder="Serial, item, model, <?= strtolower(mp_label('customer')); ?>…">
        </div>
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

<div class="mp-report-results">
  <div class="mp-card-head"><h3><?= $this->lang->line('records_table'); ?></h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table class="table table-bordered table-hover" id="report-data" style="width:100%;">
        <thead>
          <tr class="bg-blue">
            <th>#</th>
            <?php foreach($report_meta['columns'] as $col): ?>
            <th><?= htmlspecialchars($col); ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody id="tbodyid">
          <tr><td colspan="<?= count($report_meta['columns']) + 1; ?>" class="text-center text-muted">Set filters and press Show.</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if(in_array('customer', $filters)): ?>
<script src="<?php echo $theme_link; ?>js/ajaxselect/customer_select_ajax.js"></script>
<script>function getCustomerSelectionId() { return '#customer_id'; }</script>
<?php endif; ?>
<script type="text/javascript">
  var base_url = $("#base_url").val();
  $("#view, #view_all").on("click", function(){
    $(".mp-report-results").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.post($("#base_url").val() + "reports/show_scientific_report", $("#report-form").serialize(), function(result){
      $("#tbodyid").empty().append(result);
      $(".overlay").remove();
    });
  });
  $(function(){ if($.fn.datepicker){ $('.datepicker').datepicker({autoclose:true, format:'dd-mm-yyyy'}); } });
</script>
<script>$(".reports-menu").addClass("active");</script>
