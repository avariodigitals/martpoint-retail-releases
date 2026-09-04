<?php $this->load->view('marketing/desktop/_styles'); ?>
<?php
$CI =& get_instance();
?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">View and manage customer coupons</div>
  </div>
  <?php if($CI->permissions('customerCouponAdd')) { ?>
  <a class="mp-qa-btn green" href="<?= base_url('customer_coupon/generate'); ?>">
    <i class="fa fa-plus"></i> Create Customer Coupon
  </a>
  <?php } ?>
</div>

<div class="mp-table-wrap">
  <div class="box-body" style="overflow:hidden">
    <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
    <input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">
    <table id="example2" class="table mp-dt-table custom_hover" width="100%">
      <thead>
        <tr>
          <th class="text-center">
            <input type="checkbox" class="group_check checkbox">
          </th>
          <th><?= $this->lang->line('customer_name'); ?></th>
          <th><?= $this->lang->line('couponName'); ?></th>
          <th><?= $this->lang->line('couponCode'); ?></th>
          <th><?= $this->lang->line('expire_date'); ?></th>
          <th><?= $this->lang->line('value'); ?></th>
          <th><?= $this->lang->line('couponType'); ?></th>
          <th><?= $this->lang->line('description'); ?></th>
          <th><?= $this->lang->line('status'); ?></th>
          <th><?= $this->lang->line('action'); ?></th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
    <?= form_close(); ?>
  </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    //datatables
   var table = $('#example2').DataTable({

      "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],

      /* FOR EXPORT BUTTONS START*/
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
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [1,2,3,4,5,6,7,8]} },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [1,2,3,4,5,6,7,8]} },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [1,2,3,4,5,6,7,8]} },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [1,2,3,4,5,6,7,8]} },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [1,2,3,4,5,6,7,8]} },
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
            "url": "<?= base_url('customer_coupon/ajax_list'); ?>",
            "type": "POST",
            "data": function(d){
                d['<?= $this->security->get_csrf_token_name(); ?>'] = '<?= $this->security->get_csrf_hash(); ?>';
            },
            complete: function (data) {
             $('.column_checkbox').iCheck({
                checkboxClass: 'icheckbox_square-orange',
                radioClass: 'iradio_square-orange',
                increaseArea: '10%' // optional
              });
             call_code();
             },

        },

        //Set column definition initialisation properties.
        "columnDefs": [
        {
            "targets": [ 0,9 ], //first column / numbering column
            "orderable": false, //set not orderable
        },
        {
            "targets" :[0],
            "className": "text-center",
        },

        ],
    });
});
</script>
<script src="<?= htmlspecialchars($theme_link); ?>js/coupons/generate.js?v=3"></script>
<!-- Make sidebar menu highlighter/selector -->
<script>$(".customerCouponsList-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
