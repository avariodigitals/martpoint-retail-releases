<!DOCTYPE html>
<html>
<head>
<?php include"comman/code_css.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php include"sidebar.php"; ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      <?=$page_title;?>
      <small></small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?php echo $base_url; ?>customers">Customers</a></li>
      <li class="active"><?=$page_title;?></li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <!-- ********** ALERT MESSAGE START******* -->
    <?php $this->load->view('comman/code_flashdata');?>
    <!-- ********** ALERT MESSAGE END******* -->
    <div class="row">
      <div class="col-md-10 col-md-offset-1">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-gift"></i> <?=$page_title;?></h3>
            <div class="box-tools pull-right">
              <?php if(!empty($customer)) { ?>
              <span class="label label-info" style="font-size:14px; padding:8px 12px;"><?php echo $customer->customer_name; ?></span>
              <?php } ?>
            </div>
          </div>
        <div class="box-body">
          <?php if(empty($packages)) { ?>
            <div class="alert alert-info text-center">
              <h4><i class="fa fa-info-circle"></i> No packages found</h4>
              <p>This customer has not purchased any packages yet.</p>
            </div>
          <?php } else { ?>
            <div class="row">
              <?php foreach($packages as $pkg) {
                $is_expired = (!empty($pkg->expiry_date) && $pkg->expiry_date != '0000-00-00' && $pkg->expiry_date < date('Y-m-d'));
                $status_class = $is_expired ? 'danger' : ($pkg->status == 'active' ? 'success' : 'default');
                $status_label = $is_expired ? 'Expired' : ucfirst($pkg->status);
              ?>
              <div class="col-md-4">
                <div class="box box-<?php echo $status_class; ?>" style="border-top:3px solid;">
                  <div class="box-header">
                    <h3 class="box-title"><?php echo $pkg->package_name; ?></h3>
                    <div class="box-tools">
                      <span class="label label-<?php echo $status_class; ?>"><?php echo $status_label; ?></span>
                    </div>
                  </div>
                  <div class="box-body text-center">
                    <?php if(!empty($pkg->package_image) && file_exists($pkg->package_image)) { ?>
                      <img src="<?php echo base_url($pkg->package_image); ?>" style="max-height:100px; border-radius:8px; margin-bottom:10px;">
                    <?php } else { ?>
                      <i class="fa fa-gift" style="font-size:48px; color:#ccc; margin-bottom:10px;"></i>
                    <?php } ?>
                    <h4 style="margin-top:0;"><?php echo $pkg->remaining_uses; ?> <small>remaining</small></h4>
                    <?php if(!empty($pkg->expiry_date) && $pkg->expiry_date != '0000-00-00') { ?>
                      <p class="text-muted">Expires: <?php echo show_date($pkg->expiry_date); ?></p>
                    <?php } ?>
                  </div>
                  <div class="box-footer">
                    <?php if($pkg->status == 'active' && !$is_expired && $pkg->remaining_uses > 0) { ?>
                      <button type="button" class="btn btn-primary btn-block btn-redeem" data-id="<?php echo $pkg->id; ?>">
                        <i class="fa fa-check-circle"></i> Redeem Service
                      </button>
                    <?php } else { ?>
                      <button type="button" class="btn btn-default btn-block" disabled>
                        <?php echo $is_expired ? 'Expired' : ($pkg->remaining_uses <= 0 ? 'Fully Redeemed' : 'Inactive'); ?>
                      </button>
                    <?php } ?>
                    <button type="button" class="btn btn-info btn-block btn-view-history" data-id="<?php echo $pkg->id; ?>" style="margin-top:6px;">
                      <i class="fa fa-history"></i> View History
                    </button>
                  </div>
                </div>
              </div>
              <?php } ?>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Redeem Modal -->
<div class="modal fade" id="redeemModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-check-circle"></i> Redeem Package Service</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="redeem_customer_package_id">
        <div class="form-group">
          <label>Select Service to Redeem</label>
          <select class="form-control" id="redeem_item_id"></select>
        </div>
        <div class="form-group">
          <label>Quantity</label>
          <input type="number" class="form-control" id="redeem_qty" value="1" min="1">
        </div>
        <div class="form-group">
          <label>Notes (optional)</label>
          <textarea class="form-control" id="redeem_notes" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="btn_confirm_redeem">Confirm Redemption</button>
      </div>
    </div>
  </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-history"></i> Redemption History</h4>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-striped" id="history_table">
          <thead>
            <tr><th>Date</th><th>Service</th><th>Qty</th><th>By</th></tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  var packageItems = {};

  // Load package items when redeem clicked
  $('.btn-redeem').click(function() {
    var cpid = $(this).data('id');
    $('#redeem_customer_package_id').val(cpid);
    $('#redeem_item_id').html('<option>Loading...</option>');
    $('#redeemModal').modal('show');

    $.getJSON("<?php echo base_url('service_packages/ajax_get_package_items_for_redeem'); ?>?customer_package_id=" + cpid, function(res) {
      packageItems[cpid] = res;
      var opts = '';
      $.each(res, function(i, it) {
        opts += '<option value="' + it.item_id + '">' + it.item_name + ' (Qty: ' + it.quantity + ')</option>';
      });
      $('#redeem_item_id').html(opts);
    });
  });

  $('#btn_confirm_redeem').click(function() {
    var cpid = $('#redeem_customer_package_id').val();
    var item_id = $('#redeem_item_id').val();
    var qty = $('#redeem_qty').val();
    var notes = $('#redeem_notes').val();

    $.post("<?php echo base_url('service_packages/ajax_redeem'); ?>", {
      customer_package_id: cpid,
      item_id: item_id,
      qty: qty,
      notes: notes,
      '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
    }, function(res) {
      var data = JSON.parse(res);
      if(data.status == 'success') {
        toastr['success'](data.message);
        $('#redeemModal').modal('hide');
        setTimeout(function() { location.reload(); }, 800);
      } else {
        toastr['error'](data.message);
      }
    });
  });

  $('.btn-view-history').click(function() {
    var cpid = $(this).data('id');
    $('#historyModal').modal('show');
    $('#history_table tbody').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');

    $.getJSON("<?php echo base_url('service_packages/ajax_redemptions'); ?>?customer_package_id=" + cpid, function(res) {
      var rows = '';
      if(res.length === 0) {
        rows = '<tr><td colspan="4" class="text-center text-muted">No redemptions yet</td></tr>';
      } else {
        $.each(res, function(i, r) {
          rows += '<tr><td>' + r.redeemed_at + '</td><td>' + (r.item_name || '-') + '</td><td>' + r.quantity_redeemed + '</td><td>' + r.redeemed_by + '</td></tr>';
        });
      }
      $('#history_table tbody').html(rows);
    });
  });
});
</script>
</div>
<!-- /.content-wrapper -->
<?php include"footer.php"; ?>
<div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
<!-- SOUND CODE -->
<?php include"comman/code_js_sound.php"; ?>
<!-- TABLES CODE -->
<?php include"comman/code_js.php"; ?>
</body>
</html>
