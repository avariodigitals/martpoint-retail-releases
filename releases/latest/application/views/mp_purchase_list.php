<?php $this->load->view('finance/desktop/_styles'); ?>
<?php
$CI =& get_instance();

/*Total Invoices*/
if (!is_admin()) {
  if ($this->session->userdata('role_id') != '2') {
    $this->db->where("upper(created_by)", strtoupper($this->session->userdata('inv_username')));
  }
}
$total_invoice = $this->db->select("COUNT(*) as total")
                           ->from("db_purchase")
                           ->where("store_id", get_current_store_id())
                           ->get()->row()->total;

/*Total Value*/
if (!is_admin()) {
  if ($this->session->userdata('role_id') != '2') {
    $this->db->where("upper(created_by)", strtoupper($this->session->userdata('inv_username')));
  }
}
$pur_total = $this->db->select("COALESCE(sum(grand_total),0) AS tot_pur_grand_total")
                      ->from("db_purchase")
                      ->where_in("purchase_status", array('Received','Partially Received'))
                      ->where("store_id", get_current_store_id())
                      ->get()->row()->tot_pur_grand_total;

/*Paid Amount*/
if (!is_admin()) {
  if ($this->session->userdata('role_id') != '2') {
    $this->db->where("upper(created_by)", strtoupper($this->session->userdata('inv_username')));
  }
}
$tot_paid_amt = $this->db->select("COALESCE(SUM(paid_amount),0) AS paid_amount")
                         ->from("db_purchase")
                         ->where("store_id", get_current_store_id())
                         ->get()->row()->paid_amount;

/*Total Purchase Due*/
if (!is_admin()) {
  if ($this->session->userdata('role_id') != '2') {
    $this->db->where("upper(created_by)", strtoupper($this->session->userdata('inv_username')));
  }
}
$purchase_due_total = $this->db->select("COALESCE(SUM(purchase_due),0) AS purchase_due")
                                ->from("db_suppliers")
                                ->where("store_id", get_current_store_id())
                                ->get()->row()->purchase_due;

?>

<div class="mp-page-head">
  <div>
    <h2><?=$page_title;?></h2>
    <div class="mp-page-sub">View and manage purchase orders &middot; <?=date('F j, Y');?></div>
  </div>
  <?php if($CI->permissions('purchase_add')): ?>
  <a href="<?=base_url('purchase/add');?>" class="mp-qa-btn green"><i class="fa fa-plus"></i> New Purchase</a>
  <?php endif; ?>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card sales">
    <div class="mp-kpi-icon"><i class="fa fa-shopping-bag"></i></div>
    <div class="mp-kpi-label">Total Invoices</div>
    <div class="mp-kpi-value"><?=number_format($total_invoice);?></div>
    <div class="mp-kpi-sub neutral">All purchase orders</div>
  </div>
  <div class="mp-kpi-card expense">
    <div class="mp-kpi-icon"><i class="fa fa-dollar"></i></div>
    <div class="mp-kpi-label">Total Value</div>
    <div class="mp-kpi-value"><?=$CI->currency(kmb($pur_total));?></div>
    <div class="mp-kpi-sub neutral">Received &amp; partial</div>
  </div>
  <div class="mp-kpi-card profit">
    <div class="mp-kpi-icon"><i class="fa fa-money"></i></div>
    <div class="mp-kpi-label">Paid Amount</div>
    <div class="mp-kpi-value"><?=$CI->currency(kmb($tot_paid_amt));?></div>
    <div class="mp-kpi-sub neutral">Total paid</div>
  </div>
  <div class="mp-kpi-card debt">
    <div class="mp-kpi-icon"><i class="fa fa-minus-circle"></i></div>
    <div class="mp-kpi-label">Total Due</div>
    <div class="mp-kpi-value"><?=$CI->currency(kmb($purchase_due_total));?></div>
    <div class="mp-kpi-sub neutral">Supplier dues</div>
  </div>
</div>

<div class="pay_now_modal"></div>
<div class="view_payments_modal"></div>

<?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
<input type="hidden" id='base_url' value="<?=$base_url;?>">

<div class="mp-table-wrap">
  <div class="mp-card-head" style="padding:18px 20px 14px;border-bottom:1px solid var(--mp-border);">
    <h3 style="font-size:15px;font-weight:700;margin:0;color:var(--mp-text);"><?=$page_title;?></h3>
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
      <?php
      if(warehouse_module() && warehouse_count()>1) {
        $this->load->view('warehouse/warehouse_code',array('show_warehouse_select_box_2'=>true,'show_all_option'=>true));
      } else {
        echo "<input type='hidden' name='warehouse_id' id='warehouse_id' value='".get_store_warehouse_id()."'>";
      }
      ?>
    </div>
  </div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="example2" class="table mp-dt-table" width="100%">
        <thead>
          <tr>
            <th class="text-center"><input type="checkbox" class="group_check checkbox"></th>
            <th><?= $this->lang->line('purchase_date'); ?></th>
            <th><?= $this->lang->line('purchase_code'); ?></th>
            <th><?= $this->lang->line('purchase_status'); ?></th>
            <th><?= $this->lang->line('reference_no'); ?></th>
            <th><?= $this->lang->line('supplier_name'); ?></th>
            <th><?= $this->lang->line('total'); ?></th>
            <th><?= $this->lang->line('paid_amount'); ?></th>
            <th><?= $this->lang->line('payment_status'); ?></th>
            <th><?= $this->lang->line('created_by'); ?></th>
            <th><?= $this->lang->line('action'); ?></th>
          </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr>
            <th></th><th></th><th></th><th></th><th></th>
            <th style="text-align:right">Total</th>
            <th></th><th></th><th></th><th></th><th></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<?= form_close();?>

<?php include "comman/code_js_sound.php"; ?>

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
          { extend: 'copy', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
          { extend: 'excel', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
          { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
          { extend: 'print', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
          { extend: 'csv', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
          { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat',footer: true, text:'Columns' }
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
        "url": "<?php echo site_url('purchase/ajax_list')?>",
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
        {
          "targets": [ 0,10 ],
          "orderable": false,
        },
        {
          "targets" :[0],
          "className": "text-center",
        }
      ],
      "footerCallback": function ( row, data, start, end, display ) {
        var api = this.api(), data;
        var intVal = function ( i ) {
          return typeof i === 'string' ?
            i.replace(/[\$,]/g, '')*1 :
            typeof i === 'number' ?
              i : 0;
        };
        var total = api
          .column( 6, { page: 'none'} )
          .data()
          .reduce( function (a, b) {
            return intVal(a) + intVal(b);
          }, 0 );
        var paid = api
          .column( 7, { page: 'none'} )
          .data()
          .reduce( function (a, b) {
            return intVal(a) + intVal(b);
          }, 0 );
        $( api.column( 6 ).footer() ).html(to_Fixed(total));
        $( api.column( 7 ).footer() ).html(to_Fixed(paid));
      }
    });
    new $.fn.dataTable.FixedHeader( table );
  }

  $(document).ready(function() {
    load_datatable();
  });

  $("#warehouse_id").on("change",function(){
    $('#example2').DataTable().destroy();
    load_datatable();
  });

  function show_change_status_modal(purchase_id){
    var base_url = $("#base_url").val();
    $.post(base_url+'purchase/show_change_status_modal', {purchase_id: purchase_id}, function(result) {
      $(".change_status_modal").html('').html(result);
      $('#change_status_modal').modal('toggle');
      if(typeof toggle_cs_batch_fields === 'function'){
        toggle_cs_batch_fields();
      }
    }).fail(function(xhr, status, error) {
      console.error("Modal load error:", error);
      console.error("Response:", xhr.responseText);
      toastr.error("Failed to load status modal: " + error);
    });
  }

  $(document).on('click', '#btn_change_status_save', function(){
    var base_url = $("#base_url").val();
    var formData = $("#change-status-form").serialize();
    $("#btn_change_status_save").attr('disabled', true).html('<i class="fa fa-refresh fa-spin"></i> Updating...');
    $.post(base_url+'purchase/change_status', formData, function(result) {
      if(result == 'success'){
        toastr["success"]("Status updated successfully!");
        $('#change_status_modal').modal('toggle');
        $('#example2').DataTable().ajax.reload();
      } else {
        toastr["error"]("Failed to update status. Please try again.");
      }
      $("#btn_change_status_save").attr('disabled', false).html('Update Status');
    }).fail(function(xhr, status, error) {
      console.error("Status change error:", error);
      console.error("Response:", xhr.responseText);
      toastr.error("Failed to update status: " + error);
      $("#btn_change_status_save").attr('disabled', false).html('Update Status');
    });
  });
</script>

<!-- Change Status Modal Container -->
<div class="change_status_modal"></div>

<script>$(".purchase-list-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
