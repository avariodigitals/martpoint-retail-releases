<!DOCTYPE html>
<html>
   <head>
      <?php $this->load->view('comman/code_css');?>
   </head>
   <body class="hold-transition skin-blue sidebar-mini">
      <div class="wrapper">
         <?php $this->load->view('sidebar');?>
         <div class="content-wrapper">
            <section class="content-header">
               <h1><?=$page_title;?><small>Import Single &amp; Variant Products</small></h1>
               <ol class="breadcrumb">
                  <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
                  <li><a href="<?php echo $base_url; ?>items">Items List</a></li>
                  <li class="active"><?=$page_title;?></li>
               </ol>
            </section>
            <section class="content">
               <div class="row">
                  <div class="col-md-12">
                     <div class="box box-primary">
                        <div class="box-header with-border">
                           <h3 class="box-title">CSV Import: Single &amp; Variant Products</h3>
                        </div>
                        <form class="form-horizontal" id="import-form" enctype="multipart/form-data" method="POST">
                           <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
                           <input type="hidden" id="base_url" value="<?php echo $base_url; ?>">
                           <?php echo "<input type='hidden' name='store_id' id='store_id' value='".get_current_store_id()."'>"; ?>
                           <?php if(warehouse_module() && warehouse_count()>1) { $this->load->view('warehouse/warehouse_code',array('show_warehouse_select_box'=>true,'div_length'=>'col-sm-3','show_select_option'=>false)); } else { echo "<input type='hidden' name='warehouse_id' id='warehouse_id' value='".get_store_warehouse_id()."'>"; } ?>
                           <div class="box-body">
                              <div class="form-group">
                                 <label for="import_file" class="col-sm-2 control-label">Import File<label class="text-danger">*</label></label>
                                 <div class="col-sm-4">
                                    <input type="file" id="import_file" name="import_file">
                                    <span class="text-danger" style="display:block;">Note: File must be in CSV format. First row is treated as header.</span>
                                 </div>
                              </div>
                           </div>
                           <div class="box-footer">
                              <div class="col-sm-8 text-center">
                                 <div class="col-md-3">
                                    <button type="button" id="import" class=" btn btn-block btn-success" title="Import Data"><i class="fa fa-arrow-circle-o-left"></i> Import</button>
                                 </div>
                                 <div class="col-sm-3">
                                    <a href="<?php echo $base_url; ?>dashboard"><button type="button" class="col-sm-3 btn btn-block btn-warning close_btn">Close</button></a>
                                 </div>
                              </div>
                           </div>
                        </form>
                     </div>
                  </div>
               </div>
            </section>
            <section class="content">
               <div class="row">
                  <div class="col-md-12">
                     <div class="box">
                        <div class="box-header">
                           <h3 class="box-title">Column Instructions</h3>
                           <a href="<?= base_url('import/download/items-variants');?>"><button type="button" class="btn btn-info pull-right btnExport">Download Example Format</button></a>
                        </div>
                        <div class="box-body table-responsive no-padding">
                           <table class="table table-bordered table-hover">
                              <thead><tr><th>#</th><th>Column</th><th>Required</th><th>Notes</th></tr></thead>
                              <tbody>
                                 <tr><td>1</td><td>item_name</td><td><span class="label label-success">Required</span></td><td>Parent/Single product name. Leave blank for child rows to auto-generate Parent - Variant.</td></tr>
                                 <tr><td>2</td><td>category_name</td><td><span class="label label-success">Required</span></td><td>Auto-creates if missing.</td></tr>
                                 <tr><td>3</td><td>sku</td><td><span class="label label-default">Optional</span></td><td>Parent/Single SKU. Used to link child variants.</td></tr>
                                 <tr><td>4</td><td>hsn</td><td><span class="label label-default">Optional</span></td><td></td></tr>
                                 <tr><td>5</td><td>unit_name</td><td><span class="label label-success">Required</span></td><td>Auto-creates if missing.</td></tr>
                                 <tr><td>6</td><td>alert_qty</td><td><span class="label label-default">Optional</span></td><td></td></tr>
                                 <tr><td>7</td><td>brand_name</td><td><span class="label label-default">Optional</span></td><td>Auto-creates if missing.</td></tr>
                                 <tr><td>8</td><td>lot_number</td><td><span class="label label-default">Optional</span></td><td></td></tr>
                                 <tr><td>9</td><td>price_before_tax</td><td><span class="label label-success">Required</span></td><td>Purchase cost before tax.</td></tr>
                                 <tr><td>10</td><td>tax_name</td><td><span class="label label-success">Required</span></td><td>Auto-creates if missing.</td></tr>
                                 <tr><td>11</td><td>tax_value</td><td><span class="label label-success">Required</span></td><td>Numeric, e.g. 16</td></tr>
                                 <tr><td>12</td><td>tax_type</td><td><span class="label label-success">Required</span></td><td>Inclusive or Exclusive</td></tr>
                                 <tr><td>13</td><td>sales_price</td><td><span class="label label-success">Required</span></td><td></td></tr>
                                 <tr><td>14</td><td>opening_stock</td><td><span class="label label-default">Optional</span></td><td></td></tr>
                                 <tr><td>15</td><td>custom_barcode</td><td><span class="label label-default">Optional</span></td><td></td></tr>
                                 <tr><td>16</td><td>seller_points</td><td><span class="label label-default">Optional</span></td><td></td></tr>
                                 <tr><td>17</td><td>description</td><td><span class="label label-default">Optional</span></td><td></td></tr>
                                 <tr><td>18</td><td>discount_type</td><td><span class="label label-default">Optional</span></td><td>Percentage or Fixed</td></tr>
                                 <tr><td>19</td><td>discount</td><td><span class="label label-default">Optional</span></td><td></td></tr>
                                 <tr><td>20</td><td>mrp</td><td><span class="label label-default">Optional</span></td><td></td></tr>
                                 <tr><td>21</td><td>item_group</td><td><span class="label label-default">Optional</span></td><td>Single, Variants (parent) or Variant (child).</td></tr>
                                 <tr><td>22</td><td>parent_sku</td><td><span class="label label-default">Optional</span></td><td>Required for child rows. Links to parent SKU.</td></tr>
                                 <tr><td>23</td><td>variant_name</td><td><span class="label label-default">Optional</span></td><td>Required for child rows. e.g. Red / M</td></tr>
                              </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            </section>
         </div>
         <?php $this->load->view('footer');?>
         <div class="control-sidebar-bg"></div>
      </div>
      <?php $this->load->view('comman/code_js_sound');?>
      <?php $this->load->view('comman/code_js');?>
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
          function doImport(){
            data = new FormData($('#import-form')[0]);
            if(!xss_validation(data)){ return false; }
            $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
            $("#import").attr('disabled',true);
            $.ajax({
              type: 'POST',
              url: base_url+'import/import_variants_csv',
              data: data,
              cache: false,
              contentType: false,
              processData: false,
              success: function(result){
                if(result=="success"){
                  window.location=base_url+"items";
                } else if(result=="failed"){
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
            doImport();
          } else {
            swal({
              title: "Are you sure?",
              text: "This will import single & variant products from the CSV.",
              icon: "warning",
              buttons: true,
              dangerMode: true
            }).then(function(willImport){
              if(willImport) doImport();
            });
          }
        });
      </script>
      <script>$(".import_variants-active-li").addClass("active");</script>
   </body>
</html>
