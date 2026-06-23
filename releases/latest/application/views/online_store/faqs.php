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
        <div class="col-md-8">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">FAQs</h3>
              <div class="box-tools">
                <button class="btn btn-sm btn-success" onclick="openModal()"><i class="fa fa-plus"></i> Add FAQ</button>
              </div>
            </div>
            <div class="box-body table-responsive">
              <table class="table table-bordered table-striped">
                <thead><tr><th>Question</th><th>Answer</th><th>Status</th><th>Order</th><th>Actions</th></tr></thead>
                <tbody>
                  <?php foreach($faqs as $f): ?>
                  <tr data-id="<?= $f->id; ?>" data-question="<?= htmlspecialchars($f->question); ?>" data-answer="<?= htmlspecialchars($f->answer); ?>" data-enabled="<?= $f->is_enabled; ?>" data-order="<?= $f->sort_order; ?>">
                    <td><?= htmlspecialchars($f->question); ?></td>
                    <td><?= htmlspecialchars(substr($f->answer, 0, 80)) . (strlen($f->answer) > 80 ? '...' : ''); ?></td>
                    <td><span class="label label-<?= $f->is_enabled ? 'success' : 'default'; ?>"><?= $f->is_enabled ? 'Active' : 'Inactive'; ?></span></td>
                    <td><?= (int)$f->sort_order; ?></td>
                    <td>
                      <button class="btn btn-xs btn-primary" onclick="editFaq(this)">Edit</button>
                      <button class="btn btn-xs btn-danger" onclick="deleteFaq(<?= $f->id; ?>)">Delete</button>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if(empty($faqs)): ?>
                  <tr><td colspan="5" class="text-center text-muted">No FAQs yet.</td></tr>
                  <?php endif; ?>
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
<?php $this->load->view('comman/code_js.php'); ?>

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
          <div class="form-group"><label>Question</label><input type="text" class="form-control" name="question" id="question" required></div>
          <div class="form-group"><label>Answer</label><textarea class="form-control" name="answer" id="answer" rows="4" required></textarea></div>
          <div class="form-group"><label>Sort Order</label><input type="number" class="form-control" name="sort_order" id="sort_order" value="0"></div>
          <div class="form-group"><label><input type="checkbox" name="is_enabled" id="is_enabled" value="1" checked> Active</label></div>
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
    success:function(res){ if(res.status==='success'){ toastr.success(res.message); location.reload(); } else { toastr.error(res.message || 'Failed to save'); } },
    error:function(){ toastr.error('Request failed. Please check your connection.'); }
  });
}
function deleteFaq(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Delete this FAQ?')) return;
    doDeleteFaq(id);
    return;
  }
  swal({
    title: "Delete FAQ?",
    text: "This action cannot be undone.",
    icon: "warning",
    buttons: true,
    dangerMode: true
  }).then(function(willDelete){
    if(willDelete) doDeleteFaq(id);
  });
}
function doDeleteFaq(id){
  $.post('<?= base_url('online_store/delete_faq/'); ?>'+id, { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' }, function(res){ if(res.status==='success'){ toastr.success(res.message); location.reload(); } else { toastr.error(res.message || 'Failed to delete'); } }, 'json');
}
</script>
</body>
</html>
