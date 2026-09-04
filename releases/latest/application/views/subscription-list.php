<?php
/* Subscription list — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<link rel="stylesheet" href="<?php echo $theme_link; ?>css/subscription.css">

<div class="mp-page-head"><h1 class="mp-page-title"><?= $page_title; ?></h1></div>

<div class="pay_now_modal">
</div>
<div class="pay_return_due_modal">
</div>
<?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
<input type="hidden" id='base_url' value="<?=$base_url;?>">

<?php include"comman/code_flashdata.php"; ?>

<div class="col-xs-12 text-center">
  <div class="btn-group">
    <button type="button" class="btn btn-success" id='monthly_plan'>Monthly</button>
    <button type="button" class="btn btn-default" id='annually_plan'> Annually </button>
  </div>
</div>
<div class="col-xs-12">
  <div class="">
    <div class="planContainer">
      
    </div>
  </div>
</div>

<div class="mp-card">
  <div class="mp-card-head">
    <h3 class="mp-card-title"><?= $this->lang->line('subscriptions'); ?></h3>
    <?php if(is_admin()) { ?>
    <a class="mp-qa-btn green" href="<?php echo $base_url; ?>subscription/add">
    <i class="fa fa-plus"></i> <?= $this->lang->line('manual_subscription'); ?></a>
    <?php } ?>
  </div>
  <div class="mp-card-body">
    <div class="mp-dt-scroll">
    <table id="example2" class="mp-dt-table" width="100%">
      <thead class="bg-gray ">
      <tr>
        
        <th><?= $this->lang->line('package_name'); ?></th>
        <th><?= $this->lang->line('subscription_date'); ?></th>
        <th><?= $this->lang->line('trial_days'); ?></th>
        <th><?= $this->lang->line('expire_date'); ?></th>
        <th><?= $this->lang->line('max_warehouses'); ?></th>
        <th><?= $this->lang->line('max_users'); ?></th>
        <th><?= $this->lang->line('max_items'); ?></th>
        <th><?= $this->lang->line('max_invoices'); ?></th>
        <th><?= $this->lang->line('payment_status'); ?></th>
        <!-- <th><?= $this->lang->line('package_status'); ?></th> -->
        <th><?= $this->lang->line('action'); ?></th>
      </tr>
      </thead>
      <tbody>

      </tbody>
      

    </table>
    </div>
  </div>
</div>

<?= form_close();?>

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
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7]} },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7]} },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7]} },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7]} },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat',footer: true, exportOptions: { columns: [1,2,3,4,5,6,7]} },
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
            "url": "<?php echo site_url('subscription/ajax_list')?>",
            "type": "POST",
            "data": {
                      
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
            "targets": [ 0,9   ], //first column / numbering column
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

   ajax_package_list('monthly');
});


$("#monthly_plan").on('click', function() {
  $("#monthly_plan").addClass("btn-success");
  $("#annually_plan").removeClass("btn-success").addClass("btn-default");

  ajax_package_list('monthly');
});
$("#annually_plan").on('click', function() {
  $("#annually_plan").addClass("btn-success");
  $("#monthly_plan").removeClass("btn-success").addClass("btn-default");

  ajax_package_list('annually');
});


function ajax_package_list(plan_type) {
  $(".planContainer").html("Loading..!!");

  var base_url=$("#base_url").val();
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
   $.post(base_url+"subscription/ajax_package_list",{plan_type:plan_type},function(result){
   //alert(result);return;

   console.log(result);
   result=result;
     /*if(result=="success")
        { 
          toastr["success"]("Record Deleted Successfully!");
          $("#payment_row_"+payment_id).remove();
          success.currentTime = 0; 
          success.play();
        }
        else if(result=="failed"){
          toastr["error"]("Failed to Delete .Try again!");
          failed.currentTime = 0; 
          failed.play();
        }
        else{
          toastr["error"](result);
          failed.currentTime = 0; 
          failed.play();
        }*/
      $(".planContainer").html(result);

        $(".overlay").remove();
        update_paid_payment_total();
        restore_customer_list();
   });
}

</script>

<script src="<?php echo $theme_link; ?>js/subscription.js"></script>

<!-- Make sidebar menu hughlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
