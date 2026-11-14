<?php $this->load->view('reports/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Track cash in and out across sales, purchases, expenses and deposits</div>
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
          <label for="from_date"><?= $this->lang->line('from_date'); ?></label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker" id="from_date" name="from_date" value="<?php echo show_date(date('01-m-Y')); ?>">
          </div>
          <span id="from_date_msg" style="display:none" class="text-danger"></span>
        </div>

        <div class="mp-form-group">
          <label for="to_date"><?= $this->lang->line('to_date'); ?></label>
          <div class="input-group date">
            <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
            <input type="text" class="form-control pull-right datepicker" id="to_date" name="to_date" value="<?php echo show_date(date('d-m-Y')); ?>">
          </div>
          <span id="to_date_msg" style="display:none" class="text-danger"></span>
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

<div id="cf-empty" class="mp-report-results" style="text-align:center;padding:48px 20px;">
  <div class="mp-empty-state">
    <div class="mp-empty-icon"><i class="fa fa-tachometer"></i></div>
    <h4>Cash Flow Summary</h4>
    <p>Select a date range and click <strong>Show</strong> to view the cash flow statement.</p>
  </div>
</div>

<div id="cf-results" style="display:none;">
  <div class="mp-kpi-grid" style="margin-bottom:24px;">
    <div class="mp-kpi-card cash">
      <div class="mp-kpi-icon"><i class="fa fa-arrow-down"></i></div>
      <div class="mp-kpi-label">Total Cash In</div>
      <div class="mp-kpi-value" id="cf-in" style="color:var(--mp-success);">0</div>
    </div>
    <div class="mp-kpi-card expense">
      <div class="mp-kpi-icon"><i class="fa fa-arrow-up"></i></div>
      <div class="mp-kpi-label">Total Cash Out</div>
      <div class="mp-kpi-value" id="cf-out" style="color:var(--mp-danger);">0</div>
    </div>
    <div class="mp-kpi-card sales">
      <div class="mp-kpi-icon"><i class="fa fa-balance-scale"></i></div>
      <div class="mp-kpi-label">Net Cash Movement</div>
      <div class="mp-kpi-value" id="cf-net">0</div>
    </div>
  </div>

  <div class="mp-report-results">
    <div class="mp-card-head"><h3>Cash Movement Breakdown</h3></div>
    <div class="box-body">
      <div class="mp-dt-scroll">
        <table class="table table-bordered table-hover" id="report-data" style="width:100%;">
          <thead>
            <tr class="bg-blue">
              <th>#</th>
              <th>Source</th>
              <th>Direction</th>
              <th class="text-right">Amount</th>
            </tr>
          </thead>
          <tbody id="tbodyid"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  var base_url = $("#base_url").val();
  function fmt(n){ return (n * 1).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }

  function load_records(){
    $(".mp-report-results").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    data = new FormData($('#report-form')[0]);
    if(!xss_validation(data)){ return false; }
    $("#view").attr('disabled', true);
    $.ajax({
      type: 'POST',
      url: base_url + 'reports/show_cash_flow_report',
      data: data,
      cache: false,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function(r){
        $("#cf-empty").hide();
        $("#cf-results").show();
        $("#cf-in").text(fmt(r.in_total));
        $("#cf-out").text(fmt(r.out_total));
        var netEl = $("#cf-net");
        netEl.text(fmt(r.net));
        netEl.css('color', r.net >= 0 ? 'var(--mp-success)' : 'var(--mp-danger)');
        var rows = '';
        $.each(r.lines, function(i, l){
          rows += '<tr>';
          rows += '<td>' + (i + 1) + '</td>';
          rows += '<td>' + l.label + '</td>';
          rows += '<td>' + (l.direction === 'in' ? '<span class="label label-success">In</span>' : '<span class="label label-danger">Out</span>') + '</td>';
          rows += '<td class="text-right">' + fmt(l.amount) + '</td>';
          rows += '</tr>';
        });
        rows += '<tr class="bg-gray-active"><td colspan="3" class="text-right text-bold">Net Cash Movement</td><td class="text-right text-bold ' + (r.net >= 0 ? 'text-success' : 'text-danger') + '">' + fmt(r.net) + '</td></tr>';
        $("#tbodyid").empty().append(rows);
        $("#view").attr('disabled', false);
        $(".overlay").remove();
      },
      error: function(){
        $("#view").attr('disabled', false);
        $(".overlay").remove();
        toastr.error('Could not load cash flow data.');
      }
    });
  }

  $("#view").on("click", function(){
    check_field("from_date");
    check_field("to_date");
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
<script>$(".report-cash-flow-active-li").addClass("active");</script>
