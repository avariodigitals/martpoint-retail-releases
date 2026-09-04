<?php $this->load->view('marketing/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title ?? 'Gift Cards'); ?></h2>
    <div class="mp-page-sub">Manage and track gift card balances</div>
  </div>
  <?php if($CI->permissions('gift_cards_add')) { ?>
  <a class="mp-qa-btn blue" href="<?= base_url('gift_cards/add'); ?>"><i class="fa fa-plus"></i> Add Gift Card</a>
  <?php } ?>
</div>

<div class="mp-table-wrap">
  <div class="box-body">
    <table id="example2" class="table mp-dt-table custom_hover" width="100%">
      <thead>
        <tr>
          <th>#</th>
          <th>Card Number</th>
          <th>Customer</th>
          <th>Initial Value</th>
          <th>Balance</th>
          <th>Issue Date</th>
          <th>Expiry</th>
          <th>Type</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<script>
function load_datatable(){
    var table = $('#example2').DataTable({
        "aLengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
        "processing": true,
        "serverSide": true,
        "order": [],
        "responsive": true,
        "ajax": {
            "url": "<?=site_url('gift_cards/ajax_list')?>",
            "type": "POST",
            "data": function(d){
                d['<?= $this->security->get_csrf_token_name(); ?>'] = '<?= $this->security->get_csrf_hash(); ?>';
            }
        },
        "columnDefs": [{ "targets": [0,9], "orderable": false, }]
    });
}
$(document).ready(function() { load_datatable(); });
function cancel_card(id){
    swal({
        title: "Are you sure?",
        text: "Cancel this gift card?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, cancel it!",
        cancelButtonText: "No",
        closeOnConfirm: true
    }, function(isConfirm){
        if(isConfirm){
            $.post(base_url + 'gift_cards/cancel_card', {id:id}, function(res){
                if(res=='success'){ success_show('Card cancelled'); $('#example2').DataTable().ajax.reload(); }
                else{ error_show('Failed'); }
            });
        }
    });
}
$(".gift-cards-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
</script>
