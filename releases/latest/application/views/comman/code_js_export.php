<!-- Export -->
<?php if (empty($GLOBALS['__mp_export_libs_loaded'])): $GLOBALS['__mp_export_libs_loaded'] = true; ?>
      <script type="text/javascript" src="<?php echo $theme_link; ?>plugins/tableExporter/libs/js-xlsx/xlsx.core.min.js"></script>
      <script type="text/javascript" src="<?php echo $theme_link; ?>plugins/tableExporter/libs/jsPDF/jspdf.min.js"></script>
      <script type="text/javascript" src="<?php echo $theme_link; ?>plugins/tableExporter/libs/jsPDF-AutoTable/jspdf.plugin.autotable.js"></script>
      <script type="text/javascript" src="<?php echo $theme_link; ?>plugins/tableExporter/tableExport.min.js"></script>
<?php endif; ?>
<script>
      function downloadPdf(tableId){
            $('#'+tableId).tableExport({type:'pdf',escape:'false'});
      }
      function downloadExcel(tableId){
            $('#'+tableId).tableExport({type:'xlsx',escape:'false'});
      }
      /* Delegated + namespaced so a second include rebinds instead of stacking
         a duplicate handler (which would fire the download twice). */
      $(document).off('click.mpExport');
      $(document).on('click.mpExport', '.downloadPdf', function(){
            downloadPdf($(this).attr("data-table-id"));
      });
      $(document).on('click.mpExport', '.downloadExcel', function(){
            downloadExcel($(this).attr("data-table-id"));
      });
</script>
