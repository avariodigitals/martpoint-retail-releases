<?php $this->load->view('customers/desktop/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Import suppliers from a CSV file</div>
  </div>
</div>

<div class="mp-card-form box">
  <div class="mp-card-head">
    <h3>Please Enter Valid Data</h3>
  </div>
  <div class="mp-card-body">
    <form class="form-horizontal" id="import-form" enctype="multipart/form-data" method="POST">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">
      <input type="hidden" name="store_id" id="store_id" value="<?= htmlspecialchars(get_current_store_id()); ?>">

      <div class="mp-form-grid">
        <div class="mp-form-group full">
          <label for="import_file"><?= $this->lang->line('import_suppliers'); ?> <span class="text-danger">*</span></label>
          <input type="file" class="mp-form-control" id="import_file" name="import_file" style="padding:9px 14px;">
          <span id="import_file_msg" style="display:block;" class="text-danger">Note: File must be in CSV format.</span>
        </div>
      </div>

      <div class="mp-form-actions" style="margin-top:24px;">
        <button type="button" id="import" class="mp-btn-primary" title="Import Data"><i class="fa fa-arrow-circle-o-left"></i> Import</button>
        <a href="<?= base_url('dashboard'); ?>" class="mp-btn-secondary close_btn" title="Go Dashboard">Close</a>
      </div>
    </form>
  </div>
</div>

<div class="mp-card box" style="margin-top:24px;">
  <div class="mp-card-head">
    <h3><?= $this->lang->line('import_instructions'); ?></h3>
    <a href="<?= base_url('import/download/suppliers'); ?>"><button type="button" class="mp-qa-btn blue" title="Download Data in Excel Format"><?= $this->lang->line('download_example_format'); ?></button></a>
  </div>
  <div class="mp-card-body" style="padding:0;">
    <table class="table mp-static-table" id="report-data">
      <thead>
        <tr>
          <th>#</th>
          <th><?= $this->lang->line('column_name'); ?></th>
          <th><?= $this->lang->line('value'); ?></th>
          <th><?= $this->lang->line('details'); ?></th>
        </tr>
      </thead>
      <tbody id="tbodyid">
        <?php $i=1; ?>
        <tr><td><?=$i++;?></td><td><?= $this->lang->line('supplier_name'); ?></td><td><span class="mp-pill success"><?= $this->lang->line('required'); ?></span></td><td></td></tr>
        <tr><td><?=$i++;?></td><td><?= $this->lang->line('mobile'); ?></td><td><span class="mp-pill muted"><?= $this->lang->line('optional'); ?></span></td><td></td></tr>
        <tr><td><?=$i++;?></td><td><?= $this->lang->line('email'); ?></td><td><span class="mp-pill muted"><?= $this->lang->line('optional'); ?></span></td><td></td></tr>
        <tr><td><?=$i++;?></td><td><?= $this->lang->line('phone'); ?></td><td><span class="mp-pill muted"><?= $this->lang->line('optional'); ?></span></td><td></td></tr>
        <tr><td><?=$i++;?></td><td><?= $this->lang->line('gst_number'); ?></td><td><span class="mp-pill muted"><?= $this->lang->line('optional'); ?></span></td><td></td></tr>
        <tr><td><?=$i++;?></td><td><?= $this->lang->line('tax_number'); ?></td><td><span class="mp-pill muted"><?= $this->lang->line('optional'); ?></span></td><td></td></tr>
        <tr><td><?=$i++;?></td><td><?= $this->lang->line('opening_balance'); ?></td><td><span class="mp-pill muted"><?= $this->lang->line('optional'); ?></span></td><td></td></tr>
        <tr><td><?=$i++;?></td><td><?= $this->lang->line('country_name'); ?></td><td><span class="mp-pill muted"><?= $this->lang->line('optional'); ?></span></td><td></td></tr>
        <tr><td><?=$i++;?></td><td><?= $this->lang->line('state_name'); ?></td><td><span class="mp-pill muted"><?= $this->lang->line('optional'); ?></span></td><td></td></tr>
        <tr><td><?=$i++;?></td><td><?= $this->lang->line('postcode'); ?></td><td><span class="mp-pill muted"><?= $this->lang->line('optional'); ?></span></td><td></td></tr>
        <tr><td><?=$i++;?></td><td><?= $this->lang->line('address'); ?></td><td><span class="mp-pill muted"><?= $this->lang->line('optional'); ?></span></td><td></td></tr>
      </tbody>
    </table>
  </div>
</div>

<script type="text/javascript">
  $("#import").on("click",function(e) {
    var base_url = $("#base_url").val();
    if($("#import_file").val()==''){
      toastr["warning"]("Please select file to Import!");
      failed.currentTime = 0;
      failed.play();
      return;
    }

    e.preventDefault();
    function doImportSuppliers(){
      data = new FormData($('#import-form')[0]);
      if(!xss_validation(data)){ return false; }

      $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
      $("#import").attr('disabled',true);
      $.ajax({
        type: 'POST',
        url: base_url+'import/import_suppliers_csv',
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        success: function(result){
          if(result=="success") {
            window.location=base_url+"suppliers";
          } else if(result=="failed") {
            toastr["error"]("Sorry! Failed to save Record.Try again!");
          } else {
            toastr["error"](result);
          }
          $("#import").attr('disabled',false);
          $(".overlay").remove();
        }
      });
    }
    if(typeof swal === 'undefined'){
      if(!confirm("Are you sure ?")) return;
      doImportSuppliers();
    } else {
      swal({
        title: "Are you sure?",
        text: "This will import suppliers from the selected CSV file.",
        icon: "warning",
        buttons: true,
        dangerMode: true
      }).then(function(willImport){
        if(willImport) doImportSuppliers();
      });
    }
  });
</script>
<script>
  $('.mp-nav-item').removeClass('active');
  $('.import_suppliers-active-li').addClass('active');
  $('.import_suppliers-active-li').closest('.mp-nav-group').addClass('open');
</script>
