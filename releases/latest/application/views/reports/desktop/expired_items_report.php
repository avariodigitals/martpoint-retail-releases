<?php $this->load->view('reports/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<style>
  .expiry-export-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}
  .expiry-export-actions .mp-btn-secondary{cursor:pointer}
  @media print{
    body *{visibility:hidden!important}
    #expiry-report-print,#expiry-report-print *{visibility:visible!important}
    #expiry-report-print{position:absolute!important;left:0!important;top:0!important;width:100%!important;padding:0!important;margin:0!important;background:#fff!important}
    #expiry-report-print .expiry-no-print{display:none!important}
    #expiry-report-print .mp-report-results,#expiry-report-print .mp-kpi-card{box-shadow:none!important;break-inside:avoid}
    #expiry-report-print .mp-dt-scroll{overflow:visible!important}
    #expiry-report-print table{width:100%!important;font-size:9pt!important}
    #expiry-report-print th,#expiry-report-print td{padding:5px!important}
  }
</style>

<div id="expiry-report-print">
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Track expired and expiring inventory</div>
  </div>
  <div class="mp-report-actions expiry-no-print">
    <a href="<?= base_url('expiry_settings'); ?>" class="mp-btn-secondary"><i class="fa fa-cog"></i> Settings</a>
  </div>
</div>

<div class="expiry-export-actions expiry-no-print" style="margin:0 0 16px;">
  <button type="button" class="mp-btn-secondary" id="expiry-export-csv"><i class="fa fa-file-text-o"></i> CSV</button>
  <button type="button" class="mp-btn-secondary" id="expiry-export-pdf"><i class="fa fa-file-pdf-o"></i> PDF</button>
  <button type="button" class="mp-btn-secondary" id="expiry-print"><i class="fa fa-print"></i> Print</button>
  <button type="button" class="mp-btn-secondary" id="expiry-whatsapp" style="background:#25D366;color:#fff;border-color:#25D366;"><i class="fa fa-whatsapp"></i> WhatsApp PDF</button>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card debt">
    <div class="mp-kpi-icon"><i class="fa fa-calendar-times-o"></i></div>
    <div style="font-size:24px;font-weight:800;"><?= count($expired); ?></div>
    <div style="font-size:13px;color:var(--mp-muted);">Expired Batches / Items</div>
  </div>
  <div class="mp-kpi-card stock">
    <div class="mp-kpi-icon"><i class="fa fa-clock-o"></i></div>
    <div style="font-size:24px;font-weight:800;"><?= count($expiring); ?></div>
    <div style="font-size:13px;color:var(--mp-muted);">Batches / Items Expiring Soon</div>
  </div>
  <div class="mp-kpi-card cash">
    <div class="mp-kpi-icon"><i class="fa fa-bell-o"></i></div>
    <div style="font-size:24px;font-weight:800;"><?= $settings->alert_before_days; ?></div>
    <div style="font-size:13px;color:var(--mp-muted);">Alert Days</div>
  </div>
  <div class="mp-kpi-card sales">
    <div class="mp-kpi-icon"><i class="fa fa-exclamation-triangle"></i></div>
    <div style="font-size:24px;font-weight:800;"><?= count($expired) + count($expiring); ?></div>
    <div style="font-size:13px;color:var(--mp-muted);">Total Report Rows</div>
  </div>
</div>

<?php if(!empty($expired) || !empty($expiring)): ?>
<div class="mp-report-results">
  <div class="mp-card-head"><h3>Item Details</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table class="table mp-dt-table" id="expired-items-table" style="width:100%;">
        <thead>
          <tr>
            <th style="width:40px;">#</th>
            <th>Status</th>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Location</th>
            <th>Batch / Lot</th>
            <th>Expiry Date</th>
            <th>MFG Date</th>
            <th class="text-right">Stock</th>
            <th>Time Left</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; ?>
          <?php foreach($expired as $item):
            $days = round((strtotime(date('Y-m-d')) - strtotime($item->expire_date)) / 86400);
          ?>
          <tr>
            <td><?= $i++; ?></td>
            <td><span class="mp-status-pill-inline danger"><i class="fa fa-ban"></i> EXPIRED</span></td>
            <td><code><?= htmlspecialchars($item->item_code ?? '', ENT_QUOTES, 'UTF-8'); ?></code></td>
            <td><strong><?= htmlspecialchars($item->item_name ?? '', ENT_QUOTES, 'UTF-8'); ?></strong></td>
            <td><?= htmlspecialchars($item->warehouse_name ?: 'All locations', ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($item->batch_lot ?: 'Product level', ENT_QUOTES, 'UTF-8'); ?></td>
            <td><span class="text-danger"><strong><?= show_date($item->expire_date); ?></strong></span></td>
            <td><?= is_valid_date($item->mfg_date) ? show_date($item->mfg_date) : '-'; ?></td>
            <td class="text-right amt"><?= format_qty($item->stock); ?></td>
            <td><span class="mp-status-pill-inline danger"><?= $days; ?> days past</span></td>
            <td><a href="<?= base_url('items/update/'.$item->id); ?>" class="mp-btn-secondary" style="padding:6px 12px;font-size:12px;"><i class="fa fa-edit"></i> Edit</a></td>
          </tr>
          <?php endforeach; ?>

          <?php foreach($expiring as $item):
            $days = round((strtotime($item->expire_date) - strtotime(date('Y-m-d'))) / 86400);
          ?>
          <tr>
            <td><?= $i++; ?></td>
            <td><span class="mp-status-pill-inline warn"><i class="fa fa-clock-o"></i> EXPIRING</span></td>
            <td><code><?= htmlspecialchars($item->item_code ?? '', ENT_QUOTES, 'UTF-8'); ?></code></td>
            <td><strong><?= htmlspecialchars($item->item_name ?? '', ENT_QUOTES, 'UTF-8'); ?></strong></td>
            <td><?= htmlspecialchars($item->warehouse_name ?: 'All locations', ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($item->batch_lot ?: 'Product level', ENT_QUOTES, 'UTF-8'); ?></td>
            <td><span class="text-warning"><strong><?= show_date($item->expire_date); ?></strong></span></td>
            <td><?= is_valid_date($item->mfg_date) ? show_date($item->mfg_date) : '-'; ?></td>
            <td class="text-right amt"><?= format_qty($item->stock); ?></td>
            <td><span class="mp-status-pill-inline warn"><?= $days; ?> days left</span></td>
            <td><a href="<?= base_url('items/update/'.$item->id); ?>" class="mp-btn-secondary" style="padding:6px 12px;font-size:12px;"><i class="fa fa-edit"></i> Edit</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php else: ?>
<div class="mp-report-results">
  <div class="box-body">
    <div class="mp-empty-state">
      <div class="mp-empty-icon"><i class="fa fa-check-circle"></i></div>
      <h3>All Clear!</h3>
      <p>No expired or expiring items found. Your inventory is currently within safe expiry thresholds.</p>
      <a href="<?= base_url('expiry_settings'); ?>" class="mp-btn-secondary"><i class="fa fa-cog"></i> Manage Settings</a>
    </div>
  </div>
</div>
<?php endif; ?>

<script>$(".report-expired-items-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
</div>

<script>
(function(){
  var table=document.getElementById('expired-items-table');
  var reportTitle='Expired and Expiring Items Report';
  var fileBase='expiry-report-'+new Date().toISOString().slice(0,10);
  var headers=['Status','Item Code','Item Name','Location','Batch / Lot','Expiry Date','MFG Date','Stock','Time Left'];

  function rowsFromTable(){
    if(!table)return [];
    return Array.prototype.map.call(table.querySelectorAll('tbody tr'),function(tr){
      var cells=Array.prototype.slice.call(tr.children);
      return cells.slice(1,10).map(function(td){return (td.innerText||td.textContent||'').replace(/\s+/g,' ').trim();});
    });
  }
  function csvCell(value){return '"'+String(value==null?'':value).replace(/"/g,'""')+'"';}
  function downloadBlob(blob,name){
    var url=URL.createObjectURL(blob),a=document.createElement('a');
    a.href=url;a.download=name;document.body.appendChild(a);a.click();a.remove();
    setTimeout(function(){URL.revokeObjectURL(url);},1500);
  }
  function pdfBlob(callback){
    if(!window.pdfMake||!window.pdfMake.createPdf){alert('PDF export is unavailable. Please use Print and choose Save as PDF.');return;}
    var data=rowsFromTable();
    var body=[headers];
    if(data.length)body=body.concat(data);else body.push(['No expired or expiring stock found.','','','','','','','','']);
    var doc={
      pageOrientation:'landscape',pageSize:'A4',pageMargins:[28,36,28,32],
      content:[
        {text:<?= json_encode((string)($SITE_TITLE ?? 'MartPoint'), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>,fontSize:9,color:'#64748b',margin:[0,0,0,4]},
        {text:reportTitle,fontSize:17,bold:true,color:'#172033',margin:[0,0,0,4]},
        {text:'Generated '+new Date().toLocaleString(),fontSize:8,color:'#64748b',margin:[0,0,0,14]},
        {table:{headerRows:1, widths:[55,55,'*',75,65,62,62,42,60],body:body},layout:'lightHorizontalLines'}
      ],
      defaultStyle:{fontSize:8},
      styles:{tableHeader:{bold:true,color:'#172033',fillColor:'#eef3f5'}}
    };
    window.pdfMake.createPdf(doc).getBlob(callback);
  }

  var csvButton=document.getElementById('expiry-export-csv');
  if(csvButton)csvButton.addEventListener('click',function(){
    var data=rowsFromTable(),lines=[headers].concat(data);
    if(!data.length)lines.push(['No expired or expiring stock found.']);
    downloadBlob(new Blob(['\ufeff'+lines.map(function(row){return row.map(csvCell).join(',');}).join('\r\n')],{type:'text/csv;charset=utf-8;'}),fileBase+'.csv');
  });

  var pdfButton=document.getElementById('expiry-export-pdf');
  if(pdfButton)pdfButton.addEventListener('click',function(){pdfBlob(function(blob){downloadBlob(blob,fileBase+'.pdf');});});

  var printButton=document.getElementById('expiry-print');
  if(printButton)printButton.addEventListener('click',function(){window.print();});

  var whatsappButton=document.getElementById('expiry-whatsapp');
  if(whatsappButton)whatsappButton.addEventListener('click',function(){
    var blank=window.open('about:blank','_blank');
    pdfBlob(function(blob){
      var file;
      try{file=new File([blob],fileBase+'.pdf',{type:'application/pdf'});}catch(e){}
      if(file&&navigator.share&&navigator.canShare&&navigator.canShare({files:[file]})){
        if(blank)blank.close();
        navigator.share({files:[file],title:reportTitle,text:'Please see the attached expiry report.'}).catch(function(error){if(error&&error.name!=='AbortError')downloadBlob(blob,fileBase+'.pdf');});
        return;
      }
      downloadBlob(blob,fileBase+'.pdf');
      if(blank){blank.location='https://wa.me/?text='+encodeURIComponent('Please find the expired and expiring items report attached. The PDF has been downloaded; attach it in this WhatsApp chat.');}
      else{window.open('https://wa.me/?text='+encodeURIComponent('Please find the expired and expiring items report attached. The PDF has been downloaded; attach it in this WhatsApp chat.'),'_blank');}
    });
  });
})();
</script>
