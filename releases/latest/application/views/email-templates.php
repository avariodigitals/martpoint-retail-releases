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
              <h3 class="box-title">Email Templates</h3>
              <div class="box-tools pull-right">
                <a href="<?=base_url('email_settings/seed_templates');?>" class="btn btn-sm btn-warning"><i class="fa fa-refresh"></i> Restore Defaults</a>
                <a href="<?=base_url('email_settings');?>" class="btn btn-sm btn-default"><i class="fa fa-cog"></i> Settings</a>
              </div>
            </div>
            <div class="box-body">
              <p class="text-muted">Use placeholders like <code>{customer_name}</code>, <code>{invoice_number}</code>, <code>{amount_due}</code> and <code>{payment_link}</code> to automatically insert business data into emails.</p>
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Template</th>
                    <th>Key</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Copy to Owner</th>
                    <th style="width:180px;">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($templates as $t): ?>
                  <tr>
                    <td><?=htmlspecialchars($t->template_name);?></td>
                    <td><code><?=htmlspecialchars($t->template_key);?></code></td>
                    <td><?=htmlspecialchars($t->subject);?></td>
                    <td>
                      <?php if($t->status): ?>
                        <span class="label label-success">Enabled</span>
                      <?php else: ?>
                        <span class="label label-default">Disabled</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if($t->send_copy_to_owner): ?>
                        <span class="label label-info">Yes</span>
                      <?php else: ?>
                        <span class="label label-default">No</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <a href="<?=base_url('email_settings/template_edit/'.$t->id);?>" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Edit</a>
                      <button class="btn btn-xs btn-info btn-preview" data-id="<?=$t->id;?>"><i class="fa fa-eye"></i> Preview</button>
                      <button class="btn btn-xs btn-success btn-test-template" data-id="<?=$t->id;?>"><i class="fa fa-paper-plane"></i> Test</button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
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

<!-- Test Template Modal -->
<div class="modal fade" id="modal-test-template">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Send Test Email</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="test_template_id">
        <div class="form-group">
          <label>Recipient Email</label>
          <input type="email" class="form-control" id="test_template_email" placeholder="Enter email address">
        </div>
        <div class="form-group">
          <label>Sample Data (JSON)</label>
          <textarea class="form-control" id="test_template_data" rows="4" placeholder='{"customer_name":"John","invoice_number":"INV001","amount_due":"1000"}'></textarea>
          <small class="text-muted">Enter JSON object with placeholder values for this template.</small>
        </div>
        <div id="test_template_result" class="text-muted"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" id="btn-send-test-template"><i class="fa fa-paper-plane"></i> Send Test</button>
      </div>
    </div>
  </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="modal-preview-template">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Template Preview</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Sample Data (JSON)</label>
          <textarea class="form-control" id="preview_template_data" rows="3" placeholder='{"customer_name":"John","store_name":"My Store"}'></textarea>
        </div>
        <button type="button" class="btn btn-info btn-sm" id="btn-refresh-preview"><i class="fa fa-refresh"></i> Refresh Preview</button>
        <hr>
        <h5>Subject:</h5>
        <div class="well well-sm" id="preview_subject"></div>
        <h5>HTML Body:</h5>
        <div class="well" id="preview_html" style="background:#fff;"></div>
        <h5>Missing Placeholders:</h5>
        <div id="preview_missing" class="text-muted"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
var currentPreviewId = null;

function csrfData() {
  return { '<?=$this->security->get_csrf_token_name();?>': '<?=$this->security->get_csrf_hash();?>' };
}

$('.btn-test-template').on('click', function(){
  $('#test_template_id').val($(this).data('id'));
  $('#test_template_email').val('');
  $('#test_template_data').val('');
  $('#test_template_result').removeClass('text-success text-danger').addClass('text-muted').text('');
  $('#modal-test-template').modal('show');
});

$('#btn-send-test-template').on('click', function(){
  var btn = $(this); btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
  var result = $('#test_template_result');

  $.ajax({
    type: 'POST',
    url: '<?=base_url("email_settings/template_test");?>',
    data: $.extend(csrfData(), {
      template_id: $('#test_template_id').val(),
      test_email:  $('#test_template_email').val(),
      sample_data: $('#test_template_data').val()
    }),
    dataType: 'json',
    success: function(res){
      btn.attr('disabled', false).html('<i class="fa fa-paper-plane"></i> Send Test');
      if(res.status === 'success'){
        result.removeClass('text-danger text-muted').addClass('text-success').text(res.message);
      } else {
        result.removeClass('text-success text-muted').addClass('text-danger').text(res.message);
      }
    },
    error: function(){
      btn.attr('disabled', false).html('<i class="fa fa-paper-plane"></i> Send Test');
      result.removeClass('text-success text-muted').addClass('text-danger').text('Request failed.');
    }
  });
});

$('.btn-preview').on('click', function(){
  currentPreviewId = $(this).data('id');
  $('#preview_template_data').val('');
  $('#preview_subject').text('');
  $('#preview_html').html('');
  $('#preview_missing').text('');
  $('#modal-preview-template').modal('show');
  $('#btn-refresh-preview').trigger('click');
});

$('#btn-refresh-preview').on('click', function(){
  if(!currentPreviewId) return;
  var btn = $(this); btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');

  $.ajax({
    type: 'POST',
    url: '<?=base_url("email_settings/template_preview");?>',
    data: $.extend(csrfData(), {
      template_id: currentPreviewId,
      sample_data: $('#preview_template_data').val()
    }),
    dataType: 'json',
    success: function(res){
      btn.attr('disabled', false).html('<i class="fa fa-refresh"></i> Refresh Preview');
      if(res.status === 'success'){
        $('#preview_subject').text(res.subject);
        $('#preview_html').html(res.html);
        $('#preview_missing').text(res.missing.length ? 'Missing: ' + res.missing.join(', ') : 'All placeholders resolved.');
      } else {
        $('#preview_subject').text('Error: ' + res.message);
      }
    },
    error: function(){
      btn.attr('disabled', false).html('<i class="fa fa-refresh"></i> Refresh Preview');
      $('#preview_subject').text('Request failed.');
    }
  });
});
</script>
<script>$('.smtp-active-li').addClass('active');</script>
</body>
</html>
