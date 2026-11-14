<?php
/* SMS Templates List — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<?php include"comman/code_flashdata.php"; ?>

<div class="mp-page-head">
  <h1 class="mp-page-title"><?= $this->lang->line('sms_templates_list'); ?></h1>
</div>

<div class="mp-card">
  <div class="mp-card-body">
    <input type="hidden" id="base_url" value="<?=base_url()?>">
    <div class="mp-dt-scroll">
      <table id="example2" class="mp-dt-table" width="100%">
        <thead>
        <tr class='bg-gray'>
          <th>#</th>
          <th><?= $this->lang->line('template_name'); ?></th>
          <th><?= $this->lang->line('content'); ?></th>
          <th><?= $this->lang->line('status'); ?></th>
          <th><?= $this->lang->line('action'); ?></th>
        </tr>
        </thead>
        <tbody>
		
        </tbody>
       
      </table>
    </div>
  </div>
</div>

<script type="text/javascript">
var table;
$(document).ready(function() {
    //datatables
    table = $('#example2').DataTable({ 
        "processing": true, //Feature control the processing indicator.
        "serverSide": true, //Feature control DataTables' server-side processing mode.
        "order": [], //Initial no order.
        "responsive": true,
        // Load data for the table's content from an Ajax source
        "ajax": {
            "url": "<?php echo site_url('templates/ajax_list')?>",
            "type": "POST"
        },

        //Set column definition initialisation properties.
        "columnDefs": [
        { 
            "targets": [ 0,3,4 ], //first column / numbering column
            "orderable": false, //set not orderable
        },
        ],
    });
    new $.fn.dataTable.FixedHeader( table );
});
</script>
<script src="<?php echo $theme_link; ?>js/templates.js"></script>
<!-- Make sidebar menu hughlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");$(".<?php echo basename(__FILE__,'.php');?>-active-li").closest(".mp-nav-group").addClass("open");</script>
