<!DOCTYPE html>
<html>

<head>
<!-- TABLES CSS CODE -->
<?php include"comman/code_css.php"; ?>

<!-- Lightbox -->
<link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/lightbox/ekko-lightbox.css">
</head>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <!-- Left side column. contains the logo and sidebar -->
  
  <?php include"sidebar.php"; ?>
  <style>
    @media(max-width: 480px){
      .box-header>.box-tools {
          position: absolute;
          right: -13px;
          top: -106px;
      }
    }
    /* Fix oval/round checkbox backgrounds */
    .icheckbox_square-orange, .iradio_square-orange {
        border-radius: 2px !important;
    }
  </style>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?=$page_title;?>
        <small></small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?=$page_title;?></li>
      </ol>
    </section>
    
    <!-- Warehouse wise stock view -->
    <div class="view_warehouse_wise_stock_item">
    </div>
    <!-- Warehouse wise stock view end-->

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

    <!-- Main content -->
    <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
    <input type="hidden" id='base_url' value="<?=$base_url;?>">

    <section class="content">
      <div class="row">
        <!-- ********** ALERT MESSAGE START******* -->
        <?php include"comman/code_flashdata.php"; ?>
        <!-- ********** ALERT MESSAGE END******* -->
        <div class="col-xs-12">
          
          <div class="box box-primary">
            <div class="box-header with-border">
              <!-- <h3 class="box-title"><?=$page_title;?></h3> -->
              

                <div class="row">
                    <div class="col-md-12">                                  
                      <!-- Warehouse Code -->
                      <?php if(warehouse_module()){ ?>
                        <div class="col-md-3">
                    <?php $this->load->view('warehouse/warehouse_code',array('show_warehouse_select_box'=>true,'div_length'=>'',
                      'label_length'=>'','show_all'=>'true','show_all_option'=>true,'remove_star'=>true)); ?>
                    <!-- Warehouse Code end -->
                    </div>
                    <?php } ?>
                    <?php if(service_module() && $CI->permissions('services_view')){ ?>
                    <div class="col-md-3">
                        <label for="item_type" class=" control-label">Item Type</label>
                          <select class="form-control select2" id="item_type" name="item_type"  style="width: 100%;">
                            <?php if($CI->permissions('items_view') && $CI->permissions('services_view')){?>
                              <option value=''>All</option>
                            <?php } ?>  
                            <?php if($CI->permissions('items_view')){?>
                              <option value='Items'>Items</option>
                            <?php } ?>
                            <?php if($CI->permissions('services_view')){?>
                              <option value='Services'>Services</option>
                            <?php } ?>
                          </select>
                    </div>
                  <?php }else{ ?>
                    <input type="hidden" id="item_type" value="Items">
                    <?php } ?>
                    
                  </div>
                </div>

              <?php if($CI->permissions('items_add') || $CI->permissions('services_add')) { ?>
              <div class="box-tools">      
                <?php if($CI->permissions('items_add')){ ?>          
                <a class="btn btn-info margin" href="<?php echo $base_url; ?>items/add">
                <i class="fa fa-plus " ></i> <?= $this->lang->line('create_item'); ?></a>
                <?php } ?>
                <?php if(service_module() && $CI->permissions('services_add')){ ?>
                <a class="btn btn-success margin" href="<?php echo $base_url; ?>services/add">
                <i class="fa fa-plus " ></i> <?= $this->lang->line('create_service'); ?></a>
              <?php } ?>
              </div>
             <?php } ?>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example2" class="table table-bordered custom_hover" width="100%">
                <thead class="bg-gray ">
                <tr>
                  <th class="text-center">
                    <input type="checkbox" class="group_check checkbox" >
                  </th>
                  <th><?= $this->lang->line('image'); ?></th>
                  <!-- <th><?= $this->lang->line('store_name'); ?></th> -->
                  <th><?= $this->lang->line('item_code'); ?></th>
                  <th><?= $this->lang->line('item_name'); ?></th>
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
                <tbody>
				
                </tbody>
               
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
     <?= form_close();?>
  </div>
  <!-- /.content-wrapper -->
  <?php include"footer.php"; ?>
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<!-- SOUND CODE -->
<?php include"comman/code_js_sound.php"; ?>
<!-- TABLES CODE -->
<?php include"comman/code_js.php"; ?>
<!-- Lightbox -->
<script src="<?php echo $theme_link; ?>plugins/lightbox/ekko-lightbox.js"></script>
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
      /* FOR EXPORT BUTTONS START*/
  dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
 /* dom:'<"row"<"col-sm-12"<"pull-left"B><"pull-right">>> <"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>tip',*/
      buttons: {
        buttons: [
            {
                className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left',
                text: 'Delete',
                action: function ( e, dt, node, config ) {
                    multi_delete();
                }
            },
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [2,3,4,5,6,7,8,9,10,11,13]} },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [2,3,4,5,6,7,8,9,10,11,12]} },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [2,3,4,5,6,7,8,9,10,11,12]} },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [2,3,4,5,6,7,8,9,10,11,12]} },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [2,3,4,5,6,7,8,9,10,11,12]} },
            { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat',text:'Columns' },  

            ]
        },
        /* FOR EXPORT BUTTONS END */

        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "order": [], //Initial no order.
        "responsive": true,
        language: {
            processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>'
        },
        // Load data for the table's content from an Ajax source
        "ajax": {
            "url": "<?php echo site_url('items/ajax_list')?>",
            "type": "POST",
            "data": {
                      warehouse_id: $("#warehouse_id").val(),
                      item_type: $("#item_type").val(),
                    },
            complete: function (data) {
             $('.column_checkbox').iCheck({
                checkboxClass: 'icheckbox_square-orange',
                radioClass: 'iradio_square-orange'
              });
             call_code();
              //$(".delete_btn").hide();
             },

        },

        //Set column definition initialisation properties.
        "columnDefs": [
        { 
            "targets": [ 0,14 ], //first column / numbering column
            "orderable": false, //set not orderable
        },
        {
            "targets" :[0],
            "className": "text-center",
        },
        
        ],
    });
    new $.fn.dataTable.FixedHeader( table );
  }
$(document).ready(function() {
    //datatables
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


<script src="<?php echo $theme_link; ?>js/items.js"></script>

<!-- Make sidebar menu hughlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
		
</body>
</html>
