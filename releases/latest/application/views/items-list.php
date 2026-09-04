<?php $this->load->view('admin/desktop/_styles'); ?>

<?php
$CI =& get_instance();
$store_name = $this->session->userdata('store_name') ?: 'MartPoint';
$expiry_enabled = mp_feature_enabled('expiry_tracking');
$mfg_enabled    = mp_feature_enabled('mfg_tracking');
$export_columns = [2,3,4,5,6,7,8,9,10];
$col = 11;
if ($expiry_enabled) { $export_columns[] = $col; $col++; }
if ($mfg_enabled) { $export_columns[] = $col; $col++; }
$export_columns[] = $col; // status
$export_columns_json = json_encode($export_columns);
$hidden_targets = [];
if (!$expiry_enabled) $hidden_targets[] = 11;
if (!$mfg_enabled) $hidden_targets[] = 12;
$hidden_targets_json = json_encode($hidden_targets);
$last_col = $col; // status
$action_col = $col + 1;
$non_sortable = json_encode([0, $action_col]);
?>

<style>
/* Table wrapper */
.it-table-wrap{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:16px;overflow:visible;width:100%;box-sizing:border-box;box-shadow:var(--mp-shadow-sm)}
.it-table-wrap .box-body{padding:0;overflow:visible}
#example2{font-size:13px!important;width:100%!important;border-collapse:collapse!important}
#example2 .item-name{font-weight:600;color:var(--mp-ink)}
#example2 .item-sku{font-family:'SF Mono',Monaco,Consolas,monospace;font-size:12px;color:var(--mp-muted)}
#example2 .item-code{font-family:'SF Mono',Monaco,Consolas,monospace;font-size:12px;color:var(--mp-muted)}
#example2 img{border-radius:8px;border:1px solid var(--mp-border)!important;object-fit:cover}
#example2 .price-val{font-weight:700;color:var(--mp-primary)}

/* DataTables buttons (export) — delete button variant */
.dataTables_wrapper .dt-buttons .delete_btn{background:rgba(220,38,38,.08)!important;color:var(--mp-danger)!important;border-color:rgba(220,38,38,.2)!important}
.dataTables_wrapper .dt-buttons .delete_btn:hover{background:var(--mp-danger)!important;color:#fff!important;border-color:var(--mp-danger)!important}

/* Action dropdown */
#example2 .btn-group .btn{border:1px solid var(--mp-border)!important;border-radius:8px!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;padding:6px 10px!important;font-size:12px!important;font-weight:600!important;cursor:pointer!important}
#example2 .btn-group .btn:hover{background:var(--mp-bg)!important}
#example2 .dropdown-menu{border-radius:10px!important;border:1px solid var(--mp-border)!important;box-shadow:var(--mp-shadow)!important;padding:6px!important;min-width:160px!important}
#example2 .dropdown-menu > li > a{padding:9px 12px!important;font-size:13px!important;color:var(--mp-ink)!important;border-radius:6px!important;display:flex!important;align-items:center!important;gap:8px!important}
#example2 .dropdown-menu > li > a:hover{background:var(--mp-bg)!important;color:var(--mp-primary)!important}

/* Filter bar */
.it-filter-bar{display:flex;align-items:flex-end;gap:16px;flex-wrap:wrap;margin-bottom:20px}
.it-filter-bar .it-filter-group{display:flex;flex-direction:column;gap:6px}
.it-filter-bar .it-filter-group > label{font-size:12px;font-weight:600;color:var(--mp-muted);text-transform:uppercase;letter-spacing:.04em}
.it-filter-bar .mp-form-control{min-width:180px;padding:9px 14px;border:1px solid var(--mp-border);border-radius:10px;background:var(--mp-surface);color:var(--mp-ink);font-size:14px;font-weight:500;font-family:inherit}
.it-filter-bar select.mp-form-control{cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2378716C' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:38px}

/* Modal styling */
.modal-content{border-radius:14px!important;border:none!important;box-shadow:var(--mp-shadow)!important}
.modal-header{border-top-left-radius:14px!important;border-top-right-radius:14px!important}
.modal-header.bg-navy{background:var(--mp-primary)!important;color:#fff!important}
.modal-header .close{color:#fff!important;opacity:.9!important;font-size:24px!important}
.modal-body{padding:20px!important}
.modal-footer{border-top:1px solid var(--mp-border)!important;padding:14px 20px!important}
.modal-footer .btn-default{background:var(--mp-bg)!important;color:var(--mp-ink)!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;font-weight:600!important}

@media (max-width:767px){
  .it-filter-bar{flex-direction:column;align-items:stretch}
  .it-filter-bar .it-filter-group{width:100%}
  .it-filter-bar .mp-form-control{min-width:0;width:100%}
}
</style>

<div class="mp-section">
  <?php include "comman/code_flashdata.php"; ?>
</div>

<!-- Page Header -->
<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2><?= $page_title; ?></h2>
      <div class="mp-page-sub"><?= htmlspecialchars($store_name); ?> &mdash; Manage your <?= strtolower(mp_label('item')); ?> catalog</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a class="mp-qa-btn" href="<?php echo $base_url; ?>items/export_items_csv" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);">
        <i class="fa fa-download"></i> Export CSV
      </a>
      <?php if(service_module() && $CI->permissions('services_add')): ?>
      <a class="mp-qa-btn teal" href="<?php echo $base_url; ?>services/add">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <?= $this->lang->line('create_service'); ?>
      </a>
      <?php endif; ?>
      <?php if($CI->permissions('items_add')): ?>
      <a class="mp-qa-btn green" href="<?php echo $base_url; ?>items/add">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <?= $this->lang->line('create_item'); ?>
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Filter Bar + Table -->
<div class="mp-section">
  <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
  <input type="hidden" id='base_url' value="<?=$base_url;?>">

  <!-- Warehouse wise stock view -->
  <div class="view_warehouse_wise_stock_item"></div>

  <!-- Item History Modal -->
  <div class="modal fade" id="item_history_modal" tabindex="-1" role="dialog" aria-labelledby="itemHistoryLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-navy" style="color:#fff;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:1;"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="itemHistoryLabel" style="color:#fff;"><i class="fa fa-history"></i> Product History</h4>
        </div>
        <div class="modal-body" id="item_history_content">
          <div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i> Loading...</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="it-filter-bar">
    <?php if(warehouse_module()): ?>
    <div class="it-filter-group">
      <label for="warehouse_id">Branch</label>
      <select class="mp-form-control select2" id="warehouse_id" name="warehouse_id" style="width:100%;">
        <?php
        if(!is_admin() && !is_store_admin()){
          $privileged_warehouses = get_privileged_warehouses_ids();
          if(!empty($privileged_warehouses)){
            $this->db->where("id in ($privileged_warehouses)");
          } else {
            $this->db->where("id",0);
          }
        }
        $this->db->select("*")->where("status",1)->where("store_id",get_current_store_id())->from("db_warehouse");
        $q2 = $this->db->get();
        if($q2->num_rows() > 0){
          echo "<option value=''>-All Branches-</option>";
          foreach($q2->result() as $res1){
            $selected = (isset($warehouse_id) && !empty($warehouse_id) && $warehouse_id==$res1->id) ? 'selected' : '';
            echo "<option ".$selected." value='".$res1->id."'>".$res1->warehouse_name."</option>";
          }
        } else {
          echo "<option value=''>No Records Found</option>";
        }
        ?>
      </select>
    </div>
    <?php endif; ?>

    <?php if(service_module() && $CI->permissions('services_view')): ?>
    <div class="it-filter-group">
      <label for="item_type">Item Type</label>
      <select class="mp-form-control select2" id="item_type" name="item_type" style="width:100%;">
        <?php if($CI->permissions('items_view') && $CI->permissions('services_view')): ?>
        <option value=''>All</option>
        <?php endif; ?>
        <?php if($CI->permissions('items_view')): ?>
        <option value='Items'>Items</option>
        <?php endif; ?>
        <?php if($CI->permissions('services_view')): ?>
        <option value='Services'>Services</option>
        <?php endif; ?>
      </select>
    </div>
    <?php else: ?>
    <input type="hidden" id="item_type" value="Items">
    <?php endif; ?>
  </div>

  <!-- Items Table -->
  <div class="it-table-wrap">
    <div class="box-body">
      <table id="example2" class="table custom_hover" width="100%">
        <thead>
        <tr>
          <th class="text-center">
            <input type="checkbox" class="group_check checkbox" >
          </th>
          <th><?= $this->lang->line('image'); ?></th>
          <th><?= mp_label('item'); ?> Code</th>
          <th><?= mp_label('item'); ?> Name</th>
          <th><?= $this->lang->line('brand'); ?></th>
          <th><?= $this->lang->line('category'); ?>/<br><?= $this->lang->line('item_type'); ?></th>
          <th><?= $this->lang->line('unit'); ?></th>
          <th><?= $this->lang->line('stock'); ?></th>
          <th><?= $this->lang->line('alert_quantity'); ?></th>
          <th><?= $this->lang->line('sales_price'); ?></th>
          <th><?= $this->lang->line('tax'); ?></th>
          <th><?= $this->lang->line('expire_date'); ?></th>
          <th>MFG Date</th>
          <th><?= $this->lang->line('status'); ?></th>
          <th><?= $this->lang->line('action'); ?></th>
        </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <?= form_close(); ?>
</div>

<script type="text/javascript">
  $(document).on('click', '[data-toggle="lightbox"]', function(event) {
    event.preventDefault();
    $(this).ekkoLightbox();
  });
</script>

<script type="text/javascript">
  function load_datatable(){
    var table = $('#example2').DataTable({
        "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
        dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
        buttons: {
            buttons: [
                {
                    className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left',
                    text: 'Delete',
                    action: function ( e, dt, node, config ) {
                        multi_delete();
                    }
                },
                { extend: 'copy', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: <?= $export_columns_json; ?> } },
                { extend: 'excel', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: <?= $export_columns_json; ?> } },
                { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: <?= $export_columns_json; ?> } },
                { extend: 'print', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: <?= $export_columns_json; ?> } },
                { extend: 'csv', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: <?= $export_columns_json; ?> } },
                { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat',text:'Columns' },
            ]
        },
        "processing": true,
        "serverSide": true,
        "order": [],
        "responsive": true,
        "ajax": {
            "url": "<?php echo site_url('items/ajax_list')?>",
            "type": "POST",
            "data": function(d) {
                d.warehouse_id = $("#warehouse_id").val();
                d.item_type = $("#item_type").val();
            },
            complete: function (data) {
                $('.column_checkbox').iCheck({
                    checkboxClass: 'icheckbox_square-orange',
                    radioClass: 'iradio_square-orange'
                });
                call_code();
            },
        },
        "columnDefs": [
            {
                "targets": <?= $non_sortable; ?>,
                "orderable": false,
            },
            {
                "targets" :[0],
                "className": "text-center",
            },
            <?php if (!empty($hidden_targets)): ?>
            {
                "targets": <?= $hidden_targets_json; ?>,
                "visible": false,
            },
            <?php endif; ?>
        ],
    });
  }
$(document).ready(function() {
   load_datatable();
});

function view_item_history(item_id){
    $('#item_history_content').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i> Loading...</div>');
    $('#item_history_modal').modal('show');
    $.ajax({
        url: base_url + 'items/get_item_history/' + item_id,
        type: 'GET',
        dataType: 'json',
        success: function(res){
            if(res.status == 'success'){
                $('#item_history_content').html(res.html);
            } else {
                $('#item_history_content').html('<div class="alert alert-danger">'+res.message+'</div>');
            }
        },
        error: function(){
            $('#item_history_content').html('<div class="alert alert-danger">Failed to load history.</div>');
        }
    });
}
$("#warehouse_id,#item_type").on("change",function(){
    $('#example2').DataTable().destroy();
    load_datatable();
});
</script>

<!-- Make sidebar menu highlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
