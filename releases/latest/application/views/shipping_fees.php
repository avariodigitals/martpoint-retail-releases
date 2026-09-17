<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.ship-loc{color:var(--mp-muted)!important;font-size:12px!important}
.ship-fee{font-weight:700!important;color:var(--mp-text)!important}
.ship-hint{background:var(--mp-bg)!important;border:1px dashed var(--mp-border)!important;border-radius:14px!important;padding:14px 18px!important;margin-bottom:18px!important;font-size:13px!important;color:var(--mp-muted)!important;line-height:1.5!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Delivery locations &amp; fees selectable at POS checkout</div>
  </div>
  <button class="mp-qa-btn green" onclick="openModal()"><i class="fa fa-plus"></i> Add Shipping Fee</button>
</div>

<div class="ship-hint">
  <i class="fa fa-truck" style="color:var(--mp-primary);"></i>
  Fees added here appear in the POS payment screen — the cashier picks a location and its fee is added to the sale total and printed on the invoice.
  Disable the <strong>Manual Shipping</strong> feature flag in Business Profile to hide it from POS.
</div>

<div class="mp-table-wrap">
  <div class="mp-card-head"><h3>Shipping Fees</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table class="table mp-dt-table" width="100%">
        <thead><tr><th>Name</th><th>Location</th><th>Fee</th><th>Order</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($fees as $f): ?>
          <tr data-id="<?= (int)$f->id; ?>" data-label="<?= htmlspecialchars($f->label); ?>" data-location="<?= htmlspecialchars($f->location ?? ''); ?>" data-fee="<?= htmlspecialchars($f->fee); ?>" data-enabled="<?= (int)$f->is_enabled; ?>" data-order="<?= (int)$f->sort_order; ?>">
            <td class="row-name"><?= htmlspecialchars($f->label); ?></td>
            <td><span class="ship-loc"><?= htmlspecialchars($f->location ?: '—'); ?></span></td>
            <td><span class="ship-fee"><?= $CI->currency(store_number_format($f->fee)); ?></span></td>
            <td><?= (int)$f->sort_order; ?></td>
            <td><span class="label label-<?= $f->is_enabled ? 'success' : 'default'; ?>"><?= $f->is_enabled ? 'Active' : 'Inactive'; ?></span></td>
            <td>
              <div class="mp-actions">
                <button class="mp-edit" title="Edit" onclick="editFee(this)"><i class="fa fa-pencil"></i></button>
                <button class="mp-delete" title="Delete" onclick="deleteFee(<?= (int)$f->id; ?>)"><i class="fa fa-trash"></i></button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($fees)): ?><tr><td colspan="6" class="mp-empty-state">No shipping fees yet — add your first delivery location.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="feeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="feeForm" method="post" onsubmit="return false;">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Shipping Fee</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="fee_id" id="fee_id" value="">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
          <div class="form-group">
            <label>Name *</label>
            <input type="text" class="form-control" name="label" id="f_label" placeholder="e.g. Lekki Delivery" required>
          </div>
          <div class="form-group">
            <label>Location / Area</label>
            <input type="text" class="form-control" name="location" id="f_location" placeholder="e.g. Lekki Phase 1, Victoria Island">
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Fee *</label>
                <input type="number" step="0.01" min="0" class="form-control" name="fee" id="f_fee" placeholder="2000" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Sort Order</label>
                <input type="number" class="form-control" name="sort_order" id="f_order" value="0">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label><input type="checkbox" name="is_enabled" id="f_enabled" checked> Active (show in POS)</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="saveFee()">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };
function openModal(){
  $('#feeForm')[0].reset();
  $('#fee_id').val('');
  $('#f_enabled').prop('checked', true);
  $('#feeModal').modal('show');
}
function editFee(btn){
  var tr = $(btn).closest('tr');
  $('#fee_id').val(tr.data('id'));
  $('#f_label').val(tr.data('label'));
  $('#f_location').val(tr.data('location'));
  $('#f_fee').val(tr.data('fee'));
  $('#f_order').val(tr.data('order'));
  $('#f_enabled').prop('checked', tr.data('enabled') == 1);
  $('#feeModal').modal('show');
}
function saveFee(){
  var fd = new FormData(document.getElementById('feeForm'));
  $.ajax({ url:'<?= base_url('shipping_fees/save'); ?>', type:'POST', data:fd, processData:false, contentType:false, dataType:'json',
    success:function(res){
      if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 700); }
      else { toastr.error(res.message || 'Failed to save'); }
    }, error:function(){ toastr.error('Network error'); } });
}
function deleteFee(id){
  if(!confirm('Delete this shipping fee?')) return;
  $.post('<?= base_url('shipping_fees/delete/'); ?>'+id, { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' }, function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 700); } else { toastr.error('Failed to delete'); }
  }, 'json');
}
</script>
