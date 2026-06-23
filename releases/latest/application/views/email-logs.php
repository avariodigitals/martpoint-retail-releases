<!DOCTYPE html>
<html>
<head>
<?php include"comman/code_css.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

 <?php include"sidebar.php"; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo $base_url; ?>email_settings">Email Settings</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">

          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Email Logs</h3>
              <div class="box-tools pull-right">
                <a href="<?=base_url('email_settings');?>" class="btn btn-sm btn-default"><i class="fa fa-cog"></i> Settings</a>
              </div>
            </div>
            <div class="box-body">
              <div class="row" style="margin-bottom:15px;">
                <div class="col-md-2">
                  <select class="form-control" id="filter-status" style="height:36px;">
                    <option value="">All Status</option>
                    <option value="sent">Sent</option>
                    <option value="failed">Failed</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <select class="form-control" id="filter-provider" style="height:36px;">
                    <option value="">All Providers</option>
                    <option value="smtp">SMTP</option>
                    <option value="resend">Resend</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <input type="text" class="form-control" id="filter-type" placeholder="Type (e.g. invoice)" style="height:36px;">
                </div>
                <div class="col-md-2">
                  <input type="text" class="form-control" id="filter-recipient" placeholder="Recipient" style="height:36px;">
                </div>
                <div class="col-md-2">
                  <input type="date" class="form-control" id="filter-date-from" placeholder="From" style="height:36px;">
                </div>
                <div class="col-md-2">
                  <button class="btn btn-primary btn-block" id="btn-refresh-logs" style="height:36px;padding-top:7px;"><i class="fa fa-refresh"></i> Refresh</button>
                </div>
              </div>

              <table class="table table-bordered table-striped table-hover">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Provider</th>
                    <th>Recipient</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Triggered By</th>
                    <th>Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="logs-body">
                  <tr><td colspan="9" class="text-center text-muted">Loading...</td></tr>
                </tbody>
              </table>

              <div class="text-center">
                <button class="btn btn-default btn-sm" id="btn-prev" disabled><i class="fa fa-chevron-left"></i> Prev</button>
                <span id="pagination-info" class="text-muted" style="margin:0 10px;">Page 1</span>
                <button class="btn btn-default btn-sm" id="btn-next" disabled>Next <i class="fa fa-chevron-right"></i></button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>
  </div>

 <?php include"footer.php"; ?>
  <div class="control-sidebar-bg"></div>
</div>

<?php include"comman/code_js_sound.php"; ?>
<?php include"comman/code_js.php"; ?>

<script>
var currentPage = 1;
var totalPages = 1;

function csrfData() {
  return { '<?=$this->security->get_csrf_token_name();?>': '<?=$this->security->get_csrf_hash();?>' };
}

function loadLogs(page) {
  var tbody = $('#logs-body');
  tbody.html('<tr><td colspan="9" class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');

  $.ajax({
    type: 'GET',
    url: '<?=base_url("email_settings/logs_ajax");?>',
    data: {
      page: page,
      limit: 50,
      status: $('#filter-status').val(),
      provider: $('#filter-provider').val(),
      email_type: $('#filter-type').val(),
      recipient: $('#filter-recipient').val(),
      date_from: $('#filter-date-from').val()
    },
    dataType: 'json',
    success: function(res){
      if(res.status !== 'success'){
        tbody.html('<tr><td colspan="9" class="text-center text-danger">Failed to load logs.</td></tr>');
        return;
      }

      var html = '';
      if(!res.data || res.data.length === 0){
        html = '<tr><td colspan="9" class="text-center text-muted">No logs found.</td></tr>';
      } else {
        $.each(res.data, function(i, row){
          var statusBadge = row.status === 'sent'
            ? '<span class="label label-success">Sent</span>'
            : '<span class="label label-danger" title="' + (row.error_message || '').replace(/"/g,'&quot;') + '">Failed</span>';

          var retryBtn = row.status === 'failed'
            ? '<button class="btn btn-xs btn-warning btn-retry" data-id="' + row.id + '"><i class="fa fa-refresh"></i> Retry</button>'
            : '';

          html += '<tr>';
          html += '<td>' + row.id + '</td>';
          html += '<td><code>' + (row.email_type || '-') + '</code></td>';
          html += '<td>' + (row.provider_used || '-') + '</td>';
          html += '<td>' + (row.recipient || '-') + '</td>';
          html += '<td>' + (row.subject || '-') + '</td>';
          html += '<td>' + statusBadge + '</td>';
          html += '<td>' + (row.triggered_by || '-') + '</td>';
          html += '<td>' + (row.created_at || '-') + '</td>';
          html += '<td>' + retryBtn + '</td>';
          html += '</tr>';
        });
      }
      tbody.html(html);

      var total = res.total || 0;
      totalPages = Math.max(1, Math.ceil(total / 50));
      currentPage = page;
      $('#pagination-info').text('Page ' + currentPage + ' of ' + totalPages + ' (' + total + ' records)');
      $('#btn-prev').prop('disabled', currentPage <= 1);
      $('#btn-next').prop('disabled', currentPage >= totalPages);
    },
    error: function(){
      tbody.html('<tr><td colspan="9" class="text-center text-danger">Request failed.</td></tr>');
    }
  });
}

$('#btn-refresh-logs').on('click', function(){ loadLogs(1); });
$('#btn-prev').on('click', function(){ if(currentPage > 1) loadLogs(currentPage - 1); });
$('#btn-next').on('click', function(){ if(currentPage < totalPages) loadLogs(currentPage + 1); });

$(document).on('click', '.btn-retry', function(){
  var btn = $(this); btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
  $.ajax({
    type: 'POST',
    url: '<?=base_url("email_settings/retry_email");?>',
    data: $.extend(csrfData(), { log_id: btn.data('id') }),
    dataType: 'json',
    success: function(res){
      btn.attr('disabled', false).html('<i class="fa fa-refresh"></i> Retry');
      if(res.success){
        toastr['success'](res.message);
        loadLogs(currentPage);
      } else {
        toastr['error'](res.message);
      }
    },
    error: function(){
      btn.attr('disabled', false).html('<i class="fa fa-refresh"></i> Retry');
      toastr['error']('Retry request failed.');
    }
  });
});

// Load initial
loadLogs(1);
</script>
<script>$('.smtp-active-li').addClass('active');</script>
</body>
</html>
