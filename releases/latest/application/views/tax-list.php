<?php
/**
 * Tax list — CONTENT ONLY (rendered inside mp_layout).
 * Legacy chrome (doctype/code_css/sidebar/footer/code_js) removed. The
 * DataTable config and the #taxgroup-modal stay exactly as they were;
 * tax-group.js is enqueued by the controller via $data['extra_js_files'].
 */
?>
<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2><?= $this->lang->line('tax_list'); ?></h2>
      <div class="mp-page-sub">View/Search <?= $this->lang->line('tax'); ?></div>
    </div>
  </div>
</div>

    <!-- **********************MODALS***************** -->
    <div class="modal fade" id="taxgroup-modal">
      
    </div>
    <!-- /.modal -->
    <!-- **********************MODALS END***************** -->

    <!-- Main content -->
    <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
    <input type="hidden" id='base_url' value="<?=$base_url;?>">
    <section class="mp-section">
      <div class="row">
        <!-- ********** ALERT MESSAGE START******* -->
          <?php include"comman/code_flashdata.php"; ?>
            <!-- ********** ALERT MESSAGE END******* -->
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title"><?= $this->lang->line('tax_list'); ?></h3>
              <?php if($CI->permissions('tax_add')) { ?>
              <div class="box-tools">
                <a class="btn btn-block btn-info" href="<?php echo $base_url; ?>tax/add">
                <i class="fa fa-plus"></i> <?= $this->lang->line('new_tax'); ?></a>
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
                <!--   <th><?= $this->lang->line('store_name'); ?></th> -->
                  <th><?= $this->lang->line('tax_name'); ?></th>
                  <th><?= $this->lang->line('tax'); ?>(%)</th>
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
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title"><?= $this->lang->line('tax_groups'); ?></h3>
              <?php if($CI->permissions('tax_add')) { ?>
              <div class="box-tools">
                <a class="btn btn-block btn-info" href="<?php echo $base_url; ?>tax_group/add">
                <i class="fa fa-plus"></i> <?= $this->lang->line('new_tax_group'); ?></a>
              </div>
              <?php } ?>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example3" class="table table-bordered custom_hover" width="100%">
                <thead class="bg-gray ">
                <tr>
                  <!-- <th><?= $this->lang->line('store_name'); ?></th> -->
                  <th><?= $this->lang->line('tax_name'); ?></th>
                  <th><?= $this->lang->line('tax'); ?>(%)</th>
                  <th><?= $this->lang->line('sub_taxes'); ?></th>
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

<!-- tax.js and tax-group.js are enqueued by the controller via extra_js_files -->
<script type="text/javascript">
$(document).ready(function() {
    //datatables
   var table = $('#example2').DataTable({ 

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
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [1,2,3]} },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [1,2,3]} },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [1,2,3]} },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [1,2,3]} },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [1,2,3]} },
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
            "url": "<?php echo site_url('tax/ajax_list')?>",
            "type": "POST",
            
            complete: function (data) {
             $('.column_checkbox').iCheck({
                checkboxClass: 'icheckbox_square-orange',
                /*uncheckedClass: 'bg-white',*/
                radioClass: 'iradio_square-orange',
                increaseArea: '10%' // optional
              });
             call_code();
              //$(".delete_btn").hide();
             },

        },

        //Set column definition initialisation properties.
        "columnDefs": [
        { 
            "targets": [ 0,4 ], //first column / numbering column
            "orderable": false, //set not orderable
        },
        {
            "targets" :[0],
            "className": "text-center",
        },
        
        ],
    });
    new $.fn.dataTable.FixedHeader( table );
});
</script>
</section>
<style>
.box.mp-items-box { border: none !important; background: transparent !important; box-shadow: none !important; border-radius: 0 !important; }
</style>
<script type="text/javascript">
$(document).ready(function() {
    //datatables
   var table = $('#example3').DataTable({ 
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
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [0,1,2,3]} },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [0,1,2,3]} },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [0,1,2,3]} },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [0,1,2,3]} },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [0,1,2,3]} },
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
            "url": "<?php echo site_url('tax_group/ajax_list')?>",
            "type": "POST",
            
            complete: function (data) {
             $('.column_checkbox').iCheck({
                checkboxClass: 'icheckbox_square-orange',
                /*uncheckedClass: 'bg-white',*/
                radioClass: 'iradio_square-orange',
                increaseArea: '10%' // optional
              });
             call_code();
              //$(".delete_btn").hide();
             },

        },

        //Set column definition initialisation properties.
        "columnDefs": [
        { 
            "targets": [ 4 ], //first column / numbering column
            "orderable": false, //set not orderable
        },
        {
            "targets" :[0],
            "className": "text-center",
        },
        
        ],
    });
    new $.fn.dataTable.FixedHeader( table );
});
</script>
<!-- Make sidebar menu highlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
