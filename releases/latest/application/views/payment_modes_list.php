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
      <h1>
        Payment Modes
        <small>View/Search Records</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Payment Modes</li>
      </ol>
    </section>
    <section class="content">
      <div class="row">
        <?php include"comman/code_flashdata.php"; ?>
        <input type="hidden" id='base_url' value="<?=$base_url;?>">
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Payment Modes</h3>
              <?php if($CI->permissions('payment_modes_add')) { ?>
              <div class="box-tools">
                <a class="btn btn-block btn-info" href="<?php echo $base_url; ?>payment_modes/add">
                <i class="fa fa-plus"></i> Create New</a>
              </div>
              <?php } ?>
            </div>
            <div class="box-body">
              <table id="example2" class="table table-bordered custom_hover" width="100%">
                <thead class="bg-gray">
                <tr>
                  <th>Name</th>
                  <th>Code</th>
                  <th>Status</th>
                  <th>Default</th>
                  <th>Ref Required</th>
                  <th>Confirm Required</th>
                  <th>Affects Cash</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody></tbody>
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
<script type="text/javascript">
$(document).ready(function() {
   var table = $('#example2').DataTable({
      dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
      buttons: {
        buttons: [
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat' },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat' },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat' },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat' },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat' },
            { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat', text:'Columns' }
        ]
      },
      "processing": true,
      "serverSide": true,
      "order": [],
      "responsive": true,
      language: { processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>' },
      "ajax": { "url": "<?php echo site_url('payment_modes/ajax_list')?>", "type": "POST" },
      "columnDefs": [
        { "targets": [ 7 ], "orderable": false },
        { "targets": [], "className": "text-center" }
      ]
   });
   new $.fn.dataTable.FixedHeader( table );
});

function delete_payment_mode(id) {
  if(typeof swal === 'undefined'){
    if(!confirm('Are you sure?')) return;
    doDeletePaymentMode(id);
    return;
  }
  swal({
    title: "Delete Payment Mode?",
    text: "Are you sure you want to delete this payment mode?",
    icon: "warning",
    buttons: true,
    dangerMode: true
  }).then(function(willDelete){
    if(willDelete) doDeletePaymentMode(id);
  });
}
function doDeletePaymentMode(id){
  $.post(base_url + 'payment_modes/delete_payment_mode', { q_id: id }, function(res) {
    if(res == 'success') {
      toastr['success']('Deleted successfully');
      $('#example2').DataTable().ajax.reload();
    } else {
      toastr['error'](res);
    }
  });
}
</script>
<!-- Make sidebar menu selector -->
<script>$(".payment_modes-active-li").addClass("active");</script>
</body>
</html>
