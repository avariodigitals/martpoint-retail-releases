<?php $this->load->view('inventory/desktop/_styles'); ?>
<?php $CI =& get_instance();

$transfer_date  = show_date(date("d-m-Y"));
$warehouse_from = '';
$warehouse_to   = '';
$note           = '';
$store_id       = get_current_store_id();
$items_count    = 0;
$btn_id         = 'save';
$btn_name       = 'Save';

if(isset($stocktransfer_id)){
  $q2 = $this->db->query("SELECT * FROM db_stocktransfer WHERE id = ?", array($stocktransfer_id));
  if($q2->num_rows() > 0){
    $row          = $q2->row();
    $transfer_date = show_date($row->transfer_date);
    $warehouse_from = $row->warehouse_from;
    $warehouse_to   = $row->warehouse_to;
    $note           = $row->note;
    $store_id       = $row->store_id;

    $items_count_q = $this->db->query("SELECT count(*) as items_count FROM db_stocktransferitems WHERE stocktransfer_id = ?", array($stocktransfer_id));
    $items_count = $items_count_q->row()->items_count;
    $btn_id      = 'update';
    $btn_name    = 'Update';
  }
}
?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= isset($stocktransfer_id) ? 'Update stock transfer' : 'Create a new stock transfer'; ?></div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><?= htmlspecialchars($page_title); ?></h3>
    <a href="<?= base_url('stock_transfer/view'); ?>" class="mp-card-link"><i class="fa fa-arrow-left"></i> Back to List</a>
  </div>
  <div class="mp-card-body">
    <?= form_open('#', array('class'=>'form-horizontal','id'=>'stock_transfer_form','enctype'=>'multipart/form-data','method'=>'POST')); ?>
    <input type="hidden" id="base_url" value="<?= $base_url; ?>">
    <input type="hidden" value="1" id="hidden_rowcount" name="hidden_rowcount">
    <input type="hidden" value="0" id="hidden_update_rowid" name="hidden_update_rowid">
    <input type="hidden" name="store_from" id="store_from" value="<?= get_current_store_id(); ?>">
    <?php if(isset($stocktransfer_id)): ?>
    <input type="hidden" name="stocktransfer_id" id="stocktransfer_id" value="<?= $stocktransfer_id; ?>">
    <?php endif; ?>

    <div class="mp-form-grid">
      <div class="mp-form-group">
        <label for="transfer_date"><?= $this->lang->line('transfer_date') ?: 'Transfer Date'; ?> <span class="text-danger">*</span></label>
        <div class="input-group date" style="width:100%;">
          <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
          <input type="text" class="form-control pull-right datepicker" id="transfer_date" name="transfer_date" readonly value="<?= $transfer_date; ?>">
        </div>
        <span id="transfer_date_msg" style="display:none" class="text-danger"></span>
      </div>

      <div class="mp-form-group">
        <label for="warehouse_from"><?= $this->lang->line('from_warehouse') ?: 'From Warehouse'; ?> <span class="text-danger">*</span></label>
        <select class="form-control select2" id="warehouse_from" name="warehouse_from" style="width:100%;">
          <?= get_warehouse_select_list($warehouse_from, get_current_store_id(), true); ?>
        </select>
        <span id="warehouse_from_msg" style="display:none" class="text-danger"></span>
      </div>

      <div class="mp-form-group">
        <label for="warehouse_to"><?= $this->lang->line('to_warehouse') ?: 'To Warehouse'; ?> <span class="text-danger">*</span></label>
        <select class="form-control select2" id="warehouse_to" name="warehouse_to" style="width:100%;">
          <?= get_warehouse_select_list($warehouse_to, get_current_store_id(), true); ?>
        </select>
        <span id="warehouse_to_msg" style="display:none" class="text-danger"></span>
      </div>
    </div>

    <div class="mp-form-group full" style="margin-top:24px;">
      <label><?= $this->lang->line('search_items') ?: 'Search Items'; ?></label>
      <div class="input-group">
        <span class="input-group-addon" title="Select Items"><i class="fa fa-barcode"></i></span>
        <input type="text" class="form-control" placeholder="Item name / Barcode / Itemcode" id="item_search" autofocus>
        <span class="input-group-addon pointer text-green show_item_service" title="Click to Add New Item or Service"><i class="fa fa-plus"></i></span>
      </div>
    </div>

    <div class="mp-dt-scroll" style="margin:20px 0;">
      <table class="table inv-item-table" style="width:100%;" id="stock_table">
        <thead>
          <tr>
            <th style="width:55%"><?= $this->lang->line('item_name'); ?></th>
            <th style="width:30%"><?= $this->lang->line('quantity'); ?></th>
            <th style="width:15%"><?= $this->lang->line('action'); ?></th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <div class="mp-form-grid">
      <div class="mp-form-group">
        <label><?= $this->lang->line('quantity'); ?></label>
        <div class="mp-form-control" style="display:flex;align-items:center;">
          <span class="total_quantity text-success" style="font-size:18px;font-weight:700;">0</span>
        </div>
      </div>

      <div class="mp-form-group full">
        <label for="note"><?= $this->lang->line('note'); ?></label>
        <textarea class="mp-form-control" id="note" name="note" rows="3"><?= htmlspecialchars($note); ?></textarea>
        <span id="note_msg" style="display:none" class="text-danger"></span>
      </div>
    </div>

    <div class="mp-form-actions" style="margin-top:24px;">
      <button type="button" id="<?= $btn_id; ?>" class="mp-btn-primary"><i class="fa fa-check"></i> <?= $btn_name; ?></button>
      <a href="<?= base_url('stock_transfer/view'); ?>" class="mp-btn-secondary close_btn"><i class="fa fa-times"></i> Close</a>
    </div>
    <?= form_close(); ?>
  </div>
</div>

<?php $this->load->view('modals/modal_item'); ?>
<?php $this->load->view('modals/modal_item_or_service'); ?>

<script>var walk_in_customer_name='<?= get_walk_in_customer_name(); ?>'</script>
<script src="<?= $theme_link; ?>js/modals.js"></script>
<script src="<?= $theme_link; ?>js/modals/modal_item.js"></script>
<script src="<?= $theme_link; ?>js/warehouse/stock_transfer.js"></script>

<script type="text/javascript">
  /* Branch / warehouse change clears the item table */
  $("#warehouse_from").on("change", function(){
    $("#stock_table > tbody").empty();
    calculate_quantity();
  });

  $(".close_btn").on("click", function(e){
    e.preventDefault();
    if(typeof swal === 'undefined'){
      if(!confirm('Are you sure you want to navigate away from this page?')) return;
      window.location='<?= base_url('stock_transfer/view'); ?>';
    } else {
      swal({
        title: "Leave Page?",
        text: "Are you sure you want to navigate away from this page? Unsaved changes may be lost.",
        icon: "warning",
        buttons: true,
        dangerMode: true
      }).then(function(willLeave){
        if(willLeave) window.location='<?= base_url('stock_transfer/view'); ?>';
      });
    }
  });

  /* Inline helpers expected by the row templates */
  function removerow(id){
    $("#row_"+id).remove();
    calculate_quantity();
    failed.currentTime = 0;
    failed.play();
  }

  function calculate_quantity(){
    var total_quantity = 0;
    var rowcount = $("#hidden_rowcount").val();
    for(var i=1; i<=rowcount; i++){
      if(document.getElementById("td_data_"+i+"_1")){
        if($("#td_data_"+i+"_1").val() != null && $("#td_data_"+i+"_1").val() != ''){
          total_quantity += parseFloat($("#td_data_"+i+"_3").val() || 0);
        }
      }
    }
    $(".total_quantity").html(format_qty(total_quantity));
  }

  <?php if(isset($stocktransfer_id)): ?>
  $(document).ready(function(){
    var base_url = '<?= base_url(); ?>';
    var stocktransfer_id = '<?= $stocktransfer_id; ?>';
    $(".mp-card-form").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.post(base_url+"stock_transfer/return_stock_list/"+stocktransfer_id, {}, function(result){
      $('#stock_table tbody').append(result);
      $("#hidden_rowcount").val(parseInt(<?= $items_count; ?>)+1);
      success.currentTime = 0;
      success.play();
      $(".overlay").remove();
      calculate_quantity();
    });
  });
  <?php endif; ?>
</script>

<script>$(".stock_transfer_form-active-li").addClass("active");</script>
