<!DOCTYPE html>
<html>

<head>
<!-- TABLES CSS CODE -->
<?php include"comman/code_css.php"; ?>
</head>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <!-- Left side column. contains the logo and sidebar -->
  
  <?php include"sidebar.php"; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?=$page_title;?>
        <small>View/Search Package</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a data-toggle='tooltip' title='Do you want Import Package ?' href="<?php echo $base_url; ?>import/package"><i class="fa fa-arrow-circle-o-down "></i> <?= $this->lang->line('import_package'); ?></a></li>
        <li class="active"><?=$page_title;?></li>
      </ol>
    </section>
    <div class="pay_now_modal">
    </div>
    <div class="pay_return_due_modal">
    </div>
    <!-- Main content -->
    <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
    <input type="hidden" id='base_url' value="<?=$base_url;?>">
    <section class="content">
      <!-- /.row -->
      <div class="row">
        <!-- ********** ALERT MESSAGE START******* -->
        <?php include"comman/code_flashdata.php"; ?>
        <!-- ********** ALERT MESSAGE END******* -->
      </div>
      <?php
          $total_packages =$this->db->count_all_results('db_package');

          $this->db->where('upper(package_type)', strtoupper('Free'));
          $this->db->from('db_package');
          $free_packages = $this->db->count_all_results(); 

          $this->db->where('upper(plan_type)', strtoupper('monthly'));
          $this->db->from('db_package');
          $monthly_packages = $this->db->count_all_results(); 

          $this->db->where('upper(plan_type)', strtoupper('annually'));
          $this->db->from('db_package');
          $annual_packages = $this->db->count_all_results(); 


      ?>
      <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box " >
            <span class="info-box-icon bg-5"><i class="ion ion-bag"></i></span>
            <div class="info-box-content">
              <span class="info-box-text" ><?=$total_packages?></span>
              <span class="info-box-number"><?=$this->lang->line('total_packages');?></span>
            </div>
            
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <!-- /.col -->
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box " >
            <span class="info-box-icon bg-5"><i class="fa fa-check-circle-o"></i></span>

            <div class="info-box-content">
              <span class="info-box-text" ><?=$free_packages?></span>
              <span class="info-box-number"><?=$this->lang->line('free_packages');?></span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <!-- /.col -->

        <!-- fix for small devices only -->
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box " >
            <span class="info-box-icon bg-5"><i class="fa fa-money"></i></span>

            <div class="info-box-content">
              <span class="info-box-text" ><?=$monthly_packages?></span>
              <span class="info-box-number"><?=$this->lang->line('monthly_packages');?></span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <!-- /.col -->

        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box " >
            <span class="info-box-icon bg-5"><i class="fa fa-dollar"></i></span>

            <div class="info-box-content">
              <span class="info-box-text" ><?=$annual_packages?></span>
              <span class="info-box-number"><?=$this->lang->line('annual_packages');?></span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        <!-- /.col -->
      </div>
      <div class="row">
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              

              <div class="row">
                <div class="col-md-12">
                <div class="col-md-2 pull-right">
                  <?php if ($CI->permissions('package_add')) {?>
                  <div class="box-tools">
                <a class="btn btn-block btn-info" href="<?php echo $base_url; ?>package/add">
                <i class="fa fa-plus"></i> <?= $this->lang->line('new_package'); ?></a>
              </div>
                 <?php }?>
                </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">

                  <div class="col-md-4">
                    <div class="form-group">
                       <label for="users"><?=$this->lang->line('package_type');?> </label></label>
                       <select class="form-control select2" id="package_type" name="package_type"  style="width: 100%;">
                        <option value=''>All</option>
                        <?= package_type() ?>
                     </select>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                       <label for="users"><?=$this->lang->line('plan_type');?> </label></label>
                       <select class="form-control select2" id="plan_type" name="plan_type"  style="width: 100%;">
                        <option value=''>All</option>
                        <?= plan_types() ?>
                     </select>
                    </div>
                  </div>

                </div>
              </div>


            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example2" class="table table-bordered custom_hover" width="100%">
                <thead class="bg-gray ">
                <tr>
                  <th class="text-center">
                    <input type="checkbox" class="group_check checkbox" >
                  </th>
                  <th><?= $this->lang->line('package_type'); ?></th>
                  <th><?= $this->lang->line('package_name'); ?></th>
                  <th><?= $this->lang->line('monthly_price'); ?></th>
                  <th><?= $this->lang->line('annual_price'); ?></th>
                  <th><?= $this->lang->line('trial_days'); ?></th>
                  <th>Branches</th>
                  <th><?= $this->lang->line('max_users'); ?></th>
                  <th><?= $this->lang->line('max_items'); ?></th>
                  <th><?= $this->lang->line('max_invoices'); ?></th>
                  <th><?= $this->lang->line('expire_date'); ?></th>
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
<!-- bootstrap datepicker -->
<script src="<?php echo $theme_link; ?>plugins/datepicker/bootstrap-datepicker.js"></script>
<script type="text/javascript">
  //Date picker
    $('.datepicker').datepicker({
      autoclose: true,
    format: 'dd-mm-yyyy',
     todayHighlight: true
    });
</script>
<script type="text/javascript">
function load_datatable(){
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
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10]} },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10]} },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10]} },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10]} },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10]} },
            { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat',footer: true, text:'Columns' },  

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
            "url": "<?php echo site_url('package/ajax_list')?>",
            "type": "POST",
            "data": {
                      package_type : $("#package_type").val(),
                      plan_type : $("#plan_type").val(),
                    },
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
            "targets": [ 0,11 ], //first column / numbering column
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

$("#plan_type,#package_type").on('change', function() {
  $('#example2').DataTable().destroy();
  load_datatable();
});


</script>

<script src="<?php echo $theme_link; ?>js/package.js"></script>

<!-- Make sidebar menu hughlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
    
</body>
</html>
