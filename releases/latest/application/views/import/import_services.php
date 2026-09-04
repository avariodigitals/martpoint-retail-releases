<?php
$this->load->view('admin/desktop/_styles');

$CI =& get_instance();
$store_name = $this->session->userdata('store_name') ?: 'MartPoint';
?>

<style>
.mp-card-form { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 16px; box-shadow: var(--mp-shadow-sm); overflow: hidden; margin-bottom: 24px; }
.mp-card-form .mp-card-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px 14px; border-bottom: 1px solid var(--mp-border); }
.mp-card-form .mp-card-head h3 { font-size: 15px; font-weight: 700; margin: 0; color: var(--mp-text); }
.mp-card-form .mp-card-body { padding: 20px; }
.mp-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px 24px; }
.mp-form-grid .mp-form-group.full { grid-column: 1 / -1; }
.mp-form-group { display: flex; flex-direction: column; gap: 6px; }
.mp-form-group > label { font-size: 13px; font-weight: 600; color: var(--mp-ink); }
.mp-form-group > label .text-danger { color: var(--mp-danger); }
.mp-form-hint { font-size: 12px; color: var(--mp-muted); margin: 0; }
.mp-form-control { width: 100%; padding: 11px 14px; border: 1px solid var(--mp-border); border-radius: 10px; background: var(--mp-surface); color: var(--mp-ink); font-size: 14px; font-weight: 500; font-family: inherit; transition: all .15s ease; }
.mp-form-control:focus { outline: none; border-color: var(--mp-primary); box-shadow: 0 0 0 3px rgba(0,87,255,.1); }
.mp-form-actions { display: flex; gap: 10px; flex-wrap: wrap; padding: 16px 20px; border-top: 1px solid var(--mp-border); background: var(--mp-bg); }
.mp-file-drop { border: 1px dashed var(--mp-border); border-radius: 10px; padding: 22px; text-align: center; color: var(--mp-muted); font-size: 13px; cursor: pointer; transition: all .15s ease; background: var(--mp-bg); }
.mp-file-drop:hover { border-color: var(--mp-primary); color: var(--mp-primary); }
.mp-file-drop input[type=file] { display: none; }
.mp-file-name { margin-top: 10px; font-size: 12px; color: var(--mp-ink); font-weight: 600; }
.mp-tbl { width: 100%; border-collapse: collapse; }
.mp-tbl th { text-align: left; font-size: 11px; font-weight: 700; color: var(--mp-muted); text-transform: uppercase; letter-spacing: .04em; padding: 10px 16px; border-bottom: 1px solid var(--mp-border); }
.mp-tbl td { padding: 12px 16px; font-size: 13px; color: var(--mp-ink); border-bottom: 1px solid var(--mp-border); }
.mp-tbl tr:last-child td { border-bottom: none; }
.mp-tbl tr:hover td { background: var(--mp-bg); }
.mp-pill { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px; }
.mp-pill.ok { background: rgba(5,150,105,.1); color: var(--mp-success); }
.mp-pill.default { background: rgba(120,113,108,.1); color: var(--mp-muted); }
@media (max-width:767px){ .mp-form-grid { grid-template-columns: 1fr; } }
</style>

<div class="mp-section">
  <?php include "comman/code_flashdata.php"; ?>
</div>

<!-- Page Header -->
<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2><?= $page_title; ?></h2>
      <div class="mp-page-sub"><?= htmlspecialchars($store_name); ?> &mdash; Import services from a CSV file</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a href="<?php echo $base_url; ?>items" class="mp-qa-btn" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back to Items
      </a>
    </div>
  </div>
</div>

<!-- Import Form -->
<div class="mp-section">
  <div class="mp-card-form box">
    <div class="mp-card-head">
      <h3>Upload CSV File</h3>
    </div>
    <form id="import-form" enctype="multipart/form-data" method="POST">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
      <input type="hidden" id="base_url" value="<?php echo $base_url; ?>">
      <input type="hidden" name="store_id" id="store_id" value="<?php echo htmlspecialchars(get_current_store_id()); ?>">

      <div class="mp-card-body">
        <div class="mp-form-grid">
          <div class="mp-form-group full">
            <label><?= $this->lang->line('import_services'); ?> <span class="text-danger">*</span></label>
            <label class="mp-file-drop" for="import_file">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              Click to choose a CSV file
            </label>
            <input type="file" id="import_file" name="import_file" accept=".csv" style="display:none;" onchange="document.getElementById('import_file_name').textContent = this.files[0] ? this.files[0].name : ''; document.getElementById('import_file_name').style.display = this.files[0] ? 'block' : 'none';">
            <div id="import_file_name" class="mp-file-name" style="display:none;"></div>
            <p class="mp-form-hint">File must be in CSV format. <a href="<?= base_url('import/download/services'); ?>" style="color:var(--mp-primary);font-weight:600;">Download example format</a></p>
            <span id="import_file_msg" style="display:none" class="text-danger"></span>
          </div>
        </div>
      </div>

      <div class="mp-form-actions">
        <button type="button" id="import" class="mp-qa-btn green" title="Import CSV">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Import
        </button>
        <a href="<?php echo $base_url; ?>items" class="mp-qa-btn" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);">Cancel</a>
      </div>
    </form>
  </div>
</div>

<!-- Instructions -->
<div class="mp-section">
  <div class="mp-card-form">
    <div class="mp-card-head">
      <h3><?= $this->lang->line('import_instructions'); ?></h3>
    </div>
    <div class="mp-card-body" style="padding:0;">
      <table class="mp-tbl" id="report-data">
        <thead>
        <tr>
          <th>#</th>
          <th><?= $this->lang->line('column_name'); ?></th>
          <th><?= $this->lang->line('value'); ?></th>
          <th><?= $this->lang->line('description'); ?></th>
        </tr>
        </thead>
        <tbody id="tbodyid">
          <?php $i=1; ?>
          <tr><td><?= $i++; ?></td><td><?= $this->lang->line('service_name'); ?></td><td><span class="mp-pill ok"><?= $this->lang->line('required'); ?></span></td><td></td></tr>
          <tr><td><?= $i++; ?></td><td><?= $this->lang->line('category_name'); ?></td><td><span class="mp-pill ok"><?= $this->lang->line('required'); ?></span></td><td></td></tr>
          <tr><td><?= $i++; ?></td><td><?= $this->lang->line('price_before_tax'); ?></td><td><span class="mp-pill ok"><?= $this->lang->line('required'); ?></span></td><td>Enter "0" if there are no expenses</td></tr>
          <tr><td><?= $i++; ?></td><td><?= $this->lang->line('tax_name'); ?></td><td><span class="mp-pill ok"><?= $this->lang->line('required'); ?></span></td><td></td></tr>
          <tr><td><?= $i++; ?></td><td><?= $this->lang->line('tax_value'); ?></td><td><span class="mp-pill ok"><?= $this->lang->line('required'); ?></span></td><td></td></tr>
          <tr><td><?= $i++; ?></td><td><?= $this->lang->line('tax_type'); ?></td><td><span class="mp-pill ok"><?= $this->lang->line('required'); ?></span></td><td>"Inclusive" or "Exclusive"</td></tr>
          <tr><td><?= $i++; ?></td><td><?= $this->lang->line('sales_price'); ?></td><td><span class="mp-pill ok"><?= $this->lang->line('required'); ?></span></td><td></td></tr>
          <tr><td><?= $i++; ?></td><td><?= $this->lang->line('sac'); ?></td><td><span class="mp-pill default"><?= $this->lang->line('optional'); ?></span></td><td></td></tr>
          <tr><td><?= $i++; ?></td><td><?= $this->lang->line('barcode'); ?></td><td><span class="mp-pill default"><?= $this->lang->line('optional'); ?></span></td><td></td></tr>
          <tr><td><?= $i++; ?></td><td><?= $this->lang->line('seller_points'); ?></td><td><span class="mp-pill default"><?= $this->lang->line('optional'); ?></span></td><td></td></tr>
          <tr><td><?= $i++; ?></td><td><?= $this->lang->line('description'); ?></td><td><span class="mp-pill default"><?= $this->lang->line('optional'); ?></span></td><td></td></tr>
          <tr><td><?= $i++; ?></td><td><?= $this->lang->line('discount_type'); ?></td><td><span class="mp-pill default"><?= $this->lang->line('optional'); ?></span></td><td>"Percentage" or "Fixed"</td></tr>
          <tr><td><?= $i++; ?></td><td><?= $this->lang->line('discount'); ?></td><td><span class="mp-pill default"><?= $this->lang->line('optional'); ?></span></td><td></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>


<script type="text/javascript">
  $("#import").on("click", function(e) {
    var base_url = $("#base_url").val();
    var fileInput = $("#import_file");
    if (fileInput.val() == '') {
      toastr["warning"]("Please select a CSV file to import.");
      if (typeof failed !== 'undefined') { failed.currentTime = 0; failed.play(); }
      return;
    }
    var fileName = fileInput[0].files[0].name;
    if (fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase() !== 'csv') {
      toastr["error"]("Only CSV files are allowed.");
      return;
    }

    e.preventDefault();
    function doImportServices() {
      var data = new FormData($('#import-form')[0]);
      if (!xss_validation(data)) { return false; }
      $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
      $("#import").attr('disabled', true);
      $.ajax({
        type: 'POST',
        url: base_url + 'import/import_services_csv',
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        success: function(result) {
          if (result == "success") {
            toastr["success"]("Services imported successfully.");
            window.location = base_url + "items";
          } else if (result == "failed") {
            toastr["error"]("Sorry! Failed to import. Try again!");
            $("#import").attr('disabled', false);
          } else {
            toastr["error"](result);
            $("#import").attr('disabled', false);
          }
          $(".overlay").remove();
        },
        error: function() {
          toastr["error"]("Something went wrong. Please try again.");
          $("#import").attr('disabled', false);
          $(".overlay").remove();
        }
      });
    }
    if (typeof swal === 'undefined') {
      if (!confirm("Are you sure?")) return;
      doImportServices();
    } else {
      swal({
        title: "Are you sure?",
        text: "This will import services from the selected CSV file.",
        icon: "warning",
        buttons: true,
        dangerMode: true
      }).then(function(willImport) {
        if (willImport) doImportServices();
      });
    }
  });
</script>
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
