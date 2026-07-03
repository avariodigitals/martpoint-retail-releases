<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small>Beauty, Spa &amp; Salon</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li><a href="<?= base_url('operations/treatment_notes'); ?>">Treatment Notes</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="box box-info">
          <div class="box-header with-border"><h3 class="box-title"><?= $edit_note ? 'Edit' : 'New'; ?> Treatment Note</h3></div>
          <form id="note-form" class="form-horizontal">
            <input type="hidden" id="id" name="id" value="<?= $edit_note ? $edit_note->id : ''; ?>">
            <div class="box-body">
              <div class="form-group">
                <label class="col-sm-3 control-label">Customer <span class="text-red">*</span></label>
                <div class="col-sm-9">
                  <?php $preselect_customer = ($edit_note ? $edit_note->customer_id : ($this->input->get('customer_id') ?: '')); ?>
                  <select class="form-control select2" id="customer_id" name="customer_id" style="width:100%;">
                    <option value="">-- Select Customer --</option>
                    <?php foreach($customers as $c){ ?>
                    <option value="<?= $c->id; ?>" <?= ($preselect_customer==$c->id)?'selected':''; ?>><?= htmlspecialchars($c->customer_name); ?> (<?= $c->mobile; ?>)</option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">Service Type <span class="text-red">*</span></label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="service_type" name="service_type" placeholder="e.g. Facial, Massage, Hair Treatment" value="<?= $edit_note ? htmlspecialchars($edit_note->service_type) : ''; ?>" required>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">Treatment Date <span class="text-red">*</span></label>
                <div class="col-sm-9">
                  <input type="date" class="form-control" id="treatment_date" name="treatment_date" value="<?= $edit_note ? $edit_note->treatment_date : date('Y-m-d'); ?>" required>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">Staff</label>
                <div class="col-sm-9">
                  <select class="form-control select2" id="staff_id" name="staff_id" style="width:100%;">
                    <option value="">-- Select Staff --</option>
                    <?php foreach($staff as $s){ ?>
                    <option value="<?= $s->id; ?>" <?= ($edit_note && $edit_note->staff_id==$s->id)?'selected':''; ?>><?= htmlspecialchars($s->first_name . ' ' . $s->last_name); ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">Treatment Notes</label>
                <div class="col-sm-9">
                  <textarea class="form-control" id="notes" name="notes" rows="5" placeholder="Describe the treatment performed, skin/hair condition, techniques used..."><?= $edit_note ? htmlspecialchars($edit_note->notes) : ''; ?></textarea>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">Consumables Used</label>
                <div class="col-sm-9">
                  <table class="table table-bordered table-condensed" id="consumables-table">
                    <thead><tr class="bg-gray"><th style="width:50%">Item</th><th style="width:20%">Qty</th><th style="width:20%">Unit</th><th style="width:10%"></th></tr></thead>
                    <tbody>
                      <?php if($edit_note && !empty($edit_note->consumables)){ foreach($edit_note->consumables as $ci => $cons){ ?>
                      <tr id="cons-row-<?=$ci;?>">
                        <td>
                          <select class="form-control cons-item-select" name="consumable_item_id[]" style="width:100%;">
                            <option value="<?= $cons->item_id; ?>" selected><?= htmlspecialchars($cons->item_name); ?></option>
                          </select>
                        </td>
                        <td><input type="number" step="0.001" min="0" class="form-control" name="consumable_qty[]" value="<?= $cons->qty; ?>"></td>
                        <td class="cons-unit text-muted" style="padding-top:8px;"><?= htmlspecialchars($cons->consumable_unit ?: '-'); ?></td>
                        <td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="removeConsRow('cons-row-<?=$ci;?>')"><i class="fa fa-trash"></i></button></td>
                      </tr>
                      <?php }} ?>
                    </tbody>
                  </table>
                  <button type="button" class="btn btn-xs btn-success" id="btn-add-consumable"><i class="fa fa-plus"></i> Add Consumable</button>
                  <p class="text-muted" style="font-size:11px;margin-top:6px;">Select items marked as "Not for Sale" from inventory. Decimal quantities supported (e.g. 0.3 for 30ml).</p>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">Products Notes</label>
                <div class="col-sm-9">
                  <textarea class="form-control" id="products_used" name="products_used" rows="2" placeholder="Any extra notes about products used..."><?= $edit_note ? htmlspecialchars($edit_note->products_used) : ''; ?></textarea>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">Recommendations</label>
                <div class="col-sm-9">
                  <textarea class="form-control" id="recommendations" name="recommendations" rows="2" placeholder="Aftercare advice, next appointment suggestion, product recommendations..."><?= $edit_note ? htmlspecialchars($edit_note->recommendations) : ''; ?></textarea>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <a href="<?= base_url('operations/treatment_notes'); ?>" class="btn btn-warning">Back</a>
              <button type="button" class="btn btn-primary pull-right" id="btn-save">Save Note</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
</div>
<?php $this->load->view('footer'); ?>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>
var consRowIdx = <?= ($edit_note && !empty($edit_note->consumables)) ? count($edit_note->consumables) : 0; ?>;
function initConsSelect($el){
  $el.select2({
    placeholder: 'Search consumable item...',
    minimumInputLength: 1,
    ajax: {
      url: '<?= base_url('operations/ajax_consumable_items'); ?>',
      dataType: 'json',
      delay: 250,
      data: function(params){ return { term: params.term }; },
      processResults: function(data){
        return { results: $.map(data, function(item){
          return { id: item.id, text: item.item_name + ' (Stock: ' + (item.stock||0) + ')', stock: item.stock, unit: item.consumable_unit };
        })};
      }
    }
  }).on('select2:select', function(e){
    var unit = e.params.data.unit || '-';
    $(this).closest('tr').find('.cons-unit').text(unit);
  });
}
function addConsRow(){
  var rowId = 'cons-row-' + consRowIdx++;
  var html = '<tr id="'+rowId+'">' +
    '<td><select class="form-control cons-item-select" name="consumable_item_id[]" style="width:100%;"><option value=""></option></select></td>' +
    '<td><input type="number" step="0.001" min="0" class="form-control" name="consumable_qty[]" value="1" placeholder="Qty"></td>' +
    '<td class="cons-unit text-muted" style="padding-top:8px;">-</td>' +
    '<td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="removeConsRow(\''+rowId+'\')"><i class="fa fa-trash"></i></button></td>' +
    '</tr>';
  $('#consumables-table tbody').append(html);
  initConsSelect($('#'+rowId).find('.cons-item-select'));
}
function removeConsRow(rowId){
  $('#'+rowId).remove();
}
$(function(){
  $('.select2').select2();
  initConsSelect($('.cons-item-select'));
  $('#btn-add-consumable').on('click', addConsRow);
  $('#btn-save').on('click', function(){
    var customer_id = $('#customer_id').val();
    var service_type = $('#service_type').val().trim();
    var treatment_date = $('#treatment_date').val();
    if(!customer_id){ toastr.error('Select a customer.'); return; }
    if(!service_type){ toastr.error('Enter service type.'); return; }
    if(!treatment_date){ toastr.error('Select treatment date.'); return; }
    $.ajax({
      url: '<?= base_url('operations/treatment_note_save'); ?>',
      type: 'POST',
      dataType: 'json',
      data: $('#note-form').serialize(),
      success: function(res){
        if(res.success){ toastr.success(res.message); setTimeout(function(){ window.location = '<?= base_url('operations/treatment_notes'); ?>'; }, 800); }
        else { toastr.error(res.message || 'Failed to save.'); }
      },
      error: function(){ toastr.error('Server error.'); }
    });
  });
});
</script>
</body>
</html>
