<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Frequently asked questions shown as an accordion on your storefront</div>
  </div>
  <button class="mp-qa-btn green" onclick="openModal()"><i class="fa fa-plus"></i> Add FAQ</button>
</div>

<div class="mp-table-wrap">
  <div class="mp-card-head"><h3>FAQs</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="os-faqs-table" class="table mp-dt-table" width="100%">
        <thead><tr><th>Question</th><th>Answer</th><th>Status</th><th>Order</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($faqs as $f): ?>
          <tr data-id="<?= (int)$f->id; ?>" data-question="<?= htmlspecialchars($f->question); ?>" data-answer="<?= htmlspecialchars($f->answer); ?>" data-enabled="<?= (int)$f->is_enabled; ?>" data-order="<?= (int)$f->sort_order; ?>">
            <td class="row-name"><?= htmlspecialchars($f->question); ?></td>
            <td><?= htmlspecialchars(mb_substr($f->answer, 0, 80)) . (strlen($f->answer) > 80 ? '…' : ''); ?></td>
            <td><span class="label label-<?= $f->is_enabled ? 'success' : 'default'; ?>"><?= $f->is_enabled ? 'Active' : 'Inactive'; ?></span></td>
            <td><?= (int)$f->sort_order; ?></td>
            <td>
              <div class="mp-actions">
                <button class="mp-edit" title="Edit" onclick="editFaq(this)"><i class="fa fa-pencil"></i></button>
                <button class="mp-delete" title="Delete" onclick="deleteFaq(<?= (int)$f->id; ?>)"><i class="fa fa-trash"></i></button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($faqs)): ?><tr><td colspan="5" class="mp-empty-state">No FAQs yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="faqModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="faqForm" method="post" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="faq_id" id="faq_id" value="">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="modalTitle">Add FAQ</h4>
        </div>
        <div class="modal-body">
          <div class="mp-form-group"><label>Question</label><input type="text" class="mp-form-control" name="question" id="question" required></div>
          <div class="mp-form-group"><label>Answer</label><textarea class="mp-form-control" name="answer" id="answer" rows="4" required></textarea></div>
          <div class="mp-form-group"><label>Sort Order</label><input type="number" class="mp-form-control" name="sort_order" id="sort_order" value="0"></div>
          <div class="mp-form-group"><label style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:var(--mp-ink);margin:0;"><input type="checkbox" name="is_enabled" id="is_enabled" value="1" checked> Active</label></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="saveFaq()">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };
function openModal(){ $('#faq_id').val(''); $('#question').val(''); $('#answer').val(''); $('#sort_order').val(0); $('#is_enabled').prop('checked',true); $('#modalTitle').text('Add FAQ'); $('#faqModal').modal('show'); }
function editFaq(btn){
  const tr = $(btn).closest('tr');
  $('#faq_id').val(tr.data('id'));
  $('#question').val(tr.data('question'));
  $('#answer').val(tr.data('answer'));
  $('#sort_order').val(tr.data('order'));
  $('#is_enabled').prop('checked', tr.data('enabled') == 1);
  $('#modalTitle').text('Edit FAQ');
  $('#faqModal').modal('show');
}
function saveFaq(){
  const fd = new FormData(document.getElementById('faqForm'));
  $.ajax({ url:'<?= base_url('online_store/save_faq'); ?>', type:'POST', data:fd, processData:false, contentType:false, dataType:'json',
    success:function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Failed to save'); } },
    error:function(){ toastr.error('Request failed. Please check your connection.'); }
  });
}
function deleteFaq(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Delete this FAQ?')) return;
    doDeleteFaq(id);
    return;
  }
  swal({ title: "Delete FAQ?", text: "This action cannot be undone.", icon: "warning", buttons: true, dangerMode: true })
  .then(function(willDelete){ if(willDelete) doDeleteFaq(id); });
}
function doDeleteFaq(id){
  $.post('<?= base_url('online_store/delete_faq/'); ?>'+id, { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' }, function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 800); } else { toastr.error(res.message || 'Failed to delete'); } }, 'json');
}
$(document).ready(function(){
  $('#os-faqs-table').DataTable({
    "aLengthMenu": [[10,25,50,100],[10,25,50,100]],
    dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    "order": [[3,"asc"]],
    "responsive": false,
    "columnDefs": [{ "targets": [4], "orderable": false }]
  });
});
</script>
<script>$(".online_store-faqs-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
