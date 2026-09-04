<?php $this->load->view('inventory/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">View and manage stock transfers between warehouses</div>
  </div>
  <?php if($CI->permissions('stock_transfer_add')): ?>
  <a href="<?= base_url('stock_transfer/add'); ?>" class="mp-qa-btn blue">
    <i class="fa fa-plus"></i> New Transfer
  </a>
  <?php endif; ?>
</div>

<?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
<input type="hidden" id="base_url" value="<?= $base_url; ?>">

<div class="mp-table-wrap">
  <div class="mp-card-head">
    <h3><?= htmlspecialchars($page_title); ?></h3>
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
      <?php if(warehouse_module() && warehouse_count()>1): ?>
      <?php $warehouse_opts = get_warehouse_select_list('', get_current_store_id(), false); ?>
      <?php $warehouse_opts = preg_replace('/\sselected/', '', $warehouse_opts); ?>
      <select class="form-control select2" id="warehouse_id" name="warehouse_id" style="width:auto;min-width:180px;">
        <option value="" selected>-All Branches-</option>
        <?= $warehouse_opts; ?>
      </select>
      <?php else: ?>
      <input type="hidden" name="warehouse_id" id="warehouse_id" value="<?= get_store_warehouse_id(); ?>">
      <?php endif; ?>
    </div>
  </div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="example2" class="table mp-dt-table" width="100%">
        <thead>
          <tr>
            <th class="text-center"><input type="checkbox" class="group_check checkbox"></th>
            <th><?= $this->lang->line('transfer_date') ?: 'Transfer Date'; ?></th>
            <th><?= $this->lang->line('from_warehouse') ?: 'From Warehouse'; ?></th>
            <th><?= $this->lang->line('to_warehouse') ?: 'To Warehouse'; ?></th>
            <th><?= $this->lang->line('details') ?: 'Details'; ?></th>
            <th><?= $this->lang->line('note') ?: 'Note'; ?></th>
            <th><?= $this->lang->line('created_by') ?: 'Created By'; ?></th>
            <th><?= $this->lang->line('action') ?: 'Action'; ?></th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>
<?= form_close(); ?>

<script type="text/javascript">
  function load_datatable(){
    var table = $('#example2').DataTable({
      "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
      dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
      buttons: {
        buttons: [
          {
            className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left',
            text: 'Delete',
            action: function ( e, dt, node, config ) {
              multi_delete();
            }
          },
          { extend: 'copy', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6]} },
          { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6]} },
          { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6]} },
          { extend: 'print', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6]} },
          { extend: 'csv', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6]} },
          { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat', text:'Columns' }
        ]
      },
      "processing": true,
      "serverSide": true,
      "order": [],
      "responsive": false,
      language: {
        processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>'
      },
      "ajax": {
        "url": "<?= site_url('stock_transfer/ajax_list'); ?>",
        "type": "POST",
        "data": {
          warehouse_id: $("#warehouse_id").val()
        },
        complete: function (data) {
          $('.column_checkbox').iCheck({
            checkboxClass: 'icheckbox_square-orange',
            radioClass: 'iradio_square-orange',
            increaseArea: '10%'
          });
          call_code();
        }
      },
      "columnDefs": [
        { "targets": [0,4,7], "orderable": false },
        { "targets": [0], "className": "text-center" }
      ]
    });
    new $.fn.dataTable.FixedHeader( table );
  }

  $(document).ready(function() {
    load_datatable();
  });

  $("#warehouse_id").on("change", function(){
    $('#example2').DataTable().destroy();
    load_datatable();
  });
</script>

<script src="<?= $theme_link; ?>js/warehouse/stock_transfer.js"></script>
<script type="text/javascript">
  /* Override theme delete helpers to target the modern table wrapper */
  function doDeleteStock(q_id, base_url){
    $(".mp-table-wrap").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.post(base_url+"stock_transfer/delete_stock", {q_id: q_id}, function(result){
      if(result=="success"){
        toastr["success"]("Record Deleted Successfully!");
        $('#example2').DataTable().ajax.reload();
      } else if(result=="failed"){
        toastr["error"]("Failed to delete. Try again!");
      } else {
        toastr["error"](result);
      }
      $(".overlay").remove();
    });
  }

  function delete_stock(q_id){
    if(!confirm("Are you sure you want to delete this record?")) return;
    doDeleteStock(q_id, $("#base_url").val());
  }

  function multi_delete(){
    var base_url = $("#base_url").val();
    if(!confirm("Are you sure?")) return;
    var data = new FormData($('#table_form')[0]);
    if(!xss_validation(data)){ return false; }
    $(".mp-table-wrap").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.ajax({
      type: 'POST',
      url: base_url+'stock_transfer/multi_delete',
      data: data,
      cache: false,
      contentType: false,
      processData: false,
      success: function(result){
        if(result=="success"){
          toastr["success"]("Records Deleted Successfully!");
          $('#example2').DataTable().ajax.reload();
          $(".delete_btn").hide();
          $(".group_check").prop("checked",false).iCheck('update');
        } else if(result=="failed"){
          toastr["error"]("Failed to delete. Try again!");
        } else {
          toastr["error"](result);
        }
        $(".overlay").remove();
      }
    });
  }
</script>

<script>$(".stock_transfer_list-active-li").addClass("active");</script>
