<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar');?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?=base_url('online_store');?>">Online Store</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-4">
          <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Generate QR Code</h3></div>
            <div class="box-body">
              <div class="form-group"><label>QR Name</label><input type="text" class="form-control" id="qr_name" placeholder="e.g. Store QR"></div>
              <div class="form-group"><label>QR Type</label>
                <select class="form-control" id="qr_type" onchange="toggleQrOptions()">
                  <option value="store">Store QR</option>
                  <option value="product">Product QR</option>
                  <option value="service">Service QR</option>
                  <option value="category">Category QR</option>
                  <?php if(mp_feature_enabled('table_management')) { ?><option value="table">Table QR</option><?php } ?>
                  <option value="attendance">Attendance QR</option>
                </select>
              </div>
              <div class="form-group" id="product-select" style="display:none;"><label>Select Product</label>
                <select class="form-control" id="related_product"><option value="">- Select -</option><?php foreach($products as $p): ?><option value="<?= $p->id; ?>"><?= htmlspecialchars($p->item_name); ?></option><?php endforeach; ?></select>
              </div>
              <div class="form-group" id="service-select" style="display:none;"><label>Select Service</label>
                <select class="form-control" id="related_service"><option value="">- Select -</option><?php foreach($services as $s): ?><option value="<?= $s->id; ?>"><?= htmlspecialchars($s->service_name); ?></option><?php endforeach; ?></select>
              </div>
              <div class="form-group" id="category-select" style="display:none;"><label>Select Category</label>
                <select class="form-control" id="related_category"><option value="">- Select -</option><?php foreach($categories as $c): ?><option value="<?= $c->id; ?>"><?= htmlspecialchars($c->category_name); ?></option><?php endforeach; ?></select>
              </div>
              <?php if(mp_feature_enabled('table_management')) { ?><div class="form-group" id="table-input" style="display:none;"><label>Table Number</label><input type="text" class="form-control" id="table_number" placeholder="e.g. Table 5"></div><?php } ?>
              <button class="btn btn-success" id="btn-generate"><i class="fa fa-qrcode"></i> Generate QR</button>
            </div>
          </div>
        </div>
        <div class="col-md-8">
          <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title">Generated QR Codes</h3></div>
            <div class="box-body table-responsive">
              <table class="table table-bordered table-striped">
                <thead><tr><th>Name</th><th>Type</th><th>QR</th><th>Action</th></tr></thead>
                <tbody>
                  <?php foreach($qr_codes as $qr): ?>
                  <tr>
                    <td><?= htmlspecialchars($qr->qr_name); ?></td>
                    <td>
                      <?php
                      $typeLabels = [
                        'store'     => ['label-primary', 'Store'],
                        'product'   => ['label-success', 'Product'],
                        'service'   => ['label-warning', 'Service'],
                        'category'  => ['label-info', 'Category'],
                        'table'     => ['label-danger', 'Table'],
                        'attendance'=> ['bg-gray', 'Attendance']
                      ];
                      $typeKey = trim($qr->qr_type ?: '');
                      $t = $typeLabels[$typeKey] ?? null;
                      if(!$t && $typeKey){
                        $t = ['label-default', ucfirst($typeKey)];
                      }
                      if($t):
                      ?>
                      <span class="label <?= $t[0] ?>" style="display:inline-block;padding:4px 10px;border-radius:4px;"><?= $t[1] ?></span>
                      <?php else: ?>
                      <span class="text-muted">—</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if($qr->qr_image && file_exists($qr->qr_image)): ?>
                        <a href="<?= base_url($qr->qr_image); ?>" target="_blank">
                          <img src="<?= base_url($qr->qr_image); ?>" style="width:60px;height:60px;" alt="QR">
                        </a>
                      <?php endif; ?>
                    </td>
                    <td>
                      <a href="<?= base_url($qr->qr_image); ?>" download class="btn btn-xs btn-primary"><i class="fa fa-download"></i> Download</a>
                      <button class="btn btn-xs btn-danger" onclick="deleteQr(<?= $qr->id; ?>)"><i class="fa fa-trash"></i></button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if(empty($qr_codes)): ?><tr><td colspan="4" class="text-center text-muted">No QR codes yet.</td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <?php $this->load->view('footer'); ?>
</div>

<?php $this->load->view('comman/code_js_sound.php');?>
<?php $this->load->view('comman/code_js.php');?>
<script>
function toggleQrOptions(){
  var type = $('#qr_type').val();
  $('#product-select,#service-select,#category-select<?php if(mp_feature_enabled('table_management')) { ?>,#table-input<?php } ?>').hide();
  if(type=='product') $('#product-select').show();
  if(type=='service') $('#service-select').show();
  if(type=='category') $('#category-select').show();
  <?php if(mp_feature_enabled('table_management')) { ?>if(type=='table') $('#table-input').show();<?php } ?>
}

$('#btn-generate').click(function(){
  var type = $('#qr_type').val();
  var relatedId = 0;
  if(type=='product') relatedId = $('#related_product').val();
  if(type=='service') relatedId = $('#related_service').val();
  if(type=='category') relatedId = $('#related_category').val();
  var btn = $(this);
  btn.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Generating...');
  $.post('<?=base_url("online_store/generate_qr");?>', {
    qr_type: type,
    related_id: relatedId,
    table_number: <?php echo mp_feature_enabled('table_management') ? "$('#table_number').val()" : "''"; ?>,
    qr_name: $('#qr_name').val(),
    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
  }, function(res){
    btn.prop('disabled',false).html('<i class="fa fa-qrcode"></i> Generate QR');
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); }
    else { toastr.error(res.message); }
  }, 'json');
});

function deleteQr(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Delete this QR code?')) return;
    doDeleteQr(id);
    return;
  }
  swal({
    title: "Delete QR Code?",
    text: "This action cannot be undone.",
    icon: "warning",
    buttons: true,
    dangerMode: true
  }).then(function(willDelete){
    if(willDelete){
      doDeleteQr(id);
    }
  });
}
function doDeleteQr(id){
  $.post('<?=base_url("online_store/delete_qr/");?>' + id, {
    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
  }, function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(function(){location.reload();}, 800); }
    else { toastr.error(res.message); }
  }, 'json');
}
</script>
<script>$(".online-store-qr-active-li").addClass("active");</script>
</body>
</html>
