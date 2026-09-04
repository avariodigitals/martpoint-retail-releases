<?php $this->load->view('inventory/desktop/_styles'); ?>
<?php $CI =& get_instance();

$adjustment_date  = show_date(date("d-m-Y"));
$reference_no     = '';
$adjustment_note  = '';
$warehouse_id     = '';
$store_id         = get_current_store_id();
$items_count      = 0;
$btn_id           = 'save';
$btn_name         = 'Save';

if(isset($adjustment_id)){
  $q2 = $this->db->query("SELECT * FROM db_stockadjustment WHERE id = ?", array($adjustment_id));
  if($q2->num_rows() > 0){
    $row              = $q2->row();
    $warehouse_id     = $row->warehouse_id;
    $adjustment_date  = show_date($row->adjustment_date);
    $reference_no     = $row->reference_no;
    $adjustment_note  = $row->adjustment_note;
    $store_id         = $row->store_id;

    $items_count_q = $this->db->query("SELECT count(*) as items_count FROM db_stockadjustmentitems WHERE adjustment_id = ?", array($adjustment_id));
    $items_count = $items_count_q->row()->items_count;
    $btn_id      = 'update';
    $btn_name    = 'Update';
  }
}
?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= isset($adjustment_id) ? 'Update stock adjustment' : 'Create a new stock adjustment'; ?></div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><?= htmlspecialchars($page_title); ?></h3>
    <a href="<?= base_url('stock_adjustment'); ?>" class="mp-card-link"><i class="fa fa-arrow-left"></i> Back to List</a>
  </div>
  <div class="mp-card-body">
    <?= form_open('#', array('class'=>'form-horizontal','id'=>'stock_adjustment-form','enctype'=>'multipart/form-data','method'=>'POST')); ?>
    <input type="hidden" id="base_url" value="<?= $base_url; ?>">
    <input type="hidden" value="1" id="hidden_rowcount" name="hidden_rowcount">
    <input type="hidden" value="0" id="hidden_update_rowid" name="hidden_update_rowid">
    <input type="hidden" name="store_id" id="store_id" value="<?= get_current_store_id(); ?>">
    <?php if(isset($adjustment_id)): ?>
    <input type="hidden" name="adjustment_id" id="adjustment_id" value="<?= $adjustment_id; ?>">
    <?php endif; ?>

    <div class="mp-form-grid">
      <?php if(warehouse_module() && warehouse_count()>1): ?>
      <div class="mp-form-group">
        <label for="warehouse_id"><?= $this->lang->line('warehouse'); ?> <span class="text-danger">*</span></label>
        <select class="form-control select2" id="warehouse_id" name="warehouse_id" style="width:100%;">
          <?= get_warehouse_select_list($warehouse_id, get_current_store_id(), true); ?>
        </select>
        <span id="warehouse_id_msg" style="display:none" class="text-danger"></span>
      </div>
      <?php else: ?>
      <input type="hidden" name="warehouse_id" id="warehouse_id" value="<?= $warehouse_id ?: get_store_warehouse_id(); ?>">
      <?php endif; ?>

      <div class="mp-form-group">
        <label for="adjustment_date"><?= $this->lang->line('date'); ?> <span class="text-danger">*</span></label>
        <div class="input-group date" style="width:100%;">
          <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
          <input type="text" class="form-control pull-right datepicker" id="adjustment_date" name="adjustment_date" readonly value="<?= $adjustment_date; ?>">
        </div>
        <span id="adjustment_date_msg" style="display:none" class="text-danger"></span>
      </div>

      <div class="mp-form-group">
        <label for="reference_no"><?= $this->lang->line('reference_no'); ?></label>
        <input type="text" class="mp-form-control" id="reference_no" name="reference_no" value="<?= htmlspecialchars($reference_no); ?>" placeholder="">
        <span id="reference_no_msg" style="display:none" class="text-danger"></span>
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
      <table class="table inv-item-table" style="width:100%;" id="adjustment_table">
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
        <label><?= $this->lang->line('total_quantities'); ?></label>
        <div class="mp-form-control" style="display:flex;align-items:center;">
          <span class="total_quantity text-success" style="font-size:18px;font-weight:700;">0</span>
        </div>
      </div>

      <div class="mp-form-group full">
        <label for="adjustment_note"><?= $this->lang->line('note'); ?></label>
        <textarea class="mp-form-control" id="adjustment_note" name="adjustment_note" rows="3"><?= htmlspecialchars($adjustment_note); ?></textarea>
        <span id="adjustment_note_msg" style="display:none" class="text-danger"></span>
      </div>
    </div>

    <div class="mp-form-actions" style="margin-top:24px;">
      <button type="button" id="<?= $btn_id; ?>" class="mp-btn-primary"><i class="fa fa-check"></i> <?= $btn_name; ?></button>
      <a href="<?= base_url('stock_adjustment'); ?>" class="mp-btn-secondary close_btn"><i class="fa fa-times"></i> Close</a>
    </div>
    <?= form_close(); ?>
  </div>
</div>

<?php $this->load->view('modals/modal_stock_adjustment_item'); ?>
<?php $this->load->view('modals/modal_item'); ?>
<?php $this->load->view('modals/modal_item_or_service'); ?>

<script>var walk_in_customer_name='<?= get_walk_in_customer_name(); ?>'</script>
<script src="<?= $theme_link; ?>js/modals.js"></script>
<script src="<?= $theme_link; ?>js/modals/modal_item.js"></script>
<script src="<?= $theme_link; ?>js/stock_adjustment/stock_adjustment.js"></script>

<script type="text/javascript">
  function final_total(){
    var rowcount = $("#hidden_rowcount").val();
    var total_quantity = 0;
    for(var i=1; i<=rowcount; i++){
      if(document.getElementById("td_data_"+i+"_3")){
        if($("#td_data_"+i+"_3").val() != null && $("#td_data_"+i+"_3").val() != ''){
          total_quantity += parseFloat($("#td_data_"+i+"_3").val());
        }
      }
    }
    $(".total_quantity").html(format_qty(total_quantity));
  }

  function removerow(id){
    $("#row_"+id).remove();
    final_total();
    failed.currentTime = 0;
    failed.play();
  }

  function show_purchase_item_modal(row_id){
    $('#purchase_item').modal('toggle');
    var item_name = $("#td_data_"+row_id+"_1").html();
    var description = $("#description_"+row_id).val();
    $("#popup_item_name").html(item_name);
    $("#popup_description").val(description);
    $("#popup_row_id").val(row_id);
  }

  function set_info(){
    var row_id = $("#popup_row_id").val();
    var description = $("#popup_description").val();
    $("#description_"+row_id).val(description);
    final_total();
    $('#purchase_item').modal('toggle');
  }

  $("#warehouse_id").on("change", function(){
    $("#adjustment_table > tbody").empty();
    final_total();
  });

  $(".close_btn").on("click", function(e){
    e.preventDefault();
    if(typeof swal === 'undefined'){
      if(!confirm('Are you sure you want to navigate away from this page?')) return;
      window.location='<?= base_url('stock_adjustment'); ?>';
    } else {
      swal({
        title: "Leave Page?",
        text: "Are you sure you want to navigate away from this page? Unsaved changes may be lost.",
        icon: "warning",
        buttons: true,
        dangerMode: true
      }).then(function(willLeave){
        if(willLeave) window.location='<?= base_url('stock_adjustment'); ?>';
      });
    }
  });
</script>

<script type="text/javascript">
  <?php if(isset($adjustment_id)): ?>
  $(document).ready(function(){
    var base_url = '<?= base_url(); ?>';
    var adjustment_id = '<?= $adjustment_id; ?>';
    $(".mp-card-form").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.post(base_url+"stock_adjustment/return_stock_adjustment_list/"+adjustment_id, {}, function(result){
      $('#adjustment_table tbody').append(result);
      $("#hidden_rowcount").val(parseInt(<?= $items_count; ?>)+1);
      success.currentTime = 0;
      success.play();
      $(".overlay").remove();
      final_total();
    });
  });
  <?php endif; ?>
</script>

<script>$(".stock_adjustment_form-active-li").addClass("active");</script>
