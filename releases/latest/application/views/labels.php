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
.mp-form-control { width: 100%; padding: 11px 14px; border: 1px solid var(--mp-border); border-radius: 10px; background: var(--mp-surface); color: var(--mp-ink); font-size: 14px; font-weight: 500; font-family: inherit; transition: all .15s ease; }
.mp-form-control:focus { outline: none; border-color: var(--mp-primary); box-shadow: 0 0 0 3px rgba(0,87,255,.1); }
.mp-form-actions { display: flex; gap: 10px; flex-wrap: wrap; padding: 16px 20px; border-top: 1px solid var(--mp-border); background: var(--mp-bg); }
.mp-search-row { display: flex; align-items: center; gap: 10px; max-width: 560px; margin: 0 auto 20px; }
.mp-search-row .mp-search-icon { width: 42px; height: 42px; border-radius: 10px; background: var(--mp-bg); border: 1px solid var(--mp-border); display: flex; align-items: center; justify-content: center; color: var(--mp-muted); flex-shrink: 0; }
.mp-search-row .mp-form-control { flex: 1; }

/* Labels table */
#sales_table { width: 100%; max-width: 720px; margin: 0 auto; border-collapse: collapse; font-size: 13px; }
#sales_table th { text-align: center; font-size: 11px; font-weight: 700; color: var(--mp-muted); text-transform: uppercase; letter-spacing: .04em; padding: 10px 12px; border-bottom: 1px solid var(--mp-border); background: var(--mp-bg); }
#sales_table td { padding: 8px 12px; border-bottom: 1px solid var(--mp-border); color: var(--mp-ink); }
#sales_table tr:hover td { background: var(--mp-bg); }
.label_border { border: 1px dotted var(--mp-border) !important; overflow: hidden; box-sizing: border-box; }

.mp-totals-row { display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; color: var(--mp-muted); margin: 16px 0; }
.mp-totals-row .total_quantity { font-weight: 700; color: var(--mp-success); font-size: 15px; }

.mp-empty-state { text-align: center; padding: 32px 16px; color: var(--mp-muted); font-size: 13px; }

#preview_data { overflow: auto; padding: 16px; }
.mp-preview-card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 16px; box-shadow: var(--mp-shadow-sm); overflow: hidden; margin-bottom: 24px; }
.mp-preview-card .mp-card-body { padding: 16px; }
</style>

<div class="mp-section">
  <?php include "comman/code_flashdata.php"; ?>
</div>

<!-- Page Header -->
<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2><?= $page_title; ?></h2>
      <div class="mp-page-sub"><?= htmlspecialchars($store_name); ?> &mdash; Print product barcode labels</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a href="<?php echo $base_url; ?>items" class="mp-qa-btn" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back to Items
      </a>
    </div>
  </div>
</div>

<!-- Label Builder -->
<div class="mp-section">
  <?= form_open('#', array('class' => '', 'id' => 'labels-form', 'enctype'=>'multipart/form-data', 'method'=>'POST')); ?>
  <input type="hidden" id="base_url" value="<?php echo $base_url; ?>">
  <input type="hidden" value='1' id="hidden_rowcount" name="hidden_rowcount">
  <input type="hidden" value='0' id="hidden_update_rowid" name="hidden_update_rowid">
  <input type="hidden" name="store_id" id="store_id" value="<?php echo htmlspecialchars(get_current_store_id()); ?>">

  <div class="mp-card-form box">
    <div class="mp-card-head">
      <h3>Build Your Labels</h3>
    </div>
    <div class="mp-card-body">
      <div class="mp-search-row">
        <div class="mp-search-icon">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        </div>
        <input type="text" class="mp-form-control" placeholder="Search by item name, barcode or item code" id="item_search">
      </div>

      <table id="sales_table">
        <thead>
          <tr>
            <th style="width:45%"><?= $this->lang->line('item_name'); ?></th>
            <th style="width:45%"><?= $this->lang->line('quantity'); ?></th>
            <th style="width:10%"><?= $this->lang->line('action'); ?></th>
          </tr>
        </thead>
        <tbody>
          <tr class="mp-empty-state"><td colspan="3">Search for an item above to add it to the label sheet.</td></tr>
        </tbody>
      </table>

      <div class="mp-totals-row">
        <span><?= $this->lang->line('total_labels'); ?>:</span>
        <span class="total_quantity">0</span>
      </div>
    </div>
    <div class="mp-form-actions">
      <button type="button" id="preview" class="mp-qa-btn green" title="Preview Labels">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Preview Labels
      </button>
      <a href="<?php echo $base_url; ?>items" class="mp-qa-btn" style="background:var(--mp-bg);color:var(--mp-ink);border:1px solid var(--mp-border);">Close</a>
    </div>
  </div>

  <!-- Preview -->
  <div class="mp-preview-card box">
    <div class="mp-card-body">
      <span id="preview_data" style="display:block;min-height:80px;">
        <div class="mp-empty-state">Preview will appear here after you click “Preview Labels”.</div>
      </span>
      <div style="margin-top:16px;text-align:right;">
        <input type="button" class="mp-qa-btn blue" id="print" value="Print">
      </div>
    </div>
  </div>

  <?= form_close(); ?>
</div>

<script>
  var base_url = $("#base_url").val();
  $("#store_id").on("change", function(){
    var store_id = $(this).val();
    $.post(base_url + "sales/get_customers_select_list", {store_id: store_id}, function(result){
      $("#sales_table > tbody").empty();
      final_total();
    });
  });

  /* ---------- Final total quantity ------------*/
  function final_total(){
    var rowcount = $("#hidden_rowcount").val();
    var total_quantity = 0;
    for (var i = 1; i <= rowcount; i++) {
      if (document.getElementById("td_data_" + i + "_1")) {
        if ($("#td_data_" + i + "_1").val() != null && $("#td_data_" + i + "_1").val() != '') {
          total_quantity += parseInt($("#td_data_" + i + "_3").val()) || 0;
        }
      }
    }
    $(".total_quantity").html(total_quantity);
  }

  function removerow(id){
    $("#row_" + id).remove();
    final_total();
    if (typeof failed !== 'undefined') { failed.currentTime = 0; failed.play(); }
  }

  /* Print */
  $("#print").on("click", function(event){
    PrintMe("preview_data");
  });

  function PrintMe(DivID){
    var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,scrollbars=yes,width=800,height=600,left=100,top=25";
    var content_vlue = document.getElementById(DivID).innerHTML;
    var docprint = window.open("", "", disp_setting);
    if (!docprint) { toastr && toastr["error"] ? toastr["error"]("Please allow pop-ups to print labels.") : alert("Please allow pop-ups to print labels."); return; }
    docprint.document.open();
    docprint.document.write('<!DOCTYPE html><html><head><title><?php echo htmlspecialchars($store_name); ?> — Labels</title>');
    docprint.document.write('<style type="text/css">body{margin:0px;font-family:Verdana,Arial;color:#000;font-size:12px;}a{color:#000;text-decoration:none;}</style>');
    docprint.document.write('</head><body onLoad="self.print()">');
    docprint.document.write(content_vlue);
    docprint.document.write('</body></html>');
    docprint.document.close();
    docprint.focus();
  }
</script>

<!-- Purchase list barcode auto-load -->
<script type="text/javascript">
  jQuery(document).ready(function($){
    <?php if(isset($purchase_id)): ?>
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    var base_url = $("#base_url").val();
    $.post(base_url + "items/show_labels/<?= (int)$purchase_id; ?>", {}, function(result){
      $('#sales_table tbody').append(result);
      $("#hidden_rowcount").val($('#sales_table tbody tr').length + 1);
      $("#preview").trigger('click');
      if (typeof success !== 'undefined') { success.currentTime = 0; success.play(); }
      final_total();
      $(".overlay").remove();
    });
    <?php endif; ?>
  });
</script>
<!-- Make sidebar menu highlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
