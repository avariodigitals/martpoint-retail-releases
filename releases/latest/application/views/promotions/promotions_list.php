<?php $this->load->view('admin/desktop/_styles'); ?>

<?php
$CI =& get_instance();
$store_name = $this->session->userdata('store_name') ?: 'MartPoint';
?>

<style>
.pr-table-wrap{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:16px;overflow:hidden;width:100%;box-sizing:border-box;box-shadow:var(--mp-shadow-sm)}
.pr-table-wrap .box-body{padding:0;overflow-x:auto}
#promo-list-table{font-size:13px!important;width:100%!important;border-collapse:collapse!important}
#promo-list-table .promo-name{font-weight:600;color:var(--mp-ink)}
#promo-list-table .promo-code{font-family:'SF Mono',Monaco,Consolas,monospace;font-size:12px;color:var(--mp-muted)}
#promo-list-table .discount-val{font-weight:700;color:var(--mp-primary)}

/* Action buttons — clean icon buttons */
#promo-list-table .pr-actions{display:flex!important;gap:6px!important;align-items:center!important}
#promo-list-table .pr-actions a,#promo-list-table .pr-actions button{display:inline-flex!important;align-items:center!important;justify-content:center!important;width:32px!important;height:32px!important;border-radius:8px!important;border:1px solid var(--mp-border)!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;cursor:pointer!important;transition:all .15s ease!important;text-decoration:none!important;padding:0!important}
#promo-list-table .pr-actions a:hover,#promo-list-table .pr-actions button:hover{background:var(--mp-bg)!important;text-decoration:none!important}
#promo-list-table .pr-actions .pr-edit:hover{border-color:var(--mp-primary)!important;color:var(--mp-primary)!important;background:rgba(0,87,255,.06)!important}
#promo-list-table .pr-actions .pr-delete:hover{border-color:var(--mp-danger)!important;color:var(--mp-danger)!important;background:rgba(220,38,38,.06)!important}
</style>

<div class="mp-section">
  <?php include "comman/code_flashdata.php"; ?>
</div>

<!-- Page Header + Add Button -->
<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2><?= $page_title; ?></h2>
      <div class="mp-page-sub"><?= htmlspecialchars($store_name); ?> &mdash; <?= $this->lang->line('promotion_list'); ?></div>
    </div>
    <a class="mp-qa-btn green" href="<?= base_url('promotions/add'); ?>">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      <?= $this->lang->line('promotion_add'); ?>
    </a>
  </div>
</div>

<!-- Promotions Table -->
<div class="mp-section">
  <div class="pr-table-wrap">
    <div class="box-body">
      <table id="promo-list-table" class="table custom_hover responsive" width="100%">
        <thead>
        <tr>
          <th class="text-center"><input type="checkbox" id="select_all" class="checkbox"></th>
          <th>Promotion</th>
          <th>Discount</th>
          <th>Applies To</th>
          <th>Rules</th>
          <th>Start</th>
          <th>End</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
        </thead>
        <tbody id="tbodyid"></tbody>
      </table>
    </div>
  </div>
</div>

<script type="text/javascript">
var base_url = "<?= $base_url; ?>";
$(document).ready(function(){
  var table = $('#promo-list-table').DataTable({
    "aLengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
    "processing": true,
    "serverSide": true,
    "responsive": true,
    "order": [],
    "ajax": {
      url: base_url + "promotions/ajax_list",
      type: "POST",
      data: function(d){
        d[window.csrfName] = window.csrfHash;
      }
    },
    "columnDefs": [{ "orderable": false, "targets": [0, 8] }],
    "drawCallback": function(){
      $('.column_checkbox').iCheck({
        checkboxClass: 'icheckbox_square-orange',
        radioClass: 'iradio_square-orange'
      });
    }
  });
  $("#select_all").on("ifChanged", function(){
    $(".column_checkbox").iCheck(this.checked ? 'check' : 'uncheck');
  });
});

function delete_promotion(id){
  swal({
    title: "Delete Promotion?",
    text: "This cannot be undone. The promotion and all its item links will be removed.",
    icon: "warning",
    buttons: { cancel: "Cancel", confirm: { text: "Delete", value: true, closeModal: false } },
    dangerMode: true
  }).then(function(confirmed){
    if(!confirmed) return;
    $.post(base_url + "promotions/delete", {
      q_id: id,
      [window.csrfName]: window.csrfHash
    }, function(res){
      if(res.indexOf("success") !== -1){
        toastr.success("Promotion deleted.");
        $('#promo-list-table').DataTable().ajax.reload();
      } else {
        toastr.error(res || "Failed to delete promotion.");
      }
      swal.close();
    }).fail(function(){
      toastr.error("Network error. Please try again.");
      swal.close();
    });
  });
}
</script>
<script>$(".<?php echo basename(__FILE__, '.php'); ?>-active-li").addClass("active");</script>
