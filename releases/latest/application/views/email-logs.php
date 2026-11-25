<?php
/* Email Logs list — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<div class="mp-page-head"><h1 class="mp-page-title"><?= $page_title ?></h1></div>

<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Email Logs</h3>
    <div class="box-tools pull-right">
      <a href="<?=base_url('email_settings');?>" class="btn btn-sm btn-default"><i class="fa fa-cog"></i> Settings</a>
    </div>
  </div>
  <div class="box-body">
    <div class="alert alert-info" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
      <div><strong>Order email recovery</strong><br><span>The included 1,000 monthly transactional emails need no activation or purchased credits. Eligible failed order owner/customer emails: <b><?= number_format((int)($recoverable_order_email_failures??0)); ?></b>. Check the failure reason, fix the cause, then retry. Emails with uncertain or partial send results and orders that already have a successful send are protected from duplicates. Retries use the saved order and current template. Maximum 25 per click.</span></div>
      <button type="button" class="btn btn-warning" id="btn-resend-order-emails" <?= empty($recoverable_order_email_failures)?'disabled':''; ?>><i class="fa fa-refresh"></i> Retry failed order emails</button>
    </div>
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
          <th>Failure reason</th>
          <th>Triggered By</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="logs-body">
        <tr><td colspan="10" class="text-center text-muted">Loading...</td></tr>
      </tbody>
    </table>

    <div class="text-center">
      <button class="btn btn-default btn-sm" id="btn-prev" disabled><i class="fa fa-chevron-left"></i> Prev</button>
      <span id="pagination-info" class="text-muted" style="margin:0 10px;">Page 1</span>
      <button class="btn btn-default btn-sm" id="btn-next" disabled>Next <i class="fa fa-chevron-right"></i></button>
    </div>
  </div>
</div>

<script>
var currentPage = 1;
var totalPages = 1;

function csrfData() {
  return { '<?=$this->security->get_csrf_token_name();?>': '<?=$this->security->get_csrf_hash();?>' };
}

function escapeHtml(value) {
  return String(value == null ? '' : value).replace(/[&<>"']/g, function(char){
    return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char];
  });
}

function loadLogs(page) {
  var tbody = $('#logs-body');
  tbody.html('<tr><td colspan="10" class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');

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
        tbody.html('<tr><td colspan="10" class="text-center text-danger">Failed to load logs.</td></tr>');
        return;
      }

      var html = '';
      if(!res.data || res.data.length === 0){
        html = '<tr><td colspan="10" class="text-center text-muted">No logs found.</td></tr>';
      } else {
        $.each(res.data, function(i, row){
          var reason = row.error_message || '';
          var statusBadge = row.status === 'sent'
            ? '<span class="label label-success">Sent</span>'
            : '<span class="label label-danger">' + escapeHtml(row.status || 'Failed') + '</span>';

          var orderEmailType = row.email_type === 'online_order_owner' || row.email_type === 'online_order_customer';
          var uncertain = /accepted before this pause|awaiting confirmation|acceptance is unknown|unknown acceptance|partial send|partially accepted|accepted by provider/i.test(reason);
          var hasOrder = /^\d+$/.test(String(row.related_record_id || ''));
          var retryBtn = row.status === 'failed' && row.related_module === 'storefront' && orderEmailType && hasOrder && !uncertain
            ? '<button class="btn btn-xs btn-warning btn-retry" data-id="' + escapeHtml(row.id) + '"><i class="fa fa-refresh"></i> Retry order email</button>'
            : '';

          html += '<tr>';
          html += '<td>' + escapeHtml(row.id || '-') + '</td>';
          html += '<td><code>' + escapeHtml(row.email_type || '-') + '</code></td>';
          html += '<td>' + escapeHtml(row.provider_used || '-') + '</td>';
          html += '<td>' + escapeHtml(row.recipient || '-') + '</td>';
          html += '<td>' + escapeHtml(row.subject || '-') + '</td>';
          html += '<td>' + statusBadge + '</td>';
          html += '<td style="max-width:340px;white-space:normal;overflow-wrap:anywhere">' + (reason ? escapeHtml(reason) : '<span class="text-muted">—</span>') + '</td>';
          html += '<td>' + escapeHtml(row.triggered_by || '-') + '</td>';
          html += '<td>' + escapeHtml(row.created_at || '-') + '</td>';
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
      tbody.html('<tr><td colspan="10" class="text-center text-danger">Request failed.</td></tr>');
    }
  });
}

$('#btn-resend-order-emails').on('click', function(){
  if(!window.confirm('Retry up to 25 eligible failed storefront order emails? Emails with uncertain or partial send results and orders that already have a successful send are skipped.')) return;
  var btn=$(this); btn.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Sending…');
  $.ajax({
    type:'POST', url:'<?=base_url("email_settings/resend_failed_order_emails");?>',
    data:csrfData(), dataType:'json',
    success:function(res){
      toastr[res.success?'success':'warning'](res.message || 'Order email recovery finished.');
      loadLogs(1);
      window.setTimeout(function(){window.location.reload();},800);
    },
    error:function(){btn.prop('disabled',false).html('<i class="fa fa-refresh"></i> Resend failed order emails');toastr['error']('Order email recovery request failed.');}
  });
});

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
      btn.attr('disabled', false).html('<i class="fa fa-refresh"></i> Retry order email');
      if(res.success){
        toastr['success'](res.message);
        loadLogs(currentPage);
      } else {
        toastr['error'](res.message);
      }
    },
    error: function(){
      btn.attr('disabled', false).html('<i class="fa fa-refresh"></i> Retry order email');
      toastr['error']('Retry request failed.');
    }
  });
});

// Load initial
loadLogs(1);
</script>
<script>$('.smtp-active-li').addClass('active');$('.smtp-active-li').closest(".mp-nav-group").addClass("open");</script>
