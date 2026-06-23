<!DOCTYPE html>
<html>
<head>
<?php include"comman/code_css.php"; ?>
<!-- bootstrap wysihtml5 - text editor -->
<link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

 <?php include"sidebar.php"; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo $base_url; ?>email_settings/templates">Email Templates</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-8">

          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title"><?= $template ? 'Edit: ' . htmlspecialchars($template->template_name) : 'New Template' ?></h3>
            </div>
            <div class="box-body">
              <form id="template-form">
                <input type="hidden" name="id" value="<?= $template ? $template->id : ''; ?>">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">

                <div class="form-group">
                  <label>Template Key <small class="text-muted">(used in code, no spaces)</small></label>
                  <input type="text" class="form-control" name="template_key" value="<?= $template ? htmlspecialchars($template->template_key) : ''; ?>" <?= $template ? 'readonly' : ''; ?> placeholder="invoice_sent">
                </div>

                <div class="form-group">
                  <label>Template Name</label>
                  <input type="text" class="form-control" name="template_name" value="<?= $template ? htmlspecialchars($template->template_name) : ''; ?>" placeholder="Invoice Sent">
                </div>

                <div class="form-group">
                  <label>Subject</label>
                  <input type="text" class="form-control" name="subject" value="<?= $template ? htmlspecialchars($template->subject) : ''; ?>" placeholder="Invoice {invoice_number} from {store_name}">
                </div>

                <div class="form-group">
                  <label>HTML Body</label>
                  <textarea class="textarea form-control" name="html_body" rows="10" placeholder="<p>Hello {customer_name},</p>"><?= $template ? htmlspecialchars($template->html_body) : ''; ?></textarea>
                  <small class="text-muted">Supports placeholders like {customer_name}, {invoice_number}, etc.</small>
                </div>

                <div class="form-group">
                  <label>Plain Text Body <small class="text-muted">(fallback for email clients that don't render HTML)</small></label>
                  <textarea class="form-control" name="text_body" rows="6" placeholder="Hello {customer_name},..."><?= $template ? htmlspecialchars($template->text_body) : ''; ?></textarea>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Status</label>
                      <select class="form-control" name="status">
                        <option value="1" <?= ($template && $template->status) ? 'selected' : ''; ?>>Enabled</option>
                        <option value="0" <?= ($template && !$template->status) ? 'selected' : ''; ?>>Disabled</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Send Copy to Store Owner</label>
                      <select class="form-control" name="send_copy_to_owner">
                        <option value="1" <?= ($template && $template->send_copy_to_owner) ? 'selected' : ''; ?>>Yes</option>
                        <option value="0" <?= ($template && !$template->send_copy_to_owner) ? 'selected' : ''; ?>>No</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <button type="button" id="save-template" class="btn btn-success"><i class="fa fa-save"></i> Save Template</button>
                  <a href="<?=base_url('email_settings/templates');?>" class="btn btn-warning">Cancel</a>
                </div>
              </form>
            </div>
          </div>

        </div>

        <div class="col-md-4">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Available Placeholders</h3>
            </div>
            <div class="box-body">
              <div class="form-group">
                <input type="text" class="form-control input-sm" id="placeholder-search" placeholder="Search placeholders...">
              </div>
              <div style="max-height:500px;overflow-y:auto;">
                <table class="table table-condensed table-hover" id="placeholder-table">
                  <tbody>
                    <tr><td><code>{store_name}</code></td><td>Store name</td></tr>
                    <tr><td><code>{store_email}</code></td><td>Store email</td></tr>
                    <tr><td><code>{store_phone}</code></td><td>Store phone</td></tr>
                    <tr><td><code>{store_address}</code></td><td>Store address</td></tr>
                    <tr><td><code>{branch_name}</code></td><td>Branch name</td></tr>
                    <tr><td><code>{customer_name}</code></td><td>Customer name</td></tr>
                    <tr><td><code>{customer_email}</code></td><td>Customer email</td></tr>
                    <tr><td><code>{customer_phone}</code></td><td>Customer phone</td></tr>
                    <tr><td><code>{staff_name}</code></td><td>Staff name</td></tr>
                    <tr><td><code>{invoice_number}</code></td><td>Invoice number</td></tr>
                    <tr><td><code>{invoice_date}</code></td><td>Invoice date</td></tr>
                    <tr><td><code>{invoice_total}</code></td><td>Invoice total</td></tr>
                    <tr><td><code>{amount_paid}</code></td><td>Amount paid</td></tr>
                    <tr><td><code>{amount_due}</code></td><td>Amount due</td></tr>
                    <tr><td><code>{payment_status}</code></td><td>Payment status</td></tr>
                    <tr><td><code>{payment_link}</code></td><td>Payment link</td></tr>
                    <tr><td><code>{receipt_link}</code></td><td>Receipt link</td></tr>
                    <tr><td><code>{report_date}</code></td><td>Report date</td></tr>
                    <tr><td><code>{total_sales}</code></td><td>Total sales</td></tr>
                    <tr><td><code>{total_profit}</code></td><td>Total profit</td></tr>
                    <tr><td><code>{total_expenses}</code></td><td>Total expenses</td></tr>
                    <tr><td><code>{net_position}</code></td><td>Net position</td></tr>
                    <tr><td><code>{cash_expected}</code></td><td>Cash expected</td></tr>
                    <tr><td><code>{purchase_due}</code></td><td>Purchase due</td></tr>
                    <tr><td><code>{outstanding_debts}</code></td><td>Outstanding debts</td></tr>
                    <tr><td><code>{payments_collected}</code></td><td>Payments collected</td></tr>
                    <tr><td><code>{low_stock_items}</code></td><td>Low stock items</td></tr>
                    <tr><td><code>{top_selling_products}</code></td><td>Top selling products</td></tr>
                    <tr><td><code>{login_link}</code></td><td>Login link</td></tr>
                    <tr><td><code>{reset_link}</code></td><td>Password reset link</td></tr>
                    <tr><td><code>{app_name}</code></td><td>App name</td></tr>
                    <tr><td><code>{current_year}</code></td><td>Current year</td></tr>
                  </tbody>
                </table>
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
<script src="<?php echo $theme_link; ?>plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>

<script>
$('.textarea').wysihtml5({
  toolbar: {
    "font-styles": true,
    "emphasis": true,
    "lists": true,
    "html": true,
    "link": true,
    "image": false,
    "color": false,
    "blockquote": true,
    "size": 'sm'
  }
});

$('#save-template').on('click', function(){
  var btn = $(this); btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
  $.ajax({
    type: 'POST',
    url: '<?=base_url("email_settings/template_save");?>',
    data: $('#template-form').serialize(),
    dataType: 'json',
    success: function(res){
      btn.attr('disabled', false).html('<i class="fa fa-save"></i> Save Template');
      if(res.status === 'success'){
        toastr['success'](res.message);
        setTimeout(function(){ window.location.href = '<?=base_url("email_settings/templates");?>'; }, 800);
      } else {
        toastr['error'](res.message);
      }
    },
    error: function(){
      btn.attr('disabled', false).html('<i class="fa fa-save"></i> Save Template');
      toastr['error']('Request failed.');
    }
  });
});

$('#placeholder-search').on('keyup', function(){
  var val = $(this).val().toLowerCase();
  $('#placeholder-table tbody tr').each(function(){
    var txt = $(this).text().toLowerCase();
    $(this).toggle(txt.indexOf(val) > -1);
  });
});
</script>
<script>$('.smtp-active-li').addClass('active');</script>
</body>
</html>
