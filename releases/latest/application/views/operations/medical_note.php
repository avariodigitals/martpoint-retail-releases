<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?>
<style>
.mn-section { background:#F8FAFC; border-radius:10px; padding:16px; margin-bottom:16px; border:1px solid #E2E8F0; }
.mn-section-title { font-size:14px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:12px; display:flex; align-items:center; gap:8px; }
.mn-allergy-alert { background:#FEF2F2; border:1px solid #FECACA; border-radius:8px; padding:12px 14px; margin-bottom:12px; display:flex; align-items:flex-start; gap:10px; }
.mn-allergy-alert i { color:#DC2626; font-size:18px; margin-top:2px; }
.mn-allergy-alert .text { font-size:13px; color:#991B1B; }
.mn-item-row td { vertical-align:middle; }
.mn-item-row .form-control { font-size:13px; }
.mn-prescription-preview { margin-top:10px; border:1px solid #E2E8F0; border-radius:8px; overflow:hidden; }
.mn-prescription-preview img { max-width:100%; max-height:300px; display:block; margin:0 auto; }
.mn-prescription-preview .pdf-link { padding:12px; text-align:center; font-size:14px; }
.mn-upload-zone { border:2px dashed #CBD5E1; border-radius:10px; padding:20px; text-align:center; cursor:pointer; transition:border-color 0.2s; }
.mn-upload-zone:hover { border-color:#3B82F6; }
.mn-upload-zone i { font-size:32px; color:#94A3B8; margin-bottom:8px; }
.mn-upload-zone .hint { font-size:12px; color:#64748B; margin-top:4px; }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small>Pharmacy</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li><a href="<?= base_url('operations/medical_notes'); ?>">Medical Notes</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-10 col-md-offset-1">
        <div class="box box-info" style="border-radius:12px;">
          <div class="box-header with-border"><h3 class="box-title"><?= $edit_note ? 'Edit' : 'New'; ?> Medical Note</h3></div>
          <form id="note-form" class="form-horizontal" enctype="multipart/form-data">
            <input type="hidden" id="id" name="id" value="<?= $edit_note ? $edit_note->id : ''; ?>">
            <input type="hidden" id="existing_prescription" name="existing_prescription" value="<?= $edit_note && !empty($edit_note->prescription_file) ? $edit_note->prescription_file : ''; ?>">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
            <div class="box-body">

              <div class="mn-section">
                <div class="mn-section-title"><i class="fa fa-user"></i> <?= mp_label('customer'); ?> Information</div>
                <div class="form-group">
                  <label class="col-sm-3 control-label"><?= mp_label('customer'); ?> <span class="text-red">*</span></label>
                  <div class="col-sm-9">
                    <?php $preselect_customer = ($edit_note ? $edit_note->customer_id : ($this->input->get('customer_id') ?: '')); ?>
                    <select class="form-control select2" id="customer_id" name="customer_id" style="width:100%;" required>
                      <option value="">-- Select <?= mp_label('customer'); ?> --</option>
                      <?php foreach($customers as $c){ ?>
                      <option value="<?= $c->id; ?>" <?= ($preselect_customer==$c->id)?'selected':''; ?>><?= htmlspecialchars($c->customer_name); ?> (<?= $c->mobile; ?>)</option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Date <span class="text-red">*</span></label>
                  <div class="col-sm-9">
                    <input type="date" class="form-control" id="note_date" name="note_date" value="<?= $edit_note ? $edit_note->note_date : date('Y-m-d'); ?>" required>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Pharmacist / Staff</label>
                  <div class="col-sm-9">
                    <select class="form-control select2" id="staff_id" name="staff_id" style="width:100%;">
                      <option value="">-- Select Staff --</option>
                      <?php foreach($staff as $s){ ?>
                      <option value="<?= $s->id; ?>" <?= ($edit_note && $edit_note->staff_id==$s->id)?'selected':''; ?>><?= htmlspecialchars($s->first_name . ' ' . $s->last_name); ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
              </div>

              <div class="mn-section">
                <div class="mn-section-title"><i class="fa fa-stethoscope"></i> Prescription Details</div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Prescribing Doctor</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" id="prescribing_doctor" name="prescribing_doctor" placeholder="Dr. Name" value="<?= $edit_note ? htmlspecialchars($edit_note->prescribing_doctor) : ''; ?>">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Doctor Contact</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" id="doctor_contact" name="doctor_contact" placeholder="Phone or email" value="<?= $edit_note ? htmlspecialchars($edit_note->doctor_contact) : ''; ?>">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Prescription Ref / No.</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" id="prescription_ref" name="prescription_ref" placeholder="e.g. RX-2026-001" value="<?= $edit_note ? htmlspecialchars($edit_note->prescription_ref) : ''; ?>">
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Diagnosis / Condition</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" id="diagnosis" name="diagnosis" placeholder="e.g. Hypertension, Malaria, Diabetes" value="<?= $edit_note ? htmlspecialchars($edit_note->diagnosis) : ''; ?>">
                  </div>
                </div>
              </div>

              <div class="mn-section">
                <div class="mn-section-title"><i class="fa fa-camera"></i> Doctor's Prescription Upload</div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Prescription Image / PDF</label>
                  <div class="col-sm-9">
                    <?php if($edit_note && !empty($edit_note->prescription_file)): ?>
                      <div class="mn-prescription-preview">
                        <?php $ext = strtolower(pathinfo($edit_note->prescription_file, PATHINFO_EXTENSION)); ?>
                        <?php if(in_array($ext, ['jpg','jpeg','png','gif','webp'])): ?>
                          <img src="<?= base_url($edit_note->prescription_file); ?>" alt="Prescription">
                        <?php else: ?>
                          <div class="pdf-link"><i class="fa fa-file-pdf-o fa-3x"></i><br><a href="<?= base_url($edit_note->prescription_file); ?>" target="_blank">View Prescription (<?= strtoupper($ext); ?>)</a></div>
                        <?php endif; ?>
                      </div>
                      <small class="text-muted" style="display:block;margin-top:6px;">Upload a new file below to replace, or leave empty to keep the existing prescription.</small>
                    <?php endif; ?>
                    <div class="mn-upload-zone" onclick="$('#prescription_file').click()">
                      <i class="fa fa-cloud-upload"></i>
                      <div><strong>Click to upload</strong> or take a photo</div>
                      <div class="hint">JPG, PNG, GIF, WebP, or PDF — max 5MB</div>
                      <input type="file" id="prescription_file" name="prescription_file" accept="image/*,.pdf" style="display:none;" onchange="$('#prescription-name').text(this.files[0] ? this.files[0].name : '');">
                    </div>
                    <small id="prescription-name" class="text-info" style="display:block;margin-top:4px;"></small>
                  </div>
                </div>
              </div>

              <div class="mn-section">
                <div class="mn-section-title"><i class="fa fa-exclamation-triangle text-red"></i> Allergy & Safety</div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Known Allergies</label>
                  <div class="col-sm-9">
                    <input type="text" class="form-control" id="allergies_flagged" name="allergies_flagged" placeholder="e.g. Penicillin, Sulfa drugs, None" value="<?= $edit_note ? htmlspecialchars($edit_note->allergies_flagged) : ''; ?>">
                    <small class="text-muted" style="margin-top:4px;display:block;">Flag drug allergies here. A warning will appear on the <?= strtolower(mp_label('customer')); ?>'s profile and in the list.</small>
                  </div>
                </div>
              </div>

              <div class="mn-section">
                <div class="mn-section-title"><i class="fa fa-pills"></i> Medicines Prescribed & Dispensed</div>
                <small class="text-muted" style="display:block;margin-bottom:10px;">Select each medicine from your inventory. Enter the quantity dispensed, dosage strength, how long to take it, and usage instructions for the patient.</small>
                <table class="table table-bordered table-condensed" id="items-table">
                  <thead><tr class="bg-gray"><th style="width:30%">Medicine (from inventory)</th><th style="width:8%">Qty Dispensed</th><th style="width:12%">Dosage Strength</th><th style="width:12%">Duration</th><th style="width:23%">Usage Instructions</th><th style="width:5%"></th></tr></thead>
                  <tbody>
                    <?php if($edit_note && !empty($edit_note->items)){ foreach($edit_note->items as $idx => $it){ ?>
                    <tr id="item-row-<?= $idx; ?>" class="mn-item-row">
                      <td>
                        <select class="form-control item-select" name="item_id[]" style="width:100%;" required>
                          <option value="<?= $it->item_id; ?>" selected><?= htmlspecialchars($it->item_name); ?></option>
                        </select>
                      </td>
                      <td><input type="number" step="0.001" min="0" class="form-control" name="item_qty[]" value="<?= $it->qty; ?>"></td>
                      <td><input type="text" class="form-control" name="item_dosage[]" placeholder="e.g. 500mg" value="<?= htmlspecialchars($it->dosage ?? ''); ?>"></td>
                      <td><input type="text" class="form-control" name="item_duration[]" placeholder="e.g. 7 days" value="<?= htmlspecialchars($it->duration ?? ''); ?>"></td>
                      <td><input type="text" class="form-control" name="item_instructions[]" placeholder="e.g. 1 tab morning, 1 at night" value="<?= htmlspecialchars($it->instructions ?? ''); ?>"></td>
                      <td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="removeItemRow('item-row-<?= $idx; ?>')"><i class="fa fa-trash"></i></button></td>
                    </tr>
                    <?php }} ?>
                  </tbody>
                </table>
                <button type="button" class="btn btn-xs btn-success" id="btn-add-item"><i class="fa fa-plus"></i> Add Medicine</button>
              </div>

              <div class="mn-section">
                <div class="mn-section-title"><i class="fa fa-comment-medical"></i> Notes & Counselling</div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Dosage Instructions</label>
                  <div class="col-sm-9">
                    <textarea class="form-control" id="dosage_instructions" name="dosage_instructions" rows="2" placeholder="General dosage instructions for the <?= strtolower(mp_label('customer')); ?>..."><?= $edit_note ? htmlspecialchars($edit_note->dosage_instructions) : ''; ?></textarea>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Counselling Notes</label>
                  <div class="col-sm-9">
                    <textarea class="form-control" id="counselling_notes" name="counselling_notes" rows="2" placeholder="Advice given to <?= strtolower(mp_label('customer')); ?> (side effects, food interactions, storage...)"><?= $edit_note ? htmlspecialchars($edit_note->counselling_notes) : ''; ?></textarea>
                  </div>
                </div>
              </div>

              <div class="mn-section">
                <div class="mn-section-title"><i class="fa fa-refresh"></i> Refill Tracking</div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">Refills Remaining</label>
                  <div class="col-sm-3">
                    <input type="number" class="form-control" id="refills_remaining" name="refills_remaining" min="0" value="<?= $edit_note ? $edit_note->refills_remaining : 0; ?>">
                  </div>
                  <label class="col-sm-3 control-label">Next Refill Date</label>
                  <div class="col-sm-3">
                    <input type="date" class="form-control" id="next_refill_date" name="next_refill_date" value="<?= $edit_note ? $edit_note->next_refill_date : ''; ?>">
                  </div>
                </div>
              </div>

            </div>
            <div class="box-footer">
              <a href="<?= base_url('operations/medical_notes'); ?>" class="btn btn-warning">Back</a>
              <button type="button" class="btn btn-primary pull-right" id="btn-save"><i class="fa fa-save"></i> Save Medical Note</button>
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
var itemRowIdx = <?= ($edit_note && !empty($edit_note->items)) ? count($edit_note->items) : 0; ?>;
function initItemSelect($el){
  $el.select2({
    placeholder: 'Search medicine...',
    minimumInputLength: 1,
    ajax: {
      url: '<?= base_url('operations/ajax_medical_items'); ?>',
      dataType: 'json',
      delay: 250,
      data: function(params){ return { term: params.term }; },
      processResults: function(data){
        return { results: $.map(data, function(item){
          return { id: item.id, text: item.text + ' (Stock: ' + (item.stock||0) + ')' };
        })};
      }
    }
  });
}
function addItemRow(){
  var rowId = 'item-row-' + itemRowIdx++;
  var html = '<tr id="'+rowId+'" class="mn-item-row">' +
    '<td><select class="form-control item-select" name="item_id[]" style="width:100%;"><option value=""></option></select></td>' +
    '<td><input type="number" step="0.001" min="0" class="form-control" name="item_qty[]" value="1"></td>' +
    '<td><input type="text" class="form-control" name="item_dosage[]" placeholder="e.g. 500mg"></td>' +
    '<td><input type="text" class="form-control" name="item_duration[]" placeholder="e.g. 7 days"></td>' +
    '<td><input type="text" class="form-control" name="item_instructions[]" placeholder="e.g. 1 tab morning, 1 at night"></td>' +
    '<td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="removeItemRow(\''+rowId+'\')"><i class="fa fa-trash"></i></button></td>' +
    '</tr>';
  $('#items-table tbody').append(html);
  initItemSelect($('#'+rowId).find('.item-select'));
}
function removeItemRow(rowId){
  $('#'+rowId).remove();
}
$(function(){
  $('.select2').select2();
  initItemSelect($('.item-select'));
  $('#btn-add-item').on('click', addItemRow);
  $('#btn-save').on('click', function(){
    var customer_id = $('#customer_id').val();
    var note_date = $('#note_date').val();
    if(!customer_id){ toastr.error('Select a <?= strtolower(mp_label('customer')); ?>.'); return; }
    if(!note_date){ toastr.error('Select a date.'); return; }
    var formData = new FormData($('#note-form')[0]);
    $.ajax({
      url: '<?= base_url('operations/medical_note_save'); ?>',
      type: 'POST',
      dataType: 'json',
      data: formData,
      processData: false,
      contentType: false,
      success: function(res){
        if(res.success){ toastr.success(res.message); setTimeout(function(){ window.location = '<?= base_url('operations/medical_notes'); ?>'; }, 800); }
        else { toastr.error(res.message || 'Failed to save.'); }
      },
      error: function(){ toastr.error('Server error.'); }
    });
  });
});
</script>
</body>
</html>
