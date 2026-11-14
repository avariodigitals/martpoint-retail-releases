<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.os-qr-grid{display:grid!important;grid-template-columns:minmax(0,360px) minmax(0,1fr)!important;gap:20px!important;align-items:start!important}
@media(max-width:1024px){.os-qr-grid{grid-template-columns:1fr!important}}
.os-qr-img{width:60px!important;height:60px!important;border-radius:8px!important;border:1px solid var(--mp-border)!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Generate and manage QR codes for your store, products, services and tables</div>
  </div>
</div>

<div class="os-qr-grid">
  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Generate QR Code</h3></div>
    <div class="mp-card-body">
      <div class="mp-form-group"><label for="qr_name">QR Name</label><input type="text" class="mp-form-control" id="qr_name" placeholder="e.g. Store QR"></div>
      <div class="mp-form-group"><label for="qr_type">QR Type</label>
        <select class="mp-form-control" id="qr_type" onchange="toggleQrOptions()">
          <option value="store">Store QR</option>
          <option value="product">Product QR</option>
          <option value="service">Service QR</option>
          <option value="category">Category QR</option>
          <?php if(mp_feature_enabled('table_management')) { ?><option value="table">Table QR</option><?php } ?>
          <option value="attendance">Attendance QR</option>
        </select>
      </div>
      <div class="mp-form-group" id="product-select" style="display:none;"><label>Select Product</label>
        <select class="form-control select2" id="related_product" style="width:100%;"><option value="">- Select -</option><?php foreach($products as $p): ?><option value="<?= (int)$p->id; ?>"><?= htmlspecialchars($p->item_name); ?></option><?php endforeach; ?></select>
      </div>
      <div class="mp-form-group" id="service-select" style="display:none;"><label>Select Service</label>
        <select class="form-control select2" id="related_service" style="width:100%;"><option value="">- Select -</option><?php foreach($services as $s): ?><option value="<?= (int)$s->id; ?>"><?= htmlspecialchars($s->service_name); ?></option><?php endforeach; ?></select>
      </div>
      <div class="mp-form-group" id="category-select" style="display:none;"><label>Select Category</label>
        <select class="form-control select2" id="related_category" style="width:100%;"><option value="">- Select -</option><?php foreach($categories as $c): ?><option value="<?= (int)$c->id; ?>"><?= htmlspecialchars($c->category_name); ?></option><?php endforeach; ?></select>
      </div>
      <?php if(mp_feature_enabled('table_management')) { ?><div class="mp-form-group" id="table-input" style="display:none;"><label>Table Number</label><input type="text" class="mp-form-control" id="table_number" placeholder="e.g. Table 5"></div><?php } ?>
      <div class="mp-form-actions">
        <button class="mp-btn-primary" id="btn-generate"><i class="fa fa-qrcode"></i> Generate QR</button>
      </div>
    </div>
  </div>

  <div class="mp-table-wrap">
    <div class="mp-card-head"><h3>Generated QR Codes</h3></div>
    <div class="box-body">
      <div class="mp-dt-scroll">
        <table id="os-qr-table" class="table mp-dt-table" width="100%">
          <thead><tr><th>Name</th><th>Type</th><th>QR</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach($qr_codes as $qr): ?>
            <tr>
              <td class="row-name"><?= htmlspecialchars($qr->qr_name); ?></td>
              <td>
                <?php
                  $typeLabels = [
                    'store'     => ['label-primary', 'Store'],
                    'product'   => ['label-success', 'Product'],
                    'service'   => ['label-warning', 'Service'],
                    'category'  => ['label-info', 'Category'],
                    'table'     => ['label-danger', 'Table'],
                    'attendance'=> ['label-default', 'Attendance']
                  ];
                  $typeKey = trim($qr->qr_type ?: '');
                  $t = $typeLabels[$typeKey] ?? null;
                  if(!$t && $typeKey){ $t = ['label-default', ucfirst($typeKey)]; }
                  if($t):
                ?>
                <span class="label <?= $t[0]; ?>"><?= $t[1]; ?></span>
                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
              </td>
              <td>
                <?php if($qr->qr_image && file_exists($qr->qr_image)): ?>
                <a href="<?= base_url($qr->qr_image); ?>" target="_blank"><img src="<?= base_url($qr->qr_image); ?>" class="os-qr-img" alt="QR"></a>
                <?php endif; ?>
              </td>
              <td>
                <div class="mp-actions">
                  <a href="<?= base_url($qr->qr_image); ?>" download class="mp-edit" title="Download"><i class="fa fa-download"></i></a>
                  <button class="mp-delete" title="Delete" onclick="deleteQr(<?= (int)$qr->id; ?>)"><i class="fa fa-trash"></i></button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($qr_codes)): ?><tr><td colspan="4" class="mp-empty-state">No QR codes yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

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
    '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
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
  swal({ title: "Delete QR Code?", text: "This action cannot be undone.", icon: "warning", buttons: true, dangerMode: true })
  .then(function(willDelete){ if(willDelete) doDeleteQr(id); });
}
function doDeleteQr(id){
  $.post('<?=base_url("online_store/delete_qr/");?>' + id, {
    '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
  }, function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(function(){location.reload();}, 800); }
    else { toastr.error(res.message); }
  }, 'json');
}

$(document).ready(function(){
  $('#related_product,#related_service,#related_category').select2();
  $('#os-qr-table').DataTable({
    "aLengthMenu": [[10,25,50,100],[10,25,50,100]],
    dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    "order": [[0,"asc"]],
    "responsive": false,
    "columnDefs": [{ "targets": [2,3], "orderable": false }]
  });
});
</script>
<script>$(".online_store-qr-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
